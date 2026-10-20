<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Whether people may create employer and job seeker accounts.
 *
 * Until 2.7.0 the registration page followed WordPress's own "Anyone can
 * register" setting, which is off on every new WordPress site and lives on a
 * screen the setup wizard never mentions. So a job board fresh out of the
 * wizard - a marketplace "where companies register" - answered every visitor
 * with "Registration is disabled by admin".
 *
 * WP Job Portal now has its own setting, as WooCommerce does for customer
 * accounts, and the wizard asks the administrator for it. The plugin never
 * changes WordPress's setting and never creates an account itself: the
 * administrator decides whether sign-up is open, and a person who registers
 * creates their own account.
 *
 * `account_signup` holds one of three values:
 *
 *   wordpress  follow WordPress's "Anyone can register" (every existing site,
 *              so updating changes nothing until someone chooses otherwise)
 *   1          open, whatever WordPress's setting says
 *   0          closed
 */
class WPJOBPORTAL_Registration_Policy {

    const CONFIG = 'account_signup';
    const FOLLOW_WORDPRESS = 'wordpress';

    /**
     * May a visitor create an account through WP Job Portal?
     *
     * @return bool
     */
    public static function is_open() {
        $setting = self::setting();

        if ($setting === '1') {
            return true;
        }
        if ($setting === '0') {
            return false;
        }

        return (bool) get_option('users_can_register');
    }

    /**
     * The stored choice, or "follow WordPress" when there is none.
     *
     * A site that has not run the 2.7.0 migration has no row, which reads the
     * same as one that chose to follow WordPress.
     *
     * @return string wordpress, 1 or 0.
     */
    public static function setting() {
        $value = null;
        if (isset(wpjobportal::$_config) && is_object(wpjobportal::$_config)) {
            $value = wpjobportal::$_config->getConfigurationByConfigName(self::CONFIG);
        }

        // A site that was already on a 2.7.0 build when the setting arrived
        // never ran the migration that adds its row, and the settings screen
        // and the wizard can only update rows that exist - the choice would be
        // shown and then silently not saved. The same statement as 270.sql,
        // once, on the first admin screen that asks.
        if ($value === null && is_admin()) {
            self::ensure_row();
        }

        $value = (string) $value;

        return in_array($value, array('1', '0'), true) ? $value : self::FOLLOW_WORDPRESS;
    }

    /**
     * Add the setting's row, at "follow WordPress", where it is missing.
     *
     * @return void
     */
    private static function ensure_row() {
        global $wpdb;

        $table = $wpdb->prefix . 'wj_portal_config';
        wpjobportaldb_direct::query($wpdb->prepare(
            "INSERT INTO %i (`configname`, `configvalue`, `configfor`, `addon`)
             SELECT %s, %s, 'register', NULL FROM DUAL
             WHERE NOT EXISTS (SELECT 1 FROM %i WHERE `configname` = %s)",
            $table,
            self::CONFIG,
            self::FOLLOW_WORDPRESS,
            $table,
            self::CONFIG
        ));
    }

    /**
     * The choices, for the settings screen and the wizard.
     *
     * @return array
     */
    public static function options() {
        return array(
            (object) array('id' => '1', 'text' => __('Open - anyone can create an account', 'wp-job-portal')),
            (object) array('id' => '0', 'text' => __('Closed - only accounts you add', 'wp-job-portal')),
            (object) array('id' => self::FOLLOW_WORDPRESS, 'text' => __('Follow WordPress "Anyone can register"', 'wp-job-portal')),
        );
    }
}
