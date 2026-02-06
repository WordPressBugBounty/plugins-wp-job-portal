<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALConfigurationController {

    private $_msgkey;

    function __construct() {
        self::handleRequest();
        $this->_msgkey = WPJOBPORTALincluder::getJSModel('configuration')->getMessagekey();        
    }

    function handleRequest() {
        $wpjobportal_layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'configurations');
        if (self::canaddfile($wpjobportal_layout)) {
            switch ($wpjobportal_layout) {
                case 'admin_configurations':
                case 'admin_configurationsemployer':
                case 'admin_configurationsjobseeker':
                    $wpjpconfigid = WPJOBPORTALrequest::getVar('wpjpconfigid');
                    if (isset($wpjpconfigid)) {
                        wpjobportal::$_data['wpjpconfigid'] = $wpjpconfigid;
                    } else {
                        wpjobportal::$_data['wpjpconfigid'] = 'general_setting';
                    }
                    WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationsForForm();
                    break;
                default:
                    return;
            }
            $wpjobportal_module = (wpjobportal::$_common->wpjp_isadmin()) ? 'page' : 'wpjobportalme';
            $wpjobportal_module = WPJOBPORTALrequest::getVar($wpjobportal_module, null, 'configuration');
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

    function saveconfiguration() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_configuration_nonce') ) {
             die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $wpjobportal_data = WPJOBPORTALrequest::get('post');
        $wpjobportal_layout = WPJOBPORTALrequest::getVar('wpjobportallt');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('configuration')->storeConfig($wpjobportal_data);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, "configuration");
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
      $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_configuration&wpjobportallt=configurations"));
        wp_redirect($wpjobportal_url);
        die();
    }

    // function to handle auto update configuration
    function saveautoupdateconfiguration() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_configuration_nonce') ) {
             die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('configuration')->storeAutoUpdateConfig();
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, "configuration");
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal&wpjobportallt=addonstatus"));
        wp_redirect($wpjobportal_url);
        die();
    }


}

$WPJOBPORTALConfigurationController = new WPJOBPORTALConfigurationController();
?>
