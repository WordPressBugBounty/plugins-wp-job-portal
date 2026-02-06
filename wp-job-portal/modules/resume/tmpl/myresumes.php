<?php
if (!defined('ABSPATH'))
die('Restricted Access');
?>
<div class="wjportal-main-up-wrapper">
<?php
wp_enqueue_style('status-graph', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/status_graph.css');
$wpjobportal_subtype = wpjobportal::$_config->getConfigValue('submission_type');
?>
<div class="wjportal-main-wrapper wjportal-clearfix">
        <div class="wjportal-page-header">
            <div class="wjportal-page-header-cnt">
                <?php WPJOBPORTALincluder::getTemplate('templates/pagetitle',array('wpjobportal_module' => 'resume','wpjobportal_layout' => 'multiresumeadd'));
                ?>
            </div>
            <?php if (wpjobportal::$_error_flag == null) { ?>
            <div class="wjportal-header-actions">
                <div class="wjportal-filter-wrp">
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
                            (object) array('id' => 6, 'text' => esc_html(__('Created', 'wp-job-portal'))),
                            (object) array('id' => 7, 'text' => esc_html(__('Status', 'wp-job-portal')))
                        );
                        // resume filters
                        WPJOBPORTALincluder::getTemplate('resume/views/frontend/filter',array(
                            'wpjobportal_sortbylist' => $wpjobportal_categoryarray,
                            'wpjobportal_filter' => 'resume',
                            'wpjobportal_image' => $wpjobportal_image,
                            'wpjobportal_image1' => $wpjobportal_image1,
                            'wpjobportal_image2' => $wpjobportal_image2
                        ));
                    ?>
                </div>
                <div class="wjportal-act-btn-wrp">
                    <?php  do_action('wpjobportal_addon_resume_action_addResume'); ?>
                </div>
            </div>
        <?php } ?>
        <?php
        if(!WPJOBPORTALincluder::getTemplate('templates/header',array('wpjobportal_module'=>'resume'))){
                    return;
        } ?>
        </div>
        <div class="wjportal-resume-list-wrp wjportal-my-resume-wrp">
            <?php
            if(!empty(wpjobportal::$_data[0])){ ?>
                <form id="resume_form" method="post" action="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume','wpjobportallt'=>'myresumes','wpjobportalpageid' =>wpjobportal::wpjobportal_getPageid()))); ?>">
                <?php
                    $wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
                    foreach (wpjobportal::$_data[0] AS $wpjobportal_myresume) {

                        // to show resume as 100% when advanced resume builder is not installed.
                         if(!in_array('advanceresumebuilder', wpjobportal::$_active_addons)){
                            $wpjobportal_status_array = array();
                            $wpjobportal_status_array['percentage'] = 100;
                            $wpjobportal_percentage = 100;
                         }else{
                            $wpjobportal_status_array = WPJOBPORTALincluder::getJSModel('resume')->getResumePercentage($wpjobportal_myresume->id);
                            $wpjobportal_percentage = $wpjobportal_status_array['percentage'];
                         }
                            WPJOBPORTALincluder::getTemplate('resume/views/frontend/resumelist',array(
                            'wpjobportal_myresume' => $wpjobportal_myresume,
                            'wpjobportal_percentage' => $wpjobportal_status_array['percentage'],
                            'wpjobportal_dateformat' => $wpjobportal_dateformat,
                            'wpjobportal_control' => 'myresumes',
                            'wpjobportal_module' => 'myresumes'
                         ));

                    }
                    if (wpjobportal::$_data[1]) {
                        WPJOBPORTALincluder::getTemplate('templates/pagination',array('wpjobportal_module' => 'resume','pagination' => wpjobportal::$_data[1]));
                    }
                    echo wp_kses(WPJOBPORTALformfield::hidden('sortby', wpjobportal::$_data['sortby']),WPJOBPORTAL_ALLOWED_TAGS);
                    echo wp_kses(WPJOBPORTALformfield::hidden('sorton', wpjobportal::$_data['sorton']),WPJOBPORTAL_ALLOWED_TAGS);
                    echo wp_kses(WPJOBPORTALformfield::hidden('WPJOBPORTAL_form_search', 'WPJOBPORTAL_SEARCH'),WPJOBPORTAL_ALLOWED_TAGS);
                    echo wp_kses(WPJOBPORTALformfield::hidden('wpjobportallay', 'myresume'),WPJOBPORTAL_ALLOWED_TAGS);
                    ?>
                </form>
            <?php
            } else {
                $wpjobportal_msg = esc_html(__('No record found','wp-job-portal'));
                if(in_array('multiresume', wpjobportal::$_active_addons)){
                    $wpjobportal_mod = "multiresume";
                }else{
                    $wpjobportal_mod = "resume";
                }
                $wpjobportal_links[] = array(
                        'link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>$wpjobportal_mod, 'wpjobportallt'=>'addresume', 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid())),
                        'text' => esc_html(__('Add New','wp-job-portal')) .' '. esc_html(__('Resume', 'wp-job-portal'))
                    );
                WPJOBPORTALlayout::getNoRecordFound($wpjobportal_msg,$wpjobportal_links);
            }?>
        </div>

</div>
</div>
