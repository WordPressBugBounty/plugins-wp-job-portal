<?php
/**
 * @param job      job object - optional
 * @param company  company object - optional
 * @param Default Parameters
 */
if (!defined('ABSPATH'))
    die('Restricted Access');
if (!isset($wpjobportal_job)) {
    $wpjobportal_job = null;
}
if (!isset($wpjobportal_company)) {
    $wpjobportal_company = null;
}
if (!isset($wpjobportal_fields)) {
    $wpjobportal_fields = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldsOrderingforForm(2);
}
$wpjobportal_formfields = array();
foreach($wpjobportal_fields AS $wpjobportal_field){
    $wpjobportal_content = '';
   switch ($wpjobportal_field->field) {
        case "jobtitle":
            $wpjobportal_content = WPJOBPORTALformfield::text('title', isset($wpjobportal_job->title) ? $wpjobportal_job->title : '', array('class' => 'inputbox wjportal-form-input-field', 'data-validation' => $wpjobportal_field->validation,'placeholder'=> wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->placeholder)));
        break;
        case 'jobcategory':
            $wpjobportal_content = WPJOBPORTALformfield::select('jobcategory', WPJOBPORTALincluder::getJSModel('category')->getCategoryForCombobox(), isset($wpjobportal_job->jobcategory)  ? $wpjobportal_job->jobcategory : WPJOBPORTALincluder::getJSModel('category')->getDefaultCategoryId(), $wpjobportal_field->placeholder, array('class' => 'inputbox wjportal-form-select-field', 'data-validation' => $wpjobportal_field->validation));
        break;
        case 'company':
            $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
            if(!WPJOBPORTALincluder::getObjectClass('user')->isguest()){
                if (is_admin()) {
                    $wpjobportal_content = WPJOBPORTALformfield::select('companyid', WPJOBPORTALincluder::getJSModel('company')->getCompaniesForCombo(), isset($wpjobportal_job->companyid) ? $wpjobportal_job->companyid : 0, esc_html(__('Select','wp-job-portal')) .' '. esc_html(__('Company','wp-job-portal')), array('class' => 'inputbox wjportal-form-select-field', 'data-validation' => $wpjobportal_field->validation));
                } else {
                    if(in_array('multicompany',wpjobportal::$_active_addons)){
                       if(WPJOBPORTALincluder::getObjectClass('user')->isemployer()){
                            if (WPJOBPORTALincluder::getJSModel('company')->employerHaveCompany($wpjobportal_uid)) {
                                $wpjobportal_content = WPJOBPORTALformfield::select('companyid', WPJOBPORTALincluder::getJSModel('company')->getCompanyForCombo($wpjobportal_uid), isset($wpjobportal_job->companyid) ? $wpjobportal_job->companyid : '', esc_html(__('Select','wp-job-portal')) .' '. esc_html(__('Company', 'wp-job-portal')), array('class' => 'inputbox wjportal-form-select-field', 'onchange' => 'getdepartments(\'departmentid\', this.value);', 'data-validation' => $wpjobportal_field->validation));
                            } else {
                                $wpjobportal_content = '<a href="'.esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'addcompany'))).'">' . esc_html(__('Add','wp-job-portal')).' '. esc_html(__('Company', 'wp-job-portal')) . '</a><input type="hidden" name="companyid" id="companyid" data-validation="required" />';
                            }
                        }
                    }else{
                        $wpjobportal_company = WPJOBPORTALincluder::getJSModel('company')->getSingleCompanyByUid($wpjobportal_uid);
                        if(isset($wpjobportal_company->id)){
                            $wpjobportal_companyname = isset($wpjobportal_job->companyid) ? WPJOBPORTALincluder::getJSModel('company')->getCompanynameById($wpjobportal_job->companyid): $wpjobportal_company->name;
                            $wpjobportal_companyid = isset($wpjobportal_job->companyid) ? $wpjobportal_job->companyid : $wpjobportal_company->id;
                         $wpjobportal_content = "<div class='wjportal-form-text'>".$wpjobportal_companyname ." </div>";
                            WPJOBPORTALformfield::hidden('companyid',$wpjobportal_companyid);
                        }else{
                              $wpjobportal_content = '<a href="'.esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'addcompany'))).'">' . esc_html(__('Add','wp-job-portal')).' '. esc_html(__('Company', 'wp-job-portal')) . '</a><input type="hidden" name="companyid" id="companyid" data-validation="required" />';
                        }
                        
                    }
                }
            }
        break;
        case 'jobshift':
            $wpjobportal_content = WPJOBPORTALformfield::select('shift', WPJOBPORTALincluder::getJSModel('shift')->getShiftForCombo(),  isset($wpjobportal_job->shift) ? $wpjobportal_job->shift : '', '', array('class' => 'inputbox one wjportal-form-select-field', $wpjobportal_field->validation));
        break;
        case 'heighesteducation':
            $wpjobportal_content = "<div class='wjportal-form-2-fields'>";
            $wpjobportal_content .= "<div class='wjportal-form-inner-fields'>";
            $wpjobportal_content .= WPJOBPORTALformfield::select('educationid', WPJOBPORTALincluder::getJSModel('highesteducation')->getHighestEducationForCombo(), $wpjobportal_job ? $wpjobportal_job->educationid : WPJOBPORTALincluder::getJSModel('highesteducation')->getDefaultEducationId(), $wpjobportal_field->placeholder, array('class' => 'inputbox wjportal-form-select-field', 'data-validation' => $wpjobportal_field->validation));
            $wpjobportal_content .= "</div>";
            $wpjobportal_content .= "<div class='wjportal-form-inner-fields'>";
            $wpjobportal_content .= WPJOBPORTALformfield::text('degreetitle', $wpjobportal_job ? $wpjobportal_job->degreetitle : '', array('class' => 'inputbox wjportal-form-input-field'));
            $wpjobportal_content .= "</div>";
            $wpjobportal_content .= "</div>";
        break;
        case 'experience':
            $wpjobportal_content = WPJOBPORTALformfield::text('experience', $wpjobportal_job ? $wpjobportal_job->experience : '', array('class' => 'inputbox wjportal-form-input-field','placeholder'=> wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->placeholder)));
        break;
        case 'map':
            if(in_array('addressdata', wpjobportal::$_active_addons)){
               $wpjobportal_content = apply_filters('wpjobportal_credit_addons_map_load_for_jobform',false,$wpjobportal_field,$wpjobportal_job);
            }
            break;
        case 'jobsalaryrange':
            $wpjobportal_content = WPJOBPORTALincluder::getTemplateHtml('job/salary-field', array('class' => 'inputbox wjportal-form-select-field','wpjobportal_field' => $wpjobportal_field, 'wpjobportal_job' => $wpjobportal_job));
        break;
        case 'stoppublishing':
            $wpjobportal_content = WPJOBPORTALformfield::text('stoppublishing', isset($wpjobportal_job->stoppublishing) ? $wpjobportal_job->stoppublishing  : '', array('class' => 'custom_date one wjportal-form-date-field', $wpjobportal_field->validation));
        break;
        case 'metadescription':
            $wpjobportal_content = WPJOBPORTALformfield::editor('metadescription', isset($wpjobportal_job->metadescription) ? $wpjobportal_job->metadescription : '', array('class' => 'inputbox one wjportal-form-textarea-field', 'rows' => '7', 'cols' => '94', $wpjobportal_field->validation));
          break;
        case 'department':
            $wpjobportal_content = apply_filters('wpjobportal_addons_get_department',false,$wpjobportal_job,$wpjobportal_field);
        break;
        case 'jobtype':
            $wpjobportal_content = WPJOBPORTALformfield::select('jobtype', WPJOBPORTALincluder::getJSModel('jobtype')->getJobTypeForCombo(), isset($wpjobportal_job->jobtype) ? $wpjobportal_job->jobtype : WPJOBPORTALincluder::getJSModel('jobtype')->getDefaultJobTypeId(), $wpjobportal_field->placeholder, array('class' => 'inputbox wjportal-form-select-field', 'data-validation' => $wpjobportal_field->validation));
            break;
        case 'noofjobs':
            $wpjobportal_content = WPJOBPORTALformfield::text('noofjobs', isset($wpjobportal_job->noofjobs) ? $wpjobportal_job->noofjobs : '', array('class' => 'inputbox one wjportal-form-input-field', 'data-validation' => $wpjobportal_field->validation,'placeholder'=> wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->placeholder)));
            break;
        case 'jobstatus':
            $wpjobportal_content = WPJOBPORTALformfield::select('jobstatus', WPJOBPORTALincluder::getJSModel('jobstatus')->getJobStatusForCombo(), isset($wpjobportal_job->jobstatus) ? $wpjobportal_job->jobstatus : WPJOBPORTALincluder::getJSModel('jobstatus')->getDefaultJobStatusId(), $wpjobportal_field->placeholder, array('class' => 'inputbox wjportal-form-select-field', 'data-validation' => $wpjobportal_field->validation));
            break;
        case 'duration':
            $wpjobportal_content = WPJOBPORTALformfield::text('duration', isset($wpjobportal_job->duration) ? $wpjobportal_job->duration : '', array('class' => 'inputbox wjportal-form-input-field', 'data-validation' => $wpjobportal_field->validation,'placeholder'=> wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->placeholder)));
            break;
        case 'description':
            $wpjobportal_content = WPJOBPORTALformfield::editor('description', isset($wpjobportal_job->description) ? $wpjobportal_job->description : '', array('class' => 'inputbox one wjportal-form-textarea-field'));
            break;
        case 'careerlevel':
            $wpjobportal_content = WPJOBPORTALformfield::select('careerlevel', WPJOBPORTALincluder::getJSModel('careerlevel')->getCareerLevelsForCombo(), isset($wpjobportal_job->careerlevel) ? $wpjobportal_job->careerlevel : WPJOBPORTALincluder::getJSModel('careerlevel')->getDefaultCareerlevelId(), $wpjobportal_field->placeholder, array('class' => 'inputbox wjportal-form-select-field', 'data-validation' => $wpjobportal_field->validation));
            break;
        case 'city':
            $wpjobportal_content = WPJOBPORTALformfield::text('city', isset($wpjobportal_job->city) ? $wpjobportal_job->city : '', array('class' => 'inputbox', 'data-validation' => $wpjobportal_field->validation));
            break;
        case 'tags':
          if(in_array('tag',wpjobportal::$_active_addons)){
                $wpjobportal_content = apply_filters('wpjobportal_credit_job_input_for_tagline',false,$wpjobportal_field,$wpjobportal_job) ;
            }
            break;
        case 'emailsetting':
            if(in_array('email', wpjobportal::$_active_addons)){
                $wpjobportal_content = apply_filters('wpjobportal_credit_job_input_for_email_filter',false,$wpjobportal_job,$wpjobportal_field) ;
            }
            break;
        case 'metakeywords':
            $wpjobportal_content = WPJOBPORTALformfield::editor('metakeywords', isset($wpjobportal_job->metakeywords) ? $wpjobportal_job->metakeywords : '', array('class' => 'inputbox one wjportal-form-textarea-field', 'rows' => '7', 'cols' => '94', $wpjobportal_field->validation));
            break;
        case 'metadescription':
            $wpjobportal_content = WPJOBPORTALformfield::textarea('metakeywords', isset($wpjobportal_job->metakeywords) ? $wpjobportal_job->metakeywords : '', array('class' => 'inputbox one wjportal-form-textarea-field', 'rows' => '7', 'cols' => '94', $wpjobportal_field->validation));
            break;
        case 'termsandconditions':
            if(!isset($wpjobportal_job)){
                $wpjobportal_termsandconditions_flag = 1;
                $wpjobportal_termsandconditions_fieldtitle = $wpjobportal_field->fieldtitle;
                $wpjobportal_content = get_the_permalink(wpjobportal::$_configuration['terms_and_conditions_page_job']);
            }
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
