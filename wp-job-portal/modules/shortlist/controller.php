<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALshortlistController {

    private $_msgkey;

    function __construct() {
        self::handleRequest();
        $this->_msgkey = WPJOBPORTALincluder::getJSModel('job')->getMessagekey();  
    }

    function handleRequest() {
        $layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'jobs');
        $uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        if (self::canaddfile($layout)) {
            $empflag  = wpjobportal::$_config->getConfigurationByConfigName('disable_employer');
            $string = "'jscontrolpanel','emcontrolpanel','visitor'" ;
            $config_array = wpjobportal::$_config->getConfigurationByConfigForMultiple($string);
            switch ($layout) {
                case 'shortlistedjobs':
                	WPJOBPORTALincluder::getJSModel('shortlist')->getShortlistedJobs($uid);
                    $empflag = 1;
                    break;
            }

            if ($empflag == 0) {
                WPJOBPORTALLayout::setMessageFor(5);
                wpjobportal::$_error_flag_message_for=5;
                wpjobportal::$_error_flag = true;
            }
            $module = (is_admin()) ? 'page' : 'wpjobportalme';
            $module = WPJOBPORTALrequest::getVar($module, null, 'shortlist');
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
    function removeshortlist() { 
        $nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'wpjobportal_shortlist_job_nonce') ) {
             die( 'Security check Failed' ); 
        }
        $id = WPJOBPORTALrequest::getVar('wpjobportalid');
        $callfrom = WPJOBPORTALrequest::getVar('callfrom','',0);
        $result = WPJOBPORTALincluder::getJSModel('shortlist')->deleteShortlistedJobById($id);
        $msg = WPJOBPORTALMessages::getMessage($result, 'shortlisted');
        WPJOBPORTALMessages::setLayoutMessage($msg['message'], $msg['status'],$this->_msgkey);
        if($callfrom == 0){
            $url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'shortlist', 'wpjobportallt'=>'shortlistedjobs','wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
        }else{
            $url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobseeker', 'wpjobportallt'=>'controlpanel','wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
        }
        wp_safe_redirect($url);
        die();
    }
}
$WPJOBPORTALshortlistController = new WPJOBPORTALshortlistController();
?>
