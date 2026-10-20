<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * The form spam check, as the rest of the product still asks for it.
 *
 * This class used to *be* the check: it generated an arithmetic question -
 * "what is 14 + 7?" - rendered it, kept the answer in the notification table
 * against the session, and compared what came back. That is solved by any bot
 * that can read a page, and it is an obstacle only to people. The operands were
 * also offered as an image, which makes it an obstacle to anybody using a
 * screen reader and none at all to optical character recognition.
 *
 * The two methods below are called from ten places across Core, so they stay,
 * and they now hand the work to WPJOBPORTAL_Captcha_Service. Every one of those
 * call sites gets the same three providers - Cloudflare Turnstile, Google
 * reCAPTCHA with its v3 score actually checked, and a built-in check that asks
 * the visitor for nothing - without any of them having to know that.
 *
 * The arithmetic is gone: nothing generates it and nothing stores an answer.
 *
 * @see WPJOBPORTAL_Captcha_Service
 */
class WPJOBPORTALcaptcha {

    /**
     * The markup a form needs.
     *
     * @param string $wpjobportal_context Which form this is, so a response
     *                                    issued for one cannot be replayed
     *                                    against another.
     * @return string
     */
    function getCaptchaForForm($wpjobportal_context = 'form') {
        if (!class_exists('WPJOBPORTAL_Captcha_Service')) {
            return '';
        }

        return WPJOBPORTAL_Captcha_Service::render($wpjobportal_context);
    }

    /**
     * Did this submission come from a person?
     *
     * Returns 1 and not true, because every caller compares against 1.
     *
     * @param string $wpjobportal_context Which form this is.
     * @return int 1 when the check passed, 2 when it did not.
     */
    function checkCaptchaUserForm($wpjobportal_context = 'form') {
        if (!class_exists('WPJOBPORTAL_Captcha_Service')) {
            return 1;
        }

        $wpjobportal_result = WPJOBPORTAL_Captcha_Service::verify($wpjobportal_context);

        if (is_wp_error($wpjobportal_result)) {
            // Somewhere a visitor can be told what actually happened. "Invalid
            // captcha" was the same sentence whether they had mistyped
            // something or the verification service was unreachable.
            wpjobportal::$_data['google_captchaerror'] = WPJOBPORTAL_Captcha_Service::error_message($wpjobportal_result);

            return 2;
        }

        return 1;
    }

    /**
     * A short random string.
     *
     * Nothing in the spam check needs this any more; it is kept because it is a
     * public method and something else may have reached for it.
     *
     * @return string
     */
    function randomNumber() {
        $pw = '';

        // first character has to be a letter
        $characters = range('a', 'z');
        $pw .= $characters[wp_rand(0, 25)];

        // other characters arbitrarily
        $wpjobportal_numbers = range(0, 9);
        $characters = array_merge($characters, $wpjobportal_numbers);

        $pw_length = wp_rand(4, 12);

        for ($wpjobportal_i = 0; $wpjobportal_i < $pw_length; $wpjobportal_i++) {
            $pw .= $characters[wp_rand(0, 35)];
        }
        return $pw;
    }
}
