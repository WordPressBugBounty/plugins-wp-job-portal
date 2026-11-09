<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
$wpjobportal_role = wpjobportal::$_data[0];
?>
<!-- main wrapper -->
<div id="wpjobportaladmin-wrapper" class="wjpl-page">
	<!-- left menu -->
	<div id="wpjobportaladmin-leftmenu">
		<?php WPJOBPORTALincluder::getTemplate('templates/admin/leftmenue',array('wpjobportal_module' => 'user')); ?>
	</div>
    <div id="wpjobportaladmin-data">
        <?php WPJOBPORTAL_Admin_Form_UI::header(__('Change role', 'wp-job-portal'), __("Changing a user's role will not remove its entities from the system !", 'wp-job-portal'), __('Role details', 'wp-job-portal'), 'wpjobportal-form'); ?>
        <!-- page content -->
        <div id="wpjobportal-admin-wrapper">
		    <form id="wpjobportal-form" class="wpjobportal-form wjpl-form wjpl-form-simple" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=wpjobportal_user&task=saveuserrole"),'wpjobportal_user_nonce')); ?>"><?php WPJOBPORTAL_Admin_Form_UI::simple_open(); ?>
		       <?php WPJOBPORTALincluder::getTemplate('user/views/admin/form-field',array('wpjobportal_role' => $wpjobportal_role)); ?>
		    <?php WPJOBPORTAL_Admin_Form_UI::simple_close(); ?></form>
	    </div>
	</div>
</div>
