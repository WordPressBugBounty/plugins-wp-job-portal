<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Keep a superseded add-on's data alive after it is deleted.
 *
 * Deactivating an old add-on used to switch its settings off; that is handled
 * by WPJOBPORTAL_Absorbed_Config_Guard. Deleting one is the same mistake with
 * no way back. Every add-on ships an uninstall.php that ends with an
 * unconditional drop:
 *
 *     DROP TABLE IF EXISTS {$wpdb->prefix}wj_portal_coverletters
 *
 * That was right when deleting the add-on meant the feature was leaving. Core
 * and the suites took those features over and read those same tables, so the
 * obvious tidy-up after upgrading — deactivate the old add-ons, then delete
 * them — destroys the data the new code was just taught to read. Deleting the
 * Credits add-on drops wj_portal_invoices and wj_portal_transactionlog, which
 * Core reads and which are a site's billing history.
 *
 * The fix cannot live in the add-on. The copy on a customer's site is whatever
 * version they bought, and its uninstall.php cannot be changed retroactively.
 * So Core watches the database instead. WordPress runs uninstall.php inside a
 * normal request with our plugins loaded, and wpdb passes every statement
 * through the 'query' filter on its way to MySQL, so a drop can be read and
 * declined before it reaches the server.
 *
 * What gets declined is decided by one rule: a table is kept when a product
 * other than the one being deleted still reads it. That is what makes this safe
 * to leave switched on. Deleting the Recruit Suite still drops its own pipeline
 * tables, because nothing else reads them; it keeps wj_portal_company_members,
 * because the Marketplace Suite does. The four AI tables are dropped as before —
 * the Matching Suite reimplemented that work and reads none of them, so
 * guarding them would only leave litter behind.
 *
 * @see WPJOBPORTAL_Absorbed_Config_Guard for the same hazard on deactivation.
 */
class WPJOBPORTAL_Uninstall_Guard {

    /** @var string Provider key of the product being uninstalled, if it is one. */
    private static $uninstalling = '';

    /** @var array Tables kept during this request. */
    private static $kept = array();

    public static function register() {
        // Fires immediately before uninstall.php is included, and tells us which
        // plugin is going.
        add_action('pre_uninstall_plugin', array(__CLASS__, 'arm'), 1, 1);
        // No notice afterwards: the data simply stays, which is what the
        // administrator expects, and a message about tables only confused.
    }

    /**
     * Which products read each table.
     *
     * Measured rather than assumed: each entry is the set of shipping products
     * with code that names that table. A table absent from this map is one
     * nothing reads any more, and its drop is left alone.
     *
     * @return array Table name without the wj_portal_ prefix => provider keys.
     */
    public static function readers() {
        $readers = array(
            // Absorbed into Pro.
            'coverletters'        => array('core', 'pro'),
            'departments'         => array('core', 'pro', 'marketplace'),
            'folders'             => array('core', 'pro'),
            'folderresumes'       => array('core', 'pro'),
            'messages'            => array('core', 'pro', 'marketplace'),
            'resumesearches'      => array('core', 'pro'),

            // Absorbed into Core.
            'jobsearches'         => array('core'),
            'jobalertsetting'     => array('core'),
            'jobalertcities'      => array('core'),
            'jobshortlist'        => array('core', 'marketplace'),
            'tags'                => array('core'),

            // Billing. Core reads all of these, so losing them breaks a site
            // that never installed a suite at all.
            'invoices'            => array('core'),
            'packages'            => array('core', 'marketplace'),
            'paymentmethodconfig' => array('core'),
            'subscriptions'       => array('core'),
            'transactionlog'      => array('core', 'marketplace'),
            'userpackages'        => array('core', 'marketplace'),

            // Shared between the two suites that model an organisation.
            'application_stages'  => array('recruit'),
            'company_members'     => array('recruit', 'marketplace'),
            'job_owners'          => array('recruit'),
        );

        /**
         * Filter the tables the uninstall guard protects.
         *
         * Return an empty array to let every drop through.
         *
         * @param array $readers Table => provider keys that read it.
         */
        return (array) apply_filters('wpjobportal_uninstall_protected_tables', $readers);
    }

    /**
     * Start watching, now that we know what is being removed.
     *
     * @param string $plugin Plugin file WordPress is about to uninstall.
     */
    public static function arm($plugin) {
        self::$uninstalling = self::provider_for($plugin);

        // Priority 1: ahead of anything that rewrites queries for other reasons.
        add_filter('query', array(__CLASS__, 'filter_query'), 1);
    }

    /**
     * Decline a drop of a table something else still reads.
     *
     * @param string $query Statement on its way to MySQL.
     * @return string The statement, or a harmless replacement.
     */
    public static function filter_query($query) {
        // Only ever during an uninstall, and only for drops.
        if (!defined('WP_UNINSTALL_PLUGIN') || stripos((string) $query, 'DROP') === false) {
            return $query;
        }

        $tables = self::dropped_tables($query);
        if (empty($tables)) {
            return $query;
        }

        $keep = array();
        $drop = array();
        foreach ($tables as $raw => $name) {
            if (self::is_protected($name)) {
                $keep[] = $name;
                continue;
            }
            $drop[] = $raw;
        }

        if (empty($keep)) {
            return $query;
        }

        self::$kept = array_values(array_unique(array_merge(self::$kept, $keep)));

        if (empty($drop)) {
            // Something harmless that still returns cleanly to the caller.
            return 'SELECT 1';
        }

        // A drop naming several tables, only some of them protected.
        return 'DROP TABLE IF EXISTS ' . implode(', ', $drop);
    }

    /**
     * Tables named by a DROP TABLE statement.
     *
     * @param string $query Statement.
     * @return array Raw identifier => table name without the wj_portal_ prefix.
     */
    public static function dropped_tables($query) {
        global $wpdb;

        if (!preg_match('/^\s*DROP\s+(?:TEMPORARY\s+)?TABLE\s+(?:IF\s+EXISTS\s+)?(.+?)\s*;?\s*$/is', (string) $query, $match)) {
            return array();
        }

        $prefix = $wpdb->prefix . 'wj_portal_';
        $found = array();

        foreach (explode(',', $match[1]) as $piece) {
            $raw = trim($piece);
            $bare = trim($raw, " `\t\n\r");

            // Only ever our own tables, on this site's prefix.
            if (strpos($bare, $prefix) !== 0) {
                continue;
            }

            $found[$raw] = substr($bare, strlen($prefix));
        }

        return $found;
    }

    /**
     * Is this table still read by something other than what is being deleted?
     *
     * @param string $table Table name without the wj_portal_ prefix.
     * @return bool
     */
    public static function is_protected($table) {
        $readers = self::readers();
        if (!isset($readers[$table])) {
            return false;
        }

        foreach ((array) $readers[$table] as $provider) {
            // The product being removed does not get to argue for its own data.
            if ($provider === self::$uninstalling) {
                continue;
            }
            if (self::provider_present($provider)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Is this product installed?
     *
     * @param string $provider Provider key.
     * @return bool
     */
    public static function provider_present($provider) {
        if (class_exists('WPJOBPORTAL_Absorbed_Config_Guard')) {
            return WPJOBPORTAL_Absorbed_Config_Guard::provider_present($provider);
        }

        return $provider === 'core';
    }

    /**
     * The provider key a plugin file belongs to.
     *
     * Superseded add-ons provide nothing under the new model, so they map to an
     * empty string and never exempt themselves from the rule.
     *
     * @param string $plugin Plugin file, as WordPress passes it.
     * @return string
     */
    public static function provider_for($plugin) {
        $plugin = str_replace('\\', '/', (string) $plugin);
        $dir = trim(dirname($plugin), '/.');

        $map = array(
            'wp-job-portal'                  => 'core',
            'wp-job-portal-pro'              => 'pro',
            'wp-job-portal-matchingsuite'    => 'matching',
            'wp-job-portal-recruitsuite'     => 'recruit',
            'wp-job-portal-marketplacesuite' => 'marketplace',
        );

        return isset($map[$dir]) ? $map[$dir] : '';
    }

    /**
     * Tables kept in this request.
     *
     * @return array
     */
    public static function kept() {
        return self::$kept;
    }
}
