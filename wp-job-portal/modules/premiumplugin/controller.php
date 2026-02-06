<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php

class WPJOBPORTALpremiumpluginController {

    private $_msgkey;

    function __construct() {
        self::handleRequest();
        $this->_msgkey = WPJOBPORTALincluder::getJSModel('premiumplugin')->getMessagekey();
    }

    function handleRequest() {
        $wpjobportal_module = "premiumplugin";
        if ($this->canAddLayout()) {
            $wpjobportal_layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'step1');
            WPJOBPORTALincluder::getJSModel('wpjobportal')->wpjobportalCheckLicenseStatus();
            switch ($wpjobportal_layout) {
                case 'admin_step1':
                    wpjobportal::$_data['versioncode'] = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('versioncode');
                    wpjobportal::$_data['productcode'] = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('productcode');
                    wpjobportal::$_data['producttype'] = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('producttype');
                break;
                case 'admin_addonfeatures';// to avoid default case
                    break;
                case 'admin_step2':
                    break;
                case 'admin_step3':
                    break;
                case 'admin_updatekey':
                        wpjobportal::$_data['token'] = WPJOBPORTALrequest::getVar('token');
                        wpjobportal::$_data['extra_addons'] = WPJOBPORTALrequest::getVar('extraaddons');
                        wpjobportal::$_data['allowed_addons'] = WPJOBPORTALrequest::getVar('allowedaddons');
                        wpjobportal::$_data['unused_keys'] = WPJOBPORTALincluder::getJSModel('premiumplugin')->wpjobportal_count_unused_keys();
                        break;
                default:
                    exit();    
            }
            $wpjobportal_module =  'premiumplugin';
            wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
            WPJOBPORTALincluder::include_file($wpjobportal_layout, $wpjobportal_module);
        }
    }

    function canAddLayout() {
        $wpjobportal_nonce_value = WPJOBPORTALrequest::getVar('wpjobportal_nonce');
        if ( wp_verify_nonce( $wpjobportal_nonce_value, 'wpjobportal_nonce') ) {
            if (isset($_POST['form_request']) && $_POST['form_request'] == 'wpjobportal')
                return false;
            elseif (isset($_GET['action']) && $_GET['action'] == 'wpjobportaltask')
                return false;
            else
                return true;
        }
    }

    function verifytransactionkey(){

        $post_data['transactionkey'] = WPJOBPORTALrequest::getVar('transactionkey','','');
        if($post_data['transactionkey'] != ''){


            $post_data['domain'] = site_url();
            $post_data['step'] = 'one';
            $post_data['myown'] = 1;

            $wpjobportal_url = 'https://wpjobportal.com/setup/index.php';

            $response = wp_remote_post( $wpjobportal_url, array('body' => $post_data,'timeout'=>7,'sslverify'=>false));
            if( !is_wp_error($response) && $response['response']['code'] == 200 && isset($response['body']) ){
                $wpjobportal_result = $response['body'];
                $wpjobportal_result = json_decode($wpjobportal_result,true);

            }else{
                $wpjobportal_result = false;
                if(!is_wp_error($response)){
                   $error = $response['response']['message'];
               }else{
                    $error = $response->get_error_message();
               }
            }

            if(is_array($wpjobportal_result) && isset($wpjobportal_result['status']) && $wpjobportal_result['status'] == 1 ){ // means everthing ok
                $wpjobportal_installdata = $wpjobportal_result;
                $wpjobportal_installdata['actual_transaction_key'] = $post_data['transactionkey'];
                $wpjobportal_result['actual_transaction_key'] = $post_data['transactionkey'];
                // in case of session not working
                add_option('wpjobportal_addon_install_data',wp_json_encode($wpjobportal_result));
                $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_premiumplugin&wpjobportallt=step2"));
                wp_redirect($wpjobportal_url);
                return;
            }else{
                if(isset($wpjobportal_result[0]) && $wpjobportal_result[0] == 0){
                    $error = $wpjobportal_result[1];
                }elseif(isset($wpjobportal_result['error']) && $wpjobportal_result['error'] != ''){
                    $error = $wpjobportal_result['error'];
                }
            }
        }else{
            $error = esc_html(__('Please insert activation key to proceed','wp-job-portal')).'!';
        }
        $wpjobportal_addon_return_data = array();
        $wpjobportal_addon_return_data['status'] = 0;
        $wpjobportal_addon_return_data['message'] = $error;
        $wpjobportal_addon_return_data['transactionkey'] = $post_data['transactionkey'];
        update_option( 'wpjobportal_addon_return_data', wp_json_encode($wpjobportal_addon_return_data) );
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_premiumplugin&wpjobportallt=step1"));
        wp_redirect($wpjobportal_url);
        return;
    }

    function downloadandinstalladdons(){
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_premiumplugin_nonce') ) {
             die( 'Security check Failed' );
        }
        $post_data = WPJOBPORTALrequest::get('post');

        $wpjobportal_addons_array = $post_data;
        if(isset($wpjobportal_addons_array['token'])){
            unset($wpjobportal_addons_array['token']);
        }
        $wpjobportal_addon_json_array = array();

        foreach ($wpjobportal_addons_array as $wpjobportal_key => $wpjobportal_value) {
            $wpjobportal_addon_json_array[] = wpjobportalphplib::wpJP_str_replace('wp-job-portal-', '', $wpjobportal_key);
        }

        $wpjobportal_token = $post_data['token'];
        if($wpjobportal_token == ''){
            $wpjobportal_addon_return_data = array();
            $wpjobportal_addon_return_data['status'] = 0;
            $wpjobportal_addon_return_data['message'] = esc_html(__('Addon Installation Failed','wp-job-portal')).'!';
            $wpjobportal_addon_return_data['transactionkey'] = '';
            update_option( 'wpjobportal_addon_return_data', wp_json_encode($wpjobportal_addon_return_data) );
            $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_premiumplugin&wpjobportallt=step1"));
            wp_redirect($wpjobportal_url);
            exit;
        }
        $site_url = site_url();
        $site_url = wpjobportalphplib::wpJP_str_replace("https://","",$site_url);
        $site_url = wpjobportalphplib::wpJP_str_replace("http://","",$site_url);
        $wpjobportal_url = 'https://wpjobportal.com/setup/index.php?token='.$wpjobportal_token.'&productcode='. wp_json_encode($wpjobportal_addon_json_array).'&domain='. $site_url;

        $wpjobportal_install_count = 0;

        $wpjobportal_installed = $this->install_plugin($wpjobportal_url);
        if ( !is_wp_error( $wpjobportal_installed ) && $wpjobportal_installed ) {
            // had to run two seprate loops to save token for all the addons even if some error is triggered by activation.
            foreach ($post_data as $wpjobportal_key => $wpjobportal_value) {
                if(wpjobportalphplib::wpJP_strstr($wpjobportal_key, 'wp-job-portal-')){
                    update_option('transaction_key_for_'.$wpjobportal_key,$wpjobportal_token);
                }
            }

            foreach ($post_data as $wpjobportal_key => $wpjobportal_value) {
                if(wpjobportalphplib::wpJP_strstr($wpjobportal_key, 'wp-job-portal-')){
                    $wpjobportal_activate = activate_plugin( $wpjobportal_key.'/'.$wpjobportal_key.'.php' );
                    $wpjobportal_install_count++;
                }
            }

        }else{
            $wpjobportal_addon_return_data = array();
            $wpjobportal_addon_return_data['status'] = 0;
            $wpjobportal_addon_return_data['message'] = esc_html(__('Addon Installation Failed','wp-job-portal')).'!';
            $wpjobportal_addon_return_data['transactionkey'] = '';
            update_option( 'wpjobportal_addon_return_data', wp_json_encode($wpjobportal_addon_return_data) );
            $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_premiumplugin&wpjobportallt=step1"));
            wp_redirect($wpjobportal_url);
            exit;
        }
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_premiumplugin&wpjobportallt=step3"));
        wp_redirect($wpjobportal_url);
    }

    function install_plugin( $wpjobportal_plugin_zip ) {// is only called from a same controler function
        do_action('wpjobportal_load_wp_admin_file');
        WP_Filesystem();
        $tmpfile = download_url( $wpjobportal_plugin_zip);

        if ( !is_wp_error( $tmpfile ) && $tmpfile ) {
            $wpjobportal_plugin_path = WP_CONTENT_DIR;
            $wpjobportal_plugin_path = $wpjobportal_plugin_path.'/plugins/';
            $wpjobportal_path =WPJOBPORTAL_PLUGIN_PATH.'addon.zip';

            copy( $tmpfile, $wpjobportal_path );


            $unzipfile = unzip_file( $wpjobportal_path, $wpjobportal_plugin_path);
            @wp_delete_file( $wpjobportal_path ); // must wp_delete_file afterwards
            @wp_delete_file( $tmpfile ); // must wp_delete_file afterwards

            if ( is_wp_error( $unzipfile ) ) {
                $wpjobportal_addon_return_data = array();
                $wpjobportal_addon_return_data['status'] = 0;
                $wpjobportal_addon_return_data['message'] = esc_html(__('Addon installation failed, Directory permission error','wp-job-portal')).'!';
                $wpjobportal_addon_return_data['transactionkey'] = '';
                update_option( 'wpjobportal_addon_return_data', wp_json_encode($wpjobportal_addon_return_data) );
                $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_premiumplugin&wpjobportallt=step1"));
                wp_redirect($wpjobportal_url);
                exit;
            } else {
                return true;
            }
        }else{
            $wpjobportal_addon_return_data = array();
            $wpjobportal_addon_return_data['status'] = 0;
            $wpjobportal_addon_return_data['message'] = esc_html(__('Addon Installation Failed, File download error','wp-job-portal')).'!';
            $wpjobportal_addon_return_data['transactionkey'] = '';
            update_option( 'wpjobportal_addon_return_data', wp_json_encode($wpjobportal_addon_return_data) );
            $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_premiumplugin&wpjobportallt=step1"));
            wp_redirect($wpjobportal_url);
            exit;
        }
    }


    function wpjobportalupdatetransactionkey() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($wpjobportal_nonce, 'wpjobportal_premiumplugin_nonce')) {
            die( 'Security check Failed' );
        }

        $post_data   = WPJOBPORTALrequest::get('post');
        $wpjobportal_addons      = $post_data;
        $wpjobportal_addon_names = [];

        // Remove transaction key from addon array
        if (isset($wpjobportal_addons['transactionkey'])) {
            unset($wpjobportal_addons['transactionkey']);
        }

        foreach ($wpjobportal_addons as $wpjobportal_key => $wpjobportal_value) {
            $wpjobportal_addon_names[] = wpjobportalphplib::wpJP_str_replace('wp-job-portal-', '', $wpjobportal_key);
        }

        if (empty($wpjobportal_addon_names)) {
            WPJOBPORTALMessages::setLayoutMessage(__('Please select at least one addon!', 'wp-job-portal'), 'error',$this->_msgkey);
            wp_redirect(admin_url("admin.php?page=wpjobportal_premiumplugin&wpjobportallt=updatekey"));
            exit;
        }

        $wpjobportal_token = isset($post_data['transactionkey']) ? sanitize_text_field($post_data['transactionkey']) : '';
        if (empty($wpjobportal_token)) {
            WPJOBPORTALMessages::setLayoutMessage(__('Please insert activation key to proceed', 'wp-job-portal'), 'error',$this->_msgkey);
            wp_redirect(admin_url("admin.php?page=wpjobportal_premiumplugin&wpjobportallt=updatekey"));
            exit;
        }

        // Normalize domain
        $site_url = site_url();
        $site_url = wpjobportalphplib::wpJP_str_replace(['https://','http://'], '', $site_url);

        // Prepare request
        $wpjobportal_request_body = [
            'transactionkey' => $wpjobportal_token,
            'domain'         => $site_url,
            'step'           => 'one',
            'myown'          => 1,
        ];

        $wpjobportal_url      = 'https://wpjobportal.com/setup/index.php';
        $response = wp_remote_post($wpjobportal_url, ['body' => $wpjobportal_request_body, 'timeout' => 15, 'sslverify' => false]);

        if (is_wp_error($response)) {
            WPJOBPORTALMessages::setLayoutMessage($response->get_error_message(), 'error',$this->_msgkey);
            wp_redirect(admin_url("admin.php?page=wpjobportal_premiumplugin&wpjobportallt=updatekey"));
            exit;
        }

        $wpjobportal_result = json_decode(wp_remote_retrieve_body($response), true);
        if (!is_array($wpjobportal_result) || empty($wpjobportal_result['status'])) {
            $error = __('Invalid server response', 'wp-job-portal');
            if(!empty($wpjobportal_result[1])){
                $error = $wpjobportal_result[1];
            }
            WPJOBPORTALMessages::setLayoutMessage($error, 'error',$this->_msgkey);
            wp_redirect(admin_url("admin.php?page=wpjobportal_premiumplugin&wpjobportallt=updatekey"));
            exit;
        }

        if ($wpjobportal_result['status'] != 1) {
            $error = isset($wpjobportal_result['error']) ? $wpjobportal_result['error'] : __('Activation failed', 'wp-job-portal');
            WPJOBPORTALMessages::setLayoutMessage(esc_html($error), 'error',$this->_msgkey);
            wp_redirect(admin_url("admin.php?page=wpjobportal_premiumplugin&wpjobportallt=updatekey"));
            exit;
        }

        // modifed transaction key to be used from this poitn forward
        if(!empty($wpjobportal_result['token'])){
            $wpjobportal_token = $wpjobportal_result['token'];
        }

        // Store valid key for each addon
        foreach ($wpjobportal_addons as $wpjobportal_key => $wpjobportal_value) {
            if (strpos($wpjobportal_key, 'wp-job-portal-') !== false) {
                update_option('transaction_key_for_' . $wpjobportal_key, $wpjobportal_token, false);
            }
        }

        // Optional: save expiry status like wpjobportalCheckLicenseStatus
        $wpjobportal_status_prefix = 'key_status_for_wp-job-portal_';
        if (!empty($wpjobportal_result['expirydate'])) {
            $wpjobportal_expiry = strtotime($wpjobportal_result['expirydate']);
            if ($wpjobportal_expiry && strtotime(current_time('mysql')) > $wpjobportal_expiry) {
                update_option($wpjobportal_status_prefix . $wpjobportal_token, 0, false); // expired
            } else {
                update_option($wpjobportal_status_prefix . $wpjobportal_token, 1, false); // valid
            }
        }

        WPJOBPORTALMessages::setLayoutMessage(__('Addon(s) Installed successfully!', 'wp-job-portal'), 'updated',$this->_msgkey);
        wp_redirect(admin_url("admin.php?page=wpjobportal_premiumplugin&wpjobportallt=updatekey"));
        exit;
    }


    function wpjobportal_remove_unused_keys() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if ( ! wp_verify_nonce( $wpjobportal_nonce, 'delete-transaction-key' ) ) {
            die( 'Security check Failed' );
        }
        $wpjobportal_deleted = WPJOBPORTALincluder::getJSModel('premiumplugin')->wpjobportalRemoveUnusedKeys();

        if ($wpjobportal_deleted === 0) {
            WPJOBPORTALMessages::setLayoutMessage(esc_html(__('No unused keys were found.', 'wp-job-portal')),'error',$this->_msgkey);
        } elseif ($wpjobportal_deleted === 1) {
            WPJOBPORTALMessages::setLayoutMessage(esc_html($wpjobportal_deleted . ' ' . __('unused key has been deleted successfully!', 'wp-job-portal')),'updated',$this->_msgkey);
        } else {
            WPJOBPORTALMessages::setLayoutMessage(esc_html($wpjobportal_deleted . ' ' . __('unused keys have been deleted successfully!', 'wp-job-portal')),'updated',$this->_msgkey);
        }

        $wpjobportal_url = admin_url("admin.php?page=wpjobportal_premiumplugin&wpjobportallt=updatekey");
        wp_redirect($wpjobportal_url);
        exit;
    }


}
$WPJOBPORTALpremiumpluginController = new WPJOBPORTALpremiumpluginController();
?>