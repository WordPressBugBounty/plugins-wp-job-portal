<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
* @param field    salary field object
* @param job      job object - optional
*/
$salarytypelist = array(
    (object) array('id'=>WPJOBPORTAL_SALARY_NEGOTIABLE,'text'=>esc_html(__("Negotiable",'wp-job-portal'))),
    (object) array('id'=>WPJOBPORTAL_SALARY_FIXED,'text'=>esc_html(__("Fixed",'wp-job-portal'))),
    (object) array('id'=>WPJOBPORTAL_SALARY_RANGE,'text'=>esc_html(__("Range",'wp-job-portal'))),
);
?>
<div class="wjportal-form-5-fields">
    <div class="wjportal-form-inner-fields">
        <?php echo wp_kses(WPJOBPORTALformfield::select('salarytype', $salarytypelist, $job ? esc_html($job->salarytype) : 2, esc_html(__("Select",'wp-job-portal')).' '.esc_html(__("Salary Type",'wp-job-portal')), array('class' => 'inputbox sal wjportal-form-select-field', 'data-validation' => $field->validation)),WPJOBPORTAL_ALLOWED_TAGS); ?>
    </div>
    <div class="wjportal-form-inner-fields wjportal-form-symbol-fields">
        <span class="wjportal-form-symbol"><?php echo isset($job->currency) ? esc_html($job->currency) : esc_html(wpjobportal::$_config->getConfigValue('job_currency')); ?></span>
    </div>
    <div class="wjportal-form-inner-fields">
        <?php echo wp_kses(WPJOBPORTALformfield::text('salaryfixed', $job ? esc_html($job->salarymin) : '', array('class' => 'inputbox sal wjportal-form-input-field','placeholder'=> esc_html(__('e.g 45000','wp-job-portal')),'data-validation' => $field->validation)),WPJOBPORTAL_ALLOWED_TAGS); ?>
    </div>
    <div class="wjportal-form-inner-fields">
        <?php echo wp_kses(WPJOBPORTALformfield::text('salarymin', $job ? esc_html($job->salarymin) : '', array('class' => 'inputbox sal wjportal-form-input-field','placeholder'=> esc_html(__('e.g 3000','wp-job-portal')),'data-validation' => $field->validation)),WPJOBPORTAL_ALLOWED_TAGS); ?>
    </div>
    <div class="wjportal-form-inner-fields">
        <?php echo wp_kses(WPJOBPORTALformfield::text('salarymax', $job ? esc_html($job->salarymax) : '', array('class' => 'inputbox sal wjportal-form-input-field','placeholder'=> esc_html(__('e.g 6000','wp-job-portal')),'data-validation' => $field->validation)),WPJOBPORTAL_ALLOWED_TAGS); ?>
    </div>
    <div class="wjportal-form-inner-fields">
        <?php echo wp_kses(WPJOBPORTALformfield::select('salaryduration', WPJOBPORTALincluder::getJSModel('salaryrangetype')->getSalaryRangeTypesForCombo(), $job ? $job->salaryduration : WPJOBPORTALincluder::getJSModel('salaryrangetype')->getDefaultSalaryRangeTypeId(), esc_html(__('Select','wp-job-portal')), array('class' => 'inputbox sal wjportal-form-select-field', 'data-validation' => $field->validation)),WPJOBPORTAL_ALLOWED_TAGS); ?>
    </div>
</div>
<?php
    wp_register_script( 'wpjobportal-inline-handle', '' );
    wp_enqueue_script( 'wpjobportal-inline-handle' );

    $inline_js_script = "
        jQuery(document).ready(function ($) {
            jQuery(document).delegate('#salarytype', 'change', function(){
                var salarytype = jQuery(this).val();
                if(salarytype == 1){ //negotiable
                    jQuery('#salaryfixed').hide();
                    jQuery('#salarymin').hide();
                    jQuery('#salarymax').hide();
                    jQuery('#salaryduration').hide();
                    jQuery('.wjportal-form-symbol').hide();
                }else if(salarytype == 2){ //fixed
                    jQuery('#salaryfixed').show();
                    jQuery('#salarymin').hide();
                    jQuery('#salarymax').hide();
                    jQuery('#salaryduration').show();
                    jQuery('.wjportal-form-symbol').show();
                }else if(salarytype == 3){ //range
                    jQuery('#salaryfixed').hide();
                    jQuery('#salarymin').show();
                    jQuery('#salarymax').show();
                    jQuery('#salaryduration').show();
                    jQuery('.wjportal-form-symbol').show();
                }else{ //not selected
                    jQuery('#salaryfixed').hide();
                    jQuery('#salarymin').hide();
                    jQuery('#salarymax').hide();
                    jQuery('#salaryduration').hide();
                    jQuery('.wjportal-form-symbol').hide();
                }
            });

            jQuery('#salarytype').change();
            });

    ";
    wp_add_inline_script( 'wpjobportal-inline-handle', $inline_js_script );
?>

