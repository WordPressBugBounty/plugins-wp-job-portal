<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Durable ownership registry and conservative cleanup for newly installed
 * sample records. Historical, unmarked data is deliberately ignored.
 */
class WPJOBPORTAL_Sample_Data_Service {

    const REGISTRY_OPTION = 'wpjobportal_sample_data_registry_v1';
    const ASSET_STATUS_OPTION = 'wpjobportal_sample_asset_status_v1';
    const POST_BATCH_META = '_wpjobportal_sample_data_batch';
    const POST_TYPE_META = '_wpjobportal_sample_data_type';
    const MAX_ARCHIVE_BYTES = 26214400;
    const MAX_EXTRACTED_BYTES = 104857600;
    const MAX_ARCHIVE_ENTRIES = 500;
    const MAX_ENTRY_BYTES = 10485760;

    private static $batch_id = '';
    private static $transactional_tables = array();

    /**
     * Start one sample-install batch.
     *
     * @return string
     */
    public static function begin_batch() {
        if ('' === self::$batch_id) {
            self::$batch_id = wp_generate_uuid4();
        }

        $registry = self::registry();
        if (!isset($registry['batches'][self::$batch_id])) {
            $registry['batches'][self::$batch_id] = array(
                'created_at' => current_time('mysql', true),
                'created_by' => get_current_user_id(),
                'mode' => WPJOBPORTAL_Product_Mode_Service::get_mode(),
                'records' => array(),
            );
            self::save_registry($registry);
        }

        return self::$batch_id;
    }

    /**
     * Download and extract the optional sample-image archive without using or
     * deleting a shared ABSPATH/tmp directory. Only bounded image entries below
     * the expected data/ root are accepted.
     *
     * @param string $destination WP Job Portal upload-data directory.
     * @return true|WP_Error
     */
    public static function install_assets($destination) {
        $destination = untrailingslashit(wp_normalize_path((string) $destination));
        $uploads = wp_upload_dir(null, false);
        $uploads_base = !empty($uploads['basedir'])
            ? untrailingslashit(wp_normalize_path($uploads['basedir']))
            : '';

        if ('' === $destination
            || '' === $uploads_base
            || 0 !== strpos($destination . '/', $uploads_base . '/')
            || is_link($destination)
        ) {
            return self::asset_error('invalid_destination');
        }

        if (!function_exists('download_url')) {
            do_action('wpjobportal_load_wp_file');
        }
        if (!function_exists('download_url')) {
            return self::asset_error('download_unavailable');
        }

        $archive = download_url('https://d3ewuxxxzqg2dd.cloudfront.net/sample_data.zip');
        if (is_wp_error($archive)) {
            $archive = download_url('https://wpjobportal-sampledata.s3.amazonaws.com/sample_data.zip');
        }
        if (is_wp_error($archive)) {
            return self::asset_error('download_failed');
        }

        $archive = wp_normalize_path($archive);
        $result = self::extract_asset_archive($archive, $destination);
        wp_delete_file($archive);

        if (is_wp_error($result)) {
            return self::asset_error($result->get_error_code());
        }

        if (class_exists('WPJOBPORTAL_Audit_Service')) {
            WPJOBPORTAL_Audit_Service::record('sample_assets_installed', 'success', array());
        }
        self::save_asset_status('success', '');
        return true;
    }

    /**
     * Record the exact ID and an installation-time fingerprint.
     *
     * @param string $type Entity type.
     * @param int    $id   Entity ID.
     * @return bool
     */
    public static function record_entity($type, $id) {
        $type = sanitize_key($type);
        $id = absint($id);
        if (!in_array($type, self::types(), true) || $id <= 0) {
            return false;
        }

        $row = self::entity_row($type, $id);
        if (empty($row)) {
            return false;
        }

        $batch_id = self::begin_batch();
        $registry = self::registry();
        if (!isset($registry['batches'][$batch_id]['records'][$type])) {
            $registry['batches'][$batch_id]['records'][$type] = array();
        }

        $registry['batches'][$batch_id]['records'][$type][(string) $id] = array(
            'signature' => self::signature($row),
            'children' => array(),
        );

        if ('pages' === $type) {
            update_post_meta($id, self::POST_BATCH_META, $batch_id);
            update_post_meta($id, self::POST_TYPE_META, $type);
        }

        self::save_registry($registry);
        return true;
    }

    /**
     * Store the exact child-row sets created within the completed request.
     * A later child addition causes the parent to be skipped during cleanup.
     */
    public static function finalize_batch() {
        if ('' === self::$batch_id) {
            return;
        }

        $registry = self::registry();
        if (empty($registry['batches'][self::$batch_id]['records'])) {
            return;
        }

        foreach ($registry['batches'][self::$batch_id]['records'] as $type => &$records) {
            foreach ($records as $id => &$record) {
                $record['children'] = self::current_children($type, absint($id));
            }
            unset($record);
        }
        unset($records);

        self::save_registry($registry);
    }

    /**
     * Return a read-only cleanup preview.
     *
     * @return array
     */
    public static function get_preview() {
        $plan = self::build_plan();
        $counts = array();
        $total = 0;
        $eligible = 0;
        $skipped = 0;

        foreach (self::types() as $type) {
            $counts[$type] = array('tracked' => 0, 'eligible' => 0, 'skipped' => 0);
        }

        foreach ($plan as $type => $records) {
            foreach ($records as $record) {
                $counts[$type]['tracked']++;
                $total++;
                if (!empty($record['eligible'])) {
                    $counts[$type]['eligible']++;
                    $eligible++;
                } else {
                    $counts[$type]['skipped']++;
                    $skipped++;
                }
            }
        }

        return array(
            'has_markers' => $total > 0,
            'tracked' => $total,
            'eligible' => $eligible,
            'skipped' => $skipped,
            'counts' => $counts,
        );
    }

    /**
     * Delete only records that still match strong plugin-owned markers.
     * Users, settings, payments, credits and uploaded files are never deleted.
     *
     * @return array
     */
    public static function cleanup() {
        $result = array(
            'deleted' => 0,
            'skipped' => 0,
            'failed' => 0,
            'by_type' => array(),
        );

        foreach (self::types() as $type) {
            $result['by_type'][$type] = array('deleted' => 0, 'skipped' => 0, 'failed' => 0);
        }

        foreach (array('applications', 'jobs', 'resumes', 'companies', 'pages') as $type) {
            $plan = self::build_plan();
            if (empty($plan[$type])) {
                continue;
            }

            foreach ($plan[$type] as $id => $record) {
                if (empty($record['eligible'])) {
                    $result['skipped']++;
                    $result['by_type'][$type]['skipped']++;
                    continue;
                }

                $deleted = self::delete_entity($type, absint($id), $record);
                if ($deleted) {
                    self::remove_registry_record($record['batch_id'], $type, absint($id));
                    $result['deleted']++;
                    $result['by_type'][$type]['deleted']++;
                } else {
                    $result['failed']++;
                    $result['by_type'][$type]['failed']++;
                }
            }
        }

        if (class_exists('WPJOBPORTAL_Audit_Service')) {
            WPJOBPORTAL_Audit_Service::record(
                'sample_data_cleanup',
                $result['failed'] > 0 ? 'failed' : 'success',
                array(
                    'deleted' => absint($result['deleted']),
                    'skipped' => absint($result['skipped']),
                    'failed' => absint($result['failed']),
                )
            );
        }

        return $result;
    }

    /**
     * @return array
     */
    public static function get_status() {
        $preview = self::get_preview();
        $asset_status = get_option(self::ASSET_STATUS_OPTION, array());
        if (!is_array($asset_status)) {
            $asset_status = array();
        }
        return array(
            'tracked' => absint($preview['tracked']),
            'eligible' => absint($preview['eligible']),
            'skipped' => absint($preview['skipped']),
            'assets' => array(
                'status' => isset($asset_status['status']) ? sanitize_key($asset_status['status']) : 'never',
                'reason' => isset($asset_status['reason']) ? sanitize_key($asset_status['reason']) : '',
                'updated_at' => isset($asset_status['updated_at']) ? sanitize_text_field($asset_status['updated_at']) : '',
            ),
        );
    }

    /**
     * Build dependency-aware eligibility without changing the database.
     *
     * @return array
     */
    private static function build_plan() {
        global $wpdb;

        $registry = self::registry();
        $plan = array_fill_keys(self::types(), array());

        foreach ($registry['batches'] as $batch_id => $batch) {
            if (empty($batch['records']) || !is_array($batch['records'])) {
                continue;
            }
            foreach ($batch['records'] as $type => $records) {
                if (!isset($plan[$type]) || !is_array($records)) {
                    continue;
                }
                foreach ($records as $id => $record) {
                    $id = absint($id);
                    $row = self::entity_row($type, $id);
                    $eligible = !empty($row)
                        && isset($record['signature'])
                        && hash_equals((string) $record['signature'], self::signature($row));

                    if ($eligible && 'pages' === $type) {
                        $eligible = (string) get_post_meta($id, self::POST_BATCH_META, true) === (string) $batch_id
                            && 'pages' === (string) get_post_meta($id, self::POST_TYPE_META, true)
                            && !self::page_is_in_use($id);
                    }

                    if ($eligible && in_array($type, array('companies', 'jobs', 'resumes'), true)) {
                        $eligible = self::children_match(
                            isset($record['children']) && is_array($record['children']) ? $record['children'] : array(),
                            self::current_children($type, $id)
                        );
                    }

                    if ($eligible && 'pages' !== $type && !self::entity_tables_are_transactional($type)) {
                        $eligible = false;
                    }

                    $plan[$type][$id] = array(
                        'eligible' => $eligible,
                        'batch_id' => (string) $batch_id,
                        'children' => isset($record['children']) && is_array($record['children']) ? $record['children'] : array(),
                    );
                }
            }
        }

        foreach ($plan['jobs'] as $id => &$record) {
            if (empty($record['eligible'])) {
                continue;
            }
            $application_ids = self::dependent_ids('jobapply', 'jobid', $id);
            foreach ($application_ids as $application_id) {
                if (empty($plan['applications'][$application_id]['eligible'])) {
                    $record['eligible'] = false;
                    break;
                }
            }
            if ($record['eligible'] && self::table_exists('jobshortlist')) {
                $shortlist_count = absint($wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(`id`) FROM `{$wpdb->prefix}wj_portal_jobshortlist` WHERE `jobid` = %d",
                    $id
                )));
                if ($shortlist_count > 0) {
                    $record['eligible'] = false;
                }
            }
        }
        unset($record);

        foreach ($plan['resumes'] as $id => &$record) {
            if (empty($record['eligible'])) {
                continue;
            }
            $application_ids = self::dependent_ids('jobapply', 'cvid', $id);
            foreach ($application_ids as $application_id) {
                if (empty($plan['applications'][$application_id]['eligible'])) {
                    $record['eligible'] = false;
                    break;
                }
            }
        }
        unset($record);

        foreach ($plan['companies'] as $id => &$record) {
            if (empty($record['eligible'])) {
                continue;
            }
            $job_ids = self::dependent_ids('jobs', 'companyid', $id);
            foreach ($job_ids as $job_id) {
                if (empty($plan['jobs'][$job_id]['eligible'])) {
                    $record['eligible'] = false;
                    break;
                }
            }
            if ($record['eligible'] && self::table_exists('departments')) {
                $department_count = absint($wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(`id`) FROM `{$wpdb->prefix}wj_portal_departments` WHERE `companyid` = %d",
                    $id
                )));
                if ($department_count > 0) {
                    $record['eligible'] = false;
                }
            }
        }
        unset($record);

        return $plan;
    }

    /**
     * @param string $type   Entity type.
     * @param int    $id     Entity ID.
     * @param array  $record Plan record.
     * @return bool
     */
    private static function delete_entity($type, $id, $record) {
        global $wpdb;

        if ('pages' === $type) {
            return false !== wp_delete_post($id, true);
        }

        $tables = array(
            'applications' => 'jobapply',
            'jobs' => 'jobs',
            'resumes' => 'resume',
            'companies' => 'companies',
        );
        if (!isset($tables[$type])) {
            return false;
        }
        if (!self::entity_tables_are_transactional($type)) {
            return false;
        }

        $wpdb->query('START TRANSACTION');
        $allowed_child_tables = array_keys(self::child_tables($type));
        foreach ((array) $record['children'] as $table => $ids) {
            $table = sanitize_key($table);
            if (!in_array($table, $allowed_child_tables, true)) {
                continue;
            }
            foreach ((array) $ids as $child_id => $signature) {
                if (!is_numeric($child_id)) {
                    $child_id = $signature;
                }
                $child_deleted = $wpdb->delete(
                    $wpdb->prefix . 'wj_portal_' . sanitize_key($table),
                    array('id' => absint($child_id)),
                    array('%d')
                );
                if (1 !== $child_deleted) {
                    $wpdb->query('ROLLBACK');
                    return false;
                }
            }
        }

        $deleted = $wpdb->delete(
            $wpdb->prefix . 'wj_portal_' . $tables[$type],
            array('id' => $id),
            array('%d')
        );
        if (1 !== $deleted) {
            $wpdb->query('ROLLBACK');
            return false;
        }

        $wpdb->query('COMMIT');

        do_action('wpjobportal_sample_data_entity_deleted', $type, $id);
        return true;
    }

    /**
     * @return array
     */
    private static function entity_row($type, $id) {
        global $wpdb;

        $id = absint($id);
        if ('pages' === $type) {
            $post = get_post($id);
            if (!$post) {
                return array();
            }
            return array(
                'ID' => (string) $post->ID,
                'post_type' => (string) $post->post_type,
                'post_status' => (string) $post->post_status,
                'post_name' => (string) $post->post_name,
                'post_title' => (string) $post->post_title,
                'post_content' => (string) $post->post_content,
            );
        }

        $queries = array(
            'companies' => "SELECT * FROM `{$wpdb->prefix}wj_portal_companies` WHERE `id` = %d",
            'jobs' => "SELECT * FROM `{$wpdb->prefix}wj_portal_jobs` WHERE `id` = %d",
            'resumes' => "SELECT * FROM `{$wpdb->prefix}wj_portal_resume` WHERE `id` = %d",
            'applications' => "SELECT * FROM `{$wpdb->prefix}wj_portal_jobapply` WHERE `id` = %d",
        );
        if (!isset($queries[$type])) {
            return array();
        }

        $row = $wpdb->get_row($wpdb->prepare($queries[$type], $id), ARRAY_A);
        return is_array($row) ? $row : array();
    }

    /**
     * @return array
     */
    private static function current_children($type, $id) {
        $tables = self::child_tables($type);
        $children = array();
        foreach ($tables as $table => $column) {
            $children[$table] = self::dependent_records($table, $column, $id);
        }
        return $children;
    }

    /**
     * Fixed relationship allowlist used by preview and deletion.
     *
     * @return array
     */
    private static function child_tables($type) {
        $tables = array();
        if ('companies' === $type) {
            $tables = array('companycities' => 'companyid');
        } elseif ('jobs' === $type) {
            $tables = array('jobcities' => 'jobid');
        } elseif ('resumes' === $type) {
            $tables = array(
                'resumeaddresses' => 'resumeid',
                'resumeemployers' => 'resumeid',
                'resumefiles' => 'resumeid',
                'resumeinstitutes' => 'resumeid',
                'resumelanguages' => 'resumeid',
            );
        }
        return $tables;
    }

    /**
     * Return exact child IDs and row fingerprints so edits are protected.
     *
     * @return array
     */
    private static function dependent_records($table, $column, $id) {
        global $wpdb;

        $table = sanitize_key($table);
        $column = sanitize_key($column);
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM `{$wpdb->prefix}wj_portal_{$table}` WHERE `{$column}` = %d ORDER BY `id` ASC",
            absint($id)
        ), ARRAY_A);
        $records = array();
        foreach ((array) $rows as $row) {
            if (!isset($row['id'])) {
                continue;
            }
            $records[(string) absint($row['id'])] = self::signature($row);
        }
        return $records;
    }

    /**
     * @return array
     */
    private static function dependent_ids($table, $column, $id) {
        global $wpdb;

        $table = sanitize_key($table);
        $column = sanitize_key($column);
        $ids = $wpdb->get_col($wpdb->prepare(
            "SELECT `id` FROM `{$wpdb->prefix}wj_portal_{$table}` WHERE `{$column}` = %d ORDER BY `id` ASC",
            absint($id)
        ));
        return array_map('absint', (array) $ids);
    }

    /**
     * @return bool
     */
    private static function children_match($expected, $current) {
        ksort($expected);
        ksort($current);
        foreach ($expected as &$records) {
            $records = (array) $records;
            ksort($records);
        }
        unset($records);
        foreach ($current as &$records) {
            $records = (array) $records;
            ksort($records);
        }
        unset($records);
        return $expected === $current;
    }

    /**
     * @return bool
     */
    private static function page_is_in_use($id) {
        global $wpdb;

        $protected = array(
            absint(get_option('page_on_front')),
            absint(get_option('page_for_posts')),
        );
        $default_page = absint($wpdb->get_var($wpdb->prepare(
            "SELECT `configvalue` FROM `{$wpdb->prefix}wj_portal_config` WHERE `configname` = %s LIMIT 1",
            'default_pageid'
        )));
        $protected[] = $default_page;
        if (in_array(absint($id), array_filter($protected), true)) {
            return true;
        }

        $children = absint($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(`ID`) FROM `{$wpdb->posts}` WHERE `post_parent` = %d",
            absint($id)
        )));
        $menu_items = absint($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(`post_id`) FROM `{$wpdb->postmeta}` WHERE `meta_key` = '_menu_item_object_id' AND `meta_value` = %s",
            (string) absint($id)
        )));
        return $children > 0 || $menu_items > 0;
    }

    /**
     * @return bool
     */
    private static function table_exists($suffix) {
        global $wpdb;
        $table = $wpdb->prefix . 'wj_portal_' . sanitize_key($suffix);
        return $table === $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table)));
    }

    /**
     * Require transactional storage before deleting related rows as one unit.
     * Cleanup remains available after the Release 2 storage conversion and
     * safely protects records while any required table is still non-InnoDB.
     *
     * @param string $type Entity type.
     * @return bool
     */
    private static function entity_tables_are_transactional($type) {
        $main_tables = array(
            'applications' => 'jobapply',
            'jobs' => 'jobs',
            'resumes' => 'resume',
            'companies' => 'companies',
        );
        if (!isset($main_tables[$type])) {
            return false;
        }

        $tables = array($main_tables[$type]);
        $tables = array_merge($tables, array_keys(self::child_tables($type)));
        foreach ($tables as $table) {
            if (!self::table_is_transactional($table)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param string $suffix WP Job Portal table suffix.
     * @return bool
     */
    private static function table_is_transactional($suffix) {
        global $wpdb;

        $suffix = sanitize_key($suffix);
        if (isset(self::$transactional_tables[$suffix])) {
            return self::$transactional_tables[$suffix];
        }

        $table = $wpdb->prefix . 'wj_portal_' . $suffix;
        $status = $wpdb->get_row($wpdb->prepare('SHOW TABLE STATUS LIKE %s', $wpdb->esc_like($table)), ARRAY_A);
        self::$transactional_tables[$suffix] = is_array($status)
            && isset($status['Engine'])
            && 'innodb' === strtolower((string) $status['Engine']);
        return self::$transactional_tables[$suffix];
    }

    /**
     * Validate every archive entry before extraction and reject executable,
     * traversing, absolute, oversized or unexpectedly rooted content.
     *
     * @param string $archive     Temporary downloaded ZIP.
     * @param string $destination Validated upload destination.
     * @return true|WP_Error
     */
    private static function extract_asset_archive($archive, $destination) {
        if (!is_readable($archive)) {
            return new WP_Error('archive_unreadable');
        }

        $archive_size = filesize($archive);
        if (false === $archive_size || $archive_size <= 0 || $archive_size > self::MAX_ARCHIVE_BYTES) {
            return new WP_Error('archive_size');
        }

        if (!class_exists('PclZip')) {
            do_action('wpjobportal_load_wp_pcl_zip');
        }
        if (!class_exists('PclZip')) {
            require_once ABSPATH . 'wp-admin/includes/class-pclzip.php';
        }

        $zip = new PclZip($archive);
        $entries = $zip->listContent();
        if (!is_array($entries)
            || empty($entries)
            || count($entries) > self::MAX_ARCHIVE_ENTRIES
        ) {
            return new WP_Error('archive_entries');
        }

        $total_size = 0;
        $file_count = 0;
        foreach ($entries as $entry) {
            $name = isset($entry['filename']) ? str_replace('\\', '/', (string) $entry['filename']) : '';
            $is_folder = !empty($entry['folder']) || '/' === substr($name, -1);
            $entry_size = isset($entry['size']) ? absint($entry['size']) : 0;

            if (!self::is_safe_asset_entry($name, $is_folder, $entry_size)) {
                return new WP_Error('archive_entry');
            }
            if (!self::asset_entry_avoids_symlinks($destination, $name, $is_folder)) {
                return new WP_Error('archive_symlink');
            }
            if (!$is_folder) {
                $file_count++;
                $total_size += $entry_size;
                if ($total_size > self::MAX_EXTRACTED_BYTES) {
                    return new WP_Error('archive_expanded_size');
                }
            }
        }

        if (0 === $file_count) {
            return new WP_Error('archive_empty');
        }
        if (!wp_mkdir_p($destination)) {
            return new WP_Error('destination_unwritable');
        }

        $extracted = $zip->extract(PCLZIP_OPT_PATH, trailingslashit($destination));
        if (!is_array($extracted) || empty($extracted)) {
            return new WP_Error('archive_extract');
        }

        return true;
    }

    /**
     * @param string $name       Archive entry path.
     * @param bool   $is_folder  Whether the entry is a directory.
     * @param int    $entry_size Uncompressed entry size.
     * @return bool
     */
    private static function is_safe_asset_entry($name, $is_folder, $entry_size) {
        if ('' === $name
            || false !== strpos($name, "\0")
            || '/' === substr($name, 0, 1)
            || preg_match('/^[A-Za-z]:\//', $name)
        ) {
            return false;
        }

        $parts = explode('/', trim($name, '/'));
        if (empty($parts) || 'data' !== $parts[0]) {
            return false;
        }
        foreach ($parts as $part) {
            if ('' === $part || '.' === $part || '..' === $part || preg_match('/[\x00-\x1F\x7F]/', $part)) {
                return false;
            }
        }

        if ($is_folder) {
            return true;
        }
        if ($entry_size <= 0 || $entry_size > self::MAX_ENTRY_BYTES) {
            return false;
        }

        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        return in_array($extension, array('png', 'jpg', 'jpeg', 'gif', 'webp'), true);
    }

    /**
     * Do not follow a pre-existing symlink while extracting beneath uploads.
     *
     * @param string $destination Extraction root.
     * @param string $name        Validated entry name.
     * @param bool   $is_folder   Whether the final component is a directory.
     * @return bool
     */
    private static function asset_entry_avoids_symlinks($destination, $name, $is_folder) {
        $parts = explode('/', trim($name, '/'));
        if (!$is_folder) {
            array_pop($parts);
        }

        $path = untrailingslashit($destination);
        foreach ($parts as $part) {
            $path .= '/' . $part;
            if (is_link($path)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Record a privacy-safe failure without exposing remote URLs or paths.
     *
     * @param string $reason Failure code.
     * @return WP_Error
     */
    private static function asset_error($reason) {
        $reason = sanitize_key((string) $reason);
        self::save_asset_status('failed', $reason);
        if (class_exists('WPJOBPORTAL_Audit_Service')) {
            WPJOBPORTAL_Audit_Service::record(
                'sample_assets_installed',
                'failed',
                array('reason' => $reason)
            );
        }
        return new WP_Error($reason);
    }

    /**
     * @param string $status success or failed.
     * @param string $reason Privacy-safe failure code.
     */
    private static function save_asset_status($status, $reason) {
        $value = array(
            'status' => sanitize_key((string) $status),
            'reason' => sanitize_key((string) $reason),
            'updated_at' => current_time('mysql', true),
        );
        if (false === get_option(self::ASSET_STATUS_OPTION, false)) {
            add_option(self::ASSET_STATUS_OPTION, $value, '', false);
            return;
        }
        update_option(self::ASSET_STATUS_OPTION, $value, false);
    }

    /**
     * @param array $row Entity row.
     * @return string
     */
    private static function signature($row) {
        return hash('sha256', wp_json_encode(array_values((array) $row)));
    }

    /**
     * @return array
     */
    private static function registry() {
        $registry = get_option(self::REGISTRY_OPTION, array());
        if (!is_array($registry)) {
            $registry = array();
        }
        if (!isset($registry['version'])) {
            $registry['version'] = 1;
        }
        if (!isset($registry['batches']) || !is_array($registry['batches'])) {
            $registry['batches'] = array();
        }
        return $registry;
    }

    /**
     * @param array $registry Registry data.
     */
    private static function save_registry($registry) {
        if (false === get_option(self::REGISTRY_OPTION, false)) {
            add_option(self::REGISTRY_OPTION, $registry, '', false);
            return;
        }
        update_option(self::REGISTRY_OPTION, $registry, false);
    }

    /**
     * @param string $batch_id Batch ID.
     * @param string $type     Entity type.
     * @param int    $id       Entity ID.
     */
    private static function remove_registry_record($batch_id, $type, $id) {
        $registry = self::registry();
        if (isset($registry['batches'][$batch_id]['records'][$type][(string) $id])) {
            unset($registry['batches'][$batch_id]['records'][$type][(string) $id]);
        }
        if (empty($registry['batches'][$batch_id]['records'][$type])) {
            unset($registry['batches'][$batch_id]['records'][$type]);
        }
        if (empty($registry['batches'][$batch_id]['records'])) {
            unset($registry['batches'][$batch_id]);
        }
        self::save_registry($registry);
    }

    /**
     * @return array
     */
    private static function types() {
        return array('companies', 'jobs', 'resumes', 'applications', 'pages');
    }
}
