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
        $fucntin_allowed = array('DataForDepandantFieldResume', 'DataForDepandantField', 'saveJobShortlist', 'saveJobShortlistJobManager', 'getQuickViewByJobId', 'getShortListViewByJobId', 'getShortListViewByJobIdJobPortal', 'getApplyNowByJobid', 'jobapply', 'jobapplyjobmanager', 'getTellaFriend', 'getTellaFriendJobManager', 'deletecompanylogo', 'deleteResumeLogo', 'getuserlistajax', 'getLogForUserById', 'getFieldsForComboByFieldFor', 'getSectionToFillValues', 'getUserIdByCompanyid', 'changeNotifyOfNotifications', 'changeViewOfNotifications', 'getOptionsForFieldEdit', 'listdepartments', 'saveTokenInputTag', 'makeJobCopyAjax', 'getsubcategorypopup', 'updateJobApplyResumeStatus', 'getResumeCommentSection', 'getFolderSection', 'saveToFolderResume', 'storeResumeComments', 'setResumeRatting', 'getResumeDetail', 'getEmailFields', 'jobapplyid', 'getFolderSection', 'getFolderSectionJobManager', 'saveToFolderResume', 'sendEmailToJobSeeker', 'setJobApplyRating', 'getResumeDetailJobManager', 'getEmailFieldsJobManager', 'hideTemplateBanner', 'getListTranslations', 'validateandshowdownloadfilename', 'getlanguagetranslation', 'getPacakageListByUid', 'canceljobapplyasvisitor', 'visitorapplyjob', 'removeResumeFileById', 'getResumeSectionAjax', 'deleteResumeSectionAjax', 'getOptionsForEditSlug', 'getAllRoleLessUsersAjax', 'getNextJobs', 'getNextTemplateJobs','savetokeninputcity','sendmessageresume', 'sendmailtofriend', 'getJobApplyDetailByid', 'setListStyleSession','sendmailtofriendJobManager', 'getResumeCommentSectionJobManager','getPaymentPopup','getPackagePopupForFeaturedCompany','getPackagePopupForFeaturedJob','getPackagePopupForFeaturedResume','getPackagePopupForJobAlert','getPackagePopupJobView','getPackagePopupForCopyJob','getPackagePopupForCompanyContactDetail','getPackagePopupForResumeContactDetail','gettagsbytagname','listDepartments','getPackagePopupForDepartment','deleteUserPhoto','getStripePlans','downloadandinstalladdonfromAjax','getChildForVisibleCombobox','isFieldRequired','getFieldsForComboBySection','getUserRoleBasedInfo','storeConfigurationSingle');
        $wpjobportal_task = WPJOBPORTALrequest::getVar('task');
        if($wpjobportal_task != '' && in_array($wpjobportal_task, $fucntin_allowed)){
            $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme');

            // $wpjobportal_module = str_replace("..","",$wpjobportal_module);
			// $wpjobportal_module = str_replace("/","",$wpjobportal_module);
            $wpjobportal_module = sanitize_key( $wpjobportal_module );

            $wpjobportal_result = WPJOBPORTALincluder::getJSModel($wpjobportal_module)->$wpjobportal_task();
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Intentional raw response (AJAX / HTML)
            echo $wpjobportal_result;
            die();
        }else{
            die('Not Allowed!');
        }
    }

    function ajaxhandlerpopup() {
        $wpjobportal_task = WPJOBPORTALrequest::getVar('task');
        $wpjobportal_result = WPJOBPORTALincluder::getObjectClass('usercredits')->getUserCreditsDetailForAction($wpjobportal_task);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Intentional raw response (AJAX / HTML)
        echo $wpjobportal_result;
        die();
    }

    function ajaxhandlerpopupaction() {
        $wpjobportal_task = WPJOBPORTALrequest::getVar('task');
        $wpjobportal_result = WPJOBPORTALincluder::getObjectClass('usercredits')->doAction($wpjobportal_task);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Intentional raw response (AJAX / HTML)
        echo $wpjobportal_result;
        die();
    }

    function ajaxhandlerloginwith() {
        $wpjobportal_socialmedia = WPJOBPORTALrequest::getVar('socialmedia');
        $wpjobportal_task = WPJOBPORTALrequest::getVar('task');
        switch ($wpjobportal_socialmedia) {
            case 'facebook':
                $wpjobportal_result = WPJOBPORTALincluder::getObjectClass('facebook')->$wpjobportal_task();
                break;
            case 'linkedin':
                $wpjobportal_result = WPJOBPORTALincluder::getObjectClass('linkedin')->$wpjobportal_task();
                break;
            case 'xing':
                $wpjobportal_result = WPJOBPORTALincluder::getObjectClass('xing')->$wpjobportal_task();
                break;
        }
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Intentional raw response (AJAX / HTML)
        echo $wpjobportal_result;
        die();
    }

}

$wpjobportal_jsajax = new WPJOBPORTALajax();
?>
