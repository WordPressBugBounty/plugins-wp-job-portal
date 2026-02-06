<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALformhandler {

    function __construct() {
        add_action('init', array($this, 'checkFormRequest'));
        add_action('init', array($this, 'checkDeleteRequest'));
    }

    /*
     * Handle Form request
     */

    function checkFormRequest() {
        $wpjobportal_formrequest = WPJOBPORTALrequest::getVar('form_request', 'post');
        if ($wpjobportal_formrequest == 'wpjobportal') {
            //handle the request
            $wpjobportal_modulename = (is_admin()) ? 'page' : 'wpjobportalme';
            $wpjobportal_module = WPJOBPORTALrequest::getVar($wpjobportal_modulename);
            $wpjobportal_module = wpjobportalphplib::wpJP_str_replace('wpjobportal_', '', $wpjobportal_module);
            wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
            WPJOBPORTALincluder::include_file($wpjobportal_module);
            $wpjobportal_class = 'WPJOBPORTAL' . $wpjobportal_module . "Controller";
            $wpjobportal_task = WPJOBPORTALrequest::getVar('task');
            $obj = new $wpjobportal_class;
            $obj->$wpjobportal_task();
        }
    }

    /*
     * Handle Form request
     */

    function checkDeleteRequest() {
        $wpjobportal_action = WPJOBPORTALrequest::getVar('action', 'get');
        if ($wpjobportal_action == 'wpjobportaltask') {
            //handle the request
            $wpjobportal_modulename = (is_admin()) ? 'page' : 'wpjobportalme';
            $wpjobportal_module = WPJOBPORTALrequest::getVar($wpjobportal_modulename);
            $wpjobportal_module = wpjobportalphplib::wpJP_str_replace('wpjobportal_', '', $wpjobportal_module);
            wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
            WPJOBPORTALincluder::include_file($wpjobportal_module);
            $wpjobportal_class = 'WPJOBPORTAL' . $wpjobportal_module . "Controller";
            $wpjobportal_action = WPJOBPORTALrequest::getVar('task');
            $obj = new $wpjobportal_class;
            $obj->$wpjobportal_action();
        }
    }

}

$wpjobportal_formhandler = new WPJOBPORTALformhandler();
?>
