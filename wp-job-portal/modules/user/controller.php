<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALUserController {

    private $_msgkey;

    function __construct() {

        self::handleRequest();

        $this->_msgkey = WPJOBPORTALincluder::getJSModel('user')->getMessagekey();
    }

    function handleRequest() {
        $wpjobportal_layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'users');
        if (self::canaddfile($wpjobportal_layout)) {
            switch ($wpjobportal_layout) {
                case 'admin_users':
                    WPJOBPORTALincluder::getJSModel('user')->getAllUsers();
                    break;
                case 'admin_changerole':
                    $wpjobportal_id = WPJOBPORTALrequest::getVar('wpjobportalid');
                    WPJOBPORTALincluder::getJSModel('user')->getChangeRolebyId($wpjobportal_id);
                    break;
                case 'admin_userdetail':
                    $wpjobportal_id = WPJOBPORTALrequest::getVar('id');
                    WPJOBPORTALincluder::getJSModel('user')->getUserData($wpjobportal_id);
                    break;
                case 'admin_userstate_companies':
                    $wpjobportal_companyuid = WPJOBPORTALrequest::getVar('md');
                    $wpjobportal_result = WPJOBPORTALincluder::getJSModel('user')->getUserStatsCompanies($wpjobportal_companyuid);
                    break;
                case 'formprofile':
                    if( WPJOBPORTALincluder::getObjectClass('user')->isguest() ){
                        $wpjobportal_link = WPJOBPORTALincluder::getJSModel('common')->jsMakeRedirectURL('employer', $wpjobportal_layout, 1);
                        $wpjobportal_linktext = esc_html(__('Login','wp-job-portal'));
                        wpjobportal::$_error_flag_message_for = 1;
                        wpjobportal::$_error_flag_message = WPJOBPORTALLayout::setMessageFor(1 , $wpjobportal_link , $wpjobportal_linktext,1);
                        // wpjobportal::$_error_flag_message = WPJOBPORTAL_GUEST;
                    }else{
                        $wpjobportal_id = WPJOBPORTALincluder::getObjectClass('user')->uid();
                        WPJOBPORTALincluder::getJSModel('user')->getUserForForm($wpjobportal_id);
                    }
                    break;
                case 'regemployer':
                case 'regjobseeker':
                case 'userregister':
                    $wpjobportal_allow_reg_as_emp = wpjobportal::$_config->getConfigurationByConfigName('showemployerlink');
                    $cpfrom = 0;
                    if($wpjobportal_layout!="userregister"){
                        if ($wpjobportal_layout == 'regemployer') {
                            if ($wpjobportal_allow_reg_as_emp == 1) {
                                $cpfrom = 1;
                            } else {
                                $cpfrom = 2;
                            }
                        } else {
                            $cpfrom = 2;
                        }
                        $_SESSION['js_cpfrom'] = $cpfrom;
                        $wpjobportal_layout = 'userregister';
                    }

                    $wpjobportal_layout = 'userregister';
                    if($cpfrom != 0){
                        $_SESSION['js_cpfrom'] = $cpfrom;
                    }

                    break;
                case 'admin_assignrole': // to avoid default case
                    break;
                default:
                    return;
            }
            $wpjobportal_module = (wpjobportal::$_common->wpjp_isadmin()) ? 'page' : 'wpjobportalme';
            $wpjobportal_module = WPJOBPORTALrequest::getVar($wpjobportal_module, null, 'user');
            $wpjobportal_module = wpjobportalphplib::wpJP_str_replace('wpjobportal_', '', $wpjobportal_module);
            if(is_numeric($wpjobportal_module)){
                $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme', null, 'user');
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

    function saveuserrole() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_user_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_data = WPJOBPORTALrequest::get('post');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('user')->storeUserRole($wpjobportal_data);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'userrole');
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_user&wpjobportallt=users"));
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        wp_redirect($wpjobportal_url);
        die();
    }

     function saveuser() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_user_nonce') ) {
             die( 'Security check Failed' );
        }

        $wpjobportal_data = WPJOBPORTALrequest::get('post');
        if( !wpjobportal::$_common->wpjp_isadmin() ){
            $wpjobportal_data['id'] = WPJOBPORTALincluder::getObjectClass('user')->uid();
        }
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('user')->storeUser($wpjobportal_data);
        if( wpjobportal::$_common->wpjp_isadmin() ){
            $wpjobportal_msg = WPJOBPORTALmessages::getMessage($wpjobportal_result, 'user');
            WPJOBPORTALmessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
            $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_user&wpjobportallt=users"));
        }else{
            if (WPJOBPORTALincluder::getObjectClass('user')->isemployer()) {
                $wpjobportal_userrole = 1;
                $wpjobportal_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'employer', 'wpjobportallt'=>'controlpanel',"wpjobportalpageid"=>wpjobportal::wpjobportal_getPageid()));
                $wpjobportal_usermsgrole = "employer";
            } elseif (WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()) {
                $wpjobportal_userrole = 2;
                $wpjobportal_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobseeker', 'wpjobportallt'=>'controlpanel',"wpjobportalpageid"=>wpjobportal::wpjobportal_getPageid()));
                $wpjobportal_usermsgrole = "jobseeker";
            }
            $wpjobportal_msg = WPJOBPORTALmessages::getMessage($wpjobportal_result, $wpjobportal_usermsgrole);
            WPJOBPORTALmessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],WPJOBPORTALincluder::getJSModel($wpjobportal_usermsgrole)->getMessagekey());
        }
        wp_redirect($wpjobportal_url);
        die();
    }

    function assignuserrole() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_user_nonce') ) {
             die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $wpjobportal_data = WPJOBPORTALrequest::get('post');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('user')->assignUserRole($wpjobportal_data);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'userrole');
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_user&wpjobportallt=users"));
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        wp_redirect($wpjobportal_url);
        die();
    }

    function changeuserstatus() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_user_nonce') ) {
             die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $wpjobportal_userid = WPJOBPORTALrequest::getVar('wpjobportalid');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('user')->changeUserStatus($wpjobportal_userid);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'user');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_detail = WPJOBPORTALrequest::getVar('detail');
        if($wpjobportal_detail == 1){
            $wpjobportal_url = esc_url_raw(admin_url('admin.php?page=wpjobportal_user&wpjobportallt=userdetail&id='.esc_attr($wpjobportal_userid)));
        }else{
            $wpjobportal_url = esc_url_raw(admin_url('admin.php?page=wpjobportal_user&wpjobportallt=users'));
        }
        wp_redirect($wpjobportal_url);
        die();
    }

    function deleteuser() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_user_nonce') ) {
             die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $wpjobportal_userid = WPJOBPORTALrequest::getVar('wpjobportalid');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('user')->deleteUser($wpjobportal_userid);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'user');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url('admin.php?page=wpjobportal_user&wpjobportallt=users'));
        wp_redirect($wpjobportal_url);
        die();
    }

    function enforcedeleteuser() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_user_nonce') ) {
             die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $wpjobportal_userid = WPJOBPORTALrequest::getVar('wpjobportalid');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('user')->enforceDeleteUser($wpjobportal_userid);
        //var_dump($wpjobportal_result); die();
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'user');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url('admin.php?page=wpjobportal_user&wpjobportallt=users'));
        wp_redirect($wpjobportal_url);
        die();
    }

}

$WPJOBPORTALUserController = new WPJOBPORTALUserController();
?>
