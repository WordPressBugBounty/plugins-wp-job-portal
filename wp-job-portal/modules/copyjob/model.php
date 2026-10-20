<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALcopyjobModel {
   /**
    * May the current user copy this job? Only its owner (or an admin) may, and
    * a copy is a new posting, so not while old Credits is paused.
    */
   private function canCopyJob($jobid) {
        if (wpjobportal::$_common->wpjp_isadmin()) {
            return true;
        }
        if (WPJOBPORTAL_Monetisation::paused()) {
            return false;
        }
        return WPJOBPORTALincluder::getJSModel('job')->getIfJobOwner($jobid);
   }

   function copyJob($jobid, $actionid) {
        if (!$this->canCopyJob($jobid)) {
            return WPJOBPORTAL_SAVE_ERROR;
        }
        if (is_numeric($jobid)) {
            $query = "SELECT * FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` WHERE id = " . (int) ($jobid);
            $job = wpjobportaldb::get_row($query);
            $data = (array) $job;
            $data['id'] = '';
            $data['title'] = $data['title'] . ' ' . __('Copy', 'wp-job-portal');
            $data['jobid'] = WPJOBPORTALincluder::getJSModel('job')->getJobId();
            $data['isjob'] = 2;
            $isadmin = WPJOBPORTALrequest::getVar('isadmin');
            $user = WPJOBPORTALincluder::getObjectClass('user');
            $dateformat = wpjobportal::$_configuration['date_format'];
            $subType = wpjobportal::$_config->getConfigValue('submission_type');
            $expiry = false; // to handle log error
            $no_package_needed = 0;
            if(in_array('credits', wpjobportal::$_active_addons)){
                if($subType == 1){
                     $expiry = wpjobportal::$_config->getConfigValue('jobexpiry_days_free');
                    if(isset($data['stoppublishing']) && empty($data['stoppublishing'])){
                        $data['stoppublishing'] = gmdate($dateformat,strtotime($data['startpublishing'].'+'.$expiry.' days') );
                    }
                    if (!wpjobportal::$_common->wpjp_isadmin()) {
                        $data['status'] = wpjobportal::$_config->getConfigurationByConfigName('jobautoapprove');
                    }
                }elseif ($subType == 2) {
                    #Per listing --Free job Expiry date
                    $expiry = wpjobportal::$_config->getConfigValue('jobexpiry_days_perlisting');
                    if(isset($data['stoppublishing']) && empty($data['stoppublishing'])){
                        $data['stoppublishing'] = gmdate($dateformat,strtotime($data['startpublishing'].'+'.$expiry.' days') );
                    }else{
                        $data['stoppublishing'] = gmdate($dateformat,strtotime($expiry.' days') );
                    }
                    // in case of per listing submission mode
                    $price_check = WPJOBPORTALincluder::getJSModel('credits')->checkIfPriceDefinedForAction('add_job');
                    if($price_check == 1){ // if price is defined then status 3
                        $data['status'] = 3;
                    }else{ // if price not defined then status set to auto approve configuration
                        $data['status'] = wpjobportal::$_config->getConfigValue('jobautoapprove');
                    }
                }elseif ($subType == 3) {
                    if(!wpjobportal::$_common->wpjp_isadmin()){
                        $no_package_needed = 0;

                        $result = WPJOBPORTALincluder::getJSModel('credits')->checkIfPackageDefinedForRole(1); //1 for employer
                        if($result == 0){ // 0 means no package found. so allow the action.
                            $no_package_needed = 1;
                        }

                        if($no_package_needed == 0){
                            $upakid = WPJOBPORTALrequest::getVar('wpjobportal_packageid',null,0);
                            $package = apply_filters('wpjobportal_addons_userpackages_permodule',false,$upakid,$user->uid(),'remjob');
                            if( !$package ){
                                return WPJOBPORTAL_SAVE_ERROR;
                            }
                            if( $package->expired ){
                                return WPJOBPORTAL_SAVE_ERROR;
                            }
                            //if Department are not unlimited & there is no remaining left
                            if( $package->job!=-1 && !$package->remjob ){ //-1 = unlimited
                                return WPJOBPORTAL_SAVE_ERROR;
                            }
                        }
                    }elseif (wpjobportal::$_common->wpjp_isadmin()) { // checking if admin is trying to perform action
                        $no_package_needed = 0;

                        $result = WPJOBPORTALincluder::getJSModel('credits')->checkIfPackageDefinedForRole(1); //1 for employer
                        if($result == 0){ // 0 means no package found. so allow the action.
                            $no_package_needed = 1;
                        }

                        if($no_package_needed == 0){
                            $payment = WPJOBPORTALrequest::getVar('payment',null,0);
                            if ($payment == 0) { // proceed without payment option
                                $upakid = WPJOBPORTALrequest::getVar('upakid',null,0);
                                $data['userpackageid'] = $upakid;
                            } else { // if admin clicked on proceed with payment option
                                $upakid = WPJOBPORTALrequest::getVar('upakid',null,0);
                                $uid = WPJOBPORTALrequest::getVar('uid');
                                $package = apply_filters('wpjobportal_addons_userpackages_permodule',false,$upakid,$data['uid'],'remjob');
                                if( !$package ){
                                    return WPJOBPORTAL_SAVE_ERROR;
                                }
                                if( $package->expired ){
                                    return WPJOBPORTAL_SAVE_ERROR;
                                }
                                //if Department are not unlimited & there is no remaining left
                                if( $package->job!=-1 && !$package->remjob ){ //-1 = unlimited
                                    return WPJOBPORTAL_SAVE_ERROR;
                                }
                            }
                        }
                    }
                    // if($package == ''){ // to handle log errors since we are trying to access elements from package object.
                    //     return WPJOBPORTAL_SAVE_ERROR;
                    // }
                    #user packae id--
                    $data['status'] = wpjobportal::$_config->getConfigValue('jobautoapprove');
                    if($no_package_needed == 0){
                        $data['userpackageid'] = $upakid;
                    }

                    if(isset($package) && !empty($package)){
                        $expiry = $package->jobtime.''.$package->jobtimeunit;
                    }else{
                        $expiry = "30 days"; // in case of undefined add job for 30 days
                    }

                    //if(isset($data['stoppublishing']) && empty($data['stoppublishing'])){
                    $data['stoppublishing'] = gmdate($dateformat,strtotime($data['startpublishing'].'+'.$expiry) );
                    // }else{
                    //     $data['stoppublishing'] = gmdate($dateformat,strtotime($package->jobtime.''.$package->jobtimeunit));
                    // }
                    if(isset($data['price']) && !empty($data['status'])){
                        $data['price'] = '';
                    }
                }
            }
            if($expiry == false){
                $tdate1 = strtotime($data['startpublishing']);
                $tdate2 = strtotime($data['stoppublishing']);
                $seconds_diff = $tdate2 - $tdate1;
                if($seconds_diff > 86400){
                    $expiry = $seconds_diff / 86400;
                }else{
                    $expiry = 1;
                }
                $data['startpublishing'] = date_i18n("Y-m-d H:i:s");
                $data['stoppublishing'] = gmdate($dateformat,strtotime($data['startpublishing'].'+'.round($expiry).' days') );
            }
            if(isset($data['stoppublishing'])){
                $data['stoppublishing'] = gmdate('Y-m-d H:i:s', strtotime($data['stoppublishing']));
            }
            $data['created'] = date_i18n("Y-m-d H:i:s");
            $data['startpublishing'] = date_i18n("Y-m-d H:i:s");
            if(isset($data['isfeaturedjob'])){
                $data['isfeaturedjob'] = 2;
            }

            if(isset($data['startfeatureddate'])){
                $data['startfeatureddate'] = '';
            }

            if(isset($data['endfeatureddate'])){
                $data['endfeatureddate'] = '';
            }
            $row = WPJOBPORTALincluder::getJSTable('job');
            if (!$row->bind($data)) {
                $res = "error";
            }
            if (!$row->store()) {
                return false;
            }
            if ($row->city){
                $storemulticity = WPJOBPORTALincluder::getJSModel('job')->storeMultiCitiesJob($row->city, $row->id);
            }
            $this->copyCustomUploads($jobid, $row->id);
            if (isset($storemulticity) && $storemulticity == false){
                return false;
            }
            if(in_array('credits', wpjobportal::$_active_addons) && $subType == 3 && $no_package_needed == 0){
                if(!wpjobportal::$_common->wpjp_isadmin()){
                    apply_filters('wpjobportal_addons_user_transactionlog',$row,'job',$upakid,$row->uid);
                }elseif(wpjobportal::$_common->wpjp_isadmin()){
                    apply_filters('wpjobportal_addons_user_transactionlog',$row,'job',$upakid,$data['uid']);
                }
            }
            WPJOBPORTALMessages::setLayoutMessage(__('Job Copied Successfully', 'wp-job-portal'), 'updated',WPJOBPORTALincluder::getJSModel('job')->getMessagekey());
        }
        if(in_array('credits', wpjobportal::$_active_addons)){
            return true;
        }else{
            return WPJOBPORTAL_SAVED;
        }
    }

    /**
     * Give a copied job its own copy of the original's custom-field uploads.
     *
     * The copy keeps the original's field values, file names included, and a
     * file field is served from the job's own folder - without this the copy's
     * Download link pointed at a folder that did not exist.
     *
     * @param int $wpjobportal_from_jobid Original job.
     * @param int $wpjobportal_to_jobid   The new copy.
     */
    private function copyCustomUploads($wpjobportal_from_jobid, $wpjobportal_to_jobid) {
        $wpjobportal_from_jobid = absint($wpjobportal_from_jobid);
        $wpjobportal_to_jobid = absint($wpjobportal_to_jobid);
        if ($wpjobportal_from_jobid < 1 || $wpjobportal_to_jobid < 1 || $wpjobportal_from_jobid === $wpjobportal_to_jobid) {
            return;
        }
        $wpjobportal_wpdir = wp_upload_dir();
        $wpjobportal_base = $wpjobportal_wpdir['basedir'] . '/' . wpjobportal::$_config->getConfigurationByConfigName('data_directory') . '/data/employer/';
        $wpjobportal_from = $wpjobportal_base . 'job_' . $wpjobportal_from_jobid . '/custom_uploads';
        $wpjobportal_to = $wpjobportal_base . 'job_' . $wpjobportal_to_jobid . '/custom_uploads';
        global $wp_filesystem;
        $wp_filesystem = wpjobportal::wpjobportal_filesystem();
        if (!$wp_filesystem || !$wp_filesystem->is_dir($wpjobportal_from)) {
            return;
        }
        if (!function_exists('copy_dir')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        wp_mkdir_p($wpjobportal_to);
        copy_dir($wpjobportal_from, $wpjobportal_to);
    }

    function makeJobCopyAjax() {
        $nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'make-job-copy-ajax') ) {
            die( 'Security check Failed' );
        }
        $jobid = (int) WPJOBPORTALrequest::getVar('jobid');
        $res = "error";
        if (!$this->canCopyJob($jobid)) {
            return $res;
        }
        if ($jobid) {
            $res = "copied";
            $query = "SELECT * FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` WHERE id = " . (int) ($jobid);
            $job = wpjobportaldb::get_row($query);
            $data = (array) $job;

            $data['id'] = '';
            $data['title'] = $data['title'] . ' ' . __('Copy', 'wp-job-portal');
            $data['jobid'] = WPJOBPORTALincluder::getJSModel('job')->getJobId();
            $data['isjob'] = 0;
            $data['status'] = 0;
            $data['startpublishing'] = gmdate('Y-m-d H:i:s');
            $data['created'] = gmdate("Y-m-d H:i:s");
            $row = WPJOBPORTALincluder::getJSTable('job');
            if (!$row->bind($data)) {
                $res = "error";
            }
            if (!$row->check($data)) {
                $res = "error";
            }
            if (!$row->store($data)) {
                $res = "error";
            } else {
                $this->copyCustomUploads($jobid, $row->id);
            }
            if ($data['city'])
                $storemulticity = WPJOBPORTALincluder::getJSModel('job')->storeMultiCitiesJob($data['city'], $row->id);
            if (isset($storemulticity) && $storemulticity == false)
                $res = "savecitieserror";

        }
        return $res;
    }

     function getPackagePopupForCopyJob(){
            $nonce = WPJOBPORTALrequest::getVar('_wpnonce');
            if (! wp_verify_nonce( $nonce, 'get-package-popup-for-copy-job') ) {
                die( 'Security check Failed' );
            }
            $uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
            $jobid = WPJOBPORTALrequest::getVar('wpjobportalid');
            $subtype = wpjobportal::$_config->getConfigValue('submission_type');
            if( $subtype != 3 ){
                return false;
            }
            $userpackages = array();
            $userpackage = apply_filters('wpjobportal_addons_credit_get_Packages_user',false,$uid,'job');
            foreach($userpackage as $package){
                if($package->job == -1 || $package->remjob > 0){ //-1 = unlimited
                    $userpackages[] = $package;
                }
            }
            $addonclass = '';
            if(WPJOBPORTALincluder::getJSModel('common')->isElegantDesignEnabled()){
                $addonclass = ' wjportal-elegant-addon-packages-popup ';
            }
            if (wpjobportal::$wpjobportal_theme_chk == 1) {
                $content = '
                <div id="wpj-jp-popup-background" style="display: none;"></div>
                <div id="package-popup" class="wpj-jp-popup-wrp wpj-jp-packages-popup">
                    <div class="wpj-jp-popup-cnt-wrp">
                        <i class="fas fa-times wpj-jp-popup-close-icon" data-dismiss="modal"></i>
                        <h3 class="wpj-jp-popup-heading">
                            '.esc_html__("Select Package",'job-portal-theme').'
                            <div class="wpj-jp-popup-desc">
                                '.esc_html__("Please select a package first",'job-portal-theme').'
                            </div>
                        </h3>
                        <div class="wpj-jp-popup-contentarea">
                            <div class="wpj-jp-packages-wrp">';
                                if(count($userpackages) == 0 || empty($userpackages)){
                                    $content .= WPJOBPORTALmessages::showMessage(esc_html__("You do not have any job remaining",'job-portal-theme'),'error',1);
                                } else {
                                    foreach($userpackages as $package){
                                        #User Package For Selection in Popup Model --Views
                                        $content .= '
                                            <div class="wpj-jp-pkg-item" id="package-div-'.$package->id.'" >
                                                <div class="wpj-jp-pkg-item-top">
                                                    <h4 class="wpj-jp-pkg-item-title">
                                                        '.wpjobportal::wpjobportal_getVariableValue( $package->title).'
                                                    </h4>
                                                </div>
                                                <div class="wpj-jp-pkg-item-mid">
                                                    <div class="wpj-jp-pkg-item-row">
                                                        <span class="wpj-jp-pkg-item-tit">
                                                            '.esc_html__("Job",'job-portal-theme').' :
                                                        </span>
                                                        <span class="wpj-jp-pkg-item-val">
                                                            '.($package->job==-1 ? esc_html__("Unlimited",'job-portal-theme') : $package->job).'
                                                        </span>
                                                    </div>
                                                    <div class="wpj-jp-pkg-item-row">
                                                        <span class="wpj-jp-pkg-item-tit">
                                                            '.esc_html__("Remaining",'job-portal-theme').' :
                                                        </span>
                                                        <span class="wpj-jp-pkg-item-val">
                                                            '.($package->job==-1 ? esc_html__("Unlimited",'job-portal-theme') : $package->remjob).'
                                                        </span>
                                                    </div>
                                                    <div class="wpj-jp-pkg-item-row">
                                                        <span class="wpj-jp-pkg-item-tit">
                                                            '.esc_html__("Expiry",'job-portal-theme').' :
                                                        </span>
                                                        <span class="wpj-jp-pkg-item-val">
                                                            '.$package->jobtime.' '.$package->jobtimeunit.'
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="wpj-jp-pkg-item-btm">
                                                    <a href="#" class="wpj-jp-outline-btn wpj-jp-block-btn" onclick="selectPackage('.$package->id.');" title="'.esc_attr__("Select package","job-portal-theme").'">
                                                        '.esc_html__("Select Package","job-portal-theme").'
                                                    </a>
                                                </div>
                                            </div>
                                        ';
                                    }
                                }
                            $content .= '</div>
                            <div class="wpj-jp-popup-msgs" id="wjportal-package-message">&nbsp;</div>
                        </div>
                        <div class="wpj-jp-visitor-msg-btn-wrp">
                            <form action="'.esc_url(wp_nonce_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'copyjob','action'=>'wpjobportaltask','task'=>'addtocopyJob','wpjobportalid'=>$jobid,'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid())),'copy-job')).'" method="post">
                                <input type="hidden" id="wpjobportal_packageid" name="wpjobportal_packageid">
                                <input type="submit" rel="button" id="jsre_featured_button" class="wpj-jp-visitor-msg-btn" value="'.esc_attr__('Add To Job','job-portal-theme').'" disabled/>
                            </form>
                        </div>
                    </div>
                </div>';
            } else {
            $content = '
            <div id="wjportal-popup-background" style="display: none;"></div>
            <div id="package-popup" class="wjportal-popup-wrp wjportal-packages-popup '.$addonclass.'">
                <div class="wjportal-popup-cnt">
                    <img id="wjportal-popup-close-btn" alt="'.__('popup close','wp-job-portal').'" title="'.__('popup close','wp-job-portal').'" src="'.WPJOBPORTAL_PLUGIN_URL.'includes/images/popup-close.png">
                    <div class="wjportal-popup-title">
                        '.__("Select Package",'wp-job-portal').'
                        <div class="wjportal-popup-title3">
                            '.__("Please select a package first",'wp-job-portal').'
                        </div>
                    </div>
                    <div class="wjportal-popup-contentarea">
                        <div class="wjportal-packages-wrp">';
                            if(count($userpackages) == 0 || empty($userpackages)){
                                $content .= WPJOBPORTALmessages::showMessage(__("You do not have any job remaining",'wp-job-portal'),'error',1);
                            } else {
                                foreach($userpackages as $package){
                                    #User Package For Selection in Popup Model --Views
                                    $content .= '
                                        <div class="wjportal-pkg-item" id="package-div-'.$package->id.'" >
                                            <div class="wjportal-pkg-item-top">
                                                <div class="wjportal-pkg-item-title">
                                                    '.$package->title.'
                                                </div>
                                            </div>
                                            <div class="wjportal-pkg-item-btm">
                                                <div class="wjportal-pkg-item-row">
                                                    <span class="wjportal-pkg-item-tit">
                                                        '.__("Job",'wp-job-portal').'. :
                                                    </span>
                                                    <span class="wjportal-pkg-item-val">
                                                        '.($package->job==-1 ? __("Unlimited",'wp-job-portal') : $package->job).'
                                                    </span>
                                                </div>
                                                <div class="wjportal-pkg-item-row">
                                                    <span class="wjportal-pkg-item-tit">
                                                        '.__("Remaining",'wp-job-portal').'. :
                                                    </span>
                                                    <span class="wjportal-pkg-item-val">
                                                        '.($package->job==-1 ? __("Unlimited",'wp-job-portal') : $package->remjob).'
                                                    </span>
                                                </div>
                                                <div class="wjportal-pkg-item-row">
                                                    <span class="wjportal-pkg-item-tit">
                                                        '.__("Expiry",'wp-job-portal').'. :
                                                    </span>
                                                    <span class="wjportal-pkg-item-val">
                                                        '.$package->jobtime.' '.$package->jobtimeunit.'
                                                    </span>
                                                </div>
                                                <div class="wjportal-pkg-item-btn-row">
                                                    <a href="#" class="wjportal-pkg-item-btn" onclick="selectPackage('.$package->id.');">
                                                        '.__("Select Package","wp-job-portal").'
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    ';
                                }
                            }
                        $content .= '</div>
                        <div class="wjportal-popup-msgs" id="wjportal-package-message">&nbsp;</div>
                    </div>
                    <div class="wjportal-visitor-msg-btn-wrp">
                        <form action="'.wp_nonce_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'copyjob','action'=>'wpjobportaltask','task'=>'addtocopyJob','wpjobportalid'=>$jobid,'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid())),'copy-job').'" method="post">
                            <input type="hidden" id="wpjobportal_packageid" name="wpjobportal_packageid">
                            <input type="submit" rel="button" id="jsre_featured_button" class="wjportal-visitor-msg-btn" value="'.__('Add To Job','wp-job-portal').'" disabled/>
                        </form>
                    </div>
                </div>
            </div>';
            }
            echo wp_kses($content, WPJOBPORTAL_ALLOWED_TAGS);
            exit();
    }

    function getMessagekey(){
        $key = 'job';if(wpjobportal::$_common->wpjp_isadmin()){$key = 'admin_'.$key;}return $key;
    }


}

?>
