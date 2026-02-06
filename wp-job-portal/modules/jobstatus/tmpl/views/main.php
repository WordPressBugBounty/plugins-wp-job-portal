<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
* 
*/
?>
<?php
	WPJOBPORTALincluder::getTemplate('jobstatus/views/detail',array(
		'wpjobportal_row' => $wpjobportal_row,'i' => $wpjobportal_i ,'wpjobportal_pagenum' => $wpjobportal_pagenum ,
		'wpjobportal_n' => $wpjobportal_n ,'wpjobportal_pageid' => $wpjobportal_pageid ,
		'wpjobportal_islastordershow' => $wpjobportal_islastordershow
	));
?>