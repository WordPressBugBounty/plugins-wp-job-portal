<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * Job seeker Control Panel – Grouped by Section
 * Structure and logic remain identical to original code.
 */

$wpjobportal_layout = array('newestjobs','jobsearch','my_jobsearches','myappliedjobs','myresumes','formresume','listjobshortlist','mycoverletter','listallcompanies','jobcat','listjobbytype','jobsbycities','jsmessages','invoice','empresume_rss','jsregister','jobsloginlogout','jobalertsetting', 'jscredits', 'jscreditlog', 'jspurchasehistory', 'jsratelist');
$wpjobportal_layout = (array) apply_filters(
    /*
     * The employer panel has had this since team members started arriving
     * there; the job-seeker panel had nothing, so a recruiter - who holds no
     * portal role - was offered My Resumes and Add Resume as if the area were
     * theirs.
     */
    'wpjobportal_jobseeker_dashboard_link_layout',
    $wpjobportal_layout
);

$wpjobportal_manage_jobs       = array('newestjobs', 'jobsearch', 'my_jobsearches', 'myappliedjobs','jobalertsetting');
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
    if ('system' === $wpjobportal_section_key && empty($wpjobportal_extensions_printed)) {
        do_action('wpjobportal_jobseeker_dashboard_extension_links', $wpjobportal_layout);
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
                                    '. wp_kses(wpjobportal_cp_menu_icon('user-plus'), WPJOBPORTAL_ALLOWED_TAGS) .'
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
                            <a class="wjportal-list-anchor" href='. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobapply','wpjobportallt'=>esc_attr($wpjobportal_value)))).' title="'. esc_attr(__('My Applied Jobs', 'wp-job-portal')).'">
                                '. wp_kses(wpjobportal_cp_menu_icon('paper-plane'), WPJOBPORTAL_ALLOWED_TAGS) .'
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
                                    '. wp_kses(wpjobportal_cp_menu_icon('file-text'), WPJOBPORTAL_ALLOWED_TAGS) .'
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
                            <a class="wjportal-list-anchor" href='. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job','wpjobportallt'=>'newestjobs'))).' title="'. esc_attr(__('Newest Jobs', 'wp-job-portal')).'">
                                '. wp_kses(wpjobportal_cp_menu_icon('bolt'), WPJOBPORTAL_ALLOWED_TAGS) .'
                                <span class="wjportal-cp-link-text">'. esc_html(__('Newest Jobs', 'wp-job-portal')).'</span>
                            </a>
                        </div>';
                }
            break;

            case 'jobsearch':
                $wpjobportal_print = wpjobportal_jobseekercheckLinks($wpjobportal_value);
                if ($wpjobportal_print) {
                    echo '<div class="wjportal-cp-list">
                            <a class="wjportal-list-anchor" href='.esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobsearch','wpjobportallt'=>esc_attr($wpjobportal_value)))).' title="'. esc_attr(__('Search Job', 'wp-job-portal')).'">
                                '. wp_kses(wpjobportal_cp_menu_icon('search'), WPJOBPORTAL_ALLOWED_TAGS) .'
                                <span class="wjportal-cp-link-text">'. esc_html(__('Search Job', 'wp-job-portal')).'</span>
                            </a>
                    </div>';
                }
            break;

            /*
             * Saved searches.
             *
             * Sits next to Search Job because that is where it is created: the
             * seeker runs a search there, keeps it, and comes back to it here.
             * Visibility rides on the my_jobsearches setting, which has shipped
             * since the beginning with nothing behind it.
             */
            case 'my_jobsearches':
                $wpjobportal_print = wpjobportal_jobseekercheckLinks('my_jobsearches');
                if ($wpjobportal_print) {
                    echo '<div class="wjportal-cp-list">
                            <a class="wjportal-list-anchor" href='.esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobsearch','wpjobportallt'=>'jobsavesearch'))).' title="'. esc_attr(__('Saved Searches', 'wp-job-portal')).'">
                                '. wp_kses(wpjobportal_cp_menu_icon('bookmark'), WPJOBPORTAL_ALLOWED_TAGS) .'
                                <span class="wjportal-cp-link-text">'. esc_html(__('Saved Searches', 'wp-job-portal')).'</span>
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
                            <a class="wjportal-list-anchor" href='. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job','wpjobportallt'=>'jobsbycategories'))).' title="'. esc_attr(__('Jobs By Categories', 'wp-job-portal')).'">
                                '. wp_kses(wpjobportal_cp_menu_icon('th-list'), WPJOBPORTAL_ALLOWED_TAGS) .'
                                <span class="wjportal-cp-link-text">'. esc_html(__('Jobs By Categories', 'wp-job-portal')).'</span>
                            </a>
                    </div>';
                }
            break;

            case 'listjobbytype':
                $wpjobportal_print = wpjobportal_jobseekercheckLinks($wpjobportal_value);
                if ($wpjobportal_print) {
                    echo '<div class="wjportal-cp-list">
                            <a class="wjportal-list-anchor" href='. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job','wpjobportallt'=>'jobsbytypes'))).' title="'. esc_attr(__('Jobs By Types', 'wp-job-portal')).'">
                            '. wp_kses(wpjobportal_cp_menu_icon('tags'), WPJOBPORTAL_ALLOWED_TAGS) .'
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
                                '. wp_kses(wpjobportal_cp_menu_icon('building'), WPJOBPORTAL_ALLOWED_TAGS) .'
                                <span class="wjportal-cp-link-text">'. esc_html(__('Companies', 'wp-job-portal')).'</span></a>
                            </div>';
                    }
                }
            break;

            case 'jobsbycities':
                $wpjobportal_print = wpjobportal_jobseekercheckLinks($wpjobportal_value);
                if ($wpjobportal_print) {
                    echo '<div class="wjportal-cp-list">
                            <a class="wjportal-list-anchor" href='. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job','wpjobportallt'=>'jobsbycities'))).' title="'. esc_attr(__('Jobs By Cities', 'wp-job-portal')).'">
                            '. wp_kses(wpjobportal_cp_menu_icon('map-marker'), WPJOBPORTAL_ALLOWED_TAGS) .'
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
                                    <a class="wjportal-list-anchor" href='. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume','wpjobportallt'=>'addresume','wpjobportalid'=>$wpjobportal_resumeid))).' title="'. esc_attr(__('Edit Resume', 'wp-job-portal')).'">
                                        '. wp_kses(wpjobportal_cp_menu_icon('pencil-square'), WPJOBPORTAL_ALLOWED_TAGS) .'
                                        <span class="wjportal-cp-link-text">'. esc_html(__('Edit Resume', 'wp-job-portal')).'</span>
                                    </a>
                            </div>';
                        } else {
                            echo '<div class="wjportal-cp-list">
                                    <a class="wjportal-list-anchor" href='. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume','wpjobportallt'=>'addresume'))).' title="'. esc_attr(__('Add Resume', 'wp-job-portal')).'">
                                        '. wp_kses(wpjobportal_cp_menu_icon('plus-square'), WPJOBPORTAL_ALLOWED_TAGS) .'
                                        <span class="wjportal-cp-link-text">'. esc_html(__('Add Resume', 'wp-job-portal')).'</span>
                                    </a>
                            </div>';
                        }
                    }
                }
            break;

            case 'jobsloginlogout':
                if (wpjobportal_jobseekercheckLinks($wpjobportal_value)) {
                    if (WPJOBPORTALincluder::getObjectClass('user')->isguest() && (!WPJOBPORTALrequest::hasCookie('wpjobportal-socialmedia') && empty($_COOKIE['wpjobportal-socialmedia']))) {
                        $wpjobportal_thiscpurl = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobseeker','wpjobportallt'=>'controlpanel','wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        $wpjobportal_thiscpurl = wpjobportalphplib::wpJP_safe_encoding($wpjobportal_thiscpurl);
                        $wpjobportal_defaultUrl = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'wpjobportal','wpjobportallt'=>'login','wpjobportalredirecturl'=>$wpjobportal_thiscpurl,'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        $wpjobportal_lrlink = WPJOBPORTALincluder::getJSModel('configuration')->getLoginRegisterRedirectLink($wpjobportal_defaultUrl,'login');
                        echo '<div class="wjportal-cp-list">
                                <a class="wjportal-list-anchor" href='.esc_url($wpjobportal_lrlink).' title="'.esc_attr(__('Login', 'wp-job-portal')).'">
                                    '. wp_kses(wpjobportal_cp_menu_icon('sign-in'), WPJOBPORTAL_ALLOWED_TAGS) .'
                                    <span class="wjportal-cp-link-text">'.esc_html(__('Login', 'wp-job-portal')).'</span>
                                </a>
                            </div>';
                    } else {
                        $wpjobportal_logout_url = wp_logout_url(get_permalink());
                        if (WPJOBPORTALrequest::hasCookie('wpjobportal-socialmedia') && !empty(WPJOBPORTALrequest::cookie('wpjobportal-socialmedia'))) {
                            $wpjobportal_logout_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'sociallogin','task'=>'socialogout','action'=>'wpjobportaltask','wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        }
                        echo '<div class="wjportal-cp-list">
                                <a class="wjportal-list-anchor" href='. esc_url($wpjobportal_logout_url) .' title="'. esc_attr(__('Logout', 'wp-job-portal')).'">
                                    '. wp_kses(wpjobportal_cp_menu_icon('sign-out'), WPJOBPORTAL_ALLOWED_TAGS) .'
                                    <span class="wjportal-cp-link-text">'. esc_html(__('Logout', 'wp-job-portal')).'</span>
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

    echo $wpjobportal_section_links; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- buffered output of the escaped echoes above.
}

/**
 * Allow premium extensions to add a native job-seeker dashboard section.
 * Extensions are responsible for capability checks and escaped output.
 */
/*
 * Normally this has already run, just above System - see the employer menu for
 * why the account links belong last. It runs here only when there was no
 * System section to run before.
 */
if (empty($wpjobportal_extensions_printed)) {
    do_action('wpjobportal_jobseeker_dashboard_extension_links', $wpjobportal_layout);
    $wpjobportal_extensions_printed = true;
}
?>
