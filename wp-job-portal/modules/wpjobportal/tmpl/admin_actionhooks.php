<?php
if ( ! defined( 'ABSPATH' ) ) {
    die( 'Restricted Access' );
}

$hook_data = [
    'Jobs' => [
        [
            'id'          => 'after_store_job_hook',
            'addon'       => 'core',
            'addon_name'       => 'core',
            'name'        => __( 'After Store Job', 'wp-job-portal' ),
            'action'      => 'wpjobportal_after_store_job_hook',
            'type'        => 'action',
            'description' => __( 'Fires immediately after a new job is stored in the database.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$job_id', 'type' => 'int', 'desc' => __( 'The newly created job ID.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_after_store_job_hook', 'my_custom_store_job_action' );\nfunction my_custom_store_job_action( \$job_id ) {\n    // Logic after job creation\n}",
            'notes'       => __( 'Triggered upon job creation.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'after_edit_job_hook',
            'addon'       => 'core',
            'addon_name'       => 'core',
            'name'        => __( 'After Edit Job', 'wp-job-portal' ),
            'action'      => 'wpjobportal_after_edit_job_hook',
            'type'        => 'action',
            'description' => __( 'Fires immediately after an existing job is updated.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$job_id', 'type' => 'int', 'desc' => __( 'The updated job ID.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_after_edit_job_hook', 'my_custom_edit_job_action' );\nfunction my_custom_edit_job_action( \$job_id ) {\n    // Logic after job update\n}",
            'notes'       => __( 'Triggered upon job update.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'after_delete_job_hook',
            'addon'       => 'core',
            'addon_name'       => 'core',
            'name'        => __( 'After Delete Job', 'wp-job-portal' ),
            'action'      => 'wpjobportal_after_delete_job_hook',
            'type'        => 'action',
            'description' => __( 'Fires immediately after a job is deleted.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$job_id', 'type' => 'int', 'desc' => __( 'The deleted job ID.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_after_delete_job_hook', 'my_custom_delete_job_action' );\nfunction my_custom_delete_job_action( \$job_id ) {\n    // Logic after job deletion\n}",
            'notes'       => __( 'Triggered upon job deletion.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'job_published',
            'addon'       => 'core',
            'addon_name'       => 'core',
            'name'        => __( 'Job Published', 'wp-job-portal' ),
            'action'      => 'wpjobportal_job_published',
            'type'        => 'action',
            'description' => __( 'Fires when admin approved a job for publish from job listing. Essential for XML syndication to Indeed or LinkedIn.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$wpjobportal_id', 'type' => 'int', 'desc' => __( 'The job ID.', 'wp-job-portal' ) ],
                [ 'name' => '$companyid', 'type' => 'int', 'desc' => __( 'The associated company ID.', 'wp-job-portal' ) ],
                [ 'name' => '$job_data', 'type' => 'array', 'desc' => __( 'Full job data array.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_job_published', 'wpjb_syndicate_job', 10, 3 );\nfunction wpjb_syndicate_job( \$job_id, \$company_id, \$job_data ) {\n    // Push to XML feed\n}",
            'notes'       => __( 'Essential for XML syndication feeds.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'job_status_transition',
            'addon'       => 'core',
            'addon_name'       => 'core',
            'name'        => __( 'Job Status Transition', 'wp-job-portal' ),
            'action'      => 'wpjobportal_job_status_transition',
            'type'        => 'action',
            'description' => __( 'Fires when a job is approved or rejected by an admin from job que.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$wpjobportal_id', 'type' => 'int', 'desc' => __( 'The job ID.', 'wp-job-portal' ) ],
                [ 'name' => '$old_status', 'type' => 'int', 'desc' => __( 'Old status code.', 'wp-job-portal' ) ],
                [ 'name' => '$new_status', 'type' => 'string', 'desc' => __( 'New status string.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_job_status_transition', 'wpjb_on_job_status_change', 10, 3 );\nfunction wpjb_on_job_status_change( \$id, \$old, \$new ) {\n    // Status change logic\n}",
            'notes'       => __( 'Admin approval/rejection trigger.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'featured_job_activated',
            'addon'       => 'featuredjob',
            'addon_name'       => 'Featured Jobs',
            'name'        => __( 'Featured Job Activated', 'wp-job-portal' ),
            'action'      => 'wpjobportal_featured_job_activated',
            'type'        => 'action',
            'description' => __( 'Fires when a job is elevated to featured status.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$wpjobportal_id', 'type' => 'int', 'desc' => __( 'The job ID.', 'wp-job-portal' ) ],
                [ 'name' => '$uid', 'type' => 'int', 'desc' => __( 'User ID of the job owner.', 'wp-job-portal' ) ],
                [ 'name' => '$endfeatureddate', 'type' => 'string', 'desc' => __( 'Featured expiry date.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_featured_job_activated', 'wpjb_log_featured_job', 10, 3 );\nfunction wpjb_log_featured_job( \$id, \$uid, \$end_date ) {\n    // Logic here\n}",
            'notes'       => __( 'Triggered on featured upgrade.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'job_search_query_args',
            'addon'       => 'core',
            'addon_name'       => 'core',
            'name'        => __( 'Job Search Query Args', 'wp-job-portal' ),
            'action'      => 'wpjobportal_job_search_query_args',
            'type'        => 'filter',
            'description' => __( 'Modifies the underlying SQL query for job searches.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$wpjobportal_inquery', 'type' => 'string', 'desc' => __( 'The search SQL query.', 'wp-job-portal' ) ],
                [ 'name' => '$wpjobportal_vars', 'type' => 'array', 'desc' => __( 'Search filter variables.', 'wp-job-portal' ) ],
            ],
            'returns'     => [ 'type' => 'string', 'desc' => __( 'Modified SQL query string.', 'wp-job-portal' ) ],
            'example'     => "add_filter( 'wpjobportal_job_search_query_args', 'wpjb_custom_job_search', 10, 2 );\nfunction wpjb_custom_job_search( \$query, \$vars ) {\n    return \$query;\n}",
            'notes'       => __( 'Used for modifying frontend job search behaviour.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'google_job_schema',
            'addon'       => 'core',
            'addon_name'       => 'core',
            'name'        => __( 'Google Job Schema', 'wp-job-portal' ),
            'action'      => 'wpjobportal_google_job_schema_json_ld',
            'type'        => 'filter',
            'description' => __( 'Intercepts the structured SEO data array to inject dynamic properties (like baseSalary).', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$schema_array', 'type' => 'array', 'desc' => __( 'JSON-LD schema array.', 'wp-job-portal' ) ],
                [ 'name' => '$jobid', 'type' => 'int', 'desc' => __( 'Job ID.', 'wp-job-portal' ) ],
            ],
            'returns'     => [ 'type' => 'array', 'desc' => __( 'Modified JSON-LD array.', 'wp-job-portal' ) ],
            'example'     => "add_filter( 'wpjobportal_google_job_schema_json_ld', 'wpjb_add_salary_schema', 10, 2 );\nfunction wpjb_add_salary_schema( \$schema, \$job_id ) {\n    \$schema['baseSalary'] = '50000';\n    return \$schema;\n}",
            'notes'       => __( 'Crucial for SEO and Google Jobs integration.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'job_shortlisted',
            'addon'       => 'shortlist',
            'addon_name'       => 'Shortlist Job',
            'name'        => __( 'Job Shortlisted', 'wp-job-portal' ),
            'action'      => 'wpjobportal_job_shortlisted',
            'type'        => 'action',
            'description' => __( 'Candidate intent tracking for analytics when a job is saved or shortlisted.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$row_id', 'type' => 'int', 'desc' => __( 'Shortlist row ID.', 'wp-job-portal' ) ],
                [ 'name' => '$jobid', 'type' => 'int', 'desc' => __( 'Job ID.', 'wp-job-portal' ) ],
                [ 'name' => '$uid', 'type' => 'int', 'desc' => __( 'User ID.', 'wp-job-portal' ) ],
                [ 'name' => '$data', 'type' => 'array', 'desc' => __( 'Extra data array.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_job_shortlisted', 'wpjb_track_shortlist_intent', 10, 4 );\nfunction wpjb_track_shortlist_intent( \$row, \$job, \$uid, \$data ) {\n    // Analytics tracking\n}",
            'notes'       => __( 'Candidate intent tracking.', 'wp-job-portal' ),
        ],
    ],

    'Resumes' => [
        [
            'id'          => 'after_store_resume_hook',
            'addon'       => 'core',
            'addon_name'       => 'core',
            'name'        => __( 'After Store Resume', 'wp-job-portal' ),
            'action'      => 'wpjobportal_after_store_resume_hook',
            'type'        => 'action',
            'description' => __( 'Fires immediately after a new resume is stored in the database.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$resume_id', 'type' => 'int', 'desc' => __( 'The newly created resume ID.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_after_store_resume_hook', 'my_custom_store_resume_action' );\nfunction my_custom_store_resume_action( \$resume_id ) {\n    // Logic after resume creation\n}",
            'notes'       => __( 'Triggered upon resume creation.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'after_edit_resume_hook',
            'addon'       => 'core',
            'addon_name'       => 'core',
            'name'        => __( 'After Edit Resume', 'wp-job-portal' ),
            'action'      => 'wpjobportal_after_edit_resume_hook',
            'type'        => 'action',
            'description' => __( 'Fires immediately after an existing resume is updated.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$resume_id', 'type' => 'int', 'desc' => __( 'The updated resume ID.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_after_edit_resume_hook', 'my_custom_edit_resume_action' );\nfunction my_custom_edit_resume_action( \$resume_id ) {\n    // Logic after resume update\n}",
            'notes'       => __( 'Triggered upon resume update.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'after_delete_resume_hook',
            'addon'       => 'core',
            'addon_name'       => 'core',
            'name'        => __( 'After Delete Resume', 'wp-job-portal' ),
            'action'      => 'wpjobportal_after_delete_resume_hook',
            'type'        => 'action',
            'description' => __( 'Fires immediately after a resume is deleted.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$resume_id', 'type' => 'int', 'desc' => __( 'The deleted resume ID.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_after_delete_resume_hook', 'my_custom_delete_resume_action' );\nfunction my_custom_delete_resume_action( \$resume_id ) {\n    // Logic after resume deletion\n}",
            'notes'       => __( 'Triggered upon resume deletion.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'resume_status_transition',
            'addon'       => 'core',
            'addon_name'       => 'core',
            'name'        => __( 'Resume Status Transition', 'wp-job-portal' ),
            'action'      => 'wpjobportal_resume_status_transition',
            'type'        => 'action',
            'description' => __( 'Fires when an administrator manually approves or rejects a resume.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$wpjobportal_id', 'type' => 'int', 'desc' => __( 'The ID of the resume.', 'wp-job-portal' ) ],
                [ 'name' => '$old_status', 'type' => 'string', 'desc' => __( 'The previous status.', 'wp-job-portal' ) ],
                [ 'name' => '$new_status', 'type' => 'string', 'desc' => __( 'The new status.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_resume_status_transition', 'wpjb_notify_on_resume_approval', 10, 3 );\nfunction wpjb_notify_on_resume_approval( \$id, \$old_status, \$new_status ) {\n    if ( \$new_status === 'approved' ) {\n        // Notification logic\n    }\n}",
            'notes'       => __( 'Track manual admin approvals/rejections.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'resume_search_query_args',
            'addon'       => 'resumesearch',
            'addon_name'       => 'Resume Search',
            'name'        => __( 'Resume Search Query Args', 'wp-job-portal' ),
            'action'      => 'wpjobportal_resume_search_query_args',
            'type'        => 'filter',
            'description' => __( 'Modifies the SQL WHERE clause during a frontend resume search, allowing Elasticsearch integrations.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$wpjobportal_inquery', 'type' => 'string', 'desc' => __( 'The current SQL query string.', 'wp-job-portal' ) ],
                [ 'name' => '$search_args', 'type' => 'array', 'desc' => __( 'Array of applied search filters.', 'wp-job-portal' ) ],
            ],
            'returns'     => [ 'type' => 'string', 'desc' => __( 'Modified SQL query string.', 'wp-job-portal' ) ],
            'example'     => "add_filter( 'wpjobportal_resume_search_query_args', 'wpjb_custom_resume_search', 10, 2 );\nfunction wpjb_custom_resume_search( \$query, \$args ) {\n    // Append custom SQL clauses\n    return \$query;\n}",
            'notes'       => __( 'Allows Elasticsearch integrations.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'featured_resume_activated',
            'addon'       => 'featureresume',
            'addon_name'       => 'Featured Resumes',
            'name'        => __( 'Featured Resume Activated', 'wp-job-portal' ),
            'action'      => 'wpjobportal_featured_resume_activated',
            'type'        => 'action',
            'description' => __( 'Fires when a resume is promoted to featured status.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$id', 'type' => 'int', 'desc' => __( 'Resume ID.', 'wp-job-portal' ) ],
                [ 'name' => '$uid', 'type' => 'int', 'desc' => __( 'Job Seeker User ID.', 'wp-job-portal' ) ],
                [ 'name' => '$end_date', 'type' => 'string', 'desc' => __( 'Featured expiry date.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_featured_resume_activated', 'wpjb_featured_resume', 10, 3 );\nfunction wpjb_featured_resume( \$id, \$uid, \$end ) {\n    // Logic\n}",
            'notes'       => __( 'Triggered on featured upgrade.', 'wp-job-portal' ),
        ],
    ],

    'Applications & ATS Workflow' => [
        [
            'id'          => 'after_job_apply_hook',
            'addon'       => 'core',
            'addon_name'       => 'core',
            'name'        => __( 'After Job Apply', 'wp-job-portal' ),
            'action'      => 'wpjobportal_after_job_apply_hook',
            'type'        => 'action',
            'description' => __( 'Fires immediately after a candidate successfully applies for a job.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$apply_id', 'type' => 'int', 'desc' => __( 'The job application ID.', 'wp-job-portal' ) ],
                [ 'name' => '$job_id', 'type' => 'int', 'desc' => __( 'The job ID.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_after_job_apply_hook', 'my_custom_after_apply_action', 10, 2 );\nfunction my_custom_after_apply_action( \$apply_id, \$job_id ) {\n    // Logic after job application\n}",
            'notes'       => __( 'Triggered upon successful job application.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'app_submitted',
            'addon'       => 'core',
            'addon_name'       => 'core',
            'name'        => __( 'Application Submitted', 'wp-job-portal' ),
            'action'      => 'wpjobportal_application_submitted',
            'type'        => 'action',
            'description' => __( 'Standard event trigger for a logged-in user submitting a job application.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$wpjobportal_job_apply_id', 'type' => 'int', 'desc' => __( 'ID of the application record.', 'wp-job-portal' ) ],
                [ 'name' => '$wpjobportal_jobid', 'type' => 'int', 'desc' => __( 'ID of the applied job.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_application_submitted', 'wpjb_log_application', 10, 2 );\nfunction wpjb_log_application( \$apply_id, \$job_id ) {\n    error_log( 'New application: ' . \$apply_id );\n}",
            'notes'       => __( 'Fires for authenticated users only.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'quick_apply_submitted',
            'addon'       => 'core',
            'name'        => __( 'Quick Apply Submitted', 'wp-job-portal' ),
            'action'      => 'wpjobportal_quick_apply_submitted',
            'type'        => 'action',
            'description' => __( 'Triggers when a candidate uses the "Quick Apply" bypass form.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$wpjobportal_job_apply_id', 'type' => 'int', 'desc' => __( 'Application record ID.', 'wp-job-portal' ) ],
                [ 'name' => '$wpjobportal_jobid', 'type' => 'int', 'desc' => __( 'Job ID.', 'wp-job-portal' ) ],
                [ 'name' => '$wpjobportal_data', 'type' => 'array', 'desc' => __( 'Submitted form data.', 'wp-job-portal' ) ],
                [ 'name' => '$_FILES', 'type' => 'array', 'desc' => __( 'Uploaded files array.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_quick_apply_submitted', 'wpjb_handle_quick_apply', 10, 4 );\nfunction wpjb_handle_quick_apply( \$apply_id, \$job_id, \$data, \$files ) {\n    // Custom quick apply parsing\n}",
            'notes'       => __( 'Handles bypass forms and attached files.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'visitor_app_submitted',
            'addon'       => 'visitorapplyjob',
            'addon_name'       => 'Visitor Can Apply Job',
            'name'        => __( 'Visitor Application Submitted', 'wp-job-portal' ),
            'action'      => 'wpjobportal_visitor_application_submitted',
            'type'        => 'action',
            'description' => __( 'Triggers specifically for unauthenticated (guest) applications.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$wpjobportal_job_apply_id', 'type' => 'int', 'desc' => __( 'Application record ID.', 'wp-job-portal' ) ],
                [ 'name' => '$wpjobportal_jobid', 'type' => 'int', 'desc' => __( 'Job ID.', 'wp-job-portal' ) ],
                [ 'name' => '$wpjobportal_data', 'type' => 'array', 'desc' => __( 'Guest data array.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_visitor_application_submitted', 'wpjb_guest_application_check', 10, 3 );\nfunction wpjb_guest_application_check( \$apply_id, \$job_id, \$data ) {\n    // Guest logic\n}",
            'notes'       => __( 'Triggers specifically for unauthenticated applications.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'candidate_status_updated',
            'addon'       => 'resumeaction',
            'addon_name'       => 'Resume Actions',
            'name'        => __( 'Candidate Status Updated', 'wp-job-portal' ),
            'action'      => 'wpjobportal_candidate_status_updated',
            'type'        => 'action',
            'description' => __( 'Core ATS hook. Fires when an employer changes an applicants internal workflow state (e.g., Hired).', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$resumeid', 'type' => 'int', 'desc' => __( 'Resume ID.', 'wp-job-portal' ) ],
                [ 'name' => '$old_status', 'type' => 'string', 'desc' => __( 'Old ATS status.', 'wp-job-portal' ) ],
                [ 'name' => '$new_status', 'type' => 'string', 'desc' => __( 'New ATS status.', 'wp-job-portal' ) ],
                [ 'name' => '$employer_id', 'type' => 'int', 'desc' => __( 'Employer User ID.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_candidate_status_updated', 'wpjb_ats_webhook', 10, 4 );\nfunction wpjb_ats_webhook( \$id, \$old, \$new, \$emp_id ) {\n    // Zapier webhook\n}",
            'notes'       => __( 'Core ATS hook.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'application_note_saved',
            'addon'       => 'resumeaction',
            'addon_name'       => 'Resume Actions',
            'name'        => __( 'Application Note Saved', 'wp-job-portal' ),
            'action'      => 'wpjobportal_application_note_saved',
            'type'        => 'action',
            'description' => __( 'Fires when an employer adds an internal note to an application.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$resumeid', 'type' => 'int', 'desc' => __( 'Resume ID.', 'wp-job-portal' ) ],
                [ 'name' => '$comment', 'type' => 'string', 'desc' => __( 'Note text.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_application_note_saved', 'wpjb_log_note', 10, 2 );\nfunction wpjb_log_note( \$id, \$note ) {\n    // Logic\n}",
            'notes'       => __( 'Fires on employer internal note.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'application_rating_updated',
            'addon'       => 'resumeaction',
            'addon_name'       => 'Resume Actions',
            'name'        => __( 'Application Rating Updated', 'wp-job-portal' ),
            'action'      => 'wpjobportal_application_rating_updated',
            'type'        => 'action',
            'description' => __( 'Fires when a candidate receives a star rating from an employer.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$jobapplyid', 'type' => 'int', 'desc' => __( 'Application ID.', 'wp-job-portal' ) ],
                [ 'name' => '$rate', 'type' => 'int', 'desc' => __( 'Star rating.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_application_rating_updated', 'wpjb_log_rating', 10, 2 );\nfunction wpjb_log_rating( \$id, \$rate ) {\n    // Logic\n}",
            'notes'       => __( 'Fires on rating update.', 'wp-job-portal' ),
        ],

    ],

    'Companies' => [
        [
            'id'          => 'after_store_company_hook',
            'addon'       => 'core',
            'addon_name'       => 'core',
            'name'        => __( 'After Store Company', 'wp-job-portal' ),
            'action'      => 'wpjobportal_after_store_company_hook',
            'type'        => 'action',
            'description' => __( 'Fires immediately after a new company is stored in the database.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$company_id', 'type' => 'int', 'desc' => __( 'The newly created company ID.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_after_store_company_hook', 'my_custom_store_company_action' );\nfunction my_custom_store_company_action( \$company_id ) {\n    // Logic after company creation\n}",
            'notes'       => __( 'Triggered upon company creation.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'after_edit_company_hook',
            'addon'       => 'core',
            'addon_name'       => 'core',
            'name'        => __( 'After Edit Company', 'wp-job-portal' ),
            'action'      => 'wpjobportal_after_edit_company_hook',
            'type'        => 'action',
            'description' => __( 'Fires immediately after an existing company is updated.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$company_id', 'type' => 'int', 'desc' => __( 'The updated company ID.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_after_edit_company_hook', 'my_custom_edit_company_action' );\nfunction my_custom_edit_company_action( \$company_id ) {\n    // Logic after company update\n}",
            'notes'       => __( 'Triggered upon company update.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'after_delete_company_hook',
            'addon'       => 'core',
            'addon_name'       => 'core',
            'name'        => __( 'After Delete Company', 'wp-job-portal' ),
            'action'      => 'wpjobportal_after_delete_company_hook',
            'type'        => 'action',
            'description' => __( 'Fires immediately after a company is deleted.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$company_id', 'type' => 'int', 'desc' => __( 'The deleted company ID.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_after_delete_company_hook', 'my_custom_delete_company_action' );\nfunction my_custom_delete_company_action( \$company_id ) {\n    // Logic after company deletion\n}",
            'notes'       => __( 'Triggered upon company deletion.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'company_status_transition',
            'addon'       => 'core',
            'addon_name'       => 'core',
            'name'        => __( 'Company Status Transition', 'wp-job-portal' ),
            'action'      => 'wpjobportal_company_status_transition',
            'type'        => 'action',
            'description' => __( 'Fires on company approve/reject state changes.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$wpjobportal_id', 'type' => 'int', 'desc' => __( 'Company ID.', 'wp-job-portal' ) ],
                [ 'name' => '$old_status', 'type' => 'string', 'desc' => __( 'Old status.', 'wp-job-portal' ) ],
                [ 'name' => '$new_status', 'type' => 'string', 'desc' => __( 'New status.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_company_status_transition', 'wpjb_company_status_change', 10, 3 );\nfunction wpjb_company_status_change( \$id, \$old, \$new ) {\n    // Logic\n}",
            'notes'       => __( 'Fires on company state changes.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'featured_company_activated',
            'addon'       => 'featuredcompany',
            'addon_name'       => 'Featured Companies',
            'name'        => __( 'Featured Company Activated', 'wp-job-portal' ),
            'action'      => 'wpjobportal_featured_company_activated',
            'type'        => 'action',
            'description' => __( 'Fires when a company is promoted to featured status.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$id', 'type' => 'int', 'desc' => __( 'Company ID.', 'wp-job-portal' ) ],
                [ 'name' => '$uid', 'type' => 'int', 'desc' => __( 'Employer User ID.', 'wp-job-portal' ) ],
                [ 'name' => '$end_date', 'type' => 'string', 'desc' => __( 'Featured expiry date.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_featured_company_activated', 'wpjb_featured_company', 10, 3 );\nfunction wpjb_featured_company( \$id, \$uid, \$end ) {\n    // Logic\n}",
            'notes'       => __( 'Triggered on featured upgrade.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'company_search_query_args',
            'addon'       => 'allcompanies',
            'addon_name'       => 'All Companies',
            'name'        => __( 'Company Search Query Args', 'wp-job-portal' ),
            'action'      => 'wpjobportal_company_search_query_args',
            'type'        => 'filter',
            'description' => __( 'Modifies Company SQL search queries.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$inquery', 'type' => 'string', 'desc' => __( 'Current SQL query.', 'wp-job-portal' ) ],
                [ 'name' => '$search_filters', 'type' => 'array', 'desc' => __( 'Search filters.', 'wp-job-portal' ) ],
            ],
            'returns'     => [ 'type' => 'string', 'desc' => __( 'Modified query string.', 'wp-job-portal' ) ],
            'example'     => "add_filter( 'wpjobportal_company_search_query_args', 'wpjb_custom_company_search', 10, 2 );\nfunction wpjb_custom_company_search( \$query, \$filters ) {\n    return \$query;\n}",
            'notes'       => __( 'Used for modifying frontend company search behaviour.', 'wp-job-portal' ),
        ],
    ],

    'Users & Accounts' => [
        [
            'id'          => 'user_status_transition',
            'addon'       => 'core',
            'addon_name'       => 'core',
            'name'        => __( 'User Status Transition', 'wp-job-portal' ),
            'action'      => 'wpjobportal_user_status_transition',
            'type'        => 'action',
            'description' => __( 'Fires when an admin manually suspends or activates an account.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$wpjobportal_userid', 'type' => 'int', 'desc' => __( 'User ID.', 'wp-job-portal' ) ],
                [ 'name' => '$old_status', 'type' => 'string', 'desc' => __( 'Old status.', 'wp-job-portal' ) ],
                [ 'name' => '$new_status', 'type' => 'string', 'desc' => __( 'New status.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_user_status_transition', 'wpjb_suspend_user_logic', 10, 3 );\nfunction wpjb_suspend_user_logic( \$user_id, \$old, \$new ) {\n    // Suspend logic\n}",
            'notes'       => __( 'Admin account activation/suspension.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'user_deleted',
            'addon'       => 'core',
            'addon_name'       => 'core',
            'name'        => __( 'User Deleted', 'wp-job-portal' ),
            'action'      => 'wpjobportal_user_deleted',
            'type'        => 'action',
            'description' => __( 'Crucial for GDPR compliance; alerts external systems to delete user records.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$wpjobportal_uid', 'type' => 'int', 'desc' => __( 'Portal User ID.', 'wp-job-portal' ) ],
                [ 'name' => '$wp_uid', 'type' => 'int', 'desc' => __( 'WordPress User ID.', 'wp-job-portal' ) ],
                [ 'name' => '$wpjobportal_roleid', 'type' => 'int', 'desc' => __( 'User Role ID.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_user_deleted', 'wpjb_gdpr_delete', 10, 3 );\nfunction wpjb_gdpr_delete( \$uid, \$wp_uid, \$role_id ) {\n    // GDPR external wipe\n}",
            'notes'       => __( 'Crucial for GDPR compliance.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'user_profile_saved',
            'addon'       => 'core',
            'addon_name'       => 'core',
            'name'        => __( 'User Profile Saved', 'wp-job-portal' ),
            'action'      => 'wpjobportal_user_profile_saved',
            'type'        => 'action',
            'description' => __( 'Fires whenever user metadata is updated.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$id', 'type' => 'int', 'desc' => __( 'Profile ID.', 'wp-job-portal' ) ],
                [ 'name' => '$wpjobportal_data', 'type' => 'array', 'desc' => __( 'Profile metadata.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_user_profile_saved', 'wpjb_profile_sync', 10, 2 );\nfunction wpjb_profile_sync( \$id, \$data ) {\n    // Sync metadata\n}",
            'notes'       => __( 'Fires on metadata update.', 'wp-job-portal' ),
        ],
    ],

    'AI & Match Engine' => [
        [
            'id'          => 'ai_generation_prompt',
            'addon'       => 'core',
            'addon_name'       => 'core',
            'name'        => __( 'AI Content Prompt', 'wp-job-portal' ),
            'action'      => 'wpjobportal_ai_content_generation_prompt',
            'type'        => 'filter',
            'description' => __( 'Intercepts the prompt string before it hits the AI proxy. Allows insertion of mandatory EEO text.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$wpjobportal_prompt', 'type' => 'string', 'desc' => __( 'AI Prompt.', 'wp-job-portal' ) ],
                [ 'name' => '$ai_parameters', 'type' => 'array', 'desc' => __( 'Context parameters.', 'wp-job-portal' ) ],
            ],
            'returns'     => [ 'type' => 'string', 'desc' => __( 'Modified AI Prompt.', 'wp-job-portal' ) ],
            'example'     => "add_filter( 'wpjobportal_ai_content_generation_prompt', 'wpjb_inject_eeo_prompt', 10, 2 );\nfunction wpjb_inject_eeo_prompt( \$prompt, \$params ) {\n    return \$prompt . ' Include EEO statement.';\n}",
            'notes'       => __( 'Allows insertion of mandatory context.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'ai_generation_completed',
            'addon'       => 'core',
            'addon_name'       => 'core',
            'name'        => __( 'AI Generation Completed', 'wp-job-portal' ),
            'action'      => 'wpjobportal_ai_content_generation_completed',
            'type'        => 'action',
            'description' => __( 'Captures the AI response for logging and plagiarism scanning.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$status', 'type' => 'int', 'desc' => __( 'Status code.', 'wp-job-portal' ) ],
                [ 'name' => '$generated_text', 'type' => 'string', 'desc' => __( 'Response payload.', 'wp-job-portal' ) ],
                [ 'name' => '$wpjobportal_prompt', 'type' => 'string', 'desc' => __( 'Original Prompt.', 'wp-job-portal' ) ],
                [ 'name' => '$wrapper_code', 'type' => 'string', 'desc' => __( 'Wrapper used.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_ai_content_generation_completed', 'wpjb_log_ai_response', 10, 4 );\nfunction wpjb_log_ai_response( \$status, \$text, \$prompt, \$wrapper ) {\n    // Log response\n}",
            'notes'       => __( 'Used for logging and plagiarism checks.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'ai_resume_parse',
            'addon'       => 'airesumesearch',
            'addon_name'       => 'AI Resume Search',
            'name'        => __( 'Before AI Resume Parse', 'wp-job-portal' ),
            'action'      => 'wpjobportal_before_ai_resume_parse_main',
            'type'        => 'filter',
            'description' => __( 'Allows PII redaction from resume text before it is indexed for AI matching.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$ai_string', 'type' => 'string', 'desc' => __( 'Text string for AI indexing.', 'wp-job-portal' ) ],
                [ 'name' => '$id', 'type' => 'int', 'desc' => __( 'Entity ID.', 'wp-job-portal' ) ],
            ],
            'returns'     => [ 'type' => 'string', 'desc' => __( 'Sanitized AI string.', 'wp-job-portal' ) ],
            'example'     => "add_filter( 'wpjobportal_before_ai_resume_parse_main', 'wpjb_redact_pii', 10, 2 );\nfunction wpjb_redact_pii( \$string, \$id ) {\n    // Redact emails/phones\n    return \$string;\n}",
            'notes'       => __( 'Useful for Blind Hiring features.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'ai_job_parse',
            'addon'       => 'aijobsearch',
            'addon_name'       => 'AI Job Search',
            'name'        => __( 'Before AI Job Parse', 'wp-job-portal' ),
            'action'      => 'wpjobportal_before_ai_job_parse_main',
            'type'        => 'filter',
            'description' => __( 'Allows modification of job text before AI indexing.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$ai_string', 'type' => 'string', 'desc' => __( 'Text string for AI indexing.', 'wp-job-portal' ) ],
                [ 'name' => '$id', 'type' => 'int', 'desc' => __( 'Job ID.', 'wp-job-portal' ) ],
            ],
            'returns'     => [ 'type' => 'string', 'desc' => __( 'Modified AI string.', 'wp-job-portal' ) ],
            'example'     => "add_filter( 'wpjobportal_before_ai_job_parse_main', 'wpjb_modify_job_ai', 10, 2 );\nfunction wpjb_modify_job_ai( \$string, \$id ) {\n    return \$string;\n}",
            'notes'       => __( 'Allows modification of job text before AI indexing.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'ai_job_to_resume_match',
            'addon'       => 'aisuggestedresumes',
            'addon_name'       => 'AI Suggested Resumes',
            'name'        => __( 'AI Job to Resume Match', 'wp-job-portal' ),
            'action'      => 'wpjobportal_ai_job_to_resume_match_string',
            'type'        => 'filter',
            'description' => __( 'Mutates the search string the AI uses to find resumes for a job.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$job_ai_string', 'type' => 'string', 'desc' => __( 'Search string.', 'wp-job-portal' ) ],
                [ 'name' => '$jobid', 'type' => 'int', 'desc' => __( 'Job ID.', 'wp-job-portal' ) ],
            ],
            'returns'     => [ 'type' => 'string', 'desc' => __( 'Mutated search string.', 'wp-job-portal' ) ],
            'example'     => "add_filter( 'wpjobportal_ai_job_to_resume_match_string', 'wpjb_match_mutator', 10, 2 );\nfunction wpjb_match_mutator( \$string, \$id ) {\n    return \$string;\n}",
            'notes'       => __( 'Mutates the search string the AI uses.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'ai_resume_to_job_match',
            'addon'       => 'aisuggestedjobs',
            'addon_name'       => 'AI Suggested Jobs',
            'name'        => __( 'AI Resume to Job Match', 'wp-job-portal' ),
            'action'      => 'wpjobportal_ai_resume_to_job_match_string',
            'type'        => 'filter',
            'description' => __( 'Mutates the search string the AI uses to find jobs for a resume.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$resume_ai_string', 'type' => 'string', 'desc' => __( 'Search string.', 'wp-job-portal' ) ],
                [ 'name' => '$resumeid', 'type' => 'int', 'desc' => __( 'Resume ID.', 'wp-job-portal' ) ],
            ],
            'returns'     => [ 'type' => 'string', 'desc' => __( 'Mutated search string.', 'wp-job-portal' ) ],
            'example'     => "add_filter( 'wpjobportal_ai_resume_to_job_match_string', 'wpjb_resume_match_mutator', 10, 2 );\nfunction wpjb_resume_match_mutator( \$string, \$id ) {\n    return \$string;\n}",
            'notes'       => __( 'Mutates the search string the AI uses.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'ai_dashboard_job_suggestions',
            'addon'       => 'aisuggestedjobs',
            'addon_name'       => 'AI Suggested Jobs',
            'name'        => __( 'AI Dashboard Job Suggestions', 'wp-job-portal' ),
            'action'      => 'wpjobportal_ai_dashboard_job_suggestions_calculated',
            'type'        => 'action',
            'description' => __( 'Captures suggested jobs calculated for the job seekers dashboard.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$seeker_uid', 'type' => 'int', 'desc' => __( 'Seeker User ID.', 'wp-job-portal' ) ],
                [ 'name' => '$job_ids', 'type' => 'array', 'desc' => __( 'Array of matched Job IDs.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_ai_dashboard_job_suggestions_calculated', 'wpjb_cache_suggestions', 10, 2 );\nfunction wpjb_cache_suggestions( \$uid, \$ids ) {\n    // Cache results\n}",
            'notes'       => __( 'Captures dashboard suggestions.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'ai_job_suggestion_calculated',
            'addon'       => 'AI Suggested Jobs',
            'addon_name'       => 'aisuggestedjobs',
            'name'        => __( 'AI Job Suggestion Calculated', 'wp-job-portal' ),
            'action'      => 'wpjobportal_ai_job_suggestion_calculated',
            'type'        => 'action',
            'description' => __( 'Captures suggested jobs against a specific resume ID.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$resumeid', 'type' => 'int', 'desc' => __( 'Resume ID.', 'wp-job-portal' ) ],
                [ 'name' => '$job_id_array', 'type' => 'array', 'desc' => __( 'Array of matched Job IDs.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_ai_job_suggestion_calculated', 'wpjb_log_suggestions', 10, 2 );\nfunction wpjb_log_suggestions( \$resumeid, \$ids ) {\n    // Logic\n}",
            'notes'       => __( 'Captures resume-specific suggestions.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'ai_dashboard_resume_suggestions',
            'addon'       => 'aisuggestedresumes',
            'addon_name'       => 'AI Suggested Resumes',
            'name'        => __( 'AI Dashboard Resume Suggestions', 'wp-job-portal' ),
            'action'      => 'wpjobportal_ai_dashboard_resume_suggestions_calculated',
            'type'        => 'action',
            'description' => __( 'Captures suggested resumes calculated for the employers dashboard.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$employer_uid', 'type' => 'int', 'desc' => __( 'Employer User ID.', 'wp-job-portal' ) ],
                [ 'name' => '$resume_ids', 'type' => 'array', 'desc' => __( 'Array of matched Resume IDs.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_ai_dashboard_resume_suggestions_calculated', 'wpjb_employer_cache', 10, 2 );\nfunction wpjb_employer_cache( \$uid, \$ids ) {\n    // Logic\n}",
            'notes'       => __( 'Captures employer dashboard suggestions.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'ai_resume_suggestion_calculated',
            'addon'       => 'aisuggestedresumes',
            'addon_name'       => 'AI Suggested Resumes',
            'name'        => __( 'AI Resume Suggestion Calculated', 'wp-job-portal' ),
            'action'      => 'wpjobportal_ai_resume_suggestion_calculated',
            'type'        => 'action',
            'description' => __( 'Captures suggested resumes against a specific job ID.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$job_id', 'type' => 'int', 'desc' => __( 'Job ID.', 'wp-job-portal' ) ],
                [ 'name' => '$resume_id_array', 'type' => 'array', 'desc' => __( 'Array of matched Resume IDs.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_ai_resume_suggestion_calculated', 'wpjb_job_match_log', 10, 2 );\nfunction wpjb_job_match_log( \$job_id, \$ids ) {\n    // Logic\n}",
            'notes'       => __( 'Captures job-specific suggestions.', 'wp-job-portal' ),
        ],
    ],

    'Job Alerts' => [
        [
            'id'          => 'job_alert_triggered',
            'addon'       => 'jobalert',
            'addon_name'       => 'Job Alert',
            'name'        => __( 'Job Alert Triggered', 'wp-job-portal' ),
            'action'      => 'wpjobportal_job_alert_triggered',
            'type'        => 'action',
            'description' => __( 'Intercepts an alert match before the email sends. Ideal for Push/SMS notifications.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$person_id', 'type' => 'int', 'desc' => __( 'Person ID.', 'wp-job-portal' ) ],
                [ 'name' => '$subscriber_uid', 'type' => 'int', 'desc' => __( 'Subscriber User ID.', 'wp-job-portal' ) ],
                [ 'name' => '$jobs', 'type' => 'array', 'desc' => __( 'Matched jobs array.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_job_alert_triggered', 'wpjb_send_sms_alert', 10, 3 );\nfunction wpjb_send_sms_alert( \$pid, \$uid, \$jobs ) {\n    // Twilio SMS logic\n}",
            'notes'       => __( 'Ideal for Push/SMS notifications.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'job_alert_saved',
            'addon'       => 'jobalert',
            'addon_name'       => 'Job Alert',
            'name'        => __( 'Job Alert Saved', 'wp-job-portal' ),
            'action'      => 'wpjobportal_job_alert_saved',
            'type'        => 'action',
            'description' => __( 'Fires when an alert preference is created or updated.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$id', 'type' => 'int', 'desc' => __( 'Alert record ID.', 'wp-job-portal' ) ],
                [ 'name' => '$contactemail', 'type' => 'string', 'desc' => __( 'Subscriber Email.', 'wp-job-portal' ) ],
                [ 'name' => '$data', 'type' => 'array', 'desc' => __( 'Alert configuration data.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_job_alert_saved', 'wpjb_sync_mailchimp', 10, 3 );\nfunction wpjb_sync_mailchimp( \$id, \$email, \$data ) {\n    // Mailchimp logic\n}",
            'notes'       => __( 'Fires on alert create/update.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'job_alert_unsubscribed',
            'addon'       => 'jobalert',
            'addon_name'       => 'Job Alert',
            'name'        => __( 'Job Alert Unsubscribed', 'wp-job-portal' ),
            'action'      => 'wpjobportal_job_alert_unsubscribed',
            'type'        => 'action',
            'description' => __( 'Fires when an alert is deleted.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$alertid', 'type' => 'int', 'desc' => __( 'Deleted Alert ID.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_job_alert_unsubscribed', 'wpjb_alert_cleanup', 10, 1 );\nfunction wpjb_alert_cleanup( \$id ) {\n    // Cleanup logic\n}",
            'notes'       => __( 'Fires when an alert is deleted.', 'wp-job-portal' ),
        ],
    ],

    'E-Commerce & Subscriptions' => [
        [
            'id'          => 'membership_purchased',
            'addon'       => 'credits',
            'addon_name'       => 'Credits',
            'name'        => __( 'Membership Package Purchased', 'wp-job-portal' ),
            'action'      => 'wpjobportal_membership_package_purchased',
            'type'        => 'action',
            'description' => __( 'Triggers on a successful checkout via Stripe or PayPal gateways.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$uid', 'type' => 'int', 'desc' => __( 'User ID.', 'wp-job-portal' ) ],
                [ 'name' => '$package_id', 'type' => 'int', 'desc' => __( 'Package ID.', 'wp-job-portal' ) ],
                [ 'name' => '$amount', 'type' => 'float', 'desc' => __( 'Amount paid.', 'wp-job-portal' ) ],
                [ 'name' => '$gateway', 'type' => 'string', 'desc' => __( 'Payment gateway.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_membership_package_purchased', 'wpjb_assign_badge', 10, 4 );\nfunction wpjb_assign_badge( \$uid, \$pkg, \$amt, \$gw ) {\n    // Unlock features\n}",
            'notes'       => __( 'Triggers on gateway success.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'woo_order_synced',
            'addon'       => 'credits',
            'addon_name'       => 'Credits',
            'name'        => __( 'WooCommerce Order Synced', 'wp-job-portal' ),
            'action'      => 'wpjobportal_woocommerce_order_synced',
            'type'        => 'action',
            'description' => __( 'Executes when WP Job Portal routes a payment into the WooCommerce cart.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$postid', 'type' => 'int', 'desc' => __( 'WooCommerce Post ID.', 'wp-job-portal' ) ],
                [ 'name' => '$uid', 'type' => 'int', 'desc' => __( 'User ID.', 'wp-job-portal' ) ],
                [ 'name' => '$data', 'type' => 'array', 'desc' => __( 'Purchase data.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_woocommerce_order_synced', 'wpjb_woo_sync_log', 10, 3 );\nfunction wpjb_woo_sync_log( \$id, \$uid, \$data ) {\n    // Logic\n}",
            'notes'       => __( 'Executes on Woo cart route.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'per_listing_purchased',
            'addon'       => 'credits',
            'addon_name'       => 'Credits',
            'name'        => __( 'Per Listing Purchased', 'wp-job-portal' ),
            'action'      => 'wpjobportal_per_listing_purchased',
            'type'        => 'action',
            'description' => __( 'Generic action deployed across all individual purchases.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$uid', 'type' => 'int', 'desc' => __( 'User ID.', 'wp-job-portal' ) ],
                [ 'name' => '$entity_id', 'type' => 'int', 'desc' => __( 'Entity ID (job/resume/company).', 'wp-job-portal' ) ],
                [ 'name' => '$price', 'type' => 'float', 'desc' => __( 'Amount paid.', 'wp-job-portal' ) ],
                [ 'name' => '$type', 'type' => 'string', 'desc' => __( 'Entity type.', 'wp-job-portal' ) ],
                [ 'name' => '$gateway', 'type' => 'string', 'desc' => __( 'Gateway used.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_per_listing_purchased', 'wpjb_listing_log', 10, 5 );\nfunction wpjb_listing_log( \$uid, \$eid, \$pr, \$ty, \$gw ) {\n    // Logic\n}",
            'notes'       => __( 'Deployed across individual purchases.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'subscription_cancelled',
            'addon'       => 'credits',
            'addon_name'       => 'Credits',
            'name'        => __( 'Subscription Cancelled', 'wp-job-portal' ),
            'action'      => 'wpjobportal_subscription_cancelled',
            'type'        => 'action',
            'description' => __( 'Alerts external platforms (like Discord/Active Directory) to downgrade the user.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$subscription_id', 'type' => 'string', 'desc' => __( 'Gateway sub ID.', 'wp-job-portal' ) ],
                [ 'name' => '$uid', 'type' => 'int', 'desc' => __( 'User ID.', 'wp-job-portal' ) ],
                [ 'name' => '$gateway', 'type' => 'string', 'desc' => __( 'Payment gateway.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_subscription_cancelled', 'wpjb_downgrade_user', 10, 3 );\nfunction wpjb_downgrade_user( \$sub_id, \$uid, \$gw ) {\n    // Discord API call\n}",
            'notes'       => __( 'Downgrade user permissions.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'membership_purchased_db_commit',
            'addon'       => 'credits',
            'addon_name'       => 'Credits',
            'name'        => __( 'Membership Purchased DB Commit', 'wp-job-portal' ),
            'action'      => 'wpjobportal_membership_package_purchased_db_commit',
            'type'        => 'action',
            'description' => __( 'A database-level failsafe hook that fires even if a package is manually assigned by an admin.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$uid', 'type' => 'int', 'desc' => __( 'User ID.', 'wp-job-portal' ) ],
                [ 'name' => '$packageid', 'type' => 'int', 'desc' => __( 'Package ID.', 'wp-job-portal' ) ],
                [ 'name' => '$amount', 'type' => 'float', 'desc' => __( 'Amount.', 'wp-job-portal' ) ],
                [ 'name' => '$pay_method', 'type' => 'string', 'desc' => __( 'Payment Method.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_membership_package_purchased_db_commit', 'wpjb_db_audit_log', 10, 4 );\nfunction wpjb_db_audit_log( \$uid, \$pkg, \$amt, \$method ) {\n    // Audit logic\n}",
            'notes'       => __( 'Database-level failsafe hook.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'package_saved',
            'addon'       => 'credits',
            'addon_name'       => 'Credits',
            'name'        => __( 'Package Saved', 'wp-job-portal' ),
            'action'      => 'wpjobportal_package_saved',
            'type'        => 'action',
            'description' => __( 'Fires when a package is updated or unpublished.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$id', 'type' => 'int', 'desc' => __( 'Package ID.', 'wp-job-portal' ) ],
                [ 'name' => '$data', 'type' => 'array', 'desc' => __( 'Package data.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_package_saved', 'wpjb_log_pkg_update', 10, 2 );\nfunction wpjb_log_pkg_update( \$id, \$data ) {\n    // Logic\n}",
            'notes'       => __( 'Fires on package update.', 'wp-job-portal' ),
        ],
    ],

    'Billing & Invoices' => [
        [
            'id'          => 'invoice_status_transition',
            'addon'       => 'credits',
            'addon_name'       => 'Credits',
            'name'        => __( 'Invoice Status Transition', 'wp-job-portal' ),
            'action'      => 'wpjobportal_invoice_status_transition',
            'type'        => 'action',
            'description' => __( 'Fires when an invoice moves to "Paid", syncing external accounting software.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$invoice_id', 'type' => 'int', 'desc' => __( 'Invoice ID.', 'wp-job-portal' ) ],
                [ 'name' => '$old_status', 'type' => 'string', 'desc' => __( 'Old Status.', 'wp-job-portal' ) ],
                [ 'name' => '$new_status', 'type' => 'string', 'desc' => __( 'New Status.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_invoice_status_transition', 'wpjb_xero_sync', 10, 3 );\nfunction wpjb_xero_sync( \$id, \$old, \$new ) {\n    if ( \$new === 'paid' ) { // Xero API }\n}",
            'notes'       => __( 'Syncs external accounting software.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'invoice_saved',
            'addon'       => 'credits',
            'addon_name'       => 'Credits',
            'name'        => __( 'Invoice Saved', 'wp-job-portal' ),
            'action'      => 'wpjobportal_invoice_saved',
            'type'        => 'action',
            'description' => __( 'Fires on invoice creation or deletion.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$id', 'type' => 'int', 'desc' => __( 'Invoice ID.', 'wp-job-portal' ) ],
                [ 'name' => '$data', 'type' => 'array', 'desc' => __( 'Invoice Data.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_invoice_saved', 'wpjb_log_invoice', 10, 2 );\nfunction wpjb_log_invoice( \$id, \$data ) {\n    // Logic\n}",
            'notes'       => __( 'Fires on invoice creation.', 'wp-job-portal' ),
        ],
    ],

    'Dashboards & UI Widgets' => [
        [
            'id'          => 'employer_dashboard_loaded',
            'addon'       => 'core',
            'addon_name'       => 'core',
            'name'        => __( 'Employer Dashboard Loaded', 'wp-job-portal' ),
            'action'      => 'wpjobportal_employer_dashboard_loaded',
            'type'        => 'action',
            'description' => __( 'Fires when the employer portal initializes.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$employer_uid', 'type' => 'int', 'desc' => __( 'Employer User ID.', 'wp-job-portal' ) ],
                [ 'name' => '$_data', 'type' => 'array', 'desc' => __( 'Portal view data array.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_employer_dashboard_loaded', 'wpjb_emp_dashboard_init', 10, 2 );\nfunction wpjb_emp_dashboard_init( \$uid, \$data ) {\n    // Analytics tracking\n}",
            'notes'       => __( 'Fires when employer portal initializes.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'jobseeker_dashboard_loaded',
            'addon'       => 'core',
            'addon_name'       => 'core',
            'name'        => __( 'Jobseeker Dashboard Loaded', 'wp-job-portal' ),
            'action'      => 'wpjobportal_jobseeker_dashboard_loaded',
            'type'        => 'action',
            'description' => __( 'Fires when the candidate portal initializes.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$seeker_uid', 'type' => 'int', 'desc' => __( 'Candidate User ID.', 'wp-job-portal' ) ],
                [ 'name' => '$_data', 'type' => 'array', 'desc' => __( 'Portal view data array.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_jobseeker_dashboard_loaded', 'wpjb_candidate_init', 10, 2 );\nfunction wpjb_candidate_init( \$uid, \$data ) {\n    // Analytics tracking\n}",
            'notes'       => __( 'Fires when candidate portal initializes.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'admin_dashboard_loaded',
            'addon'       => 'core',
            'addon_name'       => 'core',
            'name'        => __( 'Admin Dashboard Loaded', 'wp-job-portal' ),
            'action'      => 'wpjobportal_admin_dashboard_loaded',
            'type'        => 'action',
            'description' => __( 'Injects custom data into the WP Admin control panel.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$_data', 'type' => 'array', 'desc' => __( 'Admin data array.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_admin_dashboard_loaded', 'wpjb_custom_admin_widget', 10, 1 );\nfunction wpjb_custom_admin_widget( \$data ) {\n    // Inject data\n}",
            'notes'       => __( 'Injects data into WP Admin.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'user_widget_html',
            'addon'       => 'core',
            'addon_name'       => 'core',
            'name'        => __( 'User Widget HTML', 'wp-job-portal' ),
            'action'      => 'wpjobportal_user_widget_html',
            'type'        => 'filter',
            'description' => __( 'Allows theme developers to modify member widget cards.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$html', 'type' => 'string', 'desc' => __( 'HTML Content.', 'wp-job-portal' ) ],
                [ 'name' => '$role', 'type' => 'string', 'desc' => __( 'User role.', 'wp-job-portal' ) ],
                [ 'name' => '$results', 'type' => 'array', 'desc' => __( 'User data.', 'wp-job-portal' ) ],
            ],
            'returns'     => [ 'type' => 'string', 'desc' => __( 'Modified HTML.', 'wp-job-portal' ) ],
            'example'     => "add_filter( 'wpjobportal_user_widget_html', 'wpjb_modify_user_card', 10, 3 );\nfunction wpjb_modify_user_card( \$html, \$role, \$res ) {\n    return \$html;\n}",
            'notes'       => __( 'Modify member widget cards.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'shortlist_popup_html',
            'addon'       => 'shortlist',
            'addon_name'       => 'Shortlist Job',
            'name'        => __( 'Shortlist Popup HTML', 'wp-job-portal' ),
            'action'      => 'wpjobportal_shortlist_popup_html',
            'type'        => 'filter',
            'description' => __( 'Modifies the HTML output of the shortlist modal.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$content', 'type' => 'string', 'desc' => __( 'HTML Content.', 'wp-job-portal' ) ],
                [ 'name' => '$jobid', 'type' => 'int', 'desc' => __( 'Job ID.', 'wp-job-portal' ) ],
            ],
            'returns'     => [ 'type' => 'string', 'desc' => __( 'Modified HTML.', 'wp-job-portal' ) ],
            'example'     => "add_filter( 'wpjobportal_shortlist_popup_html', 'wpjb_modify_shortlist', 10, 2 );\nfunction wpjb_modify_shortlist( \$html, \$job_id ) {\n    return \$html;\n}",
            'notes'       => __( 'Modifies shortlist modal HTML.', 'wp-job-portal' ),
        ],
    ],

    'Data Exports' => [
        [
            'id'          => 'export_resume_raw_string',
            'addon'       => 'export',
            'addon_name'       => 'Export',
            'name'        => __( 'Export Resume Raw String', 'wp-job-portal' ),
            'action'      => 'wpjobportal_export_resume_raw_string',
            'type'        => 'filter',
            'description' => __( 'Intercepts the final TSV text string for a single resume download.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$data', 'type' => 'string', 'desc' => __( 'TSV String.', 'wp-job-portal' ) ],
                [ 'name' => '$jobid', 'type' => 'int', 'desc' => __( 'Job ID.', 'wp-job-portal' ) ],
                [ 'name' => '$resumeid', 'type' => 'int', 'desc' => __( 'Resume ID.', 'wp-job-portal' ) ],
            ],
            'returns'     => [ 'type' => 'string', 'desc' => __( 'Modified TSV string.', 'wp-job-portal' ) ],
            'example'     => "add_filter( 'wpjobportal_export_resume_raw_string', 'wpjb_add_disclaimer', 10, 3 );\nfunction wpjb_add_disclaimer( \$data, \$job, \$res ) {\n    return \$data . \"\\nDisclaimer: Confidential\";\n}",
            'notes'       => __( 'Intercepts single TSV export.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'export_resume_data_array',
            'addon'       => 'export',
            'addon_name'       => 'Export',
            'name'        => __( 'Export Resume Data Array', 'wp-job-portal' ),
            'action'      => 'wpjobportal_export_resume_data_array',
            'type'        => 'filter',
            'description' => __( 'Highly useful for Blind Hiring; allows programmatic removal of PII columns before TSV conversion.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$resume_row', 'type' => 'array', 'desc' => __( 'Export row array.', 'wp-job-portal' ) ],
                [ 'name' => '$result', 'type' => 'array', 'desc' => __( 'DB result data.', 'wp-job-portal' ) ],
            ],
            'returns'     => [ 'type' => 'array', 'desc' => __( 'Sanitized row array.', 'wp-job-portal' ) ],
            'example'     => "add_filter( 'wpjobportal_export_resume_data_array', 'wpjb_blind_hire_export', 10, 2 );\nfunction wpjb_blind_hire_export( \$row, \$res ) {\n    unset(\$row['name']); unset(\$row['email']);\n    return \$row;\n}",
            'notes'       => __( 'Highly useful for Blind Hiring.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'export_all_resumes_raw_string',
            'addon'       => 'allresumes',
            'addon_name'       => 'All Resumes',
            'name'        => __( 'Export All Resumes Raw String', 'wp-job-portal' ),
            'action'      => 'wpjobportal_export_all_resumes_raw_string',
            'type'        => 'filter',
            'description' => __( 'Intercepts the bulk export string to append HR disclaimers or timestamps.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$data', 'type' => 'string', 'desc' => __( 'Bulk TSV String.', 'wp-job-portal' ) ],
                [ 'name' => '$jobid', 'type' => 'int', 'desc' => __( 'Job ID.', 'wp-job-portal' ) ],
            ],
            'returns'     => [ 'type' => 'string', 'desc' => __( 'Modified bulk string.', 'wp-job-portal' ) ],
            'example'     => "add_filter( 'wpjobportal_export_all_resumes_raw_string', 'wpjb_bulk_disclaimer', 10, 2 );\nfunction wpjb_bulk_disclaimer( \$data, \$job_id ) {\n    return \$data;\n}",
            'notes'       => __( 'Intercepts bulk export string.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'resume_export_filename',
            'addon'       => 'pdf',
            'addon_name'       => 'pdf',
            'name'        => __( 'Resume Export Filename', 'wp-job-portal' ),
            'action'      => 'wpjobportal_resume_export_filename',
            'type'        => 'filter',
            'description' => __( 'Mutates the generated ZIP file name dynamically.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$filename', 'type' => 'string', 'desc' => __( 'Default filename.', 'wp-job-portal' ) ],
                [ 'name' => '$resumeid', 'type' => 'int', 'desc' => __( 'Resume ID.', 'wp-job-portal' ) ],
                [ 'name' => '$ext', 'type' => 'string', 'desc' => __( 'File extension.', 'wp-job-portal' ) ],
            ],
            'returns'     => [ 'type' => 'string', 'desc' => __( 'Mutated filename.', 'wp-job-portal' ) ],
            'example'     => "add_filter( 'wpjobportal_resume_export_filename', 'wpjb_custom_filename', 10, 3 );\nfunction wpjb_custom_filename( \$name, \$id, \$ext ) {\n    return 'company_export_' . date('Ymd') . '.' . \$ext;\n}",
            'notes'       => __( 'Mutates generated ZIP filename.', 'wp-job-portal' ),
        ],
    ],

    'Communications & Messaging' => [
        [
            'id'          => 'system_email_headers',
            'addon'       => 'core',
            'addon_name'       => 'core',
            'name'        => __( 'System Email Headers', 'wp-job-portal' ),
            'action'      => 'wpjobportal_system_email_headers',
            'type'        => 'filter',
            'description' => __( 'Modifies outgoing SMTP headers (e.g., custom Reply-To addresses).', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$headers', 'type' => 'array', 'desc' => __( 'Email headers array.', 'wp-job-portal' ) ],
                [ 'name' => '$subject', 'type' => 'string', 'desc' => __( 'Email subject.', 'wp-job-portal' ) ],
            ],
            'returns'     => [ 'type' => 'array', 'desc' => __( 'Modified headers.', 'wp-job-portal' ) ],
            'example'     => "add_filter( 'wpjobportal_system_email_headers', 'wpjb_custom_headers', 10, 2 );\nfunction wpjb_custom_headers( \$hdrs, \$subj ) {\n    \$hdrs[] = 'Reply-To: support@example.com';\n    return \$hdrs;\n}",
            'notes'       => __( 'Modifies outgoing SMTP headers.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'email_notification_sent',
            'addon'       => 'core',
            'addon_name'       => 'core',
            'name'        => __( 'Email Notification Sent', 'wp-job-portal' ),
            'action'      => 'wpjobportal_email_notification_sent',
            'type'        => 'action',
            'description' => __( 'Logs transactional emails immediately after wp_mail() fires.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$type', 'type' => 'string', 'desc' => __( 'Email type string.', 'wp-job-portal' ) ],
                [ 'name' => '$recipient_data', 'type' => 'array', 'desc' => __( 'Recipient details.', 'wp-job-portal' ) ],
                [ 'name' => '$content', 'type' => 'string', 'desc' => __( 'Email HTML payload.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_email_notification_sent', 'wpjb_log_emails', 10, 3 );\nfunction wpjb_log_emails( \$type, \$recipients, \$content ) {\n    // Log into audit table\n}",
            'notes'       => __( 'Logs transactional emails.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'internal_message_sent',
            'addon'       => 'message',
            'addon_name'       => 'Messages',
            'name'        => __( 'Internal Message Sent', 'wp-job-portal' ),
            'action'      => 'wpjobportal_internal_message_sent',
            'type'        => 'action',
            'description' => __( 'Intercepts secure internal messages to push to external SMS/Slack APIs.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$msg_id', 'type' => 'int', 'desc' => __( 'Message ID.', 'wp-job-portal' ) ],
                [ 'name' => '$sender_id', 'type' => 'int', 'desc' => __( 'Sender ID.', 'wp-job-portal' ) ],
                [ 'name' => '$recipient_id', 'type' => 'int', 'desc' => __( 'Recipient ID.', 'wp-job-portal' ) ],
                [ 'name' => '$message_content', 'type' => 'string', 'desc' => __( 'Message Text.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_internal_message_sent', 'wpjb_slack_notify', 10, 4 );\nfunction wpjb_slack_notify( \$msg, \$snd, \$rec, \$text ) {\n    // Push to Slack\n}",
            'notes'       => __( 'Push to external SMS/Slack APIs.', 'wp-job-portal' ),
        ],

        [
            'id'          => 'message_status_transition',
            'addon'       => 'message',
            'addon_name'       => 'Messages',
            'name'        => __( 'Message Status Transition', 'wp-job-portal' ),
            'action'      => 'wpjobportal_message_status_transition',
            'type'        => 'action',
            'description' => __( 'Triggers when an admin resolves a flagged/conflicted message.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$msg_id', 'type' => 'int', 'desc' => __( 'Message ID.', 'wp-job-portal' ) ],
                [ 'name' => '$status', 'type' => 'string', 'desc' => __( 'New status.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_message_status_transition', 'wpjb_msg_status_log', 10, 2 );\nfunction wpjb_msg_status_log( \$id, \$status ) {\n    // Status change logic\n}",
            'notes'       => __( 'Admin flag resolution.', 'wp-job-portal' ),
        ],
    ],

    'System Infrastructure' => [
        [
            'id'          => 'file_uploaded',
            'addon'       => 'core',
            'addon_name'       => 'core',
            'name'        => __( 'File Uploaded (S3 Hook)', 'wp-job-portal' ),
            'action'      => 'wpjobportal_file_uploaded',
            'type'        => 'action',
            'description' => __( 'The ultimate scalability hook. Fires when a physical file is saved, allowing cloud plugins to upload to AWS S3 and delete the local instance.', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$file_path', 'type' => 'string', 'desc' => __( 'Absolute physical file path.', 'wp-job-portal' ) ],
                [ 'name' => '$context_type', 'type' => 'string', 'desc' => __( 'Context type (e.g. company_logo, resume_file).', 'wp-job-portal' ) ],
                [ 'name' => '$entity_id', 'type' => 'int', 'desc' => __( 'Associated record ID.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_file_uploaded', 'wpjb_offload_to_s3', 10, 3 );\nfunction wpjb_offload_to_s3( \$path, \$context, \$id ) {\n    // Upload to AWS S3 and unlink(\$path)\n}",
            'notes'       => __( 'The ultimate scalability hook for S3.', 'wp-job-portal' ),
        ],
        [
            'id'          => 'seo_settings_updated',
            'addon'       => 'core',
            'addon_name'       => 'core',
            'name'        => __( 'SEO Settings Updated', 'wp-job-portal' ),
            'action'      => 'wpjobportal_seo_settings_updated',
            'type'        => 'action',
            'description' => __( 'Executes when core configs update, enabling auto-purging of caching plugins (WP Rocket).', 'wp-job-portal' ),
            'params'      => [
                [ 'name' => '$wpjobportal_data', 'type' => 'array', 'desc' => __( 'Config data array.', 'wp-job-portal' ) ],
            ],
            'example'     => "add_action( 'wpjobportal_seo_settings_updated', 'wpjb_clear_rocket_cache', 10, 1 );\nfunction wpjb_clear_rocket_cache( \$data ) {\n    if ( function_exists( 'rocket_clean_domain' ) ) {\n        rocket_clean_domain();\n    }\n}",
            'notes'       => __( 'Enabling auto-purging of caching plugins.', 'wp-job-portal' ),
        ],
    ],
];

?>

<div id="wpjobportaladmin-wrapper">
    <div id="wpjobportaladmin-leftmenu">
        <?php WPJOBPORTALincluder::getClassesInclude( 'wpjobportaladminsidemenu' ); ?>
    </div>

    <div id="wpjobportaladmin-data">
        <?php
            $wpjobportal_msgkey = WPJOBPORTALincluder::getJSModel( 'wpjobportal' )->getMessagekey();
            WPJOBPORTALMessages::getLayoutMessage( $wpjobportal_msgkey );
        ?>
        <div id="wpjobportal-wrapper-top">
            <div id="wpjobportal-wrapper-top-left">
                <div id="wpjobportal-breadcrumbs">
                    <ul>
                        <li>
                            <a href="<?php echo esc_url_raw( admin_url( 'admin.php?page=wpjobportal' ) ); ?>" title="<?php echo esc_attr( __( 'dashboard', 'wp-job-portal' ) ); ?>">
                                <?php echo esc_html( __( 'Dashboard', 'wp-job-portal' ) ); ?>
                            </a>
                        </li>
                        <li><?php echo esc_html( __( 'Hook Reference', 'wp-job-portal' ) ); ?></li>
                    </ul>
                </div>
            </div>

            <div id="wpjobportal-wrapper-top-right">
                <div id="wpjobportal-config-btn">
                    <a href="admin.php?page=wpjobportal_configuration" title="<?php echo esc_attr( __( 'configuration', 'wp-job-portal' ) ); ?>">
                        <img src="<?php echo esc_url( WPJOBPORTAL_PLUGIN_URL ); ?>includes/images/control_panel/dashboard/config.png">
                    </a>
                </div>
                <div id="wpjobportal-help-btn" class="wpjobportal-help-btn">
                    <a href="admin.php?page=wpjobportal&wpjobportallt=help" title="<?php echo esc_attr( __( 'help', 'wp-job-portal' ) ); ?>">
                        <img src="<?php echo esc_url( WPJOBPORTAL_PLUGIN_URL ); ?>includes/images/control_panel/dashboard/help.png">
                    </a>
                </div>
                <div id="wpjobportal-vers-txt">
                    <?php echo esc_html( __( 'Version', 'wp-job-portal' ) ) . ': '; ?>
                    <span class="wpjobportal-ver"><?php echo esc_html( WPJOBPORTALincluder::getJSModel( 'configuration' )->getConfigValue( 'versioncode' ) ); ?></span>
                </div>
            </div>
        </div>

        <?php WPJOBPORTALincluder::getTemplate( 'templates/admin/pagetitle', array( 'wpjobportal_module' => 'wpjobportal', 'wpjobportal_layouts' => 'hook_reference' ) ); ?>

        <div class="wjp-hook-reference-wrap">
            <div class="wjp-layout-grid">

                <aside id="wjp-sidebar">
                    <div class="wjp-sidebar-header-label">
                        <?php echo esc_html__( 'Entities', 'wp-job-portal' ); ?>
                    </div>
                    <div class="wjp-sidebar-nav" id="wjp-nav-container">
                        <?php foreach ( array_keys( $hook_data ) as $entity ) : ?>
                            <button class="wjp-sidebar-nav-item <?php echo $entity === 'Jobs' ? 'active' : ''; ?>" data-entity="<?php echo esc_attr( $entity ); ?>">
                                <?php if ( $entity == 'Companies' ) { ?>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><line x1="9" y1="22" x2="9" y2="18"></line><line x1="15" y1="22" x2="15" y2="18"></line></svg>
                                <?php } elseif ( $entity == 'Resumes' ) { ?>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                <?php } elseif ( $entity == 'Applications & ATS Workflow' ) { ?>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>
                                <?php } elseif ( $entity == 'Users & Accounts' ) { ?>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                                <?php } elseif ( $entity == 'AI & Match Engine' ) { ?>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect><rect x="9" y="9" width="6" height="6"></rect><line x1="9" y1="1" x2="9" y2="4"></line><line x1="15" y1="1" x2="15" y2="4"></line><line x1="9" y1="20" x2="9" y2="23"></line><line x1="15" y1="20" x2="15" y2="23"></line><line x1="20" y1="9" x2="23" y2="9"></line><line x1="20" y1="14" x2="23" y2="14"></line><line x1="1" y1="9" x2="4" y2="9"></line><line x1="1" y1="14" x2="4" y2="14"></line></svg>
                                <?php } elseif ( $entity == 'Job Alerts' ) { ?>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                                <?php } elseif ( $entity == 'E-Commerce & Subscriptions' ) { ?>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                                <?php } elseif ( $entity == 'Billing & Invoices' ) { ?>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                                <?php } elseif ( $entity == 'Dashboards & UI Widgets' ) { ?>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                                <?php } elseif ( $entity == 'Data Exports' ) { ?>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                <?php } elseif ( $entity == 'Communications & Messaging' ) { ?>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                <?php } elseif ( $entity == 'System Infrastructure' ) { ?>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect><rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect><line x1="6" y1="6" x2="6.01" y2="6"></line><line x1="6" y1="18" x2="6.01" y2="18"></line></svg>
                                <?php } else { // Default case for 'Jobs' and any missing keys ?>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                                <?php } ?>
                                <span><?php echo esc_html( wpjobportal::wpjobportal_getVariableValue($entity) ); ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </aside>

                <div class="wjp-content-area">

                    <div class="wjp-search-card">
                        <div class="wjp-search-input-wrapper">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            <input type="text" id="wjp-global-search" placeholder="<?php echo esc_attr__( 'Search across all actions and filters...', 'wp-job-portal' ); ?>">
                        </div>
                    </div>

                    <div id="wjp-hook-results">
                        <?php foreach ( $hook_data as $entity_name => $hooks ) : ?>
                            <?php foreach ( $hooks as $hook ) : ?>
                                <div class="wjp-card wjp-hook-item"
                                     data-entity="<?php echo esc_attr( $entity_name ); ?>"
                                     data-action="<?php echo esc_attr( $hook['action'] ); ?>"
                                     data-name="<?php echo esc_attr( $hook['name'] ); ?>"
                                     data-desc="<?php echo esc_attr( $hook['description'] ); ?>"
                                     style="<?php echo $entity_name === 'Jobs' ? '' : 'display:none;'; ?>">

                                    <div class="wjp-hook-badge-row">
                                        <span class="wjp-tag wjp-entity-indicator" style="display:none;"><?php echo esc_html( $entity_name ); ?></span>
                                        <span class="wjp-tag <?php echo $hook['type'] === 'action' ? 'wjp-tag-indigo' : 'wjp-tag-emerald'; ?>">
                                            <?php echo esc_html( $hook['type'] ); ?>
                                        </span>
                                    </div>

                                    <div class="wjp-hook-title-group">
                                        <h3 class="wjp-hook-name"><?php echo esc_html( $hook['name'] ); ?></h3>&nbsp;(<?php echo esc_html( $hook['action'] ); ?>)
                                    </div>

                                    <p class="wjp-hook-description"><?php echo esc_html( $hook['description'] ); ?></p>

                                    <div class="wjp-details-flex">
                                        <div class="wjp-details-column">
                                            <span class="wjp-detail-heading"><?php echo esc_html__( 'Arguments', 'wp-job-portal' ); ?></span>
                                            <?php foreach ( $hook['params'] as $param ) : ?>
                                                <div class="wjp-param-item <?php echo esc_attr( $hook['type'] ); ?>">
                                                    <div class="wjp-param-meta">
                                                        <span class="wjp-param-name"><?php echo esc_html( $param['name'] ); ?></span>
                                                        <span class="wjp-param-type"><?php echo esc_html( $param['type'] ); ?></span>
                                                    </div>
                                                    <p class="wjp-param-desc"><?php echo esc_html( $param['desc'] ); ?></p>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="wjp-details-column">
                                            <span class="wjp-detail-heading"><?php echo esc_html__( 'Returns', 'wp-job-portal' ); ?></span>
                                            <?php if ( isset( $hook['returns'] ) ) : ?>
                                                <div class="wjp-return-box">
                                                    <div class="wjp-return-header">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 10 20 15 15 20"></polyline><path d="M4 4v7a4 4 0 0 0 4 4h12"></path></svg>
                                                        <span class="wjp-return-label"><?php echo esc_html( $hook['returns']['type'] ); ?></span>
                                                    </div>
                                                    <p class="wjp-return-desc"><?php echo esc_html( $hook['returns']['desc'] ); ?></p>
                                                </div>
                                            <?php else : ?>
                                                <div class="wjp-no-return-placeholder">
                                                    <span class="wjp-italic-text"><?php echo esc_html__( 'No return (Action Hook)', 'wp-job-portal' ); ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="wjp-code-block">
                                        <div class="wjp-code-header">
                                            <span><?php echo esc_html__( 'Snippet', 'wp-job-portal' ); ?></span>
                                            <button class="wjp-code-copy copy-trigger" data-copy="<?php echo esc_attr( $hook['example'] ); ?>"><?php echo esc_html__( 'Copy Code', 'wp-job-portal' ); ?></button>
                                        </div>
                                        <div class="wjp-code-content">
                                            <pre><code><?php echo htmlspecialchars( $hook['example'] ); ?></code></pre>
                                        </div>
                                    </div>

                                    <div class="wjp-info-note">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--wjp-color-primary)" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                        <span><?php echo esc_html( $hook['notes'] ); ?></span>
                                    </div>
                                    <?php
                                    if ( (isset($hook['addon']) && $hook['addon'] !== 'core') && !in_array($hook['addon'], wpjobportal::$_active_addons) ) :
                                    ?>
                                        <div class="wpjobportal-shortcode-notice-wrap wpjobportal-notice-msg" style="margin-top: 15px; padding: 10px; border-radius: 8px; display: flex; align-items: center; gap: 10px; background: #fff4f4; border: 1px solid #fecaca;">
                                            <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL . "includes/images/import-city-warning-icon.png"); ?>" style="width:20px; height:20px;">
                                            <p style="margin:0; font-size: 13px; color: #991b1b;">
                                                <?php echo esc_html__( 'To use this hook, please install and activate the', 'wp-job-portal' ); ?>
                                                <strong><?php echo esc_html( $hook['addon_name'] ); ?></strong>
                                                <?php echo esc_html__( 'addon.', 'wp-job-portal' ); ?>
                                                <a href="https://wpjobportal.com/addons/" target="_blank" style="text-decoration: underline; margin-left: 5px;">
                                                    <?php echo esc_html__( 'View Addon Details', 'wp-job-portal' ); ?>
                                                </a>
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endforeach; ?>

                        <div id="wjp-no-results" class="wjp-card" style="display:none; text-align:center; padding: 60px;">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="1.5" style="margin-bottom:16px;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            <div style="font-weight:700; color:var(--wjp-text-dark);"><?php echo esc_html__( 'No results found', 'wp-job-portal' ); ?></div>
                            <p style="color:var(--wjp-text-lighter); font-size:13px; margin-top:8px;"><?php echo esc_html__( 'Try searching for specific keywords like "save" or "meta".', 'wp-job-portal' ); ?></p>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div id="wjp-toast"><?php echo esc_html__( 'Copied to clipboard', 'wp-job-portal' ); ?></div>
    </div>
</div>

<style>
    /* Specific selectors to avoid design breaking in WP admin */
    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-hook-reference-wrap {
        padding: 24px 0;
        min-height: 800px;
    }

    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-hook-reference-wrap .wjp-layout-grid {
        display: flex;
        gap: 24px;
        align-items: flex-start;
        padding-top: 20px;
    }

    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-hook-reference-wrap #wjp-sidebar {
        width: 280px;
        background: var(--wjp-bg-card);
        border-radius: var(--wjp-border-radius);
        border: 1px solid var(--wjp-border-color);
        box-shadow: var(--wjp-shadow);
        flex-shrink: 0;
        position: sticky;
        top: 32px;
    }

    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-hook-reference-wrap .wjp-sidebar-header-label {
        padding: 16px 20px;
        border-bottom: 1px solid var(--wjp-border-color);
        font-weight: 700;
        font-size: 16px;
    }

    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-hook-reference-wrap .wjp-sidebar-nav {
        padding: 12px;
    }

    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-hook-reference-wrap .wjp-sidebar-nav-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 16px;
        border-radius: var(--wjp-border-radius-sm);
        color: var(--wjp-text-medium);
        text-decoration: none;
        font-weight: 500;
        font-size: 0.875rem;
        transition: all 0.2s;
        cursor: pointer;
        border: none;
        background: transparent;
        width: 100%;
        text-align: left;
        border-left: 3px solid transparent;
        margin-bottom: 4px;
    }

    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-hook-reference-wrap .wjp-sidebar-nav-item:hover {
        background-color: var(--wjp-bg-light);
    }

    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-hook-reference-wrap .wjp-sidebar-nav-item.active {
        background-color: var(--wjp-color-primary-bg);
        color: var(--wjp-color-primary);
        font-weight: 600;
        border-left: 3px solid var(--wjp-color-primary);
    }

    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-hook-reference-wrap .wjp-content-area {
        flex-grow: 1;
        min-width: 0;
    }

    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-hook-reference-wrap .wjp-search-card {
        background: var(--wjp-bg-card);
        padding: 20px;
        border-radius: var(--wjp-border-radius);
        border: 1px solid var(--wjp-border-color);
        box-shadow: var(--wjp-shadow);
        margin-bottom: 24px;
    }

    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-hook-reference-wrap .wjp-search-input-wrapper {
        position: relative;
        max-width: 100%;
        border: none;
        padding: 0;
    }

    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-hook-reference-wrap .wjp-search-input-wrapper input {
        width: 100%;
        padding: 12px 16px 12px 44px;
        border-radius: var(--wjp-border-radius-sm);
        border: 1px solid var(--wjp-border-color);
        font-size: 14px;
        background: #fff;
    }

    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-hook-reference-wrap .wjp-search-input-wrapper svg {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--wjp-text-lighter);
    }

    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-hook-reference-wrap .wjp-card {
        background-color: var(--wjp-bg-card);
        padding: 28px;
        border-radius: var(--wjp-border-radius);
        box-shadow: var(--wjp-shadow);
        border: 1px solid var(--wjp-border-color);
        margin-bottom: 24px;
    }

    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-hook-reference-wrap .wjp-hook-name {
        font-family: 'Fira Code', monospace;
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--wjp-text-dark);
        margin: 0;
    }

    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-hook-reference-wrap .wjp-details-flex {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 20px;
    }

    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-hook-reference-wrap .wjp-details-column {
        flex: 1 1 300px;
    }

    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-hook-reference-wrap .wjp-param-item {
        background: var(--wjp-bg-body);
        border: 1px solid var(--wjp-border-color);
        border-left: 4px solid var(--wjp-color-primary);
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 8px;
        display: flex;
        gap: 12px;
        width: 100%;
    }

    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-hook-reference-wrap .wjp-code-block {
        background-color: #0F172A;
        border-radius: 12px;
        overflow: hidden;
        margin-top: 20px;
    }

    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-hook-reference-wrap .wjp-code-header {
        background: #1E293B;
        padding: 8px 16px;
        display: flex;
        justify-content: space-between;
        color: #94A3B8;
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 700;
    }

    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-hook-reference-wrap .wjp-code-content {
        padding: 20px;
        color: #E2E8F0;
        font-family: 'Fira Code', monospace;
        font-size: 13px;
        line-height: 1.6;
        overflow-x: auto;
    }

    /* Syntax colors */
    .wjp-syntax-key { color: #F472B6; }
    .wjp-syntax-func { color: #60A5FA; }
    .wjp-syntax-str { color: #FCD34D; }
    .wjp-syntax-comment { color: #64748B; font-style: italic; }

    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-hook-reference-wrap .wjp-info-note {
        margin-top: 20px;
        padding: 12px;
        background: #f1f5f9;
        border-radius: 8px;
        font-size: 13px;
        display: flex;
        gap: 10px;
        align-items: center;
        color: #64748b;
    }

    #wjp-toast {
        position: fixed;
        bottom: 32px;
        left: 50%;
        transform: translateX(-50%);
        background: #1e293b;
        color: #fff;
        padding: 12px 24px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        display: none;
        z-index: 10000;
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.2);
    }

    @media (max-width: 1024px) {
        #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-hook-reference-wrap .wjp-layout-grid { flex-direction: column; }
        #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-hook-reference-wrap #wjp-sidebar { width: 100%; position: static; }
    }

    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-details-column {
            flex: 1 1 300px;
        }

    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-detail-heading {
            font-size: 11px;
            font-weight: 700;
            color: var(--wjp-text-lighter);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 12px;
            display: block;
        }
    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-param-meta { min-width: 90px; }
    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-param-name { font-family: monospace; font-weight: 700; font-size: 13px; color: var(--wjp-text-dark); display: block; }
    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-param-type { font-size: 9px; font-weight: 800; color: var(--wjp-text-lighter); text-transform: uppercase; }
    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-param-desc { font-size: 13px; color: var(--wjp-text-medium); line-height: 1.4; }

    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-hook-reference-wrap .wjp-return-box {
        background: var(--wjp-color-success-bg);
        border: 1px dashed var(--wjp-color-success);
        padding: 16px;
        border-radius: 8px;
    }

    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-hook-reference-wrap .wjp-no-return-placeholder {
        background: var(--wjp-bg-light);
        border-radius: 8px;
        padding: 24px;
        text-align: center;
        opacity: 0.6;
    }
    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-hook-reference-wrap .wjp-return-header { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; }
    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-hook-reference-wrap .wjp-return-label { font-size: 10px; font-weight: 800; color: var(--wjp-color-success-text); text-transform: uppercase; }
    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-hook-reference-wrap .wjp-return-desc { font-size: 13px; font-weight: 600; color: var(--wjp-color-success-text); }

    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-hook-reference-wrap .wjp-hook-title-group {
        display: inline-block;
        font-size: 16px;
        color: var(--wjp-text-medium);
    }
    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-hook-reference-wrap .wjp-hook-title-group h3{
        display: inline-block;
    }

    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-hook-reference-wrap .wjp-tag {
        padding: 4px 10px;
        font-size: 11px;
        font-weight: 600;
        border-radius: 5px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 10px;
        display: inline-block;
    }

    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-hook-reference-wrap .wjp-tag-indigo { background-color: var(--wjp-color-primary-light); color: var(--wjp-color-primary); }
    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-hook-reference-wrap .wjp-tag-emerald { background-color: var(--wjp-color-success-bg); color: var(--wjp-color-success-text); }
    #wpjobportaladmin-wrapper #wpjobportaladmin-data .wjp-hook-reference-wrap .wjp-tag-slate { background-color: var(--wjp-color-slate-bg); color: var(--wjp-color-slate); }



</style>

<script>
jQuery(document).ready(function($) {
    const $resultsContainer = $('#wjp-hook-results');
    const $noResults = $('#wjp-no-results');
    const $headerTitle = $('#wjp-main-panel-header');

    // Highlighting PHP
    function highlightPHP() {
    $('.wjp-code-content code').each(function() {
        let code = $(this).text();

        // 1. Escape HTML
        code = code.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");

        const tokens = [];
        let tokenId = 0;

        // Helper to "hide" matches from further processing
        function tokenize(regex, className) {
            code = code.replace(regex, function(match) {
                const id = `___WJPTOKEN${tokenId++}___`;
                tokens.push({ id: id, html: `<span class="${className}">${match}</span>` });
                return id;
            });
        }

        // 2. TOKENIZE IN ORDER (Strict)
        // Comments first
        tokenize(/(\/\*[\s\S]*?\*\/|\/\/.*$)/gm, 'wjp-syntax-comment');

        // Strings second
        tokenize(/(['"])(?:(?=(\\?))\2.)*?\1/g, 'wjp-syntax-str');

        // Variables third
        tokenize(/(\$[a-zA-Z_]\w*)/g, 'wjp-syntax-var');

        // Keywords and Functions (Whole words only)
        code = code.replace(/\b(function|return|if|else|foreach|as|isset|array|add_action|add_filter|wp_mail)\b/g, function(match) {
            const isKey = ['function', 'return', 'if', 'else', 'isset', 'array'].includes(match);
            const cls = isKey ? 'wjp-syntax-key' : 'wjp-syntax-func';
            return `<span class="${cls}">${match}</span>`;
        });

        // 3. RECONSTRUCT
        // Put the tokens back in reverse order to ensure nested structures aren't broken
        for (let i = tokens.length - 1; i >= 0; i--) {
            code = code.replace(tokens[i].id, tokens[i].html);
        }

        $(this).html(code);
    });
}
    highlightPHP();

    // Sidebar Filter
    $('.wjp-sidebar-nav-item').on('click', function() {
        const entity = $(this).data('entity');
        $('#wjp-global-search').val('');

        $('.wjp-sidebar-nav-item').removeClass('active');
        $(this).addClass('active');

        $('.wjp-hook-item').hide();
        $(`.wjp-hook-item[data-entity="${entity}"]`).fadeIn(300);

        $headerTitle.text(entity + " <?php echo esc_js( __( 'Hooks', 'wp-job-portal' ) ); ?>");
        $noResults.hide();
    });

    // Global Search
    $('#wjp-global-search').on('input', function() {
        const query = $(this).val().toLowerCase().trim();
        const $hooks = $('.wjp-hook-item');
        const $sidebarItems = $('.wjp-sidebar-nav-item');

        // Handle Empty Input (Backspace/Delete until empty)
        if (query === '') {
            // 1. Find the item that should be active or default to the first one
            let $activeTab = $sidebarItems.filter('.active');
            if ($activeTab.length === 0) {
                $activeTab = $sidebarItems.first();
            }

            // 2. Hide all entity indicators (cleanup search view)
            $('.wjp-entity-indicator').hide();

            // 3. Reset the view by triggering the tab click
            $activeTab.click();
            return;
        }

        // Handle Active Search State
        $sidebarItems.removeClass('active');
        $headerTitle.text("<?php echo esc_js( __( 'Search Results', 'wp-job-portal' ) ); ?>");

        let found = 0;
        $hooks.each(function() {
            // Using .attr() or .data() depending on how you stored them
            const action = ($(this).data('action') || "").toLowerCase();
            const desc   = ($(this).data('desc')   || "").toLowerCase();
            const name   = ($(this).data('name')   || "").toLowerCase();

            if (action.includes(query) || desc.includes(query) || name.includes(query)) {
                $(this).show();
                $(this).find('.wjp-entity-indicator').show();
                found++;
            } else {
                $(this).hide();
            }
        });

        $noResults.toggle(found === 0);

        // RE-HIGHLIGHT: Only run this if you fixed the "double-wrap" bug!
        if (typeof highlightPHP === "function" && found > 0) {
            highlightPHP();
        }
    });

    // Copy Tool
    $(document).on('click', '.copy-trigger', function() {
        const text = $(this).attr('data-copy');
        const $temp = $("<textarea>");
        $("body").append($temp);
        $temp.val(text).select();
        document.execCommand("copy");
        $temp.remove();

        $('#wjp-toast').fadeIn(200).delay(2000).fadeOut(200);
    });
});
</script>