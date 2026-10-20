<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
class WPJOBPORTALtellfriendMODEL{
    public $class_prefix = '';

    function __construct(){
        if(wpjobportal::$wpjobportal_theme_chk == 1){
            $this->class_prefix = 'wpj-jp';
        } else {
            $this->class_prefix = 'wjportal';
        }
    }

    function sendmailtofriend($themecall = null) {
        $nonce = WPJOBPORTALrequest::getVar('js_nonce');
        if (! wp_verify_nonce( $nonce, 'wp-job-portal-nonce') ) {
            die( 'Security check Failed' );
        }
        $yourname = WPJOBPORTALrequest::getVar('yourname');
        $youremail = WPJOBPORTALrequest::getVar('youremail');
        $message = WPJOBPORTALrequest::getVar('message');
        $femail1 = WPJOBPORTALrequest::getVar('femail1');
        $femail2 = WPJOBPORTALrequest::getVar('femail2');
        // $femail3 = WPJOBPORTALrequest::getVar('femail3');
        // $femail4 = WPJOBPORTALrequest::getVar('femail4');
        // $femail5 = WPJOBPORTALrequest::getVar('femail5');
        $jobtitle = WPJOBPORTALrequest::getVar('jobtitle');
        $jobid = WPJOBPORTALrequest::getVar('jobid');
      	$recaptchaToken_v3 = WPJOBPORTALrequest::getVar('recaptchaToken_v3');

        if(!is_numeric($jobid)) return false;

        $config_array = wpjobportal::$_config->getConfigByFor('captcha');

        if(WPJOBPORTALincluder::getObjectClass('user')->isguest() && $config_array['tell_a_friend_captcha'] == 1){
        // captcha check code
        // to handle problem of unseting session value of cpatcha in actual check
        // if ($config_array['captcha_selection'] != 1) { // Google recaptcha
        //     if(!isset($_SESSION['wpjobportal_rot13']) && !isset($_SESSION['wpjobportal_spamcheckresult'])){
        //         $array = array('status' => 0,'message'=> __('Incorrect Captcha','wp-job-portal'));
        //         return wp_json_encode($array);
        //     }
        //     if ($_SESSION['wpjobportal_rot13'] == 1) {
        //         $spamcheckresult = wpjobportalphplib::wpJP_safe_decoding(wpjobportalphplib::wpJP_str_rot13(sanitize_key($_SESSION['wpjobportal_spamcheckresult'])));
        //     } else {
        //         $spamcheckresult = wpjobportalphplib::wpJP_safe_decoding(sanitize_key($_SESSION['wpjobportal_spamcheckresult']));
        //     }
        //     $spamcheck = WPJOBPORTALrequest::getVar(sanitize_key($_SESSION['wpjobportal_spamcheckid']), '', 'post');
        //     if (!is_numeric($spamcheckresult) || $spamcheckresult != $spamcheck) {
        //         $array = array('status' => 0,'message'=> __('Incorrect Captcha','wp-job-portal'));
        //         return wp_json_encode($array);
        //     }
        // }
        // end
        if (!WPJOBPORTALincluder::getJSModel('job')->captchaValidate(1)) {
        //if (!WPJOBPORTALincluder::getJSModel('job')->captchaValidate(1,$recaptchaToken_v3)) {
            if($themecall != null){
                return 2;
            }else{
                $array = array('status' => 0,'message'=> __('Incorrect Captcha','wp-job-portal'));
                return wp_json_encode($array);
            }

        }
        }
        $query = "SELECT cat.cat_title,company.name
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                    ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON company.id = job.companyid
                    JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON cat.id = job.jobcategory
                    WHERE job.id = " . (int) ($jobid);
        $result = wpjobportal::$_db->get_row($query);
        $template = WPJOBPORTALincluder::getJSModel('emailtemplate')->getTemplateForEmail('job-to-friend');
        $msgSubject = $template->subject;
        $msgBody = $template->body;
        //$link = '<a href="'.wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'viewjob', 'wpjobportalid'=>$jobid,'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid())).'">'. __('Click Here To View Job','wp-job-portal') .'</a>';
        $link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'viewjob', 'wpjobportalid'=>$jobid,'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
        $sitename = get_bloginfo();
        $siteTitle = wpjobportal::$_config->getConfigValue('title');
        $matcharray = array(
            '{SENDER_NAME}' => $yourname,
            '{SITE_NAME}' => $sitename,
            '{JOB_TITLE}' => $jobtitle,
            '{JOB_CATEGORY}' => $result->cat_title,
            '{COMPANY_NAME}' => $result->name,
            '{CLICK_HERE_TO_VISIT}' => $link,
            '{SITETITLE}' => $siteTitle,
            '{CURRENT_YEAR}' => gmdate('Y'),
            '{EMAIL}' => '&nbsp;',

            '{SENDER_MESSAGE}' => $youremail
        );// email variable in this case is array to handle mulitple case of tell a friend so we can not set the variable to a value.
        $receveremail = array();
        $receveremail[] = $femail1;
        if ($femail2 != '')
            $receveremail[] = $femail2;
        // if ($femail3 != '')
        //     $receveremail[] = $femail3;
        // if ($femail4 != '')
        //     $receveremail[] = $femail4;
        // if ($femail5 != '')
        //     $receveremail[] = $femail5;
        WPJOBPORTALincluder::getJSModel('emailtemplate')->replaceMatches($msgSubject, $matcharray);
        WPJOBPORTALincluder::getJSModel('emailtemplate')->replaceMatches($msgBody, $matcharray);
        WPJOBPORTALincluder::getJSModel('emailtemplate')->sendEmail($receveremail, $msgSubject, $msgBody, $youremail, $yourname, '', '');
        // added code to show close button
        if (wpjobportal::$wpjobportal_theme_chk == 1) {
            $html = '<div id="wpj-jp-notification-ok"><label id="wpj-jp-popup_message"><img src="'.WPJOBPORTAL_PLUGIN_URL .'includes/images/approve.png">'.__('Email has been sent to friend[s]','wp-job-portal').'</label><button class="applynow-closebutton" onclick="wpjobportalClosePopup(1);" ><img src="'.WPJOBPORTAL_PLUGIN_URL.'/includes/images/popupcloseicon.png"/>'.__('Close','wp-job-portal').'</button></div>';
        } else {
            $html = '<div id="notification-ok"><label id="popup_message"><img src="'.WPJOBPORTAL_PLUGIN_URL .'includes/images/approve.png">'.__('Email has been sent to friend[s]','wp-job-portal').'</label><button class="applynow-closebutton" onclick="wpjobportalClosePopup(1);" ><img src="'.WPJOBPORTAL_PLUGIN_URL.'/includes/images/popupcloseicon.png"/>'.__('Close','wp-job-portal').'</button></div>';
        }
        $array = array('status' => 1,'message'=> $html);
        return wp_json_encode($array);
    }

    function getTellaFriend() {
        $nonce = WPJOBPORTALrequest::getVar('js_nonce');
        if (! wp_verify_nonce( $nonce, 'wp-job-portal-nonce') ) {
            die( 'Security check Failed' );
        }
        $jobid = WPJOBPORTALrequest::getVar('jobid');
        //if(!is_numeric($jobid)) return false;
        $query = "SELECT job.title,job.id FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job WHERE job.id = '".esc_sql($jobid)."' ";
        $job = wpjobportal::$_db->get_row($query);
        if (wpjobportal::$wpjobportal_theme_chk == 1) {
            $result = $this->getJobByid($jobid);
            $job = $result[0];
        }
        $title = __('Tell A Friend', 'wp-job-portal');
        $content =  '';
        $listing_fields = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldOrderingDataForListing(2); // get published field titles
        if (wpjobportal::$wpjobportal_theme_chk == 1) {
            // Pop up detail data For Job(Extra Detail)
            $content .=  '<div class="wpj-jp-popup-cnt-wrp">';
            $content .=  '<i class="fas fa-times wpj-jp-popup-close-icon" data-dismiss="modal"></i>';
            $content .=  '<div class="wpj-jp-popup-right">';
            $content .=  '<div class="wpj-jp-popup-list">';
            if($job->companyid != ""){
                $path = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('employer');
                if ($job->logofilename != "") {
                    $wpdir = wp_upload_dir();
                    $data_directory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
                    $path = $wpdir['baseurl'] . '/' . $data_directory . '/data/employer/comp_' . $job->companyid . '/logo/' . $job->logofilename;
                }
                if(in_array('multicompany', wpjobportal::$_active_addons)){
                    $mod = "multicompany";
                }else{
                    $mod = "company";
                }
                $company_listing_fields = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldOrderingData(1);

                $content .= '<div class="wpj-jp-popup-list-logo">';
                if(isset($company_listing_fields['logo'])){
                    $content .=     '<a href='. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>$mod, 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$job->companyid,'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()))) .' title='. esc_attr__('Company Logo','job-portal-theme').'>';
                    $content .=         '<img src='. esc_attr($path) .' alt="'.esc_attr__('Company Logo','job-portal-theme').'" >';
                    $content .=     '</a>';
                }

                $content .= '</div>';
            }
            $content .= '<div class="wpj-jp-popup-list-cnt-wrp">';
            $content .=     '<div class="wpj-jp-popup-list-cnt">';
            if(isset($listing_fields['jobtype'])){
                $content .=          '<span class="wpj-jp-job-type" style="color:'.esc_attr($job->jobtypecolor).'">';
                $content .=             wpjobportal::wpjobportal_getVariableValue($job->jobtypetitle, 'job-portal-theme');
                $content .=          '</span>';
            }

            $content .=     '</div>';
            $content .=     '<div class="wpj-jp-popup-list-cnt">';
            $content .=         '<a class="wpj-jp-popup-list-comp-tit" href='. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>$mod, 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$job->companyid,'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()))).' title="'.esc_attr__('Company Name','job-portal-theme').'">'. wpjobportal::wpjobportal_getVariableValue($job->companyname, 'job-portal-theme').'</a>';
            $content .=     '</div>';
            $content .=     '<div class="wpj-jp-popup-list-cnt">';
            $content .=         '<h5 class="wpj-jp-popup-list-tit">
                                    <a href='.esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'viewjob', 'wpjobportalid'=>$job->jobaliasid,'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()))).' title="'.esc_attr__('Job Title','job-portal-theme').'">'.wpjobportal::wpjobportal_getVariableValue($job->title, 'job-portal-theme').'</a>
                                </h5>';
            $content .=     '</div>';
            $content .=     '<div class="wpj-jp-popup-list-cnt">';
            $content .=         '<ul>';
                                        if(isset($job) && !empty($job->cat_title)){
                                            if(isset($listing_fields['jobcategory'])){// to check if field is published
                $content .=             '<li>';

                $content .=                     '<span class="wpj-jp-popup-list-meta-tit">';
                $content .=                         wpjobportal::wpjobportal_getVariableValue($listing_fields['jobcategory']). ':';
                $content .=                     '</span>';
                $content .=                      wpjobportal::wpjobportal_getVariableValue($job->cat_title, 'job-portal-theme');
                $content .=             '</li>';
                                            }
                                        }

                                // With no figure on file this printed "Salary: - Per Month".
                                $wpjobportal_salaryview = (string) wpjobportal::$_common->getSalaryRangeView($job->salarytype, $job->salarymin, $job->salarymax,$job->currency);
                                if(isset($listing_fields['jobsalaryrange']) && $wpjobportal_salaryview !== ''){// to check if field is published
            $content .=             '<li>';
            $content .=                  '<span class="wpj-jp-popup-list-meta-tit">';
            $content .=                       wpjobportal::wpjobportal_getVariableValue($listing_fields['jobsalaryrange']). ':';
            $content .=                   '</span>';
            $content .=                    $wpjobportal_salaryview;
                                           if($job->salarytype==3 || $job->salarytype==2) {
            $content .=                      ' - ' .wpjobportal::wpjobportal_getVariableValue($job->srangetypetitle, 'job-portal-theme');
                                           }
            $content .=             '</li>';
                                }
                                if(isset($listing_fields['city'])){// to check if field is published
            $content .=             '<li>';
                                        if(isset($job) && !empty($job->location)){
            $content .=                     '<span class="wpj-jp-popup-list-meta-tit">';
            $content .=                         wpjobportal::wpjobportal_getVariableValue($listing_fields['city']). ':';
            $content .=                     '</span>';
            $content .=                     wpjobportal::wpjobportal_getVariableValue($job->location, 'job-portal-theme');
                                        }
            $content .=             '</li>';
                                }
            $content .=         '</ul>';
            $content .=     '</div>';
            $content .= '</div>';
            $content .= '</div>';
            $content .= '</div>';
            // Pop up detail data For Job Ends there
            $content .= '<div class="wpj-jp-popup-left">';
            $content .=  '<h3 class="wpj-jp-popup-heading">'.$title.'</h3>';
            $labelclass = "sr-only";
        }
        $labelclass = "sr-only";
        $content .= '<div class="'.$this->class_prefix.'-popup-field-wrp">';

        $content .= '<div class="'.$this->class_prefix.'-popup-field '.$this->class_prefix.'-popup-field-job-title ">';
        $content .= '<label class="'.$labelclass.'" for="jobtitle">' . __('Job Title', 'wp-job-portal') . '</label>';
        $content .= '<input type="text"  id="jobtitle" name="jobtitle" value="' . htmlspecialchars($job->title) . '" disabled/>';
        $content .= '<input type="hidden" disable="disabled" id="jobid" name="jobid" value="' . $job->id . '"/>';
        $content .= '</div>';

        $content .= '<div class="'.$this->class_prefix.'-popup-field">';
        $content .= '<label class="'.$labelclass.'" for="yourname">' . __('Your Name', 'wp-job-portal') . '&nbsp;<font color="red">*</font></label>';
        $content .= '<input type="text" id="yourname" name="yourname" placeholder="' . __('Your Name', 'wp-job-portal') . '" />';
        $content .= '</div>';
        $content .= '<div class="'.$this->class_prefix.'-popup-field">';
        $content .= '<label class="'.$labelclass.'" for="youremail">' . __('Your Email', 'wp-job-portal') . '&nbsp;<font color="red">*</font></label>';
        $content .= '<input type="text" id="youremail" name="youremail" placeholder="' . __('Your Email', 'wp-job-portal') . '" />';
        $content .= '</div>';


        $content .= '<div class="'.$this->class_prefix.'-popup-field-email-title">';
        $content .=  __('Friend Email', 'wp-job-portal');
        $content .= '</div>';


        $content .= '<div class="'.$this->class_prefix.'-popup-field">';
        $content .= '<label class="'.$labelclass.'" for="femail1">' . __('Friend Email', 'wp-job-portal') . '&nbsp;<font color="red">*</font></label>';
        $content .= '<input type="text" id="femail1" name="femail1" placeholder="' . __('Friend Email', 'wp-job-portal') . '" />';
        $content .= '</div>';
        $content .= '<div class="'.$this->class_prefix.'-popup-field">';
        $content .= '<label class="'.$labelclass.'" for="femail2">' . __('Friend Email', 'wp-job-portal') . '</label>';
        $content .= '<input type="text" id="femail2" name="femail2" placeholder="' . __('Friend Email', 'wp-job-portal') . '" />';
        $content .= '</div>';
        $content .= '<div class="'.$this->class_prefix.'-popup-field '.$this->class_prefix.'-popup-fullwidth">
                        <label class="'.$labelclass.'" for="message">' . __('Message','wp-job-portal') . '&nbsp;<font color="red">*</font></label>
                        <textarea id="message" name="message" placeholder="' . __('Message', 'wp-job-portal') . '"></textarea>
                    </div>';
        $content .= '</div>';

        if(WPJOBPORTALincluder::getObjectClass('user')->isguest()){
            $config_array = wpjobportal::$_config->getConfigByFor('captcha');
            if ($config_array['tell_a_friend_captcha'] == 1) {
                $captchaclass = "";
                if (wpjobportal::$wpjobportal_theme_chk == 1) {
                    $captchaclass = "wpj-jp-popup-field";
                }
                $content .= '<div class="quickviewfullwidth '.$captchaclass.' tell-a-friend-captcha-wrapper">';
                    // A label only over something visible: the built-in check and reCAPTCHA v3 show nothing.
                    $captcha_provider = WPJOBPORTAL_Captcha_Service::effective_provider();
                    if ($captcha_provider === WPJOBPORTAL_Captcha_Service::PROVIDER_TURNSTILE || ($captcha_provider === WPJOBPORTAL_Captcha_Service::PROVIDER_RECAPTCHA && !WPJOBPORTAL_Captcha_Service::is_recaptcha_v3())) {
                        $content .= '<label for="message" class="'.$labelclass.'">' . __('Captcha', 'wp-job-portal') .'</label>';
                    }
                    $captcha = new WPJOBPORTALcaptcha;
                    $content .= $captcha->getCaptchaForForm();
                $content .=  '</div>';
            }
        }
        $btnclass1 = '';
      	$google_recaptcha_v3 = false;
        if (wpjobportal::$wpjobportal_theme_chk == 1) {
            $btnclass1 = "wpj-jp-visitor-msg-primary-btn";
            $content .= ' <div class="'.$this->class_prefix.'-visitor-msg-btn-wrp"> ';
            if($google_recaptcha_v3 == false){
                $content .= ' <a href="#" class="'.$this->class_prefix.'-visitor-msg-btn '.$btnclass1.'" id="apply-now-btn" onclick="sendEmailToFriend(1);">' . __('Send To Friends', 'wp-job-portal') . '</a>';
            }else{
                $content .= ' <a href="#" data-sitekey="'. esc_attr($config_array['recaptcha_publickey']).'" data-callback="onSubmitTellAFriendTheme" data-action="submit" class="'.$this->class_prefix.'-visitor-msg-btn '.$btnclass1.'" id="apply-now-btn" >' . __('Send to friends', 'wp-job-portal') . '</a>';

            }
            $content .= '
                    </div>';
        } else {
            $content .= '<div class="wjportal-visitor-msg-btn-wrp">';
            if($google_recaptcha_v3 == false){
                $content .= '<a href="#" class="wjportal-visitor-msg-btn" id="apply-now-btn" onclick="sendEmailToFriend();">' . __('Send To Friends', 'wp-job-portal') . '</a>';
            }else{
                $content .= '<a href="#" data-sitekey="'. esc_attr($config_array['recaptcha_publickey']).'" data-callback="onSubmitTellAFriendPlugin" data-action="submit" class="wjportal-visitor-msg-btn g-recaptcha" id="apply-now-btn" >' . __('Send to friends', 'wp-job-portal') . '</a>';
            }
            $content .= '</div>';
        }
        
        $content .= '</div>';
        if (wpjobportal::$wpjobportal_theme_chk == 1) {
            $content .= '</div>'; /// end left wrp
            $content .= '</div>'; /// end cnt wrp
        }
        $array = array('title' => $title, 'content' => $content);
        return wp_json_encode($array);
    }

    function getJobByid($jobid){
        if(!is_numeric($jobid))
            return false;
        $query = "SELECT job.endfeatureddate,job.id,job.uid,job.title,job.isfeaturedjob,job.serverid,job.noofjobs,job.city,job.status,job.currency,
                CONCAT(job.alias,'-',job.id) AS jobaliasid,job.created,job.serverid,company.name AS companyname,company.id AS companyid,company.logofilename,CONCAT(company.alias,'-',company.id) AS compnayaliasid,job.salarytype,job.salarymin,job.salarymax,salaryrangetype.title AS salarydurationtitle,
                cat.cat_title, jobtype.title AS jobtypetitle,salaryrangetype.title AS srangetypetitle,jobtype.color AS jobtypecolor,
                (SELECT count(jobapply.id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` AS jobapply
                 WHERE jobapply.jobid = job.id) AS resumeapplied ,job.params,job.startpublishing,job.stoppublishing
                 ,LOWER(jobtype.title) AS jobtypetit
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON company.id = job.companyid
                JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON cat.id = job.jobcategory
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON jobtype.id = job.jobtype
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_salaryrangetypes` AS salaryrangetype ON salaryrangetype.id = job.salaryduration
                WHERE  job.id = " . (int) ($jobid);

        $results = wpjobportaldb::get_results($query);
        $data = array();
        foreach ($results AS $d) {
            $d->location = WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($d->city);
            $data[] = $d;
        }
        $results = $data;
       return $results;
    }
}
