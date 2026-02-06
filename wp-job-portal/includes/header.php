<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

$wpjobportal_layout = WPJOBPORTALrequest::getVar('wpjobportallt');
if ($wpjobportal_layout == 'printresume' || $wpjobportal_layout == 'pdf')
    return false; // b/c we have print and pdf layouts
$wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme');
if(
    ($wpjobportal_module == 'company' && $wpjobportal_layout == 'addcompany') ||
    ($wpjobportal_module == 'company' && $wpjobportal_layout == 'mycompanies') ||
    ($wpjobportal_module == 'credits' && $wpjobportal_layout == 'employercredits') ||
    ($wpjobportal_module == 'creditslog' && $wpjobportal_layout == 'employercreditslog') ||
    ($wpjobportal_module == 'credits' && $wpjobportal_layout == 'ratelistemployer') ||
    ($wpjobportal_module == 'departments' && $wpjobportal_layout == 'adddepartment') ||
    ($wpjobportal_module == 'departments' && $wpjobportal_layout == 'mydepartments') ||
    ($wpjobportal_module == 'departments' && $wpjobportal_layout == 'viewdepartment') ||
    ($wpjobportal_module == 'folder' && $wpjobportal_layout == 'addfolder') ||
    ($wpjobportal_module == 'folder' && $wpjobportal_layout == 'myfolders') ||
    ($wpjobportal_module == 'folder' && $wpjobportal_layout == 'viewfolder') ||
    ($wpjobportal_module == 'folderresume' && $wpjobportal_layout == 'folderresume') ||
    ($wpjobportal_module == 'job' && $wpjobportal_layout == 'addjob') ||
    ($wpjobportal_module == 'job' && $wpjobportal_layout == 'myjobs') ||
    ($wpjobportal_module == 'jobapply' && $wpjobportal_layout == 'jobappliedresume') ||
    ($wpjobportal_module == 'employer' && $wpjobportal_layout == 'controlpanel') ||
    ($wpjobportal_module == 'employer' && $wpjobportal_layout == 'mystats') ||
    ($wpjobportal_module == 'message' && $wpjobportal_layout == 'employermessages') ||
    ($wpjobportal_module == 'message' && $wpjobportal_layout == 'jobmessages') ||
    ($wpjobportal_module == 'purchasehistory' && $wpjobportal_layout == 'employerpurchasehistory') ||
    ($wpjobportal_module == 'resumesearch' && $wpjobportal_layout == 'resumesearch') ||
    ($wpjobportal_module == 'resumesearch' && $wpjobportal_layout == 'resumesavesearch') ||
    ($wpjobportal_module == 'resume' && $wpjobportal_layout == 'resumebycategory') ||
    ($wpjobportal_module == 'resume' && $wpjobportal_layout == 'resumes')
){
    $wpjobportal_menu = 'employer';
}elseif(
    ($wpjobportal_module == 'company' && $wpjobportal_layout == 'companies') ||
    ($wpjobportal_module == 'company' && $wpjobportal_layout == 'viewcompany') ||
    ($wpjobportal_module == 'job' && $wpjobportal_layout == 'newestjobs') ||
    ($wpjobportal_module == 'job' && $wpjobportal_layout == 'jobs') ||
    ($wpjobportal_module == 'job' && $wpjobportal_layout == 'shortlistedjobs') ||
    ($wpjobportal_module == 'job' && $wpjobportal_layout == 'viewjob') ||
    ($wpjobportal_module == 'wpjobportal' && $wpjobportal_layout == 'login') ||
    ($wpjobportal_module == 'resume' && $wpjobportal_layout == 'viewresume') ||
    ($wpjobportal_module == 'message' && $wpjobportal_layout == 'sendmessage')
){
    if(WPJOBPORTALincluder::getObjectClass('user')->isEmployer()){
        $wpjobportal_menu = 'employer';
    }else{
        $wpjobportal_menu = 'jobseeker';
    }
}elseif(
    ($wpjobportal_module == 'coverletter' && $wpjobportal_layout == 'addcoverletter') ||
    ($wpjobportal_module == 'coverletter' && $wpjobportal_layout == 'mycoverletters') ||
    ($wpjobportal_module == 'coverletter' && $wpjobportal_layout == 'viewcoverletter') ||
    ($wpjobportal_module == 'credits' && $wpjobportal_layout == 'jobseekercredits') ||
    ($wpjobportal_module == 'credits' && $wpjobportal_layout == 'ratelistjobseeker') ||
    ($wpjobportal_module == 'creditslog' && $wpjobportal_layout == 'jobseekercreditslog') ||
    ($wpjobportal_module == 'job' && $wpjobportal_layout == 'jobsbycategories') ||
    ($wpjobportal_module == 'job' && $wpjobportal_layout == 'jobsbytypes') ||
    ($wpjobportal_module == 'visitorcanaddjob' && $wpjobportal_layout == 'visitoraddjob') ||
    ($wpjobportal_module == 'jobalert' && $wpjobportal_layout == 'jobalert') ||
    ($wpjobportal_module == 'jobapply' && $wpjobportal_layout == 'myappliedjobs') ||
    ($wpjobportal_module == 'jobsearch' && $wpjobportal_layout == 'jobsearch') ||
    ($wpjobportal_module == 'jobsearch' && $wpjobportal_layout == 'jobsavesearch') ||
    ($wpjobportal_module == 'jobseeker' && $wpjobportal_layout == 'controlpanel') ||
    ($wpjobportal_module == 'jobseeker' && $wpjobportal_layout == 'mystats') ||
    ($wpjobportal_module == 'message' && $wpjobportal_layout == 'jobseekermessages') ||
    ($wpjobportal_module == 'purchasehistory' && $wpjobportal_layout == 'jobseekerpurchasehistory') ||
    ($wpjobportal_module == 'resume' && $wpjobportal_layout == 'addresume') ||
    ($wpjobportal_module == 'resume' && $wpjobportal_layout == 'myresumes') ||
    ($wpjobportal_module == 'user' && $wpjobportal_layout == 'userregister')
){
    $wpjobportal_menu = 'jobseeker';

}else{
    $wpjobportal_menu = 'jobseeker';
}

$wpjobportal_div = '';
$wpjobportal_config_array = WPJOBPORTALincluder::getJSModel('configuration')->getConfigByFor('topmenu');
if ($wpjobportal_menu == 'employer') {
    if (is_user_logged_in()) { // Login user
        if ($wpjobportal_config_array['tmenu_emcontrolpanel'] == 1) {
            $wpjobportal_linkarray[] = array(
                'link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'employer', 'wpjobportallt'=>'controlpanel')),
                'title' => esc_html(__('Control Panel', 'wp-job-portal')),
            );
        }
        if ($wpjobportal_config_array['tmenu_emnewjob'] == 1) {
            $wpjobportal_linkarray[] = array(
                'link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'addjob')),
                'title' => esc_html(__('Add','wp-job-portal')) .' '. esc_html(__('Job', 'wp-job-portal')),
            );
        }
        if ($wpjobportal_config_array['tmenu_emmyjobs'] == 1) {
            $wpjobportal_linkarray[] = array(
                'link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'myjobs')),
                'title' => esc_html(__('My Jobs', 'wp-job-portal')),
            );
        }
        if ($wpjobportal_config_array['tmenu_emmycompanies'] == 1) {
            $wpjobportal_linkarray[] = array(
                'link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'mycompanies')),
                'title' => esc_html(__('My Companies', 'wp-job-portal')),
            );
        }
        if ($wpjobportal_config_array['tmenu_emsearchresume'] == 1) {
            if(in_array('resumesearch',wpjobportal::$_active_addons)){
                $wpjobportal_linkarray[] = array(
                    'link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resumesearch', 'wpjobportallt'=>'resumesearch')),
                    'title' => esc_html(__('Resume Search', 'wp-job-portal')),
                );
            }
        }
    } else { // user is visitor
        if ($wpjobportal_config_array['tmenu_vis_emcontrolpanel'] == 1) {
            $wpjobportal_linkarray[] = array(
                'link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'employer', 'wpjobportallt'=>'controlpanel')),
                'title' => esc_html(__('Control Panel', 'wp-job-portal')),
            );
        }
        if ($wpjobportal_config_array['tmenu_vis_emnewjob'] == 1) {
            $wpjobportal_linkarray[] = array(
                'link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'visitorcanaddjob', 'wpjobportallt'=>'visitoraddjob')),
                'title' => esc_html(__('Add','wp-job-portal')) .' '. esc_html(__('Job', 'wp-job-portal')),
            );
        }
        if ($wpjobportal_config_array['tmenu_vis_emmyjobs'] == 1) {
            $wpjobportal_linkarray[] = array(
                'link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'myjobs')),
                'title' => esc_html(__('My Jobs', 'wp-job-portal')),
            );
        }
        if ($wpjobportal_config_array['tmenu_vis_emmycompanies'] == 1) {
            $wpjobportal_linkarray[] = array(
                'link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'mycompanies')),
                'title' => esc_html(__('My Companies', 'wp-job-portal')),
            );
        }
        if ($wpjobportal_config_array['tmenu_vis_emsearchresume'] == 1) {
            $wpjobportal_linkarray[] = array(
                'link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resumesearch', 'wpjobportallt'=>'resumesearch')),
                'title' => esc_html(__('Search Resume', 'wp-job-portal')),
            );
        }
    }
} else {
    if (is_user_logged_in()) {
        if ($wpjobportal_config_array['tmenu_jscontrolpanel'] == 1) {
            $wpjobportal_linkarray[] = array(
                'link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobseeker', 'wpjobportallt'=>'controlpanel')),
                'title' => esc_html(__('Control Panel', 'wp-job-portal')),
            );
        }
        if ($wpjobportal_config_array['tmenu_wpjobportalcategory'] == 1) {
            $wpjobportal_linkarray[] = array(
                'link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'jobsbycategories')),
                'title' => esc_html(__('Jobs By Categories', 'wp-job-portal')),
            );
        }
        if ($wpjobportal_config_array['tmenu_jssearchjob'] == 1) {
            $wpjobportal_linkarray[] = array(
                'link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobsearch', 'wpjobportallt'=>'jobsearch')),
                'title' => esc_html(__('Job Search', 'wp-job-portal')),
            );
        }
        if ($wpjobportal_config_array['tmenu_jsnewestjob'] == 1) {
            $wpjobportal_linkarray[] = array(
                'link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'newestjobs')),
                'title' => esc_html(__('Newest Jobs', 'wp-job-portal')),
            );
        }
        if ($wpjobportal_config_array['tmenu_jsmyresume'] == 1) {
            $wpjobportal_linkarray[] = array(
                'link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'addresume')),
                'title' => esc_html(__('My Resumes', 'wp-job-portal')),
            );
        }
    } else { // user is visitor
        if ($wpjobportal_config_array['tmenu_vis_jscontrolpanel'] == 1) {
            $wpjobportal_linkarray[] = array(
                'link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobseeker', 'wpjobportallt'=>'controlpanel')),
                'title' => esc_html(__('Control Panel', 'wp-job-portal')),
            );
        }
        if ($wpjobportal_config_array['tmenu_vis_wpjobportalcategory'] == 1) {
            $wpjobportal_linkarray[] = array(
                'link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'jobsbycategories')),
                'title' => esc_html(__('Jobs By Categories', 'wp-job-portal')),
            );
        }
        if ($wpjobportal_config_array['tmenu_vis_jssearchjob'] == 1) {
            $wpjobportal_linkarray[] = array(
                'link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobsearch', 'wpjobportallt'=>'jobsearch')),
                'title' => esc_html(__('Job Search', 'wp-job-portal')),
            );
        }
        if ($wpjobportal_config_array['tmenu_vis_jsnewestjob'] == 1) {
            $wpjobportal_linkarray[] = array(
                'link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'newestjobs')),
                'title' => esc_html(__('Newest Jobs', 'wp-job-portal')),
            );
        }
        if ($wpjobportal_config_array['tmenu_vis_jsmyresume'] == 1) {
            $wpjobportal_linkarray[] = array(
                'link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'myresumes')),
                'title' => esc_html(__('My Resumes', 'wp-job-portal')),
            );
        }
    }
}

if (isset($wpjobportal_linkarray)) {
    $wpjobportal_div .= '<div id="wpjobportal-header-main-wrapper">';
    foreach ($wpjobportal_linkarray AS $wpjobportal_link) {
        $wpjobportal_div .= '<a class="headerlinks" href="' . esc_url($wpjobportal_link['link']) . '">' . $wpjobportal_link['title'] . '</a>';
    }
    $wpjobportal_div .= '</div>';
}
echo wp_kses($wpjobportal_div, WPJOBPORTAL_ALLOWED_TAGS);
?>
