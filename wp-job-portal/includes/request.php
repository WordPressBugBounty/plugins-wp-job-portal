<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALrequest {
    /*
     * Check Request from both the Get and post method
     */

    static function getVar($wpjobportal_variable_name, $method = null, $wpjobportal_defaultvalue = null, $typecast = null,$editor_data=null) {
        $wpjobportal_value = null;
        if ($method == null) {
            if (isset($_GET[$wpjobportal_variable_name])) {
                if(is_array($_GET[$wpjobportal_variable_name])){
                    $wpjobportal_value = filter_var_array($_GET[$wpjobportal_variable_name]);
                }else{
                    $wpjobportal_value = wpjobportal::wpjobportal_sanitizeData($_GET[$wpjobportal_variable_name]);
                }
            } elseif (isset($_POST[$wpjobportal_variable_name])) {
                if(is_array($_POST[$wpjobportal_variable_name])){
                    $wpjobportal_value = filter_var_array($_POST[$wpjobportal_variable_name]);
                }else{
                    if($editor_data == null){
                        $wpjobportal_value = wpjobportal::wpjobportal_sanitizeData($_POST[$wpjobportal_variable_name]);
                    }else{
                        $wpjobportal_value = filter_var($_POST[$wpjobportal_variable_name],FILTER_DEFAULT);
                    }
                }
            } elseif (get_query_var($wpjobportal_variable_name)) {
                $wpjobportal_value = get_query_var($wpjobportal_variable_name);
            } elseif (isset(wpjobportal::$_data['sanitized_args'][$wpjobportal_variable_name]) && wpjobportal::$_data['sanitized_args'][$wpjobportal_variable_name] != '') {
                $wpjobportal_value = wpjobportal::$_data['sanitized_args'][$wpjobportal_variable_name];
            }
        } else {
            $method = wpjobportalphplib::wpJP_strtolower($method);
            switch ($method) {
                case 'post':
                    if (isset($_POST[$wpjobportal_variable_name]))
                        if (is_array($_POST[$wpjobportal_variable_name])) {
                            $wpjobportal_value = filter_var_array($_POST[$wpjobportal_variable_name]);
                        }else{
                            if($editor_data == null){
                                $wpjobportal_value = wpjobportal::wpjobportal_sanitizeData($_POST[$wpjobportal_variable_name]);
                            }else{
                                $wpjobportal_value = filter_var($_POST[$wpjobportal_variable_name],FILTER_DEFAULT);
                            }
                        }
                    break;
                case 'get':
                    if (isset($_GET[$wpjobportal_variable_name]))
                        if (is_array($_GET[$wpjobportal_variable_name])) {
                            $wpjobportal_value = filter_var_array($_GET[$wpjobportal_variable_name]);
                        }else{
                            $wpjobportal_value = wpjobportal::wpjobportal_sanitizeData($_GET[$wpjobportal_variable_name]);
                        }
                    break;
                case 'shortcode_option': // new case to handle shortcode attributes (since many variables have already used names so cant use above methods)
                    if (isset(wpjobportal::$_data['shortcode_options'][$wpjobportal_variable_name]) && wpjobportal::$_data['shortcode_options'][$wpjobportal_variable_name] != '') {
                        $wpjobportal_value = wpjobportal::$_data['shortcode_options'][$wpjobportal_variable_name];
                    }
                    break;
            }
        }
        if ($typecast != null) {
            $typecast = wpjobportalphplib::wpJP_strtolower($typecast);
            switch ($typecast) {
                case "int":
                    $wpjobportal_value = (int) $wpjobportal_value;
                    break;
                case "string":
                    $wpjobportal_value = (string) $wpjobportal_value;
                    break;
            }
        }
        if ($wpjobportal_value == null)
            $wpjobportal_value = $wpjobportal_defaultvalue;
        //echo print_r($wpjobportal_value); exit;
        return $wpjobportal_value;
    }

    /*
     * Check Request from both the Get and post method
     */

    static function get($method = null) {
        $wpjobportal_array = null;
        if ($method != null) {
            $method = wpjobportalphplib::wpJP_strtolower($method);
            switch ($method) {
                case 'post':
                    $wpjobportal_array = filter_var_array($_POST);
                    break;
                case 'get':
                    $wpjobportal_array = filter_var_array($_GET);
                    break;
            }
        }
        return $wpjobportal_array;
    }

    /*
     * Check Request from both the Get and post method
     */

    static function getLayout($wpjobportal_layout, $method, $wpjobportal_defaultvalue) {
        $wpjobportal_layoutname = null;
        if ($method != null) {
            $method = wpjobportalphplib::wpJP_strtolower($method);
            switch ($method) {
                case 'post':
                    $wpjobportal_layoutname = wpjobportal::wpjobportal_sanitizeData($_POST[$wpjobportal_layout]);
                    break;
                case 'get':
                    $wpjobportal_layoutname = wpjobportal::wpjobportal_sanitizeData($_GET[$wpjobportal_layout]);
                    break;
            }
        } else {
            if (isset($_POST[$wpjobportal_layout]))
                $wpjobportal_layoutname = wpjobportal::wpjobportal_sanitizeData($_POST[$wpjobportal_layout]);
            elseif (isset($_GET[$wpjobportal_layout]))
                $wpjobportal_layoutname = wpjobportal::wpjobportal_sanitizeData($_GET[$wpjobportal_layout]);
            elseif (get_query_var($wpjobportal_layout))
                $wpjobportal_layoutname = get_query_var($wpjobportal_layout);
            elseif (isset(wpjobportal::$_data['sanitized_args'][$wpjobportal_layout]) && wpjobportal::$_data['sanitized_args'][$wpjobportal_layout] != '')
                $wpjobportal_layoutname = wpjobportal::$_data['sanitized_args'][$wpjobportal_layout];
        }
        if ($wpjobportal_layoutname == null) {
            $wpjobportal_layoutname = $wpjobportal_defaultvalue;
        }

        $wpjobportal_is_elementor_edit_mode = false;

        if ( class_exists('\Elementor\Plugin') && \Elementor\Plugin::$instance->editor ) {
            $wpjobportal_is_elementor_edit_mode = \Elementor\Plugin::$instance->editor->is_edit_mode();
        }

        if (is_admin() && $wpjobportal_is_elementor_edit_mode == false) {
        //if (is_admin() && !wp_is_json_request() && !wp_doing_ajax()) {
            $wpjobportal_layoutname = 'admin_' . $wpjobportal_layoutname;
        }
        return $wpjobportal_layoutname;
    }

}

?>
