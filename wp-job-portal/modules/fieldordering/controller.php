<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALfieldorderingController {

    private $_msgkey;

    function __construct() {
        self::handleRequest();
        $this->_msgkey = WPJOBPORTALincluder::getJSModel('fieldordering')->getMessagekey();
    }

    function handleRequest() {
        $wpjobportal_layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'fieldsordering');
        if (self::canaddfile($wpjobportal_layout)) {
            switch ($wpjobportal_layout) {
                case 'admin_fieldsordering':
                    $wpjobportal_fieldfor = WPJOBPORTALrequest::getVar('ff','',1);// 1 is to make sure the page does not show warnings in case of parameter drop
                    wpjobportal::$wpjobportal_data['fieldfor'] = $wpjobportal_fieldfor;
                    WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldsOrdering($wpjobportal_fieldfor);
                    break;
                case 'admin_searchfields':
                    $wpjobportal_fieldfor = WPJOBPORTALrequest::getVar('ff','',2);
                    wpjobportal::$wpjobportal_data['fieldfor'] = $wpjobportal_fieldfor;
                    WPJOBPORTALincluder::getJSModel('fieldordering')->getSearchFieldsOrdering($wpjobportal_fieldfor);
                    break;

                case 'admin_formuserfield':
                    $wpjobportal_id = WPJOBPORTALrequest::getVar('wpjobportalid');
                    $wpjobportal_fieldfor = WPJOBPORTALrequest::getVar('ff','',1);// 1 is to make sure the page does not show warnings in case of parameter drop
                    if (empty($wpjobportal_fieldfor)){
                        $wpjobportal_fieldfor = wpjobportal::$wpjobportal_data['fieldfor'];
                    }else{
                        wpjobportal::$wpjobportal_data['fieldfor'] = $wpjobportal_fieldfor;
                    }
                    wpjobportal::$_data[0]['fieldfor'] = $wpjobportal_fieldfor;
                    WPJOBPORTALincluder::getJSModel('fieldordering')->getUserFieldbyId($wpjobportal_id, $wpjobportal_fieldfor);
                    break;
                default:
                    return;
            }
            $wpjobportal_module = (wpjobportal::$_common->wpjp_isadmin()) ? 'page' : 'wpjobportalme';
            $wpjobportal_module = WPJOBPORTALrequest::getVar($wpjobportal_module, null, 'fieldordering');
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

    function fieldrequired() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_field_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_pagenum = WPJOBPORTALrequest::getVar('pagenum');
        $wpjobportal_fieldfor = WPJOBPORTALrequest::getVar('ff','',1);// 1 is to make sure the page does not show warnings in case of parameter drop
        $wpjobportal_ids = WPJOBPORTALrequest::getVar('wpjobportal-cb');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('fieldordering')->fieldsRequiredOrNot($wpjobportal_ids, 1); // required
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'fieldordering');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url('admin.php?page=wpjobportal_fieldordering&wpjobportallt=fieldsordering&ff=' . esc_attr($wpjobportal_fieldfor)));
        if ($wpjobportal_pagenum)
            $wpjobportal_url .= "&pagenum=" . $wpjobportal_pagenum;
        wp_redirect($wpjobportal_url);
        die();
    }

    function fieldnotrequired() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_field_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_pagenum = WPJOBPORTALrequest::getVar('pagenum');
        $wpjobportal_fieldfor = WPJOBPORTALrequest::getVar('ff','',1);// 1 is to make sure the page does not show warnings in case of parameter drop
        $wpjobportal_ids = WPJOBPORTALrequest::getVar('wpjobportal-cb');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('fieldordering')->fieldsRequiredOrNot($wpjobportal_ids, 0); // notrequired
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'fieldordering');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url('admin.php?page=wpjobportal_fieldordering&wpjobportallt=fieldsordering&ff=' . esc_attr($wpjobportal_fieldfor)));
        if ($wpjobportal_pagenum)
            $wpjobportal_url .= "&pagenum=" . $wpjobportal_pagenum;
        wp_redirect($wpjobportal_url);
        die();
    }

    function fieldpublished() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_field_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_pagenum = WPJOBPORTALrequest::getVar('pagenum');
        $wpjobportal_fieldfor = WPJOBPORTALrequest::getVar('ff','',1);// 1 is to make sure the page does not show warnings in case of parameter drop
        $wpjobportal_ids = WPJOBPORTALrequest::getVar('wpjobportal-cb');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('fieldordering')->fieldsPublishedOrNot($wpjobportal_ids, 1);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'record');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url('admin.php?page=wpjobportal_fieldordering&wpjobportallt=fieldsordering&ff=' . esc_attr($wpjobportal_fieldfor)));
        if ($wpjobportal_pagenum)
            $wpjobportal_url .= "&pagenum=" . $wpjobportal_pagenum;
        wp_redirect($wpjobportal_url);
        die();
    }

    function fieldunpublished() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_field_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_pagenum = WPJOBPORTALrequest::getVar('pagenum');
        $wpjobportal_fieldfor = WPJOBPORTALrequest::getVar('ff','',1);// 1 is to make sure the page does not show warnings in case of parameter drop
        $wpjobportal_ids = WPJOBPORTALrequest::getVar('wpjobportal-cb');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('fieldordering')->fieldsPublishedOrNot($wpjobportal_ids, 0);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'record');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url('admin.php?page=wpjobportal_fieldordering&wpjobportallt=fieldsordering&ff=' . esc_attr($wpjobportal_fieldfor)));
        if ($wpjobportal_pagenum)
            $wpjobportal_url .= "&pagenum=" . $wpjobportal_pagenum;
        wp_redirect($wpjobportal_url);
        die();
    }

    function visitorfieldpublished() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_field_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_pagenum = WPJOBPORTALrequest::getVar('pagenum');
        $wpjobportal_fieldfor = WPJOBPORTALrequest::getVar('ff','',1);// 1 is to make sure the page does not show warnings in case of parameter drop
        $wpjobportal_ids = WPJOBPORTALrequest::getVar('wpjobportal-cb');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('fieldordering')->visitorFieldsPublishedOrNot($wpjobportal_ids, 1);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'record');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url('admin.php?page=wpjobportal_fieldordering&wpjobportallt=fieldsordering&ff=' . esc_attr($wpjobportal_fieldfor)));
        if ($wpjobportal_pagenum)
            $wpjobportal_url .= "&pagenum=" . $wpjobportal_pagenum;
        wp_redirect($wpjobportal_url);
        die();
    }

    function visitorfieldunpublished() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_field_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_pagenum = WPJOBPORTALrequest::getVar('pagenum');
        $wpjobportal_fieldfor = WPJOBPORTALrequest::getVar('ff','',1);// 1 is to make sure the page does not show warnings in case of parameter drop
        $wpjobportal_ids = WPJOBPORTALrequest::getVar('wpjobportal-cb');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('fieldordering')->visitorFieldsPublishedOrNot($wpjobportal_ids, 0);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'record');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url('admin.php?page=wpjobportal_fieldordering&wpjobportallt=fieldsordering&ff=' . esc_attr($wpjobportal_fieldfor)));
        if ($wpjobportal_pagenum)
            $wpjobportal_url .= "&pagenum=" . $wpjobportal_pagenum;
        wp_redirect($wpjobportal_url);
        die();
    }

    // function fieldorderingup() { // not called anywere
    //     $wpjobportal_pagenum = WPJOBPORTALrequest::getVar('pagenum');
    //     $wpjobportal_fieldfor = WPJOBPORTALrequest::getVar('ff','',1);// 1 is to make sure the page does not show warnings in case of parameter drop
    //     $wpjobportal_id = WPJOBPORTALrequest::getVar('fieldid');
    //     $wpjobportal_result = WPJOBPORTALincluder::getJSModel('fieldordering')->fieldOrderingUp($wpjobportal_id);
    //     $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'fieldordering');
    //     WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
    //     $wpjobportal_url = esc_url_raw(admin_url('admin.php?page=wpjobportal_fieldordering&wpjobportallt=fieldsordering&ff=' . esc_attr($wpjobportal_fieldfor)));
    //     if ($wpjobportal_pagenum)
    //         $wpjobportal_url .= "&pagenum=" . $wpjobportal_pagenum;
    //     wp_redirect($wpjobportal_url);
    //     die();
    // }

    // function fieldorderingdown() { // not called anywere
    //     $wpjobportal_pagenum = WPJOBPORTALrequest::getVar('pagenum');
    //     $wpjobportal_fieldfor = WPJOBPORTALrequest::getVar('ff','',1);// 1 is to make sure the page does not show warnings in case of parameter drop
    //     $wpjobportal_id = WPJOBPORTALrequest::getVar('fieldid');
    //     $wpjobportal_result = WPJOBPORTALincluder::getJSModel('fieldordering')->fieldOrderingDown($wpjobportal_id);
    //     $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'fieldordering');
    //     WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
    //     $wpjobportal_url = esc_url_raw(admin_url('admin.php?page=wpjobportal_fieldordering&wpjobportallt=fieldsordering&ff=' . esc_attr($wpjobportal_fieldfor)));
    //     if ($wpjobportal_pagenum)
    //         $wpjobportal_url .= "&pagenum=" . $wpjobportal_pagenum;
    //     wp_redirect($wpjobportal_url);
    //     die();
    // }

    function saveuserfield() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_field_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_data = WPJOBPORTALrequest::get('post');
        $wpjobportal_fieldfor = WPJOBPORTALrequest::getVar('fieldfor','',1);// 1 is to make sure the page does not show warnings in case of parameter drop
        if($wpjobportal_fieldfor == ''){
            $wpjobportal_fieldfor = $wpjobportal_data['fieldfor'];
        }
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('fieldordering')->storeUserField($wpjobportal_data);
        if ($wpjobportal_result === WPJOBPORTAL_SAVE_ERROR || $wpjobportal_result === false) {
            $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_fieldordering&wpjobportallt=formuserfield&ff=" . esc_attr($wpjobportal_fieldfor)));
        } else
            $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_fieldordering&ff=" . esc_attr($wpjobportal_fieldfor)));
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'customfield');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        wp_redirect($wpjobportal_url);
        die();
    }

    function savesearchfieldordering() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_field_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_data = WPJOBPORTALrequest::get('post');
        $wpjobportal_fieldfor = WPJOBPORTALrequest::getVar('fieldfor','',1);// 1 is to make sure the page does not show warnings in case of parameter drop
        if($wpjobportal_fieldfor == ''){
            $wpjobportal_fieldfor = $wpjobportal_data['fieldfor'];
        }
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('fieldordering')->storeSearchFieldOrdering($wpjobportal_data);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_fieldordering&wpjobportallt=searchfields&fieldfor=" . esc_attr($wpjobportal_fieldfor)."&ff=" . esc_attr($wpjobportal_fieldfor)));
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'customfield');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        wp_redirect($wpjobportal_url);
        die();
    }

    function savesearchfieldorderingFromForm() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_field_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_data = WPJOBPORTALrequest::get('post');
        $wpjobportal_fieldfor = WPJOBPORTALrequest::getVar('fieldfor','',1);// 1 is to make sure the page does not show warnings in case of parameter drop
        if($wpjobportal_fieldfor == ''){
            $wpjobportal_fieldfor = $wpjobportal_data['fieldfor'];
        }
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('fieldordering')->storeSearchFieldOrderingByForm($wpjobportal_data);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_fieldordering&wpjobportallt=searchfields&fieldfor=" . $wpjobportal_fieldfor."&ff=" . $wpjobportal_fieldfor));
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'customfield');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        wp_redirect($wpjobportal_url);
        die();
    }

    function remove() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_field_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_pagenum = WPJOBPORTALrequest::getVar('pagenum');
        $wpjobportal_id = WPJOBPORTALrequest::getVar('fieldid');
        $wpjobportal_is_section_headline = WPJOBPORTALrequest::getVar('is_section_headline','',0);
        $wpjobportal_ff = WPJOBPORTALrequest::getVar('ff','',1);// 1 is to make sure the page does not show warnings in case of parameter drop
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('fieldordering')->deleteUserField($wpjobportal_id,$wpjobportal_is_section_headline);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'fieldordering');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_fieldordering&ff=".esc_attr($wpjobportal_ff)));
        if ($wpjobportal_pagenum)
            $wpjobportal_url .= "&pagenum=" . $wpjobportal_pagenum;
        wp_redirect($wpjobportal_url);
        die();
    }

}

$WPJOBPORTALfieldorderingController = new WPJOBPORTALfieldorderingController();
?>
