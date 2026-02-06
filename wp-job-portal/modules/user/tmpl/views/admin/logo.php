<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
* @param wp job portal Logo
*/
?>
<?php
$wpjobportal_html = '';
switch ($wpjobportal_layout) {
	case 'userlogo':
	    $wpjobportal_photo = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('jobseeker');
		if (isset($wpjobportal_user->photo) && $wpjobportal_user->photo != '') {
		    $wpjobportal_wpdir = wp_upload_dir();
		    $wpjobportal_data_directory = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('data_directory');
		    $wpjobportal_photo = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/profile/profile_' . esc_attr($wpjobportal_user->uid) . '/profile/' . $wpjobportal_user->photo;
		}
		$wpjobportal_html.= '<div class="wpjobportal-user-logo">
                    <a href='. esc_url_raw(admin_url('admin.php?page=wpjobportal_user&wpjobportallt=userdetail&id='.esc_attr($wpjobportal_user->id))).'>
                    	<img src="'. esc_url($wpjobportal_photo) .'" alt='.esc_html(__("logo","wp-job-portal")).'>
                    </a>
                </div>';
		break;
}
echo wp_kses($wpjobportal_html, WPJOBPORTAL_ALLOWED_TAGS);
