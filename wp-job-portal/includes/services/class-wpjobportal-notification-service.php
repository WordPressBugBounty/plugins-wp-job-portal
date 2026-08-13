<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Shared delivery path for WP Job Portal core notifications.
 *
 * Direct delivery remains available through WordPress wp_mail(). When the
 * administrator enables queue mode, messages without attachments are stored
 * durably and delivered by the queue worker.
 */
class WPJOBPORTAL_Notification_Service {

    const STATS_OPTION = 'wpjobportal_mail_delivery_stats';

    private static $captured_error = '';

    /**
     * Send one WP Job Portal email.
     *
     * @param string|array $to Recipient or recipients.
     * @param string       $subject Subject.
     * @param string       $message HTML message.
     * @param string|array $headers Headers.
     * @param string|array $attachments Attachments.
     * @param string       $source Privacy-safe source identifier.
     * @param string       $idempotency_key Optional stable key supplied by a
     *                                      caller that needs enqueue deduplication.
     * @return bool True when queued or accepted by wp_mail().
     */
    public static function send($to, $subject, $message, $headers = array(), $attachments = array(), $source = 'system_email', $idempotency_key = '') {
        $source = sanitize_key($source);
        if ($source === '') {
            $source = 'system_email';
        }

        if (
            class_exists('WPJOBPORTAL_Notification_Queue_Service')
            && WPJOBPORTAL_Notification_Queue_Service::is_enabled()
            && self::attachment_count($attachments) === 0
        ) {
            try {
                $queued = WPJOBPORTAL_Notification_Queue_Service::enqueue(
                    $to,
                    $subject,
                    $message,
                    $headers,
                    $attachments,
                    $source,
                    $idempotency_key
                );
            } catch (Throwable $throwable) {
                $queued = new WP_Error(
                    'wpjobportal_queue_exception',
                    WPJOBPORTAL_Redactor::redact($throwable->getMessage())
                );
                WPJOBPORTAL_Notification_Queue_Service::record_fallback($queued->get_error_message());
            }
            if (!is_wp_error($queued)) {
                return true;
            }

            WPJOBPORTAL_Audit_Service::record(
                'notification_queue_fallback',
                'warning',
                array(
                    'source' => $source,
                    'recipient_count' => self::recipient_count($to),
                    'error' => $queued->get_error_message(),
                )
            );
        } elseif (
            class_exists('WPJOBPORTAL_Notification_Queue_Service')
            && WPJOBPORTAL_Notification_Queue_Service::is_enabled()
            && self::attachment_count($attachments) > 0
        ) {
            WPJOBPORTAL_Audit_Service::record(
                'notification_queue_attachment_bypass',
                'info',
                array(
                    'source' => $source,
                    'recipient_count' => self::recipient_count($to),
                    'attachment_count' => self::attachment_count($attachments),
                )
            );
        }

        return self::deliver_now($to, $subject, $message, $headers, $attachments, $source);
    }

    /**
     * Submit one message directly to WordPress. Queue workers call this method
     * after atomically claiming a stored job.
     *
     * @param string|array $to Recipient or recipients.
     * @param string       $subject Subject.
     * @param string       $message HTML message.
     * @param string|array $headers Headers.
     * @param string|array $attachments Attachments.
     * @param string       $source Privacy-safe source identifier.
     * @return bool
     */
    public static function deliver_now($to, $subject, $message, $headers = array(), $attachments = array(), $source = 'system_email') {
        self::$captured_error = '';
        $source = sanitize_key($source);
        if ($source === '') {
            $source = 'system_email';
        }

        add_filter('wp_mail_content_type', array(__CLASS__, 'html_content_type'));
        add_action('wp_mail_failed', array(__CLASS__, 'capture_mail_error'), PHP_INT_MAX, 1);

        try {
            $result = wp_mail($to, $subject, $message, $headers, $attachments);
        } finally {
            remove_action('wp_mail_failed', array(__CLASS__, 'capture_mail_error'), PHP_INT_MAX);
            remove_filter('wp_mail_content_type', array(__CLASS__, 'html_content_type'));
        }

        $result = (bool) $result;
        $error = $result
            ? ''
            : (self::$captured_error !== '' ? self::$captured_error : __('wp_mail() returned false.', 'wp-job-portal'));

        self::record_result(
            $result,
            $error,
            array(
                'source' => $source,
                'recipient_count' => self::recipient_count($to),
                'attachment_count' => self::attachment_count($attachments),
            )
        );

        /**
         * Fires after WP Job Portal records a mail submission result.
         *
         * The metadata deliberately excludes recipients, subject and body.
         */
        do_action(
            'wpjobportal_email_delivery_recorded',
            $result,
            array(
                'source' => $source,
                'recipient_count' => self::recipient_count($to),
                'attachment_count' => self::attachment_count($attachments),
                'error' => $error,
            )
        );

        return $result;
    }

    /**
     * @return string
     */
    public static function html_content_type() {
        return 'text/html';
    }

    /**
     * Capture only the current wp_mail() error. Recipient data is discarded.
     *
     * @param WP_Error $error WordPress mail error.
     * @return void
     */
    public static function capture_mail_error($error) {
        if (is_wp_error($error)) {
            self::$captured_error = WPJOBPORTAL_Redactor::redact($error->get_error_message());
        }
    }

    /**
     * Get aggregate mail submission statistics.
     *
     * "Succeeded" means wp_mail() accepted the message; it does not prove final
     * inbox delivery.
     *
     * @return array
     */
    public static function get_stats() {
        $defaults = array(
            'attempted' => 0,
            'succeeded' => 0,
            'failed' => 0,
            'last_attempt' => '',
            'last_success' => '',
            'last_failure' => '',
            'last_error' => '',
            'last_source' => '',
            'last_result' => '',
        );
        $stats = get_option(self::STATS_OPTION, array());

        return wp_parse_args(is_array($stats) ? $stats : array(), $defaults);
    }

    /**
     * @param bool   $result Submission result.
     * @param string $error Redacted error.
     * @param array  $context Privacy-safe context.
     * @return void
     */
    private static function record_result($result, $error, $context) {
        $stats = self::get_stats();
        $now = current_time('mysql', true);

        $stats['attempted'] = absint($stats['attempted']) + 1;
        $stats['last_attempt'] = $now;
        $stats['last_source'] = isset($context['source']) ? sanitize_key($context['source']) : '';

        if ($result) {
            $stats['succeeded'] = absint($stats['succeeded']) + 1;
            $stats['last_success'] = $now;
            $stats['last_result'] = 'success';
            $outcome = 'success';
        } else {
            $stats['failed'] = absint($stats['failed']) + 1;
            $stats['last_failure'] = $now;
            $stats['last_result'] = 'failed';
            $stats['last_error'] = substr(
                WPJOBPORTAL_Redactor::redact(sanitize_text_field((string) $error)),
                0,
                500
            );
            $outcome = 'failed';
        }

        if (get_option(self::STATS_OPTION, null) === null) {
            add_option(self::STATS_OPTION, $stats, '', 'no');
        } else {
            update_option(self::STATS_OPTION, $stats, false);
        }

        WPJOBPORTAL_Audit_Service::record('notification_submission', $outcome, $context);
    }

    /**
     * @param string|array $recipients Recipients.
     * @return int
     */
    private static function recipient_count($recipients) {
        if (is_array($recipients)) {
            return count(array_filter($recipients));
        }

        if (!is_string($recipients) || trim($recipients) === '') {
            return 0;
        }

        return count(array_filter(array_map('trim', explode(',', $recipients))));
    }

    /**
     * @param string|array $attachments Attachments.
     * @return int
     */
    private static function attachment_count($attachments) {
        if (is_array($attachments)) {
            return count(array_filter($attachments));
        }

        return is_string($attachments) && trim($attachments) !== '' ? 1 : 0;
    }
}
