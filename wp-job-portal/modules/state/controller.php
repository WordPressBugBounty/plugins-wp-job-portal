<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALStateController {

    private $_msgkey;

    function __construct() {

        self::handleRequest();

        $this->_msgkey = WPJOBPORTALincluder::getJSModel('state')->getMessagekey();
    }

    function handleRequest() {
        $wpjobportal_layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'states');
        if (self::canaddfile($wpjobportal_layout)) {
            switch ($wpjobportal_layout) {
                case 'admin_states':
                    $wpjobportal_countryid = WPJOBPORTALrequest::getVar('countryid');
                    if (!$wpjobportal_countryid)
                        $wpjobportal_countryid = get_option("wpjobportal_countryid_for_stateid");
                    update_option( 'wpjobportal_countryid_for_stateid', $wpjobportal_countryid );
                    WPJOBPORTALincluder::getJSModel('state')->getAllCountryStates($wpjobportal_countryid);
                    break;
                case 'admin_formstate':
                    $wpjobportal_id = WPJOBPORTALrequest::getVar('wpjobportalid');
                    WPJOBPORTALincluder::getJSModel('state')->getStatebyId($wpjobportal_id);
                    break;
                default:
                    return;
            }
            $wpjobportal_module = (wpjobportal::$_common->wpjp_isadmin()) ? 'page' : 'wpjobportalme';
            $wpjobportal_module = WPJOBPORTALrequest::getVar($wpjobportal_module, null, 'state');
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

    function remove() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_state_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_ids = WPJOBPORTALrequest::getVar('wpjobportal-cb');
        $wpjobportal_countryid = get_option("wpjobportal_countryid_for_stateid");

        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('state')->deleteStates($wpjobportal_ids);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'state');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_state&wpjobportallt=states&countryid=" . esc_attr($wpjobportal_countryid)));
        wp_redirect($wpjobportal_url);
        die();
    }

    function publish() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_state_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_pagenum = WPJOBPORTALrequest::getVar('pagenum');
        $wpjobportal_ids = WPJOBPORTALrequest::getVar('wpjobportal-cb');
        $wpjobportal_countryid = get_option("wpjobportal_countryid_for_stateid");
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('state')->publishUnpublish($wpjobportal_ids, 1); //  for publish
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'record');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_state&wpjobportallt=states&countryid=" . esc_attr($wpjobportal_countryid)));
        if ($wpjobportal_pagenum)
            $wpjobportal_url .= "&pagenum=" . $wpjobportal_pagenum;
        wp_redirect($wpjobportal_url);
        die();
    }

    function unpublish() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_state_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_pagenum = WPJOBPORTALrequest::getVar('pagenum');
        $wpjobportal_ids = WPJOBPORTALrequest::getVar('wpjobportal-cb');
        $wpjobportal_countryid = get_option("wpjobportal_countryid_for_stateid");
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('state')->publishUnpublish($wpjobportal_ids, 0); //  for unpublish
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'record');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_state&wpjobportallt=states&countryid=" . esc_attr($wpjobportal_countryid)));
        if ($wpjobportal_pagenum)
            $wpjobportal_url .= "&pagenum=" . $wpjobportal_pagenum;
        wp_redirect($wpjobportal_url);
        die();
    }

    function savestate() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_state_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_data = WPJOBPORTALrequest::get('post');
        $wpjobportal_countryid = get_option("wpjobportal_countryid_for_stateid");
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('state')->storeState($wpjobportal_data, $wpjobportal_countryid);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_state&wpjobportallt=states&countryid=" . esc_attr($wpjobportal_countryid)));
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'state');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        wp_redirect($wpjobportal_url);
        die();
    }

}

$WPJOBPORTALStateController = new WPJOBPORTALStateController();
?>
