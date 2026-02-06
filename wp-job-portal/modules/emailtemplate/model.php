<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALEmailtemplateModel {

    function sendMail($wpjobportal_mailfor, $wpjobportal_action, $wpjobportal_id,$wpjobportal_mailextradata=array()) {
        if (!is_numeric($wpjobportal_mailfor))
            return false;
        if (!is_numeric($wpjobportal_action))
            return false;
        if ($wpjobportal_id != null)
            if (!is_numeric($wpjobportal_id))
                return false;
        $wpjobportal_config_array = WPJOBPORTALincluder::getJSModel('configuration')->getConfigByFor('email');
        $wpjobportal_pageid = WPJOBPORTAL::wpjobportal_getPageid();
        $siteTitle = wpjobportal::$_config->getConfigValue('title');
        switch ($wpjobportal_mailfor) {
            case 1: // Mail For Company
                switch ($wpjobportal_action) {
                    case 1: // Add New Company
                        $record = $this->getRecordByTablenameAndId('wj_portal_companies', $wpjobportal_id,15);
                        if($record == '' || empty($record)){
                            return;
                        }
                        $wpjobportal_link = null;
                        $wpjobportal_checkstatus = null;
                        $Email = $record->companyuseremail;
                        if ($Email == '') {
                            $Email = $record->useremail;
                        }
                        $wpjobportal_status = $record->status;
                        // if(in_array('multicompany', wpjobportal::$_active_addons)){
                        //     $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'multicompany', 'wpjobportallt'=>'mycompanies', 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        // }else{
                        //     $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'mycompanies', 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        // }
                        // view company button on email will take to view company layout instead of my comapnies layout
                        $wpjobportal_link =  wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$wpjobportal_id, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        if ($wpjobportal_status == 0) {
                            $wpjobportal_checkstatus = esc_html(__('Pending', 'wp-job-portal'));
                        }
                        if ($wpjobportal_status == -1) {
                            $wpjobportal_checkstatus = esc_html(__('Rejected', 'wp-job-portal'));
                        }
                        if ($wpjobportal_status == 1) {
                            $wpjobportal_checkstatus = esc_html(__('Approved', 'wp-job-portal'));
                        }

                        if ($wpjobportal_status == 3) {
                            $wpjobportal_checkstatus = esc_html(__('Pending Due to Payment', 'wp-job-portal'));
                        }
                        $Companyname = $record->companyname;

                        // to show admininstrator or guest name and email when creating new comapny
                        if($record->username == ''){
                            if(wpjobportal::$_common->wpjp_isadmin()){
                                $record->username = 'Administrator';
                            }else{
                                $record->username = 'Guest';
                            }
                        }
                        if($record->useremail == ''){
                            $record->useremail = $record->companyuseremail;
                        }

                        $wpjobportal_matcharray = array(
                            '{COMPANY_NAME}' => $Companyname,
                            '{COMPANY_LINK}' => $wpjobportal_link,
                            '{COMPANY_STATUS}' => $wpjobportal_checkstatus,
                            '{EMPLOYER_NAME}' => $record->username,
                            '{SITETITLE}' => $siteTitle,
                            '{EMAIL}' => $record->useremail,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        $wpjobportal_template = $this->getTemplateForEmail('company-new');
                        $getEmailStatus = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getEmailTemplateStatus('add_new_company');
                        $wpjobportal_msgSubject = $wpjobportal_template->subject;
                        $wpjobportal_msgBody = $wpjobportal_template->body;
                        $this->replaceMatches($wpjobportal_msgSubject, $wpjobportal_matcharray);
                        $this->replaceMatches($wpjobportal_msgBody, $wpjobportal_matcharray);
                        $wpjobportal_senderEmail = $wpjobportal_config_array['mailfromaddress'];
                        $wpjobportal_senderName = $wpjobportal_config_array['mailfromname'];
                        // Add New Company mail to User
                        if ($getEmailStatus->employer == 1) {
                            $this->sendEmail($Email, $wpjobportal_msgSubject, $wpjobportal_msgBody, $wpjobportal_senderEmail, $wpjobportal_senderName, '', 2); //2 action for add company hock
                        }
                        $wpjobportal_link = esc_url_raw(admin_url("admin.php?page=wpjobportal_company"));
                        $wpjobportal_matcharray['{COMPANY_LINK}'] = $wpjobportal_link;
                        $wpjobportal_matcharray['{CURRENT_YEAR}'] = gmdate('Y');
                        $wpjobportal_matcharray['{SITETITLE}'] = $siteTitle;
                        $wpjobportal_msgSubject = $wpjobportal_template->subject;
                        $wpjobportal_msgBody = $wpjobportal_template->body;
                        $this->replaceMatches($wpjobportal_msgSubject, $wpjobportal_matcharray);
                        $this->replaceMatches($wpjobportal_msgBody, $wpjobportal_matcharray);
                        // Add New Company mail to admin
                        if ($getEmailStatus->admin == 1) {
                            $adminEmailid = $wpjobportal_config_array['adminemailaddress'];
                            $this->sendEmail($adminEmailid, $wpjobportal_msgSubject, $wpjobportal_msgBody, $wpjobportal_senderEmail, $wpjobportal_senderName, '', 1); //1 action for add company hock
                        }
                        break;
                    case 2: // Delete Company

                        $wpjobportal_matcharray = array(
                            '{COMPANY_NAME}' => $wpjobportal_mailextradata['companyname'],
                            '{EMAIL}' => $wpjobportal_mailextradata['contactemail'],
                            '{CURRENT_YEAR}' => gmdate('Y'),
                            '{SITETITLE}' => $siteTitle
                        );
                        $Email = $wpjobportal_mailextradata['contactemail'];
                        $wpjobportal_template = $this->getTemplateForEmail('company-delete');
                        $getEmailStatus = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getEmailTemplateStatus('delete_company');
                        $wpjobportal_msgSubject = $wpjobportal_template->subject;
                        $wpjobportal_msgBody = $wpjobportal_template->body;
                        $this->replaceMatches($wpjobportal_msgSubject, $wpjobportal_matcharray);
                        $this->replaceMatches($wpjobportal_msgBody, $wpjobportal_matcharray);
                        $wpjobportal_senderEmail = $wpjobportal_config_array['mailfromaddress'];
                        $wpjobportal_senderName = $wpjobportal_config_array['mailfromname'];
                        // Delete Company mail to User
                        if ($getEmailStatus->employer == 1) {
                            $this->sendEmail($Email, $wpjobportal_msgSubject, $wpjobportal_msgBody, $wpjobportal_senderEmail, $wpjobportal_senderName, '', 3); // 3 action for company delete hock
                        }
                        break;
                    case 3: // Company approve OR compnay Reject
                        $record = $this->getRecordByTablenameAndId('wj_portal_companies', $wpjobportal_id,15);
                        $Username = '';
                        if ($Username == '') {
                            $Username = $record->username;
                        }
                        $Email = $record->companyuseremail;
                        if ($Email == '') {
                            $Email = $record->useremail;
                        }
                        $Companyname = $record->companyname;
                        // to show admininstrator or guest name and email when creating new comapny
                        if($Username == ''){
                            if(wpjobportal::$_common->wpjp_isadmin()){
                                $Username = 'Administrator';
                            }else{
                                $Username = 'Guest';
                            }
                        }

                        $wpjobportal_status = $record->status;
                        $wpjobportal_checkstatus = null;
                        $wpjobportal_link = null;
                        if(in_array('multicompany', wpjobportal::$_active_addons)){
                            $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'multicompany', 'wpjobportallt'=>'mycompanies', 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        }else{
                            $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'mycompanies', 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        }
                        if ($wpjobportal_status == -1) {
                            $wpjobportal_checkstatus = esc_html(__('Rejected', 'wp-job-portal'));
                        }
                        if ($wpjobportal_status == 1) {
                            $wpjobportal_checkstatus = esc_html(__('Approved', 'wp-job-portal'));
                            $wpjobportal_link =  wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$wpjobportal_id, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        }
                        $wpjobportal_matcharray = array(
                            '{COMPANY_NAME}' => $Companyname,
                            '{EMPLOYER_NAME}' => $Username,
                            '{COMPANY_LINK}' => $wpjobportal_link,
                            '{COMPANY_STATUS}' => $wpjobportal_checkstatus,
                            '{CURRENT_YEAR}' => gmdate('Y'),
                            '{EMAIL}' => $Email,
                            '{SITETITLE}' => $siteTitle
                        );
                        $wpjobportal_template = $this->getTemplateForEmail('company-status');
                        $getEmailStatus = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getEmailTemplateStatus('company_status');
                        $wpjobportal_msgSubject = $wpjobportal_template->subject;
                        $wpjobportal_msgBody = $wpjobportal_template->body;
                        $this->replaceMatches($wpjobportal_msgSubject, $wpjobportal_matcharray);
                        $this->replaceMatches($wpjobportal_msgBody, $wpjobportal_matcharray);
                        $wpjobportal_senderEmail = $wpjobportal_config_array['mailfromaddress'];
                        $wpjobportal_senderName = $wpjobportal_config_array['mailfromname'];
                        // Company approve or reject mail to User
                        if ($getEmailStatus->employer == 1) {
                            $this->sendEmail($Email, $wpjobportal_msgSubject, $wpjobportal_msgBody, $wpjobportal_senderEmail, $wpjobportal_senderName, '', 4); // 4 action for compnay status hock
                        }
                        break;
                    case 5: // Company approve OR reject for featured
                        $record = $this->getRecordByTablenameAndId('wj_portal_companies', $wpjobportal_id,17);
                        if($record == ''){
                            break;
                        }
                        $Username = '';
                        if ($Username == '') {
                            $Username = $record->username;
                        }

                        // to show admininstrator or guest name and email when creating new comapny
                        if($Username == ''){
                            if(wpjobportal::$_common->wpjp_isadmin()){
                                $Username = 'Administrator';
                            }else{
                                $Username = 'Guest';
                            }
                        }

                        $Email = $record->companyuseremail;
                        if ($Email == '') {
                            $Email = $record->useremail;
                        }
                        $Companyname = $record->companyname;
                        $featuredcompany = $record->featuredcompany;
                        $wpjobportal_link = null;
                        $wpjobportal_checkfeaturedcompany = null;
                        if(in_array('multicompany', wpjobportal::$_active_addons)){
                            $wpjobportal_link =  wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'multicompany', 'wpjobportallt'=>'mycompanies', 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid())) . ">" . esc_html(__('Company Detail', 'wp-job-portal')) . "</a>";
                        }else{
                            $wpjobportal_link =  wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'mycompanies', 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid())) . ">" . esc_html(__('Company Detail', 'wp-job-portal')) . "</a>";
                        }
                        if ($featuredcompany == -1) {
                            $wpjobportal_checkfeaturedcompany = esc_html(__('rejected for featured', 'wp-job-portal'));
                        }
                        if ($featuredcompany == 1) {
                            $wpjobportal_checkfeaturedcompany = esc_html(__('approved for featured', 'wp-job-portal'));
                            $wpjobportal_link =  wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$wpjobportal_id, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        }
                        if ($featuredcompany == 2) {
                            $wpjobportal_checkfeaturedcompany = esc_html(__('removed for featured', 'wp-job-portal'));
                            $wpjobportal_link =  wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$wpjobportal_id, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        }
                        if ($featuredcompany == 0) {
                            $wpjobportal_checkfeaturedcompany = esc_html(__('pending for featured', 'wp-job-portal'));
                            $wpjobportal_link =  wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$wpjobportal_id, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid())) ;
                        }
                        $wpjobportal_matcharray = array(
                            '{COMPANY_NAME}' => $Companyname,
                            '{EMPLOYER_NAME}' => $Username,
                            '{COMPANY_LINK}' => $wpjobportal_link,
                            '{COMPANY_STATUS}' => $wpjobportal_checkfeaturedcompany,
                            '{CURRENT_YEAR}' => gmdate('Y'),
                            '{EMAIL}' => $Email,
                            '{SITETITLE}' => $siteTitle
                        );
                        $wpjobportal_template = $this->getTemplateForEmail('company-status');
                        $getEmailStatus = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getEmailTemplateStatus('company_status');
                        $wpjobportal_msgSubject = $wpjobportal_template->subject;
                        $wpjobportal_msgBody = $wpjobportal_template->body;

                        $this->replaceMatches($wpjobportal_msgSubject, $wpjobportal_matcharray);
                        $this->replaceMatches($wpjobportal_msgBody, $wpjobportal_matcharray);
                        $wpjobportal_senderEmail = $wpjobportal_config_array['mailfromaddress'];
                        $wpjobportal_senderName = $wpjobportal_config_array['mailfromname'];
                        //  Featured Company mail to User
                        if ($getEmailStatus->employer == 1) {
                            $this->sendEmail($Email, $wpjobportal_msgSubject, $wpjobportal_msgBody, $wpjobportal_senderEmail, $wpjobportal_senderName, '', 6); // 6 action for company featured hock
                        }
                        break;
                }
                break;
            case 2: // Mail For Job
                switch ($wpjobportal_action) {
                    case 1: // Add New Job
                        $record = $this->getRecordByTablenameAndId('wj_portal_jobs', $wpjobportal_id,19);
			             if($record == '' || empty($record)){
                            break;
                        }
                        $wpjobportal_userid = isset($record->id) ? $record->id : '';
                        $Username = isset($record->username) ? $record->username : '';
                        $wpjobportal_jobname = $record->jobtitle;
                        $Email = $record->useremail;
                        $wpjobportal_status = $record->status;
                        $wpjobportal_companyname = $record->companyname;
                        $wpjobportal_checkstatus = null;
                        $wpjobportal_link = null;
                        if ($wpjobportal_status == 1) {
                            $wpjobportal_checkstatus = esc_html(__('Approved', 'wp-job-portal'));
                            $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'viewjob', 'wpjobportalid'=>$wpjobportal_id, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        }
                        $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'myjobs', 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid())) ;
                        if ($wpjobportal_status == -1) {
                            $wpjobportal_checkstatus = esc_html(__('Rejected', 'wp-job-portal'));
                        }
                        if ($wpjobportal_status == 0) {
                            $wpjobportal_checkstatus = esc_html(__('Pending', 'wp-job-portal'));
                        }

                        if ($wpjobportal_status == 3) {
                            $wpjobportal_checkstatus = esc_html(__('Pending Due To Payment', 'wp-job-portal'));
                        }
                        $wpjobportal_matcharray = array(
                            '{JOB_TITLE}' => $wpjobportal_jobname,
                            '{EMPLOYER_NAME}' => $Username,
                            '{JOB_LINK}' => $wpjobportal_link,
                            '{JOB_STATUS}' => $wpjobportal_checkstatus,
                            '{COMPANY_NAME}' => $wpjobportal_companyname,
                            '{CURRENT_YEAR}' => gmdate('Y'),
                            '{SITETITLE}' => $siteTitle,
                            '{EMAIL}' => $record->useremail
                        );
                        $wpjobportal_template = $this->getTemplateForEmail('job-new');
                        $getEmailStatus = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getEmailTemplateStatus('add_new_job');
                        $wpjobportal_msgSubject = $wpjobportal_template->subject;
                        $wpjobportal_msgBody = $wpjobportal_template->body;
                        $this->replaceMatches($wpjobportal_msgSubject, $wpjobportal_matcharray);
                        $this->replaceMatches($wpjobportal_msgBody, $wpjobportal_matcharray);
                        $wpjobportal_senderEmail = $wpjobportal_config_array['mailfromaddress'];
                        $wpjobportal_senderName = $wpjobportal_config_array['mailfromname'];
                        // Add New Job mail to User
                        if ($getEmailStatus->employer == 1) {
                            $this->sendEmail($Email, $wpjobportal_msgSubject, $wpjobportal_msgBody, $wpjobportal_senderEmail, $wpjobportal_senderName, '', 7); // 7 action for add job hock
                        }
                        $wpjobportal_link =  esc_url_raw(admin_url("admin.php?page=wpjobportal_job"));
                        $wpjobportal_matcharray['{JOB_LINK}'] = $wpjobportal_link;
                        $wpjobportal_matcharray['{CURRENT_YEAR}'] = gmdate('Y');
                        $wpjobportal_matcharray['{SITETITLE}'] = $siteTitle;
                        $wpjobportal_msgSubject = $wpjobportal_template->subject;
                        $wpjobportal_msgBody = $wpjobportal_template->body;
                        $this->replaceMatches($wpjobportal_msgSubject, $wpjobportal_matcharray);
                        $this->replaceMatches($wpjobportal_msgBody, $wpjobportal_matcharray);
                        // Add New Job mail to admin
                        if ($getEmailStatus->admin == 1) {
                            $adminEmailid = $wpjobportal_config_array['adminemailaddress'];
                            $this->sendEmail($adminEmailid, $wpjobportal_msgSubject, $wpjobportal_msgBody, $wpjobportal_senderEmail, $wpjobportal_senderName, '', 8); // 8 action for add job hock
                        }
                        break;
                    case 2: // Job Delete
                        $wpjobportal_matcharray = array(
                            '{JOB_TITLE}' => $wpjobportal_mailextradata['jobtitle'],
                            '{EMPLOYER_NAME}' => isset($wpjobportal_mailextradata['user']) ? $wpjobportal_mailextradata['user'] : '',
                            '{COMPANY_NAME}' => $wpjobportal_mailextradata['companyname'],
                            '{CURRENT_YEAR}' => gmdate('Y'),
                            '{SITETITLE}' => $siteTitle,
                            '{EMAIL}' => $wpjobportal_mailextradata['useremail']
                        );
                        $Email = $wpjobportal_mailextradata['useremail'];
                        $wpjobportal_template = $this->getTemplateForEmail('job-delete');
                        $getEmailStatus = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getEmailTemplateStatus('delete_job');
                        $wpjobportal_msgSubject = $wpjobportal_template->subject;
                        $wpjobportal_msgBody = $wpjobportal_template->body;
                        $this->replaceMatches($wpjobportal_msgSubject, $wpjobportal_matcharray);
                        $this->replaceMatches($wpjobportal_msgBody, $wpjobportal_matcharray);
                        $wpjobportal_senderEmail = $wpjobportal_config_array['mailfromaddress'];
                        $wpjobportal_senderName = $wpjobportal_config_array['mailfromname'];
                        // job Delete mail to User
                        if ($getEmailStatus->employer == 1) {
                            $this->sendEmail($Email, $wpjobportal_msgSubject, $wpjobportal_msgBody, $wpjobportal_senderEmail, $wpjobportal_senderName, '', 10); // 10 for job delete
                        }
                        break;
                    case 3: // job approve OR reject
                        $record = $this->getRecordByTablenameAndId('wj_portal_jobs', $wpjobportal_id ,19);
                        $Username = isset($record->username) ? $record->username : '';
                        $wpjobportal_jobname = $record->jobtitle;
                        $Email = $record->useremail;
                        $wpjobportal_status = $record->status;
                        $featuredjob = $record->featuredjob;
                        $wpjobportal_link = null;
                        $wpjobportal_checkstatus = null;
                        if ($wpjobportal_status == 1) {
                            $wpjobportal_checkstatus = esc_html(__('Approved', 'wp-job-portal'));
                            $wpjobportal_link =  wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'viewjob', 'wpjobportalid'=>$wpjobportal_id, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid())) ;
                        }
                        $wpjobportal_link =  wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'myjobs', 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        if ($wpjobportal_status == -1) {
                            $wpjobportal_checkstatus = esc_html(__('Rejected', 'wp-job-portal'));
                        }
                        if ($wpjobportal_status == 2) {
                            $wpjobportal_checkstatus = esc_html(__('Removed', 'wp-job-portal'));
                        }
                        $wpjobportal_matcharray = array(
                            '{JOB_TITLE}' => $wpjobportal_jobname,
                            '{EMPLOYER_NAME}' => $Username,
                            '{JOB_LINK}' => $wpjobportal_link,
                            '{JOB_STATUS}' => $wpjobportal_checkstatus,
                            '{CURRENT_YEAR}' => gmdate('Y'),
                            '{SITETITLE}' => $siteTitle,
                            '{EMAIL}' => $Email
                        );
                        $wpjobportal_template = $this->getTemplateForEmail('job-status');
                        $getEmailStatus = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getEmailTemplateStatus('job_status');
                        $wpjobportal_msgSubject = $wpjobportal_template->subject;
                        $wpjobportal_msgBody = $wpjobportal_template->body;
                        $this->replaceMatches($wpjobportal_msgSubject, $wpjobportal_matcharray);
                        $this->replaceMatches($wpjobportal_msgBody, $wpjobportal_matcharray);
                        $wpjobportal_senderEmail = $wpjobportal_config_array['mailfromaddress'];
                        $wpjobportal_senderName = $wpjobportal_config_array['mailfromname'];
                        // job Approve mail to User
                        if ($getEmailStatus->employer == 1 && $record->uid !=0) {
                            $this->sendEmail($Email, $wpjobportal_msgSubject, $wpjobportal_msgBody, $wpjobportal_senderEmail, $wpjobportal_senderName, '', 11); // 11 action for job gold hock
                        }
                        if ($wpjobportal_status == 1) {
                            $wpjobportal_checkstatus = esc_html(__('Approved', 'wp-job-portal'));
                            $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'viewjob', 'wpjobportalid'=>$wpjobportal_id, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        }
                        if ($wpjobportal_status == -1) {
                            $wpjobportal_checkstatus = esc_html(__('Rejected', 'wp-job-portal'));
                            $wpjobportal_link = null;
                        }
                        if ($wpjobportal_status == 2) {
                            $wpjobportal_checkstatus = esc_html(__('Removed', 'wp-job-portal'));
                            $wpjobportal_link = null;
                        }
                        $wpjobportal_msgSubject = $wpjobportal_template->subject;
                        $wpjobportal_msgBody = $wpjobportal_template->body;
                        $this->replaceMatches($wpjobportal_msgSubject, $wpjobportal_matcharray);
                        $this->replaceMatches($wpjobportal_msgBody, $wpjobportal_matcharray);
                        // job Approve mail to visitor
                        if ($getEmailStatus->employer_visitor == 1 && $record->uid == 0) {
                            $this->sendEmail($Email, $wpjobportal_msgSubject, $wpjobportal_msgBody, $wpjobportal_senderEmail, $wpjobportal_senderName, '', 12); // 12 action for job gold hock
                        }
                        break;
                    case 5: // Job approve OR reject for featured
                        $record = $this->getRecordByTablenameAndId('wj_portal_jobs', $wpjobportal_id ,21);
                        if($record == ''){
                            break;
                        }
                        if(!isset($record->visname)){ // to handle log error
                            $record->visname = '';
                        }
                        $Username = isset($record->username) ? $record->username : $record->visname;
                        $wpjobportal_jobname = $record->jobtitle;
                        $Email = $record->useremail;
                        $featuredjob = $record->featuredjob;
                        $wpjobportal_link = null;
                        $wpjobportal_checkstatus = null;
                        $wpjobportal_checkfeaturedjob = null;
                        $wpjobportal_link =  wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'myjobs', 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        if ($featuredjob == -1) {
                            $wpjobportal_checkfeaturedjob = esc_html(__('rejected for featured', 'wp-job-portal'));
                        }
                        if ($featuredjob == 1) {
                            $wpjobportal_checkfeaturedjob = esc_html(__('approved for featured', 'wp-job-portal'));
                            $wpjobportal_link =  wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'viewjob', 'wpjobportalid'=>$wpjobportal_id, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        }
                        if ($featuredjob == 2) {
                            $wpjobportal_checkfeaturedjob = esc_html(__('removed for featured', 'wp-job-portal'));
                        }
                        if ($featuredjob == 0) {
                            $wpjobportal_checkfeaturedjob = esc_html(__('pending for featured', 'wp-job-portal'));
                        }
                        $wpjobportal_matcharray = array(
                            '{JOB_TITLE}' => $wpjobportal_jobname,
                            '{EMPLOYER_NAME}' => $Username,
                            '{JOB_LINK}' => $wpjobportal_link,
                            '{JOB_STATUS}' => $wpjobportal_checkfeaturedjob,
                            '{CURRENT_YEAR}' => gmdate('Y'),
                            '{SITETITLE}' => $siteTitle,
                            '{EMAIL}' => $Email
                        );
                        $wpjobportal_template = $this->getTemplateForEmail('job-status');
                        $getEmailStatus = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getEmailTemplateStatus('job_status');
                        $wpjobportal_msgSubject = $wpjobportal_template->subject;
                        $wpjobportal_msgBody = $wpjobportal_template->body;
                        $this->replaceMatches($wpjobportal_msgSubject, $wpjobportal_matcharray);
                        $this->replaceMatches($wpjobportal_msgBody, $wpjobportal_matcharray);
                        $wpjobportal_senderEmail = $wpjobportal_config_array['mailfromaddress'];
                        $wpjobportal_senderName = $wpjobportal_config_array['mailfromname'];
                        // job featured mail to User
                        if ($getEmailStatus->employer == 1 && $record->uid !=0) {
                            $this->sendEmail($Email, $wpjobportal_msgSubject, $wpjobportal_msgBody, $wpjobportal_senderEmail, $wpjobportal_senderName, '', 15); // 15 action for job gold hock
                        }
                        $wpjobportal_matcharray['{JOB_LINK}'] = $wpjobportal_link;
                        $wpjobportal_matcharray['{CURRENT_YEAR}'] = gmdate('Y');
                        $wpjobportal_matcharray['{SITETITLE}'] = $siteTitle;
                        $wpjobportal_msgSubject = $wpjobportal_template->subject;
                        $wpjobportal_msgBody = $wpjobportal_template->body;
                        $this->replaceMatches($wpjobportal_msgSubject, $wpjobportal_matcharray);
                        $this->replaceMatches($wpjobportal_msgBody, $wpjobportal_matcharray);
                        // job featured mail to visitor
                        if ($getEmailStatus->employer_visitor == 1 && $record->uid == 0) {
                            $this->sendEmail($Email, $wpjobportal_msgSubject, $wpjobportal_msgBody, $wpjobportal_senderEmail, $wpjobportal_senderName, '', 16); // 16 action for job gold hock
                        }
                        break;
                    case 6: // Add New visitor Job
                        $record = $this->getRecordByTablenameAndId('wj_portal_jobs', $wpjobportal_id);
                        $wpjobportal_visusername = $record->visname ? $record->visname : '';
                        $wpjobportal_jobname = $record->jobtitle;
                        $Email = $record->useremail;
                        $wpjobportal_companyname = $record->companyname;
                        $wpjobportal_status = $record->status;
                        $wpjobportal_checkstatus = null;
                        $wpjobportal_link = null;
                        if ($wpjobportal_status == 1) {
                            $wpjobportal_checkstatus = esc_html(__('Approved', 'wp-job-portal'));
                            $wpjobportal_link =  wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'viewjob', 'wpjobportalid'=>$wpjobportal_id, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        }
                        if ($wpjobportal_status == -1) {
                            $wpjobportal_checkstatus = esc_html(__('Rejected', 'wp-job-portal'));
                            $wpjobportal_link = "<strong>" . esc_html(__('Due to rejection of job you do not have permission to see job detail', 'wp-job-portal')) . "</strong>";
                        }
                        if ($wpjobportal_status == 0) {
                            $wpjobportal_checkstatus = esc_html(__('Pending', 'wp-job-portal'));
                            $wpjobportal_link = "<strong>" . esc_html(__('Due to pending status of job you do not have permission to see job detail', 'wp-job-portal')) . "</strong>";
                        }
                        $wpjobportal_matcharray = array(
                            '{JOB_TITLE}' => $wpjobportal_jobname,
                            '{EMPLOYER_NAME}' => $wpjobportal_visusername,
                            '{JOB_LINK}' => $wpjobportal_link,
                            '{JOB_STATUS}' => $wpjobportal_status,
                            '{COMPANY_NAME}' => $wpjobportal_companyname,
                            '{CURRENT_YEAR}' => gmdate('Y'),
                            '{SITETITLE}' => $siteTitle,
                            '{EMAIL}' => $Email
                        );
                        $wpjobportal_template = $this->getTemplateForEmail('job-new-vis');
                        $getEmailStatus = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getEmailTemplateStatus('add_new_job');
                        $wpjobportal_msgSubject = $wpjobportal_template->subject;
                        $wpjobportal_msgBody = $wpjobportal_template->body;
                        $this->replaceMatches($wpjobportal_msgSubject, $wpjobportal_matcharray);
                        $this->replaceMatches($wpjobportal_msgBody, $wpjobportal_matcharray);
                        $wpjobportal_senderEmail = $wpjobportal_config_array['mailfromaddress'];
                        $wpjobportal_senderName = $wpjobportal_config_array['mailfromname'];
                        // Add New visitor Job mail to admin
                        if ($getEmailStatus->admin == 1) {
                            $adminEmailid = $wpjobportal_config_array['adminemailaddress'];
                            $this->sendEmail($adminEmailid, $wpjobportal_msgSubject, $wpjobportal_msgBody, $wpjobportal_senderEmail, $wpjobportal_senderName, '', 8); // 8 action for add job hock
                        }
                        if ($wpjobportal_status == 1) {
                            $wpjobportal_checkstatus = esc_html(__('Approved', 'wp-job-portal'));
                            $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'viewjob', 'wpjobportalid'=>$wpjobportal_id, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        }
                        if ($wpjobportal_status == -1) {
                            $wpjobportal_checkstatus = esc_html(__('Rejected', 'wp-job-portal'));
                            $wpjobportal_link = "<strong>" . esc_html(__('Due to rejection of job you do not have permission to see job detail', 'wp-job-portal')) . "</strong>";
                        }
                        if ($wpjobportal_status == 0) {
                            $wpjobportal_checkstatus = esc_html(__('Pending', 'wp-job-portal'));
                            $wpjobportal_link = "<strong>" . esc_html(__('Due to pending status of job you do not have permission to see job detail', 'wp-job-portal')) . "</strong>";
                        }
                        $wpjobportal_matcharray['{JOB_LINK}'] = $wpjobportal_link;
                        $wpjobportal_matcharray['{CURRENT_YEAR}'] = gmdate('Y');
                        $wpjobportal_matcharray['{SITETITLE}'] = $siteTitle;
                        $wpjobportal_matcharray['{EMAIL}'] = $Email;
                        $wpjobportal_msgSubject = $wpjobportal_template->subject;
                        $wpjobportal_msgBody = $wpjobportal_template->body;
                        $this->replaceMatches($wpjobportal_msgSubject, $wpjobportal_matcharray);
                        $this->replaceMatches($wpjobportal_msgBody, $wpjobportal_matcharray);
                        // Add New visitor Job mail to visitor
                        if ($getEmailStatus->employer_visitor == 1) {
                            $this->sendEmail($Email, $wpjobportal_msgSubject, $wpjobportal_msgBody, $wpjobportal_senderEmail, $wpjobportal_senderName, '', 9); // 9 action for add job hock
                        }
                        break;
                }
                break;

            case 3: // Mail For Resume
                switch ($wpjobportal_action) {
                    case 1: // Add New Resume
                        $record = $this->getRecordByTablenameAndId('wj_portal_resume', $wpjobportal_id,1);
                        if($record == '' || empty($record)){
                            return;
                        }
                        $Username = $record->firstname . '' . $record->lastname;
                        if ($Username == '') {
                            $Username = $record->username;
                        }
                        $Email = isset($record->useremailfromresume) ? $record->useremailfromresume : '';
                        if ($Email == '') {
                            $Email = $record->useremail;
                        }
                        $wpjobportal_resumename = $record->resumetitle;
                        $wpjobportal_status = $record->resumestatus;
                        $wpjobportal_link = null;
                        $wpjobportal_checkstatus = null;
                        if ($wpjobportal_status == 1) {
                            $wpjobportal_checkstatus = esc_html(__('Approved', 'wp-job-portal'));
                            $wpjobportal_link =  wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'viewresume', 'wpjobportalid'=>$wpjobportal_id, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        }
                        if(in_array('multiresume', wpjobportal::$_active_addons)){
                            $wpjobportal_link =  wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'multiresume', 'wpjobportallt'=>'myresumes', 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        }else{
                            $wpjobportal_link =  wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'myresumes', 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        }
                        if ($wpjobportal_status == -1) {
                            $wpjobportal_checkstatus = esc_html(__('Rejected', 'wp-job-portal'));
                        }
                        if ($wpjobportal_status == 0) {
                            $wpjobportal_checkstatus = esc_html(__('Pending', 'wp-job-portal'));
                        }

                        if ($wpjobportal_status == 3) {
                            $wpjobportal_checkstatus = esc_html(__('Pending Due to Payment', 'wp-job-portal'));
                       }
                        $wpjobportal_matcharray = array(
                            '{RESUME_TITLE}' => $wpjobportal_resumename,
                            '{JOBSEEKER_NAME}' => $Username,
                            '{RESUME_STATUS}' => $wpjobportal_checkstatus,
                            '{RESUME_LINK}' => $wpjobportal_link,
                            '{CURRENT_YEAR}' => gmdate('Y'),
                            '{SITETITLE}' => $siteTitle,
                            '{EMAIL}' => $Email
                        );
                        $wpjobportal_template = $this->getTemplateForEmail('resume-new');
                        $getEmailStatus = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getEmailTemplateStatus('add_new_resume');
                        $wpjobportal_msgSubject = $wpjobportal_template->subject;
                        $wpjobportal_msgBody = $wpjobportal_template->body;
                        $this->replaceMatches($wpjobportal_msgSubject, $wpjobportal_matcharray);
                        $this->replaceMatches($wpjobportal_msgBody, $wpjobportal_matcharray);
                        $wpjobportal_senderEmail = $wpjobportal_config_array['mailfromaddress'];
                        $wpjobportal_senderName = $wpjobportal_config_array['mailfromname'];
                        // Add New resume mail to User
                        if ($getEmailStatus->jobseeker == 1) {
                            $this->sendEmail($Email, $wpjobportal_msgSubject, $wpjobportal_msgBody, $wpjobportal_senderEmail, $wpjobportal_senderName, '', $wpjobportal_action);
                        }
                        $wpjobportal_link =  esc_url_raw(admin_url("admin.php?page=wpjobportal_resume"));
                        $wpjobportal_matcharray['{RESUME_LINK}'] = $wpjobportal_link;
                        $wpjobportal_matcharray['{CURRENT_YEAR}'] = gmdate('Y');
                        $wpjobportal_matcharray['{SITETITLE}'] = $siteTitle;
                        $wpjobportal_matcharray['{EMAIL}'] = $Email;
                        $wpjobportal_msgSubject = $wpjobportal_template->subject;
                        $wpjobportal_msgBody = $wpjobportal_template->body;
                        $this->replaceMatches($wpjobportal_msgSubject, $wpjobportal_matcharray);
                        $this->replaceMatches($wpjobportal_msgBody, $wpjobportal_matcharray);
                        // Add New resume mail to admin
                        if ($getEmailStatus->admin == 1) {
                            $adminEmailid = $wpjobportal_config_array['adminemailaddress'];
                            $this->sendEmail($adminEmailid, $wpjobportal_msgSubject, $wpjobportal_msgBody, $wpjobportal_senderEmail, $wpjobportal_senderName, '', $wpjobportal_action);
                        }
                        break;
                    case 2: // Resume Approve or Reject
                        $record = $this->getRecordByTablenameAndId('wj_portal_resume', $wpjobportal_id,1);
                        if($record == '' || empty($record)){
                            break;
                        }
                        $Username = $record->firstname . '' . $record->lastname;
                        if ($Username == '') {
                            $Username = $record->username;
                        }
                        $Email = $record->useremailfromresume;
                        if ($Email == '') {
                            $Email = $record->useremail;
                        }
                        $wpjobportal_resumename = $record->resumetitle;
                        $wpjobportal_status = $record->resumestatus;
                        $wpjobportal_link = null;
                        $wpjobportal_checkstatus = null;
                        if(in_array('multiresume', wpjobportal::$_active_addons)){
                            $wpjobportal_link =  wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'multiresume', 'wpjobportallt'=>'myresumes', 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        }else{
                            $wpjobportal_link =  wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'myresumes', 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        }
                        if ($wpjobportal_status == 1) {
                            $wpjobportal_checkstatus = esc_html(__('Approved', 'wp-job-portal'));
                        }
                        if ($wpjobportal_status == -1) {
                            $wpjobportal_checkstatus = esc_html(__('Rejected', 'wp-job-portal'));
                        }
                        $wpjobportal_matcharray = array(
                            '{RESUME_TITLE}' => $wpjobportal_resumename,
                            '{JOBSEEKER_NAME}' => $Username,
                            '{RESUME_LINK}' => $wpjobportal_link,
                            '{RESUME_STATUS}' => $wpjobportal_checkstatus,
                            '{CURRENT_YEAR}' => gmdate('Y'),
                            '{SITETITLE}' => $siteTitle,
                            '{EMAIL}' => $Email
                        );
                        $wpjobportal_template = $this->getTemplateForEmail('resume-status');
                        $getEmailStatus = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getEmailTemplateStatus('resume_status');
                        $wpjobportal_msgSubject = $wpjobportal_template->subject;
                        $wpjobportal_msgBody = $wpjobportal_template->body;
                        $this->replaceMatches($wpjobportal_msgSubject, $wpjobportal_matcharray);
                        $this->replaceMatches($wpjobportal_msgBody, $wpjobportal_matcharray);
                        $wpjobportal_senderEmail = $wpjobportal_config_array['mailfromaddress'];
                        $wpjobportal_senderName = $wpjobportal_config_array['mailfromname'];
                        // resume Approve mail to jobseeker
                        if ($getEmailStatus->jobseeker == 1 && $record->uid != 0) {
                            $this->sendEmail($Email, $wpjobportal_msgSubject, $wpjobportal_msgBody, $wpjobportal_senderEmail, $wpjobportal_senderName, '', $wpjobportal_action);
                        }
                        if ($wpjobportal_status == 1) {
                            $wpjobportal_checkstatus = esc_html(__('Approved', 'wp-job-portal'));
                            $wpjobportal_link =  wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'viewresume', 'wpjobportalid'=>$wpjobportal_id, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        }
                        if ($wpjobportal_status == -1) {
                            $wpjobportal_checkstatus = esc_html(__('Rejected', 'wp-job-portal'));
                            $wpjobportal_link = null;
                        }
                        if ($wpjobportal_status == 2) {
                            $wpjobportal_checkstatus = esc_html(__('Removed', 'wp-job-portal'));
                            $wpjobportal_link = null;
                        }
                        if ($wpjobportal_status == 0) {
                            $wpjobportal_checkstatus = esc_html(__('Pending', 'wp-job-portal'));
                            $wpjobportal_link = null;
                        }
                        $wpjobportal_matcharray['{RESUME_LINK}'] = $wpjobportal_link;
                        $wpjobportal_matcharray['{RESUME_STATUS}'] = $wpjobportal_checkstatus;
                        $wpjobportal_matcharray['{CURRENT_YEAR}'] = gmdate('Y');
                        $wpjobportal_matcharray['{SITETITLE}'] = $siteTitle;
                        $wpjobportal_matcharray['{EMAIL}'] = $Email;
                        $wpjobportal_msgSubject = $wpjobportal_template->subject;
                        $wpjobportal_msgBody = $wpjobportal_template->body;
                        $this->replaceMatches($wpjobportal_msgSubject, $wpjobportal_matcharray);
                        $this->replaceMatches($wpjobportal_msgBody, $wpjobportal_matcharray);
                        // job Approve mail to visitor
                        if ($getEmailStatus->jobseeker_visitor == 1 && $record->uid == 0) {
                            $this->sendEmail($Email, $wpjobportal_msgSubject, $wpjobportal_msgBody, $wpjobportal_senderEmail, $wpjobportal_senderName, '', 12); // 12 action for job gold hock
                        }

                        break;
                    case 4: // resume approve OR reject for featured
                        $record = $this->getRecordByTablenameAndId('wj_portal_resume', $wpjobportal_id,3);
                        if($record == '' || empty($record)){
                            break;
                        }
                        $Username = $record->firstname . '' . $record->lastname;
                        if ($Username == '') {
                            $Username = $record->username;
                        }
                        $Email = $record->useremailfromresume;
                        if ($Email == '') {
                            $Email = $record->useremail;
                        }
                        $wpjobportal_resumename = $record->resumetitle;
                        $wpjobportal_status = $record->resumestatus;
                        $featuredresume = $record->featuredresume;
                        $wpjobportal_link = null;
                        $wpjobportal_checkfeaturedresume = null;
                        if(in_array('multiresume', wpjobportal::$_active_addons)){
                            $wpjobportal_link =  wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'multiresume', 'wpjobportallt'=>'myresumes', 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        }else{
                            $wpjobportal_link =  wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'myresumes', 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid())) ;
                        }
                        if ($featuredresume == -1) {
                            $wpjobportal_checkfeaturedresume = esc_html(__('rejected for featured', 'wp-job-portal'));
                        }
                        if ($featuredresume == 1) {
                            $wpjobportal_checkfeaturedresume = esc_html(__('approved for featured', 'wp-job-portal'));
                            $wpjobportal_link =  wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'viewresume', 'wpjobportalid'=>$wpjobportal_id, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        }
                        if ($featuredresume == 0) {
                            $wpjobportal_checkfeaturedresume = esc_html(__('pending for featured', 'wp-job-portal'));
                            $wpjobportal_link =  wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'viewresume', 'wpjobportalid'=>$wpjobportal_id, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        }
                        if ($featuredresume == 2) {
                            $wpjobportal_checkfeaturedresume = esc_html(__('removed for featured', 'wp-job-portal'));
                        }
                        $wpjobportal_matcharray = array(
                            '{RESUME_TITLE}' => $wpjobportal_resumename,
                            '{JOBSEEKER_NAME}' => $Username,
                            '{RESUME_LINK}' => $wpjobportal_link,
                            '{RESUME_STATUS}' => $wpjobportal_checkfeaturedresume,
                            '{CURRENT_YEAR}' => gmdate('Y'),
                            '{SITETITLE}' => $siteTitle,
                            '{EMAIL}' => $Email
                        );
                        $wpjobportal_template = $this->getTemplateForEmail('resume-status');
                        $getEmailStatus = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getEmailTemplateStatus('resume_status');
                        $wpjobportal_msgSubject = $wpjobportal_template->subject;
                        $wpjobportal_msgBody = $wpjobportal_template->body;
                        $this->replaceMatches($wpjobportal_msgSubject, $wpjobportal_matcharray);
                        $this->replaceMatches($wpjobportal_msgBody, $wpjobportal_matcharray);
                        $wpjobportal_senderEmail = $wpjobportal_config_array['mailfromaddress'];
                        $wpjobportal_senderName = $wpjobportal_config_array['mailfromname'];
                        // resume Approve mail to Jobseeker
                        if ($getEmailStatus->jobseeker == 1) {
                            $this->sendEmail($Email, $wpjobportal_msgSubject, $wpjobportal_msgBody, $wpjobportal_senderEmail, $wpjobportal_senderName, '', 4); // 4 action for job gold hock
                        }
                        $wpjobportal_link =  wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'viewresume', 'wpjobportalid'=>$wpjobportal_id, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        if ($featuredresume == 1) {
                            $wpjobportal_checkfeaturedresume = esc_html(__('approved for featured', 'wp-job-portal'));
                        }
                        if ($featuredresume == -1) {
                            $wpjobportal_checkfeaturedresume = esc_html(__('rejected for featured', 'wp-job-portal'));
                        }
                        if ($featuredresume == 2) {
                            $wpjobportal_checkfeaturedresume = esc_html(__('removed for featured', 'wp-job-portal'));
                        }
                        if ($featuredresume == 0) {
                            $wpjobportal_checkfeaturedresume = esc_html(__('pending for featured', 'wp-job-portal'));
                            $wpjobportal_link = null;
                        }
                        $wpjobportal_matcharray['{RESUME_LINK}'] = $wpjobportal_link;
                        $wpjobportal_matcharray['{RESUME_STATUS}'] = $wpjobportal_checkfeaturedresume;
                        $wpjobportal_matcharray['{CURRENT_YEAR}'] = gmdate('Y');
                        $wpjobportal_matcharray['{SITETITLE}'] = $siteTitle;
                        $wpjobportal_matcharray['{EMAIL}'] = $Email;
                        $wpjobportal_msgSubject = $wpjobportal_template->subject;
                        $wpjobportal_msgBody = $wpjobportal_template->body;
                        $this->replaceMatches($wpjobportal_msgSubject, $wpjobportal_matcharray);
                        $this->replaceMatches($wpjobportal_msgBody, $wpjobportal_matcharray);
                        // job Approve mail to visitor
                        if ($getEmailStatus->jobseeker_visitor == 1 && $record->uid == 0) {
                            $this->sendEmail($Email, $wpjobportal_msgSubject, $wpjobportal_msgBody, $wpjobportal_senderEmail, $wpjobportal_senderName, '', 12); // 12 action for job gold hock
                        }
                        break;
                    case 5: //Add new visitor resume
                        $record = $this->getRecordByTablenameAndId('wj_portal_resume', $wpjobportal_id);
                        $wpjobportal_visusername = $record->firstname . '' . $record->lastname;
                        $Email = $record->useremailfromresume;
                        $wpjobportal_resumename = $record->resumetitle;
                        $wpjobportal_status = $record->status;
                        if ($wpjobportal_status == 1) {
                            $wpjobportal_checkstatus = esc_html(__('Approved', 'wp-job-portal'));
                            $wpjobportal_link =  wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'viewresume', 'wpjobportalid'=>$wpjobportal_id, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        }
                        if ($wpjobportal_status == -1) {
                            $wpjobportal_checkstatus = esc_html(__('Rejected', 'wp-job-portal'));
                            $wpjobportal_link = "<strong>" . esc_html(__('Due to rejection of resume you do not have permission to see resume detail', 'wp-job-portal')) . "</strong>";
                        }
                        if ($wpjobportal_status == 0) {
                            $wpjobportal_checkstatus = esc_html(__('Pending', 'wp-job-portal'));
                            $wpjobportal_link = "<strong>" . esc_html(__('Due to pending status of resume you do not have permission to see resume detail', 'wp-job-portal')) . "</strong>";
                        }
                        $wpjobportal_matcharray = array(
                            '{RESUME_TITLE}' => $wpjobportal_resumename,
                            '{JOBSEEKER_NAME}' => $wpjobportal_visusername,
                            '{RESUME_STATUS}' => $wpjobportal_checkstatus,
                            '{RESUME_LINK}' => $wpjobportal_link,
                            '{CURRENT_YEAR}' => gmdate('Y'),
                            '{SITETITLE}' => $siteTitle,
                            '{EMAIL}' => $Email
                        );
                        $wpjobportal_template = $this->getTemplateForEmail('resume-new-vis');
                        $getEmailStatus = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getEmailTemplateStatus('add_new_resume_visitor');
                        $wpjobportal_msgSubject = $wpjobportal_template->subject;
                        $wpjobportal_msgBody = $wpjobportal_template->body;
                        $this->replaceMatches($wpjobportal_msgSubject, $wpjobportal_matcharray);
                        $this->replaceMatches($wpjobportal_msgBody, $wpjobportal_matcharray);

                        $wpjobportal_senderEmail = $wpjobportal_config_array['mailfromaddress'];
                        $wpjobportal_senderName = $wpjobportal_config_array['mailfromname'];
                        // Add New visitor resume mail to User
                        if ($getEmailStatus->jobseeker_visitor == 1) {
                            $this->sendEmail($Email, $wpjobportal_msgSubject, $wpjobportal_msgBody, $wpjobportal_senderEmail, $wpjobportal_senderName, '', 7); // 7 action for add job hock
                        }
                        $wpjobportal_link =  esc_url_raw(admin_url("admin.php?page=wpjobportal_resume"));
                        $wpjobportal_matcharray['{RESUME_LINK}'] = $wpjobportal_link;
                        $wpjobportal_matcharray['{CURRENT_YEAR}'] = gmdate('Y');
                        $wpjobportal_matcharray['{SITETITLE}'] = $siteTitle;
                        $wpjobportal_matcharray['{EMAIL}'] = $Email;
                        $wpjobportal_msgSubject = $wpjobportal_template->subject;
                        $wpjobportal_msgBody = $wpjobportal_template->body;
                        $this->replaceMatches($wpjobportal_msgSubject, $wpjobportal_matcharray);
                        $this->replaceMatches($wpjobportal_msgBody, $wpjobportal_matcharray);
                        // Add New visitor resume mail to admin
                        if ($getEmailStatus->admin == 1) {
                            $adminEmailid = $wpjobportal_config_array['adminemailaddress'];
                            $this->sendEmail($adminEmailid, $wpjobportal_msgSubject, $wpjobportal_msgBody, $wpjobportal_senderEmail, $wpjobportal_senderName, '', 8); // 8 action for add job hock
                        }

                    break;
                case 6://delete resume
                    $Email = $wpjobportal_mailextradata['useremail'];
                    $wpjobportal_matcharray = array(
                        '{RESUME_TITLE}' => $wpjobportal_mailextradata['resumetitle'],
                        '{JOBSEEKER_NAME}' => $wpjobportal_mailextradata['jobseekername'],
                        '{CURRENT_YEAR}' => gmdate('Y'),
                        '{SITETITLE}' => $siteTitle,
                        '{EMAIL}' => $Email
                    );
                    $wpjobportal_template = $this->getTemplateForEmail('resume-delete');
                    $getEmailStatus = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getEmailTemplateStatus('resume-delete');
                    $wpjobportal_msgSubject = $wpjobportal_template->subject;
                    $wpjobportal_msgBody = $wpjobportal_template->body;
                    $this->replaceMatches($wpjobportal_msgSubject, $wpjobportal_matcharray);
                    $this->replaceMatches($wpjobportal_msgBody, $wpjobportal_matcharray);
                    $wpjobportal_senderEmail = $wpjobportal_config_array['mailfromaddress'];
                    $wpjobportal_senderName = $wpjobportal_config_array['mailfromname'];
                    // Delete resume mail to User
                    if ($getEmailStatus->jobseeker == 1) {
                        $this->sendEmail($Email, $wpjobportal_msgSubject, $wpjobportal_msgBody, $wpjobportal_senderEmail, $wpjobportal_senderName, '', 3); // 3 action for company delete hock
                    }
                break;
                }
            break;
            case 4: // mail for purchase Pakages pack
                switch ($wpjobportal_action) {
                    case 1: //purchase package
                        $record = $this->getRecordByTablenameAndId('wj_portal_userpackages', $wpjobportal_id);
                        if(!$record){
                            return false;
                        }
                        $wpjobportal_username = $record->username;
                        $wpjobportal_packagename = $record->packagename;
                        $receiveremail = $record->useremailaddress;
                        $wpjobportal_link =  wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'purchasehistory', 'wpjobportallt'=>'purchasehistory')).">".esc_html(__('Package Detail','wp-job-portal'));
                        if($record->isfree){
                            $wpjobportal_packageprice = esc_html(__("Free",'wp-job-portal'));
                        }else{
                            $wpjobportal_overrideConfig = array('decimal_places'=>'fit_to_currency');
                            $wpjobportal_packageprice = wpjobportal::$_common->getFancyPrice($record->price,$record->currencyid,$wpjobportal_overrideConfig);
                        }
                        if($record->status==1){
                            $wpjobportal_status =  esc_html(__("Publish",'wp-job-portal'));
                        }else if($record->status==0){
                            $wpjobportal_status =  esc_html(__("Pending",'wp-job-portal'));
                        }else if($record->status==-1){
                            $wpjobportal_status =  esc_html(__("Rejected",'wp-job-portal'));
                        }else{
                            $wpjobportal_status = esc_html(__("Unknown",'wp-job-portal'));
                        }
                        $wpjobportal_matcharray = array(
                            '{USER_NAME}' => $wpjobportal_username,
                            '{PACKAGE_TITLE}' => $wpjobportal_packagename,
                            '{PACKAGE_PRICE}' => $wpjobportal_packageprice,
                            '{PACKAGE_LINK}' => $wpjobportal_link,
                            '{PUBLISH_STATUS}' => $wpjobportal_status,
                            '{CURRENT_YEAR}' => gmdate('Y'),
                            '{SITETITLE}' => $siteTitle,
                            '{EMAIL}' => $receiveremail
                        );
                        $wpjobportal_template = $this->getTemplateForEmail('package-purchase');
                        $wpjobportal_emailstatus = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getEmailTemplateStatus('package_purchase');
                        $wpjobportal_msgSubject = $wpjobportal_template->subject;
                        $wpjobportal_msgBody = $wpjobportal_template->body;
                        $this->replaceMatches($wpjobportal_msgSubject, $wpjobportal_matcharray);
                        $this->replaceMatches($wpjobportal_msgBody, $wpjobportal_matcharray);
                        $wpjobportal_senderEmail = $wpjobportal_config_array['mailfromaddress'];
                        $wpjobportal_senderName = $wpjobportal_config_array['mailfromname'];
                        // purchase package mail to User/agency
                        if( ($record->userid ? $wpjobportal_emailstatus->employer : $wpjobportal_emailstatus->jobseeker) == 1 ){
                            $this->sendEmail($receiveremail, $wpjobportal_msgSubject, $wpjobportal_msgBody, $wpjobportal_senderEmail, $wpjobportal_senderName, '');
                        }
                        // purchase package  mail to admin
                        $wpjobportal_template = $this->getTemplateForEmail('package-purchase-admin');
                        $wpjobportal_link = esc_url_raw(admin_url("admin.php?page=wpjobportal_purchasehistory"));
                        $wpjobportal_matcharray['{PACKAGE_LINK}']= $wpjobportal_link;
                        $wpjobportal_matcharray['{CURRENT_YEAR}'] = gmdate('Y');
                        $wpjobportal_matcharray['{SITETITLE}'] = $siteTitle;
                        $wpjobportal_matcharray['{EMAIL}'] = $receiveremail;
                        $wpjobportal_msgSubject = $wpjobportal_template->subject;
                        $wpjobportal_msgBody = $wpjobportal_template->body;
                        $this->replaceMatches($wpjobportal_msgSubject, $wpjobportal_matcharray);
                        $this->replaceMatches($wpjobportal_msgBody, $wpjobportal_matcharray);
                        if($wpjobportal_emailstatus->admin == 1) {
                            $adminEmailid = $wpjobportal_config_array['adminemailaddress'];
                            $this->sendEmail($adminEmailid, $wpjobportal_msgSubject, $wpjobportal_msgBody, $wpjobportal_senderEmail, $wpjobportal_senderName, '');
                        }
                        break;
                    case 2: // package status change
                        $record = $this->getRecordByTablenameAndId('wj_portal_userpackages', $wpjobportal_id);
                        if(!$record){
                            return false;
                        }
                        $wpjobportal_username = $record->username;
                        $wpjobportal_packagename = $record->packagename;
                        $receiveremail = $record->useremailaddress;
                        $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'purchasehistory', 'wpjobportallt'=>'purchasehistory')).">".esc_html(__('Package Detail','wp-job-portal'));
                        if($record->isfree){
                            $wpjobportal_packageprice = esc_html(__("Free",'wp-job-portal'));
                        }else{
                            $wpjobportal_overrideConfig = array('decimal_places'=>'fit_to_currency');
                            $wpjobportal_packageprice = WPJOBPORTALincluder::getJSModel('common')->getFancyPrice($record->price,$record->currencyid,$wpjobportal_overrideConfig);
                        }
                        if($record->status==1){
                            $wpjobportal_status =  esc_html(__("Publish",'wp-job-portal'));
                        }else{
                            $wpjobportal_status =  esc_html(__("Unpublish",'wp-job-portal'));
                        }
                        $wpjobportal_matcharray = array(
                            '{USER_NAME}' => $wpjobportal_username,
                            '{PACKAGE_TITLE}' => $wpjobportal_packagename,
                            '{PACKAGE_PRICE}' => $wpjobportal_packageprice,
                            '{PACKAGE_LINK}' => $wpjobportal_link,
                            '{PUBLISH_STATUS}' => $wpjobportal_status,
                            '{CURRENT_YEAR}' => gmdate('Y'),
                            '{SITETITLE}' => $siteTitle,
                            '{EMAIL}' => $receiveremail
                        );
                        $wpjobportal_template = $this->getTemplateForEmail('package-status');
                        $wpjobportal_emailstatus = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getEmailTemplateStatus('package_status');
                        $wpjobportal_msgSubject = $wpjobportal_template->subject;
                        $wpjobportal_msgBody = $wpjobportal_template->body;
                        $this->replaceMatches($wpjobportal_msgSubject, $wpjobportal_matcharray);
                        $this->replaceMatches($wpjobportal_msgBody, $wpjobportal_matcharray);
                        $wpjobportal_senderEmail = $wpjobportal_config_array['mailfromaddress'];
                        $wpjobportal_senderName = $wpjobportal_config_array['mailfromname'];
                        // package status changed  mail to User/agency
                        //if( ($record->userid ? $wpjobportal_emailstatus->agency : $wpjobportal_emailstatus->user) == 1 ){ // log error agency is undefined
                        if(  $wpjobportal_emailstatus->user == 1 ){
                            $this->sendEmail($receiveremail, $wpjobportal_msgSubject, $wpjobportal_msgBody, $wpjobportal_senderEmail, $wpjobportal_senderName, '');
                        }
                        break;
                }
                break;
            case 5: //Email for Apply job
                switch ($wpjobportal_action) {
                    case 1:// jobapply email to employer and jobseeker
                        $record = $this->getRecordByTablenameAndId('wj_portal_jobapply', $wpjobportal_id);
                        $wpjobportal_employername = $record->companycontactname;
                        if ($wpjobportal_employername == '') {
                            $wpjobportal_employername = $record->username;
                        }
                        $Emailtoemployer = $record->companycontactemail;
                        if ($Emailtoemployer == '') {
                            $Emailtoemployer = $record->useremailforemployer;
                        }
                        $Emailtojobseekr = $record->resumeemail;
                        if ($Emailtojobseekr == '') {
                            $Emailtojobseekr == $record->useremailforjobseeker;
                        }
                        $wpjobportal_companyname = $record->companyname;
                        $wpjobportal_resumename = $record->resumetitle;
                        $wpjobportal_jobtitle = $record->jobtitle;
                        $wpjobportal_resumeappliedstatus = $record->resumestatus;
                        $wpjobportal_resumetitle = $record->resumetitle;
                        $wpjobportal_jobseekername = $record->firstname . '' . $record->lastname;
                        if ($wpjobportal_resumeappliedstatus == 1) {
                            $wpjobportal_checkstatus = esc_html(__('Inbox', 'wp-job-portal'));
                        }
                        if ($wpjobportal_resumeappliedstatus == 1) {
                            $wpjobportal_checkstatus = esc_html(__('Spam', 'wp-job-portal'));
                        }
                        if ($wpjobportal_resumeappliedstatus == 1) {
                            $wpjobportal_checkstatus = esc_html(__('Hired', 'wp-job-portal'));
                        }
                        if ($wpjobportal_resumeappliedstatus == 1) {
                            $wpjobportal_checkstatus = esc_html(__('Rejected', 'wp-job-portal'));
                        }
                        if ($wpjobportal_resumeappliedstatus == 1) {
                            $wpjobportal_checkstatus = esc_html(__('Short listed', 'wp-job-portal'));
                        }
                        $wpjobportal_resumedata = null;
                        $wpjobportal_matcharray = array(
                            '{JOBSEEKER_NAME}' => $wpjobportal_jobseekername,
                            '{EMPLOYER_NAME}' => $wpjobportal_employername,
                            '{RESUME_APPLIED_STATUS}' => $wpjobportal_checkstatus,
                            '{RESUME_TITLE}' => $wpjobportal_resumename,
                            '{JOB_TITLE}' => $wpjobportal_jobtitle,
                            '{RESUME_DATA}' => $wpjobportal_resumedata,
                            '{CURRENT_YEAR}' => gmdate('Y'),
                            '{SITETITLE}' => $siteTitle,
                            '{EMAIL}' => $Emailtoemployer
                        );
                        $wpjobportal_template = $this->getTemplateForEmail('jobapply-employer');
                        $getEmailStatus = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getEmailTemplateStatus($wpjobportal_template->id);
                        $wpjobportal_msgSubject = $wpjobportal_template->subject;
                        $wpjobportal_msgBody = $wpjobportal_template->body;
                        $this->replaceMatches($wpjobportal_msgSubject, $wpjobportal_matcharray);
                        $this->replaceMatches($wpjobportal_msgBody, $wpjobportal_matcharray);
                        $wpjobportal_senderEmail = $wpjobportal_config_array['mailfromaddress'];
                        $wpjobportal_senderName = $wpjobportal_config_array['mailfromname'];
                        // Add New Job mail to employer
                        if ($getEmailStatus->employer == 1) {
                            $this->sendEmail($Emailtoemployer, $wpjobportal_msgSubject, $wpjobportal_msgBody, $wpjobportal_senderEmail, $wpjobportal_senderName, '', 7); // 7 action for add job hock
                        }
                        $wpjobportal_template = $this->getTemplateForEmail('jobapply-jobseeker');
                        $getEmailStatus = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getEmailTemplateStatus($wpjobportal_template->id);
                        $wpjobportal_matcharray = array(
                            '{JOBSEEKER_NAME}' => $wpjobportal_jobseekername,
                            '{RESUME_APPLIED_STATUS}' => $wpjobportal_checkstatus,
                            '{RESUME_TITLE}' => $wpjobportal_resumename,
                            '{COMPANY_NAME}' => $wpjobportal_companyname,
                            '{JOB_TITLE}' => $wpjobportal_jobtitle,
                            '{CURRENT_YEAR}' => gmdate('Y'),
                            '{SITETITLE}' => $siteTitle,
                            '{EMAIL}' => $Emailtojobseekr
                        );
                        $wpjobportal_msgSubject = $wpjobportal_template->subject;
                        $wpjobportal_msgBody = $wpjobportal_template->body;
                        $this->replaceMatches($wpjobportal_msgSubject, $wpjobportal_matcharray);
                        $this->replaceMatches($wpjobportal_msgBody, $wpjobportal_matcharray);
                        // jobapply mail to jobseeker
                        if ($getEmailStatus->jobseeker == 1) {
                            $this->sendEmail($Emailtojobseekr, $wpjobportal_msgSubject, $wpjobportal_msgBody, $wpjobportal_senderEmail, $wpjobportal_senderName, '', 8); // 8 action for add job hock
                        }
                        break;
                }

                break;
            case 6: //employer OR jobseeker resgistration
                switch ($wpjobportal_action) {
                    case 1: //for employer registration
                        $record = $this->getRecordByTablenameAndId('users', $wpjobportal_id);
                        $wpjobportal_link = null;
                        $wpjobportal_checkuserrole = null;
                        $Username = $record->username;
                        $Email = $record->useremail;
                        $wpjobportal_userrole = $record->userrole;
                        $wpjobportal_link =  wpjobportal::wpjobportal_makeUrl(array('wpjobportalpageid'=>WPJOBPORTALRequest::getVar('wpjobportalpageid')));
                        if ($wpjobportal_userrole == 1) {
                            $wpjobportal_checkuserrole = esc_html(__('Employer', 'wp-job-portal'));
                        }
                        $wpjobportal_matcharray = array(
                            '{USER_ROLE}' => $wpjobportal_checkuserrole,
                            '{USER_NAME}' => $Username,
                            '{CONTROL_PANEL_LINK}' => $wpjobportal_link,
                            '{CURRENT_YEAR}' => gmdate('Y'),
                            '{EMAIL}' => $Email,
                            '{SITETITLE}' => $siteTitle
                        );
                        $wpjobportal_template = $this->getTemplateForEmail('employer-new');
                        $getEmailStatus = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getEmailTemplateStatus('add_new_employer');
                        $wpjobportal_msgSubject = $wpjobportal_template->subject;
                        $wpjobportal_msgBody = $wpjobportal_template->body;
                        $this->replaceMatches($wpjobportal_msgSubject, $wpjobportal_matcharray);
                        $this->replaceMatches($wpjobportal_msgBody, $wpjobportal_matcharray);
                        $wpjobportal_senderEmail = $wpjobportal_config_array['mailfromaddress'];
                        $wpjobportal_senderName = $wpjobportal_config_array['mailfromname'];
                        // New employer registration mail to user
                        if ($getEmailStatus->employer == 1) {
                            $this->sendEmail($Email, $wpjobportal_msgSubject, $wpjobportal_msgBody, $wpjobportal_senderEmail, $wpjobportal_senderName, '', 4); // 4 action for job gold hock
                        }
                        $wpjobportal_link = esc_url_raw(admin_url("admin.php?page=wpjobportal"));
                        $wpjobportal_matcharray['{CONTROL_PANEL_LINK}'] = $wpjobportal_link;
                        $wpjobportal_msgSubject = $wpjobportal_template->subject;
                        $wpjobportal_msgBody = $wpjobportal_template->body;
                        $this->replaceMatches($wpjobportal_msgSubject, $wpjobportal_matcharray);
                        $this->replaceMatches($wpjobportal_msgBody, $wpjobportal_matcharray);
                        $wpjobportal_senderEmail = $wpjobportal_config_array['mailfromaddress'];
                        $wpjobportal_senderName = $wpjobportal_config_array['mailfromname'];
                        // New employer registration mail to admin
                        if ($getEmailStatus->admin == 1) {
                            $adminEmailid = $wpjobportal_config_array['adminemailaddress'];
                            $this->sendEmail($adminEmailid, $wpjobportal_msgSubject, $wpjobportal_msgBody, $wpjobportal_senderEmail, $wpjobportal_senderName, '', 4); // 4 action for job gold hock
                        }
                        break;
                    case 2: //for jobseeker registration
                        $record = $this->getRecordByTablenameAndId('users', $wpjobportal_id);
                        $wpjobportal_link = null;
                        $wpjobportal_checkuserrole = null;
                        $Username = $record->username;
                        $Email = $record->useremail;
                        $wpjobportal_userrole = $record->userrole;
                        $wpjobportal_link =  wpjobportal::wpjobportal_makeUrl(array('wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        if ($wpjobportal_userrole == 2) {
                            $wpjobportal_checkuserrole = esc_html(__('Job seeker', 'wp-job-portal'));
                        }
                        $wpjobportal_matcharray = array(
                            '{USER_ROLE}' => $wpjobportal_checkuserrole,
                            '{USER_NAME}' => $Username,
                            '{CONTROL_PANEL_LINK}' => $wpjobportal_link,
                            '{CURRENT_YEAR}' => gmdate('Y'),
                            '{EMAIL}' => $Email,
                            '{SITETITLE}' => $siteTitle
                        );
                        $wpjobportal_template = $this->getTemplateForEmail('jobseeker-new');
                        $getEmailStatus = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getEmailTemplateStatus('add_new_jobseeker');
                        $wpjobportal_msgSubject = $wpjobportal_template->subject;
                        $wpjobportal_msgBody = $wpjobportal_template->body;
                        $this->replaceMatches($wpjobportal_msgSubject, $wpjobportal_matcharray);
                        $this->replaceMatches($wpjobportal_msgBody, $wpjobportal_matcharray);
                        $wpjobportal_senderEmail = $wpjobportal_config_array['mailfromaddress'];
                        $wpjobportal_senderName = $wpjobportal_config_array['mailfromname'];
                        // New jobseeker registration mail to user
                        if ($getEmailStatus->jobseeker == 1) {
                            $this->sendEmail($Email, $wpjobportal_msgSubject, $wpjobportal_msgBody, $wpjobportal_senderEmail, $wpjobportal_senderName, '', 4); // 4 action for job gold hock
                        }
                        $wpjobportal_link =  esc_url_raw(admin_url("admin.php?page=wpjobportal"));
                        $wpjobportal_matcharray['{CONTROL_PANEL_LINK}'] = $wpjobportal_link;
                        $wpjobportal_msgSubject = $wpjobportal_template->subject;
                        $wpjobportal_msgBody = $wpjobportal_template->body;
                        $this->replaceMatches($wpjobportal_msgSubject, $wpjobportal_matcharray);
                        $this->replaceMatches($wpjobportal_msgBody, $wpjobportal_matcharray);
                        $wpjobportal_senderEmail = $wpjobportal_config_array['mailfromaddress'];
                        $wpjobportal_senderName = $wpjobportal_config_array['mailfromname'];
                        // New jobseeker registration mail to admin
                        if ($getEmailStatus->admin == 1) {
                            $adminEmailid = $wpjobportal_config_array['adminemailaddress'];
                        }
                        break;
                }

                break;

            case 7: //employer OR jobseeker Send message
                switch ($wpjobportal_action) {
                    case 1: //Send Messaege
                                /*
                    stdClass Object
                    (
                        [message] =>
                    ffff
                        [sendby] => 3
                        [created] => 2024-09-05 13:06:19
                        [jobseekerid] => 2
                        [employerid] => 3
                        [id] => 2
                        [status] => 1
                        [replytoid] => 1
                        [jobid] => 1
                        [resumeid] => 1
                        [jobseeker_data] => stdClass Object
                            (
                                [username] => jobseeker jobseeker
                                [useremail] => jobseeker@tretrt.jo
                                [userrole] => 2
                            )
                        [employer_data] => stdClass Object
                            (
                                [username] => employer employer
                                [useremail] => employer@emigal.com
                                [userrole] => 1
                            )

                    )*/
                        $record = $this->getRecordByTablenameAndId('wj_portal_messages', $wpjobportal_id);
                        if(empty($record)){// to avoid notices in case of no data
                            return;
                        }
                        $ref_data = '';
                        if(is_numeric($record->jobid) && $record->jobid > 0){ // if message is about job apply then data will have jobid
                            $ref_data = WPJOBPORTALincluder::getJSModel('job')->getJobTitleById($record->jobid);
                            //$ref_data .= '&nbsp;('. __('Job' ,'wp-job-portal') .')';
                        }elseif(is_numeric($record->resumeid) && $record->resumeid > 0){ // if message is about job apply then data will have jobid
                            $ref_data = WPJOBPORTALincluder::getJSModel('resume')->getResumeTitleById($record->resumeid);
                            //$ref_data .= '&nbsp;('. __('Resume','wp-job-portal').')';
                        }
                        // define values for sender and recipient
                        //(since we are using same email template for job seekr and employer notification so using "sendbyid" to identify sender and reicpient)
                        $wpjobportal_send_email_employer = 0;
                        $wpjobportal_send_email_jobseeker = 0;
                        $Email = ''; // handling log error
                        if($record->sendby == $record->jobseekerid){// jobseeker sent message
                            $recipient_name = $record->employer_data->username;
                            $wpjobportal_sender_name = $record->jobseeker_data->username;
                            $Email = $record->employer_data->useremail;
                            $wpjobportal_sender_role = __('Job Seeker','wp-job-portal');
                            $wpjobportal_send_email_employer = 1;
                        }elseif($record->sendby == $record->employerid){// employer sent message
                            $recipient_name = $record->jobseeker_data->username;
                            $wpjobportal_sender_name = $record->employer_data->username;
                            $Email = $record->jobseeker_data->useremail;
                            $wpjobportal_sender_role = __('Employer','wp-job-portal');
                            $wpjobportal_send_email_jobseeker = 1;
                        }
                        if($Email == ""){
                            if(current_user_can('manage_options')){ // admin
                                if(!empty($record->jobseeker_data)){ // log error
                                    $recipient_name = $record->jobseeker_data->username;
                                    $Email = $record->jobseeker_data->useremail;
                                }
                                if(!empty($record->employer_data)){ // log error
                                    $wpjobportal_sender_name = $record->employer_data->username;
                                }
                                $wpjobportal_sender_role = __('Admin','wp-job-portal');
                                $wpjobportal_send_email_jobseeker = 1;
                            }
                        }

                        $message = $record->message;
                        $message_link_id = $record->replytoid;
                        if(is_numeric($message_link_id) && $message_link_id > 0){
                        }else{
                            $message_link_id = $record->id;
                        }
                        $wpjobportal_link =  wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'message', 'wpjobportallt'=>'sendmessage', 'wpjobportalid'=>$message_link_id, 'wpjobportalpageid'=> wpjobportal::wpjobportal_getPageid() ));

                        $wpjobportal_matcharray = array(
                            '{RECIPIENT_NAME}' => $recipient_name,
                            '{SENDER_NAME}' => $wpjobportal_sender_name,
                            '{SENDER_USER_ROLE}' => $wpjobportal_sender_role,
                            '{MESSAGE_TEXT}' => $message,
                            '{ENTITY_INFO}' => $ref_data,
                            '{MESSAGE_LINK}' => $wpjobportal_link,
                            '{CURRENT_YEAR}' => gmdate('Y'),
                            '{EMAIL}' => $Email,
                            '{SITETITLE}' => $siteTitle
                        );
                        $wpjobportal_template = $this->getTemplateForEmail('new-message');
                        $getEmailStatus = WPJOBPORTALincluder::getJSModel('emailtemplatestatus')->getEmailTemplateStatus('new_message');
                        $wpjobportal_msgSubject = $wpjobportal_template->subject;
                        $wpjobportal_msgBody = $wpjobportal_template->body;
                        $this->replaceMatches($wpjobportal_msgSubject, $wpjobportal_matcharray);
                        $this->replaceMatches($wpjobportal_msgBody, $wpjobportal_matcharray);
                        $wpjobportal_senderEmail = $wpjobportal_config_array['mailfromaddress'];
                        $wpjobportal_senderName = $wpjobportal_config_array['mailfromname'];
                        // send email

                        // echo var_dump($wpjobportal_msgSubject);
                        // echo var_dump($wpjobportal_msgBody);

                        // echo '<pre>';print_r($wpjobportal_matcharray);echo '</pre>';
                        // die('email model 1237 ');

                        // sending email for employer
                        if ($wpjobportal_send_email_employer == 1 && $getEmailStatus->employer == 1) {
                            $this->sendEmail($Email, $wpjobportal_msgSubject, $wpjobportal_msgBody, $wpjobportal_senderEmail, $wpjobportal_senderName, '', 8); // 8 action for send message
                        }

                        // sending email for jobseeker
                        if ($wpjobportal_send_email_jobseeker == 1 && $getEmailStatus->jobseeker == 1) {
                            $this->sendEmail($Email, $wpjobportal_msgSubject, $wpjobportal_msgBody, $wpjobportal_senderEmail, $wpjobportal_senderName, '', 8); // 8 action for send message
                        }



                        // $wpjobportal_link = esc_url_raw(admin_url("admin.php?page=wpjobportal"));
                        // $wpjobportal_matcharray['{CONTROL_PANEL_LINK}'] = $wpjobportal_link;
                        // $wpjobportal_msgSubject = $wpjobportal_template->subject;
                        // $wpjobportal_msgBody = $wpjobportal_template->body;
                        // $this->replaceMatches($wpjobportal_msgSubject, $wpjobportal_matcharray);
                        // $this->replaceMatches($wpjobportal_msgBody, $wpjobportal_matcharray);
                        // $wpjobportal_senderEmail = $wpjobportal_config_array['mailfromaddress'];
                        // $wpjobportal_senderName = $wpjobportal_config_array['mailfromname'];
                        // // New employer registration mail to admin
                        // if ($getEmailStatus->admin == 1) {
                        //     $adminEmailid = $wpjobportal_config_array['adminemailaddress'];
                        //     $this->sendEmail($adminEmailid, $wpjobportal_msgSubject, $wpjobportal_msgBody, $wpjobportal_senderEmail, $wpjobportal_senderName, '', 4); // 4 action for job gold hock
                        // }
                        break;
                }

                break;
        }
    }

    function getTemplate($wpjobportal_tempfor) {

        switch ($wpjobportal_tempfor) {
            case 'd-cm' : $wpjobportal_tempatefor = 'company-delete';
                break;
            case 'ew-obv' : $wpjobportal_tempatefor = 'job-new-vis';
                break;
            case 'em-n' : $wpjobportal_tempatefor = 'employer-new';
                break;
            case 'obs-n' : $wpjobportal_tempatefor = 'jobseeker-new';
                break;
            case 'ob-d' : $wpjobportal_tempatefor = 'job-delete';
                break;
            case 'obse-ps' : $wpjobportal_tempatefor = 'jobseeker-purcahse-package-status';
                break;
            case 'js-jap' : $wpjobportal_tempatefor = 'jobapply-jobseeker';
                break;
            case 'em-jap' : $wpjobportal_tempatefor = 'jobapply-employer';
                break;
            case 'ew-cm' : $wpjobportal_tempatefor = 'company-new';
                break;
            case 'cm-sts' : $wpjobportal_tempatefor = 'company-status';
                break;
            case 'cm-rj' : $wpjobportal_tempatefor = 'company-rejecting';
                break;
            case 'ew-ob' : $wpjobportal_tempatefor = 'job-new';
                break;
            case 'ob-sts' : $wpjobportal_tempatefor = 'job-Status';
                break;
            case 'ob-rj' : $wpjobportal_tempatefor = 'job-rejecting';
                break;
            case 'ap-rs' : $wpjobportal_tempatefor = 'applied-resume_status';
                break;
            case 'ew-rm' : $wpjobportal_tempatefor = 'resume-new';
                break;
            case 'ew-rmv' : $wpjobportal_tempatefor = 'resume-new-vis';
                break;
            case 'rm-sts' : $wpjobportal_tempatefor = 'resume-status';
                break;
            case 'ew-ms' : $wpjobportal_tempatefor = 'message-email';
                break;
            case 'rm-rj' : $wpjobportal_tempatefor = 'resume-rejecting';
                break;
            case 'ob-pe' : $wpjobportal_tempatefor = 'jobseeker-package-expire';
                break;
            case 'em-pe' : $wpjobportal_tempatefor = 'employer-package-expire';
                break;
            case 'em-pc' : $wpjobportal_tempatefor = 'employer-purchase-credit-pack';
                break;
            case 'obs-pc' : $wpjobportal_tempatefor = 'jobseeker-purchase-credit-pack';
                break;
            case 'ms-sy' : $wpjobportal_tempatefor = 'message-email';
                break;
            case 'jb-at' : $wpjobportal_tempatefor = 'job-alert';
                break;
            case 'jb-at-vis' : $wpjobportal_tempatefor = 'job-alert-visitor';
                break;
            case 'jb-to-fri' : $wpjobportal_tempatefor = 'job-to-friend';
                break;
            case 'd-rs' : $wpjobportal_tempatefor = 'resume-delete';
                break;
            case 'ad-jap' : $wpjobportal_tempatefor = 'jobapply-jobapply';
                break;
            case 'ap-jap' : $wpjobportal_tempatefor = 'applied-resume_status';
                break;
            case 'ew-pk-ad': $wpjobportal_tempatefor = 'package-purchase-admin';
                break;
            case 'ew-pk': $wpjobportal_tempatefor = 'package-purchase';
                break;
            case 'st-pk': $wpjobportal_tempatefor = 'package-status';
                break;
            case 'new-msg': $wpjobportal_tempatefor = 'new-message';
                break;
            default :
                $wpjobportal_tempatefor = 'company-new';
                break;
        }
        $query = "SELECT * FROM `" . wpjobportal::$_db->prefix . "wj_portal_emailtemplates` WHERE templatefor = '".esc_sql($wpjobportal_tempatefor)."'";
        wpjobportal::$_data[0] = wpjobportaldb::get_row($query);
        return;
    }

    function storeEmailTemplate($wpjobportal_data) {
        if (empty($wpjobportal_data))
            return false;
        $wpjobportal_data = wpjobportal::wpjobportal_sanitizeData($wpjobportal_data);
        $wpjobportal_data['body'] = wpautop(wptexturize(wpjobportalphplib::wpJP_stripslashes(WPJOBPORTALrequest::getVar('body','post','','',1))));;
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('emailtemplate');
        if (!$wpjobportal_row->bind($wpjobportal_data)) {
            return WPJOBPORTAL_SAVE_ERROR;
        }
        if (!$wpjobportal_row->store()) {
            return WPJOBPORTAL_SAVE_ERROR;
        }

        return WPJOBPORTAL_SAVED;
    }

    function sendMailtoVisitor($wpjobportal_jobid) {
        if ($wpjobportal_jobid)
            if ((is_numeric($wpjobportal_jobid) == false) || ($wpjobportal_jobid == 0) || ($wpjobportal_jobid == ''))
                return false;

        $wpjobportal_templatefor = 'job-new-vis';

        $query = "SELECT template.* FROM `" . wpjobportal::$_db->prefix . "wj_portal_emailtemplates` AS template	WHERE template.templatefor = '" . esc_sql($wpjobportal_templatefor)."'";

        $wpjobportal_template = wpjobportaldb::get_row($query);
        $wpjobportal_msgSubject = $wpjobportal_template->subject;
        $wpjobportal_msgBody = $wpjobportal_template->body;
        $wpjobportal_jobquery = "SELECT job.id AS id,job.title, job.jobstatus,job.jobid AS jobid, company.name AS companyname, cat.cat_title AS cattitle,job.sendemail,company.contactemail, CONCAT(user.first_name,' ',user.last_name) AS contactname
                              FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                              ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON company.id = job.companyid
                              LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_users` AS user ON user.id = company.uid
                              LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON cat.id = job.jobcategory
                              WHERE job.id = " . esc_sql($wpjobportal_jobid);
        $wpjobportal_jobuser = wpjobportaldb::get_row($wpjobportal_jobquery);
        if ($wpjobportal_jobuser->jobstatus == 1) {

            $CompanyName = $wpjobportal_jobuser->companyname;
            $JobCategory = $wpjobportal_jobuser->cattitle;
            $ContactName = $wpjobportal_jobuser->contactname;
            $JobTitle = $wpjobportal_jobuser->title;
            if ($wpjobportal_jobuser->jobstatus == 1)
                $JobStatus = esc_html(__('Approved', 'wp-job-portal'));
            else
                $JobStatus = esc_html(__('Waiting for approval', 'wp-job-portal'));
            $EmployerEmail = $wpjobportal_jobuser->contactemail;
            $ContactName = $wpjobportal_jobuser->contactname;
            if($ContactName == ''){
                $ContactName = esc_html(__('Visitor', 'wp-job-portal'));
            }
            $siteTitle = wpjobportal::$_config->getConfigValue('title');
			$wpjobportal_joblink = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'viewjob', 'wpjobportalid'=>$wpjobportal_jobid, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
            $wpjobportal_msgSubject = wpjobportalphplib::wpJP_str_replace('{COMPANY_NAME}', $CompanyName, $wpjobportal_msgSubject);
            $wpjobportal_msgSubject = wpjobportalphplib::wpJP_str_replace('{CONTACT_NAME}', $ContactName, $wpjobportal_msgSubject);
            $wpjobportal_msgSubject = wpjobportalphplib::wpJP_str_replace('{JOB_CATEGORY}', $JobCategory, $wpjobportal_msgSubject);
            $wpjobportal_msgSubject = wpjobportalphplib::wpJP_str_replace('{JOB_TITLE}', $JobTitle, $wpjobportal_msgSubject);
            $wpjobportal_msgSubject = wpjobportalphplib::wpJP_str_replace('{JOB_STATUS}', $JobStatus, $wpjobportal_msgSubject);
            $wpjobportal_msgSubject = wpjobportalphplib::wpJP_str_replace('{EMPLOYER_NAME}', $ContactName, $wpjobportal_msgSubject);
            $wpjobportal_msgSubject = wpjobportalphplib::wpJP_str_replace('{JOB_LINK}', $wpjobportal_joblink, $wpjobportal_msgSubject);
            $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{SITETITLE}', $siteTitle, $wpjobportal_msgSubject);
            $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{COMPANY_NAME}', $CompanyName, $wpjobportal_msgBody);
            $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{CONTACT_NAME}', $ContactName, $wpjobportal_msgBody);
            $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{JOB_CATEGORY}', $JobCategory, $wpjobportal_msgBody);
            $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{JOB_TITLE}', $JobTitle, $wpjobportal_msgBody);
            $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{JOB_STATUS}', $JobStatus, $wpjobportal_msgBody);
            $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{EMPLOYER_NAME}', $ContactName, $wpjobportal_msgBody);
            $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{JOB_LINK}', $wpjobportal_joblink, $wpjobportal_msgBody);
            $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{SITETITLE}', $siteTitle, $wpjobportal_msgBody);
            //$wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{EMAIL}', $Email, $wpjobportal_msgBody); // handling log error
            $wpjobportal_msgBody = wpjobportalphplib::wpJP_str_replace('{CURRENT_YEAR}', gmdate('Y'), $wpjobportal_msgBody);

            $wpjobportal_config = WPJOBPORTALincluder::getJSModel('configuration')->getConfigByFor('visitor');
            if ($wpjobportal_config['visitor_can_edit_job'] == 1) {
                $wpjobportal_path = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'employer', 'wpjobportallt'=>'addjob', 'email'=>$wpjobportal_jobuser->contactemail, 'jobid'=>$wpjobportal_jobuser->jobid, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                $wpjobportal_path = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'employer', 'wpjobportallt'=>'addjob', 'wpjobportalid'=>$wpjobportal_jobuser->id, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                $wpjobportal_text = '<br><a href="' . esc_url($wpjobportal_path) . '" target="_blank" >' . esc_html(__('click here to edit job', 'wp-job-portal')) . '</a>';
                $wpjobportal_msgBody .= $wpjobportal_text;
            }

            $wpjobportal_emailconfig = WPJOBPORTALincluder::getJSModel('configuration')->getConfigByFor('email');
            $wpjobportal_senderName = $wpjobportal_emailconfig['mailfromname'];
            $wpjobportal_senderEmail = $wpjobportal_emailconfig['mailfromaddress'];

            $recevierEmail = $EmployerEmail;

            WPJOBPORTALincluder::getJSModel('common')->sendEmail($recevierEmail, $wpjobportal_msgSubject, $wpjobportal_msgBody, $wpjobportal_senderEmail, $wpjobportal_senderName );
        }
    }

    function getTemplateForEmail($wpjobportal_templatefor) {
        $query = "SELECT * FROM `" . wpjobportal::$_db->prefix . "wj_portal_emailtemplates` WHERE templatefor = '" . esc_sql($wpjobportal_templatefor) . "'";
        $wpjobportal_template = wpjobportal::$_db->get_row($query);
        if (wpjobportal::$_db->last_error != null) {
            WPJOBPORTALincluder::getJSModel('systemerror')->addSystemError();
        }
        return $wpjobportal_template;
    }

    function replaceMatches(&$wpjobportal_string, $wpjobportal_matcharray) {
        foreach ($wpjobportal_matcharray AS $find => $replace) {
            $wpjobportal_string = wpjobportalphplib::wpJP_str_replace($find, $replace, $wpjobportal_string);
        }
    }
    function wpjobportal_set_html_content_type() {
        return 'text/html';
    }

    function sendEmail($recevierEmail, $wpjobportal_subject, $body, $wpjobportal_senderEmail, $wpjobportal_senderName, $attachments = '') {
        if (!$wpjobportal_senderName)
            $wpjobportal_senderName = wpjobportal::$_configuration['title'];
        $headers = 'From: ' . $wpjobportal_senderName . ' <' . $wpjobportal_senderEmail . '>' . "\r\n";
        add_filter('wp_mail_content_type', array($this,'wpjobportal_set_html_content_type'));
        $body = wpjobportalphplib::wpJP_preg_replace('/\r?\n|\r/', '<br/>', $body);
        $body = wpjobportalphplib::wpJP_str_replace(array("\r\n", "\r", "\n"), "<br/>", $body);
        $body = nl2br($body);
        wp_mail($recevierEmail, $wpjobportal_subject, $body, $headers, $attachments);
    }

    function getRecordByTablenameAndId($wpjobportal_tablename, $wpjobportal_id,$wpjobportal_actionid = null) {
        if (!is_numeric($wpjobportal_id))
            return false;
        $query = null;
        switch ($wpjobportal_tablename) {
            case 'wj_portal_companies':

                $query = 'SELECT company.name AS companyname,CONCAT(user.first_name," ",user.last_name) AS username,user.emailaddress AS useremail
                            , company.status AS status,company.contactemail AS companyuseremail,company.isfeaturedcompany AS featuredcompany
                            FROM `' . wpjobportal::$_db->prefix . 'wj_portal_companies` AS company
                            LEFT JOIN `' . wpjobportal::$_db->prefix . 'wj_portal_users` AS user ON user.id = company.uid
                            WHERE company.id = ' . esc_sql($wpjobportal_id);
                break;
            case 'wj_portal_jobs':
                $wpjobportal_decisionalquery = 'SELECT uid FROM `' . wpjobportal::$_db->prefix . 'wj_portal_jobs` AS job WHERE id=' . esc_sql($wpjobportal_id);
                $wpjobportal_decisionalrecord = wpjobportal::$_db->get_row($wpjobportal_decisionalquery);
                //query for get visitor jobs
                if ($wpjobportal_decisionalrecord->uid == 0) {
                    $query = 'SELECT job.title AS jobtitle,company.name AS companyname,job.status AS status
                                ,company.contactemail AS useremail,company.uid, job.isfeaturedjob AS featuredjob,job.params
                            FROM `' . wpjobportal::$_db->prefix . 'wj_portal_jobs` AS job
                            '.wpjobportal::$_company_job_table_join.' JOIN `' . wpjobportal::$_db->prefix . 'wj_portal_companies` AS company ON job.companyid = company.id
                            WHERE job.id = ' . esc_sql($wpjobportal_id);
                }
                //query for get jobs
                else {
                    $query = 'SELECT user.id AS id,job.title AS jobtitle,company.name AS companyname, CONCAT(user.first_name," ",user.last_name) AS username,job.status AS status
                    ,company.contactemail AS useremail ,company.uid, job.isfeaturedjob AS featuredjob,job.params
                            FROM `' . wpjobportal::$_db->prefix . 'wj_portal_jobs` AS job
                            '.wpjobportal::$_company_job_table_join.' JOIN `' . wpjobportal::$_db->prefix . 'wj_portal_companies` AS company ON job.companyid = company.id
                            JOIN `' . wpjobportal::$_db->prefix . 'wj_portal_users` AS user ON user.id = job.uid
                            WHERE job.id = ' . esc_sql($wpjobportal_id);
                }
                //echo $query;die;
                break;
            case 'wj_portal_resume':
                $wpjobportal_decisionalquery = 'SELECT uid FROM `' . wpjobportal::$_db->prefix . 'wj_portal_resume` AS rs WHERE id=' . esc_sql($wpjobportal_id);
                $wpjobportal_decisionalrecord = wpjobportal::$_db->get_row($wpjobportal_decisionalquery);
                if ($wpjobportal_decisionalrecord->uid == 0) {
                    //query for visitor resume
                    $query = 'SELECT rs.application_title AS resumetitle,rs.email_address AS useremail,rs.status AS resumestatus,  rs.first_name AS firstname, rs.last_name AS lastname,rs.uid, rs.isfeaturedresume AS featuredresume,rs.params
                            FROM `' . wpjobportal::$_db->prefix . 'wj_portal_resume` AS rs
                            WHERE rs.id = ' . esc_sql($wpjobportal_id);
                }
                //query for resume
                $query = 'SELECT rs.application_title AS resumetitle, CONCAT(user.first_name," ",user.last_name) AS username,rs.email_address AS useremailfromresume, rs.isfeaturedresume AS featuredresume,rs.params
                        ,rs.first_name AS firstname, rs.last_name AS lastname, rs.email_address AS useremail,rs.status AS resumestatus,rs.uid
                            FROM `' . wpjobportal::$_db->prefix . 'wj_portal_resume` AS rs
                            JOIN `' . wpjobportal::$_db->prefix . 'wj_portal_users` AS user ON user.id = rs.uid
                            WHERE rs.id = ' . esc_sql($wpjobportal_id);
                break;
            case 'users':
                $query = 'SELECT CONCAT(u.first_name," ",u.last_name) AS username, u.emailaddress AS useremail, u.roleid AS userrole
                            FROM `' . wpjobportal::$_db->prefix . 'wj_portal_users` AS u
                            WHERE u.id = ' . esc_sql($wpjobportal_id);
                break;
            case 'wj_portal_userpackages':
                $query = "SELECT package.title AS packagename,invoice.amount AS price,package.isfree,invoice.currencyid,upak.status,user.first_name as username,user.emailaddress AS useremailaddress,user.id AS userid
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_userpackages` AS upak
                JOIN `" . wpjobportal::$_db->prefix . "wj_portal_packages` AS package ON package.id = upak.packageid
                JOIN `" . wpjobportal::$_db->prefix . "wj_portal_users` AS user ON upak.uid = user.id
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_invoices` AS invoice ON invoice.recordid = upak.id
                WHERE upak.id = ".esc_sql($wpjobportal_id);
                break;
            case 'wj_portal_jobapply':
                $query = 'SELECT rs.first_name AS firstname,rs.last_name AS lastname, jobap.action_status AS resumestatus , jobap.status AS jobapplystatus,rs.email_address AS resumeemail,job.title AS jobtitle,com.contactemail AS companycontactemail,com.name AS companyname, rs.application_title AS resumetitle, CONCAT(uforemployer.first_name," ",uforemployer.last_name) AS username, uforemployer.emailaddress AS useremailforemployer,uforjobseeker.emailaddress AS useremailforjobseeker,job.params
                            FROM ' . wpjobportal::$_db->prefix . 'wj_portal_jobapply AS jobap
                            JOIN ' . wpjobportal::$_db->prefix . 'wj_portal_jobs AS job ON jobap.jobid = job.id
                            '.wpjobportal::$_company_job_table_join.' JOIN ' . wpjobportal::$_db->prefix . 'wj_portal_companies AS com ON job.companyid = com.id
                            JOIN ' . wpjobportal::$_db->prefix . 'wj_portal_resume AS rs ON rs.id = jobap.cvid
                            JOIN ' . wpjobportal::$_db->prefix . 'wj_portal_users AS uforemployer ON uforemployer.id = com.uid
                            JOIN ' . wpjobportal::$_db->prefix . 'wj_portal_users AS uforjobseeker ON uforjobseeker.id = jobap.uid
                            WHERE jobap.id =' . esc_sql($wpjobportal_id);
                break;
            case 'wj_portal_messages':
                $query = "SELECT message.message, message.sendby,message.created,message.jobseekerid
                        ,message.employerid,message.id,message.status,message.replytoid, message.jobid, message.resumeid
                        FROM `" . wpjobportal::$_db->prefix . "wj_portal_messages` AS message
                        WHERE message.id = " . esc_sql($wpjobportal_id);
                $wpjobportal_data = wpjobportaldb::get_row($query);

                if(!empty($wpjobportal_data)){
                    $wpjobportal_jobseeker_data = new stdClass();
                    if(isset($wpjobportal_data->jobseekerid) && $wpjobportal_data->jobseekerid > 0){
                        $query = 'SELECT CONCAT(u.first_name," ",u.last_name) AS username, u.emailaddress AS useremail, u.roleid AS userrole
                                    FROM `' . wpjobportal::$_db->prefix . 'wj_portal_users` AS u
                                    WHERE u.id = ' . esc_sql($wpjobportal_data->jobseekerid);
                                    $wpjobportal_jobseeker_data = wpjobportaldb::get_row($query);
                    }
                    $wpjobportal_employer_data = new stdClass();
                    if(isset($wpjobportal_data->employerid) && is_numeric($wpjobportal_data->employerid) && $wpjobportal_data->employerid > 0){
                        $query = 'SELECT CONCAT(u.first_name," ",u.last_name) AS username, u.emailaddress AS useremail, u.roleid AS userrole
                                    FROM `' . wpjobportal::$_db->prefix . 'wj_portal_users` AS u
                                    WHERE u.id = ' . esc_sql($wpjobportal_data->employerid);
                                    $wpjobportal_employer_data = wpjobportaldb::get_row($query);
                    }
                    $wpjobportal_data->jobseeker_data = $wpjobportal_jobseeker_data;
                    $wpjobportal_data->employer_data = $wpjobportal_employer_data;

                }
                return $wpjobportal_data;

                break;
        }
        if ($query != null) {
            $record = wpjobportal::$_db->get_row($query);
            return $record;
        }
        return false;
    }
    function getMessagekey(){
        $wpjobportal_key = 'emailtemplate';if(wpjobportal::$_common->wpjp_isadmin()){$wpjobportal_key = 'admin_'.$wpjobportal_key;}return $wpjobportal_key;
    }


}

?>
