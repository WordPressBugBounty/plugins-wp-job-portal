<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALjobapplyModel {
    public $class_prefix = '';

    function __construct(){
        if(wpjobportal::$wpjobportal_theme_chk == 1){
            $this->class_prefix = 'wpj-jp';
        }elseif(wpjobportal::$wpjobportal_theme_chk == 2){
            $this->class_prefix = 'jsjb-jh';
        } else {
            $this->class_prefix = 'wjportal';
        }
    }

    function jsGetPrefix(){
        global $wpdb;
        if(is_multisite()) {
            $wpjobportal_prefix = $wpdb->base_prefix;
        }else{
            $wpjobportal_prefix = wpjobportal::$_db->prefix;
        }
        return $wpjobportal_prefix;
    }

    function getAppliedResume() {
        //Filters
        $wpjobportal_searchtitle = WPJOBPORTALrequest::getVar('searchtitle');
        $wpjobportal_searchcompany = WPJOBPORTALrequest::getVar('searchcompany');
        $wpjobportal_searchjobcategory = WPJOBPORTALrequest::getVar('searchjobcategory');
        $wpjobportal_searchjobtype = WPJOBPORTALrequest::getVar('searchjobtype');
        $wpjobportal_searchjobstatus = WPJOBPORTALrequest::getVar('searchjobstatus');

        wpjobportal::$_data['filter']['searchtitle'] = $wpjobportal_searchtitle;
        wpjobportal::$_data['filter']['searchcompany'] = $wpjobportal_searchcompany;
        wpjobportal::$_data['filter']['searchjobcategory'] = $wpjobportal_searchjobcategory;
        wpjobportal::$_data['filter']['searchjobtype'] = $wpjobportal_searchjobtype;
        wpjobportal::$_data['filter']['searchjobstatus'] = $wpjobportal_searchjobstatus;

        if ($wpjobportal_searchjobcategory)
            if (is_numeric($wpjobportal_searchjobcategory) == false)
                return false;
        if ($wpjobportal_searchjobtype)
            if (is_numeric($wpjobportal_searchjobtype) == false)
                return false;
        if ($wpjobportal_searchjobstatus)
            if (is_numeric($wpjobportal_searchjobstatus) == false)
                return false;

        $wpjobportal_inquery = "";
        if ($wpjobportal_searchtitle)
            $wpjobportal_inquery .= " AND LOWER(job.title) LIKE '%" . esc_sql($wpjobportal_searchtitle) . "%'";
        if ($wpjobportal_searchcompany)
            $wpjobportal_inquery .= " AND LOWER(company.name) LIKE '%" . esc_sql($wpjobportal_searchcompany) . "%'";
        if ($wpjobportal_searchjobcategory && is_numeric($wpjobportal_searchjobcategory))
            $wpjobportal_inquery .= " AND job.jobcategory = " . esc_sql($wpjobportal_searchjobcategory);
        if ($wpjobportal_searchjobtype && is_numeric($wpjobportal_searchjobtype))
            $wpjobportal_inquery .= " AND job.jobtype = " . esc_sql($wpjobportal_searchjobtype);
        if ($wpjobportal_searchjobstatus && is_numeric($wpjobportal_searchjobstatus))
            $wpjobportal_inquery .= " AND job.jobstatus = " . esc_sql($wpjobportal_searchjobstatus);

        //Pagination
        $query = "SELECT COUNT(job.id) FROM " . wpjobportal::$_db->prefix . "wj_portal_jobs AS job
        ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON job.companyid = company.id
        WHERE job.status <> 0";
        $query.=$wpjobportal_inquery;

        $wpjobportal_total = wpjobportaldb::get_var($query);
        wpjobportal::$_data['total'] = $wpjobportal_total;
        wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($wpjobportal_total);

        //Data
        $query = "SELECT job.*, cat.cat_title, jobtype.title AS jobtypetitle, jobstatus.title AS jobstatustitle, company.name AS companyname
                , ( SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` WHERE jobid = job.id) AS totalresume
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON job.companyid = company.id
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON job.jobcategory = cat.id
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON job.jobtype = jobtype.id
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobstatus` AS jobstatus ON job.jobstatus = jobstatus.id
                WHERE job.status <> 0";
        $query.=$wpjobportal_inquery;
        $query .= " ORDER BY job.created DESC";
        $query.=" LIMIT " . WPJOBPORTALpagination::$_offset . "," . WPJOBPORTALpagination::$_limit;
        wpjobportal::$_data[0] = wpjobportaldb::get_results($query);
        return;
    }

    function canApplyOnJob($wpjobportal_jobid, $wpjobportal_uid) {
        $wpjobportal_result = $this->checkAlreadyAppliedJob($wpjobportal_jobid, $wpjobportal_uid);

        if($wpjobportal_result){// check credits if user has not already applied on this job
           return true;
        }else{// if already applied on this job return false
            return -1;
        }

    }

    function canAddJobApply($wpjobportal_jobapplyid,$wpjobportal_userid){
        $wpjobportal_result = $this->checkAlreadyAppliedJob($wpjobportal_jobapplyid, $wpjobportal_userid);
       if($wpjobportal_result && in_array('credits', wpjobportal::$_active_addons)){
            $wpjobportal_credits = WPJOBPORTALincluder::getObjectClass('userpackage')->do_action($wpjobportal_userid,'jobapply');
        }
    }

    function getJobAppliedResume($wpjobportal_tab_action, $wpjobportal_jobid, $wpjobportal_uid) {
        if (!is_numeric($wpjobportal_jobid))
            return false;
        if($wpjobportal_uid)
        if (!is_numeric($wpjobportal_uid))
            return false;

        $query = "SELECT title FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` WHERE id = ". esc_sql($wpjobportal_jobid);
        wpjobportal::$_data['jobtitle'] = wpjobportal::$_db->get_var($query);

        $title = isset(wpjobportal::$_search['search_filter']['title']) ? wpjobportal::$_search['search_filter']['title']: '';

        $wpjobportal_name = isset(wpjobportal::$_search['search_filter']['name']) ? wpjobportal::$_search['search_filter']['name']: '';
        $wpjobportal_nationality = isset(wpjobportal::$_search['search_filter']['nationality']) ? wpjobportal::$_search['search_filter']['nationality']: '';
        $wpjobportal_jobcategory = isset(wpjobportal::$_search['search_filter']['jobcategory']) ? wpjobportal::$_search['search_filter']['jobcategory']: '';
        $wpjobportal_gender = isset(wpjobportal::$_search['search_filter']['gender']) ? wpjobportal::$_search['search_filter']['gender']: '';
        $wpjobportal_jobtype = isset(wpjobportal::$_search['search_filter']['jobtype']) ? wpjobportal::$_search['search_filter']['jobtype']: '';
        $currency = isset(wpjobportal::$_search['search_filter']['currency']) ? wpjobportal::$_search['search_filter']['currency']: '';
        $wpjobportal_jobsalaryrange = isset(wpjobportal::$_search['search_filter']['jobsalaryrange']) ? wpjobportal::$_search['search_filter']['jobsalaryrange']: '';
        $heighestfinisheducation = isset(wpjobportal::$_search['search_filter']['heighestfinisheducation']) ? wpjobportal::$_search['search_filter']['heighestfinisheducation']: '';


        // $wpjobportal_formsearch = WPJOBPORTALrequest::getVar('WPJOBPORTAL_form_search', 'post');
        // if ($wpjobportal_formsearch == 'WPJOBPORTAL_SEARCH') {
        //     $_SESSION['WPJOBPORTAL_SEARCH']['title'] = $title;
        //     $_SESSION['WPJOBPORTAL_SEARCH']['name'] = $wpjobportal_name;
        //     $_SESSION['WPJOBPORTAL_SEARCH']['nationality'] = $wpjobportal_nationality;
        //     $_SESSION['WPJOBPORTAL_SEARCH']['jobcategory'] = $wpjobportal_jobcategory;
        //     $_SESSION['WPJOBPORTAL_SEARCH']['jobtype'] = $wpjobportal_jobtype;
        //     $_SESSION['WPJOBPORTAL_SEARCH']['heighestfinisheducation'] = $heighestfinisheducation;
        // }
        // if (WPJOBPORTALrequest::getVar('pagenum', 'get', null) != null) {
        //     $title = (isset($_SESSION['WPJOBPORTAL_SEARCH']['title']) && $_SESSION['WPJOBPORTAL_SEARCH']['title'] != '') ? sanitize_key($_SESSION['WPJOBPORTAL_SEARCH']['title']) : null;
        //     $wpjobportal_name = (isset($_SESSION['WPJOBPORTAL_SEARCH']['name']) && $_SESSION['WPJOBPORTAL_SEARCH']['name'] != '') ? sanitize_key($_SESSION['WPJOBPORTAL_SEARCH']['name']) : null;
        //     $wpjobportal_nationality = (isset($_SESSION['WPJOBPORTAL_SEARCH']['nationality']) && $_SESSION['WPJOBPORTAL_SEARCH']['nationality'] != '') ? sanitize_key($_SESSION['WPJOBPORTAL_SEARCH']['nationality']) : null;
        //     $wpjobportal_jobcategory = (isset($_SESSION['WPJOBPORTAL_SEARCH']['jobcategory']) && $_SESSION['WPJOBPORTAL_SEARCH']['jobcategory'] != '') ? sanitize_key($_SESSION['WPJOBPORTAL_SEARCH']['jobcategory']) : null;

        //     $wpjobportal_gender = (isset($_SESSION['WPJOBPORTAL_SEARCH']['gender']) && $_SESSION['WPJOBPORTAL_SEARCH']['gender'] != '') ? sanitize_key($_SESSION['WPJOBPORTAL_SEARCH']['gender']) : null;
        //     $wpjobportal_jobtype = (isset($_SESSION['WPJOBPORTAL_SEARCH']['jobtype']) && $_SESSION['WPJOBPORTAL_SEARCH']['jobtype'] != '') ? sanitize_key($_SESSION['WPJOBPORTAL_SEARCH']['jobtype']) : null;
        //     $currency = (isset($_SESSION['WPJOBPORTAL_SEARCH']['currency ']) && $_SESSION['WPJOBPORTAL_SEARCH']['currency '] != '') ? sanitize_key($_SESSION['WPJOBPORTAL_SEARCH']['currency ']) : null;
        //     $wpjobportal_jobsalaryrange = (isset($_SESSION['WPJOBPORTAL_SEARCH']['jobsalaryrange']) && $_SESSION['WPJOBPORTAL_SEARCH']['jobsalaryrange'] != '') ? sanitize_key($_SESSION['WPJOBPORTAL_SEARCH']['jobsalaryrange']) : null;
        //     $heighestfinisheducation = (isset($_SESSION['WPJOBPORTAL_SEARCH']['heighestfinisheducation']) && $_SESSION['WPJOBPORTAL_SEARCH']['heighestfinisheducation'] != '') ? sanitize_key($_SESSION['WPJOBPORTAL_SEARCH']['heighestfinisheducation']) : null;
        // } else if ($wpjobportal_formsearch !== 'WPJOBPORTAL_SEARCH') {
        //     if(isset($_SESSION['WPJOBPORTAL_SEARCH']))
        //         unset($_SESSION['WPJOBPORTAL_SEARCH']);
        // }

        $wpjobportal_inquery = "";
        if ($wpjobportal_tab_action) {
            $wpjobportal_inquery.=" AND jobapply.action_status =" . esc_sql($wpjobportal_tab_action);
        }
        if ($title) {
            $wpjobportal_inquery.=" AND app.application_title LIKE '%" . esc_sql($title) . "%'";
        }
        if ($wpjobportal_name) {
            $wpjobportal_inquery.=" AND LOWER(app.first_name) LIKE '%" . esc_sql($wpjobportal_name) . "%'";
        }

        if (is_numeric($wpjobportal_nationality)) {
            $wpjobportal_inquery .= " AND app.nationality = " . esc_sql($wpjobportal_nationality);
        }
        if (is_numeric($wpjobportal_gender)) {
            $wpjobportal_inquery .= " AND app.gender = " . esc_sql($wpjobportal_gender);
        }
        if (is_numeric($wpjobportal_jobtype)) {
            $wpjobportal_inquery .= " AND app.jobtype = " . esc_sql($wpjobportal_jobtype);
        }
        if (is_numeric($currency)) {
            $wpjobportal_inquery .= " AND app.currencyid = " . esc_sql($currency);
        }
        if (is_numeric($wpjobportal_jobsalaryrange)) {
            $wpjobportal_inquery .= " AND ( ( dsalarystart.rangestart >= (SELECT rangestart FROM `" . wpjobportal::$_db->prefix . "wj_portal_salaryrange` WHERE id = " . esc_sql($wpjobportal_jobsalaryrange) . "))
                          AND ( dsalarystart.rangeend <= (SELECT rangeend FROM `" . wpjobportal::$_db->prefix . "wj_portal_salaryrange` WHERE id = " . esc_sql($wpjobportal_jobsalaryrange) . ")) ) ";
        }
        if (is_numeric($heighestfinisheducation)) {
            $wpjobportal_inquery .= " AND app.heighestfinisheducation = " . esc_sql($heighestfinisheducation);
        }
        if (is_numeric($wpjobportal_jobcategory)) {
            $wpjobportal_inquery .= " AND app.job_category = " . esc_sql($wpjobportal_jobcategory);
        }


        if (!wpjobportal::$_common->wpjp_isadmin()) {
            $wpjobportal_inquery .= " AND job.uid= " . esc_sql($wpjobportal_uid);
        }

        wpjobportal::$_data['filter']['title'] = $title;
        wpjobportal::$_data['filter']['name'] = $wpjobportal_name;
        wpjobportal::$_data['filter']['nationality'] = $wpjobportal_nationality;
        wpjobportal::$_data['filter']['jobcategory'] = $wpjobportal_jobcategory;

        wpjobportal::$_data['filter']['gender'] = $wpjobportal_gender;
        wpjobportal::$_data['filter']['jobtype'] = $wpjobportal_jobtype;
        wpjobportal::$_data['filter']['jobsalaryrange'] = $wpjobportal_jobsalaryrange;
        wpjobportal::$_data['filter']['heighestfinisheducation'] = $heighestfinisheducation;


        // $wpjobportal_inquery = "";
        if ($wpjobportal_tab_action && is_numeric($wpjobportal_tab_action) && in_array('resumeaction', wpjobportal::$_active_addons)) {
            $wpjobportal_inquery.=" AND jobapply.action_status =" . esc_sql($wpjobportal_tab_action);
        }
        if(wpjobportal::$_common->wpjp_isadmin()){
            wpjobportal::$_data[4]['jobinfo'] = $this->getJobApp($wpjobportal_jobid);
        }else{
            wpjobportal::$_data[4]['jobinfo'] = $this->getMyJobs($wpjobportal_uid,$wpjobportal_jobid);
             wpjobportal::$wpjobportal_data['fields'] = wpjobportal::$_wpjpfieldordering->getFieldsOrderingforForm(2);
        wpjobportal::$_data['config'] = wpjobportal::$_config->getConfigByFor('job');
        }

        //Pagination
        $query = "SELECT COUNT(job.id)
        FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
           , `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` AS jobapply
           , `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS app
        WHERE jobapply.jobid = job.id AND jobapply.cvid = app.id AND jobapply.jobid = ". esc_sql($wpjobportal_jobid)." AND jobapply.status = 1 ";
        $query.=$wpjobportal_inquery;
        $wpjobportal_total = wpjobportaldb::get_var($query);
        wpjobportal::$_data['total'] = $wpjobportal_total;

        wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($wpjobportal_total);
        $this->sorting();
        // there was an error. the data was showing job category in the resume resume category data
        $query = "SELECT app.uid AS jobseekerid,company.uid AS employerid,jobapply.comments,jobapply.id AS jobapplyid ,job.id,job.uid as userid,cat.cat_title ,jobapply.apply_date, jobapply.resumeview, jobapply.socialprofileid, jobtype.title AS jobtypetitle,app.endfeatureddate,app.isfeaturedresume,app.id AS appid,app.id AS id, app.first_name, app.last_name,app.email_address, app.jobtype,app.gender,job.id AS jobid
                , app.id as resumeid ,job.hits AS jobview,app.last_modified,app.salaryfixed as salary,jobapply.rating,jobtype.color as jobtypecolor
                ,(SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` WHERE jobid = job.id) AS totalapply
                ,(SELECT address_city FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumeaddresses` WHERE resumeid = app.id ORDER BY created DESC LIMIT 1) AS resumecity ,app.photo AS photo,app.application_title AS applicationtitle
                ,CONCAT(app.alias,'-',app.id) resumealiasid, CONCAT(job.alias,'-',job.id) AS jobaliasid
                ,( Select rinsitute.institute From`" . wpjobportal::$_db->prefix . "wj_portal_resumeinstitutes` AS rinsitute Where rinsitute.resumeid = app.id LIMIT 1 ) AS institute
                ,( Select rinsitute.institute_study_area From`" . wpjobportal::$_db->prefix . "wj_portal_resumeinstitutes` AS rinsitute Where rinsitute.resumeid = app.id LIMIT 1 ) AS institute_study_area
                ,job.companyid,app.params, jobapply.coverletterid,resum_cat.cat_title AS resume_category,jobapply.apply_message,jobapply.quick_apply, app.skills ";
                if(in_array('sociallogin', wpjobportal::$_active_addons)){
                    $query.=" ,socialprofiles.profiledata as socialprofile ";
                }
                $query.=" FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` AS jobapply  ON jobapply.jobid = job.id
                ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON company.id = job.companyid
                JOIN `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS app ON app.id = jobapply.cvid
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON cat.id = job.jobcategory
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS resum_cat ON resum_cat.id = app.job_category
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON jobtype.id = app.jobtype
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON city.id = (SELECT address_city FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumeaddresses` WHERE resumeid = app.id LIMIT 1)";
                if(in_array('sociallogin', wpjobportal::$_active_addons)){
                    $query.=" LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_socialprofiles` AS socialprofiles ON socialprofiles.id = jobapply.socialprofileid";
                }
            $query.=" WHERE jobapply.jobid = ".esc_sql($wpjobportal_jobid)." AND jobapply.status = 1 ";
        $query.= $wpjobportal_inquery;
        $query .= " ORDER BY " . wpjobportal::$_data['sorting'];
        $query .= " LIMIT " . WPJOBPORTALpagination::$_offset . "," . WPJOBPORTALpagination::$_limit;
        $wpjobportal_result = wpjobportaldb::get_results($query);
        wpjobportal::$_data[0]['ta'] = $wpjobportal_jobid;
        wpjobportal::$_data[0]['tabaction'] = $wpjobportal_tab_action;
        wpjobportal::$_data[0]['jobid'] = $wpjobportal_jobid;
        $wpjobportal_data = array();
        foreach ($wpjobportal_result AS $d) {
            $d->location = WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($d->resumecity);
            if(in_array('coverletter',wpjobportal::$_active_addons)){
                $d->coverletterdata = WPJOBPORTALincluder::getJSModel('coverletter')->getCoverLetterTitleDescFromID($d->coverletterid);
            }
            $wpjobportal_data[] = $d;
        }
        wpjobportal::$_data[0]['data'] = $wpjobportal_data;
        $query = "Select Count(id) from`" . wpjobportal::$_db->prefix . "wj_portal_jobapply` WHERE action_status=1 AND jobid = ". esc_sql($wpjobportal_jobid) ." AND status = 1";
        wpjobportal::$_data[0]['inbox'] = wpjobportaldb::get_var($query);


        $query = "Select Count(id) from`" . wpjobportal::$_db->prefix . "wj_portal_jobapply` WHERE action_status=2 AND jobid = ". esc_sql($wpjobportal_jobid) ." AND status = 1";
        wpjobportal::$_data[0]['spam'] = wpjobportaldb::get_var($query);


        $query = "Select Count(id) from`" . wpjobportal::$_db->prefix . "wj_portal_jobapply` WHERE action_status=3 AND jobid = ". esc_sql($wpjobportal_jobid) ." AND status = 1";
        wpjobportal::$_data[0]['hired'] = wpjobportaldb::get_var($query);

        $query = "Select Count(id) from`" . wpjobportal::$_db->prefix . "wj_portal_jobapply` WHERE jobid = ".esc_sql($wpjobportal_jobid)." AND status = 1 " ;
        wpjobportal::$_data[0]['applied'] = wpjobportaldb::get_var($query);

        $query = "Select hits from`" . wpjobportal::$_db->prefix . "wj_portal_jobs` WHERE id = ". esc_sql($wpjobportal_jobid);
        wpjobportal::$_data[0]['hits'] = wpjobportaldb::get_var($query);


        $query = "Select Count(id) from`" . wpjobportal::$_db->prefix . "wj_portal_jobapply` WHERE action_status=4 AND jobid = ". esc_sql($wpjobportal_jobid) ." AND status = 1";
        wpjobportal::$_data[0]['reject'] = wpjobportaldb::get_var($query);


        $query = "Select Count(id) from`" . wpjobportal::$_db->prefix . "wj_portal_jobapply` WHERE action_status=5 AND jobid = ". esc_sql($wpjobportal_jobid) ." AND status = 1";
        wpjobportal::$_data[0]['shortlisted'] = wpjobportaldb::get_var($query);

        $query = "Select job.title,jobtype.title AS jobtypetitle,LOWER(jobtype.title) AS jobtypetit
                    FROM`" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON job.jobtype = jobtype.id
                    WHERE job.id = ". esc_sql($wpjobportal_jobid);
        $wpjobportal_job_info = wpjobportaldb::get_row($query);
        if(!empty($wpjobportal_job_info)){ // to handle log error
            wpjobportal::$_data[0]['jobtitle'] = $wpjobportal_job_info->title;
            wpjobportal::$_data[0]['jobtypetitle'] = $wpjobportal_job_info->jobtypetitle;
            wpjobportal::$_data[0]['jobtypetit'] = $wpjobportal_job_info->jobtypetit;
        }

        // wpjobportal::$wpjobportal_data['fields'] = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldsOrderingforSearch(3);// search fields
        wpjobportal::$wpjobportal_data['field'] = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldsOrderingforView(2);

        wpjobportal::$_data['listingfields'] = wpjobportal::$_wpjpfieldordering->getFieldsForListing(3);

        return;
    }

    function getResumeDetail($wpjobportal_themecall=null) {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'get-resume-detail') ) {
            die( 'Security check Failed' );
        }
        $wpjobportal_salary = WPJOBPORTALrequest::getVar('sal');
        $wpjobportal_exprince = WPJOBPORTALrequest::getVar('expe');
        $wpjobportal_insitute = WPJOBPORTALrequest::getVar('institue');
        $wpjobportal_study = WPJOBPORTALrequest::getVar('stud');
        $available = WPJOBPORTALrequest::getVar('ava');

        if ($available == 1) {
            $res = "Yes";
        } else {
            $res = "No";
        }
        if(null != $wpjobportal_themecall){
            $return['salary']=$wpjobportal_salary;
            $return['exprince']=$wpjobportal_exprince;
            $return['insitute']=$wpjobportal_insitute;
            $return['study']=$wpjobportal_study;
            $return['available']=$available;
            $return['res']=$res;
            return $return;
        }
        $wpjobportal_html = '';
        if (wpjobportal::$wpjobportal_theme_chk == 1) {
            $wpjobportal_html.='<img id="close-section" onclick="closeSection()" src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/no.png"/>';
            $wpjobportal_html.='<div class="wpj-jp-applied-resume-cnt wpj-jp-actions-detail-wrp">';
            $wpjobportal_html.='<div class="wpj-jp-applied-resume-cnt-row">';
            $wpjobportal_html.='<span class="wpj-jp-applied-resume-cnt-tit">' . esc_html(__("Current Salary", 'wp-job-portal')) . ': </span><span class="wpj-jp-applied-resume-cnt-val">' . $wpjobportal_salary;
            $wpjobportal_html.='</span></div>';
            $wpjobportal_html.='</div>';
        } else {
            $wpjobportal_html.='<img id="close-section" onclick="closeSection()" src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/no.png"/>';
            $wpjobportal_html.='<div class="wjportal-applied-job-actions-wrp wjportal-job-actions-detail-wrp">';
            $wpjobportal_html.='<div class="wjportal-job-actions-detail-row">';
            $wpjobportal_html.='<span class="wjportal-job-actions-detail-tit">' . esc_html(__('Current Salary', 'wp-job-portal')) . ': </span><span class="wjportal-job-actions-detail-val">' . $wpjobportal_salary;
            $wpjobportal_html.='</span></div>';
            $wpjobportal_html.='</div>';
        }
        return $wpjobportal_html;
    }

    /* may not in used
	function getJobApplyDetailByid(){
        $wpjobportal_id = WPJOBPORTALrequest::getVar('id');
        $wpjobportal_pageid = WPJOBPORTALrequest::getVar('pageid');
        $wpjobportal_content="";
        if ($wpjobportal_id && is_numeric($wpjobportal_id)) {
            $query = "SELECT resume.id AS resumeid
                    ,CONCAT(resume.first_name, ' ', resume.last_name) AS Name,jobapply.id AS id
                     FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` AS jobapply
                     JOIN `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume ON resume.id = jobapply.cvid
                     WHERE jobapply.id = " . esc_sql($wpjobportal_id);
            $wpjobportal_result = wpjobportaldb::get_row($query);
            if($wpjobportal_result){
                $wpjobportal_content .='<div class="modal-content '.esc_attr($this->class_prefix).'-modal-wrp">
                                <div class="'.esc_attr($this->class_prefix).'-modal-header">
                                    <a title="close" class="close '.esc_attr($this->class_prefix).'-modal-close-icon-wrap" href="#" onclick="wpjobportalClosePopup(1);" >
                                        <img id="popup_cross" alt="popup cross" src="'.esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/popup-close.png">
                                    </a>
                                    <h2 class="'.esc_attr($this->class_prefix).'-modal-title">'.esc_html(__("Applied Info",'wp-job-portal')).'</h2>
                                </div>
                                <div class="col-md-12 '.esc_attr($this->class_prefix).'-appliedinformation-modal-data-wrp">
                                    <div class="modal-body '.esc_attr($this->class_prefix).'-modal-body">
                                       <div class="'.esc_attr($this->class_prefix).'-appliedinformation-title">

                                       <h5 class="'.esc_attr($this->class_prefix).'-appliedinformation-title-txt">
                                            <a href="'.esc_url(wpjobportal::wpjobportal_makeUrl(array("wpjobportalpageid"=>$wpjobportal_pageid,"wpjobportalme"=>"resume","wpjobportallt"=>"viewresume","wpjobportalid"=>$wpjobportal_result->resumeid))).'">
                                                '.$wpjobportal_result->Name.'
                                            </a>';
                                            if($wpjobportal_result->application_title != ''){
                                                $wpjobportal_content .= '('.$wpjobportal_result->application_title.')';
                                            }
                                        $wpjobportal_content .='
                                        </h5>
                                       </div>
                                    </div>
                                </div>
                            </div>';
            }else{
                $wpjobportal_content .='<div class="modal-content '.esc_attr($this->class_prefix).'-modal-wrp">
                    <div class="'.esc_attr($this->class_prefix).'-modal-header">
                        <a title="close" class="close '.esc_attr($this->class_prefix).'-modal-close-icon-wrap" href="#" onclick="wpjobportalClosePopup(1);" >
                            <img id="popup_cross" alt="popup cross" src="'.esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/popup-close.png">
                        </a><h2 class="'.esc_attr($this->class_prefix).'-modal-title">'.esc_html(__("Applied Info",'wp-job-portal')).'</h2></div>
                        <div class="col-md-12 '.esc_attr($this->class_prefix).'-appliedinformation-modal-data-wrp">
                            <h3 class="'.esc_attr($this->class_prefix).'-modal-title">'.esc_html(__("No Record Found",'wp-job-portal')).'</h3>
                        </div>
                        </div>';
            }
        }else{
            $wpjobportal_content .='<div class="modal-content '.esc_attr($this->class_prefix).'-modal-wrp">
            <div class="'.esc_attr($this->class_prefix).'-modal-header">
                <a title="close" class="close '.esc_attr($this->class_prefix).'-modal-close-icon-wrap" href="#" onclick="wpjobportalClosePopup(1);" >
                    <img id="popup_cross" alt="popup cross" src="'.esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/popup-close.png">
                </a><h2 class="'.esc_attr($this->class_prefix).'-modal-title">'.esc_html(__("Applied Info",'wp-job-portal')).'</h2></div>
                <div class="col-md-12 '.esc_attr($this->class_prefix).'-appliedinformation-modal-data-wrp">
                    <h3 class="'.esc_attr($this->class_prefix).'-modal-title">'.esc_html(__("Something wrong pleas try later",'wp-job-portal')).'</h3>
                </div>
                </div>';
        }
        $wpjobportal_array = array('title' => "", 'wpjobportal_content' => $wpjobportal_content);
        return wp_json_encode($wpjobportal_array);
    }*/

    function getApplyNowByJobid() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('js_nonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wp-job-portal-nonce') ) {
            die( 'Security check Failed' );
        }
        $wpjobportal_jobid = WPJOBPORTALrequest::getVar('jobid');
        $wpjobportal_themecall = WPJOBPORTALrequest::getVar('themecall');
        // page id from ajax call
        $wpjobportal_pageid_ajax = WPJOBPORTALrequest::getVar('wpjobportal_pageid','',wpjobportal::wpjobportal_getPageid());
        $wpjobportal_upakid = WPJOBPORTALrequest::getVar('upkid','',0);
        $wpjobportal_config_array = wpjobportal::$_config->getConfigByFor('jobapply');
        $wpjobportal_user = WPJOBPORTALincluder::getObjectClass('user');
        $wpjobportal_config_array = wpjobportal::$_config->getConfigByFor('jobapply');
        if ($wpjobportal_jobid && is_numeric($wpjobportal_jobid)) {

                // redundunt code
                // $query = "SELECT job.title FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job WHERE job.id = ". esc_sql($wpjobportal_jobid);
                // $wpjobportal_jobtitle = wpjobportal::$_db->get_var($query);

                # Credit Member Ship Type
                $wpjobportal_visitorcanapply = wpjobportal::$_config->getConfigurationByConfigName('visitor_can_apply_to_job');
                if((in_array('credits', wpjobportal::$_active_addons) && WPJOBPORTALincluder::getObjectClass('user')->isguest() && $wpjobportal_visitorcanapply != 1) || (in_array('credits', wpjobportal::$_active_addons) && !WPJOBPORTALincluder::getObjectClass('user')->isguest())){
                    if(wpjobportal::$_config->getConfigValue('submission_type') == 3){
                        /** 21/02/2019***/
                        //Member ship Show
                        $title = '';
                        $wpjobportal_content = '';
                        $wpjobportal_package = apply_filters('wpjobportal_addons_userpackages_permodule',false,$wpjobportal_upakid,$wpjobportal_user->uid(),'remjobapply');
                        if( !$wpjobportal_package ){
                            $title = esc_html(__('Apply Now Failed', 'wp-job-portal'));
                            $wpjobportal_content = esc_html(__('You do not have package required for job apply', 'wp-job-portal'));
                        }else{
                            if( $wpjobportal_package->expired ){
                                $title = esc_html(__('Apply Now Failed', 'wp-job-portal'));
                                $wpjobportal_content = esc_html(__('You package has expired', 'wp-job-portal'));
                            }
                            //if Department are not unlimited & there is no remaining left
                            if( $wpjobportal_package->jobapply!=-1 && !$wpjobportal_package->remjobapply ){ //-1 = unlimited
                                $title = esc_html(__('Apply Now Failed', 'wp-job-portal'));
                                $wpjobportal_content = esc_html(__('You do not any more job apply available', 'wp-job-portal'));
                            }
                        }
                        // show proper messages
                        if($title != '' && $wpjobportal_content != ''){
                            // $title = wpjobportalphplib::wpJP_safe_encoding($title);
                            // $wpjobportal_content = wpjobportalphplib::wpJP_safe_encoding($wpjobportal_content);

                            $title = mb_convert_encoding($title, 'UTF-8', mb_detect_encoding($title));
                            $wpjobportal_content = mb_convert_encoding($wpjobportal_content, 'UTF-8', mb_detect_encoding($wpjobportal_content));

                            $wpjobportal_array = array('title' => $title, 'wpjobportal_content' => $wpjobportal_content);
                            return wp_json_encode($wpjobportal_array);
                        }

                        $wpjobportal_data['status'] = 1;
                        $wpjobportal_data['userpackageid'] = $wpjobportal_upakid;
                    }
               }

                // die($wpjobportal_user->uid());
                $wpjobportal_result = $this->getJobByid($wpjobportal_jobid);


                if(isset($wpjobportal_result) && !empty($wpjobportal_result)){
                    $wpjobportal_job = $wpjobportal_result[0];
                }else{
                    $title = __("Job Not Found","wp-job-portal");
                  	$wpjobportal_content = __("Job does not exist in the system","wp-job-portal");
                    $wpjobportal_array = array('title' => $title, 'wpjobportal_content' => $wpjobportal_content);
                    return wp_json_encode($wpjobportal_array);
                }
                $title = esc_html(__('Apply Now', 'wp-job-portal'));
                $wpjobportal_content = '';// to handle log error of appending to non exsistent variable content
                $wpjobportal_listing_fields = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldOrderingDataForListing(2);// get published fields labals
                if (wpjobportal::$wpjobportal_theme_chk == 1) {
                    /*Pop up detail data For Job(Extra Detail)*/
                    $wpjobportal_content .=  '<div class="wpj-jp-popup-cnt-wrp">';
                    $wpjobportal_content .=  '<i class="fas fa-times wpj-jp-popup-close-icon" data-dismiss="modal"></i>';
                    $wpjobportal_content .=  '<div class="wpj-jp-popup-right">';
                    $wpjobportal_content .=  '<div class="wpj-jp-popup-list">';
                    if($wpjobportal_job->companyid != ''){
                        if ($wpjobportal_job->logofilename != "") {
                            $wpjobportal_wpdir = wp_upload_dir();
                            $wpjobportal_data_directory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
                            $wpjobportal_path = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/employer/comp_' . $wpjobportal_job->companyid . '/logo/' . $wpjobportal_job->logofilename;
                        } else {
                            $wpjobportal_path = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('employer');
                        }
                        if(in_array('multicompany', wpjobportal::$_active_addons)){
                            $wpjobportal_mod = "multicompany";
                        }else{
                            $wpjobportal_mod = "company";
                        }
                        $wpjobportal_published_fields = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldOrderingData(1);
                        if(isset($wpjobportal_published_fields['logo']) && $wpjobportal_published_fields['logo'] != ''){
                            $wpjobportal_content .= '<div class="wpj-jp-popup-list-logo">';
                            $wpjobportal_content .=     '<a href='. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>$wpjobportal_mod, 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$wpjobportal_job->companyid,'wpjobportalpageid'=>$wpjobportal_pageid_ajax))) .' title='. esc_attr(__('company logo','wp-job-portal')).'>';
                            $wpjobportal_content .=         '<img src='. esc_url($wpjobportal_path) .' alt="'.esc_attr(__('Company logo','wp-job-portal')).'" >';
                            $wpjobportal_content .=     '</a>';
                            $wpjobportal_content .= '</div>';

                        }
                    }

                    $wpjobportal_content .= '<div class="wpj-jp-popup-list-cnt-wrp">';
                    $wpjobportal_content .=     '<div class="wpj-jp-popup-list-cnt">';
                    $wpjobportal_content .=          '<span class="wpj-jp-job-type" style="color:'.$wpjobportal_job->jobtypecolor.'">';
                    $wpjobportal_content .=             wpjobportal::wpjobportal_getVariableValue($wpjobportal_job->jobtypetitle);
                    $wpjobportal_content .=          '</span>';
                    $wpjobportal_content .=     '</div>';
                    $wpjobportal_content .=     '<div class="wpj-jp-popup-list-cnt">';
                    $wpjobportal_content .=         '<a class="wpj-jp-popup-list-comp-tit" href='. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>$wpjobportal_mod, 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$wpjobportal_job->companyid,'wpjobportalpageid'=>$wpjobportal_pageid_ajax))).' title="'.esc_attr(__('Company name','wp-job-portal')).'">
                                            '.wpjobportal::wpjobportal_getVariableValue($wpjobportal_job->companyname).'
                                        </a>';
                    $wpjobportal_content .=     '</div>';
                    $wpjobportal_content .=     '<div class="wpj-jp-popup-list-cnt">';
                    $wpjobportal_content .=         '<h5 class="wpj-jp-popup-list-tit">
                                            <a href='.esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'viewjob', 'wpjobportalid'=>$wpjobportal_job->jobaliasid,'wpjobportalpageid'=>$wpjobportal_pageid_ajax))).' title="'.esc_attr(__('job title','wp-job-portal')).'">
                                                '.wpjobportal::wpjobportal_getVariableValue($wpjobportal_job->title).'
                                            </a>
                                        </h5>';
                    $wpjobportal_content .=     '</div>';
                    $wpjobportal_content .=     '<div class="wpj-jp-popup-list-cnt">';
                    $wpjobportal_content .=         '<ul>';
                                            if(isset($wpjobportal_listing_fields['jobcategory'])){
                                                if(isset($wpjobportal_job) && !empty($wpjobportal_job->cat_title)){
                    $wpjobportal_content .=             '<li>';
                    $wpjobportal_content .=                     '<span class="wpj-jp-popup-list-meta-tit">';
                    $wpjobportal_content .=                         wpjobportal::wpjobportal_getVariableValue($wpjobportal_listing_fields['jobcategory']). ':';
                    $wpjobportal_content .=                     '</span>';
                    $wpjobportal_content .=                      wpjobportal::wpjobportal_getVariableValue($wpjobportal_job->cat_title);
                    $wpjobportal_content .=             '</li>';
                                                }
                                            }
                                            if(isset($wpjobportal_listing_fields['jobsalaryrange'])){
                        $wpjobportal_content .=             '<li>';
                        $wpjobportal_content .=                  '<span class="wpj-jp-popup-list-meta-tit">';
                        $wpjobportal_content .=                       wpjobportal::wpjobportal_getVariableValue($wpjobportal_listing_fields['jobsalaryrange']). ':';
                        $wpjobportal_content .=                   '</span>';
                        $wpjobportal_content .=                    wpjobportal::$_common->getSalaryRangeView($wpjobportal_job->salarytype, $wpjobportal_job->salarymin, $wpjobportal_job->salarymax,$wpjobportal_job->currency);
                                                       if($wpjobportal_job->salarytype==3 || $wpjobportal_job->salarytype==2) {
                        $wpjobportal_content .=                      ' - ' .wpjobportal::wpjobportal_getVariableValue($wpjobportal_job->srangetypetitle);
                                                       }
                        $wpjobportal_content .=             '</li>';
                                               }
                    $wpjobportal_content .=             '<li>';
                                            if(isset($wpjobportal_listing_fields['city'])){
                                                    if(isset($wpjobportal_job) && !empty($wpjobportal_job->location)){
                        $wpjobportal_content .=                     '<span class="wpj-jp-popup-list-meta-tit">';
                        $wpjobportal_content .=                         wpjobportal::wpjobportal_getVariableValue($wpjobportal_listing_fields['city']). ':';
                        $wpjobportal_content .=                     '</span>';
                        $wpjobportal_content .=                     wpjobportal::wpjobportal_getVariableValue($wpjobportal_job->location);
                        $wpjobportal_content .=             '</li>';
                                                    }
                                                }
                    $wpjobportal_content .=         '</ul>';
                    $wpjobportal_content .=     '</div>';
                    $wpjobportal_content .= '</div>';
                    $wpjobportal_content .= '</div>'; // end job list
                    $wpjobportal_content .= '</div>'; // right div
                    // Pop up detail data For Job Ends there
                } else {
                    /*Pop up detail data For Job(Extra Detail)*/
                    $wpjobportal_content =  '<div class="wjportal-jobs-list">';
                    $wpjobportal_content .= ' <div class="wjportal-jobs-list-top-wrp">';
                    if(in_array('multicompany', wpjobportal::$_active_addons)){
                        $wpjobportal_mod = "multicompany";
                    }else{
                        $wpjobportal_mod = "company";
                    }
                    if($wpjobportal_job->companyid != ''){
                        $wpjobportal_path = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('employer');
                        if ($wpjobportal_job->logofilename != "") {
                            $wpjobportal_wpdir = wp_upload_dir();
                            $wpjobportal_data_directory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
                            $wpjobportal_path = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/employer/comp_' . $wpjobportal_job->companyid . '/logo/' . $wpjobportal_job->logofilename;
                        }
                        if(in_array('multicompany', wpjobportal::$_active_addons)){
                            $wpjobportal_mod = "multicompany";
                        }else{
                            $wpjobportal_mod = "company";
                        }
                        $wpjobportal_published_fields = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldOrderingData(1);
                        if(isset($wpjobportal_published_fields['logo']) && $wpjobportal_published_fields['logo'] != ''){
                            $wpjobportal_content .= '<div class="wjportal-jobs-logo">';
                            $wpjobportal_content .=     '<a href='. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>$wpjobportal_mod, 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$wpjobportal_job->companyid,'wpjobportalpageid'=>$wpjobportal_pageid_ajax))) .'>';
                            $wpjobportal_content .=         '<img src='. $wpjobportal_path .' alt="'.esc_attr(__('Company logo','wp-job-portal')).'" >';
                            $wpjobportal_content .=     '</a>';
                            $wpjobportal_content .= '</div>';
                        }
                    }
                    $wpjobportal_content .= '<div class="wjportal-jobs-cnt-wrp">';
                    $wpjobportal_content .= '<div class="wjportal-jobs-middle-wrp">';
                    $wpjobportal_content .=     '<div class="wjportal-jobs-data">';
                    if (wpjobportal::$_config->getConfigValue('comp_name')) {
                        $wpjobportal_content .=         '<a class="wjportal-companyname" href='. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>$wpjobportal_mod, 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$wpjobportal_job->companyid,'wpjobportalpageid'=>$wpjobportal_pageid_ajax))).' title="'.esc_attr(__('Company name','wp-job-portal')).'">'. $wpjobportal_job->companyname.'</a>';
                    }

                    $wpjobportal_content .=     '</div>';
                    $wpjobportal_content .=     '<div class="wjportal-jobs-data">';
                    $wpjobportal_content .=         '<span class="wjportal-job-title">';
                    $wpjobportal_content .=             '<a href='.esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'viewjob', 'wpjobportalid'=>$wpjobportal_job->jobaliasid,'wpjobportalpageid'=>$wpjobportal_pageid_ajax))).' title="'.esc_attr(__('Job title','wp-job-portal')).'">';
                    $wpjobportal_content .=                 $wpjobportal_job->title;
                    $wpjobportal_content .=             '</a>';
                    $wpjobportal_content .=         '</span>';
                    $wpjobportal_content .=     '</div>';
                    $wpjobportal_content .=     '<div class="wjportal-jobs-data">';
                    if(isset($wpjobportal_listing_fields['jobcategory'])){
                        if(isset($wpjobportal_job) && !empty($wpjobportal_job->cat_title)){
                            $wpjobportal_content .= '<span class="wjportal-jobs-data-text wjportal-jobs-data-icon-class-category">
                                            '. wpjobportal::wpjobportal_getVariableValue($wpjobportal_job->cat_title).'
                                        </span>';
                        }
                    }


                    if(isset($wpjobportal_listing_fields['city'])){
                        if(isset($wpjobportal_job) && !empty($wpjobportal_job->location)){
                            $wpjobportal_content .= '<span class="wjportal-jobs-data-text wjportal-jobs-data-icon-class-location">'. $wpjobportal_job->location.'</span>';
                        }
                    }
                    $wpjobportal_content .=     '</div>';
                    $wpjobportal_content .= '</div>';
                    $wpjobportal_content .= '<div class="wjportal-jobs-right-wrp">';
                    $wpjobportal_content .=     '<div class="wjportal-jobs-info">';
                                        // if ($wpjobportal_print[0] == 1) {
                    $wpjobportal_content .=            '<span class="wjportal-job-type" style="background:'.$wpjobportal_job->jobtypecolor.'">';
                    $wpjobportal_content .=                 wpjobportal::wpjobportal_getVariableValue($wpjobportal_job->jobtypetitle);
                    $wpjobportal_content .=             '</span>';
                                        //}
                    $wpjobportal_content .=     '</div>';
                    if(isset($wpjobportal_listing_fields['jobsalaryrange'])){
                        $wpjobportal_content .=     '<div class="wjportal-jobs-info">';
                        $wpjobportal_content .=         '<div class="wjportal-jobs-salary">';
                        $wpjobportal_content .=             wpjobportal::$_common->getSalaryRangeView($wpjobportal_job->salarytype, $wpjobportal_job->salarymin, $wpjobportal_job->salarymax,$wpjobportal_job->currency);
                                                if($wpjobportal_job->salarytype==3 || $wpjobportal_job->salarytype==2) {
                        $wpjobportal_content .=                 '<span class="wjportal-salary-type">'. ' / ' .wpjobportal::wpjobportal_getVariableValue($wpjobportal_job->srangetypetitle).'</span>';
                                                }
                        $wpjobportal_content .=         '</div>';
                        $wpjobportal_content .=     '</div>';
                    }
                    $wpjobportal_content .=     '<div class="wjportal-jobs-info">';
                    $wpjobportal_dateformat =       wpjobportal::$_configuration['date_format'];
                    $wpjobportal_content .=         date_i18n($wpjobportal_dateformat, strtotime($wpjobportal_job->created));
                    $wpjobportal_print =            WPJOBPORTALincluder::getJSModel('job')->checkLinks('jobtype');
                    $wpjobportal_content .=     '</div>';
                    $wpjobportal_content .= '</div>';
                    $wpjobportal_content .= '</div>';
                    $wpjobportal_content .= '</div>';
                    $wpjobportal_content .= '</div>';
                    /*Pop up detail data For Job Ends there*/
                }
                // to handle log errors
                $wpjobportal_text2 = '';
                $wpjobportal_class2 = '';
                $wpjobportal_showlink = true;
                if (wpjobportal::$wpjobportal_theme_chk == 1) {
                    $wpjobportal_content .= '<div class="wpj-jp-popup-left  ">';
                    $wpjobportal_content .=  '<h3 class="wpj-jp-popup-heading">'.$title.'</h3>';
                }
                if (!WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
                    $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
                    $wpjobportal_resumelist = null;
                    $wpjobportal_isjobseeker = WPJOBPORTALincluder::getObjectClass('user')->isjobseeker();
                    $wpjobportal_isemployer = WPJOBPORTALincluder::getObjectClass('user')->isemployer();
                    if (is_numeric($wpjobportal_uid) && $wpjobportal_uid != 0 && $wpjobportal_isjobseeker == true) {
                        $query = "SELECT id,application_title,first_name,last_name FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` WHERE status = 1 AND quick_apply <> 1 AND uid = ". esc_sql($wpjobportal_uid);
                        // code to handle unpublished application_title
                        $wpjobportal_resume_data = wpjobportal::$_db->get_results($query);
                        $wpjobportal_resume_list = array();
                        foreach ($wpjobportal_resume_data as $wpjobportal_single_resume) {
                            $wpjobportal_resume_record = new stdClass();
                            $wpjobportal_resume_record->id = $wpjobportal_single_resume->id;
                            if($wpjobportal_single_resume->application_title != ''){
                                $wpjobportal_resume_record->text = $wpjobportal_single_resume->application_title;
                            }else{
                                $wpjobportal_resume_record->text = $wpjobportal_single_resume->first_name.' '.$wpjobportal_single_resume->last_name;
                            }
                            $wpjobportal_resume_list[] = $wpjobportal_resume_record;
                        }
                        $wpjobportal_resumelist = $wpjobportal_resume_list;
                    }

                    if ($wpjobportal_resumelist != null && $wpjobportal_isjobseeker == true) {
                        $wpjobportal_content .= '<div class="'.esc_attr($this->class_prefix).'-popup-field-wrp">';
                        $wpjobportal_content .= '<div class="'.esc_attr($this->class_prefix).'-popup-field">';
                        $wpjobportal_content .= '<label for="cvid">' . esc_html(__('Apply With Resume', 'wp-job-portal')) . '</label>';
                        $wpjobportal_content .= WPJOBPORTALformfield::select('cvid', $wpjobportal_resumelist, '');
                        $wpjobportal_content .= '</div>';

                        // to add coverletter combo box on popup
                        if(in_array('coverletter', wpjobportal::$_active_addons)){

                            $wpjobportal_cover_letter_list = WPJOBPORTALincluder::getJSModel('coverletter')->getCoverLetterForCombocoverletter($wpjobportal_uid);
                            $wpjobportal_content .= '<div class="'.esc_attr($this->class_prefix).'-popup-field">';
                                $wpjobportal_content .= '<label for="coverletterid">' . esc_html(__('Cover Letter', 'wp-job-portal')) . '</label>';
                            if($wpjobportal_cover_letter_list !='' && !empty($wpjobportal_cover_letter_list)){
                                $wpjobportal_content .= WPJOBPORTALformfield::select('coverletterid', $wpjobportal_cover_letter_list, '');
                            }else{
                                $wpjobportal_content .= esc_html(__('No Cover Letter', 'wp-job-portal'));
                            }
                            $wpjobportal_content .= '</div>';

                        }
                        $wpjobportal_content .= '</div>';
                        if (wpjobportal::$wpjobportal_theme_chk == 1) {
                            if (!isset($wpjobportal_upakid)) {
                                $wpjobportal_upakid = 0;
                            }
                            $wpjobportal_link1 = 'href="#" onclick="jobApply(' . $wpjobportal_jobid . ',' .$wpjobportal_upakid. ','.$wpjobportal_pageid_ajax.',1);"';
                        } else {
                            $wpjobportal_link1 = 'href="#" onclick="jobApply(' . $wpjobportal_jobid . ',' .$wpjobportal_upakid. ','.$wpjobportal_pageid_ajax.');"';
                        }
                        $wpjobportal_link2 = 'href="#" onclick="closePopup();"';
                        $wpjobportal_text1 = esc_html(__('Apply Now', 'wp-job-portal'));
                        $wpjobportal_text2 = '';
                        $wpjobportal_class1 = '';
                        $wpjobportal_class2 = '';
                    } else {
                        $wpjobportal_showlink = false;
                        if ($wpjobportal_isjobseeker == true) {
                            $wpjobportal_content .= '<div class="'.esc_attr($this->class_prefix).'-visitor-msg-wrp">';
                            if (wpjobportal::$wpjobportal_theme_chk == 1) {
                                $wpjobportal_content .= '<span class="'.esc_attr($this->class_prefix).'-visitor-msg">' . esc_html(__('You do not have any resume!', 'wp-job-portal')) . '</span>';
                            } else {
                                $wpjobportal_content .= '<span class="'.esc_attr($this->class_prefix).'-visitor-msg"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/not-loggedin.png" />' . esc_html(__('You do not have any resume!', 'wp-job-portal')) . '</span>';
                            }
                            $wpjobportal_content .= '</div>';
                            $wpjobportal_content .= '   <div class="'.esc_attr($this->class_prefix).'-visitor-msg-btn-wrp">
                                                <a class="'.esc_attr($this->class_prefix).'-visitor-msg-btn wpj-jp-visitor-msg-primary-btn" href="'.wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'addresume', 'wpjobportalpageid'=>$wpjobportal_pageid_ajax)).'" class="resumeaddlink">' . esc_html(__('Add Resume', 'wp-job-portal')) . '</a>
                                            </div>';
                        } elseif($wpjobportal_isemployer == true) {
                            $wpjobportal_content .= '<div class="'.esc_attr($this->class_prefix).'-visitor-msg-wrp">';
                            if (wpjobportal::$wpjobportal_theme_chk == 1) {
                                $wpjobportal_content .= '<span class="'.esc_attr($this->class_prefix).'-visitor-msg"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/not-loggedin.png" />' . esc_html(__('You are employer, you can not apply to job', 'wp-job-portal')) . '!</span>';
                            }else{
                                $wpjobportal_content .= '<span class="'.esc_attr($this->class_prefix).'-visitor-msg">' . esc_html(__('You are employer, you can not apply to job', 'wp-job-portal')) . '!</span>';
                            }
                            $wpjobportal_content .= '</div>';
                        } else {
                            $wpjobportal_showlink = true;
                            $wpjobportal_content .= '<div class="'.esc_attr($this->class_prefix).'-visitor-msg-wrp">';
                            if (wpjobportal::$wpjobportal_theme_chk == 1) {
                                $wpjobportal_content .= '<span class="'.esc_attr($this->class_prefix).'-visitor-msg">' . esc_html(__('You do not have any role', 'wp-job-portal')) . '!</span>';
                            } else {
                                $wpjobportal_content .= '<span class="'.esc_attr($this->class_prefix).'-visitor-msg"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/not-loggedin.png" />' . esc_html(__('You do not have any role', 'wp-job-portal')) . '!</span>';
                            }
                            $wpjobportal_content .= '</div>';
                            $wpjobportal_link1 = 'href="' . esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'common','wpjobportallt'=>'newinwpjobportal', 'wpjobportalid-jobid'=>$wpjobportal_jobid, 'wpjobportalpageid'=>$wpjobportal_pageid_ajax))) . '" target="_blank" ';
                            $wpjobportal_text1 = esc_html(__('Select Role', 'wp-job-portal'));
                            // $wpjobportal_link2 = 'href="#" onclick="closePopup();"';
                            // $wpjobportal_text2 = esc_html(__('Close', 'wp-job-portal'));
                        }
                    }
                } else {
                    $wpjobportal_msgapply = "You are not a logged in member. Please select below option";
                    $wpjobportal_content .= '<div class="'.esc_attr($this->class_prefix).'-visitor-msg-wrp">';
                    if (wpjobportal::$wpjobportal_theme_chk == 1) {
                        $wpjobportal_content .= '<span class="'.esc_attr($this->class_prefix).'-visitor-msg">' . wpjobportal::wpjobportal_getVariableValue($wpjobportal_msgapply) . '</span>';
                    } else {
                        $wpjobportal_content .= '<span class="'.esc_attr($this->class_prefix).'-visitor-msg"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/not-loggedin.png" />' . wpjobportal::wpjobportal_getVariableValue($wpjobportal_msgapply) . '</span>';
                    }
                    $wpjobportal_content .= '</div>';
                    $wpjobportal_link1 = 'href="' . wp_nonce_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobapply', 'action'=>'wpjobportaltask', 'task'=>'jobapplyasvisitor', 'wpjobportalid-jobid'=>$wpjobportal_jobid, 'wpjobportalpageid'=>$wpjobportal_pageid_ajax)),'wpjobportal_job_apply_nonce') . '"';
                    $wpjobportal_thiscpurl = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'viewjob', 'wpjobportalid'=>$wpjobportal_jobid, 'wpjobportalpageid'=>$wpjobportal_pageid_ajax));
                    $wpjobportal_thiscpurl = wpjobportalphplib::wpJP_safe_encoding($wpjobportal_thiscpurl);
                    $wpjobportal_link2 = 'href="'.esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'wpjobportal', 'wpjobportallt'=>'login', 'wpjobportalredirecturl'=>$wpjobportal_thiscpurl, 'wpjobportalpageid'=>$wpjobportal_pageid_ajax))).'"';
                    $wpjobportal_text1 = esc_html(__('Apply as visitor', 'wp-job-portal'));
                    $wpjobportal_text2 = esc_html(__('Login', 'wp-job-portal'));
                    $wpjobportal_class1 = 'login';
                    $wpjobportal_class2 = 'applyvisitor';
                }
                $wpjobportal_jsnext = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job','wpjobportallt'=>'viewjob','wpjobportalid'=>$wpjobportal_jobid,'wpjobportalpageid'=>$wpjobportal_pageid_ajax));
                $wpjobportal_visitor_can_apply_to_job = wpjobportal::$_config->getConfigurationByConfigName('visitor_can_apply_to_job');
                if ($wpjobportal_showlink == true) {
                    $wpjobportal_content .= '   <div class="'.esc_attr($this->class_prefix).'-visitor-msg-btn-wrp">';
                     if($wpjobportal_text2 != ''){
                        $wpjobportal_content .= ' <div class="quickviewbutton">
                                        <a ' . $wpjobportal_link2 . ' class="'.esc_attr($this->class_prefix).'-visitor-msg-btn ' . $wpjobportal_class1 . '" >' . $wpjobportal_text2 . '</a>';
                     }
                    if(WPJOBPORTALincluder::getObjectClass('user')->isguest()){
                        if($wpjobportal_visitor_can_apply_to_job == 1){
                            $wpjobportal_content .= apply_filters('wpjobportal_addons_apply_as_visitor',false,$wpjobportal_link1,$wpjobportal_class2,$wpjobportal_text1);
                        }
                    }else{
                        $wpjobportal_content .= '<a ' . $wpjobportal_link1 . ' class="'.esc_attr($this->class_prefix).'-visitor-msg-btn login' . $wpjobportal_class2 . '" id="apply-now-btn" >' . $wpjobportal_text1 . '</a>';
                    }                    $wpjobportal_content .= ' </div>';
                }
                $wpjobportal_isemployer = WPJOBPORTALincluder::getObjectClass('user')->isemployer();
                $wpjobportal_content .= apply_filters('wpjobportal_addons_social_appy_job',false,$wpjobportal_config_array,$wpjobportal_isemployer,$wpjobportal_jobid);
                $wpjobportal_content .= '</div>';
                if (wpjobportal::$wpjobportal_theme_chk == 1) {
                    $wpjobportal_content .= '</div>'; /// end left wrp
                    $wpjobportal_content .= '</div>'; /// end cnt wrp
                }
            } else {
                $title = esc_html(__('No record found', 'wp-job-portal'));
                $wpjobportal_content = '<h1>' . esc_html(__('No record found', 'wp-job-portal')) . '</h1>';
            }
        $title = mb_convert_encoding($title, 'UTF-8', mb_detect_encoding($title));
        $wpjobportal_content = mb_convert_encoding($wpjobportal_content, 'UTF-8', mb_detect_encoding($wpjobportal_content));
        $wpjobportal_array = array('title' => $title, 'wpjobportal_content' => $wpjobportal_content);
        return wp_json_encode($wpjobportal_array);
    }

    function jobapplyjobmanager(){
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('js_nonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wp-job-portal-nonce') ) {
            die( 'Security check Failed' );
        }
        $wpjobportal_jobid = WPJOBPORTALrequest::getVar('jobid');
        $return_val=$this->jobapply(1);
        if($return_val===1){
            $wpjobportal_msg = '<div id="'.esc_attr($this->class_prefix).'-notification-not-ok"><div id="'.esc_attr($this->class_prefix).'-popup_message">
            <img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/unpublish.png"/><spam class="'.esc_attr($this->class_prefix).'-popup_msg_txt">' . esc_html(__("please select a resume first", 'wp-job-portal')) . '</spam><button class="applynow-closebutton" onclick="wpjobportalClosePopup(1);" ><img src="'.esc_url(WPJOBPORTAL_PLUGIN_URL).'/includes/images/popupcloseicon.png"/>'.esc_html(__('Close','wp-job-portal')).'</button></div></div>';
        }elseif($return_val === 2) {
            $wpjobportal_msg = '<div id="'.esc_attr($this->class_prefix).'-notification-ok"><div id="'.esc_attr($this->class_prefix).'-popup_message">
            <img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/approve.png"/><spam class="'.esc_attr($this->class_prefix).'-popup_msg_txt">' . esc_html(__("You have already applied this job", 'wp-job-portal')) . '</spam><button class="applynow-closebutton" onclick="wpjobportalClosePopup(1);" ><img src="'.esc_url(WPJOBPORTAL_PLUGIN_URL).'/includes/images/popupcloseicon.png"/>'.esc_html(__('Close','wp-job-portal')).'</button></div></div>';
        }elseif($return_val == WPJOBPORTAL_SAVE_ERROR) {
            $wpjobportal_msg = '<div id="'.esc_attr($this->class_prefix).'-notification-not-ok"><div id="'.esc_attr($this->class_prefix).'-popup_message">
            <img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/unpublish.png"/><spam class="'.esc_attr($this->class_prefix).'-popup_msg_txt">' . esc_html(__("Failed while performing this action", 'wp-job-portal')) . '</spam><button class="applynow-closebutton" onclick="wpjobportalClosePopup(1);" ><img src="'.esc_url(WPJOBPORTAL_PLUGIN_URL).'/includes/images/popupcloseicon.png"/>'.esc_html(__('Close','wp-job-portal')).'</button></div></div>';
        }elseif($return_val == 3) { //payment
            $wpjobportal_arr = array('wpjobportalme'=>'purchasehistory','wpjobportallt'=>'payjobapply','wpjobportalid'=>$wpjobportal_jobid,'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid());
            $wpjobportal_msg = '<div id="'.esc_attr($this->class_prefix).'-notification-ok"><div id="'.esc_attr($this->class_prefix).'-popup_message">
            <img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/approve.png"/><spam class="'.esc_attr($this->class_prefix).'-popup_msg_txt"><a href="'.esc_url(wpjobportal::wpjobportal_makeUrl($wpjobportal_arr)).'">' . esc_html(__("Job has been Pending Due to Payment", 'wp-job-portal')) . '</a></spam><button class="applynow-closebutton" onclick="wpjobportalClosePopup(1);" ><img src="'.esc_url(WPJOBPORTAL_PLUGIN_URL).'/includes/images/popupcloseicon.png"/>'.esc_html(__('Close','wp-job-portal')).'</button></div></div>';
        }elseif($return_val == WPJOBPORTAL_SAVED) {
            $wpjobportal_msg = '<div id="'.esc_attr($this->class_prefix).'-notification-ok"><div id="'.esc_attr($this->class_prefix).'-popup_message">
            <img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/approve.png"/><spam class="'.esc_attr($this->class_prefix).'-popup_msg_txt">' . esc_html(__("Job has been applied", 'wp-job-portal')) . '</spam><button class="applynow-closebutton" onclick="wpjobportalClosePopup(1);" ><img src="'.esc_url(WPJOBPORTAL_PLUGIN_URL).'/includes/images/popupcloseicon.png"/>'.esc_html(__('Close','wp-job-portal')).'</button></div></div>';
        }
        return $wpjobportal_msg;
    }

    function jobapply($wpjobportal_themecall=null) {
        // $wpjobportal_nonce = WPJOBPORTALrequest::getVar('js_nonce');
        // if (! wp_verify_nonce( $wpjobportal_nonce, 'wp-job-portal-nonce') ) {
        //     die( 'Security check Failed' );
        // }
        $wpjobportal_jobid = WPJOBPORTALrequest::getVar('jobid');
        $cvid = WPJOBPORTALrequest::getVar('cvid');
        $coverletterid = WPJOBPORTALrequest::getVar('coverletterid');
        $wpjobportal_upkid = WPJOBPORTALrequest::getVar('upkid');
        // quick apply
        $quick_apply = WPJOBPORTALrequest::getVar('quick_apply','',0);
        $message = WPJOBPORTALrequest::getVar('message','','');
        $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        $wpjobportal_user = WPJOBPORTALincluder::getObjectClass('user');
        $wpjobportal_action_status = 1;

        if (! is_numeric($cvid)) {
            if(null !=$wpjobportal_themecall) return 1;
            $wpjobportal_msg = '<div id="notification-not-ok"><label id="popup_message"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/unpublish.png"/>' . esc_html(__("please select a resume first", 'wp-job-portal')) . '</label><button class="applynow-closebutton" onclick="closePopup();" ><img src="'.esc_url(WPJOBPORTAL_PLUGIN_URL).'/includes/images/popupcloseicon.png"/>'.esc_html(__('Close','wp-job-portal')).'</button></div>';
            return $wpjobportal_msg;
        }
        // spam check ignoreed in case of quick job apply
        if($quick_apply == 0){
            $wpjobportal_isspam = $this->validateJobFilters($wpjobportal_jobid , $cvid);
            if($wpjobportal_isspam === false){
                return WPJOBPORTAL_SAVE_ERROR;
            }elseif($wpjobportal_isspam == 1){
                $wpjobportal_action_status = 2;
            }
        }

        if(!WPJOBPORTALincluder::getJSModel('resume')->getIfResumeOwner($cvid)){
            $wpjobportal_msg = '<div id="notification-not-ok"><label id="popup_message"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/unpublish.png"/>' . esc_html(__("Failed while performing this action", 'wp-job-portal')) . '</label><button class="applynow-closebutton" onclick="closePopup();" ><img src="'.esc_url(WPJOBPORTAL_PLUGIN_URL).'/includes/images/popupcloseicon.png"/>'.esc_html(__('Close','wp-job-portal')).'</button></div>';
            return $wpjobportal_msg;
        }

        $wpjobportal_data = array();
        $wpjobportal_data['jobid'] = $wpjobportal_jobid;
        $wpjobportal_data['cvid'] = $cvid;
        $wpjobportal_data['coverletterid'] = $coverletterid;
        $wpjobportal_data['uid'] = $wpjobportal_uid;
        $wpjobportal_data['action_status'] = $wpjobportal_action_status;

        // quick apply columns
        $wpjobportal_data['apply_message'] = $message;
        $wpjobportal_data['quick_apply'] = $quick_apply;

        $wpjobportal_data['apply_date'] = gmdate('Y-m-d H:i:s');
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('jobapply');
        $wpjobportal_result = array();
        if($quick_apply != 1 && !WPJOBPORTALincluder::getObjectClass('user')->isguest()){// if current user is guest ignore the already applied check.(it returns error on missing uid value check)
            $alreadycheck = $this->checkAlreadyAppliedJob($wpjobportal_data['jobid'], $wpjobportal_data['uid']);
            if ($alreadycheck == false) {
                if(null !=$wpjobportal_themecall) return 2;
                $wpjobportal_msg = '<div id="notification-ok"><label id="popup_message"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/approve.png"/>' . esc_html(__("You have already applied this job", 'wp-job-portal')) . '</label><button class="applynow-closebutton" onclick="closePopup();" ><img src="'.esc_url(WPJOBPORTAL_PLUGIN_URL).'/includes/images/popupcloseicon.png"/>'.esc_html(__('Close','wp-job-portal')).'</button></div>';
                return $wpjobportal_msg;
            }
        }
        $return = WPJOBPORTAL_SAVED;
        $wpjobportal_visitor_can_apply_to_job = wpjobportal::$_config->getConfigurationByConfigName('visitor_can_apply_to_job');
        // package system checks need to ignored for visitor job apply
        $wpjobportal_submitType = wpjobportal::$_config->getConfigValue('submission_type'); // was showing as undefined in below cases
        if(WPJOBPORTALincluder::getObjectClass('user')->isguest() &&  $wpjobportal_visitor_can_apply_to_job == 1){
            $wpjobportal_data['status'] = 1;// job apply status 1 in case of visitor apply on job
        }else{
            if(in_array('credits', wpjobportal::$_active_addons)){
                if($wpjobportal_submitType == 2){
                    # Perlisting
                    // in case of per listing submission mode
                    $wpjobportal_price_check = WPJOBPORTALincluder::getJSModel('credits')->checkIfPriceDefinedForAction('job_apply');
                    if($wpjobportal_price_check == 1){ // if price is defined then status 3
                        $wpjobportal_data['status'] = 3;
                    }else{ // if price not defined then status set to auto approve configuration
                        $wpjobportal_data['status'] = 1;
                    }
                }elseif ($wpjobportal_submitType == 1) {
                    $wpjobportal_data['status'] = 1;
                }elseif ($wpjobportal_submitType == 3) {
                    if($wpjobportal_uid == '' || $wpjobportal_uid == 0){ // to handle different possible cases
                        $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
                    }
                    // check if there is package defined for current user
                    $wpjobportal_no_package_needed = 0;

                    $wpjobportal_result = WPJOBPORTALincluder::getJSModel('credits')->checkIfPackageDefinedForRole(2); //2 for job seeker
                    if($wpjobportal_result == 0){ // 0 means no package found. so allow the action.
                        $wpjobportal_no_package_needed = 1;
                    }

                    if($wpjobportal_no_package_needed == 0){
                        $wpjobportal_package = WPJOBPORTALincluder::getJSModel('purchasehistory')->getUserPackageById($wpjobportal_upkid,$wpjobportal_uid,'remjobapply');
                        if( !$wpjobportal_package ){
                            return WPJOBPORTAL_SAVE_ERROR;
                        }
                        if( $wpjobportal_package->expired ){
                            return WPJOBPORTAL_SAVE_ERROR;
                        }
                        //if job apply are not unlimited & there is no remaining left
                        if( $wpjobportal_package->jobapply!=-1 && !$wpjobportal_package->remjobapply ){ //-1 = unlimited
                            return WPJOBPORTAL_SAVE_ERROR;
                        }
                        $wpjobportal_data['userpackageid'] = $wpjobportal_upkid;
                    }
                    $wpjobportal_data['status'] = 1;
                }
            }else{
                if(isset($wpjobportal_data) && empty($wpjobportal_data['status'])){
                    $wpjobportal_data['status'] = 1;
                }
            }
        }

        $wpjobportal_data = wpjobportal::wpjobportal_sanitizeData($wpjobportal_data);
        if (!$wpjobportal_row->bind($wpjobportal_data)) {
            $return = WPJOBPORTAL_SAVE_ERROR;
        }
        if (!$wpjobportal_row->store()) {
            $return = WPJOBPORTAL_SAVE_ERROR;
        }
        $wpjobportal_job_apply_id = $wpjobportal_row->id;
        // needed for per listing mode redirect
        wpjobportal::$_data['job_apply_id'] = $wpjobportal_job_apply_id;
        // //if($quick_apply == 1){ // package system & apply message ignored in case of quickapply
        //     if ($return != WPJOBPORTAL_SAVE_ERROR) {
        //         $this->sendMail($wpjobportal_jobid,$cvid,$wpjobportal_job_apply_id);
        //         //return $return;
        //     }
        // //}

        if(in_array('credits', wpjobportal::$_active_addons)){
            if($wpjobportal_submitType == 3 &&  WPJOBPORTALincluder::getObjectClass('user')->isjobseeker() && $wpjobportal_no_package_needed == 0){
            # Transaction For Job Apply--
                $wpjobportal_trans = WPJOBPORTALincluder::getJSTable('transactionlog');
                $wpjobportal_arr = array();
                $wpjobportal_arr['uid'] = $wpjobportal_uid;
                $wpjobportal_arr['userpackageid'] = $wpjobportal_upkid;
                $wpjobportal_arr['recordid'] = $wpjobportal_row->id;
                $wpjobportal_arr['type'] = 'jobapply';
                $wpjobportal_arr['created'] = current_time('mysql');
                $wpjobportal_arr['status'] = 1;
                $wpjobportal_trans->bind($wpjobportal_arr);
                $wpjobportal_trans->store();
            }
        }

        if ($return != WPJOBPORTAL_SAVE_ERROR) {
            if($wpjobportal_submitType == 2 && in_array('credits', wpjobportal::$_active_addons)){
                if(wpjobportal::$_config->getConfigValue('job_jobapply_price_perlisting') > 0){
                    $wpjobportal_arr = array('wpjobportalme'=>'purchasehistory','wpjobportallt'=>'payjobapply','wpjobportalid'=>$wpjobportal_row->jobid,'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid());
                    $wpjobportal_msg = '<div id="notification-ok"><label id="popup_message"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/approve.png"/>' . esc_html(__("Job has been Pending Due to Payment", 'wp-job-portal')) . '</label><a class="wjportal-job-act-btn" href='. esc_url(wpjobportal::wpjobportal_makeUrl($wpjobportal_arr)).' title='. esc_attr(__('make payment','wp-job-portal')).'>
                                '. esc_html(__('Make Payment To Apply', 'wp-job-portal')).'
                        </a>
                    </div>';
                    $return = 3;
                }else{
                    $wpjobportal_msg = '<div id="notification-ok"><label id="popup_message"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/approve.png"/>' . esc_html(__("Job has been applied", 'wp-job-portal')) . '</label><button class="applynow-closebutton" onclick="closePopup();" ><img src="'.esc_url(WPJOBPORTAL_PLUGIN_URL).'/includes/images/popupcloseicon.png"/>'.esc_html(__('Close','wp-job-portal')).'</button></div>';
                    $this->sendMail($wpjobportal_jobid,$cvid,$wpjobportal_job_apply_id);
                }
            }else{
                $wpjobportal_msg = '<div id="notification-ok"><label id="popup_message"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/approve.png"/>' . esc_html(__("Job has been applied", 'wp-job-portal')) . '</label><button class="applynow-closebutton" onclick="closePopup();" ><img src="'.esc_url(WPJOBPORTAL_PLUGIN_URL).'/includes/images/popupcloseicon.png"/>'.esc_html(__('Close','wp-job-portal')).'</button></div>';
                $this->sendMail($wpjobportal_jobid,$cvid,$wpjobportal_job_apply_id);
            }
            $wpjobportal_uid = wpjobportal::$_common->getUidByObjectId('job', $wpjobportal_row->jobid);
        } else {
            $wpjobportal_msg = '<div id="notification-not-ok"><label id="popup_message"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/unpublish.png"/>' . esc_html(__("Failed while performing this action", 'wp-job-portal')) . '</label><button class="applynow-closebutton" onclick="closePopup();" ><img src="'.esc_url(WPJOBPORTAL_PLUGIN_URL).'/includes/images/popupcloseicon.png"/>'.esc_html(__('Close','wp-job-portal')).'</button></div>';
        }
        if(null !=$wpjobportal_themecall) return $return;
        return $wpjobportal_msg;
    }

    private function sendMail($wpjobportal_jobid, $wpjobportal_resumeid,$wpjobportal_jobapplyid = '') {
        //this code is not moved into email template model bcz of its high complextiy and low usage

        if ($wpjobportal_jobid)
            if ((is_numeric($wpjobportal_jobid) == false) || ($wpjobportal_jobid == 0) || ($wpjobportal_jobid == ''))
                return false;
        if ($wpjobportal_resumeid)
            if ((is_numeric($wpjobportal_resumeid) == false) || ($wpjobportal_resumeid == 0) || ($wpjobportal_resumeid == ''))
                return false;
        if ($wpjobportal_jobapplyid)
            if ((is_numeric($wpjobportal_jobapplyid) == false) || ($wpjobportal_jobapplyid == 0) || ($wpjobportal_jobapplyid == ''))
                return false;


        $wpjobportal_jobquery = "SELECT company.name AS companyname, company.contactemail AS email, job.title, job.sendemail
                        ,CONCAT(user.first_name,' ',user.last_name) AS username,user.emailaddress AS useremail
            FROM `".wpjobportal::$_db->prefix."wj_portal_jobs` AS job
            LEFT JOIN `".wpjobportal::$_db->prefix."wj_portal_companies` AS company ON company.id = job.companyid
            LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_users` AS user ON user.id = job.uid
            WHERE job.id = ". esc_sql($wpjobportal_jobid);

        $wpjobportal_jobuser = wpjobportaldb::get_row($wpjobportal_jobquery);

        $wpjobportal_userquery = "SELECT CONCAT(first_name,' ',last_name) AS name, email_address AS email,application_title FROM `".wpjobportal::$_db->prefix."wj_portal_resume`
            WHERE id = " . esc_sql($wpjobportal_resumeid);
        $wpjobportal_user = wpjobportaldb::get_row($wpjobportal_userquery);
        $wpjobportal_emailconfig = wpjobportal::$_config->getConfigByFor('email');

//MAIL TO ADMIN ON JOBAPPLY
        $wpjobportal_templatefor = 'jobapply-jobapply';
        $query = "SELECT template.* FROM `".wpjobportal::$_db->prefix."wj_portal_emailtemplates` AS template WHERE template.templatefor = '" . esc_sql($wpjobportal_templatefor) . "'";

        $wpjobportal_template = wpjobportaldb::get_row($query);
        $wpjobportal_msgSubject = $wpjobportal_template->subject;
        $wpjobportal_msgBody = $wpjobportal_template->body;

        $ApplicantName = $wpjobportal_user->name;
        $EmployerEmail = $wpjobportal_emailconfig['adminemailaddress'];


        $JobTitle = $wpjobportal_jobuser->title;
        $EmployerName = $wpjobportal_jobuser->username;

        $Emailtoemployer = $wpjobportal_jobuser->email;
        if ($Emailtoemployer == '') {
            $Emailtoemployer = $wpjobportal_jobuser->useremail;
        }

        $siteTitle = wpjobportal::$_config->getConfigValue('title');

        $wpjobportal_msgSubject = wpjobportalphplib::wpJP_str_replace('{JOBSEEKER_NAME}', $ApplicantName, $wpjobportal_msgSubject);
        $wpjobportal_msgSubject = wpjobportalphplib::wpJP_str_replace('{EMPLOYER_NAME}', $EmployerName, $wpjobportal_msgSubject);
        $wpjobportal_msgSubject = wpjobportalphplib::wpJP_str_replace('{JOB_TITLE}', $JobTitle, $wpjobportal_msgSubject);
        $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{JOBSEEKER_NAME}', $ApplicantName, $wpjobportal_msgBody);
        $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{EMPLOYER_NAME}', $EmployerName, $wpjobportal_msgBody);
        $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{JOB_TITLE}', $JobTitle, $wpjobportal_msgBody);

        // fatal error cause setting up string type variable as a array
        // $wpjobportal_msgSubject['{SITETITLE}'] = $siteTitle;
        // $wpjobportal_msgBody['{SITETITLE}'] = $siteTitle;
        // $wpjobportal_msgBody['{EMAIL}'] = $EmployerEmail;
        // $wpjobportal_msgBody['{CURRENT_YEAR}'] = gmdate('Y');

        $wpjobportal_msgSubject = wpjobportalphplib::wpJP_str_replace('{SITETITLE}', $siteTitle, $wpjobportal_msgSubject);
        $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{SITETITLE}', $siteTitle, $wpjobportal_msgBody);
        $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{EMAIL}', $EmployerEmail, $wpjobportal_msgBody);
        $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{CURRENT_YEAR}', gmdate('Y'), $wpjobportal_msgBody);
        if(in_array('coverletter', wpjobportal::$_active_addons)){
            $wpjobportal_jobquery = "SELECT jobapply.coverletterid
            FROM `".wpjobportal::$_db->prefix."wj_portal_jobapply` AS jobapply
            WHERE jobapply.id = " . esc_sql($wpjobportal_jobapplyid);
            $coverletterid = wpjobportaldb::get_var($wpjobportal_jobquery);
            $coverletdata = WPJOBPORTALincluder::getJSModel('coverletter')->getCoverLetterTitleDescFromID($coverletterid);
            if(!empty($coverletdata) && isset($coverletdata->description)){
                $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{COVER_LETTER_DESCRIPTION}', $coverletdata->description, $wpjobportal_msgBody);
            }
        }else{
            $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{COVER_LETTER_DESCRIPTION}', '&nbsp;', $wpjobportal_msgBody);
        }

        $wpjobportal_emailstatus = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getEmailTemplateStatus('jobapply_jobapply');
        $wpjobportal_senderName = $wpjobportal_emailconfig['mailfromname'];
        $wpjobportal_senderEmail = $wpjobportal_emailconfig['mailfromaddress'];
        $wpjobportal_resume_data = $this->prepareResumeDataForEmployer($wpjobportal_resumeid);
        if (wpjobportalphplib::wpJP_strstr($wpjobportal_msgBody, '{RESUME_DATA}')) {
            $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{RESUME_DATA}', $wpjobportal_resume_data, $wpjobportal_msgBody);
        }
            $parsed_url_admin = esc_url_raw(admin_url('admin.php?page=wpjobportal_resume&wpjobportallt=viewresume&wpjobportalid='.esc_attr($wpjobportal_resumeid)));
            //$wpjobportal_applied_resume_link_admin = '<br><a href="' . esc_url($parsed_url_admin) . '" target="_blank" >' . esc_html(__('Resume','wp-job-portal')) . '</a>';
            $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{RESUME_LINK}', $parsed_url_admin , $wpjobportal_msgBody);
            $recevierEmail = $EmployerEmail;
            $wpjobportal_subject = $wpjobportal_msgSubject;
            $body = $wpjobportal_msgBody;
        if ($wpjobportal_emailstatus->admin == 1) {
            $wpjobportal_datadirectory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
            $wpjobportal_resumeFiles = WPJOBPORTALincluder::getJSModel('resume')->getResumeFilesByResumeId($wpjobportal_resumeid);
            $attachments = '';
            if (!empty($wpjobportal_resumeFiles)) {
                $attachments = array();
                foreach ($wpjobportal_resumeFiles as $wpjobportal_resumeFile) {
                    $wpjobportal_iddir = 'resume_' . $wpjobportal_resumeid;
                    $wpjobportal_wpdir = wp_upload_dir();
                    $wpjobportal_path = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_datadirectory;
                    $wpjobportal_path = $wpjobportal_path . '/data/jobseeker/' . $wpjobportal_iddir . '/resume/' . $wpjobportal_resumeFile->filename;
                    $attachments[] = $wpjobportal_path;
                }
            }
            wpjobportal::$_common->sendEmail($recevierEmail, $wpjobportal_subject, $body, $wpjobportal_senderEmail, $wpjobportal_senderName, $attachments);
        }
    //MAIL TO EMPLOYER
        $wpjobportal_templatefor = 'jobapply-employer';
        $query = "SELECT template.* FROM `".wpjobportal::$_db->prefix."wj_portal_emailtemplates` AS template WHERE template.templatefor = '" . esc_sql($wpjobportal_templatefor) . "'";

        $wpjobportal_template = wpjobportaldb::get_row($query);
        $wpjobportal_msgSubject = $wpjobportal_template->subject;
        $wpjobportal_msgBody = $wpjobportal_template->body;

        $ApplicantName = $wpjobportal_user->name;
        $EmployerEmail = $wpjobportal_jobuser->email;
        if ($EmployerEmail == '') {
            $EmployerEmail = $wpjobportal_jobuser->useremail;
        }
        //$EmployerName = $wpjobportal_jobuser->companyname;
        $EmployerName = $wpjobportal_jobuser->username;
        $JobTitle = $wpjobportal_jobuser->title;
        $siteTitle = wpjobportal::$_config->getConfigValue('title');
        $wpjobportal_msgSubject = wpjobportalphplib::wpJP_str_replace('{JOBSEEKER_NAME}', $ApplicantName, $wpjobportal_msgSubject);
        $wpjobportal_msgSubject = wpjobportalphplib::wpJP_str_replace('{JOB_TITLE}', $JobTitle, $wpjobportal_msgSubject);
        $wpjobportal_msgSubject = wpjobportalphplib::wpJP_str_replace('{SITETITLE}', $siteTitle, $wpjobportal_msgSubject);
        $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{SITETITLE}', $siteTitle, $wpjobportal_msgBody);
        $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{JOBSEEKER_NAME}', $ApplicantName, $wpjobportal_msgBody);
        $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{JOB_TITLE}', $JobTitle, $wpjobportal_msgBody);
        $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{EMPLOYER_NAME}', $EmployerName, $wpjobportal_msgBody);
        $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{EMAIL}', $EmployerEmail, $wpjobportal_msgBody);
        $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{CURRENT_YEAR}', gmdate('Y'), $wpjobportal_msgBody);
        if(in_array('coverletter', wpjobportal::$_active_addons)){
            $wpjobportal_jobquery = "SELECT jobapply.coverletterid
            FROM `".wpjobportal::$_db->prefix."wj_portal_jobapply` AS jobapply
            WHERE jobapply.id = " . esc_sql($wpjobportal_jobapplyid);
            $coverletterid = wpjobportaldb::get_var($wpjobportal_jobquery);
            $coverletdata = WPJOBPORTALincluder::getJSModel('coverletter')->getCoverLetterTitleDescFromID($coverletterid);
            if(!empty($coverletdata) && isset($coverletdata->description)){
                $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{COVER_LETTER_DESCRIPTION}', $coverletdata->description, $wpjobportal_msgBody);
            }
        }else{
            $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{COVER_LETTER_DESCRIPTION}', '&nbsp;', $wpjobportal_msgBody);
        }

        //$wpjobportal_msgBody['{EMAIL}'] = $EmployerEmail;
        $wpjobportal_emailconfig = wpjobportal::$_config->getConfigByFor('email');
        $wpjobportal_senderName = $wpjobportal_emailconfig['mailfromname'];
        $wpjobportal_senderEmail = $wpjobportal_emailconfig['mailfromaddress'];
        $wpjobportal_resume_data = $this->prepareResumeDataForEmployer($wpjobportal_resumeid);
        if (wpjobportalphplib::wpJP_strstr($wpjobportal_msgBody, '{RESUME_DATA}')) {
            $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{RESUME_DATA}', $wpjobportal_resume_data, $wpjobportal_msgBody);
        }
        $parsed_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'viewresume','wpjobportalid'=>$wpjobportal_resumeid,'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));

        // to handle job apply action status
        $wpjobportal_jobquery = "SELECT jobapply.action_status
            FROM `".wpjobportal::$_db->prefix."wj_portal_jobapply` AS jobapply
            WHERE jobapply.id = " . esc_sql($wpjobportal_jobapplyid);
        $wpjobportal_job_apply_action_status = wpjobportaldb::get_var($wpjobportal_jobquery);

        $wpjobportal_applied_resume_status = '';
        if(isset($wpjobportal_job_apply_action_status) &&  $wpjobportal_job_apply_action_status != ''){
            switch ($wpjobportal_job_apply_action_status) {
                case 1:
                    $wpjobportal_applied_resume_status = esc_html(__('Inbox','wp-job-portal'));
                break;
                case 2:
                    $wpjobportal_applied_resume_status = esc_html(__('Spam','wp-job-portal'));
                break;
                case 3:
                    $wpjobportal_applied_resume_status = esc_html(__('Hired','wp-job-portal'));
                break;
                case 4:
                    $wpjobportal_applied_resume_status = esc_html(__('Rejected','wp-job-portal'));
                break;
                case 5:
                    $wpjobportal_applied_resume_status = esc_html(__('Short listed','wp-job-portal'));
                break;
            }
        }


        //$wpjobportal_applied_resume_link = '<br><a href="' . esc_url($parsed_url) . '" target="_blank" >' . esc_html(__('Resume','wp-job-portal')) . '</a>';
        $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{RESUME_LINK}', $parsed_url, $wpjobportal_msgBody);
        $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{RESUME_APPLIED_STATUS}', $wpjobportal_applied_resume_status, $wpjobportal_msgBody);
        $recevierEmail = $EmployerEmail;
        $wpjobportal_subject = $wpjobportal_msgSubject;
        $body = $wpjobportal_msgBody;
        if ($wpjobportal_jobuser->sendemail == 1 && $wpjobportal_emailstatus->employer == 1) {
            $attachments = '';
            wpjobportal::$_common->sendEmail($recevierEmail, $wpjobportal_subject, $body, $wpjobportal_senderEmail, $wpjobportal_senderName, $attachments);
        }elseif ($wpjobportal_jobuser->sendemail == 2 && $wpjobportal_emailstatus->employer == 1) {
            $wpjobportal_datadirectory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
            $wpjobportal_resumeFiles = WPJOBPORTALincluder::getJSModel('resume')->getResumeFilesByResumeId($wpjobportal_resumeid);
            if (!empty($wpjobportal_resumeFiles) && isset($wpjobportal_resumeFiles)) {
                $attachments = array();
                foreach ($wpjobportal_resumeFiles as $wpjobportal_resumeFile) {
                    $wpjobportal_iddir = 'resume_' . $wpjobportal_resumeid;
                    $wpjobportal_wpdir = wp_upload_dir();
                    $wpjobportal_path = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_datadirectory;
                    $wpjobportal_path = $wpjobportal_path . '/data/jobseeker/' . $wpjobportal_iddir . '/resume/' . $wpjobportal_resumeFile->filename;
                    $attachments[] = $wpjobportal_path;
                }
            }
            wpjobportal::$_common->sendEmail($recevierEmail, $wpjobportal_subject, $body, $wpjobportal_senderEmail, $wpjobportal_senderName, $attachments);
        }

    // MAIL TO JOB SEEKER
        $wpjobportal_templatefor = 'jobapply-jobseeker';
        $query = "SELECT template.* FROM `".wpjobportal::$_db->prefix."wj_portal_emailtemplates` AS template WHERE template.templatefor = '" . esc_sql($wpjobportal_templatefor) . "'";
        $wpjobportal_template = wpjobportaldb::get_row($query);
        $wpjobportal_msgSubject = $wpjobportal_template->subject;
        $wpjobportal_msgBody = $wpjobportal_template->body;

        $wpjobportal_applied_resume_status = '';
        $wpjobportal_jobquery = "SELECT jobapply.action_status
            FROM `".wpjobportal::$_db->prefix."wj_portal_jobapply` AS jobapply
            WHERE jobapply.id = " . esc_sql($wpjobportal_jobapplyid);
        $wpjobportal_job_apply_action_status = wpjobportaldb::get_var($wpjobportal_jobquery);

        if(isset($wpjobportal_job_apply_action_status) &&  $wpjobportal_job_apply_action_status != ''){
            switch ($wpjobportal_job_apply_action_status) {
                case 1:
                    $wpjobportal_applied_resume_status = esc_html(__('Inbox','wp-job-portal'));
                break;
                case 2:
                    $wpjobportal_applied_resume_status = esc_html(__('Spam','wp-job-portal'));
                break;
                case 3:
                    $wpjobportal_applied_resume_status = esc_html(__('Hired','wp-job-portal'));
                break;
                case 4:
                    $wpjobportal_applied_resume_status = esc_html(__('Rejected','wp-job-portal'));
                break;
                case 5:
                    $wpjobportal_applied_resume_status = esc_html(__('Short listed','wp-job-portal'));
                break;
            }
        }
        $wpjobportal_msgSubject = wpjobportalphplib::wpJP_str_replace('{JOB_TITLE}', $JobTitle, $wpjobportal_msgSubject);
        $wpjobportal_msgSubject = wpjobportalphplib::wpJP_str_replace('{SITETITLE}', $siteTitle, $wpjobportal_msgSubject);
        $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{SITETITLE}', $siteTitle, $wpjobportal_msgBody);
        $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{JOBSEEKER_NAME}', $ApplicantName, $wpjobportal_msgBody);
        $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{JOB_TITLE}', $JobTitle, $wpjobportal_msgBody);
        $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{RESUME_APPLIED_STATUS}', $wpjobportal_applied_resume_status, $wpjobportal_msgBody);
        $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{RESUME_TITLE}', $wpjobportal_user->application_title, $wpjobportal_msgBody);
        $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{COMPANY_NAME}', $wpjobportal_jobuser->companyname, $wpjobportal_msgBody);
        $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{EMAIL}', $wpjobportal_user->email, $wpjobportal_msgBody);
        $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{CURRENT_YEAR}', gmdate('Y'), $wpjobportal_msgBody);
        $wpjobportal_subject = $wpjobportal_msgSubject;
        $body = $wpjobportal_msgBody;
        $recevierEmail = $wpjobportal_user->email;
        $attachments ='';
        if($wpjobportal_emailstatus->jobseeker == 1){
            wpjobportal::$_common->sendEmail($recevierEmail, $wpjobportal_subject, $body, $wpjobportal_senderEmail, $wpjobportal_senderName, $attachments);
        }
        return true;
    }


    function checkAlreadyAppliedJob($wpjobportal_jobid, $wpjobportal_uid) {
        if (!is_numeric($wpjobportal_jobid))
            return false;
        if (!is_numeric($wpjobportal_uid))
            return false;
        unset($wpjobportal_result);
        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` WHERE jobid = ". esc_sql($wpjobportal_jobid) . " AND uid = ". esc_sql($wpjobportal_uid);
        $wpjobportal_result = wpjobportal::$_db->get_var($query);
        if ($wpjobportal_result == 0) {
            return true;
        } else {
            return false;
        }
    }

    function checkjobappllystats($wpjobportal_jobid,$wpjobportal_uid){
        if (!is_numeric($wpjobportal_jobid))
            return false;
        if (!is_numeric($wpjobportal_uid))
            return false;
        unset($wpjobportal_result);
        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` WHERE jobid = ". esc_sql($wpjobportal_jobid) . " AND uid = " . esc_sql($wpjobportal_uid) ." and status = 3";
        $wpjobportal_result = wpjobportal::$_db->get_var($query);
        if ($wpjobportal_result == 0) {
            return true;
        } else {
            return false;
        }
    }

    function getEmailFieldsJobManager(){
        $wpjobportal_email = WPJOBPORTALrequest::getVar('em');
        $wpjobportal_resumeid = WPJOBPORTALrequest::getVar('resumeid');
        $wpjobportal_html = '<div class="'.esc_attr($this->class_prefix).'-sendemail-form">
                    <form class="">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="">'. esc_html(__('Job seeker', 'wp-job-portal')). ':</label>
                                <input type="text" id="jobseeker" class="form-control" value="' . $wpjobportal_email . '" disabled >
                            </div>
                            <div class="form-group">
                                <label for="exampleInputName2">'. esc_html(__('Subject', 'wp-job-portal')). ':</label>
                                <input type="text" id="subject" class="form-control" placeholder="' . esc_html(__('Subject', 'wp-job-portal')) . '">
                            </div>
                            <div class="form-group">
                                <label for="exampleInputName2">'. esc_html(__('Sender Email', 'wp-job-portal')). ':</label>
                                <input type="email" id="sender"  class="form-control " placeholder="'. esc_html(__('Sender Email', 'wp-job-portal')). '">
                            </div>
                        </div>
                        <div class="col-md-4 '.esc_attr($this->class_prefix).'-ar-se">
                            <div class="form-group">
                                <textarea id="email-body" placeholder="' . esc_html(__('Type here', 'wp-job-portal')) . '" class="form-control note-txt" rows="8"></textarea>
                            </div>
                        </div>
                        <div class="col-md-4 '.esc_attr($this->class_prefix).'-sendemail-btn-wrp">
                            <div class="form-group '.esc_attr($this->class_prefix).'-sendemail-btn-data">
                                <input type="button" class="form-control '.esc_attr($this->class_prefix).'-sendemail-btn" value="' . esc_html(__('Send', 'wp-job-portal')) . '" onclick="sendEmail('.$wpjobportal_resumeid.')">
                                <input type="button" class="form-control '.esc_attr($this->class_prefix).'-sendemail-btn" onclick="closeSection()" value="' . esc_html(__('Cancel', 'wp-job-portal')) . '">
                            </div>
                        </div>
                    </form>
                </div>';
        return $wpjobportal_html;
    }


    private function validateJobFilters($wpjobportal_jobid , $cvid ){

        if( (! is_numeric($wpjobportal_jobid)) || (! is_numeric($cvid)) )
            return false;

        $wpjobportal_isspam = 0;

        $query = "SELECT job.raf_gender AS gender, job.raf_location AS location, job.raf_education AS education, job.raf_category AS category, job.raf_experience AS experience
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                    WHERE job.id = ". esc_sql($wpjobportal_jobid);
        $wpjobportal_job_filters = wpjobportaldb::get_row($query);
        if($wpjobportal_job_filters){
            $query = "SELECT job.educationid,job.jobcategory,job.city
                    FROM `".wpjobportal::$_db->prefix."wj_portal_jobs` AS job
                    WHERE job.id = ". esc_sql($wpjobportal_jobid);
            $wpjobportal_job = wpjobportaldb::get_row($query);

            $query = "SELECT resume.gender,resume.job_category,address.address_city
                    FROM `".wpjobportal::$_db->prefix."wj_portal_resume` AS resume
                    LEFT JOIN `".wpjobportal::$_db->prefix."wj_portal_resumeaddresses` AS address ON resume.id = address.resumeid
                    WHERE resume.id = " . esc_sql($cvid);
            $wpjobportal_resume = wpjobportaldb::get_row($query);

            if($wpjobportal_job_filters->gender == 1){
                if($wpjobportal_job->gender != $wpjobportal_resume->gender)
                    $wpjobportal_isspam = 1;
            }
            if($wpjobportal_job_filters->category == 1){
                if($wpjobportal_job->jobcategory != $wpjobportal_resume->job_category)
                    $wpjobportal_isspam = 1;
            }
            if($wpjobportal_job_filters->education == 1){
                if($wpjobportal_job->educationid != $wpjobportal_resume->heighestfinisheducation)
                    $wpjobportal_isspam = 1;
            }
            if($wpjobportal_job_filters->location == 1){
                $wpjobportal_joblocation = wpjobportalphplib::wpJP_explode(',', $wpjobportal_job->city);
                if(! in_array($wpjobportal_resume->address_city, $wpjobportal_joblocation))
                    $wpjobportal_isspam = 1;
            }
        }

        return $wpjobportal_isspam;
    }


    function getOrdering() {
        $sort = WPJOBPORTALrequest::getVar('sortby', '');
        $this->getListOrdering($sort);
        $this->getListSorting($sort);
    }

    function getMyAppliedJobs($wpjobportal_uid) {
        if (!is_numeric($wpjobportal_uid)) return false;

        WPJOBPORTALincluder::getJSModel('job')->sorting();
        $query = "SELECT COUNT(jobapply.id)
                 FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` AS jobapply
                 JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job ON job.id = jobapply.jobid
                 JOIN `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume ON resume.id = jobapply.cvid
                 ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON company.id = job.companyid
                 LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS category ON category.id = job.jobcategory
                 LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON jobtype.id = job.jobtype
                 WHERE jobapply.uid = ". esc_sql($wpjobportal_uid);
        $wpjobportal_total = wpjobportaldb::get_var($query);
        wpjobportal::$_data['total'] = $wpjobportal_total;
        wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($wpjobportal_total,'appliedjobs');
        $query = "SELECT job.id AS jobid,job.city,job.title,job.noofjobs,job.currency,CONCAT(job.alias,'-',job.id) AS jobaliasid ,CONCAT(company.alias,'-',companyid) AS companyaliasid, job.serverid,job.status,job.endfeatureddate,job.isfeaturedjob,job.startpublishing,job.stoppublishing,job.description,
                 jobapply.action_status AS resumestatus,jobapply.apply_date,
                 company.id AS companyid, company.name AS companyname,company.logofilename,category.cat_title,
                 jobtype.title AS jobtypetitle, jobstatus.title AS jobstatustitle,resume.id AS resumeid,resume.salaryfixed as salary,resume.application_title,job.params,job.created,LOWER(jobtype.title) AS jobtype
                ,jobapply.id AS id,resume.first_name,resume.last_name,job.salarytype,job.salarymin,job.salarymax,jobapply.status AS applystatus,
                salaryrangetype.title AS srangetypetitle,jobtype.color AS jobtypecolor, jobapply.coverletterid,jobapply.apply_message
                 FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` AS jobapply
                 JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job ON job.id = jobapply.jobid
                 JOIN `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume ON resume.id = jobapply.cvid
                 ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON company.id = job.companyid
                 LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS category ON category.id = job.jobcategory
                 LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON jobtype.id = job.jobtype
                 LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON job.city = city.id
                 LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobstatus` AS jobstatus ON jobstatus.id = job.jobstatus
                 LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_salaryrangetypes` AS salaryrangetype ON salaryrangetype.id = job.salaryduration
                 WHERE jobapply.uid = ". esc_sql($wpjobportal_uid);
        $query.= " ORDER BY " . wpjobportal::$_data['sorting'];
        $query.=" LIMIT " . WPJOBPORTALpagination::$_offset . "," . WPJOBPORTALpagination::$_limit;
        $wpjobportal_results = wpjobportaldb::get_results($query);
        $wpjobportal_data = array();
        foreach ($wpjobportal_results AS $d) {
            $d->location = WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($d->city);
            if(in_array('coverletter', wpjobportal::$_active_addons)){
                $d->coverlettertitle = WPJOBPORTALincluder::getJSModel('coverletter')->getCoverLetterTitleFromID($d->coverletterid);
            }
            $wpjobportal_data[] = $d;
        }
        $wpjobportal_results = $wpjobportal_data;
        $wpjobportal_data = array();
        foreach ($wpjobportal_results AS $d) {
            $wpjobportal_data[] = $d;
        }
        wpjobportal::$_data[0] = $wpjobportal_data;
        wpjobportal::$wpjobportal_data['fields'] = wpjobportal::$_wpjpfieldordering->getFieldsOrderingforForm(2);
        wpjobportal::$_data['config'] = wpjobportal::$_config->getConfigByFor('job');
        return;
    }

    function getListOrdering($sort) {
        switch ($sort) {
          case 'newest':
                wpjobportal::$_ordering = "resumeid DESC";
                wpjobportal::$_sorton = "newest";
                wpjobportal::$_sortorder = "DESC";
                break;
            case 'salary':
                wpjobportal::$_ordering = "app.salaryfixed DESC";
                wpjobportal::$_sorton = "salary";
                wpjobportal::$_sortorder = "DESC";
                break;
            case 'newestdesc':
                wpjobportal::$_ordering = "resumeid DESC";
                wpjobportal::$_sorton = "newest";
                wpjobportal::$_sortorder = "DESC";
                break;
            case 'newestasc':
                wpjobportal::$_ordering = "resumeid ASC";
                wpjobportal::$_sorton = "newest";
                wpjobportal::$_sortorder = "ASC";
                break;
            default: wpjobportal::$_ordering = "job.title DESC";
            break;
        }
        return;
    }

    function getListSorting($sort) {
        wpjobportal::$_sortlinks['title'] = $this->getSortArg("title", $sort);
        wpjobportal::$_sortlinks['category'] = $this->getSortArg("category", $sort);
        wpjobportal::$_sortlinks['jobtype'] = $this->getSortArg("jobtype", $sort);
        wpjobportal::$_sortlinks['jobstatus'] = $this->getSortArg("jobstatus", $sort);
        wpjobportal::$_sortlinks['company'] = $this->getSortArg("company", $sort);
        wpjobportal::$_sortlinks['salary'] = $this->getSortArg("salary", $sort);
        wpjobportal::$_sortlinks['posted'] = $this->getSortArg("posted", $sort);
        wpjobportal::$_sortlinks['newest'] = $this->getSortArg("newest",$sort);
        return;
    }

    function getSortArg($type, $sort) {
        $wpjobportal_mat = array();
        if (wpjobportalphplib::wpJP_preg_match("/(\w+)(asc|desc)/i", $sort, $wpjobportal_mat)) {
            if ($type == $wpjobportal_mat[1]) {
                return ( $wpjobportal_mat[2] == "asc" ) ? "{$type}desc" : "{$type}asc";
            } else {
                return $type . $wpjobportal_mat[2];
            }
        }
        if(WPJOBPORTALrequest::getVar('wpjobportallt')=="jobappliedresume"){
            return "newestasc";
        }else{
        return "iddesc";
        }
    }

   function setJobApplyRating() {
        $wpjobportal_jobapplyid = WPJOBPORTALrequest::getVar('jobapplyid');
        if (!is_numeric($wpjobportal_jobapplyid))
            return false;
        $wpjobportal_newrating = WPJOBPORTALrequest::getVar('newrating');

        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('jobapply');
        if ($wpjobportal_row->update(array('id' => $wpjobportal_jobapplyid , 'rating' => $wpjobportal_newrating))){
            return true;
        } else {
            return false;
        }
    }

    function prepareResumeDataForEmployer($wpjobportal_resumeid) {
        $wpjobportal_send_only_filled_fields = wpjobportal::$_config->getConfigByFor('employer_resume_alert_fields');
        $wpjobportal_show_only_section_that_have_value = wpjobportal::$_config->getConfigByFor('show_only_section_that_have_value');

        WPJOBPORTALincluder::getJSModel('resume')->getResumebyId($wpjobportal_resumeid);
        if(empty(wpjobportal::$_data[0]['personal_section'])){
            return '';;
        }
        $wpjobportal_personalInfo = wpjobportal::$_data[0]['personal_section'];
        $wpjobportal_addresses = wpjobportal::$_data[0]['address_section'];
        $wpjobportal_institutes = wpjobportal::$_data[0]['institute_section'];
        $wpjobportal_employers = wpjobportal::$_data[0]['employer_section'];
        $wpjobportal_languages = wpjobportal::$_data[0]['language_section'];
        $wpjobportal_show_contact_detail =  wpjobportal::$wpjobportal_data['resumecontactdetail'];

        $wpjobportal_userfields = ''; // Ask form Shees
        $wpjobportal_fieldsordering = wpjobportal::$_wpjpfieldordering->getFieldsOrderingforForm(3); // resume fields
        wpjobportal::$_data[2] = array();
        foreach ($wpjobportal_fieldsordering AS $wpjobportal_field) {
            wpjobportal::$wpjobportal_data['fieldtitles'][$wpjobportal_field->field] = $wpjobportal_field->fieldtitle;
            wpjobportal::$_data[2][$wpjobportal_field->section][$wpjobportal_field->field] = $wpjobportal_field->published;
        }
        $wpjobportal_fieldsordering = wpjobportal::$_data[2];
        $wpjobportal_resume_sections = WPJOBPORTALincluder::getJSModel('fieldordering')->getPublishedResumeSections();
        // get resume sections and titles by ordering
        $wpjobportal_msgBody = "<table cellpadding='5' style='border-color: #666;' cellspacing='0' border='0' width='100%'>";

        $wpjobportal_temp_body = '';
        $flag = 0;
        if(isset($wpjobportal_fieldsordering[1]))
        foreach ($wpjobportal_fieldsordering[1] as $wpjobportal_field => $wpjobportal_required) {
            switch ($wpjobportal_field) {
                case "section_personal":
                    $wpjobportal_temp_body .= "<tr style='background: #eee;'>";
                    $wpjobportal_temp_body .= "<td colspan='2' align='center'><strong>" . $wpjobportal_resume_sections['section_personal'] . "</strong></td></tr>";
                    break;
                case "application_title":
                    $this->getRowForResume(wpjobportal::wpjobportal_getVariableValue(wpjobportal::$wpjobportal_data['fieldtitles'][$wpjobportal_field]), $wpjobportal_personalInfo->application_title, $wpjobportal_temp_body, $wpjobportal_required,$wpjobportal_send_only_filled_fields , $flag);
                    break;
                case "first_name":
                    $this->getRowForResume(wpjobportal::wpjobportal_getVariableValue(wpjobportal::$wpjobportal_data['fieldtitles'][$wpjobportal_field]), $wpjobportal_personalInfo->first_name, $wpjobportal_temp_body, $wpjobportal_required,$wpjobportal_send_only_filled_fields , $flag);
                    break;
                case "last_name":
                    $this->getRowForResume(wpjobportal::wpjobportal_getVariableValue(wpjobportal::$wpjobportal_data['fieldtitles'][$wpjobportal_field]), $wpjobportal_personalInfo->last_name, $wpjobportal_temp_body, $wpjobportal_required,$wpjobportal_send_only_filled_fields , $flag);
                    break;
                case "email_address":
                    if($wpjobportal_show_contact_detail){
                        $this->getRowForResume(wpjobportal::wpjobportal_getVariableValue(wpjobportal::$wpjobportal_data['fieldtitles'][$wpjobportal_field]), $wpjobportal_personalInfo->email_address, $wpjobportal_temp_body, $wpjobportal_required,$wpjobportal_send_only_filled_fields , $flag);
                    }
                    break;
                case "cell":
                    if($wpjobportal_show_contact_detail){
                        $this->getRowForResume(wpjobportal::wpjobportal_getVariableValue(wpjobportal::$wpjobportal_data['fieldtitles'][$wpjobportal_field]), $wpjobportal_personalInfo->cell, $wpjobportal_temp_body, $wpjobportal_required,$wpjobportal_send_only_filled_fields , $flag);
                    }
                    break;
                case "gender":
                    // made this code same as resume export. the old code was not correct.
                    $wpjobportal_genderText = __('Does not matter','wp-job-portal');
                    if($wpjobportal_personalInfo->gender == 1){
                        $wpjobportal_genderText = esc_html(__('Male','wp-job-portal'));
                    }elseif($wpjobportal_personalInfo->gender == 2){
                        $wpjobportal_genderText = esc_html(__('Female','wp-job-portal'));
                    }
                    $this->getRowForResume(wpjobportal::wpjobportal_getVariableValue(wpjobportal::$wpjobportal_data['fieldtitles'][$wpjobportal_field]), $wpjobportal_genderText, $wpjobportal_temp_body, $wpjobportal_required,$wpjobportal_send_only_filled_fields , $flag);
                    break;
                case "nationality":
                    $this->getRowForResume(wpjobportal::wpjobportal_getVariableValue(wpjobportal::$wpjobportal_data['fieldtitles'][$wpjobportal_field]), $wpjobportal_personalInfo->nationality, $wpjobportal_temp_body, $wpjobportal_required,$wpjobportal_send_only_filled_fields , $flag);
                    break;
                case "category":
                    $this->getRowForResume(wpjobportal::wpjobportal_getVariableValue(wpjobportal::$wpjobportal_data['fieldtitles'][$wpjobportal_field]), $wpjobportal_personalInfo->categorytitle, $wpjobportal_temp_body, $wpjobportal_required,$wpjobportal_send_only_filled_fields , $flag);
                    break;

                case "salaryfixed":
                    $wpjobportal_salary = $wpjobportal_personalInfo->salaryfixed;
                    $this->getRowForResume(wpjobportal::wpjobportal_getVariableValue(wpjobportal::$wpjobportal_data['fieldtitles'][$wpjobportal_field]), $wpjobportal_salary, $wpjobportal_temp_body, $wpjobportal_required,$wpjobportal_send_only_filled_fields , $flag);
                    break;

                case "jobtype":
                    $this->getRowForResume(wpjobportal::wpjobportal_getVariableValue(wpjobportal::$wpjobportal_data['fieldtitles'][$wpjobportal_field]), $wpjobportal_personalInfo->jobtypetitle, $wpjobportal_temp_body, $wpjobportal_required,$wpjobportal_send_only_filled_fields , $flag);
                    break;
                default:
                    $wpjobportal_data = apply_filters('wpjobportal_addons_show_customfields_params',false,$wpjobportal_field,11,$wpjobportal_personalInfo->params);
                    if(!empty($wpjobportal_data)){
                        if($wpjobportal_send_only_filled_fields == 1){
                            if(! empty($wpjobportal_data['value'])){
                                $wpjobportal_temp_body .= "<tr style='background: #eee;'>";
                                $wpjobportal_temp_body .= "<td><strong>" . $wpjobportal_data['title'] . "</strong></td>";
                                $wpjobportal_temp_body .= "<td>" . $wpjobportal_data['value'] . "</td></tr>";
                            }
                        }else{
                            if(is_array($wpjobportal_data)){
                                $wpjobportal_temp_body .= "<tr style='background: #eee;'>";
                                $wpjobportal_temp_body .= "<td><strong>" . $wpjobportal_data['title'] . "</strong></td>";
                                $wpjobportal_temp_body .= "<td>" . $wpjobportal_data['value'] . "</td></tr>";
                            }
                        }
                    }
                break;
            }
        }
        if($wpjobportal_show_only_section_that_have_value == 1){
            if($flag > 0){
                $wpjobportal_msgBody .= $wpjobportal_temp_body;
            }
        }else{
            $wpjobportal_msgBody .= $wpjobportal_temp_body;
        }

        // to print resume sections according thier ordering in field ordering
        foreach ($wpjobportal_resume_sections as $wpjobportal_section_field => $wpjobportal_section_fieldtitle) { // loop over all the active sections
            switch ($wpjobportal_section_field) {
                case 'section_address':
                    $flag = 0;
                    $wpjobportal_temp_body = '';
                    $wpjobportal_i = 0;
                    $wpjobportal_temp_body .= "<tr style='background: #eee;'>";
                    $wpjobportal_temp_body .= "<td colspan='2' align='center'><strong>" . esc_html($wpjobportal_section_fieldtitle) . "</strong></td></tr>";
                    if(isset($wpjobportal_addresses) && is_array($wpjobportal_addresses))
                    foreach ($wpjobportal_addresses as $wpjobportal_address) {
                        $wpjobportal_i++;
                        foreach ($wpjobportal_fieldsordering[2] as $wpjobportal_field => $wpjobportal_required) {
                            switch ($wpjobportal_field) {
                                case "section_address":
                                    if ($wpjobportal_required == 1) {
                                        $wpjobportal_temp_body .= "<tr style='background: #eee;'>";
                                        $wpjobportal_temp_body .= "<td colspan='2' align='center'><strong>" . esc_html(__('Address','wp-job-portal')) . "-" . $wpjobportal_i . "</strong></td></tr>";
                                    }
                                    break;
                                case "address_city":
                                    $this->getRowForResume(esc_html(wpjobportal::$wpjobportal_data['fieldtitles'][$wpjobportal_field]), $wpjobportal_address->cityname, $wpjobportal_temp_body, $wpjobportal_required,$wpjobportal_send_only_filled_fields , $flag);
                                    break;
                                case "address":
                                    $this->getRowForResume(esc_html(wpjobportal::$wpjobportal_data['fieldtitles'][$wpjobportal_field]), $wpjobportal_address->address, $wpjobportal_temp_body, $wpjobportal_required,$wpjobportal_send_only_filled_fields , $flag);
                                    break;
                                default:
                                    $wpjobportal_data = apply_filters('wpjobportal_addons_show_customfields_params',false,$wpjobportal_field,11,$wpjobportal_address->params);
                                    if(!empty($wpjobportal_data)){
                                        if($wpjobportal_send_only_filled_fields == 1){
                                            if(! empty($wpjobportal_data['value'])){
                                                $wpjobportal_temp_body .= "<tr style='background: #eee;'>";
                                                $wpjobportal_temp_body .= "<td><strong>" . $wpjobportal_data['title'] . "</strong></td>";
                                                $wpjobportal_temp_body .= "<td>" . $wpjobportal_data['value'] . "</td></tr>";
                                            }
                                        }else{
                                            $wpjobportal_temp_body .= "<tr style='background: #eee;'>";
                                            $wpjobportal_temp_body .= "<td><strong>" . $wpjobportal_data['title'] . "</strong></td>";
                                            $wpjobportal_temp_body .= "<td>" . $wpjobportal_data['value'] . "</td></tr>";
                                        }
                                    }
                                    break;
                            }
                        }
                    }

                    if($wpjobportal_show_contact_detail){
                        if($wpjobportal_show_only_section_that_have_value == 1){
                            if($flag > 0){
                                $wpjobportal_msgBody .= $wpjobportal_temp_body;
                            }
                        }else{
                            $wpjobportal_msgBody .= $wpjobportal_temp_body;
                        }
                    }
                break;
                case 'section_education':
                    $flag = 0;
                    $wpjobportal_temp_body = '';

                    $wpjobportal_i = 0;
                    $wpjobportal_temp_body .= "<tr style='background: #eee;'>";
                    $wpjobportal_temp_body .= "<td colspan='2' align='center'><strong>" . esc_html($wpjobportal_section_fieldtitle) . "</strong></td></tr>";
                    if(isset($wpjobportal_institutes) && is_array($wpjobportal_institutes))
                    foreach ($wpjobportal_institutes as $wpjobportal_institute) {
                        $wpjobportal_i++;
                        foreach ($wpjobportal_fieldsordering[3] as $wpjobportal_field => $wpjobportal_required) {
                            switch ($wpjobportal_field) {
                                case "section_education":
                                    if ($wpjobportal_required == 1) {
                                        $wpjobportal_temp_body .= "<tr style='background: #eee;'>";
                                        $wpjobportal_temp_body .= "<td colspan='2' align='center'><strong>" . esc_html(__('Institute','wp-job-portal')) . "-" . $wpjobportal_i . "</strong></td></tr>";
                                    }
                                    break;
                                case "institute":
                                    $this->getRowForResume(esc_html(wpjobportal::$wpjobportal_data['fieldtitles'][$wpjobportal_field]), $wpjobportal_institute->institute, $wpjobportal_temp_body, $wpjobportal_required,$wpjobportal_send_only_filled_fields , $flag);
                                    break;
                                    // added the missing fields
                                case "institute_certificate_name":
                                    $this->getRowForResume(esc_html(wpjobportal::$wpjobportal_data['fieldtitles'][$wpjobportal_field]), $wpjobportal_institute->institute_certificate_name, $wpjobportal_temp_body, $wpjobportal_required,$wpjobportal_send_only_filled_fields , $flag);
                                    break;

                                case "institute_study_area":
                                    $this->getRowForResume(esc_html(wpjobportal::$wpjobportal_data['fieldtitles'][$wpjobportal_field]), $wpjobportal_institute->institute_study_area, $wpjobportal_temp_body, $wpjobportal_required,$wpjobportal_send_only_filled_fields , $flag);
                                    break;

                                case "institute_date_from":
                                    // to handle empty date or 1970 date(which is used as default value to avoid mysql error on in valid value)
                                    $fromdate = '';
                                    if($wpjobportal_institute->fromdate != '' && !strstr($wpjobportal_institute->fromdate, '1970')){
                                        $fromdate = date_i18n(wpjobportal::$_configuration['date_format'],strtotime($wpjobportal_institute->fromdate));
                                    }
                                    $this->getRowForResume(esc_html(wpjobportal::$wpjobportal_data['fieldtitles'][$wpjobportal_field]), $fromdate, $wpjobportal_temp_body, $wpjobportal_required,$wpjobportal_send_only_filled_fields , $flag);
                                    break;

                                case "institute_date_to":
                                    // to handle empty date or 1970 date(which is used as default value to avoid mysql error on in valid value)
                                    $wpjobportal_todate = '';
                                    if($wpjobportal_institute->todate != '' && !strstr($wpjobportal_institute->todate, '1970')){
                                        $wpjobportal_todate = date_i18n(wpjobportal::$_configuration['date_format'],strtotime($wpjobportal_institute->todate));
                                    }
                                    $this->getRowForResume(esc_html(wpjobportal::$wpjobportal_data['fieldtitles'][$wpjobportal_field]), $wpjobportal_todate, $wpjobportal_temp_body, $wpjobportal_required,$wpjobportal_send_only_filled_fields , $flag);
                                    break;

                                default:
                                    $wpjobportal_data = apply_filters('wpjobportal_addons_show_customfields_params',false,$wpjobportal_field,11,$wpjobportal_institute->params);
                                    if(!empty($wpjobportal_data)){
                                        if($wpjobportal_send_only_filled_fields == 1){
                                            if(! empty($wpjobportal_data['value'])){
                                                $wpjobportal_temp_body .= "<tr style='background: #eee;'>";
                                                $wpjobportal_temp_body .= "<td><strong>" . $wpjobportal_data['title'] . "</strong></td>";
                                                $wpjobportal_temp_body .= "<td>" . $wpjobportal_data['value'] . "</td></tr>";
                                            }
                                        }else{
                                            $wpjobportal_temp_body .= "<tr style='background: #eee;'>";
                                            $wpjobportal_temp_body .= "<td><strong>" . $wpjobportal_data['title'] . "</strong></td>";
                                            $wpjobportal_temp_body .= "<td>" . $wpjobportal_data['value'] . "</td></tr>";
                                        }
                                    }
                                    break;
                            }
                        }
                    }

                    if($wpjobportal_show_only_section_that_have_value == 1){
                        if($flag > 0){
                            $wpjobportal_msgBody .= $wpjobportal_temp_body;
                        }
                    }else{
                        $wpjobportal_msgBody .= $wpjobportal_temp_body;
                    }
                    break;

                case 'section_employer':
                    $flag = 0;
                    $wpjobportal_temp_body = '';

                    $wpjobportal_i = 0;
                    $wpjobportal_temp_body .= "<tr style='background: #eee;'>";
                    $wpjobportal_temp_body .= "<td colspan='2' align='center'><strong>" . esc_html($wpjobportal_section_fieldtitle) . "</strong></td></tr>";
                    if(isset($wpjobportal_employers) && is_array($wpjobportal_employers))
                    foreach ($wpjobportal_employers as $wpjobportal_employer) {
                        $wpjobportal_i++;
                        foreach ($wpjobportal_fieldsordering[4] as $wpjobportal_field => $wpjobportal_required) {
                            switch ($wpjobportal_field) {
                                case "section_employer":
                                    if ($wpjobportal_required == 1) {
                                        $wpjobportal_temp_body .= "<tr style='background: #eee;'>";
                                        $wpjobportal_temp_body .= "<td colspan='2' align='center'><strong>" . esc_html(__('Employer','wp-job-portal')) . "-" . $wpjobportal_i . "</strong></td></tr>";
                                    }
                                    break;
                                case "employer":
                                    $this->getRowForResume(esc_html(wpjobportal::$wpjobportal_data['fieldtitles'][$wpjobportal_field]), $wpjobportal_employer->employer, $wpjobportal_temp_body, $wpjobportal_required,$wpjobportal_send_only_filled_fields , $flag);
                                    break;
                                case "employer_position":
                                    $this->getRowForResume(esc_html(wpjobportal::$wpjobportal_data['fieldtitles'][$wpjobportal_field]), $wpjobportal_employer->employer_position, $wpjobportal_temp_body, $wpjobportal_required,$wpjobportal_send_only_filled_fields , $flag);
                                    break;
                                case "employer_from_date":
                                    // to handle empty date or 1970 date(which is used as default value to avoid mysql error on in valid value)
                                    $fromdate = '';
                                    if($wpjobportal_employer->employer_from_date != '' && !strstr($wpjobportal_employer->employer_from_date, '1970')){
                                        $fromdate = date_i18n(wpjobportal::$_configuration['date_format'],strtotime($wpjobportal_employer->employer_from_date));
                                    }
                                    $this->getRowForResume(esc_html(wpjobportal::$wpjobportal_data['fieldtitles'][$wpjobportal_field]), $fromdate, $wpjobportal_temp_body, $wpjobportal_required,$wpjobportal_send_only_filled_fields , $flag);
                                    break;
                                case "employer_to_date":
                                    // to handle empty date or 1970 date(which is used as default value to avoid mysql error on in valid value)
                                    $wpjobportal_todate = '';
                                    if($wpjobportal_employer->employer_to_date != '' && !strstr($wpjobportal_employer->employer_to_date, '1970')){
                                        $wpjobportal_todate = date_i18n(wpjobportal::$_configuration['date_format'],strtotime($wpjobportal_employer->employer_to_date));
                                    }
                                    $this->getRowForResume(esc_html(wpjobportal::$wpjobportal_data['fieldtitles'][$wpjobportal_field]), $wpjobportal_todate, $wpjobportal_temp_body, $wpjobportal_required,$wpjobportal_send_only_filled_fields , $flag);
                                    break;
                               case "employer_city":
                                    $this->getRowForResume(esc_html(wpjobportal::$wpjobportal_data['fieldtitles'][$wpjobportal_field]), $wpjobportal_employer->cityname, $wpjobportal_temp_body, $wpjobportal_required,$wpjobportal_send_only_filled_fields , $flag);
                                    break;
                                case "employer_address":
                                    $this->getRowForResume(esc_html(wpjobportal::$wpjobportal_data['fieldtitles'][$wpjobportal_field]), $wpjobportal_employer->employer_address, $wpjobportal_temp_body, $wpjobportal_required,$wpjobportal_send_only_filled_fields , $flag);
                                    break;
                                case "employer_phone":
                                    $this->getRowForResume(esc_html(wpjobportal::$wpjobportal_data['fieldtitles'][$wpjobportal_field]), $wpjobportal_employer->employer_phone, $wpjobportal_temp_body, $wpjobportal_required,$wpjobportal_send_only_filled_fields , $flag);
                                    break;
                                default:
                                    $wpjobportal_data = apply_filters('wpjobportal_addons_show_customfields_params',false,$wpjobportal_field,11,$wpjobportal_employer->params);
                                    if(!empty($wpjobportal_data)){
                                        if($wpjobportal_send_only_filled_fields == 1){
                                            if(! empty($wpjobportal_data['value'])){
                                                $wpjobportal_temp_body .= "<tr style='background: #eee;'>";
                                                $wpjobportal_temp_body .= "<td><strong>" . $wpjobportal_data['title'] . "</strong></td>";
                                                $wpjobportal_temp_body .= "<td>" . $wpjobportal_data['value'] . "</td></tr>";
                                            }
                                        }else{
                                            $wpjobportal_temp_body .= "<tr style='background: #eee;'>";
                                            $wpjobportal_temp_body .= "<td><strong>" . $wpjobportal_data['title'] . "</strong></td>";
                                            $wpjobportal_temp_body .= "<td>" . $wpjobportal_data['value'] . "</td></tr>";
                                        }
                                    }
                                    break;
                            }
                        }
                    }

                    if($wpjobportal_show_only_section_that_have_value == 1){
                        if($flag > 0){
                            $wpjobportal_msgBody .= $wpjobportal_temp_body;
                        }
                    }else{
                        $wpjobportal_msgBody .= $wpjobportal_temp_body;
                    }
                break;

                case 'section_skills':
                    $flag = 0;
                    $wpjobportal_temp_body = '';

                    foreach ($wpjobportal_fieldsordering[5] as $wpjobportal_field => $wpjobportal_required) {
                        switch ($wpjobportal_field) {
                            case "section_skills":
                                if ($wpjobportal_required == 1) {
                                    $wpjobportal_temp_body .= "<tr style='background: #eee;'>";
                                    $wpjobportal_temp_body .= "<td colspan='2' align='center'><strong>" . esc_html($wpjobportal_section_fieldtitle) . "</strong></td></tr>";
                                }
                                break;
                            case "skills":
                                $this->getRowForResume(esc_html(wpjobportal::$wpjobportal_data['fieldtitles'][$wpjobportal_field]), $wpjobportal_personalInfo->skills, $wpjobportal_temp_body, $wpjobportal_required,$wpjobportal_send_only_filled_fields , $flag);
                                break;
                            default:
                                $wpjobportal_data = apply_filters('wpjobportal_addons_show_customfields_params',false,$wpjobportal_field,11,$wpjobportal_personalInfo->params);
                                if(!empty($wpjobportal_data)){
                                    if($wpjobportal_send_only_filled_fields == 1){
                                        if(! empty($wpjobportal_data['value'])){
                                            $wpjobportal_temp_body .= "<tr style='background: #eee;'>";
                                            $wpjobportal_temp_body .= "<td><strong>" . $wpjobportal_data['title'] . "</strong></td>";
                                            $wpjobportal_temp_body .= "<td>" . $wpjobportal_data['value'] . "</td></tr>";
                                        }
                                    }else{
                                        $wpjobportal_temp_body .= "<tr style='background: #eee;'>";
                                        $wpjobportal_temp_body .= "<td><strong>" . $wpjobportal_data['title'] . "</strong></td>";
                                        $wpjobportal_temp_body .= "<td>" . $wpjobportal_data['value'] . "</td></tr>";
                                    }
                                }
                                break;
                        }
                    }

                    if($wpjobportal_show_only_section_that_have_value == 1){
                        if($flag > 0){
                            $wpjobportal_msgBody .= $wpjobportal_temp_body;
                        }
                    }else{
                        $wpjobportal_msgBody .= $wpjobportal_temp_body;
                    }

                break;

                case 'section_language':
                    $flag = 0;
                    $wpjobportal_temp_body = '';


                    $wpjobportal_i = 0;
                    $wpjobportal_temp_body .= "<tr style='background: #eee;'>";
                    $wpjobportal_temp_body .= "<td colspan='2' align='center'><strong>" . esc_html($wpjobportal_section_fieldtitle) . "</strong></td></tr>";
                    if(isset($wpjobportal_languages) && is_array($wpjobportal_languages))
                    foreach ($wpjobportal_languages as $wpjobportal_language) {
                        $wpjobportal_i++;
                        foreach ($wpjobportal_fieldsordering[8] as $wpjobportal_field => $wpjobportal_required) {
                            switch ($wpjobportal_field) {
                                case "section_language":
                                    if ($wpjobportal_required == 1) {
                                        $wpjobportal_temp_body .= "<tr style='background: #eee;'>";
                                        $wpjobportal_temp_body .= "<td colspan='2' align='center'><strong>" . esc_html(__('Language','wp-job-portal')) . "-" . $wpjobportal_i . "</strong></td></tr>";
                                    }
                                    break;
                                case "language_name":
                                    $this->getRowForResume(esc_html(wpjobportal::$wpjobportal_data['fieldtitles'][$wpjobportal_field]), $wpjobportal_language->language, $wpjobportal_temp_body, $wpjobportal_required,$wpjobportal_send_only_filled_fields , $flag);
                                    break;
                                default:
                                    $wpjobportal_data = apply_filters('wpjobportal_addons_show_customfields_params',false,$wpjobportal_field,11,$wpjobportal_language->params);
                                    if(!empty($wpjobportal_data)){
                                        if($wpjobportal_send_only_filled_fields == 1){
                                            if(! empty($wpjobportal_data['value'])){
                                                $wpjobportal_temp_body .= "<tr style='background: #eee;'>";
                                                $wpjobportal_temp_body .= "<td><strong>" . $wpjobportal_data['title'] . "</strong></td>";
                                                $wpjobportal_temp_body .= "<td>" . $wpjobportal_data['value'] . "</td></tr>";
                                            }
                                        }else{
                                            $wpjobportal_temp_body .= "<tr style='background: #eee;'>";
                                            $wpjobportal_temp_body .= "<td><strong>" . $wpjobportal_data['title'] . "</strong></td>";
                                            $wpjobportal_temp_body .= "<td>" . $wpjobportal_data['value'] . "</td></tr>";
                                        }
                                    }
                                    break;
                            }
                        }
                    }

                    if($wpjobportal_show_only_section_that_have_value == 1){
                        if($flag > 0){
                            $wpjobportal_msgBody .= $wpjobportal_temp_body;
                        }
                    }else{
                        $wpjobportal_msgBody .= $wpjobportal_temp_body;
                    }

                break;

                default:

                    if($wpjobportal_section_field == 'section_resume'){
                        break;
                    }

                    $flag = 0;
                    $wpjobportal_temp_body = '';
                    $wpjobportal_resume_section_value = WPJOBPORTALincluder::getJSModel('fieldordering')->getResumeCustomSectionFromSectionField($wpjobportal_section_field);
                    if($wpjobportal_resume_section_value == ''){
                        break;
                    }
                    $wpjobportal_resume_section_fields = WPJOBPORTALincluder::getJSModel('fieldordering')->getResumeCustomSectionFields($wpjobportal_resume_section_value);
                    foreach ($wpjobportal_resume_section_fields as $wpjobportal_field) {
                        switch ($wpjobportal_field) {
                            // case "section_skills":
                            //     if ($wpjobportal_required == 1) {
                            //         $wpjobportal_temp_body .= "<tr style='background: #eee;'>";
                            //         $wpjobportal_temp_body .= "<td colspan='2' align='center'><strong>" . esc_html($wpjobportal_section_fieldtitle) . "</strong></td></tr>";
                            //     }
                            //     break;
                            // case "skills":
                            //     $this->getRowForResume(esc_html(wpjobportal::$wpjobportal_data['fieldtitles'][$wpjobportal_field]), $wpjobportal_personalInfo->skills, $wpjobportal_temp_body, $wpjobportal_required,$wpjobportal_send_only_filled_fields , $flag);
                            //     break;
                            default:
                                if($wpjobportal_field->is_section_headline == 1){
                                    $wpjobportal_temp_body .= "<tr style='background: #eee;'>";
                                    $wpjobportal_temp_body .= "<td colspan='2' align='center'><strong>" . esc_html($wpjobportal_section_fieldtitle) . "</strong></td></tr>";
                                    break;
                                }
                                $wpjobportal_data = apply_filters('wpjobportal_addons_show_customfields_params',false,$wpjobportal_field->field,11,$wpjobportal_personalInfo->params);
                                if(!empty($wpjobportal_data)){
                                    if($wpjobportal_send_only_filled_fields == 1){
                                        if(! empty($wpjobportal_data['value'])){
                                            $wpjobportal_temp_body .= "<tr style='background: #eee;'>";
                                            $wpjobportal_temp_body .= "<td><strong>" . $wpjobportal_data['title'] . "</strong></td>";
                                            $wpjobportal_temp_body .= "<td>" . $wpjobportal_data['value'] . "</td></tr>";
                                        }
                                    }else{
                                        $wpjobportal_temp_body .= "<tr style='background: #eee;'>";
                                        $wpjobportal_temp_body .= "<td><strong>" . $wpjobportal_data['title'] . "</strong></td>";
                                        $wpjobportal_temp_body .= "<td>" . $wpjobportal_data['value'] . "</td></tr>";
                                    }
                                    $flag = 1;
                                }
                                break;
                        }
                    }

                    if($wpjobportal_show_only_section_that_have_value == 1){
                        if($flag > 0){
                            $wpjobportal_msgBody .= $wpjobportal_temp_body;
                        }
                    }else{
                        $wpjobportal_msgBody .= $wpjobportal_temp_body;
                    }

                break;
            }
        }


// sections no longer in the system
/*
        $flag = 0;
        $wpjobportal_temp_body = '';


        if(isset($wpjobportal_fieldsordering['resume']))
        foreach ($wpjobportal_fieldsordering['resume'] as $wpjobportal_field) {
            switch ($wpjobportal_field) {
                case "section_resume":
                    if ($wpjobportal_required == 1) {
                        $wpjobportal_temp_body .= "<tr style='background: #eee;'>";
                        $wpjobportal_temp_body .= "<td colspan='2' align='center'><strong>" . esc_html(__('Resume','wp-job-portal')) . "</strong></td></tr>";
                    }
                    break;
                case "resume":
                    $this->getRowForResume(esc_html(__('Resume','wp-job-portal')), $wpjobportal_personalInfo->resume, $wpjobportal_temp_body, $wpjobportal_required,$wpjobportal_send_only_filled_fields , $flag);
                    break;
                default:
                    $wpjobportal_data = apply_filters('wpjobportal_addons_show_customfields_params',false,$wpjobportal_field,11,$wpjobportal_personalInfo->params);
                    if(!empty($wpjobportal_data)){
                        if($wpjobportal_send_only_filled_fields == 1){
                            if(! empty($wpjobportal_data['value'])){
                                $wpjobportal_temp_body .= "<tr style='background: #eee;'>";
                                $wpjobportal_temp_body .= "<td><strong>" . $wpjobportal_data['title'] . "</strong></td>";
                                $wpjobportal_temp_body .= "<td>" . $wpjobportal_data['value'] . "</td></tr>";
                            }
                        }else{
                            $wpjobportal_temp_body .= "<tr style='background: #eee;'>";
                            $wpjobportal_temp_body .= "<td><strong>" . $wpjobportal_data['title'] . "</strong></td>";
                            $wpjobportal_temp_body .= "<td>" . $wpjobportal_data['value'] . "</td></tr>";
                        }
                    }
                    break;
            }
        }

        if($wpjobportal_show_only_section_that_have_value == 1){
            if($flag > 0){
                $wpjobportal_msgBody .= $wpjobportal_temp_body;
            }
        }else{
            $wpjobportal_msgBody .= $wpjobportal_temp_body;
        }

        $flag = 0;
        $wpjobportal_temp_body = '';


        $wpjobportal_i = 0;
        $wpjobportal_temp_body .= "<tr style='background: #eee;'>";
        $wpjobportal_temp_body .= "<td colspan='2' align='center'><strong>" . esc_html(__('References','wp-job-portal')) . "</strong></td></tr>";
        if(isset($references) && is_array($references))
            if($wpjobportal_show_only_section_that_have_value == 1){
                if($flag > 0){
                    $wpjobportal_msgBody .= $wpjobportal_temp_body;
                }
            }else{
                $wpjobportal_msgBody .= $wpjobportal_temp_body;
            }

            */

        $wpjobportal_msgBody .= "</table>";

        return $wpjobportal_msgBody;
    }

    protected function getRowForResume($title, $wpjobportal_value, &$wpjobportal_msgBody, $wpjobportal_published , $wpjobportal_send_ifnotempty , &$flag) {

        if ($wpjobportal_published == 1) {
            if($wpjobportal_send_ifnotempty == 1){
                if(! empty($wpjobportal_value)){
                    $wpjobportal_msgBody .= "<tr style='background: #eee;'>";
                    $wpjobportal_msgBody .= "<td><strong>" . $title . "</strong></td>";
                    $wpjobportal_msgBody .= "<td>" . $wpjobportal_value . "</td></tr>";
                    $flag++;
                }
            }else{
                    $wpjobportal_msgBody .= "<tr style='background: #eee;'>";
                    $wpjobportal_msgBody .= "<td><strong>" . $title . "</strong></td>";
                    $wpjobportal_msgBody .= "<td>" . $wpjobportal_value . "</td></tr>";
                    $flag++;
            }

        }
    }

    protected function getUserFieldRowForResume( &$wpjobportal_msgBody , $wpjobportal_section) {
        $wpjobportal_customfields = apply_filters('wpjobportal_addons_get_custom_field',false,3);
        foreach ($wpjobportal_customfields as $wpjobportal_field) {
            $wpjobportal_data = apply_filters('wpjobportal_addons_show_customfields_params',false,$wpjobportal_field,6,$wpjobportal_section->params);
            $wpjobportal_msgBody .= "<tr style='background: #eee;'>";
            $wpjobportal_msgBody .= "<td><strong>" . $wpjobportal_data['title'] . "</strong></td>";
            $wpjobportal_msgBody .= "<td>" . $wpjobportal_data['value'] . "</td></tr>";

        }
    }
    function canceljobapplyasvisitor(){
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'cancel-jobapply-as-visitor') ) {
            die( 'Security check Failed' );
        }
        wpjobportalphplib::wpJP_setcookie('wpjobportal_apply_visitor' , '' , time() - 3600 , COOKIEPATH);
        if ( SITECOOKIEPATH != COOKIEPATH ){
            wpjobportalphplib::wpJP_setcookie('wpjobportal_apply_visitor' , '' , time() - 3600 , SITECOOKIEPATH);
        }

        unset($_SESSION['wp-wpjobportal']);
        $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'newestjobs'));
        echo esc_url($wpjobportal_link);
        die();
    }

    function getJobByid($wpjobportal_jobid){
        if(!is_numeric($wpjobportal_jobid))
            return false;
        $query = "SELECT job.endfeatureddate,job.id,job.uid,job.title,job.isfeaturedjob,job.serverid,job.noofjobs,job.city,job.status,job.currency,
                CONCAT(job.alias,'-',job.id) AS jobaliasid,job.created,job.serverid,company.name AS companyname,company.id AS companyid,company.logofilename,CONCAT(company.alias,'-',company.id) AS compnayaliasid,job.salarytype,job.salarymin,job.salarymax,salaryrangetype.title AS salarydurationtitle,
                cat.cat_title, jobtype.title AS jobtypetitle,salaryrangetype.title AS srangetypetitle,jobtype.color AS jobtypecolor,
                (SELECT count(jobapply.id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` AS jobapply
                 WHERE jobapply.jobid = job.id) AS resumeapplied ,job.params,job.startpublishing,job.stoppublishing
                 ,LOWER(jobtype.title) AS jobtypetit
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON company.id = job.companyid
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON cat.id = job.jobcategory
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON jobtype.id = job.jobtype
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_salaryrangetypes` AS salaryrangetype ON salaryrangetype.id = job.salaryduration
                WHERE  job.id = ". esc_sql($wpjobportal_jobid);

        $wpjobportal_results = wpjobportaldb::get_results($query);
        $wpjobportal_data = array();
        foreach ($wpjobportal_results AS $d) {
            $d->location = WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($d->city);
            $wpjobportal_data[] = $d;
        }
        $wpjobportal_results = $wpjobportal_data;
       return $wpjobportal_results;
    }

     function getMyJobs($wpjobportal_uid,$wpjobportal_jobid='') {
       if (!is_numeric($wpjobportal_uid)) return false;
        # Data Query Listing
        $query = "SELECT job.endfeatureddate,job.id,job.uid,job.title,job.isfeaturedjob,job.serverid,job.noofjobs,job.city,job.status,job.currency,job.description,
                CONCAT(job.alias,'-',job.id) AS jobaliasid,job.created,job.serverid,company.name AS companyname,company.id AS companyid,company.logofilename,CONCAT(company.alias,'-',company.id) AS compnayaliasid,job.salarytype,job.salarymin,job.salarymax,salaryrangetype.title AS salarydurationtitle,
                cat.cat_title, jobtype.title AS jobtypetitle,salaryrangetype.title AS srangetypetitle,
                (SELECT count(jobapply.id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` AS jobapply
                 WHERE jobapply.jobid = job.id) AS resumeapplied ,job.params,job.startpublishing,job.stoppublishing
                 ,LOWER(jobtype.title) AS jobtypetit,jobtype.color as jobtypecolor
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON company.id = job.companyid
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON cat.id = job.jobcategory
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON jobtype.id = job.jobtype
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_salaryrangetypes` AS salaryrangetype ON salaryrangetype.id = job.salaryduration
                WHERE job.uid =". esc_sql($wpjobportal_uid)  ;
                $query .= " AND job.id =".esc_sql($wpjobportal_jobid);

        # Sorting Merge In Query
        $wpjobportal_results = wpjobportaldb::get_results($query);
        $wpjobportal_data = array();
        foreach ($wpjobportal_results AS $d) {
            $d->location = WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($d->city);
            $wpjobportal_data[] = $d;
        }
        return  $wpjobportal_data;
    }


     function sorting() {
        $wpjobportal_pagenum = WPJOBPORTALrequest::getVar('pagenum');
        wpjobportal::$_data['sorton'] = WPJOBPORTALrequest::getVar('sorton', 'post', 6);
        wpjobportal::$_data['sortby'] = WPJOBPORTALrequest::getVar('sortby', 'post', 2);
        if($wpjobportal_pagenum > 1 && isset($_SESSION['resume'])){
            wpjobportal::$_data['sorton'] = sanitize_key($_SESSION['resume']['sorton']);
            wpjobportal::$_data['sortby'] = sanitize_key($_SESSION['resume']['sortby']);
        }else{
            $_SESSION['resume']['sorton'] = wpjobportal::$_data['sorton'];
            $_SESSION['resume']['sortby'] = wpjobportal::$_data['sortby'];
        }
        switch (wpjobportal::$_data['sorton']) {
            case 1: // appilcation title
                wpjobportal::$_data['sorting'] = ' app.application_title ';
                break;
            case 2: // first name
                wpjobportal::$_data['sorting'] = ' app.first_name ';
                break;
            case 3: // category
                wpjobportal::$_data['sorting'] = ' cat.cat_title ';
                break;
            case 4: // job type
                wpjobportal::$_data['sorting'] = ' app.jobtype ';
                break;
            case 5: // location
                wpjobportal::$_data['sorting'] = ' city.name ';
                break;
            case 6: // created
                wpjobportal::$_data['sorting'] = ' app.created ';
                break;
            case 7: // status
                wpjobportal::$_data['sorting'] = ' app.status ';
                break;
        }
        if (wpjobportal::$_data['sortby'] == 1) {
            wpjobportal::$_data['sorting'] .= ' ASC ';
        } else {
            wpjobportal::$_data['sorting'] .= ' DESC ';
        }
        wpjobportal::$_data['combosort'] = wpjobportal::$_data['sorton'];
    }

    function sendEmailToJobSeeker() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'send-email-to-jobseeker') ) {
            die( 'Security check Failed' );
        }
        $wpjobportal_jobseekeremail = WPJOBPORTALrequest::getVar('jobseekerid');
        $wpjobportal_subject = WPJOBPORTALrequest::getVar('emailsubject');
        $wpjobportal_senderemail = WPJOBPORTALrequest::getVar('senderid');
        $wpjobportal_mail = WPJOBPORTALrequest::getVar('mailbody');

        // code to verify the sender & reciver of email are valid
        $wpjobportal_resumeid = WPJOBPORTALrequest::getVar('resumeid');
        $wpjobportal_jobid = WPJOBPORTALrequest::getVar('jobid');

        // check if job owner is sending the email
        $wpjobportal_job_owner = WPJOBPORTALincluder::getJSModel('job')->getIfJobOwner($wpjobportal_jobid);
        $wpjobportal_employer_email = '';
        $wpjobportal_jobseeker_email = '';
        if (current_user_can('manage_options') || $wpjobportal_job_owner == true) { //admin or job owner can send email

            // getting employer email address
            $wpjobportal_employer_record =  $this->getEmployerEmailByJobId($wpjobportal_jobid);
            if(!empty($wpjobportal_employer_record)){
                $wpjobportal_employer_email = $wpjobportal_employer_record->companyuseremail;
                if($wpjobportal_employer_email == ''){ // if comapny contact email is not set
                    $wpjobportal_employer_email = $wpjobportal_employer_record->useremail;
                }
            }

            // getting jobseeker email address
            $wpjobportal_jobseeker_record =  $this->getJobSeekerEmailByResumeId($wpjobportal_resumeid);
            if(!empty($wpjobportal_jobseeker_record)){
                $wpjobportal_jobseeker_email = $wpjobportal_jobseeker_record->useremailfromresume;
                if($wpjobportal_jobseeker_email == ''){ // if comapny contact email is not set
                    $wpjobportal_jobseeker_email = $wpjobportal_jobseeker_record->useremail;
                }
            }

        }
        $return = 0;
        if($wpjobportal_employer_email != '' && $wpjobportal_jobseeker_email != ''){
            $wpjobportal_senderemail = $wpjobportal_employer_email;
            $wpjobportal_jobseekeremail = $wpjobportal_jobseeker_email;
            $wpjobportal_subject = sanitize_text_field( $wpjobportal_subject );
            $wpjobportal_mail = sanitize_textarea_field( $wpjobportal_mail );
            $return = wpjobportal::$_common->sendEmail($wpjobportal_jobseekeremail, $wpjobportal_subject, $wpjobportal_mail, $wpjobportal_senderemail, '');
        }
        if($return == 1){
            return esc_html(__('Email has been send','wp-job-portal'));
        }else{
            return esc_html(__('Email has not been send','wp-job-portal'));
        }
    }

    function getEmailFields() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'get-email-fields') ) {
            die( 'Security check Failed' );
        }
        $wpjobportal_email = WPJOBPORTALrequest::getVar('em');
        $wpjobportal_resumeid = WPJOBPORTALrequest::getVar('resumeid');
        $wpjobportal_jobid = WPJOBPORTALrequest::getVar('jobid');

        // filliung employer email in text field and making it disabled
        $wpjobportal_employer_record =  $this->getEmployerEmailByJobId($wpjobportal_jobid);
        if(!empty($wpjobportal_employer_record)){
            $wpjobportal_employer_email = $wpjobportal_employer_record->companyuseremail;
            if($wpjobportal_employer_email == ''){ // if comapny contact email is not set
                $wpjobportal_employer_email = $wpjobportal_employer_record->useremail;
            }
        }

        $wpjobportal_html = '';
        if (wpjobportal::$wpjobportal_theme_chk == 1) {
            $wpjobportal_html.='<img id="close-section" onclick="closeSection()" src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/no.png"/>';
            $wpjobportal_html.='<div class="email-feilds wpj-jp-applied-resume-cnt wpj-jp-email-actions-wrp"><div class="wpj-jp-applied-resume-cnt-row">';
            $wpjobportal_html.='<label for="jobseeker">'
                    . esc_html(__("Job Seeker", 'wp-job-portal'))
                    . ' : </label>';
            $wpjobportal_html.='<input type="text" id="jobseeker" value="' . $wpjobportal_email . '" disabled="disabled" /></div><div class="wpj-jp-applied-resume-cnt-row"><label for="subject">'
                    . esc_html(__('Subject', 'wp-job-portal')) .
                    ' : </label>';
            $wpjobportal_html.='<input type="text" id="e-subject" />';
            $wpjobportal_html.='</div><div class="wpj-jp-applied-resume-cnt-row">';
            $wpjobportal_html.='<label for="sender">' . esc_html(__("Sender Email", 'wp-job-portal')) . '  : </label>';
            $wpjobportal_html.='<input type="text" id="sender" value="'.$wpjobportal_employer_email.'" disabled="disabled" /></div>';
            $wpjobportal_html.='<div class="wpj-jp-applied-resume-cnt-row"><textarea id="email-body" placeholder=' . esc_html(__('Type here', 'wp-job-portal')) . '>';
            $wpjobportal_html.='</textarea></div> <div class="wpj-jp-applied-resume-cnt-row"><input class="wpj-jp-outline-btn" type="button" id="send" value=' . esc_html(__("Send", 'wp-job-portal')) . ' onclick="sendEmail('.$wpjobportal_resumeid.')" /></div></div>';
        } else {
            $wpjobportal_html.='<img id="close-section" onclick="closeSection()" src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/no.png"/>';
            $wpjobportal_html.='<div class="email-feilds wjportal-applied-job-actions-wrp wjportal-email-actions-wrp"><div class="wjportal-applied-job-actions-row">';
            $wpjobportal_html.='<label for="jobseeker">'
                    . esc_html(__('Job Seeker', 'wp-job-portal'))
                    . ' : </label>';
            $wpjobportal_html.='<input type="text" id="jobseeker" value="' . $wpjobportal_email . '" disabled /></div><div class="wjportal-applied-job-actions-row"><label for="subject">'
                    . esc_html(__('Subject', 'wp-job-portal')) .
                    ' : </label>';
            $wpjobportal_html.='<input type="text" id="e-subject" />';
            $wpjobportal_html.='</div><div class="wjportal-applied-job-actions-row">';
            $wpjobportal_html.='<label for="sender">' . esc_html(__('Sender Email', 'wp-job-portal')) . '  : </label>';
            $wpjobportal_html.='<input type="text" id="sender" value="'.$wpjobportal_employer_email.'" disabled="disabled"  /></div>';
            $wpjobportal_html.='<div class="wjportal-applied-job-actions-row"><textarea id="email-body" placeholder=' . esc_html(__('Type here', 'wp-job-portal')) . '>';
            $wpjobportal_html.='</textarea></div> <div class="wjportal-job-applied-actions-btn-wrp"><input class="wjportal-job-applied-actions-btn" type="button" id="send" value=' . esc_html(__('Send', 'wp-job-portal')) . ' onclick="sendEmail('.$wpjobportal_resumeid.')" /></div></div>';
        }
        // added these values to handle some verifications before sending email
        $wpjobportal_html .= '<input type="hidden" id="jobid" id="jobid" value="'.$wpjobportal_jobid.'" />';
        $wpjobportal_html .= '<input type="hidden" id="resumeid" id="resumeid" value="'.$wpjobportal_resumeid.'" />';
        return $wpjobportal_html;
    }

    // function to fetch employer emial by job id
    function getEmployerEmailByJobId($wpjobportal_jobid){
        if(!is_numeric($wpjobportal_jobid))
            return false;
        $query = 'SELECT company.contactemail AS companyuseremail, user.emailaddress AS useremail
                    FROM `' . wpjobportal::$_db->prefix . 'wj_portal_jobs` AS job
                    '.wpjobportal::$_company_job_table_join.' JOIN `' . wpjobportal::$_db->prefix . 'wj_portal_companies` AS company ON job.companyid = company.id
                    LEFT JOIN `' . wpjobportal::$_db->prefix . 'wj_portal_users` AS user ON user.id = job.uid
                    WHERE job.id = ' . esc_sql($wpjobportal_jobid);
        $wpjobportal_result = wpjobportaldb::get_row($query);
        return $wpjobportal_result;
    }
    // function to fetch jobseeker emial by resume id
    function getJobSeekerEmailByResumeId($wpjobportal_resumeid){
        if(!is_numeric($wpjobportal_resumeid))
            return false;
        $query = 'SELECT resume.email_address AS useremailfromresume, user.emailaddress as useremail
                    FROM `' . wpjobportal::$_db->prefix . 'wj_portal_resume` AS resume
                    JOIN `' . wpjobportal::$_db->prefix . 'wj_portal_users` AS user ON user.id = resume.uid
                    WHERE resume.id = ' . esc_sql($wpjobportal_resumeid);
        $wpjobportal_result = wpjobportaldb::get_row($query);
        return $wpjobportal_result;
    }


    function getJobApp($wpjobportal_jobid){
        if(!is_numeric($wpjobportal_jobid))
            return false;
        $query = "SELECT job.*, cat.cat_title, jobtype.title AS jobtypetitle, company.name AS companyname ,company.logofilename AS logo ,company.id AS companyid,salaryrangetype.title AS salaryrangetype,jobtype.color AS jobtypecolor,( SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` WHERE jobid = job.id) AS totalresume
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON job.companyid = company.id
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON job.jobcategory = cat.id
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON job.jobtype = jobtype.id
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_salaryrangetypes` AS salaryrangetype ON salaryrangetype.id = job.salaryduration
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON city.id = (SELECT cityid FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobcities` WHERE jobid = job.id ORDER BY id DESC LIMIT 1)
                WHERE job.status != 0 AND job.id =". esc_sql($wpjobportal_jobid);
        $wpjobportal_result = wpjobportaldb::get_results($query);

        return $wpjobportal_result;
    }


    function getQuickApplyMessageByresume($cvid) {
        if (!is_numeric($cvid))
            return false;

        $query = "SELECT apply_message FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` WHERE cvid = ". esc_sql($cvid);
        $wpjobportal_result = wpjobportal::$_db->get_var($query);
        return $wpjobportal_result;
    }

    function getMessagekey(){
        $wpjobportal_key = 'jobapply';if(wpjobportal::$_common->wpjp_isadmin()){$wpjobportal_key = 'admin_'.$wpjobportal_key;}return $wpjobportal_key;
    }

    function applyOnJob(){
        $wpjobportal_data  = WPJOBPORTALrequest::get('post');

        $quick_apply_flag = WPJOBPORTALrequest::getVar('quickapply','','');

        // store resume check for quick apply
        $wpjobportal_store_resume_for_apply = 1;
        if($quick_apply_flag == 1){
            $wpjobportal_store_resume_for_apply = WPJOBPORTALincluder::getJSModel('quickapply')->quickApplyOnJob();
        }

        // if resume stored this variable will contain resume id
        if(is_numeric($wpjobportal_store_resume_for_apply)){ // regular job apply
            return $this->jobapply(1);
        }
    }

}
?>
