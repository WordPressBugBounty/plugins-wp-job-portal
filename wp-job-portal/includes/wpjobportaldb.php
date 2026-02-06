<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class wpjobportaldb {

    function __construct() {
        
    }

    public static function get_var($query) {
        $wpjobportal_result = wpjobportal::$_db->get_var($query);
        if (wpjobportal::$_db->last_error != null) {
            WPJOBPORTALincluder::getJSModel('systemerror')->addSystemError();
        }
        return $wpjobportal_result;
    }

    function setQuery($query){
        $this->_query = $this->parseQuery($query);
    }

    public static function get_row($query) {
        $wpjobportal_result = wpjobportal::$_db->get_row($query);
        if (wpjobportal::$_db->last_error != null) {
            WPJOBPORTALincluder::getJSModel('systemerror')->addSystemError();
        }
        return $wpjobportal_result;
    }

    public static function get_results($query) {
        $wpjobportal_result = wpjobportal::$_db->get_results($query);
        if (wpjobportal::$_db->last_error != null) {
            WPJOBPORTALincluder::getJSModel('systemerror')->addSystemError();
        }
        return $wpjobportal_result;
    }

    private function parseQuery($query){
        $query = wpjobportalphplib::wpJP_str_replace('#__', $this->_db->prefix, $query);
        return $query;
    }

    public static function query($query) {
        $wpjobportal_result = true;
        wpjobportal::$_db->query($query);
        if (wpjobportal::$_db->last_error != null) {
            WPJOBPORTALincluder::getJSModel('systemerror')->addSystemError();
            $wpjobportal_result = false;
        }
        return $wpjobportal_result;
    }

}

?>
