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
	if (!isset($wpjobportal_title)) {
		$wpjobportal_title = $wpjobportal_field->fieldtitle;
	}
	if (!isset($wpjobportal_required)) {
		$wpjobportal_required = $wpjobportal_field->required;
	}
	 if (!isset($wpjobportal_description)) {
	 	$wpjobportal_description = $wpjobportal_field->description;
	 }
} else {
    if (!isset($wpjobportal_title)) {
        $wpjobportal_title = '';
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
?>
<div class="wjportal-form-row <?php echo esc_attr($wpjobportal_visibleclass);?>">
    <div class="wjportal-form-title">

        <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_title)); ?>
        
        <?php if($wpjobportal_required == 1 && WPJOBPORTALrequest::getVar('wpjobportalme') != "jobsearch"): ?>
        	<font>*</font>
    	<?php endif; ?>

    </div>
    <div class="wjportal-form-value">

        <?php echo wp_kses($wpjobportal_content, WPJOBPORTAL_ALLOWED_TAGS); ?>

        <?php if(!empty($wpjobportal_description)): ?>
        	<div class="wjportal-form-help-txt"><?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_description)); ?></div>
        <?php endif; ?>

    </div>
</div>
