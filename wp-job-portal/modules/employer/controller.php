<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALEmployerController {
    private $_msgkey;
    function __construct() {
        self::handleRequest();
        $this->_msgkey = WPJOBPORTALincluder::getJSModel('employer')->getMessagekey();
    }

    function handleRequest() {
        $wpjobportal_layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'controlpanel');
        $wpjobportal_addonmissing = WPJOBPORTALrequest::getLayout('addonmissing', null, 0);
        if (self::canaddfile($wpjobportal_layout)) {
            $wpjobportal_empflag  = wpjobportal::$_config->getConfigurationByConfigName('disable_employer');
            $wpjobportal_guestflag = false;
            $wpjobportal_visitorallowed = wpjobportal::$_config->getConfigurationByConfigName('visitorview_emp_conrolpanel');
            $wpjobportal_isouruser = WPJOBPORTALincluder::getObjectClass('user')->isWPJOBPortalUser();
            $wpjobportal_isguest = WPJOBPORTALincluder::getObjectClass('user')->isguest();

            if($wpjobportal_isguest == true && $wpjobportal_visitorallowed == true){
                $wpjobportal_guestflag = true;
            }
            if($wpjobportal_isguest == false && $wpjobportal_isouruser == false && $wpjobportal_visitorallowed == true){
                $wpjobportal_guestflag = true;
            }
            $wpjobportal_hide_error_message = 0; // to handle the case of showing two separate error layouts for same user/case
            switch ($wpjobportal_layout) {
                case 'employer_report':
                    break;
                case 'controlpanel':
                    try {
                        if($wpjobportal_addonmissing == 1){
                            wpjobportal::$_error_flag_message_for=18;
                            throw new Exception(WPJOBPORTALLayout::setMessageFor(18 , '' ,'',1));
                            $wpjobportal_hide_error_message = 1;
                        }
                        if (wpjobportal::$_common->wpjp_isadmin() || (WPJOBPORTALincluder::getObjectClass('user')->isemployer() && $wpjobportal_empflag == 1 || $wpjobportal_guestflag == true)) {
                            $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
                            wpjobportal::$_data['config'] = wpjobportal::$_config->getConfigByFor('emcontrolpanel');
                            WPJOBPORTALincluder::getJSModel('employer')->getLatestResumeIdNew($wpjobportal_uid);
                            WPJOBPORTALincluder::getJSModel('employer')->getEmployerinfo($wpjobportal_uid);
                            if(in_array('credits', wpjobportal::$_active_addons)){
                                WPJOBPORTALincluder::getJSModel('employer')->getDataForDashboard($wpjobportal_uid);
                            }
                           WPJOBPORTALincluder::getJSModel('employer')->getGraphDataNew($wpjobportal_uid);
                           // handle shortcode options to manage section visiblity
                           WPJOBPORTALincluder::getJSModel('employer')->handleShortCodeOptions();
                       } else {
                                if (WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()) {
                                    $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobseeker', 'wpjobportallt'=>'controlpanel'));
                                    $wpjobportal_linktext = esc_html(__('Go Back To Home','wp-job-portal'));
                                    wpjobportal::$_error_flag_message_for = 2;
                                    throw new Exception(WPJOBPORTALLayout::setMessageFor(2,$wpjobportal_link,$wpjobportal_linktext,1));
                                    $wpjobportal_hide_error_message = 1;
                                } elseif (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
                                    $wpjobportal_link = WPJOBPORTALincluder::getJSModel('common')->jsMakeRedirectURL('employer', $wpjobportal_layout, 1);
                                    $wpjobportal_linktext = esc_html(__('Login','wp-job-portal'));
                                   wpjobportal::$_error_flag_message_for = 1;
                                    throw new Exception(WPJOBPORTALLayout::setMessageFor(1 , $wpjobportal_link , $wpjobportal_linktext,1));
                                    $wpjobportal_hide_error_message = 1;
                                } elseif (!WPJOBPORTALincluder::getObjectClass('user')->isWPJOBPortalUser()) {
                                    $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'common', 'wpjobportallt'=>'newinwpjobportal'));
                                    $wpjobportal_linktext = esc_html(__('Select role','wp-job-portal'));
                                    wpjobportal::$_error_flag_message_for = 9;
                                    throw new Exception(WPJOBPORTALLayout::setMessageFor(9 , $wpjobportal_link , $wpjobportal_linktext,1));
                                    $wpjobportal_hide_error_message = 1;
                                }
                                if(isset($wpjobportal_link) && isset($wpjobportal_linktext)){
                                    wpjobportal::$_error_flag_message_for_link = $wpjobportal_link;
                                    wpjobportal::$_error_flag_message_for_link_text = $wpjobportal_linktext;
                                }
                            }
                        } catch (Exception $wpjobportal_ex) {
                             wpjobportal::$_error_flag = true;
                             wpjobportal::$_error_flag_message = $wpjobportal_ex->getMessage();
                             $wpjobportal_hide_error_message = 1;
                        }
                    break;
                default:
                    return;
                }
            if ($wpjobportal_empflag == 0 && $wpjobportal_hide_error_message == 0) {
                WPJOBPORTALLayout::setMessageFor(5);
                wpjobportal::$_error_flag = true;
            }
            $wpjobportal_module = (wpjobportal::$_common->wpjp_isadmin()) ? 'page' : 'wpjobportalme';
            $wpjobportal_module = WPJOBPORTALrequest::getVar($wpjobportal_module, null, 'employer');
            $wpjobportal_module = wpjobportalphplib::wpJP_str_replace('wpjobportal_', '', $wpjobportal_module);
            if(is_numeric($wpjobportal_module)){
                $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme', null, 'employer');
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

}

$WPJOBPORTALEmployerController = new WPJOBPORTALEmployerController();
?>
