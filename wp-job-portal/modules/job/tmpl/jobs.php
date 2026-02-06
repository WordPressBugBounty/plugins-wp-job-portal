<?php
if (!defined('ABSPATH')) die('Restricted Access');
?>
<div class="wjportal-main-up-wrapper">
<?php
if ( !WPJOBPORTALincluder::getTemplate('templates/header',array('wpjobportal_module' => 'job')) ) {
    return;
}

$wpjobportal_jobs = isset(wpjobportal::$_data[0]) ? wpjobportal::$_data[0] : null;
$wpjobportal_labelflag = true;
$wpjobportal_labelinlisting = wpjobportal::$_configuration['labelinlisting'];
if ($wpjobportal_labelinlisting != 1)
    $wpjobportal_labelflag = false;
?>
<div class="wjportal-main-wrapper wjportal-clearfix">
    <div class="wjportal-page-header">
        <?php
            if(!WPJOBPORTALincluder::getTemplate('templates/pagetitle', array('wpjobportal_module' => 'job', 'wpjobportal_layout' => 'newestjob' ))){
                return;
            }
        ?>
    </div>
    <div class="wjportal-newest-jobs">
        <?php
        $wpjobportal_job_list_ai_filter = wpjobportal::$_config->getConfigValue('job_list_ai_filter');
        $wpjobportal_ai_extra_cls = "";
        if($wpjobportal_job_list_ai_filter != 0){ // show job filter without ai
            $wpjobportal_ai_extra_cls = "wjportal-filter-aisearch-main-wrp";
        }
        if(empty(wpjobportal::$_data['shortcode_option_hide_filter'])){ ?>
            <div class="wjportal-filter-search-main-wrp  <?php echo esc_attr($wpjobportal_ai_extra_cls);?>">
                <form class="wjportal-form-wrp" id="job_form" method="post" action="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'jobs'))); ?>">
                    <?php
                        if($wpjobportal_job_list_ai_filter == 0){ // show job filter without ai
                            WPJOBPORTALincluder::getTemplate('job/views/frontend/filter',array('wpjobportal_layout' => 'newestjobsfilter'));
                        }else{
                            WPJOBPORTALincluder::getTemplate('job/views/frontend/filter',array('wpjobportal_layout' => 'aijobfilter'));
                        }
                    ?>
                </form>
            </div>
        <?php } ?>
        <div class="wjportal-jobs-list-wrapper">
            <?php
                if (!empty($wpjobportal_jobs)) {
                    foreach ($wpjobportal_jobs AS $wpjobportal_job) {
                        WPJOBPORTALincluder::getTemplate('job/views/frontend/joblist', array(
                            'wpjobportal_job' => $wpjobportal_job,
                            'wpjobportal_labelflag' => $wpjobportal_labelflag,
                            'wpjobportal_control' => 'newestjobs'
                        ));
                    }

                    if(empty(wpjobportal::$_data['shortcode_option_no_of_jobs'])){ // if no_of_jobs is set in shortcode dont show pagiantion
                        if (wpjobportal::$_data[1]) {
                            WPJOBPORTALincluder::getTemplate('templates/pagination',array(
                                'pagination' => wpjobportal::$_data[1],
                                'wpjobportal_module' => 'job'
                            ));
                        }
                    }
                } else {
                    $wpjobportal_msg = esc_html(__('No record found','wp-job-portal'));
                    WPJOBPORTALlayout::getNoRecordFound($wpjobportal_msg);
                }
            ?>
        </div>
        <div id="wjportal-popup-background"></div>
        <div id="wjportal-listpopup" class="wjportal-popup-wrp wjportal-job-by-catg-popup">
            <div class="wjportal-popup-cnt">
                <img id="wjportal-popup-close-btn" alt="popup cross" src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/popup-close.png">
                <div class="wjportal-popup-title">
                    <span class="wjportal-popup-title2"></span>
                </div>
                <div class="wjportal-popup-contentarea"></div>
            </div>
        </div>
    </div>
</div>
</div>
