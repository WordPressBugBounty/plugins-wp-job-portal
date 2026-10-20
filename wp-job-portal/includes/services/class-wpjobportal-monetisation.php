<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Can this site charge for things, and is the code that does it actually here?
 *
 * Core asks whether Credits is installed in 114 places. Almost all of them are
 * asking something reasonable — should a price be shown, does this form need a
 * payment step, is there a packages menu — and almost all of them need no
 * change at all, because `$_active_addons` is already the indirection layer:
 * whichever product provides a feature pushes its key, and Core's existing
 * name-based checks light up. Pro already does exactly this for cover letters,
 * departments, folders and messages.
 *
 * Twelve of the 114 are different. They do not merely branch on the answer;
 * they call into Credits' own models:
 *
 *     if (in_array( 'credits', wpjobportal::$_active_addons )) {
 *         WPJOBPORTALincluder::getJSModel('credits')->...
 *
 * and getJSModel() resolves a module to a plugin directory by concatenating its
 * name — wp-job-portal-{module} — then include_once's the file and instantiates
 * the class with no check that either exists. A key whose directory is missing
 * does not degrade; it fatals with "Class WPJOBPORTALcreditsModel not found".
 *
 * That is what stands between the current model and moving Credits into the
 * Marketplace Suite: the moment a provider other than the wp-job-portal-credits
 * directory answers for monetisation, those twelve call sites take the site
 * down. It is also a fault today, on any site where an add-on was half removed
 * and its key outlived its files.
 *
 * So this answers two questions rather than one, and the difference between
 * them is the whole point:
 *
 *   available()   — is this site selling things? What 102 gates mean.
 *   resolvable()  — is the code that does it loadable right now? What the
 *                   twelve that call into it must ask before they do.
 */
class WPJOBPORTAL_Monetisation {

    /** The add-on key monetisation has always been registered under. */
    const KEY = 'credits';

    /** @var array Cache of resolvability per module, per request. */
    private static $resolvable = array();

    /** @var bool|null Whether the credit ledger tables exist, per request. */
    private static $ledger = null;

    /**
     * Is monetisation available on this site?
     *
     * Deliberately the same question the existing gates ask, so the 102 that
     * only branch on it keep working untouched whoever provides it.
     *
     * @return bool
     */
    public static function available() {
        $available = in_array(self::KEY, (array) wpjobportal::$_active_addons, true);

        /**
         * Filter whether this site can charge for things.
         *
         * @param bool $available Whether monetisation is available.
         */
        return (bool) apply_filters('wpjobportal_monetisation_available', $available);
    }

    /**
     * Was this site charging, and is that now on hold?
     *
     * The old Credits add-on keeps charging until the Marketplace Suite is
     * switched on, and is paused only then (WPJOBPORTAL_Superseded_Addons) -
     * when the Suite normally provides credits itself. If it does not (it is
     * switched on but not running), available() is false and every gate would
     * let employers post for free. A site that sold job listings must not give
     * them away, so adding new jobs, companies and resumes closes until that is
     * sorted out. Administrators can still add them.
     *
     * @return bool
     */
    public static function paused() {
        // Once the Marketplace Suite provides Credits, nothing is on hold.
        return class_exists('WPJOBPORTAL_Superseded_Addons')
            && WPJOBPORTAL_Superseded_Addons::is_paused(self::KEY)
            && !self::available();
    }

    /**
     * Is the code behind it actually loadable?
     *
     * Asked before calling into a provider's models, because the includer
     * resolves a module to a directory by name and instantiates without
     * checking. This is the check it does not do.
     *
     * @param string $module Module name; monetisation's own by default.
     * @return bool
     */
    public static function resolvable($module = self::KEY) {
        $module = sanitize_key((string) $module);

        if (isset(self::$resolvable[$module])) {
            return self::$resolvable[$module];
        }

        if (!self::available()) {
            self::$resolvable[$module] = false;

            return false;
        }

        // A provider that has already been loaded needs no lookup.
        if (class_exists('WPJOBPORTAL' . $module . 'Model', false)) {
            self::$resolvable[$module] = true;

            return true;
        }

        $path = WPJOBPORTALincluder::getPluginPath($module, 'model');
        $path = is_array($path) ? '' : (string) $path;

        self::$resolvable[$module] = ($path !== '' && file_exists($path));

        return self::$resolvable[$module];
    }

    /**
     * Both: available, and safe to call into.
     *
     * @param string $module Module name.
     * @return bool
     */
    public static function usable($module = self::KEY) {
        return self::available() && self::resolvable($module);
    }

    /**
     * The model, or null rather than a fatal error.
     *
     * @param string $module Module name.
     * @return object|null
     */
    public static function model($module = self::KEY) {
        if (!self::usable($module)) {
            return null;
        }

        return WPJOBPORTALincluder::getJSModel($module);
    }

    /**
     * Is the credit ledger actually in the database?
     *
     * available() says a provider is installed. resolvable() says its code can
     * be loaded. Neither says its tables exist, and Core reads two of them:
     *
     *     wj_portal_purchasehistory   what was bought
     *     wj_portal_credits_log       what has been spent
     *
     * Nothing creates either one. Not Core's activation, not any migration,
     * and not any add-on: the archived original distribution - Core plus all
     * 41 add-ons, each with the includes/addon-sql.php the licence server used
     * to hand out on activation - mentions these two names in exactly one
     * file, the model that reads them. Credits creates invoices, packages,
     * userpackages, subscriptions, transactionlog and paymentmethodconfig, and
     * its purchasehistory/ directory is named after the page, not a table.
     *
     * So the balance lookup has queried tables that have never existed in any
     * version of the product. It survived only because its one caller was
     * itself unreachable; anything that did reach it got two SELECTs against
     * nothing and a database error on the page.
     *
     * The check stays a check rather than a hard-coded false because the
     * question it asks is the honest one - is there a ledger to read - and a
     * site that grew these tables by hand, or a future provider that ships
     * them, is answered correctly without touching this again.
     *
     * @return bool
     */
    public static function ledger_ready() {
        if (self::$ledger !== null) {
            return self::$ledger;
        }

        if (!self::available()) {
            self::$ledger = false;

            return false;
        }

        $db = wpjobportal::$_db;
        self::$ledger = true;

        foreach (array('wj_portal_purchasehistory', 'wj_portal_credits_log') as $table) {
            // The names contain underscores, which LIKE reads as wildcards.
            $name = $db->prefix . $table;
            $found = $db->get_var($db->prepare('SHOW TABLES LIKE %s', $db->esc_like($name)));

            if ($found !== $name) {
                self::$ledger = false;
                break;
            }
        }

        return self::$ledger;
    }

    /**
     * Forget what was resolvable, for tests that change the provider.
     *
     * @return void
     */
    public static function flush() {
        self::$resolvable = array();
        self::$ledger = null;
    }
}
