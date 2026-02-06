<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALactivitylogModel {

    var $_siteurl = null;

    function __construct() {

        $this->_siteurl = site_url();
    }

    function storeActivity($flag, $wpjobportal_tablename, $columns, $wpjobportal_id = null) {
        if ($wpjobportal_id == null) {
            $wpjobportal_id = $columns['id'];
        }
        if (!is_numeric($wpjobportal_id))
            return false;

        $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        $wpjobportal_uid = ($wpjobportal_uid != null) ? $wpjobportal_uid : 0;
        $wpjobportal_text = $this->getActivityDescription($flag, $wpjobportal_tablename, $wpjobportal_uid, $columns, $wpjobportal_id);

        if ($wpjobportal_text == false) {
            return;
        }
        $wpjobportal_desc = $wpjobportal_text[1];
        $wpjobportal_name = $wpjobportal_text[0];
        $created = gmdate("Y-m-d H:i:s");

        $wpjobportal_data = array();
        $wpjobportal_data['description'] = $wpjobportal_desc;
        $wpjobportal_data['referencefor'] = $wpjobportal_name;
        $wpjobportal_data['referenceid'] = $wpjobportal_id;
        $wpjobportal_data['uid'] = $wpjobportal_uid;
        $wpjobportal_data['created'] = $created;

        wpjobportal::$_db->insert(wpjobportal::$_db->prefix.'wj_portal_activitylog',$wpjobportal_data);
        return WPJOBPORTAL_SAVED;
    }

    function storeActivityLogForActionDelete($wpjobportal_text, $wpjobportal_id) {
        if (!is_numeric($wpjobportal_id))
            return false;
        if ($wpjobportal_text == false)
            return;
        $wpjobportal_name = $wpjobportal_text[0];
        $wpjobportal_desc = $wpjobportal_text[1];
        $wpjobportal_uid = $wpjobportal_text[2];
        $wpjobportal_uid = $wpjobportal_uid != null ? $wpjobportal_uid : 0;
        $created = gmdate("Y-m-d H:i:s");

        $wpjobportal_data = array();
        $wpjobportal_data['description'] = $wpjobportal_desc;
        $wpjobportal_data['referencefor'] = $wpjobportal_name;
        $wpjobportal_data['referenceid'] = $wpjobportal_id;
        $wpjobportal_data['uid'] = $wpjobportal_uid;
        $wpjobportal_data['created'] = $created;


        wpjobportal::$_db->insert(wpjobportal::$_db->prefix.'wj_portal_activitylog',$wpjobportal_data);
        return WPJOBPORTAL_SAVED;
    }

    function sorting() {
        $wpjobportal_pagenum = WPJOBPORTALrequest::getVar('pagenum');
        wpjobportal::$_data['sorton'] = wpjobportal::$_search['jobs']['sorton'];
        wpjobportal::$_data['sortby'] = wpjobportal::$_search['jobs']['sortby'];

        switch (wpjobportal::$_data['sorton']) {
            case 1: // created
                wpjobportal::$_data['sorting'] = ' act.id ';
                break;
            case 2: // company name
                wpjobportal::$_data['sorting'] = ' u.first_name ';
                break;
            case 3: // category
                wpjobportal::$_data['sorting'] = ' act.referencefor ';
                break;
            case 4: // location
            default: // location
                wpjobportal::$_data['sorting'] = ' act.created ';
                break;
        }
        if (wpjobportal::$_data['sortby'] == 1) {
            wpjobportal::$_data['sorting'] .= ' ASC ';
        } else {
            wpjobportal::$_data['sorting'] .= ' DESC ';
        }
        wpjobportal::$_data['combosort'] = wpjobportal::$_data['sorton'];
    }

    function getAllActivities() {
        $this->sorting();

        $wpjobportal_data = WPJOBPORTALrequest::getVar('filter');

        $wpjobportal_string = '';
        $wpjobportal_comma = '';
        if (isset($wpjobportal_data['age'])) {
            $wpjobportal_string .= $wpjobportal_comma . '"ages"';
            $wpjobportal_comma = ',';
        }
        if (isset($wpjobportal_data['job'])) {
            $wpjobportal_string .= $wpjobportal_comma . '"jobs"';
            $wpjobportal_comma = ',';
        }
        if (isset($wpjobportal_data['coverletter'])) {
            $wpjobportal_string .= $wpjobportal_comma . '"coverletters"';
            $wpjobportal_comma = ',';
        }
        if (isset($wpjobportal_data['careerlevel'])) {
            $wpjobportal_string .= $wpjobportal_comma . '"careerlevels"';
            $wpjobportal_comma = ',';
        }
        if (isset($wpjobportal_data['city'])) {
            $wpjobportal_string .= $wpjobportal_comma . '"cities"';
            $wpjobportal_comma = ',';
        }
        if (isset($wpjobportal_data['state'])) {
            $wpjobportal_string .= $wpjobportal_comma . '"states"';
            $wpjobportal_comma = ',';
        }
        if (isset($wpjobportal_data['country'])) {
            $wpjobportal_string .= $wpjobportal_comma . '"countries"';
            $wpjobportal_comma = ',';
        }
        if (isset($wpjobportal_data['category'])) {
            $wpjobportal_string .= $wpjobportal_comma . '"categories"';
            $wpjobportal_comma = ',';
        }
        if (isset($wpjobportal_data['currency'])) {
            $wpjobportal_string .= $wpjobportal_comma . '"currencies"';
            $wpjobportal_comma = ',';
        }
        if (isset($wpjobportal_data['customfield'])) {
            $wpjobportal_string .= $wpjobportal_comma . '"userfields"';
            $wpjobportal_comma = ',';
        }
        if (isset($wpjobportal_data['emailtemplate'])) {
            $wpjobportal_string .= $wpjobportal_comma . '"emailtemplates"';
            $wpjobportal_comma = ',';
        }
        if (isset($wpjobportal_data['experience'])) {
            $wpjobportal_string .= $wpjobportal_comma . '"experiences"';
            $wpjobportal_comma = ',';
        }
        if (isset($wpjobportal_data['highesteducation'])) {
            $wpjobportal_string .= $wpjobportal_comma . '"heighesteducation"';
            $wpjobportal_comma = ',';
        }
        if (isset($wpjobportal_data['company'])) {
            $wpjobportal_string .= $wpjobportal_comma . '"companies"';
            $wpjobportal_comma = ',';
        }
        if (isset($wpjobportal_data['jobstatus'])) {
            $wpjobportal_string .= $wpjobportal_comma . '"jobstatus"';
            $wpjobportal_comma = ',';
        }
        if (isset($wpjobportal_data['jobtype'])) {
            $wpjobportal_string .= $wpjobportal_comma . '"jobtypes"';
            $wpjobportal_comma = ',';
        }
        if (isset($wpjobportal_data['salaryrangetype'])) {
            $wpjobportal_string .= $wpjobportal_comma . '"salaryrangetypes"';
            $wpjobportal_comma = ',';
        }
        if (isset($wpjobportal_data['salaryrange'])) {
            $wpjobportal_string .= $wpjobportal_comma . '"salaryrange"';
            $wpjobportal_comma = ',';
        }
        if (isset($wpjobportal_data['shift'])) {
            $wpjobportal_string .= $wpjobportal_comma . '"shifts"';
            $wpjobportal_comma = ',';
        }
        if (isset($wpjobportal_data['resume'])) {
            $wpjobportal_string .= $wpjobportal_comma . '"resume"';
            $wpjobportal_comma = ',';
        }
        if (isset($wpjobportal_data['resumesearches'])) {
            $wpjobportal_string .= $wpjobportal_comma . '"resumesearches"';
            $wpjobportal_comma = ',';
        }
        if (isset($wpjobportal_data['jobsearch'])) {
            $wpjobportal_string .= $wpjobportal_comma . '"jobsearches"';
            $wpjobportal_comma = ',';
        }

        if (isset($wpjobportal_data['jobapply'])) {
            $wpjobportal_string .= $wpjobportal_comma . '"jobapply"';
            $wpjobportal_comma = ',';
        }

        if (isset($wpjobportal_data['department'])) {
            $wpjobportal_string .= $wpjobportal_comma . '"department"';
            $wpjobportal_comma = ',';
        }

        $wpjobportal_inquery = " ";

        $wpjobportal_searchsubmit = WPJOBPORTALrequest::getVar('searchsubmit');
        if(!empty($wpjobportal_searchsubmit) AND $wpjobportal_searchsubmit == 1){
            // $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_config`
            //     set configvalue = '".$wpjobportal_string."' WHERE configname = 'activity_log_filter'";
            // wpjobportal::$_db->query($query);
            update_option('wp_job_portal_activity_log_filter',wp_json_encode($wpjobportal_string));
        }

        //$wpjobportal_activity_log_filter = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('activity_log_filter');
        $wpjobportal_activity_log_filter = '';
        $wpjobportal_option_value = get_option('wp_job_portal_activity_log_filter');
        if($wpjobportal_option_value !=''){
            $wpjobportal_activity_log_filter = json_decode($wpjobportal_option_value);
        }


        if ($wpjobportal_string != '') {
            $wpjobportal_inquery = "WHERE act.referencefor IN (".$wpjobportal_string.") ";
        } else if ($wpjobportal_activity_log_filter != null) {

            $wpjobportal_data = array();
            $wpjobportal_string = $wpjobportal_activity_log_filter;
            $wpjobportal_inquery = "WHERE act.referencefor IN (".$wpjobportal_string.") ";
            //showing check boxes checked
            $wpjobportal_array = wpjobportalphplib::wpJP_explode(',', $wpjobportal_string);
            foreach ($wpjobportal_array as $wpjobportal_var) {
                switch ($wpjobportal_var) {
                    case '"ages"':
                        $wpjobportal_data['age'] = 1;
                        break;
                    case '"careerlevels"':
                        $wpjobportal_data['careerlevel'] = 1;
                        break;
                    case '"coverletters"':
                        $wpjobportal_data['coverletter'] = 1;
                        break;
                    case '"currencies"':
                        $wpjobportal_data['currency'] = 1;
                        break;
                    case '"experiences"':
                        $wpjobportal_data['experience'] = 1;
                        break;
                    case '"heighesteducation"':
                        $wpjobportal_data['highesteducation'] = 1;
                        break;
                    case '"jobs"':
                        $wpjobportal_data['job'] = 1;
                        break;
                    case '"jobstatus"':
                        $wpjobportal_data['jobstatus'] = 1;
                        break;
                    case '"jobtypes"':
                        $wpjobportal_data['jobtype'] = 1;
                        break;
                    case '"salaryrangetypes"':
                        $wpjobportal_data['salaryrangetype'] = 1;
                        break;
                    case '"userfields"':
                        $wpjobportal_data['customfield'] = 1;
                        break;
                    case '"shifts"':
                        $wpjobportal_data['shift'] = 1;
                        break;
                    case '"emailtemplates"':
                        $wpjobportal_data['emailtemplate'] = 1;
                        break;
                    case '"companies"':
                        $wpjobportal_data['company'] = 1;
                        break;
                    case '"countries"':
                        $wpjobportal_data['country'] = 1;
                        break;
                    case '"states"':
                        $wpjobportal_data['state'] = 1;
                        break;
                    case '"department"':
                        $wpjobportal_data['department'] = 1;
                        break;
                    case '"cities"':
                        $wpjobportal_data['city'] = 1;
                        break;
                    case '"resume"':
                        $wpjobportal_data['resume'] = 1;
                        break;
                    case '"jobsearches"':
                        $wpjobportal_data['jobsearch'] = 1;
                        break;
                    case '"resumesearches"':
                        $wpjobportal_data['resumesearches'] = 1;
                        break;
                    case '"categories"':
                        $wpjobportal_data['category'] = 1;
                        break;
                    case '"salaryrange"':
                        $wpjobportal_data['salaryrange'] = 1;
                        break;
                    case '"jobapply"':
                        $wpjobportal_data['jobapply'] = 1;
                        break;
                }
            }
        }

        wpjobportal::$_data['filter']['age'] = isset($wpjobportal_data['age']) ? 1 : 0;
        wpjobportal::$_data['filter']['job'] = isset($wpjobportal_data['job']) ? 1 : 0;
        wpjobportal::$_data['filter']['company'] = isset($wpjobportal_data['company']) ? 1 : 0;
        wpjobportal::$_data['filter']['careerlevel'] = isset($wpjobportal_data['careerlevel']) ? 1 : 0;
        wpjobportal::$_data['filter']['city'] = isset($wpjobportal_data['city']) ? 1 : 0;
        wpjobportal::$_data['filter']['state'] = isset($wpjobportal_data['state']) ? 1 : 0;
        wpjobportal::$_data['filter']['country'] = isset($wpjobportal_data['country']) ? 1 : 0;
        wpjobportal::$_data['filter']['category'] = isset($wpjobportal_data['category']) ? 1 : 0;
        wpjobportal::$_data['filter']['currency'] = isset($wpjobportal_data['currency']) ? 1 : 0;
        wpjobportal::$_data['filter']['customfield'] = isset($wpjobportal_data['customfield']) ? 1 : 0;
        wpjobportal::$_data['filter']['emailtemplate'] = isset($wpjobportal_data['emailtemplate']) ? 1 : 0;
        wpjobportal::$_data['filter']['experience'] = isset($wpjobportal_data['experience']) ? 1 : 0;
        wpjobportal::$_data['filter']['highesteducation'] = isset($wpjobportal_data['highesteducation']) ? 1 : 0;
        wpjobportal::$_data['filter']['coverletter'] = isset($wpjobportal_data['coverletter']) ? 1 : 0;
        wpjobportal::$_data['filter']['jobstatus'] = isset($wpjobportal_data['jobstatus']) ? 1 : 0;
        wpjobportal::$_data['filter']['jobtype'] = isset($wpjobportal_data['jobtype']) ? 1 : 0;
        wpjobportal::$_data['filter']['salaryrangetype'] = isset($wpjobportal_data['salaryrangetype']) ? 1 : 0;
        wpjobportal::$_data['filter']['salaryrange'] = isset($wpjobportal_data['salaryrange']) ? 1 : 0;
        wpjobportal::$_data['filter']['shift'] = isset($wpjobportal_data['shift']) ? 1 : 0;
        wpjobportal::$_data['filter']['department'] = isset($wpjobportal_data['department']) ? 1 : 0;
        wpjobportal::$_data['filter']['resume'] = isset($wpjobportal_data['resume']) ? 1 : 0;
        wpjobportal::$_data['filter']['resumesearches'] = isset($wpjobportal_data['resumesearches']) ? 1 : 0;
        wpjobportal::$_data['filter']['jobsearch'] = isset($wpjobportal_data['jobsearch']) ? 1 : 0;
        wpjobportal::$_data['filter']['jobapply'] = isset($wpjobportal_data['jobapply']) ? 1 : 0;

        $query = "SELECT COUNT(act.id)
        FROM `" . wpjobportal::$_db->prefix . "wj_portal_activitylog` AS act
        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_users` AS u ON u.id = act.uid " . $wpjobportal_inquery;
        $wpjobportal_total = wpjobportaldb::get_var($query);
        wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($wpjobportal_total);

        $query = "SELECT act.description,act.created,act.id,act.referencefor,u.first_name,u.last_name
        FROM `" . wpjobportal::$_db->prefix . "wj_portal_activitylog` AS act
        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_users` AS u ON u.id = act.uid " . $wpjobportal_inquery;
        $query .= "ORDER BY " . wpjobportal::$_data['sorting'];
        $query .=" LIMIT " . WPJOBPORTALpagination::$_offset . "," . WPJOBPORTALpagination::$_limit;
        $wpjobportal_result = wpjobportal::$_db->get_results($query);

        wpjobportal::$_data[0] = $wpjobportal_result;
        return;
    }

    function getEntityNameOrTitle($wpjobportal_id, $wpjobportal_text, $wpjobportal_tablename) {
        if (!is_numeric($wpjobportal_id))
            return false;
        if ($wpjobportal_text == '' OR $wpjobportal_tablename == '')
            return false;

        if(!strstr($wpjobportal_tablename, "wj_portal_") ){
            return false;
        }
        switch ($wpjobportal_text) {
            case 'title':
            case 'templatefor':
            case 'name':
            case "CONCAT(first_name, ' ', last_name) AS Name":
            case 'searchname':
            case 'cat_title':
            case 'rangestart':
                $query = "SELECT $wpjobportal_text FROM `$wpjobportal_tablename` WHERE id = " . esc_sql($wpjobportal_id);
                $wpjobportal_result = wpjobportal::$_db->get_var($query);
                return $wpjobportal_result;
            break;

        }
        return false;

    }

    function getJobTitleFromid($wpjobportal_id) {
        if (!is_numeric($wpjobportal_id))
            return false;
        $query = "SELECT title FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` WHERE id =" . esc_sql($wpjobportal_id);
        $wpjobportal_result = wpjobportal::$_db->get_var($query);
        return $wpjobportal_result;
    }

    function getReusmeTitleFromid($wpjobportal_id) {
        if (!is_numeric($wpjobportal_id))
            return false;
        $query = "SELECT CONCAT(first_name, ' ', last_name) AS Name FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` WHERE id = " . esc_sql($wpjobportal_id);
        $wpjobportal_result = wpjobportal::$_db->get_var($query);
        return $wpjobportal_result;
    }

    function getEntityNameOrTitleForJobApply($wpjobportal_id, $wpjobportal_tablename) {
        if (!is_numeric($wpjobportal_id))
            return false;
        if ($wpjobportal_tablename == '')
            return false;
        $query = "SELECT cvid,jobid FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` WHERE id = " . esc_sql($wpjobportal_id);
        $wpjobportal_result = wpjobportal::$_db->get_row($query);
        $wpjobportal_data = array();
        $wpjobportal_data[0] = $wpjobportal_result->jobid;
        $wpjobportal_data[1] = $this->getJobTitleFromid($wpjobportal_result->jobid);
        $wpjobportal_data[2] = $wpjobportal_result->cvid;
        $wpjobportal_data[3] = $this->getReusmeTitleFromid($wpjobportal_result->cvid);
        return $wpjobportal_data;
    }

    function getActivityDescription($flag, $wpjobportal_tablename, $wpjobportal_uid, $columns, $wpjobportal_id) {
        $wpjobportal_array = wpjobportalphplib::wpJP_explode('_', $wpjobportal_tablename);
        if (!is_numeric($wpjobportal_uid))
            return false;

        $wpjobportal_name = $wpjobportal_array[count($wpjobportal_array) - 1];
        $wpjobportal_target = "_blank";
        switch ($wpjobportal_name) {
            //all the tables which have title as column
            case 'ages':
                $wpjobportal_entityname = esc_html(__('Age', 'wp-job-portal'));
                $wpjobportal_linktext = $flag == 1 ? $columns['title'] : $this->getEntityNameOrTitle($wpjobportal_id, 'title', $wpjobportal_tablename);
                $wpjobportal_path = "?page=wpjobportal_age&wpjobportallt=formages&wpjobportalid=$wpjobportal_id";
                $wpjobportal_html = "<a href=" . esc_url($wpjobportal_path) . " target='".esc_attr($wpjobportal_target)."''><strong>" . esc_html($wpjobportal_linktext) . "</strong></a>";
                break;
            case 'careerlevels':
                $wpjobportal_entityname = esc_html(__('Career Level', 'wp-job-portal'));
                $wpjobportal_linktext = $flag == 1 ? $columns['title'] : $this->getEntityNameOrTitle($wpjobportal_id, 'title', $wpjobportal_tablename);
                $wpjobportal_path = "?page=wpjobportal_careerlevel&wpjobportallt=formcareerlevels&wpjobportalid=$wpjobportal_id";
                $wpjobportal_html = "<a href=" . esc_url($wpjobportal_path) . " target='".esc_attr($wpjobportal_target)."''><strong>" . esc_html($wpjobportal_linktext) . "</strong></a>";
                break;
            case 'coverletters':
                $wpjobportal_entityname = esc_html(__('Cover Letter', 'wp-job-portal'));
                $wpjobportal_linktext = $flag == 1 ? $columns['title'] : $this->getEntityNameOrTitle($wpjobportal_id, 'title', $wpjobportal_tablename);
                $wpjobportal_path = "?page=wpjobportal_coverletter&wpjobportallt=formcoverletter&wpjobportalid=$wpjobportal_id";
                $wpjobportal_html = "<a href=" . esc_url($wpjobportal_path) . " target='".esc_attr($wpjobportal_target)."''><strong>" . esc_html($wpjobportal_linktext) . "</strong></a>";
                break;
            case 'currencies':
                $wpjobportal_entityname = esc_html(__('Currency', 'wp-job-portal'));
                $wpjobportal_linktext = $flag == 1 ? $columns['title'] : $this->getEntityNameOrTitle($wpjobportal_id, 'title', $wpjobportal_tablename);
                $wpjobportal_path = "?page=wpjobportal_currency&wpjobportallt=formcurrency&wpjobportalid=$wpjobportal_id";
                $wpjobportal_html = "<a href=" . esc_url($wpjobportal_path) . " target='".esc_attr($wpjobportal_target)."''><strong>" . esc_html($wpjobportal_linktext) . "</strong></a>";
                break;
            case 'experiences':
                $wpjobportal_entityname = esc_html(__('Experience', 'wp-job-portal'));
                $wpjobportal_linktext = $flag == 1 ? $columns['title'] : $this->getEntityNameOrTitle($wpjobportal_id, 'title', $wpjobportal_tablename);
                $wpjobportal_path = "?page=wpjobportal_experience&wpjobportallt=formexperience&wpjobportalid=$wpjobportal_id";
                $wpjobportal_html = "<a href=" . esc_url($wpjobportal_path) . " target='".esc_attr($wpjobportal_target)."''><strong>" . esc_html($wpjobportal_linktext) . "</strong></a>";
                break;
            case 'heighesteducation':
                $wpjobportal_entityname = esc_html(__('Education', 'wp-job-portal'));
                $wpjobportal_linktext = $flag == 1 ? $columns['title'] : $this->getEntityNameOrTitle($wpjobportal_id, 'title', $wpjobportal_tablename);
                $wpjobportal_path = "?page=wpjobportal_highesteducation&wpjobportallt=formhighesteducation&wpjobportalid=$wpjobportal_id";
                $wpjobportal_html = "<a href=" . esc_url($wpjobportal_path) . " target='".esc_attr($wpjobportal_target)."''><strong>" . esc_html($wpjobportal_linktext) . "</strong></a>";
                break;
            case 'jobs':
                $wpjobportal_entityname = esc_html(__('Job', 'wp-job-portal'));
                $wpjobportal_linktext = $flag == 1 ? $columns['title'] : $this->getEntityNameOrTitle($wpjobportal_id, 'title', $wpjobportal_tablename);
                $wpjobportal_path = "?page=wpjobportal_job&wpjobportallt=formjob&wpjobportalid=$wpjobportal_id";
                $wpjobportal_html = "<a href=" . esc_url($wpjobportal_path) . " target='".esc_attr($wpjobportal_target)."''><strong>" . esc_html($wpjobportal_linktext) . "</strong></a>";
                break;
            case 'jobstatus':
                $wpjobportal_entityname = esc_html(__('Job Status', 'wp-job-portal'));
                $wpjobportal_linktext = $flag == 1 ? $columns['title'] : $this->getEntityNameOrTitle($wpjobportal_id, 'title', $wpjobportal_tablename);
                $wpjobportal_path = "?page=wpjobportal_jobstatus&wpjobportallt=formjobstatus&wpjobportalid=$wpjobportal_id";
                $wpjobportal_html = "<a href=" . esc_url($wpjobportal_path) . " target='".esc_attr($wpjobportal_target)."''><strong>" . esc_html($wpjobportal_linktext) . "</strong></a>";
                break;
            case 'jobtypes':
                $wpjobportal_entityname = esc_html(__('Job Type', 'wp-job-portal'));
                $wpjobportal_linktext = $flag == 1 ? $columns['title'] : $this->getEntityNameOrTitle($wpjobportal_id, 'title', $wpjobportal_tablename);
                $wpjobportal_path = "?page=wpjobportal_jobtype&wpjobportallt=formjobtype&wpjobportalid=$wpjobportal_id";
                $wpjobportal_html = "<a href=" . esc_url($wpjobportal_path) . " target='".esc_attr($wpjobportal_target)."''><strong>" . esc_html($wpjobportal_linktext) . "</strong></a>";
                break;
            case 'salaryrangetypes':
                $wpjobportal_entityname = esc_html(__('Salary Range Type', 'wp-job-portal'));
                $wpjobportal_linktext = $flag == 1 ? $columns['title'] : $this->getEntityNameOrTitle($wpjobportal_id, 'title', $wpjobportal_tablename);
                $wpjobportal_path = "?page=wpjobportal_salaryrangetype&wpjobportallt=formsalaryrangetype&wpjobportalid=$wpjobportal_id";
                $wpjobportal_html = "<a href=" . esc_url($wpjobportal_path) . " target='".esc_attr($wpjobportal_target)."''><strong>" . esc_html($wpjobportal_linktext) . "</strong></a>";
                break;
            case 'userfields':
                $wpjobportal_entityname = esc_html(__('Salary Range Type', 'wp-job-portal'));
                $wpjobportal_linktext = $flag == 1 ? $columns['title'] : $this->getEntityNameOrTitle($wpjobportal_id, 'title', $wpjobportal_tablename);
                $wpjobportal_path = "?page=wpjobportal_customfield&wpjobportallt=formcustomfield&wpjobportalid=$wpjobportal_id";
                $wpjobportal_html = "<a href=" . esc_url($wpjobportal_path) . " target='".esc_attr($wpjobportal_target)."''><strong>" . esc_html($wpjobportal_linktext) . "</strong></a>";
                break;
            case 'shifts':
                $wpjobportal_entityname = esc_html(__('Shift', 'wp-job-portal'));
                $wpjobportal_linktext = $flag == 1 ? $columns['title'] : $this->getEntityNameOrTitle($wpjobportal_id, 'title', $wpjobportal_tablename);
                $wpjobportal_path = "?page=wpjobportal_shift&wpjobportallt=formshift&wpjobportalid=$wpjobportal_id";
                $wpjobportal_html = "<a href=" . esc_url($wpjobportal_path) . " target='".esc_attr($wpjobportal_target)."''><strong>" . esc_html($wpjobportal_linktext) . "</strong></a>";
                break;
            case 'emailtemplates':
                $wpjobportal_entityname = esc_html(__('Email Template', 'wp-job-portal'));
                $wpjobportal_linktext = $flag == 1 ? $columns['templatefor'] : $this->getEntityNameOrTitle($wpjobportal_id, 'templatefor', $wpjobportal_tablename);
                $wpjobportal_path = "?page=wpjobportal_emailtemplate&wpjobportallt=formemailtemplte&wpjobportalid=$wpjobportal_id";
                $wpjobportal_html = "<strong>" . esc_html($wpjobportal_linktext) . "</strong>";
                //$wpjobportal_html = "<a href=" . esc_url($wpjobportal_path) . " target='".esc_attr($wpjobportal_target)."''><strong>" . esc_html($wpjobportal_linktext) . "</strong></a>";
                break;
            //tables that have name as column
            case 'companies':
                $wpjobportal_entityname = esc_html(__('Company', 'wp-job-portal'));
                $wpjobportal_linktext = $flag == 1 ? $columns['name'] : $this->getEntityNameOrTitle($wpjobportal_id, 'name', $wpjobportal_tablename);
                $wpjobportal_path = "?page=wpjobportal_company&wpjobportallt=formcompany&wpjobportalid=$wpjobportal_id";
                $wpjobportal_html = "<a href=" . esc_url($wpjobportal_path) . " target='".esc_attr($wpjobportal_target)."''><strong>" . esc_html($wpjobportal_linktext) . "</strong></a>";
                break;
            case 'countries':
                $wpjobportal_entityname = esc_html(__('Country', 'wp-job-portal'));
                $wpjobportal_linktext = $flag == 1 ? $columns['name'] : $this->getEntityNameOrTitle($wpjobportal_id, 'name', $wpjobportal_tablename);
                $wpjobportal_path = "?page=wpjobportal_country&wpjobportallt=formcountry&wpjobportalid=$wpjobportal_id";
                $wpjobportal_html = "<a href=" . esc_url($wpjobportal_path) . " target='".esc_attr($wpjobportal_target)."''><strong>" . esc_html($wpjobportal_linktext) . "</strong></a>";
                break;
            case 'folders':
                $wpjobportal_entityname = esc_html(__('Folder', 'wp-job-portal'));
                $wpjobportal_linktext = $flag == 1 ? $columns['name'] : $this->getEntityNameOrTitle($wpjobportal_id, 'name', $wpjobportal_tablename);
                $wpjobportal_path = "?page=wpjobportal_folder&wpjobportallt=formfolder&wpjobportalid=$wpjobportal_id";
                $wpjobportal_html = "<a href=" . esc_url($wpjobportal_path) . " target='".esc_attr($wpjobportal_target)."''><strong>" . esc_html($wpjobportal_linktext) . "</strong></a>";
                break;
            case 'states':
                $wpjobportal_entityname = esc_html(__('Department', 'wp-job-portal'));
                $wpjobportal_linktext = $flag == 1 ? $columns['name'] : $this->getEntityNameOrTitle($wpjobportal_id, 'name', $wpjobportal_tablename);
                $wpjobportal_path = "?page=wpjobportal_state&wpjobportallt=formstate&wpjobportalid=$wpjobportal_id";
                $wpjobportal_html = "<a href=" . esc_url($wpjobportal_path) . " target='".esc_attr($wpjobportal_target)."''><strong>" . esc_html($wpjobportal_linktext) . "</strong></a>";
                break;
            case 'departments':
                $wpjobportal_entityname = esc_html(__('Department', 'wp-job-portal'));
                $wpjobportal_linktext = $flag == 1 ? $columns['name'] : $this->getEntityNameOrTitle($wpjobportal_id, 'name', $wpjobportal_tablename);
                $wpjobportal_path = "?page=wpjobportal_departments&wpjobportallt=formdepartment&wpjobportalid=$wpjobportal_id";
                $wpjobportal_html = "<a href=" . esc_url($wpjobportal_path) . " target='".esc_attr($wpjobportal_target)."''><strong>" . esc_html($wpjobportal_linktext) . "</strong></a>";
                break;
            case 'cities':
                $wpjobportal_entityname = esc_html(__('City', 'wp-job-portal'));
                $wpjobportal_linktext = $flag == 1 ? $columns['name'] : $this->getEntityNameOrTitle($wpjobportal_id, 'name', $wpjobportal_tablename);
                $wpjobportal_path = "?page=wpjobportal_city&wpjobportallt=formcity&wpjobportalid=$wpjobportal_id";
                $wpjobportal_html = "<a href=" . esc_url($wpjobportal_path) . " target='".esc_attr($wpjobportal_target)."''><strong>" . esc_html($wpjobportal_linktext) . "</strong></a>";
                break;
            //speceial case
            case 'resume':
                $wpjobportal_entityname = esc_html(__('Resume', 'wp-job-portal'));
                $wpjobportal_linktext = $this->getEntityNameOrTitle($wpjobportal_id, "CONCAT(first_name, ' ', last_name) AS Name", wpjobportal::$_db->prefix.'wj_portal_resume');
                $wpjobportal_path = "?page=wpjobportal_resume&wpjobportallt=formresume&wpjobportalid=$wpjobportal_id";
                $wpjobportal_html = "<a href=" . esc_url($wpjobportal_path) . " target='".esc_attr($wpjobportal_target)."''><strong>" . esc_html($wpjobportal_linktext) . "</strong></a>";
                break;
            case 'jobsearches':
                $wpjobportal_entityname = esc_html(__('Job Search', 'wp-job-portal'));
                $wpjobportal_linktext = $flag == 1 ? $columns['searchname'] : $this->getEntityNameOrTitle($wpjobportal_id, 'searchname', $wpjobportal_tablename);
                //$wpjobportal_path = "?page=wpjobportal_jobsearch"; // layout does exsist
                $wpjobportal_html = "<strong>" . esc_html($wpjobportal_linktext) . "</strong>";
                break;
            case 'resumesearches':
                $wpjobportal_entityname = esc_html(__('Resume Search', 'wp-job-portal'));
                $wpjobportal_linktext = $flag == 1 ? $columns['searchname'] : $this->getEntityNameOrTitle($wpjobportal_id, 'searchname', $wpjobportal_tablename);
                // $wpjobportal_path = "?page=wpjobportal_resumesearch"; // layout does not exsist
                $wpjobportal_html = "<strong>" . esc_html($wpjobportal_linktext) . "</strong>";
                break;
            case 'categories':
                $wpjobportal_entityname = esc_html(__('Category', 'wp-job-portal'));
                $wpjobportal_linktext = $flag == 1 ? $columns['cat_title'] : $this->getEntityNameOrTitle($wpjobportal_id, 'cat_title', $wpjobportal_tablename);
                $wpjobportal_path = "?page=wpjobportal_category&wpjobportallt=formcategory&wpjobportalid=$wpjobportal_id";
                $wpjobportal_html = "<a href=" . esc_url($wpjobportal_path) . " target='".esc_attr($wpjobportal_target)."''><strong>" . esc_html($wpjobportal_linktext) . "</strong></a>";
                break;
            case 'salaryrange':
                $wpjobportal_entityname = esc_html(__('Salary Range', 'wp-job-portal'));
                $wpjobportal_linktext = $flag == 1 ? $columns['rangestart'] : $this->getEntityNameOrTitle($wpjobportal_id, 'rangestart', $wpjobportal_tablename);
                $wpjobportal_path = "?page=wpjobportal_salaryrange&wpjobportallt=formsalaryrange&wpjobportalid=$wpjobportal_id";
                $wpjobportal_html = "<a href=" . esc_url($wpjobportal_path) . " target='".esc_attr($wpjobportal_target)."''><strong>" . esc_html($wpjobportal_linktext) . "</strong></a>";
                break;
            case 'jobapply':
                $wpjobportal_entityname = esc_html(__('Applied for job', 'wp-job-portal'));
                $wpjobportal_data = $this->getEntityNameOrTitleForJobApply($wpjobportal_id, $wpjobportal_tablename);

                $wpjobportal_path1 = "?page=wpjobportal_job&wpjobportallt=formjob&wpjobportalid=$wpjobportal_data[0]";
                $wpjobportal_path2 = "?page=wpjobportal_resume&wpjobportallt=formresume&wpjobportalid=$wpjobportal_data[2]";
                $wpjobportal_html = " ( <a href=" . esc_url($wpjobportal_path1) . " target='".esc_attr($wpjobportal_target)."''><strong>" . $wpjobportal_data[1] . "</strong></a> ) ";
                $wpjobportal_html .= esc_html(__('With Resume', 'wp-job-portal'));
                $wpjobportal_html .= " ( <a href=" . esc_url($wpjobportal_path2) . " target='".esc_attr($wpjobportal_target)."''><strong>" . $wpjobportal_data[3] . "</strong></a> ) ";
                break;
            default:
                return false;
                break;
        }
        $wpjobportal_username = $this->getNameFromUid($wpjobportal_uid);
        $wpjobportal_path2 = esc_url_raw(admin_url('admin.php?page=wpjobportal_user&wpjobportallt=userdetail&id='.esc_attr($wpjobportal_uid)));
        if(current_user_can('manage_options')){
            $wpjobportal_html2 = esc_html(__('Administrator','wp-job-portal'));
        }else{
            $wpjobportal_html2 = "<a href=" . $wpjobportal_path2 . " target='".esc_attr($wpjobportal_target)."''><strong>" . esc_html($wpjobportal_username) . "</strong></a>";
        }
        $wpjobportal_entityaction = $flag == 1 ? esc_html(__("added a new", "wp-job-portal")) : esc_html(__("Edited a existing", "wp-job-portal"));
        $wpjobportal_result = array();
        $wpjobportal_result[0] = $wpjobportal_name;
        if ($wpjobportal_name == 'jobapply') {
            $wpjobportal_result[1] = "$wpjobportal_html2" . "  " . $wpjobportal_entityname . " " . $wpjobportal_html;
        } elseif ($wpjobportal_name == 'jobshortlist') {
            $wpjobportal_result[1] = "$wpjobportal_html2" . "  " . $wpjobportal_entityname . " " . $wpjobportal_html;
        } else {
            $wpjobportal_result[1] = "$wpjobportal_html2" . " " . $wpjobportal_entityaction . " " . $wpjobportal_entityname . " ( " . $wpjobportal_html . " )";
        }
        return $wpjobportal_result;
    }

    function getNameFromUid($wpjobportal_uid) {
        if (!is_numeric($wpjobportal_uid))
            return false;
        if ($wpjobportal_uid == 0) {
            return "guest";
        }
        $query = "SELECT first_name,last_name FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` WHERE id = " . esc_sql($wpjobportal_uid);
        $wpjobportal_result = wpjobportal::$_db->get_row($query);
        $wpjobportal_name = $wpjobportal_result->first_name . ' ' . $wpjobportal_result->last_name;
        return $wpjobportal_name;
    }

    function getDeleteActionDataToStore($wpjobportal_tablename, $wpjobportal_id) {
        $wpjobportal_array = wpjobportalphplib::wpJP_explode('_', $wpjobportal_tablename);
        $wpjobportal_name = $wpjobportal_array[count($wpjobportal_array) - 1];
        switch ($wpjobportal_name) {
            //all the tables which have title as column
            case 'ages':
                $wpjobportal_entityname = esc_html(__('Age', 'wp-job-portal'));
                $wpjobportal_linktext = $this->getEntityNameOrTitle($wpjobportal_id, 'title', $wpjobportal_tablename);
                break;
            case 'careerlevels':
                $wpjobportal_entityname = esc_html(__('Career Level', 'wp-job-portal'));
                $wpjobportal_linktext = $this->getEntityNameOrTitle($wpjobportal_id, 'title', $wpjobportal_tablename);
                break;
            case 'coverletters':
                $wpjobportal_entityname = esc_html(__('Cover Letter', 'wp-job-portal'));
                $wpjobportal_linktext = $this->getEntityNameOrTitle($wpjobportal_id, 'title', $wpjobportal_tablename);
                break;
            case 'currencies':
                $wpjobportal_entityname = esc_html(__('Currency', 'wp-job-portal'));
                $wpjobportal_linktext = $this->getEntityNameOrTitle($wpjobportal_id, 'title', $wpjobportal_tablename);
                break;
            case 'experiences':
                $wpjobportal_entityname = esc_html(__('Experience', 'wp-job-portal'));
                $wpjobportal_linktext = $this->getEntityNameOrTitle($wpjobportal_id, 'title', $wpjobportal_tablename);
                break;
            case 'heighesteducation':
                $wpjobportal_entityname = esc_html(__('Education', 'wp-job-portal'));
                $wpjobportal_linktext = $this->getEntityNameOrTitle($wpjobportal_id, 'title', $wpjobportal_tablename);
                break;
            case 'jobs':
                $wpjobportal_entityname = esc_html(__('Job', 'wp-job-portal'));
                $wpjobportal_linktext = $this->getEntityNameOrTitle($wpjobportal_id, 'title', $wpjobportal_tablename);
                break;
            case 'jobstatus':
                $wpjobportal_entityname = esc_html(__('Job Status', 'wp-job-portal'));
                $wpjobportal_linktext = $this->getEntityNameOrTitle($wpjobportal_id, 'title', $wpjobportal_tablename);
                break;
            case 'jobtypes':
                $wpjobportal_entityname = esc_html(__('Job Type', 'wp-job-portal'));
                $wpjobportal_linktext = $this->getEntityNameOrTitle($wpjobportal_id, 'title', $wpjobportal_tablename);
                break;
            case 'salaryrangetypes':
                $wpjobportal_entityname = esc_html(__('Salary Range Type', 'wp-job-portal'));
                $wpjobportal_linktext = $this->getEntityNameOrTitle($wpjobportal_id, 'title', $wpjobportal_tablename);
                break;
            case 'userfields':
                $wpjobportal_entityname = esc_html(__('Salary Range Type', 'wp-job-portal'));
                $wpjobportal_linktext = $this->getEntityNameOrTitle($wpjobportal_id, 'title', $wpjobportal_tablename);
                break;
            case 'shifts':
                $wpjobportal_entityname = esc_html(__('Shift', 'wp-job-portal'));
                $wpjobportal_linktext = $this->getEntityNameOrTitle($wpjobportal_id, 'title', $wpjobportal_tablename);
                break;
            case 'emailtemplates':
                $wpjobportal_entityname = esc_html(__('Email Template', 'wp-job-portal'));
                $wpjobportal_linktext = $this->getEntityNameOrTitle($wpjobportal_id, 'templatefor', $wpjobportal_tablename);
                break;
            //tables that have name as column
            case 'companies':
                $wpjobportal_entityname = esc_html(__('Company', 'wp-job-portal'));
                $wpjobportal_linktext = $this->getEntityNameOrTitle($wpjobportal_id, 'name', $wpjobportal_tablename);
                break;
            case 'countries':
                $wpjobportal_entityname = esc_html(__('Country', 'wp-job-portal'));
                $wpjobportal_linktext = $this->getEntityNameOrTitle($wpjobportal_id, 'name', $wpjobportal_tablename);
                break;
            case 'states':
                $wpjobportal_entityname = esc_html(__('State', 'wp-job-portal'));
                $wpjobportal_linktext = $this->getEntityNameOrTitle($wpjobportal_id, 'name', $wpjobportal_tablename);
                break;
            case 'departments':
                $wpjobportal_entityname = esc_html(__('Department', 'wp-job-portal'));
                $wpjobportal_linktext = $this->getEntityNameOrTitle($wpjobportal_id, 'name', $wpjobportal_tablename);
                break;
            case 'cities':
                $wpjobportal_entityname = esc_html(__('City', 'wp-job-portal'));
                $wpjobportal_linktext = $this->getEntityNameOrTitle($wpjobportal_id, 'name', $wpjobportal_tablename);
                break;
            //speceial case
            case 'resume':
                $wpjobportal_entityname = esc_html(__('Resume', 'wp-job-portal'));
                $wpjobportal_linktext = $this->getEntityNameOrTitle($wpjobportal_id, "CONCAT(first_name, ' ', last_name) AS Name", $wpjobportal_tablename);
                break;
            case 'jobsearches':
                $wpjobportal_entityname = esc_html(__('Job Search', 'wp-job-portal'));
                $wpjobportal_linktext = $this->getEntityNameOrTitle($wpjobportal_id, 'searchname', $wpjobportal_tablename);
                break;
            case 'resumesearches':
                $wpjobportal_entityname = esc_html(__('Resume Search', 'wp-job-portal'));
                $wpjobportal_linktext = $this->getEntityNameOrTitle($wpjobportal_id, 'searchname', $wpjobportal_tablename);
                break;
            case 'categories':
                $wpjobportal_entityname = esc_html(__('Category', 'wp-job-portal'));
                $wpjobportal_linktext = $this->getEntityNameOrTitle($wpjobportal_id, 'cat_title', $wpjobportal_tablename);
                break;
            case 'salaryrange':
                $wpjobportal_entityname = esc_html(__('Salary Range', 'wp-job-portal'));
                $wpjobportal_linktext = $this->getEntityNameOrTitle($wpjobportal_id, 'rangestart', $wpjobportal_tablename);
                break;
            case 'jobapply':
                $wpjobportal_entityname = esc_html(__('Applied for job', 'wp-job-portal'));
                $wpjobportal_linktext = $this->getEntityNameOrTitleForJobApply($wpjobportal_id, $wpjobportal_tablename);
                break;
            default:
                return false;
                break;
        }
        $wpjobportal_target = "_blank";
        $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        $wpjobportal_username = $this->getNameFromUid($wpjobportal_uid);
        $wpjobportal_path2 = esc_url_raw(admin_url('admin.php?page=wpjobportal_user&wpjobportallt=userdetail&id='.esc_attr($wpjobportal_uid)));
        $wpjobportal_html2 = "<a href=" . $wpjobportal_path2 . " target='".esc_attr($wpjobportal_target)."''><strong>" . $wpjobportal_username . "</strong></a>";
        $wpjobportal_entityaction = esc_html(__("Deleted a", "wp-job-portal"));
        $wpjobportal_result = array();
        $wpjobportal_result[0] = $wpjobportal_name;
        $wpjobportal_result[1] = "$wpjobportal_html2" . " " . $wpjobportal_entityaction . " " . $wpjobportal_entityname . " ( " . esc_html($wpjobportal_linktext) . " )";
        $wpjobportal_result[2] = $wpjobportal_uid;

        return $wpjobportal_result;
    }

    function getMessagekey(){
        $wpjobportal_key = 'activitylog';if(wpjobportal::$_common->wpjp_isadmin()){$wpjobportal_key = 'admin_'.$wpjobportal_key;}return $wpjobportal_key;
    }


}

?>
