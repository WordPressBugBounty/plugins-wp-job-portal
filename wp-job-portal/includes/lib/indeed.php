<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTAL_Indeed{

    const DEFAULT_FORMAT = "json";
    const API_SEARCH_ENDPOINT = "http://api.indeed.com/ads/apisearch";
    const API_JOBS_ENDPOINT = "http://api.indeed.com/ads/apigetjobs";

    private static $API_SEARCH_REQUIRED = array("userip", "useragent", array("q", "l"));
    private static $API_JOBS_REQUIRED = array("jobkeys");

    public function __construct($wpjobportal_publisher, $wpjobportal_version = "2"){
        $this->publisher = $wpjobportal_publisher;
        $this->version = $wpjobportal_version;
    }

    public function search($wpjobportal_args){
        return $this->process_request(self::API_SEARCH_ENDPOINT, $this->validate_args(self::$API_SEARCH_REQUIRED, $wpjobportal_args));
    }

    public function jobs($wpjobportal_args){
        $wpjobportal_valid_args = $this->validate_args(self::$API_JOBS_REQUIRED, $wpjobportal_args);
        $wpjobportal_valid_args["jobkeys"] = implode(",", $wpjobportal_valid_args['jobkeys']);
        return $this->process_request(self::API_JOBS_ENDPOINT, $wpjobportal_valid_args);
    }

    private function process_request($wpjobportal_endpoint, $wpjobportal_args){
        if(!isset($wpjobportal_args["co"]) || ""==$wpjobportal_args["co"] || null==$wpjobportal_args["co"] ) {
            $wpjobportal_args["co"] = "US";
        }
        $wpjobportal_format = (array_key_exists("format", $wpjobportal_args) ? $wpjobportal_args["format"] : self::DEFAULT_FORMAT);
        $raw = ($wpjobportal_format == "xml" ? true : (array_key_exists("raw", $wpjobportal_args) ? $wpjobportal_args["raw"] : false));
        $wpjobportal_args["publisher"] = $this->publisher;
        $wpjobportal_args["v"] = $this->version;
        $wpjobportal_args["format"] = $wpjobportal_format;

        $wpjobportal_url = $wpjobportal_endpoint;
        $post_data = $wpjobportal_args;
        $response = wp_remote_post( $wpjobportal_url, array('body' => $post_data,'timeout'=>7,'sslverify'=>false));
        if( !is_wp_error($response) && $response['response']['code'] == 200 && isset($response['body']) ){
            $wpjobportal_result = $response['body'];
        }else{
           $wpjobportal_result = '';
        }



        $r = (!$raw ? json_decode($wpjobportal_result, $assoc = true) : $wpjobportal_result);
        return $r;
    }

    private function validate_args($wpjobportal_required_fields, $wpjobportal_args){
        foreach($wpjobportal_required_fields as $wpjobportal_field){
            if(is_array($wpjobportal_field)){
                $has_one_required = false;
                foreach($wpjobportal_field as $f){
                    if(array_key_exists($f, $wpjobportal_args)){
                        $has_one_required = True;
                        break;
                    }
                }
                if(!$has_one_required){
                    throw new Exception(esc_html(wpjobportal::wpjobportal_getVariableValue("You must provide one of the following %". implode(",", $wpjobportal_field))));
                }
            } elseif(!array_key_exists($wpjobportal_field, $wpjobportal_args)){
                throw new Exception("The field ".esc_html($wpjobportal_field)." is required");
            }
        }
        return $wpjobportal_args;
    }

}
