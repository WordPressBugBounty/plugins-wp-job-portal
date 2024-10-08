<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class Indeed{

    const DEFAULT_FORMAT = "json";
    const API_SEARCH_ENDPOINT = "http://api.indeed.com/ads/apisearch";
    const API_JOBS_ENDPOINT = "http://api.indeed.com/ads/apigetjobs";

    private static $API_SEARCH_REQUIRED = array("userip", "useragent", array("q", "l"));
    private static $API_JOBS_REQUIRED = array("jobkeys");

    public function __construct($publisher, $version = "2"){
        $this->publisher = $publisher;
        $this->version = $version;
    }

    public function search($args){
        return $this->process_request(self::API_SEARCH_ENDPOINT, $this->validate_args(self::$API_SEARCH_REQUIRED, $args));
    }

    public function jobs($args){
        $valid_args = $this->validate_args(self::$API_JOBS_REQUIRED, $args);
        $valid_args["jobkeys"] = implode(",", $valid_args['jobkeys']);
        return $this->process_request(self::API_JOBS_ENDPOINT, $valid_args);
    }

    private function process_request($endpoint, $args){
        if(!isset($args["co"]) || ""==$args["co"] || null==$args["co"] ) {
            $args["co"] = "US";
        }
        $format = (array_key_exists("format", $args) ? $args["format"] : self::DEFAULT_FORMAT);
        $raw = ($format == "xml" ? true : (array_key_exists("raw", $args) ? $args["raw"] : false));
        $args["publisher"] = $this->publisher;
        $args["v"] = $this->version;
        $args["format"] = $format;

        $url = $endpoint;
        $post_data = $args;
        $response = wp_remote_post( $url, array('body' => $post_data,'timeout'=>7,'sslverify'=>false));
        if( !is_wp_error($response) && $response['response']['code'] == 200 && isset($response['body']) ){
            $result = $response['body'];
        }else{
           $result = '';
        }



        $r = (!$raw ? json_decode($result, $assoc = true) : $result);
        return $r;
    }

    private function validate_args($required_fields, $args){
        foreach($required_fields as $field){
            if(is_array($field)){
                $has_one_required = false;
                foreach($field as $f){
                    if(array_key_exists($f, $args)){
                        $has_one_required = True;
                        break;
                    }
                }
                if(!$has_one_required){
                    throw new Exception(esc_html(wpjobportal::wpjobportal_getVariableValue("You must provide one of the following %". implode(",", $field))));
                }
            } elseif(!array_key_exists($field, $args)){
                throw new Exception("The field ".esc_html($field)." is required");
            }
        }
        return $args;
    }

}
