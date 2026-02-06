<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
/**
* @param wp-job-portal
*==> Main Data Admin Companies*
*/
?>
<?php
	WPJOBPORTALincluder::getTemplate('company/views/admin/logo',array(
		'wpjobportal_company' => $wpjobportal_company,
		'wpjobportal_layout' => $wpjobportal_layout,
		'wpjobportal_wpdir' => $wpjobportal_wpdir
	));

	WPJOBPORTALincluder::getTemplate('company/views/admin/detail',array(
		'wpjobportal_company' => $wpjobportal_company
	));

	WPJOBPORTALincluder::getTemplate('company/views/admin/control',array(
		'wpjobportal_company' => $wpjobportal_company,
		'wpjobportal_control' => $wpjobportal_control,
		'wpjobportal_arr' => $wpjobportal_arr
	));

?>