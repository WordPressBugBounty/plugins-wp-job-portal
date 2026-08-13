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

        $integrity_issues = self::get_integrity_issues();
        if (!empty($integrity_issues)) {
            $report['status'] = 'blocked';
            $report['failed'] = $integrity_issues;
            return self::finish_report($report, false);
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

        for ($version = $installed_version + 1; $version <= $target_version; $version++) {
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
                if ($wpdb->query($statement) === false) {
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
        }

        return self::finish_report($report, $report['status'] === 'success');
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
             FROM `' . self::table_name() . '`
             ORDER BY id DESC
             LIMIT %d',
            $limit
        );

        return (array) $wpdb->get_results($query, ARRAY_A);
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

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT migration, checksum FROM `' . self::table_name() . '`
                 WHERE component = %s AND status = %s',
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
        return $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table))) === $table;
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
        if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table))) !== $table) {
            return '';
        }

        $value = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT configvalue FROM `{$table}` WHERE configname = %s LIMIT 1",
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

        $wpdb->insert(
            self::table_name(),
            array(
                'component' => self::COMPONENT,
                'migration' => $migration,
                'checksum' => '',
                'status' => 'success',
                'attempts' => 1,
                'started_at' => current_time('mysql', true),
                'finished_at' => current_time('mysql', true),
                'error' => null,
            ),
            array('%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s')
        );
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

        return $wpdb->get_row(
            $wpdb->prepare(
                'SELECT * FROM `' . self::table_name() . '` WHERE component = %s AND migration = %s LIMIT 1',
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

        $wpdb->replace(self::table_name(), $data, $formats);
    }

    /**
     * @param string $migration Migration number/name.
     * @param string $checksum File checksum.
     */
    private static function mark_success($migration, $checksum) {
        global $wpdb;

        $wpdb->update(
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

        $wpdb->replace(self::table_name(), $data, $formats);
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

        $wpdb->replace(self::table_name(), $data, $formats);
    }

    /**
     * Split legacy local SQL files at statement-ending semicolons.
     *
     * @param string $contents SQL file contents.
     * @return array
     */
    private static function split_sql($contents) {
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
        update_option(self::REPORT_OPTION, WPJOBPORTAL_Redactor::redact($report), false);
        return $result;
    }
}
