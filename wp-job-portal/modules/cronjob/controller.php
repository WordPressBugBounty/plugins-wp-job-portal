<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALCronJobController {

    private $_msgkey;

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'cronjob');
        if (self::canaddfile($layout)) {
            switch ($layout) {
            case 'admin_cronjob':
                    WPJOBPORTALincluder::getJSModel('cronjob')->getCronKey();
                    break;
            }
            $module = (wpjobportal::$_common->wpjp_isadmin()) ? 'page' : 'wpjobportalme';
            $module = WPJOBPORTALrequest::getVar($module, null, 'cronjob');
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

   

}

$WPJOBPORTALCronJobController = new WPJOBPORTALCronJobController();
?>
