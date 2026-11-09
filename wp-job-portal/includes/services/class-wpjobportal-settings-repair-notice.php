<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * One-time notice after the 2.8.0 migration put page settings back to none (D-11).
 *
 * 280.sql records the names it reset in the option below; this lists them once,
 * by their Settings labels, until an administrator dismisses it.
 */
class WPJOBPORTAL_Settings_Repair_Notice {

    const OPTION = 'wpjobportal_280_page_settings_reset';
    const DISMISS_ACTION = 'wpjobportal_dismiss_settings_repair';

    public static function register() {
        add_action('admin_notices', array(__CLASS__, 'render'));
        add_action('admin_post_' . self::DISMISS_ACTION, array(__CLASS__, 'dismiss'));
    }

    private static function labels() {
        return array(
            'terms_and_conditions_page_company' => __('Company Terms Page', 'wp-job-portal'),
            'terms_and_conditions_page_job' => __('Job Terms Page', 'wp-job-portal'),
            'terms_and_conditions_page_resume' => __('Resume Terms Page', 'wp-job-portal'),
            'register_employer_redirect_page' => __('Employer After-Registration Page', 'wp-job-portal'),
            'register_jobseeker_redirect_page' => __('Job Seeker After-Registration Page', 'wp-job-portal'),
            'visitor_add_job_redirect_page' => __('Visitor Job Post Redirect Page', 'wp-job-portal'),
            'visitor_add_resume_redirect_page' => __('Visitor Resume Post Redirect Page', 'wp-job-portal'),
        );
    }

    /**
     * @return void
     */
    public static function render() {
        if (!current_user_can('manage_options')) {
            return;
        }
        $page = sanitize_key((string) WPJOBPORTALrequest::getVar('page', 'get', ''));
        $reset = (string) get_option(self::OPTION, '');
        if (strpos($page, 'wpjobportal') !== 0 || $reset === '') {
            return;
        }
        $labels = self::labels();
        $names = array();
        foreach (explode(',', $reset) as $key) {
            $key = sanitize_key($key);
            if (isset($labels[$key])) {
                $names[] = $labels[$key];
            }
        }
        if (!$names) {
            return;
        }
        $dismiss = wp_nonce_url(admin_url('admin-post.php?action=' . self::DISMISS_ACTION), self::DISMISS_ACTION);
        ?>
        <div class="notice notice-warning wpjobportal-settings-repair-notice">
            <p>
                <strong><?php echo esc_html__('Some page settings were set back to none.', 'wp-job-portal'); ?></strong>
                <?php
                echo esc_html__('An earlier version saved these as WordPress\'s Sample Page (or a page that no longer exists) whenever Settings were saved, even if nobody chose it:', 'wp-job-portal');
                echo ' ' . esc_html(implode(', ', $names)) . '. ';
                echo esc_html__('Pick the right page in Settings if you use them.', 'wp-job-portal');
                ?>
            </p>
            <p>
                <a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_configuration')); ?>"><?php echo esc_html__('Open Settings', 'wp-job-portal'); ?></a>
                <a class="button-link" href="<?php echo esc_url($dismiss); ?>"><?php echo esc_html__('Dismiss', 'wp-job-portal'); ?></a>
            </p>
        </div>
        <?php
    }

    /**
     * @return void
     */
    public static function dismiss() {
        if (!current_user_can('manage_options') || !check_admin_referer(self::DISMISS_ACTION)) {
            wp_die(esc_html__('You are not allowed to do that.', 'wp-job-portal'));
        }
        delete_option(self::OPTION);
        wp_safe_redirect(wp_get_referer() ? wp_get_referer() : admin_url('admin.php?page=wpjobportal'));
        exit;
    }
}
