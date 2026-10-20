<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Talks to the licence server: activate, validate, deactivate.
 *
 * ---------------------------------------------------------------------------
 * THE CONTRACT, as the server actually implements it
 * ---------------------------------------------------------------------------
 *
 * Three endpoints under one base, all POST with a JSON body. The base is set
 * with WPJOBPORTAL_LICENCE_ENDPOINT or the wpjobportal_licence_endpoint filter.
 *
 *   POST activate    { license_key, site_url }
 *   POST validate    { token, site_url }
 *   POST deactivate  { license_key, site_url }
 *
 * A successful activate replies:
 *
 *   {
 *     "ok": true, "reason": "ok", "protocol": 1,
 *     "license": { "status": "active", "expires_at": "2027-07-08 13:52:54", "updates": true, "plan": "Pro" },
 *     "products": ["pro", "marketplace", "recruit", "smart-matching", "address-data"],
 *     "token": "v1.<payload>.<signature>",
 *     "token_ttl": 3600,
 *     "activation": { "kind": "development" },
 *     "slots": { "production": {"used":2,"allowed":25}, ... }
 *   }
 *
 * Two things about that shape drive the design here.
 *
 * The token is short-lived — an hour — and it is what validate takes, while
 * activate takes the licence key. So the key is the durable credential and the
 * token is a working pass that has to be renewed. This class keeps both, uses
 * validate while the token holds, and silently re-activates when it does not.
 * A customer should never see "your licence expired" because a one-hour token
 * ran out overnight.
 *
 * Product names on the wire are short — "pro", "marketplace" — and are not
 * plugin folders. The translation lives here, in one table, rather than being
 * guessed at each call site.
 *
 * The token is signed and carries its own claims, but nothing here trusts what
 * it decodes out of it beyond the expiry: entitlement comes from what the
 * server said in the reply body, not from a payload this client could not
 * verify the signature of.
 *
 * The server spells it "license", in field names and in the reply, and this
 * client spelled it "licence" until 2.7.1. A request carrying licence_key was
 * refused as missing a parameter, so no 2.7.0 site could activate. Requests
 * now use the server's spelling; replies are read in either, so a server that
 * answers the old way is not broken by the fix.
 */
class WPJOBPORTAL_Licence_Client {

    /** The protocol version this client speaks. */
    const PROTOCOL = 1;

    /** Where the key, token and last answer are kept. */
    const KEY_OPTION = 'wpjobportal_license_key';
    const STATE_OPTION = 'wpjobportal_licence_state';

    /** Re-validate a still-good token no more often than this. */
    const REVALIDATE_EVERY = 43200; // 12 hours

    /** Renew a token this long before it actually expires. */
    const RENEW_MARGIN = 300; // 5 minutes

    /** How long to leave a failed activation alone before trying it again. */
    const RETRY_AFTER = 900; // 15 minutes

    const TIMEOUT = 20;

    /** The daily job that keeps the stored state current. */
    const CRON_HOOK = 'wpjobportal_licence_check';

    /** @var object|null Injected transport, for tests. */
    private $transport;

    /**
     * @param object|null $transport Anything with post($url, $body): array|null.
     */
    public function __construct($transport = null) {
        $this->transport = $transport;
    }

    /**
     * What the server calls each product, and what it is here.
     *
     * @return array server slug => plugin folder.
     */
    public static function products() {
        $map = array(
            'pro' => 'wp-job-portal-pro',
            'marketplace' => 'wp-job-portal-marketplacesuite',
            'recruit' => 'wp-job-portal-recruitsuite',
            'smart-matching' => 'wp-job-portal-matchingsuite',
            'address-data' => 'wp-job-portal-addressdata',
            'elegant-design' => 'wp-job-portal-elegantdesign',
        );

        /**
         * Filter the map between server product names and plugin folders.
         *
         * @param array $map server slug => plugin folder.
         */
        return (array) apply_filters('wpjobportal_licence_products', $map);
    }

    /**
     * The base URL the three endpoints hang off.
     *
     * @return string
     */
    public static function endpoint() {
        $endpoint = defined('WPJOBPORTAL_LICENCE_ENDPOINT')
            ? WPJOBPORTAL_LICENCE_ENDPOINT
            : 'https://wpjobportal.com/wp-json/wpjp-licence/v1/';

        /**
         * Filter the licence endpoint base.
         *
         * @param string $endpoint Absolute URL, with a trailing slash.
         */
        $endpoint = (string) apply_filters('wpjobportal_licence_endpoint', $endpoint);

        return rtrim($endpoint, '/') . '/';
    }

    /**
     * A licence key defined in wp-config.php, if there is one.
     *
     * Both spellings are accepted. A customer who has to look up which one we
     * chose has already been failed by the documentation, and supporting the
     * other costs one line.
     *
     * @return string
     */
    public static function key_from_constant() {
        foreach (array('WPJOBPORTAL_LICENCE_KEY', 'WPJOBPORTAL_LICENSE_KEY') as $name) {
            if (defined($name)) {
                $value = trim((string) constant($name));
                if ($value !== '') { return $value; }
            }
        }

        return '';
    }

    /**
     * Is the key fixed in wp-config.php rather than stored here?
     *
     * When it is, the form must not offer to change it: saving would write a
     * value to the database that the constant then silently overrides, and the
     * administrator would be left looking at a screen that says one thing while
     * the site does another.
     *
     * @return bool
     */
    public static function key_is_locked() {
        return self::key_from_constant() !== '';
    }

    /**
     * The key with everything but its last few characters hidden.
     *
     * Enough to tell one licence from another, not enough to use. The screen
     * used to print the whole key into a form field on every view, which put it
     * in support-ticket screenshots and the browser's form store for no benefit
     * — nobody reads their licence key off the screen, they paste it in once.
     *
     * @param string $key Key to mask; the stored one by default.
     * @return string
     */
    public static function mask($key = null) {
        $key = ($key === null) ? self::key() : trim((string) $key);
        $length = strlen($key);

        if ($length === 0) { return ''; }

        $visible = 4;
        if ($length <= $visible) { return str_repeat('*', $length); }

        $masked = '';
        for ($i = 0; $i < $length - $visible; $i++) {
            // Separators stay, so the shape of the key still reads as a key.
            $masked .= ($key[$i] === '-' || $key[$i] === ' ') ? $key[$i] : '*';
        }

        return $masked . substr($key, -$visible);
    }

    /**
     * The licence key this site holds.
     *
     * @return string
     */
    public static function key() {
        // wp-config.php wins. It is the only place a key can be set that
        // survives a database restore from another environment, which is
        // exactly why an agency puts it there.
        $key = self::key_from_constant();

        if ($key === '') {
            $key = trim((string) get_option(self::KEY_OPTION, ''));
        }

        // A key left behind by the per-add-on system. A customer upgrading from
        // that world already gave us one, and asking them to type it again
        // would be a poor introduction to the new screen.
        if ($key === '') {
            if (!function_exists('get_plugins')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            foreach (array_keys((array) get_plugins()) as $file) {
                if (strpos($file, 'wp-job-portal') !== 0) { continue; }
                $candidate = trim((string) get_option('transaction_key_for_' . dirname($file), ''));
                if ($candidate !== '') { $key = $candidate; break; }
            }
        }

        return (string) apply_filters('wpjobportal_licence_key', $key);
    }

    /**
     * Everything last known about this site's licence.
     *
     * @return array
     */
    public static function state() {
        $state = get_option(self::STATE_OPTION, array());

        return is_array($state) && $state !== array() ? $state + self::blank() : self::blank('no_state');
    }

    /**
     * Exchange the licence key for a token, and record what it covers.
     *
     * @param string $key Licence key; the stored one by default.
     * @return array The new state.
     */
    public function activate($key = null) {
        $key = $key === null ? self::key() : trim((string) $key);

        if ($key === '') {
            return $this->remember(self::blank('no_key'));
        }

        $reply = $this->call('activate', array(
            'license_key' => $key,
            'site_url' => home_url(),
        ));

        return $this->remember($this->read($reply));
    }

    /**
     * Release this site's activation.
     *
     * A slot is held by the server, not by this site, so forgetting locally
     * without the server agreeing does not free anything — it only loses the
     * one record of which slot to free. A customer moving between hosts would
     * spend a slot per move and eventually be unable to activate anywhere,
     * with nothing on either side to explain why.
     *
     * So a refusal or an unreachable server leaves the state alone and says so.
     * $force is for the case that argument does not cover: the site is being
     * decommissioned and the server cannot be reached at all, where keeping a
     * dead activation on disk helps nobody.
     *
     * @param bool $force Forget locally even if the server did not confirm.
     * @return array State, with ok true when the slot was actually released.
     */
    public function deactivate($force = false) {
        $key = self::key();

        if ($key === '') {
            $this->remember(self::blank('deactivated'));

            return self::blank('deactivated');
        }

        $read = $this->read($this->call('deactivate', array(
            'license_key' => $key,
            'site_url' => home_url(),
        )));

        // Already gone as far as the server is concerned, which is the outcome
        // we wanted; treat it as success rather than making the administrator
        // press the button again.
        $already = in_array($read['reason'], array('not_active', 'unknown_activation'), true);

        if (!$read['ok'] && !$already && !$force) {
            return $read;
        }

        // Remembered rather than forgotten. A deactivated site and a site that
        // has never been activated look identical from the outside — no token,
        // nothing entitled — and refresh() treats the second as something to
        // fix, so deleting this record would re-take the slot on the very next
        // page load and the button would appear to do nothing.
        $this->remember(self::blank('deactivated'));

        $done = self::blank('deactivated');
        $done['ok'] = true;

        return $done;
    }

    /**
     * The current licence state, refreshed when it needs to be.
     *
     * @param bool $force Check now regardless of when it was last checked.
     * @return array
     */
    public function refresh($force = false) {
        $state = self::state();
        $now = time();

        // Somebody deliberately released this site. Re-activating it because a
        // key happens to still be on disk would undo their decision silently,
        // and on a licence with a slot limit that decision usually had a reason.
        // Only activate() reverses this, and only a button calls activate().
        if (isset($state['reason']) && $state['reason'] === 'deactivated') {
            return $state;
        }

        $token = isset($state['token']) ? (string) $state['token'] : '';
        $token_expires = isset($state['token_expires']) ? (int) $state['token_expires'] : 0;
        $checked = isset($state['checked']) ? (int) $state['checked'] : 0;

        // No token, or one that is about to run out: the key is the only thing
        // that can get a new one.
        if ($token === '' || $token_expires <= ($now + self::RENEW_MARGIN)) {

            // A failed activation leaves no token, so without this a wrong key
            // or an unreachable server means an outbound call on every single
            // admin page load, each one blocking the page for up to 20 seconds.
            //
            // Only a failed attempt is backed off. A licensed site renewing an
            // hourly token is in exactly the same branch, and making it wait
            // would leave a paying customer unverified for no reason.
            if (!$force && empty($state['ok']) && $checked > 0
                && ($now - $checked) < self::RETRY_AFTER
            ) {
                return $state;
            }

            return $this->activate();
        }

        if (!$force && ($now - $checked) < self::REVALIDATE_EVERY) {
            return $state;
        }

        $reply = $this->call('validate', array(
            'token' => $token,
            'site_url' => home_url(),
        ));

        $read = $this->read($reply);

        // The server does not recognise the token any more — the licence may
        // have been moved, or the activation released elsewhere. Ask for a new
        // one rather than reporting the customer as unlicensed.
        if (!$read['ok'] && in_array($read['reason'], array('invalid_token', 'expired_token'), true)) {
            return $this->activate();
        }

        // Anything else that failed leaves the last good answer in place. A
        // server having a bad minute must not switch a paid feature off.
        if (!$read['ok'] && $reply === null) {
            return $state;
        }

        // validate does not reissue a token, so carry the existing one.
        $read['token'] = $token;
        $read['token_expires'] = $token_expires;

        return $this->remember($read);
    }

    /**
     * Keep the stored state current, off the request path.
     *
     * The system this replaces checked the licence during a session-cleanup
     * sweep and again on every dashboard load, so a slow licence server was a
     * slow admin. Nothing on a page render talks to the network now: this does,
     * once a day, on WP-Cron.
     *
     * @return void
     */
    public static function register() {
        add_action(self::CRON_HOOK, array(__CLASS__, 'scheduled_check'));

        // Scheduling waits for init. This class is constructed while the plugin
        // boots, before pluggable.php has defined wp_rand(), and asking for the
        // schedule that early is a fatal on every page of the site.
        add_action('init', array(__CLASS__, 'ensure_scheduled'));
    }

    /**
     * @return void
     */
    public static function ensure_scheduled() {
        if (wp_next_scheduled(self::CRON_HOOK)) {
            return;
        }

        // A random offset so a thousand sites sharing a release do not all
        // arrive at the licence server in the same second.
        wp_schedule_event(time() + wp_rand(60, 3600), 'daily', self::CRON_HOOK);
    }

    /**
     * @return void
     */
    public static function scheduled_check() {
        if (self::key() === '') {
            return;
        }

        $client = new self();
        $client->refresh(true);
    }

    /**
     * Which plugin folders this licence covers.
     *
     * @return array
     */
    public function entitlements() {
        $state = $this->refresh();

        return isset($state['plugins']) && is_array($state['plugins']) ? $state['plugins'] : array();
    }

    /**
     * Is this product covered?
     *
     * @param string $plugin_folder e.g. wp-job-portal-pro.
     * @return bool
     */
    public function covers($plugin_folder) {
        return in_array((string) $plugin_folder, $this->entitlements(), true);
    }

    /* ------------------------------------------------------------------ */

    /**
     * The shape every caller can rely on, saying nothing.
     *
     * Screens read expires_at and message without checking they are there, so
     * a path that returns a shorter array turns into a page of PHP warnings.
     *
     * @param string $reason Why there is nothing to say.
     * @return array
     */
    public static function blank($reason = '') {
        return array(
            'ok' => false,
            'reason' => $reason,
            'message' => '',
            'status' => '',
            'expires_at' => '',
            'plan' => '',
            'updates' => false,
            'products' => array(),
            'plugins' => array(),
            'activation' => '',
            'slots' => array(),
            'checked' => 0,
        );
    }

    /**
     * Turn a reply into the state worth keeping.
     *
     * @param array|null $reply Decoded reply, or null when the call failed.
     * @return array
     */
    private function read($reply) {
        $state = self::blank('unreachable');

        // Whatever came back, a call was made, and the backoff is measured from
        // here rather than from the last successful one.
        $state['checked'] = time();

        if (!is_array($reply)) {
            return $state;
        }

        // A protocol we do not speak could mean anything, so it means nothing —
        // and neither does an answer that never claimed to speak one. A wrong
        // URL still returns JSON: WordPress's own 404 is a well-formed object
        // with a message field, which without this test would be read as a
        // licence verdict and printed to the customer as one.
        if (!isset($reply['protocol']) || (int) $reply['protocol'] !== self::PROTOCOL) {
            $state['reason'] = 'protocol_mismatch';

            return $state;
        }

        $state['ok'] = !empty($reply['ok']);
        $state['reason'] = isset($reply['reason']) ? sanitize_key((string) $reply['reason']) : '';
        $state['message'] = isset($reply['message']) ? sanitize_text_field((string) $reply['message']) : '';

        $licence = self::licence_block($reply);
        if ($licence !== null) {
            $state['status'] = isset($licence['status']) ? sanitize_key((string) $licence['status']) : '';
            $state['expires_at'] = isset($licence['expires_at']) ? sanitize_text_field((string) $licence['expires_at']) : '';
            $state['plan'] = isset($licence['plan']) ? sanitize_text_field((string) $licence['plan']) : '';
            $state['updates'] = !empty($licence['updates']);
        }

        $map = self::products();
        foreach ((array) (isset($reply['products']) ? $reply['products'] : array()) as $slug) {
            $slug = sanitize_key(str_replace('_', '-', (string) $slug));
            if ($slug === '') { continue; }

            $state['products'][] = $slug;

            // A product name we do not recognise is recorded but not turned
            // into an entitlement, because we would not know what to unlock.
            if (isset($map[$slug])) {
                $state['plugins'][] = $map[$slug];
            }
        }

        if (isset($reply['activation']['kind'])) {
            $state['activation'] = sanitize_key((string) $reply['activation']['kind']);
        }

        if (isset($reply['slots']) && is_array($reply['slots'])) {
            foreach ($reply['slots'] as $kind => $counts) {
                if (!is_array($counts)) { continue; }
                $state['slots'][sanitize_key((string) $kind)] = array(
                    'used' => isset($counts['used']) ? (int) $counts['used'] : 0,
                    'allowed' => isset($counts['allowed']) ? (int) $counts['allowed'] : 0,
                );
            }
        }

        if (!empty($reply['token']) && is_string($reply['token'])) {
            $state['token'] = $reply['token'];

            $ttl = isset($reply['token_ttl']) ? (int) $reply['token_ttl'] : 0;
            $state['token_expires'] = $ttl > 0 ? (time() + $ttl) : (time() + HOUR_IN_SECONDS);
        }

        return $state;
    }

    /**
     * The licence part of a reply, in whichever spelling the server used.
     *
     * @param array $reply Decoded reply.
     * @return array|null
     */
    public static function licence_block($reply) {
        foreach (array('license', 'licence') as $field) {
            if (isset($reply[$field]) && is_array($reply[$field])) {
                return $reply[$field];
            }
        }

        return null;
    }

    /**
     * @param array $state State to keep.
     * @return array
     */
    private function remember($state) {
        update_option(self::STATE_OPTION, $state, false);

        return $state;
    }

    /**
     * Make one call.
     *
     * @param string $action activate, validate or deactivate.
     * @param array  $body   Request body.
     * @return array|null Null when the call itself failed.
     */
    private function call($action, $body) {
        $url = self::endpoint() . $action;

        if (!self::transport_is_acceptable($url)) {
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
     * Is this URL one we are willing to send a licence key to?
     *
     * HTTPS everywhere, with an exception for local development hosts — the
     * licence server is built and tested on one, and refusing to talk to it
     * would mean the flow could not be exercised before release. Nothing on a
     * customer's site reaches a local address.
     *
     * @param string $url Absolute URL.
     * @return bool
     */
    public static function transport_is_acceptable($url) {
        if (strpos($url, 'https://') === 0) {
            return true;
        }

        if (strpos($url, 'http://') !== 0) {
            return false;
        }

        $host = strtolower((string) wp_parse_url($url, PHP_URL_HOST));

        $local = ($host === 'localhost'
            || $host === '127.0.0.1'
            || $host === '::1'
            || substr($host, -6) === '.local'
            || substr($host, -5) === '.test');

        /**
         * Filter whether a plain-HTTP licence endpoint is acceptable.
         *
         * @param bool   $local Whether the host looks like local development.
         * @param string $host  The hostname.
         */
        return (bool) apply_filters('wpjobportal_licence_allow_http', $local, $host);
    }
}
