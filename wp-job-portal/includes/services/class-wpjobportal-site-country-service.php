<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Which country is this site actually for?
 *
 * Core ships city data for 186 countries but no city rows, so a new install has
 * an empty city list until an administrator finds Load Address Data and picks a
 * country from a list of two hundred. The list has no default, so it opens on
 * whichever country sorts first alphabetically — a poor guess everywhere.
 *
 * The site already knows the answer well enough to offer one. Three signals,
 * strongest first:
 *
 *   1. WooCommerce's base country, when WooCommerce is installed. A merchant
 *      set that deliberately to say where the business is.
 *   2. The site timezone. Set during WordPress installation and rarely wrong,
 *      and PHP maps a zone to a country without any data of our own.
 *   3. The site locale, but only when it is not the en_US default. Every
 *      untouched install in the world reports en_US, so that value says
 *      nothing about where the site is.
 *
 * Never an IP lookup: it needs a network call, it raises a question about
 * sending anything anywhere, and it returns the hosting company's country
 * rather than the site's market.
 *
 * A guess, and treated as one. It pre-selects a dropdown that the
 * administrator can change before importing anything.
 */
class WPJOBPORTAL_Site_Country_Service {

    /** WordPress installs default to this, so it carries no location signal. */
    const DEFAULT_LOCALE = 'en_US';

    /** @var string|null Resolved once per request. */
    private static $resolved = null;

    /**
     * The site's country, as a code the countries table recognises.
     *
     * @return string Empty when nothing can be determined.
     */
    public static function country_code() {
        if (self::$resolved !== null) {
            return self::$resolved;
        }

        $code = '';
        foreach (array('from_woocommerce', 'from_timezone', 'from_locale') as $source) {
            $candidate = self::$source();
            if ($candidate === '') {
                continue;
            }

            $known = self::known_country($candidate);
            if ($known !== '') {
                $code = $known;
                break;
            }
        }

        /**
         * Override the detected country, for example on a multisite network
         * where every site serves one market.
         *
         * @param string $code Detected country code, or empty.
         */
        $code = (string) apply_filters('wpjobportal_site_country_code', $code);

        self::$resolved = $code;

        return self::$resolved;
    }

    /**
     * The country's display name, for a prompt that names it.
     *
     * @return string Empty when the country is unknown.
     */
    public static function country_name() {
        $code = self::country_code();
        if ($code === '') {
            return '';
        }

        global $wpdb;

        $name = wpjobportaldb_direct::get_var($wpdb->prepare(
            "SELECT COALESCE(NULLIF(internationalname, ''), name)
               FROM `{$wpdb->prefix}wj_portal_countries`
              WHERE namecode = %s LIMIT 1",
            $code
        ));

        return (string) $name;
    }

    /**
     * WooCommerce's base country. Stored as 'DE' or 'DE:BE'.
     *
     * @return string
     */
    private static function from_woocommerce() {
        $base = get_option('woocommerce_default_country', '');
        if (!is_string($base) || $base === '') {
            return '';
        }

        $parts = explode(':', $base);

        return trim($parts[0]);
    }

    /**
     * The site timezone, mapped through PHP's own zone-to-country table.
     *
     * A site set to a raw UTC offset rather than a named zone has no location
     * to read, which is why this is not the only signal.
     *
     * @return string
     */
    private static function from_timezone() {
        $zone = get_option('timezone_string', '');
        if (!is_string($zone) || $zone === '') {
            return '';
        }

        try {
            $location = (new DateTimeZone($zone))->getLocation();
        } catch (Exception $e) {
            return '';
        }

        if (!is_array($location) || empty($location['country_code'])) {
            return '';
        }

        // PHP reports '??' for zones it cannot place, such as UTC.
        $code = $location['country_code'];

        return $code === '??' ? '' : $code;
    }

    /**
     * The site locale, when it has been changed from the default.
     *
     * @return string
     */
    private static function from_locale() {
        $locale = get_locale();
        if (!is_string($locale) || $locale === '' || $locale === self::DEFAULT_LOCALE) {
            return '';
        }

        if (!preg_match('/^[a-z]{2,3}_([A-Z]{2})/', $locale, $m)) {
            return '';
        }

        return $m[1];
    }

    /**
     * Resolve a candidate against the countries the site actually offers.
     *
     * Returns the stored namecode rather than the candidate, so the caller can
     * compare it against the dropdown's option values directly whatever case
     * the table happens to use.
     *
     * @param string $candidate Two-letter code from one of the signals.
     * @return string
     */
    private static function known_country($candidate) {
        $candidate = trim($candidate);
        if ($candidate === '' || !preg_match('/^[A-Za-z]{2}$/', $candidate)) {
            return '';
        }

        global $wpdb;

        $code = wpjobportaldb_direct::get_var($wpdb->prepare(
            "SELECT namecode FROM `{$wpdb->prefix}wj_portal_countries`
              WHERE namecode = %s AND enabled = 1 LIMIT 1",
            $candidate
        ));

        return $code ? (string) $code : '';
    }

    /**
     * Reset the per-request cache. Tests need this; nothing else should.
     *
     * @return void
     */
    public static function flush() {
        self::$resolved = null;
    }
}
