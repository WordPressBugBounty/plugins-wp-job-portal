<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALshortlistModel {

    public $class_prefix = '';

    function __construct(){
        if(wpjobportal::$wpjobportal_theme_chk == 1){
            $this->class_prefix = 'wpj-jp';
        }elseif(wpjobportal::$wpjobportal_theme_chk == 2){
            $this->class_prefix = 'jsjb-jh';
        } else {
            $this->class_prefix = 'wjportal';
        }
    }

    private function getShortlistCookieArray() {
        if (!WPJOBPORTALrequest::hasCookie('wp_wpjobportal_cookie')) {
            return array();
        }
        $value = WPJOBPORTALrequest::rawCookie('wp_wpjobportal_cookie');
        $array = json_decode($value, true);
        if (!is_array($array)) {
            $array = @unserialize($value, array('allowed_classes' => false));
        }
        if (!is_array($array)) {
            return array();
        }
        $safe_array = array();
        foreach ($array as $slid => $jobid) {
            $safe_slid = absint($slid);
            $safe_jobid = absint($jobid);
            if ($safe_slid > 0 && $safe_jobid > 0) {
                $safe_array[$safe_slid] = $safe_jobid;
            }
        }
        return $safe_array;
    }

    function getShortlistedJobs($uid,$cpcall=null) {
        WPJOBPORTALincluder::getJSModel('job')->getOrdering();
        $subquery = '';
        if (is_numeric($uid) && $uid != 0) {
            $subquery .= " AND ( sl.uid = " . (int) ($uid);
        }
        $shortlistedid_cookie = array_filter(array_map('absint', (array) $this->getShortListedIDArray()));
        if (!empty($shortlistedid_cookie)) {
            if (!is_numeric($uid)) {
                $subquery .= ' AND ';
            } else {
                $subquery .= ' OR ';
            }
            $subquery .= ' sl.id IN(' . implode(', ', $shortlistedid_cookie) . ')';
        }
        if (is_numeric($uid) && $uid != 0) {
            $subquery .= ' ) ';
        }
        if (empty($shortlistedid_cookie) && !is_numeric($uid)) { // Visitor is not eligble to view shortlisted jobs
            return false;
        }
        $query = "SELECT count(sl.id) AS total
                 FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobshortlist` AS sl
                 JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job ON job.id = sl.jobid
                 WHERE DATE(job.startpublishing) <= CURDATE() AND DATE(job.stoppublishing) >= CURDATE() ";
        $query .= $subquery;
        $query .= ' GROUP BY sl.jobid ';
        $total = wpjobportaldb::get_results($query);
        $counttotal =  count($total);
        wpjobportal::$_data['total'] = $counttotal;

        wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($counttotal);

        $query = "SELECT sl.id AS slid, sl.comments,sl.rate,sl.status,cat.cat_title,company.id AS companyid,company.name AS companyname,company.logofilename, CONCAT(company.alias,'-',company.id) AS companyaliasid,company.url,job.currency,
                    job.id AS jobid,job.id as id,job.title,job.created,job.city,CONCAT(job.alias,'-',job.id) AS jobaliasid,job.noofjobs,job.isfeaturedjob,job.salarymax,job.salarymin,job.salarytype,srtype.title AS srangetypetitle,
                     jobtype.title AS jobtypetitle,LOWER(jobtype.title) AS jobtype,job.params,job.jobapplylink,job.joblink,job.endfeatureddate,job.isfeaturedjob,job.status,job.startpublishing
                     ,job.stoppublishing,job.description,jobtype.color as jobtypecolor, job.is_urgent, job.workplace_type, job.jobcategory, job.jobtype, job.salaryduration
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobshortlist` AS sl
                    JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job ON job.id = sl.jobid
                    ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON company.id = job.companyid
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON cat.id = job.jobcategory
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_salaryrangetypes` AS srtype ON srtype.id = job.salaryduration
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON jobtype.id = job.jobtype
                    WHERE DATE(job.startpublishing) <= CURDATE() AND DATE(job.stoppublishing) >= CURDATE() ";
        $query .= $subquery;
        $query .= ' GROUP BY sl.jobid ';
        $query.= " ORDER BY ". wpjobportal::$_ordering;
        if($cpcall==1){
            $query.=" LIMIT 5";
        }else{
            $query.=" LIMIT " . WPJOBPORTALpagination::$_offset . "," . WPJOBPORTALpagination::$_limit;
        }
        $results = wpjobportaldb::get_results($query);
        $data = array();
        foreach ($results AS $d) {
            $d->location = WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($d->city);
            $data[] = $d;
        }
        $results = $data;
        if($cpcall==1) return $data;
        // ---  JOB SCORING INTEGRATION START ---
        if(in_array('smartmatching', wpjobportal::$_active_addons)){
            $show_match_score_job_list  = wpjobportal::$_config->getConfigurationByConfigName('show_match_score_job_list');
            if (WPJOBPORTALincluder::getObjectClass('user')->isjobseeker() && !empty($show_match_score_job_list) && !empty($data)) {
                $data = WPJOBPORTALincluder::getJSModel('smartmatching')->assignScoreToJobs($data);
            }
        }
        wpjobportal::$_data[0] = $data;
        wpjobportal::$wpjobportal_data['fields'] = wpjobportal::$_wpjpfieldordering->getFieldsOrderingforForm(2);
        wpjobportal::$_data['fields'] = wpjobportal::$_wpjpfieldordering->getFieldsOrderingforForm(2);
        wpjobportal::$_data['config'] = wpjobportal::$_config->getConfigByFor('jobapply');
        if(WPJOBPORTALincluder::getObjectClass('user')->isjobseeker() && !empty(wpjobportal::$_data[0])){
            if(WPJOBPORTALincluder::getJSmodel('jobapply')->checkAlreadyAppliedJob($uid,$data[0]->jobid)){
                wpjobportal::$wpjobportal_data['isapplied'] = true;
            }
        }

        return;
    }

//////Custom Table Setting.
    function saveJobShortList($themecall=null) {
        $nonce = WPJOBPORTALrequest::getVar('js_nonce');
        if (! wp_verify_nonce( $nonce, 'wp-job-portal-nonce') ) {
            die( 'Security check Failed' );
        }
        $data = array();
        $data['id'] = WPJOBPORTALrequest::getVar('slid');
        $data['jobid'] = WPJOBPORTALrequest::getVar('jobid');
        $data['comments'] = WPJOBPORTALrequest::getVar('comments');
        $data['rate'] = WPJOBPORTALrequest::getVar('rate');
        $data['created'] = date_i18n('Y-m-d H:i:s');
        $data['status'] = 1;
        $data['uid'] = WPJOBPORTALincluder::getObjectClass('user')->uid();
        $row = WPJOBPORTALincluder::getJSTable('jobshortlist');
        $result = array();
        $return = WPJOBPORTAL_SAVED;
        if (!$row->bind($data)) {
            $return = WPJOBPORTAL_SAVE_ERROR;
        }
        if (!$row->store()) {
            $return = WPJOBPORTAL_SAVE_ERROR;
        } else { // Job has been successfully shortlisted
            WPJOBPORTALincluder::getJSmodel('job')->custom_wpjobportal_cookie($data['jobid'], $row->id);

            // --- NEW HOOK INTEGRATION START ---
            // Blueprint: Core System Transitions
            // Fires when a job seeker successfully shortlists a job, useful for candidate intent tracking.
            do_action('wpjobportal_job_shortlisted', $row->id, $data['jobid'], $data['uid'], $data);
            // --- NEW HOOK INTEGRATION END ---
        }
        // return constant flag in case of theme.
        if(null != $themecall){
            return $return;
        }
        if ($return != WPJOBPORTAL_SAVE_ERROR) {
            $msg = '<div id="notification-ok"><label id="popup_message"><img src="' . WPJOBPORTAL_PLUGIN_URL . 'includes/images/approve.png"/>' . __("Job has been Shortlisted", "wp-job-portal") . '</label></div>';
        } else {
            $msg = '<div id="notification-not-ok"><label id="popup_message"><img src="' . WPJOBPORTAL_PLUGIN_URL . 'includes/images/unpublish.png"/>' . __("Failed while performing this action", "wp-job-portal") . '</label></div>';
        }
        return $msg;
    }


    function deleteShortlistedJobById($id) {
        if (!is_numeric($id))
            return false;
        //Remove from cookie if exists
        if (isset($_COOKIE)) {
            if (WPJOBPORTALrequest::hasCookie('wp_wpjobportal_cookie')) {
                $array = $this->getShortlistCookieArray();
                $newarray = array();
                if (!empty($array)) {
                    foreach ($array AS $slid => $jobid) {
                        if ((int) $id !== (int) $slid) {
                            $newarray[$slid] = $jobid;
                        }
                    }
                }
                wpjobportalphplib::wpJP_setcookie('wp_wpjobportal_cookie', wp_json_encode($newarray), time() + 1209600, SITECOOKIEPATH, null, false, true);
            }
        }
        //Remove from shortlisted table
        $row = WPJOBPORTALincluder::getJSTable('jobshortlist');
        if ($row->delete($id)) {

            // --- NEW HOOK INTEGRATION START ---
            // Blueprint: Core System Transitions
            do_action('wpjobportal_job_shortlist_deleted', $id);
            // --- NEW HOOK INTEGRATION END ---

            return WPJOBPORTAL_DELETED;
        } else {
            return WPJOBPORTAL_DELETE_ERROR;
        }
    }

    function getShortListedIDArray() {
        $returnarray = array();
        if (isset($_COOKIE)) {
            if (WPJOBPORTALrequest::hasCookie('wp_wpjobportal_cookie')) {
                $array = $this->getShortlistCookieArray();
                if (!empty($array)) {
                    foreach ($array AS $id => $jobid) {
                        $returnarray[] = absint($id);
                    }
                }
            }
        }
        return $returnarray;
    }

    function getShortListViewByJobId() {
        $jobid = WPJOBPORTALrequest::getVar('jobid');
        if (wpjobportal::$wpjobportal_theme_chk == 1) {
            $result = $this->getJobByid($jobid);
            $job = $result[0];
        }
        if ($jobid != null && is_numeric($jobid)) {
            $result = $this->getDataForShortlist($jobid);
            $title = __('Short List Job', 'wp-job-portal');
            $content =  '';
            if (wpjobportal::$wpjobportal_theme_chk == 1) {
                $listing_fields = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldOrderingDataForListing(2);
                // Pop up detail data For Job(Extra Detail)
                $content .=  '<div class="wpj-jp-popup-cnt-wrp">';
                $content .=  '<i class="fas fa-times wpj-jp-popup-close-icon" data-dismiss="modal"></i>';
                $content .=  '<div class="wpj-jp-popup-right">';
                $content .=  '<div class="wpj-jp-popup-list">';
            if(in_array('multicompany', wpjobportal::$_active_addons)){
                $mod = "multicompany";
            }else{
                $mod = "company";
            }
            if($job->companyid != ''){
                $path = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('employer');
                if ($job->logofilename != "") {
                    $wpdir = wp_upload_dir();
                    $data_directory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
                    $path = $wpdir['baseurl'] . '/' . $data_directory . '/data/employer/comp_' . $job->companyid . '/logo/' . $job->logofilename;
                }
                $content .= '<div class="wpj-jp-popup-list-logo">';
                $content .=     '<a href='. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>$mod, 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$job->companyid,'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()))) .' title='. esc_attr__('Company Logo','job-portal-theme').'>';
                $content .=         '<img src='. esc_url($path) .' alt="'.esc_attr__('Company Logo','job-portal-theme').'" >';
                $content .=     '</a>';
                $content .= '</div>';
            }
                $content .= '<div class="wpj-jp-popup-list-cnt-wrp">';
                $content .=     '<div class="wpj-jp-popup-list-cnt">';
                $content .=          '<span class="wpj-jp-job-type" style="color:'.$job->jobtypecolor.'">';
                $content .=             wpjobportal::wpjobportal_getVariableValue($job->jobtypetitle, 'job-portal-theme');
                $content .=          '</span>';
                $content .=     '</div>';
                $content .=     '<div class="wpj-jp-popup-list-cnt">';
                $content .=         '<a class="wpj-jp-popup-list-comp-tit" href='. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>$mod, 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$job->companyid,'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()))).' title="'.esc_attr__('Company Name','job-portal-theme').'">
                                        '.wpjobportal::wpjobportal_getVariableValue($job->companyname, 'job-portal-theme').'
                                    </a>';
                $content .=     '</div>';
                $content .=     '<div class="wpj-jp-popup-list-cnt">';
                $content .=         '<h5 class="wpj-jp-popup-list-tit">
                                        <a href='.esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'viewjob', 'wpjobportalid'=>$job->jobaliasid,'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()))).' title="'.esc_attr__('Job Title','job-portal-theme').'">
                                            '.wpjobportal::wpjobportal_getVariableValue($job->title, 'job-portal-theme').'
                                        </a>
                                    </h5>';
                $content .=     '</div>';
                $content .=     '<div class="wpj-jp-popup-list-cnt">';
                $content .=         '<ul>';
                                        if(isset($listing_fields['jobcategory'])){
                                            if(isset($job) && !empty($job->cat_title)){
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
                                        if(isset($listing_fields['jobsalaryrange']) && $wpjobportal_salaryview !== ''){
                    $content .=             '<li>';
                    $content .=                  '<span class="wpj-jp-popup-list-meta-tit">';
                    $content .=                       wpjobportal::wpjobportal_getVariableValue($listing_fields['jobsalaryrange']). ':';
                    $content .=                   '</span>';
                                                   $content .= $wpjobportal_salaryview;
                                                   if($job->salarytype==3 || $job->salarytype==2) {
                    $content .=                      ' - ' .wpjobportal::wpjobportal_getVariableValue($job->srangetypetitle, 'job-portal-theme');
                                                    }
                    $content .=             '</li>';
                                           }

                                        if(isset($listing_fields['city'])){
                                            if(isset($job) && !empty($job->location)){
                    $content .=             '<li>';
                    $content .=                     '<span class="wpj-jp-popup-list-meta-tit">';
                    $content .=                         wpjobportal::wpjobportal_getVariableValue($listing_fields['city']). ':';
                    $content .=                     '</span>';
                    $content .=                     wpjobportal::wpjobportal_getVariableValue($job->location, 'job-portal-theme');
                    $content .=             '</li>';
                                        }
                                    }
                $content .=         '</ul>';
                $content .=     '</div>';
                $content .= '</div>'; 
                $content .= '</div>'; // end job list
                $content .= '</div>'; // right div
                // Pop up detail data For Job Ends there
                $content .= '<div class="wpj-jp-popup-left">';
                $content .=  '<h3 class="wpj-jp-popup-heading">'.$title.'</h3>';
            }
            $content .= '<div class="'.$this->class_prefix.'-popup-field-wrp">';
            $content .= '<div class="'.$this->class_prefix.'-popup-field '.$this->class_prefix.'-popup-fullwidth">';
            if (wpjobportal::$wpjobportal_theme_chk == 1) {
                $content .= '<label class="sr-only" for="wpjobportalcomment">' . __('Comments', 'wp-job-portal') . '</label>';
            } else {
                $content .= '<label for="wpjobportalcomment">' . __('Comments', 'wp-job-portal') . '</label>';
            }
            $comment = (isset($result->comments)) ? $result->comments : '';
            $content .= '<textarea id="wpjobportalcomment" name="wpjobportalcomment" rows="5">' . $comment . '</textarea>';
            $content .= '</div>';
            if(in_array('rating', wpjobportal::$_active_addons)){
                $content .= '<label class="rate" for="wpjobportalrating">' . __('Rate', 'wp-job-portal') . '</label>';
            }
            //Rating Addons
            if ($result)
                $percent = $result->rate * 20;
            else
                $percent = 0;
            $stars = '';
            $stars = '-small';
            $content .="
                        <div class=\"$this->class_prefix-container" . $stars . "\">
                            <span id='shortlist-stars'><ul class=\"$this->class_prefix-stars" . $stars . "\" >
                                <li id=\"rating_" . $jobid . "\" class=\"current-rating\" style=\"width:" . (int) $percent . "%;\"></li>
                                <li><a anchor=\"anchor\" href=\"javascript:void(null)\" onclick=\"javascript:setrating('rating_" . $jobid . "',1);\" title=\"" . __('Very Poor', 'wp-job-portal') . "\" class=\"one-star\">1</a></li>
                                <li><a anchor=\"anchor\" href=\"javascript:void(null)\" onclick=\"javascript:setrating('rating_" . $jobid . "',2);\" title=\"" . __('Poor', 'wp-job-portal') . "\" class=\"two-stars\">2</a></li>
                                <li><a anchor=\"anchor\" href=\"javascript:void(null)\" onclick=\"javascript:setrating('rating_" . $jobid . "',3);\" title=\"" . __('Regular', 'wp-job-portal') . "\" class=\"three-stars\">3</a></li>
                                <li><a anchor=\"anchor\" href=\"javascript:void(null)\" onclick=\"javascript:setrating('rating_" . $jobid . "',4);\" title=\"" . __('Good', 'wp-job-portal') . "\" class=\"four-stars\">4</a></li>
                                <li><a anchor=\"anchor\" href=\"javascript:void(null)\" onclick=\"javascript:setrating('rating_" . $jobid . "',5);\" title=\"" . __('Very Good', 'wp-job-portal') . "\" class=\"five-stars\">5</a></li>
                            </ul></span>
                        </div>
                        ";
            $id = (isset($result->id)) ? $result->id : "";
            $content .= '<input type="hidden" name="wpjobportalid" id="wpjobportalid" value="' . $id . '">';
            $content .= '<input type="hidden" name="jobid" id="jobid" value="' . $jobid . '">';
            $btnclass1 = '';
            if (wpjobportal::$wpjobportal_theme_chk == 1) {
                $btnclass1 = "wpj-jp-visitor-msg-primary-btn";
                $content .= '<div class="'.$this->class_prefix.'-visitor-msg-btn-wrp">
                            <a href="#" class="'.$this->class_prefix.'-visitor-msg-btn '.$btnclass1.'" id="apply-now-btn" onclick="saveJobShortlist(1)">' . __('Save', 'wp-job-portal') . '</a>
                        </div>';
            } else {
                $content .= '<div class="'.$this->class_prefix.'-visitor-msg-btn-wrp">
                            <a href="#" class="'.$this->class_prefix.'-visitor-msg-btn wpjobportal-save-shortlist-popup-button '.$btnclass1.'" id="apply-now-btn" onclick="saveJobShortlist()">' . __('Save', 'wp-job-portal') . '</a>
                        </div>';
            }
            $content .= '</div>';
            if (wpjobportal::$wpjobportal_theme_chk == 1) {
                $content .= '</div>'; /// end left wrp
                $content .= '</div>'; /// end cnt wrp
            }
        }else {
            $title = __('No Records Found', 'wp-job-portal');
            $content = '<h1>' . __('No Records Found', 'wp-job-portal') . '</h1>';
        }

        // --- NEW HOOK INTEGRATION START ---
        // Blueprint: Front-End UI Filters
        // Allows theme developers to modify the HTML output of the shortlist modal programmatically.
        $content = apply_filters('wpjobportal_shortlist_popup_html', $content, $jobid);
        // --- NEW HOOK INTEGRATION END ---

        // $title = wpjobportalphplib::wpJP_safe_encoding($title);
        // $content = wpjobportalphplib::wpJP_safe_encoding($content);
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

    function saveJobShortlistJobManager(){
        return $this->saveJobShortlistForTemplate();
    }

    function saveJobShortlistForTemplate(){
        $return_value = $this->saveJobShortList(1);
        // resolved the css issue beacuse of class typo
        if ($return_value != WPJOBPORTAL_SAVE_ERROR) {
            $html = '<div id="'.$this->class_prefix.'-notification-ok"><div id="'.$this->class_prefix.'-popup_message">
            <img src="' . WPJOBPORTAL_PLUGIN_URL . 'includes/images/approve.png"/><spam class="popup_msg_txt">' . __("Job has been Shortlisted", "wp-job-portal") . '</spam><button class="applynow-closebutton" onclick="wpjobportalClosePopup(1);" ><img src="'.WPJOBPORTAL_PLUGIN_URL.'/includes/images/popupcloseicon.png"/>'.__('Close','wp-job-portal').'</button></div></div>';
        }else{
            $html = '<div id="'.$this->class_prefix.'-notification-not-ok"><div id="'.$this->class_prefix.'-popup_message">
            <img src="' . WPJOBPORTAL_PLUGIN_URL . 'includes/images/unpublish.png"/><spam class="popup_msg_txt">' . __("Failed while performing this action", "wp-job-portal") . '</spam><button class="applynow-closebutton" onclick="wpjobportalClosePopup(1);" ><img src="'.WPJOBPORTAL_PLUGIN_URL.'/includes/images/popupcloseicon.png"/>'.__('Close','wp-job-portal').'</button></div></div>';
        }
        return $html;
    }

    function getDataForShortlist($jobid) {
        if (!is_numeric($jobid))
            return false;
        $uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        $query = null;
        if ($uid == null) {
            if (!WPJOBPORTALrequest::hasCookie('wp_wpjobportal_cookie'))
                return false;
            $value = $this->getShortlistCookieArray();
            if (empty($value)) {
                return false;
            }
            foreach ($value AS $slid => $sljobid) {
                if ((int) $sljobid === (int) $jobid) {
                    $query = "SELECT id,comments,rate FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobshortlist` WHERE id = " . (int) ($slid);
                    break; // Donot iterate all element if one is got from the cookie
                }
            }
        } elseif (is_numeric($uid)) {
            $query = "SELECT id,comments,rate FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobshortlist` WHERE jobid = " . (int) ($jobid) . " AND uid = " . (int) ($uid);
        }
        if ($query != null) {
            $result = wpjobportal::$_db->get_row($query);
            if (wpjobportal::$wpjobportal_theme_chk == 1) {
                // to handle fatal error in case of new job shortlist
                if(is_null($result)){
                    $result = new stdClass();
                }

                $result->jobData = WPJOBPORTALincluder::getJSModel('job')->getJobForApply($jobid);
            }
            return $result;
        } else {
            return false;
        }
    }

    function getShortListViewByJobIdJobPortal(){
        $nonce = WPJOBPORTALrequest::getVar('js_nonce');
        if (! wp_verify_nonce( $nonce, 'wp-job-portal-nonce') ) {
            die( 'Security check Failed' );
        }
        $jobid = WPJOBPORTALrequest::getVar('jobid');
        if ($jobid != null && is_numeric($jobid)) {
            $result = $this->getDataForShortlist($jobid);
            $comment = (isset($result->comments)) ? $result->comments : '';
            $content='';
            if (wpjobportal::$wpjobportal_theme_chk == 1) {
                if(in_array('multicompany', wpjobportal::$_active_addons)){
                    $mod = "multicompany";
                }else{
                    $mod = "company";
                }
                $listing_fields = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldOrderingDataForListing(2);
                /*Pop up detail data For Job(Extra Detail)*/
                $content .=  '<div class="wpj-jp-popup-cnt-wrp">';
                $content .=  '<i class="fas fa-times wpj-jp-popup-close-icon" data-dismiss="modal"></i>';
                $content .=  '<div class="wpj-jp-popup-right">';
                $content .=  '<div class="wpj-jp-popup-list">';
                $job = $result->jobData;
                if($job->companyid != ''){
                    $path = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('employer');
                    if ($job->logofilename != "") {
                        $wpdir = wp_upload_dir();
                        $data_directory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
                        $path = $wpdir['baseurl'] . '/' . $data_directory . '/data/employer/comp_' . $job->companyid . '/logo/' . $job->logofilename;
                    }

                    $company_listing_fields = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldOrderingData(1);
                    $content .= '<div class="wpj-jp-popup-list-logo">';
                    if(isset($company_listing_fields['logo'])){
                        $content .=     '<a href='. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>$mod, 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$job->companyid,'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()))) .' title='. esc_attr__('Company Logo','job-portal-theme').'>';
                        $content .=         '<img src='. esc_url($path) .' alt="'.esc_attr__('Company Logo','job-portal-theme').'" >';
                        $content .=     '</a>';
                    }
                    $content .= '</div>';
                }
                $content .= '<div class="wpj-jp-popup-list-cnt-wrp">';
                $content .=     '<div class="wpj-jp-popup-list-cnt">';
                if(isset($listing_fields['jobtype'])){
                    $content .=          '<span class="wpj-jp-job-type" style="color:'.$job->jobtypecolor.'">';
                    $content .=             wpjobportal::wpjobportal_getVariableValue($job->jobtypetitle, 'job-portal-theme');
                    $content .=          '</span>';
                }
                $content .=     '</div>';
                $content .=     '<div class="wpj-jp-popup-list-cnt">';
                $content .=         '<a class="wpj-jp-popup-list-comp-tit" href='. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>$mod, 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$job->companyid,'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()))).' title="'.esc_attr__('Company Name','job-portal-theme').'">
                                        '.wpjobportal::wpjobportal_getVariableValue($job->companyname, 'job-portal-theme').'
                                    </a>';
                $content .=     '</div>';
                $content .=     '<div class="wpj-jp-popup-list-cnt">';
                $content .=         '<h5 class="wpj-jp-popup-list-tit">
                                        <a href='.esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'viewjob', 'wpjobportalid'=>$jobid,'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()))).' title="'.esc_attr__('Job Title','job-portal-theme').'">
                                            '.wpjobportal::wpjobportal_getVariableValue($job->title, 'job-portal-theme').'
                                        </a>
                                    </h5>';
                $content .=     '</div>';
                $content .=     '<div class="wpj-jp-popup-list-cnt">';
                $content .=         '<ul>';
                                    if(isset($listing_fields['jobcategory'])){
                                        if(isset($job) && !empty($job->cat_title)){
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
                                    if(isset($listing_fields['jobsalaryrange']) && $wpjobportal_salaryview !== ''){
                $content .=             '<li>';
                $content .=                  '<span class="wpj-jp-popup-list-meta-tit">';
                $content .=                       wpjobportal::wpjobportal_getVariableValue($listing_fields['jobsalaryrange']). ':';
                $content .=                   '</span>';
                                               $content .= $wpjobportal_salaryview;
                                               if($job->salarytype==3 || $job->salarytype==2) {
                $content .=                      ' - ' .wpjobportal::wpjobportal_getVariableValue($job->srangetypetitle, 'job-portal-theme');
                                               }
                $content .=             '</li>';
                                    }
                                        if(isset($listing_fields['city'])){
                                            if(isset($job) && !empty($job->location)){
                        $content .=             '<li>';
                $content .=                     '<span class="wpj-jp-popup-list-meta-tit">';
                $content .=                         wpjobportal::wpjobportal_getVariableValue($listing_fields['city']). ':';
                $content .=                     '</span>';
                $content .=                     wpjobportal::wpjobportal_getVariableValue($job->location, 'job-portal-theme');
                        $content .=             '</li>';
                                            }
                                        }
                $content .=         '</ul>';
                $content .=     '</div>';
                $content .= '</div>'; 
                $content .= '</div>'; // end job list
                $content .= '</div>'; // right div
                // Pop up detail data For Job Ends there
            }
            if (wpjobportal::$wpjobportal_theme_chk == 1) {
                $content .= '<div class="wpj-jp-popup-left wpj-shortList-left">';
                $content .=  '<h3 class="'.$this->class_prefix.'-modal-title">'.__("Add To ShortList","wp-job-portal").'</h3>';
            }
            $content.='
                    <div class="modal-body '.$this->class_prefix.'-modal-body">
                        <div class="form '.$this->class_prefix.'-modal-form-wrp">
                            <div class="col-md-12 '.$this->class_prefix.'-modal-form-row">
                                <div class="form-group">
                                    '.WPJOBPORTALformfield::textarea('wpjobportalcomment', $comment, array('class' => 'form-control '.$this->class_prefix.'-modal-textarea', 'placeholder' => __("Comments", "wp-job-portal"))).'
                                </div>
                            </div>
                            <div class="'.$this->class_prefix.'-modal-shortlist-star-wrp">';
                                $content .= '<label class="rate" for="wpjobportalrating">' . __("Rate", "wp-job-portal") . '</label>';
                                    $percent = 0;
                                    if ($result){
                                        if(isset($result->rate) && $result->rate > 0){
                                            $percent = $result->rate * 20;
                                        }
                                    }
                                    else
                                        $percent = 0;
                                    $stars = '';
                                    $stars = '-small';
                                    $content .="
                                        <div class=\"wpjobportal-container" . $stars . "\">
                                            <span id='shortlist-stars'><ul class=\"wpjobportal-stars" . $stars . "\" >
                                                <li id=\"rating_" . $jobid . "\" class=\"current-rating\" style=\"width:" . (int) $percent . "%;\"></li>
                                                <li><a anchor=\"anchor\" href=\"javascript:void(null)\" onclick=\"javascript:setrating('rating_" . $jobid . "',1);\" title=\"" . __('Very Poor', 'wp-job-portal') . "\" class=\"one-star\">1</a></li>
                                                <li><a anchor=\"anchor\" href=\"javascript:void(null)\" onclick=\"javascript:setrating('rating_" . $jobid . "',2);\" title=\"" . __('Poor', 'wp-job-portal') . "\" class=\"two-stars\">2</a></li>
                                                <li><a anchor=\"anchor\" href=\"javascript:void(null)\" onclick=\"javascript:setrating('rating_" . $jobid . "',3);\" title=\"" . __('Regular', 'wp-job-portal') . "\" class=\"three-stars\">3</a></li>
                                                <li><a anchor=\"anchor\" href=\"javascript:void(null)\" onclick=\"javascript:setrating('rating_" . $jobid . "',4);\" title=\"" . __('Good', 'wp-job-portal') . "\" class=\"four-stars\">4</a></li>
                                                <li><a anchor=\"anchor\" href=\"javascript:void(null)\" onclick=\"javascript:setrating('rating_" . $jobid . "',5);\" title=\"" . __('Very Good', 'wp-job-portal') . "\" class=\"five-stars\">5</a></li>
                                            </ul></span>
                                        </div>";
                                $content.='</div>
                            <div class="col-md-12 '.$this->class_prefix.'-modal-form-btn">
                                <div class="form-group">
                                    <input type="button" class="btn btn-primary btn-lg btn-block '.$this->class_prefix.'-modal-form-btn-inpf" value="'.__("Add To ShortList","wp-job-portal").'" onclick="saveJobShortlist(1);"  />
                                </div>
                            </div>
                            <div class="'.$this->class_prefix.'-visitor-msg-btn-wrp">
                            </div>
                            '.WPJOBPORTALformfield::hidden('wpjobportalid', isset($result->id) ? $result->id : '').
                            WPJOBPORTALformfield::hidden('jobid', $jobid).
                            '
                        </div>
                    </div>';
            if (wpjobportal::$wpjobportal_theme_chk == 1) {
                $content .= '</div>'; /// end left wrp
                $content .= '</div>'; /// end cnt wrp
            }
        }else {
            $title = __('No Records Found', 'wp-job-portal');
            $content = '<h1>' . __('No Records Found', 'wp-job-portal') . '</h1>';
        }

        // --- NEW HOOK INTEGRATION START ---
        // Blueprint: Front-End UI Filters
        $content = apply_filters('wpjobportal_shortlist_popup_html', $content, $jobid);
        // --- NEW HOOK INTEGRATION END ---

        $array = array('title' => "", 'content' => $content);
        return wp_json_encode($array);
    }


}

?>
