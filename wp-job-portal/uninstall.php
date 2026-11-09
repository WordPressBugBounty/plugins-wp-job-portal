<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php

/**
 * WP Job Portal Uninstall
 *
 * Uninstalling WP Job Portal tables, and pages.
 *
 * @author      Ahmed Bilal
 * @category    Core
 * @package     WP Job Portal/Uninstaller
 * @version     1.0.0
 */
if (!defined('WP_UNINSTALL_PLUGIN'))
    exit();

global $wpdb;
require_once __DIR__ . '/includes/wpjobportaldb.php';
include_once 'includes/deactivation.php';

/**
 * W-15: the data stays unless the owner switched on "Delete all data when the
 * plugin is deleted" on this site. A missing table or setting means keep.
 */
function wpjobportal_uninstall_deletes_data() {
    global $wpdb;
    $wpjobportal_config = $wpdb->prefix . 'wj_portal_config';
    if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpjobportal_config)) !== $wpjobportal_config) {
        return false;
    }
    return (string) $wpdb->get_var("SELECT configvalue FROM `" . esc_sql($wpjobportal_config) . "` WHERE configname = 'delete_data_on_uninstall'") === '1';
}

/**
 * The rest of "Delete all data": the tables and the listed options went before,
 * but every other option, the plugin's pages, user preferences, scheduled
 * events and the uploaded files (CVs, logos) stayed, so a later install found
 * a half-removed plugin. Called only when the owner chose to delete the data.
 */
function wpjobportal_uninstall_remove_everything_else() {
    global $wpdb;
    // Read before the options go: the pages the plugin made and its data folder.
    // Pages come from three places: the provisioning service (Register, Jobs),
    // the setup wizard (recorded in the sample data registry) and activation
    // (the job seeker page saved as default_pageid).
    $wpjobportal_pages = array_values((array) get_option('wpjobportal_provisioned_pages'));
    $wpjobportal_registry = get_option('wpjobportal_sample_data_registry_v1');
    if (is_array($wpjobportal_registry) && !empty($wpjobportal_registry['batches'])) {
        foreach ((array) $wpjobportal_registry['batches'] as $wpjobportal_batch) {
            if (!empty($wpjobportal_batch['records']['pages'])) {
                $wpjobportal_pages = array_merge($wpjobportal_pages, array_keys((array) $wpjobportal_batch['records']['pages']));
            }
        }
    }
    $wpjobportal_datadir = '';
    $wpjobportal_config = $wpdb->prefix . 'wj_portal_config';
    if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpjobportal_config)) === $wpjobportal_config) {
        $wpjobportal_datadir = (string) $wpdb->get_var("SELECT configvalue FROM `" . esc_sql($wpjobportal_config) . "` WHERE configname = 'data_directory'");
        $wpjobportal_default_id = (int) $wpdb->get_var("SELECT configvalue FROM `" . esc_sql($wpjobportal_config) . "` WHERE configname = 'default_pageid'");
        $wpjobportal_default_page = $wpjobportal_default_id > 0 ? get_post($wpjobportal_default_id) : null;
        // Only the page activation made (its slug), never a page the owner chose.
        if ($wpjobportal_default_page && strpos($wpjobportal_default_page->post_name, 'wp-job-portal-jobseeker-controlpanel') === 0) {
            $wpjobportal_pages[] = $wpjobportal_default_page->ID;
        }
    }

    // Audit 26: pages left over from older versions (the 2.7 Jobseeker page) and the
    // sample-data Employer page were missed and stayed published with raw shortcodes.
    // Any page holding nothing but plugin shortcodes is a plugin page and goes too.
    foreach ((array) $wpdb->get_results("SELECT ID, post_content FROM {$wpdb->posts} WHERE post_type = 'page' AND post_status <> 'trash' AND post_content LIKE '%[wpjobportal%'") as $wpjobportal_candidate) {
        $wpjobportal_rest = preg_replace('/<!--.*?-->/s', '', (string) $wpjobportal_candidate->post_content);
        $wpjobportal_rest = preg_replace('/\[\/?wpjobportal[^\]]*\]/i', '', $wpjobportal_rest);
        if (trim(str_replace('&nbsp;', ' ', wp_strip_all_tags($wpjobportal_rest))) === '') {
            $wpjobportal_pages[] = (int) $wpjobportal_candidate->ID;
        }
    }

    // Pages the plugin created go to the trash (recoverable), and only those.
    foreach (array_unique(array_map('intval', $wpjobportal_pages)) as $wpjobportal_page_id) {
        $wpjobportal_page = $wpjobportal_page_id > 0 ? get_post($wpjobportal_page_id) : null;
        if ($wpjobportal_page && $wpjobportal_page->post_type === 'page' && strpos((string) $wpjobportal_page->post_content, '[wpjobportal') !== false) {
            // Marked so a later install puts it back instead of leaving its links on a trashed page.
            update_post_meta($wpjobportal_page->ID, '_wpjobportal_uninstall_trashed', 1);
            wp_trash_post($wpjobportal_page->ID);
        }
    }

    foreach (array('wpjobportal%', 'wpjp\\_%', 'job\\_portal\\_%', 'job-hub-layout', 'job-portal-layout', 'wp\\_job\\_portal\\_%', 'wpjob\\_portal\\_%', '\\_wpjsjp\\_session\\_', 'widget\\_wpjobportal%', '\\_transient\\_%wpjobportal%', '\\_transient\\_timeout\\_%wpjobportal%', '\\_transient\\_wpjp\\_%', '\\_transient\\_timeout\\_wpjp\\_%', '\\_site\\_transient\\_%wpjobportal%') as $wpjobportal_like) {
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $wpjobportal_like));
    }
    $wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'wpjobportal%'");
    wp_cache_flush();

    WPJOBPORTALdeactivation::wpjobportal_clear_cron();
    $wpjobportal_role = get_role('administrator');
    if ($wpjobportal_role) {
        $wpjobportal_role->remove_cap('wpjobportal');
        $wpjobportal_role->remove_cap('wpjobportal_jobs');
    }

    // The uploaded files: only a plain folder name directly inside uploads.
    $wpjobportal_upload = wp_upload_dir();
    if ($wpjobportal_datadir !== '' && preg_match('/^[A-Za-z0-9._-]+$/', $wpjobportal_datadir) && $wpjobportal_datadir !== '.' && $wpjobportal_datadir !== '..') {
        $wpjobportal_path = trailingslashit($wpjobportal_upload['basedir']) . $wpjobportal_datadir;
        if (is_dir($wpjobportal_path)) {
            if (!function_exists('WP_Filesystem')) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
            }
            global $wp_filesystem;
            if (WP_Filesystem() && $wp_filesystem) {
                $wp_filesystem->rmdir($wpjobportal_path, true);
            }
        }
    }
}

if(function_exists('is_multisite') && is_multisite()){
    $wpjobportal_blogs = wpjobportaldb_direct::get_col("SELECT blog_id FROM $wpdb->blogs");
    foreach($wpjobportal_blogs as $wpjobportal_blog_id){
        switch_to_blog( $wpjobportal_blog_id );
        if (!wpjobportal_uninstall_deletes_data()) {
            restore_current_blog();
            continue;
        }
        wpjobportal_uninstall_remove_everything_else(); // first: it reads the data folder from the config table
        $wpjobportal_tablestodrop = array_merge(WPJOBPORTALdeactivation::wpjobportal_tables_to_drop(), WPJOBPORTALdeactivation::wpjobportal_shared_tables_to_drop());
        foreach($wpjobportal_tablestodrop as $wpjobportal_tablename){
            wpjobportaldb_direct::query( "DROP TABLE IF EXISTS ".esc_sql($wpjobportal_tablename) );
        }
        delete_option('wpjobportal_notification_queue_enabled');
        delete_option('wpjobportal_notification_queue_schema');
        delete_option('wpjobportal_notification_queue_stats');
        delete_option('wpjobportal_route_registry_stats');
        delete_option('wpjobportal_authorization_stats');
        delete_option('wpjobportal_core_action_policy_stats');
        delete_option('wpjobportal_addon_action_policy_stats');
        delete_option('wpjobportal_address_import_stats');
        delete_option('wpjobportal_payment_service_stats');
        delete_option('wpjobportal_payment_event_ledger');
        delete_option('wpjobportal_privacy_retention');
        delete_option('wpjobportal_privacy_stats');
        delete_option('wpjobportal_product_mode');
        delete_option('wpjobportal_product_mode_selected_at');
        delete_option('wpjobportal_translations');
        delete_site_transient('wpjobportal_translations_manifest');
        wp_clear_scheduled_hook('wpjobportal_translations_daily');
        wp_clear_scheduled_hook('wpjobportal_privacy_retention_cleanup');
        restore_current_blog();
    }
}elseif(wpjobportal_uninstall_deletes_data()){
    wpjobportal_uninstall_remove_everything_else(); // first: it reads the data folder from the config table
    $wpjobportal_tablestodrop = array_merge(WPJOBPORTALdeactivation::wpjobportal_tables_to_drop(), WPJOBPORTALdeactivation::wpjobportal_shared_tables_to_drop());
    foreach($wpjobportal_tablestodrop as $wpjobportal_tablename){
        wpjobportaldb_direct::query( "DROP TABLE IF EXISTS ".esc_sql($wpjobportal_tablename) );
    }
    delete_option('wpjobportal_notification_queue_enabled');
    delete_option('wpjobportal_notification_queue_schema');
    delete_option('wpjobportal_notification_queue_stats');
    delete_option('wpjobportal_route_registry_stats');
    delete_option('wpjobportal_authorization_stats');
    delete_option('wpjobportal_core_action_policy_stats');
    delete_option('wpjobportal_addon_action_policy_stats');
    delete_option('wpjobportal_address_import_stats');
    delete_option('wpjobportal_payment_service_stats');
    delete_option('wpjobportal_payment_event_ledger');
    delete_option('wpjobportal_privacy_retention');
    delete_option('wpjobportal_privacy_stats');
    delete_option('wpjobportal_product_mode');
    delete_option('wpjobportal_product_mode_selected_at');
    delete_option('wpjobportal_translations');
    delete_site_transient('wpjobportal_translations_manifest');
    wp_clear_scheduled_hook('wpjobportal_translations_daily');
    wp_clear_scheduled_hook('wpjobportal_privacy_retention_cleanup');
}
