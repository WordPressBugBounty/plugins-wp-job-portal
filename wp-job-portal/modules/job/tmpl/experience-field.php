<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
*
*/
?>
<?php
$wpjobportal_req = '';
if ($wpjobportal_field->required == 1) {
    $wpjobportal_req = 'required';
}
$wpjobportal_lists['experienceminimax'] = WPJOBPORTALformfield::select('experienceminimax', WPJOBPORTALincluder::getJSModel('common')->getMiniMax(''), isset(wpjobportal::$_data[0]->experienceminimax) ? wpjobportal::$_data[0]->experienceminimax : 0, '', array('class' => 'inputbox two', 'data-validation' => $wpjobportal_req));
$wpjobportal_lists['experienceid'] = WPJOBPORTALformfield::select('experienceid', WPJOBPORTALincluder::getJSModel('experience')->getExperiencesForCombo(), isset(wpjobportal::$_data[0]->experienceid) ? wpjobportal::$_data[0]->experienceid : $wpjobportal_defaultExperiences, '', array('class' => 'inputbox two', 'data-validation' => $wpjobportal_req));
$wpjobportal_lists['minexperiencerange'] = WPJOBPORTALformfield::select('minexperiencerange', WPJOBPORTALincluder::getJSModel('experience')->getExperiencesForCombo(), isset(wpjobportal::$_data[0]->minexperiencerange) ? wpjobportal::$_data[0]->minexperiencerange : WPJOBPORTALincluder::getJSModel('experience')->getDefaultExperienceId(), esc_html(__('Minimum', 'wp-job-portal')), array('class' => 'inputbox two', 'data-validation' => $wpjobportal_req));
$wpjobportal_lists['maxexperiencerange'] = WPJOBPORTALformfield::select('maxexperiencerange', WPJOBPORTALincluder::getJSModel('experience')->getExperiencesForCombo(), isset(wpjobportal::$_data[0]->maxexperiencerange) ? wpjobportal::$_data[0]->maxexperiencerange : WPJOBPORTALincluder::getJSModel('experience')->getDefaultExperienceId(), esc_html(__('Maximum', 'wp-job-portal')), array('class' => 'inputbox two', 'data-validation' => $wpjobportal_req));
?>
<?php
if (isset(wpjobportal::$_data[0]->id))
    $wpjobportal_isexperienceminimax = wpjobportal::$_data[0]->isexperienceminimax;
else
    $wpjobportal_isexperienceminimax = 1;
if ($wpjobportal_isexperienceminimax == 1) {
    $wpjobportal_minimaxExp = "display:block;";
    $wpjobportal_rangeExp = "display:none;";
} else {
    $wpjobportal_minimaxExp = "display:none;";
    $wpjobportal_rangeExp = "display:block;";
}
echo wp_kses(WPJOBPORTALformfield::hidden('isexperienceminimax', $wpjobportal_isexperienceminimax),WPJOBPORTAL_ALLOWED_TAGS);
?>
<div class="js-field-wrapper js-row no-margin">
    <div class="js-field-title js-col-lg-3 js-col-md-3 js-col-xs-12 no-padding"><?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle)); ?><?php if ($wpjobportal_req != '') { ?><font class="required-notifier">*</font><?php } ?></div>
    <div id="defaultExp" class="js-field-obj js-col-lg-9 js-col-md-9 js-col-xs-12 no-padding" style="<?php echo esc_attr($wpjobportal_minimaxExp); ?>"><?php echo esc_html($wpjobportal_lists['experienceminimax']); ?><?php echo esc_html($wpjobportal_lists['experienceid']); ?></div>
    <div id="expRanges" class="js-field-obj js-col-lg-9 js-col-md-9 js-col-xs-12 no-padding" style="<?php echo esc_attr($wpjobportal_rangeExp); ?>"><?php echo esc_html($wpjobportal_lists['minexperiencerange']); ?><?php echo esc_html($wpjobportal_lists['maxexperiencerange']); ?></div>
    <div id="defaultExpShow" class="js-field-obj js-col-lg-2 js-col-md-2 js-col-xs-12 no-padding" style="<?php echo esc_attr($wpjobportal_minimaxExp); ?>"><a class="show-hide-link" onclick="hideShowRange('defaultExp', 'expRanges', 'defaultExpShow', 'hideExpRanges', 'isexperienceminimax', 0);"><?php echo esc_html(__('Specify range', 'wp-job-portal')); ?></a></div>
    <div id="hideExpRanges" class="js-field-obj js-col-lg-2 js-col-md-2 js-col-xs-12 no-padding" style="<?php echo esc_attr($wpjobportal_rangeExp); ?>"><a class="show-hide-link" onclick="hideShowRange('expRanges', 'defaultExp', 'defaultExpShow', 'hideExpRanges', 'isexperienceminimax', 1);"><?php echo esc_html(__('Cancel range', 'wp-job-portal')); ?></a></div>
    <div class="js-field-obj js-col-lg-9 js-col-md-9 js-col-lg-offset-3 js-col-xs-12 js-col-md-offset-3 no-padding"><?php echo wp_kses(WPJOBPORTALformfield::text('experiencetext', isset(wpjobportal::$_data[0]->experiencetext),WPJOBPORTAL_ALLOWED_TAGS) ? esc_html(wpjobportal::$_data[0]->experiencetext) : '', array('class' => 'inputbox one', 'data-validation' => $wpjobportal_req)) . esc_html(__('If Any Other Experience', 'wp-job-portal')); ?></div>
            </div>