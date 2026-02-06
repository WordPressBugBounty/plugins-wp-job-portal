<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALemailtemplatestatusModel {

    function sendEmailModel($wpjobportal_id, $wpjobportal_actionfor) {
        if (empty($wpjobportal_id))
            return false;
        if (!is_numeric($wpjobportal_actionfor))
            return false;

        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('emailtemplateconfig');
        $wpjobportal_value = 1;

        switch ($wpjobportal_actionfor) {
            case 1: //updation for employer send email
                $wpjobportal_row->update(array('id' => $wpjobportal_id, 'employer' => $wpjobportal_value));
                break;
            case 2: //updation for jobseeker send email
                $wpjobportal_row->update(array('id' => $wpjobportal_id, 'jobseeker' => $wpjobportal_value));

                break;
            case 3: //updation for admin send email
                $wpjobportal_row->update(array('id' => $wpjobportal_id, 'admin' => $wpjobportal_value));
                break;
            case 4: //updation for jobseeker visitor send email
                $wpjobportal_row->update(array('id' => $wpjobportal_id, 'jobseeker_visitor' => $wpjobportal_value));
                break;
            case 5: //updation for employer visitor send email
                $wpjobportal_row->update(array('id' => $wpjobportal_id, 'employer_visitor' => $wpjobportal_value));
        }
    }

    function noSendEmailModel($wpjobportal_id, $wpjobportal_actionfor) {
        if (empty($wpjobportal_id))
            return false;
        if (!is_numeric($wpjobportal_actionfor))
            return false;

        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('emailtemplateconfig');
        $wpjobportal_value = 0;

        switch ($wpjobportal_actionfor) {
            case 1: //updation for employer not send email
                $wpjobportal_row->update(array('id' => $wpjobportal_id, 'employer' => $wpjobportal_value));
                break;
            case 2: //updation for jobseeker not send email
                $wpjobportal_row->update(array('id' => $wpjobportal_id, 'jobseeker' => $wpjobportal_value));
                break;
            case 3: //updation for admin not send email
                $wpjobportal_row->update(array('id' => $wpjobportal_id, 'admin' => $wpjobportal_value));
                break;
            case 4: //updation for jobseeker visitor not send email
                $wpjobportal_row->update(array('id' => $wpjobportal_id, 'jobseeker_visitor' => $wpjobportal_value));
                break;
            case 5: //updation for employer visitor not send email
                $wpjobportal_row->update(array('id' => $wpjobportal_id, 'employer_visitor' => $wpjobportal_value));
        }
    }

    function getLanguageForEmail($wpjobportal_keyword) {
        switch ($wpjobportal_keyword) {
            case 'add_new_company':
                $lanng = esc_html(__('Add','wp-job-portal')). esc_html(__('new','wp-job-portal')).esc_html(__('company', 'wp-job-portal'));
                return $lanng;
                break;
            case 'delete_company':
                $lanng = esc_html(__('Delete','wp-job-portal')) .' '. esc_html(__('company', 'wp-job-portal'));
                return $lanng;
                break;
            case 'company_status':
                $lanng = esc_html(__('Company','wp-job-portal')) .' '. esc_html(__('status', 'wp-job-portal'));
                return $lanng;
                break;
            case 'job_status':
                $lanng = esc_html(__('Job','wp-job-portal')) .' '. esc_html(__('Status', 'wp-job-portal'));
                return $lanng;
                break;
            case 'add_new_job':
                $lanng = esc_html(__('Add','wp-job-portal')) .' '. esc_html(__('new','wp-job-portal')) .' '. esc_html(__('job', 'wp-job-portal'));
                return $lanng;
                break;
            case 'add_new_resume':
                $lanng = esc_html(__('Add','wp-job-portal')) .' '. esc_html(__('new','wp-job-portal')) .' '. esc_html(__('resume', 'wp-job-portal'));
                return $lanng;
                break;
            case 'resume_status':
                $lanng = esc_html(__('Resume','wp-job-portal')) .' '. esc_html(__('status', 'wp-job-portal'));
                return $lanng;
                break;
            case 'employer_purchase_credits_pack':
                $lanng = esc_html(__('Employer','wp-job-portal')) .' '. esc_html(__('buy credits pack', 'wp-job-portal'));
                return $lanng;
                break;
            case 'jobseeker_package_expire':
                $lanng = esc_html(__('Job seeker','wp-job-portal')) .' '. esc_html(__('expire package', 'wp-job-portal'));
                return $lanng;
                break;
            case 'jobseeker_purchase_credits_pack':
                $lanng = esc_html(__('Job seeker','wp-job-portal')) .' '. esc_html(__('buy credits pack', 'wp-job-portal'));
                return $lanng;
                break;
            case 'employer_package_expire':
                $lanng = esc_html(__('Employer','wp-job-portal')) .' '. esc_html(__('expire package', 'wp-job-portal'));
                return $lanng;
                break;
            case 'jobapply_employer':
                $lanng = esc_html(__('Employer','wp-job-portal')) .' '. esc_html(__('job apply', 'wp-job-portal'));
                return $lanng;
                break;
            case 'jobapply_jobseeker':
                $lanng = esc_html(__('Job seeker','wp-job-portal')) .' '. esc_html(__('job apply', 'wp-job-portal'));
                return $lanng;
                break;
            case 'delete_job':
                $lanng = esc_html(__('Delete','wp-job-portal')) .' '. esc_html(__('job', 'wp-job-portal'));
                return $lanng;
                break;
            case 'add_new_employer':
                $lanng = esc_html(__('Add','wp-job-portal')) .' '. esc_html(__('New','wp-job-portal')) .' '. esc_html(__('Employer', 'wp-job-portal'));
                return $lanng;
                break;
            case 'add_new_jobseeker':
                $lanng = esc_html(__('Add','wp-job-portal')) .' '. esc_html(__('New','wp-job-portal')) .' '. esc_html(__('Job Seeker', 'wp-job-portal'));
                return $lanng;
                break;
            case 'add_new_resume_visitor':
                $lanng = esc_html(__('Add','wp-job-portal')) .' '. esc_html(__('new','wp-job-portal')) .' '. esc_html(__('resume ','wp-job-portal')) .' '. esc_html(__('by visitor', 'wp-job-portal'));
                return $lanng;
                break;
            case 'add_new_job_visitor':
                $lanng = esc_html(__('Add','wp-job-portal')) .' '. esc_html(__('new','wp-job-portal')) .' '. esc_html(__('job','wp-job-portal')) .' '. esc_html(__('by visitor', 'wp-job-portal'));
                return $lanng;
                break;
            case 'resume-delete':
                $lanng = esc_html(__('Delete','wp-job-portal')) .' '. esc_html(__('resume', 'wp-job-portal'));
                return $lanng;
                break;
            case 'jobapply_jobapply':
                $lanng = esc_html(__('job apply', 'wp-job-portal'));
                return $lanng;
                break;
            case 'applied-resume_status':
                $lanng = esc_html(__('Applied resume status change', 'wp-job-portal'));
                return $lanng;
                break;
            case 'package-purchase-admin':
                $lanng = esc_html(__('Package Purchase Admin', 'wp-job-portal'));
                return $lanng;
                break;
            case 'package_status':
                $lanng = esc_html(__('Package Status', 'wp-job-portal'));
                return $lanng;
                break;
            case 'package_purchase':
                $lanng = esc_html(__('Package Purchase', 'wp-job-portal'));
                return $lanng;
                break;
            case 'new_message':
                $lanng = esc_html(__('New Message', 'wp-job-portal'));
                return $lanng;
                break;
        }
    }

    function getEmailTemplateStatusData() {
        $query = "SELECT * FROM " . wpjobportal::$_db->prefix . "wj_portal_emailtemplates_config";
        wpjobportal::$_data[0] = wpjobportaldb::get_results($query);
        $wpjobportal_newdata = array();
        foreach (wpjobportal::$_data[0] as $wpjobportal_data) {
            $wpjobportal_newdata[$wpjobportal_data->emailfor] = array(
                'tempid' => $wpjobportal_data->id,
                'tempname' => $wpjobportal_data->emailfor,
                'admin' => $wpjobportal_data->admin,
                'employer' => $wpjobportal_data->employer,
                'jobseeker' => $wpjobportal_data->jobseeker,
                'jobseeker_vis' => $wpjobportal_data->jobseeker_visitor,
                'employer_vis' => $wpjobportal_data->employer_visitor
            );
        }
        wpjobportal::$_data[0] = $wpjobportal_newdata;
    }

    function getEmailTemplateStatus($wpjobportal_template_name) {
        $query = "SELECT emc.admin,emc.employer,emc.jobseeker,emc.employer_visitor,emc.jobseeker_visitor
                FROM " . wpjobportal::$_db->prefix . "wj_portal_emailtemplates_config AS emc
                where  emc.emailfor = '" . esc_sql($wpjobportal_template_name) . "'";
        $wpjobportal_templatestatus = wpjobportaldb::get_row($query);
        return $wpjobportal_templatestatus;
    }
    function getMessagekey(){
        $wpjobportal_key = 'emailtemplatestatus';if(wpjobportal::$_common->wpjp_isadmin()){$wpjobportal_key = 'admin_'.$wpjobportal_key;}return $wpjobportal_key;
    }


}

?>
