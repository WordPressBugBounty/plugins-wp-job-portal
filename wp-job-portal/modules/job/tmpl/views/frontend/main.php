<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param job      job object - optional
 */
if(!isset($wpjobportal_labelflag)){
	$wpjobportal_labelflag = '';
}
?>
<div class="wjportal-jobs-cnt-wrp">
	<?php 
		WPJOBPORTALincluder::getTemplate('job/views/frontend/title',array(
		    'wpjobportal_layout' => 'job',
		    'wpjobportal_job' => $wpjobportal_job,
		    'wpjobportal_labelflag' => $wpjobportal_labelflag,
		    'wpjobportal_control' => $wpjobportal_control
		)); 
	?>
</div>