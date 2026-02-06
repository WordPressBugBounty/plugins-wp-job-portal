<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param job      job object - optional
*/
$wpjobportal_listing_fields = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldOrderingDataForListing(3);
?>
<?php
    $wpjobportal_photo = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('jobseeker');
	if (isset($wpjobportal_resume->photo) && $wpjobportal_resume->photo != '') {
		$wpjobportal_data_directory = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('data_directory');
	    $wpjobportal_wpdir = wp_upload_dir();
	    $wpjobportal_photo = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/jobseeker/resume_' . $wpjobportal_resume->id. '/photo/' . $wpjobportal_resume->photo;
	}
?>

<?php echo wp_kses($wpjobportal_resumeque, WPJOBPORTAL_ALLOWED_TAGS) ?>
    
		<?php if(isset($wpjobportal_listing_fields['photo'])){ ?>
			<a href="<?php echo esc_url_raw(admin_url('admin.php?page=wpjobportal_resume&wpjobportallt=formresume&wpjobportalid='.$wpjobportal_resume->id)); ?>">
				<img src="<?php echo esc_url($wpjobportal_photo); ?>" alt="<?php echo esc_attr(__('logo','wp-job-portal')); ?>" />
			</a>
		<?php } ?>
		<div class="wpjobportal-resume-crt-date">
			<?php echo esc_html(date_i18n(wpjobportal::$_configuration['date_format'], strtotime($wpjobportal_resume->created))); ?>
		</div>
	</div>
