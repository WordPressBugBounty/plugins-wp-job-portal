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

    /**
     * Limit a query on the network-wide users table to this site's members.
     *
     * On multisite every site shares wp_users, so "all WordPress users" meant
     * every user of every site. A member of a site is a user with that site's
     * capabilities row, which is what WordPress itself checks.
     *
     * @param string $column The users.ID column in the query, e.g. user.ID.
     * @return string SQL to append to a WHERE clause; '' on a single site.
     */
    function siteMembersOnly($column) {
        global $wpdb;
        if (!is_multisite()) {
            return '';
        }
        $column = preg_replace('/[^A-Za-z0-9_.]/', '', (string) $column);
        return $wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $column is the caller's own column name (e.g. user.ID), reduced to letters, digits, _ and . above; %i cannot take a table.column pair.
            ' AND ' . $column . ' IN (SELECT user_id FROM %i WHERE meta_key = %s)',
            $wpdb->usermeta,
            $wpdb->get_blog_prefix() . 'capabilities'
        );
    }

    /**
     * Leave out this site's administrators (they are not given a portal role).
     *
     * @param string $column The users.ID column in the query.
     * @return string SQL to append to a WHERE clause.
     */
    function notSiteAdministrator($column) {
        global $wpdb;
        $column = preg_replace('/[^A-Za-z0-9_.]/', '', (string) $column);
        return $wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $column is the caller's own column name (e.g. user.ID), reduced to letters, digits, _ and . above; %i cannot take a table.column pair.
            ' AND NOT EXISTS (SELECT umeta_id FROM %i WHERE meta_key = %s AND meta_value LIKE %s AND user_id = ' . $column . ')',
            $wpdb->usermeta,
            $wpdb->get_blog_prefix() . 'capabilities',
            '%' . $wpdb->esc_like('administrator') . '%'
        );
    }

       function getMyAvailableCredits($wpjobportal_uid) {
            if (!is_numeric($wpjobportal_uid))
            return false;

        // Neither table below is created by anything - not Core, not any
        // migration, not any add-on in the original distribution. This lookup
        // has always queried tables that do not exist, and went unnoticed
        // because nothing reached it. Answer with a balance of nothing rather
        // than two failed queries. Zero, not false: callers compare the result
        // against a price, and in PHP 8 `0 <= false` is true.
        if (!class_exists('WPJOBPORTAL_Monetisation')
            || !WPJOBPORTAL_Monetisation::ledger_ready()) {
            return 0;
        }
        $query = "SELECT purchase.purchasecredit AS credits,purchase.expireindays,purchase.created
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_purchasehistory` AS purchase
                    WHERE purchase.uid = " . (int) ($wpjobportal_uid)." AND purchase.transactionverified = 1 ORDER BY purchase.id ASC";
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
            $query = "SELECT credits FROM `" . wpjobportal::$_db->prefix . "wj_portal_credits_log` WHERE uid = " . (int) ($wpjobportal_uid);
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
                $wpjobportal_inquery .= esc_sql($clause) . "a.roleid = " . (int) ($wpjobportal_searchrole);
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
        // Admin only (security fix, 2.7.2). This returns another account's
        // company name, resume title or the resume holder's real name, chosen by
        // the uid in the request. Its only callers are the admin Users list and
        // the admin user-detail screen (modules/user/tmpl/admin_users.php,
        // modules/user/tmpl/views/admin/userdetail.php). The nonce above is the
        // one the front-end profile form uses, so every logged-in portal user
        // holds it; it authenticates the caller but does not authorise reading
        // another user's record, which let any subscriber read other users' data
        // by walking uid.
        if (! current_user_can('manage_options')) {
            return false;
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
                    FROM ' . wpjobportal::$_db->prefix . 'wj_portal_companies AS company WHERE company.uid = ' . (int) ($wpjobportal_uid);
            $wpjobportal_label = __('Company', 'wp-job-portal');
            $wpjobportal_data = wpjobportaldb::get_var($query);
        }elseif($wpjobportal_roleid == 2){
            $query = 'SELECT  resume.application_title AS application_title, CONCAT(resume.first_name," ",resume.last_name) AS name
                    FROM ' . wpjobportal::$_db->prefix . 'wj_portal_resume AS resume WHERE resume.uid = ' . (int) ($wpjobportal_uid);
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
        unset($wpjobportal_roleid); // removeUserData() reads the role from the profile itself.
        return $this->removeUserData($wpjobportal_uid) !== false;
    }

    /**
     * Remove a person's portal data - the one path every account deletion takes.
     *
     * Used by WordPress's own Users screen (the delete_user hook), by bulk and
     * programmatic deletes, by this plugin's Users page, and by the System
     * Status clean-up of records whose WordPress account is already gone.
     *
     * - An employer whose content is attributed to another employer hands over
     *   companies, jobs (with their applications), departments and resume
     *   folders to that employer. A job seeker's data is personal and never
     *   moves to anyone else.
     * - Everything else the person owns is deleted through the same company,
     *   job and resume deletes the admin screens use, so files, child rows and
     *   the add-ons' after-delete hooks are handled once, in one place.
     * - Payment records stay for the site's accounts; the payer's personal
     *   details on invoices are blanked.
     *
     * @param int $wpjobportal_uid          Portal user id (wj_portal_users.id).
     * @param int $wpjobportal_reassign_wp  WordPress user id the content is attributed to, 0 for none.
     * @return array|false Counts of what was moved and deleted, false when there is no such portal user.
     */
    function removeUserData($wpjobportal_uid, $wpjobportal_reassign_wp = 0) {
        global $wpdb;
        $wpjobportal_uid = absint($wpjobportal_uid);
        if ($wpjobportal_uid < 1) {
            return false;
        }
        $wpjobportal_user = wpjobportaldb_direct::get_row($wpdb->prepare('SELECT id, uid, roleid FROM %i WHERE id = %d', $wpdb->prefix . 'wj_portal_users', $wpjobportal_uid));
        if (!$wpjobportal_user) {
            return false;
        }
        $wp_uid = absint($wpjobportal_user->uid);
        $wpjobportal_roleid = (int) $wpjobportal_user->roleid;
        $wpjobportal_report = array('moved_to' => 0, 'moved' => 0, 'jobs' => 0, 'companies' => 0, 'resumes' => 0, 'not_moved_reason' => '');

        // Who takes over an employer's listings, if anyone.
        $wpjobportal_target = 0;
        $wpjobportal_reassign_wp = absint($wpjobportal_reassign_wp);
        if ($wpjobportal_reassign_wp > 0) {
            $wpjobportal_target_row = wpjobportaldb_direct::get_row($wpdb->prepare('SELECT id, roleid FROM %i WHERE uid = %d', $wpdb->prefix . 'wj_portal_users', $wpjobportal_reassign_wp));
            if ($wpjobportal_roleid != 1) {
                $wpjobportal_report['not_moved_reason'] = 'not_employer';
            } elseif (!$wpjobportal_target_row || (int) $wpjobportal_target_row->roleid != 1) {
                $wpjobportal_report['not_moved_reason'] = 'target_not_employer';
            } elseif ((int) $wpjobportal_target_row->id !== $wpjobportal_uid) {
                $wpjobportal_target = (int) $wpjobportal_target_row->id;
            }
        }

        if ($wpjobportal_target > 0) {
            foreach (array('wj_portal_companies' => 'uid', 'wj_portal_jobs' => 'uid', 'wj_portal_departments' => 'uid', 'wj_portal_folders' => 'uid', 'wj_portal_folderresumes' => 'uid', 'wj_portal_messages' => 'employerid') as $wpjobportal_table => $wpjobportal_column) {
                if ($this->portalTableExists($wpjobportal_table)) {
                    $wpjobportal_moved = wpjobportaldb_direct::query($wpdb->prepare('UPDATE %i SET %i = %d WHERE %i = %d', $wpdb->prefix . $wpjobportal_table, $wpjobportal_column, $wpjobportal_target, $wpjobportal_column, $wpjobportal_uid));
                    $wpjobportal_report['moved'] += absint($wpjobportal_moved);
                }
            }
            $wpjobportal_report['moved_to'] = $wpjobportal_target;
            do_action('wpjobportal_user_content_reassigned', $wpjobportal_uid, $wpjobportal_target);
        } else {
            // Jobs first, each through the job delete (applications, cities, hooks), then the companies.
            foreach ((array) wpjobportaldb_direct::get_col($wpdb->prepare('SELECT id FROM %i WHERE uid = %d', $wpdb->prefix . 'wj_portal_jobs', $wpjobportal_uid)) as $wpjobportal_jobid) {
                if (WPJOBPORTALincluder::getJSModel('job')->jobEnforceDelete((int) $wpjobportal_jobid, $wpjobportal_uid) == WPJOBPORTAL_DELETED) {
                    $wpjobportal_report['jobs']++;
                }
            }
            foreach ((array) wpjobportaldb_direct::get_col($wpdb->prepare('SELECT id FROM %i WHERE uid = %d', $wpdb->prefix . 'wj_portal_companies', $wpjobportal_uid)) as $wpjobportal_companyid) {
                if (WPJOBPORTALincluder::getJSModel('company')->companyEnforceDeletes((int) $wpjobportal_companyid, false) == WPJOBPORTAL_DELETED) {
                    $wpjobportal_report['companies']++;
                }
            }
            foreach (array('wj_portal_departments' => 'uid', 'wj_portal_folders' => 'uid', 'wj_portal_folderresumes' => 'uid', 'wj_portal_messages' => 'employerid') as $wpjobportal_table => $wpjobportal_column) {
                $this->deletePortalRows($wpjobportal_table, $wpjobportal_column, $wpjobportal_uid);
            }
        }

        // A job seeker's own data: resumes (with their applications and files) and everything personal around them.
        foreach ((array) wpjobportaldb_direct::get_col($wpdb->prepare('SELECT id FROM %i WHERE uid = %d', $wpdb->prefix . 'wj_portal_resume', $wpjobportal_uid)) as $wpjobportal_resumeid) {
            if (WPJOBPORTALincluder::getJSModel('resume')->resumeEnforceDelete((int) $wpjobportal_resumeid, $wpjobportal_uid, false) == WPJOBPORTAL_DELETED) {
                $wpjobportal_report['resumes']++;
            }
        }
        $wpjobportal_alert_ids = $this->portalTableExists('wj_portal_jobalertsetting') ? array_map('absint', (array) wpjobportaldb_direct::get_col($wpdb->prepare('SELECT id FROM %i WHERE uid = %d', $wpdb->prefix . 'wj_portal_jobalertsetting', $wpjobportal_uid))) : array();
        if (!empty($wpjobportal_alert_ids) && $this->portalTableExists('wj_portal_jobalertcities')) {
            wpjobportaldb_direct::query($wpdb->prepare('DELETE FROM %i WHERE alertid IN (' . implode(',', array_fill(0, count($wpjobportal_alert_ids), '%d')) . ')', ...array_merge(array($wpdb->prefix . 'wj_portal_jobalertcities'), $wpjobportal_alert_ids)));
        }
        foreach (array('wj_portal_jobapply' => 'uid', 'wj_portal_coverletters' => 'uid', 'wj_portal_jobshortlist' => 'uid', 'wj_portal_jobalertsetting' => 'uid', 'wj_portal_jobsearches' => 'uid', 'wj_portal_resumesearches' => 'uid', 'wj_portal_messages' => 'jobseekerid') as $wpjobportal_table => $wpjobportal_column) {
            $this->deletePortalRows($wpjobportal_table, $wpjobportal_column, $wpjobportal_uid);
        }

        // Payments stay on the books; who paid does not.
        if ($this->portalTableExists('wj_portal_invoices')) {
            wpjobportaldb_direct::query($wpdb->prepare("UPDATE %i SET payer_name = %s, payer_email = '', payer_address = '', payer_contactnumber = '' WHERE uid = %d", $wpdb->prefix . 'wj_portal_invoices', __('Deleted user', 'wp-job-portal'), $wpjobportal_uid));
        }

        // The add-ons' own personal data (Recruit, Marketplace), as on a privacy erase request.
        apply_filters('wpjobportal_privacy_erase_personal_data', false, $wpjobportal_uid, $wp_uid);

        wpjobportaldb_direct::query($wpdb->prepare('DELETE FROM %i WHERE id = %d', $wpdb->prefix . 'wj_portal_users', $wpjobportal_uid));
        if ($wp_uid > 0) {
            $wpjobportal_wpdir = wp_upload_dir();
            $wpjobportal_profile_dir = $wpjobportal_wpdir['basedir'] . '/' . wpjobportal::$_config->getConfigurationByConfigName('data_directory') . '/data/profile/profile_' . $wp_uid;
            $wp_filesystem = wpjobportal::wpjobportal_filesystem();
            if ($wp_filesystem && $wp_filesystem->exists($wpjobportal_profile_dir)) {
                $wp_filesystem->rmdir($wpjobportal_profile_dir, true);
            }
        }

        do_action('wpjobportal_user_deleted', $wpjobportal_uid, $wp_uid, $wpjobportal_roleid);
        return $wpjobportal_report;
    }

    /**
     * Whether a WordPress user owns job portal content - companies, jobs,
     * resumes or applications - that deleting the account removes or moves.
     *
     * @param int $wp_uid WordPress user id.
     * @return bool
     */
    function userHasPortalContent($wp_uid) {
        global $wpdb;
        $wpjobportal_uid = absint($this->getUserIDByWPUid(absint($wp_uid)));
        if ($wpjobportal_uid < 1) {
            return false;
        }
        foreach (array('wj_portal_companies', 'wj_portal_jobs', 'wj_portal_resume', 'wj_portal_jobapply') as $wpjobportal_table) {
            if ($this->portalTableExists($wpjobportal_table) && wpjobportaldb_direct::get_var($wpdb->prepare('SELECT id FROM %i WHERE uid = %d LIMIT 1', $wpdb->prefix . $wpjobportal_table, $wpjobportal_uid))) {
                return true;
            }
        }
        return false;
    }

    private function deletePortalRows($wpjobportal_table, $wpjobportal_column, $wpjobportal_uid) {
        global $wpdb;
        if ($this->portalTableExists($wpjobportal_table)) {
            wpjobportaldb_direct::query($wpdb->prepare('DELETE FROM %i WHERE %i = %d', $wpdb->prefix . $wpjobportal_table, $wpjobportal_column, $wpjobportal_uid));
        }
    }

    private function portalTableExists($wpjobportal_table) {
        static $wpjobportal_known = array();
        global $wpdb;
        if (!isset($wpjobportal_known[$wpjobportal_table])) {
            $wpjobportal_known[$wpjobportal_table] = (wpjobportaldb_direct::get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($wpdb->prefix . $wpjobportal_table))) === $wpdb->prefix . $wpjobportal_table);
        }
        return $wpjobportal_known[$wpjobportal_table];
    }

    /**
     * Portal profiles whose WordPress account no longer exists - left behind
     * by deletions from WordPress's Users screen before 2.7.0, whose hook read
     * delete_option as one value while WordPress posts one per user.
     *
     * @return int[] Portal user ids.
     */
    function getOrphanedPortalUserIds($wpjobportal_limit = 0) {
        global $wpdb;
        if ($wpjobportal_limit > 0) {
            return array_map('absint', (array) wpjobportaldb_direct::get_col($wpdb->prepare('SELECT portal.id FROM %i AS portal LEFT JOIN %i AS wpuser ON wpuser.ID = portal.uid WHERE portal.uid > 0 AND wpuser.ID IS NULL ORDER BY portal.id LIMIT %d', $wpdb->prefix . 'wj_portal_users', $wpdb->users, absint($wpjobportal_limit))));
        }
        return array_map('absint', (array) wpjobportaldb_direct::get_col($wpdb->prepare('SELECT portal.id FROM %i AS portal LEFT JOIN %i AS wpuser ON wpuser.ID = portal.uid WHERE portal.uid > 0 AND wpuser.ID IS NULL ORDER BY portal.id', $wpdb->prefix . 'wj_portal_users', $wpdb->users)));
    }

    function getUserRoleByUid($wpjobportal_uid) {
        if (!is_numeric($wpjobportal_uid))
            return false;
        $query = "SELECT roleid FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` WHERE id = " . (int) ($wpjobportal_uid);
        $wpjobportal_result = wpjobportaldb::get_var($query);
        return $wpjobportal_result;
    }

    function getUserRoleByWPUid($wpuid) {
        if (!is_numeric($wpuid))
            return false;
        $query = "SELECT roleid FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` WHERE uid = " . (int) ($wpuid);
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
        $query = "SELECT id FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` WHERE uid = " . (int) ($wpuid);
        $wpjobportal_result = wpjobportaldb::get_var($query);
        return $wpjobportal_result;
    }

    function getWPuidByOuruid($our_uid) {
        if (!is_numeric($our_uid))
            return false;
        $query = "SELECT uid AS wpuid FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` WHERE id = " . (int) ($our_uid);
        $wpjobportal_result = wpjobportaldb::get_var($query);
        return $wpjobportal_result;
    }

    function changeUserStatus($wpjobportal_userid){
        if(!is_numeric($wpjobportal_userid)) return false;
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('users');
        if($wpjobportal_row->load($wpjobportal_userid)){
            $old_status = $wpjobportal_row->status;
            $new_status = 1 - $old_status;
            $wpjobportal_row->columns['status'] = $new_status;

            if($wpjobportal_row->store()){
                // --- NEW HOOK INTEGRATION START ---
                do_action('wpjobportal_user_status_transition', $wpjobportal_userid, $old_status, $new_status);
                // --- NEW HOOK INTEGRATION END ---

                if($new_status == 1){
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

                // removeUserData() also fires wpjobportal_user_deleted, for every way a user is deleted.
                if ($this->removeUserData($wpjobportal_uid) !== false) {
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
                    (SELECT COUNT(job.id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job WHERE job.uid = " . (int) ($wpjobportal_uid)." )
                +   (SELECT COUNT(comp.id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS comp WHERE comp.uid = " . (int) ($wpjobportal_uid)." )";
                if(in_array('departments', wpjobportal::$_active_addons)){
                    $query .= " +   (SELECT COUNT(dep.id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_departments` AS dep WHERE dep.uid = " . (int) ($wpjobportal_uid)." )";
                }
                $query .= " AS total
            ";
        }

        if ($wpjobportal_roleid == 2) { // seeker
            $query = "SELECT
                    (SELECT COUNT(resume.id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume WHERE resume.uid = " . (int) ($wpjobportal_uid)." )

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
        $query = "DELETE FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` WHERE id = " . (int) ($wpjobportal_uid);
        if (wpjobportaldb::query($query)) {
            return true;
        } else {
            return false;
        }
    }


    function getWPRoleNameById($wpjobportal_id) {
        $wpjobportal_rolename = "";
        if ($wpjobportal_id) {
            $wpjobportal_user = new WP_User($wpjobportal_id);
            $wpjobportal_rolename = $wpjobportal_user->roles[0];
        }
        return $wpjobportal_rolename;
    }

    function getuserlistajax() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'get-user-list-ajax') ) {
            die( 'Security check Failed' );
        }
        $wpjobportal_userlimit = WPJOBPORTALrequest::getVar('userlimit', null, 0);
        $wpjobportal_maxrecorded = 10;
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
        $wpjobportal_maxrecorded = 10;
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
                    FROM `" . wpjobportal::$_db->users . "` AS user
                    WHERE NOT EXISTS( SELECT jsuser.id FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` AS jsuser WHERE user.ID = jsuser.uid)"
                    . $this->notSiteAdministrator('user.ID') . $this->siteMembersOnly('user.ID');
        $query .= $wpjobportal_inquery;
        /*
         * No GROUP BY here.
         *
         * COUNT(user.ID) grouped by user.ID counts each user into a group of
         * one, and get_var reads the first row - so the total was 1 however many
         * users there were. The clamp below then reset every page to the first,
         * and the picker showed the same three people out of hundreds, which is
         * exactly how it looked: "all users not listed".
         */
        $wpjobportal_total = wpjobportaldb::get_var($query);

        $limit = $wpjobportal_userlimit * $wpjobportal_maxrecorded;
        if ($limit >= $wpjobportal_total) {
            $limit = 0;
        }

        // Data
        $query = "SELECT DISTINCT user.ID AS userid, user.user_login , user.user_email AS emailaddress, user.display_name AS name
                    FROM `" . wpjobportal::$_db->users . "` AS user
                    WHERE NOT EXISTS( SELECT jsuser.id FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` AS jsuser WHERE user.ID = jsuser.uid)"
                    . $this->notSiteAdministrator('user.ID') . $this->siteMembersOnly('user.ID');

        $query .= $wpjobportal_inquery;
        /*
         * Newest first.
         *
         * Somebody assigning a role is nearly always dealing with a recent
         * registration, and oldest-first buried those at the far end of the
         * list. Ordering rather than filtering, deliberately: a date cut-off
         * would hide an older account with no way for the administrator to tell
         * why it was missing, and the search by name, username or email above
         * still reaches everyone.
         */
        $query .= " ORDER BY user.user_registered DESC, user.ID DESC LIMIT $limit, $wpjobportal_maxrecorded";
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
                        // A blank name left an empty, unclickable link; fall back to the login, then the email.
                        $wpjobportal_username = trim((string) $wpjobportal_username);
                        if ($wpjobportal_username == '') {
                            $wpjobportal_username = !empty($wpjobportal_user->user_login) ? $wpjobportal_user->user_login : $wpjobportal_user->emailaddress;
                        }
                        $wpjobportal_html .='
                            <div class="user-records-row" >
                                <div class="user-list-body-col user-id">
                                    ' . $wpjobportal_user->userid . '
                                </div>
                                <div class="user-list-body-col user-name">
                                    <a href="#" class="userpopup-link js-userpopup-link" data-id=' . esc_attr($wpjobportal_user->userid) . ' data-name="' . esc_attr($wpjobportal_username) . '" data-email="' . esc_attr($wpjobportal_user->emailaddress) . '" >' . esc_html($wpjobportal_username) . '</a>
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

    function getUserData($wpjobportal_id){
        if (!is_numeric($wpjobportal_id))
            return false;
        $query = "SELECT * FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` WHERE id = " . (int) ($wpjobportal_id) ;
        wpjobportal::$_data[0] = wpjobportal::$_db->get_row($query);
        if(!empty(wpjobportal::$_data[0]) && isset(wpjobportal::$_data[0]->roleid)){// roleid not set error in log
            //employer
            if(wpjobportal::$_data[0]->roleid == 1){
                $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` WHERE uid=" . (int) ($wpjobportal_id);
                wpjobportal::$_data['jobs'] = wpjobportal::$_db->get_var($query);

                $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` WHERE uid=" . (int) ($wpjobportal_id);
                wpjobportal::$_data['companies'] = wpjobportal::$_db->get_var($query);
                if(in_array('departments', wpjobportal::$_active_addons)){
                    $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_departments` WHERE uid=" . (int) ($wpjobportal_id);
                    wpjobportal::$_data['department'] = wpjobportal::$_db->get_var($query);
                }

                $query = "SELECT COUNT(jobapply.id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` as jobapply
                JOIN ".wpjobportal::$_db->prefix."wj_portal_jobs AS job ON job.id = jobapply.jobid  WHERE job.uid=" . (int) ($wpjobportal_id);
                wpjobportal::$_data['jobapply'] = wpjobportal::$_db->get_var($query);
            }elseif(wpjobportal::$_data[0]->roleid == 2){
                //jobseeker
                $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` WHERE uid=" . (int) ($wpjobportal_id);
                wpjobportal::$wpjobportal_data['resume'] = wpjobportal::$_db->get_var($query);

                $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply`  WHERE uid=" . (int) ($wpjobportal_id);
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
                . " WHERE a.id = " . (int) ($c_id);
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
        $query = "SELECT uid FROM `".wpjobportal::$_db->prefix."wj_portal_companies` WHERE id = " . (int) ($wpjobportal_companyid);
        $wpjobportal_companyid = wpjobportal::$_db->get_var($query);
        return $wpjobportal_companyid;
    }

    function getUserForForm($u_id){
        if(is_numeric($u_id) == false){
            return false;
        }
        $query = "SELECT CONCAT(first_name,' ',last_name) AS name,user.* FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` AS user
        WHERE user.id = " . (int) ($u_id);
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
        $desc_text = WPJOBPORTALrequest::getVar('description','post','','',1);
        // clean it first
        $desc_text = str_ireplace(['<script', '< script'], '-', $desc_text);
        $wpjobportal_data['description'] = wpautop(wptexturize(wp_kses_post($desc_text)));
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

        $this->syncWPUserName($wpjobportal_row->id, $wpjobportal_data);

        $this->storeUserPhoto(WPJOBPORTALincluder::getObjectClass('user')->getWPuid());

        WPJOBPORTALincluder::getObjectClass('customfields')->storeCustomFields(4,$wpjobportal_row->id,$wpjobportal_data);

        if(!$wpjobportal_data['id']){
            WPJOBPORTALincluder::getJSModel('emailtemplate')->sendMail(2,1,$wpjobportal_row->id);
        }

        if(isset($wpjobportal_data['oldStatus']) && $wpjobportal_data['oldStatus']!=$wpjobportal_data['status']){
            WPJOBPORTALincluder::getJSModel('emailtemplate')->sendMail(2,2,$wpjobportal_row->id);
        }

        // --- NEW HOOK INTEGRATION START ---
        // Blueprint: Data Synchronization Action
        // Fires whenever a user profile (metadata, name, custom fields) is created or updated.
        do_action('wpjobportal_user_profile_saved', $wpjobportal_row->id, $wpjobportal_data);
        // --- NEW HOOK INTEGRATION END ---

        return WPJOBPORTAL_SAVED;
    }

    // Keep the WordPress user (first/last name meta, display name) in step with the portal profile, so WP admin
    // Users and the toolbar greeting show the new name. Only when the form sent the name fields.
    function syncWPUserName($wpjobportal_our_uid, $wpjobportal_data) {
        if (WPJOBPORTALrequest::getVar('wpjobportal_user_first', 'post', null) === null && WPJOBPORTALrequest::getVar('wpjobportal_user_last', 'post', null) === null) {
            return;
        }
        $wpjobportal_wpuid = $this->getWPuidByOuruid($wpjobportal_our_uid);
        if (!is_numeric($wpjobportal_wpuid) || !get_userdata($wpjobportal_wpuid)) {
            return;
        }
        $wpjobportal_first = isset($wpjobportal_data['first_name']) ? $wpjobportal_data['first_name'] : '';
        $wpjobportal_last = isset($wpjobportal_data['last_name']) ? $wpjobportal_data['last_name'] : '';
        $wpjobportal_userdata = array('ID' => (int) $wpjobportal_wpuid, 'first_name' => $wpjobportal_first, 'last_name' => $wpjobportal_last);
        // same display name rule as the registration form
        $wpjobportal_display_name = trim($wpjobportal_first . ' ' . $wpjobportal_last);
        if ($wpjobportal_display_name != '') {
            $wpjobportal_userdata['display_name'] = $wpjobportal_display_name;
        }
        // WordPress user functions expect slashed data and unslash it again (a typed backslash would be lost)
        wp_update_user(wp_slash($wpjobportal_userdata));
    }

    function storeUserPhoto($wpjobportal_id){
        if(!is_numeric($wpjobportal_id)){
            return false;
        }
        if(WPJOBPORTALrequest::fileSize('photo') > 0) { // logo
            // Check the new file first: removing the current photo and then refusing the upload left none.
            if (WPJOBPORTALincluder::getObjectClass('uploads')->checkImageUpload('photo') != 1) {
                $wpjobportal_msg = WPJOBPORTALMessages::getMessage(WPJOBPORTAL_FILE_TYPE_ERROR, '');
                WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'], $this->getMessagekey());
                return;
            }
            $query = "SELECT photo FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` WHERE uid = " . (int) ($wpjobportal_id);
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
        if(WPJOBPORTALrequest::hasCookie('jsjp_jobportal_search_data')){
            $wpjp_search_cookie_data = WPJOBPORTALrequest::cookie('jsjp_jobportal_search_data');
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
