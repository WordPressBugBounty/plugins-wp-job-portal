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
if ($wpjobportal_show_header && !WPJOBPORTALincluder::getTemplate('templates/header',array('wpjobportal_module' => 'user'))){
    return ;
}
if (!is_user_logged_in()) {
    // check to make sure user registration is enabled
    $wpjobportal_is_enable = get_option('users_can_register');
    // only show the registration form if allowed
    if ($wpjobportal_is_enable) { ?>
        <div class="wjportal-main-wrapper wjportal-clearfix">
            <?php if ($wpjobportal_show_title) { ?>
                <div class="wjportal-page-header">
                    <?php
                        WPJOBPORTALincluder::getTemplate('templates/pagetitle',array('wpjobportal_module' => 'user','wpjobportal_layout'=>'reg'));
                    ?>
                </div>
            <?php } ?>
            <!-- registration form fields -->
            <div class="wjportal-form-wrp wjportal-register-form">
                <?php
                    // show any error messages after form submission
                    wpjobportal_show_error_messages();
                ?>
                <form id="wpjobportal_registration_form" class="wjportal-form" action="" method="POST" enctype="multipart/form-data">
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
<?php
    $wpjobportal_protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $wpjobportal_config_array = wpjobportal::$_config->getConfigByFor('captcha');
    if($wpjobportal_config_array['captcha_selection'] == 1 && $wpjobportal_config_array['recaptcha_privatekey'] ){
        wp_enqueue_script('wpjobportal-repaptcha-scripti', $wpjobportal_protocol . 'www.google.com/recaptcha/api.js');
    }
?>
</div>
