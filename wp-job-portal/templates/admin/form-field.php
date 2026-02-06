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
?>
<div class="wpjobportal-form-wrapper <?php echo esc_attr($wpjobportal_fullwidth_class); ?> <?php echo esc_attr($wpjobportal_visibleclass); ?>">
    <div class="wpjobportal-form-title">
        <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($title)); ?>
        <?php if($wpjobportal_required == 1): ?>
        	<span color="red">*</span>
    	<?php endif;



        ?>
    </div>

    <div class="wpjobportal-form-value">
        <?php echo wp_kses($wpjobportal_content, WPJOBPORTAL_ALLOWED_TAGS); ?>
    </div>
        <?php if(!empty($wpjobportal_description)): ?>
            <div class="wpjobportal-form-description"><?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_description)); ?></div>
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
