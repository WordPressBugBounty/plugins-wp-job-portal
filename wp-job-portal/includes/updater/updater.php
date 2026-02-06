<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

do_action('wpjobportal_load_wp_plugin_file');
// check for plugin using plugin name
if (is_plugin_active('wp-job-portal/wp-job-portal.php')) {
	$wpjobportal_query = "SELECT * FROM `".wpjobportal::$_db->prefix."wj_portal_config` WHERE configname = 'versioncode' OR configname = 'last_version' OR configname = 'last_step_updater'";
	$wpjobportal_result = wpjobportal::$_db->get_results($wpjobportal_query);
	$wpjobportal_config = array();
	foreach($wpjobportal_result AS $wpjobportal_rs){
		$wpjobportal_config[$wpjobportal_rs->configname] = $wpjobportal_rs->configvalue;
	}
	$wpjobportal_config['versioncode'] = wpjobportalphplib::wpJP_str_replace('.', '', $wpjobportal_config['versioncode']);

    if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
    if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ) {
        $wpjobportal_creds = request_filesystem_credentials( site_url() );
        wp_filesystem( $wpjobportal_creds );
    }

	if(!empty($wpjobportal_config['last_version']) && $wpjobportal_config['last_version'] != '' && $wpjobportal_config['last_version'] < $wpjobportal_config['versioncode']){
		$wpjobportal_last_version = $wpjobportal_config['last_version'] + 1; // files execute from the next version
		$wpjobportal_currentversion = $wpjobportal_config['versioncode'];
		for($wpjobportal_i = $wpjobportal_last_version; $wpjobportal_i <= $wpjobportal_currentversion; $wpjobportal_i++){
			$wpjobportal_path = WPJOBPORTAL_PLUGIN_PATH.'includes/updater/files/'.$wpjobportal_i.'.php';
			if($wp_filesystem->exists($wpjobportal_path)){
				include_once($wpjobportal_path);
			}
		}
	}
	$wpjobportal_mainfile = WPJOBPORTAL_PLUGIN_URL.'wp-job-portal.php';
	$wpjobportal_contents_file = wp_remote_get($wpjobportal_mainfile);
    if (is_wp_error($wpjobportal_contents_file)) {
    	$wpjobportal_contents = '';
    }else{
    	$wpjobportal_contents = $wpjobportal_contents_file['body'];
    }
	$wpjobportal_contents = wpjobportalphplib::wpJP_str_replace("include_once 'includes/updater/updater.php';", '', $wpjobportal_contents);
	if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
    if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ) {
        $wpjobportal_creds = request_filesystem_credentials( site_url() );
        wp_filesystem( $wpjobportal_creds );
    }
    $wp_filesystem->put_contents( $wpjobportal_mainfile, $wpjobportal_contents);

	function wpjobportal_recursiveremove($wpjobportal_dir) {
		$wpjobportal_structure = glob(rtrim($wpjobportal_dir, "/").'/*');
		if (is_array($wpjobportal_structure)) {
			foreach($wpjobportal_structure as $file) {
				if (is_dir($file)) wpjobportal_recursiveremove($file);
				elseif (is_file($file)) wp_delete_file($file);
			}
		}
		if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
        if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ) {
            $wpjobportal_creds = request_filesystem_credentials( site_url() );
            wp_filesystem( $wpjobportal_creds );
        }
        $wp_filesystem->rmdir($wpjobportal_dir);
	}            	
	$wpjobportal_dir = WPJOBPORTAL_PLUGIN_PATH.'includes/updater';
	wpjobportal_recursiveremove($wpjobportal_dir);

}



?>
