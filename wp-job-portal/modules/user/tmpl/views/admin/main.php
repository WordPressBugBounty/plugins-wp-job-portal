<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
* @param js-job Optional
*/
?>
<?php
$wpjobportal_approved = ($wpjobportal_user->status == 1) ? '<span class="text-green">' . esc_html(__('Approved', 'wp-job-portal')) . '</span>' : '<span class="text-red">' . esc_html(__('Rejected', 'wp-job-portal')) . '</span>';
?>
<div id="user_<?php echo esc_attr($wpjobportal_user->id); ?>" class="wpjobportal-user-list">
    <div id="item-data">
        <span id="selector_<?php echo esc_attr($wpjobportal_user->id); ?>" class="selector">
            <input type="checkbox" onclick="javascript:highlight(<?php echo esc_js($wpjobportal_user->id); ?>);" class="wpjobportal-cb" id="wpjobportal-cb" name="wpjobportal-cb[]" value="<?php echo esc_attr($wpjobportal_user->id); ?>" />
        </span>
		<?php
			wpjobportalincluder::getTemplate('user/views/admin/detail',array('wpjobportal_user' => $wpjobportal_user));
		?>
	</div>
</div>