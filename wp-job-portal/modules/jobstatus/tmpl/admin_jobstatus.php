<?php
if (!defined('ABSPATH')) die('Restricted Access');
/*
 * Settings › Job attributes › Job statuses (design 4).
 * Drawn by WPJOBPORTAL_Admin_Lookup_UI; the module's model loads the rows and its tasks do the work.
 */
WPJOBPORTAL_Admin_Lookup_UI::render(array(
    'page' => 'wpjobportal_jobstatus', 'form' => 'formjobstatus', 'nonce' => 'wpjobportal_job_status_nonce',
    'title' => __('Job status', 'wp-job-portal'), 'noun' => __('job status', 'wp-job-portal'), 'nouns' => __('job statuses', 'wp-job-portal'), 'noun_title' => __('Job status', 'wp-job-portal'),
    'crumbs' => array(array(__('Settings', 'wp-job-portal'), admin_url('admin.php?page=wpjobportal_configuration&wpjobportallt=configurations')), array(__('Job attributes', 'wp-job-portal'), admin_url('admin.php?page=wpjobportal_jobstatus'))),
    'columns' => array(array(__('Applications', 'wp-job-portal'), function ($row) {
        $wpjobportal_takes = !isset($row->acceptsapplications) || (int) $row->acceptsapplications === 1;
        return '<span class="wjpl-badge wjpl-b-' . ($wpjobportal_takes ? 'ok' : 'closed') . '">' . esc_html($wpjobportal_takes ? __('Accepted', 'wp-job-portal') : __('Not accepted', 'wp-job-portal')) . '</span>';
    })),
    'label' => 'title', 'published' => 'isactive', 'default_for' => 'jobstatus', 'default' => 'isdefault',
    'note' => __('the hiring stage shown to the job owner; visitors see only Accepting applications or Closed', 'wp-job-portal'),
    'search' => array('title', isset(wpjobportal::$_data['filter']['title']) ? wpjobportal::$_data['filter']['title'] : '', __('Search job statuses', 'wp-job-portal')),
));
