<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALlayout {

    static function getNoRecordFound($message = null, $wpjobportal_linkarray = array()) {
        if($message == null){
            $message = esc_html(__('Could not find any matching results', 'wp-job-portal'));
        }
        $wpjobportal_html = '
                <div class="wjportal-error-messages-wrp">
                    <div class="wjportal-error-msg-image-wrp">
                        <img class="wjportal-error-msg-image" src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/errors/no-record.png" alt="'.esc_attr(__("no record", "wp-job-portal")).'" />
                    </div>
                    <div class="wjportal-error-msg-txt">
                        ' . $message . ' !...
                    </div>    
                    <div class="wjportal-error-msg-actions-wrp">';
                        if(!empty($wpjobportal_linkarray)){
                            foreach($wpjobportal_linkarray AS $wpjobportal_link){
                                if( isset($wpjobportal_link['text']) && $wpjobportal_link['text'] != ''){
                                    $wpjobportal_html .= '<a class="wjportal-error-msg-act-btn wjportal-error-msg-act-btn-back-btn" href="' . esc_url($wpjobportal_link['link']) . '">' . $wpjobportal_link['text'] . '</a>';
                                }
                            }
                        }
        $wpjobportal_html .=    '</div>
                </div>
        ';
        echo wp_kses($wpjobportal_html, WPJOBPORTAL_ALLOWED_TAGS);
    }

    static function getAdminPopupNoRecordFound() {
        $wpjobportal_html = '
                <div class="wjportal-error-messages-wrp">
                    <div class="wjportal-error-msg-image-wrp">
                        <img class="wjportal-error-msg-image" src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/errors/no-record.png" alt="'.esc_attr(__("no record", "wp-job-portal")).'" />
                    </div>
                    <div class="wjportal-error-msg-txt">
                        '.esc_html(__("No record found !...","wp-job-portal")).'
                    </div>
                </div>
        ';
        echo wp_kses($wpjobportal_html, WPJOBPORTAL_ALLOWED_TAGS);
    }

    static function getNoRecordFoundInSpecialCase() {
        if (is_admin()) {
            $wpjobportal_link = 'admin.php?page=wpjobportal_wpjobportal';
        } else {
            $wpjobportal_link = get_the_permalink();
        }
        $wpjobportal_html = '
                <div class="wjportal-error-messages-wrp">
                    <div class="wjportal-error-msg-image-wrp">
                        <img class="wjportal-error-msg-image" src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/errors/no-record.png" alt="'.esc_attr(__("no record", "wp-job-portal")).'" />
                    </div>
                    <div class="wjportal-error-msg-txt">
                        ' . esc_html(__('No record found !...', 'wp-job-portal')) . '
                    </div>
                </div>
        ';
        echo wp_kses($wpjobportal_html, WPJOBPORTAL_ALLOWED_TAGS);
    }

    static function getSystemOffline() {
        $offline_text = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('offline_text');
        $wpjobportal_html = '
                <div class="wjportal-main-up-wrapper">
                <div class="wjportal-error-messages-wrp wjportal-error-messages-style2">
                    <div class="wjportal-error-msg-image-wrp">
                        <img class="wjportal-error-msg-image" src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/errors/system-offline.png" alt="'.esc_attr(__("system offline", "wp-job-portal")).'" />
                    </div> 
                    <div class="wjportal-error-msg-txt wpjobportal-off-config-text ">
                        ' . $offline_text . '
                    </div>
                    <div class="wjportal-error-msg-txt2">
                        '.esc_html(__('Unfortunately sytem is offline for a bit of maintenance right now. But soon we will be up.','wp-job-portal')).'
                    </div>
                </div>
                </div>
        ';
        echo wp_kses($wpjobportal_html, WPJOBPORTAL_ALLOWED_TAGS);
    }

    static function getUserDisabledMsg() {
        $wpjobportal_html = '
            <div class="wjportal-main-up-wrapper">
                <div class="wjportal-error-messages-wrp">
                    <div class="wjportal-error-msg-image-wrp">
                        <img class="wjportal-error-msg-image" src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/errors/user-ban.png" alt="'.esc_attr(__("user ban", "wp-job-portal")).'" />
                    </div>
                    <div class="wjportal-error-msg-txt">
                        ' . esc_html(__('Your account is disabled, please contact system administrator !...', 'wp-job-portal')) . '
                    </div>
                </div>
            </div>
        ';
        echo wp_kses($wpjobportal_html, WPJOBPORTAL_ALLOWED_TAGS);
    }

    static function getUserGuest() {
        $wpjobportal_html = '<div class="wjportal-error-messages-wrp">
                    <div class="wjportal-error-msg-image-wrp">
                        <img class="wjportal-error-msg-image" src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/errors/login.png" alt="'.esc_attr(__("login", "wp-job-portal")).'" />
                    </div>
                    <div class="wjportal-error-msg-txt">
                        ' . esc_html(__('To Access This Page Please Login !...', 'wp-job-portal')) . '
                    </div>
                    <div class="wjportal-error-msg-actions-wrp">
                        <a class="wjportal-error-msg-act-btn wjportal-error-msg-act-login-btn" href="' . get_the_permalink() . '">' . esc_html(__('Back to control panel', 'wp-job-portal')) . '</a>
                    </div>
                </div>
        ';
        echo wp_kses($wpjobportal_html, WPJOBPORTAL_ALLOWED_TAGS);
    }

    static function getRegistrationDisabled() {
        $wpjobportal_html = '<div class="wjportal-error-messages-wrp">
                    <div class="wjportal-error-msg-image-wrp">
                        <img class="wjportal-error-msg-image" src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/errors/register-banned.png" alt="'.esc_attr(__("register banned", "wp-job-portal")).'" />
                    </div>
                    <div class="wjportal-error-msg-txt">
                        ' . esc_html(__('Registration is disabled by admin, please contact to system administrator !...', 'wp-job-portal')) . '
                    </div>
                </div>
        ';
        echo wp_kses($wpjobportal_html, WPJOBPORTAL_ALLOWED_TAGS);
    }

    static function setMessageFor($for, $wpjobportal_link = null, $wpjobportal_linktext = null, $return = 0) {
        $wpjobportal_image = null;
        $wpjobportal_description = '';
        $wpjobportal_login_register_link = 0;
        switch ($for) {
            case '1': // User is guest
                $wpjobportal_description = esc_html(__('You are not logged in', 'wp-job-portal'));
                $wpjobportal_login_register_link = 1;
                break;
            case '2': // User is job seeker
                $wpjobportal_description = esc_html(__('Jobseeker not allowed to perform this action', 'wp-job-portal'));
                break;
            case '3': // User is employer
                $wpjobportal_description = esc_html(__('Employer not allowed to perform this action', 'wp-job-portal'));
                break;
            case '4': // User is not allowed to do that b/c of credits
                $wpjobportal_description = esc_html(__('You do not have enough credits', 'wp-job-portal'));
                break;
            case '5': // When employer is disabled from configuration 
                $wpjobportal_description = esc_html(__('Employer is disabled by admin', 'wp-job-portal'));
                break;
            case '6': // When job/company/resume is not approved or expired 
                $wpjobportal_description = esc_html(__('The page you are looking for no longer exists', 'wp-job-portal'));
                break;
            case '7': // Employer not allowed in jobseeker area
                $wpjobportal_description = esc_html(__('Employer not allowed in job seeker area', 'wp-job-portal'));
                break;
            case '8': // Already loged in 
                $wpjobportal_description = esc_html(__('You are already logged in', 'wp-job-portal'));
                break;
            case '9': // User have no role
                $wpjobportal_description = esc_html(__('Please select your role', 'wp-job-portal'));
                break;
            case '10': // User have no role
                $wpjobportal_description = esc_html(__('You are not allowed', 'wp-job-portal'));
                break;
            case '15':
                $wpjobportal_description = esc_html(__('Buy New Package','wp-job-portal'));
                break;
            case '16':
                $wpjobportal_description = esc_html(__('You are not allowed to add more than one','wp-job-portal').' '.wpjobportal::wpjobportal_getVariableValue($wpjobportal_linktext).' '.esc_html__('contact adminstrator','wp-job-portal'));
                break;
            case '16':
                $wpjobportal_description = esc_html(__('Payment is not made against this job contact adminstrator','wp-job-portal'));
                break;
            case '18':
                $wpjobportal_description = esc_html(__('Addon Page Not Found','wp-job-portal'));
                break;
        }
        $wpjobportal_html = WPJOBPORTALlayout::getUserNotAllowed($wpjobportal_description, $wpjobportal_link, $wpjobportal_linktext, $wpjobportal_image, $return,$wpjobportal_login_register_link);
        if ($return == 1) {
            return $wpjobportal_html;
        }
    }

    static function getUserNotAllowed($wpjobportal_description, $wpjobportal_link, $wpjobportal_linktext, $wpjobportal_image, $return = 0,$wpjobportal_login_register_link = 0) {
        $wpjobportal_html = '<div class="wjportal-main-up-wrapper">
                <div class="wjportal-error-messages-wrp">
                    <div class="wjportal-error-msg-image-wrp">
                        <img class="wjportal-error-msg-image" src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/errors/not-allowed.png" alt="'.esc_attr(__("not allowed", "wp-job-portal")).'" />
                    </div>
                    <div class="wjportal-error-msg-txt">
                        ' . $wpjobportal_description . ' !...
                    </div>
                    <div class="wjportal-error-msg-actions-wrp">
                    ';
                        if($wpjobportal_linktext == null){
                            $wpjobportal_linktext = "Login";
                        }
                        if ($wpjobportal_link != null) {
                            $wpjobportal_lrlink = $wpjobportal_link;
                            if($wpjobportal_login_register_link == 1){
                                $wpjobportal_lrlink = WPJOBPORTALincluder::getJSModel('configuration')->getLoginRegisterRedirectLink($wpjobportal_link,'login');
                            }
                            $wpjobportal_html .= '<a class="wjportal-error-msg-act-btn wjportal-error-msg-act-login-btn" href="' . esc_url($wpjobportal_lrlink) . '">' . wpjobportal::wpjobportal_getVariableValue($wpjobportal_linktext) . '</a>';
                            if($wpjobportal_linktext == "Login"){
                                $wpjobportal_defaultUrl = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'user', 'wpjobportallt'=>'userregister','wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                                $wpjobportal_lrlink = WPJOBPORTALincluder::getJSModel('configuration')->getLoginRegisterRedirectLink($wpjobportal_defaultUrl,'register');
                                $wpjobportal_html .= '<a class="wjportal-error-msg-act-btn wjportal-error-msg-act-register-btn" href="' . esc_url($wpjobportal_lrlink) . '">' . esc_html(__("Register",'wp-job-portal')) . '</a>';
                            }
                        }
                    $wpjobportal_html .= '
                    </div>
                </div>
                </div>
        ';
        if ($return == 0) {
            echo wp_kses($wpjobportal_html, WPJOBPORTAL_ALLOWED_TAGS);
        } else {
            return $wpjobportal_html;
        }
    }

    static function getUserAlreadyLoggedin( $wpjobportal_link ) {
        $wpjobportal_html = '<div class="wjportal-main-up-wrapper">
                    <div class="wjportal-error-messages-wrp">
                        <div class="wjportal-error-msg-txt">
                            ' . esc_html(__('You are already logged in !...', 'wp-job-portal')) . '
                        </div>
                        <div class="wjportal-error-msg-actions-wrp">';
        $wpjobportal_html .= '<a class="wjportal-error-msg-act-btn wjportal-error-msg-act-login-btn" href="' . esc_url($wpjobportal_link). '">' . esc_html(__('Logout','wp-job-portal')) . '</a>';
        $wpjobportal_html .= '</div>
                </div>
                </div>
        ';
        echo wp_kses($wpjobportal_html, WPJOBPORTAL_ALLOWED_TAGS);
    }

}

?>