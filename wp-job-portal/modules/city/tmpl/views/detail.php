<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
/**
*
*/
?>
 <tr>
	<td>
	    <input type="checkbox" class="wpjobportal-cb" id="wpjobportal-cb" name="wpjobportal-cb[]" value="<?php echo esc_attr($wpjobportal_row->id); ?>" />
	</td>
	<td>
		<a href="<?php echo esc_url($wpjobportal_link); ?>" title="<?php echo esc_attr(__('name','wp-job-portal')); ?>">
	        <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_row->name)); ?>
	    </a>
	</td>

	<td>
        <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_row->internationalname)); ?>
	</td>

	<td>
        <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_row->localname)); ?>
	</td>

	<td>
		<?php if ($wpjobportal_row->enabled == '1') { ?>
	        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_city&task=unpublish&action=wpjobportaltask&wpjobportal-cb[]='.$wpjobportal_row->id.$wpjobportal_pageid),'wpjobportal_city_nonce')); ?>" title="<?php echo esc_attr(__('published', 'wp-job-portal')); ?>">
	            <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/good.png" border="0" alt="<?php echo esc_attr(__('published', 'wp-job-portal')); ?>" />
	        </a>
	    <?php } else { ?>
	        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_city&task=publish&action=wpjobportaltask&wpjobportal-cb[]='.$wpjobportal_row->id.$wpjobportal_pageid),'wpjobportal_city_nonce')); ?>" title="<?php echo esc_attr(__('not published', 'wp-job-portal')); ?>">
	            <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/close.png" border="0" alt="<?php echo esc_attr(__('not published', 'wp-job-portal')); ?>" />
	        </a>
	    <?php } ?>
	</td>
	<td>
		<a class="wpjobportal-table-act-btn" href="<?php echo esc_url($wpjobportal_link); ?>" title="<?php echo esc_attr(__('edit', 'wp-job-portal')); ?>">
	    	<img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/edit.png" alt="<?php echo esc_attr(__('edit', 'wp-job-portal')); ?>">
	    </a>
	    <a class="wpjobportal-table-act-btn" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_city&task=removecity&action=wpjobportaltask&wpjobportal-cb[]='.$wpjobportal_row->id),'wpjobportal_city_nonce')); ?>" onclick='return confirmdelete("<?php echo esc_js(__('Are you sure to delete', 'wp-job-portal')).' ?'; ?>");' title="<?php echo esc_attr(__('delete', 'wp-job-portal')); ?>">
	    	<img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/delete.png" alt="<?php echo esc_attr(__('delete', 'wp-job-portal')); ?>">
	    </a>
	</td>
</tr>
