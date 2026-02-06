<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * Employer Control Panel – Grouped by Section
 * Structure and logic aligned with Jobseeker Control Panel.
 */

$wpjobportal_layout = array(
    'formjob','myjobs','resumesearch','resumebycategory','my_resumesearches',
    'formcompany','mycompanies','formdepartment','mydepartment','empmessages',
    'myfolders','newfolders','invoice','empresume_rss','empregister','emploginlogout'
);

$wpjobportal_manage_jobs      = array('formjob', 'myjobs');
$wpjobportal_resume_access    = array('resumesearch', 'resumebycategory', 'my_resumesearches');
$wpjobportal_manage_company   = array('formcompany', 'mycompanies', 'formdepartment', 'mydepartment');
$wpjobportal_communication    = array('empmessages', 'myfolders', 'newfolders');
$wpjobportal_account_billing  = array('invoice', 'empresume_rss');
$wpjobportal_system           = array('empregister', 'emploginlogout');

/**
 * Helper function to check if a section should print
 */
function wpjobportal_section_enabled($wpjobportal_items, $wpjobportal_layout) {
    foreach ($wpjobportal_items as $wpjobportal_item) {
        if($wpjobportal_item == 'invoice'){ // handle the case of credit system section
            $wpjobportal_temp_array = [];
            $wpjobportal_temp_array[] = 'empcredits';
            $wpjobportal_temp_array[] = 'empcreditlog';
            $wpjobportal_temp_array[] = 'empratelist';
            $wpjobportal_temp_array[] = 'emppurchasehistory';
            foreach ($wpjobportal_temp_array as $wpjobportal_key => $wpjobportal_value) {
                $wpjobportal_val = wpjobportal_employercheckLinks($wpjobportal_value);
                if ($wpjobportal_val) {
                    return true;
                }
            }
        }elseif (in_array($wpjobportal_item, $wpjobportal_layout) && wpjobportal_employercheckLinks($wpjobportal_item)) {
            return true;
        }
    }
    return false;
}

$wpjobportal_sections = [
    'manage_jobs'     => $wpjobportal_manage_jobs,
    'resume_access'   => $wpjobportal_resume_access,
    'manage_company'  => $wpjobportal_manage_company,
    'communication'   => $wpjobportal_communication,
    'account_billing' => $wpjobportal_account_billing,
    'system'          => $wpjobportal_system,
];

foreach ($wpjobportal_sections as $wpjobportal_section_key => $wpjobportal_section_items) {
    if (!wpjobportal_section_enabled($wpjobportal_section_items, $wpjobportal_layout)) {
        continue;
    }

    switch ($wpjobportal_section_key) {
        case 'manage_jobs':
            echo '<h3 class="wjportal-section-title">'.esc_html(__('Manage Jobs', 'wp-job-portal')).'</h3>';
        break;
        case 'resume_access':
            echo '<h3 class="wjportal-section-title">'.esc_html(__('Resumes', 'wp-job-portal')).'</h3>';
        break;
        case 'manage_company':
            echo '<h3 class="wjportal-section-title">'.esc_html(__('Company Management', 'wp-job-portal')).'</h3>';
        break;
        case 'communication':
            echo '<h3 class="wjportal-section-title">'.esc_html(__('Communication', 'wp-job-portal')).'</h3>';
        break;
        case 'account_billing':
            echo '<h3 class="wjportal-section-title">'.esc_html(__('Account & Billing', 'wp-job-portal')).'</h3>';
        break;
        case 'system':
            echo '<h3 class="wjportal-section-title">'.esc_html(__('System', 'wp-job-portal')).'</h3>';
        break;
    }

    foreach ($wpjobportal_layout as $wpjobportal_key => $wpjobportal_value) {
        if (!in_array($wpjobportal_value, $wpjobportal_section_items)) continue;

        switch ($wpjobportal_value) {

            case 'formjob':
                $wpjobportal_print = wpjobportal_employercheckLinks('formjob');
                if ($wpjobportal_print) {
                    echo '<div class="wjportal-cp-list">
                            <a class="wjportal-list-anchor" href='. esc_url(wpjobportal::wpjobportal_makeUrl(['wpjobportalme'=>'job','wpjobportallt'=>'addjob'])) .' title="'. esc_attr(__('Add Job','wp-job-portal')).'">
                                <img src='. esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/control_panel/employer/add-job.png alt="'. esc_html(__('Add Job','wp-job-portal')).'">
                                <span class="wjportal-cp-link-text">'. esc_html(__('Add Job','wp-job-portal')).'</span>
                            </a>
                        </div>';
                }
            break;

            case 'myjobs':
                $wpjobportal_print = wpjobportal_employercheckLinks('myjobs');
                if ($wpjobportal_print) {
                    echo '<div class="wjportal-cp-list">
                            <a class="wjportal-list-anchor" href='. esc_url(wpjobportal::wpjobportal_makeUrl(['wpjobportalme'=>'job','wpjobportallt'=>'myjobs'])) .' title="'. esc_attr(__('My Jobs','wp-job-portal')).'">
                                <img src='. esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/control_panel/employer/my-job.png alt="'. esc_html(__('My Jobs','wp-job-portal')).'">
                                <span class="wjportal-cp-link-text">'. esc_html(__('My Jobs','wp-job-portal')).'</span>
                            </a>
                        </div>';
                }
            break;

            case 'resumesearch':
                if (in_array('resumesearch', wpjobportal::$_active_addons)) {
                    $wpjobportal_print = wpjobportal_employercheckLinks('resumesearch');
                    if ($wpjobportal_print) {
                        echo '<div class="wjportal-cp-list">';
                        do_action('wpjobportal_addons_mystuff_dashboard_employer_upper', $wpjobportal_print);
                        echo '</div>';
                    }
                }
            break;

            case 'resumebycategory':
                $wpjobportal_print = wpjobportal_employercheckLinks('resumebycategory');
                if ($wpjobportal_print) {
                    echo '<div class="wjportal-cp-list">
                            <a class="wjportal-list-anchor" href='. esc_url(wpjobportal::wpjobportal_makeUrl(['wpjobportalme'=>'resume','wpjobportallt'=>'resumebycategory'])) .' title="'. esc_attr(__('Resumes By Categories','wp-job-portal')).'">
                                <img src='. esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/control_panel/employer/resume-categories.png alt="'. esc_html(__('Resumes By Categories','wp-job-portal')).'">
                                <span class="wjportal-cp-link-text">'. esc_html(__('Resumes By Categories','wp-job-portal')).'</span>
                            </a>
                        </div>';
                }
            break;

            case 'my_resumesearches':
                do_action('wpjobportal_addons_mystuff_dashboard_employer_search');
            break;

            case 'formcompany':
                if (in_array('multicompany', wpjobportal::$_active_addons)) {
                    $wpjobportal_print = wpjobportal_employercheckLinks($wpjobportal_value);
                    do_action('wpjobportal_addons_mystuff_employer_dashboard_addcomp', $wpjobportal_print);
                } else {
                    $wpjobportal_print = wpjobportal_employercheckLinks($wpjobportal_value);
                    if ($wpjobportal_print) {
                        $wpjobportal_company = wpjobportal::$_data['employer_info']['companies'] ?? '';
                        if (!empty($wpjobportal_company) && $wpjobportal_company[0]->record > 0) {
                            echo '<div class="wjportal-cp-list">
                                    <a class="wjportal-list-anchor" href='. esc_url(wpjobportal::wpjobportal_makeUrl(['wpjobportalme'=>'company','wpjobportallt'=>'addcompany','wpjobportalid'=>$wpjobportal_company[0]->id])) .' title="'. esc_attr(__('Edit Company','wp-job-portal')).'">
                                        <img src='. esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/control_panel/employer/add-company.png alt="'. esc_html(__('Edit Company','wp-job-portal')).'">
                                        <span class="wjportal-cp-link-text">'. esc_html(__('Edit Company','wp-job-portal')).'</span>
                                    </a>
                                </div>';
                        } else {
                            echo '<div class="wjportal-cp-list">
                                    <a class="wjportal-list-anchor" href='. esc_url(wpjobportal::wpjobportal_makeUrl(['wpjobportalme'=>'company','wpjobportallt'=>'addcompany'])) .' title="'. esc_attr(__('Add Company','wp-job-portal')).'">
                                        <img src='. esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/control_panel/employer/add-company.png alt="'. esc_html(__('Add Company','wp-job-portal')).'">
                                        <span class="wjportal-cp-link-text">'. esc_html(__('Add Company','wp-job-portal')).'</span>
                                    </a>
                                </div>';
                        }
                    }
                }
            break;

            case 'mycompanies':
                if (in_array('multicompany', wpjobportal::$_active_addons)) {
                    do_action('wpjobportal_addons_mystuff_dashboard_employer_upper_mycomp', 'mycompanies');
                } else {
                    $wpjobportal_print = wpjobportal_employercheckLinks($wpjobportal_value);
                    if ($wpjobportal_print) {
                        echo '<div class="wjportal-cp-list">
                                <a class="wjportal-list-anchor" href='. esc_url(wpjobportal::wpjobportal_makeUrl(['wpjobportalme'=>'company','wpjobportallt'=>$wpjobportal_value])) .' title="'. esc_attr(__('My Companies','wp-job-portal')).'">
                                    <img src='. esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/control_panel/employer/companies.png alt="'. esc_html(__('My Companies','wp-job-portal')).'">
                                    <span class="wjportal-cp-link-text">'. esc_html(__('My Companies','wp-job-portal')).'</span>
                                </a>
                            </div>';
                    }
                }
            break;

            case 'formdepartment':
                do_action('wpjobportal_addons_mystuff_employer_dashboard_side_menue_dept');
            break;

            case 'empmessages':
                $wpjobportal_print = wpjobportal_employercheckLinks($wpjobportal_value);
                do_action('wpjobportal_addons_mystuff_employer_dashboard_msg', $wpjobportal_print);
            break;

            case 'myfolders':
            //case 'newfolders':
                do_action('wpjobportal_addons_mystuff_employer_dashboard');
            break;

            case 'invoice':
                do_action('wpjobportal_addons_credit_cp_leftmenue_employeer');
            break;

            case 'empresume_rss':
                $wpjobportal_print = wpjobportal_employercheckLinks('empresume_rss');
                if (in_array('rssfeedback', wpjobportal::$_active_addons)) {
                    do_action('wpjobportal_addons_mystuff_employer_dashboard_side_menue', $wpjobportal_print);
                }
            break;

            case 'empregister':
                if (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
                    $wpjobportal_print = wpjobportal_employercheckLinks('empregister');
                    if ($wpjobportal_print) {
                        $wpjobportal_defaultUrl = wpjobportal::wpjobportal_makeUrl(['wpjobportalme'=>'user','wpjobportallt'=>'regemployer']);
                        $wpjobportal_lrlink = WPJOBPORTALincluder::getJSModel('configuration')->getLoginRegisterRedirectLink($wpjobportal_defaultUrl,'register');
                        echo '<div class="wjportal-cp-list">
                                <a class="wjportal-list-anchor" href='. esc_url($wpjobportal_lrlink) .' title="'. esc_attr(__('Register','wp-job-portal')).'">
                                    <img src='. esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/control_panel/jobseeker/registers.png alt="'. esc_html(__('Register','wp-job-portal')).'">
                                    <span class="wjportal-cp-link-text">'. esc_html(__('Register','wp-job-portal')).'</span>
                                </a>
                            </div>';
                    }
                }
            break;

            case 'emploginlogout':
                if (wpjobportal_employercheckLinks('emploginlogout')) {
                    if (WPJOBPORTALincluder::getObjectClass('user')->isguest() && (!isset($_COOKIE['wpjobportal-socialmedia']) || empty($_COOKIE['wpjobportal-socialmedia']))) {
                        $wpjobportal_thiscpurl = wpjobportal::wpjobportal_makeUrl(['wpjobportalme'=>'employer','wpjobportallt'=>'controlpanel','wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()]);
                        $wpjobportal_thiscpurl = wpjobportalphplib::wpJP_safe_encoding($wpjobportal_thiscpurl);
                        $wpjobportal_defaultUrl = wpjobportal::wpjobportal_makeUrl(['wpjobportalme'=>'wpjobportal','wpjobportallt'=>'login','wpjobportalredirecturl'=>$wpjobportal_thiscpurl]);
                        $wpjobportal_lrlink = WPJOBPORTALincluder::getJSModel('configuration')->getLoginRegisterRedirectLink($wpjobportal_defaultUrl,'login');
                        echo '<div class="wjportal-cp-list">
                                <a class="wjportal-list-anchor" href='. esc_url($wpjobportal_lrlink) .' title="'. esc_attr(__('Login','wp-job-portal')).'">
                                    <img src='. esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/control_panel/jobseeker/login.png alt="'. esc_html(__('Login','wp-job-portal')).'">
                                    <span class="wjportal-cp-link-text">'. esc_html(__('Login','wp-job-portal')).'</span>
                                </a>
                            </div>';
                    } else {
                        $wpjobportal_logout_url = wp_logout_url(get_permalink());
                        if (isset($_COOKIE['wpjobportal-socialmedia']) && !empty($_COOKIE['wpjobportal-socialmedia'])) {
                            $wpjobportal_logout_url = wpjobportal::wpjobportal_makeUrl(['wpjobportalme'=>'sociallogin','task'=>'socialogout','action'=>'wpjobportaltask','wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()]);
                        }
                        echo '<div class="wjportal-cp-list">
                                <a class="wjportal-list-anchor" href='. esc_url($wpjobportal_logout_url) .' title="'. esc_attr(__('Logout','wp-job-portal')).'">
                                    <img src='. esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/control_panel/employer/logout.png alt="'. esc_html(__('Logout','wp-job-portal')).'">
                                    <span class="wjportal-cp-link-text">'. esc_html(__('Logout','wp-job-portal')).'</span>
                                </a>
                            </div>';
                    }
                }
            break;
        }
    }
}
?>
