<?php
if (!defined('ABSPATH')) die('Restricted Access');
/*
 * Settings › Locations › Cities of a country or state (design 4).
 * Drawn by WPJOBPORTAL_Admin_Lookup_UI; the module's model loads the rows and its tasks do the work.
 */
$wpjobportal_countryid = (int) get_option('wpjobportal_countryid_for_city');
$wpjobportal_stateid = (int) get_option('wpjobportal_stateid_for_city');
$wpjobportal_prefix = wpjobportal::$_db->prefix . 'wj_portal_';
$wpjobportal_country = $wpjobportal_countryid ? (string) wpjobportal::$_db->get_var('SELECT name FROM `' . $wpjobportal_prefix . 'countries` WHERE id = ' . $wpjobportal_countryid) : '';
$wpjobportal_state = $wpjobportal_stateid ? (string) wpjobportal::$_db->get_var('SELECT name FROM `' . $wpjobportal_prefix . 'states` WHERE id = ' . $wpjobportal_stateid) : '';
$wpjobportal_crumbs = array(array(__('Settings', 'wp-job-portal'), admin_url('admin.php?page=wpjobportal_configuration&wpjobportallt=configurations')), array(__('Locations', 'wp-job-portal'), admin_url('admin.php?page=wpjobportal_country')), array(__('Countries', 'wp-job-portal'), admin_url('admin.php?page=wpjobportal_country')));
if ($wpjobportal_state !== '') {
    $wpjobportal_crumbs[] = array($wpjobportal_country, admin_url('admin.php?page=wpjobportal_state&countryid=' . $wpjobportal_countryid));
}
$wpjobportal_place = $wpjobportal_state !== '' ? $wpjobportal_state : $wpjobportal_country;
WPJOBPORTAL_Admin_Lookup_UI::render(array(
    'page' => 'wpjobportal_city', 'form' => 'formcity', 'nonce' => 'wpjobportal_city_nonce', 'remove' => 'removecity',
    'title' => $wpjobportal_place !== '' ? sprintf(/* translators: %s: country or state */ __('Cities of %s', 'wp-job-portal'), $wpjobportal_place) : __('Cities', 'wp-job-portal'),
    'noun' => __('city', 'wp-job-portal'), 'nouns' => __('cities', 'wp-job-portal'), 'noun_title' => __('City', 'wp-job-portal'),
    'crumbs' => $wpjobportal_crumbs,
    'url_args' => array('countryid' => $wpjobportal_countryid, 'stateid' => $wpjobportal_stateid),
    'label' => 'name', 'published' => 'enabled', 'ordering' => false,
    'search' => array('searchname', isset(wpjobportal::$_data['filter']['searchname']) ? wpjobportal::$_data['filter']['searchname'] : '', __('Search cities', 'wp-job-portal')),
));
