<?php
if (!defined('ABSPATH')) die('Restricted Access');

// Register and enqueue the main script handle first.
wp_register_script('wjp-admin-config-js', false);
wp_enqueue_script('wjp-admin-config-js');


wp_enqueue_style('wpjobportal-redesign-css', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/wpjobportaladmin_redesign.css');
// Enqueue necessary scripts and styles

// ========================================================
// OPTION ARRAYS DEFINITIONS
// ========================================================

$wpjobportal_options_yesno = [
    (object) ['id' => 1, 'text' => __('Yes', 'wp-job-portal')],
    (object) ['id' => 0, 'text' => __('No', 'wp-job-portal')]
];

$wpjobportal_options_showhide = [
    (object) ['id' => 1, 'text' => __('Show', 'wp-job-portal')],
    (object) ['id' => 0, 'text' => __('Hide', 'wp-job-portal')]
];

$wpjobportal_options_date_format = [
    (object) ['id' => 'd-m-Y', 'text' => 'dd-mm-yyyy'],
    (object) ['id' => 'm/d/Y', 'text' => 'mm/dd/yyyy'],
    (object) ['id' => 'Y-m-d', 'text' => 'yyyy-mm-dd']
];

$wpjobportal_options_register_link = [
    (object) ['id' => '1', 'text' => __('WP Job Portal Register Page', 'wp-job-portal')],
    (object) ['id' => '3', 'text' => __('WordPress Default Register Page', 'wp-job-portal')],
    (object) ['id' => '2', 'text' => __('Custom', 'wp-job-portal')]
];

$wpjobportal_options_login_link = [
    (object) ['id' => '1', 'text' => __('WP Job Portal Login Page', 'wp-job-portal')],
    (object) ['id' => '3', 'text' => __('WordPress Default Login Page', 'wp-job-portal')],
    (object) ['id' => '2', 'text' => __('Custom', 'wp-job-portal')]
];

$wpjobportal_options_default_custom = [
    (object) ['id' => '1', 'text' => __('Default', 'wp-job-portal')],
    (object) ['id' => '2', 'text' => __('Custom', 'wp-job-portal')]
];

$wpjobportal_options_resume_email_sections = [
    (object) ['id' => 1, 'text' => __('Only section that have value', 'wp-job-portal')],
    (object) ['id' => 0, 'text' => __('All sections', 'wp-job-portal')]
];

$wpjobportal_options_resume_email_fields = [
    (object) ['id' => '', 'text' => __('Select Option', 'wp-job-portal')],
    (object) ['id' => 1, 'text' => __('All Fields', 'wp-job-portal')],
    (object) ['id' => 2, 'text' => __('Only filled fields', 'wp-job-portal')]
];

$wpjobportal_options_search_resume_permissions = [
    (object) ['id' => 0, 'text' => __('Not allowed', 'wp-job-portal')],
    (object) ['id' => 1, 'text' => __('Allowed to all', 'wp-job-portal') . ' ' . __('employers, job seekers and visitors', 'wp-job-portal')],
    (object) ['id' => 2, 'text' => __('Allowed only to employers', 'wp-job-portal')]
];

$wpjobportal_options_captcha_selection = [
    (object) ['id' => 1, 'text' => __('Google Captcha', 'wp-job-portal')],
    (object) ['id' => 2, 'text' => __('WP JOB PORTAL Captcha', 'wp-job-portal')]
];

$wpjobportal_options_captcha_calculation = [
    (object) ['id' => 0, 'text' => __('Any', 'wp-job-portal')],
    (object) ['id' => 1, 'text' => __('Addition', 'wp-job-portal')],
    (object) ['id' => 2, 'text' => __('Subtraction', 'wp-job-portal')]
];

$wpjobportal_options_captcha_operands = [
    (object) ['id' => 2, 'text' => '2'],
    (object) ['id' => 3, 'text' => '3']
];

$wpjobportal_options_recaptcha_version = [
    (object) ['id' => 1, 'text' => __('Recaptcha Version 2', 'wp-job-portal')],
    (object) ['id' => 2, 'text' => __('Recaptcha Version 3', 'wp-job-portal')]
];

$wpjobportal_options_map_radius = [
    (object) ['id' => 1, 'text' => __('Meters', 'wp-job-portal')],
    (object) ['id' => 2, 'text' => __('Kilometers', 'wp-job-portal')],
    (object) ['id' => 3, 'text' => __('Miles', 'wp-job-portal')],
    (object) ['id' => 4, 'text' => __('Nautical Miles', 'wp-job-portal')]
];

$wpjobportal_options_address_display = [
    (object) ['id' => 'csc', 'text' => __('City, State, Country', 'wp-job-portal')],
    (object) ['id' => 'cs', 'text' => __('City, State', 'wp-job-portal')],
    (object) ['id' => 'cc', 'text' => __('City, Country', 'wp-job-portal')],
    (object) ['id' => 'c', 'text' => __('City', 'wp-job-portal')]
];

$wpjobportal_options_currency_align = [
    (object) ['id' => 1, 'text' => __('Left align', 'wp-job-portal')],
    (object) ['id' => 2, 'text' => __('Right align', 'wp-job-portal')]
];

$wpjobportal_options_submission_type = [
    (object) ['id' => 1, 'text' => __("Free", 'wp-job-portal')],
    (object) ['id' => 2, 'text' => __("Per Listing", 'wp-job-portal')],
    (object) ['id' => 3, 'text' => __("Membership", 'wp-job-portal')]
];

$wpjobportal_options_mapping_service = [
    (object) ['id' => 'gmap', 'text' => __("Google Map", 'wp-job-portal')],
    (object) ['id' => 'osm', 'text' => __("Open Street Map", 'wp-job-portal')]
];

$wpjobportal_options_cron_times = array(
    (object) array('id' => '5minute', 'text' => '5 ' . __('Minutes', 'wp-job-portal')),
    (object) array('id' => '10minute', 'text' => '10 ' . __('Minutes', 'wp-job-portal')),
    (object) array('id' => '15minute', 'text' => '15 ' . __('Minutes', 'wp-job-portal')),
    (object) array('id' => '30minute', 'text' => '30 ' . __('Minutes', 'wp-job-portal')),
    (object) array('id' => '45minute', 'text' => '45 ' . __('Minutes', 'wp-job-portal')),
    (object) array('id' => '1hour', 'text' => '1 ' . __('Hour', 'wp-job-portal')),
    (object) array('id' => '2hour', 'text' => '2 ' . __('Hours', 'wp-job-portal')),
    (object) array('id' => '6hour', 'text' => '6 ' . __('Hours', 'wp-job-portal')),
    (object) array('id' => '12hour', 'text' => '12 ' . __('Hours', 'wp-job-portal')),
    (object) array('id' => '1day', 'text' => '1 ' . __('Day', 'wp-job-portal')),
    (object) array('id' => '2day', 'text' => '2 ' . __('Days', 'wp-job-portal')),
    (object) array('id' => '1week', 'text' => '1 ' . __('Week', 'wp-job-portal')),
    (object) array('id' => '15day', 'text' => '15 ' . __('Days', 'wp-job-portal')),
    (object) array('id' => '1month', 'text' => '1 ' . __('Month', 'wp-job-portal'))
);

$wpjobportal_company_contact_detail_options = [
    (object) ['id' => 0, 'text' => __('Show No One', 'wp-job-portal')],
    (object) ['id' => 1, 'text' => __('Show Everyone', 'wp-job-portal')],
    (object) ['id' => 2, 'text' => __('Show Job seekers', 'wp-job-portal')],
    (object) ['id' => 3, 'text' => __('Paid access Only', 'wp-job-portal')],
];

$wpjobportal_resume_contact_detail_options = [
    (object) ['id' => 0, 'text' => __('Show No One', 'wp-job-portal')],
    (object) ['id' => 1, 'text' => __('Show Everyone', 'wp-job-portal')],
    (object) ['id' => 2, 'text' => __('Show Employers', 'wp-job-portal')],
    (object) ['id' => 3, 'text' => __('Paid access Only', 'wp-job-portal')],
];

$wpjobportal_options_pages_list = WPJOBPORTALincluder::getJSModel('postinstallation')->getPageList();

global $wp_roles;
$wpjobportal_roles = $wp_roles->get_names();
$wpjobportal_user_roles = array();
foreach ($wpjobportal_roles as $wpjobportal_key => $wpjobportal_value) {
    $wpjobportal_user_roles[] = (object) array('id' => $wpjobportal_key, 'text' => $wpjobportal_value);
}

$wpjobportal_settings_config = [

    // ========================================================
    // 1. SYSTEM & SITE
    // ========================================================
    'system_site' => [
        'label' => __('System & Site', 'wp-job-portal'),
        'icon'  => 'sliders',
        'groups' => [
            'site' => [
                'title'       => __('Site & System', 'wp-job-portal'),
                'description' => __('Core details about your job portal, its status, and default behaviors', 'wp-job-portal'),
                'fields'      => [
                    ['id' => 'title', 'label' => __('Site Title', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['title'], 'tooltip' => __('The main title for your job portal', 'wp-job-portal')],
                    ['id' => 'offline', 'label' => __('Site Offline', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['offline'], 'tooltip' => __('Take the site offline for maintenance', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'offline_text', 'label' => __('Offline Message', 'wp-job-portal'), 'type' => 'textarea', 'value' => wpjobportal::$_data[0]['offline_text'], 'tooltip' => __('The message shown to visitors when the site is offline', 'wp-job-portal')],
                    ['id' => 'default_pageid', 'label' => __('Default Page', 'wp-job-portal'), 'type' => 'select', 'value' => wpjobportal::$_data[0]['default_pageid'], 'tooltip' => __('Select the main WordPress page for the job portal', 'wp-job-portal') . '. ' . __('Email links and support icons may not work if this is not set', 'wp-job-portal'), 'options' => $wpjobportal_options_pages_list],
                    ['id' => 'date_format', 'label' => __('Date Format', 'wp-job-portal'), 'type' => 'select', 'value' => wpjobportal::$_data[0]['date_format'], 'tooltip' => __('Set the date format used throughout the plugin', 'wp-job-portal'), 'options' => $wpjobportal_options_date_format],
                    ['id' => 'defaultaddressdisplaytype', 'label' => __('Default Address Display Style', 'wp-job-portal'), 'type' => 'select', 'value' => wpjobportal::$_data[0]['defaultaddressdisplaytype'], 'tooltip' => __('Select the default format for displaying addresses', 'wp-job-portal'), 'options' => $wpjobportal_options_address_display],
                ]
            ],
            'login_registration' => [
                'title'       => __('Registration & Login', 'wp-job-portal'),
                'description' => __('Control user registration flows and login/logout redirects', 'wp-job-portal'),
                'fields'      => [
                    ['id' => 'set_register_redirect_link', 'label' => __('Set Register Link', 'wp-job-portal'), 'type' => 'select', 'value' => wpjobportal::$_data[0]['set_register_redirect_link'], 'tooltip' => __('Set the main registration page for the site', 'wp-job-portal'), 'options' => $wpjobportal_options_register_link],
                    ['id' => 'register_redirect_link', 'label' => __('Custom Register Redirect Link', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['register_redirect_link'], 'tooltip' => __('Enter the custom URL for registration if', 'wp-job-portal') . ' ' . __('Custom', 'wp-job-portal') . ' ' . __('is selected above', 'wp-job-portal')],
                    ['id' => 'set_login_redirect_link', 'label' => __('Set Login Link', 'wp-job-portal'), 'type' => 'select', 'value' => wpjobportal::$_data[0]['set_login_redirect_link'], 'tooltip' => __('Set the main login page for the site', 'wp-job-portal'), 'options' => $wpjobportal_options_login_link],
                    ['id' => 'login_redirect_link', 'label' => __('Custom Login Redirect Link', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['login_redirect_link'], 'tooltip' => __('Enter the custom URL for login if', 'wp-job-portal') . ' ' . __('Custom', 'wp-job-portal') . ' ' . __('is selected above', 'wp-job-portal')],
                    ['id' => 'loginlinkforwpuser', 'label' => __('Redirect Link For Non-Portal WP User', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['loginlinkforwpuser'], 'tooltip' => __('Redirect WordPress users who do not have a role in WP Job Portal to this link after login', 'wp-job-portal')],
                    ['id' => 'emploginlogout', 'label' => __('Show Log In/Out Button For Employer', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['emploginlogout'], 'tooltip' => __('Show login/logout button on employer control panel', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'jobsloginlogout', 'label' => __('Show Log In/Out Button For Jobseeker', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['jobsloginlogout'], 'tooltip' => __('Show login logout button in job seeker control panel', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                ]
            ],
            'terms_conditions' => [
                'title'       => __('Terms & Conditions', 'wp-job-portal'),
                'description' => __('Assign specific Terms & Conditions pages for different submission types', 'wp-job-portal'),
                'fields'      => [
                    ['id' => 'terms_and_conditions_page_company', 'label' => __('Company Terms Page', 'wp-job-portal'), 'type' => 'select', 'value' => wpjobportal::$_data[0]['terms_and_conditions_page_company'], 'tooltip' => __('Select the', 'wp-job-portal') . ' ' . __('Terms and Conditions', 'wp-job-portal') . ' ' . __('page for company submissions', 'wp-job-portal'), 'options' => $wpjobportal_options_pages_list],
                    ['id' => 'terms_and_conditions_page_job', 'label' => __('Job Terms Page', 'wp-job-portal'), 'type' => 'select', 'value' => wpjobportal::$_data[0]['terms_and_conditions_page_job'], 'tooltip' => __('Select the', 'wp-job-portal') . ' ' . __('Terms and Conditions', 'wp-job-portal') . ' ' . __('page for job submissions', 'wp-job-portal'), 'options' => $wpjobportal_options_pages_list],
                    ['id' => 'terms_and_conditions_page_resume', 'label' => __('Resume Terms Page', 'wp-job-portal'), 'type' => 'select', 'value' => wpjobportal::$_data[0]['terms_and_conditions_page_resume'], 'tooltip' => __('Select the', 'wp-job-portal') . ' ' . __('Terms and Conditions', 'wp-job-portal') . ' ' . __('page for resume submissions', 'wp-job-portal'), 'options' => $wpjobportal_options_pages_list],
                ]
            ],
        ]
    ],

    // ========================================================
    // 2. CONTENT & DISPLAY
    // ========================================================
    'content_display' => [
        'label' => __('Content & Display', 'wp-job-portal'),
        'icon'  => 'palette',
        'groups' => [
            'taxonomy' => [
                'title'       => __('Taxonomy & Categories', 'wp-job-portal'),
                'description' => __('Manage how content like categories and tags are handled', 'wp-job-portal'),
                'fields'      => [
                    ['id' => 'job_resume_show_all_categories', 'label' => __('Show All Categories', 'wp-job-portal') . ' ' . __('Even if Empty', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['job_resume_show_all_categories'], 'tooltip' => __('If no is selected then only categories that have jobs or resumes will be shown', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'categories_colsperrow', 'label' => __('Categories Per Row', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['categories_colsperrow'], 'tooltip' => __('Number of categories to display per row on category listing pages', 'wp-job-portal')],
                    ['id' => 'subcategory_limit', 'label' => __('Sub-categories Limit', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['subcategory_limit'], 'tooltip' => __('How many sub-categories to show in the popup on category pages', 'wp-job-portal')],
                    ['id' => 'newtyped_tags', 'label' => __('User Can Add New Tags', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['newtyped_tags'], 'tooltip' => __('Allow users to create new tags in the system when posting', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno, 'pro' => ['slug' => 'tag', 'name' => __('Tag', 'wp-job-portal')]],
                    ['id' => 'pagination_default_page_size', 'label' => __('Default Pagination Size', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['pagination_default_page_size'], 'tooltip' => __('Maximum number of records to show per page', 'wp-job-portal')],
                    ['id' => 'cur_location', 'label' => __('Show Breadcrumbs', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['cur_location'], 'tooltip' => __('Show navigation in breadcrumbs', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'show_wpjobportal_page_title', 'label' => __('Show WP Job Portal Page Title', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['show_wpjobportal_page_title'], 'tooltip' => __('Show page title above wpjobportal breadcrumbs', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                ]
            ],
            'uploads' => [
                'title'       => __('File Uploads & Defaults', 'wp-job-portal'),
                'description' => __('Manage file uploads and default placeholder images', 'wp-job-portal'),
                'fields'      => [
                    ['id' => 'data_directory', 'label' => __('Data Directory', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['data_directory'], 'tooltip' => __('System will upload all user files into this folder within the WordPress uploads directory', 'wp-job-portal')],
                    ['id' => 'default_image', 'label' => __('Default Image', 'wp-job-portal'), 'type' => 'file', 'value' => wpjobportal::$_data[0]['default_image'], 'tooltip' => __('This image will be shown as the default for entities & users if no other image is provided', 'wp-job-portal')],
                    ['id' => 'image_file_type', 'label' => __('Allowed Image Extensions', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['image_file_type'], 'tooltip' => __('Comma-separated list of allowed image file extensions', 'wp-job-portal') . ' e.g. jpg,png,gif'],
                    ['id' => 'image_file_size', 'label' => __('Max Image Size', 'wp-job-portal') . ' KB', 'type' => 'text', 'value' => wpjobportal::$_data[0]['image_file_size'], 'tooltip' => __('Maximum file size for general image uploads in kilobytes', 'wp-job-portal')],
                ]
            ],
        ]
    ],

    // ========================================================
    // 3. URL & SEO
    // ========================================================
    'url_seo' => [
        'label' => __('URL & SEO', 'wp-job-portal'),
        'icon'  => 'link',
        'groups' => [
            'structure' => [
                'title'       => __('URL Structure', 'wp-job-portal'),
                'description' => __('Configure the URL structure', 'wp-job-portal') . ' ' . __('permalinks', 'wp-job-portal') . ' ' . __('for detail pages', 'wp-job-portal'),
                'fields'      => [
                    [
                        'id' => 'job_seo',
                        'label' => __('Job Detail URL Structure', 'wp-job-portal'),
                        'type' => 'seo_tags',
                        'value' => wpjobportal::$_data[0]['job_seo'],
                        'available_tags' => ['title', 'company', 'category', 'location', 'jobtype'],
                        'tooltip' => __('Click on the available tags below to build the URL structure for job detail pages', 'wp-job-portal')
                    ],
                    [
                        'id' => 'company_seo',
                        'label' => __('Company Detail URL Structure', 'wp-job-portal'),
                        'type' => 'seo_tags',
                        'value' => wpjobportal::$_data[0]['company_seo'],
                        'available_tags' => ['name', 'category', 'location'],
                        'tooltip' => __('Click on the available tags below to build the URL structure for company detail pages', 'wp-job-portal')
                    ],
                    [
                        'id' => 'resume_seo',
                        'label' => __('Resume Detail URL Structure', 'wp-job-portal'),
                        'type' => 'seo_tags',
                        'value' => wpjobportal::$_data[0]['resume_seo'],
                        'available_tags' => ['title', 'category', 'location'],
                        'tooltip' => __('Click on the available tags below to build the URL structure for resume detail pages', 'wp-job-portal')
                    ],
                ]
            ]
        ]
    ],

    // ========================================================
    // 4. EMPLOYERS
    // ========================================================
    'employers' => [
        'label' => __('Employers', 'wp-job-portal'),
        'icon'  => 'user-tie',
        'groups' => [
            'registration' => [
                'title'       => __('Registration & Access', 'wp-job-portal'),
                'description' => __("Settings related to employer accounts, registration, and permissions", 'wp-job-portal'),
                'fields'      => [
                    ['id' => 'disable_employer', 'label' => __('Enable Employer Area', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['disable_employer'], 'tooltip' => __('If no then front end employer area is not accessible', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'showemployerlink', 'label' => __('Allow Registration as Employer', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['showemployerlink'], 'tooltip' => __('Show the option to register as an employer on the registration form', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'employer_defaultgroup', 'label' => __('Employer Default Role', 'wp-job-portal'), 'type' => 'select', 'value' => wpjobportal::$_data[0]['employer_defaultgroup'], 'tooltip' => __('This role will auto assign to new employer', 'wp-job-portal'), 'options' => $wpjobportal_user_roles],
                    ['id' => 'employerview_js_controlpanel', 'label' => __('Employer Can View Job Seeker Area', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['employerview_js_controlpanel'], 'tooltip' => __('Allow logged-in employers to view the job seeker control panel', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'employe_set_register_link', 'label' => __('Set Employer After-Register Link', 'wp-job-portal'), 'type' => 'select', 'value' => wpjobportal::$_data[0]['employe_set_register_link'], 'tooltip' => __('New Employer Set After Register link redirect page', 'wp-job-portal'), 'options' => $wpjobportal_options_default_custom],
                    ['id' => 'register_employer_redirect_page', 'label' => __('Employer After-Registration Page', 'wp-job-portal'), 'type' => 'select', 'value' => wpjobportal::$_data[0]['register_employer_redirect_page'], 'tooltip' => __('whenever anyone registers as employer he will be redirected to this page', 'wp-job-portal'), 'options' => $wpjobportal_options_pages_list],
                    ['id' => 'employe_register_link', 'label' => __('Employer Custom After-Registration Link', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['employe_register_link'], 'tooltip' => __('Custom Empolyer After Regitser Link', 'wp-job-portal')],
                ]
            ],
            'dashboard_layout' => [
                'title'       => __('Dashboard', 'wp-job-portal') . ' ' . __('Layout & Widgets', 'wp-job-portal'),
                'description' => __('Control the visibility of main sections and widgets in the logged-in employer dashboard', 'wp-job-portal'),
                'fields'      => [
                    ['id' => 'employer_profile_section', 'label' => __('Show Profile Section', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['employer_profile_section'], 'tooltip' => __("Show or hide the profile section on the employer's control panel", 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'employerstatboxes', 'label' => __('Show Stat Boxes', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['employerstatboxes'], 'tooltip' => __("Show statistics boxes on the employer's dashboard", 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'employerresumebox', 'label' => __('Show Recent Resumes Box', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['employerresumebox'], 'tooltip' => __("Show a box with recent resumes on the employer's dashboard", 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'jobs_graph', 'label' => __('Show Jobs Graph for Employer', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['jobs_graph'], 'tooltip' => __("Show a graph of job statistics in the employer's dashboard", 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    //['id' => 'temp_employer_dashboard_stats_graph', 'label' => __('Show Stats Graph', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['temp_employer_dashboard_stats_graph'], 'tooltip' => __('Setting to show the stats graph on the employer dashboard', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    //['id' => 'temp_employer_dashboard_useful_links', 'label' => __('Show Useful Links', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['temp_employer_dashboard_useful_links'], 'tooltip' => __('Setting to show the useful links section', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'temp_employer_dashboard_applied_resume', 'label' => __('Show Applied Resumes', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['temp_employer_dashboard_applied_resume'], 'tooltip' => __('Setting to show the applied resumes section', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    //['id' => 'temp_employer_dashboard_saved_search', 'label' => __('Show Saved Searches', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['temp_employer_dashboard_saved_search'], 'tooltip' => __('Setting to show the saved searches section', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    //['id' => 'temp_employer_dashboard_newest_resume', 'label' => __('Show Newest Resumes', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['temp_employer_dashboard_newest_resume'], 'tooltip' => __('Setting to show the newest resumes section', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'show_employer_dashboard_invoices', 'label' => __('Show Employer Dashboard Invoices', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['show_employer_dashboard_invoices'], 'tooltip' => __("Show Invoices on the employer's dashboard", 'wp-job-portal'), 'options' => $wpjobportal_options_showhide , 'pro' => ['slug' => 'credits', 'name' => __('Credits', 'wp-job-portal')]],
                ]
            ],
            'dashboard_links' => [
                'title'       => __('Dashboard', 'wp-job-portal') . ' ' . __('Navigation Links', 'wp-job-portal'),
                'description' => __('Control which navigation links are visible in the logged-in employer dashboard menu', 'wp-job-portal'),
                'fields'      => [
                    ['id' => 'mycompanies', 'label' => __('Show', 'wp-job-portal') . ' ' . __('My Companies', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['mycompanies'], 'tooltip' => __('Show the', 'wp-job-portal') . ' ' . __('My Companies', 'wp-job-portal') . ' ' . __('link', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'formcompany', 'label' => __('Show', 'wp-job-portal') . ' ' . __('Add Company', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['formcompany'], 'tooltip' => __('Show the', 'wp-job-portal') . ' ' . __('Add Company', 'wp-job-portal') . ' ' . __('link', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'myjobs', 'label' => __('Show', 'wp-job-portal') . ' ' . __('My Jobs', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['myjobs'], 'tooltip' => __('Show the', 'wp-job-portal') . ' ' . __('My Jobs', 'wp-job-portal') . ' ' . __('link', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'formjob', 'label' => __('Show', 'wp-job-portal') . ' ' . __('Add Job', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['formjob'], 'tooltip' => __('Show the', 'wp-job-portal') . ' ' . __('Add Job', 'wp-job-portal') . ' ' . __('link', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'mydepartment', 'label' => __('Show', 'wp-job-portal') . ' ' . __('My Departments', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['mydepartment'], 'tooltip' => __('Show the', 'wp-job-portal') . ' ' . __('My Departments', 'wp-job-portal') . ' ' . __('link', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide, 'pro' => ['slug' => 'departments', 'name' => __('Departments', 'wp-job-portal')]],
                    ['id' => 'formdepartment', 'label' => __('Show', 'wp-job-portal') . ' ' . __('Add Department', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['formdepartment'], 'tooltip' => __('Show the', 'wp-job-portal') . ' ' . __('Add Department', 'wp-job-portal') . ' ' . __('link', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide, 'pro' => ['slug' => 'departments', 'name' => __('Departments', 'wp-job-portal')]],
                    ['id' => 'myfolders', 'label' => __('Show', 'wp-job-portal') . ' ' . __('My Folders', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['myfolders'], 'tooltip' => __('Show the', 'wp-job-portal') . ' ' . __('My Folders', 'wp-job-portal') . ' ' . __('link', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide, 'pro' => ['slug' => 'folder', 'name' => __('Folder', 'wp-job-portal')]],
                    ['id' => 'newfolders', 'label' => __('Show', 'wp-job-portal') . ' ' . __('Add Folder', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['newfolders'], 'tooltip' => __('Show the', 'wp-job-portal') . ' ' . __('Add Folder', 'wp-job-portal') . ' ' . __('link', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide, 'pro' => ['slug' => 'folder', 'name' => __('Folder', 'wp-job-portal')]],
                    ['id' => 'resumesearch', 'label' => __('Show', 'wp-job-portal') . ' ' . __('Resume Search', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['resumesearch'], 'tooltip' => __('Show the', 'wp-job-portal') . ' ' . __('Resume Search', 'wp-job-portal') . ' ' . __('link', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'my_resumesearches', 'label' => __('Show Saved Searches for Employers', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['my_resumesearches'], 'tooltip' => __('Allow employers to save their resume searches', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide, 'pro' => ['slug' => 'resumesearch', 'name' => __('Resume Search', 'wp-job-portal')]],
                    ['id' => 'resumebycategory', 'label' => __('Show', 'wp-job-portal') . ' ' . __('Resume By Categories', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['resumebycategory'], 'tooltip' => __('Show the', 'wp-job-portal') . ' ' . __('Resume By Categories', 'wp-job-portal') . ' ' . __('link', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'empmessages', 'label' => __('Show', 'wp-job-portal') . ' ' . __('Messages', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['empmessages'], 'tooltip' => __('Show the', 'wp-job-portal') . ' ' . __('Messages', 'wp-job-portal') . ' ' . __('link in the employer dashboard', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide, 'pro' => ['slug' => 'message', 'name' => __('Message', 'wp-job-portal')]],
                    ['id' => 'empresume_rss', 'label' => __('Show', 'wp-job-portal') . ' ' . __('Resume RSS', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['empresume_rss'], 'tooltip' => __('Show the', 'wp-job-portal') . ' ' . __('Resume RSS', 'wp-job-portal') . ' ' . __('link in the employer dashboard', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide, 'pro' => ['slug' => 'rssfeedback', 'name' => __('RSS Feedback', 'wp-job-portal')]],
                    ['id' => 'empregister', 'label' => __('Show', 'wp-job-portal') . ' ' . __('Register', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['empregister'], 'tooltip' => __('Show the', 'wp-job-portal') . ' ' . __('Register', 'wp-job-portal') . ' ' . __('link in the employer dashboard', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                ]
            ]
        ]
    ],

    // ========================================================
    // 5. JOB SEEKERS
    // ========================================================
    'job_seekers' => [
        'label' => __('Job Seekers', 'wp-job-portal'),
        'icon'  => 'user-graduate',
        'groups' => [
            'registration' => [
                'title'       => __('Registration & Access', 'wp-job-portal'),
                'description' => __("Settings related to job seeker accounts and registration", 'wp-job-portal'),
                'fields'      => [
                    ['id' => 'jobseeker_defaultgroup', 'label' => __('Job Seeker Default Role', 'wp-job-portal'), 'type' => 'select', 'value' => wpjobportal::$_data[0]['jobseeker_defaultgroup'], 'tooltip' => __('This role will auto assign to new job seeker', 'wp-job-portal'), 'options' => $wpjobportal_user_roles],
                    ['id' => 'jobseeker_set_register_link', 'label' => __('Set Job Seeker After-Register Link', 'wp-job-portal'), 'type' => 'select', 'value' => wpjobportal::$_data[0]['jobseeker_set_register_link'], 'tooltip' => __('New Job seeker Set After Register link redirect page', 'wp-job-portal'), 'options' => $wpjobportal_options_default_custom],
                    ['id' => 'register_jobseeker_redirect_page', 'label' => __('Job Seeker After-Registration Page', 'wp-job-portal'), 'type' => 'select', 'value' => wpjobportal::$_data[0]['register_jobseeker_redirect_page'], 'tooltip' => __('whenever anyone registers as job seeker, he will be redirected to this page', 'wp-job-portal'), 'options' => $wpjobportal_options_pages_list],
                    ['id' => 'jobseeker_register_link', 'label' => __('Job Seeker Custom After-Registration Link', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['jobseeker_register_link'], 'tooltip' => __('Custom Job seeker After Regitser Link', 'wp-job-portal')],
                    ['id' => 'allow_jobshortlist', 'label' => __('Enable Job Shortlisting', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['allow_jobshortlist'], 'tooltip' => __('Allow job seekers to shortlist jobs', 'wp-job-portal') . '. ' . __('This affects the job detail page', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'allow_tellafriend', 'label' => __('Enable Tell a Friend', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['allow_tellafriend'], 'tooltip' => __('Enable the', 'wp-job-portal') . ' ' . __('Tell a Friend', 'wp-job-portal') . ' ' . __('feature on job listing pages', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                ]
            ],
            'dashboard_layout' => [
                'title'       => __('Dashboard', 'wp-job-portal') . ' ' . __('Layout & Widgets', 'wp-job-portal'),
                'description' => __('Control the visibility of main sections and widgets in the logged-in job seeker dashboard', 'wp-job-portal'),
                'fields'      => [
                    ['id' => 'job_seeker_profile_section', 'label' => __('Show Profile Section', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['job_seeker_profile_section'], 'tooltip' => __("Show or hide the profile section on the job seeker's control panel", 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'jobseekerstatboxes', 'label' => __('Show Stat Boxes', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['jobseekerstatboxes'], 'tooltip' => __("Show statistics boxes on the job seeker's dashboard", 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'jobseekerjobapply', 'label' => __('Show Applied Jobs Box', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['jobseekerjobapply'], 'tooltip' => __("Show a box with applied jobs on the job seeker's dashboard", 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'jobseeker_show_resume_status_section', 'label' => __('Show Resume Status Section', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['jobseeker_show_resume_status_section'], 'tooltip' => __("Show resume status section on the job seeker's dashboard", 'wp-job-portal'), 'options' => $wpjobportal_options_showhide , 'pro' => ['slug' => 'advanceresumebuilder', 'name' => __('Advance Resume Builder', 'wp-job-portal')]],
                    ['id' => 'jobseekernewestjobs', 'label' => __('Show Newest Jobs Box', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['jobseekernewestjobs'], 'tooltip' => __("Show a box with newest jobs on the job seeker's dashboard", 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    //['id' => 'temp_jobseeker_dashboard_useful_links', 'label' => __('Show Useful Links', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['temp_jobseeker_dashboard_useful_links'], 'tooltip' => __('Setting to show the useful links section', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'temp_jobseeker_dashboard_apllied_jobs', 'label' => __('Show Applied Jobs', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['temp_jobseeker_dashboard_apllied_jobs'], 'tooltip' => __('Setting to show the applied jobs section', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    //['id' => 'temp_jobseeker_dashboard_shortlisted_jobs', 'label' => __('Show Shortlisted Jobs', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['temp_jobseeker_dashboard_shortlisted_jobs'], 'tooltip' => __('Setting to show the shortlisted jobs section', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide, 'pro' => ['slug' => 'shortlist', 'name' => __('Shortlist', 'wp-job-portal')]],
                    ['id' => 'temp_jobseeker_dashboard_newest_jobs', 'label' => __('Show Newest Jobs', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['temp_jobseeker_dashboard_newest_jobs'], 'tooltip' => __('Setting to show the newest jobs section', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'jsactivejobs_graph', 'label' => __('Show Active Jobs Graph for Job Seeker', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['jsactivejobs_graph'], 'tooltip' => __("Show a graph of job statistics in the job seeker's dashboard", 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'show_jobseeker_dashboard_invoices', 'label' => __('Show Job Seeker Dashboard Invoices', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['show_jobseeker_dashboard_invoices'], 'tooltip' => __("Show Invoices on the job seeker's dashboard", 'wp-job-portal'), 'options' => $wpjobportal_options_showhide , 'pro' => ['slug' => 'credits', 'name' => __('Credits', 'wp-job-portal')]],
                ]
            ],
            'dashboard_links' => [
                'title'       => __('Dashboard', 'wp-job-portal') . ' ' . __('Navigation Links', 'wp-job-portal'),
                'description' => __('Control which navigation links are visible in the logged-in job seeker dashboard menu', 'wp-job-portal'),
                'fields'      => [
                    ['id' => 'myresumes', 'label' => __('Show', 'wp-job-portal') . ' ' . __('My Resumes', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['myresumes'], 'tooltip' => __('Show the', 'wp-job-portal') . ' ' . __('My Resumes', 'wp-job-portal') . ' ' . __('link', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'formresume', 'label' => __('Show', 'wp-job-portal') . ' ' . __('Add Resume', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['formresume'], 'tooltip' => __('Show the', 'wp-job-portal') . ' ' . __('Add Resume', 'wp-job-portal') . ' ' . __('link', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'myappliedjobs', 'label' => __('Show', 'wp-job-portal') . ' ' . __('My Applied Jobs', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['myappliedjobs'], 'tooltip' => __('Show the', 'wp-job-portal') . ' ' . __('My Applied Jobs', 'wp-job-portal') . ' ' . __('link', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'listjobshortlist', 'label' => __('Show', 'wp-job-portal') . ' ' . __('Short Listed Jobs', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['listjobshortlist'], 'tooltip' => __('Show the', 'wp-job-portal') . ' ' . __('Short Listed Jobs', 'wp-job-portal') . ' ' . __('link', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide, 'pro' => ['slug' => 'shortlist', 'name' => __('Shortlist', 'wp-job-portal')]],
                    ['id' => 'mycoverletter', 'label' => __('Show', 'wp-job-portal') . ' ' . __('My Cover Letters', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['mycoverletter'], 'tooltip' => __('Show the', 'wp-job-portal') . ' ' . __('My Cover Letters', 'wp-job-portal') . ' ' . __('link', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide, 'pro' => ['slug' => 'coverletter', 'name' => __('Cover Letter', 'wp-job-portal')]],
                    ['id' => 'formcoverletter', 'label' => __('Show', 'wp-job-portal') . ' ' . __('Add Cover Letter', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['formcoverletter'], 'tooltip' => __('Show the', 'wp-job-portal') . ' ' . __('Add Cover Letter', 'wp-job-portal') . ' ' . __('link', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide, 'pro' => ['slug' => 'coverletter', 'name' => __('Cover Letter', 'wp-job-portal')]],
                    ['id' => 'listallcompanies', 'label' => __('Show', 'wp-job-portal') . ' ' . __('All Companies', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['listallcompanies'], 'tooltip' => __('Show the', 'wp-job-portal') . ' ' . __('All Companies', 'wp-job-portal') . ' ' . __('link', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide, 'pro' => ['slug' => 'multicompany', 'name' => __('Multi Company', 'wp-job-portal')]],
                    ['id' => 'jobcat', 'label' => __('Show', 'wp-job-portal') . ' ' . __('Jobs By Categories', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['jobcat'], 'tooltip' => __('Show the', 'wp-job-portal') . ' ' . __('Jobs By Categories', 'wp-job-portal') . ' ' . __('link in the job seeker dashboard', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'listnewestjobs', 'label' => __('Show', 'wp-job-portal') . ' ' . __('Newest Jobs', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['listnewestjobs'], 'tooltip' => __('Show the', 'wp-job-portal') . ' ' . __('Newest Jobs', 'wp-job-portal') . ' ' . __('link in the job seeker dashboard', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'listjobbytype', 'label' => __('Show', 'wp-job-portal') . ' ' . __('Jobs By Types', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['listjobbytype'], 'tooltip' => __('Show the', 'wp-job-portal') . ' ' . __('Jobs By Types', 'wp-job-portal') . ' ' . __('link in the job seeker dashboard', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'jobsbycities', 'label' => __('Show', 'wp-job-portal') . ' ' . __('Jobs By Cities', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['jobsbycities'], 'tooltip' => __('Show the', 'wp-job-portal') . ' ' . __('Jobs By Cities', 'wp-job-portal') . ' ' . __('link in the job seeker dashboard', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'jobsearch', 'label' => __('Show', 'wp-job-portal') . ' ' . __('Search Job', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['jobsearch'], 'tooltip' => __('Show the', 'wp-job-portal') . ' ' . __('Search Job', 'wp-job-portal') . ' ' . __('link in the job seeker dashboard', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'jsmessages', 'label' => __('Show', 'wp-job-portal') . ' ' . __('Messages', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['jsmessages'], 'tooltip' => __('Show the', 'wp-job-portal') . ' ' . __('Messages', 'wp-job-portal') . ' ' . __('link in the job seeker dashboard', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'wpjobportal_rss', 'label' => __('Show', 'wp-job-portal') . ' ' . __('Jobs RSS', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['wpjobportal_rss'], 'tooltip' => __('Show the', 'wp-job-portal') . ' ' . __('Jobs RSS', 'wp-job-portal') . ' ' . __('link in the job seeker dashboard', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'jsregister', 'label' => __('Show', 'wp-job-portal') . ' ' . __('Register', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['jsregister'], 'tooltip' => __('Show the', 'wp-job-portal') . ' ' . __('Register', 'wp-job-portal') . ' ' . __('link in the job seeker dashboard', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                ]
            ],
        ]
    ],

    // ========================================================
    // 6. VISITORS
    // ========================================================
    // ========================================================
    // 6. VISITORS
    // ========================================================
    'visitors' => [
         'label' => __('Visitors', 'wp-job-portal'),
         'icon'  => 'user-secret',
         'groups' => [
                 'permissions' => [
                         'title'                =>  __('General Permissions', 'wp-job-portal'),
                         'description' => __('Define what non-logged-in users are allowed to do on the site', 'wp-job-portal'),
                         'fields'            => [
                                 ['id' => 'visitor_can_post_job', 'label' => __('Visitor Can Post Job', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['visitor_can_post_job'], 'tooltip' => __('Allow non-logged-in users to post jobs', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno, 'pro' => ['slug' => 'visitorcanaddjob', 'name' => __('Visitor Can Add Job', 'wp-job-portal')]],
                                 ['id' => 'visitor_add_job_redirect_page', 'label' => __('Visitor Job Post Redirect Page', 'wp-job-portal'), 'type' => 'select', 'value' => wpjobportal::$_data[0]['visitor_add_job_redirect_page'], 'tooltip' => __('A visitor will be redirected to this page after posting a job', 'wp-job-portal'), 'options' => $wpjobportal_options_pages_list, 'pro' => ['slug' => 'visitorcanaddjob', 'name' => __('Visitor Can Add Job', 'wp-job-portal')]],
                                 ['id' => 'visitor_can_apply_to_job', 'label' => __('Visitor Can Apply to Job', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['visitor_can_apply_to_job'], 'tooltip' => __('Allow non-logged-in users to apply for jobs', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno, 'pro' => ['slug' => 'visitorapplyjob', 'name' => __('Visitor Apply Job', 'wp-job-portal')]],
                                 ['id' => 'visitor_can_add_resume', 'label' => __('Visitor Can Add Resume', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['visitor_can_add_resume'], 'tooltip' => __('Allow non-logged-in users to add a resume', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno, 'pro' => ['slug' => 'visitorapplyjob', 'name' => __('Visitor Apply Job', 'wp-job-portal')]],
                                 ['id' => 'visitor_add_resume_redirect_page', 'label' => __('Visitor Resume Post Redirect Page', 'wp-job-portal'), 'type' => 'select', 'value' => wpjobportal::$_data[0]['visitor_add_resume_redirect_page'], 'tooltip' => __('A visitor will be redirected to this page after posting a resume', 'wp-job-portal'), 'options' => $wpjobportal_options_pages_list, 'pro' => ['slug' => 'visitorapplyjob', 'name' => __('Visitor Apply Job', 'wp-job-portal')]],
                                 ['id' => 'visitor_can_edit_job', 'label' => __('Visitor Can Edit Job', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['visitor_can_edit_job'], 'tooltip' => __('Allow non-logged-in users to edit jobs they have posted', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno, 'pro' => ['slug' => 'visitorcanaddjob', 'name' => __('Visitor Can Add Job', 'wp-job-portal')]],
                         ]
                 ],
                 'page_access' => [
                         'title'                =>  __('Content Access', 'wp-job-portal'),
                         'description' => __('Control which pages and content areas are visible to non-logged-in users', 'wp-job-portal'),
                         'fields'            => [
                                 ['id' => 'visitorview_emp_conrolpanel', 'label' => __("Visitor Can View Employer's Control Panel Links", 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['visitorview_emp_conrolpanel'], 'tooltip' => __('Allow visitors to view the employer control panel links', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                                 ['id' => 'visitorview_js_controlpanel', 'label' => __("Visitor Can View Job Seeker's Control Panel Links", 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['visitorview_js_controlpanel'], 'tooltip' => __('Allow visitors to view the job seeker control panel links', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                                 ['id' => 'visitorview_emp_viewcompany', 'label' => __("Visitor Can View Company Details", 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['visitorview_emp_viewcompany'], 'tooltip' => __('Allow visitors to view company detail pages', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                                 ['id' => 'visitorview_emp_viewjob', 'label' => __("Visitor Can View Job Details", 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['visitorview_emp_viewjob'], 'tooltip' => __('Allow visitors to view job detail pages', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                                 ['id' => 'visitorview_emp_viewresume', 'label' => __("Visitor Can View Resume Details", 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['visitorview_emp_viewresume'], 'tooltip' => __('Allow visitors to view resume detail pages', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                                 ['id' => 'visitorview_emp_resumecat', 'label' => __("Visitor Can View Resume Categories", 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['visitorview_emp_resumecat'], 'tooltip' => __('Allow visitors to view the resume categories page', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                                 ['id' => 'visitorview_emp_resumesearch', 'label' => __("Visitor Can Search Resumes", 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['visitorview_emp_resumesearch'], 'tooltip' => __('Allow visitors to access the resume search functionality', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide, 'pro' => ['slug' => 'resumesearch', 'name' => __('Resume Search', 'wp-job-portal')]],
                                 ['id' => 'visitorview_js_jobcat', 'label' => __("Visitor Can View Jobs by Categories", 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['visitorview_js_jobcat'], 'tooltip' => __('Allow visitors to view the jobs by category page', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                                 ['id' => 'visitorview_js_jobsearch', 'label' => __("Visitor Can Search Jobs", 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['visitorview_js_jobsearch'], 'tooltip' => __('Allow visitors to use the job search functionality', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                                 ['id' => 'visitorview_js_jobsearchresult', 'label' => __("Visitor Can View Job Search Results", 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['visitorview_js_jobsearchresult'], 'tooltip' => __('Allow visitors to see job search result listings', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                                 ['id' => 'visitorview_js_newestjobs', 'label' => __("Visitor Can View Newest Jobs", 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['visitorview_js_newestjobs'], 'tooltip' => __('Allow visitors to see the newest jobs page', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                         ]
                 ],
                 'employer_dashboard_links' => [
                         'title'                => __("Employer Links", 'wp-job-portal'),
                         'description' => __('Control which employer dashboard navigation links are visible to visitors', 'wp-job-portal'),
                         'fields'            => [
                                 ['id' => 'vis_emmycompanies', 'label' => __("Visitor Show", 'wp-job-portal') . ' ' . __('My Companies', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_emmycompanies'], 'options' => $wpjobportal_options_showhide],
                                 ['id' => 'vis_emformcompany', 'label' => __("Visitor Show", 'wp-job-portal') . ' ' . __('Add Company', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_emformcompany'], 'options' => $wpjobportal_options_showhide],
                                 ['id' => 'vis_emmyjobs', 'label' => __("Visitor Show", 'wp-job-portal') . ' ' . __('My Jobs', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_emmyjobs'], 'options' => $wpjobportal_options_showhide],
                                 ['id' => 'vis_emformjob', 'label' => __("Visitor Show", 'wp-job-portal') . ' ' . __('Add Job', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_emformjob'], 'options' => $wpjobportal_options_showhide],
                                 ['id' => 'vis_emmydepartment', 'label' => __("Visitor Show", 'wp-job-portal') . ' ' . __('My Departments', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_emmydepartment'], 'options' => $wpjobportal_options_showhide, 'pro' => ['slug' => 'departments', 'name' => __('Departments', 'wp-job-portal')]],
                                 ['id' => 'vis_emformdepartment', 'label' => __("Visitor Show", 'wp-job-portal') . ' ' . __('Add Department', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_emformdepartment'], 'options' => $wpjobportal_options_showhide, 'pro' => ['slug' => 'departments', 'name' => __('Departments', 'wp-job-portal')]],
                                 ['id' => 'vis_emmyfolders', 'label' => __("Visitor Show", 'wp-job-portal') . ' ' . __('My Folders', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_emmyfolders'], 'options' => $wpjobportal_options_showhide, 'pro' => ['slug' => 'folder', 'name' => __('Folder', 'wp-job-portal')]],
                                 ['id' => 'vis_emnewfolders', 'label' => __("Visitor Show", 'wp-job-portal') . ' ' . __('Add Folder', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_emnewfolders'], 'options' => $wpjobportal_options_showhide, 'pro' => ['slug' => 'folder', 'name' => __('Folder', 'wp-job-portal')]],
                                 ['id' => 'vis_emresumesearch', 'label' => __("Visitor Show", 'wp-job-portal') . ' ' . __('Resume Search', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_emresumesearch'], 'options' => $wpjobportal_options_showhide],
                                 ['id' => 'vis_emresumebycategory', 'label' => __("Visitor Show", 'wp-job-portal') . ' ' . __('Resume By Categories', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_emresumebycategory'], 'options' => $wpjobportal_options_showhide],
                                 ['id' => 'vis_emmessages', 'label' => __("Visitor Show", 'wp-job-portal') . ' ' . __('Messages', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_emmessages'], 'options' => $wpjobportal_options_showhide, 'pro' => ['slug' => 'message', 'name' => __('Message', 'wp-job-portal')]],
                                 ['id' => 'vis_resume_rss', 'label' => __("Visitor Show", 'wp-job-portal') . ' ' . __('Resume RSS', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_resume_rss'], 'options' => $wpjobportal_options_showhide, 'pro' => ['slug' => 'rssfeedback', 'name' => __('RSS Feedback', 'wp-job-portal')]],
                                 ['id' => 'vis_emempregister', 'label' => __("Visitor Show", 'wp-job-portal') . ' ' . __('Register', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_emempregister'], 'options' => $wpjobportal_options_showhide],
                                 ['id' => 'vis_empcredits', 'label' => __("Visitor Show", 'wp-job-portal') . ' ' . __('Packages', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_empcredits'], 'options' => $wpjobportal_options_showhide, 'pro' => ['slug' => 'credits', 'name' => __('Credits', 'wp-job-portal')]],
                                 ['id' => 'vis_empcreditlog', 'label' => __("Visitor Show", 'wp-job-portal') . ' ' . __('My Packages', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_empcreditlog'], 'options' => $wpjobportal_options_showhide, 'pro' => ['slug' => 'credits', 'name' => __('Credits', 'wp-job-portal')]],
                                 ['id' => 'vis_emppurchasehistory', 'label' => __("Visitor Show", 'wp-job-portal') . ' ' . __('Invoice', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_emppurchasehistory'], 'options' => $wpjobportal_options_showhide, 'pro' => ['slug' => 'credits', 'name' => __('Credits', 'wp-job-portal')]],
                                 ['id' => 'vis_empratelist', 'label' => __("Visitor Show", 'wp-job-portal') . ' ' . __('My Subscriptions', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_empratelist'], 'options' => $wpjobportal_options_showhide, 'pro' => ['slug' => 'credits', 'name' => __('Credits', 'wp-job-portal')]],
                         ]
                 ],
                 'employer_dashboard_widgets' => [
                         'title'                => __("Employer Widgets", 'wp-job-portal'),
                         'description' => __('Control which employer dashboard widgets and sections are visible to visitors', 'wp-job-portal'),
                         'fields'            => [
                                 ['id' => 'vis_jobs_graph', 'label' => __('Visitor Show Jobs Graph', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_jobs_graph'], 'tooltip' => __("Show a graph of job statistics in the employer's dashboard to visitor", 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                                 ['id' => 'vis_ememployerresumebox', 'label' => __('Visitor Show Recent Resumes Box', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_ememployerresumebox'], 'tooltip' => __("Show a box with recent resumes on the employer's dashboard to visitor", 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                                 //['id' => 'vis_temp_employer_dashboard_stats_graph', 'label' => __('Visitor Show Stats Graph Section', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_temp_employer_dashboard_stats_graph'], 'options' => $wpjobportal_options_showhide],
                                 //['id' => 'vis_temp_employer_dashboard_useful_links', 'label' => __('Visitor Show Useful Links Section', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_temp_employer_dashboard_useful_links'], 'options' => $wpjobportal_options_showhide],
                                 ['id' => 'vis_temp_employer_dashboard_applied_resume', 'label' => __('Visitor Show Applied Resumes Section', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_temp_employer_dashboard_applied_resume'], 'options' => $wpjobportal_options_showhide],
                                 //['id' => 'vis_temp_employer_dashboard_saved_search', 'label' => __('Visitor Show Saved Searches Section', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_temp_employer_dashboard_saved_search'], 'options' => $wpjobportal_options_showhide],
                                 //['id' => 'vis_temp_employer_dashboard_purchase_history', 'label' => __('Visitor Show Purchase History Section', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_temp_employer_dashboard_purchase_history'], 'options' => $wpjobportal_options_showhide],
                                 //['id' => 'vis_temp_employer_dashboard_newest_resume', 'label' => __('Visitor Show Newest Resumes Section', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_temp_employer_dashboard_newest_resume'], 'options' => $wpjobportal_options_showhide],
                         ]
                 ],
                 'jobseeker_dashboard_links' => [
                         'title'                => __("Job Seeker Links", 'wp-job-portal'),
                         'description' => __('Control which job seeker dashboard navigation links are visible to visitors', 'wp-job-portal'),
                         'fields'            => [
                                 ['id' => 'vis_jsmyresumes', 'label' => __("Visitor Show", 'wp-job-portal') . ' ' . __('My Resumes', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_jsmyresumes'], 'options' => $wpjobportal_options_showhide],
                                 ['id' => 'vis_jsformresume', 'label' => __("Visitor Show", 'wp-job-portal') . ' ' . __('Add Resume', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_jsformresume'], 'options' => $wpjobportal_options_showhide],
                                 ['id' => 'vis_jsmyappliedjobs', 'label' => __("Visitor Show", 'wp-job-portal') . ' ' . __('My Applied Jobs', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_jsmyappliedjobs'], 'options' => $wpjobportal_options_showhide],
                                 ['id' => 'vis_jslistjobshortlist', 'label' => __("Visitor Show", 'wp-job-portal') . ' ' . __('Short Listed Jobs', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_jslistjobshortlist'], 'options' => $wpjobportal_options_showhide, 'pro' => ['slug' => 'shortlist', 'name' => __('Shortlist', 'wp-job-portal')]],
                                 ['id' => 'vis_jsmycoverletter', 'label' => __("Visitor Show", 'wp-job-portal') . ' ' . __('My Cover Letters', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_jsmycoverletter'], 'options' => $wpjobportal_options_showhide, 'pro' => ['slug' => 'coverletter', 'name' => __('Cover Letter', 'wp-job-portal')]],
                                 ['id' => 'vis_jsformcoverletter', 'label' => __("Visitor Show", 'wp-job-portal') . ' ' . __('Add Cover Letter', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_jsformcoverletter'], 'options' => $wpjobportal_options_showhide, 'pro' => ['slug' => 'coverletter', 'name' => __('Cover Letter', 'wp-job-portal')]],
                                 ['id' => 'vis_jslistallcompanies', 'label' => __("Visitor Show", 'wp-job-portal') . ' ' . __('All Companies', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_jslistallcompanies'], 'options' => $wpjobportal_options_showhide, 'pro' => ['slug' => 'multicompany', 'name' => __('Multi Company', 'wp-job-portal')]],
                                 ['id' => 'vis_wpjobportalcat', 'label' => __("Visitor Show", 'wp-job-portal') . ' ' . __('Jobs By Categories', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_wpjobportalcat'], 'options' => $wpjobportal_options_showhide],
                                 ['id' => 'vis_jslistnewestjobs', 'label' => __("Visitor Show", 'wp-job-portal') . ' ' . __('Newest Jobs', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_jslistnewestjobs'], 'options' => $wpjobportal_options_showhide],
                                 ['id' => 'vis_jslistjobbytype', 'label' => __("Visitor Show", 'wp-job-portal') . ' ' . __('Jobs By Types', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_jslistjobbytype'], 'options' => $wpjobportal_options_showhide],
                                 ['id' => 'vis_jobsbycities', 'label' => __("Visitor Show", 'wp-job-portal') . ' ' . __('Jobs By Cities', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_jobsbycities'], 'options' => $wpjobportal_options_showhide],
                                 ['id' => 'vis_wpjobportalearch', 'label' => __("Visitor Show", 'wp-job-portal') . ' ' . __('Search Job', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_wpjobportalearch'], 'options' => $wpjobportal_options_showhide],
                                 ['id' => 'vis_jsmessages', 'label' => __("Visitor Show", 'wp-job-portal') . ' ' . __('Messages', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_jsmessages'], 'options' => $wpjobportal_options_showhide, 'pro' => ['slug' => 'message', 'name' => __('Message', 'wp-job-portal')]],
                                 ['id' => 'vis_job_rss', 'label' => __("Visitor Show", 'wp-job-portal') . ' ' . __('Jobs RSS', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_job_rss'], 'options' => $wpjobportal_options_showhide, 'pro' => ['slug' => 'rssfeedback', 'name' => __('RSS Feedback', 'wp-job-portal')]],
                                 ['id' => 'vis_jsregister', 'label' => __("Visitor Show", 'wp-job-portal') . ' ' . __('Register', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_jsregister'], 'options' => $wpjobportal_options_showhide],
                                 ['id' => 'vis_jscredits', 'label' => __("Visitor Show", 'wp-job-portal') . ' ' . __('Packages', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_jscredits'], 'options' => $wpjobportal_options_showhide, 'pro' => ['slug' => 'credits', 'name' => __('Credits', 'wp-job-portal')]],
                                 ['id' => 'vis_jscreditlog', 'label' => __("Visitor Show", 'wp-job-portal') . ' ' . __('My Packages', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_jscreditlog'], 'options' => $wpjobportal_options_showhide, 'pro' => ['slug' => 'credits', 'name' => __('Credits', 'wp-job-portal')]],
                                 ['id' => 'vis_jspurchasehistory', 'label' => __("Visitor Show", 'wp-job-portal') . ' ' . __('Invoice', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_jspurchasehistory'], 'options' => $wpjobportal_options_showhide, 'pro' => ['slug' => 'credits', 'name' => __('Credits', 'wp-job-portal')]],
                                 ['id' => 'vis_jsratelist', 'label' => __("Visitor Show", 'wp-job-portal') . ' ' . __('My Subscription', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_jsratelist'], 'options' => $wpjobportal_options_showhide, 'pro' => ['slug' => 'credits', 'name' => __('Credits', 'wp-job-portal')]],
                         ]
                 ],
                 'jobseeker_dashboard_widgets' => [
                         'title'                => __("Job Seeker Widgets", 'wp-job-portal'),
                         'description' => __('Control which job seeker dashboard widgets and sections are visible to visitors', 'wp-job-portal'),
                         'fields'            => [
                                 ['id' => 'vis_jsactivejobs_graph', 'label' => __('Visitor Show Active Jobs Graph', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_jsactivejobs_graph'], 'tooltip' => __("Show a graph of job statistics in the job seeker's dashboard to visitor", 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                                 ['id' => 'vis_jsjobseekernewestjobs', 'label' => __('Visitor Show Newest Jobs Box', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_jsjobseekernewestjobs'], 'tooltip' => __("Show a box with newest jobs on the job seeker's dashboard to visitor", 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                                 ['id' => 'vis_temp_jobseeker_dashboard_jobs_graph', 'label' => __('Visitor Show Jobs Graph Section', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_temp_jobseeker_dashboard_jobs_graph'], 'options' => $wpjobportal_options_showhide],
                                 //['id' => 'vis_temp_jobseeker_dashboard_useful_links', 'label' => __('Visitor Show Useful Links Section', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_temp_jobseeker_dashboard_useful_links'], 'options' => $wpjobportal_options_showhide],
                                 ['id' => 'vis_temp_jobseeker_dashboard_apllied_jobs', 'label' => __('Visitor Show Applied Jobs Section', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_temp_jobseeker_dashboard_apllied_jobs'], 'options' => $wpjobportal_options_showhide],
                                 ['id' => 'vis_temp_jobseeker_dashboard_shortlisted_jobs', 'label' => __('Visitor Show Shortlisted Jobs Section', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_temp_jobseeker_dashboard_shortlisted_jobs'], 'options' => $wpjobportal_options_showhide, 'pro' => ['slug' => 'shortlist', 'name' => __('Shortlist', 'wp-job-portal')]],
                                 //['id' => 'vis_temp_jobseeker_dashboard_credits_log', 'label' => __('Visitor Show Credits Log Section', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_temp_jobseeker_dashboard_credits_log'], 'options' => $wpjobportal_options_showhide],
                                 ['id' => 'vis_temp_jobseeker_dashboard_purchase_history', 'label' => __('Visitor Show Purchase History Section', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_temp_jobseeker_dashboard_purchase_history'], 'options' => $wpjobportal_options_showhide],
                                 ['id' => 'vis_temp_jobseeker_dashboard_newest_jobs', 'label' => __('Visitor Show Newest Jobs Section', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['vis_temp_jobseeker_dashboard_newest_jobs'], 'options' => $wpjobportal_options_showhide],
                         ]
                 ],
         ]
    ],

    // ========================================================
    // 7. JOBS
    // ========================================================
    'jobs' => [
        'label' => __('Jobs', 'wp-job-portal'),
        'icon'  => 'briefcase',
        'groups' => [
            'posting' => [
                'title'       => __('Job Posting & Management', 'wp-job-portal'),
                'description' => __('Manage how jobs are posted, their lifecycle, and related features', 'wp-job-portal'),
                'fields'      => [
                    ['id' => 'jobautoapprove', 'label' => __('Job Auto Approve', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['jobautoapprove'], 'tooltip' => __('Automatically approve new jobs upon submission', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'jobexpiry_days_free', 'label' => __('Job Expiry Days', 'wp-job-portal') . ' ' . __('Free', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['jobexpiry_days_free'], 'tooltip' => __('Number of days until a freely posted job expires', 'wp-job-portal')],
                    ['id' => 'jobexpiry_days_perlisting', 'label' => __('Job Expiry Days', 'wp-job-portal') . ' ' . __('Per Listing', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['jobexpiry_days_perlisting'], 'tooltip' => __('Number of days until a paid', 'wp-job-portal') . ' ' . __('per listing', 'wp-job-portal') . ' ' . __('job expires', 'wp-job-portal')],
                ]
            ],
            'display' => [
                'title'       => __('Job Display & Listings', 'wp-job-portal'),
                'description' => __('Control how job-related pages and information are displayed to users', 'wp-job-portal'),
                'fields'      => [
                    ['id' => 'jobsbycities_countryname', 'label' => __('Show Country on Jobs by Cities Page', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['jobsbycities_countryname'], 'tooltip' => __('Display the country name next to the city', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'jobsbycities_jobcount', 'label' => __('Show Count on Jobs by Cities Page', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['jobsbycities_jobcount'], 'tooltip' => __('Display the total number of jobs available in each city', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'categories_numberofjobs', 'label' => __('Show Count in Job Categories', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['categories_numberofjobs'], 'tooltip' => __('Display the total number of jobs for each category', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'jobtype_numberofjobs', 'label' => __('Show Count on Jobs by Types Page', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['jobtype_numberofjobs'], 'tooltip' => __('Display the total number of jobs for each job type', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'jobtype_per_row', 'label' => __('Job Types Per Row', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['jobtype_per_row'], 'tooltip' => __('How many job types to display per row on the', 'wp-job-portal') . ' ' . __('Job by Type', 'wp-job-portal') . ' ' . __('page', 'wp-job-portal')],
                ]
            ]
        ]
    ],

    // ========================================================
    // 8. COMPANIES
    // ========================================================
    'companies' => [
        'label' => __('Companies', 'wp-job-portal'),
        'icon'  => 'building',
        'groups' => [
            'profile' => [
                'title'       => __('Company Profile Display', 'wp-job-portal'),
                'description' => __('Control which company details are visible on listings and company pages', 'wp-job-portal'),
                'fields'      => [
                    ['id' => 'comp_name', 'label' => __('Show Company Name', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['comp_name'], 'tooltip' => __('Effects on jobs listing and view company page', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'comp_description', 'label' => __('Show Company Description', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['comp_description'], 'tooltip' => __('Effects on view company page', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'comp_email_address', 'label' => __('Show Company Email Address', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['comp_email_address'], 'tooltip' => __('Effects on view company page', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'comp_city', 'label' => __('Show Company City', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['comp_city'], 'tooltip' => __('Effects on company listing and view company page', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'comp_show_url', 'label' => __('Show Company URL', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['comp_show_url'], 'tooltip' => __('Effects on view company page', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'comp_viewalljobs', 'label' => __('Show', 'wp-job-portal') . ' ' . __('View Company Jobs', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['comp_viewalljobs'], 'tooltip' => __('Effects on company listing and view company page', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'company_contact_detail', 'label' => __('Show Company Contact Detail by Default', 'wp-job-portal'), 'type' => 'select', 'value' => wpjobportal::$_data[0]['company_contact_detail'], 'tooltip' => __('Select who can view company contact details.', 'wp-job-portal'), 'options' => $wpjobportal_company_contact_detail_options],
                ]
            ],
            'settings' => [
                'title'       => __('Company Settings', 'wp-job-portal'),
                'description' => __('General settings for company profiles', 'wp-job-portal'),
                'fields'      => [
                    ['id' => 'company_logofilezize', 'label' => __('Company Logo Maximum Size', 'wp-job-portal') . ' KB', 'type' => 'text', 'value' => wpjobportal::$_data[0]['company_logofilezize'], 'tooltip' => __('Set the maximum file size for company logo uploads in kilobytes', 'wp-job-portal')],
                    ['id' => 'companyautoapprove', 'label' => __('Company Auto Approve', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['companyautoapprove'], 'tooltip' => __('Automatically approve new company profiles upon submission', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                ]
            ]
        ]
    ],

    // ========================================================
    // 9. RESUMES
    // ========================================================
    'resumes' => [
        'label' => __('Resumes', 'wp-job-portal'),
        'icon'  => 'file-alt',
        'groups' => [
            'posting' => [
                'title'       => __('Resume Settings', 'wp-job-portal'),
                'description' => __('Control resume submissions, visibility, and search functionality', 'wp-job-portal'),
                'fields'      => [
                    ['id' => 'empautoapprove', 'label' => __('Resume Auto Approve', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['empautoapprove'], 'tooltip' => __('Automatically approve new resumes upon submission', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'resume_contact_detail', 'label' => __('Show Resume Contact Detail by Default', 'wp-job-portal'), 'type' => 'select', 'value' => wpjobportal::$_data[0]['resume_contact_detail'], 'tooltip' => __('Who can view resume contact details', 'wp-job-portal'), 'options' => $wpjobportal_resume_contact_detail_options],
                    ['id' => 'categories_numberofresumes', 'label' => __('Show Count in Resume Categories', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['categories_numberofresumes'], 'tooltip' => __('Display the total number of resumes for each category', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'allow_search_resume', 'label' => __('Allow Resume Search', 'wp-job-portal'), 'type' => 'select', 'value' => wpjobportal::$_data[0]['allow_search_resume'], 'tooltip' => __('Define who is allowed to search for resumes', 'wp-job-portal'), 'options' => $wpjobportal_options_search_resume_permissions],
                    ['id' => 'search_resume_showsave', 'label' => __('Allow Users to Save Resume Searches', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['search_resume_showsave'], 'tooltip' => __('Allow users to save their resume search criteria for later use', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                ]
            ],
            'files' => [
                'title'       => __('Resume Files & Builder', 'wp-job-portal'),
                'description' => __('Define limits and allowed types for resume file uploads and advanced builder sections', 'wp-job-portal'),
                'fields'      => [
                    ['id' => 'resume_photofilesize', 'label' => __('Resume Photo Maximum Size', 'wp-job-portal') . ' KB', 'type' => 'text', 'value' => wpjobportal::$_data[0]['resume_photofilesize'], 'tooltip' => __('Maximum file size for resume photos in kilobytes', 'wp-job-portal')],
                    ['id' => 'document_file_size', 'label' => __('Resume Document Maximum Size', 'wp-job-portal') . ' KB', 'type' => 'text', 'value' => wpjobportal::$_data[0]['document_file_size'], 'tooltip' => __('System will not upload if resume file size exceeds the given size', 'wp-job-portal')],
                    ['id' => 'document_file_type', 'label' => __('Allowed Document Extensions', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['document_file_type'], 'tooltip' => __('Comma-separated list of allowed document file extensions', 'wp-job-portal') . ' e.g. pdf,doc,docx'],
                    ['id' => 'document_max_files', 'label' => __('Maximum Number of Files for Resume', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['document_max_files'], 'tooltip' => __('Maximum number of files a job seeker can upload with their resume', 'wp-job-portal')],
                    ['id' => 'max_resume_employers', 'label' => __('Max Employers in Resume Builder', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['max_resume_employers'], 'tooltip' => __('Maximum number of employers allowed in the resume builder', 'wp-job-portal'), 'pro' => ['slug' => 'advanceresumebuilder', 'name' => __('Advance Resume Builder', 'wp-job-portal')]],
                    ['id' => 'max_resume_institutes', 'label' => __('Max Institutes in Resume Builder', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['max_resume_institutes'], 'tooltip' => __('Maximum number of institutes allowed in the resume builder', 'wp-job-portal'), 'pro' => ['slug' => 'advanceresumebuilder', 'name' => __('Advance Resume Builder', 'wp-job-portal')]],
                    ['id' => 'max_resume_languages', 'label' => __('Max Languages in Resume Builder', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['max_resume_languages'], 'tooltip' => __('Maximum number of languages allowed in the resume builder', 'wp-job-portal'), 'pro' => ['slug' => 'advanceresumebuilder', 'name' => __('Advance Resume Builder', 'wp-job-portal')]],
                    ['id' => 'max_resume_addresses', 'label' => __('Max Addresses in Resume Builder', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['max_resume_addresses'], 'tooltip' => __('Maximum number of addresses allowed in the resume builder', 'wp-job-portal'), 'pro' => ['slug' => 'advanceresumebuilder', 'name' => __('Advance Resume Builder', 'wp-job-portal')]],
                ]
            ]
        ]
    ],

    // ========================================================
    // 10. APPLICATIONS
    // ========================================================
    'applications' => [
        'label' => __('Applications', 'wp-job-portal'),
        'icon'  => 'inbox',
        'groups' => [
            'job_applications' => [
                'title'       => __('Job Applications', 'wp-job-portal'),
                'description' => __('Configure the job application process and related notifications', 'wp-job-portal'),
                'fields'      => [
                    ['id' => 'showapplybutton', 'label' => __('Show Apply Now Button', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['showapplybutton'], 'tooltip' => __('Controls the visibility of apply now button in plugin', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'quick_apply_for_user', 'label' => __('Enable Quick Apply for Logged-in Users', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['quick_apply_for_user'], 'tooltip' => __("Show the 'Quick Apply' form to logged-in users on the job detail page", 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'quick_apply_for_visitor', 'label' => __('Enable Quick Apply for Visitors', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['quick_apply_for_visitor'], 'tooltip' => __("Show the 'Quick Apply' form to visitors on the job detail page", 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'show_applied_resume_status', 'label' => __('Show Applied Resume Status to Job Seeker', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['show_applied_resume_status'], 'tooltip' => __("Display the status of applications to the job seeker", 'wp-job-portal'), 'options' => $wpjobportal_options_yesno, 'pro' => ['slug' => 'resumeaction', 'name' => __('Resume Action', 'wp-job-portal')]],
                    ['id' => 'show_only_section_that_have_value', 'label' => __('Resume Data in Email', 'wp-job-portal') . ' ' . __('Sections', 'wp-job-portal'), 'type' => 'select', 'value' => wpjobportal::$_data[0]['show_only_section_that_have_value'], 'tooltip' => __('Choose whether to include all resume sections or only filled ones in the application email to employers', 'wp-job-portal'), 'options' => $wpjobportal_options_resume_email_sections],
                    ['id' => 'employer_resume_alert_fields', 'label' => __('Resume Data in Email', 'wp-job-portal') . ' ' . __('Fields', 'wp-job-portal'), 'type' => 'select', 'value' => wpjobportal::$_data[0]['employer_resume_alert_fields'], 'tooltip' => __('Choose whether to include all fields or only filled fields in the application email', 'wp-job-portal'), 'options' => $wpjobportal_options_resume_email_fields],
                ]
            ],
            'rules' => [
                'title'       => __('Auto-Approve Rules', 'wp-job-portal'),
                'description' => __('Set auto-approval rules for various user-submitted content', 'wp-job-portal'),
                'fields'      => [
                    ['id' => 'featuredresume_autoapprove', 'label' => __('Featured Resume Auto Approve', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['featuredresume_autoapprove'], 'tooltip' => __('Automatically approve resumes marked as featured', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno, 'pro' => ['slug' => 'featureresume', 'name' => __('Featured Resume', 'wp-job-portal')]],
                    ['id' => 'featuredcompany_autoapprove', 'label' => __('Featured Company Auto Approve', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['featuredcompany_autoapprove'], 'tooltip' => __('Automatically approve companies marked as featured', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno, 'pro' => ['slug' => 'featuredcompany', 'name' => __('Featured Company', 'wp-job-portal')]],
                    ['id' => 'featuredjob_autoapprove', 'label' => __('Featured Job Auto Approve', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['featuredjob_autoapprove'], 'tooltip' => __('Automatically approve jobs marked as featured', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno, 'pro' => ['slug' => 'featuredjob', 'name' => __('Featured Job', 'wp-job-portal')]],
                    ['id' => 'coverletter_auto_approve', 'label' => __('Cover Letter Auto Approve', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['coverletter_auto_approve'], 'tooltip' => __('Automatically approve cover letters submitted by job seekers', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno, 'pro' => ['slug' => 'coverletter', 'name' => __('Cover Letter', 'wp-job-portal')]],
                    ['id' => 'folder_auto_approve', 'label' => __('Folder Auto Approve', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['folder_auto_approve'], 'tooltip' => __('Automatically approve new folders created by employers', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno, 'pro' => ['slug' => 'folder', 'name' => __('Folder', 'wp-job-portal')]],
                    ['id' => 'department_auto_approve', 'label' => __('Department Auto Approve', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['department_auto_approve'], 'tooltip' => __('Automatically approve new departments created by employers', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno, 'pro' => ['slug' => 'departments', 'name' => __('Departments', 'wp-job-portal')]],
                    ['id' => 'message_auto_approve', 'label' => __('Message Auto Approve', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['message_auto_approve'], 'tooltip' => __('Automatically approve messages between job seekers and employers', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno, 'pro' => ['slug' => 'message', 'name' => __('Message', 'wp-job-portal')]],
                    ['id' => 'conflict_message_auto_approve', 'label' => __('Conflict Message Auto Approve', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['conflict_message_auto_approve'], 'tooltip' => __('Automatically approve conflicted messages', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno, 'pro' => ['slug' => 'message', 'name' => __('Message', 'wp-job-portal')]],
                ]
            ]
        ]
    ],

    // ========================================================
    // 11. EMAIL & ALERTS
    // ========================================================
    'email_alerts' => [
        'label' => __('Email & Alerts', 'wp-job-portal'),
        'icon'  => 'envelope',
        'groups' => [
            'email_sender' => [
                'title'       => __('Email Sender Identity', 'wp-job-portal'),
                'description' => __('Configure sender details for all outgoing system emails', 'wp-job-portal'),
                'fields'      => [
                    ['id' => 'adminemailaddress', 'label' => __('Admin Email Address', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['adminemailaddress'], 'tooltip' => __('Admin will receive email notifications at this address', 'wp-job-portal')],
                    ['id' => 'mailfromaddress', 'label' => __('Sender Email Address', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['mailfromaddress'], 'tooltip' => __('The email address that will be used to send system emails', 'wp-job-portal')],
                    ['id' => 'mailfromname', 'label' => __('Sender Name', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['mailfromname'], 'tooltip' => __('The sender name that will be used in system emails', 'wp-job-portal')],
                ]
            ],
            'job_alerts' => [
                'title'       => __('Job Alerts', 'wp-job-portal'),
                'description' => __('Configure settings for job alert emails and forms', 'wp-job-portal'),
                'pro'         => ['slug' => 'jobalert', 'name' => __('Job Alert', 'wp-job-portal')],
                'fields'      => [
                    ['id' => 'jobalertsetting', 'label' => __('Show Job Alert Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['jobalertsetting'], 'tooltip' => __('Show the Job Alert link in the job seeker control panel', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'overwrite_jobalert_settings', 'label' => __('Enable Job Alerts for Visitors', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['overwrite_jobalert_settings'], 'tooltip' => __('Allow non-logged-in users to sign up for job alerts', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'jobalert_auto_approve', 'label' => __('Job Alert Auto Approve', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['jobalert_auto_approve'], 'tooltip' => __('Automatically approve new job alerts upon creation', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'job_alert_captcha', 'label' => __('Captcha on Visitor Job Alert Form', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['job_alert_captcha'], 'tooltip' => __('Show a captcha on the job alert sign-up form for visitors', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno, 'pro' => ['slug' => 'tellfriend', 'name' => __('Tell a Friend', 'wp-job-portal')]],
                ]
            ],
            // 'saved_searches' => [
            //     'title'       => __('Saved Searches', 'wp-job-portal'),
            //     'description' => __('Allow users to save their search queries for jobs and resumes', 'wp-job-portal'),
            //     'fields'      => [
            //         //['id' => 'my_jobsearches', 'label' => __('Show Saved Searches for Job Seekers', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['my_jobsearches'], 'tooltip' => __('Allow job seekers to save their job searches', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
            //         ['id' => 'my_resumesearches', 'label' => __('Show Saved Searches for Employers', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['my_resumesearches'], 'tooltip' => __('Allow employers to save their resume searches', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide, 'pro' => ['slug' => 'resumesearch', 'name' => __('Resume Search', 'wp-job-portal')]],
            //     ]
            // ],

        ]
    ],

    // ========================================================
    // 12. FEEDS & AI
    // ========================================================
    'feeds_ai' => [
        'label' => __('Feeds & AI', 'wp-job-portal'),
        'icon'  => 'rss',
        'groups' => [
            'rss_jobs' => [
                'title'       => __('RSS Feeds', 'wp-job-portal') . ' ' . __('Jobs', 'wp-job-portal'),
                'description' => __('Configure the RSS feed for jobs', 'wp-job-portal'),
                'pro'         => ['slug' => 'rssfeedback', 'name' => __('RSS Feedback', 'wp-job-portal')],
                'fields'      => [
                    ['id' => 'job_rss', 'label' => __('Enable Jobs RSS Feed', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['job_rss'], 'tooltip' => __('Enable the main RSS feed for new jobs', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'rss_job_title', 'label' => __('Job Feed Title', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['rss_job_title'], 'tooltip' => __('The title for the job RSS feed', 'wp-job-portal')],
                    ['id' => 'rss_job_description', 'label' => __('Job Feed Description', 'wp-job-portal'), 'type' => 'textarea', 'value' => wpjobportal::$_data[0]['rss_job_description'], 'tooltip' => __('The description for the job RSS feed', 'wp-job-portal')],
                    ['id' => 'rss_job_copyright', 'label' => __('Job Feed Copyright', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['rss_job_copyright'], 'tooltip' => __('Copyright notice for the job feed', 'wp-job-portal') . '. ' . __('Leave blank to hide', 'wp-job-portal')],
                    ['id' => 'rss_job_editor', 'label' => __('Job Feed Editor', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['rss_job_editor'], 'tooltip' => __('The person responsible for editorial content', 'wp-job-portal') . '. ' . __('Leave blank to hide', 'wp-job-portal')],
                    ['id' => 'rss_job_webmaster', 'label' => __('Job Feed Webmaster', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['rss_job_webmaster'], 'tooltip' => __('The person responsible for technical issues', 'wp-job-portal') . '. ' . __('Leave blank to hide', 'wp-job-portal')],
                    ['id' => 'rss_job_ttl', 'label' => __('Job Feed TTL', 'wp-job-portal') . ' ' . __('Time to Live', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['rss_job_ttl'], 'tooltip' => __('Number of minutes the feed can be cached before refreshing', 'wp-job-portal')],
                    ['id' => 'rss_job_categories', 'label' => __('Show Categories in Job Feed', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['rss_job_categories'], 'tooltip' => __('Include categories in the job feed items', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'rss_job_image', 'label' => __('Show Company Image in Job Feed', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['rss_job_image'], 'tooltip' => __('Include the company logo in the job feed items', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                ]
            ],
            'rss_resumes' => [
                'title'       => __('RSS Feeds', 'wp-job-portal') . ' ' . __('Resumes', 'wp-job-portal'),
                'description' => __('Configure the RSS feed for resumes', 'wp-job-portal'),
                'pro'         => ['slug' => 'rssfeedback', 'name' => __('RSS Feedback', 'wp-job-portal')],
                'fields'      => [
                    ['id' => 'resume_rss', 'label' => __('Enable Resumes RSS Feed', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['resume_rss'], 'tooltip' => __('Enable the main RSS feed for new resumes', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'rss_resume_title', 'label' => __('Resume Feed Title', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['rss_resume_title'], 'tooltip' => __('The title for the resume RSS feed', 'wp-job-portal')],
                    ['id' => 'rss_resume_description', 'label' => __('Resume Feed Description', 'wp-job-portal'), 'type' => 'textarea', 'value' => wpjobportal::$_data[0]['rss_resume_description'], 'tooltip' => __('The description for the resume RSS feed', 'wp-job-portal')],
                    ['id' => 'rss_resume_copyright', 'label' => __('Resume Feed Copyright', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['rss_resume_copyright'], 'tooltip' => __('Copyright notice for the resume feed', 'wp-job-portal') . '. ' . __('Leave blank to hide', 'wp-job-portal')],
                    ['id' => 'rss_resume_editor', 'label' => __('Resume Feed Editor', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['rss_resume_editor'], 'tooltip' => __('The person responsible for editorial content', 'wp-job-portal') . '. ' . __('Leave blank to hide', 'wp-job-portal')],
                    ['id' => 'rss_resume_webmaster', 'label' => __('Resume Feed Webmaster', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['rss_resume_webmaster'], 'tooltip' => __('The person responsible for technical issues', 'wp-job-portal') . '. ' . __('Leave blank to hide', 'wp-job-portal')],
                    ['id' => 'rss_resume_ttl', 'label' => __('Resume Feed TTL', 'wp-job-portal') . ' ' . __('Time to Live', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['rss_resume_ttl'], 'tooltip' => __('Number of minutes the feed can be cached before refreshing', 'wp-job-portal')],
                    ['id' => 'rss_resume_categories', 'label' => __('Show Categories in Resume Feed', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['rss_resume_categories'], 'tooltip' => __('Include categories in the resume feed items', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'rss_resume_file', 'label' => __('Show Resume File in Resume Feed', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['rss_resume_file'], 'tooltip' => __('Make resume files downloadable from the feed', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                ]
            ],
            'ai_matches' => [
                'title'       => __('AI Suggested Matches', 'wp-job-portal'),
                'description' => __('Configure AI-powered job and resume suggestion features', 'wp-job-portal'),
                'fields'      => [
                    ['id' => 'job_search_ai_form', 'label' => __('AI Search On Job Search Page', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['job_search_ai_form'], 'tooltip' => __('Show the AI search field on the main job search form', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno, 'pro' => ['slug' => 'aijobsearch', 'name' => __('AI Job Search', 'wp-job-portal')]],
                    ['id' => 'job_list_ai_filter', 'label' => __('AI Search Filter On Job Listing', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['job_list_ai_filter'], 'tooltip' => __('Show the AI search field as a filter on the job listing page', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno, 'pro' => ['slug' => 'aijobsearch', 'name' => __('AI Job Search', 'wp-job-portal')]],
                    ['id' => 'resume_search_ai_form', 'label' => __('AI Search On Resume Search Page', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['resume_search_ai_form'], 'tooltip' => __('Show the AI search field on the main resume search form', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno, 'pro' => ['slug' => 'airesumesearch', 'name' => __('AI Resume Search', 'wp-job-portal')]],
                    ['id' => 'resume_list_ai_filter', 'label' => __('AI Search Filter On Resume Listing', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['resume_list_ai_filter'], 'tooltip' => __('Show the AI search field as a filter on the resume listing page', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno, 'pro' => ['slug' => 'airesumesearch', 'name' => __('AI Resume Search', 'wp-job-portal')]],
                    ['id' => 'show_suggested_jobs_button', 'label' => __('Show AI Suggested Jobs Button', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['show_suggested_jobs_button'], 'tooltip' => __("Show an 'AI Suggested Jobs' button on the 'My Resumes' page for job seekers", 'wp-job-portal'), 'options' => $wpjobportal_options_yesno, 'pro' => ['slug' => 'aisuggestedjobs', 'name' => __('AI Suggested Jobs', 'wp-job-portal')]],
                    ['id' => 'show_suggested_jobs_dashboard', 'label' => __('Show AI Suggested Jobs on Dashboard', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['show_suggested_jobs_dashboard'], 'tooltip' => __('Show a list of AI-suggested jobs on the job seeker dashboard', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno, 'pro' => ['slug' => 'aisuggestedjobs', 'name' => __('AI Suggested Jobs', 'wp-job-portal')]],
                    ['id' => 'show_suggested_resumes_button', 'label' => __('Show AI Suggested Resumes Button', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['show_suggested_resumes_button'], 'tooltip' => __("Show an 'AI Suggested Resumes' button on the 'My Jobs' page for employers", 'wp-job-portal'), 'options' => $wpjobportal_options_yesno, 'pro' => ['slug' => 'aisuggestedresumes', 'name' => __('AI Suggested Resumes', 'wp-job-portal')]],
                    ['id' => 'show_suggested_resumes_dashboard', 'label' => __('Show AI Suggested Resumes on Dashboard', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['show_suggested_resumes_dashboard'], 'tooltip' => __('Show a list of AI-suggested resumes on the employer dashboard', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno, 'pro' => ['slug' => 'aisuggestedresumes', 'name' => __('AI Suggested Resumes', 'wp-job-portal')]],
                ]
            ]
        ]
    ],

    // ========================================================
    // 13. INTEGRATIONS
    // ========================================================
    'integrations' => [
        'label' => __('Integrations', 'wp-job-portal'),
        'icon'  => 'plug',
        'groups' => [
            /*
            'social_login' => [
                'title'       => __('Social Login', 'wp-job-portal'),
                'description' => __('Allow users to register and log in with their social media accounts', 'wp-job-portal'),
                'pro'         => ['slug' => 'sociallogin', 'name' => __('Social Login', 'wp-job-portal')],
                'fields'      => [
                    ['id' => 'loginwithfacebook', 'label' => __('Login with Facebook', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['loginwithfacebook'], 'tooltip' => __('Allow users to log in using their Facebook account', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'applywithfacebook', 'label' => __('Apply with Facebook', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['applywithfacebook'], 'tooltip' => __('Allow users to apply for jobs using their Facebook profile', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'apikeyfacebook', 'label' => __('Facebook App ID', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['apikeyfacebook'], 'tooltip' => __('Enter your Facebook App ID', 'wp-job-portal')],
                    ['id' => 'clientsecretfacebook', 'label' => __('Facebook App Secret', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['clientsecretfacebook'], 'tooltip' => __('Enter your Facebook App Secret', 'wp-job-portal')],
                    ['id' => 'facebook_oauth_login_uri', 'type' => 'info_text', 'label' => __('Valid OAuth URI', 'wp-job-portal') . ' ' . __('Login', 'wp-job-portal'), 'text' => site_url('?wpjobportal=social&task=login&action=facebook')],
                    ['id' => 'facebook_oauth_apply_uri', 'type' => 'info_text', 'label' => __('Valid OAuth URI', 'wp-job-portal') . ' ' . __('Apply', 'wp-job-portal'), 'text' => site_url('?wpjobportal=social&task=apply&action=facebook')],
                    ['id' => 'loginwithlinkedin', 'label' => __('Login with LinkedIn', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['loginwithlinkedin'], 'tooltip' => __('Allow users to log in using their LinkedIn account', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'applywithlinkedin', 'label' => __('Apply with LinkedIn', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['applywithlinkedin'], 'tooltip' => __('Allow users to apply for jobs using their LinkedIn profile', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'apikeylinkedin', 'label' => __('LinkedIn Client ID', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['apikeylinkedin'], 'tooltip' => __('Enter your LinkedIn Client ID', 'wp-job-portal') . ' ' . __('API Key', 'wp-job-portal')],
                    ['id' => 'clientsecretlinkedin', 'label' => __('LinkedIn Client Secret', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['clientsecretlinkedin'], 'tooltip' => __('Enter your LinkedIn Client Secret', 'wp-job-portal')],
                    ['id' => 'linkedin_oauth_login_uri', 'type' => 'info_text', 'label' => __('Valid OAuth URI', 'wp-job-portal') . ' ' . __('Login', 'wp-job-portal'), 'text' => site_url('?wpjobportal=social&task=login&action=linkedin')],
                    ['id' => 'linkedin_oauth_apply_uri', 'type' => 'info_text', 'label' => __('Valid OAuth URI', 'wp-job-portal') . ' ' . __('Apply', 'wp-job-portal'), 'text' => site_url('?wpjobportal=social&task=apply&action=linkedin')],
                ]
            ],
            */
            'social_sharing' => [
                'title'       => __('Social Sharing', 'wp-job-portal'),
                'description' => __('Enable social sharing buttons on your pages', 'wp-job-portal'),
                'pro'         => ['slug' => 'socialshare', 'name' => __('Social Share', 'wp-job-portal')],
                'fields'      => [
                    ['id' => 'employer_share_fb_like', 'label' => __('Enable Facebook Likes', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['employer_share_fb_like'], 'tooltip' => __('Show Facebook like button', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'employer_share_fb_share', 'label' => __('Enable Facebook Share', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['employer_share_fb_share'], 'tooltip' => __('Show Facebook share button', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'employer_share_fb_comments', 'label' => __('Enable Facebook Comments', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['employer_share_fb_comments'], 'tooltip' => __('Show Facebook comments widget', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'employer_share_twitter_share', 'label' => __('Enable Twitter Share', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['employer_share_twitter_share'], 'tooltip' => __('Show Twitter share button', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'employer_share_linkedin_share', 'label' => __('Enable LinkedIn Share', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['employer_share_linkedin_share'], 'tooltip' => __('Show LinkedIn share button', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'employer_share_blog_share', 'label' => __('Enable Blogger Share', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['employer_share_blog_share'], 'tooltip' => __('Show Blogger share button', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'employer_share_digg_share', 'label' => __('Enable Digg Share', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['employer_share_digg_share'], 'tooltip' => __('Show Digg share button', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'employer_share_myspace_share', 'label' => __('Enable Myspace Share', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['employer_share_myspace_share'], 'tooltip' => __('Show Myspace share button', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'employer_share_yahoo_share', 'label' => __('Enable Yahoo Share', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['employer_share_yahoo_share'], 'tooltip' => __('Show Yahoo share button', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                ]
            ],
            'mapping' => [
                'title'       => __('Mapping & Geolocation', 'wp-job-portal'),
                'description' => __('Configure map services and location-based features', 'wp-job-portal'),
                'fields'      => [
                    ['id' => 'mappingservice', 'label' => __('Mapping Service', 'wp-job-portal'), 'type' => 'select', 'value' => wpjobportal::$_data[0]['mappingservice'], 'tooltip' => __('Choose between Google Maps and OpenStreetMap for map displays', 'wp-job-portal'), 'options' => $wpjobportal_options_mapping_service, 'pro' => ['slug' => 'addressdata', 'name' => __('Address Data', 'wp-job-portal')]],
                    ['id' => 'google_map_api_key', 'label' => __('Google Map API Key', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['google_map_api_key'], 'tooltip' => __('Enter your Google Maps API key', 'wp-job-portal'), 'pro' => ['slug' => 'addressdata', 'name' => __('Address Data', 'wp-job-portal')]],
                    ['id' => 'mapheight', 'label' => __('Map Height', 'wp-job-portal') . ' px', 'type' => 'text', 'value' => wpjobportal::$_data[0]['mapheight'], 'tooltip' => __('Set the height for maps displayed in the plugin, in pixels', 'wp-job-portal'), 'pro' => ['slug' => 'addressdata', 'name' => __('Address Data', 'wp-job-portal')]],
                    ['id' => 'default_longitude', 'label' => __('Default Longitude', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['default_longitude'], 'tooltip' => __('Default longitude for map centering', 'wp-job-portal'), 'pro' => ['slug' => 'addressdata', 'name' => __('Address Data', 'wp-job-portal')]],
                    ['id' => 'default_latitude', 'label' => __('Default Latitude', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['default_latitude'], 'tooltip' => __('Default latitude for map centering', 'wp-job-portal'), 'pro' => ['slug' => 'addressdata', 'name' => __('Address Data', 'wp-job-portal')]],
                    ['id' => 'defaultradius', 'label' => __('Default Map Radius Type', 'wp-job-portal'), 'type' => 'select', 'value' => wpjobportal::$_data[0]['defaultradius'], 'tooltip' => __('Default unit for map radius calculations', 'wp-job-portal'), 'options' => $wpjobportal_options_map_radius, 'pro' => ['slug' => 'addressdata', 'name' => __('Address Data', 'wp-job-portal')]],
                    ['id' => 'number_of_cities_for_autocomplete', 'label' => __('Max Records for City Field', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['number_of_cities_for_autocomplete'], 'tooltip' => __('Set the number of cities to show in the location autocomplete results', 'wp-job-portal')],
                    ['id' => 'number_of_tags_for_autocomplete', 'label' => __('Max Records for Tag Field', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['number_of_tags_for_autocomplete'], 'tooltip' => __('Set the number of tags to show in the tag autocomplete results', 'wp-job-portal'), 'pro' => ['slug' => 'tag', 'name' => __('Tag', 'wp-job-portal')]],
                ]
            ],
        ]
    ],

    // ========================================================
    // 14. SECURITY & CAPTCHA
    // ========================================================
    'security' => [
        'label' => __('Security & Captcha', 'wp-job-portal'),
        'icon'  => 'lock',
        'groups' => [
            'captcha_options' => [
                'title'       => __('Captcha Options', 'wp-job-portal'),
                'description' => __('Enable captcha on various forms to prevent spam', 'wp-job-portal'),
                'fields'      => [
                    ['id' => 'captcha_selection', 'label' => __('Default Captcha Type', 'wp-job-portal'), 'type' => 'select', 'value' => wpjobportal::$_data[0]['captcha_selection'], 'tooltip' => __('Select the default captcha to use throughout the plugin', 'wp-job-portal'), 'options' => $wpjobportal_options_captcha_selection],
                    ['id' => 'cap_on_reg_form', 'label' => __('Captcha on Registration Form', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['cap_on_reg_form'], 'tooltip' => __('Show a captcha on the WP Job Portal registration form', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'quick_apply_captcha', 'label' => __('Captcha on Visitor Quick Apply', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['quick_apply_captcha'], 'tooltip' => __('Show captcha to visitors on the quick apply form', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'job_captcha', 'label' => __('Captcha on Visitor Job Form', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['job_captcha'], 'tooltip' => __('Show captcha on the job submission form for visitors', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno, 'pro' => ['slug' => 'visitorcanaddjob', 'name' => __('Visitor Can Add Job', 'wp-job-portal')]],
                    ['id' => 'resume_captcha', 'label' => __('Captcha on Visitor Resume Form', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['resume_captcha'], 'tooltip' => __('Show captcha on the resume submission form for visitors', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno, 'pro' => ['slug' => 'visitorapplyjob', 'name' => __('Visitor Apply Job', 'wp-job-portal')]],
                    ['id' => 'tell_a_friend_captcha', 'label' => __('Captcha on Tell a Friend Popup', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['tell_a_friend_captcha'], 'tooltip' => __('Show captcha on the', 'wp-job-portal') . ' ' . __('Tell a Friend', 'wp-job-portal') . ' ' . __('popup for visitors', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno, 'pro' => ['slug' => 'tellfriend', 'name' => __('Tell a Friend', 'wp-job-portal')]],
                ]
            ],
            'own_captcha' => [
                'title'       => __('WP Job Portal Captcha', 'wp-job-portal'),
                'description' => __('Settings for the built-in mathematical captcha', 'wp-job-portal'),
                'fields'      => [
                    ['id' => 'owncaptcha_calculationtype', 'label' => __('Calculation Type', 'wp-job-portal'), 'type' => 'select', 'value' => wpjobportal::$_data[0]['owncaptcha_calculationtype'], 'tooltip' => __('Select the type of math problem for the captcha', 'wp-job-portal') . ' ' . __('Addition or Subtraction', 'wp-job-portal'), 'options' => $wpjobportal_options_captcha_calculation],
                    ['id' => 'owncaptcha_subtractionans', 'label' => __('Answer Always Positive', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['owncaptcha_subtractionans'], 'tooltip' => __('Ensure that subtraction problems always result in a positive answer', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'owncaptcha_totaloperand', 'label' => __('Number of Operands', 'wp-job-portal'), 'type' => 'select', 'value' => wpjobportal::$_data[0]['owncaptcha_totaloperand'], 'tooltip' => __('Choose the number of operands for the math problem', 'wp-job-portal'), 'options' => $wpjobportal_options_captcha_operands],
                ]
            ],
            'recaptcha' => [
                'title'       => __('Google reCAPTCHA', 'wp-job-portal'),
                'description' => __('Configure Google reCAPTCHA v2 or v3', 'wp-job-portal'),
                'fields'      => [
                    ['id' => 'recaptcha_version', 'label' => __('Google reCaptcha Version', 'wp-job-portal'), 'type' => 'select', 'value' => wpjobportal::$_data[0]['recaptcha_version'], 'tooltip' => __('Select which version of Google reCAPTCHA to use', 'wp-job-portal'), 'options' => $wpjobportal_options_recaptcha_version],
                    ['id' => 'recaptcha_publickey', 'label' => __('Google reCaptcha Site Key', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['recaptcha_publickey'], 'tooltip' => __('Enter the site key from your Google reCAPTCHA admin console', 'wp-job-portal')],
                    ['id' => 'recaptcha_privatekey', 'label' => __('Google reCaptcha Secret Key', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['recaptcha_privatekey'], 'tooltip' => __('Enter the secret key from your Google reCAPTCHA admin console', 'wp-job-portal')],
                ]
            ],
        ]
    ],

    // ========================================================
    // 15. MONETIZATION
    // ========================================================
    'monetization' => [
        'label' => __('Monetization', 'wp-job-portal'),
        'icon'  => 'dollar-sign',
        'groups' => [
            'general' => [
                'title'       => __('General Monetization', 'wp-job-portal'),
                'description' => __('Core settings for handling payments and packages', 'wp-job-portal'),
                'fields'      => [
                    ['id' => 'submission_type', 'label' => __('Submission Type', 'wp-job-portal'), 'type' => 'select', 'value' => wpjobportal::$_data[0]['submission_type'], 'tooltip' => __('Choose the monetization model for submissions', 'wp-job-portal') . ' ' . __('Free, Per Listing, or Membership-based', 'wp-job-portal'), 'options' => $wpjobportal_options_submission_type, 'pro' => ['slug' => 'credits', 'name' => __('Credits', 'wp-job-portal')]],
                    ['id' => 'job_currency', 'label' => __('Default Currency Symbol', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['job_currency'], 'tooltip' => __('The currency symbol to be used across the site', 'wp-job-portal') . ' e.g. $'],
                    ['id' => 'currency_align', 'label' => __('Currency Symbol Position', 'wp-job-portal'), 'type' => 'select', 'value' => wpjobportal::$_data[0]['currency_align'], 'tooltip' => __('Show currency symbol left or right to the amount', 'wp-job-portal'), 'options' => $wpjobportal_options_currency_align],
                ]
            ],
            'packages' => [
                'title'       => __('Packages & Featured Listings', 'wp-job-portal'),
                'description' => __('Manage free packages and enable featured listings', 'wp-job-portal'),
                'pro'         => ['slug' => 'credits', 'name' => __('Credits', 'wp-job-portal')],
                'fields'      => [
                    ['id' => 'auto_assign_free_package', 'label' => __('Auto-assign Free Package to New User', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['auto_assign_free_package'], 'tooltip' => __('Automatically give new users a free package if one exists', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'free_package_auto_approve', 'label' => __('Free Package Purchase Auto Approve', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['free_package_auto_approve'], 'tooltip' => __('Automatically approve the purchase of free packages', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'free_package_purchase_only_once', 'label' => __('Purchase Free Package Only Once', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['free_package_purchase_only_once'], 'tooltip' => __('Prevent users from acquiring a free package more than once', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'system_have_featured_company', 'label' => __('Enable Featured Companies', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['system_have_featured_company'], 'tooltip' => __('Allow companies to be marked as', 'wp-job-portal') . ' ' . __('featured', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno, 'pro' => ['slug' => 'featuredcompany', 'name' => __('Featured Company', 'wp-job-portal')]],
                    ['id' => 'system_have_featured_job', 'label' => __('Enable Featured Jobs', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['system_have_featured_job'], 'tooltip' => __('Allow jobs to be marked as', 'wp-job-portal') . ' ' . __('featured', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno, 'pro' => ['slug' => 'featuredjob', 'name' => __('Featured Job', 'wp-job-portal')]],
                    ['id' => 'system_have_featured_resume', 'label' => __('Enable Featured Resumes', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['system_have_featured_resume'], 'tooltip' => __('Allow resumes to be marked as', 'wp-job-portal') . ' ' . __('featured', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno, 'pro' => ['slug' => 'featureresume', 'name' => __('Featured Resume', 'wp-job-portal')]],
                ]
            ],
            'per_listing_prices' => [
                'title'       => __('Per Listing Prices', 'wp-job-portal'),
                'description' => __('Set prices for various actions when using the', 'wp-job-portal') . ' ' . __('Per Listing', 'wp-job-portal') . ' ' . __('submission type', 'wp-job-portal'),
                'pro'         => ['slug' => 'credits', 'name' => __('Credits', 'wp-job-portal')],
                'fields'      => [
                    ['id' => 'company_price_perlisting', 'label' => __('Price per', 'wp-job-portal') . ' ' . __('Company', 'wp-job-portal') . ' ' . __('Submission', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['company_price_perlisting'], 'tooltip' => __('Price to submit a single company profile', 'wp-job-portal')],
                    ['id' => 'company_feature_price_perlisting', 'label' => __('Price per', 'wp-job-portal') . ' ' . __('Featured Company', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['company_feature_price_perlisting'], 'tooltip' => __('Additional price to make a company', 'wp-job-portal') . ' ' . __('featured', 'wp-job-portal')],
                    ['id' => 'company_featureexpire_price_perlisting', 'label' => __('Days Until Featured Company Expires', 'wp-job-portal') . ' ' . __('Per Listing', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['company_featureexpire_price_perlisting'], 'tooltip' => __('Number of days a', 'wp-job-portal') . ' ' . __('per-listing', 'wp-job-portal') . ' ' . __('featured company remains active', 'wp-job-portal')],
                    ['id' => 'company_featureexpire_free', 'label' => __('Days Until Featured Company Expires', 'wp-job-portal') . ' ' . __('Free', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['company_featureexpire_free'], 'tooltip' => __('Number of days a', 'wp-job-portal') . ' ' . __('free', 'wp-job-portal') . ' ' . __('featured company remains active', 'wp-job-portal')],
                    ['id' => 'job_currency_price_perlisting', 'label' => __('Price per', 'wp-job-portal') . ' ' . __('Job', 'wp-job-portal') . ' ' . __('Submission', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['job_currency_price_perlisting'], 'tooltip' => __('Price to submit a single job', 'wp-job-portal')],
                    ['id' => 'jobs_feature_price_perlisting', 'label' => __('Price per', 'wp-job-portal') . ' ' . __('Featured Job', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['jobs_feature_price_perlisting'], 'tooltip' => __('Additional price to make a job', 'wp-job-portal') . ' ' . __('featured', 'wp-job-portal')],
                    ['id' => 'featuredjobexpiry_days_perlisting', 'label' => __('Days Until Featured Job Expires', 'wp-job-portal') . ' ' . __('Per Listing', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['featuredjobexpiry_days_perlisting'], 'tooltip' => __('Number of days a', 'wp-job-portal') . ' ' . __('per-listing', 'wp-job-portal') . ' ' . __('featured job remains active', 'wp-job-portal')],
                    ['id' => 'featuredjobexpiry_days_free', 'label' => __('Days Until Featured Job Expires', 'wp-job-portal') . ' ' . __('Free', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['featuredjobexpiry_days_free'], 'tooltip' => __('Number of days a', 'wp-job-portal') . ' ' . __('free', 'wp-job-portal') . ' ' . __('featured job remains active', 'wp-job-portal')],
                    ['id' => 'job_resume_price_perlisting', 'label' => __('Price per', 'wp-job-portal') . ' ' . __('Resume', 'wp-job-portal') . ' ' . __('Submission', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['job_resume_price_perlisting'], 'tooltip' => __('Price to submit a single resume', 'wp-job-portal')],
                    ['id' => 'job_featureresume_price_perlisting', 'label' => __('Price per', 'wp-job-portal') . ' ' . __('Featured Resume', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['job_featureresume_price_perlisting'], 'tooltip' => __('Additional price to make a resume', 'wp-job-portal') . ' ' . __('featured', 'wp-job-portal')],
                    ['id' => 'job_resume_days_perlisting', 'label' => __('Days Until Featured Resume Expires', 'wp-job-portal') . ' ' . __('Per Listing', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['job_resume_days_perlisting'], 'tooltip' => __('Number of days a', 'wp-job-portal') . ' ' . __('per-listing', 'wp-job-portal') . ' ' . __('featured resume remains active', 'wp-job-portal')],
                    ['id' => 'job_resume_days_free', 'label' => __('Days Until Featured Resume Expires', 'wp-job-portal') . ' ' . __('Free', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['job_resume_days_free'], 'tooltip' => __('Number of days a', 'wp-job-portal') . ' ' . __('free', 'wp-job-portal') . ' ' . __('featured resume remains active', 'wp-job-portal')],
                    ['id' => 'job_jobapply_price_perlisting', 'label' => __('Price per Job Apply', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['job_jobapply_price_perlisting'], 'tooltip' => __('Price for a job seeker to apply for a job', 'wp-job-portal')],
                    ['id' => 'job_viewresumecontact_price_perlisting', 'label' => __('Price to View Resume Contact', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['job_viewresumecontact_price_perlisting'], 'tooltip' => __("Price for an employer to view a resume's contact details", 'wp-job-portal')],
                    ['id' => 'job_viewcompanycontact_price_perlisting', 'label' => __('Price to View Company Contact', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['job_viewcompanycontact_price_perlisting'], 'tooltip' => __("Price for a job seeker to view a company's contact details", 'wp-job-portal')],
                    ['id' => 'job_department_price_perlisting', 'label' => __('Price per', 'wp-job-portal') . ' ' . __('Department', 'wp-job-portal') . ' ' . __('Submission', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['job_department_price_perlisting'], 'tooltip' => __('Price to submit a department', 'wp-job-portal')],
                    ['id' => 'job_coverletter_price_perlisting', 'label' => __('Price per', 'wp-job-portal') . ' ' . __('Cover Letter', 'wp-job-portal') . ' ' . __('Submission', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['job_coverletter_price_perlisting'], 'tooltip' => __('Price to submit a cover letter', 'wp-job-portal')],
                    ['id' => 'job_jobalert_price_perlisting', 'label' => __('Price per', 'wp-job-portal') . ' ' . __('Job Alert', 'wp-job-portal') . ' ' . __('Submission', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['job_jobalert_price_perlisting'], 'tooltip' => __('Price to create a job alert', 'wp-job-portal')],
                ]
            ],
            'dashboard_links' => [
                'title'       => __('Dashboard Monetization Links', 'wp-job-portal'),
                'description' => __('Control the visibility of monetization-related links in user dashboards', 'wp-job-portal'),
                'pro'         => ['slug' => 'credits', 'name' => __('Credits', 'wp-job-portal')],
                'fields'      => [
                    ['id' => 'empcredits', 'label' => __('Employer', 'wp-job-portal') . ' ' . __('Show', 'wp-job-portal') . ' ' . __('Packages', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['empcredits'], 'tooltip' => __("Show a link to the packages page in the employer's dashboard", 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'empcreditlog', 'label' => __('Employer', 'wp-job-portal') . ' ' . __('Show', 'wp-job-portal') . ' ' . __('My Packages', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['empcreditlog'], 'tooltip' => __("Show a link to the user's purchased packages in their dashboard", 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'emppurchasehistory', 'label' => __('Employer', 'wp-job-portal') . ' ' . __('Show', 'wp-job-portal') . ' ' . __('Invoice', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['emppurchasehistory'], 'tooltip' => __("Show a link to the purchase history/invoices in the employer's dashboard", 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'empratelist', 'label' => __('Employer', 'wp-job-portal') . ' ' . __('Show', 'wp-job-portal') . ' ' . __('My Subscriptions', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['empratelist'], 'tooltip' => __("Show a link to active subscriptions in the employer's dashboard", 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    //['id' => 'temp_employer_dashboard_purchase_history', 'label' => __('Employer Show Invoice History', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['temp_employer_dashboard_purchase_history'], 'tooltip' => __('Setting to show the invoice/purchase history section', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'jscredits', 'label' => __('Job Seeker', 'wp-job-portal') . ' ' . __('Show', 'wp-job-portal') . ' ' . __('Packages', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['jscredits'], 'tooltip' => __("Show a link to the packages page in the job seeker's dashboard", 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'jscreditlog', 'label' => __('Job Seeker', 'wp-job-portal') . ' ' . __('Show', 'wp-job-portal') . ' ' . __('My Packages', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['jscreditlog'], 'tooltip' => __("Show a link to the user's purchased packages in their dashboard", 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'jspurchasehistory', 'label' => __('Job Seeker', 'wp-job-portal') . ' ' . __('Show', 'wp-job-portal') . ' ' . __('Invoice', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['jspurchasehistory'], 'tooltip' => __("Show a link to the purchase history/invoices in the job seeker's dashboard", 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'jsratelist', 'label' => __('Job Seeker', 'wp-job-portal') . ' ' . __('Show', 'wp-job-portal') . ' ' . __('My Subscription', 'wp-job-portal') . ' ' . __('Link', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['jsratelist'], 'tooltip' => __("Show a link to active subscriptions in the job seeker's dashboard", 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    //['id' => 'temp_jobseeker_dashboard_purchase_history', 'label' => __('Job Seeker Show Invoice History', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['temp_jobseeker_dashboard_purchase_history'], 'tooltip' => __('Setting to show the invoice/purchase history section', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                    ['id' => 'temp_jobseeker_dashboard_credits_log', 'label' => __('Job Seeker Show Credits Log', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['temp_jobseeker_dashboard_credits_log'], 'tooltip' => __('Setting to show the credits log section', 'wp-job-portal'), 'options' => $wpjobportal_options_showhide],
                ]
            ],
        ]
    ],
];

    // ========================================================
    // 16. PAYMENT GATEWAYS
    // ========================================================

if(in_array('credits',wpjobportal::$_active_addons)){
$wpjobportal_settings_config['payment_gateways'] = [
        'label' => __('Payment Gateways', 'wp-job-portal'),
        'icon'  => 'credit-card',
        'groups' => [
            'paypal' => [
                'title' => __('PayPal', 'wp-job-portal'),
                'description' => __('Configure PayPal payment gateway settings for API integration', 'wp-job-portal'),
                'pro'         => ['slug' => 'credits', 'name' => __('Credits', 'wp-job-portal')],
                'fields' => [
                    ['id' => 'isenabled_paypal', 'label' => __('Enable PayPal', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['isenabled_paypal'], 'tooltip' => __('Enable or disable PayPal as a payment option', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'username_paypal', 'label' => __('Username', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['username_paypal'], 'tooltip' => __('Your PayPal API Username', 'wp-job-portal')],
                    ['id' => 'password_paypal', 'label' => __('Password', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['password_paypal'], 'tooltip' => __('Your PayPal API Password', 'wp-job-portal')],
                    ['id' => 'signature_paypal', 'label' => __('Signature', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['signature_paypal'], 'tooltip' => __('Your PayPal API Signature', 'wp-job-portal')],
                    ['id' => 'logo_paypal', 'label' => __('Logo URL', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['logo_paypal'], 'tooltip' => __('A URL to your logo image to display on the PayPal checkout page', 'wp-job-portal')],
                    ['id' => 'cancelurl_paypal', 'label' => __('Cancel URL', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['cancelurl_paypal'], 'tooltip' => __('The URL users are sent to if they cancel the payment', 'wp-job-portal')],
                    ['id' => 'notifyurl_paypal', 'type' => 'info_text', 'label' => __('Notify URL', 'wp-job-portal'), 'text' => site_url('?page_id=' . wpjobportal::wpjobportal_getPageid() . wpjobportal::$_data[0]['notifyurl_paypal'])],
                    ['id' => 'testmode_paypal', 'label' => __('Test Mode', 'wp-job-portal') . ' ' . __('Sandbox', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['testmode_paypal'], 'tooltip' => __('Enable PayPal Sandbox for testing purposes', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'lang_paypal', 'label' => __('Language', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['lang_paypal'], 'tooltip' => __('Please enter language code, for example', 'wp-job-portal') . ' EN'],
                    ['id' => 'paypal_subscription_info', 'type' => 'info_text', 'label' => __('Subscription IPN URL', 'wp-job-portal'), 'text' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'purchase','action'=>'wpjobportaltask','task'=>'paypalwebhook'))],
                ]
            ],
            'stripe' => [
                'title' => __('Stripe', 'wp-job-portal'),
                'description' => __('Configure Stripe payment gateway settings', 'wp-job-portal'),
                'pro'         => ['slug' => 'credits', 'name' => __('Credits', 'wp-job-portal')],
                'fields' => [
                    ['id' => 'isenabled_stripe', 'label' => __('Enable Stripe', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['isenabled_stripe'], 'tooltip' => __('Enable or disable Stripe as a payment option', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                    ['id' => 'publickey_stripe', 'label' => __('Publishable Key', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['publickey_stripe'], 'tooltip' => __('Enter your Stripe Publishable Key', 'wp-job-portal')],
                    ['id' => 'secretkey_stripe', 'label' => __('Secret Key', 'wp-job-portal'), 'type' => 'text', 'value' => wpjobportal::$_data[0]['secretkey_stripe'], 'tooltip' => __('Enter your Stripe Secret Key', 'wp-job-portal')],
                    ['id' => 'stripe_subscription_info', 'type' => 'info_text', 'label' => __('Subscription Webhook URL', 'wp-job-portal'), 'text' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'purchase','action'=>'wpjobportaltask','task'=>'stripewebhook'))],
                ]
            ],
            'woocommerce' => [
                'title' => __('WooCommerce', 'wp-job-portal'),
                'description' => __('Use WooCommerce to handle payments', 'wp-job-portal'),
                'pro'         => ['slug' => 'credits', 'name' => __('Credits', 'wp-job-portal')],
                'fields' => [
                    ['id' => 'isenabled_woocommerce', 'label' => __('Enable WooCommerce', 'wp-job-portal'), 'type' => 'toggle', 'value' => wpjobportal::$_data[0]['isenabled_woocommerce'], 'tooltip' => __('Enable or disable WooCommerce as a payment option', 'wp-job-portal'), 'options' => $wpjobportal_options_yesno],
                ]
            ],
        ]
    ];

}


// Mock pages for select fields.
$wpjobportal_mock_pages = [
    ['id' => '1', 'title' => __('Home Page', 'wp-job-portal')],
    ['id' => '2', 'title' => __('Sample Page', 'wp-job-portal')],
    ['id' => '3', 'title' => __('WordPress Default', 'wp-job-portal')],
    ['id' => '6', 'title' => __('Blog', 'wp-job-portal')],
];

/**
 * Renders a single setting field based on its configuration.
 *
 * @param array $wpjobportal_field The field configuration array.
 * @param string $wpjobportal_category_key The key of the parent category.
 * @param string $wpjobportal_group_key The key of the parent group.
 * @param array $wpjobportal_mock_pages An array of mock pages for select fields.
 * @param array $wpjobportal_installed_addons An array of slugs for installed addons.
 */
function wpjobportal_wjp_render_setting_field($wpjobportal_field, $wpjobportal_category_key, $wpjobportal_group_key, $wpjobportal_mock_pages, $wpjobportal_installed_addons) {
    global $wpjobportal;
    $wpjobportal_field_id = 'wjp-' . esc_attr($wpjobportal_field['id']);
    $wpjobportal_field_name = esc_attr($wpjobportal_field['id']);
    $wpjobportal_value = isset($wpjobportal::$_data[0][$wpjobportal_field['id']]) ? $wpjobportal::$_data[0][$wpjobportal_field['id']] : ($wpjobportal_field['value'] ?? '');

    $wpjobportal_control_html = '';
    $wpjobportal_pro_label_indicator = '';
    $wpjobportal_pro_field_explanation = '';
    $wpjobportal_is_field_pro = isset($wpjobportal_field['pro']);
    $wpjobportal_is_locked = $wpjobportal_is_field_pro && !in_array($wpjobportal_field['pro']['slug'], $wpjobportal_installed_addons);

    if ($wpjobportal_is_field_pro) { // This block now handles both locked and unlocked pro fields
        $wpjobportal_pro_label_indicator = '<svg class="wjp-pro-label-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="1em" height="1em" fill="currentColor">
                                  <path d="M325.8 200.7l23.5-74.4c5.3-16.7 29.1-16.7 34.4 0l23.5 74.4c5.1 16.1 17.8 28.8 33.9 33.9l74.4 23.5c16.7 5.3 16.7 29.1 0 34.4l-74.4 23.5c-16.1 5.1-28.8 17.8-33.9 33.9l-23.5 74.4c-5.3 16.7-29.1 16.7-34.4 0l-23.5-74.4c-5.1-16.1-17.8-28.8-33.9-33.9l-74.4-23.5c-16.7-5.3-16.7-29.1 0-34.4l74.4-23.5c16.1-5.1 28.8-17.8 33.9-33.9zM107.2 92l13.6-43.1c3-9.6 16.8-9.6 19.8 0l13.6 43.1c2.9 9.3 10.3 16.6 19.6 19.6l43.1 13.6c9.6 3 9.6 16.8 0 19.8l-43.1 13.6c-9.3 2.9-16.6 10.3-19.6 19.6l-13.6 43.1c-3 9.6-16.8 9.6-19.8 0l-13.6-43.1c-2.9-9.3-10.3-16.6-19.6-19.6l-43.1-13.6c-9.6-3-9.6-16.8 0-19.8l43.1-13.6c9.3-2.9 16.6-10.3 19.6-19.6zM69.8 440.6l7.9-24.9c1.7-5.5 9.7-5.5 11.4 0l7.9 24.9c1.7 5.4 5.9 9.6 11.3 11.3l24.9 7.9c5.5 1.7 5.5 9.7 0 11.4l-24.9 7.9c-5.4 1.7-9.6 5.9-11.3 11.3l-7.9 24.9c-1.7 5.5-9.7 5.5-11.4 0l-7.9-24.9c-1.7-5.4-5.9-9.6-11.3-11.3l-24.9-7.9c-5.5-1.7-5.5-9.7 0-11.4l24.9-7.9c5.4-1.7 9.6-5.9 11.3-11.3z"/>
                                </svg>';

        if ($wpjobportal_is_locked) {
            $wpjobportal_pro_field_explanation = '<p class="wjp-pro-field-explanation">' . esc_html__('This feature is part of a premium addon', 'wp-job-portal') . '</p>';

            $base_control_html = '';
            switch ($wpjobportal_field['type']) {
                case 'toggle':
                    $base_control_html = '<label class="wjp-toggle-switch"><input type="checkbox" disabled><span class="wjp-toggle-slider"></span></label>';
                    break;
                case 'select':
                    //$wpjobportal_first_option_label = is_array($wpjobportal_field['options']) && !empty($wpjobportal_field['options']) ? reset($wpjobportal_field['options']) : '';
                    $wpjobportal_first_option = is_array($wpjobportal_field['options']) && !empty($wpjobportal_field['options']) ? reset($wpjobportal_field['options']) : '';
                    if (is_object($wpjobportal_first_option)) {
                        // adjust based on your data structure, e.g. ->label or ->name
                        $wpjobportal_first_option_label = isset($wpjobportal_first_option->text) ? $wpjobportal_first_option->text : '';
                    } elseif (is_array($wpjobportal_first_option)) {
                        // adjust based on your array keys
                        $wpjobportal_first_option_label = isset($wpjobportal_first_option['text']) ? $wpjobportal_first_option['text'] : '';
                    } else {
                        $wpjobportal_first_option_label = $wpjobportal_first_option;
                    }
                    $base_control_html = '<select class="wjp-form-select" disabled><option>' . esc_html($wpjobportal_first_option_label) . '</option></select>';
                    break;
                default:
                    $base_control_html = '<input type="text" class="wjp-form-input" disabled>';
                    break;
            }

            $wpjobportal_control_html = '
                <div class="wjp-pro-control-wrapper">
                    <div class="disabled-control-bg">' . $base_control_html . '</div>
                    <div class="upgrade-overlay">
                        <a href="#" class="wjp-btn wjp-btn-upgrade-overlay">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="1em" height="1em" fill="currentColor">
                              <path d="M400 224h-24v-72C376 68.2 307.8 0 224 0S72 68.2 72 152v72H48c-26.5 0-48 21.5-48 48v192c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V272c0-26.5-21.5-48-48-48zm-104 0H152v-72c0-39.7 32.3-72 72-72s72 32.3 72 72v72z"/>
                            </svg>
                            <span>' . __('Unlock with', 'wp-job-portal').' ' .wpjobportal::wpjobportal_getVariableValue($wpjobportal_field['pro']['name']) . '</span>
                        </a>
                    </div>
                </div>';
        }
        // If it's a pro field but NOT locked (addon is active), it will fall through to the default rendering below.
    }

    // This block now renders regular fields AND unlocked pro fields.
    if (!$wpjobportal_is_locked) {
        switch ($wpjobportal_field['type']) {
            case 'toggle':
                $wpjobportal_checked = checked($wpjobportal_value, '1', false);
                $wpjobportal_control_html = '<label class="wjp-toggle-switch">
                <input type="hidden" name="' . $wpjobportal_field_name . '" value="0">' . '
                <input type="checkbox" id="' . $wpjobportal_field_id . '" name="' . $wpjobportal_field_name . '" value="1" ' . $wpjobportal_checked . '><span class="wjp-toggle-slider"></span></label>';
                break;
            case 'seo_tags':
                $available_tags_json = esc_attr(json_encode($wpjobportal_field['available_tags']));
                $wpjobportal_control_html = '
                    <div class="seo-field-container"
                         data-id="' . esc_attr($wpjobportal_field['id']) . '"
                         data-initial-value="' . esc_attr($wpjobportal_value) . '"
                         data-available-tags=\'' . $available_tags_json . '\'>

                        <div class="tags-used-container"></div>
                        <div class="tags-available-label">' . __('Available tags', 'wp-job-portal') . '</div>
                        <div class="tags-available-container"></div>
                        <input type="hidden" id="' . $wpjobportal_field_id . '" name="' . $wpjobportal_field_name . '" value="' . esc_attr($wpjobportal_value) . '">
                    </div>';
                break;
            case 'info_text':
                $wpjobportal_control_html = '<div class="wjp-info-text">' . esc_html($wpjobportal_field['text']) . '
                    <svg class="wjp-copy-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="1em" height="1em" fill="currentColor">
                      <path d="M384 336H192c-8.8 0-16-7.2-16-16V64c0-8.8 7.2-16 16-16l140.1 0L400 115.9V320c0 8.8-7.2 16-16 16zM192 384h192c35.3 0 64-28.7 64-64V115.9c0-12.7-5.1-24.9-14.1-33.9L366.1 14.1c-9-9-21.2-14.1-33.9-14.1H192c-35.3 0-64 28.7-64 64v256c0 35.3 28.7 64 64 64zM64 128c-35.3 0-64 28.7-64 64v256c0 35.3 28.7 64 64 64h192c35.3 0 64-28.7 64-64v-32h-48v32c0 8.8-7.2 16-16 16H64c-8.8 0-16-7.2-16-16V192c0-8.8 7.2-16 16-16h32v-48H64z"/>
                    </svg>
                </div>';
                break;
            case 'select':
                // Assuming WPJOBPORTALformfield is a class you have that generates form fields
                $wpjobportal_options_obj = [];
                $current_options = $wpjobportal_field['options'] ?? [];
                 if (!empty($current_options)) {
                    if (!empty($current_options)) {
                        $wpjobportal_first_option = reset($current_options);
                        if (is_object($wpjobportal_first_option) && isset($wpjobportal_first_option->id) && isset($wpjobportal_first_option->text)) {
                            // It's already an array of objects, use it directly.
                            $wpjobportal_options_obj = $current_options;
                        } else {
                            // It's a key-value array, convert it.
                            foreach ($current_options as $wpjobportal_key => $wpjobportal_label) {
                                $wpjobportal_options_obj[] = (object)['id' => $wpjobportal_key, 'text' => $wpjobportal_label];
                            }
                        }
                    }

                }
                $wpjobportal_control_html = WPJOBPORTALformfield::select($wpjobportal_field_name, $wpjobportal_options_obj, $wpjobportal_value, '', ['class' => 'wjp-form-select', 'id' => $wpjobportal_field_id]);
                break;
             case 'textarea':
                $wpjobportal_control_html = WPJOBPORTALformfield::textarea($wpjobportal_field_name, $wpjobportal_value, ['class' => 'wjp-form-textarea', 'rows' => $wpjobportal_field['rows'] ?? 4, 'id' => $wpjobportal_field_id]);
                break;
            // case 'file':
            //      $wpjobportal_img_path = WPJOBPORTAL_PLUGIN_URL . 'includes/images/default_logo.png';
            //     if(isset(wpjobportal::$_data[0]['default_image']) && wpjobportal::$_data[0]['default_image'] != ''){
            //         $wpjobportal_img_path = wp_get_attachment_url(wpjobportal::$_data[0]['default_image']);
            //     }
            //     $wpjobportal_control_html = '
            //         <div class="wjp-file-input-wrapper">
            //             <img src="' . esc_url($wpjobportal_img_path) . '" alt="Preview">
            //             <div class="wjp-file-input-actions">
            //                 <label for="' . $wpjobportal_field_id . '" class="wjp-btn wjp-btn-secondary">
            //                     <i class="fas fa-upload"></i>
            //                     <span>' . esc_html__('Upload Image', 'wp-job-portal') . '</span>
            //                 </label>
            //                 <input type="file" id="' . $wpjobportal_field_id . '" name="' . $wpjobportal_field_name . '" style="display:none;">
            //             </div>
            //         </div>';
            //     break;
            case 'file':
                $wpjobportal_default_image = isset(wpjobportal::$_data[0]['default_image']) ? wpjobportal::$_data[0]['default_image'] : '';
                $wpjobportal_data_directory = WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue('data_directory');
                $wpjobportal_wpdir = wp_upload_dir();

                if (!empty($wpjobportal_default_image)) {
                    $wpjobportal_img_path = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/default_image/' . $wpjobportal_default_image;
                    $wpjobportal_display_style = 'block';
                } else {
                    $wpjobportal_img_path = WPJOBPORTAL_PLUGIN_URL . 'includes/images/default_logo.png';
                    $wpjobportal_display_style = 'none';
                }

                $wpjobportal_logoformat = wpjobportal::$_config->getConfigValue('image_file_type');
                $wpjobportal_maxsize = wpjobportal::$_config->getConfigValue('image_file_size');

                $wpjobportal_control_html = '
                <div class="wjp-file-input-wrapper">
                    <div class="wjportal-form-image-wrp" style="display:' . esc_attr($wpjobportal_display_style) . ';">
                        <img class="wpjobportal-config-default-image" src="' . esc_url($wpjobportal_img_path) . '" id="rs_photo" alt="Preview" />
                        <img id="wjportal-form-delete-image" src="' . esc_url(WPJOBPORTAL_PLUGIN_URL . 'includes/images/no.png') . '" alt="Remove">
                    </div>

                    <div class="wjp-file-input-actions">
                        <label for="' . esc_attr($wpjobportal_field_id) . '" class="wjp-btn wjp-btn-secondary">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="1em" height="1em" fill="currentColor">
                              <path d="M105.4 182.6c12.5 12.49 32.76 12.5 45.25 .001L224 109.3V352c0 17.67 14.33 32 32 32c17.67 0 32-14.33 32-32V109.3l73.38 73.38c12.49 12.49 32.75 12.49 45.25-.001c12.49-12.49 12.49-32.75 0-45.25l-128-128c-12.5-12.5-32.75-12.5-45.25 0l-128 128C92.93 149.9 92.93 170.1 105.4 182.6zM480 352h-32C421.5 352 400 373.5 400 400v32h-288V400c0-26.5-21.49-48-48-48H32c-17.67 0-32 14.33-32 32v32c0 53.02 42.98 96 96 96h320c53.02 0 96-42.98 96-96v-32C512 366.3 497.7 352 480 352z"/>
                            </svg>
                            <span>' . esc_html__('Upload Image', 'wp-job-portal') . '</span>
                        </label>
                        <input type="file" id="' . esc_attr($wpjobportal_field_id) . '" name="' . esc_attr($wpjobportal_field_name) . '" accept="image/*" style="display:none;">
                        <input type="hidden" id="remove_default_image" name="remove_default_image" value="0">
                    </div>

                    <div class="wjportal-form-help-txt">' . esc_html($wpjobportal_logoformat) . '&nbsp;' . esc_html__('Maximum', 'wp-job-portal') . ' ' . esc_html($wpjobportal_maxsize) . ' Kb ' . esc_html__('This image will be shown as the default image for entities & users if no other image is provided', 'wp-job-portal') . '</div>
                </div>
                ';
                break;

            default:
                $wpjobportal_control_html = WPJOBPORTALformfield::text($wpjobportal_field_name, $wpjobportal_value, ['class' => 'wjp-form-input', 'type' => $wpjobportal_field['type'], 'id' => $wpjobportal_field_id]);
                break;
        }
    }


    $wpjobportal_tooltip_html = !empty($wpjobportal_field['tooltip']) ? '<span class="wjp-tooltip-wrapper" data-tooltip="' . esc_attr($wpjobportal_field['tooltip']) . '">
        <svg class="wjp-tooltip-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="1em" height="1em" fill="currentColor">
          <path d="M504 256c0 136.997-111.043 248-248 248S8 392.997 8 256C8 119.083 119.043 8 256 8s248 111.083 248 248zM262.655 90c-54.497 0-89.255 22.957-116.549 63.758-3.536 5.286-2.353 12.415 2.715 16.258l34.699 26.31c5.205 3.947 12.621 3.008 16.812-2.188 28.891-35.816 57.067-27.766 52.822 5.06-2.909 22.472-24.966 38.007-44.502 65.652-9.256 13.097-12.656 24.225-12.656 48.094 0 7.55 5.592 13.91 13.064 14.948l47.433 6.591c8.031 1.116 15.341-5.115 15.341-13.235 0-14.78 12.822-26.699 22.146-37.119 14.52-16.223 35.857-36.425 41.792-71.186 9.873-57.828-36.255-92.943-73.117-92.943zM256 368c-22.091 0-40 17.909-40 40s17.909 40 40 40 40-17.909 40-40-17.909-40-40-40z"/>
        </svg>
    </span>' : '';
    $conditional_attrs = !empty($wpjobportal_field['condition']) ? 'data-condition-field="' . esc_attr($wpjobportal_field['condition']['field']) . '" data-condition-value="' . esc_attr($wpjobportal_field['condition']['value']) . '"' : '';
    $wrapper_class = 'wjp-setting-row-wrapper ' . (!empty($wpjobportal_field['condition']) ? 'wjp-conditional-field-wrapper ' : '') . ($wpjobportal_is_locked ? 'is-pro-field' : '');

    echo '<div class="' . esc_attr($wrapper_class) . '" ' . esc_attr($conditional_attrs) . '>';
    echo '  <div class="wjp-setting-row">';
    echo '      <div class="wjp-setting-label">';
    echo '          <label for="' . esc_attr($wpjobportal_field_id) . '">' . esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_field['label'])) . wp_kses($wpjobportal_pro_label_indicator,WPJOBPORTAL_ALLOWED_TAGS) . wp_kses($wpjobportal_tooltip_html,WPJOBPORTAL_ALLOWED_TAGS) . '</label>';
    echo '      </div>';
    echo '      <div class="wjp-setting-control" data-field-id="' . esc_attr($wpjobportal_field['id']) . '">';
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped // Admin side interface values are handled safelty
    echo $wpjobportal_control_html;
    echo wp_kses($wpjobportal_pro_field_explanation,WPJOBPORTAL_ALLOWED_TAGS); // This will be empty unless the field is locked
    echo '      </div>';
    echo '  </div>';
    echo '</div>';
}

$wpjobportal_msgkey = WPJOBPORTALincluder::getJSModel('configuration')->getMessagekey();
WPJOBPORTALMessages::getLayoutMessage($wpjobportal_msgkey); ?>

<div id="wpjobportaladmin-wrapper">
    <div id="wpjobportaladmin-leftmenu">
        <?php  WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <div id="wjp-config-dashboard">
            <div id="app">
                <aside id="wjp-sidebar">
                    <div class="wjp-sidebar-header">
                        <div class="wjp-logo-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" width="1.25em" height="1em" fill="currentColor">
                              <path d="M512.1 192c0-10.6-3.7-20.8-10.6-28.8l-30-36c-11.2-13.5-12.7-33-2.9-48.2l12.7-19.6c8.1-12.6 8.1-29 0-41.6L443.2 4.9C433.8-3.1 420.2-2.3 408.8 8l-23 18.4c-12.3 9.9-29.6 9.9-42 0L320.8 8c-11.4-10.3-25-11.1-34.4-3.1l-38.1 12.9c-8.1 12.6-8.1 29 0 41.6l12.7 19.6c9.8 15.2 8.3 34.7-2.9 48.2l-30 36c-6.9 8-10.6 18.2-10.6 28.8s3.7 20.8 10.6 28.8l30 36c11.2 13.5 12.7 33 2.9 48.2l-12.7 19.6c-8.1 12.6-8.1 29 0 41.6l38.1 12.9c9.4 8 23 7.2 34.4-3.1l23-18.4c12.3-9.9 29.6-9.9 42 0l23 18.4c11.4 10.3 25 11.1 34.4 3.1l38.1-12.9c8.1-12.6 8.1-29 0-41.6l-12.7-19.6c-9.8-15.2-8.3-34.7 2.9-48.2l30-36c6.9-8 10.6-18.2 10.6-28.8zM416 256c-35.3 0-64-28.7-64-64s28.7-64 64-64 64 28.7 64 64-28.7 64-64 64zM245.9 313.3l-20.7-16.6c-13.7-11-33-11-46.7 0l-20.7 16.6c-12.6 10.1-29.8 11.9-44.2 4.4L87.5 304c-12.7-6.6-19.4-21.4-15.6-35.1l7-26.1c4.3-16.9-3.2-34.6-18.4-43.4l-23.7-13.7c-12.1-7-18.2-21.5-13.9-34.9l9.3-25.9c4.3-11.9 14.9-19.9 27.6-19.9l27.3 .1c17.5 0 33.1-10.7 39.2-27.2l9.4-25.5c4.6-12.6 .9-27-9.4-35.2L98.6 3.9c-10.3-8.2-25.3-7.5-34.8 1.9L40 29.7C15.2 54.5 0 88.5 0 123.6V208c0 13.3 10.7 24 24 24h3.7c17.6 0 33.1 10.7 39.2 27.2l9.4 25.5c4.6 12.6 .9 27-9.4 35.2l-20.7 16.6c-10.3 8.2-13.6 22.8-7.7 34.8l12.9 26.2c6 12.2 19.5 19 32.8 16.6l26.7-4.7c17.3-3.1 34.1 6.3 40.5 22.6l10 25.4c4.8 12.3 16.8 20.2 30 20.2h27.4c13.3 0 24-10.7 24-24v-4.1c0-17.6-10.7-33.1-27.2-39.2l-25.5-9.4c-12.6-4.6-27-.9-35.2 9.4zM128 208c-26.5 0-48-21.5-48-48s21.5-48 48-48 48 21.5 48 48-21.5 48-48 48z"/>
                            </svg>
                        </div>
                        <div class="wjp-title"><?php echo esc_html__('Settings', 'wp-job-portal'); ?></div>
                    </div>
                    <nav id="wjp-settings-nav"></nav>
                </aside>
                <main>
                     <header>
                        <div class="wjp-header-left">
                             <div id="wjp-main-panel-header">
                                <span id="wjp-category-title-span"></span>
                            </div>
                        </div>
                        <div class="wjp-header-actions">
                            <div id="wjp-discard-changes" class="wjp-btn wjp-btn-secondary" style="display: none;">
                                <span><?php echo esc_html__('Discard', 'wp-job-portal'); ?></span>
                            </div>
                            <div id="wjp-save-changes" class="wjp-btn wjp-btn-primary" disabled>
                                <span><?php echo esc_html__('Save Changes', 'wp-job-portal'); ?></span>
                                <svg style="display: none;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="1em" height="1em" fill="currentColor">
                                  <path d="M433.1 129.1l-83.9-83.9C342.3 38.32 327.1 32 316.1 32H64C28.65 32 0 60.65 0 96v320c0 35.35 28.65 64 64 64h320c35.35 0 64-28.65 64-64V163.9C448 152.9 441.7 137.7 433.1 129.1zM224 416c-35.34 0-64-28.66-64-64s28.66-64 64-64s64 28.66 64 64S259.3 416 224 416zM320 208C320 216.8 312.8 224 304 224h-224C71.16 224 64 216.8 64 208v-96C64 103.2 71.16 96 80 96h224C312.8 96 320 103.2 320 112V208z"/>
                                </svg>
                            </div>
                        </div>
                    </header>
                    <div id="wjp-main-panel-content">
                        <div class="wjp-search-panel">
                            <div class="wjp-search-wrapper">
                                 <div class="wjp-search-input-wrapper">
                                   <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="1em" height="1em" fill="currentColor">
                                      <path d="M505 442.7L405.3 343c-4.5-4.5-10.6-7-17-7H372c27.6-35.3 44-79.7 44-128C416 93.1 322.9 0 208 0S0 93.1 0 208s93.1 208 208 208c48.3 0 92.7-16.4 128-44v16.3c0 6.4 2.5 12.5 7 17l99.7 99.7c9.4 9.4 24.6 9.4 33.9 0l28.3-28.3c9.4-9.4 9.4-24.6.1-34zM208 336c-70.7 0-128-57.2-128-128 0-70.7 57.2-128 128-128 70.7 0 128 57.2 128 128 0 70.7-57.2 128-128 128z"/>
                                    </svg>
                                    <input type="text" id="wjp-search-box" placeholder="<?php echo esc_attr__('Quickly find any setting', 'wp-job-portal'); ?>">
                                 </div>
                                 <p class="wjp-search-description"><?php echo esc_html__('Search by setting title or tooltip description', 'wp-job-portal'); ?></p>
                            </div>
                        </div>
                        <div id="wjp-sticky-sub-nav-wrapper" class="wjp-hidden">
                            <div id="wjp-sub-nav"></div>
                        </div>
                        <form id="wpjobportal-form" class="wpjobportal-configurations" method="post" action="<?php echo esc_url_raw(admin_url("admin.php?page=wpjobportal_configuration&task=saveconfiguration")); ?>"  enctype="multipart/form-data">
                             <div class="wjp-content-wrapper">
                                 <div id="wjp-main-panel">
                                    <?php
                                    $wpjobportal_installed_addons = wpjobportal::$_active_addons;
                                    foreach ($wpjobportal_settings_config as $wpjobportal_category_key => $wpjobportal_category) : ?>
                                        <div class="wjp-category-container" data-category-key="<?php echo esc_attr($wpjobportal_category_key); ?>">
                                            <?php foreach ($wpjobportal_category['groups'] as $wpjobportal_group_key => $wpjobportal_group) :
                                                $wpjobportal_is_group_pro = isset($wpjobportal_group['pro']);
                                                $wpjobportal_is_group_locked = $wpjobportal_is_group_pro && !in_array($wpjobportal_group['pro']['slug'], $wpjobportal_installed_addons);
                                            ?>
                                                <div class="wjp-group-card <?php echo $wpjobportal_is_group_locked ? 'is-pro-group' : ''; ?>" id="wjp-group-<?php echo esc_attr($wpjobportal_group_key); ?>" data-groupkey="<?php echo esc_attr($wpjobportal_group_key); ?>">
                                                    <div class="wjp-group-header">
                                                        <div class="wjp-title"><?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_group['title'])); ?></div>
                                                        <p><?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_group['description'])); ?></p>
                                                    </div>
                                                    <?php if ($wpjobportal_is_group_locked) : ?>
                                                        <div class="wjp-pro-group-cta">
                                                             <div class="cta-icon">
                                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" width="1em" height="1em" fill="currentColor">
                                                                  <path d="M316.9 18C311.6 7 300.4 0 288.1 0s-23.4 7-28.8 18L195 150.3 51.4 171.5c-12 1.8-22 10.2-25.7 21.7s-.7 24.2 7.9 32.7L137.8 329 113.2 474.7c-2 12 3 24.2 12.9 31.3s23 8 33.8 2.3l128.3-68.5 128.3 68.5c10.8 5.7 23.9 4.9 33.8-2.3s14.9-19.3 12.9-31.3L438.5 329 542.7 225.9c8.6-8.5 11.7-21.2 7.9-32.7s-13.7-19.9-25.7-21.7L381.2 150.3 316.9 18z"/>
                                                                </svg>
                                                             </div>
                                                             <div class="cta-text">
                                                                <h3><?php echo esc_html__('Unlock', 'wp-job-portal') .' ' . esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_group['title'])); ?></h3>
                                                                <p><?php echo esc_html__('This and other powerful features are available in the', 'wp-job-portal'). '<strong>' . esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_group['pro']['name'])) . '</strong>'; ?></p>
                                                             </div>
                                                             <a href="#" class="wjp-btn wjp-btn-primary"><?php esc_html_e('Upgrade Now', 'wp-job-portal'); ?></a>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="wjp-fields-container">
                                                        <?php foreach ($wpjobportal_group['fields'] as $wpjobportal_field) {
                                                            wpjobportal_wjp_render_setting_field($wpjobportal_field, $wpjobportal_category_key, $wpjobportal_group_key, $wpjobportal_mock_pages,$wpjobportal_installed_addons);
                                                        } ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endforeach; ?>
                                    <div id="wjp-no-results-message" style="display: none; text-align: center; padding: 64px 0;">
                                        <?php echo esc_html__('No settings found for your search', 'wp-job-portal'); ?>
                                    </div>
                                 </div>
                             </div>
                             <?php echo wp_kses(WPJOBPORTALformfield::hidden('wpjobportallt', 'configurations'),WPJOBPORTAL_ALLOWED_TAGS); ?>
                             <?php echo wp_kses(WPJOBPORTALformfield::hidden('action', 'configuration_saveconfiguration'),WPJOBPORTAL_ALLOWED_TAGS); ?>
                             <?php echo wp_kses(WPJOBPORTALformfield::hidden('form_request', 'wpjobportal'),WPJOBPORTAL_ALLOWED_TAGS); ?>
                             <?php echo wp_kses(WPJOBPORTALformfield::hidden('_wpnonce', esc_html(wp_create_nonce('wpjobportal_configuration_nonce'))),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        </form>
                    </div>
                </main>
            </div>
            <div id="wjp-mobile-menu-toggle" class="wjp-btn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="1em" height="1em" fill="currentColor">
                  <path d="M0 96C0 78.3 14.3 64 32 64H416c17.7 0 32 14.3 32 32s-14.3 32-32 32H32C14.3 128 0 113.7 0 96zM0 256c0-17.7 14.3-32 32-32H416c17.7 0 32 14.3 32 32s-14.3 32-32 32H32c-17.7 0-32-14.3-32-32zM448 416c0 17.7-14.3 32-32 32H32c-17.7 0-32-14.3-32-32s14.3-32 32-32H416c17.7 0 32 14.3 32 32z"/>
                </svg>
            </div>
            <div id="wjp-toast-container"></div>
        </div>
    </div>
</div>
<?php
$wpjp_config_js = '
(function($) {
    "use strict";

    const App = {
        // --- APP STATE & CONFIG ---
        settingsConfig: ' . wp_json_encode($wpjobportal_settings_config) . ',
        activeCategory: "system_site",
        changesToSubmit: {},

        // --- INITIALIZATION ---
        init: function() {
            this.renderSidebar();
            this.showCategory(this.activeCategory);
            this.addEventListeners();
            this.initSeoFields();
            this.initCopyButtons();
        },

        initSeoFields: function() {
            $(".seo-field-container").each(function() {
                const $container = $(this);
                const fieldId = $container.data("id");

                const initialValueStr = $container.attr("data-initial-value") || "";
                const matches = initialValueStr.match(/\[(.*?)\]/g);
                const initialTags = matches ? matches.map(tag => tag.slice(1, -1)) : [];

                const state = {
                    currentValue: initialTags,
                    allAvailableTags: $container.data("available-tags") || []
                };

                const render = () => {
                    const $usedTags = $container.find(".tags-used-container").empty();
                    const $availableTags = $container.find(".tags-available-container").empty();

                    const availableTags = state.allAvailableTags.filter(
                        tag => !state.currentValue.includes(tag)
                    );

                    // Render used tags
                    state.currentValue.forEach(tag => {
                        $usedTags.append(
                            `<span class="template-tag">
                                ${tag}
                                <span class="template-tag-remove" data-tag="${tag}">X</span>
                            </span>`
                        );
                    });

                    // Render available tags
                    availableTags.forEach(tag => {
                        $availableTags.append(
                            `<span class="template-tag-available" data-tag="${tag}">${tag}</span>`
                        );
                    });

                    // Update hidden input value
                    const formattedValue = state.currentValue.map(tag => `[${tag}]`).join("");
                    $container.find("input[type=\"hidden\"]").val(formattedValue);
                };

                const updateAndNotify = () => {
                    render();
                    const formattedValue = state.currentValue.map(tag => `[${tag}]`).join("");
                    App.handleValueChange(fieldId, formattedValue);
                };

                $container.on("click", ".template-tag-available", function() {
                    state.currentValue.push($(this).data("tag"));
                    updateAndNotify();
                });

                $container.on("click", ".template-tag-remove", function() {
                    const tagToRemove = $(this).data("tag");
                    state.currentValue = state.currentValue.filter(tag => tag !== tagToRemove);
                    updateAndNotify();
                });

                // Initial Render
                render();
            });
        },
        initCopyButtons: function() {
            $("#wjp-main-panel").on("click", ".wjp-copy-icon", function() {
                const textToCopy = $(this).parent().text().trim();
                navigator.clipboard.writeText(textToCopy).then(() => {
                    App.showToast("' . esc_js(__('Copied to clipboard', 'wp-job-portal')) . '", "info");
                }).catch(err => {
                    console.error("Could not copy text: ", err);
                });
            });
        },
        // --- HELPER FUNCTIONS ---
        updateSidebarActiveState: function(isSearching = false) {
            $("#wjp-settings-nav a").each(function() {
                $(this).toggleClass("active", !isSearching && $(this).data("category") === App.activeCategory);
            });
        },

        updateSaveButtonState: function() {
            const hasChanges = Object.keys(this.changesToSubmit).length > 0;
            $("#wjp-save-changes").prop("disabled", !hasChanges);
            $("#wjp-discard-changes").toggle(hasChanges);
        },

        showToast: function(message, type = "success") {
            const icon = type === "success" ? "fa-check-circle" : "fa-info-circle";
            $("<div>", { class: `wjp-toast ${type}`, html: `<i class="fas ${icon}"></i><span>${message}</span>`})
                .appendTo("#wjp-toast-container")
                .on("animationend", function() { if ($(this).css("opacity") == 0) $(this).remove(); });
        },

        updateConditionalVisibility: function($wrapper) {
            const controllerId = $wrapper.data("conditionField");
            const $wpjobportal_controller = $(`#wjp-${controllerId}`);
            let controllerValue = $wpjobportal_controller.is("[type=checkbox]") ? ($wpjobportal_controller.is(":checked") ? "1" : "0") : $wpjobportal_controller.val();
            const isVisible = String(controllerValue) === String($wrapper.data("conditionValue"));
            $wrapper.toggleClass("visible", isVisible);
        },

        updateAllConditionalFields: function() {
            $(".wjp-conditional-field-wrapper").each(function() {
                App.updateConditionalVisibility($(this));
            });
        },

        // --- RENDER FUNCTIONS ---
        renderSidebar: function() {
            const $ul = $("<ul>");
            $.each(this.settingsConfig, (key, category) => {
                $("<li>").html(
                    $("<a>", { href: "#", "data-category": key, class: `wpjp-icon-class-${category.icon}`  })
                        //.append($("<i>", { class: `fas fa-${category.icon}` }))
                        .append($("<span>").text(category.label))
                ).appendTo($ul);
            });
            $("#wjp-settings-nav").empty().append($ul);
        },

        renderSubNav: function(category) {
            const $wrapper = $("#wjp-sticky-sub-nav-wrapper");
            const $container = $("#wjp-sub-nav");
            const groupKeys = Object.keys(category.groups);

            if (groupKeys.length > 0) {
                const navLinks = groupKeys.map((key, index) =>
                    $("<a>", {
                        href: `#wjp-group-${key}`,
                        class: `wjp-sub-nav-link ${index === 0 ? "active" : ""}`,
                        text: category.groups[key].title
                    })
                );
                $container.empty().append($("<div>").append(navLinks));
                $wrapper.removeClass("wjp-hidden");
            } else {
                $container.empty();
                $wrapper.addClass("wjp-hidden");
            }
        },

        showCategory: function(categoryKey) {
            this.activeCategory = categoryKey;
            $(".wjp-category-container").hide();
            $(`.wjp-category-container[data-category-key="${categoryKey}"]`).show();
            $("#wjp-category-title-span").text(this.settingsConfig[categoryKey].label);

            this.updateSidebarActiveState();
            this.renderSubNav(this.settingsConfig[categoryKey]);
            this.updateAllConditionalFields();
        },

        // --- EVENT HANDLERS ---
        handleSidebarNav: function(e) {
            e.preventDefault();
            $("#wjp-main-panel-content").scrollTop(0);
            $("#wjp-search-box").val("").trigger("input");
            App.showCategory($(e.currentTarget).data("category"));
            $("#wjp-sidebar").removeClass("is-open");
        },

        handleSubNavScroll: function(e) {
            e.preventDefault();
            // Add these two lines to immediately highlight the clicked pill
            $("#wjp-sub-nav a.wjp-sub-nav-link").removeClass("active");
            $(e.currentTarget).addClass("active");
            const $wpjobportal_target = $($(e.currentTarget).attr("href"));
            if ($wpjobportal_target.length) {
                const $scrollContainer = $("#wjp-main-panel-content");
                const stickyNavHeight = $("#wjp-sticky-sub-nav-wrapper").outerHeight(true) || 0;
                const scrollTop = $scrollContainer.scrollTop() + $wpjobportal_target.position().top - stickyNavHeight - 16;
                $scrollContainer.stop().animate({ scrollTop }, 500);
            }
        },

        handleSearch: function(e) {
            const query = $(e.target).val().toLowerCase().trim();

            if (!query) {
                $("#wjp-main-panel .wjp-group-card, #wjp-main-panel .wjp-setting-row-wrapper").show();
                $("#wjp-no-results-message").hide();
                App.showCategory(App.activeCategory);
                return;
            }

            $(".wjp-category-container").show();
            $("#wjp-category-title-span").text(`' . esc_js(__('Search results for', 'wp-job-portal')) . ' "${query}"`);
            $("#wjp-sticky-sub-nav-wrapper").addClass("wjp-hidden");
            let matchCount = 0;

            $(".wjp-setting-row-wrapper").each(function() {
                const label = $(this).find("label").text().toLowerCase();
                const tooltip = $(this).find(".wjp-tooltip-wrapper").data("tooltip")?.toLowerCase() || "";
                if (label.includes(query) || tooltip.includes(query)) {
                    $(this).show();
                    matchCount++;
                } else {
                    $(this).hide();
                }
            });

            $(".wjp-group-card").each(function() {
                $(this).toggle($(this).find(".wjp-setting-row-wrapper:visible").length > 0);
            });

            $(".wjp-category-container").each(function() {
                $(this).toggle($(this).find(".wjp-group-card:visible").length > 0);
            });

            $("#wjp-no-results-message").toggle(matchCount === 0);
            App.updateSidebarActiveState(true);
        },

        handleFieldChange: function(e) {
            const $wpjobportal_target = $(e.target);
            const $wrapper = $wpjobportal_target.closest(".wjp-setting-control");
            if (!$wrapper.length) return;

            const id = $wrapper.data("fieldId");
            let value;

            if ($wpjobportal_target.is("input[type=checkbox]")) {
                value = $wpjobportal_target.is(":checked") ? "1" : "0";
            } else if ($wpjobportal_target.is(".wjp-btn-segment")) {
                value = $wpjobportal_target.data("value");
                $wpjobportal_target.addClass("active").siblings().removeClass("active");
                $wrapper.find(`#wjp-${id}`).val(value);
            } else if ($wpjobportal_target.closest(".wjp-redirect-control").length) {
                const $wpjobportal_select = $wrapper.find("select");
                const $customInput = $wrapper.find("input[type=text]");
                if ($wpjobportal_target.is("select")) { $customInput.toggleClass("wjp-hidden", $wpjobportal_target.val() !== "custom"); }
                value = $wpjobportal_select.val() === "custom" ? $customInput.val() : $wpjobportal_select.val();
                $wrapper.find(`#wjp-${id}`).val(value);
            } else if ($wpjobportal_target.data("action") === "upload") {
                $wrapper.find("input[type=file]").click(); return;
            } else if ($wpjobportal_target.is("input[type=file]")) {
                    // const file = e.target.files[0];
                    // const $wpjobportal_img = $wrapper.find("img.wpjobportal-config-default-image");
                    // const $removeInput = $wrapper.find("input[type=hidden][name=\'remove_default_image\']");
                    // const $removeBtn = $wrapper.find("#wjportal-form-delete-image");
                    // const $wpjobportal_imageWrap = $wrapper.find(".wjportal-form-image-wrp");

                    // if (file) {
                    //     const reader = new FileReader();
                    //     reader.onload = function(event) {
                    //         $wpjobportal_img.attr("src", event.target.result);
                    //         $wpjobportal_imageWrap.show();
                    //         if ($removeInput.length) {
                    //             $removeInput.val("0");
                    //         }
                    //         App.handleValueChange(id, event.target.result);
                    //     };
                    //     reader.readAsDataURL(file);
                    // }
                    return;
                } else if ($wpjobportal_target.data("action") === "remove" || $wpjobportal_target.is("#wjportal-form-delete-image")) {
                    const $removeInput = $wrapper.find("input[type=hidden][name=\'remove_default_image\']");
                    if ($removeInput.length) {
                        $removeInput.val("1");
                    }
                        const $wpjobportal_imageWrap = $wrapper.find(".wjportal-form-image-wrp");
                        $wpjobportal_imageWrap.hide();
                    // if (confirm("Remove this image?")) {
                    //     const $wpjobportal_img = $wrapper.find("img.wpjobportal-config-default-image");
                    //     const $fileInput = $wrapper.find("input[type=file]");
                    //     const $wpjobportal_imageWrap = $wrapper.find(".wjportal-form-image-wrp");
                    //     const placeholder = "' . WPJOBPORTAL_PLUGIN_URL . 'includes/images/default_logo.png";

                    //     $wpjobportal_img.attr("src", placeholder);
                    //     $wpjobportal_imageWrap.hide();


                    //     // Safely reset file input without assigning value directly
                    //     if ($fileInput.length) {
                    //         $fileInput.val(null);
                    //     }

                    //     App.handleValueChange(id, "");
                    // }
                    return;
                } else {
                value = $wpjobportal_target.val();
            }
            App.handleValueChange(id, value);
        },

        handleValueChange: function(id, newValue) {
            this.changesToSubmit[id] = newValue;
            const $hiddenInput = $(`#wjp-${id}`);
            if ($hiddenInput.length) $hiddenInput.val(newValue);

            $(`[data-field-id="${id}"]`).closest(".wjp-setting-row").find(".wjp-setting-label").addClass("is-modified");
            this.updateSaveButtonState();
            this.updateConditionalFields(id);
        },

        updateConditionalFields: function(changedFieldId) {
            const self = this;
            $(`.wjp-conditional-field-wrapper[data-condition-field="${changedFieldId}"]`).each(function() {
                self.updateConditionalVisibility($(this));
            });
        },

        handleScrollSpy: function() {
            const $scrollContainer = $("#wjp-main-panel-content");
            const $wpjobportal_groupCards = $(`.wjp-category-container[data-category-key="${App.activeCategory}"] .wjp-group-card:visible`);

            // Exit if theres no sub-navigation to update.
            if ($("#wjp-sticky-sub-nav-wrapper").is(":hidden")) {
                return;
            }

            const stickyNavHeight = $("#wjp-sticky-sub-nav-wrapper").outerHeight(true) || 0;
            const offset = stickyNavHeight + 24; // The "activation line" below the sticky nav.
            let currentSectionId = "";

            // First, try to find a section that is actively intersecting the activation line.
            // This is the most reliable way to find the current section.
            $wpjobportal_groupCards.each(function() {
                const cardTop = $(this).position().top;
                const cardBottom = cardTop + $(this).outerHeight();

                // If the line is between the cards top and bottom, this is our active section.
                if (cardTop <= offset && cardBottom > offset) {
                    currentSectionId = $(this).attr("id");
                    return false; // Exit the loop, we found the one.
                }
            });

            // If no section is intersecting the line (e.g., we are in a gap between sections),
            // fall back to the previous logic: find the last section that has scrolled past the line.
            if (!currentSectionId) {
                $wpjobportal_groupCards.each(function() {
                    if ($(this).position().top <= offset) {
                        currentSectionId = $(this).attr("id");
                    }
                });
            }

            // If still no section is found (we are at the very top), default to the first one.
            if (!currentSectionId && $wpjobportal_groupCards.length > 0) {
                currentSectionId = $wpjobportal_groupCards.first().attr("id");
            }

            // Now, update the active class on the correct pill.
            if (currentSectionId) {
                const $wpjobportal_newActiveLink = $(`#wjp-sub-nav a.wjp-sub-nav-link[href="#${currentSectionId}"]`);
                if (!$wpjobportal_newActiveLink.hasClass("active")) {
                    $("#wjp-sub-nav a.wjp-sub-nav-link").removeClass("active");
                    $wpjobportal_newActiveLink.addClass("active");
                }
            }
        },

        handleSave: function() {
            $("#wpjobportal-form").submit();
        },

        handleDiscard: function() { location.reload(); },

        addEventListeners: function() {
            $("#wjp-settings-nav").on("click", "a", this.handleSidebarNav);
            $("#wjp-sticky-sub-nav-wrapper").on("click", "a.wjp-sub-nav-link", this.handleSubNavScroll);
            $("#wjp-search-box").on("input", this.handleSearch);
            $("#wjp-save-changes").on("click", this.handleSave);
            $("#wjp-discard-changes").on("click", this.handleDiscard);
            $("#wjp-mobile-menu-toggle").on("click", () => $("#wjp-sidebar").toggleClass("is-open"));
            const $wpjobportal_mainPanel = $("#wjp-main-panel");
            $wpjobportal_mainPanel.on("change", "input, select, textarea", this.handleFieldChange);
            $wpjobportal_mainPanel.on("click", ".wjp-btn-segment, [data-action=\'upload\'], .wjp-btn-remove", this.handleFieldChange);
        },
    };

    App.init();

    jQuery("#wjportal-form-delete-image").click(function(){
        jQuery(".wjportal-form-image-wrp").slideUp("slow");
        jQuery("#remove_default_image").val(1);
    });


})(jQuery);
';
wp_add_inline_script('wjp-admin-config-js', $wpjp_config_js);
?>
