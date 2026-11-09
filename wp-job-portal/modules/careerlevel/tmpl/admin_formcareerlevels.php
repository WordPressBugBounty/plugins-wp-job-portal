<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
wp_register_script( 'wpjobportal-inline-handle', '', array(), WPJOBPORTAL_VERSION, false );
wp_enqueue_script( 'wpjobportal-inline-handle' );

$wpjobportal_inline_js_script = "
    jQuery(document).ready(function ($) {
        $.validate();
    });
    ";
wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
?>
<!-- main wrapper -->
<div id="wpjobportaladmin-wrapper" class="wjpl-page">
	<!-- left menu -->
    <div id="wpjobportaladmin-leftmenu">
	 	<?php  WPJOBPORTALincluder::getTemplate('templates/admin/leftmenue',array('wpjobportal_module' => 'careerlevel')); ?>
 	</div>
    <div id="wpjobportaladmin-data">
        <?php WPJOBPORTAL_Admin_Form_UI::simple_header(__('Career Level', 'wp-job-portal'), __('career level', 'wp-job-portal'), 'wpjobportal_careerlevel', 'wpjobportal-form'); ?>
	    <!-- page content -->
        <div id="wpjobportal-admin-wrapper">
		    <form id="wpjobportal-form" class="wpjobportal-form wjpl-form wjpl-form-simple" method="post" action="<?php echo esc_url_raw(admin_url("admin.php?page=wpjobportal_careerlevel&task=savecareerlevel")); ?>"><?php WPJOBPORTAL_Admin_Form_UI::simple_open(); ?>
		        <?php WPJOBPORTALincluder::getTemplate('careerlevel/views/form-field'); ?>
		    <?php WPJOBPORTAL_Admin_Form_UI::simple_close(); ?></form>
	    </div>
	</div>
</div>
