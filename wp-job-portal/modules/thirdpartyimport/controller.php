<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALthirdpartyimportController {
    private $_msgkey;
    function __construct() {
        self::handleRequest();
        $this->_msgkey = WPJOBPORTALincluder::getJSModel('thirdpartyimport')->getMessagekey();
    }

    function handleRequest() {
        $wpjobportal_layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'importdata');
        if (self::canaddfile($wpjobportal_layout)) {
            switch ($wpjobportal_layout) {
                case 'admin_importdata':
                    
                    $wpjobportal_selected_plugin = WPJOBPORTALrequest::getVar('selected_plugin', '', 0);
                    wpjobportal::$_data['count_for'] = $wpjobportal_selected_plugin;
                    if($wpjobportal_selected_plugin != 0){
                        // prepare data for selected plugin
                        WPJOBPORTALincluder::getJSModel('thirdpartyimport')->getJobManagerDataStats($wpjobportal_selected_plugin);
                    }
                    // no plugin selected
                    break;
                case 'admin_importresult':
                    
                    break;
                default:
                    return;
            }
            $wpjobportal_module = 'page';
            $wpjobportal_module = WPJOBPORTALrequest::getVar($wpjobportal_module, null, 'thirdpartyimport');
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


    function importjobmanagerdata() {
        
        // $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        // if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_job_manager_import_nonce') ) {
        //      die( 'Security check Failed' );
        // }

        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $wpjobportal_selected_plugin = WPJOBPORTALrequest::getVar('selected_plugin', '', 0);
        if($wpjobportal_selected_plugin == 1){
            $wpjobportal_result = WPJOBPORTALincluder::getJSModel('thirdpartyimport')->importJobManagerData();
            $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, "thirdpartyimport");
            WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        }
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_thirdpartyimport&wpjobportallt=importresult"));
        wp_redirect($wpjobportal_url);
        die();
    }

    function getjobmanagerdatastats() {
        
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_job_manager_import_nonce') ) {
            die( 'Security check Failed' );
        }

        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('thirdpartyimport')->getJobManagerDataStats();
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, "thirdpartyimport");
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_thirdpartyimport&wpjobportallt=importresult"));
        wp_redirect($wpjobportal_url);
        die();
    }
}

$WPJOBPORTALthirdpartyimportController = new WPJOBPORTALthirdpartyimportController();
?>
