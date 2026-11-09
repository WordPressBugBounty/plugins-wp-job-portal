<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
    //include_once WPJOBPORTAL_PLUGIN_PATH.'includes/css/style_color.php';
//wp_enqueue_style('wpjobportal-color', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/color.css');
wp_enqueue_style('wpjobportal-style', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/style.css', array(), wpjobportal_asset_version('includes/css/style.css'));
wp_enqueue_style('wpjobportal-style-mobile', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/style_mobile.css',array(),'1.1.1','(max-width: 480px)');;
wp_enqueue_style('wpjobportal-jobseeker-style', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/jobseekercp.css', array('wpjobportal-design-system'), wpjobportal_asset_version('includes/css/jobseekercp.css'));
wp_enqueue_style('wpjobportal-employer-style', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/employercp.css', array(), wpjobportal_asset_version('includes/css/employercp.css'));
if (is_rtl()) {
    wp_register_style('wpjobportal-style-rtl', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/stylertl.css', array(), wpjobportal_asset_version('includes/css/stylertl.css'));
    wp_enqueue_style('wpjobportal-style-rtl');
}
// update_option( 'wpjobportalresumeeditadmin', 1 ); // extra no in use
?>
<!-- main wrapper -->
<div id="wpjobportaladmin-wrapper" class="wjpl-page">
    <!-- left menu -->
    <div id="wpjobportaladmin-leftmenu">
	   <?php WPJOBPORTALincluder::getTemplate('templates/admin/leftmenue',array('wpjobportal_module' => 'resume')); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <?php // The resume builder is the one job seekers use; it keeps its own design here. ?>
        <?php WPJOBPORTAL_Admin_Form_UI::simple_header(__('Resume', 'wp-job-portal'), __('resume', 'wp-job-portal'), 'wpjobportal_resume', 'resumeform'); ?>
        <!-- page content -->
        <div id="wpjobportal-admin-wrapper">
            <?php
            $wpjobportal_token = WPJOBPORTALincluder::getJSModel('common')->getUniqueIdForTransient();
            $wpjobportal_transient_val = get_transient('current_user_token_resume_'.$wpjobportal_token);
            if(!empty($wpjobportal_transient_val)){
                ?>
                <div class="wpjobportal-admin--backlink-wrap">
                    <a id="form-back-button" class="wpjobportal-form-back-btn" href="<?php echo esc_url_raw(admin_url('admin.php?page=wpjobportal_resume&wpjobportal_restore_results='.$wpjobportal_token)); ?>" title="<?php echo esc_attr(__('Back To Listing', 'wp-job-portal')); ?>">
                        <?php echo esc_html(__('Back To Listing', 'wp-job-portal')); ?>
                    </a>
                </div>
            <?php }?>
            <?php
                require_once(WPJOBPORTAL_PLUGIN_PATH . 'modules/resume/tmpl/addresume.inc.php');
                require_once(WPJOBPORTAL_PLUGIN_PATH . 'modules/resume/tmpl/addresume.php');
            ?>
        </div>
    </div>
</div>
