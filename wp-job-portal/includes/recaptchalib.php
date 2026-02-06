<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

    if(! function_exists('wpjobportal_googleRecaptchaHTTPPost')){
        function wpjobportal_googleRecaptchaHTTPPost($sharedkey , $grresponse) {
            $wpjobportal_url = "https://www.google.com/recaptcha/api/siteverify";
            $wpjobportal_secret = $sharedkey;
            $wpjobportal_ip = $_SERVER['REMOTE_ADDR'];
            
            $post_data = array();
            $post_data['secret'] = $wpjobportal_secret;
            $post_data['response'] = $grresponse;
            $post_data['remoteip'] = $wpjobportal_ip;

            $response = wp_remote_post( $wpjobportal_url, array('body' => $post_data,'timeout'=>7,'sslverify'=>false));
            if( !is_wp_error($response) && $response['response']['code'] == 200 && isset($response['body']) ){
                $wpjobportal_result = $response['body'];
            }else{
                $wpjobportal_result = false;
                if(!is_wp_error($response)){
                   $error = $response['response']['message'];
               }else{
                    $error = $response->get_error_message();
               }
            }
            if($wpjobportal_result){
                $res= json_decode($wpjobportal_result, true);
            }else{
                return FALSE;
            }
            //reCaptcha success check
            if($res['success']) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
    }
?>