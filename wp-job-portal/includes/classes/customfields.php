<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALcustomfields {
    public $class_prefix = '';

    function __construct(){
        if(wpjobportal::$wpjobportal_theme_chk == 1){
            $this->class_prefix = 'jsjb-jm';
        }elseif(wpjobportal::$wpjobportal_theme_chk == 2){
            $this->class_prefix = 'jsjb-jh';
        }
    }

    function formCustomFieldsResume($wpjobportal_field , $obj_id, $obj_params ,$wpjobportal_resumeform=null,  $wpjobportal_section = null, $wpjobportal_sectionid = null, $wpjobportal_ishidden = null,$wpjobportal_themecall=null){
        //had to do this so that there are minimum changes in resume code
        $wpjobportal_field = $this->userFieldData($wpjobportal_field->field, 5, $wpjobportal_section);
        //$wpjobportal_field = $this->userFieldData($wpjobportal_field, 5, $wpjobportal_section);
        if (empty($wpjobportal_field)) {
            return '';
        }
        //if(!in_array('customfield', wpjobportal::$_active_addons)){
        //    if($wpjobportal_field->userfieldtype != 'text' && $wpjobportal_field->userfieldtype != 'email'){
        //        return '';
        //    }
        //}
        $wpjobportal_visibleclass = "";
        if (isset($wpjobportal_field->visibleparams) && $wpjobportal_field->visibleparams != ''){
            $wpjobportal_visibleclass = " visible js-form-custm-flds-wrp";
        }
        $wpjobportal_themebfclass = " ".$this->class_prefix."-bigfont ";
        if(null != $wpjobportal_themecall){
            $wpjobportal_div1 = 'resume-row-wrapper form wjportal-form-row';
            $wpjobportal_div2 = 'row-title wjportal-form-title';
            $wpjobportal_div3 = 'row-value wjportal-form-value';
        }else{
            $wpjobportal_div1 = 'resume-row-wrapper form wjportal-form-row';
            $wpjobportal_div2 = 'row-title wjportal-form-title';
            $wpjobportal_div3 = 'row-value wjportal-form-value';

        }
        $wpjobportal_div1 .= $wpjobportal_visibleclass;
        $cssclass = "";
        $wpjobportal_required = $wpjobportal_field->required;
        $wpjobportal_html = '<div class="' . $wpjobportal_div1 . '">
               <div class="' . $wpjobportal_div2 . '">';
        if ($wpjobportal_required == 1) {
            $wpjobportal_html .= wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle) . ' <font color="red"> *</font>';
            // if ($wpjobportal_field->userfieldtype == 'email'){
            //     //$cssclass = "required validate-email";
            //     if($wpjobportal_section AND $wpjobportal_section == null){ // too handle bug related to sub section email field
            //         $cssclass = "required email";
            //     }
            // }else{
                $cssclass = " required ";
            // }
        }else {
            $wpjobportal_html .= wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle);
            // if ($wpjobportal_field->userfieldtype == 'email'){
            //     if($wpjobportal_section AND $wpjobportal_section == null){ // too handle bug related to sub section email field
            //         //$cssclass = "validate-email";
            //         $cssclass = "required email";
            //     }
            // }else{
                $cssclass = "";
            // }
        }
        $wpjobportal_html .= ' </div><div class="' . $wpjobportal_div3 . '">';

        $wpjobportal_resumeTitle = wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle);

        $size = '';
        $wpjobportal_maxlength = '';
        if(isset($wpjobportal_field->size) && 0!=$wpjobportal_field->size){
            $size = $wpjobportal_field->size;
        }
        if(isset($wpjobportal_field->maxlength) && 0!=$wpjobportal_field->maxlength){
            $wpjobportal_maxlength = $wpjobportal_field->maxlength;
        }

        $fvalue = "";
        $wpjobportal_value = "";
        $wpjobportal_userdataid = "";
        $wpjobportal_value = $obj_params;

        if($wpjobportal_value){ // data has been stored
            $wpjobportal_userfielddataarray = json_decode($wpjobportal_value);
            $wpjobportal_valuearray = json_decode($wpjobportal_value,true);
        }else{
            $wpjobportal_valuearray = array();
        }
        if(is_array($wpjobportal_valuearray) && array_key_exists($wpjobportal_field->field, $wpjobportal_valuearray)){
            $wpjobportal_value = $wpjobportal_valuearray[$wpjobportal_field->field];
        }else{
            $wpjobportal_value = '';
        }
        $wpjobportal_user_field = '';
        if($wpjobportal_themecall != null){
            $wpjobportal_theme_string = ', '. $wpjobportal_themecall;
        }else{
            $wpjobportal_theme_string = '';
        }

        $specialClass='';
        if($wpjobportal_value != ''){
            $specialClass = ' specialClass ';
        }

        switch ($wpjobportal_field->userfieldtype) {
            case 'text':
                if(wpjobportal::$wpjobportal_theme_chk == 1){
                    $wpjobportal_themeclass = getJobManagerThemeClass('text');
                }else{
                    $wpjobportal_themeclass = '';
                }
                $wpjobportal_extraattr = array('class' => "inputbox one ".$cssclass.$specialClass.$wpjobportal_themeclass." wjportal-form-input-field", 'data-validation' => $cssclass, 'size' => $size, 'maxlength' => $wpjobportal_maxlength, 'placeholder'=>$wpjobportal_field->placeholder);
                // handleformresume
                if($wpjobportal_section AND $wpjobportal_section != 1){
                    if($wpjobportal_ishidden){
                        if ($wpjobportal_required == 1) {
                            $wpjobportal_extraattr['data-validation'] = '';
                            $wpjobportal_extraattr['data-myrequired'] = $cssclass;
                            $wpjobportal_extraattr['class'] = "inputbox one ".$wpjobportal_themeclass.$specialClass." wjportal-form-input-field";
                        }
                    }
                }
                //END handleformresume
                $wpjobportal_user_field .= $this->textResume($wpjobportal_field->field, $wpjobportal_value, $wpjobportal_extraattr, $wpjobportal_section , $wpjobportal_sectionid);
            break;
            case 'email':
                if(wpjobportal::$wpjobportal_theme_chk == 1){
                    $wpjobportal_themeclass = getJobManagerThemeClass('text');
                }else{
                    $wpjobportal_themeclass = '';
                }
                $wpjobportal_extraattr = array('class' => "inputbox one ".$cssclass.$specialClass.$wpjobportal_themeclass." wjportal-form-input-field", 'data-validation' => $cssclass, 'size' => $size, 'maxlength' => $wpjobportal_maxlength, 'placeholder'=>$wpjobportal_field->placeholder);
                // handleformresume
                if($wpjobportal_section AND $wpjobportal_section != 1){
                    if($wpjobportal_ishidden){
                        if ($wpjobportal_required == 1) {
                            $wpjobportal_extraattr['data-validation'] = '';
                            $wpjobportal_extraattr['data-myrequired'] = $cssclass;
                            $wpjobportal_extraattr['class'] = "inputbox one $wpjobportal_themeclass wjportal-form-input-field";
                        }
                    }
                }
                //END handleformresume
                $wpjobportal_user_field .= $this->emailResume($wpjobportal_field->field, $wpjobportal_value, $wpjobportal_extraattr, $wpjobportal_section , $wpjobportal_sectionid);
            break;
            case 'date':
                    if(wpjobportal::$wpjobportal_theme_chk == 1){
                        $wpjobportal_themeclass = getJobManagerThemeClass('text');
                    }else{
                        $wpjobportal_themeclass = '';
                    }
                    $wpjobportal_req=($wpjobportal_field->required==1)?"required":"";
                    $wpjobportal_extraattr = array('class' => 'inputbox wjportal-form-date-field custom_date '.$specialClass.' cal_userfield  '.$wpjobportal_themeclass.' '.$cssclass, 'size' => '10', 'maxlength' => '19','data-validation'=>$wpjobportal_req,'autocomplete'=>'off', 'placeholder'=>$wpjobportal_field->placeholder);
                    // handleformresume
                    if($wpjobportal_section AND $wpjobportal_section != 1){
                        if($wpjobportal_ishidden){
                            if ($wpjobportal_required == 1) {
                                $wpjobportal_extraattr['data-validation'] = '';
                                $wpjobportal_extraattr['data-myrequired'] = $cssclass;
                                $wpjobportal_extraattr['class'] = "inputbox wjportal-form-date-field custom_date ".$specialClass." cal_userfield  ".$wpjobportal_themeclass." ".$cssclass;
                            }
                        }
                    }
                    //END handleformresume
                    $wpjobportal_user_field .= $this->dateResume($wpjobportal_field->field, $wpjobportal_value, $wpjobportal_extraattr, $wpjobportal_section , $wpjobportal_sectionid);
            break;
            case 'textarea':
                $wpjobportal_rows = '';
                $cols = '';
                if(isset($wpjobportal_field->rows)){
                    $wpjobportal_rows = $wpjobportal_field->rows;
                }
                if(isset($wpjobportal_field->cols)){
                    $cols = $wpjobportal_field->cols;
                }
                if(wpjobportal::$wpjobportal_theme_chk == 1){
                    $wpjobportal_themeclass = getJobManagerThemeClass('textarea');
                }else{
                    $wpjobportal_themeclass = '';
                }

                $wpjobportal_extraattr = array('class' => "inputbox wpjobportal-form-textarea-field one ".$cssclass.$specialClass.$wpjobportal_themeclass, 'data-validation' => $cssclass, 'rows' => $wpjobportal_rows, 'cols' => $cols);
                // handleformresume
                if($wpjobportal_section AND $wpjobportal_section != 1){
                    if($wpjobportal_ishidden){
                        if ($wpjobportal_required == 1) {
                            $wpjobportal_extraattr['data-validation'] = '';
                            $wpjobportal_extraattr['data-myrequired'] = $cssclass;
                            $wpjobportal_extraattr['class'] = "inputbox ".$specialClass." one";
                        }
                    }
                }
                //END handleformresume

                $wpjobportal_user_field .= $this->textareaResume($wpjobportal_field->field, $wpjobportal_value, $wpjobportal_extraattr , $wpjobportal_section , $wpjobportal_sectionid);
            break;
            case 'checkbox':
                if (!empty($wpjobportal_field->userfieldparams)) {
                    $wpjobportal_comboOptions = array();
                    $obj_option = json_decode($wpjobportal_field->userfieldparams);
                    $wpjobportal_i = 0;
                    $wpjobportal_valuearray = wpjobportalphplib::wpJP_explode(', ',$wpjobportal_value);
                    $wpjobportal_name = $wpjobportal_field->field;
                    if (wpjobportalphplib::wpJP_strpos($wpjobportal_name, '[]') !== false) {
                        $wpjobportal_id = wpjobportalphplib::wpJP_str_replace('[]', '', $wpjobportal_name);
                    }else{
                        $wpjobportal_id = $wpjobportal_name;
                    }
                    $wpjobportal_data_required = '';
                    if($wpjobportal_section){
                        if($wpjobportal_section != 1){
                            if($wpjobportal_ishidden){
                                if($wpjobportal_required == 1){
                                    $wpjobportal_data_required = 'data-myrequired="required"';
                                    $cssclass = '';
                                }
                            }
                            $wpjobportal_name = 'sec_'.$wpjobportal_section.'['.$wpjobportal_name.']['.$wpjobportal_sectionid.']';
                            $wpjobportal_id .=$wpjobportal_sectionid;
                        }else{
                            $wpjobportal_name = 'sec_'.$wpjobportal_section.'['.$wpjobportal_name.']';
                        }
                    }

                    $wpjobportal_jsFunction = '';
                    if ($wpjobportal_required == 1) {
                        $wpjobportal_jsFunction = "deRequireUfCheckbox('" . $wpjobportal_field->field . "');";
                    }
                  	if(is_array($obj_option)){
                      foreach ($obj_option AS $wpjobportal_option) {
                          $wpjobportal_check = '';
                          if(in_array($wpjobportal_option, $wpjobportal_valuearray)){
                              $wpjobportal_check = 'checked';
                          }
                          $wpjobportal_user_field .= '<span class="wpjobportal-form-radio-field">';
                          $wpjobportal_user_field .= '<input type="checkbox" ' . $wpjobportal_check . ' '.$wpjobportal_data_required.' class="'. $wpjobportal_field->field .' radiobutton uf_of_type_ckbox '.$cssclass. $specialClass.'" value="' . wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_option) . '" id="' . $wpjobportal_id . '_' . $wpjobportal_i . '" name="' . $wpjobportal_name . '[]" data-validation="'.esc_attr($cssclass).'" onclick = "' . $wpjobportal_jsFunction . '" ckbox-group-name="' . $wpjobportal_field->field . '">';
                          $wpjobportal_user_field .= '<label class="cf_chkbox" for="' . $wpjobportal_id . '_' . $wpjobportal_i . '" id="foruf_checkbox1">' . $wpjobportal_option . '</label>';
                          $wpjobportal_user_field .= '</span>';
                          $wpjobportal_i++;
                      }
                  	}
                  
                } else {
                    $wpjobportal_comboOptions = array('1' => wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle));
                    $wpjobportal_extraattr = array('class' => "radiobutton $cssclass");
                    // handleformresume
                    if($wpjobportal_section AND $wpjobportal_section != 1){
                        if($wpjobportal_ishidden){
                            if ($wpjobportal_required == 1) {
                                $wpjobportal_extraattr['data-validation'] = '';
                                $wpjobportal_extraattr['data-myrequired'] = $cssclass;
                                $wpjobportal_extraattr['class'] = "radiobutton";
                            }
                        }
                    }
                    //END handleformresume
                    $wpjobportal_user_field .= $this->checkboxResume($wpjobportal_field->field, $wpjobportal_comboOptions, $wpjobportal_value, array('class' => "radiobutton $cssclass") , $wpjobportal_section , $wpjobportal_sectionid);
                }
            break;
            case 'radio':
                $wpjobportal_comboOptions = array();
                if (!empty($wpjobportal_field->userfieldparams)) {
                    $obj_option = json_decode($wpjobportal_field->userfieldparams);
                  	if(is_array($obj_option)){
                      for ($wpjobportal_i = 0; $wpjobportal_i < count($obj_option); $wpjobportal_i++) {
                          $wpjobportal_comboOptions[$obj_option[$wpjobportal_i]] = wpjobportal::wpjobportal_getVariableValue($obj_option[$wpjobportal_i]);
                      }
                  }
                }
                $wpjobportal_jsFunction = '';
                if ($wpjobportal_field->depandant_field != null) {
                    $wpjobportal_jsFunction = "getDataForDepandantFieldResume('" . $wpjobportal_field->field . "','" . $wpjobportal_field->depandant_field . "',2,'".$wpjobportal_section."','".$wpjobportal_sectionid."'". $wpjobportal_theme_string.");";
                }
                $wpjobportal_extraattr = array('class' => "cf_radio radiobutton $cssclass".$specialClass , 'data-validation' => $cssclass, 'onclick' => $wpjobportal_jsFunction);
                // handleformresume
                if($wpjobportal_section AND $wpjobportal_section != 1){
                    if($wpjobportal_ishidden){
                        if ($wpjobportal_required == 1) {
                            $wpjobportal_extraattr['data-validation'] = '';
                            $wpjobportal_extraattr['data-myrequired'] = $cssclass;
                            $wpjobportal_extraattr['class'] = "cf_radio radiobutton";
                        }
                    }
                }
                //END handleformresume

                $wpjobportal_user_field .= $this->radiobuttonResume($wpjobportal_field->field, $wpjobportal_comboOptions, $wpjobportal_value, $wpjobportal_extraattr , $wpjobportal_section , $wpjobportal_sectionid);
            break;
            case 'combo':
                if(wpjobportal::$wpjobportal_theme_chk == 1){
                    $wpjobportal_themeclass = getJobManagerThemeClass('select');
                }else{
                    $wpjobportal_themeclass = '';
                }
                $wpjobportal_comboOptions = array();
                if (!empty($wpjobportal_field->userfieldparams)) {
                    $obj_option = json_decode($wpjobportal_field->userfieldparams);
                    foreach ($obj_option as $opt) {
                        $wpjobportal_comboOptions[] = (object) array('id' => $opt, 'text' => wpjobportal::wpjobportal_getVariableValue($opt));
                    }
                }
                //code for handling dependent field
                $wpjobportal_jsFunction = '';
                if ($wpjobportal_field->depandant_field != null) {
                    $wpjobportal_jsFunction = "getDataForDepandantFieldResume('" . $wpjobportal_field->field . "','" . $wpjobportal_field->depandant_field . "',1,'".$wpjobportal_section."','".$wpjobportal_sectionid."'". $wpjobportal_theme_string.");";
                }
                if ($wpjobportal_field->placeholder != '') {
                    $placeholder = $wpjobportal_field->placeholder;
                } else {
                    $placeholder = esc_html(__('Select', 'wp-job-portal')) . ' ' . esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle));
                }
                //end
                //code for handling visible field
                $wpjobportal_jsVisibleFunction = '';
                if ($wpjobportal_field->visible_field != null) {
                    $wpjobportal_visibleparams = WPJOBPORTALincluder::getJSModel('fieldordering')->getDataForVisibleField($wpjobportal_field->visible_field);
                    foreach ($wpjobportal_visibleparams as $wpjobportal_visibleparam) {
                        $wpnonce = wp_create_nonce("is-field-required");
                        // the code is double t handle the personal section and custom sections. (first line handles personal section & second line line handles resume custom sections)
                        $wpjobportal_jsVisibleFunction .= " getDataForVisibleField('".$wpnonce."', this.value, '" . $wpjobportal_visibleparam->visibleParent . "','" . 'sec_1['.$wpjobportal_visibleparam->visibleParentField.']' . "','".$wpjobportal_visibleparam->visibleValue."','".$wpjobportal_visibleparam->visibleCondition."');";
                        $wpjobportal_jsVisibleFunction .= " getDataForVisibleField('".$wpnonce."', this.value, '" . $wpjobportal_visibleparam->visibleParent . "','" . $wpjobportal_visibleparam->visibleParentField. "','".$wpjobportal_visibleparam->visibleValue."','".$wpjobportal_visibleparam->visibleCondition."');";
                    }
                    $wpjobportal_jsFunction.=$wpjobportal_jsVisibleFunction;
                }
                $wpjobportal_extraattr = array('data-validation' => $cssclass, 'onchange' => $wpjobportal_jsFunction, 'class' => "inputbox wjportal-form-select-field one ".$cssclass.$specialClass.$wpjobportal_themeclass);
                // handleformresume
                if($wpjobportal_section AND $wpjobportal_section != 1){
                    if($wpjobportal_ishidden){
                        if ($wpjobportal_required == 1) {
                            $wpjobportal_extraattr['data-validation'] = '';
                            $wpjobportal_extraattr['data-myrequired'] = $cssclass;
                            $wpjobportal_extraattr['class'] = "inputbox wjportal-form-select-field one";
                        }
                    }
                }
                //END handleformresume

                $wpjobportal_user_field .= $this->selectResume($wpjobportal_field->field, $wpjobportal_comboOptions, $wpjobportal_value, $placeholder, $wpjobportal_extraattr , null,$wpjobportal_section , $wpjobportal_sectionid);
            break;
            /*case 'depandant_field':
                if(wpjobportal::$wpjobportal_theme_chk == 1){
                    $wpjobportal_themeclass = getJobManagerThemeClass('select');
                }else{
                    $wpjobportal_themeclass = '';
                }
                $wpjobportal_comboOptions = array();
                if ($wpjobportal_value != null) {
                    if (!empty($wpjobportal_field->userfieldparams)) {
                        $obj_option = $this->getDataForDepandantFieldByParentField($wpjobportal_field->field, $wpjobportal_userfielddataarray);
                        foreach ($obj_option as $opt) {
                            $wpjobportal_comboOptions[] = (object) array('id' => $opt, 'text' => wpjobportal::wpjobportal_getVariableValue($opt));
                        }
                    }
                }
                //code for handling dependent field
                $wpjobportal_jsFunction = '';
                if ($wpjobportal_field->depandant_field != null) {
                    $wpjobportal_jsFunction = "getDataForDepandantFieldResume('" . $wpjobportal_field->field . "','" . $wpjobportal_field->depandant_field . "','".$wpjobportal_section."','".$wpjobportal_sectionid."'". $wpjobportal_theme_string.");";
                }
                //end
                $wpjobportal_extraattr = array('data-validation' => $cssclass, 'class' => "inputbox one ".$cssclass.$specialClass.$wpjobportal_themeclass);
                if(""!=$wpjobportal_jsFunction){
                    $wpjobportal_extraattr['onchange']=$wpjobportal_jsFunction;
                }
                // handleformresume
                if($wpjobportal_section AND $wpjobportal_section != 1){
                    if($wpjobportal_ishidden){
                        if ($wpjobportal_required == 1) {
                            $wpjobportal_extraattr['data-validation'] = '';
                            $wpjobportal_extraattr['data-myrequired'] = $cssclass;
                            $wpjobportal_extraattr['class'] = "inputbox one";
                        }
                    }
                }
                //END handleformresume
                $wpjobportal_user_field .= $this->selectResume($wpjobportal_field->field, $wpjobportal_comboOptions, $wpjobportal_value, esc_html(__('Select','wp-job-portal')) . ' ' . $wpjobportal_field->fieldtitle, $wpjobportal_extraattr , null, $wpjobportal_section , $wpjobportal_sectionid);
            break;*/
            case 'multiple':
                if(wpjobportal::$wpjobportal_theme_chk == 1){
                    $wpjobportal_themeclass = getJobManagerThemeClass('select');
                }else{
                    $wpjobportal_themeclass = '';
                }
                $wpjobportal_comboOptions = array();
                if (!empty($wpjobportal_field->userfieldparams)) {
                    $obj_option = json_decode($wpjobportal_field->userfieldparams);
                    foreach ($obj_option as $opt) {
                        $wpjobportal_comboOptions[] = (object) array('id' => $opt, 'text' => wpjobportal::wpjobportal_getVariableValue($opt));
                    }
                }
                $wpjobportal_name = $wpjobportal_field->field;
                $wpjobportal_name .= '[]';
                $wpjobportal_valuearray = wpjobportalphplib::wpJP_explode(', ', $wpjobportal_value);
                $wpjobportal_ismultiple = 1;
                $wpjobportal_extraattr = array('data-validation' => $cssclass, 'multiple' => 'multiple', 'class' => "inputbox one ".$cssclass.$specialClass.$wpjobportal_themeclass);
                // handleformresume
                if($wpjobportal_section AND $wpjobportal_section != 1){
                    if($wpjobportal_ishidden){
                        if ($wpjobportal_required == 1) {
                            $wpjobportal_extraattr['data-validation'] = '';
                            $wpjobportal_extraattr['data-myrequired'] = $cssclass;
                            $wpjobportal_extraattr['class'] = "inputbox one";
                        }
                    }
                }
                //END handleformresume
                //$wpjobportal_user_field .= $this->selectResume($wpjobportal_name, $wpjobportal_comboOptions, $wpjobportal_valuearray, '', $wpjobportal_extraattr , null ,$wpjobportal_section , $wpjobportal_sectionid , $wpjobportal_ismultiple);
            	$wpjobportal_user_field .= $this->selectResume($wpjobportal_name, $wpjobportal_comboOptions, $wpjobportal_valuearray,  __('Select', 'wp-job-portal') . ' ' . esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle)), $wpjobportal_extraattr , null ,$wpjobportal_section , $wpjobportal_sectionid , $wpjobportal_ismultiple);
            break;
            case 'file':
                if($wpjobportal_value != null){ // since file already uploaded so we reglect the required
                    $cssclass = wpjobportalphplib::wpJP_str_replace('required', '', $cssclass);
                }

                $wpjobportal_name = $wpjobportal_field->field;
                $wpjobportal_data_required = '';
                if($wpjobportal_section){
                    if($wpjobportal_section != 1){
                        if($wpjobportal_ishidden){
                            if($wpjobportal_required == 1){
                                $wpjobportal_data_required = 'data-myrequired="required"';
                                $cssclass = '';
                            }
                        }
                        // $wpjobportal_name = 'sec_'.$wpjobportal_section.'['.$wpjobportal_name.']['.$wpjobportal_sectionid.']';// upload code does not work for resume section specific fields
                    }else{
                        // $wpjobportal_name = 'sec_'.$wpjobportal_section.'['.$wpjobportal_name.']';// upload code does not work for resume section specific fields
                    }
                }

                $wpjobportal_user_field .= '<input type="file" class="'.esc_attr($cssclass).' cf_uploadfile" '.$wpjobportal_data_required.' name="'.$wpjobportal_name.'" id="'.$wpjobportal_field->field.'"/>';
                // if(JFactory::getApplication()->isAdmin()){
                //     $this->_config = JSModel::getJSModel('configuration')->getConfig();
                // }else{
                //     $this->_config = JSModel::getJSModel('configurations')->getConfig('');
                // }
                // $fileext  = '';
                // foreach ($this->_config as $conf) {
                //     if ($conf->configname == 'image_file_type'){
                //         if($fileext)
                //             $fileext .= ',';
                //         $fileext .= $conf->configvalue;
                //     }
                //     if ($conf->configname == 'document_file_type'){
                //         if($fileext)
                //             $fileext .= ',';
                //         $fileext .= $conf->configvalue;
                //     }
                //     if ($conf->configname == 'document_file_size')
                //         $wpjobportal_maxFileSize = $conf->configvalue;
                // }
                $wpjobportal_image_file_type = wpjobportal::$_config->getConfigurationByConfigName('image_file_type');
                $document_file_type = wpjobportal::$_config->getConfigurationByConfigName('document_file_type');
                $document_file_size = wpjobportal::$_config->getConfigurationByConfigName('document_file_size');

                $fileext  = '';
                $fileext .= $document_file_type.','.$wpjobportal_image_file_type;
                $wpjobportal_maxFileSize = $document_file_size;
                $wpjobportal_user_field .= '<div id="js_cust_file_ext">'.esc_html(__('Files','wp-job-portal')).' ('.$fileext.')<br> '.esc_html(__('Maximum Size','wp-job-portal')).' '.$wpjobportal_maxFileSize.'(kb)</div>';
                if($wpjobportal_value != null){
                    // $wpjobportal_user_field .= $this->hidden($wpjobportal_field->field.'_1', 0 , array(), $wpjobportal_section , $wpjobportal_sectionid);
                    // $wpjobportal_user_field .= $this->hidden($wpjobportal_field->field.'_2',$wpjobportal_value, array(), $wpjobportal_section , $wpjobportal_sectionid);
                    $wpjobportal_jsFunction = "deleteCutomUploadedFile('".$wpjobportal_field->field."','".$wpjobportal_field->required."')";
                    // $wpjobportal_value = wpjobportalphplib::wpJP_explode('_', $wpjobportal_value , 2);
                    // $wpjobportal_value = $wpjobportal_value[1];
                    $wpjobportal_user_field .= WPJOBPORTALformfield::hidden($wpjobportal_field->field.'_1', 0);
                    $wpjobportal_user_field .= WPJOBPORTALformfield::hidden($wpjobportal_field->field.'_2', $wpjobportal_value);
                    $wpjobportal_user_field .='<span class='.$wpjobportal_field->field.'_1>'.$wpjobportal_value.'( ';
                    $wpjobportal_user_field .= "<a href='javascript:void(0)' onClick=".$wpjobportal_jsFunction." >". esc_html(__('Delete','wp-job-portal'))."</a>";
                    $wpjobportal_user_field .= ' )</span>';
                }
            break;
        }
        $wpjobportal_html .= $wpjobportal_user_field;
        if (isset($wpjobportal_field->description) && !empty($wpjobportal_field->description)) {
            $wpjobportal_html .= '<div class="wjportal-form-help-txt">'.$wpjobportal_field->description.'</div>';
        }
        $wpjobportal_html .= '</div></div>';
        if ($wpjobportal_resumeform === 1) {
            return array('title' => $wpjobportal_resumeTitle , 'value' => $wpjobportal_user_field);
        }elseif($wpjobportal_resumeform == 'admin'){
            return array('title' => $wpjobportal_resumeTitle , 'value' => $wpjobportal_user_field , 'lable' => $wpjobportal_field->field);
        }elseif($wpjobportal_resumeform == 'f_company'){
            return array('title' => $wpjobportal_resumeTitle , 'value' => $wpjobportal_user_field , 'lable' => $wpjobportal_field->field);
        }else {
            return $wpjobportal_html;
        }

    }

    static function selectResume($wpjobportal_name, $list, $wpjobportal_defaultvalue, $title = '', $wpjobportal_extraattr = array() , $wpjobportal_disabled = '',  $wpjobportal_resume_section_id = null , $wpjobportal_sectionid = null , $wpjobportal_ismultiple = false) {
        if (wpjobportalphplib::wpJP_strpos($wpjobportal_name, '[]') !== false) {
            $wpjobportal_id = wpjobportalphplib::wpJP_str_replace('[]', '', $wpjobportal_name);
        }else{
            $wpjobportal_id = $wpjobportal_name;
        }

        // handleformresume
        if($wpjobportal_resume_section_id){
            if($wpjobportal_resume_section_id != 1){
                if($wpjobportal_ismultiple){
                    $wpjobportal_name = wpjobportalphplib::wpJP_str_replace('[]', '', $wpjobportal_name);
                    $wpjobportal_name = 'sec_'.$wpjobportal_resume_section_id.'['.$wpjobportal_name.']['.$wpjobportal_sectionid.'][]';
                    $wpjobportal_id .=$wpjobportal_sectionid;
                }else{
                    $wpjobportal_name = 'sec_'.$wpjobportal_resume_section_id.'['.$wpjobportal_name.']['.$wpjobportal_sectionid.']';
                    $wpjobportal_id .=$wpjobportal_sectionid;
                }
            }else{
                if($wpjobportal_ismultiple){
                    $wpjobportal_name = wpjobportalphplib::wpJP_str_replace('[]', '', $wpjobportal_name);
                    $wpjobportal_name = 'sec_'.$wpjobportal_resume_section_id.'['.$wpjobportal_name.'][]';
                }else{
                    $wpjobportal_name = 'sec_'.$wpjobportal_resume_section_id.'['.$wpjobportal_name.']';
                }
            }
        }
        //END handleformresume

        $wpjobportal_selectfield = '<select name="' . $wpjobportal_name . '" id="' . $wpjobportal_id . '" ';
        if (!empty($wpjobportal_extraattr))
            foreach ($wpjobportal_extraattr AS $wpjobportal_key => $wpjobportal_val) {
                $wpjobportal_selectfield .= ' ' . $wpjobportal_key . '="' . $wpjobportal_val . '"';
            }
        if($wpjobportal_disabled)
            $wpjobportal_selectfield .= ' disabled>';
        else
            $wpjobportal_selectfield .= ' >';
        if ($title != '') {
            $wpjobportal_selectfield .= '<option value="">' . $title . '</option>';
        }
        if (!empty($list))
            foreach ($list AS $record) {
                if ((is_array($wpjobportal_defaultvalue) && in_array($record->id, $wpjobportal_defaultvalue)) || $wpjobportal_defaultvalue == $record->id)
                    $wpjobportal_selectfield .= '<option selected="selected" value="' . $record->id . '">' . wpjobportal::wpjobportal_getVariableValue($record->text) . '</option>';
                else
                    $wpjobportal_selectfield .= '<option value="' . $record->id . '">' . wpjobportal::wpjobportal_getVariableValue($record->text) . '</option>';
            }

        $wpjobportal_selectfield .= '</select>';
        return $wpjobportal_selectfield;
    }



    static function radiobuttonResume($wpjobportal_name, $list, $wpjobportal_defaultvalue, $wpjobportal_extraattr = array() , $wpjobportal_resume_section_id = null , $wpjobportal_sectionid = null) {
        if (wpjobportalphplib::wpJP_strpos($wpjobportal_name, '[]') !== false) {
            $wpjobportal_id = wpjobportalphplib::wpJP_str_replace('[]', '', $wpjobportal_name);
        }else{
            $wpjobportal_id = $wpjobportal_name;
        }

        $radiobutton = '';
        $wpjobportal_count = 1;
        $wpjobportal_match = false;
        $wpjobportal_firstvalue = '';
        foreach($list AS $wpjobportal_value => $wpjobportal_label){
            if($wpjobportal_firstvalue == '')
                $wpjobportal_firstvalue = $wpjobportal_value;
            if($wpjobportal_defaultvalue == $wpjobportal_value){
                $wpjobportal_match = true;
                break;
            }
        }
        if($wpjobportal_match == false){
            //$wpjobportal_defaultvalue = $wpjobportal_firstvalue;
        }

        // handleformresume
        if($wpjobportal_resume_section_id){
            if($wpjobportal_resume_section_id != 1){
                $wpjobportal_name = 'sec_'.$wpjobportal_resume_section_id.'['.$wpjobportal_name.']['.$wpjobportal_sectionid.']';
                $wpjobportal_id .=$wpjobportal_sectionid;
            }else{
                $wpjobportal_name = 'sec_'.$wpjobportal_resume_section_id.'['.$wpjobportal_name.']';
            }
        }
        //END handleformresume

        foreach ($list AS $wpjobportal_value => $wpjobportal_label) {
            $radiobutton .= '<span class="wpjobportal-form-radio-field">';
            $radiobutton .= '<input type="radio" name="' . $wpjobportal_name . '" id="' . $wpjobportal_id . $wpjobportal_count . '" value="' . wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_value) . '"';
            if ($wpjobportal_defaultvalue == $wpjobportal_value){
                $radiobutton .= ' checked="checked"';
            }
            if (!empty($wpjobportal_extraattr))
                foreach ($wpjobportal_extraattr AS $wpjobportal_key => $wpjobportal_val) {
                    $radiobutton .= ' ' . $wpjobportal_key . '="' . $wpjobportal_val . '"';
                }
            $radiobutton .= '/><label id="for' . $wpjobportal_id . '" class="cf_radiobtn" for="' . $wpjobportal_id . $wpjobportal_count . '">' . $wpjobportal_label . '</label>';
            $radiobutton .= '</span>';
            $wpjobportal_count++;
        }
        return $radiobutton;
    }



    static function checkboxResume($wpjobportal_name, $list, $wpjobportal_defaultvalue, $wpjobportal_extraattr = array() , $wpjobportal_resume_section_id = null , $wpjobportal_sectionid = null) {

        if (wpjobportalphplib::wpJP_strpos($wpjobportal_name, '[]') !== false) {
            $wpjobportal_id = wpjobportalphplib::wpJP_str_replace('[]', '', $wpjobportal_name);
        }else{
            $wpjobportal_id = $wpjobportal_name;
        }

        $wpjobportal_checkbox = '';
        $wpjobportal_count = 1;

        // handleformresume
        if($wpjobportal_resume_section_id){
            if($wpjobportal_resume_section_id != 1){
                $wpjobportal_name = 'sec_'.$wpjobportal_resume_section_id.'['.$wpjobportal_name.']['.$wpjobportal_sectionid.'][]';
                $wpjobportal_id .=$wpjobportal_sectionid;
            }else{
                $wpjobportal_name = 'sec_'.$wpjobportal_resume_section_id.'['.$wpjobportal_name.'][]';
            }
        }
        //END handleformresume

        foreach ($list AS $wpjobportal_value => $wpjobportal_label) {
            $wpjobportal_checkbox .= '<input type="checkbox" name="' . $wpjobportal_name . '" id="' . $wpjobportal_id . $wpjobportal_count . '" value="' . wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_value) . '"';
            if ($wpjobportal_defaultvalue == $wpjobportal_value)
                $wpjobportal_checkbox .= ' checked="checked"';
            if (!empty($wpjobportal_extraattr))
                foreach ($wpjobportal_extraattr AS $wpjobportal_key => $wpjobportal_val) {
                    $wpjobportal_checkbox .= ' ' . $wpjobportal_key . '="' . $wpjobportal_val . '"';
                }
            $wpjobportal_checkbox .= '/><label id="for' . $wpjobportal_id . '" for="' . $wpjobportal_id . $wpjobportal_count . '">' . $wpjobportal_label . '</label>';
            $wpjobportal_count++;
        }
        return $wpjobportal_checkbox;
    }


    static function textareaResume($wpjobportal_name, $wpjobportal_value, $wpjobportal_extraattr = array() , $wpjobportal_resume_section_id = null , $wpjobportal_sectionid = null) {
            if (wpjobportalphplib::wpJP_strpos($wpjobportal_name, '[]') !== false) {
                $wpjobportal_id = wpjobportalphplib::wpJP_str_replace('[]', '', $wpjobportal_name);
            }else{
                $wpjobportal_id = $wpjobportal_name;
            }
        // handleformresume
        if($wpjobportal_resume_section_id){
            if($wpjobportal_resume_section_id != 1){
                $wpjobportal_name = 'sec_'.$wpjobportal_resume_section_id.'['.$wpjobportal_name.']['.$wpjobportal_sectionid.']';
                $wpjobportal_id .=$wpjobportal_sectionid;
            }else{
                $wpjobportal_name = 'sec_'.$wpjobportal_resume_section_id.'['.$wpjobportal_name.']';
            }
        }
        //END handleformresume

        $wpjobportal_textarea = '<textarea name="' . $wpjobportal_name . '" id="' . $wpjobportal_id . '" ';
        if (!empty($wpjobportal_extraattr))
            foreach ($wpjobportal_extraattr AS $wpjobportal_key => $wpjobportal_val)
                $wpjobportal_textarea .= ' ' . $wpjobportal_key . '="' . $wpjobportal_val . '"';
        $wpjobportal_textarea .= ' >' . wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_value) . '</textarea>';
        return $wpjobportal_textarea;
    }


    static function dateResume($wpjobportal_name, $wpjobportal_value, $wpjobportal_extraattr = array() , $wpjobportal_resume_section_id = null , $wpjobportal_sectionid = null) {
        if (wpjobportalphplib::wpJP_strpos($wpjobportal_name, '[]') !== false) {
            $wpjobportal_id = wpjobportalphplib::wpJP_str_replace('[]', '', $wpjobportal_name);
        }else{
            $wpjobportal_id = $wpjobportal_name;
        }

        // handleformresume
        if($wpjobportal_resume_section_id){
            if($wpjobportal_resume_section_id != 1){
                $wpjobportal_name = 'sec_'.$wpjobportal_resume_section_id.'['.$wpjobportal_name.']['.$wpjobportal_sectionid.']';
                $wpjobportal_id .=$wpjobportal_sectionid;
            }else{
                $wpjobportal_name = 'sec_'.$wpjobportal_resume_section_id.'['.$wpjobportal_name.']';
            }
        }
        //END handleformresume

        $wpjobportal_textfield = '<input type="text" name="' . $wpjobportal_name . '" id="' . $wpjobportal_id . '" value="' . htmlspecialchars($wpjobportal_value) . '" ';
        if (!empty($wpjobportal_extraattr))
            foreach ($wpjobportal_extraattr AS $wpjobportal_key => $wpjobportal_val)
                $wpjobportal_textfield .= ' ' . $wpjobportal_key . '="' . $wpjobportal_val . '"';
        $wpjobportal_textfield .= ' />';
        return $wpjobportal_textfield;
    }

    static function textResume($wpjobportal_name, $wpjobportal_value, $wpjobportal_extraattr = array() , $wpjobportal_resume_section_id = null , $wpjobportal_sectionid = null) {


        if (wpjobportalphplib::wpJP_strpos($wpjobportal_name, '[]') !== false) {
            $wpjobportal_id = wpjobportalphplib::wpJP_str_replace('[]', '', $wpjobportal_name);
        }else{
            $wpjobportal_id = $wpjobportal_name;
        }

        // handleformresume
        if($wpjobportal_resume_section_id){
            if($wpjobportal_resume_section_id != 1){
                $wpjobportal_name = 'sec_'.$wpjobportal_resume_section_id.'['.$wpjobportal_name.']['.$wpjobportal_sectionid.']';
                $wpjobportal_id .=$wpjobportal_sectionid;
            }else{
                $wpjobportal_name = 'sec_'.$wpjobportal_resume_section_id.'['.$wpjobportal_name.']';
            }
        }
        //END handleformresume

        $wpjobportal_textfield = '<input type="text" name="' . $wpjobportal_name . '" id="' . $wpjobportal_id . '" value="' . wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_value) . '" ';
        if (!empty($wpjobportal_extraattr))
            foreach ($wpjobportal_extraattr AS $wpjobportal_key => $wpjobportal_val)
                $wpjobportal_textfield .= ' ' . $wpjobportal_key . '="' . wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_val) . '"';
        $wpjobportal_textfield .= ' />';
        return $wpjobportal_textfield;
    }

    static function emailResume($wpjobportal_name, $wpjobportal_value, $wpjobportal_extraattr = array() , $wpjobportal_resume_section_id = null , $wpjobportal_sectionid = null) {
        if (wpjobportalphplib::wpJP_strpos($wpjobportal_name, '[]') !== false) {
            $wpjobportal_id = wpjobportalphplib::wpJP_str_replace('[]', '', $wpjobportal_name);
        }else{
            $wpjobportal_id = $wpjobportal_name;
        }

        // handleformresume
        if($wpjobportal_resume_section_id){
            if($wpjobportal_resume_section_id != 1){
                $wpjobportal_name = 'sec_'.$wpjobportal_resume_section_id.'['.$wpjobportal_name.']['.$wpjobportal_sectionid.']';
                $wpjobportal_id .=$wpjobportal_sectionid;
            }else{
                $wpjobportal_name = 'sec_'.$wpjobportal_resume_section_id.'['.$wpjobportal_name.']';
            }
        }
        //END handleformresume

        $wpjobportal_textfield = '<input type="email" name="' . $wpjobportal_name . '" id="' . $wpjobportal_id . '" value="' . wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_value) . '" ';
        if (!empty($wpjobportal_extraattr))
            foreach ($wpjobportal_extraattr AS $wpjobportal_key => $wpjobportal_val)
                $wpjobportal_textfield .= ' ' . $wpjobportal_key . '="' . $wpjobportal_val . '"';
        $wpjobportal_textfield .= ' />';
        return $wpjobportal_textfield;
    }

    function formCustomFields($wpjobportal_field,$wpjobportal_resumeform = null, $wpjobportal_section = null, $refid = null,$wpjobportal_themecall=null) {
        //patch to saolve notices on resume form but it may causse problems like showing disabled fiedls
        if ($wpjobportal_resumeform != 1) {
            if ($wpjobportal_field->isuserfield != 1) {
                return;
            }
        }

        if(null != $wpjobportal_themecall){
            $wpjobportal_div1 = 'resume-row-wrapper form';
            $wpjobportal_div2 = 'row-title';
            $wpjobportal_div3 = 'row-value';
        }else{
            $wpjobportal_div1 = 'resume-row-wrapper form';
            $wpjobportal_div2 = 'row-title';
            $wpjobportal_div3 = 'row-value';

        }

        $cssclass = "";
        $wpjobportal_visibleclass = "";
        if (isset($wpjobportal_field->visibleparams) && $wpjobportal_field->visibleparams != ''){
            $wpjobportal_visibleclass = "visible";
            $wpjobportal_div1 .= ' visible ';
        }
        $wpjobportal_html = '';
        $wpjobportal_themebfclass = " ".$this->class_prefix."-bigfont ";
        $wpjobportal_required = $wpjobportal_field->required;
        if ($wpjobportal_required == 1) {
            $wpjobportal_html .= wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle) . '<font color="red"> *</font>';
            $cssclass = "required";
        }else {
            $wpjobportal_html .= wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle);
            $cssclass = "";
        }
        if (isset($wpjobportal_field->visibleparams) && $wpjobportal_field->visibleparams !='') {
            $wpjobportal_required = 0;
            $cssclass= '';
        }
        //$wpjobportal_readonly = $wpjobportal_field->readonly ? "'readonly => 'readonly'" : "";
        //$wpjobportal_maxlength = $wpjobportal_field->maxlength ? "'maxlength' => '" . $wpjobportal_field->maxlength : "";
        $fvalue = "";
        $wpjobportal_value = "";
        $wpjobportal_userdataid = "";
        if ($wpjobportal_resumeform == 1) {
            if($wpjobportal_section == 1 || $wpjobportal_section == 5 || $wpjobportal_section == 6){ // personal section
                if(isset(wpjobportal::$_data[0]['personal_section'])){
                    $wpjobportal_value = wpjobportal::$_data[0]['personal_section']->params;
                }
            }elseif($wpjobportal_section == 2){
                if(isset(wpjobportal::$_data[0]['address_section'])){
                    $wpjobportal_value = wpjobportal::$_data[0]['address_section']->params;
                }
            }elseif($wpjobportal_section == 3){
                if(isset(wpjobportal::$_data[0]['institute_section'])){
                    $wpjobportal_value = wpjobportal::$_data[0]['institute_section']->params;
                }
            }elseif($wpjobportal_section == 4){
                if(isset(wpjobportal::$_data[0]['employer_section'])){
                    $wpjobportal_value = wpjobportal::$_data[0]['employer_section']->params;
                }
            }elseif($wpjobportal_section == 7){
                if(isset(wpjobportal::$_data[0]['reference_section'])){
                    $wpjobportal_value = wpjobportal::$_data[0]['reference_section']->params;
                }
            }elseif($wpjobportal_section == 8){
                if(isset(wpjobportal::$_data[0]['language_section'])){
                    $wpjobportal_value = wpjobportal::$_data[0]['language_section']->params;
                }
            }
            if($wpjobportal_value){ // data has been stored
                $wpjobportal_userfielddataarray = json_decode($wpjobportal_value);
                $wpjobportal_valuearray = json_decode($wpjobportal_value,true);
            }else{
                $wpjobportal_valuearray = array();
            }
            if(array_key_exists($wpjobportal_field->field, $wpjobportal_valuearray)){
                $wpjobportal_value = $wpjobportal_valuearray[$wpjobportal_field->field];
            }else{
                $wpjobportal_value = '';
            }
        } elseif (isset(wpjobportal::$_data[0]->id)) {
            // to handle the case of custom fields showing on listing and detail but not on form in edit case
            $params = wpjobportal::$_data[0]->params;
            if(WPJOBPORTALincluder::getJSModel('common')->checkLanguageSpecialCase()){
                $params = wpjobportalphplib::wpJP_stripslashes($params);
            }
            $params = html_entity_decode($params, ENT_QUOTES);
            $wpjobportal_userfielddataarray = json_decode($params);
            $uffield = $wpjobportal_field->field;
            if (isset($wpjobportal_userfielddataarray->$uffield) || !empty($wpjobportal_userfielddataarray->$uffield)) {
                $wpjobportal_value = $wpjobportal_userfielddataarray->$uffield;
            } else {
                $wpjobportal_value = '';
            }
        }
        $wpjobportal_html = '<div class="' . $wpjobportal_div1 . '">
               <div class="' . $wpjobportal_div2 . '">';

        $wpjobportal_theme_string = '';
        $wpjobportal_html = '';
        $specialClass = '';
        if($wpjobportal_value != ''){
            $specialClass = ' specialClass ';
        }
        switch ($wpjobportal_field->userfieldtype) {
            case 'text':
                if(wpjobportal::$wpjobportal_theme_chk == 1){
                    $wpjobportal_themeclass = getJobManagerThemeClass('text');
                }else{
                    $wpjobportal_themeclass = '';
                }
                $wpjobportal_html .= WPJOBPORTALformfield::text($wpjobportal_field->field, $wpjobportal_value, array('class' => ' inputbox one wjportal-form-input-field '. $wpjobportal_themeclass.$specialClass, 'data-validation' => $cssclass,'placeholder'=>$wpjobportal_field->placeholder));
                break;
            case 'email':
                if(wpjobportal::$wpjobportal_theme_chk == 1){
                    $wpjobportal_themeclass = getJobManagerThemeClass('text');
                }else{
                    $wpjobportal_themeclass = '';
                }
                $wpjobportal_html .= WPJOBPORTALformfield::email($wpjobportal_field->field, $wpjobportal_value, array('class' => ' inputbox one wjportal-form-input-field '. $wpjobportal_themeclass.$specialClass, 'data-validation' => $cssclass,'placeholder'=>$wpjobportal_field->placeholder));
                break;
            case 'date':
                if(wpjobportal::$wpjobportal_theme_chk == 1){
                    $wpjobportal_themeclass = getJobManagerThemeClass('text');
                }else{
                    $wpjobportal_themeclass = '';
                }
                if ($wpjobportal_value != '') {
                    $wpjobportal_value = gmdate(wpjobportal::$_configuration['date_format'],strtotime($wpjobportal_value));
                }
                $wpjobportal_html .= WPJOBPORTALformfield::text($wpjobportal_field->field, $wpjobportal_value, array('class' => 'custom_date one wjportal-form-date-field '. $wpjobportal_themeclass.$specialClass, 'data-validation' => $cssclass,'placeholder'=>$wpjobportal_field->placeholder,'autocomplete'=>'off'));
                break;
            case 'textarea':
                $wpjobportal_rows = '10';
                $cols = '10';
                // if(isset($wpjobportal_field->rows)){
                //     $wpjobportal_rows = $wpjobportal_field->rows;
                // }
                // if(isset($wpjobportal_field->cols)){
                //     $cols = $wpjobportal_field->cols;
                // }
                if(wpjobportal::$wpjobportal_theme_chk == 1){
                    $wpjobportal_themeclass = getJobManagerThemeClass('textarea');
                }else{
                    $wpjobportal_themeclass = '';
                }
                $wpjobportal_html .= WPJOBPORTALformfield::textarea($wpjobportal_field->field, $wpjobportal_value, array('class' => ' inputbox one wpjobportal-form-textarea-field '. $wpjobportal_themeclass.$specialClass, 'data-validation' => $cssclass, 'rows' => $wpjobportal_rows, 'cols' => $cols));
                break;
            case 'multiple':
                if(wpjobportal::$wpjobportal_theme_chk == 1){
                    $wpjobportal_themeclass = getJobManagerThemeClass('select');
                }else{
                    $wpjobportal_themeclass = '';
                }
                $wpjobportal_comboOptions = array();
                if (!empty($wpjobportal_field->userfieldparams)) {
                    if(WPJOBPORTALincluder::getJSModel('common')->checkLanguageSpecialCase()){
                        $wpjobportal_field->userfieldparams = wpjobportalphplib::wpJP_stripslashes($wpjobportal_field->userfieldparams);
                    }
                    $obj_option = json_decode($wpjobportal_field->userfieldparams);
                    foreach ($obj_option as $opt) {
                        $wpjobportal_comboOptions[] = (object) array('id' => $opt, 'text' => $opt);
                    }
                }
                $wpjobportal_array = $wpjobportal_field->field;
                $wpjobportal_array .= '[]';
                $wpjobportal_valuearray = wpjobportalphplib::wpJP_explode(', ', $wpjobportal_value);
                $wpjobportal_html .= WPJOBPORTALformfield::select($wpjobportal_array, $wpjobportal_comboOptions, $wpjobportal_valuearray, __('Select', 'wp-job-portal') . ' ' . $wpjobportal_field->fieldtitle, array('data-validation' => $cssclass, 'multiple' => 'multiple', 'class' => 'inputbox one wjportal-form-select-field wjportal-form-multi-select-field '. $wpjobportal_themeclass.$specialClass));
                break;
            case 'checkbox':
                if (!empty($wpjobportal_field->userfieldparams)) {
                    $wpjobportal_comboOptions = array();
                    $obj_option = json_decode($wpjobportal_field->userfieldparams);
                    $wpjobportal_i = 0;
                    $wpjobportal_valuearray = wpjobportalphplib::wpJP_explode(', ',$wpjobportal_value);
                    $wpjobportal_jsFunction = '';
                    if ($wpjobportal_required == 1) {
                        $wpjobportal_jsFunction = "deRequireUfCheckbox('" . $wpjobportal_field->field . "');";
                    }
                  	if(is_array($obj_option)){
                      foreach ($obj_option AS $wpjobportal_option) {
                          $wpjobportal_check = '';
                          if(in_array($wpjobportal_option, $wpjobportal_valuearray)){
                              $wpjobportal_check = 'checked';
                          }
                          $wpjobportal_html .= '<span class="wpjobportal-form-radio-field">';
                          $wpjobportal_html .= '<input type="checkbox" ' . $wpjobportal_check . ' class="uf_of_type_ckbox radiobutton ' . $wpjobportal_field->field .$specialClass. '" value="' . wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_option) . '" id="' . $wpjobportal_field->field . '_' . $wpjobportal_i . '" name="' . $wpjobportal_field->field . '[]" data-validation="'.esc_attr($cssclass).'" onclick = "' . $wpjobportal_jsFunction . '" ckbox-group-name="' . $wpjobportal_field->field . '">';
                          $wpjobportal_html .= '<label for="' . $wpjobportal_field->field . '_' . $wpjobportal_i . '" id="foruf_checkbox1">' . $wpjobportal_option . '</label>';
                          $wpjobportal_html .= '</span>';

                          $wpjobportal_i++;
                      }
                	}
                } else {
                    $wpjobportal_comboOptions = array('1' => $wpjobportal_field->fieldtitle);
                    $wpjobportal_html .= WPJOBPORTALformfield::checkbox($wpjobportal_field->field, $wpjobportal_comboOptions, $wpjobportal_value, array('class' => 'radiobutton wjportal-form-checkbox-field'.$specialClass));
                }
                break;
            case 'radio':
                $wpjobportal_comboOptions = array();
                if (!empty($wpjobportal_field->userfieldparams)) {
                    $obj_option = json_decode($wpjobportal_field->userfieldparams);
                  	if(is_array($obj_option)){
                      for ($wpjobportal_i = 0; $wpjobportal_i < count($obj_option); $wpjobportal_i++) {
                          $wpjobportal_comboOptions[$obj_option[$wpjobportal_i]] = "$obj_option[$wpjobportal_i]";
                      }
                    }
                }
                $wpjobportal_jsFunction = '';
                $wpjobportal_dependentclass = '';
                if ($wpjobportal_field->depandant_field != null) {
                    $wpjobportal_jsFunction = "getDataForDepandantField('" . $wpjobportal_field->field . "','" . $wpjobportal_field->depandant_field . "',2,'',''". $wpjobportal_theme_string.");";
                    $wpjobportal_dependentclass = 'dependent wjportal-form-radio-field';
                }
                $wpjobportal_html .= WPJOBPORTALformfield::radiobutton($wpjobportal_field->field, $wpjobportal_comboOptions, $wpjobportal_value, array('data-validation' => $cssclass , 'class' =>  $wpjobportal_dependentclass.$specialClass, 'onclick' => $wpjobportal_jsFunction));
                break;
            case 'combo':
                if(wpjobportal::$wpjobportal_theme_chk == 1){
                    $wpjobportal_themeclass = getJobManagerThemeClass('select');
                }else{
                    $wpjobportal_themeclass = '';
                }
                $wpjobportal_comboOptions = array();
                if (!empty($wpjobportal_field->userfieldparams)) {
                    $obj_option = json_decode($wpjobportal_field->userfieldparams);
                    if(is_array($obj_option)){ // to handle log error
                        foreach ($obj_option as $opt) {
                            $wpjobportal_comboOptions[] = (object) array('id' => $opt, 'text' => $opt);
                        }
                    }
                }
                //code for handling dependent field
                $wpjobportal_jsFunction = '';
                if ($wpjobportal_field->depandant_field != null) {
                    $wpjobportal_jsFunction = "getDataForDepandantField('" . $wpjobportal_field->field . "','" . $wpjobportal_field->depandant_field . "',1,'',''". $wpjobportal_theme_string. ");";
                }
                if ($wpjobportal_field->placeholder != '') {
                    $placeholder = $wpjobportal_field->placeholder;
                } else {
                    $placeholder = esc_html(__('Select', 'wp-job-portal')) . ' ' . esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle));
                }
                //end
                //code for handling visible field
                $wpjobportal_jsVisibleFunction = '';
                if ($wpjobportal_field->visible_field != null) {
                    $wpjobportal_visibleparams = WPJOBPORTALincluder::getJSModel('fieldordering')->getDataForVisibleField($wpjobportal_field->visible_field);
                    foreach ($wpjobportal_visibleparams as $wpjobportal_visibleparam) {
                        $wpnonce = wp_create_nonce("is-field-required");
                        $wpjobportal_jsVisibleFunction .= " getDataForVisibleField('".$wpnonce."', this.value, '" . $wpjobportal_visibleparam->visibleParent . "','" . $wpjobportal_visibleparam->visibleParentField . "','".$wpjobportal_visibleparam->visibleValue."','".$wpjobportal_visibleparam->visibleCondition."');";
                    }
                    $wpjobportal_jsFunction.=$wpjobportal_jsVisibleFunction;
                }
                // end
                $wpjobportal_html .= WPJOBPORTALformfield::select($wpjobportal_field->field, $wpjobportal_comboOptions, $wpjobportal_value, $placeholder, array('data-validation' => $cssclass, 'onchange' => $wpjobportal_jsFunction, 'class' => 'inputbox one wjportal-form-select-field'. $wpjobportal_themeclass.$specialClass));
                break;
            case 'file':
                if($wpjobportal_value != null){ // since file already uploaded so we reglect the required
                    $cssclass = wpjobportalphplib::wpJP_str_replace('required', '', $cssclass);
                }
                $wpjobportal_user_field ='';
                $wpjobportal_name = $wpjobportal_field->field;
                $wpjobportal_data_required = '';
                if($wpjobportal_section){
                    if($wpjobportal_section != 1){
                        if($wpjobportal_ishidden){
                            if($wpjobportal_required == 1){
                                $wpjobportal_data_required = 'data-myrequired="required"';
                                $cssclass = '';
                            }
                        }
                        $wpjobportal_name = 'sec_'.$wpjobportal_section.'['.$wpjobportal_name.']['.$wpjobportal_sectionid.']';
                    }else{
                        $wpjobportal_name = 'sec_'.$wpjobportal_section.'['.$wpjobportal_name.']';
                    }
                }

                $wpjobportal_user_field .= '<input type="file" class="'.$cssclass.$specialClass.' cf_uploadfile" '.$wpjobportal_data_required.' name="'.$wpjobportal_name.'" id="'.$wpjobportal_field->field.'"/>';

                $wpjobportal_image_file_type = wpjobportal::$_config->getConfigurationByConfigName('image_file_type');
                $document_file_type = wpjobportal::$_config->getConfigurationByConfigName('document_file_type');
                $document_file_size = wpjobportal::$_config->getConfigurationByConfigName('document_file_size');

                $fileext  = '';
                $fileext .= $document_file_type.','.$wpjobportal_image_file_type;
                $wpjobportal_maxFileSize = $document_file_size;

                $fileext = wpjobportalphplib::wpJP_explode(',', $fileext);
                $fileext = array_unique($fileext);
                $fileext = implode(',', $fileext);
                $wpjobportal_user_field .= '<div id="js_cust_file_ext">'.esc_html__('Files','wp-job-portal').' ('.$fileext.')<br> '.esc_html__('Maximum Size','wp-job-portal').' '.$wpjobportal_maxFileSize.'(kb)</div>';
                if($wpjobportal_value != null){
                    $wpjobportal_user_field .= WPJOBPORTALformfield::hidden($wpjobportal_field->field.'_1', 0);
                    $wpjobportal_user_field .= WPJOBPORTALformfield::hidden($wpjobportal_field->field.'_2', $wpjobportal_value);
                    // $wpjobportal_user_field .= $this->hidden($wpjobportal_field->field.'_1', 0 , array(), $wpjobportal_section , $wpjobportal_sectionid);
                    // $wpjobportal_user_field .= $this->hidden($wpjobportal_field->field.'_2',$wpjobportal_value, array(), $wpjobportal_section , $wpjobportal_sectionid);
                    //$wpjobportal_jsFunction = "";
                    // $wpjobportal_value = wpjobportalphplib::wpJP_explode('_', $wpjobportal_value , 2);
                    // $wpjobportal_value = $wpjobportal_value[1];
                    $wpjobportal_user_field .='<span class='.$wpjobportal_field->field.'_1>'.$wpjobportal_value.'( ';
                    $wpjobportal_user_field .= "<a href='#' onclick='deleteCutomUploadedFile(\"".$wpjobportal_field->field."\")' >". __('Delete','wp-job-portal')."</a>";
                    $wpjobportal_user_field .= ' )</span>';
                }
                $wpjobportal_html .= $wpjobportal_user_field;
            break;
        }
        return $wpjobportal_html;
    }

    function formCustomFieldsForSearch($wpjobportal_field, &$wpjobportal_i, $wpjobportal_resumeform = null, $wpjobportal_subrefid = null,$wpjobportal_themecall=null,$wpjobportal_themrefine=null) {
        if ($wpjobportal_field->isuserfield != 1)
            return false;
        $cssclass = "";
        $wpjobportal_html = '';
        $wpjobportal_i++;
        if($wpjobportal_resumeform != 3 && $wpjobportal_resumeform != 'f_jobsearch'){// to handle top search case for job and resume listing.

            $wpjobportal_themebfclass = " ".$this->class_prefix."-bigfont ";

            $wpjobportal_themenopadmarclass = " ".$this->class_prefix."-nopad-nomar ";

            $wpjobportal_required = $wpjobportal_field->required;
            $wpjobportal_div1 = 'wjportal-form-row '.$wpjobportal_themenopadmarclass;
            $wpjobportal_div2 = 'wjportal-form-title '.$wpjobportal_themebfclass;
            $wpjobportal_div3 = 'wjportal-form-value';

            $wpjobportal_html = '<div class="' . $wpjobportal_div1 . '" title="'. esc_attr(wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle)) .'" )>
                   <div class="' . $wpjobportal_div2 . '">';
            $wpjobportal_html .= wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle);
            $wpjobportal_html .= ' </div><div class="' . $wpjobportal_div3 . '">';
        }
        $wpjobportal_readonly = ''; //$wpjobportal_field->readonly ? "'readonly => 'readonly'" : "";
        $wpjobportal_maxlength = ''; //$wpjobportal_field->maxlength ? "'maxlength' => '".$wpjobportal_field->maxlength : "";
        $fvalue = "";
        $wpjobportal_value = null;
        $wpjobportal_userdataid = "";
        $wpjobportal_userfielddataarray = array();
        if (isset(wpjobportal::$_data['filter']['params'])) {
            $wpjobportal_userfielddataarray = wpjobportal::$_data['filter']['params'];
            $uffield = $wpjobportal_field->field;
            //had to user || oprator bcz of radio buttons

            if (isset($wpjobportal_userfielddataarray[$uffield]) || !empty($wpjobportal_userfielddataarray[$uffield])) {
                $wpjobportal_value = $wpjobportal_userfielddataarray[$uffield];
            } else {
                $wpjobportal_value = '';
            }
        }
         if($wpjobportal_themecall != null){
            $wpjobportal_theme_string = ", '". $wpjobportal_themecall ."'";
        }else{
            $wpjobportal_theme_string = '';
        }
        switch ($wpjobportal_field->userfieldtype) {
            case 'text':
            case 'textarea':
		
                if(wpjobportal::$wpjobportal_theme_chk == 1){
                    $wpjobportal_themeclass = getJobManagerThemeClass('text');
                }else{
                    $wpjobportal_themeclass = '';
                }
                $wpjobportal_html .= WPJOBPORTALformfield::text($wpjobportal_field->field, $wpjobportal_value, array('class' => 'inputbox one form-control wjportal-form-input-field '.$this->class_prefix.'-input'.$wpjobportal_themeclass, 'data-validation' => $cssclass, 'size' => $wpjobportal_field->size, $wpjobportal_maxlength, $wpjobportal_readonly, 'placeholder' => wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle)));
                break;
            case 'email':
                if(wpjobportal::$wpjobportal_theme_chk == 1){
                    $wpjobportal_themeclass = getJobManagerThemeClass('text');
                }else{
                    $wpjobportal_themeclass = '';
                }
                $wpjobportal_html .= WPJOBPORTALformfield::email($wpjobportal_field->field, $wpjobportal_value, array('class' => 'inputbox one form-control wjportal-form-input-field '.$this->class_prefix.'-input'.$wpjobportal_themeclass, 'data-validation' => $cssclass, 'size' => $wpjobportal_field->size, $wpjobportal_maxlength, $wpjobportal_readonly, 'placeholder' => wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle)));
                break;
            case 'date':
                if(wpjobportal::$wpjobportal_theme_chk == 1){
                    $wpjobportal_themeclass = getJobManagerThemeClass('text');
                }else{
                    $wpjobportal_themeclass = '';
                }
                $wpjobportal_html .= WPJOBPORTALformfield::text($wpjobportal_field->field, $wpjobportal_value, array('class' => 'custom_date wjportal-form-date-field one '.$wpjobportal_themeclass, 'data-validation' => $cssclass,'autocomplete'=>'off'));
                break;
            case 'checkbox':
                if (!empty($wpjobportal_field->userfieldparams)) {
                    $wpjobportal_comboOptions = array();
                    $obj_option = json_decode($wpjobportal_field->userfieldparams);
                    if(empty($wpjobportal_value) || $wpjobportal_value == ''){
                        unset($wpjobportal_value);
                        $wpjobportal_value = array();
                    }
                    foreach ($obj_option AS $wpjobportal_option) {
                        if(is_array($wpjobportal_value)){
                            if( in_array($wpjobportal_option, $wpjobportal_value)){
                                $wpjobportal_check = 'checked="true"';
                            }else{
                                $wpjobportal_check = '';
                            }
                        }else{
                            $wpjobportal_check = '';
                        }
                        $wpjobportal_html .= '<span class="wpjobportal-form-radio-field">';
                        $wpjobportal_html .= '<input type="checkbox" ' . $wpjobportal_check . ' class="radiobutton" value="' . wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_option) . '" id="' . $wpjobportal_field->field . '_' . $wpjobportal_i . '" name="' . $wpjobportal_field->field . '[]">';
                        $wpjobportal_html .= '<label for="' . $wpjobportal_field->field . '_' . $wpjobportal_i . '" id="foruf_checkbox1">' . $wpjobportal_option . '</label>';
                        $wpjobportal_html .= '</span>';

                        $wpjobportal_i++;
                    }
                } else {
                    $wpjobportal_comboOptions = array('1' => $wpjobportal_field->fieldtitle);
                    $wpjobportal_html .= WPJOBPORTALformfield::checkbox($wpjobportal_field->field, $wpjobportal_comboOptions, $wpjobportal_value, array('class' => 'radiobutton'));
                }
                break;
            case 'radio':
                $wpjobportal_comboOptions = array();
                if (!empty($wpjobportal_field->userfieldparams)) {
                    $obj_option = json_decode($wpjobportal_field->userfieldparams);
                    for ($wpjobportal_i = 0; $wpjobportal_i < count($obj_option); $wpjobportal_i++) {
                        $wpjobportal_comboOptions[$obj_option[$wpjobportal_i]] = "$obj_option[$wpjobportal_i]";
                    }
                }
                $wpjobportal_jsFunction = '';
                if ($wpjobportal_field->depandant_field != null) {
                    $wpjobportal_jsFunction = "getDataForDepandantField('" . $wpjobportal_field->field . "','" . $wpjobportal_field->depandant_field . "',2,'',''" . $wpjobportal_theme_string . ");";
                }
                $wpjobportal_html .= WPJOBPORTALformfield::radiobutton($wpjobportal_field->field, $wpjobportal_comboOptions, $wpjobportal_value, array('data-validation' => $cssclass, "autocomplete" => "off", 'onclick' => $wpjobportal_jsFunction));
                break;
            case 'combo':
                if(wpjobportal::$wpjobportal_theme_chk == 1){
                    $wpjobportal_themeclass = getJobManagerThemeClass('select');
                }else{
                    $wpjobportal_themeclass = '';
                }
                $wpjobportal_comboOptions = array();
                if (!empty($wpjobportal_field->userfieldparams)) {
                    $obj_option = json_decode($wpjobportal_field->userfieldparams);
                    foreach ($obj_option as $opt) {
                        $wpjobportal_comboOptions[] = (object) array('id' => $opt, 'text' => $opt);
                    }
                }
                //code for handling dependent field
                $wpjobportal_jsFunction = '';
                if ($wpjobportal_field->depandant_field != null) {
                    $wpjobportal_jsFunction = "getDataForDepandantField('" . $wpjobportal_field->field . "','" . $wpjobportal_field->depandant_field . "','1','',''" . $wpjobportal_theme_string . ");";
                }
                //end
                if ($wpjobportal_field->placeholder != '') {
                    $placeholder = $wpjobportal_field->placeholder;
                } else {
                    $placeholder = esc_html(__('Select', 'wp-job-portal')) . ' ' . esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle));
                }
                $wpjobportal_html .= WPJOBPORTALformfield::select($wpjobportal_field->field, $wpjobportal_comboOptions, $wpjobportal_value, $placeholder, array('data-validation' => $cssclass, 'onchange' => $wpjobportal_jsFunction, 'class' => 'inputbox wjportal-form-select-field one form-control  '.$this->class_prefix.'-select '.$wpjobportal_themeclass));
                break;
            case 'multiple':
                if(wpjobportal::$wpjobportal_theme_chk == 1){
                    $wpjobportal_themeclass = getJobManagerThemeClass('select');
                }else{
                    $wpjobportal_themeclass = '';
                }
                $wpjobportal_comboOptions = array();
                if (!empty($wpjobportal_field->userfieldparams)) {
                    $obj_option = json_decode($wpjobportal_field->userfieldparams);
                    foreach ($obj_option as $opt) {
                        $wpjobportal_comboOptions[] = (object) array('id' => $opt, 'text' => $opt);
                    }
                }
                //code for handling dependent field
                $wpjobportal_jsFunction = '';
                // if ($wpjobportal_field->depandant_field != null) {
                //     $wpjobportal_jsFunction = "getDataForDepandantField('" . $wpjobportal_field->field . "','" . $wpjobportal_field->depandant_field . "','1','',''" . $wpjobportal_theme_string . ");";
                // }
                //end
                if ($wpjobportal_field->placeholder != '') {
                    $placeholder = $wpjobportal_field->placeholder;
                } else {
                    $placeholder = esc_html(__('Select', 'wp-job-portal')) . ' ' . esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle));
                }
                $wpjobportal_array = $wpjobportal_field->field;
                $wpjobportal_array .= '[]';
                //$wpjobportal_html .= WPJOBPORTALformfield::select($wpjobportal_field->field, $wpjobportal_comboOptions, $wpjobportal_value, $placeholder, array('data-validation' => $cssclass, 'onchange' => $wpjobportal_jsFunction, 'class' => 'inputbox wjportal-form-select-field one form-control  '.$this->class_prefix.'-select '.$wpjobportal_themeclass));
                $wpjobportal_html .= WPJOBPORTALformfield::select($wpjobportal_array, $wpjobportal_comboOptions, $wpjobportal_value, $placeholder, array('multiple' => 'multiple', 'class' => 'inputbox one wjportal-form-select-field wjportal-form-multi-select-field '. $wpjobportal_themeclass));
            break;
        }
        if ($wpjobportal_resumeform == 3) {// to handle top search case for job and resume listing.
            return $wpjobportal_html;
        }
        if ($wpjobportal_resumeform != 'f_jobsearch') {
            $wpjobportal_html .= '</div></div>';
        }
        if ($wpjobportal_resumeform == 1 || $wpjobportal_resumeform == 'f_jobsearch') {
            return $wpjobportal_html;
        } else {
            echo wp_kses($wpjobportal_html, WPJOBPORTAL_ALLOWED_TAGS);
        }
    }

    function getUserFieldByField($wpjobportal_field){
        $query = "SELECT * FROM `".wpjobportal::$_db->prefix."wj_portal_fieldsordering` WHERE field = '".esc_sql($wpjobportal_field)."' AND isuserfield = 1 ";
        $wpjobportal_field = wpjobportal::$_db->get_row($query);
        return $wpjobportal_field;
    }

    function getSearchUserFieldByFieldFor($wpjobportal_fieldfor){
        if(!is_numeric($wpjobportal_fieldfor)){
            return;
        }
        $query = "SELECT * FROM `".wpjobportal::$_db->prefix."wj_portal_fieldsordering` WHERE fieldfor = '".esc_sql($wpjobportal_fieldfor)."' AND isuserfield = 1 AND (search_user  = 1 OR search_visitor = 1 ) ";
        $wpjobportal_fields = wpjobportal::$_db->get_results($query);
        return $wpjobportal_fields;
    }

    function showCustomFields($wpjobportal_field, $wpjobportal_fieldfor, $params,$wpjobportal_uploadfor = '',$wpjobportal_entity_id = '',$wpjobportal_field_class = '', $wpjobportal_field_title_class = '', $wpjobportal_field_value_class = '') {// 2 new paramters to handle file upload field
        $wpjobportal_html = '';
        $fvalue = '';
        $wpjobportal_labelflag = wpjobportal::$_configuration['labelinlisting'];
        if($wpjobportal_fieldfor == 11){
            $wpjobportal_field = $this->getUserFieldByField($wpjobportal_field);
            if(empty($wpjobportal_field)){
                return false;
            }
        }
        if(!empty($params)){
            if(WPJOBPORTALincluder::getJSModel('common')->checkLanguageSpecialCase()){
                if(!preg_match('/[^a-zA-Z0-9]/', $params) > 0){
                    $params = wpjobportalphplib::wpJP_stripslashes($params);
                }
            }
            //$params = html_entity_decode($params, ENT_QUOTES);
            $wpjobportal_data = json_decode($params,true);
            if(isset($wpjobportal_data) && !empty($wpjobportal_data)){
                if(array_key_exists($wpjobportal_field->field, $wpjobportal_data)){
                    $fvalue = $wpjobportal_data[$wpjobportal_field->field];
                }
            }
        }
		if($wpjobportal_field_class == "")
			$wpjobportal_field_class = "wjportal-custom-field";
		if($wpjobportal_field_title_class == "")
			$wpjobportal_field_title_class = "wjportal-custom-field-tit";
		if($wpjobportal_field_value_class == "")
			$wpjobportal_field_value_class = "wjportal-custom-field-val";

        if($wpjobportal_field->userfieldtype=='file'){

           if($wpjobportal_uploadfor !=null && $wpjobportal_entity_id !=''){
               if($fvalue !=null){
                    //$wpjobportal_path = esc_url_raw(admin_url("?page=ticket&action=jstask&task=downloadbyname&id=".jssupportticket::$_data['custom']['ticketid']."&name=".$fvalue));
                    $wpjobportal_path = esc_url(wp_nonce_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'customfield', 'task'=>'downloadcustomfile', 'entity_id'=>esc_attr($wpjobportal_entity_id),'upload_for'=>esc_attr($wpjobportal_uploadfor),'file_name'=>$fvalue, 'action'=>'wpjobportaltask','wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid())),'wpjobportal_field_nonce'));
                    $wpjobportal_html = '
                        <div class="wpjobportal_upload_file_attachment">
                            ' .  $fvalue . '
                            <a class="button" target="_blank" href="' . esc_url($wpjobportal_path) . '">' . esc_html(__('Download', 'wp-job-portal')) . '</a>
                        </div>';
                    $fvalue = $wpjobportal_html;
                }
            }
        }elseif ($wpjobportal_field->userfieldtype == 'date' && $fvalue != '') {
            $fvalue = date_i18n(wpjobportal::$_configuration['date_format'],strtotime($fvalue));
        }

        if($wpjobportal_fieldfor == 1){ // jobs listing
			$wpjobportal_html = '<div class="'.$wpjobportal_field_class.'">';
			if (wpjobportal::$wpjobportal_theme_chk == 1) {
				if ($wpjobportal_labelflag == 1) {
					$wpjobportal_html .= '<span class="'.$wpjobportal_field_title_class.'">' . esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle)) . ': </span>';
				}
				$wpjobportal_html .= '<span class="'.$wpjobportal_field_value_class.'">' . wp_kses(wpjobportal::wpjobportal_getVariableValue($fvalue), WPJOBPORTAL_ALLOWED_TAGS). '</span>
							 </div>';
			} else {
				if ($wpjobportal_labelflag == 1) {
					$wpjobportal_html .= '<span class="'.$wpjobportal_field_title_class.'">' . esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle)) . ': </span>';
				}
				$wpjobportal_html .= '<span class="'.$wpjobportal_field_value_class.'">' . wp_kses(wpjobportal::wpjobportal_getVariableValue($fvalue), WPJOBPORTAL_ALLOWED_TAGS) . '</span>
							 </div>';
			}
        }elseif($wpjobportal_fieldfor == 2){ // job view
            if (wpjobportal::$wpjobportal_theme_chk == 1) {
                $wpjobportal_html = '<div class="wpj-jp-cf"  >
                    <span class="wpj-jp-cf-tit">' . esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle)) . ':&nbsp;</span)>
                    <span class="wpj-jp-cf-val">' . wp_kses(wpjobportal::wpjobportal_getVariableValue($fvalue), WPJOBPORTAL_ALLOWED_TAGS). '</span>
		</div>';
            } else {
                $wpjobportal_html = '<div class="'.$wpjobportal_field_class.'"  >
                    <span class="'.$wpjobportal_field_title_class.'">' . esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle)) . ': </span)>
                    <span class="'.$wpjobportal_field_value_class.'">' . wp_kses(wpjobportal::wpjobportal_getVariableValue($fvalue), WPJOBPORTAL_ALLOWED_TAGS) . '</span>
                </div>';
            }
        }elseif($wpjobportal_fieldfor == 7 || $wpjobportal_fieldfor == 9 || $wpjobportal_fieldfor == 10){ // myjobs, myresume, resume listing
            if (wpjobportal::$wpjobportal_theme_chk == 1) {
                $wpjobportal_html = '<div class="wpj-jp-cf">';
                $wpjobportal_html .= '<span class="wpj-jp-cf-tit">'.esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle)).':</span>';
                $wpjobportal_html .= '<span class="wpj-jp-cf-val">'.wp_kses(wpjobportal::wpjobportal_getVariableValue($fvalue), WPJOBPORTAL_ALLOWED_TAGS).'</span>';
                $wpjobportal_html .= '</div>';
            } else {
				$wpjobportal_html = '<div class="'.$wpjobportal_field_class.'">';
				$wpjobportal_html .= '<span class="'.$wpjobportal_field_title_class.'">'.esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle)).': </span>';
				$wpjobportal_html .= '<span class="'.$wpjobportal_field_value_class.'">'.wp_kses(wpjobportal::wpjobportal_getVariableValue($fvalue), WPJOBPORTAL_ALLOWED_TAGS).'</span>';
				$wpjobportal_html .= '</div>';
            }
        }elseif($wpjobportal_fieldfor == 4){ // company listing
            if (wpjobportal::$wpjobportal_theme_chk == 1) {
                $wpjobportal_html = '<div class="wpj-jp-cf">';
                $wpjobportal_html .= '<span class="wpj-jp-cf-tit">'.esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle)).': </span>';
                $wpjobportal_html .= '<span class="wpj-jp-cf-val">'.wp_kses(wpjobportal::wpjobportal_getVariableValue($fvalue), WPJOBPORTAL_ALLOWED_TAGS).'</span>';
                $wpjobportal_html .= '</div>';
            } else {
                $wpjobportal_html = '<div class="'.$wpjobportal_field_class.'">';
                $wpjobportal_html .= '<span class="'.$wpjobportal_field_title_class.'">'.esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle)).': </span>';
                $wpjobportal_html .= '<span class="'.$wpjobportal_field_value_class.'">'.wp_kses(wpjobportal::wpjobportal_getVariableValue($fvalue), WPJOBPORTAL_ALLOWED_TAGS).'</span>';
                $wpjobportal_html .= '</div>';
            }
        }elseif($wpjobportal_fieldfor == 5){ // company view
            if (wpjobportal::$wpjobportal_theme_chk == 1) {
                $wpjobportal_html = '<div class="wpj-jp-cf">';
                $wpjobportal_html .= '<span class="wpj-jp-cf-tit">'.esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle)).': </span>';
                $wpjobportal_html .= '<span class="wpj-jp-cf-val">'.wp_kses(wpjobportal::wpjobportal_getVariableValue($fvalue), WPJOBPORTAL_ALLOWED_TAGS).'</span>';
                $wpjobportal_html .= '</div>';
            } else {
                $wpjobportal_html = '<div class="'.$wpjobportal_field_class.'">';
                $wpjobportal_html .= '<span class="'.$wpjobportal_field_title_class.'">'.esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle)).': </span>';
                $wpjobportal_html .= '<span class="'.$wpjobportal_field_value_class.'">'.wp_kses(wpjobportal::wpjobportal_getVariableValue($fvalue), WPJOBPORTAL_ALLOWED_TAGS).'</span>';
                $wpjobportal_html .= '</div>';
            }
        }elseif($wpjobportal_fieldfor == 8){ // mycompanies
            if (wpjobportal::$wpjobportal_theme_chk == 1) {
                $wpjobportal_html = '<div class="wpj-jp-cf">';
                $wpjobportal_html .= '<span class="wpj-jp-cf-tit">'.esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle)).': </span>';
                $wpjobportal_html .= '<span class="wpj-jp-cf-val">'.wp_kses(wpjobportal::wpjobportal_getVariableValue($fvalue), WPJOBPORTAL_ALLOWED_TAGS).'</span>';
                $wpjobportal_html .= '</div>';
            } else {
                $wpjobportal_html = '<div class="'.$wpjobportal_field_class.'">';
                $wpjobportal_html .= '<span class="'.$wpjobportal_field_title_class.'">'.esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle)).': </span>';
                $wpjobportal_html .= '<span class="'.$wpjobportal_field_value_class.'">'.wp_kses(wpjobportal::wpjobportal_getVariableValue($fvalue), WPJOBPORTAL_ALLOWED_TAGS).'</span>';
                $wpjobportal_html .= '</div>';
            }
        }elseif($wpjobportal_fieldfor == 11 || $wpjobportal_fieldfor == 6){ // view resume
            return array('title' => $wpjobportal_field->fieldtitle, 'value' => $fvalue);
        }elseif($wpjobportal_fieldfor == 12){ // user detail
            $wpjobportal_html = '<div class="wpjobportal-user-data-text">';
            $wpjobportal_html .= '<span class="wpjobportal-user-data-title">'.esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle)).': </span>';
            $wpjobportal_html .= '<span class="wpjobportal-user-data-value">'.wp_kses(wpjobportal::wpjobportal_getVariableValue($fvalue), WPJOBPORTAL_ALLOWED_TAGS).'</span>';
            $wpjobportal_html .= '</div>';
        }

        return $wpjobportal_html;
    }

    function userFieldData($wpjobportal_field, $wpjobportal_fieldfor, $wpjobportal_section = null) {

        if (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
            $wpjobportal_published = ' isvisitorpublished = 1 ';
        } else {
            $wpjobportal_published = ' published = 1 ';
        }
        $wpjobportal_ff = '';
        if ($wpjobportal_fieldfor == 2 || $wpjobportal_fieldfor == 3) {
            $wpjobportal_ff = " AND fieldfor = 2 ";
        } elseif ($wpjobportal_fieldfor == 1 || $wpjobportal_fieldfor == 4) {
            $wpjobportal_ff = "AND fieldfor = 1 ";
        } elseif ($wpjobportal_fieldfor == 5) {
            $wpjobportal_ff = "AND fieldfor = 3 ";
        } elseif ($wpjobportal_fieldfor == 6) {
            //form resume
            if(is_numeric($wpjobportal_section)){
                $wpjobportal_ff = "AND fieldfor = 3 AND section = $wpjobportal_section ";
            }
        }
        $query = "SELECT field,fieldtitle,required,isuserfield,userfieldtype,readonly,maxlength,depandant_field,userfieldparams,description,placeholder,visible_field,visibleparams
        FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering
        WHERE isuserfield = 1 AND " . esc_sql($wpjobportal_published) . " AND field ='" . esc_sql($wpjobportal_field) . "'" . esc_sql($wpjobportal_ff);
        $wpjobportal_data = wpjobportaldb::get_row($query);
        return $wpjobportal_data;
    }

    function userFieldsData($wpjobportal_fieldfor, $wpjobportal_listing = null,$getpersonal = null) {
        if(!is_numeric($wpjobportal_fieldfor)){
            return false;
        }
        if (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
            $wpjobportal_published = ' isvisitorpublished = 1 ';
        } else {
            $wpjobportal_published = ' published = 1 ';
        }
        $wpjobportal_inquery = '';
        if ($wpjobportal_listing == 1) {
            $wpjobportal_inquery = ' AND showonlisting = 1 ';
        }
        if( $getpersonal == 1){
            $wpjobportal_inquery .= ' AND section = 1 ';
        }
        //$wpjobportal_inquery .= " AND (userfieldtype = 'text' OR userfieldtype = 'email')";
        $query = "SELECT field,fieldtitle,isuserfield,userfieldtype,userfieldparams
        FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering
        WHERE isuserfield = 1 AND " . esc_sql($wpjobportal_published) . " AND fieldfor =" . esc_sql($wpjobportal_fieldfor) . $wpjobportal_inquery;
        $query .= " ORDER BY ordering ASC "; // to handle the case of ordering on listing layouts
        $wpjobportal_data = wpjobportaldb::get_results($query);
        return $wpjobportal_data;
    }

    function getDataForDepandantFieldByParentField($wpjobportal_fieldfor, $wpjobportal_data) {
        if (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
            $wpjobportal_published = ' isvisitorpublished = 1 ';
        } else {
            $wpjobportal_published = ' published = 1 ';
        }
        $wpjobportal_value = '';
        $returnarray = array();
        $query = "SELECT field from " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering WHERE isuserfield = 1 AND " . esc_sql($wpjobportal_published) . " AND depandant_field ='" . esc_sql($wpjobportal_fieldfor) . "'";
        $wpjobportal_field = wpjobportaldb::get_var($query);
        if ($wpjobportal_data != null) {
            foreach ($wpjobportal_data as $wpjobportal_key => $wpjobportal_val) {
                if ($wpjobportal_key == $wpjobportal_field) {
                    $wpjobportal_value = $wpjobportal_val;
                }
            }
        }
        $query = "SELECT userfieldparams from " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering WHERE isuserfield = 1 AND " . esc_sql($wpjobportal_published) . " AND field ='" . esc_sql($wpjobportal_fieldfor) . "'";
        $wpjobportal_field = wpjobportaldb::get_var($query);
        $wpjobportal_fieldarray = json_decode($wpjobportal_field);
        if(!empty($wpjobportal_fieldarray)){
            foreach ($wpjobportal_fieldarray as $wpjobportal_key => $wpjobportal_val) {
                if ($wpjobportal_value == $wpjobportal_key)
                    $returnarray = $wpjobportal_val;
            }
        }
        return $returnarray;
    }

    function storeCustomFields($wpjobportal_entity,$wpjobportal_id,$wpjobportal_data){
        $wpjobportal_customfields = WPJOBPORTALincluder::getJSModel('fieldordering')->getUserfieldsfor($wpjobportal_entity);
        $params = array();
        $filelistadd = array();
        $filelistdelete = array();
        //custom field code start
        $customflagforadd = false;
        $customflagfordelete = false;
        $custom_field_namesforadd = array();
        $custom_field_namesfordelete = array();
        foreach($wpjobportal_customfields AS $wpjobportal_field){
            $wpjobportal_vardata = '';
            if ($wpjobportal_field->userfieldtype == 'date') {
                $wpjobportal_vardata = (isset($wpjobportal_data[$wpjobportal_field->field]) && $wpjobportal_data[$wpjobportal_field->field] !='')  ? gmdate('Y-m-d H:i:s',strtotime($wpjobportal_data[$wpjobportal_field->field])) : '';
            } elseif($wpjobportal_field->userfieldtype == 'file'){ // to handle upload field seprately
                if(isset($wpjobportal_data[$wpjobportal_field->field.'_1']) && $wpjobportal_data[$wpjobportal_field->field.'_1']== 0){
                    $wpjobportal_vardata = $wpjobportal_data[$wpjobportal_field->field.'_2'];
                }
                $customflagforadd = true;
                $custom_field_namesforadd[]=$wpjobportal_field->field;
            }else{
                $wpjobportal_vardata = isset($wpjobportal_data[$wpjobportal_field->field]) ? $wpjobportal_data[$wpjobportal_field->field] : '';
            }
            if(isset($wpjobportal_data[$wpjobportal_field->field.'_1']) && $wpjobportal_data[$wpjobportal_field->field.'_1'] == 1){
                $customflagfordelete = true;
                $custom_field_namesfordelete[]= $wpjobportal_data[$wpjobportal_field->field.'_2'];
            }
            if(!empty($wpjobportal_vardata)){
                if(is_array($wpjobportal_vardata)){
                    $wpjobportal_vardata = implode(', ', $wpjobportal_vardata);
                }
                $params[$wpjobportal_field->field] = wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_vardata);
            }
        }
        // code for unpublished fields to be written later as in wp jobs by shees
        $params = wpjobportal::wpjobportal_sanitizeData($params);
        if(WPJOBPORTALincluder::getJSModel('common')->checkLanguageSpecialCase()){
            $params = WPJOBPORTALincluder::getJSModel('common')->stripslashesFull($params);// remove slashes with quotes.
        }
        $params = wp_json_encode($params);
        $wpjobportal_uploadfor = '';
        if($wpjobportal_entity == WPJOBPORTAL_COMPANY){
            //$wpjobportal_row->update(array('id' => $wpjobportal_id, 'status' => -1))
            $wpjobportal_row = WPJOBPORTALincluder::getJSTable('company');
            $wpjobportal_row->update(array('id' => $wpjobportal_id, 'params' => $params));
            //$query = "UPDATE `".wpjobportal::$_db->prefix."wj_portal_companies` SET `params`='$params' WHERE `id` =". esc_sql($wpjobportal_id);
            //wpjobportal::$_db->query($query);
            $wpjobportal_entity_for = 'company';
        }else if($wpjobportal_entity == 2){
            $wpjobportal_row = WPJOBPORTALincluder::getJSTable('job');
            $wpjobportal_row->update(array('id' => $wpjobportal_id, 'params' => $params));
            // $query = "UPDATE `".wpjobportal::$_db->prefix."wj_portal_jobs` SET `params`='$params' WHERE `id` =". esc_sql($wpjobportal_id);
            // wpjobportal::$_db->query($query);
            $wpjobportal_entity_for = 'job';
        }else if($wpjobportal_entity == WPJOBPORTAL_RESUME){
            $wpjobportal_row = WPJOBPORTALincluder::getJSTable('resume');
            $wpjobportal_row->update(array('id' => $wpjobportal_id, 'params' => $params));
            // $query = "UPDATE `".wpjobportal::$_db->prefix."wj_portal_resume` SET `params`='$params' WHERE `id` =". esc_sql($wpjobportal_id);
            // wpjobportal::$_db->query($query);
            $wpjobportal_entity_for = 'resume';
        }elseif ($wpjobportal_entity == 4) {
            $wpjobportal_row = WPJOBPORTALincluder::getJSTable('users');
            $wpjobportal_row->update(array('id' => $wpjobportal_id, 'params' => $params));
            // $query = "UPDATE `".wpjobportal::$_db->prefix."wj_portal_users` SET `params`='$params' WHERE `id` =". esc_sql($wpjobportal_id);
            // wpjobportal::$_db->query($query);
            $wpjobportal_entity_for = 'user';
        }

        //removing custom field attachments
        if($customflagfordelete == true){
            foreach ($custom_field_namesfordelete as $wpjobportal_key) {
               $res = $this->removeFileCustom($wpjobportal_id,$wpjobportal_key,$wpjobportal_entity_for);
            }
        }
        //storing custom field attachments
        if($customflagforadd == true){
            foreach ($custom_field_namesforadd as $wpjobportal_key) {
                if (isset($_FILES[$wpjobportal_key])) {
                    if ($_FILES[$wpjobportal_key]['size'] > 0) { // logo
                       $res = $this->uploadFileCustom($wpjobportal_id,$wpjobportal_key,$wpjobportal_entity_for);
                    }
                }
            }
        }
    }

    function removeFileCustom($wpjobportal_id,$wpjobportal_key,$wpjobportal_uploadfor){
        $filename = wpjobportalphplib::wpJP_str_replace(' ', '_', $wpjobportal_key);
        $wpjobportal_maindir = wp_upload_dir();
        $basedir = $wpjobportal_maindir['basedir'];
        $wpjobportal_datadirectory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');

        $wpjobportal_path = $basedir . '/' . $wpjobportal_datadirectory. '/data';

        if($wpjobportal_uploadfor == 'company'){
            $wpjobportal_path = $wpjobportal_path . '/employer/comp_'.$wpjobportal_id.'/custom_uploads';
        }elseif($wpjobportal_uploadfor == 'job'){
            $wpjobportal_path = $wpjobportal_path . '/employer/job_'.$wpjobportal_id.'/custom_uploads';
        }elseif($wpjobportal_uploadfor == 'resume'){
            $wpjobportal_path = $wpjobportal_path . '/jobseeker/resume_'.$wpjobportal_id.'/custom_uploads';
        }elseif($wpjobportal_uploadfor == 'profile'){
            $wpjobportal_path = $wpjobportal_path . '/profile/profile_'.$wpjobportal_id.'/custom_uploads';
        }


        $wpjobportal_userpath = $wpjobportal_path .'/'.$filename;
        wp_delete_file($wpjobportal_userpath);
        return ;
    }

    function uploadFileCustom($wpjobportal_id,$wpjobportal_field,$wpjobportal_uploadfor){
         WPJOBPORTALincluder::getObjectClass('uploads')->storeCustomUploadFile($wpjobportal_id,$wpjobportal_field,$wpjobportal_uploadfor);
    }

    function storeUploadFieldValueInParams($wpjobportal_entity_id,$filename,$wpjobportal_field,$wpjobportal_uploadfor){
        if(!is_numeric($wpjobportal_entity_id)){
            return false;
        }
        /*

        //$query = "UPDATE `".wpjobportal::$_db->prefix."wj_portal_companies` SET `params`='$params' WHERE `id` =". esc_sql($wpjobportal_id);
// $query = "UPDATE `".wpjobportal::$_db->prefix."wj_portal_jobs` SET `params`='$params' WHERE `id` =". esc_sql($wpjobportal_id);
// $query = "UPDATE `".wpjobportal::$_db->prefix."wj_portal_resume` SET `params`='$params' WHERE `id` =". esc_sql($wpjobportal_id);
// $query = "UPDATE `".wpjobportal::$_db->prefix."wj_portal_users` SET `params`='$params' WHERE `id` =". esc_sql($wpjobportal_id);
        */
        $query = '';
        if($wpjobportal_uploadfor == 'company'){
            $query = "SELECT params FROM `".wpjobportal::$_db->prefix."wj_portal_companies` WHERE id = ".esc_sql($wpjobportal_entity_id);
        }elseif($wpjobportal_uploadfor == 'job'){
            $query = "SELECT params FROM `".wpjobportal::$_db->prefix."wj_portal_jobs` WHERE id = ".esc_sql($wpjobportal_entity_id);
        }elseif($wpjobportal_uploadfor == 'resume'){
            $query = "SELECT params FROM `".wpjobportal::$_db->prefix."wj_portal_resume` WHERE id = ".esc_sql($wpjobportal_entity_id);
        }elseif($wpjobportal_uploadfor == 'user'){
            $query = "SELECT params FROM `".wpjobportal::$_db->prefix."wj_portal_users` WHERE id = ".esc_sql($wpjobportal_entity_id);
        }
        $params = '';
        if($query != ''){
            $params = wpjobportal::$_db->get_var($query);
        }
        if($params != ''){
            $wpjobportal_decoded_params = json_decode($params,true);
        }else{
            $wpjobportal_decoded_params = array();
        }

        $wpjobportal_decoded_params[$wpjobportal_field] = $filename;
        $params = wp_json_encode($wpjobportal_decoded_params, JSON_UNESCAPED_UNICODE);


        if($wpjobportal_uploadfor == 'company'){
            $wpjobportal_row = WPJOBPORTALincluder::getJSTable('company');
            $wpjobportal_row->update(array('id' => $wpjobportal_entity_id, 'params' => $params));
            //$query = "UPDATE `".wpjobportal::$_db->prefix."wj_portal_companies` SET `params`='$params' WHERE `id` =". esc_sql($wpjobportal_id);
            //wpjobportal::$_db->query($query);
        }else if($wpjobportal_uploadfor == 'job'){
            $wpjobportal_row = WPJOBPORTALincluder::getJSTable('job');
            $wpjobportal_row->update(array('id' => $wpjobportal_entity_id, 'params' => $params));
            // $query = "UPDATE `".wpjobportal::$_db->prefix."wj_portal_jobs` SET `params`='$params' WHERE `id` =". esc_sql($wpjobportal_id);
            // wpjobportal::$_db->query($query);
        }else if($wpjobportal_uploadfor == 'resume'){
            $wpjobportal_row = WPJOBPORTALincluder::getJSTable('resume');
            $wpjobportal_row->update(array('id' => $wpjobportal_entity_id, 'params' => $params));
            // $query = "UPDATE `".wpjobportal::$_db->prefix."wj_portal_resume` SET `params`='$params' WHERE `id` =". esc_sql($wpjobportal_id);
            // wpjobportal::$_db->query($query);
        }elseif ($wpjobportal_uploadfor == 'user') {
            $wpjobportal_row = WPJOBPORTALincluder::getJSTable('users');
            $wpjobportal_row->update(array('id' => $wpjobportal_entity_id, 'params' => $params));
            // $query = "UPDATE `".wpjobportal::$_db->prefix."wj_portal_users` SET `params`='$params' WHERE `id` =". esc_sql($wpjobportal_id);
            // wpjobportal::$_db->query($query);
        }

        return;
    }

}
?>
