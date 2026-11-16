<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

if (class_exists('WPJOBPORTAL_Zywrap_Connect')) {
    return;
}

/**
 * A Zywrap account from the AI screen, with no key to copy. (2.9.0)
 *
 * Zywrap's WordPress connect protocol, in its two forms:
 *   In wp-admin   The administrator gives an e-mail address and ticks the consent
 *                 box; Zywrap e-mails a 6-digit code, which is traded for this
 *                 site's key. An existing account is signed in the same way.
 *   In a browser  For Google or GitHub sign-in and for two-factor accounts: the
 *                 browser goes to zywrap.com and comes back with a one-time code.
 *
 * Both are PKCE: the verifier is made here, kept in a transient for this
 * administrator, and only ever sent server to server. Nothing is sent to Zywrap
 * until an administrator presses the button.
 *
 * Pattern: JS Help Desk's JSSTzywrapconnect (5.0.4).
 */
class WPJOBPORTAL_Zywrap_Connect {

    const OPT_ACCOUNT    = 'wpjobportal_zywrap_account';
    const NONCE          = 'wpjobportal-zywrap-connect';
    const SIGNUP_PREFIX  = 'wpjobportal_zywrap_signup_';
    const CONNECT_PREFIX = 'wpjobportal_zywrap_connect_';
    const PENDING_TTL    = 600;

    const API_URL = 'https://api.zywrap.com';
    const APP_URL = 'https://zywrap.com';

    public static function register() {
        add_action('wp_ajax_wpjobportal_zywrap_signup_start', array(__CLASS__, 'ajaxSignupStart'));
        add_action('wp_ajax_wpjobportal_zywrap_signup_verify', array(__CLASS__, 'ajaxSignupVerify'));
        add_action('admin_init', array(__CLASS__, 'handleReturn'));
    }

    /** The connected account (id, email, key_name, credits, connected_at), or null. */
    public static function account() {
        $account = get_option(self::OPT_ACCOUNT);
        return is_array($account) ? $account : null;
    }

    /** site_url(), not home_url(): wp-admin lives under it, and Zywrap returns codes only there. */
    public static function siteUrl() {
        return untrailingslashit(site_url());
    }

    public static function returnUrl() {
        return admin_url('admin.php?page=wpjobportal_zywrap&wpjobportallt=zywrap');
    }

    /* ------------------------------------------------------------------ *
     * Connecting in a browser
     * ------------------------------------------------------------------ */

    /** The zywrap.com address that starts a browser connect. The caller has checked nonce and capability. */
    public static function browserUrl() {
        $verifier = self::base64url(random_bytes(32));
        $state = self::base64url(random_bytes(24));
        set_transient(self::CONNECT_PREFIX . get_current_user_id(), array('state' => $state, 'verifier' => $verifier), self::PENDING_TTL);

        return add_query_arg(array_map('rawurlencode', array(
            'site'             => self::siteUrl(),
            'site_name'        => wp_strip_all_tags(wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES)),
            'return'           => self::returnUrl(),
            'state'            => $state,
            'challenge'        => self::challenge($verifier),
            'challenge_method' => 'S256',
        )), self::APP_URL . '/connect/wordpress');
    }

    /** The browser coming back from zywrap.com. Runs on admin_init. */
    public static function handleReturn() {
        if (wp_doing_ajax() || WPJOBPORTALrequest::getVar('page', 'get', '') !== 'wpjobportal_zywrap') {
            return;
        }
        $code = (string) WPJOBPORTALrequest::getVar('zywrap_code', 'get', '');
        $error = (string) WPJOBPORTALrequest::getVar('zywrap_error', 'get', '');
        if ($code === '' && $error === '') {
            return;
        }
        if (!current_user_can('manage_options')) {
            return;
        }
        // Single use whatever happens next: a refresh must not get a second try.
        $pendingKey = self::CONNECT_PREFIX . get_current_user_id();
        $pending = get_transient($pendingKey);
        delete_transient($pendingKey);

        $state = (string) WPJOBPORTALrequest::getVar('state', 'get', '');
        if (!is_array($pending) || empty($pending['state']) || empty($pending['verifier'])
                || $state === '' || !hash_equals((string) $pending['state'], $state)) {
            self::finish('expired');
        }
        if ($error !== '') {
            self::finish('cancelled');
        }
        if (!preg_match('/^[A-Za-z0-9._-]{20,4096}$/', $code)) {
            self::finish('invalid');
        }
        $result = self::post('/v1/connect/wordpress/exchange', array(
            'code'         => $code,
            'codeVerifier' => $pending['verifier'],
            'siteUrl'      => self::siteUrl(),
        ));
        if (is_wp_error($result)) {
            self::finish('network');
        }
        list($status, $json) = $result;
        if ($status !== 200 || empty($json['apiKey']) || !is_string($json['apiKey'])) {
            self::finish(!empty($json['code']) ? (string) $json['code'] : 'server', $json);
        }
        $email = self::save($json);
        self::notice(self::connectedMessage($email), 'updated');
        wp_safe_redirect(self::returnUrl());
        exit;
    }

    private static function finish($code, $json = array()) {
        self::notice(self::messageFor($code, $json), 'error');
        wp_safe_redirect(self::returnUrl());
        exit;
    }

    private static function notice($text, $type) {
        if (class_exists('WPJOBPORTALMessages')) {
            WPJOBPORTALMessages::setLayoutMessage(esc_html($text), $type, 'admin_wpjobportal');
        }
    }

    /* ------------------------------------------------------------------ *
     * Signing up in wp-admin
     * ------------------------------------------------------------------ */

    /** Step 1: Zywrap e-mails a 6-digit code. Only once the consent box is ticked. */
    public static function ajaxSignupStart() {
        self::guardAjax();
        $email = sanitize_email((string) WPJOBPORTALrequest::getVar('email', 'post', ''));
        $name = sanitize_text_field((string) WPJOBPORTALrequest::getVar('name', 'post', ''));
        $accept = ((string) WPJOBPORTALrequest::getVar('accept', 'post', '') === '1');
        if (!is_email($email)) {
            self::fail('invalid_email');
        }
        if (!$accept) {
            self::fail('terms_required');
        }
        $verifier = self::base64url(random_bytes(32));
        $result = self::post('/v1/connect/wordpress/signup/start', array(
            'email'               => $email,
            'name'                => $name,
            'siteUrl'             => self::siteUrl(),
            'codeChallenge'       => self::challenge($verifier),
            'codeChallengeMethod' => 'S256',
            'acceptTerms'         => true,
        ));
        if (is_wp_error($result)) {
            self::fail('network');
        }
        list($status, $json) = $result;
        if ($status !== 200 || empty($json['flowId']) || !is_string($json['flowId'])) {
            self::fail(!empty($json['code']) ? (string) $json['code'] : 'server', $json);
        }
        // "Send a new code" replaces the first: only the latest code counts.
        set_transient(self::SIGNUP_PREFIX . get_current_user_id(), array(
            'flow_id'  => sanitize_text_field($json['flowId']),
            'verifier' => $verifier,
        ), self::PENDING_TTL);
        wp_send_json_success(array('email' => $email, 'existingAccount' => !empty($json['existingAccount'])));
    }

    /** Step 2: the code from the e-mail, traded for this site's key. */
    public static function ajaxSignupVerify() {
        self::guardAjax();
        $pendingKey = self::SIGNUP_PREFIX . get_current_user_id();
        $pending = get_transient($pendingKey);
        if (!is_array($pending) || empty($pending['flow_id']) || empty($pending['verifier'])) {
            self::fail('expired_code');
        }
        $code = preg_replace('/\D+/', '', (string) WPJOBPORTALrequest::getVar('code', 'post', ''));
        if (strlen($code) !== 6) {
            self::fail('invalid_code');
        }
        $result = self::post('/v1/connect/wordpress/signup/verify', array(
            'flowId'       => $pending['flow_id'],
            'code'         => $code,
            'codeVerifier' => $pending['verifier'],
            'siteUrl'      => self::siteUrl(),
        ));
        if (is_wp_error($result)) {
            self::fail('network');
        }
        list($status, $json) = $result;
        if ($status !== 200 || empty($json['apiKey']) || !is_string($json['apiKey'])) {
            $why = !empty($json['code']) ? (string) $json['code'] : 'server';
            // A wrong code keeps the sign-up so the next try can be made; Zywrap counts the tries.
            if ($why !== 'wrong_code') {
                delete_transient($pendingKey);
            }
            self::fail($why, $json);
        }
        delete_transient($pendingKey);
        $email = self::save($json);
        self::notice(self::connectedMessage($email), 'updated');
        wp_send_json_success(array('createdAccount' => !empty($json['createdAccount'])));
    }

    private static function guardAjax() {
        if (!wp_verify_nonce((string) WPJOBPORTALrequest::getVar('_wpnonce', 'post', ''), self::NONCE)) {
            wp_send_json_error(array('code' => 'forbidden', 'message' => __('Security check failed. Reload the page and try again.', 'wp-job-portal')), 403);
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('code' => 'forbidden', 'message' => __('You are not allowed to do this.', 'wp-job-portal')), 403);
        }
    }

    private static function fail($code, $json = array()) {
        $left = (is_array($json) && isset($json['attemptsLeft']) && is_numeric($json['attemptsLeft'])) ? (int) $json['attemptsLeft'] : null;
        $message = self::messageFor($code, $json);
        if ($left !== null && strpos($message, (string) $left) === false) {
            /* translators: %d: how many more times a code may be tried */
            $message .= ' ' . sprintf(_n('%d try left.', '%d tries left.', $left, 'wp-job-portal'), $left);
        }
        wp_send_json_error(array('code' => sanitize_key($code), 'message' => $message, 'attemptsLeft' => $left));
    }

    /* ------------------------------------------------------------------ *
     * Keeping it, and letting it go
     * ------------------------------------------------------------------ */

    private static function save($json) {
        // Just issued by Zywrap for this site, so no second check is needed.
        WPJOBPORTAL_AI_Engine::saveKey('zywrap', sanitize_text_field($json['apiKey']), false);
        $email = isset($json['account']['email']) ? sanitize_email((string) $json['account']['email']) : '';
        update_option(self::OPT_ACCOUNT, array(
            'id'           => (isset($json['account']['id']) && is_string($json['account']['id'])) ? sanitize_text_field($json['account']['id']) : '',
            'email'        => $email,
            'key_name'     => isset($json['keyName']) ? sanitize_text_field((string) $json['keyName']) : '',
            'credits'      => (isset($json['credits']) && is_numeric($json['credits'])) ? (int) $json['credits'] : null,
            'connected_at' => time(),
        ), false);
        // Zywrap becomes the engine only where nobody has chosen one.
        if ((string) get_option(WPJOBPORTAL_AI_Engine::OPT_ENGINE, '') === '') {
            update_option(WPJOBPORTAL_AI_Engine::OPT_ENGINE, 'zywrap', false);
        }
        WPJOBPORTAL_AI_Policy::record('zywrap-connect', $email);
        // Ask the licence server now, so a paid licence's "Claim" shows at once.
        WPJOBPORTAL_AI_Credits::link();
        return $email;
    }

    /** Forget the key and the account here. The key stays valid on Zywrap until revoked there. */
    public static function disconnect() {
        WPJOBPORTAL_AI_Engine::forgetKey('zywrap');
        delete_option(self::OPT_ACCOUNT);
        WPJOBPORTAL_AI_Credits::forget();
        WPJOBPORTAL_AI_Policy::record('zywrap-disconnect', '');
    }

    /* ------------------------------------------------------------------ *
     * Plumbing
     * ------------------------------------------------------------------ */

    private static function post($path, $body) {
        $response = wp_remote_post(self::API_URL . $path, array(
            'timeout'     => 20,
            'sslverify'   => true,
            'redirection' => 0,
            'headers'     => array('Content-Type' => 'application/json'),
            'body'        => wp_json_encode($body),
        ));
        if (is_wp_error($response)) {
            return $response;
        }
        $json = json_decode((string) wp_remote_retrieve_body($response), true);
        return array((int) wp_remote_retrieve_response_code($response), is_array($json) ? $json : array());
    }

    private static function base64url($bytes) {
        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }

    private static function challenge($verifier) {
        return self::base64url(hash('sha256', $verifier, true));
    }

    private static function connectedMessage($email) {
        return ($email !== '')
            /* translators: %s: the e-mail address of the Zywrap account */
            ? sprintf(__('Connected to Zywrap as %s.', 'wp-job-portal'), $email)
            : __('Connected to Zywrap.', 'wp-job-portal');
    }

    private static function messageFor($code, $json = array()) {
        if (is_array($json) && !empty($json['message']) && is_string($json['message'])) {
            return sanitize_text_field($json['message']);
        }
        return self::errorMessage($code);
    }

    public static function errorMessage($code) {
        $messages = array(
            'wrong_code'          => __('That code is not right. Check the e-mail and try again.', 'wp-job-portal'),
            'invalid_code'        => __('Enter the 6-digit code from the e-mail.', 'wp-job-portal'),
            'expired_code'        => __('That code has expired. Ask for a new one.', 'wp-job-portal'),
            'code_unavailable'    => __('That code can no longer be used. Ask for a new one.', 'wp-job-portal'),
            'rate_limited'        => __('Too many attempts. Wait a few minutes and try again.', 'wp-job-portal'),
            'key_limit_reached'   => __('Your Zywrap account already has as many API keys as it can. Revoke one on zywrap.com, then connect again.', 'wp-job-portal'),
            'account_blocked'     => __('This Zywrap account is blocked. Contact Zywrap support.', 'wp-job-portal'),
            'use_browser_connect' => __('This account uses two-factor sign-in. Use "Connect on zywrap.com instead" below.', 'wp-job-portal'),
            'network'             => __('This site could not reach Zywrap. Check that it can make outgoing HTTPS requests, then try again.', 'wp-job-portal'),
            'invalid_email'       => __('Enter a valid e-mail address.', 'wp-job-portal'),
            'terms_required'      => __('Tick the box to agree to the Zywrap Terms of Service and Privacy Policy.', 'wp-job-portal'),
            'expired'             => __('That connect link expired or was already used. Connect again to start over.', 'wp-job-portal'),
            'cancelled'           => __('Connecting was cancelled on zywrap.com. Nothing was changed.', 'wp-job-portal'),
            'invalid'             => __('zywrap.com sent back something this could not read. Nothing was changed.', 'wp-job-portal'),
            'server'              => __('Connecting to Zywrap did not finish. Nothing was changed; try again.', 'wp-job-portal'),
        );
        return isset($messages[$code]) ? $messages[$code] : $messages['server'];
    }
}
