<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALdeactivation {

    static function wpjobportal_deactivate() {
        wp_clear_scheduled_hook('wpjobportal_cronjobs_action');
        wp_clear_scheduled_hook('wpjobportal_job_expiry_reminders');
        wp_clear_scheduled_hook('wpjobportal_table_upgrade');
        if (class_exists('WPJOBPORTAL_Resume_File_Store')) {
            wp_clear_scheduled_hook(WPJOBPORTAL_Resume_File_Store::CRON_HOOK);
            // On reactivation the first request protects folders and restarts any unfinished renaming
            delete_option(WPJOBPORTAL_Resume_File_Store::VERSION_OPTION);
        }
        if (class_exists('WPJOBPORTAL_Licence_Client')) {
            wp_clear_scheduled_hook(WPJOBPORTAL_Licence_Client::CRON_HOOK);
        }
        if (class_exists('WPJOBPORTAL_Translations')) {
            WPJOBPORTAL_Translations::deactivate();
        }
        if (class_exists('WPJOBPORTAL_Storage_Engine_Service')) {
            WPJOBPORTAL_Storage_Engine_Service::deactivate();
        } else {
            wp_clear_scheduled_hook('wpjobportal_storage_engine_conversion');
            delete_option('wpjobportal_storage_engine_conversion_lock');
        }
        if (class_exists('WPJOBPORTAL_Notification_Queue_Service')) {
            WPJOBPORTAL_Notification_Queue_Service::deactivate();
        } else {
            wp_clear_scheduled_hook('wpjobportal_notification_queue_process');
        }
        if (class_exists('WPJOBPORTAL_Privacy_Service')) {
            wp_clear_scheduled_hook(WPJOBPORTAL_Privacy_Service::CRON_HOOK);
        } else {
            wp_clear_scheduled_hook('wpjobportal_privacy_retention_cleanup');
        }
        $wpjobportal_id = wpjobportal::wpjobportal_getPageid();
        if(is_numeric($wpjobportal_id)){
            // Remembered, so activation publishes this page again whatever it holds
            // (the owner may have made the Jobs page the default, not Jobseeker).
            if (get_post_status((int) $wpjobportal_id) === 'publish') {
                update_option('wpjobportal_deactivation_drafted_page', (int) $wpjobportal_id, false);
            }
            wpjobportal::$_db->get_var("UPDATE `" . wpjobportal::$_db->prefix . "posts` SET post_status = 'draft' WHERE ID =" . (int) ($wpjobportal_id));
            clean_post_cache((int) $wpjobportal_id);
        }
        // Audit 23: the other plugin pages (Jobseeker, Register, Candidate Portal, Recruit
        // pages...) stayed published and showed visitors raw shortcodes while the plugin was
        // off. Every published page that holds nothing but plugin shortcodes goes to draft
        // too; pages with the owner's own content are left alone. Activation publishes
        // exactly these again.
        $wpjobportal_drafted_pages = array();
        $wpjobportal_plugin_pages = wpjobportal::$_db->get_results("SELECT ID, post_content FROM `" . wpjobportal::$_db->prefix . "posts` WHERE post_type = 'page' AND post_status = 'publish' AND post_content LIKE '%[wpjobportal%'");
        foreach ((array) $wpjobportal_plugin_pages as $wpjobportal_plugin_page) {
            if (self::wpjobportal_only_plugin_shortcodes($wpjobportal_plugin_page->post_content)) {
                wpjobportal::$_db->query("UPDATE `" . wpjobportal::$_db->prefix . "posts` SET post_status = 'draft' WHERE ID = " . (int) $wpjobportal_plugin_page->ID);
                clean_post_cache((int) $wpjobportal_plugin_page->ID);
                $wpjobportal_drafted_pages[] = (int) $wpjobportal_plugin_page->ID;
            }
        }
        if (!empty($wpjobportal_drafted_pages)) {
            update_option('wpjobportal_deactivation_drafted_pages', $wpjobportal_drafted_pages, false);
        }

        //Delete capabilities
        $wpjobportal_role = get_role( 'administrator' );
        $wpjobportal_role->remove_cap( 'wpjobportal' );
        $wpjobportal_role->remove_cap( 'wpjobportal_jobs' );

        // Every scheduled event of the plugin, not a list kept by hand: the job alert
        // run (wpjobportal_send_scheduled_job_alerts) was missing from it and kept
        // firing with the plugin switched off.
        self::wpjobportal_clear_cron();

        // Only working state is removed here: what a screen remembered (filters, the
        // country being browsed), scratch data of an import, cached statuses and
        // one-off notices. Settings stay. Deleting them made a deactivate and
        // reactivate reset the page-title formats, page size and location names,
        // forget the Zywrap API key and the demo pages it can remove, and turn a
        // single-employer (Careers) site back into a multi-employer one. They are
        // removed with everything else on uninstall, when the owner chose that.
        foreach (self::wpjobportal_working_state_options() as $option_name) {
            delete_option($option_name);
        }
    }

    /** Options that only hold working state; see wpjobportal_deactivate(). */
    /** Audit 23: whether a page's content is nothing but plugin shortcodes. */
    static function wpjobportal_only_plugin_shortcodes($wpjobportal_content) {
        $wpjobportal_rest = preg_replace('/<!--.*?-->/s', '', (string) $wpjobportal_content);
        $wpjobportal_rest = preg_replace('/\[\/?wpjobportal[^\]]*\]/i', '', $wpjobportal_rest);
        $wpjobportal_rest = trim(str_replace('&nbsp;', ' ', wp_strip_all_tags($wpjobportal_rest)));
        return $wpjobportal_rest === '' && stripos((string) $wpjobportal_content, '[wpjobportal') !== false;
    }

    static function wpjobportal_working_state_options() {
        return array('wp_job_portal_activity_log_filter', 'wpjobportal_countryid_for_city', 'wpjobportal_stateid_for_city',
            'wpjobportal_countryid_for_stateid', 'wpjobportal_jobs_sample_data', 'wpjobportal_addon_return_data',
            'wpjobportalformresumeuserfield_ff', 'job_portal_jm_data_users', 'job_portal_jm_data_companies', 'job_portal_jm_data_jobs',
            'job_portal_jm_data_resumes', 'wpjob_portal_import_counts', 'wpjobportal_show_key_expiry_msg', 'wpjobportal_zywrap_api_status',
            'wpjobportal_zywrap_version_time');
    }

    /** Clear every scheduled event whose hook belongs to the plugin. */
    static function wpjobportal_clear_cron() {
        $wpjobportal_crons = function_exists('_get_cron_array') ? _get_cron_array() : array();
        $wpjobportal_hooks = array();
        foreach ((array) $wpjobportal_crons as $wpjobportal_events) {
            foreach ((array) $wpjobportal_events as $wpjobportal_hook => $wpjobportal_unused) {
                if (strpos((string) $wpjobportal_hook, 'wpjobportal_') === 0) {
                    $wpjobportal_hooks[$wpjobportal_hook] = true;
                }
            }
        }
        foreach (array_keys($wpjobportal_hooks) as $wpjobportal_hook) {
            wp_unschedule_hook($wpjobportal_hook);
        }
    }

     static function wpjobportal_tables_to_drop() {
        global $wpdb;
        $wpjobportal_tables = array(
            $wpdb->prefix."wj_portal_careerlevels",
            $wpdb->prefix."wj_portal_categories",
            $wpdb->prefix."wj_portal_cities",
            $wpdb->prefix."wj_portal_companies",
            $wpdb->prefix."wj_portal_companycities",
            $wpdb->prefix."wj_portal_config",
            $wpdb->prefix."wj_portal_countries",
            $wpdb->prefix."wj_portal_currencies",
            $wpdb->prefix."wj_portal_emailtemplates",
            $wpdb->prefix."wj_portal_fieldsordering",
            $wpdb->prefix."wj_portal_heighesteducation",
            $wpdb->prefix."wj_portal_jobapply",
            $wpdb->prefix."wj_portal_jobcities",
            $wpdb->prefix."wj_portal_jobs",
            $wpdb->prefix."wj_portal_jobstatus",
            $wpdb->prefix."wj_portal_jobs_temp",
            $wpdb->prefix."wj_portal_jobs_temp_time",
            $wpdb->prefix."wj_portal_jobtypes",
            $wpdb->prefix."wj_portal_resume",
            $wpdb->prefix."wj_portal_resumeaddresses",
            $wpdb->prefix."wj_portal_resumeemployers",
            $wpdb->prefix."wj_portal_resumefiles",
            $wpdb->prefix."wj_portal_resumeinstitutes",
            $wpdb->prefix."wj_portal_resumelanguages",
            $wpdb->prefix."wj_portal_salaryrangetypes",
            $wpdb->prefix."wj_portal_states",
            $wpdb->prefix."wj_portal_subcategories",
            $wpdb->prefix."wj_portal_activitylog",
            $wpdb->prefix."wj_portal_emailtemplates_config",
            $wpdb->prefix."wj_portal_employer_view_resume",
            $wpdb->prefix."wj_portal_jobseeker_view_company",
            $wpdb->prefix."wj_portal_system_errors",
            $wpdb->prefix."wj_portal_migrations",
            $wpdb->prefix."wj_portal_notification_queue",
            $wpdb->prefix."wj_portal_users",
            $wpdb->prefix."wj_portal_slug",
            $wpdb->prefix."wj_portal_jswjsessiondata",

            // Absorbed into Core for 2.7.0. Activation creates these, so
            // uninstall has to remove them: without this an uninstall leaves
            // them behind and the next install's IF NOT EXISTS silently
            // reattaches the old shortlists, saved searches and alerts.
            $wpdb->prefix."wj_portal_tags",
            $wpdb->prefix."wj_portal_jobshortlist",
            $wpdb->prefix."wj_portal_jobsearches",
            $wpdb->prefix."wj_portal_jobalertsetting",
            $wpdb->prefix."wj_portal_jobalertcities",

            //zywrap classes
            $wpdb->prefix."wj_portal_zywrap_wrappers",
            $wpdb->prefix."wj_portal_zywrap_categories",
            $wpdb->prefix."wj_portal_zywrap_languages",
            $wpdb->prefix."wj_portal_zywrap_ai_models",
            $wpdb->prefix."wj_portal_zywrap_block_templates",
            $wpdb->prefix."wj_portal_zywrap_logs",
            $wpdb->prefix."wj_portal_zywrap_use_cases",
        );
        return $wpjobportal_tables;
    }

    /**
     * Tables Core's own list does not hold but nothing else will remove.
     *
     * While products are deleted one by one, the uninstall guard keeps every
     * table another installed plugin still reads - Core reads most of them -
     * and the Marketplace Suite keeps its business data by design. So once the
     * last product and then Core itself are deleted, those tables were left
     * behind, and a fresh install's CREATE TABLE IF NOT EXISTS picked the old
     * packages, invoices, messages and departments up again.
     *
     * Core is the last to go, so it removes them - but only when no product
     * is installed any more. A product still in its folder (active or not)
     * still owns that data.
     *
     * @return array Full table names.
     */
    static function wpjobportal_shared_tables_to_drop() {
        global $wpdb;
        foreach (array('wp-job-portal-pro', 'wp-job-portal-marketplacesuite', 'wp-job-portal-recruitsuite', 'wp-job-portal-matchingsuite') as $wpjobportal_product) {
            if (file_exists(WP_PLUGIN_DIR . '/' . $wpjobportal_product . '/' . $wpjobportal_product . '.php')) {
                return array();
            }
        }

        include_once dirname(__FILE__) . '/services/class-wpjobportal-uninstall-guard.php';
        $wpjobportal_names = array_keys(WPJOBPORTAL_Uninstall_Guard::readers());
        // The Marketplace Suite's own tables; its uninstall keeps them.
        $wpjobportal_names = array_merge($wpjobportal_names, array(
            'marketplace_verifications',
            'marketplace_risk_events',
            'marketplace_feature_schedules',
        ));

        $wpjobportal_tables = array();
        foreach (array_unique($wpjobportal_names) as $wpjobportal_name) {
            $wpjobportal_tables[] = $wpdb->prefix . 'wj_portal_' . sanitize_key($wpjobportal_name);
        }
        return $wpjobportal_tables;
    }

}

?>
