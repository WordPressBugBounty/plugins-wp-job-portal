<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Tell administrators when the site's emails are not going out, and let them
 * send a test email (D-5).
 *
 * Nothing new is logged: every submission already lands in the operational
 * audit ("notification_submission", success or failed) and the delivery stats
 * keep the last error. This reads those. The warning lists the failures since
 * the last email that did go out, or since the administrator last dismissed
 * it, whichever is later, so one bad afternoon does not nag forever and a new
 * failure brings it back.
 *
 * The test email goes straight to wp_mail() (not the queue) at the signed-in
 * administrator's own address, so the answer comes back on the next screen.
 */
class WPJOBPORTAL_Mail_Health_Notice {

    /** Per administrator: the UTC time of the newest failure they dismissed. */
    const DISMISSED_META = 'wpjobportal_mail_notice_dismissed';

    const DISMISS_ACTION = 'wpjobportal_dismiss_mail_notice';
    const TEST_ACTION = 'wpjobportal_send_test_email';

    public static function register() {
        add_action('admin_notices', array(__CLASS__, 'render'));
        add_action('admin_post_' . self::DISMISS_ACTION, array(__CLASS__, 'dismiss'));
        add_action('admin_post_' . self::TEST_ACTION, array(__CLASS__, 'send_test'));
    }

    private static function allowed() {
        return current_user_can('wpjobportal') || current_user_can('manage_options');
    }

    /**
     * Failed submissions newer than the last success and the dismissal.
     *
     * @return array ['count' => int, 'first' => UTC time, 'last' => UTC time]
     */
    public static function failures() {
        $since = (string) get_user_meta(get_current_user_id(), self::DISMISSED_META, true);
        $count = 0;
        $first = '';
        $last = '';
        // The audit is newest first; stop at the first email that went out.
        foreach (WPJOBPORTAL_Audit_Service::get_recent(WPJOBPORTAL_Audit_Service::LIMIT) as $entry) {
            if (!isset($entry['event'], $entry['outcome'], $entry['time'])) {
                continue;
            }
            // deliver_now() logs every send, the test email included, as notification_submission.
            if ($entry['event'] !== 'notification_submission') {
                continue;
            }
            if ($entry['outcome'] === 'success' || ($since !== '' && strcmp($entry['time'], $since) <= 0)) {
                break;
            }
            if ($entry['outcome'] === 'failed') {
                $count++;
                if ($last === '') {
                    $last = $entry['time'];
                }
                $first = $entry['time'];
            }
        }
        return array('count' => $count, 'first' => $first, 'last' => $last);
    }

    private static function local_date($utc) {
        $time = strtotime($utc . ' UTC');
        return $time ? wp_date(get_option('date_format') . ' ' . get_option('time_format'), $time) : '';
    }

    public static function test_url() {
        return wp_nonce_url(admin_url('admin-post.php?action=' . self::TEST_ACTION), self::TEST_ACTION);
    }

    /**
     * The warning (and the test email's result) on the plugin's own pages.
     *
     * @return void
     */
    public static function render() {
        if (!self::allowed()) {
            return;
        }
        $page = sanitize_key((string) WPJOBPORTALrequest::getVar('page', 'get', ''));
        if (strpos($page, 'wpjobportal') !== 0) {
            return;
        }

        $result = sanitize_key((string) WPJOBPORTALrequest::getVar('wpjobportal_test_email', 'get', ''));
        if ($result === 'sent') {
            echo '<div class="notice notice-success is-dismissible wpjobportal-mail-notice"><p><strong>'
                . esc_html__('Test email sent.', 'wp-job-portal') . '</strong> '
                /* translators: %s: email address */
                . esc_html(sprintf(__('WordPress accepted it for %s. If it does not arrive in a few minutes, check the spam folder and your mail settings.', 'wp-job-portal'), wp_get_current_user()->user_email))
                . '</p></div>';
        } elseif ($result === 'failed') {
            $stats = WPJOBPORTAL_Notification_Service::get_stats();
            $error = isset($stats['last_error']) ? (string) $stats['last_error'] : '';
            echo '<div class="notice notice-error is-dismissible wpjobportal-mail-notice"><p><strong>'
                . esc_html__('The test email could not be sent.', 'wp-job-portal') . '</strong> '
                . ($error !== '' ? esc_html(sprintf(/* translators: %s: error from WordPress mail */ __('WordPress said: %s', 'wp-job-portal'), $error)) . ' ' : '')
                . esc_html__('Your site cannot send email yet. An SMTP plugin with your mail provider\'s settings usually fixes this.', 'wp-job-portal')
                . '</p></div>';
            return;
        }

        $failures = self::failures();
        if ($failures['count'] < 1) {
            return;
        }
        $stats = WPJOBPORTAL_Notification_Service::get_stats();
        $error = isset($stats['last_error']) ? (string) $stats['last_error'] : '';
        $dismiss = wp_nonce_url(admin_url('admin-post.php?action=' . self::DISMISS_ACTION . '&until=' . rawurlencode($failures['last'])), self::DISMISS_ACTION);
        ?>
        <div class="notice notice-error wpjobportal-mail-notice">
            <p>
                <strong><?php echo esc_html__('Emails from your job board are not being sent.', 'wp-job-portal'); ?></strong>
                <?php
                /* translators: 1: number of emails, 2: date and time */
                echo esc_html(sprintf(_n('%1$s email failed since %2$s.', '%1$s emails failed since %2$s.', $failures['count'], 'wp-job-portal'), number_format_i18n($failures['count']), self::local_date($failures['first'])));
                echo ' ' . esc_html__('Applicants, employers and you may be missing messages.', 'wp-job-portal');
                if ($error !== '') {
                    echo ' ' . esc_html(sprintf(/* translators: %s: error from WordPress mail */ __('Last error: %s', 'wp-job-portal'), $error));
                }
                ?>
            </p>
            <p>
                <a class="button button-primary" href="<?php echo esc_url(self::test_url()); ?>"><?php echo esc_html__('Send a test email', 'wp-job-portal'); ?></a>
                <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_systemstatus')); ?>"><?php echo esc_html__('System status', 'wp-job-portal'); ?></a>
                <a class="button-link" href="<?php echo esc_url($dismiss); ?>"><?php echo esc_html__('Dismiss until the next failure', 'wp-job-portal'); ?></a>
            </p>
        </div>
        <?php
    }

    /**
     * @return void
     */
    public static function dismiss() {
        if (!self::allowed() || !check_admin_referer(self::DISMISS_ACTION)) {
            wp_die(esc_html__('You are not allowed to do that.', 'wp-job-portal'));
        }
        $until = sanitize_text_field((string) WPJOBPORTALrequest::getVar('until', 'get', ''));
        if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $until)) {
            $until = current_time('mysql', true);
        }
        update_user_meta(get_current_user_id(), self::DISMISSED_META, $until);
        wp_safe_redirect(wp_get_referer() ? wp_get_referer() : admin_url('admin.php?page=wpjobportal'));
        exit;
    }

    /**
     * Send one plain test email to the signed-in administrator, bypassing the queue.
     *
     * @return void
     */
    public static function send_test() {
        if (!self::allowed() || !check_admin_referer(self::TEST_ACTION)) {
            wp_die(esc_html__('You are not allowed to do that.', 'wp-job-portal'));
        }
        $to = wp_get_current_user()->user_email;
        $site = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
        /* translators: %s: site name */
        $subject = sprintf(__('[%s] Test email from WP Job Portal', 'wp-job-portal'), $site);
        $message = '<p>' . esc_html__('This is a test email from WP Job Portal. If you can read it, your site can send email to applicants and employers.', 'wp-job-portal') . '</p>';
        $sent = is_email($to) && WPJOBPORTAL_Notification_Service::deliver_now($to, $subject, $message, array(), array(), 'mail_test');
        WPJOBPORTAL_Audit_Service::record('notification_test', $sent ? 'success' : 'failed');
        $back = wp_get_referer() ? wp_get_referer() : admin_url('admin.php?page=wpjobportal');
        wp_safe_redirect(add_query_arg('wpjobportal_test_email', $sent ? 'sent' : 'failed', remove_query_arg('wpjobportal_test_email', $back)));
        exit;
    }
}
