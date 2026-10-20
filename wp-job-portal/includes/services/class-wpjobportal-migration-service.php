<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Local, auditable and retry-safe database migrations.
 */
class WPJOBPORTAL_Migration_Service {

    const COMPONENT = 'core';
    const REPORT_OPTION = 'wpjobportal_last_migration_report';
    const RECONCILE_OPTION = 'wpjobportal_migration_observer_signature';
    const LOCK_OPTION = 'wpjobportal_migration_lock';

    /*
     * One request at a time.
     *
     * The first admin request after an update and the heartbeat that arrives
     * with it both found the database behind and ran the same migrations
     * together: the second one failed on "Duplicate entry 'core-baseline-270'"
     * and printed it on screen. Pro's and Marketplace's runners did the same.
     * add_option() only succeeds for the request that creates the row, so it
     * works as a lock; a lock older than ten minutes is a request that died,
     * and is taken over (as Database_Readiness does). One lock per component,
     * so the products' runners share the code but never wait on Core's.
     *
     * @param string   $component Ledger component: 'core', 'pro', 'credits'...
     * @param callable $callback  The run.
     * @param mixed    $busy      Returned when another request holds the lock;
     *                            the next admin request checks again.
     * @return mixed
     */
    public static function with_lock($component, $callback, $busy = false) {
        $option = self::LOCK_OPTION . '_' . sanitize_key($component);
        if (!add_option($option, time(), '', false)) {
            $lock_time = absint(get_option($option, 0));
            if ($lock_time > 0 && (time() - $lock_time) < 600) {
                return $busy;
            }
            delete_option($option);
            if (!add_option($option, time(), '', false)) {
                return $busy;
            }
        }
        try {
            return call_user_func($callback);
        } finally {
            delete_option($option);
        }
    }

    /** Run pending local migrations on the first privileged request after update. */
    public static function maybe_run_current() {
        if (!is_admin() || !current_user_can('wpjobportal') || !class_exists('wpjobportal')) {
            return;
        }

        $target_version = absint(wpjobportal::$_currentversion);
        if ($target_version > 0 && self::get_installed_version() < $target_version) {
            self::run($target_version);
        }
    }


    /*
     * The Newest Jobs page was provisioned as "wp-job-portal-newset-jobs".
     *
     * New installs get the right spelling (postinstallation model). Existing
     * sites keep the old one on purpose: renaming it was rehearsed on a 2.6.0
     * site and every job and company link under it - /newset-jobs/job/<alias>,
     * /newset-jobs/company/<alias> - answered 404 afterwards, and links carrying
     * a query string lost it. Those are the addresses Google and old emails
     * hold, so a typo in a slug costs less than renaming it.
     *
     * redirect_renamed_page() below stays for sites a pre-release build already
     * renamed.
     */

    /**
     * Send the old page address to the new one.
     *
     * WordPress does this for posts and not for pages: wp_old_slug_redirect()
     * asks for get_query_var('name'), and a page is matched on 'pagename', so
     * it returns before it looks anything up. Checked rather than assumed - the
     * old URL answered 404 with _wp_old_slug sitting on the page.
     *
     * Renaming a published page without this would break every bookmark and
     * every link to it, which is too high a price for correcting a typo. With
     * it, the old address answers 301 and the rename costs nobody anything.
     *
     * Deliberately narrow: pages only, only on a 404, and only for a slug some
     * page actually records as its own former name.
     */
    public static function redirect_renamed_page() {
        if (!is_404() || is_admin()) {
            return;
        }
        /*
         * From the address, not from the query.
         *
         * get_query_var('pagename') is empty on a 404 for a page that does not
         * exist - measured, not assumed - so there is nothing there to match
         * against. The last segment of the request path is what the visitor
         * actually typed, and that is what the old slug has to be compared to.
         */
        $path = isset($_SERVER['REQUEST_URI']) ? wp_parse_url(sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])), PHP_URL_PATH) : '';
        $requested = is_string($path) ? basename(untrailingslashit($path)) : '';
        if ('' === $requested) {
            return;
        }

        global $wpdb;
        $page_id = absint(wpjobportaldb_direct::get_var($wpdb->prepare(
            "SELECT meta.post_id
               FROM %i AS meta
              INNER JOIN %i AS page ON page.ID = meta.post_id
              WHERE meta.meta_key = '_wp_old_slug' AND meta.meta_value = %s
                AND page.post_type = 'page' AND page.post_status = 'publish'
              ORDER BY meta.post_id ASC LIMIT 1",
            $wpdb->postmeta,
            $wpdb->posts,
            $requested
        )));
        if ($page_id < 1) {
            return;
        }

        $target = get_permalink($page_id);
        if (!$target) {
            return;
        }

        wp_safe_redirect($target, 301);
        exit;
    }

    /**
     * Reconcile migrations already executed by the proven legacy updater.
     *
     * This method never runs SQL migration files and never changes the stored
     * core version. It verifies the observable result of Release 2 migrations,
     * then repairs only their diagnostic ledger rows.
     */
    public static function reconcile_legacy_upgrade() {
        if (!is_admin() || !current_user_can('wpjobportal')) {
            return;
        }

        $installed_version = self::get_installed_version();
        if ($installed_version < 259) {
            return;
        }

        $last_version = self::get_config_version('last_version');
        $baseline_version = ($last_version > 0 && $last_version < $installed_version)
            ? $last_version
            : $installed_version;
        $signature = $baseline_version . ':' . $installed_version;

        if ((string) get_option(self::RECONCILE_OPTION, '') === $signature) {
            return;
        }

        self::with_lock(self::COMPONENT, function () use ($installed_version, $baseline_version, $signature) {
            self::reconcile_unlocked($installed_version, $baseline_version, $signature);
        });
    }

    private static function reconcile_unlocked($installed_version, $baseline_version, $signature) {
        if (!self::ensure_ledger_table()) {
            return;
        }

        self::record_baseline($baseline_version);

        $report = array(
            'status' => 'success',
            'from' => $baseline_version,
            'to' => $installed_version,
            'started_at' => current_time('mysql', true),
            'finished_at' => '',
            'applied' => array(),
            'skipped' => array(),
            'failed' => array(),
        );

        $migrations = array(
            259 => self::get_config_value('migration_framework_version') === '1',
            260 => $installed_version >= 260,
        );

        foreach ($migrations as $version => $verified) {
            if ($version <= $baseline_version || $version > $installed_version) {
                continue;
            }

            $file = WPJOBPORTAL_PLUGIN_PATH . 'includes/updates/sql/' . $version . '.sql';
            if (!$verified || !is_readable($file)) {
                $report['status'] = 'warning';
                $report['failed'][] = sprintf(
                    'Could not verify the result of legacy core migration %d.',
                    $version
                );
                continue;
            }

            $checksum = hash_file('sha256', $file);
            if ($checksum === false) {
                $report['status'] = 'warning';
                $report['failed'][] = sprintf(
                    'Could not checksum legacy core migration %d.',
                    $version
                );
                continue;
            }

            self::mark_reconciled_success((string) $version, $checksum);
            $report['applied'][] = (string) $version;
        }

        $result = $report['status'] === 'success';
        self::finish_report($report, $result);
        if ($result) {
            update_option(self::RECONCILE_OPTION, $signature, false);
        }
    }

    /**
     * Run every local core SQL migration between the installed and target versions.
     *
     * @param int|string $target_version Numeric internal version, for example 260.
     * @return bool
     */
    public static function run($target_version) {
        return self::with_lock(self::COMPONENT, function () use ($target_version) {
            return self::run_unlocked($target_version);
        });
    }

    private static function run_unlocked($target_version) {
        global $wpdb;

        $target_version = absint($target_version);
        if ($target_version < 1) {
            return false;
        }

        if (!self::ensure_ledger_table()) {
            update_option(
                self::REPORT_OPTION,
                array(
                    'status' => 'failed',
                    'from' => 0,
                    'to' => $target_version,
                    'started_at' => current_time('mysql', true),
                    'finished_at' => current_time('mysql', true),
                    'applied' => array(),
                    'skipped' => array(),
                    'failed' => array('The migration ledger table could not be created.'),
                ),
                false
            );
            return false;
        }

        $installed_version = self::get_installed_version();
        self::record_baseline($installed_version);

        $report = array(
            'status' => 'success',
            'from' => $installed_version,
            'to' => $target_version,
            'started_at' => current_time('mysql', true),
            'finished_at' => '',
            'applied' => array(),
            'skipped' => array(),
            'failed' => array(),
        );

        // A migration that has ALREADY been applied may legitimately differ from
        // the file now on disk: a later plugin release can ship a revised copy of
        // a historical .sql file. Treating that as tampering used to block every
        // future migration permanently, which punishes an ordinary upgrade — the
        // work is already in the database and cannot be re-run in any case.
        //
        // Drift on an applied migration is therefore reported and carried, not
        // fatal. The tamper case that still matters is a migration about to be
        // applied whose ledger row disagrees, and that is caught in the loop
        // below, where it does stop the run.
        $integrity_issues = self::get_integrity_issues();
        if (!empty($integrity_issues)) {
            $report['status'] = 'warning';
            $report['drift'] = $integrity_issues;
        }

        if ($installed_version > $target_version) {
            $report['status'] = 'blocked';
            $report['failed'][] = sprintf(
                'Database version %d is newer than plugin version %d.',
                $installed_version,
                $target_version
            );
            return self::finish_report($report, false);
        }

        // The migrations that ship between the installed and target versions,
        // in order. Versions are not consecutive (1.1.9 was followed by 2.0.0,
        // 2.6.0 by 2.7.0), so only the files that exist are applied; the target's
        // own file must be among them, because it is what writes versioncode.
        $versions = array();
        foreach ((array) glob(WPJOBPORTAL_PLUGIN_PATH . 'includes/updates/sql/*.sql') as $path) {
            $name = basename((string) $path, '.sql');
            if (ctype_digit($name) && (int) $name > $installed_version && (int) $name <= $target_version) {
                $versions[] = (int) $name;
            }
        }
        sort($versions);
        if ($installed_version < $target_version && !in_array($target_version, $versions, true)) {
            $versions[] = $target_version;
        }

        foreach ($versions as $version) {
            $migration = (string) $version;
            $file = WPJOBPORTAL_PLUGIN_PATH . 'includes/updates/sql/' . $migration . '.sql';

            if (!is_readable($file)) {
                $message = sprintf('Required local migration %s.sql is missing or unreadable.', $migration);
                self::mark_failed($migration, '', $message);
                $report['status'] = 'failed';
                $report['failed'][] = $message;
                break;
            }

            $checksum = hash_file('sha256', $file);
            if ($checksum === false) {
                $message = sprintf('Migration %s.sql could not be checksummed.', $migration);
                self::mark_failed($migration, '', $message);
                $report['status'] = 'failed';
                $report['failed'][] = $message;
                break;
            }
            $ledger_entry = self::get_entry($migration);

            if ($ledger_entry && $ledger_entry->status === 'success') {
                if (hash_equals((string) $ledger_entry->checksum, (string) $checksum)) {
                    $report['skipped'][] = $migration;
                    continue;
                }

                $message = sprintf('Migration %s.sql changed after it was applied.', $migration);
                self::mark_failed($migration, $checksum, $message);
                $report['status'] = 'failed';
                $report['failed'][] = $message;
                break;
            }

            self::mark_running($migration, $checksum);

            $contents = file_get_contents($file);
            if ($contents === false) {
                $message = sprintf('Migration %s.sql could not be read.', $migration);
                self::mark_failed($migration, $checksum, $message);
                $report['status'] = 'failed';
                $report['failed'][] = $message;
                break;
            }

            $statements = self::split_sql($contents);
            $migration_ok = true;

            foreach ($statements as $statement) {
                $statement = str_replace('#__', $wpdb->prefix, $statement);
                if (wpjobportaldb_direct::query($statement) === false) {
                    $message = sprintf(
                        'Migration %s.sql failed: %s',
                        $migration,
                        $wpdb->last_error ? $wpdb->last_error : 'Unknown database error'
                    );
                    $message = WPJOBPORTAL_Redactor::redact($message);
                    self::mark_failed($migration, $checksum, $message);
                    $report['status'] = 'failed';
                    $report['failed'][] = $message;
                    $migration_ok = false;
                    break;
                }
            }

            if (!$migration_ok) {
                break;
            }

            self::mark_success($migration, $checksum);
            $report['applied'][] = $migration;

            if ($migration === '270') {
                self::repair_legacy_field_options();
                self::repair_legacy_slashed_text();
            }
        }

        // A warning means historical drift was noted but every pending
        // migration still applied, so the run succeeded.
        return self::finish_report(
            $report,
            in_array($report['status'], array('success', 'warning'), true)
        );
    }

    /**
     * Give custom fields saved by 2.6.0 and older their options back.
     *
     * Those versions stored the option list of a drop-down, radio or checkbox
     * field with the apostrophes still slashed - ["Day","O\'Clock"] - which is
     * not JSON, so json_decode() returned null and the field was shown with no
     * options at all. 2.7.0 saves the list with wp_json_encode().
     *
     * Done here rather than in 270.sql because the check needs JSON_VALID(),
     * which MySQL 5.6 does not have, and one failing statement fails the whole
     * migration. A row is only rewritten when it is invalid now and removing
     * the slashes makes it valid, so it is safe to run more than once.
     *
     * @return void
     */
    private static function repair_legacy_field_options() {
        global $wpdb;

        $table = $wpdb->prefix . 'wj_portal_fieldsordering';
        $rows = wpjobportaldb_direct::get_results(
            $wpdb->prepare(
                "SELECT id, userfieldparams FROM %i WHERE isuserfield = 1 AND userfieldparams LIKE %s",
                $table,
                '%' . $wpdb->esc_like("\\'") . '%'
            )
        );

        foreach ((array) $rows as $row) {
            json_decode((string) $row->userfieldparams);
            if (json_last_error() === JSON_ERROR_NONE) {
                continue;
            }
            $options = json_decode(str_replace("\\'", "'", (string) $row->userfieldparams), true);
            if (!is_array($options)) {
                continue;
            }
            wpjobportaldb_direct::update(
                $table,
                array('userfieldparams' => wp_json_encode($options, JSON_UNESCAPED_UNICODE)),
                array('id' => (int) $row->id),
                array('%s'),
                array('%d')
            );
        }
    }

    /**
     * Remove the slashes 2.6.0 and older saved into text.
     *
     * Those versions stored some input as it arrived, magic-quoted, so a name
     * typed as O'Neil was saved as O\'Neil and every screen and email showed
     * the backslash. 2.7.0 unslashes input before it is saved, and this cleans
     * what is already there:
     *
     * - portal first and last names, set to the WordPress user's own first and
     *   last name when that is exactly the stored name without its slashes;
     * - settings, quick-apply messages and the resume AI search text, unslashed
     *   only when they contain \' or \" - the mark the slashing left. Settings
     *   that hold JSON are left alone, where \" is ordinary JSON;
     * - the same names inside the stored activity log HTML.
     *
     * A cleaned value no longer carries the mark, so running it again changes
     * nothing.
     *
     * @return void
     */
    private static function repair_legacy_slashed_text() {
        global $wpdb;

        $slashed = '/\\\\[\'"]/';
        $like = '%' . $wpdb->esc_like('\\') . '%';

        $table = $wpdb->prefix . 'wj_portal_users';
        $rows = wpjobportaldb_direct::get_results(
            $wpdb->prepare(
                'SELECT id, uid, first_name, last_name FROM %i WHERE uid > 0 AND (first_name LIKE %s OR last_name LIKE %s)',
                $table,
                $like,
                $like
            )
        );
        foreach ((array) $rows as $row) {
            $fix = array();
            foreach (array('first_name', 'last_name') as $column) {
                $stored = (string) $row->$column;
                $wp_value = (string) get_user_meta((int) $row->uid, $column, true);
                if ($stored !== $wp_value && wp_unslash($stored) === $wp_value) {
                    $fix[$column] = $wp_value;
                }
            }
            if (!empty($fix)) {
                wpjobportaldb_direct::update($table, $fix, array('id' => (int) $row->id), null, array('%d'));
            }
        }

        $columns = array(
            'wj_portal_config' => array('configname', array('configvalue')),
            'wj_portal_jobapply' => array('id', array('apply_message')),
            'wj_portal_resume' => array('id', array('airesumesearchtext', 'airesumesearchdescription')),
        );
        foreach ($columns as $short => $spec) {
            $table = $wpdb->prefix . $short;
            $key = $spec[0];
            foreach ($spec[1] as $column) {
                $rows = wpjobportaldb_direct::get_results(
                    $wpdb->prepare('SELECT %i AS k, %i AS v FROM %i WHERE %i LIKE %s', $key, $column, $table, $column, $like)
                );
                foreach ((array) $rows as $row) {
                    $value = (string) $row->v;
                    if (!preg_match($slashed, $value)) {
                        continue;
                    }
                    if ($short === 'wj_portal_config') {
                        $json = json_decode($value);
                        if (is_array($json) || is_object($json)) {
                            continue;
                        }
                    }
                    wpjobportaldb_direct::update(
                        $table,
                        array($column => wp_unslash($value)),
                        array($key => $row->k),
                        array('%s'),
                        array($key === 'id' ? '%d' : '%s')
                    );
                }
            }
        }

        // The activity log keeps each entry as finished HTML, with the slashed
        // name already escaped inside it: O\&#039;Neil. Only that pair is
        // touched - a slash in front of an escaped quote.
        $table = $wpdb->prefix . 'wj_portal_activitylog';
        foreach (array('&#039;', '&quot;') as $quote) {
            wpjobportaldb_direct::query(
                $wpdb->prepare(
                    'UPDATE %i SET description = REPLACE(description, %s, %s) WHERE description LIKE %s',
                    $table,
                    '\\' . $quote,
                    $quote,
                    '%' . $wpdb->esc_like('\\' . $quote) . '%'
                )
            );
        }
    }

    /**
     * Create the ledger independently so it can record its own first migration.
     *
     * @return bool
     */
    public static function ensure_ledger_table() {
        global $wpdb;

        $table = self::table_name();
        $charset_collate = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta(
            "CREATE TABLE {$table} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                component varchar(64) NOT NULL,
                migration varchar(191) NOT NULL,
                checksum char(64) NOT NULL DEFAULT '',
                status varchar(20) NOT NULL,
                attempts smallint(5) unsigned NOT NULL DEFAULT 0,
                started_at datetime DEFAULT NULL,
                finished_at datetime DEFAULT NULL,
                error text DEFAULT NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY component_migration (component, migration),
                KEY status (status)
            ) ENGINE=InnoDB {$charset_collate};"
        );

        return self::table_exists();
    }

    /**
     * Return recent ledger rows for diagnostics.
     *
     * @param int $limit Maximum rows.
     * @return array
     */
    public static function get_recent_entries($limit = 20) {
        global $wpdb;

        if (!self::table_exists()) {
            return array();
        }

        $limit = max(1, min(100, absint($limit)));
        $query = $wpdb->prepare(
            'SELECT component, migration, checksum, status, attempts, started_at, finished_at, error
             FROM %i
             ORDER BY id DESC
             LIMIT %d',
            self::table_name(),
            $limit
        );

        return (array) wpjobportaldb_direct::get_results($query, ARRAY_A);
    }

    /**
     * Return the last migration result.
     *
     * @return array
     */
    public static function get_last_report() {
        $report = get_option(self::REPORT_OPTION, array());
        return is_array($report) ? WPJOBPORTAL_Redactor::redact($report) : array();
    }

    /**
     * Verify that applied migration files still exist and retain their checksum.
     *
     * @return array Human-readable, redacted issues.
     */
    public static function get_integrity_issues() {
        global $wpdb;

        if (!self::table_exists()) {
            return array(__('Migration ledger is missing.', 'wp-job-portal'));
        }

        $rows = wpjobportaldb_direct::get_results(
            $wpdb->prepare(
                'SELECT migration, checksum FROM %i
                 WHERE component = %s AND status = %s',
                self::table_name(),
                self::COMPONENT,
                'success'
            ),
            ARRAY_A
        );
        $issues = array();

        foreach ((array) $rows as $row) {
            if (!ctype_digit((string) $row['migration'])) {
                continue;
            }

            $file = WPJOBPORTAL_PLUGIN_PATH . 'includes/updates/sql/' . $row['migration'] . '.sql';
            if (!is_readable($file)) {
                $issues[] = sprintf('Applied migration %s.sql is missing.', $row['migration']);
                continue;
            }

            $checksum = hash_file('sha256', $file);
            if ($checksum === false || !hash_equals((string) $row['checksum'], (string) $checksum)) {
                $issues[] = sprintf('Applied migration %s.sql has changed.', $row['migration']);
            }
        }

        return WPJOBPORTAL_Redactor::redact($issues);
    }

    /**
     * Show a persistent failure notice without exposing database details.
     */
    public static function render_admin_notice() {
        if (!current_user_can('wpjobportal')) {
            return;
        }

        $report = self::get_last_report();
        if (
            empty($report)
            || !isset($report['status'])
            || !in_array($report['status'], array('failed', 'blocked'), true)
        ) {
            return;
        }

        $url = admin_url('admin.php?page=wpjobportal_systemstatus');
        echo '<div class="notice notice-error"><p>';
        echo esc_html__('WP Job Portal could not complete a database migration. Existing data was left in place.', 'wp-job-portal');
        echo ' <a href="' . esc_url($url) . '">' . esc_html__('View System Status', 'wp-job-portal') . '</a>';
        echo '</p></div>';
    }

    /**
     * @return string
     */
    public static function table_name() {
        global $wpdb;
        return $wpdb->prefix . 'wj_portal_migrations';
    }

    /**
     * @return bool
     */
    public static function table_exists() {
        global $wpdb;
        $table = self::table_name();
        return wpjobportaldb_direct::get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table))) === $table;
    }

    /**
     * @return int
     */
    private static function get_installed_version() {
        $version = self::get_config_value('versioncode');
        if ($version === '') {
            return 100;
        }

        return absint(str_replace('.', '', $version));
    }

    /**
     * @param string $name Configuration name.
     * @return string
     */
    private static function get_config_value($name) {
        global $wpdb;

        $table = $wpdb->prefix . 'wj_portal_config';
        if (wpjobportaldb_direct::get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table))) !== $table) {
            return '';
        }

        $value = wpjobportaldb_direct::get_var(
            $wpdb->prepare(
                "SELECT configvalue FROM %i WHERE configname = %s LIMIT 1",
                $table,
                $name
            )
        );

        return is_scalar($value) ? (string) $value : '';
    }

    /**
     * @param string $name Version-bearing configuration name.
     * @return int
     */
    private static function get_config_version($name) {
        return absint(str_replace('.', '', self::get_config_value($name)));
    }

    /**
     * @param int $version Installed baseline version.
     */
    private static function record_baseline($version) {
        global $wpdb;

        $migration = 'baseline-' . absint($version);
        if (self::get_entry($migration)) {
            return;
        }

        // IGNORE: if a stale lock was taken over while its request was still
        // alive, both may get here; the row the other one wrote is this row.
        $now = current_time('mysql', true);
        wpjobportaldb_direct::query($wpdb->prepare(
            'INSERT IGNORE INTO %i (component, migration, checksum, status, attempts, started_at, finished_at, error) VALUES (%s, %s, %s, %s, %d, %s, %s, NULL)',
            self::table_name(),
            self::COMPONENT,
            $migration,
            '',
            'success',
            1,
            $now,
            $now
        ));
    }

    /**
     * @param string $migration Migration number/name.
     * @return object|null
     */
    private static function get_entry($migration) {
        global $wpdb;

        if (!self::table_exists()) {
            return null;
        }

        return wpjobportaldb_direct::get_row(
            $wpdb->prepare(
                'SELECT * FROM %i WHERE component = %s AND migration = %s LIMIT 1',
                self::table_name(),
                self::COMPONENT,
                $migration
            )
        );
    }

    /**
     * @param string $migration Migration number/name.
     * @param string $checksum File checksum.
     */
    private static function mark_running($migration, $checksum) {
        global $wpdb;

        $entry = self::get_entry($migration);
        $attempts = $entry ? absint($entry->attempts) + 1 : 1;

        $data = array(
            'component' => self::COMPONENT,
            'migration' => $migration,
            'checksum' => $checksum,
            'status' => 'running',
            'attempts' => $attempts,
            'started_at' => current_time('mysql', true),
            'finished_at' => null,
            'error' => null,
        );
        $formats = array('%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s');
        if ($entry) {
            $data = array_merge(array('id' => absint($entry->id)), $data);
            array_unshift($formats, '%d');
        }

        wpjobportaldb_direct::replace(self::table_name(), $data, $formats);
    }

    /**
     * @param string $migration Migration number/name.
     * @param string $checksum File checksum.
     */
    private static function mark_success($migration, $checksum) {
        global $wpdb;

        wpjobportaldb_direct::update(
            self::table_name(),
            array(
                'checksum' => $checksum,
                'status' => 'success',
                'finished_at' => current_time('mysql', true),
                'error' => null,
            ),
            array('component' => self::COMPONENT, 'migration' => $migration),
            array('%s', '%s', '%s', '%s'),
            array('%s', '%s')
        );
    }

    /**
     * Record a migration that the legacy updater already completed.
     *
     * Existing attempt counts are preserved because reconciliation is not a
     * second execution attempt.
     *
     * @param string $migration Migration number/name.
     * @param string $checksum File checksum.
     */
    private static function mark_reconciled_success($migration, $checksum) {
        global $wpdb;

        $entry = self::get_entry($migration);
        $data = array(
            'component' => self::COMPONENT,
            'migration' => $migration,
            'checksum' => $checksum,
            'status' => 'success',
            'attempts' => $entry ? max(1, absint($entry->attempts)) : 1,
            'started_at' => $entry && $entry->started_at
                ? $entry->started_at
                : current_time('mysql', true),
            'finished_at' => current_time('mysql', true),
            'error' => null,
        );
        $formats = array('%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s');
        if ($entry) {
            $data = array_merge(array('id' => absint($entry->id)), $data);
            array_unshift($formats, '%d');
        }

        wpjobportaldb_direct::replace(self::table_name(), $data, $formats);
    }

    /**
     * @param string $migration Migration number/name.
     * @param string $checksum File checksum.
     * @param string $message Safe error message.
     */
    private static function mark_failed($migration, $checksum, $message) {
        global $wpdb;

        $entry = self::get_entry($migration);
        $attempts = $entry ? absint($entry->attempts) : 1;

        $data = array(
            'component' => self::COMPONENT,
            'migration' => $migration,
            'checksum' => $checksum,
            'status' => 'failed',
            'attempts' => max(1, $attempts),
            'started_at' => $entry && $entry->started_at ? $entry->started_at : current_time('mysql', true),
            'finished_at' => current_time('mysql', true),
            'error' => WPJOBPORTAL_Redactor::redact($message),
        );
        $formats = array('%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s');
        if ($entry) {
            $data = array_merge(array('id' => absint($entry->id)), $data);
            array_unshift($formats, '%d');
        }

        wpjobportaldb_direct::replace(self::table_name(), $data, $formats);
    }

    /**
     * Split legacy local SQL files at statement-ending semicolons.
     *
     * Public because it is the only splitter in the plugin that reads these
     * files correctly, and there were three. The other two joined a
     * statement's lines with implode('') - no separator at all - so a
     * statement written over several lines arrived at MySQL with its words
     * glued together, and they counted a semicolon at the end of a comment
     * line as the end of a statement. Every migration up to 260 happened to
     * be one statement per line, which is the only reason that never showed.
     *
     * @param string $contents SQL file contents.
     * @return array
     */
    public static function split_sql($contents) {
        $lines = preg_split('/\r\n|\r|\n/', $contents);
        $buffer = array();
        $statements = array();

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || strpos($trimmed, '--') === 0 || strpos($trimmed, '# ') === 0) {
                continue;
            }

            $buffer[] = $line;
            if (preg_match('/;\s*$/', $line)) {
                $statement = trim(implode("\n", $buffer));
                if ($statement !== '') {
                    $statements[] = $statement;
                }
                $buffer = array();
            }
        }

        $remaining = trim(implode("\n", $buffer));
        if ($remaining !== '') {
            $statements[] = $remaining;
        }

        return $statements;
    }

    /**
     * @param array $report Migration report.
     * @param bool $result Return value.
     * @return bool
     */
    private static function finish_report($report, $result) {
        $report['finished_at'] = current_time('mysql', true);

        /*
         * An update calls run() more than once, and every call after the one
         * that did the work finds nothing to do. Writing that empty report
         * replaced the one that said what the update applied (2.6.0 -> 2.7.0
         * showed as 270 -> 270, nothing applied). A run with nothing to do
         * leaves a successful report in place; a failed or warning report is
         * still replaced, so its notice clears once the cause is gone.
         */
        $nothing_done = $report['status'] === 'success' && empty($report['applied']) && empty($report['failed'])
            && (int) $report['from'] === (int) $report['to'];
        if ($nothing_done) {
            $previous = get_option(self::REPORT_OPTION, array());
            if (is_array($previous) && isset($previous['status']) && $previous['status'] === 'success') {
                return $result;
            }
        }

        update_option(self::REPORT_OPTION, WPJOBPORTAL_Redactor::redact($report), false);

        /*
         * Say so when migrations have actually run.
         *
         * A Core migration can change data an add-on keeps its own copy of -
         * company and job ownership, which the Recruit Suite mirrors into its
         * team tables. Core cannot do
         * that mirroring itself: those tables belong to the suite and are not
         * there on a site without it, and a migration that writes to a table
         * that may not exist fails the whole upgrade on exactly the installs
         * it was written for.
         *
         * So Core says what it did and each add-on answers for its own tables.
         * Fired only when something was applied, so an ordinary page load on
         * an up-to-date site costs nothing.
         */
        if (!empty($report['applied'])) {
            do_action('wpjobportal_migrations_applied', (array) $report['applied'], $report);
        }

        return $result;
    }
}
