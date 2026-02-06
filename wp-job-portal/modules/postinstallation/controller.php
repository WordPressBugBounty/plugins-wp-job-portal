<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALpostinstallationController {

    function __construct() {

        self::handleRequest();
    }

    function handleRequest() {
        $wpjobportal_layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'stepone');
        if($this->canaddfile($wpjobportal_layout)){
            switch ($wpjobportal_layout) {
                case 'admin_stepone':
                    WPJOBPORTALincluder::getJSModel('postinstallation')->getConfigurationValues();
					WPJOBPORTALincluder::getJSModel('postinstallation')->addMissingUsers();
                break;
                case 'admin_steptwo':
                    WPJOBPORTALincluder::getJSModel('postinstallation')->getConfigurationValues();
                break;
                case 'admin_stepthree':
                    WPJOBPORTALincluder::getJSModel('postinstallation')->getConfigurationValues();
                break;
                case 'admin_themedemodata':
                    wpjobportal::$_data['flag'] = WPJOBPORTALrequest::getVar('flag');
                break;
                case 'admin_demoimporter':
                    WPJOBPORTALincluder::getJSModel('postinstallation')->getListOfDemoVersions();
                break;
                case 'admin_quickstart':
                case 'admin_stepfour':
                case 'admin_setupcomplete':
                break;

                default:
                    return;
            }
            $wpjobportal_module = (wpjobportal::$_common->wpjp_isadmin()) ? 'page' : 'wpjobportalme';
            $wpjobportal_module = WPJOBPORTALrequest::getVar($wpjobportal_module, null, 'postinstallation');
            $wpjobportal_module = wpjobportalphplib::wpJP_str_replace('wpjobportal_', '', $wpjobportal_module);
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

    function save(){
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_postinstallation_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_data = WPJOBPORTALrequest::get('post');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('postinstallation')->storeconfigurations($wpjobportal_data);

        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_postinstallation&wpjobportallt=stepone"));
        if($wpjobportal_data['step'] == 1){
            $wpjobportal_multiple_employers =  get_option( "wpjobportal_multiple_employers", 1 );
            if($wpjobportal_multiple_employers == 1){
                $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_postinstallation&wpjobportallt=steptwo"));
            }else{
                $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_postinstallation&wpjobportallt=stepthree"));
            }
        }
        if($wpjobportal_data['step'] == 2){
            $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_postinstallation&wpjobportallt=stepthree"));
        }
        if($wpjobportal_data['step'] == 3){
            $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_postinstallation&wpjobportallt=stepfour"));
        }
        wp_redirect($wpjobportal_url);
        exit();
    }

    function savesampledata(){
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_postinstallation_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_data = WPJOBPORTALrequest::get('post');
        $wpjobportal_sampledata = $wpjobportal_data['sampledata'];
        $wpjobportal_temp_data = 0;
        $wpjobportal_jsmenu = 0;
        $wpjobportal_empmenu = 0;
	   $wpjobportal_job_listing_menu=0;
        if(isset($wpjobportal_data['temp_data'])){
            $wpjobportal_temp_data = 1;
        }
        // notice for undeined variable
        if(isset($wpjobportal_data['jsmenu'])){
            $wpjobportal_jsmenu = $wpjobportal_data['jsmenu'];
        }
        if(isset($wpjobportal_data['empmenu'])){
            $wpjobportal_empmenu = $wpjobportal_data['empmenu'];
        }
        if(isset($wpjobportal_data['job_listing_menu'])){
            $wpjobportal_job_listing_menu = $wpjobportal_data['job_listing_menu'];
        }

        if(wpjobportal::$wpjobportal_theme_chk == 1){
            update_option( 'wpjobportal_jobs_sample_data', 1 ); // flag to messge that jobs data has been inserted.
            $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_postinstallation&wpjobportallt=demoimporter"));
        }else{
            //$wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal"));
            $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_postinstallation&wpjobportallt=setupcomplete"));
        }
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('postinstallation')->installSampleData($wpjobportal_sampledata,$wpjobportal_jsmenu,$wpjobportal_empmenu,$wpjobportal_temp_data, $wpjobportal_job_listing_menu);
        wp_redirect($wpjobportal_url);
        exit();
    }

    function savetemplatesampledata(){
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_postinstallation_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $flag = WPJOBPORTALrequest::getVar('flag');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('postinstallation')->installSampleDataTemplate($flag);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_postinstallation&wpjobportallt=themedemodata&flag=".esc_url($wpjobportal_result)));
        wp_redirect($wpjobportal_url);
        exit();
    }

    function importtemplatesampledata(){
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_postinstallation_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $flag = WPJOBPORTALrequest::getVar('flag','',0);// zero as default value to avoid problems
        if($flag == 'f'){
            $wpjobportal_result = WPJOBPORTALincluder::getJSModel('postinstallation')->importTemplateSampleData($flag);
        }else{
            $wpjobportal_result = 0;
        }
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_postinstallation&wpjobportallt=themedemodata&flag=".esc_url($wpjobportal_result)));
        wp_redirect($wpjobportal_url);
        exit();
    }

    function getdemocode(){
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_postinstallation_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_demoid = WPJOBPORTALrequest::getVar('demoid');
        $foldername = WPJOBPORTALrequest::getVar('foldername');
        $wpjobportal_demo_overwrite = WPJOBPORTALrequest::getVar('demo_overwrite');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('postinstallation')->getDemo($wpjobportal_demoid,$foldername,$wpjobportal_demo_overwrite);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal"));
        wp_redirect($wpjobportal_url);
        exit();
    }

    function importfreetoprotemplatedata(){
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_postinstallation_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        if(wpjobportal::$wpjobportal_theme_chk == 1){// 1 for job manager
            $wpjobportal_result = WPJOBPORTALincluder::getJSModel('postinstallation')->installFreeToProData();
        }else{
            $wpjobportal_result = WPJOBPORTALincluder::getJSModel('postinstallation')->installFreeToProDataJobHub();
        }
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal"));
        wp_redirect($wpjobportal_url);
        exit();
    }

    function installjobportaldemodata(){
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_postinstallation_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('postinstallation')->installSampleDataTemplateJobPortal();
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal"));
        wp_redirect($wpjobportal_url);
        exit();
    }

}
$WPJOBPORTALpostinstallationController = new WPJOBPORTALpostinstallationController();
?>
