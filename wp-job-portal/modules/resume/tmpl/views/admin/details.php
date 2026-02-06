<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param job      job object - optional
 */
?>
<div class="wpjobportal-resume-list-top-wrp">
	<?php
		$wpjobportal_html='<div class="wpjobportal-resume-logo">';
		WPJOBPORTALincluder::getTemplate('resume/views/admin/logo',array(
		   	'wpjobportal_resume'    => $wpjobportal_resume,
		   	'wpjobportal_resumeque' => $wpjobportal_html
	   	));
	   	WPJOBPORTALincluder::getTemplate('resume/views/admin/title',array(
	   		'wpjobportal_resume'=> $wpjobportal_resume
	   	));
	?>
</div>
<div class="wpjobportal-resume-list-btm-wrp">
	<?php
	 	WPJOBPORTALincluder::getTemplate('resume/views/admin/control', array(
	 		'wpjobportal_resume' => $wpjobportal_resume,
	 		'wpjobportal_control' => $wpjobportal_control,
	 		'wpjobportal_arr' => $wpjobportal_arr
		));
	?>
</div>
