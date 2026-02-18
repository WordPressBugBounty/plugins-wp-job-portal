<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALzywraplogsModel {

    function getMessagekey(){
        $key = 'zywraplogs';
        if(is_admin()){
            $key = 'admin_'.$key;
        }
        return $key;
    }
    /**
     * Fetches log data from the database and calculates summary statistics for the dashboard.
     *
     * It uses the 'model_code' column for model data and completely excludes 'cost' data.
     *
     * @return array An array containing 'logs' (the main table data) and 'summary' (dashboard cards data).
     */
    function wpjobportalGetLogsData() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wj_portal_zywrap_logs';
        $text_domain = 'wp-job-portal';
        $results = array(
            'logs'    => array(),
            'summary' => array(),
        );
        
        // --- 1. Security and Pagination Setup ---
        
        // Sanitize the page number input
        $pagenum = isset($_GET['pagenum']) ? absint( $_GET['pagenum'] ) : 1;
        if ( $pagenum < 1 ) {
            $pagenum = 1;
        }

        // You must load configuration dynamically for production, using a fallback here.
        // Assuming your custom methods return the correct values:
        $limit = 20; // Fallback page size, replace with your config model call if necessary.
        // $limit = WPJOBPORTALincluder::WPJOBPORTAL_getModel('configuration')->getConfigValue('pagination_default_page_size');
        
        $offset = ( $pagenum - 1 ) * $limit;

        // --- 2. Calculate Summary Data (Last 24 Hours) ---
        
        $cutoff_time = date('Y-m-d H:i:s', strtotime('-24 hours'));
        
        // Count total runs in the last 24 hours
        $total_runs_24h = $wpdb->get_var( $wpdb->prepare( 
            "SELECT COUNT(id) FROM $table_name WHERE timestamp >= %s", 
            $cutoff_time 
        ) );
        
        // Count errors (HTTP >= 400) in the last 24 hours
        $api_errors_24h = $wpdb->get_var( $wpdb->prepare( 
            "SELECT COUNT(id) FROM $table_name WHERE timestamp >= %s AND status >= 400", 
            $cutoff_time 
        ) );

        // Find the most frequent model used, using the existing 'model_code' column
        $top_model_used = $wpdb->get_var( $wpdb->prepare(
            "SELECT model_code FROM $table_name WHERE timestamp >= %s GROUP BY model_code ORDER BY COUNT(model_code) DESC LIMIT 1",
            $cutoff_time
        ) );
        
        $results['summary'] = array(
            'runs'    => number_format_i18n( absint($total_runs_24h) ),
            'errors'  => number_format_i18n( absint($api_errors_24h) ),
            'model'   => empty( $top_model_used ) ? __('N/A', $text_domain) : esc_html( $top_model_used ),
        );

        // --- 3. Fetch Main Log Data (with Pagination) ---
        
        $total_logs = $wpdb->get_var( "SELECT COUNT(id) FROM $table_name" );
        wpjobportal::$_data['total'] = $total_logs;
        wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($total_logs);
        
        // Query to get the logs: We still use SELECT * to avoid hardcoding every column name, 
        // but the presentation layer must only use columns we know exist (like model_code).
        $query = $wpdb->prepare( 
            "SELECT * FROM $table_name ORDER BY timestamp DESC LIMIT %d OFFSET %d", 
            WPJOBPORTALpagination::$_limit,
            WPJOBPORTALpagination::$_offset
        );
        
        $results['logs'] = $wpdb->get_results( $query, ARRAY_A );
        wpjobportal::$_data[0] = $results;
        return ;
    }
}
?>