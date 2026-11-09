<?php 
    if (!defined('ABSPATH')) die('Restricted Access'); 
    if(!WPJOBPORTALincluder::getTemplate('templates/admin/header',array('wpjobportal_module' => 'user'))){
        return ;
    }
?>
<!-- main wrapper -->
<div id="wpjobportaladmin-wrapper" class="wjpl-page">
    <!-- left menu -->
    <div id="wpjobportaladmin-leftmenu">
        <?php  WPJOBPORTALincluder::getTemplate('templates/admin/leftmenue',array('wpjobportal_module' => 'user')); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <!-- top head -->
        <?php ob_start(); ?>
                <?php echo esc_html(__('User Details', 'wp-job-portal')) ?>
            <?php WPJOBPORTAL_Admin_List_UI::page_head(trim(html_entity_decode(wp_strip_all_tags(ob_get_clean()), ENT_QUOTES))); ?>
        <!-- page content -->
        <div id="wpjobportal-admin-wrapper">
            <?php
            $wpjobportal_token = WPJOBPORTALincluder::getJSModel('common')->getUniqueIdForTransient();
            $wpjobportal_transient_val = get_transient('current_user_token_user_'.$wpjobportal_token);
            if(!empty($wpjobportal_transient_val)){
                ?>
                <div class="wpjobportal-admin--backlink-wrap">
                    <a id="form-back-button" class="wpjobportal-form-back-btn" href="<?php echo esc_url_raw(admin_url('admin.php?page=wpjobportal_user&wpjobportal_restore_results='.$wpjobportal_token)); ?>" title="<?php echo esc_attr(__('Back To Listing', 'wp-job-portal')); ?>">
                        <?php echo esc_html(__('Back To Listing', 'wp-job-portal')); ?>
                    </a>
                </div>
            <?php }?>
            <?php
                if (!empty(wpjobportal::$_data[0])) { 
                    $wpjobportal_user = wpjobportal::$_data[0];
                    WPJOBPORTALincluder::getTemplate('user/views/admin/user-detail',array('wpjobportal_user' => $wpjobportal_user));
                
                } else {
                    $wpjobportal_msg = esc_html(__('No Records Found','wp-job-portal'));
                    WPJOBPORTALlayout::getNoRecordFound($wpjobportal_msg);
                }
            ?>
        </div>
    </div>
</div>
