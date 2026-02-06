<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
* @param wp-job-portal
* Template Form-Field
*/
?>
<?php
///To Store Array and Return whole Form
$wpjobportal_formfields = array();
foreach ($wpjobportal_fields AS $wpjobportal_field) {
    switch ($wpjobportal_field->field) {
        case 'metakeywords':
            $wpjobportal_content = WPJOBPORTALformfield::text('metakeywords', isset(wpjobportal::$_data[0]['filter']->metakeywords) ? wpjobportal::$_data[0]['filter']->metakeywords : '', array('class' => 'inputbox wjportal-form-input-field','placeholder' => wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->placeholder)));

            break;
        case 'jobtitle':
            $wpjobportal_content = WPJOBPORTALformfield::text('jobtitle', isset(wpjobportal::$_data[0]['filter']->jobtitle) ? wpjobportal::$_data[0]['filter']->jobtitle : '', array('class' => 'inputbox wjportal-form-input-field',$wpjobportal_field->placeholder));
            break;
        case 'company':
            $wpjobportal_content = WPJOBPORTALformfield::select('company[]', WPJOBPORTALincluder::getJSModel('company')->getCompaniesForCombo(), isset(wpjobportal::$_data[0]['filter']->company) ? wpjobportal::$_data[0]['filter']->company : '', esc_html(__('Select','wp-job-portal')) .' '. esc_html(__('Company', 'wp-job-portal')), array('class' => 'inputbox wpjobportal-multiselect wjportal-form-select-field', 'multiple' => 'true'));

            break;
        case 'jobcategory':
            $wpjobportal_content = WPJOBPORTALformfield::select('category[]', WPJOBPORTALincluder::getJSModel('category')->getCategoriesForCombo(), isset(wpjobportal::$_data[0]['filter']->category) ? wpjobportal::$_data[0]['filter']->category : '', esc_html(__('Select','wp-job-portal')) .' '. esc_html(__('Category', 'wp-job-portal')), array('class' => 'inputbox wpjobportal-multiselect wjportal-form-select-field', 'multiple' => 'true'));

            break;
        case 'careerlevel':
            $wpjobportal_content = WPJOBPORTALformfield::select('careerlevel[]', WPJOBPORTALincluder::getJSModel('careerlevel')->getCareerLevelsForCombo(), isset(wpjobportal::$_data[0]['filter']->careerlevel) ? wpjobportal::$_data[0]['filter']->careerlevel : '', esc_html(__('Select','wp-job-portal')) .' '. esc_html(__('Career Level', 'wp-job-portal')), array('class' => 'inputbox wpjobportal-multiselect wjportal-form-select-field', 'multiple' => 'true'));

            break;
        case 'gender':
            $wpjobportal_content = WPJOBPORTALformfield::select('gender', WPJOBPORTALincluder::getJSModel('common')->getGender(), isset(wpjobportal::$_data[0]['filter']->gender) ? wpjobportal::$_data[0]['filter']->gender : '', esc_html(__('Select','wp-job-portal')) .' '. esc_html(__('Gender', 'wp-job-portal')), array('class' => 'inputbox wjportal-form-select-field'));

            break;
        case 'jobtype':
            $wpjobportal_content = WPJOBPORTALformfield::select('jobtype[]', WPJOBPORTALincluder::getJSModel('jobtype')->getJobTypeForCombo(), isset(wpjobportal::$_data[0]['filter']->jobtype) ? wpjobportal::$_data[0]['filter']->jobtype : '', esc_html(__('Select','wp-job-portal')) .' '. esc_html(__('Job Type', 'wp-job-portal')), array('class' => 'inputbox wpjobportal-multiselect wjportal-form-select-field', 'multiple' => 'true'));

            break;
        case 'jobstatus':
            $wpjobportal_content = WPJOBPORTALformfield::select('jobstatus[]', WPJOBPORTALincluder::getJSModel('jobstatus')->getJobStatusForCombo(), isset(wpjobportal::$_data[0]['filter']->jobstatus) ? wpjobportal::$_data[0]['filter']->jobstatus : '', esc_html(__('Select','wp-job-portal')) .' '. esc_html(__('Job Status', 'wp-job-portal')), array('class' => 'inputbox wpjobportal-multiselect wjportal-form-select-field', 'multiple' => 'true'));

            break;

        case 'city':
            $wpjobportal_content = WPJOBPORTALformfield::text('city', isset(wpjobportal::$_data[0]['filter']->city) ? wpjobportal::$_data[0]['filter']->city : '', array('class' => 'inputbox wjportal-form-input-field wpjobportal-job-search-city-field'));

            break;
        case 'duration':
            $wpjobportal_content = WPJOBPORTALformfield::text('duration', isset(wpjobportal::$_data[0]['filter']->duration) ? wpjobportal::$_data[0]['filter']->duration : '', array('class' => 'inputbox wjportal-form-input-field','placeholder' => wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->placeholder)));

            break;

        case 'tags':
            $wpjobportal_content = '';
            if(in_array('tag', wpjobportal::$_active_addons)){
                $wpjobportal_content = WPJOBPORTALformfield::text('tags', '', array('class' => 'inputbox wjportal-form-input-field','placeholder' => esc_html(__('Tags','wp-job-portal'))));
            }
            break;
        case 'jobsalaryrange':
            $wpjobportal_content = WPJOBPORTALincluder::getTemplateHtml('jobsearch/salary-field', array('class' => 'inputbox wjportal-form-select-field','wpjobportal_field' => $wpjobportal_field));
        break;
        case 'heighesteducation':
            $wpjobportal_content = WPJOBPORTALformfield::select('educationid[]', WPJOBPORTALincluder::getJSModel('highesteducation')->getHighestEducationForCombo(), isset(wpjobportal::$_data[0]['filter']->educationid) ? wpjobportal::$_data[0]['filter']->educationid : '', esc_html(__('Select','wp-job-portal')) .' '. esc_html(__('Education', 'wp-job-portal')), array('class' => 'inputbox wpjobportal-multiselect wjportal-form-select-field', 'multiple' => 'true'));
        break;
        default:
            $wpjobportal_i = 0;
            $wpjobportal_content = wpjobportal::$_wpjpcustomfield->formCustomFieldsForSearch($wpjobportal_field, $wpjobportal_i, 'f_jobsearch');
            break;
    }

    if (!empty($wpjobportal_content)) {
        $wpjobportal_formfields[] = array(
            'wpjobportal_field' => $wpjobportal_field,
            'wpjobportal_content' => $wpjobportal_content,
            'wpjobportal_require' => "no"
        );
    }
}
return $wpjobportal_formfields;

?>
