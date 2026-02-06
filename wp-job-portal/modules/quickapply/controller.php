<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALquickapplyController {

    private $_msgkey;

    function __construct() {
        self::handleRequest();
        $this->_msgkey = WPJOBPORTALincluder::getJSModel('job')->getMessagekey();
    }

    function handleRequest() {
        $wpjobportal_layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'job');
        if (self::canaddfile($wpjobportal_layout)) {
            return;// this module does not have any layout at the moment
            $wpjobportal_module = (wpjobportal::$_common->wpjp_isadmin()) ? 'page' : 'wpjobportalme';
            $wpjobportal_module = WPJOBPORTALrequest::getVar($wpjobportal_module, null, 'job');
            $wpjobportal_module = wpjobportalphplib::wpJP_str_replace('wpjobportal_', '', $wpjobportal_module);
            WPJOBPORTALincluder::include_file($wpjobportal_layout, $wpjobportal_module);
        }
    }

    function canaddfile($wpjobportal_layout) {
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

    function addtoquickapply() {

        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if(! wp_verify_nonce( $wpjobportal_nonce, 'copy-job')) {
            die( 'Security check Failed' );
        }
        $wpjobportal_id = WPJOBPORTALrequest::getVar('wpjobportalid');
        $wpjobportal_action = "job";
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('quickapply')->quickapply($wpjobportal_id,$wpjobportal_action);

        if($wpjobportal_result == WPJOBPORTAL_SAVED){
           //WPJOBPORTALmessages::setLayoutMessage(__("Job copy successfully",'wp-job-portal'),'updated',$this->_msgkey);
        }else{
            WPJOBPORTALmessages::setLayoutMessage(__("There was some problem performing action",'wp-job-portal'),'error',$this->_msgkey);
        }
        if(wpjobportal::$_common->wpjp_isadmin()){
            $wpjobportal_url = admin_url("admin.php?page=wpjobportal_job&wpjobportal=jobs");
        }else{
            $wpjobportal_url = array('wpjobportalme'=>'job','wpjobportallt'=>'myjobs');
            $wpjobportal_url = wpjobportal::wpjobportal_makeUrl($wpjobportal_url);
        }
        wp_redirect($wpjobportal_url);
        die();
    }

    function quickapplyonjob() {

        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if(! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_quick_apply_nonce')) {
            die( 'Security check Failed' );
        }

        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('quickapply')->quickApplyOnJob();
        $wpjobportal_jobid = WPJOBPORTALrequest::getVar('jobid');
        $page_id = WPJOBPORTALrequest::getVar('wpjobportalpageid');
        if($wpjobportal_result == WPJOBPORTAL_SAVED){
           WPJOBPORTALmessages::setLayoutMessage(__("Successfully applied on job",'wp-job-portal'),'updated','job');
        }else{
            WPJOBPORTALmessages::setLayoutMessage(__("There was some problem performing action",'wp-job-portal'),'error','job');
        }

        $wpjobportal_url = array('wpjobportalme'=>'job','wpjobportallt'=>'viewjob','wpjobportalid'=>$wpjobportal_jobid,'wpjobportalpageid'=>$page_id);
        $wpjobportal_url = wpjobportal::wpjobportal_makeUrl($wpjobportal_url);

        wp_redirect(esc_url_raw($wpjobportal_url));
        die();
    }

}

$WPJOBPORTALquickapplyController = new WPJOBPORTALquickapplyController();
?>
