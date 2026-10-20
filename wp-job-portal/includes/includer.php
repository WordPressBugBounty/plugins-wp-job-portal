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
        $wp_filesystem = wpjobportal::wpjobportal_filesystem();

        if ($wpjobportal_module_name != null) {
            /*
             * Not when a look is installed: its layered sheet already carries
             * jobseekercp.css, inside the legacy layer where the look can
             * answer it. On a block theme the page content is rendered before
             * the head, so this ran before the layered setup registered the
             * handle without a file - and, registered here first with its
             * file, the whole sheet was printed a second time outside any
             * layer, where it beat the look: Elegant Design's dashboard tiles
             * came out centred with Core's grey footer bar.
             */
            $wpjobportal_look = apply_filters('wpjobportal_look_provider', array());
            if (empty($wpjobportal_look['theme']['src'])) {
                wp_enqueue_style('wpjobportal-jobseeker-style', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/jobseekercp.css', array('wpjobportal-design-system'), wpjobportal_asset_version('includes/css/jobseekercp.css'));
            }
            $file_path = self::getPluginPath($wpjobportal_module_name,'file',$filename);
            if ($wp_filesystem->exists(WPJOBPORTAL_PLUGIN_PATH . 'includes/css/inc-css/' . $wpjobportal_module_name . '-' . $filename . '.css.php')) {
                require_once(WPJOBPORTAL_PLUGIN_PATH . 'includes/css/inc-css/' . $wpjobportal_module_name . '-' . $filename . '.css.php');
            }
            if(is_array($file_path) && $wp_filesystem->exists($file_path['tmpl_file'])){
                // Same as the branch below. A layout that lives in an add-on
                // plugin resolves to a tmpl_file/inc_file pair rather than a
                // path, and only that shape was missing the call — so My
                // Companies, Add Company, Add Department and Resume Search were
                // the four employer screens with no document title, falling back
                // to the WordPress page name in the browser tab and in search
                // results.
                WPJOBPORTALincluder::getJSModel('common')->addWPSEOHooks($wpjobportal_module_name,$filename);
                if ($wp_filesystem->exists($file_path['inc_file'])) {
                    require_once($file_path['inc_file']);
                }
                self::beginRender($file_path['tmpl_file']);
                include_once $file_path['tmpl_file'];
                self::endRender();
            }else if($wp_filesystem->exists($file_path)){
                $wpjobportal_incfilepath = wpjobportalphplib::wpJP_explode('.php', $file_path);
                $wpjobportal_incfilename = $wpjobportal_incfilepath[0].'.inc.php';
            // to handle page title
                WPJOBPORTALincluder::getJSModel('common')->addWPSEOHooks($wpjobportal_module_name,$filename);
                if ($wp_filesystem->exists($wpjobportal_incfilename)) {
                    require_once($wpjobportal_incfilename);
                }
                self::beginRender($file_path);
                include_once $file_path; //
                self::endRender();
            }else{
            }
        } else {
            $file_path = self::getPluginPath($filename,'file');
            if($wp_filesystem->exists($file_path)){
                $wpjobportal_controller_class = 'WPJOBPORTAL' . $filename . 'Controller';
                $wpjobportal_controller_already_loaded = class_exists($wpjobportal_controller_class, false);

                include_once $file_path; //

                if ($wpjobportal_controller_already_loaded && class_exists($wpjobportal_controller_class, false)) {
                    new $wpjobportal_controller_class();
                }
            }else{
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

    /**
     * The templates currently being rendered, outermost first, by path.
     *
     * Shared templates used to ask "is Elegant Design active?" to decide
     * things that only ever depended on "is the page I am inside one of
     * Elegant Design's own copies?" - whether to draw breadcrumbs, whether a
     * job was passed. The two questions parted company the day the first
     * forked page was retired and a Core page rendered on an Elegant Design
     * site. This answers the right one.
     *
     * @var string[]
     */
    private static $rendering = array();

    private static function beginRender($path) {
        self::$rendering[] = (string) $path;
    }

    private static function endRender() {
        array_pop(self::$rendering);
    }

    /**
     * Is any template on the current render stack a design add-on's copy?
     *
     * A page template's partials inherit the answer, which is what a shared
     * partial needs: breadcrumbs belong to the page, not to the partial.
     *
     * @return bool
     */
    /**
     * Should templates come from the old Elegant Design plugin's folder?
     *
     * Only while that plugin is active. Pro provides Elegant Design since
     * 2.7.0 with Core's own templates, and a site that switched the old plugin
     * off usually still has its folder on disk; checking for the files alone
     * kept drawing the old markup under Pro's stylesheet.
     *
     * @return bool
     */
    public static function legacyElegantTemplates() {
        return in_array('elegantdesign', wpjobportal::$_active_addons, true);
    }

    public static function renderingOverride() {
        foreach (self::$rendering as $path) {
            if (strpos($path, 'wp-job-portal-elegantdesign') !== false) {
                return true;
            }
        }
        return false;
    }

    public static function getTemplate($wpjobportal_template_name, $wpjobportal_args = array()){
        $wpjobportal_template_name = wpjobportalphplib::wpJP_clean_file_path($wpjobportal_template_name);
        $wpjobportal_template = self::locateTemplate($wpjobportal_template_name,$wpjobportal_args);
        if(!empty($wpjobportal_args) && is_array($wpjobportal_args)){
            /*
             * Two naming conventions reach this door.
             *
             * Core's templates read prefixed variables - $wpjobportal_job,
             * $wpjobportal_control. Elegant Design's forked templates read the
             * bare names, and its callers pass them that way: 'job' => $job,
             * 'control' => 'newestjobs'. That was harmless while each side only
             * ever called its own copy. It stops being harmless the moment a
             * fork is deleted and an Elegant Design page calls a Core partial:
             * the partial sees no $wpjobportal_job and draws nothing.
             *
             * It runs the other way too. Every Core caller of the company card
             * passes 'wpjobportal_company'; Elegant Design's copy of that card
             * reads $company, and had been rendering empty on all six screens
             * that use it - and its resume card threw on $myresume->id the same
             * way. So a prefixed key is also provided bare, and a bare key
             * prefixed, unless the caller already supplied both. Around
             * eighteen Elegant Design pages call partials one way and its
             * partials read the other; they are being retired, and this leaves
             * with the last of them.
             */
            foreach ($wpjobportal_args as $wpjobportal_key => $wpjobportal_value) {
                if (!is_string($wpjobportal_key)) {
                    continue;
                }
                if (strpos($wpjobportal_key, 'wpjobportal_') === 0) {
                    $wpjobportal_twin = substr($wpjobportal_key, 12);
                } else {
                    $wpjobportal_twin = 'wpjobportal_' . $wpjobportal_key;
                }
                if ($wpjobportal_twin !== '' && !array_key_exists($wpjobportal_twin, $wpjobportal_args)) {
                    $wpjobportal_args[$wpjobportal_twin] = $wpjobportal_value;
                }
            }
            extract($wpjobportal_args);
        }
        self::beginRender($wpjobportal_template);
        $wpjobportal_result = include $wpjobportal_template;
        self::endRender();
        return $wpjobportal_result;
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
				if(self::legacyElegantTemplates() && file_exists(WP_PLUGIN_DIR.'/'.'wp-job-portal-elegantdesign/' . $wpjobportal_args['module_name'] . '/tmpl/views/' . $wpjobportal_template_name . '.php')){
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
				if(self::legacyElegantTemplates() && file_exists(WP_PLUGIN_DIR.'/'.'wp-job-portal-elegantdesign/' . $wpjobportal_module . '/tmpl/' . $wpjobportal_template_name . '.php')){
					$wpjobportal_template = WP_PLUGIN_DIR.'/'.'wp-job-portal-elegantdesign/'.$wpjobportal_module.'/tmpl/'.$wpjobportal_template_name.'.php';
				}else{	
					$wpjobportal_template = WPJOBPORTAL_PLUGIN_PATH.'modules/'.$wpjobportal_module.'/tmpl/'.$wpjobportal_template_name.'.php';
				}
            }
        }

        if (!isset($wpjobportal_template)) {
            $wpjobportal_template = '';
        }

        // Allow a consolidated provider to supply a module's template without
        // requiring its files to remain in a legacy one-feature plugin folder.
        return apply_filters(
            'wpjobportal_includer_template_path',
            $wpjobportal_template,
            ($wpjobportal_module_name !== null && $wpjobportal_module_name !== '')
                ? $wpjobportal_module_name
                : $wpjobportal_module,
            $wpjobportal_template_name,
            $wpjobportal_args
        );
    }

    public static function getPluginPath($wpjobportal_module,$type,$file_name = '') {
        $wpjobportal_module = wpjobportalphplib::wpJP_clean_file_path($wpjobportal_module);
        $file_path = '';
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
                            // PHP 8 refuses an array write onto a string. This branch returns the
                            // inc/tmpl pair, so start from an array rather than the '' default.
                            $file_path = array();
                            $file_path['inc_file'] = $wpjobportal_path . 'module/tmpl/' . $file_name . '.inc.php';
                            $file_path['tmpl_file'] = locate_template('wp-job-portal/' . $wpjobportal_module . '-' . $file_name . '.php', 0, 1);
						}elseif(self::legacyElegantTemplates() && file_exists(WP_PLUGIN_DIR.'/'.'wp-job-portal-elegantdesign/' . $wpjobportal_module . '/tmpl/' . $file_name . '.php')){
                            // PHP 8 refuses an array write onto a string. This branch returns the
                            // inc/tmpl pair, so start from an array rather than the '' default.
                            $file_path = array();
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
                                // PHP 8 refuses an array write onto a string. This branch returns the
                                // inc/tmpl pair, so start from an array rather than the '' default.
                                $file_path = array();
                                $file_path['inc_file'] = $wpjobportal_path . $wpjobportal_module.'/tmpl/' . $file_name . '.inc.php';
                                $file_path['tmpl_file'] = locate_template('wp-job-portal/' . $wpjobportal_module . '-' . $file_name . '.php', 0, 1);
							}elseif(self::legacyElegantTemplates() && file_exists(WP_PLUGIN_DIR.'/'.'wp-job-portal-elegantdesign/' . $wpjobportal_module . '-' . $file_name . '.php')){
        // PHP 8 refuses an array write onto a string. This branch returns the
        // inc/tmpl pair, so start from an array rather than the '' default.
        $file_path = array();
								$file_path['inc_file'] = $wpjobportal_path . $wpjobportal_module.'/tmpl/' . $file_name . '.inc.php';
								$file_path['tmpl_file'] = WP_PLUGIN_DIR.'/'.'wp-job-portal-elegantdesign/' . $wpjobportal_module . '-' . $file_name . '.php';
                            }else{
                                if(self::legacyElegantTemplates() && file_exists(WP_PLUGIN_DIR.'/'.'wp-job-portal-elegantdesign/' . $parent_module . '/'. $wpjobportal_module . '/tmpl/' . $file_name . '.php')){
                                    
                                    // PHP 8 refuses an array write onto a string. This branch returns the
                                    // inc/tmpl pair, so start from an array rather than the '' default.
                                    $file_path = array();
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
                }
            }else{
            $wpjobportal_path = WPJOBPORTAL_PLUGIN_PATH;
            switch ($type) {
                case 'file':
                    if($file_name != ''){
                        if (locate_template('wp-job-portal/' . $wpjobportal_module . '-' . $file_name . '.php', 0, 1)) {
                            // PHP 8 refuses an array write onto a string. This branch returns the
                            // inc/tmpl pair, so start from an array rather than the '' default.
                            $file_path = array();
                            $file_path['inc_file'] = $wpjobportal_path . 'modules/' . $wpjobportal_module . '/tmpl/' . $file_name . '.inc.php';
                            $file_path['tmpl_file'] = locate_template('wp-job-portal/' . $wpjobportal_module . '-' . $file_name . '.php', 0, 1);
						}elseif(self::legacyElegantTemplates() && file_exists(WP_PLUGIN_DIR.'/'.'wp-job-portal-elegantdesign/' . $wpjobportal_module . '/tmpl/' . $file_name . '.php')){

       // PHP 8 refuses an array write onto a string. This branch returns the
       // inc/tmpl pair, so start from an array rather than the '' default.
       $file_path = array();
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
        // Allow a consolidated provider to supply an existing module without
        // requiring its files to remain in a legacy one-feature plugin folder.
        return apply_filters(
            'wpjobportal_includer_plugin_path',
            $file_path,
            $wpjobportal_module,
            $type,
            $file_name
        );
    }


}

$wpjobportal_includer = new WPJOBPORTALincluder();
?>
