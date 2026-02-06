<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
switch ($wpjobportal_layouts) {
	case 'logo':

		$wpjobportal_profile=isset(wpjobportal::$_data['emp_profile']['company']) ? wpjobportal::$_data['emp_profile']['company'] : null;
		$wpjobportal_comp_name=isset(wpjobportal::$_data['employer_info']['companies'][0]) ? wpjobportal::$_data['employer_info']['companies'][0] : null;
		$wpjobportal_img = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('jobseeker');
		if (isset($wpjobportal_profile) && $wpjobportal_profile->photo != '' ) {
			$wpjobportal_wpdir = wp_upload_dir();
			$wpjobportal_data_directory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
			$wpjobportal_img = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/profile/profile_' . wpjobportal::$_data['emp_profile']['company']->uid . '/profile/' . wpjobportal::$_data['emp_profile']['company']->photo;
		}
		$wpjobportal_field_ordering = wpjobportalincluder::getJSModel('fieldordering')->getFieldsOrderingforView(4);
    	if(!empty($wpjobportal_field_ordering) && isset($wpjobportal_field_ordering['photo'])){
			echo '
				<div class="wjportal-user-logo">
	        	 	<img src='. esc_url($wpjobportal_img) .' alt="'.esc_attr(__("User image",'wp-job-portal')).'" title="'.esc_attr(__("User image",'wp-job-portal')).'" class="wjportal-user-logo-image" />
	        	</div>
	        ';
	    }
        if(isset($wpjobportal_profile->first_name) && $wpjobportal_profile->first_name != ''){
			echo '<div class="wjportal-jobseeker-cp-data-top-middle-wrap">';        	
			echo '<div class="wjportal-user-name">
					'.  esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_profile->first_name)).' '.esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_profile->last_name)) .'
				</div>';
           	echo '<div class="wjportal-user-tagline">
           		'. esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_profile->emailaddress)).'
           		</div>
        	';
        	echo '</div>';
        }
	break;
}
?>
