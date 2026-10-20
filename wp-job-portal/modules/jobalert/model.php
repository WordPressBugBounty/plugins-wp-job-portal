<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALJobAlertModel {

    function captchaValidate() {
        if (!is_user_logged_in()) {
            $config_array = wpjobportal::$_config->getConfigByFor('captcha');
            if ($config_array['job_alert_captcha'] == 1) {
                if ($config_array['captcha_selection'] == 1) { // Google recaptcha

                    $gresponse = WPJOBPORTALrequest::getVar('g-recaptcha-response', 'post', '');
                    $resp = wpjobportal_googleRecaptchaHTTPPost($config_array['recaptcha_privatekey'] , $gresponse);

                    if ($resp) {
                        return true;
                    } else {
                        wpjobportal::$_data['google_captchaerror'] = __("Invalid captcha","wp-job-portal");
                        return false;
                    }
                } else { // own captcha
                    $captcha = new WPJOBPORTALcaptcha;
                    $result = $captcha->checkCaptchaUserForm();
                    if ($result == 1) {
                        return true;
                    } else {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * Status of a new job alert. Approving an alert needs the job alert admin
     * screens, which only a paid tier provides; without them a pending alert
     * could never be approved and would never be sent, so it starts approved.
     *
     * @return int 1 approved, 0 pending.
     */
    function getNewAlertStatus() {
        if (!WPJOBPORTAL_Feature_Tiers::job_alert_admin_tools()) {
            return 1;
        }
        return (int) wpjobportal::$_config->getConfigValue('jobalert_auto_approve');
    }

    function storeJobAlertSetting($data,$actionname) { //store job alert setting
        if (empty($data))
            return false;

        // The form only offers frequencies the tier allows, but the form is not
        // where that is decided.
        if (isset($data['alerttype'])) {
            $data['alerttype'] = apply_filters('wpjobportal_job_alert_saved_frequency', $data['alerttype']);
        }
        //$actionid = -1;
        $user = WPJOBPORTALincluder::getObjectClass('user');
        $subType = wpjobportal::$_config->getConfigValue('submission_type');
        $no_package_needed = 0;
        if (!WPJOBPORTALincluder::getObjectClass('user')->isguest()) { // user is visitor then dont check for credits
           // $actionid = $data['creditid'];
            if ($data['id'] == '') {
                # New Credit Code
                if(in_array('credits', wpjobportal::$_active_addons)){
                    if($subType == 1){
                        if (!wpjobportal::$_common->wpjp_isadmin()) {
                            $data['status'] = $this->getNewAlertStatus();
                        }
                    }elseif ($subType == 2) {
                        #Per listing--Confirmation
                        if (!wpjobportal::$_common->wpjp_isadmin()) {
                            if(wpjobportal::$_config->getConfigValue('job_jobalert_price_perlisting') > 0){
                                $data['status'] = 3;
                            }else{
                                $data['status'] = $this->getNewAlertStatus();       
                            }
                        }
                    }elseif ($subType == 3) {

                        $result = WPJOBPORTALincluder::getJSModel('credits')->checkIfPackageDefinedForRole(2); //2 for job seeker
                        if($result == 0){ // 0 means no package found. so allow the action.
                            $no_package_needed = 1;
                        }

                        if($no_package_needed == 0){
                            #Member Ship
                            $upakid = WPJOBPORTALrequest::getVar('upakid',null,0);
                            $package = apply_filters('wpjobportal_addons_userpackages_permodule',false,$upakid,$user->uid(),'remjobalert');
                            if (!wpjobportal::$_common->wpjp_isadmin()) {
                                if( !$package ){
                                    return WPJOBPORTAL_SAVE_ERROR;
                                }
                                if( $package->expired ){
                                    return WPJOBPORTAL_SAVE_ERROR;
                                }
                                //if Department are not unlimited & there is no remaining left
                                if( $package->remjobalert!=-1 && !$package->remjobalert ){ //-1 = unlimited
                                    return WPJOBPORTAL_SAVE_ERROR;
                                }
                            }
                            $data['userpackageid'] = $upakid;
                        }
                        $data['status'] = $this->getNewAlertStatus();
                    }
                }
            }
        }

        if (!$this->captchaValidate()) {
            WPJOBPORTALMessages::setLayoutMessage(__('Incorrect Captcha code', 'wp-job-portal'), 'error',$this->getMessagekey());
            return 2;
        }
        //print_r($data);
        $row = WPJOBPORTALincluder::getJSTable('jobalertsetting');
        $email = $data['contactemail'];
        if ($data['id'] == '') { // only for new
            if ($this->emailValidation($email))
                return WPJOBPORTAL_ALREADY_EXIST;
            $data['lastmailsend'] = date_i18n('Y-m-d H:i:s');
            $curdate = gmdate('Y-m-d H:i:s');
            $data['created'] = $curdate;
        }
        if($data['status'] == ''){ // trying to impliment configuration for job alert
            $data['status'] = $this->getNewAlertStatus();
        }

        // --- NEW FIELDS HANDLING ---
        // Ensure unchecked checkboxes safely evaluate to 0 instead of being skipped by the binder
        if (!isset($data['is_urgent'])) {
            $data['is_urgent'] = 0;
        }
        // ---------------------------

        # Admin and Free Submission Type--
            if (!$row->bind($data)) {
                return WPJOBPORTAL_SAVE_ERROR;
            }
            if (!$row->store()) {
                return WPJOBPORTAL_SAVE_ERROR;
            }
            wpjobportal::$_data['id'] = $row->id;
            if (!empty($data['city']))
                $storemulticity = $this->storeMultiJobAlertCities($data['city'], $row->id);
            // '$upakid' is used but not declared
            $upakid = WPJOBPORTALrequest::getVar('upakid',null,0);
            if(!wpjobportal::$_common->wpjp_isadmin() && $data['id'] == '' && $subType == 3 && $no_package_needed == 0){
                do_action('wpjobportal_addons_user_transactionlog',$row,'jobalert',$upakid,$user->uid(),$data['id']);
            }
            // --- NEW HOOK INTEGRATION START ---
            // Blueprint: Core System Transitions
            // Triggers when a new job alert is successfully created or updated.
            do_action('wpjobportal_job_alert_saved', $row->id, $data['contactemail'], $data);
            // --- NEW HOOK INTEGRATION END ---
        return WPJOBPORTAL_SAVED;
    }

    function storeMultiJobAlertCities($city_id, $alertid) { // city id comma seprated
        if (!is_numeric($alertid))
            return false;
        $query = "SELECT cityid FROM " . wpjobportal::$_db->prefix . "wj_portal_jobalertcities WHERE alertid = " . (int) ($alertid);
        $old_cities = wpjobportaldb::get_results($query);
        $id_array = wpjobportalphplib::wpJP_explode(",", $city_id);
        $row = WPJOBPORTALincluder::getJSTable('jobalertcities');
        $error = array();
        foreach ($old_cities AS $oldcityid) {
            $match = false;
            foreach ($id_array AS $cityid) {
                if ($oldcityid->cityid == $cityid) {
                    $match = true;
                    break;
                }
            }
            if ($match == false) {
                $query = "DELETE FROM " . wpjobportal::$_db->prefix . "wj_portal_jobalertcities WHERE alertid = " . (int) ($alertid) . " AND cityid=" . (int) ($oldcityid->cityid);
                if (!wpjobportaldb::query($query)) {
                    $error[] = 1;
                }
            }
        }

        foreach ($id_array AS $cityid) {
            $insert = true;
            foreach ($old_cities AS $oldcityid) {
                if ($oldcityid->cityid == $cityid) {
                    $insert = false;
                    break;
                }
            }
            if ($insert) {
                $cols = array(
                    'id' => "",
                    'alertid' => $alertid,
                    'cityid' => $cityid
                );
                if (!$row->bind($cols)) {
                    $error[] = 1;
                }
                if (!$row->store()) {
                    $error[] = 1;
                }
            }
        }
        if (!empty($error))
            return false;
        return true;
    }

    function emailValidation($email) {
        if (!$email)
            return;
        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobalertsetting` WHERE contactemail = '" . esc_sql($email) . "'";
        $result = wpjobportaldb::get_var($query);
        if ($result > 0)
            return true;
        else
            return false;
    }

    function getJobAlertbyIdforForm($id) {
        if (!is_numeric($id))
            return false;

        $query = "SELECT jobset.*
            FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobalertsetting` AS jobset
            WHERE jobset.id = " . (int) ($id);

        $setting = wpjobportaldb::get_row($query);
        wpjobportal::$_data[0] = $setting;
        if(in_array('credits', wpjobportal::$_active_addons)){
            if(wpjobportal::$_config->getConfigValue('submission_type') ==2){
                // Payment Config->woocommerce,stripe,paypal
                wpjobportal::$_data['paymentconfig'] = wpjobportal::$_wpjppaymentconfig->getPaymentConfigFor('paypal,stripe,woocommerce',true);
            }
        }
       if ($setting) {
            $multi_lists = wpjobportal::$_common->getMultiSelectEdit($setting->id, 3);
        }
        if (isset($multi_lists) && $multi_lists != "")
            wpjobportal::$_data['multi_lists'] = $multi_lists;
        return;
    }

    function getAlertTypeForCombo() {
        $list = array();

        // 1. Fetch the enabled IDs from the database
        // Ensure you handle the case where it might be empty (default to all if needed,
        // or return empty if nothing is configured)
        $allowed_types_str = wpjobportal::$_config->getConfigValue('job_alert_allowed_types');
        $allowed_ids = !empty($allowed_types_str) ? explode(',', $allowed_types_str) : array();

        // 2. Define all possible options
        $all_options = array(
            (object) array('id' => 1, 'text' => __('Daily', 'wp-job-portal')),
            (object) array('id' => 2, 'text' => __('Weekly', 'wp-job-portal')),
            (object) array('id' => 3, 'text' => __('Monthly', 'wp-job-portal')),
            (object) array('id' => 4, 'text' => __('Instant (As soon as job is posted)', 'wp-job-portal'))
        );

        // 3. Filter the list based on admin settings
        foreach ($all_options as $option) {
            if (in_array((string)$option->id, $allowed_ids)) {
                $list[] = $option;
            }
        }

        // 4. Hold the result to the licensed tier. Free offers the weekly
        // digest; a paid tier widens this back out.
        return apply_filters('wpjobportal_job_alert_frequency_options', $list);
    }

    function unSubscribeJobAlert($alertid) {
        if (!is_numeric($alertid))
            return false;

        // Only the alert's owner (or an administrator) may remove it; the id comes from the URL.
        if (!wpjobportal::$_common->wpjp_isadmin()) {
            $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
            $query = "SELECT uid FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobalertsetting` WHERE id = " . (int) ($alertid);
            $wpjobportal_owner = wpjobportaldb::get_var($query);
            if (!is_numeric($wpjobportal_uid) || $wpjobportal_uid <= 0 || $wpjobportal_owner === null || (int) $wpjobportal_owner !== (int) $wpjobportal_uid) {
                return WPJOBPORTAL_NOT_EXIST;
            }
        }

        $row = WPJOBPORTALincluder::getJSTable('jobalertsetting');
        $query = "DELETE jobalert,acity
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobalertsetting` AS jobalert
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobalertcities` AS acity ON acity.alertid=jobalert.id
            WHERE jobalert.id = " . (int) ($alertid);

        if (!wpjobportaldb::query($query)) {
            return false;
        }
        // --- NEW HOOK INTEGRATION START ---
        // Blueprint: Core System Transitions
        // Fires immediately after an alert is deleted, ensuring external CRMs drop the tag.
        do_action('wpjobportal_job_alert_unsubscribed', $alertid);
        // --- NEW HOOK INTEGRATION END ---
        return WPJOBPORTAL_DELETED;
    }

    function unSubscribeJobAlertEmail($emailaddress) {
        if(empty($emailaddress))
            return false;

        $query = "SELECT COUNT(id) FROM `".wpjobportal::$_db->prefix . "wj_portal_jobalertsetting` WHERE contactemail = '" .esc_sql($emailaddress). "'";
        $total = wpjobportaldb::get_var($query);
        if($total == 0){
            return WPJOBPORTAL_NOT_EXIST;
        }

        $query = "DELETE jobalert,acity
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobalertsetting` AS jobalert
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobalertcities` AS acity ON acity.alertid=jobalert.id
            WHERE jobalert.contactemail = '" . esc_sql($emailaddress) . "'";
        $result = wpjobportaldb::query($query);
        if (!$result) {
            return false;
        }
        return WPJOBPORTAL_DELETED;
    }

    function getAllJobAlerts() {
        //Filters
        // $title = WPJOBPORTALrequest::getVar('title');
        // $email = WPJOBPORTALrequest::getVar('email');
        // $location = WPJOBPORTALrequest::getVar('location');
        // $category = WPJOBPORTALrequest::getVar('category');
        // // $alertstatus = WPJOBPORTALrequest::getVar('alertstatus');
        // $status = WPJOBPORTALrequest::getVar('status');

        $title = wpjobportal::$_search['search_filter']['title'];
        $email = wpjobportal::$_search['search_filter']['email'];
        $location = wpjobportal::$_search['search_filter']['location'];
        $category = wpjobportal::$_search['search_filter']['category'];
        //$alertstatus = wpjobportal::$_search['search_filter']['alertstatus'];
        $status = wpjobportal::$_search['search_filter']['status'];

        // $formsearch = WPJOBPORTALrequest::getVar('WPJOBPORTAL_form_search', 'post');
        // if ($formsearch == 'WPJOBPORTAL_SEARCH') {
        //     $_SESSION['WPJOBPORTAL_SEARCH']['title'] = $title;
        //     $_SESSION['WPJOBPORTAL_SEARCH']['email'] = $email;
        //     $_SESSION['WPJOBPORTAL_SEARCH']['location'] = $location;
        //     $_SESSION['WPJOBPORTAL_SEARCH']['category'] = $category;
        //     // $_SESSION['WPJOBPORTAL_SEARCH']['alertstatus'] = $alertstatus;
        //     $_SESSION['WPJOBPORTAL_SEARCH']['status'] = $status;
        // }
        // if (WPJOBPORTALrequest::getVar('pagenum', 'get', null) != null) {
        //     $title = (isset($_SESSION['WPJOBPORTAL_SEARCH']['title']) && $_SESSION['WPJOBPORTAL_SEARCH']['title'] != '') ? sanitize_key($_SESSION['WPJOBPORTAL_SEARCH']['title']) : null;
        //     $email = (isset($_SESSION['WPJOBPORTAL_SEARCH']['email']) && $_SESSION['WPJOBPORTAL_SEARCH']['email'] != '') ? sanitize_key($_SESSION['WPJOBPORTAL_SEARCH']['email']) : null;
        //     $location = (isset($_SESSION['WPJOBPORTAL_SEARCH']['location']) && $_SESSION['WPJOBPORTAL_SEARCH']['location'] != '') ? sanitize_key($_SESSION['WPJOBPORTAL_SEARCH']['location']) : null;
        //     $category = (isset($_SESSION['WPJOBPORTAL_SEARCH']['category']) && $_SESSION['WPJOBPORTAL_SEARCH']['category'] != '') ? sanitize_key($_SESSION['WPJOBPORTAL_SEARCH']['category']) : null;
        //     // $alertstatus = (isset($_SESSION['WPJOBPORTAL_SEARCH']['alertstatus']) && $_SESSION['WPJOBPORTAL_SEARCH']['alertstatus'] != '') ? sanitize_key($_SESSION['WPJOBPORTAL_SEARCH']['alertstatus']) : null;
        //     $status = (isset($_SESSION['WPJOBPORTAL_SEARCH']['status']) && $_SESSION['WPJOBPORTAL_SEARCH']['status'] != '') ? sanitize_key($_SESSION['WPJOBPORTAL_SEARCH']['status']) : null;
        // } elseif ($formsearch !== 'WPJOBPORTAL_SEARCH') {
        //     if (isset($_SESSION['WPJOBPORTAL_SEARCH'])) {
        //         unset($_SESSION['WPJOBPORTAL_SEARCH']);
        //     }
        // }
        $inquery = '';
        $clause = ' WHERE ';
        if ($title != null) {
            $inquery .= $clause . " LOWER(alert.name) LIKE '%" . esc_sql($title) . "%'";
            $clause = ' AND ';
        }

        if ($email != null) {
            $inquery .= $clause . " alert.contactemail LIKE '%" . esc_sql($email) . "%'";
            $clause = ' AND ';
        }

        if ($category != null) {
            $inquery .= $clause . " alert.categoryid = " . (int) ($category);
            $clause = ' AND ';
        }

        if ($location != null) {
            $inquery .= $clause . " alert.city LIKE '%" . esc_sql($location) . "%'";
            $clause = ' AND ';
        }

        // if ($alertstatus != null) {
        //     $inquery .= $clause . " alert.alerttype = " . (int) ($alertstatus);
        //     $clause = ' AND ';
        // }

        if ($status != null) {
            $inquery .= $clause . " alert.status = " . (int) ($status);
            $clause = ' AND ';
        }

        wpjobportal::$_data['filter']['title'] = $title;
        wpjobportal::$_data['filter']['email'] = $email;
        wpjobportal::$_data['filter']['location'] = $location;
        wpjobportal::$_data['filter']['category'] = $category;
        // wpjobportal::$_data['filter']['alertstatus'] = $alertstatus;
        wpjobportal::$_data['filter']['status'] = $status;

        //Pagination
        $query = "SELECT COUNT(*) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobalertsetting` AS alert ";
        $query.=$inquery;

        $total = wpjobportaldb::get_var($query);
        wpjobportal::$_data['total'] = $total;
        wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($total);

        //Data
        $query = "SELECT alert.*,cat.cat_title
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobalertsetting` AS alert
                    JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON alert.categoryid = cat.id";
        $query.=$inquery;
        $query.= " LIMIT " . WPJOBPORTALpagination::$_offset . "," . WPJOBPORTALpagination::$_limit;
        wpjobportal::$_data[0] = wpjobportaldb::get_results($query);
        return;
    }

    function getMyJobAlertId() {
        $uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        if (!is_numeric($uid))
            return false;
        $query = "SELECT id FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobalertsetting` WHERE uid = " . (int) ($uid);
        $id = wpjobportal::$_db->get_var($query);
        return $id;
    }

    function canAddJobAlert($uid,$actionname){
        $credits = apply_filters('wpjobportal_addons_userpackages_module_wise',false,$uid,$actionname);
        if($credits){
            return true;
        }else{
            return false;
        }
    }

    /* new code */

    function isValidExternalCronSecret($secret) {
        if (!is_string($secret) || $secret === '') {
            return false;
        }

        return wpjobportal::$_config->checkCronKey($secret);
    }

    function sendAllJobAlerts() {
        $lock_name = 'wpjobportal_jobalert_run_lock';
        if (get_transient($lock_name)) {
            return 'locked';
        }

        // The lock prevents simultaneous WP-Cron and external-cron requests
        // from sending duplicate alert batches.
        set_transient($lock_name, 1, 15 * MINUTE_IN_SECONDS);
        try {
            $this->sendJobAlert(1); // Daily subscribers.
            $this->sendJobAlert(2); // Weekly subscribers.
            $this->sendJobAlert(3); // Monthly subscribers.
            return true;
        } catch (Exception $exception) {
            return false;
        } finally {
            delete_transient($lock_name);
        }
    }

    function sendJobAlertByAlertType($ck) {
        if (!$this->isValidExternalCronSecret($ck)) {
            return false;
        }

        return $this->sendAllJobAlerts();
    }


    function sendJobAlert($alerttype) {
        $admin_jobs = '';

        $config = wpjobportal::$_config->getConfigByFor('email');

        $curdate = gmdate('Y-m-d H:i:s');
        if ($alerttype == 1)
            $days = 1;
        elseif ($alerttype == 2)
            $days = 7;
        elseif ($alerttype == 3)
            $days = 30;

        if ($alerttype == 1)
            $alerttitle = 'Daily';
        elseif ($alerttype == 2)
            $alerttitle = 'Weekly';
        elseif ($alerttype == 3)
            $alerttitle = 'Monthly';

        $query = "SELECT person.*
                    FROM `".wpjobportal::$_db->prefix."wj_portal_jobalertsetting` AS person
                    WHERE person.alerttype = " . (int) ($alerttype) . " AND DATE(DATE_ADD(person.lastmailsend,INTERVAL " . (int) ($days) . " DAY)) <= CURDATE()
                    AND person.status = 1";

        $persons = wpjobportaldb::get_results($query);

        if (empty($persons))
            return false; // no person were selected for mail

        // moved this code up to avoid calling the template for every email seperatly
        $query = "SELECT template.* FROM `".wpjobportal::$_db->prefix."wj_portal_emailtemplates` AS template WHERE template.templatefor = 'job-alert'";
        $template = wpjobportaldb::get_row($query);

        $currentYear = gmdate('Y');
        $siteTitle = wpjobportal::$_config->getConfigValue('title');

        foreach ($persons AS $person) {
            $wherequery = "";
            $query = "SELECT malert.cityid
                        FROM `".wpjobportal::$_db->prefix."wj_portal_jobalertcities` AS malert
                        WHERE malert.alertid = " . (int) ($person->id);

            $alertcities = wpjobportaldb::get_results($query);

            if (is_object($alertcities)) {
                $lenght = count($alertcities);
                for ($i = 0; $i < $lenght; $i++) {
                    if ($i == 0)
                        $wherequery .= " AND ( mjob.cityid=" . (int) ($alertcities[$i]->cityid);
                    else
                        $wherequery .= " OR mjob.cityid=" . (int) ($alertcities[$i]->cityid);
                }
                $wherequery .= ")";
            }

            if ($alerttype == 1)
                $wherequery .= " AND DATE(job.startpublishing) = CURDATE()";
            if ($alerttype == 2)
                $wherequery .= " AND job.startpublishing >= DATE_SUB(CURDATE(),INTERVAL 7 DAY)";
            if ($alerttype == 3)
                $wherequery .= " AND job.startpublishing >= DATE_SUB(CURDATE(),INTERVAL 30 DAY)";

            $wherequery .= " AND job.startpublishing BETWEEN '" . esc_sql($person->lastmailsend) . "' AND '" . esc_sql($curdate) . "'";

            // --- NEW DYNAMIC FIELDS FILTERING START ---
            if (isset($person->company) && $person->company > 0) {
                $wherequery .= " AND job.company = " . (int) ($person->company);
            }

            if (isset($person->jobtype) && $person->jobtype > 0) {
                $wherequery .= " AND job.jobtype = " . (int) ($person->jobtype);
            }

            if (isset($person->workplace_type) && $person->workplace_type > 0) {
                $wherequery .= " AND job.workplace_type = " . (int) ($person->workplace_type);
            }

            if (isset($person->is_urgent) && $person->is_urgent == 1) {
                $wherequery .= " AND job.is_urgent = 1";
            }

            if (isset($person->tags) && !empty($person->tags)) {
                // Tags are stored as comma-separated text strings (e.g., "php, css, html")
                $tags = explode(',', $person->tags);
                $tag_conditions = array();
                foreach ($tags as $tag) {
                    $tag = trim($tag);
                    if (!empty($tag)) {
                        // Match jobs by text, mimicking the keywords logic
                        $tag_conditions[] = " job.tags LIKE LOWER ('%" . esc_sql($tag) . "%')";
                    }
                }
                if (!empty($tag_conditions)) {
                    // Match jobs that have ANY of the selected tags
                    $wherequery .= " AND (" . implode(' OR ', $tag_conditions) . ")";
                }
            }
            // --- NEW DYNAMIC FIELDS FILTERING END ---

            $metakeywords = array();
            if (isset($person->keywords)) {
                $keywords = wpjobportalphplib::wpJP_explode(' ', $person->keywords);
                $length = count($keywords);
                if ($length <= 5)
                    $i = $length;
                else
                    $i = 5;

                for ($j = 0; $j < $i; $j++) {
                    $metakeywords[] = " job.metakeywords LIKE LOWER ('%" . esc_sql($keywords[$j]) . "%')";
                }
                $metakeywords[] = " job.metakeywords = '' OR job.metakeywords IS NULL";
            }

            // UPDATED DATA FETCHING: Added LEFT JOINs to fetch companyname, jobtypetitle, and extra columns
            $query = "SELECT DISTINCT job.id, job.title, cat.cat_title AS categorytitle, job.city, job.workplace_type, job.is_urgent, job.tags, comp.name AS companyname, jtype.title AS jobtypetitle
                                FROM `".wpjobportal::$_db->prefix."wj_portal_jobs` AS job
                                JOIN `".wpjobportal::$_db->prefix."wj_portal_categories` AS cat ON cat.id = job.jobcategory
                                LEFT JOIN `".wpjobportal::$_db->prefix."wj_portal_jobcities` AS mjob ON mjob.jobid = job.id
                                LEFT JOIN `".wpjobportal::$_db->prefix."wj_portal_companies` AS comp ON comp.id = job.company
                                LEFT JOIN `".wpjobportal::$_db->prefix."wj_portal_jobtypes` AS jtype ON jtype.id = job.jobtype
                                WHERE job.jobcategory = " . (int) ($person->categoryid);

            if ($person->keywords)
                $query .= " AND ( " . implode(' OR ', $metakeywords) . " )";
            $query .=$wherequery;

            $jobs = wpjobportaldb::get_results($query);
            foreach ($jobs AS $job) {  // for multicity select
                $multicitydata = WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($job->city);
                if ($multicitydata != "")
                    $job->mcity = $multicitydata;
            }

            if (!empty($jobs)) {
                $msgSubject = $template->subject;
                $msgBody = $template->body;
                // $mail_jobs = '<table width="100%" cellpadding="10px" cellspacing="0">
                //                 <tr>
                //                     <th>' . __('Job Title','wp-job-portal') . '</th>
                //                     <th>' . __('Job Category','wp-job-portal') . '</th>
                //                     <th>' . __('Job Location','wp-job-portal') . '</th>
                //                 </tr>';
                // foreach ($jobs AS $job) {
                //     $comma = '';
                //     $location = '';
                //     if (isset($job->mcity))
                //         $location = $job->mcity;
                //     $path = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'viewjob', 'wpjobportalid'=>$job->id, 'wpjobportalpageid'=>WPJOBPORTALRequest::getVar('wpjobportalpageid')));
                //     $mail_jobs .= '<tr>
                //                         <td><a href="' . $path . '" target="_blank">' . $job->title . '</a></td>
                //                         <td>' . $job->categorytitle . '</td>
                //                         <td>' . $location . '</td>
                //                     </tr>';
                // }
                // // $admin_jobs .= $m_jobs;
                // // $mail_jobs .= $m_jobs;
                // $mail_jobs .= '</table>';
                $mail_jobs = $this->generateJobsEmailTable($jobs);
                $admin_jobs .= $mail_jobs . " <br/> To " . $person->name . " <br/><br/>";
                $msgBody = wpjobportalphplib::wpJP_str_replace('{JOBSEEKER_NAME}', $person->name, $msgBody);
                $msgBody = wpjobportalphplib::wpJP_str_replace('{JOBS_INFO}', $mail_jobs, $msgBody);
                $msgBody = wpjobportalphplib::wpJP_str_replace('{CURRENT_YEAR}', $currentYear, $msgBody);
                $msgBody = wpjobportalphplib::wpJP_str_replace('{SITETITLE}', $siteTitle, $msgBody);
                $msgBody = wpjobportalphplib::wpJP_str_replace('{EMAIL}', $person->contactemail, $msgBody);

                $recevierEmail = $person->contactemail;
                $subject = $msgSubject;
                $body = $msgBody;
                $senderEmail = $config['mailfromaddress'];
                $senderName = $config['mailfromname'];
                WPJOBPORTALincluder::getJSModel('emailtemplate')->sendEmail($recevierEmail, $subject, $body, $senderEmail, $senderName);
                // --- NEW HOOK INTEGRATION START ---
                // Blueprint: wpjobportal_job_alert_triggered
                // Fires when the Job Alert add-on identifies matches for a user.
                // Allows external push-notification services or APIs to intercept the alert payload.
                $subscriber_uid = isset($person->uid) ? $person->uid : 0;
                do_action('wpjobportal_job_alert_triggered', $person->id, $subscriber_uid, $jobs);
                // --- NEW HOOK INTEGRATION END ---
            }

            //update last mail send
            $query = "UPDATE `".wpjobportal::$_db->prefix."wj_portal_jobalertsetting` SET lastmailsend = '" . esc_sql($curdate) . "' WHERE id = " . (int) ($person->id);
            wpjobportaldb::query($query);


        } // end of each persons foreach loop

        // mail send to admin
        // Only send the summary if alerts were actually dispatched
        // &&
        // configuration to control job alert report to admin
        $job_alert_admin_report = wpjobportal::$_config->getConfigValue('job_alert_admin_report');

        if (!empty($admin_jobs) && $job_alert_admin_report == 1) {
            // mail send to admin
            // new code
            // 1. Fetch the generic system email template from the database
            $query = "SELECT template.* FROM `" . wpjobportal::$_db->prefix . "wj_portal_emailtemplates` AS template WHERE template.templatefor = 'system-email'";
            $systemTemplate = wpjobportaldb::get_row($query);

            // Ensure the template exists before proceeding
            if ($systemTemplate) {
                $msgSubject = $systemTemplate->subject;
                $msgBody = $systemTemplate->body;

                $currentYear = gmdate('Y');
                $siteTitle = wpjobportal::$_config->getConfigValue('title');
                $recevierEmail = $config['adminemailaddress'];

                // Prepare the specific context for this admin notification
                $adminBodyContent = esc_html__('The following job alerts were successfully processed and sent.', 'wp-job-portal') . '<br/><br/><strong>' . esc_html__('Alert type:', 'wp-job-portal') . '</strong> ' . esc_html($alerttitle) . "<br /><br />" . $admin_jobs;

                // Replace the placeholders in the Subject
                $msgSubject = wpjobportalphplib::wpJP_str_replace('{SYSTEM_SUBJECT}', 'Job Alert Information', $msgSubject);

                // Replace the placeholders in the Body
                $msgBody = wpjobportalphplib::wpJP_str_replace('{NOTIFICATION_HEADING}', 'Job Alert Summary', $msgBody);
                $msgBody = wpjobportalphplib::wpJP_str_replace('{USER_NAME}', 'Admin', $msgBody);
                $msgBody = wpjobportalphplib::wpJP_str_replace('{EMAIL_BODY}', $adminBodyContent, $msgBody);
                $msgBody = wpjobportalphplib::wpJP_str_replace('{SITETITLE}', $siteTitle, $msgBody);
                $msgBody = wpjobportalphplib::wpJP_str_replace('{EMAIL}', $recevierEmail, $msgBody);
                $msgBody = wpjobportalphplib::wpJP_str_replace('{CURRENT_YEAR}', $currentYear, $msgBody);

                // Send the email using your standard mailer
                $subject = $msgSubject;
                $body = $msgBody;
                $senderEmail = $config['mailfromaddress'];
                $senderName = $config['mailfromname'];

                WPJOBPORTALincluder::getJSModel('emailtemplate')->sendEmail($recevierEmail, $subject, $body, $senderEmail, $senderName);
            }
        }
        // old code
        // $recevierEmail = $config['adminemailaddress'];
        // $subject = "Job Alert Information";
        // $body = "<p>Dear Admin</p><br/>Following jobs was succefully sent.<br/>Alert type: $alerttitle <br />" . $admin_jobs;
        // $senderEmail = $config['mailfromaddress'];
        // $senderName = $config['mailfromname'];
        // WPJOBPORTALincluder::getJSModel('emailtemplate')->sendEmail($recevierEmail, $subject, $body, $senderEmail, $senderName);
    }
    /*
    function sendInstantAlertsBackground($job_id) {
        if (!is_numeric($job_id)) return;

        // 1. UPDATED DATA FETCHING: Added JOINs to fetch dynamic text titles alongside the raw IDs
        $query = "SELECT job.*, comp.name AS companyname, jtype.title AS jobtypetitle, cat.cat_title AS categorytitle
                  FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                  LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS comp ON comp.id = job.companyid
                  LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jtype ON jtype.id = job.jobtype
                  LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON cat.id = job.jobcategory
                  WHERE job.id = " . (int) ($job_id) . " AND job.status = 1 LIMIT 1";
        $job = wpjobportaldb::get_row($query);

        if (!$job) return;

        // 2. Optimized SQL: Get "Instant" alerts matching this Job's Category & City
        // We use a LEFT JOIN to ensure if an alert has NO specific cities, it still gets pulled.
        $query = "SELECT DISTINCT a.* FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobalertsetting` AS a
                  LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobalertcities` AS ac ON a.id = ac.alertid
                  WHERE a.status = 1
                  AND a.alerttype = 4
                  AND a.categoryid = " . (int) ($job->jobcategory) . "
                  AND (ac.cityid IS NULL OR ac.cityid = " . (int) ($job->city) . ")";

        $subscribers = wpjobportaldb::get_results($query);


        if (empty($subscribers)) return;
        $config = wpjobportal::$_config->getConfigByFor('email');

        // Fetch Template
        $query = "SELECT * FROM `" . wpjobportal::$_db->prefix . "wj_portal_emailtemplates` WHERE templatefor = 'job-alert' LIMIT 1";
        $template = wpjobportaldb::get_row($query);

        // 3. Loop through potential matches and strictly filter the dynamic fields
        $currentYear = gmdate('Y');
        $siteTitle = wpjobportal::$_config->getConfigValue('title');
        foreach ($subscribers as $person) {
            $is_match = true;

            // Filter: Company
            //if (!empty($person->company) && $person->company != $job->companyid) { $is_match = false; }

            // Filter: Job Type
            //if (!empty($person->jobtype) && $person->jobtype != $job->jobtype) { $is_match = false; }

            // Filter: Workplace Type
            //if (!empty($person->workplace_type) && $person->workplace_type != $job->workplace_type) { $is_match = false; }

            // Filter: Is Urgent
            //if ($person->is_urgent == 1 && $job->is_urgent != 1) { $is_match = false; }

            // // Filter: Tags (Text matching)
            // if ($is_match && !empty($person->tags)) {
            //     $alert_tags = array_map('trim', explode(',', strtolower($person->tags)));
            //     $job_tags_lower = strtolower($job->tags);
            //     $tag_found = false;
            //     foreach ($alert_tags as $t) {
            //         if (strpos($job_tags_lower, $t) !== false) {
            //             $tag_found = true; break;
            //         }
            //     }
            //     if (!$tag_found) $is_match = false;
            // }

            // // Filter: Keywords (Title / Metakeywords matching)
            // if ($is_match && !empty($person->keywords)) {
            //     $keywords = array_map('trim', explode(' ', strtolower($person->keywords)));
            //     $search_pool = strtolower($job->title . ' ' . $job->metakeywords);
            //     $keyword_found = false;
            //     foreach ($keywords as $kw) {
            //         if (strpos($search_pool, $kw) !== false) {
            //             $keyword_found = true; break;
            //         }
            //     }
            //     if (!$keyword_found) $is_match = false;
            // }

            //echo var_dump($is_match);exit;

            // 4. Send the Email if it passes all filters
            if ($is_match) {
                $path = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'viewjob', 'wpjobportalid'=>$job->id));

                // $mail_jobs = '<table width="100%" cellpadding="10px" cellspacing="0">
                //                 <tr><th>' . __('Job Title','wp-job-portal') . '</th></tr>
                //                 <tr><td><a href="' . $path . '" target="_blank">' . $job->title . '</a></td></tr>
                //               </table>';
                $mail_jobs = $this->generateJobsEmailTable([$job]);
                $msgBody = wpjobportalphplib::wpJP_str_replace('{JOBSEEKER_NAME}', $person->name, $template->body);
                $msgBody = wpjobportalphplib::wpJP_str_replace('{JOBS_INFO}', $mail_jobs, $msgBody);
                $msgBody = wpjobportalphplib::wpJP_str_replace('{CURRENT_YEAR}', $currentYear, $msgBody);
                $msgBody = wpjobportalphplib::wpJP_str_replace('{SITETITLE}', $siteTitle, $msgBody);
                $msgBody = wpjobportalphplib::wpJP_str_replace('{EMAIL}', $person->contactemail, $msgBody);

                echo '<pre>';print_r($msgBody);echo '</pre>';
                exit;

                WPJOBPORTALincluder::getJSModel('emailtemplate')->sendEmail(
                    $person->contactemail,
                    $template->subject,
                    $msgBody,
                    $config['mailfromaddress'],
                    $config['mailfromname']
                );

                // Update Last Mail Sent for this user ??
                // $update_q = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_jobalertsetting` SET lastmailsend = '" . current_time('mysql') . "' WHERE id = " . (int) ($person->id);
                // wpjobportaldb::query($update_q);
            }
        }
    }

    */
    function sendInstantAlertsBackground($job_id) {
            if (!is_numeric($job_id)) return;

            // 1. Fetch Job Data
            $query = "SELECT job.*, comp.name AS companyname, jtype.title AS jobtypetitle, cat.cat_title AS categorytitle
                      FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                      LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS comp ON comp.id = job.companyid
                      LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jtype ON jtype.id = job.jobtype
                      LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON cat.id = job.jobcategory
                      WHERE job.id = " . (int) ($job_id) . " AND job.status = 1 LIMIT 1";
            $job = wpjobportaldb::get_row($query);

            if (!$job) return;

            // 2. Optimized SQL: Use FIND_IN_SET to handle jobs with multiple comma-separated cities
            $job_city_ids = implode(',', array_filter(array_map('absint', wpjobportalphplib::wpJP_explode(',', (string) $job->city))));
            $query = "SELECT DISTINCT a.* FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobalertsetting` AS a
                      LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobalertcities` AS ac ON a.id = ac.alertid
                      WHERE a.status = 1
                      AND a.alerttype = 4
                      AND a.categoryid = " . (int) ($job->jobcategory) . "
                      AND (ac.cityid IS NULL OR FIND_IN_SET(ac.cityid, '" . esc_sql($job_city_ids) . "') > 0)";

            $subscribers = wpjobportaldb::get_results($query);

            if (empty($subscribers)) return;
            $config = wpjobportal::$_config->getConfigByFor('email');

            // Fetch Template
            $query = "SELECT * FROM `" . wpjobportal::$_db->prefix . "wj_portal_emailtemplates` WHERE templatefor = 'job-alert' LIMIT 1";
            $template = wpjobportaldb::get_row($query);

            // 3. Loop through potential matches and strictly filter the dynamic fields
            $currentYear = gmdate('Y');
            $siteTitle = wpjobportal::$_config->getConfigValue('title');

            foreach ($subscribers as $person) {
                $is_match = true;

                // Filter: Company
                if (!empty($person->company) && $person->company != $job->companyid) { $is_match = false; }

                // Filter: Job Type
                if (!empty($person->jobtype) && $person->jobtype != $job->jobtype) { $is_match = false; }

                // Filter: Workplace Type
                if (!empty($person->workplace_type) && $person->workplace_type != $job->workplace_type) { $is_match = false; }

                // Filter: Is Urgent
                if ($person->is_urgent == 1 && $job->is_urgent != 1) { $is_match = false; }

                // Filter: Tags (Text matching)
                if ($is_match && !empty($person->tags)) {
                    $alert_tags = array_map('trim', explode(',', strtolower($person->tags)));
                    $job_tags_lower = strtolower($job->tags);
                    $tag_found = false;
                    foreach ($alert_tags as $t) {
                        if (strpos($job_tags_lower, $t) !== false) {
                            $tag_found = true; break;
                        }
                    }
                    if (!$tag_found) $is_match = false;
                }

                // Filter: Keywords (Title / Metakeywords matching)
                if ($is_match && !empty($person->keywords)) {
                    $keywords = array_map('trim', explode(' ', strtolower($person->keywords)));
                    $search_pool = strtolower($job->title . ' ' . $job->metakeywords);
                    $keyword_found = false;
                    foreach ($keywords as $kw) {
                        if (strpos($search_pool, $kw) !== false) {
                            $keyword_found = true; break;
                        }
                    }
                    if (!$keyword_found) $is_match = false;
                }

                // 4. Send the Email if it passes all filters
                if ($is_match) {
                    $mail_jobs = $this->generateJobsEmailTable([$job]);
                    $msgBody = wpjobportalphplib::wpJP_str_replace('{JOBSEEKER_NAME}', $person->name, $template->body);
                    $msgBody = wpjobportalphplib::wpJP_str_replace('{JOBS_INFO}', $mail_jobs, $msgBody);
                    $msgBody = wpjobportalphplib::wpJP_str_replace('{CURRENT_YEAR}', $currentYear, $msgBody);
                    $msgBody = wpjobportalphplib::wpJP_str_replace('{SITETITLE}', $siteTitle, $msgBody);
                    $msgBody = wpjobportalphplib::wpJP_str_replace('{EMAIL}', $person->contactemail, $msgBody);

                    WPJOBPORTALincluder::getJSModel('emailtemplate')->sendEmail(
                        $person->contactemail,
                        $template->subject,
                        $msgBody,
                        $config['mailfromaddress'],
                        $config['mailfromname']
                    );

                    // Update Last Mail Sent for this user
                    $update_q = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_jobalertsetting` SET lastmailsend = '" . current_time('mysql') . "' WHERE id = " . (int) ($person->id);
                    wpjobportaldb::query($update_q);
                }
            }
        }
    /**
     * Generates a unified HTML table for job alerts with dynamic fields and ordering.
     * @param array $jobs Array of job objects.
     * @return string HTML table.
     */
    public function generateJobsEmailTable($jobs) {
        if (empty($jobs)) return '';

        // Fetch Field Ordering Data (ID 2 is for Job Fields)
        $wpjobportal_job_listing_fields = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldOrderingData(2);

        // Configuration check for globally toggled items like Company Name
        $wpjobportal_show_comp_name = wpjobportal::$_config->getConfigValue('comp_name');

        // Determine Visibility Status based on field ordering
        $wpjobportal_show_category  = isset($wpjobportal_job_listing_fields['jobcategory']) && $wpjobportal_job_listing_fields['jobcategory'] != '';
        $wpjobportal_show_type      = isset($wpjobportal_job_listing_fields['jobtype']) && $wpjobportal_job_listing_fields['jobtype'] != '';
        $wpjobportal_show_workplace = isset($wpjobportal_job_listing_fields['workplace_type']) && $wpjobportal_job_listing_fields['workplace_type'] != '';
        $wpjobportal_show_location  = isset($wpjobportal_job_listing_fields['city']) && $wpjobportal_job_listing_fields['city'] != '';
        $wpjobportal_show_tags      = isset($wpjobportal_job_listing_fields['tags']) && $wpjobportal_job_listing_fields['tags'] != '';
        $wpjobportal_show_urgent    = isset($wpjobportal_job_listing_fields['is_urgent']) && $wpjobportal_job_listing_fields['is_urgent'] != '';

        // Fetch Labels dynamically using wpjobportal_getVariableValue for translations
        $wpjobportal_label_title     = isset($wpjobportal_job_listing_fields['title']) ? wpjobportal::wpjobportal_getVariableValue($wpjobportal_job_listing_fields['title']) : esc_html__('Job Title', 'wp-job-portal');
        $wpjobportal_label_category  = $wpjobportal_show_category ? wpjobportal::wpjobportal_getVariableValue($wpjobportal_job_listing_fields['jobcategory']) : '';
        $wpjobportal_label_type      = $wpjobportal_show_type ? wpjobportal::wpjobportal_getVariableValue($wpjobportal_job_listing_fields['jobtype']) : '';
        $wpjobportal_label_workplace = $wpjobportal_show_workplace ? wpjobportal::wpjobportal_getVariableValue($wpjobportal_job_listing_fields['workplace_type']) : '';
        $wpjobportal_label_location  = $wpjobportal_show_location ? wpjobportal::wpjobportal_getVariableValue($wpjobportal_job_listing_fields['city']) : '';
        $wpjobportal_label_tags      = $wpjobportal_show_tags ? wpjobportal::wpjobportal_getVariableValue($wpjobportal_job_listing_fields['tags']) : '';

        // Build Dynamic Table Headers
        $html = '<table width="100%" cellpadding="10px" cellspacing="0" style="border-collapse: collapse; font-family: sans-serif; font-size: 14px;">
                    <tr>
                        <th style="text-align: left; border-bottom: 1px solid #ddd; padding: 8px;">' . esc_html($wpjobportal_label_title) . '</th>';

        if ($wpjobportal_show_comp_name) {
            $html .= '<th style="text-align: left; border-bottom: 1px solid #ddd; padding: 8px;">' . esc_html__('Company', 'wp-job-portal') . '</th>';
        }
        if ($wpjobportal_show_category) {
            $html .= '<th style="text-align: left; border-bottom: 1px solid #ddd; padding: 8px;">' . esc_html($wpjobportal_label_category) . '</th>';
        }
        if ($wpjobportal_show_type) {
            $html .= '<th style="text-align: left; border-bottom: 1px solid #ddd; padding: 8px;">' . esc_html($wpjobportal_label_type) . '</th>';
        }
        if ($wpjobportal_show_workplace) {
            $html .= '<th style="text-align: left; border-bottom: 1px solid #ddd; padding: 8px;">' . esc_html($wpjobportal_label_workplace) . '</th>';
        }
        if ($wpjobportal_show_location) {
            $html .= '<th style="text-align: left; border-bottom: 1px solid #ddd; padding: 8px;">' . esc_html($wpjobportal_label_location) . '</th>';
        }
        if ($wpjobportal_show_tags) {
            $html .= '<th style="text-align: left; border-bottom: 1px solid #ddd; padding: 8px;">' . esc_html($wpjobportal_label_tags) . '</th>';
        }

        $html .= '</tr>';

        // Populate Data Rows
        foreach ($jobs as $job) {
            $wpjobportal_id_for_url = isset($job->jobaliasid) ? $job->jobaliasid : $job->id;
            $path = wpjobportal::wpjobportal_makeUrl(array(
                'wpjobportalme' => 'job',
                'wpjobportallt' => 'viewjob',
                'wpjobportalid' => $wpjobportal_id_for_url
            ));

            $urgentBadge = '';
            if ($wpjobportal_show_urgent && isset($job->is_urgent) && $job->is_urgent == 1) {
                $urgent_label = wpjobportal::wpjobportal_getVariableValue($wpjobportal_job_listing_fields['is_urgent']);
                $urgentBadge = ' <span style="color: #d9534f; font-size: 11px; text-transform: uppercase; border: 1px solid #d9534f; padding: 1px 4px; border-radius: 3px;">' . esc_html($urgent_label) . '</span>';
            }

            $html .= '<tr>';

            $html .= '<td style="border-bottom: 1px solid #eee; padding: 8px;">
                        <a href="' . esc_url($path) . '" target="_blank" style="text-decoration: none; color: #0073aa;">' . esc_html($job->title) . '</a>' . $urgentBadge . '
                      </td>';

            if ($wpjobportal_show_comp_name) {
                $companyName = !empty($job->companyname) ? $job->companyname : '-';
                $html .= '<td style="border-bottom: 1px solid #eee; padding: 8px;">' . esc_html($companyName) . '</td>';
            }

            if ($wpjobportal_show_category) {
                $category = !empty($job->cat_title) ? $job->cat_title : (!empty($job->categorytitle) ? $job->categorytitle : (!empty($job->jobcategory) ? $job->jobcategory : '-'));
                $html .= '<td style="border-bottom: 1px solid #eee; padding: 8px;">' . esc_html(wpjobportal::wpjobportal_getVariableValue($category)) . '</td>';
            }

            if ($wpjobportal_show_type) {
                $jobType = !empty($job->jobtypetitle) ? $job->jobtypetitle : '-';
                $html .= '<td style="border-bottom: 1px solid #eee; padding: 8px;">' . esc_html(wpjobportal::wpjobportal_getVariableValue($jobType)) . '</td>';
            }

            if ($wpjobportal_show_workplace) {
                $workplace = '-';
                if (isset($job->workplace_type) && $job->workplace_type != 0) {
                    switch ($job->workplace_type) {
                        case '1': $workplace = esc_html__('On-site', 'wp-job-portal'); break;
                        case '2': $workplace = esc_html__('Hybrid', 'wp-job-portal'); break;
                        case '3': $workplace = esc_html__('Remote', 'wp-job-portal'); break;
                    }
                }
                $html .= '<td style="border-bottom: 1px solid #eee; padding: 8px;">' . esc_html($workplace) . '</td>';
            }

            if ($wpjobportal_show_location) {
                $location = '-';
                if (!empty($job->location)) {
                    $location = $job->location;
                } elseif (!empty($job->city)) {
                    $location = WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($job->city);
                } elseif (!empty($job->mcity)) {
                    $location = $job->mcity;
                }
                $html .= '<td style="border-bottom: 1px solid #eee; padding: 8px;">' . esc_html(wp_strip_all_tags($location)) . '</td>';
            }

            if ($wpjobportal_show_tags) {
                $tags = '-';
                $raw_tags = isset($job->jobtags) ? $job->jobtags : (isset($job->tags) ? $job->tags : '');
                if (!empty($raw_tags)) {
                    $tagArray = array_map('trim', explode(',', $raw_tags));
                    $tags = implode(', ', array_filter($tagArray));
                }
                $html .= '<td style="border-bottom: 1px solid #eee; padding: 8px;">' . esc_html($tags) . '</td>';
            }

            $html .= '</tr>';
        }

        $html .= '</table>';
        return $html;
    }

    function getMessagekey(){
        $key = 'jobalert';if(wpjobportal::$_common->wpjp_isadmin()){$key = 'admin_'.$key;}return $key;
    }
}
?>
