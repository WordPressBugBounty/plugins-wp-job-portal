<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

class WPJOBPORTALsystemstatusController {

    public function __construct() {
        $this->handleRequest();
    }

    private function handleRequest() {
        if (!is_admin() || !current_user_can('wpjobportal')) {
            wp_die(esc_html__('You are not allowed to view WP Job Portal diagnostics.', 'wp-job-portal'));
        }

        $layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'systemstatus');
        if ($layout !== 'admin_systemstatus') {
            return;
        }

        WPJOBPORTALincluder::getJSModel('systemstatus')->getSystemStatus();
        WPJOBPORTALincluder::include_file($layout, 'systemstatus');
    }
}

new WPJOBPORTALsystemstatusController();
