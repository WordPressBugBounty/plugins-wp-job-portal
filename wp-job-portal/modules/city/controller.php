<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALCityController {

    private $_msgkey;

    function __construct() {
        self::handleRequest();
        $this->_msgkey = WPJOBPORTALincluder::getJSModel('city')->getMessagekey();
    }

    function handleRequest() {
        $wpjobportal_layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'cities');
        if (self::canaddfile($wpjobportal_layout)) {
            switch ($wpjobportal_layout) {
                case 'admin_cities':
                    $wpjobportal_countryid = WPJOBPORTALrequest::getVar('countryid');
                    $wpjobportal_stateid = WPJOBPORTALrequest::getVar('stateid');

                    update_option( 'wpjobportal_countryid_for_city', $wpjobportal_countryid);
                    update_option( 'wpjobportal_stateid_for_city', $wpjobportal_stateid);
                    WPJOBPORTALincluder::getJSModel('city')->getAllStatesCities($wpjobportal_countryid, $wpjobportal_stateid);
                    break;
                case 'admin_formcity':
                    $wpjobportal_id = WPJOBPORTALrequest::getVar('wpjobportalid');
                    WPJOBPORTALincluder::getJSModel('city')->getCitybyId($wpjobportal_id);
                    break;
                    case 'admin_loadaddressdata':
                        break;
                    case 'admin_locationnamesettings':
                        WPJOBPORTALincluder::getJSModel('city')->getSampleCities();
                        break;
                 default:
                    return;
           }
            $wpjobportal_module = (wpjobportal::$_common->wpjp_isadmin()) ? 'page' : 'wpjobportalme';
            $wpjobportal_module = WPJOBPORTALrequest::getVar($wpjobportal_module, null, 'city');
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

    function getaddressdatabycityname() {
        $cityname = WPJOBPORTALrequest::getVar('q');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('city')->getAddressDataByCityName($cityname);
        $wpjobportal_json_response = wp_json_encode($wpjobportal_result);
        echo wp_kses($wpjobportal_json_response,WPJOBPORTAL_ALLOWED_TAGS);
        exit();
    }

    function removecity() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_city_nonce') ) {
             die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) { //only admin can DO it.
            return false;
        }
        $wpjobportal_countryid = get_option("wpjobportal_countryid_for_city" );
        $wpjobportal_stateid = get_option( "wpjobportal_stateid_for_city" );

        $wpjobportal_ids = WPJOBPORTALrequest::getVar('wpjobportal-cb');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('city')->deleteCities($wpjobportal_ids);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'city');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_city&wpjobportallt=cities&countryid=" . esc_attr($wpjobportal_countryid) . "&stateid=" . esc_attr($wpjobportal_stateid)));
        wp_redirect($wpjobportal_url);
        die();
    }

    function publish() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_city_nonce') ) {
             die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) { //only admin can DO it.
            return false;
        }
        $wpjobportal_countryid = get_option("wpjobportal_countryid_for_city" );
        $wpjobportal_stateid = get_option( "wpjobportal_stateid_for_city" );

        $wpjobportal_pagenum = WPJOBPORTALrequest::getVar('pagenum');
        $wpjobportal_ids = WPJOBPORTALrequest::getVar('wpjobportal-cb');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('city')->publishUnpublish($wpjobportal_ids, 1); //  for publish
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'record');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_city&wpjobportallt=cities&countryid=" . esc_attr($wpjobportal_countryid) . "&stateid=" . esc_attr($wpjobportal_stateid)));
        if ($wpjobportal_pagenum)
            $wpjobportal_url .= "&pagenum=" . $wpjobportal_pagenum;
        wp_redirect($wpjobportal_url);
        die();
    }

    function unpublish() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_city_nonce') ) {
             die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) { //only admin can DO it.
            return false;
        }
        $wpjobportal_countryid = get_option("wpjobportal_countryid_for_city" );
        $wpjobportal_stateid = get_option( "wpjobportal_stateid_for_city" );

        $wpjobportal_pagenum = WPJOBPORTALrequest::getVar('pagenum');
        $wpjobportal_ids = WPJOBPORTALrequest::getVar('wpjobportal-cb');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('city')->publishUnpublish($wpjobportal_ids, 0); //  for unpublish
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'record');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_city&wpjobportallt=cities&countryid=" . esc_attr($wpjobportal_countryid) . "&stateid=" . esc_attr($wpjobportal_stateid)));
        if ($wpjobportal_pagenum)
            $wpjobportal_url .= "&pagenum=" . $wpjobportal_pagenum;
        wp_redirect($wpjobportal_url);
        die();
    }

    function savecity() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_city_nonce') ) {
             die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) { //only admin can DO it.
            return false;
        }
        $wpjobportal_countryid = get_option("wpjobportal_countryid_for_city" );
        $wpjobportal_stateid = get_option( "wpjobportal_stateid_for_city" );
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_city&wpjobportallt=cities&countryid=" . esc_attr($wpjobportal_countryid) . "&stateid=" . esc_attr($wpjobportal_stateid)));

        $wpjobportal_data = WPJOBPORTALrequest::get('post');
        if ($wpjobportal_data['stateid'])
            $wpjobportal_stateid = $wpjobportal_data['stateid'];
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('city')->storeCity($wpjobportal_data, $wpjobportal_countryid, $wpjobportal_stateid);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'city');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        wp_redirect($wpjobportal_url);
        die();
    }

    function loadaddressdata() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_address_data_nonce') ) {
             die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) { //only admin can DO it.
            return false;
        }
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('city')->loadAddressData();
        // echo var_dump($wpjobportal_result);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'addressdata');
        // echo var_dump($wpjobportal_msg);
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = admin_url("admin.php?page=wpjobportal_city&wpjobportallt=loadaddressdata");
        wp_redirect($wpjobportal_url);
        die();
    }

    function savecitynamesettings() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_address_data_nonce') ) {
             die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) { //only admin can DO it.
            return false;
        }
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('city')->updateCityNameSettings();
        // echo var_dump($wpjobportal_result);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'addressdata');
        // echo var_dump($wpjobportal_msg);
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = admin_url("admin.php?page=wpjobportal_city&wpjobportallt=locationnamesettings");
        wp_redirect($wpjobportal_url);
        die();
    }


}

$WPJOBPORTALCityController = new WPJOBPORTALCityController();
?>
