<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALJobseekerController {

    function __construct() {

        self::handleRequest();
    }

    function handleRequest() {
        $wpjobportal_layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'controlpanel');
        $wpjobportal_addonmissing = WPJOBPORTALrequest::getLayout('addonmissing', null, 0);
        if (self::canaddfile($wpjobportal_layout)) {
            switch ($wpjobportal_layout) {
                case 'jobseeker_report':
                    break;
                case 'controlpanel':
					// temporary code to avoid any resume error.
                    include_once WPJOBPORTAL_PLUGIN_PATH . 'includes/updates/updates.php';
                    WPJOBPORTALupdates::checkUpdates();
					
                    if(get_option( 'wpjobportal_apply_visitor', '' ) != '')
                        delete_option( 'wpjobportal_apply_visitor' );
                    $wpjobportal_visitorview_js_controlpanel = wpjobportal::$_config->getConfigurationByConfigName('visitorview_js_controlpanel');
                    try {
                        if($wpjobportal_addonmissing == 1){
                            wpjobportal::$_error_flag_message_for=18;
                            throw new Exception(WPJOBPORTALLayout::setMessageFor(18 , '' ,'',1));
                        }
                        if ($wpjobportal_visitorview_js_controlpanel != 1) {
                            if (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
                                $wpjobportal_link = WPJOBPORTALincluder::getJSModel('common')->jsMakeRedirectURL('jobseeker', $wpjobportal_layout, 1);
                                $wpjobportal_linktext = esc_html(__('Login','wp-job-portal'));
                                wpjobportal::$_error_flag_message_for=1;
                                wpjobportal::$_error_flag_message_register_for=1; // register as jobseeker
                                throw new Exception(WPJOBPORTALLayout::setMessageFor(1 , $wpjobportal_link , $wpjobportal_linktext,1));
                            } elseif (!WPJOBPORTALincluder::getObjectClass('user')->isWPJOBPortalUser()) {
                                $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'common', 'wpjobportallt'=>'newinwpjobportal',  'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                                $wpjobportal_linktext = esc_html(__('Select role','wp-job-portal'));
                                wpjobportal::$_error_flag_message_for=1;
                                throw new Exception(WPJOBPORTALLayout::setMessageFor(9 , $wpjobportal_link , $wpjobportal_linktext,1));
                            }
                        }
                        if (WPJOBPORTALincluder::getObjectClass('user')->isemployer()) {
                            $wpjobportal_employerview_js_controlpanel = wpjobportal::$_config->getConfigurationByConfigName('employerview_js_controlpanel');
                            if ($wpjobportal_employerview_js_controlpanel != 1){
                                wpjobportal::$_error_flag_message_for=7;
                                throw new Exception(WPJOBPORTALLayout::setMessageFor(7,null,null,1));
                            }
                        }
                        if(isset($wpjobportal_link) && isset($wpjobportal_linktext)){
                            wpjobportal::$_error_flag_message_for_link = $wpjobportal_link;
                            wpjobportal::$_error_flag_message_for_link_text = $wpjobportal_linktext;
                        }

                    } catch (Exception $wpjobportal_ex) {
                        wpjobportal::$_error_flag = true;
                        wpjobportal::$_error_flag_message = $wpjobportal_ex->getMessage();
                    }
                    //code for user related jobs
                    $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
                    WPJOBPORTALincluder::getJSModel('jobseeker')->getResumeStatusByUid($wpjobportal_uid);
                    WPJOBPORTALincluder::getJSModel('jobseeker')->getConfigurationForControlPanel();
                    WPJOBPORTALincluder::getJSModel('jobseeker')->getLatestJobs();
                    WPJOBPORTALincluder::getJSModel('jobseeker')->getJobsAppliedRecently($wpjobportal_uid);
                    WPJOBPORTALincluder::getJSModel('jobseeker')->getUserinfo($wpjobportal_uid);
                    WPJOBPORTALincluder::getJSModel('jobseeker')->getJobsekerResumeTitle($wpjobportal_uid);
                    WPJOBPORTALincluder::getJSModel('jobseeker')->getGraphDataNew($wpjobportal_uid);
                    // handle shortcode options to manage section visiblity
                    WPJOBPORTALincluder::getJSModel('jobseeker')->handleShortCodeOptions();
                    if(in_array('credits', wpjobportal::$_active_addons)){
                        WPJOBPORTALincluder::getJSModel('employer')->getDataForDashboard($wpjobportal_uid);
                    }
                    // data in this function also prepared above but casues issue on other layouts where left menu is added so changed it
                    WPJOBPORTALincluder::getJSModel('jobseeker')->getResumeInfoForJobSeekerLeftMenu($wpjobportal_uid);
                    // data for new sections
                    if (WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()) {
                        WPJOBPORTALincluder::getJSModel('jobseeker')->getJobSeekerResumeData($wpjobportal_uid);
                    }
                    break;
                default:
                    return;
            }
            $wpjobportal_module = (wpjobportal::$_common->wpjp_isadmin()) ? 'page' : 'wpjobportalme';
            $wpjobportal_module = WPJOBPORTALrequest::getVar($wpjobportal_module, null, 'jobseeker');
            $wpjobportal_module = wpjobportalphplib::wpJP_str_replace('wpjobportal_', '', $wpjobportal_module);
            if(is_numeric($wpjobportal_module)){
                $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme', null, 'jobseeker');
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

$WPJOBPORTALJobseekerController = new WPJOBPORTALJobseekerController();
?>
