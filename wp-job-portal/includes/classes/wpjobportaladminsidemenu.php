<?php
if (!defined('ABSPATH')) die('Restricted Access');
$wpjobportal_controller = WPJOBPORTALrequest::getVar('page',null,'wpjobportal');
$wpjobportal_layout = WPJOBPORTALrequest::getVar('wpjobportallt');
$wpjobportal_fieldfor = WPJOBPORTALrequest::getVar('ff');
$wpjobportal_for = WPJOBPORTALrequest::getVar('for');

wp_register_script( 'wpjobportal-menu-handle', '' );
wp_enqueue_script( 'wpjobportal-menu-handle' );

$wpjobportal_menu_js_script = '
    jQuery( function() {
        jQuery( "#accordion" ).accordion({
            heightStyle: "content",
            collapsible: true,
            active: true,
        });
    });

    ';
wp_add_inline_script( 'wpjobportal-menu-handle', $wpjobportal_menu_js_script );
?>
<div id="wpjobportaladmin-logo">
    <a href="admin.php?page=wpjobportal" class="wpjobportaladmin-anchor">
        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/control_panel/dashboard/logo.png'; ?>"/>
    </a>
    <img id="wpjobportaladmin-menu-toggle" src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/control_panel/dashboard/admin-left-menu/menu.png'; ?>" />
</div>
<ul class="wpjobportaladmin-sidebar-menu tree" data-widget="tree" id="accordion">
    <li class="treeview <?php if( ($wpjobportal_controller == 'wpjobportal' && $wpjobportal_layout != 'themes' && $wpjobportal_layout != 'shortcodes' && $wpjobportal_layout != 'addonstatus') || $wpjobportal_controller == 'wpjobportal_activitylog' || $wpjobportal_controller == 'wpjobportal_systemerror' || $wpjobportal_controller == 'wpjobportal_slug' ) echo 'active'; ?>">
        <a href="admin.php?page=wpjobportal" title="<?php echo esc_attr(__('dashboard' , 'wp-job-portal')); ?>" class="wpjobportaladmin-menu-icon-class wpjobportaladmin-menu-icon-dashboard">
            <span class="wpjobportaladmin-text">
                <?php echo esc_html(__('Dashboard' , 'wp-job-portal')); ?>
            </span>
        </a>
        <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
            <li class="<?php if($wpjobportal_controller == 'wpjobportal' && ($wpjobportal_layout == 'controlpanel' || $wpjobportal_layout == '')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal" title="<?php echo esc_attr(__('dashboard', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Dashboard', 'wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_slug' && ($wpjobportal_layout == 'slug')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_slug&wpjobportallt=slug" title="<?php echo esc_attr(__('slug','wp-job-portal')); ?>">
                    <?php echo esc_html(__('Slug','wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal' && ($wpjobportal_layout == 'pageseo' || $wpjobportal_layout == '')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal&wpjobportallt=pageseo" title="<?php echo esc_attr(__('SEO','wp-job-portal')); ?>">
                    <?php echo esc_html(__('SEO','wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_thirdpartyimport' && ($wpjobportal_layout == 'importdata' || $wpjobportal_layout == '')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_thirdpartyimport" title="<?php echo esc_attr(__('Import Data','wp-job-portal')); ?>">
                    <?php echo esc_html(__('Import Third Party Data','wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_activitylog' && ($wpjobportal_layout == 'wpjobportal_activitylog' || $wpjobportal_layout == '')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_activitylog" title="<?php echo esc_attr(__('activity log','wp-job-portal')); ?>">
                    <?php echo esc_html(__('Activity Log','wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal' && ($wpjobportal_layout == 'wpjobportalstats' || $wpjobportal_layout == '')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal&wpjobportallt=wpjobportalstats" title="<?php echo esc_attr(__('stats','wp-job-portal')); ?>">
                    <?php echo esc_html(__('Stats','wp-job-portal')); ?>
                </a>
            </li>
            <?php /*<li class="<?php if($wpjobportal_controller == 'wpjobportal' && ($wpjobportal_layout == 'translations')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal&wpjobportallt=translations" title="<?php echo esc_attr(__('translations','wp-job-portal')); ?>">
                    <?php echo esc_html(__('Translations','wp-job-portal')); ?>
                </a>
            </li> */?>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_systemerror' && ($wpjobportal_layout == 'wpjobportal_systemerror' || $wpjobportal_layout == '')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_systemerror" title="<?php echo esc_attr(__('system errors','wp-job-portal')); ?>">
                    <?php echo esc_html(__('System Errors','wp-job-portal')); ?>
                </a>
            </li>

        </ul>
    </li>
    <li class="treeview <?php if($wpjobportal_controller == 'wpjobportal_job' || $wpjobportal_controller == 'wpjobportal_jobapply' || $wpjobportal_controller == 'wpjobportal_jobalert' || $wpjobportal_controller == 'wpjobportal_customfield' || ($wpjobportal_controller == 'wpjobportal_fieldordering' && $wpjobportal_fieldfor == 2)) echo 'active'; ?>">
        <a href="admin.php?page=wpjobportal_job" title="<?php echo esc_attr(__('jobs' , 'wp-job-portal')); ?>" class="wpjobportaladmin-menu-icon-class wpjobportaladmin-menu-icon-jobs">
            <span class="wpjobportaladmin-text">
                <?php echo esc_html(__('Jobs' , 'wp-job-portal')); ?>
            </span>
        </a>
        <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_job' && ($wpjobportal_controller == 'wpjobportal_jobapply' && $wpjobportal_layout == 'jobappliedresume' || $wpjobportal_layout == '')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_job" title="<?php echo esc_attr(__('jobs', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Jobs', 'wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_job' && ($wpjobportal_layout == 'formjob')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_job&wpjobportallt=formjob" title="<?php echo esc_attr(__('add new job', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Add New Job', 'wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_job' && ($wpjobportal_layout == 'jobqueue')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_job&wpjobportallt=jobqueue" title="<?php echo esc_attr(__('approval queue', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Approval Queue', 'wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_fieldordering' && ($wpjobportal_layout == '')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_fieldordering&ff=2" title="<?php echo esc_attr(__('fields', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Fields', 'wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_fieldordering' && ($wpjobportal_layout == 'searchfields')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_fieldordering&wpjobportallt=searchfields&ff=2" title="<?php echo esc_attr(__('search fields', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Search Fields', 'wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_configuration' && ($wpjobportal_layout == 'configurations')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_configuration&wpjobportallt=configurations&wpjpconfigid=job_apply" title="<?php echo esc_attr(__('configuration', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Job Apply Configurations', 'wp-job-portal')); ?>
                </a>
            </li>
            <?php
                //do_action('wpjobportal_addons_custom_fields_searchfields',$wpjobportal_controller,$wpjobportal_layout);
            ?>
            <?php
            if(in_array('jobalert', wpjobportal::$_active_addons)){
                do_action('wpjobportal_addons_sidemenue_admin_jobalert',$wpjobportal_controller,$wpjobportal_layout);
            }else{
                $wpjobportal_addoninfo = wpjobportal_checkWPJPPluginInfo('wp-job-portal-jobalert/wp-job-portal-jobalert.php');
                if($wpjobportal_addoninfo['availability'] == "1"){
                    $wpjobportal_text = $wpjobportal_addoninfo['text'];
                    $wpjobportal_url = "plugins.php?s=wp-job-portal-jobalert&plugin_status=inactive";
                }elseif($wpjobportal_addoninfo['availability'] == "0"){
                    $wpjobportal_text = $wpjobportal_addoninfo['text'];
                    $wpjobportal_url = "https://wpjobportal.com/product/job-alert/";
                } ?>
                <li class="disabled-menu">
                    <span class="wpjobportaladmin-text"><?php echo esc_html(__('Job Alert' , 'wp-job-portal')); ?></span>
                    <a href="<?php echo esc_url($wpjobportal_url); ?>" class="wpjobportaladmin-install-btn" title="<?php echo esc_attr($wpjobportal_text); ?>"><?php echo esc_html($wpjobportal_text); ?></a>
                </li>
            <?php } ?>
        </ul>
    </li>
    <li class="treeview <?php if($wpjobportal_controller == 'wpjobportal_resume' ||  ($wpjobportal_controller == 'wpjobportal_fieldordering' && $wpjobportal_fieldfor == 3)) echo 'active'; ?>">
        <a href="admin.php?page=wpjobportal_resume" title="<?php echo esc_attr(__('resume' , 'wp-job-portal')); ?>" class="wpjobportaladmin-menu-icon-class wpjobportaladmin-menu-icon-resumes">
            <span class="wpjobportaladmin-text">
                <?php echo esc_html(__('Resume' , 'wp-job-portal')); ?>
            </span>
        </a>
        <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_resume' && ($wpjobportal_layout == '')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_resume" title="<?php echo esc_attr(__('resume', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Resume', 'wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_resume' && ($wpjobportal_layout == 'resumequeue')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_resume&wpjobportallt=resumequeue" title="<?php echo esc_attr(__('approval queue', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Approval Queue', 'wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_fieldordering' && ($wpjobportal_layout == '')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_fieldordering&ff=3" title="<?php echo esc_attr(__('fields', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Fields', 'wp-job-portal')); ?>
                </a>
            </li>
            <?php if(in_array('resumesearch', wpjobportal::$_active_addons)){ // hiding search fields without resume search addon ?>
                <li class="<?php if($wpjobportal_controller == 'wpjobportal_fieldordering' && ($wpjobportal_layout == 'searchfields')) echo 'active'; ?>">
                    <a href="admin.php?page=wpjobportal_fieldordering&wpjobportallt=searchfields&ff=3" title="<?php echo esc_attr(__('search fields', 'wp-job-portal')); ?>">
                        <?php echo esc_html(__('Search Fields', 'wp-job-portal')); ?>
                    </a>
                </li>
            <?php } ?>
            
                <li class="<?php if($wpjobportal_controller == 'wpjobportal_fieldordering' && ($wpjobportal_layout == 'quickapply')) echo 'active'; ?>">
                    <a href="admin.php?page=wpjobportal_fieldordering&ff=5" title="<?php echo esc_attr(__('search fields', 'wp-job-portal')); ?>">
                        <?php echo esc_html(__('Quick Apply Fields', 'wp-job-portal')); ?>
                    </a>
                </li>
        </ul>
    </li>

    <li class="treeview <?php if(($wpjobportal_controller == 'wpjobportal_company' || ($wpjobportal_controller == 'wpjobportal_fieldordering' && $wpjobportal_fieldfor == 1)) ) echo 'active'; ?>">
        <a href="admin.php?page=wpjobportal_company" title="<?php echo esc_attr(__('companies' , 'wp-job-portal')); ?>" class="wpjobportaladmin-menu-icon-class wpjobportaladmin-menu-icon-companies">
            <span class="wpjobportaladmin-text">
                <?php echo esc_html(__('Companies' , 'wp-job-portal')); ?>
            </span>
        </a>
        <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_company' && ($wpjobportal_layout == '')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_company" title="<?php echo esc_attr(__('companies', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Companies', 'wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_company' && ($wpjobportal_layout == 'formcompany')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_company&wpjobportallt=formcompany" title="<?php echo esc_attr(__('add new company', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Add New Company', 'wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_company' && ($wpjobportal_layout == 'companiesqueue')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_company&wpjobportallt=companiesqueue" title="<?php echo esc_attr(__('approval queue', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Approval Queue', 'wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_fieldordering' && ($wpjobportal_layout == '')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_fieldordering&ff=1" title="<?php echo esc_attr(__('fields', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Fields', 'wp-job-portal')); ?>
                </a>
            </li>
        </ul>
    </li>
    <li class="treeview <?php if($wpjobportal_controller == 'wpjobportal_configuration' || $wpjobportal_controller == 'wpjobportal_paymentmethodconfiguration' || $wpjobportal_controller == 'wpjobportal_cronjob' ) echo 'active'; ?>">
        <a href="admin.php?page=wpjobportal_configuration" title="<?php echo esc_attr(__('configuration' , 'wp-job-portal')); ?>" class="wpjobportaladmin-menu-icon-class wpjobportaladmin-menu-icon-configurations">
            <span class="wpjobportaladmin-text">
                <?php echo esc_html(__('Configuration' , 'wp-job-portal')); ?>
            </span>
        </a>
        <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_configuration' && ($wpjobportal_layout == 'configurations')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_configuration&wpjobportallt=configurations" title="<?php echo esc_attr(__('configuration', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Configuration', 'wp-job-portal')); ?>
                </a>
            </li>
        </ul>
    </li>
    <li class="treeview <?php if($wpjobportal_controller == 'wpjobportal_premiumplugin' || ($wpjobportal_controller == 'wpjobportal' && $wpjobportal_layout == 'addonstatus')) echo 'active'; ?>">
        <a href="admin.php?page=wpjobportal_premiumplugin" title="<?php echo esc_attr(__('ad ons' , 'wp-job-portal')); ?>" class="wpjobportaladmin-menu-icon-class wpjobportaladmin-menu-icon-addons">
            <span class="wpjobportaladmin-text">
                <?php echo esc_html(__('Addons' , 'wp-job-portal')); ?>
            </span>
        </a>
        <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_premiumplugin' && ($wpjobportal_layout == '' || $wpjobportal_layout == 'step1' || $wpjobportal_layout == 'step2' || $wpjobportal_layout == 'step3')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_premiumplugin" title="<?php echo esc_attr(__('install addons','wp-job-portal')); ?>">
                    <?php echo esc_html(__('Install Addons','wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal' && $wpjobportal_layout == 'addonstatus') echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal&wpjobportallt=addonstatus" title="<?php echo esc_attr(__('Addons Status','wp-job-portal')); ?>">
                    <?php echo esc_html(__('Addons Status','wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal' && $wpjobportal_layout == 'updatekey') echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_premiumplugin&wpjobportallt=updatekey" title="<?php echo esc_attr(__('Update Key','wp-job-portal')); ?>">
                    <?php echo esc_html(__('Update Key','wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_premiumplugin' && ($wpjobportal_layout == 'addonfeatures')) echo 'active'; ?>">
                <a href="?page=wpjobportal_premiumplugin&wpjobportallt=addonfeatures" title="<?php echo esc_attr(__('addons list','wp-job-portal')); ?>">
                    <?php echo esc_html(__('Addons List','wp-job-portal')); ?>
                </a>
            </li>
        </ul>
    </li>
    <li class="treeview <?php if($wpjobportal_controller == 'wpjobportal' && $wpjobportal_layout == 'shortcodes' ) echo 'active'; ?>">
        <a href="admin.php?page=wpjobportal&wpjobportallt=shortcodes" title="<?php echo esc_attr(__('short codes' , 'wp-job-portal')); ?>" class="wpjobportaladmin-menu-icon-class wpjobportaladmin-menu-icon-shortcodes">
            <span class="wpjobportaladmin-text">
                <?php echo esc_html(__('Short Codes' , 'wp-job-portal')); ?>
            </span>
        </a>
        <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
            <li class="<?php if($wpjobportal_controller == 'wpjobportal' && ($wpjobportal_layout == 'shortcodes')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal&wpjobportallt=shortcodes" title="<?php echo esc_attr(__('short codes', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Short Codes', 'wp-job-portal')); ?>
                </a>
            </li>
        </ul>
    </li>
    <li class="treeview <?php if($wpjobportal_controller == 'wpjobportal_theme') echo 'active'; ?>">
        <a href="admin.php?page=wpjobportal_theme" title="<?php echo esc_attr(__('colors' , 'wp-job-portal')); ?>" class="wpjobportaladmin-menu-icon-class wpjobportaladmin-menu-icon-colors">
            <span class="wpjobportaladmin-text">
                <?php echo esc_html(__('Colors' , 'wp-job-portal')); ?>
            </span>
        </a>
        <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_theme') echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_theme" title="<?php echo esc_attr(__('colors','wp-job-portal')); ?>">
                    <?php echo esc_html(__('Colors','wp-job-portal')); ?>
                </a>
            </li>
        </ul>
    </li>
    <li class="treeview <?php if($wpjobportal_controller == 'wpjobportal_report' || ($wpjobportal_controller == 'wpjobportal_reports')) echo 'active'; ?>">
        <a href="admin.php?page=wpjobportal_report&wpjobportallt=overallreports" title="<?php echo esc_attr(__('reports' , 'wp-job-portal')); ?>" class="wpjobportaladmin-menu-icon-class wpjobportaladmin-menu-icon-reports">
            <span class="wpjobportaladmin-text">
                <?php echo esc_html(__('Reports' , 'wp-job-portal')); ?>
            </span>
        </a>
        <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_report' && ($wpjobportal_layout == 'overallreports')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_report&wpjobportallt=overallreports" title="<?php echo esc_attr(__('overall reports', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Overall Reports', 'wp-job-portal')); ?>
                </a>
            </li>
            <?php
                if(in_array('reports', wpjobportal::$_active_addons)){
                    do_action('wpjobportal_addons_admin_sidemenu_links_for_reports',$wpjobportal_controller,$wpjobportal_layout);
                }else{
                    $wpjobportal_addoninfo = wpjobportal_checkWPJPPluginInfo('wp-job-portal-reports/wp-job-portal-reports.php');
                    if($wpjobportal_addoninfo['availability'] == "1"){
                        $wpjobportal_text = $wpjobportal_addoninfo['text'];
                        $wpjobportal_url = "plugins.php?s=wp-job-portal-reports&plugin_status=inactive";
                    }elseif($wpjobportal_addoninfo['availability'] == "0"){
                        $wpjobportal_text = $wpjobportal_addoninfo['text'];
                        $wpjobportal_url = "https://wpjobportal.com/product/reports/";
                    } ?>
                    <li class="disabled-menu fw">
                        <span class="wpjobportaladmin-text"><?php echo esc_html(__('Employer / Job Seeker Report' , 'wp-job-portal')); ?></span>
                        <a href="<?php echo esc_url($wpjobportal_url); ?>" class="wpjobportaladmin-install-btn" title="<?php echo esc_attr($wpjobportal_text); ?>"><?php echo esc_html($wpjobportal_text); ?></a>
                    </li>
               <?php } ?>
        </ul>
    </li>
    <?php if(in_array('departments', wpjobportal::$_active_addons)){ ?>
        <li class="treeview <?php if($wpjobportal_controller == 'wpjobportal_departments') echo 'active'; ?>">
            <a href="admin.php?page=wpjobportal_departments" title="<?php echo esc_attr(__('departments' , 'wp-job-portal')); ?>" class="wpjobportaladmin-menu-icon-class wpjobportaladmin-menu-icon-departments">
                <span class="wpjobportaladmin-text">
                    <?php echo esc_html(__('Departments' , 'wp-job-portal')); ?>
                </span>
            </a>
            <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
                <li class="<?php if($wpjobportal_controller == 'wpjobportal_departments' && ($wpjobportal_layout == '')) echo 'active'; ?>">
                    <a href="admin.php?page=wpjobportal_departments" title="<?php echo esc_attr(__('departments', 'wp-job-portal')); ?>">
                        <?php echo esc_html(__('Departments', 'wp-job-portal')); ?>
                    </a>
                </li>
                <li class="<?php if($wpjobportal_controller == 'wpjobportal_departments' && ($wpjobportal_layout == 'formdepartment')) echo 'active'; ?>">
                    <a href="admin.php?page=wpjobportal_departments&wpjobportallt=formdepartment" title="<?php echo esc_attr(__('add new department', 'wp-job-portal')); ?>">
                        <?php echo esc_html(__('Add New Department', 'wp-job-portal')); ?>
                    </a>
                </li>
                <li class="<?php if($wpjobportal_controller == 'wpjobportal_departments' && ($wpjobportal_layout == 'departmentqueue')) echo 'active'; ?>">
                    <a href="admin.php?page=wpjobportal_departments&wpjobportallt=departmentqueue" title="<?php echo esc_attr(__('approval queue', 'wp-job-portal')); ?>">
                        <?php echo esc_html(__('Approval Queue', 'wp-job-portal')); ?>
                    </a>
                </li>
            </ul>
        </li>
    <?php }else{
        $wpjobportal_addoninfo = wpjobportal_checkWPJPPluginInfo('wp-job-portal-departments/wp-job-portal-departments.php');
        if($wpjobportal_addoninfo['availability'] == "1"){
            $wpjobportal_text = $wpjobportal_addoninfo['text'];
            $wpjobportal_url = "plugins.php?s=wp-job-portal-departments&plugin_status=inactive";
        }elseif($wpjobportal_addoninfo['availability'] == "0"){
            $wpjobportal_text = $wpjobportal_addoninfo['text'];
            $wpjobportal_url = "https://wpjobportal.com/product/multi_departments/";
        } ?>
        <li class="treeview">
            <a href="javascript: void(0);" title="<?php echo esc_attr(__('departments' , 'wp-job-portal')); ?>" class="wpjobportaladmin-menu-icon-class wpjobportaladmin-menu-icon-departments wpjobportaladmin-menu-icon-disabled">
                <span class="wpjobportaladmin-text disabled-menu"><?php echo esc_html(__('Department' , 'wp-job-portal')); ?></span>
            </a>
            <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
                <li class="disabled-menu">
                    <span class="wpjobportaladmin-text"><?php echo esc_html(__('departments' , 'wp-job-portal')); ?></span>
                    <a href="<?php echo esc_url($wpjobportal_url); ?>" class="wpjobportaladmin-install-btn" title="<?php echo esc_attr($wpjobportal_text); ?>"><?php echo esc_html($wpjobportal_text); ?></a>
                </li>
            </ul>
        </li>
    <?php } ?>

    <?php if(in_array('coverletter', wpjobportal::$_active_addons)){ ?>
        <li class="treeview <?php if($wpjobportal_controller == 'wpjobportal_coverletter') echo 'active'; ?>" >
            <a href="admin.php?page=wpjobportal_coverletter" title="<?php echo esc_attr(__('coverletters' , 'wp-job-portal')); ?>" class="wpjobportaladmin-menu-icon-class wpjobportaladmin-menu-icon-coverletters">
                <span class="wpjobportaladmin-text">
                    <?php echo esc_html(__('Cover Letters' , 'wp-job-portal')); ?>
                </span>
            </a>
            <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
                <li class="<?php if($wpjobportal_controller == 'wpjobportal_coverletter' && (($wpjobportal_layout == '') || ($wpjobportal_layout == 'formcoverletter') )) echo 'active'; ?>">
                    <a href="admin.php?page=wpjobportal_coverletter" title="<?php echo esc_attr(__('coverletter', 'wp-job-portal')); ?>">
                        <?php echo esc_html(__('Cover Letters', 'wp-job-portal')); ?>
                    </a>
                </li>
                <?php /*
                <li class="<?php if($wpjobportal_controller == 'wpjobportal_coverletter' && ($wpjobportal_layout == 'formcoverletter')) echo 'active'; ?>">
                    <a href="admin.php?page=wpjobportal_coverletter&wpjobportallt=formcoverletter" title="<?php //echo esc_html(__('add new cover letter', 'wp-job-portal')); ?>">
                        <?php //echo esc_html(__('Add New Cover Letter', 'wp-job-portal')); ?>
                    </a>
                </li>
                */ ?>
                <li class="<?php if($wpjobportal_controller == 'wpjobportal_coverletter' && ($wpjobportal_layout == 'coverletterqueue')) echo 'active'; ?>">
                    <a href="admin.php?page=wpjobportal_coverletter&wpjobportallt=coverletterqueue" title="<?php echo esc_attr(__('approval queue', 'wp-job-portal')); ?>">
                        <?php echo esc_html(__('Approval Queue', 'wp-job-portal')); ?>
                    </a>
                </li>
            </ul>
        </li>
    <?php }else{
        $wpjobportal_addoninfo = wpjobportal_checkWPJPPluginInfo('wp-job-portal-coverletter/wp-job-portal-coverletter.php');
        if($wpjobportal_addoninfo['availability'] == "1"){
            $wpjobportal_text = $wpjobportal_addoninfo['text'];
            $wpjobportal_url = "plugins.php?s=wp-job-portal-coverletter&plugin_status=inactive";
        }elseif($wpjobportal_addoninfo['availability'] == "0"){
            $wpjobportal_text = $wpjobportal_addoninfo['text'];
            $wpjobportal_url = "https://wpjobportal.com/product/multi_coverletter/";
        } ?>
        <li class="treeview">
            <a href="javascript: void(0);" title="<?php echo esc_attr(__('coverletter' , 'wp-job-portal')); ?>" class="wpjobportaladmin-menu-icon-class wpjobportaladmin-menu-icon-coverletters wpjobportaladmin-menu-icon-disabled">
                <span class="wpjobportaladmin-text disabled-menu"><?php echo esc_html(__('Cover Letter' , 'wp-job-portal')); ?></span>
            </a>
            <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
                <li class="disabled-menu">
                    <span class="wpjobportaladmin-text"><?php echo esc_html(__('Cover Letter' , 'wp-job-portal')); ?></span>
                    <a href="<?php echo esc_url($wpjobportal_url); ?>" class="wpjobportaladmin-install-btn" title="<?php echo esc_attr($wpjobportal_text); ?>"><?php echo esc_html($wpjobportal_text); ?></a>
                </li>
            </ul>
        </li>
    <?php } ?>


    <?php
        if(in_array('message', wpjobportal::$_active_addons)){
            do_action('wpjobportal_addons_admin_sidemenu_links_for_message' , $wpjobportal_controller,$wpjobportal_layout ); // cc
        }else{
            $wpjobportal_addoninfo = wpjobportal_checkWPJPPluginInfo('wp-job-portal-message/wp-job-portal-message.php');
            if($wpjobportal_addoninfo['availability'] == "1"){
                $wpjobportal_text = $wpjobportal_addoninfo['text'];
                $wpjobportal_url = "plugins.php?s=wp-job-portal-message&plugin_status=inactive";
            }elseif($wpjobportal_addoninfo['availability'] == "0"){
                $wpjobportal_text = $wpjobportal_addoninfo['text'];
                $wpjobportal_url = "https://wpjobportal.com/product/messages/";
            } ?>
            <li class="treeview">
                <a href="javascript: void(0);" title="<?php echo esc_attr(__('Message' , 'wp-job-portal')); ?>" class="wpjobportaladmin-menu-icon-class wpjobportaladmin-menu-icon-messages wpjobportaladmin-menu-icon-disabled">
                    <span class="wpjobportaladmin-text disabled-menu"><?php echo esc_html(__('Message' , 'wp-job-portal')); ?></span>
                </a>
                <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
                    <li class="disabled-menu">
                        <span class="wpjobportaladmin-text"><?php echo esc_html(__('Message' , 'wp-job-portal')); ?></span>
                        <a href="<?php echo esc_url($wpjobportal_url); ?>" class="wpjobportaladmin-install-btn" title="<?php echo esc_attr($wpjobportal_text); ?>"><?php echo esc_html($wpjobportal_text); ?></a>
                    </li>
                </ul>
            </li>
    <?php } ?>
    <?php
        if(in_array('credits', wpjobportal::$_active_addons)){
            do_action('wpjobportal_addons_admin_sidemenu_package',$wpjobportal_controller,$wpjobportal_layout);
        }else{

            $wpjobportal_addoninfo = wpjobportal_checkWPJPPluginInfo('wp-job-portal-credits/wp-job-portal-credits.php');
            if($wpjobportal_addoninfo['availability'] == "1"){
                $wpjobportal_text = $wpjobportal_addoninfo['text'];
                $wpjobportal_url = "plugins.php?s=wp-job-portal-credits&plugin_status=inactive";
            }elseif($wpjobportal_addoninfo['availability'] == "0"){
                $wpjobportal_text = $wpjobportal_addoninfo['text'];
                $wpjobportal_url = "https://wpjobportal.com/product/credit-system/";
            } ?>
            <li class="treeview">
                <a href="javascript: void(0);" title="<?php echo esc_attr(__('Credits' , 'wp-job-portal')); ?>" class="wpjobportaladmin-menu-icon-class wpjobportaladmin-menu-icon-credits wpjobportaladmin-menu-icon-disabled">
                    <span class="wpjobportaladmin-text disabled-menu"><?php echo esc_html(__('Credits' , 'wp-job-portal')); ?></span>
                </a>
                <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
                    <li class="disabled-menu">
                        <span class="wpjobportaladmin-text"><?php echo esc_html(__('Credits' , 'wp-job-portal')); ?></span>
                        <a href="<?php echo esc_url($wpjobportal_url); ?>" class="wpjobportaladmin-install-btn" title="<?php echo esc_attr($wpjobportal_text); ?>"><?php echo esc_html($wpjobportal_text); ?></a>
                    </li>
                </ul>
            </li>
    <?php } ?>
    <li class="treeview <?php if($wpjobportal_controller == 'wpjobportal_country' || $wpjobportal_controller == 'wpjobportal_addressdata' || $wpjobportal_controller == 'wpjobportal_state' || $wpjobportal_controller == 'wpjobportal_city') echo 'active'; ?>">
        <a href="admin.php?page=wpjobportal_country" title="<?php echo esc_attr(__('countries' , 'wp-job-portal')); ?>" class="wpjobportaladmin-menu-icon-class wpjobportaladmin-menu-icon-addressdata">
            <span class="wpjobportaladmin-text">
                <?php echo esc_html(__('Address Data' , 'wp-job-portal')); ?>
            </span>
        </a>
        <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_city' && ($wpjobportal_layout == 'loadaddressdata')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_city&wpjobportallt=loadaddressdata" title="<?php echo esc_attr(__('load address data', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Load Address Data', 'wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_city' && ($wpjobportal_layout == 'locationnamesettings')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_city&wpjobportallt=locationnamesettings" title="<?php echo esc_attr(__('Loaction Name settings', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Loaction Name Settings', 'wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if(($wpjobportal_controller == 'wpjobportal_country' && $wpjobportal_layout != 'formcountry') || $wpjobportal_controller == 'wpjobportal_state' || $wpjobportal_controller == 'wpjobportal_city' && ($wpjobportal_layout == 'formcity' || $wpjobportal_layout == '' )) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_country" title="<?php echo esc_attr(__('countries', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Countries', 'wp-job-portal')); ?>&nbsp;/&nbsp;<?php echo esc_html(__('Cities', 'wp-job-portal')); ?>
                </a>
            </li>
            <?php /*
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_country' && ($wpjobportal_layout == 'formcountry')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_country&wpjobportallt=formcountry" title="<?php echo esc_attr(__('add new country', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Add New Country', 'wp-job-portal')); ?>
                </a>
            </li>
            */?>
        </ul>
    </li>
    <?php
        if(in_array('folder', wpjobportal::$_active_addons)){
            do_action('wpjobportal_addons_admin_sidemenu_links_for_folder' , $wpjobportal_controller,$wpjobportal_layout );
        }else{
            $wpjobportal_addoninfo = wpjobportal_checkWPJPPluginInfo('wp-job-portal-folder/wp-job-portal-folder.php');
            if($wpjobportal_addoninfo['availability'] == "1"){
                $wpjobportal_text = $wpjobportal_addoninfo['text'];
                $wpjobportal_url = "plugins.php?s=wp-job-portal-folder&plugin_status=inactive";
            }elseif($wpjobportal_addoninfo['availability'] == "0"){
                $wpjobportal_text = $wpjobportal_addoninfo['text'];
                $wpjobportal_url = "https://wpjobportal.com/product/folders/";
            } ?>
            <li class="treeview">
                <a href="javascript: void(0);" title="<?php echo esc_attr(__('Folder' , 'wp-job-portal')); ?>" class="wpjobportaladmin-menu-icon-class wpjobportaladmin-menu-icon-folders wpjobportaladmin-menu-icon-disabled">
                    <span class="wpjobportaladmin-text disabled-menu"><?php echo esc_html(__('Folder' , 'wp-job-portal')); ?></span>
                </a>
                <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
                    <li class="disabled-menu">
                        <span class="wpjobportaladmin-text"><?php echo esc_html(__('Folder' , 'wp-job-portal')); ?></span>
                        <a href="<?php echo esc_url($wpjobportal_url); ?>" class="wpjobportaladmin-install-btn" title="<?php echo esc_attr($wpjobportal_text); ?>"><?php echo esc_html($wpjobportal_text); ?></a>
                    </li>
                </ul>
            </li>
    <?php } ?>

    <li class="treeview <?php if($wpjobportal_controller == 'wpjobportal_jobtype') echo 'active'; ?>">
        <a href="admin.php?page=wpjobportal_jobtype" title="<?php echo esc_attr(__('job types' , 'wp-job-portal')); ?>" class="wpjobportaladmin-menu-icon-class wpjobportaladmin-menu-icon-jobtypes">
            <span class="wpjobportaladmin-text">
                <?php echo esc_html(__('Job Types' , 'wp-job-portal')); ?>
            </span>
        </a>
        <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_jobtype' && ($wpjobportal_layout == '')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_jobtype" title="<?php echo esc_attr(__('job types','wp-job-portal')); ?>">
                    <?php echo esc_html(__('Job Types','wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_jobtype' && ($wpjobportal_layout == 'formjobtype')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_jobtype&wpjobportallt=formjobtype" title="<?php echo esc_attr(__('add new job type','wp-job-portal')); ?>">
                    <?php echo esc_html(__('Add New Job Type','wp-job-portal')); ?>
                </a>
            </li>
        </ul>
    </li>
    <li class="treeview <?php if($wpjobportal_controller == 'wpjobportal_jobstatus') echo 'active'; ?>">
        <a href="admin.php?page=wpjobportal_jobstatus" title="<?php echo esc_attr(__('job status' , 'wp-job-portal')); ?>" class="wpjobportaladmin-menu-icon-class wpjobportaladmin-menu-icon-jobstatus">
            <span class="wpjobportaladmin-text">
                <?php echo esc_html(__('Job Status' , 'wp-job-portal')); ?>
            </span>
        </a>
        <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_jobstatus' && ($wpjobportal_layout == '')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_jobstatus" title="<?php echo esc_attr(__('job status','wp-job-portal')); ?>">
                    <?php echo esc_html(__('Job Status','wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_jobstatus' && ($wpjobportal_layout == 'formjobstatus')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_jobstatus&wpjobportallt=formjobstatus" title="<?php echo esc_attr(__('add new job status','wp-job-portal')); ?>">
                    <?php echo esc_html(__('Add New Job Status','wp-job-portal')); ?>
                </a>
            </li>
        </ul>
    </li>
    <?php /*<li class="treeview <?php if($wpjobportal_controller == 'wpjobportal_shift') echo 'active'; ?>">
        <a href="admin.php?page=wpjobportal_shift" title="<?php echo esc_attr(__('shifts' , 'wp-job-portal')); ?>">
            <img class="wpjobportaladmin-menu-icon" alt="<?php echo esc_attr(__('shifts' , 'wp-job-portal')); ?>" src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/control_panel/dashboard/admin-left-menu/job-shifts.png'; ?>" />
            <span class="wpjobportaladmin-text">
                <?php echo esc_html(__('Shifts' , 'wp-job-portal')); ?>
            </span>
        </a>
        <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_shift' && ($wpjobportal_layout == '')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_shift" title="<?php echo esc_attr(__('shifts','wp-job-portal')); ?>">
                    <?php echo esc_html(__('Shifts','wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_shift' && ($wpjobportal_layout == 'formshift')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_shift&wpjobportallt=formshift" title="<?php echo esc_attr(__('add new shift','wp-job-portal')); ?>">
                    <?php echo esc_html(__('Add New Shift','wp-job-portal')); ?>
                </a>
            </li>
        </ul>
    </li> */ ?>
    <li class="treeview <?php if($wpjobportal_controller == 'wpjobportal_highesteducation') echo 'active'; ?>">
        <a href="admin.php?page=wpjobportal_highesteducation" title="<?php echo esc_attr(__('highest educations' , 'wp-job-portal')); ?>" class="wpjobportaladmin-menu-icon-class wpjobportaladmin-menu-icon-highesteducation">
            <span class="wpjobportaladmin-text">
                <?php echo esc_html(__('Highest Educations' , 'wp-job-portal')); ?>
            </span>
        </a>
        <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_highesteducation' && ($wpjobportal_layout == '')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_highesteducation" title="<?php echo esc_attr(__('highest educations','wp-job-portal')); ?>">
                    <?php echo esc_html(__('Highest Educations','wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_highesteducation' && ($wpjobportal_layout == 'formhighesteducation')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_highesteducation&wpjobportallt=formhighesteducation" title="<?php echo esc_attr(__('add new highest education','wp-job-portal')); ?>">
                    <?php echo esc_html(__('Add New Highest Education','wp-job-portal')); ?>
                </a>
            </li>
        </ul>
    </li>
    <?php /*<li class="treeview <?php if($wpjobportal_controller == 'wpjobportal_age') echo 'active'; ?>">
        <a href="admin.php?page=wpjobportal_age" title="<?php echo esc_attr(__('ages' , 'wp-job-portal')); ?>">
            <img class="wpjobportaladmin-menu-icon" alt="<?php echo esc_attr(__('ages' , 'wp-job-portal')); ?>" src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/control_panel/dashboard/admin-left-menu/ages.png'; ?>" />
            <span class="wpjobportaladmin-text">
                <?php echo esc_html(__('Ages' , 'wp-job-portal')); ?>
            </span>
        </a>
        <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_age' && ($wpjobportal_layout == '')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_age" title="<?php echo esc_attr(__('ages','wp-job-portal')); ?>">
                    <?php echo esc_html(__('Ages','wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_age' && ($wpjobportal_layout == 'formages')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_age&wpjobportallt=formages" title="<?php echo esc_attr(__('add new age','wp-job-portal')); ?>">
                    <?php echo esc_html(__('Add New Age','wp-job-portal')); ?>
                </a>
            </li>
        </ul>
    </li> */ ?>
    <li class="treeview <?php if($wpjobportal_controller == 'wpjobportal_careerlevel') echo 'active'; ?>">
        <a href="admin.php?page=wpjobportal_careerlevel" title="<?php echo esc_attr(__('career levels' , 'wp-job-portal')); ?>" class="wpjobportaladmin-menu-icon-class wpjobportaladmin-menu-icon-careerlevels">
            <span class="wpjobportaladmin-text">
                <?php echo esc_html(__('Career Levels' , 'wp-job-portal')); ?>
            </span>
        </a>
        <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_careerlevel' && ($wpjobportal_layout == 'wpjobportal_careerlevel' || $wpjobportal_layout == '')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_careerlevel" title="<?php echo esc_attr(__('career levels','wp-job-portal')); ?>">
                    <?php echo esc_html(__('Career Levels','wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_careerlevel' && ($wpjobportal_layout == 'formcareerlevels')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_careerlevel&wpjobportallt=formcareerlevels" title="<?php echo esc_attr(__('add new career level','wp-job-portal')); ?>">
                    <?php echo esc_html(__('Add New Career Level','wp-job-portal')); ?>
                </a>
            </li>
        </ul>
    </li>
    <li class="treeview <?php if($wpjobportal_controller == 'wpjobportal_currency') echo 'active'; ?>">
        <a href="admin.php?page=wpjobportal_currency" title="<?php echo esc_attr(__('currency' , 'wp-job-portal')); ?>" class="wpjobportaladmin-menu-icon-class wpjobportaladmin-menu-icon-currencies">
            <span class="wpjobportaladmin-text">
                <?php echo esc_html(__('Currency' , 'wp-job-portal')); ?>
            </span>
        </a>
        <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_currency' && ($wpjobportal_layout == 'wpjobportal_currency' || $wpjobportal_layout == '')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_currency" title="<?php echo esc_attr(__('currency','wp-job-portal')); ?>">
                    <?php echo esc_html(__('Currency','wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_currency' && ($wpjobportal_layout == 'formcurrency')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_currency&wpjobportallt=formcurrency" title="<?php echo esc_attr(__('add new currency','wp-job-portal')); ?>">
                    <?php echo esc_html(__('Add New Currency','wp-job-portal')); ?>
                </a>
            </li>
        </ul>
    </li>
    <li class="treeview <?php if($wpjobportal_controller == 'wpjobportal_category') echo 'active'; ?>">
        <a href="admin.php?page=wpjobportal_category" title="<?php echo esc_attr(__('categories' , 'wp-job-portal')); ?>" class="wpjobportaladmin-menu-icon-class wpjobportaladmin-menu-icon-categories">
            <span class="wpjobportaladmin-text">
                <?php echo esc_html(__('Categories' , 'wp-job-portal')); ?>
            </span>
        </a>
        <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_category' && ($wpjobportal_layout == '')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_category" title="<?php echo esc_attr(__('categories', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Categories', 'wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_category' && ($wpjobportal_layout == 'formcategory')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_category&wpjobportallt=formcategory" title="<?php echo esc_attr(__('add new category', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Add New Category', 'wp-job-portal')); ?>
                </a>
            </li>
        </ul>
    </li>
    <?php
        if(in_array('tag', wpjobportal::$_active_addons)){
            do_action('wpjobportal_addons_admin_sidemenu_links_for_tags',$wpjobportal_controller,$wpjobportal_layout);
        }else{
            $wpjobportal_addoninfo = wpjobportal_checkWPJPPluginInfo('wp-job-portal-tag/wp-job-portal-tag.php');
            if($wpjobportal_addoninfo['availability'] == "1"){
                $wpjobportal_text = $wpjobportal_addoninfo['text'];
                $wpjobportal_url = "plugins.php?s=wp-job-portal-tag&plugin_status=inactive";
            }elseif($wpjobportal_addoninfo['availability'] == "0"){
                $wpjobportal_text = $wpjobportal_addoninfo['text'];
                $wpjobportal_url = "https://wpjobportal.com/product/tags/";
            } ?>
            <li class="treeview">
                <a href="javascript: void(0);" title="<?php echo esc_attr(__('Tags' , 'wp-job-portal')); ?>" class="wpjobportaladmin-menu-icon-class wpjobportaladmin-menu-icon-tags wpjobportaladmin-menu-icon-disabled">
                    <span class="wpjobportaladmin-text disabled-menu"><?php echo esc_html(__('Tags' , 'wp-job-portal')); ?></span>
                </a>
                <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
                    <li class="disabled-menu">
                        <span class="wpjobportaladmin-text"><?php echo esc_html(__('Tags' , 'wp-job-portal')); ?></span>
                        <a href="<?php echo esc_url($wpjobportal_url); ?>" class="wpjobportaladmin-install-btn" title="<?php echo esc_attr($wpjobportal_text); ?>"><?php echo esc_html($wpjobportal_text); ?></a>
                    </li>
                </ul>
            </li>
    <?php } ?>
    <li class="treeview <?php if($wpjobportal_controller == 'wpjobportal_salaryrange' || $wpjobportal_controller == 'wpjobportal_salaryrangetype' ) echo 'active'; ?>">
        <a href="admin.php?page=wpjobportal_salaryrangetype" title="<?php echo esc_attr(__('salary range' , 'wp-job-portal')); ?>" class="wpjobportaladmin-menu-icon-class wpjobportaladmin-menu-icon-salaryrange">
            <span class="wpjobportaladmin-text">
                <?php echo esc_html(__('Salary Range' , 'wp-job-portal')); ?>
            </span>
        </a>
        <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_salaryrangetype' && ($wpjobportal_layout == '')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_salaryrangetype" title="<?php echo esc_attr(__('salary range type', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Salary Range Type', 'wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_salaryrangetype' && ($wpjobportal_layout == 'formsalaryrangetype')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_salaryrangetype&wpjobportallt=formsalaryrangetype" title="<?php echo esc_attr(__('add new salary range type', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Add New Salary Range Type', 'wp-job-portal')); ?>
                </a>
            </li>
        </ul>
    </li>
    <li class="treeview <?php if($wpjobportal_controller == 'wpjobportal_user' || $wpjobportal_controller == 'wpjobportal_customfield' || ($wpjobportal_controller == 'wpjobportal_fieldordering' && ($wpjobportal_layout == '') && $wpjobportal_fieldfor == 4)) echo 'active'; ?>">
        <a href="admin.php?page=wpjobportal_user" title="<?php echo esc_attr(__('users' , 'wp-job-portal')); ?>" class="wpjobportaladmin-menu-icon-class wpjobportaladmin-menu-icon-users">
            <span class="wpjobportaladmin-text">
                <?php echo esc_html(__('Users' , 'wp-job-portal')); ?>
            </span>
        </a>
        <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_user' && ($wpjobportal_layout == '')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_user" title="<?php echo esc_attr(__('users', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Users', 'wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_user' && ($wpjobportal_layout == 'assignrole')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_user&wpjobportallt=assignrole" title="<?php echo esc_attr(__('assign role', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Assign Role', 'wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_fieldordering' || $wpjobportal_controller == 'wpjobportal_customfield'  && ($wpjobportal_layout == '' || $wpjobportal_layout == 'formuserfield')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_fieldordering&ff=4" title="<?php echo esc_attr(__('fields', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Fields', 'wp-job-portal')); ?>
                </a>
            </li>
        </ul>
    </li>
    <li class="treeview <?php if($wpjobportal_controller == 'wpjobportal_emailtemplate' || $wpjobportal_controller == 'wpjobportal_emailtemplatestatus') echo 'active'; ?>">
        <a href="admin.php?page=wpjobportal_emailtemplate" title="<?php echo esc_attr(__('email templates' , 'wp-job-portal')); ?>" class="wpjobportaladmin-menu-icon-class wpjobportaladmin-menu-icon-emials">
            <span class="wpjobportaladmin-text">
                <?php echo esc_html(__('Email Templates' , 'wp-job-portal')); ?>
            </span>
        </a>
        <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_emailtemplatestatus' && ($wpjobportal_layout == '')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_emailtemplatestatus" title="<?php echo esc_attr(__('options', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Options', 'wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_emailtemplate' && $wpjobportal_for == 'ew-cm') echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_emailtemplate&for=ew-cm" title="<?php echo esc_attr(__('new company', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('New Company', 'wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_emailtemplate' && $wpjobportal_for == 'd-cm') echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_emailtemplate&for=d-cm" title="<?php echo esc_attr(__('delete company', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Delete Company', 'wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_emailtemplate' && $wpjobportal_for == 'cm-sts') echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_emailtemplate&for=cm-sts" title="<?php echo esc_attr(__('company status', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Company Status', 'wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_emailtemplate' && $wpjobportal_for == 'ew-ob') echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_emailtemplate&for=ew-ob" title="<?php echo esc_attr(__('new job', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('New Job', 'wp-job-portal')); ?>
                </a>
            </li>
            <?php if(in_array('visitorcanaddjob', wpjobportal::$_active_addons)){ ?>
                <li class="<?php if($wpjobportal_controller == 'wpjobportal_emailtemplate' && $wpjobportal_for == 'ew-obv') echo 'active'; ?>">
                    <a href="admin.php?page=wpjobportal_emailtemplate&for=ew-obv" title="<?php echo esc_attr(__('new visitor job', 'wp-job-portal')); ?>">
                        <?php echo esc_html(__('New Visitor Job', 'wp-job-portal')); ?>
                    </a>
                </li>
            <?php } ?>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_emailtemplate' && $wpjobportal_for == 'ob-sts') echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_emailtemplate&for=ob-sts" title="<?php echo esc_attr(__('job status', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Job Status', 'wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_emailtemplate' && $wpjobportal_for == 'ob-d') echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_emailtemplate&for=ob-d" title="<?php echo esc_attr(__('job delete', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Job Delete', 'wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_emailtemplate' && $wpjobportal_for == 'ew-rm') echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_emailtemplate&for=ew-rm" title="<?php echo esc_attr(__('new resume', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('New Resume', 'wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_emailtemplate' && $wpjobportal_for == 'ew-rmv') echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_emailtemplate&for=ew-rmv" title="<?php echo esc_attr(__('new visitor resume', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('New Visitor Resume', 'wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_emailtemplate' && $wpjobportal_for == 'rm-sts') echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_emailtemplate&for=rm-sts" title="<?php echo esc_attr(__('resume status', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Resume Status', 'wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_emailtemplate' && $wpjobportal_for == 'd-rs') echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_emailtemplate&for=d-rs" title="<?php echo esc_attr(__('delete resume', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Delete Resume', 'wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_emailtemplate' && $wpjobportal_for == 'em-n') echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_emailtemplate&for=em-n" title="<?php echo esc_attr(__('new employer', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('New Employer', 'wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_emailtemplate' && $wpjobportal_for == 'obs-n') echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_emailtemplate&for=obs-n" title="<?php echo esc_attr(__('new job seeker', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('New Job Seeker', 'wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_emailtemplate' && $wpjobportal_for == 'ad-jap') echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_emailtemplate&for=ad-jap" title="<?php echo esc_attr(__('job apply admin', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Job Apply Admin', 'wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_emailtemplate' && $wpjobportal_for == 'em-jap') echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_emailtemplate&for=em-jap" title="<?php echo esc_attr(__('job apply employer', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Job Apply Employer', 'wp-job-portal')); ?>
                </a>
            </li>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_emailtemplate' && $wpjobportal_for == 'js-jap') echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_emailtemplate&for=js-jap" title="<?php echo esc_attr(__('job apply job seeker', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Job Apply Job Seeker', 'wp-job-portal')); ?>
                </a>
            </li>
             <?php if(in_array('resumeaction', wpjobportal::$_active_addons)){ ?>
                <li class="<?php if($wpjobportal_controller == 'wpjobportal_emailtemplate' && $wpjobportal_for == 'ap-jap') echo 'active'; ?>">
                    <a href="admin.php?page=wpjobportal_emailtemplate&for=ap-jap" title="<?php echo esc_attr(__('applied resume status change', 'wp-job-portal')); ?>">
                        <?php echo esc_html(__('Applied Resume Status Change', 'wp-job-portal')); ?>
                    </a>
                </li>
            <?php } ?>

             <?php if(in_array('message', wpjobportal::$_active_addons)){ ?>
                <li class="<?php if($wpjobportal_controller == 'wpjobportal_emailtemplate' && $wpjobportal_for == 'new-msg') echo 'active'; ?>">
                    <a href="admin.php?page=wpjobportal_emailtemplate&for=new-msg" title="<?php echo esc_attr(__('new message alert', 'wp-job-portal')); ?>">
                        <?php echo esc_html(__('New Message Alert', 'wp-job-portal')); ?>
                    </a>
                </li>
            <?php } ?>

             <?php if(in_array('jobalert', wpjobportal::$_active_addons)){ ?>
                    <li class="<?php if($wpjobportal_controller == 'wpjobportal_emailtemplate' && $wpjobportal_for == 'jb-at') echo 'active'; ?>">
                        <a href="admin.php?page=wpjobportal_emailtemplate&for=jb-at" title="<?php echo esc_attr(__('job alert', 'wp-job-portal')); ?>">
                            <?php echo esc_html(__('Job Alert', 'wp-job-portal')); ?>
                        </a>
                    </li>
            <?php } ?>
            <?php if(in_array('tellfriend', wpjobportal::$_active_addons)){ ?>
            <li class="<?php if($wpjobportal_controller == 'wpjobportal_emailtemplate' && $wpjobportal_for == 'jb-to-fri') echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal_emailtemplate&for=jb-to-fri" title="<?php echo esc_attr(__('tell to friend', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Tell To Friend', 'wp-job-portal')); ?>
                </a>
            </li>
            <?php } ?>

                <?php if(in_array('credits', wpjobportal::$_active_addons)){ ?>
                        <li class="<?php if($wpjobportal_controller == 'wpjobportal_emailtemplate' && $wpjobportal_for == 'ew-pk-ad') echo 'active'; ?>">
                            <a href="admin.php?page=wpjobportal_emailtemplate&for=ew-pk-ad" title="<?php echo esc_attr(__('Purchase Package Admin', 'wp-job-portal')); ?>">
                                <?php echo esc_html(__('Purchase Package Admin', 'wp-job-portal')); ?>
                            </a>
                        </li>
                        <li class="<?php if($wpjobportal_controller == 'wpjobportal_emailtemplate' && $wpjobportal_for == 'ew-pk') echo 'active'; ?>">
                            <a href="admin.php?page=wpjobportal_emailtemplate&for=ew-pk" title="<?php echo esc_attr(__('Purchase Package Admin', 'wp-job-portal')); ?>">
                                <?php echo esc_html(__('Purchase Package', 'wp-job-portal')); ?>
                            </a>
                        </li>
                        <li class="<?php if($wpjobportal_controller == 'wpjobportal_emailtemplate' && $wpjobportal_for == 'st-pk') echo 'active'; ?>">
                            <a href="admin.php?page=wpjobportal_emailtemplate&for=st-pk" title="<?php echo esc_attr(__('Purchase Package Admin', 'wp-job-portal')); ?>">
                                <?php echo esc_html(__('Purchase Status', 'wp-job-portal')); ?>
                            </a>
                        </li>
                <?php } ?>
        </ul>
    </li>
    <li class="treeview <?php if($wpjobportal_controller == 'wpjobportal' && $wpjobportal_layout == 'help' ) echo 'active'; ?>">
        <a href="admin.php?page=wpjobportal&wpjobportallt=help" title="<?php echo esc_attr(__('help' , 'wp-job-portal')); ?> " class="wpjobportaladmin-menu-icon-class wpjobportaladmin-menu-icon-help">
            <span class="wpjobportaladmin-text">
                <?php echo esc_html(__('Help' , 'wp-job-portal')); ?>
            </span>
        </a>
        <ul class="wpjobportaladmin-sidebar-submenu treeview-menu">
            <li class="<?php if($wpjobportal_controller == 'wpjobportal' && ($wpjobportal_layout == 'help')) echo 'active'; ?>">
                <a href="admin.php?page=wpjobportal&wpjobportallt=help" title="<?php echo esc_attr(__('help', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Help', 'wp-job-portal')); ?>
                </a>
            </li>
        </ul>
    </li>
</ul>
<?php
$wpjobportal_menu_js_script = '
    var cookielist = document.cookie.split(";");
    for (var i=0; i<cookielist.length; i++) {
        if (cookielist[i].trim() == "wpjobportaladmin_collapse_admin_menu=1") {
            jQuery("#wpjobportaladmin-wrapper").addClass("menu-collasped-active");
            break;
        }
    }

    jQuery(document).ready(function(){

        var pageWrapper = jQuery("#wpjobportaladmin-wrapper");
        var sideMenuArea = jQuery("#wpjobportaladmin-leftmenu");

        jQuery("#wpjobportaladmin-menu-toggle").on("click", function () {

            if (pageWrapper.hasClass("menu-collasped-active")) {
                pageWrapper.removeClass("menu-collasped-active");
                document.cookie = "wpjobportaladmin_collapse_admin_menu=0; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/";
            }else{
                pageWrapper.addClass("menu-collasped-active");
                document.cookie = "wpjobportaladmin_collapse_admin_menu=1; expires=Sat, 01 Jan 2050 00:00:00 UTC; path=/";
            }

        });

        // to set anchor link active on menu collpapsed
        jQuery(".wpjobportaladmin-sidebar-menu li.treeview a").on("click", function() {
            if (!(pageWrapper.hasClass("menu-collasped-active"))) {
                window.location.href = jQuery(this).attr("href");
            }
        });
    });

    ';
wp_add_inline_script( 'wpjobportal-menu-handle', $wpjobportal_menu_js_script );
?>
