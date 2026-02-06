<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
/**
 * @param job      job object - optional
*/
?>
<?php
	WPJOBPORTALincluder::getTemplate('company/views/frontend/viewcompanydetail',array(
		'wpjobportal_config_array' => $wpjobportal_config_array,
		'wpjobportal_data_class' => $wpjobportal_data_class,
		'wpjobportal_module' => $wpjobportal_module,
		'wpjobportal_config_array' => $wpjobportal_config_array
	));

?>