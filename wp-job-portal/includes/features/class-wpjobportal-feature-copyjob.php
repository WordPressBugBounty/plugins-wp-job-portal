<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Duplicate an existing job.
 *
 * Absorbed from the Copy Job add-on. Adds the copy control to the
 * employer job list and the confirmation prompt behind it.
 *
 * Hook names, routes and output are preserved exactly, and the callbacks are
 * carried over unchanged, so a site that removes the old add-on sees no
 * difference. Registration is skipped entirely while that add-on is still
 * installed; see WPJOBPORTAL_Absorbed_Features.
 */
final class WPJOBPORTAL_Feature_copyjob {

    public static function register() {
        $feature = new self();

        add_action('wpjobportal_addons_copyjob_credit_for_job', array($feature, 'wp_job_portal_addons_input_for_copyjobs'), 10, 1);
        add_filter('wpjobportal_addon_action_credit_copyjob', array($feature, 'wp_job_portal_addon_button_for_copy_job'), 10, 3);
        add_action('wpjobportal_addons_credit_popup_copy_job', array($feature, 'wp_job_portal_addons_input_popup_copy_jobs'), 10, 1);
    }

    /* ------------------------------------------------------------------ */
    /* Callbacks carried over unchanged from the add-on                    */
    /* ------------------------------------------------------------------ */

    public function wp_job_portal_addons_input_for_copyjobs($job){
        WPJOBPORTALincluder::getJSModel('copyjob');
        // copy job action without credits addon should directly be performed
        if(WPJOBPORTAL_Monetisation::usable('credits')){
            $no_package_needed = 0;
            $subType = wpjobportal::$_config->getConfigValue('submission_type');
            if ($subType == 3) {
                $result = WPJOBPORTALincluder::getJSModel('credits')->checkIfPackageDefinedForRole(1); //1 for employer
                if($result == 0){ // 0 means no package found. so allow the action.
                    $no_package_needed = 1;
                }
            }

            if($no_package_needed == 0){
                ?>
                <a href="#" class="wpjobportal-jobs-act-btn" onclick="wpjobportalPopupAdmin('copy_job', '<?php echo esc_js($job->id); ?>', 'job_<?php echo esc_attr($job->id);?>', 'goldjob_<?php echo esc_attr($job->id);?>','<?php echo esc_js($job->uid); ?>');" title="<?php echo esc_attr__('Copy Job','wp-job-portal'); ?>">
                    <?php echo esc_html__('Copy Job','wp-job-portal'); ?>
                </a>
                <?php
            }else{
                $url = (wp_nonce_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'copyjob', 'task'=>'addtocopyJob', 'action'=>'wpjobportaltask', 'wpjobportalid'=>$job->id,'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid())),'copy-job'));
                ?>
                <a href="<?php echo esc_url($url); ?>" class="wpjobportal-jobs-act-btn" title="<?php echo esc_attr__('Copy Job','wp-job-portal'); ?>">
                    <?php echo esc_html__('Copy Job','wp-job-portal'); ?>
                </a>
                <?php
            }
        }else{
            $url = (wp_nonce_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'copyjob', 'task'=>'addtocopyJob', 'action'=>'wpjobportaltask', 'wpjobportalid'=>$job->id,'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid())),'copy-job'));
            ?>
            <a href="<?php echo esc_url($url); ?>" class="wpjobportal-jobs-act-btn" title="<?php echo esc_attr__('Copy Job','wp-job-portal'); ?>">
                <?php echo esc_html__('Copy Job','wp-job-portal'); ?>
            </a>
            <?php
        }
    }

    public function wp_job_portal_addon_button_for_copy_job($id,$actionid){
        //wpjobportalPopupProceedsAdmin
        $result = WPJOBPORTALincluder::getJSModel('copyjob')->copyJob($id, $actionid);
        return $result;
    }

    public function wp_job_portal_addons_input_popup_copy_jobs($myjob) {
        $subType = wpjobportal::$_config->getConfigValue('submission_type');
        if(WPJOBPORTAL_Monetisation::usable('credits')){
            $no_package_needed = 0;
            if ($subType == 3) {

                $result = WPJOBPORTALincluder::getJSModel('credits')->checkIfPackageDefinedForRole(1); //1 for employer
                if($result == 0){ // 0 means no package found. so allow the action.
                    $no_package_needed = 1;
                }

            }

            if($no_package_needed == 0){
                if (wpjobportal::$wpjobportal_theme_chk == 1) {
                    if ($subType == 1 || $subType == 2) { ?>
                        <a class="wpj-jp-icon-btn" href="javascript:void(0);" onclick="wpjobportalPopup('copy_job', '<?php echo esc_js($myjob->id); ?>', <?php echo esc_js(wpjobportal::wpjobportal_getPageid());?>,'job_<?php echo esc_attr($myjob->id); ?>', 'goldjob_<?php echo esc_attr($myjob->id); ?>');" title="<?php echo esc_attr__('Copy Job', 'job-portal-theme'); ?>">
                            <i class="far fa-copy wpj-jp-sm-icon"></i>
                        </a>
                    <?php } elseif ($subType == 3) { ?>
                        <a class="wpj-jp-icon-btn" href="javascript:void(0);" onclick="getPackageCopyPopup(<?php echo esc_js($myjob->id); ?>,1)" title="<?php echo esc_attr__('Copy Job', 'job-portal-theme'); ?>">
                            <i class="far fa-copy wpj-jp-sm-icon"></i>
                        </a>
                   <?php }
                } else {
                      if ($subType == 1 || $subType == 2) { ?>
                         <a class="wjportal-jobs-act-btn wjportal-list-act-btn-copy-job" href="#" onclick="wpjobportalPopup('copy_job', '<?php echo esc_js($myjob->id); ?>', <?php echo esc_js(wpjobportal::wpjobportal_getPageid());?>,'job_<?php echo esc_attr($myjob->id); ?>', 'goldjob_<?php echo esc_attr($myjob->id); ?>');" title="<?php echo esc_attr__('Copy Job', 'wp-job-portal'); ?>">
                             <?php echo esc_html__('Copy Job', 'wp-job-portal'); ?>
                         </a>
                     <?php } elseif ($subType == 3) { ?>
                             <a class="wjportal-jobs-act-btn wjportal-list-act-btn-copy-job" href="#" onclick="getPackageCopyPopup(<?php echo esc_js($myjob->id); ?>)" title="<?php echo esc_attr__('Copy Job', 'wp-job-portal'); ?>"><?php echo esc_html__('Copy Job', 'wp-job-portal'); ?></a>
                    <?php }
               }
           }else{
                // if no package defined for submission type 3 case then allow to perfrom this
                $url = (wp_nonce_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'copyjob', 'task'=>'addtocopyJob', 'action'=>'wpjobportaltask', 'wpjobportalid'=>$myjob->id,'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid())),'copy-job'));
                if (wpjobportal::$wpjobportal_theme_chk == 1) { ?>
                    <a href="<?php echo esc_url($url);?>" class="wpj-jp-icon-btn"  title="<?php echo esc_attr__('Copy Job', 'job-portal-theme'); ?>">
                    <i class="far fa-copy wpj-jp-sm-icon"></i>
                    </a><?php
                } else { ?>
                    <a href="<?php echo esc_url($url);?>" class="wjportal-jobs-act-btn wjportal-list-act-btn-copy-job"  title="<?php echo esc_attr__('Copy Job', 'wp-job-portal'); ?>">
                        <?php echo esc_html__('Copy Job', 'wp-job-portal'); ?>
                    </a>
                    <?php
                }
           }
        }else{ 
            // copy job action without credits addon should directly be performed
            $url = (wp_nonce_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'copyjob', 'task'=>'addtocopyJob', 'action'=>'wpjobportaltask', 'wpjobportalid'=>$myjob->id,'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid())),'copy-job'));
            if (wpjobportal::$wpjobportal_theme_chk == 1) { ?>
                 <a href="<?php echo esc_url($url);?>" class="wpj-jp-icon-btn"  title="<?php echo esc_attr__('Copy Job', 'job-portal-theme'); ?>">
                    <i class="far fa-copy wpj-jp-sm-icon"></i>
                 </a>
       <?php } else { ?>
            <a href="<?php echo esc_url($url);?>" class="wjportal-jobs-act-btn wjportal-list-act-btn-copy-job"  title="<?php echo esc_attr__('Copy Job', 'wp-job-portal'); ?>">
               <?php echo esc_html__('Copy Job', 'wp-job-portal'); ?>
            </a>
        <?php
            }
        }

    }

}
