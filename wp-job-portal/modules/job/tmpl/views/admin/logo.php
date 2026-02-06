<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
* @param logo wp-job-portal
*/
$wpjobportal_data_directory = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('data_directory');
$wpjobportal_company_fields = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldOrderingData(1);
$wpjobportal_html = '';
switch ($wpjobportal_layout) {
	case 'logo':
		if(!isset($wpjobportal_company_fields['logo'])){
			break;
		}
        $wpjobportal_logo = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('employer');
		if (isset($wpjobportal_job->logo) && $wpjobportal_job->logo != '') {
	        $wpjobportal_wpdir = wp_upload_dir();
	        $wpjobportal_logo = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory.'/data/employer/comp_'.$wpjobportal_job->companyid.'/logo/'. $wpjobportal_job->logo;
	    }
		$wpjobportal_html.= '<div class="wpjobportal-jobs-logo">
					<a href='. esc_url_raw(admin_url('admin.php?page=wpjobportal_job&wpjobportallt=formjob&wpjobportalid='.esc_attr($wpjobportal_job->id))).'>
						<img src='.$wpjobportal_logo.' alt='.esc_html(__("logo",'wp-job-portal')).'>
					</a>
				</div>';
		break;
	case 'que-logo':
		if(!isset($wpjobportal_company_fields['logo'])){
			break;
		}
        $wpjobportal_path = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('employer');
	 	if ($wpjobportal_job->logofilename != "") {
            $wpjobportal_data_directory = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('data_directory');
            $wpjobportal_wpdir = wp_upload_dir();
            $wpjobportal_path = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/employer/comp_' . $wpjobportal_job->companyid . '/logo/' . $wpjobportal_job->logofilename;
        }
		$wpjobportal_html.='<div class="wpjobportal-jobs-logo">
                    <a href='. esc_url_raw(admin_url('admin.php?page=wpjobportal_job&wpjobportallt=formjob&wpjobportalid='.esc_attr($wpjobportal_job->id).'&isqueue=1')).'>
                    	<img src='.$wpjobportal_path.' alt='.esc_html(__("logo",'wp-job-portal')).'>
                    </a>
                </div>';
		break;

	default:

		break;
}
echo wp_kses($wpjobportal_html, WPJOBPORTAL_ALLOWED_TAGS);

?>
