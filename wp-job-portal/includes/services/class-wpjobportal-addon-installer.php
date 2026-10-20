<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Installs and updates the products a licence covers, from the Licences screen.
 *
 * A customer who has just bought Pro has a key and nothing else. Before this,
 * the next step was a trip back to the shop to download a ZIP, then Plugins →
 * Add New → Upload, once per product — and most support tickets after a sale
 * were somewhere along that path. Here it is one button per product, or one for
 * everything the licence covers.
 *
 * ---------------------------------------------------------------------------
 * THE CONTRACT
 * ---------------------------------------------------------------------------
 *
 *   POST <licence-base>/install
 *   { "token", "site_url", "product": "pro", "channel": "stable", "core_version": "2.7.1" }
 *
 *   { "ok": true, "protocol": 1, "product": "pro",
 *     "package": "https://.../download?ticket=...", "package_expires": 1750000000,
 *     "version": "1.0.1", "sha256": "<64 hex>", "size": 12345, ... }
 *
 * The token is the one the licence client already holds from activation, so a
 * request here proves the site is activated without sending the key again.
 *
 * ---------------------------------------------------------------------------
 *
 * Every download is checked against the sha256 the server published before
 * WordPress unpacks it, and that check is not only for installs made here: the
 * same filter covers updates run from Dashboard → Updates and the Plugins
 * screen, which until now unpacked whatever the ticketed URL returned. A file
 * that does not match is deleted, not installed, and the customer is told.
 */
class WPJOBPORTAL_Addon_Installer {

    const PROTOCOL = 1;

    const TIMEOUT = 30;

    /** @var object|null Injected transport, for tests. */
    private $transport;

    /** @var array Package URL => expected sha256, for installs started here. */
    private static $expected = array();

    /**
     * @param object|null $transport Anything with post($url, $body): array|null.
     */
    public function __construct($transport = null) {
        $this->transport = $transport;
    }

    public static function register() {
        add_filter('upgrader_pre_download', array(__CLASS__, 'verified_download'), 10, 4);
    }

    /**
     * The products this screen offers, in the order it shows them.
     *
     * The old Address Data and Elegant Design add-ons are not listed: their
     * data and their look now ship inside Pro. An entry marked 'legacy' (via
     * the filter) shows only where it is installed and is never activated.
     *
     * @return array folder => array(label, summary, legacy)
     */
    public static function catalogue() {
        $products = array(
            'wp-job-portal-pro' => array(
                'label' => __('Pro', 'wp-job-portal'),
                'summary' => __('Resume builder, cover letters, messages, PDF and export, reports, resume search and the address database.', 'wp-job-portal'),
                'legacy' => false,
            ),
            'wp-job-portal-marketplacesuite' => array(
                'label' => __('Marketplace Suite', 'wp-job-portal'),
                'summary' => __('Credits and subscriptions, featured jobs, companies and resumes, multiple companies and visitor job posting.', 'wp-job-portal'),
                'legacy' => false,
            ),
            'wp-job-portal-recruitsuite' => array(
                'label' => __('Recruit Suite', 'wp-job-portal'),
                'summary' => __('Hiring teams, pipelines, evaluations, offers, candidate portal, calendar and e-signature.', 'wp-job-portal'),
                'legacy' => false,
            ),
            'wp-job-portal-matchingsuite' => array(
                'label' => __('Matching Suite', 'wp-job-portal'),
                'summary' => __('Relevance-ranked job and resume search, suggestions and match scores, run on your own server.', 'wp-job-portal'),
                'legacy' => false,
            ),
        );

        /**
         * Filter the products offered on the Licences screen.
         *
         * @param array $products folder => array(label, summary, legacy).
         */
        return (array) apply_filters('wpjobportal_addon_catalogue', $products);
    }

    /**
     * Where customers compare plans.
     *
     * @return string
     */
    public static function plans_url() {
        return (string) apply_filters('wpjobportal_plans_url', 'https://wpjobportal.com/pricing/');
    }

    /**
     * Can this site write plugin files without asking for FTP details?
     *
     * A button that ends in a credentials form it cannot show is worse than no
     * button, so the screen asks this first and points elsewhere when not.
     *
     * @return bool
     */
    public static function can_write_plugins() {
        if (!function_exists('get_filesystem_method')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        return get_filesystem_method(array(), WP_PLUGIN_DIR) === 'direct';
    }

    /**
     * Install one product and switch it on.
     *
     * @param string $folder Plugin folder, e.g. wp-job-portal-pro.
     * @return array ok, message, version.
     */
    public function install($folder) {
        $folder = sanitize_key((string) $folder);
        $catalogue = self::catalogue();
        $product = array_search($folder, WPJOBPORTAL_Licence_Client::products(), true);

        if (!isset($catalogue[$folder]) || $product === false) {
            return self::result(false, __('That is not a WP Job Portal product.', 'wp-job-portal'));
        }

        $label = $catalogue[$folder]['label'];

        if (self::installed_file($folder) !== '') {
            return self::result(false, sprintf(
                /* translators: %s: product name. */
                __('%s is already installed.', 'wp-job-portal'), $label));
        }

        $licence = new WPJOBPORTAL_Licence_Client();
        $state = $licence->refresh();

        if (empty($state['ok'])) {
            return self::result(false, __('Activate your licence on this site before installing.', 'wp-job-portal'));
        }

        if (!in_array($folder, (array) $state['plugins'], true)) {
            return self::result(false, sprintf(
                /* translators: %s: product name. */
                __('%s is not part of your licence.', 'wp-job-portal'), $label));
        }

        $reply = $this->ask_for_package($product, $state);

        // A token can lapse between the page loading and the button being
        // pressed. One fresh one, one more try, and no more than that.
        if (is_array($reply) && isset($reply['reason']) && $reply['reason'] === 'invalid_token') {
            $state = $licence->refresh(true);
            $reply = empty($state['ok']) ? $reply : $this->ask_for_package($product, $state);
        }

        $package = self::read_package($reply, $product);

        if (isset($package['error'])) {
            return self::result(false, sprintf(
                /* translators: 1: product name, 2: reason. */
                __('%1$s could not be installed: %2$s', 'wp-job-portal'), $label, $package['error']));
        }

        self::$expected[$package['url']] = $package['sha256'];

        $upgrader = self::upgrader();
        if (is_wp_error($upgrader)) {
            return self::result(false, $upgrader->get_error_message());
        }

        $done = $upgrader->install($package['url']);
        unset(self::$expected[$package['url']]);

        $failure = self::failure($done, $upgrader);
        if ($failure !== '') {
            return self::result(false, sprintf(
                /* translators: 1: product name, 2: reason. */
                __('%1$s could not be installed: %2$s', 'wp-job-portal'), $label, $failure));
        }

        // The archive decides which folder it unpacks into. One that landed
        // anywhere else is not the product this licence named.
        //
        // plugin_info() names the first file in the package that carries a
        // plugin header, which is not always the main file: Marketplace Suite
        // ships its old Credits module at credits/wp-job-portal-credits.php,
        // header and all, and it sorts first. So it only says where the archive
        // landed; the file to activate is looked up in that folder, main file
        // first.
        $landed = (string) $upgrader->plugin_info();
        wp_clean_plugins_cache(false);
        $file = strtok($landed, '/') === $folder ? self::installed_file($folder) : '';
        if ($file === '') {
            return self::result(false, sprintf(
                /* translators: %s: product name. */
                __('%s was downloaded, but the package did not contain the expected plugin. Nothing was activated.', 'wp-job-portal'), $label));
        }

        $activated = activate_plugin($file);

        if (is_wp_error($activated)) {
            return self::result(false, sprintf(
                /* translators: 1: product name, 2: version, 3: reason. */
                __('%1$s %2$s is installed but could not be activated: %3$s', 'wp-job-portal'),
                $label, $package['version'], $activated->get_error_message()), $package['version']);
        }

        return self::result(true, sprintf(
            /* translators: 1: product name, 2: version. */
            __('%1$s %2$s is installed and active.', 'wp-job-portal'), $label, $package['version']), $package['version']);
    }

    /**
     * Update one installed product to what the server currently offers.
     *
     * Runs WordPress's own updater, so maintenance mode, the active state and
     * the upgrader_process_complete hooks behave exactly as on Dashboard →
     * Updates. Only the question "what is the newest version" is asked fresh,
     * because the cached answer may hold a download ticket that has expired.
     *
     * @param string $folder Plugin folder.
     * @return array ok, message, version.
     */
    public function update($folder) {
        $folder = sanitize_key((string) $folder);
        $catalogue = self::catalogue();
        $label = isset($catalogue[$folder]) ? $catalogue[$folder]['label'] : $folder;
        $file = self::installed_file($folder);

        if ($file === '') {
            return self::result(false, sprintf(
                /* translators: %s: product name. */
                __('%s is not installed.', 'wp-job-portal'), $label));
        }

        WPJOBPORTAL_Update_Service::forget();
        $manifest = WPJOBPORTAL_Update_Service::manifest(true);
        $entry = isset($manifest[$folder]) ? $manifest[$folder] : null;

        // An empty answer is a server that did not answer or did not accept
        // this site, not a product with nothing new.
        if (empty($manifest)) {
            return self::result(false, sprintf(
                /* translators: %s: product name. */
                __('Could not check %s for updates: the licence server did not answer, or did not accept this site. Press Check again and retry.', 'wp-job-portal'), $label));
        }

        if ($entry === null || empty($entry['update_available'])) {
            return self::result(false, sprintf(
                /* translators: %s: product name. */
                __('%s is already up to date.', 'wp-job-portal'), $label));
        }

        if ($entry['package'] === '') {
            return self::result(false, sprintf(
                /* translators: 1: product name, 2: version. */
                __('%1$s %2$s is available, but this licence does not include updates. Renew it to download.', 'wp-job-portal'),
                $label, $entry['version']));
        }

        // Put the fresh answer into WordPress's update list, which is where its
        // updater reads the package from. Saving the list runs offer_updates().
        $current = get_site_transient('update_plugins');
        set_site_transient('update_plugins', is_object($current) ? $current : new stdClass());

        $upgrader = self::upgrader();
        if (is_wp_error($upgrader)) {
            return self::result(false, $upgrader->get_error_message());
        }

        $done = $upgrader->upgrade($file);

        $failure = self::failure($done, $upgrader);
        if ($failure !== '') {
            return self::result(false, sprintf(
                /* translators: 1: product name, 2: reason. */
                __('%1$s could not be updated: %2$s', 'wp-job-portal'), $label, $failure));
        }

        return self::result(true, sprintf(
            /* translators: 1: product name, 2: version. */
            __('%1$s is updated to %2$s.', 'wp-job-portal'), $label, $entry['version']), $entry['version']);
    }

    /**
     * Switch on a product that is installed but inactive.
     *
     * @param string $folder Plugin folder.
     * @return array
     */
    public function activate($folder) {
        $folder = sanitize_key((string) $folder);
        $catalogue = self::catalogue();
        $label = isset($catalogue[$folder]) ? $catalogue[$folder]['label'] : $folder;
        $file = isset($catalogue[$folder]) ? self::installed_file($folder) : '';

        if ($file === '') {
            return self::result(false, sprintf(
                /* translators: %s: product name. */
                __('%s is not installed.', 'wp-job-portal'), $label));
        }

        // Retired products are not switched back on from here; see catalogue().
        if (!empty($catalogue[$folder]['legacy'])) {
            return self::result(false, sprintf(
                /* translators: %s: product name. */
                __('%s is no longer needed and is not activated from this screen.', 'wp-job-portal'), $label));
        }

        $activated = activate_plugin($file);

        if (is_wp_error($activated)) {
            return self::result(false, $activated->get_error_message());
        }

        return self::result(true, sprintf(
            /* translators: %s: product name. */
            __('%s is active.', 'wp-job-portal'), $label));
    }

    /**
     * Check a download against its published checksum before WordPress opens it.
     *
     * Hooked to upgrader_pre_download, so it sees every package WordPress is
     * about to fetch. Anything not from the licence endpoint's download route is
     * left alone. Anything that is, is fetched here, hashed, and handed back as a
     * local file only if the hash matches - a file WordPress already knows to
     * delete after unpacking.
     *
     * @param mixed  $reply      False, or what an earlier filter decided.
     * @param string $package    Package URL.
     * @param mixed  $upgrader   The upgrader.
     * @param array  $hook_extra Which plugin, when WordPress knows.
     * @return mixed
     */
    public static function verified_download($reply, $package, $upgrader = null, $hook_extra = array()) {
        if ($reply !== false || !is_string($package) || !self::is_our_package($package)) {
            return $reply;
        }

        $expected = self::expected_hash($package, is_array($hook_extra) ? $hook_extra : array());

        if ($expected === '') {
            return new WP_Error('wpjobportal_unverified', __('This download has no published checksum to verify it against, so it was not installed. Check for updates again and retry.', 'wp-job-portal'));
        }

        if (!function_exists('download_url')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        $file = download_url($package, 300);

        if (is_wp_error($file)) {
            return $file;
        }

        $actual = hash_file('sha256', $file);

        if (!is_string($actual) || !hash_equals($expected, strtolower($actual))) {
            wp_delete_file($file);

            return new WP_Error('wpjobportal_checksum', __('The downloaded file does not match its published checksum, so it was deleted and nothing was installed. Try again; if this repeats, contact support.', 'wp-job-portal'));
        }

        return $file;
    }

    /**
     * Is this a package from the licence endpoint's own download route?
     *
     * @param string $package URL.
     * @return bool
     */
    public static function is_our_package($package) {
        if (!preg_match('#^https?://#i', $package)) {
            return false;
        }

        if (!WPJOBPORTAL_Update_Client::package_is_acceptable($package)) {
            return false;
        }

        $path = (string) wp_parse_url($package, PHP_URL_PATH);
        $query = (string) wp_parse_url($package, PHP_URL_QUERY);
        $base = (string) wp_parse_url(WPJOBPORTAL_Licence_Client::endpoint(), PHP_URL_PATH);

        return $base !== ''
            && strpos($path, rtrim($base, '/') . '/download') === 0
            && strpos($query, 'ticket=') !== false;
    }

    /**
     * The sha256 a package must have.
     *
     * An install started on this screen registered it. An update knows which
     * plugin it is for, and the update manifest carries that release's hash -
     * looked up by product rather than by URL, because the URL holds a ticket
     * that changes every time the manifest is refreshed.
     *
     * @param string $package    URL.
     * @param array  $hook_extra Upgrader context.
     * @return string 64 hex characters, or '' when unknown.
     */
    private static function expected_hash($package, $hook_extra) {
        if (isset(self::$expected[$package])) {
            return self::$expected[$package];
        }

        $manifest = WPJOBPORTAL_Update_Service::manifest();

        if (!empty($hook_extra['plugin'])) {
            $folder = dirname((string) $hook_extra['plugin']);
            if (isset($manifest[$folder]['sha256']) && $manifest[$folder]['sha256'] !== '') {
                return $manifest[$folder]['sha256'];
            }
        }

        foreach ($manifest as $entry) {
            if (isset($entry['package'], $entry['sha256']) && $entry['package'] === $package && $entry['sha256'] !== '') {
                return $entry['sha256'];
            }
        }

        return '';
    }

    /**
     * Ask the server for a package.
     *
     * @param string $product Product slug.
     * @param array  $state   Licence state holding the token.
     * @return array|null
     */
    private function ask_for_package($product, $state) {
        $token = isset($state['token']) ? (string) $state['token'] : '';
        if ($token === '') {
            return null;
        }

        $installed = WPJOBPORTAL_Update_Client::installed();

        $body = array(
            'protocol' => self::PROTOCOL,
            'token' => $token,
            'site_url' => home_url(),
            'product' => $product,
            'channel' => WPJOBPORTAL_Update_Client::channel(),
            'core_version' => isset($installed['wp-job-portal/wp-job-portal.php']) ? $installed['wp-job-portal/wp-job-portal.php'] : '',
        );

        $url = rtrim(WPJOBPORTAL_Licence_Client::endpoint(), '/') . '/install';

        if (!WPJOBPORTAL_Licence_Client::transport_is_acceptable($url)) {
            return null;
        }

        if ($this->transport !== null) {
            return $this->transport->post($url, $body);
        }

        $response = wp_remote_post($url, array(
            'timeout' => self::TIMEOUT,
            'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
            'body' => wp_json_encode($body),
        ));

        if (is_wp_error($response)) {
            return null;
        }

        $decoded = json_decode((string) wp_remote_retrieve_body($response), true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Read an install reply into a package, or the reason there is none.
     *
     * @param mixed  $reply   Decoded reply.
     * @param string $product Product asked for.
     * @return array url, sha256, version - or error.
     */
    public static function read_package($reply, $product) {
        if (!is_array($reply) || !isset($reply['protocol']) || (int) $reply['protocol'] !== self::PROTOCOL) {
            return array('error' => __('the licence server did not answer. Try again shortly.', 'wp-job-portal'));
        }

        if (empty($reply['ok'])) {
            $reason = isset($reply['reason']) ? sanitize_key((string) $reply['reason']) : '';
            $reasons = array(
                'not_entitled' => __('it is not part of your licence.', 'wp-job-portal'),
                'not_published' => __('no release has been published yet.', 'wp-job-portal'),
                'license_expired' => __('your licence has expired. Renew it to install new products.', 'wp-job-portal'),
                'license_suspended' => __('your licence is suspended.', 'wp-job-portal'),
                'license_revoked' => __('your licence has been revoked.', 'wp-job-portal'),
                'invalid_token' => __('this site needs to be activated again. Press Check again, then retry.', 'wp-job-portal'),
                'rate_limited' => __('too many requests. Wait a minute and try again.', 'wp-job-portal'),
            );

            if ($reason === 'requires_core' && !empty($reply['requires_core'])) {
                return array('error' => sprintf(
                    /* translators: %s: version number. */
                    __('it needs WP Job Portal %s or later. Update WP Job Portal first.', 'wp-job-portal'),
                    sanitize_text_field((string) $reply['requires_core'])));
            }

            if (isset($reasons[$reason])) {
                return array('error' => $reasons[$reason]);
            }

            return array('error' => isset($reply['message']) && $reply['message'] !== ''
                ? sanitize_text_field((string) $reply['message'])
                : __('the licence server refused the request.', 'wp-job-portal'));
        }

        if (!isset($reply['product']) || (string) $reply['product'] !== $product) {
            return array('error' => __('the licence server answered for a different product.', 'wp-job-portal'));
        }

        $url = isset($reply['package']) ? (string) $reply['package'] : '';
        $sha256 = isset($reply['sha256']) ? strtolower(trim((string) $reply['sha256'])) : '';

        if ($url === '' || !self::is_our_package($url)) {
            return array('error' => __('the download address is not one this site trusts.', 'wp-job-portal'));
        }

        if (!preg_match('/^[a-f0-9]{64}$/', $sha256)) {
            return array('error' => __('the release has no checksum to verify it against.', 'wp-job-portal'));
        }

        return array(
            'url' => $url,
            'sha256' => $sha256,
            'version' => isset($reply['version']) ? sanitize_text_field((string) $reply['version']) : '',
        );
    }

    /**
     * The installed plugin file for a folder, or ''.
     *
     * @param string $folder Plugin folder.
     * @return string
     */
    public static function installed_file($folder) {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugins = (array) get_plugins();
        $main = $folder . '/' . $folder . '.php';

        if (isset($plugins[$main])) {
            return $main;
        }

        foreach (array_keys($plugins) as $file) {
            if (dirname($file) === $folder) {
                return $file;
            }
        }

        return '';
    }

    /**
     * A quiet upgrader, or the reason this site cannot use one.
     *
     * @return Plugin_Upgrader|WP_Error
     */
    private static function upgrader() {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/misc.php';
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        if (!self::can_write_plugins()) {
            return new WP_Error('wpjobportal_filesystem', __('WordPress needs FTP details to write plugin files on this server. Download the product from your account and upload it under Plugins → Add New instead.', 'wp-job-portal'));
        }

        return new Plugin_Upgrader(new WP_Ajax_Upgrader_Skin());
    }

    /**
     * Why an upgrader run failed, or '' when it did not.
     *
     * @param mixed            $done     What install() or upgrade() returned.
     * @param Plugin_Upgrader  $upgrader The upgrader.
     * @return string
     */
    private static function failure($done, $upgrader) {
        if (is_wp_error($done)) {
            return $done->get_error_message();
        }

        $errors = $upgrader->skin->get_errors();
        if (is_wp_error($errors) && $errors->has_errors()) {
            return $errors->get_error_message();
        }

        if ($done !== true) {
            return __('WordPress did not finish the installation.', 'wp-job-portal');
        }

        return '';
    }

    /**
     * @param bool   $ok      Success.
     * @param string $message What to tell the administrator.
     * @param string $version Version involved.
     * @return array
     */
    private static function result($ok, $message, $version = '') {
        return array('ok' => (bool) $ok, 'message' => $message, 'version' => $version);
    }
}
