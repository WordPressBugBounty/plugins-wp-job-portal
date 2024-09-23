<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param field 		fieldordering field object
 * @param title 		field title or name
 * @param required  	is field required
 * @param content 		field html
 * @param description 	field description
 */
if (isset($field)) {
	if (!isset($title)) {
		$title = $field->fieldtitle;
	}
	if (!isset($required)) {
		$required = $field->required;
	}
	if (!isset($description)) {
		$description = $field->description;
	}
} else {
    if (!isset($title)) {
        $title = '';
    }
    if (!isset($required)) {
        $required = false;
    }
    if (!isset($description)) {
        $description = '';
    }
}
$visibleclass = "";
if (isset($field->visibleparams) && $field->visibleparams != ''){
    $visibleclass = " visible js-form-custm-flds-wrp";
}

$fullwidth_class = "";
if(isset($field->field) && $field->field == 'description') {
   $fullwidth_class = "wpjobportal-fullwidth";
}


?>
<div class="wpjobportal-form-wrapper <?php echo esc_attr($fullwidth_class); ?> <?php echo esc_attr($visibleclass); ?>">
    <div class="wpjobportal-form-title">
        <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($title)); ?>
        <?php if($required == 1): ?>
        	<span color="red">*</span>
    	<?php endif; ?>
    </div>
    <div class="wpjobportal-form-value">
        <?php echo wp_kses($content, WPJOBPORTAL_ALLOWED_TAGS); ?>
    </div>
        <?php if(!empty($description)): ?>
            <div class="wpjobportal-form-description"><?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($description)); ?></div>
        <?php endif; ?>
</div>