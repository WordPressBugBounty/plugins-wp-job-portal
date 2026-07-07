<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param none
*/

$wpjobportal_show_quick_apply_form  = wpjobportal::$_config->getConfigurationByConfigName('quick_apply_for_user');
if (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
    $wpjobportal_show_quick_apply_form  = wpjobportal::$_config->getConfigurationByConfigName('quick_apply_for_visitor');
}
$wpjobportal_google_recaptcha_3 = false;
$wpjobportal_captcha_quick_apply  = wpjobportal::$_config->getConfigurationByConfigName('quick_apply_captcha');

wp_register_script( 'wpjobportal-inline-handle', '' );
wp_enqueue_script( 'wpjobportal-inline-handle' );

$wpjobportal_inline_js_script = "
    jQuery(document).ready(function ($) {
        $.validate();

        // Mode Switcher Logic for Cover Letter Copilot
        $('.wjportal-cl-mode-label').on('click', function() {
            $('.wjportal-cl-mode-label').removeClass('is-active');
            $(this).addClass('is-active');

            const mode = $(this).data('mode');
            if (mode === 'ai') {
                $('#wjportal-ai-cl-workspace').slideDown(250);
                $('#wjportal-saved-cl-container').slideUp(250);
                $('select[name=\"coverletterid\"]').prop('disabled', true).removeClass('required');
                $('input[name=\"coverletter_title\"]').prop('disabled', false).addClass('required').attr('data-validation', 'required');
            } else {
                $('#wjportal-ai-cl-workspace').slideUp(250);
                $('#wjportal-saved-cl-container').slideDown(250);
                $('select[name=\"coverletterid\"]').prop('disabled', false).addClass('required');
                $('input[name=\"coverletter_title\"]').prop('disabled', true).removeClass('required').removeAttr('data-validation');
            }
        });
    });
";
wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
?>

<style>
    /* Unified Cover Letter Box Styles - SaaS 2.0 Aesthetic */
    .wjportal-cl-unified-box { border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.25rem; margin-bottom: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
    .wjportal-cl-unified-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem; padding-bottom: 1rem; border-bottom: 1px dashed #e2e8f0; }
    .wjportal-cl-unified-header .wjportal-form-title { font-size: 1.125rem; color: #1e293b; font-weight: 600; margin: 0; padding: 0; border: none; }

    /* Mode Switcher */
    .wjportal-cl-mode-container { display: flex; background: #f1f5f9; border-radius: 0.75rem; padding: 0.25rem; width: 100%; max-width: 320px; margin: 0; }
    .wjportal-cl-mode-label { flex: 1; text-align: center; padding: 0.5rem 0; font-size: 0.8125rem; font-weight: 600; color: #475569; cursor: pointer; transition: all 0.2s ease; display: flex; align-items: center; justify-content: center; gap: 0.375rem; margin: 0 !important; }
    .wjportal-cl-mode-label input[type="radio"] { display: none !important; }
    .wjportal-cl-mode-label.is-active { background: #ffffff; color: #3baeda; border-radius: 0.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.04); }

    /* Merged Inputs */
    .wjportal-cl-input-label { display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.375rem; text-transform: uppercase; letter-spacing: 0.05em; }
    .wjportal-cl-title-input-wrap { margin-bottom: 1.25rem; }
    .wjportal-cl-title-input-wrap input, .wjportal-cl-saved-select-wrap select { width: 100%; border-radius: 0.75rem; border: 1px solid #e2e8f0; padding: 0.75rem 1rem; font-size: 0.875rem; background: #f8fafc; transition: all 0.2s ease; box-sizing: border-box; }
    .wjportal-cl-title-input-wrap input:focus, .wjportal-cl-saved-select-wrap select:focus { border-color: #3baeda; background: #ffffff; outline: none; box-shadow: 0 0 0 3px rgba(59, 174, 218, 0.1); }
    .wjportal-cl-saved-select-wrap select:disabled { opacity: 0.6; cursor: not-allowed; }
</style>

<?php if ($wpjobportal_show_quick_apply_form == 1 || wpjobportal::$_config->getConfigValue('showapplybutton') == 1) { ?>
    <div class="wjportal-view-job-page-job-apply-form-wraper" id="wjportal-view-job-page-job-apply-form-bottom-wraper">
        <?php
            echo '<div class="wjportal-form-wrp wpjobportal-quickapply-form" >';
                echo '<div class="wjportal-job-sec-title" >';
                    echo esc_html(__('Apply On The Job', 'wp-job-portal'));
                echo '</div>';

                $wpjobportal_show_job_apply_redirect_link_only = 0;
                if( !empty($wpjobportal_job) && $wpjobportal_job->jobapplylink == 1 && !empty($wpjobportal_job->joblink)){
                    $wpjobportal_show_job_apply_redirect_link_only = 1;
                }

                echo '<form class="wjportal-form" id="wpjobportal-form" method="post" enctype="multipart/form-data" action="'. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobapply', 'task'=>'applyonjob'))).'">';
                    $wpjobportal_jobid =  (!empty(wpjobportal::$_data[0]) && isset(wpjobportal::$_data[0]->id)) ? wpjobportal::$_data[0]->id : '';
                    $wpjobportal_hide_apply_btn = 0;
                    $wpjobportal_hide_login_and_apply_btn = 1;
                    $wpjobportal_hide_select_role_btn = 1;
                    $wpjobportal_show_buy_package_btn = 0;
                    $wpjobportal_show_proceed_to_payment_button = 0;
                    $wpjobportal_payment_methods_array = array();
                    $wpjobportal_force_hide_btn = 0;
                    $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();

                    if($wpjobportal_show_job_apply_redirect_link_only == 0){
                        if($wpjobportal_show_quick_apply_form == 1){
                            $wpjobportal_formfields = WPJOBPORTALincluder::getTemplate('quickapply/form-fields',array());
                            foreach ($wpjobportal_formfields as $wpjobportal_formfield) {
                                WPJOBPORTALincluder::getTemplate('templates/form-field', $wpjobportal_formfield);
                            }

                            // -------------------------------------------------------------
                            // INTEGRATED COVER LETTER (QUICK APPLY)
                            // -------------------------------------------------------------
                            $enable_cover_letter_quick_apply  = wpjobportal::$_config->getConfigurationByConfigName('enable_cover_letter_quick_apply');
                            if(in_array('coverletter', wpjobportal::$_active_addons) && !WPJOBPORTALincluder::getObjectClass('user')->isguest() && $enable_cover_letter_quick_apply == 1){
                                $api_key = get_option('wpjobportal_zywrap_api_key', '');
                                $enable_jobapply_copilot  = wpjobportal::$_config->getConfigurationByConfigName('enable_jobapply_copilot');

                                // Check if AI Copilot is fully configured and enabled
                                if(!empty($api_key)  && (!empty($enable_jobapply_copilot) || current_user_can('manage_options')) ){

                                    echo '
                                    <div class="wjportal-cl-unified-box">
                                        <div class="wjportal-cl-unified-header">
                                            <div class="wjportal-form-title">
                                                '. esc_html__('Cover Letter', 'wp-job-portal').' <font color="#000">*</font>
                                            </div>
                                            <div class="wjportal-cl-mode-container">
                                                <label class="wjportal-cl-mode-label is-active" data-mode="ai">
                                                    <input type="radio" name="cl_mode" value="ai" checked>
                                                    ' . esc_html__('✨ Create with Copilot', 'wp-job-portal') . '
                                                </label>
                                                <label class="wjportal-cl-mode-label" data-mode="saved">
                                                    <input type="radio" name="cl_mode" value="saved">
                                                    ' . esc_html__('📁 Use Pre-Saved', 'wp-job-portal') . '
                                                </label>
                                            </div>
                                        </div>

                                        <div class="wjportal-cl-unified-body">';

                                            // Container Mode 1: Pre-Saved Dropdown (Hidden by Default)
                                            echo '<div id="wjportal-saved-cl-container" style="display:none;">';
                                            $wpjobportal_cover_letter_list = WPJOBPORTALincluder::getJSModel('coverletter')->getCoverLetterForCombocoverletter($wpjobportal_uid);
                                            if(!empty($wpjobportal_cover_letter_list)){
                                                echo '<div class="wjportal-cl-saved-select-wrap">';
                                                echo '<label for="coverletterid" class="wjportal-cl-input-label">' . esc_html__('Select Cover Letter', 'wp-job-portal') . '</label>';
                                                echo wp_kses(WPJOBPORTALformfield::select('coverletterid', $wpjobportal_cover_letter_list, '', '', array('class' => 'inputbox wjportal-form-select-field', 'disabled' => 'disabled')), WPJOBPORTAL_ALLOWED_TAGS);
                                                echo '</div>';
                                            } else {
                                                echo '<div class="job-detail-jobapply-message-wrap" style="margin-bottom:0;">';
                                                    echo '<span class="job-detail-jobapply-message-msg"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/not-loggedin.png" />' . esc_html(__('No Cover Letters found on your profile.', 'wp-job-portal')) . '</span>';
                                                echo '</div>';
                                            }
                                            echo '</div>';

                                            // Container Mode 2: Interactive AI Workspace View (Active Default)
                                            echo '<div id="wjportal-ai-cl-workspace">';

                                                echo '<div class="wjportal-cl-title-input-wrap">';
                                                    echo '<label for="coverletter_title" class="wjportal-cl-input-label">' . esc_html__('Cover Letter Title', 'wp-job-portal') . ' <font color="#000">*</font></label>';
                                                    echo wp_kses(WPJOBPORTALformfield::text('coverletter_title', '', array('class' => 'inputbox wjportal-form-text-field', 'placeholder' => esc_attr__('e.g., Senior Product Owner Application', 'wp-job-portal'), 'data-validation' => 'required')), WPJOBPORTAL_ALLOWED_TAGS);
                                                echo '</div>';

                                                $wpjobportal_cl_editor = WPJOBPORTALformfield::editor('coverletter_text', '', array('class' => 'inputbox one wjportal-form-textarea-field'));

                                                echo WPJOBPORTALincluder::getTemplate('views/frontend/content-generation-cover-letter', array('wpjobportal_desc_editor' => $wpjobportal_cl_editor,'module'=>'coverletter','module_name'=>'coverletter'));

                                            echo '</div>';

                                    echo '
                                        </div>
                                    </div>';
                                } else {
                                    // Fallback to legacy simple Dropdown if AI is disabled
                                    $wpjobportal_cover_letter_list = WPJOBPORTALincluder::getJSModel('coverletter')->getCoverLetterForCombocoverletter($wpjobportal_uid);
                                    if(!empty($wpjobportal_cover_letter_list)){
                                        echo '
                                        <div class="wjportal-form-row">
                                            <div class="wjportal-form-title">
                                                '. esc_html__('Cover Letter', 'wp-job-portal').' <font color="#000">*</font>
                                            </div>
                                            <div class="wjportal-form-value"> ';
                                                echo wp_kses(WPJOBPORTALformfield::select('coverletterid', $wpjobportal_cover_letter_list, '', '', array('class' => 'inputbox wjportal-form-select-field', 'data-validation' => 'required')), WPJOBPORTAL_ALLOWED_TAGS);
                                        echo '
                                            </div>
                                        </div>';
                                    } else {
                                        echo '<div class="job-detail-jobapply-message-wrap">';
                                            echo '<span class="job-detail-jobapply-message-msg"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/not-loggedin.png" />' . esc_html(__('No Cover Letter', 'wp-job-portal')) . '</span>';
                                            echo '<a class="job-detail-jobapply-message-link" href="'.esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'coverletter', 'wpjobportallt'=>'addcoverletter'))).'" class="coverlettteraddlink" target="_blank">' . esc_html(__('Add Cover Letter', 'wp-job-portal')) . '</a>';
                                        echo '</div>';
                                    }
                                }
                            }
                            // -------------------------------------------------------------

                            if (WPJOBPORTALincluder::getObjectClass('user')->isguest() && $wpjobportal_captcha_quick_apply == 1) {
                                $wpjobportal_config_array = wpjobportal::$_config->getConfigByFor('captcha');
                                if ($wpjobportal_config_array['captcha_selection'] == 1) {
                                    if($wpjobportal_config_array['recaptcha_version'] == 1){
                                        echo '<div class="g-recaptcha" data-sitekey="'.esc_attr($wpjobportal_config_array["recaptcha_publickey"]).'"></div>';
                                    }else{
                                        $wpjobportal_google_recaptcha_3 = true;
                                    }
                                } else {
                                    $wpjobportal_captcha = new WPJOBPORTALcaptcha;
                                    echo '<div class="recaptcha-wrp">'.wp_kses($wpjobportal_captcha->getCaptchaForForm(),WPJOBPORTAL_ALLOWED_TAGS).'</div>';
                                }
                            }
                            echo wp_kses(WPJOBPORTALformfield::hidden('quickapply', 1),WPJOBPORTAL_ALLOWED_TAGS);
                        } else {
                            if (!WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
                                $wpjobportal_isjobseeker = WPJOBPORTALincluder::getObjectClass('user')->isjobseeker();
                                if (is_numeric($wpjobportal_uid) && $wpjobportal_uid != 0 && $wpjobportal_isjobseeker == true) {

                                    // A. RESUME SELECTION ROW
                                    $wpjobportal_resume_list = WPJOBPORTALincluder::getJSModel('resume')->getResumesForJobapply();
                                    if(!empty($wpjobportal_resume_list)){
                                        echo '
                                        <div class="wjportal-form-row">
                                            <div class="wjportal-form-title">
                                                '. esc_html__('Resume', 'wp-job-portal').' <font color="#000">*</font>
                                            </div>
                                            <div class="wjportal-form-value"> ';
                                                echo wp_kses(WPJOBPORTALformfield::select('cvid', $wpjobportal_resume_list, '', '', array('class' => 'inputbox wjportal-form-select-field', 'data-validation' => 'required')), WPJOBPORTAL_ALLOWED_TAGS);
                                        echo '
                                            </div>
                                        </div>';
                                    } else {
                                        echo '<div class="job-detail-jobapply-message-wrap">';
                                            echo '<span class="job-detail-jobapply-message-msg"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/not-loggedin.png" />' . esc_html(__('You do not have any resume!', 'wp-job-portal')) . '</span>';
                                            echo '<a class="job-detail-jobapply-message-link" href="'.esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'addresume'))).'" class="resumeaddlink" target="_blank">' . esc_html(__('Add Resume', 'wp-job-portal')) . '</a>';
                                        echo '</div>';
                                        $wpjobportal_hide_apply_btn = 1;
                                    }

                                    // B. INTEGRATED COVER LETTER (REGULAR APPLY)
                                    if(in_array('coverletter', wpjobportal::$_active_addons) && !WPJOBPORTALincluder::getObjectClass('user')->isguest()){

                                        $api_key = get_option('wpjobportal_zywrap_api_key', '');
                                        $enable_jobapply_copilot  = wpjobportal::$_config->getConfigurationByConfigName('enable_jobapply_copilot');

                                        // Check if AI Copilot is fully configured and enabled
                                        if(!empty($api_key) && (!empty($enable_jobapply_copilot) || current_user_can('manage_options')) ){
                                            echo '
                                            <div class="wjportal-cl-unified-box">
                                                <div class="wjportal-cl-unified-header">
                                                    <div class="wjportal-form-title">
                                                        '. esc_html__('Cover Letter', 'wp-job-portal').' <font color="#000">*</font>
                                                    </div>
                                                    <div class="wjportal-cl-mode-container">
                                                        <label class="wjportal-cl-mode-label is-active" data-mode="ai">
                                                            <input type="radio" name="cl_mode" value="ai" checked>
                                                            ' . esc_html__('✨ Create with Copilot', 'wp-job-portal') . '
                                                        </label>
                                                        <label class="wjportal-cl-mode-label" data-mode="saved">
                                                            <input type="radio" name="cl_mode" value="saved">
                                                            ' . esc_html__('📁 Use Pre-Saved', 'wp-job-portal') . '
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="wjportal-cl-unified-body">';

                                                    echo '<div id="wjportal-saved-cl-container" style="display:none;">';
                                                    $wpjobportal_cover_letter_list = WPJOBPORTALincluder::getJSModel('coverletter')->getCoverLetterForCombocoverletter($wpjobportal_uid);
                                                    if(!empty($wpjobportal_cover_letter_list)){
                                                        echo '<div class="wjportal-cl-saved-select-wrap">';
                                                        echo '<label for="coverletterid" class="wjportal-cl-input-label">' . esc_html__('Select Cover Letter', 'wp-job-portal') . '</label>';
                                                        echo wp_kses(WPJOBPORTALformfield::select('coverletterid', $wpjobportal_cover_letter_list, '', '', array('class' => 'inputbox wjportal-form-select-field', 'disabled' => 'disabled')), WPJOBPORTAL_ALLOWED_TAGS);
                                                        echo '</div>';
                                                    } else {
                                                        echo '<div class="job-detail-jobapply-message-wrap" style="margin-bottom:0;">';
                                                            echo '<span class="job-detail-jobapply-message-msg"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/not-loggedin.png" />' . esc_html(__('No Cover Letters found on your profile.', 'wp-job-portal')) . '</span>';
                                                        echo '</div>';
                                                    }
                                                    echo '</div>';

                                                    echo '<div id="wjportal-ai-cl-workspace">';

                                                        echo '<div class="wjportal-cl-title-input-wrap">';
                                                            echo '<label for="coverletter_title" class="wjportal-cl-input-label">' . esc_html__('Document Title', 'wp-job-portal') . ' <font color="#000">*</font></label>';
                                                            echo wp_kses(WPJOBPORTALformfield::text('coverletter_title', '', array('class' => 'inputbox wjportal-form-text-field', 'placeholder' => esc_attr__('e.g., Senior Product Owner Application', 'wp-job-portal'), 'data-validation' => 'required')), WPJOBPORTAL_ALLOWED_TAGS);
                                                        echo '</div>';

                                                        $wpjobportal_cl_editor = WPJOBPORTALformfield::editor('coverletter_text', '', array('class' => 'inputbox one wjportal-form-textarea-field'));

                                                        echo WPJOBPORTALincluder::getTemplate('views/frontend/content-generation-cover-letter', array('wpjobportal_desc_editor' => $wpjobportal_cl_editor,'module'=>'coverletter','module_name'=>'coverletter'));

                                                    echo '</div>';

                                                echo '
                                                </div>
                                            </div>';
                                        } else {
                                            // Fallback to legacy simple Dropdown if AI is disabled
                                            $wpjobportal_cover_letter_list = WPJOBPORTALincluder::getJSModel('coverletter')->getCoverLetterForCombocoverletter($wpjobportal_uid);
                                            if(!empty($wpjobportal_cover_letter_list)){
                                                echo '
                                                <div class="wjportal-form-row">
                                                    <div class="wjportal-form-title">
                                                        '. esc_html__('Cover Letter', 'wp-job-portal').' <font color="#000">*</font>
                                                    </div>
                                                    <div class="wjportal-form-value"> ';
                                                        echo wp_kses(WPJOBPORTALformfield::select('coverletterid', $wpjobportal_cover_letter_list, '', '', array('class' => 'inputbox wjportal-form-select-field', 'data-validation' => 'required')), WPJOBPORTAL_ALLOWED_TAGS);
                                                echo '
                                                    </div>
                                                </div>';
                                            } else {
                                                echo '<div class="job-detail-jobapply-message-wrap">';
                                                    echo '<span class="job-detail-jobapply-message-msg"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/not-loggedin.png" />' . esc_html(__('No Cover Letter', 'wp-job-portal')) . '</span>';
                                                    echo '<a class="job-detail-jobapply-message-link" href="'.esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'coverletter', 'wpjobportallt'=>'addcoverletter'))).'" class="coverlettteraddlink" target="_blank">' . esc_html(__('Add Cover Letter', 'wp-job-portal')) . '</a>';
                                                echo '</div>';
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // C. MEMBERSHIP & PACKAGE PROCESSING PIPELINE (Unchanged)
                    $wpjobportal_subtype = wpjobportal::$_config->getConfigValue('submission_type');
                    if (WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()) {
                        $wpjobportal_can_apply_on_job = WPJOBPORTALincluder::getJSModel('jobapply')->checkAlreadyAppliedJob($wpjobportal_jobid, $wpjobportal_uid);
                        $wpjobportal_payment_not_required = WPJOBPORTALincluder::getJSmodel('jobapply')->checkjobappllystats($wpjobportal_jobid, $wpjobportal_uid);

                        if($wpjobportal_can_apply_on_job == false && $wpjobportal_payment_not_required == true){
                            echo '<div class="frontend error"><p>'.esc_html(__('You have already applied on this job.', 'wp-job-portal')).'</p></div>';
                            $wpjobportal_hide_apply_btn = 1;
                            $wpjobportal_force_hide_btn = 1;
                        } else {
                            if(in_array('credits', wpjobportal::$_active_addons)){
                                if( $wpjobportal_subtype == 3 ){
                                    if ( empty($wpjobportal_uid) ) {
                                        $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
                                    }

                                    $wpjobportal_no_package_needed = 0;
                                    if ( is_numeric($wpjobportal_uid) && $wpjobportal_uid > 0 ) {
                                        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('credits')->checkIfPackageDefinedForUserRole($wpjobportal_uid);
                                        if ( $wpjobportal_result == 0 ) {
                                            $wpjobportal_no_package_needed = 1;
                                        }
                                    }

                                    if ( $wpjobportal_no_package_needed !== 1 ) {
                                        $wpjobportal_userpackages = array();
                                        $wpjobportal_userpackage = apply_filters('wpjobportal_addons_credit_get_Packages_user', false, $wpjobportal_uid, 'jobapply');

                                        if ( is_array($wpjobportal_userpackage) && !empty($wpjobportal_userpackage) ) {
                                            foreach ($wpjobportal_userpackage as $wpjobportal_package) {
                                                if ($wpjobportal_package->jobapply == -1 || $wpjobportal_package->remjobapply > 0) {
                                                    $wpjobportal_package_for_combo = new stdClass();
                                                    $wpjobportal_package_for_combo->id = $wpjobportal_package->id;
                                                    $wpjobportal_package_for_combo->text = $wpjobportal_package->title;
                                                    $wpjobportal_package_for_combo->text .= $wpjobportal_package->jobapply == -1
                                                        ? ' ('.esc_html(__("Unlimited job applies",'wp-job-portal')).')'
                                                        : ' ('.esc_attr($wpjobportal_package->remjobapply).' '.esc_html(__("Job applies remaining",'wp-job-portal')).')' ;
                                                    $wpjobportal_userpackages[] = $wpjobportal_package_for_combo;
                                                }
                                            }
                                        }

                                        if ( !empty($wpjobportal_userpackages) ) {
                                            echo '
                                            <div class="wjportal-form-row wjportal-form-pckge-row">
                                                <div class="wjportal-form-title">
                                                    '. esc_html__('Apply With Package', 'wp-job-portal').' <font color="#000">*</font>
                                                </div>
                                                <div class="wjportal-form-value"> ';
                                                    echo wp_kses(WPJOBPORTALformfield::select('upkid', $wpjobportal_userpackages, '', '', array('class' => 'inputbox wjportal-form-select-field', 'data-validation' => 'required')), WPJOBPORTAL_ALLOWED_TAGS);
                                            echo '
                                                </div>
                                            </div>';
                                        } else {
                                            echo '<div class="frontend error"><p>'.esc_html(__("Buy package to apply on job.",'wp-job-portal')).'</p></div>';
                                            $wpjobportal_hide_apply_btn = 1;
                                            $wpjobportal_show_buy_package_btn = 1;
                                        }
                                    }
                                } elseif( $wpjobportal_subtype == 2 ){
                                    $wpjobportal_price = wpjobportal::$_config->getConfigValue('job_jobapply_price_perlisting');
                                    $wpjobportal_currencyid = wpjobportal::$_config->getConfigValue('job_currency_jobapply_perlisting');
                                    $wpjobportal_decimals = WPJOBPORTALincluder::getJSModel('currency')->getDecimalPlaces($wpjobportal_currencyid);
                                    $wpjobportal_formattedPrice = wpjobportalphplib::wpJP_number_format($wpjobportal_price,$wpjobportal_decimals);
                                    $wpjobportal_priceCompanytlist = WPJOBPORTALincluder::getJSModel('common')->getFancyPrice($wpjobportal_price,$wpjobportal_currencyid,array('decimal_places'=>$wpjobportal_decimals));

                                    if(is_numeric($wpjobportal_price) && $wpjobportal_price > 0){
                                        echo '<div class="wjportal-job-apply-price-msg" >';
                                        echo esc_html(__('Payment of', 'wp-job-portal')). ' <strong>'.esc_html($wpjobportal_priceCompanytlist).'</strong> '.esc_html(__('is required to complete the job apply process', 'wp-job-portal'));
                                        echo '</div>';

                                        if($wpjobportal_payment_not_required == true){
                                            $wpjobportal_paymentconfig = wpjobportal::$_wpjppaymentconfig->getPaymentConfigFor('paypal,stripe,woocommerce',true);
                                            $wpjobportal_default_selected_payment_method = '';
                                            if($wpjobportal_paymentconfig['isenabled_paypal'] == 1){
                                                $wpjobportal_payment_methods_array[1] = '<img src="'. esc_url(WPJOBPORTAL_IMAGE).'/paypal.jpg" alt="'. esc_attr(__("PayPal","wp-job-portal")).'" title="'. esc_attr(__("PayPal","wp-job-portal")).'" /> '. esc_html(__('PayPal', 'wp-job-portal'));
                                                $wpjobportal_default_selected_payment_method = 1;
                                            }
                                            if($wpjobportal_paymentconfig['isenabled_woocommerce'] == 1) {
                                                $wpjobportal_payment_methods_array[2] = '<img src="'. esc_url(WPJOBPORTAL_IMAGE).'/woo.jpg" alt="'. esc_attr(__("WooCommerce","wp-job-portal")).'" title="'. esc_attr(__("WooCommerce","wp-job-portal")).'" /> '. esc_html(__('WooCommerce', 'wp-job-portal'));
                                                if($wpjobportal_default_selected_payment_method == '') $wpjobportal_default_selected_payment_method = 2;
                                            }
                                            if($wpjobportal_paymentconfig['isenabled_stripe'] == 1) {
                                                $wpjobportal_payment_methods_array[3] = '<img src="'. esc_url(WPJOBPORTAL_IMAGE).'/stripe.jpg" alt="'. esc_attr(__("Stripe","wp-job-portal")).'" title="'. esc_attr(__("Stripe","wp-job-portal")).'" /> '. esc_html(__('Stripe', 'wp-job-portal'));
                                                if($wpjobportal_default_selected_payment_method == '') $wpjobportal_default_selected_payment_method = 3;
                                            }
                                        } else {
                                            $wpjobportal_show_proceed_to_payment_button = 1;
                                            $wpjobportal_hide_apply_btn = 1;
                                        }
                                    }
                                }
                            }
                        }
                    } elseif (WPJOBPORTALincluder::getObjectClass('user')->isemployer()) {
                        echo '<div class="frontend error"><p>'.esc_html(__('You are logged in as employer.', 'wp-job-portal')).'</p></div>';
                        $wpjobportal_hide_apply_btn = 1;
                    } elseif (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
                        if($wpjobportal_show_quick_apply_form != 1){
                            echo '<div class="frontend error"><p>'.esc_html(__('You are not a logged in member.', 'wp-job-portal')).'</p></div>';
                            $wpjobportal_hide_apply_btn = 1;
                            $wpjobportal_hide_login_and_apply_btn = 0;
                        }
                    } else {
                        echo '<div class="frontend error"><p>'.esc_html(__('You do not have any role', 'wp-job-portal')).'</p></div>';
                        $wpjobportal_hide_apply_btn = 1;
                        $wpjobportal_hide_select_role_btn = 0;
                    }

                    // D. RENDER PRIMARY SUBMIT CONTROLS (Unchanged)
                    $wpjobportal_btn_visible = 0;
                    if($wpjobportal_hide_apply_btn == 0){
                        $wpjobportal_btn_label  = __('Apply Now', 'wp-job-portal');
                        if(!empty($wpjobportal_payment_methods_array) && $wpjobportal_show_job_apply_redirect_link_only == 0){
                            $wpjobportal_btn_label  = __('Proceed To Payment', 'wp-job-portal');
                            echo '
                            <div class="wjportal-form-row">
                                <div class="wjportal-form-title">
                                    '. esc_html__('Payment Method', 'wp-job-portal').' <font color="#000">*</font>
                                </div>
                                <div class="wjportal-form-value wjportal-job-apply-payment-method"> ';
                                    echo wp_kses(WPJOBPORTALformfield::radiobutton('selected_payment_method', $wpjobportal_payment_methods_array, $wpjobportal_default_selected_payment_method, array('class' => 'radiobutton')),WPJOBPORTAL_ALLOWED_TAGS);
                            echo '
                                </div>
                            </div>';
                        }

                        if($wpjobportal_show_job_apply_redirect_link_only == 0){
                            echo '<div class="wjportal-form-btn-wrp wjportal-apply-package-apply-now-button">
                                '. wp_kses(WPJOBPORTALformfield::submitbutton('save', esc_html($wpjobportal_btn_label), array('class' => 'button wjportal-form-btn wjportal-save-btn')),WPJOBPORTAL_ALLOWED_TAGS).'
                            </div>';
                            $wpjobportal_btn_visible = 1;
                        } elseif($wpjobportal_show_job_apply_redirect_link_only == 1){
                            echo '<div class="wjportal-form-btn-wrp wjportal-apply-package-apply-now-button">
                             <a class="wjportal-login-to-apply-btn" href="'.esc_url($wpjobportal_job->joblink).'"  target="_blank">' . esc_html(__('Apply Now', 'wp-job-portal')).'</a>
                            </div>';
                            $wpjobportal_btn_visible = 1;
                        }
                    }

                    if($wpjobportal_hide_login_and_apply_btn == 0  && $wpjobportal_show_job_apply_redirect_link_only == 0){
                        $wpjobportal_redirect_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'viewjob', 'wpjobportalid'=>$wpjobportal_jobid));
                        $wpjobportal_redirect_url = wpjobportalphplib::wpJP_safe_encoding($wpjobportal_redirect_url);
                        $wpjobportal_login_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'wpjobportal', 'wpjobportallt'=>'login', 'wpjobportalredirecturl'=>$wpjobportal_redirect_url));

                        echo '<div class="wjportal-form-btn-wrp wjportal-login-to-apply-btn-wrap">
                            <a href="'.esc_url($wpjobportal_login_link).'" target="_blank" class="wjportal-login-to-apply-btn" >'.esc_html__('Login', 'wp-job-portal').'</a>
                        </div>';

                        $wpjobportal_visitor_can_apply_to_job = wpjobportal::$_config->getConfigurationByConfigName('visitor_can_apply_to_job');
                        if($wpjobportal_visitor_can_apply_to_job == 1){
                            echo '<div class="wjportal-job-apply-or-visitor"><span>'. esc_html(__("Or", "wp-job-portal")) .'</span></div>';
                            $wpjobportal_visitorapplylink = wp_nonce_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobapply', 'action'=>'wpjobportaltask', 'task'=>'jobapplyasvisitor', 'wpjobportalid-jobid'=>$wpjobportal_jobid)),'wpjobportal_job_apply_nonce') ;
                            echo '<div class="wjportal-form-btn-wrp wjportal-apply-as-visitor-btn-wrap">
                                <a href="'.esc_url($wpjobportal_visitorapplylink).'" class="wjportal-apply-as-visitor-btn" >'.esc_html__('Apply As Visitor', 'wp-job-portal').'</a>
                            </div>';
                        }
                        $wpjobportal_btn_visible = 1;
                    }

                    if($wpjobportal_hide_select_role_btn == 0){
                        $wpjobportal_select_role_link =  esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'common','wpjobportallt'=>'newinwpjobportal', 'wpjobportalid-jobid'=>$wpjobportal_jobid))) ;
                        echo '<div class="wjportal-form-btn-wrp wjportal-login-to-apply-btn-wrap">
                            <a href="'.esc_url($wpjobportal_select_role_link).'" target="_blank" class="wjportal-login-to-apply-btn" >'.esc_html(__('Select Role', 'wp-job-portal')).'</a>
                        </div>';
                        $wpjobportal_btn_visible = 1;
                    }

                    if($wpjobportal_show_buy_package_btn == 1){
                        $wpjobportal_buy_packages_link =  esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'package','wpjobportallt'=>'packages'))) ;
                        echo '<div class="wjportal-form-btn-wrp wjportal-login-to-apply-btn-wrap">
                            <a href="'.esc_url($wpjobportal_buy_packages_link).'" target="_blank" class="wjportal-login-to-apply-btn" >'.esc_html(__('Buy Package', 'wp-job-portal')).'</a>
                        </div>';
                        $wpjobportal_btn_visible = 1;
                    }
                    if($wpjobportal_show_proceed_to_payment_button == 1){
                        $wpjobportal_buy_packages_link =  wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'purchasehistory','wpjobportallt'=>'payjobapply','wpjobportalid'=>$wpjobportal_jobid,'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                        echo '<div class="wjportal-form-btn-wrp wjportal-login-to-apply-btn-wrap">
                            <a href="'.esc_url($wpjobportal_buy_packages_link).'" target="_blank" class="wjportal-login-to-apply-btn" >'.esc_html(__('Proceed To Payment', 'wp-job-portal')).'</a>
                        </div>';
                        $wpjobportal_btn_visible = 1;
                    }

                    if($wpjobportal_btn_visible == 0  && $wpjobportal_show_job_apply_redirect_link_only == 1){
                        echo '<div class="wjportal-form-btn-wrp wjportal-apply-package-apply-now-button">
                         <a class="wjportal-login-to-apply-btn" href="'.esc_url($wpjobportal_job->joblink).'"  target="_blank">' . esc_html(__('Apply Now', 'wp-job-portal')).'</a>
                        </div>';
                        $wpjobportal_btn_visible = 1;
                    }

                    if($wpjobportal_btn_visible == 0 && $wpjobportal_force_hide_btn == 0 && $wpjobportal_hide_apply_btn == 0){
                        echo '<div class="wjportal-form-btn-wrp wjportal-login-to-apply-btn-wrap">
                            <a href="#"  class="wjportal-login-to-apply-btn" >'.esc_html(__('Apply Now', 'wp-job-portal')).'</a>
                        </div>';
                    }

                    echo wp_kses(WPJOBPORTALformfield::hidden('wpjobportalpageid', wpjobportal::wpjobportal_getPageid()),WPJOBPORTAL_ALLOWED_TAGS);
                    echo wp_kses(WPJOBPORTALformfield::hidden('jobid', (isset(wpjobportal::$_data[0]) && !empty(wpjobportal::$_data[0])) ? wpjobportal::$_data[0]->id: '' ),WPJOBPORTAL_ALLOWED_TAGS);
                    echo wp_kses(WPJOBPORTALformfield::hidden('form_request', 'wpjobportal'),WPJOBPORTAL_ALLOWED_TAGS);
                    echo wp_kses(WPJOBPORTALformfield::hidden('_wpnonce', esc_html(wp_create_nonce('wpjobportal_job_apply_nonce'))),WPJOBPORTAL_ALLOWED_TAGS);
                echo '</form>';
            echo '</div>';
       ?>
    </div>
    <?php
}

if($wpjobportal_captcha_quick_apply == 1){
    $wpjobportal_protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $wpjobportal_config_array = wpjobportal::$_config->getConfigByFor('captcha');
    if($wpjobportal_config_array['captcha_selection'] == 1 && $wpjobportal_config_array['recaptcha_privatekey'] ){
        wp_enqueue_script('wpjobportal-repaptcha-scripti', $wpjobportal_protocol . 'www.google.com/recaptcha/api.js');
    }
}
?>