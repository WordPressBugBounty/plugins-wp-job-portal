<?php
if (!defined('ABSPATH')) die('Restricted Access');
/*
 * Jobs › Categories › Tags (design 4, Tags add-on).
 * Drawn by WPJOBPORTAL_Admin_Lookup_UI; the module's model loads the rows and its tasks do the work.
 */
WPJOBPORTAL_Admin_Lookup_UI::render(array(
    'page' => 'wpjobportal_tag', 'form' => 'formtag', 'nonce' => 'wpjobportal_tag_nonce', 'remove' => 'removetag',
    'title' => __('Tags', 'wp-job-portal'), 'noun' => __('tag', 'wp-job-portal'), 'nouns' => __('tags', 'wp-job-portal'), 'noun_title' => __('Tag', 'wp-job-portal'),
    'crumbs' => array(array(__('Jobs', 'wp-job-portal'), admin_url('admin.php?page=wpjobportal_job'))),
    'label' => 'tag', 'published' => 'status', 'ordering' => false,
    // 2.7 listed who each tag is for; the redesigned list had dropped it (tester report).
    'columns' => array(array(__('Tag For', 'wp-job-portal'), function ($row) {
        $wpjobportal_for = isset($row->tagfor) ? (int) $row->tagfor : 0;
        if ($wpjobportal_for === 1) { return esc_html__('Employer', 'wp-job-portal'); }
        if ($wpjobportal_for === 2) { return esc_html__('Job seeker', 'wp-job-portal'); }
        if ($wpjobportal_for === 3) { return esc_html__('Both', 'wp-job-portal'); }
        return '';
    })),
    'search' => array('searchname', isset(wpjobportal::$_data['filter']['searchname']) ? wpjobportal::$_data['filter']['searchname'] : '', __('Search tags', 'wp-job-portal')),
));
