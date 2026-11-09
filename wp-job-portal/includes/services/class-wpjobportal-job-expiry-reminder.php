<?php
if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * W-14: "your job expires in 3 days", once per closing date, through the
 * system email template. Close or extend the job and it is not sent.
 */
class WPJOBPORTAL_Job_Expiry_Reminder {

    const DAYS = 3;
    const OPTION = 'wpjobportal_job_expiry_reminded';

    /** Live jobs that take applications and close within the window. */
    public static function due_jobs() {
        $db = wpjobportal::$_db;
        $today = date_i18n('Y-m-d');
        $sql = "SELECT job.id, job.title, job.stoppublishing FROM `" . $db->prefix . "wj_portal_jobs` AS job
                 WHERE job.status = 1 AND job.stoppublishing >= %s AND DATE(job.stoppublishing) <= DATE_ADD(%s, INTERVAL " . self::DAYS . " DAY)";
        $closed = array_map('intval', (array) WPJOBPORTALincluder::getJSModel('jobstatus')->getClosedStatusIds());
        if (!empty($closed)) {
            $sql .= ' AND job.jobstatus NOT IN (' . implode(',', $closed) . ')';
        }
        return (array) $db->get_results($db->prepare($sql, $today, $today));
    }

    public static function send() {
        /**
         * Filter whether employers get the job expiry reminder.
         *
         * @param bool $send Default true.
         */
        if (!apply_filters('wpjobportal_send_job_expiry_reminder', true)) {
            return;
        }
        $db = wpjobportal::$_db;
        $template = $db->get_row("SELECT subject, body FROM `" . $db->prefix . "wj_portal_emailtemplates` WHERE templatefor = 'system-email' LIMIT 1");
        if (!$template) {
            return;
        }
        $config = wpjobportal::$_config->getConfigByFor('email');
        $reminded = (array) get_option(self::OPTION, array());
        $now_due = array();
        foreach (self::due_jobs() as $job) {
            $stop = substr((string) $job->stoppublishing, 0, 10);
            $now_due[$job->id] = $stop;
            if (isset($reminded[$job->id]) && $reminded[$job->id] === $stop) {
                continue;
            }
            $to = WPJOBPORTALincluder::getJSModel('jobapply')->getEmployerEmailByJobId($job->id);
            $email = $to ? ($to->useremail ? $to->useremail : $to->companyuseremail) : '';
            if (!$email) {
                continue;
            }
            $url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme' => 'job', 'wpjobportallt' => 'myjobs', 'wpjobportalpageid' => WPJOBPORTAL_Page_Provisioning_Service::page_carrying('wpjobportal_employer_controlpanel')));
            /* translators: 1: job title, 2: closing date */
            $text = sprintf(__('Your job "%1$s" closes on %2$s and will stop taking applications then. To keep it open, change its closing date from My Jobs:', 'wp-job-portal'), esc_html($job->title), date_i18n(get_option('date_format'), strtotime($stop)))
                . ' <a href="' . esc_url($url) . '">' . esc_html__('My Jobs', 'wp-job-portal') . '</a>';
            $values = array(
                '{SYSTEM_SUBJECT}' => __('Your job closes soon', 'wp-job-portal'),
                '{NOTIFICATION_HEADING}' => __('Your job closes soon', 'wp-job-portal'),
                '{USER_NAME}' => '',
                '{EMAIL_BODY}' => $text,
                '{EMAIL}' => $email,
            );
            WPJOBPORTALincluder::getJSModel('emailtemplate')->sendEmail($email, strtr($template->subject, $values), str_replace('Dear ,', __('Hello,', 'wp-job-portal'), strtr($template->body, $values)), $config['mailfromaddress'], $config['mailfromname']);
            $reminded[$job->id] = $stop;
        }
        // Forget jobs no longer in the window, so the option stays small.
        update_option(self::OPTION, array_intersect_key($reminded, $now_due), false);
    }
}
