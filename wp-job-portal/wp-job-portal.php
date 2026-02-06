<?php

/**
 * @package WP JOB PORTAL
 * @author Ahmad Bilal
 * @version 2.4.6
 */
/*
  * Plugin Name: WP Job Portal
  * Plugin URI: https://wpjobportal.com/
  * Description: WP Job Portal is WordPress’s best job board plugin — easy to use, highly configurable, and built to support both job seekers and employers. AI-powered add-ons offers smart job & resume search, and personalized recommendations.
  * Author: WP Job Portal
  * Version: 2.4.6
  * Text Domain: wp-job-portal
  * Domain Path: /languages
  * Author URI: https://wpjobportal.com/
  * License: GPLv3
  * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

if (!defined('ABSPATH'))
    die('Restricted Access');

class wpjobportal {

    public static $_path;
    public static $_pluginpath;
    public static $_data; /* data[0] for list , data[1] for total paginition ,data[2] fieldsorderring , data[3] userfield for form , data[4] for reply , data[5] for ticket history  , data[6] for internal notes  , data[7] for ban email  , data['ticket_attachment'] for attachment */
    public static $_pageid;
    public static $_db;
    public static $_configuration;
    public static $_sorton;
    public static $_sortorder;
    public static $_ordering;
    public static $_sortlinks;
    public static $_msg;
    public static $_error_flag;
    public static $_error_flag_message;
    public static $_currentversion;
    public static $_active_addons;
    public static $_addon_query;
    public static $_error_flag_message_for;
    public static $_error_flag_message_for_link;
    public static $_error_flag_message_for_link_text;
    public static $_error_flag_message_register_for;
    public static $wpjobportal_theme_chk;
    public static $wpjobportal_theme_chk_flag;
    public static $_common;
    public static $_config;
    public static $_wpjppurchasehistory;
    public static $_wpjppaymentconfig;
    public static $_wpjpfieldordering;
    public static $_wpjpcustomfield;
    public static $_wpjpcompany;
    //public static $_wpjpmodalpath;
    public static $_wpprefixforuser;
    public static $_iswpjobportalplugin;
    public static $_search;
    public static $_captcha;
    public static $_jsjpsession;
    public static $_company_job_table_join;
    public static $wpjobportal_data; // may work ????

    function __construct() {
        self::wpjobportal_LoadWpCoreFiles();
        self::wpjobportal_includes();
        $wpjobportal_plugin_array = get_option('active_plugins');
        $wpjobportal_addon_array = array();
        foreach ($wpjobportal_plugin_array as $wpjobportal_key => $wpjobportal_value) {
            $wpjobportal_plugin_name = pathinfo($wpjobportal_value, PATHINFO_FILENAME);
            if(wpjobportalphplib::wpJP_strstr($wpjobportal_plugin_name, 'wp-job-portal-')){
                $wpjobportal_addon_array[] = wpjobportalphplib::wpJP_str_replace('wp-job-portal-', '', $wpjobportal_plugin_name);
            }
        }
        self::$_active_addons = $wpjobportal_addon_array;
        self::$_wpjpcustomfield = WPJOBPORTALincluder::getObjectClass('customfields');
        //  self::registeractions();
        self::$_path = plugin_dir_path(__FILE__);
        self::$_pluginpath = plugins_url('/', __FILE__);
        self::$_data = array();
        self::$_error_flag = null;
        self::$_error_flag_message = null;
        self::$_currentversion = '246';
        self::$_addon_query = array('select'=>'','join'=>'','where'=>'');
        self::$_common = WPJOBPORTALincluder::getJSModel('common');
        self::$_config = WPJOBPORTALincluder::getJSModel('configuration');
        self::$_wpjpfieldordering = WPJOBPORTALincluder::getJSModel('fieldordering');
        self::$_iswpjobportalplugin = true;
        self::$_jsjpsession = WPJOBPORTALincluder::getObjectClass('wpjpsession');
        if(in_array('credits', wpjobportal::$_active_addons)){
            self::$_wpjppurchasehistory = WPJOBPORTALincluder::getJSModel('purchasehistory');
            self::$_wpjppaymentconfig = WPJOBPORTALincluder::getJSModel('paymentmethodconfiguration');
        }
        if(in_array('multicompany', wpjobportal::$_active_addons)){
            self::$_wpjpcompany = WPJOBPORTALincluder::getJSModel('multicompany');
        }else{
            self::$_wpjpcompany = WPJOBPORTALincluder::getJSModel('company');
        }
        global $wpdb;
        self::$_db = $wpdb;
        if(is_multisite()) {
            self::$_wpprefixforuser = $wpdb->base_prefix;
        }else{
            self::$_wpprefixforuser = self::$_db->prefix;
        }
        WPJOBPORTALincluder::getJSModel('configuration')->getConfiguration();
        register_activation_hook(__FILE__, array($this, 'wpjobportal_activate'));
        register_deactivation_hook(__FILE__, array($this, 'wpjobportal_deactivate'));
        if(version_compare(get_bloginfo('version'),'5.1', '>=')){ //for wp version >= 5.1
            add_action('wp_insert_site', array($this, 'wpjobportal_new_site')); //when new site is added in multisite
        }else{ //for wp version < 5.1
            add_action('wpmu_new_blog', array($this, 'wpjobportal_new_blog'), 10, 6);
        }
        add_filter('wpmu_drop_tables', array($this, 'wpjobportal_delete_site'));
        //add_action('plugins_loaded', array($this, 'wpjobportal_load_plugin_textdomain'));
        //PDF Change
        //add_action('template_redirect', array($this, 'pdf'), 5); // Only for the pdf in wordpress
        add_action('admin_init', array($this, 'wpjobportal_activation_redirect'));//for post installation screens
        add_action('wpjobportal_cronjobs_action', array($this,'wpjobportal_cronjobs'));
        add_action('reset_wpjobportal_aadon_query', array($this,'reset_wpjobportal_aadon_query') );
        $wpjobportal_theme_chk = 0;
        $wpjobportal_theme_chk_flag = 0;
        $wpjobportal_theme = get_option( 'template' );
        if($wpjobportal_theme == 'job-portal-theme'){
            $wpjobportal_theme_chk_flag = 1;
            $wpjobportal_theme_chk = 1;
        }
        define( 'WPJOBPORTAL_IMAGE', self::$_pluginpath . 'includes/images' );
        self::$wpjobportal_theme_chk = $wpjobportal_theme_chk;
        self::$wpjobportal_theme_chk_flag = $wpjobportal_theme_chk_flag;

        self::$_company_job_table_join = ' LEFT ';

        add_action('admin_init', array($this,'jsjp_handle_search_form_data'));
        add_action('admin_init', array($this,'jsjp_handle_delete_cookies'));
        add_action('init', array($this,'jsjp_handle_search_form_data'));
        add_action( 'jsjp_delete_expire_session_data', array($this , 'jsjp_delete_expire_session_data') );
        if( !wp_next_scheduled( 'jsjp_delete_expire_session_data' ) ) {
            // Schedule the event
            wp_schedule_event( time(), 'daily', 'jsjp_delete_expire_session_data' );
        }
        add_filter('safe_style_css', array($this,'jsjp_safe_style_css'), 10, 1);
        //add_action( 'upgrader_process_complete', array($this , 'jsjp_upgrade_completed'), 10, 2 );
        // code from advance custom fields addone
        add_filter('wpjobportal_addons_get_custom_field',array($this,'wpjobportal_addons_get_activeField'),10,4);
        add_filter('wpjobportal_addons_show_customfields_params',array($this,'wpjobportal_addons_paramsfields'),10,4);
        // job_manager_options global varible is intlized at the bottom of this file.
        // If seo plugin is activated
        if (is_plugin_active( 'all-in-one-seo-pack/all_in_one_seo_pack.php' ) ){
            add_filter( 'aioseo_disable_shortcode_parsing', '__return_true' );
        }
        // deactivate new block editor
        add_action( 'after_setup_theme', array($this , 'phi_theme_support'), 10, 2 );
        add_filter( 'document_title', array($this, 'wpjobportalgetdocumenttitle'),1,99);
    }

    function wpjobportalgetdocumenttitle($title){
        $custom_title = WPJOBPORTALincluder::getJSModel('common')->getWPJobPortalDocumentTitleByPage();
        if($custom_title != '' ){
            return $custom_title;
        }
        return $title;
    }
    // functions from advance custom fields addone
    function wpjobportal_addons_get_activeField($wpjobportal_default_val,$wpjobportal_id,$wpjobportal_id1='',$wpjobportal_id2=''){
        if($wpjobportal_id1 == '' && $wpjobportal_id2 == ''){
            return wpjobportal::$_wpjpcustomfield->userFieldsData($wpjobportal_id);
        }elseif ($wpjobportal_id1 != '' && $wpjobportal_id2 == '') {
            return wpjobportal::$_wpjpcustomfield->userFieldsData($wpjobportal_id,$wpjobportal_id1);
        }elseif ($wpjobportal_id1 != '' && $wpjobportal_id2 != '') {
            return wpjobportal::$_wpjpcustomfield->userFieldsData($wpjobportal_id,$wpjobportal_id1,$wpjobportal_id2);
        }
    }

    function wpjobportal_addons_paramsfields($wpjobportal_default_val,$wpjobportal_field,$wpjobportal_id,$params){
        return wpjobportal::$_wpjpcustomfield->showCustomFields($wpjobportal_field,$wpjobportal_id,$params);
    }

    function phi_theme_support() {
        remove_theme_support( 'widgets-block-editor' );
    }

    function jsjp_upgrade_completed( $wpjobportal_upgrader_object, $wpjobportal_options ) {
        // The path to our plugin's main file
        $our_plugin = plugin_basename( __FILE__ );
        // If an update has taken place and the updated type is plugins and the plugins element exists
        if( $wpjobportal_options['action'] == 'update' && $wpjobportal_options['type'] == 'plugin' && isset( $wpjobportal_options['plugins'] ) ) {
            // Iterate through the plugins being updated and check if ours is there
            foreach( $wpjobportal_options['plugins'] as $plugin ) {
                if( $plugin == $our_plugin ) {
                    update_option('wpjp_currentversion', self::$_currentversion);
                    include_once WPJOBPORTAL_PLUGIN_PATH . 'includes/updates/updates.php';
                    WPJOBPORTALupdates::checkUpdates('246');

                	// restore colors data
		            require(WPJOBPORTAL_PLUGIN_PATH . 'includes/css/style_color.php');

			        // restore colors data end
                    WPJOBPORTALincluder::getJSModel('wpjobportal')->wpjobportalCheckLicenseStatus();
                }
            }
        }
    }

    function WPJPreplaceString(&$filestring, $wpjobportal_colorNo, $wpjobportal_data) {
        if (wpjobportalphplib::wpJP_strstr($filestring, '$wpjobportal_color' . $wpjobportal_colorNo)) {
            $wpjobportal_path1 = wpjobportalphplib::wpJP_strpos($filestring, '$wpjobportal_color' . $wpjobportal_colorNo);
            $wpjobportal_path2 = wpjobportalphplib::wpJP_strpos($filestring, ';', $wpjobportal_path1);
            $filestring = substr_replace($filestring, '$wpjobportal_color' . $wpjobportal_colorNo . ' = "' . $wpjobportal_data['color' . $wpjobportal_colorNo] . '";', $wpjobportal_path1, $wpjobportal_path2 - $wpjobportal_path1 + 1);
        }
    }

    function getWPJPCurrentTheme() {
        $wpjobportal_optiondata = get_option('wpjp_set_theme_colors');
        $wpjobportal_theme = array();
        if (!empty($wpjobportal_optiondata)) {
            $filestring = json_decode($wpjobportal_optiondata, true);
            $wpjobportal_theme['color1'] = $filestring['color1'];
            $wpjobportal_theme['color2'] = $filestring['color2'];
            $wpjobportal_theme['color3'] = $filestring['color3'];
        }
        return $wpjobportal_theme;
    }

    function wpjobportal_activation_redirect(){
        if (get_option('wpjobportal_do_activation_redirect') == true) {
            update_option('wpjobportal_do_activation_redirect',false);
            exit(esc_url(wp_redirect(admin_url('admin.php?page=wpjobportal_postinstallation&wpjobportallt=quickstart'))));
        }
    }

    function wpjobportal_activate() {
        include_once 'includes/activation.php';
        WPJOBPORTALactivation::wpjobportal_activate();
		add_option('wpjobportal_do_activation_redirect', true);
    }

    function wpjobportal_deactivate() {
        include_once 'includes/deactivation.php';
        WPJOBPORTALdeactivation::wpjobportal_deactivate();
    }

    /*
     * Include the required files
     */

    function wpjobportal_includes() {
        // php 8.1 issues
        require_once 'includes/wpjobportalphplib.php';
        if (is_admin()) {
            include_once 'includes/wpjobportaladmin.php';
        }
        include_once 'includes/wpjobportal-hooks.php';
        include_once 'includes/captcha.php';
        include_once 'includes/recaptchalib.php';
        include_once 'includes/layout.php';
        include_once 'includes/pagination.php';
        include_once 'includes/includer.php';
        include_once 'includes/formfield.php';
        include_once 'includes/request.php';
        include_once 'includes/wpjobportal-wc.php';
        include_once 'includes/formhandler.php';
        include_once 'includes/ajax.php';
        require_once 'includes/constants.php';
        require_once 'includes/messages.php';
        require_once 'includes/wpjobportaldb.php';
        include_once 'includes/shortcodes.php';
        include_once 'includes/paramregister.php';
        include_once 'includes/breadcrumbs.php';
        include_once 'includes/dashboardapi.php';
        //Widgets TO include
        include_once 'includes/widgets/searchjobs.php';
        // include_once 'includes/addon-updater/wpjobportalupdater.php';
        // check for elementor to include intergation files
        if ( class_exists( '\Elementor\Plugin' ) ) {
            include_once 'includes/elementor-addon.php';
        }

    }

    /*
     * Localization
     */

    // public function wpjobportal_load_plugin_textdomain() {
    //     //if(!load_plugin_textdomain('wp-job-portal')){
    //         load_plugin_textdomain('wp-job-portal', false, wpjobportalphplib::wpJP_dirname(plugin_basename(__FILE__)) . '/languages/');
    //     /*}else{
    //         load_plugin_textdomain('wp-job-portal');
    //     }*/
    // }

    /*
     * function for the Style Sheets
     */

    static function wpjobportal_addStyleSheets() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('wpjobportal-commonjs', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/common.js');
        wp_enqueue_script('wpjobportal-res-tables', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/responsivetable.js');
        wp_enqueue_script('jquery-ui-accordion');
         wp_enqueue_style('wpjobportal-tokeninput', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/tokeninput.css');
        wp_enqueue_style('wpjobportal-fontawesome', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/font-awesome.css');
        if(wpjobportal::$wpjobportal_theme_chk == 1){
            $wpjobportal_class_prefix = 'wpj-jp';
        }else{
            $wpjobportal_class_prefix = 'wjportal';
        }
        $wpjobportal_nonce_value = wp_create_nonce("wp-job-portal-nonce");
        wp_localize_script('wpjobportal-commonjs', 'common', array('ajaxurl' => esc_url_raw(admin_url('admin-ajax.php')),'js_nonce'=>$wpjobportal_nonce_value,'insufficient_credits' => esc_html(__('You have insufficient credits, you can not perform this action','wp-job-portal')),'theme_chk_prefix'=> $wpjobportal_class_prefix,'theme_chk_number'=>wpjobportal::$wpjobportal_theme_chk,'theme_chk_flag'=>wpjobportal::$wpjobportal_theme_chk_flag, 'theme_image' => WPJOBPORTAL_IMAGE,'terms_conditions' => esc_html(__('Please Accept Terms And Conditions So You Can Proceed','wp-job-portal')) ,'company_feature_text' => esc_html(__('Are You Sure You Want To Feature this Company?','wp-job-portal')),'job_feature_text' => esc_html(__('Are You Sure You Want To Feature this Job?','wp-job-portal')),'resume_feature_text' => esc_html(__('Are You Sure You Want To Feature this Resume?','wp-job-portal'))));

        wp_enqueue_script('wpjobportal-formvalidator', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/jquery.form-validator.js');
        if(wpjobportal::$wpjobportal_theme_chk == 0 || wpjobportal::$_common->wpjp_isadmin()){
            wp_enqueue_script('wpjobportal-tokeninput', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/jquery.tokeninput.js');
        }else{
            # token  input For admin side
         wp_enqueue_script('wpjobportal-tokeninput', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/jquery.tokeninput.js');
        }
        wp_enqueue_script('chosen', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/chosen/chosen.jquery.min.js');
        // wp_enqueue_script('bootstrap-min', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js');
    }

    /*
     * function to get the pageid from the wpoptions
     */

    public static function wpjobportal_getPageidForWidgets() {
        $wpjobportal_pageid = wpjobportal::$_db->get_var("SELECT configvalue FROM ".wpjobportal::$_db->prefix."wj_portal_config WHERE configname = 'default_pageid'");
        if ( is_numeric($wpjobportal_pageid) && $wpjobportal_pageid > 0){
            $wpjobportal_id = $wpjobportal_pageid;
        }else{ // fall back. just in case of no config set
            $wpjobportal_id = wpjobportal::wpjobportal_getPageid();
        }
        return $wpjobportal_id;
    }

    public static function wpjobportal_getPageid() {
        if(wpjobportal::$_pageid != ''){
            return wpjobportal::$_pageid;
        }else{
            $wpjobportal_pageid = WPJOBPORTALrequest::getVar('page_id','GET');
            if($wpjobportal_pageid){
                return $wpjobportal_pageid;
            }else{ // in case of categories popup
                $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme');
                if($wpjobportal_module == 'category'){
                    $wpjobportal_pageid = WPJOBPORTALrequest::getVar('page_id','POST');
                    if($wpjobportal_pageid)
                        return $wpjobportal_pageid;
                }

                // to fetch page id from post in case of form submission & payment hooks
                $wpjobportal_pageid = WPJOBPORTALrequest::getVar('wpjobportalpageid');
                if($wpjobportal_pageid){
                    return $wpjobportal_pageid;
                }
                $wpjobportal_pageid = get_queried_object_id(); // current pageid
                if($wpjobportal_pageid != '' && is_numeric($wpjobportal_pageid)){
                    return $wpjobportal_pageid;
                }
                    // to get page id from url
                $wpjobportal_actual_link = rtrim($_SERVER['REQUEST_URI'], '/');
                $wpjobportal_pageid = url_to_postid($wpjobportal_actual_link);
                if($wpjobportal_pageid != '' && is_numeric($wpjobportal_pageid)){
                    return $wpjobportal_pageid;
                }

            }
            $wpjobportal_id = 0;
            $wpjobportal_pageid = wpjobportal::$_db->get_var("SELECT configvalue FROM `".wpjobportal::$_db->prefix."wj_portal_config` WHERE configname = 'default_pageid'");
            if ($wpjobportal_pageid)
                $wpjobportal_id = $wpjobportal_pageid;
            return $wpjobportal_id;
        }
    }

    public static function wpjobportal_setPageID($wpjobportal_id) {
        wpjobportal::$_pageid = $wpjobportal_id;
    }

     function reset_wpjobportal_aadon_query(){
        wpjobportal::$_addon_query = array('select'=>'','join'=>'','where'=>'');
    }


    /*
     * function to parse the spaces in given string
     */

    public static function parseSpaces($wpjobportal_string) {
        // php 8 issue for str_replce
        if($wpjobportal_string == ''){
            return $wpjobportal_string;
        }
        return wpjobportalphplib::wpJP_str_replace('%20', ' ', $wpjobportal_string);
    }

    public static function tagfillin($wpjobportal_string) {
        // php 8 issue for str_replce
        if($wpjobportal_string == ''){
            return $wpjobportal_string;
        }

        return wpjobportalphplib::wpJP_str_replace(' ', '_', $wpjobportal_string);
    }

    public static function tagfillout($wpjobportal_string) {
        // php 8 issue for str_replce
        if($wpjobportal_string == ''){
            return $wpjobportal_string;
        }
        return wpjobportalphplib::wpJP_str_replace('_', ' ', $wpjobportal_string);
    }

    static function wpjobportal_sanitizeData($wpjobportal_data){
        if($wpjobportal_data == null){
            return $wpjobportal_data;
        }
        if(is_array($wpjobportal_data)){
            return map_deep( $wpjobportal_data, 'sanitize_text_field' );
        }else{
            return sanitize_text_field( $wpjobportal_data );
        }
    }

    static function wpjobportal_makeUrl($wpjobportal_args = array()){
        global $wp_rewrite;
        // firest check the args if page id is set
        if(isset($wpjobportal_args['wpjobportalpageid']) && is_numeric($wpjobportal_args['wpjobportalpageid'])){
            $wpjobportal_pageid = $wpjobportal_args['wpjobportalpageid'];
        }else{
            // if no page id in $wpjobportal_args then check GET POST and wpjobportal::$_data['satized_args']
            $wpjobportal_pageid = WPJOBPORTALrequest::getVar('wpjobportalpageid');
            // to check current wordpress object post/page id
            if(empty($wpjobportal_pageid)){
                $wpjobportal_pageid = get_queried_object_id(); // current pageid
            }

            // to get page id from url
            if(empty($wpjobportal_pageid)){
                $wpjobportal_actual_link = rtrim($_SERVER['REQUEST_URI'], '/');
                $wpjobportal_pageid = url_to_postid($wpjobportal_actual_link);
            }
        }

        if(empty($wpjobportal_pageid)){
            $wpjobportal_pageid = wpjobportal::$_db->get_var("SELECT configvalue FROM `".wpjobportal::$_db->prefix."wj_portal_config` WHERE configname = 'default_pageid'");
        }else{
			// page will open in defaul page (not homepage)
            $homepageid = get_option('page_on_front');
            if($homepageid == $wpjobportal_pageid){
                $wpjobportal_pageid = wpjobportal::$_db->get_var("SELECT configvalue FROM `".wpjobportal::$_db->prefix."wj_portal_config` WHERE configname = 'default_pageid'");
            }
        }

        if(is_numeric($wpjobportal_pageid)){
            $wpjobportal_permalink = get_the_permalink($wpjobportal_pageid);
        }else{
            if(isset($wpjobportal_args['wpjobportalpageid']) && is_numeric($wpjobportal_args['wpjobportalpageid'])){
                $wpjobportal_permalink = get_the_permalink($wpjobportal_args['wpjobportalpageid']);
            }else{
                $wpjobportal_permalink = get_the_permalink();
            }
        }
        if (!$wp_rewrite->using_permalinks()){
            if(!wpjobportalphplib::wpJP_strstr($wpjobportal_permalink, 'page_id') && !wpjobportalphplib::wpJP_strstr($wpjobportal_permalink, '?p=')) {
                //$page['page_id'] = get_option('page_on_front');
				$page['page_id'] = wpjobportal::$_db->get_var("SELECT configvalue FROM `".wpjobportal::$_db->prefix."wj_portal_config` WHERE configname = 'default_pageid'");
                $wpjobportal_args = $page + $wpjobportal_args;
            }
            $redirect_url = add_query_arg($wpjobportal_args,$wpjobportal_permalink);
            return $redirect_url;
        }

        if(isset($wpjobportal_args['wpjobportalme']) && isset($wpjobportal_args['wpjobportallt'])){
            // Get the original query parts
            $redirect = @wp_parse_url($wpjobportal_permalink);
            if (!isset($redirect['query']))
                $redirect['query'] = '';

            if(wpjobportalphplib::wpJP_strstr($wpjobportal_permalink, '?')){ // if variable exist
                $redirect_array = wpjobportalphplib::wpJP_explode('?', $wpjobportal_permalink);
                $_redirect = $redirect_array[0];
            }else{
                $_redirect = $wpjobportal_permalink;
            }
            if($_redirect != ''){
                if($_redirect[wpjobportalphplib::wpJP_strlen($_redirect) - 1] == '/'){
                    $_redirect = wpjobportalphplib::wpJP_substr($_redirect, 0, wpjobportalphplib::wpJP_strlen($_redirect) - 1);
                }
            }
            // If is layout
            if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
            if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ) {
                $creds = request_filesystem_credentials( site_url() );
                wp_filesystem( $creds );
            }

            $changename = false;
            if($wp_filesystem->exists(WP_PLUGIN_DIR.'/js-vehicle-manager/js-vehicle-manager.php')){
                $changename = true;
            }
            if($wp_filesystem->exists(WP_PLUGIN_DIR.'/js-support-ticket/js-support-ticket.php')){
                $changename = true;
            }

            if($wp_filesystem->exists(WP_PLUGIN_DIR.'/js-jobs/js-jobs.php')){
                $changename = true;
            }

            if (isset($wpjobportal_args['wpjobportallt'])) {
                $wpjobportal_layout = '';
                ///echo $wpjobportal_args['wpjobportallt'].'-';
                $wpjobportal_layout = WPJOBPORTALincluder::getJSModel('slug')->getSlugFromFileName($wpjobportal_args['wpjobportallt'],$wpjobportal_args['wpjobportalme']);
                global $wp_rewrite;
                $wpjobportal_slug_prefix = WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue('home_slug_prefix');
                if($_redirect == site_url()){
                    $wpjobportal_layout = $wpjobportal_slug_prefix.$wpjobportal_layout;
                }

                $_redirect .= '/' . $wpjobportal_layout;
            }
            // If is jobid
            if (isset($wpjobportal_args['jobid'])) {
                $_redirect .= '/' . $wpjobportal_args['jobid'];
            }
            // If is list
            if (isset($wpjobportal_args['list'])) {
                $_redirect .= '/' . $wpjobportal_args['list'];
            }
            // If is wpjobportal_id
            if (isset($wpjobportal_args['wpjobportalid'])) {
                $wpjobportal_id = $wpjobportal_args['wpjobportalid'];
                //$wpjobportal_layout = wpjobportalphplib::wpJP_str_replace('jm-', '', $wpjobportal_layout);
                if($wpjobportal_args['wpjobportallt'] == 'viewjob'){
                    $wpjobportal_job_seo = WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue('job_seo');
                    if(!empty($wpjobportal_job_seo)){
                        $wpjobportal_job_seo = WPJOBPORTALincluder::getJSModel('job')->makeJobSeo($wpjobportal_job_seo , $wpjobportal_id);
                        if($wpjobportal_job_seo != ''){
                            $wpjobportal_id = WPJOBPORTALincluder::getJSModel('common')->parseID($wpjobportal_id);
                            $wpjobportal_id = $wpjobportal_job_seo.'-'.$wpjobportal_id;
                        }
                    }
                }elseif($wpjobportal_args['wpjobportallt'] == 'viewcompany'){
                    $wpjobportal_company_seo = WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue('company_seo');
                    if(!empty($wpjobportal_company_seo)){
                        $wpjobportal_company_seo = WPJOBPORTALincluder::getJSModel('company')->makeCompanySeo($wpjobportal_company_seo , $wpjobportal_id);
                        if($wpjobportal_company_seo != ''){
                            $wpjobportal_id = WPJOBPORTALincluder::getJSModel('common')->parseID($wpjobportal_id);
                            $wpjobportal_id = $wpjobportal_company_seo.'-'.$wpjobportal_id;
                        }
                    }
                }elseif($wpjobportal_args['wpjobportallt'] == 'viewresume'){
                    $wpjobportal_resume_seo = WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue('resume_seo');
                    if(!empty($wpjobportal_resume_seo)){
                        $wpjobportal_resume_seo = WPJOBPORTALincluder::getJSModel('resume')->makeResumeSeo($wpjobportal_resume_seo , $wpjobportal_id);
                        if($wpjobportal_resume_seo != ''){
                            $wpjobportal_id = WPJOBPORTALincluder::getJSModel('common')->parseID($wpjobportal_id);
                            $wpjobportal_id = $wpjobportal_resume_seo.'-'.$wpjobportal_id;
                        }
                    }
                }

                $_redirect .= '/' . $wpjobportal_id;
            }

            // If is ta
            if (isset($wpjobportal_args['ta'])) {
                $_redirect .= '/' . $wpjobportal_args['ta'];
            }
            // If is ta
            if (isset($wpjobportal_args['viewtype'])) { // resume list or grid view
                $_redirect .= '/vt-' . $wpjobportal_args['viewtype'];
            }
            // If is jsscid
            if (isset($wpjobportal_args['jsscid'])) {
                $_redirect .= '/sc-' . $wpjobportal_args['jsscid'];
            }
            // If is category
            if (isset($wpjobportal_args['category'])) {
                $wpjobportal_category = $wpjobportal_args['category'];
                $wpjobportal_array = wpjobportalphplib::wpJP_explode('-', $wpjobportal_category);
                $wpjobportal_count = count($wpjobportal_array);
                $wpjobportal_id = $wpjobportal_array[$wpjobportal_count - 1];
                unset($wpjobportal_array[$wpjobportal_count - 1]);
                $wpjobportal_string = implode("-", $wpjobportal_array);
                $finalstring = $wpjobportal_string . '_10' . $wpjobportal_id;
                $_redirect .= '/' . $finalstring;
            }
            // If is tags
            if (isset($wpjobportal_args['tags'])) {
                $wpjobportal_tags = $wpjobportal_args['tags'];
                $finalstring = 'tags' . '_' . $wpjobportal_tags;
                $_redirect .= '/' . $finalstring;
            }
            // If is jobtype
            if (isset($wpjobportal_args['jobtype'])) {
                $wpjobportal_jobtype = $wpjobportal_args['jobtype'];
                $wpjobportal_array = wpjobportalphplib::wpJP_explode('-', $wpjobportal_jobtype);
                $wpjobportal_count = count($wpjobportal_array);
                $wpjobportal_id = $wpjobportal_array[$wpjobportal_count - 1];
                unset($wpjobportal_array[$wpjobportal_count - 1]);
                $wpjobportal_string = implode("-", $wpjobportal_array);
                $finalstring = $wpjobportal_string . '_11' . $wpjobportal_id;
                $_redirect .= '/' . $finalstring;
            }
            // If is company
            if (isset($wpjobportal_args['company'])) {
                $wpjobportal_company = $wpjobportal_args['company'];
                $wpjobportal_array = wpjobportalphplib::wpJP_explode('-', $wpjobportal_company);
                $wpjobportal_count = count($wpjobportal_array);
                $wpjobportal_id = $wpjobportal_array[$wpjobportal_count - 1];
                unset($wpjobportal_array[$wpjobportal_count - 1]);
                $wpjobportal_string = implode("-", $wpjobportal_array);
                $finalstring = $wpjobportal_string . '_12' . $wpjobportal_id;
                $_redirect .='/' . $finalstring;
            }
            // If is search
            if (isset($wpjobportal_args['search'])) {
                $wpjobportal_search = $wpjobportal_args['search'];
                $wpjobportal_array = wpjobportalphplib::wpJP_explode('-', $wpjobportal_search);
                $wpjobportal_count = count($wpjobportal_array);
                $wpjobportal_id = $wpjobportal_array[$wpjobportal_count - 1];
                unset($wpjobportal_array[$wpjobportal_count - 1]);
                $wpjobportal_string = implode("-", $wpjobportal_array);
                $finalstring = $wpjobportal_string . '_13' . $wpjobportal_id;
                $_redirect .='/' . $finalstring;
            }
            // If is city
            if (isset($wpjobportal_args['city'])) {
                $alias = WPJOBPORTALincluder::getJSModel('city')->getCityNamebyId($wpjobportal_args['city']);
                $alias = WPJOBPORTALincluder::getJSModel('common')->removeSpecialCharacter($alias);
                $_redirect .= '/'.urlencode($alias).'_14' . $wpjobportal_args['city'];
            }

            // If is suggested jobs resume id
            if (isset($wpjobportal_args['aisuggestedjobs_resume'])) {
                $wpjobportal_aisuggestedjobs_resume = $wpjobportal_args['aisuggestedjobs_resume'];
                $wpjobportal_array = wpjobportalphplib::wpJP_explode('-', $wpjobportal_aisuggestedjobs_resume);
                $wpjobportal_count = count($wpjobportal_array);
                $wpjobportal_id = $wpjobportal_array[$wpjobportal_count - 1];
                unset($wpjobportal_array[$wpjobportal_count - 1]);
                $wpjobportal_string = implode("-", $wpjobportal_array);
                $finalstring = $wpjobportal_string . '_15' . $wpjobportal_id;
                $_redirect .='/' . $finalstring;
            }

            // If is suggested resumes job id
            if (isset($wpjobportal_args['aisuggestedresumes_job'])) {
                $wpjobportal_aisuggestedresumes_job = $wpjobportal_args['aisuggestedresumes_job'];
                $wpjobportal_array = wpjobportalphplib::wpJP_explode('-', $wpjobportal_aisuggestedresumes_job);
                $wpjobportal_count = count($wpjobportal_array);
                $wpjobportal_id = $wpjobportal_array[$wpjobportal_count - 1];
                unset($wpjobportal_array[$wpjobportal_count - 1]);
                $wpjobportal_string = implode("-", $wpjobportal_array);
                $finalstring = $wpjobportal_string . '_16' . $wpjobportal_id;
                $_redirect .='/' . $finalstring;
            }


            // If is sortby
            if (isset($wpjobportal_args['sortby'])) {
                //$_redirect .= '/sortby-' . $wpjobportal_args['sortby'];
                $_redirect .= '/' . $wpjobportal_args['sortby'];
            }

            if(isset($wpjobportal_args['userpackageid'])) {
                $_redirect .= '/package-' . $wpjobportal_args['userpackageid'];
            }

            // login redirect
            if (isset($wpjobportal_args['wpjobportalredirecturl'])) {
                //$_redirect .= '/sortby-' . $wpjobportal_args['sortby'];
                $_redirect .= '/' . $wpjobportal_args['wpjobportalredirecturl'];
            }
           return $_redirect;
        }else{ // incase of form
            $redirect_url = add_query_arg($wpjobportal_args,$wpjobportal_permalink);
            return $redirect_url;
        }

    }

    function wpjobportal_new_site($wpjobportal_new_site){
        $pluginname = plugin_basename(__FILE__);
        if(is_plugin_active_for_network($pluginname)){
            include_once 'includes/activation.php';
            switch_to_blog($wpjobportal_new_site->blog_id);
            WPJOBPORTALactivation::wpjobportal_activate();
            restore_current_blog();
        }
    }

    function wpjobportal_new_blog($wpjobportal_blog_id, $wpjobportal_user_id, $domain, $wpjobportal_path, $site_id, $meta){
        $pluginname = plugin_basename(__FILE__);
        if(is_plugin_active_for_network($pluginname)){
            include_once 'includes/activation.php';
            switch_to_blog($wpjobportal_blog_id);
            WPJOBPORTALactivation::wpjobportal_activate();
            restore_current_blog();
        }
    }

    function wpjobportal_delete_site($wpjobportal_tables){
        include_once 'includes/deactivation.php';
        $wpjobportal_tablestodrop = WPJOBPORTALdeactivation::wpjobportal_tables_to_drop();
        foreach($wpjobportal_tablestodrop as $wpjobportal_tablename){
            $wpjobportal_tables[] = $wpjobportal_tablename;
        }
        return $wpjobportal_tables;
    }

    static function checkAddonActiveOrNot($for){
        if(in_array($for, wpjobportal::$_active_addons)){
            return true;
        }
        return false;
    }

    static function bjencode($wpjobportal_array){
        return base64_encode(wp_json_encode($wpjobportal_array));
    }

    static function bjdecode($wpjobportal_array){
        return json_decode(base64_decode($wpjobportal_array));
    }

    static function wpjobportal_redirectUrl($wpjobportal_entityaction,$wpjobportal_id=0){
        $wpjobportal_isadmin = wpjobportal::$_common->wpjp_isadmin();
        if(wpjobportal::$_common->wpjp_isadmin()){
            switch($wpjobportal_entityaction){
                case 'job.success':
                    $wpjobportal_url = admin_url("admin.php?page=wpjobportal_job&wpjobportallt=jobs");
                break;
                case 'job.fail':
                    $wpjobportal_url = admin_url("admin.php?page=wpjobportal_job&wpjobportallt=formjob");
                break;
                case 'company.success':
                    $wpjobportal_url = admin_url("admin.php?page=wpjobportal_company");
                break;
                case 'company.fail':
                    $wpjobportal_url = admin_url("admin.php?page=wpjobportal_company&wpjobportallt=formcompany");
                break;
                case 'resume.success':
                    $wpjobportal_url = admin_url("admin.php?page=wpjobportal_resume");
                break;
                case 'resume.fail':
                    $wpjobportal_url = admin_url("admin.php?page=wpjobportal_resume");
                break;
                default:
                    $wpjobportal_url = null;
                break;
            }
        }else{
            switch($wpjobportal_entityaction){
                case 'job.success':
                    if(WPJOBPORTALincluder::getObjectClass('user')->isguest()){
                        $wpjobportal_pageid = WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue('visitor_add_job_redirect_page');
                        $wpjobportal_url = get_the_permalink($wpjobportal_pageid);
                    }else{
                        $wpjobportal_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'myjobs'));
                    }
                break;
                case 'job.fail':
                    $wpjobportal_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'addjob'));
                break;
                case 'company.success':
                    if(in_array('credits', wpjobportal::$_active_addons)){
                        if(wpjobportal::$_config->getConfigValue('submission_type') == 2){
                            $wpjobportal_url = apply_filters('wpjobportal_addons_credit_save_perlisting',false,wpjobportal::$_data['id'],'paycompany');
                        }else{
                            $wpjobportal_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'mycompanies'));
                        }
                    }else{
                        $wpjobportal_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'mycompanies'));
                    }
                break;
                case 'company.fail':
                    $wpjobportal_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'mycompanies'));
                break;
                case 'resume.success':
                    if(in_array('credits', wpjobportal::$_active_addons)){
                        if(wpjobportal::$_config->getConfigValue('submission_type') == 2){
                            # perlisting Type
                            $wpjobportal_url = apply_filters('wpjobportal_addons_credit_save_perlisting',false,wpjobportal::$_data['id'],'payresume');
                        }else{
                            $wpjobportal_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'myresumes'));
                        }
                    }else{
                        $wpjobportal_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'myresumes'));
                    }
                break;
                case 'resume.fail':
                    $wpjobportal_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'addresume'));
                break;
                default:
                    $wpjobportal_url = null;
                break;
            }
        }
        return $wpjobportal_url;
    }

    function jsjp_handle_search_form_data(){
        WPJOBPORTALincluder::getObjectClass('handlesearchcookies');
    }
    function jsjp_safe_style_css($custom_styles){
        $wpjobportal_styles[] = 'display';
        $wpjobportal_styles[] = 'color';
        $wpjobportal_styles[] = 'width';
        $wpjobportal_styles[] = 'max-width';
        $wpjobportal_styles[] = 'min-width';
        $wpjobportal_styles[] = 'height';
        $wpjobportal_styles[] = 'min-height';
        $wpjobportal_styles[] = 'max-height';
        $wpjobportal_styles[] = 'background-color';
        $wpjobportal_styles[] = 'border';
        $wpjobportal_styles[] = 'border-bottom';
        $wpjobportal_styles[] = 'border-top';
        $wpjobportal_styles[] = 'border-left';
        $wpjobportal_styles[] = 'border-right';
        $wpjobportal_styles[] = 'border-color';
        $wpjobportal_styles[] = 'padding';
        $wpjobportal_styles[] = 'padding-top';
        $wpjobportal_styles[] = 'padding-bottom';
        $wpjobportal_styles[] = 'padding-left';
        $wpjobportal_styles[] = 'padding-right';
        $wpjobportal_styles[] = 'margin';
        $wpjobportal_styles[] = 'margin-top';
        $wpjobportal_styles[] = 'margin-bottom';
        $wpjobportal_styles[] = 'margin-left';
        $wpjobportal_styles[] = 'margin-right';
        $wpjobportal_styles[] = 'background';
        $wpjobportal_styles[] = 'font-weight';
        $wpjobportal_styles[] = 'font-size';
        $wpjobportal_styles[] = 'text-align';
        $wpjobportal_styles[] = 'text-decoration';
        $wpjobportal_styles[] = 'text-transform';
        $wpjobportal_styles[] = 'line-height';
        $wpjobportal_styles[] = 'visibility';
        $wpjobportal_styles[] = 'cellspacing';
        $wpjobportal_styles[] = 'data-id';
        $wpjobportal_styles[] = 'cursor';
        $wpjobportal_styles[] = 'vertical-align';
        $wpjobportal_styles[] = 'float';
        $wpjobportal_styles[] = 'position';
        $wpjobportal_styles[] = 'left';
        $wpjobportal_styles[] = 'right';
        $wpjobportal_styles[] = 'bottom';
        $wpjobportal_styles[] = 'top';
        $wpjobportal_styles[] = 'z-index';
        $wpjobportal_styles[] = 'overflow';
        return array_merge($wpjobportal_styles, $custom_styles);
    }

    function jsjp_handle_delete_cookies(){

        if(isset($_COOKIE['jsjp_addon_return_data'])){
            wpjobportalphplib::wpJP_setcookie('jsjp_addon_return_data' , '' , time() - 3600, COOKIEPATH);
            if ( SITECOOKIEPATH != COOKIEPATH ){
                wpjobportalphplib::wpJP_setcookie('jsjp_addon_return_data' , '' , time() - 3600, SITECOOKIEPATH);
            }
        }

        if(isset($_COOKIE['jsjp_addon_install_data'])){
            wpjobportalphplib::wpJP_setcookie('jsjp_addon_install_data' , '' , time() - 3600);
        }
    }

    public static function removeusersearchcookies(){
        if(isset($_COOKIE['jsjp_jobportal_search_data'])){
            wpjobportalphplib::wpJP_setcookie('jsjp_jobportal_search_data' , '' , time() - 3600 , COOKIEPATH);
            if ( SITECOOKIEPATH != COOKIEPATH ){
                wpjobportalphplib::wpJP_setcookie('jsjp_jobportal_search_data' , '' , time() - 3600 , SITECOOKIEPATH);
            }
        }
    }

    public static function wpjobportal_setusersearchcookies($cookiesval , $wpjobportal_jsjp_search_array){
        if(!$cookiesval)
            return false;
        $wpjobportal_data = wp_json_encode( $wpjobportal_jsjp_search_array );
        $wpjobportal_data = wpjobportalphplib::wpJP_safe_encoding($wpjobportal_data);
        wpjobportalphplib::wpJP_setcookie('jsjp_jobportal_search_data' , $wpjobportal_data , time() + 600 , COOKIEPATH);
        if ( SITECOOKIEPATH != COOKIEPATH ){
            wpjobportalphplib::wpJP_setcookie('jsjp_jobportal_search_data' , $wpjobportal_data , time() + 600 , SITECOOKIEPATH);
        }
    }

    function jsjp_delete_expire_session_data(){
        wpjobportal::$_db->query('DELETE  FROM '.wpjobportal::$_db->prefix.'wj_portal_jswjsessiondata WHERE sessionexpire < "'. time() .'"');
    }
    static function wpjobportal_getVariableValue($wpjobportal_text_string){
        $wpjobportal_translations = get_translations_for_domain( 'wp-job-portal' );
        $wpjobportal_translation = $wpjobportal_translations->translate( $wpjobportal_text_string );
        return $wpjobportal_translation;
    }

    function wpjobportal_LoadWpCoreFiles() {
        add_action('wpjobportal_load_wp_plugin_file', array($this,'wpjobportal_load_wp_plugin_file') );
        add_action('wpjobportal_load_wp_admin_file', array($this,'wpjobportal_load_wp_admin_file') );
        add_action('wpjobportal_load_wp_file', array($this,'wpjobportal_load_wp_file') );
        add_action('wpjobportal_load_wp_pcl_zip', array($this,'wpjobportal_load_wp_pcl_zip') );
        add_action('wpjobportal_load_wp_upgrader', array($this,'wpjobportal_load_wp_upgrader') );
        add_action('wpjobportal_load_wp_ajax_upgrader_skin', array($this,'wpjobportal_load_wp_ajax_upgrader_skin') );
        add_action('wpjobportal_load_wp_plugin_upgrader', array($this,'wpjobportal_load_wp_plugin_upgrader') );
        add_action('wpjobportal_load_wp_translation_install', array($this,'wpjobportal_load_wp_translation_install') );
        add_action('wpjobportal_load_wp_users', array($this,'wpjobportal_load_wp_users') );
        add_action('wpjobportal_load_wp_image', array($this,'wpjobportal_load_wp_image') );
        add_action('wpjobportal_load_phpass', array($this,'wpjobportal_load_phpass') );
        //add_filter('cron_schedules',array($this,'wpjobportal_customschedules'));
    }

    function wpjobportal_load_wp_plugin_file() {
        $wp_admin_url = admin_url('includes/plugin.php');
        $wp_admin_path = str_replace(site_url('/'), ABSPATH, $wp_admin_url);
        if(strpos($wp_admin_path, "http") !== false) {
            $wp_admin_path = ABSPATH . 'wp-admin/includes/plugin.php';
        }
        require_once($wp_admin_path);
    }

    function wpjobportal_load_wp_admin_file() {
        $wp_admin_url = admin_url('includes/admin.php');
        $wp_admin_path = str_replace(site_url('/'), ABSPATH, $wp_admin_url);
        if(strpos($wp_admin_path, "http") !== false) {
            $wp_admin_path = ABSPATH . 'wp-admin/includes/admin.php';
        }
        require_once($wp_admin_path);
    }

    function wpjobportal_load_wp_file() {
        $wp_admin_url = admin_url('includes/file.php');
        $wp_admin_path = str_replace(site_url('/'), ABSPATH, $wp_admin_url);
        if(strpos($wp_admin_path, "http") !== false) {
            $wp_admin_path = ABSPATH . 'wp-admin/includes/file.php';
        }
        require_once($wp_admin_path);
    }

    function wpjobportal_load_wp_pcl_zip() {
        $wp_admin_url = admin_url('includes/class-pclzip.php');
        $wp_admin_path = str_replace(site_url('/'), ABSPATH, $wp_admin_url);
        if(strpos($wp_admin_path, "http") !== false) {
            $wp_admin_path = ABSPATH . 'wp-admin/includes/class-pclzip.php';
        }
        require_once($wp_admin_path);
    }

    function wpjobportal_load_wp_ajax_upgrader_skin() {
        $wp_admin_url = admin_url('includes/class-wp-ajax-upgrader-skin.php');
        $wp_admin_path = str_replace(site_url('/'), ABSPATH, $wp_admin_url);
        if(strpos($wp_admin_path, "http") !== false) {
            $wp_admin_path = ABSPATH . 'wp-admin/includes/class-wp-ajax-upgrader-skin.php';
        }
        require_once($wp_admin_path);
    }

    function wpjobportal_load_wp_upgrader() {
        $wp_admin_url = admin_url('includes/class-wp-upgrader.php');
        $wp_admin_path = str_replace(site_url('/'), ABSPATH, $wp_admin_url);
        if(strpos($wp_admin_path, "http") !== false) {
            $wp_admin_path = ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        }
        require_once($wp_admin_path);
    }

    function wpjobportal_load_wp_plugin_upgrader() {
        $wp_admin_url = admin_url('includes/class-plugin-upgrader.php');
        $wp_admin_path = str_replace(site_url('/'), ABSPATH, $wp_admin_url);
        if(strpos($wp_admin_path, "http") !== false) {
            $wp_admin_path = ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';
        }
        require_once($wp_admin_path);
    }

    function wpjobportal_load_wp_translation_install() {
        $wp_admin_url = admin_url('includes/translation-install.php');
        $wp_admin_path = str_replace(site_url('/'), ABSPATH, $wp_admin_url);
        if(strpos($wp_admin_path, "http") !== false) {
            $wp_admin_path = ABSPATH . 'wp-admin/includes/translation-install.php';
        }
        require_once($wp_admin_path);
    }
    function wpjobportal_load_wp_users() {
        $wp_admin_url = admin_url('includes/user.php');
        $wp_admin_path = str_replace(site_url('/'), ABSPATH, $wp_admin_url);
        if(strpos($wp_admin_path, "http") !== false) {
            $wp_admin_path = ABSPATH . 'wp-admin/includes/user.php';
        }
        require_once($wp_admin_path);
    }
    function wpjobportal_load_wp_image() {
        $wp_admin_url = admin_url('includes/image.php');
        $wp_admin_path = str_replace(site_url('/'), ABSPATH, $wp_admin_url);
        if(strpos($wp_admin_path, "http") !== false) {
            $wp_admin_path = ABSPATH . 'wp-admin/includes/image.php';
        }
        require_once($wp_admin_path);
    }
    function wpjobportal_load_phpass() {
        $wp_site_url = site_url('wp-includes/class-phpass.php');
        $wp_site_path = str_replace(site_url('/'), ABSPATH, $wp_site_url);
        if(strpos($wp_site_path, "http") !== false) {
            $wp_site_path = ABSPATH . 'wp-includes/class-phpass.php';
        }
        require_once($wp_site_path);
    }
}

$wpjobportal = new wpjobportal();
// lost your password link hook
add_action( 'login_form_bottom', 'wpjobportaladdLostPasswordLink' );
function wpjobportaladdLostPasswordLink() {
    if(wpjobportal::$wpjobportal_theme_chk == 1){
        $wpjobportal_class_prefix = 'wpj-jp-form';
    }else{
        $wpjobportal_class_prefix = 'wjportal-form';
    }
   return '<a class="'.esc_attr($wpjobportal_class_prefix).'-lost-password" href="'.site_url().'/wp-login.php?action=lostpassword">'. esc_html(__('Lost your password','wp-job-portal')) .'?</a>';
}

add_action('init', 'wpjobportal_custom_init_session', 1);

function wpjobportal_custom_init_session() {
    if(isset($_COOKIE['wpjobportal_apply_visitor'])){
        $wpjobportal_layout = WPJOBPORTALrequest::getVar('wpjobportallt');
        if($wpjobportal_layout != null && $wpjobportal_layout != 'addresume'){ // reset the session id
            wpjobportalphplib::wpJP_setcookie('wpjobportal_apply_visitor' , '' , time() - 3600 , COOKIEPATH);
            if ( SITECOOKIEPATH != COOKIEPATH ){
                wpjobportalphplib::wpJP_setcookie('wpjobportal_apply_visitor' , '' , time() - 3600 , SITECOOKIEPATH);
            }
        }
    }
    if(isset($_SESSION['wp-wpjobportal']) && isset($_SESSION['wp-wpjobportal']['resumeid'])){
       $wpjobportal_layout = WPJOBPORTALrequest::getVar('wpjobportallt');
       if($wpjobportal_layout != null && $wpjobportal_layout != 'addresume'){ // reset the session id
           unset($_SESSION['wp-wpjobportal']);
       }
    }
    // added this defination of nonce to handle admin side layouts
    wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
}

function wpjobportal_register_plugin_styles(){
    wp_enqueue_script('jquery');
    if(wpjobportal::$wpjobportal_theme_chk == 1){
        $wpjobportal_class_prefix = 'wpj-jp';
    } else {
        $wpjobportal_class_prefix = 'wjportal';
    }
    // vars are defined to support job hub and job manager with minimum changes to plugin code.
    $wpjobportal_nonce_value = wp_create_nonce("wp-job-portal-nonce");
    wp_localize_script('wpjobportal-commonjs', 'common', array('ajaxurl' => esc_url_raw(admin_url('admin-ajax.php')),'js_nonce'=>$wpjobportal_nonce_value,'insufficient_credits' => esc_html(__('You have insufficient credits, you can not perform this action','wp-job-portal')),'theme_chk_prefix'=> $wpjobportal_class_prefix,'theme_chk_number'=>wpjobportal::$wpjobportal_theme_chk,'theme_chk_flag'=>wpjobportal::$wpjobportal_theme_chk_flag, 'theme_image' => WPJOBPORTAL_IMAGE,'terms_conditions' => esc_html(__('Please Accept Terms And Conditions So You Can Proceed','wp-job-portal')) ,'company_feature_text' => esc_html(__('Are You Sure You Want To Feature this Company?','wp-job-portal')),'job_feature_text' => esc_html(__('Are You Sure You Want To Feature this Job?','wp-job-portal')),'resume_feature_text' => esc_html(__('Are You Sure You Want To Feature this Resume?','wp-job-portal'))));
    //wp_localize_script('wpjobportal-commonjs', 'common', array('ajaxurl' => esc_url_raw(admin_url('admin-ajax.php')),'js_nonce'=>$wpjobportal_nonce_value ,'insufficient_credits' => esc_html(__('You have insufficient credits, you can not perform this action','wp-job-portal')),'theme_chk_prefix'=> $wpjobportal_class_prefix,'theme_chk_number'=>wpjobportal::$wpjobportal_theme_chk,'pluginurl'=>WPJOBPORTAL_PLUGIN_URL,'cityajaxurl' => admin_url("admin.php?page=wpjobportal_city&action=wpjobportaltask&task=getaddressdatabycityname"),'theme_chk_flag'=>wpjobportal::$wpjobportal_theme_chk_flag, 'theme_image' => WPJOBPORTAL_IMAGE,'terms_conditions' => esc_html(__('Please Accept Terms And Conditions So You Can Proceed','wp-job-portal') )));
    //include_once 'includes/css/style_color.php';
    wp_enqueue_style('wpjobportal-jobseeker-style', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/jobseekercp.css');
    wp_enqueue_style('wpjobportal-employer-style', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/employercp.css');
    wp_enqueue_style('wpjobportal-style', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/style.css');
    //wp_enqueue_style('wpjobportal-color', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/color.css');
    wp_enqueue_style('wpjobportal-star-rating', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/wpjobportalrating.css');
    // wp_enqueue_style('wpjobportal-style-tablet', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/style_tablet.css',array(),'1.1.1','(max-width: 782px)');
    // wp_enqueue_style('wpjobportal-style-mobile-landscape', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/style_mobile_landscape.css',array(),'1.1.1','(max-width: 650px)');
    // wp_enqueue_style('wpjobportal-style-mobile', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/style_mobile.css',array(),'1.1.1','(max-width: 480px)');
    wp_enqueue_style('wpjobportal-chosen-style', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/chosen/chosen.min.css');
    if (is_rtl()) {
        wp_register_style('wpjobportal-style-rtl', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/stylertl.css');
        wp_enqueue_style('wpjobportal-style-rtl');
    }
    //wp_enqueue_style('wpjobportal-css-ie', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/wpjobportal-ie.css');
    //wp_style_add_data( 'wpjobportal-css-ie', 'conditional', 'IE' );
    //wp_enqueue_script('wpjobportal-vue.js','https://cdn.jsdelivr.net/npm/vue/dist/vue.js',array(),false,1);
    //wp_enqueue_script('wpjobportal-vue-components', WPJOBPORTALincluder::getComponentJsUrl('common'),array(),false,1);

     // elementor overides css
    $wpjobportal_is_elementor_edit_mode = false;

    if ( class_exists('\Elementor\Plugin') && \Elementor\Plugin::$instance->editor ) {
        $wpjobportal_is_elementor_edit_mode = \Elementor\Plugin::$instance->editor->is_edit_mode();
    }

    if ($wpjobportal_is_elementor_edit_mode == false) {
        wp_enqueue_style('wpjobportal-elementor-overrides', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/wpjp_elementor_overrides.css');
    }

}

add_action( 'wp_enqueue_scripts', 'wpjobportal_register_plugin_styles' );

function wpjobportal_admin_register_plugin_styles() {
    wp_enqueue_style('wpjobportal-admin-desktop-css', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/wpjobportaladmin_desktop.css',array(),'1.1.1','all');
    // wp_enqueue_style('wpjobportal-admin-tablet-css', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/wpjobportaladmin_tablet.css',array(),'1.1.1','(min-width: 651px) and (max-width: 782px)');
    // wp_enqueue_style('wpjobportal-admin-mobile-css', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/wpjobportaladmin_mobile.css',array(),'1.1.1','(min-width: 481px) and (max-width: 650px)');
    // wp_enqueue_style('wpjobportal-admin-oldmobile-css', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/wpjobportaladmin_oldmobile.css',array(),'1.1.1','(max-width: 480px)');
    if (is_rtl()) {
        wp_register_style('wpjobportal-admincss-rtl', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/wpjobportaladmin_rtl.css');
        wp_enqueue_style('wpjobportal-admincss-rtl');
    }
}
add_action( 'admin_enqueue_scripts', 'wpjobportal_admin_register_plugin_styles' );

add_action( 'wp_head', 'wpjobportal_add_wpjobportal_meta_tags' , 10 );
function wpjobportal_add_wpjobportal_meta_tags(){

    $wpjobportal_layout = WPJOBPORTALrequest::getVar('wpjobportallt');
    if($wpjobportal_layout == 'viewjob' || $wpjobportal_layout == 'viewresume'){
        $wpjobportal_upid = WPJOBPORTALrequest::getVar('wpjobportalid');
        $wpjobportal_id  = WPJOBPORTALincluder::getJSModel('common')->parseID($wpjobportal_upid);
        if(is_numeric($wpjobportal_id) && $wpjobportal_id > 0){
            if($wpjobportal_layout == 'viewjob'){
                $query = "SELECT job.tags,job.metakeywords
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                    WHERE job.id = " . esc_sql($wpjobportal_id);
                $wpjobportal_data = wpjobportaldb::get_row($query);
                if($wpjobportal_data != ''){
                    if($wpjobportal_data->metakeywords != ''){
                        echo '<meta name="keywords"  content="'.esc_html($wpjobportal_data->metakeywords).'">';
                    }
                    if($wpjobportal_data->tags != ''){
                        echo '<meta name="tags"  content="'.esc_html($wpjobportal_data->tags).'">';
                    }
                }
            }else{
                $query = "SELECT resume.tags,resume.keywords
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume
                    WHERE resume.id = " . esc_sql($wpjobportal_id);
                $wpjobportal_data = wpjobportaldb::get_row($query);
                if($wpjobportal_data != ''){
                    if($wpjobportal_data->keywords != ''){
                        echo '<meta name="keywords"  content="'.esc_html($wpjobportal_data->keywords).'">';
                    }
                    if($wpjobportal_data->tags != ''){
                        echo '<meta name="tags"  content="'.esc_html($wpjobportal_data->tags).'">';
                    }
                }
            }

        }
    }

    return;
}


add_action("wp_head","wpjobportal_socialmedia_metatags");
function wpjobportal_socialmedia_metatags(){
    $wpjobportal_defaultDescriptionMeta = 1;
    $wpjobportal_layout = WPJOBPORTALrequest::getVar('wpjobportallt');
    if($wpjobportal_layout == 'viewjob'){
        $wpjobportal_jobid = WPJOBPORTALrequest::getVar('wpjobportalid');
        $wpjobportal_job = WPJOBPORTALincluder::getJSTable('job');
        $wpjobportal_job->load($wpjobportal_jobid);
        if( $wpjobportal_job->id ){
            $title = $wpjobportal_job->title;
            $wpjobportal_description = $wpjobportal_job->metadescription;
            if(empty($wpjobportal_description)){
                $wpjobportal_description = wpjobportalphplib::wpJP_strip_tags($wpjobportal_job->description);
            }
            echo '<meta name= "twitter:card" content="summary" />'."\n";
            echo '<meta job="og:type" content="place" />'."\n";
            echo '<meta name="twitter:title" job="og:title" content="'.esc_html($title).'" />'."\n";
            echo '<meta name="twitter:description" job="og:description" content="'.esc_html($wpjobportal_description).'" />'."\n";
            if(!empty($wpjobportal_job->metakeywords)){
                echo '<meta name="keywords" content="'.esc_html($wpjobportal_job->metakeywords).'"/>'."\n";
            }
            if(!empty($wpjobportal_description)){
                $wpjobportal_defaultDescriptionMeta = 0;
                echo '<meta name="description" content="'.esc_html($wpjobportal_description).'"/>'."\n";
            }
            if(!empty($wpjobportal_job->latitude) && !empty($wpjobportal_job->longitude)){
                echo '<meta job="place:location:latitude" content="'.esc_html($wpjobportal_job->latitude).'">'."\n";
                echo '<meta job="place:location:longitude" content="'.esc_html($wpjobportal_job->longitude).'">'."\n";
            }
        }
    }

    if( $wpjobportal_defaultDescriptionMeta ){
        echo '<meta name="description" content="';
        bloginfo('description');
        echo '" />';
    }
}


// package system popup was not working so commented it out
add_action( 'wp_head', 'wpjobportal_job_posting_structured');
function wpjobportal_job_posting_structured(){
    $wpjobportal_layout = WPJOBPORTALrequest::getVar('wpjobportallt');
    if($wpjobportal_layout == 'viewjob'){
        $wpjobportal_jobid = WPJOBPORTALrequest::getVar('wpjobportalid');
        $wpjobportal_jobid = wpjobportal::$_common->parseID($wpjobportal_jobid);
        $wpjobportal_job = WPJOBPORTALincluder::getJSModel('job')->jobDataStructuredPost($wpjobportal_jobid);
        if(isset($wpjobportal_job->title) && isset($wpjobportal_job->id)){
            $wpjobportal_job_json = WPJOBPORTALincluder::getJSModel('job')->jobDataStructuredPostJSON($wpjobportal_job);
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON-LD structured data output
            echo '<script type="application/ld+json">' . $wpjobportal_job_json . '</script>';
        }
    }
}

add_action( 'admin_enqueue_scripts', 'wpjobportal_admin_register_plugin_styles' );
add_filter('style_loader_tag', 'wpjobportalW3cValidation', 10, 2);
add_filter('script_loader_tag', 'wpjobportalW3cValidation', 10, 2);
function wpjobportalW3cValidation($wpjobportal_tag, $handle) {
    return wpjobportalphplib::wpJP_preg_replace( "/type=['\"]text\/(javascript|css)['\"]/", '', $wpjobportal_tag );
}

function wpjobportal_checkWPJPPluginInfo($wpjobportal_slug){

    if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
    if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ) {
        $creds = request_filesystem_credentials( site_url() );
        wp_filesystem( $creds );
    }

    if($wp_filesystem->exists(WP_PLUGIN_DIR . '/'.$wpjobportal_slug) && is_plugin_active($wpjobportal_slug)){
        $wpjobportal_text = esc_html(__("Activated","wp-job-portal"));
        $wpjobportal_disabled = "disabled";
        $wpjobportal_class = "js-btn-activated";
        $availability = "-1";
    }else if($wp_filesystem->exists(WP_PLUGIN_DIR . '/'.$wpjobportal_slug) && !is_plugin_active($wpjobportal_slug)){
        $wpjobportal_text = esc_html(__("Active Now","wp-job-portal"));
        $wpjobportal_disabled = "";
        $wpjobportal_class = "js-btn-green js-btn-active-now";
        $availability = "1";
    }else if(!$wp_filesystem->exists(WP_PLUGIN_DIR . '/'.$wpjobportal_slug)){
        $wpjobportal_text = esc_html(__("Install Now","wp-job-portal"));
        $wpjobportal_disabled = "";
        $wpjobportal_class = "js-btn-install-now";
        $availability = "0";
    }

    return array("text" => $wpjobportal_text, "disabled" => $wpjobportal_disabled, "class" => $wpjobportal_class, "availability" => $availability);
}

function wpjobportal_employercheckLinks($wpjobportal_name) {
    $wpjobportal_print = false;
    switch ($wpjobportal_name) {
        case 'formcompany': $wpjobportal_visname = 'vis_emformcompany';
        break;
        case 'alljobsappliedapplications': $wpjobportal_visname = 'vis_emalljobsappliedapplications';
        break;
        case 'mycompanies': $wpjobportal_visname = 'vis_emmycompanies';
        break;
        case 'resumesearch': $wpjobportal_visname = 'vis_emresumesearch';
        break;
        case 'formjob': $wpjobportal_visname = 'vis_emformjob';
        break;
        case 'my_resumesearches': $wpjobportal_visname = 'vis_emmy_resumesearches';
        break;
        case 'myjobs': $wpjobportal_visname = 'vis_emmyjobs';
        break;
        case 'formdepartment': $wpjobportal_visname = 'vis_emformdepartment';
        break;
        case 'my_stats': $wpjobportal_visname = 'vis_emmy_stats';
        break;
        case 'empresume_rss': $wpjobportal_visname = 'vis_resume_rss';
        break;
        case 'newfolders': $wpjobportal_visname = 'vis_emnewfolders';
        break;
        case 'empregister': $wpjobportal_visname = 'vis_emempregister';
        break;
        case 'empcredits': $wpjobportal_visname = 'vis_empcredits';
        break;
        case 'empcreditlog': $wpjobportal_visname = 'vis_empcreditlog';
        break;
        case 'emppurchasehistory': $wpjobportal_visname = 'vis_emppurchasehistory';
        break;
        case 'empmessages': $wpjobportal_visname = 'vis_emmessages';
        break;
        case 'empregister': $wpjobportal_visname = 'vis_emregister';
        break;
        case 'empratelist': $wpjobportal_visname = 'vis_empratelist';
        break;
        case 'jobs_graph': $wpjobportal_visname = 'vis_jobs_graph';
        break;
        case 'resume_graph': $wpjobportal_visname = 'vis_resume_graph';
        break;
        case 'box_newestresume': $wpjobportal_visname = 'vis_box_newestresume';
        break;
        case 'box_appliedresume': $wpjobportal_visname = 'vis_box_appliedresume';
        break;
        case 'emploginlogout': $wpjobportal_visname = 'emploginlogout';
        break;
        case 'empmystats': $wpjobportal_visname = 'vis_empmystats';
        break;
        case 'resumebycategory': $wpjobportal_visname = 'vis_emresumebycategory';
        break;
        case 'temp_employer_dashboard_stats_graph': $wpjobportal_visname = 'vis_temp_employer_dashboard_stats_graph';
        break;
        case 'temp_employer_dashboard_useful_links': $wpjobportal_visname = 'vis_temp_employer_dashboard_useful_links';
        break;
        case 'temp_employer_dashboard_applied_resume': $wpjobportal_visname = 'vis_temp_employer_dashboard_applied_resume';
        break;
        case 'temp_employer_dashboard_saved_search': $wpjobportal_visname = 'vis_temp_employer_dashboard_saved_search';
        break;
        case 'temp_employer_dashboard_credits_log': $wpjobportal_visname = 'vis_temp_employer_dashboard_credits_log';
        break;
        case 'temp_employer_dashboard_purchase_history': $wpjobportal_visname = 'vis_temp_employer_dashboard_purchase_history';
        break;
        case 'temp_employer_dashboard_newest_resume': $wpjobportal_visname = 'vis_temp_employer_dashboard_newest_resume';
        break;
        default:$wpjobportal_visname = 'vis_em' . $wpjobportal_name;
        break;
    }

    $wpjobportal_isouruser = WPJOBPORTALincluder::getObjectClass('user')->isWPJOBPortalUser();
    $wpjobportal_isguest = WPJOBPORTALincluder::getObjectClass('user')->isguest();

    $guest = false;

    if($wpjobportal_isguest == true){
        $guest = true;
    }
    if($wpjobportal_isguest == false && $wpjobportal_isouruser == false){
        $guest = true;
    }

    $wpjobportal_config_array = WPJOBPORTALincluder::getJSModel('configuration')->getConfigByFor('emcontrolpanel');

    if ($guest == false) {
        if (WPJOBPORTALincluder::getObjectClass('user')->isemployer()) {
            if (isset($wpjobportal_config_array[$wpjobportal_name]) && $wpjobportal_config_array[$wpjobportal_name] == 1){
               $wpjobportal_print = true;
            }
        }elseif (WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()) {
            if (isset($wpjobportal_config_array["$wpjobportal_visname"]) && $wpjobportal_config_array["$wpjobportal_visname"] == 1){
               $wpjobportal_print = true;
            }
        }else{
            if (isset($wpjobportal_config_array["$wpjobportal_visname"]) && $wpjobportal_config_array["$wpjobportal_visname"] == 1){
               $wpjobportal_print = true;
            }
        }
    } else {
        if ($wpjobportal_config_array["$wpjobportal_visname"] == 1)
            $wpjobportal_print = true;
    }
    return $wpjobportal_print;
}

if(!empty(wpjobportal::$_active_addons)){
    require_once 'includes/addon-updater/wpjobportalupdater.php';
    $WPJOBPORTAL_JOBPORTALUpdater  = new WPJOBPORTAL_JOBPORTALUpdater();
}

if(is_file('includes/updater/updater.php')){
    include_once 'includes/updater/updater.php';
}

function wpjobportal_jobseekercheckLinks($wpjobportal_name) {

    $wpjobportal_print = false;
    switch ($wpjobportal_name) {
        case 'formresume': $wpjobportal_visname = 'vis_jsformresume';
            break;
        case 'jobcat': $wpjobportal_visname = 'vis_wpjobportalcat';
            break;
        case 'myresumes': $wpjobportal_visname = 'vis_jsmyresumes';
            break;
        case 'listnewestjobs': $wpjobportal_visname = 'vis_jslistnewestjobs';
            break;
        case 'listallcompanies': $wpjobportal_visname = 'vis_jslistallcompanies';
            break;
        case 'listjobbytype': $wpjobportal_visname = 'vis_jslistjobbytype';
            break;
        case 'myappliedjobs': $wpjobportal_visname = 'vis_jsmyappliedjobs';
            break;
        case 'jobsearch': $wpjobportal_visname = 'vis_wpjobportalearch';
            break;
        case 'my_jobsearches': $wpjobportal_visname = 'vis_jsmy_jobsearches';
            break;
       case 'jscredits': $wpjobportal_visname = 'vis_jscredits';
            break;
        case 'jscreditlog': $wpjobportal_visname = 'vis_jscreditlog';
            break;
        case 'jspurchasehistory': $wpjobportal_visname = 'vis_jspurchasehistory';
            break;
        case 'jsratelist': $wpjobportal_visname = 'vis_jsratelist';
            break;
        case 'jsmy_stats': $wpjobportal_visname = 'vis_jsmy_stats';
            break;
        case 'jobalertsetting': $wpjobportal_visname = 'vis_wpjobportalalertsetting';
            break;
        case 'jsmessages': $wpjobportal_visname = 'vis_jsmessages';
            break;
        case 'wpjobportal_rss': $wpjobportal_visname = 'vis_job_rss';
            break;
        case 'jsregister': $wpjobportal_visname = 'vis_jsregister';
            break;
        case 'jsactivejobs_graph': $wpjobportal_visname = 'vis_jsactivejobs_graph';
            break;
        case 'jssuggestedjobs_box': $wpjobportal_visname = 'vis_jssuggestedjobs_box';
            break;
        case 'jsappliedresume_box': $wpjobportal_visname = 'vis_jsappliedresume_box';
            break;
        case 'listjobshortlist': $wpjobportal_visname = 'vis_jslistjobshortlist';
            break;
        case 'jsmystats': $wpjobportal_visname = 'vis_jsmystats';
            break;
        case 'jobsloginlogout': $wpjobportal_visname = 'jobsloginlogout';
            break;
        case 'temp_jobseeker_dashboard_jobs_graph': $wpjobportal_visname = 'vis_temp_jobseeker_dashboard_jobs_graph';
            break;
        case 'temp_jobseeker_dashboard_useful_links': $wpjobportal_visname = 'vis_temp_jobseeker_dashboard_useful_links';
            break;
        case 'temp_jobseeker_dashboard_apllied_jobs': $wpjobportal_visname = 'vis_temp_jobseeker_dashboard_apllied_jobs';
            break;
        case 'temp_jobseeker_dashboard_shortlisted_jobs': $wpjobportal_visname = 'vis_temp_jobseeker_dashboard_shortlisted_jobs';
            break;
        case 'temp_jobseeker_dashboard_credits_log': $wpjobportal_visname = 'vis_temp_jobseeker_dashboard_credits_log';
            break;
        case 'temp_jobseeker_dashboard_purchase_history': $wpjobportal_visname = 'vis_temp_jobseeker_dashboard_purchase_history';
            break;
        case 'temp_jobseeker_dashboard_newest_jobs': $wpjobportal_visname = 'vis_temp_jobseeker_dashboard_newest_jobs';
            break;
        case 'jobsbycities': $wpjobportal_visname = 'vis_jobsbycities';
            break;
        case 'mycoverletter': $wpjobportal_visname = 'vis_jsmycoverletter';
            break;
        case 'formcoverletter': $wpjobportal_visname = 'vis_jsformcoverletter';
            break;
        case 'jobseekernewestjobs': $wpjobportal_visname = 'vis_jsjobseekernewestjobs';
            break;

        default:$wpjobportal_visname = 'vis_js' . $wpjobportal_name;
            break;
    }
    $wpjobportal_isouruser = WPJOBPORTALincluder::getObjectClass('user')->isisWPJobportalUser();
    $wpjobportal_isguest = WPJOBPORTALincluder::getObjectClass('user')->isguest();

    $guest = false;

    if($wpjobportal_isguest == true){
        $guest = true;
    }
    if($wpjobportal_isguest == false && $wpjobportal_isouruser == false){
        $guest = true;
    }
    WPJOBPORTALincluder::getJSModel('jobseeker')->getConfigurationForControlPanel();
    $wpjobportal_config_array = wpjobportal::$_data['configs'];
    if ($guest == false) {
        if (WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()) {
            if (isset($wpjobportal_config_array[$wpjobportal_name]) && $wpjobportal_config_array[$wpjobportal_name] == 1)
               $wpjobportal_print = true;
        }elseif (WPJOBPORTALincluder::getObjectClass('user')->isemployer()) {
            if ($wpjobportal_config_array['employerview_js_controlpanel'] == 1)
                if (isset($wpjobportal_config_array["$wpjobportal_visname"]) && $wpjobportal_config_array["$wpjobportal_visname"] == 1) {
                    $wpjobportal_print = true;
                }
        }else{
            if (isset($wpjobportal_config_array["$wpjobportal_visname"]) && $wpjobportal_config_array["$wpjobportal_visname"] == 1) {
                $wpjobportal_print = true;
            }
        }

    } else {
        if (isset($wpjobportal_config_array["$wpjobportal_visname"]) && $wpjobportal_config_array["$wpjobportal_visname"] == 1)
            $wpjobportal_print = true;
        }

    return $wpjobportal_print;
}
add_filter( 'login_redirect', 'wpjobportal_login_redirect', 10, 3 );
function wpjobportal_login_redirect($redirect_to, $wpjobportal_request, $wpjobportal_user){
   //is there a user to check?
   global $user;
   if ( isset( $user->roles ) && is_array( $user->roles ) ) {
       //check for admins
       if ( in_array( 'administrator', $user->roles ) ) {
           return $redirect_to;
       } else {
	   $query = "SELECT roleid FROM ".wpjobportal::$_db->prefix."wj_portal_users WHERE uid = " . esc_sql($user->ID);// small case id casuse notice
           $wpjobportal_roleid = wpjobportaldb::get_var($query);
           $wpjobportal_url = $redirect_to;
           if($wpjobportal_roleid == 2){
               $wpjobportal_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid(),'wpjobportalme'=>'jobseeker','wpjobportallt'=>'controlpanel'));
            }elseif($wpjobportal_roleid == 1){
               $wpjobportal_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid(),'wpjobportalme'=>'employer','wpjobportallt'=>'controlpanel'));
            }
            return $wpjobportal_url;
        }
   } else {
       return $redirect_to;
   }
}

add_action( 'upgrader_process_complete', 'wpjobportal_upgrade_completed', 10, 2 ); // some time above hook does not workin, so add this hook.
function wpjobportal_upgrade_completed( $wpjobportal_upgrader_object, $wpjobportal_options ) {

	// The path to our plugin's main file
	$our_plugin = plugin_basename( __FILE__ );
	// If an update has taken place and the updated type is plugins and the plugins element exists
    // log error
	if( isset($wpjobportal_options['action']) && $wpjobportal_options['action'] == 'update' && $wpjobportal_options['type'] == 'plugin' && isset( $wpjobportal_options['plugins'] ) ) {
		// Iterate through the plugins being updated and check if ours is there
		foreach( $wpjobportal_options['plugins'] as $plugin ) {
			if( $plugin == $our_plugin ) {
				update_option('wpjp_currentversion', wpjobportal::$_currentversion);
				include_once WPJOBPORTAL_PLUGIN_PATH . 'includes/updates/updates.php';

				WPJOBPORTALupdates::checkUpdates('246');


				// restore colors data
				//require_once(WPJOBPORTAL_PLUGIN_PATH . 'includes/css/style_color.php');
                $wpjobportal_color_string_values = get_option("wpjp_set_theme_colors");
                if($wpjobportal_color_string_values != ''){
                    $wpjobportal_json_values = json_decode($wpjobportal_color_string_values,true);
                    if(is_array($wpjobportal_json_values) && !empty($wpjobportal_json_values)){
                        WPJOBPORTALincluder::getJSModel('theme')->wpjpGenerateColorVariablesFile($wpjobportal_json_values);
                    }
                }

				// restore colors data end
				WPJOBPORTALincluder::getJSModel('wpjobportal')->wpjobportalCheckLicenseStatus();
                WPJOBPORTALincluder::getJSModel('wpjobportal')->WPJPAddonsAutoUpdate();
			}
		}
	}
}
add_filter('jsjp_delete_expire_session_data','wpjobportal_auto_update_addons');
function wpjobportal_auto_update_addons( ) {
	WPJOBPORTALincluder::getJSModel('wpjobportal')->wpjobportalCheckLicenseStatus();
    WPJOBPORTALincluder::getJSModel('wpjobportal')->WPJPAddonsAutoUpdate();
}

add_action('admin_notices', 'wpjobportal_show_expiry_error_notice');

function wpjobportal_show_expiry_error_notice() {
    // Check if the option is set and equals '1'
    if (get_option('wpjobportal_show_key_expiry_msg') == '1') {
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo esc_html__('Your WP Job Portal license key has expired or is invalid. Please update it to continue receiving support and updates.', 'wp-job-portal'); ?></p>
        </div>
        <?php
    }
}
