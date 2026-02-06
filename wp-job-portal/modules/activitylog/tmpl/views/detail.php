<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
/**
* @param wp-job-portal Optional
* ==>Detail
*/
?>
<tr>
	<td>
		<?php echo esc_html($wpjobportal_data->id); ?>
	</td>
	<td class="wpjobportal-text-left">
		<?php echo esc_html($wpjobportal_data->first_name) . ' ' . esc_html($wpjobportal_data->last_name); ?>
	</td>
	<td class="wpjobportal-text-left">
		<?php echo wp_kses($wpjobportal_data->description, WPJOBPORTAL_ALLOWED_TAGS); ?>
	</td>
	<td>
		<?php echo esc_html(wpjobportalphplib::wpJP_ucwords($wpjobportal_data->referencefor)); ?>
	</td>
	<td>
		<?php echo esc_html($wpjobportal_data->created); ?>
	</td>
</tr>
