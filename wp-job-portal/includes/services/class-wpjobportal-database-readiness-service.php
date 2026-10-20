<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Installs the first Release 2 database indexes without changing the legacy
 * core or add-on update dispatchers.
 */
class WPJOBPORTAL_Database_Readiness_Service {

    const SCHEMA_VERSION = 1;
    const VERSION_OPTION = 'wpjobportal_database_readiness_version';
    const REPORT_OPTION = 'wpjobportal_database_readiness_report';
    const LOCK_OPTION = 'wpjobportal_database_readiness_lock';
    const RETRY_TRANSIENT = 'wpjobportal_database_readiness_retry';

    /**
     * Apply the index set once after the normal core updater has completed.
     *
     * This observer is deliberately independent of includes/updates/updates.php.
     * A failure is reported and retried later, but never blocks WP Job Portal.
     */
    public static function maybe_apply() {
        if (!is_admin() || !current_user_can('wpjobportal')) {
            return;
        }

        if (absint(get_option(self::VERSION_OPTION, 0)) >= self::SCHEMA_VERSION) {
            return;
        }

        if (get_transient(self::RETRY_TRANSIENT)) {
            return;
        }

        if (!self::core_database_is_ready()) {
            return;
        }

        if (!add_option(self::LOCK_OPTION, time(), '', false)) {
            $lock_time = absint(get_option(self::LOCK_OPTION, 0));
            if ($lock_time > 0 && (time() - $lock_time) < 600) {
                return;
            }
            delete_option(self::LOCK_OPTION);
            if (!add_option(self::LOCK_OPTION, time(), '', false)) {
                return;
            }
        }

        $report = array(
            'status' => 'success',
            'schema_version' => self::SCHEMA_VERSION,
            'started_at' => current_time('mysql', true),
            'finished_at' => '',
            'added' => array(),
            'already_present' => array(),
            'failed' => array(),
        );

        foreach (self::expected_indexes() as $table_suffix => $indexes) {
            self::apply_table_indexes($table_suffix, $indexes, $report);
        }

        if (!empty($report['failed'])) {
            $report['status'] = 'failed';
        }

        $remaining = self::get_missing_indexes();
        if (!empty($remaining)) {
            $report['status'] = 'failed';
            foreach ($remaining as $index_name) {
                if (!isset($report['failed'][$index_name])) {
                    $report['failed'][$index_name] = sprintf(
                        'Index %s is still missing.',
                        $index_name
                    );
                }
            }
        }

        $report['finished_at'] = current_time('mysql', true);
        $report = WPJOBPORTAL_Redactor::redact($report);
        update_option(self::REPORT_OPTION, $report, false);

        if ($report['status'] === 'success') {
            update_option(self::VERSION_OPTION, self::SCHEMA_VERSION, false);
            delete_transient(self::RETRY_TRANSIENT);
        } else {
            set_transient(self::RETRY_TRANSIENT, 1, 10 * MINUTE_IN_SECONDS);
        }

        delete_option(self::LOCK_OPTION);
    }

    /**
     * Return the logical names of planned indexes that are not present.
     *
     * @return array
     */
    public static function get_missing_indexes() {
        $missing = array();

        foreach (self::expected_indexes() as $table_suffix => $indexes) {
            $table = wpjobportal::$_db->prefix . $table_suffix;
            $actual = self::table_indexes($table);
            if ($actual === null) {
                continue;
            }

            foreach ($indexes as $name => $columns) {
                if (!self::has_column_sequence($actual, $columns)) {
                    $missing[] = $name;
                }
            }
        }

        return $missing;
    }

    /**
     * @return array
     */
    public static function get_last_report() {
        $report = get_option(self::REPORT_OPTION, array());
        return is_array($report) ? WPJOBPORTAL_Redactor::redact($report) : array();
    }

    /**
     * @return array
     */
    private static function expected_indexes() {
        return array(
            'wj_portal_jobs' => array(
                'jobs_uid' => array('uid'),
                'jobs_status_created' => array('status', 'created'),
                'jobs_publish_window' => array('startpublishing', 'stoppublishing'),
            ),
            'wj_portal_jobapply' => array(
                'jobapply_job_status' => array('jobid', 'action_status'),
                'jobapply_date' => array('apply_date'),
            ),
            'wj_portal_resume' => array(
                'resume_uid' => array('uid'),
                'resume_status_searchable' => array('status', 'searchable'),
                'resume_created' => array('created'),
            ),
            'wj_portal_companies' => array(
                'companies_status_created' => array('status', 'created'),
            ),
        );
    }

    /**
     * @return bool
     */
    private static function core_database_is_ready() {
        global $wpdb;

        $table = $wpdb->prefix . 'wj_portal_config';
        if (wpjobportaldb_direct::get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table))) !== $table) {
            return false;
        }

        $version = wpjobportaldb_direct::get_var(
            $wpdb->prepare("SELECT configvalue FROM %i WHERE configname = 'versioncode' LIMIT 1", $table)
        );
        $version = absint(str_replace('.', '', (string) $version));

        return $version >= 260;
    }

    /**
     * @param string $table_suffix Table name without the WordPress prefix.
     * @param array  $expected Expected index definitions.
     * @param array  $report Mutable result report.
     */
    private static function apply_table_indexes($table_suffix, $expected, &$report) {
        global $wpdb;

        $table = $wpdb->prefix . $table_suffix;
        $actual = self::table_indexes($table);
        if ($actual === null) {
            foreach ($expected as $name => $columns) {
                $report['failed'][$name] = sprintf('Required table %s was not found.', $table_suffix);
            }
            return;
        }

        $columns_in_table = self::table_columns($table);
        $additions = array();
        foreach ($expected as $name => $columns) {
            if (self::has_column_sequence($actual, $columns)) {
                $report['already_present'][] = $name;
                continue;
            }

            foreach ($columns as $column) {
                if (!isset($columns_in_table[$column])) {
                    $report['failed'][$name] = sprintf(
                        'Column %s.%s was not found.',
                        $table_suffix,
                        $column
                    );
                    continue 2;
                }
            }

            $index_name = $name;
            if (isset($actual[$index_name])) {
                $index_name .= '_r2';
                $suffix = 2;
                while (isset($actual[$index_name])) {
                    $index_name = $name . '_r2_' . $suffix;
                    $suffix++;
                }
            }

            $quoted_columns = array();
            foreach ($columns as $column) {
                $quoted_columns[] = '`' . $column . '`';
            }
            $additions[$name] = 'ADD INDEX `' . $index_name . '` (' . implode(', ', $quoted_columns) . ')';
        }

        if (empty($additions)) {
            return;
        }

        $sql = 'ALTER TABLE `' . $table . '` ' . implode(', ', array_values($additions));
        if (wpjobportaldb_direct::query($sql) === false) {
            $error = $wpdb->last_error ? $wpdb->last_error : 'Unknown database error';
            $error = WPJOBPORTAL_Redactor::redact($error);
            foreach ($additions as $name => $statement) {
                $report['failed'][$name] = $error;
            }
            return;
        }

        foreach ($additions as $name => $statement) {
            $report['added'][] = $name;
        }
    }

    /**
     * @param string $table Full table name.
     * @return array|null
     */
    private static function table_indexes($table) {
        global $wpdb;

        if (wpjobportaldb_direct::get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table))) !== $table) {
            return null;
        }

        $rows = wpjobportaldb_direct::get_results($wpdb->prepare('SHOW INDEX FROM %i', $table), ARRAY_A);
        $indexes = array();
        foreach ((array) $rows as $row) {
            $name = $row['Key_name'];
            $sequence = absint($row['Seq_in_index']);
            $indexes[$name][$sequence] = $row['Column_name'];
        }

        foreach ($indexes as $name => $columns) {
            ksort($columns);
            $indexes[$name] = array_values($columns);
        }

        return $indexes;
    }

    /**
     * @param string $table Full table name.
     * @return array
     */
    private static function table_columns($table) {
        global $wpdb;

        $rows = wpjobportaldb_direct::get_results($wpdb->prepare('SHOW COLUMNS FROM %i', $table), ARRAY_A);
        $columns = array();
        foreach ((array) $rows as $row) {
            $columns[$row['Field']] = true;
        }
        return $columns;
    }

    /**
     * @param array $indexes Existing indexes.
     * @param array $expected_columns Expected leading columns.
     * @return bool
     */
    private static function has_column_sequence($indexes, $expected_columns) {
        foreach ($indexes as $columns) {
            if (array_slice($columns, 0, count($expected_columns)) === $expected_columns) {
                return true;
            }
        }
        return false;
    }
}
