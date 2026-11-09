<?php
if (!defined('ABSPATH')) die('Restricted Access');
/*
 * Jobs › Categories › Job types (design 4).
 * Drawn by WPJOBPORTAL_Admin_Lookup_UI; the module's model loads the rows and its tasks do the work.
 */
WPJOBPORTAL_Admin_Lookup_UI::render(array(
    'page' => 'wpjobportal_jobtype', 'form' => 'formjobtype', 'nonce' => 'wpjobportal_job_type_nonce',
    'title' => __('Job types', 'wp-job-portal'), 'noun' => __('job type', 'wp-job-portal'), 'nouns' => __('job types', 'wp-job-portal'), 'noun_title' => __('Job type', 'wp-job-portal'),
    'crumbs' => array(array(__('Jobs', 'wp-job-portal'), admin_url('admin.php?page=wpjobportal_job'))),
    'label' => 'title', 'published' => 'isactive', 'default_for' => 'jobtypes', 'default' => 'isdefault',
    'search' => array('title', isset(wpjobportal::$_data['filter']['title']) ? wpjobportal::$_data['filter']['title'] : '', __('Search job types', 'wp-job-portal')),
    'columns' => array(array(__('Colour', 'wp-job-portal'), function ($row) { return WPJOBPORTAL_Admin_Lookup_UI::swatch($row->color); })),
));
