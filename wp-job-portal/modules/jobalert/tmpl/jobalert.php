<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
?>
<div class="wjportal-main-up-wrapper">
<?php
if(in_array('credits', wpjobportal::$_active_addons) && wpjobportal::$_config->getConfigValue('submission_type') == 2 && isset(wpjobportal::$_data[0])){
    $wpjobportal_paymentconfig = wpjobportal::$_data['paymentconfig'];

    //Price Listing For Department--Currency
    $wpjobportal_price = wpjobportal::$_config->getConfigValue('job_jobalert_price_perlisting');
    $wpjobportal_currencyid = wpjobportal::$_config->getConfigValue('job_currency_jobalert_perlisting');
    $wpjobportal_decimals = WPJOBPORTALincluder::getJSModel('currency')->getDecimalPlaces($wpjobportal_currencyid);
    $wpjobportal_formattedPrice = wpjobportalphplib::wpJP_number_format($wpjobportal_price,$wpjobportal_decimals);
    //Price Listing For Department
    $wpjobportal_priceDepartmentlist = wpjobportal::$_common->getFancyPrice($wpjobportal_price,$wpjobportal_currencyid,array('decimal_places'=>$wpjobportal_decimals));
}
$wpjobportal_jobalertid = isset(wpjobportal::$_data[0]->id) ? wpjobportal::$_data[0]->id : '';
$status =isset(wpjobportal::$_data[0]->status) ? wpjobportal::$_data[0]->status : '';
$wpjobportal_google_recaptcha = false;
$wpjobportal_google_recaptcha_v3 = false;
if (wpjobportal::$_error_flag == null) {?>
        <div id="wjportal-popup-background"></div>
        <div id="wjportal-listpopup" class="wjportal-popup-wrp wjportal-job-alert-popup">
            <div class="wjportal-popup-cnt">
                <img id="wjportal-popup-close-btn" alt="<?php echo esc_attr__('Close', 'wp-job-portal'); ?>" src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL . 'includes/images/popup-close.png'); ?>">
                <div class="wjportal-popup-title">
                    <span class="wjportal-popup-title2">
                        <?php echo esc_html__('Unsubscribe job alert', 'wp-job-portal'); ?>
                    </span>
                </div>
                <div class="wjportal-popup-contentarea">
                    <form class="wjportal-form" method="post" action="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobalert', 'task'=>'unsubscribeJobAlertSettingEmail'))); ?>">
                        <div class="wjportal-popup-field-wrp">
                            <div class="wjportal-popup-field wjportal-popup-fullwidth">
                                <label>
                                    <?php echo esc_html__('Email Address', 'wp-job-portal'); ?> <font color="red">*</font>
                                </label>
                                <?php echo wp_kses(WPJOBPORTALformfield::text('emailaddress', '', array('class' => 'inputbox wjportal-form-input-field', 'data-validation' => 'email','placeholder'=> __('Email Address', 'wp-job-portal'))), WPJOBPORTAL_ALLOWED_TAGS) ?>
                            </div>
                        </div>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('action', 'jobalert_unsubscribeJobAlertSettingEmail'), WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('form_request', 'wpjobportal'), WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(WPJOBPORTALformfield::hidden('_wpnonce', wp_create_nonce('wpjobportal_job_alert_nonce')), WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <div class="wjportal-visitor-msg-btn-wrp" id="save-button">
                            <?php echo wp_kses(WPJOBPORTALformfield::submitbutton('save', __('Unsubscribe', 'wp-job-portal'), array('class' => 'button wjportal-visitor-msg-btn ')), WPJOBPORTAL_ALLOWED_TAGS); ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="wjportal-main-wrapper wjportal-clearfix">
            <div class="wjportal-page-header">
                <div class="wjportal-page-header-cnt">
                    <?php WPJOBPORTALincluder::getTemplate('templates/pagetitle',array('wpjobportal_module'=>'jobalert','wpjobportal_layout'=>'jobalert'));  ?>
                </div>
                <div class="wjportal-header-actions">
                    <div class="wjportal-act-btn-wrp">
                        <?php
                            /*Submission For Per Listing */
                            if(isset(wpjobportal::$_data[0]) && wpjobportal::$_config->getConfigValue('submission_type') == 2 && ($status != 1 && $status == 3)){
                                echo '<a class="wjportal-act-btn wjportan-canc-act-btn  wjportal-list-act-btn-cancel-payment" href='. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobseeker', 'wpjobportallt'=>'controlpanel'))).'>
                                            '. esc_html__('Cancel Payment', 'wp-job-portal').'
                                        </a>
                                        <button type="button" class="wjportal-act-btn wjportal-list-act-btn-proceed-payment" id="proceedPaymentBtn">
                                            '. esc_html(__('Proceed To Payment','wp-job-portal')).'
                                        </button>
                                       ';
                                } ?>
                        <?php
                            if (isset(wpjobportal::$_data[0]->id)) {
                                $wpjobportal_unsub_href = wp_nonce_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid(),'wpjobportalme'=>'jobalert', 'action'=>'wpjobportaltask', 'task'=>'unsubscribeJobAlertSetting', 'jobalertid'=>wpjobportal::$_data[0]->id)),'wpjobportal_job_alert_nonce');
                                $wpjobportal_unsub_onclick = 'return confirm(' . wp_json_encode(__('Are you sure to Unsubscribe job alert', 'wp-job-portal')) . ');';
                            } else {
                                $wpjobportal_unsub_href = '#';
                                $wpjobportal_unsub_onclick = 'showpopup();';
                            }
                        ?>
                        <?php // Any saved alert can be removed - pending payment or approval included, not only active ones. ?>
                        <?php if(!empty($wpjobportal_jobalertid)): ?>
                        <a class="wjportal-act-btn wjportal-unsubscribed-btn-wrp wjportal-list-act-btn-unsub-job-alert" href="<?php echo esc_url($wpjobportal_unsub_href); ?>" onclick="<?php echo esc_attr($wpjobportal_unsub_onclick); ?>" >
                            <?php echo esc_html__('Unsubscribe job alert', 'wp-job-portal'); ?>
                        </a>
                    <?php endif; ?>
                    </div>
                </div>
                <?php
                    if(!WPJOBPORTALincluder::getTemplate('templates/header',array('wpjobportal_module' => 'jobalert'))){
                        return;
                    }
                ?>
            </div>
            <?php if(isset(wpjobportal::$_data['package']) && wpjobportal::$_config->getConfigValue('submission_type')){
                    WPJOBPORTALincluder::getTemplate('views/package',array('module_name' => 'jobalert','packages' => wpjobportal::$_data['package']));
                }
            ?>

            <div class="wjportal-form-wrp wjportal-job-alert-form">
                <form class="wjportal-form" id="alert_form" method="post" action="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobalert', 'task'=>'savejobalert'))); ?>">

                    <?php
                        // --- DYNAMIC FORM FIELDS INTEGRATION START ---
                        // Reverted to raw insertion to match jobsearch layout directly inside the form tag
                        $wpjobportal_formfields = WPJOBPORTALincluder::getTemplate('jobalert/form-fields', array(
                            'wpjobportal_jobalert' => isset(wpjobportal::$_data[0]) ? wpjobportal::$_data[0] : null,
                            'module_name'=>'jobalert'
                        ));
                        foreach ($wpjobportal_formfields as $wpjobportal_formfield) {
                            WPJOBPORTALincluder::getTemplate('templates/form-field', $wpjobportal_formfield);
                        }
                        // --- DYNAMIC FORM FIELDS INTEGRATION END ---
                    ?>

                    <?php
                        $wpjobportal_config_array = wpjobportal::$_config->getConfigByFor('captcha');
                        if (!is_user_logged_in() && $wpjobportal_config_array['job_alert_captcha'] == 1) {
                                     if ($wpjobportal_config_array['captcha_selection'] == 1) {
                                            if($wpjobportal_config_array['recaptcha_version'] == 1){
                                            }else{
                                                $wpjobportal_google_recaptcha_v3 = true;
                                            }
                                        }
                              if($wpjobportal_google_recaptcha_v3 == false){
                              ?>
                              <div class="wjportal-form-row">
                                  <div class="wjportal-form-title">
                                      <?php echo esc_html__('Captcha', 'wp-job-portal'); ?>
                                  </div>

                                  <div class="wjportal-form-value">
                                      <?php
                                          if ($wpjobportal_config_array['captcha_selection'] == 1) {
                                              $wpjobportal_google_recaptcha = true;
                                              if($wpjobportal_config_array['recaptcha_version'] == 1){
                                                  ?>
                                                  <div class="g-recaptcha" data-sitekey="<?php echo esc_attr($wpjobportal_config_array['recaptcha_publickey']);?>"></div>
                                          <?php
                                              }
                                          } else {
                                              if (class_exists('WPJOBPORTAL_Captcha_Service')) {
                                                  WPJOBPORTAL_Captcha_Service::output();
                                              }
                                          }
                                      ?>
                                  </div>
                              </div>
                            <?php } ?>
                    <?php } ?>

                    <?php echo wp_kses(WPJOBPORTALformfield::hidden('id', isset(wpjobportal::$_data[0]->id) ? wpjobportal::$_data[0]->id : '' ), WPJOBPORTAL_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(WPJOBPORTALformfield::hidden('status', isset(wpjobportal::$_data[0]->status) ? wpjobportal::$_data[0]->status : '' ), WPJOBPORTAL_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(WPJOBPORTALformfield::hidden('uid', WPJOBPORTALincluder::getObjectClass('user')->uid()), WPJOBPORTAL_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(WPJOBPORTALformfield::hidden('created', isset(wpjobportal::$_data[0]->created) ? wpjobportal::$_data[0]->created : gmdate('Y-m-d H:i:s')), WPJOBPORTAL_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(WPJOBPORTALformfield::hidden('creditid', ''), WPJOBPORTAL_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(WPJOBPORTALformfield::hidden('action', 'job_savejob'), WPJOBPORTAL_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(WPJOBPORTALformfield::hidden('wpjobportalpageid', get_the_ID()), WPJOBPORTAL_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(WPJOBPORTALformfield::hidden('form_request', 'wpjobportal'), WPJOBPORTAL_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(WPJOBPORTALformfield::hidden('upakid', isset(wpjobportal::$_data['package']) ? wpjobportal::$_data['package']->id : 0), WPJOBPORTAL_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(WPJOBPORTALformfield::hidden('_wpnonce', wp_create_nonce('wpjobportal_job_alert_nonce')), WPJOBPORTAL_ALLOWED_TAGS); ?>

                    <div class="wjportal-form-btn-wrp" id="save-button">
                        <?php
                        if($wpjobportal_google_recaptcha_v3 == false){
                            echo wp_kses(WPJOBPORTALformfield::submitbutton('save', __('Save Job Alert','wp-job-portal'), array('class' => 'button wjportal-form-btn wjportal-save-btn')), WPJOBPORTAL_ALLOWED_TAGS);
                        }else{
                            echo wp_kses(WPJOBPORTALformfield::submitbutton('save', __('Save Job Alert','wp-job-portal'), array('class' => 'button wjportal-form-btn wjportal-save-btn g-recaptcha','data-sitekey'=> esc_attr($wpjobportal_config_array['recaptcha_publickey']), "data-callback"=>'onSubmit', "data-action"=>'submit')), WPJOBPORTAL_ALLOWED_TAGS);
                        }
                        ?>
                    </div>
                </form>
            </div>
        </div>
        <?php
            if(in_array('credits', wpjobportal::$_active_addons) && wpjobportal::$_config->getConfigValue('submission_type') == 2 && isset(wpjobportal::$_data[0])){
                do_action('wpjobportal_addons_perlisting_payment',$wpjobportal_paymentconfig,$wpjobportal_jobalertid,'listingpaypalJobAlert','job_jobalert_price_perlisting','listingstripeJobAlert','jobtitle',wpjobportal::$_data[0]->name,$wpjobportal_price,$wpjobportal_currencyid,'Job');
            }
    } else {
        echo wp_kses(wpjobportal::$_error_flag_message, WPJOBPORTAL_ALLOWED_TAGS);
    }
  if(WPJOBPORTALincluder::getObjectClass('user')->isguest()){
            $wpjobportal_protocol = is_ssl() ? "https://" : "http://";
            $wpjobportal_config_array = wpjobportal::$_config->getConfigByFor('captcha');
            if($wpjobportal_config_array['captcha_selection'] == 1 && $wpjobportal_config_array['recaptcha_privatekey'] ){
                wp_enqueue_script( 'wpjobportal-repaptcha-script', $wpjobportal_protocol . 'www.google.com/recaptcha/api.js', array(), WPJOBPORTAL_VERSION, false );
            }
        }
  ?>
</div>
