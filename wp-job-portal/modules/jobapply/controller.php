<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALJobapplyController {

    private $_msgkey;

    function __construct() {

        self::handleRequest();

        $this->_msgkey = WPJOBPORTALincluder::getJSModel('jobapply')->getMessagekey();
    }

    function handleRequest() {
        $wpjobportal_layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'appliedresumes');
        if (self::canaddfile($wpjobportal_layout)) {
            switch ($wpjobportal_layout) {
                case 'admin_appliedresumes':
                    WPJOBPORTALincluder::getJSModel('jobapply')->getAppliedResume();
                    break;
                case 'myappliedjobs':
                    try {
                        $conflag = wpjobportal::$_config->getConfigurationByConfigName('myappliedjobs');
                        if (WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()) {
                            $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
                            WPJOBPORTALincluder::getJSModel('jobapply')->getMyAppliedJobs($wpjobportal_uid);
                            // to handle jobseeker left menu data
                            WPJOBPORTALincluder::getJSModel('jobseeker')->getResumeInfoForJobSeekerLeftMenu($wpjobportal_uid);
                        } else {
                            if (WPJOBPORTALincluder::getObjectClass('user')->isemployer()) {
                                wpjobportal::$_error_flag_message_for=3;
                                throw new Exception(WPJOBPORTALLayout::setMessageFor(3,null,null,1));

                            } elseif (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
                                $wpjobportal_link = wpjobportal::$_common->jsMakeRedirectURL('jobapply', $wpjobportal_layout, 1);
                                $wpjobportal_linktext = esc_html(__('Login','wp-job-portal'));
                                wpjobportal::$_error_flag_message_for=1;
                                wpjobportal::$_error_flag_message_register_for=1; // register as jobseeker
                                throw new Exception(WPJOBPORTALLayout::setMessageFor(1 , $wpjobportal_link , $wpjobportal_linktext,1));

                            } elseif (!WPJOBPORTALincluder::getObjectClass('user')->isWPJOBPortalUser()) {
                                $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'common', 'wpjobportallt'=>'newinwpjobportal'));
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
                        if(isset($wpjobportal_link) && isset($wpjobportal_linktext) && wpjobportal::$wpjobportal_theme_chk == 1){
                            wpjobportal::$_error_flag_message_for_link=$wpjobportal_link;
                            wpjobportal::$_error_flag_message_for_link_text=$wpjobportal_linktext;
                        }


                    }

                    break;
                case 'jobappliedresume':
                case 'admin_jobappliedresume':
                    try {
                        if (WPJOBPORTALincluder::getObjectClass('user')->isemployer() || wpjobportal::$_common->wpjp_isadmin()) {
                            $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
                            $wpjobportal_jobid = WPJOBPORTALrequest::getVar('jobid');
                            $wpjobportal_tab_action = WPJOBPORTALrequest::getVar('ta', null, 1);
                            WPJOBPORTALincluder::getJSModel('jobapply')->getJobAppliedResume($wpjobportal_tab_action, $wpjobportal_jobid, $wpjobportal_uid);
                            wpjobportal::$_data['jobid'] = $wpjobportal_jobid;
                            wpjobportal::$_data['tab_action_value'] = $wpjobportal_tab_action;
                        } else {
                            if (WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()) {
                                wpjobportal::$_error_flag_message_for=2;
                                throw new Exception(WPJOBPORTALLayout::setMessageFor(2,null,null,1));

                            } elseif (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
                                $wpjobportal_link = wpjobportal::$_common->jsMakeRedirectURL('jobapply', $wpjobportal_layout, 1);
                                $wpjobportal_linktext = esc_html(__('Login','wp-job-portal'));
                                wpjobportal::$_error_flag_message_for=1;
                                wpjobportal::$_error_flag_message_register_for=2; // register as employer
                                throw new Exception(WPJOBPORTALLayout::setMessageFor(1 , $wpjobportal_link , $wpjobportal_linktext,1));

                            } elseif (!WPJOBPORTALincluder::getObjectClass('user')->isWPJOBPortalUser()) {
                                $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'common', 'wpjobportallt'=>'newinwpjobportal'));
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
                        if(isset($wpjobportal_link) && isset($wpjobportal_linktext) && wpjobportal::$wpjobportal_theme_chk == 1){
                            wpjobportal::$_error_flag_message_for_link=$wpjobportal_link;
                            wpjobportal::$_error_flag_message_for_link_text=$wpjobportal_linktext;
                        }
                    }
                    break;
                default:
                    return;
            }
            $wpjobportal_module = (wpjobportal::$_common->wpjp_isadmin()) ? 'page' : 'wpjobportalme';
            $wpjobportal_module = WPJOBPORTALrequest::getVar($wpjobportal_module, null, 'jobapply');
            $wpjobportal_module = wpjobportalphplib::wpJP_str_replace('wpjobportal_', '', $wpjobportal_module);
            if(is_numeric($wpjobportal_module)){
                $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme', null, 'jobapply');
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


    function jobapplyasvisitor() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_job_apply_nonce') ) {
             die( 'Security check Failed' );
        }
        $wpjobportal_jobid = WPJOBPORTALrequest::getVar('wpjobportalid-jobid');
        if (!is_numeric($wpjobportal_jobid)) { // redirect to jobs page if id is not numeric
            if (wpjobportal::$wpjobportal_theme_chk == 1) {
                $wpjobportal_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'jobs'));
            } else {
                $wpjobportal_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'jobs'));
            }
        } else {
            wpjobportalphplib::wpJP_setcookie('wpjobportal_apply_visitor' , $wpjobportal_jobid , 0 , COOKIEPATH);
            if ( SITECOOKIEPATH != COOKIEPATH ){
                wpjobportalphplib::wpJP_setcookie('wpjobportal_apply_visitor' , $wpjobportal_jobid , 0 , SITECOOKIEPATH);
            }
            if (in_array('multiresume',wpjobportal::$_active_addons)) {
                $wpjobportal_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'multiresume', 'wpjobportallt'=>'addresume'));
            } else {
                $wpjobportal_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'addresume'));
            }
        }
        wp_redirect($wpjobportal_url);
        die();
    }

    function applyonjob() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if(! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_job_apply_nonce')) {
            die( 'Security check Failed' );
        }

        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('jobapply')->applyOnJob();
        $wpjobportal_jobid = WPJOBPORTALrequest::getVar('jobid');
        $page_id = WPJOBPORTALrequest::getVar('wpjobportalpageid');
        if($wpjobportal_result == WPJOBPORTAL_SAVE_ERROR){
            WPJOBPORTALmessages::setLayoutMessage(__("There was some problem performing action",'wp-job-portal'),'error','job');
        }elseif($wpjobportal_result == 3){
            WPJOBPORTALmessages::setLayoutMessage(__("Make Payment To Complete The Job Apply",'wp-job-portal'),'updated','job');
        }else{
            WPJOBPORTALmessages::setLayoutMessage(__("Successfully applied on job",'wp-job-portal'),'updated','job');
        }

        $wpjobportal_url = array('wpjobportalme'=>'job','wpjobportallt'=>'viewjob','wpjobportalid'=>$wpjobportal_jobid,'wpjobportalpageid'=>$page_id);
        $wpjobportal_url = wpjobportal::wpjobportal_makeUrl($wpjobportal_url);
        if(in_array('credits', wpjobportal::$_active_addons)){ // check for credit system
            $wpjobportal_subtype = wpjobportal::$_config->getConfigValue('submission_type');
            if( $wpjobportal_subtype == 2 ){ // per listing mode is on
                $wpjobportal_selected_payment_method = WPJOBPORTALrequest::getVar('selected_payment_method');
                if(isset(wpjobportal::$_data['job_apply_id'])){
                    $wpjobportal_id = wpjobportal::$_data['job_apply_id'];
                    $wpjobportal_paymentconfig = wpjobportal::$_wpjppaymentconfig->getPaymentConfigFor('paypal,stripe,woocommerce',true);
                    if($wpjobportal_selected_payment_method == 1){
                        $wpjobportal_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'purchase','action'=>'wpjobportaltask','task'=>'listingpaypalJobApply','wpjobportalid'=>$wpjobportal_id,'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                    }elseif($wpjobportal_selected_payment_method == 2) {
                        $wpjobportal_url =  wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'purchase','action'=>'wpjobportaltask','task'=>'woocommedeptrceorder','wpjobportalid'=>'job_jobapply_price_perlisting','wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid(),'moduleid'=>$wpjobportal_id));
                    }elseif($wpjobportal_selected_payment_method == 3) {
                        //$wpjobportal_url =  wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'purchase','action'=>'wpjobportaltask','task'=>'woocommedeptrceorder','wpjobportalid'=>'job_jobapply_price_perlisting','wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid(),'moduleid'=>$wpjobportal_id));
                        $wpjobportal_url =  wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'purchasehistory','wpjobportallt'=>'payjobapply','wpjobportalid'=>$wpjobportal_jobid,'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                    }
                }
            }
        }
        // echo var_dump($wpjobportal_url);
        // die(' in job apply controller apply function ');

        wp_redirect(esc_url_raw($wpjobportal_url));
        die();
    }

}

$WPJOBPORTALJobapplyController = new WPJOBPORTALJobapplyController();
?>
