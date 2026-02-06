<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param job      job object - optional
 * IF PREVIOUS ID SAME AS COMPARE TO PREVIOUS THAN SHOW SAME ELSE VARIATE
*/?>
<?php
if((WPJOBPORTALincluder::getObjectClass('user')->isemployer()) && count(wpjobportal::$_data['employer_info']['jobid'])>0) {
    ?>
    <div  class="wjportal-resume-list-wrp">
        <?php
            $wpjobportal_jobtype = wpjobportal::$_data['employer_info']['jobid'];
            foreach ($wpjobportal_jobtype as $wpjobportal_key=>$wpjobportal_value) { ?>
                <div class="wjportal-resume-app-title" id="jobid<?php $wpjobportal_value->jobid?>">
                    <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_value->title)); ?>
                </div>
                <?php
                foreach (wpjobportal::$_data[0]['data'][$wpjobportal_value->jobid] AS $wpjobportal_resume) {
                    //Job Wise LOOP Resume's
                    WPJOBPORTALincluder::getTemplate('resume/views/frontend/resumelist',array(
                        'wpjobportal_myresume' => $wpjobportal_resume,
                        'wpjobportal_module' => 'dashboard',
                        'wpjobportal_control' => 'resumedashboard',
                        'wpjobportal_percentage' => ''
                    ));
                }
            }
        ?>
    </div>
        <?php
} else {
    $wpjobportal_msg = esc_html(__('No record found','wp-job-portal'));
    WPJOBPORTALlayout::getNoRecordFound($wpjobportal_msg, '');
  }
?>

