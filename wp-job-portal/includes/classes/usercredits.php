<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALUserCredits {

    function doAction($wpjobportal_actionname) {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('js_nonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wp-job-portal-nonce') ) {
            die( 'Security check Failed' );
        }
        $wpjobportal_result = false;
        $wpjobportal_id = WPJOBPORTALRequest::getVar('id');
        $wpjobportal_payment = WPJOBPORTALRequest::getVar('payment');
        $wpjobportal_actionid = WPJOBPORTALRequest::getVar('actiona');
        switch ($wpjobportal_actionname) {
            case 'featured_company':
            $wpjobportal_result = apply_filters('wpjobportal_addons_company_credit_featurecompany',$wpjobportal_id,$wpjobportal_actionid,false,$wpjobportal_payment);
                break;
            case 'featured_job':
                $wpjobportal_result = apply_filters('wpjobportal_addons_admin_feature_credit_popupaction',$wpjobportal_id,$wpjobportal_actionid,false,$wpjobportal_payment);
                break;
            case 'featured_resume':
                $wpjobportal_result = apply_filters('wpjobportal_resume_feauture_action_bottom',$wpjobportal_id,$wpjobportal_actionid,false,$wpjobportal_payment);
                break;
            case 'copy_job': // here we use the add job just for the copy job functionality
                $wpjobportal_result = apply_filters('wpjobportal_addon_action_credit_copyjob',$wpjobportal_id,$wpjobportal_actionid,false);
                break;
        }
        return $wpjobportal_result;
    }

    function getUserCreditsDetailForAction($wpjobportal_actionname) {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('js_nonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wp-job-portal-nonce') ) {
            die( 'Security check Failed' );
        }
        $wpjobportal_isadmin = WPJOBPORTALrequest::getVar('isadmin');
        $wpjobportal_id = WPJOBPORTALrequest::getVar('id');
        $wpjobportal_themecall = WPJOBPORTALrequest::getVar('themecall');
        $wpjobportal_pageid = WPJOBPORTALrequest::getVar('wpjobportal_pageid');
        if($wpjobportal_isadmin != 1){
            $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
            if ($wpjobportal_uid == 0)
                return false;
            $wpjobportal_result = false; // by default action is not default if case is not found
            switch ($wpjobportal_actionname) {
                case 'featured_company':
                    $wpjobportal_result = $this->getValidate($wpjobportal_uid, 'featuredcompany', 'canAddFeaturedCompany');
                    break;
                case 'featured_job':
                    $wpjobportal_result = $this->getValidate($wpjobportal_uid, 'featuredjob', 'canAddFeaturedJob');
                    break;
                case 'featured_resume':
                    $wpjobportal_result = $this->getValidate($wpjobportal_uid, 'featureresume', 'canAddFeaturedResume');
                    break;
                case 'view_company_contact_detail':
                    $wpjobportal_result = true; // always set to true b/c if company contact detail is allowed then this button show contact detail not be shown
                    break;
                case 'view_resume_contact_detail':
                    $wpjobportal_result = true; // always set to true b/c if company contact detail is allowed then this button show contact detail not be shown
                    break;
                case 'resume_save_search':
                    if(in_array('resumesearch', wpjobportal::$_active_addons)){
                        $wpjobportal_result = apply_filters('wpjobportal_addons_admin_resume_save_search',false);  // always set to true b/c if company contact detail is allowed then this button show contact detail not be shown
                    }
                    break;
                case 'add_department':
                    $wpjobportal_result = $this->getValidate($wpjobportal_uid, 'departments', 'canAddDepartment');
                    break;
                case 'add_job':
                case 'copy_job':
                    $wpjobportal_result = $this->getValidate($wpjobportal_uid, 'job', 'canAddJob',$wpjobportal_id);
                    break;
                case 'add_company':
                    $wpjobportal_result = $this->getValidate($wpjobportal_uid, 'company', 'canAddCompany');
                    break;
                case 'add_resume':
                    $wpjobportal_result = $this->getValidate($wpjobportal_uid, 'resume', 'canAddResume');
                    break;
                case 'add_job_alert':
                   $wpjobportal_result = $this->getValidate($wpjobportal_uid, 'jobalert', 'canAddJobAlert');
                    break;
                case 'job_apply':
                    $wpjobportal_result = $this->getValidate($wpjobportal_uid, 'jobapply', 'canApplyOnJob',$wpjobportal_id);
                    break;
            }
        }else{
            $wpjobportal_result = true;
        }
        if ($wpjobportal_result === true) {
            if($wpjobportal_isadmin == 1){
                $wpjobportal_html = WPJOBPORTALincluder::getObjectClass('popup')->getPopupForAdmin($wpjobportal_actionname,$wpjobportal_themecall,$wpjobportal_pageid);
            }else{
                $wpjobportal_html = WPJOBPORTALincluder::getObjectClass('popup')->getPopupFor($wpjobportal_actionname,$wpjobportal_themecall,$wpjobportal_pageid);
            }
        } else {
            $wpjobportal_html = WPJOBPORTALincluder::getObjectClass('popup')->getErrorPopupFor($wpjobportal_actionname,$wpjobportal_pageid,$wpjobportal_themecall,$wpjobportal_result);// fourth parameter ($wpjobportal_result) is to manager already applied on a job case.
        }
        return $wpjobportal_html;
    }

    private function getValidate($wpjobportal_uid, $wpjobportal_model, $function,$wpjobportal_id=0) {
        if (!is_numeric($wpjobportal_uid))
            return false;
        if($wpjobportal_id == 0){
            $wpjobportal_result = WPJOBPORTALincluder::getJSModel($wpjobportal_model)->$function($wpjobportal_uid);
        }else{// to handle job appply case
            $wpjobportal_result = WPJOBPORTALincluder::getJSModel($wpjobportal_model)->$function($wpjobportal_id,$wpjobportal_uid);
        }
        return $wpjobportal_result;
    }
}

?>
