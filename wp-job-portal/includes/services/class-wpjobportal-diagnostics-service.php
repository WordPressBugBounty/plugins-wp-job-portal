<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Read-only, support-safe environment and database diagnostics.
 */
class WPJOBPORTAL_Diagnostics_Service {

    /**
     * Build the System Status data shown to administrators.
     *
     * @return array
     */
    public static function get_status() {
        return array(
            'generated_at' => current_time('mysql', true),
            'environment' => self::environment_checks(),
            'operational_readiness' => self::operational_readiness_checks(),
            'database' => self::database_checks(),
            'request_security' => self::request_security_checks(),
            'privacy_payments' => self::privacy_payment_checks(),
            'scheduled_tasks' => self::scheduled_task_checks(),
            'privacy' => WPJOBPORTAL_Privacy_Service::get_status(),
            'payments' => WPJOBPORTAL_Payment_Service::get_status(),
            'address_import' => WPJOBPORTAL_Address_Import_Service::get_status(),
            'notification_queue' => WPJOBPORTAL_Notification_Queue_Service::get_status(),
            'storage_conversion' => WPJOBPORTAL_Storage_Engine_Service::get_progress(),
            'audit' => array(
                'summary' => WPJOBPORTAL_Audit_Service::get_summary(),
                'recent' => WPJOBPORTAL_Audit_Service::get_recent(20),
            ),
            'migrations' => array(
                'last_report' => WPJOBPORTAL_Migration_Service::get_last_report(),
                'recent' => WPJOBPORTAL_Migration_Service::get_recent_entries(20),
            ),
        );
    }

    /**
     * Create a plain-text status report suitable for support tickets.
     *
     * @param array|null $status Optional pre-built status.
     * @return string
     */
    public static function as_text($status = null) {
        if (!is_array($status)) {
            $status = self::get_status();
        }

        $lines = array(
            'WP Job Portal System Status',
            'Generated (UTC): ' . $status['generated_at'],
            '',
        );

        foreach (array('environment', 'operational_readiness', 'database', 'request_security', 'privacy_payments', 'scheduled_tasks') as $group) {
            $lines[] = strtoupper(str_replace('_', ' ', $group));
            foreach ($status[$group] as $check) {
                $lines[] = sprintf(
                    '[%s] %s: %s',
                    strtoupper($check['status']),
                    $check['label'],
                    $check['value']
                );
            }
            $lines[] = '';
        }

        $queue = $status['notification_queue'];
        $lines[] = 'NOTIFICATION QUEUE';
        $lines[] = 'Mode: ' . ($queue['enabled'] ? 'enabled' : 'direct');
        $lines[] = 'Queue table: ' . ($queue['schema_ready'] ? 'available' : ($queue['table_exists'] ? 'incomplete' : 'not initialized'));
        $lines[] = sprintf(
            'Jobs: %d pending; %d retrying; %d processing; %d failed; %d completed',
            absint($queue['counts']['pending']),
            absint($queue['counts']['retry']),
            absint($queue['counts']['processing']),
            absint($queue['counts']['failed']),
            absint($queue['counts']['completed'])
        );
        $lines[] = sprintf(
            'Outcomes: %d queued; %d delivered; %d retries; %d terminal failures; %d direct fallbacks; %d duplicates suppressed',
            absint($queue['stats']['queued']),
            absint($queue['stats']['delivered']),
            absint($queue['stats']['retried']),
            absint($queue['stats']['failed']),
            absint($queue['stats']['fallbacks']),
            absint($queue['stats']['duplicates'])
        );
        if (!empty($queue['oldest_due'])) {
            $lines[] = 'Oldest due job (UTC): ' . $queue['oldest_due'];
        }
        if (!empty($queue['stats']['last_error'])) {
            $lines[] = 'Last queue error: ' . $queue['stats']['last_error'];
        }
        $lines[] = '';

        $storage = $status['storage_conversion'];
        $lines[] = 'STORAGE CONVERSION';
        $lines[] = 'Status: ' . $storage['status'];
        $lines[] = sprintf(
            'Progress: %d of %d core tables converted (%d remaining)',
            absint($storage['converted']),
            absint($storage['total']),
            absint($storage['remaining'])
        );
        if (!empty($storage['current_table'])) {
            $lines[] = 'Current table: ' . $storage['current_table'];
        }
        if (!empty($storage['last_table'])) {
            $lines[] = 'Last table: ' . $storage['last_table'];
        }
        if (!empty($storage['last_error'])) {
            $lines[] = 'Last error: ' . $storage['last_error'];
        }
        if (!empty($storage['missing_tables'])) {
            $lines[] = 'Missing core tables: ' . implode(', ', $storage['missing_tables']);
        }
        $lines[] = '';

        $lines[] = 'OPERATIONAL AUDIT';
        $audit = $status['audit'];
        $lines[] = sprintf(
            'Retained events: %d (%d failed)',
            absint($audit['summary']['retained']),
            absint($audit['summary']['failed'])
        );
        foreach ($audit['recent'] as $entry) {
            $lines[] = sprintf(
                '[%s] %s at %s',
                strtoupper($entry['outcome']),
                $entry['event'],
                $entry['time']
            );
        }
        $lines[] = '';

        $lines[] = 'MIGRATIONS';
        $report = $status['migrations']['last_report'];
        $lines[] = 'Last result: ' . (!empty($report['status']) ? $report['status'] : 'No recorded run');
        foreach ($status['migrations']['recent'] as $migration) {
            $lines[] = sprintf(
                '[%s] %s/%s (attempts: %d)',
                strtoupper($migration['status']),
                $migration['component'],
                $migration['migration'],
                absint($migration['attempts'])
            );
        }

        return WPJOBPORTAL_Redactor::redact(implode("\n", $lines));
    }

    /**
     * @return array
     */
    private static function environment_checks() {
        global $wpdb;

        $memory_limit = ini_get('memory_limit');
        $cron_disabled = defined('DISABLE_WP_CRON') && DISABLE_WP_CRON;
        $debug_display = defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY;
        $product_mode = WPJOBPORTAL_Product_Mode_Service::get_status();
        $design_system_file = defined('WPJOBPORTAL_PLUGIN_PATH')
            ? WPJOBPORTAL_PLUGIN_PATH . 'includes/css/wpjobportal-design-system.css'
            : '';
        $design_system_ready = '' !== $design_system_file && is_readable($design_system_file);
        $accessible_forms_css = defined('WPJOBPORTAL_PLUGIN_PATH')
            ? WPJOBPORTAL_PLUGIN_PATH . 'includes/css/wpjobportal-accessible-forms.css'
            : '';
        $accessible_forms_js = defined('WPJOBPORTAL_PLUGIN_PATH')
            ? WPJOBPORTAL_PLUGIN_PATH . 'includes/js/wpjobportal-accessible-forms.js'
            : '';
        $accessible_forms_ready = '' !== $accessible_forms_css
            && '' !== $accessible_forms_js
            && is_readable($accessible_forms_css)
            && is_readable($accessible_forms_js);
        $blocks_editor_js = defined('WPJOBPORTAL_PLUGIN_PATH')
            ? WPJOBPORTAL_PLUGIN_PATH . 'includes/js/gutenberg/blocks.js'
            : '';
        $blocks_editor_css = defined('WPJOBPORTAL_PLUGIN_PATH')
            ? WPJOBPORTAL_PLUGIN_PATH . 'includes/css/wpjobportal-block-editor.css'
            : '';
        $elementor_integration = defined('WPJOBPORTAL_PLUGIN_PATH')
            ? WPJOBPORTAL_PLUGIN_PATH . 'includes/elementor-addon.php'
            : '';
        $builder_compatibility_ready = '' !== $blocks_editor_js
            && '' !== $blocks_editor_css
            && '' !== $elementor_integration
            && is_readable($blocks_editor_js)
            && is_readable($blocks_editor_css)
            && is_readable($elementor_integration);

        return array(
            self::check('good', __('WP Job Portal version', 'wp-job-portal'), self::display_version()),
            self::check(
                !$product_mode['selected'] ? 'info' : ($product_mode['aligned'] ? 'good' : 'warning'),
                __('Operating mode', 'wp-job-portal'),
                $product_mode['selected']
                    ? ($product_mode['aligned'] ? sprintf(
                        /* translators: %s: product mode label */ __('%s explicitly selected; mode-specific setup is active.', 'wp-job-portal'),
                        $product_mode['label']
                    ) : sprintf(
                        /* translators: %s: product mode label */ __('%s is selected, but its legacy compatibility setting is out of sync; run Setup & Mode again.', 'wp-job-portal'),
                        $product_mode['label']
                    ))
                    : __('Existing-site compatibility; no explicit mode is selected and current behavior is unchanged.', 'wp-job-portal')
            ),
            self::check(
                $design_system_ready ? 'good' : 'critical',
                __('Shared design system', 'wp-job-portal'),
                $design_system_ready
                    ? __('Shared design tokens, responsive primitives, focus treatment, and reduced-motion support are available.', 'wp-job-portal')
                    : __('The shared design-system stylesheet is missing or unreadable.', 'wp-job-portal')
            ),
            self::check(
                $accessible_forms_ready ? 'good' : 'critical',
                __('Accessible mobile-first forms', 'wp-job-portal'),
                $accessible_forms_ready
                    ? __('Accessible labels, help text, required-field, validation-error, mobile-control, and keyboard-focus enhancements are available.', 'wp-job-portal')
                    : __('The accessible form stylesheet or behavior script is missing or unreadable.', 'wp-job-portal')
            ),
            self::check(
                $builder_compatibility_ready ? 'good' : 'critical',
                __('Blocks and Elementor compatibility', 'wp-job-portal'),
                $builder_compatibility_ready
                    ? __('Dynamic blocks, translated editor controls, bounded attributes, and load-order-safe Elementor integration are available.', 'wp-job-portal')
                    : __('A block editor or Elementor compatibility file is missing or unreadable.', 'wp-job-portal')
            ),
            self::check(
                version_compare(PHP_VERSION, '7.4', '>=') ? 'good' : 'critical',
                __('PHP version', 'wp-job-portal'),
                PHP_VERSION
            ),
            self::check(
                version_compare(get_bloginfo('version'), '6.2', '>=') ? 'good' : 'warning',
                __('WordPress version', 'wp-job-portal'),
                get_bloginfo('version')
            ),
            self::check('good', __('Database version', 'wp-job-portal'), $wpdb->db_version()),
            self::check(
                $cron_disabled ? 'warning' : 'good',
                __('WordPress Cron', 'wp-job-portal'),
                $cron_disabled ? __('Disabled; a real server cron is required.', 'wp-job-portal') : __('Enabled', 'wp-job-portal')
            ),
            self::check(
                $debug_display ? 'warning' : 'good',
                __('Debug display', 'wp-job-portal'),
                $debug_display ? __('Enabled; disable on production sites.', 'wp-job-portal') : __('Not exposed', 'wp-job-portal')
            ),
            self::check('info', __('PHP memory limit', 'wp-job-portal'), $memory_limit ? $memory_limit : __('Unknown', 'wp-job-portal')),
            self::check('info', __('Maximum upload size', 'wp-job-portal'), size_format(wp_max_upload_size())),
            self::check(
                get_option('permalink_structure') ? 'good' : 'warning',
                __('Permalinks', 'wp-job-portal'),
                get_option('permalink_structure') ? __('Pretty permalinks enabled', 'wp-job-portal') : __('Plain permalinks', 'wp-job-portal')
            ),
        );
    }

    /**
     * @return array
     */
    private static function database_checks() {
        global $wpdb;

        $pattern = $wpdb->esc_like($wpdb->prefix . 'wj_portal_') . '%';
        $tables = wpjobportaldb_direct::get_results($wpdb->prepare('SHOW TABLE STATUS LIKE %s', $pattern), ARRAY_A);
        $storage = WPJOBPORTAL_Storage_Engine_Service::get_progress();
        $missing_indexes = WPJOBPORTAL_Database_Readiness_Service::get_missing_indexes();
        $migration_table = WPJOBPORTAL_Migration_Service::table_exists();
        $migration_issues = $migration_table ? WPJOBPORTAL_Migration_Service::get_integrity_issues() : array();

        return array(
            self::check(
                count($tables) > 0 ? 'good' : 'critical',
                __('WP Job Portal tables', 'wp-job-portal'),
                sprintf(/* translators: %d: number of tables */ _n('%d table detected', '%d tables detected', count($tables), 'wp-job-portal'), count($tables))
            ),
            self::check(
                $migration_table ? 'good' : 'critical',
                __('Migration ledger', 'wp-job-portal'),
                $migration_table ? __('Available', 'wp-job-portal') : __('Missing', 'wp-job-portal')
            ),
            self::check(
                empty($migration_issues) ? 'good' : 'critical',
                __('Migration file integrity', 'wp-job-portal'),
                empty($migration_issues)
                    ? __('Applied local migrations are unchanged.', 'wp-job-portal')
                    : implode(' ', $migration_issues)
            ),
            self::check(
                !empty($storage['missing_tables'])
                    ? 'critical'
                    : ($storage['remaining'] === 0 ? 'good' : ($storage['status'] === 'failed' ? 'critical' : 'warning')),
                __('Core storage engines', 'wp-job-portal'),
                !empty($storage['missing_tables'])
                    ? sprintf(
                        /* translators: %s: list of storage missing tables */ __('Expected core tables are missing: %s', 'wp-job-portal'),
                        implode(', ', $storage['missing_tables'])
                    )
                    : ($storage['remaining'] === 0
                    ? __('All detected core tables use InnoDB and support transactions.', 'wp-job-portal')
                    : sprintf(
                        /* translators: 1: storage remaining, 2: storage total */ _n(
                            '%1$d of %2$d core tables awaits conversion.',
                            '%1$d of %2$d core tables await conversion.',
                            $storage['remaining'],
                            'wp-job-portal'
                        ),
                        $storage['remaining'],
                        $storage['total']
                    ))
            ),
            self::check(
                empty($missing_indexes) ? 'good' : 'warning',
                __('High-volume indexes', 'wp-job-portal'),
                empty($missing_indexes)
                    ? __('Recommended indexes are present.', 'wp-job-portal')
                    : sprintf(
                        /* translators: 1: number of missing indexes, 2: list of missing indexes */ __('%1$d planned indexes are not present: %2$s', 'wp-job-portal'),
                        count($missing_indexes),
                        implode(', ', $missing_indexes)
                    )
            ),
        );
    }

    /**
     * Mode-aware, actionable checks that remain read-only.
     *
     * @return array
     */
    private static function operational_readiness_checks() {
        $onboarding = WPJOBPORTAL_Onboarding_Service::get_state();
        $sample_data = WPJOBPORTAL_Sample_Data_Service::get_preview();
        $routing = WPJOBPORTAL_Route_Registry_Service::get_status();
        $uploads = wp_upload_dir(null, false);
        $upload_error = isset($uploads['error']) ? (string) $uploads['error'] : '';
        $upload_writable = '' === $upload_error
            && !empty($uploads['basedir'])
            && wp_is_writable($uploads['basedir']);
        $portal_page = array('complete' => false, 'description' => __('The onboarding page check is unavailable.', 'wp-job-portal'));

        foreach ((array) $onboarding['items'] as $item) {
            if (isset($item['id']) && 'portal_page' === $item['id']) {
                $portal_page = $item;
                break;
            }
        }

        $checks = array(
            self::check(
                WPJOBPORTAL_Product_Mode_Service::is_selected() ? 'good' : 'warning',
                __('Mode-specific readiness', 'wp-job-portal'),
                WPJOBPORTAL_Product_Mode_Service::is_selected()
                    ? sprintf(
                        /* translators: 1: onboarding mode label, 2: onboarding completed, 3: onboarding total */ __('%1$s is selected; %2$d of %3$d onboarding steps are complete.', 'wp-job-portal'),
                        $onboarding['mode_label'],
                        absint($onboarding['completed']),
                        absint($onboarding['total'])
                    )
                    : __('Existing-site compatibility is active. Choose a mode from Setup & Mode when ready; no mode is selected automatically.', 'wp-job-portal')
            ),
            self::check(
                !empty($portal_page['complete']) ? 'good' : 'warning',
                __('Default portal page', 'wp-job-portal'),
                !empty($portal_page['complete'])
                    ? __('Published and contains WP Job Portal content.', 'wp-job-portal')
                    : $portal_page['description'] . ' ' . __('Review the main page in WP Job Portal configuration.', 'wp-job-portal')
            ),
            self::check(
                $upload_writable ? 'good' : 'critical',
                __('Upload directory', 'wp-job-portal'),
                $upload_writable
                    ? __('Available and writable for WP Job Portal files.', 'wp-job-portal')
                    : __('Unavailable or not writable. Correct the WordPress uploads configuration and permissions before accepting files.', 'wp-job-portal')
            ),
            self::check(
                absint($sample_data['tracked']) > 0 && absint($sample_data['skipped']) > 0 ? 'warning' : 'info',
                __('Marked sample data', 'wp-job-portal'),
                absint($sample_data['tracked']) > 0
                    ? sprintf(
                        /* translators: 1: sample data tracked, 2: sample data eligible, 3: sample data skipped */ __('%1$d records are strongly marked; %2$d are cleanup-eligible and %3$d are protected because they changed or have dependencies.', 'wp-job-portal'),
                        absint($sample_data['tracked']),
                        absint($sample_data['eligible']),
                        absint($sample_data['skipped'])
                    )
                    : __('No strongly marked records are available. Historical sample-like content is preserved rather than guessed.', 'wp-job-portal')
            ),
        );

        if (!empty($routing['unknown_active_addons'])) {
            $checks[] = self::check(
                'warning',
                __('Add-on compatibility', 'wp-job-portal'),
                sprintf(
                    /* translators: 1: routing supported active addons, 2: number of routing unknown active addons */ __('%1$d supported add-ons are active; %2$d unknown or retired add-ons need manual compatibility review.', 'wp-job-portal'),
                    absint($routing['supported_active_addons']),
                    count($routing['unknown_active_addons'])
                )
            );
        } else {
            $checks[] = self::check(
                'good',
                __('Add-on compatibility', 'wp-job-portal'),
                sprintf(
                    /* translators: %d: routing supported active addons */ _n('%d supported add-on is active and no unknown add-on route was detected.', '%d supported add-ons are active and no unknown add-on route was detected.', absint($routing['supported_active_addons']), 'wp-job-portal'),
                    absint($routing['supported_active_addons'])
                )
            );
        }

        return $checks;
    }

    /**
     * @return array
     */
    private static function request_security_checks() {
        $routing = WPJOBPORTAL_Route_Registry_Service::get_status();
        $mutation_policies = WPJOBPORTAL_Core_Action_Policy_Service::get_status();
        $addon_policies = WPJOBPORTAL_Addon_Action_Policy_Service::get_status();
        $authorization = WPJOBPORTAL_Authorization_Service::get_status();
        $file_policy = WPJOBPORTAL_File_Policy_Service::get_status();
        $address_import = WPJOBPORTAL_Address_Import_Service::get_status();

        $checks = array(
            self::check(
                'good',
                __('Core request routes', 'wp-job-portal'),
                sprintf(
                    /* translators: 1: routing core routes, 2: routing core modules */ __('%1$d actions across %2$d core modules are explicitly registered; unknown core controller actions are denied.', 'wp-job-portal'),
                    absint($routing['core_routes']),
                    absint($routing['core_modules'])
                )
            ),
        );

        if (!empty($routing['strict_addon_mode'])) {
            $checks[] = self::check(
                'good',
                __('Supported add-on request routes', 'wp-job-portal'),
                sprintf(
                    /* translators: 1: routing extension routes, 2: routing extension modules, 3: routing supported active addons */ __('%1$d actions across %2$d add-on modules are explicitly registered; strict mode is enabled for %3$d active supported add-ons.', 'wp-job-portal'),
                    absint($routing['extension_routes']),
                    absint($routing['extension_modules']),
                    absint($routing['supported_active_addons'])
                )
            );
        } elseif (empty($routing['unknown_active_addons'])) {
            $checks[] = self::check(
                'good',
                __('Add-on request compatibility', 'wp-job-portal'),
                sprintf(
                    /* translators: %d: number of supported add-ons. */
                    __('%d supported add-ons are active. WP Job Portal Pro and the suites keep compatibility mode for add-on routes.', 'wp-job-portal'),
                    absint($routing['supported_active_addons'])
                )
            );
        } else {
            $checks[] = self::check(
                'warning',
                __('Add-on request compatibility', 'wp-job-portal'),
                sprintf(
                    /* translators: 1: routing supported active addons, 2: number of routing unknown active addons, 3: list of routing unknown active addons */ __('%1$d supported add-ons are active, but %2$d unknown or retired add-on(s) were detected: %3$s. Unregistered add-on routes remain in compatibility mode.', 'wp-job-portal'),
                    absint($routing['supported_active_addons']),
                    count($routing['unknown_active_addons']),
                    implode(', ', array_map('sanitize_key', $routing['unknown_active_addons']))
                )
            );
        }

        if (absint($routing['denied']) > 0) {
            $checks[] = self::check(
                'warning',
                __('Denied request routes', 'wp-job-portal'),
                sprintf(
                    /* translators: 1: routing denied, 2: routing last denied at, 3: routing last reason */ _n(
                        '%1$d request has been denied. Most recent: %2$s UTC (%3$s).',
                        '%1$d requests have been denied. Most recent: %2$s UTC (%3$s).',
                        absint($routing['denied']),
                        'wp-job-portal'
                    ),
                    absint($routing['denied']),
                    $routing['last_denied_at'],
                    $routing['last_reason']
                )
            );
        } else {
            $checks[] = self::check(
                'good',
                __('Denied request routes', 'wp-job-portal'),
                __('No denied controller-route attempts have been recorded.', 'wp-job-portal')
            );
        }

        $checks[] = self::check(
            'good',
            __('Core mutation authorization', 'wp-job-portal'),
            sprintf(
                /* translators: 1: mutation policies protected routes, 2: mutation policies schema routes, 3: mutation policies ownership routes */ __('%1$d state-changing core routes enforce HTTP method, role or capability and nonce policies; %2$d validate security-relevant input fields and %3$d also enforce object ownership.', 'wp-job-portal'),
                absint($mutation_policies['protected_routes']),
                absint($mutation_policies['schema_routes']),
                absint($mutation_policies['ownership_routes'])
            )
        );

        $checks[] = self::check(
            'good',
            __('Add-on mutation authorization', 'wp-job-portal'),
            sprintf(
                /* translators: 1: addon policies protected routes, 2: addon policies schema routes, 3: addon policies ownership routes */ __('%1$d state-changing add-on routes enforce centralized policies; %2$d validate target identifiers and %3$d apply record-ownership rules.', 'wp-job-portal'),
                absint($addon_policies['protected_routes']),
                absint($addon_policies['schema_routes']),
                absint($addon_policies['ownership_routes'])
            )
        );

        if (absint($addon_policies['denied']) > 0) {
            $checks[] = self::check(
                'warning',
                __('Denied add-on mutations', 'wp-job-portal'),
                sprintf(
                    /* translators: 1: addon policies denied, 2: addon policies last denied at, 3: addon policies last module, 4: addon policies last task, 5: addon policies last reason */ _n(
                        '%1$d add-on mutation has been denied. Most recent: %2$s UTC (%3$s.%4$s: %5$s).',
                        '%1$d add-on mutations have been denied. Most recent: %2$s UTC (%3$s.%4$s: %5$s).',
                        absint($addon_policies['denied']),
                        'wp-job-portal'
                    ),
                    absint($addon_policies['denied']),
                    $addon_policies['last_denied_at'],
                    $addon_policies['last_module'],
                    $addon_policies['last_task'],
                    $addon_policies['last_reason']
                )
            );
        }

        if (absint($mutation_policies['denied']) > 0) {
            $checks[] = self::check(
                'warning',
                __('Denied core mutations', 'wp-job-portal'),
                sprintf(
                    /* translators: 1: mutation policies denied, 2: mutation policies last denied at, 3: mutation policies last module, 4: mutation policies last task, 5: mutation policies last reason */ _n(
                        '%1$d core mutation has been denied. Most recent: %2$s UTC (%3$s.%4$s: %5$s).',
                        '%1$d core mutations have been denied. Most recent: %2$s UTC (%3$s.%4$s: %5$s).',
                        absint($mutation_policies['denied']),
                        'wp-job-portal'
                    ),
                    absint($mutation_policies['denied']),
                    $mutation_policies['last_denied_at'],
                    $mutation_policies['last_module'],
                    $mutation_policies['last_task'],
                    $mutation_policies['last_reason']
                )
            );
        } else {
            $checks[] = self::check(
                'good',
                __('Denied core mutations', 'wp-job-portal'),
                __('No centralized core-mutation denials have been recorded.', 'wp-job-portal')
            );
        }

        $checks[] = self::check(
            'good',
            __('Sensitive download authorization', 'wp-job-portal'),
            sprintf(
                /* translators: %d: authorization protected routes */ __('%d resume and custom-file download routes enforce server-side object authorization.', 'wp-job-portal'),
                absint($authorization['protected_routes'])
            )
        );

        $checks[] = self::check(
            'good',
            __('File extension validation', 'wp-job-portal'),
            sprintf(
                /* translators: %d: file policy protected paths */ __('%d upload and file-management paths use trimmed, lowercase, exact extension allowlists.', 'wp-job-portal'),
                absint($file_policy['protected_paths'])
            )
        );

        $checks[] = self::check(
            'good',
            __('Address-data import validation', 'wp-job-portal'),
            sprintf(
                /* translators: 1: address import max archive size, 2: address import max sql size */ __('ZIP files are read without extraction and SQL is limited to staged state/city INSERT operations (%1$s archive limit; %2$s SQL limit).', 'wp-job-portal'),
                size_format($address_import['max_archive_bytes']),
                size_format($address_import['max_sql_bytes'])
            )
        );

        if (absint($authorization['denied']) > 0) {
            $checks[] = self::check(
                'warning',
                __('Denied sensitive downloads', 'wp-job-portal'),
                sprintf(
                    /* translators: 1: authorization denied, 2: authorization last denied at, 3: authorization last resource, 4: authorization last reason */ _n(
                        '%1$d download has been denied. Most recent: %2$s UTC (%3$s: %4$s).',
                        '%1$d downloads have been denied. Most recent: %2$s UTC (%3$s: %4$s).',
                        absint($authorization['denied']),
                        'wp-job-portal'
                    ),
                    absint($authorization['denied']),
                    $authorization['last_denied_at'],
                    $authorization['last_resource'],
                    $authorization['last_reason']
                )
            );
        } else {
            $checks[] = self::check(
                'good',
                __('Denied sensitive downloads', 'wp-job-portal'),
                __('No object-authorization denials have been recorded.', 'wp-job-portal')
            );
        }

        return $checks;
    }

    /**
     * @return array
     */
    private static function privacy_payment_checks() {
        $privacy = WPJOBPORTAL_Privacy_Service::get_status();
        $payments = WPJOBPORTAL_Payment_Service::get_status();
        $settings = $privacy['settings'];

        return array(
            self::check(
                'good',
                __('WordPress privacy tools', 'wp-job-portal'),
                __('Personal-data exporter and eraser callbacks are registered; erasure anonymizes records that must remain for relational integrity.', 'wp-job-portal')
            ),
            self::check(
                !empty($privacy['next_run']) ? 'good' : 'warning',
                __('Privacy retention cleanup', 'wp-job-portal'),
                !empty($privacy['next_run'])
                    ? sprintf(
                        /* translators: 1: settings activity days, 2: settings message days, 3: settings audit days */ __('Scheduled; activity %1$d days, messages %2$d days, operational audit %3$d days.', 'wp-job-portal'),
                        absint($settings['activity_days']),
                        absint($settings['message_days']),
                        absint($settings['audit_days'])
                    )
                    : __('Not scheduled', 'wp-job-portal')
            ),
            self::check(
                'good',
                __('Payment service boundary', 'wp-job-portal'),
                sprintf(
                    /* translators: %d: payments providers */ _n(
                        '%d provider is registered with normalized routing, privacy-safe outcomes and hashed event idempotency.',
                        '%d providers are registered with normalized routing, privacy-safe outcomes and hashed event idempotency.',
                        absint($payments['providers']),
                        'wp-job-portal'
                    ),
                    absint($payments['providers'])
                )
            ),
            self::check(
                absint($payments['failed']) > 0 ? 'warning' : 'good',
                __('Payment processing outcomes', 'wp-job-portal'),
                sprintf(
                    /* translators: 1: payments completed, 2: payments failed, 3: payments duplicates, 4: payments rejected */ __('%1$d completed; %2$d failed; %3$d duplicate provider events suppressed; %4$d boundary rejections.', 'wp-job-portal'),
                    absint($payments['completed']),
                    absint($payments['failed']),
                    absint($payments['duplicates']),
                    absint($payments['rejected'])
                )
            ),
        );
    }

    /**
     * @return array
     */
    private static function scheduled_task_checks() {
        $checks = array();
        $mail_stats = WPJOBPORTAL_Notification_Service::get_stats();
        $queue = WPJOBPORTAL_Notification_Queue_Service::get_status();
        $session_event = wp_next_scheduled('jsjp_delete_expire_session_data');
        $checks[] = self::check(
            $session_event ? 'good' : 'warning',
            __('Session cleanup', 'wp-job-portal'),
            $session_event ? self::format_timestamp($session_event) : __('Not scheduled', 'wp-job-portal')
        );

        if (in_array('jobalert', (array) wpjobportal::$_active_addons, true)) {
            $alert_hook = 'wpjobportal_send_scheduled_job_alerts';
            $alert_handler = has_action($alert_hook);
            $alert_event = wp_next_scheduled($alert_hook);
            if (false === $alert_handler) {
                $checks[] = self::check(
                    'warning',
                    __('Job Alert delivery', 'wp-job-portal'),
                    __('The add-on is active, but its scheduled-delivery handler is not loaded. Reactivate or reinstall the current supported Job Alert add-on.', 'wp-job-portal')
                );
            } elseif (!$alert_event) {
                $checks[] = self::check(
                    'warning',
                    __('Job Alert delivery', 'wp-job-portal'),
                    __('The delivery handler is loaded, but WordPress could not create its daily schedule.', 'wp-job-portal')
                );
            } else {
                $checks[] = self::check(
                    'good',
                    __('Job Alert delivery', 'wp-job-portal'),
                    self::format_timestamp($alert_event)
                );
            }
        } else {
            $checks[] = self::check('info', __('Job Alert delivery', 'wp-job-portal'), __('Add-on not active', 'wp-job-portal'));
        }

        $checks[] = self::check(
            function_exists('wp_mail') ? 'good' : 'critical',
            __('WordPress mail function', 'wp-job-portal'),
            function_exists('wp_mail') ? __('Available', 'wp-job-portal') : __('Unavailable', 'wp-job-portal')
        );

        $checks[] = self::check(
            'info',
            __('WP Job Portal mail mode', 'wp-job-portal'),
            $queue['enabled']
                ? __('Reliable queue enabled; delivery uses wp_mail() in bounded cron batches.', 'wp-job-portal')
                : ($queue['outstanding'] > 0
                    ? __('Direct wp_mail() submission for new messages; the disabled queue is draining existing jobs.', 'wp-job-portal')
                    : __('Direct wp_mail() submission; the reliable queue is not enabled.', 'wp-job-portal'))
        );

        $checks[] = self::check(
            $queue['enabled'] && !$queue['schema_ready']
                ? 'critical'
                : ($queue['schema_ready'] ? 'good' : ($queue['table_exists'] ? 'warning' : 'info')),
            __('Notification queue storage', 'wp-job-portal'),
            $queue['schema_ready']
                ? __('Encrypted queue table is available.', 'wp-job-portal')
                : ($queue['enabled']
                    ? __('Queue mode is enabled but its table is unavailable; new mail will use direct fallback.', 'wp-job-portal')
                    : ($queue['table_exists']
                        ? __('The queue table is incomplete; enable the queue to run its safe schema repair.', 'wp-job-portal')
                        : __('Not initialized; enable the queue from System Status to create it.', 'wp-job-portal')))
        );

        $cron_healthy = $queue['outstanding'] === 0;
        if ($queue['outstanding'] > 0) {
            $cron_healthy = !empty($queue['next_run']) && empty($queue['worker_overdue']);
        }
        $checks[] = self::check(
            $cron_healthy ? 'good' : 'warning',
            __('Notification queue worker', 'wp-job-portal'),
            !empty($queue['next_run'])
                ? (empty($queue['worker_overdue'])
                    ? self::format_timestamp($queue['next_run'])
                    : sprintf(
                        /* translators: %s: wp date get option date format get option time format queue  */ __('Overdue since %s; the recovery check will replace the event.', 'wp-job-portal'),
                        wp_date(get_option('date_format') . ' ' . get_option('time_format'), $queue['next_run'])
                    ))
                : ($queue['outstanding'] > 0
                    ? __('No future run is scheduled; the recovery check will recreate it on the next administrator request.', 'wp-job-portal')
                    : __('Idle; a worker is scheduled automatically when mail enters the queue.', 'wp-job-portal'))
        );

        $checks[] = self::check(
            $queue['counts']['failed'] > 0 ? 'warning' : 'good',
            __('Notification queue health', 'wp-job-portal'),
            sprintf(
                /* translators: 1: queue counts pending, 2: queue counts retry, 3: queue counts processing, 4: queue counts failed */ __('%1$d waiting; %2$d retrying; %3$d processing; %4$d failed.', 'wp-job-portal'),
                absint($queue['counts']['pending']),
                absint($queue['counts']['retry']),
                absint($queue['counts']['processing']),
                absint($queue['counts']['failed'])
            )
        );

        if (absint($mail_stats['attempted']) === 0) {
            $checks[] = self::check(
                'info',
                __('WP Job Portal mail submissions', 'wp-job-portal'),
                __('No submissions have been recorded since this build was installed.', 'wp-job-portal')
            );
        } else {
            $checks[] = self::check(
                $mail_stats['last_result'] === 'failed' ? 'warning' : 'good',
                __('WP Job Portal mail submissions', 'wp-job-portal'),
                sprintf(
                    /* translators: 1: mail stats attempted, 2: mail stats succeeded, 3: mail stats failed */ __('%1$d attempted; %2$d accepted by wp_mail(); %3$d rejected.', 'wp-job-portal'),
                    absint($mail_stats['attempted']),
                    absint($mail_stats['succeeded']),
                    absint($mail_stats['failed'])
                )
            );
        }

        if (!empty($mail_stats['last_error'])) {
            $checks[] = self::check(
                $mail_stats['last_result'] === 'failed' ? 'warning' : 'info',
                __('Most recent WP Job Portal mail error', 'wp-job-portal'),
                sprintf(
                    /* translators: 1: mail stats last error, 2: mail stats last failure */ __('%1$s at %2$s', 'wp-job-portal'),
                    $mail_stats['last_error'],
                    $mail_stats['last_failure']
                )
            );
        }

        return $checks;
    }

    /**
     * @param string $status good, warning, critical or info.
     * @param string $label Check label.
     * @param string $value Human-readable value.
     * @return array
     */
    private static function check($status, $label, $value) {
        return array(
            'status' => $status,
            'label' => $label,
            'value' => WPJOBPORTAL_Redactor::redact($value),
        );
    }

    /**
     * @return string
     */
    private static function display_version() {
        $version = (string) wpjobportal::$_currentversion;
        if (strlen($version) === 3) {
            return $version[0] . '.' . $version[1] . '.' . $version[2];
        }
        return $version;
    }

    /**
     * @param int $timestamp UTC timestamp.
     * @return string
     */
    private static function format_timestamp($timestamp) {
        return sprintf(
            /* translators: %s: wp date get option date format get option time format timest */ __('Next run: %s', 'wp-job-portal'),
            wp_date(get_option('date_format') . ' ' . get_option('time_format'), $timestamp)
        );
    }
}
