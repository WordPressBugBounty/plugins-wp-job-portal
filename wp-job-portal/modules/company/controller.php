<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALCompanyController {

    private $_msgkey;

    function __construct() {
        self::handleRequest();
        $this->_msgkey = WPJOBPORTALincluder::getJSModel('company')->getMessagekey();
        $mcompany = WPJOBPORTALincluder::getJSModel('company');
    }

    function handleRequest() {
        $wpjobportal_layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'companies');
        $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        $mcompany = WPJOBPORTALincluder::getJSModel('company');
        if (self::canaddfile($wpjobportal_layout)) {
            $wpjobportal_empflag  = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('disable_employer');
            $wpjobportal_string = "'jscontrolpanel','emcontrolpanel','visitor'" ;
            $wpjobportal_config_array = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigForMultiple($wpjobportal_string);
            switch ($wpjobportal_layout) {
                case 'admin_companies':
                        WPJOBPORTALincluder::getJSModel('company')->getAllCompanies();
                    break;
                case 'admin_companiesqueue':
                    WPJOBPORTALincluder::getJSModel('company')->getAllUnapprovedCompanies();
                    break;
                case 'mycompanies':
                    try {
                        if (WPJOBPORTALincluder::getObjectClass('user')->isemployer() && $wpjobportal_empflag == 1) {
                            WPJOBPORTALincluder::getJSModel('company')->getMyCompanies($wpjobportal_uid);
                        } else {
                            //wpjobportal::$_common->validateEmployerArea();
                            if (WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()) {
                                wpjobportal::$_error_flag_message_for=2; // user is jobseeker
                                throw new Exception(WPJOBPORTALLayout::setMessageFor(2,null,null,1));

                            } elseif (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
                                $wpjobportal_link = WPJOBPORTALincluder::getJSModel('common')->jsMakeRedirectURL('company', $wpjobportal_layout, 1);
                                $wpjobportal_linktext = esc_html(__('Login','wp-job-portal'));
                                wpjobportal::$_error_flag_message_for=1; // user is guest
                                wpjobportal::$_error_flag_message_register_for=2; // register as employer
                                throw new Exception(WPJOBPORTALLayout::setMessageFor(1 , $wpjobportal_link , $wpjobportal_linktext,1));

                            } elseif (!WPJOBPORTALincluder::getObjectClass('user')->isWPJOBPortalUser()) {
                                $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'common', 'wpjobportallt'=>'newinwpjobportal'));
                                $wpjobportal_linktext = esc_html(__('Select role','wp-job-portal'));
                                wpjobportal::$_error_flag_message_for=9; // role is not select
                                throw new Exception(WPJOBPORTALLayout::setMessageFor(9 , $wpjobportal_link , $wpjobportal_linktext,1));
                            }
                            if(isset($wpjobportal_link) && isset($wpjobportal_linktext)){
                                wpjobportal::$_error_flag_message_for_link=$wpjobportal_link;
                                wpjobportal::$_error_flag_message_for_link_text=$wpjobportal_linktext;
                            }
                        }

                    } catch (Exception $wpjobportal_ex) {
                        wpjobportal::$_error_flag = true;
                        wpjobportal::$_error_flag_message = $wpjobportal_ex->getMessage();
                    }

                    break;
                case 'admin_formcompany':
                    try {
                        if (wpjobportal::$_common->wpjp_isadmin() || (WPJOBPORTALincluder::getObjectClass('user')->isemployer() && $wpjobportal_empflag == 1)) {
                        $wpjobportal_id = WPJOBPORTALrequest::getVar('wpjobportalid');
                            if($wpjobportal_id == ''){
                                $wpjobportal_check = true;
                            }else{
                                if(!wpjobportal::$_common->wpjp_isadmin()){
                                    $wpjobportal_check = WPJOBPORTALincluder::getJSModel('company')->getIfCompanyOwner($wpjobportal_id);// so only owner can edit company
                                }
                            }
                            if (wpjobportal::$_common->wpjp_isadmin() || $wpjobportal_check == true) {
                                WPJOBPORTALincluder::getJSModel('company')->getCompanybyId($wpjobportal_id);
                            }elseif($wpjobportal_id != ''){
                                wpjobportal::$_error_flag_message_for=10;//companies
                                throw new Exception(WPJOBPORTALLayout::setMessageFor(10));

                            }
                        } else {
                            if (WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()) {
                                wpjobportal::$_error_flag_message_for=2;
                                throw new Exception(WPJOBPORTALLayout::setMessageFor(2,null,null,1));

                            } elseif (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
                                $wpjobportal_link = wpjobportal::$_common->jsMakeRedirectURL('company', $wpjobportal_layout, 1);
                                $wpjobportal_linktext = esc_html(__('Login','wp-job-portal'));
                                wpjobportal::$_error_flag_message_for=1;
                                wpjobportal::$_error_flag_message_register_for=2;
                                throw new Exception(WPJOBPORTALLayout::setMessageFor(1 , $wpjobportal_link , $wpjobportal_linktext,1));

                            } elseif (!WPJOBPORTALincluder::getObjectClass('user')->isWPJOBPortalUser()) {
                                $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'common', 'wpjobportallt'=>'newinwpjobportal'));
                                $wpjobportal_linktext = esc_html(__('Select role','wp-job-portal'));
                                wpjobportal::$_error_flag_message_for=9;
                                throw new Exception(WPJOBPORTALLayout::setMessageFor(9 , $wpjobportal_link , $wpjobportal_linktext,1));

                            }
                        }
                        if(isset($wpjobportal_link) && isset($wpjobportal_linktext)){
                            wpjobportal::$_error_flag_message_for_link=$wpjobportal_link;
                            wpjobportal::$_error_flag_message_for_link_text=$wpjobportal_linktext;
                        }

                    } catch (Exception $wpjobportal_ex) {
                        wpjobportal::$_error_flag = true;
                        wpjobportal::$_error_flag_message=$wpjobportal_ex->getMessage();
                    }
                    break;
                case 'addcompany':
                    try {
                         if (WPJOBPORTALincluder::getObjectClass('user')->isemployer() && $wpjobportal_empflag == 1) {
                            $wpjobportal_id = WPJOBPORTALrequest::getVar('wpjobportalid');
                            $wpjobportal_actionname = 'company';
                            if($wpjobportal_id == ''){
                                 if(in_array('credits',wpjobportal::$_active_addons)){
                                    // Filter Package For Controller
                                    if(WPJOBPORTALincluder::getJSModel('company')->userCanAddCompany($wpjobportal_uid) == true){
                                        $wpjobportal_data = json_decode(apply_filters('wpjobportal_addons_available_package',false,'company','company','canAddCompany'));
                                        $wpjobportal_check = $wpjobportal_data->check;
                                    }else{
                                         wpjobportal::$_common->getMessagesForAddMore('Company');
                                    }
                                    if($wpjobportal_check == true){
                                        if(isset($wpjobportal_data->layout) && $wpjobportal_data->layout == "packageselection" ){
                                            $wpjobportal_layout = $wpjobportal_data->layout;
                                            $wpjobportal_module = 'package';
                                        }
                                   }else{
                                        wpjobportal::$_common->getBuyErrMsg();
                                   }
                                }else{ // without credit system case
                                    if(WPJOBPORTALincluder::getJSModel('company')->canAddCompany($wpjobportal_uid) == false){ // check employer already has 1 company
                                        wpjobportal::$_common->getMessagesForAddMore('Company'); // show error message (not allowed more companies)
                                    }else{
                                        $wpjobportal_check =  true;
                                    }
                                }
                            }else{
                                if(!wpjobportal::$_common->wpjp_isadmin()){
                                    $wpjobportal_check = WPJOBPORTALincluder::getJSModel('company')->getIfCompanyOwner($wpjobportal_id);// so only owner can edit company
                                }
                            }
                            if (wpjobportal::$_common->wpjp_isadmin() || $wpjobportal_check == true) {
                                WPJOBPORTALincluder::getJSModel('company')->getCompanybyId($wpjobportal_id);
                            }elseif($wpjobportal_id != ''){
                                wpjobportal::$_error_flag = true;
                                wpjobportal::$_error_flag_message_for=10;
                                $wpjobportal_link = 10;
                                throw new Exception( WPJOBPORTALLayout::setMessageFor(10,null,null,1));

                            } else {
                                wpjobportal::$_common->getBuyErrMsg();
                            }
                        } else {
                            // wpjobportal::$_common->validateEmployerArea();
                            if (WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()) {
                                wpjobportal::$_error_flag_message_for=2;
                                throw new Exception( WPJOBPORTALLayout::setMessageFor(2,null,null,1));

                            } elseif (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
                                $wpjobportal_link = WPJOBPORTALincluder::getJSModel('common')->jsMakeRedirectURL('company', $wpjobportal_layout, 1);
                                $wpjobportal_linktext = esc_html(__('Login','wp-job-portal'));
                                wpjobportal::$_error_flag_message_for=1;
                                wpjobportal::$_error_flag_message_register_for=2;
                                throw new Exception( WPJOBPORTALLayout::setMessageFor(1 , $wpjobportal_link , $wpjobportal_linktext,1));

                            } elseif (!WPJOBPORTALincluder::getObjectClass('user')->isWPJOBPortalUser()) {
                                $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'common', 'wpjobportallt'=>'newinwpjobportal'));
                                $wpjobportal_linktext = esc_html(__('Select role','wp-job-portal'));
                                wpjobportal::$_error_flag_message_for=9;
                                throw new Exception(WPJOBPORTALLayout::setMessageFor(9 , $wpjobportal_link , $wpjobportal_linktext,1));

                            }
                        }
                        if(isset($wpjobportal_link) && isset($wpjobportal_linktext)){
                            wpjobportal::$_error_flag_message_for_link=$wpjobportal_link;
                            wpjobportal::$_error_flag_message_for_link_text=$wpjobportal_linktext;
                        }

                    } catch (Exception $wpjobportal_ex) {
                         wpjobportal::$_error_flag = true;
                         wpjobportal::$_error_flag_message = $wpjobportal_ex->getMessage();
                    }

                    break;
                case 'admin_view_company':
                    try {
                        if (WPJOBPORTALincluder::getObjectClass('user')->isguest() && $wpjobportal_config_array['visitorview_emp_viewcompany'] != 1) {
                            $wpjobportal_link = wpjobportal::$_common->jsMakeRedirectURL('company', $wpjobportal_layout, 1);
                            $wpjobportal_linktext = esc_html(__('Login','wp-job-portal'));
                            wpjobportal::$_error_flag_message_for=1;
                            wpjobportal::$_error_flag_message_register_for=2;
                            throw new Exception(WPJOBPORTALLayout::setMessageFor(1 , $wpjobportal_link , $wpjobportal_linktext,1));

                        } elseif (!WPJOBPORTALincluder::getObjectClass('user')->isWPJOBPortalUser() && $wpjobportal_config_array['visitorview_emp_viewcompany'] != 1) {
                            $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'common', 'wpjobportallt'=>'newinwpjobportal'));
                            $wpjobportal_linktext = esc_html(__('Select role','wp-job-portal'));
                            wpjobportal::$_error_flag_message_for=9;
                            wpjobportal::$_error_flag_message_register_for=2;
                            throw new Exception(WPJOBPORTALLayout::setMessageFor(9 , $wpjobportal_link , $wpjobportal_linktext,1));

                        } else {
                            $wpjobportal_id = WPJOBPORTALrequest::getVar('wpjobportalid');
                            $wpjobportal_id = wpjobportal::$_common->parseID($wpjobportal_id);
                            $wpjobportal_expiryflag = WPJOBPORTALincluder::getJSModel('company')->getCompanyExpiryStatus($wpjobportal_id);
                                if (WPJOBPORTALincluder::getObjectClass('user')->isemployer()) {
                                    if (WPJOBPORTALincluder::getJSModel('company')->getIfCompanyOwner($wpjobportal_id)) {
                                        $wpjobportal_expiryflag = true;
                                    }
                                }
                                if ($wpjobportal_expiryflag == false) {
                                    wpjobportal::$_error_flag_message_for=6;
                                    throw new Exception(WPJOBPORTALLayout::setMessageFor(6,null,null,1));

                                } else {
                                   //echo "Company Perlisting On Contact Detail OF Company";
                                    wpjobportal::$_common->validateEmployerArea();
                                    WPJOBPORTALincluder::getJSModel('company')->getCompanybyIdForView($wpjobportal_id);
                                    }
                                }
                            if(isset($wpjobportal_link) && isset($wpjobportal_linktext)){
                                wpjobportal::$_error_flag_message_for_link=$wpjobportal_link;
                                wpjobportal::$_error_flag_message_for_link_text=$wpjobportal_linktext;
                            }
                    } catch (Exception $wpjobportal_ex) {
                        wpjobportal::$_error_flag = true;
                        wpjobportal::$_error_flag_message = $wpjobportal_ex->getMessage();
                    }
                    break;
                case 'viewcompany':
                    try {
                        if (WPJOBPORTALincluder::getObjectClass('user')->isguest() && $wpjobportal_config_array['visitorview_emp_viewcompany'] != 1) {
                            $wpjobportal_link = wpjobportal::$_common->jsMakeRedirectURL('company', $wpjobportal_layout, 1);
                            $wpjobportal_linktext = esc_html(__('Login','wp-job-portal'));
                            wpjobportal::$_error_flag_message_for=1;
                            wpjobportal::$_error_flag_message_register_for=2;
                            throw new Exception(WPJOBPORTALLayout::setMessageFor(1 , $wpjobportal_link , $wpjobportal_linktext,1));

                        } elseif (!WPJOBPORTALincluder::getObjectClass('user')->isWPJOBPortalUser() && $wpjobportal_config_array['visitorview_emp_viewcompany'] != 1) {
                            $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'common', 'wpjobportallt'=>'newinwpjobportal'));
                            $wpjobportal_linktext = esc_html(__('Select role','wp-job-portal'));
                            wpjobportal::$_error_flag_message_for=9;
                            wpjobportal::$_error_flag_message_register_for=2;
                            throw new Exception(WPJOBPORTALLayout::setMessageFor(9 , $wpjobportal_link , $wpjobportal_linktext,1));

                        } else {
                            $wpjobportal_id = WPJOBPORTALrequest::getVar('wpjobportalid');
                            $wpjobportal_id = wpjobportal::$_common->parseID($wpjobportal_id);
                            $wpjobportal_expiryflag = WPJOBPORTALincluder::getJSModel('company')->getCompanyExpiryStatus($wpjobportal_id);
                            if (WPJOBPORTALincluder::getObjectClass('user')->isemployer()) {
                                if (WPJOBPORTALincluder::getJSModel('company')->getIfCompanyOwner($wpjobportal_id)) {
                                    $wpjobportal_expiryflag = true;
                                }
                            }
                            if ($wpjobportal_expiryflag == false) {
                                wpjobportal::$_error_flag_message = WPJOBPORTALLayout::setMessageFor(6,null,null,1);
                                wpjobportal::$_error_flag_message_for=6;
                                wpjobportal::$_error_flag = true;
                            } else {
                                WPJOBPORTALincluder::getJSModel('company')->getCompanybyIdForView($wpjobportal_id);
                                $wpjobportal_empflag = 1;
                            }
                        }
                        if(isset($wpjobportal_link) && isset($wpjobportal_linktext)){
                            wpjobportal::$_error_flag_message_for_link=$wpjobportal_link;
                            wpjobportal::$_error_flag_message_for_link_text=$wpjobportal_linktext;
                        }

                    } catch (Exception $wpjobportal_ex) {
                        wpjobportal::$_error_flag = true;
                        wpjobportal::$_error_flag_message = $wpjobportal_ex->getMessage();
                    }
                    break;
                default:
                    return;
            }
            if (WPJOBPORTALincluder::getObjectClass('user')->isemployer() || (('mycompanies' == $wpjobportal_layout)  || ('addcompany' == $wpjobportal_layout)) ) {
                if ($wpjobportal_empflag == 0) {
                    WPJOBPORTALLayout::setMessageFor(5);
                    wpjobportal::$_error_flag_message_for=5;
                    wpjobportal::$_error_flag = true;
                }
            }

            if(!isset($wpjobportal_module)){
                $wpjobportal_module = (wpjobportal::$_common->wpjp_isadmin()) ? 'page' : 'wpjobportalme';
                $wpjobportal_module = WPJOBPORTALrequest::getVar($wpjobportal_module, null, 'company');
            }
            $wpjobportal_module = wpjobportalphplib::wpJP_str_replace('wpjobportal_', '', $wpjobportal_module);
            if(is_numeric($wpjobportal_module)){
                $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme', null, 'company');
            }
            wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
            WPJOBPORTALincluder::include_file($wpjobportal_layout, $wpjobportal_module);
        }
    }

    function canaddfile($wpjobportal_layout) {
        $wpjobportal_nonce_value = WPJOBPORTALrequest::getVar('wpjobportal_nonce');
        if ( wp_verify_nonce( $wpjobportal_nonce_value, 'wpjobportal_nonce') ) {
            if (isset($_POST['form_request']) && $_POST['form_request'] == 'wpjobportal')
                return false;
            elseif (isset($_GET['action']) && $_GET['action'] == 'wpjobportaltask')
                return false;
            else{
                if(!is_admin() && strpos($wpjobportal_layout, 'admin_') === 0){
                    return false;
                }
                return true;
            }
        }
    }

    function approveQueueCompany() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_company_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_id = WPJOBPORTALrequest::getVar('id');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('company')->approveQueueCompanyModel($wpjobportal_id);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'company');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_company&wpjobportallt=companiesqueue"));
        wp_redirect($wpjobportal_url);
        die();
    }

    function approveQueueFeaturedCompany() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_company_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_id = WPJOBPORTALrequest::getVar('id');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('company')->approveQueueFeaturedCompanyModel($wpjobportal_id);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'company');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_company&wpjobportallt=companiesqueue"));
        wp_redirect($wpjobportal_url);
        die();
    }

    function rejectQueueCompany() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_company_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_id = WPJOBPORTALrequest::getVar('id');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('company')->rejectQueueCompanyModel($wpjobportal_id);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'company');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_company&wpjobportallt=companiesqueue"));
        wp_redirect($wpjobportal_url);
        die();
    }

    function rejectQueueFeatureCompany() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_company_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_id = WPJOBPORTALrequest::getVar('id');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('company')->rejectQueueFeatureCompanyModel($wpjobportal_id);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'company');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_company&wpjobportallt=companiesqueue"));
        wp_redirect($wpjobportal_url);
        die();
    }

    function approveQueueAllCompanies() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_company_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_id = WPJOBPORTALrequest::getVar('id');
        $wpjobportal_alltype = WPJOBPORTALrequest::getVar('objid');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('company')->approveQueueAllCompaniesModel($wpjobportal_id, $wpjobportal_alltype);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'company');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_company&wpjobportallt=companiesqueue"));
        wp_redirect($wpjobportal_url);
        die();
    }

    function rejectQueueAllCompanies() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_company_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_id = WPJOBPORTALrequest::getVar('id');
        $wpjobportal_alltype = WPJOBPORTALrequest::getVar('objid');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('company')->rejectQueueAllCompaniesModel($wpjobportal_id, $wpjobportal_alltype);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'company');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_company&wpjobportallt=companiesqueue"));
        wp_redirect($wpjobportal_url);
        die();
    }

    function savecompany() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_company_nonce') ) {
             die( 'Security check Failed' );
        }
        try{
            //requesting parameters
            $mcompany = WPJOBPORTALincluder::getJSModel('company');
            $wpjobportal_data = WPJOBPORTALrequest::get('post');
            $wpjobportal_companyid = (int) $wpjobportal_data['id'];
            $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();

            $wpjobportal_isnew = !$wpjobportal_companyid;
            if(!wpjobportal::$_common->wpjp_isadmin()){
                if(!$wpjobportal_isnew && !$mcompany->getIfCompanyOwner($wpjobportal_companyid)){
                    throw new Exception(WPJOBPORTAL_SAVE_ERROR, WPJOBPORTAL_COMPANY);
                }
                if($wpjobportal_isnew && !$mcompany->userCanAddCompany($wpjobportal_uid)){
                    throw new Exception(WPJOBPORTAL_SAVE_ERROR, WPJOBPORTAL_COMPANY);
                }
            }
            $wpjobportal_companyid = $mcompany->storeCompany($wpjobportal_data);
            if(!$wpjobportal_companyid){
                throw new Exception(WPJOBPORTAL_SAVE_ERROR, WPJOBPORTAL_COMPANY);
            }

            $wpjobportal_msg = WPJOBPORTALMessages::getMessage(WPJOBPORTAL_SAVED, WPJOBPORTAL_COMPANY);
            WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
            $redirecturl = wpjobportal::wpjobportal_redirectUrl('company.success',isset(wpjobportal::$_data['id']) ? wpjobportal::$_data['id'] : '');

        }catch(Exception $wpjobportal_ex){
            $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_ex->getMessage(), $wpjobportal_ex->getCode());
            WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
            $redirecturl = wpjobportal::wpjobportal_redirectUrl('company.fail');
        }
        wp_redirect($redirecturl);
        die();
    }

    function enforcedelete() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_company_nonce') ) {
             die( 'Security check Failed' );
        }

        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $wpjobportal_companyid = WPJOBPORTALrequest::getVar('id');
        $callfrom = WPJOBPORTALrequest::getVar('callfrom');

        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('company')->companyEnforceDeletes($wpjobportal_companyid);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'company');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        if ($callfrom == 1) {
            $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_company&wpjobportallt=companies"));
        } else {
            $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_company&wpjobportallt=companiesqueue"));
        }
        wp_redirect($wpjobportal_url);
        die();
    }

    function remove() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_company_nonce') ) {
             die( 'Security check Failed' );
        }
        $wpjobportal_data = WPJOBPORTALrequest::get('post');
        if (!isset($wpjobportal_data['callfrom']) || $wpjobportal_data['callfrom'] == null) {
            $wpjobportal_data['callfrom'] = $callfrom = WPJOBPORTALrequest::getVar('callfrom');
        }
        $wpjobportal_ids = WPJOBPORTALrequest::getVar('wpjobportal-cb');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('company')->deleteCompanies($wpjobportal_ids);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'company');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->_msgkey);
        if (wpjobportal::$_common->wpjp_isadmin()) {
            if ($wpjobportal_data['callfrom'] == 1) {
                $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_company&wpjobportallt=companies"));
            } elseif ($wpjobportal_data['callfrom'] == 2) {
                $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_company&wpjobportallt=companiesqueue"));
            }
        } else {
            $wpjobportal_url = esc_url_raw(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'mycompanies','wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid())));
        }
        wp_redirect($wpjobportal_url);
        die();
    }

    function addviewcontactdetail() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_company_nonce')){
             die( 'Security check Failed' );
        }
        $wpjobportal_id = WPJOBPORTALrequest::getVar('companyid');
        if($wpjobportal_id == ''){
            $wpjobportal_id = WPJOBPORTALrequest::getVar('wpjobportalid');
        }
        $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        WPJOBPORTALincluder::getJSModel('company')->addViewContactDetail($wpjobportal_id, $wpjobportal_uid);
        $wpjobportal_url = esc_url_raw(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$wpjobportal_id,'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid())));
        wp_redirect($wpjobportal_url);
        die();
    }
}

$WPJOBPORTALCompanyController = new WPJOBPORTALCompanyController();
?>
