<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALResumeController {

    private $_msgkey;

    function __construct() {
        self::handleRequest();
        $this->_msgkey = WPJOBPORTALincluder::getJSModel('resume')->getMessagekey();
        $wpjobportal_model_resume  = WPJOBPORTALincluder::getJSModel('resume');
    }

    function handleRequest() {
        $wpjobportal_layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'resumes');
        $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        if (self::canaddfile($wpjobportal_layout)) {
            $wpjobportal_empflag  = wpjobportal::$_config->getConfigurationByConfigName('disable_employer');
            $wpjobportal_string = "'jscontrolpanel','emcontrolpanel','visitor','resume'" ;
            $wpjobportal_config_array = wpjobportal::$_config->getConfigurationByConfigForMultiple($wpjobportal_string);
            switch ($wpjobportal_layout) {
                case 'myresumes':
                    if (WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()) {
                        WPJOBPORTALincluder::getJSModel('resume')->getMyResumes($wpjobportal_uid);
                        // to handle jobseeker left menu data
                        WPJOBPORTALincluder::getJSModel('jobseeker')->getResumeInfoForJobSeekerLeftMenu($wpjobportal_uid);
                    } else {
                        if (WPJOBPORTALincluder::getObjectClass('user')->isemployer()) {
                            wpjobportal::$_error_flag_message = WPJOBPORTALLayout::setMessageFor(3,null,null,1);
                            wpjobportal::$_error_flag_message_for=3;
                        } elseif (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
                            $wpjobportal_link = wpjobportal::$_common->jsMakeRedirectURL('resume', $wpjobportal_layout, 1);
                            $wpjobportal_linktext = esc_html(__('Login','wp-job-portal'));
                            wpjobportal::$_error_flag_message = WPJOBPORTALLayout::setMessageFor(1 , $wpjobportal_link , $wpjobportal_linktext,1);
                            wpjobportal::$_error_flag_message_for=1;
                            wpjobportal::$_error_flag_message_register_for=1; // register as jobseeker
                        } elseif (!WPJOBPORTALincluder::getObjectClass('user')->isWPJOBPortalUser()) {
                            $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'common', 'wpjobportallt'=>'newinwpjobportal', 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                            $wpjobportal_linktext = esc_html(__('Select role','wp-job-portal'));
                            wpjobportal::$_error_flag_message = WPJOBPORTALLayout::setMessageFor(9 , $wpjobportal_link , $wpjobportal_linktext,1);
                            wpjobportal::$_error_flag_message_for=9;
                        }
                        if(isset($wpjobportal_link) && isset($wpjobportal_linktext)){
                            wpjobportal::$_error_flag_message_for_link = $wpjobportal_link;
                            wpjobportal::$_error_flag_message_for_link_text = $wpjobportal_linktext;
                        }
                        wpjobportal::$_error_flag = true;
                    }
                    break;
                 case 'resumes':
                    $wpjobportal_vars = array();
                    $wpjobportal_resume_view_type = WPJOBPORTALrequest::getVar('viewtype',null,1); // 1 for list view 2 for grid view
                    $wpjobportal_resume_view_type=wpjobportalphplib::wpJP_str_replace("vt-","",$wpjobportal_resume_view_type);
                    wpjobportal::$_data['viewtype'] = $wpjobportal_resume_view_type;
                    if($wpjobportal_resume_view_type==2){ // switch list to grid show save serch
                        //wpjobportal::$wpjobportal_data['issearchform'] = 1; casuing issues.
                        //wpjobportal::$_data['filter'] = "";
                    }
                    $wpjobportal_id = WPJOBPORTALrequest::getVar('wpjobportalid');
                    if ($wpjobportal_id) {
                        $wpjobportal_array = wpjobportalphplib::wpJP_explode('_', $wpjobportal_id);
                        if ($wpjobportal_array[0] == 'tags') {
                            unset($wpjobportal_array[0]);
                            $wpjobportal_array = implode(' ', $wpjobportal_array);
                            $wpjobportal_vars['tags'] = $wpjobportal_array;
                            wpjobportal::$_data['tags'] = $wpjobportal_vars['tags'];
                        } else {
                            if(isset($wpjobportal_array[1])){
                                $wpjobportal_id = $wpjobportal_array[1];
                                $clue = $wpjobportal_id[0] . $wpjobportal_id[1];
                                switch ($clue) {
                                    case '10': //Category
                                        $wpjobportal_vars['category'] = wpjobportalphplib::wpJP_substr($wpjobportal_id, 2);
                                        wpjobportal::$_data['categoryid'] = $wpjobportal_array[0] . '-' . $wpjobportal_vars['category'];
                                        break;
                                    case '13': //Search
                                        $wpjobportal_id = wpjobportalphplib::wpJP_substr($wpjobportal_id, 2);
                                        wpjobportal::$_data['searchid'] = $wpjobportal_array[0] . '-' . $wpjobportal_id;
                                        $wpjobportal_vars['searchid'] = $wpjobportal_id;
                                        break;
                                    case '14': //sorting in case of parama and no other option selected
                                        $sortby = $wpjobportal_array[0];
                                        $wpjobportal_id = '';
                                        break;
                                    case '15': //Search
                                        $wpjobportal_id = wpjobportalphplib::wpJP_substr($wpjobportal_id, 2);
                                        wpjobportal::$_data['aisuggestedresumes_job'] = $wpjobportal_array[0] . '-' . $wpjobportal_id;
                                        $wpjobportal_vars['aisuggestedresumes_job'] = $wpjobportal_id;
                                        break;
                                    default:
                                        $wpjobportal_id = '';
                                        break;
                                }
                            }
                            // had to do this to handle a sorting in sef case
                            if(wpjobportalphplib::wpJP_strstr($wpjobportal_id, 'asc') || wpjobportalphplib::wpJP_strstr($wpjobportal_id, 'desc')){
                                wpjobportal::$_data['sanitized_args']['sortby'] = $wpjobportal_id;
                            }
                        }
                    } else {
                        $wpjobportal_searchtext = WPJOBPORTALrequest::getVar('search');
                        if ($wpjobportal_searchtext) {
                            //parse id what is the meaning of it
                            $wpjobportal_array = wpjobportalphplib::wpJP_explode('-', $wpjobportal_searchtext);
                            $wpjobportal_vars['searchid'] = $wpjobportal_array[count($wpjobportal_array) - 1];
                        } else {
                            $wpjobportal_vars['searchid'] = '';
                        }
                        $wpjobportal_id = WPJOBPORTALrequest::getVar('category', 'get');
                        if ($wpjobportal_id) {
                            $wpjobportal_array = wpjobportalphplib::wpJP_explode('-', $wpjobportal_id);
                            $wpjobportal_id = $wpjobportal_array[count($wpjobportal_array) - 1];
                            $wpjobportal_vars['category'] = (int) $wpjobportal_id;
                        }
                        $wpjobportal_tags = WPJOBPORTALrequest::getVar('tags', 'get');
                        if ($wpjobportal_tags) {
                            $wpjobportal_tags = wpjobportal::tagfillout($wpjobportal_tags);
                            $wpjobportal_vars['tags'] = $wpjobportal_tags;
                        }
                        $wpjobportal_aisuggestedresumes_job = WPJOBPORTALrequest::getVar('aisuggestedresumes_job', 'get');
                        if ($wpjobportal_aisuggestedresumes_job) {
                            $wpjobportal_array = wpjobportalphplib::wpJP_explode('-', $wpjobportal_aisuggestedresumes_job);
                            $wpjobportal_aisuggestedresumes_job = $wpjobportal_array[count($wpjobportal_array) - 1];
                            $wpjobportal_vars['aisuggestedresumes_job'] = (int) $wpjobportal_aisuggestedresumes_job;
                        }
                    }
                    if (WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()) {
                        $wpjobportal_empflag = 1;
                    }
                    WPJOBPORTALincluder::getJSModel('resume')->getResumes($wpjobportal_vars);
                    break;
                case 'viewresume':
                case 'admin_viewresume':
                    //$wpjobportal_layout = 'viewresume';
                    $wpjobportal_resumeid = '';
                    try {
                        if (current_user_can('manage_options') || (WPJOBPORTALincluder::getObjectClass('user')->isemployer() && $wpjobportal_empflag == 1) || wpjobportal::$_config->getConfigurationByConfigName('visitorview_emp_viewresume') == 1 || WPJOBPORTALincluder::getObjectClass('user')->isjobseeker() ) {
                            $wpjobportal_resumeid = WPJOBPORTALrequest::getVar('wpjobportalid');
                            $wpjobportal_socialid = WPJOBPORTALrequest::getVar('jsscid');
                            //check for the social id
                            if ((!is_numeric($wpjobportal_resumeid) && $wpjobportal_resumeid[0] . $wpjobportal_resumeid[1] . $wpjobportal_resumeid[2] == 'sc-') || $wpjobportal_socialid != null) { // social
                                $wpjobportal_idarray = wpjobportalphplib::wpJP_explode('-', $wpjobportal_resumeid);
                                $wpjobportal_profileid = $wpjobportal_idarray[1];
                                wpjobportal::$_data['socialprofileid'] = $wpjobportal_profileid;
                                wpjobportal::$_data['socialprofile'] = true;
                            } else {
                                $wpjobportal_resumeowner = true;
                                $wpjobportal_idarray = wpjobportalphplib::wpJP_explode('-', $wpjobportal_resumeid);
                                $wpjobportal_resumeid = $wpjobportal_idarray[count($wpjobportal_idarray) - 1];
                                $wpjobportal_expiryflag = WPJOBPORTALincluder::getJSModel('resume')->getResumeExpiryStatus($wpjobportal_resumeid);
                                if(wpjobportal::$_common->wpjp_isadmin()){
                                    $wpjobportal_expiryflag = true;
                                }
                                if (WPJOBPORTALincluder::getObjectClass('user')->isjobseeker() && !wpjobportal::$_common->wpjp_isadmin()) {
                                    if (WPJOBPORTALincluder::getJSModel('resume')->getIfResumeOwner($wpjobportal_resumeid)) {
                                        $wpjobportal_expiryflag = true;
                                    }else{
                                        wpjobportal::$_error_flag_message = WPJOBPORTALLayout::setMessageFor(10,null,null,1);
                                        wpjobportal::$_error_flag_message_for = 2;
                                        wpjobportal::$_error_flag = true;
                                        break;
                                    }
                                }
                                if ($wpjobportal_expiryflag == false) {
                                    wpjobportal::$_error_flag_message = WPJOBPORTALLayout::setMessageFor(6,null,null,1);
                                    wpjobportal::$_error_flag_message_for=6;
                                    wpjobportal::$_error_flag = true;
                                } else {
                                    WPJOBPORTALincluder::getJSModel('resume')->getResumeById($wpjobportal_resumeid);
                                    $wpjobportal_empflag = 1;
                                    wpjobportal::$_data['socialprofile'] = false;
                                }
                            }
                        } else {
                            if (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
                                $wpjobportal_link = wpjobportal::$_common->jsMakeRedirectURL('resume', $wpjobportal_layout, 1);
                                $wpjobportal_linktext = esc_html(__('Login','wp-job-portal'));
                                wpjobportal::$_error_flag_message_for=1;
                                wpjobportal::$_error_flag_message_register_for=2; // register as employer
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
                        // was showing error in log code seems redundant
                        // $wpjobportal_jobid = WPJOBPORTALrequest::getVar('jobid');
                        // $wpjobportal_idarray = wpjobportalphplib::wpJP_explode('-', $wpjobportal_jobid);
                        // $wpjobportal_jobid = $wpjobportal_idarray[count($wpjobportal_idarray) - 1];
                    } catch (Exception $wpjobportal_ex) {
                        wpjobportal::$_error_flag = true;
                        wpjobportal::$_error_flag_message=$wpjobportal_ex->getMessage();
                    }
                break;
                case 'resumebycategory':
                    try {
                        if (wpjobportal::$_common->wpjp_isadmin() || (WPJOBPORTALincluder::getObjectClass('user')->isemployer() && $wpjobportal_empflag == 1) || WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('visitorview_emp_resumecat') == 1 ) {
                            $wpjobportal_resumeid = WPJOBPORTALrequest::getVar('resumeid');
                            WPJOBPORTALincluder::getJSModel('resume')->getResumeByCategory();
                        } else {
                            if (WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()) {
                                wpjobportal::$_error_flag_message_for=2;
                                throw new Exception(WPJOBPORTALLayout::setMessageFor(2,null,null,1));

                            } elseif (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
                                $wpjobportal_link = wpjobportal::$_common->jsMakeRedirectURL('resume', $wpjobportal_layout, 1);
                                $wpjobportal_linktext = esc_html(__('Login','wp-job-portal'));
                                wpjobportal::$_error_flag_message_for=1;
                                wpjobportal::$_error_flag_message_register_for=2; // register as employer
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
                        wpjobportal::$_error_flag_message=$wpjobportal_ex->getMessage();
                    }
                    break;
                case 'admin_formresume':
                    try {
                            wpjobportal::$_error_flag_message = null;
                            $wpjobportal_isouruser = WPJOBPORTALincluder::getObjectClass('user')->isWPJOBPortalUser();
                            $wpjobportal_isguest = WPJOBPORTALincluder::getObjectClass('user')->isguest();
                            $guest = false;
                            if($wpjobportal_isguest == true){
                                $guest = true;
                            }
                            if($wpjobportal_isguest == false && $wpjobportal_isouruser == false){
                                $guest = true;
                            }
                            // Check user is guest and is allowed to add resume
                            $guestallowed = 0;

                            if ($guest && in_array('visitorapplyjob', wpjobportal::$_active_addons)) {
                                $guestallowed = $wpjobportal_config_array['visitor_can_add_resume'];
                            }
                            if ((WPJOBPORTALincluder::getObjectClass('user')->isjobseeker() && $wpjobportal_config_array['formresume'] == 1)|| ($guestallowed == 1)  || wpjobportal::$_common->wpjp_isadmin()) {
                                wpjobportal::$wpjobportal_data['resumeid'] = WPJOBPORTALrequest::getVar('wpjobportalid');

                                if(is_numeric(wpjobportal::$wpjobportal_data['resumeid'])){
                                    if(!wpjobportal::$_common->wpjp_isadmin()){
                                        $wpjobportal_check = WPJOBPORTALincluder::getJSModel('resume')->getIfResumeOwner(wpjobportal::$wpjobportal_data['resumeid']);
                                    }
                                }else{
                                    $wpjobportal_check = WPJOBPORTALincluder::getJSModel('resume')->canAddResume($wpjobportal_uid);
                                }
                                if (wpjobportal::$_common->wpjp_isadmin() || $guestallowed == 1 || $wpjobportal_check == true) {
                                    if ($guestallowed == 1) {
                                        if (isset($_SESSION['wp-wpjobportal']) && isset($_SESSION['wp-wpjobportal']['resumeid'])) {
                                            wpjobportal::$wpjobportal_data['resumeid'] = sanitize_key($_SESSION['wp-wpjobportal']['resumeid']);
                                        }
                                    }
                                    // code to make sure the current resume being edidted is not quick apply resume
                                    if(isset(wpjobportal::$wpjobportal_data['resumeid']) && is_numeric(wpjobportal::$wpjobportal_data['resumeid'])){
                                        $quick_apply_check = WPJOBPORTALincluder::getJSModel('resume')->checkQuickApply(wpjobportal::$wpjobportal_data['resumeid']);
                                        if($quick_apply_check){// will be true if the resume is quick apply
                                            wpjobportal::$_error_flag_message = WPJOBPORTALLayout::setMessageFor(10,null,null,1);
                                            wpjobportal::$_error_flag_message_for = 2;
                                            wpjobportal::$_error_flag = true;
                                            break;
                                        }
                                    }
                                    WPJOBPORTALincluder::getJSModel('resume')->getResumeById(wpjobportal::$wpjobportal_data['resumeid']);
                                }elseif(is_numeric(wpjobportal::$wpjobportal_data['resumeid'])){
                                    wpjobportal::$_error_flag_message_for= 3;
                                    throw new Exception(WPJOBPORTALLayout::setMessageFor(3,null,null,1));
                                }
                            } else {
                                if (WPJOBPORTALincluder::getObjectClass('user')->isemployer()) {
                                    wpjobportal::$_error_flag_message_for=3;
                                    throw new Exception(WPJOBPORTALLayout::setMessageFor(3,null,null,1));

                                } elseif (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
                                    $wpjobportal_link = wpjobportal::$_common->jsMakeRedirectURL('resume', $wpjobportal_layout, 1);
                                    $wpjobportal_linktext = esc_html(__('Login','wp-job-portal'));
                                    wpjobportal::$_error_flag_message_for=1;
                                    wpjobportal::$_error_flag_message_register_for=1; // register as jobseeker
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
                            wpjobportal::$_error_flag_message =$wpjobportal_ex->getMessage();
                        }
                    break;
                case 'addresume':
                    wpjobportal::$_error_flag_message = null;
                    $wpjobportal_isouruser = WPJOBPORTALincluder::getObjectClass('user')->isWPJOBPortalUser();
                    $wpjobportal_isguest = WPJOBPORTALincluder::getObjectClass('user')->isguest();
                    $guest = false;
                    if($wpjobportal_isguest == true){
                        $guest = true;
                    }
                    if($wpjobportal_isguest == false && $wpjobportal_isouruser == false){
                        $guest = true;
                    }
                    // Check user is guest and is allowed to add resume
                    $guestallowed = 0;

                    if ($guest) {
                        $guestallowed = $wpjobportal_config_array['visitor_can_add_resume'];
                    }
                    try {
                        $wpjobportal_visitorcanapply = wpjobportal::$_config->getConfigurationByConfigName('visitor_can_apply_to_job');
                        if($guest && in_array('credits', wpjobportal::$_active_addons) && $wpjobportal_visitorcanapply != 1){
                            $wpjobportal_link = WPJOBPORTALincluder::getJSModel('common')->jsMakeRedirectURL('resume', $wpjobportal_layout, 1);
                            $wpjobportal_linktext = esc_html(__('Login','wp-job-portal'));
                            wpjobportal::$_error_flag_message_for=1;
                            throw new Exception(WPJOBPORTALLayout::setMessageFor(1 , $wpjobportal_link , $wpjobportal_linktext,1));
                        }
                        if ((WPJOBPORTALincluder::getObjectClass('user')->isjobseeker() && $wpjobportal_config_array['formresume'] == 1) || ($guestallowed == 1) && in_array('visitorapplyjob', wpjobportal::$_active_addons) || wpjobportal::$_common->wpjp_isadmin()) {
                            wpjobportal::$wpjobportal_data['resumeid'] = WPJOBPORTALrequest::getVar('wpjobportalid');

                            if(is_numeric(wpjobportal::$wpjobportal_data['resumeid'])){
                                if(!wpjobportal::$_common->wpjp_isadmin()){
                                    $wpjobportal_check = WPJOBPORTALincluder::getJSModel('resume')->getIfResumeOwner(wpjobportal::$wpjobportal_data['resumeid']);
                                }
                            }else{
                                $wpjobportal_actionname = "resume";
                                if(in_array('credits',wpjobportal::$_active_addons)){
                                        # Filter Package For Controller
                                    if(WPJOBPORTALincluder::getJSModel('resume')->UserCanAddResume($wpjobportal_uid) == true){
                                        $wpjobportal_data = json_decode(apply_filters('wpjobportal_addons_available_package',false,'resume','resume','canAddResume'));
                                        $wpjobportal_check = $wpjobportal_data->check;
                                    }else{
                                        wpjobportal::$_common->getMessagesForAddMore('Resume');
                                    }
                                        if($wpjobportal_check == true){
                                            if(isset($wpjobportal_data->layout) && $wpjobportal_data->layout == "packageselection" ){
                                                $wpjobportal_layout = $wpjobportal_data->layout;
                                                $wpjobportal_module = 'package';
                                            }
                                       }else{
                                            $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'package', 'wpjobportallt'=>'packages'));
                                            $wpjobportal_linktext = esc_html(__('Buy Package', 'wp-job-portal'));
                                            wpjobportal::$_error_flag = true;
                                            wpjobportal::$_error_flag_message_for=15;
                                            throw new Exception(WPJOBPORTALLayout::setMessageFor(15,$wpjobportal_link,$wpjobportal_linktext,1));
                                       }
                                    }else{
                                        if(WPJOBPORTALincluder::getObjectClass('user')->isguest()){
                                            $wpjobportal_check =  true;
                                        }else{
                                            if(WPJOBPORTALincluder::getJSModel('resume')->canAddResume($wpjobportal_uid) == false){
                                                wpjobportal::$_common->getMessagesForAddMore('Resume');
                                            }else{
                                                $wpjobportal_check =  true;
                                            }
                                        }
                                }
                            }
                            if (wpjobportal::$_common->wpjp_isadmin() || $guestallowed == 1 || $wpjobportal_check == true) {
                                if ($guestallowed == 1) {
                                    if (isset($_SESSION['wp-wpjobportal']) && isset($_SESSION['wp-wpjobportal']['resumeid'])) {
                                        wpjobportal::$wpjobportal_data['resumeid'] = sanitize_key($_SESSION['wp-wpjobportal']['resumeid']);
                                    }
                                }
                                // code to make sure the current resume being edidted is not quick apply resume
                                if(isset(wpjobportal::$wpjobportal_data['resumeid']) && is_numeric(wpjobportal::$wpjobportal_data['resumeid'])){
                                    $quick_apply_check = WPJOBPORTALincluder::getJSModel('resume')->checkQuickApply(wpjobportal::$wpjobportal_data['resumeid']);
                                    if($quick_apply_check){// will be true if the resume is quick apply
                                        wpjobportal::$_error_flag_message = WPJOBPORTALLayout::setMessageFor(10,null,null,1);
                                        wpjobportal::$_error_flag_message_for = 2;
                                        wpjobportal::$_error_flag = true;
                                        break;
                                    }
                                }

                                WPJOBPORTALincluder::getJSModel('resume')->getResumeById(wpjobportal::$wpjobportal_data['resumeid']);
                                $wpjobportal_empflag = 1;
                            }elseif(is_numeric(wpjobportal::$wpjobportal_data['resumeid'])){
                                wpjobportal::$_error_flag_message = WPJOBPORTALLayout::setMessageFor(10,null,null,1);
                                wpjobportal::$_error_flag_message_for = 2;
                                wpjobportal::$_error_flag = true;
                                break;
                            }
                        } else {
                            // wpjobportal::$_common->validateEmployerArea();
                            // if(isset($wpjobportal_link) && isset($wpjobportal_linktext)){
                            //     wpjobportal::$_error_flag_message_for_link = $wpjobportal_link;
                            //     wpjobportal::$_error_flag_message_for_link_text = $wpjobportal_linktext;
                            // }
                            if (WPJOBPORTALincluder::getObjectClass('user')->isemployer()) {
                                wpjobportal::$_error_flag_message_for=3;
                                throw new Exception(WPJOBPORTALLayout::setMessageFor(3,null,null,1));

                            } elseif (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
                                $wpjobportal_link = WPJOBPORTALincluder::getJSModel('common')->jsMakeRedirectURL('resume', $wpjobportal_layout, 1);
                                $wpjobportal_linktext = esc_html(__('Login','wp-job-portal'));
                                wpjobportal::$_error_flag_message_for=1;
                                wpjobportal::$_error_flag_message_register_for=1; // register as jobseeker
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
                case 'admin_formresume':
                    $wpjobportal_resumeid = WPJOBPORTALrequest::getVar('resumeid');
                    WPJOBPORTALincluder::getJSModel('resume')->getResumebyId($wpjobportal_resumeid);
                    break;
                case 'admin_formresume':
                    wpjobportal::$_wpjpfieldordering->getFieldsOrderingforForm(3);
                    break;
                case 'admin_formresumeuserfield':
                    $wpjobportal_ff = WPJOBPORTALrequest::getVar('ff');
                    if ($wpjobportal_ff == "")
                        $wpjobportal_ff = get_option('wpjobportalformresumeuserfield_ff');
                    else
                        update_option( 'wpjobportalformresumeuserfield_ff',$wpjobportal_ff);
                    $wpjobportal_result = WPJOBPORTALincluder::getJSModel('resume')->getResumeUserFields($wpjobportal_ff);
                    break;
                case 'admin_resumequeue':
                    WPJOBPORTALincluder::getJSModel('resume')->getAllUnapprovedEmpApps();
                    break;
                case 'admin_resumes':
                    if(wpjobportal::$_common->wpjp_isadmin()){
                        WPJOBPORTALincluder::getJSModel('resume')->getAllEmpApps();
                    }
                    break;
                default:
                    return;
            }
            if (WPJOBPORTALincluder::getObjectClass('user')->isemployer() || (('resumes' == $wpjobportal_layout)  || ('resumebycategory' == $wpjobportal_layout)) ) {
                if ($wpjobportal_empflag == 0) {
                    WPJOBPORTALLayout::setMessageFor(5);
                    wpjobportal::$_error_flag = true;
                }
            }
            if(!isset($wpjobportal_module)){
                $wpjobportal_module = (wpjobportal::$_common->wpjp_isadmin()) ? 'page' : 'wpjobportalme';
                $wpjobportal_module = WPJOBPORTALrequest::getVar($wpjobportal_module, null, 'resume');
                $wpjobportal_module = wpjobportalphplib::wpJP_str_replace('wpjobportal_', '', $wpjobportal_module);
                if(is_numeric($wpjobportal_module)){
                    $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme', null, 'resume');
                }
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

    function approveQueueResume() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_resume_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin()) return false;
        $wpjobportal_id = WPJOBPORTALrequest::getVar('id');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('resume')->approveQueueResumeModel($wpjobportal_id);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'resume');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_resume&wpjobportallt=resumequeue"));
        wp_redirect($wpjobportal_url);
        die();
    }

    function approveQueueFeatureResume() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_resume_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin()) return false;
        $wpjobportal_id = WPJOBPORTALrequest::getVar('id');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('resume')->approveQueueFeatureResumeModel($wpjobportal_id);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'resume');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_resume&wpjobportallt=resumequeue"));
        wp_redirect($wpjobportal_url);
        die();
    }

    function rejectQueueResume() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_resume_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin()) return false;
        $wpjobportal_id = WPJOBPORTALrequest::getVar('id');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('resume')->rejectQueueResumeModel($wpjobportal_id);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'resume');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_resume&wpjobportallt=resumequeue"));
        wp_redirect($wpjobportal_url);
        die();
    }

    function rejectQueueFeatureResume() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_resume_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin()) return false;
        $wpjobportal_id = WPJOBPORTALrequest::getVar('id');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('resume')->rejectQueueFeatureResumeModel($wpjobportal_id);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'resume');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_resume&wpjobportallt=resumequeue"));
        wp_redirect($wpjobportal_url);
        die();
    }


    function approveQueueAllResumes() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_resume_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin()) return false;
        $wpjobportal_id = WPJOBPORTALrequest::getVar('id');
        $wpjobportal_alltype = WPJOBPORTALrequest::getVar('objid');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('resume')->approveQueueAllResumesModel($wpjobportal_id, $wpjobportal_alltype);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'resume');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_resume&wpjobportallt=resumequeue"));
        WPJOBPORTALincluder::getJSModel('emailtemplate')->sendMail(3, 2, $wpjobportal_id); // 3 for resume,2 for Approve resume
        wp_redirect($wpjobportal_url);
        die();
    }

    function rejectQueueAllResumes() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_resume_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin()) return false;
        $wpjobportal_id = WPJOBPORTALrequest::getVar('id');
        $wpjobportal_alltype = WPJOBPORTALrequest::getVar('objid');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('resume')->rejectQueueAllResumesModel($wpjobportal_id, $wpjobportal_alltype);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'resume');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_resume&wpjobportallt=resumequeue"));
        wp_redirect($wpjobportal_url);
        die();
    }

    /* STRAT EXPORT RESUMES */

    function resumeenforcedelete() {
        if(!wpjobportal::$_common->wpjp_isadmin()) return false;
        // $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        // if (! wp_verify_nonce( $wpjobportal_nonce, 'delete-resume') ) {
        //      die( 'Security check Failed' );
        // }
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_resume_nonce') ) {
             die( 'Security check Failed' );
        }
        $wpjobportal_resumeid = WPJOBPORTALrequest::getVar('resumeid');
        $callfrom = WPJOBPORTALrequest::getVar('callfrom');
        $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('resume')->resumeEnforceDelete($wpjobportal_resumeid, $wpjobportal_uid);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'resume');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);

        if ($callfrom == 1) {
            $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_resume&wpjobportallt=resumes"));
        } else {
            $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_resume&wpjobportallt=resumequeue"));
        }
        wp_redirect($wpjobportal_url);
        die();
    }

    // function empappreject() {
    //     $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
    //     if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_resume_nonce') ) {
    //          die( 'Security check Failed' );
    //     }
    //     $wpjobportal_appid = WPJOBPORTALrequest::getVar('resumeid');
    //     $wpjobportal_result = WPJOBPORTALincluder::getJSModel('resume')->empappReject($wpjobportal_appid);
    //     $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'resume');
    //     WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
    //     wp_redirect(esc_url_raw(admin_url("admin.php?page=wpjobportal_resume&wpjobportallt=resumequeue")));
    //     die();
    // }


    function saveresume() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_resume_nonce') ) {
             die( 'Security check Failed' );
        }
        try{
            //requesting parameters
            $mresume = WPJOBPORTALincluder::getJSModel('resume');
            $wpjobportal_data = WPJOBPORTALrequest::get('post');
            if (!isset($wpjobportal_data['sec_1']['searchable'])) {
                $wpjobportal_data['sec_1']['searchable'] = 0;
            }
            $wpjobportal_resumeid = (int) $wpjobportal_data['id'];
            $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();

            $wpjobportal_isnew = !$wpjobportal_resumeid;
            if(!wpjobportal::$_common->wpjp_isadmin() && !WPJOBPORTALincluder::getObjectClass('user')->isguest()){
                if($wpjobportal_isnew && !$mresume->checkAlreadyadd($wpjobportal_uid)){
                    throw new Exception(WPJOBPORTAL_SAVE_ERROR, WPJOBPORTAL_RESUME);
                }
            }
            $wpjobportal_resumeid = $mresume->storeResume($wpjobportal_data);
            if(!$wpjobportal_resumeid){
                throw new Exception(WPJOBPORTAL_SAVE_ERROR, WPJOBPORTAL_RESUME);
            }

            $wpjobportal_msg = WPJOBPORTALMessages::getMessage(WPJOBPORTAL_SAVED, WPJOBPORTAL_RESUME);
            WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
            $redirecturl = wpjobportal::wpjobportal_redirectUrl('resume.success',isset(wpjobportal::$_data['id']) ? wpjobportal::$_data['id'] : '');

            // visitor add resume redirect configuration not implimented
            if(WPJOBPORTALincluder::getObjectClass('user')->isguest()){
                $wpjobportal_pageid = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('visitor_add_resume_redirect_page');
                if(isset($wpjobportal_pageid) && $wpjobportal_pageid > 0){ // to hanle redict in case of not set up in configuration
                    $redirecturl = get_the_permalink($wpjobportal_pageid);
                }else{
                    $redirecturl = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobseeker', 'wpjobportallt'=>'controlpanel',"wpjobportalpageid"=>wpjobportal::wpjobportal_getPageid()));
                }
            }

        }catch(Exception $wpjobportal_ex){
            $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_ex->getMessage(), $wpjobportal_ex->getCode());
            WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
            $redirecturl = wpjobportal::wpjobportal_redirectUrl('resume.fail');
        }
        wp_redirect($redirecturl);
        die();
    }

    function removeresume() {
        // $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        // if (! wp_verify_nonce( $wpjobportal_nonce, 'delete-resume') ) {
        //      die( 'Security check Failed' );
        // }
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_resume_nonce') ) {
             die( 'Security check Failed' );
        }
        $wpjobportal_data = WPJOBPORTALrequest::get('post');
        $wpjobportal_resumeid = WPJOBPORTALrequest::getVar('wpjobportal-cb');
        if (!isset($wpjobportal_data['callfrom'])) {
            $wpjobportal_data['callfrom'] = $callfrom = WPJOBPORTALrequest::getVar('callfrom');
        }
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('resume')->deleteResume($wpjobportal_resumeid);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'resume');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        if (wpjobportal::$_common->wpjp_isadmin()) {
            if ($wpjobportal_data['callfrom'] == 1) {
                $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_resume&wpjobportallt=resumes"));
            } elseif ($wpjobportal_data['callfrom'] == 2) {
                $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_resume&wpjobportallt=resumequeue"));
            }
        } else {
            if (in_array('multiresume',wpjobportal::$_active_addons)) {
                $wpjobportal_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'multiresume', 'wpjobportallt'=>'myresumes', 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
            } else {
                $wpjobportal_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'myresumes', 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
            }
        }
        wp_redirect($wpjobportal_url);
        die();
    }

    function getallresumefiles() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        $wpjobportal_resumeid = WPJOBPORTALrequest::getVar('resumeid');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_resume_nonce'.$wpjobportal_resumeid) ) {
             die( 'Security check Failed' );
        }
        WPJOBPORTALincluder::getJSModel('resume')->getAllResumeFiles();
    }

    function getresumefiledownloadbyid() {
        $fileid = WPJOBPORTALrequest::getVar('wpjobportalid');
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_resume_nonce'.$fileid) ) {
             die( 'Security check Failed' );
        }
        WPJOBPORTALincluder::getJSModel('resume')->getResumeFileDownloadById($fileid);
    }

    function addviewresumedetail() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_resume_nonce') ) {
             die( 'Security check Failed' );
        }
        // $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        // if (! wp_verify_nonce( $wpjobportal_nonce, 'resume-view') ) {
        //     die( 'Security check Failed' );
        // }
        $wpjobportal_id = WPJOBPORTALrequest::getVar('wpjobportalid');
        $wpjobportal_pageid = WPJOBPORTALrequest::getVar('wpjobportal_pageid');
        $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        WPJOBPORTALincluder::getJSModel('resume')->addViewContactDetail($wpjobportal_id, $wpjobportal_uid);
        $wpjobportal_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'viewresume','wpjobportalid'=>$wpjobportal_id, 'wpjobportalpageid'=>$wpjobportal_pageid));
        wp_redirect($wpjobportal_url);
        die();
    }
}

$WPJOBPORTALResumeController = new WPJOBPORTALResumeController();
?>
