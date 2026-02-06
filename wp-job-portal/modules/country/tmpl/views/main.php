<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
/**
* @param wp-job-portal Optional
*/
?>
<?php
WPJOBPORTALincluder::getTemplate('country/views/detail',array(
	'wpjobportal_row' => $wpjobportal_row ,
	'wpjobportal_pagenum' => $wpjobportal_pagenum ,
	'wpjobportal_pageid' => $wpjobportal_pageid,
	'wpjobportal_published' => $wpjobportal_published
));
?>
