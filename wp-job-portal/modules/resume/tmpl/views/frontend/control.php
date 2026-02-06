<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param WP JOB PORTAL 
 * @param Control 
 */
$wpjobportal_html = '';

switch ($wpjobportal_control) {
	case 'myresumes':
        if ($wpjobportal_myresume->status == 1 || $wpjobportal_myresume->status == 3) {
            $wpjobportal_config_array_res = wpjobportal::$_data['config'];
            if(in_array('multiresume', wpjobportal::$_active_addons)){
                $wpjobportal_mod = "multiresume";
            }else{
                $wpjobportal_mod = "resume";
            }
            ?>
            <div class="wjportal-resume-list-btm-wrp">
                <div class="wjportal-resume-action-wrp">
                    <a class="wjportal-resume-act-btn wjportal-list-act-btn-edit" href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>$wpjobportal_mod, 'wpjobportallt'=>'addresume', 'wpjobportalid'=>$wpjobportal_myresume->id, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()))); ?>">
                        <?php echo esc_html(__('Edit Resume', 'wp-job-portal')); ?>
                    </a>
                    <?php if ($wpjobportal_myresume->status != 3){ ?>
                            <?php if(in_array('multiresume', wpjobportal::$_active_addons)){ ?>
                                <a class="wjportal-resume-act-btn wjportal-list-act-btn-view" href="<?php echo esc_url( wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'viewresume', 'wpjobportalid'=>$wpjobportal_myresume->id, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()))); ?>">
                            <?php echo esc_html(__('View Resume', 'wp-job-portal')); ?>
                        </a>
                        <?php    } 
                    }
                    if ($wpjobportal_config_array_res['system_have_featured_resume'] == 1 && $wpjobportal_featuredflag == true && $wpjobportal_myresume->status !=3) {
                        do_action('wpjobportal_addons_feature_multiresume',$wpjobportal_myresume);
                     } 
                    if($wpjobportal_myresume->status == 3){
                        do_action('wpjobportal_addons_makePayment_for_department',$wpjobportal_myresume,"payresume");
                    } ?>

                    <a class="wjportal-resume-act-btn wjportal-list-act-btn-delete" href="<?php echo esc_url(wp_nonce_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'task'=>'removeresume', 'action'=>'wpjobportaltask', 'wpjobportal-cb[]'=>$wpjobportal_myresume->id, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid())),'wpjobportal_resume_nonce')); ?>"onclick='return confirmdelete("<?php echo esc_js(__('Are you sure to delete','wp-job-portal')).' ?'; ?>");'>
                        <?php echo esc_html(__('Delete Resume', 'wp-job-portal')); ?>
                    </a>
                    <?php
                    $wpjobportal_show_suggested_jobs_button = wpjobportal::$_config->getConfigValue('show_suggested_jobs_button');
                    if(in_array('aisuggestedjobs', wpjobportal::$_active_addons)){
                        if($wpjobportal_show_suggested_jobs_button == 1){ // show button for suggested jobs
                        ?>
                            <a class="wjportal-resume-act-btn wjportal-resume-act-btn-ai-suggested-jobs" href="<?php echo esc_url(wp_nonce_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'jobs', 'aisuggestedjobs_resume'=> $wpjobportal_myresume->resumealiasid, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid())),'wpjobportal_resume_nonce')); ?>">
                                <?php echo esc_html(__('Suggested Jobs', 'wp-job-portal')); ?>
                            </a>
                        <?php
                        }
                    }  ?>

                </div>
            </div>
        <?php } elseif ($wpjobportal_myresume->status == 0) { ?>
            <div class="wjportal-resume-list-btm-wrp">
                <span class="wjportal-item-act-status wjportal-waiting">
                    <?php echo esc_html(__('Waiting For Approval', 'wp-job-portal')); ?>
                </span>
            </div>
        <?php } elseif ($wpjobportal_myresume->status == -1){ ?>
            <div class="wjportal-resume-list-btm-wrp">
                <span class="wjportal-item-act-status wjportal-rejected">
                    <?php echo esc_html(__('Rejected', 'wp-job-portal')); ?>
                </span>
            </div>
          <?php
              } 
         break;
     case 'folderresume':
            do_action('wpjobportal_addons_folderresume_control',$wpjobportal_myresume);
         break;
     case 'jobapply': ?>
         <div class="wjportal-resume-list-btm-wrp">
            <div class="wjportal-resume-action-wrp">
                        <?php
                        // the below code is written in this way to accomodate the design
                            echo '
                            <a class="wjportal-resume-act-btn wjportal-list-act-btn-view-profile" href='. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'viewresume', 'jobid'=>$wpjobportal_myresume->id, 'wpjobportalid'=>$wpjobportal_myresume->resumealiasid, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()))).' title='. esc_html(__('view profile', 'wp-job-portal')) .'>
                                '. esc_html(__('View Profile', 'wp-job-portal')) .'
                            </a>';
                            if ( has_action( 'wpjobportal_addons_resume_bottom_action_appliedresume' ) ) {
                                ?>
                                <div class="wjportal-resume-act-actions-dropdown">
                                    <button class="wjportal-resume-act-actions-dropdown-toggle" onclick="return false;">
                                        <?php echo esc_html__('More Actions', 'wp-job-portal'); ?> <span>&#9662;</span>
                                    </button>

                                    <div class="wjportal-resume-act-actions-dropdown-menu">
                                        <?php
                                            $wpjobportal_class = 'action-links';
                                            do_action('wpjobportal_addons_resume_bottom_action_appliedresume',$wpjobportal_myresume,$wpjobportal_class);

                            }else{
                            // if resumeaction addon is missing then these two div will remain open closing them to handle that case
                            if(!in_array('resumeaction',wpjobportal::$_active_addons)){  ?>
                                    </div>
                                </div>
                            <?php   }  ?>
                            <?php   }  ?>


            </div>
        </div>
        <?php
        break;
    case 'payresume':
    case 'payfeaturedresume': ?>
            <div class="wjportal-resume-list-btm-wrp">
                <div class="wjportal-resume-action-wrp"> <?php
                    do_action('wpjobportal_addons_proceedPayment_PerListing',$wpjobportal_myresume->resumealiasid,'resume','myresumes'); ?>
                </div>
            </div> <?php
        break;
    case 'resumelisting':
    case 'resumedashboard':
     ?>
        <div class="wjportal-resume-list-btm-wrp">
            <div class="wjportal-resume-action-wrp">
                <a class="wjportal-resume-act-btn-view wjportal-list-act-btn-view" href="<?php echo esc_url( wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'viewresume', 'wpjobportalid'=>$wpjobportal_myresume->id, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()))); ?>">
                    <?php echo esc_html(__('View Resume', 'wp-job-portal')); ?>
                </a>
            </div>
        </div>
            <?php
        break;


}
?>
