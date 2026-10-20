<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALjoblistingenhancerModel {
    //die('abd');


    function getMessagekey(){
        $key = 'joblistingenhancer';if(wpjobportal::$_common->wpjp_isadmin()){$key = 'admin_'.$key;} return $key;
    }
}
?>
