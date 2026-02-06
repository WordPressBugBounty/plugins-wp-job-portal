<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
/**
* @param WP JOB PORTAL
*/
$wpjobportal_company_fields = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldOrderingData(1);
switch ($wpjobportal_layout) {
	case 'que-logo':
		if(!isset($wpjobportal_company_fields['logo'])){
			break;
		}
	    $wpjobportal_path = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('employer');
		if ($wpjobportal_company->logofilename != "") {
		    $wpjobportal_data_directory = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('data_directory');
		    $wpjobportal_wpdir = wp_upload_dir();
		    $wpjobportal_path = $wpjobportal_wpdir['baseurl'] .'/'. $wpjobportal_data_directory . '/data/employer/comp_' . $wpjobportal_company->id . '/logo/' . $wpjobportal_company->logofilename;
		}
		echo '<div class="wpjobportal-company-logo">';
		echo '	<a href='.esc_url_raw(admin_url('admin.php?page=wpjobportal_company&wpjobportallt=formcompany&wpjobportalid='.esc_attr($wpjobportal_company->id))).'&isqueue=1 title='.esc_html(__("logo","wp-job-portal")).'>
					<img src='. esc_url($wpjobportal_path).' alt='.esc_html(__("logo","wp-job-portal")).'>
				</a>
				<div class="wpjobportal-company-crt-date">
					'.esc_html(date_i18n(wpjobportal::$_configuration['date_format'], strtotime($wpjobportal_company->created))).'
				</div>
			</div>';
		
		break;
	case 'comp-logo':
		if(!isset($wpjobportal_company_fields['logo'])){
			break;
		}
        $wpjobportal_path = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('employer');
		if ($wpjobportal_company->logofilename != "") {
			$wpjobportal_wpdir = wp_upload_dir();
            $wpjobportal_data_directory = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('data_directory');
            $wpjobportal_path = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/employer/comp_' . $wpjobportal_company->id . '/logo/' . $wpjobportal_company->logofilename;
        }
        echo '<div class="wpjobportal-company-logo">
                	<a href='.esc_url_raw(admin_url('admin.php?page=wpjobportal_company&wpjobportallt=formcompany&wpjobportalid='.esc_html($wpjobportal_company->id))).' title='.esc_html(__("logo","wp-job-portal")).'>
                		<img src='.esc_url($wpjobportal_path).' alt='.esc_html(__("logo","wp-job-portal")).'>
                	</a>
                	<div class="wpjobportal-company-crt-date">
                		'.esc_html(date_i18n(wpjobportal::$_configuration['date_format'], strtotime($wpjobportal_company->created))).'
                	</div>
                </div>';
		break;
}


