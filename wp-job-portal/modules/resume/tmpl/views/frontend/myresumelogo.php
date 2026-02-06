<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param job      job object - optional
*/
?>
<?php
$wpjobportal_logo = isset($wpjobportal_logo) ? $wpjobportal_logo : null;
$wpjobportal_path = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('jobseeker');
if($wpjobportal_myresume->photo != "") {
	$wpjobportal_data_directory = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('data_directory');
	$wpjobportal_wpdir = wp_upload_dir();
	$wpjobportal_path = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/jobseeker/resume_' . $wpjobportal_myresume->id . '/photo/' . $wpjobportal_myresume->photo;
}
	if($wpjobportal_logo=="1"){
		?>
		<span class="fir">
            <a href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'viewresume', 'wpjobportalid'=>$wpjobportal_myresume->id, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()))); ?>">
                <img  src="<?php echo esc_url($wpjobportal_path); ?>" />
            </a>
        </span>
		<?php
	}else{?>
	<div class="wjportal-resume-logo">
		<a href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'viewresume', 'wpjobportalid'=>$wpjobportal_myresume->aliasid, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()))); ?>">
		<img src="<?php echo esc_url($wpjobportal_path); ?>">
		</a>
	</div>
	<?php
	}
?>