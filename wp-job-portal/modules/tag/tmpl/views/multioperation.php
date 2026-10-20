<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
/**
* @param WP Job Portal
* @param multioperation
*/
?>
<div id="wpjobportal-page-quick-actions">
    <a class="wpjobportal-page-quick-act-btn multioperation" message="<?php echo esc_attr(WPJOBPORTALMessages::getMSelectionEMessage()); ?>" data-for="publish" href="#" title="<?php echo esc_attr__('publish', 'wp-job-portal') ?>">
    	<img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL . 'includes/images/control_panel/dashboard/good.png'); ?>" alt="<?php echo esc_attr__('publish', 'wp-job-portal') ?>" />
    	<?php echo esc_html__('Publish', 'wp-job-portal') ?>
    </a>
    <a class="wpjobportal-page-quick-act-btn multioperation" message="<?php echo esc_attr(WPJOBPORTALMessages::getMSelectionEMessage()); ?>" data-for="unpublish" href="#" title="<?php echo esc_attr__('Unpublish', 'wp-job-portal') ?>">
    	<img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL . 'includes/images/control_panel/dashboard/close.png'); ?>" alt="<?php echo esc_attr__('Unpublish', 'wp-job-portal') ?>" />
    	<?php echo esc_html__('Unpublish', 'wp-job-portal') ?>
    </a>
    <a class="wpjobportal-page-quick-act-btn multioperation" message="<?php echo esc_attr(WPJOBPORTALMessages::getMSelectionEMessage()); ?>" confirmmessage="<?php echo esc_html__('Are you sure to delete','wp-job-portal') . ' ?'; ?>" data-for="removetag" href="#" title="<?php echo esc_attr__('Delete', 'wp-job-portal'); ?>">
    	<img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL . 'includes/images/control_panel/dashboard/forced-delete.png'); ?>" alt="<?php echo esc_attr__('Delete', 'wp-job-portal'); ?>" />
    	<?php echo esc_html__('Delete', 'wp-job-portal') ?>
    </a>
</div>
