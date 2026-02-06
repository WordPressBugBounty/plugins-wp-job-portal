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
</div>
