<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALcopyjobController {

    private $_msgkey;

    function __construct() {
        self::handleRequest();
        $this->_msgkey = WPJOBPORTALincluder::getJSModel('job')->getMessagekey();
    }

    function handleRequest() {
        $layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'job');
        if (self::canaddfile($layout)) {
            $module = (wpjobportal::$_common->wpjp_isadmin()) ? 'page' : 'wpjobportalme';
            $module = WPJOBPORTALrequest::getVar($module, null, 'job');
            $module = wpjobportalphplib::wpJP_str_replace('wpjobportal_', '', $module);
            WPJOBPORTALincluder::include_file($layout, $module);
        }
    }

    function canaddfile($layout) {
        if (WPJOBPORTALrequest::getVar('form_request', 'post', '') == 'wpjobportal')
            return false;
        elseif (WPJOBPORTALrequest::getVar('action', 'get', '') == 'wpjobportaltask')
            return false;
        else{
                if(!is_admin() && strpos($layout, 'admin_') === 0){
                    return false;
                }
                return true;
            }
    }

    function addtocopyJob() {

        $nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if(! wp_verify_nonce( $nonce, 'copy-job')) {
            die( 'Security check Failed' );
        }
        $id = WPJOBPORTALrequest::getVar('wpjobportalid');
        $action = "job";
        $result = WPJOBPORTALincluder::getJSModel('copyjob')->copyJob($id,$action);

        if($result == WPJOBPORTAL_SAVED){
           //WPJOBPORTALmessages::setLayoutMessage(__('Job Copied Successfully', 'wp-job-portal'),'updated',$this->_msgkey);
        }else{
            WPJOBPORTALmessages::setLayoutMessage(__("There was some problem performing action",'wp-job-portal'),'error',$this->_msgkey);
        }
        if(wpjobportal::$_common->wpjp_isadmin()){
            $url = admin_url("admin.php?page=wpjobportal_job&wpjobportal=jobs");
        }else{
            $url = array('wpjobportalme'=>'job','wpjobportallt'=>'myjobs');
            $url = wpjobportal::wpjobportal_makeUrl($url);
        }
        wp_safe_redirect($url);
        die();
    }
}

$WPJOBPORTALcopyjobController = new WPJOBPORTALcopyjobController();
?>
