<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALwpjobportalModel {

    function getCPJobs() {
        $query = "SELECT comp.name,comp.logofilename,cat.cat_title ,job.city
            FROM " . wpjobportal::$_db->prefix . "wj_portal_jobs as job
            ".wpjobportal::$_company_job_table_join." JOIN " . wpjobportal::$_db->prefix . "wj_portal_companies as comp on comp.id = job.companyid
            LEFT JOIN " . wpjobportal::$_db->prefix . "wj_portal_categories as cat on cat.id = job.jobcategory";
        wpjobportal::$_data[0]['jobs'] = wpjobportaldb::get_results($query);
    }

    function getAdminControlPanelData() {
        $wpjobportal_curdate = date_i18n('Y-m-d');
        //AND date(created) = '".esc_sql($wpjobportal_curdate)."'
        $query = "SELECT jobtype.title,(SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` WHERE jobtype = jobtype.id ) AS totaljob FROM  `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ORDER BY jobtype.id";
        $priorities = wpjobportal::$_db->get_results($query);
        wpjobportal::$_data['today_ticket_chart']['title'] = "['".esc_html(__('Today Jobs','wp-job-portal'))."',";
        wpjobportal::$_data['today_ticket_chart']['data'] = "['',";
        foreach($priorities AS $pr){
            wpjobportal::$_data['today_ticket_chart']['title'] .= "'".wpjobportalphplib::wpJP_htmlspecialchars(wpjobportal::wpjobportal_getVariableValue($pr->title))."',";
            wpjobportal::$_data['today_ticket_chart']['data'] .= $pr->totaljob.",";
        }
        wpjobportal::$_data['today_ticket_chart']['title'] .= "]";
        wpjobportal::$_data['today_ticket_chart']['data'] .= "]";



        wpjobportal::$_data[0]['today_stats'] = WPJOBPORTALincluder::getJSModel('wpjobportal')->getTodayStats();

        // Data for the control panel graph
        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs`";
        wpjobportal::$_data['totaljobs'] = wpjobportal::$_db->get_var($query);
        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies`";
        wpjobportal::$_data['totalcompanies'] = wpjobportal::$_db->get_var($query);

        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` WHERE status = 1";
        wpjobportal::$_data['totalapcompanies'] = wpjobportal::$_db->get_var($query);


        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` WHERE quick_apply <> 1 ";
        wpjobportal::$_data['totalresume'] = wpjobportal::$_db->get_var($query);

        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` WHERE status = 1 AND quick_apply <> 1 ";
        wpjobportal::$_data['totalapresume'] = wpjobportal::$_db->get_var($query);


        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply`";
        wpjobportal::$_data['totaljobapply'] = wpjobportal::$_db->get_var($query);
        $wpjobportal_curdate = gmdate('Y-m-d');
        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` WHERE DATE(startpublishing) <= '".esc_sql($wpjobportal_curdate)."' AND DATE(stoppublishing) >= '".esc_sql($wpjobportal_curdate)."' AND status = 1";
        wpjobportal::$_data['totalactivejobs'] = wpjobportal::$_db->get_var($query);
        $wpjobportal_newindays = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('newdays');
        if ($wpjobportal_newindays == 0) {
            $wpjobportal_newindays = 7;
        }
        $time = strtotime($wpjobportal_curdate . ' -' . $wpjobportal_newindays . ' days');
        $lastdate = gmdate("Y-m-d", $time);
        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` WHERE DATE(created) >= DATE('".esc_sql($lastdate)."') AND DATE(created) <= DATE('".esc_sql($wpjobportal_curdate)."') AND status = 0 ";
        wpjobportal::$_data['totalnewjobspending'] = wpjobportal::$_db->get_var($query);
        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` WHERE DATE(created) >= DATE('".esc_sql($lastdate)."') AND DATE(created) <= DATE('".esc_sql($wpjobportal_curdate)."')";
        wpjobportal::$_data['totalnewcompanies'] = wpjobportal::$_db->get_var($query);
        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` WHERE DATE(created) >= DATE('".esc_sql($lastdate)."') AND DATE(created) <= DATE('".esc_sql($wpjobportal_curdate)."') AND quick_apply <> 1 ";
        wpjobportal::$_data['totalnewresume'] = wpjobportal::$_db->get_var($query);
        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` WHERE DATE(apply_date) >= DATE('".esc_sql($lastdate)."') AND DATE(apply_date) <= DATE('".esc_sql($wpjobportal_curdate)."')";
        wpjobportal::$_data['totalnewjobapply'] = wpjobportal::$_db->get_var($query);

        $wpjobportal_curdate = gmdate('Y-m-d');
        $fromdate = gmdate('Y-m-d', strtotime("now -1 month"));
        wpjobportal::$_data['curdate'] = $wpjobportal_curdate;
        wpjobportal::$_data['fromdate'] = $fromdate;
        $query = "SELECT job.startpublishing AS created
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job WHERE date(job.startpublishing) >= '" . esc_sql($fromdate) . "' AND date(job.startpublishing) <= '" . esc_sql($wpjobportal_curdate) . "' ORDER BY job.startpublishing";
        $wpjobportal_alljobs = wpjobportal::$_db->get_results($query);
        $wpjobportal_jobs = array();
        foreach ($wpjobportal_alljobs AS $wpjobportal_job) {
            $wpjobportal_date = gmdate('Y-m-d', strtotime($wpjobportal_job->created));
            $wpjobportal_jobs[$wpjobportal_date] = isset($wpjobportal_jobs[$wpjobportal_date]) ? ($wpjobportal_jobs[$wpjobportal_date] + 1) : 1;
        }
        $query = "SELECT company.created
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company WHERE date(company.created) >= '" . esc_sql($fromdate) . "' AND date(company.created) <= '" . esc_sql($wpjobportal_curdate) . "' ORDER BY company.created";
        $wpjobportal_allcompanies = wpjobportal::$_db->get_results($query);
        $wpjobportal_companies = array();
        foreach ($wpjobportal_allcompanies AS $wpjobportal_company) {
            $wpjobportal_date = gmdate('Y-m-d', strtotime($wpjobportal_company->created));
            $wpjobportal_companies[$wpjobportal_date] = isset($wpjobportal_companies[$wpjobportal_date]) ? ($wpjobportal_companies[$wpjobportal_date] + 1) : 1;
        }
        $query = "SELECT resume.created
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume WHERE date(resume.created) >= '" . esc_sql($fromdate) . "' AND date(resume.created) <= '" . esc_sql($wpjobportal_curdate) . "' AND resume.quick_apply <> 1   ORDER BY resume.created";
        $wpjobportal_allresume = wpjobportal::$_db->get_results($query);
        $wpjobportal_resumes = array();
        foreach ($wpjobportal_allresume AS $wpjobportal_resume) {
            $wpjobportal_date = gmdate('Y-m-d', strtotime($wpjobportal_resume->created));
            $wpjobportal_resumes[$wpjobportal_date] = isset($wpjobportal_resumes[$wpjobportal_date]) ? ($wpjobportal_resumes[$wpjobportal_date] + 1) : 1;
        }
        $query = "SELECT job.startpublishing AS created
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job WHERE date(job.startpublishing) >= '" . esc_sql($fromdate) . "' AND date(job.startpublishing) <= '" . esc_sql($wpjobportal_curdate) . "' AND job.status = 1 ORDER BY job.created";
        $wpjobportal_allactivejob = wpjobportal::$_db->get_results($query);
        $wpjobportal_activejobs = array();
        foreach ($wpjobportal_allactivejob AS $ajob) {
            $wpjobportal_date = gmdate('Y-m-d', strtotime($ajob->created));
            $wpjobportal_activejobs[$wpjobportal_date] = isset($wpjobportal_activejobs[$wpjobportal_date]) ? ($wpjobportal_activejobs[$wpjobportal_date] + 1) : 1;
        }
        wpjobportal::$_data['stack_chart_horizontal']['title'] = "['" . esc_html(__('Dates', 'wp-job-portal')) . "','" . esc_html(__('Jobs', 'wp-job-portal')) . "','" . esc_html(__('Companies', 'wp-job-portal')) . "','" . esc_html(__('Resume', 'wp-job-portal')) . "','" . esc_html(__('Active Jobs', 'wp-job-portal')) . "']";
        wpjobportal::$_data['stack_chart_horizontal']['data'] = '';
        for ($wpjobportal_i = 29; $wpjobportal_i >= 0; $wpjobportal_i--) {
            $wpjobportal_checkdate = gmdate('Y-m-d', strtotime($wpjobportal_curdate . " -$wpjobportal_i days"));
            if ($wpjobportal_i != 29) {
                wpjobportal::$_data['stack_chart_horizontal']['data'] .= ',';
            }
            wpjobportal::$_data['stack_chart_horizontal']['data'] .= "['" . date_i18n('Y-M-d', strtotime($wpjobportal_checkdate)) . "',";
            $wpjobportal_job = isset($wpjobportal_jobs[$wpjobportal_checkdate]) ? $wpjobportal_jobs[$wpjobportal_checkdate] : 0;
            $wpjobportal_company = isset($wpjobportal_companies[$wpjobportal_checkdate]) ? $wpjobportal_companies[$wpjobportal_checkdate] : 0;
            $wpjobportal_resume = isset($wpjobportal_resumes[$wpjobportal_checkdate]) ? $wpjobportal_resumes[$wpjobportal_checkdate] : 0;
            $ajob = isset($wpjobportal_activejobs[$wpjobportal_checkdate]) ? $wpjobportal_activejobs[$wpjobportal_checkdate] : 0;
            wpjobportal::$_data['stack_chart_horizontal']['data'] .= "$wpjobportal_job,$wpjobportal_company,$wpjobportal_resume,$ajob]";

        }
        // update available alert
        wpjobportal::$_data['update_avaliable_for_addons'] = $this->showUpdateAvaliableAlert();



        // AI Code Calls (need to add control statements)
        $wpjobportal_wjp_dashboard_defaults = [
            'quick_actions' => 'on',
            'platform_growth' => 'on',
            'quick_stats' => 'on',
            'recent_jobs' => 'on',
            'jobs_by_status_chart' => 'on',
            'top_categories_chart' => 'on',
            'latest_job_applies' => 'on',
            'latest_resumes' => 'on',
            'latest_subscriptions' => 'on',
            'latest_payments' => 'on',
            'latest_activity' => 'on',
            'system_error_log' => 'on',
            'latest_job_seekers' => 'on',
            'latest_employers' => 'on',
        ];
        // Retrieve the saved options, falling back to defaults if not set
        $wpjobportal_wjp_options = get_option('wjp_dashboard_screen_options', $wpjobportal_wjp_dashboard_defaults);



        // WPJOBPORTALincluder::getJSModel('wpjobportal')->getLatestJobAppliesForDashboard();
        // WPJOBPORTALincluder::getJSModel('wpjobportal')->getLatestSubscriptionsForDashboard();
        // WPJOBPORTALincluder::getJSModel('wpjobportal')->getLatestPaymentsForDashboard();
        // WPJOBPORTALincluder::getJSModel('wpjobportal')->getLatestActivityLogsForDashboard();
        // WPJOBPORTALincluder::getJSModel('wpjobportal')->getLatestErrorLogsForDashboard();
        // WPJOBPORTALincluder::getJSModel('wpjobportal')->getLatestMembersForDashboard();


        // --- AI Code Calls (Conditionally load these) ---
        if (isset($wpjobportal_wjp_options['platform_growth'])) {
            $this->getPlatformGrowthData();
        }
        if (isset($wpjobportal_wjp_options['quick_stats'])) {
            $this->getQuickStatsData();
        }
        if (isset($wpjobportal_wjp_options['jobs_by_status_chart'])) {
            $this->getJobsByStatusData();
        }
        if (isset($wpjobportal_wjp_options['top_categories_chart'])) {
            $this->getTopCategoriesData();
        }
        if (isset($wpjobportal_wjp_options['latest_job_applies'])) {
            WPJOBPORTALincluder::getJSModel('wpjobportal')->getLatestJobAppliesForDashboard();
        }
        if (isset($wpjobportal_wjp_options['latest_subscriptions'])) {
            WPJOBPORTALincluder::getJSModel('wpjobportal')->getLatestSubscriptionsForDashboard();
        }
        if (isset($wpjobportal_wjp_options['latest_payments'])) {
            WPJOBPORTALincluder::getJSModel('wpjobportal')->getLatestPaymentsForDashboard();
        }
        if (isset($wpjobportal_wjp_options['latest_activity'])) {
            WPJOBPORTALincluder::getJSModel('wpjobportal')->getLatestActivityLogsForDashboard();
        }
        if (isset($wpjobportal_wjp_options['system_error_log'])) {
            WPJOBPORTALincluder::getJSModel('wpjobportal')->getLatestErrorLogsForDashboard();
        }
        if (isset($wpjobportal_wjp_options['latest_job_seekers']) || isset($wpjobportal_wjp_options['latest_employers'])) {
            WPJOBPORTALincluder::getJSModel('wpjobportal')->getLatestMembersForDashboard();
        }
        if (isset($wpjobportal_wjp_options['recent_jobs']) || isset($wpjobportal_wjp_options['latest_resumes'])) {
            WPJOBPORTALincluder::getJSModel('wpjobportal')->getNewestJobs();
        }


        return;
    }


    /* AI CODE START  ==================================================================================================================================== */

    /**
     * Retrieves and prepares data for the Platform Growth chart.
     */
    function getPlatformGrowthData() {
        $wpjobportal_end_date = current_time('Y-m-d H:i:s');
        $wpjobportal_start_date = gmdate('Y-m-d H:i:s', strtotime('-29 days', strtotime($wpjobportal_end_date)));

        // Query for new jobs
        $wpjobportal_jobs_query = "SELECT DATE(created) as date, COUNT(id) as count
                       FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs`
                       WHERE created BETWEEN '" . esc_sql($wpjobportal_start_date) . "' AND '" . esc_sql($wpjobportal_end_date) . "'
                       GROUP BY DATE(created) ORDER BY date ASC";
        $wpjobportal_jobs_results = wpjobportal::$_db->get_results($wpjobportal_jobs_query);

        // Query for new applications
        $wpjobportal_applies_query = "SELECT DATE(apply_date) as date, COUNT(id) as count
                          FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply`
                          WHERE apply_date BETWEEN '" . esc_sql($wpjobportal_start_date) . "' AND '" . esc_sql($wpjobportal_end_date) . "'
                          GROUP BY DATE(apply_date) ORDER BY date ASC";
        $wpjobportal_applies_results = wpjobportal::$_db->get_results($wpjobportal_applies_query);

        // Process data into arrays for the chart
        $wpjobportal_dates = [];
        $wpjobportal_job_counts = [];
        $wpjobportal_apply_counts = [];

        $wpjobportal_jobs_by_date = !empty($wpjobportal_jobs_results) ? array_column($wpjobportal_jobs_results, 'count', 'date') : [];
        $wpjobportal_applies_by_date = !empty($wpjobportal_applies_results) ? array_column($wpjobportal_applies_results, 'count', 'date') : [];

        for ($wpjobportal_i = 0; $wpjobportal_i < 30; $wpjobportal_i++) {
            $current_date_str = gmdate('Y-m-d', strtotime("$wpjobportal_start_date +$wpjobportal_i days"));
            $wpjobportal_dates[] = gmdate('M j', strtotime($current_date_str));
            $wpjobportal_job_counts[] = isset($wpjobportal_jobs_by_date[$current_date_str]) ? (int)$wpjobportal_jobs_by_date[$current_date_str] : 0;
            $wpjobportal_apply_counts[] = isset($wpjobportal_applies_by_date[$current_date_str]) ? (int)$wpjobportal_applies_by_date[$current_date_str] : 0;
        }

        wpjobportal::$_data['platform_growth'] = [
            'labels'  => $wpjobportal_dates,
            'jobs'    => $wpjobportal_job_counts,
            'applies' => $wpjobportal_apply_counts,
        ];
        return;
    }

    /**
     * Retrieves and prepares data for the Quick Stats widget.
     */
    function getQuickStatsData() {
        $wpjobportal_today = current_time('Y-m-d H:i:s');
        $wpjobportal_start_date_month = gmdate('Y-m-d H:i:s', strtotime('-30 days', strtotime($wpjobportal_today)));
        $wpjobportal_start_date_week = gmdate('Y-m-d H:i:s', strtotime('-7 days', strtotime($wpjobportal_today)));

        $revenue = NULL;
        if(in_array('credits',wpjobportal::$_active_addons)){
            // 1. Monthly Revenue
            $query = "SELECT SUM(amount) FROM `" . wpjobportal::$_db->prefix . "wj_portal_invoices` WHERE status = 1 AND created >= '" . esc_sql($wpjobportal_start_date_month) . "'";
            $revenue = wpjobportal::$_db->get_var($query);
        }

        // 2. New Applicants (last 7 days)
        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` WHERE apply_date >= '" . esc_sql($wpjobportal_start_date_week) . "'";
        $wpjobportal_new_applicants = wpjobportal::$_db->get_var($query);

        $wpjobportal_active_subscriptions = NULL;
        if(in_array('credits',wpjobportal::$_active_addons)){
            // 3. Active Subscriptions
            $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_subscriptions` WHERE status = 1 AND nextbillingdate >= '" . esc_sql($wpjobportal_today) . "'";
            $wpjobportal_active_subscriptions = wpjobportal::$_db->get_var($query);
        }

        // 4. Pending Jobs
        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` WHERE status = 0";
        $pending_jobs = wpjobportal::$_db->get_var($query);

        // 5. Total Users
        $query = "SELECT COUNT(ID) FROM `" . wpjobportal::$_db->prefix . "users`";
        $wpjobportal_total_users = wpjobportal::$_db->get_var($query);

        // 6. Closed Jobs
        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` WHERE DATE(stoppublishing) < CURDATE(); ";
        $closed_jobs = wpjobportal::$_db->get_var($query);

        wpjobportal::$_data['quick_stats'] = [
            'monthly_revenue'      => is_null($revenue) ? 0 : $revenue,
            'new_applicants'       => is_null($wpjobportal_new_applicants) ? 0 : $wpjobportal_new_applicants,
            'active_subscriptions' => is_null($wpjobportal_active_subscriptions) ? 0 : $wpjobportal_active_subscriptions,
            'pending_jobs'         => is_null($pending_jobs) ? 0 : $pending_jobs,
            'total_users'          => is_null($wpjobportal_total_users) ? 0 : $wpjobportal_total_users,
            'closed_jobs'          => is_null($closed_jobs) ? 0 : $closed_jobs,
        ];
        return;
    }

    /**
     * Retrieves and prepares data for the Jobs by Status chart.
     */
    function getJobsByStatusData() {
        $query = "SELECT s.title, COUNT(j.id) as count
                  FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS j
                  JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobstatus` AS s ON j.jobstatus = s.id
                  GROUP BY j.jobstatus
                  ORDER BY s.ordering ASC";

        $wpjobportal_results = wpjobportal::$_db->get_results($query);
        $wpjobportal_labels = array();
        $wpjobportal_data = array();
        if(!empty($wpjobportal_results)){
            foreach ($wpjobportal_results as $wpjobportal_row) {
                $wpjobportal_labels[] = $wpjobportal_row->title;
                $wpjobportal_data[] = (int)$wpjobportal_row->count;
            }
        }

        wpjobportal::$_data['jobs_by_status'] = [
            'labels' => $wpjobportal_labels,
            'data'   => $wpjobportal_data,
        ];
        return;
    }

    /**
     * Retrieves and prepares data for the Top Job Categories chart.
     */
    function getTopCategoriesData() {
        $query = "SELECT c.cat_title, COUNT(j.id) as count
                  FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS j
                  JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS c ON j.jobcategory = c.id
                  GROUP BY j.jobcategory
                  ORDER BY count DESC
                  LIMIT 5";

        $wpjobportal_results = wpjobportal::$_db->get_results($query);
        $wpjobportal_labels = array();
        $wpjobportal_data = array();
        if(!empty($wpjobportal_results)){
            foreach ($wpjobportal_results as $wpjobportal_row) {
                $wpjobportal_labels[] = $wpjobportal_row->cat_title;
                $wpjobportal_data[] = (int)$wpjobportal_row->count;
            }
        }

        wpjobportal::$_data['top_categories'] = [
            'labels' => $wpjobportal_labels,
            'data'   => $wpjobportal_data,
        ];
        return;
    }

    /* AI CODE START  ==================================================================================================================================== */
    // Latest Job Applies
    // Latest Job Applies
    function getLatestJobAppliesForDashboard() {
        $query = "SELECT
                    resume.id AS resumeid,
                    resume.first_name,
                    resume.last_name,
                    job.id AS jobid,
                    job.title AS job_title,
                    job.city,
                    job.salarytype,
                    job.salarymin,
                    job.salarymax,
                    job.currency,
                    company.id AS companyid,
                    company.name AS company_name,
                    company.logofilename AS logo,
                    jobapply.apply_date,
                    jobtype.title as jobtype_title,
                    srtype.title AS salaryrangetype
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` AS jobapply
                JOIN `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume ON jobapply.cvid = resume.id
                JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job ON jobapply.jobid = job.id
                JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON job.companyid = company.id
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON job.jobtype = jobtype.id
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_salaryrangetypes` AS srtype ON srtype.id = job.salaryduration
                ORDER BY jobapply.apply_date DESC
                LIMIT 5";

        $wpjobportal_results = wpjobportal::$_db->get_results($query);
        $wpjobportal_data = array();
        foreach ($wpjobportal_results as $d) {
            $wpjobportal_data[] = $d;
        }
        wpjobportal::$_data[0]['latest_applies'] = $wpjobportal_data;
        return;
    }

    // Latest Subscriptions
    function getLatestSubscriptionsForDashboard() {
        if(in_array('credits',wpjobportal::$_active_addons)){
            $query = "SELECT
                    ph.uid,
                    ph.created,
                    users.first_name,
                    users.last_name,
                    packages.title AS package_name,
                    users.photo
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_userpackages` AS ph
                JOIN `" . wpjobportal::$_db->prefix . "wj_portal_users` AS users ON ph.uid = users.uid
                JOIN `" . wpjobportal::$_db->prefix . "wj_portal_packages` AS packages ON ph.packageid = packages.id
                ORDER BY ph.created DESC
                LIMIT 5";

            $wpjobportal_results = wpjobportal::$_db->get_results($query);
            wpjobportal::$_data[0]['latest_subscriptions'] = $wpjobportal_results;
        }
        return;
    }

    // Latest Payments
    function getLatestPaymentsForDashboard() {
        if(in_array('credits',wpjobportal::$_active_addons)){
            $query = "SELECT
                        inv.created,
                        inv.payer_name,
                        inv.amount,
                        inv.description,
                        curr.symbol,
                        users.photo
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_invoices` AS inv
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_users` AS users ON inv.uid = users.uid
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_currencies` as curr ON inv.currencyid = curr.id
                    WHERE inv.status = 1
                    ORDER BY inv.created DESC
                    LIMIT 5";

            $wpjobportal_results = wpjobportal::$_db->get_results($query);
            $wpjobportal_data = array();
            foreach ($wpjobportal_results as $d) {
                $wpjobportal_data[] = $d;
            }
            wpjobportal::$_data[0]['latest_payments'] = $wpjobportal_data;
        }
        return;
    }

    // Latest Activity Logs
    function getLatestActivityLogsForDashboard() {
        $query = "SELECT description, created
                  FROM `" . wpjobportal::$_db->prefix . "wj_portal_activitylog`
                  ORDER BY created DESC
                  LIMIT 5";

        $wpjobportal_results = wpjobportal::$_db->get_results($query);
        $wpjobportal_data = array();
        foreach ($wpjobportal_results as $d) {
            $d->icon_config = $this->getActivityLogIconConfigForDashboard($d->description);
            $wpjobportal_data[] = $d;
        }
        wpjobportal::$_data[0]['latest_activity'] = $wpjobportal_data;
        return;
    }

    /**
     * Determines the icon and background class for an activity log entry.
     *
     * @param string $wpjobportal_description The activity log description.
     * @return array An associative array with 'icon' and 'bg_class'.
     */
    // function getActivityLogIconConfigForDashboard($wpjobportal_description) {
    //     $wpjobportal_description = strtolower($wpjobportal_description);

    //     if (str_contains($wpjobportal_description, 'new job') || str_contains($wpjobportal_description, 'job posted')) {
    //         return ['icon' => 'fas fa-briefcase', 'bg_class' => 'wjp-bg-sky'];
    //     } elseif (str_contains($wpjobportal_description, 'new resume') || str_contains($wpjobportal_description, 'resume created')) {
    //         return ['icon' => 'fas fa-file-alt', 'bg_class' => 'wjp-bg-emerald'];
    //     } elseif (str_contains($wpjobportal_description, 'approved')) {
    //         return ['icon' => 'fas fa-check', 'bg_class' => 'wjp-bg-emerald'];
    //     } elseif (str_contains($wpjobportal_description, 'rejected') || str_contains($wpjobportal_description, 'deleted')) {
    //         return ['icon' => 'fas fa-times', 'bg_class' => 'wjp-bg-amber'];
    //     } elseif (str_contains($wpjobportal_description, 'new company')) {
    //         return ['icon' => 'fas fa-building', 'bg_class' => 'wjp-bg-indigo'];
    //     }

    //     // Default icon for other activities
    //     return ['icon' => 'fas fa-info', 'bg_class' => 'wjp-bg-slate'];
    // }

    /**
     * Gets the icon and background class configuration for a dashboard activity log entry.
     *
     * This function is data-driven, using a map to associate keywords in the
     * activity description with specific visual configurations. It checks for
     * high-priority status keywords first before moving on to entity-based keywords.
     *
     * @param string $wpjobportal_description The activity log description (e.g., "New job posted", "Company deleted").
     * @return array An associative array with 'icon' and 'bg_class' keys.
     */
    function getActivityLogIconConfigForDashboard($wpjobportal_description){
        $lowerDesc = strtolower($wpjobportal_description);

        // Configuration map: Associates keywords with their icon and background class.
        // The order is important: more specific or higher-priority rules should come first.
        $wpjobportal_configMap = [
            // --- High-priority status keywords ---
            'approved'        => ['icon' => 'fas fa-check', 'bg_class' => 'wjp-bg-emerald'],
            'rejected'        => ['icon' => 'fas fa-times', 'bg_class' => 'wjp-bg-amber'],
            'deleted'         => ['icon' => 'fas fa-trash-alt', 'bg_class' => 'wjp-bg-red'],
            'updated'         => ['icon' => 'fas fa-pencil-alt', 'bg_class' => 'wjp-bg-yellow'],
            'applied for job' => ['icon' => 'fas fa-paper-plane', 'bg_class' => 'wjp-bg-teal'],

            // --- Entity-based keywords (based on your reference code) ---
            'job'             => ['icon' => 'fas fa-briefcase', 'bg_class' => 'wjp-bg-sky'],
            'resume'          => ['icon' => 'fas fa-file-alt', 'bg_class' => 'wjp-bg-emerald'],
            'company'         => ['icon' => 'fas fa-building', 'bg_class' => 'wjp-bg-indigo'],
            'category'        => ['icon' => 'fas fa-sitemap', 'bg_class' => 'wjp-bg-purple'],
            'education'       => ['icon' => 'fas fa-graduation-cap', 'bg_class' => 'wjp-bg-blue'],
            'experience'      => ['icon' => 'fas fa-star', 'bg_class' => 'wjp-bg-cyan'],
            'cover letter'    => ['icon' => 'fas fa-envelope', 'bg_class' => 'wjp-bg-rose'],
            'salary'          => ['icon' => 'fas fa-money-bill-wave', 'bg_class' => 'wjp-bg-green'],
            'currency'        => ['icon' => 'fas fa-dollar-sign', 'bg_class' => 'wjp-bg-green'],
            'department'      => ['icon' => 'fas fa-users', 'bg_class' => 'wjp-bg-fuchsia'],
            'country'         => ['icon' => 'fas fa-globe-americas', 'bg_class' => 'wjp-bg-orange'],
            'city'            => ['icon' => 'fas fa-city', 'bg_class' => 'wjp-bg-orange'],
            'email template'  => ['icon' => 'fas fa-envelope-open-text', 'bg_class' => 'wjp-bg-pink'],
            'user'            => ['icon' => 'fas fa-user', 'bg_class' => 'wjp-bg-slate'],
        ];

        // Iterate through the map and return the configuration for the first keyword found.
        foreach ($wpjobportal_configMap as $wpjobportal_keyword => $wpjobportal_config) {
            if (str_contains($lowerDesc, $wpjobportal_keyword)) {
                return $wpjobportal_config;
            }
        }

        // Return a default icon if no keywords match.
        return ['icon' => 'fas fa-info-circle', 'bg_class' => 'wjp-bg-slate'];
    }

    // Latest Error Logs
    function getLatestErrorLogsForDashboard() {
        $query = "SELECT id, error, created
                  FROM `" . wpjobportal::$_db->prefix . "wj_portal_system_errors`
                  ORDER BY created DESC
                  LIMIT 5";

        $wpjobportal_results = wpjobportal::$_db->get_results($query);
        $wpjobportal_data = array();
        foreach ($wpjobportal_results as $d) {
            $d->style_class = $this->getErrorLogStyleConfigForDashboard($d->error);
            $wpjobportal_data[] = $d;
        }
        wpjobportal::$_data[0]['latest_errors'] = $wpjobportal_data;
        return;
    }


    /**
     * Determines the CSS style class for a system error log entry based on severity.
     *
     * @param string $error The error message.
     * @return string The CSS class name for styling.
     */
    function getErrorLogStyleConfigForDashboard($error) {
        $error = strtolower($error);

        if (str_contains($error, 'fatal') || str_contains($error, 'critical') || str_contains($error, 'error')) {
            return 'wjp-text-red';
        } elseif (str_contains($error, 'warning') || str_contains($error, 'notice')) {
            return 'wjp-text-amber';
        }

        // Default for info or unknown errors
        return '';
    }


    // Latest Members (Jobseekers + Employers)
    function getLatestMembersForDashboard() {
        // Job Seekers
        $query_seekers = "SELECT u.uid, CONCAT(u.first_name,' ',u.last_name) AS username, r.application_title AS title, u.created,u.photo,u.id
                          FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` AS u
                          LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS r ON u.uid = r.uid
                          WHERE u.roleid = 2
                          GROUP BY u.uid
                          ORDER BY u.created DESC
                          LIMIT 5";

        $wpjobportal_seekers = wpjobportal::$_db->get_results($query_seekers);
        $wpjobportal_jobseekers = array();
        foreach ($wpjobportal_seekers as $s) {
            $wpjobportal_jobseekers[] = $s;
        }
        wpjobportal::$_data[0]['latest_jobseekers'] = $wpjobportal_jobseekers;

        // Employers
        $query_employers = "SELECT u.uid, c.id AS companyid, CONCAT(u.first_name,' ',u.last_name) AS username, c.name AS title, u.created,u.photo,u.id AS emp_user_id
                            FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` AS u
                            LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS c ON u.uid = c.uid
                            WHERE u.roleid = 1
                            GROUP BY u.uid
                            ORDER BY u.created DESC
                            LIMIT 5";

        $wpjobportal_employers = wpjobportal::$_db->get_results($query_employers);
        $wpjobportal_empl = array();
        foreach ($wpjobportal_employers as $e) {
            $wpjobportal_empl[] = $e;
        }
        wpjobportal::$_data[0]['latest_employers'] = $wpjobportal_empl;

        return;
    }

    /* AI CODE END  ==================================================================================================================================== */



    function showUpdateAvaliableAlert(){
        require_once WPJOBPORTAL_PLUGIN_PATH.'includes/addon-updater/wpjobportalupdater.php';
        $WPJOBPORTAL_JOBPORTALUpdater    = new WPJOBPORTAL_JOBPORTALUpdater();
        $wpjobportal_cdnversiondata = $WPJOBPORTAL_JOBPORTALUpdater->getPluginVersionDataFromCDN();
        $wpjobportal_not_installed = array();

        $wpjobportal_addons = $this->getWPJPAddonsArray();
        $wpjobportal_installed_plugins = get_plugins();
        $wpjobportal_count = 0;
        foreach ($wpjobportal_addons as $wpjobportal_key1 => $wpjobportal_value1) {
                $wpjobportal_matched = 0;
                $wpjobportal_version = "";
                foreach ($wpjobportal_installed_plugins as $wpjobportal_name => $wpjobportal_value) {
                        $wpjobportal_install_plugin_name = str_replace(".php","",basename($wpjobportal_name));
                        if($wpjobportal_key1 == $wpjobportal_install_plugin_name){
                                $wpjobportal_matched = 1;
                                $wpjobportal_version = $wpjobportal_value["Version"];
                                $wpjobportal_install_plugin_matched_name = $wpjobportal_install_plugin_name;
                        }
                }
                if($wpjobportal_matched == 1){ //installed
                        $wpjobportal_name = $wpjobportal_key1;
                        $title = $wpjobportal_value1['title'];
                        $wpjobportal_img = str_replace("wp-job-portal-", "", $wpjobportal_key1).'.png';
                        $wpjobportal_cdnavailableversion = "";
                        if($wpjobportal_cdnversiondata){
                            foreach ($wpjobportal_cdnversiondata as $wpjobportal_cdnname => $wpjobportal_cdnversion) {
                                    $wpjobportal_install_plugin_name_simple = str_replace("-", "", $wpjobportal_install_plugin_matched_name);
                                    if($wpjobportal_cdnname == str_replace("-", "", $wpjobportal_install_plugin_matched_name)){
                                            if($wpjobportal_cdnversion > $wpjobportal_version){ // new version available
                                                    $wpjobportal_count++;
                                            }
                                    }
                            }
                        }
                }
        }
        return $wpjobportal_count;
    }



    function getWPJPAddonsArray(){
        return  array(
            'wp-job-portal-elegantdesign' => array('title' => esc_html(__('Elegant Design','wp-job-portal')), 'price' => 0, 'status' => 1),
            'wp-job-portal-addressdata' => array('title' => esc_html(__('Address Data','wp-job-portal')), 'price' => 0, 'status' => 1),
            //'wp-job-portal-sociallogin' => array('title' => esc_html(__('Social Login','wp-job-portal')), 'price' => 0, 'status' => 1),
            'wp-job-portal-visitorapplyjob' => array('title' => esc_html(__('visitor apply job','wp-job-portal')), 'price' => 0, 'status' => 1),
            'wp-job-portal-multicompany' => array('title' => esc_html(__('Multi Company','wp-job-portal')), 'price' => 0, 'status' => 1),
            'wp-job-portal-featuredcompany' => array('title' => esc_html(__('featured company','wp-job-portal')), 'price' => 0, 'status' => 1),
            'wp-job-portal-copyjob' => array('title' => esc_html(__('copy job','wp-job-portal')), 'price' => 0, 'status' => 1),
            'wp-job-portal-credits' => array('title' => esc_html(__('Credits','wp-job-portal')), 'price' => 0, 'status' => 1),
            'wp-job-portal-departments' => array('title' => esc_html(__('Department','wp-job-portal')), 'price' => 0, 'status' => 1),
            'wp-job-portal-export' => array('title' => esc_html(__('Export','wp-job-portal')), 'price' => 0, 'status' => 1),
            'wp-job-portal-featureresume' => array('title' => esc_html(__('Feature Resume','wp-job-portal')), 'price' => 0, 'status' => 1),
            'wp-job-portal-featuredjob' => array('title' => esc_html(__('Featured Job','wp-job-portal')), 'price' => 0, 'status' => 1),
            'wp-job-portal-rssfeedback' => array('title' => esc_html(__('Rss Feed','wp-job-portal')), 'price' => 0, 'status' => 1),
            'wp-job-portal-folder' => array('title' => esc_html(__('Folder','wp-job-portal')), 'price' => 0, 'status' => 1),
            'wp-job-portal-jobalert' => array('title' => esc_html(__('Job Alert','wp-job-portal')), 'price' => 0, 'status' => 1),
            'wp-job-portal-message' => array('title' => esc_html(__('Message System','wp-job-portal')), 'price' => 0, 'status' => 1),
            'wp-job-portal-pdf' => array('title' => esc_html(__('PDF','wp-job-portal')), 'price' => 0, 'status' => 1),
            'wp-job-portal-print' => array('title' => esc_html(__('Print','wp-job-portal')), 'price' => 0, 'status' => 1),
            'wp-job-portal-reports'=> array('title' => esc_html(__("Reports","wp-job-portal")), 'price' => 0, 'status' => 1),
            'wp-job-portal-resumeaction' => array('title' => esc_html(__('Resume Action','wp-job-portal')), 'price' => 0, 'status' => 1),
            'wp-job-portal-multiresume' => array('title' => esc_html(__('Multi Resume','wp-job-portal')), 'price' => 0, 'status' => 1),
            'wp-job-portal-resumesearch' => array('title' => esc_html(__('Resume Search','wp-job-portal')), 'price' => 0, 'status' => 1),
            'wp-job-portal-shortlist' => array('title' => esc_html(__('Shortlist','wp-job-portal')), 'price' => 0, 'status' => 1),
            'wp-job-portal-socialshare' => array('title' => esc_html(__('Social Share','wp-job-portal')), 'price' => 0, 'status' => 1),
            'wp-job-portal-tag' => array('title' => esc_html(__('Tags','wp-job-portal')), 'price' => 0, 'status' => 1),
            'wp-job-portal-tellfriend' => array('title' => esc_html(__('Tell Friend','wp-job-portal')), 'price' => 0, 'status' => 1),
            'wp-job-portal-advanceresumebuilder' => array('title' => esc_html(__('Advance Resume Builder','wp-job-portal')), 'price' => 0, 'status' => 1),
            'wp-job-portal-visitorcanaddjob' => array('title' => esc_html(__('Visitor Add Job','wp-job-portal')), 'price' => 0, 'status' => 1),
            'wp-job-portal-cronjob' => array('title' => esc_html(__('Cron Job','wp-job-portal')), 'price' => 0, 'status' => 1),
            'wp-job-portal-widgets' => array('title' => esc_html(__('Front-End Widgets','wp-job-portal')), 'price' => 0, 'status' => 1),
            'wp-job-portal-coverletter' => array('title' => esc_html(__('Cover Letter','wp-job-portal')), 'price' => 0, 'status' => 1),
            'wp-job-portal-allcompanies' => array('title' => esc_html(__('All Companies','wp-job-portal')), 'price' => 0, 'status' => 1),
            'wp-job-portal-allresumes' => array('title' => esc_html(__('All Resumes','wp-job-portal')), 'price' => 0, 'status' => 1),

            'wp-job-portal-aijobsearch' => array('title' => esc_html(__('AI Job Search','wp-job-portal')), 'price' => 0, 'status' => 1),
            'wp-job-portal-airesumesearch' => array('title' => esc_html(__('AI Resume Search','wp-job-portal')), 'price' => 0, 'status' => 1),
            'wp-job-portal-aisuggestedjobs' => array('title' => esc_html(__('AI Suggested Jobs','wp-job-portal')), 'price' => 0, 'status' => 1),
            'wp-job-portal-aisuggestedresumes' => array('title' => esc_html(__('AI Suggested Resumes','wp-job-portal')), 'price' => 0, 'status' => 1)

        );
    }


    function storeServerSerailNumber($wpjobportal_data) {
        if (empty($wpjobportal_data))
            return false;
        // DB class limitations
        if ($wpjobportal_data['server_serialnumber']) {
            $query = "UPDATE  `" . wpjobportal::$_db->prefix . "wj_portal_config` SET configvalue='" . esc_sql($wpjobportal_data['server_serialnumber']) . "' WHERE configname='server_serial_number'";

            if (!wpjobportaldb::query($query))
                return WPJOBPORTAL_SAVE_ERROR;
            else
                return WPJOBPORTAL_SAVED;
        } else
            return WPJOBPORTAL_SAVE_ERROR;
    }

    function storeModule($wpjobportal_data,$wpjobportal_actionname){
        # Woo Commerce Save WOrking For Module
        # Configuration Base Switch
        switch ($wpjobportal_actionname) {
            case 'job_department_price_perlisting':
                # Department Configuration + subAddon(Purchase History)
                WPJOBPORTALincluder::getJSModel('purchasehistory')->storeDepartmentPayment($wpjobportal_data);
                break;
            case 'job_coverletter_price_perlisting':
                # Department Configuration + subAddon(Purchase History)
                WPJOBPORTALincluder::getJSModel('purchasehistory')->storeCoverLetterPayment($wpjobportal_data);
                break;
            case 'company_price_perlisting':
                WPJOBPORTALincluder::getJSModel('purchasehistory')->storeCompanyPayment($wpjobportal_data);
                break;
            case 'company_feature_price_perlisting':
                WPJOBPORTALincluder::getJSModel('purchasehistory')->storeFeaturedCompanyPayment($wpjobportal_data);
                break;
            case 'job_currency_price_perlisting':
                WPJOBPORTALincluder::getJSModel('purchasehistory')->StoreJobPayment($wpjobportal_data);
                break;
            case 'jobs_feature_price_perlisting':
                WPJOBPORTALincluder::getJSModel('purchasehistory')->storeFeaturedJobPayment($wpjobportal_data);
                break;
            case 'job_resume_price_perlisting':
                WPJOBPORTALincluder::getJSModel('purchasehistory')->StoreResumePayment($wpjobportal_data);
                break;
            case 'job_featureresume_price_perlisting':
                WPJOBPORTALincluder::getJSModel('purchasehistory')->storeFeaturedResumePayment($wpjobportal_data);
                break;
            case 'job_jobalert_price_perlisting':
               WPJOBPORTALincluder::getJSModel('purchasehistory')->storeJobAlertPayment($wpjobportal_data);
                break;
            case 'job_resumesavesearch_price_perlisting':
                # Resume Search Payment
               WPJOBPORTALincluder::getJSModel('purchasehistory')->storeResumeSearchPayment($wpjobportal_data);
                break;
            case 'job_jobapply_price_perlisting':
                WPJOBPORTALincluder::getJSModel('purchasehistory')->storeJobApplyPayment($wpjobportal_data);
                break;
            case 'job_viewcompanycontact_price_perlisting':
                WPJOBPORTALincluder::getJSModel('purchasehistory')->storeCompanyViewPayment($wpjobportal_data);
                break;
           case 'job_viewresumecontact_price_perlisting':
                WPJOBPORTALincluder::getJSModel('purchasehistory')->storeResumeViewPayment($wpjobportal_data);
                break;
        }
    }


    function getNewestJobs() {
        $query = "SELECT  DISTINCT job.id AS id,job.currency,job.tags AS jobtags,job.title,job.created,job.city,
                    CONCAT(job.alias,'-',job.id) AS jobaliasid,job.noofjobs,job.isfeaturedjob,job.status,
                    cat.cat_title,company.id AS companyid,company.name AS companyname,company.logofilename AS logo, jobtype.title AS jobtypetitle,job.endfeatureddate,job.startpublishing,job.stoppublishing,
                    job.params,CONCAT(company.alias,'-',company.id) AS companyaliasid,LOWER(jobtype.title) AS jobtypetit,
                    job.salarymax,job.salarymin,job.salarytype,srtype.title AS salaryrangetype,jobtype.color AS jobtypecolor
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                    ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON company.id = job.companyid
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON cat.id = job.jobcategory
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_salaryrangetypes` AS srtype ON srtype.id = job.salaryduration
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON jobtype.id = job.jobtype
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobcities` AS jobcity ON jobcity.jobid = job.id
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON city.id = jobcity.cityid
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_states` AS state ON state.countryid = city.countryid
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_countries` AS country ON country.id = city.countryid
                    ORDER BY job.created DESC
                    LIMIT 0,5";
                    //getMyResumes
        wpjobportal::$_data[0]['latestjobs'] = wpjobportaldb::get_results($query);
        $query = "SELECT app.uid,app.id,app.endfeatureddate, app.application_title,app.first_name, app.last_name,
                        app.jobtype,app.photo,app.salaryfixed, app.created, app.status, cat.cat_title
                , jobtype.title AS jobtypetitle,app.isfeaturedresume,city.id as city,jobtype.color
            FROM " . wpjobportal::$_db->prefix . "wj_portal_resume AS app
            LEFT JOIN " . wpjobportal::$_db->prefix . "wj_portal_categories AS cat ON app.job_category = cat.id
            LEFT JOIN " . wpjobportal::$_db->prefix . "wj_portal_jobtypes AS jobtype    ON app.jobtype = jobtype.id
            LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON city.id = (SELECT address_city FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumeaddresses` WHERE resumeid = app.id ORDER BY id DESC LIMIT 1)
            WHERE app.status <> 0 AND app.quick_apply <> 1";

            $query.=" ORDER BY app.created DESC LIMIT 0,6 ";

        $wpjobportal_results = wpjobportal::$_db->get_results($query);
        wpjobportal::$_data[0]['latestresumes'] = wpjobportaldb::get_results($query);
        wpjobportal::$wpjobportal_data['fields'] = wpjobportal::$_wpjpfieldordering->getFieldsOrderingforView(3);
        wpjobportal::$_data['config'] = wpjobportal::$_config->getConfigByFor('resume');
    }

    function getTodayStats() {

        $query = "SELECT count(id) AS totalcompanies
        FROM " . wpjobportal::$_db->prefix . "wj_portal_companies AS company WHERE company.status=1 AND company.created >= CURDATE();";

        $wpjobportal_companies = wpjobportaldb::get_row($query);
        $query = "SELECT count(id) AS totaljobs
        FROM " . wpjobportal::$_db->prefix . "wj_portal_jobs AS job WHERE job.status=1 AND job.created >= CURDATE();";

        $wpjobportal_jobs = wpjobportaldb::get_row($query);
        $query = "SELECT count(id) AS totalresume
        FROM " . wpjobportal::$_db->prefix . "wj_portal_resume AS resume WHERE resume.status=1 AND resume.created >= CURDATE();";

        $wpjobportal_resumes = wpjobportaldb::get_row($query);

        $query = "SELECT count(user.id) AS totalemployer
                    FROM " . wpjobportal::$_db->prefix . "wj_portal_users AS user
                    WHERE user.roleid = 1 AND DATE(user.created) = CURDATE()";

        $wpjobportal_employer = wpjobportaldb::get_row($query);

        $query = "SELECT count(user.id) AS totaljobseeker
                    FROM " . wpjobportal::$_db->prefix . "wj_portal_users AS user
                    WHERE user.roleid = 2 AND DATE(user.created) = CURDATE()";

        $wpjobportal_jobseeker = wpjobportaldb::get_row($query);

        wpjobportal::$_data[0]['companies'] = $wpjobportal_companies;
        wpjobportal::$_data[0]['jobs'] = $wpjobportal_jobs;
        wpjobportal::$_data[0]['resumes'] = $wpjobportal_resumes;
        wpjobportal::$_data[0]['employer'] = $wpjobportal_employer;
        wpjobportal::$_data[0]['jobseeker'] = $wpjobportal_jobseeker;
        return;
    }

    function getConcurrentRequestData() {

        $query = "SELECT configname,configvalue FROM `".wpjobportal::$_db->prefix."wj_portal_config` WHERE configfor = hostdata";
        $wpjobportal_result = wpjobportaldb::get_results($query);
        foreach ($wpjobportal_result AS $res) {
            $return[$res->configname] = $res->configvalue;
        }
        return $return;
    }

    function getMultiCityDataForView($wpjobportal_id, $for) {
        if (!is_numeric($wpjobportal_id))
            return false;

        $query = "select mcity.id AS id,country.name AS countryName,city.name AS cityName,state.name AS stateName, city.id AS cityid";
        switch ($for) {
            case 1:
                $query.=" FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobcities` AS mcity";
                $query.=" LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job ON mcity.jobid=job.id";
                break;
            case 2:
                $query.=" FROM `" . wpjobportal::$_db->prefix . "wj_portal_companycities` AS mcity";
                $query.=" LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON mcity.companyid=company.id";
                break;
        }
        $query.=" LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON mcity.cityid=city.id
                  LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_states` AS state ON city.stateid=state.id
                  LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_countries` AS country ON city.countryid=country.id";
        switch ($for) {
            case 1:
                $query.=" where mcity.jobid=" . esc_sql($wpjobportal_id);
                break;
            case 2:
                $query.=" where mcity.companyid=" . esc_sql($wpjobportal_id);
                break;
        }
        $query.=" ORDER BY country.name";

        $cities = wpjobportaldb::get_results($query);
        $mloc = array();
        $mcountry = array();
        $finalloc = "";
        $cityids = '';
        foreach ($cities AS $city) {
            if($cityids != ''){
                $cityids .= ',';
            }
            $cityids .= $city->cityid;
            // if ($city->countryName != null)
            //     $mcountry[] = $city->countryName;
        }
        if($cityids != ''){
            $finalloc = WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($cityids);
        }
        // if (!empty($mcountry)) {
        //     $wpjobportal_country_total = array_count_values($mcountry);
        // } else {
        //     $wpjobportal_country_total = array();
        // }
        // $wpjobportal_i = 0;
        // foreach ($wpjobportal_country_total AS $wpjobportal_key => $wpjobportal_val) {
        //     foreach ($cities AS $city) {

        //         if ($wpjobportal_key == $city->countryName) {
        //             $wpjobportal_i++;
        //             if ($wpjobportal_val == 1) {
        //                 $finalloc.="[" . $city->cityName . ", " . $wpjobportal_key . " ] ";
        //                 $wpjobportal_i = 0;
        //             } elseif ($wpjobportal_i == $wpjobportal_val) {
        //                 $finalloc.=$city->cityName . ", " . $wpjobportal_key . " ] ";
        //                 $wpjobportal_i = 0;
        //             } elseif ($wpjobportal_i == 1)
        //                 $finalloc.= "[" . $city->cityName . ", ";
        //             else
        //                 $finalloc.=$city->cityName . ", ";
        //         }
        //     }
        // }
        return $finalloc;
    }

    function getwpjobportalStats() {

        $query = "SELECT count(id) AS totalcompanies,(SELECT count(company.id) FROM " . wpjobportal::$_db->prefix . "wj_portal_companies AS company WHERE company.status=1 ) AS activecompanies
        FROM " . wpjobportal::$_db->prefix . "wj_portal_companies ";

        $wpjobportal_companies = wpjobportaldb::get_row($query);

        $query = "SELECT count(id) AS totaljobs,(SELECT count(job.id) FROM " . wpjobportal::$_db->prefix . "wj_portal_jobs AS job WHERE job.status=1 AND job.stoppublishing >= CURDATE())  AS activejobs
        FROM " . wpjobportal::$_db->prefix . "wj_portal_jobs ";

        $wpjobportal_jobs = wpjobportaldb::get_row($query);

        $query = "SELECT count(id) AS totalresumes,(SELECT count(resume.id) FROM " . wpjobportal::$_db->prefix . "wj_portal_resume AS resume WHERE resume.status=1 ) AS activeresumes
        FROM " . wpjobportal::$_db->prefix . "wj_portal_resume ";

        $wpjobportal_resumes = wpjobportaldb::get_row($query);

        if(in_array('featuredcompany', wpjobportal::$_active_addons) && in_array('credits', wpjobportal::$_active_addons)){
            $query = "SELECT (SELECT COUNT(id) FROM " . wpjobportal::$_db->prefix . "wj_portal_companies WHERE isfeaturedcompany=1) AS totalfeaturedcompanies,
                    (SELECT count(featuredcompany.id) FROM " . wpjobportal::$_db->prefix . "wj_portal_companies  AS featuredcompany
                    JOIN  " . wpjobportal::$_db->prefix . "wj_portal_userpackages AS package ON package.id=featuredcompany.userpackageid
                    WHERE  featuredcompany.status=1 AND featuredcompany.isfeaturedcompany=1  AND featuredcompany.endfeatureddate >= CURDATE() ) AS activefeaturedcompanies
            FROM " . wpjobportal::$_db->prefix . "wj_portal_companies";

            $featuredcompanies = wpjobportaldb::get_row($query);
            wpjobportal::$_data[0]['featuredcompanies'] = $featuredcompanies;
        }
        if(in_array('featuredjob', wpjobportal::$_active_addons) && in_array('credits', wpjobportal::$_active_addons)){
            $query = "SELECT ( SELECT COUNT(id) FROM " . wpjobportal::$_db->prefix . "wj_portal_jobs WHERE isfeaturedjob=1 ) AS totalfeaturedjobs,(SELECT count(featuredjob.id) FROM " . wpjobportal::$_db->prefix . "wj_portal_jobs AS featuredjob
            JOIN  " . wpjobportal::$_db->prefix . "wj_portal_userpackages AS package ON package.id=featuredjob.userpackageid
            WHERE  featuredjob.status= 1 AND featuredjob.isfeaturedjob= 1  AND featuredjob.endfeatureddate >= CURDATE() ) AS activefeaturedjobs
            ";
            $featuredjobs = wpjobportaldb::get_row($query);
            wpjobportal::$_data[0]['featuredjobs'] = $featuredjobs;
        }


        if(in_array('featureresume', wpjobportal::$_active_addons) && in_array('credits', wpjobportal::$_active_addons)){
            $query = "SELECT ( SELECT COUNT(id) FROM " . wpjobportal::$_db->prefix . "wj_portal_resume WHERE isfeaturedresume=1 ) AS totalfeaturedresumes,(SELECT count(featuredresume.id) FROM " . wpjobportal::$_db->prefix . "wj_portal_resume  AS featuredresume
            JOIN  " . wpjobportal::$_db->prefix . "wj_portal_userpackages AS package ON package.id=featuredresume.userpackageid
            WHERE  featuredresume.status= 1 AND featuredresume.isfeaturedresume= 1  AND featuredresume.endfeatureddate >= CURDATE() ) AS activefeaturedresumes
            ";

            $featuredresumes = wpjobportaldb::get_row($query);
            wpjobportal::$_data[0]['featuredresumes'] = $featuredresumes;
        }


        $wpjobportal_totalpaidamount = 'Recalculate';

        $query = "SELECT count(user.id) AS totalemployer
                    FROM " . wpjobportal::$_db->prefix . "wj_portal_users AS user
                    WHERE user.roleid = 1";

        $wpjobportal_totalemployer = wpjobportaldb::get_row($query);

        $query = "SELECT count(user.id) AS totaljobseeker
                    FROM " . wpjobportal::$_db->prefix . "wj_portal_users AS user
                    WHERE user.roleid=2";

        $wpjobportal_totaljobseeker = wpjobportaldb::get_row($query);

        wpjobportal::$_data[0]['companies'] = $wpjobportal_companies;
        wpjobportal::$_data[0]['jobs'] = $wpjobportal_jobs;
        wpjobportal::$_data[0]['resumes'] = $wpjobportal_resumes;
        wpjobportal::$_data[0]['totalpaidamount'] = $wpjobportal_totalpaidamount;
        wpjobportal::$_data[0]['totalemployer'] = $wpjobportal_totalemployer;
        wpjobportal::$_data[0]['totaljobseeker'] = $wpjobportal_totaljobseeker;
        return;
    }


    function widgetTotalStatsData() {
        $query = "SELECT count(id) AS totalcompanies
        FROM " . wpjobportal::$_db->prefix . "wj_portal_companies ";

        $wpjobportal_companies = wpjobportaldb::get_row($query);

        $query = "SELECT count(id) AS totaljobs,(SELECT count(job.id) FROM " . wpjobportal::$_db->prefix . "wj_portal_jobs AS job WHERE job.status=1 AND job.stoppublishing >= CURDATE())  AS activejobs
        FROM " . wpjobportal::$_db->prefix . "wj_portal_jobs ";

        $wpjobportal_jobs = wpjobportaldb::get_row($query);

        $query = "SELECT count(id) AS totalresumes
        FROM " . wpjobportal::$_db->prefix . "wj_portal_resume ";

        $wpjobportal_resumes = wpjobportaldb::get_row($query);

        $query = "SELECT count(DISTINCT jobid) AS appliedjobs
        FROM " . wpjobportal::$_db->prefix . "wj_portal_jobapply ";

        $aplliedjobs = wpjobportaldb::get_row($query);


        wpjobportal::$_data['widget']['companies'] = $wpjobportal_companies;
        wpjobportal::$_data['widget']['jobs'] = $wpjobportal_jobs;
        wpjobportal::$_data['widget']['resumes'] = $wpjobportal_resumes;
        wpjobportal::$_data['widget']['aplliedjobs'] = $aplliedjobs;
        return true;
    }

    function widgetLastWeekData() {
        $wpjobportal_newindays = 7;
        $wpjobportal_curdate = gmdate('Y-m-d');
        $time = strtotime($wpjobportal_curdate . ' -' . $wpjobportal_newindays . ' days');
        $lastdate = gmdate("Y-m-d", $time);
        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` WHERE DATE(created) >= DATE('" . esc_sql($lastdate) . "') AND DATE(created) <= '" . esc_sql($wpjobportal_curdate) . "'";
        wpjobportal::$_data['widget']['newjobs'] = wpjobportal::$_db->get_var($query);
        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` WHERE DATE(created) >= DATE('" . esc_sql($lastdate) . "') AND DATE(created) <= DATE('" . esc_sql($wpjobportal_curdate) . "')";
        wpjobportal::$_data['widget']['newcompanies'] = wpjobportal::$_db->get_var($query);

        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` WHERE DATE(created) >= DATE('" . esc_sql($lastdate) . "') AND DATE(created) <= DATE('" . esc_sql($wpjobportal_curdate) . "')";
        wpjobportal::$_data['widget']['newresume'] = wpjobportal::$_db->get_var($query);

        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` WHERE DATE(apply_date) >= '" . esc_sql($lastdate) . "' AND DATE(apply_date) <= '" . esc_sql($wpjobportal_curdate) . "'";
        wpjobportal::$_data['widget']['newjobapply'] = wpjobportal::$_db->get_var($query);
        if(!wpjobportal::$_data['widget']['newjobapply']) wpjobportal::$_data['widget']['newjobapply'] = 0;

        wpjobportal::$_data['widget']['startdate'] = gmdate('d M, Y', strtotime($lastdate));
        wpjobportal::$_data['widget']['enddate'] = gmdate('d M, Y', strtotime($wpjobportal_curdate));
        return true;
    }

    function getDataForWidgetPopup() {
        $wpjobportal_dataid = WPJOBPORTALrequest::getVar('dataid');
        $wpjobportal_newindays = 7;
        $wpjobportal_curdate = gmdate('Y-m-d');
        $time = strtotime($wpjobportal_curdate . ' -' . $wpjobportal_newindays . ' days');
        $lastdate = gmdate("Y-m-d", $time);
        if ($wpjobportal_dataid == 1) { //job
            $query = "SELECT job.companyid AS id,job.title,isfeaturedjob AS isfeatured
                        ,job.status,cat.cat_title,job.city,comp.logofilename AS photo
            FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
            ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS comp ON comp.id = job.companyid
            LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON cat.id = job.jobcategory
            WHERE DATE(job.created) >= DATE('" . esc_sql($lastdate) . "') AND DATE(job.created) <= DATE('" . esc_sql($wpjobportal_curdate) . "')
            ORDER BY job.created DESC LIMIT 5";
            $wpjobportal_results = wpjobportal::$_db->get_results($query);
        }
        if ($wpjobportal_dataid == 2) { //company
            $query = "SELECT comp.id ,comp.name AS title,comp.isfeaturedcompany AS isfeatured
                        ,comp.city,comp.status,comp.logofilename AS photo,cat.cat_title
            FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS comp
            LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON cat.id = comp.category
            WHERE DATE(comp.created) >= DATE('" . esc_sql($lastdate) . "') AND DATE(comp.created) <= DATE('" . esc_sql($wpjobportal_curdate) . "')
            ORDER BY comp.created DESC LIMIT 5";
            $wpjobportal_results = wpjobportal::$_db->get_results($query);
        }
        if ($wpjobportal_dataid == 3) {     //resume
            $query = "SELECT resume.id, CONCAT(resume.application_title,' ( ',resume.first_name,' ',resume.last_name,' )' ) AS title,resume.isfeaturedresume AS isfeatured,resume.status,cat.cat_title,resume.photo
            FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume
            LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON cat.id = resume.job_category
            WHERE DATE(resume.created) >= DATE('" . esc_sql($lastdate) . "') AND DATE(resume.created) <= DATE('" . esc_sql($wpjobportal_curdate) . "')
            ORDER BY resume.created DESC LIMIT 5";
            $wpjobportal_results = wpjobportal::$_db->get_results($query);
        }
        if ($wpjobportal_dataid == 4) {  //jobappply
            $query = "SELECT  comp.id,comp.logofilename AS logo,job.title AS title
                    ,CONCAT(resume.application_title,' / ',resume.first_name,' ',resume.last_name) AS name
                    ,jobapp.apply_date,jobapp.action_status as status,job.isfeaturedjob AS isfeatured
            FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` AS jobapp
            JOIN `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume ON resume.id = jobapp.cvid
            JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job on job.id = jobapp.jobid
            ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS comp ON comp.id = job.companyid
            WHERE DATE(jobapp.apply_date) >= DATE('" . esc_sql($lastdate) . "') AND DATE(jobapp.apply_date) <= DATE('" . esc_sql($wpjobportal_curdate) . "')
            ORDER BY jobapp.apply_date DESC LIMIT 5";
            $wpjobportal_results = wpjobportal::$_db->get_results($query);
        }
        $wpjobportal_html = $this->generatePopup($wpjobportal_results, $wpjobportal_dataid);
        return $wpjobportal_html;
    }

//function to denerate popup from new jobs companies and resume
    function generatePopup($wpjobportal_results, $wpjobportal_dataid) {
        if ($wpjobportal_dataid == 1) {
            $title = esc_html(__('Newest Jobs', 'wp-job-portal'));
        } elseif ($wpjobportal_dataid == 2) {
            $title = esc_html(__('Newest Companies', 'wp-job-portal'));
        } elseif ($wpjobportal_dataid == 3) {
            $title = esc_html(__('Newest Resumes', 'wp-job-portal'));
        } elseif ($wpjobportal_dataid == 4) {
            $title = esc_html(__('Newest Applied Jobs', 'wp-job-portal'));
        }
        $wpjobportal_html = '';
        $wpjobportal_html = '<span class="popup-top">
                    <span id="popup_title" >
                    ' . $title . '
                    </span>
                    <img id="popup_cross" src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/popup-close.png">
                </span>
                <div class="widget-popup-body">';
        if (empty($wpjobportal_results)) {
            $error = '
                <div class="js_job_error_messages_wrapper">
                    <div class="message1">
                        <span>
                            ' . esc_html(__("Oops...", 'wp-job-portal')) . '
                        </span>
                    </div>
                    <div class="message2">
                         <span class="img">
                        <img class="js_job_messages_image" src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/norecordfound.png"/>
                         </span>
                         <span class="message-text">
                            ' . esc_html(__('Record Not Found', 'wp-job-portal')) . '
                         </span>
                    </div>
                    <div class="footer">
                        <a href ="' . 'admin.php?page=wpjobportal' . '">' . esc_html(__('Back to control panel', 'wp-job-portal')) . '</a>
                    </div>
                </div>
        ';
            $wpjobportal_html .= ' ' . $error . '</div>';
            return $wpjobportal_html;
        }

        //popup layout for new job /company/resume
        if ($wpjobportal_dataid != 4) {
            //1 = newest jobs
            //2 = newest compnay
            //3 = newest resume
            //4 = applied jobs

            foreach ($wpjobportal_results as $wpjobportal_data) {
                //photo / logo
                //for company and job
                $wpjobportal_data_directory = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('data_directory');
                $wpjobportal_wpdir = wp_upload_dir();
                if ($wpjobportal_dataid == 1 || $wpjobportal_dataid == 2) {
                    if ($wpjobportal_data->photo != "") {
                        $wpjobportal_path = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/employer/comp_' . $wpjobportal_data->id . '/logo/' . $wpjobportal_data->photo;
                    } else {
                        $wpjobportal_path = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('employer');
                    }
                } elseif ($wpjobportal_data->photo != "") {
                    $wpjobportal_path = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/jobseeker/resume_' . $wpjobportal_data->id . '/photo/' . $wpjobportal_data->photo;
                } else {
                    $wpjobportal_path = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('jobseeker');
                }

                $picstyle = '';
                //bottom link

                if ($wpjobportal_dataid == 1) {
                    $wpjobportal_link = 'admin.php?page=wpjobportal_job&wpjobportallt=jobs';
                }
                if ($wpjobportal_dataid == 2) {
                    $wpjobportal_link = 'admin.php?page=wpjobportal_company&wpjobportallt=companies';
                }
                if ($wpjobportal_dataid == 3) {
                    $wpjobportal_link = 'admin.php?page=wpjobportal_resume&wpjobportallt=resumes';
                    $picstyle = 'resume-img';
                }


                //city //resume has education not city
                if ($wpjobportal_dataid != 3) {
                    $wpjobportal_data->city = WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($wpjobportal_data->city);
                }
                //flags expressing status
                if ($wpjobportal_data->status == 0) {
                    $flaghtml = '<div class="pending-badge badges">
                                        <span class="flag pending"><span></span>' . esc_html(__('Pending', 'wp-job-portal')) . '</span>
                                        </div>';
                } elseif ($wpjobportal_data->status == 1) {
                    $flaghtml = '<div class="approved-badge badges">
                                        <span class="flag approved"><span></span>' . esc_html(__('Approved', 'wp-job-portal')) . '</span>
                                        </div>';
                } else {
                    $flaghtml = '<div class="rejected-badge badges">
                                        <span class="flag rejected"><span></span>' . esc_html(__('Rejected', 'wp-job-portal')) . '</span>
                                        </div>';
                }


                $wpjobportal_html .= '<div class="widget-data-wrapper">
                                    <div class="left-data ' . $picstyle . '">
                                    <img class="left-data-img" src="' . $wpjobportal_path . '"/>
                                    </div>
                                    <div class="right-data">
                                        <div class="data-title">
                                        ' . $wpjobportal_data->title;
                if ($wpjobportal_data->isfeatured == 1) {
                    $wpjobportal_html .= '<span id="badge_featured" class="feature badge featured">' . esc_html(__('Featured', 'wp-job-portal')) . '</span>';
                }

                $wpjobportal_html .= '</div>
                                        <div class="data-data">
                                            <span class="heading">
                                            ' . esc_html(__('Category', 'wp-job-portal')) . ' :
                                            </span>
                                            <span class="text">
                                            ' . $wpjobportal_data->cat_title . '
                                            </span>
                                        </div>';
                if ($wpjobportal_dataid != 3) {
                    $wpjobportal_html .= '<div class="data-data">
                                                    <span class="heading">
                                                ' . esc_html(__('Location', 'wp-job-portal')) . ' :
                                                </span>
                                                <span class="text">
                                                ' . $wpjobportal_data->city . '
                                                </span>';
                } else {
                    $wpjobportal_html .= '<div class="data-data">
                                                    <span class="heading">
                                                ' . esc_html(__('Highest Education', 'wp-job-portal')) . ' :
                                                </span>
                                                <span class="text">
                                                ' . $wpjobportal_data->education . '
                                                </span>';
                }
                $wpjobportal_html .='
                                        </div>
                                        ' . $flaghtml . '
                                    </div>
                                </div>';
            }
        } elseif ($wpjobportal_dataid == 4) {
            $wpjobportal_html .= $this->getAppliedJobPopup($wpjobportal_results);
            return $wpjobportal_html;
        }
        $wpjobportal_html .= '<a href = "' . esc_url($wpjobportal_link) . '" class="popup-bottom-button">' . esc_html(__('Show More', 'wp-job-portal')) . '</a></div>';
        return $wpjobportal_html;
    }

//function to create popup of newest applied jobs
    function getAppliedJobPopup($wpjobportal_results) {
        $wpjobportal_html = '';
        $wpjobportal_wpdir = wp_upload_dir();
        $wpjobportal_data_directory = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('data_directory');
        foreach ($wpjobportal_results as $wpjobportal_data) {
            //photo / logo
            $wpjobportal_path = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('employer');
            if ($wpjobportal_data->logo != "") {
                $wpjobportal_path = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/employer/comp_' . $wpjobportal_data->id . '/logo/' . $wpjobportal_data->logo;
            }


            //flags expressing status
            $flaghtml = '';
            if ($wpjobportal_data->status == 2) {
                $flaghtml = '<div class="spam-badge badges">
                                        <span class="flag spam"><span></span>' . esc_html(__('Spam', 'wp-job-portal')) . '</span>
                                        </div>';
            } elseif ($wpjobportal_data->status == 3) {
                $flaghtml = '<div class="hired-badge badges">
                                        <span class="flag hired"><span></span>' . esc_html(__('Hired', 'wp-job-portal')) . '</span>
                                        </div>';
            } elseif ($wpjobportal_data->status == 4) {
                $flaghtml = '<div class="reject-badge badges">
                                        <span class="flag reject"><span></span>' . esc_html(__('Rejected', 'wp-job-portal')) . '</span>
                                        </div>';
            } elseif ($wpjobportal_data->status == 5) {
                $flaghtml = '<div class="shortlisted-badge badges">
                                        <span class="flag shortlisted"><span></span>' . esc_html(__('Short listed', 'wp-job-portal')) . '</span>
                                        </div>';
            }


            $wpjobportal_html .= '<div class="widget-data-wrapper">
                                    <div class="left-data">
                                        <img class="left-data-img" src="' . $wpjobportal_path . '"/>
                                    </div>
                                    <div class="right-data">
                                        <div class="data-title">
                                        ' . $wpjobportal_data->title;
            if ($wpjobportal_data->isfeatured == 1) {
                $wpjobportal_html .= '<span id="badge_featured" class="feature badge featured">' . esc_html(__('Featured', 'wp-job-portal')) . '</span>';
            }

            $wpjobportal_html .= '</div>
                                        <div class="data-data">
                                            <span class="heading">
                                            ' . esc_html(__('Applicant', 'wp-job-portal')) . ' :
                                            </span>
                                            <span class="text">
                                            ' . $wpjobportal_data->name . '
                                            </span>
                                        </div>';
            $wpjobportal_html .= '<div class="data-data">
                                                    <span class="heading">
                                                ' . esc_html(__('Applied Date', 'wp-job-portal')) . ' :
                                                </span>
                                                <span class="text">
                                                ' . $wpjobportal_data->apply_date . '
                                                </span>';

            $wpjobportal_html .='
                                        </div>
                                        ' . $flaghtml . '
                                    </div>

                        </div>';
        }
        $wpjobportal_html .= '</div>';
        return $wpjobportal_html;
    }

    // function getLatestResumes() {
    //     if(!is_numeric($wpjobportal_uid)){
    //         return false;
    //     }
    //     $query = "SELECT resume.id,resume.first_name,resume.last_name,resume.application_title as applicationtitle,CONCAT(resume.alias,'-',resume.id) resumealiasid,resume.email_address,category.cat_title,resume.experienceid,resume.created,jobtype.title AS jobtypetitle,resume.photo,resume.salaryfixed as salary,resume.isfeaturedresume,resume.status,city.name AS cityname,state.name AS statename,country.name AS countryname,resume.endfeatureddate,resume.params,resume.last_modified,LOWER(jobtype.title) AS jobtypetit,jobtype.color as jobtypecolor
    //             FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume
    //             JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS category ON category.id = resume.job_category
    //             LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON jobtype.id = resume.jobtype
    //             LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON city.id = (SELECT address_city FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumeaddresses` WHERE resumeid = resume.id LIMIT 1)
    //             LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_states` AS state ON state.id = city.stateid
    //             LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_countries` AS country ON country.id = city.countryid
    //             WHERE resume.uid = ". esc_sql($wpjobportal_uid);
    //             $query.=" ORDER BY resume.id ASC LIMIT 0,5 ";

    //     $wpjobportal_results = wpjobportal::$_db->get_results($query);
    //     $wpjobportal_data = array();
    //     foreach ($wpjobportal_results AS $d) {
    //         $d->location = wpjobportal::$_common->getLocationForView($d->cityname, $d->statename, $d->countryname);//  updated the query select to select 'name' as cityname
    //         $wpjobportal_data[] = $d;
    //     }
    //     wpjobportal::$wpjobportal_data['fields'] = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldsOrderingforView(3);
    //     wpjobportal::$_data[0]['latestresumes'] = $wpjobportal_data;
    //     wpjobportal::$_data['config'] = wpjobportal::$_config->getConfigByFor('resume');
    //     wpjobportal::$_data['listingfields'] = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldsForListing(3);
    //     return;
    // }

    function getNewestUsers($wpjobportal_role) {
        if (!is_numeric($wpjobportal_role))
            return false;
        $query = "SELECT u.id,CONCAT(u.first_name,' ',u.last_name) AS username,u.emailaddress AS email,u.created AS created
        FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` AS u
        WHERE u.roleid = " . esc_sql($wpjobportal_role) . " ORDER BY u.created DESC LIMIT 5";

        $wpjobportal_results = wpjobportal::$_db->get_results($query);
        //company logo for employer
        if ($wpjobportal_role == 1) {
            $wpjobportal_data = array();
            foreach ($wpjobportal_results AS $d) {
                if (!is_numeric($d->id)) {
                    continue;
                }
                $query = "SELECT logofilename AS photo,id AS companyid FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies`
                WHERE uid = " . esc_sql($d->id) . " ORDER BY logofilename DESC LIMIT 1";
                $wpjobportal_result = wpjobportal::$_db->get_row($query);
                if($wpjobportal_result){
                    $d->photo = $wpjobportal_result->photo;
                    $d->companyid = $wpjobportal_result->companyid;
                }else{
                    $d->photo = '';
                    $d->companyid = '';
                }
                $wpjobportal_data[] = $d;
            }
            $wpjobportal_results = $wpjobportal_data;
        }
        //resume photo  for jobseeker
        if ($wpjobportal_role == 2) {
            $wpjobportal_data = array();
            foreach ($wpjobportal_results AS $d) {
                if(!is_numeric($d->id)){
                    continue;
                }
                $query = "SELECT photo,id AS resumeid FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume`
                WHERE uid = " . esc_sql($d->id) . " ORDER BY photo DESC LIMIT 1";
                $wpjobportal_result = wpjobportal::$_db->get_row($query);
                if($wpjobportal_result){
                    $d->photo = $wpjobportal_result->photo;
                    $d->resumeid = $wpjobportal_result->resumeid;
                }else{
                    $d->photo = '';
                    $d->resumeid = '';
                }
                $wpjobportal_data[] = $d;
            }
            $wpjobportal_results = $wpjobportal_data;
        }
        $wpjobportal_html = $this->genrateUserWidget($wpjobportal_results, $wpjobportal_role);
        return $wpjobportal_html;
    }
    function WPJPcheck_autfored() {
        // Retrieve the option
        $wpjobportal_option_name = 'portledadofor_k';
        $wpjobportal_stored_data = get_option($wpjobportal_option_name);

        if ($wpjobportal_stored_data) {
            // Compare the encrypted value
            if ($wpjobportal_stored_data['wpjpkeyedfieldforkey']) {
                return $wpjobportal_stored_data['wpjpkeyedfieldforkey']; // Match found
            }
        }

        return; // No match
    }

    function genrateUserWidget($wpjobportal_results, $wpjobportal_role) {
        $wpjobportal_html = '';
        $wpjobportal_html .= '<div id="wp-job-portal-widget-wrapper">';
        $wpjobportal_wpdir = wp_upload_dir();
        $wpjobportal_data_directory = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('data_directory');
        foreach ($wpjobportal_results as $wpjobportal_data) {
            //name
            $wpjobportal_name = $wpjobportal_data->username;
            //photo code
            $wpjobportal_path = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('jobseeker');
            if ($wpjobportal_role == 1) {
                if ($wpjobportal_data->photo != "") {
                    $wpjobportal_path = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/employer/comp_' . $wpjobportal_data->companyid . '/logo/' . $wpjobportal_data->photo;
                }
            } elseif ($wpjobportal_data->photo != "") {
                $wpjobportal_path = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/jobseeker/resume_' . $wpjobportal_data->resumeid . '/photo/' . $wpjobportal_data->photo;
            }
            //photo code
            $wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
            $wpjobportal_html .= '<div class="users-widget-data">
                        <img class="photo" src="' . esc_url($wpjobportal_path) . '"/>
                        <div class="widget-data-upper">
                            <a href="'.esc_url_raw(admin_url('admin.php?page=wpjobportal_user&wpjobportallt=userdetail&id='.esc_attr($wpjobportal_data->id))).'">
                                '. esc_html($wpjobportal_name) .'
                            </a>
                            <span class="Widget-data-date">( ' . esc_html(date_i18n($wpjobportal_dateformat, strtotime($wpjobportal_data->created))) . ' )</span>
                        </div>
                        <div class="widget-data-lower">
                            ' . esc_html($wpjobportal_data->email) . '
                        </div>
                    </div>';
        }

        $wpjobportal_html .= '</div>';
        return $wpjobportal_html;
    }

    function getListTranslations() {

        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'get-list-translations') ) {
            die( 'Security check Failed' );
        }

        $wpjobportal_result = array();
        $wpjobportal_result['error'] = false;

        $wpjobportal_path = WPJOBPORTAL_PLUGIN_PATH.'languages';

        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
        if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ) {
            $creds = request_filesystem_credentials( site_url() );
            wp_filesystem( $creds );
        }


        if( ! $wp_filesystem->is_writable($wpjobportal_path)){
            $wpjobportal_result['error'] = esc_html(__('Dir is not writable','wp-job-portal')).' '.$wpjobportal_path;

        }else{

            if($this->isConnected()){

                $wpjobportal_version = WPJOBPORTALIncluder::getJSModel('configuration')->getConfigByFor('default');

                $post_data = array();
                $wpjobportal_url = "http://www.joomsky.com/translations/api/1.0/index.php";
                $post_data['product'] ='wp-job-portal-wp';
                $post_data['domain'] = get_site_url();
                $post_data['producttype'] = $wpjobportal_version['producttype'];
                $post_data['productcode'] = 'wpjobportal';
                $post_data['productversion'] = $wpjobportal_version['version'];
                $post_data['JVERSION'] = get_bloginfo('version');
                $post_data['method'] = 'getTranslations';

                $response = wp_remote_post( $wpjobportal_url, array('body' => $post_data,'timeout'=>7,'sslverify'=>false));
                if( !is_wp_error($response) && $response['response']['code'] == 200 && isset($response['body']) ){
                    $call_result = $response['body'];
                }else{
                    $call_result = false;
                    if(!is_wp_error($response)){
                       $error = $response['response']['message'];
                   }else{
                        $error = $response->get_error_message();
                   }
                }

                $wpjobportal_result['data'] = $call_result;
                if(!$call_result){
                    $wpjobportal_result['error'] = $error;
                }
            }else{
                $wpjobportal_result['error'] = esc_html(__('Unable to connect to server','wp-job-portal'));
            }
        }

        $wpjobportal_result = wp_json_encode($wpjobportal_result);

        return $wpjobportal_result;
    }

    function makeLanguageCode($wpjobportal_lang_name){
        $wpjobportal_langarray = wp_get_installed_translations('core');
        $wpjobportal_langarray = $wpjobportal_langarray['default'];
        $wpjobportal_match = false;
        if(array_key_exists($wpjobportal_lang_name, $wpjobportal_langarray)){
            $wpjobportal_lang_name = $wpjobportal_lang_name;
            $wpjobportal_match = true;
        }else{
            $m_lang = '';
            foreach($wpjobportal_langarray AS $wpjobportal_k => $v){
                if($wpjobportal_lang_name[0].$wpjobportal_lang_name[1] == $wpjobportal_k[0].$wpjobportal_k[1]){
                    $m_lang .= $wpjobportal_k.', ';
                }
            }

            if($m_lang != ''){
                $m_lang = wpjobportalphplib::wpJP_substr($m_lang, 0,wpjobportalphplib::wpJP_strlen($m_lang) - 2);
                $wpjobportal_lang_name = $m_lang;
                $wpjobportal_match = 2;
            }else{
                $wpjobportal_lang_name = $wpjobportal_lang_name;
                $wpjobportal_match = false;
            }
        }

        return array('match' => $wpjobportal_match , 'lang_name' => $wpjobportal_lang_name);
    }

    function validateAndShowDownloadFileName( ){

        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'validate-and-show-download-filename') ) {
            die( 'Security check Failed' );
        }
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
        if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ) {
            $creds = request_filesystem_credentials( site_url() );
            wp_filesystem( $creds );
        }

        $wpjobportal_lang_name = WPJOBPORTALrequest::getVar('langname');
        if($wpjobportal_lang_name == '') return '';
        $wpjobportal_result = array();
        $f_result = $this->makeLanguageCode($wpjobportal_lang_name);
        $wpjobportal_path = WPJOBPORTAL_PLUGIN_PATH.'languages';
        $wpjobportal_result['error'] = false;
        if($f_result['match'] === false){
            $wpjobportal_result['error'] = $wpjobportal_lang_name. ' ' . esc_html(__('Language is not installed','wp-job-portal'));
        }elseif( ! $wp_filesystem->is_writable($wpjobportal_path)){
            $wpjobportal_result['error'] = $wpjobportal_lang_name. ' ' . esc_html(__('Language directory is not writeable','wp-job-portal')).': '.$wpjobportal_path;
        }else{
            $wpjobportal_result['input'] = '<input id="languagecode" class="text_area" type="text" value="'.esc_attr($wpjobportal_lang_name).'" name="languagecode">';
            if($f_result['match'] === 2){
                $wpjobportal_result['input'] .= '<div id="js-emessage-wrapper" style="display:block;margin:20px 0px 20px;">';
                $wpjobportal_result['input'] .= esc_html(__('Required language is not installed but similar language[s] like','wp-job-portal')).': "<b>'.esc_html($f_result['lang_name']).'</b>" '.esc_html(__('is found in your system','wp-job-portal'));
                $wpjobportal_result['input'] .= '</div>';

            }
            $wpjobportal_result['path'] = esc_html(__('Language code','wp-job-portal'));
        }
        $wpjobportal_result = wp_json_encode($wpjobportal_result);
        return $wpjobportal_result;
    }

    function getLanguageTranslation(){

        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'get-language-translation') ) {
            die( 'Security check Failed' );
        }
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
        if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ) {
            $creds = request_filesystem_credentials( site_url() );
            wp_filesystem( $creds );
        }


        $wpjobportal_lang_name = WPJOBPORTALrequest::getVar('langname');
        $wpjobportal_language_code = WPJOBPORTALrequest::getVar('filename');

        $wpjobportal_result = array();
        $wpjobportal_result['error'] = false;
        $wpjobportal_path = WPJOBPORTAL_PLUGIN_PATH.'languages';

        if($wpjobportal_lang_name == '' || $wpjobportal_language_code == ''){
            $wpjobportal_result['error'] = esc_html(__('Empty values','wp-job-portal'));
            return wp_json_encode($wpjobportal_result);
        }

        $final_path = $wpjobportal_path.'/wp-job-portal-'.$wpjobportal_language_code.'.po';


        $wpjobportal_langarray = wp_get_installed_translations('core');
        $wpjobportal_langarray = $wpjobportal_langarray['default'];

        if(!array_key_exists($wpjobportal_language_code, $wpjobportal_langarray)){
            $wpjobportal_result['error'] = $wpjobportal_lang_name. ' ' . esc_html(__('Language is not installed','wp-job-portal'));
            return wp_json_encode($wpjobportal_result);
        }elseif( ! $wp_filesystem->is_writable($wpjobportal_path)){
            $wpjobportal_result['error'] = $wpjobportal_lang_name. ' ' . esc_html(__('Language directory is not writable','wp-job-portal')).': '.$wpjobportal_path;
            return wp_json_encode($wpjobportal_result);
        }

        if( ! $wp_filesystem->exists($final_path)){
            //touch($final_path);
        }

        if( ! $wp_filesystem->is_writable($final_path)){
            $wpjobportal_result['error'] = esc_html(__('File is not writable','wp-job-portal')).': '.$final_path;
        }else{

            if($this->isConnected()){

                $wpjobportal_version = WPJOBPORTALIncluder::getJSModel('configuration')->getConfigByFor('version');

                $wpjobportal_url = "http://www.joomsky.com/translations/api/1.0/index.php";
                $post_data['product'] ='wp-job-portal-wp';
                $post_data['domain'] = get_site_url();
                $post_data['producttype'] = $wpjobportal_version['versiontype'];
                $post_data['productcode'] = 'wpjobportal';
                $post_data['productversion'] = $wpjobportal_version['version'];
                $post_data['JVERSION'] = get_bloginfo('version');
                $post_data['translationcode'] = $wpjobportal_lang_name;
                $post_data['method'] = 'getTranslationFile';

                $curl_response = wp_remote_post($wpjobportal_url,array('body'=>$post_data));
                if( !is_wp_error($curl_response) && $curl_response['response']['code'] == 200 && isset($curl_response['body']) ){
                    $response = $curl_response['body'];
                    $wpjobportal_array = json_decode($response, true);
                    $ret = $this->writeLanguageFile( $final_path , $wpjobportal_array['file']);
                    if($ret != false){
                        $wpjobportal_url = "https://www.joomsky.com/translations/api/1.0/index.php";
                        $post_data['product'] ='wp-job-portal';
                        $post_data['domain'] = get_site_url();
                        // $post_data['producttype'] = $wpjobportal_version['versiontype'];
                        $post_data['productcode'] = 'wpjobportal';
                        $post_data['productversion'] = $wpjobportal_version['productversion'];
                        $post_data['JVERSION'] = get_bloginfo('version');
                        $post_data['folder'] = $wpjobportal_array['foldername'];
                        $curl_response = wp_remote_post($wpjobportal_url,array('body'=>$post_data));
                        $response = $curl_response['body'];
                    }
                    $wpjobportal_result['data'] = esc_html(__('File Downloaded Successfully','wp-job-portal'));
                }else{
                    $wpjobportal_result['error'] = $curl_response->get_error_message();
                }
            }else{
                $wpjobportal_result['error'] = esc_html(__('Unable to connect to server','wp-job-portal'));
            }
        }

        $wpjobportal_result = wp_json_encode($wpjobportal_result);

        return $wpjobportal_result;

    }

    function writeLanguageFile( $wpjobportal_path , $wpjobportal_url ){
        do_action('wpjobportal_load_wp_admin_file');

        $tmpfile = download_url( $wpjobportal_url);
        copy( $tmpfile, $wpjobportal_path );
        @wp_delete_file( $tmpfile ); // must wp_delete_file afterwards

        //make mo for po file
        $this->phpmo_convert($wpjobportal_path);
        return $wpjobportal_result;
    }

    function isConnected(){
        $wpjobportal_url = "www.google.com";
        $response = wp_remote_get($wpjobportal_url, array('timeout' => 30));
        if (is_wp_error($response)) {
            $wpjobportal_is_conn = false; //action in connection failure
        }else{
            $wpjobportal_is_conn = true; //action when connected
        }
        return $wpjobportal_is_conn;
    }

    function phpmo_convert($wpjobportal_input, $output = false) {
        if ( !$output )
            $output = wpjobportalphplib::wpJP_str_replace( '.po', '.mo', $wpjobportal_input );
        $hash = $this->phpmo_parse_po_file( $wpjobportal_input );
        if ( $hash === false ) {
            return false;
        } else {
            $this->phpmo_write_mo_file( $hash, $output );
            return true;
        }
    }

    function phpmo_clean_helper($x) {
        if (is_array($x)) {
            foreach ($x as $wpjobportal_k => $v) {
                $x[$wpjobportal_k] = $this->phpmo_clean_helper($v);
            }
        } else {
            if ($x[0] == '"')
                $x = wpjobportalphplib::wpJP_substr($x, 1, -1);
            $x = wpjobportalphplib::wpJP_str_replace("\"\n\"", '', $x);
            $x = wpjobportalphplib::wpJP_str_replace('$', '\\$', $x);
        }
        return $x;
    }
    /* Parse gettext .po files. */
    /* @link http://www.gnu.org/software/gettext/manual/gettext.html#PO-Files */
    function phpmo_parse_po_file($wpjobportal_in) {
    if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
    if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ) {
        $creds = request_filesystem_credentials( site_url() );
        wp_filesystem( $creds );
    }

    if (!$wp_filesystem->exists($wpjobportal_in)){ return false; }
    $wpjobportal_ids = array();
    $wpjobportal_strings = array();
    $wpjobportal_language = array();
    $lines = file($wpjobportal_in);
    foreach ($lines as $line_num => $line) {
        if (wpjobportalphplib::wpJP_strstr($line, 'msgid')){
            $wpjobportal_endpos = strrchr($line, '"');
            $wpjobportal_id = wpjobportalphplib::wpJP_substr($line, 7, $wpjobportal_endpos-2);
            $wpjobportal_ids[] = $wpjobportal_id;
        }elseif(wpjobportalphplib::wpJP_strstr($line, 'msgstr')){
            $wpjobportal_endpos = strrchr($line, '"');
            $wpjobportal_string = wpjobportalphplib::wpJP_substr($line, 8, $wpjobportal_endpos-2);
            $wpjobportal_strings[] = array($wpjobportal_string);
        }else{}
    }
    for ($wpjobportal_i=0; $wpjobportal_i<count($wpjobportal_ids); $wpjobportal_i++){
        //Shoaib
        if(isset($wpjobportal_ids[$wpjobportal_i]) && isset($wpjobportal_strings[$wpjobportal_i])){
            if($wpjobportal_entry['msgstr'][0] == '""'){
                continue;
            }
            $wpjobportal_language[$wpjobportal_ids[$wpjobportal_i]] = array('msgid' => $wpjobportal_ids[$wpjobportal_i], 'msgstr' =>$wpjobportal_strings[$wpjobportal_i]);
        }
    }
    return $wpjobportal_language;
    }
    /* Write a GNU gettext style machine object. */
    /* @link http://www.gnu.org/software/gettext/manual/gettext.html#MO-Files */
    function phpmo_write_mo_file($hash, $out) {
        // sort by msgid
        ksort($hash, SORT_STRING);
        // our mo file data
        $mo = '';
        // header data
        $offsets = array ();
        $wpjobportal_ids = '';
        $wpjobportal_strings = '';
        foreach ($hash as $wpjobportal_entry) {
            $wpjobportal_id = $wpjobportal_entry['msgid'];
            $wpjobportal_str = implode("\x00", $wpjobportal_entry['msgstr']);
            // keep track of offsets
            $offsets[] = array (
                            wpjobportalphplib::wpJP_strlen($wpjobportal_ids), wpjobportalphplib::wpJP_strlen($wpjobportal_id), wpjobportalphplib::wpJP_strlen($wpjobportal_strings), wpjobportalphplib::wpJP_strlen($wpjobportal_str)
                            );
            // plural msgids are not stored (?)
            $wpjobportal_ids .= $wpjobportal_id . "\x00";
            $wpjobportal_strings .= $wpjobportal_str . "\x00";
        }
        // keys start after the header (7 words) + index tables ($#hash * 4 words)
        $wpjobportal_key_start = 7 * 4 + sizeof($hash) * 4 * 4;
        // values start right after the keys
        $wpjobportal_value_start = $wpjobportal_key_start +wpjobportalphplib::wpJP_strlen($wpjobportal_ids);
        // first all key offsets, then all value offsets
        $wpjobportal_key_offsets = array ();
        $wpjobportal_value_offsets = array ();
        // calculate
        foreach ($offsets as $v) {
            list ($o1, $l1, $o2, $l2) = $v;
            $wpjobportal_key_offsets[] = $l1;
            $wpjobportal_key_offsets[] = $o1 + $wpjobportal_key_start;
            $wpjobportal_value_offsets[] = $l2;
            $wpjobportal_value_offsets[] = $o2 + $wpjobportal_value_start;
        }
        $offsets = array_merge($wpjobportal_key_offsets, $wpjobportal_value_offsets);
        // write header
        $mo .= pack('Iiiiiii', 0x950412de, // magic number
        0, // version
        sizeof($hash), // number of entries in the catalog
        7 * 4, // key index offset
        7 * 4 + sizeof($hash) * 8, // value index offset,
        0, // hashtable size (unused, thus 0)
        $wpjobportal_key_start // hashtable offset
        );
        // offsets
        foreach ($offsets as $offset)
            $mo .= pack('i', $offset);
        // ids
        $mo .= $wpjobportal_ids;
        // strings
        $mo .= $wpjobportal_strings;

        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
        if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ) {
            $creds = request_filesystem_credentials( site_url() );
            wp_filesystem( $creds );
        }
        $wp_filesystem->put_contents( $out, $mo);
    }

    function updateDate($wpjobportal_addon_name,$wpjobportal_plugin_version){
        return WPJOBPORTALincluder::getJSModel('premiumplugin')->verfifyAddonActivation($wpjobportal_addon_name);
    }

    function getAddonSqlForActivation($wpjobportal_addon_name,$wpjobportal_addon_version){
        return WPJOBPORTALincluder::getJSModel('premiumplugin')->verifyAddonSqlFile($wpjobportal_addon_name,$wpjobportal_addon_version);
    }

    function storeOrderingFromPage($wpjobportal_data) {//
        if (empty($wpjobportal_data)) {
            return false;
        }
        $sorted_array = array();
        wpjobportalphplib::wpJP_parse_str($wpjobportal_data['fields_ordering_new'],$sorted_array);
        $sorted_array = reset($sorted_array);
        if(!empty($sorted_array)){
            if($wpjobportal_data['ordering_for'] == 'fieldordering'){
                $wpjobportal_row = WPJOBPORTALincluder::getJSTable('fieldsordering');
                $ordering_coloumn = 'ordering';
                $wpjobportal_msgkey = WPJOBPORTALincluder::getJSModel('fieldordering')->getMessagekey();
            }
            $page_multiplier = 1;
            if($wpjobportal_data['pagenum_for_ordering'] > 1){
                $page_multiplier = ($wpjobportal_data['pagenum_for_ordering'] - 1) * wpjobportal::$_configuration['pagination_default_page_size'] + 1;
            }
            for ($wpjobportal_i=0; $wpjobportal_i < count($sorted_array) ; $wpjobportal_i++) {
                $wpjobportal_row->update(array('id' => $sorted_array[$wpjobportal_i], $ordering_coloumn => $page_multiplier + $wpjobportal_i));
            }
        }
        WPJOBPORTALMessages::setLayoutMessage(esc_html(__('Ordering updated', 'wp-job-portal')), 'updated', $wpjobportal_msgkey);
        return ;
    }

    function checkWPJPAddoneInfo($wpjobportal_name){
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
        if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ) {
            $creds = request_filesystem_credentials( site_url() );
            wp_filesystem( $creds );
        }

        $wpjobportal_slug = $wpjobportal_name.'/'.$wpjobportal_name.'.php';
        if($wp_filesystem->exists(WP_PLUGIN_DIR . '/'.$wpjobportal_slug) && is_plugin_active($wpjobportal_slug)){
            $wpjobportal_status = __("Activated","wp-job-portal");
            $wpjobportal_action = __("Deactivate","wp-job-portal");
            $wpjobportal_actionClass = 'wpjp-admin-adons-status-Deactive';
            $wpjobportal_url = "plugins.php?s=".$wpjobportal_name."&plugin_status=active";
            $wpjobportal_disabled = "disabled";
            $wpjobportal_class = "js-btn-activated";
            $availability = "-1";
            $wpjobportal_version = "";
        }else if($wp_filesystem->exists(WP_PLUGIN_DIR . '/'.$wpjobportal_slug) && !is_plugin_active($wpjobportal_slug)){
            $wpjobportal_status = __("Deactivated","wp-job-portal");
            $wpjobportal_action = __("Activate","wp-job-portal");
            $wpjobportal_actionClass = 'wpjp-admin-adons-status-Active';
            $wpjobportal_url = "plugins.php?s=".$wpjobportal_name."&plugin_status=inactive";
            $wpjobportal_disabled = "";
            $wpjobportal_class = "js-btn-green js-btn-active-now";
            $availability = "1";
            $wpjobportal_version = "";
        }else if(!$wp_filesystem->exists(WP_PLUGIN_DIR . '/'.$wpjobportal_slug)){
            $wpjobportal_status = __("Not Installed","wp-job-portal");
            $wpjobportal_action = __("Install Now","wp-job-portal");
            $wpjobportal_actionClass = 'wpjp-admin-adons-status-Install';
            $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_premiumplugin&wpjobportallt=step1"));
            $wpjobportal_disabled = "";
            $wpjobportal_class = "js-btn-install-now";
            $availability = "0";
            $wpjobportal_version = "---";
        }
        return array("status" => $wpjobportal_status, "action" => $wpjobportal_action, "url" => $wpjobportal_url, "disabled" => $wpjobportal_disabled, "class" => $wpjobportal_class, "availability" => $availability, "actionClass" => $wpjobportal_actionClass, "version" => $wpjobportal_version);
    }

    function downloadandinstalladdonfromAjax(){
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'download-and-install-addon') ) {
            die( 'Security check Failed' );
        }

        $wpjobportal_key = WPJOBPORTALrequest::getVar('dataFor');
        $wpjobportal_installedversion = WPJOBPORTALrequest::getVar('currentVersion');
        $wpjobportal_newversion = WPJOBPORTALrequest::getVar('cdnVersion');
        $wpjobportal_addon_json_array = array();

        if($wpjobportal_key != ''){
            $wpjobportal_addon_json_array[] = str_replace('wp-job-portal-', '', $wpjobportal_key);
            $wpjobportal_plugin_slug = str_replace('wp-job-portal-', '', $wpjobportal_key);
        }
        $wpjobportal_token = get_option('transaction_key_for_'.$wpjobportal_key);
        $wpjobportal_result = array();
        $wpjobportal_result['error'] = false;
        if($wpjobportal_token == ''){
            $wpjobportal_result['error'] = esc_html(__('Addon Installation Failed','wp-job-portal'));
            $wpjobportal_result = wp_json_encode($wpjobportal_result);
            return $wpjobportal_result;
        }
        $site_url = site_url();
        if($site_url != ''){
            $site_url = str_replace("https://","",$site_url);
            $site_url = str_replace("http://","",$site_url);
        }
        $wpjobportal_url = 'https://wpjobportal.com/setup/index.php?token='.$wpjobportal_token.'&productcode='. wp_json_encode($wpjobportal_addon_json_array).'&domain='.$site_url;
        // verify token
        $verifytransactionkey = $this->verifytransactionkey($wpjobportal_token, $wpjobportal_url);
        if($verifytransactionkey['status'] == 0){
            $wpjobportal_result['error'] = $verifytransactionkey['message'];
            $wpjobportal_result = wp_json_encode($wpjobportal_result);
            return $wpjobportal_result;
        }
        $wpjobportal_install_count = 0;

        $wpjobportal_installed = $this->install_plugin($wpjobportal_url);
        if ( !is_wp_error( $wpjobportal_installed ) && $wpjobportal_installed ) {
            // had to run two seprate loops to save token for all the addons even if some error is triggered by activation.
            if(strstr($wpjobportal_key, 'wp-job-portal-')){
                update_option('transaction_key_for_'.$wpjobportal_key,$wpjobportal_token);
            }

            if(strstr($wpjobportal_key, 'wp-job-portal-')){
                $wpjobportal_activate = activate_plugin( $wpjobportal_key.'/'.$wpjobportal_key.'.php' );
                $wpjobportal_install_count++;
            }

            // run update sql
            if ($wpjobportal_installedversion != $wpjobportal_newversion) {
                $wpjobportal_optionname = 'wpjobportal-addon-'. $wpjobportal_plugin_slug .'s-version';
                update_option($wpjobportal_optionname, $wpjobportal_newversion);
                $wpjobportal_plugin_path = WP_CONTENT_DIR;
                $wpjobportal_plugin_path = $wpjobportal_plugin_path.'/plugins/'.$wpjobportal_key.'/includes';
                if(is_dir($wpjobportal_plugin_path . '/sql/') && is_readable($wpjobportal_plugin_path . '/sql/')){
                    if($wpjobportal_installedversion != ''){
                        $wpjobportal_installedversion = str_replace('.','', $wpjobportal_installedversion);
                    }
                    if($wpjobportal_newversion != ''){
                        $wpjobportal_newversion = str_replace('.','', $wpjobportal_newversion);
                    }
                    WPJOBPORTALincluder::getJSModel('premiumplugin')->getAddonUpdateSqlFromUpdateDir($wpjobportal_installedversion,$wpjobportal_newversion,$wpjobportal_plugin_path . '/sql/');
                    $wpjobportal_updatesdir = $wpjobportal_plugin_path.'/sql/';
                    if(preg_match('/wp-job-portal-[a-zA-Z]+/', $wpjobportal_updatesdir)){
                        $this->wpjpRemoveAddonUpdatesFolder($wpjobportal_updatesdir);
                    }
                }else{
                    WPJOBPORTALincluder::getJSModel('premiumplugin')->getAddonUpdateSqlFromLive($wpjobportal_installedversion,$wpjobportal_newversion,$wpjobportal_plugin_slug);
                }
            }

        }else{
            $wpjobportal_result['error'] = esc_html(__('Addon Installation Failed','wp-job-portal'));
            $wpjobportal_result = wp_json_encode($wpjobportal_result);
            return $wpjobportal_result;
        }

        $wpjobportal_result['success'] = esc_html(__('Addon Installed Successfully','wp-job-portal'));
        $wpjobportal_result = wp_json_encode($wpjobportal_result);
        return $wpjobportal_result;
    }

    function WPJPAddonsAutoUpdate(){
        /*
            code for auto update check from configuration
        */

        $wpjobportal_addons_auto_update = wpjobportal::$_config->getConfigValue('wpjobportal_addons_auto_update');
        if( $wpjobportal_addons_auto_update != 1){
            return;
        }

        require_once WPJOBPORTAL_PLUGIN_PATH.'includes/addon-updater/wpjobportalupdater.php';
        $WPJOBPORTAL_JOBPORTALUpdater  = new WPJOBPORTAL_JOBPORTALUpdater();
        $wpjobportal_cdnversiondata = $WPJOBPORTAL_JOBPORTALUpdater->getPluginVersionDataFromCDN();

        $wpjobportal_addons = $this->getWPJPAddonsArray();
        $wpjobportal_installed_plugins = get_plugins();
        $wpjobportal_need_to_update = array();
        $wpjobportal_addon_json_array = array();

        $wpjobportal_status_prefix = 'key_status_for_wp-job-portal_';
        foreach ($wpjobportal_addons as $wpjobportal_key1 => $wpjobportal_value1) {
            $wpjobportal_matched = 0;
            $wpjobportal_version = "";
            foreach ($wpjobportal_installed_plugins as $wpjobportal_name => $wpjobportal_value) {
                $wpjobportal_install_plugin_name = wpjobportalphplib::wpJP_str_replace(".php","",wpjobportalphplib::wpJP_basename($wpjobportal_name));
                if($wpjobportal_key1 == $wpjobportal_install_plugin_name){
                    $wpjobportal_matched = 1;
                    $wpjobportal_version = $wpjobportal_value["Version"];
                    $wpjobportal_install_plugin_matched_name = $wpjobportal_install_plugin_name;
                }
            }
            if($wpjobportal_matched == 1){ //installed
                $wpjobportal_name = $wpjobportal_key1;
                $title = $wpjobportal_value1['title'];
                $wpjobportal_cdnavailableversion = "";
                if(is_array($wpjobportal_cdnversiondata)){ // log error for null instead of array
                    foreach ($wpjobportal_cdnversiondata as $wpjobportal_cdnname => $wpjobportal_cdnversion) {
                        $wpjobportal_install_plugin_name_simple = wpjobportalphplib::wpJP_str_replace("-", "", $wpjobportal_install_plugin_matched_name);
                        if($wpjobportal_cdnname == wpjobportalphplib::wpJP_str_replace("-", "", $wpjobportal_install_plugin_matched_name)){
                            if($wpjobportal_cdnversion > $wpjobportal_version){ // new version available
                                $wpjobportal_status = 'update_available';
                                $wpjobportal_cdnavailableversion = $wpjobportal_cdnversion;
                                $wpjobportal_plugin_slug = wpjobportalphplib::wpJP_str_replace('wp-job-portal-', '', $wpjobportal_name);
                                // check key status
                                $wpjobportal_token = get_option('transaction_key_for_'.esc_attr($wpjobportal_name));
                                $wpjobportal_key_local_status = get_option($wpjobportal_status_prefix . $wpjobportal_token);
                                if($wpjobportal_key_local_status == 1){
                                    $wpjobportal_need_to_update[] = array("name" => $wpjobportal_name, "current_version" => $wpjobportal_version, "available_version" => $wpjobportal_cdnavailableversion, "plugin_slug" => $wpjobportal_plugin_slug );
                                    $wpjobportal_addon_json_array[] = wpjobportalphplib::wpJP_str_replace('wp-job-portal-', '', $wpjobportal_name);
                                }
                            }
                        }
                    }
                }
            }
        }
        $wpjobportal_token = "";
        if(is_array($wpjobportal_need_to_update)){
            if(isset($wpjobportal_need_to_update[0])){
                $wpjobportal_key = $wpjobportal_need_to_update[0]["name"];
                $wpjobportal_token = get_option('transaction_key_for_'.esc_attr($wpjobportal_key));
            }
            if($wpjobportal_token == ''){
                return;
            }

            $site_url = site_url();
            if($site_url != ''){
                $site_url = wpjobportalphplib::wpJP_str_replace("https://","",$site_url);
                $site_url = wpjobportalphplib::wpJP_str_replace("http://","",$site_url);
            }
            $wpjobportal_url = 'https://wpjobportal.com/setup/index.php?token='.esc_attr($wpjobportal_token).'&productcode='. wp_json_encode($wpjobportal_addon_json_array).'&domain='.$site_url;
            // verify token
            $verifytransactionkey = $this->verifytransactionkey($wpjobportal_token, $wpjobportal_url);
            if($verifytransactionkey['status'] == 0){
                return;
            }

            $wpjobportal_installed = $this->install_plugin($wpjobportal_url);
            if ( !is_wp_error( $wpjobportal_installed ) && $wpjobportal_installed ) {
                // had to run two seprate loops to save token for all the addons even if some error is triggered by activation.

                // run update sql
                foreach($wpjobportal_need_to_update AS $wpjobportal_update){
                    $wpjobportal_installedversion = $wpjobportal_update["current_version"];
                    $wpjobportal_newversion = $wpjobportal_update["available_version"];
                    $wpjobportal_plugin_slug = $wpjobportal_update["plugin_slug"];
                    $wpjobportal_key = $wpjobportal_update["name"];
                    if ($wpjobportal_installedversion != $wpjobportal_newversion) {
                        $wpjobportal_optionname = 'wpjobportal-addon-'. $wpjobportal_plugin_slug .'s-version';
                        update_option($wpjobportal_optionname, $wpjobportal_newversion);
                        $wpjobportal_plugin_path = WP_CONTENT_DIR;
                        $wpjobportal_plugin_path = $wpjobportal_plugin_path.'/plugins/'.$wpjobportal_key.'/includes';
                        if(is_dir($wpjobportal_plugin_path . '/sql/') && is_readable($wpjobportal_plugin_path . '/sql/')){
                            if($wpjobportal_installedversion != ''){
                                $wpjobportal_installedversion = str_replace('.','', $wpjobportal_installedversion);
                            }
                            if($wpjobportal_newversion != ''){
                                $wpjobportal_newversion = str_replace('.','', $wpjobportal_newversion);
                            }
                            WPJOBPORTALincluder::getJSModel('premiumplugin')->getAddonUpdateSqlFromUpdateDir($wpjobportal_installedversion,$wpjobportal_newversion,$wpjobportal_plugin_path . '/sql/');
                            $wpjobportal_updatesdir = $wpjobportal_plugin_path.'/sql/';
                            if(preg_match('/wp-job-portal-[a-zA-Z]+/', $wpjobportal_updatesdir)){
                                $this->wpjpRemoveAddonUpdatesFolder($wpjobportal_updatesdir);
                            }
                        }else{
                            WPJOBPORTALincluder::getJSModel('premiumplugin')->getAddonUpdateSqlFromLive($wpjobportal_installedversion,$wpjobportal_newversion,$wpjobportal_plugin_slug);
                        }
                    }
                }

            }else{
                return;
            }
        }
        return;
    }
    function verifytransactionkey($wpjobportal_transactionkey, $wpjobportal_url){
        $message = 1;
        if($wpjobportal_transactionkey != ''){
            $response = wp_remote_post( $wpjobportal_url );
            if( !is_wp_error($response) && $response['response']['code'] == 200 && isset($response['body']) ){
                $wpjobportal_result = $response['body'];
                $wpjobportal_result = json_decode($wpjobportal_result,true);
                if(is_array($wpjobportal_result) && isset($wpjobportal_result[0]) && $wpjobportal_result[0] == 0){
                    $wpjobportal_result['status'] = 0;
                } else{
                    $wpjobportal_result['status'] = 1;
                }
            }else{
                $wpjobportal_result = false;
                if(!is_wp_error($response)){
                   $error = $response['response']['message'];
                }else{
                    $error = $response->get_error_message();
                }
            }
            if(is_array($wpjobportal_result) && isset($wpjobportal_result['status']) && $wpjobportal_result['status'] == 1 ){ // means everthing ok
                $message = 1;
            }else{
                if(isset($wpjobportal_result[0]) && $wpjobportal_result[0] == 0){
                    $error = $wpjobportal_result[1];
                }elseif(isset($wpjobportal_result['error']) && $wpjobportal_result['error'] != ''){
                    $error = $wpjobportal_result['error'];
                }
                $message = 0;
            }
        }else{
            $message = 0;
            $error = esc_html(__('Please insert activation key to proceed','wp-job-portal')).'!';
        }
        $wpjobportal_array['data'] = array();
        if ($message == 0) {
            $wpjobportal_array['status'] = 0;
            $wpjobportal_array['message'] = $error;
        } else {
            $wpjobportal_array['status'] = 1;
            $wpjobportal_array['message'] = 'success';
        }
        return $wpjobportal_array;
    }

    function install_plugin( $wpjobportal_plugin_zip ) {

        do_action('wpjobportal_load_wp_admin_file');
        WP_Filesystem();

        $tmpfile = download_url( $wpjobportal_plugin_zip);

        if ( !is_wp_error( $tmpfile ) && $tmpfile ) {
            $wpjobportal_plugin_path = WP_CONTENT_DIR;
            $wpjobportal_plugin_path = $wpjobportal_plugin_path.'/plugins/';
            $wpjobportal_path = WPJOBPORTAL_PLUGIN_PATH.'addon.zip';

            copy( $tmpfile, $wpjobportal_path );

            $unzipfile = unzip_file( $wpjobportal_path, $wpjobportal_plugin_path);

            @wp_delete_file( $wpjobportal_path ); // must wp_delete_file afterwards
            @wp_delete_file( $tmpfile ); // must wp_delete_file afterwards

            if ( is_wp_error( $unzipfile ) ) {
                $wpjobportal_result['error'] = esc_html(__('Addon installation failed','wp-job-portal')).'.';
                $wpjobportal_result['error'] .= " ".esc_html(wpjobportal::wpjobportal_getVariableValue($unzipfile->get_error_message()));
                $wpjobportal_result = wp_json_encode($wpjobportal_result);
                return $wpjobportal_result;
            } else {
                return true;
            }
        }else{
            $wpjobportal_error_string = $tmpfile->get_error_message();
            $wpjobportal_result['error'] = esc_html(__('Addon Installation Failed, File download error','wp-job-portal')).'!'.$wpjobportal_error_string;
            $wpjobportal_result = wp_json_encode($wpjobportal_result);
            return $wpjobportal_result;
        }
    }

    function wpjpRemoveAddonUpdatesFolder($wpjobportal_dir)
    {
        $wpjobportal_structure = glob(rtrim($wpjobportal_dir, "/") . '/*');
        if (is_array($wpjobportal_structure)) {
            foreach ($wpjobportal_structure as $file) {
                if (is_dir($file)) {
                    $this->wpjpRemoveAddonUpdatesFolder($file);
                } elseif (is_file($file)) {
                    @wp_delete_file($file);
                }
            }
        }
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
        if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ) {
            $creds = request_filesystem_credentials( site_url() );
            wp_filesystem( $creds );
        }

        @$wp_filesystem->rmdir($wpjobportal_dir);
    }

    function saveDocumentTitleOptions($wpjobportal_data){
        $wpjobportal_company_options =  $wpjobportal_data['wpjobportal_company_document_title_settings'];
        $wpjobportal_job_options =  $wpjobportal_data['wpjobportal_job_document_title_settings'];
        $wpjobportal_resume_options =  $wpjobportal_data['wpjobportal_resume_document_title_settings'];

        update_option( 'wpjobportal_company_document_title_settings', $wpjobportal_company_options);
        update_option( 'wpjobportal_job_document_title_settings', $wpjobportal_job_options);
        update_option( 'wpjobportal_resume_document_title_settings', $wpjobportal_resume_options);

        $error = false;
        // job seo
        if(isset($wpjobportal_data['job_seo'])){
            $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_config` SET `configvalue` = '".esc_sql($wpjobportal_data['job_seo'])."' WHERE `configname`= 'job_seo'";
            if (false === wpjobportaldb::query($query)) {
                $error = true;
            }
        }
        // company seo
        if(isset($wpjobportal_data['company_seo'])){
            $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_config` SET `configvalue` = '".esc_sql($wpjobportal_data['company_seo'])."' WHERE `configname`= 'company_seo'";
            if (false === wpjobportaldb::query($query)) {
                $error = true;
            }
        }
        // resume seo
        if(isset($wpjobportal_data['resume_seo'])){
            $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_config` SET `configvalue` = '".esc_sql($wpjobportal_data['resume_seo'])."' WHERE `configname`= 'resume_seo'";
            if (false === wpjobportaldb::query($query)) {
                $error = true;
            }
        }
        return WPJOBPORTAL_SAVED;
    }

    function wpjobportalCheckLicenseStatus() {
        // Get all distinct transaction keys
        $query = "
            SELECT DISTINCT option_value
            FROM " . wpjobportal::$_db->prefix . "options
            WHERE option_name LIKE 'transaction_key_for_wp-job-portal%'
        ";
        $wpjobportal_transaction_keys = wpjobportal::$_db->get_col($query);

        if (empty($wpjobportal_transaction_keys)) return;

        $wpjobportal_status_prefix = 'key_status_for_wp-job-portal_';
        // $site_url = WPJOBPORTALincluder::getJSModel('wpjobportal')->getSiteUrl();
        $site_url = site_url();
        $site_url = wpjobportalphplib::wpJP_str_replace("https://","",$site_url);
        $site_url = wpjobportalphplib::wpJP_str_replace("http://","",$site_url);

        $wpjobportal_show_key_expiry_msg = 0;

        foreach ($wpjobportal_transaction_keys as $wpjobportal_key) {
            // Build query string for GET request
            $query_args = [
                'token'   => $wpjobportal_key,
                'domain'  => $site_url,
                'request' => 'keyexpirycheck'
            ];

            $wpjobportal_url = add_query_arg($query_args, 'https://wpjobportal.com/setup/index.php');

            // Perform GET request
            $response = wp_remote_get($wpjobportal_url, [ 'timeout' => 15 ]);


            if (is_wp_error($response)) {
                continue; // Skip on error
            }

            $body = wp_remote_retrieve_body($response);
            $wpjobportal_data = json_decode($body, true);


            if (!is_array($wpjobportal_data) || !isset($wpjobportal_data['status'])) {
                continue; // Invalid response
            }

            // Save status
            update_option($wpjobportal_status_prefix . $wpjobportal_key, $wpjobportal_data['status'], false);

            // Save expiry date if available
            if ($wpjobportal_data['status'] == 1 && !empty($wpjobportal_data['expirydate'])) {
                if (strtotime(current_time('mysql')) > strtotime($wpjobportal_data['expirydate'])) {
                    $wpjobportal_show_key_expiry_msg = 1;
                }
            } else {
                $wpjobportal_show_key_expiry_msg = 1;
            }
        }

        update_option('wpjobportal_show_key_expiry_msg', $wpjobportal_show_key_expiry_msg, false);
    }

    function getMessagekey(){
        $wpjobportal_key = 'wpjobportal';if(wpjobportal::$_common->wpjp_isadmin()){$wpjobportal_key = 'admin_'.$wpjobportal_key;}return $wpjobportal_key;
    }
}

?>
