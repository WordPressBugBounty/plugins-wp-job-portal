<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
* @param js-job optional  
*/
?>
<div id="job_<?php echo esc_attr($wpjobportal_job->id); ?>" class="wpjobportal-jobs-list">
	<div id="item-data">
	    <span id="selector_<?php echo esc_attr($wpjobportal_job->id); ?>" class="selector">
	    	<input type="checkbox" onclick="javascript:highlight(<?php echo esc_js($wpjobportal_job->id); ?>);" class="wpjobportal-cb" id="wpjobportal-cb" name="wpjobportal-cb[]" value="<?php echo esc_attr($wpjobportal_job->id); ?>" />
	    </span>
		<?php
			WPJOBPORTALincluder::getTemplate('job/views/admin/jobdetail',array(
				'wpjobportal_job' => $wpjobportal_job,
				'wpjobportal_layout' => $wpjobportal_layout,
				'wpjobportal_logo' => $wpjobportal_logo
			));
		?>
	</div>
</div>