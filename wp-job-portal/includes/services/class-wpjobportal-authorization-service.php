<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Object-level authorization for sensitive legacy download routes.
 *
 * A valid nonce proves request intent, but it does not prove that the caller
 * may read the requested candidate or entity. This service resolves the
 * requested object from the database and applies the same ownership and
 * visibility rules on every download request.
 */
class WPJOBPORTAL_Authorization_Service {

    const STATS_OPTION = 'wpjobportal_authorization_stats';

    /**
     * Authorize a single resume attachment by its resumefiles record.
     *
     * @param mixed $file_id        Resume-file record ID.
     * @param bool  $record_denial  Whether to retain a privacy-safe denial.
     * @return bool
     */
    public static function can_download_resume_file($file_id, $record_denial = true) {
        if (!is_numeric($file_id) || absint($file_id) < 1) {
            return self::deny('resume_attachment', 'invalid_object', $record_denial);
        }

        $query = wpjobportal::$_db->prepare(
            "SELECT resumeid
             FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumefiles`
             WHERE id = %d",
            absint($file_id)
        );
        $resume_id = wpjobportaldb::get_var($query);

        if (!is_numeric($resume_id) || absint($resume_id) < 1) {
            return self::deny('resume_attachment', 'object_not_found', $record_denial);
        }

        if (!self::can_access_resume(absint($resume_id), false)) {
            return self::deny('resume_attachment', 'object_access_denied', $record_denial);
        }

        return true;
    }

    /**
     * Authorize the ZIP download containing every attachment for one resume.
     *
     * @param mixed $resume_id      Resume record ID.
     * @param bool  $record_denial  Whether to retain a privacy-safe denial.
     * @return bool
     */
    public static function can_download_resume_archive($resume_id, $record_denial = true) {
        if (!self::can_access_resume($resume_id, false)) {
            return self::deny('resume_archive', 'object_access_denied', $record_denial);
        }

        return true;
    }

    /**
     * Authorize a custom-field attachment and confirm that the requested file
     * is actually referenced by the requested entity's saved field data.
     *
     * @param string $upload_for    company, job, resume, profile or user.
     * @param mixed  $entity_id     Entity record ID.
     * @param string $file_name     Requested basename.
     * @param bool   $record_denial Whether to retain a privacy-safe denial.
     * @return bool
     */
    public static function can_download_custom_file($upload_for, $entity_id, $file_name, $record_denial = true) {
        $upload_for = sanitize_key($upload_for);
        if ($upload_for === 'profile') {
            $upload_for = 'user';
        }

        if (!in_array($upload_for, array('company', 'job', 'resume', 'user'), true)
            || !is_numeric($entity_id)
            || absint($entity_id) < 1
        ) {
            return self::deny('custom_field', 'invalid_object', $record_denial);
        }

        $file_name = sanitize_file_name(
            wpjobportalphplib::wpJP_basename(
                wpjobportalphplib::wpJP_clean_file_path((string) $file_name)
            )
        );
        if ($file_name === '') {
            return self::deny('custom_field', 'invalid_file', $record_denial);
        }

        $entity = self::get_custom_file_entity($upload_for, absint($entity_id));
        if (!$entity) {
            return self::deny('custom_field', 'object_not_found', $record_denial);
        }

        $allowed = false;
        if (current_user_can('manage_options')) {
            $allowed = true;
        } elseif ($upload_for === 'resume') {
            $allowed = self::can_access_resume(absint($entity_id), false);
        } elseif ($upload_for === 'user') {
            $allowed = self::current_internal_user_id() === absint($entity_id);
        } else {
            $owner_id = isset($entity->uid) ? absint($entity->uid) : 0;
            $allowed = $owner_id > 0 && $owner_id === self::current_internal_user_id();

            // Published company and currently published job attachments are
            // intentionally public because their links are rendered on the
            // corresponding public detail pages.
            if (!$allowed && $upload_for === 'company') {
                $allowed = isset($entity->status) && (int) $entity->status === 1;
            } elseif (!$allowed && $upload_for === 'job') {
                $allowed = !empty($entity->is_public);
            }
        }

        if (!$allowed) {
            return self::deny('custom_field', 'object_access_denied', $record_denial);
        }

        if (!self::filename_is_referenced($file_name, isset($entity->params) ? $entity->params : '')) {
            return self::deny('custom_field', 'file_not_referenced', $record_denial);
        }

        return true;
    }

    /**
     * Apply the configured resume contact-detail policy to a resume object.
     *
     * @param mixed $resume_id      Resume record ID.
     * @param bool  $record_denial  Whether to retain a privacy-safe denial.
     * @return bool
     */
    public static function can_access_resume($resume_id, $record_denial = true) {
        if (!is_numeric($resume_id) || absint($resume_id) < 1) {
            return self::deny('resume', 'invalid_object', $record_denial);
        }

        $query = wpjobportal::$_db->prepare(
            "SELECT id, uid, status, searchable
             FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume`
             WHERE id = %d",
            absint($resume_id)
        );
        $resume = wpjobportaldb::get_row($query);
        if (!$resume) {
            return self::deny('resume', 'object_not_found', $record_denial);
        }

        if (current_user_can('manage_options')) {
            return true;
        }

        $internal_user_id = self::current_internal_user_id();
        if ($internal_user_id > 0 && absint($resume->uid) === $internal_user_id) {
            return true;
        }

        // Visitor-created resumes are associated with a server-side session
        // until the visitor completes or abandons the application.
        if (isset($_SESSION['wp-wpjobportal']['resumeid'])
            && absint($_SESSION['wp-wpjobportal']['resumeid']) === absint($resume_id)
        ) {
            return true;
        }

        $is_public_resume = (int) $resume->status === 1 && (int) $resume->searchable === 1;
        $contact_policy = absint(
            wpjobportal::$_config->getConfigValue('resume_contact_detail')
        );

        if ($contact_policy === 1 && $is_public_resume) {
            return true;
        }

        $user = WPJOBPORTALincluder::getObjectClass('user');
        $is_employer = is_object($user) && $user->isemployer();
        if ($contact_policy === 2 && $is_employer && $is_public_resume) {
            return true;
        }

        // An employer always reaches the resumes that applied to their own jobs
        // or that they already unlocked. Quick-apply and visitor resumes are
        // never searchable, so the contact-detail policy cannot cover them.
        if ($is_employer
            && self::employer_has_resume_relationship(absint($resume_id), $internal_user_id)
        ) {
            return true;
        }

        return self::deny('resume', 'object_access_denied', $record_denial);
    }

    /**
     * Return privacy-safe authorization diagnostics.
     *
     * @return array
     */
    public static function get_status() {
        $stats = get_option(self::STATS_OPTION, array());
        if (!is_array($stats)) {
            $stats = array();
        }

        return array(
            'protected_routes' => 3,
            'denied' => isset($stats['denied']) ? absint($stats['denied']) : 0,
            'last_denied_at' => isset($stats['last_denied_at'])
                ? sanitize_text_field($stats['last_denied_at'])
                : '',
            'last_resource' => isset($stats['last_resource'])
                ? sanitize_key($stats['last_resource'])
                : '',
            'last_reason' => isset($stats['last_reason'])
                ? sanitize_key($stats['last_reason'])
                : '',
        );
    }

    /**
     * @return int
     */
    private static function current_internal_user_id() {
        $user = WPJOBPORTALincluder::getObjectClass('user');
        if (!is_object($user)) {
            return 0;
        }

        return absint($user->uid());
    }

    /**
     * @param int $resume_id
     * @param int $employer_id
     * @return bool
     */
    private static function employer_has_resume_relationship($resume_id, $employer_id) {
        if ($resume_id < 1 || $employer_id < 1) {
            return false;
        }

        $query = wpjobportal::$_db->prepare(
            "SELECT COUNT(jobapply.id)
             FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` AS jobapply
             INNER JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                ON job.id = jobapply.jobid
             WHERE job.uid = %d
               AND jobapply.cvid = %d",
            $employer_id,
            $resume_id
        );
        if (absint(wpjobportaldb::get_var($query)) > 0) {
            return true;
        }

        $query = wpjobportal::$_db->prepare(
            "SELECT COUNT(id)
             FROM `" . wpjobportal::$_db->prefix . "wj_portal_employer_view_resume`
             WHERE uid = %d
               AND resumeid = %d",
            $employer_id,
            $resume_id
        );

        return absint(wpjobportaldb::get_var($query)) > 0;
    }

    /**
     * @param string $upload_for
     * @param int    $entity_id
     * @return object|null
     */
    private static function get_custom_file_entity($upload_for, $entity_id) {
        if ($upload_for === 'company') {
            $query = wpjobportal::$_db->prepare(
                "SELECT uid, status, params
                 FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies`
                 WHERE id = %d",
                $entity_id
            );
        } elseif ($upload_for === 'job') {
            $today = current_time('Y-m-d');
            $query = wpjobportal::$_db->prepare(
                "SELECT uid, status, params,
                    CASE
                        WHEN status = 1
                         AND DATE(startpublishing) <= %s
                         AND DATE(stoppublishing) >= %s
                        THEN 1 ELSE 0
                    END AS is_public
                 FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs`
                 WHERE id = %d",
                $today,
                $today,
                $entity_id
            );
        } elseif ($upload_for === 'resume') {
            $query = wpjobportal::$_db->prepare(
                "SELECT uid, status, searchable, params
                 FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume`
                 WHERE id = %d",
                $entity_id
            );
        } else {
            $query = wpjobportal::$_db->prepare(
                "SELECT id, params
                 FROM `" . wpjobportal::$_db->prefix . "wj_portal_users`
                 WHERE id = %d",
                $entity_id
            );
        }

        return wpjobportaldb::get_row($query);
    }

    /**
     * @param string $file_name
     * @param string $params
     * @return bool
     */
    private static function filename_is_referenced($file_name, $params) {
        if (!is_string($params) || $params === '') {
            return false;
        }

        $decoded = json_decode($params, true);
        if (!is_array($decoded)) {
            return false;
        }

        return self::array_contains_filename($decoded, $file_name);
    }

    /**
     * @param array  $values
     * @param string $file_name
     * @return bool
     */
    private static function array_contains_filename($values, $file_name) {
        foreach ($values as $value) {
            if (is_array($value) && self::array_contains_filename($value, $file_name)) {
                return true;
            }

            if (is_string($value)) {
                $candidate = sanitize_file_name(
                    wpjobportalphplib::wpJP_basename(
                        htmlspecialchars_decode($value, ENT_QUOTES)
                    )
                );
                if ($candidate !== '' && hash_equals($file_name, $candidate)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param string $resource
     * @param string $reason
     * @param bool   $record
     * @return false
     */
    private static function deny($resource, $reason, $record) {
        if (!$record) {
            return false;
        }

        $stats = get_option(self::STATS_OPTION, array());
        if (!is_array($stats)) {
            $stats = array();
        }

        $stats = array(
            'denied' => isset($stats['denied']) ? absint($stats['denied']) + 1 : 1,
            'last_denied_at' => current_time('mysql', true),
            'last_resource' => sanitize_key($resource),
            'last_reason' => sanitize_key($reason),
        );

        if (get_option(self::STATS_OPTION, null) === null) {
            add_option(self::STATS_OPTION, $stats, '', 'no');
        } else {
            update_option(self::STATS_OPTION, $stats, false);
        }

        if (class_exists('WPJOBPORTAL_Audit_Service')) {
            WPJOBPORTAL_Audit_Service::record(
                'sensitive_download_denied',
                'warning',
                array(
                    'resource' => sanitize_key($resource),
                    'reason' => sanitize_key($reason),
                )
            );
        }

        return false;
    }
}
