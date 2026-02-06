<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALjobModel {
        public $class_prefix = '';

        function __construct(){
            if(wpjobportal::$wpjobportal_theme_chk == 1){
                $this->class_prefix = 'jsjb-jm';
            }elseif(wpjobportal::$wpjobportal_theme_chk == 2){
                $this->class_prefix = 'jsjb-jh';
            }
        }

        function setListStyleSession(){
            $wpjobportal_listingstyle = WPJOBPORTALrequest::getVar('styleid');
            if(wpjobportal::$wpjobportal_theme_chk == 1){
                update_option( 'jsjb_jm_listing_style', $wpjobportal_listingstyle );
            }else{
                update_option( 'jsjb_jh_listing_style', $wpjobportal_listingstyle );
            }

            return $wpjobportal_listingstyle;
        }

        function getNewestJobsForMap_Widget($wpjobportal_noofjobs) {
            if(!isset($wpjobportal_noofjobs))
                $wpjobportal_noofjobs = 0;
            if( ! is_numeric($wpjobportal_noofjobs))
                $wpjobportal_noofjobs = 0;
            if($wpjobportal_noofjobs > 100)
                $wpjobportal_noofjobs = 100;
            if($wpjobportal_noofjobs < 0)
                $wpjobportal_noofjobs = 0;

            $wpjobportal_id = "job.id AS id";
            $alias = ",CONCAT(job.alias,'-',job.id) AS aliasid ";
            $wpjobportal_companyaliasid = ", CONCAT(company.alias,'-',company.id) AS companyaliasid ";

            $query = "SELECT job.id,job.title, job.jobcategory, job.created, cat.cat_title
                , job.city, job.latitude, job.longitude
                , company.id AS companyid, company.name AS companyname,company.logofilename AS companylogo, jobtype.title AS jobtypetitle
                $alias $wpjobportal_companyaliasid

                FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON job.jobcategory = cat.id
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON job.jobtype = jobtype.id
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON job.companyid = company.id
                WHERE job.status = 1 AND DATE(job.startpublishing) <= CURDATE() AND DATE(job.stoppublishing) >= CURDATE()
                ORDER BY created DESC LIMIT " . esc_sql($wpjobportal_noofjobs);

            $wpjobportal_result = wpjobportaldb::get_results($query);

            foreach ($wpjobportal_result AS $wpjobportal_job) {
                if(!is_numeric($wpjobportal_job->id)){
                    continue;
                }
                if (empty($wpjobportal_job->latitude) || empty($wpjobportal_job->longitude)) {
                    $query = "SELECT city.name AS cityname, country.name AS countryname
                                FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobcities` AS job
                                JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON city.id = job.cityid
                                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_countries` AS country ON country.id = city.countryid
                                WHERE job.jobid = " . esc_sql($wpjobportal_job->id);
                    $wpjobportal_job->multicity = wpjobportaldb::get_results($query);
                }
            }
            $wpjobportal_jobs = $wpjobportal_result;
            foreach ($wpjobportal_jobs AS $wpjobportal_job) {
                $wpjobportal_job->joblink = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'viewjob', 'wpjobportalid'=>$wpjobportal_job->aliasid));
            }
            $wpjobportal_result = $wpjobportal_jobs;
            return $wpjobportal_result;
    }
    function getjobType($wpjobportal_jobid){

    }

    function getJobInfo($wpjobportal_jobid,$wpjobportal_uid){
        if (!is_numeric($wpjobportal_jobid))
            return false;
    $query = "SELECT DISTINCT job.id AS jobid,job.tags AS jobtags,job.title,job.created,job.city,
        CONCAT(job.alias,'-',job.id) AS jobaliasid,job.noofjobs,job.isfeaturedjob,job.status,
        cat.cat_title,company.id AS companyid,company.name AS companyname,company.logofilename, jobtype.title AS jobtypetitle,job.endfeatureddate,job.startpublishing,job.stoppublishing,
        job.params,CONCAT(company.alias,'-',company.id) AS companyaliasid,LOWER(jobtype.title) AS jobtypetit,
        job.salarymax,job.salarymin,job.salarytype,srtype.title AS srangetypetitle,jobtype.color AS jobtypecolor,job.currency
        FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
        ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON company.id = job.companyid
        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON cat.id = job.jobcategory
        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_salaryrangetypes` AS srtype ON srtype.id = job.salaryduration
        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON jobtype.id = job.jobtype
        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobcities` AS jobcity ON jobcity.jobid = job.id
        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON city.id = jobcity.cityid
        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_states` AS state ON state.countryid = city.countryid
        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_countries` AS country ON country.id = city.countryid
        WHERE job.id =". esc_sql($wpjobportal_jobid);
        $wpjobportal_results = wpjobportal::$_db->get_results($query);
        $wpjobportal_data = array();
        foreach ($wpjobportal_results AS $d) {
            $d->location = WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($d->city);
             $wpjobportal_data[] = $d;
        }
        wpjobportal::$_data['jobinfo'] = $wpjobportal_data;
        wpjobportal::$wpjobportal_data['fields'] = wpjobportal::$_wpjpfieldordering->getFieldsOrderingforForm(2);
        wpjobportal::$_data['config'] = wpjobportal::$_config->getConfigByFor('job');
        return;
    }

    function getJobsByTypes_Widget($wpjobportal_showalltypes, $haverecordss, $wpjobportal_maximumrecords) {
        if ((!is_numeric($wpjobportal_showalltypes)) || ( !is_numeric($haverecordss)) || ( !is_numeric($wpjobportal_maximumrecords)))
            return false;

        $haverecords = '';
        $wpjobportal_maxlimit = '';
        if ($haverecordss == 1) {
            $haverecords = " HAVING totaljobs > 0 ";
        }

        if ($wpjobportal_maximumrecords >= 0) {
            $wpjobportal_maxlimit = " LIMIT $wpjobportal_maximumrecords";
        }

        if ($wpjobportal_showalltypes == 1) {
            $haverecords = '';
            $wpjobportal_maxlimit = '';
        }

        $wpjobportal_inquery = " (SELECT COUNT(jobs.id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS jobs
                        WHERE jobtype.id = jobs.jobtype AND jobs.status = 1
                        AND DATE(jobs.startpublishing) <= CURDATE() AND DATE(jobs.stoppublishing) >= CURDATE() ) as totaljobs";
        $query = "SELECT DISTINCT jobtype.id, jobtype.title AS objtitle , CONCAT(jobtype.alias, '-' , jobtype.id) AS aliasid , ";
        $query .= $wpjobportal_inquery;
        $query .= " FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job ON jobtype.id = job.jobcategory
                    WHERE jobtype.isactive = 1 ";
        $query .= esc_sql($haverecords)." ORDER BY objtitle ".esc_sql($wpjobportal_maxlimit);
        $wpjobportal_results = wpjobportaldb::get_results($query);
        return $wpjobportal_results;
    }

    function getJobsBycategory_Widget($wpjobportal_showallcats, $haverecordss, $wpjobportal_maximumrecords) {
        if ((!is_numeric($wpjobportal_showallcats)) || ( !is_numeric($haverecordss)) || ( !is_numeric($wpjobportal_maximumrecords)))
            return false;

        $haverecords = '';
        $wpjobportal_maxlimit = '';
        if ($haverecordss == 1) {
            $haverecords = " HAVING totaljobs > 0 ";
        }

        if ($wpjobportal_maximumrecords >= 0) {
            $wpjobportal_maxlimit = " LIMIT " . $wpjobportal_maximumrecords;
        }

        if ($wpjobportal_showallcats == 1) {
            $haverecords = '';
            $wpjobportal_maxlimit = '';
        }

        $wpjobportal_inquery = " (SELECT COUNT(jobs.id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS jobs
                        WHERE cat.id = jobs.jobcategory AND jobs.status = 1
                        AND DATE(jobs.startpublishing) <= CURDATE() AND DATE(jobs.stoppublishing) >= CURDATE() ) as totaljobs";
        $query = "SELECT DISTINCT cat.id, cat.cat_title AS objtitle , CONCAT(cat.alias,'-',cat.id) AS aliasid,";
        $query .= $wpjobportal_inquery;
        $query .= " FROM `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job ON cat.id = job.jobcategory
                    WHERE cat.isactive = 1 ";
        $query .= esc_sql($haverecords)." ORDER BY objtitle ".esc_sql($wpjobportal_maxlimit);


        $wpjobportal_results = wpjobportaldb::get_results($query);
        return $wpjobportal_results;
    }

    function getJobsBylocation_Widget($wpjobportal_showjobsby, $wpjobportal_showonlyrecordhavejobs, $wpjobportal_maximumrecords) {
        if ((!is_numeric($wpjobportal_showjobsby)) || ( !is_numeric($wpjobportal_showonlyrecordhavejobs)) || ( !is_numeric($wpjobportal_maximumrecords)))
            return false;

        if ($wpjobportal_maximumrecords > 100)
            $wpjobportal_maximumrecords = 100;
        elseif ($wpjobportal_maximumrecords < 0)
            $wpjobportal_maximumrecords = 20;

        $haverecords = "";
        if ($wpjobportal_showonlyrecordhavejobs == 1) {
            $haverecords = " HAVING totaljobs > 0 ";
        }

        if ($wpjobportal_showjobsby == 1) {
            $query = "SELECT city.id AS locationid, city.name AS locationname, COUNT(job.id) AS totaljobs
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobcities` AS mcity ON mcity.cityid = city.id
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_countries` AS country ON country.id = city.countryid
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job ON (job.id = mcity.jobid AND job.status =1 AND job.stoppublishing >= CURDATE() )
                    WHERE country.enabled = 1
                    GROUP BY locationid ".esc_sql($haverecords)." ORDER BY totaljobs DESC , locationname ASC LIMIT " . esc_sql($wpjobportal_maximumrecords);
        } elseif ($wpjobportal_showjobsby == 2) {
            $query = "SELECT state.id AS locationid, state.name AS locationname, COUNT(job.id) AS totaljobs
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_states` AS state
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON state.id = city.stateid
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_countries` AS country ON country.id = city.countryid
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobcities` AS mcity ON mcity.cityid = city.id
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job ON (job.id = mcity.jobid AND job.status =1 AND job.stoppublishing >= CURDATE() )
                    WHERE country.enabled = 1
                    GROUP BY locationid ".esc_sql($haverecords)." ORDER BY totaljobs DESC, cityname ASC LIMIT " . esc_sql($wpjobportal_maximumrecords);
        } elseif ($wpjobportal_showjobsby == 3) {
            $query = "SELECT country.id AS locationid, country.name AS locationname,COUNT(job.id) AS totaljobs
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_countries` AS country
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON country.id = city.countryid
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobcities` AS mcity ON mcity.cityid = city.id
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job ON (job.id = mcity.jobid AND job.status =1 AND job.stoppublishing >= CURDATE() )
                    WHERE country.enabled = 1
                    GROUP BY locationid ".esc_sql($haverecords)." ORDER BY totaljobs DESC, locationname ASC LIMIT " . esc_sql($wpjobportal_maximumrecords);
        } else {
            return '';
        }

        $wpjobportal_results = wpjobportaldb::get_results($query);
        return $wpjobportal_results;
    }

    function getJobs_Widget($typeofjobs, $wpjobportal_noofjobs) {
        if ((!is_numeric($typeofjobs)) || ( !is_numeric($wpjobportal_noofjobs)))
            return '';
        $col = '';
        if ($typeofjobs == 1) { // newest jobs
            $wpjobportal_inquery = " WHERE job.status = 1  AND DATE(job.stoppublishing) >= CURDATE() ORDER BY job.created DESC LIMIT " . esc_sql($wpjobportal_noofjobs);
        } elseif ($typeofjobs == 2) { //top jobs
            $wpjobportal_inquery = " WHERE job.status = 1  AND DATE(job.stoppublishing) >= CURDATE() ORDER BY job.hits DESC LIMIT " . esc_sql($wpjobportal_noofjobs);
        } elseif ($typeofjobs == 3) { // hot jobs
            $col = ' COUNT(ja.jobid) as totalapply , ';
            $wpjobportal_inquery = " JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` AS ja ON ja.jobid = job.id WHERE job.status = 1 AND job.stoppublishing >= CURDATE() GROUP BY ja.jobid ORDER BY totalapply DESC LIMIT " . esc_sql($wpjobportal_noofjobs);
        }  elseif ($typeofjobs == 5) { // featured jobs
            $wpjobportal_inquery = " WHERE job.status = 1 AND DATE(job.endfeatureddate) >= CURDATE() AND job.isfeaturedjob = 1 AND job.stoppublishing >= CURDATE() ORDER BY job.created DESC LIMIT " . esc_sql($wpjobportal_noofjobs);
        } else {
            return '';
        }
        $query = "SELECT $col job.id AS jobid,job.title,job.created,job.city,CONCAT(job.alias,'-',job.id) AS jobaliasid,job.currency, job.stoppublishing,
                 cat.cat_title,company.id AS companyid,company.name AS companyname,company.logofilename, CONCAT(company.alias,'-',company.id) AS companyaliasid,
                 jobtype.color AS jobtypecolor, jobtype.title AS jobtypetitle,job.salarymax,job.salarymin,job.salarytype,salarytype.title AS srangetypetitle, careerlevel.title AS careerleveltitle,job.hits
                 FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                 ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON company.id = job.companyid
                 LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_salaryrangetypes` AS salarytype ON salarytype.id = job.salaryduration
                 LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON cat.id = job.jobcategory
                 LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON jobtype.id = job.jobtype
                 LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_careerlevels` AS careerlevel ON careerlevel.id = job.careerlevel ";
        $query .= $wpjobportal_inquery;
        $wpjobportal_results = wpjobportaldb::get_results($query);
        foreach ($wpjobportal_results AS $d) {
            $d->location = WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($d->city);
        }
        return $wpjobportal_results;
    }

    function getTopJobs() {

        $wpjobportal_result = array();
        $query = "SELECT job.id,job.title AS jobtitle,company.name AS companyname,cat.cat_title AS cattile,job.stoppublishing,
        salaryfrom.rangestart AS salaryfrom, salaryto.rangestart AS salaryto
        FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
        ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON job.companyid = company.id
        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON job.jobcategory = cat.id
        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_salaryrange` AS salaryfrom ON job.salaryrangefrom = salaryfrom.id
        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_salaryrange` AS salaryto ON job.salaryrangeto = salaryto.id
        ORDER BY job.created desc LIMIT 5";

        wpjobportal::$_data[0] = wpjobportaldb::get_results($query);

        return;
    }

    function approveQueueJobModel($wpjobportal_id) {
        if (is_numeric($wpjobportal_id) == false) return false;

        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('job');
        if($wpjobportal_row->load($wpjobportal_id)){
            $wpjobportal_row->columns['status'] = 1;
            $wpjobportal_startpublishing = strtotime($wpjobportal_row->startpublishing);
            $wpjobportal_stoppublishing = strtotime($wpjobportal_row->stoppublishing);
            $wpjobportal_datediff = $wpjobportal_stoppublishing - $wpjobportal_startpublishing;
            $wpjobportal_diff_days = floor($wpjobportal_datediff/(60*60*24));
            $wpjobportal_row->columns['startpublishing'] = gmdate('Y-m-d H:i:s');
            $wpjobportal_row->columns['stoppublishing'] = gmdate('Y-m-d H:i:s',strtotime(" +$wpjobportal_diff_days days"));
            if(!$wpjobportal_row->store()){
                return WPJOBPORTAL_APPROVE_ERROR;
            }
        }else{
            return WPJOBPORTAL_APPROVE_ERROR;
        }
        WPJOBPORTALincluder::getJSModel('emailtemplate')->sendMail(2, 3, $wpjobportal_id); // 2 for job,3 for Approve or reject Job
        return WPJOBPORTAL_APPROVED;
    }

    function rejectQueueJobModel($wpjobportal_id) {
        if (is_numeric($wpjobportal_id) == false)
            return false;

        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('job');
        if (!$wpjobportal_row->update(array('id' => $wpjobportal_id , 'status' => -1))) {
            return WPJOBPORTAL_REJECT_ERROR;
        }

       $wpjobportal_company_approve_email = WPJOBPORTALincluder::getJSModel('emailtemplate')->sendMail(1, -1, $wpjobportal_id);
        WPJOBPORTALincluder::getJSModel('emailtemplate')->sendMail(2, 3, $wpjobportal_id); // 2 for job,3 for reject or approve  Job
        return WPJOBPORTAL_REJECTED;
    }

    function rejectQueueFeaturedJobModel($wpjobportal_id) {
        if (is_numeric($wpjobportal_id) == false)
            return false;

        //8 featured job reject
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('job');
        if (!$wpjobportal_row->update(array( 'id' => $wpjobportal_id , 'isfeaturedjob' => -1))) {
            return WPJOBPORTAL_REJECT_ERROR;
        }
        WPJOBPORTALincluder::getJSModel('emailtemplate')->sendMail(2, 5, $wpjobportal_id); // 2 for job,5 for reject or approve featured job
        return WPJOBPORTAL_REJECTED;
    }

    function approveQueueFeaturedJobModel($wpjobportal_id) {
        if (is_numeric($wpjobportal_id) == false) return false;

        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('job');
        if($wpjobportal_row->load($wpjobportal_id)){
            $wpjobportal_row->columns['isfeaturedjob'] = 1;
            $wpjobportal_startfeatureddate = strtotime($wpjobportal_row->startfeatureddate);
            $wpjobportal_endfeatureddate = strtotime($wpjobportal_row->endfeatureddate);
            $wpjobportal_datediff = $wpjobportal_endfeatureddate - $wpjobportal_startfeatureddate;
            $wpjobportal_diff_days = floor($wpjobportal_datediff/(60*60*24));
            $wpjobportal_row->columns['startfeatureddate'] = gmdate('Y-m-d H:i:s');
            $wpjobportal_row->columns['endfeatureddate'] = gmdate('Y-m-d H:i:s',strtotime(" +$wpjobportal_diff_days days"));
            if(!$wpjobportal_row->store()){
                return WPJOBPORTAL_APPROVE_ERROR;
            }
        }else{
            return WPJOBPORTAL_APPROVE_ERROR;
        }
        WPJOBPORTALincluder::getJSModel('emailtemplate')->sendMail(2, 5, $wpjobportal_id); // 2 for job,5 for reject or approve featured job
        return WPJOBPORTAL_APPROVED;
    }

    function approveQueueAllJobsModel($wpjobportal_id, $wpjobportal_actionid) {
        /*
         * *  1 for comp&gold
         * *  2 for comp&feature
         * *  3 for gold&feature
         * *  4 for All
         */
        if (!is_numeric($wpjobportal_id))
            return false;
        switch ($wpjobportal_actionid) {
            case '1':
                $wpjobportal_result = $this->approveQueueJobModel($wpjobportal_id);
                break;
            case '2':
                $wpjobportal_result = $this->approveQueueJobModel($wpjobportal_id);
                $wpjobportal_result = $this->approveQueueFeaturedJobModel($wpjobportal_id);
                break;
            case '3':
                $wpjobportal_result = $this->approveQueueFeaturedJobModel($wpjobportal_id);
                break;
            case '4':
                $wpjobportal_result = $this->approveQueueFeaturedJobModel($wpjobportal_id);
                $wpjobportal_result = $this->approveQueueJobModel($wpjobportal_id);
                break;
        }
        return $wpjobportal_result;
    }

    function rejectQueueAllJobsModel($wpjobportal_id, $wpjobportal_actionid) {
        /*
         * *  1 for comp&gold
         * *  2 for comp&feature
         * *  3 for gold&feature
         * *  4 for All
         */
        if (!is_numeric($wpjobportal_id))
            return false;
        switch ($wpjobportal_actionid) {
            case '1':
                $wpjobportal_result = $this->rejectQueueJobModel($wpjobportal_id);
                //$wpjobportal_result = $this->rejectQueueGoldJobModel($wpjobportal_id);
                break;
            case '2':
                $wpjobportal_result = $this->rejectQueueJobModel($wpjobportal_id);
                $wpjobportal_result = $this->rejectQueueFeaturedJobModel($wpjobportal_id);
                break;
            case '3':
                //$wpjobportal_result = $this->rejectQueueGoldJobModel($wpjobportal_id);
                $wpjobportal_result = $this->rejectQueueFeaturedJobModel($wpjobportal_id);
                break;
            case '4':
                //$wpjobportal_result = $this->rejectQueueGoldJobModel($wpjobportal_id);
                $wpjobportal_result = $this->rejectQueueFeaturedJobModel($wpjobportal_id);
                $wpjobportal_result = $this->rejectQueueJobModel($wpjobportal_id);
                break;
        }
        return $wpjobportal_result;
    }

    function getMultiCityData($wpjobportal_jobid) {
        if (!is_numeric($wpjobportal_jobid))
            return false;

        $query = "SELECT mjob.*,city.id AS cityid,city.name AS cityname ,state.name AS statename,country.name AS countryname
                FROM " . wpjobportal::$_db->prefix . "wj_portal_jobcities AS mjob
                LEFT JOIN " . wpjobportal::$_db->prefix . "wj_portal_cities AS city on mjob.cityid=city.id
                LEFT JOIN " . wpjobportal::$_db->prefix . "wj_portal_states AS state on city.stateid=state.id
                LEFT JOIN " . wpjobportal::$_db->prefix . "wj_portal_countries AS country on city.countryid=country.id
                WHERE mjob.jobid=" . esc_sql($wpjobportal_jobid);

        $wpjobportal_data = wpjobportaldb::get_results($query);
        if (is_array($wpjobportal_data) AND ! empty($wpjobportal_data)) {
            $wpjobportal_i = 0;
            $multicitydata = "";
            foreach ($wpjobportal_data AS $multicity) {
                $last_index = count($wpjobportal_data) - 1;
                if ($wpjobportal_i == $last_index)
                    $multicitydata.=$multicity->cityname;
                else
                    $multicitydata.=$multicity->cityname . " ,";
                $wpjobportal_i++;
            }
            if ($multicitydata != "") {
                $mc = esc_html(__('JS multi city', 'wp-job-portal'));
                $multicity = (wpjobportalphplib::wpJP_strlen($multicitydata) > 35) ? $mc . wpjobportalphplib::wpJP_substr($multicitydata, 0, 35) . '...' : $multicitydata;
                return $multicity;
            } else
                return;
        }
    }
    // joomla code
    // function getSearchOptions() {
    //     $wpjobportal_searchjobconfig = wpjobportal::$_config->getConfigByFor('searchjob');

    //     $wpjobportal_searchoptions = array();
    //     $wpjobportal_companies = WPJOBPORTALincluder::getJSModel('company')->getAllCompaniesForSearchForCombo(esc_html(__('JS search all', 'wp-job-portal')));
    //     $wpjobportal_job_type = WPJOBPORTALincluder::getJSModel('jobtype')->getJobType(esc_html(__('JS_SEARCH_ALL', 'wp-job-portal')));
    //     $wpjobportal_jobstatus = WPJOBPORTALincluder::getJSModel('jobstatus')->getJobStatus(esc_html(__('JS_SEARCH_ALL', 'wp-job-portal')));
    //     $wpjobportal_heighesteducation = WPJOBPORTALincluder::getJSModel('highesteducation')->getHeighestEducation(esc_html(__('JS search all', 'wp-job-portal')));
    //     $wpjobportal_job_categories = WPJOBPORTALincluder::getJSModel('category')->getCategoriesForCombo(esc_html(__('JS search all', 'wp-job-portal')), '');
    //     $wpjobportal_job_salaryrange = WPJOBPORTALincluder::getJSModel('salaryrange')->getJobSalaryRangeForCombo(esc_html(__('JS search all', 'wp-job-portal')), '');
    //     $shift = WPJOBPORTALincluder::getJSModel('shift')->getShift(esc_html(__('JS search all', 'wp-job-portal')));
    //     $wpjobportal_countries = WPJOBPORTALincluder::getJSModel('country')->getCountriesForCombo('');

    //     if (!isset($this->_config)) {
    //         $this->_config = wpjobportal::$_config->getConfig();
    //     }
    //     $wpjobportal_searchoptions['country'] = WPJOBPORTALformfield::select('select.genericList', $wpjobportal_countries, 'country', 'class="inputbox required" ' . 'onChange="dochange(\'state\', this.value)"', 'value', 'text', '');
    //     if (isset($wpjobportal_states[1]))
    //         if ($wpjobportal_states[1] != '')
    //             $wpjobportal_searchoptions['state'] = WPJOBPORTALformfield::select('select.genericList', $wpjobportal_states, 'state', 'class="inputbox" ' . 'onChange="dochange(\'city\', this.value)"', 'value', 'text', '');
    //     if (isset($cities[1]))
    //         if ($cities[1] != '')
    //             $wpjobportal_searchoptions['city'] = WPJOBPORTALformfield::select('select.genericList', $cities, 'city', 'class="inputbox" ' . '', 'value', 'text', '');
    //     $wpjobportal_searchoptions['companies'] = WPJOBPORTALformfield::select('select.genericList', $wpjobportal_companies, 'company', 'class="inputbox" ' . '', 'value', 'text', '');
    //     $wpjobportal_searchoptions['jobcategory'] = WPJOBPORTALformfield::select('select.genericList', $wpjobportal_job_categories, 'jobcategory', 'class="inputbox" ' . '', 'value', 'text', '');
    //     $wpjobportal_searchoptions['jobsalaryrange'] = WPJOBPORTALformfield::select('select.genericList', $wpjobportal_job_salaryrange, 'jobsalaryrange', 'class="inputbox" ' . 'style="width:150px;"', 'value', 'text', '');
    //     $wpjobportal_searchoptions['salaryrangefrom'] = WPJOBPORTALformfield::select('select.genericList', WPJOBPORTALincluder::getJSModel('salaryrange')->getSalaryRangeForCombo(esc_html(__('JS From', 'wp-job-portal'))), 'salaryrangefrom', 'class="inputbox" ' . 'style="width:150px;"', 'value', 'text', '');
    //     $wpjobportal_searchoptions['salaryrangeto'] = WPJOBPORTALformfield::select('select.genericList', WPJOBPORTALincluder::getJSModel('salaryrange')->getSalaryRangeForCombo(esc_html(__('JS To', 'wp-job-portal'))), 'salaryrangeto', 'class="inputbox" ' . 'style="width:150px;"', 'value', 'text', '');
    //     $wpjobportal_searchoptions['salaryrangetypes'] = WPJOBPORTALformfield::select('select.genericList', WPJOBPORTALincluder::getJSModel('salaryrangetype')->getSalaryRangeTypes(''), 'salaryrangetype', 'class="inputbox" ' . 'style="width:150px;"', 'value', 'text', 2);
    //     $wpjobportal_searchoptions['jobstatus'] = WPJOBPORTALformfield::select('select.genericList', $wpjobportal_jobstatus, 'jobstatus', 'class="inputbox" ' . '', 'value', 'text', '');
    //     $wpjobportal_searchoptions['jobtype'] = WPJOBPORTALformfield::select('select.genericList', $wpjobportal_job_type, 'jobtype', 'class="inputbox" ' . '', 'value', 'text', '');
    //     $wpjobportal_searchoptions['heighestfinisheducation'] = WPJOBPORTALformfield::select('select.genericList', $wpjobportal_heighesteducation, 'heighestfinisheducation', 'class="inputbox" ' . '', 'value', 'text', '');
    //     $wpjobportal_searchoptions['shift'] = WPJOBPORTALformfield::select('select.genericList', $shift, 'shift', 'class="inputbox" ' . '', 'value', 'text', '');
    //     $wpjobportal_searchoptions['currency'] = WPJOBPORTALformfield::select('select.genericList', WPJOBPORTALincluder::getJSModel('currency')->getCurrency(), 'currency', 'class="inputbox" ' . 'style="width:150px;"', 'value', 'text', '');
    //     $wpjobportal_result = array();
    //     $wpjobportal_result[0] = $wpjobportal_searchoptions;
    //     $wpjobportal_result[1] = $wpjobportal_searchjobconfig;
    //     return $wpjobportal_result;
    // }

    function getJobbyIdForView($wpjobportal_job_id) {
        ////Start's WOrking From There FRIDAY//31..2020
        if (is_numeric($wpjobportal_job_id) == false) return false;
        global $job_portal_theme_options;
        $query = "SELECT job.*,company.url AS companyurl,company.logofilename,company.city AS compcity,company.isfeaturedcompany,cat.cat_title , company.name as companyname, jobtype.title AS jobtypetitle, company.id As companyid, company.alias AS companyalias,company.description AS company_desc
            ,(SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` WHERE jobid = job.id) AS totalapply
                , jobstatus.title AS jobstatustitle";
                 if(in_array('departments', wpjobportal::$_active_addons)){
                    $query .= ", department.name AS departmentname";
                }
                $query .= " , salarytype.id AS salarytypeid
                , education.title AS educationtitle
                ,LOWER(jobtype.title) AS jobtypetit,careerlevel.title AS careerleveltitle,salarytype.title AS srangetypetitle,jobtype.color AS jobtypecolor,company.contactemail AS companyemail ";
                if(in_array('shortlist', wpjobportal::$_active_addons)){
                    $query .= " ,jobshort.jobid AS isshort ";
                }

        $query .= " FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` AS jobapply  ON jobapply.jobid = job.id
                    ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON job.companyid = company.id
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON job.jobcategory = cat.id
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON job.jobtype = jobtype.id
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobstatus` AS jobstatus ON job.jobstatus = jobstatus.id ";
                    if(in_array('departments', wpjobportal::$_active_addons)){
                        $query .= " LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_departments` AS department ON job.departmentid = department.id";
                    }

        $query .= " LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_salaryrangetypes` AS salarytype ON salarytype.id = job.salaryduration
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_heighesteducation` AS education ON job.educationid = education.id
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_careerlevels` AS careerlevel ON careerlevel.id = job.careerlevel ";
        if(in_array('shortlist', wpjobportal::$_active_addons)){
            $query .= "LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobshortlist` AS jobshort ON jobshort.jobid = job.id
        ";
        }
        $query .= " WHERE  job.id = " . esc_sql($wpjobportal_job_id) ."";
        wpjobportal::$_data[0] = wpjobportaldb::get_row($query);
        $wpjobportal_job = wpjobportal::$_data[0];
        wpjobportal::$_data[0]->multicity = WPJOBPORTALincluder::getJSModel('wpjobportal')->getMultiCityDataForView($wpjobportal_job_id, 1);
        wpjobportal::$_data[0]->location = WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView(wpjobportal::$_data[0]->compcity);
        wpjobportal::$_data[2] = wpjobportal::$_wpjpfieldordering->getFieldsOrderingforForm(2);
        $wpjobportal_string = "'company', 'jobapply','social'";
        wpjobportal::$_data['config'] = wpjobportal::$_config->getConfigurationByConfigForMultiple($wpjobportal_string);
        $wpjobportal_theme = wp_get_theme();

        $wpjobportal_layout = WPJOBPORTALrequest::getVar('wpjobportallt');
        if($wpjobportal_layout == 'viewjob'){
            if(wpjobportal::$_data[0] != '' && wpjobportal::$_data[0]->metakeywords != '' ){
                $_SESSION['m_keywords'] = wpjobportal::$_data[0]->metakeywords;
            }
        }

        if(wpjobportal::$wpjobportal_theme_chk != 0){
            // Related Jobs data
            $wpjobportal_max = $job_portal_theme_options['maximum_relatedjobs'];
            if(!is_numeric($wpjobportal_max)){
                $wpjobportal_max = 5;
            }
            $finaljobs = array();
            $relatedjobs=array();
            //var_dump($job_portal_theme_options['relatedjob_criteria_sorter']['enabled']);
            foreach($job_portal_theme_options['relatedjob_criteria_sorter']['enabled'] AS $wpjobportal_key => $wpjobportal_value){
                $wpjobportal_inquery = '';
                switch($wpjobportal_key){
                    case 'type':
                        if(wpjobportal::$_data[0]->jobtype != '' && is_numeric(wpjobportal::$_data[0]->jobtype)){
                            $wpjobportal_inquery = ' job.jobtype = ' . esc_sql(wpjobportal::$_data[0]->jobtype);
                        }
                    break;
                    case 'category':
                        if(wpjobportal::$_data[0]->jobcategory != '' && is_numeric(wpjobportal::$_data[0]->jobcategory)){
                            $wpjobportal_inquery = ' job.jobcategory = ' . esc_sql(wpjobportal::$_data[0]->jobcategory);
                        }
                    break;
                    case 'location':
                        if(wpjobportal::$_data[0]->city != ''){
                            $wpjobportal_inquery = ' job.city IN (' . esc_sql(wpjobportal::$_data[0]->city) .')';
                        }
                    break;
                }
                if(!empty($wpjobportal_inquery)){
                    $query = "SELECT job.id,job.title,job.alias,job.created,job.city AS jobcity,company.id AS companyid,company.url AS companyurl,company.logofilename,company.city AS compcity,company.isfeaturedcompany,cat.cat_title , company.name as companyname, jobtype.title AS jobtypetitle
                            ,job.salarytype,job.salarymin,job.salarymax,salarytype.title AS salarydurationtitle,job.currency
                            , jobstatus.title AS jobstatustitle,job.created
                            ,LOWER(jobtype.title) AS jobtypetit,job.isfeaturedjob,job.startfeatureddate,job.endfeatureddate
                            ,jobtype.color AS jobtypecolor,job.params

                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                    ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON job.companyid = company.id
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON job.jobcategory = cat.id
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes`  AS jobtype ON job.jobtype = jobtype.id
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobstatus` AS jobstatus ON job.jobstatus = jobstatus.id
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_salaryrangetypes` AS salarytype ON job.salaryduration = salarytype.id

                    WHERE job.status = 1 AND DATE(job.startpublishing) <= CURDATE() AND DATE(job.stoppublishing) >= CURDATE()
                    AND ".$wpjobportal_inquery." AND job.id != ".esc_sql($wpjobportal_job_id)." LIMIT ".esc_sql($wpjobportal_max);
                    $wpjobportal_result = wpjobportaldb::get_results($query);
                    $relatedjobs = array_merge($relatedjobs, $wpjobportal_result);
                    $relatedjobs = array_map('unserialize', array_unique(array_map('serialize', $relatedjobs)));
                    if(COUNT($relatedjobs) >= $wpjobportal_max){
                        break;
                    }
                }
            }
            if(!empty($relatedjobs)){
                foreach ($relatedjobs AS $d) {
                    $d->location = WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($d->jobcity);
                    $finaljobs[] = $d;
                }
            }
            wpjobportal::$_data['relatedjobs'] = $finaljobs;
        }
        //update the job view counter
        $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_jobs` SET hits = hits + 1 WHERE id = " . esc_sql($wpjobportal_job_id);
        wpjobportal::$_db->query($query);
        wpjobportal::$_data['submission_type'] =  wpjobportal::$_config->getConfigValue('submission_type');
       return;
    }

    function getPackagePopupJobView(){
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'get-package-popup-job-view') ) {
            die( 'Security check Failed' );
        }
        $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        $wpjobportal_jobapplyid = WPJOBPORTALrequest::getVar('wpjobportalid');
        $wpjobportal_subtype = wpjobportal::$_config->getConfigValue('submission_type');
        if( $wpjobportal_subtype != 3 ){
            return false;
        }
        $wpjobportal_userpackages = array();
        $wpjobportal_userpackage = apply_filters('wpjobportal_addons_credit_get_Packages_user',false,$wpjobportal_uid,'jobapply');
        if(is_array($wpjobportal_userpackage)){
            foreach($wpjobportal_userpackage as $wpjobportal_package){
                if($wpjobportal_package->jobapply == -1 || $wpjobportal_package->remjobapply > 0){ //-1 = unlimited
                    $wpjobportal_userpackages[] = $wpjobportal_package;
                }
            }
        }
        $wpjobportal_addonclass = '';
        if(WPJOBPORTALincluder::getJSModel('common')->isElegantDesignEnabled()){
            $wpjobportal_addonclass = ' wjportal-elegant-addon-packages-popup ';
        }
        if (wpjobportal::$wpjobportal_theme_chk == 1) {
            $wpjobportal_content = '
            <div id="wpj-jp-popup-background" style="display: none;"></div>
            <div id="package-popup" class="wpj-jp-popup-wrp wpj-jp-packages-popup">
                <div class="wpj-jp-popup-cnt-wrp">
                    <i class="fas fa-times wpj-jp-popup-close-icon" data-dismiss="modal"></i>
                    <h3 class="wpj-jp-popup-heading">
                        '.esc_html(__("Select Package",'wp-job-portal')).'
                        <div class="wpj-jp-popup-desc">
                            '.esc_html(__("Please select a package first",'wp-job-portal')).'
                        </div>
                    </h3>
                    <div class="wpj-jp-popup-contentarea">
                        <div class="wpj-jp-packages-wrp">';
                            if(count($wpjobportal_userpackages) == 0  || empty($wpjobportal_userpackages)){
                                $wpjobportal_content .= WPJOBPORTALmessages::showMessage(esc_html(__("You do not have any Job Apply remaining",'wp-job-portal')),'error',1);
                            } else {
                                foreach($wpjobportal_userpackages as $wpjobportal_package){
                                    $wpjobportal_content .= '
                                        <div class="wpj-jp-pkg-item" id="package-div-'.esc_attr($wpjobportal_package->id).'" >
                                            <div class="wpj-jp-pkg-item-top">
                                                <h4 class="wpj-jp-pkg-item-title">
                                                    '.esc_html(wpjobportal::wpjobportal_getVariableValue( $wpjobportal_package->title)).'
                                                </h4>
                                            </div>
                                            <div class="wpj-jp-pkg-item-mid">
                                                <div class="wpj-jp-pkg-item-row">
                                                    <span class="wpj-jp-pkg-item-tit">
                                                        '.esc_html(__("Job Apply",'wp-job-portal')).' :
                                                    </span>
                                                    <span class="wpj-jp-pkg-item-val">
                                                        '.($wpjobportal_package->jobapply==-1 ? esc_html(__("Unlimited",'wp-job-portal')) : esc_attr($wpjobportal_package->jobapply)).'
                                                    </span>
                                                </div>
                                                <div class="wpj-jp-pkg-item-row">
                                                    <span class="wpj-jp-pkg-item-tit">
                                                        '.esc_html(__("Remaining",'wp-job-portal')).' :
                                                    </span>
                                                    <span class="wpj-jp-pkg-item-val">
                                                        '.($wpjobportal_package->jobapply==-1 ? esc_html(__("Unlimited",'wp-job-portal')) : esc_attr($wpjobportal_package->remjobapply)).'
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="wpj-jp-pkg-item-btm">
                                                <a href="#" class="wpj-jp-outline-btn wpj-jp-block-btn" onclick="selectPackage('.esc_attr($wpjobportal_package->id).');" title="'.esc_attr(__("Select package",'wp-job-portal')).'">
                                                    '.esc_html(__("Select Package",'wp-job-portal')).'
                                                </a>
                                            </div>
                                        </div>
                                    ';
                                }
                            }
                        $wpjobportal_content .= '</div>
                        <div class="wpj-jp-popup-msgs" id="wjportal-package-message"> </div>
                    </div>';
                    if(count($wpjobportal_userpackages) != 0  && !empty($wpjobportal_userpackages)){
                        $wpjobportal_content .= '
                        <div class="wpj-jp-visitor-msg-btn-wrp">
                            <input type="hidden" id="wpjobportal_packageid" name="wpjobportal_packageid">
                            <input type="submit" rel="button" id="jsre_featured_button" class="wpj-jp-visitor-msg-btn" onclick="getApplyNowByJobid('. esc_attr($wpjobportal_jobapplyid) .','.wpjobportal::wpjobportal_getPageid().','.$wpjobportal_package->id.')" value="'.esc_attr(__('Apply On This Job','wp-job-portal')).'"  data-dismiss="modal" disabled/>
                        </div>';
                    }
                    $wpjobportal_content .= '
                </div>
            </div>';
        } else {
            $wpjobportal_content = '
            <div id="wjportal-popup-background" style="display: none;"></div>
            <div id="package-popup" class="wjportal-popup-wrp wjportal-packages-popup '.$wpjobportal_addonclass.'">
                <div class="wjportal-popup-cnt">
                    <img id="wjportal-popup-close-btn" alt="popup cross" src="'.esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/popup-close.png">
                    <div class="wjportal-popup-title">
                        '.esc_html(__("Select Package",'wp-job-portal')).'
                        <div class="wjportal-popup-title3">
                            '.esc_html(__("Please select a package first",'wp-job-portal')).'
                        </div>
                    </div>
                    <div class="wjportal-popup-contentarea">
                        <div class="wjportal-packages-wrp">';
                            if(count($wpjobportal_userpackages) == 0  || empty($wpjobportal_userpackages)){
                                $wpjobportal_content .= WPJOBPORTALmessages::showMessage(esc_html(__("You do not have any Job Apply remaining",'wp-job-portal')),'error',1);
                            } else {
                                foreach($wpjobportal_userpackages as $wpjobportal_package){
                                    $wpjobportal_content .= '
                                        <div class="wjportal-pkg-item" id="package-div-'.esc_attr($wpjobportal_package->id).'" >
                                            <div class="wjportal-pkg-item-top">
                                                <div class="wjportal-pkg-item-title">
                                                    '.$wpjobportal_package->title.'
                                                </div>
                                            </div>
                                            <div class="wjportal-pkg-item-btm">
                                                <div class="wjportal-pkg-item-row">
                                                    <span class="wjportal-pkg-item-tit">
                                                        '.esc_html(__("Job Apply",'wp-job-portal')).' :
                                                    </span>
                                                    <span class="wjportal-pkg-item-val">
                                                        '.($wpjobportal_package->jobapply==-1 ? esc_html(__("Unlimited",'wp-job-portal')) : esc_attr($wpjobportal_package->jobapply)).'
                                                    </span>
                                                </div>
                                                <div class="wjportal-pkg-item-row">
                                                    <span class="wjportal-pkg-item-tit">
                                                        '.esc_html(__("Remaining",'wp-job-portal')).' :
                                                    </span>
                                                    <span class="wjportal-pkg-item-val">
                                                        '.($wpjobportal_package->jobapply==-1 ? esc_html(__("Unlimited",'wp-job-portal')) : esc_attr($wpjobportal_package->remjobapply)).'
                                                    </span>
                                                </div>
                                                <div class="wjportal-pkg-item-btn-row">
                                                    <a href="#" class="wjportal-pkg-item-btn" onclick="selectPackage('.esc_attr($wpjobportal_package->id).');">
                                                        '.esc_html(__("Select Package",'wp-job-portal')).'
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    ';
                                }
                                /*$wpjobportal_content .= '<div class="wjportal-pkg-help-txt">
                                        '.esc_html(__("Click on package to select one",'wp-job-portal')).'
                                        </div>';*/
                            }
                        $wpjobportal_content .= '</div>
                        <div class="wjportal-popup-msgs" id="wjportal-package-message"> </div>
                    </div>
                    <div class="wjportal-visitor-msg-btn-wrp">
                        <input type="hidden" id="wpjobportal_packageid" name="wpjobportal_packageid">
                        <input type="submit" selected_pack="0" rel="button" id="jsre_featured_button" class="wjportal-visitor-msg-btn disabled" onclick="hidePackagePopupForJobApply()" value="'.esc_html(__('Apply On This Job','wp-job-portal')).'"  data-dismiss="modal" disabled/>
                    </div>
                </div>
            </div>';
        }
        echo wp_kses($wpjobportal_content, WPJOBPORTAL_ALLOWED_TAGS);
        exit();
    }

    function checkAlreadyAppliedJob($wpjobportal_jobid, $wpjobportal_uid) {
        if (!is_numeric($wpjobportal_jobid))
            return false;
        if (!is_numeric($wpjobportal_uid))
            return false;
        unset($wpjobportal_result);
        $query = "SELECT COUNT(id) as no,status FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` WHERE jobid = " . esc_sql($wpjobportal_jobid) . " AND uid = " . esc_sql($wpjobportal_uid);
        $wpjobportal_result = wpjobportal::$_db->get_row($query);
        return $wpjobportal_result;
    }

    function getJobTitleById($wpjobportal_id) {
        if (!is_numeric($wpjobportal_id))
            return false;
        $query = "SELECT job.title FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job WHERE job.id = " . esc_sql($wpjobportal_id);
        $wpjobportal_jobname = wpjobportal::$_db->get_var($query);
        return $wpjobportal_jobname;
    }

    function getJobsExpiryStatus($wpjobportal_jobid) {
        if (!is_numeric($wpjobportal_jobid))
            return false;
        $wpjobportal_curdate = date_i18n('Y-m-d');
        $query = "SELECT job.id
        FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
        WHERE job.status = 1 AND DATE(job.stoppublishing) >= DATE('" . esc_sql($wpjobportal_curdate) . "')
        AND job.id =" . esc_sql($wpjobportal_jobid);
        $wpjobportal_result = wpjobportal::$_db->get_var($query);
        if ($wpjobportal_result == null) {
            return false;
        } else {
            return true;
        }
    }


    function getJobPay($wpjobportal_jobid){
        if (!is_numeric($wpjobportal_jobid))
            return false;
        $wpjobportal_curdate = date_i18n('Y-m-d');
        $query = "SELECT job.id
        FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
        WHERE job.status = 3
        AND job.id =" . esc_sql($wpjobportal_jobid);
        $wpjobportal_result = wpjobportal::$_db->get_var($query);
        if ($wpjobportal_result == null) {
            return false;
        } else {
            return true;
        }

    }

    function getJobbyId($wpjobportal_jobid) {
        if ($wpjobportal_jobid) {
            if (!is_numeric($wpjobportal_jobid))
                return false;
            $query = "SELECT job.* ,cat.cat_title
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON job.jobcategory = cat.id
                WHERE job.id = " . esc_sql($wpjobportal_jobid);
            wpjobportal::$_data[0] = wpjobportaldb::get_row($query);
        }
        if (isset(wpjobportal::$_data[0])) {
            wpjobportal::$_data[0]->multicity = wpjobportal::$_common->getMultiSelectEdit($wpjobportal_jobid, 1);
            wpjobportal::$_data[0]->jobtags = wpjobportal::$_common->makeFilterdOrEditedTagsToReturn( wpjobportal::$_data[0]->tags );
        }
       wpjobportal::$_data[2] = wpjobportal::$_wpjpfieldordering->getFieldsOrderingforForm(2); // job fields
    }

    function getJobForApply($wpjobportal_jobid) {
        $wpjobportal_data = array();
        if ($wpjobportal_jobid) {
            if (!is_numeric($wpjobportal_jobid))
                return false;
            $query = "SELECT job.* ,cat.cat_title, jobtype.title AS jobtypetitle, company.name AS companyname,company.logofilename ,company.id AS companyid,salaryrangetype.title AS salaryrangetype,jobtype.color AS jobtypecolor,salaryrangetype.title AS srangetypetitle
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON job.companyid = company.id
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON job.jobcategory = cat.id
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON job.jobtype = jobtype.id
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_salaryrangetypes` AS salaryrangetype ON salaryrangetype.id = job.salaryduration
                WHERE job.id = " . esc_sql($wpjobportal_jobid);
            $wpjobportal_data = wpjobportaldb::get_row($query);
        }
        return $wpjobportal_data;
        if (isset(wpjobportal::$_data[0])) {
            wpjobportal::$_data[0]->multicity = wpjobportal::$_common->getMultiSelectEdit($wpjobportal_jobid, 1);
            wpjobportal::$_data[0]->jobtags = wpjobportal::$_common->makeFilterdOrEditedTagsToReturn( wpjobportal::$_data[0]->tags );
        }
       wpjobportal::$_data[2] = wpjobportal::$_wpjpfieldordering->getFieldsOrderingforForm(2); // job fields
    }

    function sorting() {
        $wpjobportal_pagenum = WPJOBPORTALrequest::getVar('pagenum');
        wpjobportal::$_data['sorton'] = isset(wpjobportal::$_search['jobs']['sorton']) ? wpjobportal::$_search['jobs']['sorton'] : 6;
        wpjobportal::$_data['sortby'] = isset(wpjobportal::$_search['jobs']['sortby']) ? wpjobportal::$_search['jobs']['sortby'] : 2;
        switch (wpjobportal::$_data['sorton']) {
            case 6: // created
                wpjobportal::$_data['sorting'] = ' job.created ';
                break;
            case 2: // company name
                wpjobportal::$_data['sorting'] = ' company.name ';
                break;
            case 3: // category
                wpjobportal::$_data['sorting'] = ' cat.cat_title ';
                break;
            case 5: // location
                wpjobportal::$_data['sorting'] = ' city.name ';
                break;
            case 7: // status
                wpjobportal::$_data['sorting'] = ' job.jobstatus ';
                break;
            case 1: // job title
                wpjobportal::$_data['sorting'] = ' job.title ';
                break;
            case 4: // job type
                wpjobportal::$_data['sorting'] = ' jobtype.title ';
                break;
            case 8:
                wpjobportal::$_data['sorting'] = ' job.salarymax ';
                break;
        }
        if (wpjobportal::$_data['sortby'] == 1) {
            wpjobportal::$_data['sorting'] .= ' ASC ';
        } else {
            wpjobportal::$_data['sorting'] .= ' DESC ';
        }
        wpjobportal::$_data['combosort'] = wpjobportal::$_data['sorton'];
        //die(wpjobportal::$_data['combosort']);
    }

    function checkLinks($wpjobportal_name) {
        if(isset(wpjobportal::$wpjobportal_data['fields'])){
            foreach (wpjobportal::$wpjobportal_data['fields'] as $wpjobportal_field) {
                $wpjobportal_array =  array();
                $wpjobportal_array[0] = 0;
                switch ($wpjobportal_field->field) {
                    case $wpjobportal_name:
                    if($wpjobportal_field->showonlisting == 1){
                        $wpjobportal_array[0] = 1;
                        $wpjobportal_array[1] =  $wpjobportal_field->fieldtitle;
                    }
                    return $wpjobportal_array;
                    break;
                }
            }
            return $wpjobportal_array;
        }else{
            return '';
        }
    }

    function getAllJobs() {
        //die('abc');
        $this->sorting();
        //filters

        $jobs_search = isset(wpjobportal::$_search['jobs']) ? wpjobportal::$_search['jobs'] : [];

        $wpjobportal_searchtitle = isset($jobs_search['searchtitle']) ? $jobs_search['searchtitle'] : null;
        $wpjobportal_searchcompany = isset($jobs_search['searchcompany']) ? $jobs_search['searchcompany'] : null;
        $wpjobportal_searchjobcategory = isset($jobs_search['searchjobcategory']) ? $jobs_search['searchjobcategory'] : null;
        $wpjobportal_searchjobtype = isset($jobs_search['searchjobtype']) ? $jobs_search['searchjobtype'] : null;
        $wpjobportal_status = isset($jobs_search['status']) ? $jobs_search['status'] : null;
        $featured = isset($jobs_search['featured']) ? $jobs_search['featured'] : null;
        $wpjobportal_datestart = isset($jobs_search['datestart'])  ? $jobs_search['datestart'] : null;
        $wpjobportal_dateend = isset($jobs_search['dateend']) ? $jobs_search['dateend'] : null;
        $location = isset($jobs_search['location']) ? $jobs_search['location'] : null;

        wpjobportal::$_data['filter']['searchtitle'] = $wpjobportal_searchtitle;
        wpjobportal::$_data['filter']['searchcompany'] = $wpjobportal_searchcompany;
        wpjobportal::$_data['filter']['searchjobcategory'] = $wpjobportal_searchjobcategory;
        wpjobportal::$_data['filter']['searchjobtype'] = $wpjobportal_searchjobtype;
        wpjobportal::$_data['filter']['status'] = $wpjobportal_status;
        wpjobportal::$_data['filter']['featured'] = $featured;
        wpjobportal::$_data['filter']['datestart'] = $wpjobportal_datestart;
        wpjobportal::$_data['filter']['dateend'] = $wpjobportal_dateend;
        wpjobportal::$_data['filter']['location'] = $location;

        if ($wpjobportal_searchjobcategory)
            if (is_numeric($wpjobportal_searchjobcategory) == false)
                return false;
        if ($wpjobportal_searchjobtype)
            if (is_numeric($wpjobportal_searchjobtype) == false)
                return false;
        if ($wpjobportal_status)
            if (is_numeric($wpjobportal_status) == false)
                return false;

        $this->checkCall();
        $wpjobportal_curdate = gmdate('Y-m-d');
        $wpjobportal_inquery = "";
        if ($wpjobportal_searchtitle)
            $wpjobportal_inquery .= " AND LOWER(job.title) LIKE '%" . esc_sql($wpjobportal_searchtitle) . "%'";
        if ($wpjobportal_searchcompany)
            $wpjobportal_inquery .= " AND LOWER(company.name) LIKE '%" . esc_sql($wpjobportal_searchcompany) . "%'";
        if ($wpjobportal_searchjobcategory && is_numeric($wpjobportal_searchjobcategory))
            $wpjobportal_inquery .= " AND job.jobcategory = " . esc_sql($wpjobportal_searchjobcategory);
        if ($wpjobportal_searchjobtype && is_numeric($wpjobportal_searchjobtype))
            $wpjobportal_inquery .= " AND job.jobtype = " . esc_sql($wpjobportal_searchjobtype);
        if ($wpjobportal_dateend != null){
            $wpjobportal_dateend = gmdate('Y-m-d',strtotime($wpjobportal_dateend));
            $wpjobportal_inquery .= " AND DATE(job.created) <= '" . esc_sql($wpjobportal_dateend) . "'";
        }
        if ($wpjobportal_datestart != null){
            $wpjobportal_datestart = gmdate('Y-m-d',strtotime($wpjobportal_datestart));
            $wpjobportal_inquery .= " AND DATE(job.created) >= '" . esc_sql($wpjobportal_datestart) . "'";
        }
        if ($wpjobportal_status != null && is_numeric($wpjobportal_status))
            $wpjobportal_inquery .= " AND job.status = " . esc_sql($wpjobportal_status);
        if ($featured != null)
            $wpjobportal_inquery .= " AND job.isfeaturedjob = 1 AND DATE(job.startfeatureddate) <= '".esc_sql($wpjobportal_curdate)."' AND DATE(job.endfeatureddate) >= '".esc_sql($wpjobportal_curdate)."'";
        if ($location != null)
            $wpjobportal_inquery .= " AND city.name LIKE '%" . esc_sql($location) . "%'";

        $query = "SELECT COUNT(job.id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON job.companyid = company.id
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON city.id = (SELECT cityid FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobcities` WHERE jobid = job.id ORDER BY id DESC LIMIT 1)
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_salaryrangetypes` AS salaryrangetype ON salaryrangetype.id = job.salaryduration
                WHERE job.status != 0 ";
        $query.=$wpjobportal_inquery;

        $wpjobportal_total = wpjobportaldb::get_var($query);
        wpjobportal::$_data['total'] = $wpjobportal_total;
        wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($wpjobportal_total);

        //Data
        $query = "SELECT job.*, cat.cat_title, jobtype.title AS jobtypetitle, company.name AS companyname ,company.logofilename AS logo ,company.id AS companyid,salaryrangetype.title AS salaryrangetype,jobtype.color AS jobtypecolor,
                ( SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` WHERE jobid = job.id AND status = 1) AS totalresume
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON job.companyid = company.id
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON job.jobcategory = cat.id
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON job.jobtype = jobtype.id
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_salaryrangetypes` AS salaryrangetype ON salaryrangetype.id = job.salaryduration
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON city.id = (SELECT cityid FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobcities` WHERE jobid = job.id ORDER BY id DESC LIMIT 1)
                WHERE job.status != 0";
        $query.=$wpjobportal_inquery;
        $query.= " ORDER BY" . wpjobportal::$_data['sorting'];
        $query.=" LIMIT " . WPJOBPORTALpagination::$_offset . "," . WPJOBPORTALpagination::$_limit;

        wpjobportal::$_data[0] = wpjobportaldb::get_results($query);
        wpjobportal::$wpjobportal_data['fields'] = wpjobportal::$_wpjpfieldordering->getFieldsOrderingforView(2);
        wpjobportal::$_data['config'] = wpjobportal::$_config->getConfigByFor('job');

        return;
    }

    function getAllUnapprovedJobs() {
        $this->sorting();
        //filters
        $wpjobportal_searchtitle = wpjobportal::$_search['jobs']['searchtitle'];
        $wpjobportal_searchcompany = wpjobportal::$_search['jobs']['searchcompany'];
        $wpjobportal_searchjobcategory = wpjobportal::$_search['jobs']['searchjobcategory'];
        $wpjobportal_searchjobtype = wpjobportal::$_search['jobs']['searchjobtype'];
        $wpjobportal_status = wpjobportal::$_search['jobs']['status'];
        $featured = wpjobportal::$_search['jobs']['featured'];
        $wpjobportal_datestart = wpjobportal::$_search['jobs']['datestart'];
        $wpjobportal_dateend = wpjobportal::$_search['jobs']['dateend'];
        $location = wpjobportal::$_search['jobs']['location'];

        wpjobportal::$_data['filter']['searchtitle'] = $wpjobportal_searchtitle;
        wpjobportal::$_data['filter']['searchcompany'] = $wpjobportal_searchcompany;
        wpjobportal::$_data['filter']['searchjobcategory'] = $wpjobportal_searchjobcategory;
        wpjobportal::$_data['filter']['searchjobtype'] = $wpjobportal_searchjobtype;
        wpjobportal::$_data['filter']['status'] = $wpjobportal_status;
        wpjobportal::$_data['filter']['featured'] = $featured;
        wpjobportal::$_data['filter']['datestart'] = $wpjobportal_datestart;
        wpjobportal::$_data['filter']['dateend'] = $wpjobportal_dateend;
        wpjobportal::$_data['filter']['location'] = $location;

        if ($wpjobportal_searchjobcategory)
            if (is_numeric($wpjobportal_searchjobcategory) == false)
                return false;
        if ($wpjobportal_searchjobtype)
            if (is_numeric($wpjobportal_searchjobtype) == false)
                return false;
        if ($wpjobportal_status)
            if (is_numeric($wpjobportal_status) == false)
                return false;

        $this->checkCall();

        $wpjobportal_inquery = "";
        if ($wpjobportal_searchtitle)
            $wpjobportal_inquery .= " AND LOWER(job.title) LIKE '%" . esc_sql($wpjobportal_searchtitle) . "%'";
        if ($wpjobportal_searchcompany)
            $wpjobportal_inquery .= " AND LOWER(company.name) LIKE '%" . esc_sql($wpjobportal_searchcompany) . "%'";
        if ($wpjobportal_searchjobcategory && is_numeric($wpjobportal_searchjobcategory))
            $wpjobportal_inquery .= " AND job.jobcategory = " . esc_sql($wpjobportal_searchjobcategory);
        if ($wpjobportal_searchjobtype && is_numeric($wpjobportal_searchjobtype))
            $wpjobportal_inquery .= " AND job.jobtype = " . esc_sql($wpjobportal_searchjobtype);
        if ($wpjobportal_dateend != null){
            $wpjobportal_dateend = gmdate('Y-m-d',strtotime($wpjobportal_dateend));
            $wpjobportal_inquery .= " AND DATE(job.created) <= '" . esc_sql($wpjobportal_dateend) . "'";
        }
        if ($wpjobportal_datestart != null){
            $wpjobportal_datestart = gmdate('Y-m-d',strtotime($wpjobportal_datestart));
            $wpjobportal_inquery .= " AND DATE(job.created) >= '" . esc_sql($wpjobportal_datestart) . "'";
        }
        if ($wpjobportal_status != null && is_numeric($wpjobportal_status))
            $wpjobportal_inquery .= " AND job.status = " . esc_sql($wpjobportal_status);
        if ($featured != null)
            $wpjobportal_inquery .= " AND job.isfeaturedjob = 1";
        if ($location != null)
            $wpjobportal_inquery .= " AND city.name LIKE '%" . esc_sql($location) . "%'";

        $query = "SELECT COUNT(job.id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON job.companyid = company.id
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON city.id = (SELECT cityid FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobcities` WHERE jobid = job.id ORDER BY id DESC LIMIT 1)
                 WHERE (job.status = 0";
        if(in_array('featuredjob', wpjobportal::$_active_addons)){
            $query .= " OR isfeaturedjob = 0)";
        }else{
            $query .= ")";
        }
        $query.=$wpjobportal_inquery;

        $wpjobportal_total = wpjobportaldb::get_var($query);
        wpjobportal::$_data['total'] = $wpjobportal_total;
        wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($wpjobportal_total);

        //Data
        // company is must for job so changed it join from left join
        $query = "SELECT job.*, cat.cat_title, jobtype.title AS jobtypetitle,company.logofilename AS logofilename, company.name AS companyname ,salaryrangetype.title AS salaryrangetype,jobtype.color AS jobtypecolor,job.currency,
                ( SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` WHERE jobid = job.id) AS totalresume
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON job.companyid = company.id
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON job.jobcategory = cat.id
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON job.jobtype = jobtype.id
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON city.id = (SELECT cityid FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobcities` WHERE jobid = job.id ORDER BY id DESC LIMIT 1)
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_salaryrangetypes` AS salaryrangetype ON salaryrangetype.id = job.salaryduration
                WHERE (job.status = 0";
        if(in_array('featuredjob', wpjobportal::$_active_addons)){
            $query .= " OR isfeaturedjob = 0)";
        }else{
            $query .= ")";
        }
        $query.= $wpjobportal_inquery;
        $query.= " ORDER BY" . wpjobportal::$_data['sorting'];
        $query.=" LIMIT " . WPJOBPORTALpagination::$_offset . "," . WPJOBPORTALpagination::$_limit;
        wpjobportal::$_data[0] = wpjobportaldb::get_results($query);
        wpjobportal::$wpjobportal_data['fields'] = wpjobportal::$_wpjpfieldordering->getFieldsOrderingforView(2);

        return;
    }

    function storeJob($wpjobportal_data) {
       if (empty($wpjobportal_data))
            return false;
        $wpjobportal_isnew  = isset($wpjobportal_data['id']) && ((int)$wpjobportal_data['id']) ? 0 : 1;
        $wpjobportal_user =WPJOBPORTALincluder::getObjectClass('user');
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('job');
        if(isset($wpjobportal_data['companyid'])){
            $wpjobportal_data['uid'] = WPJOBPORTALincluder::getJSModel('company')->getUidByCompanyId($wpjobportal_data['companyid']);
        }else{
            $wpjobportal_data['uid'] = false;
        }

        // need to reheck this case
        if( isset($wpjobportal_data['companyid']) && $wpjobportal_data['companyid'] !=''){ // handling log error for unpublished company
            if(!wpjobportal::$_common->wpjp_isadmin()){ // maiking sure crrent record is not added/updated by admin
                if(!WPJOBPORTALincluder::getJSModel('company')->getIfCompanyOwner($wpjobportal_data['companyid'])){ // check if current user owns the company for which the job is being created
                    return WPJOBPORTAL_SAVE_ERROR; // if current user does not own the company he is posting job for return false.
                }
            }
        }

        if($wpjobportal_data['uid'] == false){
            $wpjobportal_data['uid'] = '';
        }
        $wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
        $wpjobportal_ignore_stop_pulbishing_verification = 0;
        $wpjobportal_no_package_needed = 0;
        if ($wpjobportal_data['id'] == '') {
            if(in_array('credits', wpjobportal::$_active_addons)){
                $wpjobportal_submission_type   = wpjobportal::$_config->getConfigValue('submission_type');
                if($wpjobportal_submission_type == 1){
                    #Per listing --Free job Expiry date
                    $wpjobportal_expiry = wpjobportal::$_config->getConfigValue('jobexpiry_days_free');
                    if(isset($wpjobportal_data['stoppublishing']) && empty($wpjobportal_data['stoppublishing'])){
                        $wpjobportal_data['stoppublishing'] = gmdate($wpjobportal_dateformat,strtotime($wpjobportal_data['stoppublishing'].'+'.$wpjobportal_expiry.' days') );
                    }
                    if (!wpjobportal::$_common->wpjp_isadmin()) {
                        $wpjobportal_data['status'] = wpjobportal::$_config->getConfigurationByConfigName('jobautoapprove');
                    }
                }elseif ($wpjobportal_submission_type == 2) {
                    #Per listing --Free job Expiry date
                    $wpjobportal_expiry = wpjobportal::$_config->getConfigValue('jobexpiry_days_perlisting');
                    if(isset($wpjobportal_data['stoppublishing']) && empty($wpjobportal_data['stoppublishing'])){
                        $wpjobportal_data['stoppublishing'] = gmdate($wpjobportal_dateformat,strtotime($wpjobportal_data['stoppublishing'].'+'.$wpjobportal_expiry.' days') );
                    }
                    if (!wpjobportal::$_common->wpjp_isadmin()) {
                        // in case of per listing submission mode
                        $wpjobportal_price_check = WPJOBPORTALincluder::getJSModel('credits')->checkIfPriceDefinedForAction('add_job');
                        if($wpjobportal_price_check == 1){ // if price is defined then status 3
                            $wpjobportal_data['status'] = 3;
                        }else{ // if price not defined then status set to auto approve configuration
                            $wpjobportal_data['status'] = wpjobportal::$_config->getConfigValue('jobautoapprove');
                        }
                    }else{
                        $wpjobportal_data['status'] = 1;
                    }
                }elseif ($wpjobportal_submission_type == 3) {
                    if(!wpjobportal::$_common->wpjp_isadmin()){ // to handle different possible cases
                        $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
                    }else{
                        $wpjobportal_uid = $wpjobportal_data['uid'];
                    }
                    // check if there is package defined for current user

                    $wpjobportal_result = WPJOBPORTALincluder::getJSModel('credits')->checkIfPackageDefinedForRole(1); //1 for employer
                    if($wpjobportal_result == 0){ // 0 means no package found. so allow the action.
                        $wpjobportal_no_package_needed = 1;
                        $wpjobportal_expiry = "90 days"; // in case of undefined add job for 90 days
                    }

                    if($wpjobportal_no_package_needed == 0){
                        $wpjobportal_upakid = WPJOBPORTALrequest::getVar('upakid',null,0);
                        if ($wpjobportal_data['payment'] == 0 && wpjobportal::$_common->wpjp_isadmin()) {
                            $wpjobportal_upakid = WPJOBPORTALrequest::getVar('upakid',null,0);
                            $wpjobportal_data['userpackageid'] = $wpjobportal_upakid;
                        } else {
                            if(!wpjobportal::$_common->wpjp_isadmin()){
                                $wpjobportal_package = WPJOBPORTALincluder::getJSModel('purchasehistory')->getUserPackageById($wpjobportal_upakid,$wpjobportal_user->uid(),'remjob');
                            }elseif (wpjobportal::$_common->wpjp_isadmin()) {
                                $wpjobportal_package = WPJOBPORTALincluder::getJSModel('purchasehistory')->getUserPackageById($wpjobportal_upakid,$wpjobportal_data['uid'],'remjob');
                            }
                            if( !$wpjobportal_package ){
                                return WPJOBPORTAL_SAVE_ERROR;
                            }
                            if( $wpjobportal_package->expired ){
                                return WPJOBPORTAL_SAVE_ERROR;
                            }
                            //if Department are not unlimited & there is no remaining left
                            if( $wpjobportal_package->job!=-1 && !$wpjobportal_package->remjob ){ //-1 = unlimited
                                return WPJOBPORTAL_SAVE_ERROR;
                            }
                        }
                        #user packae id--
                        $wpjobportal_data['userpackageid'] = $wpjobportal_upakid;
                        if(isset($wpjobportal_package) && !empty($wpjobportal_package)){
                            $wpjobportal_expiry = $wpjobportal_package->jobtime.''.$wpjobportal_package->jobtimeunit;
                        }else{
                            $wpjobportal_expiry = "30 days"; // in case of undefined add job for 30 days
                        }
                    }
                    if (!wpjobportal::$_common->wpjp_isadmin()) {
                        $wpjobportal_data['status'] = wpjobportal::$_config->getConfigValue('jobautoapprove');
                    }
                    $wpjobportal_curdate = date_i18n('Y-m-d');
                    $wpjobportal_data['stoppublishing'] = gmdate($wpjobportal_dateformat,strtotime($wpjobportal_curdate.'+'.$wpjobportal_expiry));
                }
            }else{
                if(isset($wpjobportal_data['draft']) && $wpjobportal_data['draft'] == 1 ){
                    $wpjobportal_data['status'] = 4;
                }else{
                    if(!wpjobportal::$_common->wpjp_isadmin()){
                        $wpjobportal_data['status'] = wpjobportal::$_config->getConfigurationByConfigName('jobautoapprove');
                    }
                }
            }
        }else{ // edit
            if(!wpjobportal::$_common->wpjp_isadmin()){ // checking if is admin
                // verify that can current user is editing his owned entity
                $wpjobportal_id = $wpjobportal_data['id'];
                if(! $this->getIfJobOwner($wpjobportal_id)){
                    // if current entity being edited is not owned by current user dont allow to procced further
                    return false;
                }
                //handle stop publishing date in edit case
                if(in_array('credits', wpjobportal::$_active_addons)){ // if package system
                    $wpjobportal_submission_type   = wpjobportal::$_config->getConfigValue('submission_type');
                    if($wpjobportal_submission_type == 3){// if membership mode is selected
                        if(!wpjobportal::$_common->wpjp_isadmin()){ // to handle different possible cases
                            $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
                        }else{
                            $wpjobportal_uid = $wpjobportal_data['uid'];
                        }

                        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('credits')->checkIfPackageDefinedForRole(1); //1 for employer
                        if($wpjobportal_result == 0){ // 0 means no package found. so allow the action.
                            $wpjobportal_no_package_needed = 1;
                        }

                        if($wpjobportal_no_package_needed == 0){ // dont allow update for date if package is defined
                            unset($wpjobportal_data['stoppublishing']); // making sure the value does not update
                        	$wpjobportal_ignore_stop_pulbishing_verification = 1;
                        }
                    }
                }
            }
       }
        #Free,per-listing,Package --Adjust
        if($wpjobportal_ignore_stop_pulbishing_verification == 0){ // to handle edit in case in membership mode
            if(isset($wpjobportal_data['stoppublishing'])){
                $wpjobportal_data['stoppublishing'] = gmdate('Y-m-d H:i:s', strtotime($wpjobportal_data['stoppublishing']));
            }else{ // if stop publishing date
                $wpjobportal_expiry = "2 years";
                $wpjobportal_curdate = date_i18n('Y-m-d');
                $wpjobportal_data['stoppublishing'] = gmdate('Y-m-d H:i:s',strtotime($wpjobportal_curdate.'+'.$wpjobportal_expiry));
            }
        }

        $wpjobportal_data['jobapplylink'] = isset($wpjobportal_data['jobapplylink']) ? 1 : 0;
        if (!empty($wpjobportal_data['alias']))
            $wpjobportal_jobalias = wpjobportal::$_common->removeSpecialCharacter($wpjobportal_data['alias']);
        else
            $wpjobportal_jobalias = wpjobportal::$_common->removeSpecialCharacter($wpjobportal_data['title']);

        $wpjobportal_jobalias = wpjobportalphplib::wpJP_strtolower(wpjobportalphplib::wpJP_str_replace(' ', '-', $wpjobportal_jobalias));
        $wpjobportal_jobalias = wpjobportalphplib::wpJP_strtolower(wpjobportalphplib::wpJP_str_replace('_', '-', $wpjobportal_jobalias));
        $wpjobportal_data['alias'] = $wpjobportal_jobalias;
        if( isset($wpjobportal_data['salarytype']) && $wpjobportal_data['salarytype'] == WPJOBPORTAL_SALARY_FIXED){ // min field issue
            $wpjobportal_data['salarymin'] = $wpjobportal_data['salaryfixed'];
            $wpjobportal_data['salarymax'] = $wpjobportal_data['salaryfixed'];
        }
        // Uid must be the same as the company owner id

        if ($wpjobportal_data['id'] == '') {
            $wpjobportal_data['jobid'] = $this->getJobId();
            $wpjobportal_data['created'] = date_i18n("Y-m-d H:i:s");
            $wpjobportal_data['startpublishing'] = date_i18n("Y-m-d H:i:s");
        } else {
            $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        }

        #Currency For Job
        $wpjobportal_data['currency'] = wpjobportal::$_config->getConfigValue('job_currency');

        $wpjobportal_data = wpjobportal::wpjobportal_sanitizeData($wpjobportal_data);
        $wpjobportal_data['description'] = wpautop(wptexturize(wp_kses_post(wpjobportalphplib::wpJP_stripslashes(WPJOBPORTALrequest::getVar('description','post','','',1)))));
        if(isset($wpjobportal_data['qualifications'])){
            $wpjobportal_data['qualifications'] = wpautop(wptexturize(wp_kses_post(wpjobportalphplib::wpJP_stripslashes(sanitize_textarea_field(WPJOBPORTALrequest::getVar('qualifications','post','','',1))))));
        }
        if(isset($wpjobportal_data['qualifications'])){
            $wpjobportal_data['prefferdskills'] = wpautop(wptexturize(wp_kses_post(wpjobportalphplib::wpJP_stripslashes(sanitize_textarea_field(WPJOBPORTALrequest::getVar('prefferdskills','post','','',1))))));
        }
        if(isset($wpjobportal_data['qualifications'])){
            $wpjobportal_data['agreement'] = wpautop(wptexturize(wp_kses_post(wpjobportalphplib::wpJP_stripslashes(sanitize_textarea_field(WPJOBPORTALrequest::getVar('agreement','post','','',1))))));
        }
          // commented this code to make storing customfields same for all entites.
        // //custom field code start
        // $wpjobportal_userfieldforjob = wpjobportal::$_wpjpfieldordering->getUserfieldsfor(2);
        // $params = array();
        // foreach ($wpjobportal_userfieldforjob AS $ufobj) {
        //     $wpjobportal_vardata = isset($wpjobportal_data[$ufobj->field]) ? $wpjobportal_data[$ufobj->field] : '';
        //     if($wpjobportal_vardata != ''){
        //         /*if($ufobj->userfieldtype == 'multiple'){ // multiple field change its behave
        //             $wpjobportal_vardata = wpjobportalphplib::wpJP_explode(',', $wpjobportal_vardata[0]); // fixed index
        //         }*/
        //         if(is_array($wpjobportal_vardata)){
        //             $wpjobportal_vardata = implode(', ', $wpjobportal_vardata);
        //         }
        //         $params[$ufobj->field] = wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_vardata);
        //     }
        // }
        // $params = wp_json_encode($params);
        // $wpjobportal_data['params'] = $params;
        //custom field code end
        if(!isset($wpjobportal_data['jobapplylink'])){
            $wpjobportal_data['jobapplylink'] = 0;
        }
        if(empty($wpjobportal_data['uid'])){
            $wpjobportal_data['uid'] = $wpjobportal_user->uid();
        }

        // handle email alert for base version job apply
        if(!in_array('jobalert', wpjobportal::$_active_addons)){
            $wpjobportal_data['sendemail'] = 1; // setting this one to make sure for base plugin the email alert is based on admin email settings
        }

// already satinzed above
        if(WPJOBPORTALincluder::getJSModel('common')->checkLanguageSpecialCase()){
           $wpjobportal_data = wpjobportal::$_common->stripslashesFull($wpjobportal_data);
        }

    	$wpjobportal_description = WPJOBPORTALrequest::getVar('description','post','','',1);// sanitizing description data
        $wpjobportal_data['description'] = wpautop(wptexturize(wp_kses_post(wpjobportalphplib::wpJP_stripslashes($wpjobportal_description))));
        // remove slashes with quotes.
        if (!$wpjobportal_row->bind($wpjobportal_data)) {
           return WPJOBPORTAL_SAVE_ERROR;
        }

        if (!$wpjobportal_row->check()) {
            return WPJOBPORTAL_SAVE_ERROR;
        }
        if (!$wpjobportal_row->store()) {
            return WPJOBPORTAL_SAVE_ERROR;
        }
        $wpjobportal_jobid = $wpjobportal_row->id;
        wpjobportal::$_data['id'] = $wpjobportal_row->id;

        //store custom fields (same as company module)
        wpjobportal::$_wpjpcustomfield->storeCustomFields(2,$wpjobportal_jobid,$wpjobportal_data);
        if(in_array('credits', wpjobportal::$_active_addons)){
            if($wpjobportal_isnew && $wpjobportal_submission_type == 3 && $wpjobportal_no_package_needed == 0){
                $wpjobportal_trans = WPJOBPORTALincluder::getJSTable('transactionlog');
                $wpjobportal_arr = array();
                if(!wpjobportal::$_common->wpjp_isadmin()){
                    $wpjobportal_arr['uid'] = $wpjobportal_user->uid();
                }elseif (wpjobportal::$_common->wpjp_isadmin()) {
                    $wpjobportal_arr['uid'] = $wpjobportal_data['uid'];
                }
                $wpjobportal_arr['userpackageid'] = $wpjobportal_upakid;
                $wpjobportal_arr['recordid'] = $wpjobportal_row->id;
                $wpjobportal_arr['type'] = 'job';
                $wpjobportal_arr['created'] = current_time('mysql');
                $wpjobportal_arr['status'] = 1;
                $wpjobportal_trans->bind($wpjobportal_arr);
                $wpjobportal_trans->store();
            }
        }

        if ($wpjobportal_data['id'] == '') {
            WPJOBPORTALincluder::getJSModel('emailtemplate')->sendMail(2, 1, $wpjobportal_row->id); // 2 for Job,1 for add new Job
        } else {
            $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        }
        if (isset($wpjobportal_data['city']))
            $wpjobportal_storemulticity = $this->storeMultiCitiesJob($wpjobportal_data['city'], $wpjobportal_row->id);
        if (isset($wpjobportal_storemulticity) && $wpjobportal_storemulticity == false)
            return false;

        // action hook for add job
        if(empty($wpjobportal_data['id'])){ // changed the if to handle problem case (it will handle unset and null set value case)
            $wpjobportal_data['id'] = $wpjobportal_row->id;
        }
        do_action('wpjobportal_after_store_job_hook',$wpjobportal_data);

        return WPJOBPORTAL_SAVED;
    }

    function captchaValidate($tellafriend = 0,$wpjobportal_token_v3 = '') {
        if (!is_user_logged_in()) {
            $wpjobportal_config_array = wpjobportal::$_config->getConfigByFor('captcha');
            if($tellafriend == 0){
                $wpjobportal_captcha_check = $wpjobportal_config_array['job_captcha'];
            }else{
                $wpjobportal_captcha_check = $wpjobportal_config_array['tell_a_friend_captcha'];
            }

            if ($wpjobportal_captcha_check == 1) {
                if ($wpjobportal_config_array['captcha_selection'] == 1) { // Google recaptcha
                    if($wpjobportal_token_v3 == ''){
                        $wpjobportal_google_recaptcha = WPJOBPORTALrequest::getVar('g-recaptcha-response','post','');
                        if($wpjobportal_google_recaptcha != ''){
                            $gresponse = wpjobportal::wpjobportal_sanitizeData($wpjobportal_google_recaptcha);
                        }
                    }else{
                        $gresponse = $wpjobportal_token_v3;
                    }
                    $resp = wpjobportal_googleRecaptchaHTTPPost($wpjobportal_config_array['recaptcha_privatekey'] , $gresponse);

                    if ($resp) {
                        return true;
                    } else {
                        wpjobportal::$_data['google_captchaerror'] = esc_html(__("Invalid captcha",'wp-job-portal'));
                        return false;
                    }
                } else { // own captcha
                    $wpjobportal_captcha = new WPJOBPORTALcaptcha;
                    $wpjobportal_result = $wpjobportal_captcha->checkCaptchaUserForm();
                    if ($wpjobportal_result == 1) {
                        return true;
                    } else {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    function storeMultiCitiesJob($city_id, $wpjobportal_jobid) { // city id comma seprated
        if (!is_numeric($wpjobportal_jobid))
            return false;

        $query = "SELECT cityid FROM " . wpjobportal::$_db->prefix . "wj_portal_jobcities WHERE jobid = " . esc_sql($wpjobportal_jobid);

        $old_cities = wpjobportaldb::get_results($query);

        $wpjobportal_id_array = wpjobportalphplib::wpJP_explode(",", $city_id);
        $error = array();

        foreach ($old_cities AS $oldcityid) {
            $wpjobportal_match = false;
            foreach ($wpjobportal_id_array AS $cityid) {
                if ($oldcityid->cityid == $cityid) {
                    $wpjobportal_match = true;
                    break;
                }
            }
            if ($wpjobportal_match == false) {
                $query = "DELETE FROM " . wpjobportal::$_db->prefix . "wj_portal_jobcities WHERE jobid = " . esc_sql($wpjobportal_jobid) . " AND cityid = " . esc_sql($oldcityid->cityid);
                if (!wpjobportaldb::query($query)) {
                    $error[] = wpjobportal::$_db->last_error;
                }
            }
        }
        foreach ($wpjobportal_id_array AS $cityid) {
            $wpjobportal_insert = true;
            foreach ($old_cities AS $oldcityid) {
                if ($oldcityid->cityid == $cityid) {
                    $wpjobportal_insert = false;
                    break;
                }
            }
            if ($wpjobportal_insert) {
                $wpjobportal_row = WPJOBPORTALincluder::getJSTable('jobcities');
                $cols['jobid'] = $wpjobportal_jobid;
                $cols['cityid'] = $cityid;
                if (!$wpjobportal_row->bind($cols)) {
                    $error[] = wpjobportal::$_db->last_error;
                }
                if (!$wpjobportal_row->store()) {
                    $error[] = wpjobportal::$_db->last_error;
                }
            }
        }
        if (empty($error))
            return true;
        else
            return false;
    }

    function deleteJobs($wpjobportal_ids) {
        if (empty($wpjobportal_ids))
            return false;
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('job');
        $wpjobportal_notdeleted = 0;

        foreach ($wpjobportal_ids as $wpjobportal_id) {
            if(!is_numeric($wpjobportal_id)){
                continue;
            }
            if ($this->jobCanDelete($wpjobportal_id) == true) {
                $wpjobportal_mailextradata = array();
                $wpjobportal_resultforsendmail = WPJOBPORTALincluder::getJSModel('job')->getJobInfoForEmail($wpjobportal_id);
                if(empty($wpjobportal_resultforsendmail)){ // to handle log errors in case of empty object or false coming from the "getJobInfoForEmail" function
                  $wpjobportal_mailextradata['jobtitle'] = '';
                  $wpjobportal_mailextradata['companyname'] = '';
                  $wpjobportal_mailextradata['user'] = '';
                  $wpjobportal_mailextradata['useremail'] = '';
                }else{
                  $wpjobportal_mailextradata['jobtitle'] = $wpjobportal_resultforsendmail->jobtitle;
                  $wpjobportal_mailextradata['companyname'] = $wpjobportal_resultforsendmail->companyname;
                  $wpjobportal_mailextradata['user'] = $wpjobportal_resultforsendmail->username;
                  $wpjobportal_mailextradata['useremail'] = $wpjobportal_resultforsendmail->useremail;
                }

                if (!$wpjobportal_row->delete($wpjobportal_id)) {
                    $wpjobportal_notdeleted += 1;
                } else {
                    $query = "DELETE FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobcities` WHERE jobid = " . esc_sql($wpjobportal_id);
                    wpjobportaldb::query($query);
                    if(in_array('shortlist', wpjobportal::$_active_addons)){
                        $query = "DELETE FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobshortlist` WHERE jobid = " . esc_sql($wpjobportal_id);
                        wpjobportaldb::query($query);
                    }
                    WPJOBPORTALincluder::getJSModel('emailtemplate')->sendMail(2, 2, $wpjobportal_id,$wpjobportal_mailextradata); // 2 for job,2 for DELETE job
                    // action hook for delete job
                    do_action('wpjobportal_after_delete_job_hook',$wpjobportal_id);
                }
            } else {
                $wpjobportal_notdeleted += 1;
            }
        }
        if ($wpjobportal_notdeleted == 0) {
            WPJOBPORTALMessages::$wpjobportal_counter = false;
            return WPJOBPORTAL_DELETED;
        } else {
            WPJOBPORTALMessages::$wpjobportal_counter = $wpjobportal_notdeleted;
            return WPJOBPORTAL_DELETE_ERROR;
        }
    }

    function jobCanDelete($wpjobportal_jobid) {
        if (!is_numeric($wpjobportal_jobid))
            return false;
        if(!wpjobportal::$_common->wpjp_isadmin()){
            if(!$this->getIfJobOwner($wpjobportal_jobid)){
                return false;
            }
        }
        $query = "SELECT
                    ( SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` WHERE jobid = " . esc_sql($wpjobportal_jobid) . ")
                    AS total ";
        $wpjobportal_total = wpjobportaldb::get_var($query);
        if ($wpjobportal_total > 0)
            return false;
        else
            return true;
    }

    function getJobInfoForEmail($wpjobportal_jobid) {
        if ((is_numeric($wpjobportal_jobid) == false))
            return false;
        $query = "SELECT job.title AS jobtitle, company.contactemail AS useremail,company.name AS companyname, user.first_name AS username
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                    ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON job.companyid=company.id
                    JOIN `" . wpjobportal::$_db->prefix . "wj_portal_users` AS user ON user.id = job.uid
                    WHERE job.id =" . esc_sql($wpjobportal_jobid);
        $return_value = wpjobportaldb::get_row($query);
        return $return_value;
    }

    function jobEnforceDelete($wpjobportal_jobid, $wpjobportal_uid) {
        if (is_numeric($wpjobportal_jobid) == false)
            return false;
        $wpjobportal_serverjodid = 0;
        $wpjobportal_inquery = "";
        $foq = "";
        $wpjobportal_select_query = "";
        if(in_array('message', wpjobportal::$_active_addons)){
            $wpjobportal_select_query .= ",message";
            $wpjobportal_inquery .= " LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_messages` AS message ON job.id=message.jobid";
        }
        if(in_array('shortlist', wpjobportal::$_active_addons)){
            $wpjobportal_select_query .= ",jobshortlist";
            $wpjobportal_inquery .= " LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobshortlist` AS jobshortlist ON job.id=jobshortlist.jobid";
        }
        $query = "DELETE  job,apply,jobcity ";
        $query .= $wpjobportal_select_query;
        $query .= "
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` AS apply ON job.id=apply.jobid
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobcities` AS jobcity ON job.id=jobcity.jobid";
                    $query .= $wpjobportal_inquery;
                    $query .= " WHERE job.id = " . esc_sql($wpjobportal_jobid);
        if (!wpjobportaldb::query($query)) {
            return WPJOBPORTAL_DELETE_ERROR;
        }
        // action hook for delete job
        do_action('wpjobportal_after_delete_job_hook',$wpjobportal_jobid);
        return WPJOBPORTAL_DELETED;
    }

    function featuredJobValidation($wpjobportal_jobid) {
        if (!is_numeric($wpjobportal_jobid))
            return false;
        $query = "SELECT COUNT(job.id) FROM " . wpjobportal::$_db->prefix . "wj_portal_jobs  AS job
                WHERE job.isfeaturedjob=1 AND job.id = " . esc_sql($wpjobportal_jobid);
        $wpjobportal_result = wpjobportaldb::get_var($query);
        if ($wpjobportal_result > 0)
            return false;
        else
            return true;
    }

    function checkCall() {
        // DB class limitations
        $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_config` SET configvalue = configvalue+1 WHERE configname = 'wpjobportalupdatecount'";
        wpjobportaldb::query($query);
        $query = "SELECT configvalue AS wpjobportalupdatecount FROM `" . wpjobportal::$_db->prefix . "wj_portal_config` WHERE configname = 'wpjobportalupdatecount'";
        $wpjobportal_result = wpjobportaldb::get_var($query);
        if ($wpjobportal_result >= 100) {
            WPJOBPORTALincluder::getJSModel('wpjobportal')->getConcurrentrequestdata();
        }
    }

    function getJobId() {

        $query = "Select jobid from `" . wpjobportal::$_db->prefix . "wj_portal_jobs`";
        $wpjobportal_match = '';
        do {

            $wpjobportal_jobid = "";
            $length = 9;
            $possible = "2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ!@#";
            // we refer to the length of $possible a few times, so let's grab it now
            $wpjobportal_maxlength = wpjobportalphplib::wpJP_strlen($possible);
            // check for length overflow and truncate if necessary
            if ($length > $wpjobportal_maxlength) {
                $length = $wpjobportal_maxlength;
            }
            // set up a counter for how many characters are in the password so far
            $wpjobportal_i = 0;
            // add random characters to $password until $length is reached
            while ($wpjobportal_i < $length) {
                // pick a random character from the possible ones
                $char = wpjobportalphplib::wpJP_substr($possible, wp_rand(0, $wpjobportal_maxlength - 1), 1);
                // have we already used this character in $password?

                if (!wpjobportalphplib::wpJP_strstr($wpjobportal_jobid, $char)) {
                    if ($wpjobportal_i == 0) {
                        if (ctype_alpha($char)) {
                            $wpjobportal_jobid .= $char;
                            $wpjobportal_i++;
                        }
                    } else {
                        $wpjobportal_jobid .= $char;
                        $wpjobportal_i++;
                    }
                }
            }

            $wpjobportal_rows = wpjobportaldb::get_results($query);
            foreach ($wpjobportal_rows as $wpjobportal_row) {
                if ($wpjobportal_jobid == $wpjobportal_row->jobid)
                    $wpjobportal_match = 'Y';
                else
                    $wpjobportal_match = 'N';
            }
        }while ($wpjobportal_match == 'Y');
        return $wpjobportal_jobid;
    }

    function getJobSearch() {
    //Filters
        $title = WPJOBPORTALrequest::getVar('title');
        $wpjobportal_jobcategory = WPJOBPORTALrequest::getVar('jobcategory');
        $wpjobportal_jobsubcategory = WPJOBPORTALrequest::getVar('jobsubcategory');
        $wpjobportal_jobtype = WPJOBPORTALrequest::getVar('jobtype');
        $wpjobportal_jobstatus = WPJOBPORTALrequest::getVar('jobstatus');
        $wpjobportal_salaryrangefrom = WPJOBPORTALrequest::getVar('salaryrangefrom');
        $wpjobportal_salaryrangeto = WPJOBPORTALrequest::getVar('salaryrangeto');
        $wpjobportal_salaryrangetype = WPJOBPORTALrequest::getVar('salaryrangetype');
        $shift = WPJOBPORTALrequest::getVar('shift');
        $durration = WPJOBPORTALrequest::getVar('durration');
        $wpjobportal_startpublishing = WPJOBPORTALrequest::getVar('startpublishing');
        $wpjobportal_stoppublishing = WPJOBPORTALrequest::getVar('stoppublishing');
        $wpjobportal_company = WPJOBPORTALrequest::getVar('company');
        $city = WPJOBPORTALrequest::getVar('city');
        $zipcode = WPJOBPORTALrequest::getVar('zipcode');
        $currency = WPJOBPORTALrequest::getVar('currency');
        $longitude = WPJOBPORTALrequest::getVar('longitude');
        $latitude = WPJOBPORTALrequest::getVar('latitude');
        $radius = WPJOBPORTALrequest::getVar('radius');
        $radius_length_type = WPJOBPORTALrequest::getVar('radius_length_type');
        $wpjobportal_keywords = WPJOBPORTALrequest::getVar('keywords');

        wpjobportal::$_data['filter']['title'] = $title;
        wpjobportal::$_data['filter']['jobcategory'] = $wpjobportal_jobcategory;
        wpjobportal::$_data['filter']['jobsubcategory'] = $wpjobportal_jobsubcategory;
        wpjobportal::$_data['filter']['jobtype'] = $wpjobportal_jobtype;
        wpjobportal::$_data['filter']['jobstatus'] = $wpjobportal_jobstatus;
        wpjobportal::$_data['filter']['salaryrangefrom'] = $wpjobportal_salaryrangefrom;
        wpjobportal::$_data['filter']['salaryrangeto'] = $wpjobportal_salaryrangeto;
        wpjobportal::$_data['filter']['salaryrangetype'] = $wpjobportal_salaryrangetype;
        wpjobportal::$_data['filter']['shift'] = $shift;
        wpjobportal::$_data['filter']['durration'] = $durration;
        wpjobportal::$_data['filter']['startpublishing'] = $wpjobportal_startpublishing;
        wpjobportal::$_data['filter']['stoppublishing'] = $wpjobportal_stoppublishing;
        wpjobportal::$_data['filter']['company'] = $wpjobportal_company;
        wpjobportal::$_data['filter']['city'] = $city;
        wpjobportal::$_data['filter']['zipcode'] = $zipcode;
        wpjobportal::$_data['filter']['currency'] = $currency;
        wpjobportal::$_data['filter']['longitude'] = $longitude;
        wpjobportal::$_data['filter']['latitude'] = $latitude;
        wpjobportal::$_data['filter']['radius'] = $radius;
        wpjobportal::$_data['filter']['radius_length_type'] = $radius_length_type;
        wpjobportal::$_data['filter']['keywords'] = $wpjobportal_keywords;

        if ($wpjobportal_jobcategory != '')
            if (is_numeric($wpjobportal_jobcategory) == false)
                return false;
        if ($wpjobportal_jobsubcategory != '')
            if (is_numeric($wpjobportal_jobsubcategory) == false)
                return false;
        if ($wpjobportal_jobtype != '')
            if (is_numeric($wpjobportal_jobtype) == false)
                return false;
        if ($wpjobportal_jobstatus != '')
            if (is_numeric($wpjobportal_jobstatus) == false)
                return false;
        if ($wpjobportal_salaryrangefrom != '')
            if (is_numeric($wpjobportal_salaryrangefrom) == false)
                return false;
        if ($wpjobportal_salaryrangeto != '')
            if (is_numeric($wpjobportal_salaryrangeto) == false)
                return false;
        if ($wpjobportal_salaryrangetype != '')
            if (is_numeric($wpjobportal_salaryrangetype) == false)
                return false;
        if ($shift != '')
            if (is_numeric($shift) == false)
                return false;
        if ($wpjobportal_company != '')
            if (is_numeric($wpjobportal_company) == false)
                return false;
        if ($currency != '')
            if (is_numeric($currency) == false)
                return false;


        $wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
        if ($wpjobportal_startpublishing != '') {
            $wpjobportal_startpublishing = gmdate('Y-m-d', strtotime($wpjobportal_startpublishing));
        }
        if ($wpjobportal_stoppublishing != '') {
            $wpjobportal_stoppublishing = gmdate('Y-m-d', strtotime($wpjobportal_stoppublishing));
        }

        $wpjobportal_issalary = '';
        //for radius search
        switch ($radius_length_type) {
            case "m":$radiuslength = 6378137;
                break;
            case "km":$radiuslength = 6378.137;
                break;
            case "mile":$radiuslength = 3963.191;
                break;
            case "nacmiles":$radiuslength = 3441.596;
                break;
        }
        if ($wpjobportal_keywords) {// For keyword Search
            $wpjobportal_keywords = wpjobportalphplib::wpJP_explode(' ', $wpjobportal_keywords);
            $length = count($wpjobportal_keywords);
            if ($length <= 5) {// For Limit keywords to 5
                $wpjobportal_i = $length;
            } else {
                $wpjobportal_i = 5;
            }
            for ($j = 0; $j < $wpjobportal_i; $j++) {
                $wpjobportal_keys[] = " job.metakeywords Like '%" . esc_sql($wpjobportal_keywords[$j]) . "%'";
            }
        }
        $wpjobportal_selectdistance = " ";
        if ($longitude != '' && $latitude != '' && $radius != '') {
            $radiussearch = " acos((SIN( PI()* ".esc_sql($latitude)." /180 )*SIN( PI()*job.latitude/180 ))+(cos(PI()* ".esc_sql($latitude)." /180)*COS( PI()*job.latitude/180) *COS(PI()*job.longitude/180-PI()* ".esc_sql($longitude)." /180)))* ".esc_sql($radiuslength)." <= ".esc_sql($radius);
            $wpjobportal_selectdistance = " ,acos((sin(PI()*".esc_sql($latitude)."/180)*sin(PI()*job.latitude/180))+(cos(PI()*".esc_sql($latitude)."/180)*cos(PI()*job.latitude/180)*cose(PI()*job.longitude/180 - PI()*".esc_sql($longitude)."/180)))*".esc_sql($radiuslength)." AS distance ";
        }

        $wherequery = '';
        if ($title != '') {
            $title_keywords = wpjobportalphplib::wpJP_explode(' ', $title);
            $tlength = count($title_keywords);
            if ($tlength <= 5) {// For Limit keywords to 5
                $r = $tlength;
            } else {
                $r = 5;
            }
            for ($wpjobportal_k = 0; $wpjobportal_k < $r; $wpjobportal_k++) {
                $t_keywords = wpjobportalphplib::wpJP_str_replace("'", "", $title_keywords[$wpjobportal_k]);
                $titlekeys[] = " job.title LIKE '%" . esc_sql($t_keywords) . "%'";
            }
        }
        if ($wpjobportal_jobcategory != '' && is_numeric($wpjobportal_jobcategory))
            if ($wpjobportal_jobcategory != '')
                $wherequery .= " AND job.jobcategory = " . esc_sql($wpjobportal_jobcategory);
        if (isset($wpjobportal_keys))
            $wherequery .= " AND ( " . implode(' OR ', esc_sql($wpjobportal_keys)) . " )";
        if (isset($titlekeys))
            $wherequery .= " AND ( " . implode(' OR ', esc_sql($titlekeys)) . " )";
        if ($wpjobportal_jobsubcategory != '' && is_numeric($wpjobportal_jobsubcategory))
            $wherequery .= " AND job.subcategoryid = " . esc_sql($wpjobportal_jobsubcategory);
        if ($wpjobportal_jobtype != '' && is_numeric($wpjobportal_jobtype))
            $wherequery .= " AND job.jobtype = " . esc_sql($wpjobportal_jobtype);
        if ($wpjobportal_jobstatus != '' && is_numeric($wpjobportal_jobstatus))
            $wherequery .= " AND job.jobstatus = " . esc_sql($wpjobportal_jobstatus);
        if ($wpjobportal_salaryrangefrom != '') {
            $query = "SELECT salfrom.rangestart
            FROM `" . wpjobportal::$_db->prefix . "wj_portal_salaryrange` AS salfrom
            WHERE salfrom.id = " . esc_sql($wpjobportal_salaryrangefrom);

            $wpjobportal_rangestart_value = wpjobportaldb::get_var($query);
            $wherequery .= " AND salaryrangefrom.rangestart >= " . esc_sql($wpjobportal_rangestart_value);
            $wpjobportal_issalary = 1;
        }
        if ($wpjobportal_salaryrangeto != '') {
            $query = "SELECT salto.rangestart
            FROM `" . wpjobportal::$_db->prefix . "wj_portal_salaryrange` AS salto
            WHERE salto.id = " . esc_sql($wpjobportal_salaryrangeto);

            $wpjobportal_rangeend_value = wpjobportaldb::get_var($query);
            $wherequery .= " AND salaryrangeto.rangeend <= " . esc_sql($wpjobportal_rangeend_value);
            $wpjobportal_issalary = 1;
        }
        if (($wpjobportal_issalary != '') && ($wpjobportal_salaryrangetype != '' && is_numeric($wpjobportal_salaryrangetype))) {
            $wherequery .= " AND job.salaryrangetype = " . esc_sql($wpjobportal_salaryrangetype);
        }
        if ($shift != '' && is_numeric($shift))
            $wherequery .= " AND job.shift = " . esc_sql($shift);
        if ($durration != '')
            $wherequery .= " AND job.duration LIKE '" . esc_sql($durration) . "'";
        if ($wpjobportal_startpublishing != '')
            $wherequery .= " AND job.startpublishing >= '" . esc_sql($wpjobportal_startpublishing) . "'";
        if ($wpjobportal_stoppublishing != '')
            $wherequery .= " AND job.stoppublishing <= '" . esc_sql($wpjobportal_stoppublishing) . "'";
        if ($wpjobportal_company != '' && is_numeric($wpjobportal_company))
            $wherequery .= " AND job.companyid = " . esc_sql($wpjobportal_company);
        if ($city != '') {
            $city_value = wpjobportalphplib::wpJP_explode(',', $city);
            $lenght = count($city_value);
            for ($wpjobportal_i = 0; $wpjobportal_i < $lenght; $wpjobportal_i++) {
                if(is_numeric($city_value[$wpjobportal_i])){
                    if ($wpjobportal_i == 0){
                        $wherequery .= " AND ( mjob.cityid=" . esc_sql($city_value[$wpjobportal_i]);
                    }else{
                        $wherequery .= " OR mjob.cityid=" . esc_sql($city_value[$wpjobportal_i]);
                    }
                }
            }
            $wherequery .= ")";
        }

        if ($zipcode != '')
            $wherequery .= " AND job.zipcode = '" . esc_sql($zipcode) . "'";
        if (isset($radiussearch) && $radiussearch != '')
            $wherequery .= " AND " . esc_sql($radiussearch);

        //Pagination
        $wpjobportal_curdate = gmdate('Y-m-d');
        $query = "SELECT count(DISTINCT job.id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON job.jobcategory = cat.id
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_salaryrange` AS salaryrangefrom ON job.salaryrangefrom = salaryrangefrom.id
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_salaryrange` AS salaryrangeto ON job.salaryrangeto = salaryrangeto.id";
        $query .= " LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobcities` AS mjob ON mjob.jobid = job.id ";
        $query .= " WHERE job.status = 1 ";
        if ($wpjobportal_startpublishing == '')
            $query .= " AND DATE(job.startpublishing) <= " . esc_sql($wpjobportal_curdate);
        if ($wpjobportal_stoppublishing == '')
            $query .= " AND DATE(job.stoppublishing) >= " . esc_sql($wpjobportal_curdate);
        $query .= $wherequery;

        $wpjobportal_total = wpjobportaldb::get_var($query);
        wpjobportal::$_data['total'] = $wpjobportal_total;
        wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($wpjobportal_total);

        //Data
        // company is must for job
        $query = "SELECT DISTINCT job.*, cat.cat_title, jobtype.title AS jobtypetitle, jobstatus.title AS jobstatustitle
                , salaryrangefrom.rangestart AS salaryfrom, salaryrangeto.rangeend AS salaryend
                , company.name AS companyname, company.url
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON job.companyid = company.id
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON job.jobcategory = cat.id
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON job.jobtype = jobtype.id
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobstatus` AS jobstatus ON job.jobstatus = jobstatus.id
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_salaryrange` AS salaryrangefrom ON job.salaryrangefrom = salaryrangefrom.id
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_salaryrange` AS salaryrangeto ON job.salaryrangeto = salaryrangeto.id";
        $query .= " LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobcities` AS mjob ON mjob.jobid = job.id ";
        $query .= " WHERE  job.status = 1 ";
        if ($wpjobportal_startpublishing == '')
            $query .= " AND DATE(job.startpublishing) <= " . esc_sql($wpjobportal_curdate);
        if ($wpjobportal_stoppublishing == '')
            $query .= " AND DATE(job.stoppublishing) >= " . esc_sql($wpjobportal_curdate);
        if ($currency != '')
            $query.= " AND currency.id = job.currencyid ";
        $query .= $wherequery;
        $query.=" LIMIT " . WPJOBPORTALpagination::$_offset . "," . WPJOBPORTALpagination::$_limit;

        wpjobportal::$_data[0] = wpjobportaldb::get_results($query);

        foreach (wpjobportal::$_data[0] AS $wpjobportal_searchdata) {  // for multicity select
            $multicitydata = $this->getMultiCityData($wpjobportal_searchdata->id);
            if ($multicitydata != "")
                $wpjobportal_searchdata->city = $multicitydata;
        }

        return;
    }

    function getMyJobs($wpjobportal_uid) {
       if (!is_numeric($wpjobportal_uid)) return false;
        # Sorting
        $this->sorting();
        $this->checkCall();
        # pagination
        $query = "SELECT COUNT(job.id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON job.companyid = company.id
                WHERE job.uid =". esc_sql($wpjobportal_uid);
        $wpjobportal_total = wpjobportaldb::get_var($query);
        wpjobportal::$_data['total'] = $wpjobportal_total;
        wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($wpjobportal_total,'myjobs');

        # Data Query Listing
        // company is must for job
        $query = "SELECT job.endfeatureddate,job.id,job.uid,job.title,job.isfeaturedjob,job.serverid,job.noofjobs,job.city,job.status,
                CONCAT(job.alias,'-',job.id) AS jobaliasid,job.created,job.serverid,company.name AS companyname,company.id AS companyid,company.logofilename,CONCAT(company.alias,'-',company.id) AS compnayaliasid,job.salarytype,job.salarymin,job.salarymax,salaryrangetype.title AS salarydurationtitle,job.currency,
                cat.cat_title, jobtype.title AS jobtypetitle,salaryrangetype.title AS srangetypetitle,
                (SELECT count(jobapply.id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` AS jobapply
                 WHERE jobapply.jobid = job.id and jobapply.status = 1) AS resumeapplied ,job.params,job.startpublishing,job.stoppublishing, job.description
                 ,LOWER(jobtype.title) AS jobtypetit,jobtype.color AS jobtypecolor
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON company.id = job.companyid
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON cat.id = job.jobcategory
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON jobtype.id = job.jobtype
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON job.city = city.id
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_salaryrangetypes` AS salaryrangetype ON salaryrangetype.id = job.salaryduration
                WHERE job.uid =". esc_sql($wpjobportal_uid) ;
        # Sorting Merge In Query
        $query.= " ORDER BY" . wpjobportal::$_data['sorting'];
        $query.=" LIMIT " . WPJOBPORTALpagination::$_offset . "," . WPJOBPORTALpagination::$_limit;
        $wpjobportal_results = wpjobportaldb::get_results($query);
        $wpjobportal_data = array();
        foreach ($wpjobportal_results AS $d) {
            $d->location = WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($d->city);
            $wpjobportal_data[] = $d;
        }
        $wpjobportal_results = $wpjobportal_data;
         wpjobportal::$_data[0] = $wpjobportal_data;
        wpjobportal::$wpjobportal_data['fields'] = wpjobportal::$_wpjpfieldordering->getFieldsOrderingforForm(2);
        wpjobportal::$_data['config'] = wpjobportal::$_config->getConfigByFor('job');
        return;
    }

    function getJobsByCompany($cid) {
        if (!is_numeric($cid)) return false;
        
        $query = "SELECT job.endfeatureddate,job.id,job.uid,job.title,job.isfeaturedjob,job.city,job.status,
        CONCAT(job.alias,'-',job.id) AS jobaliasid,job.created,company.name AS companyname,company.id AS companyid,company.logofilename,job.salarytype,job.salarymin,job.salarymax,salaryrangetype.title AS salarydurationtitle,job.currency,
                cat.cat_title, jobtype.title AS jobtypetitle,salaryrangetype.title AS srangetypetitle ,job.startpublishing,job.stoppublishing
                 ,LOWER(jobtype.title) AS jobtypetit,jobtype.color AS jobtypecolor
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON company.id = job.companyid
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON cat.id = job.jobcategory
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON jobtype.id = job.jobtype
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON job.city = city.id
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_salaryrangetypes` AS salaryrangetype ON salaryrangetype.id = job.salaryduration
                WHERE job.status = 1 AND DATE(job.startpublishing) <= CURDATE() AND DATE(job.stoppublishing) >= CURDATE() and company.id =". esc_sql($cid) ;
        # Sorting Merge In Query
        $query.= " ORDER BY job.created DESC LIMIT 3";
        $wpjobportal_results = wpjobportaldb::get_results($query);
        $wpjobportal_data = array();
        foreach ($wpjobportal_results AS $d) {
            $d->location = WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($d->city);
            $wpjobportal_data[] = $d;
        }
        $wpjobportal_results = $wpjobportal_data;
        return $wpjobportal_data;
    }

    function getJobsByCategories() {
        $query = "SELECT category.cat_title, CONCAT(category.alias,'-',category.id) AS aliasid,category.serverid,category.id AS categoryid
            ,(SELECT count(job.id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                WHERE job.status = 1 AND job.jobcategory = category.id AND DATE(job.startpublishing) <= CURDATE() AND DATE(job.stoppublishing) >= CURDATE())  AS totaljobs
            FROM `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS category
            WHERE category.isactive = 1 AND category.parentid = 0 ORDER BY category.ordering ASC";
        $wpjobportal_categories = wpjobportaldb::get_results($query);
        $wpjobportal_config_array = wpjobportal::$_config->getConfigByFor('category');
        $wpjobportal_subcategory_limit = 3;
        if($wpjobportal_config_array['subcategory_limit'] != ''){ // to handle float value in configuration
            $wpjobportal_subcategory_limit = ceil($wpjobportal_config_array['subcategory_limit']);
        }
        foreach($wpjobportal_categories AS $wpjobportal_category){
            $wpjobportal_total = 0;
            $query = "SELECT category.cat_title, CONCAT(category.alias,'-',category.id) AS aliasid,category.serverid
                ,(SELECT count(job.id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                    WHERE job.jobcategory = category.id AND DATE(job.startpublishing) <= CURDATE() AND DATE(job.stoppublishing) >= CURDATE())  AS totaljobs
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS category
                WHERE category.isactive = 1 AND category.parentid = ".esc_sql($wpjobportal_category->categoryid)." ORDER BY category.ordering ASC ";
            $wpjobportal_subcats = wpjobportal::$_db->get_results($query);
            $wpjobportal_i = 0;
            foreach ($wpjobportal_subcats as $wpjobportal_id => $scat) {
                $wpjobportal_total += $scat->totaljobs;
                if($wpjobportal_subcategory_limit <= $wpjobportal_i){
                    unset($wpjobportal_subcats[$wpjobportal_id]);
                }
                $wpjobportal_i++;
            }
            $wpjobportal_category->subcat = $wpjobportal_subcats;
            $wpjobportal_category->total_sub_jobs = $wpjobportal_total;
        }


        if(wpjobportal::$_configuration['job_resume_show_all_categories'] == 0){//conifguration based
            $final_arr = array();
            foreach ($wpjobportal_categories as $wpjobportal_job_category) {
                if($wpjobportal_job_category->totaljobs != 0 || $wpjobportal_job_category->total_sub_jobs != 0){
                    $final_arr[] = $wpjobportal_job_category;
                }
            }
            $wpjobportal_categories = $final_arr;
        }

        wpjobportal::$_data[0] = $wpjobportal_categories;
        wpjobportal::$_data['config'] =  wpjobportal::$_config->getConfigByFor('category');
        return;
    }

    function getJobsByTypes() {
        $query = "SELECT jobtype.title,jobtype.id, jobtype.serverid,CONCAT(jobtype.alias,'-',jobtype.id) AS alias
                ,(SELECT count(job.id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                where job.status = 1 AND job.jobtype = jobtype.id AND DATE(job.startpublishing) <= CURDATE() AND DATE(job.stoppublishing) >= CURDATE())  AS totaljobs
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype
                WHERE jobtype.isactive = 1 ORDER BY jobtype.ordering ASC"; // to show job types by the ordering set from admin side
        wpjobportal::$_data[0] = wpjobportaldb::get_results($query);
        wpjobportal::$_data['config'] =  wpjobportal::$_config->getConfigByFor('jobtype');
        return;
    }


    function getJobsByCities(){
        $query="SELECT city.id AS cityid, city.name AS cityname
                ,country.name AS countryname,COUNT(job.id) AS totaljobs
                FROM `". wpjobportal::$_db->prefix ."wj_portal_jobcities` AS jobc
                JOIN `". wpjobportal::$_db->prefix ."wj_portal_jobs` AS job ON jobc.jobid = job.id
                JOIN `". wpjobportal::$_db->prefix ."wj_portal_cities` AS city ON city.id = jobc.cityid
                JOIN `". wpjobportal::$_db->prefix ."wj_portal_countries` AS country ON country.id = city.countryid
                WHERE country.enabled = 1 AND job.status = 1
                AND DATE(job.stoppublishing) >= CURDATE() AND DATE(job.startpublishing) <= CURDATE() GROUP BY city.id ORDER BY cityname";
        $wpjobportal_data = wpjobportaldb::get_results($query);
        wpjobportal::$_data[0] = $wpjobportal_data;
        wpjobportal::$_data['config'] =  wpjobportal::$_config->getConfigByFor('jobcity');
        return;
    }


    private function makeQueryFromArray($for, $wpjobportal_array) {
        if (empty($wpjobportal_array))
            return false;

        if (!is_array($wpjobportal_array) && $for != 'metakeywords' && $for != 'tags') {
            $wpjobportal_newarray[] = $wpjobportal_array;
            $wpjobportal_array = $wpjobportal_newarray;
        }
        $qa = array();
        switch ($for) {
            case 'metakeywords':
                $wpjobportal_array = wpjobportalphplib::wpJP_explode(",", $wpjobportal_array);
                $wpjobportal_total = count($wpjobportal_array);
                if ($wpjobportal_total > 5)
                    $wpjobportal_total = 5;
                for ($wpjobportal_i = 0; $wpjobportal_i < $wpjobportal_total; $wpjobportal_i++) {
                    $qa[] = "job.metakeywords LIKE '%" . esc_sql($wpjobportal_array[$wpjobportal_i]) . "%'";
                }
                break;
            case 'company':
                foreach ($wpjobportal_array as $wpjobportal_item) {
                    if (is_numeric($wpjobportal_item)) {
                        $qa[] = "job.companyid = " . esc_sql($wpjobportal_item);
                    }
                }
                break;
            case 'category':
                foreach ($wpjobportal_array as $wpjobportal_item) {
                    if (is_numeric($wpjobportal_item)) {
                        $query = "SELECT id FROM `" . wpjobportal::$_db->prefix . "wj_portal_categories` WHERE parentid = ". esc_sql($wpjobportal_item);
                        $cats = wpjobportaldb::get_results($query);
                        $wpjobportal_ids = [];
                        foreach ($cats as $cat) {
                            $wpjobportal_ids[] = $cat->id;
                        }
                        $wpjobportal_ids[] = $wpjobportal_item;
                        $wpjobportal_ids = implode(",",$wpjobportal_ids);
                        $qa[] = "job.jobcategory IN(" . esc_sql($wpjobportal_ids).")";
                    }
                }
                break;
            case 'careerlevel':
                foreach ($wpjobportal_array as $wpjobportal_item) {
                    if (is_numeric($wpjobportal_item)) {
                        $qa[] = "job.careerlevel = " . esc_sql($wpjobportal_item);
                    }
                }
                break;
            case 'jobtype':
                foreach ($wpjobportal_array as $wpjobportal_item) {
                    if (is_numeric($wpjobportal_item)) {
                        $qa[] = "job.jobtype = " . esc_sql($wpjobportal_item);
                    }
                }
                break;
            case 'jobstatus':
                foreach ($wpjobportal_array as $wpjobportal_item) {
                    if (is_numeric($wpjobportal_item)) {
                        $qa[] = "job.jobstatus = " . esc_sql($wpjobportal_item);
                    }
                }
                break;
            case 'education':
                foreach ($wpjobportal_array as $wpjobportal_item) {
                    if (is_numeric($wpjobportal_item)) {
                        $qa[] = "job.educationid = " . esc_sql($wpjobportal_item);
                    }
                }
                break;
            case 'city':
                $a = wpjobportalphplib::wpJP_explode(',', $wpjobportal_array[0]);
                foreach ($a as $wpjobportal_item) {
                    if (is_numeric($wpjobportal_item)) {
                        //$qa[] = "job.city LIKE '%" . esc_sql($wpjobportal_item) . "%'";
                        $qa[] = "  FIND_IN_SET('" . esc_sql($wpjobportal_item) . "', job.city) > 0 ";
                    }
                }
                break;
            case 'job_cities':
                foreach ($wpjobportal_array as $wpjobportal_item) {
                    if (is_numeric($wpjobportal_item)) {
                        //$qa[] = "job.city LIKE '%" . esc_sql($wpjobportal_item) . "%'";
                        $qa[] = "  FIND_IN_SET('" . esc_sql($wpjobportal_item) . "', job.city) > 0 ";
                    }
                }
                break;
            case 'tags':
                $wpjobportal_array = wpjobportalphplib::wpJP_explode(',', $wpjobportal_array);
                foreach ($wpjobportal_array as $wpjobportal_item) {
                    $qa[] = "job.tags LIKE '%" . esc_sql($wpjobportal_item) . "%'";
                }
                break;
            case 'job_tags':
                foreach ($wpjobportal_array as $wpjobportal_item) {
                    $qa[] = "job.tags LIKE '%" . esc_sql($wpjobportal_item) . "%'";
                }
                break;
            case 'jobid': // new case for shortcode atttributes
                $wpjobportal_job_id_list = implode(',', $wpjobportal_array);
                $qa[] = "  job.id IN (" . esc_sql($wpjobportal_job_id_list) . ") ";
                break;
            default:
                return false;
                break;
        }
        $query = implode(" OR ", $qa);
        return $query;
    }

    function isvalidJSON($wpjobportal_string) {
        return ((is_string($wpjobportal_string) &&
                (is_object(json_decode($wpjobportal_string)) ||
                is_array(json_decode($wpjobportal_string))))) ? true : false;
    }

    function getRSSJobs() {
        $wpjobportal_job_rss = wpjobportal::$_config->getConfigurationByConfigName('job_rss');
        if ($wpjobportal_job_rss == 1) {
            $wpjobportal_curdate = gmdate('Y-m-d H:i:s');
            $query = "SELECT job.title,job.noofjobs,job.id, cat.cat_title,company.logofilename AS logofilename,company.id AS companyid,
                        company.name AS comp_title,jobtype.title AS jobtype,jobstatus.title AS jobstatus,CONCAT(job.alias,'-',job.id) AS jobaliasid,salarytype.title AS salarytype
                        FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                        ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON job.companyid = company.id
                        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON job.jobcategory = cat.id
                        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON job.jobtype = jobtype.id
                        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobstatus` AS jobstatus ON job.jobstatus = jobstatus.id
                        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_salaryrangetypes` AS salarytype ON job.salaryrangetype = salarytype.id
                        WHERE job.status = 1 AND job.startpublishing <= '" . esc_sql($wpjobportal_curdate) . "' AND job.stoppublishing >= '" . esc_sql($wpjobportal_curdate) . "'";
            $query .= " ORDER BY  job.startpublishing DESC";
            $wpjobportal_jobs = wpjobportal::$_db->get_results($query);
            return $wpjobportal_jobs;
        }
        return false;
    }

    private function processShortcodeAttributes() {
        $wpjobportal_inquery = "";

        /* shortcode attributes
    'companies' => '',
    'categories' => '',
    'types' => '',
    'locations' => '',
    'ids' => '',
         */

        // comapnies
        $wpjobportal_company_list = WPJOBPORTALrequest::getVar('companies', 'shortcode_option', false);
        if ($wpjobportal_company_list && $wpjobportal_company_list !='' ) {
            $wpjobportal_company_array = explode(',', $wpjobportal_company_list);// attribute contains comma seprated list. converting it into array to use exsisting code
            $res = $this->makeQueryFromArray('company', $wpjobportal_company_array);
            if ($res){
                $wpjobportal_inquery .= " AND ( " . esc_sql($res ). " )";
            }
        }

        // cateogries
        $wpjobportal_category_list = WPJOBPORTALrequest::getVar('categories', 'shortcode_option', false);
        if ($wpjobportal_category_list && $wpjobportal_category_list !='' ) {
            $wpjobportal_category_array = explode(',', $wpjobportal_category_list);// attribute contains comma seprated list. converting it into array to use exsisting code
            $res = $this->makeQueryFromArray('category', $wpjobportal_category_array);
            if ($res){
                $wpjobportal_inquery .= " AND ( " . esc_sql($res ). " )";
            }
        }


        // jobtypes
        $wpjobportal_jobtype_list = WPJOBPORTALrequest::getVar('types', 'shortcode_option', false);
        if ($wpjobportal_jobtype_list && $wpjobportal_jobtype_list !='') {
            $wpjobportal_jobtype_array = explode(',', $wpjobportal_jobtype_list);// attribute contains comma seprated list. converting it into array to use exsisting code
            $res = $this->makeQueryFromArray('jobtype', $wpjobportal_jobtype_array);
            if ($res){
                $wpjobportal_inquery .= " AND ( " . esc_sql($res ). " )";
            }
        }

        // cities
        $cities_list = WPJOBPORTALrequest::getVar('locations', 'shortcode_option', false);
        if ($cities_list && $cities_list !='' ) {
            $cities_array = explode(',', $cities_list);// attribute contains comma seprated list. converting it into array to use exsisting code
            $res = $this->makeQueryFromArray('job_cities', $cities_array);
            if ($res){
                $wpjobportal_inquery .= " AND ( " . $res. " )";// $res has quotes can not esc_sql it
            }
        }

        // job ids
        $wpjobportal_job_id_list = WPJOBPORTALrequest::getVar('ids', 'shortcode_option', false);
        if ($wpjobportal_job_id_list && $wpjobportal_job_id_list !='' ) {
            $wpjobportal_job_id_array = explode(',', $wpjobportal_job_id_list);// attribute contains comma seprated list. converting it into array to use exsisting code
            $res = $this->makeQueryFromArray('jobid', $wpjobportal_job_id_array);
            if ($res){
                $wpjobportal_inquery .= " AND ( " . $res. " )";// $res has quotes can not esc_sql it
            }
        }


        // career levels
        $careerlevel_list = WPJOBPORTALrequest::getVar('careerlevels', 'shortcode_option', false);
        if ($careerlevel_list && $careerlevel_list !='' ) {
            $careerlevel_array = explode(',', $careerlevel_list);// attribute contains comma seprated list. converting it into array to use exsisting code
            $res = $this->makeQueryFromArray('careerlevel', $careerlevel_array);
            if ($res){
                $wpjobportal_inquery .= " AND ( " . esc_sql($res ). " )";
            }
        }

        // job statuses
        $wpjobportal_jobstatus_list = WPJOBPORTALrequest::getVar('jobstatuses', 'shortcode_option', false);
        if ($wpjobportal_jobstatus_list && $wpjobportal_jobstatus_list !='' ) {
            $wpjobportal_jobstatus_array = explode(',', $wpjobportal_jobstatus_list);// attribute contains comma seprated list. converting it into array to use exsisting code
            $res = $this->makeQueryFromArray('jobstatus', $wpjobportal_jobstatus_array);
            if ($res){
                $wpjobportal_inquery .= " AND ( " . esc_sql($res ). " )";
            }
        }


        // tags
        $wpjobportal_tags_list = WPJOBPORTALrequest::getVar('tags', 'shortcode_option', false);
        if ($wpjobportal_tags_list && $wpjobportal_tags_list !='' ) {
            $wpjobportal_tags_array = explode(',', $wpjobportal_tags_list);// attribute contains comma seprated list. converting it into array to use exsisting code
            $res = $this->makeQueryFromArray('job_tags', $wpjobportal_tags_array);
            if ($res){
                $wpjobportal_inquery .= " AND ( " . $res. " )";// $res has quotes can not esc_sql it
            }
        }

        //handle attirbute for ordering
        $sorting = WPJOBPORTALrequest::getVar('sorting', 'shortcode_option', false);
        if($sorting && $sorting != ''){
            $this->makeOrderingQueryFromShortcodeAttributes($sorting);
            wpjobportal::$_data['shortcode_option_sorting'] = $sorting;
        }

        //handle attirbute for no of jobs
        $wpjobportal_no_of_jobs = WPJOBPORTALrequest::getVar('no_of_jobs', 'shortcode_option', false);
        if($wpjobportal_no_of_jobs && $wpjobportal_no_of_jobs != ''){
            wpjobportal::$_data['shortcode_option_no_of_jobs'] = (int) $wpjobportal_no_of_jobs;
        }


        // handle visibilty of data based on shortcode
        $this->handleDataVisibilityByShortcodeAttributes();

        return $wpjobportal_inquery;
    }


    function handleDataVisibilityByShortcodeAttributes() {
        /*
            'hide_filter' => '',
            'hide_filter_job_title' => '',
            'hide_filter_job_location' => '',
        */

        //handle attirbute for hide filter on job listing
        $wpjobportal_hide_filter = WPJOBPORTALrequest::getVar('hide_filter', 'shortcode_option', false);
        if($wpjobportal_hide_filter && $wpjobportal_hide_filter != ''){
            wpjobportal::$_data['shortcode_option_hide_filter'] = 1;
        }

        //handle attirbute for hide job title filter on job listing
        $wpjobportal_hide_filter_job_title = WPJOBPORTALrequest::getVar('hide_filter_job_title', 'shortcode_option', false);
        if($wpjobportal_hide_filter_job_title && $wpjobportal_hide_filter_job_title != ''){
            wpjobportal::$_data['shortcode_option_hide_filter_job_title'] = 1;
        }

        //handle attirbute for hide job location filter on job listing
        $wpjobportal_hide_filter_job_location = WPJOBPORTALrequest::getVar('hide_filter_job_location', 'shortcode_option', false);
        if($wpjobportal_hide_filter_job_location && $wpjobportal_hide_filter_job_location != ''){
            wpjobportal::$_data['shortcode_option_hide_filter_job_location'] = 1;
        }

        //handle attirbute for hide company logo on job listing
        $wpjobportal_hide_company_logo = WPJOBPORTALrequest::getVar('hide_company_logo', 'shortcode_option', false);
        if($wpjobportal_hide_company_logo && $wpjobportal_hide_company_logo != ''){
            wpjobportal::$_data['shortcode_option_hide_company_logo'] = 1;
        }

        //handle attirbute for hide company name on job listing
        $wpjobportal_hide_company_name = WPJOBPORTALrequest::getVar('hide_company_name', 'shortcode_option', false);
        if($wpjobportal_hide_company_name && $wpjobportal_hide_company_name != ''){
            wpjobportal::$_data['shortcode_option_hide_company_name'] = 1;
        }

    }

    function makeOrderingQueryFromShortcodeAttributes($sorting) {
        switch ($sorting) {
            case "title_desc":
                wpjobportal::$_ordering = "job.title DESC";
                break;
            case "title_asc":
                wpjobportal::$_ordering = "job.title ASC";
                break;
            case "category_desc":
                wpjobportal::$_ordering = "cat.cat_title DESC";
                break;
            case "category_asc":
                wpjobportal::$_ordering = "cat.cat_title ASC";
                break;
            case "jobtype_desc":
                wpjobportal::$_ordering = "jobtype.title DESC";
                break;
            case "jobtype_asc":
                wpjobportal::$_ordering = "jobtype.title ASC";
                break;
            case "jobstatus_desc":
                wpjobportal::$_ordering = "job.status DESC";
                break;
            case "jobstatus_asc":
                wpjobportal::$_ordering = "job.status ASC";
                break;
            case "company_desc":
                wpjobportal::$_ordering = "company.name DESC";
                break;
            case "company_asc":
                wpjobportal::$_ordering = "company.name ASC";
                break;
            case "salary_desc":
                wpjobportal::$_ordering = "srfrom.rangestart DESC";
                break;
            case "salary_asc":
                wpjobportal::$_ordering = "srfrom.rangestart ASC";
                break;
            case "posted_desc":
                wpjobportal::$_ordering = "job.created DESC";
                break;
            case "posted_asc":
                wpjobportal::$_ordering = "job.created ASC";
                break;
        }

        return;
    }



    private function getRefinedJobs($wpjobportal_searchajax = null) {
        $wpjobportal_inquery = "";
        if($wpjobportal_searchajax == null){
            $wpjobportal_keywords_a = isset(wpjobportal::$_search['jobs']['metakeywords']) ? wpjobportal::$_search['jobs']['metakeywords'] : '';
        }else{
            $wpjobportal_keywords_a = isset($wpjobportal_searchajax['metakeywords']) ? $wpjobportal_searchajax['metakeywords'] : '';
        }
        if ($wpjobportal_keywords_a) {
            wpjobportal::$_data['filter']['metakeywords'] = $wpjobportal_keywords_a;
            $res = $this->makeQueryFromArray('metakeywords', $wpjobportal_keywords_a);
            if ($res)
                $wpjobportal_inquery .= " AND ( " . $res . " )";
        }
        if($wpjobportal_searchajax == null){
            $wpjobportal_jobtitle = isset(wpjobportal::$_search['jobs']['jobtitle']) ? wpjobportal::$_search['jobs']['jobtitle'] : '';
        }else{
            $wpjobportal_jobtitle = isset($wpjobportal_searchajax['jobtitle']) ? $wpjobportal_searchajax['jobtitle'] : '';
        }
        if ($wpjobportal_jobtitle) {
            wpjobportal::$_data['filter']['jobtitle'] = $wpjobportal_jobtitle;
            $wpjobportal_inquery .= " AND job.title LIKE '%" . esc_sql($wpjobportal_jobtitle) . "%'";
        }
        if($wpjobportal_searchajax == null){
            $wpjobportal_company_a = isset(wpjobportal::$_search['jobs']['company']) ? wpjobportal::$_search['jobs']['company'] : '';
        }else{
            $wpjobportal_company_a = isset($wpjobportal_searchajax['company']) ? $wpjobportal_searchajax['company'] : '';
        }
        if ($wpjobportal_company_a) {
            wpjobportal::$_data['filter']['company'] = $wpjobportal_company_a;
            $res = $this->makeQueryFromArray('company', $wpjobportal_company_a);
            if ($res)
                $wpjobportal_inquery .= " AND ( " . esc_sql($res ). " )";
        }
        if($wpjobportal_searchajax == null){
            $wpjobportal_category_a = isset(wpjobportal::$_search['jobs']['category']) ? wpjobportal::$_search['jobs']['category'] : '';
        }else{
            $wpjobportal_category_a = isset($wpjobportal_searchajax['category']) ? $wpjobportal_searchajax['category'] : '';
        }
        if ($wpjobportal_category_a) {
            wpjobportal::$_data['filter']['category'] = $wpjobportal_category_a;
            $res = $this->makeQueryFromArray('category', $wpjobportal_category_a);
            if ($res)
                $wpjobportal_inquery .= " AND ( " . $res . " )";
        }
        if($wpjobportal_searchajax == null){
            $wpjobportal_jobtype_a = isset(wpjobportal::$_search['jobs']['jobtype']) ? wpjobportal::$_search['jobs']['jobtype'] : '';
        }else{
            $wpjobportal_jobtype_a = isset($wpjobportal_searchajax['jobtype']) ? $wpjobportal_searchajax['jobtype'] : '';
        }
        if ($wpjobportal_jobtype_a) {
            wpjobportal::$_data['filter']['jobtype'] = $wpjobportal_jobtype_a;
            $res = $this->makeQueryFromArray('jobtype', $wpjobportal_jobtype_a);
            if ($res)
                $wpjobportal_inquery .= " AND ( " . $res . " )";
        }
        if($wpjobportal_searchajax == null){
            $careerlevel_a = isset(wpjobportal::$_search['jobs']['careerlevel']) ? wpjobportal::$_search['jobs']['careerlevel'] : '';
        }else{
            $careerlevel_a = isset($wpjobportal_searchajax['careerlevel']) ? $wpjobportal_searchajax['careerlevel'] : '';
        }
        if ($careerlevel_a) {
            wpjobportal::$_data['filter']['careerlevel'] = $careerlevel_a;
            $res = $this->makeQueryFromArray('careerlevel', $careerlevel_a);
            if ($res)
                $wpjobportal_inquery .= " AND ( " .esc_sql( $res) . " )";
        }
        if($wpjobportal_searchajax == null){
            $wpjobportal_jobstatus_a = isset(wpjobportal::$_search['job']['jobstatus']) ? wpjobportal::$_search['job']['jobstatus'] : '';
        }else{
            $wpjobportal_jobstatus_a = isset($wpjobportal_searchajax['jobstatus']) ? $wpjobportal_searchajax['jobstatus'] : '';
        }
        if ($wpjobportal_jobstatus_a) {
            wpjobportal::$_data['filter']['jobstatus'] = $wpjobportal_jobstatus_a;
            $res = $this->makeQueryFromArray('jobstatus', $wpjobportal_jobstatus_a);
            if ($res)
                $wpjobportal_inquery .= " AND ( " . $res . " )";
        }
        if($wpjobportal_searchajax == null){
            $symbol = isset(wpjobportal::$_search['jobs']['currencyid']) ? wpjobportal::$_search['jobs']['currencyid'] : '';
        }else{
            $symbol = isset($wpjobportal_searchajax['currencyid']) ? $wpjobportal_searchajax['currencyid'] : '';
        }
        if ($symbol) {
            if (is_numeric($symbol)) {
                wpjobportal::$_data['filter']['currencyid'] = $symbol;
                $wpjobportal_inquery .= " AND job.currencyid = " . esc_sql($symbol);
            }
        }

        if($wpjobportal_searchajax == null){
            $wpjobportal_salarytype = isset(wpjobportal::$_search['jobs']['salarytype']) ? wpjobportal::$_search['jobs']['salarytype'] : '';
        }else{
            $wpjobportal_salarytype = isset($wpjobportal_searchajax['salarytype']) ? $wpjobportal_searchajax['salarytype'] : '';
        }

        if ($wpjobportal_salarytype) {
            if (is_numeric($wpjobportal_salarytype)) {
                wpjobportal::$_data['filter']['salarytype'] = $wpjobportal_salarytype;
                $wpjobportal_inquery .= " AND job.salarytype = " . esc_sql($wpjobportal_salarytype);
            }
        }


        ///Salary max min
        if($wpjobportal_searchajax == null){
            $wpjobportal_salaryfixed = isset(wpjobportal::$_search['jobs']['salaryfixed']) ? wpjobportal::$_search['jobs']['salaryfixed'] : '';
        }else{
            $wpjobportal_salaryfixed = isset($wpjobportal_searchajax['salaryfixed']) ? $wpjobportal_searchajax['salarytype'] : '';
        }

        if ($wpjobportal_salaryfixed) {
            if (is_numeric($wpjobportal_salaryfixed)) {
                wpjobportal::$_data['filter']['salaryfixed'] = $wpjobportal_salaryfixed;
                if ($wpjobportal_salarytype == 2) {
                    $wpjobportal_inquery .= " AND job.salarymax = " . esc_sql($wpjobportal_salaryfixed);
                }
            }
        }


        if($wpjobportal_searchajax == null){
            $wpjobportal_salaryduration = isset(wpjobportal::$_search['jobs']['salaryduration']) ? wpjobportal::$_search['jobs']['salaryduration'] : '';
        }else{
            $wpjobportal_salaryduration = isset($wpjobportal_searchajax['salaryduration']) ? $wpjobportal_searchajax['salarytype'] : '';
        }

        if ($wpjobportal_salaryduration) {
            if (is_numeric($wpjobportal_salaryduration)) {
                wpjobportal::$_data['filter']['salaryduration'] = $wpjobportal_salaryduration;
                if ($wpjobportal_salarytype == 2 || $wpjobportal_salarytype == 3) {
                    $wpjobportal_inquery .= " AND job.salaryduration = " . esc_sql($wpjobportal_salaryduration);
                }
            }
        }


        if($wpjobportal_searchajax == null){
            $wpjobportal_salarymin = isset(wpjobportal::$_search['jobs']['salarymin']) ? wpjobportal::$_search['jobs']['salarymin'] : '';
        }else{
            $wpjobportal_salarymin = isset($wpjobportal_searchajax['salarymin']) ? $wpjobportal_searchajax['salarytype'] : '';
        }

        if ($wpjobportal_salarymin) {
            if (is_numeric($wpjobportal_salarymin)) {
                wpjobportal::$_data['filter']['salarymin'] = $wpjobportal_salarymin;
                if ($wpjobportal_salarytype == 3) {
                    $wpjobportal_inquery .= " AND job.salarymin >= " . esc_sql($wpjobportal_salarymin);
                }
            }
        }

        if($wpjobportal_searchajax == null){
            $wpjobportal_salarymax = isset(wpjobportal::$_search['jobs']['salarymax']) ? wpjobportal::$_search['jobs']['salarymax'] : '';;
        }else{
            $wpjobportal_salarymax = isset($wpjobportal_searchajax['salarymax']) ? $wpjobportal_searchajax['salarytype'] : '';
        }

        if ($wpjobportal_salarymax) {
            if (is_numeric($wpjobportal_salarymax)) {
                wpjobportal::$_data['filter']['salarymax'] = $wpjobportal_salarymax;
                if ($wpjobportal_salarytype == 3) {
                    $wpjobportal_inquery .= " AND job.salarymax <= " . esc_sql($wpjobportal_salarymax);
                }
            }
        }

        if($wpjobportal_searchajax == null){
            $srangetype = isset(wpjobportal::$_search['jobs']['salaryrangetype']) ? wpjobportal::$_search['jobs']['salaryrangetype'] : '';;
        }else{
            $srangetype = isset($wpjobportal_searchajax['salaryrangetype']) ? $wpjobportal_searchajax['salaryrangetype'] : '';
        }
        if ($srangetype) {
            if (is_numeric($srangetype)) {
                wpjobportal::$_data['filter']['salaryrangetype'] = $srangetype;
                $wpjobportal_inquery .= " AND job.salaryrangetype = " . esc_sql($srangetype);
            }
        }

        if($wpjobportal_searchajax == null){
            $wpjobportal_educationid_a = isset(wpjobportal::$_search['jobs']['educationid']) ? wpjobportal::$_search['jobs']['educationid'] : '';
        }else{
            $wpjobportal_educationid_a = isset($wpjobportal_searchajax['educationid']) ? $wpjobportal_searchajax['educationid'] : '';
        }
        if ($wpjobportal_educationid_a) {
            wpjobportal::$_data['filter']['educationid'] = $wpjobportal_educationid_a;
            $res = $this->makeQueryFromArray('education', $wpjobportal_educationid_a);
            if ($res)
                $wpjobportal_inquery .= " AND ( " . $res . " )";
        }

        if($wpjobportal_searchajax == null){
            $city_a = isset(wpjobportal::$_search['jobs']['city']) ? wpjobportal::$_search['jobs']['city'] : '';
        }else{
            $city_a = isset($wpjobportal_searchajax['city']) ? $wpjobportal_searchajax['city'] : '';
        }

        if ($city_a) {
            wpjobportal::$_data['filter']['city_ids'] = $city_a;
            wpjobportal::$_data['filter']['city'] = wpjobportal::$_common->getCitiesForFilter($city_a);
            $res = $this->makeQueryFromArray('city', $city_a);
            if ($res)
                $wpjobportal_inquery .= " AND ( " . $res . " )";
        }
        if($wpjobportal_searchajax == null){
            if(in_array('tag',wpjobportal::$_active_addons)){
                $wpjobportal_tags_a = isset(wpjobportal::$_search['jobs']['tags']) ? wpjobportal::$_search['jobs']['tags'] : '';
            }
        }else{
            if(in_array('tag',wpjobportal::$_active_addons)){
                $wpjobportal_tags_a = isset($wpjobportal_searchajax['tags']) ? $wpjobportal_searchajax['tags'] : '';
            }
        }
        if(in_array('tag',wpjobportal::$_active_addons)){
            if ($wpjobportal_tags_a) {
                wpjobportal::$_data['filter']['tags'] = $wpjobportal_tags_a;
                $res = $this->makeQueryFromArray('tags', $wpjobportal_tags_a);
                if ($res)
                    $wpjobportal_inquery .= " AND ( " . $res . " )";
            }
        }
        if($wpjobportal_searchajax == null){
            $workpermit_a = WPJOBPORTALrequest::getVar('workpermit', 'post'); // workpermit countries
        }else{
            $workpermit_a = isset($wpjobportal_searchajax['workpermit']) ? $wpjobportal_searchajax['workpermit'] : '';
        }
        if ($workpermit_a) {
            wpjobportal::$_data['filter']['workpermit'] = $workpermit_a;
            $res = $this->makeQueryFromArray('workpermit', $workpermit_a);
            if ($res)
                $wpjobportal_inquery .= " AND ( " . $res . " )";
        }
        if($wpjobportal_searchajax == null){
            $wpjobportal_requiredtravel = WPJOBPORTALrequest::getVar('requiredtravel', 'post');
        }else{
            $wpjobportal_requiredtravel = isset($wpjobportal_searchajax['requiredtravel']) ? $wpjobportal_searchajax['requiredtravel'] : '';
        }
        if ($wpjobportal_requiredtravel) {
            if (is_numeric($wpjobportal_requiredtravel)) {
                wpjobportal::$_data['filter']['requiredtravel'] = $wpjobportal_requiredtravel;
                $wpjobportal_inquery .= " AND job.requiredtravel = " . esc_sql($wpjobportal_requiredtravel);
            }
        }
        if($wpjobportal_searchajax == null){
            $duration = WPJOBPORTALrequest::getVar('duration', 'post');
        }else{
            $duration = isset($wpjobportal_searchajax['duration']) ? $wpjobportal_searchajax['duration'] : '';
        }
        if ($duration) {
            wpjobportal::$_data['filter']['duration'] = $duration;
            $wpjobportal_inquery .= " AND job.duration LIKE '%" . esc_sql($duration) . "%'";
        }
        if($wpjobportal_searchajax == null){
            $zipcode = WPJOBPORTALrequest::getVar('zipcode', 'post');
        }else{
            $zipcode = isset($wpjobportal_searchajax['zipcode']) ? $wpjobportal_searchajax['zipcode'] : '';
        }
        if ($zipcode) {
            wpjobportal::$_data['filter']['zipcode'] = $zipcode;
            $wpjobportal_inquery .= " AND job.zipcode LIKE '%" . esc_sql($zipcode) . "%'";
        }
        //Custom field search
        //start
        $wpjobportal_data = wpjobportal::$_wpjpcustomfield->userFieldsData(2)/*apply_filters('wpjobportal_addons_get_custom_field',false,2)*/;
        $wpjobportal_valarray = array();
        if (!empty($wpjobportal_data)) {
            foreach ($wpjobportal_data as $uf) {
                if($wpjobportal_searchajax == null){
                    //$wpjobportal_valarray[$uf->field] = WPJOBPORTALrequest::getVar($uf->field, 'post');
                    $wpjobportal_valarray[$uf->field] = isset(wpjobportal::$_search['jobs'][$uf->field]) ? wpjobportal::$_search['jobs'][$uf->field] : '';
                }else{
                    $wpjobportal_valarray[$uf->field] = isset($wpjobportal_searchajax[$uf->field]) ? $wpjobportal_searchajax[$uf->field] : '';
                }
                if (isset($wpjobportal_valarray[$uf->field]) && $wpjobportal_valarray[$uf->field] != null) {
                    switch ($uf->userfieldtype) {
                        case 'text':
                        case 'email':
                            $wpjobportal_inquery .= ' AND job.params REGEXP \'"' . esc_sql($uf->field) . '":"[^"]*' . wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_valarray[$uf->field]) . '.*"\' ';
                            break;
                        case 'combo':
                            $wpjobportal_inquery .= ' AND job.params LIKE \'%"' . esc_sql($uf->field) . '":"' . wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_valarray[$uf->field]) . '"%\' ';
                            break;
                        case 'depandant_field':
                            $wpjobportal_inquery .= ' AND job.params LIKE \'%"' . esc_sql($uf->field) . '":"' . wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_valarray[$uf->field]) . '"%\' ';
                            break;
                        case 'radio':
                            $wpjobportal_inquery .= ' AND job.params LIKE \'%"' . esc_sql($uf->field) . '":"' . wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_valarray[$uf->field]) . '"%\' ';
                            break;
                        case 'checkbox':
                            $finalvalue = '';
                            foreach($wpjobportal_valarray[$uf->field] AS $wpjobportal_value){
                                $finalvalue .= $wpjobportal_value.'.*';
                            }
                            $wpjobportal_inquery .= ' AND job.params REGEXP \'"' . esc_sql($uf->field) . '":"[^"]*' . wpjobportalphplib::wpJP_htmlspecialchars($finalvalue) . '.*"\' ';
                            break;
                        case 'date':
                            if (isset($wpjobportal_valarray[$uf->field]) && $wpjobportal_valarray[$uf->field] != '') {
                                $wpjobportal_valarray[$uf->field] = gmdate('Y-m-d H:i:s',strtotime($wpjobportal_valarray[$uf->field]));
                            }
                            $wpjobportal_inquery .= ' AND job.params LIKE \'%"' . esc_sql($uf->field) . '":"' . wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_valarray[$uf->field]) . '"%\' ';
                            break;
                        case 'textarea':
                            $wpjobportal_inquery .= ' AND job.params REGEXP \'"' . esc_sql($uf->field) . '":"[^"]*' . wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_valarray[$uf->field]) . '.*"\' ';
                            break;
                        case 'multiple':
                            $finalvalue = '';
                            foreach($wpjobportal_valarray[$uf->field] AS $wpjobportal_value){
                                $finalvalue .= $wpjobportal_value.'.*';
                            }
                            $wpjobportal_inquery .= ' AND job.params REGEXP \'"' . esc_sql($uf->field) . '":"[^"]*'.wpjobportalphplib::wpJP_htmlspecialchars($finalvalue).'"\'';
                            break;

                    }
                    wpjobportal::$_data['filter']['params'] = $wpjobportal_valarray;
                }
            }
        }

        //end
        if($wpjobportal_searchajax == null){
            $longitude = WPJOBPORTALrequest::getVar('longitude', 'post');
            $latitude = WPJOBPORTALrequest::getVar('latitude', 'post');
            $radius = WPJOBPORTALrequest::getVar('radius', 'post');
            $radius_length_type = WPJOBPORTALrequest::getVar('radiuslengthtype', 'post');
        }else{
            $longitude = isset($wpjobportal_searchajax['longitude']) ? $wpjobportal_searchajax['longitude'] : '';
            $latitude = isset($wpjobportal_searchajax['latitude']) ? $wpjobportal_searchajax['latitude'] : '';
            $radius = isset($wpjobportal_searchajax['radius']) ? $wpjobportal_searchajax['radius'] : '';
            $radius_length_type = isset($wpjobportal_searchajax['radiuslengthtype']) ? $wpjobportal_searchajax['radiuslengthtype'] : '';
        }
        // php 8 issue for wpjobportalphplib::wpJP_str_replace
        if($longitude !=''){
            $longitude = wpjobportalphplib::wpJP_str_replace(',', '', $longitude);
        }
        if($latitude !=''){
            $latitude = wpjobportalphplib::wpJP_str_replace(',', '', $latitude);
        }
        //for radius search
        switch ($radius_length_type) {
            case "1":$radiuslength = 6378137;
                break;
            case "2":$radiuslength = 6378.137;
                break;
            case "3":$radiuslength = 3963.191;
                break;
            case "4":$radiuslength = 3441.596;
                break;
        }
        if ($longitude != '' && $latitude != '' && $radius != '' && $radiuslength != '') {
            wpjobportal::$_data['filter']['longitude'] = $longitude;
            wpjobportal::$_data['filter']['latitude'] = $latitude;
            wpjobportal::$_data['filter']['radius'] = $radius;
            wpjobportal::$_data['filter']['radiuslengthtype'] = $radius_length_type;
            $wpjobportal_inquery .= " AND acos((SIN( PI()* ".esc_sql($latitude)." /180 )*SIN( PI()*job.latitude/180 ))+(cos(PI()* ".esc_sql($latitude)." /180)*COS( PI()*job.latitude/180) *COS(PI()*job.longitude/180-PI()* ".esc_sql($longitude)." /180)))* ".esc_sql($radiuslength)." <= ".esc_sql($radius);
        }
        return $wpjobportal_inquery;
    }

    function getJobs($wpjobportal_vars,$wpjobportal_id='',$only_featured = 0){
        $this->getOrdering();
        $wpjobportal_inquery = '';

        if (isset($wpjobportal_vars['search']) && $wpjobportal_vars['search'] != null) {
            $wpjobportal_array = wpjobportalphplib::wpJP_explode('-', $wpjobportal_vars['search']);
            $wpjobportal_search = $wpjobportal_array[count($wpjobportal_array) - 1];
            $wpjobportal_inquery = $this->getSaveSearchForView($wpjobportal_search);
            wpjobportal::$_data['filter']['search'] = $wpjobportal_search;
        } elseif (empty($wpjobportal_vars)) {
            $wpjobportal_inquery = $this->getRefinedJobs();
        } elseif(isset($wpjobportal_vars['searchajax'])){
            $wpjobportal_inquery = $this->getRefinedJobs($wpjobportal_vars);
        } else {
            if (isset($wpjobportal_vars['company']) && is_numeric($wpjobportal_vars['company'])) { // if action form a <link> defined in cp
                wpjobportal::$_data['filter']['company'] = $wpjobportal_vars['company'];
                $wpjobportal_inquery = " AND job.companyid=" . esc_sql($wpjobportal_vars['company']);
            }
            if (isset($wpjobportal_vars['category']) && is_numeric($wpjobportal_vars['category'])) { // if action form a <link> defined in cp
                wpjobportal::$_data['filter']['category'] = $wpjobportal_vars['category'];
                $wpjp_query = "SELECT id FROM `" . wpjobportal::$_db->prefix . "wj_portal_categories` WHERE parentid = ". esc_sql($wpjobportal_vars['category']);
                $wpjp_cats = wpjobportaldb::get_results($wpjp_query);
                $wpjp_ids = [];
                foreach ($wpjp_cats as $wpjp_cat) {
                    $wpjp_ids[] = $wpjp_cat->id;
                }
                $wpjp_ids[] = $wpjobportal_vars['category'];
                $wpjp_ids = implode(",",$wpjp_ids);
                $wpjobportal_inquery = " AND job.jobcategory IN(".esc_sql($wpjp_ids).")";
            }
            if (isset($wpjobportal_vars['jobtype']) && is_numeric($wpjobportal_vars['jobtype'])) { // if action form a <link> defined in cp
                wpjobportal::$_data['filter']['jobtype'] = $wpjobportal_vars['jobtype'];
                $wpjobportal_inquery = " AND job.jobtype=" . esc_sql($wpjobportal_vars['jobtype']);
            }
            if (isset($wpjobportal_vars['tags']) && (!is_numeric($wpjobportal_vars['tags']))) { // if action form a <link> defined in cp
                wpjobportal::$_data['filter']['tags'] = wpjobportal::$_common->makeFilterdOrEditedTagsToReturn($wpjobportal_vars['tags']);
                wpjobportal::$_data['filter']['fromtaglink'] = $wpjobportal_vars['tags'];
                $wpjobportal_inquery = " AND job.tags LIKE '%" . esc_sql($wpjobportal_vars['tags']) . "%'";
            }

            if (isset($wpjobportal_vars['city']) && is_numeric($wpjobportal_vars['city'])) { // if action form a <link> defined in cp
                wpjobportal::$_data['filter']['city'] = wpjobportal::$_common->getCitiesForFilter($wpjobportal_vars['city']);
                $res = $this->makeQueryFromArray('city', $wpjobportal_vars['city']);
                if ($res){
                    $wpjobportal_inquery = " AND ( " . $res . " )";
                }
            }

        }
        $city = WPJOBPORTALrequest::getVar('city','GET');
        if($city && is_numeric($city)){
            //$wpjobportal_inquery .= " AND city.id = ".esc_sql($city);
            wpjobportal::$_data['filter']['city'] = wpjobportal::$_common->getCitiesForFilter($city);
            $res = $this->makeQueryFromArray('city', $city);
            if ($res){
                $wpjobportal_inquery = " AND ( " . $res . " )";
            }
        }

        $wpjobportal_state = WPJOBPORTALrequest::getVar('state','GET');
        if($wpjobportal_state && is_numeric($wpjobportal_state)){
            $wpjobportal_inquery .= " AND state.id = ".esc_sql($wpjobportal_state);
        }

        $wpjobportal_country = WPJOBPORTALrequest::getVar('country','GET');
        if($wpjobportal_country && is_numeric($wpjobportal_country)){
            $wpjobportal_inquery .= " AND country.id = ".esc_sql($wpjobportal_country);
        }
        //local vars
        $simplejobs = array();

        // featuerd job short code parameter
        $featured_flag = WPJOBPORTALrequest::getVar('show_only_featured_jobs','',0);
        if($featured_flag == 1){
            $wpjobportal_inquery .= ' AND job.isfeaturedjob = 1 AND DATE(job.endfeatureddate) >= CURDATE() ';
        }// featuerd job short code parameter


        if($only_featured == 1){
            $wpjobportal_inquery .= ' AND job.isfeaturedjob = 1 AND DATE(job.endfeatureddate) >= CURDATE() ';
        }


        $dont_prep_data = 0;
        // AI Job search

        $wpjobportal_aijobsearcch = isset(wpjobportal::$_search['jobs']['aijobsearcch']) ? wpjobportal::$_search['jobs']['aijobsearcch'] : '';
        if ($wpjobportal_aijobsearcch != '') {
            do_action('wpjobportal_addons_aijobsearch_query');
            if( !empty(wpjobportal::$_data['ai_job_data_set']) ){
                $dont_prep_data = 1;
            }
        }

        //echo '<pre>';print_r($wpjobportal_vars);echo '</pre>';

        // AI Suggested Jobs
        if (isset($wpjobportal_vars['aisuggestedjobs_resume']) && is_numeric($wpjobportal_vars['aisuggestedjobs_resume'])) { // if action form a <link> defined in cp
            do_action('wpjobportal_addons_aisuggestesjobs_jobs',$wpjobportal_vars['aisuggestedjobs_resume']);
            if( !empty(wpjobportal::$_data['ai_job_data_set']) ){
                $dont_prep_data = 1;
            }
        }


        //shortcode attribute proceesing (filter,ordering,no of jobs)
        // by detafult set these values to 0 to make sure that these sections are visble
        wpjobportal::$_data['shortcode_option_hide_filter'] = 0;
        wpjobportal::$_data['shortcode_option_hide_filter_job_title'] = 0;
        wpjobportal::$_data['shortcode_option_hide_filter_job_location'] = 0;
        wpjobportal::$_data['shortcode_option_hide_company_logo'] = 0;
        wpjobportal::$_data['shortcode_option_hide_company_name'] = 0;

        $attributes_query = $this->processShortcodeAttributes();
        if($attributes_query != ''){
            $wpjobportal_inquery .= $attributes_query;
        }


        $wpjobportal_noofjobs = '';
        if(!empty(wpjobportal::$_data['shortcode_option_no_of_jobs'])){
            $wpjobportal_noofjobs = wpjobportal::$_data['shortcode_option_no_of_jobs'];
        }

        if($dont_prep_data == 0){
            $wpjobportal_curdate = gmdate('Y-m-d');
            //Pagination
            if($wpjobportal_noofjobs == ''){ // if no of jobs not set in shortcode then do the pagiatnion total
                $query = "SELECT COUNT(DISTINCT job.id)
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON company.id = job.companyid
                LEFT JOIN `".wpjobportal::$_db->prefix."wj_portal_jobcities` AS jobcity ON jobcity.jobid = job.id
                LEFT JOIN `".wpjobportal::$_db->prefix."wj_portal_cities` AS city ON city.id = jobcity.cityid
                LEFT JOIN `".wpjobportal::$_db->prefix."wj_portal_states` AS state ON state.countryid = city.countryid
                LEFT JOIN `".wpjobportal::$_db->prefix."wj_portal_countries` AS country ON country.id = city.countryid
                LEFT JOIN `".wpjobportal::$_db->prefix."wj_portal_jobtypes` AS jobtype ON jobtype.id = job.jobtype
                WHERE job.status = 1 AND DATE(job.startpublishing) <= '".$wpjobportal_curdate."' AND DATE(job.stoppublishing) >= '".$wpjobportal_curdate."'";
                $query .= $wpjobportal_inquery;
                $wpjobportal_total = wpjobportaldb::get_var($query);
                wpjobportal::$_data['total'] = $wpjobportal_total;
                wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($wpjobportal_total,'jobs');
            }
            //Data/Data
            $query = "SELECT DISTINCT job.id AS jobid,job.id AS id,job.tags AS jobtags,job.title,job.created,job.city,job.endfeatureddate,job.isfeaturedjob,job.status,job.startpublishing,job.stoppublishing,job.currency,
            CONCAT(job.alias,'-',job.id) AS jobaliasid,job.noofjobs,
            cat.cat_title,company.id AS companyid,company.name AS companyname,company.logofilename, jobtype.title AS jobtypetitle,
            job.params,CONCAT(company.alias,'-',company.id) AS companyaliasid,LOWER(jobtype.title) AS jobtypetit,
            job.salarymax,job.salarymin,job.salarytype,srtype.title AS srangetypetitle,jobtype.color AS jobtypecolor, job.jobapplylink, job.joblink, job.description
            FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
            ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON company.id = job.companyid
            LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON cat.id = job.jobcategory
            LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_salaryrangetypes` AS srtype ON srtype.id = job.salaryduration
            LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON jobtype.id = job.jobtype
            LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobcities` AS jobcity ON jobcity.jobid = job.id
            LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON city.id = jobcity.cityid
            LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_states` AS state ON state.countryid = city.countryid
            LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_countries` AS country ON country.id = city.countryid
            WHERE job.status = 1 AND DATE(job.startpublishing) <= '".$wpjobportal_curdate."' AND DATE(job.stoppublishing) >= '".$wpjobportal_curdate."'";
            $query .= $wpjobportal_inquery;

            // ordering
            $query .= " ORDER BY ".wpjobportal::$_ordering;

            // limit (no of jobs)
            if($wpjobportal_noofjobs !='' && is_numeric($wpjobportal_noofjobs)){
                $query .= " LIMIT " . esc_sql($wpjobportal_noofjobs);
            }else{
                $query .= " LIMIT " . WPJOBPORTALpagination::$_offset . "," . WPJOBPORTALpagination::$_limit;
            }

            $wpjobportal_results = wpjobportaldb::get_results($query);

            $wpjobportal_data = array();
            foreach ($wpjobportal_results AS $d) {
                $d->location = WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($d->city);
                $wpjobportal_data[] = $d;
            }
            wpjobportal::$_data[0] = $wpjobportal_data;
        }// dont prep data
        if(wpjobportal::$wpjobportal_theme_chk == 1){
            wpjobportal::$_data[2] = wpjobportal::$_wpjpfieldordering->getFieldsOrderingforSearch(2);
        }
        wpjobportal::$wpjobportal_data['fields'] = wpjobportal::$_wpjpfieldordering->getFieldsOrderingforForm(2);
        wpjobportal::$_data['config'] = wpjobportal::$_config->getConfigByFor('job');
        return;
    }

    function getIpAddress() {
        //if client use the direct ip
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $wpjobportal_ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $wpjobportal_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $wpjobportal_ip = $_SERVER['REMOTE_ADDR'];
        }
        return $wpjobportal_ip;
    }



    function canAddFeaturedJob($wpjobportal_uid) {
        if (!is_numeric($wpjobportal_uid))
            return false;
        $wpjobportal_credits = WPJOBPORTALincluder::getJSModel('credits')->getMinimumCreditIDByAction('featured_job');
        $availablecredits = WPJOBPORTALincluder::getObjectClass('user')->getAvailableCredits();
        if ($wpjobportal_credits <= $availablecredits) {
            return true;
        } else {
            return false;
        }
    }

   function canAddJob($wpjobportal_uid,$wpjobportal_actionname) {
       if (!is_numeric($wpjobportal_uid))
            return false;
       if(in_array('credits', wpjobportal::$_active_addons)){
            $wpjobportal_credits = apply_filters('wpjobportal_addons_userpackages_module_wise',false,$wpjobportal_uid,$wpjobportal_actionname);//
        return $wpjobportal_credits;
       }else{
        return true;
        }
    }

    function getQuickViewByJobId() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('js_nonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wp-job-portal-nonce') ) {
            die( 'Security check Failed' );
        }
        $wpjobportal_jobid = WPJOBPORTALrequest::getVar('jobid');
        if ($wpjobportal_jobid != null && is_numeric($wpjobportal_jobid)) {
            $query = "SELECT job.title AS jobtitle, company.name AS companyname,job.isfeaturedjob , jobtype.title AS jobtypetitle
                        , salaryrangetype.title AS salaryrangetype,company.id AS companyid, job.currency, category.cat_title AS category, job.startpublishing, jobstatus.title AS jobstatustitle, job.degreetitle, job.city, job.longitude,job.latitude, job.description, job.duration,job.jobapplylink,job.joblink
                        FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                        ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON company.id = job.companyid
                        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS category ON category.id = job.jobcategory
                        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON jobtype.id = job.jobtype
                        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobstatus` AS jobstatus ON jobstatus.id = job.jobstatus
                        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_salaryrangetypes` AS salaryrangetype ON salaryrangetype.id = job.salaryrangetype
                        WHERE job.id = " . esc_sql($wpjobportal_jobid);
            $wpjobportal_job = wpjobportal::$_db->get_row($query);
            $title = esc_html(__('Job Information', 'wp-job-portal'));
            $wpjobportal_content = '<div class="quickviewupper">';
            $wpjobportal_content .= '<span class="quickviewtitle">' . $wpjobportal_job->jobtitle . '</span>';
            $wpjobportal_comp_name = wpjobportal::$_config->getConfigurationByConfigName('comp_name');
            if($wpjobportal_comp_name == 1){
                $wpjobportal_content .= '<span class="quickviewcompanytitle"><a href="'.esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$wpjobportal_job->companyid,'wpjobportalpageid'=>WPJOBPORTALRequest::getVar('wpjobportalpageid')))) . '">' . $wpjobportal_job->companyname;
                $wpjobportal_content .= '</a>';
            }

            if ($wpjobportal_job->isfeaturedjob == 1) {
                $wpjobportal_content .= '<span class="quickviewfeatured">' . esc_html(__('Featured', 'wp-job-portal')) . '</span>';
            }
            $wpjobportal_content .= '</span>';
            $wpjobportal_content .= '<span class="quickviewhalfwidth-right">' . WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($wpjobportal_job->city) . '</span>';
            $hourago = esc_html(__('Posted', 'wp-job-portal')) . ": ";
            $wpjobportal_startTimeStamp = strtotime($wpjobportal_job->startpublishing);
            $wpjobportal_endTimeStamp = strtotime("now");
            $timeDiff = abs($wpjobportal_endTimeStamp - $wpjobportal_startTimeStamp);
            $wpjobportal_numberDays = $timeDiff / 86400;  // 86400 seconds in one day
            // and you might want to convert to integer
            $wpjobportal_numberDays = intval($wpjobportal_numberDays);
            if ($wpjobportal_numberDays != 0 && $wpjobportal_numberDays == 1) {
                $wpjobportal_day_text = esc_html(__('Day', 'wp-job-portal'));
            } elseif ($wpjobportal_numberDays > 1) {
                $wpjobportal_day_text = esc_html(__('Days', 'wp-job-portal'));
            } elseif ($wpjobportal_numberDays == 0) {
                $wpjobportal_day_text = esc_html(__('Today', 'wp-job-portal'));
            }
            if ($wpjobportal_numberDays == 0) {
                $hourago .= $wpjobportal_day_text;
            } else {
                $hourago .= $wpjobportal_numberDays . ' ' . $wpjobportal_day_text . ' ' . esc_html(__('Ago', 'wp-job-portal'));
            }
            $wpjobportal_fieldordering = wpjobportal::$_wpjpfieldordering->getFieldsOrderingforView(2);
            $wpjobportal_content .= '<span class="quickviewhalfwidth">' . $hourago . '</span>';
            $wpjobportal_content .= '</div>';
            $wpjobportal_content .= '<div class="quickviewlower">';
            $wpjobportal_content .= '<span class="quickviewtitle">' . esc_html(__('Overview', 'wp-job-portal')) . '</span>';
            if (isset($wpjobportal_fieldordering['jobtype'])) {
                $wpjobportal_content .= '<div class="quickviewrow">';
                $wpjobportal_content .= '<span class="title">' . wpjobportal::wpjobportal_getVariableValue($wpjobportal_fieldordering['jobtype']) . ':</span>';
                $wpjobportal_content .= wpjobportal::wpjobportal_getVariableValue($wpjobportal_job->jobtypetitle);
                $wpjobportal_content .= '</div>';
            }
            if (isset($wpjobportal_fieldordering['duration'])) {
                $wpjobportal_content .= '<div class="quickviewrow">';
                $wpjobportal_content .= '<span class="title">' . wpjobportal::wpjobportal_getVariableValue($wpjobportal_fieldordering['duration']) . ':</span>';
                $wpjobportal_content .= $wpjobportal_job->duration;
                $wpjobportal_content .= '</div>';
            }
            if (isset($wpjobportal_fieldordering['jobsalaryrange'])) {
                $wpjobportal_content .= '<div class="quickviewrow">';
                $wpjobportal_content .= '<span class="title">' . wpjobportal::wpjobportal_getVariableValue($wpjobportal_fieldordering['jobsalaryrange']) . ':</span>';
                $wpjobportal_content .= wpjobportal::$_common->getSalaryRangeView($wpjobportal_job->currencysymbol, $wpjobportal_job->salaryrangestart, $wpjobportal_job->salaryrangeend, $wpjobportal_job->salaryrangetype);
                $wpjobportal_content .= '</div>';
            }
            if (isset($wpjobportal_fieldordering['jobcategory'])) {
                $wpjobportal_content .= '<div class="quickviewrow">';
                $wpjobportal_content .= '<span class="title">' . wpjobportal::wpjobportal_getVariableValue($wpjobportal_fieldordering['jobcategory']) . ':</span>';
                $wpjobportal_content .= wpjobportal::wpjobportal_getVariableValue($wpjobportal_job->category);
                $wpjobportal_content .= '</div>';
            }
            if (isset($wpjobportal_fieldordering['startpublishing'])) {
                $wpjobportal_content .= '<div class="quickviewrow">';
                $wpjobportal_content .= '<span class="title">' . esc_html(__('Posted', 'wp-job-portal')) . ':</span>';
                $wpjobportal_content .= date_i18n(wpjobportal::$_configuration['date_format'], strtotime($wpjobportal_job->startpublishing));
                $wpjobportal_content .= '</div>';
            }
            $wpjobportal_content .= '<span class="quickviewtitle">' . esc_html(__('Location', 'wp-job-portal')) . '</span>';
            if (isset($wpjobportal_fieldordering['city'])) {
                $wpjobportal_content .= '<div class="quickviewrow">';
                $wpjobportal_content .= WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($wpjobportal_job->city);
                $wpjobportal_content .= '</div>';
            }
            if (isset($wpjobportal_fieldordering['map'])) {
                $wpjobportal_content .= '<div class="quickviewrow-without-border">';
                $wpjobportal_content .= '<div id="map"><div id="map_container1"></div></div>';
                $wpjobportal_content .= '<input type="hidden" name="longitude1" id="longitude1" value="' . $wpjobportal_job->longitude . '"/>';
                $wpjobportal_content .= '<input type="hidden" name="latitude1" id="latitude1" value="' . $wpjobportal_job->latitude . '"/>';
                $wpjobportal_content .= '</div>';
            }
            $wpjobportal_content .= '<span class="quickviewtitle">' . esc_html(__('Description', 'wp-job-portal')) . '</span>';
            if (isset($wpjobportal_fieldordering['description'])) {
                $wpjobportal_content .= '<div class="quickviewrow-without-border1">';
                $wpjobportal_content .= $wpjobportal_job->description;
                $wpjobportal_content .= '</div>';
            }
            $wpjobportal_content .= '<div class="quickviewbutton">';
                $wpjobportal_show_apply_form  = wpjobportal::$_config->getConfigurationByConfigName('quick_apply_for_user');
                if (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
                    $wpjobportal_show_apply_form  = wpjobportal::$_config->getConfigurationByConfigName('quick_apply_for_visitor');
                }
                if($wpjobportal_show_apply_form == 0){ // hide apply now button if quick apply is enabled
                    $wpjobportal_config_array = wpjobportal::$_data[0]['config'] = wpjobportal::$_config->getConfigByFor('jobapply');
                    if($wpjobportal_config_array['showapplybutton'] == 1){
                        if($wpjobportal_job->jobapplylink == 1 && !empty($wpjobportal_job->joblink)){
                            if(!wpjobportalphplib::wpJP_strstr('http',$wpjobportal_job->joblink)){
                                $wpjobportal_joblink = 'http://'.$wpjobportal_job->joblink;
                            }else{
                                $wpjobportal_joblink = $wpjobportal_job->joblink;
                            }
                            $wpjobportal_content .='    <a class="quickviewbutton" id="apply-now-btn" href= "'. esc_url($wpjobportal_joblink).'" target="_blank" >'. esc_html(__('Apply Now','wp-job-portal')).'</a>';
                        }elseif(!empty($wpjobportal_config_array['applybuttonredirecturl'])){
                            if(!wpjobportalphplib::wpJP_strstr('http',$wpjobportal_config_array['applybuttonredirecturl'])){
                                $wpjobportal_joblink = 'http://'.$wpjobportal_config_array['applybuttonredirecturl'];
                            }else{
                                $wpjobportal_joblink = $wpjobportal_config_array['applybuttonredirecturl'];
                            }
                            $wpjobportal_content .='    <a class="quickviewbutton" id="apply-now-btn" href='.esc_url($wpjobportal_joblink).'" target="_blank" >'. esc_html(__('Apply Now','wp-job-portal')).'</a>';
                        }else{
                            $wpjobportal_isguest = WPJOBPORTALincluder::getObjectClass('user')->isguest();
                            if($wpjobportal_isguest){
                                $wpjobportal_visitorcanapply = wpjobportal::$_config->getConfigurationByConfigName('visitor_can_apply_to_job');
                                if($wpjobportal_visitorcanapply == 1){
                                    $wpjobportal_visitor_show_login_message = wpjobportal::$_config->getConfigurationByConfigName('visitor_show_login_message');
                                    if($wpjobportal_visitor_show_login_message == 1){
                                        $wpjobportal_content .='<a class="quickviewbutton" id="apply-now-btn" href="#" onclick="getApplyNowByJobid('.$wpjobportal_jobid.','.esc_js(wpjobportal::wpjobportal_getPageid()).');">'.esc_html(__('Apply Now','wp-job-portal')).'</a>';
                                    }else{
                                        $wpjobportal_vis_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobapply', 'action'=>'wpjobportaltask', 'task'=>'jobapplyasvisitor', 'wpjobportalid-jobid'=>$wpjobportal_jobid, 'wpjobportalpageid'=>esc_js(wpjobportal::wpjobportal_getPageid())));
                                        $wpjobportal_content .='<a class="quickviewbutton" id="apply-now-btn" href="'.esc_url($wpjobportal_vis_link).'">'.esc_html(__('Apply Now','wp-job-portal')).'</a>';
                                    }
                                }
                            }else{
                                if(WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()){
                                    $wpjobportal_content .='<a class="quickviewbutton" id="apply-now-btn" href="#" onclick="wpjobportalPopup(\'job_apply\','.esc_js(wpjobportal::wpjobportal_getPageid()).','.$wpjobportal_jobid.');">'.esc_html(__('Apply Now','wp-job-portal')).'</a>';
                                }else{
                                    $wpjobportal_content .='<a class="quickviewbutton" id="apply-now-btn" href="#" onclick="getApplyNowByJobid('.$wpjobportal_jobid.','.esc_js(wpjobportal::wpjobportal_getPageid()).');">'.esc_html(__('Apply Now','wp-job-portal')).'</a>';
                                }
                            }
                        }
                    }
                }
                $wpjobportal_content .= '<a href="'.wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'viewjob', 'wpjobportalid'=>$wpjobportal_jobid,'wpjobportalpageid'=>esc_js(wpjobportal::wpjobportal_getPageid()))). '" class="quickviewbutton">' . esc_html(__('Full Detail', 'wp-job-portal')) . '</a>
                            <a href="#" class="quickviewbutton" onclick="closePopup();">' . esc_html(__('Close', 'wp-job-portal')) . '</a>
                        </div>';
            $wpjobportal_content .= '</div>';
        } else {
            $title = esc_html(__('No record found', 'wp-job-portal'));
            $wpjobportal_content = '<h1>' . esc_html(__('No record found', 'wp-job-portal')) . '</h1>';
        }
        $wpjobportal_array = array('title' => $title, 'wpjobportal_content' => $wpjobportal_content);
        return wp_json_encode($wpjobportal_array);
    }
    function getDataForShortlistForTemplate() {
        $wpjobportal_jobid = WPJOBPORTALrequest::getVar('jobid');
        $wpjobportal_result = $this->getDataForShortlist($wpjobportal_jobid);
        if($wpjobportal_result == false){
            return false;
        }else{
            return wp_json_encode($wpjobportal_result);
        }
    }
    function getShortListViewByJobIdJobManager(){
        $wpjobportal_jobid = WPJOBPORTALrequest::getVar('jobid');
        if ($wpjobportal_jobid != null && is_numeric($wpjobportal_jobid)) {
            $wpjobportal_result = $this->getDataForShortlist($wpjobportal_jobid);
            $wpjobportal_comment = (isset($wpjobportal_result->comments)) ? $wpjobportal_result->comments : '';
            $wpjobportal_content='<div class="modal-content '.esc_attr($this->class_prefix).'-modal-wrp">
                <div class="'.esc_attr($this->class_prefix).'-modal-header">
                    <a title="close" class="close '.esc_attr($this->class_prefix).'-modal-close-icon-wrap" href="#" onclick="wpjobportalClosePopup(1);" >
                        <img id="popup_cross" alt="popup cross" src="'.esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/popup-close.png">
                    </a>
                    <h3 class="'.esc_attr($this->class_prefix).'-modal-title">'.esc_html(__('Add To ShortList','wp-job-portal')).'</h3>
                </div>
                <div class="col-md-11 col-md-offset-1 '.esc_attr($this->class_prefix).'-modal-data-wrp">
                    <div class="'.esc_attr($this->class_prefix).'-modal-left-image-wrp">
                        <i class="fa fa-heart '.esc_attr($this->class_prefix).'-modal-left-image" aria-hidden="true"></i>
                    </div>
                    <div class="modal-body '.esc_attr($this->class_prefix).'-modal-body">
                        <div class="form '.esc_attr($this->class_prefix).'-modal-form-wrp">
                            <div class="col-md-12 '.esc_attr($this->class_prefix).'-modal-form-row">
                                <div class="form-group">
                                    '.WPJOBPORTALformfield::textarea('wpjobportalcomment', $wpjobportal_comment, array('class' => 'form-control '.esc_attr($this->class_prefix).'-modal-textarea', 'placeholder' => esc_html(__('Comments', 'wp-job-portal')))).'
                                </div>
                            </div>
                            <div class="'.esc_attr($this->class_prefix).'-modal-shortlist-star-wrp">';
                                $wpjobportal_content .= '<label class="rate" for="wpjobportalrating">' . esc_html(__('Rate', 'wp-job-portal')) . '</label>';
                                    $wpjobportal_percent = 0;
                                    if ($wpjobportal_result)
                                        $wpjobportal_percent = $wpjobportal_result->rate * 20;
                                    else
                                        $wpjobportal_percent = 0;
                                    $wpjobportal_stars = '';
                                    $wpjobportal_stars = '-small';
                                    $wpjobportal_content .="
                                        <div class=\"wpjobportal-container" . esc_attr($wpjobportal_stars) . "\">
                                            <span id='shortlist-stars'><ul class=\"wpjobportal-stars" . esc_attr($wpjobportal_stars) . "\" >
                                                <li id=\"rating_" . esc_attr($wpjobportal_jobid) . "\" class=\"current-rating\" style=\"width:" . (int) $wpjobportal_percent . "%;\"></li>
                                                <li><a anchor=\"anchor\" href=\"javascript:void(null)\" onclick=\"javascript:setrating('rating_" . esc_attr($wpjobportal_jobid) . "',1);\" title=\"" . esc_html(__('Very Poor', 'wp-job-portal')) . "\" class=\"one-star\">1</a></li>
                                                <li><a anchor=\"anchor\" href=\"javascript:void(null)\" onclick=\"javascript:setrating('rating_" . esc_attr($wpjobportal_jobid) . "',2);\" title=\"" . esc_html(__('Poor', 'wp-job-portal')) . "\" class=\"two-stars\">2</a></li>
                                                <li><a anchor=\"anchor\" href=\"javascript:void(null)\" onclick=\"javascript:setrating('rating_" . esc_attr($wpjobportal_jobid) . "',3);\" title=\"" . esc_html(__('Regular', 'wp-job-portal')) . "\" class=\"three-stars\">3</a></li>
                                                <li><a anchor=\"anchor\" href=\"javascript:void(null)\" onclick=\"javascript:setrating('rating_" . esc_attr($wpjobportal_jobid) . "',4);\" title=\"" . esc_html(__('Good', 'wp-job-portal')) . "\" class=\"four-stars\">4</a></li>
                                                <li><a anchor=\"anchor\" href=\"javascript:void(null)\" onclick=\"javascript:setrating('rating_" . esc_attr($wpjobportal_jobid) . "',5);\" title=\"" . esc_html(__('Very Good', 'wp-job-portal')) . "\" class=\"five-stars\">5</a></li>
                                            </ul></span>
                                        </div>";
                                $wpjobportal_content.='</div>
                            <div class="col-md-12 '.esc_attr($this->class_prefix).'-modal-form-btn">
                                <div class="form-group">
                                    <input type="button" class="btn btn-primary btn-lg btn-block '.esc_attr($this->class_prefix).'-modal-form-btn-inpf" value="'.esc_html(__("Add To ShortList",'wp-job-portal')).'" onclick="saveJobShortlist(1);"  />
                                </div>
                            </div>
                            '.WPJOBPORTALformfield::hidden('wpjobportalid', isset($wpjobportal_result->id) ? $wpjobportal_result->id : '').
                            WPJOBPORTALformfield::hidden('jobid', $wpjobportal_jobid).
                            '
                        </div>
                    </div>
                </div>
            </div>';
        }else {
            $title = esc_html(__('No record found', 'wp-job-portal'));
            $wpjobportal_content = '<h1>' . esc_html(__('No record found', 'wp-job-portal')) . '</h1>';
        }
        $wpjobportal_array = array('title' => "", 'wpjobportal_content' => $wpjobportal_content);
        return wp_json_encode($wpjobportal_array);
    }

    function getShortListViewByJobId() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('js_nonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wp-job-portal-nonce') ) {
            die( 'Security check Failed' );
        }
        $wpjobportal_jobid = WPJOBPORTALrequest::getVar('jobid');

        if ($wpjobportal_jobid != null && is_numeric($wpjobportal_jobid)) {
            $wpjobportal_result = $this->getDataForShortlist($wpjobportal_jobid);
            $title = esc_html(__('Short List Job', 'wp-job-portal'));
            $wpjobportal_content = '<div class="commentrow">';
            $wpjobportal_content .= '<label for="wpjobportalcomment">' . esc_html(__('Comments', 'wp-job-portal')) . '</label>';
            $wpjobportal_comment = (isset($wpjobportal_result->comments)) ? $wpjobportal_result->comments : '';
            $wpjobportal_content .= '<textarea id="wpjobportalcomment" name="wpjobportalcomment">' . $wpjobportal_comment . '</textarea>';
            $wpjobportal_content .= '<label class="rate" for="wpjobportalrating">' . esc_html(__('Rate', 'wp-job-portal')) . '</label>';
            $wpjobportal_percent = 0;
            if ($wpjobportal_result)
                $wpjobportal_percent = $wpjobportal_result->rate * 20;
            else
                $wpjobportal_percent = 0;
            $wpjobportal_stars = '';
            $wpjobportal_stars = '-small';
            $wpjobportal_content .="
                        <div class=\"wpjobportal-container" . $wpjobportal_stars . "\">
                            <span id='shortlist-stars'><ul class=\"wpjobportal-stars" . $wpjobportal_stars . "\" >
                                <li id=\"rating_" . $wpjobportal_jobid . "\" class=\"current-rating\" style=\"width:" . (int) $wpjobportal_percent . "%;\"></li>
                                <li><a anchor=\"anchor\" href=\"javascript:void(null)\" onclick=\"javascript:setrating('rating_" . $wpjobportal_jobid . "',1);\" title=\"" . esc_html(__('Very Poor', 'wp-job-portal')) . "\" class=\"one-star\">1</a></li>
                                <li><a anchor=\"anchor\" href=\"javascript:void(null)\" onclick=\"javascript:setrating('rating_" . $wpjobportal_jobid . "',2);\" title=\"" . esc_html(__('Poor', 'wp-job-portal')) . "\" class=\"two-stars\">2</a></li>
                                <li><a anchor=\"anchor\" href=\"javascript:void(null)\" onclick=\"javascript:setrating('rating_" . $wpjobportal_jobid . "',3);\" title=\"" . esc_html(__('Regular', 'wp-job-portal')) . "\" class=\"three-stars\">3</a></li>
                                <li><a anchor=\"anchor\" href=\"javascript:void(null)\" onclick=\"javascript:setrating('rating_" . $wpjobportal_jobid . "',4);\" title=\"" . esc_html(__('Good', 'wp-job-portal')) . "\" class=\"four-stars\">4</a></li>
                                <li><a anchor=\"anchor\" href=\"javascript:void(null)\" onclick=\"javascript:setrating('rating_" . $wpjobportal_jobid . "',5);\" title=\"" . esc_html(__('Very Good', 'wp-job-portal')) . "\" class=\"five-stars\">5</a></li>
                            </ul></span>
                        </div>
                        ";

            $wpjobportal_id = (isset($wpjobportal_result->id)) ? $wpjobportal_result->id : "";
            $wpjobportal_content .= '<input type="hidden" name="wpjobportalid" id="wpjobportalid" value="' . $wpjobportal_id . '">';
            $wpjobportal_content .= '<input type="hidden" name="jobid" id="jobid" value="' . $wpjobportal_jobid . '">';
            $wpjobportal_content .= '<div class="quickviewlower">
                            <div class="quickviewbutton">
                                <a href="#" class="quickviewbutton wpjobportal-save-shortlist-popup-button" id="apply-now-btn" onclick="saveJobShortlist()">' . esc_html(__('Save', 'wp-job-portal')) . '</a>
                                <a href="#" class="quickviewbutton" onclick="closePopup();">' . esc_html(__('Close', 'wp-job-portal')) . '</a>
                            </div>
                        </div>';
            $wpjobportal_content .= '</div>';
        }else {
            $title = esc_html(__('No record found', 'wp-job-portal'));
            $wpjobportal_content = '<h1>' . esc_html(__('No record found', 'wp-job-portal')) . '</h1>';
        }
        $wpjobportal_array = array('title' => $title, 'wpjobportal_content' => $wpjobportal_content);
        return wp_json_encode($wpjobportal_array);
    }


   function custom_wpjobportal_cookie($cookievalue, $cookieindex) {
        $wpjobportal_value = array();
        if (isset($_COOKIE['wp_wpjobportal_cookie'])) {
            $cookie = sanitize_key($_COOKIE['wp_wpjobportal_cookie']);
            $wpjobportal_value = unserialize($cookie);
        }
        $wpjobportal_value[(int) $cookieindex] = (int) $cookievalue;
        wpjobportalphplib::wpJP_setcookie('wp_wpjobportal_cookie', serialize($wpjobportal_value), time() + 1209600, SITECOOKIEPATH, null, false, true);
    }

    function getNextJobs() {
        $wpjobportal_searchcriteria = WPJOBPORTALrequest::getVar('ajaxsearch');
        wpjobportal::$_data['wpjobportal_pageid'] = WPJOBPORTALrequest::getVar('wpjobportal_pageid');
        $wpjobportal_decoded = wpjobportalphplib::wpJP_safe_decoding($wpjobportal_searchcriteria);
        $wpjobportal_array = json_decode($wpjobportal_decoded,true);
        //$wpjobportal_vars = $this->getjobsvar();
        $wpjobportal_array['searchajax'] = 1;
        $this->getJobs($wpjobportal_array);
        $wpjobportal_jobs = WPJOBPORTALincluder::getObjectClass('jobslist');
        $wpjobportal_jobshtml = $wpjobportal_jobs->printjobs(wpjobportal::$_data[0]);
        echo wp_kses($wpjobportal_jobshtml, WPJOBPORTAL_ALLOWED_TAGS);
        exit;
    }

    function getNextTemplateJobs(){
        $wpjobportal_searchcriteria = WPJOBPORTALrequest::getVar('ajaxsearch');
        wpjobportal::$_data['wpjobportal_pageid'] = WPJOBPORTALrequest::getVar('wpjobportal_pageid');

        $wpjobportal_decoded = wpjobportalphplib::wpJP_safe_decoding($wpjobportal_searchcriteria);
        $wpjobportal_array = json_decode($wpjobportal_decoded,true);
        //$wpjobportal_vars = $this->getjobsvar();
        $wpjobportal_array['searchajax'] = 1;
        $this->getJobs($wpjobportal_array);
        $wpjobportal_jobs = WPJOBPORTALincluder::getObjectClass('jobslist');
        $wpjobportal_jobshtml = $wpjobportal_jobs->printtemplatejobs(wpjobportal::$_data[0]);
        echo wp_kses($wpjobportal_jobshtml, WPJOBPORTAL_ALLOWED_TAGS);
        exit;
    }

    function getjobsvar() {
        $wpjobportal_vars = array();
        $wpjobportal_id = WPJOBPORTALrequest::getVar('wpjobportalid');
        if ($wpjobportal_id) {
            //parse id what is the meaning of it
            $wpjobportal_array = wpjobportalphplib::wpJP_explode('_', $wpjobportal_id);

            // this code handles the case where alias for the link enitity (company,category,city etc) contanins  "_"
            if(is_array($wpjobportal_array)){
                $wpjobportal_array_count = count($wpjobportal_array);
                if($wpjobportal_array_count > 2){ // count > 2 means there were other underscores in the urls id section
                    $wpjobportal_array[1] = $wpjobportal_array[($wpjobportal_array_count - 1)];
                }
            }else{// there was a log error for array[0] in the code below
                return $wpjobportal_vars;
            }

            if ($wpjobportal_array[0] == 'tags') {
                unset($wpjobportal_array[0]);
                $wpjobportal_array = implode(' ', $wpjobportal_array);
                $wpjobportal_vars['tags'] = $wpjobportal_array;
            } else {
                if(isset($wpjobportal_array[1])){
                    $wpjobportal_id = $wpjobportal_array[1];
                    // $clue = $wpjobportal_id{0} . $wpjobportal_id{1}; Deprecated syntax

                    $clue = '';
                    if(isset($wpjobportal_id[0])){
                        $clue = $wpjobportal_id[0];
                    }
                    if(isset($wpjobportal_id[1])){
                        $clue .= $wpjobportal_id[1];
                    }

                    $wpjobportal_id = wpjobportalphplib::wpJP_substr($wpjobportal_id, 2);
                    switch ($clue) {
                        case '10':
                            $wpjobportal_vars['category'] = $wpjobportal_id;
                            break;
                        case '11':
                            $wpjobportal_vars['jobtype'] = $wpjobportal_id;
                            break;
                        case '12':
                            $wpjobportal_vars['company'] = $wpjobportal_id;
                            break;
                        case '13':
                            $wpjobportal_vars['search'] = $wpjobportal_id;
                            break;
                        case '14':
                            $wpjobportal_vars['city'] = $wpjobportal_id;
                            break;
                        case '15':
                            $wpjobportal_vars['aisuggestedjobs_resume'] = $wpjobportal_id;
                            break;
                    }
                }
            }
        } else {
            $wpjobportal_id = WPJOBPORTALrequest::getVar('category', 'get');
            if ($wpjobportal_id) {
                $wpjobportal_vars['category'] = $this->parseid($wpjobportal_id);
            }
            $wpjobportal_id = WPJOBPORTALrequest::getVar('bycompany', 'get');
            if($wpjobportal_id){
                $wpjobportal_vars['bycompany'] = $this->parseid($wpjobportal_id);
            }
            $wpjobportal_id = WPJOBPORTALrequest::getVar('jobtype', 'get');
            if ($wpjobportal_id) {
                $wpjobportal_vars['jobtype'] = $this->parseid($wpjobportal_id);
            }
            $wpjobportal_id = WPJOBPORTALrequest::getVar('company', 'get');
            if ($wpjobportal_id) {
                $wpjobportal_vars['company'] = $this->parseid($wpjobportal_id);
            }
            $wpjobportal_id = WPJOBPORTALrequest::getVar('search', 'get');
            if ($wpjobportal_id) {
                $wpjobportal_vars['search'] = $this->parseid($wpjobportal_id);
            }
            $wpjobportal_id = WPJOBPORTALrequest::getVar('city', 'get');
            if ($wpjobportal_id) {
                $wpjobportal_vars['city'] = $this->parseid($wpjobportal_id);
            }
            $wpjobportal_id = WPJOBPORTALrequest::getVar('aisuggestedjobs_resume', 'get');
            if ($wpjobportal_id) {
                $wpjobportal_vars['aisuggestedjobs_resume'] = $this->parseid($wpjobportal_id);
            }

            if(in_array('tag',wpjobportal::$_active_addons)){
                $wpjobportal_id = WPJOBPORTALrequest::getVar('tags', 'get');
                if ($wpjobportal_id) {
                    $wpjobportal_id = wpjobportal::tagfillout($wpjobportal_id);
                    $wpjobportal_vars['tags'] = $wpjobportal_id;
                }
            }
        }
        return $wpjobportal_vars;
    }

    function parseid($wpjobportal_value) {
        $wpjobportal_arr = wpjobportalphplib::wpJP_explode('-', $wpjobportal_value);
        $wpjobportal_id = $wpjobportal_arr[count($wpjobportal_arr) - 1];
        return $wpjobportal_id;
    }

    function getOrdering() {
        $sort = WPJOBPORTALrequest::getVar('sortby', '', 'posteddesc');
        wpjobportal::$_data['sortby'] = $sort;// to manager sorting on ajax loaded jobs.
        $this->getListOrdering($sort);
        $this->getListSorting($sort);
    }

    function getListOrdering($sort) {
        switch ($sort) {
            case "titledesc":
                wpjobportal::$_ordering = "job.title DESC";
                wpjobportal::$_sorton = "title";
                wpjobportal::$_sortorder = "DESC";
                break;
            case "titleasc":
                wpjobportal::$_ordering = "job.title ASC";
                wpjobportal::$_sorton = "title";
                wpjobportal::$_sortorder = "ASC";
                break;
            case "categorydesc":
                wpjobportal::$_ordering = "cat.cat_title DESC";
                wpjobportal::$_sorton = "category";
                wpjobportal::$_sortorder = "DESC";
                break;
            case "categoryasc":
                wpjobportal::$_ordering = "cat.cat_title ASC";
                wpjobportal::$_sorton = "category";
                wpjobportal::$_sortorder = "ASC";
                break;
            case "jobtypedesc":
                wpjobportal::$_ordering = "jobtype.title DESC";
                wpjobportal::$_sorton = "jobtype";
                wpjobportal::$_sortorder = "DESC";
                break;
            case "jobtypeasc":
                wpjobportal::$_ordering = "jobtype.title ASC";
                wpjobportal::$_sorton = "jobtype";
                wpjobportal::$_sortorder = "ASC";
                break;
            case "jobstatusdesc":
                wpjobportal::$_ordering = "job.status DESC";
                wpjobportal::$_sorton = "jobstatus";
                wpjobportal::$_sortorder = "DESC";
                break;
            case "jobstatusasc":
                wpjobportal::$_ordering = "job.status ASC";
                wpjobportal::$_sorton = "jobstatus";
                wpjobportal::$_sortorder = "ASC";
                break;
            case "companydesc":
                wpjobportal::$_ordering = "company.name DESC";
                wpjobportal::$_sorton = "company";
                wpjobportal::$_sortorder = "DESC";
                break;
            case "companyasc":
                wpjobportal::$_ordering = "company.name ASC";
                wpjobportal::$_sorton = "company";
                wpjobportal::$_sortorder = "ASC";
                break;
            case "salarydesc":
                wpjobportal::$_ordering = "srfrom.rangestart DESC";
                wpjobportal::$_sorton = "salary";
                wpjobportal::$_sortorder = "DESC";
                break;
            case "salaryasc":
                wpjobportal::$_ordering = "srfrom.rangestart ASC";
                wpjobportal::$_sorton = "salary";
                wpjobportal::$_sortorder = "ASC";
                break;
            case "posteddesc":
                wpjobportal::$_ordering = "job.created DESC";
                wpjobportal::$_sorton = "posted";
                wpjobportal::$_sortorder = "DESC";
                break;
            case "postedasc":
                wpjobportal::$_ordering = "job.created ASC";
                wpjobportal::$_sorton = "posted";
                wpjobportal::$_sortorder = "ASC";
                break;
            case "salary":
                wpjobportal::$_ordering = "job.salarymax DESC";
                wpjobportal::$_sorton = "salarymax";
                wpjobportal::$_sortorder = "DESC";
                wpjobportal::$_data['filter']['sortby'] = 'salary';
                break;
            case "newest":
                wpjobportal::$_ordering = "job.created DESC";
                wpjobportal::$_sorton = "created";
                wpjobportal::$_sortorder = "DESC";
                wpjobportal::$_data['filter']['sortby'] = 'newest';
                break;
            case 'newestasc':
                wpjobportal::$_ordering = "job.created ASC";
                wpjobportal::$_sorton = "newest";
                wpjobportal::$_sortorder = "ASC";
                wpjobportal::$_data['filter']['sortby'] = 'newest';
                break;
            case 'newestdesc':
                wpjobportal::$_ordering = "job.created DESC";
                wpjobportal::$_sorton = "newest";
                wpjobportal::$_sortorder = "DESC";
                wpjobportal::$_data['filter']['sortby'] = 'newest';
                break;
            default: wpjobportal::$_ordering = "job.title DESC";
        }
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
        return "iddesc";
    }

    function getListSorting($sort) {
        wpjobportal::$_sortlinks['title'] = $this->getSortArg("title", $sort);
        wpjobportal::$_sortlinks['category'] = $this->getSortArg("category", $sort);
        wpjobportal::$_sortlinks['jobtype'] = $this->getSortArg("jobtype", $sort);
        wpjobportal::$_sortlinks['jobstatus'] = $this->getSortArg("jobstatus", $sort);
        wpjobportal::$_sortlinks['company'] = $this->getSortArg("company", $sort);
        wpjobportal::$_sortlinks['salary'] = $this->getSortArg("salary", $sort);
        wpjobportal::$_sortlinks['posted'] = $this->getSortArg("posted", $sort);
        wpjobportal::$_sortlinks['newest'] = $this->getSortArg("newest", $sort);
        return;
    }

    function makeJobSeo($wpjobportal_job_seo , $wpjobportalid){
        if(empty($wpjobportal_job_seo))
            return '';

        $wpjobportal_common = wpjobportal::$_common;
        $wpjobportal_id = $wpjobportal_common->parseID($wpjobportalid);
        if(! is_numeric($wpjobportal_id)) return '';
        $wpjobportal_result = '';
        $wpjobportal_job_seo = wpjobportalphplib::wpJP_str_replace( ' ', '', $wpjobportal_job_seo);
        $wpjobportal_job_seo = wpjobportalphplib::wpJP_str_replace( '[', '', $wpjobportal_job_seo);
        $wpjobportal_array = wpjobportalphplib::wpJP_explode(']', $wpjobportal_job_seo);

        $wpjobportal_total = count($wpjobportal_array);
        if($wpjobportal_total > 5)
            $wpjobportal_total = 5;

        for ($wpjobportal_i=0; $wpjobportal_i < $wpjobportal_total; $wpjobportal_i++) {
            $query = '';
            switch ($wpjobportal_array[$wpjobportal_i]) {
                case 'title':
                    $query = "SELECT title AS col FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` WHERE id = " . esc_sql($wpjobportal_id);
                break;
                case 'category':
                    $query = "SELECT category.cat_title AS col
                        FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS category ON category.id = job.jobcategory
                        WHERE job.id = " . esc_sql($wpjobportal_id);
                break;
                case 'company':
                    $query = "SELECT company.name AS col
                        FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                        ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON company.id = job.companyid
                        WHERE job.id = " . esc_sql($wpjobportal_id);
                break;
                case 'jobtype':
                    $query = "SELECT jt.title AS col
                        FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jt ON jt.id = job.jobtype
                        WHERE job.id = " . esc_sql($wpjobportal_id);
                break;
                case 'location':
                    $query = "SELECT job.city AS col
                        FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job WHERE job.id = " . esc_sql($wpjobportal_id);
                break;
            }
            if($query != ''){
                $wpjobportal_data = wpjobportaldb::get_row($query);
                if(isset($wpjobportal_data->col)){
                    if($wpjobportal_array[$wpjobportal_i] == 'location'){
                        $cityids = wpjobportalphplib::wpJP_explode(',', $wpjobportal_data->col);
                        $location = '';
                        for ($j=0; $j < count($cityids); $j++) {
                            if(is_numeric($cityids[$j])){
                                $query = "SELECT name FROM `" . wpjobportal::$_db->prefix . "wj_portal_cities` WHERE id = ". esc_sql($cityids[$j]);
                                $cityname = wpjobportaldb::get_row($query);
                                if(isset($cityname->name)){
                                    if($location == '')
                                        $location .= $cityname->name;
                                    else
                                        $location .= ' '.$cityname->name;
                                }
                            }
                        }
                        $location = $wpjobportal_common->removeSpecialCharacter($location);
                        // if url encoded string is different from the orginal string dont add it to url
                        $wpjobportal_val = $location;
                        $test_val = urlencode($wpjobportal_val);
                        if($wpjobportal_val != $test_val){
                            continue;
                        }
                        if($location != ''){
                            if($wpjobportal_result == '')
                                $wpjobportal_result .= wpjobportalphplib::wpJP_str_replace(' ', '-', $location);
                            else
                                $wpjobportal_result .= '-'.wpjobportalphplib::wpJP_str_replace(' ', '-', $location);
                        }
                    }else{
                        $wpjobportal_val = $wpjobportal_common->removeSpecialCharacter($wpjobportal_data->col);
                        // if url encoded string is different from the orginal string dont add it to url
                        $test_val = urlencode($wpjobportal_val);
                        if($wpjobportal_val != $test_val){
                            continue;
                        }

                        if($wpjobportal_result == '')
                            $wpjobportal_result .= wpjobportalphplib::wpJP_str_replace(' ', '-', $wpjobportal_val);
                        else
                            $wpjobportal_result .= '-'.wpjobportalphplib::wpJP_str_replace(' ', '-', $wpjobportal_val);
                    }
                }
            }
            if($wpjobportal_array[$wpjobportal_i] == "sep"){
                $wpjobportal_result .= " - ";
            }
        }
        if($wpjobportal_result != ''){
            $wpjobportal_result = wpjobportalphplib::wpJP_str_replace('_', '-', $wpjobportal_result);
        }
        return $wpjobportal_result;
    }

    function makeJobSeoDocumentTitle($wpjobportal_job_seo , $wpjobportalid){
        if(empty($wpjobportal_job_seo))
            return '';

        $wpjobportal_common = wpjobportal::$_common;
        $wpjobportal_id = $wpjobportal_common->parseID($wpjobportalid);
        if(! is_numeric($wpjobportal_id))
            return '';
        $wpjobportal_result = '';

        $wpjobportal_jobtitle = '';
        $wpjobportal_companyname = '';
        $wpjobportal_jobcategory = '';
        $wpjobportal_jobtype = '';
        $wpjobportal_joblocation = '';


        $query = "SELECT job.title AS jobtitle, company.name AS companyname, category.cat_title AS categorytitle,
                    jobtype.title AS jobtypetitle, job.city as jobcities
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                    ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON company.id = job.companyid
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON jobtype.id = job.jobtype
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS category ON category.id = job.jobcategory
                    WHERE job.id = " . esc_sql($wpjobportal_id);
        $wpjobportal_data = wpjobportaldb::get_row($query);

        if(!empty($wpjobportal_data)){
            $wpjobportal_jobtitle = $wpjobportal_data->jobtitle;
            $wpjobportal_companyname = $wpjobportal_data->companyname;
            $wpjobportal_jobcategory = $wpjobportal_data->categorytitle;
            $wpjobportal_jobtype = $wpjobportal_data->jobtypetitle;

            if($wpjobportal_data->jobcities != ''){
                $cityids = wpjobportalphplib::wpJP_explode(',', $wpjobportal_data->jobcities);
                for ($j=0; $j < count($cityids); $j++) {
                    if(is_numeric($cityids[$j])){
                        $query = "SELECT name FROM `" . wpjobportal::$_db->prefix . "wj_portal_cities` WHERE id = ". esc_sql($cityids[$j]);
                        $cityname = wpjobportaldb::get_row($query);
                        if(isset($cityname->name)){
                            if($wpjobportal_joblocation == '')
                                $wpjobportal_joblocation .= $cityname->name;
                            else
                                $wpjobportal_joblocation .= ', '.$cityname->name;
                        }
                    }
                }
            }
            $wpjobportal_matcharray = array(
                '[title]' => $wpjobportal_jobtitle,
                '[companyname]' => $wpjobportal_companyname,
                '[jobcategory]' => $wpjobportal_jobcategory,
                '[jobtype]' => $wpjobportal_jobtype,
                '[location]' => $wpjobportal_joblocation,
                '[separator]' => '-',
                '[sitename]' => get_bloginfo( 'name', 'display' )
            );
            $wpjobportal_result = $this->replaceMatches($wpjobportal_job_seo,$wpjobportal_matcharray);
        }
        return $wpjobportal_result;
    }

    function replaceMatches($wpjobportal_string, $wpjobportal_matcharray) {
        foreach ($wpjobportal_matcharray AS $find => $replace) {
            $wpjobportal_string = wpjobportalphplib::wpJP_str_replace($find, $replace, $wpjobportal_string);
        }
        return $wpjobportal_string;
    }


    function getIfJobOwner($wpjobportal_jobid) {
       if (!is_numeric($wpjobportal_jobid))
            return false;
        $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        if(!is_numeric($wpjobportal_uid)) return false;
        $query = "SELECT job.id
        FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
        WHERE job.uid = ". esc_sql($wpjobportal_uid)."
        AND job.id =" . esc_sql($wpjobportal_jobid);
        $wpjobportal_result = wpjobportal::$_db->get_var($query);
        if ($wpjobportal_result == null) {
            return false;
        } else {
            return true;
        }
    }
    function getJobUid($wpjobportal_jobid){
        if (!is_numeric($wpjobportal_jobid))
            return false;
        $query = "SELECT job.uid
        FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
        WHERE job.id =" . esc_sql($wpjobportal_jobid);
        $wpjobportal_result = wpjobportal::$_db->get_var($query);
        return $wpjobportal_result;
    }

    function getMessagekey(){
        $wpjobportal_key = 'job';if(wpjobportal::$_common->wpjp_isadmin()){$wpjobportal_key = 'admin_'.$wpjobportal_key;}return $wpjobportal_key;
    }
    // fucntion for app


    function getSerachCriteriaForApp(){

        $wpjobportal_vars = array();

        $wpjobportal_vars['company'] = WPJOBPORTALrequest::getVar('wpjobportalapp_companyid','post');
        $wpjobportal_vars['jobtype'] = WPJOBPORTALrequest::getVar('wpjobportalapp_jobtypeid','post');
        $wpjobportal_vars['category'] = WPJOBPORTALrequest::getVar('wpjobportalapp_categoryid','post');
        $wpjobportal_vars['city'] = WPJOBPORTALrequest::getVar('wpjobportalapp_cityid','post');
        $wpjobportal_vars['carrier'] = WPJOBPORTALrequest::getVar('wpjobportalapp_carrierid','post');
        $wpjobportal_vars['shift'] = WPJOBPORTALrequest::getVar('wpjobportalapp_shiftid','post');
        $wpjobportal_vars['workpermit'] = WPJOBPORTALrequest::getVar('wpjobportalapp_workpermitid','post');
        $wpjobportal_vars['jobstatus'] = WPJOBPORTALrequest::getVar('wpjobportalapp_jobstatus_id','post');
        $wpjobportal_vars['education'] = WPJOBPORTALrequest::getVar('wpjobportalapp_educationid','post');
        $wpjobportal_vars['jobtitle'] = WPJOBPORTALrequest::getVar('wpjobportalapp_jobtitle','post');
        $wpjobportal_vars['duration'] = WPJOBPORTALrequest::getVar('wpjobportalapp_duration','post');
        $wpjobportal_vars['metakeywords'] = WPJOBPORTALrequest::getVar('wpjobportalapp_metakeyword','post');
        $wpjobportal_vars['salaryrangestart'] = WPJOBPORTALrequest::getVar('wpjobportalapp_startrangeid','post');
        $wpjobportal_vars['salaryrangeend'] = WPJOBPORTALrequest::getVar('wpjobportalapp_endrangeid','post');
        $wpjobportal_vars['salaryrangetype'] = WPJOBPORTALrequest::getVar('wpjobportalapp_salary_type_id','post');
        $wpjobportal_vars['currency'] = WPJOBPORTALrequest::getVar('wpjobportalapp_currenyid','post');


        //$wpjobportal_vars['experience'] = WPJOBPORTALrequest::getVar('wpjobportalapp_experienceid','post');
        //$wpjobportal_vars['age'] = WPJOBPORTALrequest::getVar('wpjobportalapp_ageid','post');

        $wpjobportal_vars['gender'] = WPJOBPORTALrequest::getVar('wpjobportalapp_genderid','post');
        //$wpjobportal_vars['radiusid'] = WPJOBPORTALrequest::getVar('wpjobportalapp_radiusid','post');
        // lat lang missing


        $wpjobportal_inquery  = '';
        if (isset($wpjobportal_vars['city']) && is_numeric($wpjobportal_vars['city']) && $wpjobportal_vars['city'] != 0 ) {
            $return_data['filter']['city'] = $wpjobportal_vars['city'];
            $wpjobportal_inquery .= " AND  job.city  LIKE '%".esc_sql($wpjobportal_vars['city'])."%'";
        }
        if (isset($wpjobportal_vars['metakeywords'])) {
            wpjobportal::$_data['filter']['metakeywords'] = $wpjobportal_vars['metakeywords'];
            $wpjobportal_vars['metakeywords'] = json_decode(wpjobportalphplib::wpJP_stripslashes($wpjobportal_vars['metakeywords']),true);
            $res = $this->makeQueryFromArray('metakeywords', $wpjobportal_vars['metakeywords']);
            if ($res)
                $wpjobportal_inquery .= " AND ( " . $res . " )";
        }
        if (isset($wpjobportal_vars['jobtitle'])) {
            wpjobportal::$_data['filter']['jobtitle'] = $wpjobportal_vars['jobtitle'];
            $wpjobportal_inquery .= " AND job.title LIKE '%" . esc_sql($wpjobportal_vars['jobtitle']) . "%'";
        }
        if (isset($wpjobportal_vars['company'])) {
            wpjobportal::$_data['filter']['company'] = $wpjobportal_vars['company'];
            $wpjobportal_vars['company'] = json_decode(wpjobportalphplib::wpJP_stripslashes($wpjobportal_vars['company']),true);
            $res = $this->makeQueryFromArray('company', $wpjobportal_vars['company']);
            if ($res)
                $wpjobportal_inquery .= " AND ( " . $res . " )";
        }

        if (isset($wpjobportal_vars['category'])) {
            wpjobportal::$_data['filter']['category'] = $wpjobportal_vars['category'];
            $wpjobportal_vars['category'] = json_decode(wpjobportalphplib::wpJP_stripslashes($wpjobportal_vars['category']),true);
            $res = $this->makeQueryFromArray('category', $wpjobportal_vars['category']);
            if ($res)
                $wpjobportal_inquery .= " AND ( " . $res . " )";
        }

        if (isset($wpjobportal_vars['jobtype'])) {
            wpjobportal::$_data['filter']['jobtype'] = $wpjobportal_vars['jobtype'];
            $wpjobportal_vars['jobtype'] = json_decode(wpjobportalphplib::wpJP_stripslashes($wpjobportal_vars['jobtype']),true);
            $res = $this->makeQueryFromArray('jobtype', $wpjobportal_vars['jobtype']);
            if ($res)
                $wpjobportal_inquery .= " AND ( " . $res . " )";
        }
        if (isset($wpjobportal_vars['carrier'])) {
            wpjobportal::$_data['filter']['carrier'] = $wpjobportal_vars['carrier'];
            $wpjobportal_vars['carrier'] = json_decode(wpjobportalphplib::wpJP_stripslashes($wpjobportal_vars['carrier']),true);
            $res = $this->makeQueryFromArray('careerlevel', $wpjobportal_vars['carrier']);
            if ($res)
                $wpjobportal_inquery .= " AND ( " . $res . " )";
        }
        if (isset($wpjobportal_vars['gender'])) {
            if (is_numeric($wpjobportal_vars['gender'])) {
                $wpjobportal_inquery .= " AND job.gender = " . esc_sql($wpjobportal_vars['gender']);
                wpjobportal::$_data['filter']['gender'] = $wpjobportal_vars['gender'];
            }
        }
        if (isset($wpjobportal_vars['jobstatus'])) {
            wpjobportal::$_data['filter']['jobstatus'] = $wpjobportal_vars['jobstatus'];
            $wpjobportal_vars['jobstatus'] = json_decode(wpjobportalphplib::wpJP_stripslashes($wpjobportal_vars['jobstatus']),true);
            $res = $this->makeQueryFromArray('jobstatus', $wpjobportal_vars['jobstatus']);
            if ($res)
                $wpjobportal_inquery .= " AND ( " . $res . " )";
        }
        if (isset($wpjobportal_vars['currency'])) {
            if (is_numeric($wpjobportal_vars['currency'])) {
                wpjobportal::$_data['filter']['currency'] = $wpjobportal_vars['currency'];
                $wpjobportal_inquery .= " AND job.currencyid = " . esc_sql($wpjobportal_vars['currency']);
            }
        }
        if (isset($wpjobportal_vars['salaryrangestart'])) {
            if (is_numeric($wpjobportal_vars['salaryrangestart'])) {
                wpjobportal::$_data['filter']['salaryrangestart'] = $wpjobportal_vars['salaryrangestart'];
                $wpjobportal_inquery .= " AND job.salaryrangefrom = " . esc_sql($wpjobportal_vars['salaryrangestart']);
            }
        }
        if (isset($wpjobportal_vars['salaryrangeend'])) {
            if (is_numeric($wpjobportal_vars['salaryrangeend'])) {
                wpjobportal::$_data['filter']['salaryrangeend'] = $wpjobportal_vars['salaryrangeend'];
                $wpjobportal_inquery .= " AND job.salaryrangeto = " . esc_sql($wpjobportal_vars['salaryrangeend']);
            }
        }
        if (isset($wpjobportal_vars['salaryrangetype'])) {
            if (is_numeric($wpjobportal_vars['salaryrangetype'])) {
                wpjobportal::$_data['filter']['srangetype'] = $wpjobportal_vars['salaryrangetype'];
                $wpjobportal_inquery .= " AND job.salaryrangetype = " . esc_sql($wpjobportal_vars['salaryrangetype']);
            }
        }
        if (isset($wpjobportal_vars['shift'])) {
            wpjobportal::$_data['filter']['shift'] = $wpjobportal_vars['shift'];
            $wpjobportal_vars['shift'] = json_decode(wpjobportalphplib::wpJP_stripslashes($wpjobportal_vars['shift']),true);
            $res = $this->makeQueryFromArray('shift', $wpjobportal_vars['shift']);
            if ($res)
                $wpjobportal_inquery .= " AND ( " . $res . " )";
        }
        if (isset($wpjobportal_vars['education'])) {
            wpjobportal::$_data['filter']['education'] = $wpjobportal_vars['education'];
            $wpjobportal_vars['education'] = json_decode(wpjobportalphplib::wpJP_stripslashes($wpjobportal_vars['education']),true);
            $res = $this->makeQueryFromArray('education', $wpjobportal_vars['education']);
            if ($res)
                $wpjobportal_inquery .= " AND ( " . $res . " )";
        }
        if (isset($wpjobportal_vars['city'])) {
            wpjobportal::$_data['filter']['city'] = $wpjobportal_vars['city'];
            $wpjobportal_vars['city'] = json_decode(wpjobportalphplib::wpJP_stripslashes($wpjobportal_vars['city']),true);
            $res = $this->makeQueryFromArray('city', $wpjobportal_vars['city']);
            if ($res)
                $wpjobportal_inquery .= " AND ( " . $res . " )";
        }
        if (isset($wpjobportal_vars['tags'])) {
            wpjobportal::$_data['filter']['tags'] = $wpjobportal_var['tags'];
            $wpjobportal_vars['tags'] = json_decode(wpjobportalphplib::wpJP_stripslashes($wpjobportal_vars['tags']),true);
            $res = $this->makeQueryFromArray('tags', $wpjobportal_vars['tags']);
            if ($res)
                $wpjobportal_inquery .= " AND ( " . $res . " )";

        }
        if (isset($wpjobportal_vars['workpermit'])) {
            wpjobportal::$_data['filter']['workpermit'] = $wpjobportal_vars['workpermit'];
            $wpjobportal_vars['workpermit'] = json_decode(wpjobportalphplib::wpJP_stripslashes($wpjobportal_vars['workpermit']),true);
            $res = $this->makeQueryFromArray('workpermit', $wpjobportal_vars['workpermit']);
            if ($res)
                $wpjobportal_inquery .= " AND ( " . $res . " )";
        }
        if (isset($wpjobportal_vars['requiredtravel'])) {
            if (is_numeric($wpjobportal_vars['requiredtravel'])) {
                wpjobportal::$_data['filter']['requiredtravel'] = $wpjobportal_vars['requiredtravel'];
                $wpjobportal_inquery .= " AND job.requiredtravel = " . esc_sql($wpjobportal_vars['requiredtravel']);
            }
        }
        if (isset($wpjobportal_vars['duration'])) {
            wpjobportal::$_data['filter']['duration'] = $wpjobportal_vars['duration'];
            $wpjobportal_inquery .= " AND job.duration LIKE '%" . esc_sql($wpjobportal_vars['duration']) . "%'";
        }
        if (isset($wpjobportal_vars['wpjobportalapp_idfeatured']) && $wpjobportal_vars['wpjobportalapp_idfeatured'] == 1) {
            wpjobportal::$_data['filter']['wpjobportalapp_idfeatured'] = $wpjobportal_vars['wpjobportalapp_idfeatured'];
            $wpjobportal_inquery .= " AND job.isfeaturedjob = " . esc_sql($wpjobportal_vars['wpjobportalapp_idfeatured'])." AND job.endfeatureddate >= CURDATE() ";
        }
        return $wpjobportal_inquery;
     }

    function getJobListingSortingForApp(){
        $sorton = WPJOBPORTALrequest::getVar('wpjobportalapp_sorton','post',2);
        $sortby = WPJOBPORTALrequest::getVar('wpjobportalapp_sortby','post',2);
        switch ($sorton) {
            case 1: // job title
                $sorting = ' job.title ';
                break;
            case 2: // created
                $sorting = ' job.created ';
                break;
            case 3: // company name
                $sorting = ' company.name ';
                break;
            case 4: // job type
                $sorting = ' jobtype.title ';
                break;
            case 5: // category
                $sorting = ' cat.cat_title ';
                break;
            // case 5: // location
            //     $sorting = ' city.name ';
            //     break;
            // case 7: // status
            //     $sorting = ' job.jobstatus ';
            //     break;
        }
        if ($sortby == 1) {
            $sorting .= ' ASC ';
        } else {
            $sorting .= ' DESC ';
        }
        return $sorting;
    }

    function jobDataStructuredPost($wpjobportal_job_id){
        if(!is_numeric($wpjobportal_job_id))
            return false;
        $query = "SELECT job.*,company.url AS companyurl,company.logofilename,company.city AS compcity,company.isfeaturedcompany,cat.cat_title , company.name as companyname, jobtype.title AS jobtypetitle
                , jobstatus.title AS jobstatustitle";
                if(in_array('departments', wpjobportal::$_active_addons)){
                    $query .= " , department.name AS departmentname";
                }
                $query .= " , salarytype.id AS salarytype,LOWER(jobtype.title) AS jobtypetit,careerlevel.title AS careerleveltitle,salarytype.title AS srangetypetitle,jobtype.color AS jobtypecolor,company.contactemail AS companyemail
        FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
        ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON job.companyid = company.id
        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON job.jobcategory = cat.id
        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON job.jobtype = jobtype.id
        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobstatus` AS jobstatus ON job.jobstatus = jobstatus.id";
            if(in_array('departments', wpjobportal::$_active_addons)){
                $query .= " LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_departments` AS department ON job.departmentid = department.id";
            }
        $query .= " LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_salaryrangetypes` AS salarytype ON job.salarytype = salarytype.id
        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_heighesteducation` AS education ON job.educationid = education.id
        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_careerlevels` AS careerlevel ON careerlevel.id = job.careerlevel
        WHERE  job.id = " . esc_sql($wpjobportal_job_id);
        $wpjobportal_job = wpjobportaldb::get_row($query);
        if(isset($wpjobportal_job->id)){
            $wpjobportal_job->multicity = WPJOBPORTALincluder::getJSModel('wpjobportal')->getMultiCityDataForView($wpjobportal_job_id, 1);
            $wpjobportal_job->salary = wpjobportal::$_common->getSalaryRangeView($wpjobportal_job->salarytype, $wpjobportal_job->salarymin, $wpjobportal_job->salarymax, $wpjobportal_job->srangetypetitle);
            $wpjobportal_job->location = WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($wpjobportal_job->city);
        }
        return $wpjobportal_job;
    }

    // get form submit data for frontend side
    function getFrontSideJobSearchFormData($wpjobportal_search_userfields){
        $wpjobportal_jsjp_search_array = array();
        // $wpjobportal_search_userfields = WPJOBPORTALincluder::getObjectClass('customfields')->userFieldsForSearch(1);
        $wpjobportal_jsjp_search_array['metakeywords'] = WPJOBPORTALrequest::getVar('metakeywords', 'post');
        $wpjobportal_jsjp_search_array['jobtitle'] = WPJOBPORTALrequest::getVar('jobtitle', 'post');
        $wpjobportal_jsjp_search_array['company'] = WPJOBPORTALrequest::getVar('company', 'post');
        $wpjobportal_jsjp_search_array['category'] = WPJOBPORTALrequest::getVar('category', 'post');
        $wpjobportal_jsjp_search_array['jobtype'] = WPJOBPORTALrequest::getVar('jobtype', 'post');
        $wpjobportal_jsjp_search_array['careerlevel'] = WPJOBPORTALrequest::getVar('careerlevel', 'post');
        $wpjobportal_jsjp_search_array['jobstatus'] = WPJOBPORTALrequest::getVar('jobstatus', 'post');
        $wpjobportal_jsjp_search_array['currencyid'] = WPJOBPORTALrequest::getVar('currencyid', 'post');
        $wpjobportal_jsjp_search_array['salarytype'] = WPJOBPORTALrequest::getVar('salarytype', 'post');
        $wpjobportal_jsjp_search_array['city'] = WPJOBPORTALrequest::getVar('city', 'post');
        $wpjobportal_jsjp_search_array['salarymin'] = WPJOBPORTALrequest::getVar('salarymin', 'post');
        $wpjobportal_jsjp_search_array['salarymax'] = WPJOBPORTALrequest::getVar('salarymax', 'post');
        $wpjobportal_jsjp_search_array['salaryfixed'] = WPJOBPORTALrequest::getVar('salaryfixed', 'post');
        $wpjobportal_jsjp_search_array['salaryduration'] = WPJOBPORTALrequest::getVar('salaryduration', 'post');
        $wpjobportal_jsjp_search_array['salaryrangetype'] = WPJOBPORTALrequest::getVar('salaryrangetype', 'post');
        $wpjobportal_jsjp_search_array['educationid'] = WPJOBPORTALrequest::getVar('educationid', 'post');
        $wpjobportal_jsjp_search_array['aijobsearcch'] = WPJOBPORTALrequest::getVar('aijobsearcch', 'post');
        if(in_array('tag', wpjobportal::$_active_addons)){
            $wpjobportal_jsjp_search_array['tags'] = WPJOBPORTALrequest::getVar('tags', 'post');
        }
        $wpjobportal_jsjp_search_array['search_from_jobs'] = 1;
        $wpjobportal_search_userfields = WPJOBPORTALincluder::getObjectClass('customfields')->getSearchUserFieldByFieldFor(2);
        if (!empty($wpjobportal_search_userfields)) {
            foreach ($wpjobportal_search_userfields as $uf) {
                $wpjobportal_jsjp_search_array[$uf->field] = WPJOBPORTALrequest::getVar($uf->field, 'post');
            }
        }
        return $wpjobportal_jsjp_search_array;
    }

    // get form submit data for admin jobs
    function getAdminJobSearchFormData($wpjobportal_search_userfields){
        $wpjobportal_jsjp_search_array = array();
        $wpjobportal_jsjp_search_array['searchtitle'] = WPJOBPORTALrequest::getVar('searchtitle');
        $wpjobportal_jsjp_search_array['searchcompany'] = WPJOBPORTALrequest::getVar('searchcompany');
        $wpjobportal_jsjp_search_array['searchjobcategory'] = WPJOBPORTALrequest::getVar('searchjobcategory');
        $wpjobportal_jsjp_search_array['searchjobtype'] = WPJOBPORTALrequest::getVar('searchjobtype');
        $wpjobportal_jsjp_search_array['status'] = WPJOBPORTALrequest::getVar('status');
        $wpjobportal_jsjp_search_array['featured'] = WPJOBPORTALrequest::getVar('featured');
        $wpjobportal_jsjp_search_array['datestart'] = WPJOBPORTALrequest::getVar('datestart');
        $wpjobportal_jsjp_search_array['dateend'] = WPJOBPORTALrequest::getVar('dateend');
        $wpjobportal_jsjp_search_array['location'] = WPJOBPORTALrequest::getVar('location');
        $wpjobportal_jsjp_search_array['sorton'] = WPJOBPORTALrequest::getVar('sorton' , 'post', 6);
        $wpjobportal_jsjp_search_array['sortby'] = WPJOBPORTALrequest::getVar('sortby' , 'post', 2);
        $wpjobportal_jsjp_search_array['aijobsearcch'] = WPJOBPORTALrequest::getVar('aijobsearcch', 'post');
        $wpjobportal_jsjp_search_array['search_from_jobs'] = 1;
        return $wpjobportal_jsjp_search_array;
    }

    function setSearchVariableForJob($wpjobportal_jsjp_search_array,$wpjobportal_search_userfields){
        if(wpjobportal::$_common->wpjp_isadmin()){
            wpjobportal::$_search['jobs']['searchtitle'] = isset($wpjobportal_jsjp_search_array['searchtitle']) ? $wpjobportal_jsjp_search_array['searchtitle'] : '';
            wpjobportal::$_search['jobs']['searchcompany'] = isset($wpjobportal_jsjp_search_array['searchcompany']) ? $wpjobportal_jsjp_search_array['searchcompany'] : '';
            wpjobportal::$_search['jobs']['searchjobcategory'] = isset($wpjobportal_jsjp_search_array['searchjobcategory']) ? $wpjobportal_jsjp_search_array['searchjobcategory'] : '';
            wpjobportal::$_search['jobs']['searchjobtype'] = isset($wpjobportal_jsjp_search_array['searchjobtype']) ? $wpjobportal_jsjp_search_array['searchjobtype'] : '';
            wpjobportal::$_search['jobs']['status'] = isset($wpjobportal_jsjp_search_array['status']) ? $wpjobportal_jsjp_search_array['status'] : '';
            wpjobportal::$_search['jobs']['featured'] = isset($wpjobportal_jsjp_search_array['featured']) ? $wpjobportal_jsjp_search_array['featured'] : '';
            wpjobportal::$_search['jobs']['datestart'] = isset($wpjobportal_jsjp_search_array['datestart']) ? $wpjobportal_jsjp_search_array['datestart'] : '';
            wpjobportal::$_search['jobs']['dateend'] = isset($wpjobportal_jsjp_search_array['dateend']) ? $wpjobportal_jsjp_search_array['dateend'] : '';
            wpjobportal::$_search['jobs']['location'] = isset($wpjobportal_jsjp_search_array['location']) ? $wpjobportal_jsjp_search_array['location'] : '';
            wpjobportal::$_search['jobs']['sorton'] = isset($wpjobportal_jsjp_search_array['sorton']) ? $wpjobportal_jsjp_search_array['sorton'] : 6;
            wpjobportal::$_search['jobs']['sortby'] = isset($wpjobportal_jsjp_search_array['sortby']) ? $wpjobportal_jsjp_search_array['sortby'] : 2;
            wpjobportal::$_search['jobs']['aijobsearcch'] = isset($wpjobportal_jsjp_search_array['aijobsearcch']) ? $wpjobportal_jsjp_search_array['aijobsearcch'] : null;
        }else{
            wpjobportal::$_search['jobs']['jobtitle'] = isset($wpjobportal_jsjp_search_array['jobtitle']) ? $wpjobportal_jsjp_search_array['jobtitle'] : null;
            wpjobportal::$_search['jobs']['city'] = isset($wpjobportal_jsjp_search_array['city']) ? $wpjobportal_jsjp_search_array['city'] : null;
            wpjobportal::$_search['jobs']['company'] = isset($wpjobportal_jsjp_search_array['company']) ? $wpjobportal_jsjp_search_array['company'] : null;
            wpjobportal::$_search['jobs']['metakeywords'] = isset($wpjobportal_jsjp_search_array['metakeywords']) ? $wpjobportal_jsjp_search_array['metakeywords'] : null;
            wpjobportal::$_search['jobs']['category'] = isset($wpjobportal_jsjp_search_array['category']) ? $wpjobportal_jsjp_search_array['category'] : null;
            wpjobportal::$_search['jobs']['jobtype'] = isset($wpjobportal_jsjp_search_array['jobtype']) ? $wpjobportal_jsjp_search_array['jobtype'] : null;
            wpjobportal::$_search['jobs']['careerlevel'] = isset($wpjobportal_jsjp_search_array['careerlevel']) ? $wpjobportal_jsjp_search_array['careerlevel'] : null;
            wpjobportal::$_search['job']['jobstatus'] = isset($wpjobportal_jsjp_search_array['jobstatus']) ? $wpjobportal_jsjp_search_array['jobstatus'] : null;
            wpjobportal::$_search['jobs']['currencyid'] = isset($wpjobportal_jsjp_search_array['currencyid']) ? $wpjobportal_jsjp_search_array['currencyid'] : null;
            wpjobportal::$_search['jobs']['salarytype'] = isset($wpjobportal_jsjp_search_array['salarytype']) ? $wpjobportal_jsjp_search_array['salarytype'] : null;
            wpjobportal::$_search['jobs']['salaryfixed'] = isset($wpjobportal_jsjp_search_array['salaryfixed']) ? $wpjobportal_jsjp_search_array['salaryfixed'] : null;
            wpjobportal::$_search['jobs']['salaryduration'] = isset($wpjobportal_jsjp_search_array['salaryduration']) ? $wpjobportal_jsjp_search_array['salaryduration'] : null;
            wpjobportal::$_search['jobs']['salarymin'] = isset($wpjobportal_jsjp_search_array['salarymin']) ? $wpjobportal_jsjp_search_array['salarymin'] : null;
            wpjobportal::$_search['jobs']['salarymax'] = isset($wpjobportal_jsjp_search_array['salarymax']) ? $wpjobportal_jsjp_search_array['salarymax'] : null;
            wpjobportal::$_search['jobs']['salaryrangetype'] = isset($wpjobportal_jsjp_search_array['salaryrangetype']) ? $wpjobportal_jsjp_search_array['salaryrangetype'] : null;
            wpjobportal::$_search['jobs']['educationid'] = isset($wpjobportal_jsjp_search_array['educationid']) ? $wpjobportal_jsjp_search_array['educationid'] : null;
            wpjobportal::$_search['jobs']['aijobsearcch'] = isset($wpjobportal_jsjp_search_array['aijobsearcch']) ? $wpjobportal_jsjp_search_array['aijobsearcch'] : null;
            if(in_array('tag', wpjobportal::$_active_addons)){
                wpjobportal::$_search['jobs']['tags'] = isset($wpjobportal_jsjp_search_array['tags']) ? $wpjobportal_jsjp_search_array['tags'] : null;
            }
            $wpjobportal_search_userfields = WPJOBPORTALincluder::getObjectClass('customfields')->getSearchUserFieldByFieldFor(2);
                if (!empty($wpjobportal_search_userfields)) {
                    foreach ($wpjobportal_search_userfields as $uf) {
                        wpjobportal::$_search['jobs'][$uf->field] = isset($wpjobportal_jsjp_search_array[$uf->field]) ? $wpjobportal_jsjp_search_array[$uf->field] : null;
                    }
                }
        }
    }

    function getCookiesSavedSearchDataJob($wpjobportal_search_userfields){
        $wpjobportal_jsjp_search_array = array();
        $wpjp_search_cookie_data = '';
        if(isset($_COOKIE['jsjp_jobportal_search_data'])){
            $wpjp_search_cookie_data = wpjobportal::wpjobportal_sanitizeData($_COOKIE['jsjp_jobportal_search_data']);
            $wpjp_search_cookie_data = json_decode( wpjobportalphplib::wpJP_safe_decoding($wpjp_search_cookie_data) , true );
        }
        if($wpjp_search_cookie_data != '' && isset($wpjp_search_cookie_data['search_from_jobs']) && $wpjp_search_cookie_data['search_from_jobs'] == 1){
            if(wpjobportal::$_common->wpjp_isadmin()){
                // updated the code below to handle log errors in case of field disabled for search
                $wpjobportal_jsjp_search_array['searchtitle'] = isset($wpjp_search_cookie_data['searchtitle']) ? $wpjp_search_cookie_data['searchtitle']: '';
                $wpjobportal_jsjp_search_array['searchcompany'] = isset($wpjp_search_cookie_data['searchcompany']) ? $wpjp_search_cookie_data['searchcompany']: '';
                $wpjobportal_jsjp_search_array['searchjobcategory'] = isset($wpjp_search_cookie_data['searchjobcategory']) ? $wpjp_search_cookie_data['searchjobcategory']: '';
                $wpjobportal_jsjp_search_array['searchjobtype'] = isset($wpjp_search_cookie_data['searchjobtype']) ? $wpjp_search_cookie_data['searchjobtype']: '';
                $wpjobportal_jsjp_search_array['status'] = isset($wpjp_search_cookie_data['status']) ? $wpjp_search_cookie_data['status']: '';
                $wpjobportal_jsjp_search_array['featured'] = isset($wpjp_search_cookie_data['featured']) ? $wpjp_search_cookie_data['featured']: '';
                $wpjobportal_jsjp_search_array['datestart'] = isset($wpjp_search_cookie_data['datestart']) ? $wpjp_search_cookie_data['datestart']: '';
                $wpjobportal_jsjp_search_array['dateend'] = isset($wpjp_search_cookie_data['dateend']) ? $wpjp_search_cookie_data['dateend']: '';
                $wpjobportal_jsjp_search_array['location'] = isset($wpjp_search_cookie_data['location']) ? $wpjp_search_cookie_data['location']: '';
                $wpjobportal_jsjp_search_array['sorton'] = isset($wpjp_search_cookie_data['sorton']) ? $wpjp_search_cookie_data['sorton']: '';
                $wpjobportal_jsjp_search_array['sortby'] = isset($wpjp_search_cookie_data['sortby']) ? $wpjp_search_cookie_data['sortby']: '';
                $wpjobportal_jsjp_search_array['aijobsearcch'] = isset($wpjp_search_cookie_data['aijobsearcch']) ? $wpjp_search_cookie_data['aijobsearcch']: '';
            }else{
                $wpjobportal_jsjp_search_array['metakeywords'] = isset($wpjp_search_cookie_data['metakeywords']) ? $wpjp_search_cookie_data['metakeywords']: '';
                $wpjobportal_jsjp_search_array['jobtitle'] = isset($wpjp_search_cookie_data['jobtitle']) ? $wpjp_search_cookie_data['jobtitle']: '';
                $wpjobportal_jsjp_search_array['company'] = isset($wpjp_search_cookie_data['company']) ? $wpjp_search_cookie_data['company']: '';
                $wpjobportal_jsjp_search_array['category'] = isset($wpjp_search_cookie_data['category']) ? $wpjp_search_cookie_data['category']: '';
                $wpjobportal_jsjp_search_array['jobtype'] = isset($wpjp_search_cookie_data['jobtype']) ? $wpjp_search_cookie_data['jobtype']: '';
                $wpjobportal_jsjp_search_array['careerlevel'] = isset($wpjp_search_cookie_data['careerlevel']) ? $wpjp_search_cookie_data['careerlevel']: '';
                $wpjobportal_jsjp_search_array['jobstatus'] = isset($wpjp_search_cookie_data['jobstatus']) ? $wpjp_search_cookie_data['jobstatus']: '';
                $wpjobportal_jsjp_search_array['currencyid'] = isset($wpjp_search_cookie_data['currencyid']) ? $wpjp_search_cookie_data['currencyid']: '';
                $wpjobportal_jsjp_search_array['salarytype'] = isset($wpjp_search_cookie_data['salarytype']) ? $wpjp_search_cookie_data['salarytype']: '';
                $wpjobportal_jsjp_search_array['city'] = isset($wpjp_search_cookie_data['city']) ? $wpjp_search_cookie_data['city']: '';
                $wpjobportal_jsjp_search_array['salarymin'] = isset($wpjp_search_cookie_data['salarymin']) ? $wpjp_search_cookie_data['salarymin']: '';
                $wpjobportal_jsjp_search_array['salarymax'] = isset($wpjp_search_cookie_data['salarymax']) ? $wpjp_search_cookie_data['salarymax']: '';
                $wpjobportal_jsjp_search_array['salaryfixed'] = isset($wpjp_search_cookie_data['salaryfixed']) ? $wpjp_search_cookie_data['salaryfixed']: '';
                $wpjobportal_jsjp_search_array['salaryduration'] = isset($wpjp_search_cookie_data['salaryduration']) ? $wpjp_search_cookie_data['salaryduration']: '';
                $wpjobportal_jsjp_search_array['salaryrangetype'] = isset($wpjp_search_cookie_data['salaryrangetype']) ? $wpjp_search_cookie_data['salaryrangetype']: '';
                $wpjobportal_jsjp_search_array['educationid'] = isset($wpjp_search_cookie_data['educationid']) ? $wpjp_search_cookie_data['educationid']: '';
                $wpjobportal_jsjp_search_array['aijobsearcch'] = isset($wpjp_search_cookie_data['aijobsearcch']) ? $wpjp_search_cookie_data['aijobsearcch']: '';
                $wpjobportal_jsjp_search_array['tags'] = isset($wpjp_search_cookie_data['tags']) ? $wpjp_search_cookie_data['tags']: '';
		$wpjobportal_search_userfields = WPJOBPORTALincluder::getObjectClass('customfields')->getSearchUserFieldByFieldFor(2);
                if (!empty($wpjobportal_search_userfields)) {
                    foreach ($wpjobportal_search_userfields as $uf) {
                        $wpjobportal_jsjp_search_array[$uf->field] = $wpjp_search_cookie_data[$uf->field];
                    }
                }
            }
        }
        return $wpjobportal_jsjp_search_array;
    }

    function getJobsForPageBuilderWidget($wpjobportal_no_of_jobs){

        $query="SELECT COUNT(job.id)
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job ";
        $query .= " WHERE job.status = 1 AND job.startpublishing <= CURDATE() AND job.stoppublishing > CURDATE() " ;
        $wpjobportal_totaljobs = wpjobportaldb::get_var($query);
        $query = "SELECT DISTINCT job.id AS jobid,job.id AS id,job.tags AS jobtags,job.title,job.created,job.city,job.endfeatureddate,job.isfeaturedjob,job.status,job.startpublishing,job.stoppublishing,job.currency,
                CONCAT(job.alias,'-',job.id) AS jobaliasid,job.noofjobs,
                cat.cat_title,company.id AS companyid,company.name AS companyname,company.logofilename, jobtype.title AS jobtypetitle,
                job.params,CONCAT(company.alias,'-',company.id) AS companyaliasid,LOWER(jobtype.title) AS jobtypetit,
                job.salarymax,job.salarymin,job.salarytype,srtype.title AS srangetypetitle,jobtype.color AS jobtypecolor,job.jobapplylink ,job.joblink
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON company.id = job.companyid
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON cat.id = job.jobcategory
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_salaryrangetypes` AS srtype ON srtype.id = job.salaryduration
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON jobtype.id = job.jobtype
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobcities` AS jobcity ON jobcity.jobid = job.id
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON city.id = jobcity.cityid
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_states` AS state ON state.countryid = city.countryid
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_countries` AS country ON country.id = city.countryid
                WHERE job.status = 1 AND DATE(job.startpublishing) <= CURDATE() AND DATE(job.stoppublishing) >= CURDATE()";
        $query .= "  ORDER BY job.created DESC LIMIT " . esc_sql($wpjobportal_no_of_jobs);
        $wpjobportal_results = wpjobportaldb::get_results($query);
        $latestjobs = array();
        foreach ($wpjobportal_results AS $d) {
            $d->location = WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($d->city);
            $latestjobs[] = $d;
        }
        return $latestjobs;
    }

    function jobDataStructuredPostJSON($wpjobportal_job){
        $wpjobportal_wpdir = wp_upload_dir();
        $wpjobportal_data_directory = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('data_directory');
        $wpjobportal_path = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/employer/comp_' . $wpjobportal_job->companyid . '/logo/' . $wpjobportal_job->logofilename;

        $wpjobportal_job_json = '
        {
          "@context" : "https://schema.org/",
          "@type" : "JobPosting",
          "title" : "'. $wpjobportal_job->title .'",
          "description" : "'. $wpjobportal_job->description .'",
          "identifier": {
            "@type": "PropertyValue",
            "name": "'. $wpjobportal_job->companyname .'",
            "value": "'. $wpjobportal_job->jobid .'"
          },
          "datePosted" : "'. $wpjobportal_job->created .'",
          "validThrough" : "'. $wpjobportal_job->stoppublishing .'",
          "employmentType" : "'. $wpjobportal_job->jobtypetitle .'",
          "hiringOrganization" : {
            "@type" : "Organization",
            "name" : "'. $wpjobportal_job->companyname .'",
            "sameAs" : "https://www.facebook.com",
            "logo" : "'. $wpjobportal_path .'"
          },
          "jobLocation": {
          "@type": "Place",
            "address": [{
            "@type": "PostalAddress",
            "streetAddress": "'. $wpjobportal_job->multicity .'"
            },{
            "@type": "PostalAddress",
            "streetAddress": "'. $wpjobportal_job->multicity .'"
            }]
          },
         "baseSalary": {
            "@type": "MonetaryAmount",
            "currency": "'.$wpjobportal_job->currency.'",
            "value": {
              "@type": "QuantitativeValue",
              "value": "'.$wpjobportal_job->salary.'",
              "unitText": "'.$wpjobportal_job->srangetypetitle.'"
            }
          }
        }
        ';
        return $wpjobportal_job_json;
    }

    // functino for ai addons get jobs by job ids for suggested and ai web search

    function getJobsByJobIds($wpjobportal_job_id_list){
        if($wpjobportal_job_id_list == ''){
            return false;
        }
        $wpjobportal_curdate = gmdate('Y-m-d');
        //Data/Data
        $query = "SELECT DISTINCT job.id AS jobid,job.id AS id,job.tags AS jobtags,job.title,job.created,job.city,job.endfeatureddate,job.isfeaturedjob,job.status,job.startpublishing,job.stoppublishing,job.currency,
        CONCAT(job.alias,'-',job.id) AS jobaliasid,job.noofjobs,
        cat.cat_title,company.id AS companyid,company.name AS companyname,company.logofilename, jobtype.title AS jobtypetitle,
        job.params,CONCAT(company.alias,'-',company.id) AS companyaliasid,LOWER(jobtype.title) AS jobtypetit,
        job.salarymax,job.salarymin,job.salarytype,srtype.title AS srangetypetitle,jobtype.color AS jobtypecolor, job.jobapplylink, job.joblink, job.description
        FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
        ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON company.id = job.companyid
        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON cat.id = job.jobcategory
        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_salaryrangetypes` AS srtype ON srtype.id = job.salaryduration
        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON jobtype.id = job.jobtype
        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobcities` AS jobcity ON jobcity.jobid = job.id
        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON city.id = jobcity.cityid
        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_states` AS state ON state.countryid = city.countryid
        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_countries` AS country ON country.id = city.countryid
        WHERE job.status = 1 AND DATE(job.startpublishing) <= '".$wpjobportal_curdate."' AND DATE(job.stoppublishing) >= '".$wpjobportal_curdate."'";
        $query .= " AND job.id IN (".$wpjobportal_job_id_list.")";

        $wpjobportal_results = wpjobportaldb::get_results($query);

        foreach ($wpjobportal_results AS $d) {
            $d->location = WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($d->city);
            $wpjobportal_data[] = $d;
        }
        return $wpjobportal_results;
    }

    // function to handle ai column on new/edit job
    function prepareAIStringDataForJob($wpjobportal_data){
        if(empty($wpjobportal_data)){
            return;
        }
        if(empty($wpjobportal_data['id'])){
            return;
        }

        $wpjobportal_job_ai_string = '';

        if (!empty($wpjobportal_data['title'])) {
            $wpjobportal_job_ai_string .= wpjobportalphplib::wpJP_trim($wpjobportal_data['title']) . ' ';
        }
        if (!empty($wpjobportal_data['companyid']) && is_numeric($wpjobportal_data['companyid'])) {
            $wpjobportal_company_name = WPJOBPORTALincluder::getJSModel('company')->getCompanynameById($wpjobportal_data['companyid']);
            if($wpjobportal_company_name){ // the above function may return false
                $wpjobportal_job_ai_string .= $wpjobportal_company_name . ' ';
            }
        }

        if (!empty($wpjobportal_data['jobcategory']) && is_numeric($wpjobportal_data['jobcategory'])) {
            $cat_title = WPJOBPORTALincluder::getJSModel('category')->getTitleByCategory($wpjobportal_data['jobcategory']);
            if($cat_title){ // the above function may return false
                $wpjobportal_job_ai_string .= $cat_title . ' ';
            }
        }

        if (!empty($wpjobportal_data['jobtype']) && is_numeric($wpjobportal_data['jobtype'])) {
            $wpjobportal_jobtype_title = WPJOBPORTALincluder::getJSModel('jobtype')->getTitleByid($wpjobportal_data['jobtype']);
            if($wpjobportal_jobtype_title){ // the above function may return false
                $wpjobportal_job_ai_string .= $wpjobportal_jobtype_title . ' ';
            }
        }

        if (!empty($wpjobportal_data['jobstatus']) && is_numeric($wpjobportal_data['jobstatus'])) {
            $wpjobportal_jobstatus_title = WPJOBPORTALincluder::getJSModel('jobstatus')->getTitleByid($wpjobportal_data['jobstatus']);
            if($wpjobportal_jobstatus_title){ // the above function may return false
                $wpjobportal_job_ai_string .= $wpjobportal_jobstatus_title . ' ';
            }
        }

        // handling unpublished fields
        $wpjobportal_salarytype = '';
        if (!empty($wpjobportal_data['salarytype'])) {
            $wpjobportal_salarytype = $wpjobportal_data['salarytype'];
        }
        $wpjobportal_salarymin = '';
        if (!empty($wpjobportal_data['salarymin'])) {
            $wpjobportal_salarymin = $wpjobportal_data['salarymin'];
        }
        $wpjobportal_salarymax = '';
        if (!empty($wpjobportal_data['salarymax'])) {
            $wpjobportal_salarymax = $wpjobportal_data['salarymax'];
        }
        $currency = '';
        if (!empty($wpjobportal_data['currency'])) {
            $currency = $wpjobportal_data['currency'];
        }

        $wpjobportal_salary = wpjobportal::$_common->getSalaryRangeView($wpjobportal_salarytype, $wpjobportal_salarymin, $wpjobportal_salarymax,$currency);
        if($wpjobportal_salary != ''){
            $wpjobportal_job_ai_string .= $wpjobportal_salary.' ';
            if(!empty($wpjobportal_data['salaryduration'])) {
                $wpjobportal_salaryrange_title = WPJOBPORTALincluder::getJSModel('salaryrangetype')->getTitleByid($wpjobportal_data['salaryduration']);
                if($wpjobportal_salaryrange_title){
                    $wpjobportal_job_ai_string .= $wpjobportal_salaryrange_title. ' ';
                }
            }
        }

        if (!empty($wpjobportal_data['careerlevel']) && is_numeric($wpjobportal_data['careerlevel'])) {
            $careerlevel_title = WPJOBPORTALincluder::getJSModel('careerlevel')->getTitleByid($wpjobportal_data['careerlevel']);
            if($careerlevel_title){ // the above function may return false
                $wpjobportal_job_ai_string .= $careerlevel_title . ' ';
            }
        }

        if (!empty($wpjobportal_data['city'])) {
            $location_string = WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($wpjobportal_data['city']);
            if($location_string){ // the above function may return false
                $wpjobportal_job_ai_string .= $location_string . ' ';
            }
        }

        if (!empty($wpjobportal_data['duration'])) {
            $wpjobportal_job_ai_string .= wpjobportalphplib::wpJP_trim($wpjobportal_data['duration']) . ' ';
        }

        if (!empty($wpjobportal_data['experience'])) {
            $wpjobportal_job_ai_string .= wpjobportalphplib::wpJP_trim($wpjobportal_data['experience']) . ' ';
        }


        // handle custom fields
        $custom_fields = WPJOBPORTALincluder::getObjectClass('customfields')->userFieldsData(2);
        // ignore these field types from current case
        $wpjobportal_skip_types = ['file', 'email', 'textarea'];

        $wpjobportal_text_area_field_values = '';

        foreach ($custom_fields as $wpjobportal_single_field) {
            if(!in_array($wpjobportal_single_field->userfieldtype, $wpjobportal_skip_types)){ // check if type agaisnt array
                if (!empty($wpjobportal_data[$wpjobportal_single_field->field])) { // check value exsists
                    if(is_array($wpjobportal_data[$wpjobportal_single_field->field])){ // to handle multi select and check box case
                        $wpjobportal_job_ai_string .= implode(',', $wpjobportal_data[$wpjobportal_single_field->field]) . ' ';
                    }else{
                        $wpjobportal_job_ai_string .= $wpjobportal_data[$wpjobportal_single_field->field] . ' ';
                    }
                }
            }elseif($wpjobportal_single_field->userfieldtype == 'textarea'){ // to handle description case in same loop
                if (!empty($wpjobportal_data[$wpjobportal_single_field->field])) { // check value exsists
                    $wpjobportal_text_area_field_values .= $wpjobportal_data[$wpjobportal_single_field->field] . ' ';
                }
            }
        }

        $wpjobportal_job_ai_string = trim($wpjobportal_job_ai_string); // Clean trailing space

        // SECOND LEVEL FOR DESCRIPTION FIELD

        $wpjobportal_job_ai_desc_string = $wpjobportal_job_ai_string;

        if (!empty($wpjobportal_data['description'])) {
            $wpjobportal_job_ai_desc_string .= wpjobportalphplib::wpJP_trim($wpjobportal_data['description']) . ' ';
        }

        if (!empty($wpjobportal_data['tags'])) {
            $wpjobportal_job_ai_desc_string .= wpjobportalphplib::wpJP_trim($wpjobportal_data['tags']) . ' ';
        }

        if (!empty($wpjobportal_data['metakeywords'])) {
            $wpjobportal_job_ai_desc_string .= wpjobportalphplib::wpJP_trim($wpjobportal_data['metakeywords']) . ' ';
        }

        $wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
        if (!empty($wpjobportal_data['startpublishing'])) {
            $wpjobportal_start_date = date_i18n($wpjobportal_dateformat, strtotime($wpjobportal_data['startpublishing']));
            $wpjobportal_job_ai_desc_string .= $wpjobportal_start_date . ' ';
        }

        $wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
        if (!empty($wpjobportal_data['stoppublishing'])) {
            $wpjobportal_stop_date = date_i18n($wpjobportal_dateformat, strtotime($wpjobportal_data['stoppublishing']));
            $wpjobportal_job_ai_desc_string .= $wpjobportal_stop_date . ' ';
        }


        if (!empty($wpjobportal_data['metadescription'])) {
            $wpjobportal_job_ai_desc_string .= wpjobportalphplib::wpJP_trim($wpjobportal_data['metadescription']) . ' ';
        }

        if (!empty($wpjobportal_text_area_field_values)) { // text area type field values from the above loop
            $wpjobportal_job_ai_desc_string .= wpjobportalphplib::wpJP_trim($wpjobportal_text_area_field_values) . ' ';
        }

        // echo '</pre>';print_r($wpjobportal_job_ai_string);echo '</pre>';
        // echo '================================================================================================================';
        // echo '</pre>';print_r($wpjobportal_job_ai_desc_string);echo '</pre>';

        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('job');
        if ($wpjobportal_row->update(array('id'=>$wpjobportal_data['id'], 'aijobsearchtext' => $wpjobportal_job_ai_string, 'aijobsearchdescription' => $wpjobportal_job_ai_desc_string))) {
            return;
        }
        return;
    }

    // function to handle ai columns for jobs in case of importing exsistin data
    // this fucntion enusres to handle bulk cases efficecntly
    function importAIStringDataForJobs($wpjobportal_data){
        if(empty($wpjobportal_data)){
            return;
        }
        if(empty($wpjobportal_data['id'])){
            return;
        }

        $wpjobportal_job_ai_string = '';

        if (!empty($wpjobportal_data['title'])) {
            $wpjobportal_job_ai_string .= wpjobportalphplib::wpJP_trim($wpjobportal_data['title']) . ' ';
        }

        // to keep record of entity titles insted of fetching from db everytime
        if(!isset(wpjobportal::$_data['ai'])){
            wpjobportal::$_data['ai'] = array();
        }
        if(!isset(wpjobportal::$_data['ai']['companies'])){
            wpjobportal::$_data['ai']['companies'] = array();
        }
        if (!empty($wpjobportal_data['companyid']) && is_numeric($wpjobportal_data['companyid'])) {
            if (!isset(wpjobportal::$_data['ai']['companies'][$wpjobportal_data['companyid']])) {
                $wpjobportal_company_name = WPJOBPORTALincluder::getJSModel('company')->getCompanynameById($wpjobportal_data['companyid']);
                wpjobportal::$_data['ai']['companies'][$wpjobportal_data['companyid']] = $wpjobportal_company_name;
            }else{
                $wpjobportal_company_name = wpjobportal::$_data['ai']['companies'][$wpjobportal_data['companyid']];
            }
            if($wpjobportal_company_name){ // the above function may return false
                $wpjobportal_job_ai_string .= $wpjobportal_company_name . ' ';
            }
        }

        if(!isset(wpjobportal::$_data['ai']['categories'])){
            wpjobportal::$_data['ai']['categories'] = array();
        }
        if (!empty($wpjobportal_data['jobcategory']) && is_numeric($wpjobportal_data['jobcategory'])) {
            if (!isset(wpjobportal::$_data['ai']['categories'][$wpjobportal_data['jobcategory']])) {
                $cat_title = WPJOBPORTALincluder::getJSModel('category')->getTitleByCategory($wpjobportal_data['jobcategory']);
                wpjobportal::$_data['ai']['categories'][$wpjobportal_data['jobcategory']] = $cat_title;
            } else {
                $cat_title = wpjobportal::$_data['ai']['categories'][$wpjobportal_data['jobcategory']];
            }

            if ($cat_title) {
                $wpjobportal_job_ai_string .= $cat_title . ' ';
            }
        }

        if(!isset(wpjobportal::$_data['ai']['jobtypes'])){
            wpjobportal::$_data['ai']['jobtypes'] = array();
        }
        if (!empty($wpjobportal_data['jobtype']) && is_numeric($wpjobportal_data['jobtype'])) {
            if (!isset(wpjobportal::$_data['ai']['jobtypes'][$wpjobportal_data['jobtype']])) {
                $wpjobportal_jobtype_title = WPJOBPORTALincluder::getJSModel('jobtype')->getTitleByid($wpjobportal_data['jobtype']);
                wpjobportal::$_data['ai']['jobtypes'][$wpjobportal_data['jobtype']] = $wpjobportal_jobtype_title;
            } else {
                $wpjobportal_jobtype_title = wpjobportal::$_data['ai']['jobtypes'][$wpjobportal_data['jobtype']];
            }

            if ($wpjobportal_jobtype_title) {
                $wpjobportal_job_ai_string .= $wpjobportal_jobtype_title . ' ';
            }
        }

        if(!isset(wpjobportal::$_data['ai']['jobstatuses'])){
            wpjobportal::$_data['ai']['jobstatuses'] = array();
        }

        if (!empty($wpjobportal_data['jobstatus']) && is_numeric($wpjobportal_data['jobstatus'])) {
            if (!isset(wpjobportal::$_data['ai']['jobstatuses'][$wpjobportal_data['jobstatus']])) {
                $wpjobportal_jobstatus_title = WPJOBPORTALincluder::getJSModel('jobstatus')->getTitleByid($wpjobportal_data['jobstatus']);
                wpjobportal::$_data['ai']['jobstatuses'][$wpjobportal_data['jobstatus']] = $wpjobportal_jobstatus_title;
            } else {
                $wpjobportal_jobstatus_title = wpjobportal::$_data['ai']['jobstatuses'][$wpjobportal_data['jobstatus']];
            }

            if ($wpjobportal_jobstatus_title) {
                $wpjobportal_job_ai_string .= $wpjobportal_jobstatus_title . ' ';
            }
        }

        $wpjobportal_salary = wpjobportal::$_common->getSalaryRangeView($wpjobportal_data['salarytype'], $wpjobportal_data['salarymin'], $wpjobportal_data['salarymax'],$wpjobportal_data['currency']);
        if(!isset(wpjobportal::$_data['ai']['salaryranges'])){
            wpjobportal::$_data['ai']['salaryranges'] = array();
        }
        if($wpjobportal_salary != ''){
            $wpjobportal_job_ai_string .= $wpjobportal_salary.' ';
            if(!empty($wpjobportal_data['salaryduration'])) {
                if (!isset(wpjobportal::$_data['ai']['salaryranges'][$wpjobportal_data['salaryduration']])) {
                    $wpjobportal_salaryrange_title = WPJOBPORTALincluder::getJSModel('salaryrangetype')->getTitleByid($wpjobportal_data['salaryduration']);
                    wpjobportal::$_data['ai']['salaryranges'][$wpjobportal_data['salaryduration']] = $wpjobportal_salaryrange_title;
                } else {
                    $wpjobportal_salaryrange_title = wpjobportal::$_data['ai']['salaryranges'][$wpjobportal_data['salaryduration']];
                }
                if($wpjobportal_salaryrange_title){
                    $wpjobportal_job_ai_string .= $wpjobportal_salaryrange_title. ' ';
                }
            }
        }

        if(!isset(wpjobportal::$_data['ai']['careerlevels'])){
            wpjobportal::$_data['ai']['careerlevels'] = array();
        }

        if (!empty($wpjobportal_data['careerlevel']) && is_numeric($wpjobportal_data['careerlevel'])) {
            if (!isset(wpjobportal::$_data['ai']['careerlevels'][$wpjobportal_data['careerlevel']])) {
                $careerlevel_title = WPJOBPORTALincluder::getJSModel('careerlevel')->getTitleByid($wpjobportal_data['careerlevel']);
                wpjobportal::$_data['ai']['careerlevels'][$wpjobportal_data['careerlevel']] = $careerlevel_title;
            } else {
                $careerlevel_title = wpjobportal::$_data['ai']['careerlevels'][$wpjobportal_data['careerlevel']];
            }

            if ($careerlevel_title) {
                $wpjobportal_job_ai_string .= $careerlevel_title . ' ';
            }
        }

        if(!isset(wpjobportal::$_data['ai']['locations'])){
            wpjobportal::$_data['ai']['locations'] = array();
        }

        if (!empty($wpjobportal_data['city'])) {
            if (!isset(wpjobportal::$_data['ai']['locations'][$wpjobportal_data['city']])) {
                $location_string = WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($wpjobportal_data['city']);
                wpjobportal::$_data['ai']['locations'][$wpjobportal_data['city']] = $location_string;
            } else {
                $location_string = wpjobportal::$_data['ai']['locations'][$wpjobportal_data['city']];
            }

            $location_string = WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($wpjobportal_data['city']);
            if($location_string){ // the above function may return false
                $wpjobportal_job_ai_string .= $location_string . ' ';
            }
        }

        if (!empty($wpjobportal_data['duration'])) {
            $wpjobportal_job_ai_string .= wpjobportalphplib::wpJP_trim($wpjobportal_data['duration']) . ' ';
        }

        if (!empty($wpjobportal_data['experience'])) {
            $wpjobportal_job_ai_string .= wpjobportalphplib::wpJP_trim($wpjobportal_data['experience']) . ' ';
        }

        // handle custom fields
        if(!isset(wpjobportal::$_data['ai']['customfields_user_2'])){
            wpjobportal::$_data['ai']['customfields_user_2'] = WPJOBPORTALincluder::getObjectClass('customfields')->userFieldsData(2);
        }

        $custom_fields = wpjobportal::$_data['ai']['customfields_user_2'];
        //$custom_fields = WPJOBPORTALincluder::getObjectClass('customfields')->userFieldsData(2);
        // ignore these field types from current case
        $wpjobportal_skip_types = ['file', 'email', 'textarea'];

        $wpjobportal_text_area_field_values = '';

        foreach ($custom_fields as $wpjobportal_single_field) {
            if(!in_array($wpjobportal_single_field->userfieldtype, $wpjobportal_skip_types)){ // check if type agaisnt array
                if (!empty($wpjobportal_data[$wpjobportal_single_field->field])) { // check value exsists
                    if(is_array($wpjobportal_data[$wpjobportal_single_field->field])){ // to handle multi select and check box case
                        $wpjobportal_job_ai_string .= implode(',', $wpjobportal_data[$wpjobportal_single_field->field]) . ' ';
                    }else{
                        $wpjobportal_job_ai_string .= $wpjobportal_data[$wpjobportal_single_field->field] . ' ';
                    }
                }
            }elseif($wpjobportal_single_field->userfieldtype == 'textarea'){ // to handle text area field for description case in same loop
                if (!empty($wpjobportal_data[$wpjobportal_single_field->field])) { // check value exsists
                    $wpjobportal_text_area_field_values .= $wpjobportal_data[$wpjobportal_single_field->field] . ' ';
                }
            }
        }

        $wpjobportal_job_ai_string = trim($wpjobportal_job_ai_string); // Clean trailing space

        // SECOND LEVEL FOR DESCRIPTION FIELD

        $wpjobportal_job_ai_desc_string = $wpjobportal_job_ai_string;

        if (!empty($wpjobportal_data['description'])) {
            $wpjobportal_job_ai_desc_string .= wpjobportalphplib::wpJP_trim($wpjobportal_data['description']) . ' ';
        }

        if (!empty($wpjobportal_data['tags'])) {
            $wpjobportal_job_ai_desc_string .= wpjobportalphplib::wpJP_trim($wpjobportal_data['tags']) . ' ';
        }

        if (!empty($wpjobportal_data['metakeywords'])) {
            $wpjobportal_job_ai_desc_string .= wpjobportalphplib::wpJP_trim($wpjobportal_data['metakeywords']) . ' ';
        }

        $wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
        if (!empty($wpjobportal_data['startpublishing'])) {
            $wpjobportal_start_date = date_i18n($wpjobportal_dateformat, strtotime($wpjobportal_data['startpublishing']));
            $wpjobportal_job_ai_desc_string .= $wpjobportal_start_date . ' ';
        }

        $wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
        if (!empty($wpjobportal_data['stoppublishing'])) {
            $wpjobportal_stop_date = date_i18n($wpjobportal_dateformat, strtotime($wpjobportal_data['stoppublishing']));
            $wpjobportal_job_ai_desc_string .= $wpjobportal_stop_date . ' ';
        }


        if (!empty($wpjobportal_data['metadescription'])) {
            $wpjobportal_job_ai_desc_string .= wpjobportalphplib::wpJP_trim($wpjobportal_data['metadescription']) . ' ';
        }

        if (!empty($wpjobportal_text_area_field_values)) { // text area type field values from the above loop
            $wpjobportal_job_ai_desc_string .= wpjobportalphplib::wpJP_trim($wpjobportal_text_area_field_values) . ' ';
        }

        // echo '</pre>';print_r($wpjobportal_job_ai_string);echo '</pre>';
        // echo '================================================================================================================';
        // echo '</pre>';print_r($wpjobportal_job_ai_desc_string);echo '</pre>';

        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('job');
        if ($wpjobportal_row->update(array('id'=>$wpjobportal_data['id'], 'aijobsearchtext' => $wpjobportal_job_ai_string, 'aijobsearchdescription' => $wpjobportal_job_ai_desc_string))) {
            return;
        }

        return;
    }

    function updateRecordsForAISearchJob(){
        $query = " SELECT job.*
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                ORDER BY job.id ASC ";
        $wpjobportal_jobs = wpjobportaldb::get_results($query); // fetch all jobs

        foreach ($wpjobportal_jobs as $wpjobportal_job) { // loop over all jobs
            $wpjobportal_job_arrray = json_decode(json_encode($wpjobportal_job),true); // convert std class object to array
            WPJOBPORTALincluder::getJSModel('job')->importAIStringDataForJobs($wpjobportal_job_arrray);
        }
        return;
    }

    function getNumberOfJobsByCompany($company_id){
        if (is_numeric($company_id) == false) return false;
        $query = " SELECT count(job.id) AS total
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                WHERE job.companyid = ".$company_id." AND job.status = 1 AND DATE(job.stoppublishing) >= CURDATE() ";
        $wpjobportal_jobs = wpjobportaldb::get_var($query); // fetch all jobs
        return $wpjobportal_jobs;
    }

}
?>
