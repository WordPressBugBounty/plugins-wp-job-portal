<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param job      job object - optional
 */

$wpjobportal_published_fields = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldOrderingData(1);
if(isset($wpjobportal_published_fields['logo']) && $wpjobportal_published_fields['logo'] != ''){
	switch ($wpjobportal_layout) {
		case 'toprowlogo':
			if($wpjobportal_job->companyid != '' && is_numeric($wpjobportal_job->companyid) && $wpjobportal_job->companyid > 0){
				if(empty(wpjobportal::$_data['shortcode_option_hide_company_logo'])){
		            $wpjobportal_path = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('employer');
					if (isset($wpjobportal_job->logofilename) && $wpjobportal_job->logofilename != "") {
			            $wpjobportal_wpdir = wp_upload_dir();
			            $wpjobportal_data_directory = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('data_directory');
			            $wpjobportal_path = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/employer/comp_' . $wpjobportal_job->companyid . '/logo/' . $wpjobportal_job->logofilename;
			        }
			        if(in_array('multicompany', wpjobportal::$_active_addons)){
			        	$wpjobportal_url = "multicompany";
			        }else{
			        	$wpjobportal_url = "company";
			        }
					echo '<div class="wjportal-jobs-logo">
		                    <a href='. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>$wpjobportal_url, 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$wpjobportal_job->companyid))) .'>
		                        <img src='. esc_url($wpjobportal_path) .' alt="'.esc_attr(__('Company logo','wp-job-portal')).'" />
		                    </a>
		                </div>
						';
				}
			}
			break;
		case 'logo':
			if($wpjobportal_job->companyid != '' && is_numeric($wpjobportal_job->companyid) && $wpjobportal_job->companyid > 0){
				echo ' <div class="wjportal-job-company-logo">
		                    <img class="wjportal-job-company-logo-image" src='. esc_url(WPJOBPORTALincluder::getJSModel('company')->getLogoUrl($wpjobportal_job->companyid,$wpjobportal_job->logofilename)) .'  alt="'.esc_attr(__('Company logo','wp-job-portal')).'">
		                </div>';
			}
		break;
	}
}
?>

