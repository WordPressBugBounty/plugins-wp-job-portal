<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
* @param js-job optional 
*Template For job Detail
*/
?>
<div class="wpjobportal-jobs-list-top-wrp">
	<?php
	    //$goldflag = true;
	    do_action('wpjobportal_addons_admin_feature_lable_for_job',$wpjobportal_job);
	?>
	<?php
		WPJOBPORTALincluder::getTemplate('job/views/admin/logo',array(
			'wpjobportal_layout' => $wpjobportal_logo,
			'wpjobportal_job' => $wpjobportal_job
		));

		WPJOBPORTALincluder::getTemplate('job/views/admin/title',array(
			'wpjobportal_layout' => 'title'
			 ,'wpjobportal_job' => $wpjobportal_job
		));
	?>
</div>
<div class="wpjobportal-jobs-list-btm-wrp">
	<?php
		WPJOBPORTALincluder::getTemplate('job/views/admin/control',array(
			'wpjobportal_layout' => $wpjobportal_layout,
			 'wpjobportal_job' => $wpjobportal_job
		));
	?>
</div>