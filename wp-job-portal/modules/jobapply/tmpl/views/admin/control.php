<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
* @param WP JOb Portal
* @param Conrtol Section 
*/
$wpjobportal_class = 'wpjobportal-resume-act-btn';
?>
 <div id="item-actions" class="wpjobportal-resume-action-wrp">
 	<a id="view-resume" class="wpjobportal-resume-act-btn" href="<?php echo esc_url_raw(admin_url('admin.php?page=wpjobportal_resume&wpjobportallt=viewresume&wpjobportalid='.$wpjobportal_data->appid)); ?>" title="<?php echo esc_attr(__('view profile', 'wp-job-portal')); ?>">
        <?php echo esc_html(__('View Profile', 'wp-job-portal')); ?>
	</a>
	<?php 
	    do_action('wpjobportal_addons_resume_bottom_action_appliedresume',$wpjobportal_data,$wpjobportal_class);
	    do_action('wpjobportal_addons_resume_bottom_action_appliedresume_exc',wpjobportal::$_data['jobid'],$wpjobportal_data);
    ?>
</div>