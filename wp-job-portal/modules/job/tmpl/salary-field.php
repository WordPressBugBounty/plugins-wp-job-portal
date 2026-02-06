<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
* @param field    salary field object
* @param job      job object - optional
*/
$wpjobportal_salarytypelist = array(
    (object) array('id'=>WPJOBPORTAL_SALARY_NEGOTIABLE,'text'=>esc_html(__("Negotiable",'wp-job-portal'))),
    (object) array('id'=>WPJOBPORTAL_SALARY_FIXED,'text'=>esc_html(__("Fixed",'wp-job-portal'))),
    (object) array('id'=>WPJOBPORTAL_SALARY_RANGE,'text'=>esc_html(__("Range",'wp-job-portal'))),
);
?>
<div class="wjportal-form-5-fields">
    <div class="wjportal-form-inner-fields">
        <?php echo wp_kses(WPJOBPORTALformfield::select('salarytype', $wpjobportal_salarytypelist, $wpjobportal_job ? esc_html($wpjobportal_job->salarytype) : 2, esc_html(__("Select",'wp-job-portal')).' '.esc_html(__("Salary Type",'wp-job-portal')), array('class' => 'inputbox sal wjportal-form-select-field', 'data-validation' => $wpjobportal_field->validation)),WPJOBPORTAL_ALLOWED_TAGS); ?>
    </div>
    <div class="wjportal-form-inner-fields wjportal-form-symbol-fields">
        <span class="wjportal-form-symbol"><?php echo isset($wpjobportal_job->currency) ? esc_html($wpjobportal_job->currency) : esc_html(wpjobportal::$_config->getConfigValue('job_currency')); ?></span>
    </div>
    <div class="wjportal-form-inner-fields">
        <?php echo wp_kses(WPJOBPORTALformfield::text('salaryfixed', $wpjobportal_job ? esc_html($wpjobportal_job->salarymin) : '', array('class' => 'inputbox sal wjportal-form-input-field','placeholder'=> esc_html(__('e.g 45000','wp-job-portal')),'data-validation' => $wpjobportal_field->validation)),WPJOBPORTAL_ALLOWED_TAGS); ?>
    </div>
    <div class="wjportal-form-inner-fields">
        <?php echo wp_kses(WPJOBPORTALformfield::text('salarymin', $wpjobportal_job ? esc_html($wpjobportal_job->salarymin) : '', array('class' => 'inputbox sal wjportal-form-input-field','placeholder'=> esc_html(__('e.g 3000','wp-job-portal')),'data-validation' => $wpjobportal_field->validation)),WPJOBPORTAL_ALLOWED_TAGS); ?>
    </div>
    <div class="wjportal-form-inner-fields">
        <?php echo wp_kses(WPJOBPORTALformfield::text('salarymax', $wpjobportal_job ? esc_html($wpjobportal_job->salarymax) : '', array('class' => 'inputbox sal wjportal-form-input-field','placeholder'=> esc_html(__('e.g 6000','wp-job-portal')),'data-validation' => $wpjobportal_field->validation)),WPJOBPORTAL_ALLOWED_TAGS); ?>
    </div>
    <div class="wjportal-form-inner-fields">
        <?php echo wp_kses(WPJOBPORTALformfield::select('salaryduration', WPJOBPORTALincluder::getJSModel('salaryrangetype')->getSalaryRangeTypesForCombo(), $wpjobportal_job ? $wpjobportal_job->salaryduration : WPJOBPORTALincluder::getJSModel('salaryrangetype')->getDefaultSalaryRangeTypeId(), esc_html(__('Select','wp-job-portal')), array('class' => 'inputbox sal wjportal-form-select-field', 'data-validation' => $wpjobportal_field->validation)),WPJOBPORTAL_ALLOWED_TAGS); ?>
    </div>
</div>
<?php
    wp_register_script( 'wpjobportal-inline-handle', '' );
    wp_enqueue_script( 'wpjobportal-inline-handle' );

    $wpjobportal_inline_js_script = "
        jQuery(document).ready(function ($) {
            jQuery(document).on('change', '#salarytype', function(){
                var salarytype = jQuery(this).val();
                var salarytype_req = jQuery(this).attr('data-validation');
                if(salarytype == 1){ //negotiable
                    jQuery('#salaryfixed').hide().removeAttr('data-validation').closest('.wjportal-form-inner-fields').hide();
                    jQuery('#salarymin').hide().removeAttr('data-validation').closest('.wjportal-form-inner-fields').hide();
                    jQuery('#salarymax').hide().removeAttr('data-validation').closest('.wjportal-form-inner-fields').hide();
                    jQuery('#salaryduration').hide().removeAttr('data-validation').closest('.wjportal-form-inner-fields').hide();
                    jQuery('.wjportal-form-symbol').hide().removeAttr('data-validation').closest('.wjportal-form-inner-fields').hide();
                }else if(salarytype == 2){ //fixed
                    jQuery('#salaryfixed').show().attr('data-validation',salarytype_req).closest('.wjportal-form-inner-fields').show();
                    jQuery('#salarymin').hide().removeAttr('data-validation').closest('.wjportal-form-inner-fields').hide();
                    jQuery('#salarymax').hide().removeAttr('data-validation').closest('.wjportal-form-inner-fields').hide();
                    jQuery('#salaryduration').show().attr('data-validation',salarytype_req).closest('.wjportal-form-inner-fields').show();
                    jQuery('.wjportal-form-symbol').show().attr('data-validation',salarytype_req).closest('.wjportal-form-inner-fields').show();
                }else if(salarytype == 3){ //range
                    jQuery('#salaryfixed').hide().removeAttr('data-validation').closest('.wjportal-form-inner-fields').hide();
                    jQuery('#salarymin').show().attr('data-validation',salarytype_req).closest('.wjportal-form-inner-fields').show();
                    jQuery('#salarymax').show().attr('data-validation',salarytype_req).closest('.wjportal-form-inner-fields').show();
                    jQuery('#salaryduration').show().attr('data-validation',salarytype_req).closest('.wjportal-form-inner-fields').show();
                    jQuery('.wjportal-form-symbol').show().attr('data-validation',salarytype_req).closest('.wjportal-form-inner-fields').show();
                }else{ //not selected
                    jQuery('#salaryfixed').hide().removeAttr('data-validation').closest('.wjportal-form-inner-fields').hide();
                    jQuery('#salarymin').hide().removeAttr('data-validation').closest('.wjportal-form-inner-fields').hide();
                    jQuery('#salarymax').hide().removeAttr('data-validation').closest('.wjportal-form-inner-fields').hide();
                    jQuery('#salaryduration').hide().removeAttr('data-validation').closest('.wjportal-form-inner-fields').hide();
                    jQuery('.wjportal-form-symbol').hide().removeAttr('data-validation').closest('.wjportal-form-inner-fields').hide();
                }
            });

            jQuery('#salarytype').change();
            });

    ";
    wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
?>

