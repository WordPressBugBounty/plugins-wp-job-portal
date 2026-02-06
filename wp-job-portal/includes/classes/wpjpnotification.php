<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALwpjpnotification {

    function __construct( ) {

    }

    public function addSessionNotificationDataToTable($message, $wpjobportal_msgtype, $wpjobportal_sessiondatafor = 'notification',$wpjobportal_msgkey = 'captcha'){
        /*$message belows to repsonse message
        $wpjobportal_msgtyp belongs to reponse type eg error or success
        $wpjobportal_sessiondatafor belong to any random thing or reponse notification after saving some data
        $wpjobportal_msgkey belong to module
        */
        if($message == ''){
            if(!is_numeric($message))
                return false;
        }
        $wpjobportal_data = array();
        $wpjobportal_update = false;
        if(isset($_COOKIE['_wpjsjp_session_']) && isset(wpjobportal::$_jsjpsession->sessionid)){
            if($wpjobportal_sessiondatafor == 'notification'){
                $wpjobportal_data = $this->getNotificationDatabySessionId($wpjobportal_sessiondatafor);
                if(empty($wpjobportal_data)){
                    $wpjobportal_data['msg'][0] = $message;
                    $wpjobportal_data['type'][0] = $wpjobportal_msgtype;
                }else{
                    $wpjobportal_update = true;
                    $wpjobportal_count = count($wpjobportal_data['msg']);
                    $wpjobportal_data['msg'][$wpjobportal_count] = $message;
                    $wpjobportal_data['type'][$wpjobportal_count] = $wpjobportal_msgtype;
                }
            }

            if($wpjobportal_sessiondatafor == 'wpjobportal_spamcheckid'){
                $wpjobportal_msgkey = 'captcha';
                $wpjobportal_data = $this->getNotificationDatabySessionId($wpjobportal_sessiondatafor,$wpjobportal_msgkey);
                if($wpjobportal_data != ""){
                    $wpjobportal_update = true;
                    $wpjobportal_data = $message;
                }else{
                    $wpjobportal_data = $message;
                }
            }
            if($wpjobportal_sessiondatafor == 'wpjobportal_rot13'){
                $wpjobportal_msgkey = 'captcha';
                $wpjobportal_data = $this->getNotificationDatabySessionId($wpjobportal_sessiondatafor,$wpjobportal_msgkey);
                if($wpjobportal_data != ""){
                    $wpjobportal_update = true;
                    $wpjobportal_data = $message;
                }else{
                    $wpjobportal_data = $message;
                }
            }
            if($wpjobportal_sessiondatafor == 'wpjobportal_spamcheckresult'){
                $wpjobportal_msgkey = 'captcha';
                $wpjobportal_data = $this->getNotificationDatabySessionId($wpjobportal_sessiondatafor,$wpjobportal_msgkey);
                if($wpjobportal_data != ""){
                    $wpjobportal_update = true;
                    $wpjobportal_data = $message;
                }else{
                    $wpjobportal_data = $message;
                }
            }


            $wpjobportal_data = wp_json_encode($wpjobportal_data , true);
            $wpjobportal_sessionmsg = wpjobportalphplib::wpJP_safe_encoding($wpjobportal_data);
            $wpjobportal_newexp = time() + (int)(30); // 30 sec
            if(!$wpjobportal_update){
                wpjobportal::$_db->insert( wpjobportal::$_db->prefix."wj_portal_jswjsessiondata", array("usersessionid" => wpjobportal::$_jsjpsession->sessionid, "sessionmsg" => $wpjobportal_sessionmsg, "sessionexpire" => $wpjobportal_newexp, "sessionfor" => $wpjobportal_sessiondatafor , "msgkey" => $wpjobportal_msgkey) );
            }else{
                wpjobportal::$_db->update( wpjobportal::$_db->prefix."wj_portal_jswjsessiondata", array("sessionmsg" => $wpjobportal_sessionmsg), array("usersessionid" => wpjobportal::$_jsjpsession->sessionid , 'sessionfor' => $wpjobportal_sessiondatafor) );
            }
        }
        return false;
    }

    public function getNotificationDatabySessionId($wpjobportal_sessionfor , $wpjobportal_msgkey = null, $wpjobportal_deldata = false){
        if(wpjobportal::$_jsjpsession->sessionid == '')
            return false;

        $query = "SELECT sessionmsg FROM " . wpjobportal::$_db->prefix . "wj_portal_jswjsessiondata WHERE usersessionid = '" . esc_sql(wpjobportal::$_jsjpsession->sessionid) . "' AND sessionfor = '" . esc_sql($wpjobportal_sessionfor) . "' AND sessionexpire > '" . time() . "'";
        $wpjobportal_data = wpjobportal::$_db->get_var($query);

        if(!empty($wpjobportal_data)){
            $wpjobportal_data = wpjobportalphplib::wpJP_safe_decoding($wpjobportal_data);
            $wpjobportal_data = json_decode( $wpjobportal_data , true);
            //$wpjobportal_deldata = true; // to remove notices once shown
        }
        if($wpjobportal_deldata){
            wpjobportal::$_db->delete( wpjobportal::$_db->prefix."wj_portal_jswjsessiondata", array( 'usersessionid' => wpjobportal::$_jsjpsession->sessionid , 'sessionfor' => $wpjobportal_sessionfor , 'msgkey' => $wpjobportal_msgkey) );
        }
        return $wpjobportal_data;
    }

}

?>
