<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
?>
<div class="wjportal-main-up-wrapper">
<?php
if ( !WPJOBPORTALincluder::getTemplate('templates/header',array('wpjobportal_module' => 'jobapply'))) {
    return;
}
if (wpjobportal::$_error_flag == null) {
    $wpjobportal_ids=WPJOBPORTALrequest::getVar('jobid');
    $wpjobportal_id=isset($wpjobportal_ids) ? $wpjobportal_ids : null;
    ?>
    <div class="wjportal-main-wrapper wjportal-clearfix">
        <?php do_action('wpjobportal_addons_sendmessage_popup_main_outer'); ?>
        <?php do_action('wpjobportal_addons_coverletter_popup_main_outer'); ?>
        <div class="wjportal-page-header">
            <div class="wjportal-page-header-cnt">
                <?php
                    if(!WPJOBPORTALincluder::getTemplate('templates/pagetitle',array('wpjobportal_module' => 'jobapply','wpjobportal_layout' =>'appliedres'))){
                        return;
                    }
                ?>
            </div>
            <div class="wjportal-header-actions">
                <?php
                $wpjobportal_image1 = esc_url(WPJOBPORTAL_PLUGIN_URL) . "includes/images/sort-up.png";
                $wpjobportal_image2 = esc_url(WPJOBPORTAL_PLUGIN_URL) . "includes/images/sort-down.png";
                if (wpjobportal::$_data['sortby'] == 1) {
                    $wpjobportal_image = $wpjobportal_image1;
                } else {
                    $wpjobportal_image = $wpjobportal_image2;
                }
                $wpjobportal_categoryarray = array(
                    (object) array('id' => 1, 'text' => esc_html(__('Application title', 'wp-job-portal'))),
                    (object) array('id' => 2, 'text' => esc_html(__('First name', 'wp-job-portal'))),
                    (object) array('id' => 3, 'text' => esc_html(__('Category', 'wp-job-portal'))),
                    (object) array('id' => 4, 'text' => esc_html(__('Job type', 'wp-job-portal'))),
                    (object) array('id' => 5, 'text' => esc_html(__('Location', 'wp-job-portal'))),
                    (object) array('id' => 6, 'text' => esc_html(__('Created', 'wp-job-portal')))
                );
            // resume filters
                WPJOBPORTALincluder::getTemplate('jobapply/views/frontend/filter',array(
                    'wpjobportal_sortbylist' => $wpjobportal_categoryarray,
                    'wpjobportal_layout' => 'sortby',
                    'wpjobportal_image' => $wpjobportal_image,
                    'wpjobportal_image1' => $wpjobportal_image1,
                    'wpjobportal_image2' => $wpjobportal_image2
                ));
            ?>

            </div>
        </div>
        <form class="wjportal-form" id="job_form" method="post" action="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobapply','wpjobportallt'=>'jobappliedresume', 'jobid'=>$wpjobportal_id))); ?>">
            <div id="job-applied-resume-wrapper" class="wjportal-job-applied-resume">
                <div class="wjportal-section-heading">
                    <?php echo esc_html(__('Job Info','wp-job-portal')); ?>
                </div>
                <?php
                   if (isset(wpjobportal::$_data[4]['jobinfo']) && !empty(wpjobportal::$_data[4]['jobinfo'])) {
                        WPJOBPORTALincluder::getTemplate('job/views/frontend/joblist',array('wpjobportal_job'=>wpjobportal::$_data[4]['jobinfo'][0],'wpjobportal_labelflag' => true,'wpjobportal_control'=>''));
                    }
                ?>
                <?php
                    //Resume Action's For Addons
                    $wpjobportal_tab = WPJOBPORTALrequest::getVar('ta',"","1");
                    $wpjobportal_ta = WPJOBPORTALrequest::getVar('ta', null, 1);
                ?>
                <div class="wjportal-job-applied-resume-actions">
                    <ul>
                        <?php //ADDONS FOR MESSAGE
                            do_action('wpjobportal_addons_resume_top_buttons_actions',wpjobportal::$_data[0],$wpjobportal_ta,$wpjobportal_tab);
                             do_action('wpjobportal_addons_resume_top_buttons_actions_export',wpjobportal::$_data['jobid']);
                        ?>
                    </ul>
                </div>
                <?php do_action('wpjobportal_addons_top_btn_action_popup'); ?>
                <div class="wjportal-job-applied-resume-list">
                    <div class="wjportal-section-heading">
                        <?php echo esc_html(__('Resume Applied On Job','wp-job-portal')); ?>
                    </div>
                    <?php
                        if (isset(wpjobportal::$_data[0]['data']) && !empty(wpjobportal::$_data[0]['data'])) {
                            foreach (wpjobportal::$_data[0]['data'] AS $wpjobportal_resume) {

                                WPJOBPORTALincluder::getTemplate('resume/views/frontend/resumelist',array(
                                    'wpjobportal_myresume' => $wpjobportal_resume,
                                    'wpjobportal_module' => 'jobappliedresume',
                                    'wpjobportal_control' => 'jobapply',
                                    'wpjobportal_percentage' => ''
                                ));
                            }
                            if (wpjobportal::$_data[1]) {
                                WPJOBPORTALincluder::getTemplate('templates/pagination',array(
                                    'wpjobportal_pagination' => wpjobportal::$_data[1],
                                    'wpjobportal_module' => 'jobapply'
                                ));
                            }
                        echo wp_kses(WPJOBPORTALformfield::hidden('sortby', wpjobportal::$_data['sortby']),WPJOBPORTAL_ALLOWED_TAGS);
                        echo wp_kses(WPJOBPORTALformfield::hidden('sorton', wpjobportal::$_data['sorton']),WPJOBPORTAL_ALLOWED_TAGS);
                        } else {
                            WPJOBPORTALlayout::getNoRecordFound();
                        }
                    ?>
                </div>
            </div>
        </form>
    </div>

<?php
} else {
    echo wp_kses_post(wpjobportal::$_error_flag_message);
}
?>
</div>
