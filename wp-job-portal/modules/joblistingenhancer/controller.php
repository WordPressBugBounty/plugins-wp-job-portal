<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALjoblistingenhancerController {

    private $_msgkey;

    function __construct() {
        self::handleRequest();
        $this->_msgkey = WPJOBPORTALincluder::getJSModel('joblistingenhancer')->getMessagekey();

    }

    function handleRequest() {
        $layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'joblistingenhancer');
        $uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        if (self::canaddfile()) {
            $config_array = wpjobportal::$_config->getConfigByFor('emcontrolpanel');
            // switch ($layout) {
            //     case 'myjoblistingenhancers':
            //         try {
            //             if (WPJOBPORTALincluder::getObjectClass('user')->isjobseeker() ) {
            //             WPJOBPORTALincluder::getJSModel('joblistingenhancer')->getMyCoverLetters($uid);
            //             } else {
            //                 if (WPJOBPORTALincluder::getObjectClass('user')->isemployer()) {
            //                     wpjobportal::$_error_flag_message_for=2;
            //                     throw new Exception(WPJOBPORTALLayout::setMessageFor(2,null,null,1));
            //                 } elseif (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
            //                     $link = WPJOBPORTALincluder::getJSModel('common')->jsMakeRedirectURL('joblistingenhancer', $layout, 1);
            //                     $linktext = __('Login','wp-job-portal');
            //                     wpjobportal::$_error_flag_message_for=1;
            //                     wpjobportal::$_error_flag_message_register_for=2;
            //                     throw new Exception(WPJOBPORTALLayout::setMessageFor(1 , $link , $linktext,1));

            //                 } elseif (!WPJOBPORTALincluder::getObjectClass('user')->isWPJOBPortalUser()) {
            //                     $link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'common', 'wpjobportallt'=>'newinwpjobportal'));
            //                     $linktext = __('Select Role','wp-job-portal');
            //                    wpjobportal::$_error_flag_message_for=9;
            //                     throw new Exception(WPJOBPORTALLayout::setMessageFor(9 , $link , $linktext,1));

            //                 }
            //                 if(isset($link) && isset($linktext)){
            //                     wpjobportal::$_error_flag_message_for_link=$link;
            //                     wpjobportal::$_error_flag_message_for_link_text=$linktext;
            //                 }
            //             }
            //         } catch (Exception $ex) {
            //             wpjobportal::$_error_flag = true;
            //             wpjobportal::$_error_flag_message = $ex->getMessage();
            //             if(isset($link) && isset($linktext) && wpjobportal::$theme_chk == 1){
            //                 wpjobportal::$_error_flag_message_for_link=$link;
            //                 wpjobportal::$_error_flag_message_for_link_text=$linktext;
            //             }

            //         }

            //         break;
            //     case 'viewjoblistingenhancer':
            //         try {
            //             if (WPJOBPORTALincluder::getObjectClass('user')->isjobseeker() || WPJOBPORTALincluder::getObjectClass('user')->isemployer()) {
            //             $id = WPJOBPORTALrequest::getVar('wpjobportalid');
            //             WPJOBPORTALincluder::getJSModel('joblistingenhancer')->getViewCoverLetter($id);
            //             } else {
            //                 if (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
            //                     $link = WPJOBPORTALincluder::getJSModel('common')->jsMakeRedirectURL('joblistingenhancer', $layout, 1);
            //                     $linktext = __('Login','wp-job-portal');
            //                     wpjobportal::$_error_flag_message_for=1;
            //                     throw new Exception(WPJOBPORTALLayout::setMessageFor(1 , $link , $linktext,1));

            //                 } elseif (!WPJOBPORTALincluder::getObjectClass('user')->isWPJOBPortalUser()) {
            //                     $link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'common', 'wpjobportallt'=>'newinwpjobportal'));
            //                     $linktext = __('Select Role','wp-job-portal');
            //                     wpjobportal::$_error_flag_message_for=9;
            //                     throw new Exception(WPJOBPORTALLayout::setMessageFor(9 , $link , $linktext,1));

            //                 }
            //                 if(isset($link) && isset($linktext)){
            //                     wpjobportal::$_error_flag_message_for_link=$link;
            //                     wpjobportal::$_error_flag_message_for_link_text=$linktext;
            //                 }
            //             }

            //             if(empty(wpjobportal::$_data[0])){
            //                 wpjobportal::$_error_flag = true;
            //                 wpjobportal::$_error_flag_message_for=6;
            //                 throw new Exception(WPJOBPORTALLayout::setMessageFor(6,'','',1));
            //             }
            //         } catch (Exception $ex) {
            //             wpjobportal::$_error_flag = true;
            //             wpjobportal::$_error_flag_message = $ex->getMessage();

            //         }
            //         break;
            //     case 'addjoblistingenhancer':
            //         try {
            //             if (WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()) {
            //                 $id = WPJOBPORTALrequest::getVar('wpjobportalid');
            //                 if($id == ''){
            //                     # Package Code
            //                     if(in_array('credits',wpjobportal::$_active_addons)){
            //                         # Filter Package For Controller
            //                         $data = json_decode(apply_filters('wpjobportal_addons_available_package',false,'joblistingenhancer','joblistingenhancer','canAddCoverLetter'));
            //                         $check = $data->check;
            //                         if($check == true){
            //                             if(isset($data->layout) && $data->layout == "packageselection" ){
            //                                 $layout = $data->layout;
            //                                 $module = "package";
            //                             }
            //                        }else{
            //                             $link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'package', 'wpjobportallt'=>'packages'));
            //                             $linktext = __('Buy Package', 'wp-job-portal');
            //                             wpjobportal::$_error_flag = true;
            //                             wpjobportal::$_error_flag_message_for=15;
            //                             throw new Exception(WPJOBPORTALLayout::setMessageFor(15,$link,$linktext,1));
            //                        }
            //                     }else{
            //                         $check = true;
            //                     }

            //                     //$check = true;
            //                 }else{
            //                     if(!wpjobportal::$_common->wpjp_isadmin()){
            //                         $check = WPJOBPORTALincluder::getJSModel('joblistingenhancer')->getIfCoverLetterOwner($id);
            //                     }
            //                 }
            //                 if ($check == true) {
            //                     WPJOBPORTALincluder::getJSModel('joblistingenhancer')->getCoverLetterById($id);
            //                 } elseif($id != ''){
            //                     // deleted cover letter access
            //                     wpjobportal::$_error_flag = true;
            //                     wpjobportal::$_error_flag_message_for=6;
            //                     throw new Exception(WPJOBPORTALLayout::setMessageFor(6,'','',1));

            //                 } else {
            //                     $link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'package', 'wpjobportallt'=>'packages'));
            //                     $linktext = __('Buy credits', 'wp-job-portal');
            //                     wpjobportal::$_error_flag = true;
            //                     wpjobportal::$_error_flag_message_for=4;
            //                     wpjobportal::$_error_flag_message_for_link = $link;
            //                     wpjobportal::$_error_flag_message_for_link_text = $linktext;
            //                     throw new Exception(WPJOBPORTALLayout::setMessageFor(4, $link, $linktext , 1));
            //                 }
            //             } else {
            //                 if (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
            //                     $link = WPJOBPORTALincluder::getJSModel('common')->jsMakeRedirectURL('joblistingenhancer', $layout, 1);
            //                     $linktext = __('Login','wp-job-portal');
            //                     wpjobportal::$_error_flag_message_for=1;
            //                     wpjobportal::$_error_flag_message_register_for=2;
            //                     throw new Exception(WPJOBPORTALLayout::setMessageFor(1 , $link , $linktext,1));

            //                 } elseif (!WPJOBPORTALincluder::getObjectClass('user')->isWPJOBPortalUser()) {
            //                     $link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'common', 'wpjobportallt'=>'newinwpjobportal'));
            //                     $linktext = __('Select Role','wp-job-portal');
            //                     wpjobportal::$_error_flag_message_for=9;
            //                     throw new Exception(WPJOBPORTALLayout::setMessageFor(9 , $link , $linktext,1));

            //                 } elseif (WPJOBPORTALincluder::getObjectClass('user')->isemployer()) {
            //                     wpjobportal::$_error_flag_message_for = 2;
            //                     throw new Exception(WPJOBPORTALLayout::setMessageFor(2,null,null,1));
            //                 }
            //             }
            //             if(isset($link) && isset($linktext)){
            //                 wpjobportal::$_error_flag_message_for_link=$link;
            //                 wpjobportal::$_error_flag_message_for_link_text=$linktext;
            //             }
            //         } catch (Exception $ex) {
            //             wpjobportal::$_error_flag = true;
            //             wpjobportal::$_error_flag_message = $ex->getMessage();
            //             if(isset($link) && isset($linktext) && wpjobportal::$theme_chk == 1){
            //                 wpjobportal::$_error_flag_message_for_link=$link;
            //                 wpjobportal::$_error_flag_message_for_link_text=$linktext;
            //             }
            //         }
            //         break;
            //         case 'admin_joblistingenhancer':
            //         $layout = 'admin_joblistingenhancers';
            //         WPJOBPORTALincluder::getJSModel('joblistingenhancer')->getCoverLetters();
            //     break;
            //     case 'admin_formjoblistingenhancer':
            //         try {
            //             if (wpjobportal::$_common->wpjp_isadmin()) {
            //                 $id =(int) WPJOBPORTALrequest::getVar('wpjobportalid');
            //                     if($id == ''){
            //                         $check = true;
            //                        }else{
            //                         if(!wpjobportal::$_common->wpjp_isadmin()){
            //                             $check = WPJOBPORTALincluder::getJSModel('joblistingenhancer')->getIfCoverLetterOwner($id);
            //                         }
            //                     }
            //                     if (wpjobportal::$_common->wpjp_isadmin() || $check == true) {
            //                         WPJOBPORTALincluder::getJSModel('joblistingenhancer')->getCoverLetterById($id);
            //                     } else{
            //                         wpjobportal::$_error_flag_message_for=4;
            //                         throw new Exception(WPJOBPORTALLayout::setMessageFor(10));
            //                     }
            //                 } else {
            //                     if (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
            //                         $link = WPJOBPORTALincluder::getJSModel('common')->jsMakeRedirectURL('joblistingenhancer', $layout, 1);
            //                         $linktext = __('Login','wp-job-portal');
            //                         wpjobportal::$_error_flag_message_for=1;
            //                         wpjobportal::$_error_flag_message_register_for=2;
            //                         throw new Exception(WPJOBPORTALLayout::setMessageFor(1 , $link , $linktext,1));
            //                     } elseif (!WPJOBPORTALincluder::getObjectClass('user')->isWPJOBPortalUser()) {
            //                         $link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'common', 'wpjobportallt'=>'newinwpjobportal'));
            //                         $linktext = __('Select Role','wp-job-portal');
            //                         wpjobportal::$_error_flag_message_for=9;
            //                     } elseif (WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()) {
            //                         wpjobportal::$_error_flag_message_for=2;
            //                         throw new Exception(WPJOBPORTALLayout::setMessageFor(2,null,null,1));
            //                     }
            //              }
            //             if(isset($link) && isset($linktext)){
            //                 wpjobportal::$_error_flag_message_for_link=$link;
            //                 wpjobportal::$_error_flag_message_for_link_text=$linktext;
            //             }
            //         } catch (Exception $ex) {
            //             wpjobportal::$_error_flag = true;
            //             wpjobportal::$_error_flag_message=$ex->getMessage();
            //          }
            //     break;
            //     case 'admin_joblistingenhancerqueue':
            //      WPJOBPORTALincluder::getJSModel('joblistingenhancer')->getAllUnapprovedCoverLetters();
            //         break;
            // }


            if(!isset($module)){
                $module = (wpjobportal::$_common->wpjp_isadmin()) ? 'page' : 'wpjobportalme';
                $module = WPJOBPORTALrequest::getVar($module, null, 'joblistingenhancer');
            }
            $module = str_replace('wpjobportal_', '', $module);
            WPJOBPORTALincluder::include_file($layout, $module);
        }
    }

    function canaddfile() {
        if (WPJOBPORTALrequest::getVar('form_request', 'post', '') == 'wpjobportal')
            return false;
        elseif (WPJOBPORTALrequest::getVar('action', 'get', '') == 'wpjobportaltask')
            return false;
        else
            return true;
    }

    /*
     * approve() used to sit here and has been removed, along with its route.
     *
     * It called joblistingenhancerApprove() on this module's model, which
     * defines exactly one method - getMessagekey() - and never defined that
     * one. It then redirected to admin.php?page=wpjobportal_joblistingenhancer
     * with a joblistingenhancerqueue layout: there is no such admin page, no
     * such template, and the queue case in handleRequest above is commented
     * out. Nothing in the product links to any of it.
     *
     * Its origin is visible in that commented-out case, which calls
     * getAllUnapprovedCoverLetters(): this controller was copied from the cover
     * letter module and the approval half was never made to mean anything here.
     * The listing enhancer's actual feature is the front-end filter bar under
     * tmpl/views/frontend, which is untouched.
     *
     * Unlike the other fossils removed this round, this one was dispatchable -
     * 'approve' was a registered route - so a request carrying a valid nonce
     * reached it and fataled. The route registration is removed with it.
     */


}

$WPJOBPORTALjoblistingenhancerController = new WPJOBPORTALjoblistingenhancerController();
?>
