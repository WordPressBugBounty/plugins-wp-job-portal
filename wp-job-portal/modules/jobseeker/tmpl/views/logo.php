<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param job      job object - optional
 */
?>
<?php
	switch ($wpjobportal_layout) {
		case 'toprowlogo':
			echo '
				 <div class="wjportal-jobs-logo">
					<a href='. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$wpjobportal_job->companyid))) .' >
					    <img src='. esc_url(WPJOBPORTALincluder::getJSModel('company')->getLogoUrl($wpjobportal_job->companyid,$wpjobportal_job->logofilename)).' alt="'.esc_attr(__('Company logo','wp-job-portal')).'">
					</a>
				</div>
				';
		break;
		case 'profile':
			$wpjobportal_img = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('jobseeker');
			if (!empty($wpjobportal_profile->photo)) {
		        $wpjobportal_wpdir = wp_upload_dir();
		        $wpjobportal_data_directory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
		        $wpjobportal_img = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/profile/profile_' . $wpjobportal_profile->uid . '/profile/' . $wpjobportal_profile->photo;
        	}
        	$wpjobportal_field_ordering = wpjobportalincluder::getJSModel('fieldordering')->getFieldsOrderingforView(4);
        	if(!empty($wpjobportal_field_ordering) && isset($wpjobportal_field_ordering['photo'])){
				echo '<div class="wjportal-user-logo">
			 		<img src='.esc_url($wpjobportal_img).' class="wjportal-user-logo-image" alt="'.esc_attr(__('Profile image','wp-job-portal')).'">
		 		</div>';
        	}

		 	echo '<div class="wjportal-jobseeker-cp-data-top-middle-wrap">';
		 		if (isset($wpjobportal_profile->first_name)) {
				 	echo '<div class="wjportal-user-name">
				 			'.  esc_html(isset($wpjobportal_profile->first_name) ? esc_html($wpjobportal_profile->first_name): '' ) .'
				 			'.  esc_html(isset($wpjobportal_profile->last_name) ? esc_html($wpjobportal_profile->last_name): '' ) .'
	             	</div>';
	         	}
	         	if (isset(wpjobportal::$_data['application_title'])) {
					echo '<div class="wjportal-user-tagline">
							'.  esc_html(isset(wpjobportal::$_data['application_title'])? wpjobportal::wpjobportal_getVariableValue(wpjobportal::$_data['application_title']):'' ) .'
	            	</div>';
	        	}
            echo	'</div>';
		break;
		default:
			$wpjobportal_msg=esc_html(__('No Record Found','wp-job-portal')) ;
			echo '
			 	<div class="js-image">
					'.wp_kses(WPJOBPORTALlayout::getNoRecordFound($wpjobportal_msg, esc_url($wpjobportal_linkcompany)),WPJOBPORTAL_ALLOWED_TAGS).'
			 	</div>
		 	';
		break;
	}
?>

