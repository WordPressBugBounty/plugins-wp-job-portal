<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALMessages {
    /*
     * setLayoutMessage
     * @params $message = Your message to display
     * @params $type = Messages types => 'updated','error','update-nag'
     */

    public static $wpjobportal_counter;

    public static function setLayoutMessage($message, $type, $wpjobportal_msgkey) {
        WPJOBPORTALincluder::getObjectClass('wpjpnotification')->addSessionNotificationDataToTable($message,$type,'notification',$wpjobportal_msgkey);
    }

    public static function getLayoutMessage($wpjobportal_msgkey) {
        $frontend = (is_admin()) ? '' : 'frontend';
        $wpjobportal_divHtml = '';
        $wpjobportal_notificationdata = WPJOBPORTALincluder::getObjectClass('wpjpnotification')->getNotificationDatabySessionId('notification',$wpjobportal_msgkey,true);
        if (isset($wpjobportal_notificationdata['msg'][0]) && isset($wpjobportal_notificationdata['type'][0])) {
            for ($wpjobportal_i = 0; $wpjobportal_i < COUNT($wpjobportal_notificationdata['msg']); $wpjobportal_i++){
                if (isset($wpjobportal_notificationdata['msg'][$wpjobportal_i]) && isset($wpjobportal_notificationdata['type'][$wpjobportal_i])) {
                    if(is_admin()){
                        $wpjobportal_divHtml .= '<div class="frontend ' . $wpjobportal_notificationdata['type'][$wpjobportal_i] . '"><p>' . $wpjobportal_notificationdata['msg'][$wpjobportal_i] . '</p></div>';
                    }else{
                        if(wpjobportal::$wpjobportal_theme_chk != 0){
                            if($wpjobportal_notificationdata['type'][$wpjobportal_i] == 'updated'){
                                $alert_class = 'success';
                                $wpjobportal_img_name = 'job-alert-successful.png';
                            }elseif($wpjobportal_notificationdata['type'][$wpjobportal_i] == 'saved'){
                                $alert_class = 'success';
                                $wpjobportal_img_name = 'job-alert-successful.png';
                            }elseif($wpjobportal_notificationdata['type'][$wpjobportal_i] == 'saved'){
                                        //$alert_class = 'info';
                                        //$alert_class = 'warning';
                            }elseif($wpjobportal_notificationdata['type'][$wpjobportal_i] == 'error'){
                                $alert_class = 'danger';
                                $wpjobportal_img_name = 'job-alert-unsuccessful.png';
                            }
                            $wpjobportal_divHtml .= '<div class="alert alert-' . $alert_class . '" role="alert" id="autohidealert">
                                            <img class="leftimg" src="'.esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/'.$wpjobportal_img_name.'" />
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                            '. $wpjobportal_notificationdata['msg'][$wpjobportal_i] . '
                                        </div>';
                         }else{
                             $wpjobportal_pu = "";
                             if($wpjobportal_notificationdata['type'][$wpjobportal_i] == 'updated'){
                                 $wpjobportal_pu = 'published';
                             }
                            $wpjobportal_divHtml .= '<div class="'. $frontend." ".$wpjobportal_notificationdata['type'][$wpjobportal_i]." ".$wpjobportal_pu.'"><p>' . $wpjobportal_notificationdata['msg'][$wpjobportal_i] . '</p></div>';
                          
                            
                        }
                    }
                }
            }
        }

	    echo wp_kses($wpjobportal_divHtml, WPJOBPORTAL_ALLOWED_TAGS);
    }

    public static function getMSelectionEMessage() { // multi selection error message
        return esc_html(__('Please first make a selection from the list', 'wp-job-portal'));
    }

    public static function getMessage($wpjobportal_result, $wpjobportal_entity) {
       $wpjobportal_msg['message'] = esc_html(__('Unknown', 'wp-job-portal'));
        $wpjobportal_msg['status'] = "updated";
        $wpjobportal_msg1 = WPJOBPORTALMessages::getEntityName($wpjobportal_entity);

        switch ($wpjobportal_result) {
            case WPJOBPORTAL_INVALID_REQUEST:
                $wpjobportal_msg['message'] = esc_html(__('Invalid request', 'wp-job-portal'));
                $wpjobportal_msg['status'] = 'error';
                break;
            case WPJOBPORTAL_SAVED:
                $wpjobportal_msg2 = esc_html(__('has been successfully saved', 'wp-job-portal'));
                if ($wpjobportal_msg1)
                    $wpjobportal_msg['message'] = $wpjobportal_msg1 . ' ' . $wpjobportal_msg2;
                break;
            case WPJOBPORTAL_SAVE_ERROR:
                $wpjobportal_msg['status'] = "error";
                $wpjobportal_msg2 = esc_html(__('has not been saved', 'wp-job-portal'));
                if ($wpjobportal_msg1)
                    $wpjobportal_msg['message'] = $wpjobportal_msg1 . ' ' . $wpjobportal_msg2;
                break;
            case WPJOBPORTAL_DELETED:
                $wpjobportal_msg2 = esc_html(__('has been successfully deleted', 'wp-job-portal'));
                if ($wpjobportal_msg1)
                    $wpjobportal_msg['message'] = $wpjobportal_msg1 . ' ' . $wpjobportal_msg2;
                break;
            case WPJOBPORTAL_NOT_EXIST:
                $wpjobportal_msg['status'] = "error";
                $wpjobportal_msg['message'] = esc_html(__('Record not exist', 'wp-job-portal'));
                break;
            case WPJOBPORTAL_DELETE_ERROR:
                $wpjobportal_msg['status'] = "error";
                $wpjobportal_msg2 = esc_html(__('has not been deleted', 'wp-job-portal'));
                if ($wpjobportal_msg1) {
                    $wpjobportal_msg['message'] = $wpjobportal_msg1 . ' ' . $wpjobportal_msg2;
                    if (WPJOBPORTALMessages::$wpjobportal_counter) {
                        if(WPJOBPORTALMessages::$wpjobportal_counter > 1){
                            $wpjobportal_msg['message'] = WPJOBPORTALMessages::$wpjobportal_counter . ' ' . $wpjobportal_msg['message'];
                        }
                    }
                }
                break;
            case WPJOBPORTAL_PUBLISHED:
                $wpjobportal_msg2 = esc_html(__('has been successfully published', 'wp-job-portal'));
                if ($wpjobportal_msg1) {
                    $wpjobportal_msg['message'] = $wpjobportal_msg1 . ' ' . $wpjobportal_msg2;
                    if (WPJOBPORTALMessages::$wpjobportal_counter) {
                        if(WPJOBPORTALMessages::$wpjobportal_counter > 1){
                            $wpjobportal_msg['message'] = WPJOBPORTALMessages::$wpjobportal_counter . ' ' . $wpjobportal_msg['message'];
                        }
                    }
                }
                break;
            case WPJOBPORTAL_VERIFIED:
                $wpjobportal_msg['message'] = esc_html(__('transaction has been successfully verified', 'wp-job-portal'));
                break;
            case WPJOBPORTAL_UN_VERIFIED:
                $wpjobportal_msg['message'] = esc_html(__('transaction has been successfully un-verified', 'wp-job-portal'));
                break;
            case WPJOBPORTAL_VERIFIED_ERROR:
                $wpjobportal_msg['message'] = esc_html(__('transaction has not been successfully verified', 'wp-job-portal'));
                break;
            case WPJOBPORTAL_NOTENOUGHCREDITS:
                $this->notEnoughCredits();
                break;
            case WPJOBPORTAL_UN_VERIFIED_ERROR:
                $wpjobportal_msg['message'] = esc_html(__('transaction has not been successfully un-verified', 'wp-job-portal'));
                break;
            case WPJOBPORTAL_PUBLISH_ERROR:
                $wpjobportal_msg['status'] = "error";
                $wpjobportal_msg2 = esc_html(__('has not been published', 'wp-job-portal'));
                if ($wpjobportal_msg1) {
                    $wpjobportal_msg['message'] = $wpjobportal_msg1 . ' ' . $wpjobportal_msg2;
                    if (WPJOBPORTALMessages::$wpjobportal_counter) {
                            $wpjobportal_msg['message'] = WPJOBPORTALMessages::$wpjobportal_counter . ' ' . $wpjobportal_msg['message'];
                    }
                }
                break;
            case WPJOBPORTAL_UN_PUBLISHED:
                $wpjobportal_msg2 = esc_html(__('has been successfully unpublished', 'wp-job-portal'));
                if ($wpjobportal_msg1) {
                    $wpjobportal_msg['message'] = $wpjobportal_msg1 . ' ' . $wpjobportal_msg2;
                    if (WPJOBPORTALMessages::$wpjobportal_counter) {
                        if(WPJOBPORTemALMessages::$wpjobportal_counter > 1){
                            $wpjobportal_msg['message'] = WPJOBPORTALMessages::$wpjobportal_counter . ' ' . $wpjobportal_msg['message'];
                        }
                    }
                }
                break;
            case WPJOBPORTAL_UN_PUBLISH_ERROR:
                $wpjobportal_msg['status'] = "error";
                $wpjobportal_msg2 = esc_html(__('has not been unpublished', 'wp-job-portal'));
                if ($wpjobportal_msg1) {
                    $wpjobportal_msg['message'] = $wpjobportal_msg1 . ' ' . $wpjobportal_msg2;
                    if (WPJOBPORTALMessages::$wpjobportal_counter) {
                            $wpjobportal_msg['message'] = WPJOBPORTALMessages::$wpjobportal_counter . ' ' . $wpjobportal_msg['message'];
                    }
                }
                break;
            case WPJOBPORTAL_REQUIRED:
                $wpjobportal_msg['message'] = esc_html(__('Fields has been successfully required', 'wp-job-portal'));
                break;
            case WPJOBPORTAL_REQUIRED_ERROR:
                $wpjobportal_msg['status'] = "error";
                if (WPJOBPORTALMessages::$wpjobportal_counter) {
                    if (WPJOBPORTALMessages::$wpjobportal_counter == 1)
                        $wpjobportal_msg['message'] = WPJOBPORTALMessages::$wpjobportal_counter . ' ' . esc_html(__('Field has not been required', 'wp-job-portal'));
                    else
                        $wpjobportal_msg['message'] = WPJOBPORTALMessages::$wpjobportal_counter . ' ' . esc_html(__('Fields has not been required', 'wp-job-portal'));
                }else {
                    $wpjobportal_msg['message'] = esc_html(__('Field has not been required', 'wp-job-portal'));
                }
                break;
            case WPJOBPORTAL_NOT_REQUIRED:
                $wpjobportal_msg['message'] = esc_html(__('Fields has been successfully not required', 'wp-job-portal'));
                break;
            case WPJOBPORTAL_NOT_REQUIRED_ERROR:
                $wpjobportal_msg['status'] = "error";
                if (WPJOBPORTALMessages::$wpjobportal_counter) {
                    if (WPJOBPORTALMessages::$wpjobportal_counter == 1)
                        $wpjobportal_msg['message'] = WPJOBPORTALMessages::$wpjobportal_counter . ' ' . esc_html(__('Field has not been not required', 'wp-job-portal'));
                    else
                        $wpjobportal_msg['message'] = WPJOBPORTALMessages::$wpjobportal_counter . ' ' . esc_html(__('Fields has not been not required', 'wp-job-portal'));
                }else {
                    $wpjobportal_msg['message'] = esc_html(__('Field has not been not required', 'wp-job-portal'));
                }
                break;
            case WPJOBPORTAL_ORDER_UP:
                $wpjobportal_msg['message'] = esc_html(__('Field order up successfully', 'wp-job-portal'));
                break;
            case WPJOBPORTAL_ORDER_UP_ERROR:
                $wpjobportal_msg['status'] = "error";
                $wpjobportal_msg['message'] = esc_html(__('Field order up error', 'wp-job-portal'));
                break;
            case WPJOBPORTAL_ORDER_DOWN:
                $wpjobportal_msg['message'] = esc_html(__('Field order down successfully', 'wp-job-portal'));
                break;
            case WPJOBPORTAL_ORDER_DOWN_ERROR:
                $wpjobportal_msg['status'] = "error";
                $wpjobportal_msg['message'] = esc_html(__('Field order up error', 'wp-job-portal'));
                break;
            case WPJOBPORTAL_REJECTED:
                $wpjobportal_msg2 = esc_html(__('has been rejected', 'wp-job-portal'));
                if ($wpjobportal_msg1)
                    $wpjobportal_msg['message'] = $wpjobportal_msg1 . ' ' . $wpjobportal_msg2;
                break;
            case WPJOBPORTAL_APPLY:
                $wpjobportal_msg['status'] = "updated";
                $wpjobportal_msg2 = esc_html(__('Job applied successfully', 'wp-job-portal'));
                $wpjobportal_msg['message'] = $wpjobportal_msg2;
                break;
            case WPJOBPORTAL_APPLY_ERROR:
                $wpjobportal_msg2 = esc_html(__('Error in applying job', 'wp-job-portal'));
                $wpjobportal_msg['message'] = $wpjobportal_msg2;
                $wpjobportal_msg['status'] = "error";
                break;
            case WPJOBPORTAL_REJECT_ERROR:
                $wpjobportal_msg['status'] = "error";
                $wpjobportal_msg2 = esc_html(__('has not been rejected', 'wp-job-portal'));
                if ($wpjobportal_msg1)
                    $wpjobportal_msg['message'] = $wpjobportal_msg1 . ' ' . $wpjobportal_msg2;
                break;
            case WPJOBPORTAL_APPROVED:
                $wpjobportal_msg2 = esc_html(__('has been approved', 'wp-job-portal'));
                if ($wpjobportal_msg1)
                    $wpjobportal_msg['message'] = $wpjobportal_msg1 . ' ' . $wpjobportal_msg2;
                break;
            case WPJOBPORTAL_APPROVE_ERROR:
                $wpjobportal_msg['status'] = "error";
                $wpjobportal_msg2 = esc_html(__('has not been approved', 'wp-job-portal'));
                if ($wpjobportal_msg1) {
                    $wpjobportal_msg['message'] = $wpjobportal_msg1 . ' ' . $wpjobportal_msg2;
                    if (WPJOBPORTALMessages::$wpjobportal_counter) {
                        $wpjobportal_msg['message'] = WPJOBPORTALMessages::$wpjobportal_counter . ' ' . $wpjobportal_msg['message'];
                    }
                }
                break;
            case WPJOBPORTAL_SET_DEFAULT:
                $wpjobportal_msg2 = esc_html(__('has been set as default', 'wp-job-portal'));
                if ($wpjobportal_msg1)
                    $wpjobportal_msg['message'] = $wpjobportal_msg1 . ' ' . $wpjobportal_msg2;
                break;
            case WPJOBPORTAL_UNPUBLISH_DEFAULT_ERROR:
                $wpjobportal_msg['status'] = "error";
                $wpjobportal_msg['message'] = esc_html(__('Unpublished field cannot set default', 'wp-job-portal'));
                break;
            case WPJOBPORTAL_SET_DEFAULT_ERROR:
                $wpjobportal_msg['status'] = "error";
                $wpjobportal_msg2 = esc_html(__('has not been set as default', 'wp-job-portal'));
                if ($wpjobportal_msg1)
                    $wpjobportal_msg['message'] = $wpjobportal_msg1 . ' ' . $wpjobportal_msg2;
                break;
            case WPJOBPORTAL_STATUS_CHANGED:
                $wpjobportal_msg2 = esc_html(__('status has been updated', 'wp-job-portal'));
                if ($wpjobportal_msg1)
                    $wpjobportal_msg['message'] = $wpjobportal_msg1 . ' ' . $wpjobportal_msg2;
                break;
            case WPJOBPORTAL_STATUS_CHANGED_ERROR:
                $wpjobportal_msg['status'] = "error";
                $wpjobportal_msg2 = esc_html(__('has not been updated', 'wp-job-portal'));
                if ($wpjobportal_msg1)
                    $wpjobportal_msg['message'] = $wpjobportal_msg1 . ' ' . $wpjobportal_msg2;
                break;
            case WPJOBPORTAL_IN_USE:
                $wpjobportal_msg['status'] = "error";
                $wpjobportal_msg2 = esc_html(__('is in use', 'wp-job-portal'));
                $wpjobportal_msg3 = esc_html(__('can not deleted it', 'wp-job-portal'));
                if ($wpjobportal_msg1)
                    $wpjobportal_msg['message'] = $wpjobportal_msg1 . ' ' . $wpjobportal_msg2 . ', ' . $wpjobportal_msg3;
                break;
            case WPJOBPORTAL_ALREADY_EXIST:
                $wpjobportal_msg['status'] = "error";
                $wpjobportal_msg2 = esc_html(__('already exist', 'wp-job-portal'));
                if ($wpjobportal_msg1)
                    $wpjobportal_msg['message'] = $wpjobportal_msg1 . ' ' . $wpjobportal_msg2;
                break;
            case WPJOBPORTAL_FILE_TYPE_ERROR:
                $wpjobportal_msg['status'] = "error";
                $wpjobportal_msg['message'] = esc_html(__('File type error', 'wp-job-portal'));
                break;
            case WPJOBPORTAL_FILE_SIZE_ERROR:
                $wpjobportal_msg['status'] = "error";
                $wpjobportal_msg['message'] = esc_html(__('File size error', 'wp-job-portal'));
                break;
            case WPJOBPORTAL_ENABLED:
                $wpjobportal_msg['status'] = "updated";
                $wpjobportal_msg2 = esc_html(__('has been enabled', 'wp-job-portal'));
                if ($wpjobportal_msg1) {
                    $wpjobportal_msg['message'] = $wpjobportal_msg1 . ' ' . $wpjobportal_msg2;
                }
                break;
            case WPJOBPORTAL_PACKAGE_ALREADY_PURCHASED:
                $wpjobportal_msg['status'] = "error";
                $wpjobportal_msg2 = esc_html(__('Can not buy free package more than once', 'wp-job-portal'));
                $wpjobportal_msg['message'] = $wpjobportal_msg2;
                break;
            case WPJOBPORTAL_DISABLED:
                $wpjobportal_msg['status'] = "updated";
                $wpjobportal_msg2 = esc_html(__('has been disabled', 'wp-job-portal'));
                if ($wpjobportal_msg1) {
                    $wpjobportal_msg['message'] = $wpjobportal_msg1 . ' ' . $wpjobportal_msg2;
                }
                break;
        }
        return $wpjobportal_msg;
    }
        private function notEnoughCredits(){
            $wpjobportal_html = '
                    <div class="jsre-error-page-message-wrapper">
                        <div class="jsre-error-page-message-image">
                            <img alt="'.esc_attr(__('no active package','wp-job-portal')).'" src="'.esc_url(WPJOBPORTAL_IMAGE).'/no-package.jpg'.'" />
                        </div>
                        <div class="jsre-error-page-message-text">
                            <div class="jsre-error-page-message-txt">
                                ' . esc_html(__('You do not have enough credits','wp-job-portal')) . '
                            </div>
                        </div>
                        <div class="jsre-error-page-message-btn">
                            <a title="'.esc_attr(__('buy packages','wp-job-portal')).'" class="jsre-error-page-message-btn-link" href="'.esc_url(wpjobportal::wpjobportal_makeUrl(array('jsreme'=>'package', 'jsrelt'=>'packages'))).'" >'. esc_html(__('Buy Package','wp-job-portal')) .'</a>
                        </div>
                    </div>
            ';
            echo wp_kses($wpjobportal_html, WPJOBPORTAL_ALLOWED_TAGS);
        }
    static function getEntityName($wpjobportal_entity) {
        $wpjobportal_name = "";
        $wpjobportal_entity = wpjobportalphplib::wpJP_strtolower($wpjobportal_entity);
        switch ($wpjobportal_entity) {
            case WPJOBPORTAL_SALARYRANGE:$wpjobportal_name = esc_html(__('Salary Range', 'wp-job-portal'));
                break;
            case WPJOBPORTAL_ADDRESSDATA:$wpjobportal_name = esc_html(__('Address Data', 'wp-job-portal'));
                break;
            case WPJOBPORTAL_AGE:$wpjobportal_name = esc_html(__('Age', 'wp-job-portal'));
                break;
            case WPJOBPORTAL_CATEGORY:$wpjobportal_name = esc_html(__('Category', 'wp-job-portal'));
                break;
            case WPJOBPORTAL_CITY:$wpjobportal_name = esc_html(__('City', 'wp-job-portal'));
                break;
            case WPJOBPORTAL_COMPANY:
                    $wpjobportal_name = esc_html(__('Company', 'wp-job-portal'));
                    if(WPJOBPORTALMessages::$wpjobportal_counter){
                        if(WPJOBPORTALMessages::$wpjobportal_counter >1){
                            $wpjobportal_name = esc_html(__('Companies', 'wp-job-portal'));
                        }
                    }
                break;
            case WPJOBPORTAL_RESUME:
                $wpjobportal_name = esc_html(__('Resume', 'wp-job-portal'));
                    if(WPJOBPORTALMessages::$wpjobportal_counter){
                        if(WPJOBPORTALMessages::$wpjobportal_counter >1){
                            $wpjobportal_name = esc_html(__('Resume', 'wp-job-portal'));
                        }
                    }
                break;
            case 'company':
                    $wpjobportal_name = esc_html(__('Company', 'wp-job-portal'));
                    if(WPJOBPORTALMessages::$wpjobportal_counter){
                        if(WPJOBPORTALMessages::$wpjobportal_counter >1){
                            $wpjobportal_name = esc_html(__('Companies', 'wp-job-portal'));
                        }
                    }
                break;
            case 'featuredcompany':$wpjobportal_name = esc_html(__('Featured company', 'wp-job-portal'));
                break;
            case 'message':$wpjobportal_name = esc_html(__('Message', 'wp-job-portal'));
                break;
            case WPJOBPORTAL_COUNTRY:$wpjobportal_name = esc_html(__('Country', 'wp-job-portal'));
                break;
            case WPJOBPORTAL_CURRENCY:$wpjobportal_name = esc_html(__('Currency', 'wp-job-portal'));
                break;
            case WPJOBPORTAL_CUSTOMFIELD:
            case WPJOBPORTAL_FIELDORDERING:$wpjobportal_name = esc_html(__('Field', 'wp-job-portal'));
                break;
            case 'department':case 'departments':$wpjobportal_name = esc_html(__('Department', 'wp-job-portal'));
                break;
            case 'coverletter':case 'coverletters':$wpjobportal_name = esc_html(__('Cover Letter', 'wp-job-portal'));
                break;
            case WPJOBPORTAL_EMPLOYERPACKAGES:$wpjobportal_name = esc_html(__('Employer package', 'wp-job-portal'));
                break;
            case WPJOBPORTAL_EXPERIENCE:$wpjobportal_name = esc_html(__('Experience', 'wp-job-portal'));
                break;
            case WPJOBPORTAL_HIGHESTEDUCATION:$wpjobportal_name = esc_html(__('Highest education', 'wp-job-portal'));
                break;
            case 'job':
                $wpjobportal_name = esc_html(__('Job', 'wp-job-portal'));
                if(WPJOBPORTALMessages::$wpjobportal_counter){
                    if(WPJOBPORTALMessages::$wpjobportal_counter >1){
                        $wpjobportal_name = esc_html(__('Jobs', 'wp-job-portal'));
                    }
                }
                break;
             case 'jobtype':$wpjobportal_name = esc_html(__('Job type', 'wp-job-portal'));
                break;
            case 'featuredjob':$wpjobportal_name = esc_html(__('Featured job', 'wp-job-portal'));
                break;
            case 'jobalert':$wpjobportal_name = esc_html(__('Job alert', 'wp-job-portal'));
                break;
            case 'jobstatus':$wpjobportal_name = esc_html(__('Job Status', 'wp-job-portal'));
                break;
            case WPJOBPORTAL_JOBTYPE:$wpjobportal_name = esc_html(__('Job type', 'wp-job-portal'));
                break;
            case WPJOBPORTAL_SALARYRANGE:$wpjobportal_name = esc_html(__('Salary Range', 'wp-job-portal'));
                break;
            case 'city':$wpjobportal_name = esc_html(__('City', 'wp-job-portal'));
            break;
            case WPJOBPORTAL_SALARYRANGETYPE:$wpjobportal_name = esc_html(__('Salary Range Type', 'wp-job-portal'));
                break;
            case WPJOBPORTAL_SHIFT:$wpjobportal_name = esc_html(__('Shift', 'wp-job-portal'));
                break;
            case WPJOBPORTAL_STATE:$wpjobportal_name = esc_html(__('State', 'wp-job-portal'));
                break;
            case WPJOBPORTAL_USER:$wpjobportal_name = esc_html(__('User', 'wp-job-portal'));
                break;
            case WPJOBPORTAL_USERROLE:$wpjobportal_name = esc_html(__('User role', 'wp-job-portal'));
                break;
            case 'tag':$wpjobportal_name = esc_html(__('Tag', 'wp-job-portal'));
                break;
            case 'shortlisted':$wpjobportal_name = esc_html(__('Short listed', 'wp-job-portal'));
                break;
            case WPJOBPORTAL_CONFIGURATION:$wpjobportal_name = esc_html(__('Configuration', 'wp-job-portal'));
                break;
            case WPJOBPORTAL_EMAILTEMPLATE:$wpjobportal_name = esc_html(__('Email Template', 'wp-job-portal'));
                break;
            case WPJOBPORTAL_JOBSAVESEARCH:$wpjobportal_name = esc_html(__('Job Search', 'wp-job-portal'));
                break;
            case WPJOBPORTAL_RESUMESEARCH:$wpjobportal_name = esc_html(__('Resume Search', 'wp-job-portal'));
                break;
            case WPJOBPORTAL_RECORD:
                $wpjobportal_name = esc_html(__('record', 'wp-job-portal')).'('. esc_html(__('s','wp-job-portal')) .')';
                break;
            case 'record':
                $wpjobportal_name = esc_html(__('record', 'wp-job-portal')).'('. esc_html(__('s','wp-job-portal')) .')';
                break;
            case WPJOBPORTAL_SLUG:
                    $wpjobportal_name = esc_html(__('Slug', 'wp-job-portal')).'('. esc_html(__('s','wp-job-portal')) .')';
                break;
            case 'slug':
                $wpjobportal_name = esc_html(__('Slug', 'wp-job-portal')).'('. esc_html(__('s','wp-job-portal')) .')';
            break;
             case 'currency':$wpjobportal_name = esc_html(__('Currency', 'wp-job-portal'));
                break;
            case 'country':$wpjobportal_name = esc_html(__('Country', 'wp-job-portal'));
            break;
            case 'state':$wpjobportal_name = esc_html(__('State', 'wp-job-portal'));
                break;
            case 'prefix':
                $wpjobportal_name = esc_html(__('Prefix', 'wp-job-portal')).'('. esc_html(__('s','wp-job-portal')) .')';
            break;
            case folder:$wpjobportal_name = esc_html(__('Folder', 'wp-job-portal'));
                break;
            case folderresume:$wpjobportal_name = esc_html(__('Folder Resume', 'wp-job-portal'));
                break;
            case 'resume':
                $wpjobportal_name = esc_html(__('Resume', 'wp-job-portal'));
                if(WPJOBPORTALMessages::$wpjobportal_counter){
                    if(WPJOBPORTALMessages::$wpjobportal_counter >1){
                        $wpjobportal_name = esc_html(__('Resume', 'wp-job-portal'));
                    }
                }
                break;
            case 'featuredresume':$wpjobportal_name = esc_html(__('Featured resume', 'wp-job-portal'));
            case 'folder':$wpjobportal_name = esc_html(__('Folder', 'wp-job-portal'));
            break;
            case 'folderresume':$wpjobportal_name = esc_html(__('Folder Resume', 'wp-job-portal'));
                break;
            case WPJOBPORTAL_PREFIX:
                    $wpjobportal_name = esc_html(__('Prefix', 'wp-job-portal')).'('. esc_html(__('s','wp-job-portal')) .')';
                break;
            case 'jobsavesearch':$wpjobportal_name = esc_html(__('Job Search', 'wp-job-portal'));
            break;
        case 'resumesearch':$wpjobportal_name = esc_html(__('Resume Search', 'wp-job-portal'));
            break;
        case 'package':$wpjobportal_name=esc_html(__('Package','wp-job-portal'));
            break;
            case 'purchasehistory':$wpjobportal_name=esc_html(__('Package','wp-job-portal'));
            break;
        case 'user':$wpjobportal_name = esc_html(__('User', 'wp-job-portal'));
                break;
        case 'userrole':$wpjobportal_name = esc_html(__('User role', 'wp-job-portal'));
            break;
         case 'configuration':$wpjobportal_name = esc_html(__('Configuration', 'wp-job-portal'));
            break;
        case 'highesteducation':$wpjobportal_name = esc_html(__('Highest education', 'wp-job-portal'));
                break;
        case 'category':$wpjobportal_name = esc_html(__('Category', 'wp-job-portal'));
                break;
        case 'salaryrangetype':$wpjobportal_name = esc_html(__('Salary Range Type', 'wp-job-portal'));
                break;
        case 'emailtemplate':$wpjobportal_name = esc_html(__('Email Template', 'wp-job-portal'));
                break;
        case 'careerlevel':$wpjobportal_name = esc_html(__('Career Level', 'wp-job-portal'));
                break;
        case 'employer':$wpjobportal_name = esc_html(__('Employer', 'wp-job-portal'));
                break;
        case 'jobseeker':$wpjobportal_name = esc_html(__('Jobseeker', 'wp-job-portal'));
                break;
        case 'invoice':$wpjobportal_name = esc_html(__('Invoice', 'wp-job-portal'));
                break;
        case 'customfield':
            case 'fieldordering':$wpjobportal_name = esc_html(__('Field', 'wp-job-portal'));
                break;
        case 'wpjobportal':
            $wpjobportal_name = esc_html(__('Options', 'wp-job-portal'));
                break;
        case 'addressdata':
            $wpjobportal_name = esc_html(__('Address data', 'wp-job-portal'));
                break;
        }
        return $wpjobportal_name;
    }

    public static function showMessage($message,$type,$return=0) {
        $wpjobportal_divHtml = '';
        if($type == 'updated'){
            $alert_class = 'success';
            $wpjobportal_img_name = 'job-alert-successful.png';
        }else if($type == 'saved'){
            $alert_class = 'success';
            $wpjobportal_img_name = 'job-alert-successful.png';
        }else if($type == 'error'){
            $alert_class = 'danger';
            $wpjobportal_img_name = 'job-alert-unsuccessful.png';
        }
        $wpjobportal_divHtml .= '<div class="alert alert-' . $alert_class . '" role="alert" id="autohidealert">
            <img class="leftimg" src="'.esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/'.$wpjobportal_img_name.'" />
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            '. $message . '
        </div>';
        if($return){
            return $wpjobportal_divHtml;
        }
        echo wp_kses($wpjobportal_divHtml, WPJOBPORTAL_ALLOWED_TAGS);
    }

}

?>
