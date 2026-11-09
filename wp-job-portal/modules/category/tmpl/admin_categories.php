<?php
if (!defined('ABSPATH')) die('Restricted Access');
/*
 * Jobs › Categories (design 4).
 * Drawn by WPJOBPORTAL_Admin_Lookup_UI; the module's model loads the rows and its tasks do the work.
 */
WPJOBPORTAL_Admin_Lookup_UI::render(array(
    'page' => 'wpjobportal_category', 'form' => 'formcategory', 'nonce' => 'wpjobportal_category_nonce',
    'title' => __('Categories', 'wp-job-portal'), 'noun' => __('category', 'wp-job-portal'), 'nouns' => __('categories', 'wp-job-portal'), 'noun_title' => __('Category', 'wp-job-portal'),
    'crumbs' => array(array(__('Jobs', 'wp-job-portal'), admin_url('admin.php?page=wpjobportal_job'))),
    'label' => 'cat_title', 'published' => 'isactive', 'default_for' => 'categories', 'default' => 'isdefault',
    'search' => array('searchname', isset(wpjobportal::$_data['filter']['searchname']) ? wpjobportal::$_data['filter']['searchname'] : '', __('Search categories', 'wp-job-portal')),
));
