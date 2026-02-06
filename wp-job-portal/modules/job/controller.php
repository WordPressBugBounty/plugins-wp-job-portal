<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALJobController {

    private $_msgkey;

    function __construct() {

        self::handleRequest();
        $this->_msgkey = WPJOBPORTALincluder::getJSModel('job')->getMessagekey();

    }

    function handleRequest() {
        $wpjobportal_layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'jobs');
        $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        $wpjpjob = WPJOBPORTALincluder::getJSModel('job');
        if (self::canaddfile($wpjobportal_layout)) {
            $wpjobportal_empflag  = wpjobportal::$_config->getConfigurationByConfigName('disable_employer');
            if(is_admin()){
                $wpjobportal_empflag = true;
            }
            $wpjobportal_string = "'jscontrolpanel','emcontrolpanel','visitor'" ;
            $wpjobportal_config_array = wpjobportal::$_config->getConfigurationByConfigForMultiple($wpjobportal_string);
            switch ($wpjobportal_layout) {
                case 'myjobs':
                    try {
                        if (WPJOBPORTALincluder::getObjectClass('user')->isemployer() && $wpjobportal_empflag == 1) {
                            $wpjpjob->getMyJobs($wpjobportal_uid);
                        } else {
                            wpjobportal::$_common->validateEmployerArea();
                            wpjobportal::$_error_flag = true;
                            if (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
                                $wpjobportal_linktext = esc_html(__('Login','wp-job-portal'));
                                $wpjobportal_link = wpjobportal::$_common->jsMakeRedirectURL('job', $wpjobportal_layout, 1);
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
                case 'jobs':
                case 'newestjobs':
                    $flag = true;
                    $wpjobportal_search = WPJOBPORTALrequest::getVar('issearchform', 'post');
                    $wpjobportal_companyid = WPJOBPORTALrequest::getVar('companyid', 'get');
                    $wpjobportal_jobtypeid = WPJOBPORTALrequest::getVar('jobtype', 'get');
                    $wpjobportal_categoryid = WPJOBPORTALrequest::getVar('category', 'get');
                    $wpjobportalid = WPJOBPORTALrequest::getVar('wpjobportalid', 'get');
                    $wpjobportalid = wpjobportal::$_common->parseID($wpjobportalid);
                    if ($wpjobportal_categoryid != null) {
                        if(WPJOBPORTALincluder::getObjectClass('user')->isguest() && $wpjobportal_config_array['visitorview_js_jobcat'] != 1){
                            $flag = 2;
                        }
                        if(!WPJOBPORTALincluder::getObjectClass('user')->isWPJOBPortalUser() && $wpjobportal_config_array['visitorview_js_jobcat'] != 1){
                            $flag = 3;
                        }
                    }elseif(WPJOBPORTALincluder::getObjectClass('user')->isguest() && $wpjobportal_config_array['visitorview_js_newestjobs'] != 1) {
                        $flag = 2;
                    }elseif(!WPJOBPORTALincluder::getObjectClass('user')->isWPJOBPortalUser() && $wpjobportal_config_array['visitorview_js_newestjobs'] != 1) {
                        $flag = 3;
                    } elseif (WPJOBPORTALincluder::getObjectClass('user')->isguest() && $wpjobportal_config_array['visitorview_js_jobsearchresult'] != 1 && $wpjobportal_search != null) {
                        $flag = 2;
                    } elseif (!WPJOBPORTALincluder::getObjectClass('user')->isWPJOBPortalUser() && $wpjobportal_config_array['visitorview_js_jobsearchresult'] != 1 && $wpjobportal_search != null) {
                        $flag = 3;
                    }
                    if ($flag === true) {
                        $wpjobportal_vars = $wpjpjob->getjobsvar();
                        $wpjpjob->getJobs($wpjobportal_vars);
                        $wpjobportal_empflag = 1;
                        wpjobportal::$_data['vars'] = $wpjobportal_vars;
                        $wpjobportal_issearchform = WPJOBPORTALrequest::getVar('issearchform', 'post', null);
                        if ($wpjobportal_issearchform != null) {
                            wpjobportal::$wpjobportal_data['issearchform'] = $wpjobportal_issearchform;
                        }
                    }elseif($flag === 2){
                        $wpjobportal_link = wpjobportal::$_common->jsMakeRedirectURL('job', $wpjobportal_layout, 1);
                        $wpjobportal_linktext = esc_html(__('Login','wp-job-portal'));
                        wpjobportal::$_error_flag_message = WPJOBPORTALLayout::setMessageFor(1 , $wpjobportal_link , $wpjobportal_linktext,1);
                        wpjobportal::$_error_flag_message_for=1; // user is guest
                        wpjobportal::$_error_flag_message_register_for=1; // register as jobseeker
                        wpjobportal::$_error_flag = true;
                    }elseif($flag === 3){
                        $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'common', 'wpjobportallt'=>'newinwpjobportal'));
                        $wpjobportal_linktext = esc_html(__('Select role','wp-job-portal'));
                        wpjobportal::$_error_flag_message = WPJOBPORTALLayout::setMessageFor(9 , $wpjobportal_link , $wpjobportal_linktext,1);
                        wpjobportal::$_error_flag_message_for=9;
                        wpjobportal::$_error_flag = true;
                    }elseif($flag === 4){
                        wpjobportal::$_error_flag_message = WPJOBPORTALLayout::setMessageFor(2 , null , null,1);
                        wpjobportal::$_error_flag_message_for=2;
                        wpjobportal::$_error_flag = true;
                    }
                    if(isset($wpjobportal_link) && isset($wpjobportal_linktext)){
                        wpjobportal::$_error_flag_message_for_link = $wpjobportal_link;
                        wpjobportal::$_error_flag_message_for_link_text = $wpjobportal_linktext;
                    }
                    $wpjobportal_layout = 'jobs';

                    break;
                case 'viewjob':
                    $wpjobportal_jobid = WPJOBPORTALrequest::getVar('wpjobportalid');
                    $wpjobportal_jobid = wpjobportal::$_common->parseID($wpjobportal_jobid);
                    # paid submission
                    $wpjobportal_submission_type = wpjobportal::$_config->getConfigValue('submission_type');

                    $wpjobportal_expiryflag = $wpjpjob->getJobsExpiryStatus($wpjobportal_jobid);
                    // moved this code up to enable employer to view his own job that is not yet payment approved
                    if($wpjpjob->getJobPay($wpjobportal_jobid)){
                        $wpjobportal_expiryflag = false;
                    }
                    if (WPJOBPORTALincluder::getObjectClass('user')->isemployer()) {
                        if ($wpjpjob->getIfJobOwner($wpjobportal_jobid)) {
                            $wpjobportal_expiryflag = true;
                        }
                    }

                    if (WPJOBPORTALincluder::getObjectClass('user')->isguest() && $wpjobportal_config_array['visitorview_emp_viewjob'] != 1) {
                        $wpjobportal_linktext = esc_html(__('Login','wp-job-portal'));
                        $wpjobportal_link = wpjobportal::$_common->jsMakeRedirectURL('job', $wpjobportal_layout, 1);
                        wpjobportal::$_error_flag_message = WPJOBPORTALLayout::setMessageFor(1 , $wpjobportal_link , $wpjobportal_linktext,1);
                        wpjobportal::$_error_flag = true;
                        wpjobportal::$_error_flag_message_for=1;
                        wpjobportal::$_error_flag_message_register_for=1;
                    } elseif ($wpjobportal_expiryflag == false) {
                        wpjobportal::$_error_flag_message = WPJOBPORTALLayout::setMessageFor(6,null,null,1);
                        wpjobportal::$_error_flag_message_for=6;
                        wpjobportal::$_error_flag = true;
                    } else {
                        # Submission Type for User pakeg
                        if($wpjobportal_submission_type == 3){
                            $wpjobportal_check = WPJOBPORTALincluder::getJSModel('jobapply')->canAddJobApply($wpjobportal_jobid,$wpjobportal_uid);
                        }
                        $wpjpjob->getJobbyIdForView($wpjobportal_jobid);
                        $wpjobportal_empflag = 1;
                    }
                    if(isset($wpjobportal_link) && isset($wpjobportal_linktext)){
                        wpjobportal::$_error_flag_message_for_link=$wpjobportal_link;
                        wpjobportal::$_error_flag_message_for_link_text=$wpjobportal_linktext;
                    }
                    break;
                case 'jobsbycategories':
                    try {
                        if (WPJOBPORTALincluder::getObjectClass('user')->isguest() && $wpjobportal_config_array['visitorview_js_jobcat'] != 1) {
                            $wpjobportal_link = WPJOBPORTALincluder::getJSModel('common')->jsMakeRedirectURL('company', $wpjobportal_layout, 1);
                            $wpjobportal_linktext = esc_html(__('Login','wp-job-portal'));
                            wpjobportal::$_error_flag_message_for=1;
                            wpjobportal::$_error_flag_message_register_for=2;
                            throw new Exception(WPJOBPORTALLayout::setMessageFor(1 , $wpjobportal_link , $wpjobportal_linktext,1));

                        } elseif ((WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()) || ($wpjobportal_config_array['visitorview_js_jobcat'] == 1)) {
                            $wpjpjob->getJobsByCategories();
                            $wpjobportal_empflag = 1;
                        } else {
                            wpjobportal::$_common->validateEmployerArea();
                            wpjobportal::$_error_flag = true;
                            $wpjobportal_link = wpjobportal::$_common->jsMakeRedirectURL('job', $wpjobportal_layout, 1);
                            $wpjobportal_linktext = esc_html(__('Login','wp-job-portal'));
                            wpjobportal::$_error_flag_message_for=1; // user is guest;
                            wpjobportal::$_error_flag_message_register_for=1;
                            if(isset($wpjobportal_link) && isset($wpjobportal_linktext)){
                                wpjobportal::$_error_flag_message_for_link=$wpjobportal_link;
                                wpjobportal::$_error_flag_message_for_link_text=$wpjobportal_linktext;
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
                case 'jobsbytypes':
                    $wpjpjob->getJobsByTypes();
                    $wpjobportal_empflag = 1;
                    break;
                case 'jobsbycities':
                    $wpjpjob->getJobsByCities();
                    $wpjobportal_empflag = 1;
                    break;
                case 'admin_jobs':
                    $wpjpjob->getAllJobs();
                    break;
               case 'addjob':
               case 'admin_formjob':
                    try {
                        if (wpjobportal::$_common->wpjp_isadmin() || (WPJOBPORTALincluder::getObjectClass('user')->isemployer() && $wpjobportal_empflag == 1)) {
                            $wpjobportal_id = WPJOBPORTALrequest::getVar('wpjobportalid');
                            if($wpjobportal_id == '' && !wpjobportal::$_common->wpjp_isadmin()){
                                $wpjobportal_actionname = 'job';
                                if(in_array('credits',wpjobportal::$_active_addons)){
                                        # Filter Package For Controller
                                        $wpjobportal_data = json_decode(apply_filters('wpjobportal_addons_available_package',false,'job','job','canAddJob'));
                                        $wpjobportal_check = $wpjobportal_data->check;
                                        if($wpjobportal_check == true){
                                            if(isset($wpjobportal_data->layout) && $wpjobportal_data->layout == "packageselection" ){
                                                $wpjobportal_layout = $wpjobportal_data->layout;
                                                $wpjobportal_module = 'package';
                                            }
                                       }else{
                                            wpjobportal::$_common->getBuyErrMsg();
                                       }
                                    }else{
                                    $wpjobportal_check = true;
                                }
                                if(!in_array('multicompany',wpjobportal::$_active_addons)){
                                    $wpjobportal_company = WPJOBPORTALincluder::getJSModel('company')->getSingleCompanyByUid($wpjobportal_uid);
                                }
                            }else{
                                if(!wpjobportal::$_common->wpjp_isadmin()){
                                    $wpjobportal_check = $wpjpjob->getIfJobOwner($wpjobportal_id);// owner check
                                    if(!in_array('multicompany',wpjobportal::$_active_addons)){
                                        $wpjobportal_company = WPJOBPORTALincluder::getJSModel('company')->getSingleCompanyByUid($wpjobportal_uid);
                                    }
                                }
                            }
                            if (wpjobportal::$_common->wpjp_isadmin() || $wpjobportal_check == true) {
                                $wpjpjob->getJobbyId($wpjobportal_id);
                            }elseif($wpjobportal_id != ''){// $wpjobportal_id != ''  means this is not new entity case
                                wpjobportal::$_error_flag_message_for = 10; //edit form for a deleted job should show no record found. "4" shows message that not enough credits
                                throw new Exception( WPJOBPORTALLayout::setMessageFor(10,null,null,1));// was showing a log error

                            }else {
                                wpjobportal::$_common->getBuyErrMsg();
                            }
                        } else {
                            if (WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()) {
                                wpjobportal::$_error_flag_message_for=2;
                                throw new Exception( WPJOBPORTALLayout::setMessageFor(2,null,null,1));

                            } elseif ((WPJOBPORTALincluder::getObjectClass('user')->isguest() || !WPJOBPORTALincluder::getObjectClass('user')->isWPJOBPortalUser()) && $wpjobportal_config_array['visitor_can_post_job'] == 1 && in_array('visitorcanaddjob', wpjobportal::$_active_addons)) {
                                $wpjobportal_visitor_add_job = 0;
                                // visitor add job is not supposed to be dependent on credits addon
                                //if(in_array('credits', wpjobportal::$_active_addons)){
                                    if($wpjobportal_config_array['visitor_can_post_job'] == 1 && in_array('visitorcanaddjob', wpjobportal::$_active_addons)){
                                        $wpjobportal_visitor_add_job = 1;
                                    }else{
                                        $wpjobportal_link = WPJOBPORTALincluder::getJSModel('common')->jsMakeRedirectURL('job', $wpjobportal_layout, 1);
                                        $wpjobportal_linktext = esc_html(__('Login','wp-job-portal'));
                                        wpjobportal::$_error_flag_message_for=1;
                                        wpjobportal::$_error_flag_message_register_for=2;
                                        throw new Exception(WPJOBPORTALLayout::setMessageFor(1 , $wpjobportal_link , $wpjobportal_linktext,1));
                                    }
                                //}
                                if($wpjobportal_visitor_add_job == 1) {
                                    $wpjobportal_layout = 'visitoraddjob';
                                    $wpjobportal_module = "visitorcanaddjob";
                                    $wpjobportal_id = WPJOBPORTALrequest::getVar('wpjobportalid');
                                    WPJOBPORTALincluder::getJSModel('company')->getCompanybyId($wpjobportal_id);
                                    if (isset(wpjobportal::$_data[0])) {
                                        wpjobportal::$_data[4] = wpjobportal::$_data[0]; //company data
                                    }
                                    //wpjobportal::$_data[5] = wpjobportal::$_data[2]; //company fields ordering
                                    $wpjpjob->getJobbyId($wpjobportal_id);
                                    if (isset(wpjobportal::$_data[0])) {
                                        wpjobportal::$_data[7] = wpjobportal::$_data[0]; //job data
                                    }
                                    wpjobportal::$_data[8] = wpjobportal::$_data[2];
                                }
                            } else{
                                if(wpjobportal::$wpjobportal_theme_chk == 1){
                                    $wpjobportal_link = WPJOBPORTALincluder::getJSModel('common')->jsMakeRedirectURL('job', $wpjobportal_layout, 1);
                                    $wpjobportal_linktext = esc_html(__('Login','wp-job-portal'));
                                    wpjobportal::$_error_flag_message_for=1;
                                    wpjobportal::$_error_flag_message_register_for=2;
                                    throw new Exception(WPJOBPORTALLayout::setMessageFor(1 , $wpjobportal_link , $wpjobportal_linktext,1));

                                }else{
                                    wpjobportal::$_common->validateEmployerArea();
                                }
                            }
                        }
                        if(isset($wpjobportal_link) && isset($wpjobportal_linktext)){
                            wpjobportal::$_error_flag_message_for_link=$wpjobportal_link;
                            wpjobportal::$_error_flag_message_for_link_text=$wpjobportal_linktext;
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
                case 'admin_jobqueue':
                    $wpjpjob->getAllUnapprovedJobs();
                    break;
                case 'admin_job_searchresult':
                    $wpjpjob->getJobSearch();
                    break;
                case 'admin_jobsearch':
                    //$wpjpjob->getSearchOptions();
                    break;
                case 'admin_view_job':
                    $wpjobportal_id = WPJOBPORTALrequest::getVar('wpjobportalid');
                    $wpjpjob->getJobbyIdForView($wpjobportal_jobid);
                    break;
                default:
                    return;
            }
            if (WPJOBPORTALincluder::getObjectClass('user')->isemployer() || (('myjobs' == $wpjobportal_layout)  || ('addjob' == $wpjobportal_layout)) ) {
                if ($wpjobportal_empflag == 0) {
                    WPJOBPORTALLayout::setMessageFor(5);
                    wpjobportal::$_error_flag_message_for=5;
                    wpjobportal::$_error_flag = true;
                }
            }
            if(!isset($wpjobportal_module)){
                $wpjobportal_module = (wpjobportal::$_common->wpjp_isadmin()) ? 'page' : 'wpjobportalme';
                $wpjobportal_module = WPJOBPORTALrequest::getVar($wpjobportal_module, null, 'job');
            }
            $wpjobportal_module = wpjobportalphplib::wpJP_str_replace('wpjobportal_', '', $wpjobportal_module);
            if(is_numeric($wpjobportal_module)){
                $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme', null, 'job');
            }
            wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
            WPJOBPORTALincluder::include_file($wpjobportal_layout, $wpjobportal_module);
        }
    }

    function approveQueueJob() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_job_nonce') ) {
             die( 'Security check Failed' );
        }
        $wpjobportal_id = WPJOBPORTALrequest::getVar('id');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('job')->approveQueueJobModel($wpjobportal_id);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'job');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_job&wpjobportallt=jobqueue"));
        wp_redirect($wpjobportal_url);
        die();
    }

    function rejectQueueJob() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_job_nonce') ) {
             die( 'Security check Failed' );
        }
        $wpjobportal_id = WPJOBPORTALrequest::getVar('id');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('job')->rejectQueueJobModel($wpjobportal_id);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'job');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_job&wpjobportallt=jobqueue"));
        wp_redirect($wpjobportal_url);
        die();
    }

    function approveQueueFeaturedJob() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_job_nonce') ) {
             die( 'Security check Failed' );
        }
        $wpjobportal_id = WPJOBPORTALrequest::getVar('id');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('job')->approveQueueFeaturedJobModel($wpjobportal_id);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'job');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_job&wpjobportallt=jobqueue"));
        wp_redirect($wpjobportal_url);
        die();
    }

    function rejectQueueFeaturedJob() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_job_nonce') ) {
             die( 'Security check Failed' );
        }
        $wpjobportal_id = WPJOBPORTALrequest::getVar('id');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('job')->rejectQueueFeaturedJobModel($wpjobportal_id);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'job');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_job&wpjobportallt=jobqueue"));
        wp_redirect($wpjobportal_url);
        die();
    }

    // function approveQueueAllJobs() {
    //     $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
    //     if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_job_nonce') ) {
    //          die( 'Security check Failed' );
    //     }
    //     $wpjobportal_id = WPJOBPORTALrequest::getVar('id');
    //     $wpjobportal_alltype = WPJOBPORTALrequest::getVar('objid');
    //     $wpjobportal_result = WPJOBPORTALincluder::getJSModel('job')->approveQueueAllJobsModel($wpjobportal_id, $wpjobportal_alltype);
    //     $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'job');
    //     WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
    //     $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_job&wpjobportallt=jobqueue"));

    //     wp_redirect($wpjobportal_url);
    //     die();
    // }

    // function rejectQueueAllJobs() {
    //     $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
    //     if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_job_nonce') ) {
    //          die( 'Security check Failed' );
    //     }
    //     $wpjobportal_id = WPJOBPORTALrequest::getVar('id');
    //     $wpjobportal_alltype = WPJOBPORTALrequest::getVar('objid');
    //     $wpjobportal_result = WPJOBPORTALincluder::getJSModel('job')->rejectQueueAllJobsModel($wpjobportal_id, $wpjobportal_alltype);
    //     $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'job');
    //     WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
    //     $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_job&wpjobportallt=jobqueue"));
    //     wp_redirect($wpjobportal_url);
    // }

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

    function savejob() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_job_nonce') ) {
             die( 'Security check Failed' );
        }
        $mjob = WPJOBPORTALincluder::getJSModel('job');
        $wpjobportal_data = WPJOBPORTALrequest::get('post');
        $wpjobportal_result = $mjob->storeJob($wpjobportal_data);
        $wpjobportal_isnew = !( isset($wpjobportal_data['id']) && (int)$wpjobportal_data['id'] ) ? 1 : 0;
        $wpjobportal_isqueue = WPJOBPORTALrequest::getVar('isqueue','post',0);
        $adminjoblayout = $wpjobportal_isqueue == 1 ? 'jobqueue' : 'jobs';
        $wpjobportal_submission_type = wpjobportal::$_config->getConfigValue('submission_type');
        $wpjobportal_isnew = !( isset($wpjobportal_data['id']) && (int)$wpjobportal_data['id'] ) ? 1 : 0;
        if ($wpjobportal_result == WPJOBPORTAL_SAVED) {
            if (wpjobportal::$_common->wpjp_isadmin()) {
                $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_job&wpjobportallt=".esc_attr($adminjoblayout)));
            } else {
                if(in_array('credits', wpjobportal::$_active_addons)){
                    if($wpjobportal_submission_type == 2 &&   $wpjobportal_isnew == 1 ){
                        if(wpjobportal::$_config->getConfigValue('job_currency_price_perlisting') > 0){
                            # credit to save
                            $wpjobportal_url = apply_filters('wpjobportal_addons_credit_save_perlisting',false,wpjobportal::$_data['id'],'payjob');
                        }else{
                            $wpjobportal_url = esc_url_raw(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'myjobs')));
                        }
                    }else{
                        $wpjobportal_url = esc_url_raw(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'myjobs')));
                    }
                }else{
                    $wpjobportal_url = esc_url_raw(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'myjobs')));
                }
            }
            if(WPJOBPORTALincluder::getObjectClass('user')->isguest()){
                $wpjobportal_pageid = wpjobportal::$_config->getConfigurationByConfigName('visitor_add_job_redirect_page');
                $wpjobportal_url = get_the_permalink($wpjobportal_pageid);
            }
        } else {
            if (wpjobportal::$_common->wpjp_isadmin()) {
                $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_job&wpjobportallt=formjob"));
            } else {
                $wpjobportal_url = esc_url_raw(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'addjob')));
            }
        }
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'job');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        wp_redirect($wpjobportal_url);
        die();
    }

    function remove() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_job_nonce') ) {
             die( 'Security check Failed' );
        }
        $wpjobportal_ids = WPJOBPORTALrequest::getVar('wpjobportal-cb');
        $wpjobportal_data = WPJOBPORTALrequest::get('post');
        $callfrom = '';
        if (!isset($wpjobportal_data['callfrom']) || $wpjobportal_data['callfrom'] == null) {
            $wpjobportal_data['callfrom'] = $callfrom = WPJOBPORTALrequest::getVar('callfrom');
        }

        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('job')->deleteJobs($wpjobportal_ids);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'job');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        if (wpjobportal::$_common->wpjp_isadmin()) {
            if (isset($wpjobportal_data['callfrom']) AND $wpjobportal_data['callfrom'] == 2) {
                $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_job&wpjobportallt=jobqueue"));
            }else{
                $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_job&wpjobportallt=jobs"));
            }
        } else {
            $wpjobportal_url = esc_url_raw(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'myjobs')));
        }
        wp_redirect($wpjobportal_url);
        die();
    }

    function jobenforcedelete() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_job_nonce') ) {
             die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $wpjobportal_jobid = WPJOBPORTALrequest::getVar('jobid');
        $callfrom = WPJOBPORTALrequest::getVar('callfrom');
        $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        $wpjobportal_resultforsendmail = WPJOBPORTALincluder::getJSModel('job')->getJobInfoForEmail($wpjobportal_jobid);
        $wpjobportal_mailextradata = array();
        if(!empty($wpjobportal_resultforsendmail)){ // to handle log error
            $wpjobportal_mailextradata['jobtitle'] = $wpjobportal_resultforsendmail->jobtitle;
            $wpjobportal_mailextradata['useremail'] = $wpjobportal_resultforsendmail->useremail;
            // log error resolved
            $wpjobportal_mailextradata['companyname'] = $wpjobportal_resultforsendmail->companyname;
            $wpjobportal_mailextradata['user'] = $wpjobportal_resultforsendmail->username;
        }

        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('job')->jobEnforceDelete($wpjobportal_jobid, $wpjobportal_uid);

        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'job');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        if ($callfrom == 1) {
            $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_job&wpjobportallt=jobs"));
        } else {
            $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_job&wpjobportallt=jobqueue"));
        }
        if ($wpjobportal_result == WPJOBPORTAL_DELETED) {
            WPJOBPORTALincluder::getJSModel('emailtemplate')->sendMail(2, 2, $wpjobportal_jobid,$wpjobportal_mailextradata); // 2 for job,2 for DELETE job
        }
        wp_redirect($wpjobportal_url);
        die();
    }
}

$WPJOBPORTALJobController = new WPJOBPORTALJobController();
?>
