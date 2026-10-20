<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
/**
* @param wp job portal
* @param WP Job Portal Details
*/
?>
<tr>
    <td>
        <input type="checkbox" class="wpjobportal-cb" id="wpjobportal-cb" name="wpjobportal-cb[]" value="<?php echo esc_attr($row->id); ?>" />
    </td>
    <td class="wpjobportal-text-left">
        <a href="<?php echo esc_url($link); ?>" title="<?php echo esc_attr__('Tag Name','wp-job-portal'); ?>">
            <?php echo esc_html($row->tag); ?>
        </a>
    </td>
    <td>
        <?php
            if ($row->tagfor == 1) {
                echo esc_html__('Employer', 'wp-job-portal');
            } elseif ($row->tagfor == 2) {
                echo esc_html__('Job seeker', 'wp-job-portal');
            } elseif($row->tagfor == 3) {
                echo esc_html__('Both', 'wp-job-portal');
            }
        ?>
    </td>
    <td>
        <?php if ($row->status == '1') { ?>
            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_tag&task=unpublish&action=wpjobportaltask&wpjobportal-cb[]='.$row->id.$pageid),'wpjobportal_tag_nonce')); ?>" title="<?php echo esc_attr__('published', 'wp-job-portal'); ?>">
                <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL . 'includes/images/control_panel/dashboard/good.png'); ?>" border="0" alt="<?php echo esc_attr__('published', 'wp-job-portal'); ?>" />
            </a>
           <?php } else { ?>
            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_tag&task=publish&action=wpjobportaltask&wpjobportal-cb[]='.$row->id.$pageid),'wpjobportal_tag_nonce')); ?>" title="<?php echo esc_attr__('Not Published', 'wp-job-portal'); ?>">
                <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL . 'includes/images/control_panel/dashboard/close.png'); ?>" border="0" alt="<?php echo esc_attr__('Not Published', 'wp-job-portal'); ?>" />
            </a>
    <?php } ?>
    </td>
    <td>
        <a class="wpjobportal-table-act-btn" href="<?php echo esc_url($link); ?>" title="<?php echo esc_attr__('Edit', 'wp-job-portal'); ?>">
            <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL . 'includes/images/control_panel/dashboard/edit.png'); ?>" alt="<?php echo esc_attr__('Edit', 'wp-job-portal'); ?>">
        </a>
        <a class="wpjobportal-table-act-btn" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_tag&task=removetag&action=wpjobportaltask&wpjobportal-cb[]='.$row->id),'wpjobportal_tag_nonce')); ?>" onclick="return confirmdelete('<?php echo esc_html__('Are you sure to delete', 'wp-job-portal').' ?'; ?>');" title="<?php echo esc_attr__('Delete', 'wp-job-portal'); ?>">
            <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL . 'includes/images/control_panel/dashboard/delete.png'); ?>" alt="<?php echo esc_attr__('Delete', 'wp-job-portal'); ?>">
        </a>
    </td>
</tr>