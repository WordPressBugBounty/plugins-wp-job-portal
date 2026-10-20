<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Converts legacy core tables from MyISAM to InnoDB in resumable batches.
 *
 * The conversion is deliberately separate from the legacy updater. An
 * administrator starts it from System Status, one table is converted per
 * batch, and a failed table stops the queue until an administrator retries.
 */
class WPJOBPORTAL_Storage_Engine_Service {

    const CRON_HOOK = 'wpjobportal_storage_engine_conversion';
    const STATE_OPTION = 'wpjobportal_storage_engine_conversion_state';
    const LOCK_OPTION = 'wpjobportal_storage_engine_conversion_lock';
    const LOCK_TTL = 43200;
    const BATCH_DELAY = 60;

    /**
     * The 28 MyISAM tables created by WP Job Portal core 2.6.0 and earlier.
     *
     * Add-on tables are intentionally excluded until the coordinated add-on
     * migration release.
     *
     * @return array
     */
    public static function target_tables() {
        return array(
            'wj_portal_activitylog',
            'wj_portal_careerlevels',
            'wj_portal_categories',
            'wj_portal_cities',
            'wj_portal_companies',
            'wj_portal_companycities',
            'wj_portal_config',
            'wj_portal_countries',
            'wj_portal_currencies',
            'wj_portal_emailtemplates',
            'wj_portal_emailtemplates_config',
            'wj_portal_employer_view_resume',
            'wj_portal_fieldsordering',
            'wj_portal_heighesteducation',
            'wj_portal_jobapply',
            'wj_portal_jobcities',
            'wj_portal_jobs',
            'wj_portal_jobseeker_view_company',
            'wj_portal_jobtypes',
            'wj_portal_resume',
            'wj_portal_resumeemployers',
            'wj_portal_resumefiles',
            'wj_portal_resumeinstitutes',
            'wj_portal_resumelanguages',
            'wj_portal_salaryrangetypes',
            'wj_portal_states',
            'wj_portal_system_errors',
            'wj_portal_users',
        );
    }

    /**
     * Begin or resume conversion after an administrator explicitly requests it.
     *
     * @return true|WP_Error
     */
    public static function start() {
        if (!current_user_can('wpjobportal')) {
            return new WP_Error('wpjobportal_storage_forbidden', __('You are not allowed to convert WP Job Portal tables.', 'wp-job-portal'));
        }

        $compatibility = self::compatibility();
        if (!$compatibility['supported']) {
            return new WP_Error('wpjobportal_storage_unsupported', $compatibility['message']);
        }

        $progress = self::get_progress();
        if ($progress['detected'] < 1) {
            return new WP_Error('wpjobportal_storage_missing', __('No WP Job Portal core tables were detected.', 'wp-job-portal'));
        }

        if (!empty($progress['missing_tables'])) {
            return new WP_Error(
                'wpjobportal_storage_incomplete',
                __('One or more expected core tables are missing. No tables were changed; review System Status before retrying.', 'wp-job-portal')
            );
        }

        if ($progress['remaining'] < 1) {
            self::save_state(array(
                'status' => 'success',
                'started_at' => $progress['started_at'],
                'finished_at' => current_time('mysql', true),
                'current_table' => '',
                'last_table' => $progress['last_table'],
                'last_error' => '',
            ));
            return true;
        }

        $state = self::get_state();
        self::save_state(array(
            'status' => 'scheduled',
            'started_at' => !empty($state['started_at']) ? $state['started_at'] : current_time('mysql', true),
            'finished_at' => '',
            'current_table' => '',
            'last_table' => !empty($state['last_table']) ? $state['last_table'] : '',
            'last_error' => '',
        ));
        self::schedule_next(5);

        return true;
    }

    /**
     * Pause future batches. A table already being altered cannot be interrupted.
     *
     * @return true|WP_Error
     */
    public static function pause() {
        if (!current_user_can('wpjobportal')) {
            return new WP_Error('wpjobportal_storage_forbidden', __('You are not allowed to pause WP Job Portal database maintenance.', 'wp-job-portal'));
        }

        $state = self::get_state();
        $state['status'] = 'paused';
        $state['current_table'] = '';
        self::save_state($state);
        wp_clear_scheduled_hook(self::CRON_HOOK);

        return true;
    }

    /**
     * Process Start/Pause before WordPress renders an admin page.
     *
     * System Status previously posted back to its page controller. By the time
     * that controller ran, WordPress (or another plugin) could already have
     * printed admin styles such as font-face declarations, making a redirect
     * impossible. admin-post.php provides the normal early action endpoint for
     * authenticated WordPress admin requests.
     */
    public static function handle_admin_action() {
        if (!current_user_can('wpjobportal')) {
            wp_die(
                esc_html__('You are not allowed to manage WP Job Portal database maintenance.', 'wp-job-portal'),
                esc_html__('Access denied', 'wp-job-portal'),
                array('response' => 403)
            );
        }

        check_admin_referer('wpjobportal_storage_engine_action', 'wpjobportal_storage_nonce');

        $storage_action = isset($_POST['wpjobportal_storage_action'])
            ? sanitize_key(wp_unslash($_POST['wpjobportal_storage_action']))
            : '';

        if ($storage_action === 'start') {
            $result = self::start();
            $result_code = is_wp_error($result) ? 'failed' : 'started';
        } elseif ($storage_action === 'pause') {
            $result = self::pause();
            $result_code = is_wp_error($result) ? 'failed' : 'paused';
        } else {
            $result_code = 'invalid';
        }

        wp_safe_redirect(
            add_query_arg(
                'wpjobportal_storage_result',
                $result_code,
                admin_url('admin.php?page=wpjobportal_systemstatus')
            )
        );
        exit;
    }

    /**
     * Ensure a resumable queue still has a future event after cron cleanup.
     */
    public static function maybe_continue() {
        if (!is_admin() || !current_user_can('wpjobportal')) {
            return;
        }

        $state = self::get_state();
        if (!in_array($state['status'], array('scheduled', 'running'), true)) {
            return;
        }

        $progress = self::get_progress();
        if (!empty($progress['missing_tables'])) {
            self::mark_failed(
                $state,
                '',
                __('One or more expected core tables are missing. Conversion was stopped.', 'wp-job-portal')
            );
            return;
        }

        if ($progress['remaining'] < 1) {
            self::mark_complete($state);
            return;
        }

        if (!wp_next_scheduled(self::CRON_HOOK) && !self::is_locked()) {
            self::schedule_next(self::BATCH_DELAY);
        }
    }

    /**
     * Convert one remaining core table and schedule the next batch.
     */
    public static function run_batch() {
        $state = self::get_state();
        if (!in_array($state['status'], array('scheduled', 'running'), true)) {
            return;
        }

        if (!self::acquire_lock()) {
            return;
        }

        try {
            $compatibility = self::compatibility();
            if (!$compatibility['supported']) {
                self::mark_failed($state, '', $compatibility['message']);
                return;
            }

            $progress = self::get_progress();
            if (!empty($progress['missing_tables'])) {
                self::mark_failed(
                    $state,
                    '',
                    __('One or more expected core tables are missing. Conversion was stopped.', 'wp-job-portal')
                );
                return;
            }

            if (empty($progress['remaining_tables'])) {
                self::mark_complete($state);
                return;
            }

            $table_suffix = reset($progress['remaining_tables']);
            $state['status'] = 'running';
            $state['current_table'] = $table_suffix;
            $state['last_error'] = '';
            self::save_state($state);

            $result = self::convert_table($table_suffix);
            if (is_wp_error($result)) {
                self::mark_failed($state, $table_suffix, $result->get_error_message());
                return;
            }

            $latest_state = self::get_state();
            $state['last_table'] = $table_suffix;
            $state['current_table'] = '';
            $state['last_error'] = '';
            if ($latest_state['status'] === 'paused') {
                $state['status'] = 'paused';
                self::save_state($state);
                return;
            }

            $progress = self::get_progress();
            if ($progress['remaining'] < 1) {
                self::mark_complete($state);
                return;
            }

            $state['status'] = 'scheduled';
            self::save_state($state);
            self::schedule_next(self::BATCH_DELAY);
        } finally {
            delete_option(self::LOCK_OPTION);
        }
    }

    /**
     * Return actual conversion progress from database metadata.
     *
     * @return array
     */
    public static function get_progress() {
        $state = self::get_state();
        $table_statuses = self::get_target_table_statuses();
        $remaining_tables = array();
        $converted_tables = array();
        $missing_tables = array_values(array_diff(self::target_tables(), array_keys($table_statuses)));

        foreach ($table_statuses as $suffix => $engine) {
            if ($engine === 'INNODB') {
                $converted_tables[] = $suffix;
            } else {
                $remaining_tables[] = $suffix;
            }
        }

        $total = count(self::target_tables());
        $detected = count($table_statuses);
        $converted = count($converted_tables);
        $remaining = count($remaining_tables);
        if ($total > 0 && $remaining === 0 && empty($missing_tables)) {
            $state['status'] = 'success';
        }

        return array(
            'status' => $state['status'],
            'total' => $total,
            'detected' => $detected,
            'converted' => $converted,
            'remaining' => $remaining,
            'percent' => $total > 0 ? (int) floor(($converted / $total) * 100) : 0,
            'remaining_tables' => $remaining_tables,
            'converted_tables' => $converted_tables,
            'missing_tables' => $missing_tables,
            'current_table' => $state['current_table'],
            'last_table' => $state['last_table'],
            'last_error' => WPJOBPORTAL_Redactor::redact($state['last_error']),
            'started_at' => $state['started_at'],
            'finished_at' => $state['finished_at'],
            'next_run' => wp_next_scheduled(self::CRON_HOOK),
            'locked' => self::is_locked(),
        );
    }

    /**
     * Check whether this database server exposes InnoDB.
     *
     * @return array
     */
    public static function compatibility() {
        global $wpdb;

        $rows = wpjobportaldb_direct::get_results('SHOW ENGINES', ARRAY_A);
        foreach ((array) $rows as $row) {
            if (isset($row['Engine'], $row['Support'])
                && strtoupper((string) $row['Engine']) === 'INNODB'
                && in_array(strtoupper((string) $row['Support']), array('YES', 'DEFAULT'), true)
            ) {
                return array(
                    'supported' => true,
                    'message' => __('InnoDB is available.', 'wp-job-portal'),
                );
            }
        }

        return array(
            'supported' => false,
            'message' => __('The database server does not report InnoDB support. No tables were changed.', 'wp-job-portal'),
        );
    }

    /**
     * Stop cron work and release only the transient conversion lock.
     */
    public static function deactivate() {
        wp_clear_scheduled_hook(self::CRON_HOOK);
        delete_option(self::LOCK_OPTION);

        $state = self::get_state();
        if (in_array($state['status'], array('scheduled', 'running'), true)) {
            $state['status'] = 'paused';
            $state['current_table'] = '';
            self::save_state($state);
        }
    }

    /**
     * @param string $table_suffix Core table name without WordPress prefix.
     * @return true|WP_Error
     */
    private static function convert_table($table_suffix) {
        global $wpdb;

        if (!in_array($table_suffix, self::target_tables(), true)) {
            return new WP_Error('wpjobportal_storage_invalid_table', __('The requested table is not part of the core conversion plan.', 'wp-job-portal'));
        }

        $table = $wpdb->prefix . $table_suffix;
        $engine = self::get_table_engine($table);
        if ($engine === null) {
            return new WP_Error(
                'wpjobportal_storage_table_missing',
                sprintf(/* translators: %s: table suffix */ __('Core table %s was not found.', 'wp-job-portal'), $table_suffix)
            );
        }

        if ($engine === 'INNODB') {
            return true;
        }

        if ($engine !== 'MYISAM') {
            return new WP_Error(
                'wpjobportal_storage_engine_unexpected',
                sprintf(/* translators: 1: table suffix, 2: engine */ __('Core table %1$s uses unsupported engine %2$s.', 'wp-job-portal'), $table_suffix, $engine)
            );
        }

        if (wpjobportaldb_direct::query($wpdb->prepare('ALTER TABLE %i ENGINE=InnoDB', $table)) === false) {
            $message = $wpdb->last_error ? $wpdb->last_error : __('Unknown database error.', 'wp-job-portal');
            return new WP_Error(
                'wpjobportal_storage_conversion_failed',
                sprintf(/* translators: 1: table suffix, 2: wpjobportal redactor redact message */ __('Could not convert %1$s: %2$s', 'wp-job-portal'), $table_suffix, WPJOBPORTAL_Redactor::redact($message))
            );
        }

        if (self::get_table_engine($table) !== 'INNODB') {
            return new WP_Error(
                'wpjobportal_storage_conversion_unverified',
                sprintf(/* translators: %s: table suffix */ __('The database did not confirm conversion of %s.', 'wp-job-portal'), $table_suffix)
            );
        }

        return true;
    }

    /**
     * @return array Table suffix => uppercase engine.
     */
    private static function get_target_table_statuses() {
        global $wpdb;

        $targets = array_fill_keys(self::target_tables(), true);
        $pattern = $wpdb->esc_like($wpdb->prefix . 'wj_portal_') . '%';
        $rows = wpjobportaldb_direct::get_results($wpdb->prepare('SHOW TABLE STATUS LIKE %s', $pattern), ARRAY_A);
        $statuses = array();

        foreach ((array) $rows as $row) {
            if (empty($row['Name'])) {
                continue;
            }
            $suffix = substr((string) $row['Name'], strlen($wpdb->prefix));
            if (!isset($targets[$suffix])) {
                continue;
            }
            $statuses[$suffix] = !empty($row['Engine']) ? strtoupper((string) $row['Engine']) : 'UNKNOWN';
        }

        $ordered = array();
        foreach (self::target_tables() as $suffix) {
            if (isset($statuses[$suffix])) {
                $ordered[$suffix] = $statuses[$suffix];
            }
        }
        return $ordered;
    }

    /**
     * @param string $table Full table name from the fixed core table list.
     * @return string|null
     */
    private static function get_table_engine($table) {
        global $wpdb;

        $row = wpjobportaldb_direct::get_row($wpdb->prepare('SHOW TABLE STATUS LIKE %s', $table), ARRAY_A);
        if (!$row || empty($row['Engine'])) {
            return null;
        }
        return strtoupper((string) $row['Engine']);
    }

    /**
     * @return array
     */
    private static function get_state() {
        $defaults = array(
            'status' => 'not_started',
            'started_at' => '',
            'finished_at' => '',
            'current_table' => '',
            'last_table' => '',
            'last_error' => '',
        );
        $state = get_option(self::STATE_OPTION, array());
        return wp_parse_args(is_array($state) ? $state : array(), $defaults);
    }

    /**
     * @param array $state Partial or complete state.
     */
    private static function save_state($state) {
        $state = wp_parse_args($state, self::get_state());
        $state['last_error'] = WPJOBPORTAL_Redactor::redact((string) $state['last_error']);
        update_option(self::STATE_OPTION, $state, false);
    }

    /**
     * @param array $state Current state.
     */
    private static function mark_complete($state) {
        $state['status'] = 'success';
        $state['finished_at'] = current_time('mysql', true);
        $state['current_table'] = '';
        $state['last_error'] = '';
        self::save_state($state);
        wp_clear_scheduled_hook(self::CRON_HOOK);
    }

    /**
     * @param array  $state Current state.
     * @param string $table_suffix Failed table.
     * @param string $message Redacted failure.
     */
    private static function mark_failed($state, $table_suffix, $message) {
        $state['status'] = 'failed';
        $state['current_table'] = '';
        $state['last_table'] = $table_suffix;
        $state['last_error'] = WPJOBPORTAL_Redactor::redact($message);
        self::save_state($state);
        wp_clear_scheduled_hook(self::CRON_HOOK);
    }

    /**
     * @return bool
     */
    private static function acquire_lock() {
        if (add_option(self::LOCK_OPTION, time(), '', false)) {
            return true;
        }

        $lock_time = absint(get_option(self::LOCK_OPTION, 0));
        if ($lock_time > 0 && (time() - $lock_time) < self::LOCK_TTL) {
            return false;
        }

        delete_option(self::LOCK_OPTION);
        return add_option(self::LOCK_OPTION, time(), '', false);
    }

    /**
     * @return bool
     */
    private static function is_locked() {
        $lock_time = absint(get_option(self::LOCK_OPTION, 0));
        return $lock_time > 0 && (time() - $lock_time) < self::LOCK_TTL;
    }

    /**
     * @param int $delay Seconds from now.
     */
    private static function schedule_next($delay) {
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_single_event(time() + max(1, absint($delay)), self::CRON_HOOK);
        }
    }
}
