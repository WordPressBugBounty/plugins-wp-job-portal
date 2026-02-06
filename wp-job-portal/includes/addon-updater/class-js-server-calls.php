<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class wpjobportalServerCalls extends WPJOBPORTAL_JOBPORTALUpdater{

	private static $wpjobportal_server_url = 'https://wpjobportal.com/setup/index.php';

	public static function wpjobportalPluginUpdateCheck($wpjobportal_token_arrray_json) {
		$wpjobportal_args = array(
			'request' => 'pluginupdatecheck',
			'token' => $wpjobportal_token_arrray_json,
			'domain' => site_url()
		);

		$wpjobportal_url = self::$wpjobportal_server_url . '?' . http_build_query( $wpjobportal_args, '', '&' );
		$wpjobportal_request = wp_remote_get($wpjobportal_url);

		if ( is_wp_error( $wpjobportal_request ) || wp_remote_retrieve_response_code( $wpjobportal_request ) != 200 ) {
			$wpjobportal_error_message = 'pluginupdatecheck case returned error';
			WPJOBPORTALincluder::getJSModel('systemerror')->addSystemError($wpjobportal_error_message);
			return false;
		}

		$response = wp_remote_retrieve_body( $wpjobportal_request );
		$response = json_decode($response);

		if ( is_object( $response ) ) {
			return $response;
		} else {
			$wpjobportal_error_message = 'pluginupdatecheck case returned data which was not correct';
			WPJOBPORTALincluder::getJSModel('systemerror')->addSystemError($wpjobportal_error_message);
			return false;
		}
	}

	public static function wpjobportalPluginUpdateCheckFromCDN() {

		$wpjobportal_url = "https://d37svg59fxemmm.cloudfront.net/addonslatestversions.txt";
		$wpjobportal_request = wp_remote_get($wpjobportal_url);

		if ( is_wp_error( $wpjobportal_request ) || wp_remote_retrieve_response_code( $wpjobportal_request ) != 200 ) {
			$wpjobportal_error_message = 'pluginupdatecheck cdn case returned error';
			WPJOBPORTALincluder::getJSModel('systemerror')->addSystemError($wpjobportal_error_message);
			return false;
		}

		$response = wp_remote_retrieve_body( $wpjobportal_request );
		$response = json_decode($response);

		if ( is_object( $response ) ) {
			return $response;
		} else {
			$wpjobportal_error_message = 'pluginupdatecheck cdn case returned data which was not correct';
			WPJOBPORTALincluder::getJSModel('systemerror')->addSystemError($wpjobportal_error_message);
			return false;
		}
	}

	public static function wpjobportalGenerateToken($wpjobportal_transaction_key,$wpjobportal_addon_name) {
		$wpjobportal_args = array(
			'request' => 'generatetoken',
			'transactionkey' => $wpjobportal_transaction_key,
			'productcode' => $wpjobportal_addon_name,
			'domain' => site_url()
		);

		$wpjobportal_url = self::$wpjobportal_server_url . '?' . http_build_query( $wpjobportal_args, '', '&' );
		$wpjobportal_request = wp_remote_get($wpjobportal_url);
		if ( is_wp_error( $wpjobportal_request ) || wp_remote_retrieve_response_code( $wpjobportal_request ) != 200 ) {
			$wpjobportal_error_message = 'generatetoken case returned error';
			WPJOBPORTALincluder::getJSModel('systemerror')->addSystemError($wpjobportal_error_message);
			return array('error'=>$wpjobportal_error_message);
		}

		$response = wp_remote_retrieve_body( $wpjobportal_request );
		$response = json_decode($response,true);

		if ( is_array( $response ) ) {
			return $response;
		} else {
			$wpjobportal_error_message = 'generatetoken case returned data which was not correct';
			WPJOBPORTALincluder::getJSModel('systemerror')->addSystemError($wpjobportal_error_message);
			return array('error'=>$wpjobportal_error_message);
		}
		return false;
	}


	public static function wpjobportalGetLatestVersions() {
		$wpjobportal_args = array(
				'request' => 'getlatestversions'
			);
		$wpjobportal_request = wp_remote_get( 'https://wpjobportal.com/appsys/addoninfo/index.php' . '?' . http_build_query( $wpjobportal_args, '', '&' ) );

		if ( is_wp_error( $wpjobportal_request ) || wp_remote_retrieve_response_code( $wpjobportal_request ) != 200 ) {
			$wpjobportal_error_message = 'getlatestversions case returned error';
			WPJOBPORTALincluder::getJSModel('systemerror')->addSystemError($wpjobportal_error_message);
			return false;
		}

		$response = wp_remote_retrieve_body( $wpjobportal_request );
		// $response = array();
		// $response['js-support-ticket-agent'] = '1.1.0';
		// $response['js-support-ticket-actions'] = '1.1.0';
		// $response['js-support-ticket-announcement'] = '1.1.0';
		// $response['js-support-ticket-feedback'] = '1.1.0';

		$response = json_decode($response,true);
		if ( is_array( $response ) ) {
			return $response;
		} else {
			$wpjobportal_error_message = 'getlatestversions case returned data which was not correct';
			WPJOBPORTALincluder::getJSModel('systemerror')->addSystemError($wpjobportal_error_message);
			return false;
		}
	}

	public static function wpjobportalPluginInformation( $wpjobportal_args ) {
		$wpjobportal_defaults = array(
			'request'        => 'plugininformation',
			'plugin_slug'    => '',
			'version'        => '',
			'token'    => '',
			'domain'          => site_url()
		);

		$wpjobportal_args    = wp_parse_args( $wpjobportal_args, $wpjobportal_defaults );
		$wpjobportal_request = wp_remote_get( 'https://wpjobportal.com/appsys/addoninfo/index.php' . '?' . http_build_query( $wpjobportal_args, '', '&' ) );

		if ( is_wp_error( $wpjobportal_request ) || wp_remote_retrieve_response_code( $wpjobportal_request ) != 200 ) {
			$wpjobportal_error_message = 'plugininformation case returned data error';
			WPJOBPORTALincluder::getJSModel('systemerror')->addSystemError($wpjobportal_error_message);
			return false;
		}
		$response = wp_remote_retrieve_body( $wpjobportal_request );

		$response = json_decode($response);

		if ( is_object( $response ) ) {
			return $response;
		} else {
			$wpjobportal_error_message = 'plugininformation case returned data which is not correct';
			WPJOBPORTALincluder::getJSModel('systemerror')->addSystemError($wpjobportal_error_message);
			return false;
		}
	}
}
