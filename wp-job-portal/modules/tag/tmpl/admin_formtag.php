<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<script >
    jQuery(document).ready(function ($) {
        $.validate();
    });
</script>
<!-- main wrapper -->
<div id="wpjobportaladmin-wrapper" class="wjpl-page">
    <!-- left menu -->
    <div id="wpjobportaladmin-leftmenu">
	   <?php WPJOBPORTALincluder::getTemplate('templates/admin/leftmenue',array('wpjobportal_module' => 'tag')); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <?php WPJOBPORTAL_Admin_Form_UI::simple_header(__('Tag', 'wp-job-portal'), __('tag', 'wp-job-portal'), 'wpjobportal_tag', 'wpjobportal-form'); ?>
        <!-- page content -->
        <div id="wpjobportal-admin-wrapper">
            <form id="wpjobportal-form" class="wpjobportal-form wjpl-form wjpl-form-simple" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=wpjobportal_tag&task=savetag&action=wpjobportaltask"),'wpjobportal_tag_nonce')); ?>"><?php WPJOBPORTAL_Admin_Form_UI::simple_open(); ?>
               <?php WPJOBPORTALincluder::getTemplate('views/form-field',array('module_name' => 'tag')); ?>
            <?php WPJOBPORTAL_Admin_Form_UI::simple_close(); ?></form>
        </div>
    </div>
</div>
