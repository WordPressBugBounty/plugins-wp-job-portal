<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALrssfeedbackModel {

    function getrssjobs() {
        $job_rss = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('job_rss');
        if ($job_rss != 1) {
            return false;
        }

        $curdate = current_time('mysql');
        $limit = absint(apply_filters('wpjobportal_rss_jobs_feed_limit', 50));
        if ($limit < 1) {
            $limit = 50;
        }

        $query = "SELECT job.title, job.description, job.noofjobs, job.id, job.created, job.modified,
                        job.startpublishing, job.stoppublishing, job.address1, job.address2, job.hidesalaryrange,
                        cat.cat_title, company.logofilename AS logofilename, company.id AS companyid,
                        company.name AS comp_title, jobtype.title AS jobtype, jobstatus.title AS jobstatus,
                        job.salarymax, job.salarymin, job.salarytype, srtype.title AS srangetypetitle,
                        jobtype.color AS jobtypecolor, CONCAT(job.alias,'-',job.id) AS jobaliasid, job.currency
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                    JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON job.jobcategory = cat.id
                    JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON job.jobtype = jobtype.id
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobstatus` AS jobstatus ON job.jobstatus = jobstatus.id
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON job.companyid = company.id
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_salaryrangetypes` AS srtype ON srtype.id = job.salaryduration
                    WHERE job.status = 1 AND job.startpublishing <= '" . esc_sql($curdate) . "' AND job.stoppublishing >= '" . esc_sql($curdate) . "'
                    ORDER BY job.startpublishing DESC
                    LIMIT " . esc_sql($limit);
        $jobs = wpjobportal::$_db->get_results($query);

        if (!empty($jobs)) {
            foreach ($jobs as $job) {
                $job->locations = $this->getJobLocationsForFeed($job->id);
            }
        }

        return $jobs;
    }

    function getJobLocationsForFeed($job_id) {
        if (!is_numeric($job_id)) {
            return '';
        }

        $query = "SELECT COALESCE(NULLIF(city.name, ''), NULLIF(city.cityName, '')) AS cityname,
                         state.name AS statename,
                         country.name AS countryname
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobcities` AS jobcity
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON city.id = jobcity.cityid
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_states` AS state ON state.id = city.stateid
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_countries` AS country ON country.id = COALESCE(NULLIF(city.countryid, 0), state.countryid)
                    WHERE jobcity.jobid = %d
                    ORDER BY country.name ASC, state.name ASC, city.name ASC";
        $query = wpjobportal::$_db->prepare($query, $job_id);
        $locations = wpjobportal::$_db->get_results($query);

        if (empty($locations)) {
            return '';
        }

        $output = array();
        foreach ($locations as $location) {
            $parts = array();
            if (!empty($location->cityname)) {
                $parts[] = $location->cityname;
            }
            if (!empty($location->statename)) {
                $parts[] = $location->statename;
            }
            if (!empty($location->countryname)) {
                $parts[] = $location->countryname;
            }
            $location_text = trim(implode(', ', array_filter($parts)));
            if ($location_text != '') {
                $output[$location_text] = $location_text;
            }
        }

        return implode(' | ', $output);
    }

    function getrssresumes() {
        $resume_rss = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('resume_rss');
        if ($resume_rss != 1) {
            return false;
        }

        $limit = absint(apply_filters('wpjobportal_rss_resumes_feed_limit', 50));
        if ($limit < 1) {
            $limit = 50;
        }

        $query = "SELECT resume.id, resume.application_title, resume.photo, resume.first_name, resume.last_name,
                        resume.email_address, resume.cat_title, resume.gender, resume.resume, resume.created, resume.last_modified,
                        CONCAT(resume.alias,'-',resume.id) AS resumealiasid
                    FROM (
                        SELECT resume.id, resume.application_title, resume.photo, resume.first_name, resume.last_name,
                               resume.email_address, resume.gender, resume.resume, resume.created, resume.last_modified,
                               resume.alias, cat.cat_title
                        FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume
                        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON resume.job_category = cat.id
                        WHERE resume.status = 1 AND resume.searchable = 1
                        ORDER BY resume.created DESC
                        LIMIT " . esc_sql($limit) . "
                    ) AS resume";

        $result = wpjobportal::$_db->get_results($query);
        if (!empty($result)) {
            foreach ($result as $rs) {
                $query = "SELECT filename, filetype, filesize FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumefiles` WHERE resumeid = %d";
                $query = wpjobportal::$_db->prepare($query, $rs->id);
                $rs->filename = wpjobportal::$_db->get_results($query);
            }
        }
        return $result;
    }
}

?>
