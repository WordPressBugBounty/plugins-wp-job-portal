<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * The one place Core talks to the licence and update endpoint.
 *
 * What this replaces made a separate request per add-on and spoke ten different
 * request types — plugininformation, activate, deactivate, delete, generatetoken,
 * getlatestversions, pluginupdatecheck, and three that fetched SQL to run. A site
 * with a dozen add-ons made a dozen calls to answer one question, and the server
 * could hand back SQL for the client to execute, which is not a thing a licence
 * server should ever be able to do.
 *
 * This asks once, for everything, and the answer is data.
 *
 * ---------------------------------------------------------------------------
 * THE CONTRACT
 * ---------------------------------------------------------------------------
 *
 * One endpoint, in the same REST namespace as activation. POST, JSON in, JSON
 * out, versioned by `protocol` so client and server can move independently.
 *
 * Request:
 *
 *     POST <licence-base>/update-check
 *     {
 *       "protocol": 1,
 *       "token": "from a recent activation, when there is one",
 *       "license_key": "the customer's key, when there is one",
 *       "site_url": "https://example.com",
 *       "channel": "stable",
 *       "products": { "pro": "1.0.0", "core": "2.7.0" }
 *     }
 *
 * Products are named by the slug the server sells, not by plugin folder; the
 * translation lives in products_map(). Either credential is accepted, and both
 * are sent when both are held, so an expired token falls back to the key. A
 * request with neither is answered for the free products alone.
 *
 * Response:
 *
 *     {
 *       "ok": true,
 *       "protocol": 1,
 *       "license": { "status": "active", "expires_at": "...",
 *                    "updates": true, "channel": "stable" },
 *       "updates": { "pro": { "installed": "1.0.0", "version": "1.1.0",
 *                             "update_available": true, "sha256": "...",
 *                             "size": 12345, "package": "https://.../download?ticket=...",
 *                             "package_expires": 1750000000,
 *                             "requires_wp": "6.0", "requires_php": "7.4",
 *                             "tested_wp": "7.1", "changelog": "<p>…</p>" } },
 *       "unentitled": ["smart-matching"],
 *       "unknown": ["recruit"]
 *     }
 *
 * Rules the client depends on:
 *
 * - `updates` arrives as [] when empty and as an object when not, because that
 *   is how PHP serialises an array. Both are answers; a missing key is not.
 * - A product this licence does not cover is listed in `unentitled`, and one
 *   with no published release in `unknown`. Neither carries a version.
 * - `package` is a short-lived ticketed URL on the endpoint's own host, and is
 *   absent unless `licence.updates` is true and something newer exists.
 * - Every package carries a `sha256`. One without it is refused here, because
 *   an unverifiable download of executable code is worse than no update.
 * - The response never contains SQL, PHP, or anything else meant to be
 *   executed. This client will not run it.
 *
 * ---------------------------------------------------------------------------
 *
 * Everything here fails safe. A server that is down, slow, wrong, or lying
 * results in no updates being offered and no product being marked unlicensed —
 * because the alternative is a network blip disabling a customer's paid feature
 * or, worse, offering them a download that is not theirs.
 */
class WPJOBPORTAL_Update_Client {

    /** The contract version this client speaks. */
    const API = 1;

    /** How long a good answer is reused. */
    const CACHE_TTL = 43200; // 12 hours

    const CACHE_KEY = 'wpjobportal_update_manifest';

    /** Where the licence key entered on the Licences screen is kept. */
    const KEY_OPTION = 'wpjobportal_license_key';

    /** Nothing is worth blocking an admin page load for longer than this. */
    const TIMEOUT = 15;

    /** @var object|null Injected transport, for tests. */
    private $transport;

    /**
     * @param object|null $transport Anything with post($url, $body): array.
     */
    public function __construct($transport = null) {
        $this->transport = $transport;
    }

    /**
     * Where the endpoint lives.
     *
     * @return string
     */
    public static function endpoint() {
        if (defined('WPJOBPORTAL_UPDATE_ENDPOINT')) {
            $endpoint = WPJOBPORTAL_UPDATE_ENDPOINT;
        } else {
            // Updates live in the same REST namespace as activation, so the
            // licence endpoint decides where they are asked for. Pointing a
            // site at a staging server is then one setting rather than two
            // that can disagree - and two that disagree means a site activated
            // against one server taking packages from another.
            $base = class_exists('WPJOBPORTAL_Licence_Client')
                ? WPJOBPORTAL_Licence_Client::endpoint()
                : 'https://wpjobportal.com/wp-json/wpjp-licence/v1/';

            $endpoint = rtrim((string) $base, '/') . '/update-check';
        }

        /**
         * Filter the update endpoint.
         *
         * @param string $endpoint Absolute URL.
         */
        return (string) apply_filters('wpjobportal_update_endpoint', $endpoint);
    }

    /**
     * Product slugs the endpoint may be asked about without a licence.
     *
     * None by default. The free plugin is published on WordPress.org and
     * updates from there like any other plugin, so a site running Core alone
     * has nothing to ask this endpoint. Asking anyway was worse than useless:
     * the licence server answers a key-less request with "not found" and
     * counts it as a failed attempt, so a busy host of Core-only sites would
     * be rate-limited for everyone behind it.
     *
     * The filter remains for a self-hosted setup that serves Core itself.
     *
     * @return array Product slugs.
     */
    public static function free_products() {
        /**
         * Filter which products may be asked about without a licence.
         *
         * @param array $slugs Product slugs.
         */
        return (array) apply_filters('wpjobportal_update_free_products', array());
    }

    /**
     * Stable releases, or the channel the licence is set to.
     *
     * The server has the final say - it clamps the channel to what the licence
     * allows - so this only expresses a preference.
     *
     * @return string
     */
    public static function channel() {
        $channel = 'stable';

        if (class_exists('WPJOBPORTAL_Licence_Client')) {
            $state = WPJOBPORTAL_Licence_Client::state();
            if (!empty($state['channel'])) { $channel = (string) $state['channel']; }
        }

        /**
         * Filter the release channel asked for.
         *
         * @param string $channel Either 'stable' or 'beta'.
         */
        $channel = (string) apply_filters('wpjobportal_update_channel', $channel);

        return in_array($channel, array('stable', 'beta'), true) ? $channel : 'stable';
    }

    /**
     * Is this URL one we are willing to talk to?
     *
     * HTTPS anywhere; plain HTTP only to a host that is plainly a development
     * machine. Delegated to the licence client so that the endpoint and the
     * packages it names are judged by one rule rather than two that can drift.
     *
     * @param string $url URL to judge.
     * @return bool
     */
    public static function transport_is_acceptable($url) {
        if (class_exists('WPJOBPORTAL_Licence_Client')) {
            return WPJOBPORTAL_Licence_Client::transport_is_acceptable($url);
        }

        return strpos((string) $url, 'https://') === 0;
    }

    /**
     * Which product each plugin folder is licensed as.
     *
     * The server sells products, not directories: one licence covers "recruit"
     * however the folder holding it happens to be spelled. Anything not on this
     * map cannot be asked about, and asking anyway only spends a place in the
     * reply's "unentitled" list to learn nothing.
     *
     * Core is not on it: the free plugin updates from WordPress.org, and
     * offering it from here as well would give WordPress two sources for the
     * same plugin. A self-hosted setup can add it back through the filter.
     *
     * @return array plugin folder => product slug.
     */
    public static function products_map() {
        $map = array(
            'wp-job-portal-pro' => 'pro',
            'wp-job-portal-marketplacesuite' => 'marketplace',
            'wp-job-portal-recruitsuite' => 'recruit',
            'wp-job-portal-matchingsuite' => 'smart-matching',
            'wp-job-portal-addressdata' => 'address-data',
            'wp-job-portal-elegantdesign' => 'elegant-design',
        );

        /**
         * Filter the plugin folder to product slug map.
         *
         * @param array $map Folder => product slug.
         */
        return (array) apply_filters('wpjobportal_update_products_map', $map);
    }

    /**
     * What this site is running, keyed by plugin file.
     *
     * Read from WordPress rather than from constants each product defines, so a
     * product cannot report a version it is not actually running.
     *
     * @return array plugin file => version.
     */
    public static function installed() {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $ours = array();
        foreach ((array) get_plugins() as $file => $data) {
            if (strpos($file, 'wp-job-portal') !== 0) {
                continue;
            }
            $ours[$file] = isset($data['Version']) ? (string) $data['Version'] : '';
        }

        /**
         * Filter which plugins are reported to the update endpoint.
         *
         * @param array $ours plugin file => version.
         */
        return (array) apply_filters('wpjobportal_update_installed', $ours);
    }

    /**
     * The licence key to ask with.
     *
     * One key covers whatever the customer's plan includes; which products that
     * is, is the server's answer, not a decision made here.
     *
     * @return string
     */
    public static function key() {
        // The key entered on the Licences screen. One key covers whatever plan
        // the customer bought, so this is the normal case.
        $key = trim((string) get_option(self::KEY_OPTION, ''));

        // Failing that, a key left behind by the per-add-on system. A customer
        // upgrading from that world has one of these and has entered nothing
        // new, and asking them to re-type a key they already gave us would be
        // a poor way to introduce the new screen.
        if ($key === '') {
            foreach (array_keys(self::installed()) as $file) {
                $candidate = trim((string) get_option('transaction_key_for_' . dirname($file), ''));
                if ($candidate !== '') {
                    $key = $candidate;
                    break;
                }
            }
        }

        /**
         * Filter the licence key sent to the endpoint.
         *
         * @param string $key Licence key.
         */
        return (string) apply_filters('wpjobportal_update_key', $key);
    }

    /**
     * Ask the endpoint what is available.
     *
     * @param bool $force Ignore the cached answer.
     * @return array Product slug => details. Empty when anything went wrong.
     */
    public function manifest($force = false) {
        if (!$force) {
            $cached = get_site_transient(self::CACHE_KEY);
            if (is_array($cached)) {
                return $cached;
            }
        }

        $installed = self::installed();
        if (empty($installed)) {
            return array();
        }

        $map = self::products_map();

        $products = array();
        foreach ($installed as $file => $version) {
            $folder = dirname($file);
            if (!isset($map[$folder])) { continue; }

            $products[$map[$folder]] = $version;
        }

        // Only Core, or only unlicensed products. There is nothing the server
        // could answer, so do not spend a request finding that out.
        if (empty($products)) {
            return array();
        }

        $body = array(
            'protocol' => self::API,
            'site_url' => home_url(),
            'channel' => self::channel(),
            'products' => $products,
        );

        // A token, when a recent activation left one, and the key as well. The
        // server accepts either; sending both means an expired token falls back
        // to the key instead of stranding exactly the sites that check least
        // often.
        $token = '';
        if (class_exists('WPJOBPORTAL_Licence_Client')) {
            $state = WPJOBPORTAL_Licence_Client::state();
            $token = isset($state['token']) ? (string) $state['token'] : '';
        }

        if ($token !== '') {
            $body['token'] = $token;
        }

        $key = self::key();
        if ($key !== '') {
            $body['license_key'] = $key;
        }

        // A site with no licence may still ask, so long as it is asking about
        // something free. Anything else needs a credential, and without one
        // there is nothing the endpoint could usefully answer.
        if ($token === '' && $key === '') {
            $free = array_intersect_key($products, array_flip(self::free_products()));

            if (empty($free)) {
                return array();
            }

            $body['products'] = $free;
        }

        $answer = $this->ask($body);

        // Nothing usable came back. Cache the emptiness briefly so a server that
        // is down is not asked again on every admin page load, but not for the
        // full period, so recovery is quick.
        if ($answer === null) {
            set_site_transient(self::CACHE_KEY, array(), 15 * MINUTE_IN_SECONDS);

            return array();
        }

        set_site_transient(self::CACHE_KEY, $answer, self::CACHE_TTL);

        return $answer;
    }

    /**
     * Throw away the cached answer.
     *
     * @return void
     */
    public static function flush() {
        delete_site_transient(self::CACHE_KEY);
    }

    /**
     * Make the call and read the reply.
     *
     * @param array $body Request body.
     * @return array|null Null when the answer cannot be trusted.
     */
    private function ask($body) {
        $endpoint = self::endpoint();

        // A licence key and a site URL are not going over plain HTTP - except
        // to a host that is plainly a development machine, which is the same
        // judgement the licence client makes and is made in one place so the
        // two cannot drift apart.
        if (!self::transport_is_acceptable($endpoint)) {
            return null;
        }

        if ($this->transport !== null) {
            $raw = $this->transport->post($endpoint, $body);
        } else {
            $response = wp_remote_post($endpoint, array(
                'timeout' => self::TIMEOUT,
                'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
                'body' => wp_json_encode($body),
                'sslverify' => true,
            ));

            if (is_wp_error($response) || (int) wp_remote_retrieve_response_code($response) !== 200) {
                return null;
            }

            $raw = json_decode((string) wp_remote_retrieve_body($response), true);
        }

        return $this->read($raw);
    }

    /**
     * Turn a reply into something safe to act on.
     *
     * Everything is checked rather than trusted: this decides whether a
     * customer is offered a download, and the reply arrives over the network.
     *
     * @param mixed $raw Decoded reply.
     * @return array|null
     */
    public function read($raw) {
        if (!is_array($raw) || empty($raw['ok'])) {
            return null;
        }

        // The protocol number is the one thing that says this reply came from
        // something that meant to answer us. A 404 page from a misconfigured
        // host is valid JSON with a message in it, and without this check the
        // absence of an endpoint reads as an answer.
        if (!isset($raw['protocol']) || (int) $raw['protocol'] !== self::API) {
            return null;
        }

        // PHP serialises an empty array as [] and a populated one as an object,
        // so "no updates" and "some updates" arrive as different JSON types.
        // Both are legitimate; only a missing key is not.
        if (!isset($raw['updates']) || !is_array($raw['updates'])) {
            return null;
        }

        // Whether the licence is in a state that may take files at all. The
        // server already withholds packages otherwise, but an expired licence
        // still gets told what exists so its own screen can say so, and that
        // must not be mistaken for an offer.
        // Read in either spelling; see WPJOBPORTAL_Licence_Client::licence_block().
        $licence = class_exists('WPJOBPORTAL_Licence_Client')
            ? WPJOBPORTAL_Licence_Client::licence_block($raw)
            : (isset($raw['license']) && is_array($raw['license']) ? $raw['license'] : null);
        $may_update = isset($licence['updates']) ? !empty($licence['updates']) : false;
        $expires = isset($licence['expires_at']) ? sanitize_text_field((string) $licence['expires_at']) : '';

        // Back from product slug to the folder it is installed in, because
        // everything downstream of here is WordPress, which thinks in folders.
        $folders = array_flip(self::products_map());

        $installed = array();
        foreach (array_keys(self::installed()) as $file) {
            $installed[dirname($file)] = $file;
        }

        $products = array();
        foreach ($raw['updates'] as $slug => $details) {
            $slug = sanitize_key(str_replace('_', '-', (string) $slug));

            if (!is_array($details) || !isset($folders[$slug])) {
                continue;
            }

            $folder = $folders[$slug];

            // Only ever about plugins this site actually has. A reply naming
            // something else is not something to act on.
            if (!isset($installed[$folder])) {
                continue;
            }

            $package = isset($details['package']) ? (string) $details['package'] : '';

            // A package offered while the same reply says this licence may not
            // take updates is a server fault. Drop it rather than follow it.
            if (!$may_update || empty($details['update_available'])) {
                $package = '';
            }

            // Downloads come from where the endpoint does, and are never a path
            // on this filesystem.
            if ($package !== '' && !self::package_is_acceptable($package)) {
                $package = '';
            }

            // A package with no checksum cannot be verified before it is
            // unpacked, and an unverifiable download of executable code is
            // worse than no update at all.
            $sha256 = isset($details['sha256']) ? strtolower(trim((string) $details['sha256'])) : '';
            if (!preg_match('/^[a-f0-9]{64}$/', $sha256)) {
                $sha256 = '';
                $package = '';
            }

            $products[$folder] = array(
                'file' => $installed[$folder],
                'slug' => $slug,
                'licensed' => true,           // listed at all means entitled
                'expires' => $expires,
                'version' => isset($details['version']) ? sanitize_text_field((string) $details['version']) : '',
                'installed' => isset($details['installed']) ? sanitize_text_field((string) $details['installed']) : '',
                'update_available' => !empty($details['update_available']),
                'package' => $package,
                'sha256' => $sha256,
                'size' => isset($details['size']) ? (int) $details['size'] : 0,
                'package_expires' => isset($details['package_expires']) ? (int) $details['package_expires'] : 0,
                'requires' => isset($details['requires_wp']) ? sanitize_text_field((string) $details['requires_wp']) : '',
                'requires_php' => isset($details['requires_php']) ? sanitize_text_field((string) $details['requires_php']) : '',
                'tested' => isset($details['tested_wp']) ? sanitize_text_field((string) $details['tested_wp']) : '',
                'released' => isset($details['released_at']) ? sanitize_text_field((string) $details['released_at']) : '',
                'homepage' => isset($details['homepage']) ? esc_url_raw((string) $details['homepage']) : '',
                'changelog' => isset($details['changelog']) ? wp_kses_post((string) $details['changelog']) : '',
            );
        }

        // Products the server says this licence does not cover, so the admin
        // screen can say so rather than leaving them looking simply quiet.
        foreach (array('unentitled', 'unknown') as $bucket) {
            if (empty($raw[$bucket]) || !is_array($raw[$bucket])) { continue; }

            foreach ($raw[$bucket] as $slug) {
                $slug = sanitize_key(str_replace('_', '-', (string) $slug));
                if (!isset($folders[$slug])) { continue; }

                $folder = $folders[$slug];
                if (!isset($installed[$folder]) || isset($products[$folder])) { continue; }

                // Same shape as an offered product, so that everything reading
                // a manifest can read every entry in it without checking which
                // kind it got.
                $products[$folder] = array(
                    'file' => $installed[$folder],
                    'slug' => $slug,
                    'licensed' => ($bucket === 'unknown'),
                    'expires' => $expires,
                    'version' => '',
                    'installed' => '',
                    'update_available' => false,
                    'package' => '',
                    'sha256' => '',
                    'size' => 0,
                    'package_expires' => 0,
                    'requires' => '',
                    'requires_php' => '',
                    'tested' => '',
                    'released' => '',
                    'homepage' => '',
                    'changelog' => '',
                    'reason' => $bucket,
                );
            }
        }

        return $products;
    }

    /**
     * Is this somewhere we are willing to download a plugin from?
     *
     * @param string $package Package URL.
     * @return bool
     */
    public static function package_is_acceptable($package) {
        if (!self::transport_is_acceptable($package)) {
            return false;
        }

        $host = wp_parse_url($package, PHP_URL_HOST);
        if (!is_string($host) || $host === '') {
            return false;
        }

        $endpoint_host = wp_parse_url(self::endpoint(), PHP_URL_HOST);

        /**
         * Filter the hosts a plugin package may be downloaded from.
         *
         * Defaults to the endpoint's own host, so moving the endpoint moves the
         * downloads with it and nothing else becomes trusted by accident.
         *
         * @param array $hosts Acceptable hostnames.
         */
        $allowed = (array) apply_filters('wpjobportal_update_package_hosts', array_filter(array($endpoint_host)));

        foreach ($allowed as $candidate) {
            $candidate = strtolower((string) $candidate);
            if ($host === $candidate || substr($host, -strlen('.' . $candidate)) === '.' . $candidate) {
                return true;
            }
        }

        return false;
    }
}
