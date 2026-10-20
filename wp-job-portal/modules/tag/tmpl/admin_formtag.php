<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<script >
    jQuery(document).ready(function ($) {
        $.validate();
    });
</script>
<!-- main wrapper -->
<div id="wpjobportaladmin-wrapper">
    <!-- left menu -->
    <div id="wpjobportaladmin-leftmenu">
	   <?php WPJOBPORTALincluder::getTemplate('templates/admin/leftmenue',array('wpjobportal_module' => 'tag')); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <!-- top bar -->
        <div id="wpjobportal-wrapper-top">
            <div id="wpjobportal-wrapper-top-left">
                <div id="wpjobportal-breadcrumbs">
                    <ul>
                        <li>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal')); ?>" title="<?php echo esc_attr__('Dashboard','wp-job-portal'); ?>">
                                <?php echo esc_html__('Dashboard','wp-job-portal'); ?>
                            </a>
                        </li>
                        <li><?php echo esc_html__('Add New Tag','wp-job-portal'); ?></li>
                    </ul>
                </div>
            </div>
            <div id="wpjobportal-wrapper-top-right">
                <div id="wpjobportal-config-btn">
                    <a href="admin.php?page=wpjobportal_configuration" title="<?php echo esc_attr__('Configuration','wp-job-portal'); ?>">
                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL . 'includes/images/control_panel/dashboard/config.png'); ?>">
                   </a>
                </div>
                <div id="wpjobportal-help-btn" class="wpjobportal-help-btn">
                    <a href="admin.php?page=wpjobportal&wpjobportallt=help" title="<?php echo esc_attr__('Help','wp-job-portal'); ?>">
                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL . 'includes/images/control_panel/dashboard/help.png'); ?>">
                   </a>
                </div>
                <div id="wpjobportal-vers-txt">
                    <?php echo esc_html__('Version','wp-job-portal').': '; ?>
                    <span class="wpjobportal-ver"><?php echo esc_html(WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue('versioncode')); ?></span>
                </div>
            </div>
        </div>
        <!-- top head -->
        <?php
            $wpjobportal_heading = isset(wpjobportal::$_data[0]) ? __('Edit', 'wp-job-portal') : __('Add New', 'wp-job-portal');
            WPJOBPORTALincluder::getTemplate('templates/admin/pagetitle',array('wpjobportal_module' => 'tag','wpjobportal_layouts' => 'tag','wpjobportal_heading' => $wpjobportal_heading));
        ?>
        <!-- page content -->
        <div id="wpjobportal-admin-wrapper">
            <form id="wpjobportal-form" class="wpjobportal-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=wpjobportal_tag&task=savetag&action=wpjobportaltask"),'wpjobportal_tag_nonce')); ?>">
               <?php WPJOBPORTALincluder::getTemplate('views/form-field',array('module_name' => 'tag')); ?>
            </form>
        </div>
    </div>
</div>
