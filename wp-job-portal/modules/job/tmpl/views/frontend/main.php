<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param job      job object - optional
 */
if(!isset($wpjobportal_labelflag)){
	$wpjobportal_labelflag = '';
}

?>
<div class="wjportal-jobs-cnt-wrp">
	<?php
		WPJOBPORTALincluder::getTemplate('job/views/frontend/title',array(
		    'wpjobportal_layout' => 'job',
		    'wpjobportal_job' => $wpjobportal_job,
		    'wpjobportal_labelflag' => $wpjobportal_labelflag,
		    'wpjobportal_control' => $wpjobportal_control
		));

        if ($wpjobportal_control == 'newestjobs') {
        	$wpjobportal_print = WPJOBPORTALincluder::getJSModel('job')->checkLinks('tags');
            if (isset($wpjobportal_print[0]) && $wpjobportal_print[0] == 1) {
		        if (!empty($wpjobportal_job->jobtags) && in_array('tag', wpjobportal::$_active_addons)) {
		            $wpjp_tags_array = explode(',', $wpjobportal_job->jobtags);
		            if (count($wpjp_tags_array) > 0) {
		                echo '<div class="wpjp-job-tags-list">';
		                foreach ($wpjp_tags_array as $wpjp_tag) {
		                    $wpjp_tag = trim($wpjp_tag);
		                    if (!empty($wpjp_tag)) {
		                        echo '<a href="#" class="wpjp-clickable-tag" data-tag="'.esc_attr($wpjp_tag).'">#'.esc_html($wpjp_tag).'</a>';
		                    }
		                }
		                echo '</div>';
		            }
		        }
	        }
        }
	?>
</div>