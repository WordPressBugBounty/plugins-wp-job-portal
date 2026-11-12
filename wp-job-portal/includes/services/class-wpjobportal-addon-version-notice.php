<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Fix #44: tell the administrator when an add-on is older than this base needs.
 *
 * Sites often update the base first (wordpress.org auto-update) and the add-ons
 * later, by hand. Until they do, base and add-ons are out of step, and nothing
 * said so: the Plugins page and the dashboard showed the old add-ons as active
 * and fine. This notice names each add-on that is behind and the version it
 * needs, on the Plugins page and on the plugin's own screens.
 */
class WPJOBPORTAL_Addon_Version_Notice {

    /**
     * Minimum add-on versions for this base (2.8.0). Raise them when the base
     * starts to rely on something only a newer add-on has.
     *
     * @return array plugin file => array(label, minimum version)
     */
    public static function minimums() {
        return array(
            'wp-job-portal-pro/wp-job-portal-pro.php' => array('WP Job Portal Pro', '1.1.0'),
            'wp-job-portal-recruitsuite/wp-job-portal-recruitsuite.php' => array('WP Job Portal Recruit Suite', '1.1.0'),
            'wp-job-portal-marketplacesuite/wp-job-portal-marketplacesuite.php' => array('WP Job Portal Marketplace Suite', '1.1.0'),
            'wp-job-portal-matchingsuite/wp-job-portal-matchingsuite.php' => array('WP Job Portal Matching Suite', '1.1.0'),
        );
    }

    /** The base plugin's own version, from its header. */
    private static function base_version() {
        $data = get_file_data(dirname(__DIR__, 2) . '/wp-job-portal.php', array('Version' => 'Version'));
        return !empty($data['Version']) ? $data['Version'] : '2.8.1';
    }

    public static function register() {
        add_action('admin_notices', array(__CLASS__, 'render'));
    }

    /**
     * Active add-ons below their minimum.
     *
     * @return array list of array(label, installed version, minimum version)
     */
    public static function outdated() {
        $active = (array) get_option('active_plugins', array());
        $behind = array();
        foreach (self::minimums() as $file => $info) {
            if (!in_array($file, $active, true)) {
                continue;
            }
            $path = WP_PLUGIN_DIR . '/' . $file;
            if (!is_readable($path)) {
                continue;
            }
            $data = get_file_data($path, array('Version' => 'Version'));
            $version = isset($data['Version']) ? trim((string) $data['Version']) : '';
            if ($version !== '' && version_compare($version, $info[1], '<')) {
                $behind[] = array($info[0], $version, $info[1]);
            }
        }
        return $behind;
    }

    /**
     * @return void
     */
    public static function render() {
        if (!current_user_can('activate_plugins')) {
            return;
        }
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        $page = sanitize_key((string) WPJOBPORTALrequest::getVar('page', 'get', ''));
        $on_plugins = $screen && in_array($screen->id, array('plugins', 'plugins-network', 'dashboard'), true);
        if (!$on_plugins && strpos($page, 'wpjobportal') !== 0) {
            return;
        }
        $behind = self::outdated();
        if (empty($behind)) {
            return;
        }
        ?>
        <div class="notice notice-error wpjobportal-addon-version-notice">
            <p><strong><?php echo esc_html__('Please update your WP Job Portal add-ons.', 'wp-job-portal'); ?></strong>
            <?php
            echo esc_html(sprintf(
                /* translators: %s: version of the base plugin */
                __('WP Job Portal %s needs newer versions of these add-ons. Until they are updated, some of their features may not work.', 'wp-job-portal'),
                self::base_version()
            ));
            ?></p>
            <ul style="list-style:disc;margin-left:20px">
                <?php foreach ($behind as $row) { ?>
                    <li><?php echo esc_html(sprintf(
                        /* translators: 1: add-on name, 2: installed version, 3: version needed */
                        __('%1$s: installed %2$s, needs %3$s or later', 'wp-job-portal'),
                        $row[0], $row[1], $row[2]
                    )); ?></li>
                <?php } ?>
            </ul>
        </div>
        <?php
    }
}
