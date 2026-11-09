<?php
if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * W-4: a new job seeker's first run - apply, add a resume, back to the same
 * job with that resume selected - and what the job says once they applied.
 */
class WPJOBPORTAL_Seeker_Start {

    const RETURN_JOB = 'wpjobportal_return_job';
    const NEW_RESUME = 'wpjobportal_new_resume';
    const APPLY_FORM = 'wjportal-view-job-page-job-apply-form-bottom-wraper';

    /** Add Resume, coming back to this job once the resume is saved. */
    public static function add_resume_url($jobid, $pageid = null) {
        $args = array('wpjobportalme' => 'resume', 'wpjobportallt' => 'addresume');
        if ($pageid) {
            $args['wpjobportalpageid'] = $pageid;
        }
        return add_query_arg(self::RETURN_JOB, (int) $jobid, wpjobportal::wpjobportal_makeUrl($args));
    }

    /** The job the resume form was opened from (only ever an id, so no address from the request is followed), or 0. */
    public static function requested_job() {
        return absint(WPJOBPORTALrequest::getVar(self::RETURN_JOB, '', 0));
    }

    /** The resume just added, to select in the apply form, or 0. */
    public static function new_resume() {
        return absint(WPJOBPORTALrequest::getVar(self::NEW_RESUME, '', 0));
    }

    /** Where to go after a resume was saved, when it was added from a job; else ''.
     * A new resume that must be paid for first keeps its payment step. */
    public static function return_url_after_resume($resumeid, $isnew = false) {
        $jobid = self::requested_job();
        if (!$jobid || wpjobportal::$_common->wpjp_isadmin() || WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
            return '';
        }
        if ($isnew && in_array('credits', (array) wpjobportal::$_active_addons, true)
            && (int) wpjobportal::$_config->getConfigValue('submission_type') === 2
            && (float) wpjobportal::$_config->getConfigValue('job_resume_price_perlisting') > 0) {
            return '';
        }
        $url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme' => 'job', 'wpjobportallt' => 'viewjob', 'wpjobportalid' => $jobid));
        return ($resumeid ? add_query_arg(self::NEW_RESUME, (int) $resumeid, $url) : $url) . '#' . self::APPLY_FORM;
    }

    /** Back to the form after a failed save, still on the way to the job. */
    public static function keep_return($url) {
        $jobid = self::requested_job();
        return $jobid ? add_query_arg(self::RETURN_JOB, $jobid, $url) : $url;
    }

    /** Whether this seeker has a resume an admin has not approved yet (it cannot be applied with). */
    public static function has_resume_waiting($uid) {
        $db = wpjobportal::$_db;
        return (bool) $db->get_var($db->prepare("SELECT COUNT(id) FROM `" . $db->prefix . "wj_portal_resume` WHERE uid = %d AND status = 0 AND (quick_apply IS NULL OR quick_apply <> 1)", (int) $uid));
    }

    /**
     * "You applied on 23 Oct · View application" for a job this user applied
     * to, or ''. An application still waiting for payment is not sent yet, so
     * the job keeps offering the payment step.
     */
    public static function applied_notice($jobid, $uid) {
        $uid = (int) $uid;
        if ($uid < 1 || !WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()) {
            return '';
        }
        $db = wpjobportal::$_db;
        $apply = $db->get_row($db->prepare("SELECT apply_date, status FROM `" . $db->prefix . "wj_portal_jobapply` WHERE jobid = %d AND uid = %d ORDER BY id DESC LIMIT 1", (int) $jobid, $uid));
        if (!$apply || (int) $apply->status === 3) {
            return '';
        }
        if ((int) $apply->status === WPJOBPORTAL_Application_Status::APPLY_WITHDRAWN) {
            $text = __('You withdrew your application', 'wp-job-portal');
        } else {
            $time = strtotime($apply->apply_date);
            $format = date_i18n('Y', $time) === date_i18n('Y') ? 'j M' : 'j M Y';
            /* translators: %s: the date of the application */
            $text = sprintf(__('You applied on %s', 'wp-job-portal'), date_i18n($format, $time));
        }
        $url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme' => 'jobapply', 'wpjobportallt' => 'myappliedjobs'));
        return '<div class="wjportal-job-applied-note"><span>' . esc_html($text) . '</span> <span aria-hidden="true">&middot;</span> <a href="' . esc_url($url) . '">' . esc_html__('View application', 'wp-job-portal') . '</a></div>';
    }
}
