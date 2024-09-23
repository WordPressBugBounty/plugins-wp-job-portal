<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
?>
<div class="wjportal-main-up-wrapper">
<?php
if ( !WPJOBPORTALincluder::getTemplate('templates/header',array('module' => 'employer'))) {
    return;
}
if (wpjobportal::$_error_flag == null) {
    ?>
    <div class="wjportal-main-wrapper wjportal-clearfix">
        <div class="wjportal-page-header">
            <?php WPJOBPORTALincluder::getTemplate('templates/pagetitle', array('module' => 'employer','layout' => 'employer_cp' )); ?>
            <?php
                $guestflag = false;
                $visitorallowed = wpjobportal::$_config->getConfigurationByConfigName('visitorview_emp_conrolpanel');
                $isouruser = WPJOBPORTALincluder::getObjectClass('user')->isWPJOBPortalUser();
                $isguest = WPJOBPORTALincluder::getObjectClass('user')->isguest();
                if($isguest == true && $visitorallowed == true){
                    $guestflag = true;
                }
                if($isguest == false && $isouruser == false && $visitorallowed == true){
                    $guestflag = true;
                }
            ?>
        </div>
        <div id="wjportal-emp-cp-wrp">
            <div class="wjportal-cp-left">
                <?php if(WPJOBPORTALincluder::getObjectClass('user')->isemployer() || wpjobportal::$_common->wpjp_isadmin()) { ?>
                    <div class="wjportal-cp-user">
                        <?php
                            WPJOBPORTALincluder::getTemplate('employer/views/controlpanel',array(
                                'layouts' => 'logo'
                            ));
                        ?>
                        <div class="wjportal-cp-user-action">
                            <a class="wjportal-cp-user-act-btn" href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'user', 'wpjobportallt'=>'formprofile'))); ?>" title="<?php echo esc_html(__('Edit profile', 'wp-job-portal')); ?>">
                                <?php echo esc_html(__('Edit Profile', 'wp-job-portal')); ?>
                            </a>
                        </div>
                    </div>
                <?php } ?>
                <div class="wjportal-cp-short-links-wrp"> 
                    <div class="wjportal-cp-sec-title">
                        <?php echo esc_html(__('Short Links', 'wp-job-portal')); ?>
                    </div>
                    <div class="wjportal-cp-short-links-list">
                        <?php
                            $arrayList = array('1' => array('formjob','myjobs','resumesearch','resumebycategory','my_resumesearches','formcompany','mycompanies','formdepartment','mydepartment','empmessages','myfolders','newfolders','invoice','empresume_rss','empregister','emploginlogout'));
                            WPJOBPORTALincluder::getTemplate('employer/views/leftmenue', array(
                                'layout' =>reset($arrayList)
                            ));
                        ?>
                    </div>
                </div>
            </div>
            <?php //if(WPJOBPORTALincluder::getObjectClass('user')->isemployer()){?>
                <div class="wjportal-cp-right">
                    <?php if(WPJOBPORTALincluder::getObjectClass('user')->isemployer()) { ?>
                        <!-- cp boxes -->
                        <?php $print = wpjobportal_employercheckLinks('employerstatboxes');
                        if ($print) { ?>
                            <div class="wjportal-cp-boxes">
                                <div class="wjportal-cp-box box1">
                                    <div class="wjportal-cp-box-top">
                                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/employer/cp-posted-jobs.png" alt="<?php echo esc_html(__("posted jobs",'wp-job-portal')); ?>">
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
                                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/employer/cp-applied-resume.png" alt="<?php echo esc_html(__("applied resume",'wp-job-portal')); ?>">
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
                                            $url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'multicompany', 'wpjobportallt'=>'mycompanies'));
                                        }else{
                                            $url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'mycompanies'));
                                        }
                                     ?>
                                    <div class="wjportal-cp-box-top">
                                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/employer/cp-my-company.png" alt="<?php echo esc_html(__("my company",'wp-job-portal')); ?>">
                                        <div class="wjportal-cp-box-num">
                                            <?php echo isset(wpjobportal::$_data['totalcompanies']) ? esc_html(wpjobportal::$_data['totalcompanies']) : ''; ?>
                                        </div>
                                        <div class="wjportal-cp-box-tit">
                                            <?php echo esc_html(__('My Company','wp-job-portal')); ?>
                                        </div>
                                    </div>
                                    <div class="wjportal-cp-box-btm clearfix">
                                        <a href="<?php echo esc_url($url);; ?>" title="View detail">
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
                                            <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/employer/cp-save-searches.png" alt="<?php echo esc_html(__("save searches",'wp-job-portal')); ?>">
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
                        <?php } ?>
                    <?php } ?>
                    <?php $print = wpjobportal_employercheckLinks('jobs_graph');
                        if ($print) { ?>
                        <div id="job-applied-resume-wrapper" class="wjportal-cp-graph-wrp wjportal-cp-sect-wrp">
                            <div class="wjportal-cp-sec-title">
                                <?php echo esc_html(__('Applied Jobs','wp-job-portal')); ?> 
                            </div>
                            <?php WPJOBPORTALincluder::getTemplate('employer/views/graph'); ?>
                        </div>    
                    <?php } ?>
                    <?php $print = wpjobportal_employercheckLinks('employerresumebox');
                        if ($print) { ?>
                            <div id="job-applied-resume-wrapper" class="wjportal-cp-sect-wrp wjportal-applied-resume-wrp">
                                <div class="wjportal-cp-sec-title">
                                    <?php echo esc_html(__("Recent Application's","wp-job-portal")); ?>
                                </div>
                                <div class="wjportal-cp-cnt">
                                    <?php WPJOBPORTALincluder::getTemplate('employer/views/recentapplication');?>
                                </div>
                            </div>
                    <?php } ?>
                    <?php 
                        //Invoices
                        if (in_array('credits', wpjobportal::$_active_addons)) {
                             do_action('wpjobportal_addons_invoices_dasboard_emp',wpjobportal::$_data[0]['invoices']);
                        }
                    ?>
                </div>
            <?php //} ?>
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
