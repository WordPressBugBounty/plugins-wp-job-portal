<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALdeactivation {

    static function wpjobportal_deactivate() {
        wp_clear_scheduled_hook('wpjobportal_cronjobs_action');
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
            wpjobportal::$_db->get_var("UPDATE `" . wpjobportal::$_db->prefix . "posts` SET post_status = 'draft' WHERE ID =" . (int) ($wpjobportal_id));
        }

        //Delete capabilities
        $wpjobportal_role = get_role( 'administrator' );
        $wpjobportal_role->remove_cap( 'wpjobportal' );
        $wpjobportal_role->remove_cap( 'wpjobportal_jobs' );

        //Delete Options
        $wpjobportal_options_array = ['wpjobportal_company_document_title_settings' ,'wpjobportal_job_document_title_settings' ,'wpjobportal_resume_document_title_settings' ,'wp_job_portal_activity_log_filter' ,'wpjobportal_page_size' ,'wpjobportal_countryid_for_city' ,'wpjobportal_stateid_for_city' ,'wpjobportal_location_name_preference' ,'wpjobportal_jobs_sample_data' ,'wpjobportal_post_installation' ,'wpjobportal_multiple_employers' ,'job_portal_demno_id' ,'job_portal_theme_demo_pages_ids' ,'job_portal_theme_demo_post_ids' ,'job_portal_theme_demo_demo_specific_data' ,'wpjobportal_addon_return_data' ,'wpjobportalformresumeuserfield_ff' ,'wpjobportal_countryid_for_stateid' ,'job_portal_jm_data_users' ,'job_portal_jm_data_companies' ,'job_portal_jm_data_jobs' ,'job_portal_jm_data_resumes' ,'wpjob_portal_import_counts' ,'wpjobportal_show_key_expiry_msg' ,'wpjobportal_zywrap_api_status' ,'wpjobportal_zywrap_api_key' ,'wpjobportal_zywrap_version' ,'wpjobportal_zywrap_version_time'];
        foreach ($wpjobportal_options_array as $option_name) {
            delete_option($option_name);
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
