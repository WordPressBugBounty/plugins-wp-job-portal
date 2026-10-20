<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALallcompaniesController {

    private $_msgkey;

    function __construct() {
        self::handleRequest();
        $this->_msgkey = WPJOBPORTALincluder::getJSModel('job')->getMessagekey();
    }

    function handleRequest() {
        $layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'allcompanies');
        if (self::canaddfile($layout)) {
            $layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'companies');
            $uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
            if (self::canaddfile($layout)) {
                $empflag  = wpjobportal::$_config->getConfigurationByConfigName('disable_employer');
                $string = "'jscontrolpanel','emcontrolpanel','visitor'" ;
                $config_array = wpjobportal::$_config->getConfigurationByConfigForMultiple($string);
                switch ($layout) {
                    case 'companies':
                            WPJOBPORTALincluder::getJSModel('company')->getCompanies();
                            $empflag = 1;
                        break;
                }
                if (WPJOBPORTALincluder::getObjectClass('user')->isemployer() || (('mycompanies' == $layout)  || ('addcompany' == $layout)) ) {
                    if ($empflag == 0) {
                        WPJOBPORTALLayout::setMessageFor(5);
                        wpjobportal::$_error_flag_message_for=5;
                        wpjobportal::$_error_flag = true;
                    }
                }
                if(!isset($module)){
                    $module = (wpjobportal::$_common->wpjp_isadmin()) ? 'page' : 'wpjobportalme';
                    $module = WPJOBPORTALrequest::getVar($module, null, 'allcompanies');
                }
                $module = wpjobportalphplib::wpJP_str_replace('wpjobportal_', '', $module);
                if(is_numeric($module)){
                    $module = WPJOBPORTALrequest::getVar('wpjobportalme', null, 'allcompanies');
                }
                WPJOBPORTALincluder::include_file($layout, $module);
            }


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

$WPJOBPORTALallcompaniesController = new WPJOBPORTALallcompaniesController();
?>
