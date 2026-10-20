<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Features that used to be sold as separate add-ons and now ship with Core.
 *
 * The conflict guard runs in the opposite direction to the one used by the Pro
 * and Matching suites. Those are add-ons deferring to Core; this is Core
 * deferring to an add-on. A customer who still has the old plugin installed
 * keeps being served by it, unchanged, until they remove it. Core only steps in
 * once it is gone.
 *
 * That ordering matters for upgrades. Core loads before add-ons, so if Core
 * registered these hooks unconditionally, both providers would attach to the
 * same actions and every button would render twice on sites that have not yet
 * removed the old plugin.
 *
 * Detection uses wpjobportal::$_active_addons, which Core builds from the active
 * plugin list at construction, so it is already populated by the time this runs
 * and does not depend on the add-on's own class being loaded yet.
 */
final class WPJOBPORTAL_Absorbed_Features {

    /**
     * Absorbed feature => the add-on slug that still owns it when installed.
     *
     * The slug is what appears in wpjobportal::$_active_addons, which is the
     * plugin folder name minus the "wp-job-portal-" prefix.
     */
    private static $features = array(
        'allcompanies' => 'allcompanies',
        'copyjob' => 'copyjob',
        'shortlist' => 'shortlist',
        'socialshare' => 'socialshare',
        'tag' => 'tag',
        'rssfeedback' => 'rssfeedback',
        'cronjob' => 'cronjob',
        'joblistingenhancer' => 'joblistingenhancer',
        'tellfriend' => 'tellfriend',
        // Tier-split features: Core owns the free half, Pro raises the ceiling.
        'print' => 'print',
        'widgets' => 'widgets',
        'jobalert' => 'jobalert',
    );

    /**
     * Sub-module table names that belong to an absorbed feature.
     *
     * Core resolves a sub-module through its parent add-on folder, which for an
     * absorbed feature no longer exists. These are the ones that would
     * otherwise point at a deleted directory.
     */
    private static $submodule_tables = array(
        'jobalertsetting' => 'jobalert',
        'jobalertcities' => 'jobalert',
    );

    /** @var array feature => registered|addon-active|missing */
    private static $status = array();

    /**
     * Load and register every absorbed feature the site is not already serving
     * from a legacy add-on.
     */
    public static function register() {
        foreach (self::$features as $feature => $slug) {
            // A paused old add-on is loaded but does nothing, so Core serves the
            // feature - except where the two cannot load side by side.
            if (self::addon_is_active($slug) || (WPJOBPORTAL_Superseded_Addons::is_paused($slug) && WPJOBPORTAL_Superseded_Addons::must_deactivate($slug))) {
                self::$status[$feature] = 'addon-active';
                continue;
            }

            $file = WPJOBPORTAL_PLUGIN_PATH . 'includes/features/class-wpjobportal-feature-' . $feature . '.php';
            if (!file_exists($file)) {
                self::$status[$feature] = 'missing';
                continue;
            }

            require_once $file;

            $class = 'WPJOBPORTAL_Feature_' . str_replace('-', '_', $feature);
            if (!class_exists($class) || !is_callable(array($class, 'register'))) {
                self::$status[$feature] = 'missing';
                continue;
            }

            call_user_func(array($class, 'register'));

            // Core gates a lot of this on $_active_addons — 29 checks across
            // these seven features, deciding whether a shortlist button, a tag
            // field or an RSS link renders at all. Those checks were written
            // when the only possible provider was an add-on. Now that Core is
            // the provider the key still has to be present, or the feature
            // loads and then renders nothing.
            if (!in_array($feature, (array) wpjobportal::$_active_addons, true)) {
                wpjobportal::$_active_addons[] = $feature;
            }

            self::$status[$feature] = 'registered';
        }

        // Registering the module key above makes Core resolve these modules
        // through its add-on branch, which looks for wp-job-portal-<slug>/.
        // That folder is exactly what we are replacing, so point the lookups
        // back at Core own module tree.
        add_filter('wpjobportal_includer_plugin_path', array(__CLASS__, 'provider_path'), 10, 4);
        add_filter('wpjobportal_includer_template_path', array(__CLASS__, 'provider_template'), 10, 4);

        add_filter('wpjobportal_absorbed_feature_status', array(__CLASS__, 'get_status'));
    }

    /**
     * Whether a feature is being served by Core right now.
     *
     * Templates and other features can use this to decide whether to render a
     * control, without caring which provider supplies it.
     *
     * @param string $feature Feature key.
     * @return bool
     */
    public static function is_active($feature) {
        $feature = (string) $feature;

        if (isset(self::$status[$feature]) && self::$status[$feature] === 'registered') {
            return true;
        }

        // Still available if the legacy add-on is providing it.
        return isset(self::$features[$feature]) && self::addon_is_active(self::$features[$feature]);
    }

    /**
     * @param array $status Existing status values.
     * @return array
     */
    public static function get_status($status = array()) {
        if (!is_array($status)) {
            $status = array();
        }

        return array_merge($status, self::$status);
    }

    /**
     * @param string $slug Add-on slug as it appears in $_active_addons.
     * @return bool
     */
    private static function addon_is_active($slug) {
        return in_array($slug, (array) wpjobportal::$_active_addons, true);
    }

    /**
     * Resolve an absorbed module inside Core rather than in its old add-on folder.
     *
     * @param string|array $path      Path Core resolved.
     * @param string       $module    Module being loaded.
     * @param string       $type      file|model|controller|table|class.
     * @param string       $file_name Layout name for the file type.
     * @return string|array
     */
    public static function provider_path($path, $module, $type, $file_name) {
        $module = sanitize_key((string) $module);

        // A sub-module table of an absorbed feature lives in Core now.
        if (sanitize_key((string) $type) === 'table' && isset(self::$submodule_tables[$module])) {
            $parent = self::$submodule_tables[$module];
            if (isset(self::$status[$parent]) && self::$status[$parent] === 'registered') {
                $file = WPJOBPORTAL_PLUGIN_PATH . 'includes/tables/' . $module . '.php';
                if (file_exists($file)) {
                    return $file;
                }
            }
        }
        if (!isset(self::$features[$module]) || self::$status[$module] !== 'registered') {
            return $path;
        }

        $type = sanitize_key((string) $type);
        $file_name = sanitize_file_name((string) $file_name);
        $base = WPJOBPORTAL_PLUGIN_PATH . 'modules/' . $module . '/';

        if ($type === 'table') {
            $file = WPJOBPORTAL_PLUGIN_PATH . 'includes/tables/' . $module . '.php';
            return file_exists($file) ? $file : $path;
        }

        if (($type === 'file' && $file_name === '') || $type === 'controller') {
            $file = $base . 'controller.php';
            return file_exists($file) ? $file : $path;
        }

        if ($type === 'model') {
            $file = $base . 'model.php';
            return file_exists($file) ? $file : $path;
        }

        if ($type === 'file' && $file_name !== '') {
            $override = locate_template('wp-job-portal/' . $module . '-' . $file_name . '.php', 0, 1);
            if ($override) {
                return array(
                    'inc_file' => $base . 'tmpl/' . $file_name . '.inc.php',
                    'tmpl_file' => $override,
                );
            }

            $inc = $base . 'tmpl/' . $file_name . '.inc.php';
            if (file_exists($inc)) {
                return array(
                    'inc_file' => $inc,
                    'tmpl_file' => $base . 'tmpl/' . $file_name . '.php',
                );
            }

            $file = $base . 'tmpl/' . $file_name . '.php';
            return file_exists($file) ? $file : $path;
        }

        return $path;
    }

    /**
     * Resolve an absorbed view partial inside Core.
     *
     * @param string $path          Path Core resolved.
     * @param string $module        Module or module_name in play.
     * @param string $template_name Template path below tmpl/views.
     * @param array  $args          Template arguments.
     * @return string
     */
    public static function provider_template($path, $module, $template_name, $args = array()) {
        $module = sanitize_key((string) $module);
        if (!isset(self::$features[$module]) || self::$status[$module] !== 'registered') {
            return $path;
        }

        $template_name = wpjobportalphplib::wpJP_clean_file_path((string) $template_name);
        if ($template_name === '') {
            return $path;
        }

        $candidate = WPJOBPORTAL_PLUGIN_PATH . 'modules/' . $module . '/tmpl/views/' . $template_name . '.php';

        return file_exists($candidate) ? $candidate : $path;
    }
}
