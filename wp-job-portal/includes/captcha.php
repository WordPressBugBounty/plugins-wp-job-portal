<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALcaptcha {

    function getCaptchaForForm() {
        $wpjobportal_config_array = WPJOBPORTALincluder::getJSModel('configuration')->getConfigByFor('captcha');
        $wpjobportal_rand = $this->randomNumber();
        WPJOBPORTALincluder::getObjectClass('wpjpnotification')->addSessionNotificationDataToTable($wpjobportal_rand,'','wpjobportal_spamcheckid','captcha');
        $wpjobportal_rot13 = wp_rand(0, 1);
        WPJOBPORTALincluder::getObjectClass('wpjpnotification')->addSessionNotificationDataToTable($wpjobportal_rot13,'','wpjobportal_rot13','captcha');
        $operator = 2;
        if ($operator == 2) {
            $tcalc = $wpjobportal_config_array['owncaptcha_calculationtype'];
        }
        $wpjobportal_max_value = 20;
        $wpjobportal_negativ = 1;
        $operend_1 = wp_rand($wpjobportal_negativ, $wpjobportal_max_value);
        $operend_2 = wp_rand($wpjobportal_negativ, $wpjobportal_max_value);
        $operand = $wpjobportal_config_array['owncaptcha_totaloperand'];
        if ($operand == 3) {
            $operend_3 = wp_rand($wpjobportal_negativ, $wpjobportal_max_value);
        }

        if ($wpjobportal_config_array['owncaptcha_calculationtype'] == 2) { // Subtraction
            if ($wpjobportal_config_array['owncaptcha_subtractionans'] == 1) {
                $ans = $operend_1 - $operend_2;
                if ($ans < 0) {
                    $one = $operend_2;
                    $operend_2 = $operend_1;
                    $operend_1 = $one;
                }
                if ($operand == 3) {
                    $ans = $operend_1 - $operend_2 - $operend_3;
                    if ($ans < 0) {
                        if ($operend_1 < $operend_2) {
                            $one = $operend_2;
                            $operend_2 = $operend_1;
                            $operend_1 = $one;
                        }
                        if ($operend_1 < $operend_3) {
                            $one = $operend_3;
                            $operend_3 = $operend_1;
                            $operend_1 = $one;
                        }
                    }
                }
            }
        }

        if ($tcalc == 0)
            $tcalc = wp_rand(1, 2);

        if ($tcalc == 1) { // Addition
            if ($wpjobportal_rot13 == 1) { // ROT13 coding
                if ($operand == 2) {
                    WPJOBPORTALincluder::getObjectClass('wpjpnotification')->addSessionNotificationDataToTable(wpjobportalphplib::wpJP_str_rot13(wpjobportalphplib::wpJP_safe_encoding($operend_1 + $operend_2)),'','wpjobportal_spamcheckresult','captcha');
                } elseif ($operand == 3) {
                    WPJOBPORTALincluder::getObjectClass('wpjpnotification')->addSessionNotificationDataToTable(wpjobportalphplib::wpJP_str_rot13(wpjobportalphplib::wpJP_safe_encoding($operend_1 + $operend_2 + $operend_3)),'','wpjobportal_spamcheckresult','captcha');
                }
            } else {
                if ($operand == 2) {
                    WPJOBPORTALincluder::getObjectClass('wpjpnotification')->addSessionNotificationDataToTable(wpjobportalphplib::wpJP_safe_encoding($operend_1 + $operend_2),'','wpjobportal_spamcheckresult','captcha');
                } elseif ($operand == 3) {
                    WPJOBPORTALincluder::getObjectClass('wpjpnotification')->addSessionNotificationDataToTable(wpjobportalphplib::wpJP_safe_encoding($operend_1 + $operend_2 + $operend_3),'','wpjobportal_spamcheckresult','captcha');
                }
            }
        } elseif ($tcalc == 2) { // Subtraction
            if ($wpjobportal_rot13 == 1) {
                if ($operand == 2) {
                    WPJOBPORTALincluder::getObjectClass('wpjpnotification')->addSessionNotificationDataToTable(wpjobportalphplib::wpJP_str_rot13(wpjobportalphplib::wpJP_safe_encoding($operend_1 - $operend_2)),'','wpjobportal_spamcheckresult','captcha');
                } elseif ($operand == 3) {
                    WPJOBPORTALincluder::getObjectClass('wpjpnotification')->addSessionNotificationDataToTable(wpjobportalphplib::wpJP_str_rot13(wpjobportalphplib::wpJP_safe_encoding($operend_1 - $operend_2 - $operend_3)),'','wpjobportal_spamcheckresult','captcha');
                }
            } else {
                if ($operand == 2) {
                    WPJOBPORTALincluder::getObjectClass('wpjpnotification')->addSessionNotificationDataToTable(wpjobportalphplib::wpJP_safe_encoding($operend_1 - $operend_2),'','wpjobportal_spamcheckresult','captcha');
                } elseif ($operand == 3) {
                    WPJOBPORTALincluder::getObjectClass('wpjpnotification')->addSessionNotificationDataToTable(wpjobportalphplib::wpJP_safe_encoding($operend_1 - $operend_2 - $operend_3),'','wpjobportal_spamcheckresult','captcha');
                }
            }
        }
        $wpjobportal_add_string = "";
        if (wpjobportal::$wpjobportal_theme_chk == 1) {
            $wpjobportal_add_string .= '<div class="wpj-jp-form-captcha" ><div class="wpj-jp-form-label" for="' . $wpjobportal_rand . '">';
        } else {
            $wpjobportal_add_string .= '<div class="wjportal-form-row wjportal-form-captcha" ><div class="wjportal-form-title" for="' . $wpjobportal_rand . '">';
        }

        if ($tcalc == 1) {
            if ($operand == 2) {
                $wpjobportal_add_string .= $operend_1 . ' ' . esc_html(__('Plus', 'wp-job-portal')) . ' ' . $operend_2 . ' ' . esc_html(__('Equals', 'wp-job-portal')) . ' ';
            } elseif ($operand == 3) {
                $wpjobportal_add_string .= $operend_1 . ' ' . esc_html(__('Plus', 'wp-job-portal')) . ' ' . $operend_2 . ' ' . esc_html(__('Plus', 'wp-job-portal')) . ' ' . $operend_3 . ' ' . esc_html(__('Equals', 'wp-job-portal')) . ' ';
            }
        } elseif ($tcalc == 2) {
            $converttostring = 0;
            if ($operand == 2) {
                $wpjobportal_add_string .= $operend_1 . ' ' . esc_html(__('Minus', 'wp-job-portal')) . ' ' . $operend_2 . ' ' . esc_html(__('Equals', 'wp-job-portal')) . ' ';
            } elseif ($operand == 3) {
                $wpjobportal_add_string .= $operend_1 . ' ' . esc_html(__('Minus', 'wp-job-portal')) . ' ' . $operend_2 . ' ' . esc_html(__('Minus', 'wp-job-portal')) . ' ' . $operend_3 . ' ' . esc_html(__('Equals', 'wp-job-portal')) . ' ';
            }
        }

        $wpjobportal_add_string .= '<font color="red">* </font></div>';
        $wpjobportal_class_prefix = "";
        if(wpjobportal::$wpjobportal_theme_chk == 1){
            $wpjobportal_class_prefix = 'wpj-jp';
        }

        $wpjobportal_add_string .= '<div class="wjportal-form-value"><input type="text" name="' . $wpjobportal_rand . '" id="' . $wpjobportal_rand . '" size="3" class="inputbox form-control wjportal-form-input-field '.$wpjobportal_class_prefix.'-input-field  ' . $wpjobportal_rand . '" value="" data-validation="required" /></div>';
        $wpjobportal_add_string .= '</div>';

        return $wpjobportal_add_string;
    }

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

    private function performChecks() {
        $wpjobportal_rot13 = WPJOBPORTALincluder::getObjectClass('wpjpnotification')->getNotificationDatabySessionId('wpjobportal_rot13',true);
        if ($wpjobportal_rot13 == 1) {
            $spamcheckresult = wpjobportalphplib::wpJP_str_rot13(WPJOBPORTALincluder::getObjectClass('wpjpnotification')->getNotificationDatabySessionId('wpjobportal_spamcheckresult',true));
        } else {
            $spamcheckresult = WPJOBPORTALincluder::getObjectClass('wpjpnotification')->getNotificationDatabySessionId('wpjobportal_spamcheckresult',true);
        }
        $spamcheckresult = wpjobportalphplib::wpJP_safe_decoding($spamcheckresult);


        $spamcheck = WPJOBPORTALincluder::getObjectClass('wpjpnotification')->getNotificationDatabySessionId('wpjobportal_spamcheckid',true);
        $spamcheck = WPJOBPORTALrequest::getVar($spamcheck, '', 'post');
        if (!is_numeric($spamcheckresult) || $spamcheckresult != $spamcheck) {
            return false; // Failed
        }
        return true;
    }

    function checkCaptchaUserForm() {
        if (!$this->performChecks())
            $return = 2;
        else
            $return = 1;
        return $return;
    }

}

?>
