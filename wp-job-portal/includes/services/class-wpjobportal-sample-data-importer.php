<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Builds the sample data a new site can try the job board with.
 *
 * It replaced 562 lines of hand-written INSERT statements. Those wrote nine
 * companies and nine resumes for one employer and one job seeker - more than
 * Core shows either of them, so the dashboards said "My Companies: 1" beside
 * nine company pages - and they hard-coded city, category and record ids, and
 * downloaded a zip of logos from a third-party bucket whose folders were named
 * by record id, so on any site that already had companies the logos landed in
 * the wrong folders. The logos themselves included real trademarks.
 *
 * Now the content lives in includes/sample-data/sample-data.json and is sized
 * to what the site will show: one hiring company with six jobs everywhere; two
 * more companies when the multi-company module is active; a second resume when
 * the multi-resume module is. Reference data - categories, job types, cities -
 * is looked up by name on the site, so it fits whatever country and language
 * the site was set up with. Rows are shaped by the plugin's own table classes,
 * so a column added to a table gets its default here too. Logos ship with the
 * plugin and are copied into each new record's own folder.
 *
 * Every record is registered with WPJOBPORTAL_Sample_Data_Service, so cleanup
 * and changing the owner work exactly as before.
 */
class WPJOBPORTAL_Sample_Data_Importer {

    /** @var array Lookups for the current run. */
    private static $ctx = array();

    /**
     * Import the sample set for the chosen owners.
     *
     * @param int $employer_id  Portal user id of the employer who owns companies and jobs.
     * @param int $jobseeker_id Portal user id of the job seeker who owns resumes and applications.
     * @return array Counts by type, or an empty array when nothing could be read.
     */
    public static function import($employer_id, $jobseeker_id) {
        global $wpdb;

        $file = WPJOBPORTAL_PLUGIN_PATH . 'includes/sample-data/sample-data.json';
        $data = is_readable($file) ? json_decode((string) file_get_contents($file), true) : null;
        if (!is_array($data)) {
            return array();
        }

        self::$ctx = array(
            'employer' => absint($employer_id),
            'jobseeker' => absint($jobseeker_id),
            'now' => current_time('mysql'),
            'until' => gmdate('Y-m-d H:i:s', current_time('timestamp') + 30 * DAY_IN_SECONDS),
            'cities' => self::pick_cities(3),
            'currency' => (string) wpjobportal::$_config->getConfigValue('job_currency'),
            'activity_from' => (int) wpjobportaldb_direct::get_var($wpdb->prepare("SELECT COALESCE(MAX(`id`), 0) FROM %i", $wpdb->prefix . 'wj_portal_activitylog')),
            'created' => array('companies' => array(), 'jobs' => array(), 'resumes' => array(), 'applications' => array()),
        );

        $companies = array();
        foreach (self::wanted($data, 'companies') as $i => $company) {
            $companies[$company['key']] = self::company($company, $i);
        }

        $jobs = array();
        foreach (self::wanted($data, 'jobs') as $i => $job) {
            if (empty($companies[$job['company']])) {
                continue;
            }
            $jobs[$job['key']] = self::job($job, $companies[$job['company']], $i);
        }

        $resumes = array();
        foreach (self::wanted($data, 'resumes') as $i => $resume) {
            $resumes[$resume['key']] = self::resume($resume, $i);
        }

        foreach (self::wanted($data, 'applications') as $application) {
            if (!empty($jobs[$application['job']]) && !empty($resumes[$application['resume']])) {
                self::application($jobs[$application['job']], $resumes[$application['resume']]);
            }
        }

        self::forget_activity();

        $counts = array();
        foreach (self::$ctx['created'] as $type => $ids) {
            $counts[$type] = count(array_filter($ids));
        }

        return $counts;
    }

    /**
     * Records of one type whose add-on, if any, is active.
     *
     * @param array  $data Decoded data file.
     * @param string $type companies, jobs, resumes or applications.
     * @return array
     */
    private static function wanted($data, $type) {
        $active = isset(wpjobportal::$_active_addons) ? (array) wpjobportal::$_active_addons : array();
        $out = array();
        foreach (isset($data[$type]) && is_array($data[$type]) ? $data[$type] : array() as $record) {
            if (!empty($record['requires']) && !in_array($record['requires'], $active, true)) {
                continue;
            }
            $out[] = $record;
        }

        return $out;
    }

    /**
     * Insert one row through the plugin's table class.
     *
     * The class supplies the column list and its defaults; the row is written
     * directly so that sample data does not fill the activity log with
     * "created" entries that would outlive a cleanup.
     *
     * @param string $table  Table class name, e.g. company.
     * @param array  $values Column values.
     * @return int New id, or 0.
     */
    private static function insert($table, $values) {
        global $wpdb;

        $row = WPJOBPORTALincluder::getJSTable($table);
        if (!$row->bind($values)) {
            return 0;
        }
        wpjobportaldb_direct::insert($row->tablename, $row->columns);

        return $wpdb->last_error === '' ? (int) $wpdb->insert_id : 0;
    }

    private static function company($company, $index) {
        $featured = self::featured('featuredcompany', $index === 0);
        $city = self::city($index);
        $id = self::insert('company', array(
            'uid' => self::$ctx['employer'],
            'name' => $company['name'],
            'alias' => wpjobportal::$_common->stringToAlias($company['name']),
            'url' => $company['url'],
            'contactemail' => $company['email'],
            'tagline' => $company['tagline'],
            'description' => $company['description'],
            'city' => $city,
            'logofilename' => $company['logo'],
            'logoisfile' => 1,
            'created' => self::$ctx['now'],
            'modified' => self::$ctx['now'],
            'hits' => 0,
            'status' => 1,
            'price' => 0,
            'isfeaturedcompany' => $featured[0],
            'startfeatureddate' => $featured[1],
            'endfeatureddate' => $featured[2],
            'params' => '[]',
        ));
        if (!$id) {
            return 0;
        }
        if ($city) {
            self::insert('companycities', array('companyid' => $id, 'cityid' => $city));
        }
        self::copy_image($company['logo'], 'employer/comp_' . $id . '/logo');
        WPJOBPORTAL_Sample_Data_Service::record_entity('companies', $id);
        self::$ctx['created']['companies'][] = $id;

        return $id;
    }

    private static function job($job, $company_id, $index) {
        $featured = self::featured('featuredjob', !empty($job['featured']));
        $city = self::city($index);
        $values = array(
            'uid' => self::$ctx['employer'],
            'companyid' => $company_id,
            'title' => $job['title'],
            'alias' => wpjobportal::$_common->stringToAlias($job['title']),
            'jobcategory' => self::lookup('categories', 'cat_title', $job['category']),
            'jobtype' => self::lookup('jobtypes', 'title', $job['type']),
            'jobstatus' => self::lookup('jobstatus', 'title', $job['status']),
            'careerlevel' => self::lookup('careerlevels', 'title', $job['career']),
            'description' => $job['description'],
            'city' => $city,
            'noofjobs' => 1,
            'showcontact' => 0,
            'hidesalaryrange' => 0,
            'created' => self::$ctx['now'],
            'modified' => self::$ctx['now'],
            'hits' => 0,
            'startpublishing' => self::$ctx['now'],
            'stoppublishing' => self::$ctx['until'],
            'status' => 1,
            'currency' => self::$ctx['currency'],
            'salarytype' => 3,
            'salarymin' => (int) $job['salary'][0],
            'salarymax' => (int) $job['salary'][1],
            'salaryduration' => self::lookup('salaryrangetypes', 'title', $job['salary'][2]),
            'jobid' => WPJOBPORTALincluder::getJSModel('job')->getJobId(),
            'isfeaturedjob' => $featured[0],
            'startfeatureddate' => $featured[1],
            'endfeatureddate' => $featured[2],
            'params' => '[]',
            'price' => 0,
            'is_urgent' => 0,
        );
        $id = self::insert('job', $values);
        if (!$id) {
            return 0;
        }
        if ($city) {
            self::insert('jobcities', array('jobid' => $id, 'cityid' => $city));
        }
        // The search text a saved job gets, built by the job model's own
        // routine for imported rows.
        $values['id'] = $id;
        WPJOBPORTALincluder::getJSModel('job')->importAIStringDataForJobs($values);
        WPJOBPORTAL_Sample_Data_Service::record_entity('jobs', $id);
        self::$ctx['created']['jobs'][] = $id;

        return $id;
    }

    private static function resume($resume, $index) {
        global $wpdb;

        // The resume is the job seeker's own, so it carries their name and
        // email rather than a stranger's.
        $wp_user = get_userdata((int) wpjobportaldb_direct::get_var($wpdb->prepare(
            "SELECT `uid` FROM %i WHERE `id` = %d", $wpdb->prefix . 'wj_portal_users', self::$ctx['jobseeker']
        )));
        $first = $wp_user && $wp_user->first_name !== '' ? $wp_user->first_name : ($wp_user ? $wp_user->display_name : __('Sample', 'wp-job-portal'));
        $last = $wp_user ? $wp_user->last_name : '';
        $city = self::city($index);

        $values = array(
            'uid' => self::$ctx['jobseeker'],
            'created' => self::$ctx['now'],
            'last_modified' => self::$ctx['now'],
            'published' => 1,
            'hits' => 0,
            'application_title' => $resume['title'],
            'alias' => wpjobportal::$_common->stringToAlias($resume['title']),
            'salaryfixed' => $resume['salary'],
            'first_name' => $first,
            'last_name' => $last,
            'email_address' => $wp_user ? $wp_user->user_email : '',
            'nationality' => self::country_of($city),
            'searchable' => 1,
            'job_category' => self::lookup('categories', 'cat_title', $resume['category']),
            'jobtype' => self::lookup('jobtypes', 'title', $resume['type']),
            'status' => 1,
            'skills' => $resume['skills'],
            'isfeaturedresume' => 0,
            'params' => '[]',
            'price' => 0,
            'quick_apply' => 0,
        );
        $id = self::insert('resume', $values);
        if (!$id) {
            return 0;
        }

        if ($city) {
            self::insert('resumeaddresses', array('resumeid' => $id, 'address' => self::city_name($city), 'address_city' => $city, 'created' => self::$ctx['now'], 'params' => '[]'));
        }
        $employer = $resume['employer'];
        self::insert('resumeemployers', array(
            'resumeid' => $id,
            'employer' => $employer['name'],
            'employer_address' => $employer['address'],
            'employer_city' => $city,
            'employer_from_date' => gmdate('Y-m-d', strtotime($employer['from'])),
            'employer_to_date' => !empty($employer['current']) ? '' : gmdate('Y-m-d', strtotime('-3 months')),
            'employer_current_status' => !empty($employer['current']) ? 1 : 0,
            'created' => self::$ctx['now'],
            'params' => '[]',
        ));
        $institute = $resume['institute'];
        self::insert('resumeinstitutes', array(
            'resumeid' => $id,
            'institute' => $institute['name'],
            'institute_certificate_name' => $institute['certificate'],
            'institute_study_area' => $institute['area'],
            'fromdate' => $institute['from'],
            'todate' => $institute['to'],
            'created' => self::$ctx['now'],
            'params' => '[]',
        ));
        foreach ((array) $resume['languages'] as $language) {
            self::insert('resumelanguages', array('resumeid' => $id, 'language' => $language, 'created' => self::$ctx['now'], 'params' => '[]'));
        }

        $values['id'] = $id;
        WPJOBPORTALincluder::getJSModel('resume')->importAIStringDataForResume($values);
        WPJOBPORTAL_Sample_Data_Service::record_entity('resumes', $id);
        self::$ctx['created']['resumes'][] = $id;

        return $id;
    }

    private static function application($job_id, $resume_id) {
        $id = self::insert('jobapply', array(
            'jobid' => $job_id,
            'uid' => self::$ctx['jobseeker'],
            'cvid' => $resume_id,
            'apply_date' => self::$ctx['now'],
            'resumeview' => 0,
            'action_status' => 1,
            'status' => 1,
            'price' => 0,
            'quick_apply' => 0,
        ));
        if ($id) {
            WPJOBPORTAL_Sample_Data_Service::record_entity('applications', $id);
            self::$ctx['created']['applications'][] = $id;
        }
    }

    /**
     * Featured flag and dates: featured for a year when the feature's module
     * is active and the record asks for it.
     *
     * @return array flag, start, end.
     */
    private static function featured($addon, $wants) {
        if ($wants && in_array($addon, (array) wpjobportal::$_active_addons, true)) {
            return array(1, self::$ctx['now'], gmdate('Y-m-d H:i:s', current_time('timestamp') + YEAR_IN_SECONDS));
        }

        return array(0, '', '');
    }

    /**
     * Id of a reference row by its title, or the first row when the title is
     * not there (a site that renamed or translated its categories).
     */
    private static function lookup($table, $column, $title) {
        global $wpdb;

        $key = $table . ':' . $title;
        if (isset(self::$ctx['lookups'][$key])) {
            return self::$ctx['lookups'][$key];
        }
        $t = $wpdb->prefix . 'wj_portal_' . sanitize_key($table);
        $column = sanitize_key($column);
        $id = (int) wpjobportaldb_direct::get_var($wpdb->prepare("SELECT `id` FROM %i WHERE %i = %s ORDER BY `id` ASC LIMIT 1", $t, $column, $title));
        if (!$id) {
            $id = (int) wpjobportaldb_direct::get_var($wpdb->prepare("SELECT `id` FROM %i ORDER BY `id` ASC LIMIT 1", $t));
        }

        return self::$ctx['lookups'][$key] = $id;
    }

    /**
     * A few enabled cities from different states of the site's own city list.
     *
     * @return int[]
     */
    private static function pick_cities($count) {
        global $wpdb;

        $ids = wpjobportaldb_direct::get_col($wpdb->prepare(
            "SELECT MIN(`id`) FROM %i WHERE `enabled` = 1 GROUP BY `stateid` ORDER BY MIN(`id`) ASC LIMIT %d",
            $wpdb->prefix . 'wj_portal_cities',
            (int) $count
        ));

        return array_map('intval', (array) $ids);
    }

    private static function city($index) {
        $cities = self::$ctx['cities'];

        return empty($cities) ? '' : $cities[$index % count($cities)];
    }

    private static function city_name($city) {
        global $wpdb;

        return (string) wpjobportaldb_direct::get_var($wpdb->prepare("SELECT `name` FROM %i WHERE `id` = %d", $wpdb->prefix . 'wj_portal_cities', $city));
    }

    private static function country_of($city) {
        global $wpdb;

        return $city ? (int) wpjobportaldb_direct::get_var($wpdb->prepare("SELECT `countryid` FROM %i WHERE `id` = %d", $wpdb->prefix . 'wj_portal_cities', $city)) : '';
    }

    /**
     * Copy a bundled image into a record's folder under the data directory.
     *
     * @param string $file   Image name in includes/sample-data/images.
     * @param string $folder Path below <data directory>/data/.
     */
    private static function copy_image($file, $folder) {
        $source = WPJOBPORTAL_PLUGIN_PATH . 'includes/sample-data/images/' . basename($file);
        if (!is_readable($source)) {
            return;
        }
        $uploads = wp_upload_dir();
        $target = trailingslashit($uploads['basedir']) . wpjobportal::$_config->getConfigValue('data_directory') . '/data/' . $folder;
        if (wp_mkdir_p($target)) {
            copy($source, $target . '/' . basename($file));
        }
    }

    /**
     * Drop the activity-log lines the import itself caused.
     *
     * The job and resume models' search-text routines save through the table
     * class, which logs an "updated" line each time; on a new site those read
     * as the administrator editing records they never touched, and they would
     * outlive a cleanup.
     */
    private static function forget_activity() {
        global $wpdb;

        $ids = array();
        foreach (self::$ctx['created'] as $created) {
            $ids = array_merge($ids, array_map('intval', $created));
        }
        if (empty($ids)) {
            return;
        }
        $ids = array_map('intval', $ids);
        wpjobportaldb_direct::query($wpdb->prepare(
            'DELETE FROM %i WHERE `id` > %d AND `referenceid` IN (' . implode(',', array_fill(0, count($ids), '%d')) . ')',
            ...array_merge(array($wpdb->prefix . 'wj_portal_activitylog', self::$ctx['activity_from']), $ids)
        ));
    }
}
