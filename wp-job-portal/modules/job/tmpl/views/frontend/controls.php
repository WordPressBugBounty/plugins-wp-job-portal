<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
* @param WP JOB PORTAL
* @param WP Control My Jobs
* @param Feature Job - Copy Job
*/
?>
<?php
switch ($wpjobportal_control) {
    case 'myjobs':
        $wpjobportal_featuredexpiry = date_i18n('Y-m-d', strtotime($wpjobportal_job->endfeatureddate));
        $wpjobportal_print = WPJOBPORTALincluder::getJSModel('job')->checkLinks('noofjobs');
        $wpjobportal_startdate = date_i18n('Y-m-d',strtotime($wpjobportal_job->startpublishing));
        $wpjobportal_enddate = date_i18n('Y-m-d',strtotime($wpjobportal_job->stoppublishing));
        $wpjobportal_curdate = date_i18n('Y-m-d');
        echo '<div class="wjportal-jobs-list-btm-wrp">
                <div class="wjportal-jobs-action-wrp">';
                    if($wpjobportal_job->status == 1 || $wpjobportal_job->status == 3){
                        echo '<a class="wjportal-jobs-act-btn wjportal-list-act-btn-edit" title ='.esc_html(__('Edit Job','wp-job-portal')).' href='. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'addjob', 'wpjobportalid'=>$wpjobportal_job->id))).'>'. esc_html(__('Edit Job', 'wp-job-portal')).'</a>';
                    }

                    $wpjobportal_config_array = wpjobportal::$_data['config'];
                    if($wpjobportal_job->status != 3 && $wpjobportal_job->status != 4){
                        #Feature Job--
                        do_action('wpjobportal_credit_addons_feature_job_popup',$wpjobportal_config_array,$wpjobportal_job,$wpjobportal_featuredexpiry);
                    }
                    if($wpjobportal_job->status != 4){ ?>
                    <a class="wjportal-jobs-act-btn wjportal-list-act-btn-delete" href="<?php echo esc_url(wp_nonce_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'task'=>'remove', 'action'=>'wpjobportaltask', 'wpjobportal-cb[]'=>$wpjobportal_job->id,'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid())),'wpjobportal_job_nonce')); ?>" onclick='return confirmdelete("<?php echo esc_js(__('Are you sure to delete','wp-job-portal')).' ?'; ?>");'><?php echo esc_html(__('Delete Job', 'wp-job-portal')); ?></a>
                    <?php }
                    # Copy Job --
                    do_action('wpjobportal_addons_credit_popup_copy_job',$wpjobportal_job);
                    if($wpjobportal_job->status != 3 && $wpjobportal_job->status != 4 ){
                       echo '<a class="wjportal-jobs-act-btn wjportal-jobs-apply-res wjportal-list-act-btn-applied-resumes" title = "'.esc_attr(__('Resume','wp-job-portal')).'" href='. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobapply', 'wpjobportallt'=>'jobappliedresume', 'jobid'=>$wpjobportal_job->id))).'>'. esc_html(__('Resume', 'wp-job-portal')) . " (" . esc_html($wpjobportal_job->resumeapplied) . ")".'</a>';
                    }
                   if($wpjobportal_job->status == 0){
                        echo '
                                <span class="wjportal-item-act-status wjportal-waiting">'. esc_html(__('Waiting For Approval', 'wp-job-portal')).'</span>
                            ';
                    }elseif($wpjobportal_job->status == -1){
                        #Rejected Job
                        echo '
                                <span class="wjportal-item-act-status wjportal-rejected">'.esc_html(__('Rejected', 'wp-job-portal')).'</span>
                            ';
                 }elseif ($wpjobportal_job->status == 3) {
                    # job perlisting --payment
                    do_action('wpjobportal_addons_makePayment_for_department',$wpjobportal_job,'payjob');
                }  
                if(in_array('aisuggestedresumes', wpjobportal::$_active_addons)){
                    $wpjobportal_show_suggested_resumes_button = wpjobportal::$_config->getConfigValue('show_suggested_resumes_button');
                    if($wpjobportal_show_suggested_resumes_button == 1){ ?>
                        <a class="wjportal-jobs-act-btn wjportal-jobs-act-btn-ai-suggested-resumes" href="<?php echo esc_url(wp_nonce_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'resumes', 'aisuggestedresumes_job'=>$wpjobportal_job->jobaliasid,'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid())),'wpjobportal_job_nonce')); ?>"><?php echo esc_html(__('Suggested Resumes', 'wp-job-portal')); ?></a>
                    <?php
                    }
                }

                // close action wrp
        echo '</div> 
            </div>'; /* close bottom wrp */
        break;
        case 'resumetitle':
         ?><div class="wjportal-jobs-list-btm-wrp" id="full-width-top">
            <div class="wjportal-jobs-list-resume-wrp">
                <?php
                    if(in_array('credits', wpjobportal::$_active_addons) && $wpjobportal_job->applystatus == 3){
                       do_action('wpjobportal_addons_makePayment_for_department',$wpjobportal_job,'payjobapply');
                    }
                    $wpjobportal_val_lable = __('Name', 'wp-job-portal');
                    $wpjobportal_val_value = $wpjobportal_job->first_name . ' ' .$wpjobportal_job->last_name;

                    if($wpjobportal_job->application_title != ''){
                       $wpjobportal_val_lable = __('Resume Title', 'wp-job-portal');
                       $wpjobportal_val_value = $wpjobportal_job->application_title;
                    }
                ?>
                <div class="wjportal-jobs-list-resume-data">
                    <span class="wjportal-jobs-list-resume-tit">
                        <?php echo esc_html($wpjobportal_val_lable).': '; ?>
                    </span>
                    <span class="wjportal-jobs-list-resume-val">
                        <a href="<?php echo esc_url( wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume','wpjobportallt'=>'viewresume','wpjobportalid'=>$wpjobportal_job->resumeid,'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid())) );?>">
                            <?php echo esc_html($wpjobportal_val_value);?>
                        </a>
                    </span> 
                </div>
                <!-- applied job resume status -->

                    <?php do_action('wpjobportal_addons_resume_action_jobapplied_status',$wpjobportal_job);

                    if(in_array('coverletter', wpjobportal::$_active_addons) ){ ?>
                        <div class="wjportal-jobs-list-resume-data">
                            <span class="wjportal-jobs-list-resume-tit">
                                <?php echo esc_html(__('Cover Letter Title', 'wp-job-portal')).': '; ?>
                            </span>
                            <span class="wjportal-jobs-list-resume-val">
                                <?php
                                    echo esc_html($wpjobportal_job->coverlettertitle);
                                ?>
                            </span>
                        </div>
                    <?php }
                    /*
                    if(isset($wpjobportal_job->apply_message) && $wpjobportal_job->apply_message !=''){
                    $wpjobportal_apply_message_label = wpjobportal::$_wpjpfieldordering->getFieldTitleByFieldAndFieldfor('message',5);
                    ?>
                        <div class="wjportal-jobs-list-resume-data">
                            <span class="wjportal-jobs-list-resume-tit">
                                <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_apply_message_label)).': '; ?>
                            </span>
                            <span class="wjportal-jobs-list-resume-val">
                                <?php
                                    echo esc_html($wpjobportal_job->apply_message);
                                ?>
                            </span>
                        </div>
                    <?php }
                    */ ?>
                </div>
            </div>
            <?php
        break;
    case 'newestjobs': ?>
        <?php
        $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        $wpjobportal_desc = __("Apply Now", 'wp-job-portal');
        $wpjobportal_extra_class_job_applied = '';
        if(is_numeric($wpjobportal_uid) && $wpjobportal_uid > 0){
            $wpjobportal_applied =  WPJOBPORTALincluder::getJSmodel('jobapply')->checkAlreadyAppliedJob($wpjobportal_job->jobid,$wpjobportal_uid);
            if ($wpjobportal_applied == false) {
                $wpjobportal_desc = __("You have Already Applied", 'wp-job-portal');
                $wpjobportal_extra_class_job_applied = 'wp-job-portal-already-applied';
            }
        }
        ?>
        <div class="wjportal-jobs-list-btm-wrp">
                <div class="wjportal-newest-jobs-date">
                    <?php
                        $wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
                        echo esc_html(human_time_diff(strtotime($wpjobportal_job->created),strtotime(date_i18n("Y-m-d H:i:s"))).' '.esc_html(__("Ago",'wp-job-portal')));
                    ?>
                </div>
            
            <div class="wjportal-jobs-action-wrp">

                <?php 
                $wpjobportal_allow_jobshortlist  = wpjobportal::$_config->getConfigurationByConfigName('allow_jobshortlist');
                if($wpjobportal_allow_jobshortlist == 1 AND (! WPJOBPORTALincluder::getObjectClass('user')->isemployer())){
                    if(!WPJOBPORTALincluder::getObjectClass('user')->isguest()){
                        do_action('wpjobportal_addons_newestjob_btm_btn_for_shortlist',$wpjobportal_job);
                    }
                }
                $wpjobportal_allow_tellafriend  = wpjobportal::$_config->getConfigurationByConfigName('allow_tellafriend');
                if($wpjobportal_allow_tellafriend == 1){
                   do_action('wpjobportal_addons_tellfriend_shorlist',$wpjobportal_job->jobid);
                } ?>
                <?php
                $wpjobportal_showapplybutton  = wpjobportal::$_config->getConfigurationByConfigName('showapplybutton');
                if($wpjobportal_showapplybutton == 1){
                    ?>
                    <a class="wjportal-jobs-act-btn-apply <?php echo esc_attr( $wpjobportal_extra_class_job_applied );?>" href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'viewjob', 'wpjobportalid'=>$wpjobportal_job->jobaliasid))); ?>" ><?php echo esc_html($wpjobportal_desc); ?></a><?php
                }
                ?>
            </div>
        </div>
        <?php
        break;
    case 'shortlistjob': ?>
        <?php
            $wpjobportal_applied =  WPJOBPORTALincluder::getJSmodel('jobapply')->checkAlreadyAppliedJob($wpjobportal_job->jobid,WPJOBPORTALincluder::getObjectClass('user')->uid());
            if ($wpjobportal_applied == false) {
                $wpjobportal_desc = __("You have Already Applied", 'wp-job-portal');
            }else{
                $wpjobportal_desc = __("Apply Now", 'wp-job-portal');
            }
        ?>
        <div class="wjportal-jobs-list-btm-wrp">
            <div class="wjportal-jobs-action-wrp">
                <?php $wpjobportal_allow_tellafriend  = wpjobportal::$_config->getConfigurationByConfigName('allow_tellafriend');
                if($wpjobportal_allow_tellafriend == 1){
                   do_action('wpjobportal_addons_tellfriend_shorlist',$wpjobportal_job->jobid);
                 } ?>
               <a class="wjportal-jobs-act-btn wjportal-jobs-act-btn-delete-shortlist" href="<?php echo  esc_url(wp_nonce_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid(),'wpjobportalme'=>'shortlist', 'action'=>'wpjobportaltask', 'task'=>'removeshortlist', 'wpjobportalid'=>$wpjobportal_job->slid)),'wpjobportal_shortlist_job_nonce')); ?>"><?php echo esc_html(__('Delete Job', 'wp-job-portal')); ?></a><?php
                $wpjobportal_config_array = wpjobportal::$_data['config'];
                /*
                $wpjobportal_show_apply_form  = wpjobportal::$_config->getConfigurationByConfigName('quick_apply_for_user');
                if (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
                    $wpjobportal_show_apply_form  = wpjobportal::$_config->getConfigurationByConfigName('quick_apply_for_visitor');
                }
                if($wpjobportal_show_apply_form == 0){ // hide apply now button if quick apply is enabled
                    if($wpjobportal_config_array['showapplybutton'] == 1){
                        if($wpjobportal_job->jobapplylink == 1 && !empty($wpjobportal_job->joblink)){
                            if(!wpjobportalphplib::wpJP_strstr('http',$wpjobportal_job->joblink)){
                                $wpjobportal_job->joblink = 'http://'.$wpjobportal_job->joblink;
                            } ?>
                            <a class="wjportal-jobs-act-btn" href= "<?php echo esc_url($wpjobportal_job->joblink) ;?>" target="_blank" ><?php echo esc_html(__('Apply Now','wp-job-portal')); ?></a><?php
                        }elseif(!empty($wpjobportal_config_array['applybuttonredirecturl'])){
                            if(!wpjobportalphplib::wpJP_strstr('http',$wpjobportal_config_array['applybuttonredirecturl'])){
                                $wpjobportal_joblink = 'http://'.$wpjobportal_config_array['applybuttonredirecturl'];
                            }else{
                                $wpjobportal_joblink = $wpjobportal_config_array['applybuttonredirecturl'];
                            } ?>
                            <a class="wjportal-jobs-act-btn" href= "<?php echo esc_url($wpjobportal_joblink); ?>" target="_blank" ><?php echo esc_html(__('Apply Now','wp-job-portal')); ?></a><?php
                        }else{
                            if(WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()){
                                if(in_array('credits', wpjobportal::$_active_addons)){
                                    //if($wpjobportal_applied == true){
                                    $wpjobportal_submission_type = wpjobportal::$_config->getConfigValue('submission_type');
                                    if($wpjobportal_submission_type == 1){
                                        if($wpjobportal_applied == true){?>
                                            <a class="wjportal-jobs-act-btn" href="#" onclick="wpjobportalPopup('job_apply', '<?php echo esc_js($wpjobportal_job->jobid); ?>',<?php echo esc_js(wpjobportal::wpjobportal_getPageid());?>)"><?php echo esc_html(__('Apply Now', 'wp-job-portal')) ?></a><?php
                                        }else{
                                            echo'<a class="wjportal-job-jobapply-btn wjportal-jobs-act-btn" href="#" >'. esc_html(__("You have Already Applied",'wp-job-portal')) .' </a>';
                                        }
                                    }elseif ($wpjobportal_submission_type == 2) {
                                        $wpjobportal_payment = WPJOBPORTALincluder::getJSmodel('jobapply')->checkjobappllystats($wpjobportal_job->jobid,WPJOBPORTALincluder::getObjectClass('user')->uid());
                                       // echo $wpjobportal_payment;
                                        //echo $wpjobportal_applied;
                                        if($wpjobportal_payment == true && $wpjobportal_payment == false){ ?>
                                            <a class="wjportal-jobs-act-btn" href="#" onclick="wpjobportalPopup('job_apply', '<?php echo esc_js($wpjobportal_job->jobid); ?>',<?php echo esc_js(wpjobportal::wpjobportal_getPageid());?>)"><?php echo esc_html(__('Apply Now', 'wp-job-portal')) ?></a><?php
                                        }
                                        if($wpjobportal_payment == false && $wpjobportal_applied != true){
                                                $wpjobportal_arr = array('wpjobportalme'=>'purchasehistory','wpjobportallt'=>'payjobapply','wpjobportalid'=>$wpjobportal_job->jobid);
                                                echo '<a class="wjportal-job-act-btn" href='. esc_url(wpjobportal::wpjobportal_makeUrl($wpjobportal_arr)).' title='. esc_attr(__('make payment','wp-job-portal')).'>
                                                 '. esc_html(__('Make Payment To Apply', 'wp-job-portal')).'
                                                 </a>';
                                        }else{
                                                echo'<a class="wjportal-job-jobapply-btn wjportal-jobs-act-btn" href="#" >'. esc_html(__("You have Already Applied",'wp-job-portal')) .' </a>';
                                        }
                                    }elseif ($wpjobportal_submission_type == 3) {
                                        if($wpjobportal_applied == true){

                                         echo'<a class="wjportal-job-jobapply-btn wjportal-jobs-act-btn" href="#" onclick="getPackagePopupJobView('. esc_js($wpjobportal_job->jobid) .')">'. esc_html(__("Apply On This Job",'wp-job-portal')) .' </a>';
                                        }else{
                                            echo'<a class="wjportal-job-jobapply-btn wjportal-jobs-act-btn" href="#" >'. esc_html(__("You have Already Applied",'wp-job-portal')) .' </a>';
                                        }
                                    }
                                }else{ ?>
                                    <a class="wjportal-jobs-act-btn" href="#" onclick="getApplyNowByJobid('<?php echo esc_js($wpjobportal_job->jobid); ?>',<?php echo esc_js(wpjobportal::wpjobportal_getPageid());?>)"><?php echo esc_html(__('Apply Now', 'wp-job-portal')) ?></a><?php
                                }
                            }else{ ?>
                                <a class="wjportal-jobs-act-btn" href="#" onclick="getApplyNowByJobid('<?php echo esc_js($wpjobportal_job->jobid); ?>',<?php echo esc_js(wpjobportal::wpjobportal_getPageid());?>);"><?php echo esc_html($wpjobportal_desc); ?></a><?php
                            }
                        }
                    }
                }// closing if for quick apply check
                */
                ?>
                <div class="wjportal-shortlist-stars">
                    <?php
                        if(isset($wpjobportal_control)){
                            if($wpjobportal_control == "shortlistjob"){
                                do_action('wpjobportal_addons_upper_lable_shortlist_rating',$wpjobportal_job);
                            }
                        }
                    ?>
                </div>
            </div>
        </div>
    <?php   break;
    case 'payjob': ?>
        <div class="wjportal-jobs-list-btm-wrp">
            <div class="wjportal-jobs-action-wrp"> <?php
                do_action('wpjobportal_addons_proceedPayment_PerListing',$wpjobportal_job->jobid,'job','myjobs');
                ?>
            </div>
        </div> <?php
        break;
    case 'payjobapply': ?>
            <div class="wjportal-jobs-list-btm-wrp">
                <div class="wjportal-jobs-action-wrp"> <?php
                    do_action('wpjobportal_addons_proceedPayment_PerListing',$wpjobportal_job->jobaliasid,'job','viewjob');
                    ?>
                </div>
            </div> <?php
        break;
}

       
