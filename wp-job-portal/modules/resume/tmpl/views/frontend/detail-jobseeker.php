<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param job      job object - optional
*/
$wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
?>
<div class="my-resume-data object_<?php echo esc_attr($wpjobportal_myresume->id); ?>">
    <div class="my-resume-listing-img-modified-wrapper" >
<?php
	 WPJOBPORTALincluder::getTemplate('resume/list-view/frontend/myresumelogo',array(
        'wpjobportal_myresume' =>    $wpjobportal_myresume,
        'wpjobportal_logo'    =>    '1'
     ));
    WPJOBPORTALincluder::getTemplate('resume/list-view/frontend/jobseeker-view',array(
        'wpjobportal_myresume'     => $wpjobportal_myresume,
	    'wpjobportal_percentage'   => $wpjobportal_percentage,
	    'wpjobportal_status_array' => $wpjobportal_status_array
    ));
?>
</div>

