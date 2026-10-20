<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * One place that decides how a form proves a person filled it in.
 *
 * There were two schemes before this, chosen by `captcha_selection`, and every
 * form in the product carried its own copy of the branch between them: render
 * one or the other, then verify one or the other. Twenty-four scripts enqueued
 * by hand, twenty-two files that knew what a captcha was.
 *
 * What each scheme actually did:
 *
 *   - The built-in one asked the visitor to add two numbers together. It is
 *     solved by any bot that can read, and it is an obstacle only to people -
 *     particularly anybody using a screen reader, since the sum was rendered as
 *     an image. It is replaced here, not kept as an option.
 *
 *   - Google reCAPTCHA verified the token and looked at `success`. For v2 that
 *     is right. For v3 it is close to meaningless: v3 answers `success` for
 *     virtually every request and reports its judgement as a *score*, which
 *     nothing read. A site set to v3 believed it was protected and was not.
 *
 * Three providers now, all behind the same two calls - render() and verify():
 *
 *   1 reCAPTCHA  v2 checkbox or v3 score, and v3 now checks the score and the
 *                action it was issued for.
 *   2 Built-in   No puzzle. A honeypot field and a signed timestamp, which cost
 *                a person nothing and catch the bots that fill every field in a
 *                form and post it instantly.
 *   3 Turnstile  Cloudflare. Free, no puzzle for the visitor in the common
 *                case, and no tracking cookie.
 *
 * The numbering is not cosmetic: 1 and 2 are what customers already have
 * stored, so an existing site keeps working and a site set to 2 quietly stops
 * asking people to do arithmetic.
 */
class WPJOBPORTAL_Captcha_Service {

    const PROVIDER_RECAPTCHA = 1;
    const PROVIDER_BUILTIN = 2;
    const PROVIDER_TURNSTILE = 3;

    /** How long a form may sit before its signed timestamp is stale. */
    const FORM_MAX_AGE = 7200;

    /** Below this, nothing human filled the form in. */
    const FORM_MIN_AGE = 2;

    /** Default v3 score below which a request is treated as automated. */
    const DEFAULT_SCORE_THRESHOLD = 0.5;

    /** @var array|null Cached captcha configuration. */
    private static $config = null;

    /**
     * The captcha settings, read once.
     *
     * @return array
     */
    private static function config() {
        if (self::$config === null) {
            $config = WPJOBPORTALincluder::getJSModel('configuration')->getConfigByFor('captcha');
            self::$config = is_array($config) ? $config : array();
        }

        return self::$config;
    }

    /**
     * One setting, with a fallback.
     *
     * @param string $name    Setting name.
     * @param mixed  $default Value when it has never been set.
     * @return mixed
     */
    private static function setting($name, $default = '') {
        $config = self::config();

        return isset($config[$name]) && $config[$name] !== '' ? $config[$name] : $default;
    }

    /**
     * Which provider this site is set to.
     *
     * @return int
     */
    public static function provider() {
        $provider = (int) self::setting('captcha_selection', self::PROVIDER_BUILTIN);

        if (!in_array($provider, array(self::PROVIDER_RECAPTCHA, self::PROVIDER_BUILTIN, self::PROVIDER_TURNSTILE), true)) {
            $provider = self::PROVIDER_BUILTIN;
        }

        /**
         * Filter the captcha provider.
         *
         * @param int $provider One of the PROVIDER_ constants.
         */
        return (int) apply_filters('wpjobportal_captcha_provider', $provider);
    }

    /**
     * The provider that will actually be used.
     *
     * A third-party provider with no keys protects nothing and, worse, renders
     * a widget that never resolves - so the form cannot be submitted at all.
     * The built-in checks need no keys and no network, so that is what an
     * unconfigured site falls back to.
     *
     * @return int
     */
    public static function effective_provider() {
        $provider = self::provider();

        if ($provider === self::PROVIDER_BUILTIN) {
            return $provider;
        }

        return self::keys_present($provider) ? $provider : self::PROVIDER_BUILTIN;
    }

    /**
     * Are both keys set for a provider?
     *
     * @param int $provider Provider constant.
     * @return bool
     */
    public static function keys_present($provider) {
        return self::site_key($provider) !== '' && self::secret_key($provider) !== '';
    }

    /**
     * The public key the widget is rendered with.
     *
     * @param int|null $provider Provider, or the current one.
     * @return string
     */
    public static function site_key($provider = null) {
        $provider = $provider === null ? self::provider() : (int) $provider;

        return trim((string) ($provider === self::PROVIDER_TURNSTILE
            ? self::setting('turnstile_sitekey')
            : self::setting('recaptcha_publickey')));
    }

    /**
     * The secret the response is verified with.
     *
     * @param int|null $provider Provider, or the current one.
     * @return string
     */
    public static function secret_key($provider = null) {
        $provider = $provider === null ? self::provider() : (int) $provider;

        return trim((string) ($provider === self::PROVIDER_TURNSTILE
            ? self::setting('turnstile_secretkey')
            : self::setting('recaptcha_privatekey')));
    }

    /**
     * Is reCAPTCHA configured as v3?
     *
     * @return bool
     */
    public static function is_recaptcha_v3() {
        return (int) self::setting('recaptcha_version', 1) === 2;
    }

    /**
     * The score below which a v3 response is treated as automated.
     *
     * @return float
     */
    public static function score_threshold() {
        $threshold = (float) self::setting('recaptcha_score_threshold', self::DEFAULT_SCORE_THRESHOLD);

        if ($threshold <= 0 || $threshold >= 1) {
            $threshold = self::DEFAULT_SCORE_THRESHOLD;
        }

        /**
         * Filter the reCAPTCHA v3 score threshold.
         *
         * @param float $threshold Between 0 and 1.
         */
        return (float) apply_filters('wpjobportal_captcha_score_threshold', $threshold);
    }

    /* ------------------------------------------------------------------ */
    /* Rendering                                                           */
    /* ------------------------------------------------------------------ */

    /**
     * The markup a form needs, for whichever provider is in use.
     *
     * @param string $context What the form is for, e.g. 'register', 'jobapply'.
     *                        Carried into v3 as the action, so a token issued
     *                        for one form cannot be replayed against another.
     * @return string
     */
    public static function render($context = 'form') {
        $context = sanitize_key($context);

        switch (self::effective_provider()) {
            case self::PROVIDER_TURNSTILE:
                return self::render_turnstile($context);

            case self::PROVIDER_RECAPTCHA:
                return self::render_recaptcha($context);
        }

        return self::render_builtin($context);
    }

    /**
     * Echo what render() returns, for templates that expect to print.
     *
     * @param string $context Form context.
     * @return void
     */
    public static function output($context = 'form') {
        echo self::render($context); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts below.
    }

    /**
     * Load the current provider's script.
     *
     * render() calls this for a form in the page. A form that arrives later by
     * AJAX - the Tell a Friend popup - needs the script on the page before it
     * opens, so the page that offers the popup calls it too.
     *
     * @return void
     */
    public static function enqueue_script() {
        switch (self::effective_provider()) {
            case self::PROVIDER_TURNSTILE:
                // phpcs:ignore PluginCheck.CodeAnalysis.EnqueuedResourceOffloading.OffloadedContent -- Cloudflare Turnstile only works from Cloudflare's own script.
                wp_enqueue_script( 'wpjobportal-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', array(), WPJOBPORTAL_VERSION, true );
                break;

            case self::PROVIDER_RECAPTCHA:
                if (self::is_recaptcha_v3()) {
                    wp_enqueue_script( 'wpjobportal-recaptcha', 'https://www.google.com/recaptcha/api.js?render=' . rawurlencode(self::site_key()), array(), WPJOBPORTAL_VERSION, true );
                } else {
                    wp_enqueue_script( 'wpjobportal-recaptcha', 'https://www.google.com/recaptcha/api.js', array(), WPJOBPORTAL_VERSION, true );
                }
                break;
        }
    }

    /**
     * Cloudflare Turnstile.
     *
     * @param string $context Form context.
     * @return string
     */
    private static function render_turnstile($context) {
        self::enqueue_script();

        return '<div class="wpjp-captcha wpjp-captcha-turnstile cf-turnstile"'
            . ' data-sitekey="' . esc_attr(self::site_key()) . '"'
            . ' data-action="' . esc_attr($context) . '"'
            . ' data-theme="auto"></div>';
    }

    /**
     * Google reCAPTCHA, v2 checkbox or v3.
     *
     * @param string $context Form context.
     * @return string
     */
    private static function render_recaptcha($context) {
        $site_key = self::site_key();

        if (!self::is_recaptcha_v3()) {
            self::enqueue_script();

            return '<div class="wpjp-captcha wpjp-captcha-recaptcha g-recaptcha"'
                . ' data-sitekey="' . esc_attr($site_key) . '"></div>';
        }

        // v3 has nothing to show. It needs a token fetched for this action and
        // put into the form before it is submitted.
        self::enqueue_script();

        $field_id = 'wpjp-recaptcha-' . $context . '-' . wp_rand(1000, 9999);

        $script = 'window.addEventListener("load",function(){'
            . 'if(typeof grecaptcha==="undefined"){return;}'
            . 'grecaptcha.ready(function(){'
            . 'var f=document.getElementById(' . wp_json_encode($field_id) . ');'
            . 'if(!f){return;}'
            . 'var fill=function(){grecaptcha.execute(' . wp_json_encode($site_key) . ',{action:' . wp_json_encode($context) . '})'
            . '.then(function(t){f.value=t;});};'
            . 'fill();'
            // A v3 token expires after two minutes, and somebody filling in a
            // job application takes longer than that. Refreshing keeps the
            // token valid for as long as the form is open.
            . 'setInterval(fill,100000);'
            . '});});';

        wp_add_inline_script('wpjobportal-recaptcha', $script);

        // The site key and action are repeated on the field for a form that arrives by AJAX, where the
        // inline script above never runs (the Tell a Friend popup fetches its own token from them).
        return '<input type="hidden" id="' . esc_attr($field_id) . '" name="g-recaptcha-response" value=""'
            . ' data-sitekey="' . esc_attr($site_key) . '" data-action="' . esc_attr($context) . '" />';
    }

    /**
     * The built-in checks: a honeypot and a signed timestamp.
     *
     * No puzzle, nothing to read, nothing for a person to do. What it catches
     * is the common case - a script that fills in every field it finds and
     * posts the form immediately - without ever standing between a visitor and
     * the thing they came to do.
     *
     * @param string $context Form context.
     * @return string
     */
    private static function render_builtin($context) {
        $issued = time();

        // Hidden from people by position rather than by type="hidden", which a
        // form-filling script skips, and marked so assistive technology ignores
        // it as well.
        return '<div class="wpjp-captcha wpjp-captcha-builtin" aria-hidden="true"'
            . ' style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;">'
            . '<label>' . esc_html__('Leave this field empty', 'wp-job-portal')
            . '<input type="text" name="' . esc_attr(self::honeypot_field()) . '" value="" tabindex="-1" autocomplete="off" />'
            . '</label>'
            . '</div>'
            . '<input type="hidden" name="wpjp_form_issued" value="' . esc_attr($issued) . '" />'
            . '<input type="hidden" name="wpjp_form_token" value="' . esc_attr(self::timestamp_token($issued, $context)) . '" />'
            . '<input type="hidden" name="wpjp_form_context" value="' . esc_attr($context) . '" />';
    }

    /**
     * The honeypot's field name.
     *
     * Derived from the site's own salt so it is stable for a site - the form
     * and the check have to agree - but not the same string on every WP Job
     * Portal install, which is what would let one script recognise it
     * everywhere.
     *
     * @return string
     */
    public static function honeypot_field() {
        return 'wpjp_' . substr(wp_hash('wpjobportal_captcha_honeypot'), 0, 12);
    }

    /**
     * A signature over when a form was issued.
     *
     * @param int    $issued  Unix time.
     * @param string $context Form context.
     * @return string
     */
    private static function timestamp_token($issued, $context) {
        return wp_hash('wpjobportal_captcha|' . (int) $issued . '|' . $context);
    }

    /* ------------------------------------------------------------------ */
    /* Verifying                                                           */
    /* ------------------------------------------------------------------ */

    /**
     * Did this submission come from a person?
     *
     * @param string $context Form context, matching the one render() was given.
     * @return true|WP_Error True, or why not. The error code distinguishes a
     *                       failed check from a verification service that could
     *                       not be reached, because those need different words
     *                       in front of the visitor.
     */
    public static function verify($context = 'form') {
        $context = sanitize_key($context);

        switch (self::effective_provider()) {
            case self::PROVIDER_TURNSTILE:
                return self::verify_remote(
                    'https://challenges.cloudflare.com/turnstile/v0/siteverify', // phpcs:ignore PluginCheck.CodeAnalysis.Offloading.OffloadedContent -- the captcha's verification API, not an offloaded asset.
                    (string) WPJOBPORTALrequest::getVar('cf-turnstile-response', 'post'),
                    $context
                );

            case self::PROVIDER_RECAPTCHA:
                return self::verify_remote(
                    'https://www.google.com/recaptcha/api/siteverify',
                    (string) WPJOBPORTALrequest::getVar('g-recaptcha-response', 'post'),
                    $context
                );
        }

        return self::verify_builtin($context);
    }

    /**
     * The message to show a visitor when verify() said no.
     *
     * @param WP_Error $error What verify() returned.
     * @return string
     */
    public static function error_message($error) {
        if (!is_wp_error($error)) {
            return '';
        }

        if ($error->get_error_code() === 'captcha_unavailable') {
            return esc_html__('The spam check could not be completed just now. Please try again in a moment.', 'wp-job-portal');
        }

        return esc_html__('The spam check did not pass. Please try again.', 'wp-job-portal');
    }

    /**
     * Ask a provider whether a token is good.
     *
     * @param string $endpoint Verification URL.
     * @param string $response The token the widget produced.
     * @param string $context  Form context, checked against v3's action.
     * @return true|WP_Error
     */
    private static function verify_remote($endpoint, $response, $context) {
        $response = trim($response);

        // No token at all is a failed check and not worth a round trip - the
        // old code posted an empty string to Google and waited for the answer.
        if ($response === '') {
            return new WP_Error('captcha_missing', __('No spam-check response was submitted.', 'wp-job-portal'));
        }

        $body = array(
            'secret' => self::secret_key(),
            'response' => $response,
        );

        $remote_ip = self::remote_ip();
        if ($remote_ip !== '') {
            $body['remoteip'] = $remote_ip;
        }

        $result = wp_remote_post($endpoint, array(
            'body' => $body,
            'timeout' => 10,
            'sslverify' => function_exists('wpjobportal_sslverify') ? wpjobportal_sslverify($endpoint) : true,
        ));

        // Unreachable is not the same as failed, and the two used to be
        // indistinguishable: an outage at the provider made every form on the
        // site tell people their answer was wrong.
        if (is_wp_error($result)) {
            return new WP_Error('captcha_unavailable', $result->get_error_message());
        }

        if ((int) wp_remote_retrieve_response_code($result) !== 200) {
            return new WP_Error('captcha_unavailable', sprintf(
                /* translators: %s: HTTP status code. */
                __('The spam-check service answered with status %s.', 'wp-job-portal'),
                wp_remote_retrieve_response_code($result)
            ));
        }

        $decoded = json_decode(wp_remote_retrieve_body($result), true);
        if (!is_array($decoded)) {
            return new WP_Error('captcha_unavailable', __('The spam-check service sent an answer that could not be read.', 'wp-job-portal'));
        }

        if (empty($decoded['success'])) {
            $codes = isset($decoded['error-codes']) ? (array) $decoded['error-codes'] : array();

            // A wrong or missing secret is the site's fault, not the visitor's,
            // and it should be findable rather than blamed on whoever is trying
            // to apply for a job.
            if (array_intersect($codes, array('invalid-input-secret', 'missing-input-secret', 'invalid-keys'))) {
                self::log('Spam check rejected the site keys: ' . implode(', ', $codes));

                return new WP_Error('captcha_unavailable', __('The spam-check keys on this site are not valid.', 'wp-job-portal'));
            }

            return new WP_Error('captcha_failed', implode(', ', $codes));
        }

        // v3 answers success for almost anything and puts its judgement in the
        // score. Not reading it is the difference between a site that is
        // protected and one that only believes it is.
        if (self::effective_provider() === self::PROVIDER_RECAPTCHA && self::is_recaptcha_v3()) {
            if (isset($decoded['action']) && sanitize_key($decoded['action']) !== $context) {
                return new WP_Error('captcha_failed', __('The spam-check response was issued for a different form.', 'wp-job-portal'));
            }

            $score = isset($decoded['score']) ? (float) $decoded['score'] : 0.0;
            if ($score < self::score_threshold()) {
                return new WP_Error('captcha_failed', sprintf(
                    /* translators: 1: score returned, 2: threshold required. */
                    __('The spam check scored %1$s, below the %2$s this site requires.', 'wp-job-portal'),
                    $score,
                    self::score_threshold()
                ));
            }
        }

        return true;
    }

    /**
     * The honeypot and the timestamp.
     *
     * @param string $context Form context.
     * @return true|WP_Error
     */
    private static function verify_builtin($context) {
        $honeypot = (string) WPJOBPORTALrequest::getVar(self::honeypot_field(), 'post');
        if (trim($honeypot) !== '') {
            return new WP_Error('captcha_failed', __('A field that should have been left empty was filled in.', 'wp-job-portal'));
        }

        $issued = (int) WPJOBPORTALrequest::getVar('wpjp_form_issued', 'post');
        $token = (string) WPJOBPORTALrequest::getVar('wpjp_form_token', 'post');
        $posted_context = sanitize_key((string) WPJOBPORTALrequest::getVar('wpjp_form_context', 'post'));

        // A form that was never rendered by this site, or was rendered for a
        // different form, has no valid signature.
        if ($issued < 1 || !hash_equals(self::timestamp_token($issued, $posted_context), $token)) {
            return new WP_Error('captcha_failed', __('The form could not be verified. Please reload the page and try again.', 'wp-job-portal'));
        }

        if ($posted_context !== $context) {
            return new WP_Error('captcha_failed', __('The form could not be verified. Please reload the page and try again.', 'wp-job-portal'));
        }

        $elapsed = time() - $issued;

        if ($elapsed < self::FORM_MIN_AGE) {
            return new WP_Error('captcha_failed', __('That was submitted faster than a form can be filled in.', 'wp-job-portal'));
        }

        if ($elapsed > self::FORM_MAX_AGE) {
            return new WP_Error('captcha_failed', __('This form has been open too long. Please reload the page and try again.', 'wp-job-portal'));
        }

        return true;
    }

    /**
     * The visitor's address, if there is one to be had.
     *
     * @return string
     */
    private static function remote_ip() {
        if (empty($_SERVER['REMOTE_ADDR'])) {
            return '';
        }

        $ip = filter_var(wp_unslash($_SERVER['REMOTE_ADDR']), FILTER_VALIDATE_IP);

        return $ip === false ? '' : $ip;
    }

    /**
     * Record something an administrator would want to know.
     *
     * @param string $message What happened.
     * @return void
     */
    private static function log($message) {
        if (class_exists('WPJOBPORTAL_Audit_Service')) {
            WPJOBPORTAL_Audit_Service::record('captcha_configuration', 'failed', array('detail' => $message));
        }
    }
}
