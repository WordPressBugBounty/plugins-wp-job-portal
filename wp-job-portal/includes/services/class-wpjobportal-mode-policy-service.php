<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Single source of truth for what each product mode turns on and off.
 *
 * Careers & ATS and Job Marketplace differ in a handful of features, and each
 * of those features owns a small set of configuration values. Rather than
 * repeating `if (careers)` tests in menus, forms, save handlers and upgrade
 * code, every consumer reads the one map declared in self::features().
 *
 * The chain the map drives is:
 *
 *     selected mode -> locked settings -> configuration UI -> save validation
 *                   -> menu visibility -> mode guide
 *
 * A feature that is unavailable in a mode declares the value each of its
 * settings is forced to, so "unavailable" always has a concrete, enforceable
 * meaning instead of only hiding a menu entry.
 */
class WPJOBPORTAL_Mode_Policy_Service {

    /**
     * Records the policy revision already forced onto this site, so the 2.6.0
     * alignment runs once per revision instead of on every admin request.
     */
    const APPLIED_VERSION_OPTION = 'wpjobportal_mode_policy_applied_version';

    /**
     * Bump only when the map below changes in a way existing sites must adopt.
     */
    const POLICY_VERSION = '260';

    /**
     * The mode feature map. This is the single source of truth.
     *
     * Each entry declares:
     *   label            Human name used in the guide and lock messages.
     *   description      One sentence explaining what the feature does.
     *   available        Per mode: is the feature offered at all?
     *   settings         configname => array(mode => forced value). A mode that
     *                    is absent for a setting leaves that setting free.
     *   menu             Where the feature appears in the admin menu, or ''.
     *   unavailable_note Why it is hidden, shown in the guide and on locks.
     *
     * @return array
     */
    public static function features() {
        $careers = WPJOBPORTAL_Product_Mode_Service::MODE_CAREERS;
        $marketplace = WPJOBPORTAL_Product_Mode_Service::MODE_MARKETPLACE;
        $legacy = WPJOBPORTAL_Product_Mode_Service::MODE_LEGACY;

        $features = array(
            'job_approval' => array(
                'label' => __('Job approval queue', 'wp-job-portal'),
                'description' => __('Hold submitted jobs for review before they appear on the site.', 'wp-job-portal'),
                'available' => array($careers => false, $marketplace => true, $legacy => true),
                'settings' => array(
                    // Auto approve on means nothing ever reaches the queue.
                    'jobautoapprove' => array($careers => '1'),
                ),
                'menu' => __('Jobs → Approval Queue', 'wp-job-portal'),
                'unavailable_note' => __('In Careers & ATS you publish your own jobs from the admin, so there is nothing waiting to be moderated. Jobs are approved automatically.', 'wp-job-portal'),
            ),
            'company_approval' => array(
                'label' => __('Company approval queue', 'wp-job-portal'),
                'description' => __('Hold new company profiles for review before they are listed.', 'wp-job-portal'),
                'available' => array($careers => false, $marketplace => true, $legacy => true),
                'settings' => array(
                    'companyautoapprove' => array($careers => '1'),
                ),
                'menu' => __('Companies → Approval Queue', 'wp-job-portal'),
                'unavailable_note' => __('Careers & ATS runs on a single company profile that you manage yourself, so there are no third-party companies to approve.', 'wp-job-portal'),
            ),
            'employer_accounts' => array(
                'label' => __('Employer accounts', 'wp-job-portal'),
                'description' => __('Let outside employers register, own a company profile, and post their own jobs.', 'wp-job-portal'),
                'available' => array($careers => false, $marketplace => true, $legacy => true),
                'settings' => array(
                    'showemployerlink' => array($careers => '0', $marketplace => '1'),
                    'visitor_can_post_job' => array($careers => '0'),
                ),
                'menu' => '',
                'unavailable_note' => __('Careers & ATS is a single-company hiring site. Employer self-registration is off so that only your team posts jobs.', 'wp-job-portal'),
            ),
            'credits' => array(
                'label' => __('Credits and paid submissions', 'wp-job-portal'),
                'description' => __('Charge employers per listing or through a membership package.', 'wp-job-portal'),
                'available' => array($careers => false, $marketplace => true, $legacy => true),
                'settings' => array(
                    // 1 = Free. Per Listing and Membership both need employers
                    // who pay to post, which Careers & ATS does not have.
                    'submission_type' => array($careers => '1'),
                ),
                'menu' => __('Credits', 'wp-job-portal'),
                'unavailable_note' => __('There is nobody to bill in Careers & ATS because you are the only publisher, so submissions stay free.', 'wp-job-portal'),
            ),
        );

        /**
         * Allows a narrowly scoped extension to add or adjust one mode feature.
         * Consumers all read the filtered map, so an addition is picked up by
         * the configuration UI, the save guard, and the mode guide at once.
         *
         * @param array $features The mode feature map.
         */
        $filtered = apply_filters('wpjobportal_mode_features', $features);
        return is_array($filtered) ? $filtered : $features;
    }

    /**
     * @param string      $feature Feature key.
     * @param string|null $mode    Optional mode; defaults to the selected mode.
     * @return bool
     */
    public static function is_feature_available($feature, $mode = null) {
        $features = self::features();
        if (!isset($features[$feature])) {
            return true;
        }

        $mode = self::resolve_mode($mode);
        return !isset($features[$feature]['available'][$mode])
            || (bool) $features[$feature]['available'][$mode];
    }

    /**
     * Every configuration value the given mode controls, as name => value.
     *
     * @param string|null $mode Optional mode.
     * @return array
     */
    public static function get_locked_settings($mode = null) {
        $mode = self::resolve_mode($mode);
        $locked = array();

        foreach (self::features() as $feature) {
            if (empty($feature['settings']) || !is_array($feature['settings'])) {
                continue;
            }
            foreach ($feature['settings'] as $name => $per_mode) {
                if (is_array($per_mode) && isset($per_mode[$mode])) {
                    $locked[(string) $name] = (string) $per_mode[$mode];
                }
            }
        }

        return $locked;
    }

    /**
     * Lock details for one setting, or an empty array when it is free.
     *
     * @param string      $name Configuration name.
     * @param string|null $mode Optional mode.
     * @return array
     */
    public static function get_setting_lock($name, $mode = null) {
        $mode = self::resolve_mode($mode);
        $name = (string) $name;

        foreach (self::features() as $key => $feature) {
            if (empty($feature['settings'][$name]) || !is_array($feature['settings'][$name])) {
                continue;
            }
            if (!isset($feature['settings'][$name][$mode])) {
                continue;
            }

            return array(
                'feature' => $key,
                'label' => $feature['label'],
                'value' => (string) $feature['settings'][$name][$mode],
                'note' => isset($feature['unavailable_note']) ? $feature['unavailable_note'] : '',
                'mode' => $mode,
                'mode_label' => WPJOBPORTAL_Product_Mode_Service::get_label($mode),
            );
        }

        return array();
    }

    /**
     * @param string      $name Configuration name.
     * @param string|null $mode Optional mode.
     * @return bool
     */
    public static function is_setting_locked($name, $mode = null) {
        $lock = self::get_setting_lock($name, $mode);
        return !empty($lock);
    }

    /**
     * The one sentence shown wherever a locked control is rendered.
     *
     * @return string
     */
    public static function lock_message() {
        return __('This setting is controlled by the selected WP Job Portal mode.', 'wp-job-portal');
    }

    /**
     * @return string
     */
    public static function guide_url() {
        return admin_url('admin.php?page=wpjobportal_postinstallation&wpjobportallt=modeguide');
    }

    /**
     * Overwrite mode-controlled values in a submitted configuration payload.
     *
     * The configuration form disables these controls, but a disabled control is
     * a convenience, not a boundary: it is absent from the POST body and can be
     * re-enabled in the browser. Save handlers run the payload through here so
     * the stored value is the mode's value either way.
     *
     * @param array       $data Submitted configuration values.
     * @param string|null $mode Optional mode.
     * @return array
     */
    public static function enforce_settings($data, $mode = null) {
        if (!is_array($data)) {
            return $data;
        }

        foreach (self::get_locked_settings($mode) as $name => $value) {
            // Set unconditionally. An absent key means the browser dropped a
            // disabled control, and a present key may have been tampered with.
            $data[$name] = $value;
        }

        return $data;
    }

    /**
     * Write every mode-controlled value to the configuration table.
     *
     * Only the names in the map are touched. Unrelated settings, content and
     * user data are never read or modified here.
     *
     * @param string|null $mode Optional mode.
     * @return bool True when all writes succeeded.
     */
    public static function apply_to_site($mode = null) {
        global $wpdb;

        $mode = self::resolve_mode($mode);
        $locked = self::get_locked_settings($mode);
        $ok = true;

        if (!empty($locked)) {
            $table = $wpdb->prefix . 'wj_portal_config';

            foreach ($locked as $name => $value) {
                $result = wpjobportaldb_direct::query($wpdb->prepare(
                    "UPDATE %i SET `configvalue` = %s WHERE `configname` = %s AND `configvalue` <> %s",
                    $table,
                    (string) $value,
                    (string) $name,
                    (string) $value
                ));

                if (false === $result) {
                    $ok = false;
                }
            }
        }

        return self::sync_multiple_employers($mode) && $ok;
    }

    /**
     * The employer-count flag a mode implies, or null when it dictates none.
     *
     * Legacy sites keep whatever 2.5.x left behind, which is the same promise
     * the mode chooser makes about the rest of their settings.
     *
     * @param string|null $mode Optional mode.
     * @return int|null
     */
    public static function get_multiple_employers_flag($mode = null) {
        $mode = self::resolve_mode($mode);

        if (WPJOBPORTAL_Product_Mode_Service::MODE_CAREERS === $mode) {
            return 0;
        }
        if (WPJOBPORTAL_Product_Mode_Service::MODE_MARKETPLACE === $mode) {
            return 1;
        }

        return null;
    }

    /**
     * Keep the legacy wpjobportal_multiple_employers option level with the mode.
     *
     * Before 2.6.0 this option was the source of truth; now it is derived from
     * the mode. Two readers still depend on it -- the setup wizard's employer
     * steps and get_suggested_mode() -- so a stale value contradicts the menus,
     * which are driven by the mode. 2.5.x could also delete the option outright
     * during sample-data install, leaving sites whose flag was never chosen, so
     * this cannot assume the two already agree.
     *
     * @param string|null $mode Optional mode.
     * @return bool
     */
    private static function sync_multiple_employers($mode = null) {
        $flag = self::get_multiple_employers_flag($mode);
        if (null === $flag) {
            return true;
        }

        if (false === get_option('wpjobportal_multiple_employers', false)) {
            return add_option('wpjobportal_multiple_employers', $flag, '', false);
        }

        if (absint(get_option('wpjobportal_multiple_employers')) === $flag) {
            return true;
        }

        return update_option('wpjobportal_multiple_employers', $flag, false);
    }

    /**
     * Align an upgraded site with the mode it is already running in.
     *
     * Sites that never chose a mode stay in legacy compatibility and are left
     * exactly as they are, which is the same promise the mode chooser makes.
     * Nothing outside the feature map is written, and no data is removed.
     */
    public static function maybe_migrate() {
        if (self::POLICY_VERSION === (string) get_option(self::APPLIED_VERSION_OPTION, '')) {
            return;
        }

        if (!WPJOBPORTAL_Product_Mode_Service::is_selected()) {
            // Legacy sites are aligned by apply_mode() when a mode is chosen.
            self::remember_applied_version();
            return;
        }

        if (!self::apply_to_site()) {
            // Leave the marker unset so the next admin request retries.
            return;
        }

        if (class_exists('WPJOBPORTAL_Audit_Service')) {
            WPJOBPORTAL_Audit_Service::record(
                'mode_policy_applied',
                'success',
                array(
                    'mode' => WPJOBPORTAL_Product_Mode_Service::get_mode(),
                    'policy_version' => self::POLICY_VERSION,
                )
            );
        }

        self::remember_applied_version();
    }

    /**
     * Re-align immediately whenever the mode changes.
     */
    public static function register() {
        add_action('admin_init', array(__CLASS__, 'maybe_migrate'), 6);
        add_action('wpjobportal_product_mode_changed', array(__CLASS__, 'apply_to_site'), 10, 1);
    }

    /**
     * Comparison rows for the mode guide.
     *
     * @return array
     */
    public static function get_matrix() {
        $careers = WPJOBPORTAL_Product_Mode_Service::MODE_CAREERS;
        $marketplace = WPJOBPORTAL_Product_Mode_Service::MODE_MARKETPLACE;
        $rows = array();

        foreach (self::features() as $key => $feature) {
            $rows[] = array(
                'key' => $key,
                'label' => $feature['label'],
                'description' => isset($feature['description']) ? $feature['description'] : '',
                'menu' => isset($feature['menu']) ? $feature['menu'] : '',
                'note' => isset($feature['unavailable_note']) ? $feature['unavailable_note'] : '',
                'careers' => self::is_feature_available($key, $careers),
                'marketplace' => self::is_feature_available($key, $marketplace),
                'settings' => self::describe_settings($key),
            );
        }

        return $rows;
    }

    /**
     * Per-mode forced values for one feature, labelled for display.
     *
     * @param string $feature Feature key.
     * @return array
     */
    private static function describe_settings($feature) {
        $features = self::features();
        if (empty($features[$feature]['settings'])) {
            return array();
        }

        $described = array();
        foreach ($features[$feature]['settings'] as $name => $per_mode) {
            $described[] = array(
                'name' => (string) $name,
                'label' => self::setting_label($name),
                'careers' => isset($per_mode[WPJOBPORTAL_Product_Mode_Service::MODE_CAREERS])
                    ? self::value_label($name, $per_mode[WPJOBPORTAL_Product_Mode_Service::MODE_CAREERS])
                    : '',
                'marketplace' => isset($per_mode[WPJOBPORTAL_Product_Mode_Service::MODE_MARKETPLACE])
                    ? self::value_label($name, $per_mode[WPJOBPORTAL_Product_Mode_Service::MODE_MARKETPLACE])
                    : '',
            );
        }

        return $described;
    }

    /**
     * Display name for a configuration key, matching the settings screen.
     *
     * @param string $name Configuration name.
     * @return string
     */
    public static function setting_label($name) {
        $labels = array(
            'jobautoapprove' => __('Job Auto Approve', 'wp-job-portal'),
            'companyautoapprove' => __('Company Auto Approve', 'wp-job-portal'),
            'showemployerlink' => __('Allow Registration as Employer', 'wp-job-portal'),
            'visitor_can_post_job' => __('Visitor Can Post Job', 'wp-job-portal'),
            'submission_type' => __('Submission Type', 'wp-job-portal'),
        );

        return isset($labels[$name]) ? $labels[$name] : $name;
    }

    /**
     * Readable form of a forced value, for the guide and the lock text.
     *
     * @param string $name  Configuration name.
     * @param string $value Forced value.
     * @return string
     */
    public static function value_label($name, $value) {
        $value = (string) $value;

        if ('submission_type' === $name) {
            $options = array(
                '1' => __('Free', 'wp-job-portal'),
                '2' => __('Per Listing', 'wp-job-portal'),
                '3' => __('Membership', 'wp-job-portal'),
            );
            return isset($options[$value]) ? $options[$value] : $value;
        }

        if ('1' === $value) {
            return __('Yes', 'wp-job-portal');
        }
        if ('0' === $value) {
            return __('No', 'wp-job-portal');
        }

        return $value;
    }

    /**
     * @param string|null $mode Requested mode.
     * @return string
     */
    private static function resolve_mode($mode) {
        if (null === $mode || '' === $mode) {
            return WPJOBPORTAL_Product_Mode_Service::get_mode();
        }

        return sanitize_key((string) $mode);
    }

    /**
     * Store the marker without adding an autoloaded option.
     */
    private static function remember_applied_version() {
        if (false === get_option(self::APPLIED_VERSION_OPTION, false)) {
            add_option(self::APPLIED_VERSION_OPTION, self::POLICY_VERSION, '', false);
            return;
        }

        update_option(self::APPLIED_VERSION_OPTION, self::POLICY_VERSION, false);
    }
}
