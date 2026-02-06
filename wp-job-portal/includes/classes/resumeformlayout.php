<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALResumeFormlayout {

    public $config_array_sec=array();
    public $resumefields=array();
    public $class_prefix = '';
    public $themecall = 0;
    public $show_terms_and_conditions = 0;
    public $terms_and_conditions_title = '';

    function __construct(){
        $this->config_array_sec = wpjobportal::$_config->getConfigByFor('resume');
        $wpjobportal_fieldsordering = wpjobportal::$_wpjpfieldordering->getFieldsOrderingforForm(3); // resume fields
        $this->resumefields = $wpjobportal_fieldsordering;
        wpjobportal::$_data[2] = array();
        foreach ($wpjobportal_fieldsordering AS $wpjobportal_field) {
            wpjobportal::$wpjobportal_data['fieldtitles'][$wpjobportal_field->field] = $wpjobportal_field->fieldtitle;
            wpjobportal::$_data[2][$wpjobportal_field->section][$wpjobportal_field->field] = $wpjobportal_field;
        }
        if(WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()){
            wpjobportal::$_data['userinfo'] = WPJOBPORTALincluder::getObjectClass('user')->getEmployerProfile();
        }

        if(wpjobportal::$wpjobportal_theme_chk == 2){/// code to manage class prefix for diffrent template cases
            $this->class_prefix = 'jsjb-jh';
            $this->themecall = 2;

        }elseif(wpjobportal::$wpjobportal_theme_chk == 1){
            $this->class_prefix = '';
            $this->themecall = 1;
        }else{
            $this->class_prefix = '';
        }
    }

    function getFieldTitleByField($wpjobportal_field){
        return wpjobportal::wpjobportal_getVariableValue(wpjobportal::$wpjobportal_data['fieldtitles'][$wpjobportal_field]);
    }

    function getResumeFormUserFieldJobManager($title, $wpjobportal_field,$wpjobportal_required) {
        $wpjobportal_html = '<div class="js-col-md-12 js-form-wrapper">
        <div class="js-col-md-12 js-form-title '.esc_attr($this->class_prefix).'-bigfont">' . esc_attr($title);
        if($wpjobportal_required==1){
            $wpjobportal_html .= '<span class="'.esc_attr($this->class_prefix).'-error-msg">*</span>';
        }
        $wpjobportal_html .= '</div>
            <div class="js-col-md-12 js-form-value">' . $wpjobportal_field . '</div>
        </div>';

        return $wpjobportal_html;
    }



    function getResumeFormUserField($wpjobportal_field, $object , $wpjobportal_section , $wpjobportal_sectionid, $wpjobportal_ishidden,$wpjobportal_themecall=null) {
        $wpjobportal_visibleclass = "";
        if (isset($wpjobportal_field->visibleparams) && $wpjobportal_field->visibleparams != ''){
            $wpjobportal_visibleclass = " visible js-form-custm-flds-wrp ";
        }
        $wpjobportal_id = isset($object->id)  ? $object->id : NULL;
        $params = isset($object->params) ? $object->params : NULL;
        $wpjobportal_data = NULL;
        $wpjobportal_result = wpjobportal::$_wpjpcustomfield->formCustomFieldsResume($wpjobportal_field , $wpjobportal_id , $params,null,$wpjobportal_section , $wpjobportal_sectionid, $wpjobportal_ishidden,$wpjobportal_themecall);
        if( isset($wpjobportal_result['value']) && $wpjobportal_result['value'] != null){
            if(null !=$wpjobportal_themecall){
                $wpjobportal_data .= '<div class="resume-row-wrapper form resumefieldswrapper '.esc_attr($wpjobportal_visibleclass).' ">';
                $wpjobportal_data .= '  <label class="resumefieldtitle" for="">';
                $wpjobportal_data .=        wpjobportal::wpjobportal_getVariableValue($wpjobportal_result['title']);
                                if($wpjobportal_field->required == 1){
                $wpjobportal_data .= '          <span class="error-msg">*</span>';
                                }
                $wpjobportal_data .= '  </label>';
                $wpjobportal_data .= '  <div class="resumefieldvalue">';
                $wpjobportal_data .=        $wpjobportal_result['value'];
                        $wpjobportal_description = $wpjobportal_field->description;
                        if(!empty($wpjobportal_description)){
                            $wpjobportal_data .= '<div class="wjportal-form-help-txt">'. esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_description)).'</div>';
                        }
                $wpjobportal_data .= '  </div>
                          </div>';

            }else{
                $wpjobportal_data .= '<div class="resume-row-wrapper form resumefieldswrapper '.esc_attr($wpjobportal_visibleclass).' ">';
                $wpjobportal_data .= '  <label class="resumefieldtitle" for="">';
                $wpjobportal_data .=        wpjobportal::wpjobportal_getVariableValue($wpjobportal_result['title']);
                                if($wpjobportal_field->required == 1){
                $wpjobportal_data .= '          <span class="error-msg">*</span>';
                                }
                $wpjobportal_data .= '  </label>';
                $wpjobportal_data .= '  <div class="resumefieldvalue">';
                $wpjobportal_data .=        $wpjobportal_result['value'];
                        $wpjobportal_description = $wpjobportal_field->description;
                        if(!empty($wpjobportal_description)){
                            $wpjobportal_data .= '<div class="wjportal-form-help-txt">'. esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_description)).'</div>';
                        }
                $wpjobportal_data .= '  </div>
                          </div>';
            }
            return $wpjobportal_data;
        }
        return $wpjobportal_result;
    }


    function getResumeCheckBoxField($wpjobportal_field, $wpjobportal_fieldValue){
        $wpjobportal_fieldtitle = $wpjobportal_field->fieldtitle;
        $wpjobportal_fieldName = $wpjobportal_field->field;
        $wpjobportal_required = $wpjobportal_field->required;

        $wpjobportal_name = 'sec_1['.$wpjobportal_fieldName.']';
        $wpjobportal_data = '
            <div class="resume-row-wrapper form wjportal-form-row">
                <div class="row-title wjportal-form-title">';
                    if ($wpjobportal_required == 1) {
                        $wpjobportal_data .= wpjobportal::wpjobportal_getVariableValue($wpjobportal_fieldtitle) . ' <font color="red"> *</font>';
                        $cssclass = "required";
                    }else {
                        $wpjobportal_data .= wpjobportal::wpjobportal_getVariableValue($wpjobportal_fieldtitle);
                        $cssclass = "";
                    }
                $wpjobportal_data .= '</div>
                <div class="row-value wjportal-form-value">
                    <div class="checkbox-field wpjp-form-value wjportal-searchable-wrp">';
                        $wpjobportal_data .= WPJOBPORTALformfield::checkbox($wpjobportal_name, array('1' => wpjobportal::wpjobportal_getVariableValue($wpjobportal_fieldtitle)), $wpjobportal_fieldValue);
                        $wpjobportal_data .= '
                    </div>
                    ';
                $wpjobportal_description = $wpjobportal_field->description;
                if(!empty($wpjobportal_description)){
                    $wpjobportal_data .= '<div class="wjportal-form-help-txt">'. esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_description)).'</div>';
                }
                $wpjobportal_data .= '
                </div>
            </div>';
        return $wpjobportal_data;
    }

    function getResumeSelectFieldJobManager($wpjobportal_fieldtitle,$wpjobportal_fieldName,$wpjobportal_fieldValue,$wpjobportal_required,$column){
        $wpjobportal_html="";
        if($column==4){
            $wpjobportal_html .= '<div class="js-col-md-3 '.esc_attr($this->class_prefix).'-field-padding">';
        }else{
            $wpjobportal_html .= '<div class="js-col-md-12 js-form-wrapper">';

        }
        $wpjobportal_html .= '
            <div class="js-col-md-12 js-form-title '.esc_attr($this->class_prefix).'-bigfont">' . $wpjobportal_fieldtitle;
            if($wpjobportal_required==1){
                $wpjobportal_html .='<span class="'.esc_attr($this->class_prefix).'-error-msg">*</span>';
            }
            $wpjobportal_html .='</div>
            <div class="js-col-md-12 js-form-value">' . $wpjobportal_fieldValue . '</div>
        </div>';
        return $wpjobportal_html;
    }

    function getResumeSelectField($wpjobportal_field, $wpjobportal_fieldValue,$column=0,$wpjobportal_themecall=null) {

        $wpjobportal_fieldtitle="";
        if(isset($wpjobportal_field->fieldtitle)) $wpjobportal_fieldtitle = $wpjobportal_field->fieldtitle;
        $wpjobportal_fieldName="";
        if(isset($wpjobportal_field->field)) $wpjobportal_fieldName = $wpjobportal_field->field;
        $wpjobportal_required="";
        if(isset($wpjobportal_field->required)) $wpjobportal_required = $wpjobportal_field->required;
        if(null != $wpjobportal_themecall){
            $wpjobportal_data = '
                <div class="wjportal-form-row">
                    <div class="wjportal-form-title">
                        <label " for="' . $wpjobportal_fieldName . '">' . wpjobportal::wpjobportal_getVariableValue($wpjobportal_fieldtitle);
                            if ($wpjobportal_required == 1) {
                                $wpjobportal_data .= '<span class="error-msg" style="color: red;"> *</span>';
                            }
            $wpjobportal_data .= '
                        </label>
                    </div>
                    <div class="wjportal-form-value">
                        ' . $wpjobportal_fieldValue .'
                    </div>
                </div>';
        }else{
            $wpjobportal_data = '
                <div class="wjportal-form-row">
                    <div class="wjportal-form-title">
                        <label " for="' . $wpjobportal_fieldName . '">' . wpjobportal::wpjobportal_getVariableValue($wpjobportal_fieldtitle);
                            if ($wpjobportal_required == 1) {
                                $wpjobportal_data .= '<span class="error-msg" style="color: red;"> *</span>';
                            }
            $wpjobportal_data .= '
                        </label>
                    </div>
                    <div class="wjportal-form-value">
                        ' . $wpjobportal_fieldValue .'
                        ';
                $wpjobportal_description = $wpjobportal_field->description;
                if(!empty($wpjobportal_description)){
                    $wpjobportal_data .= '<div class="wjportal-form-help-txt">'. esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_description)).'</div>';
                }
                $wpjobportal_data .= '
                    </div>
                </div>';
        }
        return $wpjobportal_data;
    }
    function getResumeSectionTitleJobPortal($wpjobportal_sectionid,$title) {

        $wpjobportal_html='<h2 id="jsresume_sectionid'.esc_attr($wpjobportal_sectionid).'" class="wjportal-resume-section-title">' . wpjobportal::wpjobportal_getVariableValue($title) . '</h2>';

        return $wpjobportal_html;
    }

    function getSectionTitle($wpjobportal_sectionFor, $title , $wpjobportal_sectionid,$wpjobportal_themecall) {
        if ($wpjobportal_sectionFor == "education") {
            $wpjobportal_sectionFor = "institute";
        }
        switch ($wpjobportal_sectionFor) {
            case 'personal':
                if(null!=$wpjobportal_themecall){
                    $wpjobportal_html=$this->getResumeSectionTitleJobPortal($wpjobportal_sectionid,$title);
                }else{
                    $wpjobportal_html = '<div id="jsresume_sectionid'.esc_attr($wpjobportal_sectionid).'" class="wjportal-resume-section-title"><strong>' . wpjobportal::wpjobportal_getVariableValue($title) . '</strong></div>';
                }
            break;
            case 'address':
                if(null!=$wpjobportal_themecall){
                    $wpjobportal_html=$this->getResumeSectionTitleJobPortal($wpjobportal_sectionid,$title);
                }else{
                    $wpjobportal_html = '<div id="jsresume_sectionid'.esc_attr($wpjobportal_sectionid).'" class="wjportal-resume-section-title">' . wpjobportal::wpjobportal_getVariableValue($title) . '</div>';
                }

            break;
            case 'institute':
                if(null!=$wpjobportal_themecall){
                    $wpjobportal_html= $this->getResumeSectionTitleJobPortal($wpjobportal_sectionid,$title);
                }else{
                    $wpjobportal_html = apply_filters('wpjobportal_addons_resume_formTitile',false,$wpjobportal_sectionid,$title);
                }

            break;
            case 'employer':
                if(null!=$wpjobportal_themecall){
                    $wpjobportal_html=$this->getResumeSectionTitleJobPortal($wpjobportal_sectionid,$title);
                }else{
                    $wpjobportal_html = '<div id="jsresume_sectionid'.esc_attr($wpjobportal_sectionid).'" class="wjportal-resume-section-title">' . wpjobportal::wpjobportal_getVariableValue($title) . '</div>';
                }

            break;
            case 'skills':
                if(null!=$wpjobportal_themecall){
                    $wpjobportal_html=$this->getResumeSectionTitleJobPortal($wpjobportal_sectionid,$title);
                }else{
                    $wpjobportal_html = apply_filters('wpjobportal_addons_resume_formTitile',false,$wpjobportal_sectionid,$title);
                }

            break;
            case 'language':
                if(null!=$wpjobportal_themecall){
                    $wpjobportal_html=$this->getResumeSectionTitleJobPortal($wpjobportal_sectionid,$title);
                }else{
                    $wpjobportal_html = apply_filters('wpjobportal_addons_resume_formTitile',false,$wpjobportal_sectionid,$title);
                }

            break;
		default:
                if(null!=$wpjobportal_themecall){
                    $wpjobportal_html=$this->getResumeSectionTitleJobPortal($wpjobportal_sectionid,$title);
                }else{
                    $wpjobportal_html = apply_filters('wpjobportal_addons_resume_formTitile',false,$wpjobportal_sectionid,$title);
                }
            break;
        }
        return $wpjobportal_html;
    }

    function getFieldForPersonalSectionJobManager($wpjobportal_fieldtitle,$wpjobportal_fieldName,$wpjobportal_fieldValue,$wpjobportal_required,$wpjobportal_extraattr,$columns = 0){

        $wpjobportal_data="";

        if($columns == 3){
            $wpjobportal_data .= '<div class="js-col-md-4 '.esc_attr($this->class_prefix).'-field-padding">';
        }else{
            $wpjobportal_data .= '<div class="js-col-md-12 js-form-wrapper">';
        }
        $wpjobportal_data .= '
            <div class="js-col-md-12 js-form-title '.esc_attr($this->class_prefix).'-bigfont">' . wpjobportal::wpjobportal_getVariableValue($wpjobportal_fieldtitle);
            if ($wpjobportal_required == 1) {
                $wpjobportal_data .= '<span class="'.esc_attr($this->class_prefix).'-error-msg"     color: redstyle="color: red;"> *</span>';
            }
            $wpjobportal_data .='</div>
            <div class="js-col-md-12 js-form-value">';
                $wpjobportal_data .='<input class="inputbox form-control '.esc_attr($this->class_prefix).'-input-field';

                        if ($wpjobportal_required == 1 ) {
                                $wpjobportal_data .= ' required ';
                        }
                        if($wpjobportal_fieldName == "date_of_birth" || $wpjobportal_fieldName == "date_start" ){
                            $wpjobportal_data .= ' custom_date ';
                            if($wpjobportal_fieldValue = '0000-00-00 00:00:00'){
                                $wpjobportal_fieldValue = '';
                            }
                        }
                        $wpjobportal_data .= '"';
                        if ($wpjobportal_fieldName == "email_address") {
                            $wpjobportal_data .= ' data-validation="email"';
                        }
                        if ($wpjobportal_required == 1 && $wpjobportal_fieldName != "email_address") {
                            $wpjobportal_data .= ' data-validation="required"';
                        }
                $wpjobportal_name = 'sec_1['.$wpjobportal_fieldName.']';
                $wpjobportal_data .=        ' type="text" name="' . $wpjobportal_name . '" id="' . $wpjobportal_fieldName . '" value = "' .  wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_fieldValue).'"' ;
                if (!empty($wpjobportal_extraattr)){
                    foreach ($wpjobportal_extraattr AS $wpjobportal_key => $wpjobportal_val){
                        $wpjobportal_data .= ' ' . $wpjobportal_key . '="' . $wpjobportal_val . '"';
                    }
                }
                $wpjobportal_data .= '" />';
            $wpjobportal_data .='</div>
        </div>';
        return $wpjobportal_data;
    }

    function getFieldForPersonalSection($wpjobportal_field, $wpjobportal_fieldValue, $columns = 0,$wpjobportal_extraattr=array(),$wpjobportal_themecall=null) {

        $wpjobportal_fieldtitle = $wpjobportal_field->fieldtitle;
        $wpjobportal_fieldName = $wpjobportal_field->field;
        $wpjobportal_required = $wpjobportal_field->required;
        $wpjobportal_description = $wpjobportal_field->description;
        $wpjobportal_style = '';
        $jb_jm_class="";
        if($columns == 3){
            $wpjobportal_style = ' formresumethree';
        }
        if(null != $wpjobportal_themecall){
            $wpjobportal_data = '
                <div class="wjportal-form-row'.$wpjobportal_style.'">
                    <div class="wjportal-form-title">
                        <label for="' . $wpjobportal_fieldName . '">';
                        $wpjobportal_data .= wpjobportal::wpjobportal_getVariableValue($wpjobportal_fieldtitle);
                            if ($wpjobportal_required == 1) {
                                        $wpjobportal_data .= '<span class="error-msg" style="color: red;"> *</span>';
                            }
            $wpjobportal_data .= '</label>
                    </div>
                    <div class="wjportal-form-value">
                        <input class="inputbox wjportal-form-input-field';
                            if ($wpjobportal_required == 1 ) {
                                $wpjobportal_data .= ' required ';
                            }
                            if($wpjobportal_fieldName == "date_of_birth" || $wpjobportal_fieldName == "date_start" ){
                                $wpjobportal_data .= ' custom_date ';
                            }
                            $wpjobportal_data .= '"';
                            if ($wpjobportal_fieldName == "email_address") {
                                $wpjobportal_data .= ' data-validation="email"';
                            }
                            if ($wpjobportal_required == 1 && $wpjobportal_fieldName != "email_address") {
                                $wpjobportal_data .= ' data-validation="required"';
                            }

            $wpjobportal_name = 'sec_1['.$wpjobportal_fieldName.']';
            $wpjobportal_data .=        ' type="text" name="' . $wpjobportal_name . '" id="' . $wpjobportal_fieldName . '" value = "' .  wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_fieldValue).'"' ;
                // adding place holder in field
            $placeholder = $wpjobportal_field->placeholder;
            if(!empty($placeholder)){
                $wpjobportal_data .= ' placeholder="'. esc_html(wpjobportal::wpjobportal_getVariableValue($placeholder)).'" ';
            }
            if (!empty($wpjobportal_extraattr)){
                foreach ($wpjobportal_extraattr AS $wpjobportal_key => $wpjobportal_val){
                    $wpjobportal_data .= ' ' . $wpjobportal_key . '="' . $wpjobportal_val . '"';
                }
            }
                $wpjobportal_data .= '" />
                ';
                $wpjobportal_description = $wpjobportal_field->description;
                if(!empty($wpjobportal_description)){
                    $wpjobportal_data .= '<div class="wjportal-form-help-txt">'. esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_description)).'</div>';
                }
                $wpjobportal_data .= '
                </div>
            </div>';
        }else{
            $wpjobportal_data = '
                <div class="wjportal-form-row'.$wpjobportal_style.'">
                    <div class="wjportal-form-title">
                        <label for="' . $wpjobportal_fieldName . '">';
                        $wpjobportal_data .= wpjobportal::wpjobportal_getVariableValue($wpjobportal_fieldtitle);
                            if ($wpjobportal_required == 1) {
                                        $wpjobportal_data .= '<span class="error-msg" style="color: red;"> *</span>';
                            }
            $wpjobportal_data .= '</label>
                    </div>
                    <div class="wjportal-form-value">
                        <input class="inputbox wjportal-form-input-field';
                            if ($wpjobportal_required == 1 ) {
                                $wpjobportal_data .= ' required ';
                            }
                            if($wpjobportal_fieldName == "date_of_birth" || $wpjobportal_fieldName == "date_start" ){
                                $wpjobportal_data .= ' custom_date ';
                            }
                            $wpjobportal_data .= '"';
                            if ($wpjobportal_fieldName == "email_address") {
                                $wpjobportal_data .= ' data-validation="email"';
                            }
                            if ($wpjobportal_required == 1 && $wpjobportal_fieldName != "email_address") {
                                $wpjobportal_data .= ' data-validation="required"';
                            }




            $wpjobportal_name = 'sec_1['.$wpjobportal_fieldName.']';
            $placeholder = $wpjobportal_field->placeholder;
            if(!empty($placeholder)){
                $wpjobportal_data .= ' placeholder="'. esc_html(wpjobportal::wpjobportal_getVariableValue($placeholder)).'" ';
            }
            $wpjobportal_data .=        ' type="text" name="' . $wpjobportal_name . '" id="' . $wpjobportal_fieldName . '" value = "' .  wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_fieldValue).'"' ;
            if (!empty($wpjobportal_extraattr)){
                foreach ($wpjobportal_extraattr AS $wpjobportal_key => $wpjobportal_val){
                    $wpjobportal_data .= ' ' . $wpjobportal_key . '="' . $wpjobportal_val . '"';
                }
            }
                $wpjobportal_data .= '" />
                ';
                $wpjobportal_description = $wpjobportal_field->description;
                if(!empty($wpjobportal_description)){
                    $wpjobportal_data .= '<div class="wjportal-form-help-txt">'. esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_description)).'</div>';
                }
                $wpjobportal_data .= '
                </div>
            </div>';
        }
        return $wpjobportal_data;
    }
    function getFieldForMultiSectionJobManager($wpjobportal_fieldtitle,$wpjobportal_fieldName,$wpjobportal_required,$wpjobportal_fieldValue,$wpjobportal_field_id_for,$wpjobportal_section, $wpjobportal_sectionid, $wpjobportal_ishidden){
            $wpjobportal_html = '<div class="js-col-md-12 js-form-wrapper">
            <div class="js-col-md-12 js-form-title '.esc_attr($this->class_prefix).'-bigfont" for="'.$wpjobportal_field_id_for.'">' . wpjobportal::wpjobportal_getVariableValue($wpjobportal_fieldtitle);
                if ($wpjobportal_required == 1) {
                    $wpjobportal_html .= '<span class="'.esc_attr($this->class_prefix).'-error-msg">*</span>';
                }
              $wpjobportal_html .='</div>
            <div class="js-col-md-12 js-form-value">';
                $wpjobportal_data_required = '';
                $wpjobportal_class_required = '';
                if($wpjobportal_ishidden != ''){
                    if ($wpjobportal_required == 1) {
                        $wpjobportal_data_required = 'data-myrequired="required"';
                    }
                    if ($wpjobportal_fieldName == "email_address") {
                        $wpjobportal_data_required = 'data-myrequired="required validate-email"';
                    }
                }else{
                    if ($wpjobportal_required == 1) {
                        $wpjobportal_class_required = ' required';
                    }
                    if ($wpjobportal_fieldName == "email_address") {
                        $wpjobportal_class_required = ' required validate-email';
                    }
                }

                $wpjobportal_html .= '<input class="inputbox form-control '.esc_attr($this->class_prefix).'-input-field '.$wpjobportal_class_required.'" '.$wpjobportal_data_required;

                switch ($wpjobportal_section) {
                    case '2': $wpjobportal_section = 'sec_2'; break;
                    case '3': $wpjobportal_section = 'sec_3'; break;
                    case '4': $wpjobportal_section = 'sec_4'; break;
                    case '5': $wpjobportal_section = 'sec_5'; break;
                    case '6': $wpjobportal_section = 'sec_6'; break;
                    case '7': $wpjobportal_section = 'sec_7'; break;
                    case '8': $wpjobportal_section = 'sec_8'; break;
                }
                $wpjobportal_name = $wpjobportal_section."[$wpjobportal_fieldName][$wpjobportal_sectionid]";

                $wpjobportal_html .=    ' type="text" name="' . $wpjobportal_name . '" id="' . $wpjobportal_field_id_for . '" maxlength="250" value = "' .  wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_fieldValue) . '" />';

            $wpjobportal_html .= '</div>
        </div>';
        return $wpjobportal_html;

    }

    function getFieldForMultiSection($wpjobportal_field, $wpjobportal_fieldValue, $wpjobportal_section, $wpjobportal_sectionid, $wpjobportal_ishidden,$wpjobportal_themecall ) {

        $wpjobportal_fieldtitle = $wpjobportal_field->fieldtitle;
        $wpjobportal_fieldName = $wpjobportal_field->field;
        $wpjobportal_required = $wpjobportal_field->required;
        $placeholder = $wpjobportal_field->placeholder;

        $wpjobportal_field_id_for = $wpjobportal_fieldName.$wpjobportal_section.$wpjobportal_sectionid;
        if(null !=$wpjobportal_themecall){

            $wpjobportal_data = '
                <div class="wjportal-form-row">
                    <div class="wjportal-form-title">
                        <label for="' . $wpjobportal_field_id_for . '">';
                            $wpjobportal_data .= wpjobportal::wpjobportal_getVariableValue($wpjobportal_fieldtitle);
                            if ($wpjobportal_required == 1) {
                                        $wpjobportal_data .= '<span class="error-msg">*</span>';
                            }
            $wpjobportal_data .= '  </label>
                    </div>';
            $wpjobportal_data .= '<div class="wjportal-form-value">';

            $wpjobportal_data_required = '';
            $wpjobportal_class_required = '';
            if($wpjobportal_ishidden != ''){
                if ($wpjobportal_required == 1) {
                    $wpjobportal_data_required = 'data-myrequired="required"';
                }
                if ($wpjobportal_fieldName == "email_address") {
                    $wpjobportal_data_required = 'data-myrequired="required validate-email"';
                }
            }else{
                if ($wpjobportal_required == 1) {
                    $wpjobportal_class_required = ' required';
                }
                if ($wpjobportal_fieldName == "email_address") {
                    $wpjobportal_class_required = ' required validate-email';
                }
            }

            $wpjobportal_data .= '<input class="inputbox wjportal-form-input-field'.$wpjobportal_class_required.'" '.$wpjobportal_data_required;

            switch ($wpjobportal_section) {
                case '2': $wpjobportal_section = 'sec_2'; break;
                case '3': $wpjobportal_section = 'sec_3'; break;
                case '4': $wpjobportal_section = 'sec_4'; break;
                case '5': $wpjobportal_section = 'sec_5'; break;
                case '6': $wpjobportal_section = 'sec_6'; break;
                case '7': $wpjobportal_section = 'sec_7'; break;
                case '8': $wpjobportal_section = 'sec_8'; break;
            }
            $wpjobportal_name = $wpjobportal_section."[$wpjobportal_fieldName][$wpjobportal_sectionid]";

            if($placeholder != ''){
                $wpjobportal_data .= ' placeholder="'.wpjobportalphplib::wpJP_htmlspecialchars($placeholder).'" ';
            }

            $wpjobportal_data .=    ' type="text" name="' . $wpjobportal_name . '" id="' . $wpjobportal_field_id_for . '" maxlength="250" value = "' .  wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_fieldValue) . '" />
                    </div>
                </div>';

        }else{

            $wpjobportal_data = '
                <div class="wjportal-form-row">
                    <div class="wjportal-form-title">
                        <label for="' . $wpjobportal_field_id_for . '">';
                            $wpjobportal_data .= wpjobportal::wpjobportal_getVariableValue($wpjobportal_fieldtitle);
                            if ($wpjobportal_required == 1) {
                                        $wpjobportal_data .= '<span class="error-msg">*</span>';
                            }
            $wpjobportal_data .= '  </label>
                    </div>';
            $wpjobportal_data .= '<div class="wjportal-form-value">';

            $wpjobportal_data_required = '';
            $wpjobportal_class_required = '';
            if($wpjobportal_ishidden != ''){
                if ($wpjobportal_required == 1) {
                    $wpjobportal_data_required = 'data-myrequired="required"';
                }
                if ($wpjobportal_fieldName == "email_address") {
                    $wpjobportal_data_required = 'data-myrequired="required validate-email"';
                }
            }else{
                if ($wpjobportal_required == 1) {
                    $wpjobportal_class_required = ' required';
                }
                if ($wpjobportal_fieldName == "email_address") {
                    $wpjobportal_class_required = ' required validate-email';
                }
            }

            $wpjobportal_data .= '<input class="inputbox wjportal-form-input-field'.$wpjobportal_class_required.'" '.$wpjobportal_data_required;

            switch ($wpjobportal_section) {
                case '2': $wpjobportal_section = 'sec_2'; break;
                case '3': $wpjobportal_section = 'sec_3'; break;
                case '4': $wpjobportal_section = 'sec_4'; break;
                case '5': $wpjobportal_section = 'sec_5'; break;
                case '6': $wpjobportal_section = 'sec_6'; break;
                case '7': $wpjobportal_section = 'sec_7'; break;
                case '8': $wpjobportal_section = 'sec_8'; break;
            }
            $wpjobportal_name = $wpjobportal_section."[$wpjobportal_fieldName][$wpjobportal_sectionid]";

            $wpjobportal_data .=    ' type="text" name="' . $wpjobportal_name . '" id="' . $wpjobportal_field_id_for . '" maxlength="250" value = "' .  wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_fieldValue) . '" />
                ';
                    $wpjobportal_description = $wpjobportal_field->description;
                    if(!empty($wpjobportal_description)){
                        $wpjobportal_data .= '<div class="wjportal-form-help-txt">'. esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_description)).'</div>';
                    }
                    $wpjobportal_data .= '
                    </div>
                </div>';
        }

        return $wpjobportal_data;
    }

    function prepareDateFormat(){

        $wpjobportal_config_date=wpjobportal::$_config->getConfigurationByConfigName('date_format');
        if ($wpjobportal_config_date == 'm/d/Y'){
            $wpjobportal_dash = '/';
        }else{
            $wpjobportal_dash = "-";
        }
        $wpjobportal_dateformat = $wpjobportal_config_date;
        $wpjobportal_firstdash = wpjobportalphplib::wpJP_strpos($wpjobportal_dateformat, $wpjobportal_dash, 0);
        $wpjobportal_firstvalue = wpjobportalphplib::wpJP_substr($wpjobportal_dateformat, 0, $wpjobportal_firstdash);
        $wpjobportal_firstdash = $wpjobportal_firstdash + 1;
        $wpjobportal_seconddash = wpjobportalphplib::wpJP_strpos($wpjobportal_dateformat, $wpjobportal_dash, $wpjobportal_firstdash);
        $wpjobportal_secondvalue = wpjobportalphplib::wpJP_substr($wpjobportal_dateformat, $wpjobportal_firstdash, $wpjobportal_seconddash - $wpjobportal_firstdash);
        $wpjobportal_seconddash = $wpjobportal_seconddash + 1;
        $wpjobportal_thirdvalue = wpjobportalphplib::wpJP_substr($wpjobportal_dateformat, $wpjobportal_seconddash, wpjobportalphplib::wpJP_strlen($wpjobportal_dateformat) - $wpjobportal_seconddash);
        //$wpjobportal_js_dateformat = '%' . $wpjobportal_firstvalue . $wpjobportal_dash . '%' . $wpjobportal_secondvalue . $wpjobportal_dash . '%' . $wpjobportal_thirdvalue;
        $wpjobportal_js_dateformat =  $wpjobportal_firstvalue . $wpjobportal_dash . $wpjobportal_secondvalue . $wpjobportal_dash . $wpjobportal_thirdvalue;

        return $wpjobportal_js_dateformat;
    }

    function getCityFieldForForm($for , $wpjobportal_sectionid, $object, $wpjobportal_field , $wpjobportal_ishidden,$wpjobportal_themecall){

        $wpjobportal_html = '';
        switch ($for) {
            case '2':
                $cityfor = 'address'; break;
            case '3':
                $cityfor = 'institute'; break;
            case '4':
                $cityfor = 'employer'; break;
            case '7':
                $cityfor = 'reference'; break;
            break;
        }
        $wpjobportal_data_required = '';
        $city_required = ($wpjobportal_field->required ? 'required' : '');
        if($wpjobportal_ishidden){
            if($city_required){
                $wpjobportal_data_required = 'data-myrequired="required"';
                $city_required = '';
            }
        }
        $cityforedit = '';
        $wpjobportal_data = array('city_id' => null, 'city_name' => null);
        if (isset($object->{$wpjobportal_field->field}) AND ($object->{$wpjobportal_field->field})) {
            $cityforedit = 1;
            $wpjobportal_data['city_id'] = $object->{$wpjobportal_field->field};
            //$wpjobportal_data['city_name'] = $object->cityname ;
            $wpjobportal_default_location_view = wpjobportal::$_config->getConfigurationByConfigName('defaultaddressdisplaytype');
            $wpjobportal_data['city_name'] = WPJOBPORTALincluder::getJSModel('common')->getLocationForView($object->cityname, $object->statename, $object->countryname);
            /*switch ($wpjobportal_default_location_view) {
                case 'csc':
                   $wpjobportal_data['city_name'] .= ", " . $object->statename . ", " . $object->countryname;
                  $wpjobportal_data['city_name'] .=", " . $object->countryname;
                    break;
                case 'cs':
                    $wpjobportal_data['city_name'] .= ", " . $object->statename;
                    break;
                case 'cc':
                    $wpjobportal_data['city_name'] .= ", " . $object->countryname;
                    break;
            }*/
        }
        $wpjobportal_field_city_id="'".$cityfor.'_city_'.$wpjobportal_sectionid."'";
        $edit_field_city_id="'".$cityfor.'cityforedit_'.$wpjobportal_sectionid."'";
        $wpjobportal_html .= '
            <div class="wjportal-form-row">
                <div class="wjportal-form-title">
                    <label id="'.esc_attr($cityfor).'_citymsg" for="'.esc_attr($cityfor).'_city_'.esc_attr($wpjobportal_sectionid).'">' . wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle);
                        if ($wpjobportal_field->required == 1) {
                            $wpjobportal_html .= '<span class="error-msg">*</span>';
                        }
        $wpjobportal_html .= '  </label>
                </div>
                <div class="wjportal-form-value">
                    <input data-for="'.esc_attr($cityfor).'_'.esc_attr($wpjobportal_sectionid).'" class="inputbox jstokeninputcity ' . $city_required . '" '.$wpjobportal_data_required.' type="text" name="sec_'.$for.'['.esc_attr($cityfor).'_city]['.esc_attr($wpjobportal_sectionid).']" id="'.esc_attr($cityfor).'_city_'.esc_attr($wpjobportal_sectionid).'" size="40" maxlength="100" value="'.$wpjobportal_data['city_name'].'" />
                    <input type="hidden" name="sec_'.$for.'['.esc_attr($cityfor).'cityforedit]['.esc_attr($wpjobportal_sectionid).']" id="'.esc_attr($cityfor).'cityforedit_'.esc_attr($wpjobportal_sectionid).'" value="'. wpjobportalphplib::wpJP_htmlspecialchars($cityforedit).'" />
                    <input type="hidden" class="jscityid" name="jscityid" value="'.$wpjobportal_data['city_id'].'" />
                    <input type="hidden" class="jscityname" name="jscityname" value="'.$wpjobportal_data['city_name'].'" />
                    ';
                $wpjobportal_description = $wpjobportal_field->description;
                if(!empty($wpjobportal_description)){
                    $wpjobportal_html .= '<div class="wjportal-form-help-txt">'. esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_description)).'</div>';
                }
                $wpjobportal_html .= '
                </div>';
            $wpjobportal_html .= '</div>';
        return $wpjobportal_html;
    }

   function makeResumeSectionFields($wpjobportal_themecall=null){
        $wpjobportal_resume="";
        if(isset(wpjobportal::$_data[0]['personal_section'])) $wpjobportal_resume = wpjobportal::$_data[0]['personal_section'];
        //$wpjobportal_fields_ordering = wpjobportal::$_data[1];

        $wpjobportal_html = '<div id="jssection_resume" class="section_wrapper jssectionwrapper ">';
        if(empty($wpjobportal_resume->resume)){
            //$wpjobportal_jssection_hide = (isset(wpjobportal::$wpjobportal_data['resumeid']) && is_numeric(wpjobportal::$wpjobportal_data['resumeid']))?"": 'jssection_hide';
            $wpjobportal_jssection_hide = 'jssection_hide';
        }else{
            ///$wpjobportal_jssection_hide = (isset(wpjobportal::$wpjobportal_data['resumeid']) && is_numeric(wpjobportal::$wpjobportal_data['resumeid']))?"": 'jssection_hide';
            $wpjobportal_jssection_hide = '';
        }
        $wpjobportal_sectionid = 0;
        // <div class="jsundo wjportal-resume-section-undo"><img class="jsundoimage wjportal-resume-section-undo-image" onclick="undoThisSection(this);" src="'.JURI::root().'components/com_wpjobportal/images/resume/undo-icon.png" /></div>
        // <img class="jsdeleteimage wjportal-resume-section-delete" onclick="deleteThisSection(this);" src="'.JURI::root().'components/com_wpjobportal/images/resume/delete-icon.png" />
        $wpjobportal_html .= '<div class="section_wrapper form wjportal-resume-section jssection_wrapper '.$wpjobportal_jssection_hide.' jssection_resume_'.esc_attr($wpjobportal_sectionid).'">';
        foreach (wpjobportal::$_data[2][6] as $wpjobportal_field) {
            switch ($wpjobportal_field->field) {
                case "resume":
                    $fvalue = isset($wpjobportal_resume->resume) ? $wpjobportal_resume->resume : '';
                    $wpjobportal_req = ($wpjobportal_field->required ? 'required' : '');
                    $wpjobportal_data_required = '';
                    if($wpjobportal_jssection_hide){
                        if($wpjobportal_req){
                            $wpjobportal_data_required = 'data-myrequired="required"';
                            $wpjobportal_req = '';
                        }
                    }
                    $wpjobportal_html .= '
                        <div class="wpjp-form-wrapper js-col-md-12 js-form-wrapper">
                            <label id="" class="wpjp-form-title " for="resumeeditor">' . wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle);
                                if ($wpjobportal_field->required == 1) {
                                    $wpjobportal_html .= '<span class="error-msg">*</span>';
                                }
                    //$wpjobportal_name = 'sec_6[resume]['.esc_attr($wpjobportal_sectionid).']';
                    $wpjobportal_name = 'resumeeditor';

                    //$wpjobportal_value=wp_editor(isset($wpjobportal_resume->resume) ? $wpjobportal_resume->resume: '', 'resume', array('media_buttons' => false, 'data-validation' => $wpjobportal_req));
                    $wpjobportal_value=isset($wpjobportal_resume->resume) ? $wpjobportal_resume->resume: '';
                    $efield = WPJOBPORTALformfield::textarea('resume', $wpjobportal_value, array('class' => 'inputbox one resumeeditor form-control '.esc_attr($this->class_prefix).'-textarea-field', 'height'=>'270px','rows'=>'10','cols'=>'40'));
                    $efield .= WPJOBPORTALformfield::hidden('resume_edit_val','');
                    $wpjobportal_html .= '</label>
                            <div class="wpjp-form-value ">
                                '.$efield.'
                            </div>
                        </div>';
                    break;
                default:
                    $wpjobportal_html .= $this->getResumeFormUserField($wpjobportal_field, $wpjobportal_resume , 6 , $wpjobportal_sectionid, $wpjobportal_jssection_hide,$wpjobportal_themecall);
                break;
            }
        }
        $wpjobportal_id = '';
        $wpjobportal_deletethis = (empty($wpjobportal_resume->resume)) ? 1 : 0;
        $wpjobportal_html .= '<input type="hidden" id="deletethis6'.esc_attr($wpjobportal_sectionid).'" class="jsdeletethissection" name="sec_6[deletethis]['.esc_attr($wpjobportal_sectionid).']" value="'. wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_deletethis).'">
                    <input type="hidden" id="id" name="sec_6[id]['.esc_attr($wpjobportal_sectionid).']" value="'.$wpjobportal_id.'">
            </div></div>';
        if(empty($wpjobportal_resume->resume)){
            if(null !=$wpjobportal_themecall){
                $wpjobportal_html .= '<div class="wpjp-add-new-section-link wjportal-resume-add-new-section-btn" onclick="showResumeSection( this, \'resume\');"><i class="fa fa-plus"></i>'.esc_html(__('Add Resume','wp-job-portal')).'</div>';
            }else{
                $wpjobportal_html .= '<div class="wpjp-add-new-section-link wjportal-resume-add-new-section-btn" onclick="showResumeSection( this, \'resume\');"><i class="fa fa-plus"></i>'.esc_html(__('Add Resume','wp-job-portal')).'</div>';

            }
        }
        return $wpjobportal_html;
    }

    /* function makeAddressSectionFields($wpjobportal_themecall=null) {
        $wpjobportal_addresses=array();
        if(isset(wpjobportal::$_data[0]['address_section'])){
            $wpjobportal_addresses = wpjobportal::$_data[0]['address_section'];
        }
        //$wpjobportal_fields_ordering = wpjobportal::$_data[1];
        $wpjobportal_sections_allowed = wpjobportal::$_config->getConfigurationByConfigName('max_resume_addresses');
        $j = 1;
        $wpjobportal_html = '<div id="jssection_address" class="jssectionwrapper section_wrapper wjportal-resume-section-wrp">';
        if(empty($wpjobportal_addresses)){
            $wpjobportal_addresses = array();
            for ($wpjobportal_i=0; $wpjobportal_i < $wpjobportal_sections_allowed; $wpjobportal_i++) {
                $wpjobportal_addresses[] = 'new';
            }
        }else{
            //Edit case to show remaining allowed sections
            $wpjobportal_totalexistings = count($wpjobportal_addresses);
            $j = $wpjobportal_sections_allowed - $wpjobportal_totalexistings;
            if($wpjobportal_totalexistings < $wpjobportal_sections_allowed){
                for ($wpjobportal_i=0; $wpjobportal_i < $j; $wpjobportal_i++) {
                    $wpjobportal_addresses[] = 'new';
                }
            }
        }

        $wpjobportal_sectionid = 0;
        $wpjobportal_sectionhead = 1;
        foreach ($wpjobportal_addresses as $wpjobportal_address) {

            //$wpjobportal_jssection_hide = isset($wpjobportal_address->id) ? '' :((isset(wpjobportal::$wpjobportal_data['resumeid']) && is_numeric(wpjobportal::$wpjobportal_data['resumeid']))?"": 'jssection_hide');
            $wpjobportal_jssection_hide = isset($wpjobportal_address->id) ? '' : 'jssection_hide';
            //$wpjobportal_jssection_hide = isset($wpjobportal_address->id) ? '' : '';
            $wpjobportal_html .= '<div class="section_wrapper form wjportal-resume-section '.$wpjobportal_jssection_hide.' jssection_address_'.esc_attr($wpjobportal_sectionid).'">
                        <div class="wjportal-resume-section-head">'.esc_html(__('Address','wp-job-portal')). ' ' .$wpjobportal_sectionhead++.'</div>
                        <div class="jsundo wjportal-resume-section-undo"><img class="jsundoimage wjportal-resume-section-undo-image" onclick="undoThisSection(this);" src="'.esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/resume/undo-icon.png" /></div>
                        <img class="jsdeleteimage wjportal-resume-section-delete" onclick="deleteThisSection(this);" src="'.esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/resume/delete-icon.png" />';
            foreach (wpjobportal::$_data[2][2] as $wpjobportal_field) {
                switch ($wpjobportal_field->field) {
                    case "address_city":
                        $for = 2;
                        $wpjobportal_html .= $this->getCityFieldForForm( $for , $wpjobportal_sectionid, $wpjobportal_address, $wpjobportal_field ,$wpjobportal_jssection_hide,$wpjobportal_themecall);
                        break;
                    case "address":
                        $wpjobportal_fieldValue = isset($wpjobportal_address->address) ? $wpjobportal_address->address : '';
                        $wpjobportal_html .= $this->getFieldForMultiSection($wpjobportal_field, $wpjobportal_fieldValue, 2, $wpjobportal_sectionid, $wpjobportal_jssection_hide,$wpjobportal_themecall);
                        break;
                    case "address_location": //longitude and latitude
                        $wpjobportal_required = ($wpjobportal_field->required ? 'required' : '');
                        $latitude = isset($wpjobportal_address->latitude) ? $wpjobportal_address->latitude : '';
                        $longitude = isset($wpjobportal_address->longitude) ? $wpjobportal_address->longitude : '';
                        $wpjobportal_data_required = '';
                        if($wpjobportal_jssection_hide){
                            if($wpjobportal_required){
                                $wpjobportal_data_required = 'data-myrequired="required"';
                                $wpjobportal_required = '';
                            }
                        }
                        $wpjobportal_html .= apply_filters('wpjobportal_addons_loadadressdata_for_resume',false,$wpjobportal_address,$wpjobportal_required,$wpjobportal_data_required,$wpjobportal_sectionid,$wpjobportal_field,$this->class_prefix,$wpjobportal_jssection_hide);
                    break;

                    default:
                        $wpjobportal_html .= $this->getResumeFormUserField($wpjobportal_field, $wpjobportal_address , 2 ,  $wpjobportal_sectionid, $wpjobportal_jssection_hide,$wpjobportal_themecall);
                    break;
                }
            }
            $wpjobportal_id = isset($wpjobportal_address->id) ? $wpjobportal_address->id : '';
            $wpjobportal_deletethis = ($wpjobportal_id != '') ? 0 : 1;
            $wpjobportal_html .= '<input type="hidden" id="deletethis2'.esc_attr($wpjobportal_sectionid).'" class="jsdeletethissection" name="sec_2[deletethis]['.esc_attr($wpjobportal_sectionid).']" value="'.$wpjobportal_deletethis.'">
                        <input type="hidden" id="id" name="sec_2[id]['.esc_attr($wpjobportal_sectionid).']" value="'.$wpjobportal_id.'">';
                    if(null !=$wpjobportal_themecall){
                        $wpjobportal_html .= '<hr class="'.esc_attr($this->class_prefix).'-resume-section-sep" />';
                    }
                    $wpjobportal_html .= '</div>';
            $wpjobportal_sectionid++;
        }
        $wpjobportal_html .= '</div>';
        if($j > 0){
            if(null !=$wpjobportal_themecall){
                $wpjobportal_html .= '<div class="wpjp-add-new-section-link  '.esc_attr($this->class_prefix).'-resume-addnewbutton" onclick="showResumeSection( this ,\'address\');">
                <span class="'.esc_attr($this->class_prefix).'-addresume-addfield-btn-txt"><i class="fa fa-plus-square-o" aria-hidden="true"></i>'.esc_html(__('Add New','wp-job-portal')).' '. esc_html(__('Address','wp-job-portal')).'
                </span></div>';
            }else{
                $wpjobportal_html .= '<div class="wpjp-add-new-section-link wjportal-resume-add-new-section-btn" onclick="showResumeSection( this ,\'address\');"><i class="fa fa-plus"></i>'.esc_html(__('Add New','wp-job-portal')).' '. esc_html(__('Address','wp-job-portal')).'</div>';
            }
        }
        return $wpjobportal_html;
    }

    function makeEmployerSectionFields($wpjobportal_themecall=null){
        $wpjobportal_employers="";
        if(isset(wpjobportal::$_data[0]['employer_section'])){
            $wpjobportal_employers = wpjobportal::$_data[0]['employer_section'];
        }
        $wpjobportal_sections_allowed = wpjobportal::$_config->getConfigurationByConfigName('max_resume_employers');
        $wpjobportal_js_dateformat = $this->prepareDateFormat();
        $j = 1;
        $wpjobportal_html = '<div id="jssection_employer" class="jssectionwrapper section_wrapper wjportal-resume-section-wrp">';
        if(empty($wpjobportal_employers)){
            $wpjobportal_employers = array();
            for ($wpjobportal_i=0; $wpjobportal_i < $wpjobportal_sections_allowed; $wpjobportal_i++) {
                $wpjobportal_employers[] = 'new';
            }
        }else{
            //Edit case to show remaining allowed sections
            $wpjobportal_totalexistings = count($wpjobportal_employers);
            $j = $wpjobportal_sections_allowed - $wpjobportal_totalexistings;
            if($wpjobportal_totalexistings < $wpjobportal_sections_allowed){
                for ($wpjobportal_i=0; $wpjobportal_i < $j; $wpjobportal_i++) {
                    $wpjobportal_employers[] = 'new';
                }
            }
        }

        $wpjobportal_sectionid = 0;
        $wpjobportal_sectionhead = 1;
        foreach ($wpjobportal_employers as $wpjobportal_employer) {
            $wpjobportal_jssection_hide = isset($wpjobportal_employer->id) ? '' : 'jssection_hide';
            $wpjobportal_html .= '<div class="section_wrapper form wjportal-resume-section jssection_wrapper '.$wpjobportal_jssection_hide.' jssection_employer_'.esc_attr($wpjobportal_sectionid).'">
                        <div class="wjportal-resume-section-head">'.esc_html(__('Employer','wp-job-portal')).' '.$wpjobportal_sectionhead++.'</div>
                        <div class="jsundo wjportal-resume-section-undo"><img class="jsundoimage wjportal-resume-section-undo-image" onclick="undoThisSection(this);" src="'.esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/resume/undo-icon.png" /></div>
                        <img class="jsdeleteimage wjportal-resume-section-delete" onclick="deleteThisSection(this);" src="'.esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/resume/delete-icon.png" />';
            $wpjobportal_counter = 0;
            foreach (wpjobportal::$_data[2][4] as $wpjobportal_field) {
                switch ($wpjobportal_field->field) {
                    case "employer":
                        $fvalue = isset($wpjobportal_employer->employer) ? $wpjobportal_employer->employer : '';
                        $wpjobportal_html .= $this->getFieldForMultiSection($wpjobportal_field, $fvalue, 4, $wpjobportal_sectionid, $wpjobportal_jssection_hide,$wpjobportal_themecall);
                        break;
                    case "employer_position":
                        $fvalue = isset($wpjobportal_employer->employer_position) ? $wpjobportal_employer->employer_position : '';
                        $wpjobportal_html .= $this->getFieldForMultiSection($wpjobportal_field, $fvalue, 4, $wpjobportal_sectionid, $wpjobportal_jssection_hide,$wpjobportal_themecall);
                        break;
                   //
                    case "employer_from_date":
                    case "employer_to_date":
                    case "employer_current_status":
                        if($wpjobportal_counter == 0){
                                $wpjobportal_field_obj = '';
                                foreach (wpjobportal::$_data[2][4] as $wpjobportal_field_obj) {
                                    switch ($wpjobportal_field_obj->field) {
                                        case "employer_from_date":
                                            $wpjobportal_html .= '
                                                <div id="fromdate'.esc_attr($wpjobportal_sectionid).'" class="wjportal-form-row">
                                                    <div class="wjportal-form-title">
                                                        <label for="employer_from_date4'.esc_attr($wpjobportal_sectionid).'">' . wpjobportal::wpjobportal_getVariableValue($wpjobportal_field_obj->fieldtitle);
                                                            if ($wpjobportal_field_obj->required == 1) {
                                                                $wpjobportal_html .= '<span class="'.esc_attr($this->class_prefix).'-error-msg error-msg">*</span>';
                                                            }
                                            $wpjobportal_html .='   </label>
                                                    </div>
                                                    <div class="wjportal-form-value">';
                                                         $wpjobportal_fieldValue = isset($wpjobportal_employer->employer_from_date) ? date_i18n(wpjobportal::$_configuration['date_format'],strtotime($wpjobportal_employer->employer_from_date)) : '';
                                                        $wpjobportal_html .= '<input type="text" class="input wjportal-form-date-field form-control '.esc_attr($this->class_prefix).'-input-field custom_date" name="sec_4[employer_from_date][]" id="employer_from_date4'.esc_attr($wpjobportal_sectionid).'" value='. $wpjobportal_fieldValue .'>';
                                            $wpjobportal_html .='</div>
                                                </div>';
                                            break;
                                        case "employer_to_date":
                                        $fvalue = isset($wpjobportal_employer->employer_current_status) ? $wpjobportal_employer->employer_current_status : '';
                                        if($fvalue==1){
                                            $wpjobportal_display="none";
                                        }else if($fvalue==0){
                                            $wpjobportal_display="";
                                        }else{
                                            $wpjobportal_display="";
                                        }
                                            $wpjobportal_html .= '
                                                <div class="wjportal-form-row" id="resto_date'.esc_attr($wpjobportal_sectionid).'" style="display:'.$wpjobportal_display.'" >
                                                    <div class="wjportal-form-title">
                                                        <label class="wpjp-form-title " for="employer_to_date4'.esc_attr($wpjobportal_sectionid).'">' . wpjobportal::wpjobportal_getVariableValue($wpjobportal_field_obj->fieldtitle);
                                                            if ($wpjobportal_field_obj->required == 1) {
                                                                $wpjobportal_html .= '<span class="'.esc_attr($this->class_prefix).'-error-msg error-msg">*</span>';
                                                            }
                                            $wpjobportal_html .='   </label>
                                                    </div>
                                                    <div class="wjportal-form-value">';
                                                          $wpjobportal_fieldValue = isset($wpjobportal_employer->employer_to_date) ? date_i18n(wpjobportal::$_configuration['date_format'],strtotime($wpjobportal_employer->employer_to_date)) : '';
                                                        $wpjobportal_html .= '<input type="text" class="input wjportal-form-date-field form-control '.esc_attr($this->class_prefix).'-input-field custom_date" name="sec_4[employer_to_date][]" id="employer_to_date4'.esc_attr($wpjobportal_sectionid).'" onchange="dateValidator('.esc_attr($wpjobportal_sectionid).')" value='. $wpjobportal_fieldValue .'>';
                                            $wpjobportal_html .='</div>
                                                </div>';
                                            break;
                                        case "employer_current_status":
                                            $wpjobportal_html .= '<div class="wjportal-form-row">
                                                        <div class="wjportal-form-title">
                                                            <label class="wpjp-form-title " for="' . $wpjobportal_field->id . '">';
                                            $wpjobportal_html .=            wpjobportal::wpjobportal_getVariableValue($wpjobportal_field_obj->fieldtitle);
                                            $wpjobportal_html .='       </label>
                                                        </div>
                                                        <div class="wjportal-form-value">';
                                                $fvalue = isset($wpjobportal_employer->employer_current_status) ? $wpjobportal_employer->employer_current_status : '';
                                            $wpjobportal_html .= '<label class="wjportal-input-box-switch"><input type="checkbox" onclick="disablefields('.esc_attr($wpjobportal_sectionid).')" class="input wjportal-form-chkbox-field" name="sec_4[employer_current_status][]" id="employer_current_status'.esc_attr($wpjobportal_sectionid).'" value="'.$fvalue.'"
                                            ';
                                              if($fvalue==1){
                                                    $wpjobportal_html.='checked="checked"';
                                                }else if($fvalue==0){
                                                    $wpjobportal_html.='';
                                                }else if($fvalue==''){
                                                }else{

                                                }
                                                $wpjobportal_html.='><span class="wjportal-input-box-slider"></span></label>';
                                                $wpjobportal_html .='</div>
                                                </div>';
                                            break;
                                        }
                                    }
                            }
                            $wpjobportal_counter = 1;
                        break;
                    case "employer_city":
                        $for = 4;
                        $wpjobportal_html .= $this->getCityFieldForForm( $for , $wpjobportal_sectionid, $wpjobportal_employer, $wpjobportal_field , $wpjobportal_jssection_hide,$wpjobportal_themecall);
                        break;
                    case "employer_phone":
                        $fvalue = isset($wpjobportal_employer->employer_phone) ? $wpjobportal_employer->employer_phone : '';
                        $wpjobportal_html .= $this->getFieldForMultiSection($wpjobportal_field, $fvalue, 4, $wpjobportal_sectionid, $wpjobportal_jssection_hide,$wpjobportal_themecall);
                        break;
                    case "employer_address":
                        $fvalue = isset($wpjobportal_employer->employer_address) ? $wpjobportal_employer->employer_address : '';
                        $wpjobportal_html .= $this->getFieldForMultiSection($wpjobportal_field, $fvalue, 4, $wpjobportal_sectionid, $wpjobportal_jssection_hide,$wpjobportal_themecall);
                        break;

                    default:
                        $wpjobportal_html .= $this->getResumeFormUserField($wpjobportal_field, $wpjobportal_employer , 4 , $wpjobportal_sectionid, $wpjobportal_jssection_hide,$wpjobportal_themecall);
                    break;
                }
            }
            $wpjobportal_id = isset($wpjobportal_employer->id) ? $wpjobportal_employer->id : '';
            $wpjobportal_deletethis = ($wpjobportal_id != '') ? 0 : 1;
            $wpjobportal_html .= '<input type="hidden" id="deletethis4'.esc_attr($wpjobportal_sectionid).'" class="jsdeletethissection" name="sec_4[deletethis]['.esc_attr($wpjobportal_sectionid).']" value="'.$wpjobportal_deletethis.'">
                        <input type="hidden" id="id" name="sec_4[id]['.esc_attr($wpjobportal_sectionid).']" value="'.$wpjobportal_id.'">';
                    if(null !=$wpjobportal_themecall){
                        $wpjobportal_html .= '<hr class="'.esc_attr($this->class_prefix).'-resume-section-sep" />';
                    }
                    $wpjobportal_html .='</div>';
            $wpjobportal_sectionid++;
        }
        $wpjobportal_html .= '</div>';
        if($j > 0){
            if(null !=$wpjobportal_themecall){
                $wpjobportal_html .= '<div class="wpjp-add-new-section-link  '.esc_attr($this->class_prefix).'-resume-addnewbutton" onclick="showResumeSection( this ,\'employer\');">
                <span class="'.esc_attr($this->class_prefix).'-addresume-addfield-btn-txt"><i class="fa fa-plus-square-o" aria-hidden="true"></i>'.esc_html(__('Add New','wp-job-portal')).' '. esc_html(__('Employer','wp-job-portal')).'
                </span></div>';
            }else{
                $wpjobportal_html .= '<div class="wpjp-add-new-section-link wjportal-resume-add-new-section-btn " onclick="showResumeSection( this ,\'employer\');"><i class="fa fa-plus"></i>'.esc_html(__('Add New','wp-job-portal')).' '. esc_html(__('Employer').'</div>';
            }
        }
        return $wpjobportal_html;

    } */

    function makePersonalSectionFields($wpjobportal_themecall=null) {
        $wpjobportal_resume="";
        $wpjobportal_userinfo = isset(wpjobportal::$_data['userinfo']) ? wpjobportal::$_data['userinfo'] : null;
        if(isset(wpjobportal::$_data[0]['personal_section'])){
            $wpjobportal_resume = wpjobportal::$_data[0]['personal_section'];
        }
        $wpjobportal_resumelists = "";
        $wpjobportal_js_dateformat = $this->prepareDateFormat();
        $wpjobportal_sectionid = 0;
        if(isset($wpjobportal_userinfo)){
            $wpjobportal_emailAddress =  $wpjobportal_userinfo->emailaddress;
            $wpjobportal_firstName = $wpjobportal_userinfo->first_name ;
            $lastName = $wpjobportal_userinfo->last_name;
        }else{
             $wpjobportal_emailAddress =  '';
            $wpjobportal_firstName = '' ;
            $lastName = '';
        }
        $wpjobportal_data = '<div class="wjportal-resume-section-wrp" data-section="personal" data-sectionid="">';
            $wpjobportal_name_counter = 0;
            $cell_counter = 0;
            $wpjobportal_date_counter = 0;
            $available_counter = 0;
            $wpjobportal_searchable_counter = 0;
            foreach (wpjobportal::$_data[2][1] as $wpjobportal_field) {
                switch ($wpjobportal_field->field) {
                    case "application_title":
                            $wpjobportal_fieldValue = isset($wpjobportal_resume->application_title) ? $wpjobportal_resume->application_title : "";
                            $wpjobportal_data .= $this->getFieldForPersonalSection($wpjobportal_field, $wpjobportal_fieldValue,'','',$wpjobportal_themecall);
                        break;
                    case "first_name":
                    case "last_name":
                        if($wpjobportal_name_counter == 0){
                            $wpjobportal_data .= '';
                                $wpjobportal_field_obj = '';
                                foreach (wpjobportal::$_data[2][1] as $wpjobportal_field_obj) {
                                    switch ($wpjobportal_field_obj->field) {
                                        case "first_name":
                                                $wpjobportal_fieldValue = isset($wpjobportal_resume->first_name) ? $wpjobportal_resume->first_name : $wpjobportal_firstName;
                                                $wpjobportal_data .= $this->getFieldForPersonalSection($wpjobportal_field_obj, $wpjobportal_fieldValue, 3,'',$wpjobportal_themecall);
                                            break;
                                        case "last_name":
                                                $wpjobportal_fieldValue = isset($wpjobportal_resume->last_name) ? $wpjobportal_resume->last_name : $lastName;
                                                $wpjobportal_data .= $this->getFieldForPersonalSection($wpjobportal_field_obj, $wpjobportal_fieldValue, 3,'',$wpjobportal_themecall);
                                            break;
                                    }
                                }
                            $wpjobportal_data .= '';
                        }
                        $wpjobportal_name_counter = 1;
                        break;
                    case "email_address": $wpjobportal_email_required = ($wpjobportal_field->required ? 'required' : '');
                            $wpjobportal_fieldValue = isset($wpjobportal_resume->email_address) ? $wpjobportal_resume->email_address : $wpjobportal_emailAddress;
                            $wpjobportal_data .= $this->getFieldForPersonalSection($wpjobportal_field, $wpjobportal_fieldValue,'','',$wpjobportal_themecall);
                        break;
                    case "cell":
                        if($cell_counter == 0){
                            $wpjobportal_data .= '';
                                $wpjobportal_field_obj = '';
                                foreach (wpjobportal::$_data[2][1] as $wpjobportal_field_obj) {
                                    switch ($wpjobportal_field_obj->field) {
                                        case "cell":
                                            $wpjobportal_fieldValue = isset($wpjobportal_resume->cell) ? $wpjobportal_resume->cell : "";
                                            $wpjobportal_data .= $this->getFieldForPersonalSection($wpjobportal_field_obj, $wpjobportal_fieldValue , 3,'',$wpjobportal_themecall);
                                            break;
                                    }
                                }
                            $wpjobportal_data .= '';
                        }
                        $cell_counter = 1;
                        break;
                    case "gender":
                            $wpjobportal_value=isset($wpjobportal_resume->gender)?$wpjobportal_resume->gender:"";
                            $wpjobportal_req = ($wpjobportal_field->required ? 'required' : '');
                            $wpjobportal_fieldValue = WPJOBPORTALformfield::resumeSelect('gender', wpjobportal::$_common->getGender(), $wpjobportal_value,'sec_1', esc_html(__('Select','wp-job-portal')) .' '. esc_html(__('Gender', 'wp-job-portal')), array('class' => 'inputbox form-control wjportal-form-select-field '.esc_attr($this->class_prefix).'-select-field', 'data-validation' => $wpjobportal_req));
                            $wpjobportal_data .= $this->getResumeSelectField($wpjobportal_field, $wpjobportal_fieldValue,'',$wpjobportal_themecall);
                        break;
                    case "photo":
                        $wpjobportal_text = wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle);

                        $wpjobportal_photo_required = ($wpjobportal_field->required ? 'required' : '');
                        $wpjobportal_imgpath = '';

                        $wpjobportal_img = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('jobseeker');
                        $wpjobportal_class = 'none';

                        if (!empty($wpjobportal_resume->photo)) {
                            $wpjobportal_wpdir = wp_upload_dir();
                            $wpjobportal_data_directory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
                            $wpjobportal_img = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/jobseeker/resume_' . $wpjobportal_resume->id . '/photo/' . $wpjobportal_resume->photo;
                            $wpjobportal_class = '';
                        }

                        $wpjobportal_resumephoto = isset($wpjobportal_resume->photo) ? $wpjobportal_resume->photo : null;
                        $wpjobportal_hide_extra_block = '';
                        if($wpjobportal_resumephoto == null){
                            $wpjobportal_hide_extra_block = ' style="display:none;" ';
                        }
                        //starts From there
                        $wpjobportal_fieldvalue = '
                            <div class="wjportal-form-upload">
                                <div class="wjportal-form-upload-btn-wrp">
                                    <span class="wjportal-form-upload-btn-wrp-txt" '.$wpjobportal_hide_extra_block.' >'.$wpjobportal_resumephoto.'
                                    </span>
                                    <span class="wjportal-form-upload-btn">
                                        '.esc_html(__("Upload Image","wp-job-portal")).'
                                        <input type="file" name="photo" class="photo" id="photo" />
                                    </span>
                                </div>
                                <div class="wjportal-form-image-wrp" style="display:'.$wpjobportal_class.'">
                                    <img class="rs_photo wjportal-form-image" id="rs_photo" src="' . esc_url($wpjobportal_img) . '" alt="'.esc_attr(__('Resume image','wp-job-portal')).'"/>
                                    <img id="wjportal-form-delete-image" alt="cross" src="'.esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/no.png" />
                                </div>';
                                $wpjobportal_logoformat = wpjobportal::$_config->getConfigurationByConfigName('image_file_type');
                                $wpjobportal_maxsize = wpjobportal::$_config->getConfigurationByConfigName('resume_photofilesize');
                                $wpjobportal_p_detail = '<div class="wjportal-form-help-txt"> ('.$wpjobportal_logoformat.')</div>';
                                $wpjobportal_p_detail .= '<div class="wjportal-form-help-txt"> ('.esc_html(__("Max logo size allowed","wp-job-portal")).' '.$wpjobportal_maxsize.' Kb)</div>';
                            $wpjobportal_fieldvalue .= $wpjobportal_p_detail;
                        $wpjobportal_fieldvalue .= '</div>';
                        $wpjobportal_data .= $this->wpjobportal_getRowForForm($wpjobportal_text, $wpjobportal_fieldvalue,$wpjobportal_themecall);
                        break;
                    case "resumefiles":

                        $wpjobportal_text = wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle);
                        $wpjobportal_req = ''; // for checking field is required or not
                        if ($wpjobportal_field->required == 1) {
                            $wpjobportal_text .= '<span style="color:red;">*</span>';
                            $wpjobportal_req = 'required';
                        }
                        $wpjobportal_fieldvalue = '<div class="wjportal-form-upload">
                                    <div id="resumefileswrapper" class="wjportal-form-upload-btn-wrp"><span class="livefiles wjportal-form-upload-files">';
                                    $file_names_string = '';
                        if (!empty(wpjobportal::$_data[0]['file_section'])) {
                            foreach (wpjobportal::$_data[0]['file_section'] AS $file) {
                                $wpjobportal_fieldvalue .= '<a href="#" id="file_' . $file->id . '" onclick="deleteResumeFile(' . $file->id . ');" class="file">
                                            <span class="filename wjportal-form-upload-file-name">' . $file->filename . '</span><span class="fileext wjportal-form-upload-file-text"></span>
                                            <img class="filedownload wjportal-form-upload-file-close" src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/resume/cancel.png" />
                                        </a>';
                                $file_names_string .= $file->filename;
                            }
                        }
                        $wpjobportal_fieldvalue .= '</span><span class="clickablefiles wjportal-form-upload-btn">'.esc_html(__('Select Multiple Files','wp-job-portal')).'</span>';

                        $wpjobportal_fieldvalue .= '<input  type="file" id="resumefiles" value="'.$file_names_string.'" placeholder="Choose Resume File" class="wjportal-form-upload-file" name="resumefiles[]" data-validation="' . $wpjobportal_req . '" multiple="true" style="display:none;" />';
                        $wpjobportal_fieldvalue .= '</div>';
                        $wpjobportal_logoformat = wpjobportal::$_config->getConfigurationByConfigName('document_file_type');
                                $wpjobportal_maxsize = wpjobportal::$_config->getConfigurationByConfigName('document_file_size');
                                $wpjobportal_p_detail = '<div class="wjportal-form-help-txt"> ('.$wpjobportal_logoformat.')</div>';
                                $wpjobportal_p_detail .= '<div class="wjportal-form-help-txt"> ('.esc_html(__("Max logo size allowed","wp-job-portal")).' '.$wpjobportal_maxsize.' Kb)</div>';
                            $wpjobportal_fieldvalue .= $wpjobportal_p_detail;
                        $wpjobportal_fieldvalue .= '</div>';
                        $wpjobportal_data .= $this->wpjobportal_getRowForForm($wpjobportal_text, $wpjobportal_fieldvalue,$wpjobportal_themecall);
                        break;
                    case "job_category":
                            $wpjobportal_value=isset($wpjobportal_resume->job_category)?$wpjobportal_resume->job_category:WPJOBPORTALincluder::getJSModel('category')->getDefaultCategoryId();
                            $wpjobportal_req = ($wpjobportal_field->required ? 'required' : '');
                            $wpjobportal_fieldValue = WPJOBPORTALformfield::resumeSelect('job_category', WPJOBPORTALincluder::getJSModel('category')->getCategoryForCombobox(''),$wpjobportal_value,'sec_1', esc_html(__('Select','wp-job-portal')) , array('class' => 'inputbox wjportal-form-select-field  form-control '.esc_attr($this->class_prefix).'-select-field', 'data-validation' => $wpjobportal_req));
                            $wpjobportal_data .= $this->getResumeSelectField($wpjobportal_field, $wpjobportal_fieldValue,'',$wpjobportal_themecall);
                        break;
                    case "jobtype":
                            $wpjobportal_value = isset($wpjobportal_resume->jobtype) ? $wpjobportal_resume->jobtype : WPJOBPORTALincluder::getJSModel('jobtype')->getDefaultJobTypeId();
                            $wpjobportal_req = ($wpjobportal_field->required ? 'required' : '');
                            $wpjobportal_fieldValue = WPJOBPORTALformfield::resumeSelect('jobtype', WPJOBPORTALincluder::getJSModel('jobtype')->getJobTypeForCombo(), $wpjobportal_value,'sec_1', esc_html(__('Select','wp-job-portal')) , array('class' => 'inputbox one wjportal-form-select-field form-control '.esc_attr($this->class_prefix).'-select-field', 'data-validation' => $wpjobportal_req));
                            $wpjobportal_data .= $this->getResumeSelectField($wpjobportal_field, $wpjobportal_fieldValue,'',$wpjobportal_themecall);
                        break;
                    case "nationality":
                            $wpjobportal_value = isset($wpjobportal_resume->nationalityid) ? $wpjobportal_resume->nationalityid : "";
                            $wpjobportal_req = ($wpjobportal_field->required ? 'required' : '');
                            $wpjobportal_fieldValue = WPJOBPORTALformfield::resumeSelect('nationality', WPJOBPORTALincluder::getJSModel('country')->getCountriesForCombo(), $wpjobportal_value,'sec_1', esc_html(__('Select','wp-job-portal')) .' '. esc_html(__('Nationality', 'wp-job-portal')), array('class' => 'inputbox  form-control wjportal-form-select-field '.esc_attr($this->class_prefix).'-select-field', 'data-validation' => $wpjobportal_req));;
                            $wpjobportal_data .= $this->getResumeSelectField($wpjobportal_field, $wpjobportal_fieldValue,'',$wpjobportal_themecall);
                        break;
                    case 'salaryfixed':
                            $wpjobportal_salaryfixed_require = ($wpjobportal_field->required ? 'required' : '');
                            $wpjobportal_fieldValue = isset($wpjobportal_resume->salaryfixed) ? $wpjobportal_resume->salaryfixed : "";
                            $wpjobportal_data .= $this->getFieldForPersonalSection($wpjobportal_field, $wpjobportal_fieldValue,'','',$wpjobportal_themecall);
                        break;
                    case 'tags':
                        if(in_array('tag', wpjobportal::$_active_addons)){
                            $wpjobportal_value = isset($wpjobportal_resume->resumetags) ? $wpjobportal_resume->resumetags : '';
                            $wpjobportal_data .= $this->getFieldForPersonalSection($wpjobportal_field,$wpjobportal_value,'','',$wpjobportal_themecall);
                            wp_register_script( 'wpjobportal-inline-handle', '' );
                            wp_enqueue_script( 'wpjobportal-inline-handle' );
                            $wpjobportal_inline_js_script = '
                                            jQuery(document).ready(function(){
                                                getTokenInputTags(' . $wpjobportal_value . ');
                                            });';
                            wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
                        }
                    break;
                    case "searchable":
                        if($wpjobportal_searchable_counter == 0){
                            $wpjobportal_value = isset($wpjobportal_resume->searchable) ? $wpjobportal_resume->searchable : "";
                            $wpjobportal_data .= $this->getResumeCheckBoxField($wpjobportal_field, $wpjobportal_value);
                        }
                        $wpjobportal_searchable_counter = 1;
                    break;
                    case 'termsandconditions':
                        if (isset(wpjobportal::$wpjobportal_data['resumeid']) && is_numeric(wpjobportal::$wpjobportal_data['resumeid'])) {
                        }else{
                            $this->show_terms_and_conditions = 1;
                            $this->terms_and_conditions_title = $wpjobportal_field->fieldtitle;
                        }
                    break;

                    default:
                        $wpjobportal_data .= $this->getResumeFormUserField($wpjobportal_field, $wpjobportal_resume , 1 ,  0 , '',$wpjobportal_themecall);
                    break;
                }
            }
        // if ($wpjobportal_sectionmoreoption == 1) {
        //     $wpjobportal_data .= '</div>'; // closing div for the more option
        // }
        $wpjobportal_data .= '</div>'; // to handle background color of scetions
        return $wpjobportal_data;
    }

    function printResume($wpjobportal_themecall=null) {

        //check wheather to show resume form or resumeformview
        $wpjobportal_resumeformview = 1; // for add case
        if (isset(wpjobportal::$wpjobportal_data['resumeid']) && is_numeric(wpjobportal::$wpjobportal_data['resumeid'])) {
            $wpjobportal_resumeformview = 0; // for edit case
            $wpjobportal_resumeid=wpjobportal::$wpjobportal_data['resumeid'];
        }
        if(wpjobportal::$wpjobportal_theme_chk == 1 && !wpjobportal::$_common->wpjp_isadmin()){
            $this->class_prefix = 'wpj-jp-form-wrp wpj-jp-resume-form';
            $this->themecall = 1;
            $wpjobportal_themecall = 1;
        }else{
            $this->class_prefix = 'wjportal-form-wrp wjportal-resume-form';
        }
        $wpjobportal_html = '<div id="resume-wrapper" class="'.esc_attr($this->class_prefix).'">';
        $wpjobportal_form_class="wjportal-form";
        if(1 == $wpjobportal_themecall){
            // $wpjobportal_html='<div class="jsjb-jm-form-wrap">';
            // $wpjobportal_form_class="jsjb-jm-form";
        }elseif(2 == $wpjobportal_themecall){
            $wpjobportal_html='<div class="jsjb-jh-form-wrap">';
            $wpjobportal_form_class="jsjb-jh-form";
        }
        $wpjobportal_check = apply_filters('wpjobportal_addons_multiresume_add',false,$wpjobportal_form_class);
        if($wpjobportal_check == false){
            $wpjobportal_html .= '<form class="'.$wpjobportal_form_class.'" id="resumeform" method="post" enctype="multipart/form-data" action="'.wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'task'=>'saveresume')).'" >';

        }else{
            $wpjobportal_html .= $wpjobportal_check;
        }
        if (!isset(wpjobportal::$_data[0]['personal_section']->uid)) {
            $wpjobportal_isowner = 1; // user come to add new resume
        } else {
            $wpjobportal_isowner = (WPJOBPORTALincluder::getObjectClass('user')->uid() == wpjobportal::$_data[0]['personal_section']->uid) ? 1 : 0;
        }
        foreach ($this->resumefields AS $wpjobportal_field) {
            if($wpjobportal_field->published == 1 || (WPJOBPORTALincluder::getObjectClass('user')->isguest() && $wpjobportal_field->isvisitorpublished == 1)){// disabling field for user also disabled them for visitor
                switch ($wpjobportal_field->field){
                    case 'section_personal':
                        $title = 'Personal Information';
                        $title = $wpjobportal_field->fieldtitle;
                        $wpjobportal_html .= $this->getSectionTitle('personal', $title , 1,$wpjobportal_themecall);
                        $wpjobportal_html .= $this->makePersonalSectionFields($wpjobportal_themecall);
                    break;
                    case 'section_address':
                        $title = 'Address';
                        $title = $wpjobportal_field->fieldtitle;
                        $wpjobportal_html .= apply_filters('wpjobportal_addons_section_wise_form',false,'getSectionTitle','address',$title,'3',$wpjobportal_themecall);
                        $wpjobportal_html .= apply_filters('wpjobportal_addons_selection_fields',false,'makeAddressSectionFields',$wpjobportal_themecall);
                    break;
                    case 'section_education':
                        $title = 'Education';
                        $title = $wpjobportal_field->fieldtitle;
                        $wpjobportal_html .= apply_filters('wpjobportal_addons_section_wise_form',false,'getSectionTitle','education',$title,'3',$wpjobportal_themecall);
                        $wpjobportal_html .= apply_filters('wpjobportal_addons_selection_fields',false,'makeInstituteSectionFields',$wpjobportal_themecall);
                        break;
                    case 'section_employer':
                        $title = 'Employer';
                        $title = $wpjobportal_field->fieldtitle;
                        $wpjobportal_html .= apply_filters('wpjobportal_addons_section_wise_form',false,'getSectionTitle','employer',$title,'3',$wpjobportal_themecall);
                        $wpjobportal_html .= apply_filters('wpjobportal_addons_selection_fields',false,'makeEmployerSectionFields',$wpjobportal_themecall);
                        break;
                    case 'section_skills':
                        if(in_array('advanceresumebuilder', wpjobportal::$_active_addons)){
                            $title = 'Skills';
                            $title = $wpjobportal_field->fieldtitle;
                            $wpjobportal_html .= $this->getSectionTitle('skills', $title, 5,$wpjobportal_themecall);
                            $wpjobportal_html .= apply_filters('wpjobportal_addons_selection_fields',false,'makeSkillsSectionFields',$wpjobportal_themecall);
                        }
                        break;
                    case 'section_language':
                        if(in_array('advanceresumebuilder', wpjobportal::$_active_addons)){
                            $title = 'Language';
                            $title = $wpjobportal_field->fieldtitle;
                            $wpjobportal_html .= $this->getSectionTitle('language', $title, 8,$wpjobportal_themecall);
                            $wpjobportal_html .=apply_filters('wpjobportal_addons_selection_fields',false,'makeLanguageSectionFields',$wpjobportal_themecall);
                        }
                        break;

                    default:
                        if($wpjobportal_field->is_section_headline == 1){ // to print resume custom sections
                            if($wpjobportal_field->field != 'section_resume'){ // avoid resume editor section legacy code issue
                                $title = $wpjobportal_field->fieldtitle;
                                $wpjobportal_sectionid = $wpjobportal_field->section;
                                $wpjobportal_html .= $this->getSectionTitle($wpjobportal_field->field, $title, $wpjobportal_sectionid,$wpjobportal_themecall);
                                $wpjobportal_html .= apply_filters('wpjobportal_addons_selection_fields_custom_sections',false,'makeCustomResumeSectionFields',$wpjobportal_sectionid,$wpjobportal_themecall);
                            }
                        }
                    break;
                }
            }
        }
        $wpjobportal_protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $wpjobportal_html .= '<div class="wpjp-resume-section-button">'.wp_enqueue_script('wpjobportal-repactcha-script', $wpjobportal_protocol.'www.google.com/recaptcha/api.js');
        if(current_user_can('manage_options')){
            $one = '';
            $two = '';
            $three = '';
            $four = '';
            if(isset(wpjobportal::$_data[0]['personal_section']->status)){
                if(wpjobportal::$_data[0]['personal_section']->status == 1){
                    $one = ' selected ';
                }elseif(wpjobportal::$_data[0]['personal_section']->status == 0){
                    $two = ' selected ';
                }elseif(wpjobportal::$_data[0]['personal_section']->status == -1){
                    $three = ' selected ';
                }elseif(wpjobportal::$_data[0]['personal_section']->status == 3){
                    $four = ' selected ';
                }
            }
            $wpjobportal_status = isset(wpjobportal::$_data[0]['personal_section']->status) ? wpjobportal::$_data[0]['personal_section']->status : '';
            $wpjobportal_html .= '
                <div class="wjportal-form-row">
                    <div class="wjportal-form-title">
                        <label id="total_experiencemsg" class="row-title" for="status">'.esc_html(__('Status','wp-job-portal')).'</label>
                    </div>
                    <div class="wjportal-form-value">
                    <select id="status" name="sec_1[status]" class="wjportal-form-select-field">
                        <option ';
                        $wpjobportal_selected = ($wpjobportal_status == 1) ? 'selected="selected"' : '';
            $wpjobportal_html .=    $wpjobportal_selected.' value="1" '.$one.'>'.esc_html(__('Approved','wp-job-portal')).'</option>
                        <option ';
                        $wpjobportal_selected = ($wpjobportal_status == 0) ? 'selected="selected"' : '';
            $wpjobportal_html .=    $wpjobportal_selected.' value="0" '.$two.'>'.esc_html(__('Pending','wp-job-portal')).'</option>
                        <option ';
                        $wpjobportal_selected = ($wpjobportal_status == -1) ? 'selected="selected"' : '';
            $wpjobportal_html .=    $wpjobportal_selected.' value="-1" '.$three.'>'.esc_html(__('Reject','wp-job-portal')).'</option>
                        <option ';
                        $wpjobportal_selected = ($wpjobportal_status == 3) ? 'selected="selected"' : '';
            $wpjobportal_html .=    $wpjobportal_selected.' value="3" '.$four.'>'.esc_html(__('Pending Payment','wp-job-portal')).'</option>
                    </select></div>
                </div>
                ';
        }
        $wpjobportal_isvisitor=false;
        if(isset($_COOKIE['wpjobportal_apply_visitor'])){
            if (!is_user_logged_in()) {
                $wpjobportal_isvisitor=true;
            }
        }
        $wpjobportal_google_recaptcha_3 = false;
        $wpjobportal_config_array = wpjobportal::$_config->getConfigByFor('captcha');
        if (!is_user_logged_in() && $wpjobportal_config_array['resume_captcha'] == 1) {
            if ($wpjobportal_config_array['captcha_selection'] == 1) { // Google recaptcha
                if($wpjobportal_config_array['recaptcha_version'] == 1){
                    $wpjobportal_html .= '<div class="g-recaptcha" data-sitekey="'.$wpjobportal_config_array["recaptcha_publickey"].'"></div>';
                }else{
                    $wpjobportal_google_recaptcha_3 = true;
                }

            } else { // own captcha
                $wpjobportal_captcha = new WPJOBPORTALcaptcha;
                $wpjobportal_html .= '<div class="recaptcha-wrp">'.$wpjobportal_captcha->getCaptchaForForm().'</div>';
            }
        }

        if($this->show_terms_and_conditions == 1){
            $wpjobportal_termsandconditions_link = get_the_permalink(wpjobportal::$_configuration['terms_and_conditions_page_resume']);
            $wpjobportal_html .='
                <div class="js-col-md-12 js-form-wrapper wjportal-terms-and-conditions-wrap wpjobportal-terms-and-conditions-wrap" data-wpjobportal-terms-and-conditions="1" >
                    <div class="js-col-md-12 js-form-value">
                        '.WPJOBPORTALformfield::checkbox('termsconditions', array('1' => wpjobportal::wpjobportal_getVariableValue($this->terms_and_conditions_title)), 0, array('class' => 'checkbox')).'
                        <a title="'. esc_attr(__('Terms and Conditions','wp-job-portal')).'" href="'. esc_url($wpjobportal_termsandconditions_link).'" target="_blank" >
                        <img alt="'. esc_html(__('Terms and Conditions','wp-job-portal')).'" title="'. esc_attr(__('Terms and Conditions','wp-job-portal')).'" src="'. esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/widget-link.png" /></a>
                    </div>
                </div>

            ';
        }
        $created = isset(wpjobportal::$_data[0]['personal_section']->created) ? wpjobportal::$_data[0]['personal_section']->created : gmdate('Y-m-d H:i:s');
        $wpjobportal_html .= '<div class="wpjp-resume-form-btn-wrp">
                <input type="hidden" id="created" name="sec_1[created]" value="'.esc_attr($created).'">';
            $wpjobportal_html .=WPJOBPORTALformfield::hidden('id', isset(wpjobportal::$_data[0]['personal_section']->id) ? wpjobportal::$_data[0]['personal_section']->id : '' );
            if(isset(wpjobportal::$_data[0]['personal_section']->uid) && ""!=wpjobportal::$_data[0]['personal_section']->uid){
                $wpjobportal_uid=wpjobportal::$_data[0]['personal_section']->uid;
            } else{
                $wpjobportal_uid=WPJOBPORTALincluder::getObjectClass('user')->uid();
            }
            //$wpjobportal_html .= '<input type="hidden" id="uid" name="sec_1[uid]" value="'.esc_attr($wpjobportal_uid).'">';
            $wpjobportal_html .=WPJOBPORTALformfield::hidden('uid', esc_html($wpjobportal_uid));
            $wpjobportal_html .=WPJOBPORTALformfield::hidden('action', 'resume_saveresume');
            $wpjobportal_html .=WPJOBPORTALformfield::hidden('wpjobportalpageid',esc_html( get_the_ID()));
            $wpjobportal_html .=WPJOBPORTALformfield::hidden('creditid', '');
            $wpjobportal_html .=WPJOBPORTALformfield::hidden('upakid', isset(wpjobportal::$_data['package']) ? esc_html(wpjobportal::$_data['package']->id) : 0);
            $wpjobportal_html .=WPJOBPORTALformfield::hidden('form_request', 'wpjobportal');
            $wpjobportal_html .=WPJOBPORTALformfield::hidden('resume_logo_deleted', '');
            $wpjobportal_html .=WPJOBPORTALformfield::hidden('_wpnonce', esc_html(wp_create_nonce('wpjobportal_resume_nonce')));
            $wpjobportal_html .='<div class="wjportal-form-btn-wrp" id="save-button">';
            $guestallowed = 0;
            if (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
                $guestallowed = wpjobportal::$_config->getConfigurationByConfigName('visitor_can_add_resume');
            }
            if(!wpjobportal::$_common->wpjp_isadmin()){ // site
                if(in_array('multiresume', wpjobportal::$_active_addons)){
                    $cancel_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'multiresume', 'wpjobportallt'=>'myresumes'));
                }else{
                    //$cancel_link=wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobseeker'));
                    $cancel_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'myresumes'));// to redirect on cancel to my resumes
                }
            }elseif(wpjobportal::$_common->wpjp_isadmin()){
                $cancel_link = esc_url_raw(admin_url("admin.php?page=wpjobportal_resume"));
            }
            $wpjobportal_btn_cancel=false;
            if(!$wpjobportal_isvisitor && is_user_logged_in() ){
                $wpjobportal_btn_cancel=true;
            }
            if($wpjobportal_btn_cancel==true)  {
                $wpjobportal_html .= '<div class="wjportal-form-2-btn">';
            }

            if($wpjobportal_google_recaptcha_3 == false){ // to handle case of google recpatcha version 3
                if ($wpjobportal_isvisitor &&  !wpjobportal::$_common->wpjp_isadmin()) {
                    $wpjobportal_html .= '<input class="'.esc_attr($this->class_prefix).'-btn-primary wjportal-form-btn wjportal-form-save-btn" type="button" onclick="submitresume();" value="' . esc_html(__('Apply Now', 'wp-job-portal')) . '"/>';
                } else {
                    $wpjobportal_html .= '<input class="'.esc_attr($this->class_prefix).'-btn-primary wjportal-form-btn wjportal-form-save-btn" type="button" onclick="submitresume();" value="' . esc_html(__("Save Resume", "wp-job-portal")) . '"/>';
                }
            }else{
                if ($wpjobportal_isvisitor &&  !wpjobportal::$_common->wpjp_isadmin()) {
                    $wpjobportal_html .= '<input class="'.esc_attr($this->class_prefix).'-btn-primary wjportal-form-btn wjportal-form-save-btn g-recaptcha" data-sitekey="'. esc_attr($wpjobportal_config_array['recaptcha_publickey']).'" data-callback="onSubmit" data-action="submit" type="button" value="' . esc_html(__('Apply Now', 'wp-job-portal')) . '"/>';
                } else {
                    $wpjobportal_html .= '<input class="'.esc_attr($this->class_prefix).'-btn-primary wjportal-form-btn wjportal-form-save-btn g-recaptcha" data-sitekey="'. esc_attr($wpjobportal_config_array['recaptcha_publickey']).'" data-callback="onSubmit" data-action="submit" type="button"  value="' . esc_html(__("Save Resume", "wp-job-portal")) . '"/>';
                }
            }

            if($wpjobportal_btn_cancel==true)  $wpjobportal_html .= '</div>';
            if(!$wpjobportal_isvisitor && is_user_logged_in() ){
                if($wpjobportal_btn_cancel==true)  $wpjobportal_html .= '<div class="wjportal-form-2-btn-cancel">';
                    $wpjobportal_html .= '<a class="resume_submits cancel '.esc_attr($this->class_prefix).' wjportal-form-btn wjportal-form-cancel-btn" href="'.esc_url($cancel_link).'">' . esc_html(__('Cancel Resume', 'wp-job-portal')) . '</a>';
                if($wpjobportal_btn_cancel==true)  {
                        $wpjobportal_html .= '</div>';
                }
            }
        $wpjobportal_html .= '</div>';
        $wpjobportal_html .= '</div>';
        $wpjobportal_html .= '</form>';
        $wpjobportal_html .= '</div>';// section wrapper end;

        //echo wp_kses($wpjobportal_html,WPJOBPORTAL_ALLOWED_TAGS);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Large HTML form output, escaping handled at field level
        echo $wpjobportal_html;

        /*if (isset(wpjobportal::$_data[0]) && isset(wpjobportal::$_data[0]['personal_section'])) {
            $wpjobportal_viewtags = wpjobportal::$_data[0]['personal_section']->viewtags;
        } else {
            $wpjobportal_viewtags = '';
        }
        $wpjobportal_viewtags = apply_filters('wpjobportal_addons_makeanchor_for_tags',false,$wpjobportal_viewtags);
        echo esc_attr($wpjobportal_viewtags);*/
    }

    function getRowForView($wpjobportal_text, $wpjobportal_value, &$wpjobportal_i) {
        $wpjobportal_html = '';
        if ($wpjobportal_i == 0 || $wpjobportal_i % 2 == 0) {
            $wpjobportal_html .= '<div class="wpjp-resume-row-wrp">';
        }
        $wpjobportal_html .= '<div class="resume-row-wrapper">
                    <div class="wpjp-form-title">' . $wpjobportal_text . ':</div>
                    <div class="wpjp-form-value">' . wpjobportal::wpjobportal_getVariableValue($wpjobportal_value) . '</div>
                </div>';
        $wpjobportal_i++;
        if ($wpjobportal_i % 2 == 0) {
            $wpjobportal_html .= '</div>';
        }
        return $wpjobportal_html;
    }

    function wpjobportal_getRowForForm($wpjobportal_text, $wpjobportal_value,$wpjobportal_themecall=null) {
        if(null != $wpjobportal_themecall){
            $wpjobportal_html = '<div class="wjportal-form-row">
                <div class="wjportal-form-title">' . $wpjobportal_text . ':</div>
                <div class="wjportal-form-value">' . $wpjobportal_value . '</div>
            </div>';
        }else{
            $wpjobportal_html = '<div class="wjportal-form-row">
                <div class="wjportal-form-title">' . $wpjobportal_text . ':</div>
                <div class="wjportal-form-value">' . $wpjobportal_value . '</div>
            </div>';
        }
        return $wpjobportal_html;
    }

    function getHeadingRowForView($wpjobportal_value) {
        $wpjobportal_html = '<div class="resume-heading-row">' . $wpjobportal_value . '</div>';
        return $wpjobportal_html;
    }
}

?>
