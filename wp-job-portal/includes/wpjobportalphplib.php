<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class wpjobportalphplib {

    function __construct() {
    }

    static function wpJP_str_replace($wpjobportal_search,$replace,$wpjobportal_content){
        if($wpjobportal_content == ''){
            return $wpjobportal_content;
        }
        if($replace === null){
            return $wpjobportal_content;
        }

        $wpjobportal_content = str_replace($wpjobportal_search, $replace, $wpjobportal_content);
        return $wpjobportal_content;
    }

    static function wpJP_safe_encoding($wpjobportal_string){
        if($wpjobportal_string == ''){
            return $wpjobportal_string;
        }
        $wpjobportal_string = base64_encode($wpjobportal_string);
        //return mb_convert_encoding($wpjobportal_string, 'UTF-8', mb_detect_encoding($wpjobportal_string));
        return $wpjobportal_string;
    }

    static function wpJP_safe_decoding($wpjobportal_string){
        if($wpjobportal_string == ''){
            return $wpjobportal_string;
        }
        $wpjobportal_string = base64_decode($wpjobportal_string);
        return $wpjobportal_string;
    }


    public static function wpJP_strstr($haystack, $wpjobportal_needle) {
        if($haystack == '' || $wpjobportal_needle == ''){
            return false;
        }
        return strstr($haystack, $wpjobportal_needle);
    }

    public static function wpJP_explode($wpjobportal_separator, $haystack) {
        if($wpjobportal_separator == ''){
            return array();
        }
        if($haystack == ''){
            return array();
        }
        return explode($wpjobportal_separator, $haystack);
    }

    // public static function wpJP_strip_tags($wpjobportal_string, $wpjobportal_allowed_tags) {
    //     if($wpjobportal_string == ''){
    //         return '';
    //     }
    //     return strip_tags($wpjobportal_string, $wpjobportal_allowed_tags);
    // }
    public static function wpJP_strip_tags($wpjobportal_string, $wpjobportal_allowable_tags = NULL) {
      if (!is_null($wpjobportal_string)) {
        return strip_tags($wpjobportal_string, $wpjobportal_allowable_tags);
      }
      return $wpjobportal_string;
    }


    public static function wpJP_htmlentities($wpjobportal_string) {
        if($wpjobportal_string == ''){
            return '';
        }
        return htmlentities($wpjobportal_string);
    }

    public static function wpJP_strtoupper($wpjobportal_string) {
        if($wpjobportal_string == ''){
            return '';
        }
        return strtoupper($wpjobportal_string);
    }

    public static function wpJP_basename($wpjobportal_string,$wpjobportal_suffix = '') {
        $basename = '';
        if($wpjobportal_string !== ''){
           $basename = basename($wpjobportal_string,$wpjobportal_suffix);
        }
        return $basename;
    }

    public static function wpJP_dirname($wpjobportal_string,$lvls = 1) {
        $wpjobportal_dirname = '';
        if($wpjobportal_string !== ''){
           $wpjobportal_dirname = dirname($wpjobportal_string,$lvls);
        }
        return $wpjobportal_dirname;
    }


    public static function wpJP_substr($wpjobportal_str, $wpjobportal_start, $length = null) {
        $output = null;
        if ($wpjobportal_str !== null) {
            if ($length !== null) {
                $output = substr($wpjobportal_str, $wpjobportal_start, $length);
            } else {
                $output = substr($wpjobportal_str, $wpjobportal_start);
            }
        }
        return $output;
    }


    public static function wpJP_ucwords($wpjobportal_str, $wpjobportal_delimiters = "") {
        $output = null;
        if ($wpjobportal_str !== null) {
            $output = ucwords($wpjobportal_str, $wpjobportal_delimiters);
        }
        return $output;
    }

    public static function wpJP_str_rot13($wpjobportal_str){
        $output = null;
        if ($wpjobportal_str !== null) {
            $wpjobportal_original = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $rotated  = 'nopqrstuvwxyzabcdefghijklmNOPQRSTUVWXYZABCDEFGHIJKLM';
            $output = strtr($wpjobportal_str, $wpjobportal_original, $rotated);
        }
        return $output;
    }

    public static function wpJP_preg_replace($pattern, $replacement, $wpjobportal_subject, $limit = -1, &$wpjobportal_count = null){
        $output = null;
        if ($pattern !== null && $replacement !== null && $wpjobportal_subject !== null) {
            $output = preg_replace($pattern, $replacement, $wpjobportal_subject, $limit, $wpjobportal_count);
        }
        return $output;
    }

    public static function wpJP_strlen($wpjobportal_str){
        $output = null;
        if ($wpjobportal_str !== null) {
            $output = strlen($wpjobportal_str);
        }
        return $output;
    }


    public static function wpJP_md5($wpjobportal_str, $raw_output = false){
        $output = null;
        if ($wpjobportal_str !== null) {
            $output = md5($wpjobportal_str, $raw_output);
        }
        return $output;
    }

    public static function wpJP_preg_match($pattern, $wpjobportal_subject, &$wpjobportal_matches = null, $flags = 0, $offset = 0){
        $output = null;
        if ($pattern !== null && $wpjobportal_subject !== null) {
            $output = preg_match($pattern, $wpjobportal_subject, $wpjobportal_matches, $flags, $offset);
        }
        return $output;
    }

    public static function wpJP_strtolower($wpjobportal_str){
        $output = null;
        if ($wpjobportal_str !== null) {
            $output = strtolower($wpjobportal_str);
        }
        return $output;
    }

    public static function wpJP_strpos($haystack, $wpjobportal_needle, $offset = 0){
        $output = null;
        if ($haystack !== null && $wpjobportal_needle !== null) {
            $output = strpos($haystack, $wpjobportal_needle, $offset);
        }
        return $output;
    }

    public static function wpJP_str_repeat($wpjobportal_input, $multiplier){
        $output = null;
        if ($wpjobportal_input !== null && $multiplier !== null) {
            $output = str_repeat($wpjobportal_input, $multiplier);
        }
        return $output;
    }

    public static function wpJP_stripslashes($wpjobportal_str){
        $output = null;
        if ($wpjobportal_str !== null) {
            $output = stripslashes($wpjobportal_str);
        }
        return $output;
    }

    public static function wpJP_htmlspecialchars($wpjobportal_string, $flags = ENT_COMPAT | ENT_HTML401, $wpjobportal_encoding = 'UTF-8', $double_encode = true){
        $output = null;
        if ($wpjobportal_string !== null) {
            $output = htmlspecialchars($wpjobportal_string, $flags, $wpjobportal_encoding, $double_encode);
        }
        return $output;
    }

    public static function wpJP_setcookie($wpjobportal_name, $wpjobportal_value = "", $wpjobportal_expires = 0, $wpjobportal_path = "", $domain = "", $wpjobportal_secure = false, $httponly = false){
        $output = null;
        if ($wpjobportal_name != null && $domain !== null) {
          	$output = setcookie($wpjobportal_name, $wpjobportal_value, $wpjobportal_expires, $wpjobportal_path, $domain, $wpjobportal_secure, $httponly);
        }
        return $output;
    }

    public static function wpJP_urlencode($wpjobportal_str){
        $output = null;
        if ($wpjobportal_str !== null) {
            $output = urlencode($wpjobportal_str);
        }
        return $output;
    }

    public static function wpJP_crypt($wpjobportal_str, $wpjobportal_salt = null)
    {
        $output = null;
        if ($wpjobportal_str !== null) {
            if ($wpjobportal_salt !== null) {
                $output = crypt($wpjobportal_str, $wpjobportal_salt);
            } else {
                $output = crypt($wpjobportal_str);
            }
        }
        return $output;
    }

    public static function wpJP_urldecode($wpjobportal_str)
    {
        $output = null;
        if ($wpjobportal_str !== null) {
            $output = urldecode($wpjobportal_str);
        }
        return $output;
    }

    public static function wpJP_trim($wpjobportal_str, $charlist = ""){
        $output = null;
        if ($wpjobportal_str !== null) {
            $output = trim($wpjobportal_str, $charlist);
        }
        return $output;
    }

    public static function wpJP_rtrim($wpjobportal_str, $chars = null){
        $output = null;
        if ($wpjobportal_str !== null) {
            if ($chars !== null) {
                $output = rtrim($wpjobportal_str, $chars);
            } else {
                $output = rtrim($wpjobportal_str);
            }
        }
        return $output;
    }

    public static function wpJP_stristr($haystack, $wpjobportal_needle, $before_needle = false)
    {
        $output = null;
        if ($haystack !== null && $wpjobportal_needle !== null) {
            $output = stristr($haystack, $wpjobportal_needle, $before_needle);
        }
        return $output;
    }

    public static function wpJP_ucfirst($wpjobportal_str){
        $output = null;
        if ($wpjobportal_str !== null) {
            $output = ucfirst($wpjobportal_str);
        }
        return $output;
    }

    public static function wpJP_parse_str($wpjobportal_str, &$output){
        if ($wpjobportal_str !== null) {
            parse_str($wpjobportal_str, $output);
        }
    }


    public static function wpJP_preg_split($pattern, $wpjobportal_subject, $limit = -1, $flags = 0){
        $output = null;
        if ($pattern !== null && $wpjobportal_subject !== null) {
            $output = preg_split($pattern, $wpjobportal_subject, $limit, $flags);
        }
        return $output;
    }

    public static function wpJP_number_format($wpjobportal_num,$wpjobportal_decimals = 0,$wpjobportal_decimal_separator = ".",$thousands_separator = ","){
        $output = null;
        if ($wpjobportal_num !== null) {
            $output = number_format($wpjobportal_num,$wpjobportal_decimals,$wpjobportal_decimal_separator,$thousands_separator);
        }
        return $output;
    }

    public static function wpJP_strtotime($wpjobportal_datetime, $baseTimestamp = null){
        $output = null;
        if ($wpjobportal_datetime !== null) {
            $output = strtotime($wpjobportal_datetime, $baseTimestamp);
        }
        return $output;
    }

    public static function wpJP_mb_strpos($haystack, $wpjobportal_needle, $offset = 0){ // this function was missed
        $output = null;
        if ($haystack !== null && $wpjobportal_needle !== null) {
            $output = mb_strpos($haystack, $wpjobportal_needle, $offset);
        }
        return $output;
    }


    public static function wpJP_clean_file_path($wpjobportal_path){ // this function to remove relative path componenets from module and file name
        if($wpjobportal_path != ''){
            $wpjobportal_path = str_replace('./','',$wpjobportal_path);
            $wpjobportal_path = str_replace('..','',$wpjobportal_path);
        }
        return $wpjobportal_path;
    }



}
?>
