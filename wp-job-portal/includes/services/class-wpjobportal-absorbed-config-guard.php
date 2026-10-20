<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Keep a superseded add-on's settings alive after it is switched off.
 *
 * Every legacy add-on ends its deactivation by disabling its own settings:
 *
 *     UPDATE wj_portal_config SET configvalue = 0 WHERE configname = ...
 *
 * That was right when deactivating meant the feature was going away — it hid
 * the add-on's screens from a site that no longer had it, and the values were
 * stashed in an option so reactivating brought them back.
 *
 * It is wrong now. Core and the suites took those features over and read those
 * same rows, so the natural thing for an administrator to do after upgrading —
 * tidy up by deactivating the old add-ons — quietly turns off the settings the
 * new code depends on. The feature is still installed and still licensed, and
 * it stops working. Job alerts is the one that will be noticed first.
 *
 * The fix cannot live in the add-on. The copy on a customer's site is the
 * version they already installed, and its deactivation hook cannot be changed
 * retroactively. So Core watches instead: it records the settings just before a
 * superseded add-on deactivates, and puts back anything that was switched off
 * on the way out — only rows that were on and are now off. An administrator who
 * turned a setting off deliberately keeps it off.
 *
 * This holds whether or not the replacing product is installed yet. An
 * administrator who deactivates the old add-on first and installs its product
 * afterwards must not find the feature's settings switched off. Until the
 * product arrives the feature itself stays off, because it is not in
 * wpjobportal::$_active_addons.
 *
 * @see WPJOBPORTAL_Absorbed_Features for the features Core itself took over.
 */
class WPJOBPORTAL_Absorbed_Config_Guard {

    /** @var array Settings captured before the add-on's own hook runs. */
    private static $snapshot = array();

    public static function register() {
        // Before the add-on's deactivation hook has a chance to zero anything.
        add_action('deactivate_plugin', array(__CLASS__, 'remember'), 1, 1);

        // After it has. Priority is late so any other listener has finished.
        add_action('deactivated_plugin', array(__CLASS__, 'restore'), 99, 1);
        // No notice: the settings simply stay as they were, which is what the
        // administrator expects; a message about it only confused.
    }

    /**
     * Which product provides each superseded add-on's feature.
     *
     * A slug maps to the product that took the feature over. The settings guard
     * protects every slug listed here; WPJOBPORTAL_Superseded_Addons also reads the
     * map to tell the administrator which product replaces each old add-on.
     *
     * @return array slug => provider.
     */
    public static function providers() {
        $providers = array();

        // Absorbed into free Core, including the three split by tier: Core owns
        // the implementation of all of them.
        foreach (array(
            'allcompanies', 'copyjob', 'shortlist', 'socialshare', 'tag',
            'rssfeedback', 'cronjob', 'joblistingenhancer', 'tellfriend',
            'print', 'widgets', 'jobalert',
        ) as $slug) {
            $providers[$slug] = 'core';
        }

        foreach (array(
            'pdf', 'export', 'allresumes', 'advanceresumebuilder', 'multiresume',
            'coverletter', 'visitorapplyjob', 'resumesearch', 'departments',
            'folder', 'message', 'reports',
            // Pro's address-data and maps modules took this over. Left off the
            // list, switching the add-on off still zeroed newtyped_cities - a
            // setting Core's own company forms read.
            'addressdata',
        ) as $slug) {
            $providers[$slug] = 'pro';
        }

        foreach (array(
            'aijobsearch', 'airesumesearch', 'aisuggestedjobs',
            'aisuggestedresumes', 'smartmatching',
        ) as $slug) {
            $providers[$slug] = 'matching';
        }

        $providers['resumeaction'] = 'recruit';

        foreach (array(
            'featuredjob', 'featuredcompany', 'featureresume', 'multicompany',
            'visitorcanaddjob',
            // Retired: the Suite ships the monetisation code itself now.
            // Listing it here before that was true would have been wrong —
            // the standalone plugin still owned the feature, and the guard
            // would have restored settings on deactivation as though a
            // replacement existed.
            'credits',
        ) as $slug) {
            $providers[$slug] = 'marketplace';
        }

        /**
         * Adjust which product provides a superseded add-on's feature.
         *
         * @param array $providers slug => core|pro|matching|recruit|marketplace.
         */
        return (array) apply_filters('wpjobportal_absorbed_config_providers', $providers);
    }

    /**
     * Is the product that took this feature over actually here?
     *
     * @param string $provider Provider key.
     * @return bool
     */
    public static function provider_present($provider) {
        switch ($provider) {
            case 'core':
                return true;
            case 'pro':
                return defined('WPJOBPORTAL_PRO_VERSION');
            case 'matching':
                return defined('WPJOBPORTAL_MATCHING_VERSION');
            case 'recruit':
                return class_exists('WPJOBPORTAL_Recruit_Suite_Service');
            case 'marketplace':
                return class_exists('WPJOBPORTAL_Marketplace_Featured_Job_Service')
                    || class_exists('wpjobportalmarketplacesuite');
        }

        return false;
    }

    /**
     * The add-on slug a plugin file belongs to, if it is one of ours.
     *
     * @param string $plugin Plugin file, as WordPress passes it.
     * @return string Empty when it is not a superseded add-on.
     */
    public static function slug_for($plugin) {
        $plugin = str_replace('\\', '/', (string) $plugin);
        $folder = strtok($plugin, '/');

        if (strpos($folder, 'wp-job-portal-') !== 0) {
            return '';
        }

        $slug = substr($folder, strlen('wp-job-portal-'));
        $providers = self::providers();

        return isset($providers[$slug]) ? $slug : '';
    }

    /**
     * Record the add-on's settings before its own hook can disable them.
     *
     * @param string $plugin Plugin being deactivated.
     * @return void
     */
    public static function remember($plugin) {
        $slug = self::slug_for($plugin);
        if ($slug === '') {
            return;
        }

        global $wpdb;

        // Every superseded add-on's settings, not only this one's: an old
        // deactivation hook may switch off another add-on's rows too (Tell a
        // Friend's zeroes Address Data's, newtyped_cities among them).
        $slugs = array_keys(self::providers());
        $rows = wpjobportaldb_direct::get_results($wpdb->prepare(
            "SELECT configname, configvalue FROM `{$wpdb->prefix}wj_portal_config` WHERE addon IN (" . implode(', ', array_fill(0, count($slugs), '%s')) . ")",
            $slugs
        ), ARRAY_A);

        $values = array();
        foreach ((array) $rows as $row) {
            $values[$row['configname']] = $row['configvalue'];
        }

        self::$snapshot[$slug] = $values;
    }

    /**
     * Put back anything the deactivation switched off.
     *
     * @param string $plugin Plugin that was deactivated.
     * @return void
     */
    public static function restore($plugin) {
        $slug = self::slug_for($plugin);
        if ($slug === '' || empty(self::$snapshot[$slug])) {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'wj_portal_config';
        $restored = 0;

        foreach (self::$snapshot[$slug] as $name => $was) {
            // Only what the deactivation itself turned off. A setting an
            // administrator had already disabled stays disabled, and one that
            // still holds its value is left alone.
            if ((string) $was === '0' || (string) $was === '') {
                continue;
            }

            $now = wpjobportaldb_direct::get_var($wpdb->prepare(
                "SELECT configvalue FROM %i WHERE configname = %s LIMIT 1",
                $table,
                $name
            ));

            if ((string) $now !== '0') {
                continue;
            }

            wpjobportaldb_direct::update(
                $table,
                array('configvalue' => $was),
                array('configname' => $name),
                array('%s'),
                array('%s')
            );

            $restored++;
        }

        unset(self::$snapshot[$slug]);

        if ($restored < 1) {
            return;
        }

        /**
         * Settings were kept alive after a superseded add-on was switched off.
         *
         * @param string $slug     Add-on slug.
         * @param int    $restored How many settings were put back.
         */
        do_action('wpjobportal_absorbed_config_restored', $slug, $restored);
    }
}
