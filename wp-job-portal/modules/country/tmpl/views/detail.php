<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
/**
*
*/
?>
<tr>
    <td>
        <input type="checkbox" class="wpjobportal-cb" id="wpjobportal-cb" name="wpjobportal-cb[]" value="<?php echo esc_attr($row->id); ?>" />
    </td>
    <td class="wpjobportal-text-left">
        <a href="<?php echo esc_url_raw(admin_url('admin.php?page=wpjobportal_country&wpjobportallt=formcountry&wpjobportalid='.$row->id)); ?>" title="<?php echo esc_html(__('name','wp-job-portal')); ?>">
            <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($row->name)); ?>
        </a>
    </td>
    <td>
        <?php if ($row->enabled == 1) { ?>
            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_country&task=unpublish&action=wpjobportaltask&wpjobportal-cb[]='.$row->id.$pageid),'wpjobportal_country_nonce')); ?>" title="<?php echo esc_html(__('published', 'wp-job-portal')); ?>">
                <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/good.png" border="0" alt="<?php echo esc_html(__('published', 'wp-job-portal')); ?>" />
            </a>
        <?php } else { ?>
            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_country&task=publish&action=wpjobportaltask&wpjobportal-cb[]='.$row->id.$pageid),'wpjobportal_country_nonce')); ?>" title="<?php echo esc_html(__('not published', 'wp-job-portal')); ?>">
                <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/close.png" border="0" alt="<?php echo esc_html(__('not published', 'wp-job-portal')); ?>" />
            </a>
        <?php } ?>
    </td>
    <td>
        <a href="<?php echo esc_url_raw(admin_url('admin.php?page=wpjobportal_state&countryid='.$row->id)); ?>" title="<?php echo esc_html(__('states', 'wp-job-portal')); ?>">
            <?php echo esc_html(__('States', 'wp-job-portal')); ?>
        </a>
    </td>
    <td>
        <a href="<?php echo esc_url_raw(admin_url('admin.php?page=wpjobportal_city&countryid='.$row->id)); ?>" title="<?php echo esc_html(__('cities', 'wp-job-portal')); ?>">
            <?php echo esc_html(__('Cities', 'wp-job-portal')); ?>
        </a>
    </td>
    <td>
        <a class="wpjobportal-table-act-btn" href="<?php echo esc_url_raw(admin_url('admin.php?page=wpjobportal_country&wpjobportallt=formcountry&wpjobportalid='.$row->id)); ?>" title="<?php echo esc_html(__('edit', 'wp-job-portal')); ?>">
            <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/edit.png" alt="<?php echo esc_html(__('edit', 'wp-job-portal')); ?>">
        </a>
        <a class="wpjobportal-table-act-btn" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_country&task=remove&action=wpjobportaltask&wpjobportal-cb[]='.$row->id),'wpjobportal_country_nonce')); ?>" onclick='return confirmdelete("<?php echo esc_html(__('Are you sure to delete', 'wp-job-portal')).' ?'; ?>");' title="<?php echo esc_html(__('delete', 'wp-job-portal')); ?>">
            <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/delete.png" alt="<?php echo esc_html(__('delete', 'wp-job-portal')); ?>">
        </a>
    </td>
</tr>