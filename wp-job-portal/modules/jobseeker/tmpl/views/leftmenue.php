<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * Jobseeker Control Panel – Grouped by Section
 * Structure and logic remain identical to original code.
 */

$wpjobportal_layout = array('newestjobs','jobsearch','myappliedjobs','myresumes','formresume','listjobshortlist','mycoverletter','listallcompanies','jobcat','listjobbytype','jobsbycities','jsmessages','invoice','empresume_rss','jsregister','jobsloginlogout','jobalertsetting', 'jscredits', 'jscreditlog', 'jspurchasehistory', 'jsratelist');

$wpjobportal_manage_jobs       = array('newestjobs', 'jobsearch', 'myappliedjobs','jobalertsetting');
$wpjobportal_manage_documents  = array('myresumes', 'formresume', 'listjobshortlist', 'mycoverletter');
$wpjobportal_discover          = array('listallcompanies', 'jobcat', 'listjobbytype', 'jobsbycities');
$wpjobportal_account_billing   = array('jsmessages', 'jscredits', 'jscreditlog', 'jspurchasehistory', 'jsratelist');
$wpjobportal_system            = array('empresume_rss', 'jsregister', 'jobsloginlogout');

/**
 * Helper function to check if a section should print
 */
function wpjobportal_section_enabled($wpjobportal_items, $wpjobportal_layout) {
    foreach ($wpjobportal_items as $wpjobportal_item) {
        if (wpjobportal_jobseekercheckLinks($wpjobportal_item)) {
            return true;
        }
    }
    return false;
}

$wpjobportal_sections = [
    'manage_documents' => $wpjobportal_manage_documents,
    'manage_jobs'      => $wpjobportal_manage_jobs,
    'discover'         => $wpjobportal_discover,
    'account_billing'  => $wpjobportal_account_billing,
    'system'           => $wpjobportal_system,
];

foreach ($wpjobportal_sections as $wpjobportal_section_key => $wpjobportal_section_items) {
    if (!wpjobportal_section_enabled($wpjobportal_section_items, $wpjobportal_layout)) {
        continue;
    }
    switch ($wpjobportal_section_key) {
        case 'manage_jobs':
            echo '<h3 class="wjportal-section-title">'.esc_html(__('Manage Jobs', 'wp-job-portal')).'</h3>';
        break;
        case 'manage_documents':
            echo '<h3 class="wjportal-section-title">'.esc_html(__('Manage Documents', 'wp-job-portal')).'</h3>';
        break;
        case 'discover':
            echo '<h3 class="wjportal-section-title">'.esc_html(__('Discover', 'wp-job-portal')).'</h3>';
        break;
        case 'account_billing':
            echo '<h3 class="wjportal-section-title">'.esc_html(__('Account & Billing', 'wp-job-portal')).'</h3>';
        break;
        case 'system':
            echo '<h3 class="wjportal-section-title">'.esc_html(__('System', 'wp-job-portal')).'</h3>';
        break;
    }
    $wpjobportal_credits_links_printed = 0;
    foreach ($wpjobportal_layout as $wpjobportal_key => $wpjobportal_value) {
        if (!in_array($wpjobportal_value, $wpjobportal_section_items)) continue;

        switch ($wpjobportal_value) {
            case 'jsregister':
                if (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
                    $wpjobportal_print = wpjobportal_jobseekercheckLinks($wpjobportal_value);
                    if ($wpjobportal_print) {
                        $wpjobportal_defaultUrl = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'user','wpjobportallt'=>'regjobseeker'));
                        $wpjobportal_lrlink = WPJOBPORTALincluder::getJSModel('configuration')->getLoginRegisterRedirectLink($wpjobportal_defaultUrl,'register');
                        echo '<div class="wjportal-cp-list">
                                <a class="wjportal-list-anchor" href='.esc_url($wpjobportal_lrlink).' title="'. esc_attr(__('Register', 'wp-job-portal')) .'">
                                    <img src='. esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/control_panel/jobseeker/registers.png alt="'. esc_html(__('register', 'wp-job-portal')) .'">
                                    <span class="wjportal-cp-link-text">'. esc_html(__('Register', 'wp-job-portal')) .'</span>
                                </a>
                        </div>';
                    }
                }
            break;

            case 'myappliedjobs':
                $wpjobportal_print = wpjobportal_jobseekercheckLinks($wpjobportal_value);
                if ($wpjobportal_print) {
                    echo' <div class="wjportal-cp-list">
                            <a class="wjportal-list-anchor" href='. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobapply','wpjobportallt'=>esc_attr($wpjobportal_value)))).' title="'. esc_attr(__('my applied jobs', 'wp-job-portal')).'">
                                <img src='. esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/control_panel/jobseeker/applied-jobs.png alt="'. esc_html(__('my applied jobs', 'wp-job-portal')).'">
                                <span class="wjportal-cp-link-text">'. esc_html(__('My Applied Jobs', 'wp-job-portal')).'</span>
                            </a>
                        </div>';
                }
            break;

            case 'listjobshortlist':
                if (in_array('shortlist', wpjobportal::$_active_addons)) {
                    do_action('wpjobportal_addons_jobseeker_dashboard_bottom_btn_shortlist', $wpjobportal_value);
                }
            break;

            case 'myresumes':
                $wpjobportal_print = wpjobportal_jobseekercheckLinks($wpjobportal_value);
                if ($wpjobportal_print && in_array('multiresume', wpjobportal::$_active_addons)) {
                    do_action('wpjobportal_addons_multiresume_myresume', $wpjobportal_print);
                } else {
                    if ($wpjobportal_print) {
                        echo '<div class="wjportal-cp-list">
                                <a class="wjportal-list-anchor" href='. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume','wpjobportallt'=>'myresumes'))).'>
                                    <img src='. esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/control_panel/jobseeker/resume.png>
                                    <span class="wjportal-cp-link-text">'. esc_html(__('My Resumes', 'wp-job-portal')).'</span>
                                </a>
                            </div>';
                    }
                }
            break;

            case 'newestjobs':
                $wpjobportal_print = wpjobportal_jobseekercheckLinks('listnewestjobs');
                if ($wpjobportal_print) {
                    echo '<div class="wjportal-cp-list">
                            <a class="wjportal-list-anchor" href='. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job','wpjobportallt'=>'newestjobs'))).' title="'. esc_attr(__('newest jobs', 'wp-job-portal')).'">
                                <img src='. esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/control_panel/jobseeker/add-job.png alt="'. esc_html(__('newest jobs', 'wp-job-portal')).'">
                                <span class="wjportal-cp-link-text">'. esc_html(__('Newest Jobs', 'wp-job-portal')).'</span>
                            </a>
                        </div>';
                }
            break;

            case 'jobsearch':
                $wpjobportal_print = wpjobportal_jobseekercheckLinks($wpjobportal_value);
                if ($wpjobportal_print) {
                    echo '<div class="wjportal-cp-list">
                            <a class="wjportal-list-anchor" href='.esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobsearch','wpjobportallt'=>esc_attr($wpjobportal_value)))).' title="'. esc_attr(__('search job', 'wp-job-portal')).'">
                                <img src='. esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/control_panel/jobseeker/search.png>
                                <span class="wjportal-cp-link-text">'. esc_html(__('Search Job', 'wp-job-portal')).'</span>
                            </a>
                    </div>';
                }
            break;

            case 'jsmessages':
                do_action('wpjobportal_addons_jobseeker_dashboard_bottom_btn_msg');
            break;
            case 'jobalertsetting':
                $wpjobportal_print = wpjobportal_jobseekercheckLinks('jobalertsetting');
                do_action('wpjobportal_addons_jobseeker_dashboard_bottom_btn_jobalert', $wpjobportal_print);
            break;

            case 'mycoverletter':
                do_action('wpjobportal_addons_jobseeker_dashboard_side_menue_coverletter');
            break;

            case 'empresume_rss':
                do_action('wpjobportal_addons_jobseeker_dashboard_bottom_btn_rss');
            break;

            case 'jscredits':
            case 'jscreditlog':
            case 'jspurchasehistory':
            case 'jsratelist':
                if($wpjobportal_credits_links_printed == 0){
                    $wpjobportal_credits_links_printed = 1;
                    do_action('wpjobportal_addons_credit_cp_leftmenue_jobseeker');
                }
            break;

            case 'jobcat':
                $wpjobportal_print = wpjobportal_jobseekercheckLinks($wpjobportal_value);
                if ($wpjobportal_print) {
                    echo '<div class="wjportal-cp-list">
                            <a class="wjportal-list-anchor" href='. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job','wpjobportallt'=>'jobsbycategories'))).' title="'. esc_attr(__('jobs by categories', 'wp-job-portal')).'">
                                <img class="wjportal-img" src='. esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/control_panel/jobseeker/job-category.png alt="'. esc_html(__('jobs by categories', 'wp-job-portal')).'">
                                <span class="wjportal-cp-link-text">'. esc_html(__('Jobs By Categories', 'wp-job-portal')).'</span>
                            </a>
                    </div>';
                }
            break;

            case 'listjobbytype':
                $wpjobportal_print = wpjobportal_jobseekercheckLinks($wpjobportal_value);
                if ($wpjobportal_print) {
                    echo '<div class="wjportal-cp-list">
                            <a class="wjportal-list-anchor" href='. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job','wpjobportallt'=>'jobsbytypes'))).' title="'. esc_attr(__('jobs by types', 'wp-job-portal')).'">
                            <img class="wjportal-img" src='. esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/control_panel/jobseeker/job-type.png alt="'. esc_html(__('jobs by types', 'wp-job-portal')).'">
                            <span class="wjportal-cp-link-text">'. esc_html(__('Jobs By Types', 'wp-job-portal')).'</span></a>
                        </div>';
                }
            break;

            case 'listallcompanies':
                if (in_array('multicompany', wpjobportal::$_active_addons)) {
                    $wpjobportal_print = wpjobportal_jobseekercheckLinks($wpjobportal_value);
                    if ($wpjobportal_print) {
                        echo '<div class="wjportal-cp-list">
                                <a class="wjportal-list-anchor" href='. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'multicompany','wpjobportallt'=>'companies'))).' title="'. esc_attr(__('Companies', 'wp-job-portal')).'">
                                <img class="wjportal-img" src='. esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/control_panel/jobseeker/companies.png alt="'. esc_html(__('Companies', 'wp-job-portal')).'">
                                <span class="wjportal-cp-link-text">'. esc_html(__('Companies', 'wp-job-portal')).'</span></a>
                            </div>';
                    }
                }
            break;

            case 'jobsbycities':
                $wpjobportal_print = wpjobportal_jobseekercheckLinks($wpjobportal_value);
                if ($wpjobportal_print) {
                    echo '<div class="wjportal-cp-list">
                            <a class="wjportal-list-anchor" href='. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job','wpjobportallt'=>'jobsbycities'))).' title="'. esc_attr(__('jobs by cities', 'wp-job-portal')).'">
                            <img class="wjportal-img" src='. esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/control_panel/jobseeker/job-city.png alt="'. esc_html(__('jobs by cities', 'wp-job-portal')).'">
                            <span class="wjportal-cp-link-text">'. esc_html(__('Jobs By Cities', 'wp-job-portal')).'</span></a>
                        </div>';
                }
            break;

            case 'formresume':
                $wpjobportal_count = '';
                if (!empty(wpjobportal::$_data[0]['resume']['info']) && wpjobportal::$_data[0]['resume']['info'] != NULL) {
                    $wpjobportal_resumeid = wpjobportal::$_data[0]['resume']['info'][0]->resumeid;
                    $wpjobportal_count = wpjobportal::$_data[0]['resume']['info'][0]->resumeno;
                }
                if (in_array('multiresume', wpjobportal::$_active_addons)) {
                    do_action('wpjobportal_addons_multiresume_addresume', $wpjobportal_value);
                } else {
                    $wpjobportal_print = wpjobportal_jobseekercheckLinks($wpjobportal_value);
                    if ($wpjobportal_print) {
                        if ($wpjobportal_count > 0) {
                            echo '<div class="wjportal-cp-list">
                                    <a class="wjportal-list-anchor" href='. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume','wpjobportallt'=>'addresume','wpjobportalid'=>$wpjobportal_resumeid))).' title="'. esc_attr(__('edit resume', 'wp-job-portal')).'">
                                        <img class="wjportal-img" src='.esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/control_panel/jobseeker/add-resume.png alt="'. esc_html(__('edit resume', 'wp-job-portal')).'">
                                        <span class="wjportal-cp-link-text">'. esc_html(__('Edit Resume', 'wp-job-portal')).'</span>
                                    </a>
                            </div>';
                        } else {
                            echo '<div class="wjportal-cp-list">
                                    <a class="wjportal-list-anchor" href='. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume','wpjobportallt'=>'addresume'))).' title="'. esc_attr(__('add resume', 'wp-job-portal')).'">
                                        <img class="wjportal-img" src='. esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/control_panel/jobseeker/add-resume.png alt="'. esc_html(__('add resume', 'wp-job-portal')).'">
                                        <span class="wjportal-cp-link-text">'. esc_html(__('Add Resume', 'wp-job-portal')).'</span>
                                    </a>
                            </div>';
                        }
                    }
                }
            break;

            case 'jobsloginlogout':
                if (wpjobportal_jobseekercheckLinks($wpjobportal_value)) {
                    if (WPJOBPORTALincluder::getObjectClass('user')->isguest() && (!isset($_COOKIE['wpjobportal-socialmedia']) && empty($_COOKIE['wpjobportal-socialmedia']))) {
                        $wpjobportal_thiscpurl = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobseeker','wpjobportallt'=>'controlpanel','wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        $wpjobportal_thiscpurl = wpjobportalphplib::wpJP_safe_encoding($wpjobportal_thiscpurl);
                        $wpjobportal_defaultUrl = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'wpjobportal','wpjobportallt'=>'login','wpjobportalredirecturl'=>$wpjobportal_thiscpurl,'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        $wpjobportal_lrlink = WPJOBPORTALincluder::getJSModel('configuration')->getLoginRegisterRedirectLink($wpjobportal_defaultUrl,'login');
                        echo '<div class="wjportal-cp-list">
                                <a class="wjportal-list-anchor" href='.esc_url($wpjobportal_lrlink).' title="'.esc_attr(__('login', 'wp-job-portal')).'">
                                    <img class="wjportal-img" src='. esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/control_panel/jobseeker/login.png alt="'.esc_attr(__('login', 'wp-job-portal')).'">
                                    <span class="wjportal-cp-link-text">'.esc_html(__('Login', 'wp-job-portal')).'</span>
                                </a>
                            </div>';
                    } else {
                        $wpjobportal_logout_url = wp_logout_url(get_permalink());
                        if (isset($_COOKIE['wpjobportal-socialmedia']) && !empty($_COOKIE['wpjobportal-socialmedia'])) {
                            $wpjobportal_logout_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'sociallogin','task'=>'socialogout','action'=>'wpjobportaltask','wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        }
                        echo '<div class="wjportal-cp-list">
                                <a class="wjportal-list-anchor" href='. esc_url($wpjobportal_logout_url) .' title="'. esc_attr(__('logout', 'wp-job-portal')).'">
                                    <img class="wjportal-img" src='.esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/control_panel/jobseeker/logout.png alt="'. esc_html(__('logout', 'wp-job-portal')).'">
                                    <span class="wjportal-cp-link-text">'. esc_html(__('Logout', 'wp-job-portal')).'</span>
                                </a>
                            </div>';
                    }
                }
            break;
        }
    }
}
?>
