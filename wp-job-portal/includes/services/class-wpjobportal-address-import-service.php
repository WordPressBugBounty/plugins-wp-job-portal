<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Safe, bounded Address Data import service.
 *
 * ZIP entries are read as strings and never extracted. SQL is restricted to
 * INSERT statements targeting the state/city tables. Imports are built in
 * staging tables and swapped only after every selected statement succeeds.
 */
class WPJOBPORTAL_Address_Import_Service {

    const STATS_OPTION = 'wpjobportal_address_import_stats';
    const MAX_ARCHIVE_BYTES = 33554432;
    const MAX_SQL_BYTES = 134217728;
    const MAX_COMPRESSION_RATIO = 200;

    public static function import_from_request() {
        if (!current_user_can('wpjobportal')
            && !current_user_can('manage_options')
            && !(isset(wpjobportal::$_common)
                && is_object(wpjobportal::$_common)
                && wpjobportal::$_common->wpjp_isadmin())
        ) {
            return self::failure('capability');
        }

        $nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (!is_string($nonce)
            || !wp_verify_nonce($nonce, 'wpjobportal_address_data_nonce')
        ) {
            return self::failure('nonce');
        }

        $keep = absint(WPJOBPORTALrequest::getVar('datakept', 'post'));
        $owner = absint(WPJOBPORTALrequest::getVar('fileowner', 'post'));
        $contains = absint(WPJOBPORTALrequest::getVar('datacontain', 'post'));
        if (!in_array($keep, array(1, 2), true)
            || !in_array($owner, array(1, 2), true)
            || !in_array($contains, array(1, 2, 3), true)
        ) {
            return self::failure('input_schema');
        }

        $source = self::resolve_source($owner);
        if (is_wp_error($source)) {
            return self::failure($source->get_error_code());
        }

        $sql = self::read_sql($source['path'], $source['extension']);
        if (is_wp_error($sql)) {
            return self::failure($sql->get_error_code());
        }

        $statements = self::parse_statements($sql);
        if (is_wp_error($statements)) {
            return self::failure($statements->get_error_code());
        }

        $result = self::stage_and_swap($statements, $keep === 1, $contains);
        if (is_wp_error($result)) {
            return self::failure($result->get_error_code());
        }

        self::record('completed', array(
            'statements' => count($statements),
            'tables' => $contains === 3 ? 2 : 1,
        ));
        return defined('WPJOBPORTAL_SAVED') ? WPJOBPORTAL_SAVED : true;
    }

    public static function get_status() {
        $stats = get_option(self::STATS_OPTION, array());
        if (!is_array($stats)) {
            $stats = array();
        }
        return array(
            'protected' => true,
            'max_archive_bytes' => self::MAX_ARCHIVE_BYTES,
            'max_sql_bytes' => self::MAX_SQL_BYTES,
            'completed' => isset($stats['completed']) ? absint($stats['completed']) : 0,
            'failed' => isset($stats['failed']) ? absint($stats['failed']) : 0,
            'last_at' => isset($stats['last_at']) ? sanitize_text_field($stats['last_at']) : '',
            'last_outcome' => isset($stats['last_outcome']) ? sanitize_key($stats['last_outcome']) : '',
            'last_reason' => isset($stats['last_reason']) ? sanitize_key($stats['last_reason']) : '',
        );
    }

    private static function resolve_source($owner) {
        if ($owner === 2) {
            $path = WP_PLUGIN_DIR . '/wp-job-portal-addressdata/includes/data/data.zip';
            if (!is_readable($path)) {
                return new WP_Error('bundled_file_missing');
            }
            $size = filesize($path);
            if ($size === false || $size < 1 || $size > self::MAX_ARCHIVE_BYTES) {
                return new WP_Error('archive_size');
            }
            return array('path' => $path, 'extension' => 'zip');
        }

        if (empty($_FILES['loadaddressdata'])
            || !is_array($_FILES['loadaddressdata'])
            || !isset($_FILES['loadaddressdata']['error'], $_FILES['loadaddressdata']['tmp_name'], $_FILES['loadaddressdata']['name'], $_FILES['loadaddressdata']['size'])
        ) {
            return new WP_Error('upload_missing');
        }

        $file = $_FILES['loadaddressdata'];
        $extension = strtolower(pathinfo(sanitize_file_name($file['name']), PATHINFO_EXTENSION));
        if (!in_array($extension, array('zip', 'sql'), true)) {
            return new WP_Error('file_type');
        }
        $maximum = $extension === 'zip' ? self::MAX_ARCHIVE_BYTES : self::MAX_SQL_BYTES;
        if ((int) $file['error'] !== UPLOAD_ERR_OK
            || !is_uploaded_file($file['tmp_name'])
            || (int) $file['size'] < 1
            || (int) $file['size'] > $maximum
        ) {
            return new WP_Error('upload_invalid');
        }
        return array('path' => $file['tmp_name'], 'extension' => $extension);
    }

    private static function read_sql($path, $extension) {
        if ($extension === 'sql') {
            $size = filesize($path);
            if ($size === false || $size < 1 || $size > self::MAX_SQL_BYTES) {
                return new WP_Error('sql_size');
            }
            $contents = file_get_contents($path);
            return is_string($contents) ? $contents : new WP_Error('sql_read');
        }

        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($path) !== true) {
                return new WP_Error('archive_open');
            }
            if ($zip->numFiles !== 1) {
                $zip->close();
                return new WP_Error('archive_entries');
            }
            $stat = $zip->statIndex(0);
            if (!is_array($stat)
                || empty($stat['name'])
                || basename($stat['name']) !== $stat['name']
                || strtolower(pathinfo($stat['name'], PATHINFO_EXTENSION)) !== 'sql'
                || empty($stat['size'])
                || $stat['size'] > self::MAX_SQL_BYTES
                || (!empty($stat['comp_size']) && ($stat['size'] / max(1, $stat['comp_size'])) > self::MAX_COMPRESSION_RATIO)
            ) {
                $zip->close();
                return new WP_Error('archive_entry');
            }
            $contents = $zip->getFromIndex(0, self::MAX_SQL_BYTES);
            $zip->close();
            return is_string($contents) ? $contents : new WP_Error('archive_read');
        }

        require_once ABSPATH . 'wp-admin/includes/class-pclzip.php';
        $archive = new PclZip($path);
        $list = $archive->listContent();
        if (!is_array($list)
            || count($list) !== 1
            || empty($list[0]['filename'])
            || basename($list[0]['filename']) !== $list[0]['filename']
            || strtolower(pathinfo($list[0]['filename'], PATHINFO_EXTENSION)) !== 'sql'
            || empty($list[0]['size'])
            || $list[0]['size'] > self::MAX_SQL_BYTES
            || (!empty($list[0]['compressed_size'])
                && ($list[0]['size'] / max(1, $list[0]['compressed_size'])) > self::MAX_COMPRESSION_RATIO)
        ) {
            return new WP_Error('archive_entry');
        }
        $extracted = $archive->extract(PCLZIP_OPT_EXTRACT_AS_STRING);
        if (!is_array($extracted)
            || count($extracted) !== 1
            || !isset($extracted[0]['content'])
            || !is_string($extracted[0]['content'])
        ) {
            return new WP_Error('archive_read');
        }
        return $extracted[0]['content'];
    }

    /**
     * Split SQL on semicolons outside quoted strings and reject every command
     * except INSERT into the two supported placeholders.
     */
    private static function parse_statements($sql) {
        if (!is_string($sql) || $sql === '' || strlen($sql) > self::MAX_SQL_BYTES) {
            return new WP_Error('sql_size');
        }
        if (strpos($sql, "\0") !== false || preg_match('/\/\*|^\s*(?:--|#)/m', $sql)) {
            return new WP_Error('sql_comments');
        }

        $statements = array();
        $buffer = '';
        $quote = '';
        $escaped = false;
        $length = strlen($sql);
        for ($index = 0; $index < $length; $index++) {
            $character = $sql[$index];
            if ($escaped) {
                $buffer .= $character;
                $escaped = false;
                continue;
            }
            if ($quote !== '' && $character === '\\') {
                $buffer .= $character;
                $escaped = true;
                continue;
            }
            if ($character === "'" || $character === '"') {
                if ($quote === '') {
                    $quote = $character;
                } elseif ($quote === $character) {
                    if ($index + 1 < $length && $sql[$index + 1] === $character) {
                        $buffer .= $character . $sql[++$index];
                        continue;
                    }
                    $quote = '';
                }
                $buffer .= $character;
                continue;
            }
            if ($character === ';' && $quote === '') {
                $statement = trim($buffer);
                if ($statement !== '') {
                    $statements[] = $statement;
                }
                $buffer = '';
                continue;
            }
            $buffer .= $character;
        }
        if ($quote !== '') {
            return new WP_Error('sql_quote');
        }
        if (trim($buffer) !== '') {
            $statements[] = trim($buffer);
        }
        if (empty($statements) || count($statements) > 500) {
            return new WP_Error('sql_statement_count');
        }

        foreach ($statements as $statement) {
            if (!preg_match(
                '/^INSERT\s+(?:IGNORE\s+)?INTO\s+`?(?:#__|[A-Za-z0-9_]*?)wj_portal_(states|cities)`?\s*(?:\([^;]*\))?\s+VALUES\s*\(.+\)\s*$/is',
                $statement
            )) {
                return new WP_Error('sql_command');
            }
        }
        return $statements;
    }

    private static function stage_and_swap($statements, $keep_existing, $contains) {
        global $wpdb;

        $selected = array();
        if ($contains === 1 || $contains === 3) {
            $selected[] = 'states';
        }
        if ($contains === 2 || $contains === 3) {
            $selected[] = 'cities';
        }

        $token = strtolower(wp_generate_password(8, false, false));
        $tables = array();
        foreach ($selected as $entity) {
            $original = $wpdb->prefix . 'wj_portal_' . $entity;
            $stage = substr($original, 0, 48) . '_imp_' . $token;
            $backup = substr($original, 0, 48) . '_bak_' . $token;
            $tables[$entity] = compact('original', 'stage', 'backup');

            $wpdb->query("DROP TABLE IF EXISTS `{$stage}`");
            if ($wpdb->query("CREATE TABLE `{$stage}` LIKE `{$original}`") === false
                || $wpdb->query("ALTER TABLE `{$stage}` ENGINE=InnoDB") === false
            ) {
                self::drop_staging($tables);
                return new WP_Error('stage_create');
            }
            if ($keep_existing
                && $wpdb->query("INSERT INTO `{$stage}` SELECT * FROM `{$original}`") === false
            ) {
                self::drop_staging($tables);
                return new WP_Error('stage_copy');
            }
        }

        $seen = array_fill_keys($selected, 0);
        foreach ($statements as $statement) {
            if (!preg_match('/wj_portal_(states|cities)/i', $statement, $match)) {
                self::drop_staging($tables);
                return new WP_Error('sql_target');
            }
            $entity = strtolower($match[1]);
            if (!isset($tables[$entity])) {
                continue;
            }
            $rewritten = preg_replace(
                '/`?(?:#__|[A-Za-z0-9_]*?)wj_portal_' . preg_quote($entity, '/') . '`?/i',
                '`' . $tables[$entity]['stage'] . '`',
                $statement,
                1
            );
            if ($keep_existing && !preg_match('/^INSERT\s+IGNORE\s+/i', $rewritten)) {
                $rewritten = preg_replace('/^INSERT\s+/i', 'INSERT IGNORE ', $rewritten, 1);
            }
            if ($wpdb->query($rewritten) === false) {
                self::drop_staging($tables);
                return new WP_Error('sql_execute');
            }
            $seen[$entity]++;
        }
        foreach ($seen as $count) {
            if ($count < 1) {
                self::drop_staging($tables);
                return new WP_Error('sql_missing_table');
            }
        }

        $rename = array();
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS `{$table['backup']}`");
            $rename[] = "`{$table['original']}` TO `{$table['backup']}`";
            $rename[] = "`{$table['stage']}` TO `{$table['original']}`";
        }
        if ($wpdb->query('RENAME TABLE ' . implode(', ', $rename)) === false) {
            self::drop_staging($tables);
            return new WP_Error('table_swap');
        }
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS `{$table['backup']}`");
        }
        return true;
    }

    private static function drop_staging($tables) {
        global $wpdb;
        foreach ($tables as $table) {
            if (!empty($table['stage'])) {
                $wpdb->query("DROP TABLE IF EXISTS `{$table['stage']}`");
            }
        }
    }

    private static function failure($reason) {
        self::record('failed', array('reason' => sanitize_key($reason)));
        return defined('WPJOBPORTAL_SAVE_ERROR') ? WPJOBPORTAL_SAVE_ERROR : false;
    }

    private static function record($outcome, $context) {
        $stats = get_option(self::STATS_OPTION, array());
        if (!is_array($stats)) {
            $stats = array();
        }
        $stats[$outcome] = isset($stats[$outcome]) ? absint($stats[$outcome]) + 1 : 1;
        $stats['last_outcome'] = sanitize_key($outcome);
        $stats['last_reason'] = isset($context['reason']) ? sanitize_key($context['reason']) : '';
        $stats['last_at'] = current_time('mysql', true);
        update_option(self::STATS_OPTION, $stats, false);

        if (class_exists('WPJOBPORTAL_Audit_Service')) {
            WPJOBPORTAL_Audit_Service::record(
                'address_import_' . sanitize_key($outcome),
                $outcome === 'completed' ? 'success' : 'warning',
                array(
                    'reason' => isset($context['reason']) ? sanitize_key($context['reason']) : '',
                    'statements' => isset($context['statements']) ? absint($context['statements']) : 0,
                    'tables' => isset($context['tables']) ? absint($context['tables']) : 0,
                )
            );
        }
    }
}
