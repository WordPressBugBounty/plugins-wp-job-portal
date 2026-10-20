<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALjobSearchController {

    private $_msgkey;

    function __construct() {

        self::handleRequest();

        $this->_msgkey = WPJOBPORTALincluder::getJSModel('jobsearch')->getMessagekey();
    }

    function handleRequest() {

        // Defaults to the search page. It defaulted to jobsavesearch, so a
        // request that named no layout landed on the route that fataled - and
        // with that route gone it would render nothing at all. jobsearch is the
        // only layout this controller still serves.
        $wpjobportal_layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'jobsearch');
        if (self::canaddfile($wpjobportal_layout)) {
            $wpjobportal_config_array = WPJOBPORTALincluder::getJSModel('configuration')->getConfigByFor('jscontrolpanel');
            switch ($wpjobportal_layout) {
                case 'jobsearch':
                    try {
                        if (WPJOBPORTALincluder::getObjectClass('user')->isjobseeker() || WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('visitorview_js_jobsearch') == 1 ) {
                            WPJOBPORTALincluder::getJSModel('jobsearch')->getJobSearchOptions();
                        } else {
                            if (WPJOBPORTALincluder::getObjectClass('user')->isemployer()) {
                                wpjobportal::$_error_flag_message_for=3;
                                throw new Exception(WPJOBPORTALLayout::setMessageFor(3,null,null,1));
                            } elseif (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
                                $wpjobportal_link = WPJOBPORTALincluder::getJSModel('common')->jsMakeRedirectURL('jobsearch', $wpjobportal_layout, 1);
                                $wpjobportal_linktext = esc_html(__('Login','wp-job-portal'));
                                wpjobportal::$_error_flag_message_for=1;
                                wpjobportal::$_error_flag_message_register_for=1;
                                throw new Exception(WPJOBPORTALLayout::setMessageFor(1 , $wpjobportal_link , $wpjobportal_linktext,1));

                            } elseif (!WPJOBPORTALincluder::getObjectClass('user')->isWPJOBPortalUser()) {
                                $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'common', 'wpjobportallt'=>'newinwpjobportal', 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                                $wpjobportal_linktext = esc_html(__('Select Role','wp-job-portal'));
                                wpjobportal::$_error_flag_message_for=9;
                                throw new Exception(WPJOBPORTALLayout::setMessageFor(9 , $wpjobportal_link , $wpjobportal_linktext,1));
                            }
                            if(isset($wpjobportal_link) && isset($wpjobportal_linktext)){
                                wpjobportal::$_error_flag_message_for_link = $wpjobportal_link;
                                wpjobportal::$_error_flag_message_for_link_text = $wpjobportal_linktext;
                            }
                        }
                    } catch (Exception $wpjobportal_ex) {
                        wpjobportal::$_error_flag = true;
                        wpjobportal::$_error_flag_message = $wpjobportal_ex->getMessage();
                    }

                    break;
                /*
                 * The searches this job seeker has kept.
                 *
                 * This layout existed before and fataled: it called
                 * getMyJobSaveSearchbyUid(), a name that appeared exactly once
                 * across Core and all 41 add-ons - on the line calling it. It was
                 * removed, and is restored here now that there is something behind
                 * it: a jobsearches table, a model that reads it, and a menu entry
                 * that leads to it.
                 */
                case 'jobsavesearch':
                    try {
                        if (WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()) {
                            $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
                            WPJOBPORTALincluder::getJSModel('jobsearch')->getMyJobSearchesbyUid($wpjobportal_uid);
                        } elseif (WPJOBPORTALincluder::getObjectClass('user')->isemployer()) {
                            wpjobportal::$_error_flag_message_for = 3;
                            throw new Exception(WPJOBPORTALLayout::setMessageFor(3, null, null, 1));
                        } else {
                            // A saved search belongs to somebody, so there is
                            // nothing to show a guest but a way to sign in.
                            $wpjobportal_link = WPJOBPORTALincluder::getJSModel('common')->jsMakeRedirectURL('jobsearch', $wpjobportal_layout, 1);
                            $wpjobportal_linktext = esc_html(__('Login', 'wp-job-portal'));
                            wpjobportal::$_error_flag_message_for = 1;
                            wpjobportal::$_error_flag_message_register_for = 1;
                            throw new Exception(WPJOBPORTALLayout::setMessageFor(1, $wpjobportal_link, $wpjobportal_linktext, 1));
                        }
                    } catch (Exception $wpjobportal_ex) {
                        wpjobportal::$_error_flag = true;
                        wpjobportal::$_error_flag_message = $wpjobportal_ex->getMessage();
                        if (isset($wpjobportal_link) && isset($wpjobportal_linktext)) {
                            wpjobportal::$_error_flag_message_for_link = $wpjobportal_link;
                            wpjobportal::$_error_flag_message_for_link_text = $wpjobportal_linktext;
                        }
                    }
                    break;
                default:
                    return;
            }
            $wpjobportal_module = (wpjobportal::$_common->wpjp_isadmin()) ? 'page' : 'wpjobportalme';
            $wpjobportal_module = WPJOBPORTALrequest::getVar($wpjobportal_module, null, 'jobsearch');
            $wpjobportal_module = wpjobportalphplib::wpJP_str_replace('wpjobportal_', '', $wpjobportal_module);
            if(is_numeric($wpjobportal_module)){
                $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme', null, 'jobsearch');
            }
            wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
            WPJOBPORTALincluder::include_file($wpjobportal_layout, $wpjobportal_module);
        }
    }

    /**
     * Keep the search that produced this page.
     *
     * @return void Redirects.
     */
    function saveJobSearch() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($wpjobportal_nonce, 'wpjobportal_job_search_nonce')) {
            die('Security check Failed');
        }

        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('jobsearch')->storeJobSearch();
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'jobsearch');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'], $this->_msgkey);

        // Back to the results they were reading, not to a list of names: the
        // page they saved is the page they still want to look at.
        $wpjobportal_url = wpjobportal::wpjobportal_makeUrl(array(
            'wpjobportalme' => 'job',
            'wpjobportallt' => 'jobs',
            'wpjobportalpageid' => wpjobportal::wpjobportal_getPageid(),
        ));

        wp_safe_redirect($wpjobportal_url);
        die();
    }

    /**
     * Forget a saved search.
     *
     * @return void Redirects.
     */
    function removeSavedJobSearch() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($wpjobportal_nonce, 'wpjobportal_job_search_nonce')) {
            die('Security check Failed');
        }

        $wpjobportal_id = WPJOBPORTALrequest::getVar('wpjobportalid');
        $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();

        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('jobsearch')->deleteJobSearch($wpjobportal_id, $wpjobportal_uid);
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage($wpjobportal_result, 'jobsearch');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'], $this->_msgkey);

        $wpjobportal_url = wpjobportal::wpjobportal_makeUrl(array(
            'wpjobportalme' => 'jobsearch',
            'wpjobportallt' => 'jobsavesearch',
            'wpjobportalpageid' => wpjobportal::wpjobportal_getPageid(),
        ));

        wp_safe_redirect($wpjobportal_url);
        die();
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


}

$WPJOBPORTALjobSearchController = new WPJOBPORTALjobSearchController();
?>
