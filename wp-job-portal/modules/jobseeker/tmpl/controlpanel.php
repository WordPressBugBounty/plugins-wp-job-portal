<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
?>
<div class="wjportal-main-up-wrapper">
<?php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
if ( !WPJOBPORTALincluder::getTemplate('templates/header',array('module' => 'jobseeker')) ) {
    return;
}
$application_title = isset(wpjobportal::$_data['application_title'][0]) ? wpjobportal::$_data['application_title'][0] :null;
$jobs = isset(wpjobportal::$_data[0]['appliedjobs']) ? wpjobportal::$_data[0]['appliedjobs']:null;
$newestjobs = isset(wpjobportal::$_data[0]['latestjobs']) ? wpjobportal::$_data[0]['latestjobs'] :null;
if (wpjobportal::$_error_flag == null) {
    $guestflag = false;
    $isouruser = WPJOBPORTALincluder::getObjectClass('user')->isWPJOBPortalUser();
    $isguest = WPJOBPORTALincluder::getObjectClass('user')->isguest();
    $profile = isset(wpjobportal::$_data['userprofile'][0]) ? wpjobportal::$_data['userprofile'][0] : null;
    if($isguest == true){
        $guestflag = true;
    }
    if($isguest == false && $isouruser == false){
        $guestflag = true;
    }
    $labelflag = true;
    $labelinlisting = wpjobportal::$_configuration['labelinlisting'];
    if ($labelinlisting != 1) {
        $labelflag = false;
    }
    if(WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()){
        if(isset(wpjobportal::$_data[0]['resume']['info'][0]) && wpjobportal::$_data[0]['resume']['info'][0]->resumeid != ''){
            $resumeid =  wpjobportal::$_data[0]['resume']['info'][0]->resumeid;
        }else{

            $resumeid = '';
        }

    }else{
        $resumeid = '';
    }

    ////***************Section's 1 LEFT SIDE PORTION***************//////
    ?>
    <div class="wjportal-main-wrapper wjportal-clearfix">
        <div class="wjportal-page-header">
            <?php WPJOBPORTALincluder::getTemplate('templates/pagetitle', array('module' => 'employer','layout' => 'employer_cp' )); ?>
        </div>
        <div id="wjportal-job-cp-wrp">
            <div class="wjportal-cp-left">
                <?php if(WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()|| wpjobportal::$_common->wpjp_isadmin()) { ?>
                    <div class="wjportal-cp-user">
                        <?php
                            WPJOBPORTALincluder::getTemplate('jobseeker/views/logo',array(
                                'profile' => $profile,
                                'application_title' => $application_title,
                                'layout' => 'profile'
                            ));
                        ?>
                        <div class="wjportal-cp-user-action">
                            <a class="wjportal-cp-user-act-btn" href="<?php   echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'user', 'wpjobportallt'=>'formprofile'))) ?>" title="<?php echo esc_html(__('Edit profile', 'wp-job-portal')); ?>">
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
                            $arrayList = array('1'=>array('newestjobs','jobsearch','myappliedjobs','myresumes','formresume','listjobshortlist','mycoverletter','listallcompanies','jobcat','listjobbytype','jobsbycities','jsmessages','invoice','empresume_rss','jsregister','jobsloginlogout'));
                            WPJOBPORTALincluder::getTemplate('jobseeker/views/leftmenue',array(
                                'layout' =>reset($arrayList)
                            ));
                        ?>
                    </div>
                </div>
            </div>
            <div class="wjportal-cp-right">
                <?php
                // to show notification messages on jobseeker dashboard
                // was showing notifications twice once with breadcrumbs at the top and once here
                if(!WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()){
                    //WPJOBPORTALMessages::getLayoutMessage('user'); // showing double message once here once before breadcrumbs
                }else{
                    // $msgkey = WPJOBPORTALincluder::getJSModel('jobseeker')->getMessagekey();
                    // WPJOBPORTALMessages::getLayoutMessage($msgkey);
                 ?>
                <!-- cp boxes -->
                <?php $print = wpjobportal_jobseekercheckLinks('jobseekerstatboxes');
                        if ($print) { ?>
                    <div class="wjportal-cp-boxes">
                        <div class="wjportal-cp-box box1">
                            <div class="wjportal-cp-box-top">
                                <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/jobseeker/cp-my-resume.png" alt="<?php echo esc_html(__("my resume",'wp-job-portal')); ?>">
                                <?php
                                if(in_array('multiresume', wpjobportal::$_active_addons)){
                                    $url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'multiresume', 'wpjobportallt'=> 'myresumes'));
                                }else{
                                    $url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'myresumes'));
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
                                <a href="<?php echo esc_url($url); ?>" title="View detail">
                                    <span class="wjportal-cp-box-text">
                                       <?php echo esc_html(__('View Detail','wp-job-portal')); ?>
                                    </span>
                                    <i class="fa fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="wjportal-cp-box box2">
                            <div class="wjportal-cp-box-top">
                                <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/jobseeker/cp-applied-job.png" alt="<?php echo esc_html(__("applied job","wp-job-portal")); ?>">
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
                                <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/jobseeker/cp-newest-jobs.png" alt="<?php echo esc_html(__("newest jobs","wp-job-portal")); ?>">
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
                                    <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/jobseeker/cp-shortlisted-jobs.png" alt="<?php echo esc_html(__("shortlisted jobs","wp-job-portal")); ?>">
                                    <div class="wjportal-cp-box-num">
                                        <?php echo isset(wpjobportal::$_data['totalshorlistjob']) ? esc_html(wpjobportal::$_data['totalshorlistjob']) : 0 ; ?>
                                    </div>
                                    <div class="wjportal-cp-box-tit">
                                        <?php echo esc_html(__('Shotlisted Jobs','wp-job-portal')); ?>
                                    </div>
                                </div>
                                    <div class="wjportal-cp-box-btm clearfix">
                                        <a href=" <?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'shortlist', 'wpjobportallt'=> 'shortlistedjobs'))); ?>" title="<?php echo esc_html(__('view detail','wp-job-portal')) ?>">
                                            <span class="wjportal-cp-box-text">
                                                <?php echo esc_html(__('View Detail','wp-job-portal')) ?>
                                            </span>
                                            <i class="fa fa-arrow-right"></i>
                                        </a>
                                    </div>
                            </div>
                             <?php } ?>
                    </div>
                    <?php } ?>
                <?php } ?>
                    <?php $print = wpjobportal_jobseekercheckLinks('jsactivejobs_graph');
                        if ($print) { ?>
                            <div id="job-applied-resume-wrapper" class="wjportal-cp-graph-wrp wjportal-cp-sect-wrp">
                                <div class="wjportal-cp-sec-title">
                                    <?php echo esc_html(__('Jobs By Types','wp-job-portal')); ?>
                                </div>
                                <div>
                                    <?php
                                        WPJOBPORTALincluder::getTemplate('jobseeker/views/graph');
                                    ?>
                                </div>
                            </div>
                        <?php } ?>
                    <?php
                    if(WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()){
                ?>
                <?php $print = wpjobportal_jobseekercheckLinks('jobseekerjobapply');
                        if ($print) { ?>
                        <div id='wpjobportal-center' class="wjportal-cp-sect-wrp wjportal-applied-jobs-wrp">
                            <div class="wjportal-cp-sec-title">
                                <?php echo esc_html(__('Jobs Applied Recently','wp-job-portal')); ?>
                            </div>
                            <div class="wjportal-cp-cnt">
                                <?php
                                    if (!empty($jobs)) {
                                        foreach ($jobs AS $job) {
                                            WPJOBPORTALincluder::getTemplate('job/views/frontend/joblist',array('job'=>$job,'labelflag'=>$labelflag,'control'=>'resumetitle'));
                                        }
                                ?>
                            </div>
                            <div class="wjportal-cp-view-btn-wrp">
                                <a class="wjportal-cp-view-btn" href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobapply', 'wpjobportallt'=>'myappliedjobs'))); ?>" title="<?php echo esc_html(__('view all','wp-job-portal')); ?>">
                                    <?php echo esc_html(__('View All','wp-job-portal')); ?>
                                </a>
                            </div>
                        <?php  } else {
                                    $msg = esc_html(__('No record found','wp-job-portal'));
                                    WPJOBPORTALlayout::getNoRecordFound($msg, '');
                                }
                        ?>
                        </div>
                    <?php } ?>
                <?php
                }
                    ////////////******Graph For Job Seeker ******///////////
                ?>
                <!-- Section Newest Job's -->
                <?php if(WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()){ ?>
                    <div id="job-applied-resume-wrapper" class="wjportal-cp-sect-wrp wjportal-newest-jobs-wrp">
                <?php } else { ?>
                    <div id="job-applied-resume-wrapper" class="wjportal-cp-sect-wrp wjportal-newest-jobs-wrp">
                <?php } ?>
                        <?php $print = wpjobportal_jobseekercheckLinks('jobseekernewestjobs');
                        if ($print) { ?>
                            <div class="wjportal-cp-sec-title">
                                <?php echo esc_html(__('Newest Jobs','wp-job-portal')); ?>
                            </div>
                            <div class="wjportal-cp-cnt">
                                <?php
                                    if(!empty($newestjobs)){
                                        foreach ($newestjobs AS $job) {
                                            WPJOBPORTALincluder::getTemplate('job/views/frontend/joblist', array(
                                                'job' => $job,
                                                'labelflag' => $labelflag,
                                                'control' => ''
                                            ));
                                        }

                                ?>
                            </div>
                            <div class="wjportal-cp-view-btn-wrp">
                                <a class="wjportal-cp-view-btn" href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'newestjobs'))); ?>" title="<?php echo esc_html(__('view all','wp-job-portal')); ?>">
                                    <?php echo esc_html(__('View All','wp-job-portal')); ?>
                                </a>
                            </div>
                     <?php  }else{
                                $msg = esc_html(__('No record found','wp-job-portal'));
                                    WPJOBPORTALlayout::getNoRecordFound($msg, '');
                            }
                        ?>
                    <?php } ?>
                    </div>
                    <?php
                        //Invoices
                        if (in_array('credits', wpjobportal::$_active_addons)) {
                            do_action('wpjobportal_addons_invoices_dasboard_emp',wpjobportal::$_data[0]['invoices']);
                        }
                    ?>
            </div>
        </div>
    </div>
<?php
} else {
    echo wp_kses_post(wpjobportal::$_error_flag_message);
}
?>
</div>
