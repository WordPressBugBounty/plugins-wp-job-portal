<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param field 		fieldordering field object
 * @param title 		field title or name
 * @param required  	is field required
 * @param content 		field html
 * @param description 	field description
 */
if (isset($wpjobportal_field)) {
	if (!isset($title)) {
		$title = $wpjobportal_field->fieldtitle;
	}
	if (!isset($wpjobportal_required)) {
		$wpjobportal_required = $wpjobportal_field->required;
	}
	if (!isset($wpjobportal_description)) {
		$wpjobportal_description = $wpjobportal_field->description;
	}
} else {
    if (!isset($title)) {
        $title = '';
    }
    if (!isset($wpjobportal_required)) {
        $wpjobportal_required = false;
    }
    if (!isset($wpjobportal_description)) {
        $wpjobportal_description = '';
    }
}
$wpjobportal_visibleclass = "";
if (isset($wpjobportal_field->visibleparams) && $wpjobportal_field->visibleparams != ''){
    $wpjobportal_visibleclass = " visible js-form-custm-flds-wrp";
}

$wpjobportal_fullwidth_class = "";
if(isset($wpjobportal_field->field) && $wpjobportal_field->field == 'description') {
   $wpjobportal_fullwidth_class = "wpjobportal-fullwidth";
}
$wpjobportal_field_key = isset($wpjobportal_field->field) ? sanitize_key($wpjobportal_field->field) : '';
$wpjobportal_field_uid = wp_unique_id('wpjp-admin-field-' . ($wpjobportal_field_key ? $wpjobportal_field_key . '-' : ''));
$wpjobportal_label_id = $wpjobportal_field_uid . '-label';
$wpjobportal_description_id = !empty($wpjobportal_description) ? $wpjobportal_field_uid . '-help' : '';
$wpjobportal_content = WPJOBPORTALformfield::prepareAccessibleContent(
    $wpjobportal_content,
    $wpjobportal_label_id,
    $wpjobportal_description_id,
    1 == $wpjobportal_required
);
$wpjobportal_control_id = WPJOBPORTALformfield::getFirstControlId($wpjobportal_content);
?>
<div class="wpjobportal-form-wrapper wpjp-ds-field <?php echo esc_attr($wpjobportal_fullwidth_class); ?> <?php echo esc_attr($wpjobportal_visibleclass); ?>">
    <?php if ('' !== $wpjobportal_control_id) : ?>
    <label class="wpjobportal-form-title wpjp-ds-label" id="<?php echo esc_attr($wpjobportal_label_id); ?>" for="<?php echo esc_attr($wpjobportal_control_id); ?>">
    <?php else : ?>
    <span class="wpjobportal-form-title wpjp-ds-label" id="<?php echo esc_attr($wpjobportal_label_id); ?>">
    <?php endif; ?>
        <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($title)); ?>
        <?php if($wpjobportal_required == 1): ?>
	        	<span class="wpjp-ds-required" aria-hidden="true">*</span>
            <span class="wpjp-ds-visually-hidden"><?php echo esc_html__('Required', 'wp-job-portal'); ?></span>
    	<?php endif;



        ?>
    <?php if ('' !== $wpjobportal_control_id) : ?></label><?php else : ?></span><?php endif; ?>

    <div class="wpjobportal-form-value" role="group" aria-labelledby="<?php echo esc_attr($wpjobportal_label_id); ?>">
           <?php
            if(isset($wpjobportal_field->field) && ($wpjobportal_field->field == 'description' || $wpjobportal_field->field == 'skills')  ){
               echo '<a href="#" id="zywrap-open-modal-button" class="button" title="' . esc_attr__('AI Content Generation', 'wp-job-portal') . '">
                     <span class="dashicons dashicons-testimonial"></span>
                     ' . esc_html__('AI Content Generation', 'wp-job-portal') . '
                     </a>';
            wp_enqueue_script('media-upload');
            wp_enqueue_script('thickbox');
            wp_enqueue_style('thickbox');
            echo wp_kses(WPJOBPORTALformfield::hidden('wpjobportal_content_generation_fieldfor', $wpjobportal_field->fieldfor),WPJOBPORTAL_ALLOWED_TAGS);

            }
        ?>
        <?php echo wp_kses($wpjobportal_content, WPJOBPORTAL_ALLOWED_TAGS); ?>
    </div>
        <?php if(!empty($wpjobportal_description)): ?>
            <div class="wpjobportal-form-description wpjp-ds-help" id="<?php echo esc_attr($wpjobportal_description_id); ?>"><?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_description)); ?></div>
        <?php endif; ?>
        <?php
        // adding import data message to city fields
        if(is_admin()){ // only show on admin side
            if(isset($wpjobportal_field->field) && $wpjobportal_field->field == 'city') { // only show on city fields ?>
                <div class="wpjobportal-city-field-import-data-link-wrapper">
                    <a class="wpjobportal-city-field-import-data-link" href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_city&wpjobportallt=loadaddressdata')); ?>" title="<?php echo esc_attr(__('Import Location Data', 'wp-job-portal')); ?>">
                        <?php echo esc_html(__('Import Location Data', 'wp-job-portal')); ?>
                    </a>
                </div>
            <?php
            }
        }
        ?>
</div>
