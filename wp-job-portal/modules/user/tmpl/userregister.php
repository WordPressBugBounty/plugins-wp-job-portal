<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
?>
<?php
$wpjobportal_shortcode_options = isset(wpjobportal::$_data['shortcode_options']) && is_array(wpjobportal::$_data['shortcode_options'])
    ? wpjobportal::$_data['shortcode_options']
    : array();
$wpjobportal_show_header = !isset($wpjobportal_shortcode_options['show_header']) || !empty($wpjobportal_shortcode_options['show_header']);
$wpjobportal_show_title = !isset($wpjobportal_shortcode_options['show_title']) || !empty($wpjobportal_shortcode_options['show_title']);
$wpjobportal_custom_class = !empty($wpjobportal_shortcode_options['class']) ? ' ' . $wpjobportal_shortcode_options['class'] : '';
?>
<div class="wjportal-main-up-wrapper <?php echo esc_attr($wpjobportal_custom_class); ?>">
<?php
// Messages and errors always; show_header is the trail, show_title the heading.
if (!WPJOBPORTALincluder::getTemplate('templates/header',array('wpjobportal_module' => 'user'))){
    return ;
}
if (!is_user_logged_in()) {
    // WP Job Portal's own sign-up setting, which by default follows
    // WordPress's "Anyone can register"; see WPJOBPORTAL_Registration_Policy.
    $wpjobportal_is_enable = WPJOBPORTAL_Registration_Policy::is_open();
    // only show the registration form if allowed
    if ($wpjobportal_is_enable) { ?>
        <div class="wjportal-main-wrapper wjportal-clearfix">
            <?php if ($wpjobportal_show_title || $wpjobportal_show_header) { ?>
                <div class="wjportal-page-header">
                    <?php
                        WPJOBPORTALincluder::getTemplate('templates/pagetitle',array(
                            'wpjobportal_module' => 'user',
                            'wpjobportal_layout' => 'reg',
                            'wpjobportal_hide_heading' => !$wpjobportal_show_title,
                            'wpjobportal_hide_breadcrumbs' => !$wpjobportal_show_header,
                        ));
                    ?>
                </div>
            <?php } ?>
            <!-- registration form fields -->
            <div class="wjportal-form-wrp wjportal-register-form">
                <?php
                    // show any error messages after form submission
                    wpjobportal_show_error_messages();
                ?>
                <form id="wpjobportal_registration_form" class="wjportal-form wpjp-ds-form" action="" method="POST" enctype="multipart/form-data">
                    <?php WPJOBPORTALincluder::getTemplate('user/views/frontend/form-field'); ?>
                </form>
            </div>
        </div>
        <?php
    } else {
        WPJOBPORTALlayout::getRegistrationDisabled();
    }
}
?>
</div>
