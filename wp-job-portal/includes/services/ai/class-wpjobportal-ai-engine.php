<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

if (class_exists('WPJOBPORTAL_AI_Engine')) {
    return;
}

/**
 * Where WP Job Portal's AI text comes from. (2.9.0)
 *
 * One registry, asked by every AI button in Core and the suites, so a site has
 * one answer to "which engine writes this, and who pays for it". Three engines:
 *
 *   zywrap     The default. The account is created or connected inside wp-admin
 *              (WPJOBPORTAL_Zywrap_Connect), a paid licence adds monthly credits
 *              to it (WPJOBPORTAL_AI_Credits), and Zywrap's ready-made hiring
 *              prompts ("wrappers") do the writing.
 *   anthropic  The site's own Anthropic key. Request goes straight from this
 *              server to Anthropic: their key, their bill.
 *   openai     The same with OpenAI.
 *
 * Requests use wp_remote_post rather than a bundled SDK: a plugin installed from
 * a zip next to dozens of others cannot rely on Composer, and two plugins
 * shipping two copies of one SDK is a classic way to take a site down.
 *
 * Pattern: JS Help Desk's JSSTaiengine (5.0.4), without its local-model lane.
 */
class WPJOBPORTAL_AI_Engine {

    const OPT_ENGINE       = 'wpjobportal_ai_engine';
    const OPT_FALLBACK     = 'wpjobportal_ai_fallback';
    const OPT_MODEL        = 'wpjobportal_ai_model_';
    const OPT_KEY          = 'wpjobportal_ai_key_';
    const OPT_ZYWRAP_MODEL = 'wpjobportal_ai_zywrap_model';
    /** 2.8.0 and earlier: the Zywrap key, in plain text, autoloaded. Moved on first read. */
    const LEGACY_ZYWRAP_KEY = 'wpjobportal_zywrap_api_key';
    /** Set while Zywrap refuses for want of credits; cleared by the next answer. */
    const OPT_ZYWRAP_NO_CREDITS = 'wpjobportal_ai_zywrap_no_credits';

    /** Seconds to wait for an answer. A person is waiting on almost every call. */
    const TIMEOUT     = 60;
    const TIMEOUT_MAX = 120;
    /** The longest retry-after this waits out before asking once more. */
    const RETRY_WAIT_MAX = 5;

    const ZYWRAP_PROXY = 'https://api.zywrap.com/v1/proxy';
    const ZYWRAP_CHECK = 'https://api.zywrap.com/v1/key/check';

    /* ------------------------------------------------------------------ *
     * The engines
     * ------------------------------------------------------------------ */

    public static function engines() {
        $engines = array(
            'zywrap' => array(
                'label'         => 'Zywrap',
                'blurb'         => __('Create a free account right here, with 10,000 credits to start, or connect the one you have. Paid WP Job Portal plans add monthly credits.', 'wp-job-portal'),
                'recommended'   => true,
                'models'        => array(),
                'default_model' => '',
                'keyhint'       => __('A key from the API keys page of your Zywrap account.', 'wp-job-portal'),
                'ask'           => array(__CLASS__, 'askZywrap'),
            ),
            'anthropic' => array(
                'label'    => 'Anthropic (Claude)',
                'blurb'    => __('Your own Anthropic key. The request goes from your server to Anthropic, and Anthropic bills you.', 'wp-job-portal'),
                'endpoint' => 'https://api.anthropic.com/v1/messages',
                'models'   => array(
                    // The default first, and the cheapest current model: these are
                    // short, well-specified writing jobs.
                    'claude-haiku-5-5'  => 'Claude Haiku 5.5 - $0.10 / $0.50 per 1M tokens (recommended)',
                    'claude-sonnet-5-5' => 'Claude Sonnet 5.5 - $2 / $10 per 1M tokens',
                    'claude-opus-5-5'   => 'Claude Opus 5.5 - $4 / $20 per 1M tokens',
                ),
                'default_model' => 'claude-haiku-5-5',
                'build'         => array(__CLASS__, 'buildAnthropic'),
                'read'          => array(__CLASS__, 'readAnthropic'),
                'keyhint'       => __('Starts with sk-ant-. Created at console.anthropic.com.', 'wp-job-portal'),
            ),
            'openai' => array(
                'label'    => 'OpenAI',
                'blurb'    => __('Your own OpenAI key. The request goes from your server to OpenAI, and OpenAI bills you.', 'wp-job-portal'),
                'endpoint' => 'https://api.openai.com/v1/chat/completions',
                'models'   => array(
                    'gpt-6-luna'    => 'GPT-6 Luna - $0.10 / $0.50 per 1M tokens (recommended)',
                    'gpt-5.6-luna'  => 'GPT-5.6 Luna - $0.20 / $1.20 per 1M tokens',
                    'gpt-6.1-terra' => 'GPT-6.1 Terra - $1 / $5 per 1M tokens',
                    'gpt-6.1-sol'   => 'GPT-6.1 Sol - $2 / $10 per 1M tokens',
                ),
                'default_model' => 'gpt-6-luna',
                'build'         => array(__CLASS__, 'buildOpenAi'),
                'read'          => array(__CLASS__, 'readOpenAi'),
                'keyhint'       => __('Starts with sk-. Created at platform.openai.com.', 'wp-job-portal'),
            ),
        );
        return apply_filters('wpjobportal_ai_engines', $engines);
    }

    /** The engine in use; a stale option falls back to Zywrap rather than erroring. */
    public static function current() {
        $engines = self::engines();
        $id = (string) get_option(self::OPT_ENGINE, '');
        if ($id === '' || !isset($engines[$id])) {
            $id = isset($engines['zywrap']) ? 'zywrap' : key($engines);
        }
        return array('key' => $id, 'def' => $engines[$id]);
    }

    public static function currentId() {
        $current = self::current();
        return $current['key'];
    }

    public static function currentLabel() {
        $current = self::current();
        return $current['def']['label'];
    }

    public static function byId($id) {
        $engines = self::engines();
        $id = (string) $id;
        return isset($engines[$id]) ? array('key' => $id, 'def' => $engines[$id]) : false;
    }

    public static function setEngine($id) {
        if (!self::byId($id)) {
            return false;
        }
        if (self::currentId() !== $id && class_exists('WPJOBPORTAL_AI_Policy')) {
            WPJOBPORTAL_AI_Policy::record('engine', $id);
        }
        update_option(self::OPT_ENGINE, (string) $id, false);
        return true;
    }

    /**
     * The model to ask for. A saved name from another engine's list falls back
     * to the engine's default, so switching engine never sends a 404-ing model.
     */
    public static function model($id = null) {
        $current = ($id === null) ? self::current() : self::byId($id);
        if (!$current) {
            return '';
        }
        if ($current['key'] === 'zywrap') {
            $chosen = trim((string) get_option(self::OPT_ZYWRAP_MODEL, ''));
            return $chosen !== '' ? $chosen : 'zywrap';
        }
        $model = trim((string) get_option(self::OPT_MODEL . $current['key'], ''));
        if ($model !== '' && isset($current['def']['models'][$model])) {
            return $model;
        }
        return (string) $current['def']['default_model'];
    }

    public static function setModel($id, $model) {
        $current = self::byId($id);
        if (!$current) {
            return false;
        }
        $model = trim((string) $model);
        if ($current['key'] === 'zywrap') {
            update_option(self::OPT_ZYWRAP_MODEL, sanitize_text_field($model), false);
            delete_transient('wpjobportal_ai_zywrap_model_gone');
            return true;
        }
        if (!isset($current['def']['models'][$model])) {
            return false;
        }
        update_option(self::OPT_MODEL . $current['key'], $model, false);
        return true;
    }

    /* ------------------------------------------------------------------ *
     * Keys
     * ------------------------------------------------------------------ */

    /**
     * The key for one engine, read only at the moment of use. Never shown, never
     * logged, never sent to the browser.
     */
    public static function apiKey($id) {
        $id = (string) $id;
        $stored = (string) get_option(self::OPT_KEY . $id, '');
        if ($stored !== '') {
            return self::decrypt($stored);
        }
        // 2.8.0 kept the Zywrap key in plain text in an autoloaded option.
        if ($id === 'zywrap') {
            $legacy = self::legacyZywrapKey();
            if ($legacy !== '') {
                self::storeKey('zywrap', $legacy);
                delete_option(self::LEGACY_ZYWRAP_KEY);
                return $legacy;
            }
        }
        return '';
    }

    /**
     * On the first admin page after the update: move a 2.8.0 key out of its
     * plain-text, autoloaded option, so no site keeps that copy until somebody
     * happens to use an AI button.
     */
    public static function migrateLegacyKey() {
        if (self::legacyZywrapKey() !== '') {
            self::apiKey('zywrap');
        }
        // 2.8.0's "key checked" status: nothing reads it any more.
        if (get_option('wpjobportal_zywrap_api_status', null) !== null) {
            delete_option('wpjobportal_zywrap_api_status');
        }
    }

    /** The old option, read past the compatibility filter that now answers for it. */
    private static function legacyZywrapKey() {
        global $wpdb;
        $value = $wpdb->get_var($wpdb->prepare(
            "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s LIMIT 1",
            self::LEGACY_ZYWRAP_KEY
        ));
        return trim((string) $value);
    }

    /**
     * Keep a key, after checking it with the provider.
     *
     * An empty value means "leave the saved key alone", never "delete it": the
     * screen can never show a key back, so the box is empty on every save.
     *
     * @return true|WP_Error
     */
    public static function saveKey($id, $value, $check = true) {
        $id = (string) $id;
        $value = trim((string) $value);
        if ($value === '' || !self::byId($id)) {
            return new WP_Error('wpjobportal_ai_nokey', __('Enter a key first.', 'wp-job-portal'));
        }
        if ($check) {
            $verdict = self::checkKey($id, $value);
            if (is_wp_error($verdict)) {
                return $verdict;
            }
        }
        self::storeKey($id, $value);
        if (class_exists('WPJOBPORTAL_AI_Policy')) {
            WPJOBPORTAL_AI_Policy::record('key', $id);
        }
        return true;
    }

    private static function storeKey($id, $value) {
        update_option(self::OPT_KEY . $id, self::encrypt($value), false);
    }

    public static function forgetKey($id) {
        delete_option(self::OPT_KEY . (string) $id);
        if ($id === 'zywrap') {
            delete_option(self::LEGACY_ZYWRAP_KEY);
        }
    }

    /** Enough of a key to recognise it, not enough to use it. */
    public static function keyHint($id) {
        $key = self::apiKey($id);
        if ($key === '') {
            return '';
        }
        return str_repeat('*', 8) . substr($key, -4);
    }

    /**
     * Ask the provider whether a key works, without spending anything: Zywrap's
     * key check, and the model list for Anthropic and OpenAI.
     *
     * @return true|WP_Error
     */
    public static function checkKey($id, $key) {
        if ($id === 'zywrap') {
            $response = wp_remote_post(self::ZYWRAP_CHECK, array(
                'timeout'     => 20,
                'redirection' => 0,
                'headers'     => array('Content-Type' => 'application/json'),
                'body'        => wp_json_encode(array('apiKey' => $key)),
            ));
        } elseif ($id === 'anthropic') {
            $response = wp_remote_get('https://api.anthropic.com/v1/models?limit=1', array(
                'timeout'     => 20,
                'redirection' => 0,
                'headers'     => array('x-api-key' => $key, 'anthropic-version' => '2023-06-01'),
            ));
        } elseif ($id === 'openai') {
            $response = wp_remote_get('https://api.openai.com/v1/models', array(
                'timeout'     => 20,
                'redirection' => 0,
                'headers'     => array('authorization' => 'Bearer ' . $key),
            ));
        } else {
            return new WP_Error('wpjobportal_ai_noengine', __('That is not an engine this site knows about.', 'wp-job-portal'));
        }
        if (is_wp_error($response)) {
            return new WP_Error('wpjobportal_ai_unreachable', sprintf(
                /* translators: %s: the network error */
                __('Could not reach the provider to check the key: %s', 'wp-job-portal'),
                $response->get_error_message()
            ));
        }
        $code = (int) wp_remote_retrieve_response_code($response);
        if ($code === 401 || $code === 403) {
            return new WP_Error('wpjobportal_ai_auth', __('The provider rejected this key. Check it and try again.', 'wp-job-portal'));
        }
        if ($code !== 200) {
            return new WP_Error('wpjobportal_ai_failed', sprintf(
                /* translators: %d: HTTP status code */
                __('The provider could not check the key (HTTP %d). Try again shortly.', 'wp-job-portal'),
                $code
            ));
        }
        if ($id === 'zywrap') {
            $json = json_decode((string) wp_remote_retrieve_body($response), true);
            if (!is_array($json) || (isset($json['status']) && !in_array($json['status'], array('ok', 'limited'), true))) {
                return new WP_Error('wpjobportal_ai_auth', __('Zywrap says this key is not active.', 'wp-job-portal'));
            }
            if (!empty($json['account']['id']) && is_string($json['account']['id'])) {
                $account = get_option('wpjobportal_zywrap_account');
                if (!is_array($account)) {
                    $account = array('email' => '', 'key_name' => '', 'connected_at' => time());
                }
                $account['id'] = sanitize_text_field($json['account']['id']);
                if (!empty($json['account']['email'])) {
                    $account['email'] = sanitize_email((string) $json['account']['email']);
                }
                if (isset($json['credits']['balance']) && is_numeric($json['credits']['balance'])) {
                    $account['credits'] = (int) floor((float) $json['credits']['balance']);
                    $account['credits_at'] = time();
                }
                update_option('wpjobportal_zywrap_account', $account, false);
            }
        }
        return true;
    }

    /**
     * Keys are kept encrypted with a key derived from this site's salts, so a
     * database dump on its own does not hand over a working provider key.
     */
    private static function cipherKey() {
        return hash('sha256', wp_salt('auth') . '|wpjobportal-ai-keys', true);
    }

    private static function encrypt($plain) {
        if (!function_exists('openssl_encrypt') || !in_array('aes-256-gcm', openssl_get_cipher_methods(), true)) {
            return 'plain:' . $plain;
        }
        $iv = random_bytes(12);
        $tag = '';
        $cipher = openssl_encrypt($plain, 'aes-256-gcm', self::cipherKey(), OPENSSL_RAW_DATA, $iv, $tag);
        if ($cipher === false) {
            return 'plain:' . $plain;
        }
        return 'wpjpenc1:' . base64_encode($iv . $tag . $cipher);
    }

    private static function decrypt($stored) {
        $stored = (string) $stored;
        if (strpos($stored, 'plain:') === 0) {
            return substr($stored, 6);
        }
        if (strpos($stored, 'wpjpenc1:') !== 0 || !function_exists('openssl_decrypt')) {
            return '';
        }
        $raw = base64_decode(substr($stored, 9), true);
        if ($raw === false || strlen($raw) < 29) {
            return '';
        }
        $plain = openssl_decrypt(substr($raw, 28), 'aes-256-gcm', self::cipherKey(), OPENSSL_RAW_DATA, substr($raw, 0, 12), substr($raw, 12, 16));
        // The salts changed (a migrated site): the key has to be entered again.
        return ($plain === false) ? '' : $plain;
    }

    /* ------------------------------------------------------------------ *
     * Ready?
     * ------------------------------------------------------------------ */

    public static function configured($id = null) {
        $current = ($id === null) ? self::current() : self::byId($id);
        return $current ? (self::apiKey($current['key']) !== '') : false;
    }

    /** The master switch is on and the engine is set up. */
    public static function usable($id = null) {
        if (class_exists('WPJOBPORTAL_AI_Policy') && !WPJOBPORTAL_AI_Policy::enabled()) {
            return false;
        }
        return self::configured($id);
    }

    /** A page on zywrap.com, tagged so Zywrap can tell the visit came from WP Job Portal. */
    public static function zywrapUrl($path = '') {
        return add_query_arg(
            array('utm_source' => 'wp-job-portal', 'utm_medium' => 'plugin'),
            'https://www.zywrap.com/' . ltrim((string) $path, '/')
        );
    }

    /* ------------------------------------------------------------------ *
     * Asking
     * ------------------------------------------------------------------ */

    /**
     * Run one call on the configured engine, then the fallback engine when the
     * first could not answer for a reason the second might not share.
     *
     * @param array $call system, prompt, maxtokens, effort, schema, feature, item,
     *                    timeout, zywrap (wrapper, variables).
     * @return array|WP_Error text, truncated, model, intokens, outtokens.
     */
    public static function ask($call) {
        $first = null;
        foreach (self::chain() as $id) {
            $result = self::askEngine($id, $call);
            if (!is_wp_error($result)) {
                return $result;
            }
            if ($first === null) {
                $first = $result;
            }
            if (!self::worthRetrying($result)) {
                return $first;
            }
        }
        return ($first !== null) ? $first
            : new WP_Error('wpjobportal_ai_noengine', __('No AI engine is set up on this site.', 'wp-job-portal'));
    }

    public static function chain() {
        $chain = array(self::currentId());
        $spare = self::fallbackId();
        if ($spare !== '' && $spare !== $chain[0] && self::usable($spare)) {
            $chain[] = $spare;
        }
        return $chain;
    }

    public static function fallbackId() {
        $id = (string) get_option(self::OPT_FALLBACK, '');
        return self::byId($id) ? $id : '';
    }

    public static function setFallback($id) {
        update_option(self::OPT_FALLBACK, self::byId($id) ? (string) $id : '', false);
        return true;
    }

    /** Only "not answering" and "out of budget" are worth a second engine. */
    public static function worthRetrying($error) {
        return is_wp_error($error) && in_array($error->get_error_code(), array(
            'wpjobportal_ai_unreachable',
            'wpjobportal_ai_ratelimited',
            'wpjobportal_ai_overloaded',
            'wpjobportal_ai_budget',
            'wpjobportal_ai_empty',
        ), true);
    }

    /**
     * One engine, asked once: the switch, the key, the scrub, then the meter.
     */
    public static function askEngine($id, $call) {
        $current = self::byId($id);
        if (!$current) {
            return new WP_Error('wpjobportal_ai_noengine', __('That is not an engine this site knows about.', 'wp-job-portal'));
        }
        if (class_exists('WPJOBPORTAL_AI_Policy') && !WPJOBPORTAL_AI_Policy::enabled()) {
            return new WP_Error('wpjobportal_ai_off', __('AI is switched off on this site.', 'wp-job-portal'));
        }
        if (!self::configured($current['key'])) {
            return new WP_Error('wpjobportal_ai_nokey', __('No AI engine is set up yet. An administrator can connect one under WP Job Portal > AI > Settings.', 'wp-job-portal'));
        }

        // Everything about to leave the site, scrubbed once, here.
        if (class_exists('WPJOBPORTAL_AI_Policy')) {
            foreach (array('prompt', 'system') as $field) {
                if (isset($call[$field])) {
                    $call[$field] = WPJOBPORTAL_AI_Policy::redact($call[$field]);
                }
            }
            if (!empty($call['zywrap']['variables']) && is_array($call['zywrap']['variables'])) {
                $call['zywrap']['variables'] = self::redactValues($call['zywrap']['variables']);
            }
            if (!empty($call['zywrap']['inputs']) && is_array($call['zywrap']['inputs'])) {
                $call['zywrap']['inputs'] = self::redactValues($call['zywrap']['inputs']);
            }
        }

        $def = $current['def'];
        $work = function () use ($current, $def, $call) {
            return self::whole(self::send($current, $def, $call), $call);
        };
        if (class_exists('WPJOBPORTAL_AI_Usage')) {
            return WPJOBPORTAL_AI_Usage::meter(array(
                'engine'  => $current['key'],
                'model'   => self::model($current['key']),
                'funded'  => ($current['key'] === 'zywrap') ? WPJOBPORTAL_AI_Usage::FUNDED_ZYWRAP : WPJOBPORTAL_AI_Usage::FUNDED_BYOK,
                'feature' => isset($call['feature']) ? (string) $call['feature'] : '',
                'item'    => isset($call['item']) ? (int) $call['item'] : 0,
            ), $work);
        }
        return $work();
    }

    /** A cut-off answer is refused when the caller cannot use one. */
    private static function whole($result, $call) {
        if (is_wp_error($result) || empty($call['notruncate']) || empty($result['truncated'])) {
            return $result;
        }
        return new WP_Error('wpjobportal_ai_truncated', __('The answer was cut off before it finished, so it was not used.', 'wp-job-portal'), array(
            'intokens'  => isset($result['intokens']) ? (int) $result['intokens'] : 0,
            'outtokens' => isset($result['outtokens']) ? (int) $result['outtokens'] : 0,
        ));
    }

    private static function timeoutFor($call) {
        $seconds = isset($call['timeout']) ? (int) $call['timeout'] : self::TIMEOUT;
        return max(10, min(self::TIMEOUT_MAX, $seconds));
    }

    /** The request itself. Nothing here decides whether it should be made. */
    private static function send($current, $def, $call) {
        if (isset($def['ask']) && is_callable($def['ask'])) {
            return call_user_func($def['ask'], $call, $current['key']);
        }

        $request = call_user_func($def['build'], $call, self::model($current['key']), self::apiKey($current['key']));
        if (is_wp_error($request)) {
            return $request;
        }
        $args = array(
            'headers'     => $request['headers'],
            'body'        => wp_json_encode($request['body']),
            'timeout'     => self::timeoutFor($call),
            'sslverify'   => true,
            'redirection' => 0,
        );
        $response = wp_remote_post($def['endpoint'], $args);

        // One more try when the provider says "busy" and asks for a short wait.
        // Never after a transport error: a request we stopped waiting for may
        // still have run and been billed.
        if (!is_wp_error($response)) {
            $wait = self::retryWait($response);
            if ($wait !== null) {
                sleep($wait);
                $response = wp_remote_post($def['endpoint'], $args);
            }
        }

        if (is_wp_error($response)) {
            return new WP_Error('wpjobportal_ai_unreachable', sprintf(
                /* translators: %s: the network error the request failed with */
                __('Could not reach the AI provider: %s', 'wp-job-portal'),
                $response->get_error_message()
            ));
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $body = json_decode((string) wp_remote_retrieve_body($response), true);
        if ($code !== 200) {
            return self::httpError($code, is_array($body) ? $body : array());
        }
        if (!is_array($body)) {
            return new WP_Error('wpjobportal_ai_unreadable', __('The AI provider sent something this could not read.', 'wp-job-portal'));
        }
        return call_user_func($def['read'], $body);
    }

    private static function busy($code) {
        return ($code === 429 || ($code >= 500 && $code <= 599));
    }

    private static function retryWait($response) {
        if (!self::busy((int) wp_remote_retrieve_response_code($response))) {
            return null;
        }
        $body = json_decode((string) wp_remote_retrieve_body($response), true);
        if (is_array($body) && self::outOfCredit($body)) {
            return null;
        }
        $after = trim((string) wp_remote_retrieve_header($response, 'retry-after'));
        $seconds = ($after === '') ? 2 : (is_numeric($after) ? (int) ceil((float) $after) : 99);
        return ($seconds > self::RETRY_WAIT_MAX) ? null : max(1, $seconds);
    }

    private static function httpError($code, $body) {
        $detail = '';
        if (isset($body['error']['message'])) {
            $detail = (string) $body['error']['message'];
        } elseif (isset($body['error']) && is_string($body['error'])) {
            $detail = (string) $body['error'];
        }
        if (self::outOfCredit($body)) {
            return new WP_Error('wpjobportal_ai_budget', __('The AI provider account has no credit left. Add credit with the provider, or choose another engine under WP Job Portal > AI > Settings.', 'wp-job-portal'));
        }
        switch ($code) {
            case 401:
            case 403:
                return new WP_Error('wpjobportal_ai_auth', __('The AI provider rejected the key. An administrator can check it under WP Job Portal > AI > Settings.', 'wp-job-portal'));
            case 404:
                return new WP_Error('wpjobportal_ai_model', __('The provider does not offer the model this site is set to use. Pick another under WP Job Portal > AI > Settings.', 'wp-job-portal'));
            case 429:
                return new WP_Error('wpjobportal_ai_ratelimited', __('The AI provider is busy with this site\'s requests. Wait a moment and try again.', 'wp-job-portal'));
        }
        if ($code === 408 || self::busy($code)) {
            return new WP_Error('wpjobportal_ai_overloaded', __('The AI provider is unavailable right now. Nothing was changed; try again shortly.', 'wp-job-portal'));
        }
        return new WP_Error('wpjobportal_ai_failed', sprintf(
            /* translators: 1: HTTP status code, 2: the provider's own error message */
            __('The AI provider refused the request (%1$d). %2$s', 'wp-job-portal'),
            $code,
            sanitize_text_field($detail)
        ));
    }

    /** OpenAI's "no credit left" arrives as a 429, like a rate limit. */
    private static function outOfCredit($body) {
        foreach (array('code', 'type') as $field) {
            if (isset($body['error'][$field]) && $body['error'][$field] === 'insufficient_quota') {
                return true;
            }
        }
        return false;
    }

    private static function redactValues($value) {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = self::redactValues($v);
            }
            return $value;
        }
        return is_string($value) ? WPJOBPORTAL_AI_Policy::redact($value) : $value;
    }

    /* ------------------------------------------------------------------ *
     * Zywrap
     * ------------------------------------------------------------------ */

    /**
     * Ask Zywrap. It takes a wrapper code (a prompt template kept on Zywrap's
     * side) rather than a system prompt, so it has its own asker.
     *
     * 2.9.0: a task names its WP Job Portal wrapper ('own', for example
     * wpjp_cover_letter_writer) and that wrapper's named inputs ('inputs').
     * Those wrappers hold the instructions, so no prompt is sent. Until Zywrap
     * offers a code (404 wrapper_not_found) the catalogue wrapper answers
     * instead, with the instructions in the prompt; the miss is remembered for
     * six hours, so a press does not cost two requests.
     */
    public static function askZywrap($call, $id) {
        $key = self::apiKey($id);
        $z = (isset($call['zywrap']) && is_array($call['zywrap'])) ? $call['zywrap'] : array();
        $wrapper = !empty($z['wrapper']) ? (string) $z['wrapper'] : '';
        $own = !empty($z['own']) ? (string) $z['own'] : '';
        if ($wrapper === '' && $own === '') {
            return new WP_Error('wpjobportal_ai_zywrap_wrapper', __('This AI task has no Zywrap prompt. Use an Anthropic or OpenAI key for it.', 'wp-job-portal'));
        }

        // What every call carries: the language, the model and the output overrides.
        $common = array();
        if (!empty($call['language'])) {
            $common['language'] = self::zywrapLanguage($call['language']);
        }
        $model = self::model('zywrap');
        if ($model !== 'zywrap' && get_transient('wpjobportal_ai_zywrap_model_gone') !== $model) {
            $common['model'] = $model;
        }
        foreach (array('output' => 'outputCode', 'format' => 'formatCode') as $wpjobportal_k => $wpjobportal_api) {
            if (!empty($z[$wpjobportal_k])) {
                $common[$wpjobportal_api] = (string) $z[$wpjobportal_k];
            }
        }

        if ($own !== '' && !get_transient(self::zywrapMissKey($own))) {
            $inputs = array();
            foreach ((isset($z['inputs']) && is_array($z['inputs'])) ? $z['inputs'] : array() as $wpjobportal_name => $wpjobportal_value) {
                $wpjobportal_value = is_array($wpjobportal_value) ? implode(', ', $wpjobportal_value) : (string) $wpjobportal_value;
                if (trim($wpjobportal_value) !== '') {
                    $inputs[$wpjobportal_name] = $wpjobportal_value;
                }
            }
            $result = self::zywrapSend($key, array_merge(array(
                'wrapperCodes' => array($own),
                'variables'    => (object) $inputs,
                'source'       => 'wp-job-portal',
            ), $common), $call);
            if (!is_wp_error($result) || $result->get_error_code() !== 'wpjobportal_ai_zywrap_nowrapper' || $wrapper === '') {
                return $result;
            }
            set_transient(self::zywrapMissKey($own), 1, 6 * HOUR_IN_SECONDS);
        }

        $prompt = (isset($call['system']) && $call['system'] !== '')
            ? $call['system'] . "\n\n" . $call['prompt']
            : (string) $call['prompt'];

        // Zywrap takes no schema, so the fields are asked for in words.
        if (!empty($call['schema']['properties']) && is_array($call['schema']['properties'])) {
            $keys = array();
            foreach ($call['schema']['properties'] as $name => $prop) {
                $keys[] = '"' . $name . '"' . (!empty($prop['description']) ? ' (' . $prop['description'] . ')' : '');
            }
            $prompt .= "\n\nAnswer with ONLY one JSON object and nothing else: no headings, no markdown, no code fence. Its keys must be exactly: " . implode('; ', $keys) . '.';
        }

        // No `source` field on catalogue calls: 2.8.0 never sent one.
        $body = array(
            'wrapperCodes' => array($wrapper),
            'prompt'       => $prompt,
        );
        if (!empty($z['variables']) && is_array($z['variables'])) {
            $body['variables'] = (object) $z['variables'];
        }
        return self::zywrapSend($key, array_merge($body, $common), $call);
    }

    /** The transient that remembers Zywrap does not offer a wrapper code yet. */
    private static function zywrapMissKey($code) {
        return 'wpjobportal_ai_zw_miss_' . md5((string) $code);
    }

    /** The two-letter code Zywrap lists (de, pt, zh), from a WordPress locale or a code. */
    public static function zywrapLanguage($lang) {
        $lang = strtolower(str_replace('-', '_', trim((string) $lang)));
        $parts = explode('_', $lang);
        return preg_match('/^[a-z]{2,3}$/', $parts[0]) ? $parts[0] : $lang;
    }

    /** One request to Zywrap's proxy, and its answer read. */
    private static function zywrapSend($key, $body, $call) {
        $args = array(
            'headers'     => array('Authorization' => 'Bearer ' . $key, 'Content-Type' => 'application/json'),
            'body'        => wp_json_encode($body),
            'timeout'     => self::timeoutFor($call),
            'sslverify'   => true,
            'redirection' => 0,
        );
        $response = wp_remote_post(self::ZYWRAP_PROXY, $args);

        // A saved model Zywrap no longer offers (or that needs bought credits):
        // ask again with the model left to Zywrap.
        if (isset($body['model']) && !is_wp_error($response)) {
            $check = self::parseZywrap(wp_remote_retrieve_body($response));
            $why = (is_array($check) && isset($check['code'])) ? (string) $check['code'] : '';
            if ($why === 'model_not_found' || $why === 'model_requires_paid_credits') {
                set_transient('wpjobportal_ai_zywrap_model_gone', $body['model'], $why === 'model_requires_paid_credits' ? HOUR_IN_SECONDS : DAY_IN_SECONDS);
                unset($body['model']);
                $args['body'] = wp_json_encode($body);
                $response = wp_remote_post(self::ZYWRAP_PROXY, $args);
            }
        }

        if (is_wp_error($response)) {
            return new WP_Error('wpjobportal_ai_unreachable', sprintf(
                /* translators: %s: the network error the request failed with */
                __('Could not reach Zywrap: %s', 'wp-job-portal'),
                $response->get_error_message()
            ));
        }

        $status = (int) wp_remote_retrieve_response_code($response);
        $raw = (string) wp_remote_retrieve_body($response);
        $json = self::parseZywrap($raw);
        $usage = self::usageFrom($json);

        if (is_array($json) && isset($json['output'])) {
            self::noteCredits($json);
            delete_transient(self::OPT_ZYWRAP_NO_CREDITS);
            $output = $json['output'];
            if (is_array($output) || is_object($output)) {
                $output = wp_json_encode($output);
            }
            if (!is_string($output) || trim($output) === '') {
                return new WP_Error('wpjobportal_ai_empty', __('Zywrap returned an empty answer. Try again.', 'wp-job-portal'), $usage);
            }
            return array_merge(array(
                'text'      => trim($output),
                'truncated' => false,
                'model'     => isset($json['model']) ? (string) $json['model'] : 'zywrap',
            ), $usage);
        }

        if (is_array($json) && (isset($json['error']) || isset($json['code']))) {
            $code = isset($json['code']) ? (string) $json['code'] : '';
            $text = self::zywrapErrorText($json);
            if ($status === 402 || in_array($code, array('insufficient_credits', 'model_requires_paid_credits'), true)) {
                set_transient(self::OPT_ZYWRAP_NO_CREDITS, time(), DAY_IN_SECONDS);
                return new WP_Error('wpjobportal_ai_budget', __('The Zywrap credits have run out. An administrator can add credits on zywrap.com.', 'wp-job-portal'), $usage);
            }
            if (in_array($code, array('api_key_invalid', 'api_key_revoked', 'api_key_missing', 'api_key_no_user'), true) || $status === 401 || $status === 403) {
                return new WP_Error('wpjobportal_ai_auth', __('Zywrap rejected this site\'s key. An administrator can connect Zywrap again under WP Job Portal > AI > Settings.', 'wp-job-portal'), $usage);
            }
            if ($status === 429 || $code === 'rate_limited') {
                return new WP_Error('wpjobportal_ai_ratelimited', __('Zywrap is busy with this site\'s requests. Wait a moment and try again.', 'wp-job-portal'), $usage);
            }
            if ($status >= 500) {
                return new WP_Error('wpjobportal_ai_overloaded', __('Zywrap is unavailable right now. Nothing was changed; try again shortly.', 'wp-job-portal'), $usage);
            }
            if ($status === 404 && $code === 'wrapper_not_found') {
                return new WP_Error('wpjobportal_ai_zywrap_nowrapper', sprintf(
                    /* translators: %s: the error Zywrap returned */
                    __('Zywrap refused the request: %s', 'wp-job-portal'),
                    sanitize_text_field($text)
                ), $usage);
            }
            return new WP_Error('wpjobportal_ai_failed', sprintf(
                /* translators: %s: the error Zywrap returned */
                __('Zywrap refused the request: %s', 'wp-job-portal'),
                sanitize_text_field($text)
            ), $usage);
        }

        // Zywrap down: Cloudflare answers with an HTML page, not JSON.
        if ($status >= 500 || $status === 0) {
            return new WP_Error('wpjobportal_ai_overloaded', __('Zywrap is unavailable right now. Nothing was changed; try again shortly.', 'wp-job-portal'));
        }
        return new WP_Error('wpjobportal_ai_unreadable', sprintf(
            /* translators: %d: HTTP status code */
            __('Zywrap sent an answer this could not read (HTTP %d).', 'wp-job-portal'),
            $status
        ));
    }

    /** A direct JSON answer, or the last useful "data:" line of an event stream. */
    public static function parseZywrap($raw) {
        $direct = json_decode((string) $raw, true);
        if (is_array($direct) && (isset($direct['output']) || isset($direct['error']) || isset($direct['code']))) {
            return $direct;
        }
        $found = null;
        foreach (explode("\n", (string) $raw) as $line) {
            $line = trim($line);
            if (strpos($line, 'data: ') === 0) {
                $data = json_decode(substr($line, 6), true);
                if (is_array($data) && (isset($data['output']) || isset($data['error']))) {
                    $found = $data;
                }
            }
        }
        return $found;
    }

    private static function zywrapErrorText($body) {
        if (isset($body['error'])) {
            return is_string($body['error']) ? $body['error'] : (string) wp_json_encode($body['error']);
        }
        return isset($body['message']) ? (string) $body['message'] : (isset($body['code']) ? (string) $body['code'] : '');
    }

    /** Token counts under whichever names the answer uses; empty when it has none. */
    public static function usageFrom($body) {
        if (!is_array($body)) {
            return array();
        }
        $places = array($body);
        foreach (array('usage', 'tokens', 'meta', 'metadata') as $k) {
            if (isset($body[$k]) && is_array($body[$k])) {
                $places[] = $body[$k];
            }
        }
        $names = array(
            'intokens'  => array('input_tokens', 'prompt_tokens', 'inputTokens', 'promptTokens'),
            'outtokens' => array('output_tokens', 'completion_tokens', 'outputTokens', 'completionTokens'),
        );
        $found = array();
        foreach ($places as $place) {
            foreach ($names as $side => $keys) {
                foreach ($keys as $k) {
                    if (!isset($found[$side]) && isset($place[$k]) && is_numeric($place[$k])) {
                        $found[$side] = (int) $place[$k];
                    }
                }
            }
        }
        return $found;
    }

    /** The balances a Zywrap answer reports, so the screens stay current. */
    public static function noteCredits($body) {
        if (isset($body['cost']['credits_remaining']) && is_numeric($body['cost']['credits_remaining'])) {
            $account = get_option('wpjobportal_zywrap_account');
            if (!is_array($account)) {
                $account = array('email' => '', 'key_name' => '', 'connected_at' => 0);
            }
            $account['credits'] = (int) floor((float) $body['cost']['credits_remaining']);
            $account['credits_at'] = time();
            update_option('wpjobportal_zywrap_account', $account, false);
        }
        if (isset($body['cost']['plan_credits_remaining']) && class_exists('WPJOBPORTAL_AI_Credits')) {
            WPJOBPORTAL_AI_Credits::noteRemaining($body['cost']['plan_credits_remaining']);
        }
    }

    /* ------------------------------------------------------------------ *
     * Anthropic
     * ------------------------------------------------------------------ */

    /**
     * One Messages API request. Effort is low for these short writing jobs;
     * max_tokens never goes below 1024 because thinking spends from the same
     * allowance. A schema asks for structured output.
     */
    public static function buildAnthropic($call, $model, $key) {
        $output = array('effort' => isset($call['effort']) ? (string) $call['effort'] : 'low');
        if (!empty($call['schema'])) {
            $output['format'] = array('type' => 'json_schema', 'schema' => self::strictSchema($call['schema']));
        }
        $body = array(
            'model'         => $model,
            'max_tokens'    => max(1024, isset($call['maxtokens']) ? (int) $call['maxtokens'] : 4000),
            'system'        => isset($call['system']) ? (string) $call['system'] : '',
            'messages'      => array(array('role' => 'user', 'content' => (string) $call['prompt'])),
            'output_config' => $output,
        );
        $headers = array(
            'x-api-key'         => $key,
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        );
        // A safety refusal is answered on another model instead (not on Haiku,
        // which has no server-side fallback). Filterable for sites that would
        // rather not depend on a beta header.
        if (strpos((string) $model, 'claude-haiku') !== 0 && apply_filters('wpjobportal_ai_use_fallbacks', true)) {
            $body['fallbacks'] = 'default';
            $headers['anthropic-beta'] = 'server-side-fallback-2026-07-01';
        }
        return array('headers' => $headers, 'body' => $body);
    }

    /**
     * The subset of JSON Schema structured output accepts: no length or range
     * constraints, every object closed, every property required.
     */
    private static function strictSchema($schema) {
        if (!is_array($schema)) {
            return $schema;
        }
        foreach (array('minimum', 'maximum', 'exclusiveMinimum', 'exclusiveMaximum', 'multipleOf', 'minLength', 'maxLength', 'pattern', 'maxItems', 'format') as $k) {
            unset($schema[$k]);
        }
        if (isset($schema['minItems']) && (int) $schema['minItems'] > 1) {
            unset($schema['minItems']);
        }
        if (isset($schema['properties']) && is_array($schema['properties'])) {
            foreach ($schema['properties'] as $name => $sub) {
                $schema['properties'][$name] = self::strictSchema($sub);
            }
        }
        if (isset($schema['items']) && is_array($schema['items'])) {
            $schema['items'] = self::strictSchema($schema['items']);
        }
        $type = isset($schema['type']) ? $schema['type'] : '';
        if ($type === 'object' || isset($schema['properties'])) {
            $schema['additionalProperties'] = false;
            $names = (isset($schema['properties']) && is_array($schema['properties'])) ? array_map('strval', array_keys($schema['properties'])) : array();
            if (empty($names)) {
                $schema['properties'] = new stdClass();
            } else {
                $schema['required'] = $names;
            }
        }
        return $schema;
    }

    /** A refusal and a cut-off answer both arrive as HTTP 200, so both are checked. */
    public static function readAnthropic($body) {
        $usage = array();
        if (isset($body['usage']['input_tokens'])) {
            $usage['intokens'] = (int) $body['usage']['input_tokens'];
        }
        if (isset($body['usage']['output_tokens'])) {
            $usage['outtokens'] = (int) $body['usage']['output_tokens'];
        }
        $stop = isset($body['stop_reason']) ? (string) $body['stop_reason'] : '';
        if ($stop === 'refusal') {
            return new WP_Error('wpjobportal_ai_refused', __('The model declined to write this one. Nothing was changed.', 'wp-job-portal'), $usage);
        }
        $text = '';
        foreach ((array) (isset($body['content']) ? $body['content'] : array()) as $block) {
            if (isset($block['type']) && $block['type'] === 'text' && isset($block['text'])) {
                $text .= $block['text'];
            }
        }
        $text = trim($text);
        if ($text === '') {
            return new WP_Error('wpjobportal_ai_empty', __('The model returned nothing. Try again.', 'wp-job-portal'), $usage);
        }
        return array_merge(array(
            'text'      => $text,
            'truncated' => ($stop === 'max_tokens'),
            'model'     => isset($body['model']) ? (string) $body['model'] : '',
        ), $usage);
    }

    /* ------------------------------------------------------------------ *
     * OpenAI
     * ------------------------------------------------------------------ */

    /**
     * One Chat Completions request for OpenAI's reasoning models: instructions
     * in a developer message, the cap includes the reasoning, nothing stored.
     */
    public static function buildOpenAi($call, $model, $key) {
        $messages = array();
        if (!empty($call['system'])) {
            $messages[] = array('role' => 'developer', 'content' => (string) $call['system']);
        }
        $messages[] = array('role' => 'user', 'content' => (string) $call['prompt']);

        $effort = isset($call['effort']) ? (string) $call['effort'] : 'low';
        if (!in_array($effort, array('low', 'medium', 'high'), true)) {
            $effort = 'low';
        }
        $visible = isset($call['maxtokens']) ? max(1, (int) $call['maxtokens']) : 4000;
        $body = array(
            'model'                 => $model,
            'messages'              => $messages,
            'max_completion_tokens' => min(32000, $visible + 4000),
            'reasoning_effort'      => $effort,
            'store'                 => false,
        );
        if (!empty($call['schema'])) {
            $body['response_format'] = array(
                'type'        => 'json_schema',
                'json_schema' => array('name' => 'answer', 'strict' => true, 'schema' => self::strictSchema($call['schema'])),
            );
        }
        return array(
            'headers' => array('authorization' => 'Bearer ' . trim((string) $key), 'content-type' => 'application/json'),
            'body'    => $body,
        );
    }

    public static function readOpenAi($body) {
        $usage = array();
        if (isset($body['usage']['prompt_tokens'])) {
            $usage['intokens'] = (int) $body['usage']['prompt_tokens'];
        }
        if (isset($body['usage']['completion_tokens'])) {
            $usage['outtokens'] = (int) $body['usage']['completion_tokens'];
        }
        $choice = isset($body['choices'][0]) ? $body['choices'][0] : array();
        $finish = isset($choice['finish_reason']) ? (string) $choice['finish_reason'] : '';
        if (!empty($choice['message']['refusal']) || $finish === 'content_filter') {
            return new WP_Error('wpjobportal_ai_refused', __('The model declined to write this one. Nothing was changed.', 'wp-job-portal'), $usage);
        }
        $text = isset($choice['message']['content']) ? trim((string) $choice['message']['content']) : '';
        if ($text === '') {
            return new WP_Error('wpjobportal_ai_empty', __('The model returned nothing. Try again.', 'wp-job-portal'), $usage);
        }
        return array_merge(array(
            'text'      => $text,
            'truncated' => ($finish === 'length'),
            'model'     => isset($body['model']) ? (string) $body['model'] : '',
        ), $usage);
    }

    /* ------------------------------------------------------------------ *
     * Test
     * ------------------------------------------------------------------ */

    /**
     * Say one word back, through ask(), so a green tick means the path a real
     * request takes works. Zywrap needs a wrapper, so it borrows one.
     */
    public static function selfTest() {
        $started = microtime(true);
        // Zywrap checks its keys for free: no AI call and no credits.
        if (self::currentId() === 'zywrap') {
            $response = wp_remote_post(self::ZYWRAP_CHECK, array(
                'timeout'     => 20,
                'redirection' => 0,
                'headers'     => array('Content-Type' => 'application/json'),
                'body'        => wp_json_encode(array('apiKey' => self::apiKey('zywrap'))),
            ));
            $ms = (int) round((microtime(true) - $started) * 1000);
            if (is_wp_error($response)) {
                return array('ok' => false, 'ms' => $ms, 'detail' => sprintf(
                    /* translators: %s: the network error the request failed with */
                    __('Could not reach Zywrap: %s', 'wp-job-portal'),
                    $response->get_error_message()
                ));
            }
            $code = (int) wp_remote_retrieve_response_code($response);
            $json = json_decode((string) wp_remote_retrieve_body($response), true);
            $state = (is_array($json) && isset($json['status'])) ? strtolower((string) $json['status']) : '';
            if ($code === 200 && $state === 'limited') {
                return array('ok' => false, 'ms' => $ms, 'detail' => __('The Zywrap credits have run out. An administrator can add credits on zywrap.com.', 'wp-job-portal'));
            }
            if ($code === 200 && $state === 'ok') {
                return array('ok' => true, 'ms' => $ms, 'detail' => 'ready');
            }
            if ($code === 401 || $code === 403 || $state === 'invalid') {
                return array('ok' => false, 'ms' => $ms, 'detail' => __('Zywrap rejected this site\'s key. An administrator can connect Zywrap again under WP Job Portal > AI > Settings.', 'wp-job-portal'));
            }
            return array('ok' => false, 'ms' => $ms, 'detail' => sprintf(
                /* translators: %d: HTTP status code */
                __('Zywrap sent an answer this could not read (HTTP %d).', 'wp-job-portal'),
                $code
            ));
        }
        $answer = self::ask(array(
            'system'    => 'You are a connection test. Answer with one word.',
            'prompt'    => 'Reply with the single word: ready',
            'maxtokens' => 200,
            'effort'    => 'low',
            'feature'   => 'test',
            'zywrap'    => array('wrapper' => 'hjd_job_title_optimizer_base'),
        ));
        $ms = (int) round((microtime(true) - $started) * 1000);
        if (is_wp_error($answer)) {
            return array('ok' => false, 'ms' => $ms, 'detail' => $answer->get_error_message());
        }
        return array('ok' => true, 'ms' => $ms, 'detail' => wp_trim_words((string) $answer['text'], 12, '...'));
    }
}
