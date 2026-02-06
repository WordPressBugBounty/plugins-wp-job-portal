<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
/**
* @param wp-job-portal detail
*/
?>
<?php
	WPJOBPORTALincluder::getTemplate('careerlevel/views/detail',array(
		'wpjobportal_row' => $wpjobportal_row,
		'wpjobportal_i' => $wpjobportal_i,
		'wpjobportal_pagenum' => $wpjobportal_pagenum ,
		'wpjobportal_n' => $wpjobportal_n ,
		'wpjobportal_pageid' => $wpjobportal_pageid
	));
?>