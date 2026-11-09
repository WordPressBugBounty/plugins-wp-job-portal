<?php
if (!defined('ABSPATH')) die('Restricted Access');
/*
 * Settings › Locations › Countries › States of one country (design 4).
 * Drawn by WPJOBPORTAL_Admin_Lookup_UI; the module's model loads the rows and its tasks do the work.
 */
$wpjobportal_countryid = (int) get_option('wpjobportal_countryid_for_stateid');
$wpjobportal_country = $wpjobportal_countryid ? (string) wpjobportal::$_db->get_var('SELECT name FROM `' . wpjobportal::$_db->prefix . 'wj_portal_countries` WHERE id = ' . $wpjobportal_countryid) : '';
WPJOBPORTAL_Admin_Lookup_UI::render(array(
    'page' => 'wpjobportal_state', 'form' => 'formstate', 'nonce' => 'wpjobportal_state_nonce',
    'title' => $wpjobportal_country !== '' ? sprintf(/* translators: %s: country */ __('States of %s', 'wp-job-portal'), $wpjobportal_country) : __('States', 'wp-job-portal'),
    'noun' => __('state', 'wp-job-portal'), 'nouns' => __('states', 'wp-job-portal'), 'noun_title' => __('State', 'wp-job-portal'),
    'crumbs' => array(array(__('Settings', 'wp-job-portal'), admin_url('admin.php?page=wpjobportal_configuration&wpjobportallt=configurations')), array(__('Locations', 'wp-job-portal'), admin_url('admin.php?page=wpjobportal_country')), array(__('Countries', 'wp-job-portal'), admin_url('admin.php?page=wpjobportal_country'))),
    'url_args' => array('countryid' => $wpjobportal_countryid),
    'label' => 'name', 'published' => 'enabled', 'ordering' => false,
    'search' => array('searchname', isset(wpjobportal::$_data['filter']['searchname']) ? wpjobportal::$_data['filter']['searchname'] : '', __('Search states', 'wp-job-portal')),
    'checks' => array(array('city', __('Has cities', 'wp-job-portal'), isset(wpjobportal::$_data['filter']['city']) ? wpjobportal::$_data['filter']['city'] : '')),
    'row_links' => function ($row) use ($wpjobportal_countryid) {
        return array(__('Cities', 'wp-job-portal') => admin_url('admin.php?page=wpjobportal_city&countryid=' . $wpjobportal_countryid . '&stateid=' . (int) $row->id));
    },
));
