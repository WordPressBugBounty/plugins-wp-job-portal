<?php
/**
 * The job searches this job seeker has kept.
 *
 * "Run search" replays a saved search by id rather than by name: the name is
 * the visitor's label and they may reuse it, so it is not an identifier.
 */

if (!defined('ABSPATH'))
    die('Restricted Access');
?>
<div class="wjportal-main-up-wrapper">
    <div class="wjportal-main-wrapper wjportal-clearfix">
        <div class="wjportal-page-header">
            <?php
            WPJOBPORTALincluder::getTemplate('templates/pagetitle', array('wpjobportal_module' => 'jobsearch', 'wpjobportal_layout' => 'jobsavesearch'));
            if (!WPJOBPORTALincluder::getTemplate('templates/header', array('wpjobportal_module' => 'jobsearch'))) {
                return;
            }
            ?>
        </div>
        <div class="wjportal-save-search-list-wrapper">
            <?php
            if (!empty(wpjobportal::$_data[0])) {
                $wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
                foreach (wpjobportal::$_data[0] AS $wpjobportal_savedsearch) {
                    ?>
                    <div class="wjportal-save-search-list">
                        <div class="wjportal-save-search-title">
                            <?php echo esc_html($wpjobportal_savedsearch->searchname); ?>
                        </div>
                        <div class="wjportal-save-search-created">
                            <span class="wjportal-save-search-created-text">
                                <?php echo esc_html__('Saved', 'wp-job-portal') . ': ' . esc_html(date_i18n($wpjobportal_dateformat, strtotime($wpjobportal_savedsearch->created))); ?>
                            </span>
                        </div>
                        <div class="wjportal-save-search-action-wrp">
                            <a class="wjportal-save-search-act-btn-remove wjportal-list-act-btn-delete" href="<?php echo esc_url(wp_nonce_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme' => 'jobsearch', 'task' => 'removeSavedJobSearch', 'action' => 'wpjobportaltask', 'wpjobportalid' => $wpjobportal_savedsearch->id, 'wpjobportalpageid' => wpjobportal::wpjobportal_getPageid())), 'wpjobportal_job_search_nonce')); ?>" onclick="return confirmdelete('<?php echo esc_html__('Are you sure to delete', 'wp-job-portal') . ' ?'; ?>');">
                                <?php echo esc_html__('Delete', 'wp-job-portal'); ?>
                            </a>
                            <?php
                            /*
                             * wpjobportal_makeUrl() builds the pretty path from a
                             * fixed set of arguments and quietly drops the rest, so
                             * these two are appended afterwards.
                             *
                             * wpjobportallay is what tells the search pipeline this
                             * request is a job listing. The search form posts it for
                             * the same reason; the pretty path alone is not enough,
                             * because the layout is only resolved from it later, on
                             * parse_request, by which time the pipeline has run.
                             */
                            $wpjobportal_run_url = add_query_arg(
                                array(
                                    'savedsearch' => (int) $wpjobportal_savedsearch->id,
                                    'wpjobportallay' => 'jobs',
                                ),
                                wpjobportal::wpjobportal_makeUrl(array('wpjobportalme' => 'job', 'wpjobportallt' => 'jobs', 'wpjobportalpageid' => wpjobportal::wpjobportal_getPageid()))
                            );
                            ?>
                            <a class="wjportal-save-search-act-btn wjportal-list-act-btn-view" href="<?php echo esc_url($wpjobportal_run_url); ?>">
                                <?php echo esc_html__('Run Search', 'wp-job-portal'); ?>
                            </a>
                        </div>
                    </div>
                    <?php
                }
                if (wpjobportal::$_data[1]) {
                    WPJOBPORTALincluder::getTemplate('templates/pagination', array('wpjobportal_module' => 'jobsearch', 'pagination' => wpjobportal::$_data[1]));
                }
            } else {
                // Say what the page is for, rather than only that it is empty:
                // somebody arriving from the menu has not seen the button yet.
                $wpjobportal_msg = __('You have not saved any searches yet. Run a job search, then use Save Search to keep it here.', 'wp-job-portal');
                WPJOBPORTALlayout::getNoRecordFound($wpjobportal_msg);
            }
            ?>
        </div>
    </div>
</div>
