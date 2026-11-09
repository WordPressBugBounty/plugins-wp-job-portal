<?php
if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * W-3: a new employer's first run - company, then the first job, then its
 * applicants - without dead ends: the job form says up front that a company
 * comes first, Add Company brings them back to the job, and the dashboard
 * shows the three steps until they are done.
 */
class WPJOBPORTAL_Employer_Start {

    /** The one place Add Company may send the employer back to. */
    const RETURN_ADDJOB = 'addjob';

    /** Whether this employer has a company (any status). */
    public static function has_company($uid) {
        $uid = (int) $uid;
        if ($uid < 1) {
            return false;
        }
        if (in_array('multicompany', (array) wpjobportal::$_active_addons, true)) {
            return (bool) WPJOBPORTALincluder::getJSModel('company')->employerHaveCompany($uid);
        }
        $company = WPJOBPORTALincluder::getJSModel('company')->getSingleCompanyByUid($uid);
        return isset($company->id);
    }

    /** Add Company, coming back to Add Job once the company is saved. */
    public static function add_company_url() {
        $module = in_array('multicompany', (array) wpjobportal::$_active_addons, true) ? 'multicompany' : 'company';
        return add_query_arg('wpjobportal_return', self::RETURN_ADDJOB, wpjobportal::wpjobportal_makeUrl(array('wpjobportalme' => $module, 'wpjobportallt' => 'addcompany')));
    }

    /**
     * The return asked for by the company form (only the known value counts, so
     * no address from the request is ever followed), or ''.
     */
    public static function requested_return() {
        $asked = sanitize_key((string) WPJOBPORTALrequest::getVar('wpjobportal_return', '', ''));
        return $asked === self::RETURN_ADDJOB ? $asked : '';
    }

    /** Where to go after a company was saved, when the employer came from Add Job; else ''.
     * A new company that must be paid for first keeps its payment step. */
    public static function return_url_after_company($isnew = false) {
        if ($isnew && in_array('credits', (array) wpjobportal::$_active_addons, true)
            && (int) wpjobportal::$_config->getConfigValue('submission_type') === 2
            && (float) wpjobportal::$_config->getConfigValue('company_price_perlisting') > 0) {
            return '';
        }
        if (self::requested_return() !== self::RETURN_ADDJOB || wpjobportal::$_common->wpjp_isadmin()) {
            return '';
        }
        return wpjobportal::wpjobportal_makeUrl(array('wpjobportalme' => 'job', 'wpjobportallt' => 'addjob'));
    }

    /**
     * The three first-run steps for this employer, each done or not.
     *
     * @return array[] key, label, done, url, action
     */
    public static function steps($uid) {
        $db = wpjobportal::$_db;
        $uid = (int) $uid;
        $has_company = self::has_company($uid);
        $jobs = $uid ? (int) $db->get_var($db->prepare("SELECT COUNT(id) FROM `" . $db->prefix . "wj_portal_jobs` WHERE uid = %d", $uid)) : 0;
        $apps = $uid ? (int) $db->get_var($db->prepare("SELECT COUNT(ja.id) FROM `" . $db->prefix . "wj_portal_jobapply` AS ja JOIN `" . $db->prefix . "wj_portal_jobs` AS job ON job.id = ja.jobid WHERE job.uid = %d", $uid)) : 0;
        return array(
            array('key' => 'company', 'label' => __('Add your company', 'wp-job-portal'), 'hint' => __('Jobs are posted under a company.', 'wp-job-portal'),
                'done' => $has_company, 'url' => self::add_company_url(), 'action' => __('Add company', 'wp-job-portal')),
            array('key' => 'job', 'label' => __('Post your first job', 'wp-job-portal'), 'hint' => __('It goes live once it is approved.', 'wp-job-portal'),
                'done' => $jobs > 0, 'url' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme' => 'job', 'wpjobportallt' => 'addjob')), 'action' => __('Add job', 'wp-job-portal')),
            array('key' => 'applicants', 'label' => __('Review your applicants', 'wp-job-portal'), 'hint' => __('Applications show up under My Jobs.', 'wp-job-portal'),
                'done' => $apps > 0, 'url' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme' => 'job', 'wpjobportallt' => 'myjobs')), 'action' => __('My jobs', 'wp-job-portal')),
        );
    }
}
