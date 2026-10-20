<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * Verify a Google reCAPTCHA response.
 *
 * Called from five places in Core. What it used to do was post the token to
 * Google and return whether `success` came back true. For reCAPTCHA v2 that is
 * the whole answer. For v3 it is close to no answer at all: v3 replies
 * `success` for virtually every well-formed request and puts its actual
 * judgement in a `score` between 0 and 1, which this never read. A site that
 * had chosen v3 in the settings believed it was protected and was not.
 *
 * It also could not tell a failed check from an unreachable service, so an
 * outage at Google made every form in the product tell people their answer was
 * wrong; it computed an error message and then discarded it; and it posted an
 * empty token over the network rather than failing immediately.
 *
 * All of that now lives in WPJOBPORTAL_Captcha_Service, which also handles
 * Cloudflare Turnstile and the built-in check. This stays because those five
 * call sites still exist, and because a site may have its own code calling it.
 *
 * @param string $sharedkey  Secret key. Ignored: the service reads the key for
 *                           whichever provider the site is set to, and taking
 *                           one here would let a caller verify against the
 *                           wrong provider's secret.
 * @param string $grresponse The token. Ignored for the same reason - the
 *                           service reads the field its own provider posts.
 * @return bool
 * @see WPJOBPORTAL_Captcha_Service::verify()
 */
if (!function_exists('wpjobportal_googleRecaptchaHTTPPost')) {
    function wpjobportal_googleRecaptchaHTTPPost($sharedkey, $grresponse) {
        if (!class_exists('WPJOBPORTAL_Captcha_Service')) {
            return false;
        }

        $wpjobportal_result = WPJOBPORTAL_Captcha_Service::verify();

        if (is_wp_error($wpjobportal_result)) {
            wpjobportal::$_data['google_captchaerror'] = WPJOBPORTAL_Captcha_Service::error_message($wpjobportal_result);

            return false;
        }

        return true;
    }
}
