<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALCronJobModel {

function getCronKey() {
        if (!current_user_can('manage_options')) {
            return false;
        }

        $query = "SELECT configvalue FROM `" . wpjobportal::$_db->prefix . "wj_portal_config` WHERE configname = %s LIMIT 1";
        $key = wpjobportaldb::get_var(wpjobportaldb::prepare($query, 'cron_job_alert_key'));
        if (!is_string($key) || $key === '') {
            return false;
        }

        wpjobportal::$_data[0]['ck'] = $key;
        return $key;
    }
}
?>
