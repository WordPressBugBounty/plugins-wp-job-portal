<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALquickapplyModel {
   function quickapply($wpjobportal_jobid, $wpjobportal_actionid) {
        if (is_numeric($wpjobportal_jobid)) {
            $query = "SELECT * FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` WHERE id = " . esc_sql($wpjobportal_jobid);
            $wpjobportal_job = wpjobportaldb::get_row($query);
            $wpjobportal_data = (array) $wpjobportal_job;
            $wpjobportal_data['id'] = '';
            $wpjobportal_data['title'] = $wpjobportal_data['title'] . ' ' . __('Copy', 'wp-job-portal');
            $wpjobportal_data['jobid'] = WPJOBPORTALincluder::getJSModel('job')->getJobId();
            $wpjobportal_data['isjob'] = 2;
            $wpjobportal_isadmin = WPJOBPORTALrequest::getVar('isadmin');
            $wpjobportal_user = WPJOBPORTALincluder::getObjectClass('user');
            $wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
            $wpjobportal_subType = wpjobportal::$_config->getConfigValue('submission_type');
            $wpjobportal_expiry = false; // to handle log error
            if(in_array('credits', wpjobportal::$_active_addons)){
                if($wpjobportal_subType == 1){
                     $wpjobportal_expiry = wpjobportal::$_config->getConfigValue('jobexpiry_days_free');
                    if(isset($wpjobportal_data['stoppublishing']) && empty($wpjobportal_data['stoppublishing'])){
                        $wpjobportal_data['stoppublishing'] = gmdate($wpjobportal_dateformat,strtotime($wpjobportal_data['startpublishing'].'+'.$wpjobportal_expiry.' days') );
                    }
                    if (!wpjobportal::$_common->wpjp_isadmin()) {
                        $wpjobportal_data['status'] = wpjobportal::$_config->getConfigurationByConfigName('jobautoapprove');
                    }
                }elseif ($wpjobportal_subType == 2) {
                    #Per listing --Free job Expiry date
                    $wpjobportal_expiry = wpjobportal::$_config->getConfigValue('jobexpiry_days_perlisting');
                    if(isset($wpjobportal_data['stoppublishing']) && empty($wpjobportal_data['stoppublishing'])){
                        $wpjobportal_data['stoppublishing'] = gmdate($wpjobportal_dateformat,strtotime($wpjobportal_data['startpublishing'].'+'.$wpjobportal_expiry.' days') );
                    }else{
                        $wpjobportal_data['stoppublishing'] = gmdate($wpjobportal_dateformat,strtotime($wpjobportal_expiry.' days') );
                    }
                    $wpjobportal_data['status'] = 3;
                }elseif ($wpjobportal_subType == 3) {
                    if(!wpjobportal::$_common->wpjp_isadmin()){
                        $wpjobportal_upakid = WPJOBPORTALrequest::getVar('wpjobportal_packageid',null,0);
                        $wpjobportal_package = apply_filters('wpjobportal_addons_userpackages_permodule',false,$wpjobportal_upakid,$wpjobportal_user->uid(),'remjob');
                        if( !$wpjobportal_package ){
                            return WPJOBPORTAL_SAVE_ERROR;
                        }
                        if( $wpjobportal_package->expired ){
                            return WPJOBPORTAL_SAVE_ERROR;
                        }
                        //if Department are not unlimited & there is no remaining left
                        if( $wpjobportal_package->job!=-1 && !$wpjobportal_package->remjob ){ //-1 = unlimited
                            return WPJOBPORTAL_SAVE_ERROR;
                        }
                    }elseif (wpjobportal::$_common->wpjp_isadmin()) { // checking if admin is trying to perform action
                        $wpjobportal_payment = WPJOBPORTALrequest::getVar('payment',null,0);
                        if ($wpjobportal_payment == 0) { // proceed without payment option
                            $wpjobportal_upakid = WPJOBPORTALrequest::getVar('upakid',null,0);
                            $wpjobportal_data['userpackageid'] = $wpjobportal_upakid;
                        } else { // if admin clicked on proceed with payment option
                            $wpjobportal_upakid = WPJOBPORTALrequest::getVar('upakid',null,0);
                            $wpjobportal_uid = WPJOBPORTALrequest::getVar('uid');
                            $wpjobportal_package = apply_filters('wpjobportal_addons_userpackages_permodule',false,$wpjobportal_upakid,$wpjobportal_data['uid'],'remjob');
                            if( !$wpjobportal_package ){
                                return WPJOBPORTAL_SAVE_ERROR;
                            }
                            if( $wpjobportal_package->expired ){
                                return WPJOBPORTAL_SAVE_ERROR;
                            }
                            //if Department are not unlimited & there is no remaining left
                            if( $wpjobportal_package->job!=-1 && !$wpjobportal_package->remjob ){ //-1 = unlimited
                                return WPJOBPORTAL_SAVE_ERROR;
                            }
                        }
                    }
                    // if($wpjobportal_package == ''){ // to handle log errors since we are trying to access elements from package object.
                    //     return WPJOBPORTAL_SAVE_ERROR;
                    // }
                    #user packae id--
                    $wpjobportal_data['status'] = wpjobportal::$_config->getConfigValue('jobautoapprove');
                    $wpjobportal_data['userpackageid'] = $wpjobportal_upakid;

                    if(isset($wpjobportal_package) && !empty($wpjobportal_package)){
                        $wpjobportal_expiry = $wpjobportal_package->jobtime.''.$wpjobportal_package->jobtimeunit;
                    }else{
                        $wpjobportal_expiry = "30 days"; // in case of undefined add job for 30 days
                    }

                    //if(isset($wpjobportal_data['stoppublishing']) && empty($wpjobportal_data['stoppublishing'])){
                    $wpjobportal_data['stoppublishing'] = gmdate($wpjobportal_dateformat,strtotime($wpjobportal_data['startpublishing'].'+'.$wpjobportal_expiry) );
                    // }else{
                    //     $wpjobportal_data['stoppublishing'] = gmdate($wpjobportal_dateformat,strtotime($wpjobportal_package->jobtime.''.$wpjobportal_package->jobtimeunit));
                    // }
                    if(isset($wpjobportal_data['price']) && !empty($wpjobportal_data['status'])){
                        $wpjobportal_data['price'] = '';
                    }
                }
            }
            if($wpjobportal_expiry == false){
                $tdate1 = strtotime($wpjobportal_data['startpublishing']);
                $tdate2 = strtotime($wpjobportal_data['stoppublishing']);
                $wpjobportal_seconds_diff = $tdate2 - $tdate1;
                if($wpjobportal_seconds_diff > 86400){
                    $wpjobportal_expiry = $wpjobportal_seconds_diff / 86400;
                }else{
                    $wpjobportal_expiry = 1;
                }
                $wpjobportal_data['startpublishing'] = date_i18n("Y-m-d H:i:s");
                $wpjobportal_data['stoppublishing'] = gmdate($wpjobportal_dateformat,strtotime($wpjobportal_data['startpublishing'].'+'.round($wpjobportal_expiry).' days') );
            }
            if(isset($wpjobportal_data['stoppublishing'])){
                $wpjobportal_data['stoppublishing'] = gmdate('Y-m-d H:i:s', strtotime($wpjobportal_data['stoppublishing']));
            }
            $wpjobportal_data['created'] = date_i18n("Y-m-d H:i:s");
            $wpjobportal_data['startpublishing'] = date_i18n("Y-m-d H:i:s");
            if(isset($wpjobportal_data['isfeaturedjob'])){
                $wpjobportal_data['isfeaturedjob'] = 2;
            }

            if(isset($wpjobportal_data['startfeatureddate'])){
                $wpjobportal_data['startfeatureddate'] = '';
            }

            if(isset($wpjobportal_data['endfeatureddate'])){
                $wpjobportal_data['endfeatureddate'] = '';
            }
            $wpjobportal_row = WPJOBPORTALincluder::getJSTable('job');
            if (!$wpjobportal_row->bind($wpjobportal_data)) {
                $res = "error";
            }
            if (!$wpjobportal_row->store()) {
                return false;
            }
            if ($wpjobportal_row->city){
                $wpjobportal_storemulticity = WPJOBPORTALincluder::getJSModel('job')->storeMultiCitiesJob($wpjobportal_row->city, $wpjobportal_row->id);
            }
            if (isset($wpjobportal_storemulticity) && $wpjobportal_storemulticity == false){
                return false;
            }
            if(in_array('credits', wpjobportal::$_active_addons) && $wpjobportal_subType == 3){
                if(!wpjobportal::$_common->wpjp_isadmin()){
                    apply_filters('wpjobportal_addons_user_transactionlog',$wpjobportal_row,'job',$wpjobportal_upakid,$wpjobportal_row->uid);
                }elseif(wpjobportal::$_common->wpjp_isadmin()){
                    apply_filters('wpjobportal_addons_user_transactionlog',$wpjobportal_row,'job',$wpjobportal_upakid,$wpjobportal_data['uid']);
                }
            }
            WPJOBPORTALMessages::setLayoutMessage(__('Job Copied Successfully', 'wp-job-portal'), 'updated',WPJOBPORTALincluder::getJSModel('job')->getMessagekey());
        }
        if(in_array('credits', wpjobportal::$_active_addons)){
            return true;
        }else{
            return WPJOBPORTAL_SAVED;
        }
    }

    function makeJobCopyAjax() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'make-job-copy-ajax') ) {
            die( 'Security check Failed' );
        }
        $wpjobportal_jobid = (int) WPJOBPORTALrequest::getVar('jobid');
        $res = "error";
        if ($wpjobportal_jobid && is_numeric($wpjobportal_jobid)) {
            $res = "copied";
            $query = "SELECT * FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` WHERE id = " . esc_sql($wpjobportal_jobid);
            $wpjobportal_job = wpjobportaldb::get_row($query);
            $wpjobportal_data = (array) $wpjobportal_job;

            $wpjobportal_data['id'] = '';
            $wpjobportal_data['title'] = $wpjobportal_data['title'] . ' ' . __('Copy', 'wp-job-portal');
            $wpjobportal_data['jobid'] = WPJOBPORTALincluder::getJSModel('job')->getJobId();
            $wpjobportal_data['isjob'] = 0;
            $wpjobportal_data['status'] = 0;
            $wpjobportal_data['startpublishing'] = gmdate('Y-m-d H:i:s');
            $wpjobportal_data['created'] = gmdate("Y-m-d H:i:s");
            $wpjobportal_row = WPJOBPORTALincluder::getJSTable('job');
            if (!$wpjobportal_row->bind($wpjobportal_data)) {
                $res = "error";
            }
            if (!$wpjobportal_row->check($wpjobportal_data)) {
                $res = "error";
            }
            if (!$wpjobportal_row->store($wpjobportal_data)) {
                $res = "error";
            }
            if ($wpjobportal_data['city'])
                $wpjobportal_storemulticity = WPJOBPORTALincluder::getJSModel('job')->storeMultiCitiesJob($wpjobportal_data['city'], $wpjobportal_row->id);
            if (isset($wpjobportal_storemulticity) && $wpjobportal_storemulticity == false)
                $res = "savecitieserror";

        }
        return $res;
    }

    function captchaValidate() {
        if (!is_user_logged_in()) {
            $wpjobportal_config_array = wpjobportal::$_config->getConfigByFor('captcha');
            //$wpjobportal_captcha_check = $wpjobportal_config_array['job_captcha'];
            $wpjobportal_captcha_check  = wpjobportal::$_config->getConfigurationByConfigName('quick_apply_captcha');
            if ($wpjobportal_captcha_check == 1) {
                if ($wpjobportal_config_array['captcha_selection'] == 1) { // Google recaptcha
                    $wpjobportal_google_recaptcha = WPJOBPORTALrequest::getVar('g-recaptcha-response','post','');
                    if($wpjobportal_google_recaptcha != ''){
                        $gresponse = wpjobportal::wpjobportal_sanitizeData($wpjobportal_google_recaptcha);
                    }
                    $resp = wpjobportal_googleRecaptchaHTTPPost($wpjobportal_config_array['recaptcha_privatekey'] , $gresponse);

                    if ($resp) {
                        return true;
                    } else {
                        wpjobportal::$_data['google_captchaerror'] = esc_html(__("Invalid captcha",'wp-job-portal'));
                        return false;
                    }
                } else { // own captcha
                    $wpjobportal_captcha = new WPJOBPORTALcaptcha;
                    $wpjobportal_result = $wpjobportal_captcha->checkCaptchaUserForm();
                    if ($wpjobportal_result == 1) {
                        return true;
                    } else {
                        return false;
                    }
                }
            }
        }
        return true;
    }



    function quickApplyOnJob(){
        $wpjobportal_data = WPJOBPORTALrequest::get('post'); // form data
        if(empty($wpjobportal_data)){
            return false;
        }

        if(!$this->captchaValidate()){
            WPJOBPORTALMessages::setLayoutMessage(__('Incorrect Captcha code', 'wp-job-portal'), 'error',WPJOBPORTALincluder::getJSModel('job')->getMessagekey());
            return false;
        }

        // captcha

        // make sure that minimum data is present
        if($wpjobportal_data['full_name'] == '' || $wpjobportal_data['email'] == '' || $wpjobportal_data['jobid'] == ''){
            //return WPJOBPORTAL_SAVE_ERROR;
        }

        $wpjobportal_resume_data = array();

        $wpjobportal_resume_data['first_name'] = !empty($wpjobportal_data['full_name']) ? $wpjobportal_data['full_name'] : '';
        $wpjobportal_resume_data['email_address'] = !empty($wpjobportal_data['email']) ? $wpjobportal_data['email'] : '';
        $wpjobportal_resume_data['cell'] = !empty($wpjobportal_data['phone']) ? $wpjobportal_data['phone'] : '';
        $wpjobportal_resume_data['created'] = gmdate('Y-m-d H:i:s');
        $wpjobportal_resume_data['last_modified'] = gmdate('Y-m-d H:i:s');
        $wpjobportal_resume_data['status'] = 1;
        $wpjobportal_resume_data['uid'] = WPJOBPORTALincluder::getObjectClass('user')->uid();
        //
        $wpjobportal_resume_data['quick_apply'] = 1;

        $alias = wpjobportalphplib::wpJP_str_replace(' ', '-', $wpjobportal_resume_data['first_name']);
        $alias = wpjobportalphplib::wpJP_str_replace('_', '-', $alias);

        $wpjobportal_resume_data['alias'] = $alias;

        $wpjobportal_resume_data = wpjobportal::wpjobportal_sanitizeData($wpjobportal_resume_data);
        $wpjobportal_resume_data = WPJOBPORTALincluder::getJSmodel('common')->stripslashesFull($wpjobportal_resume_data);// remove slashes with quotes.
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('resume');

        if (!$wpjobportal_row->bind($wpjobportal_resume_data)) {
            die('bind failed 393');
            return WPJOBPORTAL_SAVE_ERROR;
        }
        if (!$wpjobportal_row->store()) {
            die('store failed 397');
            return WPJOBPORTAL_SAVE_ERROR;
        }

        // uploading file to resume files

        // the below code is to modify the data to use existing file upload code
        if(isset($_FILES['resumefiles']) && !empty($_FILES['resumefiles'])){
            $outputArray = array(
                'name' => array($_FILES['resumefiles']['name']),
                'type' => array($_FILES['resumefiles']['type']),
                'tmp_name' => array($_FILES['resumefiles']['tmp_name']),
                'error' => array($_FILES['resumefiles']['error']),
                'size' => array($_FILES['resumefiles']['size'])
            );
            $_FILES['resumefiles'] = $outputArray;

            WPJOBPORTALincluder::getJSmodel('resume')->uploadResume($wpjobportal_row->id);
        }

        // setting variables here to accomodate exsisting code without change
        wpjobportal::$_data['sanitized_args']['js_nonce'] = wp_create_nonce('wp-job-portal-nonce');
        wpjobportal::$_data['sanitized_args']['jobid'] = $wpjobportal_data['jobid'] ;
        wpjobportal::$_data['sanitized_args']['cvid'] = $wpjobportal_row->id;// newly created resume id
        wpjobportal::$_data['sanitized_args']['quick_apply'] = 1;
        if(isset($wpjobportal_data['message'])){
            wpjobportal::$_data['sanitized_args']['message'] = $wpjobportal_data['message'];
        }

        // calling the job apply function with "1" for $wpjobportal_themecall to make sure it returns a numric value for the status of job apply
        //$wpjobportal_job_applied = WPJOBPORTALincluder::getJSmodel('jobapply')->jobapply(1);

        return $wpjobportal_row->id; // returning resume id

        return WPJOBPORTAL_SAVED;
    }

    function uploadFile($wpjobportal_id) {
        $wpjobportal_result =  WPJOBPORTALincluder::getObjectClass('uploads')->uploadQuickApplyResume($wpjobportal_id);
        return $wpjobportal_result;
    }


    function getMessagekey(){
        $wpjobportal_key = 'job';if(wpjobportal::$_common->wpjp_isadmin()){$wpjobportal_key = 'admin_'.$wpjobportal_key;}return $wpjobportal_key;
    }
}

?>