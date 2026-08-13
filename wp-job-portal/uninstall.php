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
include_once 'includes/deactivation.php';

if(function_exists('is_multisite') && is_multisite()){
    $wpjobportal_blogs = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
    foreach($wpjobportal_blogs as $wpjobportal_blog_id){
        switch_to_blog( $wpjobportal_blog_id );
        $wpjobportal_tablestodrop = WPJOBPORTALdeactivation::wpjobportal_tables_to_drop();
        foreach($wpjobportal_tablestodrop as $wpjobportal_tablename){
            $wpdb->query( "DROP TABLE IF EXISTS ".esc_sql($wpjobportal_tablename) );
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
        wp_clear_scheduled_hook('wpjobportal_privacy_retention_cleanup');
        restore_current_blog();
    }
}else{
    $wpjobportal_tablestodrop = WPJOBPORTALdeactivation::wpjobportal_tables_to_drop();
    foreach($wpjobportal_tablestodrop as $wpjobportal_tablename){
        $wpdb->query( "DROP TABLE IF EXISTS ".esc_sql($wpjobportal_tablename) );
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
    wp_clear_scheduled_hook('wpjobportal_privacy_retention_cleanup');
}
