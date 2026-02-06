<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALshortcodes {

    function __construct() {

        add_shortcode('wpjobportal_employer_controlpanel', array($this, 'show_employer_controlpanel'));
        add_shortcode('wpjobportal_jobseeker_controlpanel', array($this, 'show_jobseeker_controlpanel'));

        add_shortcode('wpjobportal_job_search', array($this, 'show_job_search'));
        add_shortcode('wpjobportal_job', array($this, 'show_job'));

        add_shortcode('wpjobportal_job_categories', array($this, 'show_job_categories'));
        add_shortcode('wpjobportal_job_types', array($this, 'show_job_types'));
        add_shortcode('wpjobportal_my_appliedjobs', array($this, 'show_my_appliedjobs'));
        add_shortcode('wpjobportal_my_companies', array($this, 'show_my_companies'));

        add_shortcode('wpjobportal_my_departments', array($this, 'show_my_departments'));
        add_shortcode('wpjobportal_my_jobs', array($this, 'show_my_jobs'));
        add_shortcode('wpjobportal_my_resumes', array($this, 'show_my_resumes'));
        add_shortcode('wpjobportal_add_company', array($this, 'show_add_company'));
        add_shortcode('wpjobportal_add_department', array($this, 'show_add_department'));
        add_shortcode('wpjobportal_add_job', array($this, 'show_add_job'));
        add_shortcode('wpjobportal_add_resume', array($this, 'show_add_resume'));
        add_shortcode('wpjobportal_employer_registration', array($this, 'show_employer_registration'));
        add_shortcode('wpjobportal_jobseeker_registration', array($this, 'show_jobseeker_registration'));
        add_shortcode('wpjobportal_registration', array($this, 'show_registration'));

        add_shortcode('wpjobportal_jobseeker_my_stats', array($this, 'show_jobseeker_my_stats'));
        add_shortcode('wpjobportal_employer_my_stats', array($this, 'show_employer_my_stats'));
        add_shortcode('wpjobportal_login_page', array($this, 'show_login_page'));
        /**
        * @param wp job portal widgets Shortcdes
        * Support Blog template
        * add_shortcodes widget
        */
        add_shortcode('wpjobportal_jobs', array($this, 'show_jobs'));
        add_shortcode('wpjobportal_resumes', array($this, 'show_resumes'));
        add_shortcode('wpjobportal_companies', array($this, 'show_companies'));
        add_shortcode('wpjobportal_searchjob', array($this, 'show_searchjob'));
        add_shortcode('wpjobportal_searchresume', array($this, 'show_searchresume'));
        add_shortcode('wpjobportal_jobbycategory', array($this, 'show_jobbycategory'));
        add_shortcode('wpjobportal_jobbytypes', array($this, 'show_jobbytypes'));
        add_shortcode('wpjobportal_jobstats', array($this, 'show_jobstats'));
        add_shortcode('wpjobportal_jobsbycities', array($this, 'show_jobsbycity'));
        add_shortcode('wpjobportal_jobsbystate', array($this, 'show_jobsbystate'));
        add_shortcode('wpjobportal_jobsbycountries', array($this, 'show_jobsbycountries'));
        add_shortcode('wpjobportal_jobsonmap', array($this, 'show_jobsonmap'));

    }

    function show_employer_controlpanel($raw_args, $wpjobportal_content = null) {
        //default set of parameters for the front end shortcodes
        ob_start();
        $wpjobportal_defaults = array(
            'wpjobportalme' => 'employer',
            'wpjobportallt' => 'controlpanel',
        );

        $wpjobportal_default_short_code_options = array(
            'hide_profile_section' => '',
            'hide_graph' => '',
            'hide_recent_applications' => '',
            'hide_stat_boxes' => '',
            'hide_invoices' => '',
        );

        $wpjobportal_sanitized_args = shortcode_atts($wpjobportal_defaults, $raw_args);
        $shortcode_options = shortcode_atts($wpjobportal_default_short_code_options, $raw_args);
        if(isset(wpjobportal::$_data['sanitized_args']) && !empty(wpjobportal::$_data['sanitized_args'])){
            wpjobportal::$_data['sanitized_args'] += $wpjobportal_sanitized_args;
        }else{
            wpjobportal::$_data['sanitized_args'] = $wpjobportal_sanitized_args;
        }

        if(isset(wpjobportal::$_data['shortcode_options']) && !empty(wpjobportal::$_data['shortcode_options'])){
            wpjobportal::$_data['shortcode_options'] += $shortcode_options;
        }else{
            wpjobportal::$_data['shortcode_options'] = $shortcode_options;
        }

        $wpjobportal_pageid = get_the_ID();
        wpjobportal::wpjobportal_setPageID($wpjobportal_pageid);
        wpjobportal::wpjobportal_addStyleSheets();
        $offline = wpjobportal::$_config->getConfigurationByConfigName('offline');
        if ($offline == 1) {
            WPJOBPORTALlayout::getSystemOffline();
        } elseif (WPJOBPORTALincluder::getObjectClass('user')->isdisabled()) { // handling for the user disabled
            WPJOBPORTALlayout::getUserDisabledMsg();
        } else {
            $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme', null, 'employer');
            $wpjobportal_layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'controlpanel');
            $wpjobportal_employerarray = array('addcompany', 'mycompanies', 'adddepartment', 'mydepartments', 'addfolder', 'myfolders', 'addjob', 'myjobs');
            $wpjobportal_isouruser = WPJOBPORTALincluder::getObjectClass('user')->isWPJOBPortalUser();
            $wpjobportal_isguest = WPJOBPORTALincluder::getObjectClass('user')->isguest();
            if (in_array($wpjobportal_layout, $wpjobportal_employerarray) && $wpjobportal_isouruser == false && $wpjobportal_isguest == false) {
                WPJOBPORTALincluder::include_file('newinwpjobportal', 'common');
            } else {
                wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
                WPJOBPORTALincluder::include_file($wpjobportal_module);
            }
        }
                unset(wpjobportal::$_data['sanitized_args']);
        $wpjobportal_content .= ob_get_clean();
        return $wpjobportal_content;
    }

    function show_jobseeker_controlpanel($raw_args, $wpjobportal_content = null) {
        //default set of parameters for the front end shortcodes
        ob_start();
        $wpjobportal_defaults = array(
            'wpjobportalme' => 'jobseeker',
            'wpjobportallt' => 'controlpanel',
        );

        $wpjobportal_default_short_code_options = array(
            'hide_profile_section' => '',
            'hide_graph' => '',
            'hide_job_applies' => '',
            'hide_newest_jobs' => '',
            'hide_stat_boxes' => '',
            'hide_invoices' => '',
        );

        $wpjobportal_sanitized_args = shortcode_atts($wpjobportal_defaults, $raw_args);
        $shortcode_options = shortcode_atts($wpjobportal_default_short_code_options, $raw_args);
        if(isset(wpjobportal::$_data['sanitized_args']) && !empty(wpjobportal::$_data['sanitized_args'])){
            wpjobportal::$_data['sanitized_args'] += $wpjobportal_sanitized_args;
        }else{
            wpjobportal::$_data['sanitized_args'] = $wpjobportal_sanitized_args;
        }

        if(isset(wpjobportal::$_data['shortcode_options']) && !empty(wpjobportal::$_data['shortcode_options'])){
            wpjobportal::$_data['shortcode_options'] += $shortcode_options;
        }else{
            wpjobportal::$_data['shortcode_options'] = $shortcode_options;
        }

        $wpjobportal_pageid = get_the_ID();
        wpjobportal::wpjobportal_setPageID($wpjobportal_pageid);
        wpjobportal::wpjobportal_addStyleSheets();
        $offline = wpjobportal::$_config->getConfigurationByConfigName('offline');
        if ($offline == 1) {
            WPJOBPORTALlayout::getSystemOffline();
        } elseif (WPJOBPORTALincluder::getObjectClass('user')->isdisabled()) { // handling for the user disabled
            WPJOBPORTALlayout::getUserDisabledMsg();
        } else {
            $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme', null, 'jobseeker');
            $wpjobportal_layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'controlpanel');
            $wpjobportal_jobseekerarray = array('myresumes','myappliedjobs');
            $wpjobportal_isouruser = WPJOBPORTALincluder::getObjectClass('user')->isWPJOBPortalUser();
            $wpjobportal_isguest = WPJOBPORTALincluder::getObjectClass('user')->isguest();
            if (in_array($wpjobportal_layout, $wpjobportal_jobseekerarray) && $wpjobportal_isouruser == false && $wpjobportal_isguest == false) {
                WPJOBPORTALincluder::include_file('newinwpjobportal', 'common');
            } else {
                wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
                WPJOBPORTALincluder::include_file($wpjobportal_module);
            }
        }
        unset(wpjobportal::$_data['sanitized_args']);
        $wpjobportal_content .= ob_get_clean();
        return $wpjobportal_content;
    }

    function show_job_search($raw_args, $wpjobportal_content = null) {
        //default set of parameters for the front end shortcodes
        ob_start();
        $wpjobportal_defaults = array(
            'wpjobportalme' => 'jobsearch',
            'wpjobportallt' => 'jobsearch',
        );
        $wpjobportal_sanitized_args = shortcode_atts($wpjobportal_defaults, $raw_args);
        if(isset(wpjobportal::$_data['sanitized_args']) && !empty(wpjobportal::$_data['sanitized_args'])){
            wpjobportal::$_data['sanitized_args'] += $wpjobportal_sanitized_args;
        }else{
            wpjobportal::$_data['sanitized_args'] = $wpjobportal_sanitized_args;
        }
        $wpjobportal_pageid = get_the_ID();
        wpjobportal::wpjobportal_setPageID($wpjobportal_pageid);
        wpjobportal::wpjobportal_addStyleSheets();
        $offline = wpjobportal::$_config->getConfigurationByConfigName('offline');
        if ($offline == 1) {
            WPJOBPORTALlayout::getSystemOffline();
        } elseif (WPJOBPORTALincluder::getObjectClass('user')->isdisabled()) { // handling for the user disabled
            WPJOBPORTALlayout::getUserDisabledMsg();
        } else {
            $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme', null, 'jobsearch');
            wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
            WPJOBPORTALincluder::include_file($wpjobportal_module);
        }
        unset(wpjobportal::$_data['sanitized_args']);
        $wpjobportal_content .= ob_get_clean();
        return $wpjobportal_content;
    }

    function show_job($raw_args, $wpjobportal_content = null) {
        //default set of parameters for the front end shortcodes
        ob_start();
        $wpjobportal_defaults = array(
            'wpjobportalme' => 'job',
            'wpjobportallt' => 'jobs',
            'show_only_featured_jobs' => '0',
        );

        $wpjobportal_default_short_code_options = array(
            'no_of_jobs' => '',
            'hide_filter' => '',
            'hide_filter_job_title' => '',
            'hide_filter_job_location' => '',
            'hide_company_logo' => '',
            'hide_company_name' => '',

            'companies' => '',
            'categories' => '',
            'types' => '',
            'locations' => '',
            'ids' => '',
            'careerlevels' => '',
            'jobstatuses' => '',
            'tags' => '',
            'sorting' => '',
        );

        $wpjobportal_sanitized_args = shortcode_atts($wpjobportal_defaults, $raw_args);
        $shortcode_options = shortcode_atts($wpjobportal_default_short_code_options, $raw_args);
        if(isset(wpjobportal::$_data['sanitized_args']) && !empty(wpjobportal::$_data['sanitized_args'])){
            wpjobportal::$_data['sanitized_args'] += $wpjobportal_sanitized_args;
        }else{
            wpjobportal::$_data['sanitized_args'] = $wpjobportal_sanitized_args;
        }

        if(isset(wpjobportal::$_data['shortcode_options']) && !empty(wpjobportal::$_data['shortcode_options'])){
            wpjobportal::$_data['shortcode_options'] += $shortcode_options;
        }else{
            wpjobportal::$_data['shortcode_options'] = $shortcode_options;
        }

        $wpjobportal_pageid = get_the_ID();
        wpjobportal::wpjobportal_setPageID($wpjobportal_pageid);
        wpjobportal::wpjobportal_addStyleSheets();
        $offline = wpjobportal::$_config->getConfigurationByConfigName('offline');
        if ($offline == 1) {
            WPJOBPORTALlayout::getSystemOffline();
        } elseif (WPJOBPORTALincluder::getObjectClass('user')->isdisabled()) { // handling for the user disabled
            WPJOBPORTALlayout::getUserDisabledMsg();
        } else {
            $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme', null, 'job');
            wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
            WPJOBPORTALincluder::include_file($wpjobportal_module);
        }
        unset(wpjobportal::$_data['sanitized_args']);
        $wpjobportal_content .= ob_get_clean();
        return $wpjobportal_content;
    }

    function show_job_categories($raw_args, $wpjobportal_content = null) {
        //default set of parameters for the front end shortcodes
        ob_start();
        $wpjobportal_defaults = array(
            'wpjobportalme' => 'job',
            'wpjobportallt' => 'jobsbycategories',
        );
        $wpjobportal_sanitized_args = shortcode_atts($wpjobportal_defaults, $raw_args);
        if(isset(wpjobportal::$_data['sanitized_args']) && !empty(wpjobportal::$_data['sanitized_args'])){
            wpjobportal::$_data['sanitized_args'] += $wpjobportal_sanitized_args;
        }else{
            wpjobportal::$_data['sanitized_args'] = $wpjobportal_sanitized_args;
        }
        $wpjobportal_pageid = get_the_ID();
        wpjobportal::wpjobportal_setPageID($wpjobportal_pageid);
        wpjobportal::wpjobportal_addStyleSheets();
        $offline = wpjobportal::$_config->getConfigurationByConfigName('offline');
        if ($offline == 1) {
            WPJOBPORTALlayout::getSystemOffline();
        } elseif (WPJOBPORTALincluder::getObjectClass('user')->isdisabled()) { // handling for the user disabled
            WPJOBPORTALlayout::getUserDisabledMsg();
        } else {
            $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme', null, 'job');
            wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
            WPJOBPORTALincluder::include_file($wpjobportal_module);
        }
                unset(wpjobportal::$_data['sanitized_args']);
        $wpjobportal_content .= ob_get_clean();
        return $wpjobportal_content;
    }

    function show_job_types($raw_args, $wpjobportal_content = null) {
        //default set of parameters for the front end shortcodes
        ob_start();
        $wpjobportal_defaults = array(
            'wpjobportalme' => 'job',
            'wpjobportallt' => 'jobsbytypes',
        );
        $wpjobportal_sanitized_args = shortcode_atts($wpjobportal_defaults, $raw_args);
        if(isset(wpjobportal::$_data['sanitized_args']) && !empty(wpjobportal::$_data['sanitized_args'])){
            wpjobportal::$_data['sanitized_args'] += $wpjobportal_sanitized_args;
        }else{
            wpjobportal::$_data['sanitized_args'] = $wpjobportal_sanitized_args;
        }
        $wpjobportal_pageid = get_the_ID();
        wpjobportal::wpjobportal_setPageID($wpjobportal_pageid);
        wpjobportal::wpjobportal_addStyleSheets();
        $offline = wpjobportal::$_config->getConfigurationByConfigName('offline');
        if ($offline == 1) {
            WPJOBPORTALlayout::getSystemOffline();
        } elseif (WPJOBPORTALincluder::getObjectClass('user')->isdisabled()) { // handling for the user disabled
            WPJOBPORTALlayout::getUserDisabledMsg();
        } else {
            $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme', null, 'job');
            wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
            WPJOBPORTALincluder::include_file($wpjobportal_module);
        }
                unset(wpjobportal::$_data['sanitized_args']);
        $wpjobportal_content .= ob_get_clean();
        return $wpjobportal_content;
    }

    function show_my_appliedjobs($raw_args, $wpjobportal_content = null) {
        //default set of parameters for the front end shortcodes
        ob_start();
        $wpjobportal_defaults = array(
            'wpjobportalme' => 'jobapply',
            'wpjobportallt' => 'myappliedjobs',
        );
        $wpjobportal_sanitized_args = shortcode_atts($wpjobportal_defaults, $raw_args);
        if(isset(wpjobportal::$_data['sanitized_args']) && !empty(wpjobportal::$_data['sanitized_args'])){
            wpjobportal::$_data['sanitized_args'] += $wpjobportal_sanitized_args;
        }else{
            wpjobportal::$_data['sanitized_args'] = $wpjobportal_sanitized_args;
        }
        $wpjobportal_pageid = get_the_ID();
        wpjobportal::wpjobportal_setPageID($wpjobportal_pageid);
        wpjobportal::wpjobportal_addStyleSheets();
        $offline = wpjobportal::$_config->getConfigurationByConfigName('offline');
        if ($offline == 1) {
            WPJOBPORTALlayout::getSystemOffline();
        } elseif (WPJOBPORTALincluder::getObjectClass('user')->isdisabled()) { // handling for the user disabled
            WPJOBPORTALlayout::getUserDisabledMsg();
        } else {
            $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme', null, 'jobapply');
            wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
            WPJOBPORTALincluder::include_file($wpjobportal_module);
        }
                unset(wpjobportal::$_data['sanitized_args']);
        $wpjobportal_content .= ob_get_clean();
        return $wpjobportal_content;
    }

    function show_my_companies($raw_args, $wpjobportal_content = null) {
        //default set of parameters for the front end shortcodes
        ob_start();
        if(in_array('multicompany', wpjobportal::$_active_addons)){
            $wpjobportal_mod = "multicompany";
        }else{
            $wpjobportal_mod = "company";
        }
        $wpjobportal_defaults = array(
            'wpjobportalme' => $wpjobportal_mod,
            'wpjobportallt' => 'mycompanies',
        );
        $wpjobportal_sanitized_args = shortcode_atts($wpjobportal_defaults, $raw_args);
        if(isset(wpjobportal::$_data['sanitized_args']) && !empty(wpjobportal::$_data['sanitized_args'])){
            wpjobportal::$_data['sanitized_args'] += $wpjobportal_sanitized_args;
        }else{
            wpjobportal::$_data['sanitized_args'] = $wpjobportal_sanitized_args;
        }
        $wpjobportal_pageid = get_the_ID();
        wpjobportal::wpjobportal_setPageID($wpjobportal_pageid);
        wpjobportal::wpjobportal_addStyleSheets();
        $offline = wpjobportal::$_config->getConfigurationByConfigName('offline');
        if ($offline == 1) {
            WPJOBPORTALlayout::getSystemOffline();
        } elseif (WPJOBPORTALincluder::getObjectClass('user')->isdisabled()) { // handling for the user disabled
            WPJOBPORTALlayout::getUserDisabledMsg();
        } else {
            $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme', null, 'company');
            wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
            WPJOBPORTALincluder::include_file($wpjobportal_module);
        }
                unset(wpjobportal::$_data['sanitized_args']);
        $wpjobportal_content .= ob_get_clean();
        return $wpjobportal_content;
    }


    function show_my_departments($raw_args, $wpjobportal_content = null) {
        //default set of parameters for the front end shortcodes
        ob_start();
        $wpjobportal_defaults = array(
            'wpjobportalme' => 'departments',
            'wpjobportallt' => 'mydepartments',
        );
        $wpjobportal_sanitized_args = shortcode_atts($wpjobportal_defaults, $raw_args);
        if(isset(wpjobportal::$_data['sanitized_args']) && !empty(wpjobportal::$_data['sanitized_args'])){
            wpjobportal::$_data['sanitized_args'] += $wpjobportal_sanitized_args;
        }else{
            wpjobportal::$_data['sanitized_args'] = $wpjobportal_sanitized_args;
        }
        $wpjobportal_pageid = get_the_ID();
        wpjobportal::wpjobportal_setPageID($wpjobportal_pageid);
        wpjobportal::wpjobportal_addStyleSheets();
        $offline = wpjobportal::$_config->getConfigurationByConfigName('offline');
        if ($offline == 1) {
            WPJOBPORTALlayout::getSystemOffline();
        } elseif (WPJOBPORTALincluder::getObjectClass('user')->isdisabled()) { // handling for the user disabled
            WPJOBPORTALlayout::getUserDisabledMsg();
        } else {
            $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme', null, 'departments');
            wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
            WPJOBPORTALincluder::include_file($wpjobportal_module);
        }
                unset(wpjobportal::$_data['sanitized_args']);
        $wpjobportal_content .= ob_get_clean();
        return $wpjobportal_content;
    }

    function show_my_jobs($raw_args, $wpjobportal_content = null) {
        //default set of parameters for the front end shortcodes
        ob_start();
        $wpjobportal_defaults = array(
            'wpjobportalme' => 'job',
            'wpjobportallt' => 'myjobs',
        );
        $wpjobportal_sanitized_args = shortcode_atts($wpjobportal_defaults, $raw_args);
        if(isset(wpjobportal::$_data['sanitized_args']) && !empty(wpjobportal::$_data['sanitized_args'])){
            wpjobportal::$_data['sanitized_args'] += $wpjobportal_sanitized_args;
        }else{
            wpjobportal::$_data['sanitized_args'] = $wpjobportal_sanitized_args;
        }
        $wpjobportal_pageid = get_the_ID();
        wpjobportal::wpjobportal_setPageID($wpjobportal_pageid);
        wpjobportal::wpjobportal_addStyleSheets();
        $offline = wpjobportal::$_config->getConfigurationByConfigName('offline');
        if ($offline == 1) {
            WPJOBPORTALlayout::getSystemOffline();
        } elseif (WPJOBPORTALincluder::getObjectClass('user')->isdisabled()) { // handling for the user disabled
            WPJOBPORTALlayout::getUserDisabledMsg();
        } else {
            $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme', null, 'job');
            wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
            WPJOBPORTALincluder::include_file($wpjobportal_module);
        }
                unset(wpjobportal::$_data['sanitized_args']);
        $wpjobportal_content .= ob_get_clean();
        return $wpjobportal_content;
    }

    function show_my_resumes($raw_args, $wpjobportal_content = null) {
        //default set of parameters for the front end shortcodes
        ob_start();
        $wpjobportal_defaults = array(
            'wpjobportalme' => 'resume',
            'wpjobportallt' => 'myresumes',
        );
        $wpjobportal_sanitized_args = shortcode_atts($wpjobportal_defaults, $raw_args);
        if(isset(wpjobportal::$_data['sanitized_args']) && !empty(wpjobportal::$_data['sanitized_args'])){
            wpjobportal::$_data['sanitized_args'] += $wpjobportal_sanitized_args;
        }else{
            wpjobportal::$_data['sanitized_args'] = $wpjobportal_sanitized_args;
        }
        $wpjobportal_pageid = get_the_ID();
        wpjobportal::wpjobportal_setPageID($wpjobportal_pageid);
        wpjobportal::wpjobportal_addStyleSheets();
        $offline = wpjobportal::$_config->getConfigurationByConfigName('offline');
        if ($offline == 1) {
            WPJOBPORTALlayout::getSystemOffline();
        } elseif (WPJOBPORTALincluder::getObjectClass('user')->isdisabled()) { // handling for the user disabled
            WPJOBPORTALlayout::getUserDisabledMsg();
        } else {
            $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme', null, 'resume');
            wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
            WPJOBPORTALincluder::include_file($wpjobportal_module);
        }
                unset(wpjobportal::$_data['sanitized_args']);
        $wpjobportal_content .= ob_get_clean();
        return $wpjobportal_content;
    }

    function show_add_company($raw_args, $wpjobportal_content = null) {
        //default set of parameters for the front end shortcodes
        ob_start();
        if(in_array('multicompany', wpjobportal::$_active_addons)){
            $wpjobportal_mod = "multicompany";
        }else{
            $wpjobportal_mod = "company";
        }
        $wpjobportal_defaults = array(
            'wpjobportalme' => $wpjobportal_mod,
            'wpjobportallt' => 'addcompany',
        );
        $wpjobportal_sanitized_args = shortcode_atts($wpjobportal_defaults, $raw_args);
        if(isset(wpjobportal::$_data['sanitized_args']) && !empty(wpjobportal::$_data['sanitized_args'])){
            wpjobportal::$_data['sanitized_args'] += $wpjobportal_sanitized_args;
        }else{
            wpjobportal::$_data['sanitized_args'] = $wpjobportal_sanitized_args;
        }
        $wpjobportal_pageid = get_the_ID();
        wpjobportal::wpjobportal_setPageID($wpjobportal_pageid);
        wpjobportal::wpjobportal_addStyleSheets();
        $offline = wpjobportal::$_config->getConfigurationByConfigName('offline');
        if ($offline == 1) {
            WPJOBPORTALlayout::getSystemOffline();
        } elseif (WPJOBPORTALincluder::getObjectClass('user')->isdisabled()) { // handling for the user disabled
            WPJOBPORTALlayout::getUserDisabledMsg();
        } else {
            $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme', null, 'company');
            wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
            WPJOBPORTALincluder::include_file($wpjobportal_module);
        }
                unset(wpjobportal::$_data['sanitized_args']);
        $wpjobportal_content .= ob_get_clean();
        return $wpjobportal_content;
    }



    function show_add_department($raw_args, $wpjobportal_content = null) {
        //default set of parameters for the front end shortcodes
        ob_start();
        $wpjobportal_defaults = array(
            'wpjobportalme' => 'departments',
            'wpjobportallt' => 'adddepartment',
        );
        $wpjobportal_sanitized_args = shortcode_atts($wpjobportal_defaults, $raw_args);
        if(isset(wpjobportal::$_data['sanitized_args']) && !empty(wpjobportal::$_data['sanitized_args'])){
            wpjobportal::$_data['sanitized_args'] += $wpjobportal_sanitized_args;
        }else{
            wpjobportal::$_data['sanitized_args'] = $wpjobportal_sanitized_args;
        }
        $wpjobportal_pageid = get_the_ID();
        wpjobportal::wpjobportal_setPageID($wpjobportal_pageid);
        wpjobportal::wpjobportal_addStyleSheets();
        $offline = wpjobportal::$_config->getConfigurationByConfigName('offline');
        if ($offline == 1) {
            WPJOBPORTALlayout::getSystemOffline();
        } elseif (WPJOBPORTALincluder::getObjectClass('user')->isdisabled()) { // handling for the user disabled
            WPJOBPORTALlayout::getUserDisabledMsg();
        } else {
            $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme', null, 'departments');
            wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
            WPJOBPORTALincluder::include_file($wpjobportal_module);
        }
                unset(wpjobportal::$_data['sanitized_args']);
        $wpjobportal_content .= ob_get_clean();
        return $wpjobportal_content;
    }

    function show_add_job($raw_args, $wpjobportal_content = null) {
        //default set of parameters for the front end shortcodes
        ob_start();
        $wpjobportal_defaults = array(
            'wpjobportalme' => 'job',
            'wpjobportallt' => 'addjob',
        );
        $wpjobportal_sanitized_args = shortcode_atts($wpjobportal_defaults, $raw_args);
        if(isset(wpjobportal::$_data['sanitized_args']) && !empty(wpjobportal::$_data['sanitized_args'])){
            wpjobportal::$_data['sanitized_args'] += $wpjobportal_sanitized_args;
        }else{
            wpjobportal::$_data['sanitized_args'] = $wpjobportal_sanitized_args;
        }
        $wpjobportal_pageid = get_the_ID();
        wpjobportal::wpjobportal_setPageID($wpjobportal_pageid);
        wpjobportal::wpjobportal_addStyleSheets();
        $offline = wpjobportal::$_config->getConfigurationByConfigName('offline');
        if ($offline == 1) {
            WPJOBPORTALlayout::getSystemOffline();
        } elseif (WPJOBPORTALincluder::getObjectClass('user')->isdisabled()) { // handling for the user disabled
            WPJOBPORTALlayout::getUserDisabledMsg();
        } else {
            $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme', null, 'job');
            wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
            WPJOBPORTALincluder::include_file($wpjobportal_module);
        }
                unset(wpjobportal::$_data['sanitized_args']);
        $wpjobportal_content .= ob_get_clean();
        return $wpjobportal_content;
    }

    function show_add_resume($raw_args, $wpjobportal_content = null) {
        //default set of parameters for the front end shortcodes
        ob_start();
        if(in_array('multiresume', wpjobportal::$_active_addons)){
            $wpjobportal_mod = "multiresume";
        }else{
            $wpjobportal_mod = "resume";
        }
        $wpjobportal_defaults = array(
            'wpjobportalme' => $wpjobportal_mod,
            'wpjobportallt' => 'addresume',
        );
        $wpjobportal_sanitized_args = shortcode_atts($wpjobportal_defaults, $raw_args);
        if(isset(wpjobportal::$_data['sanitized_args']) && !empty(wpjobportal::$_data['sanitized_args'])){
            wpjobportal::$_data['sanitized_args'] += $wpjobportal_sanitized_args;
        }else{
            wpjobportal::$_data['sanitized_args'] = $wpjobportal_sanitized_args;
        }
        $wpjobportal_pageid = get_the_ID();
        wpjobportal::wpjobportal_setPageID($wpjobportal_pageid);
        wpjobportal::wpjobportal_addStyleSheets();
        $offline = wpjobportal::$_config->getConfigurationByConfigName('offline');
        if ($offline == 1) {
            WPJOBPORTALlayout::getSystemOffline();
        } elseif (WPJOBPORTALincluder::getObjectClass('user')->isdisabled()) { // handling for the user disabled
            WPJOBPORTALlayout::getUserDisabledMsg();
        } else {
            $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme', null, 'resume');
            wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
            WPJOBPORTALincluder::include_file($wpjobportal_module);
        }
                unset(wpjobportal::$_data['sanitized_args']);
        $wpjobportal_content .= ob_get_clean();
        return $wpjobportal_content;
    }



    function show_employer_registration($raw_args, $wpjobportal_content = null) {
        //default set of parameters for the front end shortcodes
        ob_start();
        $wpjobportal_defaults = array(
            'wpjobportalme' => 'user',
            'wpjobportallt' => 'regemployer',
        );
        $wpjobportal_sanitized_args = shortcode_atts($wpjobportal_defaults, $raw_args);
        if(isset(wpjobportal::$_data['sanitized_args']) && !empty(wpjobportal::$_data['sanitized_args'])){
            wpjobportal::$_data['sanitized_args'] += $wpjobportal_sanitized_args;
        }else{
            wpjobportal::$_data['sanitized_args'] = $wpjobportal_sanitized_args;
        }
        $wpjobportal_pageid = get_the_ID();
        wpjobportal::wpjobportal_setPageID($wpjobportal_pageid);
        wpjobportal::wpjobportal_addStyleSheets();
        $offline = wpjobportal::$_config->getConfigurationByConfigName('offline');
        if ($offline == 1) {
            WPJOBPORTALlayout::getSystemOffline();
        } elseif (WPJOBPORTALincluder::getObjectClass('user')->isdisabled()) { // handling for the user disabled
            WPJOBPORTALlayout::getUserDisabledMsg();
        } else {
            $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme', null, 'user');
            wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
            WPJOBPORTALincluder::include_file($wpjobportal_module);
        }
                unset(wpjobportal::$_data['sanitized_args']);
        $wpjobportal_content .= ob_get_clean();
        return $wpjobportal_content;
    }

    function show_registration($raw_args, $wpjobportal_content = null) {
        //default set of parameters for the front end shortcodes
        ob_start();
        $wpjobportal_defaults = array(
            'wpjobportalme' => 'user',
            'wpjobportallt' => 'userregister',
        );
        $wpjobportal_sanitized_args = shortcode_atts($wpjobportal_defaults, $raw_args);
        if(isset(wpjobportal::$_data['sanitized_args']) && !empty(wpjobportal::$_data['sanitized_args'])){
            wpjobportal::$_data['sanitized_args'] += $wpjobportal_sanitized_args;
        }else{
            wpjobportal::$_data['sanitized_args'] = $wpjobportal_sanitized_args;
        }
        $wpjobportal_pageid = get_the_ID();
        wpjobportal::wpjobportal_setPageID($wpjobportal_pageid);
        wpjobportal::wpjobportal_addStyleSheets();
        $offline = wpjobportal::$_config->getConfigurationByConfigName('offline');
        if ($offline == 1) {
            WPJOBPORTALlayout::getSystemOffline();
        } elseif (WPJOBPORTALincluder::getObjectClass('user')->isdisabled()) { // handling for the user disabled
            WPJOBPORTALlayout::getUserDisabledMsg();
        } else {
            $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme', null, 'user');
            wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
            WPJOBPORTALincluder::include_file($wpjobportal_module);
        }
        unset(wpjobportal::$_data['sanitized_args']);
        $wpjobportal_content .= ob_get_clean();
        return $wpjobportal_content;
    }

    function show_jobseeker_registration($raw_args, $wpjobportal_content = null) {
        //default set of parameters for the front end shortcodes
        ob_start();
        $wpjobportal_defaults = array(
            'wpjobportalme' => 'user',
            'wpjobportallt' => 'regjobseeker',
        );
        $wpjobportal_sanitized_args = shortcode_atts($wpjobportal_defaults, $raw_args);
        if(isset(wpjobportal::$_data['sanitized_args']) && !empty(wpjobportal::$_data['sanitized_args'])){
            wpjobportal::$_data['sanitized_args'] += $wpjobportal_sanitized_args;
        }else{
            wpjobportal::$_data['sanitized_args'] = $wpjobportal_sanitized_args;
        }
        $wpjobportal_pageid = get_the_ID();
        wpjobportal::wpjobportal_setPageID($wpjobportal_pageid);
        wpjobportal::wpjobportal_addStyleSheets();
        $offline = wpjobportal::$_config->getConfigurationByConfigName('offline');
        if ($offline == 1) {
            WPJOBPORTALlayout::getSystemOffline();
        } elseif (WPJOBPORTALincluder::getObjectClass('user')->isdisabled()) { // handling for the user disabled
            WPJOBPORTALlayout::getUserDisabledMsg();
        } else {
            $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme', null, 'user');
            wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
            WPJOBPORTALincluder::include_file($wpjobportal_module);
        }
                unset(wpjobportal::$_data['sanitized_args']);
        $wpjobportal_content .= ob_get_clean();
        return $wpjobportal_content;
    }

    function show_jobseeker_my_stats($raw_args, $wpjobportal_content = null){
        //default set of parameters for the front end shortcodes
        ob_start();
        $wpjobportal_defaults = array(
            'wpjobportalme' => 'jobseeker',
            'wpjobportallt' => 'mystats',
        );
        $wpjobportal_sanitized_args = shortcode_atts($wpjobportal_defaults, $raw_args);
        if(isset(wpjobportal::$_data['sanitized_args']) && !empty(wpjobportal::$_data['sanitized_args'])){
            wpjobportal::$_data['sanitized_args'] += $wpjobportal_sanitized_args;
        }else{
            wpjobportal::$_data['sanitized_args'] = $wpjobportal_sanitized_args;
        }
        $wpjobportal_pageid = get_the_ID();
        wpjobportal::wpjobportal_setPageID($wpjobportal_pageid);
        wpjobportal::wpjobportal_addStyleSheets();
        $offline = wpjobportal::$_config->getConfigurationByConfigName('offline');
        if ($offline == 1) {
            WPJOBPORTALlayout::getSystemOffline();
        } elseif (WPJOBPORTALincluder::getObjectClass('user')->isdisabled()) { // handling for the user disabled
            WPJOBPORTALlayout::getUserDisabledMsg();
        } else {
            $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme', null, 'jobseeker');
            wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
            WPJOBPORTALincluder::include_file($wpjobportal_module);
        }
                unset(wpjobportal::$_data['sanitized_args']);
        $wpjobportal_content .= ob_get_clean();
        return $wpjobportal_content;
    }

    function show_employer_my_stats($raw_args, $wpjobportal_content = null){
        //default set of parameters for the front end shortcodes
        ob_start();
        $wpjobportal_defaults = array(
            'wpjobportalme' => 'employer',
            'wpjobportallt' => 'mystats',
        );
        $wpjobportal_sanitized_args = shortcode_atts($wpjobportal_defaults, $raw_args);
        wpjobportal::$_data['sanitized_args'] = $wpjobportal_sanitized_args;
        $wpjobportal_pageid = get_the_ID();
        wpjobportal::wpjobportal_setPageID($wpjobportal_pageid);
        wpjobportal::wpjobportal_addStyleSheets();
        $offline = wpjobportal::$_config->getConfigurationByConfigName('offline');
        if ($offline == 1) {
            WPJOBPORTALlayout::getSystemOffline();
        } elseif (WPJOBPORTALincluder::getObjectClass('user')->isdisabled()) { // handling for the user disabled
            WPJOBPORTALlayout::getUserDisabledMsg();
        } else {
            $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme', null, 'employer');
            wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
            WPJOBPORTALincluder::include_file($wpjobportal_module);
        }
                unset(wpjobportal::$_data['sanitized_args']);
        $wpjobportal_content .= ob_get_clean();
        return $wpjobportal_content;
    }

    function show_login_page($raw_args, $wpjobportal_content = null){
        //default set of parameters for the front end shortcodes
        ob_start();
        $wpjobportal_defaults = array(
            'wpjobportalme' => 'wpjobportal',
            'wpjobportallt' => 'login',
        );
        $wpjobportal_sanitized_args = shortcode_atts($wpjobportal_defaults, $raw_args);
        if(isset(wpjobportal::$_data['sanitized_args']) && !empty(wpjobportal::$_data['sanitized_args'])){
            wpjobportal::$_data['sanitized_args'] += $wpjobportal_sanitized_args;
        }else{
            wpjobportal::$_data['sanitized_args'] = $wpjobportal_sanitized_args;
        }
        $wpjobportal_pageid = get_the_ID();
        wpjobportal::wpjobportal_setPageID($wpjobportal_pageid);
        wpjobportal::wpjobportal_addStyleSheets();
        $offline = wpjobportal::$_config->getConfigurationByConfigName('offline');
        if ($offline == 1) {
            WPJOBPORTALlayout::getSystemOffline();
        } elseif (WPJOBPORTALincluder::getObjectClass('user')->isdisabled()) { // handling for the user disabled
            WPJOBPORTALlayout::getUserDisabledMsg();
        } else {
            $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme', null, 'wpjobportal');
            wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
            WPJOBPORTALincluder::include_file($wpjobportal_module);
        }
                unset(wpjobportal::$_data['sanitized_args']);
        $wpjobportal_content .= ob_get_clean();
        return $wpjobportal_content;
    }

    function show_searchjob($raw_args, $wpjobportal_content = null) {

        ob_start();

        $wpjobportal_defaults = array(
            'title' => esc_html(__('Search job', 'wp-job-portal')),
            'showtitle' => '1',
            'jobtitle' => '1',
            'category' => '1',
            'jobtype' => '1',
            'jobstatus' => '1',
            'salaryrange' => '1',
            'shift' => '1',
            'duration' => '1',
            'startpublishing' => '1',
            'stoppublishing' => '1',
            'company' => '1',
            'address' => '1',
            'columnperrow' => '1',
        );

        $wpjobportal_arr = (object) shortcode_atts($wpjobportal_defaults, $raw_args);
        wpjobportal::wpjobportal_addStyleSheets();
        $offline = wpjobportal::$_config->getConfigurationByConfigName('offline');
        if ($offline == 1) {
            WPJOBPORTALlayout::getSystemOffline();
        } else {
            $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme');
            if($wpjobportal_module != null){
                $wpjobportal_pageid = get_the_ID();
                wpjobportal::wpjobportal_setPageID($wpjobportal_pageid);
                wpjobportal::wpjobportal_addStyleSheets();
                wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
                WPJOBPORTALincluder::include_file($wpjobportal_module);
                $wpjobportal_content .= ob_get_clean();
                return $wpjobportal_content;
            }
            $wpjobportal_modules_html = WPJOBPORTALincluder::getJSModel('jobsearch')->getSearchJobs_Widget($wpjobportal_arr->title, $wpjobportal_arr->showtitle, $wpjobportal_arr->jobtitle, $wpjobportal_arr->category, $wpjobportal_arr->jobtype, $wpjobportal_arr->jobstatus, $wpjobportal_arr->salaryrange, $wpjobportal_arr->shift, $wpjobportal_arr->duration, $wpjobportal_arr->startpublishing, $wpjobportal_arr->stoppublishing, $wpjobportal_arr->company, $wpjobportal_arr->address, $wpjobportal_arr->columnperrow);
            echo wp_kses($wpjobportal_modules_html, WPJOBPORTAL_ALLOWED_TAGS);
        }
        unset(wpjobportal::$_data['sanitized_args']);
        $wpjobportal_content .= ob_get_clean();
        return $wpjobportal_content;
    }

    function show_resumes($raw_args, $wpjobportal_content = null) {

        ob_start();

        $wpjobportal_defaults = array(
            'title' => esc_html(__('Resumes', 'wp-job-portal')),
            'typeofresume' => '1',
            'showtitle' => '1',
            'applicationtitle' => '1',
            'name' => '1',
            'category' => '1',
            'jobtype' => '1',
            'experience' => '1',
            'available' => '1',
            'gender' => '1',
            'nationality' => '1',
            'location' => '1',
            'posted' => '1',
            'noofresume' => '5',
            'listingstyle' => '1',
            'boxstyle' => '1',
            'fieldcolumn' => '1',
            'moduleheight' => '400',
            'resumeheight' => '250',
            'logowidth' => '150',
            'logoheight' => '90',
            'resumephoto' => '1',
            'nofresumedesktop' => '1',
            'nofresumetablet' => '1',
            'topmargin' => '10',
            'leftmargin' => '10',
            'titlecolor' => '',
            'titleborderbottom' => '',
            'backgroundcolor' => '',
            'bordercolor' => '',
            'datalabelcolor' => '',
            'datavaluecolor' => '',
        );

        $wpjobportal_arr = (object) shortcode_atts($wpjobportal_defaults, $raw_args);
        $wpjobportal_arr->subcategory = 0;

        wpjobportal::wpjobportal_addStyleSheets();
        $offline = wpjobportal::$_config->getConfigurationByConfigName('offline');
        if ($offline == 1) {
            WPJOBPORTALlayout::getSystemOffline();
        } else {
            $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme');
            if($wpjobportal_module != null){
                $wpjobportal_pageid = get_the_ID();
                wpjobportal::wpjobportal_setPageID($wpjobportal_pageid);
                wpjobportal::wpjobportal_addStyleSheets();
                wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
                WPJOBPORTALincluder::include_file($wpjobportal_module);
                $wpjobportal_content .= ob_get_clean();
                return $wpjobportal_content;
            }
            //Frontend HTML starts -----------
            $wpjobportal_mod = 'fd';
            if ($wpjobportal_arr->typeofresume == 1) {
                $wpjobportal_mod = 'newestresume';
            } elseif ($wpjobportal_arr->typeofresume == 2) {
                $wpjobportal_mod = 'topresume';
            }  elseif ($wpjobportal_arr->typeofresume == 4) {
                $wpjobportal_mod = 'featuredresume';
            }

            $wpjobportal_layoutName = $wpjobportal_mod . uniqid();

            if ($wpjobportal_arr->typeofresume != 0) {

                $wpjobportal_resumes = WPJOBPORTALincluder::getJSModel('resume')->getResumes_Widget($wpjobportal_arr->typeofresume, $wpjobportal_arr->noofresume);
                // parameters [for later use]
                $speedTest = '';
                $sliding = '';
                $consecutivesliding = '';
                $slidingdirection = '';
                $wpjobportal_separator = '';

                $wpjobportal_modules_html = WPJOBPORTALincluder::getJSModel('wpjobportalwidgets')->listModuleResumes($wpjobportal_layoutName, $wpjobportal_resumes, $wpjobportal_arr->noofresume, $wpjobportal_arr->applicationtitle, $wpjobportal_arr->name, $wpjobportal_arr->experience, $wpjobportal_arr->available, $wpjobportal_arr->gender, $wpjobportal_arr->nationality, $wpjobportal_arr->location, $wpjobportal_arr->category, $wpjobportal_arr->subcategory, $wpjobportal_arr->jobtype, $wpjobportal_arr->posted, $wpjobportal_separator, $wpjobportal_arr->moduleheight, $wpjobportal_arr->resumeheight, $wpjobportal_arr->topmargin, $wpjobportal_arr->leftmargin, $wpjobportal_arr->logowidth, $wpjobportal_arr->logoheight, $wpjobportal_arr->fieldcolumn, $wpjobportal_arr->listingstyle, $wpjobportal_arr->title, $wpjobportal_arr->showtitle, $speedTest, $sliding, $consecutivesliding, $slidingdirection, $wpjobportal_arr->resumephoto, $wpjobportal_arr->nofresumedesktop, $wpjobportal_arr->nofresumetablet, $wpjobportal_arr->boxstyle);
                echo wp_kses($wpjobportal_modules_html, WPJOBPORTAL_ALLOWED_TAGS);
                $wpjobportal_classname = $wpjobportal_layoutName;

                $wpjobportal_color1 = $wpjobportal_arr->titlecolor;
                $wpjobportal_color2 = $wpjobportal_arr->titleborderbottom;
                $wpjobportal_color3 = $wpjobportal_arr->backgroundcolor;
                $wpjobportal_color4 = $wpjobportal_arr->bordercolor;
                $wpjobportal_color5 = $wpjobportal_arr->datalabelcolor;
                $wpjobportal_color6 = $wpjobportal_arr->datavaluecolor;

                $echo_style = WPJOBPORTALincluder::getJSModel('wpjobportalwidgets')->perpareStyleSheet($wpjobportal_classname , $wpjobportal_color1 , $wpjobportal_color2 , $wpjobportal_color3 , $wpjobportal_color4 , $wpjobportal_color5 , $wpjobportal_color6 );
                echo wp_kses($echo_style, WPJOBPORTAL_ALLOWED_TAGS);
            }
        }

        $wpjobportal_content .= ob_get_clean();
        return $wpjobportal_content;
    }

    function show_companies($raw_args, $wpjobportal_content = null) {

        ob_start();

        $wpjobportal_defaults = array(
            'title' => esc_html(__('Companies', 'wp-job-portal')),
            'companytype' => '1',
            'showtitle' => '1',
            'companylogo' => '1',
            'category' => '1',
            'location' => '1',
            'posted' => '1',
            'noofcompanies' => '5',
            'listingstyle' => '1',
            'boxstyle' => '1',
            'fieldcolumn' => '1',
            'moduleheight' => '400',
            'companyheight' => '250',
            'complogowidth' => '150',
            'complogoheight' => '90',
            'nofcompanies' => '1',
            'nofcompaniesrowtab' => '1',
            'topmargin' => '10',
            'leftmargin' => '10',
            'titlecolor' => '',
            'titleborderbottom' => '',
            'backgroundcolor' => '',
            'bordercolor' => '',
            'datalabelcolor' => '',
            'datavaluecolor' => '',
        );

        $wpjobportal_arr = (object) shortcode_atts($wpjobportal_defaults, $raw_args);

        wpjobportal::wpjobportal_addStyleSheets();
        $offline = wpjobportal::$_config->getConfigurationByConfigName('offline');
        if ($offline == 1) {
            WPJOBPORTALlayout::getSystemOffline();
        } else {
            $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme');
            if($wpjobportal_module != null){
                $wpjobportal_pageid = get_the_ID();
                wpjobportal::wpjobportal_setPageID($wpjobportal_pageid);
                wpjobportal::wpjobportal_addStyleSheets();
                wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
                WPJOBPORTALincluder::include_file($wpjobportal_module);
                $wpjobportal_content .= ob_get_clean();
                return $wpjobportal_content;
            }
            $wpjobportal_mod = 'abc';
            if ($wpjobportal_arr->companytype == 2) {
                $wpjobportal_mod = 'featuredcompany';
            }
            $wpjobportal_layoutName = $wpjobportal_mod . uniqid();

            if ($wpjobportal_arr->companytype != 0) {

                $wpjobportal_companies = WPJOBPORTALincluder::getJSModel('company')->getCompanies_Widget($wpjobportal_arr->companytype, $wpjobportal_arr->noofcompanies);
                //parameters [for later use]
                $wpjobportal_theme = '';
                $wpjobportal_jobwidth = '';
                $wpjobportal_jobfloat = '';
                $speedTest = '';
                $sliding = '';
                $slidingdirection = '';
                $consecutivesliding = '';

                $wpjobportal_modules_html = WPJOBPORTALincluder::getJSModel('wpjobportalwidgets')->listModuleCompanies($wpjobportal_layoutName, $wpjobportal_companies, $wpjobportal_arr->noofcompanies, $wpjobportal_arr->category, $wpjobportal_arr->posted, $wpjobportal_arr->listingstyle, $wpjobportal_theme, $wpjobportal_arr->location, $wpjobportal_arr->moduleheight, $wpjobportal_jobwidth, $wpjobportal_arr->companyheight, $wpjobportal_jobfloat, $wpjobportal_arr->topmargin, $wpjobportal_arr->leftmargin, $wpjobportal_arr->companylogo, $wpjobportal_arr->complogowidth, $wpjobportal_arr->complogoheight, $wpjobportal_arr->fieldcolumn, $wpjobportal_arr->listingstyle, $wpjobportal_arr->title, $wpjobportal_arr->showtitle, $speedTest, $sliding, $slidingdirection, $consecutivesliding, $wpjobportal_arr->nofcompanies, $wpjobportal_arr->nofcompaniesrowtab, $wpjobportal_arr->boxstyle);

                echo wp_kses($wpjobportal_modules_html, WPJOBPORTAL_ALLOWED_TAGS);
                $wpjobportal_classname = $wpjobportal_layoutName;

                $wpjobportal_color1 = $wpjobportal_arr->titlecolor;
                $wpjobportal_color2 = $wpjobportal_arr->titleborderbottom;
                $wpjobportal_color3 = $wpjobportal_arr->backgroundcolor;
                $wpjobportal_color4 = $wpjobportal_arr->bordercolor;
                $wpjobportal_color5 = $wpjobportal_arr->datalabelcolor;
                $wpjobportal_color6 = $wpjobportal_arr->datavaluecolor;

                $echo_style = WPJOBPORTALincluder::getJSModel('wpjobportalwidgets')->perpareStyleSheet($wpjobportal_classname , $wpjobportal_color1 , $wpjobportal_color2 , $wpjobportal_color3 , $wpjobportal_color4 , $wpjobportal_color5 , $wpjobportal_color6 );
                echo wp_kses($echo_style, WPJOBPORTAL_ALLOWED_TAGS);
            }
        }
        $wpjobportal_content .= ob_get_clean();
        return $wpjobportal_content;
    }

    function show_searchresume($raw_args, $wpjobportal_content = null) {

        ob_start();

        $wpjobportal_defaults = array(
            'title' => esc_html(__('Search Resume', 'wp-job-portal')),
            'showtitle' => '1',
            'apptitle' => '1',
            'name' => '1',
            'natinality' => '1',
            'gender' => '1',
            'iamavailable' => '1',
            'category' => '1',
            'jobtype' => '1',
            'salaryrange' => '1',
            'heighesteducation' => '1',
            'experience' => '1',
            'columnperrow' => '1',
        );

        $wpjobportal_arr = (object) shortcode_atts($wpjobportal_defaults, $raw_args);

        wpjobportal::wpjobportal_addStyleSheets();
        $offline = wpjobportal::$_config->getConfigurationByConfigName('offline');
        if ($offline == 1) {
            WPJOBPORTALlayout::getSystemOffline();
        } else {
            $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme');
            if($wpjobportal_module != null){
                $wpjobportal_pageid = get_the_ID();
                wpjobportal::wpjobportal_setPageID($wpjobportal_pageid);
                wpjobportal::wpjobportal_addStyleSheets();
                wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
                WPJOBPORTALincluder::include_file($wpjobportal_module);
                $wpjobportal_content .= ob_get_clean();
                return $wpjobportal_content;
            }
            $wpjobportal_modules_html = WPJOBPORTALincluder::getJSModel('resumesearch')->getSearchResume_Widget($wpjobportal_arr->title, $wpjobportal_arr->showtitle, $wpjobportal_arr->apptitle, $wpjobportal_arr->name, $wpjobportal_arr->natinality, $wpjobportal_arr->gender, $wpjobportal_arr->iamavailable, $wpjobportal_arr->category, $wpjobportal_arr->jobtype, $wpjobportal_arr->salaryrange, $wpjobportal_arr->heighesteducation, $wpjobportal_arr->columnperrow, $wpjobportal_arr->experience);
            echo wp_kses($wpjobportal_modules_html, WPJOBPORTAL_ALLOWED_TAGS);
        }

        $wpjobportal_content .= ob_get_clean();
        return $wpjobportal_content;
    }

    function show_jobbycategory($raw_args, $wpjobportal_content = null) {

        ob_start();

        $wpjobportal_defaults = array(
            'title' => esc_html(__('Jobs By Categories', 'wp-job-portal')),
            'showtitle' => '1',
            'maximumrecords' => '20',
            'haverecords' => '1',
            'showallcats' => '2',
            'columnperrow' => '3',
            'titlecolor' => '',
            'backgroundcolor' => '',
            'bordercolor' => '',
        );

        $wpjobportal_arr = (object) shortcode_atts($wpjobportal_defaults, $raw_args);

        wpjobportal::wpjobportal_addStyleSheets();

        $offline = wpjobportal::$_config->getConfigurationByConfigName('offline');
        if ($offline == 1) {
            WPJOBPORTALlayout::getSystemOffline();
        } else {
            $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme');
            if($wpjobportal_module != null){
                $wpjobportal_pageid = get_the_ID();
                wpjobportal::wpjobportal_setPageID($wpjobportal_pageid);
                wpjobportal::wpjobportal_addStyleSheets();
                wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
                WPJOBPORTALincluder::include_file($wpjobportal_module);
                $wpjobportal_content .= ob_get_clean();
                return $wpjobportal_content;
            }
            $wpjobportal_classname = 'category' . uniqid();

            $wpjobportal_color1 = $wpjobportal_arr->titlecolor;
            $wpjobportal_color2 = $wpjobportal_arr->backgroundcolor;
            $wpjobportal_color3 = $wpjobportal_arr->bordercolor;

            $wpjobportal_categories = WPJOBPORTALincluder::getJSModel('job')->getJobsBycategory_Widget($wpjobportal_arr->showallcats, $wpjobportal_arr->haverecords, $wpjobportal_arr->maximumrecords);

            $wpjobportal_html = WPJOBPORTALincluder::getJSModel('wpjobportalwidgets')->listModuleByJobcatOrType($wpjobportal_categories, $wpjobportal_classname, $wpjobportal_arr->showtitle, $wpjobportal_arr->title, $wpjobportal_arr->columnperrow, 2 );
            echo wp_kses($wpjobportal_html, WPJOBPORTAL_ALLOWED_TAGS);

            $echo_style = WPJOBPORTALincluder::getJSModel('wpjobportalwidgets')->prepareStyleForBlocks($wpjobportal_classname, $wpjobportal_color1, $wpjobportal_color2, $wpjobportal_color3);
            echo wp_kses($echo_style, WPJOBPORTAL_ALLOWED_TAGS);

        }

        $wpjobportal_content .= ob_get_clean();
        return $wpjobportal_content;
    }

    function show_jobbytypes($raw_args, $wpjobportal_content = null) {

        ob_start();

        $wpjobportal_defaults = array(
            'title' => esc_html(__('Jobs By Types', 'wp-job-portal')),
            'showtitle' => '1',
            'maximumrecords' => '20',
            'haverecords' => '1',
            'showallcats' => '2',
            'columnperrow' => '3',
            'titlecolor' => '',
            'backgroundcolor' => '',
            'bordercolor' => '',
        );

        $wpjobportal_arr = (object) shortcode_atts($wpjobportal_defaults, $raw_args);

        wpjobportal::wpjobportal_addStyleSheets();
        $offline = wpjobportal::$_config->getConfigurationByConfigName('offline');
        if ($offline == 1) {
            WPJOBPORTALlayout::getSystemOffline();
        } else {
            $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme');
            if($wpjobportal_module != null){
                $wpjobportal_pageid = get_the_ID();
                wpjobportal::wpjobportal_setPageID($wpjobportal_pageid);
                wpjobportal::wpjobportal_addStyleSheets();
                wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
                WPJOBPORTALincluder::include_file($wpjobportal_module);
                $wpjobportal_content .= ob_get_clean();
                return $wpjobportal_content;
            }
            $wpjobportal_classname = 'jobtype' . uniqid();
            $wpjobportal_color1 = $wpjobportal_arr->titlecolor;
            $wpjobportal_color2 = $wpjobportal_arr->backgroundcolor;
            $wpjobportal_color3 = $wpjobportal_arr->bordercolor;

            $types = WPJOBPORTALincluder::getJSModel('job')->getJobsByTypes_Widget($wpjobportal_arr->showallcats, $wpjobportal_arr->haverecords, $wpjobportal_arr->maximumrecords);

            $wpjobportal_html = WPJOBPORTALincluder::getJSModel('wpjobportalwidgets')->listModuleByJobcatOrType($types, $wpjobportal_classname, $wpjobportal_arr->showtitle, $wpjobportal_arr->title, $wpjobportal_arr->columnperrow, 1 );
            echo wp_kses($wpjobportal_html, WPJOBPORTAL_ALLOWED_TAGS);

            $echo_style = WPJOBPORTALincluder::getJSModel('wpjobportalwidgets')->prepareStyleForBlocks($wpjobportal_classname, $wpjobportal_color1, $wpjobportal_color2, $wpjobportal_color3);
            echo wp_kses($echo_style, WPJOBPORTAL_ALLOWED_TAGS);

        }

        $wpjobportal_content .= ob_get_clean();
        return $wpjobportal_content;
    }

    function show_jobsonmap($raw_args, $wpjobportal_content = null) {

        ob_start();

        $wpjobportal_defaults = array(
            'title' => esc_html(__('Hot jobs', 'wp-job-portal')),
            'showtitle' => 1,
            'numberofjobs' => 20,
            'company' => 1,
            'category' => 1,
            'moduleheight' => 300,
            'mapzoom' => 10,
        );

        $wpjobportal_arr = (object) shortcode_atts($wpjobportal_defaults, $raw_args);

        wpjobportal::wpjobportal_addStyleSheets();
        $offline = wpjobportal::$_config->getConfigurationByConfigName('offline');
        if ($offline == 1) {
            WPJOBPORTALlayout::getSystemOffline();
        } else {
            $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme');
            if($wpjobportal_module != null){
                $wpjobportal_pageid = get_the_ID();
                wpjobportal::wpjobportal_setPageID($wpjobportal_pageid);
                wpjobportal::wpjobportal_addStyleSheets();
                wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
                WPJOBPORTALincluder::include_file($wpjobportal_module);
                $wpjobportal_content .= ob_get_clean();
                return $wpjobportal_content;
            }
            $wpjobportal_jobs = WPJOBPORTALincluder::getJSModel('job')->getNewestJobsForMap_Widget($wpjobportal_arr->numberofjobs);
            $wpjobportal_html = WPJOBPORTALincluder::getJSModel('wpjobportalwidgets')->listModuleJobsForMap($wpjobportal_jobs, $wpjobportal_arr->title, $wpjobportal_arr->showtitle, $wpjobportal_arr->company, $wpjobportal_arr->category, $wpjobportal_arr->moduleheight, $wpjobportal_arr->mapzoom);
            echo wp_kses($wpjobportal_html, WPJOBPORTAL_ALLOWED_TAGS);
        }

        $wpjobportal_content .= ob_get_clean();
        return $wpjobportal_content;
    }

    function show_jobstats($raw_args, $wpjobportal_content = null) {

        ob_start();

        $wpjobportal_defaults = array(
            'title' => esc_html(__('Stats', 'wp-job-portal')),
            'showtitle' => '1',
            'employer' => '1',
            'jobseeker' => '1',
            'jobs' => '1',
            'companies' => '1',
            'activejobs' => '1',
            'resumes' => '1',
            'todaystats' => '1',
            'titlecolor' => '',
            'backgroundcolor' => '',
            'bordercolor' => '',
        );

        $wpjobportal_arr = (object) shortcode_atts($wpjobportal_defaults, $raw_args);

        wpjobportal::wpjobportal_addStyleSheets();
        $offline = wpjobportal::$_config->getConfigurationByConfigName('offline');
        if ($offline == 1) {
            WPJOBPORTALlayout::getSystemOffline();
        } else {
            $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme');
            if($wpjobportal_module != null){
                $wpjobportal_pageid = get_the_ID();
                wpjobportal::wpjobportal_setPageID($wpjobportal_pageid);
                wpjobportal::wpjobportal_addStyleSheets();
                wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
                WPJOBPORTALincluder::include_file($wpjobportal_module);
                $wpjobportal_content .= ob_get_clean();
                return $wpjobportal_content;
            }
            $wpjobportal_classname = 'stats' . uniqid();
            $wpjobportal_data = WPJOBPORTALincluder::getJSModel('common')->getJobsStats_Widget($wpjobportal_classname, $wpjobportal_arr->title, $wpjobportal_arr->showtitle, $wpjobportal_arr->employer, $wpjobportal_arr->jobseeker, $wpjobportal_arr->jobs, $wpjobportal_arr->companies, $wpjobportal_arr->activejobs, $wpjobportal_arr->resumes, $wpjobportal_arr->todaystats);
            $wpjobportal_modules_html = WPJOBPORTALincluder::getJSModel('common')->listModuleJobsStats($wpjobportal_classname, $wpjobportal_arr->title, $wpjobportal_arr->showtitle, $wpjobportal_arr->employer, $wpjobportal_arr->jobseeker, $wpjobportal_arr->jobs, $wpjobportal_arr->companies, $wpjobportal_arr->activejobs, $wpjobportal_arr->resumes, $wpjobportal_arr->todaystats,$wpjobportal_data);
            echo wp_kses($wpjobportal_modules_html, WPJOBPORTAL_ALLOWED_TAGS);

            $wpjobportal_color1 = $wpjobportal_arr->titlecolor;
            $wpjobportal_color2 = $wpjobportal_arr->backgroundcolor;
            $wpjobportal_color3 = $wpjobportal_arr->bordercolor;

            $echo_style = WPJOBPORTALincluder::getJSModel('wpjobportalwidgets')->prepareStyleForStats($wpjobportal_classname, $wpjobportal_color1, $wpjobportal_color2, $wpjobportal_color3);
            echo wp_kses($echo_style, WPJOBPORTAL_ALLOWED_TAGS);
        }

        $wpjobportal_content .= ob_get_clean();
        return $wpjobportal_content;
    }

    function show_jobsbycity($raw_args, $wpjobportal_content = null) {

        ob_start();

        $wpjobportal_defaults = array(
            'title' => esc_html(__('Jobs by cities', 'wp-job-portal')),
            'showtitle' => '1',
            'maximumrecords' => '20',
            'haverecords' => '1',
            'columnperrow' => '3',
            'titlecolor' => '',
            'backgroundcolor' => '',
            'bordercolor' => '',
        );

        $wpjobportal_showjobsby = 1; //for cities

        $wpjobportal_arr = (object) shortcode_atts($wpjobportal_defaults, $raw_args);
        wpjobportal::wpjobportal_addStyleSheets();
        $offline = wpjobportal::$_config->getConfigurationByConfigName('offline');
        if ($offline == 1) {
            WPJOBPORTALlayout::getSystemOffline();
        } else {
            $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme');
            if($wpjobportal_module != null){
                $wpjobportal_pageid = get_the_ID();
                wpjobportal::wpjobportal_setPageID($wpjobportal_pageid);
                wpjobportal::wpjobportal_addStyleSheets();
                wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
                WPJOBPORTALincluder::include_file($wpjobportal_module);
                $wpjobportal_content .= ob_get_clean();
                return $wpjobportal_content;
            }
            if ($wpjobportal_showjobsby != 0) {

                $wpjobportal_jobs = WPJOBPORTALincluder::getJSModel('job')->getJobsBylocation_Widget($wpjobportal_showjobsby, $wpjobportal_arr->haverecords, $wpjobportal_arr->maximumrecords);

                $wpjobportal_classname = 'city' . uniqid();

                $wpjobportal_html = WPJOBPORTALincluder::getJSModel('wpjobportalwidgets')->listModuleLocation($wpjobportal_jobs, $wpjobportal_classname, $wpjobportal_arr->showtitle, $wpjobportal_arr->title, $wpjobportal_arr->columnperrow, $wpjobportal_showjobsby);

                echo wp_kses($wpjobportal_html, WPJOBPORTAL_ALLOWED_TAGS);

                $wpjobportal_color1 = $wpjobportal_arr->titlecolor;
                $wpjobportal_color2 = $wpjobportal_arr->backgroundcolor;
                $wpjobportal_color3 = $wpjobportal_arr->bordercolor;

                $echo_style = WPJOBPORTALincluder::getJSModel('wpjobportalwidgets')->prepareStyleForBlocks($wpjobportal_classname, $wpjobportal_color1, $wpjobportal_color2, $wpjobportal_color3);

                echo wp_kses($echo_style, WPJOBPORTAL_ALLOWED_TAGS);

            }
        }

        $wpjobportal_content .= ob_get_clean();
        return $wpjobportal_content;
    }

    function show_jobsbystate($raw_args, $wpjobportal_content = null) {

        ob_start();

        $wpjobportal_defaults = array(
            'title' => esc_html(__('Jobs by state', 'wp-job-portal')),
            'showtitle' => '1',
            'maximumrecords' => '20',
            'haverecords' => '1',
            'columnperrow' => '3',
            'titlecolor' => '',
            'backgroundcolor' => '',
            'bordercolor' => '',
        );

        $wpjobportal_showjobsby = 2; //for state

        $wpjobportal_arr = (object) shortcode_atts($wpjobportal_defaults, $raw_args);

        wpjobportal::wpjobportal_addStyleSheets();
        $offline = wpjobportal::$_config->getConfigurationByConfigName('offline');
        if ($offline == 1) {
            WPJOBPORTALlayout::getSystemOffline();
        } else {
            $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme');
            if($wpjobportal_module != null){
                $wpjobportal_pageid = get_the_ID();
                wpjobportal::wpjobportal_setPageID($wpjobportal_pageid);
                wpjobportal::wpjobportal_addStyleSheets();
                wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
                WPJOBPORTALincluder::include_file($wpjobportal_module);
                $wpjobportal_content .= ob_get_clean();
                return $wpjobportal_content;
            }
            $wpjobportal_jobs = '';
            if ($wpjobportal_showjobsby != 0) {

                $wpjobportal_jobs = WPJOBPORTALincluder::getJSModel('job')->getJobsBylocation_Widget($wpjobportal_showjobsby, $wpjobportal_arr->haverecords, $wpjobportal_arr->maximumrecords);

                $wpjobportal_classname = 'state' . uniqid();

                $wpjobportal_html = WPJOBPORTALincluder::getJSModel('wpjobportalwidgets')->listModuleLocation($wpjobportal_jobs, $wpjobportal_classname, $wpjobportal_arr->showtitle, $wpjobportal_arr->title, $wpjobportal_arr->columnperrow, $wpjobportal_showjobsby);

                echo wp_kses($wpjobportal_html, WPJOBPORTAL_ALLOWED_TAGS);

                $wpjobportal_color1 = $wpjobportal_arr->titlecolor;
                $wpjobportal_color2 = $wpjobportal_arr->backgroundcolor;
                $wpjobportal_color3 = $wpjobportal_arr->bordercolor;

                $echo_style = WPJOBPORTALincluder::getJSModel('wpjobportalwidgets')->prepareStyleForBlocks($wpjobportal_classname, $wpjobportal_color1, $wpjobportal_color2, $wpjobportal_color3);

                echo wp_kses($echo_style, WPJOBPORTAL_ALLOWED_TAGS);
            }
        }

        $wpjobportal_content .= ob_get_clean();
        return $wpjobportal_content;
    }

    function show_jobsbycountries($raw_args, $wpjobportal_content = null) {

        ob_start();

        $wpjobportal_defaults = array(
            'title' => esc_html(__('Jobs by countries', 'wp-job-portal')),
            'showtitle' => '1',
            'maximumrecords' => '20',
            'haverecords' => '1',
            'columnperrow' => '3',
            'titlecolor' => '',
            'backgroundcolor' => '',
            'bordercolor' => '',
        );

        $wpjobportal_showjobsby = 3; //for countries

        $wpjobportal_arr = (object) shortcode_atts($wpjobportal_defaults, $raw_args);

        wpjobportal::wpjobportal_addStyleSheets();
        $offline = wpjobportal::$_config->getConfigurationByConfigName('offline');
        if ($offline == 1) {
            WPJOBPORTALlayout::getSystemOffline();
        } else {
            $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme');
            if($wpjobportal_module != null){
                $wpjobportal_pageid = get_the_ID();
                wpjobportal::wpjobportal_setPageID($wpjobportal_pageid);
                wpjobportal::wpjobportal_addStyleSheets();
                wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
                WPJOBPORTALincluder::include_file($wpjobportal_module);
                $wpjobportal_content .= ob_get_clean();
                return $wpjobportal_content;
            }
            $wpjobportal_jobs = '';
            if ($wpjobportal_showjobsby != 0) {

                $wpjobportal_jobs = WPJOBPORTALincluder::getJSModel('job')->getJobsBylocation_Widget($wpjobportal_showjobsby, $wpjobportal_arr->haverecords, $wpjobportal_arr->maximumrecords);

                $wpjobportal_classname = 'country' . uniqid();

                $wpjobportal_html = WPJOBPORTALincluder::getJSModel('wpjobportalwidgets')->listModuleLocation($wpjobportal_jobs, $wpjobportal_classname, $wpjobportal_arr->showtitle, $wpjobportal_arr->title, $wpjobportal_arr->columnperrow, $wpjobportal_showjobsby);

                echo wp_kses($wpjobportal_html, WPJOBPORTAL_ALLOWED_TAGS);

                $wpjobportal_color1 = $wpjobportal_arr->titlecolor;
                $wpjobportal_color2 = $wpjobportal_arr->backgroundcolor;
                $wpjobportal_color3 = $wpjobportal_arr->bordercolor;

                $echo_style = WPJOBPORTALincluder::getJSModel('wpjobportalwidgets')->prepareStyleForBlocks($wpjobportal_classname, $wpjobportal_color1, $wpjobportal_color2, $wpjobportal_color3);

                echo wp_kses($echo_style, WPJOBPORTAL_ALLOWED_TAGS);
            }
        }

        $wpjobportal_content .= ob_get_clean();
        return $wpjobportal_content;
    }
}

$wpjobportal_shortcodes = new WPJOBPORTALshortcodes();
add_action( 'init', 'wpjobportal_jobs_block' );

function wpjobportal_jobs_block(){
    if(!function_exists("register_block_type")){
        return;
    }
    wp_register_script(
        'wpjobportaljobsblock',
        esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/gutenberg/jobs.js',
        array( 'wp-blocks', 'wp-element','wp-editor' )
    );
    register_block_type( 'wpjobportal/wpjobportaljobsblock', array(
        'attributes'      => array(
            'title'    => array(
                'type'      => 'string',
                'default'   => '',
            ),
            'typeofjobs'    => array(
                'type'      => 'select',
                'default'   => '',
            ),
            'noofjobs'    => array(
                'type'      => 'string',
                'default'   => '',
            ),
            'fieldcolumn'    => array(
                'type'      => 'select',
                'default'   => '',
            ),
            'listingstyle' => array(
                'type' => 'select',
                'default' => ''
            ),
        ),
        'render_callback' => 'wpjobportal_jobs_block_widgets',
        'editor_script' => 'wpjobportaljobsblock',
    ) );
}

function wpjobportal_jobs_block_widgets($attributes, $wpjobportal_content){
    $wpjobportal_defaults = array(
        'wpjobportalpageid' => '0',
        'title' => esc_html(__('Newest jobs','wp-job-portal')),
        'typeofjobs' => '1',
        'noofjobs' => '5',
        'fieldcolumn' => '1',
        'listingstyle' => '0',
    );

    $wpjobportal_sanitized_args = shortcode_atts($wpjobportal_defaults, $attributes);
    if($wpjobportal_sanitized_args['fieldcolumn'] == '' || $wpjobportal_sanitized_args['fieldcolumn'] == 0){
        $wpjobportal_sanitized_args['fieldcolumn'] = 1;
    }
    if($wpjobportal_sanitized_args['noofjobs'] == '' || $wpjobportal_sanitized_args['noofjobs'] == 0){
        $wpjobportal_sanitized_args['noofjobs'] = 1;
    }
    if($wpjobportal_sanitized_args['title'] == ''){
        $wpjobportal_sanitized_args['title'] = 'Latest Jobs';
    }
    if($wpjobportal_sanitized_args['wpjobportalpageid'] == '' || $wpjobportal_sanitized_args['wpjobportalpageid'] == 0){
        $wpjobportal_sanitized_args['wpjobportalpageid'] = wpjobportal::wpjobportal_getPageid();
    }
    if($wpjobportal_sanitized_args['typeofjobs'] == '' || $wpjobportal_sanitized_args['typeofjobs'] == 0){
        $wpjobportal_sanitized_args['typeofjobs'] = 1;
    }
    if($wpjobportal_sanitized_args['listingstyle'] == ''){
        $wpjobportal_sanitized_args['listingstyle'] = 1;
    }

    $wpjobportal_jobs = WPJOBPORTALincluder::getJSModel('job')->getJobs_Widget($wpjobportal_sanitized_args['typeofjobs'], $wpjobportal_sanitized_args['noofjobs']);
    //Frontend HTML starts -----------
    $wpjobportal_mod = 'newestjobs';
    if ($wpjobportal_sanitized_args['typeofjobs'] == 1) {
        $wpjobportal_mod = 'newestjobs';
    } elseif ($wpjobportal_sanitized_args['typeofjobs'] == 2) {
        $wpjobportal_mod = 'topjobs';
    } elseif ($wpjobportal_sanitized_args['typeofjobs'] == 3) {
        $wpjobportal_mod = 'hotjobs';
    }  elseif ($wpjobportal_sanitized_args['typeofjobs'] == 5) {
        $wpjobportal_mod = 'featuredjobs';
    }
    $wpjobportal_layoutName = $wpjobportal_mod . uniqid();
    // parameeters to be use later
    $wpjobportal_html = '';
    if ($wpjobportal_jobs != '') {
        $wpjobportal_html = WPJOBPORTALincluder::getJSModel('wpjobportalwidgets')->getJOBSWidgetHTML($wpjobportal_jobs,$wpjobportal_sanitized_args['wpjobportalpageid'],$wpjobportal_sanitized_args['title'],$wpjobportal_sanitized_args['fieldcolumn'],$wpjobportal_layoutName,$wpjobportal_sanitized_args['listingstyle'],$wpjobportal_sanitized_args['typeofjobs']);
    }
    wp_enqueue_style('wpjobportal-style', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/style.css');
    if (is_rtl()) {
        wp_enqueue_style('wpjobportal-site-rtl', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/stylertl.css');
    }
    return $wpjobportal_html;
}

add_action( 'init', 'wpjobportal_companies_block');

function wpjobportal_companies_block(){
    if(!function_exists("register_block_type")){
        return;
    }
    wp_register_script(
        'wpjobportalcompaniesblock',
        esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/gutenberg/companies.js',
        array( 'wp-blocks', 'wp-element','wp-editor' )
    );
    register_block_type( 'wpjobportal/wpjobportalcompaniesblock', array(
        'attributes'      => array(
            'title' => array(
                'type' => 'string',
                'default' => ''
            ),
            'companytype' => array(
                'type' => 'select',
                'default' => ''
            ),
            'listingstyle' => array(
                'type' => 'select',
                'default' => ''
            ),
            'fieldcolumn' => array(
                'type' => 'select',
                'default' =>''
            ),
            'noofcompanies' => array(
                'type' => 'string',
                'default' => ''
            ),
        ),
        'render_callback' => 'wpjobportal_companies_block_widgets',
        'editor_script' => 'wpjobportalcompaniesblock'
    ) );
}

function wpjobportal_companies_block_widgets($attributes, $wpjobportal_content){
    $wpjobportal_defaults = array(
        'wpjobportalpageid' => '0',
        'title' => esc_html(__('Companies','wp-job-portal')),
        'companytype' => '1',
        'fieldcolumn' => '1',
        'listingstyle' => '0',
        'noofcompanies' => '1',
    );
    $wpjobportal_sanitized_args = shortcode_atts($wpjobportal_defaults, $attributes);
    if($wpjobportal_sanitized_args['fieldcolumn'] == '' || $wpjobportal_sanitized_args['fieldcolumn'] == 0){
        $wpjobportal_sanitized_args['fieldcolumn'] = 1;
    }
    if($wpjobportal_sanitized_args['title'] == ''){
        $wpjobportal_sanitized_args['title'] = 'Companies';
    }
    if($wpjobportal_sanitized_args['wpjobportalpageid'] == '' || $wpjobportal_sanitized_args['wpjobportalpageid'] == 0){
        $wpjobportal_sanitized_args['wpjobportalpageid'] = wpjobportal::wpjobportal_getPageid();
    }
    if($wpjobportal_sanitized_args['companytype'] == '' || $wpjobportal_sanitized_args['companytype'] == 0){
        $wpjobportal_sanitized_args['companytype'] = 2;
    }
    if($wpjobportal_sanitized_args['noofcompanies'] == '' || $wpjobportal_sanitized_args['noofcompanies'] == 0){
        $wpjobportal_sanitized_args['noofcompanies'] = 1;
    }
    if($wpjobportal_sanitized_args['listingstyle'] == ''){
        $wpjobportal_sanitized_args['listingstyle'] = 1;
    }


    if ($wpjobportal_sanitized_args['companytype'] == 2) {
        $wpjobportal_mod = 'featuredcompany';
    }
    $wpjobportal_layoutName = $wpjobportal_mod . uniqid();
    $wpjobportal_html = '';
    $wpjobportal_companies = WPJOBPORTALincluder::getJSModel('company')->getCompanies_Widget($wpjobportal_sanitized_args['companytype'], $wpjobportal_sanitized_args['noofcompanies']);
    $wpjobportal_html = WPJOBPORTALincluder::getJSModel('wpjobportalwidgets')->getCompanies_WidgetHtml($wpjobportal_sanitized_args['title'],$wpjobportal_layoutName, $wpjobportal_companies, $wpjobportal_sanitized_args['noofcompanies'], $wpjobportal_sanitized_args['listingstyle'],$wpjobportal_sanitized_args['companytype'],$wpjobportal_sanitized_args['fieldcolumn']);
    wp_enqueue_style('wpjobportal-style', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/style.css');
    if (is_rtl()) {
        wp_enqueue_style('wpjobportal-site-rtl', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/stylertl.css');
    }
    return $wpjobportal_html;
}

add_action( 'init', 'wpjobportal_resumes_block');

function wpjobportal_resumes_block(){
    if(!function_exists("register_block_type")){
        return;
    }
    wp_register_script(
        'wpjobportalresumesblock',
        esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/gutenberg/resumes.js',
        array( 'wp-blocks', 'wp-element','wp-editor' )
    );
    register_block_type( 'wpjobportal/wpjobportalresumesblock', array(
        'attributes'      => array(
            'title' => array(
                'type' => 'string',
                'default' => ''
            ),
            'typeofresume' => array(
                'type' => 'select',
                'default' => ''
            ),
            'listingstyle' => array(
                'type' => 'select',
                'default' => ''
            ),
            'fieldcolumn' => array(
                'type' => 'select',
                'default' =>''
            ),
            'noofresumes' => array(
                'type' => 'string',
                'default' => ''
            ),
        ),
        'render_callback' => 'wpjobportal_resumes_block_widgets',
        'editor_script' => 'wpjobportalresumesblock'
    ) );
}

function wpjobportal_resumes_block_widgets($attributes, $wpjobportal_content){
    $wpjobportal_defaults = array(
        'wpjobportalpageid' => '0',
        'title' => esc_html(__('Latest Resumes','wp-job-portal')),
        'typeofresume' => '1',
        'fieldcolumn' => '1',
        'listingstyle' => '0',
        'noofresumes' => '1',
    );
    $wpjobportal_sanitized_args = shortcode_atts($wpjobportal_defaults, $attributes);
    if($wpjobportal_sanitized_args['fieldcolumn'] == '' || $wpjobportal_sanitized_args['fieldcolumn'] == 0){
        $wpjobportal_sanitized_args['fieldcolumn'] = 1;
    }
    if($wpjobportal_sanitized_args['title'] == ''){
        $wpjobportal_sanitized_args['title'] = 'Latest Resumes';
    }
    if($wpjobportal_sanitized_args['wpjobportalpageid'] == '' || $wpjobportal_sanitized_args['wpjobportalpageid'] == 0){
        $wpjobportal_sanitized_args['wpjobportalpageid'] = wpjobportal::wpjobportal_getPageid();
    }
    if($wpjobportal_sanitized_args['typeofresume'] == '' || $wpjobportal_sanitized_args['typeofresume'] == 0){
        $wpjobportal_sanitized_args['typeofresume'] = 1;
    }
    if($wpjobportal_sanitized_args['noofresumes'] == '' || $wpjobportal_sanitized_args['noofresumes'] == 0){
        $wpjobportal_sanitized_args['noofresumes'] = 1;
    }
    if($wpjobportal_sanitized_args['listingstyle'] == ''){
        $wpjobportal_sanitized_args['listingstyle'] = 1;
    }

    $wpjobportal_mod = 'newestresume';
    if ($wpjobportal_sanitized_args['typeofresume'] == 1) {
        $wpjobportal_mod = 'newestresume';
    } elseif ($wpjobportal_sanitized_args['typeofresume'] == 2) {
        $wpjobportal_mod = 'topresume';
    } elseif ($wpjobportal_sanitized_args['typeofresume'] == 4) {
        $wpjobportal_mod = 'featuredresume';
    }
    $wpjobportal_layoutName = $wpjobportal_mod . uniqid();
    $wpjobportal_html = '';
    $wpjobportal_resumes = WPJOBPORTALincluder::getJSModel('resume')->getResumes_Widget($wpjobportal_sanitized_args['typeofresume'], $wpjobportal_sanitized_args['noofresumes']);
    $wpjobportal_html = WPJOBPORTALincluder::getJSModel('wpjobportalwidgets')->getResume_WidgetHtml($wpjobportal_sanitized_args['title'],$wpjobportal_layoutName, $wpjobportal_resumes, $wpjobportal_sanitized_args['noofresumes'], $wpjobportal_sanitized_args['listingstyle'],$wpjobportal_sanitized_args['typeofresume'],$wpjobportal_sanitized_args['fieldcolumn']);
    wp_enqueue_style('wpjobportal-style', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/style.css');
    if (is_rtl()) {
        wp_enqueue_style('wpjobportal-site-rtl', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/stylertl.css');
    }
    return $wpjobportal_html;
}

add_action( 'init', 'wpjobportal_jobsearch_block');

function wpjobportal_jobsearch_block(){
    if(!function_exists("register_block_type")){
        return;
    }
    wp_register_script(
        'wpjobportaljobsearchblock',
        esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/gutenberg/jobsearch.js',
        array( 'wp-blocks', 'wp-element','wp-editor' )
    );
    register_block_type( 'wpjobportal/wpjobportaljobsearchblock', array(
        'attributes'      => array(
            'title' => array(
                'type' => 'string',
                'default' => 'Job Search'
            ),
            'showtitle' => array(
                'type' => 'string',
                'default' => ''
            ),
            'jobtitle' => array(
                'type' => 'select',
                'default' => ''
            ),
            'category' => array(
                'type' => 'select',
                'default' =>''
            ),
            'jobtype' => array(
                'type' => 'select',
                'default' =>''
            ),
            'jobstatus' => array(
                'type' => 'select',
                'default' =>''
            ),
            'salaryrange' => array(
                'type' => 'select',
                'default' =>''
            ),
            'duration' => array(
                'type' => 'select',
                'default' =>''
            ),
            'startpublishing' => array(
                'type' => 'select',
                'default' =>''
            ),
            'stoppublishing' => array(
                'type' => 'select',
                'default' =>''
            ),
            'company' => array(
                'type' => 'select',
                'default' =>''
            ),
            'address' => array(
                'type' => 'select',
                'default' =>''
            ),
            'columnperrow' => array(
                'type' => 'string',
                'default' => ''
            ),
        ),
        'render_callback' => 'wpjobportal_jobsearch_block_widgets',
        'editor_script' => 'wpjobportaljobsearchblock'
    ) );
}

function wpjobportal_jobsearch_block_widgets($attributes, $wpjobportal_content){
    $wpjobportal_defaults = array(
        'wpjobportalpageid' => '0',
        'title' => esc_html(__('Job Search','wp-job-portal')),
        'showtitle' => '1',
        'jobtitle' => '1',
        'category' => '1',
        'jobtype' => '1',
        'jobstatus' => '1',
        'salaryrange' => '1',
        'shift' => '1',
        'duration' => '1',
        'startpublishing' => '1',
        'stoppublishing' => '1',
        'company' => '1',
        'address' => '1',
        'columnperrow' => '1',
    );
    $wpjobportal_sanitized_args = shortcode_atts($wpjobportal_defaults, $attributes);
    if($wpjobportal_sanitized_args['wpjobportalpageid'] == '' || $wpjobportal_sanitized_args['wpjobportalpageid'] == 0){
        $wpjobportal_sanitized_args['wpjobportalpageid'] = wpjobportal::wpjobportal_getPageid();
    }
    if($wpjobportal_sanitized_args['title'] == ''){
        $wpjobportal_sanitized_args['title'] = 'Job Search';
    }
    if($wpjobportal_sanitized_args['showtitle'] == ''){
        $wpjobportal_sanitized_args['showtitle'] = 1;
    }
    if($wpjobportal_sanitized_args['jobtitle'] == ''){
        $wpjobportal_sanitized_args['jobtitle'] = 1;
    }
    if($wpjobportal_sanitized_args['category'] == ''){
        $wpjobportal_sanitized_args['category'] = 1;
    }
    if($wpjobportal_sanitized_args['jobtype'] == ''){
        $wpjobportal_sanitized_args['jobtype'] = 1;
    }
    if($wpjobportal_sanitized_args['jobstatus'] == ''){
        $wpjobportal_sanitized_args['jobstatus'] = 1;
    }
    if($wpjobportal_sanitized_args['salaryrange'] == ''){
        $wpjobportal_sanitized_args['salaryrange'] = 1;
    }
    if($wpjobportal_sanitized_args['shift'] == ''){
        $wpjobportal_sanitized_args['shift'] = 1;
    }
    if($wpjobportal_sanitized_args['duration'] == ''){
        $wpjobportal_sanitized_args['duration'] = 1;
    }
    if($wpjobportal_sanitized_args['startpublishing'] == ''){
        $wpjobportal_sanitized_args['startpublishing'] = 1;
    }
    if($wpjobportal_sanitized_args['stoppublishing'] == ''){
        $wpjobportal_sanitized_args['stoppublishing'] = 1;
    }
    if($wpjobportal_sanitized_args['company'] == ''){
        $wpjobportal_sanitized_args['company'] = 1;
    }
    if($wpjobportal_sanitized_args['address'] == ''){
        $wpjobportal_sanitized_args['address'] = 1;
    }
    if($wpjobportal_sanitized_args['columnperrow'] == '' || $wpjobportal_sanitized_args['columnperrow'] == ''){
        $wpjobportal_sanitized_args['columnperrow'] = 1;
    }

    $wpjobportal_html = '';
    $wpjobportal_html = WPJOBPORTALincluder::getJSModel('wpjobportalwidgets')->getSearchJobs_WidgetHTML($wpjobportal_sanitized_args['title'], $wpjobportal_sanitized_args['showtitle'], $wpjobportal_sanitized_args['jobtitle'], $wpjobportal_sanitized_args['category'], $wpjobportal_sanitized_args['jobtype'], $wpjobportal_sanitized_args['jobstatus'], $wpjobportal_sanitized_args['salaryrange'], $wpjobportal_sanitized_args['shift'], $wpjobportal_sanitized_args['duration'], $wpjobportal_sanitized_args['startpublishing'], $wpjobportal_sanitized_args['stoppublishing'], $wpjobportal_sanitized_args['company'], $wpjobportal_sanitized_args['address'], $wpjobportal_sanitized_args['columnperrow']);
    wp_enqueue_style('wpjobportal-style', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/style.css');
    wp_enqueue_style('wpjobportal-tokeninput', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/tokeninput.css');
    wp_enqueue_script('wpjobportal-tokeninput', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/jquery.tokeninput.js');
    if (is_rtl()) {
        wp_enqueue_style('wpjobportal-site-rtl', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/stylertl.css');
    }
    return $wpjobportal_html;
}
?>
