<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class wpjobportaldb {

    function __construct() {
        
    }


    public static function prepare($query, ...$args) {
        if (empty($args)) {
            return $query;
        }
        array_unshift($args, $query);
        return call_user_func_array(array(wpjobportal::$_db, 'prepare'), $args);
    }

    public static function like($value) {
        return '%' . wpjobportal::$_db->esc_like($value) . '%';
    }

    public static function prepareIn($values, $format = '%d') {
        if (!is_array($values)) {
            $values = explode(',', (string) $values);
        }
        $clean_values = array();
        foreach ($values as $value) {
            if ($format === '%d') {
                $value = absint($value);
                if ($value > 0) {
                    $clean_values[] = $value;
                }
            } else {
                $value = sanitize_text_field($value);
                if ($value !== '') {
                    $clean_values[] = $value;
                }
            }
        }
        $clean_values = array_values(array_unique($clean_values));
        if (empty($clean_values)) {
            return false;
        }
        $placeholders = implode(',', array_fill(0, count($clean_values), $format));
        return wpjobportal::$_db->prepare($placeholders, $clean_values);
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
