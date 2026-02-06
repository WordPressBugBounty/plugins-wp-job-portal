<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALUserModel {

    function jsGetPrefix(){
        global $wpdb;
        if(is_multisite()) {
            $wpjobportal_prefix = $wpdb->base_prefix;
        }else{
            $wpjobportal_prefix = wpjobportal::$_db->prefix;
        }
        return $wpjobportal_prefix;
    }

       function getMyAvailableCredits($wpjobportal_uid) {
            if (!is_numeric($wpjobportal_uid))
            return false;
        $query = "SELECT purchase.purchasecredit AS credits,purchase.expireindays,purchase.created
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_purchasehistory` AS purchase
                    WHERE purchase.uid = ". esc_sql($wpjobportal_uid)." AND purchase.transactionverified = 1 ORDER BY purchase.id ASC";
        $wpjobportal_credits = wpjobportal::$_db->get_results($query);
        $wpjobportal_totalcredits = 0;
        $wpjobportal_expireindays = 7;
        $lastpurchase = '';
        foreach ($wpjobportal_credits AS $wpjobportal_credit) {
            $wpjobportal_totalcredits += $wpjobportal_credit->credits;
            $wpjobportal_expireindays = $wpjobportal_credit->expireindays;
            $lastpurchase = $wpjobportal_credit->created;
        }
        if($wpjobportal_expireindays > 7900) // php max limit
            $wpjobportal_expireindays = 7900;

        $lastpurchasedate = gmdate('Y-m-d', strtotime($lastpurchase));
        $wpjobportal_expirydate = gmdate('Y-m-d', strtotime($lastpurchasedate . " + $wpjobportal_expireindays days"));
        $wpjobportal_curdate = gmdate('Y-m-d');
        if ($wpjobportal_expirydate > $wpjobportal_curdate) { // credits are valid
            $query = "SELECT credits FROM `" . wpjobportal::$_db->prefix . "wj_portal_credits_log` WHERE uid = ". esc_sql($wpjobportal_uid);
            $wpjobportal_creditslog = wpjobportal::$_db->get_results($query);
            $wpjobportal_totalusecredits = 0;
            foreach ($wpjobportal_creditslog AS $wpjobportal_log) {
                $wpjobportal_totalusecredits += $wpjobportal_log->credits;
            }
            $available = $wpjobportal_totalcredits - $wpjobportal_totalusecredits;
            return $available;
        } else { // credits are expired
            return 0;
        }
    }

    function getAllUsers() {

        //Filters
        $wpjobportal_searchname = wpjobportal::$_search['user']['searchname'];
        $wpjobportal_searchusername = wpjobportal::$_search['user']['searchusername'];
        $wpjobportal_searchrole = wpjobportal::$_search['user']['searchrole'];
        $wpjobportal_searchcompany = wpjobportal::$_search['user']['searchcompany'];
        $wpjobportal_searchresume = wpjobportal::$_search['user']['searchresume'];

        wpjobportal::$_data['filter']['searchname'] = $wpjobportal_searchname;
        wpjobportal::$_data['filter']['searchusername'] = $wpjobportal_searchusername;
        wpjobportal::$_data['filter']['searchrole'] = $wpjobportal_searchrole;
        wpjobportal::$_data['filter']['searchcompany'] = $wpjobportal_searchcompany;
        wpjobportal::$_data['filter']['searchresume'] = $wpjobportal_searchresume;

        $clause = " WHERE ";
        $wpjobportal_inquery = '';
        if ($wpjobportal_searchname) {
            $wpjobportal_inquery .= esc_sql($clause) . "(LOWER(a.first_name) LIKE '%" . esc_sql($wpjobportal_searchname) . "%' OR LOWER(a.last_name) LIKE '%" . esc_sql($wpjobportal_searchname) . "%')";
            $clause = " AND ";
        }
        if ($wpjobportal_searchusername) {
            $wpjobportal_inquery .= esc_sql($clause) . " LOWER(u.user_login) LIKE '%" . esc_sql($wpjobportal_searchusername) . "%'";
            $clause = " AND ";
        }
        $wpjobportal_company_join = '';
        if ($wpjobportal_searchcompany) {
            $wpjobportal_inquery .= esc_sql($clause) . " LOWER(company.name) LIKE '%" . esc_sql($wpjobportal_searchcompany) . "%'";
            $clause = " AND ";
            $wpjobportal_company_join = 'LEFT JOIN ' . wpjobportal::$_db->prefix . 'wj_portal_companies AS company ON company.uid = a.id ';

        }
        $wpjobportal_resume_join = '';
        if ($wpjobportal_searchresume) {
            $wpjobportal_inquery .= esc_sql($clause) . " ( LOWER(resume.first_name) LIKE '%" . esc_sql($wpjobportal_searchresume) . "%'
                        OR LOWER(resume.last_name) LIKE '%" . esc_sql($wpjobportal_searchresume) . "%')";
            $clause = " AND ";
            $wpjobportal_resume_join = 'LEFT JOIN ' . wpjobportal::$_db->prefix . 'wj_portal_resume AS resume ON resume.uid = a.id ';
        }
        if ($wpjobportal_searchrole){
            if (is_numeric($wpjobportal_searchrole))
                $wpjobportal_inquery .= esc_sql($clause) . "a.roleid = " . esc_sql($wpjobportal_searchrole);
        }
        //Pagination
        $query = 'SELECT a.id '
                . ' FROM `' . wpjobportal::$_db->prefix . 'wj_portal_users` AS a'
                . ' LEFT JOIN `' . $this->jsGetPrefix() . 'users` AS u ON u.id = a.uid ';
        $query .= $wpjobportal_company_join;
        $query .= $wpjobportal_resume_join;
        $query .= $wpjobportal_inquery;
        $query .= " GROUP BY a.id ";
        $wpjobportal_total = wpjobportaldb::get_results($query);
        $wpjobportal_total = count($wpjobportal_total);
        wpjobportal::$_data['total'] = $wpjobportal_total;
        wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($wpjobportal_total);

        //Data
        $query = 'SELECT a.*,u.user_login,u.id AS wpuid'
                . ' FROM ' . wpjobportal::$_db->prefix . 'wj_portal_users AS a'
                . ' LEFT JOIN ' . $this->jsGetPrefix() . 'users AS u ON u.id = a.uid ';
        $query .= $wpjobportal_company_join;
        $query .= $wpjobportal_resume_join;
        $query .= $wpjobportal_inquery;
        $query .= ' GROUP BY a.id LIMIT ' . WPJOBPORTALpagination::$_offset . ',' . WPJOBPORTALpagination::$_limit;

        wpjobportal::$_data[0] = wpjobportaldb::get_results($query);
        return;
    }


    function getUserRoleBasedInfo() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_user_nonce') ) {
            die( 'Security check Failed' );
        }
        $wpjobportal_uid = WPJOBPORTALrequest::getVar('uid');
        if(!is_numeric($wpjobportal_uid)){
            return false;
        }
        $wpjobportal_roleid = WPJOBPORTALrequest::getVar('roleid');
        if(!is_numeric($wpjobportal_roleid)){
            return false;
        }


        //Data
        $wpjobportal_data = '';
        if($wpjobportal_roleid == 1){
            $query = 'SELECT company.name AS display_value
                    FROM ' . wpjobportal::$_db->prefix . 'wj_portal_companies AS company WHERE company.uid = '.esc_sql($wpjobportal_uid);
            $wpjobportal_label = __('Company', 'wp-job-portal');
            $wpjobportal_data = wpjobportaldb::get_var($query);
        }elseif($wpjobportal_roleid == 2){
            $query = 'SELECT  resume.application_title AS application_title, CONCAT(resume.first_name," ",resume.last_name) AS name
                    FROM ' . wpjobportal::$_db->prefix . 'wj_portal_resume AS resume WHERE resume.uid = '.esc_sql($wpjobportal_uid);
            $wpjobportal_label = __('Resume', 'wp-job-portal');
            $wpjobportal_data_row = wpjobportaldb::get_row($query);
            if(!empty($wpjobportal_data_row)){ // to handle the case of user application title field is not published.
                if(isset($wpjobportal_data_row->application_title) && $wpjobportal_data_row->application_title != ''){
                    $wpjobportal_data = $wpjobportal_data_row->application_title;
                }else{
                    $wpjobportal_label = __('Name', 'wp-job-portal');
                    $wpjobportal_data = $wpjobportal_data_row->name;
                }
            }
        }

        if($wpjobportal_data !=''){
            $return_html = '
                            <div class="wpjobportal-user-data-text">
                                <span class="wpjobportal-user-data-title">
                                    '.esc_html($wpjobportal_label) . ':
                                </span>
                                <span class="wpjobportal-user-data-value">
                                    '.esc_html($wpjobportal_data).'
                                </span>
                            </div>';
            return wp_json_encode($return_html);
        }else{
            return false;
        }

    }

    function enforceDeleteUser($wpjobportal_uid) {
        if (!is_numeric($wpjobportal_uid))
            return false;

        $wpjobportal_roleid = $this->getUserRoleByUid($wpjobportal_uid);

        if (!is_numeric($wpjobportal_roleid)) {
            // this user has no role
            // what to do then ?
        } else {

            $wp_uid = $this->getWPuidByOuruid($wpjobportal_uid);

            if ($this->enforceDeleteOurUser($wpjobportal_uid, $wpjobportal_roleid)) {

                do_action('wpjobportal_load_wp_users');

                if (wp_delete_user($wp_uid))
                    return WPJOBPORTAL_DELETED;
                else {
                    return WPJOBPORTAL_DELETE_ERROR;
                }
            } else {
                return WPJOBPORTAL_DELETE_ERROR;
            }
        }
    }

   function enforceDeleteOurUser($wpjobportal_uid, $wpjobportal_roleid) {
        if (!is_numeric($wpjobportal_uid))
            return false;
        $query = '';

        if ($wpjobportal_roleid == 1) { // employer
            $query = "DELETE u, job,comp";
            if(in_array('departments', wpjobportal::$_active_addons)){
                $query .= ",dep ";
            }
            if(in_array('folder', wpjobportal::$_active_addons)){
                $query .= ",folder,folder_resumes ";
            }
            if(in_array('message', wpjobportal::$_active_addons)){
                $query .= ",message ";
            }

            if(in_array('credits', wpjobportal::$_active_addons)){
                $query .= ",upackage,tlog,subs,invoice ";
            }

            $query .= " ,jobcity,compcity
                        FROM
                        `" . wpjobportal::$_db->prefix . "wj_portal_users` AS u
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job ON job.uid = u.id
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobcities` AS jobcity ON jobcity.jobid = job.id
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS comp ON comp.uid = u.id";
                if(in_array('departments', wpjobportal::$_active_addons)){
                    $query .= " LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_departments` AS dep ON dep.companyid = comp.id";
                }
                if(in_array('folder', wpjobportal::$_active_addons)){
                    $query .= " LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_folders` AS folder ON folder.uid = u.id";
                    $query .= " LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_folderresumes` AS folder_resumes ON folder_resumes.uid = u.id";
                }
                if(in_array('message', wpjobportal::$_active_addons)){
                    $query .= " LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_messages` AS message ON message.employerid = u.id";
                }
                if(in_array('credits', wpjobportal::$_active_addons)){
                    $query .= " LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_userpackages` AS upackage ON upackage.uid = u.id";
                    $query .= " LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_transactionlog` AS tlog ON tlog.uid = u.id";
                    $query .= " LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_subscriptions` AS subs ON subs.uid = u.id";
                    $query .= " LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_invoices` AS invoice ON invoice.uid = u.id";
                }

                $query .= " LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companycities` AS compcity ON compcity.companyid = comp.id
                WHERE u.id = " . esc_sql($wpjobportal_uid);
        }

        if ($wpjobportal_roleid == 2) { // seeker
                $query = "DELETE u,resume , ra, re,rf,ri,rl,ja ";
                    if(in_array('resumesearch', wpjobportal::$_active_addons)){
                        $query .= " ,rs ";
                    }
                    if(in_array('message', wpjobportal::$_active_addons)){
                        $query .= " ,message ";
                    }
                    if(in_array('credits', wpjobportal::$_active_addons)){
                        $query .= ",upackage,tlog,subs,invoice ";
                    }
                    if(in_array('jobalert', wpjobportal::$_active_addons)){
                        $query .= " ,jobalert,acity ";
                    }
                $query .= "
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` AS u
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume ON resume.uid = u.id
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_resumeaddresses` AS ra ON ra.resumeid = resume.id
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_resumeemployers` AS re ON re.resumeid = resume.id
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_resumefiles` AS rf ON rf.resumeid = resume.id
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_resumeinstitutes` AS ri ON ri.resumeid = resume.id
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_resumelanguages` AS rl ON rl.resumeid = resume.id
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` AS ja ON ja.uid = u.id ";
                    if(in_array('resumesearch', wpjobportal::$_active_addons)){
                        $query .= " LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_resumesearches` AS rs ON rs.uid = u.id";
                    }
                    if(in_array('message', wpjobportal::$_active_addons)){
                        $query .= " LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_messages` AS message ON message.jobseekerid = u.id ";
                    }
                    if(in_array('credits', wpjobportal::$_active_addons)){
                        $query .= " LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_userpackages` AS upackage ON upackage.uid = u.id";
                        $query .= " LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_transactionlog` AS tlog ON tlog.uid = u.id";
                        $query .= " LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_subscriptions` AS subs ON subs.uid = u.id";
                        $query .= " LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_invoices` AS invoice ON invoice.uid = u.id";
                    }
                    if(in_array('jobalert', wpjobportal::$_active_addons)){
                        $query .= " LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobalertsetting` AS jobalert ON jobalert.uid = u.id ";
                        $query .= " LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobalertcities` AS acity ON acity.alertid = jobalert.id ";
                    }
                    $query .= " WHERE u.id = " . esc_sql($wpjobportal_uid);
        }

        if($query != ''){
            if (wpjobportaldb::query($query)) {
                return true;
            } else {
                return false;
            }
        }
    }

    function getUserRoleByUid($wpjobportal_uid) {
        if (!is_numeric($wpjobportal_uid))
            return false;
        $query = "SELECT roleid FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` WHERE id = " . esc_sql($wpjobportal_uid);
        $wpjobportal_result = wpjobportaldb::get_var($query);
        return $wpjobportal_result;
    }

    function getUserRoleByWPUid($wpuid) {
        if (!is_numeric($wpuid))
            return false;
        $query = "SELECT roleid FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` WHERE uid = " . esc_sql($wpuid);
        $wpjobportal_result = wpjobportaldb::get_var($query);
        return $wpjobportal_result;
    }

     function deleteUserPhoto() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'delete-user-photo') ) {
            die( 'Security check Failed' );
        }        $cid = WPJOBPORTALrequest::getVar('userid');
        if(!is_numeric($cid)){
            return false;
        }
        WPJOBPORTALincluder::getObjectClass('uploads')->removeUserPhoto($cid);
        return true;
    }

    function getUserIDByWPUid($wpuid) {
        if (!is_numeric($wpuid))
            return false;
        $query = "SELECT id FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` WHERE uid = " . esc_sql($wpuid);
        $wpjobportal_result = wpjobportaldb::get_var($query);
        return $wpjobportal_result;
    }

    function getWPuidByOuruid($our_uid) {
        if (!is_numeric($our_uid))
            return false;
        $query = "SELECT uid AS wpuid FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` WHERE id = " . esc_sql($our_uid);
        $wpjobportal_result = wpjobportaldb::get_var($query);
        return $wpjobportal_result;
    }

    function changeUserStatus($wpjobportal_userid){
        if(!is_numeric($wpjobportal_userid)) return false;
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('users');
        if($wpjobportal_row->load($wpjobportal_userid)){
            $wpjobportal_row->columns['status'] = 1 - $wpjobportal_row->status;
            if($wpjobportal_row->store()){
                if($wpjobportal_row->columns['status'] == 1){
                    return WPJOBPORTAL_ENABLED;
                }else{
                    return WPJOBPORTAL_DISABLED;
                }
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    function assignUserRole($wpjobportal_data){

        if(empty($wpjobportal_data))
            return false;
        if(! is_numeric($wpjobportal_data['uid']))
            return false;
        if(! is_numeric($wpjobportal_data['roleid']))
            return false;

        $wpjobportal_arr = array();
        $wpjobportal_arr['uid'] = $wpjobportal_data['uid'];
        $wpjobportal_arr['roleid'] = $wpjobportal_data['roleid'];
        $wpjobportal_arr['first_name'] = $wpjobportal_data['payer_firstname'];
        $wpjobportal_arr['emailaddress'] = $wpjobportal_data['payer_emailadress'];
        $wpjobportal_arr['status'] = 1;
        $wpjobportal_arr['created'] = gmdate("Y-m-d H:i:s");
        $wpjobportal_arr = wpjobportal::wpjobportal_sanitizeData($wpjobportal_arr);
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('users');
        if (!$wpjobportal_row->bind($wpjobportal_arr)) {
            return WPJOBPORTAL_SAVE_ERROR;
        }
        if (!$wpjobportal_row->check()) {
            return WPJOBPORTAL_SAVE_ERROR;
        }
        if (!$wpjobportal_row->store()) {
            return WPJOBPORTAL_SAVE_ERROR;
        }
        return WPJOBPORTAL_SAVED;
    }

    function deleteUser($wpjobportal_uid) {
        if (!is_numeric($wpjobportal_uid))
            return false;
        $wpjobportal_roleid = $this->getUserRoleByUid($wpjobportal_uid);
        if (!is_numeric($wpjobportal_roleid)) {
            // this user has no role
            // what to do then ?
        } else {
            if ($this->userCanDelete($wpjobportal_uid, $wpjobportal_roleid)) {
                $wp_uid = $this->getWPuidByOuruid($wpjobportal_uid);

                if ($this->deleteOurUser($wpjobportal_uid)) {
                    do_action('wpjobportal_load_wp_users');
                    if (wp_delete_user($wp_uid)) {
                        return WPJOBPORTAL_DELETED;
                    } else {
                        return WPJOBPORTAL_DELETE_ERROR;
                    }
                } else {
                    return WPJOBPORTAL_DELETE_ERROR;
                }
            } else {
                return WPJOBPORTAL_IN_USE;
            }
        }
    }

    function userCanDelete($wpjobportal_uid, $wpjobportal_roleid) {
        if (!is_numeric($wpjobportal_uid))
            return false;
        if ($wpjobportal_roleid == 1) { // employer
            $query = "SELECT
                    (SELECT COUNT(job.id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job WHERE job.uid = ".esc_sql($wpjobportal_uid)." )
                +   (SELECT COUNT(comp.id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS comp WHERE comp.uid = ".esc_sql($wpjobportal_uid)." )";
                if(in_array('departments', wpjobportal::$_active_addons)){
                    $query .= " +   (SELECT COUNT(dep.id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_departments` AS dep WHERE dep.uid = ".esc_sql($wpjobportal_uid)." )";
                }
                $query .= " AS total
            ";
        }

        if ($wpjobportal_roleid == 2) { // seeker
            $query = "SELECT
                    (SELECT COUNT(resume.id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume WHERE resume.uid = ".esc_sql($wpjobportal_uid)." )

                AS total
            ";
        }

        $wpjobportal_result = wpjobportaldb::get_var($query);
        if ($wpjobportal_result > 0)
            return false;
        else
            return true;
    }

    function deleteOurUser($wpjobportal_uid) {
        if (!is_numeric($wpjobportal_uid))
            return false;
        $query = "DELETE FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` WHERE id = " . esc_sql($wpjobportal_uid);
        if (wpjobportaldb::query($query)) {
            return true;
        } else {
            return false;
        }
    }

    // function getUserStats() {
    //     //Filters
    //     $wpjobportal_searchname = WPJOBPORTALrequest::getVar('searchname');
    //     $wpjobportal_searchusername = WPJOBPORTALrequest::getVar('searchusername');
    //     $wpjobportal_formsearch = WPJOBPORTALrequest::getVar('WPJOBPORTAL_form_search', 'post');
    //     if ($wpjobportal_formsearch == 'WPJOBPORTAL_SEARCH') {
    //         $_SESSION['WPJOBPORTAL_SEARCH']['searchname'] = $wpjobportal_searchname;
    //         $_SESSION['WPJOBPORTAL_SEARCH']['searchusername'] = $wpjobportal_searchusername;
    //     }
    //     if (WPJOBPORTALrequest::getVar('pagenum', 'get', null) != null) {
    //         $wpjobportal_searchname = (isset($_SESSION['WPJOBPORTAL_SEARCH']['searchname']) && $_SESSION['WPJOBPORTAL_SEARCH']['searchname'] != '') ? sanitize_key($_SESSION['WPJOBPORTAL_SEARCH']['searchname']) : null;
    //         $wpjobportal_searchusername = (isset($_SESSION['WPJOBPORTAL_SEARCH']['searchusername']) && $_SESSION['WPJOBPORTAL_SEARCH']['searchusername'] != '') ? sanitize_key($_SESSION['WPJOBPORTAL_SEARCH']['searchusername']) : null;
    //     } elseif ($wpjobportal_formsearch !== 'WPJOBPORTAL_SEARCH') {
    //         unset($_SESSION['WPJOBPORTAL_SEARCH']);
    //     }
    //     wpjobportal::$_data['filter']['searchname'] = $wpjobportal_searchname;
    //     wpjobportal::$_data['filter']['searchusername'] = $wpjobportal_searchusername;

    //     $clause = " WHERE ";
    //     $wpjobportal_inquery = "";
    //     if ($wpjobportal_searchname) {
    //         $wpjobportal_inquery .= esc_sql($clause) . " (LOWER(a.first_name) LIKE '%" . esc_sql($wpjobportal_searchname) . "%' OR LOWER(a.last_name) LIKE '%" . esc_sql($wpjobportal_searchname) . "%')";
    //         $clause = 'AND';
    //     }
    //     if ($wpjobportal_searchusername)
    //         $wpjobportal_inquery .= esc_sql($clause) . " LOWER(a.user_login) LIKE '%" . esc_sql($wpjobportal_searchusername) . "%'";

    //     //Pagination
    //     $query = "SELECT COUNT(a.ID) FROM " . $this->jsGetPrefix() . "users AS a";
    //     $query.=$wpjobportal_inquery;

    //     $wpjobportal_total = wpjobportaldb::get_var($query);
    //     wpjobportal::$_data['total'] = $wpjobportal_total;
    //     wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($wpjobportal_total);

    //     //Data
    //     $query = "SELECT a.id AS id, CONCAT(a.first_name,' ',a.last_name) AS name, u.user_login AS username
    //             ,(SELECT name FROM " . wpjobportal::$_db->prefix . "wj_portal_companies WHERE uid=a.id limit 1 ) AS companyname
    //             ,(SELECT CONCAT(first_name,' ',last_name) FROM " . wpjobportal::$_db->prefix . "wj_portal_resume WHERE uid=a.id limit 1 ) AS resumename
    //             ,(SELECT count(id) FROM " . wpjobportal::$_db->prefix . "wj_portal_companies WHERE uid=a.id ) AS companies
    //             ,(SELECT count(id) FROM " . wpjobportal::$_db->prefix . "wj_portal_jobs WHERE uid=a.id ) AS jobs
    //             ,(SELECT count(id) FROM " . wpjobportal::$_db->prefix . "wj_portal_resume WHERE uid=a.id ) AS resumes
    //             FROM " . wpjobportal::$_db->prefix . "wj_portal_users AS a
    //             LEFT JOIN " . $this->jsGetPrefix() . "users AS u ON u.id = a.uid";
    //     $query.=$wpjobportal_inquery;
    //     $query .= ' GROUP BY a.id LIMIT ' . WPJOBPORTALpagination::$_offset . ',' . WPJOBPORTALpagination::$_limit;
    //     wpjobportal::$_data[0] = wpjobportaldb::get_results($query);

    //     return;
    // }

    // function getUserStatsCompanies($wpjobportal_companyuid) {
    //     if (is_numeric($wpjobportal_companyuid) == false)
    //         return false;

    //     //Pagination
    //     $query = "SELECT COUNT(company.id)
    //               FROM " . wpjobportal::$_db->prefix . "wj_portal_companies AS company
	   //            WHERE company.uid = " . esc_sql($wpjobportal_companyuid);
    //     $wpjobportal_total = wpjobportaldb::get_var($query);
    //     wpjobportal::$_data['total'] = $wpjobportal_total;
    //     wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($wpjobportal_total);

    //     //Data
    //     $query = "SELECT company.*,cat.cat_title"
    //             . " FROM " . wpjobportal::$_db->prefix . "wj_portal_companies AS company"
    //             . " LEFT JOIN " . wpjobportal::$_db->prefix . "wj_portal_categories AS cat ON cat.id=company.category"
    //             . " LEFT JOIN " . wpjobportal::$_db->prefix . "wj_portal_cities AS city ON city.id=company.city"
    //             . " LEFT JOIN " . wpjobportal::$_db->prefix . "wj_portal_countries AS country ON country.id=city.countryid
		  //         WHERE company.uid = " . esc_sql($wpjobportal_companyuid);
    //     $query .= " ORDER BY company.name LIMIT " . WPJOBPORTALpagination::$_offset . "," . WPJOBPORTALpagination::$_limit;

    //     wpjobportal::$_data[0] = wpjobportaldb::get_results($query);

    //     return;
    // }

    function getWPRoleNameById($wpjobportal_id) {
        $wpjobportal_rolename = "";
        if ($wpjobportal_id) {
            $wpjobportal_user = new WP_User($wpjobportal_id);
            $wpjobportal_rolename = $wpjobportal_user->roles[0];
        }
        return $wpjobportal_rolename;
    }

    // function getUserStatsJobs($wpjobportal_jobuid) {
    //     if (is_numeric($wpjobportal_jobuid) == false)
    //         return false;

    //     //Pagination
    //     $query = "SELECT COUNT(job.id)
    //             FROM " . wpjobportal::$_db->prefix . "wj_portal_jobs AS job WHERE job.uid = " . esc_sql($wpjobportal_jobuid);

    //     $wpjobportal_total = wpjobportaldb::get_var($query);
    //     wpjobportal::$_data['total'] = $wpjobportal_total;
    //     wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($wpjobportal_total);

    //     //Data
    //     $query = "SELECT job.*,company.name AS companyname,cat.cat_title,jobtype.title AS jobtypetitle"
    //             . " FROM " . wpjobportal::$_db->prefix . "wj_portal_jobs AS job"
    //             . " LEFT JOIN " . wpjobportal::$_db->prefix . "wj_portal_companies AS company ON company.id=job.companyid"
    //             . " LEFT JOIN " . wpjobportal::$_db->prefix . "wj_portal_categories AS cat ON cat.id=job.jobcategory"
    //             . " LEFT JOIN " . wpjobportal::$_db->prefix . "wj_portal_jobtypes AS jobtype ON jobtype.id=job.jobtype
		  //  WHERE job.uid = " . esc_sql($wpjobportal_jobuid);
    //     $query .= " ORDER BY job.title LIMIT " . WPJOBPORTALpagination::$_offset . "," . WPJOBPORTALpagination::$_limit;

    //     wpjobportal::$_data[0] = wpjobportaldb::get_results($query);
    //     return;
    // }

    function getuserlistajax() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'get-user-list-ajax') ) {
            die( 'Security check Failed' );
        }
        $wpjobportal_userlimit = WPJOBPORTALrequest::getVar('userlimit', null, 0);
        $wpjobportal_maxrecorded = 3;
        //Filters
        $uname = WPJOBPORTALrequest::getVar('uname');
        $wpjobportal_name = WPJOBPORTALrequest::getVar('name');
        $wpjobportal_email = WPJOBPORTALrequest::getVar('email');
        $listfor = WPJOBPORTALrequest::getVar('listfor');

        wpjobportal::$_data['filter']['name'] = $wpjobportal_name;
        wpjobportal::$_data['filter']['uname'] = $uname;
        wpjobportal::$_data['filter']['email'] = $wpjobportal_email;

        $wpjobportal_inquery = "";

        if ($wpjobportal_name != null) {
            $wpjobportal_inquery .= " AND ( user.first_name LIKE '%" . esc_sql($wpjobportal_name) . "%' OR user.last_name LIKE '%" . esc_sql($wpjobportal_name) . "%' ) ";
        }
        if ($uname != null) {
            $wpjobportal_inquery .= " AND  u.user_login LIKE  '%" . esc_sql($uname) . "%' ";
        }
        if ($wpjobportal_email != null)
            $wpjobportal_inquery .= " AND user.emailaddress LIKE '%" . esc_sql($wpjobportal_email) . "%' ";

        if ($listfor == 1) {
            $wpjobportal_status = "WHERE 1 = 1 "; //to get all users
        } else {
            $wpjobportal_status = "WHERE user.roleid =1 ";
        }


        $query = "SELECT COUNT(user.id)
                FROM " . wpjobportal::$_db->prefix . "wj_portal_users AS user
                LEFT JOIN " . $this->jsGetPrefix() . "users AS u ON u.id = user.uid
                $wpjobportal_status ";
        $query .= $wpjobportal_inquery;
        $wpjobportal_total = wpjobportaldb::get_var($query);
        $limit = $wpjobportal_userlimit * $wpjobportal_maxrecorded;
        if ($limit >= $wpjobportal_total) {
            $limit = 0;
        }

        //Data
        $query = "SELECT user.id AS userid,user.first_name,user.last_name,user.emailaddress
                    ,u.user_login
                FROM " . wpjobportal::$_db->prefix . "wj_portal_users AS user
                LEFT JOIN " . $this->jsGetPrefix() . "users AS u ON u.id = user.uid
                $wpjobportal_status ";
        $query .= $wpjobportal_inquery;
        $query .= " ORDER BY user.id LIMIT $limit, $wpjobportal_maxrecorded";
        $wpjobportal_users = wpjobportaldb::get_results($query);

        $wpjobportal_html = $this->makeUserList($wpjobportal_users, $wpjobportal_total, $wpjobportal_maxrecorded, $wpjobportal_userlimit);
        return $wpjobportal_html;
    }

    function getAllRoleLessUsersAjax() {

        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'get-all-role-less-users-ajax') ) {
            die( 'Security check Failed' );
        }
        $wpjobportal_userlimit = WPJOBPORTALrequest::getVar('userlimit', null, 0);
        $wpjobportal_maxrecorded = 3;
        //Filters

        $wpjobportal_name = WPJOBPORTALrequest::getVar('name');
        $uname = WPJOBPORTALrequest::getVar('uname');
        $wpjobportal_email = WPJOBPORTALrequest::getVar('email');

        wpjobportal::$_data['filter']['name'] = $wpjobportal_name;
        wpjobportal::$_data['filter']['uname'] = $uname;
        wpjobportal::$_data['filter']['email'] = $wpjobportal_email;

        $wpjobportal_inquery = "";

        if ($uname != null) {
            $wpjobportal_inquery .= " AND ( user.user_login LIKE '%" . esc_sql($uname) . "%' ) ";
        }

        if ($wpjobportal_name != null) {
            $wpjobportal_inquery .= " AND ( user.display_name LIKE '%" . esc_sql($wpjobportal_name) . "%' ) ";
        }

        if ($wpjobportal_email != null) {
            $wpjobportal_inquery .= " AND ( user.user_email LIKE '%" . esc_sql($wpjobportal_email) . "%' ) ";
        }

        $query = "SELECT COUNT( user.ID ) AS total
                    FROM `" . $this->jsGetPrefix() . "users` AS user
                    WHERE NOT EXISTS( SELECT jsuser.id FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` AS jsuser WHERE user.ID = jsuser.uid) AND
                    NOT EXISTS(SELECT umeta_id FROM `".wpjobportal::$_db->prefix."usermeta` WHERE meta_value LIKE '%administrator%' AND user_id = user.id)";
        $query .= $wpjobportal_inquery;
        $query .= " GROUP BY user.ID";
        $wpjobportal_total = wpjobportaldb::get_var($query);

        $limit = $wpjobportal_userlimit * $wpjobportal_maxrecorded;
        if ($limit >= $wpjobportal_total) {
            $limit = 0;
        }

        // Data
        $query = "SELECT DISTINCT user.ID AS userid, user.user_login , user.user_email AS emailaddress, user.display_name AS name
                    FROM `" . $this->jsGetPrefix() . "users` AS user
                    WHERE NOT EXISTS( SELECT jsuser.id FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` AS jsuser WHERE user.ID = jsuser.uid) AND
                    NOT EXISTS(SELECT umeta_id FROM `".wpjobportal::$_db->prefix."usermeta` WHERE meta_value LIKE '%administrator%' AND user_id = user.ID)";

        $query .= $wpjobportal_inquery;
        $query .= " ORDER BY user.ID ASC LIMIT $limit, $wpjobportal_maxrecorded";
        $wpjobportal_users = wpjobportaldb::get_results($query);

        $wpjobportal_html = $this->makeUserList($wpjobportal_users, $wpjobportal_total, $wpjobportal_maxrecorded, $wpjobportal_userlimit , true);
        return $wpjobportal_html;
    }

    function makeUserList($wpjobportal_users, $wpjobportal_total, $wpjobportal_maxrecorded, $wpjobportal_userlimit , $assignrole = false) {
        $wpjobportal_html = '';
        if (!empty($wpjobportal_users)) {
            if (is_array($wpjobportal_users)) {

                $wpjobportal_html .= '
                    <div id="records">';

                $wpjobportal_html .='
                <div id="user-list-header" class="popup-table">
                    <div class="user-list-header-col user-id">' . esc_html(__('ID', 'wp-job-portal')) . '</div>
                    <div class="user-list-header-col user-name">' . esc_html(__('Name', 'wp-job-portal')) . '</div>
                    <div class="user-list-header-col user-name-n">' . esc_html(__('User Name', 'wp-job-portal')) . '</div>
                    <div class="user-list-header-col user-email">' . esc_html(__('Email Address', 'wp-job-portal')) . '</div>

                </div>
                <div class="user-records-wrapper" >';

                    foreach ($wpjobportal_users AS $wpjobportal_user) {
                        if($assignrole){
                            $wpjobportal_username = $wpjobportal_user->name;
                        }else{
                            $wpjobportal_username = $wpjobportal_user->first_name . ' ' . $wpjobportal_user->last_name;
                        }
                        $wpjobportal_html .='
                            <div class="user-records-row" >
                                <div class="user-list-body-col user-id">
                                    ' . $wpjobportal_user->userid . '
                                </div>
                                <div class="user-list-body-col user-name">
                                    <a href="#" class="userpopup-link js-userpopup-link" data-id=' . $wpjobportal_user->userid . ' data-name="' . $wpjobportal_username . '" data-email="' . $wpjobportal_user->emailaddress . '" >' . $wpjobportal_username . '</a>
                                </div>
                                <div class="user-list-body-col user-name-n">
                                    ' . $wpjobportal_user->user_login . '
                                </div>
                                <div class="user-list-body-col user-email">
                                    ' . $wpjobportal_user->emailaddress . '
                                </div>
                            </div>';
                    }
                $wpjobportal_html .='</div>';
            }
            $wpjobportal_num_of_pages = ceil($wpjobportal_total / $wpjobportal_maxrecorded);
            $wpjobportal_num_of_pages = ($wpjobportal_num_of_pages > 0) ? ceil($wpjobportal_num_of_pages) : floor($wpjobportal_num_of_pages);
            if ($wpjobportal_num_of_pages > 0) {
                $page_html = '';
                $wpjobportal_prev = $wpjobportal_userlimit;
                if ($wpjobportal_prev > 0) {
                    $page_html .= '<a class="wpjobportaladmin-userlink" href="#" onclick="updateuserlist(' . ($wpjobportal_prev - 1) . ');">' . esc_html(__('Previous', 'wp-job-portal')) . '</a>';
                }
                for ($wpjobportal_i = 0; $wpjobportal_i < $wpjobportal_num_of_pages; $wpjobportal_i++) {
                    if ($wpjobportal_i == $wpjobportal_userlimit)
                        $page_html .= '<span class="wpjobportaladmin-userlink selected" >' . ($wpjobportal_i + 1) . '</span>';
                    else
                        $page_html .= '<a class="wpjobportaladmin-userlink" href="#" onclick="updateuserlist(' . $wpjobportal_i . ');">' . ($wpjobportal_i + 1) . '</a>';
                }
                $wpjobportal_next = $wpjobportal_userlimit + 1;
                if ($wpjobportal_next < $wpjobportal_num_of_pages) {
                    $page_html .= '<a class="wpjobportaladmin-userlink" href="#" onclick="updateuserlist(' . $wpjobportal_next . ');">' . esc_html(__('Next', 'wp-job-portal')) . '</a>';
                }
                if ($page_html != '') {
                    $wpjobportal_html .= '<div class="wpjobportaladmin-userpages">' . $page_html . '</div>';
                }
            }
        } else {
            $wpjobportal_html = WPJOBPORTALlayout::getAdminPopupNoRecordFound();
        }
        $wpjobportal_html .= '</div>';
        return $wpjobportal_html;
    }

    function checkUserBySocialID($wpjobportal_socialid) {
        $query = "SELECT id FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` WHERE socialid = '" . esc_sql($wpjobportal_socialid) . "'";
        $wpjobportal_result = wpjobportal::$_db->get_var($query);
        return $wpjobportal_result;
    }
    
    function getAppliedCountProfileID($wpjobportal_socialprofileid,$wpjobportal_jobid) {
        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` WHERE socialprofileid = '" . esc_sql($wpjobportal_socialprofileid) . "' AND jobid ='".esc_sql($wpjobportal_jobid)."'";
        $wpjobportal_result = wpjobportal::$_db->get_var($query);
        return $wpjobportal_result;
    }

    function getSocialProfileID($wpjobportal_socialid) {
        $query = "SELECT id FROM `" . wpjobportal::$_db->prefix . "wj_portal_socialprofiles` WHERE socialid = '" . esc_sql($wpjobportal_socialid) . "'";
        $wpjobportal_result = wpjobportal::$_db->get_var($query);
        return $wpjobportal_result;
    }

    function getUserData($wpjobportal_id){
        if (!is_numeric($wpjobportal_id))
            return false;
        $query = "SELECT * FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` WHERE id = " . esc_sql($wpjobportal_id) ;
        wpjobportal::$_data[0] = wpjobportal::$_db->get_row($query);
        if(!empty(wpjobportal::$_data[0]) && isset(wpjobportal::$_data[0]->roleid)){// roleid not set error in log
            //employer
            if(wpjobportal::$_data[0]->roleid == 1){
                $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` WHERE uid=".esc_sql($wpjobportal_id);
                wpjobportal::$_data['jobs'] = wpjobportal::$_db->get_var($query);

                $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` WHERE uid=".esc_sql($wpjobportal_id);
                wpjobportal::$_data['companies'] = wpjobportal::$_db->get_var($query);
                if(in_array('departments', wpjobportal::$_active_addons)){
                    $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_departments` WHERE uid=".esc_sql($wpjobportal_id);
                    wpjobportal::$_data['department'] = wpjobportal::$_db->get_var($query);
                }

                $query = "SELECT COUNT(jobapply.id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` as jobapply
                JOIN ".wpjobportal::$_db->prefix."wj_portal_jobs AS job ON job.id = jobapply.jobid  WHERE job.uid=".esc_sql($wpjobportal_id);
                wpjobportal::$_data['jobapply'] = wpjobportal::$_db->get_var($query);
            }elseif(wpjobportal::$_data[0]->roleid == 2){
                //jobseeker
                $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` WHERE uid=".esc_sql($wpjobportal_id);
                wpjobportal::$wpjobportal_data['resume'] = wpjobportal::$_db->get_var($query);

                $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply`  WHERE uid=".esc_sql($wpjobportal_id);
                wpjobportal::$_data['jobapply'] = wpjobportal::$_db->get_var($query);
            }
        }
        return ;
    }

    function getChangeRolebyId($c_id) {
        if (is_numeric($c_id) == false)
            return false;
        $query = "SELECT a.*,a.created AS dated,u.user_login,u.id AS wpuid"
                . " FROM " . wpjobportal::$_db->prefix . "wj_portal_users AS a"
                . " LEFT JOIN " . $this->jsGetPrefix() . "users AS u ON u.id = a.uid"
                . " WHERE a.id = " . esc_sql($c_id);
        wpjobportal::$_data[0] = wpjobportaldb::get_row($query);
        return;
    }

    function storeUserRole($wpjobportal_data) {
        if (empty($wpjobportal_data))
            return false;
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('users');
        $wpjobportal_data = wpjobportal::wpjobportal_sanitizeData($wpjobportal_data);
        if (!$wpjobportal_row->bind($wpjobportal_data)) {
            return WPJOBPORTAL_SAVE_ERROR;
        }
        if (!$wpjobportal_row->check()) {
            return WPJOBPORTAL_SAVE_ERROR;
        }
        if (!$wpjobportal_row->store()) {
            return WPJOBPORTAL_SAVE_ERROR;
        }
        return WPJOBPORTAL_SAVED;
    }

    function getUserIdByCompanyid(){
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'get-user-id-by-company-id') ) {
            die( 'Security check Failed' );
        }
        $wpjobportal_companyid = WPJOBPORTALrequest::getVar('companyid');
        if(!is_numeric($wpjobportal_companyid)) return false;
        $query = "SELECT uid FROM `".wpjobportal::$_db->prefix."wj_portal_companies` WHERE id = ".esc_sql($wpjobportal_companyid);
        $wpjobportal_companyid = wpjobportal::$_db->get_var($query);
        return $wpjobportal_companyid;
    }

    function getUserDetailsById($u_id){
        if (is_numeric($u_id) == false)
            return false;
        $query = "SELECT user.emailaddress AS email,CONCAT(first_name,' ',last_name) AS name,user.roleid "
                . " FROM " . wpjobportal::$_db->prefix . "wj_portal_users AS user"
                . " WHERE user.id = " . esc_sql($u_id);
        return wpjobportaldb::get_row($query);
    }

    function getUserForForm($u_id){
        if(is_numeric($u_id) == false){
            return false;
        }
        $query = "SELECT CONCAT(first_name,' ',last_name) AS name,user.* FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` AS user
        WHERE user.id = " . esc_sql($u_id);
        $res = wpjobportaldb::get_row($query);
        if(!$res){
            return false;
        }
        wpjobportal::$_data[0] = $res;
        return true;
    }

    function storeUser($wpjobportal_data){
        if(empty($wpjobportal_data)){
            return false;
        }
        if(!$wpjobportal_data['id']){
            return false;
        }
        $wpjobportal_data['first_name'] = wpjobportal::wpjobportal_sanitizeData(WPJOBPORTALrequest::getVar('wpjobportal_user_first'));
        $wpjobportal_data['last_name'] = wpjobportal::wpjobportal_sanitizeData(WPJOBPORTALrequest::getVar('wpjobportal_user_last'));
        $wpjobportal_data = wpjobportal::wpjobportal_sanitizeData($wpjobportal_data);
        $wpjobportal_data = wpjobportal::$_common->stripslashesFull($wpjobportal_data);// remove slashes with quotes.
        $wpjobportal_data['description'] = wpautop(wptexturize(wptexturize(wpjobportalphplib::wpJP_stripslashes(WPJOBPORTALrequest::getVar('description','post','','',1)))));
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('users');
        if(!$wpjobportal_row->bind($wpjobportal_data)) {
            return WPJOBPORTAL_SAVE_ERROR;
        }
        if(!$wpjobportal_row->check()) {
            return WPJOBPORTAL_SAVE_ERROR;
        }
        if(!$wpjobportal_row->store()) {
            return WPJOBPORTAL_SAVE_ERROR;
        }

        $this->storeUserPhoto(WPJOBPORTALincluder::getObjectClass('user')->getWPuid());

        WPJOBPORTALincluder::getObjectClass('customfields')->storeCustomFields(4,$wpjobportal_row->id,$wpjobportal_data);

        if(!$wpjobportal_data['id']){
            WPJOBPORTALincluder::getJSModel('emailtemplate')->sendMail(2,1,$wpjobportal_row->id);
        }

        if(isset($wpjobportal_data['oldStatus']) && $wpjobportal_data['oldStatus']!=$wpjobportal_data['status']){
            WPJOBPORTALincluder::getJSModel('emailtemplate')->sendMail(2,2,$wpjobportal_row->id);
        }

        return WPJOBPORTAL_SAVED;
    }

    function storeUserPhoto($wpjobportal_id){
        if(!is_numeric($wpjobportal_id)){
            return false;
        }
        if(!empty($_FILES['photo']) && $_FILES['photo']['size'] > 0) { // logo
            $query = "SELECT photo FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` WHERE uid = ".esc_sql($wpjobportal_id);
            $wpjobportal_photo = wpjobportal::$_db->get_var($query);
            if( !empty($wpjobportal_photo) ){
               WPJOBPORTALincluder::getObjectClass('uploads')->removeUserPhoto($wpjobportal_id);
            }
            WPJOBPORTALincluder::getObjectClass('uploads')->uploadUserPhoto($wpjobportal_id);
        }
        return;
    }

    // End Function
    // setcookies for search form data
    //search cookies data
    function getSearchFormData(){
        $wpjobportal_jsjp_search_array = array();
        $wpjobportal_jsjp_search_array['searchname'] = WPJOBPORTALrequest::getVar('searchname');
        $wpjobportal_jsjp_search_array['searchusername'] = WPJOBPORTALrequest::getVar('searchusername');
        $wpjobportal_jsjp_search_array['searchrole'] = WPJOBPORTALrequest::getVar('searchrole');
        $wpjobportal_jsjp_search_array['searchcompany'] = WPJOBPORTALrequest::getVar('searchcompany');
        $wpjobportal_jsjp_search_array['searchresume'] = WPJOBPORTALrequest::getVar('searchresume');
        $wpjobportal_jsjp_search_array['search_from_user'] = 1;
        return $wpjobportal_jsjp_search_array;
    }

    function getSavedCookiesDataForSearch(){
        $wpjobportal_jsjp_search_array = array();
        $wpjp_search_cookie_data = '';
        if(isset($_COOKIE['jsjp_jobportal_search_data'])){
            $wpjp_search_cookie_data = wpjobportal::wpjobportal_sanitizeData($_COOKIE['jsjp_jobportal_search_data']);
            $wpjp_search_cookie_data = wpjobportalphplib::wpJP_safe_decoding($wpjp_search_cookie_data);
            $wpjp_search_cookie_data = json_decode( $wpjp_search_cookie_data , true );
        }
        if($wpjp_search_cookie_data != '' && isset($wpjp_search_cookie_data['search_from_user']) && $wpjp_search_cookie_data['search_from_user'] == 1){
            $wpjobportal_jsjp_search_array['searchname'] = $wpjp_search_cookie_data['searchname'];
            $wpjobportal_jsjp_search_array['searchusername'] = $wpjp_search_cookie_data['searchusername'];
            $wpjobportal_jsjp_search_array['searchrole'] = $wpjp_search_cookie_data['searchrole'];
            $wpjobportal_jsjp_search_array['searchcompany'] = $wpjp_search_cookie_data['searchcompany'];
            $wpjobportal_jsjp_search_array['searchresume'] = $wpjp_search_cookie_data['searchresume'];
        }
        return $wpjobportal_jsjp_search_array;
    }

    function setSearchVariableForSearch($wpjobportal_jsjp_search_array){
        wpjobportal::$_search['user']['searchname'] = isset($wpjobportal_jsjp_search_array['searchname']) ? $wpjobportal_jsjp_search_array['searchname'] : null;
        wpjobportal::$_search['user']['searchusername'] = isset($wpjobportal_jsjp_search_array['searchusername']) ? $wpjobportal_jsjp_search_array['searchusername'] : null;
        wpjobportal::$_search['user']['searchrole'] = isset($wpjobportal_jsjp_search_array['searchrole']) ? $wpjobportal_jsjp_search_array['searchrole'] : null;
        wpjobportal::$_search['user']['searchcompany'] = isset($wpjobportal_jsjp_search_array['searchcompany']) ? $wpjobportal_jsjp_search_array['searchcompany'] : null;
        wpjobportal::$_search['user']['searchresume'] = isset($wpjobportal_jsjp_search_array['searchresume']) ? $wpjobportal_jsjp_search_array['searchresume'] : null;
    }

    function getMessagekey(){
        $wpjobportal_key = 'user';if(wpjobportal::$_common->wpjp_isadmin()){$wpjobportal_key = 'admin_'.$wpjobportal_key;}return $wpjobportal_key;
    }


}

?>
