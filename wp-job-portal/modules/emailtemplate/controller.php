<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALEmailtemplateController {

    private $_msgkey;

    function __construct() {
        self::handleRequest();
        $this->_msgkey = WPJOBPORTALincluder::getJSModel('emailtemplate')->getMessagekey();        
    }

    function handleRequest() {
        $wpjobportal_layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'emailtemplate');
        if (self::canaddfile($wpjobportal_layout)) {
            switch ($wpjobportal_layout) {
                case 'admin_emailtemplate':
                    $wpjobportal_tempfor = WPJOBPORTALrequest::getVar('for', null, 'ew-cm');
                    WPJOBPORTALincluder::getJSModel('emailtemplate')->getTemplate($wpjobportal_tempfor);
                    wpjobportal::$_data[1] = $wpjobportal_tempfor;
                    break;
                default:
                    return;
            }
            $wpjobportal_module = (wpjobportal::$_common->wpjp_isadmin()) ? 'page' : 'wpjobportalme';
            $wpjobportal_module = WPJOBPORTALrequest::getVar($wpjobportal_module, null, 'emailtemplate');
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

    function saveemailtemplate() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_email_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_data = WPJOBPORTALrequest::get('post');
        $wpjobportal_templatefor = $wpjobportal_data['templatefor'];
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('emailtemplate')->storeEmailTemplate($wpjobportal_data);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'emailtemplate');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);

        switch ($wpjobportal_templatefor) {
            case 'company-new' : $wpjobportal_tempfor = 'ew-cm';
                break;
            case 'company-delete' : $wpjobportal_tempfor = 'd-cm';
                break;
            case 'company-status' : $wpjobportal_tempfor = 'cm-sts';
                break;
            case 'company-rejecting' : $wpjobportal_tempfor = 'cm-rj';
                break;
            case 'job-new' : $wpjobportal_tempfor = 'ew-ob';
                break;
            case 'job-approval' : $wpjobportal_tempfor = 'ob-ap';
                break;
            case 'job-delete' : $wpjobportal_tempfor = 'ob-d';
                break;
            case 'resume-new' : $wpjobportal_tempfor = 'ew-rm';
                break;
            case 'message-email' : $wpjobportal_tempfor = 'ew-ms';
                break;
            case 'resume-approval' : $wpjobportal_tempfor = 'rm-ap';
                break;
            case 'resume-rejecting' : $wpjobportal_tempfor = 'rm-rj';
                break;
            case 'applied-resume_status' : $wpjobportal_tempfor = 'ap-rs';
                break;
            case 'jobapply-jobapply' : $wpjobportal_tempfor = 'ba-ja';
                break;
            case 'department-new' : $wpjobportal_tempfor = 'ew-md';
                break;
            case 'employer-buypackage' : $wpjobportal_tempfor = 'ew-rp';
                break;
            case 'jobseeker-buypackage' : $wpjobportal_tempfor = 'ew-js';
                break;
            case 'job-alert' : $wpjobportal_tempfor = 'jb-at';
                break;
            case 'job-alert-visitor' : $wpjobportal_tempfor = 'jb-at-vis';
                break;
            case 'job-to-friend' : $wpjobportal_tempfor = 'jb-to-fri';
                break;
            default : 
            	$wpjobportal_tempfor = 'ew-cm';
                break;
        }
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_emailtemplate&for=" . esc_attr($wpjobportal_tempfor)));
        wp_redirect($wpjobportal_url);
        die();
    }

}

$WPJOBPORTALEmailtemplateController = new WPJOBPORTALEmailtemplateController();
?>
