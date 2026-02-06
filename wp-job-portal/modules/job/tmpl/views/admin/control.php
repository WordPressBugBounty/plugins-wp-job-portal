<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
* 
*/
$wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
?>
<?php
$wpjobportal_html = '';
 switch ($wpjobportal_layout) {
 	case 'control':
        $wpjobportal_config_array = wpjobportal::$_data['config'];
        $wpjobportal_featuredflag = true;
        $wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
        $wpjobportal_curdate = date_i18n('Y-m-d');
        $wpjobportal_featuredexpiry = date_i18n('Y-m-d', strtotime($wpjobportal_job->endfeatureddate));
        if ($wpjobportal_job->isfeaturedjob == 1 && $wpjobportal_featuredexpiry >= $wpjobportal_curdate) {
            $wpjobportal_featuredflag = false;
        }
 		?>
        <div id="for_ajax_only_<?php echo esc_attr($wpjobportal_job->id); ?>">
            <div id="item-actions" class="wpjobportal-jobs-action-wrp">
                <a class="wpjobportal-jobs-act-btn" href="<?php echo esc_url_raw(admin_url('admin.php?page=wpjobportal_job&wpjobportallt=formjob&wpjobportalid='.$wpjobportal_job->id)); ?>" title="<?php echo esc_attr(__('edit', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Edit', 'wp-job-portal')); ?>
                </a>
                <a class="wpjobportal-jobs-act-btn" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_job&action=wpjobportaltask&task=remove&callfrom=1&wpjobportal-cb[]='.$wpjobportal_job->id),'wpjobportal_job_nonce')); ?>" onclick='return confirm("<?php echo esc_js(__('Are you sure to delete','wp-job-portal')).' ?'; ?>");' title="<?php echo esc_attr(__('delete', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Delete', 'wp-job-portal')); ?>
                </a>
                <a class="wpjobportal-jobs-act-btn" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_job&action=wpjobportaltask&callfrom=1&task=jobenforcedelete&jobid='.$wpjobportal_job->id),'wpjobportal_job_nonce')); ?>" onclick='return confirmdelete("<?php echo esc_js(__('This will delete every thing about this record','wp-job-portal')).'. '.esc_html(__('Are you sure to delete','wp-job-portal')).' ?'; ?>");' title="<?php echo esc_attr(__('force delete', 'wp-job-portal')) ?>">
                    <?php echo esc_html(__('Force Delete', 'wp-job-portal')) ?>
                </a>
                <?php do_action('wpjobportal_addons_admin_feature_for_job',wpjobportal::$_data['config'],$wpjobportal_job,$wpjobportal_featuredflag); ?>
                <?php do_action('wpjobportal_addons_copyjob_credit_for_job',$wpjobportal_job) ?>
                <a class="wpjobportal-jobs-act-btn wpjobportal-jobs-apply-res" href="<?php echo esc_url_raw(admin_url('admin.php?page=wpjobportal_jobapply&wpjobportallt=jobappliedresume&jobid='.$wpjobportal_job->id)); ?>" title="<?php echo esc_attr(__('Applied Resume', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Applied Resume', 'wp-job-portal')) . " (" . esc_html($wpjobportal_job->totalresume) . ")" ?>
                </a>
            </div>
        </div>
        <?php
        break;
 	case 'que-control':

        $wpjobportal_class_color = '';
        $wpjobportal_arr = array();
        if ($wpjobportal_job->status == 0) {
            if ($wpjobportal_class_color == '') {
                ?>
            <?php } ?>
            <?php
            $wpjobportal_class_color = 'q-self';
            $wpjobportal_arr['self'] = 1;
        }
        if ($wpjobportal_job->isfeaturedjob == 0) {
            if ($wpjobportal_class_color == '') {
                ?>
            <?php } ?>
            <?php
            $wpjobportal_class_color = 'q-feature';
            $wpjobportal_arr['feature'] = 1;
        }
        $wpjobportal_total = count($wpjobportal_arr);
        if ($wpjobportal_total == 3) {
            $wpjobportal_objid = 4; //for all
        } elseif ($wpjobportal_total != 1) {
            if (isset($wpjobportal_arr['self']) && isset($wpjobportal_arr['gold'])) {
                $wpjobportal_objid = 1; // for job&gold
            } elseif (isset($wpjobportal_arr['self']) && isset($wpjobportal_arr['feature'])) {
                $wpjobportal_objid = 2; //for job&feature
            } else {
                $wpjobportal_objid = 3; //for gold&feature
            }
        }

        $wpjobportal_html.='<div class="wpjobportal-jobs-action-wrp">';
                    $wpjobportal_total = count($wpjobportal_arr);
                    if ($wpjobportal_total == 3) {
                        $wpjobportal_objid = 4; //for all
                    }
                    if ($wpjobportal_total == 1) {
                        if (isset($wpjobportal_arr['self'])) {
                           
                            $wpjobportal_html.='<a class="wpjobportal-jobs-act-btn" href="admin.php?page=wpjobportal_job&task=approveQueueJob&id='.$wpjobportal_job->id.'&action=wpjobportaltask&_wpnonce='.wp_create_nonce('wpjobportal_job_nonce').'" title='. esc_html(__('approve', 'wp-job-portal')).'>
                                '. esc_html(__('Job Approve', 'wp-job-portal')).'
                            </a>';
                        }
                        if (isset($wpjobportal_arr['feature']) && in_array('featuredjob', wpjobportal::$_active_addons)) {
                           
                            $wpjobportal_html.='<a class="wpjobportal-jobs-act-btn" href="admin.php?page=wpjobportal_job&task=approveQueueFeaturedJob&id='.$wpjobportal_job->id.'&action=wpjobportaltask&_wpnonce='.wp_create_nonce('wpjobportal_job_nonce').'" title='. esc_html(__('approve', 'wp-job-portal')).'>
                                '. esc_html(__('Feature Approve', 'wp-job-portal')).'
                            </a>';
                        }
                    } else {
                        $wpjobportal_html.='
                        <div class="wpjobportal-jobs-act-btn jobsqueue-approvalqueue" onmouseout="hideThis(this);" onmouseover="approveActionPopup('. $wpjobportal_job->id.');">
                            '. esc_html(__('Approve', 'wp-job-portal')).'';
                        $wpjobportal_html.='</div>';
                    } // End approve
                    if ($wpjobportal_total == 1) {
                        if (isset($wpjobportal_arr['self'])) {
                            $wpjobportal_html.='<a class="wpjobportal-jobs-act-btn" href="admin.php?page=wpjobportal_job&task=rejectQueueJob&id='. $wpjobportal_job->id.'&action=wpjobportaltask&_wpnonce='.wp_create_nonce('wpjobportal_job_nonce').'" title='.  esc_html(__('reject', 'wp-job-portal')).'>
                                '.  esc_html(__('Job Reject', 'wp-job-portal')).'
                            </a>';
                        }
                        if (isset($wpjobportal_arr['feature']) && in_array('featuredjob', wpjobportal::$_active_addons)) {
                            $wpjobportal_html.='<a class="wpjobportal-jobs-act-btn" href="admin.php?page=wpjobportal_job&task=rejectQueueFeaturedJob&id='. $wpjobportal_job->id.'&action=wpjobportaltask&_wpnonce='.wp_create_nonce('wpjobportal_job_nonce').'" title='.  esc_html(__('reject', 'wp-job-portal')).'>
                                '.  esc_html(__('Feature Reject', 'wp-job-portal')).'
                            </a>';
                        }
                    } else {
                        $wpjobportal_html.='<div class="wpjobportal-jobs-act-btn jobsqueue-approvalqueue" onmouseout="hideThis(this);" onmouseover="rejectActionPopup('. $wpjobportal_job->id.');">
                                '. esc_html(__('Reject', 'wp-job-portal')).'';
                            $wpjobportal_html.='</div>';
                    }//End Reject 
                    $wpjobportal_html.='
                    <a class="wpjobportal-jobs-act-btn" href='. esc_url_raw(admin_url('admin.php?page=wpjobportal_job&wpjobportallt=formjob&wpjobportalid='.$wpjobportal_job->id)).' title='.  esc_html(__('edit', 'wp-job-portal')).'>
                        '. esc_html(__('Edit', 'wp-job-portal')).'
                    </a>
                    <a class="wpjobportal-jobs-act-btn" href='. wp_nonce_url(admin_url('admin.php?page=wpjobportal_job&task=remove&wpjobportal-cb[]='.$wpjobportal_job->id),'wpjobportal_job_nonce').'&action=wpjobportaltask&callfrom=2 onclick="return confirm(\''. esc_html(__('Are you sure to delete','wp-job-portal')) . ' ?'.'\');" title='.esc_html(__('delete', 'wp-job-portal')).'>
                        '.esc_html(__('Delete', 'wp-job-portal')).'
                    </a>
                    <a class="wpjobportal-jobs-act-btn" href='. wp_nonce_url(admin_url('admin.php?page=wpjobportal_job&task=jobenforcedelete&jobid='.$wpjobportal_job->id),'wpjobportal_job_nonce') .'&action=wpjobportaltask&callfrom=2 onclick="return confirmdelete(\''. esc_html(__('This will delete every thing about this record','wp-job-portal')).'. '.esc_html(__('Are you sure to delete','wp-job-portal')).'?'.'\');" title='. esc_html(__('force delete', 'wp-job-portal')).'>
                        '. esc_html(__('Force Delete', 'wp-job-portal')).'
                    </a>
            </div>  ';
        break;
 }

 echo wp_kses($wpjobportal_html, WPJOBPORTAL_ALLOWED_TAGS);
?>
