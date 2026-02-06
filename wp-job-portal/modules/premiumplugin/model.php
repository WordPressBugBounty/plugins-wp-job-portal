<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALpremiumpluginModel {

    private static $wpjobportal_server_url = 'https://wpjobportal.com/setup/index.php';

    function verfifyAddonActivation($wpjobportal_addon_name){
        $wpjobportal_option_name = 'transaction_key_for_wp-job-portal-'.$wpjobportal_addon_name;
        $wpjobportal_transaction_key = wpjobportal::$_common->getTranskey($wpjobportal_option_name);
        try {
            if (! $wpjobportal_transaction_key ) {
                throw new Exception( 'License key not found' );
            }
            if ( empty( $wpjobportal_transaction_key ) ) {
                throw new Exception( 'License key not found' );
            }
            $wpjobportal_activate_results = $this->activate( array(
                'token'    => $wpjobportal_transaction_key,
                'plugin_slug'    => $wpjobportal_addon_name
            ) );
            if ( false === $wpjobportal_activate_results ) {
                throw new Exception( 'Connection failed to the server' );
            } elseif ( isset( $wpjobportal_activate_results['error_code'] ) ) {
                throw new Exception( $wpjobportal_activate_results['error'] );
            } elseif(isset($wpjobportal_activate_results['verfication_status']) && $wpjobportal_activate_results['verfication_status'] == 1 ){
                return true;
            }
            throw new Exception( 'License could not activate. Please contact support.' );
        } catch ( Exception $e ) {
            echo '<div class="notice notice-error is-dismissible">
                    <p>'.esc_html($e->getMessage()).'.</p>
                </div>';
            return false;
        }
    }

    function logAddonDeactivation($wpjobportal_addon_name){
        $wpjobportal_option_name = 'transaction_key_for_wp-job-portal-'.$wpjobportal_addon_name;
        $wpjobportal_transaction_key = wpjobportal::$_common->getTranskey($wpjobportal_option_name);

        $wpjobportal_activate_results = $this->deactivate( array(
            'token'    => $wpjobportal_transaction_key,
            'plugin_slug'    => $wpjobportal_addon_name
        ) );
    }

    function logAddonDeletion($wpjobportal_addon_name){
        $wpjobportal_option_name = 'transaction_key_for_wp-job-portal-'.$wpjobportal_addon_name;
        $wpjobportal_transaction_key = wpjobportal::$_common->getTranskey($wpjobportal_option_name);
        $wpjobportal_activate_results = $this->delete( array(
            'token'    => $wpjobportal_transaction_key,
            'plugin_slug'    => $wpjobportal_addon_name
        ) );
    }

    public static function activate( $wpjobportal_args ) {
        $site_url = site_url();
        $site_url = wpjobportalphplib::wpJP_str_replace("http://","",$site_url);
        $site_url = wpjobportalphplib::wpJP_str_replace("https://","",$site_url);

        $wpjobportal_defaults = array(
            'request'  => 'activate',
            'domain' => $site_url,
            'activation_call' => 1
        );

        $wpjobportal_args    = wp_parse_args( $wpjobportal_defaults, $wpjobportal_args );

        $wpjobportal_request = wp_remote_get( self::$wpjobportal_server_url . '?' . http_build_query( $wpjobportal_args, '', '&' ) );
        if ( is_wp_error( $wpjobportal_request ) ) {
            return wp_json_encode( array( 'error_code' => $wpjobportal_request->get_error_code(), 'error' => $wpjobportal_request->get_error_message() ) );
        }

        if ( wp_remote_retrieve_response_code( $wpjobportal_request ) != 200 ) {
            return wp_json_encode( array( 'error_code' => wp_remote_retrieve_response_code( $wpjobportal_request ), 'error' => 'Error code: ' . wp_remote_retrieve_response_code( $wpjobportal_request ) ) );
        }
        $response =  wp_remote_retrieve_body( $wpjobportal_request );
        $response = json_decode($response,true);
        return $response;
    }

    /**
     * Attempt t deactivate a license
     */
    public static function deactivate( $dargs ) {
        $site_url = site_url();
        $site_url = wpjobportalphplib::wpJP_str_replace("http://","",$site_url);
        $site_url = wpjobportalphplib::wpJP_str_replace("https://","",$site_url);

        $wpjobportal_defaults = array(
            'request'  => 'deactivate',
            'domain' => $site_url
        );

        $wpjobportal_args    = wp_parse_args( $wpjobportal_defaults, $dargs );
        $wpjobportal_request = wp_remote_get( self::$wpjobportal_server_url . '?' . http_build_query( $wpjobportal_args, '', '&' ) );
        if ( is_wp_error( $wpjobportal_request ) || wp_remote_retrieve_response_code( $wpjobportal_request ) != 200 ) {
            return false;
        } else {
            return wp_remote_retrieve_body( $wpjobportal_request );
        }
    }
    /**
     * Attempt t deactivate a license
     */
    public static function delete( $wpjobportal_args ) {
        $site_url = site_url();
        $site_url = wpjobportalphplib::wpJP_str_replace("http://","",$site_url);
        $site_url = wpjobportalphplib::wpJP_str_replace("https://","",$site_url);

        $wpjobportal_defaults = array(
            'request'  => 'delete',
            'domain' => $site_url,
        );

        $wpjobportal_args    = wp_parse_args( $wpjobportal_defaults, $wpjobportal_args );
        $wpjobportal_request = wp_remote_get( self::$wpjobportal_server_url . '?' . http_build_query( $wpjobportal_args, '', '&' ) );
        if ( is_wp_error( $wpjobportal_request ) || wp_remote_retrieve_response_code( $wpjobportal_request ) != 200 ) {
            return false;
        } else {
            return;
        }
    }

    function verifyAddonSqlFile($wpjobportal_addon_name,$wpjobportal_addon_version){
        $wpjobportal_option_name = 'transaction_key_for_wp-job-portal-'.$wpjobportal_addon_name;
        $wpjobportal_transaction_key = wpjobportal::$_common->getTranskey($wpjobportal_option_name);
        // $wpjobportal_addonversion = wpjobportalphplib::wpJP_str_replace('.', '', $wpjobportal_addon_version);
        $site_url = site_url();
        $site_url = wpjobportalphplib::wpJP_str_replace("http://","",$site_url);
        $site_url = wpjobportalphplib::wpJP_str_replace("https://","",$site_url);

        $wpjobportal_network_site_url = network_site_url();
        $wpjobportal_network_site_url = wpjobportalphplib::wpJP_str_replace("http://","",$wpjobportal_network_site_url);
        $wpjobportal_network_site_url = wpjobportalphplib::wpJP_str_replace("https://","",$wpjobportal_network_site_url);

        $wpjobportal_defaults = array(
            'request'  => 'getactivatesql',
            'domain' => $wpjobportal_network_site_url,
            'subsite' => $site_url,
            'activation_call' => 1,
            'plugin_slug' => $wpjobportal_addon_name,
            'addonversion' => $wpjobportal_addon_version,
            'token' => $wpjobportal_transaction_key
        );
        $wpjobportal_request = wp_remote_get( self::$wpjobportal_server_url . '?' . http_build_query( $wpjobportal_defaults, '', '&' ) );
        if ( is_wp_error( $wpjobportal_request ) ) {
            return wp_json_encode( array( 'error_code' => $wpjobportal_request->get_error_code(), 'error' => $wpjobportal_request->get_error_message() ) );
        }

        if ( wp_remote_retrieve_response_code( $wpjobportal_request ) != 200 ) {
            return wp_json_encode( array( 'error_code' => wp_remote_retrieve_response_code( $wpjobportal_request ), 'error' => 'Error code: ' . wp_remote_retrieve_response_code( $wpjobportal_request ) ) );
        }

        $response =  wp_remote_retrieve_body( $wpjobportal_request );
        return $response;
    }

    function getAddonSqlForUpdation($wpjobportal_plugin_slug,$wpjobportal_installed_version,$wpjobportal_new_version){
        $wpjobportal_option_name = 'transaction_key_for_wp-job-portal-'.$wpjobportal_plugin_slug;
        $wpjobportal_transaction_key = wpjobportal::$_common->getTranskey($wpjobportal_option_name);
        $site_url = site_url();
        $site_url = wpjobportalphplib::wpJP_str_replace("http://","",$site_url);
        $site_url = wpjobportalphplib::wpJP_str_replace("https://","",$site_url);

        $wpjobportal_network_site_url = network_site_url();
        $wpjobportal_network_site_url = wpjobportalphplib::wpJP_str_replace("http://","",$wpjobportal_network_site_url);
        $wpjobportal_network_site_url = wpjobportalphplib::wpJP_str_replace("https://","",$wpjobportal_network_site_url);

        $wpjobportal_defaults = array(
            'request'  => 'getupdatesql',
            'domain' => $wpjobportal_network_site_url,
            'subsite' => $site_url,
            'activation_call' => 1,
            'plugin_slug' => $wpjobportal_plugin_slug,
            'installedversion' => $wpjobportal_installed_version,
            'newversion' => $wpjobportal_new_version,
            'token' => $wpjobportal_transaction_key
        );

        $wpjobportal_request = wp_remote_get( self::$wpjobportal_server_url . '?' . http_build_query( $wpjobportal_defaults, '', '&' ) );
        if ( is_wp_error( $wpjobportal_request ) ) {
            return wp_json_encode( array( 'error_code' => $wpjobportal_request->get_error_code(), 'error' => $wpjobportal_request->get_error_message() ) );
        }

        if ( wp_remote_retrieve_response_code( $wpjobportal_request ) != 200 ) {
            return wp_json_encode( array( 'error_code' => wp_remote_retrieve_response_code( $wpjobportal_request ), 'error' => 'Error code: ' . wp_remote_retrieve_response_code( $wpjobportal_request ) ) );
        }

        $response =  wp_remote_retrieve_body( $wpjobportal_request );
        return $response;
    }

    function getAddonUpdateSqlFromUpdateDir($wpjobportal_installedversion,$wpjobportal_newversion,$wpjobportal_directory){
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
        if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ) {
            $creds = request_filesystem_credentials( site_url() );
            wp_filesystem( $creds );
        }

        if($wpjobportal_installedversion != "" && $wpjobportal_newversion != ""){
            for ($wpjobportal_i = ($wpjobportal_installedversion + 1); $wpjobportal_i <= $wpjobportal_newversion; $wpjobportal_i++) {
                $wpjobportal_installfile = $wpjobportal_directory . '/' . $wpjobportal_i . '.sql';

                // Check if the file exists
                if ($wp_filesystem->exists($wpjobportal_installfile)) {
                    $wpjobportal_delimiter = ';';
                    // Get the file contents
                    $file_contents = $wp_filesystem->get_contents($wpjobportal_installfile);
                    if ($file_contents !== false) {
                        $lines = explode("\n", $file_contents);
                        $query = array();

                        foreach ($lines as $line) {
                            $query[] = $line;
                            if (wpjobportalphplib::wpJP_preg_match('~' . preg_quote($wpjobportal_delimiter, '~') . '\s*$~iS', end($query)) === 1) {
                                $query_string = wpjobportalphplib::wpJP_trim(implode('', $query));
                                $query_string = wpjobportalphplib::wpJP_str_replace("#__", wpjobportal::$_db->prefix, $query_string);
                                if (!empty($query_string)) {
                                    wpjobportal::$_db->query($query_string);
                                }
                                $query = array();
                            }
                        }
                    } else {
                        //echo 'Failed to open file.';
                    }
                }
            }
        }
    }

    function getAddonUpdateSqlFromLive($wpjobportal_installedversion,$wpjobportal_newversion,$wpjobportal_plugin_slug){
        if($wpjobportal_installedversion != "" && $wpjobportal_newversion != "" && $wpjobportal_plugin_slug != ""){
            $wpjobportal_addonsql = $this->getAddonSqlForUpdation($wpjobportal_plugin_slug,$wpjobportal_installedversion,$wpjobportal_newversion);
            $wpjobportal_decodedata = json_decode($wpjobportal_addonsql,true);
            $wpjobportal_delimiter = ';';
			if(isset($wpjobportal_decodedata['update_sql'])) $wpjobportal_update_sql = $wpjobportal_decodedata['update_sql']; else $wpjobportal_update_sql = "";
            if(isset($wpjobportal_decodedata['verfication_status']) && $wpjobportal_update_sql != ""){
                $lines = wpjobportalphplib::wpJP_explode(PHP_EOL, $wpjobportal_addonsql);
                if(!empty($lines)){
                    foreach($lines as $line){
                        $query[] = $line;
                        if (wpjobportalphplib::wpJP_preg_match('~' . preg_quote($wpjobportal_delimiter, '~') . '\s*$~iS', end($query)) === 1) {
                            $query = wpjobportalphplib::wpJP_trim(implode('', $query));
                            $query = wpjobportalphplib::wpJP_str_replace("#__", wpjobportal::$_db->prefix, $query);
                            if (!empty($query)) {
                                wpjobportal::$_db->query($query);
                            }
                        }
                        if (is_string($query) === true) {
                            $query = array();
                        }
                    }
                }
            }
        }
    }

    // get country cities sql from live
    function getAddressSqlFile($wpjobportal_addon_name,$wpjobportal_addon_version,$wpjobportal_countrycode){
        $wpjobportal_option_name = 'transaction_key_for_wp-job-portal-'.$wpjobportal_addon_name;
        $wpjobportal_transaction_key = wpjobportal::$_common->getTranskey($wpjobportal_option_name);
        // $wpjobportal_addonversion = wpjobportalphplib::wpJP_str_replace('.', '', $wpjobportal_addon_version);
        $site_url = site_url();
        $site_url = wpjobportalphplib::wpJP_str_replace("http://","",$site_url);
        $site_url = wpjobportalphplib::wpJP_str_replace("https://","",$site_url);

        $wpjobportal_network_site_url = network_site_url();
        $wpjobportal_network_site_url = wpjobportalphplib::wpJP_str_replace("http://","",$wpjobportal_network_site_url);
        $wpjobportal_network_site_url = wpjobportalphplib::wpJP_str_replace("https://","",$wpjobportal_network_site_url);

        $wpjobportal_defaults = array(
            'request'  => 'getcitiessql',
            'domain' => $wpjobportal_network_site_url,
            'subsite' => $site_url,
            'activation_call' => 1,
            'plugin_slug' => $wpjobportal_addon_name,
            'addonversion' => $wpjobportal_addon_version,
            'countrycode' => $wpjobportal_countrycode,
            'token' => $wpjobportal_transaction_key
        );

        $wpjobportal_request = wp_remote_get( self::$wpjobportal_server_url . '?' . http_build_query( $wpjobportal_defaults, '', '&' ) );
        if ( is_wp_error( $wpjobportal_request ) ) {
            return wp_json_encode( array( 'error_code' => $wpjobportal_request->get_error_code(), 'error' => $wpjobportal_request->get_error_message() ) );
        }

        if ( wp_remote_retrieve_response_code( $wpjobportal_request ) != 200 ) {
            return wp_json_encode( array( 'error_code' => wp_remote_retrieve_response_code( $wpjobportal_request ), 'error' => 'Error code: ' . wp_remote_retrieve_response_code( $wpjobportal_request ) ) );
        }

        $response =  wp_remote_retrieve_body( $wpjobportal_request );
        return $response;
    }


    function wpjobportal_count_unused_keys() {
        $query = "SELECT option_name, option_value
            FROM `" . wpjobportal::$_db->prefix . "options` WHERE option_name LIKE 'transaction_key_for_wp-job-portal%'";
            $wpjobportal_results = wpjobportaldb::get_results($query);

        if (empty($wpjobportal_results)) {
            return 0;
        }

        $unused = [];

        foreach ($wpjobportal_results as $wpjobportal_row) {
            $wpjobportal_addon_slug = str_replace('transaction_key_for_', '', $wpjobportal_row->option_name);

            // 🔹 Check if addon is installed
            $wpjobportal_is_installed = apply_filters(
                'wpjobportal_is_addon_installed',
                file_exists(WP_PLUGIN_DIR . '/' . $wpjobportal_addon_slug)
            );

            if (!$wpjobportal_is_installed && !empty($wpjobportal_row->option_value)) {
                $unused[] = $wpjobportal_row->option_value;
            }
        }

        return count(array_unique($unused));
    }

    function wpjobportalRemoveUnusedKeys() {
        $query = "SELECT option_name, option_value
            FROM `" . wpjobportal::$_db->prefix . "options` WHERE option_name LIKE 'transaction_key_for_wp-job-portal%'";
            $wpjobportal_results = wpjobportaldb::get_results($query);

        if (empty($wpjobportal_results)) {
            return 0;
        }

        $wpjobportal_deleted_keys = [];

        foreach ($wpjobportal_results as $wpjobportal_row) {
            $wpjobportal_addon_slug = str_replace('transaction_key_for_', '', $wpjobportal_row->option_name);

            // 🔹 Check if addon is installed
            $wpjobportal_is_installed = apply_filters(
                'wpjobportal_is_addon_installed',
                file_exists(WP_PLUGIN_DIR . '/' . $wpjobportal_addon_slug)
            );

            if (!$wpjobportal_is_installed && !empty($wpjobportal_row->option_value)) {
                if (delete_option($wpjobportal_row->option_name)) {
                    $wpjobportal_deleted_keys[$wpjobportal_row->option_value] = true; // track by key
                }
            }
        }

        return count($wpjobportal_deleted_keys); // number of unique keys removed
    }

    function getMessagekey(){
        $wpjobportal_key = 'premiumplugin';if(wpjobportal::$_common->wpjp_isadmin()){$wpjobportal_key = 'admin_'.$wpjobportal_key;}return $wpjobportal_key;
    }

}

?>
