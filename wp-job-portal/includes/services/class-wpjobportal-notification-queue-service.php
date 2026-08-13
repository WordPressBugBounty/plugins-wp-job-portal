<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Durable, privacy-aware queue for WP Job Portal core notifications.
 *
 * Queue mode is opt-in. Payloads are encrypted before database storage and are
 * removed after successful delivery. If durable storage is unavailable, the
 * caller can immediately fall back to the existing direct wp_mail() path.
 */
class WPJOBPORTAL_Notification_Queue_Service {

    const CRON_HOOK = 'wpjobportal_notification_queue_process';
    const MODE_OPTION = 'wpjobportal_notification_queue_enabled';
    const SCHEMA_OPTION = 'wpjobportal_notification_queue_schema';
    const STATS_OPTION = 'wpjobportal_notification_queue_stats';
    const SCHEMA_VERSION = '1';
    const BATCH_SIZE = 5;
    const MAX_ATTEMPTS = 5;
    const LOCK_TTL = 900;
    const RETENTION_DAYS = 7;

    private static $schema_ready = null;

    /**
     * @return string
     */
    public static function table_name() {
        global $wpdb;

        return $wpdb->prefix . 'wj_portal_notification_queue';
    }

    /**
     * Queue mode affects new notifications only. Existing queued work is
     * allowed to drain after queue mode is disabled.
     *
     * @return bool
     */
    public static function is_enabled() {
        return get_option(self::MODE_OPTION, '0') === '1';
    }

    /**
     * Create or update the local queue table without changing the legacy core
     * updater. This is safe to call repeatedly on existing and new sites.
     *
     * @return true|WP_Error
     */
    public static function ensure_schema() {
        global $wpdb;

        if (self::$schema_ready === true) {
            return true;
        }
        if (get_option(self::SCHEMA_OPTION, '') === self::SCHEMA_VERSION && self::schema_is_ready()) {
            self::$schema_ready = true;
            return true;
        }

        $table = self::table_name();
        $charset_collate = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql = "CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            job_key char(64) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            payload longtext NOT NULL,
            source varchar(64) NOT NULL DEFAULT 'system_email',
            recipient_count smallint(5) unsigned NOT NULL DEFAULT 0,
            attachment_count smallint(5) unsigned NOT NULL DEFAULT 0,
            attempts smallint(5) unsigned NOT NULL DEFAULT 0,
            max_attempts smallint(5) unsigned NOT NULL DEFAULT 5,
            available_at datetime NOT NULL,
            locked_at datetime NULL DEFAULT NULL,
            lock_token char(64) NOT NULL DEFAULT '',
            last_error text NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            completed_at datetime NULL DEFAULT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY job_key (job_key),
            KEY due_jobs (status,available_at),
            KEY stale_jobs (status,locked_at),
            KEY completed_jobs (completed_at)
        ) ENGINE=InnoDB {$charset_collate};";

        dbDelta($sql);

        if (!self::schema_is_ready()) {
            self::$schema_ready = false;
            return new WP_Error(
                'wpjobportal_queue_schema_failed',
                __('The notification queue table could not be created.', 'wp-job-portal')
            );
        }

        update_option(self::SCHEMA_OPTION, self::SCHEMA_VERSION, false);
        self::$schema_ready = true;

        return true;
    }

    /**
     * @return bool
     */
    public static function table_exists() {
        global $wpdb;

        $table = self::table_name();
        $found = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table)));

        return $found === $table;
    }

    /**
     * @return bool
     */
    private static function schema_is_ready() {
        global $wpdb;

        if (!self::table_exists()) {
            return false;
        }

        $table = self::table_name();
        $columns = $wpdb->get_col("SHOW COLUMNS FROM `{$table}`", 0);
        $required = array(
            'id',
            'job_key',
            'status',
            'payload',
            'source',
            'attempts',
            'max_attempts',
            'available_at',
            'locked_at',
            'lock_token',
            'last_error',
            'created_at',
            'updated_at',
            'completed_at',
        );

        return empty(array_diff($required, (array) $columns));
    }

    /**
     * Store one encrypted mail payload.
     *
     * @param string|array $to Recipients.
     * @param string       $subject Subject.
     * @param string       $message Message body.
     * @param string|array $headers Headers.
     * @param string|array $attachments Attachment paths.
     * @param string       $source Privacy-safe source.
     * @param string       $idempotency_key Optional caller-provided stable key.
     * @return true|WP_Error
     */
    public static function enqueue($to, $subject, $message, $headers, $attachments, $source, $idempotency_key = '') {
        global $wpdb;

        $schema = self::ensure_schema();
        if (is_wp_error($schema)) {
            self::record_stat('fallbacks', $schema->get_error_message());
            return $schema;
        }

        $payload = self::encrypt_payload(
            array(
                'to' => $to,
                'subject' => (string) $subject,
                'message' => (string) $message,
                'headers' => $headers,
                'attachments' => $attachments,
            )
        );
        if (is_wp_error($payload)) {
            self::record_stat('fallbacks', $payload->get_error_message());
            return $payload;
        }

        $source = sanitize_key($source);
        if ($source === '') {
            $source = 'system_email';
        }

        $job_key = self::make_job_key($source, $idempotency_key);
        $now = current_time('mysql', true);
        $table = self::table_name();
        $inserted = $wpdb->insert(
            $table,
            array(
                'job_key' => $job_key,
                'status' => 'pending',
                'payload' => $payload,
                'source' => $source,
                'recipient_count' => self::recipient_count($to),
                'attachment_count' => self::attachment_count($attachments),
                'attempts' => 0,
                'max_attempts' => self::MAX_ATTEMPTS,
                'available_at' => $now,
                'lock_token' => '',
                'last_error' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ),
            array('%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s')
        );

        if ($inserted === false) {
            $existing = $wpdb->get_var(
                $wpdb->prepare("SELECT id FROM `{$table}` WHERE job_key = %s LIMIT 1", $job_key)
            );
            if ($existing) {
                self::record_stat('duplicates');
                WPJOBPORTAL_Audit_Service::record(
                    'notification_duplicate_suppressed',
                    'info',
                    array('source' => $source)
                );
                self::ensure_schedule(5);
                return true;
            }

            $error = WPJOBPORTAL_Redactor::redact(
                $wpdb->last_error !== '' ? $wpdb->last_error : __('The notification could not be added to the queue.', 'wp-job-portal')
            );
            self::record_stat('fallbacks', $error);
            return new WP_Error('wpjobportal_queue_insert_failed', $error);
        }

        self::record_stat('queued');
        WPJOBPORTAL_Audit_Service::record(
            'notification_queued',
            'success',
            array(
                'source' => $source,
                'recipient_count' => self::recipient_count($to),
                'attachment_count' => self::attachment_count($attachments),
            )
        );
        self::ensure_schedule(5);

        return true;
    }

    /**
     * Process a bounded number of due messages.
     *
     * @return void
     */
    public static function run_batch() {
        global $wpdb;

        if (!self::table_exists()) {
            if (self::is_enabled()) {
                $schema = self::ensure_schema();
                if (!is_wp_error($schema)) {
                    self::ensure_schedule(60);
                }
            }
            return;
        }

        self::release_stale_locks();
        self::cleanup_old_rows();

        $table = self::table_name();
        $now = current_time('mysql', true);
        $ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT id FROM `{$table}`
                 WHERE status IN ('pending','retry') AND available_at <= %s
                 ORDER BY available_at ASC, id ASC
                 LIMIT %d",
                $now,
                self::BATCH_SIZE
            )
        );

        foreach ($ids as $id) {
            self::process_job(absint($id));
        }

        if (self::has_outstanding_jobs()) {
            self::ensure_schedule(60);
        } elseif (self::has_retained_rows()) {
            self::ensure_schedule(DAY_IN_SECONDS);
        }
    }

    /**
     * Recover a missing cron event and a lazily missing schema. Runs only for an
     * administrator so normal front-end requests do not perform maintenance.
     *
     * @return void
     */
    public static function maybe_recover() {
        if (!is_admin() || !current_user_can('wpjobportal')) {
            return;
        }

        if (self::is_enabled()) {
            $schema = self::ensure_schema();
            if (is_wp_error($schema)) {
                return;
            }
        }

        $next_run = wp_next_scheduled(self::CRON_HOOK);
        $overdue = $next_run && $next_run < (time() - 300);
        if ($overdue) {
            wp_unschedule_event($next_run, self::CRON_HOOK);
            $next_run = false;
        }

        if (self::has_outstanding_jobs() && !$next_run) {
            self::ensure_schedule(60);
            WPJOBPORTAL_Audit_Service::record('notification_queue_cron_recovered', 'warning');
        } elseif (self::has_retained_rows() && !$next_run) {
            self::ensure_schedule(DAY_IN_SECONDS);
        }
    }

    /**
     * AJAX controls avoid redirect/header interactions with rendered admin
     * pages. No recipients or message content are returned.
     *
     * @return void
     */
    public static function handle_ajax_action() {
        if (!current_user_can('wpjobportal')) {
            wp_send_json_error(array('message' => __('Access denied.', 'wp-job-portal')), 403);
        }

        check_ajax_referer('wpjobportal_notification_queue_action', 'nonce');
        $queue_action = isset($_POST['queue_action'])
            ? sanitize_key(wp_unslash($_POST['queue_action']))
            : '';

        if ($queue_action === 'enable') {
            $schema = self::ensure_schema();
            if (is_wp_error($schema)) {
                wp_send_json_error(array('message' => $schema->get_error_message()), 500);
            }
            update_option(self::MODE_OPTION, '1', false);
            self::ensure_schedule(5);
            WPJOBPORTAL_Audit_Service::record('notification_queue_enabled', 'success');
        } elseif ($queue_action === 'disable') {
            update_option(self::MODE_OPTION, '0', false);
            if (!self::has_outstanding_jobs()) {
                wp_clear_scheduled_hook(self::CRON_HOOK);
            }
            WPJOBPORTAL_Audit_Service::record('notification_queue_disabled', 'info');
        } elseif ($queue_action === 'run_now') {
            self::run_batch();
        } elseif ($queue_action === 'retry_failed') {
            $retried = self::retry_failed();
            if (is_wp_error($retried)) {
                wp_send_json_error(array('message' => $retried->get_error_message()), 500);
            }
        } else {
            wp_send_json_error(array('message' => __('Invalid queue action.', 'wp-job-portal')), 400);
        }

        wp_send_json_success(array('status' => self::get_status()));
    }

    /**
     * Return failed rows to the retry queue. Lifetime failure statistics remain
     * intact while each selected row receives one new bounded retry cycle.
     *
     * @return int|WP_Error
     */
    public static function retry_failed() {
        global $wpdb;

        if (!self::table_exists()) {
            return new WP_Error('wpjobportal_queue_missing', __('The notification queue table is not available.', 'wp-job-portal'));
        }

        $table = self::table_name();
        $now = current_time('mysql', true);
        $updated = $wpdb->query(
            $wpdb->prepare(
                "UPDATE `{$table}`
                 SET status = 'retry', attempts = 0, available_at = %s,
                     locked_at = NULL, lock_token = '', last_error = '', updated_at = %s
                 WHERE status = 'failed'",
                $now,
                $now
            )
        );

        if ($updated === false) {
            return new WP_Error(
                'wpjobportal_queue_retry_failed',
                WPJOBPORTAL_Redactor::redact($wpdb->last_error)
            );
        }

        if ($updated > 0) {
            self::ensure_schedule(5);
            WPJOBPORTAL_Audit_Service::record(
                'notification_queue_manual_retry',
                'info',
                array('count' => absint($updated))
            );
        }

        return absint($updated);
    }

    /**
     * @return array
     */
    public static function get_status() {
        global $wpdb;

        $counts = array(
            'pending' => 0,
            'retry' => 0,
            'processing' => 0,
            'failed' => 0,
            'completed' => 0,
        );
        $table_exists = self::table_exists();
        $schema_ready = $table_exists ? self::schema_is_ready() : false;
        $oldest_due = '';

        if ($schema_ready) {
            $table = self::table_name();
            $rows = $wpdb->get_results(
                "SELECT status, COUNT(*) AS total FROM `{$table}` GROUP BY status",
                ARRAY_A
            );
            foreach ((array) $rows as $row) {
                $status = isset($row['status']) ? sanitize_key($row['status']) : '';
                if (array_key_exists($status, $counts)) {
                    $counts[$status] = absint($row['total']);
                }
            }

            $oldest_due = (string) $wpdb->get_var(
                "SELECT MIN(available_at) FROM `{$table}` WHERE status IN ('pending','retry')"
            );
        }

        $outstanding = $counts['pending'] + $counts['retry'] + $counts['processing'];
        $next_run = wp_next_scheduled(self::CRON_HOOK);

        return array(
            'enabled' => self::is_enabled(),
            'table_exists' => $table_exists,
            'schema_ready' => $schema_ready,
            'counts' => $counts,
            'outstanding' => $outstanding,
            'oldest_due' => $oldest_due,
            'next_run' => $next_run ? absint($next_run) : 0,
            'worker_overdue' => $next_run && $next_run < (time() - 300),
            'stats' => self::get_stats(),
        );
    }

    /**
     * @return array
     */
    public static function get_stats() {
        $defaults = array(
            'queued' => 0,
            'duplicates' => 0,
            'delivered' => 0,
            'retried' => 0,
            'failed' => 0,
            'fallbacks' => 0,
            'last_event' => '',
            'last_event_at' => '',
            'last_error' => '',
        );
        $stats = get_option(self::STATS_OPTION, array());

        return wp_parse_args(is_array($stats) ? $stats : array(), $defaults);
    }

    /**
     * Record a direct-delivery fallback caused before enqueue could return a
     * normal WP_Error.
     *
     * @param string $error Redacted or raw error.
     * @return void
     */
    public static function record_fallback($error) {
        self::record_stat('fallbacks', $error);
    }

    /**
     * Clear cron state while preserving queued data and the administrator's
     * selected mode for safe reactivation.
     *
     * @return void
     */
    public static function deactivate() {
        wp_clear_scheduled_hook(self::CRON_HOOK);
    }

    /**
     * @param int $job_id Queue row id.
     * @return void
     */
    private static function process_job($job_id) {
        global $wpdb;

        $table = self::table_name();
        $token = hash('sha256', wp_generate_uuid4() . '|' . microtime(true));
        $now = current_time('mysql', true);
        $claimed = $wpdb->query(
            $wpdb->prepare(
                "UPDATE `{$table}`
                 SET status = 'processing', locked_at = %s, lock_token = %s,
                     attempts = attempts + 1, updated_at = %s
                 WHERE id = %d AND status IN ('pending','retry') AND available_at <= %s",
                $now,
                $token,
                $now,
                $job_id,
                $now
            )
        );
        if ($claimed !== 1) {
            return;
        }

        $job = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM `{$table}` WHERE id = %d AND lock_token = %s LIMIT 1", $job_id, $token),
            ARRAY_A
        );
        if (!is_array($job)) {
            return;
        }

        $payload = self::decrypt_payload($job['payload']);
        if (is_wp_error($payload)) {
            self::handle_failure($job, $token, $payload->get_error_message());
            return;
        }

        try {
            $result = WPJOBPORTAL_Notification_Service::deliver_now(
                $payload['to'],
                $payload['subject'],
                $payload['message'],
                $payload['headers'],
                $payload['attachments'],
                $job['source']
            );
        } catch (Throwable $throwable) {
            self::handle_failure(
                $job,
                $token,
                WPJOBPORTAL_Redactor::redact($throwable->getMessage())
            );
            return;
        }

        if ($result) {
            $updated = $wpdb->query(
                $wpdb->prepare(
                    "UPDATE `{$table}`
                     SET status = 'completed', payload = '', locked_at = NULL,
                         lock_token = '', last_error = '', completed_at = %s, updated_at = %s
                     WHERE id = %d AND lock_token = %s",
                    $now,
                    $now,
                    $job_id,
                    $token
                )
            );
            if ($updated === 1) {
                self::record_stat('delivered');
                WPJOBPORTAL_Audit_Service::record(
                    'notification_delivered',
                    'success',
                    array(
                        'source' => sanitize_key($job['source']),
                        'attempts' => absint($job['attempts']),
                    )
                );
            }
            return;
        }

        $stats = WPJOBPORTAL_Notification_Service::get_stats();
        $error = !empty($stats['last_error'])
            ? $stats['last_error']
            : __('wp_mail() rejected the queued notification.', 'wp-job-portal');
        self::handle_failure($job, $token, $error);
    }

    /**
     * @param array  $job Queue row.
     * @param string $token Claim token.
     * @param string $error Raw or redacted delivery error.
     * @return void
     */
    private static function handle_failure($job, $token, $error) {
        global $wpdb;

        $table = self::table_name();
        $attempts = absint($job['attempts']);
        $max_attempts = max(1, absint($job['max_attempts']));
        $error = substr(
            WPJOBPORTAL_Redactor::redact(sanitize_text_field((string) $error)),
            0,
            500
        );
        $now = current_time('mysql', true);

        if ($attempts >= $max_attempts) {
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE `{$table}`
                     SET status = 'failed', locked_at = NULL, lock_token = '',
                         last_error = %s, updated_at = %s
                     WHERE id = %d AND lock_token = %s",
                    $error,
                    $now,
                    absint($job['id']),
                    $token
                )
            );
            self::record_stat('failed', $error);
            WPJOBPORTAL_Audit_Service::record(
                'notification_queue_failed',
                'failed',
                array(
                    'source' => sanitize_key($job['source']),
                    'attempts' => $attempts,
                    'error' => $error,
                )
            );
            return;
        }

        $available_at = gmdate('Y-m-d H:i:s', time() + self::retry_delay($attempts));
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE `{$table}`
                 SET status = 'retry', available_at = %s, locked_at = NULL,
                     lock_token = '', last_error = %s, updated_at = %s
                 WHERE id = %d AND lock_token = %s",
                $available_at,
                $error,
                $now,
                absint($job['id']),
                $token
            )
        );
        self::record_stat('retried', $error);
        WPJOBPORTAL_Audit_Service::record(
            'notification_queue_retry',
            'warning',
            array(
                'source' => sanitize_key($job['source']),
                'attempts' => $attempts,
                'retry_delay' => self::retry_delay($attempts),
                'error' => $error,
            )
        );
    }

    /**
     * @return void
     */
    private static function release_stale_locks() {
        global $wpdb;

        $table = self::table_name();
        $stale_before = gmdate('Y-m-d H:i:s', time() - self::LOCK_TTL);
        $now = current_time('mysql', true);
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE `{$table}`
                 SET status = 'retry', available_at = %s, locked_at = NULL,
                     lock_token = '', updated_at = %s
                 WHERE status = 'processing' AND locked_at < %s",
                $now,
                $now,
                $stale_before
            )
        );
    }

    /**
     * @return void
     */
    private static function cleanup_old_rows() {
        global $wpdb;

        $table = self::table_name();
        $cutoff = gmdate('Y-m-d H:i:s', time() - (DAY_IN_SECONDS * self::RETENTION_DAYS));
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM `{$table}`
                 WHERE (status = 'completed' AND completed_at < %s)
                    OR (status = 'failed' AND updated_at < %s)",
                $cutoff,
                $cutoff
            )
        );
    }

    /**
     * @return bool
     */
    private static function has_outstanding_jobs() {
        global $wpdb;

        if (!self::table_exists()) {
            return false;
        }

        $table = self::table_name();
        $count = $wpdb->get_var(
            "SELECT COUNT(*) FROM `{$table}` WHERE status IN ('pending','retry','processing')"
        );

        return absint($count) > 0;
    }

    /**
     * Keep low-frequency maintenance alive until privacy-retention cleanup has
     * removed completed metadata and expired terminal failures.
     *
     * @return bool
     */
    private static function has_retained_rows() {
        global $wpdb;

        if (!self::table_exists()) {
            return false;
        }

        $table = self::table_name();
        $count = $wpdb->get_var(
            "SELECT COUNT(*) FROM `{$table}` WHERE status IN ('completed','failed')"
        );

        return absint($count) > 0;
    }

    /**
     * @param int $delay Seconds.
     * @return void
     */
    private static function ensure_schedule($delay) {
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_single_event(time() + max(1, absint($delay)), self::CRON_HOOK);
        }
    }

    /**
     * @param int $attempt Attempt number starting at one.
     * @return int
     */
    private static function retry_delay($attempt) {
        $delays = array(60, 300, 900, 3600, 21600);
        $index = max(0, min(absint($attempt) - 1, count($delays) - 1));

        return $delays[$index];
    }

    /**
     * @param string $source Source key.
     * @param string $idempotency_key Optional stable key.
     * @return string
     */
    private static function make_job_key($source, $idempotency_key) {
        $idempotency_key = sanitize_text_field((string) $idempotency_key);
        if ($idempotency_key !== '') {
            return hash('sha256', get_current_blog_id() . '|' . $source . '|' . $idempotency_key);
        }

        return hash('sha256', wp_generate_uuid4() . '|' . microtime(true));
    }

    /**
     * @param array $payload Mail payload.
     * @return string|WP_Error
     */
    private static function encrypt_payload($payload) {
        $json = wp_json_encode($payload);
        if (!is_string($json)) {
            return new WP_Error('wpjobportal_queue_encode_failed', __('The notification payload could not be encoded.', 'wp-job-portal'));
        }

        $key = self::encryption_key();

        try {
            if (function_exists('sodium_crypto_secretbox') && defined('SODIUM_CRYPTO_SECRETBOX_NONCEBYTES')) {
                $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
                $ciphertext = sodium_crypto_secretbox($json, $nonce, $key);
                return 's1:' . base64_encode($nonce . $ciphertext);
            }

            if (function_exists('openssl_encrypt')) {
                $iv = random_bytes(12);
                $tag = '';
                $ciphertext = openssl_encrypt(
                    $json,
                    'aes-256-gcm',
                    $key,
                    OPENSSL_RAW_DATA,
                    $iv,
                    $tag
                );
                if ($ciphertext !== false) {
                    return 'o1:' . base64_encode($iv . $tag . $ciphertext);
                }
            }
        } catch (Throwable $exception) {
            return new WP_Error(
                'wpjobportal_queue_encrypt_failed',
                WPJOBPORTAL_Redactor::redact($exception->getMessage())
            );
        }

        return new WP_Error(
            'wpjobportal_queue_crypto_unavailable',
            __('No supported payload-encryption provider is available.', 'wp-job-portal')
        );
    }

    /**
     * @param string $encrypted Encrypted envelope.
     * @return array|WP_Error
     */
    private static function decrypt_payload($encrypted) {
        $key = self::encryption_key();
        $parts = explode(':', (string) $encrypted, 2);
        if (count($parts) !== 2) {
            return new WP_Error('wpjobportal_queue_payload_invalid', __('The queued notification payload is invalid.', 'wp-job-portal'));
        }

        $binary = base64_decode($parts[1], true);
        if ($binary === false) {
            return new WP_Error('wpjobportal_queue_payload_invalid', __('The queued notification payload is invalid.', 'wp-job-portal'));
        }

        $json = false;
        try {
            if ($parts[0] === 's1' && function_exists('sodium_crypto_secretbox_open') && defined('SODIUM_CRYPTO_SECRETBOX_NONCEBYTES')) {
                $nonce = substr($binary, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
                $ciphertext = substr($binary, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
                $json = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);
            } elseif ($parts[0] === 'o1' && function_exists('openssl_decrypt')) {
                $iv = substr($binary, 0, 12);
                $tag = substr($binary, 12, 16);
                $ciphertext = substr($binary, 28);
                $json = openssl_decrypt(
                    $ciphertext,
                    'aes-256-gcm',
                    $key,
                    OPENSSL_RAW_DATA,
                    $iv,
                    $tag
                );
            }
        } catch (Throwable $throwable) {
            $json = false;
        }

        if (!is_string($json)) {
            return new WP_Error('wpjobportal_queue_decrypt_failed', __('The queued notification payload could not be decrypted.', 'wp-job-portal'));
        }

        $payload = json_decode($json, true);
        $required = array('to', 'subject', 'message', 'headers', 'attachments');
        if (!is_array($payload) || array_diff($required, array_keys($payload))) {
            return new WP_Error('wpjobportal_queue_payload_invalid', __('The queued notification payload is incomplete.', 'wp-job-portal'));
        }

        return $payload;
    }

    /**
     * @return string Binary 32-byte key.
     */
    private static function encryption_key() {
        $material = defined('AUTH_KEY') ? AUTH_KEY : wp_salt('auth');
        $material .= defined('SECURE_AUTH_KEY') ? SECURE_AUTH_KEY : wp_salt('secure_auth');
        $material .= '|' . get_current_blog_id() . '|wpjobportal-notification-queue-v1';

        return hash('sha256', $material, true);
    }

    /**
     * @param string $stat Counter name.
     * @param string $error Optional error.
     * @return void
     */
    private static function record_stat($stat, $error = '') {
        $stats = self::get_stats();
        if (array_key_exists($stat, $stats)) {
            $stats[$stat] = absint($stats[$stat]) + 1;
        }
        $stats['last_event'] = sanitize_key($stat);
        $stats['last_event_at'] = current_time('mysql', true);
        if ($error !== '') {
            $stats['last_error'] = substr(
                WPJOBPORTAL_Redactor::redact(sanitize_text_field((string) $error)),
                0,
                500
            );
        } elseif ($stat === 'delivered') {
            $stats['last_error'] = '';
        }

        if (get_option(self::STATS_OPTION, null) === null) {
            add_option(self::STATS_OPTION, $stats, '', 'no');
        } else {
            update_option(self::STATS_OPTION, $stats, false);
        }
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
