<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALJobalertController {

    private $_msgkey;

    function __construct() {

        self::handleRequest();

        $this->_msgkey = WPJOBPORTALincluder::getJSModel('jobalert')->getMessagekey();
    }

    function handleRequest() {
        $layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'jobalert');
        // The admin list and form are a paid-tier tool (hidden from the menu without it), but
        // opened by URL they loaded anyway; say what they need instead
        if (is_admin() && in_array($layout, array('admin_jobalert', 'admin_formjobalert'), true)
            && class_exists('WPJOBPORTAL_Feature_Tiers') && !WPJOBPORTAL_Feature_Tiers::job_alert_admin_tools()
            && class_exists('WPJOBPORTAL_Addon_Fallbacks')) {
            WPJOBPORTAL_Addon_Fallbacks::render_missing_screen('jobalert');
            return;
        }
        if (self::canaddfile($layout)) {
            $config_array = wpjobportal::$_config->getConfigByFor('jscontrolpanel');
            switch ($layout) {
                case 'jobalert':
                case 'admin_formjobalert':
                    $showform = 1;
                    $uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
                    try {
                        if (WPJOBPORTALincluder::getObjectClass('user')->isguest()) { // user is visitor
                            $showform = $config_array['vis_wpjobportalalertsetting'];
                        } elseif (!WPJOBPORTALincluder::getObjectClass('user')->isemployer()) {
                            $showform = $config_array['jobalertsetting'];
                        }
                        if ($showform == 1) {
                            if (wpjobportal::$_common->wpjp_isadmin()) {
                                $id = WPJOBPORTALrequest::getVar('wpjobportalid');
                            } else {
                                $id = WPJOBPORTALincluder::getJSModel('jobalert')->getMyJobAlertId();
                            }
                            if(in_array('credits', wpjobportal::$_active_addons) && $id == '' && !WPJOBPORTALincluder::getObjectClass('user')->isguest()){
                                # Filter Package For Controller
                                $data = json_decode(apply_filters('wpjobportal_addons_available_package',false,'jobalert','jobalert','canAddJobAlert'));
                                $check = $data->check;
                                if($check == true){
                                    if(isset($data->layout) && $data->layout == "packageselection" ){
                                       $layout = $data->layout;
                                        $module = 'package';
                                    }
                                }else{
                                    $link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'package', 'wpjobportallt'=>'packages'));
                                    $linktext = __('Buy Package', 'wp-job-portal');
                                    wpjobportal::$_error_flag = true;
                                    wpjobportal::$_error_flag_message_for=15;
                                    throw new Exception(WPJOBPORTALLayout::setMessageFor(15,$link,$linktext,1));
                                }
                            }else{
                                $check = true;
                            }
                            if(wpjobportal::$_common->wpjp_isadmin() || $check == true){
                                WPJOBPORTALincluder::getJSModel('jobalert')->getJobAlertbyIdforForm($id);
                            }
                        } else {
                            if (WPJOBPORTALincluder::getObjectClass('user')->isemployer()) {
                                throw new Exception(WPJOBPORTALLayout::setMessageFor(3,null,null,1));

                            } elseif (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
                                wpjobportal::$_error_flag_message_register_for=1; // register as jobseeker
                                throw new Exception(WPJOBPORTALLayout::setMessage(1,null,null,1));

                            }
                            if(isset($link) && isset($linktext)){
                                wpjobportal::$_error_flag_message_for_link = $link;
                                wpjobportal::$_error_flag_message_for_link_text = $linktext;
                            }
                        }
                    } catch (Exception $ex) {
                        wpjobportal::$_error_flag = true;
                        wpjobportal::$_error_flag_message  = $ex->getMessage();
                    }
                    break;
                case 'admin_jobalert':
                    WPJOBPORTALincluder::getJSModel('jobalert')->getAdminJobAlertList();
                    break;
            }

            if(!isset($module)){
                $module = (wpjobportal::$_common->wpjp_isadmin()) ? 'page' : 'wpjobportalme';
                $module = WPJOBPORTALrequest::getVar($module, null, 'jobalert');
            }

            $module = wpjobportalphplib::wpJP_str_replace('wpjobportal_', '', $module);
            WPJOBPORTALincluder::include_file($layout, $module);
        }
    }

    function canaddfile($layout) {
        if (WPJOBPORTALrequest::getVar('form_request', 'post', '') == 'wpjobportal')
            return false;
        elseif (WPJOBPORTALrequest::getVar('action', 'get', '') == 'wpjobportaltask')
            return false;
        else{
                if(!is_admin() && strpos($layout, 'admin_') === 0){
                    return false;
                }
                return true;
            }
    }

    function savejobalert() { //save savejobalert
        $nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'wpjobportal_job_alert_nonce') ) {
             die( 'Security check Failed' );
        }
        $data = WPJOBPORTALrequest::get('post');
        $submissionType = wpjobportal::$_config->getConfigValue('submission_type');
        $action = "jobalert";
        $result = WPJOBPORTALincluder::getJSModel('jobalert')->storeJobAlertSetting($data,$action);
        if ($result != 2) {
            $msg = WPJOBPORTALMessages::getMessage($result, 'jobalert');
            WPJOBPORTALMessages::setLayoutMessage($msg['message'], $msg['status'],$this->_msgkey);
        }

        if (wpjobportal::$_common->wpjp_isadmin()) {
            $url = admin_url("admin.php?page=wpjobportal_jobalert");
        } else {
            if($submissionType == 2){
                $url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobalert', 'wpjobportallt'=>'jobalert'));
            }else{
                  $url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobalert', 'wpjobportallt'=>'jobalert'));
            }
        }

        wp_safe_redirect($url);
        die();
    }

    function unsubscribeJobAlertSetting() {
        $nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'wpjobportal_job_alert_nonce') ) {
             die( 'Security check Failed' );
        }
        $alertid = WPJOBPORTALrequest::getVar('jobalertid');
        $result = WPJOBPORTALincluder::getJSModel('jobalert')->unSubscribeJobAlert($alertid);
        $msg = WPJOBPORTALMessages::getMessage($result, 'jobalert');
        WPJOBPORTALMessages::setLayoutMessage($msg['message'], $msg['status'],$this->_msgkey);
        if(wpjobportal::$_common->wpjp_isadmin()){
            $url = WPJOBPORTAL_Admin_List_UI::return_url('wpjobportal_jobalert', 'admin.php?page=wpjobportal_jobalert&wpjobportallt=jobalert');
        }else{
            $url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobalert', 'wpjobportallt'=>'jobalert','wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
        }
        wp_safe_redirect($url);
        die();
    }

    /**
     * Bulk action from the Job alerts list: unsubscribe (delete) the selected
     * alerts, as the 2.7 list's per-row Unsubscribe did one at a time.
     */
    function bulkjobalerts() {
        $nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'wpjobportal_job_alert_nonce') ) {
             die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) {
            die( 'Not Allowed!' );
        }
        $ids = array_values(array_filter(array_map('absint', (array) WPJOBPORTALrequest::getVar('wpjobportal-cb'))));
        if (sanitize_key((string) WPJOBPORTALrequest::getVar('bulkop')) === 'delete') {
            // unSubscribeJobAlert() reports success for an id that no longer exists: count only real alerts.
            $existing = $ids ? array_map('absint', (array) wpjobportal::$_db->get_col("SELECT id FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobalertsetting` WHERE id IN (" . implode(',', $ids) . ")")) : array();
            $done = 0;
            foreach ($existing as $id) {
                if (WPJOBPORTALincluder::getJSModel('jobalert')->unSubscribeJobAlert($id) == WPJOBPORTAL_DELETED) {
                    $done++;
                }
            }
            /* translators: 1: alerts removed, 2: alerts selected */
            WPJOBPORTALMessages::setLayoutMessage(sprintf(__('%1$d of %2$d job alerts unsubscribed.', 'wp-job-portal'), $done, count($ids)), $done ? 'updated' : 'error', $this->_msgkey);
        }
        wp_safe_redirect(WPJOBPORTAL_Admin_List_UI::return_url('wpjobportal_jobalert', 'admin.php?page=wpjobportal_jobalert'));
        die();
    }

    function unsubscribeJobAlertSettingEmail() {
        $nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'wpjobportal_job_alert_nonce') ) {
             die( 'Security check Failed' );
        }
        $emailaddress = WPJOBPORTALrequest::getVar('emailaddress');
        $result = WPJOBPORTALincluder::getJSModel('jobalert')->unSubscribeJobAlertEmail($emailaddress);
        if ($result === 'WPJOBPORTAL_UNSUBSCRIBE_NONE') {
            WPJOBPORTALMessages::setLayoutMessage(__('Your account has no job alerts at this email address.', 'wp-job-portal'), 'error', $this->_msgkey);
        } elseif ($result === 'WPJOBPORTAL_UNSUBSCRIBE_EMAIL_SENT') {
            WPJOBPORTALMessages::setLayoutMessage(__('If this address has job alerts, we have emailed it a link to unsubscribe.', 'wp-job-portal'), 'updated', $this->_msgkey);
        } else {
            $msg = WPJOBPORTALMessages::getMessage($result, 'jobalert');
            WPJOBPORTALMessages::setLayoutMessage($msg['message'], $msg['status'],$this->_msgkey);
        }
        wp_safe_redirect(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobalert', 'wpjobportallt'=>'jobalert','wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid())));
        die();
    }

    function sendjobalert() {
        $ck = WPJOBPORTALrequest::getVar('ck');
        if (!WPJOBPORTALincluder::getJSModel('jobalert')->isValidExternalCronSecret($ck)) {
            status_header(403);
            wp_die(esc_html__('Invalid job alert cron secret.', 'wp-job-portal'), '', array('response' => 403));
        }

        $result = WPJOBPORTALincluder::getJSModel('jobalert')->sendAllJobAlerts();
        if ($result === 'locked') {
            status_header(409);
            echo esc_html__('A job alert run is already in progress.', 'wp-job-portal');
        } elseif ($result) {
            echo esc_html__('Job alerts processed.', 'wp-job-portal');
        } else {
            status_header(500);
            echo esc_html__('Job alerts could not be processed.', 'wp-job-portal');
        }
        die();
    }

	/*
    function sendjobalertdTESTCASE() {
        $job_id = 1;
        WPJOBPORTALincluder::getJSModel('jobalert')->sendInstantAlertsBackground($job_id);
        die('in controller line no 176');
    }
*/
}

$WPJOBPORTALJobalertController = new WPJOBPORTALJobalertController();
?>
