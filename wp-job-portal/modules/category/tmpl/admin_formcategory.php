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
		<?php  WPJOBPORTALincluder::getTemplate('templates/admin/leftmenue',array('wpjobportal_module' => 'categories')); ?>
	</div>
    <div id="wpjobportaladmin-data">
        <?php WPJOBPORTAL_Admin_Form_UI::simple_header(__('Category', 'wp-job-portal'), __('category', 'wp-job-portal'), 'wpjobportal_category', 'wpjobportal-form'); ?>
        <!-- page content -->
        <div id="wpjobportal-admin-wrapper">
            <?php
            $wpjobportal_token = WPJOBPORTALincluder::getJSModel('common')->getUniqueIdForTransient();
            $wpjobportal_transient_val = get_transient('current_user_token_category_'.$wpjobportal_token);
            if(!empty($wpjobportal_transient_val)){
                ?>
                <div class="wpjobportal-admin--backlink-wrap">
                    <a id="form-back-button" class="wpjobportal-form-back-btn" href="<?php echo esc_url_raw(admin_url('admin.php?page=wpjobportal_category&wpjobportal_restore_results='.$wpjobportal_token)); ?>" title="<?php echo esc_attr(__('Back To Listing', 'wp-job-portal')); ?>">
                        <?php echo esc_html(__('Back To Listing', 'wp-job-portal')); ?>
                    </a>
                </div>
            <?php }?>
		    <form id="wpjobportal-form" class="wpjobportal-form wjpl-form wjpl-form-simple" method="post" action="<?php echo esc_url_raw(admin_url("admin.php?page=wpjobportal_category&task=savecategory")); ?>"><?php WPJOBPORTAL_Admin_Form_UI::simple_open(); ?>
		        <?php WPJOBPORTALincluder::getTemplate('category/views/form-field'); ?>
		    <?php WPJOBPORTAL_Admin_Form_UI::simple_close(); ?></form>
	    </div>
	</div>
</div>
