<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALReportModel {

    function getChartColor() {
        $wpjobportal_colors = array('#3366CC', '#DC3912', '#FF9900', '#109618', '#990099', '#B77322', '#8B0707', '#AAAA11', '#316395', '#DD4477', '#3B3EAC', '#ADD042', '#9D98CA', '#ED3237', '#585570', '#4E5A62', '#5CC6D0');
        return $wpjobportal_colors;
    }

    function getOverallReports() {
        //Line Chart Data
        $wpjobportal_curdate = gmdate('Y-m-d');
        $wpjobportal_dates = '';
        $fromdate = gmdate('Y-m-d', strtotime("now -1 month"));
        $wpjobportal_nextdate = $wpjobportal_curdate;
        //Query to get Data
        $query = "SELECT created FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` WHERE date(created) >= '" . esc_sql($fromdate) . "' AND date(created) <= '" . esc_sql($wpjobportal_curdate) . "'";
        $wpjobportal_jobs = wpjobportal::$_db->get_results($query);

        $query = "SELECT created FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs`";
        wpjobportal::$_data['tot_jobs'] =  wpjobportal::$_db->get_results($query);

        $query = "SELECT created FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` WHERE date(created) >= '" . esc_sql($fromdate) . "' AND date(created) <= '" . esc_sql($wpjobportal_curdate) . "'";
        $wpjobportal_resume = wpjobportal::$_db->get_results($query);

         $query = "SELECT count(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` WHERE status = 1 ";
        wpjobportal::$_data['presume'] = wpjobportal::$_db->get_var($query);

        $query = "SELECT created FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` WHERE date(created) >= '" . esc_sql($fromdate) . "' AND date(created) <= '" . esc_sql($wpjobportal_curdate) . "'";
        $wpjobportal_companies = wpjobportal::$_db->get_results($query);

         $query = "SELECT count(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` ";
        wpjobportal::$_data['tot_comp'] = wpjobportal::$_db->get_var($query);

        $query = "SELECT apply_date FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` WHERE date(apply_date) >= '" . esc_sql($fromdate) . "' AND date(apply_date) <= '" . esc_sql($wpjobportal_curdate) . "'";
        $wpjobportal_appliedresume = wpjobportal::$_db->get_results($query);

        $wpjobportal_date_jobs = array();
        $wpjobportal_date_companies = array();
        $wpjobportal_date_resume = array();
        $wpjobportal_date_appliedresume = array();
        foreach ($wpjobportal_jobs AS $wpjobportal_job) {
            if (!isset($wpjobportal_date_jobs[date_i18n('Y-m-d', strtotime($wpjobportal_job->created))]))
                $wpjobportal_date_jobs[date_i18n('Y-m-d', strtotime($wpjobportal_job->created))] = 0;
            $wpjobportal_date_jobs[date_i18n('Y-m-d', strtotime($wpjobportal_job->created))] = $wpjobportal_date_jobs[date_i18n('Y-m-d', strtotime($wpjobportal_job->created))] + 1;
        }
        foreach ($wpjobportal_resume AS $rs) {
            if (!isset($wpjobportal_date_resume[date_i18n('Y-m-d', strtotime($rs->created))]))
                $wpjobportal_date_resume[date_i18n('Y-m-d', strtotime($rs->created))] = 0;
            $wpjobportal_date_resume[date_i18n('Y-m-d', strtotime($rs->created))] = $wpjobportal_date_resume[date_i18n('Y-m-d', strtotime($rs->created))] + 1;
        }
        foreach ($wpjobportal_companies AS $wpjobportal_company) {
            if (!isset($wpjobportal_date_companies[date_i18n('Y-m-d', strtotime($wpjobportal_company->created))]))
                $wpjobportal_date_companies[date_i18n('Y-m-d', strtotime($wpjobportal_company->created))] = 0;
            $wpjobportal_date_companies[date_i18n('Y-m-d', strtotime($wpjobportal_company->created))] = $wpjobportal_date_companies[date_i18n('Y-m-d', strtotime($wpjobportal_company->created))] + 1;
        }
        foreach ($wpjobportal_appliedresume AS $ar) {
            if (!isset($wpjobportal_date_appliedresume[date_i18n('Y-m-d', strtotime($ar->apply_date))]))
                $wpjobportal_date_appliedresume[date_i18n('Y-m-d', strtotime($ar->apply_date))] = 0;
            $wpjobportal_date_appliedresume[date_i18n('Y-m-d', strtotime($ar->apply_date))] = $wpjobportal_date_appliedresume[date_i18n('Y-m-d', strtotime($ar->apply_date))] + 1;
        }
        $wpjobportal_job_s = 0;
        $wpjobportal_company_s = 0;
        $wpjobportal_resume_s = 0;
        $wpjobportal_appliedresume_s = 0;
        $wpjobportal_json_array = "";

        do {
            $year = date_i18n('Y', strtotime($wpjobportal_nextdate));
            $wpjobportal_month = date_i18n('m', strtotime($wpjobportal_nextdate));
            $wpjobportal_month = $wpjobportal_month - 1; //js month are 0 based
            $wpjobportal_day = date_i18n('d', strtotime($wpjobportal_nextdate));
            $wpjobportal_job_tmp = isset($wpjobportal_date_jobs[$wpjobportal_nextdate]) ? $wpjobportal_date_jobs[$wpjobportal_nextdate] : 0;
            $wpjobportal_resume_tmp = isset($wpjobportal_date_resume[$wpjobportal_nextdate]) ? $wpjobportal_date_resume[$wpjobportal_nextdate] : 0;
            $wpjobportal_company_tmp = isset($wpjobportal_date_companies[$wpjobportal_nextdate]) ? $wpjobportal_date_companies[$wpjobportal_nextdate] : 0;
            $wpjobportal_appliedresume_tmp = isset($wpjobportal_date_appliedresume[$wpjobportal_nextdate]) ? $wpjobportal_date_appliedresume[$wpjobportal_nextdate] : 0;
            $wpjobportal_json_array .= "[new Date($year,$wpjobportal_month,$wpjobportal_day),$wpjobportal_job_tmp,$wpjobportal_resume_tmp,$wpjobportal_company_tmp,$wpjobportal_appliedresume_tmp],";
            $wpjobportal_job_s += $wpjobportal_job_tmp;
            $wpjobportal_company_s += $wpjobportal_company_tmp;
            $wpjobportal_resume_s += $wpjobportal_resume_tmp;
            $wpjobportal_appliedresume_s += $wpjobportal_appliedresume_tmp;
            if($wpjobportal_nextdate == $fromdate){
                break;
            }
            $wpjobportal_nextdate = date_i18n('Y-m-d', strtotime($wpjobportal_nextdate . " -1 days"));
        } while ($wpjobportal_nextdate != $fromdate);

        wpjobportal::$_data['totaljobs'] = $wpjobportal_job_s;
        wpjobportal::$_data['totalcompany'] = $wpjobportal_company_s;
        wpjobportal::$_data['totalresume'] = $wpjobportal_resume_s;
        wpjobportal::$_data['totalappliedresume'] = $wpjobportal_appliedresume_s;

        wpjobportal::$_data['line_chart_json_array'] = $wpjobportal_json_array;

        $query = "SELECT cat.cat_title,(SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` WHERE jobcategory = cat.id) AS jobs
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat
                    ORDER BY jobs DESC LIMIT 5";
        $wpjobportal_jobs = wpjobportal::$_db->get_results($query);
        /*$query = "SELECT cat.cat_title,(SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` ) AS companies
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat
                    ORDER BY companies DESC LIMIT 5";*/
       // $wpjobportal_companies = wpjobportal::$_db->get_results($query);
        $query = "SELECT cat.cat_title,(SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` WHERE job_category = cat.id) AS resumes
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat
                    ORDER BY resumes DESC LIMIT 5";
        $wpjobportal_resume = wpjobportal::$_db->get_results($query);
        wpjobportal::$_data['catbar1'] = '';
        wpjobportal::$_data['catbar2'] = '';
        wpjobportal::$_data['catpie'] = '';
        $wpjobportal_colors = $this->getChartColor();
        for ($wpjobportal_i = 0; $wpjobportal_i < 5; $wpjobportal_i++) {
            $wpjobportal_job = $wpjobportal_jobs[$wpjobportal_i];
            /*$wpjobportal_company = $wpjobportal_companies[$wpjobportal_i];*/
            $resum = $wpjobportal_resume[$wpjobportal_i];
            wpjobportal::$_data['catbar1'] .= "['" . wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_job->cat_title) . "', " . $wpjobportal_job->jobs . ", '" . $wpjobportal_colors[$wpjobportal_i] . "', '" . esc_html(__('Jobs', 'wp-job-portal')) . "' ],";
            wpjobportal::$_data['catbar2'] .= "['" . wpjobportalphplib::wpJP_htmlspecialchars($resum->cat_title) . "', " . $resum->resumes . ", '" . $wpjobportal_colors[$wpjobportal_i] . "', '" . esc_html(__('Jobs', 'wp-job-portal')) . "' ],";
            /*wpjobportal::$_data['catpie'] .= "['" . $wpjobportal_company->cat_title . "', " . $wpjobportal_company->companies . "],";*/
        }

        $query = "SELECT city.name,(SELECT COUNT(jobid) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobcities` WHERE cityid = city.id ) AS jobs
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city
                    ORDER BY jobs DESC LIMIT 5";
        $wpjobportal_jobs = wpjobportal::$_db->get_results($query);
        $query = "SELECT city.name,(SELECT COUNT(companyid) FROM `" . wpjobportal::$_db->prefix . "wj_portal_companycities` WHERE cityid = city.id) AS companies
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city
                    ORDER BY companies DESC LIMIT 5";
        $wpjobportal_companies = wpjobportal::$_db->get_results($query);
        $query = "SELECT city.name,(SELECT COUNT(resumeid) FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumeaddresses` WHERE address_city = city.id) AS resumes
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city
                    ORDER BY resumes DESC LIMIT 5";
        $wpjobportal_resume = wpjobportal::$_db->get_results($query);
        wpjobportal::$_data['citybar1'] = '';
        wpjobportal::$_data['citybar2'] = '';
        wpjobportal::$_data['citypie'] = '';
        for ($wpjobportal_i = 0; $wpjobportal_i < 5; $wpjobportal_i++) {
            $wpjobportal_job = $wpjobportal_jobs[$wpjobportal_i];
            $wpjobportal_company = $wpjobportal_companies[$wpjobportal_i];
            $resum = $wpjobportal_resume[$wpjobportal_i];
            wpjobportal::$_data['citybar1'] .= "['" . wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_job->name) . "', " . $wpjobportal_job->jobs . ", '" . $wpjobportal_colors[$wpjobportal_i] . "', '" . esc_html(__('Jobs', 'wp-job-portal')) . "' ],";
            wpjobportal::$_data['citybar2'] .= "['" . wpjobportalphplib::wpJP_htmlspecialchars($resum->name) . "', " . $resum->resumes . ", '" . $wpjobportal_colors[$wpjobportal_i] . "', '" . esc_html(__('Jobs', 'wp-job-portal')) . "' ],";
            wpjobportal::$_data['citypie'] .= "['" . wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_company->name) . "', " . $wpjobportal_company->companies . "],";
        }

        $query = "SELECT jobtype.title,(SELECT COUNT(jobid) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` WHERE jobtype = jobtype.id ) AS jobs
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype
                    ORDER BY jobs DESC LIMIT 5";
        $wpjobportal_jobs = wpjobportal::$_db->get_results($query);
        $query = "SELECT jobtype.title,(SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` WHERE jobtype = jobtype.id) AS resumes
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype
                    ORDER BY resumes DESC LIMIT 5";
        $wpjobportal_resume = wpjobportal::$_db->get_results($query);
        wpjobportal::$_data['jobtypebar1'] = '';
        wpjobportal::$_data['jobtypebar2'] = '';
        for ($wpjobportal_i = 0; $wpjobportal_i < 5; $wpjobportal_i++) {
            if (isset($wpjobportal_jobs[$wpjobportal_i]) && isset($wpjobportal_jobs[$wpjobportal_i])) {
                $wpjobportal_job = $wpjobportal_jobs[$wpjobportal_i];
                $resum = $wpjobportal_resume[$wpjobportal_i];
                wpjobportal::$_data['jobtypebar1'] .= "['" . wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_job->title) . "', " . $wpjobportal_job->jobs . ", '" . $wpjobportal_colors[$wpjobportal_i] . "', '" . esc_html(__('Jobs', 'wp-job-portal')) . "' ],";
                wpjobportal::$_data['jobtypebar2'] .= "['" . wpjobportalphplib::wpJP_htmlspecialchars($resum->title) . "', " . $resum->resumes . ", '" . $wpjobportal_colors[$wpjobportal_i] . "', '" . esc_html(__('Jobs', 'wp-job-portal')) . "' ],";
            }
        }
    }

    function getMessagekey(){
        $wpjobportal_key = 'report';if(wpjobportal::$_common->wpjp_isadmin()){$wpjobportal_key = 'admin_'.$wpjobportal_key;}return $wpjobportal_key;
    }


}

?>
