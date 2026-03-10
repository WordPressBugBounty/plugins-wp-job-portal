<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALdeactivation {

    static function wpjobportal_deactivate() {
        wp_clear_scheduled_hook('wpjobportal_cronjobs_action');
        $wpjobportal_id = wpjobportal::wpjobportal_getPageid();
        if(is_numeric($wpjobportal_id)){
            wpjobportal::$_db->get_var("UPDATE `" . wpjobportal::$_db->prefix . "posts` SET post_status = 'draft' WHERE ID =".esc_sql($wpjobportal_id));
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
            $wpdb->prefix."wj_portal_users",
            $wpdb->prefix."wj_portal_slug",
            $wpdb->prefix."wj_portal_jswjsessiondata",

            //zywrap classes
            $wpdb->prefix."wj_portal_zywrap_wrappers",
            $wpdb->prefix."wj_portal_zywrap_categories",
            $wpdb->prefix."wj_portal_zywrap_languages",
            $wpdb->prefix."wj_portal_zywrap_ai_models",
            $wpdb->prefix."wj_portal_zywrap_block_templates",
            $wpdb->prefix."wj_portal_zywrap_logs",
        );
        return $wpjobportal_tables;
    }

}

?>
