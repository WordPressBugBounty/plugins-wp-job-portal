<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

class WPJOBPORTALsystemstatusModel {

    /**
     * Populate read-only diagnostics for the admin template.
     */
    public function getSystemStatus() {
        $status = WPJOBPORTAL_Diagnostics_Service::get_status();
        wpjobportal::$_data['systemstatus'] = $status;
        wpjobportal::$_data['systemstatus_text'] = WPJOBPORTAL_Diagnostics_Service::as_text($status);
    }

    /**
     * @return string
     */
    public function getMessagekey() {
        return 'admin_systemstatus';
    }
}
