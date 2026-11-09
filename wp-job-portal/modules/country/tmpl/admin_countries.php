<?php
if (!defined('ABSPATH')) die('Restricted Access');
/*
 * Settings › Locations › Countries (design 4).
 * Drawn by WPJOBPORTAL_Admin_Lookup_UI; the module's model loads the rows and its tasks do the work.
 */
WPJOBPORTAL_Admin_Lookup_UI::render(array(
    'page' => 'wpjobportal_country', 'form' => 'formcountry', 'nonce' => 'wpjobportal_country_nonce',
    'title' => __('Countries', 'wp-job-portal'), 'noun' => __('country', 'wp-job-portal'), 'nouns' => __('countries', 'wp-job-portal'), 'noun_title' => __('Country', 'wp-job-portal'),
    'crumbs' => array(array(__('Settings', 'wp-job-portal'), admin_url('admin.php?page=wpjobportal_configuration&wpjobportallt=configurations')), array(__('Locations', 'wp-job-portal'), admin_url('admin.php?page=wpjobportal_country'))),
    'label' => 'name', 'published' => 'enabled', 'ordering' => false,
    'search' => array('countryname', isset(wpjobportal::$_data['filter']['countryname']) ? wpjobportal::$_data['filter']['countryname'] : '', __('Search countries', 'wp-job-portal')),
    'checks' => array(
        array('states', __('Has states', 'wp-job-portal'), isset(wpjobportal::$_data['filter']['states']) ? wpjobportal::$_data['filter']['states'] : ''),
        array('city', __('Has cities', 'wp-job-portal'), isset(wpjobportal::$_data['filter']['city']) ? wpjobportal::$_data['filter']['city'] : ''),
    ),
    'row_links' => function ($row) {
        return array(
            __('States', 'wp-job-portal') => admin_url('admin.php?page=wpjobportal_state&countryid=' . (int) $row->id),
            __('Cities', 'wp-job-portal') => admin_url('admin.php?page=wpjobportal_city&countryid=' . (int) $row->id),
        );
    },
));
