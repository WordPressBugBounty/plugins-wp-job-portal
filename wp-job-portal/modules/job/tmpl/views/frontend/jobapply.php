
<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param none
*/

// code to manage width of info section and show hide apply form


$wpjobportal_show_quick_apply_form  = wpjobportal::$_config->getConfigurationByConfigName('quick_apply_for_user');
if (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
    $wpjobportal_show_quick_apply_form  = wpjobportal::$_config->getConfigurationByConfigName('quick_apply_for_visitor');
}
$wpjobportal_google_recaptcha_3 = false;
$wpjobportal_captcha_quick_apply  = wpjobportal::$_config->getConfigurationByConfigName('quick_apply_captcha');
?>

<?php
         wp_register_script( 'wpjobportal-inline-handle', '' );
         wp_enqueue_script( 'wpjobportal-inline-handle' );

         $wpjobportal_inline_js_script = "
             jQuery(document).ready(function ($) {
                 $.validate();
             });
             ";
         wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
        if ($wpjobportal_show_quick_apply_form == 1 || wpjobportal::$_config->getConfigValue('showapplybutton') == 1) { ?>
            <div class="wjportal-view-job-page-job-apply-form-wraper"  id="wjportal-view-job-page-job-apply-form-bottom-wraper">
                <?php  //do_action('wpjobportal_addons_quick_apply_form');
                    echo '<div class="wjportal-form-wrp wpjobportal-quickapply-form" >';
                        echo '<div class="wjportal-job-sec-title" >';
                            echo esc_html(__('Apply to the Job', 'wp-job-portal'));
                        echo '</div>';
                        $wpjobportal_show_job_apply_redirect_link_only = 0;
                        if( !empty($wpjobportal_job) && $wpjobportal_job->jobapplylink == 1 && !empty($wpjobportal_job->joblink)){ // hadnling error in case of employer job detail
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
                            if($wpjobportal_show_job_apply_redirect_link_only ==0){
                                if($wpjobportal_show_quick_apply_form == 1){ // quick apply case
                                    $wpjobportal_formfields = WPJOBPORTALincluder::getTemplate('quickapply/form-fields',array());
                                    foreach ($wpjobportal_formfields as $wpjobportal_formfield) {
                                        WPJOBPORTALincluder::getTemplate('templates/form-field', $wpjobportal_formfield);
                                    }
                                    if (WPJOBPORTALincluder::getObjectClass('user')->isguest() && $wpjobportal_captcha_quick_apply == 1) {
                                        $wpjobportal_config_array = wpjobportal::$_config->getConfigByFor('captcha');
                                        if ($wpjobportal_config_array['captcha_selection'] == 1) { // Google recaptcha
                                            if($wpjobportal_config_array['recaptcha_version'] == 1){
                                                echo '<div class="g-recaptcha" data-sitekey="'.esc_attr($wpjobportal_config_array["recaptcha_publickey"]).'"></div>';
                                            }else{
                                                $wpjobportal_google_recaptcha_3 = true;
                                            }

                                        } else { // own captcha
                                            $wpjobportal_captcha = new WPJOBPORTALcaptcha;
                                            echo '<div class="recaptcha-wrp">'.wp_kses($wpjobportal_captcha->getCaptchaForForm(),WPJOBPORTAL_ALLOWED_TAGS).'</div>';
                                        }
                                    }
                                    echo wp_kses(WPJOBPORTALformfield::hidden('quickapply', 1),WPJOBPORTAL_ALLOWED_TAGS);
                                }else{ // legacy apply case
                                    if (!WPJOBPORTALincluder::getObjectClass('user')->isguest()) { // curent user not guest
                                        $wpjobportal_isjobseeker = WPJOBPORTALincluder::getObjectClass('user')->isjobseeker();
                                        $wpjobportal_isemployer = WPJOBPORTALincluder::getObjectClass('user')->isemployer();
                                        if (is_numeric($wpjobportal_uid) && $wpjobportal_uid != 0 && $wpjobportal_isjobseeker == true) { // not guest and is jobseeker
                                            // resume section

                                            //get resumes
                                            $wpjobportal_resume_list = WPJOBPORTALincluder::getJSModel('resume')->getResumesForJobapply();
                                            if(!empty($wpjobportal_resume_list)){ // if user has resumes
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
                                            }else{ // no resume message and link to add resume
                                                echo '<div class="job-detail-jobapply-message-wrap">';
                                                    echo '<span class="job-detail-jobapply-message-msg"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/not-loggedin.png" />' . esc_html(__('You do not have any resume!', 'wp-job-portal')) . '</span>';
                                                    echo '<a class="job-detail-jobapply-message-link" href="'.esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'addresume'))).'" class="resumeaddlink" target="_blank">' . esc_html(__('Add Resume', 'wp-job-portal')) . '</a>';
                                                echo '</div>';
                                                $wpjobportal_hide_apply_btn = 1;
                                            }

                                            if(in_array('coverletter', wpjobportal::$_active_addons)){
                                                // Cover letter section
                                                // get user cover letters
                                                $wpjobportal_cover_letter_list = WPJOBPORTALincluder::getJSModel('coverletter')->getCoverLetterForCombocoverletter($wpjobportal_uid);
                                                if(!empty($wpjobportal_cover_letter_list)){ // if user has coverletters
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
                                                }else{ // no cover letter message and add cover letter link
                                                    echo '<div class="job-detail-jobapply-message-wrap">';
                                                        echo '<span class="job-detail-jobapply-message-msg"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/not-loggedin.png" />' . esc_html(__('No Cover Letter!', 'wp-job-portal')) . '</span>';
                                                        echo '<a class="job-detail-jobapply-message-link" href="'.esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'coverletter', 'wpjobportallt'=>'addcoverletter'))).'" class="coverlettteraddlink" target="_blank">' . esc_html(__('Add Cover Lettter', 'wp-job-portal')) . '</a>';
                                                    echo '</div>';
                                                }
                                            }
                                        }
                                    }
                                } // legacy apply resume and cover letter section ended
                            } // wpjobportal_show_job_apply_redirect_link_only

                            // to handle per listing and membership mode
                            $wpjobportal_subtype = wpjobportal::$_config->getConfigValue('submission_type');
                            if (WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()) {
                                // check if already applied on job
                                $wpjobportal_can_apply_on_job = WPJOBPORTALincluder::getJSModel('jobapply')->checkAlreadyAppliedJob($wpjobportal_jobid, $wpjobportal_uid);
                                // check if job apply payment is pending
                                $wpjobportal_payment_not_required = WPJOBPORTALincluder::getJSmodel('jobapply')->checkjobappllystats($wpjobportal_jobid, $wpjobportal_uid);
                                if($wpjobportal_can_apply_on_job == false && $wpjobportal_payment_not_required == true){ //show already applied message
                                    echo '<div class="frontend error"><p>'.esc_html(__('You have already applied on this job.', 'wp-job-portal')).'</p></div>';
                                    $wpjobportal_hide_apply_btn = 1;
                                    $wpjobportal_force_hide_btn = 1;
                                }else{ // current user is job seeker and has no job apply check and impliment package system
                                    if(in_array('credits', wpjobportal::$_active_addons)){ // check for credit system
                                        if( $wpjobportal_subtype == 3 ){ // membership mode is on
                                            // Ensure $wpjobportal_uid is resolved
                                            if ( empty($wpjobportal_uid) ) {
                                                $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
                                            }

                                            // Determine if package system is defined for this user's role.
                                            // If no package is defined for the role (result == 0) -> bypass package system entirely.
                                            $wpjobportal_no_package_needed = 0;
                                            if ( is_numeric($wpjobportal_uid) && $wpjobportal_uid > 0 ) { // only meaningful for logged in users
                                                $wpjobportal_result = WPJOBPORTALincluder::getJSModel('credits')->checkIfPackageDefinedForUserRole($wpjobportal_uid);
                                                if ( $wpjobportal_result == 0 ) { // 0 means no package defined for this user role -> bypass packages
                                                    $wpjobportal_no_package_needed = 1;
                                                }
                                            }

                                            if ( $wpjobportal_no_package_needed == 1 ) {
                                                // No package system defined for this user role — allow apply flow like "free" mode.
                                                // Do not show any package UI or messages. Keep $wpjobportal_hide_apply_btn as-is (default behavior).
                                                // Intentionally empty: form continues without package-related UI.
                                            } else {
                                                // Package system exists for this role -> enforce package logic (unchanged behaviour)
                                                $wpjobportal_userpackages = array(); // array to handle user packages in select package drop down
                                                $wpjobportal_userpackage = apply_filters('wpjobportal_addons_credit_get_Packages_user', false, $wpjobportal_uid, 'jobapply');

                                                if ( is_array($wpjobportal_userpackage) && !empty($wpjobportal_userpackage) ) { // user bought packages array
                                                    foreach ($wpjobportal_userpackage as $wpjobportal_package) {
                                                        if ($wpjobportal_package->jobapply == -1 || $wpjobportal_package->remjobapply > 0) { // -1 = unlimited, or remaining > 0
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
                                                    // If user has valid packages, show dropdown (same UI as before)
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
                                                    // User has no purchased package but package system IS defined for the role -> require buying package
                                                    echo '<div class="frontend error"><p>'.esc_html(__("Buy package to apply on job.",'wp-job-portal')).'</p></div>';
                                                    $wpjobportal_hide_apply_btn = 1;
                                                    $wpjobportal_show_buy_package_btn = 1;
                                                }
                                            }
                                            // memebership mode code ended

                                        }elseif( $wpjobportal_subtype == 2 ){ // per listing mode is on
                                            //per Listing For job apply
                                            $wpjobportal_price = wpjobportal::$_config->getConfigValue('job_jobapply_price_perlisting');
                                            $wpjobportal_currencyid = wpjobportal::$_config->getConfigValue('job_currency_jobapply_perlisting');
                                            $wpjobportal_decimals = WPJOBPORTALincluder::getJSModel('currency')->getDecimalPlaces($wpjobportal_currencyid);
                                            $wpjobportal_formattedPrice = wpjobportalphplib::wpJP_number_format($wpjobportal_price,$wpjobportal_decimals);
                                            $wpjobportal_priceCompanytlist = WPJOBPORTALincluder::getJSModel('common')->getFancyPrice($wpjobportal_price,$wpjobportal_currencyid,array('decimal_places'=>$wpjobportal_decimals));
                                            if(is_numeric($wpjobportal_price) && $wpjobportal_price > 0){
                                                echo '<div class="wjportal-job-apply-price-msg" >';
                                                echo esc_html(__('Payment of', 'wp-job-portal')). ' <strong>'.esc_html($wpjobportal_priceCompanytlist).'</strong> '.esc_html(__('is required to complete the job apply process', 'wp-job-portal'));
                                                echo '</div>';
                                                if($wpjobportal_payment_not_required == true){ // job apply is not pending becasue of payment

                                                    // check enabled payment methods create an array for radio button selection in case of multiple
                                                    $wpjobportal_paymentconfig = wpjobportal::$_wpjppaymentconfig->getPaymentConfigFor('paypal,stripe,woocommerce',true);
                                                    $wpjobportal_default_selected_payment_method = '';
                                                    if($wpjobportal_paymentconfig['isenabled_paypal'] == 1){ // paypal as a payment method is enabled
                                                        $wpjobportal_payment_methods_array[1] = '<img src="'. esc_url(WPJOBPORTAL_IMAGE).'/paypal.jpg" alt="'. esc_attr(__("paypal","wp-job-portal")).'" title="'. esc_attr(__("paypal","wp-job-portal")).'" /> '. esc_html(__('PayPal', 'wp-job-portal'));
                                                        $wpjobportal_default_selected_payment_method = 1;
                                                    }
                                                    if($wpjobportal_paymentconfig['isenabled_woocommerce'] == 1) { // woo commerce as a payment method is enabled
                                                        // uncomment this line
                                                        // if(class_exists( 'WooCommerce' )){
                                                            $wpjobportal_payment_methods_array[2] = '<img src="'. esc_url(WPJOBPORTAL_IMAGE).'/woo.jpg" alt="'. esc_attr(__("woocommerce","wp-job-portal")).'" title="'. esc_attr(__("woocommerce","wp-job-portal")).'" /> '. esc_html(__('Woocommerce', 'wp-job-portal'));
                                                            if($wpjobportal_default_selected_payment_method == '')
                                                                $wpjobportal_default_selected_payment_method = 2;
                                                        // }
                                                    }
                                                    if($wpjobportal_paymentconfig['isenabled_stripe'] == 1) { // stripe as a payment method is enabled
                                                        $wpjobportal_payment_methods_array[3] = '<img src="'. esc_url(WPJOBPORTAL_IMAGE).'/stripe.jpg" alt="'. esc_attr(__("stripe","wp-job-portal")).'" title="'. esc_attr(__("stripe","wp-job-portal")).'" /> '. esc_html(__('Stripe', 'wp-job-portal'));
                                                        if($wpjobportal_default_selected_payment_method == '')
                                                            $wpjobportal_default_selected_payment_method = 3;
                                                    }
                                                }else{ // payment is requied for job apply
                                                    $wpjobportal_show_proceed_to_payment_button = 1; // show proceed to payment button
                                                    $wpjobportal_hide_apply_btn = 1; // hide apply button
                                                }
                                            }
                                        }
                                    }
                                }
                                // job seeker case ended
                            }elseif (WPJOBPORTALincluder::getObjectClass('user')->isemployer()) { // employer case
                                echo '<div class="frontend error"><p>'.esc_html(__('You are logged in as employer.', 'wp-job-portal')).'</p></div>';
                                $wpjobportal_hide_apply_btn = 1;
                            }elseif (WPJOBPORTALincluder::getObjectClass('user')->isguest()) { // guest case
                                if($wpjobportal_show_quick_apply_form != 1){ // dont show buttons for quick apply form
                                    echo '<div class="frontend error"><p>'.esc_html(__('You are not a logged in member.', 'wp-job-portal')).'</p></div>';
                                    $wpjobportal_hide_apply_btn = 1;
                                    $wpjobportal_hide_login_and_apply_btn = 0; // show login and apply button
                                }
                            }else{ // wp user but not job portal user
                                echo '<div class="frontend error"><p>'.esc_html(__('You do not have any role.', 'wp-job-portal')).'</p></div>';
                                $wpjobportal_hide_apply_btn = 1;
                                $wpjobportal_hide_select_role_btn = 0; // show select role button
                            }

                            $wpjobportal_btn_visible = 0;
                            if($wpjobportal_hide_apply_btn == 0){
                                $wpjobportal_btn_label  = __('Apply Now', 'wp-job-portal');
                                // if payment method array is not empty show select package drop down and change button text
                                if(!empty($wpjobportal_payment_methods_array) && $wpjobportal_show_job_apply_redirect_link_only == 0){
                                    $wpjobportal_btn_label  = __('Proceed to payment', 'wp-job-portal');
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
                                    // button will remain submit for all three modes.(free, per listing, memebership)
                                    echo '<div class="wjportal-form-btn-wrp wjportal-apply-package-apply-now-button">
                                        '. wp_kses(WPJOBPORTALformfield::submitbutton('save', esc_html($wpjobportal_btn_label), array('class' => 'button wjportal-form-btn wjportal-save-btn')),WPJOBPORTAL_ALLOWED_TAGS).'
                                    </div>';
                                    $wpjobportal_btn_visible = 1;
                                }elseif($wpjobportal_show_job_apply_redirect_link_only == 1){
                                    echo '<div class="wjportal-form-btn-wrp wjportal-apply-package-apply-now-button">
                                     <a class="wjportal-login-to-apply-btn" href="'.esc_url($wpjobportal_job->joblink).'"  target="_blank">' . esc_html(__('Apply Now', 'wp-job-portal')).'</a>
                                    </div>';
                                    $wpjobportal_btn_visible = 1;
                                }
                            }
                            if($wpjobportal_hide_login_and_apply_btn == 0  && $wpjobportal_show_job_apply_redirect_link_only == 0){ // show login & apply button to visitor
                                $wpjobportal_redirect_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'viewjob', 'wpjobportalid'=>$wpjobportal_jobid));
                                $wpjobportal_redirect_url = wpjobportalphplib::wpJP_safe_encoding($wpjobportal_redirect_url);
                                $wpjobportal_login_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'wpjobportal', 'wpjobportallt'=>'login', 'wpjobportalredirecturl'=>$wpjobportal_redirect_url));

                                echo '<div class="wjportal-form-btn-wrp wjportal-login-to-apply-btn-wrap">
                                    <a href="'.esc_url($wpjobportal_login_link).'" target="_blank" class="wjportal-login-to-apply-btn" >'.esc_html__('Login', 'wp-job-portal').'</a>
                                </div>';
                                // show apply button to visitor
                                $wpjobportal_visitor_can_apply_to_job = wpjobportal::$_config->getConfigurationByConfigName('visitor_can_apply_to_job');
                                if($wpjobportal_visitor_can_apply_to_job == 1){
                                    echo '<div class="wjportal-job-apply-or-visitor">
                                        <span>'. esc_html(__("Or", "wp-job-portal")) .'</span>
                                    </div>';
                                    $wpjobportal_visitorapplylink = wp_nonce_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobapply', 'action'=>'wpjobportaltask', 'task'=>'jobapplyasvisitor', 'wpjobportalid-jobid'=>$wpjobportal_jobid)),'wpjobportal_job_apply_nonce') ;
                                    echo '<div class="wjportal-form-btn-wrp wjportal-apply-as-visitor-btn-wrap">
                                        <a href="'.esc_url($wpjobportal_visitorapplylink).'" class="wjportal-apply-as-visitor-btn" >'.esc_html__('Apply as visitor', 'wp-job-portal').'</a>
                                    </div>';
                                }
                                $wpjobportal_btn_visible = 1;
                            }

                            if($wpjobportal_hide_select_role_btn == 0){ // show select role btn to wordpress logged in user
                                $wpjobportal_select_role_link =  esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'common','wpjobportallt'=>'newinwpjobportal', 'wpjobportalid-jobid'=>$wpjobportal_jobid))) ;
                                echo '<div class="wjportal-form-btn-wrp wjportal-login-to-apply-btn-wrap">
                                    <a href="'.esc_url($wpjobportal_select_role_link).'" target="_blank" class="wjportal-login-to-apply-btn" >'.esc_html(__('Select Role', 'wp-job-portal')).'</a>
                                </div>';
                                $wpjobportal_btn_visible = 1;
                            }

                            if($wpjobportal_show_buy_package_btn == 1){ // show buy package button in case of memeber ship mode and no package
                                $wpjobportal_buy_packages_link =  esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'package','wpjobportallt'=>'packages'))) ;
                                echo '<div class="wjportal-form-btn-wrp wjportal-login-to-apply-btn-wrap">
                                    <a href="'.esc_url($wpjobportal_buy_packages_link).'" target="_blank" class="wjportal-login-to-apply-btn" >'.esc_html(__('Buy Package', 'wp-job-portal')).'</a>
                                </div>';
                                $wpjobportal_btn_visible = 1;
                            }
                            if($wpjobportal_show_proceed_to_payment_button == 1){ // show proceed to payment button to handle per listing mode (mainly stripe payment case)
                                $wpjobportal_buy_packages_link =  wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'purchasehistory','wpjobportallt'=>'payjobapply','wpjobportalid'=>$wpjobportal_jobid,'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()));
                                echo '<div class="wjportal-form-btn-wrp wjportal-login-to-apply-btn-wrap">
                                    <a href="'.esc_url($wpjobportal_buy_packages_link).'" target="_blank" class="wjportal-login-to-apply-btn" >'.esc_html(__('Proceed to payment', 'wp-job-portal')).'</a>
                                </div>';
                                $wpjobportal_btn_visible = 1;
                            }

                            if($wpjobportal_btn_visible == 0  && $wpjobportal_show_job_apply_redirect_link_only == 1){
                                echo '<div class="wjportal-form-btn-wrp wjportal-apply-package-apply-now-button">
                                 <a class="wjportal-login-to-apply-btn" href="'.esc_url($wpjobportal_job->joblink).'"  target="_blank">' . esc_html(__('Apply Now', 'wp-job-portal')).'</a>
                                </div>';
                                $wpjobportal_btn_visible = 1;
                            }


                            if($wpjobportal_btn_visible == 0 && $wpjobportal_force_hide_btn == 0 && $wpjobportal_hide_apply_btn == 0){ // show dummy btn if no button is shown
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
        // to handle captcha on quick apply form
        if($wpjobportal_captcha_quick_apply == 1){
            $wpjobportal_protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $wpjobportal_config_array = wpjobportal::$_config->getConfigByFor('captcha');
            if($wpjobportal_config_array['captcha_selection'] == 1 && $wpjobportal_config_array['recaptcha_privatekey'] ){
                wp_enqueue_script('wpjobportal-repaptcha-scripti', $wpjobportal_protocol . 'www.google.com/recaptcha/api.js');
            }
        }
    ?>
