<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param job      job object - optional
 */
?>
<?php
	$wpjobportal_html='<div id="wpjobportal-top-comp-left" class=" js_circle">';
  	WPJOBPORTALincluder::getTemplate('resume/views/admin/logo',array(
    	'wpjobportal_resume' => $wpjobportal_resume,
    	'wpjobportal_resumeque' => $wpjobportal_html
    ));
   	WPJOBPORTALincluder::getTemplate('resume/views/admin/que-title',array(
	   	'wpjobportal_resume' => $wpjobportal_resume,
	   	'wpjobportal_control' => $wpjobportal_control
   	));
?>