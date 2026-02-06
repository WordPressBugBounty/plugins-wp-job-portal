<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param job      job object - optional
*/
?>
	<div class="data-big-lower">
        <span class="big-lower-left">
            <img class="big-lower-img" src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/location.png"><?php echo esc_html($wpjobportal_myresume->location); ?>
        </span>
        <?php if ($wpjobportal_myresume->status == 1) {
            $wpjobportal_config_array_res = wpjobportal::$_data['config'];
         ?>
        <div class="big-lower-data-icons">
            <a href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'addresume', 'wpjobportalid'=>$wpjobportal_myresume->id, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()))); ?>"><img class="icon-img" src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/fe-edit.png" alt="<?php echo esc_attr(__('Edit', 'wp-job-portal')); ?>" title="<?php echo esc_attr(__('Edit', 'wp-job-portal')); ?>"/></a>
            <a href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'viewresume', 'wpjobportalid'=>$wpjobportal_myresume->id, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()))); ?>"><img class="icon-img" src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/fe-view.png" alt="<?php echo esc_attr(__('View', 'wp-job-portal')); ?>" title="<?php echo esc_attr(__('View', 'wp-job-portal')); ?>"/></a>
            <a href="<?php echo esc_url(wp_nonce_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'task'=>'removeresume', 'action'=>'wpjobportaltask', 'wpjobportal-cb[]'=>$wpjobportal_myresume->id, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid())),'wpjobportal_resume_nonce')); ?>"onclick="return confirmdelete('<?php echo esc_js(__('Are you sure to delete','wp-job-portal')).' ?'; ?>');"><img class="icon-img" src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/fe-force-delete.png" alt="<?php echo esc_attr(__('Delete', 'wp-job-portal')); ?>" title="<?php echo esc_attr(__('Delete', 'wp-job-portal')); ?>"/></a>
        </div>
		<?php } elseif ($wpjobportal_myresume->status == 0) { ?>
		            <div class="big-lower-data-text"><img id="pending-img"  src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/pending-corner.png"/><?php echo esc_html(__('Waiting for approval', 'wp-job-portal')); ?></div>
		<?php }elseif ($wpjobportal_myresume->status == -1){ ?>
		            <div class="big-lower-data-text rjctd"><img id="pending-img"  src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/reject-cornor.png"/><?php echo esc_html(__('Rejected', 'wp-job-portal')); ?></div>
		<?php
		} ?>
    </div>