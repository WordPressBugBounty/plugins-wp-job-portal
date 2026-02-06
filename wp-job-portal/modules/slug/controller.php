<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALslugController {
    private $_msgkey;
    function __construct() {
        self::handleRequest();
        $this->_msgkey = WPJOBPORTALincluder::getJSModel('slug')->getMessagekey();        
    }

    function handleRequest() {
        $wpjobportal_layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'slug');
        if (self::canaddfile($wpjobportal_layout)) {
            switch ($wpjobportal_layout) {
                case 'admin_slug':
                    WPJOBPORTALincluder::getJSModel('slug')->getSlug();
                    break;
                default:
                    return;
            }
            $wpjobportal_module = 'page';
            $wpjobportal_module = WPJOBPORTALrequest::getVar($wpjobportal_module, null, 'slug');
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

    function saveSlug() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_slug_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_data = WPJOBPORTALrequest::get('post');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('slug')->storeSlug($wpjobportal_data);
        if($wpjobportal_data['pagenum'] > 0){
            $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_slug&pagenum=".esc_attr($wpjobportal_data['pagenum'])));
        }else{
            $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_slug"));
        }

        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'slug');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        wp_redirect($wpjobportal_url);
        exit;
    }

    function saveprefix() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_slug_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_data = WPJOBPORTALrequest::get('post');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('slug')->savePrefix($wpjobportal_data);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_slug"));
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'prefix');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        wp_redirect($wpjobportal_url);
        exit;
    }

    function savehomeprefix() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_slug_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_data = WPJOBPORTALrequest::get('post');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('slug')->saveHomePrefix($wpjobportal_data);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_slug"));
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'prefix');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        wp_redirect($wpjobportal_url);
        exit;
    }

    function resetallslugs() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_slug_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_data = WPJOBPORTALrequest::get('post');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('slug')->resetAllSlugs();
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_slug"));
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'slug');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        wp_redirect($wpjobportal_url);
        exit;
    }
}

$WPJOBPORTALslugController = new WPJOBPORTALslugController();
?>
