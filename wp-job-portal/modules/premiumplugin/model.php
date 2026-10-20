<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALpremiumpluginModel {

    /** Where a licence message waits until there is a screen to show it on. */
    const NOTICE_TRANSIENT = 'wpjobportal_licence_notices';

    private static $wpjobportal_server_url = 'https://wpjobportal.com/setup/index.php';

    function verfifyAddonActivation($wpjobportal_addon_name){
        $wpjobportal_option_name = 'transaction_key_for_wp-job-portal-'.$wpjobportal_addon_name;
        $wpjobportal_transaction_key = wpjobportal::$_common->getTranskey($wpjobportal_option_name);

        // An add-on now sold inside a suite is unlocked by the suite's licence.
        // Its own key still wins where there is one, so a customer who bought it
        // separately before it joined the suite is asked to do nothing at all;
        // only a site with no key of its own falls through. The slug has to
        // follow the token — the server validates a Suite key against the Suite,
        // not against the add-on it covers.
        $wpjobportal_verify_slug = $wpjobportal_addon_name;

        if (empty($wpjobportal_transaction_key) && class_exists('WPJOBPORTAL_Product_Map')) {
            $wpjobportal_covering = WPJOBPORTAL_Product_Map::licence_key_for($wpjobportal_addon_name);
            if (!empty($wpjobportal_covering['key'])) {
                $wpjobportal_products = WPJOBPORTAL_Product_Map::products();
                $wpjobportal_owner = $wpjobportal_covering['source'];
                if (isset($wpjobportal_products[$wpjobportal_owner]['licence'])
                    && $wpjobportal_products[$wpjobportal_owner]['licence'] !== ''
                ) {
                    $wpjobportal_transaction_key = $wpjobportal_covering['key'];
                    $wpjobportal_verify_slug = $wpjobportal_products[$wpjobportal_owner]['licence'];
                }
            }
        }

        // Since 2.7.0 one licence key covers every product. It is kept by
        // WPJOBPORTAL_Licence_Client, which checks it with the store and shows
        // the result on the Licences screen. Recruit Suite and the Marketplace
        // Suite's credits module still call this per-add-on check when they
        // are activated; with no per-add-on key it used to report "License key
        // not found" on a site whose licence was active. When the site has the
        // one key, that check is the one that counts, so say nothing here.
        if (empty($wpjobportal_transaction_key)
            && class_exists('WPJOBPORTAL_Licence_Client')
            && WPJOBPORTAL_Licence_Client::key() !== ''
        ) {
            return true;
        }

        try {
            if (! $wpjobportal_transaction_key ) {
                throw new Exception( 'License key not found' );
            }
            if ( empty( $wpjobportal_transaction_key ) ) {
                throw new Exception( 'License key not found' );
            }
            $wpjobportal_activate_results = $this->activate( array(
                'token'    => $wpjobportal_transaction_key,
                'plugin_slug'    => $wpjobportal_verify_slug
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
            self::noticeLater($e->getMessage());
            return false;
        }
    }

    /**
     * Say it where a notice is meant to be said.
     *
     * This used to echo straight out of the catch block, and the licence
     * check runs inside register_activation_hook(). Anything printed there is
     * "unexpected output" to WordPress: it refuses to report a clean
     * activation and tells the customer the plugin is broken, which on a site
     * with no licence key yet - every site, the first time - is every add-on
     * they activate. On an ordinary request the same echo lands wherever the
     * code happened to run, above the admin header or inside a redirect.
     *
     * The message is worth showing, so it is held over and rendered on the
     * next admin screen instead of thrown at the output stream.
     */
    public static function noticeLater($wpjobportal_message) {
        $wpjobportal_message = trim((string) $wpjobportal_message);
        if ($wpjobportal_message === '') {
            return;
        }
        $wpjobportal_queue = get_transient(self::NOTICE_TRANSIENT);
        $wpjobportal_queue = is_array($wpjobportal_queue) ? $wpjobportal_queue : array();
        if (!in_array($wpjobportal_message, $wpjobportal_queue, true)) {
            $wpjobportal_queue[] = $wpjobportal_message;
        }
        set_transient(self::NOTICE_TRANSIENT, array_slice($wpjobportal_queue, -5), 5 * MINUTE_IN_SECONDS);
    }

    /** Print whatever the licence check had to say, once, and forget it. */
    public static function renderNotices() {
        $wpjobportal_queue = get_transient(self::NOTICE_TRANSIENT);
        if (empty($wpjobportal_queue) || !is_array($wpjobportal_queue)) {
            return;
        }
        delete_transient(self::NOTICE_TRANSIENT);
        foreach ($wpjobportal_queue as $wpjobportal_message) {
            echo '<div class="notice notice-error is-dismissible"><p>'
                . esc_html($wpjobportal_message) . '.</p></div>';
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
            return wp_json_encode( array( 'error_code' => wp_remote_retrieve_response_code( $wpjobportal_request ), 'error' => __('Error code:', 'wp-job-portal') . ' ' . wp_remote_retrieve_response_code( $wpjobportal_request ) ) );
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
            return wp_json_encode( array( 'error_code' => wp_remote_retrieve_response_code( $wpjobportal_request ), 'error' => __('Error code:', 'wp-job-portal') . ' ' . wp_remote_retrieve_response_code( $wpjobportal_request ) ) );
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
            return wp_json_encode( array( 'error_code' => wp_remote_retrieve_response_code( $wpjobportal_request ), 'error' => __('Error code:', 'wp-job-portal') . ' ' . wp_remote_retrieve_response_code( $wpjobportal_request ) ) );
        }

        $response =  wp_remote_retrieve_body( $wpjobportal_request );
        return $response;
    }

    function getAddonUpdateSqlFromUpdateDir($wpjobportal_installedversion,$wpjobportal_newversion,$wpjobportal_directory){
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
        $wp_filesystem = wpjobportal::wpjobportal_filesystem();

        if($wpjobportal_installedversion != "" && $wpjobportal_newversion != ""){
            for ($wpjobportal_i = ($wpjobportal_installedversion + 1); $wpjobportal_i <= $wpjobportal_newversion; $wpjobportal_i++) {
                $wpjobportal_installfile = $wpjobportal_directory . '/' . $wpjobportal_i . '.sql';

                // Check if the file exists
                if ($wp_filesystem->exists($wpjobportal_installfile)) {
                    // Get the file contents
                    $file_contents = $wp_filesystem->get_contents($wpjobportal_installfile);
                    if ($file_contents !== false) {
                        /*
                         * One splitter for every SQL file this plugin runs.
                         *
                         * This loop joined a statement's lines with implode() and no
                         * separator, and ended a statement on a semicolon that merely
                         * closed a comment, so an add-on migration written over more
                         * than one line would have reached MySQL with its words glued
                         * together. Core had the same reader, and its own 270.sql is
                         * the first migration not written one statement to a line.
                         */
                        $wpjobportal_statements = class_exists('WPJOBPORTAL_Migration_Service')
                            ? WPJOBPORTAL_Migration_Service::split_sql($file_contents)
                            : array();

                        foreach ($wpjobportal_statements as $query_string) {
                            $query_string = wpjobportalphplib::wpJP_str_replace("#__", wpjobportal::$_db->prefix, $query_string);
                            if (!empty($query_string)) {
                                wpjobportal::$_db->query($query_string);
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
            return wp_json_encode( array( 'error_code' => wp_remote_retrieve_response_code( $wpjobportal_request ), 'error' => __('Error code:', 'wp-job-portal') . ' ' . wp_remote_retrieve_response_code( $wpjobportal_request ) ) );
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
