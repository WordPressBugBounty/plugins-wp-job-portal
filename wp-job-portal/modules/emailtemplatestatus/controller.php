<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALemailtemplatestatusController {

    function __construct() {

        self::handleRequest();
    }

    function handleRequest() {
        $wpjobportal_layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'emailtemplatestatus');
        if (self::canaddfile($wpjobportal_layout)) {
            switch ($wpjobportal_layout) {
                case 'admin_emailtemplatestatus':
                    WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getEmailTemplateStatusData();
                    break;
                default:
                    return;
            }
            $wpjobportal_module = (wpjobportal::$_common->wpjp_isadmin()) ? 'page' : 'wpjobportalme';
            $wpjobportal_module = WPJOBPORTALrequest::getVar($wpjobportal_module, null, 'emailtemplatestatus');
            $wpjobportal_module = wpjobportalphplib::wpJP_str_replace('wpjobportal_', '', $wpjobportal_module);
            wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
            WPJOBPORTALincluder::include_file($wpjobportal_layout, $wpjobportal_module);
        }
    }

    function sendEmail() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_emailstatus_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_id = WPJOBPORTALrequest::getVar('wpjobportalid');
        $wpjobportal_action = WPJOBPORTALrequest::getVar('actionfor');
        WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->sendEmailModel($wpjobportal_id, $wpjobportal_action); //  for send email
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_emailtemplatestatus"));
        wp_redirect($wpjobportal_url);
        die();
    }

    function noSendEmail() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_emailstatus_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_id = WPJOBPORTALrequest::getVar('wpjobportalid');
        $wpjobportal_action = WPJOBPORTALrequest::getVar('actionfor');
        WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->noSendEmailModel($wpjobportal_id, $wpjobportal_action); //  for notsendemail
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_emailtemplatestatus"));
        wp_redirect($wpjobportal_url);
        die();
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

$WPJOBPORTALEmailtemplatestatusController = new WPJOBPORTALEmailtemplatestatusController();
?>
