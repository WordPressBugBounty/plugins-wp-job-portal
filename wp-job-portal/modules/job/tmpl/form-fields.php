<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param job      job object - optional
 * @param company  company object - optional
 * @param Default Parameters
 */
if (!isset($wpjobportal_job)) {
    $wpjobportal_job = null;
}
if (!isset($wpjobportal_company)) {
    $wpjobportal_company = null;
}
if (!isset($wpjobportal_fields)) {
    $wpjobportal_fields = wpjobportal::$_wpjpfieldordering->getFieldsOrderingforForm(2);
}
$wpjobportal_formfields = array();
foreach($wpjobportal_fields AS $wpjobportal_field){
    // W-10: search engine fields belong to the admin, not the employer's job form
    if (in_array($wpjobportal_field->field, array('metadescription', 'metakeywords'), true) && !wpjobportal::$_common->wpjp_isadmin()) {
        continue;
    }
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
                if (wpjobportal::$_common->wpjp_isadmin()) {
                    // The list holds approved companies only. A job whose company is
                    // still waiting (or was rejected) showed "Select Company" and lost
                    // its company on the next save; its own company is always offered.
                    $wpjobportal_companies = (array) WPJOBPORTALincluder::getJSModel('company')->getCompaniesForCombo();
                    $wpjobportal_own_company = isset($wpjobportal_job->companyid) ? (int) $wpjobportal_job->companyid : 0;
                    if ($wpjobportal_own_company > 0 && !in_array($wpjobportal_own_company, array_map('intval', wp_list_pluck($wpjobportal_companies, 'id')), true)) {
                        $wpjobportal_own = wpjobportal::$_db->get_row('SELECT id, name, status FROM `' . wpjobportal::$_db->prefix . 'wj_portal_companies` WHERE id = ' . $wpjobportal_own_company);
                        if ($wpjobportal_own) {
                            $wpjobportal_own_state = (int) $wpjobportal_own->status === 0 ? __('waiting for approval', 'wp-job-portal') : __('not approved', 'wp-job-portal');
                            array_unshift($wpjobportal_companies, (object) array('id' => (int) $wpjobportal_own->id, 'text' => $wpjobportal_own->name . ' (' . $wpjobportal_own_state . ')'));
                        }
                    }
                    $wpjobportal_content = WPJOBPORTALformfield::select('companyid', $wpjobportal_companies, isset($wpjobportal_job->companyid) ? $wpjobportal_job->companyid : 0, esc_html(__('Select','wp-job-portal')) .' '. esc_html(__('Company','wp-job-portal')), array('class' => 'inputbox wjportal-form-select-field', 'data-validation' => $wpjobportal_field->validation));
                } else {
                    if(in_array('multicompany',wpjobportal::$_active_addons)){
                       if(WPJOBPORTALincluder::getObjectClass('user')->isemployer()){
                            if (WPJOBPORTALincluder::getJSModel('company')->employerHaveCompany($wpjobportal_uid)) {
                                // W-3: an employer with one company has nothing to choose - it is selected.
                                $wpjobportal_my_companies = (array) WPJOBPORTALincluder::getJSModel('company')->getCompanyForCombo($wpjobportal_uid);
                                $wpjobportal_company_pick = isset($wpjobportal_job->companyid) ? $wpjobportal_job->companyid : (count($wpjobportal_my_companies) === 1 ? $wpjobportal_my_companies[0]->id : '');
                                $wpjobportal_content = WPJOBPORTALformfield::select('companyid', $wpjobportal_my_companies, $wpjobportal_company_pick, esc_html(__('Select','wp-job-portal')) .' '. esc_html(__('Company', 'wp-job-portal')), array('class' => 'inputbox wjportal-form-select-field', 'onchange' => 'getdepartments(\'departmentid\', this.value);', 'data-validation' => $wpjobportal_field->validation));
                            } else {
                                $wpjobportal_content = '<div class="wjportal-form-needs-company">'
                                    . '<p>' . esc_html(__('A job belongs to a company, and you have not added one yet. Add your company first, then come back and post the job.', 'wp-job-portal')) . '</p>'
                                    . '<a class="wjportal-form-add-comp" href="'.esc_url(WPJOBPORTAL_Employer_Start::add_company_url()).'">' . esc_html(__('Add','wp-job-portal')).' '. esc_html(__('Company', 'wp-job-portal')) . '</a>'
                                    . '</div>'
                                    /*
                                     * The field was a hidden input carrying
                                     * data-validation="required" and no value it
                                     * could ever be given. Pressing Save Job did
                                     * nothing at all: the validator refused the
                                     * form and hung its complaint on something
                                     * nobody can see. The requirement is real -
                                     * storeJob() enforces it now - but it is a
                                     * sentence to read, not an invisible trap.
                                     */
                                    . '<input type="hidden" name="companyid" id="companyid" value="" />';
                            }
                        }
                    }else{
                        $wpjobportal_company = WPJOBPORTALincluder::getJSModel('company')->getSingleCompanyByUid($wpjobportal_uid);
                        if(isset($wpjobportal_company->id)){
                            $wpjobportal_companyname = isset($wpjobportal_job->companyid) ? WPJOBPORTALincluder::getJSModel('company')->getCompanynameById($wpjobportal_job->companyid): $wpjobportal_company->name;
                            $wpjobportal_companyid = isset($wpjobportal_job->companyid) ? $wpjobportal_job->companyid : $wpjobportal_company->id;
                         $wpjobportal_content = "<div class='wjportal-form-text'>".$wpjobportal_companyname ." </div>";
                            $wpjobportal_content .= WPJOBPORTALformfield::hidden('companyid',$wpjobportal_companyid);
                        }else{
                              $wpjobportal_content = '<div class="wjportal-form-needs-company">'
                                    . '<p>' . esc_html(__('A job belongs to a company, and you have not added one yet. Add your company first, then come back and post the job.', 'wp-job-portal')) . '</p>'
                                    . '<a class="wjportal-form-add-comp" href="'.esc_url(WPJOBPORTAL_Employer_Start::add_company_url()).'">' . esc_html(__('Add','wp-job-portal')).' '. esc_html(__('Company', 'wp-job-portal')) . '</a>'
                                    . '</div>'
                                    /*
                                     * The field was a hidden input carrying
                                     * data-validation="required" and no value it
                                     * could ever be given. Pressing Save Job did
                                     * nothing at all: the validator refused the
                                     * form and hung its complaint on something
                                     * nobody can see. The requirement is real -
                                     * storeJob() enforces it now - but it is a
                                     * sentence to read, not an invisible trap.
                                     */
                                    . '<input type="hidden" name="companyid" id="companyid" value="" />';
                        }

                    }
                }
            }
        break;
        case 'heighesteducation':
            $wpjobportal_content = "<div class='wjportal-form-2-fields'>";
            $wpjobportal_content .= "<div class='wjportal-form-inner-fields'>";
            $wpjobportal_content .= WPJOBPORTALformfield::select('educationid', WPJOBPORTALincluder::getJSModel('highesteducation')->getHighestEducationForCombo(), $wpjobportal_job ? $wpjobportal_job->educationid : WPJOBPORTALincluder::getJSModel('highesteducation')->getDefaultEducationId(), $wpjobportal_field->placeholder, array('class' => 'inputbox wjportal-form-select-field', 'data-validation' => $wpjobportal_field->validation));
            $wpjobportal_content .= "</div>";
            $wpjobportal_content .= "<div class='wjportal-form-inner-fields'>";
            $wpjobportal_content .= WPJOBPORTALformfield::text('degreetitle', $wpjobportal_job ? $wpjobportal_job->degreetitle : '', array('class' => 'inputbox wjportal-form-input-field','data-validation' => $wpjobportal_field->validation));
            $wpjobportal_content .= "</div>";
            $wpjobportal_content .= "</div>";
        break;
        case 'experience':
            $wpjobportal_content = WPJOBPORTALformfield::text('experience', $wpjobportal_job ? $wpjobportal_job->experience : '', array('class' => 'inputbox wjportal-form-input-field', 'data-validation' => $wpjobportal_field->validation,'placeholder'=> wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->placeholder)));
        break;
        case 'map':
            // Provided by Pro, or by the legacy add-on where it is still
            // installed. Nothing renders when neither is present.
            $wpjobportal_content = apply_filters('wpjobportal_credit_addons_map_load_for_jobform',false,$wpjobportal_field,$wpjobportal_job);
            break;
        case 'jobsalaryrange':
            $wpjobportal_content = WPJOBPORTALincluder::getTemplateHtml('job/salary-field', array('class' => 'inputbox wjportal-form-select-field','wpjobportal_field' => $wpjobportal_field, 'wpjobportal_job' => $wpjobportal_job));
        break;
        case 'stoppublishing':
            if($wpjobportal_field->required == 1) {
                $wpjobportal_required = "required";
            }else{
                $wpjobportal_required = '';
            }
            $wpjobportal_content = WPJOBPORTALformfield::text('stoppublishing', isset($wpjobportal_job->stoppublishing) ?  gmdate(wpjobportal::$_config->getConfigValue('date_format'), strtotime($wpjobportal_job->stoppublishing))  : '', array('class' => 'custom_date one wjportal-form-date-field','placeholder'=>wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->placeholder),'data-validation'=>$wpjobportal_required,'autocomplete'=>'off'));

        break;
        case 'metadescription':
            $wpjobportal_content = WPJOBPORTALformfield::textarea('metadescription', isset($wpjobportal_job->metadescription) ? $wpjobportal_job->metadescription : '', array('class' => 'inputbox one wjportal-form-textarea-field', 'rows' => '7', 'cols' => '94', $wpjobportal_field->validation));
        break;
        case 'department':
            // do not show department field for visitor add job form
            if(WPJOBPORTALincluder::getObjectClass('user')->isguest()){
                break;
            }
            $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
            $wpjobportal_company = WPJOBPORTALincluder::getJSModel('company')->getSingleCompanyByUid($wpjobportal_uid);
            if(isset($wpjobportal_company) && !empty($wpjobportal_company->uid)){
                $wpjobportal_id = $wpjobportal_company->uid;
            }else{
                $wpjobportal_id = '' ;
            }
            $wpjobportal_content = apply_filters('wpjobportal_addons_get_department',false,$wpjobportal_job,$wpjobportal_field,$wpjobportal_id);
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
            $wpjobportal_api_key = get_option('wpjobportal_zywrap_api_key', '');
            if(!empty($wpjobportal_api_key) && !WPJOBPORTALincluder::getObjectClass('user')->isguest()){
                $wpjobportal_enable_job_copilot  = wpjobportal::$_config->getConfigurationByConfigName('enable_job_copilot');
                if(!empty($wpjobportal_enable_job_copilot) || current_user_can('manage_options')){
                    $wpjobportal_content = WPJOBPORTALincluder::getTemplate('job/views/frontend/content-generation',array(
                        'wpjobportal_desc_editor' => $wpjobportal_content
                    ));
                }
            }

            break;
        case 'careerlevel':
            $wpjobportal_content = WPJOBPORTALformfield::select('careerlevel', WPJOBPORTALincluder::getJSModel('careerlevel')->getCareerLevelsForCombo(), isset($wpjobportal_job->careerlevel) ? $wpjobportal_job->careerlevel : WPJOBPORTALincluder::getJSModel('careerlevel')->getDefaultCareerlevelId(), $wpjobportal_field->placeholder, array('class' => 'inputbox wjportal-form-select-field', 'data-validation' => $wpjobportal_field->validation));
            break;
        case 'city':
            $wpjobportal_content = WPJOBPORTALformfield::text('city', isset($wpjobportal_job->city) ? $wpjobportal_job->city : '', array('class' => 'inputbox wpjobportal-form-input-field wpjobportal-job-form-city-field', 'data-validation' => $wpjobportal_field->validation));
            // Google for Jobs lists a job only with a location or a remote flag: ask
            // for one before the form is sent, so nothing typed is lost (storeJob
            // refuses the same case). The city box is a token picker, which hides
            // the input from the form validator, hence a submit check of its own.
            wp_register_script('wpjobportal-inline-handle', '', array(), WPJOBPORTAL_VERSION, false);
            wp_enqueue_script('wpjobportal-inline-handle');
            wp_add_inline_script('wpjobportal-inline-handle', 'jQuery(function ($) {
                var $city = $("#city"), $form = $city.closest("form"), text = ' . wp_json_encode(__('Add a city, or set Workplace Type to Remote. Google for Jobs lists a job only when it has a location or is marked remote.', 'wp-job-portal')) . ';
                $form.on("submit", function (e) {
                    var $mode = $("#workplace_type");
                    if ($.trim($city.val() || "") !== "" || ($mode.length && $mode.val() === "3")) { return; }
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    var $note = $("#wpjobportal-location-required");
                    if (!$note.length) {
                        $note = $("<div id=\\"wpjobportal-location-required\\" class=\\"help-block form-error wpjobportal-location-required\\" role=\\"alert\\"></div>").insertAfter($city.parent().children().last());
                    }
                    $note.text(text);
                    $("html, body").animate({ scrollTop: $note.offset().top - 140 }, 200);
                });
                $(document).on("change", "#workplace_type, #city", function () { $("#wpjobportal-location-required").remove(); });
            });');
            break;
        case 'tags':
          if(in_array('tag',wpjobportal::$_active_addons)){
                $wpjobportal_content = apply_filters('wpjobportal_credit_job_input_for_tagline',false,$wpjobportal_field,$wpjobportal_job) ;
            }
            break;
        case 'emailsetting':
            if(in_array('jobalert', wpjobportal::$_active_addons)){
                $wpjobportal_content = apply_filters('wpjobportal_credit_job_input_for_email_filter',false,$wpjobportal_job,$wpjobportal_field) ;
            }
            break;
        case 'metakeywords':
            $wpjobportal_content = WPJOBPORTALformfield::textarea('metakeywords', isset($wpjobportal_job->metakeywords) ? $wpjobportal_job->metakeywords : '', array('class' => 'inputbox one wjportal-form-textarea-field', 'rows' => '7', 'cols' => '94', $wpjobportal_field->validation));
            break;
        case 'metadescription':
            $wpjobportal_content = WPJOBPORTALformfield::textarea('metakeywords', isset($wpjobportal_job->metakeywords) ? $wpjobportal_job->metakeywords : '', array('class' => 'inputbox one wjportal-form-textarea-field', 'rows' => '7', 'cols' => '94', $wpjobportal_field->validation));
            break;
        case 'termsandconditions':
            if(!isset($wpjobportal_job)){
                $wpjobportal_termsandconditions_flag = 1;
                $wpjobportal_termsandconditions_fieldtitle = $wpjobportal_field->fieldtitle;
                // $wpjobportal_content = get_the_permalink(wpjobportal::$_configuration['terms_and_conditions_page_job']);
            }
            break;
        case 'joblink':
            $wpjobportal_content = WPJOBPORTALformfield::checkbox("jobapplylink", array('1' => esc_html__("Set Job Apply Redirect Link","wp-job-portal")), isset($wpjobportal_job->jobapplylink) && $wpjobportal_job->jobapplylink == 1 ? 1 : 0);
            $wpjobportal_content .= "<div id='input-text-joblink'>".WPJOBPORTALformfield::text('joblink', isset($wpjobportal_job->joblink) ? $wpjobportal_job->joblink : '', array('class' => 'inputbox one wjportal-form-input-field input-text-joblink', 'data-validation' => $wpjobportal_field->validation,'placeholder'=> wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->placeholder)))."</div>";
        break;
        // --- NEW: Render the Urgent Checkbox ---
        case 'is_urgent':
            if(in_array('joblistingenhancer', wpjobportal::$_active_addons)){
                $wpjobportal_content = WPJOBPORTALformfield::checkbox("is_urgent", array('1' => esc_html__("Yes, mark this job as Urgent", "wp-job-portal")), isset($wpjobportal_job->is_urgent) && $wpjobportal_job->is_urgent == 1 ? 1 : 0);
            }
        break;
        // --- NEW: Render the Workplace Type Dropdown with Default Empty Option ---
        case 'workplace_type':
            $wpjp_workplace_options = array(
                (object) array('id' => '', 'text' => esc_html__('Select One', 'wp-job-portal')),
                (object) array('id' => '1', 'text' => esc_html__('On-site', 'wp-job-portal')),
                (object) array('id' => '2', 'text' => esc_html__('Hybrid', 'wp-job-portal')),
                (object) array('id' => '3', 'text' => esc_html__('Remote', 'wp-job-portal')),
            );
            $wpjobportal_content = WPJOBPORTALformfield::select('workplace_type', $wpjp_workplace_options, isset($wpjobportal_job->workplace_type) ? $wpjobportal_job->workplace_type : '', '', array('class' => 'inputbox wjportal-form-input-field', 'data-validation' => $wpjobportal_field->validation));
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
