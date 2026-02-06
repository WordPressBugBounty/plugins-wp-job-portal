<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param job      job object - optional
*/
?>
<?php
	WPJOBPORTALincluder::getTemplate('resume/list-view/frontend/jobseeker-title',array(
			'wpjobportal_myresume'	=>	$wpjobportal_myresume,
			'wpjobportal_percentage'=>	$wpjobportal_percentage
	));
	WPJOBPORTALincluder::getTemplate('resume/list-view/frontend/jobsekr-perc',array(
			'wpjobportal_myresume'	=>	$wpjobportal_myresume,
			'wpjobportal_percentage'=>	$wpjobportal_percentage
	));
	WPJOBPORTALincluder::getTemplate('resume/list-view/frontend/jobsekr-controls',array(
			'wpjobportal_myresume'	=>	$wpjobportal_myresume
	));
?>
                
