<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
/**
* @param WP Job Portal
* @param Form -Field
*/
?>
 <div class="wpjobportal-form-wrapper">
    <div class="wpjobportal-form-title">
    	<?php echo esc_html__('Tag', 'wp-job-portal'); ?>
        <span style="color: red;" >*</span>
    </div>
    <div class="wpjobportal-form-value">
    	<?php echo wp_kses(WPJOBPORTALformfield::text('tag', isset(wpjobportal::$_data[0]->tag) ? wpjobportal::$_data[0]->tag : '', array('class' => 'inputbox one wpjobportal-form-input-field', 'data-validation' => 'required')), WPJOBPORTAL_ALLOWED_TAGS) ?>
    </div>
</div>
<div class="wpjobportal-form-wrapper">
    <div class="wpjobportal-form-title">
    	<?php echo esc_html__('Tag For', 'wp-job-portal'); ?>
    </div>
    <div class="wpjobportal-form-value">
    	<?php echo wp_kses(WPJOBPORTALformfield::radiobutton('tagfor', array('1' => __('Employer', 'wp-job-portal'), '2' => __('Job seeker', 'wp-job-portal'), '3' => __('Both', 'wp-job-portal')), isset(wpjobportal::$_data[0]->tagfor) ? wpjobportal::$_data[0]->tagfor : 3, array('class' => 'radiobutton')), WPJOBPORTAL_ALLOWED_TAGS); ?>
    </div>
</div>
<div class="wpjobportal-form-wrapper">
    <div class="wpjobportal-form-title">
    	<?php echo esc_html__('Published', 'wp-job-portal'); ?>
    </div>
    <div class="wpjobportal-form-value">
    	<?php echo wp_kses(WPJOBPORTALformfield::radiobutton('status', array('1' => __('Yes', 'wp-job-portal'), '0' => __('No', 'wp-job-portal')), isset(wpjobportal::$_data[0]->status) ? wpjobportal::$_data[0]->status : 1, array('class' => 'radiobutton')), WPJOBPORTAL_ALLOWED_TAGS); ?>
    </div>
</div>
<?php echo wp_kses(WPJOBPORTALformfield::hidden('id', isset(wpjobportal::$_data[0]->id) ? wpjobportal::$_data[0]->id : ''), WPJOBPORTAL_ALLOWED_TAGS); ?>
<?php echo wp_kses(WPJOBPORTALformfield::hidden('created', isset(wpjobportal::$_data[0]->created) ? wpjobportal::$_data[0]->created : date_i18n('Y-m-d')), WPJOBPORTAL_ALLOWED_TAGS); ?>
<?php
if (isset(wpjobportal::$_data[0]->id) AND ( wpjobportal::$_data[0]->id != 0)) {
    echo wp_kses(WPJOBPORTALformfield::hidden('isedit', 1), WPJOBPORTAL_ALLOWED_TAGS);
}
?>
<?php echo wp_kses(WPJOBPORTALformfield::hidden('action', 'tag_savetag'), WPJOBPORTAL_ALLOWED_TAGS); ?>
<?php echo wp_kses(WPJOBPORTALformfield::hidden('form_request', 'wp-job-portal'), WPJOBPORTAL_ALLOWED_TAGS); ?>
<?php echo wp_kses(WPJOBPORTALformfield::hidden('_wpnonce', wp_create_nonce('wpjobportal_tag_nonce')), WPJOBPORTAL_ALLOWED_TAGS); ?>
<div class="wpjobportal-form-button">
    <a id="form-cancel-button" class="wpjobportal-form-cancel-btn" href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_tag')); ?>" title="<?php echo esc_attr__('cancel', 'wp-job-portal'); ?>">
    	<?php echo esc_html__('Cancel', 'wp-job-portal'); ?>
    </a>
    <?php echo wp_kses(WPJOBPORTALformfield::submitbutton('save', __('Save','wp-job-portal') .' '. __('Tag', 'wp-job-portal'), array('class' => 'button wpjobportal-form-save-btn')), WPJOBPORTAL_ALLOWED_TAGS); ?>
</div>