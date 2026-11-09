<?php
if (!defined('ABSPATH')) die('Restricted Access');
/*
 * Settings › Job attributes › Career levels (design 4).
 * Drawn by WPJOBPORTAL_Admin_Lookup_UI; the module's model loads the rows and its tasks do the work.
 */
WPJOBPORTAL_Admin_Lookup_UI::render(array(
    'page' => 'wpjobportal_careerlevel', 'form' => 'formcareerlevels', 'nonce' => 'wpjobportal_careerlevel_nonce',
    'title' => __('Career levels', 'wp-job-portal'), 'noun' => __('career level', 'wp-job-portal'), 'nouns' => __('career levels', 'wp-job-portal'), 'noun_title' => __('Career level', 'wp-job-portal'),
    'crumbs' => array(array(__('Settings', 'wp-job-portal'), admin_url('admin.php?page=wpjobportal_configuration&wpjobportallt=configurations')), array(__('Job attributes', 'wp-job-portal'), admin_url('admin.php?page=wpjobportal_jobstatus'))),
    'label' => 'title', 'published' => 'status', 'default_for' => 'careerlevels', 'default' => 'isdefault',
    'search' => array('title', isset(wpjobportal::$_data['filter']['title']) ? wpjobportal::$_data['filter']['title'] : '', __('Search career levels', 'wp-job-portal')),
));
