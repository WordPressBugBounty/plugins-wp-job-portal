<?php
if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * W-12: leaving job alerts from the email itself, without logging in. Gmail and
 * Yahoo require this of bulk senders (RFC 8058). The link holds only a signed
 * alert id. Opening it asks first, because mail scanners follow links; the
 * POST, which is what a mail client's own Unsubscribe button sends, does it.
 */
class WPJOBPORTAL_Alert_Unsubscribe {

    const ACTION = 'wpjobportal_alert_unsubscribe';

    public static function register() {
        add_action('admin_post_nopriv_' . self::ACTION, array(__CLASS__, 'handle'));
        add_action('admin_post_' . self::ACTION, array(__CLASS__, 'handle'));
    }

    private static function token($alertid, $email) {
        return wp_hash('alert-unsubscribe|' . (int) $alertid . '|' . strtolower((string) $email));
    }

    public static function url($alertid, $email) {
        return add_query_arg(array('action' => self::ACTION, 'a' => (int) $alertid, 't' => self::token($alertid, $email)), admin_url('admin-post.php'));
    }

    /** The mail headers for one alert email. */
    public static function headers($alertid, $email) {
        return array('List-Unsubscribe: <' . self::url($alertid, $email) . '>', 'List-Unsubscribe-Post: List-Unsubscribe=One-Click');
    }

    /** The email's own link, added after the template so an edited template has it too. */
    public static function footer($alertid, $email) {
        return '<p style="text-align: center; font-size: 13px; color: #727376;">' . esc_html__('Do not want these emails any more?', 'wp-job-portal')
            . ' <a href="' . esc_url(self::url($alertid, $email)) . '">' . esc_html__('Unsubscribe', 'wp-job-portal') . '</a></p>';
    }

    public static function handle() {
        $alertid = absint(WPJOBPORTALrequest::getVar('a'));
        $token = sanitize_text_field((string) WPJOBPORTALrequest::getVar('t'));
        $db = wpjobportal::$_db;
        $email = $db->get_var($db->prepare("SELECT contactemail FROM `" . $db->prefix . "wj_portal_jobalertsetting` WHERE id = %d", $alertid));
        $title = __('Job alerts', 'wp-job-portal');
        if (!$email || !hash_equals(self::token($alertid, $email), $token)) {
            // A used link lands here too, and says nothing about whether an alert exists.
            wp_die(esc_html__('This unsubscribe link is no longer active. You may already be unsubscribed.', 'wp-job-portal'), esc_html($title), array('response' => 200));
        }
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            // The token above proves this request comes from the mailbox itself.
            WPJOBPORTALincluder::getJSModel('jobalert')->unSubscribeJobAlertEmail($email, true);
            wp_die(esc_html__('You are unsubscribed and will not receive these job alerts any more.', 'wp-job-portal'), esc_html($title), array('response' => 200));
        }
        $form = '<p>' . esc_html__('Stop receiving job alerts by email?', 'wp-job-portal') . '</p>'
            . '<form method="post" action="' . esc_url(self::url($alertid, $email)) . '"><button type="submit" class="button">' . esc_html__('Unsubscribe', 'wp-job-portal') . '</button></form>';
        wp_die(wp_kses($form, array('p' => array(), 'form' => array('method' => array(), 'action' => array()), 'button' => array('type' => array(), 'class' => array()))), esc_html($title), array('response' => 200));
    }
}
