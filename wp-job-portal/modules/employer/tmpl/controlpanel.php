<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
?>
<div class="wjportal-main-up-wrapper">
    <?php
    if ( !WPJOBPORTALincluder::getTemplate('templates/header',array('wpjobportal_module' => 'employer'))) {
        return;
    }


    if (wpjobportal::$_error_flag == null) { ?>
        <div class="wjportal-main-wrapper wjportal-clearfix">
            <div class="wjportal-page-header">
                <?php WPJOBPORTALincluder::getTemplate('templates/pagetitle', array('wpjobportal_module' => 'employer','wpjobportal_layout' => 'employer_cp' ));
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
                ?>
            </div>
            <div id="wjportal-emp-cp-wrp">
                <?php
                $wpjobportal_employer_profile_section = wpjobportal::$_config->getConfigurationByConfigName('employer_profile_section');
                $wpjobportal_print_employerstatboxes = false;
                if(WPJOBPORTALincluder::getObjectClass('user')->isemployer()){
                    $wpjobportal_print_employerstatboxes = wpjobportal_employercheckLinks('employerstatboxes');
                }
                if((WPJOBPORTALincluder::getObjectClass('user')->isemployer() || wpjobportal::$_common->wpjp_isadmin()) && ($wpjobportal_employer_profile_section == 1 || $wpjobportal_print_employerstatboxes) ) { ?>
                    <div class="wjportal-cp-top">
                        <?php
                        if($wpjobportal_employer_profile_section == 1 && empty(wpjobportal::$_data['shortcode_option_hide_profile_section'])){
                             ?>
                            <div class="wjportal-cp-user">
                                <?php
                                    WPJOBPORTALincluder::getTemplate('employer/views/controlpanel',array(
                                        'wpjobportal_layouts' => 'logo'
                                    ));
                                ?>
                                <div class="wjportal-cp-user-action">
                                    <a class="wjportal-cp-user-act-btn wjportal-cp-user-act-profile-add-job" href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'addjob'))) ?>" title="<?php echo esc_attr(__('Add Job', 'wp-job-portal')); ?>">
                                        <?php echo esc_html(__('Add Job', 'wp-job-portal')); ?>
                                    </a>
                                    <?php
                                    if(in_array('multicompany', wpjobportal::$_active_addons)){
                                        $wpjobportal_mycompany_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'multicompany', 'wpjobportallt'=>'mycompanies'));
                                    }else{
                                        $wpjobportal_mycompany_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'mycompanies'));
                                    }
                                    ?>
                                    <a class="wjportal-cp-user-act-btn wjportal-cp-user-act-profile-my-companies" href="<?php echo esc_url($wpjobportal_mycompany_url) ?>" title="<?php echo esc_attr(__('My Companies', 'wp-job-portal')); ?>">
                                        <?php echo esc_html(__('My Companies', 'wp-job-portal')); ?>
                                    </a>
                                    <a class="wjportal-cp-user-act-btn wjportal-cp-user-act-profile-edit-profile" href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'user', 'wpjobportallt'=>'formprofile'))) ?>" title="<?php echo esc_attr(__('Edit profile', 'wp-job-portal')); ?>">
                                        <?php echo esc_html(__('Edit Profile', 'wp-job-portal')); ?>
                                    </a>
                                </div>
                            </div><?php
                        }
                        if(empty(wpjobportal::$_data['shortcode_option_hide_stat_boxes'])){
                         ?>
                            <!-- cp boxes -->
                            <?php
                            if ($wpjobportal_print_employerstatboxes) { ?>
                                <div class="wjportal-cp-boxes">
                                    <div class="wjportal-cp-box box1">
                                        <div class="wjportal-cp-box-top">
                                            <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/employer/cp-posted-jobs.png" alt="<?php echo esc_attr(__("posted jobs",'wp-job-portal')); ?>">
                                            <div class="wjportal-cp-box-num">
                                                <?php echo isset(wpjobportal::$_data['totaljobs']) ? esc_html(wpjobportal::$_data['totaljobs']) : ''; ?>
                                            </div>
                                            <div class="wjportal-cp-box-tit">
                                                <?php echo esc_html(__('Posted Jobs','wp-job-portal')); ?>
                                            </div>
                                        </div>
                                        <div class="wjportal-cp-box-btm clearfix">
                                            <a href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'myjobs'))); ?>" title="View detail">
                                                <span class="wjportal-cp-box-text">
                                                    <?php echo esc_html(__('View Detail','wp-job-portal')); ?>
                                                </span>
                                                <i class="fa fa-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="wjportal-cp-box box2">
                                        <div class="wjportal-cp-box-top">
                                            <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/employer/cp-applied-resume.png" alt="<?php echo esc_attr(__("applied resume",'wp-job-portal')); ?>">
                                            <div class="wjportal-cp-box-num">
                                                <?php echo isset(wpjobportal::$_data['totaljobapply']) ? esc_html(wpjobportal::$_data['totaljobapply']) : ''; ?>
                                            </div>
                                            <div class="wjportal-cp-box-tit">
                                                <?php echo esc_html(__('Applied Resume','wp-job-portal')); ?>
                                            </div>
                                        </div>
                                        <div class="wjportal-cp-box-btm clearfix">
                                            <a href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'myjobs'))); ?>" title="View detail">
                                                <span class="wjportal-cp-box-text">
                                                    <?php echo esc_html(__('View Detail','wp-job-portal')); ?>
                                                </span>
                                                <i class="fa fa-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="wjportal-cp-box box3">
                                        <?php
                                            if(in_array('multicompany', wpjobportal::$_active_addons)){
                                                $wpjobportal_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'multicompany', 'wpjobportallt'=>'mycompanies'));
                                            }else{
                                                $wpjobportal_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'mycompanies'));
                                            }
                                         ?>
                                        <div class="wjportal-cp-box-top">
                                            <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/employer/cp-my-company.png" alt="<?php echo esc_attr(__("my company",'wp-job-portal')); ?>">
                                            <div class="wjportal-cp-box-num">
                                                <?php echo isset(wpjobportal::$_data['totalcompanies']) ? esc_html(wpjobportal::$_data['totalcompanies']) : ''; ?>
                                            </div>
                                            <div class="wjportal-cp-box-tit">
                                                <?php echo esc_html(__('My Company','wp-job-portal')); ?>
                                            </div>
                                        </div>
                                        <div class="wjportal-cp-box-btm clearfix">
                                            <a href="<?php echo esc_url($wpjobportal_url);; ?>" title="View detail">
                                                <span class="wjportal-cp-box-text">
                                                    <?php  echo esc_html(__('View Detail','wp-job-portal')); ?>
                                                </span>
                                                <i class="fa fa-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <?php if(in_array('resumesearch', wpjobportal::$_active_addons)){ ?>
                                        <div class="wjportal-cp-box box4">
                                            <div class="wjportal-cp-box-top">
                                                <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/employer/cp-save-searches.png" alt="<?php echo esc_attr(__("save searches",'wp-job-portal')); ?>">
                                                <div class="wjportal-cp-box-num">
                                                    <?php echo isset( wpjobportal::$_data['totalresumesearch']) ?  esc_html(wpjobportal::$_data['totalresumesearch']) : ''; ?>
                                                </div>
                                                <div class="wjportal-cp-box-tit">
                                                    <?php echo esc_html(__('Resume Save Search','wp-job-portal')); ?>
                                                </div>
                                            </div>
                                            <div class="wjportal-cp-box-btm clearfix">
                                                <a href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resumesearch', 'wpjobportallt'=>'resumesavesearch'))); ?>" title="View detail">
                                                    <span class="wjportal-cp-box-text">
                                                        <?php echo esc_html(__('View Detail','wp-job-portal')); ?>
                                                    </span>
                                                    <i class="fa fa-arrow-right"></i>
                                                </a>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            <?php
                            }
                        }
                    ?>
                </div>
                <?php } 

                // show guest banner
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
                            $wpjobportal_thiscpurl = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'employer','wpjobportallt'=>'controlpanel','wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                            $wpjobportal_thiscpurl = wpjobportalphplib::wpJP_safe_encoding($wpjobportal_thiscpurl);
                            $wpjobportal_defaultUrl = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'wpjobportal','wpjobportallt'=>'login','wpjobportalredirecturl'=>$wpjobportal_thiscpurl,'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                            $wpjobportal_loginlink = WPJOBPORTALincluder::getJSModel('configuration')->getLoginRegisterRedirectLink($wpjobportal_defaultUrl,'login');
                            ?>
                            <a class="wjportal-cp-guest-banner-login-link" href="<?php echo esc_url($wpjobportal_loginlink); ?>" title="<?php echo esc_attr(__('Login', 'wp-job-portal')); ?>">
                                <?php echo esc_html(__('Login', 'wp-job-portal')); ?>
                            </a>
                            <?php
                            $wpjobportal_defaultUrl = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'user','wpjobportallt'=>'regemployer'));
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
                <div class="wjportal-cp-left"><?php
                     ?>
                    <div class="wjportal-cp-short-links-wrp">
                        <div class="wjportal-cp-sec-title">
                            <?php echo esc_html(__('Short Links', 'wp-job-portal')); ?>
                        </div>
                        <div class="wjportal-cp-short-links-list">
                            <?php
                                //$wpjobportal_arrayList = array('1' => array('formjob','myjobs','resumesearch','resumebycategory','my_resumesearches','formcompany','mycompanies','formdepartment','mydepartment','empmessages','myfolders','newfolders','invoice','empresume_rss','empregister','emploginlogout'));
                                WPJOBPORTALincluder::getTemplate('employer/views/leftmenue', array(

                                ));
                                // WPJOBPORTALincluder::getTemplate('employer/views/leftmenue', array(
                                //     'layout' =>reset($wpjobportal_arrayList)
                                // ));
                            ?>
                        </div>
                    </div>
                </div>

                <div class="wjportal-cp-right">
                    <?php
                    if(empty(wpjobportal::$_data['shortcode_option_hide_graph'])){
                        $wpjobportal_print = wpjobportal_employercheckLinks('jobs_graph');
                        if ($wpjobportal_print) { ?>
                        <div id="job-applied-resume-wrapper" class="wjportal-cp-graph-wrp wjportal-cp-sect-wrp">
                            <div class="wjportal-cp-sec-title">
                                <?php echo esc_html(__('Applied Jobs','wp-job-portal')); ?>
                            </div>
                            <?php WPJOBPORTALincluder::getTemplate('employer/views/graph'); ?>
                        </div>    <?php
                        }
                    }
                    if(empty(wpjobportal::$_data['shortcode_option_hide_recent_applications'])){
                        $wpjobportal_print = wpjobportal_employercheckLinks('employerresumebox');
                        if ($wpjobportal_print) { ?>
                            <div id="job-applied-resume-wrapper" class="wjportal-cp-sect-wrp wjportal-applied-resume-wrp">
                                <div class="wjportal-cp-sec-title">
                                    <?php echo esc_html(__("Recent Application's","wp-job-portal")); ?>
                                </div>
                                <div class="wjportal-cp-cnt">
                                    <?php WPJOBPORTALincluder::getTemplate('employer/views/recentapplication');?>
                                </div>
                            </div><?php
                        }
                    }

                    $wpjobportal_show_suggested_resumes_dashboard = wpjobportal::$_config->getConfigValue('show_suggested_resumes_dashboard');
                    if($wpjobportal_show_suggested_resumes_dashboard == 1){
                        do_action('wpjobportal_addons_aisuggestedresumes_dashboard');
                        if (isset(wpjobportal::$_data['suggested_resumes']) && !empty(wpjobportal::$_data['suggested_resumes'])) { ?>
                            <div id="job-applied-resume-wrapper" class="wjportal-cp-sect-wrp wjportal-applied-resume-wrp">
                                <div class="wjportal-cp-sec-title">
                                    <?php echo esc_html(__("Suggested Resumes","wp-job-portal")); ?>
                                </div>
                                <div class="wjportal-cp-cnt">
                                    <div class="wjportal-resume-list-wrp">
                                        <?php
                                            $wpjobportal_suggested_resumes = wpjobportal::$_data['suggested_resumes'];
                                            foreach ($wpjobportal_suggested_resumes AS $wpjobportal_resume) {
                                                WPJOBPORTALincluder::getTemplate('resume/views/frontend/resumelist',array(
                                                    'wpjobportal_myresume' => $wpjobportal_resume,
                                                    'wpjobportal_module' => 'dashboard',
                                                    'wpjobportal_control' => 'resumedashboard',
                                                    'wpjobportal_percentage' => ''
                                                ));
                                            }
                                        ?>
                                    </div>
                                </div>
                            </div><?php
                        }
                    }

                    $wpjobportal_show_employer_dashboard_invoices = wpjobportal::$_config->getConfigValue('show_employer_dashboard_invoices');
                    if($wpjobportal_show_employer_dashboard_invoices == 1){
                        if(empty(wpjobportal::$_data['shortcode_option_hide_invoices'])){
                            //Invoices
                            if (in_array('credits', wpjobportal::$_active_addons)) {
                                do_action('wpjobportal_addons_invoices_dasboard_emp',wpjobportal::$_data[0]['invoices']);
                            }
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    <?php
    } else {
        if(wpjobportal::$_error_flag_message != null){
            echo wp_kses_post(wpjobportal::$_error_flag_message);
        }
    }
    ?>
</div>
