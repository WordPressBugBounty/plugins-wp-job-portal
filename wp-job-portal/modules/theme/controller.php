<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALthemeController {

    private $_msgkey;

    function __construct() {

        self::handleRequest();
        
    }

    function handleRequest() {
        $wpjobportal_layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'themes');
        $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        if (self::canaddfile($wpjobportal_layout)) {
            $wpjobportal_string = "'jscontrolpanel','emcontrolpanel'";
            $wpjobportal_config_array = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigForMultiple($wpjobportal_string);

            switch ($wpjobportal_layout) {
                case 'admin_themes':
                    WPJOBPORTALincluder::getJSModel('theme')->getCurrentTheme();
                    WPJOBPORTALincluder::getJSModel('wpjobportal')->getCPJobs();
                   break;
                default:
                    return;
            }

            $wpjobportal_module = (wpjobportal::$_common->wpjp_isadmin()) ? 'page' : 'wpjobportalme';
            $wpjobportal_module = WPJOBPORTALrequest::getVar($wpjobportal_module, null, 'theme');
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

    static function savetheme() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_theme_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_data = WPJOBPORTALrequest::get('post');
        WPJOBPORTALincluder::getJSModel('theme')->storeTheme($wpjobportal_data);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_theme&wpjobportallt=themes"));
        wp_redirect($wpjobportal_url);
        die();
    }
}

$WPJOBPORTALthemeController = new WPJOBPORTALthemeController();
?>
