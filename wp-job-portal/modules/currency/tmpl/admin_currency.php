<?php
if (!defined('ABSPATH')) die('Restricted Access');
/*
 * Settings › Job attributes › Currencies (design 4).
 * Drawn by WPJOBPORTAL_Admin_Lookup_UI; the module's model loads the rows and its tasks do the work.
 */
WPJOBPORTAL_Admin_Lookup_UI::render(array(
    'page' => 'wpjobportal_currency', 'form' => 'formcurrency', 'nonce' => 'wpjobportal_currency_nonce',
    'title' => __('Currency', 'wp-job-portal'), 'noun' => __('currency', 'wp-job-portal'), 'nouns' => __('currencies', 'wp-job-portal'), 'noun_title' => __('Currency', 'wp-job-portal'),
    'crumbs' => array(array(__('Settings', 'wp-job-portal'), admin_url('admin.php?page=wpjobportal_configuration&wpjobportallt=configurations')), array(__('Job attributes', 'wp-job-portal'), admin_url('admin.php?page=wpjobportal_jobstatus'))),
    'label' => 'title', 'published' => 'status', 'default_for' => 'currencies', 'default' => 'default',
    'search' => array('title', isset(wpjobportal::$_data['filter']['title']) ? wpjobportal::$_data['filter']['title'] : '', __('Search currencies', 'wp-job-portal')),
    'columns' => array(
        array(__('Symbol', 'wp-job-portal'), function ($row) { return esc_html(trim((string) $row->symbol)); }),
        array(__('Code', 'wp-job-portal'), function ($row) { return '<code>' . esc_html($row->code) . '</code>'; }),
    ),
));
