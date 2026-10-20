<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
?>
<div class="wjportal-main-up-wrapper">
<div id="wjportal-popup-background"></div>
<div id="wjportal-listpopup" class="wjportal-popup-wrp">
    <div class="wjportal-popup-cnt">
        <img id="wjportal-popup-close-btn" alt="<?php echo esc_attr__('Close', 'wp-job-portal'); ?>" src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL . 'includes/images/popup-close.png'); ?>">
        <div class="wjportal-popup-title">
            <span class="wjportal-popup-title2"></span>
        </div>
        <div class="wjportal-popup-contentarea"></div>
    </div>
</div>
    <div class="wjportal-main-wrapper wjportal-clearfix">
        <div class="wjportal-page-header">
            <?php
                WPJOBPORTALincluder::getTemplate('templates/pagetitle',array('wpjobportal_module'=>'job','wpjobportal_layout'=> 'shortListedJob'));
                if(!WPJOBPORTALincluder::getTemplate('templates/header',array('wpjobportal_module' => 'job'))){
                    return;
                }
             ?>
        </div>
        <?php
if (wpjobportal::$_error_flag == null) {
    $wpjobportal_labelflag = true;
    $wpjobportal_labelinlisting = wpjobportal::$_configuration['labelinlisting'];
    if ($wpjobportal_labelinlisting != 1) {
        $wpjobportal_labelflag = false;
    }
    ?>
        <div class="wjportal-jobs-list-wrapper wjportal-shortlisted-jobs-wrp">
            <?php
                if (!empty(wpjobportal::$_data[0])) {
                    $wpjobportal_wpdir = wp_upload_dir();
                    $wpjobportal_data_directory = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('data_directory');
                    foreach (wpjobportal::$_data[0] AS $wpjobportal_shortListedJob) {
                        if ($wpjobportal_shortListedJob->logofilename != "") {
                            $path = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/employer/comp_' . $wpjobportal_shortListedJob->companyid . '/logo/' . $wpjobportal_shortListedJob->logofilename;
                        } else {
                            $path = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('employer');
                        }
                        WPJOBPORTALincluder::getTemplate('job/views/frontend/joblist', array(
                            'wpjobportal_job' => $wpjobportal_shortListedJob,
                            'wpjobportal_labelflag' => $wpjobportal_labelflag,
                            'wpjobportal_control' => 'shortlistjob'
                        ));
                    }
                    if (wpjobportal::$_data[1]) {
                        WPJOBPORTALincluder::getTemplate('templates/pagination',array('wpjobportal_module'=>'shortlist','pagination'=>wpjobportal::$_data[1]));
                    }
                    ?>
                    <?php
                } else {
                    WPJOBPORTALlayout::getNoRecordFound();
                }
            ?>
        </div>
<?php
} else {
    if(wpjobportal::$_error_flag_message !=''){
        echo wp_kses_post(wpjobportal::$_error_flag_message);
    }
}
?>
</div>
</div>
