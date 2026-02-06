<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALincluder {

    function __construct() {

    }

    /*
     * Includes files
     */

    public static function include_file($filename, $wpjobportal_module_name = null) {
        // making usre no relative path is being used
        $filename = wpjobportalphplib::wpJP_clean_file_path($filename);
        $wpjobportal_module_name = wpjobportalphplib::wpJP_clean_file_path($wpjobportal_module_name);


        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
        if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ) {
            $creds = request_filesystem_credentials( site_url() );
            wp_filesystem( $creds );
        }

        if ($wpjobportal_module_name != null) {
            wp_enqueue_style('wpjobportal-jobseeker-style', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/jobseekercp.css');
            $file_path = self::getPluginPath($wpjobportal_module_name,'file',$filename);
            if ($wp_filesystem->exists(WPJOBPORTAL_PLUGIN_PATH . 'includes/css/inc-css/' . $wpjobportal_module_name . '-' . $filename . '.css.php')) {
                require_once(WPJOBPORTAL_PLUGIN_PATH . 'includes/css/inc-css/' . $wpjobportal_module_name . '-' . $filename . '.css.php');
            }
            if(is_array($file_path) && $wp_filesystem->exists($file_path['tmpl_file'])){
                if ($wp_filesystem->exists($file_path['inc_file'])) {
                    require_once($file_path['inc_file']);
                }
                include_once $file_path['tmpl_file'];
            }else if($wp_filesystem->exists($file_path)){
                $wpjobportal_incfilepath = wpjobportalphplib::wpJP_explode('.php', $file_path);
                $wpjobportal_incfilename = $wpjobportal_incfilepath[0].'.inc.php';
            // to handle page title
                WPJOBPORTALincluder::getJSModel('common')->addWPSEOHooks($wpjobportal_module_name,$filename);
                if ($wp_filesystem->exists($wpjobportal_incfilename)) {
                    require_once($wpjobportal_incfilename);
                }
                include_once $file_path; //
            }else{
                /*$file_path = self::getPluginPath('premiumplugin','file','missingaddon');
                if(is_array($file_path)){
                    include_once $file_path['tmpl_file'];
                }else{
                    include_once $file_path; //
                }*/
            }
        } else {
            $file_path = self::getPluginPath($filename,'file');
            if($wp_filesystem->exists($file_path)){
                include_once $file_path; //
            }else{
               /* $file_path = self::getPluginPath('premiumplugin','file');
                include_once $file_path; //*/
            }
        }



        return;
    }

    /*
     * Static function to handle the page slugs
     */

    public static function include_slug($page_slug) {
        include_once WPJOBPORTAL_PLUGIN_PATH . 'modules/wp-job-portal-controller.php';
    }

    /*
     * Static function for the model object
     */

    public static function getJSModel($wpjobportal_modelname) {
        $file_path = self::getPluginPath($wpjobportal_modelname,'model');
        include_once $file_path;
        $wpjobportal_classname = "WPJOBPORTAL" . $wpjobportal_modelname . 'Model';
        //var_dump($wpjobportal_classname);
        //exit();
        $obj = new $wpjobportal_classname();
        return $obj;
    }

    /*
     * Static function for the classes objects
     */

    public static function getObjectClass($wpjobportal_classname) {

        $file_path = self::getPluginPath($wpjobportal_classname,'class');
        include_once $file_path;
        $wpjobportal_classname = "WPJOBPORTAL" . $wpjobportal_classname ;
        $obj = new $wpjobportal_classname();
        return $obj;
    }

    /*
     * Static function for the classes not objects
     */

    public static function getClassesInclude($wpjobportal_classname) {
        $file_path = self::getPluginPath($wpjobportal_classname,'class');
        include_once $file_path;
    }

    /*
     * Static function for the table object
     */

    public static function getJSTable($wpjobportal_tableclass) {
        $file_path = self::getPluginPath($wpjobportal_tableclass,'table');
        require_once WPJOBPORTAL_PLUGIN_PATH . 'includes/tables/table.php';
        include_once $file_path;
        $wpjobportal_classname = "WPJOBPORTAL" . $wpjobportal_tableclass . 'Table';
        $obj = new $wpjobportal_classname();
        return $obj;
    }

    /*
     * Static function for the controller object
     */

    public static function getJSController($wpjobportal_controllername) {
        $file_path = self::getPluginPath($wpjobportal_controllername,'controller');

        include_once $file_path;
        $wpjobportal_classname = "WPJOBPORTAL".$wpjobportal_controllername . "Controller";
        $obj = new $wpjobportal_classname();
        return $obj;
    }
/*
    public static function loadComponents($filenames){
        if(!is_array($filenames)){
            $filenames = array($filenames);
        }
        foreach($filenames as $filename){
            //load component template
            $wpjobportal_templatepath = self::getComponentTemplatePath($filename);
            if(file_exists($wpjobportal_templatepath)){
                echo '<div id="wpjobportal-'.$filename.'" style="display:none;">';
                include $wpjobportal_templatepath;
                echo '</div>';
            }

            //load component js file
            $wpjobportal_jsfilepath = self::getComponentJsUrl($filename);
            wp_enqueue_script($filename,$wpjobportal_jsfilepath,array(),false,1);
        }
    }

    public static function getComponentJsUrl($filename){
        return esc_url(WPJOBPORTAL_PLUGIN_URL) . '/components_js/'.$filename.'.vue.js';
    }

    public static function getComponentTemplatePath($filename){
        return WPJOBPORTAL_PLUGIN_PATH . '/components/'.$filename.'.vue.php';
    }*/

    public static function getTemplate($wpjobportal_template_name, $wpjobportal_args = array()){
        $wpjobportal_template_name = wpjobportalphplib::wpJP_clean_file_path($wpjobportal_template_name);
        $wpjobportal_template = self::locateTemplate($wpjobportal_template_name,$wpjobportal_args);
        if(!empty($wpjobportal_args) && is_array($wpjobportal_args)){
            extract($wpjobportal_args);
        }
        return include $wpjobportal_template;
    }

    public static function getTemplateHtml($wpjobportal_template_name, $wpjobportal_args = array()){
        ob_start();
        self::getTemplate($wpjobportal_template_name, $wpjobportal_args);
        return ob_get_clean();
    }

    public static function locateTemplate($wpjobportal_template_name,$wpjobportal_args= array()){
        $wpjobportal_template_name = wpjobportalphplib::wpJP_clean_file_path($wpjobportal_template_name);
        $wpjobportal_module = wpjobportalphplib::wpJP_substr($wpjobportal_template_name, 0, wpjobportalphplib::wpJP_strpos($wpjobportal_template_name, '/'));
        $wpjobportal_template_name = wpjobportalphplib::wpJP_substr($wpjobportal_template_name, wpjobportalphplib::wpJP_strpos($wpjobportal_template_name, '/')+1);
        $wpjobportal_module_name = isset($wpjobportal_args['module_name']) ? $wpjobportal_args['module_name'] : null;
        /* ADDONS PLUGIN DIR FOR TEMPLATE => module_name  */
       if($wpjobportal_module_name!=null && $wpjobportal_module_name!=""){
    //To Manage Template Working IN Addons
            if(in_array($wpjobportal_args['module_name'], wpjobportal::$_active_addons)){
				if(WPJOBPORTALincluder::getJSModel('common')->isElegantDesignEnabled() && file_exists(WP_PLUGIN_DIR.'/'.'wp-job-portal-elegantdesign/' . $wpjobportal_args['module_name'] . '/tmpl/views/' . $wpjobportal_template_name . '.php')){
					$wpjobportal_template = WP_PLUGIN_DIR.'/'.'wp-job-portal-elegantdesign/' . $wpjobportal_args['module_name'] . '/tmpl/views/' . $wpjobportal_template_name . '.php';
				}else{	
					$wpjobportal_path = WP_PLUGIN_DIR.'/'.'wp-job-portal-'.$wpjobportal_args['module_name'].'/';
					$wpjobportal_template = $wpjobportal_path.'module/tmpl/views/'.$wpjobportal_template_name.'.php';
				}
            }
        }else{
            if($wpjobportal_module == 'templates'){
                $wpjobportal_template = WPJOBPORTAL_PLUGIN_PATH.'templates/'.$wpjobportal_template_name.'.php';
            }else{
				if(WPJOBPORTALincluder::getJSModel('common')->isElegantDesignEnabled() && file_exists(WP_PLUGIN_DIR.'/'.'wp-job-portal-elegantdesign/' . $wpjobportal_module . '/tmpl/' . $wpjobportal_template_name . '.php')){
					$wpjobportal_template = WP_PLUGIN_DIR.'/'.'wp-job-portal-elegantdesign/'.$wpjobportal_module.'/tmpl/'.$wpjobportal_template_name.'.php';
				}else{	
					$wpjobportal_template = WPJOBPORTAL_PLUGIN_PATH.'modules/'.$wpjobportal_module.'/tmpl/'.$wpjobportal_template_name.'.php';
				}
            }
        }

       return $wpjobportal_template;
    }

    public static function getPluginPath($wpjobportal_module,$type,$file_name = '') {
        $wpjobportal_module = wpjobportalphplib::wpJP_clean_file_path($wpjobportal_module);
        if($file_name != ''){
            $file_name = wpjobportalphplib::wpJP_clean_file_path($file_name);
        }

        //$wpjobportal_addons_secondry = array('socialmedia','facebook','linkedin','xing','folderresume','mystats','creditslog','creditspack','purchasehistory','purchase','userpackage','subscription','invoice','userpackage','jobalertsetting','package','jobseekerviewcompany','employerviewresume','rating','transactionlog','jobalertcities','paymentmethodconfiguration','paypal','Stripe','resumeformAdons','ResumeViewAdons','Stripe/init','coverletter');
        $wpjobportal_addons_secondry = array('socialmedia','facebook','linkedin','xing','folderresume','mystats','creditslog','creditspack','purchasehistory','purchase','userpackage','subscription','invoice','userpackage','jobalertsetting','package','jobseekerviewcompany','employerviewresume','rating','transactionlog','jobalertcities','paymentmethodconfiguration','paypal','Stripe','resumeformAdons','ResumeViewAdons','Stripe/init','coverletter','popup','multicompany');
        if(in_array($wpjobportal_module, wpjobportal::$_active_addons) && $wpjobportal_module != 'theme' && $wpjobportal_module != 'customfields'){

            $wpjobportal_path = WP_PLUGIN_DIR.'/'.'wp-job-portal-'.$wpjobportal_module.'/';
            switch ($type) {
                case 'file':
                    if($file_name != ''){
                        if (locate_template('wp-job-portal/' . $wpjobportal_module . '-' . $file_name . '.php', 0, 1)) {
                            $file_path['inc_file'] = $wpjobportal_path . 'module/tmpl/' . $file_name . '.inc.php';
                            $file_path['tmpl_file'] = locate_template('wp-job-portal/' . $wpjobportal_module . '-' . $file_name . '.php', 0, 1);
						}elseif(WPJOBPORTALincluder::getJSModel('common')->isElegantDesignEnabled() && file_exists(WP_PLUGIN_DIR.'/'.'wp-job-portal-elegantdesign/' . $wpjobportal_module . '/tmpl/' . $file_name . '.php')){
                            $file_path['inc_file'] = $wpjobportal_path . 'module/tmpl/' . $file_name . '.inc.php';
							$file_path['tmpl_file'] = WP_PLUGIN_DIR.'/'.'wp-job-portal-elegantdesign/'.$wpjobportal_module.'/tmpl/'.$file_name.'.php';
                        }else{
                            $file_path = $wpjobportal_path . 'module/tmpl/' . $file_name . '.php';

                        }
                    }else{
                        $file_path = $wpjobportal_path . 'module/controller.php';
                    }
                    break;
                case 'model':
                    $file_path = $wpjobportal_path . 'module/model.php';
                    break;
                case 'class':
                    $file_path = $wpjobportal_path . 'classes/' . $wpjobportal_module . '.php';
                    break;
                case 'controller':
                    $file_path = $wpjobportal_path . 'module/controller.php';
                    break;
                case 'table':
                    $file_path = $wpjobportal_path . 'includes/' . $wpjobportal_module . '-table.php';
                    break;
            }

        }elseif(in_array($wpjobportal_module, $wpjobportal_addons_secondry)){ // to handle the case of modules that are submodules for some addon
            $parent_module = '';
            switch ($wpjobportal_module) {// to identify addon for submodules.
                case 'folderresume':
                    $parent_module = 'folder';
                    break;
                    case 'socialmedia':
                    case 'facebook':
                    case 'linkedin':
                    case 'xing':
                    $parent_module = 'sociallogin';
                    break;
                case 'mystats':
                    $parent_module = 'reports';
                    break;
                case 'jobalertsetting':
                    $parent_module = 'jobalert';
                    break;
                case 'jobalertcities':
                    $parent_module = 'jobalert';
                    break;
                case 'creditslog':
                case 'creditspack':
                case 'purchasehistory':
                case 'purchase':
                case 'userpackage':
                case 'subscription':
                case 'package':
                case 'jobseekerviewcomny':
                case 'employerviewresume':
                case 'transactionlog':
                case 'paymentmethodconfiguration':
                case 'jobseekerviewcompany':
                case 'Stripe':
                case 'paypal':
                case 'invoice':
                case 'Stripe/init':
                case 'popup':
                    $parent_module = 'credits';
                    break;
                case 'customfields':
                    $parent_module = 'customfield';
                    break;
                case 'cronjob':
                    $parent_module = 'cronjob';
                    break;
                case 'rating':
                    $parent_module = 'resumeaction';
                    break;
                case 'resumeformAdons':
                    $parent_module  = 'advanceresumebuilder';
                    break;
                case 'ResumeViewAdons':
                    $parent_module  = 'advanceresumebuilder';
                    break;
                }
                if($parent_module == "customfield" && !in_array('customfield', wpjobportal::$_active_addons)){
                  $wpjobportal_path = WP_PLUGIN_DIR.'/'.'wp-job-portal/includes/';
                }else{
                    $wpjobportal_path = WP_PLUGIN_DIR.'/'.'wp-job-portal-'.$parent_module.'/';

                }
            if(in_array($parent_module, wpjobportal::$_active_addons) || $parent_module == "customfield"){
                switch ($type) {
                    case 'file':
                        if($file_name != ''){
                            if (locate_template('wp-job-portal/' . $wpjobportal_module . '-' . $file_name . '.php', 0, 1)) {
                                $file_path['inc_file'] = $wpjobportal_path . $wpjobportal_module.'/tmpl/' . $file_name . '.inc.php';
                                $file_path['tmpl_file'] = locate_template('wp-job-portal/' . $wpjobportal_module . '-' . $file_name . '.php', 0, 1);
							}elseif(WPJOBPORTALincluder::getJSModel('common')->isElegantDesignEnabled() && file_exists(WP_PLUGIN_DIR.'/'.'wp-job-portal-elegantdesign/' . $wpjobportal_module . '-' . $file_name . '.php')){
								$file_path['inc_file'] = $wpjobportal_path . $wpjobportal_module.'/tmpl/' . $file_name . '.inc.php';
								$file_path['tmpl_file'] = WP_PLUGIN_DIR.'/'.'wp-job-portal-elegantdesign/' . $wpjobportal_module . '-' . $file_name . '.php';
                            }else{
                                if(WPJOBPORTALincluder::getJSModel('common')->isElegantDesignEnabled() && file_exists(WP_PLUGIN_DIR.'/'.'wp-job-portal-elegantdesign/' . $parent_module . '/'. $wpjobportal_module . '/tmpl/' . $file_name . '.php')){
                                    
                                    $file_path['inc_file'] = $wpjobportal_path . $wpjobportal_module.'/tmpl/' . $file_name . '.inc.php';
                                    $file_path['tmpl_file'] = WP_PLUGIN_DIR.'/'.'wp-job-portal-elegantdesign/' . $parent_module . '/'. $wpjobportal_module . '/tmpl/' . $file_name . '.php';
                                } else {
                                    $file_path = $wpjobportal_path . $wpjobportal_module.'/tmpl/' . $file_name . '.php';
                                }
                            }
                        }else{
                            $file_path = $wpjobportal_path . $wpjobportal_module.'/controller.php';
                        }
                        break;
                    case 'model':
                        $file_path = $wpjobportal_path . $wpjobportal_module.'/model.php';
                        break;

                    case 'class':
                        $file_path = $wpjobportal_path . 'classes/' . $wpjobportal_module . '.php';
                        break;
                    case 'controller':
                        $file_path = $wpjobportal_path . $wpjobportal_module.'/controller.php';
                        break;
                    case 'table':
                        $file_path = $wpjobportal_path . 'includes/' . $wpjobportal_module . '-table.php';
                        break;
                }
            }else{
               // $file_path = self::getPluginPath('premiumplugin','file');
                }
            }else{
            $wpjobportal_path = WPJOBPORTAL_PLUGIN_PATH;
            switch ($type) {
                case 'file':
                    if($file_name != ''){
                        if (locate_template('wp-job-portal/' . $wpjobportal_module . '-' . $file_name . '.php', 0, 1)) {
                            $file_path['inc_file'] = $wpjobportal_path . 'modules/' . $wpjobportal_module . '/tmpl/' . $file_name . '.inc.php';
                            $file_path['tmpl_file'] = locate_template('wp-job-portal/' . $wpjobportal_module . '-' . $file_name . '.php', 0, 1);
						}elseif(WPJOBPORTALincluder::getJSModel('common')->isElegantDesignEnabled() && file_exists(WP_PLUGIN_DIR.'/'.'wp-job-portal-elegantdesign/' . $wpjobportal_module . '/tmpl/' . $file_name . '.php')){

							$file_path['inc_file'] = $wpjobportal_path . 'modules/' . $wpjobportal_module.'/tmpl/' . $file_name . '.inc.php';
							$file_path['tmpl_file'] = WP_PLUGIN_DIR.'/'.'wp-job-portal-elegantdesign/' . $wpjobportal_module . '/tmpl/' . $file_name . '.php';
                        }else{
                            $file_path = $wpjobportal_path . 'modules/' . $wpjobportal_module . '/tmpl/' . $file_name . '.php';
                        }
                    }else{
                        $file_path = $wpjobportal_path . 'modules/' . $wpjobportal_module . '/controller.php';
                    }
                    break;
                case 'model':
                        $file_path = $wpjobportal_path . 'modules/' . $wpjobportal_module . '/model.php';
                    break;

                case 'class':
                    $file_path = $wpjobportal_path . 'includes/classes/' . $wpjobportal_module . '.php';
                    break;
                case 'controller':
                        $file_path = $wpjobportal_path . 'modules/' . $wpjobportal_module . '/controller.php';
                    break;
                case 'table':
                    $file_path = $wpjobportal_path . 'includes/tables/' . $wpjobportal_module . '.php';;
                    break;
            }
        }
        //echo $file_path;exit()
        return $file_path;
    }


}

$wpjobportal_includer = new WPJOBPORTALincluder();
?>
