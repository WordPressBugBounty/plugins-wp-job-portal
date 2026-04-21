<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
*
*/
$wpjobportal_job_listing_fields = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldOrderingData(2);
?>
<div class="wpjobportal-jobs-cnt-wrp">
    <div class="wpjobportal-jobs-middle-wrp">
        <div class="wpjobportal-jobs-data">
            <span class="wpjobportal-jobs-title">
                <a href="<?php echo esc_url_raw(admin_url('admin.php?page=wpjobportal_job&wpjobportallt=formjob&wpjobportalid='.$wpjobportal_job->id)); ?>">
                    <?php echo esc_html($wpjobportal_job->title); ?>
                </a>
            </span>
            <?php
                if ($wpjobportal_job->status == 0) {
                    echo '<span class="wpjobportal-item-status pending">' . esc_html(__('Pending', 'wp-job-portal')) . '</span>';
                } elseif ($wpjobportal_job->status == 1) {
                    echo '<span class="wpjobportal-item-status approved">' . esc_html(__('Approved', 'wp-job-portal')) . '</span>';
                } elseif ($wpjobportal_job->status == -1) {
                    echo '<span class="wpjobportal-item-status rejected">' . esc_html(__('Rejected', 'wp-job-portal')) . '</span>';
                }elseif ($wpjobportal_job->status == 3) {
                    echo '<span class="wpjobportal-item-status rejected">' . esc_html(__('Pending Payment', 'wp-job-portal')) . '</span>';
                }

            ?>
            <?php
                $wpjobportal_print = true;
                $wpjobportal_startdate = date_i18n('Y-m-d',strtotime($wpjobportal_job->startpublishing));
                $wpjobportal_enddate = date_i18n('Y-m-d',strtotime($wpjobportal_job->stoppublishing));
                $wpjobportal_curdate = date_i18n('Y-m-d');
                if($wpjobportal_startdate > $wpjobportal_curdate){
                    $wpjobportal_publishstatus = esc_html(__('Not publish','wp-job-portal'));
                    $wpjobportal_publishstyle = 'background:#FEA702;color:#ffffff;border:unset;';
                }elseif($wpjobportal_startdate <= $wpjobportal_curdate && $wpjobportal_enddate >= $wpjobportal_curdate){
                    $wpjobportal_publishstatus = esc_html(__('Publish','wp-job-portal'));
                    $wpjobportal_publishstyle = 'background:#00A859;color:#ffffff;border:unset;';
                }else{
                    $wpjobportal_publishstatus = esc_html(__('Expired','wp-job-portal'));
                    $wpjobportal_publishstyle = 'background:#ED3237;color:#ffffff;border:unset;';
                }
            ?>
            <?php if($wpjobportal_job->status == 1){ ?>
                <span class="wpjobportal-item-status" style="<?php echo esc_attr($wpjobportal_publishstyle); ?>">
                    <?php echo esc_html($wpjobportal_publishstatus); ?>
                </span>
            <?php } ?>

            <?php
            // new badge
            if(in_array('joblistingenhancer',wpjobportal::$_active_addons)){
                $new_badge_days = (int) wpjobportal::$_config->getConfigValue('job_new_badge_days');
                $is_new_job = false;
                if(!empty($new_badge_days)){ // to handle no badge case 'vakue will be zero'
                    if (!empty($wpjobportal_job->created)) {
                        $job_created_timestamp = strtotime($wpjobportal_job->created);
                        $current_timestamp = current_time('timestamp');
                        $days_old = round(($current_timestamp - $job_created_timestamp) / (60 * 60 * 24));
                        if ($days_old <= $new_badge_days) {
                            $is_new_job = true;
                        }
                    }


                    if ($is_new_job){ ?>
                        <span class="wpjp-badge-new">
                            <?php echo esc_html__('New', 'wp-job-portal'); ?>
                        </span>
                    <?php
                    }
                }
            }
                 ?>




            <?php
                //$goldflag = true;
                //do_action('wpjobportal_addons_admin_feature_lable_for_job',$wpjobportal_job);
            ?>
        </div>
        <div class="wpjobportal-jobs-data">
            <a class="wpjobportal-companyname" href="<?php echo esc_url_raw(admin_url('admin.php?page=wpjobportal_job&wpjobportallt=formjob&wpjobportalid='.$wpjobportal_job->id)); ?>">
                <?php echo esc_html($wpjobportal_job->companyname); ?>
            </a>
        </div>
        
        <div class="wpjobportal-jobs-data">
            <?php if(isset($wpjobportal_job_listing_fields['jobcategory']) && $wpjobportal_job_listing_fields['jobcategory'] !='' ){ ?>
                <span class="wpjobportal-jobs-data-text wpjobportal-listing-data-category">
                    <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_job->cat_title)); ?>
                </span>
            <?php }
            if(isset($wpjobportal_job_listing_fields['city']) && $wpjobportal_job_listing_fields['city'] !='' ){ ?>
                <span class="wpjobportal-jobs-data-text wpjobportal-listing-data-location">
                    <?php echo esc_html(WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($wpjobportal_job->city)); ?>
                </span>
            <?php } ?>
        </div>
    </div>
    <div class="wpjobportal-jobs-right-wrp">
        <?php if(isset($wpjobportal_job_listing_fields['jobtype']) && $wpjobportal_job_listing_fields['jobtype'] !='' ){ ?>
            <div class="wpjobportal-jobs-info">

                <?php
                if(isset($wpjobportal_job_listing_fields['workplace_type']) && $wpjobportal_job_listing_fields['workplace_type'] !='' ){
                        if (isset($wpjobportal_job->workplace_type) && $wpjobportal_job->workplace_type != 0) {
                            $wpjp_badge_class = '';
                            $wpjp_badge_label = '';
                            $wpjp_badge_icon  = '';

                            switch ($wpjobportal_job->workplace_type) {
                                case '1': // On-site
                                    $wpjp_badge_class = 'wpjp-badge-onsite';
                                    $wpjp_badge_label = esc_html__('On-site', 'wp-job-portal');
                                    $wpjp_badge_icon  = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="wpjp-badge-icon"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><line x1="9" y1="22" x2="9" y2="22"></line><line x1="15" y1="22" x2="15" y2="22"></line><line x1="12" y1="18" x2="12" y2="18"></line><line x1="12" y1="14" x2="12" y2="14"></line><line x1="12" y1="10" x2="12" y2="10"></line><line x1="8" y1="18" x2="8" y2="18"></line><line x1="8" y1="14" x2="8" y2="14"></line><line x1="8" y1="10" x2="8" y2="10"></line><line x1="16" y1="18" x2="16" y2="18"></line><line x1="16" y1="14" x2="16" y2="14"></line><line x1="16" y1="10" x2="16" y2="10"></line></svg>';
                                    break;
                                case '2': // Hybrid
                                    $wpjp_badge_class = 'wpjp-badge-hybrid';
                                    $wpjp_badge_label = esc_html__('Hybrid', 'wp-job-portal');
                                    $wpjp_badge_icon  = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="wpjp-badge-icon"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>';
                                    break;
                                case '3': // Remote
                                    $wpjp_badge_class = 'wpjp-badge-remote';
                                    $wpjp_badge_label = esc_html__('Remote', 'wp-job-portal');
                                    $wpjp_badge_icon  = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="wpjp-badge-icon"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>';
                                    break;
                            }

                            if (!empty($wpjp_badge_class)) {
                                echo '<div class="wpjp-badge-mode ' . esc_attr($wpjp_badge_class) . '">';
                                echo $wpjp_badge_icon;
                                echo '<span>' . $wpjp_badge_label . '</span>';
                                echo '</div>';
                            }
                        }
                }
                ?>
                <span class="wpjobportal-jobs-type" style="background: <?php echo esc_attr($wpjobportal_job->jobtypecolor); ?>;">
                    <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_job->jobtypetitle)); ?>
                </span>
            </div>
        <?php } ?>
        <?php if(isset($wpjobportal_job_listing_fields['jobsalaryrange']) && $wpjobportal_job_listing_fields['jobsalaryrange'] !='' ){ ?>
        <div class="wpjobportal-jobs-info">
            <div class="wpjobportal-jobs-salary">
                <?php echo esc_html(wpjobportal::$_common->getSalaryRangeView($wpjobportal_job->salarytype, $wpjobportal_job->salarymin, $wpjobportal_job->salarymax,$wpjobportal_job->currency)); ?>
                <?php if($wpjobportal_job->salarytype==3 || $wpjobportal_job->salarytype==2) { ?>
                    <span class="wpjobportal-salary-type"> <?php echo ' / ' .esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_job->salaryrangetype)); ?></span>
                <?php }?>
            </div>
        </div>
        <?php } ?>
        <div class="wpjobportal-jobs-info">
            <?php
                $wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
                echo esc_html(human_time_diff(strtotime($wpjobportal_job->created) ,strtotime(date_i18n("Y-m-d H:i:s")))).' '.esc_html(__("Ago",'wp-job-portal'));
            ?>
        </div>
    </div>
    <?php
    // urgent badge
    if(in_array('joblistingenhancer',wpjobportal::$_active_addons)){
        if(isset($wpjobportal_job_listing_fields['is_urgent']) && $wpjobportal_job_listing_fields['is_urgent'] !='' ){
            $is_urgent_job = false;
            if (isset($wpjobportal_job->is_urgent) && $wpjobportal_job->is_urgent == 1) {
                $is_urgent_job = true;
            }
            if ($is_urgent_job) { ?>
            <span class="wpjp-badge-urgent-flag">
                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                <?php echo esc_html__('Urgent', 'wp-job-portal'); ?>
            </span>
            <?php
            }
        }
    }
    ?>
</div>
