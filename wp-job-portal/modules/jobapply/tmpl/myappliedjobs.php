 <?php
if (!defined('ABSPATH'))
    die('Restricted Access');
?>
<div class="wjportal-main-up-wrapper">
<?php
$wpjobportal_labelflag = true;
$wpjobportal_labelinlisting = wpjobportal::$_configuration['labelinlisting'];
if ($wpjobportal_labelinlisting != 1) {
    $wpjobportal_labelflag = false;
} ?>
<div class="wjportal-main-wrapper wjportal-clearfix">
    <div class="wjportal-page-header">
        <div class="wjportal-page-header-cnt">
            <?php
                WPJOBPORTALincluder::getTemplate('templates/pagetitle',array('wpjobportal_module' => 'jobapply','wpjobportal_layout' => 'myapplied'));
            ?>
        </div>
        <div id="my-applied-jobs-wrraper" class="wjportal-header-actions">
            <div class="wjportal-filter-wrp">
                <?php
                    $wpjobportal_image1 = esc_url(WPJOBPORTAL_PLUGIN_URL) . "includes/images/sort-up.png";
                    $wpjobportal_image2 = esc_url(WPJOBPORTAL_PLUGIN_URL) . "includes/images/sort-down.png";
                    if (isset(wpjobportal::$_data['sortby']) && wpjobportal::$_data['sortby'] == 1) {
                        $wpjobportal_image = $wpjobportal_image1;
                    } else {
                        $wpjobportal_image = $wpjobportal_image2;
                    }
                    $wpjobportal_categoryarray = array(
                        (object) array('id' => 1, 'text' => esc_html(__('Job Title', 'wp-job-portal'))),
                        (object) array('id' => 2, 'text' => esc_html(__('Company Name', 'wp-job-portal'))),
                        (object) array('id' => 5, 'text' => esc_html(__('Location', 'wp-job-portal'))),
                        (object) array('id' => 7, 'text' => esc_html(__('Status', 'wp-job-portal'))),
                        (object) array('id' => 4, 'text' => esc_html(__('Job Type', 'wp-job-portal'))),
                        (object) array('id' => 6, 'text' => esc_html(__('Created', 'wp-job-portal'))),
                        (object) array('id' => 8, 'text' => esc_html(__('Salary', 'wp-job-portal')))
                    );
                    // resume filters
                     WPJOBPORTALincluder::getTemplate('jobapply/views/frontend/filter',array(
                        'wpjobportal_sortbylist' => $wpjobportal_categoryarray,
                        'wpjobportal_layout' => 'myjobapplfilter',
                        'wpjobportal_image' => $wpjobportal_image,
                        'wpjobportal_image1' => $wpjobportal_image1,
                        'wpjobportal_image2' => $wpjobportal_image2
                    ));
                ?>
            </div>
        </div>
        <?php if(!WPJOBPORTALincluder::getTemplate('templates/header',array('wpjobportal_module' => 'jobapply'))){
            return;
        } ?>
    </div>
    <?php if (isset(wpjobportal::$_data[0]) && !empty(wpjobportal::$_data[0])) { ?>
            <form id="job_form" method="post" action="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobapply','wpjobportallt'=>'myappliedjobs'))); ?>">
                <div class="wjportal-jobs-list-wrapper wjportal-applied-jobs-wrp">
                    <?php
                        foreach (wpjobportal::$_data[0] AS $wpjobportal_appliedJobs) {
                            WPJOBPORTALincluder::getTemplate('job/views/frontend/joblist',array('wpjobportal_job'=>$wpjobportal_appliedJobs,'wpjobportal_labelflag'=>$wpjobportal_labelflag,'wpjobportal_control'=>'resumetitle'));
                        }
                    ?>
                </div>
                <?php
                    if (wpjobportal::$_data[1]) {
                        if(!WPJOBPORTALincluder::getTemplate('templates/pagination',array('wpjobportal_module' => 'jobapply','pagination' => wpjobportal::$_data[1]))) {
                            return;
                        }
                    }
                    echo wp_kses(WPJOBPORTALformfield::hidden('sortby', wpjobportal::$_data['sortby']),WPJOBPORTAL_ALLOWED_TAGS);
                    echo wp_kses(WPJOBPORTALformfield::hidden('sorton', wpjobportal::$_data['sorton']),WPJOBPORTAL_ALLOWED_TAGS);
                    echo wp_kses(WPJOBPORTALformfield::hidden('WPJOBPORTAL_form_search', 'WPJOBPORTAL_SEARCH'),WPJOBPORTAL_ALLOWED_TAGS);
                    echo wp_kses(WPJOBPORTALformfield::hidden('wpjobportallay', 'appliedjobs'),WPJOBPORTAL_ALLOWED_TAGS);
                ?>
          </form>

          <?php

        } else {
            WPJOBPORTALlayout::getNoRecordFound();
        }
    ?>
</div>

