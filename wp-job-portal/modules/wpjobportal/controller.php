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
                        if(isset($_COOKIE['wpjobportal-socialmedia']) && !empty($_COOKIE['wpjobportal-socialmedia'])){
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
                case 'admin_addonstatus': // to avoid default case
                    WPJOBPORTALincluder::getJSModel('wpjobportal')->wpjobportalCheckLicenseStatus();
                    break;
                case 'admin_shortcodes': // to avoid default case
                case 'admin_help': // to avoid default case
                case 'admin_pageseo': // to avoid default case
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
            if (isset($_POST['form_request']) && $_POST['form_request'] == 'wpjobportal')
                return false;
            elseif (isset($_GET['action']) && $_GET['action'] == 'wpjobportaltask')
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
        wp_redirect($wpjobportal_url);
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
        wp_redirect($wpjobportal_url);
        die();
    }

    function savescreenoptions() {
        // 1. Verify the security nonce
        if (!isset($_POST['wjp_dashboard_nonce']) || !wp_verify_nonce($_POST['wjp_dashboard_nonce'], 'wjp_dashboard_options_nonce')) {
            wp_die('Security check failed.');
        }

        // 2. Check if the user has permission to save options
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to perform this action.');
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
        if (isset($_POST['wjp_reset_options'])) {
            update_option($wpjobportal_option_name, $wpjobportal_wjp_dashboard_defaults);
        } else {
            // 5. Sanitize and save the submitted options
            $wpjobportal_submitted_options = isset($_POST['wjp_screen_options']) ? (array) $_POST['wjp_screen_options'] : [];
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


}

$WPJOBPORTALwpjobportalController = new WPJOBPORTALwpjobportalController();
?>
