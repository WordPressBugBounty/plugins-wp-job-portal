<?php
if (!defined('ABSPATH')) die('Restricted Access');
/*
 * Settings › Job attributes › Education (design 4).
 * Drawn by WPJOBPORTAL_Admin_Lookup_UI; the module's model loads the rows and its tasks do the work.
 */
WPJOBPORTAL_Admin_Lookup_UI::render(array(
    'page' => 'wpjobportal_highesteducation', 'form' => 'formhighesteducation', 'nonce' => 'wpjobportal_highest_education_nonce',
    'title' => __('Highest educations', 'wp-job-portal'), 'noun' => __('education level', 'wp-job-portal'), 'nouns' => __('education levels', 'wp-job-portal'), 'noun_title' => __('Education level', 'wp-job-portal'),
    'crumbs' => array(array(__('Settings', 'wp-job-portal'), admin_url('admin.php?page=wpjobportal_configuration&wpjobportallt=configurations')), array(__('Job attributes', 'wp-job-portal'), admin_url('admin.php?page=wpjobportal_jobstatus'))),
    'label' => 'title', 'published' => 'isactive', 'default_for' => 'heighesteducation', 'default' => 'isdefault',
    'search' => array('title', isset(wpjobportal::$_data['filter']['title']) ? wpjobportal::$_data['filter']['title'] : '', __('Search education levels', 'wp-job-portal')),
));
