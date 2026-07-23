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
            $wpjobportal_module = sanitize_key(wpjobportalphplib::wpJP_str_replace('wpjobportal_', '', $wpjobportal_module));
            $wpjobportal_task = preg_replace('/[^A-Za-z0-9_]/', '', (string) WPJOBPORTALrequest::getVar('task'));
            if (empty($wpjobportal_module) || empty($wpjobportal_task)) {
                return;
            }

            wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
            WPJOBPORTALincluder::include_file($wpjobportal_module);
            $wpjobportal_class = 'WPJOBPORTAL' . $wpjobportal_module . "Controller";
            $wpjobportal_task = WPJOBPORTALrequest::getVar('task');

            if (!class_exists($wpjobportal_class) || !method_exists($wpjobportal_class, $wpjobportal_task)) {
                return;
            }


            $obj = new $wpjobportal_class;

            if (!method_exists($obj, $wpjobportal_task)) {
                return;
            }
            $wpjobportal_reflection = new ReflectionMethod($obj, $wpjobportal_task);
            if ($wpjobportal_reflection->getNumberOfRequiredParameters() > 0) {
                wp_die(esc_html__('Invalid request.', 'wp-job-portal'));
            }
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
            $wpjobportal_module = sanitize_key(wpjobportalphplib::wpJP_str_replace('wpjobportal_', '', $wpjobportal_module));
            $wpjobportal_action = preg_replace('/[^A-Za-z0-9_]/', '', (string) WPJOBPORTALrequest::getVar('task'));
            if (empty($wpjobportal_module) || empty($wpjobportal_action)) {
                return;
            }

            wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
            WPJOBPORTALincluder::include_file($wpjobportal_module);
            $wpjobportal_class = 'WPJOBPORTAL' . $wpjobportal_module . "Controller";
            $wpjobportal_action = WPJOBPORTALrequest::getVar('task');

            if (!class_exists($wpjobportal_class) || !method_exists($wpjobportal_class, $wpjobportal_action)) {
                return;
            }

            if (!class_exists($wpjobportal_class)) {
                return;
            }

            $obj = new $wpjobportal_class;

            if (!method_exists($obj, $wpjobportal_action)) {
                return;
            }
            $wpjobportal_reflection = new ReflectionMethod($obj, $wpjobportal_action);
            if ($wpjobportal_reflection->getNumberOfRequiredParameters() > 0) {
                wp_die(esc_html__('Invalid request.', 'wp-job-portal'));
            }
            $obj->$wpjobportal_action();
        }
    }

}

$wpjobportal_formhandler = new WPJOBPORTALformhandler();
?>
