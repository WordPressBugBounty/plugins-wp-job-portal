<?php
//deleteUserPhoto
if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALJobseekerModel {

    function getConfigurationForControlPanel() {
        // configuration for layout
        $wpjobportal_config =  wpjobportal::$_config->getConfigByFor('jscontrolpanel');
        $wpjobportal_config['show_applied_resume_status'] = wpjobportal::$_config->getConfigurationByConfigName('show_applied_resume_status');
        wpjobportal::$_data['configs'] = $wpjobportal_config;
    }

    function getMessagekey(){
        $wpjobportal_key = 'jobseeker';if(wpjobportal::$_common->wpjp_isadmin()){$wpjobportal_key = 'admin_'.$wpjobportal_key;}return $wpjobportal_key;
    }

    function getResumeStatusByUid($wpjobportal_uid) {
        if (!is_numeric($wpjobportal_uid))
            return false;
        $query = "SELECT jobapply.action_status, job.title, job.city ,resume.application_title, resume.photo,job.id AS jobid
                    , resume.email_address,jobcat.cat_title,resume.id,jobapply.apply_date As jobapply,company.id AS companyid,company.logofilename AS companylogo
                    FROM " . wpjobportal::$_db->prefix . "wj_portal_resume AS resume
                    JOIN " . wpjobportal::$_db->prefix . "wj_portal_jobapply AS jobapply ON jobapply.cvid = resume.id
                    JOIN " . wpjobportal::$_db->prefix . "wj_portal_jobs AS job ON job.id = jobapply.jobid
                    ".wpjobportal::$_company_job_table_join." JOIN " . wpjobportal::$_db->prefix . "wj_portal_companies AS company ON company.id = job.companyid
                    LEFT JOIN " . wpjobportal::$_db->prefix . "wj_portal_categories AS jobcat ON jobcat.id = resume.job_category
                    WHERE resume.uid = ". esc_sql($wpjobportal_uid)." GROUP BY jobapply.id LIMIT 0,5";

        wpjobportal::$_data[0]['resume'] = wpjobportaldb::get_results($query);

        $query = "SELECT resume.id as resumeid ,count(*) as resumeno
                    FROM " . wpjobportal::$_db->prefix . "wj_portal_resume AS resume
                    WHERE `uid`=".esc_sql($wpjobportal_uid)."
                    GROUP BY resume.id  ORDER BY resume.id ASC LIMIT 0,1 ";// ASC to change to same resume shown in my resume listing in case of missing mutlti resume addon
        wpjobportal::$_data[0]['resume']['info'] = wpjobportaldb::get_results($query);
    }

    // tried using the above getResumeStatusByUid function
    //but setting data in "wpjobportal::$_data[0]['resume']" causes listings to break
    // listings have foreach on "wpjobportal::$_data[0]"
    function getResumeInfoForJobSeekerLeftMenu($wpjobportal_uid){
        if (!is_numeric($wpjobportal_uid))
            return false;
        $query = "SELECT resume.id as resumeid, resume.application_title as application_title
                    FROM " . wpjobportal::$_db->prefix . "wj_portal_resume AS resume
                    WHERE `uid`='".esc_sql($wpjobportal_uid)."'
                    ORDER BY resume.id ASC ";
        wpjobportal::$wpjobportal_data['resume_info_menu'] = wpjobportaldb::get_row($query);
     }

    function getLatestJobs() {
        $query = "SELECT DISTINCT job.id AS jobid,job.tags AS jobtags,job.title,job.created,job.city,job.currency,
        CONCAT(job.alias,'-',job.id) AS jobaliasid,job.noofjobs,job.endfeatureddate,
        job.isfeaturedjob,job.status,job.startpublishing,job.stoppublishing,cat.cat_title,company.id AS companyid,company.name AS companyname,company.logofilename, jobtype.title AS jobtypetitle,job.id AS id,
        job.params,CONCAT(company.alias,'-',company.id) AS companyaliasid,LOWER(jobtype.title) AS jobtypetit,
        job.salarymax,job.salarymin,job.salarytype,job.description,srtype.title AS srangetypetitle,jobtype.color AS jobtypecolor
        FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
        ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON company.id = job.companyid
        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON cat.id = job.jobcategory
        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_salaryrangetypes` AS srtype ON srtype.id = job.salaryduration
        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON jobtype.id = job.jobtype
        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobcities` AS jobcity ON jobcity.jobid = job.id
        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON city.id = jobcity.cityid
        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_states` AS state ON state.countryid = city.countryid
        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_countries` AS country ON country.id = city.countryid
        WHERE job.status = 1 AND DATE(job.startpublishing) <= CURDATE() AND DATE(job.stoppublishing) >= CURDATE()
                    ORDER BY job.created DESC
                    LIMIT 0,4";
        $wpjobportal_data = wpjobportaldb::get_results($query);
        foreach ($wpjobportal_data as $wpjobportal_job) {
            $wpjobportal_job->location = WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($wpjobportal_job->city);
        }
        wpjobportal::$_data['latestjobs'] = $wpjobportal_data;
        wpjobportal::$wpjobportal_data['fields'] = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldsOrderingforForm(2);
        wpjobportal::$_data['config'] = wpjobportal::$_config->getConfigByFor('job');
    }

    function getJobsAppliedRecently($wpjobportal_uid){
        if (!is_numeric($wpjobportal_uid))
            return false;
        $query = "SELECT job.id AS jobid,job.city,job.title,job.noofjobs,CONCAT(job.alias,'-',job.id) AS jobaliasid ,CONCAT(company.alias,'-',companyid) AS companyaliasid, job.serverid,
                 jobapply.action_status AS resumestatus,jobapply.apply_date,jobapply.status AS applystatus,job.currency,
                 company.id AS companyid, company.name AS companyname,company.logofilename,category.cat_title,
                 jobtype.title AS jobtypetitle, jobstatus.title AS jobstatustitle,resume.id AS resumeid,resume.salaryfixed as salary,resume.application_title,job.params,job.created,LOWER(jobtype.title) AS jobtype
                ,jobapply.id AS id,resume.first_name,resume.last_name,job.salarymin,job.salarymax,job.salarytype,
                                salaryrangetype.title AS srangetypetitle,job.endfeatureddate,job.isfeaturedjob,job.status,job.startpublishing,job.stoppublishing,jobtype.color AS jobtypecolor,jobapply.coverletterid,job.description
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` AS jobapply
                 JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job ON job.id = jobapply.jobid
                 JOIN `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume ON resume.id = jobapply.cvid
                 ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON company.id = job.companyid
                 LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS category ON category.id = job.jobcategory
                 LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON jobtype.id = job.jobtype
                 LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobstatus` AS jobstatus ON jobstatus.id = job.jobstatus
                 LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_salaryrangetypes` AS salaryrangetype ON salaryrangetype.id = job.salaryduration
                 WHERE jobapply.uid = ". esc_sql($wpjobportal_uid);
        $query.= " ORDER BY resume.id ";
        $query.=" LIMIT  0,4";
        $wpjobportal_results = wpjobportaldb::get_results($query);
        $wpjobportal_data = array();
        foreach ($wpjobportal_results AS $d) {
            $d->location = WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($d->city);
            if(in_array('coverletter', wpjobportal::$_active_addons)){
                $d->coverlettertitle = WPJOBPORTALincluder::getJSModel('coverletter')->getCoverLetterTitleFromID($d->coverletterid);
            }
            $wpjobportal_data[] = $d;
        }
        wpjobportal::$_data['appliedjobs'] = $wpjobportal_data;
        wpjobportal::$wpjobportal_data['fields'] = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldsOrderingforForm(2);
        wpjobportal::$_data['config'] = wpjobportal::$_config->getConfigByFor('job');
        return;
    }

    function getUserinfo($wpjobportal_uid){
        if (!is_numeric($wpjobportal_uid))
            return false;
        $query = "SELECT * FROM `".wpjobportal::$_db->prefix."wj_portal_users` as users
        WHERE `id`=".esc_sql($wpjobportal_uid);
        $wpjobportal_data = wpjobportaldb::get_results($query);
        wpjobportal::$_data['userprofile'] = $wpjobportal_data;
         $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs`";
        wpjobportal::$_data['totaljobs'] = wpjobportal::$_db->get_var($query);
        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies`";
        wpjobportal::$_data['totalcompanies'] = wpjobportal::$_db->get_var($query);
        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` where uid = ".esc_sql($wpjobportal_uid)." AND status = 1";
        wpjobportal::$_data['totalresume'] = wpjobportal::$_db->get_var($query);
        if(!in_array('multiresume', wpjobportal::$_active_addons) && wpjobportal::$_data['totalresume'] > 1){
            wpjobportal::$_data['totalresume'] = 1;
        }
        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` where status = 1 and uid=".esc_sql($wpjobportal_uid);
        wpjobportal::$_data['totaljobapply'] = wpjobportal::$_db->get_var($query);
        if(in_array('shortlist', wpjobportal::$_active_addons)){
            // modified the below code to make sure that the jobs that are shown on shortlisted job listing are counted for dashboard stat
            $query23 = "SELECT COUNT(shortlist.id)
                            FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobshortlist` AS shortlist
                            JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job ON  job.id = shortlist.jobid
                            WHERE shortlist.status = 1  AND shortlist.uid =". esc_sql($wpjobportal_uid)."
                            AND DATE(job.startpublishing) <= CURDATE() AND DATE(job.stoppublishing) >= CURDATE()";
            wpjobportal::$_data['totalshorlistjob'] = wpjobportal::$_db->get_var($query23);
        }
        $wpjobportal_curdate = gmdate('Y-m-d');
        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` WHERE DATE(startpublishing) <= '".esc_sql($wpjobportal_curdate)."' AND DATE(stoppublishing) >= '".esc_sql($wpjobportal_curdate)."' AND status = 1";
        wpjobportal::$_data['totalactivejobs'] = wpjobportal::$_db->get_var($query);
        $wpjobportal_newindays = wpjobportal::$_config->getConfigurationByConfigName('newdays');
        if ($wpjobportal_newindays == 0) {
            $wpjobportal_newindays = 7;
        }
        $time = strtotime($wpjobportal_curdate . ' -' . $wpjobportal_newindays . ' days');
        $lastdate = gmdate("Y-m-d", $time);
        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` WHERE DATE(created) >= DATE('".esc_sql($lastdate)."') AND DATE(created) <= DATE('".esc_sql($wpjobportal_curdate)."')";
        wpjobportal::$_data['totalnewjobs'] = wpjobportal::$_db->get_var($query);
        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` WHERE DATE(created) >= DATE('".esc_sql($lastdate)."') AND DATE(created) <= DATE('".esc_sql($wpjobportal_curdate)."')";
        wpjobportal::$_data['totalnewcompanies'] = wpjobportal::$_db->get_var($query);
        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` WHERE DATE(created) >= DATE('".esc_sql($lastdate)."') AND DATE(created) <= DATE('".esc_sql($wpjobportal_curdate)."')";
        wpjobportal::$_data['totalnewresume'] = wpjobportal::$_db->get_var($query);
        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` WHERE DATE(apply_date) >= DATE('".esc_sql($lastdate)."') AND DATE(apply_date) <= DATE('".esc_sql($wpjobportal_curdate)."')";
        wpjobportal::$_data['totalnewjobapply'] = wpjobportal::$_db->get_var($query);

    }

    function getJobsekerResumeTitle($wpjobportal_uid){
        if(!is_numeric($wpjobportal_uid))
            return false;
        $query="SELECT application_title as tite FROM `".wpjobportal::$_db->prefix."wj_portal_resume`
        WHERE `uid`=".esc_sql($wpjobportal_uid)." ORDER BY ID DESC LIMIT 0,1";
        $wpjobportal_data=wpjobportaldb::get_var($query);
        wpjobportal::$_data['application_title'] = $wpjobportal_data;
    }

    function getGraphDataNew($wpjobportal_uid=''){

        $query = "SELECT * FROM `".wpjobportal::$_db->prefix."wj_portal_jobtypes`
         WHERE `id`>0 LIMIT 0,3";
         $wpjobportal_data = wpjobportaldb::get_results($query);
         wpjobportal::$_data['jobtype'] = $wpjobportal_data;
         $wpjobportal_html = "['" . esc_html(__('Dates', 'wp-job-portal')) . "'";
          foreach (wpjobportal::$_data['jobtype'] as $wpjobportal_key ) {
            $wpjobportal_html .= ",'". wpjobportal::wpjobportal_getVariableValue($wpjobportal_key->title)."'";
            $wpjobportal_jobtype[] = $wpjobportal_key->id;
        }
        $query = "SELECT count(job.id) AS job,MONTH(job.created) AS MONTH, YEAR(job.created) AS YEAR ,type.id AS jobtype
                    FROM `".wpjobportal::$_db->prefix."wj_portal_jobs` AS job
                    RIGHT JOIN `".wpjobportal::$_db->prefix."wj_portal_jobtypes` AS type ON job.jobtype=type.id  ";
        $query .= " GROUP by MONTH(job.created),YEAR(job.created),type.id
                    ORDER BY YEAR(job.created),MONTH(job.created) ASC";
        $wpjobportal_result = wpjobportaldb::get_results($query);
        wpjobportal::$_data['jobseeker_info']['title'] = $wpjobportal_result;
        $wpjobportal_prev_workstations = '';
        foreach (wpjobportal::$_data['jobseeker_info']['title'] as $parent) {
            $crm_workstations = $parent->jobtype;
            if (($crm_workstations !='') && ($crm_workstations != $wpjobportal_prev_workstations)){
                $wpjobportal_prev_workstations = $crm_workstations;
               $crm_workstations;
            }
            // php 8 issue md_strlen function
            if($parent->MONTH != ''){
                if(wpjobportalphplib::wpJP_strlen($parent->MONTH) <= 1){
                    $parent->MONTH='0'.$parent->MONTH;
                }
            }
            wpjobportal::$_data['datachart'][$crm_workstations][$parent->YEAR][$parent->MONTH]=$parent->job;
        }
        $wpjobportal_html.="]";
         wpjobportal::$_data['stack_chart_horizontal']['title'] = $wpjobportal_html;
         wpjobportal::$_data['stack_chart_horizontal']['data']='';
         ///////*****TO Show All Month Till Last Month ****////////
         for ($wpjobportal_i=0; $wpjobportal_i<=11; $wpjobportal_i++) {
            $Date = gmdate('Y-m', strtotime("-$wpjobportal_i month"));
            $Time = wpjobportalphplib::wpJP_explode('-',$Date);
            $Month = $Time[1];
            $Year = $Time[0];
            $wpjobportal_dateObj = DateTime::createFromFormat('!m', $Month);
            $wpjobportal_monthName = $wpjobportal_dateObj->format('M');
             $MonthName=$wpjobportal_monthName.'-'.wpjobportalphplib::wpJP_substr($Year,-2);
            /////******Passing Data To Graph*********//////////
            $FullTime = wpjobportal::$_data['jobtype'][0]->id;
            $PartTime = wpjobportal::$_data['jobtype'][1]->id;
            $wpjobportal_internship = wpjobportal::$_data['jobtype'][2]->id;
            wpjobportal::$_data['stack_chart_horizontal']['data'] .= "['" . $MonthName . "',";
            $FullTimeData = isset(wpjobportal::$_data['datachart'][$FullTime][$Year][$Month]) ? wpjobportal::$_data['datachart'][$FullTime][$Year][$Month] : 0;
            $ParTimeData = isset(wpjobportal::$_data['datachart'][$PartTime][$Year][$Month]) ? wpjobportal::$_data['datachart'][$PartTime][$Year][$Month] : 0;
            $wpjobportal_internshipData = isset(wpjobportal::$_data['datachart'][$wpjobportal_internship][$Year][$Month]) ? wpjobportal::$_data['datachart'][$wpjobportal_internship][$Year][$Month] : 0;
            wpjobportal::$_data['stack_chart_horizontal']['data'] .=  $FullTimeData.",".$ParTimeData.",".$wpjobportal_internshipData."]";
            if($wpjobportal_i!=12){
             wpjobportal::$_data['stack_chart_horizontal']['data'] .= ',';
            }
        }
        return ;
    }

    function handleShortCodeOptions(){
        //handle attirbute for hide profile section on dashboard
        $wpjobportal_hide_profile_section = WPJOBPORTALrequest::getVar('hide_profile_section', 'shortcode_option', false);
        if($wpjobportal_hide_profile_section && $wpjobportal_hide_profile_section != ''){
            wpjobportal::$_data['shortcode_option_hide_profile_section'] = 1;
        }

        //handle attirbute for hide profile section on dashboard
        $wpjobportal_hide_graph = WPJOBPORTALrequest::getVar('hide_graph', 'shortcode_option', false);
        if($wpjobportal_hide_graph && $wpjobportal_hide_graph != ''){
            wpjobportal::$_data['shortcode_option_hide_graph'] = 1;
        }

        //handle attirbute for hide profile section on dashboard
        $wpjobportal_hide_newest_jobs = WPJOBPORTALrequest::getVar('hide_newest_jobs', 'shortcode_option', false);
        if($wpjobportal_hide_newest_jobs && $wpjobportal_hide_newest_jobs != ''){
            wpjobportal::$_data['shortcode_option_hide_newest_jobs'] = 1;
        }

        //handle attirbute for hide profile section on dashboard
        $wpjobportal_hide_job_applies = WPJOBPORTALrequest::getVar('hide_job_applies', 'shortcode_option', false);
        if($wpjobportal_hide_job_applies && $wpjobportal_hide_job_applies != ''){
            wpjobportal::$_data['shortcode_option_hide_job_applies'] = 1;
        }

        //handle attirbute for hide profile section on dashboard
        $wpjobportal_hide_stat_boxes = WPJOBPORTALrequest::getVar('hide_stat_boxes', 'shortcode_option', false);
        if($wpjobportal_hide_stat_boxes && $wpjobportal_hide_stat_boxes != ''){
            wpjobportal::$_data['shortcode_option_hide_stat_boxes'] = 1;
        }

        //handle attirbute for hide profile section on dashboard
        $wpjobportal_hide_invoices = WPJOBPORTALrequest::getVar('hide_invoices', 'shortcode_option', false);
        if($wpjobportal_hide_invoices && $wpjobportal_hide_invoices != ''){
            wpjobportal::$_data['shortcode_option_hide_invoices'] = 1;
        }

    }

    function getJobSeekerResumeData(){
        $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();

        if(!is_numeric($wpjobportal_uid)){
            return false;
        }




        $query = "SELECT SUM(resume.hits) FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume
                    WHERE resume.uid = ". esc_sql($wpjobportal_uid)." AND resume.quick_apply <> 1 ";
        $wpjobportal_total_hits = wpjobportal::$_db->get_var($query);

        $query = "SELECT resume.id,resume.first_name,resume.last_name,resume.application_title as applicationtitle,CONCAT(resume.alias,'-',resume.id) resumealiasid,resume.email_address,category.cat_title,resume.created,jobtype.title AS jobtypetitle,resume.photo,resume.salaryfixed as salary,
                resume.isfeaturedresume,resume.status,city.name AS cityname,state.name AS statename,country.name AS countryname,resume.id as resumeid,resume.endfeatureddate,resume.params,resume.last_modified,LOWER(jobtype.title) AS jobtypetit,jobtype.color as jobtypecolor,resume.skills
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS category ON category.id = resume.job_category
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON jobtype.id = resume.jobtype
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON city.id = (SELECT address_city FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumeaddresses` WHERE resumeid = resume.id LIMIT 1)
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_states` AS state ON state.id = city.stateid
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_countries` AS country ON country.id = city.countryid
                WHERE resume.uid = ". esc_sql($wpjobportal_uid)." AND resume.quick_apply <> 1 ";
        if(in_array('multiresume', wpjobportal::$_active_addons)){
            $query.= " ORDER BY resume.created DESC LIMIT 10 "; // fetching upto 20 resumes at the moment. but will only show 5 most uncomplete onse
        }else{
            $query.=" ORDER BY resume.id ASC LIMIT 0,1 ";
        }
        $wpjobportal_results = wpjobportal::$_db->get_results($query);
        $wpjobportal_resume_array = [];

        //collect resumes with percentage less than 100
        foreach ($wpjobportal_results as $wpjobportal_resume) {
            $wpjobportal_resumestatus = WPJOBPORTALincluder::getJSModel('resume')->getResumePercentage($wpjobportal_resume->id);
            $wpjobportal_percentage = $wpjobportal_resumestatus['percentage'];

            if ($wpjobportal_percentage < 100) {
                $wpjobportal_resume->resumestatus = $wpjobportal_resumestatus;
                $wpjobportal_resume->percentage = $wpjobportal_percentage;
                $wpjobportal_resume_array[] = $wpjobportal_resume;
            }
        }

        //sort by percentage descending (simplified)
        usort($wpjobportal_resume_array, fn($a, $b) => $b->percentage <=> $a->percentage);

        //keep only top 5 from array
        $wpjobportal_resume_array = array_slice($wpjobportal_resume_array, 0, 3);

        wpjobportal::$_data['jobseeker_data']['hits'] = $wpjobportal_total_hits;
        wpjobportal::$_data['jobseeker_data']['resumes'] = $wpjobportal_resume_array;

    }

}

?>
