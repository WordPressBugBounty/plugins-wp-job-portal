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