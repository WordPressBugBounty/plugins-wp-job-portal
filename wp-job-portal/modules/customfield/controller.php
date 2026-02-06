<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALCustomFieldController {

    private $_msgkey;

    function __construct() {
        self::handleRequest();
        $this->_msgkey = WPJOBPORTALincluder::getJSModel('customfield')->getMessagekey();
    }

    function handleRequest() {
        $wpjobportal_layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'fieldsordering');
        if (self::canaddfile($wpjobportal_layout)) {
            switch ($wpjobportal_layout) {
                case 'admin_searchfields':
                    $wpjobportal_fieldfor = WPJOBPORTALrequest::getVar('ff','',2);
                    wpjobportal::$wpjobportal_data['fieldfor'] = $wpjobportal_fieldfor;
                    WPJOBPORTALincluder::getJSModel('customfield')->getSearchFieldsOrdering($wpjobportal_fieldfor);
                    break;

                case 'admin_formuserfield':
                    $wpjobportal_id = WPJOBPORTALrequest::getVar('wpjobportalid');
                    $wpjobportal_fieldfor = WPJOBPORTALrequest::getVar('ff');
                    if (empty($wpjobportal_fieldfor)){
                        $wpjobportal_fieldfor = wpjobportal::$wpjobportal_data['fieldfor'];
                    }else{
                        wpjobportal::$wpjobportal_data['fieldfor'] = $wpjobportal_fieldfor;
                    }
                    wpjobportal::$_data[0]['fieldfor'] = $wpjobportal_fieldfor;
                    WPJOBPORTALincluder::getJSModel('customfield')->getUserFieldbyId($wpjobportal_id, $wpjobportal_fieldfor);
                    break;
                default:
                    return;
            }
            $wpjobportal_module = (wpjobportal::$_common->wpjp_isadmin()) ? 'page' : 'wpjobportalme';
            $wpjobportal_module = WPJOBPORTALrequest::getVar($wpjobportal_module, null, 'customfield');
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
        $wpjobportal_fieldfor = WPJOBPORTALrequest::getVar('ff');
        $wpjobportal_ids = WPJOBPORTALrequest::getVar('wpjobportal-cb');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('customfield')->fieldsRequiredOrNot($wpjobportal_ids, 1); // required
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'customfield');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url('admin.php?page=wpjobportal_customfield&wpjobportallt=fieldsordering&ff=' . esc_attr($wpjobportal_fieldfor)));
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
        $wpjobportal_fieldfor = WPJOBPORTALrequest::getVar('ff');
        $wpjobportal_ids = WPJOBPORTALrequest::getVar('wpjobportal-cb');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('customfield')->fieldsRequiredOrNot($wpjobportal_ids, 0); // notrequired
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'customfield');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url('admin.php?page=wpjobportal_customfield&wpjobportallt=fieldsordering&ff=' . esc_attr($wpjobportal_fieldfor)));
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
        $wpjobportal_fieldfor = WPJOBPORTALrequest::getVar('ff');
        $wpjobportal_ids = WPJOBPORTALrequest::getVar('wpjobportal-cb');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('customfield')->fieldsPublishedOrNot($wpjobportal_ids, 1);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'record');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url('admin.php?page=wpjobportal_customfield&wpjobportallt=fieldsordering&ff=' . esc_attr($wpjobportal_fieldfor)));
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
        $wpjobportal_fieldfor = WPJOBPORTALrequest::getVar('ff');
        $wpjobportal_ids = WPJOBPORTALrequest::getVar('wpjobportal-cb');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('customfield')->fieldsPublishedOrNot($wpjobportal_ids, 0);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'record');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url('admin.php?page=wpjobportal_customfield&wpjobportallt=fieldsordering&ff=' . esc_attr($wpjobportal_fieldfor)));
        if ($wpjobportal_pagenum)
            $wpjobportal_url .= "&pagenum=" . $wpjobportal_pagenum;
        wp_redirect($wpjobportal_url);
        die();
    }

    /*function visitorfieldpublished() {
        $wpjobportal_pagenum = WPJOBPORTALrequest::getVar('pagenum');
        $wpjobportal_fieldfor = WPJOBPORTALrequest::getVar('ff');
        $wpjobportal_ids = WPJOBPORTALrequest::getVar('wpjobportal-cb');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('customfield')->visitorFieldsPublishedOrNot($wpjobportal_ids, 1);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'record');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url('admin.php?page=wpjobportal_customfield&wpjobportallt=fieldsordering&ff=' . esc_attr($wpjobportal_fieldfor)));
        if ($wpjobportal_pagenum)
            $wpjobportal_url .= "&pagenum=" . $wpjobportal_pagenum;
        wp_redirect($wpjobportal_url);
        die();
    }

    function visitorfieldunpublished() {
        $wpjobportal_pagenum = WPJOBPORTALrequest::getVar('pagenum');
        $wpjobportal_fieldfor = WPJOBPORTALrequest::getVar('ff');
        $wpjobportal_ids = WPJOBPORTALrequest::getVar('wpjobportal-cb');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('customfield')->visitorFieldsPublishedOrNot($wpjobportal_ids, 0);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'record');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url('admin.php?page=wpjobportal_customfield&wpjobportallt=fieldsordering&ff=' . esc_attr($wpjobportal_fieldfor)));
        if ($wpjobportal_pagenum)
            $wpjobportal_url .= "&pagenum=" . $wpjobportal_pagenum;
        wp_redirect($wpjobportal_url);
        die();
    }*/

    /*function customfieldup() {
        $wpjobportal_pagenum = WPJOBPORTALrequest::getVar('pagenum');
        $wpjobportal_fieldfor = WPJOBPORTALrequest::getVar('ff');
        $wpjobportal_id = WPJOBPORTALrequest::getVar('fieldid');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('customfield')->fieldOrderingUp($wpjobportal_id);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'customfield');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url('admin.php?page=wpjobportal_customfield&wpjobportallt=fieldsordering&ff=' . esc_attr($wpjobportal_fieldfor)));
        if ($wpjobportal_pagenum)
            $wpjobportal_url .= "&pagenum=" . $wpjobportal_pagenum;
        wp_redirect($wpjobportal_url);
        die();
    }

    function customfielddown() {
        $wpjobportal_pagenum = WPJOBPORTALrequest::getVar('pagenum');
        $wpjobportal_fieldfor = WPJOBPORTALrequest::getVar('ff');
        $wpjobportal_id = WPJOBPORTALrequest::getVar('fieldid');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('customfield')->fieldOrderingDown($wpjobportal_id);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'customfield');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url('admin.php?page=wpjobportal_customfield&wpjobportallt=fieldsordering&ff=' . esc_attr($wpjobportal_fieldfor)));
        if ($wpjobportal_pagenum)
            $wpjobportal_url .= "&pagenum=" . $wpjobportal_pagenum;
        wp_redirect($wpjobportal_url);
        die();
    }*/

    function saveuserfield() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_field_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_data = WPJOBPORTALrequest::get('post');
        $wpjobportal_fieldfor = WPJOBPORTALrequest::getVar('fieldfor');
        if($wpjobportal_fieldfor == ''){
            $wpjobportal_fieldfor = $wpjobportal_data['fieldfor'];
        }
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('customfield')->storeUserField($wpjobportal_data);
        if ($wpjobportal_result === WPJOBPORTAL_SAVE_ERROR || $wpjobportal_result === false) {
            $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_customfield&wpjobportallt=formuserfield&ff=" . esc_attr($wpjobportal_fieldfor)));
        } else
            $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_customfield&ff=" . esc_attr($wpjobportal_fieldfor)));
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'customfield');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        wp_redirect($wpjobportal_url);
        die();
    }

    // function savesearchcustomfield() { // not called anywhere
    //     $wpjobportal_data = WPJOBPORTALrequest::get('post');
    //     $wpjobportal_fieldfor = WPJOBPORTALrequest::getVar('fieldfor');
    //     if($wpjobportal_fieldfor == ''){
    //         $wpjobportal_fieldfor = $wpjobportal_data['fieldfor'];
    //     }
    //     $wpjobportal_result = WPJOBPORTALincluder::getJSModel('customfield')->storeSearchFieldOrdering($wpjobportal_data);
    //     $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_customfield&wpjobportallt=searchfields&fieldfor=" . esc_attr($wpjobportal_fieldfor)));
    //     $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'customfield');
    //     WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
    //     wp_redirect($wpjobportal_url);
    //     die();
    // }

    // function savesearchcustomfieldFromForm() { // not called anywhere
    //     $wpjobportal_data = WPJOBPORTALrequest::get('post');
    //     $wpjobportal_fieldfor = WPJOBPORTALrequest::getVar('fieldfor');
    //     if($wpjobportal_fieldfor == ''){
    //         $wpjobportal_fieldfor = $wpjobportal_data['fieldfor'];
    //     }
    //     $wpjobportal_result = WPJOBPORTALincluder::getJSModel('customfield')->storeSearchFieldOrderingByForm($wpjobportal_data);
    //     $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_customfield&wpjobportallt=searchfields&fieldfor=" . esc_attr($wpjobportal_fieldfor)));
    //     $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'customfield');
    //     WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
    //     wp_redirect($wpjobportal_url);
    //     die();
    // }

    function remove() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_field_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_pagenum = WPJOBPORTALrequest::getVar('pagenum');
        $wpjobportal_id = WPJOBPORTALrequest::getVar('fieldid');
        $wpjobportal_ff = WPJOBPORTALrequest::getVar('ff');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('customfield')->deleteUserField($wpjobportal_id);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'customfield');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_customfield&ff=".esc_attr($wpjobportal_ff)));
        if ($wpjobportal_pagenum)
            $wpjobportal_url .= "&pagenum=" . $wpjobportal_pagenum;
        wp_redirect($wpjobportal_url);
        die();
    }

    function downloadcustomfile(){
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_field_nonce') ) {
             die( 'Security check Failed' );
        }

        $wpjobportal_upload_for = WPJOBPORTALrequest::getVar('upload_for');// to handle different entities(company, job, resume)
        $wpjobportal_entity_id = WPJOBPORTALrequest::getVar('entity_id');// to create path for enitity directory where the file is located
        $file_name = WPJOBPORTALrequest::getVar('file_name');// to access the file and download it

        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('customfield')->downloadCustomUploadedFile($wpjobportal_upload_for,$file_name,$wpjobportal_entity_id);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'customfield');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_customfield&ff=".esc_attr($wpjobportal_ff)));
        if ($wpjobportal_pagenum)
            $wpjobportal_url .= "&pagenum=" . $wpjobportal_pagenum;
        wp_redirect($wpjobportal_url);
        die();
    }

}

$WPJOBPORTALcustomfieldController = new WPJOBPORTALcustomfieldController();
?>
