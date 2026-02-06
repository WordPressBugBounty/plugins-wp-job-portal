<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param job      job object - optional
 */
?>
<?php

switch ($wpjobportal_control) {
    case 'resume':
        $wpjobportal_featuredflag = true;
        $wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
        $wpjobportal_curdate = date_i18n('Y-m-d');
        $wpjobportal_featuredexpiry = date_i18n('Y-m-d', strtotime($wpjobportal_resume->endfeatureddate));
        if ($wpjobportal_resume->isfeaturedresume == 1 && $wpjobportal_featuredexpiry >= $wpjobportal_curdate) {
            $wpjobportal_featuredflag = false;
        }
        ?>

        <div id="item-actions" class="wpjobportal-resume-action-wrp">
            <?php 
                $wpjobportal_config_array = wpjobportal::$_data['config'];
             ?>
            <a class="wpjobportal-resume-act-btn" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_resume&task=removeresume&wpjobportal-cb[]='.esc_attr($wpjobportal_resume->id).'&action=wpjobportaltask&callfrom=1'),'wpjobportal_resume_nonce')) ;?>" onclick='return confirm("<?php echo esc_js(__('Are you sure to delete','wp-job-portal')).' ?'; ?>");' title="<?php echo esc_attr(__('delete', 'wp-job-portal')); ?>">
                <?php echo esc_html(__('Delete', 'wp-job-portal')); ?>
            </a>
            <a class="wpjobportal-resume-act-btn" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_resume&task=resumeEnforceDelete&action=wpjobportaltask&resumeid='.esc_attr($wpjobportal_resume->id).'&callfrom=1'),'wpjobportal_resume_nonce')) ;?>" onclick='return confirmdelete("<?php echo esc_js(__('Are you sure to force delete', 'wp-job-portal')).' ?'; ?>");' title="<?php echo esc_attr(__('enforce delete', 'wp-job-portal')) ?>">
                <?php echo esc_html(__('Enforce Delete', 'wp-job-portal')) ?>
            </a>
            <?php do_action('wpjobportal_addons_feature_for_resume',$wpjobportal_config_array,$wpjobportal_resume,$wpjobportal_featuredflag); ?>
            <a class="wpjobportal-resume-act-btn" href="admin.php?page=wpjobportal_resume&wpjobportallt=formresume&wpjobportalid=<?php echo esc_attr($wpjobportal_resume->id); ?>" title="<?php echo esc_attr(__('edit', 'wp-job-portal')); ?>">
                <?php echo esc_html(__('Edit', 'wp-job-portal')); ?>
            </a>
            <a class="wpjobportal-resume-act-btn" href="admin.php?page=wpjobportal_resume&wpjobportallt=viewresume&wpjobportalid=<?php echo esc_attr($wpjobportal_resume->id); ?>" title="<?php echo esc_attr(__('view', 'wp-job-portal')); ?>">
                <?php echo esc_html(__('View', 'wp-job-portal')); ?>
            </a>
        </div>
        <?php
    break;
    case 'resumeque':
        $wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
        ?>
        <div class="wpjobportal-resume-action-wrp">
            <a class="wpjobportal-resume-act-btn" href="admin.php?page=wpjobportal_resume&wpjobportallt=viewresume&wpjobportalid=<?php echo esc_attr($wpjobportal_resume->id); ?>" title="<?php echo esc_attr(__('view', 'wp-job-portal')); ?>">
                <?php echo esc_html(__('View', 'wp-job-portal')); ?>
            </a>                  
            <a class="wpjobportal-resume-act-btn" href="admin.php?page=wpjobportal_resume&wpjobportallt=formresume&wpjobportalid=<?php echo esc_attr($wpjobportal_resume->id); ?>" title="<?php echo esc_attr(__('edit', 'wp-job-portal')); ?>">
                <?php echo esc_html(__('Edit', 'wp-job-portal')); ?>
            </a>
            <?php
                $wpjobportal_total = count($wpjobportal_arr);
                if ($wpjobportal_total == 3) {
                    $wpjobportal_objid = 4; //for all
                } elseif ($wpjobportal_total != 1) {
                }
                if ($wpjobportal_total == 1) {
                    if (isset($wpjobportal_arr['self'])) {
                        ?>
                        <a class="wpjobportal-resume-act-btn" href="admin.php?page=wpjobportal_resume&task=approveQueueResume&id=<?php echo esc_attr($wpjobportal_resume->id); ?>&action=wpjobportaltask&_wpnonce=<?php echo esc_attr(wp_create_nonce('wpjobportal_resume_nonce'));?>" title="<?php echo esc_attr(__('approve', 'wp-job-portal')); ?>">
                            <?php echo esc_html(__('Approve', 'wp-job-portal')); ?>
                        </a>
                    <?php
                    }
                    if (isset($wpjobportal_arr['feature']) && in_array('featureresume', wpjobportal::$_active_addons)) {
                        ?>
                        <a class="wpjobportal-resume-act-btn" href="admin.php?page=wpjobportal_resume&task=approveQueueFeatureResume&id=<?php echo esc_attr($wpjobportal_resume->id); ?>&action=wpjobportaltask&_wpnonce=<?php echo esc_attr(wp_create_nonce('wpjobportal_resume_nonce'));?>" title="<?php echo esc_attr(__('feature approve', 'wp-job-portal')); ?>">
                            <?php echo esc_html(__('Feature Approve', 'wp-job-portal')); ?>
                        </a>
                    <?php
                    }
                } /*else {
                    ?>
                    <div class="wpjobportal-resume-act-btn jobsqueue-approvalqueue" onmouseout="hideThis(this);" onmouseover='approveActionPopup("<?php echo esc_js($wpjobportal_resume->id); ?>");'>
                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/publish-icon.png">
                        <?php echo esc_html(__('Approve', 'wp-job-portal')); ?>
                        <div id="wpjobportal-queue-actionsbtn" class="jobsqueueapprove_<?php echo esc_attr($wpjobportal_resume->id); ?>">
                            <?php if (isset($wpjobportal_arr['self'])) { ?>
                                <a id="wpjobportal-act-row" class="wpjobportal-act-row" href="admin.php?page=wpjobportal_resume&task=approveQueueResume&id=<?php echo esc_url($wpjobportal_resume->id); ?>&action=wpjobportaltask&_wpnonce=<?php echo esc_attr(wp_create_nonce('wpjobportal_resume_nonce'));?>"><img class="jobs-action-image" src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/comapny-logo.png"><?php echo esc_html(__("Resume Approve", 'wp-job-portal')); ?></a>
                            <?php } ?>
                            <a id="wpjobportal-act-row-all" class="wpjobportal-act-row-all" href="admin.php?page=wpjobportal_resume&task=approveQueueAllResumes&objid=<?php echo esc_url($wpjobportal_objid); ?>&id=<?php echo esc_url($wpjobportal_resume->id); ?>&action=wpjobportaltask&_wpnonce=<?php echo esc_attr(wp_create_nonce('wpjobportal_resume_nonce'));?>">
                                <img class="jobs-action-image" src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/select-all.png">
                                <?php echo esc_html(__("All Approve", 'wp-job-portal')); ?>
                            </a>
                        </div>
                    </div>
                    <?php
                } // End approve */
                if ($wpjobportal_total == 1) {
                    if (isset($wpjobportal_arr['self'])) {
                        ?>
                        <a class="wpjobportal-resume-act-btn" href="admin.php?page=wpjobportal_resume&task=rejectQueueResume&id=<?php echo esc_attr($wpjobportal_resume->id); ?>&action=wpjobportaltask&_wpnonce=<?php echo esc_attr(wp_create_nonce('wpjobportal_resume_nonce'));?>" title="<?php echo esc_attr(__('reject', 'wp-job-portal')); ?>">
                            <?php echo esc_html(__('Reject', 'wp-job-portal')); ?>
                        </a>
                    <?php
                    }
                    if (isset($wpjobportal_arr['feature']) && in_array('featureresume', wpjobportal::$_active_addons)) {
                        ?>
                        <a class="wpjobportal-resume-act-btn" href="admin.php?page=wpjobportal_resume&task=rejectQueueFeatureResume&id=<?php echo esc_attr($wpjobportal_resume->id); ?>&action=wpjobportaltask&_wpnonce=<?php echo esc_attr(wp_create_nonce('wpjobportal_resume_nonce'));?>" title="<?php echo esc_attr(__('feature reject', 'wp-job-portal')); ?>">
                            <?php echo esc_html(__('Feature Reject', 'wp-job-portal')); ?>
                        </a>
                    <?php
                    }
                } /*else {
                    ?>
                    <div class="wpjobportal-resume-act-btn jobsqueue-approvalqueue" onmouseout="hideThis(this);" onmouseover='rejectActionPopup("<?php echo esc_attr($wpjobportal_resume->id); ?>");'><img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/reject-s.png">  <?php echo esc_html(__('Reject', 'wp-job-portal')); ?>
                        <div id="wpjobportal-queue-actionsbtn" class="jobsqueuereject_<?php echo esc_attr($wpjobportal_resume->id); ?>">
                            <?php if (isset($wpjobportal_arr['self'])) { ?>
                                <a id="wpjobportal-act-row" class="wpjobportal-act-row" href="admin.php?page=wpjobportal_resume&task=rejectQueueResume&id=<?php echo esc_url($wpjobportal_resume->id); ?>&action=wpjobportaltask&_wpnonce=<?php echo esc_attr(wp_create_nonce('wpjobportal_resume_nonce'));?>">
                                    <img class="jobs-action-image" src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/comapny-logo.png">
                                    <?php echo esc_html(__("Resume Reject", 'wp-job-portal')); ?>
                                </a>
                            <?php
                            } ?>
                            <a id="wpjobportal-act-row-all" class="wpjobportal-act-row-all" href="admin.php?page=resume&task=rejectQueueAllResumes&objid=<?php echo esc_url($wpjobportal_objid); ?>&id=<?php echo esc_url($wpjobportal_resume->id); ?>&action=wpjobportaltask&_wpnonce=<?php echo esc_attr(wp_create_nonce('wpjobportal_resume_nonce'));?>">
                                <img class="jobs-action-image" src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/select-all.png">
                                <?php echo esc_html(__("All Reject", 'wp-job-portal')); ?>
                            </a>
                        </div>
                    </div>
            <?php }//End Reject */ ?>
            <a class="wpjobportal-resume-act-btn" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_resume&task=removeresume&wpjobportal-cb[]='.esc_attr($wpjobportal_resume->id)),'wpjobportal_resume_nonce')); ?>&action=wpjobportaltask&callfrom=2" onclick='return confirm("<?php echo esc_js(__('Are you sure to delete','wp-job-portal')).' ?'; ?>");' title="<?php echo esc_attr(__('delete', 'wp-job-portal')); ?>">
                <?php echo esc_html(__('Delete', 'wp-job-portal')); ?>
            </a>
            <a class="wpjobportal-resume-act-btn" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_resume&task=resumeEnforceDelete&resumeid='.esc_attr($wpjobportal_resume->id)),'wpjobportal_resume_nonce')); ?>&action=wpjobportaltask&callfrom=2" onclick='return confirmdelete("<?php echo esc_js(__('This will delete every thing about this record','wp-job-portal')).'. '.esc_html(__('Are you sure to delete','wp-job-portal')).'?'; ?>");'  title="<?php echo esc_attr(__('force delete', 'wp-job-portal')); ?>">
                <?php echo esc_html(__('Force Delete', 'wp-job-portal')); ?>
            </a>
        </div>
        <?php
    break;
    case 'jobapply':
        $wpjobportal_class = 'wpjobportal-resume-act-btn';
        ?>
         <div id="item-actions" class="wpjobportal-resume-action-wrp">
            <a id="view-resume" class="wpjobportal-resume-act-btn" href="<?php echo esc_url_raw(admin_url('admin.php?page=wpjobportal_resume&wpjobportallt=formresume&wpjobportalid='.esc_attr($wpjobportal_data->appid))); ?>" title="<?php echo esc_attr(__('view profile', 'wp-job-portal')); ?>">
                <?php echo esc_html(__('View Profile', 'wp-job-portal')); ?>
            </a>
            <?php 
                do_action('wpjobportal_addons_resume_bottom_action_appliedresume',$wpjobportal_data,$wpjobportal_class);
                do_action('wpjobportal_addons_resume_bottom_action_appliedresume_exc',wpjobportal::$_data['jobid'],$wpjobportal_data);
            ?>
        </div>
        <?php
        break;
}
