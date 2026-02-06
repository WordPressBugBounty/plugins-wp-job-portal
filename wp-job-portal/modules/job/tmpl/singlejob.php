<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>

<div id="wp-job-portal-wrapper">
    <div class="js-toprow">
        <div class="js-image">
            <a href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$wpjobportal_job->companyaliasid))); ?>">
                <img src="<?php echo esc_url(WPJOBPORTALincluder::getJSModel('company')->getLogoUrl($wpjobportal_job->companyid,$wpjobportal_job->logofilename)); ?>">
            </a>
        </div>
        <div class="js-data">
            <div class="left">
                <?php if(wpjobportal::$_config->getConfigValue('comp_name') == 1){ ?>
                <div class="js-col-xs-12 js-col-sm-12 js-col-md-12 js-midrow">
                    <a class="js-companyname" href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$wpjobportal_job->companyaliasid))); ?>"><?php echo esc_html($wpjobportal_job->companyname); ?></a>
                </div>
                <?php } ?>
                <div class="js-first-row">
                    <span class="js-col-xs-12 js-col-sm-8 js-col-md-8 js-title joblist-jobtitle">
                        <a href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'viewjob', 'wpjobportalid'=>$wpjobportal_job->jobaliasid))); ?>"><?php echo esc_html($wpjobportal_job->title); ?></a>
                    </span>

                </div>
                <div class="js-dash-fields">
                <?php if($wpjobportal_jobfields['jobcategory']->showonlisting == 1){ ?>
                    <span class="get-text"><?php echo  esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_job->cat_title)); ?></span>
                <?php } ?>
                <?php if($wpjobportal_jobfields['city']->showonlisting == 1){ ?>
                    <span class="get-text"><?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_job->location)); ?></span>
                <?php } ?>
                </div>
            </div>
            <div class="right">
                <div class="js-col-xs-12 js-col-sm-12 js-col-md-12 js-fields for-rtl joblist-datafields">
                    <span class="js-type" style="color:#fff;padding:3px;background:<?php echo esc_attr($wpjobportal_job->jobtypecolor); ?>;"><?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_job->jobtypetitle)); ?></span>
                </div>
                <?php if($wpjobportal_jobfields['jobsalaryrange']->showonlisting == 1){ ?>
                <div class="js-col-xs-12 js-col-sm-12 js-col-md-12 js-fields for-rtl joblist-datafields">
                    <span class="get-text"><b><?php echo esc_html($wpjobportal_job->salary); ?></b></span>
                </div>
                <?php } ?>
                <div class="js-col-xs-12 js-col-sm-12 js-col-md-12 js-fields for-rtl joblist-datafields">
                    <?php echo esc_html(human_time_diff(strtotime($wpjobportal_job->created),strtotime(date_i18n("Y-m-d H:i:s")) )).' '.esc_html(__("Ago",'wp-job-portal')); ?>
                </div>
            </div>


            <div class="js-second-row">


                <?php
                // custom fields
                /*foreach ($wpjobportal_customfields as $wpjobportal_field) {
                    echo WPJOBPORTALincluder::getObjectClass('customfields')->showCustomFields($wpjobportal_field,JOB,$wpjobportal_job->params);
                }*/
                //end
                ?>
            </div>
        </div>
    </div>
</div>
