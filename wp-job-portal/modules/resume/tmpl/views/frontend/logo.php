<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param WPJOB PORTAL
 * @param Logo 
 */
?>

<?php
$wpjobportal_photourl = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('jobseeker');
if (isset($wpjobportal_myresume->photo) && $wpjobportal_myresume->photo != "") {
    $wpjobportal_wpdir = wp_upload_dir();
    $wpjobportal_data_directory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
    $wpjobportal_photourl = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/jobseeker/resume_' . $wpjobportal_myresume->id . '/photo/' . $wpjobportal_myresume->photo;
}
$wpjobportal_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'viewresume', 'wpjobportalid'=>$wpjobportal_myresume->id, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));

if(empty(wpjobportal::$_data['shortcode_option_hide_resume_photo'])){ ?>
    <div class="wjportal-resume-logo">
        <span class="fir">
            <a href="<?php echo esc_url($wpjobportal_url); ?>">
                <img  src="<?php echo esc_url($wpjobportal_photourl); ?>" />
            </a>
        </span>
    </div>
<?php
}
?>