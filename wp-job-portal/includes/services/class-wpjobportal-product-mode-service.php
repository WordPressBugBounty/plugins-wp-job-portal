<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Stable product-mode boundary for the modern core experience.
 *
 * Existing sites remain in legacy compatibility mode until an administrator
 * explicitly selects Careers & ATS or Job Marketplace. This prevents an
 * upgrade from silently rewriting established configuration.
 */
class WPJOBPORTAL_Product_Mode_Service {

    const OPTION = 'wpjobportal_product_mode';
    const SELECTED_AT_OPTION = 'wpjobportal_product_mode_selected_at';
    const MODE_LEGACY = 'legacy';
    const MODE_CAREERS = 'careers_ats';
    const MODE_MARKETPLACE = 'marketplace';

    /**
     * Register presentation-only mode helpers.
     */
    public static function register() {
        add_filter('admin_body_class', array(__CLASS__, 'filter_admin_body_class'));
        add_filter('body_class', array(__CLASS__, 'filter_body_classes'));
        add_action('admin_notices', array(__CLASS__, 'render_mode_notice'));
    }

    /**
     * Return the explicitly selected mode or the non-mutating legacy state.
     *
     * @return string
     */
    public static function get_mode() {
        $mode = sanitize_key((string) get_option(self::OPTION, ''));

        if (in_array($mode, self::selectable_modes(), true)) {
            return $mode;
        }

        return self::MODE_LEGACY;
    }

    /**
     * Infer the setup-card selection from the old compatibility option.
     * This does not persist a modern mode.
     *
     * @return string
     */
    public static function get_suggested_mode() {
        $mode = self::get_mode();
        if (self::MODE_LEGACY !== $mode) {
            return $mode;
        }

        return absint(get_option('wpjobportal_multiple_employers', 1)) === 1
            ? self::MODE_MARKETPLACE
            : self::MODE_CAREERS;
    }

    /**
     * @return bool
     */
    public static function is_selected() {
        return self::MODE_LEGACY !== self::get_mode();
    }

    /**
     * @param string|null $mode Optional mode.
     * @return string
     */
    public static function get_label($mode = null) {
        if (null === $mode) {
            $mode = self::get_mode();
        }

        switch ($mode) {
            case self::MODE_CAREERS:
                return __('Careers & ATS', 'wp-job-portal');
            case self::MODE_MARKETPLACE:
                return __('Job Marketplace', 'wp-job-portal');
            default:
                return __('Existing-site compatibility', 'wp-job-portal');
        }
    }

    /**
     * Persist an explicit mode and its compatibility settings.
     *
     * @param string $mode Requested mode.
     * @return bool
     */
    public static function apply_mode($mode) {
        global $wpdb;

        $mode = sanitize_key((string) $mode);
        if (!in_array($mode, self::selectable_modes(), true)) {
            return false;
        }

        $profile = self::get_configuration_profile($mode);
        if (
            !is_array($profile)
            || !isset($profile['configuration'], $profile['company_required'], $profile['multiple_employers'])
            || !is_array($profile['configuration'])
        ) {
            return false;
        }
        $config_table = $wpdb->prefix . 'wj_portal_config';
        $fields_table = $wpdb->prefix . 'wj_portal_fieldsordering';

        foreach ($profile['configuration'] as $name => $value) {
            $result = $wpdb->query($wpdb->prepare(
                "UPDATE `{$config_table}` SET `configvalue` = %s WHERE `configname` = %s",
                (string) $value,
                (string) $name
            ));

            if (false === $result) {
                return false;
            }
        }

        $field_result = $wpdb->query($wpdb->prepare(
            "UPDATE `{$fields_table}` SET `required` = %d WHERE `field` = %s",
            absint($profile['company_required']),
            'company'
        ));
        if (false === $field_result) {
            return false;
        }

        self::update_non_autoloaded_option(
            'wpjobportal_multiple_employers',
            absint($profile['multiple_employers'])
        );
        self::update_non_autoloaded_option(self::OPTION, $mode);
        self::update_non_autoloaded_option(self::SELECTED_AT_OPTION, current_time('mysql', true));

        if (class_exists('WPJOBPORTAL_Audit_Service')) {
            WPJOBPORTAL_Audit_Service::record(
                'product_mode_selected',
                'success',
                array('mode' => $mode)
            );
        }

        do_action('wpjobportal_product_mode_changed', $mode, $profile);
        return true;
    }

    /**
     * @return array
     */
    public static function get_status() {
        $mode = self::get_mode();
        $multiple_employers = absint(get_option('wpjobportal_multiple_employers', 1));
        $aligned = self::MODE_LEGACY === $mode
            || (self::MODE_CAREERS === $mode && 0 === $multiple_employers)
            || (self::MODE_MARKETPLACE === $mode && 1 === $multiple_employers);

        return array(
            'mode' => $mode,
            'label' => self::get_label($mode),
            'selected' => self::MODE_LEGACY !== $mode,
            'selected_at' => (string) get_option(self::SELECTED_AT_OPTION, ''),
            'multiple_employers' => $multiple_employers,
            'aligned' => $aligned,
        );
    }

    /**
     * @param string $classes Existing class string.
     * @return string
     */
    public static function filter_admin_body_class($classes) {
        $classes .= ' wpjobportal-design-system wpjobportal-mode-' . sanitize_html_class(self::get_mode());
        return trim($classes);
    }

    /**
     * @param array $classes Existing body classes.
     * @return array
     */
    public static function filter_body_classes($classes) {
        $classes[] = 'wpjobportal-design-system';
        $classes[] = 'wpjobportal-mode-' . sanitize_html_class(self::get_mode());
        return array_values(array_unique($classes));
    }

    /**
     * Invite upgraded sites to choose a mode without forcing a migration.
     */
    public static function render_mode_notice() {
        if (self::is_selected() || (!current_user_can('wpjobportal') && !current_user_can('manage_options'))) {
            return;
        }

        $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
        if ('wpjobportal' !== $page) {
            return;
        }

        $url = admin_url('admin.php?page=wpjobportal_postinstallation&wpjobportallt=quickstart');
        ?>
        <div class="notice notice-info wpjobportal-mode-notice">
            <p>
                <strong><?php echo esc_html__('Choose how you use WP Job Portal.', 'wp-job-portal'); ?></strong>
                <?php echo esc_html__('Your existing settings are unchanged. Select Careers & ATS or Job Marketplace when you are ready to use mode-specific setup and navigation.', 'wp-job-portal'); ?>
                <a href="<?php echo esc_url($url); ?>"><?php echo esc_html__('Choose setup mode', 'wp-job-portal'); ?></a>
                <a href="<?php echo esc_url(WPJOBPORTAL_Mode_Policy_Service::guide_url()); ?>"><?php echo esc_html__('Compare the modes', 'wp-job-portal'); ?></a>
            </p>
        </div>
        <?php
    }

    /**
     * @return array
     */
    private static function selectable_modes() {
        return array(self::MODE_CAREERS, self::MODE_MARKETPLACE);
    }

    /**
     * @param string $mode Selected mode.
     * @return array
     */
    private static function get_configuration_profile($mode) {
        if (self::MODE_CAREERS === $mode) {
            $profile = array(
                'multiple_employers' => 0,
                'company_required' => 0,
                'configuration' => array(
                    'disable_employer' => 0,
                    'employerview_js_controlpanel' => 1,
                    'visitor_can_apply_to_job' => 1,
                    'visitor_can_add_resume' => 1,
                    'quick_apply_for_user' => 1,
                    'quick_apply_for_visitor' => 1,
                    'cur_location' => 0,
                ),
            );
        } else {
            $profile = array(
                'multiple_employers' => 1,
                'company_required' => 1,
                'configuration' => array(
                    'disable_employer' => 0,
                ),
            );
        }

        // The values above are starting points a site owner may change later.
        // Anything the mode actually locks is owned by the feature map, so it
        // is merged last and wins over a stale entry here.
        $profile['configuration'] = array_merge(
            $profile['configuration'],
            WPJOBPORTAL_Mode_Policy_Service::get_locked_settings($mode)
        );

        // The employer-count flag is owned by the same map, so apply_mode() and
        // the migration cannot end up writing different values for it.
        $wpjobportal_flag = WPJOBPORTAL_Mode_Policy_Service::get_multiple_employers_flag($mode);
        if (null !== $wpjobportal_flag) {
            $profile['multiple_employers'] = $wpjobportal_flag;
        }

        return apply_filters('wpjobportal_product_mode_profile', $profile, $mode);
    }

    /**
     * Store small mode metadata without adding new autoloaded options.
     *
     * @param string $name Option name.
     * @param mixed  $value Option value.
     */
    private static function update_non_autoloaded_option($name, $value) {
        if (false === get_option($name, false)) {
            add_option($name, $value, '', false);
            return;
        }

        update_option($name, $value, false);
    }
}
