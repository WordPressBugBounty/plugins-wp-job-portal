<?php
if (!defined('ABSPATH')) die('Restricted Access');
/*
 * Settings › Job attributes › Salary range types (design 4).
 * Drawn by WPJOBPORTAL_Admin_Lookup_UI; the module's model loads the rows and its tasks do the work.
 */
WPJOBPORTAL_Admin_Lookup_UI::render(array(
    'page' => 'wpjobportal_salaryrangetype', 'form' => 'formsalaryrangetype', 'nonce' => 'wpjobportal_salary_range_type_nonce',
    'title' => __('Salary range types', 'wp-job-portal'), 'noun' => __('salary range type', 'wp-job-portal'), 'nouns' => __('salary range types', 'wp-job-portal'), 'noun_title' => __('Salary range type', 'wp-job-portal'),
    'crumbs' => array(array(__('Settings', 'wp-job-portal'), admin_url('admin.php?page=wpjobportal_configuration&wpjobportallt=configurations')), array(__('Job attributes', 'wp-job-portal'), admin_url('admin.php?page=wpjobportal_jobstatus'))),
    'label' => 'title', 'published' => 'status', 'default_for' => 'salaryrangetypes', 'default' => 'isdefault',
    'note' => __('per hour, per month, per year', 'wp-job-portal'),
    'search' => array('title', isset(wpjobportal::$_data['filter']['title']) ? wpjobportal::$_data['filter']['title'] : '', __('Search salary periods', 'wp-job-portal')),
));
