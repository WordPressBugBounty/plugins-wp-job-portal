<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Public company directory.
 *
 * Absorbed from the All Companies add-on. Provides the
 * [wpjobportal_all_companies] shortcode listing approved companies.
 *
 * Hook names, routes and output are preserved exactly, and the callbacks are
 * carried over unchanged, so a site that removes the old add-on sees no
 * difference. Registration is skipped entirely while that add-on is still
 * installed; see WPJOBPORTAL_Absorbed_Features.
 */
final class WPJOBPORTAL_Feature_allcompanies {

    public static function register() {
        $feature = new self();

        add_shortcode('wpjobportal_all_companies', array($feature, 'show_all_companies'));
    }

    /* ------------------------------------------------------------------ */
    /* Callbacks carried over unchanged from the add-on                    */
    /* ------------------------------------------------------------------ */

    public function show_all_companies($raw_args, $content = null) {
        //default set of parameters for the front end shortcodes
        ob_start();
        $defaults = array(
            'wpjobportalme' => 'allcompanies',
            'wpjobportallt' => 'companies',
        );


        $default_short_code_options = array(
            'hide_company_logo' => '',
            'hide_company_location' => '',
            'hide_company_name' => '',

            'locations' => '',
            'employers' => '',
            'ids' => '',

            'sorting' => '',
            'no_of_companies' => '',

        );

        $sanitized_args = shortcode_atts($defaults, $raw_args);
        $shortcode_options = shortcode_atts($default_short_code_options, $raw_args);

        if(isset(wpjobportal::$_data['sanitized_args']) && !empty(wpjobportal::$_data['sanitized_args'])){
            wpjobportal::$_data['sanitized_args'] += $sanitized_args;
        }else{
            wpjobportal::$_data['sanitized_args'] = $sanitized_args;
        }

        if(isset(wpjobportal::$_data['shortcode_options']) && !empty(wpjobportal::$_data['shortcode_options'])){
            wpjobportal::$_data['shortcode_options'] += $shortcode_options;
        }else{
            wpjobportal::$_data['shortcode_options'] = $shortcode_options;
        }

        $pageid = get_the_ID();
        wpjobportal::wpjobportal_setPageID($pageid);
        wpjobportal::wpjobportal_addStyleSheets();
        $offline = wpjobportal::$_config->getConfigurationByConfigName('offline');
        if ($offline == 1) {
            WPJOBPORTALlayout::getSystemOffline();
        } elseif (WPJOBPORTALincluder::getObjectClass('user')->isdisabled()) { // handling for the user disabled
            WPJOBPORTALlayout::getUserDisabledMsg();
        } else {
            $module = WPJOBPORTALrequest::getVar('wpjobportalme', null, 'allcompanies');
            WPJOBPORTALincluder::include_file($module);
        }
                unset(wpjobportal::$_data['sanitized_args']);
        $content .= ob_get_clean();
        return $content;
    }

}
