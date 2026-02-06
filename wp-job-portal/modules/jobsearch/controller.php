<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALjobSearchController {

    private $_msgkey;

    function __construct() {

        self::handleRequest();

        $this->_msgkey = WPJOBPORTALincluder::getJSModel('jobsearch')->getMessagekey();
    }

    function handleRequest() {

        $wpjobportal_layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'jobsavesearch');
        if (self::canaddfile($wpjobportal_layout)) {
            $wpjobportal_config_array = WPJOBPORTALincluder::getJSModel('configuration')->getConfigByFor('jscontrolpanel');
            switch ($wpjobportal_layout) {
                case 'jobsearch':
                    try {
                        if (WPJOBPORTALincluder::getObjectClass('user')->isjobseeker() || WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('visitorview_js_jobsearch') == 1 ) {
                            WPJOBPORTALincluder::getJSModel('jobsearch')->getJobSearchOptions();
                        } else {
                            if (WPJOBPORTALincluder::getObjectClass('user')->isemployer()) {
                                wpjobportal::$_error_flag_message_for=3;
                                throw new Exception(WPJOBPORTALLayout::setMessageFor(3,null,null,1));
                            } elseif (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
                                $wpjobportal_link = WPJOBPORTALincluder::getJSModel('common')->jsMakeRedirectURL('jobsearch', $wpjobportal_layout, 1);
                                $wpjobportal_linktext = esc_html(__('Login','wp-job-portal'));
                                wpjobportal::$_error_flag_message_for=1;
                                wpjobportal::$_error_flag_message_register_for=1;
                                throw new Exception(WPJOBPORTALLayout::setMessageFor(1 , $wpjobportal_link , $wpjobportal_linktext,1));

                            } elseif (!WPJOBPORTALincluder::getObjectClass('user')->isWPJOBPortalUser()) {
                                $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'common', 'wpjobportallt'=>'newinwpjobportal', 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                                $wpjobportal_linktext = esc_html(__('Select role','wp-job-portal'));
                                wpjobportal::$_error_flag_message_for=9;
                                throw new Exception(WPJOBPORTALLayout::setMessageFor(9 , $wpjobportal_link , $wpjobportal_linktext,1));
                            }
                            if(isset($wpjobportal_link) && isset($wpjobportal_linktext)){
                                wpjobportal::$_error_flag_message_for_link = $wpjobportal_link;
                                wpjobportal::$_error_flag_message_for_link_text = $wpjobportal_linktext;
                            }
                        }
                    } catch (Exception $wpjobportal_ex) {
                        wpjobportal::$_error_flag = true;
                        wpjobportal::$_error_flag_message = $wpjobportal_ex->getMessage();
                    }

                    break;
                case 'jobsavesearch':
                    try {
                        if (WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()) {
                        $wpjobportal_userid = WPJOBPORTALincluder::getObjectClass('user')->uid();
                        WPJOBPORTALincluder::getJSModel('jobsearch')->getMyJobSaveSearchbyUid($wpjobportal_userid);
                    } else {
                        if (WPJOBPORTALincluder::getObjectClass('user')->isemployer()) {
                            wpjobportal::$_error_flag_message_for=3;
                            throw new Exception(WPJOBPORTALLayout::setMessageFor(3,null,null,1));

                        } elseif (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
                            $wpjobportal_link = WPJOBPORTALincluder::getJSModel('common')->jsMakeRedirectURL('jobsearch', $wpjobportal_layout, 1);
                            $wpjobportal_linktext = esc_html(__('Login','wp-job-portal'));
                            wpjobportal::$_error_flag_message_for=1;
                            throw new Exception(WPJOBPORTALLayout::setMessageFor(1 , $wpjobportal_link , $wpjobportal_linktext,1));

                        } elseif (!WPJOBPORTALincluder::getObjectClass('user')->isWPJOBPortalUser()) {
                            $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'common', 'wpjobportallt'=>'newinwpjobportal', 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                            $wpjobportal_linktext = esc_html(__('Select role','wp-job-portal'));
                            wpjobportal::$_error_flag_message_for=9;
                            throw new Exception(WPJOBPORTALLayout::setMessageFor(9 , $wpjobportal_link , $wpjobportal_linktext,1));

                        }
                            if(isset($wpjobportal_link) && isset($wpjobportal_linktext)){
                                wpjobportal::$_error_flag_message_for_link = $wpjobportal_link;
                                wpjobportal::$_error_flag_message_for_link_text = $wpjobportal_linktext;
                            }
                        }
                    } catch (Exception $wpjobportal_ex) {
                        wpjobportal::$_error_flag = true;
                        wpjobportal::$_error_flag_message = $wpjobportal_ex->getMessage();
                    }

                    break;
                default:
                    return;
            }
            $wpjobportal_module = (wpjobportal::$_common->wpjp_isadmin()) ? 'page' : 'wpjobportalme';
            $wpjobportal_module = WPJOBPORTALrequest::getVar($wpjobportal_module, null, 'jobsearch');
            $wpjobportal_module = wpjobportalphplib::wpJP_str_replace('wpjobportal_', '', $wpjobportal_module);
            if(is_numeric($wpjobportal_module)){
                $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme', null, 'jobsearch');
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

$WPJOBPORTALjobSearchController = new WPJOBPORTALjobSearchController();
?>
