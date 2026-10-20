<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Server-derived, mode-aware onboarding state.
 */
class WPJOBPORTAL_Onboarding_Service {

    const DISMISSED_META = 'wpjobportal_onboarding_dismissed';

    /**
     * Register the compact dashboard reminder.
     */
    public static function register() {
        add_action('admin_notices', array(__CLASS__, 'render_dashboard_notice'));
    }

    /**
     * Build persistent onboarding state for the current installation.
     *
     * Completion is always calculated from saved configuration and content.
     * Dismissal is presentation-only and stored per administrator.
     *
     * @param int $user_id Optional WordPress user ID.
     * @return array
     */
    public static function get_state($user_id = 0) {
        global $wpdb;

        $user_id = absint($user_id);
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $mode = WPJOBPORTAL_Product_Mode_Service::get_mode();
        $is_marketplace = WPJOBPORTAL_Product_Mode_Service::MODE_MARKETPLACE === $mode;
        // Sample records are not the site's own. Counted, they ticked "company
        // profile complete" and "first job published" the moment the demo was
        // imported, and the checklist stopped pointing at work still to do.
        $company_count = absint(wpjobportaldb_direct::get_var(
            "SELECT COUNT(`id`) FROM `{$wpdb->prefix}wj_portal_companies` WHERE `status` = 1" . self::not_sample('companies')
        ));
        $job_count = absint(wpjobportaldb_direct::get_var(
            "SELECT COUNT(`id`) FROM `{$wpdb->prefix}wj_portal_jobs` WHERE `status` = 1" . self::not_sample('jobs')
        ));
        $application_count = absint(wpjobportaldb_direct::get_var(
            "SELECT COUNT(`id`) FROM `{$wpdb->prefix}wj_portal_jobapply` WHERE 1 = 1" . self::not_sample('applications')
        ));
        $city_count = absint(wpjobportaldb_direct::get_var(
            $wpdb->prepare("SELECT COUNT(`id`) FROM %i", $wpdb->prefix . 'wj_portal_cities')
        ));
        $page_status = self::default_page_status();
        $items = array();

        $items[] = self::item(
            'mode',
            __('Choose an operating mode', 'wp-job-portal'),
            WPJOBPORTAL_Product_Mode_Service::is_selected()
                ? sprintf(
                    /* translators: %s: wpjobportal product mode service get label mode */ __('%s is selected.', 'wp-job-portal'),
                    WPJOBPORTAL_Product_Mode_Service::get_label($mode)
                )
                : __('Choose Careers & ATS or Job Marketplace intentionally; existing-site compatibility remains unchanged until then.', 'wp-job-portal'),
            WPJOBPORTAL_Product_Mode_Service::is_selected(),
            admin_url('admin.php?page=wpjobportal_postinstallation&wpjobportallt=quickstart'),
            __('Review mode', 'wp-job-portal')
        );

        if ($is_marketplace) {
            // 1 is "on", whatever the setting's name suggests.
            $employer_enabled = '1' === (string) self::configuration_value('disable_employer', '1');
            $registration_enabled = '1' === (string) self::configuration_value('showemployerlink', '0');
            $items[] = self::item(
                'employer_registration',
                __('Configure employer registration', 'wp-job-portal'),
                $employer_enabled && $registration_enabled
                    ? __('The employer area and employer registration are enabled.', 'wp-job-portal')
                    : __('Enable the employer area and decide whether employers may register.', 'wp-job-portal'),
                $employer_enabled && $registration_enabled,
                admin_url('admin.php?page=wpjobportal_configuration&wpjobportallt=configurations'),
                __('Review employer settings', 'wp-job-portal')
            );
        }

        // Before the company and job steps on purpose: both ask for a
        // location, and typed locations are off by default, so neither can
        // be completed properly until a city list exists.
        $items[] = self::item(
            'cities',
            __('Load the city list', 'wp-job-portal'),
            $city_count > 0
                ? sprintf(
                    /* translators: %d: city count */ _n('%d city is available to choose from.', '%d cities are available to choose from.', $city_count, 'wp-job-portal'),
                    $city_count
                )
                : self::city_prompt(),
            $city_count > 0,
            admin_url('admin.php?page=wpjobportal_city&wpjobportallt=loadaddressdata'),
            $city_count > 0 ? __('Review locations', 'wp-job-portal') : __('Load address data', 'wp-job-portal')
        );

        $items[] = self::item(
            'company',
            $is_marketplace ? __('Approve the first company', 'wp-job-portal') : __('Complete the company profile', 'wp-job-portal'),
            $company_count > 0
                ? sprintf(
                    /* translators: %d: company count */ _n('%d approved company is available.', '%d approved companies are available.', $company_count, 'wp-job-portal'),
                    $company_count
                )
                : ($is_marketplace
                    ? __('Create or approve the first employer company.', 'wp-job-portal')
                    : __('Create the hiring company identity used by jobs and applications.', 'wp-job-portal')),
            $company_count > 0,
            $company_count > 0
                ? admin_url('admin.php?page=wpjobportal_company')
                : admin_url('admin.php?page=wpjobportal_company&wpjobportallt=formcompany'),
            $company_count > 0 ? __('Review companies', 'wp-job-portal') : __('Add company', 'wp-job-portal')
        );

        $items[] = self::item(
            'portal_page',
            __('Configure the main portal page', 'wp-job-portal'),
            $page_status['complete']
                ? __('The configured default page is published and contains WP Job Portal content.', 'wp-job-portal')
                : $page_status['message'],
            $page_status['complete'],
            admin_url('admin.php?page=wpjobportal_configuration&wpjobportallt=configurations'),
            __('Review page settings', 'wp-job-portal')
        );

        $items[] = self::item(
            'job',
            __('Publish the first job', 'wp-job-portal'),
            $job_count > 0
                ? sprintf(/* translators: %d: job count */ _n('%d published job is available.', '%d published jobs are available.', $job_count, 'wp-job-portal'), $job_count)
                : __('Create and publish a real job so candidates can apply.', 'wp-job-portal'),
            $job_count > 0,
            $job_count > 0
                ? admin_url('admin.php?page=wpjobportal_job')
                : admin_url('admin.php?page=wpjobportal_job&wpjobportallt=formjob'),
            $job_count > 0 ? __('Review jobs', 'wp-job-portal') : __('Add job', 'wp-job-portal')
        );

        $items[] = self::item(
            'applications',
            $is_marketplace ? __('Verify the application workflow', 'wp-job-portal') : __('Review the candidate workflow', 'wp-job-portal'),
            $application_count > 0
                ? sprintf(/* translators: %d: application count */ _n('%d application has been received.', '%d applications have been received.', $application_count, 'wp-job-portal'), $application_count)
                : __('No application has been received yet. Use a separate candidate account when testing this step.', 'wp-job-portal'),
            $application_count > 0,
            admin_url('admin.php?page=wpjobportal_job'),
            __('Review job applications', 'wp-job-portal')
        );

        $completed = 0;
        foreach ($items as $item) {
            if (!empty($item['complete'])) {
                $completed++;
            }
        }

        $dismissed_mode = $user_id > 0
            ? sanitize_key((string) get_user_meta($user_id, self::DISMISSED_META, true))
            : '';

        return array(
            'mode' => $mode,
            'mode_label' => WPJOBPORTAL_Product_Mode_Service::get_label($mode),
            'items' => $items,
            'completed' => $completed,
            'total' => count($items),
            'is_complete' => count($items) > 0 && $completed === count($items),
            'dismissed' => $user_id > 0 && $dismissed_mode === $mode,
        );
    }

    /**
     * Hide the checklist for one administrator without changing completion.
     *
     * @param int $user_id WordPress user ID.
     * @return bool
     */
    public static function dismiss($user_id) {
        $user_id = absint($user_id);
        return $user_id > 0 && false !== update_user_meta(
            $user_id,
            self::DISMISSED_META,
            WPJOBPORTAL_Product_Mode_Service::get_mode()
        );
    }

    /**
     * Reopen the checklist for one administrator.
     *
     * @param int $user_id WordPress user ID.
     * @return bool
     */
    public static function reopen($user_id) {
        $user_id = absint($user_id);
        if ($user_id <= 0) {
            return false;
        }
        delete_user_meta($user_id, self::DISMISSED_META);
        return true;
    }

    /**
     * Show a non-blocking reminder on the WP Job Portal dashboard.
     */
    public static function render_dashboard_notice() {
        if (!current_user_can('wpjobportal') && !current_user_can('manage_options')) {
            return;
        }

        $page = sanitize_key((string) WPJOBPORTALrequest::getVar('page', 'get', ''));
        if ('wpjobportal' !== $page || !WPJOBPORTAL_Product_Mode_Service::is_selected()) {
            return;
        }

        $state = self::get_state();
        if ($state['dismissed'] || $state['is_complete']) {
            return;
        }

        $url = admin_url('admin.php?page=wpjobportal_postinstallation&wpjobportallt=quickstart');
        ?>
        <div class="notice notice-info wpjobportal-onboarding-notice">
            <p>
                <strong><?php echo esc_html__('Finish setting up WP Job Portal.', 'wp-job-portal'); ?></strong>
                <?php
                echo esc_html(sprintf(
                    /* translators: 1: state completed, 2: state total */ __('%1$d of %2$d onboarding steps are complete.', 'wp-job-portal'),
                    absint($state['completed']),
                    absint($state['total'])
                ));
                ?>
                <a href="<?php echo esc_url($url); ?>"><?php echo esc_html__('Open checklist', 'wp-job-portal'); ?></a>
            </p>
        </div>
        <?php
    }

    /**
     * What to say when no city has been loaded.
     *
     * Naming the country makes the step one click rather than a decision,
     * and it is the same guess the setup notice offers.
     *
     * @return string
     */
    /**
     * An "AND id NOT IN (...)" for the sample records of one type, or ''.
     *
     * @param string $type companies, jobs or applications.
     * @return string
     */
    private static function not_sample($type) {
        if (!class_exists('WPJOBPORTAL_Sample_Data_Service')) {
            return '';
        }
        $ids = array_map('absint', WPJOBPORTAL_Sample_Data_Service::tracked_ids($type));

        return empty($ids) ? '' : ' AND `id` NOT IN (' . implode(',', $ids) . ')';
    }

    private static function city_prompt() {
        if (!class_exists('WPJOBPORTAL_Site_Country_Service')
            || !class_exists('WPJOBPORTAL_Address_Setup_Notice')) {
            return __('Import a city list so employers can choose a job location.', 'wp-job-portal');
        }

        $country = WPJOBPORTAL_Site_Country_Service::country_name();
        $available = WPJOBPORTAL_Address_Setup_Notice::available_for(
            WPJOBPORTAL_Site_Country_Service::country_code()
        );

        if ($country === '' || $available < 1) {
            return __('Import a city list so employers can choose a job location.', 'wp-job-portal');
        }

        /* translators: 1: number of cities, 2: country name. */
        return sprintf(
            /* translators: 1: available, 2: country */ _n(
                'Employers cannot choose a job location yet. %1$d city is ready to import for %2$s.',
                'Employers cannot choose a job location yet. %1$d cities are ready to import for %2$s.',
                $available,
                'wp-job-portal'
            ),
            $available,
            $country
        );
    }

    /**
     * @return array
     */
    private static function default_page_status() {
        $page_id = absint(self::configuration_value('default_pageid', 0));
        if ($page_id <= 0) {
            return array(
                'complete' => false,
                'message' => __('Choose a published WordPress page as the default WP Job Portal page.', 'wp-job-portal'),
            );
        }

        $page = get_post($page_id);
        if (!$page || 'page' !== $page->post_type || 'publish' !== $page->post_status) {
            return array(
                'complete' => false,
                'message' => __('The configured default page is missing, deleted, or not published.', 'wp-job-portal'),
            );
        }

        $content = (string) $page->post_content;
        $has_shortcode = (bool) preg_match('/\[\s*wpjobportal_[a-z0-9_-]+(?:\s|\])/i', $content);
        $has_block = (bool) preg_match('/<!--\s+wp:wpjobportal\/[a-z0-9_-]+/i', $content);
        if (!$has_shortcode && !$has_block) {
            return array(
                'complete' => false,
                'message' => __('The configured default page does not contain a WP Job Portal shortcode or block.', 'wp-job-portal'),
            );
        }

        return array('complete' => true, 'message' => '');
    }

    /**
     * @param string $name    Configuration name.
     * @param mixed  $default Fallback value.
     * @return mixed
     */
    private static function configuration_value($name, $default = '') {
        global $wpdb;

        $value = wpjobportaldb_direct::get_var($wpdb->prepare(
            "SELECT `configvalue` FROM %i WHERE `configname` = %s LIMIT 1",
            $wpdb->prefix . 'wj_portal_config',
            sanitize_key($name)
        ));

        return null === $value ? $default : $value;
    }

    /**
     * @return array
     */
    private static function item($id, $label, $description, $complete, $url, $action) {
        return array(
            'id' => sanitize_key($id),
            'label' => (string) $label,
            'description' => (string) $description,
            'complete' => (bool) $complete,
            'url' => esc_url_raw($url),
            'action' => (string) $action,
        );
    }
}
