<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALsystemerrorModel {

    function getSystemErrors() {
        $wpjobportal_inquery = '';
        // Pagination
        $query = "SELECT COUNT(`id`) FROM `" . wpjobportal::$_db->prefix . "wj_portal_system_errors`";
        $query .= $wpjobportal_inquery;
        $wpjobportal_total = wpjobportal::$_db->get_var($query);
        wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($wpjobportal_total);

        // Data
        $query = " SELECT systemerror.*
					FROM `" . wpjobportal::$_db->prefix . "wj_portal_system_errors` AS systemerror ";
        $query .= $wpjobportal_inquery;
        $query .= " ORDER BY systemerror.created DESC LIMIT " . WPJOBPORTALpagination::$_offset . ", " . WPJOBPORTALpagination::$_limit;
        wpjobportal::$_data[0] = wpjobportal::$_db->get_results($query);
        if (wpjobportal::$_db->last_error != null) {
            $this->addSystemError();
        }
        return;
    }


    function addSystemError($possible_error_msg = '') {
        // 1. Always grab the URL for context
        $error_data = array(
            'url' => isset($_SERVER['REQUEST_URI']) ? esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])) : 'Unknown URL'
        );

        // 2. If it's a custom error, skip the DB and backtrace stuff
        if ( $possible_error_msg !== '' ) {

            $error_data['error'] = $possible_error_msg;

        } else {
            // 3. Otherwise, it's an internal DB error: gather query, error, and trace
            $error_msg    = wpjobportal::$_db->last_error;
            $failed_query = wpjobportal::$_db->last_query;

            // Middle Ground Backtrace: Grab up to 5 levels
            $raw_backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
            $trace_path    = array();

            foreach ($raw_backtrace as $trace) {
                $func = isset($trace['function']) ? $trace['function'] : 'Unknown';
                $file = isset($trace['file']) ? basename($trace['file']) : 'Unknown';

                // Skip the addSystemError function itself
                if ($func !== 'addSystemError') {
                    $trace_path[] = "$func ($file)";
                }
            }

            $called_by_path = implode(' <- ', $trace_path);

            $error_data['error'] = $error_msg ? $error_msg : 'Unknown DB Error';
            $error_data['query'] = $failed_query ? $failed_query : 'No query recorded';
            $error_data['path']  = $called_by_path;
        }

        // 4. Prepare for insertion
        $query_array = array(
            'error'   => wp_json_encode($error_data),
            'uid'     => get_current_user_id(),
            'isview'  => 0,
            'created' => gmdate("Y-m-d H:i:s")
        );

        // Insert directly to avoid infinite loops from your table class
        wpjobportal::$_db->insert(wpjobportal::$_db->prefix . 'wj_portal_system_errors', $query_array);

        // CLEAR THE PREVIOUS ERROR TO PREVENT PERSISTENCE
        wpjobportal::$_db->last_error = '';
        wpjobportal::$_db->last_query = '';

        return;
    }

    function getMessagekey(){
        $wpjobportal_key = 'systemerror';if(wpjobportal::$_common->wpjp_isadmin()){$wpjobportal_key = 'admin_'.$wpjobportal_key;}return $wpjobportal_key;
    }
}
?>
