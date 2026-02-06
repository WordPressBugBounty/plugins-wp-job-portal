<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
if(!empty($this->addon_installed_array)){
	$wpjobportal_new_transient_flag = 0;
	//delete_transient('wpjobportal_addon_update_flag');
	$wpjobportal_response = get_transient('wpjobportal_addon_update_flag');
	if(!$wpjobportal_response){
		$wpjobportal_response = $this->getPluginLatestVersionData();
		set_transient('wpjobportal_addon_update_flag',$wpjobportal_response,HOUR_IN_SECONDS * 6);
		$wpjobportal_new_transient_flag = 1;
	}
	if(!empty($wpjobportal_response)){
		foreach ($this->addon_installed_array as $wpjobportal_addon) {
			if(!isset($wpjobportal_response[$wpjobportal_addon])){
				continue;
			}
			$wpjobportal_plugin_file_path = WP_PLUGIN_DIR."/".$wpjobportal_addon.'/'.$wpjobportal_addon.'.php';

			// $wpjobportal_plugin_file_path = plugins_url($wpjobportal_addon . '/' . $wpjobportal_addon . '.php');
			// $wpjobportal_plugin_file_path = content_url().'/plugins/'.$wpjobportal_addon.'/'.$wpjobportal_addon.'.php';

			$wpjobportal_plugin_data = get_plugin_data($wpjobportal_plugin_file_path);
			$wpjobportal_transient_val = get_transient('dismiss-wpjobportal-addon-update-notice-'.$wpjobportal_addon);
			if($wpjobportal_new_transient_flag == 1){
				delete_transient('dismiss-wpjobportal-addon-update-notice-'.$wpjobportal_addon);
			}
			if(!$wpjobportal_transient_val){
				if (version_compare( $wpjobportal_response[$wpjobportal_addon], $wpjobportal_plugin_data['Version'], '>' ) ) { ?>
					<div class="updated">
						<p class="wpjm-updater-dismiss" style="float:right;"><a href="<?php echo esc_url( add_query_arg( 'dismiss-wpjobportal-addon-update-notice-' . sanitize_title( $wpjobportal_addon ), '1' ) ); ?>"><?php __('Hide notice', 'wp-job-portal'); ?></a></p>
						<p><?php printf( '<a href="%s">New Version is avaible</a> for "%s".', esc_url_raw(admin_url('plugins.php')), esc_html( $wpjobportal_plugin_data['Name'] ) ); ?></p>
					</div>
				<?php }
			}
		}
	}

}

if(get_option( 'wpjobportal-addon-key-error-message', '' ) != ''){
	echo '<div class="notice notice-error is-dismissible"><p>'. esc_html(get_option( 'wpjobportal-addon-key-error-message')) .'</p></div>';
	delete_option( 'wpjobportal-addon-key-error-message' );
}
?>
