<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param module 		module name - optional
 */
if (!isset($wpjobportal_module)) {
	// if module name is not passed than pick from url
	$wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme');
}


/*
show module wise flash messages
*/
if ($wpjobportal_module) {
	$wpjobportal_model = WPJOBPORTALincluder::getJSModel($wpjobportal_module);
	if ($wpjobportal_model) {
		$wpjobportal_msgkey = $wpjobportal_model->getMessagekey();
		WPJOBPORTALMessages::getLayoutMessage($wpjobportal_msgkey);
	}
}


/*
show breadcrumbs
*/


/*
show menu for jobseeker and employer
*/
//include_once(WPJOBPORTAL_PLUGIN_PATH . 'includes/header.php');



/*
if there is any error, show error and return from page
*/
if (wpjobportal::$_error_flag != null &&  wpjobportal::$_error_flag_message != null) {
	echo wp_kses_post(wpjobportal::$_error_flag_message);
    return false;
}