<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALwpjobportalController {

    private $_msgkey;

    function __construct() {

        self::handleRequest();
        $this->_msgkey = WPJOBPORTALincluder::getJSModel('wpjobportal')->getMessagekey();
    }

    function handleRequest() {
        $wpjobportal_layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'controlpanel');
        if (self::canaddfile($wpjobportal_layout)) {
            switch ($wpjobportal_layout) {
                case 'admin_controlpanel':
                    include_once WPJOBPORTAL_PLUGIN_PATH . 'includes/updates/updates.php';
                    WPJOBPORTALupdates::checkUpdates();
                    WPJOBPORTALincluder::getJSModel('wpjobportal')->getAdminControlPanelData();
                    break;
                case 'admin_wpjobportalstats':
                    WPJOBPORTALincluder::getJSModel('wpjobportal')->getwpjobportalStats();
                    break;
                case 'login':
                    if(WPJOBPORTALincluder::getObjectClass('user')->isguest()){
                        $wpjobportal_url = WPJOBPORTALrequest::getVar('wpjobportalredirecturl');
                        if(isset($wpjobportal_url)){
                            wpjobportal::$wpjobportal_data[0]['redirect_url'] = wpjobportalphplib::wpJP_safe_decoding($wpjobportal_url);
                        }else{
                            wpjobportal::$wpjobportal_data[0]['redirect_url'] = home_url();
                        }
                    }else{
                        $finalurl = wp_logout_url(home_url());
                        if(WPJOBPORTALrequest::hasCookie('wpjobportal-socialmedia') && !empty(WPJOBPORTALrequest::cookie('wpjobportal-socialmedia'))){
                            $finalurl = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'sociallogin', 'task'=>'socialogout', 'action'=>'wpjobportaltask',  'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        }
                        wpjobportal::$_error_flag = true;
                        if(class_exists('job_manager_Messages')){
                            job_manager_Messages::alreadyLoggedIn($finalurl);
                        }elseif(class_exists('job_hub_Messages')){
                            job_hub_Messages::alreadyLoggedIn($finalurl);
                        }else{
                            WPJOBPORTALLayout::getUserAlreadyLoggedin($finalurl);
                        }
                    }
                    break;
                case 'admin_licences':
                    $this->wpjobportal_handle_licence_form();
                    break;
                case 'admin_shortcodes': // to avoid default case
                case 'admin_translations': // to avoid default case
                case 'admin_help': // to avoid default case
                case 'admin_pageseo': // to avoid default case

                case 'admin_actionhooks': // to avoid default case
                    break;

                default:
                    return;
            }
            $wpjobportal_module = (wpjobportal::$_common->wpjp_isadmin()) ? 'page' : 'wpjobportalme';
            $wpjobportal_module = WPJOBPORTALrequest::getVar($wpjobportal_module, null, 'wpjobportal');
            $wpjobportal_module = wpjobportalphplib::wpJP_str_replace('wpjobportal_', '', $wpjobportal_module);
            if($wpjobportal_layout=="thankyou"){
                if($wpjobportal_module=="" || $wpjobportal_module!="wpjobportal") $wpjobportal_module="wpjobportal";
            }
            wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
            WPJOBPORTALincluder::include_file($wpjobportal_layout, $wpjobportal_module);
        }
    }

    function canaddfile($wpjobportal_layout) {
        $wpjobportal_nonce_value = WPJOBPORTALrequest::getVar('wpjobportal_nonce');
        if ( wp_verify_nonce( $wpjobportal_nonce_value, 'wpjobportal_nonce') ) {
            if (WPJOBPORTALrequest::getVar('form_request', 'post', '') == 'wpjobportal')
                return false;
            elseif (WPJOBPORTALrequest::getVar('action', 'get', '') == 'wpjobportaltask')
                return false;
            else{
                if(!is_admin() && strpos($wpjobportal_layout, 'admin_') === 0){
                    return false;
                }
                return true;
            }
        }
    }

    function saveordering(){
        $post = WPJOBPORTALrequest::get('post');
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;

        WPJOBPORTALincluder::getJSModel('wpjobportal')->storeOrderingFromPage($post);
        if($post['ordering_for'] == 'fieldordering'){
            $wpjobportal_fieldfor = WPJOBPORTALrequest::getVar('fieldfor');
            if($wpjobportal_fieldfor == ''){
                $wpjobportal_fieldfor = wpjobportal::$wpjobportal_data['fieldfor'];
            }
            $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_fieldordering&wpjobportallt=fieldsordering&ff=".esc_attr($wpjobportal_fieldfor)));
        }
        wp_safe_redirect($wpjobportal_url);
        exit;
    }

    function savedocumenttitleoptions() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_document_title_nonce') ) {
             die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $wpjobportal_data = WPJOBPORTALrequest::get('post');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('wpjobportal')->saveDocumentTitleOptions($wpjobportal_data);
        // echo var_dump($wpjobportal_result);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, "wpjobportal");
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal&wpjobportallt=pageseo"));
        wp_safe_redirect($wpjobportal_url);
        die();
    }

    function savescreenoptions() {
        // 1. Verify the security nonce
        $wpjobportal_nonce = (string) WPJOBPORTALrequest::getVar('wjp_dashboard_nonce', 'post', '');
        if ($wpjobportal_nonce === '' || !wp_verify_nonce($wpjobportal_nonce, 'wjp_dashboard_options_nonce')) {
            wp_die(esc_html__('Security check failed.', 'wp-job-portal'));
        }

        // 2. Check if the user has permission to save options
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'wp-job-portal'));
        }

        $wpjobportal_option_name = 'wjp_dashboard_screen_options';

        // 3. Define the default state for the dashboard sections
        $wpjobportal_wjp_dashboard_defaults = [
            'quick_actions' => 'on',
            'platform_growth' => 'on',
            'quick_stats' => 'on',
            'recent_jobs' => 'on',
            'jobs_by_status_chart' => 'on',
            'top_categories_chart' => 'on',
            'latest_job_applies' => 'on',
            'latest_resumes' => 'on',
            'latest_subscriptions' => 'on',
            'latest_payments' => 'on',
            'latest_activity' => 'on',
            'system_error_log' => 'on',
            'latest_job_seekers' => 'on',
            'latest_employers' => 'on',
        ];

        // 4. Handle the "Reset Defaults" action
        if (WPJOBPORTALrequest::has('wjp_reset_options', 'post')) {
            update_option($wpjobportal_option_name, $wpjobportal_wjp_dashboard_defaults);
        } else {
            // 5. Sanitize and save the submitted options
            $wpjobportal_submitted_options = (array) WPJOBPORTALrequest::getVar('wjp_screen_options', 'post', array());
            $wpjobportal_sanitized_options = [];

            // Loop through the defaults to ensure only valid keys are saved
            foreach (array_keys($wpjobportal_wjp_dashboard_defaults) as $wpjobportal_key) {
                if (isset($wpjobportal_submitted_options[$wpjobportal_key])) {
                    $wpjobportal_sanitized_options[$wpjobportal_key] = 'on'; // Save a consistent value
                }
            }
            update_option($wpjobportal_option_name, $wpjobportal_sanitized_options);
        }

        // 6. Redirect the user back to the dashboard
        $wpjobportal_url = admin_url('admin.php?page=wpjobportal');
        wp_safe_redirect($wpjobportal_url);
        exit;
    }



    /**
     * Save the licence key, re-check it, or release this site.
     *
     * Talks to the licence server and stores what it says, and installs, updates
     * or activates the products the licence covers. The file work is done by
     * WordPress's own upgrader through WPJOBPORTAL_Addon_Installer, and each of
     * those actions asks for the capability WordPress itself would ask for.
     *
     * @return void
     */
    private function wpjobportal_handle_licence_form() {
        if (!WPJOBPORTALrequest::has('wpjobportal_licence_action', 'post')) {
            return;
        }

        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You are not allowed to change licence settings.', 'wp-job-portal'));
        }

        if (!WPJOBPORTALrequest::has('wpjobportal_licence_nonce', 'post')
            || !wp_verify_nonce(sanitize_text_field((string) WPJOBPORTALrequest::getVar('wpjobportal_licence_nonce', 'post', '')), 'wpjobportal_save_licence')
        ) {
            wp_die(esc_html__('That request could not be verified. Please try again.', 'wp-job-portal'));
        }

        $wpjobportal_action = sanitize_key((string) WPJOBPORTALrequest::getVar('wpjobportal_licence_action', 'post', ''));
        $wpjobportal_licence = new WPJOBPORTAL_Licence_Client();

        switch ($wpjobportal_action) {
            case 'save':
                // The constant wins over anything stored, so writing one here
                // would leave the screen saying a key was saved while the site
                // went on using a different one.
                if (WPJOBPORTAL_Licence_Client::key_is_locked()) {
                    set_transient('wpjobportal_licence_notice', array(
                        'type' => 'warning',
                        'text' => __('This key is set in wp-config.php and cannot be changed from this screen.', 'wp-job-portal'),
                    ), 60);
                    break;
                }

                if (!WPJOBPORTALrequest::has('wpjobportal_licence_key', 'post')) {
                    break;
                }
                $wpjobportal_new_key = sanitize_text_field((string) WPJOBPORTALrequest::getVar('wpjobportal_licence_key', 'post', ''));

                // The field is rendered empty on purpose — the key is masked
                // rather than printed back — so an empty submission means
                // "leave it alone", not "delete my licence key".
                if ($wpjobportal_new_key === '') {
                    $wpjobportal_licence->refresh(true);
                    break;
                }

                $wpjobportal_old_key = WPJOBPORTAL_Licence_Client::key();

                if ($wpjobportal_new_key === $wpjobportal_old_key) {
                    $wpjobportal_licence->refresh(true);
                    break;
                }

                // Releasing the old key before overwriting it is the only chance
                // to give its activation slot back: once the key is gone the
                // server has no way to know which slot this site was holding.
                //
                // Forced, because the stored state is being replaced by the new
                // key either way. Keeping the old one on a failed call would
                // leave this site claiming a licence it no longer has.
                if ($wpjobportal_old_key !== '') {
                    $wpjobportal_licence->deactivate(true);
                }

                update_option(WPJOBPORTAL_Licence_Client::KEY_OPTION, $wpjobportal_new_key, false);
                $wpjobportal_licence->activate($wpjobportal_new_key);
                break;

            case 'activate':
                // The only way back from a deactivation. refresh() will not do
                // it, on purpose: releasing a site is a decision, and undoing it
                // has to be one too.
                $wpjobportal_licence->activate();
                break;

            case 'deactivate':
                $wpjobportal_released = $wpjobportal_licence->deactivate();

                // Not forced: if the server did not confirm, the slot is still
                // held and the administrator needs to know rather than be shown
                // a screen that looks successfully deactivated.
                if (empty($wpjobportal_released['ok'])) {
                    set_transient('wpjobportal_licence_notice', array(
                        'type' => 'error',
                        'text' => $wpjobportal_released['message'] !== ''
                            ? $wpjobportal_released['message']
                            : __('This site could not be released: the licence server did not answer. Nothing has changed. Try again shortly.', 'wp-job-portal'),
                    ), 60);
                }
                break;

            case 'install':
            case 'install_all':
            case 'update':
            case 'activate_addon':
                $this->wpjobportal_handle_addon_action($wpjobportal_action);
                break;

            case 'refresh':
            default:
                $wpjobportal_licence->refresh(true);
                break;
        }

        // Whichever way it went, what this site is entitled to may have changed,
        // so the cached update manifest is no longer trustworthy.
        if (class_exists('WPJOBPORTAL_Update_Service')) {
            WPJOBPORTAL_Update_Service::forget();
        }
    }

    /**
     * Install, update or activate products from the Licences screen.
     *
     * @param string $wpjobportal_action install, install_all, update or activate_addon.
     * @return void
     */
    private function wpjobportal_handle_addon_action($wpjobportal_action) {
        $wpjobportal_caps = array(
            'install' => array('install_plugins', 'activate_plugins'),
            'install_all' => array('install_plugins', 'activate_plugins'),
            'update' => array('update_plugins'),
            'activate_addon' => array('activate_plugins'),
        );

        // install_plugins and update_plugins are also false when the site sets
        // DISALLOW_FILE_MODS, so a locked-down site is refused here too.
        foreach ($wpjobportal_caps[$wpjobportal_action] as $wpjobportal_cap) {
            if (!current_user_can($wpjobportal_cap)) {
                set_transient('wpjobportal_licence_notice', array(
                    'type' => 'error',
                    'text' => __('Your account is not allowed to install or update plugins on this site.', 'wp-job-portal'),
                ), 60);
                return;
            }
        }

        $wpjobportal_installer = new WPJOBPORTAL_Addon_Installer();
        $wpjobportal_folder = sanitize_key((string) WPJOBPORTALrequest::getVar('wpjobportal_addon', 'post', ''));
        $wpjobportal_results = array();

        if ($wpjobportal_action === 'install_all') {
            $wpjobportal_state = WPJOBPORTAL_Licence_Client::state();
            $wpjobportal_entitled = isset($wpjobportal_state['plugins']) ? (array) $wpjobportal_state['plugins'] : array();

            foreach (WPJOBPORTAL_Addon_Installer::catalogue() as $wpjobportal_slug => $wpjobportal_product) {
                if (!empty($wpjobportal_product['legacy']) || !in_array($wpjobportal_slug, $wpjobportal_entitled, true)) {
                    continue;
                }
                if (WPJOBPORTAL_Addon_Installer::installed_file($wpjobportal_slug) !== '') {
                    continue;
                }
                $wpjobportal_results[] = $wpjobportal_installer->install($wpjobportal_slug);
            }

            if (empty($wpjobportal_results)) {
                $wpjobportal_results[] = array('ok' => true, 'message' => __('Everything your licence covers is already installed.', 'wp-job-portal'));
            }
        } elseif ($wpjobportal_action === 'install') {
            $wpjobportal_results[] = $wpjobportal_installer->install($wpjobportal_folder);
        } elseif ($wpjobportal_action === 'update') {
            $wpjobportal_results[] = $wpjobportal_installer->update($wpjobportal_folder);
        } else {
            $wpjobportal_results[] = $wpjobportal_installer->activate($wpjobportal_folder);
        }

        $wpjobportal_failed = count(array_filter($wpjobportal_results, function ($wpjobportal_result) {
            return empty($wpjobportal_result['ok']);
        }));

        set_transient('wpjobportal_licence_notice', array(
            'type' => $wpjobportal_failed === 0 ? 'success' : ($wpjobportal_failed < count($wpjobportal_results) ? 'warning' : 'error'),
            'text' => wp_list_pluck($wpjobportal_results, 'message'),
        ), 60);
    }

}

$WPJOBPORTALwpjobportalController = new WPJOBPORTALwpjobportalController();
