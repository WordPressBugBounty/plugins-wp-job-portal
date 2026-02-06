<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALCommonController {

    private $_msgkey;

    function __construct() {
        self::handleRequest();
        $this->_msgkey = WPJOBPORTALincluder::getJSModel('common')->getMessagekey();
    }

    function handleRequest() {
        $wpjobportal_layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'newinwpjobportal');
         $wpjobportal_socialUser = "";
        if(isset($_COOKIE['wpjobportal-socialid'])){
            $wpjobportal_socialUser = sanitize_key($_COOKIE['wpjobportal-socialid']);
        }
        if (self::canaddfile($wpjobportal_layout)) {
            switch ($wpjobportal_layout) {
                case 'newinwpjobportal':
                    if(WPJOBPORTALincluder::getObjectClass('user')->isguest() && !$wpjobportal_socialUser){
                        $wpjobportal_link = get_permalink();
                        $wpjobportal_linktext = esc_html(__('Login','wp-job-portal'));
                        wpjobportal::$_error_flag_message = WPJOBPORTALLayout::setMessageFor(1 , $wpjobportal_link , $wpjobportal_linktext,1);
                        wpjobportal::$_error_flag = true;
                    }
                    // to disable admin from selecting role
                    if(current_user_can('manage_options')){
                        $wpjobportal_link = get_permalink();
                        $wpjobportal_linktext = '';
                        wpjobportal::$_error_flag_message = WPJOBPORTALLayout::setMessageFor(10 , $wpjobportal_link , $wpjobportal_linktext,1);
                        wpjobportal::$_error_flag = true;
                    }
                    // check if registration as employer is enabled.
                    $wpjobportal_showemployerlink  = wpjobportal::$_config->getConfigurationByConfigName('showemployerlink');
                    if($wpjobportal_showemployerlink == 0){ // if not then force currnt user to job seeker
                        $this->forcecurrentusertojobsseker();
                    }
                break;
                case 'addonmissing':
                    // set error message page only shows in case of missing addon link
                    wpjobportal::$_error_flag_message = WPJOBPORTALLayout::setMessageFor(6,null,null,1);
                    wpjobportal::$_error_flag_message_for=6;
                    wpjobportal::$_error_flag = true;
                    break;
                default:
                    return;
            }
            $wpjobportal_module = (wpjobportal::$_common->wpjp_isadmin()) ? 'page' : 'wpjobportalme';
            $wpjobportal_module = WPJOBPORTALrequest::getVar($wpjobportal_module, null, 'common');
            $wpjobportal_module = wpjobportalphplib::wpJP_str_replace('wpjobportal_', '', $wpjobportal_module);
            if(is_numeric($wpjobportal_module)){
                $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme', null, 'common');
            }
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

    function makedefault() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_common_entity_nonce') ) {
             die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) { //only admin can DO it.
            return false;
        }
        $wpjobportal_id = WPJOBPORTALrequest::getVar('id');
        $for = WPJOBPORTALrequest::getVar('for'); // table name
        $wpjobportal_pagenum = WPJOBPORTALrequest::getVar('pagenum');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('common')->setDefaultForDefaultTable($wpjobportal_id, $for);
        $object = $this->getpageandlayoutname($for);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, $object['page']);
        switch ($for) {
            case "jobstatus":
                $this->_msgkey = WPJOBPORTALincluder::getJSModel('jobstatus')->getMessagekey();
                break;
            case "jobtypes":
                $this->_msgkey = WPJOBPORTALincluder::getJSModel('jobtype')->getMessagekey();
                break;
            case "careerlevels":
                $this->_msgkey = WPJOBPORTALincluder::getJSModel('careerlevel')->getMessagekey();
                break;
            case "salaryrangetypes":
                $this->_msgkey = WPJOBPORTALincluder::getJSModel('salaryrangetype')->getMessagekey();
                break;
            case "currencies":
                $this->_msgkey = WPJOBPORTALincluder::getJSModel('currency')->getMessagekey();
                break;
            case "heighesteducation":
                $this->_msgkey = WPJOBPORTALincluder::getJSModel('highesteducation')->getMessagekey();
                break;
            case "categories":
                $this->_msgkey = WPJOBPORTALincluder::getJSModel('category')->getMessagekey();
                break;
        }
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_" . esc_attr($object['page']) . "&wpjobportallt=" . esc_attr($object['wpjobportallt'])));
        if ($wpjobportal_pagenum)
            $wpjobportal_url .= "&pagenum=" . $wpjobportal_pagenum;
        wp_redirect($wpjobportal_url);
        die();
    }

    // function defaultorderingup() {
    //     $wpjobportal_id = WPJOBPORTALrequest::getVar('id');
    //     $for = WPJOBPORTALrequest::getVar('for'); //table name
    //     $wpjobportal_pagenum = WPJOBPORTALrequest::getVar('pagenum');
    //     $wpjobportal_result = WPJOBPORTALincluder::getJSModel('common')->setOrderingUpForDefaultTable($wpjobportal_id, $for);
    //     $object = $this->getpageandlayoutname($for);
    //     $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, $object['page']);
    //     $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_" . esc_attr($object['page']) . "&wpjobportallt=" . esc_attr($object['wpjobportallt'])));
    //     if ($wpjobportal_pagenum)
    //         $wpjobportal_url .= "&pagenum=" . $wpjobportal_pagenum;
    //     WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
    //     wp_redirect($wpjobportal_url);
    //     die();
    // }

    // function defaultorderingdown() {
    //     $wpjobportal_id = WPJOBPORTALrequest::getVar('id');
    //     $for = WPJOBPORTALrequest::getVar('for'); // table name
    //     $wpjobportal_pagenum = WPJOBPORTALrequest::getVar('pagenum');
    //     $wpjobportal_result = WPJOBPORTALincluder::getJSModel('common')->setOrderingDownForDefaultTable($wpjobportal_id, $for);
    //     $object = $this->getpageandlayoutname($for);
    //     $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, $object['page']);
    //     $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_" . esc_attr($object['page']) . "&wpjobportallt=" . esc_attr($object['wpjobportallt'])));
    //     if ($wpjobportal_pagenum)
    //         $wpjobportal_url .= "&pagenum=" . $wpjobportal_pagenum;
    //     WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
    //     wp_redirect($wpjobportal_url);
    //     die();
    // }

    function getpageandlayoutname($for) { // for tablename
        switch ($for) {
            case 'jobtypes' : $object['page'] = "jobtype";
                $object['wpjobportallt'] = "jobtypes";
                break;
            case 'shifts' : $object['page'] = "shift";
                $object['wpjobportallt'] = "shifts";
                break;
            case 'ages' : $object['page'] = "age";
                $object['wpjobportallt'] = "ages";
                break;
            case 'careerlevels' : $object['page'] = "careerlevel";
                $object['wpjobportallt'] = "careerlevels";
                break;
            case 'salaryrangetypes' : $object['page'] = "salaryrangetype";
                $object['wpjobportallt'] = "salaryrangetype";
                break;
            case 'currencies' : $object['page'] = "currency";
                $object['wpjobportallt'] = "currency";
                break;
            case 'experiences' : $object['page'] = "experience";
                $object['wpjobportallt'] = "experience";
                break;
            case 'heighesteducation' : $object['page'] = "highesteducation";
                $object['wpjobportallt'] = "highesteducations";
                break;
            case 'categories' : $object['page'] = "category";
                $object['wpjobportallt'] = "categories";
                break;
            case 'subcategories' :
                $object['page'] = "subcategory";
                $wpjobportal_categoryid = get_option("wpjobportal_sub_categoryid");
                $object['wpjobportallt'] = "subcategories&categoryid=" . $wpjobportal_categoryid;
                break;
            default : $object['page'] = $object['wpjobportallt'] = $for;
                break;
        }
        return $object;
    }

    function savenewinwpjobportal() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_new_in_jobportal_nonce') ) {
             die( 'Security check Failed' );
        }
        if(current_user_can( 'manage_options' )){ // if current user is admin{
             die( 'Not Allowed' );
        }
        $wpjobportal_data = WPJOBPORTALrequest::get('post');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('common')->saveNewInWPJOBPORTAL($wpjobportal_data);
        if ($wpjobportal_data['desired_module'] == 'common' && $wpjobportal_data['desired_layout'] == 'newinwpjobportal') {
            if (WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()) {
                $wpjobportal_data['desired_module'] = 'job seeker';
            } else {
                $wpjobportal_data['desired_module'] = 'employer';
            }
            $wpjobportal_data['desired_layout'] = 'controlpanel';
        }
        $wpjobportal_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>$wpjobportal_data['desired_module'], 'wpjobportallt'=>$wpjobportal_data['desired_layout']));
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'userrole');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        wp_redirect($wpjobportal_url);
        die();
    }

    // can only be called internaly to force user as jobseeker
    private function forcecurrentusertojobsseker() {
        if(current_user_can( 'manage_options' )){ // if current user is admin{
             die( 'Not Allowed' );
        }
        if(!is_user_logged_in()){ // only case for wordpress logged in user
             die( 'Not Allowed' );
        }
        if(!WPJOBPORTALincluder::getObjectClass('user')->isguest()){ // current user must be our system visitor
             die( 'Not Allowed' );
        }

        $wpjobportal_data = [];
        $wpjobportal_data['roleid'] = 2;
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('common')->saveNewInWPJOBPORTAL($wpjobportal_data);

        $wpjobportal_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobseeker', 'wpjobportallt'=>'controlpanel'));
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'userrole');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],'common');
        wp_redirect($wpjobportal_url);
        die();
    }


    function wpjobportal_synchronize_ai_search_data() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'synchronize_ai_search_data') ) {
            die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) { //only admin can DO it.
            return false;
        }
        WPJOBPORTALincluder::getJSModel('common')->updateRecordsForAISearch();
        $wpjobportal_msgkey = WPJOBPORTALincluder::getJSModel('wpjobportal')->getMessagekey();
        WPJOBPORTALMessages::setLayoutMessage(__('Database update completed', 'wp-job-portal'), "updated",$wpjobportal_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal"));
        wp_redirect($wpjobportal_url);
        die();
    }

}

$WPJOBPORTALCommonController = new WPJOBPORTALCommonController;
?>
