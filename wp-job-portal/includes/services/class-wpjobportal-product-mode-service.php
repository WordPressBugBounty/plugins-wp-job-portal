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
    const SNAPSHOT_OPTION = 'wpjobportal_product_mode_saved_settings';
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
    /**
     * W-18: what switching to a mode would change on this site, for the confirm step.
     *
     * @return array[] name, label, from, to
     */
    public static function changes_for($mode) {
        global $wpdb;
        $mode = sanitize_key((string) $mode);
        if (!in_array($mode, self::selectable_modes(), true)) {
            return array();
        }
        $profile = self::get_configuration_profile($mode);
        // The page always restores saved settings when there are some (its
        // Restore box is ticked and cannot be cleared), so preview the same
        // values apply_mode() will write, not the bare preset.
        if (self::has_saved_settings($mode)) {
            $saved = get_option(self::SNAPSHOT_OPTION, array());
            $profile['configuration'] = array_merge($profile['configuration'], (array) $saved[$mode], WPJOBPORTAL_Mode_Policy_Service::get_locked_settings($mode));
        }
        $current = self::current_values(array_keys($profile['configuration']));
        $labels = array(
            'disable_employer' => __('Employer area', 'wp-job-portal'),
            'employerview_js_controlpanel' => __('Employers can see the job seeker dashboard', 'wp-job-portal'),
            'visitor_can_apply_to_job' => __('Visitors can apply without an account', 'wp-job-portal'),
            'visitor_can_add_resume' => __('Visitors can add a resume', 'wp-job-portal'),
            'quick_apply_for_user' => __('Quick apply for members', 'wp-job-portal'),
            'quick_apply_for_visitor' => __('Quick apply for visitors', 'wp-job-portal'),
            'cur_location' => __('Current location field', 'wp-job-portal'),
            'jobautoapprove' => __('Approve new jobs automatically', 'wp-job-portal'),
            'companyautoapprove' => __('Approve new companies automatically', 'wp-job-portal'),
            'showemployerlink' => __('Employers can register', 'wp-job-portal'),
            'visitor_can_post_job' => __('Visitors can post jobs', 'wp-job-portal'),
            'submission_type' => __('Paid submissions', 'wp-job-portal'),
        );
        $word = function ($value, $name) {
            if ($name === 'submission_type') {
                return (string) $value === '1' ? __('Free', 'wp-job-portal') : __('Paid', 'wp-job-portal');
            }
            return (string) $value === '1' ? __('On', 'wp-job-portal') : ((string) $value === '0' ? __('Off', 'wp-job-portal') : (string) $value);
        };
        $changes = array();
        foreach ($profile['configuration'] as $name => $value) {
            if (isset($current[$name]) && (string) $current[$name] !== (string) $value) {
                $changes[] = array(
                    'name' => $name,
                    'label' => isset($labels[$name]) ? $labels[$name] : ucfirst(str_replace('_', ' ', $name)),
                    'from' => $word($current[$name], $name),
                    'to' => $word($value, $name),
                );
            }
        }
        return $changes;
    }

    /** @return array configname => value, for the names that exist */
    private static function current_values($names) {
        global $wpdb;
        if (empty($names)) {
            return array();
        }
        $rows = wpjobportaldb_direct::get_results($wpdb->prepare(
            "SELECT configname, configvalue FROM `" . $wpdb->prefix . "wj_portal_config` WHERE configname IN (" . implode(',', array_fill(0, count($names), '%s')) . ")",
            $names
        ));
        $values = array();
        foreach ((array) $rows as $row) {
            $values[$row->configname] = $row->configvalue;
        }
        return $values;
    }

    /** W-18: whether this site has settings saved from when it last used this mode. */
    public static function has_saved_settings($mode) {
        $saved = get_option(self::SNAPSHOT_OPTION, array());
        return is_array($saved) && !empty($saved[sanitize_key((string) $mode)]);
    }

    /** Remember the values the mode being left would overwrite, so they can come back with it. */
    private static function remember_settings($leaving, $names) {
        $saved = get_option(self::SNAPSHOT_OPTION, array());
        $saved = is_array($saved) ? $saved : array();
        $saved[$leaving] = self::current_values($names);
        self::update_non_autoloaded_option(self::SNAPSHOT_OPTION, $saved);
    }

    /**
     * @param string $mode    The mode to switch to.
     * @param bool   $restore W-18: bring back the values saved when this mode was last left.
     */
    public static function apply_mode($mode, $restore = false) {
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

        // W-18: a site that really changes mode can get its settings back later.
        $leaving = self::is_selected() ? self::get_mode() : '';
        if ($leaving !== '' && $leaving !== $mode) {
            self::remember_settings($leaving, array_keys($profile['configuration']));
        }
        if ($restore && self::has_saved_settings($mode)) {
            $saved = get_option(self::SNAPSHOT_OPTION, array());
            // What the mode locks still wins, so saved values cannot break it.
            $profile['configuration'] = array_merge($profile['configuration'], (array) $saved[$mode], WPJOBPORTAL_Mode_Policy_Service::get_locked_settings($mode));
        }

        foreach ($profile['configuration'] as $name => $value) {
            $result = wpjobportaldb_direct::query($wpdb->prepare(
                "UPDATE %i SET `configvalue` = %s WHERE `configname` = %s",
                $config_table,
                (string) $value,
                (string) $name
            ));

            if (false === $result) {
                return false;
            }
        }

        $field_result = wpjobportaldb_direct::query($wpdb->prepare(
            "UPDATE %i SET `required` = %d WHERE `field` = %s",
            $fields_table,
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
        // A page that shows the portal: style.css hides the theme's page title
        // on it, because every portal screen prints its own heading.
        if (is_singular()) {
            $wpjobportal_post = get_post();
            if ($wpjobportal_post && strpos((string) $wpjobportal_post->post_content, 'wpjobportal') !== false) {
                $classes[] = 'wpjobportal-page';
            }
        }
        return array_values(array_unique($classes));
    }

    /**
     * Invite upgraded sites to choose a mode without forcing a migration.
     */
    public static function render_mode_notice() {
        if (self::is_selected() || (!current_user_can('wpjobportal') && !current_user_can('manage_options'))) {
            return;
        }

        $page = sanitize_key((string) WPJOBPORTALrequest::getVar('page', 'get', ''));
        if ('wpjobportal' !== $page) {
            return;
        }

        $url = admin_url('admin.php?page=wpjobportal_postinstallation&wpjobportallt=quickstart');
        ?>
        <div class="notice notice-info wpjobportal-mode-notice">
            <p>
                <strong><?php echo esc_html__('Choose how you use WP Job Portal.', 'wp-job-portal'); ?></strong>
                <?php echo esc_html__('Your existing settings are unchanged. Select Careers & ATS or Job Marketplace when you are ready to use mode-specific setup and navigation.', 'wp-job-portal'); ?>
            </p>
            <p>
                <a class="button button-primary" href="<?php echo esc_url($url); ?>"><?php echo esc_html__('Choose setup mode', 'wp-job-portal'); ?></a>
                <a class="button" href="<?php echo esc_url(WPJOBPORTAL_Mode_Policy_Service::guide_url()); ?>"><?php echo esc_html__('Compare the modes', 'wp-job-portal'); ?></a>
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
                    // Despite its name, 1 means the employer area is ON: the
                    // settings screen labels it "Enable Employer Area" and every
                    // controller lets employers in only when it is 1. Writing 0
                    // here switched the employer area off for every site that
                    // picked a mode.
                    'disable_employer' => 1,
                    'employerview_js_controlpanel' => 1,
                    'visitor_can_apply_to_job' => 1,
                    'visitor_can_add_resume' => 1,
                    'quick_apply_for_user' => 1,
                    'quick_apply_for_visitor' => 1,
                    'cur_location' => 0,
                    // Careers mode has no approval queue for jobs, so a job
                    // left waiting could never be published.
                    'jobautoapprove' => 1,
                ),
            );
        } else {
            $profile = array(
                'multiple_employers' => 1,
                'company_required' => 1,
                'configuration' => array(
                    'disable_employer' => 1, // 1 = employer area on; see above
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
