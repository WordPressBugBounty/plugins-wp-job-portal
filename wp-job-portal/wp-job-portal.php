<?php

/*
  * Plugin Name: WP Job Portal
  * Plugin URI: https://wpjobportal.com/
  * Description: WP Job Portal is WordPress’s best job board plugin — easy to use, highly configurable, and built to support both job seekers and employers. AI-powered add-ons offers smart job & resume search, and personalized recommendations.
  * Author: WP Job Portal
  * Version: 2.7.2
  * Requires at least: 6.2
  * Requires PHP: 7.4
  * Text Domain: wp-job-portal
  * Domain Path: /languages
  * Author URI: https://wpjobportal.com/
  * License: GPLv3
  * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */


if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * Version of the provider extension API that consolidated add-ons build against.
 *
 * A consolidated plugin such as WP Job Portal Pro supplies modules that used to
 * live in their own one-feature plugin folders. It does that through a small set
 * of extension points, and it needs to know they exist before it registers
 * anything. Checking this constant is more honest than comparing release
 * numbers: it states exactly which contract is available, and its absence
 * correctly identifies every build from before the contract existed.
 *
 * Bump this whenever a provider extension point is added, changed or removed,
 * and record what changed here.
 *
 *   1  wpjobportal_includer_plugin_path      resolve a module file
 *      wpjobportal_includer_template_path    resolve a module view partial
 *      wpjobportal_boot_controller_on_include suppress a controller auto-boot
 */
if (!defined('WPJOBPORTAL_PROVIDER_API')) {
    define('WPJOBPORTAL_PROVIDER_API', 1);
}

class wpjobportal {

    const LEGACY_JOB_ALERT_CRON_KEY = 'f1877c1756a68271d12db39ddc87dad7';

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

    /**
     * Pick up models a provider registered after this plugin loaded.
     *
     * Runs late on plugins_loaded, after the suites at 25. Only fills handles
     * that are still empty, so a site whose standalone add-on supplied them at
     * load time is untouched.
     *
     * @return void
     */
    public function wpjobportal_bind_provider_models() {
        // What was resolvable when the constructor asked is not what is
        // resolvable now; that answer was cached.
        WPJOBPORTAL_Monetisation::flush();

        if (self::$_wpjppurchasehistory === null && WPJOBPORTAL_Monetisation::usable('purchasehistory')) {
            self::$_wpjppurchasehistory = WPJOBPORTALincluder::getJSModel('purchasehistory');
        }

        if (self::$_wpjppaymentconfig === null && WPJOBPORTAL_Monetisation::usable('paymentmethodconfiguration')) {
            self::$_wpjppaymentconfig = WPJOBPORTALincluder::getJSModel('paymentmethodconfiguration');
        }
    }

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
        // An old add-on whose feature moved into a 2.7.0 product is paused while
        // it stays active; see WPJOBPORTAL_Superseded_Addons.
        self::$_active_addons = array_values(array_diff($wpjobportal_addon_array, WPJOBPORTAL_Superseded_Addons::paused()));
        self::$_wpjpcustomfield = WPJOBPORTALincluder::getObjectClass('customfields');
        //  self::registeractions();
        self::$_path = plugin_dir_path(__FILE__);
        self::$_pluginpath = plugins_url('/', __FILE__);
        self::$_data = array();
        self::$_error_flag = null;
        self::$_error_flag_message = null;
        self::$_currentversion = '270';
        self::$_addon_query = array('select'=>'','join'=>'','where'=>'');
        self::$_common = WPJOBPORTALincluder::getJSModel('common');
        self::$_config = WPJOBPORTALincluder::getJSModel('configuration');
        self::$_wpjpfieldordering = WPJOBPORTALincluder::getJSModel('fieldordering');
        self::$_iswpjobportalplugin = true;
        self::$_jsjpsession = WPJOBPORTALincluder::getObjectClass('wpjpsession');
        if(WPJOBPORTAL_Monetisation::usable('purchasehistory')){
            self::$_wpjppurchasehistory = WPJOBPORTALincluder::getJSModel('purchasehistory');
            self::$_wpjppaymentconfig = WPJOBPORTALincluder::getJSModel('paymentmethodconfiguration');
        }

        // This constructor runs while the plugin file loads, which is before any
        // suite has had a chance to say it provides monetisation. When one does,
        // the handles above are still null and every later use of them is a call
        // on null. Bind them again once the providers have registered.
        add_action('plugins_loaded', array($this, 'wpjobportal_bind_provider_models'), 30);
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
        // A front-end redirect, so it is registered outside the admin block.
        add_action('template_redirect', array('WPJOBPORTAL_Migration_Service', 'redirect_renamed_page'), 9);
        if (is_admin()) {
            add_action('admin_init', array('WPJOBPORTAL_Migration_Service', 'maybe_run_current'), 1);
            add_action('admin_init', function () {
                include_once WPJOBPORTAL_PLUGIN_PATH . 'includes/activation.php';
                WPJOBPORTALactivation::repairMissingTables();
            }, 2);
            add_action('admin_init', array('WPJOBPORTAL_Migration_Service', 'reconcile_legacy_upgrade'), 5);
            add_action('admin_init', array('WPJOBPORTAL_Database_Readiness_Service', 'maybe_apply'), 10);
            add_action('admin_init', 'wpjobportal_add_missing_menu_settings', 12);
            add_action('admin_init', array('WPJOBPORTAL_Storage_Engine_Service', 'maybe_continue'), 15);
            add_action('admin_init', array('WPJOBPORTAL_Notification_Queue_Service', 'maybe_recover'), 20);
            add_action('admin_post_wpjobportal_storage_engine_action', array('WPJOBPORTAL_Storage_Engine_Service', 'handle_admin_action'));
            add_action('wp_ajax_wpjobportal_notification_queue_action', array('WPJOBPORTAL_Notification_Queue_Service', 'handle_ajax_action'));
            add_action('admin_notices', array('WPJOBPORTAL_Migration_Service', 'render_admin_notice'));
            // Whatever the licence check had to say while a plugin was being
            // activated, said on a screen instead of into the activation's
            // output - see WPJOBPORTALpremiumpluginModel::noticeLater(). The
            // model is loaded on demand, so it is asked for here rather than
            // named as a callback that may not exist yet.
            add_action('admin_notices', function () {
                WPJOBPORTALincluder::getJSModel('premiumplugin');
                if (method_exists('WPJOBPORTALpremiumpluginModel', 'renderNotices')) {
                    WPJOBPORTALpremiumpluginModel::renderNotices();
                }
            });
        }
        add_action(WPJOBPORTAL_Storage_Engine_Service::CRON_HOOK, array('WPJOBPORTAL_Storage_Engine_Service', 'run_batch'));
        add_action(WPJOBPORTAL_Notification_Queue_Service::CRON_HOOK, array('WPJOBPORTAL_Notification_Queue_Service', 'run_batch'));
        WPJOBPORTAL_Privacy_Service::register();
        WPJOBPORTAL_Product_Mode_Service::register();
        WPJOBPORTAL_Marketplace_Design_Compatibility_Service::register();
        WPJOBPORTAL_Recruit_Design_Compatibility_Service::register();
        WPJOBPORTAL_Mode_Policy_Service::register();
        WPJOBPORTAL_Onboarding_Service::register();
        WPJOBPORTAL_Address_Setup_Notice::register();
        // Features that used to be separate add-ons. Each stands down while
        // its old plugin is still installed, so upgrades never double-register.
        WPJOBPORTAL_Absorbed_Features::register();
        // Watches for a superseded add-on being switched off, so the settings
        // the absorbed feature reads are not disabled along with it.
        WPJOBPORTAL_Absorbed_Config_Guard::register();
        // Pauses old add-ons that are still active and tells the site which
        // product replaces them.
        WPJOBPORTAL_Superseded_Addons::register();
        // The same hazard on the way out: an old add-on's uninstall.php drops
        // tables the absorbed feature now reads. Deleting is not reversible,
        // so this one declines the drop rather than repairing afterwards.
        WPJOBPORTAL_Uninstall_Guard::register();
        // Updates for every product, through WordPress's own Updates screen
        // rather than a wizard of our own.
        WPJOBPORTAL_Update_Service::register();
        // And the licence behind them, checked on a schedule rather than while
        // somebody is waiting for a page.
        WPJOBPORTAL_Licence_Client::register();
        // Install and update from the Licences screen, and a checksum test on
        // every package downloaded from the licence server, wherever it starts.
        WPJOBPORTAL_Addon_Installer::register();
        // Translations from wpjobportal.com, installed where WordPress keeps
        // its own, so an update never deletes them.
        WPJOBPORTAL_Translations::register();
        WPJOBPORTALincluder::getJSModel('configuration')->getConfiguration();
        register_activation_hook(__FILE__, array($this, 'wpjobportal_activate'));
        register_deactivation_hook(__FILE__, array($this, 'wpjobportal_deactivate'));
        if(version_compare(get_bloginfo('version'),'5.1', '>=')){ //for wp version >= 5.1
            add_action('wp_insert_site', array($this, 'wpjobportal_new_site')); //when new site is added in multisite
        }else{ //for wp version < 5.1
            add_action('wpmu_new_blog', array($this, 'wpjobportal_new_blog'), 10, 6);
        }
        add_filter('wpmu_drop_tables', array($this, 'wpjobportal_delete_site'));
        add_action('plugins_loaded', array($this, 'wpjobportal_load_plugin_textdomain'));
        add_action('plugins_loaded', array($this, 'wpjobportal_ensure_job_alert_cron_secret'), 5);
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
        add_action('init', array($this, 'wpjobportal_recover_job_alert_schedule'), 50);
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
        if (class_exists('WPJOBPORTALsitemap')) {
            WPJOBPORTALsitemap::init();
        }
    }

    function wpjobportalgetdocumenttitle($title){
        $custom_title = WPJOBPORTALincluder::getJSModel('common')->getWPJobPortalDocumentTitleByPage();
        if($custom_title != '' ){
            return $custom_title;
        }
        return $title;
    }

    /**
     * The historical alert key was included in the distributed source. Rotate
     * only that value (or an empty fresh-install value) so custom external cron
     * keys remain valid after the security update.
     */
    function wpjobportal_ensure_job_alert_cron_secret(){
        global $wpdb;
        $table = $wpdb->prefix . 'wj_portal_config';
        $key = wpjobportaldb_direct::get_var($wpdb->prepare(
            "SELECT configvalue FROM %i WHERE configname = %s LIMIT 1",
            $table,
            'cron_job_alert_key'
        ));

        if (!empty($key) && !hash_equals(self::LEGACY_JOB_ALERT_CRON_KEY, (string) $key)) {
            return;
        }

        $secret = wp_generate_password(64, false, false);
        if (empty($secret)) {
            return;
        }

        wpjobportaldb_direct::query($wpdb->prepare(
            "UPDATE %i SET configvalue = %s WHERE configname = %s AND (configvalue = %s OR configvalue = '')",
            $table,
            $secret,
            'cron_job_alert_key',
            self::LEGACY_JOB_ALERT_CRON_KEY
        ));
    }

    /**
     * Recover a missing Job Alert schedule only when the supported add-on has
     * registered a real worker for the event. This avoids creating an orphaned
     * cron event from a stale active-state option.
     */
    function wpjobportal_recover_job_alert_schedule() {
        if (!in_array('jobalert', (array) self::$_active_addons, true)) {
            return;
        }

        $hook = 'wpjobportal_send_scheduled_job_alerts';
        if (false === has_action($hook) || wp_next_scheduled($hook)) {
            return;
        }

        $scheduled = wp_schedule_event(time() + wp_rand(60, 300), 'daily', $hook);
        if ($scheduled && class_exists('WPJOBPORTAL_Audit_Service')) {
            WPJOBPORTAL_Audit_Service::record(
                'job_alert_schedule_recovered',
                'success',
                array('source' => 'core_recovery')
            );
        }
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
                    WPJOBPORTAL_Migration_Service::run(self::$_currentversion);

                	// restore colors data
		            require(WPJOBPORTAL_PLUGIN_PATH . 'includes/css/style_color.php');

			        // restore colors data end
                }
            }
        }
    }

    function wpjobportal_activation_redirect(){
        if (get_option('wpjobportal_do_activation_redirect') == true) {
            update_option('wpjobportal_do_activation_redirect',false);
            wp_safe_redirect(admin_url('admin.php?page=wpjobportal_postinstallation&wpjobportallt=quickstart'));
            exit;
        }
    }

    function wpjobportal_activate() {
        include_once 'includes/activation.php';
        WPJOBPORTALactivation::wpjobportal_activate();
        if (class_exists('WPJOBPORTALsitemap')) {
            WPJOBPORTALsitemap::addRewriteRule();
        }
        flush_rewrite_rules();
		add_option('wpjobportal_do_activation_redirect', true);
    }

    function wpjobportal_deactivate() {
        include_once 'includes/deactivation.php';
        WPJOBPORTALdeactivation::wpjobportal_deactivate();
        flush_rewrite_rules();
    }

    /*
     * Include the required files
     */

    function wpjobportal_includes() {
        // php 8.1 issues
        require_once 'includes/wpjobportalphplib.php';
        require_once 'includes/http.php';
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
        require_once 'includes/services/class-wpjobportal-woocommerce-package-service.php';
        WPJOBPORTAL_WooCommerce_Package_Service::register();
        require_once 'includes/services/class-wpjobportal-captcha-service.php';
        require_once 'includes/services/class-wpjobportal-route-registry-service.php';
        require_once 'includes/services/class-wpjobportal-core-action-policy-service.php';
        require_once 'includes/services/class-wpjobportal-payment-service.php';
        require_once 'includes/services/class-wpjobportal-entitlement-service.php';
        require_once 'includes/services/class-wpjobportal-addon-action-policy-service.php';
        require_once 'includes/services/class-wpjobportal-authorization-service.php';
        require_once 'includes/services/class-wpjobportal-file-policy-service.php';
        require_once 'includes/services/class-wpjobportal-address-import-service.php';
        require_once 'includes/services/class-wpjobportal-site-country-service.php';
        require_once 'includes/services/class-wpjobportal-product-map.php';
        require_once 'includes/services/class-wpjobportal-licence-client.php';
        require_once 'includes/services/class-wpjobportal-update-client.php';
        require_once 'includes/services/class-wpjobportal-update-service.php';
        require_once 'includes/services/class-wpjobportal-addon-installer.php';
        require_once 'includes/services/class-wpjobportal-translations.php';
        require_once 'includes/services/class-wpjobportal-registration-policy.php';
        require_once 'includes/services/class-wpjobportal-sample-data-importer.php';
        require_once 'includes/services/class-wpjobportal-monetisation.php';
        require_once 'includes/services/class-wpjobportal-absorbed-config-guard.php';
        require_once 'includes/services/class-wpjobportal-superseded-addons.php';
        require_once 'includes/services/class-wpjobportal-uninstall-guard.php';
        include_once 'includes/formhandler.php';
        include_once 'includes/ajax.php';
        require_once 'includes/constants.php';
        require_once 'includes/messages.php';
        require_once 'includes/wpjobportaldb.php';
        require_once 'includes/services/class-wpjobportal-redactor.php';
        require_once 'includes/services/class-wpjobportal-audit-service.php';
        require_once 'includes/services/class-wpjobportal-notification-queue-service.php';
        require_once 'includes/services/class-wpjobportal-notification-service.php';
        require_once 'includes/services/class-wpjobportal-migration-service.php';
        require_once 'includes/services/class-wpjobportal-database-readiness-service.php';
        require_once 'includes/services/class-wpjobportal-storage-engine-service.php';
        require_once 'includes/services/class-wpjobportal-privacy-service.php';
        require_once 'includes/services/class-wpjobportal-product-mode-service.php';
        require_once 'includes/services/class-wpjobportal-marketplace-design-compatibility-service.php';
        require_once 'includes/services/class-wpjobportal-recruit-design-compatibility-service.php';
        require_once 'includes/services/class-wpjobportal-mode-policy-service.php';
        require_once 'includes/services/class-wpjobportal-onboarding-service.php';
        require_once 'includes/services/class-wpjobportal-address-setup-notice.php';
        require_once 'includes/services/class-wpjobportal-feature-tiers.php';
        require_once 'includes/services/class-wpjobportal-absorbed-features.php';
        require_once 'includes/services/class-wpjobportal-sample-data-service.php';
        require_once 'includes/services/class-wpjobportal-diagnostics-service.php';
        require_once 'includes/services/class-wpjobportal-page-provisioning-service.php';
        WPJOBPORTAL_Page_Provisioning_Service::register();
        include_once 'includes/shortcodes.php';
        include_once 'includes/paramregister.php';
        include_once 'includes/breadcrumbs.php';
        include_once 'includes/dashboardapi.php';
        include_once 'includes/sitemap.php';
        //Widgets TO include
        include_once 'includes/widgets/searchjobs.php';
        // Register Elementor hooks regardless of plugin load order. The
        // integration itself checks Elementor availability before using it.
        include_once 'includes/elementor-addon.php';

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


    public function wpjobportal_load_plugin_textdomain() {
        $domain = 'wp-job-portal';
        $locale = function_exists('determine_locale') ? determine_locale() : get_locale();
        $locale = apply_filters('plugin_locale', $locale, $domain); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress core filter.

        $candidates = array_unique(array_filter([
            $locale,
            strtolower((string) $locale),
            strpos((string) $locale, '_') !== false ? substr((string) $locale, 0, 2) : '',
        ]));

        foreach ($candidates as $candidate) {
            $global_mo = trailingslashit(WP_LANG_DIR) . 'plugins/' . $domain . '-' . $candidate . '.mo';
            if (file_exists($global_mo) && load_textdomain($domain, $global_mo)) {
                return;
            }
        }

        foreach ($candidates as $candidate) {
            $bundled_mo = trailingslashit(WPJOBPORTAL_PLUGIN_PATH) . 'languages/' . $domain . '-' . $candidate . '.mo';
            if (file_exists($bundled_mo) && load_textdomain($domain, $bundled_mo)) {
                return;
            }
        }

        // Keep the standard WordPress path registration as a final fallback.
        load_plugin_textdomain($domain, false, dirname(plugin_basename(__FILE__)) . '/languages'); // phpcs:ignore PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound -- language files downloaded into the plugin's own folder.
    }

    /*
     * function for the Style Sheets
     */

    static function wpjobportal_addStyleSheets() {
        wp_enqueue_script('jquery');
        /*
         * Versioned from the file on disk, like the stylesheets. Without a
         * version of its own this went out as ?ver=<WordPress version>, so a
         * plugin update shipped new script under a URL the browser already
         * had cached, and the old file stayed until WordPress itself moved.
         */
        wp_enqueue_script( 'wpjobportal-commonjs', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/common.js', array(), wpjobportal_asset_version('includes/js/common.js'), false );
        wp_enqueue_script( 'wpjobportal-res-tables', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/responsivetable.js', array(), wpjobportal_asset_version('includes/js/responsivetable.js'), false );
        wp_enqueue_script('jquery-ui-accordion');
         wp_enqueue_style('wpjobportal-tokeninput', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/tokeninput.css', array(), wpjobportal_asset_version('includes/css/tokeninput.css'));
        wp_enqueue_style('wpjobportal-fontawesome', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/font-awesome.css', array(), wpjobportal_asset_version('includes/css/font-awesome.css'));
        if(wpjobportal::$wpjobportal_theme_chk == 1){
            $wpjobportal_class_prefix = 'wpj-jp';
        }else{
            $wpjobportal_class_prefix = 'wjportal';
        }
        $wpjobportal_nonce_value = wp_create_nonce("wp-job-portal-nonce");
        wp_localize_script('wpjobportal-commonjs', 'common', array('ajaxurl' => esc_url_raw(admin_url('admin-ajax.php')),'js_nonce'=>$wpjobportal_nonce_value,'insufficient_credits' => esc_html(__('You have insufficient credits, you can not perform this action','wp-job-portal')),'theme_chk_prefix'=> $wpjobportal_class_prefix,'theme_chk_number'=>wpjobportal::$wpjobportal_theme_chk,'theme_chk_flag'=>wpjobportal::$wpjobportal_theme_chk_flag, 'theme_image' => WPJOBPORTAL_IMAGE,'terms_conditions' => esc_html(__('Please Accept Terms And Conditions So You Can Proceed','wp-job-portal')) ,'company_feature_text' => esc_html(__('Are You Sure You Want To Feature this Company?','wp-job-portal')),'job_feature_text' => esc_html(__('Are You Sure You Want To Feature this Job?','wp-job-portal')),'resume_feature_text' => esc_html(__('Are You Sure You Want To Feature this Resume?','wp-job-portal')),'email_format_error' => esc_html(__('Email is not of correct Format','wp-job-portal'))));

        wp_enqueue_script( 'wpjobportal-formvalidator', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/jquery.form-validator.js', array(), wpjobportal_asset_version('includes/js/jquery.form-validator.js'), false );
        wp_enqueue_script(
            'wpjobportal-accessible-forms',
            esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/wpjobportal-accessible-forms.js',
            array('wpjobportal-formvalidator'),
            wpjobportal_asset_version('includes/js/wpjobportal-accessible-forms.js'),
            true
        );
        // What a form says while it is being sent, so a second press is not
        // needed to find out whether the first one landed.
        wp_localize_script('wpjobportal-accessible-forms', 'wpjobportalFormsL10n', array(
            'working'  => esc_html(__('Working...', 'wp-job-portal')),
            'applying' => esc_html(__('Applying...', 'wp-job-portal')),
            'noMatch'  => esc_html(__('Nothing matches', 'wp-job-portal')),
            'needed'   => esc_html(__('This form cannot be sent yet:', 'wp-job-portal')),
        ));
        if(wpjobportal::$wpjobportal_theme_chk == 0 || wpjobportal::$_common->wpjp_isadmin()){
            wp_enqueue_script( 'wpjobportal-tokeninput', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/jquery.tokeninput.js', array(), wpjobportal_asset_version('includes/js/jquery.tokeninput.js'), false );
        }else{
            # token  input For admin side
         wp_enqueue_script( 'wpjobportal-tokeninput', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/jquery.tokeninput.js', array(), wpjobportal_asset_version('includes/js/jquery.tokeninput.js'), false );
        }
        wp_enqueue_script( 'chosen', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/chosen/chosen.jquery.min.js', array(), wpjobportal_asset_version('includes/js/chosen/chosen.jquery.min.js'), false );
        // wp_enqueue_script( 'bootstrap-min', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js', array(), WPJOBPORTAL_VERSION, false );
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

    /**
     * Where a visitor goes after posting a job.
     *
     * The page chosen in settings when it is a published page; otherwise the
     * job list. A fresh install stores 0 there, and get_the_permalink(0) gave
     * false: the visitor ended on a blank page, and the "job saved" message
     * never showed.
     *
     * @return string
     */
    public static function wpjobportal_visitorJobRedirectUrl() {
        $wpjobportal_pageid = absint(wpjobportal::$_config->getConfigurationByConfigName('visitor_add_job_redirect_page'));
        if ($wpjobportal_pageid > 0 && get_post_status($wpjobportal_pageid) == 'publish') {
            return get_the_permalink($wpjobportal_pageid);
        }
        return esc_url_raw(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'jobs', 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid())));
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
                /*
                 * Both of these answer 0 when they found nothing, and 0 is not
                 * a page. The original guard was `!= '' && is_numeric()`, which
                 * on PHP 7 rejected 0 - `0 == ''` was true there - and fell
                 * through to the default below. PHP 8 changed that comparison,
                 * so `0 != ''` became true and this returned 0 instead: every
                 * request with no queried page (admin-ajax, cron, an e-mail
                 * being composed) got a page id of zero. The visible symptom
                 * was an employer clicking Buy Now and being sent through the
                 * job-seeker control panel, because makeUrl() treats the 0 as
                 * empty and falls back on its own.
                 */
                $wpjobportal_pageid = get_queried_object_id(); // current pageid
                if($wpjobportal_pageid > 0){
                    return $wpjobportal_pageid;
                }
                    // to get page id from url
                $wpjobportal_actual_link = rtrim(WPJOBPORTALrequest::requestUri(), '/');
                $wpjobportal_pageid = url_to_postid($wpjobportal_actual_link);
                if($wpjobportal_pageid > 0){
                    return $wpjobportal_pageid;
                }

            }
            return wpjobportal::wpjobportal_getDefaultPageid();
        }
    }

    /**
     * The page to fall back on when the request has no page of its own.
     *
     * 'default_pageid' is the job-seeker control panel, and for years that was
     * the only answer. It is the wrong one for an employer: it is what sent
     * somebody buying a package off to the job seeker's dashboard, and what
     * their checkout returned them to afterwards. So when we know the visitor
     * is an employer, their own panel is the better default.
     *
     * The filter is for everyone else the roles do not describe. A recruiter
     * holds no portal role at all - team membership is what grants them
     * anything - so Core cannot recognise one, and the Recruit Suite answers
     * for them instead.
     *
     * @return int
     */
    public static function wpjobportal_getDefaultPageid() {
        static $wpjobportal_employer_pageid = null;

        $wpjobportal_id = 0;
        $wpjobportal_pageid = wpjobportal::$_db->get_var("SELECT configvalue FROM `".wpjobportal::$_db->prefix."wj_portal_config` WHERE configname = 'default_pageid'");
        if ($wpjobportal_pageid)
            $wpjobportal_id = (int) $wpjobportal_pageid;

        // Resolved once per request: this runs on a path that can be hit many
        // times while one page of links is built.
        if ($wpjobportal_employer_pageid === null) {
            $wpjobportal_employer_pageid = 0;
            if (class_exists('WPJOBPORTAL_Page_Provisioning_Service')) {
                $wpjobportal_employer_pageid = (int) WPJOBPORTAL_Page_Provisioning_Service::page_carrying('wpjobportal_employer_controlpanel');
            }
        }

        $wpjobportal_is_employer = false;
        if (is_user_logged_in() && class_exists('WPJOBPORTALincluder')) {
            $wpjobportal_user = WPJOBPORTALincluder::getObjectClass('user');
            if (is_object($wpjobportal_user) && method_exists($wpjobportal_user, 'isemployer')) {
                $wpjobportal_is_employer = (bool) $wpjobportal_user->isemployer();
            }
        }

        if ($wpjobportal_is_employer && $wpjobportal_employer_pageid > 0) {
            $wpjobportal_id = $wpjobportal_employer_pageid;
        }

        /**
         * Filter the page a link falls back to when the request has none.
         *
         * @param int  $wpjobportal_id          The page decided so far.
         * @param bool $wpjobportal_is_employer Whether Core recognises an employer.
         * @param int  $wpjobportal_employer_pageid The employer control panel, or 0.
         */
        return (int) apply_filters('wpjobportal_default_pageid', $wpjobportal_id, $wpjobportal_is_employer, $wpjobportal_employer_pageid);
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

    /**
     * The filesystem object, without ever asking for FTP details mid-page.
     *
     * request_filesystem_credentials() prints WordPress's "Connection
     * Information" form when the host is not set up for direct file access, and
     * the object WP_Filesystem() leaves behind then has no connection: the next
     * exists() is a fatal error. That happened on every include and every URL
     * built. Direct access is used when WordPress allows it; otherwise FTP
     * details from wp-config.php, if they are there; otherwise PHP's own file
     * access, which can still read everything and write wherever this process
     * may.
     *
     * @return WP_Filesystem_Base
     */
    static function wpjobportal_filesystem() {
        global $wp_filesystem;
        if ($wp_filesystem instanceof WP_Filesystem_Base && (!$wp_filesystem->errors instanceof WP_Error || !$wp_filesystem->errors->has_errors())) {
            return $wp_filesystem;
        }
        if (!function_exists('WP_Filesystem')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        if (get_filesystem_method() === 'direct') {
            if (WP_Filesystem()) {
                return $wp_filesystem;
            }
        } else {
            // Returns the details without a form only when wp-config.php has them.
            ob_start();
            $wpjobportal_creds = request_filesystem_credentials(site_url(), '', false, false, null);
            ob_end_clean();
            if ($wpjobportal_creds && WP_Filesystem($wpjobportal_creds)) {
                return $wp_filesystem;
            }
        }
        require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
        // WP_Filesystem() defines these; every write needs them. Same values.
        if (!defined('FS_CHMOD_DIR')) {
            define('FS_CHMOD_DIR', (fileperms(ABSPATH) & 0777 | 0755)); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- WordPress core constant, defined only if WP_Filesystem() has not.
        }
        if (!defined('FS_CHMOD_FILE')) {
            define('FS_CHMOD_FILE', (fileperms(ABSPATH . 'index.php') & 0777 | 0644)); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- WordPress core constant, defined only if WP_Filesystem() has not.
        }
        $wp_filesystem = new WP_Filesystem_Direct(null);
        return $wp_filesystem;
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
                $wpjobportal_actual_link = rtrim(WPJOBPORTALrequest::requestUri(), '/');
                $wpjobportal_pageid = url_to_postid($wpjobportal_actual_link);
            }
        }

        // Same default as wpjobportal_getPageid(), so a link built with an
        // explicit pageid of 0 - which is what that used to hand out - lands
        // where the caller's own role says it should, not always on the
        // job seeker's panel.
        if(empty($wpjobportal_pageid)){
            $wpjobportal_pageid = wpjobportal::wpjobportal_getDefaultPageid();
        }else{
			// page will open in defaul page (not homepage)
            $homepageid = get_option('page_on_front');
            if($homepageid == $wpjobportal_pageid){
                $wpjobportal_pageid = wpjobportal::wpjobportal_getDefaultPageid();
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
            $wp_filesystem = wpjobportal::wpjobportal_filesystem();

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
        }else{ // in case of form
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
                        $wpjobportal_url = wpjobportal::wpjobportal_visitorJobRedirectUrl();
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

        if(WPJOBPORTALrequest::hasCookie('jsjp_addon_return_data')){
            wpjobportalphplib::wpJP_setcookie('jsjp_addon_return_data' , '' , time() - 3600, COOKIEPATH);
            if ( SITECOOKIEPATH != COOKIEPATH ){
                wpjobportalphplib::wpJP_setcookie('jsjp_addon_return_data' , '' , time() - 3600, SITECOOKIEPATH);
            }
        }

        if(WPJOBPORTALrequest::hasCookie('jsjp_addon_install_data')){
            wpjobportalphplib::wpJP_setcookie('jsjp_addon_install_data' , '' , time() - 3600);
        }
    }

    public static function removeusersearchcookies(){
        if(WPJOBPORTALrequest::hasCookie('jsjp_jobportal_search_data')){
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

    static function wpjobportal_getVariableValue($wpjobportal_text_string, $wpjobportal_domain = 'wp-job-portal'){
        if(empty($wpjobportal_text_string)){
            return $wpjobportal_text_string;
        }
        $wpjobportal_text_string = trim($wpjobportal_text_string);
        $wpjobportal_translations = get_translations_for_domain( $wpjobportal_domain );
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
    if(WPJOBPORTALrequest::hasCookie('wpjobportal_apply_visitor')){
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

/**
 * Version string for a plugin asset, based on its last-modified time.
 *
 * The stylesheets and scripts below were originally enqueued with a hardcoded release version.
 * That never changes when a file does, so browsers keep serving a cached copy
 * and edits appear not to take effect until a manual hard refresh. Keying the
 * version to filemtime busts the cache exactly when the file changes, and
 * falls back to the release string if the file cannot be read.
 *
 * A few screens preview an add-on's own stylesheets, which sit outside this
 * plugin; those pass that add-on's directory as the base so the file is still
 * the thing the version is read from.
 *
 * @param string      $wpjobportal_relative_path Path under $wpjobportal_base_path.
 * @param string|null $wpjobportal_base_path     Directory to resolve against, or null for this plugin.
 * @return string
 */
function wpjobportal_asset_version($wpjobportal_relative_path, $wpjobportal_base_path = null) {
    $wpjobportal_base = ($wpjobportal_base_path === null) ? WPJOBPORTAL_PLUGIN_PATH : $wpjobportal_base_path;
    $wpjobportal_full_path = trailingslashit($wpjobportal_base) . ltrim($wpjobportal_relative_path, '/');
    if (is_readable($wpjobportal_full_path)) {
        $wpjobportal_mtime = filemtime($wpjobportal_full_path);
        if (!empty($wpjobportal_mtime)) {
            return (string) $wpjobportal_mtime;
        }
    }
    return '2.7.2';
}

/**
 * Whether this Core lays out the front-end cascade in layers for a look
 * provider. A design add-on checks this before deciding whether to hand its
 * stylesheets to Core or to enqueue them itself the old way.
 *
 * @return bool
 */
function wpjobportal_supports_look_layers() {
    return true;
}

/**
 * One @import line, into one cascade layer.
 *
 * @param string $src   Absolute URL, or a path relative to the plugin.
 * @param string $layer wpjp-legacy | wpjp-foundation, or '' for no layer.
 * @param string $media Optional media query.
 * @param string $ver   Optional version; relative paths get theirs from disk.
 * @return string
 */
function wpjobportal_layered_import($src, $layer, $media = '', $ver = '') {
    if (strpos($src, '//') === false) {
        $ver = $ver !== '' ? $ver : wpjobportal_asset_version($src);
        $src = WPJOBPORTAL_PLUGIN_URL . ltrim($src, '/');
    }
    if ($ver !== '') {
        $src = add_query_arg('ver', rawurlencode($ver), $src);
    }
    $layer = preg_replace('/[^a-z0-9-]/', '', $layer);
    return sprintf(
        "@import url(\"%s\")%s%s;\n",
        esc_url_raw($src),
        $layer !== '' ? ' layer(' . $layer . ')' : '',
        $media !== '' ? ' ' . wp_strip_all_tags($media) : ''
    );
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
    wp_localize_script('wpjobportal-commonjs', 'common', array('ajaxurl' => esc_url_raw(admin_url('admin-ajax.php')),'js_nonce'=>$wpjobportal_nonce_value,'insufficient_credits' => esc_html(__('You have insufficient credits, you can not perform this action','wp-job-portal')),'theme_chk_prefix'=> $wpjobportal_class_prefix,'theme_chk_number'=>wpjobportal::$wpjobportal_theme_chk,'theme_chk_flag'=>wpjobportal::$wpjobportal_theme_chk_flag, 'theme_image' => WPJOBPORTAL_IMAGE,'terms_conditions' => esc_html(__('Please Accept Terms And Conditions So You Can Proceed','wp-job-portal')) ,'company_feature_text' => esc_html(__('Are You Sure You Want To Feature this Company?','wp-job-portal')),'job_feature_text' => esc_html(__('Are You Sure You Want To Feature this Job?','wp-job-portal')),'resume_feature_text' => esc_html(__('Are You Sure You Want To Feature this Resume?','wp-job-portal')),'email_format_error' => esc_html(__('Email is not of correct Format','wp-job-portal'))));
    //wp_localize_script('wpjobportal-commonjs', 'common', array('ajaxurl' => esc_url_raw(admin_url('admin-ajax.php')),'js_nonce'=>$wpjobportal_nonce_value ,'insufficient_credits' => esc_html(__('You have insufficient credits, you can not perform this action','wp-job-portal')),'theme_chk_prefix'=> $wpjobportal_class_prefix,'theme_chk_number'=>wpjobportal::$wpjobportal_theme_chk,'pluginurl'=>WPJOBPORTAL_PLUGIN_URL,'cityajaxurl' => admin_url("admin.php?page=wpjobportal_city&action=wpjobportaltask&task=getaddressdatabycityname"),'theme_chk_flag'=>wpjobportal::$wpjobportal_theme_chk_flag, 'theme_image' => WPJOBPORTAL_IMAGE,'terms_conditions' => esc_html(__('Please Accept Terms And Conditions So You Can Proceed','wp-job-portal') )));
    // Shared Release 3 design tokens and low-specificity component contract.
    /*
     * Which look loads.
     *
     * A design add-on used to stack its stylesheet on top of Core's and beat
     * it with !important - Elegant Design carried 204 of them. Now a look
     * provider hands its sheets to Core through one filter, and Core lays
     * out the whole cascade in layers:
     *
     *     wpjp-legacy      Core's own look (style.css and the two control
     *                      panel sheets), plus whatever the provider still
     *                      ships from before its migration
     *     wpjp-foundation  the shared design system
     *     (unlayered)      the provider's theme
     *
     * Unlayered rules beat layered ones whatever the specificity, so a theme
     * rule as plain as `.wjportal-jobs-list { }` beats anything in the 14,000
     * lines beneath it without escalating. The theme is deliberately NOT in a
     * layer of its own: the site theme's global styles are unlayered, and a
     * layer, however late, ranks below them - a first draft in a `wpjp-theme`
     * layer lost every link colour to Twenty Twenty-Five's `a:where(...)`.
     * Unlayered, the two meet on ordinary specificity, which the theme wins by
     * being scoped under the portal wrapper. Stylesheets from suites and other
     * plugins are unlayered too; their own components are theirs to style.
     *
     * The five Core handles are still registered, with no source, so anything
     * depending on them keeps loading in the right order; the layered block
     * prints in their place.
     *
     * With no provider registered nothing here changes: the same five sheets
     * enqueue directly, as they always have.
     */
    $wpjobportal_look = apply_filters('wpjobportal_look_provider', array());
    $wpjobportal_layered = !empty($wpjobportal_look['theme']['src']);

    if ($wpjobportal_layered) {
        wp_register_style( 'wpjobportal-look-layers', false, array(), WPJOBPORTAL_VERSION );
        wp_enqueue_style('wpjobportal-look-layers');

        $wpjobportal_layer_css = '@layer wpjp-legacy, wpjp-foundation;' . "\n";
        $wpjobportal_layer_css .= wpjobportal_layered_import('includes/css/job_portal_variables.css', 'wpjp-legacy');
        $wpjobportal_layer_css .= wpjobportal_layered_import('includes/css/style.css', 'wpjp-legacy');
        $wpjobportal_layer_css .= wpjobportal_layered_import('includes/css/jobseekercp.css', 'wpjp-legacy');
        $wpjobportal_layer_css .= wpjobportal_layered_import('includes/css/employercp.css', 'wpjp-legacy');
        foreach ((array) (isset($wpjobportal_look['legacy']) ? $wpjobportal_look['legacy'] : array()) as $wpjobportal_sheet) {
            if (!empty($wpjobportal_sheet['src'])) {
                $wpjobportal_layer_css .= wpjobportal_layered_import(
                    $wpjobportal_sheet['src'],
                    'wpjp-legacy',
                    isset($wpjobportal_sheet['media']) ? $wpjobportal_sheet['media'] : '',
                    isset($wpjobportal_sheet['ver']) ? $wpjobportal_sheet['ver'] : ''
                );
            }
        }
        $wpjobportal_layer_css .= wpjobportal_layered_import('includes/css/wpjobportal-design-system.css', 'wpjp-foundation');
        $wpjobportal_layer_css .= wpjobportal_layered_import(
            $wpjobportal_look['theme']['src'],
            '',
            '',
            isset($wpjobportal_look['theme']['ver']) ? $wpjobportal_look['theme']['ver'] : ''
        );
        wp_add_inline_style('wpjobportal-look-layers', $wpjobportal_layer_css);

        wp_register_style( 'wpjobportal-design-tokens', false, array('wpjobportal-look-layers'), WPJOBPORTAL_VERSION );
        wp_register_style( 'wpjobportal-design-system', false, array('wpjobportal-design-tokens'), WPJOBPORTAL_VERSION );
        wp_register_style( 'wpjobportal-jobseeker-style', false, array('wpjobportal-design-system'), WPJOBPORTAL_VERSION );
        wp_register_style( 'wpjobportal-employer-style', false, array('wpjobportal-design-system'), WPJOBPORTAL_VERSION );
        wp_register_style( 'wpjobportal-style', false, array('wpjobportal-design-system'), WPJOBPORTAL_VERSION );
        wp_enqueue_style('wpjobportal-design-tokens');
        wp_enqueue_style('wpjobportal-design-system');
        wp_enqueue_style('wpjobportal-jobseeker-style');
        wp_enqueue_style('wpjobportal-employer-style');
        wp_enqueue_style('wpjobportal-style');
    } else {
        // Shared Release 3 design tokens and low-specificity component contract.
        wp_enqueue_style('wpjobportal-design-tokens', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/job_portal_variables.css', array(), wpjobportal_asset_version('includes/css/job_portal_variables.css'));
        wp_enqueue_style('wpjobportal-design-system', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/wpjobportal-design-system.css', array('wpjobportal-design-tokens'), wpjobportal_asset_version('includes/css/wpjobportal-design-system.css'));
        /*
         * Elegant Design keeps its palette under its own variable names, which the
         * design system cannot see, so every wpjp-ds-* component - which is every
         * new Recruit and Marketplace screen - rendered Core's colours inside an
         * Elegant Design site. This restates that palette under the names the
         * design system reads. Only reached by an Elegant Design too old to be a
         * look provider; a current one defines the tokens in its theme.
         */
        if (WPJOBPORTALincluder::getJSModel('common')->isElegantDesignEnabled()) {
            wp_enqueue_style('wpjobportal-design-bridge', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/wpjobportal-design-bridge.css', array('wpjobportal-design-system'), wpjobportal_asset_version('includes/css/wpjobportal-design-bridge.css'));
        }
        wp_enqueue_style('wpjobportal-jobseeker-style', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/jobseekercp.css', array('wpjobportal-design-system'), wpjobportal_asset_version('includes/css/jobseekercp.css'));
        wp_enqueue_style('wpjobportal-employer-style', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/employercp.css', array('wpjobportal-design-system'), wpjobportal_asset_version('includes/css/employercp.css'));
        wp_enqueue_style('wpjobportal-style', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/style.css', array('wpjobportal-design-system'), wpjobportal_asset_version('includes/css/style.css'));
    }
    /*
     * Chosen's own sheet has to be in place before Core restyles what it builds.
     *
     * chosen.min.css is a third-party sheet outside every layer, and the rules
     * that give its control the height, colour and typeface of the fields around
     * it are deliberately weightless - :where() so a theme can still have the
     * last word. Weightless rules are settled by source order, and this one was
     * printed first, so Chosen won every property both of them named: a 13px
     * grey gradient sitting in a column of white 17px fields. Naming it as a
     * dependency puts it where the comment in that sheet always assumed it was.
     */
    wp_enqueue_style('wpjobportal-chosen-style', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/chosen/chosen.min.css', array(), wpjobportal_asset_version('includes/js/chosen/chosen.min.css'));
    wp_enqueue_style(
        'wpjobportal-accessible-forms',
        esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/wpjobportal-accessible-forms.css',
        array('wpjobportal-style', 'wpjobportal-chosen-style'),
        wpjobportal_asset_version('includes/css/wpjobportal-accessible-forms.css')
    );
    //wp_enqueue_style( 'wpjobportal-color', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/color.css', array(), WPJOBPORTAL_VERSION );
    wp_enqueue_style('wpjobportal-star-rating', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/wpjobportalrating.css', array(), wpjobportal_asset_version('includes/css/wpjobportalrating.css'));
    // wp_enqueue_style('wpjobportal-style-tablet', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/style_tablet.css',array(),'1.1.1','(max-width: 782px)');
    // wp_enqueue_style('wpjobportal-style-mobile-landscape', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/style_mobile_landscape.css',array(),'1.1.1','(max-width: 650px)');
    // wp_enqueue_style('wpjobportal-style-mobile', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/style_mobile.css',array(),'1.1.1','(max-width: 480px)');
    if (is_rtl()) {
        wp_register_style('wpjobportal-style-rtl', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/stylertl.css', array(), wpjobportal_asset_version('includes/css/stylertl.css'));
        wp_enqueue_style('wpjobportal-style-rtl');
    }
    //wp_enqueue_style( 'wpjobportal-css-ie', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/wpjobportal-ie.css', array(), WPJOBPORTAL_VERSION );
    //wp_style_add_data( 'wpjobportal-css-ie', 'conditional', 'IE' );
    //wp_enqueue_script( 'wpjobportal-vue.js', 'https://cdn.jsdelivr.net/npm/vue/dist/vue.js', array(), WPJOBPORTAL_VERSION, 1 );
    //wp_enqueue_script( 'wpjobportal-vue-components', WPJOBPORTALincluder::getComponentJsUrl('common'), array(), WPJOBPORTAL_VERSION, 1 );

     // elementor overides css
    $wpjobportal_is_elementor_edit_mode = false;

    if (
        class_exists('\Elementor\Plugin')
        && isset(\Elementor\Plugin::$instance->editor)
        && is_object(\Elementor\Plugin::$instance->editor)
    ) {
        $wpjobportal_is_elementor_edit_mode = \Elementor\Plugin::$instance->editor->is_edit_mode();
    }

    if ($wpjobportal_is_elementor_edit_mode == false) {
        wp_enqueue_style('wpjobportal-elementor-overrides', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/wpjp_elementor_overrides.css', array(), wpjobportal_asset_version('includes/css/wpjp_elementor_overrides.css'));
    }

}

add_action( 'wp_enqueue_scripts', 'wpjobportal_register_plugin_styles' );

function wpjobportal_admin_register_plugin_styles() {
    wp_enqueue_style('wpjobportal-design-tokens', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/job_portal_variables.css', array(), wpjobportal_asset_version('includes/css/job_portal_variables.css'));
    wp_enqueue_style('wpjobportal-design-system', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/wpjobportal-design-system.css', array('wpjobportal-design-tokens'), wpjobportal_asset_version('includes/css/wpjobportal-design-system.css'));
    wp_enqueue_style('wpjobportal-admin-desktop-css', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/wpjobportaladmin_desktop.css',array('wpjobportal-design-system'),wpjobportal_asset_version('includes/css/wpjobportaladmin_desktop.css'),'all');
    // Chosen's sheet first, for the reason given above.
    wp_enqueue_style('wpjobportal-chosen-style', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/chosen/chosen.min.css', array(), wpjobportal_asset_version('includes/js/chosen/chosen.min.css'));
    wp_enqueue_style(
        'wpjobportal-accessible-forms',
        esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/wpjobportal-accessible-forms.css',
        array('wpjobportal-admin-desktop-css', 'wpjobportal-chosen-style'),
        wpjobportal_asset_version('includes/css/wpjobportal-accessible-forms.css')
    );
    // The redesigned admin pages. Enqueued here so the sheet is in the head: from the
    // page's own template it was printed at the end of the page, and the page showed
    // unstyled until then (most visibly Configurations, which is large).
    if (wpjobportal_admin_page_uses_redesign_css()) {
        wp_enqueue_style('wpjobportal-redesign-css', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/wpjobportaladmin_redesign.css', array(), wpjobportal_asset_version('includes/css/wpjobportaladmin_redesign.css'));
    }
    // wp_enqueue_style('wpjobportal-admin-tablet-css', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/wpjobportaladmin_tablet.css',array(),'1.1.1','(min-width: 651px) and (max-width: 782px)');
    // wp_enqueue_style('wpjobportal-admin-mobile-css', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/wpjobportaladmin_mobile.css',array(),'1.1.1','(min-width: 481px) and (max-width: 650px)');
    // wp_enqueue_style('wpjobportal-admin-oldmobile-css', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/wpjobportaladmin_oldmobile.css',array(),'1.1.1','(max-width: 480px)');
    if (is_rtl()) {
        wp_register_style('wpjobportal-admincss-rtl', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/wpjobportaladmin_rtl.css', array(), wpjobportal_asset_version('includes/css/wpjobportaladmin_rtl.css'));
        wp_enqueue_style('wpjobportal-admincss-rtl');
    }
    wp_enqueue_script( 'chosen', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/chosen/chosen.jquery.min.js', array(), wpjobportal_asset_version('includes/js/chosen/chosen.jquery.min.js'), false );
}
add_action( 'admin_enqueue_scripts', 'wpjobportal_admin_register_plugin_styles' );

/**
 * Whether the admin screen being loaded is one built on the redesigned layout:
 * Dashboard, Configurations, Translations, Licences and the Mode guide.
 *
 * @return bool
 */
function wpjobportal_admin_page_uses_redesign_css() {
    $wpjobportal_page = (string) WPJOBPORTALrequest::getVar('page', 'get', '');
    $wpjobportal_layout = (string) WPJOBPORTALrequest::getVar('wpjobportallt', 'get', '');
    switch ($wpjobportal_page) {
        case 'wpjobportal_configuration':
            return true;
        case 'wpjobportal':
            return in_array($wpjobportal_layout, array('', 'controlpanel', 'translations', 'licences'), true);
        case 'wpjobportal_postinstallation':
            return $wpjobportal_layout === 'modeguide';
    }
    return false;
}

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
                    WHERE job.id = " . (int) ($wpjobportal_id);
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
                    WHERE resume.id = " . (int) ($wpjobportal_id);
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
                echo '<meta name="description" content="'.esc_html($wpjobportal_description).'"/>'."\n";
            }
            if(!empty($wpjobportal_job->latitude) && !empty($wpjobportal_job->longitude)){
                echo '<meta job="place:location:latitude" content="'.esc_html($wpjobportal_job->latitude).'">'."\n";
                echo '<meta job="place:location:longitude" content="'.esc_html($wpjobportal_job->longitude).'">'."\n";
            }
        }
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

/**
 * The icon in front of a control panel menu entry.
 *
 * One markup for every entry, Core's and the add-ons' alike: a Font Awesome
 * glyph that takes the row's colour, so it matches the text, follows the
 * theme colours and changes with the row on hover and when current. The
 * entries used to carry PNG drawings in their own colours, which no style
 * could bring in line with the rest.
 *
 * @param string $wpjobportal_icon Font Awesome 4 name without "fa-", e.g. "briefcase".
 * @return string
 */
function wpjobportal_cp_menu_icon($wpjobportal_icon) {
    return '<span class="wjportal-cp-menu-icon" aria-hidden="true"><i class="fa fa-' . esc_attr($wpjobportal_icon) . '"></i></span>';
}

/**
 * Tags and attributes an inline SVG icon needs, for wp_kses().
 *
 * WPJOBPORTAL_ALLOWED_TAGS has no SVG, so an icon printed through it would
 * lose its drawing. Attribute names are lower case, as wp_kses() compares them.
 *
 * @param array $wpjobportal_extra More tags (e.g. the icon's wrapper) to allow alongside.
 * @return array
 */
function wpjobportal_svg_allowed_tags($wpjobportal_extra = array()) {
    $wpjobportal_shape = array(
        'class' => true, 'd' => true, 'points' => true, 'cx' => true, 'cy' => true, 'r' => true,
        'rx' => true, 'ry' => true, 'x' => true, 'y' => true, 'x1' => true, 'y1' => true,
        'x2' => true, 'y2' => true, 'width' => true, 'height' => true, 'fill' => true,
        'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true,
        'stroke-linejoin' => true, 'fill-rule' => true, 'clip-rule' => true, 'transform' => true,
    );
    $wpjobportal_tags = array(
        'svg' => array(
            'class' => true, 'xmlns' => true, 'viewbox' => true, 'width' => true, 'height' => true,
            'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true,
            'stroke-linejoin' => true, 'aria-hidden' => true, 'focusable' => true, 'role' => true,
        ),
        'g' => $wpjobportal_shape,
        'path' => $wpjobportal_shape,
        'polyline' => $wpjobportal_shape,
        'polygon' => $wpjobportal_shape,
        'circle' => $wpjobportal_shape,
        'ellipse' => $wpjobportal_shape,
        'line' => $wpjobportal_shape,
        'rect' => $wpjobportal_shape,
    );
    return array_merge($wpjobportal_tags, $wpjobportal_extra);
}

/**
 * Folder of the Elegant Design files (stylesheets and images), with a trailing slash.
 *
 * The old Elegant Design add-on defines ELEGANTDESIGN_PLUGIN_PATH/_URL as it
 * loads - before Core and Pro - and a constant cannot be changed afterwards.
 * Readers ask here instead, so Pro's copy is used while the old add-on is
 * still active but paused. Empty when nothing provides the design.
 *
 * @return string
 */
function wpjobportal_elegant_design_path() {
    return (string) apply_filters('wpjobportal_elegant_design_path', defined('ELEGANTDESIGN_PLUGIN_PATH') ? ELEGANTDESIGN_PLUGIN_PATH : '');
}

/**
 * URL of the Elegant Design files, with a trailing slash.
 *
 * @see wpjobportal_elegant_design_path()
 * @return string
 */
function wpjobportal_elegant_design_url() {
    return (string) apply_filters('wpjobportal_elegant_design_url', defined('ELEGANTDESIGN_PLUGIN_URL') ? ELEGANTDESIGN_PLUGIN_URL : '');
}

function wpjobportal_checkWPJPPluginInfo($wpjobportal_slug){

    if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
    $wp_filesystem = wpjobportal::wpjobportal_filesystem();

    // An add-on whose feature moved into a 2.7.0 product points at that product.
    $wpjobportal_superseded = WPJOBPORTAL_Superseded_Addons::plugin_info($wpjobportal_slug);
    if ($wpjobportal_superseded !== null) {
        return $wpjobportal_superseded + array(
            "disabled" => "",
            "class" => $wpjobportal_superseded['availability'] === "0" ? "js-btn-install-now" : "js-btn-green js-btn-active-now",
        );
    }

    $wpjobportal_url = "";
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
        $wpjobportal_url = "plugins.php?s=" . strtok($wpjobportal_slug, '/') . "&plugin_status=inactive";
    }else if(!$wp_filesystem->exists(WP_PLUGIN_DIR . '/'.$wpjobportal_slug)){
        $wpjobportal_text = esc_html(__("Install Now","wp-job-portal"));
        $wpjobportal_disabled = "";
        $wpjobportal_class = "js-btn-install-now";
        $availability = "0";
        $wpjobportal_url = WPJOBPORTAL_Addon_Installer::plans_url();
    }

    return array("text" => $wpjobportal_text, "disabled" => $wpjobportal_disabled, "class" => $wpjobportal_class, "availability" => $availability, "url" => $wpjobportal_url);
}

/**
 * Fold a rendered control panel menu into collapsible sections.
 *
 * The employer menu had grown to twenty-one links under seven headings -
 * over twice the height of the window, and twelve of those links belonged
 * to Recruiting alone. Every heading is already printed the same way by
 * everyone who contributes to this menu: core here, and the Recruit and
 * Marketplace suites through wpjobportal_employer_dashboard_extension_links.
 * So the folding is done once, on the finished markup, and an add-on that
 * follows the existing convention is folded too without knowing about it.
 *
 * <details> was chosen over a scripted toggle deliberately: it opens and
 * closes, and announces itself to a screen reader, with no JavaScript at
 * all, so a menu is never left sealed shut by a script that failed to load.
 *
 * The section holding the current page is the one left open. Only the
 * Recruit Suite marks its current item, which is enough - the rest of the
 * menu is only ever rendered on the control panel itself - and where
 * nothing is marked the first section opens so the menu never reads as a
 * stack of closed doors.
 *
 * @param string $wpjobportal_html Rendered menu markup.
 * @return string
 */
/**
 * The features that are still worth pointing out, and who has seen them.
 *
 * A "New" mark is only useful until it has been read. These are kept per
 * person rather than per site: the badge is telling this administrator that
 * there is something here they have not opened, and somebody else opening it
 * first does not make that untrue for them.
 *
 * @return string[]
 */
function wpjobportal_new_features() {
    return (array) apply_filters('wpjobportal_new_features', array('recruitment', 'marketplace', 'typography'));
}

/**
 * Whether this person has yet to open a feature.
 *
 * @param string $wpjobportal_key Feature key.
 * @return bool
 */
function wpjobportal_is_new_feature($wpjobportal_key) {
    $wpjobportal_key = sanitize_key($wpjobportal_key);
    if ($wpjobportal_key === '' || !is_user_logged_in()) {
        return false;
    }
    if (!in_array($wpjobportal_key, wpjobportal_new_features(), true)) {
        return false;
    }
    $wpjobportal_seen = get_user_meta(get_current_user_id(), 'wpjobportal_seen_features', true);
    $wpjobportal_seen = is_array($wpjobportal_seen) ? $wpjobportal_seen : array();

    return !in_array($wpjobportal_key, $wpjobportal_seen, true);
}

/**
 * Remember that this person has opened a feature, once and for good.
 *
 * @param string $wpjobportal_key Feature key.
 * @return void
 */
function wpjobportal_mark_feature_seen($wpjobportal_key) {
    $wpjobportal_key = sanitize_key($wpjobportal_key);
    if ($wpjobportal_key === '' || !is_user_logged_in()) {
        return;
    }
    $wpjobportal_uid = get_current_user_id();
    $wpjobportal_seen = get_user_meta($wpjobportal_uid, 'wpjobportal_seen_features', true);
    $wpjobportal_seen = is_array($wpjobportal_seen) ? $wpjobportal_seen : array();
    if (in_array($wpjobportal_key, $wpjobportal_seen, true)) {
        return;
    }
    $wpjobportal_seen[] = $wpjobportal_key;
    update_user_meta($wpjobportal_uid, 'wpjobportal_seen_features', array_values(array_unique($wpjobportal_seen)));
}

/**
 * The mark itself. Prints nothing once the feature has been opened.
 *
 * @param string $wpjobportal_key Feature key.
 * @return void
 */
function wpjobportal_new_feature_badge($wpjobportal_key) {
    if (!wpjobportal_is_new_feature($wpjobportal_key)) {
        return;
    }
    echo '<span class="wpjobportal-new-badge">' . esc_html__('New', 'wp-job-portal') . '</span>';
}

/**
 * Opening a feature is what clears its mark.
 *
 * The recruiting and marketplace screens are reached from the sidebar the
 * badge sits in, so the moment one of their pages is requested the person has
 * plainly found it. Typography is different: the badge is inside the Colors
 * screen rather than beside a link to it, so that one is cleared by the
 * template after it has had its one showing.
 *
 * @return void
 */
function wpjobportal_mark_opened_feature_seen() {
    if (!is_admin() || !WPJOBPORTALrequest::has('page', 'get')) {
        return;
    }
    $wpjobportal_page = sanitize_key((string) WPJOBPORTALrequest::getVar('page', 'get', '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

    if (strpos($wpjobportal_page, 'wpjobportal_recruit') === 0) {
        wpjobportal_mark_feature_seen('recruitment');
    }
    if (strpos($wpjobportal_page, 'wpjobportal_marketplace') === 0) {
        wpjobportal_mark_feature_seen('marketplace');
    }
}
add_action('admin_init', 'wpjobportal_mark_opened_feature_seen');

function wpjobportal_group_control_panel_menu($wpjobportal_html) {
    if (strpos($wpjobportal_html, 'wjportal-section-title') === false) {
        return $wpjobportal_html;
    }

    $wpjobportal_parts = preg_split(
        '#<h3[^>]*\bclass="[^"]*\bwjportal-section-title\b[^"]*"[^>]*>(.*?)</h3>#s',
        $wpjobportal_html,
        -1,
        PREG_SPLIT_DELIM_CAPTURE
    );

    // A menu that does not split is a menu we do not understand; leave it be.
    if (!is_array($wpjobportal_parts) || count($wpjobportal_parts) < 3) {
        return $wpjobportal_html;
    }

    $wpjobportal_out = $wpjobportal_parts[0];
    $wpjobportal_groups = array();

    for ($wpjobportal_i = 1; $wpjobportal_i < count($wpjobportal_parts); $wpjobportal_i += 2) {
        $wpjobportal_groups[] = array(
            'title' => $wpjobportal_parts[$wpjobportal_i],
            'body'  => isset($wpjobportal_parts[$wpjobportal_i + 1]) ? $wpjobportal_parts[$wpjobportal_i + 1] : '',
        );
    }

    $wpjobportal_current = -1;
    foreach ($wpjobportal_groups as $wpjobportal_index => $wpjobportal_group) {
        if (strpos($wpjobportal_group['body'], 'is-active') !== false) {
            $wpjobportal_current = $wpjobportal_index;
            break;
        }
    }
    if ($wpjobportal_current < 0) {
        $wpjobportal_current = 0;
    }

    /*
     * Only a section long enough to be the problem folds.
     *
     * Folding all of them put Add Job, My Jobs, Add Company - the things this
     * panel is for - behind a click each, to solve a length problem none of
     * them caused. On the employer menu one section is twelve entries and
     * every other is one to three; on the job seeker's the longest is five.
     * So the test is length, not name: it reaches the section that is
     * actually long, in whatever language it is written, and reaches a future
     * add-on that grows just as long. Everything short enough to show stays
     * shown.
     */
    $wpjobportal_fold_over = (int) apply_filters('wpjobportal_cp_menu_fold_threshold', 6);

    foreach ($wpjobportal_groups as $wpjobportal_index => $wpjobportal_group) {
        $wpjobportal_count = preg_match_all('#<a\s#i', $wpjobportal_group['body']);

        if ($wpjobportal_fold_over < 1 || $wpjobportal_count <= $wpjobportal_fold_over) {
            // Short enough to stand open, exactly as it did before any of this.
            $wpjobportal_out .= '<h3 class="wjportal-section-title">' . $wpjobportal_group['title'] . '</h3>'
                . $wpjobportal_group['body'];
            continue;
        }

        $wpjobportal_open = ($wpjobportal_index === $wpjobportal_current) ? ' open' : '';

        $wpjobportal_out .= '<details class="wjportal-cp-group"' . $wpjobportal_open . '>'
            . '<summary class="wjportal-section-title wjportal-cp-group-summary">'
            . '<span class="wjportal-cp-group-name">' . $wpjobportal_group['title'] . '</span>'
            . '<span class="wjportal-cp-group-count">' . esc_html(number_format_i18n($wpjobportal_count)) . '</span>'
            . '</summary>'
            . '<div class="wjportal-cp-group-body">' . $wpjobportal_group['body'] . '</div>'
            . '</details>';
    }

    return $wpjobportal_out;
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

/**
 * Switch on the dashboard menu links a bundle brings, the first time it is
 * activated on this site.
 *
 * Each stand-alone add-on used to do this for itself on activation - Credits
 * set empcredits and the rest to 1 - so the feature a customer had just bought
 * appeared in the menu. The features now ship inside Pro and Marketplace Suite,
 * whose activation did not, so Departments, Folders, Messages, Resume Search
 * and Credits all arrived with their links hidden, and an employer had no way
 * to reach them until an administrator found the setting.
 *
 * Once per bundle per site: the flag is kept, so an administrator who later
 * hides one of these links keeps that choice through a deactivate and
 * reactivate. Only rows that already exist are written - the setting names are
 * Core's, and a missing row is left for Core's own install or migration.
 *
 * @param string   $wpjobportal_owner Bundle slug, e.g. 'pro'.
 * @param string[] $wpjobportal_keys  Setting names (wj_portal_config.configname).
 */
function wpjobportal_switch_on_menu_links($wpjobportal_owner, $wpjobportal_keys) {
    global $wpdb;
    $wpjobportal_flag = 'wpjobportal_menu_links_on_' . sanitize_key($wpjobportal_owner);
    if (get_option($wpjobportal_flag)) {
        return;
    }
    foreach ((array) $wpjobportal_keys as $wpjobportal_key) {
        wpjobportaldb_direct::query($wpdb->prepare(
            "UPDATE %i SET configvalue = '1' WHERE configname = %s",
            $wpdb->prefix . 'wj_portal_config',
            sanitize_key($wpjobportal_key)
        ));
    }
    update_option($wpjobportal_flag, 1, false);
}

/**
 * Whether an add-on's section of the employer's dashboard menu is switched on.
 *
 * For the sections the suites add - em_recruiting_menu, em_marketplace_menu.
 * A site whose database does not have the setting yet (its migration has not
 * run) shows the section, which is what it did before the setting existed.
 */
function wpjobportal_employer_menu_section_on($wpjobportal_configname) {
    $wpjobportal_config = WPJOBPORTALincluder::getJSModel('configuration')->getConfigByFor('emcontrolpanel');
    if (!is_array($wpjobportal_config) || !isset($wpjobportal_config[$wpjobportal_configname])) {
        return true;
    }
    return (string) $wpjobportal_config[$wpjobportal_configname] !== '0';
}

/**
 * Whether one of the links an add-on puts in a dashboard menu is switched on.
 *
 * The section switches above hide a whole section; these hide one link in it -
 * Recruiting Overview, Assigned Jobs, Company Verification and the rest - the
 * way My Jobs or Messages can be hidden. Same rule for a missing row: shown.
 *
 * @param string $wpjobportal_configname e.g. 'em_recruit_overview'.
 * @param string $wpjobportal_configfor  'emcontrolpanel' or 'jscontrolpanel'.
 */
function wpjobportal_menu_link_on($wpjobportal_configname, $wpjobportal_configfor = 'emcontrolpanel') {
    // One read per panel per request: a menu asks once for each of its links.
    static $wpjobportal_configs = array();
    if (!isset($wpjobportal_configs[$wpjobportal_configfor])) {
        $wpjobportal_configs[$wpjobportal_configfor] = WPJOBPORTALincluder::getJSModel('configuration')->getConfigByFor($wpjobportal_configfor);
    }
    $wpjobportal_config = $wpjobportal_configs[$wpjobportal_configfor];
    if (!is_array($wpjobportal_config) || !isset($wpjobportal_config[$wpjobportal_configname])) {
        return true;
    }
    return (string) $wpjobportal_config[$wpjobportal_configname] !== '0';
}

/**
 * Add the add-on menu and dashboard-box switches a site is missing.
 *
 * They arrived inside the 2.7.0 migration after some sites had already run
 * it, and the updater does not run a migration twice. Without its row a switch
 * still shows on the settings screen, but saving it changes nothing - the
 * settings screen can only update rows that exist. Same rows and values as
 * activation.php and 270.sql; WHERE NOT EXISTS keeps a site's own choices.
 *
 * Once per site, and again only if this list grows.
 */
function wpjobportal_add_missing_menu_settings() {
    global $wpdb;
    $wpjobportal_settings = array(
        // configname => array(configfor, addon)
        'em_recruiting_menu' => array('emcontrolpanel', 'recruitsuite'),
        'em_marketplace_menu' => array('emcontrolpanel', 'marketplacesuite'),
        'em_recruit_overview' => array('emcontrolpanel', 'recruitsuite'),
        'em_recruit_jobs' => array('emcontrolpanel', 'recruitsuite'),
        'em_recruit_candidates' => array('emcontrolpanel', 'recruitsuite'),
        'em_recruit_pipeline' => array('emcontrolpanel', 'recruitsuite'),
        'em_recruit_tasks' => array('emcontrolpanel', 'recruitsuite'),
        'em_recruit_interviews' => array('emcontrolpanel', 'recruitsuite'),
        'em_recruit_scorecards' => array('emcontrolpanel', 'recruitsuite'),
        'em_recruit_offers' => array('emcontrolpanel', 'recruitsuite'),
        'em_recruit_notifications' => array('emcontrolpanel', 'recruitsuite'),
        'em_recruit_insights' => array('emcontrolpanel', 'recruitsuite'),
        'em_recruit_pools' => array('emcontrolpanel', 'recruitsuite'),
        'em_recruit_team' => array('emcontrolpanel', 'recruitsuite'),
        'em_company_verification' => array('emcontrolpanel', 'marketplacesuite'),
        'em_promotion_schedule' => array('emcontrolpanel', 'marketplacesuite'),
        'em_recruiting_dashboard' => array('emcontrolpanel', 'recruitsuite'),
        'js_recruitment_center' => array('jscontrolpanel', 'recruitsuite'),
        'js_recruiting_center' => array('jscontrolpanel', 'recruitsuite'),
        'js_recruitment_dashboard' => array('jscontrolpanel', 'recruitsuite'),
    );
    $wpjobportal_flag = 'wpjobportal_menu_settings_added';
    if ((int) get_option($wpjobportal_flag) === count($wpjobportal_settings)) {
        return;
    }
    $wpjobportal_table = $wpdb->prefix . 'wj_portal_config';
    foreach ($wpjobportal_settings as $wpjobportal_name => $wpjobportal_row) {
        wpjobportaldb_direct::query($wpdb->prepare(
            "INSERT INTO %i (`configname`, `configvalue`, `configfor`, `addon`)
             SELECT %s, '1', %s, %s FROM DUAL
             WHERE NOT EXISTS (SELECT 1 FROM %i WHERE `configname` = %s)",
            $wpjobportal_table,
            $wpjobportal_name,
            $wpjobportal_row[0],
            $wpjobportal_row[1],
            $wpjobportal_table,
            $wpjobportal_name
        ));
    }
    update_option($wpjobportal_flag, count($wpjobportal_settings), false);
}

// The add-on updater that used to run here has been replaced by
// WPJOBPORTAL_Update_Service, which answers WordPress's own update hooks
// instead of running a separate wizard. Its files are kept out of the load
// path rather than deleted, so a site mid-upgrade is not left without either.

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
	   $query = wpjobportal::$_db->prepare("SELECT roleid FROM ".wpjobportal::$_db->prefix."wj_portal_users WHERE uid = %d", $user->ID);// small case id casuse notice
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

				WPJOBPORTAL_Migration_Service::run(wpjobportal::$_currentversion);


				// restore colors data
				//require_once(WPJOBPORTAL_PLUGIN_PATH . 'includes/css/style_color.php');
                $wpjobportal_color_string_values = get_option("wpjp_set_theme_colors");
                if($wpjobportal_color_string_values != ''){
                    $wpjobportal_json_values = json_decode($wpjobportal_color_string_values,true);
                    if(is_array($wpjobportal_json_values) && !empty($wpjobportal_json_values)){
                        if(WPJOBPORTALincluder::getJSModel('common')->isElegantDesignEnabled()){ // in case of elegent leave the color variable as is.
                            WPJOBPORTALincluder::getJSModel('theme')->wpjpGenerateColorVariablesFileElegant($wpjobportal_json_values);
                        }else{
                            WPJOBPORTALincluder::getJSModel('theme')->wpjpGenerateColorVariablesFile($wpjobportal_json_values);
                        }
                    }
                }

				// restore colors data end
			}
		}
	}
}
add_action('admin_notices', 'wpjobportal_show_expiry_error_notice');

function wpjobportal_show_expiry_error_notice() {
    if (!current_user_can('manage_options') || !class_exists('WPJOBPORTAL_Licence_Client')) {
        return;
    }

    // Read only. The stored state is kept current by the scheduled check and by
    // the Licences screen; an admin_notices callback that makes an HTTP request
    // would delay every admin page on this site.
    $wpjobportal_state = WPJOBPORTAL_Licence_Client::state();

    // Say nothing unless the server actually told us something negative. A
    // request that never got through, or a site with no key yet, is not a
    // customer whose licence expired, and telling them it did sends them to
    // support over our own connectivity.
    $wpjobportal_quiet = array('', 'no_state', 'no_key', 'unreachable', 'protocol_mismatch', 'deactivated');
    if (!empty($wpjobportal_state['ok']) || in_array($wpjobportal_state['reason'], $wpjobportal_quiet, true)) {
        return;
    }
    ?>
        <div class="notice notice-error is-dismissible">
            <p>
                <?php echo esc_html__('Your WP Job Portal licence is not active. Everything you have installed keeps working; updates and support are paused until it is renewed.', 'wp-job-portal'); ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal&wpjobportallt=licences')); ?>"><?php echo esc_html__('Check licence', 'wp-job-portal'); ?></a>
            </p>
        </div>
    <?php
}

// function wpjobportal_load_block_editor_assets() {

//     // --- NEW: Check if Block Editor is active ---
//     $screen = get_current_screen();
//     if (!$screen || !$screen->is_block_editor()) {
//         return; // Don't load React script for Classic Editor
//     }
//     //include_once WPJOBPORTAL_PLUGIN_PATH . 'modules/zywrap/model.php';

//     // Register our new script
//     include_once WPJOBPORTAL_PLUGIN_PATH . 'modules/zywrap/model.php';

//     wp_register_script(
//         'wpjobportal-zywrap-editor',
//         WPJOBPORTAL_PLUGIN_URL . 'includes/js/zywrap/zywrap-editor.js',
//         array('wp-plugins', 'wp-element', 'wp-components', 'wp-data', 'wp-util'), // This dependency will now work
//         '',
//         true
//     );
//     // Get all data from our local DB tables
//     $playground_data = WPJOBPORTALincluder::getJSModel('zywrap')->getPlaygroundData();
//     $api_key = get_option('wpjobportal_zywrap_api_key', '');

//     // Pass all data and security nonces to the JavaScript file
//     wp_localize_script('wpjobportal-zywrap-editor', 'zywrapEditorData', array(
//         'categories'      => $playground_data['categories'],
//         'models'          => $playground_data['models'],
//         'languages'       => $playground_data['languages'],
//         'templates'       => $playground_data['templates'],
//         'wrappers_nonce'  => wp_create_nonce('zywrap_get_wrappers'),
//         'execute_nonce'   => wp_create_nonce('zywrap_execute_proxy'),
//         // === ADDED: Data for Warnings ===
//         'has_api_key'     => ($api_key),
//         'settings_url'    => admin_url("admin.php?page=wpjobportal_zywrap&wpjobportallt=zywrap"),
//         'warning_key'     => __('Setup Required: Please add your API Key in settings.', 'wp-job-portal'),
//         'warning_sync'    => __('Action Needed: Please sync the Wrapper Catalog (Step 2) in settings.', 'wp-job-portal'),
//         'plugin_url'      => WPJOBPORTAL_PLUGIN_URL // For the warning icon
//     ));

//     // Load the script
//     wp_enqueue_script('wpjobportal-zywrap-editor');
// }

// === MODIFIED: HOOKS FOR BOTH EDITORS ===
// Block Editor (Gutenberg)

//add_action('add_meta_boxes', 'wpjobportal_render_zywrap_meta_box');
//add_action('enqueue_block_editor_assets', 'wpjobportal_load_block_editor_assets');

// Classic Editor
add_action('media_buttons', 'wpjobportal_add_zywrap_classic_button');
add_action('admin_footer', 'wpjobportal_add_zywrap_classic_modal');
add_action('admin_enqueue_scripts', 'wpjobportal_load_classic_editor_assets');
//add_action( 'admin_enqueue_scripts', 'wpjobportal_admin_register_plugin_styles' );


/**
     * Renders the root div for our React component to mount to.
     * [cite: `modules/zywrap/controller.php`]
     */
    function wpjobportal_render_zywrap_meta_box() {
        // This is the root element our React script will attach to.
        echo '<div id="zywrap-editor-root"></div>';
    }

    /**
     * Enqueues the new JavaScript file and passes all our database data
     * (categories, models, etc.) to it.
     * [cite: `modules/zywrap/controller.php`]
     */
    function wpjobportal_load_block_editor_assets() {

        // --- NEW: Check if Block Editor is active ---
        $screen = get_current_screen();
        if (!$screen || !$screen->is_block_editor()) {
            return; // Don't load React script for Classic Editor
        }
        //include_once WPJOBPORTAL_PLUGIN_PATH . 'modules/zywrap/model.php';

        // Register our new script
        include_once WPJOBPORTAL_PLUGIN_PATH . 'modules/zywrap/model.php';

        wp_register_script(
            'wpjobportal-zywrap-editor',
            WPJOBPORTAL_PLUGIN_URL . 'includes/js/zywrap/zywrap-editor.js',
            array('wp-plugins', 'wp-element', 'wp-components', 'wp-data', 'wp-util'), // This dependency will now work
            wpjobportal::$_currentversion,
            true
        );
        // Get all data from our local DB tables
        $playground_data = WPJOBPORTALincluder::getJSModel('zywrap')->getPlaygroundData();
        $api_key = get_option('wpjobportal_zywrap_api_key', '');

        // Pass all data and security nonces to the JavaScript file
        wp_localize_script('wpjobportal-zywrap-editor', 'zywrapEditorData', array(
            'categories'      => $playground_data['categories'],
            'models'          => $playground_data['models'],
            'languages'       => $playground_data['languages'],
            'templates'       => $playground_data['templates'],
            'wrappers_nonce'  => wp_create_nonce('zywrap_get_wrappers'),
            'execute_nonce'   => wp_create_nonce('zywrap_execute_proxy'),
            // === ADDED: Data for Warnings ===
            'has_api_key'     => ($api_key),
            'settings_url'    => admin_url("admin.php?page=wpjobportal_zywrap&wpjobportallt=zywrap"),
            'warning_key'     => __('Setup Required: Please add your API Key in settings.', 'wp-job-portal'),
            'warning_sync'    => __('Action Needed: Please sync the Wrapper Catalog (Step 2) in settings.', 'wp-job-portal'),
            'plugin_url'      => WPJOBPORTAL_PLUGIN_URL // For the warning icon
        ));

        // Load the script
        wp_enqueue_script('wpjobportal-zywrap-editor');
    }

    // === NEW: CLASSIC EDITOR FUNCTIONS ===

    /**
     * Adds the "Add Zywrap Content" button next to "Add Media".
     */
    function wpjobportal_add_zywrap_classic_button() {
        // === MODIFIED: Removed the API key check so the button always shows ===
        echo '<a href="#" id="zywrap-open-modal-button" class="button" title="' . esc_attr__('AI Content Generation', 'wp-job-portal') . '">
              <span class="dashicons dashicons-testimonial"></span>
              ' . esc_html__('AI Content Generation', 'wp-job-portal') . '
              </a>';
    }

    /**
     * Loads the new jQuery script and modal styles for the Classic Editor.
     */
    function wpjobportal_load_classic_editor_assets($hook) {
        if ('post.php' !== $hook && 'post-new.php' !== $hook) {
            return;
        }

        // Load these assets only for the Classic Editor. The modal markup is
        // rendered later in admin_footer so enqueueing can never send output
        // before an admin controller performs a redirect.
        $screen = get_current_screen();
        if (!$screen || $screen->is_block_editor()) {
            return;
        }

        // We must enqueue Select2 with its full path, not just by its handle.
        // wp_enqueue_style('wpjobportal-select2css', WPJOBPORTAL_PLUGIN_URL . 'includes/css/select2.min.css', array(), wpjobportal::$_currentversion);
        // wp_enqueue_script('wpjobportal-select2js', WPJOBPORTAL_PLUGIN_URL . 'includes/js/select2.min.js', array('jquery'), wpjobportal::$_currentversion, true);

        // Register our new classic editor script
        wp_register_script(
            'wpjobportal-zywrap-classic-editor',
            WPJOBPORTAL_PLUGIN_URL . 'includes/js/zywrap/zywrap-editor-classic.js',
            array('jquery'), // This dependency will now work
            wpjobportal::$_currentversion,
            true
        );

        // Get all data from our local DB tables
        $playground_data = WPJOBPORTALincluder::getJSModel('zywrap')->getPlaygroundData();
        $api_key = get_option('wpjobportal_zywrap_api_key', '');

        // Pass all data and nonces to this script
        wp_localize_script('wpjobportal-zywrap-classic-editor', 'wpjpzywrapClassicData', array(
            'categories'      => $playground_data['categories'],
            'models'          => $playground_data['models'],
            'languages'       => $playground_data['languages'],
            'templates'       => $playground_data['templates'],
            'wrappers_nonce'  => wp_create_nonce('zywrap_get_wrappers'),
            'execute_nonce'   => wp_create_nonce('zywrap_execute_proxy'),
            'wrappers_nonce'  => wp_create_nonce('zywrap_get_wrappers'),
            'all_wrappers_nonce'  => wp_create_nonce('zywrap_get_all_wrappers'),
            'schema_nonce'  => wp_create_nonce('zywrap_get_schema'),
            'ajax_url'        => admin_url('admin-ajax.php'),
            'loading_text'    => esc_js(__('Loading', 'wp-job-portal').'...'),
            'generating_text' => esc_js(__('Generating', 'wp-job-portal').'...'),
            'run_text'        => esc_js(__('Generate', 'wp-job-portal')),
            'error_text'      => esc_js(__('Error', 'wp-job-portal').': '),
            'validation_text' => esc_js(__('Please select a Wrapper.', 'wp-job-portal')),
            'additional_instructions' => esc_js(__('Additional Free-form Instructions.', 'wp-job-portal')),
            'core_inputs' => esc_js(__('Core Inputs (Optional)', 'wp-job-portal')),
            'additional_context' => esc_js(__('Additional Context (Optional)', 'wp-job-portal')),
            // === ADDED: Data for Warnings ===
            'has_api_key'     => !empty($api_key),
            'settings_url'    => admin_url("admin.php?page=wpjobportal_zywrap&wpjobportallt=zywrap"),
            'warning_key'     => __('Setup Required: Please add your API Key in settings.', 'wp-job-portal'),
            'warning_sync'    => __('Action Needed: Please sync the Wrapper Catalog (Step 2) in settings.', 'wp-job-portal'),
        ));
        // language strings to be translated
        wp_localize_script('wpjobportal-zywrap-classic-editor', 'wpjpZywrapStrings', wpjobportal_zywrap_editor_strings());

        // Load the script
        wp_enqueue_script('wpjobportal-zywrap-classic-editor');
    }

    /**
     * Renders the hidden HTML for the modal popup in the admin footer.
     */
    function wpjobportal_add_zywrap_classic_modal() {
        global $pagenow;

        if ('post.php' !== $pagenow && 'post-new.php' !== $pagenow) {
            return;
        }

        // Gutenberg has its own footer callback below.
        $screen = get_current_screen();
        if (!$screen || $screen->is_block_editor()) {
            return;
        }

        WPJOBPORTALincluder::include_file('zywrap', 'admin_classic_modal');
    }


    // zwrap js
    add_action('enqueue_block_editor_assets', function() {
        // Enqueue the React logic
        wp_enqueue_script(
            'wpjobportal-zywrap-gutenberg-editor',
            plugin_dir_url(__FILE__) . 'includes/js/zywrap/zywrap-editor.js',
            array('wp-plugins',
                'wp-i18n',
                'wp-edit-post',
                'wp-element',
                'wp-components',
                'wp-compose',      // <--- Required for createHigherOrderComponent
                'wp-block-editor', // <--- Required for BlockControls
                'wp-hooks',        // <--- Required for addFilter
                'jquery'
            ),
            filemtime(plugin_dir_path(__FILE__) . 'includes/js/zywrap/zywrap-editor.js'),
            true
        );
        if (function_exists('wp_set_script_translations')) {
            wp_set_script_translations('wpjobportal-zywrap-gutenberg-editor', 'wp-job-portal', WPJOBPORTAL_PLUGIN_PATH . 'languages');
        }

        // Enqueue the jQuery Modal logic
        wp_enqueue_script(
            'wpjobportal-zywrap-classic-logic',
            plugin_dir_url(__FILE__) . 'includes/js/zywrap/zywrap-editor-classic.js',
            array('jquery'),
            filemtime(plugin_dir_path(__FILE__) . 'includes/js/zywrap/zywrap-editor-classic.js'),
            true
        );

        // Enqueue the standard localized data (categories, models, etc.)

        // Get all data from our local DB tables
        $playground_data = WPJOBPORTALincluder::getJSModel('zywrap')->getPlaygroundData();
        $api_key = get_option('wpjobportal_zywrap_api_key', '');

        $data_array = array(
            'categories'      => $playground_data['categories'],
            'models'          => $playground_data['models'],
            'languages'       => $playground_data['languages'],
            'templates'       => $playground_data['templates'],
            'wrappers_nonce'  => wp_create_nonce('zywrap_get_wrappers'),
            'execute_nonce'   => wp_create_nonce('zywrap_execute_proxy'),
            'wrappers_nonce'  => wp_create_nonce('zywrap_get_wrappers'),
            'all_wrappers_nonce'  => wp_create_nonce('zywrap_get_all_wrappers'),
            'ajax_url'        => admin_url('admin-ajax.php'),
            'loading_text'    => esc_js(__('Loading', 'wp-job-portal').'...'),
            'generating_text' => esc_js(__('Generating', 'wp-job-portal').'...'),
            'run_text'        => esc_js(__('Generate', 'wp-job-portal')),
            'error_text'      => esc_js(__('Error', 'wp-job-portal').': '),
            'validation_text' => esc_js(__('Please select a Wrapper.', 'wp-job-portal')),
            // === ADDED: Data for Warnings ===
            'has_api_key'     => !empty($api_key),
            'settings_url'    => admin_url("admin.php?page=wpjobportal_zywrap&wpjobportallt=zywrap"),
            'warning_key'     => __('Setup Required: Please add your API Key in settings.', 'wp-job-portal'),
            'warning_sync'    => __('Action Needed: Please sync the Wrapper Catalog (Step 2) in settings.', 'wp-job-portal'),
        );

        wp_localize_script('wpjobportal-zywrap-gutenberg-editor', 'zywrapEditorData', $data_array);
        wp_localize_script('wpjobportal-zywrap-classic-logic', 'wpjpzywrapClassicData', $data_array);
        wp_localize_script('wpjobportal-zywrap-classic-logic', 'wpjpZywrapStrings', wpjobportal_zywrap_editor_strings());
    });

    // Load the modal HTML in the admin footer so it's available to JS
    add_action('admin_footer', function() {
        $screen = get_current_screen();
        if ($screen && $screen->is_block_editor()) {
            include_once plugin_dir_path(__FILE__) . 'modules/zywrap/admin_classic_modal.php';
        }
    });

/**
 * The texts the Zywrap modal script (zywrap-editor-classic.js) shows. The script
 * runs in the classic editor, the block editor and Elementor; each of them hands
 * it this same list as wpjpZywrapStrings.
 */
function wpjobportal_zywrap_editor_strings() {
    return array(
        'editor_not_found'    => __('Could not find an text area to insert into.', 'wp-job-portal'),
        'select_category_text' => __('Select Category', 'wp-job-portal'),
        'select_wrapper_text'  => __('Select Wrapper', 'wp-job-portal'),
        'default_text'         => __('Default', 'wp-job-portal'),
        'search_templates_text'=> __('Search Templates', 'wp-job-portal'),
        'close_search_text'    => __('Close Search', 'wp-job-portal'),
        'no_records_found'     => __('No Records Found', 'wp-job-portal'),
        'records_found_text'   => "+ ".__('records found', 'wp-job-portal'),
        'error_text'           => __('Error', 'wp-job-portal'),
        'select_wrapper_first' => __('Please select a wrapper first.', 'wp-job-portal'),
        'generating_text'      => __('Generating', 'wp-job-portal') . '...',
        'generating_wait_text' => __('Generating content, please wait...', 'wp-job-portal'),
        'unknown_error_text'   => __('Unknown error occurred.', 'wp-job-portal'),
        'output_empty_text'    => __('The output is empty.', 'wp-job-portal'),
    );
}

// NEW: Hook specifically for Elementor Editor
add_action('elementor/editor/after_enqueue_scripts', 'wpjobportal_load_classic_editor_assets_elementor');

function wpjobportal_load_classic_editor_assets_elementor($hook) {
    // 1. Determine if we are in Elementor
    $is_elementor = (WPJOBPORTALrequest::getVar('action', 'get', '') == 'elementor') || (WPJOBPORTALrequest::has('elementor-preview', 'get'));

    // 2. Determine if we are on a standard Post/Page edit screen
    $is_post_page = ($hook === 'post.php' || $hook === 'post-new.php');

    // If it's neither, don't load (to prevent errors on other admin pages)
    if (!$is_post_page && !$is_elementor) {
        return;
    }

    // Register & Enqueue your script
    wp_enqueue_script(
        'wpjobportal-zywrap-classic-editor',
        WPJOBPORTAL_PLUGIN_URL . 'includes/js/zywrap/zywrap-editor-classic.js',
        array('jquery'),
        wpjobportal::$_currentversion,
        true
    );

    // Get your data
    $playground_data = WPJOBPORTALincluder::getJSModel('zywrap')->getPlaygroundData();
    $api_key = get_option('wpjobportal_zywrap_api_key', '');

    // Pass data to JS
    wp_localize_script('wpjobportal-zywrap-classic-editor', 'wpjpzywrapClassicData', array(
        'categories'      => $playground_data['categories'],
        'models'          => $playground_data['models'],
        'languages'       => $playground_data['languages'],
        'templates'       => $playground_data['templates'],
        'wrappers_nonce'  => wp_create_nonce('zywrap_get_wrappers'),
        'execute_nonce'   => wp_create_nonce('zywrap_execute_proxy'),
        'all_wrappers_nonce'  => wp_create_nonce('zywrap_get_all_wrappers'),
        'ajax_url'        => admin_url('admin-ajax.php'),
        'has_api_key'     => !empty($api_key),
        // ... add other strings as needed ...
    ));
    wp_localize_script('wpjobportal-zywrap-classic-editor', 'wpjpZywrapStrings', wpjobportal_zywrap_editor_strings());
    wp_enqueue_style('wpjobportal-chosen-style', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/chosen/chosen.min.css', array(), wpjobportal_asset_version('includes/js/chosen/chosen.min.css'));
    // some specific styling for button
    $wpjobportal_inline_css = '
        #zywrap-open-modal-button{

            cursor: pointer;
            color: #6366f1;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            padding: 4px 5px;
            background: #fff;
            border: 1px solid #cbd5e1;
            transition: all 0.2s;
            margin: 5px 0;
        }

    ';

    wp_add_inline_style('wpjobportal-chosen-style', $wpjobportal_inline_css);
    wp_enqueue_script( 'chosen', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/chosen/chosen.jquery.min.js', array(), wpjobportal_asset_version('includes/js/chosen/chosen.jquery.min.js'), false );
}
