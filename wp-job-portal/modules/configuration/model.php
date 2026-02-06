<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALconfigurationModel {

    var $_data_directory = null;
    var $_comp_editor = null;
    var $_job_editor = null;
    var $_defaultcountry = null;
    var $_config = null;

    function __construct() {

    }

    function getConfiguration() {
        do_action('wpjobportal_load_wp_plugin_file');
        // check for plugin using plugin name
        if (is_plugin_active('wp-job-portal/wp-job-portal.php')) {
            $query = "SELECT config.* FROM `" . wpjobportal::$_db->prefix . "wj_portal_config` AS config WHERE configfor = 'default'";
            $wpjobportal_config = wpjobportaldb::get_results($query);
            foreach ($wpjobportal_config as $conf) {
                wpjobportal::$_configuration[$conf->configname] = $conf->configvalue;
            }
            wpjobportal::$_configuration['config_count'] = COUNT($wpjobportal_config);
        }
    }

    function getConfigurationsForForm() {
        $query = "SELECT config.* FROM `" . wpjobportal::$_db->prefix . "wj_portal_config` AS config";
        $wpjobportal_config = wpjobportaldb::get_results($query);
        foreach ($wpjobportal_config as $conf) {
            wpjobportal::$_data[0][$conf->configname] = $conf->configvalue;
        }
        wpjobportal::$_data[0]['config_count'] = COUNT($wpjobportal_config);
        if(in_array('credits',wpjobportal::$_active_addons)){
            // payment method config
            $query = "SELECT * FROM `" . wpjobportal::$_db->prefix . "wj_portal_paymentmethodconfig`";
            $wpjobportal_paymentmethodconfig = wpjobportaldb::get_results($query);
            foreach ($wpjobportal_paymentmethodconfig AS $wpjobportal_configvalue) {
                wpjobportal::$_data[0][$wpjobportal_configvalue->configname] = $wpjobportal_configvalue->configvalue;
            }
        }
    }



    function storeConfig($wpjobportal_data) {
        if (empty($wpjobportal_data))
            return false;
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }

        // if ($wpjobportal_data['isgeneralbuttonsubmit'] == 1) {
        //     if (!isset($wpjobportal_data['employer_share_fb_like']))
        //         $wpjobportal_data['employer_share_fb_like'] = 0;
        //     if (!isset($wpjobportal_data['employer_share_fb_share']))
        //         $wpjobportal_data['employer_share_fb_share'] = 0;
        //     if (!isset($wpjobportal_data['employer_share_fb_comments']))
        //         $wpjobportal_data['employer_share_fb_comments'] = 0;
        //     if (!isset($wpjobportal_data['employer_share_google_like']))
        //         $wpjobportal_data['employer_share_google_like'] = 0;
        //     if (!isset($wpjobportal_data['employer_share_google_share']))
        //         $wpjobportal_data['employer_share_google_share'] = 0;
        //     if (!isset($wpjobportal_data['employer_share_blog_share']))
        //         $wpjobportal_data['employer_share_blog_share'] = 0;
        //     if (!isset($wpjobportal_data['employer_share_friendfeed_share']))
        //         $wpjobportal_data['employer_share_friendfeed_share'] = 0;
        //     if (!isset($wpjobportal_data['employer_share_linkedin_share']))
        //         $wpjobportal_data['employer_share_linkedin_share'] = 0;
        //     if (!isset($wpjobportal_data['employer_share_digg_share']))
        //         $wpjobportal_data['employer_share_digg_share'] = 0;
        //     if (!isset($wpjobportal_data['employer_share_twitter_share']))
        //         $wpjobportal_data['employer_share_twitter_share'] = 0;
        //     if (!isset($wpjobportal_data['employer_share_myspace_share']))
        //         $wpjobportal_data['employer_share_myspace_share'] = 0;
        //     if (!isset($wpjobportal_data['employer_share_yahoo_share']))
        //         $wpjobportal_data['employer_share_yahoo_share'] = 0;

        // }
        $wpjobportal_data = wpjobportal::wpjobportal_sanitizeData($wpjobportal_data);
        $wpjobportal_data['offline_text'] = wpautop(wptexturize(wptexturize(wpjobportalphplib::wpJP_stripslashes(WPJOBPORTALrequest::getVar('offline_text','post','','',1)))));

        $error = false;



        $query = "SELECT configname,configvalue FROM `" . wpjobportal::$_db->prefix . "wj_portal_config` ";
        $wpjobportal_config = wpjobportaldb::get_results($query);
        $current_configs = array();
        foreach ($wpjobportal_config as $conf) {
            $current_configs[$conf->configname] = $conf->configvalue;
        }


        // payment method configs
        $current_payment_configs = array();
        if(in_array('credits',wpjobportal::$_active_addons)){
            // payment method config
            $query = "SELECT * FROM `" . wpjobportal::$_db->prefix . "wj_portal_paymentmethodconfig`";
            $wpjobportal_paymentmethodconfig = wpjobportaldb::get_results($query);

            foreach ($wpjobportal_paymentmethodconfig as $conf) {
                $current_payment_configs[$conf->configname] = $conf->configvalue;
            }
        }


        //DB class limitations
        foreach ($wpjobportal_data as $wpjobportal_key => $wpjobportal_value) {
            if(isset($current_configs[$wpjobportal_key]) && ($current_configs[$wpjobportal_key]  != $wpjobportal_value)) {

                if ($wpjobportal_key == 'default_image') { // ignore saving default image from here
                    continue;
                }
                if ($wpjobportal_key == 'data_directory') {
                    $wpjobportal_data_directory = $wpjobportal_value;
                    if(empty($wpjobportal_data_directory)){
                        WPJOBPORTALMessages::setLayoutMessage(esc_html(__('Data directory can not empty.', 'wp-job-portal')), 'error',$this->getMessagekey());
                        continue;
                    }
                    if(wpjobportalphplib::wpJP_strpos($wpjobportal_data_directory, '/') !== false){
                        WPJOBPORTALMessages::setLayoutMessage(esc_html(__('Data directory is not proper.', 'wp-job-portal')), 'error',$this->getMessagekey());
                        continue;
                    }
                    $wpjobportal_path = WPJOBPORTAL_PLUGIN_PATH.'/'.$wpjobportal_data_directory;
                    if ( ! function_exists( 'WP_Filesystem' ) ) {
                        require_once ABSPATH . 'wp-admin/includes/file.php';
                    }
                    global $wp_filesystem;
                    if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ) {
                        $creds = request_filesystem_credentials( site_url() );
                        wp_filesystem( $creds );
                    }

                    if ( ! $wp_filesystem->exists($wpjobportal_path)) {
                       $wp_filesystem->mkdir($wpjobportal_path, 0755);
                    }
                    if( ! $wp_filesystem->is_writable($wpjobportal_path)){
                        WPJOBPORTALMessages::setLayoutMessage(esc_html(__('Data directory is not writable.', 'wp-job-portal')), 'error',$this->getMessagekey());
                        continue;
                    }
                }
                $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_config` SET `configvalue` = '".esc_sql($wpjobportal_value)."' WHERE `configname`= '" . esc_sql($wpjobportal_key) . "'";
                if (false === wpjobportaldb::query($query)) {
                    $error = true;
                }
            }elseif(isset($current_payment_configs[$wpjobportal_key]) && ($current_payment_configs[$wpjobportal_key]  != $wpjobportal_value)) {
                if(in_array('credits',wpjobportal::$_active_addons)){
                    $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_paymentmethodconfig` SET `configvalue` = '" . esc_sql($wpjobportal_value) . "' WHERE `configname` = '" . esc_sql($wpjobportal_key) . "';";
                    if(false === wpjobportaldb::query($query)) {
                        $error = true;
                    }
                }
            }
        }

        // upload deault image code
        // removing file
        if(isset($wpjobportal_data['remove_default_image']) && $wpjobportal_data['remove_default_image'] == 1){
            $this->deletedefaultImageModel();
        }
        // uploading (attaching) file
        if(isset($_FILES['default_image'])){// min field issue
            if ($_FILES['default_image']['size'] > 0) {
                // if(!isset($wpjobportal_data['remove_default_image'])){
                //     $this->deletedefaultImageModel();
                // }
                $res = WPJOBPORTALincluder::getObjectClass('uploads')->uploadDeafultImage();
                if ($res == 6){
                    $wpjobportal_msg = WPJOBPORTALMessages::getMessage(WPJOBPORTAL_FILE_TYPE_ERROR, '');
                    WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->getMessagekey());
                }
                if($res == 5){
                    $wpjobportal_msg = WPJOBPORTALMessages::getMessage(WPJOBPORTAL_FILE_SIZE_ERROR, '');
                    WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->getMessagekey());
                }
            }
        }
        if ($error)
            return WPJOBPORTAL_SAVE_ERROR;
        else
            return WPJOBPORTAL_SAVED;
    }

    function storeAutoUpdateConfig() {

        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $wpjobportal_configvalue = WPJOBPORTALrequest::getVar('wpjobportal_addons_auto_update','','');

        if (!is_numeric($wpjobportal_configvalue)) { //can only have numric value
            return false;
        }

        $error = false;
        $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_config` SET `configvalue` = ".esc_sql($wpjobportal_configvalue)." WHERE `configname`= 'wpjobportal_addons_auto_update'";
        if (false === wpjobportaldb::query($query)) {
            $error = true;
        }

        if ($error)
            return WPJOBPORTAL_SAVE_ERROR;
        else
            return WPJOBPORTAL_SAVED;
    }

    // remove default image file and configuration value
    private function deletedefaultImageModel(){
        $wpjobportal_data_directory = wpjobportal::$_config->getConfigValue('data_directory');
        $wpjobportal_wpdir = wp_upload_dir();
        $wpjobportal_path = $wpjobportal_wpdir['basedir'] . '/' . $wpjobportal_data_directory . '/data/default_image/';
        $files = glob($wpjobportal_path . '/*.*');
        array_map('wp_delete_file', $files);    // delete all file in the direcoty
        $query = "UPDATE `".wpjobportal::$_db->prefix."wj_portal_config` SET configvalue = '' WHERE configname = 'default_image'";
        wpjobportal::$_db->query($query);
        return true;
    }

    function getConfigByFor($wpjobportal_configfor) {
        if (!$wpjobportal_configfor)
            return;
        $query = "SELECT * FROM `" . wpjobportal::$_db->prefix . "wj_portal_config` WHERE configfor = '" . esc_sql($wpjobportal_configfor) . "'";
        $wpjobportal_config = wpjobportaldb::get_results($query);
        $wpjobportal_configs = array();
        foreach ($wpjobportal_config as $conf) {
            $wpjobportal_configs[$conf->configname] = $conf->configvalue;
        }
        return $wpjobportal_configs;
    }

    function getCountConfig() {

        $query = "SELECT COUNT(*) FROM `" . wpjobportal::$_db->prefix . "wj_portal_config`";
        $wpjobportal_result = wpjobportaldb::get_var($query);
        return $wpjobportal_result;
    }

    function getConfigValue($wpjobportal_configname) {
        $query = "SELECT configvalue FROM `" . wpjobportal::$_db->prefix . "wj_portal_config` WHERE configname = '" . esc_sql($wpjobportal_configname) . "'";
        //return wpjobportaldb::get_var($query);
        return wpjobportal::$_db->get_var($query);
    }

    function getConfigurationByConfigForMultiple($wpjobportal_configfor){
        $query = "SELECT configname,configvalue
                  FROM `".wpjobportal::$_db->prefix."wj_portal_config` WHERE configfor IN (".$wpjobportal_configfor.")";
        $wpjobportal_result = wpjobportaldb::get_results($query);
        $wpjobportal_config_array =  array();
        //to make configuration in to an array with key as index
        foreach ($wpjobportal_result as $wpjobportal_config ) {
           $wpjobportal_config_array[$wpjobportal_config->configname] = $wpjobportal_config->configvalue;
        }
        return $wpjobportal_config_array;
    }

    function getConfigurationByConfigName($wpjobportal_configname){
        $query = "SELECT configvalue
                  FROM `".wpjobportal::$_db->prefix."wj_portal_config` WHERE configname ='" . esc_sql($wpjobportal_configname) . "'";
        $wpjobportal_result = wpjobportaldb::get_var($query);
        return $wpjobportal_result;

    }

    function checkCronKey($passkey) {

        $query = "SELECT COUNT(configvalue) FROM `".wpjobportal::$_db->prefix."wj_portal_config` WHERE configname = 'cron_job_alert_key' AND configvalue = '" . esc_sql($passkey) . "'";
        $wpjobportal_key = wpjobportaldb::get_var($query);
        if ($wpjobportal_key == 1)
            return true;
        else
            return false;
    }

    function getLoginRegisterRedirectLink($wpjobportal_defaulUrl,$redirectType) {
        if ($redirectType == 'register') {
            $wpjobportal_val = wpjobportal::$_configuration['set_register_redirect_link'];
            $wpjobportal_link = wpjobportal::$_configuration['register_redirect_link'];
            $wpDefaultPage = wp_registration_url();
        } else if ($redirectType == 'login') {
            $wpjobportal_val = wpjobportal::$_configuration['set_login_redirect_link'];
            $wpjobportal_link = wpjobportal::$_configuration['login_redirect_link'];
            $wpDefaultPage = wp_login_url();
        }
        $redirectval = $wpjobportal_val;
        $redirectlink = esc_url($wpjobportal_link);// to handle improper urls showing error
        if ($redirectval == 3){
            $hreflink = $wpDefaultPage;
        }
        else if($redirectval == 2 && $redirectlink != ""){
            $hreflink = $redirectlink;
        }else{
            $hreflink = $wpjobportal_defaulUrl;
        }
        return $hreflink;
    }
    function getMessagekey(){
        $wpjobportal_key = 'configuration';if(wpjobportal::$_common->wpjp_isadmin()){$wpjobportal_key = 'admin_'.$wpjobportal_key;}return $wpjobportal_key;
    }



    function getConfigSideMenu(){
        $wpjobportal_html = '<ul id="wpjobportaladmin-menu-links" class="tree config-accordion accordion wpjobportaladmin-sidebar-menu "  data-widget="tree">
            <li class="treeview" id="gen_setting">
                <a class="js-icon-left" href="#" title="'. esc_attr(__('general setting' , 'wp-job-portal')) .'">
                    <img src="'. esc_url(WPJOBPORTAL_PLUGIN_URL)."includes/images/control_panel/dashboard/admin-left-menu/config.png" .'"/>
                    <span class="wpjobportal_text wpjobportal-parent">'. esc_html(__("General Settings" , 'wp-job-portal')) .'</span>
                </a>
                <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
                    <li class="wpjobportal-child"><a href="?page=wpjobportal_configuration&wpjobportallt=configurations&wpjpconfigid=general_setting#site_setting" class="jslm_text">'. esc_html(__("Site Settings",'wp-job-portal')) .'</a></li>';
                    if(in_array('message', wpjobportal::$_active_addons)){
                        $wpjobportal_html .= '<li class="wpjobportal-child"><a href="?page=wpjobportal_configuration&wpjobportallt=configurations&wpjpconfigid=general_setting#message" class="jslm_text">'.  esc_html(__("Messages" , 'wp-job-portal')) .'</a></li>';
                    }
                    $wpjobportal_html .= '<li class="wpjobportal-child"><a href="?page=wpjobportal_configuration&wpjobportallt=configurations&wpjpconfigid=general_setting#defaul_setting" class="jslm_text">'.  esc_html(__("Default Settings" , 'wp-job-portal')) .'</a></li>
                    <li class="wpjobportal-child"><a href="?page=wpjobportal_configuration&wpjobportallt=configurations&wpjpconfigid=general_setting#categories" class="jslm_text">'.  esc_html(__("Categories" , 'wp-job-portal')) .'</a></li>
                    <li class="wpjobportal-child"><a href="?page=wpjobportal_configuration&wpjobportallt=configurations&wpjpconfigid=general_setting#email" class="jslm_text">'.  esc_html(__("Email" , 'wp-job-portal')) .'</a></li>';
                    if(in_array('addressdata', wpjobportal::$_active_addons)){
                        $wpjobportal_html .= '<li class="wpjobportal-child"><a href="?page=wpjobportal_configuration&wpjobportallt=configurations&wpjpconfigid=general_setting#googlemapadsense" class="jslm_text">'.  esc_html(__("Map" , 'wp-job-portal')) .'</a></li>';
                    }
                    $wpjobportal_html .= '<li class="wpjobportal-child"><a href="?page=wpjobportal_configuration&wpjobportallt=configurations&wpjpconfigid=general_setting#offline" class="jslm_text">'.  esc_html(__("Offline" , 'wp-job-portal')) .'</a></li>
                    <li class="wpjobportal-child"><a href="?page=wpjobportal_configuration&wpjobportallt=configurations&wpjpconfigid=general_setting#terms" class="jslm_text">'.  esc_html(__("Term And Conditions" , 'wp-job-portal')) .'</a></li>
                    <li class="wpjobportal-child"><a href="?page=wpjobportal_configuration&wpjobportallt=configurations&wpjpconfigid=general_setting#url-settings" class="jslm_text">'.  esc_html(__("URL Settings" , 'wp-job-portal')) .'</a></li>
                </ul>
            </li>
            <li class="treeview" id="emp_setting">
                <a class="js-icon-left" href="#" title="'. esc_attr(__('employer' , 'wp-job-portal')) .'">
                    <img src="'.  esc_url(WPJOBPORTAL_PLUGIN_URL)."includes/images/config/employer.png" .'"/>
                    <span class="jslm_text wpjobportal-parent ">'.  esc_html(__("Employer" , 'wp-job-portal')) .'</span>
                </a>
                <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
                    <li class="wpjobportal-child"><a href="admin.php?page=wpjobportal_configuration&wpjobportallt=configurationsemployer&wpjpconfigid=emp_general_setting#emp_generalsetting" class="jslm_text">'.  esc_html(__("General Settings",'wp-job-portal')) .'</a></li>';
                    if(in_array('addressdata', wpjobportal::$_active_addons)){
                        $wpjobportal_html .= '<li class="wpjobportal-child"><a href="admin.php?page=wpjobportal_configuration&wpjobportallt=configurationsemployer&wpjpconfigid=emp_general_setting#emp_listresume" class="jslm_text">'.  esc_html(__("Search Resume" , 'wp-job-portal')) .'</a></li> ';
                    }
                    $wpjobportal_html .= '<li class="wpjobportal-child"><a href="admin.php?page=wpjobportal_configuration&wpjobportallt=configurationsemployer&wpjpconfigid=emp_general_setting#email" class="jslm_text">'.  esc_html(__("Email" , 'wp-job-portal')) .'</a></li>
                    <li class="wpjobportal-child"><a href="admin.php?page=wpjobportal_configuration&wpjobportallt=configurationsemployer&wpjpconfigid=emp_general_setting#emp_auto_approve" class="jslm_text">'.  esc_html(__("Auto Approve" , 'wp-job-portal')) .'</a></li>
                    <li class="wpjobportal-child"><a href="admin.php?page=wpjobportal_configuration&wpjobportallt=configurationsemployer&wpjpconfigid=emp_general_setting#emp_company" class="jslm_text">'.  esc_html(__("Company" , 'wp-job-portal')) .'</a></li>
                    <li class="wpjobportal-child"><a href="admin.php?page=wpjobportal_configuration&wpjobportallt=configurationsemployer&wpjpconfigid=emp_general_setting#emp_memberlinks" class="jslm_text">'.  esc_html(__("Members Links" , 'wp-job-portal')) .'</a></li>
                </ul>
            </li>
            <li class="treeview" id="js_setting">
                <a class="js-icon-left" href="#" title="'. esc_attr(__('job seeker' , 'wp-job-portal')) .'">
                    <img src="'. esc_url(WPJOBPORTAL_PLUGIN_URL)."includes/images/config/joseeker.png" .'"/>
                    <span class="jslm_text wpjobportal-parent">'. esc_html(__("Job Seeker" , 'wp-job-portal')) .'</span>
                </a>
                <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
                    <li class="wpjobportal-child"><a href="admin.php?page=wpjobportal_configuration&wpjobportallt=configurationsjobseeker&wpjpconfigid=jobseeker_general_setting#js_generalsetting" class="jslm_text">'.  esc_html(__("General Settings",'wp-job-portal')) .'</a></li>
                    <li class="wpjobportal-child"><a href="admin.php?page=wpjobportal_configuration&wpjobportallt=configurationsjobseeker&wpjpconfigid=jobseeker_general_setting#js_resume_setting" class="jslm_text">'.  esc_html(__("Resume Settings" , 'wp-job-portal')) .'</a></li>
                    <li class="wpjobportal-child"><a href="admin.php?page=wpjobportal_configuration&wpjobportallt=configurationsjobseeker&wpjpconfigid=jobseeker_general_setting#js_memberlinks" class="jslm_text">'.  esc_html(__("Members Links" , 'wp-job-portal')) .'</a></li>
                </ul>
            </li>
            <li class="treeview" id="apply_setting">
                <a class="js-icon-left" href="#" title="'. esc_attr(__('Job Apply' , 'wp-job-portal')) .'">
                    <img src="'. esc_url(WPJOBPORTAL_PLUGIN_URL)."includes/images/config/apply-config.png" .'"/>
                    <span class="jslm_text wpjobportal-parent">'. esc_html(__("Job Apply settings" , 'wp-job-portal')) .'</span>
                </a>
                <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
                    <li class="wpjobportal-child"><a href="admin.php?page=wpjobportal_configuration&wpjobportallt=configurations&wpjpconfigid=job_apply#quick_apply" class="jslm_text">'.  esc_html(__("Quick Apply settings",'wp-job-portal')) .'</a></li>
                    <li class="wpjobportal-child"><a href="admin.php?page=wpjobportal_configuration&wpjobportallt=configurations&wpjpconfigid=job_apply#job_apply_settings" class="jslm_text">'.  esc_html(__("Job Apply settings",'wp-job-portal')) .'</a></li>
                </ul>
            </li>


            <li class="treeview" id="ai_setting">
                <a class="js-icon-left" href="#" title="'. esc_attr(__('AI Settings' , 'wp-job-portal')) .'">
                    <img src="'. esc_url(WPJOBPORTAL_PLUGIN_URL)."includes/images/config/ai-addons.png" .'"/>
                    <span class="jslm_text wpjobportal-parent">'. esc_html(__("AI settings" , 'wp-job-portal')) .'</span>
                </a>
                <ul class="wpjobportaladmin-sidebar-submenu treeview-menu"> ';

                    if(in_array('aijobsearch', wpjobportal::$_active_addons)){
                        $wpjobportal_html .= '<li class="wpjobportal-child"><a href="admin.php?page=wpjobportal_configuration&wpjobportallt=configurations&wpjpconfigid=ai_settings#aijobsearch" class="jslm_text">'.  esc_html(__("AI Job Search",'wp-job-portal')) .'</a></li>';
                    } else {
                        $plugininfo = wpjobportal_checkWPJPPluginInfo('wp-job-portal-aijobsearch/wp-job-portal-aijobsearch.php');
                        if($plugininfo['availability'] == "1"){
                            $wpjobportal_text = $plugininfo['text'];
                            $wpjobportal_url = "plugins.php?s=wp-job-portal-aijobsearch&plugin_status=inactive";
                        }elseif($plugininfo['availability'] == "0"){
                            $wpjobportal_text = $plugininfo['text'];
                            $wpjobportal_url = "https://wpjobportal.com/product/social-share/";
                        }
                        $wpjobportal_html .= '<li class="disabled-menu">
                                    <span class="wpjobportaladmin-text">'. esc_html(__('AI Job Search' , 'wp-job-portal')).'</span>
                                    <a href="'. esc_url($wpjobportal_url).'" class="jslm_text">'. esc_html($wpjobportal_text).'</a>
                                 </li>';
                    }

                    if(in_array('aisuggestedjobs', wpjobportal::$_active_addons)){
                        $wpjobportal_html .= '<li class="wpjobportal-child"><a href="admin.php?page=wpjobportal_configuration&wpjobportallt=configurations&wpjpconfigid=ai_settings#aisuggestedjobs" class="jslm_text">'.  esc_html(__("AI Suggested Jobs",'wp-job-portal')) .'</a></li>';
                    } else {
                        $plugininfo = wpjobportal_checkWPJPPluginInfo('wp-job-portal-aisuggestedjobs/wp-job-portal-aisuggestedjobs.php');
                        if($plugininfo['availability'] == "1"){
                            $wpjobportal_text = $plugininfo['text'];
                            $wpjobportal_url = "plugins.php?s=wp-job-portal-aisuggestedjobs&plugin_status=inactive";
                        }elseif($plugininfo['availability'] == "0"){
                            $wpjobportal_text = $plugininfo['text'];
                            $wpjobportal_url = "https://wpjobportal.com/product/social-share/";
                        }
                        $wpjobportal_html .= '<li class="disabled-menu">
                                    <span class="wpjobportaladmin-text">'. esc_html(__('AI Suggested Jobs' , 'wp-job-portal')).'</span>
                                    <a href="'. esc_url($wpjobportal_url).'" class="jslm_text">'. esc_html($wpjobportal_text).'</a>
                                 </li>';
                    }

                    if(in_array('airesumesearch', wpjobportal::$_active_addons)){
                        $wpjobportal_html .= '<li class="wpjobportal-child"><a href="admin.php?page=wpjobportal_configuration&wpjobportallt=configurations&wpjpconfigid=ai_settings#airesumesearch" class="jslm_text">'.  esc_html(__("AI Resume Search",'wp-job-portal')) .'</a></li>';
                    } else {
                        $plugininfo = wpjobportal_checkWPJPPluginInfo('wp-job-portal-airesumesearch/wp-job-portal-airesumesearch.php');
                        if($plugininfo['availability'] == "1"){
                            $wpjobportal_text = $plugininfo['text'];
                            $wpjobportal_url = "plugins.php?s=wp-job-portal-airesumesearch&plugin_status=inactive";
                        }elseif($plugininfo['availability'] == "0"){
                            $wpjobportal_text = $plugininfo['text'];
                            $wpjobportal_url = "https://wpjobportal.com/product/social-share/";
                        }
                        $wpjobportal_html .= '<li class="disabled-menu">
                                    <span class="wpjobportaladmin-text">'. esc_html(__('AI Resume Search' , 'wp-job-portal')).'</span>
                                    <a href="'. esc_url($wpjobportal_url).'" class="jslm_text">'. esc_html($wpjobportal_text).'</a>
                                 </li>';
                    }

                    if(in_array('aisuggestedresumes', wpjobportal::$_active_addons)){
                        $wpjobportal_html .= '<li class="wpjobportal-child"><a href="admin.php?page=wpjobportal_configuration&wpjobportallt=configurations&wpjpconfigid=ai_settings#aisuggestedresumes" class="jslm_text">'.  esc_html(__("AI Suggested Resumes",'wp-job-portal')) .'</a></li>';
                    } else {
                        $plugininfo = wpjobportal_checkWPJPPluginInfo('wp-job-portal-aisuggestedresumes/wp-job-portal-aisuggestedresumes.php');
                        if($plugininfo['availability'] == "1"){
                            $wpjobportal_text = $plugininfo['text'];
                            $wpjobportal_url = "plugins.php?s=wp-job-portal-aisuggestedresumes&plugin_status=inactive";
                        }elseif($plugininfo['availability'] == "0"){
                            $wpjobportal_text = $plugininfo['text'];
                            $wpjobportal_url = "https://wpjobportal.com/product/social-share/";
                        }
                        $wpjobportal_html .= '<li class="disabled-menu">
                                    <span class="wpjobportaladmin-text">'. esc_html(__('AI Suggested Resumes' , 'wp-job-portal')).'</span>
                                    <a href="'. esc_url($wpjobportal_url).'" class="jslm_text">'. esc_html($wpjobportal_text).'</a>
                                 </li>';
                    }

                $wpjobportal_html .= '
                </ul>
            </li>


            <li class="treeview" id="vis_setting">
                <a class="js-icon-left" href="#" title="'. esc_attr(__('visitor setting' , 'wp-job-portal')) .'">
                    <img src="'. esc_url(WPJOBPORTAL_PLUGIN_URL)."includes/images/config/user.png" .'"/>
                    <span class="jslm_text wpjobportal-parent">'. esc_html(__("Visitor Settings" , 'wp-job-portal')) .'</span>
                </a>
                <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
                    <li class="wpjobportal-child"><a href="admin.php?page=wpjobportal_configuration&wpjobportallt=configurations&wpjpconfigid=visitor_setting#captcha_setting" class="jslm_text">'.  esc_html(__("Captcha Settings",'wp-job-portal')) .'</a></li>
                    <li class="wpjobportal-child"><a href="admin.php?page=wpjobportal_configuration&wpjobportallt=configurations&wpjpconfigid=visitor_setting#visitor_setting_employer_side" class="jslm_text">'.  esc_html(__("Employer Settings" , 'wp-job-portal')) .'</a></li>
                    <li class="wpjobportal-child"><a href="admin.php?page=wpjobportal_configuration&wpjobportallt=configurations&wpjpconfigid=visitor_setting#js_visitor" class="jslm_text">'.  esc_html(__("Jobseeker Settings" , 'wp-job-portal')) .'</a></li>
                    <li class="wpjobportal-child"><a href="admin.php?page=wpjobportal_configuration&wpjobportallt=configurations&wpjpconfigid=visitor_setting#emp_visitorlinks" class="jslm_text">'.  esc_html(__("Employer Links" , 'wp-job-portal')) .'</a></li>
                    <li class="wpjobportal-child"><a href="admin.php?page=wpjobportal_configuration&wpjobportallt=configurations&wpjpconfigid=visitor_setting#js_memberlinks" class="jslm_text">'.  esc_html(__("Jobseeker Links" , 'wp-job-portal')) .'</a></li>
                </ul>
            </li>

            ';
            if(in_array('credits', wpjobportal::$_active_addons)){
                 $wpjobportal_html .= '<li class="treeview" id="pack_setting">
                    <a class="js-icon-left" href="#" title="'. esc_attr(__('package setting' , 'wp-job-portal')) .'">
                        <img src="'. esc_url(WPJOBPORTAL_PLUGIN_URL)."includes/images/config/package.png" .'"/>
                        <span class="jslm_text wpjobportal-parent">'. esc_html(__("Package Settings" , 'wp-job-portal')) .'</span>
                    </a>
                    <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
                        <li class="wpjobportal-child"><a href="admin.php?page=wpjobportal_configuration&wpjobportallt=configurations&wpjpconfigid=package_setting#package" class="jslm_text">'.  esc_html(__("Free Packages",'wp-job-portal')) .'</a></li>
                        <li class="wpjobportal-child"><a href="admin.php?page=wpjobportal_configuration&wpjobportallt=configurations&wpjpconfigid=package_setting#paid_submission" class="jslm_text">'.  esc_html(__("Paid Submissions" , 'wp-job-portal')) .'</a></li>
                    </ul>
                </li>';
            } else {
                $plugininfo = wpjobportal_checkWPJPPluginInfo('wp-job-portal-credits/wp-job-portal-credits.php');
                if($plugininfo['availability'] == "1"){
                    $wpjobportal_text = $plugininfo['text'];
                    $wpjobportal_url = "plugins.php?s=wp-job-portal-credits&plugin_status=inactive";
                }elseif($plugininfo['availability'] == "0"){
                    $wpjobportal_text = $plugininfo['text'];
                    $wpjobportal_url = "https://wpjobportal.com/product/credit-system/";
                }
                $wpjobportal_html .= '<li class="disabled-menu">
                    <img src="'. esc_url(WPJOBPORTAL_PLUGIN_URL)."includes/images/config/package-grey.png" .'"/>
                    <span class="wpjobportaladmin-text">'. esc_html(__('Package Settings' , 'wp-job-portal')).'</span>
                    <a href="'. esc_url($wpjobportal_url).'" class="wpjobportaladmin-install-btn" title="'. esc_attr($wpjobportal_text).'">'. esc_html($wpjobportal_text).'</a>
               </li>';
            }
            $wpjobportal_html .= '<li class="treeview" id="social_setting">
                <a class="js-icon-left" href="#" title="'. esc_attr(__('social apps' , 'wp-job-portal')) .'">
                    <img src="'. esc_url(WPJOBPORTAL_PLUGIN_URL)."includes/images/config/social_share.png" .'"/>
                    <span class="jslm_text wpjobportal-parent">'. esc_html(__(" Social Apps" , 'wp-job-portal')) .'</span>
                </a>
                <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">';
                    if(in_array('socialshare', wpjobportal::$_active_addons)){
                        $wpjobportal_html .= '<li class="wpjobportal-child"><a href="admin.php?page=wpjobportal_configuration&wpjobportallt=configurations&wpjpconfigid=social_share#socialsharing" class="jslm_text">'.  esc_html(__("Social Links",'wp-job-portal')) .'</a></li>';
                    } else {
                        $plugininfo = wpjobportal_checkWPJPPluginInfo('wp-job-portal-socialshare/wp-job-portal-socialshare.php');
                        if($plugininfo['availability'] == "1"){
                            $wpjobportal_text = $plugininfo['text'];
                            $wpjobportal_url = "plugins.php?s=wp-job-portal-socialshare&plugin_status=inactive";
                        }elseif($plugininfo['availability'] == "0"){
                            $wpjobportal_text = $plugininfo['text'];
                            $wpjobportal_url = "https://wpjobportal.com/product/social-share/";
                        }
                        $wpjobportal_html .= '<li class="disabled-menu">
                                    <span class="wpjobportaladmin-text">'. esc_html(__('Social Share' , 'wp-job-portal')).'</span>
                                    <a href="'. esc_url($wpjobportal_url).'" class="jslm_text">'. esc_html($wpjobportal_text).'</a>
                                 </li>';
                    }
                    if(in_array('sociallogin', wpjobportal::$_active_addons)){
                        $wpjobportal_html .= '<li class="wpjobportal-child"><a href="admin.php?page=wpjobportal_configuration&wpjobportallt=configurations&wpjpconfigid=social_share#facebook" class="jslm_text">'.  esc_html(__("Facebook" , 'wp-job-portal')) .'</a></li>
                        <li class="wpjobportal-child"><a href="admin.php?page=wpjobportal_configuration&wpjobportallt=configurations&wpjpconfigid=social_share#linkedin" class="jslm_text">'.  esc_html(__("Linkedin" , 'wp-job-portal')) .'</a></li>';
                    } else {
                        $plugininfo = wpjobportal_checkWPJPPluginInfo('wp-job-portal-sociallogin/wp-job-portal-sociallogin.php');
                        if($plugininfo['availability'] == "1"){
                            $wpjobportal_text = $plugininfo['text'];
                            $wpjobportal_url = "plugins.php?s=wp-job-portal-sociallogin&plugin_status=inactive";
                        }elseif($plugininfo['availability'] == "0"){
                            $wpjobportal_text = $plugininfo['text'];
                            $wpjobportal_url = "https://wpjobportal.com/product/social-login/";
                        }
                        $wpjobportal_html .= '<li class="disabled-menu">
                                    <span class="wpjobportaladmin-text">'. esc_html(__('Social Login' , 'wp-job-portal')).'</span>
                                    <a href="'. esc_url($wpjobportal_url).'" class="jslm_text">'. esc_html($wpjobportal_text).'</a>
                                 </li>';
                    }
                $wpjobportal_html .= '</ul>
            </li>';
            if(in_array('rssfeedback', wpjobportal::$_active_addons)){
                $wpjobportal_html .= '<li class="treeview" id="rs_setting">
                    <a class="js-icon-left" href="#" title="'. esc_attr(__('rss' , 'wp-job-portal')) .'">
                        <img src="'. esc_url(WPJOBPORTAL_PLUGIN_URL)."includes/images/config/rss.png" .'"/>
                        <span class="jslm_text wpjobportal-parent">'. esc_html(__("RSS" , 'wp-job-portal')) .'</span>
                    </a>
                    <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
                        <li class="wpjobportal-child"><a href="admin.php?page=wpjobportal_configuration&wpjobportallt=configurations&wpjpconfigid=rss_setting#rssjob" class="jslm_text">'.  esc_html(__("Job Settings",'wp-job-portal')) .'</a></li>
                        <li class="wpjobportal-child"><a href="admin.php?page=wpjobportal_configuration&wpjobportallt=configurations&wpjpconfigid=rss_setting#rssresume" class="jslm_text">'.  esc_html(__("Resume Settings" , 'wp-job-portal')) .'</a></li>
                    </ul>
                </li>';
            } else {
                $plugininfo = wpjobportal_checkWPJPPluginInfo('wp-job-portal-rssfeedback/wp-job-portal-rssfeedback.php');
                if($plugininfo['availability'] == "1"){
                    $wpjobportal_text = $plugininfo['text'];
                    $wpjobportal_url = "plugins.php?s=wp-job-portal-rssfeedback&plugin_status=inactive";
                }elseif($plugininfo['availability'] == "0"){
                    $wpjobportal_text = $plugininfo['text'];
                    $wpjobportal_url = "https://wpjobportal.com/product/rss-2/";
                }
                $wpjobportal_html .= '<li class="disabled-menu">
                    <img src="'. esc_url(WPJOBPORTAL_PLUGIN_URL)."includes/images/config/package-grey.png" .'"/>
                    <span class="wpjobportaladmin-text">'. esc_html(__('RSS' , 'wp-job-portal')).'</span>
                    <a href="'. esc_url($wpjobportal_url).'" class="wpjobportaladmin-install-btn" title="'. esc_attr($wpjobportal_text).'">'. esc_html($wpjobportal_text).'</a>
               </li>';
            }
            $wpjobportal_html .= '<li class="treeview" id="lr_setting">
                    <a class="js-icon-left" href="#" title="'. esc_attr(__('login/register' , 'wp-job-portal')) .'">
                        <img src="'. esc_url(WPJOBPORTAL_PLUGIN_URL)."includes/images/config/login.png" .'"/>
                        <span class="jslm_text wpjobportal-parent">'. esc_html(__(" Login/Register" , 'wp-job-portal')) .'</span>
                    </a>
                    <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
                        <li class="wpjobportal-child"><a href="admin.php?page=wpjobportal_configuration&wpjobportallt=configurations&wpjpconfigid=login_register#login" class="jslm_text">'.  esc_html(__("Login",'wp-job-portal')) .'</a></li>
                        <li class="wpjobportal-child"><a href="admin.php?page=wpjobportal_configuration&wpjobportallt=configurations&wpjpconfigid=login_register#register" class="jslm_text">'.  esc_html(__("Register" , 'wp-job-portal')) .'</a></li>
                    </ul>
                </li>';
            if(in_array('credits', wpjobportal::$_active_addons)){
                $wpjobportal_html .= '<li class="treeview" id="pm_setting">
                    <a class="js-icon-left" href="#" title="'. esc_attr(__('payment method' , 'wp-job-portal')) .'">
                        <img src="'. esc_url(WPJOBPORTAL_PLUGIN_URL)."includes/images/config/payment.png" .'"/>
                        <span class="jslm_text wpjobportal-parent">'. esc_html(__("Payment Method" , 'wp-job-portal')) .'</span>
                    </a>
                    <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
                        <li class="wpjobportal-child"><a href="admin.php?page=wpjobportal_paymentmethodconfiguration&wpjpconfigid=pay_setting#paypal" class="jslm_text">'.  esc_html(__("PayPal",'wp-job-portal')) .'</a></li>
                        <li class="wpjobportal-child"><a href="admin.php?page=wpjobportal_paymentmethodconfiguration&wpjpconfigid=pay_setting#stripe" class="jslm_text">'.  esc_html(__("Stripe" , 'wp-job-portal')) .'</a></li>
                        <li class="wpjobportal-child"><a href="admin.php?page=wpjobportal_paymentmethodconfiguration&wpjpconfigid=pay_setting#others" class="jslm_text">'.  esc_html(__("Woocommerce" , 'wp-job-portal')) .'</a></li>
                    </ul>
                </li>';
            }
            else{
                $plugininfo = wpjobportal_checkWPJPPluginInfo('wp-job-portal-credits/wp-job-portal-credits.php');
                if($plugininfo['availability'] == "1"){
                    $wpjobportal_text = $plugininfo['text'];
                    $wpjobportal_url = "plugins.php?s=wp-job-portal-credits&plugin_status=inactive";
                }elseif($plugininfo['availability'] == "0"){
                    $wpjobportal_text = $plugininfo['text'];
                    $wpjobportal_url = "https://wpjobportal.com/product/credit-system/";
                }
                $wpjobportal_html .= '<li class="disabled-menu">
                    <img src="'. esc_url(WPJOBPORTAL_PLUGIN_URL)."includes/images/config/payment_grey.png" .'"/>
                    <span class="wpjobportaladmin-text">'. esc_html(__('Payment Method' , 'wp-job-portal')).'</span>
                    <a href="'. esc_url($wpjobportal_url).'" class="wpjobportaladmin-install-btn" title="'. esc_attr($wpjobportal_text).'">'. esc_html($wpjobportal_text).'</a>
                </li>';
            }
            if(in_array('cronjob', wpjobportal::$_active_addons)){
                $wpjobportal_html .= '<li class="treeview" id="cj_setting">
                    <a class="js-icon-left" href="#" title="'. esc_attr(__('cron job' , 'wp-job-portal')) .'">
                        <img src="'. esc_url(WPJOBPORTAL_PLUGIN_URL)."includes/images/config/cron_job.png" .'"/>
                        <span class="jslm_text wpjobportal-parent">'. esc_html(__("Cron Job" , 'wp-job-portal')) .'</span>
                    </a>
                    <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
                        <li class="wpjobportal-child"><a href="admin.php?page=wpjobportal_cronjob&wpjobportallt=cronjob&wpjpconfigid=cron_setting#webcrown" class="jslm_text">'.  esc_html(__("Webcrown.org",'wp-job-portal')) .'</a></li>
                        <li class="wpjobportal-child"><a href="admin.php?page=wpjobportal_cronjob&wpjobportallt=cronjob&wpjpconfigid=cron_setting#wget" class="jslm_text">'.  esc_html(__("Wget" , 'wp-job-portal')) .'</a></li>
                        <li class="wpjobportal-child"><a href="admin.php?page=wpjobportal_cronjob&wpjobportallt=cronjob&wpjpconfigid=cron_setting#curl" class="jslm_text">'.  esc_html(__("Curl" , 'wp-job-portal')) .'</a></li>
                        <li class="wpjobportal-child"><a href="admin.php?page=wpjobportal_cronjob&wpjobportallt=cronjob&wpjpconfigid=cron_setting#phpscript" class="jslm_text">'.  esc_html(__("Php Script" , 'wp-job-portal')) .'</a></li>
                        <li class="wpjobportal-child"><a href="admin.php?page=wpjobportal_cronjob&wpjobportallt=cronjob&wpjpconfigid=cron_setting#url" class="jslm_text">'.  esc_html(__("Website" , 'wp-job-portal')) .'</a></li>
                    </ul>
                </li>';
            }else{
                $plugininfo = wpjobportal_checkWPJPPluginInfo('wp-job-portal-cronjob/wp-job-portal-cronjob.php');
                if($plugininfo['availability'] == "1"){
                    $wpjobportal_text = $plugininfo['text'];
                    $wpjobportal_url = "plugins.php?s=wp-job-portal-cronjob&plugin_status=inactive";
                }elseif($plugininfo['availability'] == "0"){
                    $wpjobportal_text = $plugininfo['text'];
                    $wpjobportal_url = "https://wpjobportal.com/product/cron-job-copy/";
                }
                $wpjobportal_html .= '<li class="disabled-menu">
                    <img src="'. esc_url(WPJOBPORTAL_PLUGIN_URL)."includes/images/config/cron_job_grey.png" .'"/>
                    <span class="wpjobportaladmin-text">'. esc_html(__('Cron Job' , 'wp-job-portal')).'</span>
                    <a href="'. esc_url($wpjobportal_url).'" class="wpjobportaladmin-install-btn" title="'. esc_attr($wpjobportal_text).'">'. esc_html($wpjobportal_text).'</a>
                </li>';
             }
        $wpjobportal_html .= '</ul>';
        return $wpjobportal_html;
    }
/* NO LONGER USED
    // update single configuration from overview page
    function storeConfigurationSingle() {
        // nonce check
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($wpjobportal_nonce, 'wpjobportal_configuration_nonce')) {
            die('Security check Failed');
        }

        // onlyy admin can use this fucntion
        if (!current_user_can('manage_options')) {
            return false;
        }

        $wpjobportal_config_name = WPJOBPORTALrequest::getVar('config_name', '', '');
        $wpjobportal_config_value = WPJOBPORTALrequest::getVar('config_value', '', '');

        if($wpjobportal_config_name == ''){
            return false;
        }

        // not sure about this if code
        // if($wpjobportal_config_value == ''){
        //     return false;
        // }


        // List of allowed configurations to avoud issues
        $wpjobportal_allowed_configs = array(
            'companyautoapprove',
        );

        if (!in_array($wpjobportal_config_name, $wpjobportal_allowed_configs)) {
            return false;
        }

        $error = false;
        $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_config`
                  SET `configvalue` = '".esc_sql($wpjobportal_config_value)."'
                  WHERE `configname` = '".esc_sql($wpjobportal_config_name)."'";
        if (wpjobportaldb::query($query)) {
            $error = true;
        }
        return $error;
    }
    */
}

?>
