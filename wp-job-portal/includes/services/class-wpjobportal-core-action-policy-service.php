<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Central authorization policies for state-changing core controller routes.
 *
 * The legacy controllers keep their existing checks. This service runs before
 * controller construction so method, capability, nonce, identifier schema and
 * object ownership are enforced consistently at the dispatch boundary.
 */
class WPJOBPORTAL_Core_Action_Policy_Service {

    const STATS_OPTION = 'wpjobportal_core_action_policy_stats';

    /**
     * Authorize a registered core route when it changes application state.
     *
     * Read-only routes are intentionally absent from the policy map and retain
     * the Beta 2E route-registry behavior.
     *
     * @param string $module  Core module.
     * @param string $task    Controller method.
     * @param string $channel form or task.
     * @return bool
     */
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
        $request_method = isset($_SERVER['REQUEST_METHOD'])
            ? strtoupper(sanitize_text_field(wp_unslash($_SERVER['REQUEST_METHOD'])))
            : '';

        if (!in_array($request_method, $policy['methods'], true)) {
            return self::deny($module, $task, $channel, 'http_method');
        }

        if (!self::role_is_allowed($policy['role'])) {
            return self::deny($module, $task, $channel, 'capability');
        }

        $nonce_field = isset($policy['nonce_field']) ? $policy['nonce_field'] : '_wpnonce';
        $nonce = WPJOBPORTALrequest::getVar($nonce_field);
        if (!is_string($nonce)
            || $nonce === ''
            || !wp_verify_nonce($nonce, $policy['nonce_action'])
        ) {
            return self::deny($module, $task, $channel, 'nonce');
        }

        if (!self::schema_is_valid($policy['schema'])) {
            return self::deny($module, $task, $channel, 'input_schema');
        }

        if ($policy['ownership'] !== ''
            && !self::ownership_is_allowed($policy['ownership'])
        ) {
            return self::deny($module, $task, $channel, 'object_ownership');
        }

        return true;
    }

    /**
     * Return all core mutation policies.
     *
     * Every registered core action except the six reviewed read-only download
     * and lookup routes receives an explicit policy.
     *
     * @return array
     */
    public static function policies() {
        $routes = WPJOBPORTAL_Route_Registry_Service::core_routes();
        $read_only = array(
            'category.getcategorydatabyname',
            'city.getaddressdatabycityname',
            'customfield.downloadcustomfile',
            'resume.getallresumefiles',
            'resume.getresumefiledownloadbyid',
            'thirdpartyimport.getjobmanagerdatastats',
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
                    'role' => 'administrator',
                    'nonce_action' => self::nonce_action_for($module, $task),
                    'nonce_field' => self::nonce_field_for($module, $task),
                    'schema' => self::schema_for($module, $task),
                    'ownership' => '',
                );
            }
        }

        self::override_policy($policies, 'common.savenewinwpjobportal', 'wordpress_user_without_portal_role');

        self::override_policy($policies, 'company.savecompany', 'employer_or_administrator', 'company_save');
        self::override_policy($policies, 'company.remove', 'employer_or_administrator', 'company_remove');
        self::override_policy($policies, 'company.addviewcontactdetail', 'jobseeker_or_administrator');

        self::override_policy($policies, 'job.savejob', 'employer_or_administrator', 'job_save');
        self::override_policy($policies, 'job.remove', 'employer_or_administrator', 'job_remove');

        self::override_policy($policies, 'jobapply.jobapplyasvisitor', 'public');
        self::override_policy($policies, 'jobapply.applyonjob', 'jobseeker_or_permitted_visitor');

        self::override_policy($policies, 'quickapply.addtoquickapply', 'employer_or_administrator');
        self::override_policy($policies, 'quickapply.quickapplyonjob', 'jobseeker');

        self::override_policy($policies, 'resume.saveresume', 'resume_submitter', 'resume_save');
        self::override_policy($policies, 'resume.removeresume', 'resume_submitter', 'resume_remove');
        self::override_policy($policies, 'resume.addviewresumedetail', 'employer_or_administrator');

        self::override_policy($policies, 'user.saveuser', 'portal_user_or_administrator', 'user_self');

        foreach (array(
            'premiumplugin.verifytransactionkey',
            'premiumplugin.downloadandinstalladdons',
            'premiumplugin.wpjobportalupdatetransactionkey',
            'premiumplugin.wpjobportal_remove_unused_keys',
        ) as $route) {
            self::override_policy($policies, $route, 'plugin_installer');
        }

        /**
         * Allows narrowly scoped extensions to adjust a core policy without
         * replacing the dispatcher. Removing a policy is not supported.
         */
        $filtered = apply_filters('wpjobportal_core_action_policies', $policies);
        return is_array($filtered) ? $filtered : $policies;
    }

    /**
     * Return privacy-safe policy diagnostics.
     *
     * @return array
     */
    public static function get_status() {
        $policies = self::policies();
        $stats = get_option(self::STATS_OPTION, array());
        if (!is_array($stats)) {
            $stats = array();
        }

        $ownership_routes = 0;
        $schema_routes = 0;
        $post_only_routes = 0;
        foreach ($policies as $policy) {
            if (!empty($policy['ownership'])) {
                $ownership_routes++;
            }
            if (!empty($policy['schema'])) {
                $schema_routes++;
            }
            if ($policy['methods'] === array('POST')) {
                $post_only_routes++;
            }
        }

        return array(
            'protected_routes' => count($policies),
            'ownership_routes' => $ownership_routes,
            'schema_routes' => $schema_routes,
            'post_only_routes' => $post_only_routes,
            'denied' => isset($stats['denied']) ? absint($stats['denied']) : 0,
            'last_denied_at' => isset($stats['last_denied_at'])
                ? sanitize_text_field($stats['last_denied_at'])
                : '',
            'last_module' => isset($stats['last_module'])
                ? sanitize_key($stats['last_module'])
                : '',
            'last_task' => isset($stats['last_task'])
                ? sanitize_key($stats['last_task'])
                : '',
            'last_reason' => isset($stats['last_reason'])
                ? sanitize_key($stats['last_reason'])
                : '',
        );
    }

    /**
     * @param array  $policies
     * @param string $route
     * @param string $role
     * @param string $ownership
     * @return void
     */
    private static function override_policy(&$policies, $route, $role, $ownership = '') {
        if (!isset($policies[$route])) {
            return;
        }
        $policies[$route]['role'] = $role;
        $policies[$route]['ownership'] = $ownership;
    }

    /**
     * @param string $module
     * @param string $task
     * @return array
     */
    private static function methods_for($module, $task) {
        $post_only = array(
            'common.savenewinwpjobportal',
            'company.savecompany',
            'configuration.saveconfiguration',
            'configuration.saveautoupdateconfiguration',
            'customfield.saveuserfield',
            'emailtemplate.saveemailtemplate',
            'fieldordering.saveuserfield',
            'fieldordering.savesearchfieldordering',
            'fieldordering.savesearchfieldorderingfromform',
            'job.savejob',
            'jobapply.applyonjob',
            'postinstallation.save',
            'postinstallation.savesampledata',
            'postinstallation.getdemocode',
            'postinstallation.dismissonboarding',
            'postinstallation.reopenonboarding',
            'postinstallation.cleansampledata',
            'premiumplugin.verifytransactionkey',
            'premiumplugin.downloadandinstalladdons',
            'premiumplugin.wpjobportalupdatetransactionkey',
            'premiumplugin.wpjobportal_remove_unused_keys',
            'quickapply.quickapplyonjob',
            'resume.saveresume',
            'user.saveuserrole',
            'user.saveuser',
            'user.assignuserrole',
            'wpjobportal.saveordering',
            'wpjobportal.savedocumenttitleoptions',
            'wpjobportal.savescreenoptions',
            'zywrap.save_zywrap_settings',
        );
        $route = $module . '.' . $task;

        if (in_array($route, $post_only, true)
            || strpos($task, 'save') === 0
        ) {
            // Some historical "save" actions are links rather than forms.
            if (in_array($route, array(
                'configuration.saveautoupdateconfiguration',
                'postinstallation.savetemplatesampledata',
            ), true)) {
                return array('GET');
            }
            return array('POST');
        }

        if ($route === 'jobapply.jobapplyasvisitor'
            || $route === 'quickapply.addtoquickapply'
            || $route === 'common.makedefault'
            || $route === 'common.wpjobportal_synchronize_ai_search_data'
            || $route === 'thirdpartyimport.importjobmanagerdata'
        ) {
            return array('GET');
        }

        // Legacy single actions use GET links; multi-operation controls submit
        // the same route by POST. Both remain intentional and nonce protected.
        return array('GET', 'POST');
    }

    /**
     * @param string $module
     * @param string $task
     * @return string
     */
    private static function nonce_action_for($module, $task) {
        $module_nonces = array(
            'careerlevel' => 'wpjobportal_careerlevel_nonce',
            'category' => 'wpjobportal_category_nonce',
            'company' => 'wpjobportal_company_nonce',
            'configuration' => 'wpjobportal_configuration_nonce',
            'country' => 'wpjobportal_country_nonce',
            'currency' => 'wpjobportal_currency_nonce',
            'customfield' => 'wpjobportal_field_nonce',
            'emailtemplate' => 'wpjobportal_email_nonce',
            'emailtemplatestatus' => 'wpjobportal_emailstatus_nonce',
            'fieldordering' => 'wpjobportal_field_nonce',
            'highesteducation' => 'wpjobportal_highest_education_nonce',
            'job' => 'wpjobportal_job_nonce',
            'jobapply' => 'wpjobportal_job_apply_nonce',
            'jobstatus' => 'wpjobportal_job_status_nonce',
            'jobtype' => 'wpjobportal_job_type_nonce',
            'postinstallation' => 'wpjobportal_postinstallation_nonce',
            'premiumplugin' => 'wpjobportal_premiumplugin_nonce',
            'resume' => 'wpjobportal_resume_nonce',
            'salaryrangetype' => 'wpjobportal_salary_range_type_nonce',
            'slug' => 'wpjobportal_slug_nonce',
            'state' => 'wpjobportal_state_nonce',
            'theme' => 'wpjobportal_theme_nonce',
            'thirdpartyimport' => 'wpjobportal_job_manager_import_nonce',
            'user' => 'wpjobportal_user_nonce',
        );
        $route_nonces = array(
            'city.loadaddressdata' => 'wpjobportal_address_data_nonce',
            'city.savecitynamesettings' => 'wpjobportal_address_data_nonce',
            'common.makedefault' => 'wpjobportal_common_entity_nonce',
            'common.savenewinwpjobportal' => 'wpjobportal_new_in_jobportal_nonce',
            'common.wpjobportal_synchronize_ai_search_data' => 'synchronize_ai_search_data',
            'premiumplugin.wpjobportal_remove_unused_keys' => 'delete-transaction-key',
            'quickapply.addtoquickapply' => 'copy-job',
            'quickapply.quickapplyonjob' => 'wpjobportal_quick_apply_nonce',
            'wpjobportal.saveordering' => 'wpjobportal_field_nonce',
            'wpjobportal.savedocumenttitleoptions' => 'wpjobportal_document_title_nonce',
            'wpjobportal.savescreenoptions' => 'wjp_dashboard_options_nonce',
            'zywrap.save_zywrap_settings' => 'save-zywrap-settings',
        );
        $route = $module . '.' . $task;

        if (isset($route_nonces[$route])) {
            return $route_nonces[$route];
        }
        if ($module === 'city') {
            return 'wpjobportal_city_nonce';
        }
        return isset($module_nonces[$module])
            ? $module_nonces[$module]
            : 'wpjobportal_nonce';
    }

    /**
     * @param string $module
     * @param string $task
     * @return string
     */
    private static function nonce_field_for($module, $task) {
        if ($module === 'wpjobportal' && $task === 'savescreenoptions') {
            return 'wjp_dashboard_nonce';
        }
        return '_wpnonce';
    }

    /**
     * Return the security-relevant input schema for one mutation.
     *
     * Complex form bodies continue to be sanitized and validated by their
     * models. The dispatch boundary validates identifiers, enums and secrets
     * that affect authorization or select the mutation target.
     *
     * @param string $module
     * @param string $task
     * @return array
     */
    private static function schema_for($module, $task) {
        $route = $module . '.' . $task;
        $schema = array();

        if (strpos($task, 'save') === 0) {
            $schema['id'] = array('type' => 'optional_id');
        }

        if (in_array($task, array(
            'remove',
            'removecity',
            'publish',
            'unpublish',
            'fieldrequired',
            'fieldnotrequired',
            'fieldpublished',
            'fieldunpublished',
            'visitorfieldpublished',
            'visitorfieldunpublished',
        ), true)) {
            $schema['wpjobportal-cb'] = array('type' => 'id_list', 'required' => true);
        }

        if (strpos($task, 'saveordering') !== false) {
            $schema['fields_ordering_new'] = array('type' => 'optional_text', 'max' => 20000);
        }

        $overrides = array(
            'common.makedefault' => array(
                'id' => array('type' => 'id', 'required' => true),
                'for' => array(
                    'type' => 'enum',
                    'required' => true,
                    'values' => array(
                        'jobstatus',
                        'jobtypes',
                        'careerlevels',
                        'salaryrangetypes',
                        'currencies',
                        'heighesteducation',
                        'categories',
                    ),
                ),
            ),
            'common.savenewinwpjobportal' => array(
                'roleid' => array('type' => 'enum', 'required' => true, 'values' => array('1', '2')),
            ),
            'company.approvequeuecompany' => self::required_id('id'),
            'company.approvequeuefeaturedcompany' => self::required_id('id'),
            'company.rejectqueuecompany' => self::required_id('id'),
            'company.rejectqueuefeaturecompany' => self::required_id('id'),
            'company.approvequeueallcompanies' => self::required_id_list('id'),
            'company.rejectqueueallcompanies' => self::required_id_list('id'),
            'company.savecompany' => array('id' => array('type' => 'optional_id')),
            'company.enforcedelete' => self::required_id('id'),
            'company.addviewcontactdetail' => array(
                '__any_id' => array(
                    'type' => 'any_id',
                    'required' => true,
                    'fields' => array('companyid', 'wpjobportalid'),
                ),
            ),
            'customfield.remove' => array(
                '__any_id' => array(
                    'type' => 'any_id',
                    'required' => true,
                    'fields' => array('fieldid', 'wpjobportal-cb'),
                ),
            ),
            'fieldordering.remove' => array(
                '__any_id' => array(
                    'type' => 'any_id',
                    'required' => true,
                    'fields' => array('fieldid', 'wpjobportal-cb'),
                ),
            ),
            'emailtemplatestatus.sendemail' => array(
                'wpjobportalid' => array('type' => 'id', 'required' => true),
                'actionfor' => array(
                    'type' => 'enum',
                    'required' => true,
                    'values' => array('1', '2', '3', '4', '5'),
                ),
            ),
            'emailtemplatestatus.nosendemail' => array(
                'wpjobportalid' => array('type' => 'id', 'required' => true),
                'actionfor' => array(
                    'type' => 'enum',
                    'required' => true,
                    'values' => array('1', '2', '3', '4', '5'),
                ),
            ),
            'job.approvequeuejob' => self::required_id('id'),
            'job.rejectqueuejob' => self::required_id('id'),
            'job.approvequeuefeaturedjob' => self::required_id('id'),
            'job.rejectqueuefeaturedjob' => self::required_id('id'),
            // The company field is configurable, so it can be unpublished or
            // left unselected. The value is still constrained when supplied.
            'job.savejob' => array(
                'id' => array('type' => 'optional_id'),
                'companyid' => array('type' => 'optional_id'),
            ),
            'job.jobenforcedelete' => self::required_id('jobid'),
            'jobapply.jobapplyasvisitor' => self::required_id('wpjobportalid-jobid'),
            'jobapply.applyonjob' => self::required_id('jobid'),
            'postinstallation.save' => array(
                // Step 0 is the quick-start screen that stores the multiple
                // employers mode before the numbered setup steps begin.
                'step' => array('type' => 'enum', 'required' => true, 'values' => array('0', '1', '2', '3')),
            ),
            'postinstallation.savesampledata' => array(
                'sampledata' => array('type' => 'enum', 'required' => true, 'values' => array('0', '1')),
                'jsmenu' => array('type' => 'enum', 'values' => array('0', '1')),
                'empmenu' => array('type' => 'enum', 'values' => array('0', '1')),
                'job_listing_menu' => array('type' => 'enum', 'values' => array('0', '1')),
                'temp_data' => array('type' => 'enum', 'values' => array('0', '1')),
            ),
            'postinstallation.savetemplatesampledata' => array(
                'flag' => array('type' => 'enum', 'required' => true, 'values' => array('f', 'p', 'ftp')),
            ),
            'postinstallation.importtemplatesampledata' => array(
                'flag' => array('type' => 'enum', 'required' => true, 'values' => array('f')),
            ),
            'postinstallation.getdemocode' => array(
                'demoid' => array('type' => 'safe_key', 'required' => true, 'max' => 100),
            ),
            'postinstallation.cleansampledata' => array(
                'sample_cleanup_confirmation' => array(
                    'type' => 'enum',
                    'required' => true,
                    'values' => array('cleanup_marked_sample_data'),
                ),
            ),
            // The activation-key routes validate an empty submission themselves
            // and return the user to the form with a readable message, so the
            // boundary only constrains the value when one is supplied.
            'premiumplugin.verifytransactionkey' => array(
                'transactionkey' => array('type' => 'optional_text', 'max' => 512),
            ),
            'premiumplugin.downloadandinstalladdons' => array(
                'token' => array('type' => 'optional_text', 'max' => 512),
            ),
            'premiumplugin.wpjobportalupdatetransactionkey' => array(
                'transactionkey' => array('type' => 'optional_text', 'max' => 512),
            ),
            'quickapply.addtoquickapply' => self::required_id('wpjobportalid'),
            'quickapply.quickapplyonjob' => self::required_id('jobid'),
            'resume.approvequeueresume' => self::required_id('id'),
            'resume.approvequeuefeatureresume' => self::required_id('id'),
            'resume.rejectqueueresume' => self::required_id('id'),
            'resume.rejectqueuefeatureresume' => self::required_id('id'),
            'resume.approvequeueallresumes' => self::required_id_list('id'),
            'resume.rejectqueueallresumes' => self::required_id_list('id'),
            'resume.resumeenforcedelete' => self::required_id('resumeid'),
            'resume.saveresume' => array(
                'id'    => array('type' => 'optional_id'),
                'sec_1' => array('type' => 'section_ids'),
                'sec_2' => array('type' => 'section_ids'),
                'sec_3' => array('type' => 'section_ids'),
                'sec_4' => array('type' => 'section_ids'),
                'sec_5' => array('type' => 'section_ids'),
                'sec_6' => array('type' => 'section_ids'),
                'sec_7' => array('type' => 'section_ids'),
                'sec_8' => array('type' => 'section_ids'),
            ),
            'resume.removeresume' => self::required_id_list('wpjobportal-cb'),
            'resume.addviewresumedetail' => self::required_id('wpjobportalid'),
            'thirdpartyimport.importjobmanagerdata' => array(
                'selected_plugin' => array('type' => 'enum', 'required' => true, 'values' => array('1')),
            ),
            'user.saveuserrole' => array(
                'id' => array('type' => 'id', 'required' => true),
                'roleid' => array('type' => 'enum', 'required' => true, 'values' => array('1', '2')),
            ),
            'user.saveuser' => array('id' => array('type' => 'optional_id')),
            'user.assignuserrole' => array(
                'uid' => array('type' => 'id', 'required' => true),
                'roleid' => array('type' => 'enum', 'required' => true, 'values' => array('1', '2')),
            ),
            'user.changeuserstatus' => self::required_id('wpjobportalid'),
            'user.deleteuser' => self::required_id('wpjobportalid'),
            'user.enforcedeleteuser' => self::required_id('wpjobportalid'),
        );

        if (isset($overrides[$route])) {
            return $overrides[$route];
        }

        return $schema;
    }

    /**
     * @param string $field
     * @return array
     */
    private static function required_id($field) {
        return array($field => array('type' => 'id', 'required' => true));
    }

    /**
     * @param string $field
     * @return array
     */
    private static function required_id_list($field) {
        return array($field => array('type' => 'id_list', 'required' => true));
    }

    /**
     * @param string $role
     * @return bool
     */
    private static function role_is_allowed($role) {
        if ($role === 'public') {
            return true;
        }
        if ($role === 'administrator') {
            return current_user_can('manage_options');
        }
        if ($role === 'plugin_installer') {
            return current_user_can('install_plugins');
        }

        $user = WPJOBPORTALincluder::getObjectClass('user');
        if (!is_object($user) || (method_exists($user, 'isdisabled') && $user->isdisabled())) {
            return false;
        }

        if ($role === 'employer_or_administrator') {
            return current_user_can('manage_options') || $user->isemployer();
        }
        if ($role === 'jobseeker_or_administrator') {
            return current_user_can('manage_options') || $user->isjobseeker();
        }
        if ($role === 'jobseeker') {
            return $user->isjobseeker();
        }
        if ($role === 'jobseeker_or_permitted_visitor') {
            if ($user->isjobseeker()) {
                return true;
            }
            // Job apply also runs for guests when an administrator enabled the
            // visitor quick apply form or visitor job applications.
            return $user->isguest()
                && (absint(wpjobportal::$_config->getConfigValue('quick_apply_for_visitor')) === 1
                    || absint(wpjobportal::$_config->getConfigValue('visitor_can_apply_to_job')) === 1);
        }
        if ($role === 'portal_user_or_administrator') {
            return current_user_can('manage_options') || $user->isemployer() || $user->isjobseeker();
        }
        if ($role === 'wordpress_user_without_portal_role') {
            return is_user_logged_in()
                && !current_user_can('manage_options')
                && !$user->isemployer()
                && !$user->isjobseeker();
        }
        if ($role === 'resume_submitter') {
            if (current_user_can('manage_options') || $user->isjobseeker()) {
                return true;
            }
            return $user->isguest()
                && in_array('visitorapplyjob', (array) wpjobportal::$_active_addons, true)
                && absint(wpjobportal::$_config->getConfigValue('visitor_can_add_resume')) === 1;
        }

        return false;
    }

    /**
     * @param array $schema
     * @return bool
     */
    private static function schema_is_valid($schema) {
        foreach ((array) $schema as $field => $rule) {
            if (!is_array($rule) || empty($rule['type'])) {
                return false;
            }

            if ($rule['type'] === 'any_id') {
                $valid = false;
                foreach ((array) $rule['fields'] as $candidate) {
                    $value = self::request_value($candidate);
                    if (self::valid_id_or_list($value)) {
                        $valid = true;
                        break;
                    }
                }
                if (!$valid) {
                    return false;
                }
                continue;
            }

            $value = self::request_value($field);
            $required = !empty($rule['required']);
            if (self::is_empty_value($value)) {
                if ($required) {
                    return false;
                }
                continue;
            }

            switch ($rule['type']) {
                case 'id':
                    if (!self::valid_id($value)) {
                        return false;
                    }
                    break;
                case 'optional_id':
                    if (!self::valid_id($value)) {
                        return false;
                    }
                    break;
                case 'id_list':
                    if (!self::valid_id_or_list($value)) {
                        return false;
                    }
                    break;
                case 'section_ids':
                    if (!self::valid_section_ids($value)) {
                        return false;
                    }
                    break;
                case 'enum':
                    if (is_array($value)
                        || !in_array((string) $value, (array) $rule['values'], true)
                    ) {
                        return false;
                    }
                    break;
                case 'text':
                case 'optional_text':
                    if (is_array($value)) {
                        return false;
                    }
                    $max = isset($rule['max']) ? absint($rule['max']) : 255;
                    if ($max < 1 || strlen((string) $value) > $max) {
                        return false;
                    }
                    break;
                case 'safe_key':
                    if (is_array($value)) {
                        return false;
                    }
                    $max = isset($rule['max']) ? absint($rule['max']) : 100;
                    if ($max < 1
                        || strlen((string) $value) > $max
                        || !preg_match('/^[A-Za-z0-9_-]+$/', (string) $value)
                    ) {
                        return false;
                    }
                    break;
                default:
                    return false;
            }
        }

        return true;
    }

    /**
     * @param string $resolver
     * @return bool
     */
    private static function ownership_is_allowed($resolver) {
        if (current_user_can('manage_options')) {
            return true;
        }

        if ($resolver === 'company_save') {
            $id = self::request_value('id');
            return self::is_empty_value($id)
                || self::owns_records('wj_portal_companies', array($id));
        }
        if ($resolver === 'company_remove') {
            return self::owns_records(
                'wj_portal_companies',
                self::normalize_ids(self::request_value('wpjobportal-cb'))
            );
        }
        if ($resolver === 'job_save') {
            $id = self::request_value('id');
            $company_id = self::request_value('companyid');
            return (self::is_empty_value($id)
                    || self::owns_records('wj_portal_jobs', array($id)))
                && (self::is_empty_value($company_id)
                    || self::owns_records('wj_portal_companies', array($company_id)));
        }
        if ($resolver === 'job_remove') {
            return self::owns_records(
                'wj_portal_jobs',
                self::normalize_ids(self::request_value('wpjobportal-cb'))
            );
        }
        if ($resolver === 'resume_save') {
            $id = self::request_value('id');
            return self::is_empty_value($id) || self::owns_resume(array($id));
        }
        if ($resolver === 'resume_remove') {
            return self::owns_resume(
                self::normalize_ids(self::request_value('wpjobportal-cb'))
            );
        }
        if ($resolver === 'user_self') {
            $id = self::request_value('id');
            return self::is_empty_value($id)
                || absint($id) === self::current_internal_user_id();
        }

        return false;
    }

    /**
     * @param string $table_suffix
     * @param array  $ids
     * @return bool
     */
    private static function owns_records($table_suffix, $ids) {
        $ids = self::normalize_ids($ids);
        $user_id = self::current_internal_user_id();
        if (empty($ids) || $user_id < 1) {
            return false;
        }

        foreach ($ids as $id) {
            $query = wpjobportal::$_db->prepare(
                'SELECT uid FROM `' . wpjobportal::$_db->prefix . $table_suffix . '` WHERE id = %d',
                $id
            );
            if (absint(wpjobportaldb::get_var($query)) !== $user_id) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param array $ids
     * @return bool
     */
    private static function owns_resume($ids) {
        $ids = self::normalize_ids($ids);
        if (empty($ids)) {
            return false;
        }

        $user = WPJOBPORTALincluder::getObjectClass('user');
        if (is_object($user) && $user->isguest()) {
            if (count($ids) !== 1
                || !isset($_SESSION['wp-wpjobportal']['resumeid'])
            ) {
                return false;
            }
            return absint($_SESSION['wp-wpjobportal']['resumeid']) === $ids[0];
        }

        return self::owns_records('wj_portal_resume', $ids);
    }

    /**
     * @return int
     */
    private static function current_internal_user_id() {
        $user = WPJOBPORTALincluder::getObjectClass('user');
        return is_object($user) ? absint($user->uid()) : 0;
    }

    /**
     * @param string $field
     * @return mixed
     */
    private static function request_value($field) {
        if (isset($_POST[$field])) {
            return wp_unslash($_POST[$field]);
        }
        if (isset($_GET[$field])) {
            return wp_unslash($_GET[$field]);
        }
        return null;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    private static function valid_id($value) {
        return !is_array($value)
            && is_numeric($value)
            && (string) absint($value) === (string) $value
            && absint($value) > 0;
    }

    /**
     * A resume section is posted as sec_N[id][i], sec_N[deletethis][i] and so on. Each posted
     * section id addresses a stored row, so it has to be either empty (a new row) or a clean
     * positive integer. Anything else is rejected here rather than left for MySQL to coerce.
     *
     * @param mixed $value
     * @return bool
     */
    private static function valid_section_ids($value) {
        if (!is_array($value) || !isset($value['id'])) {
            return true; // this section addresses no stored row
        }

        $ids = is_array($value['id']) ? $value['id'] : array($value['id']);
        if (count($ids) > 1000) {
            return false;
        }

        foreach ($ids as $candidate) {
            if (self::is_empty_value($candidate)
                || $candidate === 0
                || $candidate === '0'
            ) {
                continue; // a new row carries no id
            }
            if (!self::valid_id($candidate)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    private static function valid_id_or_list($value) {
        $ids = self::normalize_ids($value);
        return !empty($ids) && count($ids) <= 1000;
    }

    /**
     * @param mixed $value
     * @return array
     */
    private static function normalize_ids($value) {
        if (is_array($value)) {
            $values = $value;
        } elseif (is_string($value) && strpos($value, ',') !== false) {
            $values = explode(',', $value);
        } else {
            $values = array($value);
        }

        $ids = array();
        foreach ($values as $candidate) {
            if (!self::valid_id($candidate)) {
                return array();
            }
            $ids[] = absint($candidate);
        }
        return array_values(array_unique($ids));
    }

    /**
     * @param mixed $value
     * @return bool
     */
    private static function is_empty_value($value) {
        return $value === null || $value === '';
    }

    /**
     * Record a denial without request data, record IDs, nonces or form values.
     *
     * @param string $module
     * @param string $task
     * @param string $channel
     * @param string $reason
     * @return false
     */
    private static function deny($module, $task, $channel, $reason) {
        $old = get_option(self::STATS_OPTION, array());
        if (!is_array($old)) {
            $old = array();
        }
        $stats = array(
            'denied' => isset($old['denied']) ? absint($old['denied']) + 1 : 1,
            'last_denied_at' => current_time('mysql', true),
            'last_module' => sanitize_key($module),
            'last_task' => sanitize_key($task),
            'last_reason' => sanitize_key($reason),
        );

        if (get_option(self::STATS_OPTION, null) === null) {
            add_option(self::STATS_OPTION, $stats, '', 'no');
        } else {
            update_option(self::STATS_OPTION, $stats, false);
        }

        if (class_exists('WPJOBPORTAL_Audit_Service')) {
            WPJOBPORTAL_Audit_Service::record(
                'core_mutation_denied',
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
