<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
/* Update for custom plugins by joomsky */
class WPJOBPORTAL_JOBPORTALUpdater {

	private $api_key = '';
	private $addon_update_data = array();
	private $addon_update_data_errors = array();
	public $addon_installed_array = '';// it is public static bcz it is being used in extended class

	public $addon_installed_version_data = '';// it is public static bcz it is being used in extended class

	public function __construct() {
		$this->jsUpdateIntilized();

		$wpjobportal_transaction_key_array = array();
		$wpjobportal_addon_installed_array = array();
		foreach (wpjobportal::$_active_addons AS $wpjobportal_addon) {
			$wpjobportal_addon_installed_array[] = 'wp-job-portal-'.$wpjobportal_addon;
			$wpjobportal_option_name = 'transaction_key_for_wp-job-portal-'.$wpjobportal_addon;
			$wpjobportal_transaction_key = wpjobportal::$_common->getTranskey($wpjobportal_option_name);
			if(!in_array($wpjobportal_transaction_key, $wpjobportal_transaction_key_array)){
				$wpjobportal_transaction_key_array[] = $wpjobportal_transaction_key;
			}
		}
		$this->addon_installed_array = $wpjobportal_addon_installed_array;
		$this->api_key = wp_json_encode($wpjobportal_transaction_key_array);
	}

	// class constructor triggers this function. sets up intail hooks and filters to be used.
	public function jsUpdateIntilized(  ) {
		add_action( 'admin_init', array( $this, 'jsAdminIntilization' ) );
		include_once( 'class-js-server-calls.php' );
	}

	// admin init hook triggers this fuction. sets up admin specific hooks and filter
	public function jsAdminIntilization() {


		add_filter( 'plugins_api', array( $this, 'jsPluginsAPI' ), 10, 3 );

		if ( current_user_can( 'update_plugins' ) ) {
			$this->jsCheckTriggers();
			add_action( 'admin_notices', array( $this, 'jsCheckUpdateNotice' ) );
			add_action( 'after_plugin_row', array( $this, 'jsKeyInput' ) );
		}
	}

	public function jsKeyInput( $file ) {
		$file_array = wpjobportalphplib::wpJP_explode('/', $file);
		$wpjobportal_addon_slug = $file_array[0];
		if(wpjobportalphplib::wpJP_strstr($wpjobportal_addon_slug, 'wp-job-portal-')){
			$wpjobportal_addon_name = wpjobportalphplib::wpJP_str_replace('wp-job-portal-', '', $wpjobportal_addon_slug);
			if(isset($this->addon_update_data[$file]) || !in_array($wpjobportal_addon_name, wpjobportal::$_active_addons)){ // Only checking which addon have update version
				$wpjobportal_option_name = 'transaction_key_for_wp-job-portal-'.$wpjobportal_addon_name;
				$wpjobportal_transaction_key = wpjobportal::$_common->getTranskey($wpjobportal_option_name);
				if($wpjobportal_transaction_key == '' || $wpjobportal_transaction_key == null){
					$wpjobportal_transaction_key = 0;
				}
				$verify_results = WPJOBPORTALincluder::getJSModel('premiumplugin')->activate( array(
		            'token'    => $wpjobportal_transaction_key,
		            'plugin_slug'    => $wpjobportal_addon_name
		        ) );
		        if(isset($verify_results['verfication_status']) && $verify_results['verfication_status'] == 0){
		        	$wpjobportal_updateaddon_slug = wpjobportalphplib::wpJP_str_replace("-", " ", $wpjobportal_addon_slug);
		        	$message = wpjobportalphplib::wpJP_strtoupper( wpjobportalphplib::wpJP_substr( $wpjobportal_updateaddon_slug, 0, 2 ) ).wpjobportalphplib::wpJP_substr(  wpjobportalphplib::wpJP_ucwords($wpjobportal_updateaddon_slug), 2 ) .' authentication failed. Please insert valid key for authentication.';
		        	if(isset($this->addon_update_data[$file])){
		        		$message = 'There is new version of '. wpjobportalphplib::wpJP_strtoupper( wpjobportalphplib::wpJP_substr( $wpjobportal_updateaddon_slug, 0, 2 ) ).wpjobportalphplib::wpJP_substr(  wpjobportalphplib::wpJP_ucwords($wpjobportal_updateaddon_slug), 2 ) .' avaible. Please insert valid activation key for updation.';
		        		remove_action('after_plugin_row_'.$file,'wp_plugin_update_row');
					}
		        	include( 'views/html-key-input.php' );
		        	echo '
					<tr>
						<td class="plugin-update plugin-update colspanchange" colspan="3">
							<div class="update-message notice inline notice-error notice-alt"><p>'. esc_html($message) .'</p></div>
						</td>
					</tr>';
		        }
			}
		}
	}

	public function jsCheckVersionUpdate( $wpjobportal_update_data ) {
		if ( empty( $wpjobportal_update_data->checked ) ) {
			return $wpjobportal_update_data;
		}
		$response_version_data = get_transient('wpjobportal_addon_update_temp_data');
		$response_version_data_cdn = get_transient('wpjobportal_addon_update_temp_data_cdn');

		if(isset($_SERVER) &&  $_SERVER['REQUEST_URI'] !=''){
            if(wpjobportalphplib::wpJP_strstr( $_SERVER['REQUEST_URI'], 'plugins.php')) {
				$response_version_data = get_transient('wpjobportal_addon_update_temp_data_plugins');
				$response_version_data_cdn = get_transient('wpjobportal_addon_update_temp_data_plugins_cdn');
			}
        }
        if($response_version_data_cdn === false){
			$wpjobportal_cdnversiondata = $this->getPluginVersionDataFromCDN();
			set_transient('wpjobportal_addon_update_temp_data_cdn', $wpjobportal_cdnversiondata, HOUR_IN_SECONDS * 6);
			set_transient('wpjobportal_addon_update_temp_data_plugins_cdn', $wpjobportal_cdnversiondata, 15);
		}else{
			$wpjobportal_cdnversiondata = $response_version_data_cdn;
		}
		$wpjobportal_newversionfound = 0;
		if ( $wpjobportal_cdnversiondata) {
			if(is_object($wpjobportal_cdnversiondata) ){
				foreach ($wpjobportal_update_data->checked AS $wpjobportal_key => $wpjobportal_value) {
					$c_key_array = wpjobportalphplib::wpJP_explode('/', $wpjobportal_key);
					$c_key = $c_key_array[0];
					$c_key = wpjobportalphplib::wpJP_str_replace("-","",$c_key);
					$wpjobportal_newversion = $this->getVersionFromLiveData($wpjobportal_cdnversiondata, $c_key);
					if($wpjobportal_newversion){
						if(version_compare( $wpjobportal_newversion, $wpjobportal_value, '>' )){
							$wpjobportal_newversionfound = 1;
						}
					}
				}
			}
		}
		if($wpjobportal_newversionfound == 1){
			if($response_version_data === false){
				$response = $this->getPluginVersionData();
				set_transient('wpjobportal_addon_update_temp_data', $response, HOUR_IN_SECONDS * 6);
				set_transient('wpjobportal_addon_update_temp_data_plugins', $response, 15);
			}else{
				$response = $response_version_data;
			}
			if ( $response) {
				if(is_object($response) ){
					if(isset($response->addon_response_type) && $response->addon_response_type == 'no_key'){
						foreach ($wpjobportal_update_data->checked AS $wpjobportal_key => $wpjobportal_value) {
							$c_key_array = wpjobportalphplib::wpJP_explode('/', $wpjobportal_key);
							$c_key = $c_key_array[0];
							if(isset($response->addon_version_data->{$c_key})){
								if(version_compare( $response->addon_version_data->{$c_key}, $wpjobportal_value, '>' )){
									$wpjobportal_transient_val = get_transient('wpjobportal_addon_hide_update_notice');
									if($wpjobportal_transient_val === false){
										set_transient('wpjobportal_addon_hide_update_notice', 1, DAY_IN_SECONDS );
									}
									$this->addon_update_data[$wpjobportal_key] = $response->addon_version_data->{$c_key};
								}
							}
						}
					}else{// addon_response_type other than no_key
						foreach ($wpjobportal_update_data->checked AS $wpjobportal_key => $wpjobportal_value) {
							$c_key_array = wpjobportalphplib::wpJP_explode('/', $wpjobportal_key);
							$c_key = $c_key_array[0];
							if(isset($response->addon_update_data) && !empty($response->addon_update_data) && isset( $response->addon_update_data->{$c_key})){
								if(version_compare( $response->addon_update_data->{$c_key}->new_version, $wpjobportal_value, '>' )){
									$wpjobportal_update_data->response[ $wpjobportal_key ] = $response->addon_update_data->{$c_key};
									$this->addon_update_data[$wpjobportal_key] = $response->addon_update_data->{$c_key};
								}
							}elseif(isset($response->addon_version_data->{$c_key})){
								if(version_compare( $response->addon_version_data->{$c_key}, $wpjobportal_value, '>' )){
									$wpjobportal_transient_val = get_transient('wpjobportal_addon_hide_update_expired_key_notice');
									if($wpjobportal_transient_val === false){
										set_transient('wpjobportal_addon_hide_update_expired_key_notice', 1, DAY_IN_SECONDS );
									}
									$this->addon_update_data_errors[$wpjobportal_key] = $response->addon_version_data->{$c_key};
									$this->addon_update_data[$wpjobportal_key] = $response->addon_version_data->{$c_key};
								}
							}else{ // set latest version from cdn data
								if ( $wpjobportal_cdnversiondata) {
									if(is_object($wpjobportal_cdnversiondata) ){
										$c_key_plain = wpjobportalphplib::wpJP_str_replace("-","",$c_key);
										$wpjobportal_newversion = $this->getVersionFromLiveData($wpjobportal_cdnversiondata, $c_key_plain);
										if($wpjobportal_newversion){
											if(version_compare( $wpjobportal_newversion, $wpjobportal_value, '>' )){

												$wpjobportal_option_name = 'transaction_key_for_'.$c_key;
												$wpjobportal_transaction_key = wpjobportal::$_common->getTranskey($wpjobportal_option_name);
												$wpjobportal_addon_json_array = array();
												$wpjobportal_addon_json_array[] = wpjobportalphplib::wpJP_str_replace('wp-job-portal-', '', $c_key);
												$wpjobportal_url = 'https://wpjobportal.com/setup/index.php?token='.$wpjobportal_transaction_key.'&productcode='. wp_json_encode($wpjobportal_addon_json_array).'&domain='. site_url();

												// prepping data for seamless update of allowed addons
												$plugin = new stdClass();
												$plugin->id = 'w.org/plugins/wp-job-portal';
												$wpjobportal_addon_slug = $c_key;
												$plugin->name = $wpjobportal_addon_slug;
												$plugin->plugin = $wpjobportal_addon_slug.'/'.$wpjobportal_addon_slug.'.php';
												$plugin->slug = $wpjobportal_addon_slug;
												$plugin->version = '1.0.0';
												$wpjobportal_addonwithoutslash = wpjobportalphplib::wpJP_str_replace('-', '', $wpjobportal_addon_slug);
												$plugin->new_version = $wpjobportal_newversion;
												$plugin->url = 'https://www.wpjobportal.com/';
												$plugin->download_url = $wpjobportal_url;
												$plugin->package = $wpjobportal_url;
												$plugin->trunk = $wpjobportal_url;
												
												$wpjobportal_update_data->response[ $wpjobportal_key ] = $plugin;
												$this->addon_update_data[$wpjobportal_key] = $plugin;
											}
										}

									}
								}
							}
						}
					}
				}
			}
		}// new version found	
		if(isset($wpjobportal_update_data->checked)){
			$this->addon_installed_version_data = $wpjobportal_update_data->checked;
		}
		return $wpjobportal_update_data;
	}

	public function jsPluginsAPI( $false, $wpjobportal_action, $wpjobportal_args ) {
		if (!isset( $wpjobportal_args->slug )) {
			return false;
		}

		if(wpjobportalphplib::wpJP_strstr($wpjobportal_args->slug, 'wp-job-portal-')){
			$response = $this->jsGetPluginInfo($wpjobportal_args->slug);
			if ($response) {
				$response->sections = json_decode(wp_json_encode($response->sections),true);
				$response->banners = json_decode(wp_json_encode($response->banners),true);
				$response->contributors = json_decode(wp_json_encode($response->contributors),true);
				return $response;
			}
		}else{
			return false;// to handle the case of plugins that need to check version data from wordpress repositry.
		}
	}

	public function jsGetPluginInfo($wpjobportal_addon_slug) {
		$wpjobportal_option_name = 'transaction_key_for_'.$wpjobportal_addon_slug;
		$wpjobportal_transaction_key = wpjobportal::$_common->getTranskey($wpjobportal_option_name);

		if(!$wpjobportal_transaction_key){
			die('transient');
			return false;
		}

		// $wpjobportal_plugin_file_path = content_url().'/plugins/'.$wpjobportal_addon_slug.'/'.$wpjobportal_addon_slug.'.php';
		// $wpjobportal_plugin_file_path = plugins_url($wpjobportal_addon_slug . '/' . $wpjobportal_addon_slug . '.php');
		$wpjobportal_plugin_file_path = WP_PLUGIN_DIR."/".$wpjobportal_addon_slug.'/'.$wpjobportal_addon_slug.'.php';
		$wpjobportal_plugin_data = get_plugin_data($wpjobportal_plugin_file_path);

		$response = wpjobportalServerCalls::wpjobportalPluginInformation( array(
			'plugin_slug'    => $wpjobportal_addon_slug,
			'version'        => $wpjobportal_plugin_data['Version'],
			'token'    => $wpjobportal_transaction_key,
			'domain'          => site_url()
		) );
		if ( isset( $response->errors ) ) {
			$this->handle_errors( $response->errors );
		}

		// If everything is okay return the $response
		if ( isset( $response ) && is_object( $response ) && $response !== false ) {
			return $response;
		}

		return false;
	}

	// does changes according to admin triggers.
	private function jsCheckTriggers() {
		$wpjobportal_addon_array_for_token = WPJOBPORTALrequest::getVar('wpjobportal_addon_array_for_token','post');
		if ( isset($wpjobportal_addon_array_for_token) && ! empty($wpjobportal_addon_array_for_token)){
			$wpjobportal_transaction_key = '';
			$wpjobportal_addon_name = '';
			foreach ($wpjobportal_addon_array_for_token as $wpjobportal_key => $wpjobportal_value) {
				$wpjobportal_addon_transaction_key = WPJOBPORTALrequest::getVar($wpjobportal_value.'_transaction_key');
				if(isset($wpjobportal_addon_transaction_key) && $wpjobportal_addon_transaction_key != ''){
					$wpjobportal_transaction_key = sanitize_text_field($wpjobportal_addon_transaction_key);
					$wpjobportal_addon_name = sanitize_text_field($wpjobportal_value);
					break;
				}
			}

			if($wpjobportal_transaction_key != ''){
				$wpjobportal_token = $this->wpjobportalGetTokenFromTransactionKey( $wpjobportal_transaction_key,$wpjobportal_addon_name);
				if($wpjobportal_token){
					foreach ($wpjobportal_addon_array_for_token as $wpjobportal_key => $wpjobportal_value) {
						update_option('transaction_key_for_'.$wpjobportal_value,$wpjobportal_token);
					}
				}else{
					update_option( 'wpjobportal-addon-key-error-message', esc_html(__('Something went wrong','wp-job-portal')));
				}
			}
		}else{
			foreach ($this->addon_installed_array as $wpjobportal_key) {
				$wpjobportal_dismiss_wpjobportal_addon_update_notice = WPJOBPORTALrequest::getVar('dismiss-wpjobportal-addon-update-notice-'.$wpjobportal_key,'get');
				if ( ! empty( $wpjobportal_dismiss_wpjobportal_addon_update_notice ) ) {
					set_transient('dismiss-wpjobportal-addon-update-notice-'.$wpjobportal_key, 1, DAY_IN_SECONDS );
				}
			}
		}
	}

	public function jsCheckUpdateNotice( ) {
		include_once( 'views/html-update-availble.php' );
		// if ( sizeof( $this->errors ) === 0 && ! get_option( $this->plugin_slug . '_hide_update_notice' ) ) {
		// }
	}

	public function getPluginVersionData() {
			$response = wpjobportalServerCalls::wpjobportalPluginUpdateCheck($this->api_key);
			if ( isset( $response->errors ) ) {
				$this->jsHandleErrors( $response->errors );
			}

			// Set version variables
			if ( isset( $response ) && is_object( $response ) && $response !== false ) {
				return $response;
			}
		return false;
	}

	public function getPluginVersionDataFromCDN() {
			$response = wpjobportalServerCalls::wpjobportalPluginUpdateCheckFromCDN();
			if ( isset( $response->errors ) ) {
				$this->jsHandleErrors( $response->errors );
			}

			// Set version variables
			if ( isset( $response ) && is_object( $response ) && $response !== false ) {
				return $response;
			}
		return false;
	}


	private function getVersionFromLiveData($wpjobportal_data, $wpjobportal_addon_name){
		foreach ($wpjobportal_data as $wpjobportal_key => $wpjobportal_value) {
			if($wpjobportal_key == $wpjobportal_addon_name){
				return $wpjobportal_value;
			}
		}
		return;
	}

	public function getPluginLatestVersionData() {
		$response = wpjobportalServerCalls::wpjobportalGetLatestVersions();
		// Set version variables
		if ( isset( $response ) && is_array( $response ) && $response !== false ) {
			return $response;
		}
		return false;
	}

	public function wpjobportalGetTokenFromTransactionKey($wpjobportal_transaction_key,$wpjobportal_addon_name) {
		$response = wpjobportalServerCalls::wpjobportalGenerateToken($wpjobportal_transaction_key,$wpjobportal_addon_name);
		// Set version variables
		if (is_array($response) && isset($response['verfication_status']) && $response['verfication_status'] == 1 ) {
			return $response['token'];
		}else{
			$wpjobportal_error_message = esc_html(__('Something went wrong','wp-job-portal'));
			if(is_array($response) && isset($response['error'])){
				$wpjobportal_error_message = $response['error'];
			}
			update_option( 'wpjobportal-addon-key-error-message', $wpjobportal_error_message );
		}
		return false;
	}
}
?>
