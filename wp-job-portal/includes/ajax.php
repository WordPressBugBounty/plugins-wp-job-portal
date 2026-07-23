<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALajax {

    function __construct() {
        add_action("wp_ajax_wpjobportal_ajax", array($this, "ajaxhandler")); // when user is login
        add_action("wp_ajax_nopriv_wpjobportal_ajax", array($this, "ajaxhandler")); // when user is not login
        add_action("wp_ajax_wpjobportal_ajax_popup", array($this, "ajaxhandlerpopup")); // when user is login
        add_action("wp_ajax_nopriv_wpjobportal_ajax_popup", array($this, "ajaxhandlerpopup")); // when user is not login
        add_action("wp_ajax_wpjobportal_ajax_popup_action", array($this, "ajaxhandlerpopupaction")); // when user is login
        add_action("wp_ajax_nopriv_wpjobportal_ajax_popup_action", array($this, "ajaxhandlerpopupaction")); // when user is not login
        add_action("wp_ajax_wpjobportal_loginwith_ajax", array($this, "ajaxhandlerloginwith")); // when user is login
        add_action("wp_ajax_nopriv_wpjobportal_loginwith_ajax", array($this, "ajaxhandlerloginwith")); // when user is not login
    }

    function ajaxhandler() {
        $fucntin_allowed = array('DataForDepandantFieldResume', 'DataForDepandantField', 'saveJobShortlist', 'saveJobShortlistJobManager',
                                'getQuickViewByJobId', 'getShortListViewByJobId', 'getShortListViewByJobIdJobPortal', 'getApplyNowByJobid',
                                'jobapply', 'jobapplyjobmanager', 'getTellaFriend', 'deletecompanylogo', 'deleteResumeLogo',
                                'getuserlistajax', 'getLogForUserById', 'getFieldsForComboByFieldFor', 'getSectionToFillValues', 'getUserIdByCompanyid',
                                'changeNotifyOfNotifications', 'changeViewOfNotifications', 'getOptionsForFieldEdit', 'listdepartments',
                                'saveTokenInputTag', 'makeJobCopyAjax', 'getsubcategorypopup', 'updateJobApplyResumeStatus', 'getResumeCommentSection',
                                'getFolderSection', 'saveToFolderResume', 'storeResumeComments', 'setResumeRatting', 'getResumeDetail', 'getEmailFields',
                                'jobapplyid', 'getFolderSection', 'getFolderSectionJobManager', 'saveToFolderResume', 'sendEmailToJobSeeker',
                                'getResumeDetailJobManager', 'getEmailFieldsJobManager', 'hideTemplateBanner', 'getListTranslations',
                                'validateandshowdownloadfilename', 'getlanguagetranslation', 'getPacakageListByUid', 'canceljobapplyasvisitor',
                                'visitorapplyjob', 'removeResumeFileById', 'getResumeSectionAjax', 'deleteResumeSectionAjax', 'getOptionsForEditSlug',
                                'getAllRoleLessUsersAjax', 'savetokeninputcity','sendmessageresume', 'sendmailtofriend',
                                'getResumeCommentSectionJobManager',
                                'getPaymentPopup','getPackagePopupForFeaturedCompany','getPackagePopupForFeaturedJob','getPackagePopupForFeaturedResume',
                                'getPackagePopupForJobAlert','getPackagePopupJobView','getPackagePopupForCopyJob','getPackagePopupForCompanyContactDetail',
                                'getPackagePopupForResumeContactDetail','gettagsbytagname','listDepartments','deleteUserPhoto',
                                'getStripePlans','downloadandinstalladdonfromAjax','getChildForVisibleCombobox','isFieldRequired','getFieldsForComboBySection',
                                'getUserRoleBasedInfo','storeConfigurationSingle','importZywrapData','checkZywrapApiKey','importZywrapBatchProcess',
                                'getWrappersByCategory','executeZywrapProxy','getZywrapAllWrappers','getSchemaByUseCode','getAjaxJobs', 'executeCompanyCopilot',
                                'executeJobCopilot', 'executeResumeCopilot', 'executeCoverLetterCopilot');
        $wpjobportal_task = preg_replace('/[^A-Za-z0-9_]/', '', (string) WPJOBPORTALrequest::getVar('task'));
        if($wpjobportal_task != '' && in_array($wpjobportal_task, $fucntin_allowed, true)){
            $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme');

            // $wpjobportal_module = str_replace("..","",$wpjobportal_module);
			// $wpjobportal_module = str_replace("/","",$wpjobportal_module);
            $wpjobportal_module = sanitize_key( $wpjobportal_module );
            if (empty($wpjobportal_module)) {
                die('Not Allowed!');
            }
            $wpjobportal_model_path = WPJOBPORTALincluder::getPluginPath($wpjobportal_module, 'model');
            if (empty($wpjobportal_model_path) || !file_exists($wpjobportal_model_path)) {
                die('Not Allowed!');
            }
            $wpjobportal_model = WPJOBPORTALincluder::getJSModel($wpjobportal_module);
            if (!is_object($wpjobportal_model) || !method_exists($wpjobportal_model, $wpjobportal_task) || !is_callable(array($wpjobportal_model, $wpjobportal_task))) {
                die('Not Allowed!');
            }
            $wpjobportal_reflection = new ReflectionMethod($wpjobportal_model, $wpjobportal_task);
            if ($wpjobportal_reflection->getNumberOfRequiredParameters() > 0) {
                die('Not Allowed!');
            }
            $wpjobportal_result = $wpjobportal_model->$wpjobportal_task();
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Intentional raw response (AJAX / HTML)
            echo $wpjobportal_result;
            die();
        }else{
            die('Not Allowed!');
        }
    }

    function ajaxhandlerpopup() {
        $wpjobportal_task = preg_replace('/[^A-Za-z0-9_]/', '', (string) WPJOBPORTALrequest::getVar('task'));
        $wpjobportal_allowed = array('featured_company','featured_job','featured_resume','view_company_contact_detail','view_resume_contact_detail','resume_save_search','add_department','add_job','copy_job','add_company','add_resume','add_job_alert','job_apply');
        if (!in_array($wpjobportal_task, $wpjobportal_allowed, true)) {
            die('Not Allowed!');
        }
        $wpjobportal_result = WPJOBPORTALincluder::getObjectClass('usercredits')->getUserCreditsDetailForAction($wpjobportal_task);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Intentional raw response (AJAX / HTML)
        echo $wpjobportal_result;
        die();
    }

    function ajaxhandlerpopupaction() {
        $wpjobportal_task = preg_replace('/[^A-Za-z0-9_]/', '', (string) WPJOBPORTALrequest::getVar('task'));
        $wpjobportal_allowed = array('featured_company','featured_job','featured_resume','copy_job');
        if (!in_array($wpjobportal_task, $wpjobportal_allowed, true)) {
            die('Not Allowed!');
        }
        $wpjobportal_result = WPJOBPORTALincluder::getObjectClass('usercredits')->doAction($wpjobportal_task);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Intentional raw response (AJAX / HTML)
        echo $wpjobportal_result;
        die();
    }

    function ajaxhandlerloginwith() {
        $wpjobportal_socialmedia = sanitize_key(WPJOBPORTALrequest::getVar('socialmedia'));
        $wpjobportal_task = preg_replace('/[^A-Za-z0-9_]/', '', (string) WPJOBPORTALrequest::getVar('task'));
        $wpjobportal_allowed_tasks = array(
            'facebook' => array('login', 'logout', 'applywithfacebook'),
            'linkedin' => array('login', 'logout', 'applywithlinkedin'),
            'xing' => array('login', 'logout', 'applywithxing'),
        );
        if (!isset($wpjobportal_allowed_tasks[$wpjobportal_socialmedia]) || !in_array($wpjobportal_task, $wpjobportal_allowed_tasks[$wpjobportal_socialmedia], true)) {
            die('Not Allowed!');
        }
        $wpjobportal_result = '';
        switch ($wpjobportal_socialmedia) {
            case 'facebook':
                $wpjobportal_object = WPJOBPORTALincluder::getObjectClass('facebook');
                break;
            case 'linkedin':
                $wpjobportal_object = WPJOBPORTALincluder::getObjectClass('linkedin');
                break;
            case 'xing':
                $wpjobportal_object = WPJOBPORTALincluder::getObjectClass('xing');
                break;
        }
        if (!isset($wpjobportal_object) || !method_exists($wpjobportal_object, $wpjobportal_task) || !is_callable(array($wpjobportal_object, $wpjobportal_task))) {
            die('Not Allowed!');
        }
        $wpjobportal_reflection = new ReflectionMethod($wpjobportal_object, $wpjobportal_task);
        if ($wpjobportal_reflection->getNumberOfRequiredParameters() > 0) {
            die('Not Allowed!');
        }
        $wpjobportal_result = $wpjobportal_object->$wpjobportal_task();
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Intentional raw response (AJAX / HTML)
        echo $wpjobportal_result;
        die();
    }

}

$wpjobportal_jsajax = new WPJOBPORTALajax();
?>
