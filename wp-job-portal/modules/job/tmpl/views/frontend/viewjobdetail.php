<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param job      job object - optional
*/

$wpjobportal_extra_featured_class = '';
if(!empty($wpjobportal_job) && !empty($wpjobportal_job->isfeaturedjob) && $wpjobportal_job->isfeaturedjob == 1){
    $wpjobportal_curdate = date_i18n('Y-m-d');
    $wpjobportal_featuredexpiry = date_i18n('Y-m-d', strtotime((string) $wpjobportal_job->endfeatureddate));
    if($wpjobportal_featuredexpiry >= $wpjobportal_curdate){
        $wpjobportal_extra_featured_class = 'wjportal-view-page-featured-flag';
    }
}
?>
<div class="wjportal-main-wrapper wjportal-clearfix">
    <div class="wjportal-view-job-page-wrapper <?php echo esc_attr( $wpjobportal_extra_featured_class );?>" >
        <div class="wjportal-view-job-page-job-info-wraper wjportal-view-job-page-job-info-wraper-with-apply-form " >
            <?php
                WPJOBPORTALincluder::getTemplate('job/views/frontend/jobtitle', array(
                    'wpjobportal_job'       =>  $wpjobportal_job ,
                    'wpjobportal_jobfields'  =>  $wpjobportal_jobfields
                ));
                // 2.9.0: a private skills-gap check for the job seeker (advice, not a hiring decision).
                if (!empty($wpjobportal_job->id) && class_exists('WPJOBPORTAL_AI_Tasks')) {
                    echo WPJOBPORTAL_AI_Tasks::toolbar(array('skills_gap'), array('job' => (int) $wpjobportal_job->id)); // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped parts
                }
            ?>
        </div>
    </div>
</div>
