<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param job      job object - optional
 */
?>
<div class="right">
    <div class="js-col-xs-12 js-col-sm-12 js-col-md-12 js-fields for-rtl joblist-datafields">
        <span class="js-type" style="background: <?php echo esc_attr($wpjobportal_job->jobtypecolor); ?>"><?php echo esc_html($wpjobportal_job->jobtypetitle); ?></span>
    </div>
    <div class="js-col-xs-12 js-col-sm-12 js-col-md-12 js-fields for-rtl joblist-datafields">
        <span class="get-text"><b><?php echo esc_html(wpjobportal::$_common->getSalaryRangeView( $wpjobportal_job->salarytype, $wpjobportal_job->salarymin, $wpjobportal_job->salarymax, $wpjobportal_job->srangetypetitle)); ?></b></span>
    </div>
    <div class="js-col-xs-12 js-col-sm-12 js-col-md-12 js-fields for-rtl joblist-datafields" title="<?php echo esc_attr(date_i18n('d F Y h:i A',strtotime($wpjobportal_job->created))); ?>">
        <?php echo esc_html(human_time_diff(strtotime($wpjobportal_job->created) ,strtotime(date_i18n("Y-m-d H:i:s"))).' '.esc_html(__("ago",'wp-job-portal'))); ?>
    </div>
</div>
