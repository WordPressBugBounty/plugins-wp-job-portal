<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param WP JOB PORTAL
 * @param Main  
 */
$wpjobportal_listing_fields = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldOrderingDataForListing(3);
echo '<div class="wjportal-resume-status-dashboard-data">
<div class="wjportal-resume-status-dashboard-left-data">';
        $wpjobportal_photourl = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('jobseeker');
        if (isset($wpjobportal_resume->photo) && $wpjobportal_resume->photo != "") {
            $wpjobportal_wpdir = wp_upload_dir();
            $wpjobportal_data_directory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
            $wpjobportal_photourl = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/jobseeker/resume_' . $wpjobportal_resume->id . '/photo/' . $wpjobportal_resume->photo;
        }
        $wpjobportal_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'viewresume', 'wpjobportalid'=>$wpjobportal_resume->id, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));

        if(empty(wpjobportal::$_data['shortcode_option_hide_resume_photo'])){
            echo '
            <div class="wjportal-resume-logo">
                <span class="fir">
                    <a href="'.esc_url($wpjobportal_url).'">
                        <img  src="'.esc_url($wpjobportal_photourl).'" />
                    </a>
                </span>
            </div>
        ';
        }
        echo'
</div>
<div class="wjportal-resume-status-dashboard-right-data">
        ';
        echo '<div class="wjportal-resume-data">
                <a href='.esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'viewresume', 'wpjobportalid'=>$wpjobportal_resume->id, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()))).'>
                    <span class="wjportal-resume-name">'.esc_html($wpjobportal_resume->first_name) .' ' . esc_html($wpjobportal_resume->last_name).' '.'</span>
                    <span class="wjportal-resume-title">'. esc_html($wpjobportal_resume->applicationtitle) .'</span>
                </a>';
        echo '</div>  ';


        $wpjobportal_percentage = $wpjobportal_resume->percentage;
    // resume status bar
        echo '<div class="wjportal-progress-bar-container">';
        echo '    <div class="wjportal-progress-bar-header">';
        echo '        <span class="wjportal-progress-bar-title">' . esc_html(__('Profile Status', 'wp-job-portal')) . '</span>';
        echo '        <span class="wjportal-progress-bar-percentage">' . esc_html($wpjobportal_percentage) . '%</span>';
        echo '    </div>';
        echo '    <div class="wjportal-progress-bar-wrapper">';
        echo '        <div class="wjportal-progress-bar-fill" style="width: ' . esc_attr($wpjobportal_percentage) . '%;"></div>';
        echo '    </div>';
        echo '</div>';
        echo '<div class="wjportal-progress-complete-resume-wrap">';
        echo '<a href='.esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'addresume', 'wpjobportalid'=>$wpjobportal_resume->id, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()))).'>
                    ' . esc_html(__('Complete Your Resume', 'wp-job-portal')) . '
                </a>';
        echo '</div>';


echo '</div>
</div>';