<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALcontroller {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme', null, 'wpjobportal');
       WPJOBPORTALincluder::include_file($wpjobportal_module);
    }

}

$WPJOBPORTALcontroller = new WPJOBPORTALcontroller();
?>
