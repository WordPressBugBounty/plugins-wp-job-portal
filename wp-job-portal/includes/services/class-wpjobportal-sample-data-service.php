<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Durable ownership registry and conservative cleanup for newly installed
 * sample records. Historical, unmarked data is deliberately ignored.
 */
class WPJOBPORTAL_Sample_Data_Service {

    const REGISTRY_OPTION = 'wpjobportal_sample_data_registry_v1';
    const POST_BATCH_META = '_wpjobportal_sample_data_batch';
    const POST_TYPE_META = '_wpjobportal_sample_data_type';

    private static $batch_id = '';
    private static $transactional_tables = array();

    /**
     * Start one sample-install batch.
     *
     * @return string
     */
    public static function begin_batch() {
        if ('' === self::$batch_id) {
            self::$batch_id = wp_generate_uuid4();
        }

        $registry = self::registry();
        if (!isset($registry['batches'][self::$batch_id])) {
            $registry['batches'][self::$batch_id] = array(
                'created_at' => current_time('mysql', true),
                'created_by' => get_current_user_id(),
                'mode' => WPJOBPORTAL_Product_Mode_Service::get_mode(),
                'records' => array(),
            );
            self::save_registry($registry);
        }

        return self::$batch_id;
    }

    /**
     * Record the exact ID and an installation-time fingerprint.
     *
     * @param string $type Entity type.
     * @param int    $id   Entity ID.
     * @return bool
     */
    public static function record_entity($type, $id) {
        $type = sanitize_key($type);
        $id = absint($id);
        if (!in_array($type, self::types(), true) || $id <= 0) {
            return false;
        }

        $row = self::entity_row($type, $id);
        if (empty($row)) {
            return false;
        }

        $batch_id = self::begin_batch();
        $registry = self::registry();
        if (!isset($registry['batches'][$batch_id]['records'][$type])) {
            $registry['batches'][$batch_id]['records'][$type] = array();
        }

        $registry['batches'][$batch_id]['records'][$type][(string) $id] = array(
            'signature' => self::signature($row),
            'children' => array(),
        );

        if ('pages' === $type) {
            update_post_meta($id, self::POST_BATCH_META, $batch_id);
            update_post_meta($id, self::POST_TYPE_META, $type);
        }

        self::save_registry($registry);
        return true;
    }

    /**
     * Store the exact child-row sets created within the completed request.
     * A later child addition causes the parent to be skipped during cleanup.
     */
    public static function finalize_batch() {
        if ('' === self::$batch_id) {
            return;
        }

        $registry = self::registry();
        if (empty($registry['batches'][self::$batch_id]['records'])) {
            return;
        }

        foreach ($registry['batches'][self::$batch_id]['records'] as $type => &$records) {
            foreach ($records as $id => &$record) {
                $record['children'] = self::current_children($type, absint($id));
            }
            unset($record);
        }
        unset($records);

        self::save_registry($registry);
    }

    /**
     * Return a read-only cleanup preview.
     *
     * @return array
     */
    public static function get_preview() {
        $plan = self::build_plan();
        $counts = array();
        $total = 0;
        $eligible = 0;
        $skipped = 0;

        foreach (self::types() as $type) {
            $counts[$type] = array('tracked' => 0, 'eligible' => 0, 'skipped' => 0);
        }

        foreach ($plan as $type => $records) {
            foreach ($records as $record) {
                $counts[$type]['tracked']++;
                $total++;
                if (!empty($record['eligible'])) {
                    $counts[$type]['eligible']++;
                    $eligible++;
                } else {
                    $counts[$type]['skipped']++;
                    $skipped++;
                }
            }
        }

        return array(
            'has_markers' => $total > 0,
            'tracked' => $total,
            'eligible' => $eligible,
            'skipped' => $skipped,
            'counts' => $counts,
        );
    }

    /**
     * Delete only records that still match strong plugin-owned markers.
     * Users, settings, payments, credits and uploaded files are never deleted.
     *
     * @return array
     */
    public static function cleanup() {
        $result = array(
            'deleted' => 0,
            'skipped' => 0,
            'failed' => 0,
            'by_type' => array(),
        );

        foreach (self::types() as $type) {
            $result['by_type'][$type] = array('deleted' => 0, 'skipped' => 0, 'failed' => 0);
        }

        foreach (array('applications', 'jobs', 'resumes', 'companies', 'pages') as $type) {
            $plan = self::build_plan();
            if (empty($plan[$type])) {
                continue;
            }

            foreach ($plan[$type] as $id => $record) {
                if (empty($record['eligible'])) {
                    $result['skipped']++;
                    $result['by_type'][$type]['skipped']++;
                    continue;
                }

                $deleted = self::delete_entity($type, absint($id), $record);
                if ($deleted) {
                    self::remove_registry_record($record['batch_id'], $type, absint($id));
                    $result['deleted']++;
                    $result['by_type'][$type]['deleted']++;
                } else {
                    $result['failed']++;
                    $result['by_type'][$type]['failed']++;
                }
            }
        }

        if (class_exists('WPJOBPORTAL_Audit_Service')) {
            WPJOBPORTAL_Audit_Service::record(
                'sample_data_cleanup',
                $result['failed'] > 0 ? 'failed' : 'success',
                array(
                    'deleted' => absint($result['deleted']),
                    'skipped' => absint($result['skipped']),
                    'failed' => absint($result['failed']),
                )
            );
        }

        return $result;
    }

    /*
     * Who owns the sample data.
     *
     * The administrator chooses, in the wizard or later from the Setup page:
     * an existing WordPress user for each role, or nobody. The plugin never
     * creates a WordPress user; "Add new user" opens WordPress's own screen.
     * What it does create is the WP Job Portal profile row that makes an
     * existing user an employer or a job seeker, which is what registering
     * would have done for them.
     *
     * There is no fallback any more. It used to hand unassigned sample data to
     * "the first active employer on the site", which on a site with customers
     * put sample jobs in a real employer's My Jobs.
     */

    /** Portal role ids. */
    const ROLE_EMPLOYER = 1;
    const ROLE_JOBSEEKER = 2;

    /**
     * Sample record types each role owns.
     *
     * @param int $role Portal role id.
     * @return array
     */
    private static function owned_types($role) {
        return self::ROLE_EMPLOYER === (int) $role
            ? array('companies', 'jobs')
            : array('resumes', 'applications');
    }

    /**
     * WordPress users who may own sample data for a role.
     *
     * Someone who already has the other portal role is left out: one account
     * is an employer or a job seeker, not both.
     *
     * @param int $role Portal role id.
     * @return array of (object) {id, text}, starting with "Leave unassigned".
     */
    public static function owner_candidates($role) {
        global $wpdb;

        $role = (int) $role;
        $other = self::ROLE_EMPLOYER === $role ? self::ROLE_JOBSEEKER : self::ROLE_EMPLOYER;
        $portal = array();
        foreach ((array) wpjobportaldb_direct::get_results($wpdb->prepare("SELECT `uid`, `roleid` FROM %i WHERE `uid` > 0", $wpdb->prefix . 'wj_portal_users')) as $row) {
            $portal[(int) $row->uid] = (int) $row->roleid;
        }

        // One query for who is an administrator, rather than a capability
        // check per user - on a site with thousands of accounts that was
        // hundreds of queries to draw two dropdowns.
        $admins = array_flip(array_map('intval', get_users(array('role' => 'administrator', 'fields' => 'ID'))));

        $list = array((object) array('id' => 0, 'text' => __('Leave unassigned', 'wp-job-portal')));
        foreach (get_users(array('orderby' => 'display_name', 'number' => 500, 'fields' => array('ID', 'display_name', 'user_login'))) as $user) {
            $uid = (int) $user->ID;
            if (isset($portal[$uid]) && $portal[$uid] === $other) {
                continue;
            }
            $text = $user->display_name . ' (' . $user->user_login . ')';
            if (isset($portal[$uid]) && $portal[$uid] === $role) {
                $text .= ' - ' . (self::ROLE_EMPLOYER === $role ? __('employer', 'wp-job-portal') : __('job seeker', 'wp-job-portal'));
            } elseif (isset($admins[$uid])) {
                $text .= ' - ' . __('administrator', 'wp-job-portal');
            }
            $list[] = (object) array('id' => $uid, 'text' => $text);
        }

        return $list;
    }

    /**
     * The owner to suggest when there is only one sensible answer.
     *
     * A site that already has exactly one employer (or one job seeker) almost
     * certainly wants that account to own the demo; anything else - none, or
     * several - is a choice the administrator makes, so nothing is suggested.
     *
     * @param int $role Portal role id.
     * @return int WordPress user id, or 0.
     */
    public static function default_owner($role) {
        global $wpdb;

        $uids = wpjobportaldb_direct::get_col($wpdb->prepare(
            "SELECT DISTINCT p.`uid` FROM %i p
               INNER JOIN %i u ON u.`ID` = p.`uid`
              WHERE p.`roleid` = %d AND p.`status` = 1 AND p.`uid` > 0
              LIMIT 2",
            $wpdb->prefix . 'wj_portal_users',
            $wpdb->users,
            (int) $role
        ));

        return count($uids) === 1 ? (int) $uids[0] : 0;
    }

    /**
     * Where to see the sample data once it is in, for the Setup Complete screen.
     *
     * @return array employer, jobseeker and jobs URLs; a page that does not
     *               exist is left out rather than linked to nothing.
     */
    public static function result_links() {
        global $wpdb;

        $page = function ($shortcode) use ($wpdb) {
            return (int) wpjobportaldb_direct::get_var($wpdb->prepare(
                "SELECT `ID` FROM %i WHERE `post_type` = 'page' AND `post_status` = 'publish' AND `post_content` LIKE %s ORDER BY `ID` ASC LIMIT 1",
                $wpdb->posts,
                '%' . $wpdb->esc_like('[' . $shortcode . ']') . '%'
            ));
        };

        $links = array();
        $employer = $page('wpjobportal_employer_controlpanel');
        $jobseeker = $page('wpjobportal_jobseeker_controlpanel');
        if ($employer > 0) {
            $links['employer'] = get_permalink($employer);
        }
        if ($jobseeker > 0) {
            $links['jobseeker'] = get_permalink($jobseeker);
            $links['jobs'] = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme' => 'job', 'wpjobportallt' => 'newestjobs', 'wpjobportalpageid' => $jobseeker));
        }

        return array_filter($links);
    }

    /**
     * The portal profile a WordPress user owns sample data through.
     *
     * @param int  $wp_user_id WordPress user id.
     * @param int  $role       Portal role id.
     * @param bool $create     Write the profile, or only check it could be.
     * @return int|WP_Error Portal user id (0 when only checking), or why not.
     */
    public static function portal_account_for($wp_user_id, $role, $create = true) {
        global $wpdb;

        $wp_user_id = absint($wp_user_id);
        $role = (int) $role;
        $user = get_userdata($wp_user_id);
        if (!$user) {
            return new WP_Error('no_user', __('That user does not exist.', 'wp-job-portal'));
        }

        $row = wpjobportaldb_direct::get_row($wpdb->prepare(
            "SELECT `id`, `roleid` FROM %i WHERE `uid` = %d ORDER BY `id` ASC LIMIT 1",
            $wpdb->prefix . 'wj_portal_users',
            $wp_user_id
        ));

        if ($row && (int) $row->roleid > 0 && (int) $row->roleid !== $role) {
            return new WP_Error('other_role', sprintf(
                /* translators: %s: user's display name. */
                self::ROLE_EMPLOYER === $role
                    ? /* translators: %s: so cannot own the sample companies and jobs wp job portal s  */ __('%s already has a job seeker account, so cannot own the sample companies and jobs.', 'wp-job-portal')
                    : /* translators: %s: so cannot own the sample companies and jobs wp job portal s  */ __('%s already has an employer account, so cannot own the sample resumes and applications.', 'wp-job-portal'),
                $user->display_name
            ));
        }

        if (!$create) {
            return $row ? (int) $row->id : 0;
        }

        if ($row) {
            if ((int) $row->roleid !== $role) {
                wpjobportaldb_direct::update($wpdb->prefix . 'wj_portal_users', array('roleid' => $role), array('id' => (int) $row->id));
            }
            return (int) $row->id;
        }

        wpjobportaldb_direct::insert($wpdb->prefix . 'wj_portal_users', array(
            'uid' => $wp_user_id,
            'roleid' => $role,
            'first_name' => $user->first_name !== '' ? $user->first_name : $user->display_name,
            'last_name' => $user->last_name,
            'emailaddress' => $user->user_email,
            'status' => 1,
            'created' => current_time('mysql'),
        ));

        return (int) $wpdb->insert_id;
    }

    /**
     * Check an employer and job seeker choice before anything is written.
     *
     * @param int $employer_wp_id  WordPress user id, or 0.
     * @param int $jobseeker_wp_id WordPress user id, or 0.
     * @return true|WP_Error
     */
    public static function validate_owners($employer_wp_id, $jobseeker_wp_id) {
        $employer_wp_id = absint($employer_wp_id);
        $jobseeker_wp_id = absint($jobseeker_wp_id);

        if ($employer_wp_id > 0 && $employer_wp_id === $jobseeker_wp_id) {
            return new WP_Error('same_user', __('Choose different users for the employer and the job seeker. One account cannot be both.', 'wp-job-portal'));
        }
        foreach (array(self::ROLE_EMPLOYER => $employer_wp_id, self::ROLE_JOBSEEKER => $jobseeker_wp_id) as $role => $wp_id) {
            if ($wp_id > 0) {
                $check = self::portal_account_for($wp_id, $role, false);
                if (is_wp_error($check)) {
                    return $check;
                }
            }
        }

        return true;
    }

    /**
     * Give the untouched sample records of one role to a new owner.
     *
     * Only records that still match their fingerprint move; anything the
     * administrator has edited stays with whoever it belongs to now. The
     * fingerprint is taken again afterwards, so cleanup still recognises the
     * records - a new owner is not an edit.
     *
     * @param int $role       Portal role id.
     * @param int $wp_user_id WordPress user id, or 0 to leave them unassigned.
     * @return array|WP_Error moved, kept.
     */
    public static function assign_owner($role, $wp_user_id) {
        global $wpdb;

        $role = (int) $role;
        $portal_id = 0;
        if (absint($wp_user_id) > 0) {
            $portal_id = self::portal_account_for($wp_user_id, $role);
            if (is_wp_error($portal_id)) {
                return $portal_id;
            }
        }

        $tables = array(
            'companies' => 'wj_portal_companies',
            'jobs' => 'wj_portal_jobs',
            'resumes' => 'wj_portal_resume',
            'applications' => 'wj_portal_jobapply',
        );
        $registry = self::registry();
        $moved = 0;
        $kept = 0;

        foreach ($registry['batches'] as $batch_id => $batch) {
            foreach (self::owned_types($role) as $type) {
                if (empty($batch['records'][$type]) || !is_array($batch['records'][$type])) {
                    continue;
                }
                foreach ($batch['records'][$type] as $id => $record) {
                    $row = self::entity_row($type, $id);
                    $untouched = !empty($row) && isset($record['signature'])
                        && (hash_equals((string) $record['signature'], self::signature($row))
                            || hash_equals((string) $record['signature'], self::legacy_signature($row)));
                    if (!$untouched) {
                        $kept++;
                        continue;
                    }
                    if ((int) $row['uid'] !== $portal_id) {
                        wpjobportaldb_direct::update($wpdb->prefix . $tables[$type], array('uid' => $portal_id), array('id' => absint($id)));
                        $row = self::entity_row($type, $id);
                    }
                    $registry['batches'][$batch_id]['records'][$type][$id]['signature'] = self::signature($row);
                    $moved++;
                }
            }
        }

        self::save_registry($registry);

        return array('moved' => $moved, 'kept' => $kept);
    }

    /**
     * The current owner of the sample data for a role, as a WordPress user id.
     *
     * @param int $role Portal role id.
     * @return int 0 when unassigned or mixed.
     */
    public static function current_owner($role) {
        global $wpdb;

        $tables = array('companies' => 'wj_portal_companies', 'jobs' => 'wj_portal_jobs', 'resumes' => 'wj_portal_resume', 'applications' => 'wj_portal_jobapply');
        $owners = array();
        foreach (self::owned_types($role) as $type) {
            foreach (self::tracked_ids($type) as $id) {
                $uid = wpjobportaldb_direct::get_var($wpdb->prepare("SELECT `uid` FROM %i WHERE `id` = %d", $wpdb->prefix . $tables[$type], $id));
                if ($uid !== null) {
                    $owners[(int) $uid] = true;
                }
            }
        }
        if (count($owners) !== 1) {
            return 0;
        }
        $portal_id = (int) key($owners);

        return $portal_id > 0
            ? (int) wpjobportaldb_direct::get_var($wpdb->prepare("SELECT `uid` FROM %i WHERE `id` = %d", $wpdb->prefix . 'wj_portal_users', $portal_id))
            : 0;
    }

    /**
     * Ids of the sample records of one type that still exist.
     *
     * For anything that should not count sample data as the site's own - the
     * onboarding checklist, for one.
     *
     * @param string $type companies, jobs, resumes, applications or pages.
     * @return int[]
     */
    public static function tracked_ids($type) {
        $ids = array();
        foreach (self::registry()['batches'] as $batch) {
            if (!empty($batch['records'][$type]) && is_array($batch['records'][$type])) {
                foreach (array_keys($batch['records'][$type]) as $id) {
                    $ids[] = absint($id);
                }
            }
        }

        return array_values(array_unique(array_filter($ids)));
    }

    /**
     * Build dependency-aware eligibility without changing the database.
     *
     * @return array
     */
    private static function build_plan() {
        global $wpdb;

        $registry = self::registry();
        $plan = array_fill_keys(self::types(), array());

        foreach ($registry['batches'] as $batch_id => $batch) {
            if (empty($batch['records']) || !is_array($batch['records'])) {
                continue;
            }
            foreach ($batch['records'] as $type => $records) {
                if (!isset($plan[$type]) || !is_array($records)) {
                    continue;
                }
                foreach ($records as $id => $record) {
                    $id = absint($id);
                    $row = self::entity_row($type, $id);
                    $eligible = !empty($row)
                        && isset($record['signature'])
                        && (hash_equals((string) $record['signature'], self::signature($row))
                            || hash_equals((string) $record['signature'], self::legacy_signature($row)));

                    if ($eligible && 'pages' === $type) {
                        $eligible = (string) get_post_meta($id, self::POST_BATCH_META, true) === (string) $batch_id
                            && 'pages' === (string) get_post_meta($id, self::POST_TYPE_META, true)
                            && !self::page_is_in_use($id);
                    }

                    if ($eligible && in_array($type, array('companies', 'jobs', 'resumes'), true)) {
                        $eligible = self::children_match(
                            isset($record['children']) && is_array($record['children']) ? $record['children'] : array(),
                            self::current_children($type, $id)
                        );
                    }

                    if ($eligible && 'pages' !== $type && !self::entity_tables_are_transactional($type)) {
                        $eligible = false;
                    }

                    $plan[$type][$id] = array(
                        'eligible' => $eligible,
                        'batch_id' => (string) $batch_id,
                        'children' => isset($record['children']) && is_array($record['children']) ? $record['children'] : array(),
                    );
                }
            }
        }

        foreach ($plan['jobs'] as $id => &$record) {
            if (empty($record['eligible'])) {
                continue;
            }
            $application_ids = self::dependent_ids('jobapply', 'jobid', $id);
            foreach ($application_ids as $application_id) {
                if (empty($plan['applications'][$application_id]['eligible'])) {
                    $record['eligible'] = false;
                    break;
                }
            }
            if ($record['eligible'] && self::table_exists('jobshortlist')) {
                $shortlist_count = absint(wpjobportaldb_direct::get_var($wpdb->prepare(
                    "SELECT COUNT(`id`) FROM %i WHERE `jobid` = %d",
                    $wpdb->prefix . 'wj_portal_jobshortlist',
                    $id
                )));
                if ($shortlist_count > 0) {
                    $record['eligible'] = false;
                }
            }
        }
        unset($record);

        foreach ($plan['resumes'] as $id => &$record) {
            if (empty($record['eligible'])) {
                continue;
            }
            $application_ids = self::dependent_ids('jobapply', 'cvid', $id);
            foreach ($application_ids as $application_id) {
                if (empty($plan['applications'][$application_id]['eligible'])) {
                    $record['eligible'] = false;
                    break;
                }
            }
        }
        unset($record);

        foreach ($plan['companies'] as $id => &$record) {
            if (empty($record['eligible'])) {
                continue;
            }
            $job_ids = self::dependent_ids('jobs', 'companyid', $id);
            foreach ($job_ids as $job_id) {
                if (empty($plan['jobs'][$job_id]['eligible'])) {
                    $record['eligible'] = false;
                    break;
                }
            }
            if ($record['eligible'] && self::table_exists('departments')) {
                $department_count = absint(wpjobportaldb_direct::get_var($wpdb->prepare(
                    "SELECT COUNT(`id`) FROM %i WHERE `companyid` = %d",
                    $wpdb->prefix . 'wj_portal_departments',
                    $id
                )));
                if ($department_count > 0) {
                    $record['eligible'] = false;
                }
            }
        }
        unset($record);

        return $plan;
    }

    /**
     * @param string $type   Entity type.
     * @param int    $id     Entity ID.
     * @param array  $record Plan record.
     * @return bool
     */
    private static function delete_entity($type, $id, $record) {
        global $wpdb;

        if ('pages' === $type) {
            return false !== wp_delete_post($id, true);
        }

        $tables = array(
            'applications' => 'jobapply',
            'jobs' => 'jobs',
            'resumes' => 'resume',
            'companies' => 'companies',
        );
        if (!isset($tables[$type])) {
            return false;
        }
        if (!self::entity_tables_are_transactional($type)) {
            return false;
        }

        wpjobportaldb_direct::query('START TRANSACTION');
        $allowed_child_tables = array_keys(self::child_tables($type));
        foreach ((array) $record['children'] as $table => $ids) {
            $table = sanitize_key($table);
            if (!in_array($table, $allowed_child_tables, true)) {
                continue;
            }
            foreach ((array) $ids as $child_id => $signature) {
                if (!is_numeric($child_id)) {
                    $child_id = $signature;
                }
                $child_deleted = wpjobportaldb_direct::delete(
                    $wpdb->prefix . 'wj_portal_' . sanitize_key($table),
                    array('id' => absint($child_id)),
                    array('%d')
                );
                if (1 !== $child_deleted) {
                    wpjobportaldb_direct::query('ROLLBACK');
                    return false;
                }
            }
        }

        $deleted = wpjobportaldb_direct::delete(
            $wpdb->prefix . 'wj_portal_' . $tables[$type],
            array('id' => $id),
            array('%d')
        );
        if (1 !== $deleted) {
            wpjobportaldb_direct::query('ROLLBACK');
            return false;
        }

        wpjobportaldb_direct::query('COMMIT');

        do_action('wpjobportal_sample_data_entity_deleted', $type, $id);
        return true;
    }

    /**
     * @return array
     */
    private static function entity_row($type, $id) {
        global $wpdb;

        $id = absint($id);
        if ('pages' === $type) {
            $post = get_post($id);
            if (!$post) {
                return array();
            }
            return array(
                'ID' => (string) $post->ID,
                'post_type' => (string) $post->post_type,
                'post_status' => (string) $post->post_status,
                'post_name' => (string) $post->post_name,
                'post_title' => (string) $post->post_title,
                'post_content' => (string) $post->post_content,
            );
        }

        $tables = array(
            'companies' => 'wj_portal_companies',
            'jobs' => 'wj_portal_jobs',
            'resumes' => 'wj_portal_resume',
            'applications' => 'wj_portal_jobapply',
        );
        if (!isset($tables[$type])) {
            return array();
        }

        $row = wpjobportaldb_direct::get_row($wpdb->prepare('SELECT * FROM %i WHERE `id` = %d', $wpdb->prefix . $tables[$type], $id), ARRAY_A);
        return is_array($row) ? $row : array();
    }

    /**
     * @return array
     */
    private static function current_children($type, $id) {
        $tables = self::child_tables($type);
        $children = array();
        foreach ($tables as $table => $column) {
            $children[$table] = self::dependent_records($table, $column, $id);
        }
        return $children;
    }

    /**
     * Fixed relationship allowlist used by preview and deletion.
     *
     * @return array
     */
    private static function child_tables($type) {
        $tables = array();
        if ('companies' === $type) {
            $tables = array('companycities' => 'companyid');
        } elseif ('jobs' === $type) {
            $tables = array('jobcities' => 'jobid');
        } elseif ('resumes' === $type) {
            $tables = array(
                'resumeaddresses' => 'resumeid',
                'resumeemployers' => 'resumeid',
                'resumefiles' => 'resumeid',
                'resumeinstitutes' => 'resumeid',
                'resumelanguages' => 'resumeid',
            );
        }
        return $tables;
    }

    /**
     * Return exact child IDs and row fingerprints so edits are protected.
     *
     * @return array
     */
    private static function dependent_records($table, $column, $id) {
        global $wpdb;

        $table = sanitize_key($table);
        $column = sanitize_key($column);
        $rows = wpjobportaldb_direct::get_results($wpdb->prepare(
            "SELECT * FROM %i WHERE %i = %d ORDER BY `id` ASC",
            $wpdb->prefix . 'wj_portal_' . $table,
            $column,
            absint($id)
        ), ARRAY_A);
        $records = array();
        foreach ((array) $rows as $row) {
            if (!isset($row['id'])) {
                continue;
            }
            $records[(string) absint($row['id'])] = self::signature($row);
        }
        return $records;
    }

    /**
     * @return array
     */
    private static function dependent_ids($table, $column, $id) {
        global $wpdb;

        $table = sanitize_key($table);
        $column = sanitize_key($column);
        $ids = wpjobportaldb_direct::get_col($wpdb->prepare(
            "SELECT `id` FROM %i WHERE %i = %d ORDER BY `id` ASC",
            $wpdb->prefix . 'wj_portal_' . $table,
            $column,
            absint($id)
        ));
        return array_map('absint', (array) $ids);
    }

    /**
     * @return bool
     */
    private static function children_match($expected, $current) {
        ksort($expected);
        ksort($current);
        foreach ($expected as &$records) {
            $records = (array) $records;
            ksort($records);
        }
        unset($records);
        foreach ($current as &$records) {
            $records = (array) $records;
            ksort($records);
        }
        unset($records);
        return $expected === $current;
    }

    /**
     * @return bool
     */
    private static function page_is_in_use($id) {
        global $wpdb;

        $protected = array(
            absint(get_option('page_on_front')),
            absint(get_option('page_for_posts')),
        );
        $default_page = absint(wpjobportaldb_direct::get_var($wpdb->prepare(
            "SELECT `configvalue` FROM %i WHERE `configname` = %s LIMIT 1",
            $wpdb->prefix . 'wj_portal_config',
            'default_pageid'
        )));
        $protected[] = $default_page;
        if (in_array(absint($id), array_filter($protected), true)) {
            return true;
        }

        $children = absint(wpjobportaldb_direct::get_var($wpdb->prepare(
            "SELECT COUNT(`ID`) FROM %i WHERE `post_parent` = %d",
            $wpdb->posts,
            absint($id)
        )));
        $menu_items = absint(wpjobportaldb_direct::get_var($wpdb->prepare(
            "SELECT COUNT(`post_id`) FROM %i WHERE `meta_key` = '_menu_item_object_id' AND `meta_value` = %s",
            $wpdb->postmeta,
            (string) absint($id)
        )));
        return $children > 0 || $menu_items > 0;
    }

    /**
     * @return bool
     */
    private static function table_exists($suffix) {
        global $wpdb;
        $table = $wpdb->prefix . 'wj_portal_' . sanitize_key($suffix);
        return $table === wpjobportaldb_direct::get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table)));
    }

    /**
     * Require transactional storage before deleting related rows as one unit.
     * Cleanup remains available after the Release 2 storage conversion and
     * safely protects records while any required table is still non-InnoDB.
     *
     * @param string $type Entity type.
     * @return bool
     */
    private static function entity_tables_are_transactional($type) {
        $main_tables = array(
            'applications' => 'jobapply',
            'jobs' => 'jobs',
            'resumes' => 'resume',
            'companies' => 'companies',
        );
        if (!isset($main_tables[$type])) {
            return false;
        }

        $tables = array($main_tables[$type]);
        $tables = array_merge($tables, array_keys(self::child_tables($type)));
        foreach ($tables as $table) {
            if (!self::table_is_transactional($table)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param string $suffix WP Job Portal table suffix.
     * @return bool
     */
    private static function table_is_transactional($suffix) {
        global $wpdb;

        $suffix = sanitize_key($suffix);
        if (isset(self::$transactional_tables[$suffix])) {
            return self::$transactional_tables[$suffix];
        }

        $table = $wpdb->prefix . 'wj_portal_' . $suffix;
        $status = wpjobportaldb_direct::get_row($wpdb->prepare('SHOW TABLE STATUS LIKE %s', $wpdb->esc_like($table)), ARRAY_A);
        self::$transactional_tables[$suffix] = is_array($status)
            && isset($status['Engine'])
            && 'innodb' === strtolower((string) $status['Engine']);
        return self::$transactional_tables[$suffix];
    }

    /**
     * A fingerprint of what the customer could have changed.
     *
     * The view counter is left out. Every visit to a sample job, company or
     * resume page adds one to `hits`, so with it in, a single page view made a
     * record look edited and cleanup kept it for good - on a site where the
     * demo had been looked at at all, the sample jobs and companies stayed live.
     *
     * @param array $row Entity row.
     * @return string
     */
    private static function signature($row) {
        $row = (array) $row;
        unset($row['hits']);

        return hash('sha256', wp_json_encode(array_values($row)));
    }

    /**
     * The fingerprint as first recorded, view counter included, so sample data
     * imported before the counter was left out can still be cleaned up while it
     * has not been viewed.
     *
     * @param array $row Entity row.
     * @return string
     */
    private static function legacy_signature($row) {
        return hash('sha256', wp_json_encode(array_values((array) $row)));
    }

    /**
     * @return array
     */
    private static function registry() {
        $registry = get_option(self::REGISTRY_OPTION, array());
        if (!is_array($registry)) {
            $registry = array();
        }
        if (!isset($registry['version'])) {
            $registry['version'] = 1;
        }
        if (!isset($registry['batches']) || !is_array($registry['batches'])) {
            $registry['batches'] = array();
        }
        return $registry;
    }

    /**
     * @param array $registry Registry data.
     */
    private static function save_registry($registry) {
        if (false === get_option(self::REGISTRY_OPTION, false)) {
            add_option(self::REGISTRY_OPTION, $registry, '', false);
            return;
        }
        update_option(self::REGISTRY_OPTION, $registry, false);
    }

    /**
     * @param string $batch_id Batch ID.
     * @param string $type     Entity type.
     * @param int    $id       Entity ID.
     */
    private static function remove_registry_record($batch_id, $type, $id) {
        $registry = self::registry();
        if (isset($registry['batches'][$batch_id]['records'][$type][(string) $id])) {
            unset($registry['batches'][$batch_id]['records'][$type][(string) $id]);
        }
        if (empty($registry['batches'][$batch_id]['records'][$type])) {
            unset($registry['batches'][$batch_id]['records'][$type]);
        }
        if (empty($registry['batches'][$batch_id]['records'])) {
            unset($registry['batches'][$batch_id]);
        }
        self::save_registry($registry);
    }

    /**
     * @return array
     */
    private static function types() {
        return array('companies', 'jobs', 'resumes', 'applications', 'pages');
    }
}
