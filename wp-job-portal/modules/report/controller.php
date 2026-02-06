<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALReportController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $wpjobportal_layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'reports');
        if (self::canaddfile($wpjobportal_layout)) {
            switch ($wpjobportal_layout) {
                case 'admin_overallreports':
                    WPJOBPORTALincluder::getJSModel('report')->getOverallReports();
                    break;
                default:
                    return;
            }
            $wpjobportal_module = (wpjobportal::$_common->wpjp_isadmin()) ? 'page' : 'wpjobportalme';
            $wpjobportal_module = WPJOBPORTALrequest::getVar($wpjobportal_module, null, 'report');
            $wpjobportal_module = wpjobportalphplib::wpJP_str_replace('wpjobportal_', '', $wpjobportal_module);
            wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
            WPJOBPORTALincluder::include_file($wpjobportal_layout, $wpjobportal_module);
        }
    }

    function canaddfile($wpjobportal_layout) {
        $wpjobportal_nonce_value = WPJOBPORTALrequest::getVar('wpjobportal_nonce');
        if ( wp_verify_nonce( $wpjobportal_nonce_value, 'wpjobportal_nonce') ) {
            if (isset($_POST['form_request']) && $_POST['form_request'] == 'wpjobportal')
                return false;
            elseif (isset($_GET['action']) && $_GET['action'] == 'wpjobportaltask')
                return false;
            else{
                if(!is_admin() && strpos($wpjobportal_layout, 'admin_') === 0){
                    return false;
                }
                return true;
            }
        }

    }

}

$WPJOBPORTALReportController = new WPJOBPORTALReportController();
?>
