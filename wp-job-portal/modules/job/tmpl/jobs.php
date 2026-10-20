<?php
if (!defined('ABSPATH')) die('Restricted Access');
?>
<div class="wjportal-main-up-wrapper">
    <?php
    if ( !WPJOBPORTALincluder::getTemplate('templates/header',array('wpjobportal_module' => 'job')) ) {
        return;
    }
    wpjobportal::$_data['fields_titles_array'] = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldOrderingData(2); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- a static property of the wpjobportal class, not a global.
    $wpjobportal_jobs = isset(wpjobportal::$_data[0]) ? wpjobportal::$_data[0] : null;
    $wpjobportal_labelflag = true;
    $wpjobportal_labelinlisting = wpjobportal::$_configuration['labelinlisting'];
    if ($wpjobportal_labelinlisting != 1)
        $wpjobportal_labelflag = false;
    ?>
    <div class="wjportal-main-wrapper wjportal-clearfix">
        <div class="wjportal-page-header">
            <?php
                if(!WPJOBPORTALincluder::getTemplate('templates/pagetitle', array('wpjobportal_module' => 'job', 'wpjobportal_layout' => 'newestjob' ))){
                    return;
                }
            ?>
        </div>
        <div class="wjportal-newest-jobs">
            <?php
            $wpjobportal_job_list_ai_filter = wpjobportal::$_config->getConfigValue('job_list_ai_filter');
            $wpjobportal_ai_extra_cls = "";
            if($wpjobportal_job_list_ai_filter != 0){ // show job filter without ai
                $wpjobportal_ai_extra_cls = "wjportal-filter-aisearch-main-wrp";
            }
            $wpjobportal_show_job_listing_top_filter = wpjobportal::$_config->getConfigValue('show_job_listing_top_filter');
            if($wpjobportal_show_job_listing_top_filter == 1){
                if(empty(wpjobportal::$_data['shortcode_option_hide_filter'])){ ?>
                    <div class="wjportal-filter-search-main-wrp  <?php echo esc_attr($wpjobportal_ai_extra_cls);?>">
                        <form class="wjportal-form-wrp" id="job_form" method="post" action="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'jobs'))); ?>">
                            <?php
                                if($wpjobportal_job_list_ai_filter == 0){ // show job filter without ai
                                    WPJOBPORTALincluder::getTemplate('job/views/frontend/filter',array('wpjobportal_layout' => 'newestjobsfilter'));
                                }else{
                                    WPJOBPORTALincluder::getTemplate('job/views/frontend/filter',array('wpjobportal_layout' => 'aijobfilter'));
                                }
                            ?>
                        </form>
                    </div>
                <?php }
            }

            /*
             * Keep this search.
             *
             * Only offered once a search has actually been run - saving an
             * untouched form would store nothing and replay as nothing - and
             * only to a signed-in job seeker, because a saved search has to
             * belong to somebody. A guest sees nothing rather than a button
             * that sends them to a login screen and loses their search.
             */
            $wpjobportal_show_save_search = wpjobportal::$_config->getConfigValue('search_job_showsave');
            if ($wpjobportal_show_save_search == 1
                && WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()
                && empty(wpjobportal::$_data['savedsearchid'])
                && WPJOBPORTALincluder::getJSModel('jobsearch')->hasSavableJobSearch()) { ?>
                <div class="wjportal-save-search-form-wrp">
                    <form class="wjportal-save-search-form" id="job_form_search" method="post" action="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme' => 'jobsearch', 'task' => 'saveJobSearch', 'action' => 'wpjobportaltask', 'wpjobportalpageid' => wpjobportal::wpjobportal_getPageid()))); ?>">
                        <label class="wjportal-save-search-label" for="searchname"><?php echo esc_html(__('Search Name', 'wp-job-portal')); ?>*</label>
                        <?php
                        echo wp_kses(WPJOBPORTALformfield::text('searchname', '', array('class' => 'inputbox wjportal-save-search-input-field', 'data-validation' => 'required')), WPJOBPORTAL_ALLOWED_TAGS);
                        echo wp_kses(WPJOBPORTALformfield::submitbutton('save', __('Save Search', 'wp-job-portal'), array('class' => 'button wjportal-save-search-btn')), WPJOBPORTAL_ALLOWED_TAGS);
                        echo wp_kses(WPJOBPORTALformfield::hidden('form_request', 'wpjobportal'), WPJOBPORTAL_ALLOWED_TAGS);
                        echo wp_kses(WPJOBPORTALformfield::hidden('_wpnonce', wp_create_nonce('wpjobportal_job_search_nonce')), WPJOBPORTAL_ALLOWED_TAGS);
                        ?>
                    </form>
                </div>
            <?php }

            if(in_array('joblistingenhancer', wpjobportal::$_active_addons)){ // make sure addon exsists
            $wpjobportal_joblisting_ajax_filter_tags = wpjobportal::$_config->getConfigValue('joblisting_ajax_filter_tags');
            $wpjobportal_joblisting_ajax_show_sorting = wpjobportal::$_config->getConfigValue('joblisting_ajax_show_sorting');
                if(!empty($wpjobportal_joblisting_ajax_filter_tags) || $wpjobportal_joblisting_ajax_show_sorting == 1){ // makeing sure some section will be visisble
                    WPJOBPORTALincluder::getTemplate('/frontend/filters', array('module_name'=>'joblistingenhancer'));
                }
            }
            ?>

            <div class="wjportal-jobs-list-wrapper">
                <?php
                    if (!empty($wpjobportal_jobs)) {
                        foreach ($wpjobportal_jobs AS $wpjobportal_job) {
                            WPJOBPORTALincluder::getTemplate('job/views/frontend/joblist', array(
                                'wpjobportal_job' => $wpjobportal_job,
                                'wpjobportal_labelflag' => $wpjobportal_labelflag,
                                'wpjobportal_control' => 'newestjobs'
                            ));
                        }

                        if(empty(wpjobportal::$_data['shortcode_option_no_of_jobs'])){ // if no_of_jobs is set in shortcode dont show pagiantion
                            if (wpjobportal::$_data[1]) {
                                WPJOBPORTALincluder::getTemplate('templates/pagination',array(
                                    'pagination' => wpjobportal::$_data[1],
                                    'wpjobportal_module' => 'job'
                                ));
                            }
                        }
                    } else {
                        $wpjobportal_msg = esc_html(__('No Records Found','wp-job-portal'));
                        WPJOBPORTALlayout::getNoRecordFound($wpjobportal_msg);
                    }
                ?>
            </div>
            <div id="wjportal-popup-background"></div>
            <div id="wjportal-listpopup" class="wjportal-popup-wrp wjportal-job-by-catg-popup">
                <div class="wjportal-popup-cnt">
                    <img id="wjportal-popup-close-btn" alt="<?php echo esc_attr__('Close', 'wp-job-portal'); ?>" src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/popup-close.png">
                    <div class="wjportal-popup-title">
                        <span class="wjportal-popup-title2"></span>
                    </div>
                    <div class="wjportal-popup-contentarea"></div>
                </div>
            </div>
        </div>
    </div>
</div>
