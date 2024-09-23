<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param job      job object - optional
*/

// code to manage width of info section and show hide apply form


$show_apply_form  = wpjobportal::$_config->getConfigurationByConfigName('quick_apply_for_user');
if (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
    $show_apply_form  = wpjobportal::$_config->getConfigurationByConfigName('quick_apply_for_visitor');
}

$captcha_quick_apply  = wpjobportal::$_config->getConfigurationByConfigName('quick_apply_captcha');

$extra_cls_apply_form = '';
if($show_apply_form == 1){
    $extra_cls_apply_form = 'wjportal-view-job-page-job-info-wraper-with-apply-form';
}
?>
<div class="wjportal-main-wrapper wjportal-clearfix">
    <div class="wjportal-view-job-page-wrapper" >
        <div class="wjportal-view-job-page-job-info-wraper <?php echo esc_attr($extra_cls_apply_form); ?>" >
            <?php
                WPJOBPORTALincluder::getTemplate('job/views/frontend/jobtitle', array(
                    'job'       =>  $job ,
                    'jobfields'  =>  $jobfields
                ));
            ?>
        </div>
        <?php if($show_apply_form == 1){

         ?>
         <?php
         wp_register_script( 'wpjobportal-inline-handle', '' );
         wp_enqueue_script( 'wpjobportal-inline-handle' );

         $inline_js_script = "
             jQuery(document).ready(function ($) {
                 $.validate();
             });
             ";
         wp_add_inline_script( 'wpjobportal-inline-handle', $inline_js_script );
         ?>
            <div class="wjportal-view-job-page-job-apply-form-wraper" >
                <?php  //do_action('wpjobportal_addons_quick_apply_form');
                //  check if already applied
                    // if (!WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
                    //     $uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
                    //     $already_applied = WPJOBPORTALincluder::getJSModel('jobapply')->checkAlreadyAppliedJob(wpjobportal::$_data[0]->id,$uid);
                    //     if(!$already_applied){ //show already applied message
                    //         echo '<div class="frontend error"><p>'.esc_html(__('You have already applied on this job.', 'wp-job-portal')).'</p></div>';
                    //     }
                    // }
                    $formfields = WPJOBPORTALincluder::getTemplate('quickapply/form-fields',array());
                    echo '<div class="wjportal-form-wrp wpjobportal-quickapply-form" >';
                        echo '<form class="wjportal-form" id="wpjobportal-form" method="post" enctype="multipart/form-data" action="'. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'quickapply', 'task'=>'quickapplyonjob'))).'">';
                            foreach ($formfields as $formfield) {
                                WPJOBPORTALincluder::getTemplate('templates/form-field', $formfield);
                            }
                            $google_recaptcha_3 = false;
                            $config_array = wpjobportal::$_config->getConfigByFor('captcha');
                            if (!is_user_logged_in() && $captcha_quick_apply == 1) {
                                if ($config_array['captcha_selection'] == 1) { // Google recaptcha
                                    if($config_array['recaptcha_version'] == 1){
                                        echo '<div class="g-recaptcha" data-sitekey="'.$config_array["recaptcha_publickey"].'"></div>';
                                    }else{
                                        $google_recaptcha_3 = true;
                                    }

                                } else { // own captcha
                                    $captcha = new WPJOBPORTALcaptcha;
                                    echo '<div class="recaptcha-wrp">'.$captcha->getCaptchaForForm().'</div>';
                                }
                            }
                            $hide_apply_btn = 0;
                            if (WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()) {
                                $uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
                                $already_applied = WPJOBPORTALincluder::getJSModel('jobapply')->checkAlreadyAppliedJob(wpjobportal::$_data[0]->id,$uid);
                                if(!$already_applied){ //show already applied message
                                    echo '<div class="frontend error"><p>'.esc_html(__('You have already applied on this job.', 'wp-job-portal')).'</p></div>';
                                    $hide_apply_btn = 1;
                                }
                            }elseif (WPJOBPORTALincluder::getObjectClass('user')->isemployer()) {
                                echo '<div class="frontend error"><p>'.esc_html(__('You are logged in as employer !', 'wp-job-portal')).'</p></div>';
                                $hide_apply_btn = 1;

                            }
                            if($hide_apply_btn == 0){
                                echo '<div class="wjportal-form-btn-wrp">
                                    '. wp_kses(WPJOBPORTALformfield::submitbutton('save', esc_html(__('Apply Now', 'wp-job-portal')), array('class' => 'button wjportal-form-btn wjportal-save-btn')),WPJOBPORTAL_ALLOWED_TAGS).'
                                </div>';
                            }
                            echo wp_kses(WPJOBPORTALformfield::hidden('wpjobportalpageid', wpjobportal::wpjobportal_getPageid()),WPJOBPORTAL_ALLOWED_TAGS);
                            echo wp_kses(WPJOBPORTALformfield::hidden('jobid', (isset(wpjobportal::$_data[0]) && !empty(wpjobportal::$_data[0])) ? wpjobportal::$_data[0]->id: '' ),WPJOBPORTAL_ALLOWED_TAGS);
                            echo wp_kses(WPJOBPORTALformfield::hidden('form_request', 'wpjobportal'),WPJOBPORTAL_ALLOWED_TAGS);
                            echo wp_kses(WPJOBPORTALformfield::hidden('_wpnonce', esc_html(wp_create_nonce('wpjobportal_quick_apply_nonce'))),WPJOBPORTAL_ALLOWED_TAGS);
                        echo '</form>';
                    echo '</div>';

                ?>
            </div>

        <?php
        if($captcha_quick_apply == 1){
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $config_array = wpjobportal::$_config->getConfigByFor('captcha');
            if($config_array['captcha_selection'] == 1 && $config_array['recaptcha_privatekey'] ){
                wp_enqueue_script('wpjobportal-repaptcha-scripti', $protocol . 'www.google.com/recaptcha/api.js');
            }
        }
    } ?>
    </div>
</div>
