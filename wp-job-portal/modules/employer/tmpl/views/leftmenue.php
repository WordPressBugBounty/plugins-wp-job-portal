<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * Employer Control Panel – Grouped by Section
 * Structure and logic aligned with Job seeker Control Panel.
 */

$wpjobportal_layout = array(
    'formjob','myjobs','resumesearch','resumebycategory','my_resumesearches',
    'formcompany','mycompanies','formdepartment','mydepartment','empmessages',
    'myfolders','newfolders','invoice','empresume_rss','empregister','emploginlogout'
);
$wpjobportal_layout = (array) apply_filters(
    'wpjobportal_employer_dashboard_link_layout',
    $wpjobportal_layout
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

/*
 * Where an add-on's own section belongs.
 *
 * The extension links - the Recruiting Center, the Marketplace - used to be
 * printed after the whole menu, which put them under System, below Register,
 * Login and Logout. Signing out is not a feature of the portal and does not
 * belong above one: the account links are the end of a menu everywhere else on
 * the web, and they read as the end here too.
 *
 * So the add-ons get their turn immediately before System. If System is turned
 * off entirely the loop never reaches it, so the flag below makes sure they are
 * still printed rather than lost with it.
 */
$wpjobportal_extensions_printed = false;

foreach ($wpjobportal_sections as $wpjobportal_section_key => $wpjobportal_section_items) {
    if (!wpjobportal_section_enabled($wpjobportal_section_items, $wpjobportal_layout)) {
        continue;
    }

    if ('system' === $wpjobportal_section_key && !$wpjobportal_extensions_printed) {
        do_action('wpjobportal_employer_dashboard_extension_links', $wpjobportal_layout);
        $wpjobportal_extensions_printed = true;
    }

    /*
     * A heading is printed only once its section has printed something.
     *
     * Whether a section has anything in it was decided up front, by
     * wpjobportal_section_enabled(); but some of the links are not printed
     * here at all - they are a do_action, and the add-on listening decides
     * for itself whether this person may see them. A recruiter may not see
     * credits or invoices, so Account & Billing announced itself and then
     * printed nothing: a heading with empty space under it, in a menu where
     * every other heading has links.
     *
     * So the links are gathered first and the heading follows them.
     */
    ob_start();

    foreach ($wpjobportal_layout as $wpjobportal_key => $wpjobportal_value) {
        if (!in_array($wpjobportal_value, $wpjobportal_section_items)) continue;

        switch ($wpjobportal_value) {

            case 'formjob':
                $wpjobportal_print = wpjobportal_employercheckLinks('formjob');
                if ($wpjobportal_print) {
                    echo '<div class="wjportal-cp-list">
                            <a class="wjportal-list-anchor" href='. esc_url(wpjobportal::wpjobportal_makeUrl(['wpjobportalme'=>'job','wpjobportallt'=>'addjob'])) .' title="'. esc_attr(__('Add Job','wp-job-portal')).'">
                                '. wp_kses(wpjobportal_cp_menu_icon('plus-square'), WPJOBPORTAL_ALLOWED_TAGS) .'
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
                                '. wp_kses(wpjobportal_cp_menu_icon('briefcase'), WPJOBPORTAL_ALLOWED_TAGS) .'
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
                                '. wp_kses(wpjobportal_cp_menu_icon('th-list'), WPJOBPORTAL_ALLOWED_TAGS) .'
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
                                        '. wp_kses(wpjobportal_cp_menu_icon('pencil-square'), WPJOBPORTAL_ALLOWED_TAGS) .'
                                        <span class="wjportal-cp-link-text">'. esc_html(__('Edit Company','wp-job-portal')).'</span>
                                    </a>
                                </div>';
                        } else {
                            echo '<div class="wjportal-cp-list">
                                    <a class="wjportal-list-anchor" href='. esc_url(wpjobportal::wpjobportal_makeUrl(['wpjobportalme'=>'company','wpjobportallt'=>'addcompany'])) .' title="'. esc_attr(__('Add Company','wp-job-portal')).'">
                                        '. wp_kses(wpjobportal_cp_menu_icon('plus-square'), WPJOBPORTAL_ALLOWED_TAGS) .'
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
                                    '. wp_kses(wpjobportal_cp_menu_icon('building'), WPJOBPORTAL_ALLOWED_TAGS) .'
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
                                    '. wp_kses(wpjobportal_cp_menu_icon('user-plus'), WPJOBPORTAL_ALLOWED_TAGS) .'
                                    <span class="wjportal-cp-link-text">'. esc_html(__('Register','wp-job-portal')).'</span>
                                </a>
                            </div>';
                    }
                }
            break;

            case 'emploginlogout':
                if (wpjobportal_employercheckLinks('emploginlogout')) {
                    if (WPJOBPORTALincluder::getObjectClass('user')->isguest() && (!WPJOBPORTALrequest::hasCookie('wpjobportal-socialmedia') || empty($_COOKIE['wpjobportal-socialmedia']))) {
                        $wpjobportal_thiscpurl = wpjobportal::wpjobportal_makeUrl(['wpjobportalme'=>'employer','wpjobportallt'=>'controlpanel','wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()]);
                        $wpjobportal_thiscpurl = wpjobportalphplib::wpJP_safe_encoding($wpjobportal_thiscpurl);
                        $wpjobportal_defaultUrl = wpjobportal::wpjobportal_makeUrl(['wpjobportalme'=>'wpjobportal','wpjobportallt'=>'login','wpjobportalredirecturl'=>$wpjobportal_thiscpurl]);
                        $wpjobportal_lrlink = WPJOBPORTALincluder::getJSModel('configuration')->getLoginRegisterRedirectLink($wpjobportal_defaultUrl,'login');
                        echo '<div class="wjportal-cp-list">
                                <a class="wjportal-list-anchor" href='. esc_url($wpjobportal_lrlink) .' title="'. esc_attr(__('Login','wp-job-portal')).'">
                                    '. wp_kses(wpjobportal_cp_menu_icon('sign-in'), WPJOBPORTAL_ALLOWED_TAGS) .'
                                    <span class="wjportal-cp-link-text">'. esc_html(__('Login','wp-job-portal')).'</span>
                                </a>
                            </div>';
                    } else {
                        $wpjobportal_logout_url = wp_logout_url(get_permalink());
                        if (WPJOBPORTALrequest::hasCookie('wpjobportal-socialmedia') && !empty(WPJOBPORTALrequest::cookie('wpjobportal-socialmedia'))) {
                            $wpjobportal_logout_url = wpjobportal::wpjobportal_makeUrl(['wpjobportalme'=>'sociallogin','task'=>'socialogout','action'=>'wpjobportaltask','wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()]);
                        }
                        echo '<div class="wjportal-cp-list">
                                <a class="wjportal-list-anchor" href='. esc_url($wpjobportal_logout_url) .' title="'. esc_attr(__('Logout','wp-job-portal')).'">
                                    '. wp_kses(wpjobportal_cp_menu_icon('sign-out'), WPJOBPORTAL_ALLOWED_TAGS) .'
                                    <span class="wjportal-cp-link-text">'. esc_html(__('Logout','wp-job-portal')).'</span>
                                </a>
                            </div>';
                    }
                }
            break;
        }
    }
    $wpjobportal_section_links = ob_get_clean();
    if (trim($wpjobportal_section_links) === '') {
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

    echo $wpjobportal_section_links; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- buffered output of the escaped echoes above.
}

/**
 * Allow premium extensions to add a native employer dashboard section.
 * Extensions are responsible for capability checks and escaped output.
 *
 * Normally this has already run, just above System. It runs here only when
 * there was no System section to run before.
 */
if (!$wpjobportal_extensions_printed) {
    do_action('wpjobportal_employer_dashboard_extension_links', $wpjobportal_layout);
    $wpjobportal_extensions_printed = true;
}
?>
