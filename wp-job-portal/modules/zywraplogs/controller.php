<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALzywraplogsController {

    private $_msgkey;

    function __construct() {
        self::handleRequest();
        $this->_msgkey = WPJOBPORTALincluder::getJSModel('zywraplogs')->getMessagekey();
    }

    function handleRequest() {

        $wpjobportal_layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'logs');
        
        if (self::canaddfile()) {
            switch ($wpjobportal_layout) {
                case 'admin_logs':
                case 'logs':
                    WPJOBPORTALincluder::getJSModel('zywraplogs')->wpjobportalGetLogsData();
                    break;
                default:
                    exit;
            }
            $wpjobportal_module = (wpjobportal::$_common->wpjp_isadmin()) ? 'page' : 'wpjobportalme';
            $wpjobportal_module = WPJOBPORTALrequest::getVar($wpjobportal_module, null, 'wpjobportal');
            $wpjobportal_module = wpjobportalphplib::wpJP_str_replace('wpjobportal_', '', $wpjobportal_module);
            if($wpjobportal_layout=="thankyou"){
                if($wpjobportal_module=="" || $wpjobportal_module!="wpjobportal") $wpjobportal_module="wpjobportal";
            }
            wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
            WPJOBPORTALincluder::include_file($wpjobportal_layout, $wpjobportal_module);
        }
    }

    function canaddfile() {
        $nonce_value = WPJOBPORTALrequest::getVar('wpjobportal_nonce');
        if ( wp_verify_nonce( $nonce_value, 'wpjobportal_nonce') ) {
            if (isset($_POST['form_request']) && $_POST['form_request'] == 'wpjobportal')
                return false;
            elseif (isset($_GET['action']) && $_GET['action'] == 'wpjobportaltask')
                return false;
            else
                return true;
        }
        return true;
    }
}
$WPJOBPORTALzywraplogsController = new WPJOBPORTALzywraplogsController();
?>