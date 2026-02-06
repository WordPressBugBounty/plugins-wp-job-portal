<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALpopup {
    public $class_prefix = '';
    function __construct(){
        if(wpjobportal::$wpjobportal_theme_chk == 2){
            $this->class_prefix = 'jsjb-jh';
        }elseif(wpjobportal::$wpjobportal_theme_chk == 1){
            $this->class_prefix = 'wpj-jp';
        }
    }

    function canAutoSubmit($wpjobportal_result){
        return true;
        $wpjobportal_totalcredits = 0;
        $wpjobportal_i = 0;
        foreach ($wpjobportal_result AS $wpjobportal_value) {
            $wpjobportal_totalcredits += $wpjobportal_value->credits;
            $wpjobportal_i++;
        }
        if($wpjobportal_i > 1){ // show popup on multioption
            return false;
        }
        if($wpjobportal_totalcredits == 0){
            return true;
        }else{
            return false;
        }
    }

     function getPopupForAdmin($wpjobportal_actionname,$wpjobportal_themecall=null,$wpjobportal_pageid=null) {
        $wpjobportal_uid = WPJOBPORTALRequest::getVar('userid');
        $wpjobportal_module = WPJOBPORTALRequest::getVar('module');
        if($wpjobportal_pageid == null){
            $wpjobportal_pageid = wpjobportal::wpjobportal_getPageid();
        }
        $wpjobportal_result = $this->getActionDetailForpopup($wpjobportal_actionname,$wpjobportal_pageid);

        if ($wpjobportal_result != false) {
            $wpjobportal_html = null;
            if(in_array('credits', wpjobportal::$_active_addons) && wpjobportal::$_config->getConfigValue('submission_type')==3){
                $autosubmit = false ;
            }else{
                $autosubmit = true ;
            }
            $wpjobportal_isadmin = WPJOBPORTALRequest::getVar('isadmin');
            if($wpjobportal_isadmin){
                $wpjobportalPopupResumeFormProceeds = 'wpjobportalPopupResumeFormProceedsAdmin';
                $wpjobportalPopupFormProceeds = 'wpjobportalPopupFormProceedsAdmin';
                $wpjobportalPopupProceeds = 'wpjobportalPopupProceedsAdmin';
                $wpjobportal_proceedlang = esc_html(__('Proceed Without Paying','wp-job-portal'));
            }else{
                $wpjobportalPopupResumeFormProceeds = 'wpjobportalPopupResumeFormProceeds';
                $wpjobportalPopupFormProceeds = 'wpjobportalPopupFormProceeds';
                $wpjobportalPopupProceeds = 'wpjobportalPopupProceeds';
                $wpjobportal_proceedlang = esc_html(__('Proceed','wp-job-portal'));
            }
            if($autosubmit == true){
                $objectid = WPJOBPORTALRequest::getVar('id');
                $srcid = WPJOBPORTALRequest::getVar('srcid');
                $anchorid = WPJOBPORTALRequest::getVar('anchorid');
                $wpjobportal_formid = WPJOBPORTALRequest::getVar('formid');
                $wpjobportal_action = 0;
                if(wpjobportal::$_common->wpjp_isadmin()){
                    if(is_array($wpjobportal_result['value'])){
                        foreach($wpjobportal_result['value'] AS $wpjobportal_value){
                            $wpjobportal_action = $wpjobportal_value->id;
                        }
                    }elseif(is_numeric($wpjobportal_result['value'])){
                        $wpjobportal_action = $wpjobportal_result['value'];
                    }

                }
                $wpjobportal_action = '';
                if ($wpjobportal_formid) { // popup in case of form is opened
                    if ($wpjobportal_formid == 'resumeform') {
                        wp_register_script( 'wpjobportal-inline-handle', '' );
                        wp_enqueue_script( 'wpjobportal-inline-handle' );
                        $wpjobportal_inline_js_script = '
                                    '.$wpjobportalPopupResumeFormProceeds.'(\'' . $wpjobportal_action . '\');
                                ';
                                wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
                    } else {
                        wp_register_script( 'wpjobportal-inline-handle', '' );
                        wp_enqueue_script( 'wpjobportal-inline-handle' );
                        $wpjobportal_inline_js_script = '
                                    '.$wpjobportalPopupFormProceeds.'(\'' . $wpjobportal_formid . '\',\'' . $wpjobportal_action . '\');
                                ';
                                wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
                    }
                } elseif ($srcid && $anchorid) { // popup in case of add to gold and feature
                    wp_register_script( 'wpjobportal-inline-handle', '' );
                        wp_enqueue_script( 'wpjobportal-inline-handle' );
                        $wpjobportal_inline_js_script = '
                                '.$wpjobportalPopupProceeds.'(\'' . $wpjobportal_actionname . '\',' . $objectid . ',\'' . $srcid . '\',\'' . $anchorid . '\',\'' . $wpjobportal_action . '\',\'' . $wpjobportal_themecall . '\');
                            ';
                            wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
                } elseif($wpjobportal_actionname == 'job_apply') { // popup in case of view company, resume, job contact detail
                    if($wpjobportal_themecall != null){
                        wp_register_script( 'wpjobportal-inline-handle', '' );
                        wp_enqueue_script( 'wpjobportal-inline-handle' );
                        $wpjobportal_inline_js_script = '
                                getApplyNowByJobid('. $objectid . ',' . $wpjobportal_pageid .',1);
                            ';
                            wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
                    }else{
                        wp_register_script( 'wpjobportal-inline-handle', '' );
                        wp_enqueue_script( 'wpjobportal-inline-handle' );
                        $wpjobportal_inline_js_script = '
                                getApplyNowByJobid('. $objectid . ',' . $wpjobportal_pageid .');
                            ';
                            wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
                    }
                }else { // popup in case of view company, resume, job contact detail
                    wp_register_script( 'wpjobportal-inline-handle', '' );
                        wp_enqueue_script( 'wpjobportal-inline-handle' );
                        $wpjobportal_inline_js_script = '
                                location.href= "' . esc_url($wpjobportal_result['link']) . '";
                            ';
                            wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
                }
            }elseif($wpjobportal_themecall!=null){
                $wpjobportal_credit_small_flag=0;
                $wpjobportal_font_aw_value="";
                $absolute_class="";
                $s_class="";

                        $wpjobportal_credit_small_flag=1;
                        $wpjobportal_font_aw_value="fa-database";
                        $absolute_class=" ".esc_attr($this->class_prefix)."-fa-absolute ";
                        $s_class=" ".esc_attr($this->class_prefix)."-fa-small ";

                $wpjobportal_html .= '<div id="'.esc_attr($this->class_prefix).'-popup-background"></div>';
                $wpjobportal_html .= '<div id="'.esc_attr($this->class_prefix).'-popup">';
                $wpjobportal_html .='<div class="'.esc_attr($this->class_prefix).'-modal-wrp">
                            <div class="'.esc_attr($this->class_prefix).'-modal-left-image-wrp">';
                                if($wpjobportal_credit_small_flag==1){
                                    $wpjobportal_html .='<i class="fa '.$wpjobportal_font_aw_value. $s_class .' " aria-hidden="true" ></i>';
                                }
                            $wpjobportal_html .='<i class="fa '.$wpjobportal_font_aw_value. $absolute_class .' '.esc_attr($this->class_prefix).'-modal-left-image" aria-hidden="true" ></i>
                            </div>
                            <div class="'.esc_attr($this->class_prefix).'-modal-header">
                                <a title="close" class="'.esc_attr($this->class_prefix).'-modal-close-icon-wrap" >
                                    <i class="fa fa-times-circle-o '.esc_attr($this->class_prefix).'-modal-close-icon" aria-hidden="true"></i>
                                </a>
                                <h2 class="'.esc_attr($this->class_prefix).'-modal-title">'. $wpjobportal_result['title-text'] .' '.$wpjobportal_result['title'].' </h2>
                            </div>
                            <div class="col-md-12 '.esc_attr($this->class_prefix).'-modal-credit-row-wrp">
                                <div class="'.esc_attr($this->class_prefix).'-modal-credit-row color">
                                    <span class="tit">'. esc_html(__("Total Credits", "wp-job-portal")) .'</span>
                                    <span class="val">'.$wpjobportal_result['totalcredits'] .'</span>
                                </div>';
                $wpjobportal_totalcredituse = 0;
                $wpjobportal_action = 0;
                foreach ($wpjobportal_result['value'] AS $wpjobportal_value) {
                    $wpjobportal_html .='<div class="'.esc_attr($this->class_prefix).'-modal-credit-row">';
                    $wpjobportal_html .='<span class="tit">';
                    if (sizeof($wpjobportal_result['value']) > 1) {
                        $wpjobportal_html .= '<input name="credits" type="radio" class="checkboxes" data-credits="' . $wpjobportal_value->credits . '" data-totalcredits="' . $wpjobportal_result['totalcredits'] . '" value=' . $wpjobportal_value->id . ' />';
                        $wpjobportal_action = -1;
                    } else {
                        $wpjobportal_action = $wpjobportal_value->id;
                    }
                    $wpjobportal_html .= esc_html(__('Credit for action', 'wp-job-portal'));
                    $wpjobportal_expirydatearray = array('featured_job','gold_job','add_job','featured_company','gold_company','featured_resume','gold_resume','job_alert_time');
                    if(in_array($wpjobportal_value->creditaction, $wpjobportal_expirydatearray)){
                        $wpjobportal_html .= '<span class="expiry"> (' . esc_html(__('Expire in', 'wp-job-portal')) . ' ' . $wpjobportal_value->expiry . ' ' . esc_html(__('Days', 'wp-job-portal')) . ')</span>';
                    }elseif($wpjobportal_value->creditaction == 'job_alert_lifetime'){
                        $wpjobportal_html .= '<span class="expiry"> ('.esc_html(__('Life time alerts','wp-job-portal')).') </span>';
                    }
                    $wpjobportal_html .= '</span>';
                    $wpjobportal_html .= '<span class="val">' . $wpjobportal_value->credits . '</span>';
                    $wpjobportal_html .= '</div>';
                    $wpjobportal_totalcredituse = $wpjobportal_value->credits;
                }

                $wpjobportal_html .='<div class="'.esc_attr($this->class_prefix).'-modal-credit-row color">';
                    $wpjobportal_html .='<span class="tit" >'. esc_html(__('Credits remaining after proceed', 'wp-job-portal')) .'</span>';
                    $wpjobportal_html .='<span class="val" id="remaing-credits">'. ($wpjobportal_result['totalcredits'] - $wpjobportal_totalcredituse) .'</span>';
                $wpjobportal_html .='</div>';
                $wpjobportal_html .='</div>';

                if($wpjobportal_actionname == 'job_apply') {
                    $wpjobportal_html .= '<div class="wpjobportal-job-apply-meesage">';
                    $wpjobportal_html .= esc_html(__('Credits will only be deducted if you select a resume and click Apply Now on next popup.', 'wp-job-portal'));
                    $wpjobportal_html .= '</div>';
                }

                $wpjobportal_html .='<div class="col-md-11 col-md-offset-1 '.esc_attr($this->class_prefix).'-modal-data-wrp">
                        <div class="modal-body '.esc_attr($this->class_prefix).'-modal-body">
                              <div class="'.esc_attr($this->class_prefix).'-modal-credit-action-btn-wrp">
                                  <a title="cancel" href="#" class="'.esc_attr($this->class_prefix).'-modal-credit-action-btn" onclick="wpjobportalClosePopup(\''.$wpjobportal_themecall.'\');">
                                      ' . esc_html(__('Cancel', 'wp-job-portal')) . '
                                  </a>';
                $objectid = WPJOBPORTALRequest::getVar('id');
                $srcid = WPJOBPORTALRequest::getVar('srcid');
                $anchorid = WPJOBPORTALRequest::getVar('anchorid');
                $wpjobportal_formid = WPJOBPORTALRequest::getVar('formid');
                if ($wpjobportal_formid) { // popup in case of form is opened
                    if ($wpjobportal_formid == 'resumeform') {
                        $wpjobportal_html .= '<a href="#" class="'.esc_attr($this->class_prefix).'-modal-credit-action-btn color" onclick="'.$wpjobportalPopupResumeFormProceeds.'(\'' . $wpjobportal_action . '\');">' . $wpjobportal_proceedlang . '</a>';
                    }else {
                        $wpjobportal_html .= '<a href="#" class="'.esc_attr($this->class_prefix).'-modal-credit-action-btn color" onclick="'.$wpjobportalPopupFormProceeds.'(\'' . $wpjobportal_formid . '\',\'' . $wpjobportal_action . '\');">' . $wpjobportal_proceedlang . '</a>';
                    }
                } elseif ($srcid && $anchorid) { // popup in case of add to gold and feature
                    $wpjobportal_html .= '<a href="#" class="'.esc_attr($this->class_prefix).'-modal-credit-action-btn color" onclick="'.$wpjobportalPopupProceeds.'(\'' . $wpjobportal_actionname . '\',' . $objectid . ',\'' . $srcid . '\',\'' . $anchorid . '\',\'' . $wpjobportal_action . '\',\''.$wpjobportal_themecall.'\');">' . $wpjobportal_proceedlang . '</a>';
                } elseif($wpjobportal_actionname == 'job_apply') {
                        $wpjobportal_html .= '<a href="#" class="'.esc_attr($this->class_prefix).'-modal-credit-action-btn color" onclick="getApplyNowByJobid('. $objectid . ',' . $wpjobportal_pageid .',1);">' . $wpjobportal_proceedlang . '</a>';
                }else { // popup in case of view company, resume, job contact detail
                    $wpjobportal_html .= '<a href="' . esc_url($wpjobportal_result['link']) .'" class="'.esc_attr($this->class_prefix).'-modal-credit-action-btn color" onclick="return validateRemaingCredits();" >' . esc_html(__('Proceed', 'wp-job-portal')) . '</a>';
                }
                $wpjobportal_html .='</div>
                        </div>
                    </div>
                </div>';
                $wpjobportal_html .= '</div>';
            }else{
                $objectid = WPJOBPORTALRequest::getVar('id');
                $srcid = WPJOBPORTALRequest::getVar('srcid');
                $anchorid = WPJOBPORTALRequest::getVar('anchorid');
                $wpjobportal_formid = WPJOBPORTALRequest::getVar('formid');
                if(!isset($wpjobportal_action)){
                    $wpjobportal_action = 0;
                }
                $wpjobportal_html .= apply_filters('wpjobportal_addons_popup_admin_credits',false,$wpjobportal_module,$wpjobportal_uid,$wpjobportal_formid,$wpjobportal_action,$srcid,$anchorid,$wpjobportal_actionname,$objectid,$wpjobportal_proceedlang);
            }
        } else {
            $wpjobportal_html = $this->getErrorPopupFor($wpjobportal_actionname,$wpjobportal_pageid=null,$wpjobportal_themecall=null);
        }
        return $wpjobportal_html;
    }

    function getPopupFor($wpjobportal_actionname,$wpjobportal_themecall=null,$wpjobportal_pageid=null) {

        if($wpjobportal_pageid == null){
            $wpjobportal_pageid = wpjobportal::wpjobportal_getPageid();
        }

        $wpjobportal_result = true/*$this->getActionDetailForpopup($wpjobportal_actionname,$wpjobportal_pageid)*/;
        if ($wpjobportal_result != false) {
            $wpjobportal_html = null;
            $autosubmit = true;
            $wpjobportal_isadmin = WPJOBPORTALRequest::getVar('isadmin');
            if($wpjobportal_isadmin){
                $wpjobportalPopupResumeFormProceeds = 'wpjobportalPopupResumeFormProceedsAdmin';
                $wpjobportalPopupFormProceeds = 'wpjobportalPopupFormProceedsAdmin';
                $wpjobportalPopupProceeds = 'wpjobportalPopupProceedsAdmin';
                $wpjobportal_proceedlang = esc_html(__('Proceed Without Paying','wp-job-portal'));
            }else{
                $wpjobportalPopupResumeFormProceeds = 'wpjobportalPopupResumeFormProceeds';
                $wpjobportalPopupFormProceeds = 'wpjobportalPopupFormProceeds';
                $wpjobportalPopupProceeds = 'wpjobportalPopupProceeds';
                $wpjobportal_proceedlang = esc_html(__('Proceed','wp-job-portal'));
            }
            if($autosubmit == true){
                $objectid = WPJOBPORTALRequest::getVar('id');
                $srcid = WPJOBPORTALRequest::getVar('srcid');
                $anchorid = WPJOBPORTALRequest::getVar('anchorid');
                $wpjobportal_formid = WPJOBPORTALRequest::getVar('formid');
                $wpjobportal_action = 0;
               if(isset($wpjobportal_result)){
                   /* foreach($wpjobportal_result['value'] AS $wpjobportal_value){
                        $wpjobportal_action = $wpjobportal_value->id;
                    }*/
                    $wpjobportal_action = '';
                }
                if ($wpjobportal_formid) { // popup in case of form is opened
                    if ($wpjobportal_formid == 'resumeform') {
                        wp_register_script( 'wpjobportal-inline-handle', '' );
                        wp_enqueue_script( 'wpjobportal-inline-handle' );
                        $wpjobportal_inline_js_script = '
                                    '.$wpjobportalPopupResumeFormProceeds.'(\'' . $wpjobportal_action . '\');
                                ';
                                wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
                    } else {
                        wp_register_script( 'wpjobportal-inline-handle', '' );
                        wp_enqueue_script( 'wpjobportal-inline-handle' );
                        $wpjobportal_inline_js_script = '
                                    '.$wpjobportalPopupFormProceeds.'(\'' . $wpjobportal_formid . '\',\'' . $wpjobportal_action . '\');
                                ';
                                wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
                    }
                } elseif ($srcid && $anchorid) { // popup in case of add to gold and feature
                    wp_register_script( 'wpjobportal-inline-handle', '' );
                        wp_enqueue_script( 'wpjobportal-inline-handle' );
                        $wpjobportal_inline_js_script = '
                                '.$wpjobportalPopupProceeds.'(\'' . $wpjobportal_actionname . '\',' . $objectid . ',\'' . $srcid . '\',\'' . $anchorid . '\',\'' . $wpjobportal_action . '\',\'' . $wpjobportal_themecall . '\');
                            ';
                            wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
                } elseif($wpjobportal_actionname == 'job_apply') { // popup in case of view company, resume, job contact detail
                    if($wpjobportal_themecall != null){
                        wp_register_script( 'wpjobportal-inline-handle', '' );
                        wp_enqueue_script( 'wpjobportal-inline-handle' );
                        $wpjobportal_inline_js_script = '
                                getApplyNowByJobid('. $objectid . ',' . $wpjobportal_pageid .',1);
                            ';
                            wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
                    }else{
                        wp_register_script( 'wpjobportal-inline-handle', '' );
                        wp_enqueue_script( 'wpjobportal-inline-handle' );
                        $wpjobportal_inline_js_script = '
                                getApplyNowByJobid('. $objectid . ',' . $wpjobportal_pageid .');
                            ';
                            wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
                    }
                }else { // popup in case of view company, resume, job contact detail
                    wp_register_script( 'wpjobportal-inline-handle', '' );
                        wp_enqueue_script( 'wpjobportal-inline-handle' );
                        $wpjobportal_inline_js_script = '
                                location.href= "' . esc_url($wpjobportal_result['link']) . '";
                            ';
                            wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
                }
            }elseif($wpjobportal_themecall!=null){
                $wpjobportal_credit_small_flag=0;
                $wpjobportal_font_aw_value="";
                $absolute_class="";
                $s_class="";

                        $wpjobportal_credit_small_flag=1;
                        $wpjobportal_font_aw_value="fa-database";
                        $absolute_class=" ".esc_attr($this->class_prefix)."-fa-absolute ";
                        $s_class=" ".esc_attr($this->class_prefix)."-fa-small ";

                $wpjobportal_html .= '<div id="'.esc_attr($this->class_prefix).'-popup-background"></div>';
                $wpjobportal_html .= '<div id="'.esc_attr($this->class_prefix).'-popup">';
                $wpjobportal_html .='<div class="'.esc_attr($this->class_prefix).'-modal-wrp">
                            <div class="'.esc_attr($this->class_prefix).'-modal-left-image-wrp">';
                                if($wpjobportal_credit_small_flag==1){
                                    $wpjobportal_html .='<i class="fa '.$wpjobportal_font_aw_value. $s_class .' " aria-hidden="true" ></i>';
                                }
                            $wpjobportal_html .='<i class="fa '.$wpjobportal_font_aw_value. $absolute_class .' '.esc_attr($this->class_prefix).'-modal-left-image" aria-hidden="true" ></i>
                            </div>
                            <div class="'.esc_attr($this->class_prefix).'-modal-header">
                                <a title="close" class="'.esc_attr($this->class_prefix).'-modal-close-icon-wrap" >
                                    <i class="fa fa-times-circle-o '.esc_attr($this->class_prefix).'-modal-close-icon" aria-hidden="true"></i>
                                </a>
                                <h2 class="'.esc_attr($this->class_prefix).'-modal-title">'. $wpjobportal_result['title-text'] .' '.$wpjobportal_result['title'].' </h2>
                            </div>
                            <div class="col-md-12 '.esc_attr($this->class_prefix).'-modal-credit-row-wrp">
                                <div class="'.esc_attr($this->class_prefix).'-modal-credit-row color">
                                    <span class="tit">'. esc_html(__("Total Credits", "wp-job-portal")) .'</span>
                                    <span class="val">'.$wpjobportal_result['totalcredits'] .'</span>
                                </div>';
                $wpjobportal_totalcredituse = 0;
                $wpjobportal_action = 0;
                foreach ($wpjobportal_result['value'] AS $wpjobportal_value) {
                    $wpjobportal_html .='<div class="'.esc_attr($this->class_prefix).'-modal-credit-row">';
                    $wpjobportal_html .='<span class="tit">';
                    if (sizeof($wpjobportal_result['value']) > 1) {
                        $wpjobportal_html .= '<input name="credits" type="radio" class="checkboxes" data-credits="' . $wpjobportal_value->credits . '" data-totalcredits="' . $wpjobportal_result['totalcredits'] . '" value=' . $wpjobportal_value->id . ' />';
                        $wpjobportal_action = -1;
                    } else {
                        $wpjobportal_action = $wpjobportal_value->id;
                    }
                    $wpjobportal_html .= esc_html(__('Credit for action', 'wp-job-portal'));
                    $wpjobportal_expirydatearray = array('featured_job','gold_job','add_job','featured_company','gold_company','featured_resume','gold_resume','job_alert_time');
                    if(in_array($wpjobportal_value->creditaction, $wpjobportal_expirydatearray)){
                        $wpjobportal_html .= '<span class="expiry"> (' . esc_html(__('Expire in', 'wp-job-portal')) . ' ' . $wpjobportal_value->expiry . ' ' . esc_html(__('Days', 'wp-job-portal')) . ')</span>';
                    }elseif($wpjobportal_value->creditaction == 'job_alert_lifetime'){
                        $wpjobportal_html .= '<span class="expiry"> ('.esc_html(__('Life time alerts','wp-job-portal')).') </span>';
                    }
                    $wpjobportal_html .= '</span>';
                    $wpjobportal_html .= '<span class="val">' . $wpjobportal_value->credits . '</span>';
                    $wpjobportal_html .= '</div>';
                    $wpjobportal_totalcredituse = $wpjobportal_value->credits;
                }

                $wpjobportal_html .='<div class="'.esc_attr($this->class_prefix).'-modal-credit-row color">';
                    $wpjobportal_html .='<span class="tit" >'. esc_html(__('Credits remaining after proceed', 'wp-job-portal')) .'</span>';
                    $wpjobportal_html .='<span class="val" id="remaing-credits">'. ($wpjobportal_result['totalcredits'] - $wpjobportal_totalcredituse) .'</span>';
                $wpjobportal_html .='</div>';
                $wpjobportal_html .='</div>';

                if($wpjobportal_actionname == 'job_apply') {
                    $wpjobportal_html .= '<div class="wpjobportal-job-apply-meesage">';
                    $wpjobportal_html .= esc_html(__('Credits will only be deducted if you select a resume and click Apply Now on next popup.', 'wp-job-portal'));
                    $wpjobportal_html .= '</div>';
                }

                $wpjobportal_html .='<div class="col-md-11 col-md-offset-1 '.esc_attr($this->class_prefix).'-modal-data-wrp">
                        <div class="modal-body '.esc_attr($this->class_prefix).'-modal-body">
                              <div class="'.esc_attr($this->class_prefix).'-modal-credit-action-btn-wrp">
                                  <a title="cancel" href="#" class="'.esc_attr($this->class_prefix).'-modal-credit-action-btn" onclick="wpjobportalClosePopup(\''.$wpjobportal_themecall.'\');">
                                      ' . esc_html(__('Cancel', 'wp-job-portal')) . '
                                  </a>';
                $objectid = WPJOBPORTALRequest::getVar('id');
                $srcid = WPJOBPORTALRequest::getVar('srcid');
                $anchorid = WPJOBPORTALRequest::getVar('anchorid');
                $wpjobportal_formid = WPJOBPORTALRequest::getVar('formid');
                if ($wpjobportal_formid) { // popup in case of form is opened
                    if ($wpjobportal_formid == 'resumeform') {
                        $wpjobportal_html .= '<a href="#" class="'.esc_attr($this->class_prefix).'-modal-credit-action-btn color" onclick="'.$wpjobportalPopupResumeFormProceeds.'(\'' . $wpjobportal_action . '\');">' . $wpjobportal_proceedlang . '</a>';
                    }else {
                        $wpjobportal_html .= '<a href="#" class="'.esc_attr($this->class_prefix).'-modal-credit-action-btn color" onclick="'.$wpjobportalPopupFormProceeds.'(\'' . $wpjobportal_formid . '\',\'' . $wpjobportal_action . '\');">' . $wpjobportal_proceedlang . '</a>';
                    }
                } elseif ($srcid && $anchorid) { // popup in case of add to gold and feature
                    $wpjobportal_html .= '<a href="#" class="'.esc_attr($this->class_prefix).'-modal-credit-action-btn color" onclick="'.$wpjobportalPopupProceeds.'(\'' . $wpjobportal_actionname . '\',' . $objectid . ',\'' . $srcid . '\',\'' . $anchorid . '\',\'' . $wpjobportal_action . '\',\''.$wpjobportal_themecall.'\');">' . $wpjobportal_proceedlang . '</a>';
                } elseif($wpjobportal_actionname == 'job_apply') {
                        $wpjobportal_html .= '<a href="#" class="'.esc_attr($this->class_prefix).'-modal-credit-action-btn color" onclick="getApplyNowByJobid('. $objectid . ',' . $wpjobportal_pageid .',1);">' . $wpjobportal_proceedlang . '</a>';
                }else { // popup in case of view company, resume, job contact detail
                    $wpjobportal_html .= '<a href="' . esc_url($wpjobportal_result['link']) .'" class="'.esc_attr($this->class_prefix).'-modal-credit-action-btn color" onclick="return validateRemaingCredits();" >' . esc_html(__('Proceed', 'wp-job-portal')) . '</a>';
                }
                $wpjobportal_html .='</div>
                        </div>
                    </div>
                </div>';
                $wpjobportal_html .= '</div>';
            }else{
                $wpjobportal_html .= '<div id="wpjobportal-popup-background"></div>';
                $wpjobportal_html .= '<div id="wpjobportal-popup">';
                $wpjobportal_html .= '<img class="jsicon" src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/popup-coin-icon.png"/>';
                $wpjobportal_html .= '<span class="popup-title">' . $wpjobportal_result['popuptitle'] . '<img id="popup_cross" alt="popup cross"  src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/popup-close.png"></span>';
                $wpjobportal_html .= '<div class="popup-row name">';
                $wpjobportal_html .= '<span class="title">' . $wpjobportal_result['title-text'] . ' </span><span class="value">' . $wpjobportal_result['title'] . '</span></div>';
                $wpjobportal_html .= '<div class="popup-row name"><span class="title">' . esc_html(__('Total Credits', 'wp-job-portal')) . '</span>';
                $wpjobportal_html .= '<span class="value">' . $wpjobportal_result['totalcredits'] . '</span>';
                $wpjobportal_html .= '</div>';
                $wpjobportal_totalcredituse = 0;
                $wpjobportal_action = 0;
                foreach ($wpjobportal_result['value'] AS $wpjobportal_value) {
                    $wpjobportal_html .= '<div class="popup-row name">';
                    $wpjobportal_html .= '<span class="title">';
                    if (sizeof($wpjobportal_result['value']) > 1) {
                        $wpjobportal_html .= '<input name="credits" type="radio" class="checkboxes" data-credits="' . $wpjobportal_value->credits . '" data-totalcredits="' . $wpjobportal_result['totalcredits'] . '" value=' . $wpjobportal_value->id . ' />';
                        $wpjobportal_action = -1;
                    } else {
                        $wpjobportal_action = $wpjobportal_value->id;
                    }
                    $wpjobportal_html .= esc_html(__('Credit for action', 'wp-job-portal'));
                    $wpjobportal_expirydatearray = array('featured_job','gold_job','add_job','featured_company','gold_company','featured_resume','gold_resume','job_alert_time');
                    if(in_array($wpjobportal_value->creditaction, $wpjobportal_expirydatearray)){
                        $wpjobportal_html .= '<span class="expiry"> (' . esc_html(__('Expire in', 'wp-job-portal')) . ' ' . $wpjobportal_value->expiry . ' ' . esc_html(__('Days', 'wp-job-portal')) . ')</span>';
                    }elseif($wpjobportal_value->creditaction == 'job_alert_lifetime'){
                        $wpjobportal_html .= '<span class="expiry"> ('.esc_html(__('Life time alerts','wp-job-portal')).') </span>';
                    }
                    $wpjobportal_html .= '</span>';
                    $wpjobportal_html .= '<span class="value">' . $wpjobportal_value->credits . '</span>';
                    $wpjobportal_html .= '</div>';
                    $wpjobportal_totalcredituse = $wpjobportal_value->credits;
                }
                $wpjobportal_html .= '<div class="popup-row name">';
                $wpjobportal_html .= '<span class="title">' . esc_html(__('Credits remaining after proceed', 'wp-job-portal')) . '</span>';
                $wpjobportal_html .= '<span class="value" id="remaing-credits">' . ($wpjobportal_result['totalcredits'] - $wpjobportal_totalcredituse) . '</span>';
                $wpjobportal_html .= '</div>';
                if($wpjobportal_actionname == 'job_apply') {
                    $wpjobportal_html .= '<div class="wpjobportal-job-apply-meesage">';
                    $wpjobportal_html .= esc_html(__('Credits will only be deducted if you select a resume and click Apply Now on next popup.', 'wp-job-portal'));
                    $wpjobportal_html .= '</div>';
                }

                $wpjobportal_html .= '<div class="popup-row button">';
                $wpjobportal_html .= '<a href="#" class="wpjobportal-popup cancel" onclick="wpjobportalClosePopup();">' . esc_html(__('Cancel', 'wp-job-portal')) . '</a>';
                $objectid = WPJOBPORTALRequest::getVar('id');
                $srcid = WPJOBPORTALRequest::getVar('srcid');
                $anchorid = WPJOBPORTALRequest::getVar('anchorid');
                $wpjobportal_formid = WPJOBPORTALRequest::getVar('formid');
                if ($wpjobportal_formid) { // popup in case of form is opened
                    if ($wpjobportal_formid == 'resumeform') {
                        $wpjobportal_html .= '<a href="#" class="wpjobportal-popup proceed" onclick="'.$wpjobportalPopupResumeFormProceeds.'(\'' . $wpjobportal_action . '\');">' . $wpjobportal_proceedlang . '</a>';
                        if($wpjobportal_isadmin){
                            $wpjobportal_html .= '<a href="#" class="wpjobportal-popup proceed" onclick="'.$wpjobportalPopupResumeFormProceeds.'(\'' . $wpjobportal_action . '\',1);">' . esc_html(__('Proceed With Paying', 'wp-job-portal')) . '</a>';
                        }
                    } else {
                        $wpjobportal_html .= '<a href="#" class="wpjobportal-popup proceed" onclick="'.$wpjobportalPopupFormProceeds.'(\'' . $wpjobportal_formid . '\',\'' . $wpjobportal_action . '\');">' . $wpjobportal_proceedlang . '</a>';
                        if($wpjobportal_isadmin){
                            $wpjobportal_html .= '<a href="#" class="wpjobportal-popup proceed" onclick="'.$wpjobportalPopupFormProceeds.'(\'' . $wpjobportal_formid . '\',\'' . $wpjobportal_action . '\',1);">' . esc_html(__('Proceed With Paying', 'wp-job-portal')) . '</a>';
                        }
                    }
                     $wpjobportal_html .= '<a href="#" class="wpjobportal-popup proceed" onclick="'.$wpjobportalPopupProceeds.'(\'' . $wpjobportal_actionname . '\',' . $objectid . ',\'' . $srcid . '\',\'' . $anchorid . '\',\'' . $wpjobportal_action . '\');">' . $wpjobportal_proceedlang . '</a>';
                    if($wpjobportal_isadmin){
                        $wpjobportal_html .= '<a href="#" class="wpjobportal-popup proceed" onclick="'.$wpjobportalPopupProceeds.'(\'' . $wpjobportal_actionname . '\',' . $objectid . ',\'' . $srcid . '\',\'' . $anchorid . '\',\'' . $wpjobportal_action . '\',1);">' . esc_html(__('Proceed With Paying', 'wp-job-portal')) . '</a>';
                    }
                } elseif($wpjobportal_actionname == 'job_apply') {
                        $wpjobportal_html .= '<a href="#" class="wpjobportal-popup proceed" onclick="getApplyNowByJobid('. $objectid . ',' . $wpjobportal_pageid .');">' . $wpjobportal_proceedlang . '</a>';
                } else { // popup in case of view company, resume, job contact detail
                    $wpjobportal_html .= '<a href="' . esc_url($wpjobportal_result['link']) . '" class="proceed" onclick="return validateRemaingCredits();" >' . esc_html(__('Proceed', 'wp-job-portal')) . '</a>';
                }
                $wpjobportal_html .= '</div>';
                $wpjobportal_html .= '</div>';
            }
        } else {
            $wpjobportal_html = $this->getErrorPopupFor($wpjobportal_actionname,$wpjobportal_pageid=null,$wpjobportal_themecall=null);
        }
        return $wpjobportal_html;
    }
    function getErrorPopupFor($wpjobportal_actionname,$wpjobportal_pageid=null,$wpjobportal_themecall=null,$wpjobportal_result= false) {
        if(null != $wpjobportal_themecall){
            $wpjobportal_html = $this->getErrorPopupForJobManager($wpjobportal_actionname,$wpjobportal_result);
        }else{
            if($wpjobportal_pageid != null){
                $wpjobportal_pageid = $wpjobportal_pageid;
            }else{
                $wpjobportal_pageid = wpjobportal::wpjobportal_getPageid();
            }
            $wpjobportal_html = '<div id="wpjobportal-popup-background"></div>';
            $wpjobportal_html .= '<div id="wpjobportal-popup">';
            if($wpjobportal_result == false){
                $wpjobportal_html .= '<span class="popup-title">' . esc_html(__('Insufficient Credits', 'wp-job-portal')) . '</span>';
                $wpjobportal_actionfor = WPJOBPORTALincluder::getJSModel('credits')->getCreditsForByAction($wpjobportal_actionname);
                if ($wpjobportal_actionfor == 2) {
                    $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'credits', 'wpjobportallt'=>'jobseekercredits', 'wpjobportalpageid'=>$wpjobportal_pageid));
                } else {
                    $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'credits', 'wpjobportallt'=>'employercredits', 'wpjobportalpageid'=>$wpjobportal_pageid));
                }
                $wpjobportal_linktext = esc_html(__('Buy credits', 'wp-job-portal'));
                $wpjobportal_html .= WPJOBPORTALLayout::setMessageFor(4, $wpjobportal_link, $wpjobportal_linktext, 1);
            }else{
                $wpjobportal_html .= '<span class="popup-title">' . esc_html(__('Can Not Proceed', 'wp-job-portal')) . '</span>';

                $wpjobportal_html .= WPJOBPORTALLayout::setMessageFor(11,'','',1);
            }
            $wpjobportal_html .= '</div>';
            $wpjobportal_html .= '</div>';

        }
        return $wpjobportal_html;
    }

    private function getActionDetailForpopup($wpjobportal_actionname,$wpjobportal_pageid = '') {
        $return = false;
        $return['totalcredits'] = 12;
        if($wpjobportal_actionname == 'copy_job'){
            $wpjobportal_actionfor = 'add_job';
        }else{
            $wpjobportal_actionfor = $wpjobportal_actionname;
        }
        $wpjobportal_creditsrequired = 4;

        switch ($wpjobportal_actionname) {
            case 'featured_company':
                $return['popuptitle'] = esc_html(__('Add to','wp-job-portal')) .' '. esc_html(__('featured','wp-job-portal')) .' '. esc_html(__('company', 'wp-job-portal'));
                $wpjobportal_id = WPJOBPORTALRequest::getVar('id');
                $wpjobportal_companyname = WPJOBPORTALincluder::getJSModel('company')->getCompanynameById($wpjobportal_id);
                $return['title-text'] = esc_html(__('Company name', 'wp-job-portal'));
                $return['title'] = $wpjobportal_companyname;
                $return['value'] = $wpjobportal_creditsrequired;
                break;
            case 'featured_job':
                $return['popuptitle'] = esc_html(__('Add to','wp-job-portal')) .' '. esc_html(__('featured','wp-job-portal')) .' '. esc_html(__('job', 'wp-job-portal'));
                $wpjobportal_id = WPJOBPORTALRequest::getVar('id');
                $wpjobportal_jobtile = WPJOBPORTALincluder::getJSModel('job')->getJobTitleById($wpjobportal_id);
                $return['title-text'] = esc_html(__('Job title', 'wp-job-portal'));
                $return['title'] = $wpjobportal_jobtile;
                $return['value'] = $wpjobportal_creditsrequired;
                break;
            case 'featured_resume':
                $return['popuptitle'] = esc_html(__('Add to','wp-job-portal')) .' '. esc_html(__('featured','wp-job-portal')) .' '. esc_html(__('resume', 'wp-job-portal'));
                $wpjobportal_id = WPJOBPORTALRequest::getVar('id');
                $wpjobportal_resumetile = WPJOBPORTALincluder::getJSModel('resume')->getResumeTitleById($wpjobportal_id);
                $return['title-text'] = esc_html(__('Resume title', 'wp-job-portal'));
                $return['title'] = $wpjobportal_resumetile;
                $return['value'] = $wpjobportal_creditsrequired;
                break;
            case 'add_department':
                $return['popuptitle'] = esc_html(__('Add','wp-job-portal')) .' '. esc_html(__('Department', 'wp-job-portal'));
                $return['title-text'] = esc_html(__('Add','wp-job-portal')) .' '. esc_html(__('Department', 'wp-job-portal'));
                $return['title'] = ' ';
                $return['value'] = $wpjobportal_creditsrequired;
                break;
            case 'add_job':
                $return['popuptitle'] = esc_html(__('Add','wp-job-portal')) .' '. esc_html(__('Job', 'wp-job-portal'));
                $return['title-text'] = esc_html(__('Add','wp-job-portal')) .' '. esc_html(__('job', 'wp-job-portal'));
                $return['title'] = ' ';
                $return['value'] = $wpjobportal_creditsrequired;
                break;
            case 'copy_job':
                $return['popuptitle'] = esc_html(__('Copy','wp-job-portal')) .' '. esc_html(__('Job', 'wp-job-portal'));
                $return['title-text'] = esc_html(__('Copy','wp-job-portal')) .' '. esc_html(__('Job', 'wp-job-portal'));
                $return['title'] = ' ';
                $return['value'] = $wpjobportal_creditsrequired;
                break;
            case 'add_company':
                $return['popuptitle'] = esc_html(__('Add','wp-job-portal')) .' '. esc_html(__('Company', 'wp-job-portal'));
                $return['title-text'] = esc_html(__('Add','wp-job-portal')) .' '. esc_html(__('Company', 'wp-job-portal'));
                $return['title'] = ' ';
                $return['value'] = $wpjobportal_creditsrequired;
                break;
            case 'add_resume':
                $return['popuptitle'] = esc_html(__('Add','wp-job-portal')) .' '. esc_html(__('Resume', 'wp-job-portal'));
                $return['title-text'] = esc_html(__('Add','wp-job-portal')) .' '. esc_html(__('Resume', 'wp-job-portal'));
                $return['title'] = ' ';
                $return['value'] = $wpjobportal_creditsrequired;
                break;
            case 'add_job_alert':
                $return['popuptitle'] = esc_html(__('Add','wp-job-portal')) .' '. esc_html(__('Alert', 'wp-job-portal'));
                $return['title-text'] = esc_html(__('Add','wp-job-portal')) .' '. esc_html(__('Alert', 'wp-job-portal'));
                $return['title'] = ' ';
                $return['value'] = $wpjobportal_creditsrequired;
                break;
            case 'view_company_contact_detail':
                $return['popuptitle'] = esc_html(__('View company contact detail', 'wp-job-portal'));
                $wpjobportal_id = WPJOBPORTALRequest::getVar('id');
                $wpjobportal_companyname = WPJOBPORTALincluder::getJSModel('company')->getCompanynameById($wpjobportal_id);
                $return['title-text'] = esc_html(__('View company contact detail', 'wp-job-portal'));
                $return['title'] = $wpjobportal_companyname;
                $return['value'] = $wpjobportal_creditsrequired;
                $return['link'] = wp_nonce_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'action'=>'wpjobportaltask', 'task'=>'addviewcontactdetail', 'companyid'=>$wpjobportal_id, 'wpjobportalpageid'=>$wpjobportal_pageid)),'wpjobportal_company_nonce');
                break;
            case 'view_resume_contact_detail':
                $return['popuptitle'] = esc_html(__('View resume contact detail', 'wp-job-portal'));
                $wpjobportal_id = WPJOBPORTALRequest::getVar('id');
                $wpjobportal_resumename = WPJOBPORTALincluder::getJSModel('resume')->getResumenameById($wpjobportal_id);
                $return['title-text'] = esc_html(__('View resume contact detail', 'wp-job-portal'));
                $return['title'] = $wpjobportal_resumename;
                $return['value'] = $wpjobportal_creditsrequired;
                $return['link'] = wp_nonce_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'action'=>'wpjobportaltask', 'task'=>'addviewresumedetail', 'resumeid'=>$wpjobportal_id, 'wpjobportalpageid'=>$wpjobportal_pageid)),'wpjobportal_resume_nonce');
                break;
            case 'resume_save_search':
                $return['popuptitle'] = esc_html(__('Save','wp-job-portal')) .' '. esc_html(__('search', 'wp-job-portal'));
                $return['title-text'] = esc_html(__('Save','wp-job-portal')) .' '. esc_html(__('search', 'wp-job-portal'));
                $return['title'] = ' ';
                $return['value'] = $wpjobportal_creditsrequired;
                break;
            case 'job_apply':
                $return['popuptitle'] = esc_html(__('Apply On Job', 'wp-job-portal'));
                $wpjobportal_id = WPJOBPORTALRequest::getVar('id');
                $wpjobportal_jobtile = WPJOBPORTALincluder::getJSModel('job')->getJobTitleById($wpjobportal_id);
                $return['title-text'] = esc_html(__('Job title', 'wp-job-portal'));
                $return['title'] = $wpjobportal_jobtile;
                $return['value'] = $wpjobportal_creditsrequired;
                break;
        }
        return $return;
    }
}
