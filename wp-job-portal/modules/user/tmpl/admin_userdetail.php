<?php 
    if (!defined('ABSPATH')) die('Restricted Access'); 
    if(!WPJOBPORTALincluder::getTemplate('templates/admin/header',array('wpjobportal_module' => 'user'))){
        return ;
    }
?>
<!-- main wrapper -->
<div id="wpjobportaladmin-wrapper">
    <!-- left menu -->
    <div id="wpjobportaladmin-leftmenu">
        <?php  WPJOBPORTALincluder::getTemplate('templates/admin/leftmenue',array('wpjobportal_module' => 'user')); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <!-- top bar -->
        <div id="wpjobportal-wrapper-top">
            <div id="wpjobportal-wrapper-top-left">
                <div id="wpjobportal-breadcrumbs">
                    <ul>
                        <li>
                            <a href="<?php echo esc_url_raw(admin_url('admin.php?page=wpjobportal')); ?>" title="<?php echo esc_attr(__('dashboard','wp-job-portal')); ?>">
                                <?php echo esc_html(__('Dashboard','wp-job-portal')); ?>
                            </a>
                        </li>
                        <li><?php echo esc_html(__('User Details','wp-job-portal')); ?></li>
                    </ul>
                </div>
            </div>    
            <div id="wpjobportal-wrapper-top-right">
                <div id="wpjobportal-config-btn">
                    <a href="admin.php?page=wpjobportal_configuration" title="<?php echo esc_attr(__('configuration','wp-job-portal')); ?>">
                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/config.png">
                   </a>
                </div>
                <div id="wpjobportal-help-btn" class="wpjobportal-help-btn">
                    <a href="admin.php?page=wpjobportal&wpjobportallt=help" title="<?php echo esc_attr(__('help','wp-job-portal')); ?>">
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
        <div id="wpjobportal-head">
            <h1 class="wpjobportal-head-text">
                <?php echo esc_html(__('User Details', 'wp-job-portal')) ?>
            </h1>
        </div>
        <!-- page content -->
        <div id="wpjobportal-admin-wrapper">
            <?php
            $wpjobportal_token = WPJOBPORTALincluder::getJSModel('common')->getUniqueIdForTransient();
            $wpjobportal_transient_val = get_transient('current_user_token_user_'.$wpjobportal_token);
            if(!empty($wpjobportal_transient_val)){
                ?>
                <div class="wpjobportal-admin--backlink-wrap">
                    <a id="form-back-button" class="wpjobportal-form-back-btn" href="<?php echo esc_url_raw(admin_url('admin.php?page=wpjobportal_user&wpjobportal_restore_results='.$wpjobportal_token)); ?>" title="<?php echo esc_attr(__('Back to listing', 'wp-job-portal')); ?>">
                        <?php echo esc_html(__('Back to listing', 'wp-job-portal')); ?>
                    </a>
                </div>
            <?php }?>
            <?php
                if (!empty(wpjobportal::$_data[0])) { 
                    $wpjobportal_user = wpjobportal::$_data[0];
                    WPJOBPORTALincluder::getTemplate('user/views/admin/user-detail',array('wpjobportal_user' => $wpjobportal_user));
                
                } else {
                    $wpjobportal_msg = esc_html(__('No record found','wp-job-portal'));
                    WPJOBPORTALlayout::getNoRecordFound($wpjobportal_msg);
                }
            ?>
        </div>
    </div>
</div>
