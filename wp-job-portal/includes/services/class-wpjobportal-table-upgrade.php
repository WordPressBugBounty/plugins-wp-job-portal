<?php
if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * W-15: bring old tables to the site's utf8mb4 collation, so emoji are not lost,
 * and money columns to DECIMAL(12,2), one step per scheduled run. Each ALTER can
 * take a while on a large table, so this is not part of a .sql migration. What is
 * left is read from the database every time, which makes the job resumable, and
 * tables of add-ons that are not installed are simply not there to convert.
 *
 * Starts only once the 2.8.0 update has run, so it follows the update's backup prompt.
 */
class WPJOBPORTAL_Table_Upgrade {

    const CRON_HOOK = 'wpjobportal_table_upgrade';
    const BATCH = 'table_upgrade_280';
    const MAX_ATTEMPTS = 3;

    public static function register() {
        add_action(self::CRON_HOOK, array(__CLASS__, 'run_step'));
        add_action('admin_init', array(__CLASS__, 'maybe_schedule'));
    }

    /** @return array{0:string,1:string} charset and collation, the site's own */
    private static function target() {
        global $wpdb;
        return array('utf8mb4', $wpdb->collate ? $wpdb->collate : 'utf8mb4_unicode_ci');
    }

    /** The money columns: floats round, and ints cannot hold cents. */
    private static function money_columns() {
        return array(
            'invoices' => array('amount'),
            'packages' => array('price', 'discount'),
            'companies' => array('price'), 'departments' => array('price'), 'employer_view_resume' => array('price'),
            'jobalertsetting' => array('price'), 'jobapply' => array('price'), 'jobs' => array('price'),
            'jobseeker_view_company' => array('price'), 'resume' => array('price'), 'resumesearches' => array('price'),
        );
    }

    /** The ALTER statements still to run. */
    public static function remaining() {
        global $wpdb;
        $like = $wpdb->esc_like($wpdb->prefix . 'wj_portal_') . '%';
        $steps = array();
        if ($wpdb->charset === 'utf8mb4') {
            list($charset, $collation) = self::target();
            $tables = $wpdb->get_col($wpdb->prepare(
                "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME LIKE %s AND TABLE_TYPE = 'BASE TABLE' AND (TABLE_COLLATION <> %s"
                // 2.7 declared some columns "CHARACTER SET utf8" inside utf8mb4 tables
                . " OR EXISTS (SELECT 1 FROM information_schema.COLUMNS c WHERE c.TABLE_SCHEMA = TABLES.TABLE_SCHEMA AND c.TABLE_NAME = TABLES.TABLE_NAME AND c.CHARACTER_SET_NAME IS NOT NULL AND c.CHARACTER_SET_NAME <> %s)) ORDER BY TABLE_NAME",
                $like, $collation, $charset
            ));
            foreach ((array) $tables as $table) {
                $steps[] = "ALTER TABLE `" . esc_sql($table) . "` CONVERT TO CHARACTER SET " . $charset . " COLLATE " . esc_sql($collation);
            }
        }
        foreach (self::money_columns() as $suffix => $columns) {
            foreach ($columns as $column) {
                $table = $wpdb->prefix . 'wj_portal_' . $suffix;
                $col = $wpdb->get_row($wpdb->prepare(
                    "SELECT DATA_TYPE AS type, IS_NULLABLE AS nullable FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s AND COLUMN_NAME = %s",
                    $table, $column
                ));
                if ($col && $col->type !== 'decimal') {
                    $steps[] = "ALTER TABLE `" . esc_sql($table) . "` MODIFY `" . esc_sql($column) . "` DECIMAL(12,2) " . ($col->nullable === 'YES' ? 'NULL' : 'NOT NULL');
                }
            }
        }
        // quick_apply was nullable, and every "quick_apply <> 1" in the resume lists silently drops a
        // NULL row. Fill NULLs first (so the ALTER cannot fail in strict mode), then forbid them.
        foreach (array('resume', 'jobapply') as $suffix) {
            $table = $wpdb->prefix . 'wj_portal_' . $suffix;
            $nullable = $wpdb->get_var($wpdb->prepare(
                "SELECT IS_NULLABLE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s AND COLUMN_NAME = 'quick_apply'",
                $table
            ));
            if ($nullable !== 'YES') {
                continue;
            }
            if ($wpdb->get_var("SELECT 1 FROM `" . esc_sql($table) . "` WHERE quick_apply IS NULL LIMIT 1")) {
                $steps[] = "UPDATE `" . esc_sql($table) . "` SET quick_apply = 0 WHERE quick_apply IS NULL";
            } else {
                $steps[] = "ALTER TABLE `" . esc_sql($table) . "` MODIFY `quick_apply` TINYINT(1) NOT NULL DEFAULT 0";
            }
        }
        return $steps;
    }

    public static function maybe_schedule() {
        if (version_compare((string) wpjobportal::$_config->getConfigValue('versioncode'), '2.8.0', '<')
            || wp_next_scheduled(self::CRON_HOOK) || !class_exists('WPJOBPORTAL_Migration_Service')) {
            return;
        }
        $entry = WPJOBPORTAL_Migration_Service::get_batch_entry(self::BATCH);
        if ($entry && ($entry->status === 'success' || ($entry->status === 'failed' && (int) $entry->attempts >= self::MAX_ATTEMPTS))) {
            return;
        }
        wp_schedule_single_event(time() + 60, self::CRON_HOOK);
    }

    /** One ALTER, then the next run is scheduled by the next admin request or from here. */
    public static function run_step() {
        global $wpdb;
        WPJOBPORTAL_Migration_Service::with_lock(self::BATCH, function () use ($wpdb) {
            // S-4: older CVs get their random names here too, 50 at a time
            if (class_exists('WPJOBPORTAL_Resume_File_Store') && WPJOBPORTAL_Resume_File_Store::protect_batch() > 0) {
                wp_schedule_single_event(time() + 60, self::CRON_HOOK);
                return;
            }
            $steps = self::remaining();
            if (empty($steps)) {
                WPJOBPORTAL_Migration_Service::record_batch(self::BATCH, 'success');
                return;
            }
            WPJOBPORTAL_Migration_Service::record_batch(self::BATCH, 'running');
            if (wpjobportaldb_direct::query($steps[0]) === false) {
                WPJOBPORTAL_Migration_Service::record_batch(self::BATCH, 'failed', $wpdb->last_error);
                return;
            }
            wp_schedule_single_event(time() + 60, self::CRON_HOOK);
        });
    }
}
