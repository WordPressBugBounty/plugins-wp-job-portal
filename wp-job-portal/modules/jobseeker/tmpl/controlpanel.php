<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
?>
<div class="wjportal-main-up-wrapper">
    <?php
    //$wpjobportal_protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    if ( !WPJOBPORTALincluder::getTemplate('templates/header',array('wpjobportal_module' => 'jobseeker')) ) {
        return;
    }
    $wpjobportal_application_title = isset(wpjobportal::$_data['application_title'][0]) ? wpjobportal::$_data['application_title'][0] :null;
    $wpjobportal_jobs = isset(wpjobportal::$_data['appliedjobs']) ? wpjobportal::$_data['appliedjobs']:null;
    $wpjobportal_newestjobs = isset(wpjobportal::$_data['latestjobs']) ? wpjobportal::$_data['latestjobs'] :null;
    if (wpjobportal::$_error_flag == null) {
        $wpjobportal_guestflag = false;
        $wpjobportal_isouruser = WPJOBPORTALincluder::getObjectClass('user')->isWPJOBPortalUser();
        $wpjobportal_isguest = WPJOBPORTALincluder::getObjectClass('user')->isguest();
        $wpjobportal_profile = isset(wpjobportal::$_data['userprofile'][0]) ? wpjobportal::$_data['userprofile'][0] : null;
        if($wpjobportal_isguest == true){
            $wpjobportal_guestflag = true;
        }
        if($wpjobportal_isguest == false && $wpjobportal_isouruser == false){
            $wpjobportal_guestflag = true;
        }
        $wpjobportal_labelflag = true;
        $wpjobportal_labelinlisting = wpjobportal::$_configuration['labelinlisting'];
        if ($wpjobportal_labelinlisting != 1) {
            $wpjobportal_labelflag = false;
        }
        $wpjobportal_resumeid = '';
        if(WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()){
            if(isset(wpjobportal::$_data[0]['resume']['info'][0]) && wpjobportal::$_data[0]['resume']['info'][0]->resumeid != ''){
                $wpjobportal_resumeid =  wpjobportal::$_data[0]['resume']['info'][0]->resumeid;
            }
        }
        ////***************Section's 1 LEFT SIDE PORTION***************//////
        ?>
        <div class="wjportal-main-wrapper wjportal-clearfix">
            <div class="wjportal-page-header">
                <?php WPJOBPORTALincluder::getTemplate('templates/pagetitle', array('wpjobportal_module' => 'employer','wpjobportal_layout' => 'employer_cp' )); ?>
            </div>
            <div id="wjportal-job-cp-wrp">
                <?php
                $wpjobportal_job_seeker_profile_section = wpjobportal::$_config->getConfigurationByConfigName('job_seeker_profile_section');
                $wpjobportal_print_jobseeker_stat_boxes = false;
                if(WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()){
                    $wpjobportal_print_jobseeker_stat_boxes = wpjobportal_jobseekercheckLinks('jobseekerstatboxes');
                }
                if(WPJOBPORTALincluder::getObjectClass('user')->isjobseeker() && ($wpjobportal_job_seeker_profile_section == 1 || $wpjobportal_print_jobseeker_stat_boxes)){ ?>
                    <div class="wjportal-cp-top">
                        <?php
                            // hide shortcode option to hide profile section

                            if ( $wpjobportal_job_seeker_profile_section == 1 && empty(wpjobportal::$_data['shortcode_option_hide_profile_section'])) {
                                if(WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()|| wpjobportal::$_common->wpjp_isadmin()) { ?>
                                    <div class="wjportal-cp-user">
                                        <?php
                                            WPJOBPORTALincluder::getTemplate('jobseeker/views/logo',array(
                                                'wpjobportal_profile' => $wpjobportal_profile,
                                                'wpjobportal_application_title' => $wpjobportal_application_title,
                                                'wpjobportal_layout' => 'profile'
                                            ));
                                        ?>
                                        <div class="wjportal-cp-user-action">
                                            <?php
                                            if(in_array('multiresume', wpjobportal::$_active_addons)){
                                                $wpjobportal_addresume_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'multiresume', 'wpjobportallt'=> 'addresume'));
                                            }else{
                                                $wpjobportal_addresume_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'addresume'));
                                            }
                                            ?>
                                            <a class="wjportal-cp-user-act-btn wjportal-cp-user-act-profile-add-resume" href="<?php echo esc_url($wpjobportal_addresume_url); ?>" title="<?php echo esc_attr(__('Add Resume', 'wp-job-portal')); ?>">
                                                <?php echo esc_html(__('Add Resume', 'wp-job-portal')); ?>
                                            </a>
                                            <a class="wjportal-cp-user-act-btn wjportal-cp-user-act-profile-search-job" href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobsearch', 'wpjobportallt'=>'jobsearch'))) ?>" title="<?php echo esc_attr(__('Search Job', 'wp-job-portal')); ?>">
                                                <?php echo esc_html(__('Search Job', 'wp-job-portal')); ?>
                                            </a>
                                            <a class="wjportal-cp-user-act-btn wjportal-cp-user-act-profile-edit-profile" href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'user', 'wpjobportallt'=>'formprofile'))) ?>" title="<?php echo esc_attr(__('Edit profile', 'wp-job-portal')); ?>">
                                                <?php echo esc_html(__('Edit Profile', 'wp-job-portal')); ?>
                                            </a>
                                        </div>
                                    </div>
                                <?php }
                            }
                            // simplified the above code
                            if(empty(wpjobportal::$_data['shortcode_option_hide_stat_boxes'])){ // handle shortcode option to hide stat boxes
                                if ($wpjobportal_print_jobseeker_stat_boxes) { ?>
                                    <!-- cp boxes -->
                                    <div class="wjportal-cp-boxes">
                                        <div class="wjportal-cp-box box1">
                                            <div class="wjportal-cp-box-top">
                                                <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/jobseeker/cp-my-resume.png" alt="<?php echo esc_attr(__("my resume",'wp-job-portal')); ?>">
                                                <?php
                                                if(in_array('multiresume', wpjobportal::$_active_addons)){
                                                    $wpjobportal_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'multiresume', 'wpjobportallt'=> 'myresumes'));
                                                }else{
                                                    $wpjobportal_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'myresumes'));
                                                }
                                                ?>
                                                <div class="wjportal-cp-box-num">
                                                    <?php echo isset(wpjobportal::$_data['totalresume']) ? esc_html(wpjobportal::$_data['totalresume']) : ''; ?>
                                                </div>
                                                <div class="wjportal-cp-box-tit">
                                                    <?php echo esc_html(__('My Resumes','wp-job-portal')); ?>
                                                </div>
                                            </div>
                                            <div class="wjportal-cp-box-btm clearfix">
                                                <a href="<?php echo esc_url($wpjobportal_url); ?>" title="View detail">
                                                    <span class="wjportal-cp-box-text">
                                                       <?php echo esc_html(__('View Detail','wp-job-portal')); ?>
                                                    </span>
                                                    <i class="fa fa-arrow-right"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="wjportal-cp-box box2">
                                            <div class="wjportal-cp-box-top">
                                                <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/jobseeker/cp-applied-job.png" alt="<?php echo esc_attr(__("applied job","wp-job-portal")); ?>">
                                                <div class="wjportal-cp-box-num">
                                                   <?php echo isset(wpjobportal::$_data['totaljobapply'])  ? esc_html(wpjobportal::$_data['totaljobapply']) : 0; ?>
                                                </div>
                                                <div class="wjportal-cp-box-tit">
                                                   <?php echo esc_html(__('Applied jobs','wp-job-portal')); ?>
                                                </div>
                                            </div>
                                            <div class="wjportal-cp-box-btm clearfix">
                                                <a href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobapply', 'wpjobportallt'=>'myappliedjobs'))); ?>" title="View detail">
                                                    <span class="wjportal-cp-box-text">
                                                       <?php echo esc_html(__('View Detail','wp-job-portal')); ?>
                                                    </span>
                                                    <i class="fa fa-arrow-right"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="wjportal-cp-box box3">
                                            <div class="wjportal-cp-box-top">
                                                <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/jobseeker/cp-newest-jobs.png" alt="<?php echo esc_attr(__("newest jobs","wp-job-portal")); ?>">
                                                <div class="wjportal-cp-box-num">
                                                    <?php echo isset(wpjobportal::$_data['totalnewjobs']) ? esc_html(wpjobportal::$_data['totalnewjobs']) : 0 ; ?>
                                                </div>
                                                <div class="wjportal-cp-box-tit">
                                                    <?php echo esc_html(__('Newest Job','wp-job-portal')); ?>
                                                </div>
                                            </div>
                                            <div class="wjportal-cp-box-btm clearfix">
                                                <a href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'newestjobs'))); ?>" title="View detail">
                                                    <span class="wjportal-cp-box-text">
                                                       <?php echo esc_html(__('View Detail','wp-job-portal')); ?>
                                                    </span>
                                                    <i class="fa fa-arrow-right"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <?php
                                        if(in_array('shortlist', wpjobportal::$_active_addons)){ ?>
                                            <div class="wjportal-cp-box box4">
                                                <div class="wjportal-cp-box-top">
                                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/jobseeker/cp-shortlisted-jobs.png" alt="<?php echo esc_attr(__("shortlisted jobs","wp-job-portal")); ?>">
                                                    <div class="wjportal-cp-box-num">
                                                        <?php echo isset(wpjobportal::$_data['totalshorlistjob']) ? esc_html(wpjobportal::$_data['totalshorlistjob']) : 0 ; ?>
                                                    </div>
                                                    <div class="wjportal-cp-box-tit">
                                                        <?php echo esc_html(__('Shotlisted Jobs','wp-job-portal')); ?>
                                                    </div>
                                                </div>
                                                    <div class="wjportal-cp-box-btm clearfix">
                                                        <a href=" <?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'shortlist', 'wpjobportallt'=> 'shortlistedjobs'))); ?>" title="<?php echo esc_attr(__('view detail','wp-job-portal')) ?>">
                                                            <span class="wjportal-cp-box-text">
                                                                <?php echo esc_html(__('View Detail','wp-job-portal')) ?>
                                                            </span>
                                                            <i class="fa fa-arrow-right"></i>
                                                        </a>
                                                    </div>
                                            </div> <?php
                                        } ?>
                                    </div>
                                <?php
                                }
                            }?>
                    </div>
                    <?php
                }

                if(WPJOBPORTALincluder::getObjectClass('user')->isguest()){ ?>
                    <div class="wjportal-cp-guest-banner-wrap" >
                        <div class="wjportal-cp-guest-banner-left" >
                            <div class="wjportal-cp-guest-banner-left-icon-wrap">
                                <!-- Empty section for icon  -->
                            </div>
                        </div>
                        <div class="wjportal-cp-guest-banner-middle" >
                            <div class="wjportal-cp-guest-banner-middle-top" >
                                <?php echo esc_html(__('Welcome, Visitor', 'wp-job-portal')); ?>!
                            </div>
                            <div class="wjportal-cp-guest-banner-middle-bottom" >
                                <?php echo esc_html(__('Please log in to register to continue.', 'wp-job-portal')); ?>
                            </div>
                        </div>
                        <div class="wjportal-cp-guest-banner-right" >
                            <?php
                            $wpjobportal_thiscpurl = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobseeker','wpjobportallt'=>'controlpanel','wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                            $wpjobportal_thiscpurl = wpjobportalphplib::wpJP_safe_encoding($wpjobportal_thiscpurl);
                            $wpjobportal_defaultUrl = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'wpjobportal','wpjobportallt'=>'login','wpjobportalredirecturl'=>$wpjobportal_thiscpurl,'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                            $wpjobportal_loginlink = WPJOBPORTALincluder::getJSModel('configuration')->getLoginRegisterRedirectLink($wpjobportal_defaultUrl,'login');
                            ?>
                            <a class="wjportal-cp-guest-banner-login-link" href="<?php echo esc_url($wpjobportal_loginlink); ?>" title="<?php echo esc_attr(__('Login', 'wp-job-portal')); ?>">
                                <?php echo esc_html(__('Login', 'wp-job-portal')); ?>
                            </a>
                            <?php
                            $wpjobportal_defaultUrl = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'user','wpjobportallt'=>'regjobseeker'));
                            $wpjobportal_register_url = WPJOBPORTALincluder::getJSModel('configuration')->getLoginRegisterRedirectLink($wpjobportal_defaultUrl,'register');
                            ?>
                            <a class="wjportal-cp-guest-banner-register-link" href="<?php echo esc_url($wpjobportal_register_url) ?>" title="<?php echo esc_attr(__('Register', 'wp-job-portal')); ?>">
                                <?php echo esc_html(__('Register', 'wp-job-portal')); ?>
                            </a>
                        </div>
                    </div>
                    <?php
                }
                ?>
	        <div class="wjportal-cp-content-mainwrp">
                <div class="wjportal-cp-left">
                    <div class="wjportal-cp-short-links-wrp">
                        <div class="wjportal-cp-sec-title">
                            <?php echo esc_html(__('Short Links', 'wp-job-portal')); ?>
                        </div>
                        <div class="wjportal-cp-short-links-list">
                            <?php
                                WPJOBPORTALincluder::getTemplate('jobseeker/views/leftmenue',array(
                                ));
                            ?>
                        </div>
                    </div>
                </div>
                <div class="wjportal-cp-right">
                    <?php
                    

                    // handle shortcode option to hide this section
                    //if(empty(wpjobportal::$_data['shortcode_option_hide_job_applies'])){
                        // only show this section if advanced resume builder addon is active
                        if(WPJOBPORTALincluder::getObjectClass('user')->isjobseeker() && in_array('advanceresumebuilder', wpjobportal::$_active_addons)){
                            $wpjobportal_print_resume_status = wpjobportal::$_config->getConfigValue('jobseeker_show_resume_status_section');
                            if ($wpjobportal_print_resume_status == 1) { ?>
                                <div id='wpjobportal-center' class="wjportal-cp-sect-wrp wjportal-resume-status-dashboard-wrap">
                                    <div class="wjportal-cp-sec-title">
                                        <?php echo esc_html(__('Resume Status','wp-job-portal')); ?>
                                    </div>
                                    <?php
                                    if (!empty(wpjobportal::$_data['jobseeker_data']['resumes'])) { ?>
                                        <div class="wjportal-cp-cnt">
                                            <?php
                                            foreach (wpjobportal::$_data['jobseeker_data']['resumes'] AS $wpjobportal_resume) {
                                                WPJOBPORTALincluder::getTemplate('resume/views/frontend/resumestatuslist',array('wpjobportal_resume'=>$wpjobportal_resume));
                                            }?>
                                        </div>
                                        <?php
                                    } else {
                                        $wpjobportal_msg = esc_html(__('No record found','wp-job-portal'));
                                        WPJOBPORTALlayout::getNoRecordFound($wpjobportal_msg, '');
                                    }?>
                                    <div class="wjportal-cp-view-all-wrp">
                                        <?php
                                        if(in_array('multiresume', wpjobportal::$_active_addons)){
                                            $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'multiresume', 'wpjobportallt'=>'myresumes','wpjobportalpageid' =>wpjobportal::wpjobportal_getPageid()));
                                        }else{
                                            $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'myresumes','wpjobportalpageid' =>wpjobportal::wpjobportal_getPageid()));
                                        }

                                        ?>
                                        <div class="wjportal-cp-view-btn-wrp">
                                            <a class="wjportal-cp-view-btn" href="<?php echo esc_url($wpjobportal_link); ?>" title="<?php echo esc_attr(__('view all','wp-job-portal')); ?>">
                                                <?php echo esc_html(__('View All','wp-job-portal')); ?>
                                            </a>

                                        </div>
                                    </div>
                                </div><?php
                            }
                        }
                    //}

                        // handle shortcode option to hide graph
                        if(empty(wpjobportal::$_data['shortcode_option_hide_graph'])){
                            $wpjobportal_print = wpjobportal_jobseekercheckLinks('jsactivejobs_graph');
                            if ($wpjobportal_print) { ?>
                                <div id="job-applied-resume-wrapper" class="wjportal-cp-graph-wrp wjportal-cp-sect-wrp">
                                    <div class="wjportal-cp-sec-title">
                                        <?php echo esc_html(__('Jobs By Types','wp-job-portal')); ?>
                                    </div>
                                    <div>
                                        <?php WPJOBPORTALincluder::getTemplate('jobseeker/views/graph');?>
                                    </div>
                                </div>
                            <?php
                            }
                        }

                        if(empty(wpjobportal::$_data['shortcode_option_hide_job_applies'])){
                            if(WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()){
                                $wpjobportal_print = wpjobportal_jobseekercheckLinks('jobseekerjobapply');
                                if ($wpjobportal_print) { ?>
                                    <div id='wpjobportal-center' class="wjportal-cp-sect-wrp wjportal-applied-jobs-wrp">
                                        <div class="wjportal-cp-sec-title">
                                            <?php echo esc_html(__('Jobs Applied Recently','wp-job-portal')); ?>
                                        </div>
                                        <?php
                                        if (!empty($wpjobportal_jobs)) { ?>
                                            <div class="wjportal-cp-cnt">
                                                <?php
                                                foreach ($wpjobportal_jobs AS $wpjobportal_job) {
                                                    WPJOBPORTALincluder::getTemplate('job/views/frontend/joblist',array('wpjobportal_job'=>$wpjobportal_job,'wpjobportal_labelflag'=>$wpjobportal_labelflag,'wpjobportal_control'=>'resumetitle'));
                                                }?>
                                            </div>
                                            <div class="wjportal-cp-view-btn-wrp">
                                                <a class="wjportal-cp-view-btn" href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobapply', 'wpjobportallt'=>'myappliedjobs'))); ?>" title="<?php echo esc_attr(__('view all','wp-job-portal')); ?>">
                                                    <?php echo esc_html(__('View All','wp-job-portal')); ?>
                                                </a>
                                            </div><?php
                                        } else {
                                            $wpjobportal_msg = esc_html(__('No record found','wp-job-portal'));
                                            WPJOBPORTALlayout::getNoRecordFound($wpjobportal_msg, '');
                                        }?>
                                    </div><?php
                                }
                            }
                        }
                    ?>
                    <!-- Section Newest Job's -->
                    <?php
                    if(empty(wpjobportal::$_data['shortcode_option_hide_newest_jobs'])){
                         ?>
                            <?php $wpjobportal_print = wpjobportal_jobseekercheckLinks('jobseekernewestjobs');
                            if ($wpjobportal_print) { ?>
                            <div id="job-applied-resume-wrapper" class="wjportal-cp-sect-wrp wjportal-newest-jobs-wrp">
                                <div class="wjportal-cp-sec-title">
                                    <?php echo esc_html(__('Newest Jobs','wp-job-portal')); ?>
                                </div><?php
                                if(!empty($wpjobportal_newestjobs)){ ?>
                                    <div class="wjportal-cp-cnt">
                                        <?php
                                        foreach ($wpjobportal_newestjobs AS $wpjobportal_job) {
                                            WPJOBPORTALincluder::getTemplate('job/views/frontend/joblist', array(
                                                'wpjobportal_job' => $wpjobportal_job,
                                                'wpjobportal_labelflag' => $wpjobportal_labelflag,
                                                'wpjobportal_control' => ''
                                            ));
                                        }
                                        ?>
                                    </div>
                                    <div class="wjportal-cp-view-btn-wrp">
                                        <a class="wjportal-cp-view-btn" href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'newestjobs'))); ?>" title="<?php echo esc_attr(__('view all','wp-job-portal')); ?>">
                                            <?php echo esc_html(__('View All','wp-job-portal')); ?>
                                        </a>
                                    </div><?php
                                }else{
                                    $wpjobportal_msg = esc_html(__('No record found','wp-job-portal'));
                                    WPJOBPORTALlayout::getNoRecordFound($wpjobportal_msg, '');
                                } ?>
                                </div>
                                <?php
                            } ?>
                        <?php
                    }

                    // suggested jobs
                    $wpjobportal_show_suggested_jobs_dashboard = wpjobportal::$_config->getConfigValue('show_suggested_jobs_dashboard');
                    if($wpjobportal_show_suggested_jobs_dashboard == 1){
                        if( in_array('aisuggestedjobs', wpjobportal::$_active_addons)){
                            // this hook prepares the data for suggested jobs
                            do_action('wpjobportal_addons_aisuggestedjobs_dashboard');
                            if(isset(wpjobportal::$_data['suggested_jobs']) && !empty(wpjobportal::$_data['suggested_jobs'])){
                                //the data is set from addon
                                $wpjobportal_suggestedjobs = wpjobportal::$_data['suggested_jobs']; ?>
                                <div  id="job-applied-resume-wrapper" class="wjportal-cp-sect-wrp wjportal-newest-jobs-wrp">
                                    <?php
                                    $wpjobportal_print = TRUE;
                                    if ($wpjobportal_print) { ?>
                                        <div class="wjportal-cp-sec-title">
                                            <?php echo esc_html(__('Sugeested Jobs','wp-job-portal')); ?>
                                        </div>
                                        <div class="wjportal-cp-cnt">
                                                <?php
                                                foreach ($wpjobportal_suggestedjobs AS $wpjobportal_job) {
                                                    WPJOBPORTALincluder::getTemplate('job/views/frontend/joblist', array(
                                                        'wpjobportal_job' => $wpjobportal_job,
                                                        'wpjobportal_labelflag' => $wpjobportal_labelflag,
                                                        'wpjobportal_control' => ''
                                                    ));
                                                }
                                                ?>
                                        </div>
                                        <div class="wjportal-cp-view-btn-wrp">

                                        </div><?php
                                    } ?>
                                </div><?php
                            }
                        }
                    }
                    $wpjobportal_show_jobseeker_dashboard_invoices = wpjobportal::$_config->getConfigValue('show_jobseeker_dashboard_invoices');
                    if($wpjobportal_show_jobseeker_dashboard_invoices == 1){
                        if(empty(wpjobportal::$_data['shortcode_option_hide_invoices'])){
                            if(WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()){
                                //Invoices
                                if (in_array('credits', wpjobportal::$_active_addons)) {
                                    do_action('wpjobportal_addons_invoices_dasboard_emp',wpjobportal::$_data[0]['invoices']);
                                }
                            }
                        }
                    }


                        ?>
                    </div>
                </div>
            </div>
        </div>
    <?php
    } else {
        echo wp_kses_post(wpjobportal::$_error_flag_message);
    }
    ?>
</div>
