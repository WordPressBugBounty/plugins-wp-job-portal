<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALCategoryController {

    private $_msgkey;

    function __construct() {
        self::handleRequest();
        $this->_msgkey = WPJOBPORTALincluder::getJSModel('category')->getMessagekey();
    }

    function handleRequest() {
        $wpjobportal_layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'categories');
        if (self::canaddfile($wpjobportal_layout)) {
            switch ($wpjobportal_layout) {
                case 'admin_categories':
                    WPJOBPORTALincluder::getJSModel('category')->getAllCategories();
                    break;
                case 'admin_formcategory':
                    $wpjobportal_id = WPJOBPORTALrequest::getVar('wpjobportalid');
                    WPJOBPORTALincluder::getJSModel('category')->getCategorybyId($wpjobportal_id);
                    break;
                 default:
                    return;
           }
            $wpjobportal_module = (wpjobportal::$_common->wpjp_isadmin()) ? 'page' : 'wpjobportalme';
            $wpjobportal_module = WPJOBPORTALrequest::getVar($wpjobportal_module, null, 'category');
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

    function savecategory() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_category_nonce') ) {
             die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) { //only admin can DO it.
            return false;
        }
        $wpjobportal_data = WPJOBPORTALrequest::get('post');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('category')->storeCategory($wpjobportal_data);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'category');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_category&wpjobportallt=categories"));
        wp_redirect($wpjobportal_url);
        die();
    }

    function remove() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_category_nonce') ) {
             die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) { //only admin can DO it.
            return false;
        }
        $wpjobportal_ids = WPJOBPORTALrequest::getVar('wpjobportal-cb');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('category')->deleteCategories($wpjobportal_ids);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'category');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_category&wpjobportallt=categories"));
        wp_redirect($wpjobportal_url);
        die();
    }

    function publish() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_category_nonce') ) {
             die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) { //only admin can DO it.
            return false;
        }
        $wpjobportal_pagenum = WPJOBPORTALrequest::getVar('pagenum');
        $wpjobportal_ids = WPJOBPORTALrequest::getVar('wpjobportal-cb');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('category')->publishUnpublish($wpjobportal_ids, 1); //  for publish
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'record');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_category&wpjobportallt=categories"));
        if ($wpjobportal_pagenum)
            $wpjobportal_url .= "&pagenum=" . $wpjobportal_pagenum;
        wp_redirect($wpjobportal_url);
        die();
    }

    function unpublish() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_category_nonce') ) {
             die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) { //only admin can DO it.
            return false;
        }
        $wpjobportal_pagenum = WPJOBPORTALrequest::getVar('pagenum');
        $wpjobportal_ids = WPJOBPORTALrequest::getVar('wpjobportal-cb');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('category')->publishUnpublish($wpjobportal_ids, 0); //  for unpublish
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'record');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_category&wpjobportallt=categories"));
        if ($wpjobportal_pagenum)
            $wpjobportal_url .= "&pagenum=" . $wpjobportal_pagenum;
        wp_redirect($wpjobportal_url);
        die();
    }




    // WE will Save the Ordering system in this Function
    function saveordering(){
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_category_nonce') ) {
             die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) { //only admin can DO it.
            return false;
        }
        $post = WPJOBPORTALrequest::get('post');
        if($post['task'] == 'unpublish'){
            $this->unpublish();
            exit();
        }
        if($post['task'] == 'publish'){
            $this->publish();
            exit();
        }
        if($post['task'] == 'remove'){
            $this->remove();
            exit();
        }
        WPJOBPORTALincluder::getJSModel('category')->storeOrderingFromPage($post);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_category"));
        wp_redirect($wpjobportal_url);
        exit;
    }


}

$WPJOBPORTALCategoryController = new WPJOBPORTALCategoryController();
?>
