<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * WordPress personal-data integration and bounded retention controls.
 */
class WPJOBPORTAL_Privacy_Service {

    const CRON_HOOK = 'wpjobportal_privacy_retention_cleanup';
    const SETTINGS_OPTION = 'wpjobportal_privacy_retention';
    const STATS_OPTION = 'wpjobportal_privacy_stats';
    const PAGE_SIZE = 50;

    public static function register() {
        add_filter('wp_privacy_personal_data_exporters', array(__CLASS__, 'register_exporter'));
        add_filter('wp_privacy_personal_data_erasers', array(__CLASS__, 'register_eraser'));
        add_action(self::CRON_HOOK, array(__CLASS__, 'run_retention'));
        add_action('wp_ajax_wpjobportal_privacy_settings', array(__CLASS__, 'save_settings_ajax'));
        // Defer scheduling until WordPress has loaded pluggable.php, which
        // defines wp_rand(). Plugin constructors run before that point.
        add_action('init', array(__CLASS__, 'ensure_schedule'), 5);
    }

    public static function register_exporter($exporters) {
        $exporters['wp-job-portal'] = array(
            'exporter_friendly_name' => __('WP Job Portal', 'wp-job-portal'),
            'callback' => array(__CLASS__, 'export_personal_data'),
        );
        return $exporters;
    }

    public static function register_eraser($erasers) {
        $erasers['wp-job-portal'] = array(
            'eraser_friendly_name' => __('WP Job Portal', 'wp-job-portal'),
            'callback' => array(__CLASS__, 'erase_personal_data'),
        );
        return $erasers;
    }

    public static function export_personal_data($email_address, $page = 1) {
        global $wpdb;

        $email_address = sanitize_email($email_address);
        $page = max(1, absint($page));
        $identity = self::identity_for_email($email_address);
        if (empty($identity['portal_uid']) && empty($identity['wp_uid']) && !self::has_visitor_data($email_address)) {
            return array('data' => array(), 'done' => true);
        }

        $offset = ($page - 1) * self::PAGE_SIZE;
        $uid = absint($identity['portal_uid']);
        $wp_uid = absint($identity['wp_uid']);
        $definitions = array();
        if ($uid > 0) {
            $definitions = array(
                array('table' => 'wj_portal_users', 'column' => 'id', 'value' => $uid, 'group' => 'profile', 'label' => __('Portal profile', 'wp-job-portal')),
                array('table' => 'wj_portal_resume', 'column' => 'uid', 'value' => $uid, 'group' => 'resumes', 'label' => __('Resumes', 'wp-job-portal')),
                array('table' => 'wj_portal_companies', 'column' => 'uid', 'value' => $uid, 'group' => 'companies', 'label' => __('Companies', 'wp-job-portal')),
                array('table' => 'wj_portal_jobs', 'column' => 'uid', 'value' => $uid, 'group' => 'jobs', 'label' => __('Jobs', 'wp-job-portal')),
                array('table' => 'wj_portal_jobapply', 'column' => 'uid', 'value' => $uid, 'group' => 'applications', 'label' => __('Applications', 'wp-job-portal')),
                array('table' => 'wj_portal_activitylog', 'column' => 'uid', 'value' => $uid, 'group' => 'activity', 'label' => __('Activity history', 'wp-job-portal')),
                array('table' => 'wj_portal_jobalertsetting', 'column' => 'uid', 'value' => $uid, 'group' => 'job-alerts', 'label' => __('Job alerts', 'wp-job-portal')),
            );
        }
        // Applications and alerts made without an account carry only the email address.
        $definitions[] = array('table' => 'wj_portal_resume', 'column' => 'email_address', 'value' => $email_address, 'group' => 'visitor-resumes', 'label' => __('Resumes sent without an account', 'wp-job-portal'));
        $definitions[] = array('table' => 'wj_portal_jobalertsetting', 'column' => 'contactemail', 'value' => $email_address, 'group' => 'visitor-job-alerts', 'label' => __('Job alerts without an account', 'wp-job-portal'));
        $data = array();
        $done = true;
        foreach ($definitions as $definition) {
            $table = $wpdb->prefix . $definition['table'];
            if (!self::table_exists($table)) {
                continue;
            }
            if (is_int($definition['value'])) {
                $query = $wpdb->prepare(
                    "SELECT * FROM %i WHERE %i = %d ORDER BY id ASC LIMIT %d OFFSET %d",
                    $table,
                    $definition['column'],
                    $definition['value'],
                    self::PAGE_SIZE,
                    $offset
                );
            } else {
                $query = $wpdb->prepare(
                    "SELECT * FROM %i WHERE uid = 0 AND %i = %s ORDER BY id ASC LIMIT %d OFFSET %d",
                    $table,
                    $definition['column'],
                    $definition['value'],
                    self::PAGE_SIZE,
                    $offset
                );
            }
            $rows = wpjobportaldb_direct::get_results($query, ARRAY_A);
            if (count($rows) === self::PAGE_SIZE) {
                $done = false;
            }
            foreach ($rows as $row) {
                $data[] = array(
                    'group_id' => 'wpjobportal-' . $definition['group'],
                    'group_label' => $definition['label'],
                    'item_id' => 'wpjobportal-' . $definition['group'] . '-' . absint($row['id']),
                    'data' => self::export_row($row),
                );
            }
        }

        /**
         * Allow active add-ons to append their own personal-data definitions.
         * Definitions use fixed table names and prepared integer ownership.
         */
        $addon_definitions = apply_filters('wpjobportal_privacy_export_definitions', array(), $uid, $wp_uid);
        foreach ((array) $addon_definitions as $definition) {
            if (!self::valid_export_definition($definition)) {
                continue;
            }
            $table = $wpdb->prefix . $definition['table'];
            if (!self::table_exists($table)) {
                continue;
            }
            $identity_value = isset($definition['identity']) && $definition['identity'] === 'wp_uid' ? $wp_uid : $uid;
            if ($identity_value < 1) {
                continue;
            }
            // Not every add-on table has an id column (application_stages is keyed by application_id).
            $key = self::primary_key($table);
            $query = $wpdb->prepare(
                "SELECT * FROM %i WHERE %i = %d ORDER BY %i ASC LIMIT %d OFFSET %d",
                $table,
                $definition['owner_column'],
                $identity_value,
                $key,
                self::PAGE_SIZE,
                $offset
            );
            $rows = wpjobportaldb_direct::get_results($query, ARRAY_A);
            if (count($rows) === self::PAGE_SIZE) {
                $done = false;
            }
            foreach ($rows as $row) {
                $group = sanitize_key($definition['group']);
                $data[] = array(
                    'group_id' => 'wpjobportal-' . $group,
                    'group_label' => sanitize_text_field($definition['label']),
                    'item_id' => 'wpjobportal-' . $group . '-' . absint(isset($row[$key]) ? $row[$key] : 0),
                    'data' => self::export_row($row),
                );
            }
        }

        if (!empty($data)) {
            self::record('exports', 'completed');
        }
        return array('data' => $data, 'done' => $done);
    }

    public static function erase_personal_data($email_address, $page = 1) {
        global $wpdb;

        $email_address = sanitize_email($email_address);
        $page = max(1, absint($page));
        if ($page > 1) {
            return array(
                'items_removed' => false,
                'items_retained' => false,
                'messages' => array(),
                'done' => true,
            );
        }

        $identity = self::identity_for_email($email_address);
        $uid = absint($identity['portal_uid']);
        $wp_uid = absint($identity['wp_uid']);
        if ($uid < 1 && $wp_uid < 1 && !self::has_visitor_data($email_address)) {
            return array(
                'items_removed' => false,
                'items_retained' => false,
                'messages' => array(),
                'done' => true,
            );
        }

        $anonymous_email = 'erased-' . substr(hash_hmac('sha256', $email_address, wp_salt('nonce')), 0, 20) . '@invalid.example';
        $removed = false;
        $errors = array();

        $removed = (bool) apply_filters('wpjobportal_privacy_erase_personal_data', $removed, $uid, $wp_uid);

        // Without an account: resumes sent and alerts set up with this email only.
        $files = self::erase_visitor_data($email_address, $anonymous_email);
        $alerts = self::erase_job_alerts($uid, $email_address);
        if ($wp_uid > 0) {
            // The profile photo folder is named after the WordPress user.
            $files += self::delete_data_folder('profile/profile_' . $wp_uid . '/profile');
        }
        $removed = $removed || $files > 0 || $alerts > 0;

        if ($uid < 1) {
            self::record('erasures', $removed ? 'completed' : 'failed', $files);
            return array(
                'items_removed' => $removed,
                'items_retained' => false,
                'messages' => array_merge($errors, self::erasure_summary($files, $alerts)),
                'done' => true,
            );
        }

        $users = $wpdb->prefix . 'wj_portal_users';
        $updated = wpjobportaldb_direct::update(
            $users,
            array(
                'photo' => '',
                'first_name' => __('Erased', 'wp-job-portal'),
                'last_name' => __('User', 'wp-job-portal'),
                'emailaddress' => $anonymous_email,
                'socialid' => '',
                'socialmedia' => '',
                'params' => '',
            ),
            array('id' => $uid),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s'),
            array('%d')
        );
        if ($updated === false) {
            $errors[] = __('The portal profile could not be anonymized.', 'wp-job-portal');
        } else {
            $removed = true;
        }

        $resume_table = $wpdb->prefix . 'wj_portal_resume';
        if (self::table_exists($resume_table)) {
            $resume_ids = wpjobportaldb_direct::get_col($wpdb->prepare(
                "SELECT id FROM %i WHERE uid = %d",
                $resume_table,
                $uid
            ));
            wpjobportaldb_direct::query($wpdb->prepare(
                "UPDATE %i
                 SET first_name = %s, last_name = %s, email_address = %s,
                     cell = '', photo = '', resume = '', skills = '', params = '',
                     keywords = '', searchable = 0, airesumesearchtext = '',
                     airesumesearchdescription = ''
                 WHERE uid = %d",
                $resume_table,
                __('Erased', 'wp-job-portal'),
                __('User', 'wp-job-portal'),
                $anonymous_email,
                $uid
            ));
            self::erase_resume_children($resume_ids);
            foreach ($resume_ids as $resume_id) {
                $files += self::delete_data_folder('jobseeker/resume_' . absint($resume_id));
            }
        }
        // Custom-field uploads on the profile are filed under the portal user.
        $files += self::delete_data_folder('profile/profile_' . $uid . '/custom_uploads');

        $jobapply = $wpdb->prefix . 'wj_portal_jobapply';
        if (self::table_exists($jobapply)) {
            wpjobportaldb_direct::query($wpdb->prepare(
                "UPDATE %i
                 SET uid = 0, cvid = NULL, comments = '', apply_message = ''
                 WHERE uid = %d",
                $jobapply,
                $uid
            ));
        }

        self::erase_addon_personal_data($uid, $wp_uid);
        self::record('erasures', $removed ? 'completed' : 'failed', $files);

        return array(
            'items_removed' => $removed,
            'items_retained' => true,
            'messages' => array_merge(
                $errors,
                self::erasure_summary($files, $alerts),
                array(__('Listing, payment and application records required for site integrity are retained in anonymized form.', 'wp-job-portal'))
            ),
            'done' => true,
        );
    }

    /** Whether resumes or job alerts exist for this email without an account. */
    private static function has_visitor_data($email_address) {
        global $wpdb;
        if ($email_address === '') {
            return false;
        }
        foreach (array('wj_portal_resume' => 'email_address', 'wj_portal_jobalertsetting' => 'contactemail') as $suffix => $column) {
            $table = $wpdb->prefix . $suffix;
            if (self::table_exists($table) && wpjobportaldb_direct::get_var($wpdb->prepare(
                "SELECT id FROM %i WHERE uid = 0 AND %i = %s LIMIT 1",
                $table,
                $column,
                $email_address
            ))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Resumes sent without an account (visitor and quick apply) under this email:
     * anonymized like an account's resumes, their files deleted.
     *
     * @return int Files deleted.
     */
    private static function erase_visitor_data($email_address, $anonymous_email) {
        global $wpdb;
        $resume_table = $wpdb->prefix . 'wj_portal_resume';
        if ($email_address === '' || !self::table_exists($resume_table)) {
            return 0;
        }
        $ids = array_map('absint', (array) wpjobportaldb_direct::get_col($wpdb->prepare(
            "SELECT id FROM %i WHERE uid = 0 AND email_address = %s",
            $resume_table,
            $email_address
        )));
        if (empty($ids)) {
            return 0;
        }
        $in = implode(',', array_fill(0, count($ids), '%d'));
        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $in is only %d placeholders, one per id
        wpjobportaldb_direct::query($wpdb->prepare(
            "UPDATE %i
             SET first_name = %s, last_name = %s, email_address = %s,
                 cell = '', photo = '', resume = '', skills = '', params = '',
                 keywords = '', searchable = 0, airesumesearchtext = '',
                 airesumesearchdescription = ''
             WHERE id IN ($in)",
            ...array_merge(array($resume_table, __('Erased', 'wp-job-portal'), __('User', 'wp-job-portal'), $anonymous_email), $ids)
        ));
        // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        self::erase_resume_children($ids);
        $jobapply = $wpdb->prefix . 'wj_portal_jobapply';
        if (self::table_exists($jobapply)) {
            wpjobportaldb_direct::query($wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $in is only %d placeholders, one per id
                "UPDATE %i SET cvid = NULL, comments = '', apply_message = '' WHERE uid = 0 AND cvid IN ($in)",
                ...array_merge(array($jobapply), $ids)
            ));
        }
        $files = 0;
        foreach ($ids as $id) {
            $files += self::delete_data_folder('jobseeker/resume_' . $id);
        }
        return $files;
    }

    /**
     * Job alert subscriptions of the account, and any set up with this email
     * without an account, with their cities.
     *
     * @return int Subscriptions deleted.
     */
    private static function erase_job_alerts($uid, $email_address) {
        global $wpdb;
        $table = $wpdb->prefix . 'wj_portal_jobalertsetting';
        if (!self::table_exists($table)) {
            return 0;
        }
        $ids = array_map('absint', (array) wpjobportaldb_direct::get_col($wpdb->prepare(
            "SELECT id FROM %i WHERE (uid > 0 AND uid = %d) OR (uid = 0 AND contactemail = %s)",
            $table,
            absint($uid),
            $email_address
        )));
        if (empty($ids)) {
            return 0;
        }
        $in = implode(',', array_fill(0, count($ids), '%d'));
        $cities = $wpdb->prefix . 'wj_portal_jobalertcities';
        if (self::table_exists($cities)) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $in is only %d placeholders, one per id
            wpjobportaldb_direct::query($wpdb->prepare("DELETE FROM %i WHERE alertid IN ($in)", ...array_merge(array($cities), $ids)));
        }
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $in is only %d placeholders, one per id
        wpjobportaldb_direct::query($wpdb->prepare("DELETE FROM %i WHERE id IN ($in)", ...array_merge(array($table), $ids)));
        return count($ids);
    }

    /**
     * Deletes a folder under uploads/<data directory>/data, with everything in it.
     * The path must resolve inside that data folder.
     *
     * @param string $relative e.g. jobseeker/resume_12
     * @return int Files deleted.
     */
    private static function delete_data_folder($relative) {
        $data_directory = (string) wpjobportal::$_config->getConfigurationByConfigName('data_directory');
        if ($data_directory === '' || !preg_match('#^[a-z]+/[a-z]+_\d+(/[a-z_]+)?$#', $relative)) {
            return 0;
        }
        $uploads = wp_upload_dir();
        $root = realpath($uploads['basedir'] . '/' . $data_directory . '/data');
        $path = realpath($uploads['basedir'] . '/' . $data_directory . '/data/' . $relative);
        if ($root === false || $path === false || strpos($path, $root . DIRECTORY_SEPARATOR) !== 0 || !is_dir($path)) {
            return 0;
        }
        $files = 0;
        $items = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($items as $item) {
            if ($item->isDir() && !$item->isLink()) {
                continue;
            }
            // The empty index.html guard files the plugin writes are not counted.
            if ($item->getFilename() !== 'index.html') {
                $files++;
            }
            wp_delete_file($item->getPathname());
        }
        // The emptied folders go through WP_Filesystem. Where it cannot start without
        // credentials they stay behind, empty: the person's files are already gone.
        if (!function_exists('WP_Filesystem')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
        if (WP_Filesystem() && $wp_filesystem) {
            $wp_filesystem->rmdir($path, true);
        }
        return $files;
    }

    /** The eraser's report lines for files and alerts. */
    private static function erasure_summary($files, $alerts) {
        $lines = array();
        if ($files > 0) {
            /* translators: %d: number of files */
            $lines[] = sprintf(_n('%d uploaded file (resume, photo or attachment) was deleted from the server.', '%d uploaded files (resumes, photos or attachments) were deleted from the server.', $files, 'wp-job-portal'), $files);
        }
        if ($alerts > 0) {
            /* translators: %d: number of job alert subscriptions */
            $lines[] = sprintf(_n('%d job alert subscription was deleted.', '%d job alert subscriptions were deleted.', $alerts, 'wp-job-portal'), $alerts);
        }
        return $lines;
    }

    public static function ensure_schedule() {
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_event(time() + wp_rand(300, 1800), 'daily', self::CRON_HOOK);
        }
    }

    public static function run_retention() {
        global $wpdb;
        $settings = self::settings();
        $deleted = 0;

        $activity_table = $wpdb->prefix . 'wj_portal_activitylog';
        if ($settings['activity_days'] > 0 && self::table_exists($activity_table)) {
            $cutoff = gmdate('Y-m-d H:i:s', time() - ($settings['activity_days'] * DAY_IN_SECONDS));
            $result = wpjobportaldb_direct::query($wpdb->prepare(
                "DELETE FROM %i WHERE created < %s",
                $activity_table,
                $cutoff
            ));
            if ($result !== false) {
                $deleted += absint($result);
            }
        }

        $message_table = $wpdb->prefix . 'wj_portal_messages';
        if ($settings['message_days'] > 0 && self::table_exists($message_table)) {
            $cutoff = gmdate('Y-m-d H:i:s', time() - ($settings['message_days'] * DAY_IN_SECONDS));
            $result = wpjobportaldb_direct::query($wpdb->prepare(
                "DELETE FROM %i WHERE created < %s",
                $message_table,
                $cutoff
            ));
            if ($result !== false) {
                $deleted += absint($result);
            }
        }

        self::prune_operational_audit($settings['audit_days']);
        self::record('retention_runs', 'completed', $deleted);
    }

    public static function save_settings_ajax() {
        if (!current_user_can('manage_options') && !current_user_can('wpjobportal')) {
            wp_send_json_error(array('message' => __('Access denied.', 'wp-job-portal')), 403);
        }
        check_ajax_referer('wpjobportal_privacy_settings', 'nonce');

        self::save_settings(array(
            'activity_days' => WPJOBPORTALrequest::getVar('activity_days', 'post'),
            'message_days' => WPJOBPORTALrequest::getVar('message_days', 'post'),
            'audit_days' => WPJOBPORTALrequest::getVar('audit_days', 'post'),
        ));
        wp_send_json_success(array('message' => __('Privacy retention settings saved.', 'wp-job-portal')));
    }

    /** U-1: the retention days as Settings shows and saves them (the same limits as before). */
    public static function current_settings() {
        return self::settings();
    }

    /** Saves the three retention periods; values missing from $input keep their current setting. */
    public static function save_settings($input) {
        $current = self::settings();
        $settings = array(
            'activity_days' => self::bounded_days(isset($input['activity_days']) ? $input['activity_days'] : $current['activity_days'], 3650),
            'message_days' => self::bounded_days(isset($input['message_days']) ? $input['message_days'] : $current['message_days'], 3650),
            'audit_days' => self::bounded_days(isset($input['audit_days']) ? $input['audit_days'] : $current['audit_days'], 365),
        );
        if ($settings !== $current) { // Settings posts every field on each save
            update_option(self::SETTINGS_OPTION, $settings, false);
            self::record('settings_updates', 'completed');
        }
        return $settings;
    }

    public static function get_status() {
        $stats = get_option(self::STATS_OPTION, array());
        if (!is_array($stats)) {
            $stats = array();
        }
        return array(
            'exporter_registered' => true,
            'eraser_registered' => true,
            'settings' => self::settings(),
            'next_run' => wp_next_scheduled(self::CRON_HOOK),
            'exports' => isset($stats['exports']) ? absint($stats['exports']) : 0,
            'erasures' => isset($stats['erasures']) ? absint($stats['erasures']) : 0,
            'retention_runs' => isset($stats['retention_runs']) ? absint($stats['retention_runs']) : 0,
            'last_at' => isset($stats['last_at']) ? sanitize_text_field($stats['last_at']) : '',
        );
    }

    private static function settings() {
        $defaults = array(
            'activity_days' => 365,
            'message_days' => 730,
            'audit_days' => 90,
        );
        $settings = get_option(self::SETTINGS_OPTION, array());
        if (!is_array($settings)) {
            $settings = array();
        }
        return array(
            'activity_days' => self::bounded_days(isset($settings['activity_days']) ? $settings['activity_days'] : $defaults['activity_days'], 3650),
            'message_days' => self::bounded_days(isset($settings['message_days']) ? $settings['message_days'] : $defaults['message_days'], 3650),
            'audit_days' => self::bounded_days(isset($settings['audit_days']) ? $settings['audit_days'] : $defaults['audit_days'], 365),
        );
    }

    private static function identity_for_email($email_address) {
        global $wpdb;
        $wp_user = get_user_by('email', $email_address);
        $wp_uid = $wp_user ? absint($wp_user->ID) : 0;
        $table = $wpdb->prefix . 'wj_portal_users';
        if (!self::table_exists($table)) {
            return array('wp_uid' => $wp_uid, 'portal_uid' => 0);
        }
        if ($wp_uid > 0) {
            $portal_uid = wpjobportaldb_direct::get_var($wpdb->prepare(
                "SELECT id FROM %i WHERE uid = %d LIMIT 1",
                $table,
                $wp_uid
            ));
        } else {
            $portal_uid = wpjobportaldb_direct::get_var($wpdb->prepare(
                "SELECT id FROM %i WHERE emailaddress = %s LIMIT 1",
                $table,
                $email_address
            ));
        }
        return array('wp_uid' => $wp_uid, 'portal_uid' => absint($portal_uid));
    }

    private static function export_row($row) {
        $data = array();
        foreach ((array) $row as $key => $value) {
            if (preg_match('/password|passwd|secret|token|private.?key|webhook.?secret/i', (string) $key)
                || is_array($value)
                || is_object($value)
            ) {
                continue;
            }
            $data[] = array(
                'name' => ucwords(str_replace('_', ' ', sanitize_key($key))),
                'value' => is_scalar($value) ? WPJOBPORTAL_Redactor::redact((string) $value) : '',
            );
        }
        return $data;
    }

    private static function valid_export_definition($definition) {
        return is_array($definition)
            && !empty($definition['table'])
            && preg_match('/^wj_portal_[a-z0-9_]+$/', $definition['table'])
            && !empty($definition['owner_column'])
            && preg_match('/^[a-z0-9_]+$/', $definition['owner_column'])
            && (!isset($definition['identity']) || in_array($definition['identity'], array('portal_uid', 'wp_uid'), true))
            && !empty($definition['group'])
            && !empty($definition['label']);
    }

    private static function erase_resume_children($resume_ids) {
        global $wpdb;
        $ids = array_values(array_filter(array_map('absint', (array) $resume_ids)));
        if (empty($ids)) {
            return;
        }
        foreach (array(
            'wj_portal_resumeaddresses',
            'wj_portal_resumeemployers',
            'wj_portal_resumeinstitutes',
            'wj_portal_resumelanguages',
            'wj_portal_resumefiles',
        ) as $suffix) {
            $table = $wpdb->prefix . $suffix;
            if (self::table_exists($table)) {
                wpjobportaldb_direct::query($wpdb->prepare(
                    'DELETE FROM %i WHERE resumeid IN (' . implode(',', array_fill(0, count($ids), '%d')) . ')',
                    ...array_merge(array($table), $ids)
                ));
            }
        }
    }

    private static function erase_addon_personal_data($uid, $wp_uid = 0) {
        global $wpdb;
        $definitions = array(
            array('table' => 'wj_portal_coverletters', 'column' => 'uid', 'mode' => 'delete'),
            array('table' => 'wj_portal_folders', 'column' => 'uid', 'mode' => 'delete'),
            array('table' => 'wj_portal_jobshortlist', 'column' => 'uid', 'mode' => 'delete'),
            array('table' => 'wj_portal_resumesearches', 'column' => 'uid', 'mode' => 'delete'),
            array('table' => 'wj_portal_jobsearches', 'column' => 'uid', 'mode' => 'delete'),
            array('table' => 'wj_portal_messages', 'column' => 'employerid', 'mode' => 'delete'),
            array('table' => 'wj_portal_messages', 'column' => 'jobseekerid', 'mode' => 'delete'),
        );
        foreach ($definitions as $definition) {
            $table = $wpdb->prefix . $definition['table'];
            if (!self::table_exists($table)) {
                continue;
            }
            wpjobportaldb_direct::query($wpdb->prepare(
                "DELETE FROM %i WHERE %i = %d",
                $table,
                $definition['column'],
                $uid
            ));
        }
        do_action('wpjobportal_privacy_personal_data_erased', $uid, $wp_uid);
    }

    private static function prune_operational_audit($days) {
        if ($days < 1 || !class_exists('WPJOBPORTAL_Audit_Service')) {
            return;
        }
        $events = get_option(WPJOBPORTAL_Audit_Service::OPTION, array());
        if (!is_array($events)) {
            return;
        }
        $cutoff = time() - ($days * DAY_IN_SECONDS);
        $events = array_values(array_filter($events, static function ($event) use ($cutoff) {
            $timestamp = !empty($event['time']) ? strtotime($event['time'] . ' UTC') : false;
            return $timestamp !== false && $timestamp >= $cutoff;
        }));
        update_option(WPJOBPORTAL_Audit_Service::OPTION, $events, false);
    }

    /** The first primary key column of a table, or id. */
    private static function primary_key($table) {
        global $wpdb;
        $column = wpjobportaldb_direct::get_var($wpdb->prepare("SHOW KEYS FROM %i WHERE Key_name = 'PRIMARY'", $table), 4);
        return (is_string($column) && preg_match('/^[a-z0-9_]+$/', $column)) ? $column : 'id';
    }

    private static function table_exists($table) {
        global $wpdb;
        return wpjobportaldb_direct::get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) === $table;
    }

    private static function bounded_days($value, $maximum) {
        $value = absint($value);
        return min($value, absint($maximum));
    }

    private static function record($counter, $outcome, $deleted = 0) {
        $stats = get_option(self::STATS_OPTION, array());
        if (!is_array($stats)) {
            $stats = array();
        }
        $stats[$counter] = isset($stats[$counter]) ? absint($stats[$counter]) + 1 : 1;
        $stats['last_outcome'] = sanitize_key($outcome);
        $stats['last_deleted'] = absint($deleted);
        $stats['last_at'] = current_time('mysql', true);
        update_option(self::STATS_OPTION, $stats, false);

        if (class_exists('WPJOBPORTAL_Audit_Service')) {
            WPJOBPORTAL_Audit_Service::record(
                'privacy_' . sanitize_key($counter),
                $outcome === 'completed' ? 'success' : 'warning',
                array('deleted' => absint($deleted))
            );
        }
    }
}
