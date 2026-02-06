<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALuser {

    private $currentuser = null;

    function __construct() {
        if (is_user_logged_in()) { // wp user logged in
            $wpuserid = get_current_user_id();
            if (!is_numeric($wpuserid))
                return false;
            $query = "SELECT * FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` WHERE uid = " . esc_sql($wpuserid);
            $this->currentuser = wpjobportal::$_db->get_row($query);
        }else { // wp user is not logged in
        //sanitize_key($_SESSION['wpjobportal-socialid'])
            if (isset($_COOKIE['wpjobportal-socialid']) && !empty($_COOKIE['wpjobportal-socialid'])) { // social user is logged in
            
                $query = "SELECT * FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` WHERE socialid = '" . sanitize_key($_COOKIE['wpjobportal-socialid']) . "'";
                $this->currentuser = wpjobportal::$_db->get_row($query);
            }
        }
    }

    function isguest() {
        if (isset($_COOKIE['wpjobportal-socialid']) && !empty($_COOKIE['wpjobportal-socialid'])) {
            return false;
        } elseif ($this->currentuser == null && !is_user_logged_in()) { // current user is guest
            return true;
        } else {
            return false;
        }
    }

    function getEmployerProfile(){
        //if($this->isemployer()==true){
        $wpjobportal_id  = $this->currentuser->uid;
        $wpjobportal_string = "users.uid";
        if (isset($_COOKIE['wpjobportal-socialid']) && !empty($_COOKIE['wpjobportal-socialid'])) { // social user is logged in
            $wpjobportal_id  = $this->currentuser->id;
            $wpjobportal_string = "users.id";
        }
        if(is_numeric($wpjobportal_id)){
            $query = "SELECT users.id,users.uid,users.first_name,users.photo,users.emailaddress,users.last_name,users.socialmedia
            FROM " . wpjobportal::$_db->prefix . "wj_portal_users AS users  
            WHERE users.id != 0 AND ".esc_sql($wpjobportal_string)."=".esc_sql($wpjobportal_id) ."  LIMIT 1 ";
            return wpjobportal::$_db->get_row($query);
        }
        
        
        //}else{
            return false;
        //}
    }

    function getJobSeekerProfile(){
        if($this->isjobseeker()==true){
            $wpjobportal_id  = $this->currentuser->uid;
            $wpjobportal_string = "users.uid";
            if (isset($_COOKIE['wpjobportal-socialid']) && !empty($_COOKIE['wpjobportal-socialid'])) { // social user is logged in
                $wpjobportal_id  = $this->currentuser->id;
                $wpjobportal_string = "users.id";
            }
            if(is_numeric($wpjobportal_id)){
                $query = "SELECT users.id,users.uid,users.first_name,users.photo,users.emailaddress,users.last_name,users.socialmedia
                FROM " . wpjobportal::$_db->prefix . "wj_portal_users AS users
                WHERE users.id != 0 AND ".esc_sql($wpjobportal_string)."=".esc_sql($wpjobportal_id) ."  LIMIT 1 ";
                return wpjobportal::$_db->get_row($query);
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    function isisWPJobportalUser() {
        if (is_user_logged_in()) { // wp user logged in
            $wpuserid = get_current_user_id();
            if (!is_numeric($wpuserid))
                return false;
            $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` WHERE uid = " . esc_sql($wpuserid);
            $wpjobportal_result = wpjobportal::$_db->get_var($query);
            if ($wpjobportal_result > 0) {
                return true;
            } else {
                return false;
            }
        } else {
            if (isset($_COOKIE['wpjobportal-socialid']) && !empty($_COOKIE['wpjobportal-socialid'])) { // social user is logged in
                $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` WHERE socialid = '" . sanitize_key($_COOKIE['wpjobportal-socialid']) . "'";
                $wpjobportal_result = wpjobportal::$_db->get_var($query);
                if ($wpjobportal_result > 0) {
                    return true;
                } else {
                    return false;
                }
            }
        }
    }

    function isdisabled() {
        if ($this->currentuser != null && $this->currentuser->status == 0) { // current user is disabled
            return true;
        } else {
            return false;
        }
    }

    function getJobseekerLogo(){
        if($this->isjobseeker()==true){
            if(is_numeric($this->currentuser->uid)){
                $query = "SELECT resume.id,resume.photo
                FROM " . wpjobportal::$_db->prefix . "wj_portal_resume AS resume  
                WHERE resume.status != 0 AND resume.uid=".esc_sql($this->currentuser->uid)." AND resume.photo !='' LIMIT 1 ";
                return wpjobportal::$_db->get_row($query);
            }
        }
        return false;
    }
    function getEmployerLogo(){
        if($this->isemployer()==true){
            if(is_numeric($this->currentuser->uid)){
                $query = "SELECT company.id,company.name,company.logofilename
                FROM " . wpjobportal::$_db->prefix . "wj_portal_companies AS company  
                WHERE company.status != 0 AND company.uid=".esc_sql($this->currentuser->uid)." AND company.logofilename !='' LIMIT 1 ";
                return wpjobportal::$_db->get_row($query);
            }
        }
        return false;
    }
    function isemployer() {
        if ($this->currentuser == null) { // current user is guest
            return false;
        } else {
            if ($this->currentuser->roleid == 1) {
                return true;
            } else {
                return false;
            }
        }
    }

    function roleid($wpjobportal_uid=0){
        if(is_numeric($wpjobportal_uid) && $wpjobportal_uid != 0){
            $query = "SELECT roleid FROM ".wpjobportal::$_db->prefix."wj_portal_users WHERE id = ".esc_sql($wpjobportal_uid);
            return wpjobportaldb::get_var($query); 
        }elseif($this->currentuser != null) {
            return $this->currentuser->roleid;
        }
    }

    function isjobseeker() {
        if ($this->currentuser == null) { // current user is guest
            return false;
        } else {
            if ($this->currentuser->roleid == 2) {
                return true;
            } else {
                return false;
            }
        }
    }

    function uid() {
        if ($this->currentuser != null) {
            return $this->currentuser->id;
        }
    }

    function getWPuid() {
        if ($this->currentuser != null) {
            return $this->currentuser->uid;
        }
    }

    function emailaddress() {
        if ($this->currentuser == null) { // current user is guest
            return false;
        } else {
            return $this->currentuser->emailaddress;
        }
    }

    function fullname($wpjobportal_uid='') {
        if($wpjobportal_uid==''){
            if ($this->currentuser == null) { // current user is guest
                return false;
            } else {
                $wpjobportal_name = $this->currentuser->first_name . ' ' . $this->currentuser->last_name;
                return $wpjobportal_name;
            }
        }else{
            if(is_numeric($wpjobportal_uid)){
                if(wpjobportal::$_common->wpjp_isadmin()){
                    $query = "SELECT CONCAT(first_name,' ',last_name) FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` WHERE `ID` = " . esc_sql($wpjobportal_uid);
                    return wpjobportal::$_db->get_var($query);
                }else{
                    return '';
                }
            }else{
                return '';
            }
        }
    }

    function getAvailableCredits() {
        $wpjobportal_isadmin = WPJOBPORTALrequest::getVar('isadmin');
        $wpjobportal_userid = WPJOBPORTALrequest::getVar('userid');
        if($wpjobportal_isadmin && is_numeric($wpjobportal_userid)){
            $wpjobportal_uid = $wpjobportal_userid;
        }else{
            $wpjobportal_uid = $this->uid();
        }
        $wpjobportal_credits = WPJOBPORTALIncluder::getJSModel('user')->getMyAvailableCredits($wpjobportal_uid);
        return $wpjobportal_credits;
    }

    function getAvailableCreditsForUser($wpjobportal_uid) {
        if (!is_numeric($wpjobportal_uid))
            return false;
        $wpjobportal_credits = WPJOBPORTALIncluder::getJSModel('user')->getMyAvailableCredits($wpjobportal_uid);
        return $wpjobportal_credits;
    }

    function isWPJOBPortalUser() {
        if (is_user_logged_in()) { // wp user logged in
            $wpuserid = get_current_user_id();
            if (!is_numeric($wpuserid))
                return false;
            $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` WHERE uid = " . esc_sql($wpuserid);
            $wpjobportal_result = wpjobportal::$_db->get_var($query);
            if ($wpjobportal_result > 0) {
                return true;
            } else {
                return false;
            }
        } else {
            if (isset($_COOKIE['wpjobportal-socialid']) && !empty($_COOKIE['wpjobportal-socialid'])) { // social user is logged in
                $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` WHERE socialid = '" . sanitize_key($_COOKIE['wpjobportal-socialid']) . "'";
                $wpjobportal_result = wpjobportal::$_db->get_var($query);
                if ($wpjobportal_result > 0) {
                    return true;
                } else {
                    return false;
                }
            }
        }
    }

    function isSocialLogin() {
        if (isset($_COOKIE['wpjobportal-socialid']) && !empty($_COOKIE['wpjobportal-socialid'])) {
            return true;
        } else {
            return false;
        }
    }
    
    function getwpjobportaluidbyuserid($wpjobportal_userid){
        if(!is_numeric($wpjobportal_userid)) return false;
        $query = "SELECT id FROM `".wpjobportal::$_db->prefix."wj_portal_users` WHERE uid = ".esc_sql($wpjobportal_userid);
        $wpjobportal_uid = wpjobportal::$_db->get_var($query);
        return $wpjobportal_uid;
    }

}

?>
