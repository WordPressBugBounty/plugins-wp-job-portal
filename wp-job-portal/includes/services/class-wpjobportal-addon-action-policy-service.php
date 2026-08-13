<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Central authorization policies for coordinated add-on mutations.
 *
 * Add-on controllers keep their existing checks. This service runs before
 * controller construction so the shared dispatcher enforces a minimum policy
 * even when an older controller later loses one of its local checks.
 */
class WPJOBPORTAL_Addon_Action_Policy_Service {

    const STATS_OPTION = 'wpjobportal_addon_action_policy_stats';

    public static function authorize($module, $task, $channel) {
        $module = sanitize_key($module);
        $task = strtolower(preg_replace('/[^A-Za-z0-9_]/', '', (string) $task));
        $channel = sanitize_key($channel);
        $policies = self::policies();
        $route = $module . '.' . $task;

        if (!isset($policies[$route])) {
            return true;
        }

        $policy = $policies[$route];
        $method = isset($_SERVER['REQUEST_METHOD'])
            ? strtoupper(sanitize_text_field(wp_unslash($_SERVER['REQUEST_METHOD'])))
            : '';

        if (!in_array($method, $policy['methods'], true)) {
            return self::deny($module, $task, $channel, 'http_method');
        }
        if (!self::role_is_allowed($policy['role'])) {
            return self::deny($module, $task, $channel, 'capability');
        }
        if (!empty($policy['nonce_action'])) {
            $nonce = WPJOBPORTALrequest::getVar($policy['nonce_field']);
            if (!is_string($nonce)
                || $nonce === ''
                || !wp_verify_nonce($nonce, $policy['nonce_action'])
            ) {
                return self::deny($module, $task, $channel, 'nonce');
            }
        }
        if (!self::schema_is_valid($policy['schema'])) {
            return self::deny($module, $task, $channel, 'input_schema');
        }
        if (!empty($policy['ownership'])
            && !self::ownership_is_allowed($policy['ownership'], $module, $task)
        ) {
            return self::deny($module, $task, $channel, 'object_ownership');
        }
        if ($module === 'purchase'
            && class_exists('WPJOBPORTAL_Payment_Service')
            && !WPJOBPORTAL_Payment_Service::authorize_route($task)
        ) {
            return self::deny($module, $task, $channel, 'payment_boundary');
        }

        return true;
    }

    /**
     * Build policies from the reviewed add-on route manifest.
     */
    public static function policies() {
        $routes = WPJOBPORTAL_Route_Registry_Service::coordinated_addon_routes();
        $read_only = array(
            'export.exportallresume',
            'export.exportresume',
            'tag.gettagsbytagname',
        );
        $policies = array();

        foreach ($routes as $module => $tasks) {
            foreach ($tasks as $task) {
                $task = strtolower($task);
                $route = $module . '.' . $task;
                if (in_array($route, $read_only, true)) {
                    continue;
                }

                $policies[$route] = array(
                    'methods' => self::methods_for($module, $task),
                    'role' => self::role_for($module, $task),
                    'nonce_action' => self::nonce_for($module, $task),
                    'nonce_field' => '_wpnonce',
                    'schema' => self::schema_for($module, $task),
                    'ownership' => self::ownership_for($module, $task),
                );
            }
        }

        $filtered = apply_filters('wpjobportal_addon_action_policies', $policies);
        return is_array($filtered) ? $filtered : $policies;
    }

    public static function get_status() {
        $policies = self::policies();
        $stats = get_option(self::STATS_OPTION, array());
        if (!is_array($stats)) {
            $stats = array();
        }

        $post_only = 0;
        $ownership = 0;
        $schema = 0;
        foreach ($policies as $policy) {
            if ($policy['methods'] === array('POST')) {
                $post_only++;
            }
            if (!empty($policy['ownership'])) {
                $ownership++;
            }
            if (!empty($policy['schema'])) {
                $schema++;
            }
        }

        return array(
            'protected_routes' => count($policies),
            'post_only_routes' => $post_only,
            'ownership_routes' => $ownership,
            'schema_routes' => $schema,
            'denied' => isset($stats['denied']) ? absint($stats['denied']) : 0,
            'last_denied_at' => isset($stats['last_denied_at']) ? sanitize_text_field($stats['last_denied_at']) : '',
            'last_module' => isset($stats['last_module']) ? sanitize_key($stats['last_module']) : '',
            'last_task' => isset($stats['last_task']) ? sanitize_key($stats['last_task']) : '',
            'last_reason' => isset($stats['last_reason']) ? sanitize_key($stats['last_reason']) : '',
        );
    }

    private static function methods_for($module, $task) {
        if ($module === 'purchase') {
            if (in_array($task, array('stripewebhook', 'paypalwebhook'), true)
                || strpos($task, 'stripe') !== false
            ) {
                return array('POST');
            }
            if (strpos($task, 'woocommerce') !== false || strpos($task, 'woocomme') !== false) {
                return array('GET');
            }
            return array('GET', 'POST');
        }

        if (strpos($task, 'save') === 0
            || $module === 'addressdata'
            || $module === 'paymentmethodconfiguration'
            || $module === 'visitorcanaddjob'
        ) {
            return array('POST');
        }

        return array('GET', 'POST');
    }

    private static function role_for($module, $task) {
        $route = $module . '.' . $task;
        if ($module === 'purchase'
            && in_array($task, array('stripewebhook', 'paypalwebhook'), true)
        ) {
            return 'public';
        }
        if ($module === 'jobalert'
            && in_array($task, array('savejobalert', 'unsubscribejobalertsettingemail'), true)
        ) {
            return 'public';
        }
        if ($module === 'visitorcanaddjob') {
            return 'public';
        }
        if ($route === 'multicompany.addviewcontactdetail') {
            return 'jobseeker_or_administrator';
        }
        // Mirrors the core resume.saveresume policy: visitors may submit a
        // resume when the administrator enabled visitor resume submission.
        if ($route === 'multiresume.saveresume') {
            return 'resume_submitter';
        }
        if ($route === 'jobalert.sendjobalert') {
            return 'administrator';
        }
        if ($module === 'purchasehistory'
            && in_array($task, array('savepurchasehistory', 'publish', 'unpublish', 'remove'), true)
        ) {
            return 'administrator';
        }
        if ($module === 'purchasehistory'
            && in_array($task, array('saveuserpackage'), true)
        ) {
            return 'jobseeker_or_employer';
        }

        $admin_modules = array(
            'addressdata',
            'credits',
            'invoice',
            'package',
            'paymentmethodconfiguration',
            'tag',
        );
        $admin_tasks = array(
            'approve',
            'reject',
            'publish',
            'unpublish',
            'publishmessages',
            'unpublishmessages',
            'publishinvoices',
            'unpublishinvoices',
            'enforcedeletemessage',
        );
        if (in_array($module, $admin_modules, true)
            || in_array($task, $admin_tasks, true)
        ) {
            return 'administrator';
        }

        if (in_array($module, array(
            'copyjob',
            'departments',
            'featuredcompany',
            'featuredjob',
            'folder',
            'folderresume',
            'multicompany',
            'resumesearch',
            'smartmatching',
        ), true)) {
            return 'employer_or_administrator';
        }

        if (in_array($module, array(
            'message',
        ), true)) {
            return 'jobseeker_or_employer';
        }

        if (in_array($module, array(
            'coverletter',
            'featureresume',
            'multiresume',
            'shortlist',
        ), true)) {
            return 'jobseeker_or_administrator';
        }

        return 'portal_user_or_administrator';
    }

    private static function nonce_for($module, $task) {
        if ($module === 'purchase') {
            return '';
        }

        $module_nonces = array(
            'addressdata' => 'wpjobportal_address_data_nonce',
            'coverletter' => 'wpjobportal_coverletter_nonce',
            'credits' => 'wpjobportal_credit_nonce',
            'departments' => 'wpjobportal_department_nonce',
            'folder' => 'wpjobportal_folder_nonce',
            'folderresume' => 'delete-resumefromfolder',
            'invoice' => 'update-status',
            'jobalert' => 'wpjobportal_job_alert_nonce',
            'joblistingenhancer' => 'wpjobportal_joblistingenhancer_nonce',
            'message' => 'wpjobportal_message_nonce',
            'multicompany' => 'wpjobportal_company_nonce',
            'multiresume' => 'wpjobportal_resume_nonce',
            'paymentmethodconfiguration' => 'store-payment-config',
            'resumesearch' => 'wpjobportal_resume_search_nonce',
            'shortlist' => 'wpjobportal_shortlist_job_nonce',
            'smartmatching' => 'wpjobportal_addonbp_nonce',
            'tag' => 'wpjobportal_tag_nonce',
            'visitorcanaddjob' => 'wpjobportal_job_nonce',
        );
        $route_nonces = array(
            'copyjob.addtocopyjob' => 'copy-job',
            'featuredcompany.addtofeaturedcompany' => 'featured-company',
            'featuredcompany.removefeaturedcompany' => 'delete-featuredcompany',
            'featuredjob.addtofeaturedjobs' => 'featured-job',
            'featuredjob.removefeaturedjob' => 'delete-featured-job',
            'featureresume.addtofeaturedresume' => 'featured-resume',
            'featureresume.removefeaturedresume' => 'delete-featured-resume',
            'folder.removeresumefromfolder' => 'delete-resumefromfolder',
            'invoice.remove' => 'delete-invoice',
            'package.savepackage' => 'store-package',
            'package.remove' => 'delete-package',
            'package.publish' => 'delete-package',
            'package.unpublish' => 'delete-package',
            'purchasehistory.savepurchasehistory' => 'store-purchase-history',
            'purchasehistory.saveuserpackage' => 'store-user-package',
            'purchasehistory.publish' => 'update-status',
            'purchasehistory.unpublish' => 'update-status',
            'purchasehistory.remove' => 'delete-user-package',
            'purchasehistory.cancelsubscription' => 'cancel-subscription',
        );
        $route = $module . '.' . $task;

        if (isset($route_nonces[$route])) {
            return $route_nonces[$route];
        }
        return isset($module_nonces[$module]) ? $module_nonces[$module] : '';
    }

    private static function schema_for($module, $task) {
        if ($module === 'purchase') {
            if (in_array($task, array('stripewebhook', 'paypalwebhook'), true)) {
                return array();
            }
            return array(
                '__payment_target' => array(
                    'type' => 'any_id',
                    'required' => false,
                    'fields' => array('wpjobportalid', 'id', 'packageid'),
                ),
            );
        }

        if (strpos($task, 'save') === 0) {
            return array('id' => array('type' => 'optional_id'));
        }

        if (in_array($task, array(
            'remove',
            'publish',
            'unpublish',
            'approve',
            'reject',
            'publishmessages',
            'unpublishmessages',
            'publishinvoices',
            'unpublishinvoices',
        ), true)) {
            return array(
                '__target' => array(
                    'type' => 'any_id',
                    'required' => true,
                    'fields' => self::target_id_fields(),
                ),
            );
        }

        return array();
    }

    /**
     * Request fields that can name the record an add-on mutation targets.
     *
     * Legacy add-on links do not share one identifier name: the message
     * add-on passes `messageid` where most others pass `id`.
     *
     * @return array
     */
    private static function target_id_fields() {
        return array('id', 'wpjobportalid', 'wpjobportal-cb', 'messageid', 'invoiceid');
    }

    private static function ownership_for($module, $task) {
        if ($module === 'multicompany' && $task === 'addviewcontactdetail') {
            return '';
        }
        if (in_array($task, array(
            'approve',
            'reject',
            'publish',
            'unpublish',
            'publishmessages',
            'unpublishmessages',
            'publishinvoices',
            'unpublishinvoices',
        ), true)) {
            return '';
        }

        // Removing a resume from a folder is keyed by resumeid + folder id
        // rather than a record id. The boundary is the folder: the resume
        // itself belongs to a jobseeker, not to the employer acting here.
        if ($task === 'removeresumefromfolder') {
            return 'folder_owner';
        }

        $ownership_modules = array(
            'coverletter',
            'departments',
            'featuredcompany',
            'featuredjob',
            'featureresume',
            'folder',
            'folderresume',
            'message',
            'multicompany',
            'multiresume',
            'resumesearch',
            'shortlist',
        );
        return in_array($module, $ownership_modules, true) ? 'portal_record' : '';
    }

    private static function role_is_allowed($role) {
        if ($role === 'public') {
            return true;
        }
        if (self::is_administrator()) {
            return true;
        }

        if ($role === 'resume_submitter') {
            return self::is_resume_submitter();
        }

        if (!is_user_logged_in()) {
            return false;
        }

        $user = WPJOBPORTALincluder::getObjectClass('user');
        if ($role === 'administrator') {
            return false;
        }
        if ($role === 'employer_or_administrator') {
            return is_object($user) && method_exists($user, 'isemployer') && $user->isemployer();
        }
        if ($role === 'jobseeker_or_administrator') {
            return is_object($user) && method_exists($user, 'isjobseeker') && $user->isjobseeker();
        }
        if ($role === 'jobseeker_or_employer') {
            return is_object($user) &&
                (
                    (method_exists($user, 'isjobseeker') && $user->isjobseeker()) ||
                    (method_exists($user, 'isemployer') && $user->isemployer())
                );
        }
        return is_object($user)
            && method_exists($user, 'isWPJOBPORTALUser')
            && $user->isWPJOBPORTALUser();
    }

    /**
     * Jobseekers may always submit a resume. Visitors may do so only while
     * visitor resume submission is enabled and the add-on providing it is
     * active, which is the same condition the add-resume layout renders on.
     *
     * @return bool
     */
    private static function is_resume_submitter() {
        $user = WPJOBPORTALincluder::getObjectClass('user');
        if (!is_object($user)) {
            return false;
        }
        if (method_exists($user, 'isjobseeker') && $user->isjobseeker()) {
            return true;
        }
        return method_exists($user, 'isguest')
            && $user->isguest()
            && in_array('visitorapplyjob', (array) wpjobportal::$_active_addons, true)
            && absint(wpjobportal::$_config->getConfigValue('visitor_can_add_resume')) === 1;
    }

    private static function is_administrator() {
        return current_user_can('manage_options')
            || current_user_can('wpjobportal')
            || (isset(wpjobportal::$_common)
                && is_object(wpjobportal::$_common)
                && method_exists(wpjobportal::$_common, 'wpjp_isadmin')
                && wpjobportal::$_common->wpjp_isadmin());
    }

    private static function schema_is_valid($schema) {
        foreach ((array) $schema as $field => $rule) {
            if ($rule['type'] === 'any_id') {
                $found = false;
                foreach ($rule['fields'] as $candidate) {
                    $value = WPJOBPORTALrequest::getVar($candidate);
                    if (is_array($value)) {
                        $value = reset($value);
                    }
                    if ($value !== null && $value !== '' && absint($value) > 0) {
                        $found = true;
                        break;
                    }
                }
                if (!empty($rule['required']) && !$found) {
                    return false;
                }
                continue;
            }

            $value = WPJOBPORTALrequest::getVar($field);
            if ($rule['type'] === 'optional_id') {
                if ($value !== null && $value !== '' && absint($value) < 1) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Enforce ownership for existing records. New records (id absent) are
     * authorized by role and assigned to the current user by the model.
     */
    private static function ownership_is_allowed($ownership, $module, $task) {
        if ($ownership === '' || self::is_administrator()) {
            return true;
        }

        $user = WPJOBPORTALincluder::getObjectClass('user');
        $uid = is_object($user) && method_exists($user, 'uid') ? absint($user->uid()) : 0;

        if ($ownership === 'folder_owner') {
            return $uid > 0 && self::owns_folder($uid);
        }
        if ($ownership !== 'portal_record') {
            return $uid > 0;
        }

        $ids = array();
        foreach (self::target_id_fields() as $field) {
            $candidate = WPJOBPORTALrequest::getVar($field);
            if ($candidate === null || $candidate === '') {
                continue;
            }
            foreach ((array) $candidate as $value) {
                $value = absint($value);
                if ($value > 0 && !in_array($value, $ids, true)) {
                    $ids[] = $value;
                }
            }
        }
        if (empty($ids) && strpos($task, 'save') === 0) {
            return true;
        }
        if (empty($ids)) {
            return false;
        }
        if ($uid < 1) {
            // A visitor owns nothing in the user tables; the only record it
            // may act on is the resume held in its own session.
            return self::owns_guest_resume($module, $ids);
        }

        $table_map = array(
            'coverletter' => 'wj_portal_coverletters',
            'departments' => 'wj_portal_departments',
            'featuredcompany' => 'wj_portal_companies',
            'featuredjob' => 'wj_portal_jobs',
            'featureresume' => 'wj_portal_resume',
            'folder' => 'wj_portal_folders',
            'folderresume' => 'wj_portal_folderresumes',
            'multicompany' => 'wj_portal_companies',
            'multiresume' => 'wj_portal_resume',
            'resumesearch' => 'wj_portal_resumesearches',
            'shortlist' => 'wj_portal_jobshortlist',
        );
        if ($module === 'message') {
            global $wpdb;
            $table = $wpdb->prefix . 'wj_portal_messages';
            $placeholders = implode(', ', array_fill(0, count($ids), '%d'));
            $sql = $wpdb->prepare(
                "SELECT COUNT(DISTINCT id) FROM `{$table}` WHERE id IN ({$placeholders}) AND (employerid = %d OR jobseekerid = %d)",
                array_merge($ids, array($uid, $uid))
            );
            return absint($wpdb->get_var($sql)) === count($ids);
        }
        if (!isset($table_map[$module])) {
            return true;
        }

        global $wpdb;
        $table = $wpdb->prefix . $table_map[$module];
        $placeholders = implode(', ', array_fill(0, count($ids), '%d'));
        $sql = $wpdb->prepare(
            "SELECT COUNT(DISTINCT id) FROM `{$table}` WHERE id IN ({$placeholders}) AND uid = %d",
            array_merge($ids, array($uid))
        );
        return absint($wpdb->get_var($sql)) === count($ids);
    }

    /**
     * Match the core boundary: a guest resume submission is tracked in the
     * session, so exactly that one resume is in scope.
     *
     * @param string $module
     * @param array  $ids
     * @return bool
     */
    private static function owns_guest_resume($module, $ids) {
        if ($module !== 'multiresume' || count($ids) !== 1) {
            return false;
        }

        $user = WPJOBPORTALincluder::getObjectClass('user');
        if (!is_object($user) || !method_exists($user, 'isguest') || !$user->isguest()) {
            return false;
        }
        if (!isset($_SESSION['wp-wpjobportal']['resumeid'])) {
            return false;
        }
        return absint($_SESSION['wp-wpjobportal']['resumeid']) === absint($ids[0]);
    }

    /**
     * The frontend link carries the folder as `folderid`; the admin link
     * carries it as `fd`. Both name the folder whose contents are edited.
     */
    private static function owns_folder($uid) {
        $folder_id = 0;
        foreach (array('folderid', 'fd') as $field) {
            $candidate = WPJOBPORTALrequest::getVar($field);
            if (is_array($candidate)) {
                $candidate = reset($candidate);
            }
            if (absint($candidate) > 0) {
                $folder_id = absint($candidate);
                break;
            }
        }
        if ($folder_id < 1) {
            return false;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'wj_portal_folders';
        $sql = $wpdb->prepare(
            "SELECT COUNT(id) FROM `{$table}` WHERE id = %d AND uid = %d",
            $folder_id,
            $uid
        );
        return absint($wpdb->get_var($sql)) > 0;
    }

    private static function deny($module, $task, $channel, $reason) {
        $stats = get_option(self::STATS_OPTION, array());
        if (!is_array($stats)) {
            $stats = array();
        }
        $stats = array(
            'denied' => isset($stats['denied']) ? absint($stats['denied']) + 1 : 1,
            'last_denied_at' => current_time('mysql', true),
            'last_module' => sanitize_key($module),
            'last_task' => sanitize_key($task),
            'last_reason' => sanitize_key($reason),
        );
        update_option(self::STATS_OPTION, $stats, false);

        if (class_exists('WPJOBPORTAL_Audit_Service')) {
            WPJOBPORTAL_Audit_Service::record(
                'addon_mutation_denied',
                'warning',
                array(
                    'module' => sanitize_key($module),
                    'task' => sanitize_key($task),
                    'channel' => sanitize_key($channel),
                    'reason' => sanitize_key($reason),
                )
            );
        }
        return false;
    }
}
