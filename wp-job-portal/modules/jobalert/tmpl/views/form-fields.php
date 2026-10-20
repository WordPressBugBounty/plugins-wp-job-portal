<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param wpjobportal_jobalert      jobalert object - optional
 * @param Default Parameters
 */
if (!isset($wpjobportal_jobalert)) {
    $wpjobportal_jobalert = null;
}

if (!isset($wpjobportal_fields)) {
    // 6 represents the Job Alert form fields in wp_wj_portal_fieldsordering
    $wpjobportal_fields = wpjobportal::$_wpjpfieldordering->getFieldsOrderingforForm(6);
}

// Fetch default info for logged in Job Seeker
if(WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()){
    $wpjobportal_jobinfo = WPJOBPORTALincluder::getObjectClass('user')->getEmployerProfile();
}
if(isset($wpjobportal_jobinfo)){
    $wpjobportal_default_email = $wpjobportal_jobinfo->emailaddress;
    $wpjobportal_default_name = $wpjobportal_jobinfo->first_name;
}else{
    $wpjobportal_default_email = '';
    $wpjobportal_default_name = '';
}

$wpjobportal_formfields = array();

foreach($wpjobportal_fields AS $wpjobportal_field){
    $wpjobportal_content = '';

    switch ($wpjobportal_field->field) {
        case "name":
            $wpjobportal_value = isset($wpjobportal_jobalert->name) ? $wpjobportal_jobalert->name : $wpjobportal_default_name;
            $wpjobportal_content = WPJOBPORTALformfield::text('name', $wpjobportal_value, array('class' => 'inputbox wjportal-form-input-field', 'data-validation' => $wpjobportal_field->validation, 'placeholder'=> wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->placeholder)));
            break;

        case 'contactemail':
            $wpjobportal_value = isset($wpjobportal_jobalert->contactemail) ? $wpjobportal_jobalert->contactemail : $wpjobportal_default_email;
            $wpjobportal_content = WPJOBPORTALformfield::text('contactemail', $wpjobportal_value, array('class' => 'inputbox wjportal-form-input-field', 'data-validation' => $wpjobportal_field->validation, 'placeholder'=> wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->placeholder)));
            break;

        case 'categoryid':
            $wpjobportal_content = WPJOBPORTALformfield::select('categoryid', WPJOBPORTALincluder::getJSModel('category')->getCategoryForCombobox(), isset($wpjobportal_jobalert->categoryid) ? $wpjobportal_jobalert->categoryid : WPJOBPORTALincluder::getJSModel('category')->getDefaultCategoryId(), wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->placeholder), array('class' => 'inputbox wjportal-form-select-field', 'data-validation' => $wpjobportal_field->validation));
            break;

        case 'alerttype':
            $wpjobportal_content = WPJOBPORTALformfield::select('alerttype', WPJOBPORTALincluder::getJSModel('jobalert')->getAlertTypeForCombo(), isset($wpjobportal_jobalert->alerttype) ? $wpjobportal_jobalert->alerttype : '', wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->placeholder), array('class' => 'inputbox wjportal-form-select-field', 'data-validation' => $wpjobportal_field->validation));
            break;

        case 'jobtype':
            $wpjobportal_content = WPJOBPORTALformfield::select('jobtype', WPJOBPORTALincluder::getJSModel('jobtype')->getJobTypeForCombo(), isset($wpjobportal_jobalert->jobtype) ? $wpjobportal_jobalert->jobtype : WPJOBPORTALincluder::getJSModel('jobtype')->getDefaultJobTypeId(), wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->placeholder), array('class' => 'inputbox wjportal-form-select-field', 'data-validation' => $wpjobportal_field->validation));
            break;

        case 'workplace_type':
            $wpjp_workplace_options = array(
                (object) array('id' => '', 'text' => esc_html__('Select One', 'wp-job-portal')),
                (object) array('id' => '1', 'text' => esc_html__('On-site', 'wp-job-portal')),
                (object) array('id' => '2', 'text' => esc_html__('Hybrid', 'wp-job-portal')),
                (object) array('id' => '3', 'text' => esc_html__('Remote', 'wp-job-portal')),
            );
            $wpjobportal_content = WPJOBPORTALformfield::select('workplace_type', $wpjp_workplace_options, isset($wpjobportal_jobalert->workplace_type) ? $wpjobportal_jobalert->workplace_type : '', '', array('class' => 'inputbox wjportal-form-select-field', 'data-validation' => $wpjobportal_field->validation));
            break;

        case 'company':
            $wpjobportal_content = WPJOBPORTALformfield::select('company', WPJOBPORTALincluder::getJSModel('company')->getCompaniesForCombo(), isset($wpjobportal_jobalert->company) ? $wpjobportal_jobalert->company : 0, wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->placeholder), array('class' => 'inputbox wjportal-form-select-field', 'data-validation' => $wpjobportal_field->validation));
            break;

        case 'city':
            // CORRECTED: wpjobportal-form-input-field -> wjportal-form-input-field
            $wpjobportal_content = WPJOBPORTALformfield::text('city', isset($wpjobportal_jobalert->city) ? $wpjobportal_jobalert->city : '', array('class' => 'inputbox wjportal-form-input-field wpjobportal-job-form-city-field', 'id' => 'city', 'data-validation' => $wpjobportal_field->validation));
            break;

        case 'zipcode':
            $wpjobportal_content = WPJOBPORTALformfield::text('zipcode', isset($wpjobportal_jobalert->zipcode) ? $wpjobportal_jobalert->zipcode : '', array('class' => 'inputbox wjportal-form-input-field', 'data-validation' => $wpjobportal_field->validation, 'placeholder'=> wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->placeholder)));
            break;

        case 'keywords':
            $wpjobportal_content = WPJOBPORTALformfield::text('keywords', isset($wpjobportal_jobalert->keywords) ? $wpjobportal_jobalert->keywords : '', array('class' => 'inputbox wjportal-form-input-field', 'data-validation' => $wpjobportal_field->validation, 'placeholder'=> wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->placeholder)));
            break;

        case 'tags':
            if(in_array('tag', wpjobportal::$_active_addons)){
                $wpjobportal_content = apply_filters('wpjobportal_credit_job_input_for_tagline', false, $wpjobportal_field, $wpjobportal_jobalert);
            } else {
                $wpjobportal_content = WPJOBPORTALformfield::text('tags', isset($wpjobportal_jobalert->tags) ? $wpjobportal_jobalert->tags : '', array('class' => 'inputbox wjportal-form-input-field', 'id' => 'tags', 'data-validation' => $wpjobportal_field->validation, 'placeholder'=> wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->placeholder)));
            }
            break;

        case 'is_urgent':
            $wpjobportal_content = WPJOBPORTALformfield::checkbox("is_urgent", array('1' => esc_html__("Yes, mark as Urgent", "wp-job-portal")), isset($wpjobportal_jobalert->is_urgent) && $wpjobportal_jobalert->is_urgent == 1 ? 1 : 0);
            break;

        default:
            $wpjobportal_content = wpjobportal::$_wpjpcustomfield->formCustomFields($wpjobportal_field);
            break;
    }

    if (!empty($wpjobportal_content)) {
        $wpjobportal_formfields[] = array(
            'wpjobportal_field' => $wpjobportal_field,
            'wpjobportal_content' => $wpjobportal_content
        );
    }
}

return $wpjobportal_formfields;