<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALuser {

    private $currentuser = null;

    private static $rows = array();

    /**
     * The social-login id of the current visitor, or '' when there is none.
     *
     * Security: the discontinued Social Login add-on identified its users by a
     * plain `wpjobportal-socialid` cookie. A cookie is chosen by the browser,
     * so trusting it let any visitor become any social-login portal user by
     * setting that cookie (and made isguest() false for any non-empty value).
     * Social Login is discontinued in 2.8.0 and nothing sets the cookie any
     * more, so the cookie is never trusted as an identity: this always answers
     * ''. Social-login users sign in with a WordPress account instead.
     *
     * @return string
     */
    public static function trustedSocialId() {
        return '';
    }

    public static function forgetOnWrite($wpjobportal_query) {
        if (!empty(self::$rows) && strpos($wpjobportal_query, 'wj_portal_users') !== false && preg_match('/^\s*(UPDATE|INSERT|REPLACE|DELETE)\b/i', $wpjobportal_query)) {
            self::$rows = array();
        }
        return $wpjobportal_query;
    }

    function __construct() {
        if (is_user_logged_in()) { // wp user logged in
            $wpuserid = get_current_user_id();
            if (!is_numeric($wpuserid))
                return false;
            // P-2: this class is built for almost every check on a page. Any write to the
            // users table empties the remembered row, so a new role shows at once.
            if (!array_key_exists($wpuserid, self::$rows)) {
                if (!has_filter('query', array(__CLASS__, 'forgetOnWrite'))) {
                    add_filter('query', array(__CLASS__, 'forgetOnWrite'));
                }
                $query = wpjobportal::$_db->prepare("SELECT * FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` WHERE uid = %d", $wpuserid);
                self::$rows[$wpuserid] = wpjobportal::$_db->get_row($query);
            }
            $this->currentuser = self::$rows[$wpuserid];
        }else { // wp user is not logged in
        //sanitize_key($_SESSION['wpjobportal-socialid'])
            if (self::trustedSocialId() !== '') { // social user is logged in
            
                $wpjobportal_socialid = self::trustedSocialId();
                $query = wpjobportal::$_db->prepare("SELECT * FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` WHERE socialid = %s", $wpjobportal_socialid);
                $this->currentuser = wpjobportal::$_db->get_row($query);
            }
        }
    }

    function isguest() {
        if (self::trustedSocialId() !== '') {
            return false;
        } elseif ($this->currentuser == null && !is_user_logged_in()) { // current user is guest
            return true;
        } else {
            return false;
        }
    }

    function getEmployerProfile(){
        //if($this->isemployer()==true){
        /*
         * There may be no portal user behind the WordPress one.
         *
         * currentuser is the wj_portal_users row for whoever is signed in, and
         * somebody can be signed in without having one - a Recruit Suite team
         * member is added to a company, not registered through the portal. The
         * isemployer() guard above used to catch that and was commented out,
         * which left this reading ->uid off null and warning about it on every
         * recruiter page load.
         *
         * The test is for the missing row rather than for the employer role,
         * because a recruiter carries roleid 0: putting isemployer() back would
         * stop the warning by taking the profile away from the very people who
         * were seeing it. No row, no profile - which is what the tail of this
         * function already answers.
         */
        if ($this->currentuser == null) {
            return false;
        }
        $wpjobportal_id  = $this->currentuser->uid;
        $wpjobportal_string = "users.uid";
        if (self::trustedSocialId() !== '') { // social user is logged in
            $wpjobportal_id  = $this->currentuser->id;
            $wpjobportal_string = "users.id";
        }
        if(is_numeric($wpjobportal_id)){
            $query = "SELECT users.id,users.uid,users.first_name,users.photo,users.emailaddress,users.last_name,users.socialmedia
            FROM " . wpjobportal::$_db->prefix . "wj_portal_users AS users  
            WHERE users.id != 0 AND " . $wpjobportal_string . " = %d  LIMIT 1 ";
            $query = wpjobportal::$_db->prepare($query, $wpjobportal_id);
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
            if (self::trustedSocialId() !== '') { // social user is logged in
                $wpjobportal_id  = $this->currentuser->id;
                $wpjobportal_string = "users.id";
            }
            if(is_numeric($wpjobportal_id)){
                $query = "SELECT users.id,users.uid,users.first_name,users.photo,users.emailaddress,users.last_name,users.socialmedia
                FROM " . wpjobportal::$_db->prefix . "wj_portal_users AS users
                WHERE users.id != 0 AND " . $wpjobportal_string . " = %d  LIMIT 1 ";
            $query = wpjobportal::$_db->prepare($query, $wpjobportal_id);
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
            if (!empty($this->currentuser)) {
                return true;
            } else {
                return false;
            }
        } else {
            if (self::trustedSocialId() !== '') { // social user is logged in
                $wpjobportal_socialid = self::trustedSocialId();
                $query = wpjobportal::$_db->prepare("SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` WHERE socialid = %s", $wpjobportal_socialid);
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
            $query = wpjobportal::$_db->prepare("SELECT roleid FROM ".wpjobportal::$_db->prefix."wj_portal_users WHERE id = %d", $wpjobportal_uid);
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
                    // Accounts without a first/last name showed blank in admin lists; fall back to the WP display name, login, then email.
                    $query = wpjobportal::$_db->prepare("SELECT TRIM(CONCAT(IFNULL(user.first_name,''),' ',IFNULL(user.last_name,''))) AS name, wpuser.display_name, wpuser.user_login, user.emailaddress
                        FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` AS user
                        LEFT JOIN `" . wpjobportal::$_db->users . "` AS wpuser ON wpuser.ID = user.uid
                        WHERE user.`ID` = %d", $wpjobportal_uid);
                    $wpjobportal_row = wpjobportal::$_db->get_row($query);
                    if (!$wpjobportal_row) {
                        return '';
                    }
                    foreach (array('name', 'display_name', 'user_login', 'emailaddress') as $wpjobportal_key) {
                        if (!empty($wpjobportal_row->$wpjobportal_key)) {
                            return $wpjobportal_row->$wpjobportal_key;
                        }
                    }
                    return '';
                }else{
                    return '';
                }
            }else{
                return '';
            }
        }
    }

    function getAvailableCredits() {
        $wpjobportal_isadmin = (current_user_can('manage_options') ? WPJOBPORTALrequest::getVar('isadmin') : ''); // Security: admin-only flag, never trusted from a non-admin request.
        $wpjobportal_userid = WPJOBPORTALrequest::getVar('userid');
        if($wpjobportal_isadmin && is_numeric($wpjobportal_userid)){
            $wpjobportal_uid = $wpjobportal_userid;
        }else{
            $wpjobportal_uid = $this->uid();
        }
        $wpjobportal_credits = WPJOBPORTALIncluder::getJSModel('user')->getMyAvailableCredits($wpjobportal_uid);
        return $wpjobportal_credits;
    }

    function isWPJOBPortalUser() {
        if (is_user_logged_in()) { // wp user logged in
            $wpuserid = get_current_user_id();
            if (!is_numeric($wpuserid))
                return false;
            if (!empty($this->currentuser)) {
                return true;
            } else {
                return false;
            }
        } else {
            if (self::trustedSocialId() !== '') { // social user is logged in
                $wpjobportal_socialid = self::trustedSocialId();
                $query = wpjobportal::$_db->prepare("SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` WHERE socialid = %s", $wpjobportal_socialid);
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
        if (self::trustedSocialId() !== '') {
            return true;
        } else {
            return false;
        }
    }
    
    function getwpjobportaluidbyuserid($wpjobportal_userid){
        if(!is_numeric($wpjobportal_userid)) return false;
        $query = wpjobportal::$_db->prepare("SELECT id FROM `".wpjobportal::$_db->prefix."wj_portal_users` WHERE uid = %d", $wpjobportal_userid);
        $wpjobportal_uid = wpjobportal::$_db->get_var($query);
        return $wpjobportal_uid;
    }

}

?>
