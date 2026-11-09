<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * The one place that names an application's status (W-1).
 *
 * An application has one status: jobapply.action_status (1 Inbox, 2 Spam,
 * 3 Hired, 4 Rejected, 5 Short listed). With Recruit Suite the pipeline stage
 * is the truth and action_status follows it; stages that have no status of
 * their own (Interviewing, Offer) are reported through the
 * 'wpjobportal_application_stage_slug' filter.
 *
 * Employers see the working labels. Candidates see what the step means for
 * them: Submitted, In review, Interviewing, Offer, Hired or Not selected.
 * Spam is never shown to a candidate (it reads as Submitted).
 */
class WPJOBPORTAL_Application_Status {

    const INBOX = 1;
    const SPAM = 2;
    const HIRED = 3;
    const REJECTED = 4;
    const SHORTLISTED = 5;

    /** jobapply.status: 1 sent, 3 waiting for payment, 4 withdrawn by the candidate. */
    const APPLY_ACTIVE = 1;
    const APPLY_WITHDRAWN = 4;
    const WITHDRAW_ACTION = 'wpjobportal_withdraw_application';

    public static function register() {
        add_action('admin_post_' . self::WITHDRAW_ACTION, array(__CLASS__, 'handle_withdraw'));
    }

    /** @return array action_status => employer label */
    public static function employer_labels() {
        return array(
            self::INBOX => __('Inbox', 'wp-job-portal'),
            self::SHORTLISTED => __('Short listed', 'wp-job-portal'),
            self::HIRED => __('Hired', 'wp-job-portal'),
            self::REJECTED => __('Rejected', 'wp-job-portal'),
            self::SPAM => __('Spam', 'wp-job-portal'),
        );
    }

    /**
     * @param int $status action_status
     * @return string
     */
    public static function employer_label($status) {
        $labels = self::employer_labels();
        $status = absint($status);
        return isset($labels[$status]) ? $labels[$status] : $labels[self::INBOX];
    }

    /**
     * The employer's label for a stored application: its Recruit Suite stage
     * when it has one (Interviewing, Offer, or a stage the company named), else
     * the core status. The core status alone said "Inbox" for an application in
     * Interviewing, while the candidate's email said Interviewing.
     *
     * @param int      $application_id
     * @param int|null $status action_status when already loaded.
     * @return string
     */
    public static function employer_label_for($application_id, $status = null) {
        $stage = trim((string) apply_filters('wpjobportal_application_stage_title', '', absint($application_id)));
        if ($stage !== '') {
            return $stage;
        }
        if ($status === null) {
            $status = wpjobportaldb::get_var(wpjobportal::$_db->prepare(
                'SELECT action_status FROM `' . wpjobportal::$_db->prefix . 'wj_portal_jobapply` WHERE id = %d',
                $application_id
            ));
        }
        return self::employer_label($status);
    }

    /** @return array key => candidate label, in the order a candidate moves through them */
    public static function public_labels() {
        return array(
            'submitted' => __('Submitted', 'wp-job-portal'),
            'review' => __('In review', 'wp-job-portal'),
            'interviewing' => __('Interviewing', 'wp-job-portal'),
            'offer' => __('Offer', 'wp-job-portal'),
            'hired' => __('Hired', 'wp-job-portal'),
            'not_selected' => __('Not selected', 'wp-job-portal'),
        );
    }

    /**
     * The candidate-facing key for an application.
     *
     * @param int    $status     action_status
     * @param string $stage_slug Recruit Suite stage, when known.
     * @return string A key of public_labels().
     */
    public static function public_key($status, $stage_slug = '') {
        $by_stage = array(
            'inbox' => 'submitted',
            'spam' => 'submitted',
            'shortlisted' => 'review',
            'interviewing' => 'interviewing',
            'offer' => 'offer',
            'hired' => 'hired',
            'rejected' => 'not_selected',
        );
        $stage_slug = sanitize_key((string) $stage_slug);
        if (isset($by_stage[$stage_slug])) {
            return $by_stage[$stage_slug];
        }
        $by_status = array(
            self::SHORTLISTED => 'review',
            self::HIRED => 'hired',
            self::REJECTED => 'not_selected',
        );
        $status = absint($status);
        return isset($by_status[$status]) ? $by_status[$status] : 'submitted';
    }

    /**
     * @param int    $status
     * @param string $stage_slug
     * @return string
     */
    public static function public_label($status, $stage_slug = '') {
        $labels = self::public_labels();
        return $labels[self::public_key($status, $stage_slug)];
    }

    /**
     * The Recruit Suite stage of an application, or ''.
     *
     * @param int $application_id
     * @return string
     */
    public static function stage_slug($application_id) {
        return (string) apply_filters('wpjobportal_application_stage_slug', '', absint($application_id));
    }

    /**
     * Candidate label for a stored application.
     *
     * @param int      $application_id
     * @param int|null $status action_status when already loaded.
     * @return string
     */
    public static function public_label_for($application_id, $status = null) {
        if ($status === null) {
            $status = wpjobportaldb::get_var(wpjobportal::$_db->prepare(
                'SELECT action_status FROM `' . wpjobportal::$_db->prefix . 'wj_portal_jobapply` WHERE id = %d',
                $application_id
            ));
        }
        return self::public_label($status, self::stage_slug($application_id));
    }

    /**
     * The status badge and the Withdraw button on a candidate's applied jobs
     * (My Applied Jobs, dashboard).
     *
     * @param object $job Row with id (the application), resumestatus and applystatus.
     * @return void
     */
    public static function render_candidate_badge($job) {
        if (!isset($job->id, $job->resumestatus)) {
            return;
        }
        $apply = isset($job->applystatus) ? (int) $job->applystatus : self::APPLY_ACTIVE;
        // Waiting for payment: the application has not reached the employer yet.
        if ($apply === 3) {
            return;
        }
        if ($apply === self::APPLY_WITHDRAWN) {
            echo '<span class="wjportal-applied-job-resume-status statustags wjportal-app-status wjportal-app-status-withdrawn">'
                . esc_html__('Withdrawn', 'wp-job-portal') . '</span>';
            return;
        }
        $key = self::public_key($job->resumestatus, self::stage_slug($job->id));
        if ((string) wpjobportal::$_config->getConfigurationByConfigName('show_applied_resume_status') === '1') {
            $labels = self::public_labels();
            echo '<span class="wjportal-applied-job-resume-status statustags wjportal-app-status wjportal-app-status-' . esc_attr($key) . '">'
                . esc_html($labels[$key]) . '</span>';
        }
        if (!in_array($key, array('hired', 'not_selected'), true)) {
            $confirm = __('Withdraw this application? The employer will no longer see it, and you cannot apply to this job again.', 'wp-job-portal');
            // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- every part is escaped as it is added; wp_nonce_field() returns safe markup
            echo '<form class="wjportal-app-withdraw" method="post" action="' . esc_url(admin_url('admin-post.php')) . '" onsubmit="return confirm(' . esc_attr(wp_json_encode($confirm)) . ');">'
                . '<input type="hidden" name="action" value="' . esc_attr(self::WITHDRAW_ACTION) . '">'
                . '<input type="hidden" name="application" value="' . esc_attr((int) $job->id) . '">'
                . wp_nonce_field(self::WITHDRAW_ACTION . (int) $job->id, '_wpnonce', true, false)
                . '<button type="submit" class="wjportal-app-withdraw-btn">' . esc_html__('Withdraw application', 'wp-job-portal') . '</button>'
                . '</form>';
            // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
        }
    }

    /**
     * The candidate withdraws one of their own applications.
     *
     * The record stays (the employer's history and the candidate's list keep
     * it, marked Withdrawn); employer lists count only sent applications
     * (status 1), so it drops out of them.
     *
     * @return void
     */
    public static function handle_withdraw() {
        $id = absint(WPJOBPORTALrequest::getVar('application', 'post', 0));
        if (!$id || !is_user_logged_in() || !check_admin_referer(self::WITHDRAW_ACTION . $id)) {
            wp_die(esc_html__('You are not allowed to do that.', 'wp-job-portal'));
        }
        $uid = (int) WPJOBPORTALincluder::getObjectClass('user')->uid();
        $done = false;
        if ($uid > 0) {
            $done = (int) wpjobportal::$_db->query(wpjobportal::$_db->prepare(
                'UPDATE `' . wpjobportal::$_db->prefix . 'wj_portal_jobapply` SET status = %d
                 WHERE id = %d AND uid = %d AND status = %d AND action_status NOT IN (%d, %d)',
                self::APPLY_WITHDRAWN,
                $id,
                $uid,
                self::APPLY_ACTIVE,
                self::HIRED,
                self::REJECTED
            )) > 0;
        }
        WPJOBPORTAL_Audit_Service::record('application_withdrawn', $done ? 'success' : 'failed', array('application_id' => $id));
        if ($done) {
            do_action('wpjobportal_application_withdrawn', $id, $uid);
            WPJOBPORTALmessages::setLayoutMessage(__('Your application was withdrawn. The employer no longer sees it.', 'wp-job-portal'), 'updated', 'jobapply');
        } else {
            WPJOBPORTALmessages::setLayoutMessage(__('This application could not be withdrawn.', 'wp-job-portal'), 'error', 'jobapply');
        }
        $back = wp_get_referer();
        wp_safe_redirect($back ? $back : home_url('/'));
        exit;
    }

    /**
     * Call after anything that may change an application's status (legacy
     * buttons, pipeline moves, automation). The candidate is emailed once,
     * and only when the status in their words changed: Inbox to Spam, or a
     * move between two custom stages that both read "In review", sends
     * nothing. Recruit Suite decides per stage through
     * 'wpjobportal_notify_candidate_status'.
     *
     * @param int    $application_id
     * @param string $old_key public_key() before the change.
     * @return bool Whether an email went out.
     */
    public static function status_changed($application_id, $old_key) {
        $application_id = absint($application_id);
        $new_key = self::public_key(
            wpjobportaldb::get_var(wpjobportal::$_db->prepare('SELECT action_status FROM `' . wpjobportal::$_db->prefix . 'wj_portal_jobapply` WHERE id = %d', $application_id)),
            self::stage_slug($application_id)
        );
        if ($new_key === $old_key) {
            return false;
        }
        do_action('wpjobportal_application_public_status_changed', $application_id, $old_key, $new_key);
        if (!apply_filters('wpjobportal_notify_candidate_status', true, $new_key, $application_id, $old_key)) {
            return false;
        }
        $labels = self::public_labels();
        return self::send_status_email($application_id, $labels[$new_key]);
    }

    /** public_key() of a stored application. */
    public static function current_key($application_id) {
        $status = wpjobportaldb::get_var(wpjobportal::$_db->prepare('SELECT action_status FROM `' . wpjobportal::$_db->prefix . 'wj_portal_jobapply` WHERE id = %d', absint($application_id)));
        return self::public_key($status, self::stage_slug($application_id));
    }

    /**
     * The "applied resume status" email to the candidate, with the label in their words.
     *
     * @param int    $application_id
     * @param string $label
     * @return bool
     */
    private static function send_status_email($application_id, $label) {
        $db = wpjobportal::$_db;
        $template = wpjobportaldb::get_row($db->prepare('SELECT subject, body FROM `' . $db->prefix . 'wj_portal_emailtemplates` WHERE templatefor = %s', 'applied-resume_status'));
        $emailstatus = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getEmailTemplateStatus('applied-resume_status');
        if (!$template || !$emailstatus || (int) $emailstatus->jobseeker !== 1) {
            return false;
        }
        $data = wpjobportaldb::get_row($db->prepare(
            "SELECT resume.email_address, job.title, CONCAT(resume.first_name, ' ', resume.last_name) AS jname
             FROM `{$db->prefix}wj_portal_jobapply` AS jobap
             JOIN `{$db->prefix}wj_portal_resume` AS resume ON resume.id = jobap.cvid
             JOIN `{$db->prefix}wj_portal_jobs` AS job ON job.id = jobap.jobid
             WHERE jobap.id = %d AND jobap.status = %d",
            $application_id,
            self::APPLY_ACTIVE
        ));
        if (!$data || !is_email($data->email_address)) {
            return false;
        }
        $site = wpjobportal::$_config->getConfigValue('title');
        $link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme' => 'jobapply', 'wpjobportallt' => 'myappliedjobs', 'wpjobportalpageid' => wpjobportal::wpjobportal_getPageid()));
        $replace = array(
            '{SITETITLE}' => $site,
            '{CURRENT_YEAR}' => gmdate('Y'),
            '{JOB_TITLE}' => $data->title,
            '{JOBSEEKER_NAME}' => $data->jname,
            '{RESUME_STATUS}' => $label,
            '{EMAIL}' => $data->email_address,
            '{RESUME_LINK}' => $link,
        );
        $subject = strtr((string) $template->subject, $replace);
        $body = strtr((string) $template->body, $replace);
        $config = WPJOBPORTALincluder::getJSModel('configuration')->getConfigByFor('email');
        return (bool) wpjobportal::$_common->sendEmail($data->email_address, $subject, $body, $config['mailfromaddress'], $config['mailfromname'], '');
    }

    /**
     * Script for the status buttons: after a change the page reloads as before,
     * but the confirmation stays at the top until dismissed and says which tab
     * the application moved to (round 4: it flashed for 0.7 seconds and the
     * candidate vanished from the tab being viewed).
     *
     * Defines wpjobportalStatusChanged(obj, actionid); call it on success.
     * An add-on may define wpjobportalAskRejection(id, box, done) through
     * 'wpjobportal_status_change_js' to collect a reason before a Reject.
     *
     * @return string
     */
    public static function status_change_js() {
        $labels = self::employer_labels();
        return apply_filters('wpjobportal_status_change_js', '') . "
        (function () {
            var KEY = 'wpjobportal_status_changed', labels = " . wp_json_encode($labels) . ";
            var moved = " . wp_json_encode(/* translators: %s: hiring stage name */ __('It is now under %s.', 'wp-job-portal')) . ", open = " . wp_json_encode(/* translators: %s: hiring stage name */ __('Open %s', 'wp-job-portal')) . ", close = " . wp_json_encode(__('Dismiss', 'wp-job-portal')) . ";
            window.wpjobportalStatusChanged = function (obj, aid) {
                try { sessionStorage.setItem(KEY, JSON.stringify({m: obj.message || '', a: parseInt(aid, 10) || 0})); } catch (e) {}
                window.location.reload();
            };
            function tabUrl(aid) {
                var u = new URL(window.location.href);
                u.searchParams.set('ta', aid);
                u.searchParams.delete('pagenum');
                return u.toString();
            }
            function show() {
                var raw = null;
                try { raw = sessionStorage.getItem(KEY); sessionStorage.removeItem(KEY); } catch (e) {}
                if (!raw) { return; }
                var d; try { d = JSON.parse(raw); } catch (e) { return; }
                var box = document.createElement('div');
                box.className = 'wjportal-status-changed';
                box.setAttribute('role', 'status');
                var t = document.createElement('span');
                t.textContent = d.m + (labels[d.a] ? ' ' + moved.replace('%s', labels[d.a]) : '');
                box.appendChild(t);
                if (labels[d.a]) {
                    var a = document.createElement('a');
                    a.href = tabUrl(d.a);
                    a.textContent = open.replace('%s', labels[d.a]);
                    box.appendChild(a);
                }
                var x = document.createElement('button');
                x.type = 'button'; x.textContent = close;
                x.onclick = function () { box.parentNode.removeChild(box); };
                box.appendChild(x);
                // Admin: under the page title. Site: top of the plugin's content.
                var head = document.querySelector('#wpjobportaladmin-data .wjpl-head');
                if (head) { head.parentNode.insertBefore(box, head.nextSibling); return; }
                var host = document.querySelector('.wjportal-main-wrapper, .wjportal-main-up-wrapper') || document.body;
                host.insertBefore(box, host.firstChild);
            }
            if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', show); } else { show(); }
        })();
        ";
    }
}
