<?php
    if (!defined('ABSPATH'))
        die('Restricted Access');
    wp_enqueue_script('wpjobportal-res-tables', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/responsivetable.js');
?>
<!-- main wrapper -->
<div id="wpjobportaladmin-wrapper">
    <!-- left menu -->
    <div id="wpjobportaladmin-leftmenu">
        <?php  WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <?php
            $msgkey = WPJOBPORTALincluder::getJSModel('wpjobportal')->getMessagekey();
            WPJOBPORTALMessages::getLayoutMessage($msgkey);
        ?>
        <!-- top bar -->
        <div id="wpjobportal-wrapper-top">
            <div id="wpjobportal-wrapper-top-left">
                <div id="wpjobportal-breadcrumbs">
                    <ul>
                        <li>
                            <a href="<?php echo esc_url_raw(admin_url('admin.php?page=wpjobportal')); ?>" title="<?php echo esc_html(__('dashboard','wp-job-portal')); ?>">
                                <?php echo esc_html(__('Dashboard','wp-job-portal')); ?>
                            </a>
                        </li>
                        <li><?php echo esc_html(__('Short Codes','wp-job-portal')); ?></li>
                    </ul>
                </div>
            </div>
            <div id="wpjobportal-wrapper-top-right">
                <div id="wpjobportal-config-btn">
                    <a href="admin.php?page=wpjobportal_configuration" title="<?php echo esc_html(__('configuration','wp-job-portal')); ?>">
                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/config.png">
                   </a>
                </div>
                <div id="wpjobportal-help-btn" class="wpjobportal-help-btn">
                    <a href="admin.php?page=wpjobportal&wpjobportallt=help" title="<?php echo esc_html(__('help','wp-job-portal')); ?>">
                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/help.png">
                   </a>
                </div>
                <div id="wpjobportal-vers-txt">
                    <?php echo esc_html(__('Version','wp-job-portal')).': '; ?>
                    <span class="wpjobportal-ver"><?php echo esc_html(WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue('versioncode')); ?></span>
                </div>
            </div>
        </div>
        <!-- top head -->
        <?php WPJOBPORTALincluder::getTemplate('templates/admin/pagetitle',array('module' => 'wpjobportal' , 'layouts' => 'shortcodes')); ?>
        <!-- page content -->
        <div id="wpjobportal-admin-wrapper" class="p0">
            <div id="wpjobportal-shortcode-wrapper">
                <div class="wpjobportal-shortcode-1">
                    <?php echo esc_html(__('Job seeker control panel','wp-job-portal')); ?>
                </div>
                <div class="wpjobportal-shortcode-2">
                    <?php echo '[wpjobportal_jobseeker_controlpanel]'; ?>
                </div>
                <div class="wpjobportal-shortcode-3">
                    <?php echo esc_html(__('Show','wp-job-portal')).' '. esc_html(__('job seeker control panel','wp-job-portal')); ?>
                </div>
            </div>
            <div id="wpjobportal-shortcode-wrapper">
                <div class="wpjobportal-shortcode-1">
                    <?php echo esc_html(__('Employer control panel','wp-job-portal')); ?>
                </div>
                <div class="wpjobportal-shortcode-2">
                    <?php echo '[wpjobportal_employer_controlpanel]'; ?>
                </div>
                <div class="wpjobportal-shortcode-3">
                    <?php echo esc_html(__('Show','wp-job-portal')).' '. esc_html(__('employer control panel','wp-job-portal')); ?>
                </div>
            </div>
            <div id="wpjobportal-shortcode-wrapper">
                <div class="wpjobportal-shortcode-1">
                    <?php echo esc_html(__('Job','wp-job-portal')).' '. esc_html(__('search','wp-job-portal')); ?>
                </div>
                <div class="wpjobportal-shortcode-2">
                    <?php echo '[wpjobportal_job_search]'; ?>
                </div>
                <div class="wpjobportal-shortcode-3">
                    <?php echo esc_html(__('Show','wp-job-portal')).' '. esc_html(__('job','wp-job-portal')).' '. esc_html(__('search','wp-job-portal')).' '. esc_html(__('form','wp-job-portal')); ?>
                </div>
            </div>
            <div id="wpjobportal-shortcode-wrapper">
                <div class="wpjobportal-shortcode-1">
                    <?php echo esc_html(__('Jobs','wp-job-portal')); ?>
                </div>
                <div class="wpjobportal-shortcode-2">
                    <?php echo '[wpjobportal_job]'; ?>
                </div>
                <div class="wpjobportal-shortcode-3">
                    <?php echo esc_html(__('Show','wp-job-portal')).' '. esc_html(__('jobs','wp-job-portal')); ?>
                </div>
            </div>
            <div id="wpjobportal-shortcode-wrapper">
                <div class="wpjobportal-shortcode-1">
                    <?php echo esc_html(__('Featured Jobs','wp-job-portal')); ?>
                </div>
                <div class="wpjobportal-shortcode-2">
                    <?php echo '[wpjobportal_featured_jobs]'; ?>
                </div>
                <div class="wpjobportal-shortcode-3">
                    <?php echo esc_html(__('Show featured jobs','wp-job-portal')); ?>&nbsp;(<?php echo esc_html(__('Requires WP Job Portal Featured Jobs Addon','wp-job-portal')); ?>)
                </div>
            </div>

            <div id="wpjobportal-shortcode-wrapper">
                <div class="wpjobportal-shortcode-1">
                    <?php echo esc_html(__('Job categories','wp-job-portal')); ?>
                </div>
                <div class="wpjobportal-shortcode-2">
                    <?php echo '[wpjobportal_job_categories]'; ?>
                </div>
                <div class="wpjobportal-shortcode-3">
                    <?php echo esc_html(__('Show','wp-job-portal')).' '. esc_html(__('job categories','wp-job-portal')); ?>
                </div>
            </div>
            <div id="wpjobportal-shortcode-wrapper">
                <div class="wpjobportal-shortcode-1">
                    <?php echo esc_html(__('Job types','wp-job-portal')); ?>
                </div>
                <div class="wpjobportal-shortcode-2">
                    <?php echo '[wpjobportal_job_types]'; ?>
                </div>
                <div class="wpjobportal-shortcode-3">
                    <?php echo esc_html(__('Show','wp-job-portal')).' '. esc_html(__('job types','wp-job-portal')); ?>
                </div>
            </div>
            <div id="wpjobportal-shortcode-wrapper">
                <div class="wpjobportal-shortcode-1">
                    <?php echo esc_html(__('My applied jobs','wp-job-portal')); ?>
                </div>
                <div class="wpjobportal-shortcode-2">
                    <?php echo '[wpjobportal_my_appliedjobs]'; ?>
                </div>
                <div class="wpjobportal-shortcode-3">
                    <?php echo esc_html(__('Show','wp-job-portal')).' '. esc_html(__('job seeker','wp-job-portal')).' '. esc_html(__('My applied jobs','wp-job-portal')); ?>
                </div>
            </div>
            <div id="wpjobportal-shortcode-wrapper">
                <div class="wpjobportal-shortcode-1">
                    <?php echo esc_html(__('My companies','wp-job-portal')); ?>
                </div>
                <div class="wpjobportal-shortcode-2">
                    <?php echo '[wpjobportal_my_companies]'; ?>
                </div>
                <div class="wpjobportal-shortcode-3">
                    <?php echo esc_html(__('Show employer','wp-job-portal')).' '. esc_html(__('My companies','wp-job-portal')); ?>
                </div>
            </div>
            <div id="wpjobportal-shortcode-wrapper">
                <div class="wpjobportal-shortcode-1">
                    <?php echo esc_html(__('All Companies','wp-job-portal')); ?>
                </div>
                <div class="wpjobportal-shortcode-2">
                    <?php echo '[wpjobportal_all_companies]'; ?>
                </div>
                <div class="wpjobportal-shortcode-3">
                    <?php echo esc_html(__('Show all companies','wp-job-portal')); ?>&nbsp;(<?php echo esc_html(__('Requires WP Job Portal All Companies Addon','wp-job-portal')); ?>)
                </div>
            </div>
            <div id="wpjobportal-shortcode-wrapper">
                <div class="wpjobportal-shortcode-1">
                    <?php echo esc_html(__('Featured Companies','wp-job-portal')); ?>
                </div>
                <div class="wpjobportal-shortcode-2">
                    <?php echo '[wpjobportal_featured_companies]'; ?>
                </div>
                <div class="wpjobportal-shortcode-3">
                    <?php echo esc_html(__('Show featured companies','wp-job-portal')); ?>&nbsp;(<?php echo esc_html(__('Requires WP Job Portal Featured Companies Addon','wp-job-portal')); ?>)
                </div>
            </div>

            <div id="wpjobportal-shortcode-wrapper">
                <div class="wpjobportal-shortcode-1">
                    <?php echo esc_html(__('My jobs','wp-job-portal')); ?>
                </div>
                <div class="wpjobportal-shortcode-2">
                    <?php echo '[wpjobportal_my_jobs]'; ?>
                </div>
                <div class="wpjobportal-shortcode-3">
                    <?php echo esc_html(__('Show','wp-job-portal')).' '. esc_html(__('employer','wp-job-portal')).' '. esc_html(__('My jobs','wp-job-portal')); ?>
                </div>
            </div>
            <div id="wpjobportal-shortcode-wrapper">
                <div class="wpjobportal-shortcode-1">
                    <?php echo esc_html(__('My resume','wp-job-portal')); ?>
                </div>
                <div class="wpjobportal-shortcode-2">
                    <?php echo '[wpjobportal_my_resumes]'; ?>
                </div>
                <div class="wpjobportal-shortcode-3">
                    <?php echo esc_html(__('Show','wp-job-portal')).' '. esc_html(__('job seeker','wp-job-portal')).' '. esc_html(__('My resumes','wp-job-portal')); ?>
                </div>
            </div>
            <div id="wpjobportal-shortcode-wrapper">
                <div class="wpjobportal-shortcode-1">
                    <?php echo esc_html(__('All Resumes','wp-job-portal')); ?>
                </div>
                <div class="wpjobportal-shortcode-2">
                    <?php echo '[wpjobportal_all_resumes]'; ?>
                </div>
                <div class="wpjobportal-shortcode-3">
                    <?php echo esc_html(__('Show all resumes','wp-job-portal')); ?>&nbsp;(<?php echo esc_html(__('Requires WP Job Portal All Resumes Addon','wp-job-portal')); ?>)
                </div>
            </div>
            <div id="wpjobportal-shortcode-wrapper">
                <div class="wpjobportal-shortcode-1">
                    <?php echo esc_html(__('Featured Resumes','wp-job-portal')); ?>
                </div>
                <div class="wpjobportal-shortcode-2">
                    <?php echo '[wpjobportal_featured_resumes]'; ?>
                </div>
                <div class="wpjobportal-shortcode-3">
                    <?php echo esc_html(__('Show featured resumes','wp-job-portal'));?>&nbsp;(<?php echo esc_html(__('Requires WP Job Portal Featured Resumes Addon','wp-job-portal')); ?>)
                </div>
            </div>

            <div id="wpjobportal-shortcode-wrapper">
                <div class="wpjobportal-shortcode-1">
                    <?php echo esc_html(__('Add','wp-job-portal')).' '. esc_html(__('Company','wp-job-portal')); ?>
                </div>
                <div class="wpjobportal-shortcode-2">
                    <?php echo '[wpjobportal_add_company]'; ?>
                </div>
                <div class="wpjobportal-shortcode-3">
                    <?php echo esc_html(__('Show','wp-job-portal')).' '. esc_html(__('add','wp-job-portal')).' '. esc_html(__('company','wp-job-portal')).' '. esc_html(__('form','wp-job-portal')); ?>
                </div>
            </div>

                <div id="wpjobportal-shortcode-wrapper">
                    <div class="wpjobportal-shortcode-1">
                        <?php echo esc_html(__('Add','wp-job-portal')).' '. esc_html(__('Department','wp-job-portal')); ?>
                    </div>
                    <div class="wpjobportal-shortcode-2">
                        <?php echo '[wpjobportal_add_department]'; ?>
                    </div>
                    <div class="wpjobportal-shortcode-3">
                        <?php echo esc_html(__('Show','wp-job-portal')).' '. esc_html(__('add','wp-job-portal')).' '. esc_html(__('Department','wp-job-portal')).' '. esc_html(__('form','wp-job-portal')); ?>&nbsp;(<?php echo esc_html(__('Requires WP Job Portal Multi Departments Addon','wp-job-portal')); ?>)
                    </div>
                </div>
            <div id="wpjobportal-shortcode-wrapper">
                <div class="wpjobportal-shortcode-1">
                    <?php echo esc_html(__('Add','wp-job-portal')).' '. esc_html(__('job','wp-job-portal')); ?>
                </div>
                <div class="wpjobportal-shortcode-2">
                    <?php echo '[wpjobportal_add_job]'; ?>
                </div>
                <div class="wpjobportal-shortcode-3">
                    <?php echo esc_html(__('Show','wp-job-portal')).' '. esc_html(__('add','wp-job-portal')).' '. esc_html(__('job','wp-job-portal')).' '. esc_html(__('form','wp-job-portal')); ?>
                </div>
            </div>
            <div id="wpjobportal-shortcode-wrapper">
                <div class="wpjobportal-shortcode-1">
                    <?php echo esc_html(__('Add','wp-job-portal')).' '. esc_html(__('resume','wp-job-portal')); ?>
                </div>
                <div class="wpjobportal-shortcode-2">
                    <?php echo '[wpjobportal_add_resume]'; ?>
                </div>
                <div class="wpjobportal-shortcode-3">
                    <?php echo esc_html(__('Show','wp-job-portal')).' '. esc_html(__('add','wp-job-portal')).' '. esc_html(__('resume','wp-job-portal')).' '. esc_html(__('form','wp-job-portal')); ?>
                </div>
            </div>
            <div id="wpjobportal-shortcode-wrapper">
                <div class="wpjobportal-shortcode-1">
                    <?php echo esc_html(__('Resume','wp-job-portal')).' '. esc_html(__('search','wp-job-portal')); ?>
                </div>
                <div class="wpjobportal-shortcode-2">
                    <?php echo '[wpjobportal_resume_search]'; ?>
                </div>
                <div class="wpjobportal-shortcode-3">
                    <?php echo esc_html(__('Show','wp-job-portal')).' '. esc_html(__('resume','wp-job-portal')).' '. esc_html(__('search','wp-job-portal')).' '. esc_html(__('form','wp-job-portal')); ?>&nbsp;(<?php echo esc_html(__('Requires WP Job Portal Resume Search Addon','wp-job-portal')); ?>)
                </div>
            </div>
            <div id="wpjobportal-shortcode-wrapper">
                <div class="wpjobportal-shortcode-1">
                    <?php echo esc_html(__('Employer registration','wp-job-portal')); ?>
                </div>
                <div class="wpjobportal-shortcode-2">
                    <?php echo '[wpjobportal_employer_registration]'; ?>
                </div>
                <div class="wpjobportal-shortcode-3">
                    <?php echo esc_html(__('Show','wp-job-portal')).' '. esc_html(__('employer','wp-job-portal')).' '. esc_html(__('registration form','wp-job-portal')); ?>
                </div>
            </div>
            <div id="wpjobportal-shortcode-wrapper">
                <div class="wpjobportal-shortcode-1">
                    <?php echo esc_html(__('Job seeker registration','wp-job-portal')); ?>
                </div>
                <div class="wpjobportal-shortcode-2">
                    <?php echo '[wpjobportal_jobseeker_registration]'; ?>
                </div>
                <div class="wpjobportal-shortcode-3">
                    <?php echo esc_html(__('Show','wp-job-portal')).' '. esc_html(__('job seeker','wp-job-portal')).' '. esc_html(__('registration form','wp-job-portal')); ?>
                </div>
            </div>


            <div id="wpjobportal-shortcode-wrapper">
                <div class="wpjobportal-shortcode-1">
                    <?php echo esc_html(__('Login page','wp-job-portal')); ?>
                </div>
                <div class="wpjobportal-shortcode-2">
                    <?php echo '[wpjobportal_login_page]'; ?>
                </div>
                <div class="wpjobportal-shortcode-3">
                    <?php echo esc_html(__('Show login page','wp-job-portal')); ?>
                </div>
            </div>
            <div id="wpjobportal-shortcode-wrapper">
                <div class="wpjobportal-shortcode-1">
                    <?php echo esc_html(__('Search job','wp-job-portal')).' '. esc_html(__('widget','wp-job-portal')); ?>
                </div>
                <div class="wpjobportal-shortcode-2">
                    <?php echo '[wpjobportal_searchjob]'; ?>
                </div>
                <div class="wpjobportal-shortcode-3">
                    <?php echo esc_html(__('Show','wp-job-portal')).' '. esc_html(__('search','wp-job-portal')).' '. esc_html(__('job','wp-job-portal')).' '. esc_html(__('form','wp-job-portal')).' '. esc_html(__('in widget style','wp-job-portal')); ?>
                </div>
            </div>

                <div id="wpjobportal-shortcode-wrapper">
                    <div class="wpjobportal-shortcode-1">
                        <?php echo esc_html(__('My Packages','wp-job-portal')); ?>
                    </div>
                    <div class="wpjobportal-shortcode-2">
                        <?php echo '[wpjobportal_mypackages]'; ?>
                    </div>
                    <div class="wpjobportal-shortcode-3">
                        <?php echo esc_html(__('Show','wp-job-portal')).' '. esc_html(__('My','wp-job-portal')).' '. esc_html(__('Packages','wp-job-portal')).' '. esc_html(__('form','wp-job-portal')).' '. esc_html(__('in widget style','wp-job-portal')); ?>&nbsp;(<?php echo esc_html(__('Requires WP Job Portal Credits Addon','wp-job-portal')); ?>)
                    </div>
                </div>

                <div id="wpjobportal-shortcode-wrapper">
                    <div class="wpjobportal-shortcode-1">
                        <?php echo esc_html(__('My Subscription','wp-job-portal')); ?>
                    </div>
                    <div class="wpjobportal-shortcode-2">
                        <?php echo '[wpjobportal_mysubscription]'; ?>
                    </div>
                    <div class="wpjobportal-shortcode-3">
                        <?php echo esc_html(__('Show','wp-job-portal')).' '. esc_html(__('My','wp-job-portal')).' '. esc_html(__('Subscription','wp-job-portal')).' '. esc_html(__('form','wp-job-portal')); ?>&nbsp;(<?php echo esc_html(__('Requires WP Job Portal Credits Addon','wp-job-portal')); ?>)
                    </div>
                </div>

                <div id="wpjobportal-shortcode-wrapper">
                    <div class="wpjobportal-shortcode-1">
                        <?php echo esc_html(__('All Packages','wp-job-portal')); ?>
                    </div>
                    <div class="wpjobportal-shortcode-2">
                        <?php echo '[wpjobportal_allpackages]'; ?>
                    </div>
                    <div class="wpjobportal-shortcode-3">
                        <?php echo esc_html(__('Show','wp-job-portal')).' '. esc_html(__('All','wp-job-portal')).' '. esc_html(__('Packages','wp-job-portal')).' '. esc_html(__('form','wp-job-portal')); ?>&nbsp;(<?php echo esc_html(__('Requires WP Job Portal Credits Addon','wp-job-portal')); ?>)
                    </div>
                </div>

                <div id="wpjobportal-shortcode-wrapper">
                    <div class="wpjobportal-shortcode-1">
                        <?php echo esc_html(__('My Invoices','wp-job-portal')); ?>
                    </div>
                    <div class="wpjobportal-shortcode-2">
                        <?php echo '[wpjobportal_myinvoices]'; ?>
                    </div>
                    <div class="wpjobportal-shortcode-3">
                        <?php echo esc_html(__('Show','wp-job-portal')).' '. esc_html(__('My','wp-job-portal')).' '. esc_html(__('Invoices','wp-job-portal')).' '. esc_html(__('form','wp-job-portal')); ?>&nbsp;(<?php echo esc_html(__('Requires WP Job Portal Credits Addon','wp-job-portal')); ?>)
                    </div>
                </div>
        </div>
    </div>
</div>
