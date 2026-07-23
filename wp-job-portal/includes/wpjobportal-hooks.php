<?php
if (!defined('ABSPATH'))
    die('Restricted Access');


// Updates login failed to send user back to the custom form with a query var
add_action( 'wp_login_failed', 'wpjobportal_login_failed', 10, 2 );
// Updates authentication to return an error when one field or both are blank
add_filter( 'authenticate', 'wpjobportal_authenticate_username_password', 30, 3);

function wpjobportal_login_failed( $wpjobportal_username ){
    $referrer = wp_get_referer();
    if ( $referrer && ! wpjobportalphplib::wpJP_strstr($referrer, 'wp-login') && ! wpjobportalphplib::wpJP_strstr($referrer, 'wp-admin') ){
        $wpjobportal_submit = WPJOBPORTALrequest::getVar('wp-submit','post','');
        if ($wpjobportal_submit != ''){
            $wpjobportal_key = WPJOBPORTALincluder::getJSModel('user')->getMessagekey();
            WPJOBPORTALMessages::setLayoutMessage(esc_html(__('Username / password is incorrect',"wp-job-portal")), 'error',$wpjobportal_key);
            $referrer=wpjobportal::wpjobportal_makeUrl(array('wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid(),'wpjobportalme'=>'wpjobportal','wpjobportallt'=>'login'));
            wp_redirect($referrer);
            exit;
        }else{
            return;
        }
    }
}

/**
* Commit For Zub
**/
function wpjobportal_authenticate_username_password( $wpjobportal_user, $wpjobportal_username, $password ){
    if ( is_a($wpjobportal_user, 'WP_User') ) {
        return $wpjobportal_user;
    }
    $wp_submit = WPJOBPORTALrequest::getVar('wp-submit','post','');
    $pwd = WPJOBPORTALrequest::getVar('pwd','post','');
    $wpjobportal_log = WPJOBPORTALrequest::getVar('log','post','');

    if ($wp_submit != '' && $pwd != '' && $wpjobportal_log != ''){
        return false;
    }
    return $wpjobportal_user;
}



add_action('admin_head', 'wpjobportal_custom_css_add');

function wpjobportal_custom_css_add() {
    wp_enqueue_style('wpjobportal-menu-style', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/adminmenu.css');
}

/**
 * Build and validate a signed registration shortcode context.
 *
 * The signed value lets the registration handler reliably enforce the role and
 * optional redirect selected by the shortcode without trusting editable hidden fields.
 */
function wpjobportal_create_registration_shortcode_context($wpjobportal_role, $wpjobportal_lock_role = false, $wpjobportal_redirect_url = '') {
    $wpjobportal_role = (int) $wpjobportal_role;
    if (!in_array($wpjobportal_role, array(1, 2), true)) {
        return '';
    }

    $wpjobportal_payload = array(
        'role' => $wpjobportal_role,
        'lock_role' => $wpjobportal_lock_role ? 1 : 0,
        'redirect_url' => wp_validate_redirect(esc_url_raw($wpjobportal_redirect_url), ''),
        'expires' => time() + DAY_IN_SECONDS,
    );
    $wpjobportal_json = wp_json_encode($wpjobportal_payload);
    if (!$wpjobportal_json) {
        return '';
    }

    $wpjobportal_encoded = rtrim(strtr(base64_encode($wpjobportal_json), '+/', '-_'), '=');
    $wpjobportal_signature = hash_hmac('sha256', $wpjobportal_encoded, wp_salt('nonce'));
    return $wpjobportal_encoded . '.' . $wpjobportal_signature;
}

function wpjobportal_get_registration_shortcode_context() {
    static $wpjobportal_context_loaded = false;
    static $wpjobportal_context = array();

    if ($wpjobportal_context_loaded) {
        return $wpjobportal_context;
    }
    $wpjobportal_context_loaded = true;

    $wpjobportal_token = WPJOBPORTALrequest::getVar('wpjobportal_registration_context', 'post', '');
    if (!is_string($wpjobportal_token) || strpos($wpjobportal_token, '.') === false) {
        return $wpjobportal_context;
    }

    list($wpjobportal_encoded, $wpjobportal_signature) = explode('.', $wpjobportal_token, 2);
    $wpjobportal_expected_signature = hash_hmac('sha256', $wpjobportal_encoded, wp_salt('nonce'));
    if (!hash_equals($wpjobportal_expected_signature, $wpjobportal_signature)) {
        return $wpjobportal_context;
    }

    $wpjobportal_padding = strlen($wpjobportal_encoded) % 4;
    if ($wpjobportal_padding) {
        $wpjobportal_encoded .= str_repeat('=', 4 - $wpjobportal_padding);
    }
    $wpjobportal_json = base64_decode(strtr($wpjobportal_encoded, '-_', '+/'), true);
    $wpjobportal_data = json_decode((string) $wpjobportal_json, true);
    if (!is_array($wpjobportal_data)) {
        return $wpjobportal_context;
    }

    $wpjobportal_role = isset($wpjobportal_data['role']) ? (int) $wpjobportal_data['role'] : 0;
    $wpjobportal_expires = isset($wpjobportal_data['expires']) ? (int) $wpjobportal_data['expires'] : 0;
    if (!in_array($wpjobportal_role, array(1, 2), true) || $wpjobportal_expires < time()) {
        return $wpjobportal_context;
    }

    $wpjobportal_context = array(
        'role' => $wpjobportal_role,
        'lock_role' => !empty($wpjobportal_data['lock_role']) ? 1 : 0,
        'redirect_url' => !empty($wpjobportal_data['redirect_url'])
            ? wp_validate_redirect(esc_url_raw($wpjobportal_data['redirect_url']), '')
            : '',
    );
    return $wpjobportal_context;
}

function wpjobportal_get_registration_role() {
    $wpjobportal_context = wpjobportal_get_registration_shortcode_context();
    if (!empty($wpjobportal_context['lock_role'])) {
        return (int) $wpjobportal_context['role'];
    }

    return wpjobportal::wpjobportal_sanitizeData(WPJOBPORTALrequest::getVar('jobs_role', 'post', ''));
}

// --------------------------WP registration from fields --------
// 1. wp register form extra field
add_action('register_form', 'wpjobportal_add_registration_fields');
add_action('wpjobportal_register_form', 'wpjobportal_add_registration_fields');

function wpjobportal_add_registration_fields() {
    $wpjobportal_shortcode_options = isset(wpjobportal::$_data['shortcode_options']) && is_array(wpjobportal::$_data['shortcode_options'])
        ? wpjobportal::$_data['shortcode_options']
        : array();
    $wpjobportal_is_registration_shortcode = !empty($wpjobportal_shortcode_options['registration_shortcode']);
    $wpjobportal_hide_role = $wpjobportal_is_registration_shortcode && !empty($wpjobportal_shortcode_options['hide_role']);
    $wpjobportal_shortcode_role = $wpjobportal_is_registration_shortcode && isset($wpjobportal_shortcode_options['registration_role'])
        ? (int) $wpjobportal_shortcode_options['registration_role']
        : 0;

    if ($wpjobportal_hide_role && in_array($wpjobportal_shortcode_role, array(1, 2), true)) {
        $wpjobportal_context = wpjobportal_create_registration_shortcode_context(
            $wpjobportal_shortcode_role,
            true,
            isset($wpjobportal_shortcode_options['redirect_url']) ? $wpjobportal_shortcode_options['redirect_url'] : ''
        );
        ?>
        <input type="hidden" name="jobs_role" value="<?php echo esc_attr($wpjobportal_shortcode_role); ?>" />
        <input type="hidden" name="jobs_notfromourform" value="1" />
        <input type="hidden" name="wpjobportal_registration_context" value="<?php echo esc_attr($wpjobportal_context); ?>" />
        <?php
        return;
    }

    ?>
    <div class="wjportal-form-title">
        <label for="jobs_role">
            <?php echo esc_html(__('Jobs Role', 'wp-job-portal')); ?>
        </label>
    </div>
    <div class="wjportal-form-value">
        <?php
        $wpjobportal_empflag = wpjobportal::$_config->getConfigurationByConfigName('disable_employer');
        $wpjobportal_showemployerlink = wpjobportal::$_config->getConfigurationByConfigName('showemployerlink');
        if ($wpjobportal_empflag == 1 && $wpjobportal_showemployerlink == 1) {
            $wpjobportal_selected_role = in_array($wpjobportal_shortcode_role, array(1, 2), true) ? $wpjobportal_shortcode_role : 0;
            ?>
            <select id="jobs_role" name="jobs_role" class="input form-control wjportal-form-select-field">
                <option value="0"<?php selected($wpjobportal_selected_role, 0); ?>><?php echo esc_html(__('Select Role', 'wp-job-portal')); ?></option>
                <option value="1"<?php selected($wpjobportal_selected_role, 1); ?>><?php echo esc_html(__('Employer', 'wp-job-portal')); ?></option>
                <option value="2"<?php selected($wpjobportal_selected_role, 2); ?>><?php echo esc_html(__('Job seeker', 'wp-job-portal')); ?></option>
            </select>
            <?php
        } else {
            ?>
            <div class="wjportal-form-text">
                <input type="hidden" name="jobs_role" value="2" />
                <?php echo esc_html(__('Job seeker', 'wp-job-portal')); ?>
            </div>
            <?php
        }
        ?>
    </div>
    <input type="hidden" name="jobs_notfromourform" value="1" />
    <?php

    if ($wpjobportal_is_registration_shortcode && in_array($wpjobportal_shortcode_role, array(1, 2), true)) {
        $wpjobportal_context = wpjobportal_create_registration_shortcode_context(
            $wpjobportal_shortcode_role,
            false,
            isset($wpjobportal_shortcode_options['redirect_url']) ? $wpjobportal_shortcode_options['redirect_url'] : ''
        );
        ?>
        <input type="hidden" name="wpjobportal_registration_context" value="<?php echo esc_attr($wpjobportal_context); ?>" />
        <?php
    }
}

//2. Add validation. In this case, we make sure jobs_role is required
add_filter('registration_errors', 'wpjobportal_registration_errors', 10, 3);

function wpjobportal_registration_errors($errors, $wpjobportal_sanitized_user_login, $wpjobportal_user_email) {
    $wpjobportal_jobs_role = wpjobportal_get_registration_role();
    if ($wpjobportal_jobs_role == 0) {

        $errors->add('user_role_error','<strong>'.esc_html(__("Error","wp-job-portal")).'</strong>:'. esc_html(__('You must set jobs user role', 'wp-job-portal')).'.');
    }

    return $errors;
}

// 3. wp register form extra field get and set to user meta
add_action('user_register', 'wpjobportal_registration_save', 10, 1);

function wpjobportal_registration_save($wpjobportal_user_id) {
    $wpjobportal_jobs_role = wpjobportal_get_registration_role();
    $wpjobportal_jobs_register_nonce = WPJOBPORTALrequest::getVar('wpjobportal_jobs_register_nonce','post','');
    if ($wpjobportal_jobs_role != '' && $wpjobportal_jobs_register_nonce != '' && !wp_verify_nonce($wpjobportal_jobs_register_nonce, 'wpjobportal-jobs-register-nonce') ) {
        $wpjobportal_role = wpjobportal_get_registration_role();
        $wpjobportal_user_email = sanitize_email(WPJOBPORTALrequest::getVar('wpjobportal_user_email'));
        if (is_numeric($wpjobportal_role)) {
            $wpjobportal_empflag  = wpjobportal::$_config->getConfigurationByConfigName('disable_employer');
            $wpjobportal_allow_reg_as_emp  = wpjobportal::$_config->getConfigurationByConfigName('showemployerlink');
            if($wpjobportal_empflag == 0 || $wpjobportal_allow_reg_as_emp == 0){
                $wpjobportal_role = 2;
            }
            if ($wpjobportal_role == 1) {
                update_user_meta($wpjobportal_user_id, 'jobs_role', 'employer');
                $wpjobportal_employer_defaultgroup = wpjobportal::$_config->getConfigurationByConfigName('employer_defaultgroup');
                wp_update_user(array('ID' => $wpjobportal_user_id, 'role' => $wpjobportal_employer_defaultgroup));
            } elseif ($wpjobportal_role == 2) {
                update_user_meta($wpjobportal_user_id, 'jobs_role', 'jobseeker');
                $wpjobportal_jobseeker_defaultgroup = wpjobportal::$_config->getConfigurationByConfigName('jobseeker_defaultgroup');
                wp_update_user(array('ID' => $wpjobportal_user_id, 'role' => $wpjobportal_jobseeker_defaultgroup));
            }
            $wpjobportal_jobs_notfromourform = WPJOBPORTALrequest::getVar('jobs_notfromourform','post','');
            if ( $wpjobportal_jobs_notfromourform == 1) {
                $wpjobportal_nickname = get_user_meta($wpjobportal_user_id, 'nickname', true);

                $wpjobportal_row = WPJOBPORTALincluder::getJSTable('users');
                $wpjobportal_data['uid'] = $wpjobportal_user_id;
                $wpjobportal_data['roleid'] = $wpjobportal_role;
                $wpjobportal_data['first_name'] = $wpjobportal_nickname;
                $wpjobportal_data['emailaddress'] = $wpjobportal_user_email;
                $wpjobportal_data['status'] = 1;
                $wpjobportal_data['created'] = date_i18n('Y-m-d H:i:s');
                $wpjobportal_data = wpjobportal::wpjobportal_sanitizeData($wpjobportal_data);
                if (!$wpjobportal_row->bind($wpjobportal_data)) {
                    echo esc_html(WPJOBPORTAL_SAVE_ERROR);
                }
                if (!$wpjobportal_row->store()) {
                    echo esc_html(WPJOBPORTAL_SAVE_ERROR);
                }
                WPJOBPORTALincluder::getJSModel('emailtemplate')->sendMail(6,$wpjobportal_role,$wpjobportal_row->id); // 6 for regesitration $wpjobportal_role for role jobseeker and employer
            }
        }
    }
}

// ------------------- wpjobportal registrationFrom request handler--------
// register a new user
function wpjobportal_add_new_member() {
    $wpjobportal_registration_context = wpjobportal_get_registration_shortcode_context();
    $wpjobportal_user_login  = WPJOBPORTALrequest::getVar('wpjobportal_user_login','post','');
    $wpjobportal_jobs_register_nonce  = WPJOBPORTALrequest::getVar('wpjobportal_jobs_register_nonce','post','');
    if ($wpjobportal_user_login != '' && $wpjobportal_jobs_register_nonce != '' && wp_verify_nonce($wpjobportal_jobs_register_nonce, 'wpjobportal-jobs-register-nonce')) {
        $wpjobportal_user_login = sanitize_user(WPJOBPORTALrequest::getVar("wpjobportal_user_login"));
        $wpjobportal_user_email = sanitize_email(WPJOBPORTALrequest::getVar('wpjobportal_user_email'));
        $wpjobportal_user_first = sanitize_text_field(WPJOBPORTALrequest::getVar("wpjobportal_user_first"));
        $wpjobportal_user_last = sanitize_text_field(WPJOBPORTALrequest::getVar("wpjobportal_user_last"));
        $wpjobportal_user_pass = wpjobportal::wpjobportal_sanitizeData(WPJOBPORTALrequest::getVar("wpjobportal_user_pass") );
        if(!empty($_FILES['photo'])){
            $wpjobportal_photo = sanitize_file_name($_FILES['photo']['name']);
        }else{
            $wpjobportal_photo = '';
        }
        $pass_confirm = wpjobportal::wpjobportal_sanitizeData(WPJOBPORTALrequest::getVar("wpjobportal_user_pass_confirm") );

        // this is required for username checks

        $wpjobportal_fieldslist = wpjobportal::$_wpjpfieldordering->getFieldsOrderingforForm(4);
        if ($wpjobportal_user_login == '' && $wpjobportal_fieldslist['wpjobportal_user_login']->required == 1) {
            // empty username
            wpjobportal_errors()->add('username_empty', esc_html(wpjobportal::wpjobportal_getVariableValue('Please enter a '.$wpjobportal_fieldslist['wpjobportal_user_login']->fieldtitle)));
        } elseif ($wpjobportal_user_login == '' && $wpjobportal_fieldslist['wpjobportal_user_login']->required == 0) {
            $wpjobportal_user_login = $wpjobportal_user_email;
        }
        if (username_exists($wpjobportal_user_login)) {
            // Username already registered
            wpjobportal_errors()->add('username_unavailable', wpjobportal::wpjobportal_getVariableValue($wpjobportal_fieldslist['wpjobportal_user_login']->fieldtitle).' already taken');
        }
        if (!validate_username($wpjobportal_user_login)) {
            // invalid username
            wpjobportal_errors()->add('username_invalid', esc_html(wpjobportal::wpjobportal_getVariableValue('Invalid '.$wpjobportal_fieldslist['wpjobportal_user_login']->fieldtitle)));
        }

        if ($wpjobportal_user_first == ''  && $wpjobportal_fieldslist['wpjobportal_user_first']->required == 1) {
            // empty first name
            wpjobportal_errors()->add('firstname_empty', esc_html(wpjobportal::wpjobportal_getVariableValue('Please enter a '.$wpjobportal_fieldslist['wpjobportal_user_first']->fieldtitle)));
        }

        if ($wpjobportal_user_last == ''  && $wpjobportal_fieldslist['wpjobportal_user_last']->required == 1) {
            // empty last name
            wpjobportal_errors()->add('lastname_empty', esc_html(wpjobportal::wpjobportal_getVariableValue('Please enter a '.$wpjobportal_fieldslist['wpjobportal_user_last']->fieldtitle)));
        }
        // should not be required
        // if ($wpjobportal_photo == ''  && isset($wpjobportal_fieldslist['photo']) && $wpjobportal_fieldslist['photo']->required == 1) {
        //     // empty last name
        //     wpjobportal_errors()->add('photo_empty', esc_html(wpjobportal::wpjobportal_getVariableValue('Please enter a '.$wpjobportal_fieldslist['photo']->fieldtitle)));
        // }
        if (!is_email($wpjobportal_user_email)) {
            //invalid email
            wpjobportal_errors()->add('email_invalid', esc_html(wpjobportal::wpjobportal_getVariableValue('Invalid '.$wpjobportal_fieldslist['wpjobportal_user_email']->fieldtitle)));
        }
        if (email_exists($wpjobportal_user_email)) {
            //Email address already registered
            wpjobportal_errors()->add('email_used', wpjobportal::wpjobportal_getVariableValue($wpjobportal_fieldslist['wpjobportal_user_email']->fieldtitle.' already registered'));
        }
        if ($wpjobportal_user_pass == '') {
            // passwords do not match
            wpjobportal_errors()->add('password_empty', esc_html(__('Please enter a password', 'wp-job-portal')));
        }
        if ($wpjobportal_user_pass != $pass_confirm) {
            // passwords do not match
            wpjobportal_errors()->add('password_mismatch', esc_html(__('Passwords do not match', 'wp-job-portal')));
        }

        foreach ($wpjobportal_fieldslist AS $wpjobportal_field) {
            if($wpjobportal_field->isuserfield == 1 && $wpjobportal_field->required == 1) {
                $wpjobportal_field_value = WPJOBPORTALrequest::getVar($wpjobportal_field->field,'post','');
                if ($wpjobportal_field_value != '') {
                    $cf_data = $wpjobportal_field_value;
                }
                if (empty($cf_data)) {
                    wpjobportal_errors()->add($wpjobportal_field->fieldtitle.'_empty', esc_html(wpjobportal::wpjobportal_getVariableValue('Please enter a '.$wpjobportal_field->fieldtitle)));
                }
            }
        }

        $wpjobportal_config_array = wpjobportal::$_config->getConfigByFor('captcha');
        if ($wpjobportal_config_array['cap_on_reg_form'] == 1) {
            if ($wpjobportal_config_array['captcha_selection'] == 1) { // Google recaptcha

                $gresponse = wpjobportal::wpjobportal_sanitizeData(WPJOBPORTALrequest::getVar('g-recaptcha-response','post'));
                $resp = wpjobportal_googleRecaptchaHTTPPost($wpjobportal_config_array['recaptcha_privatekey'] , $gresponse);
                if (! $resp) {
                    wpjobportal_errors()->add('invalid_captcha', esc_html(__('Invalid captcha', 'wp-job-portal')));
                }
            } else { // own captcha
                $wpjobportal_captcha = new WPJOBPORTALcaptcha;
                $wpjobportal_result = $wpjobportal_captcha->checkCaptchaUserForm();
                if ($wpjobportal_result != 1) {
                    wpjobportal_errors()->add('invalid_captcha', esc_html(__('Invalid captcha', 'wp-job-portal')));
                }
            }
        }

        $errors = wpjobportal_errors()->get_error_messages();

        // only create the user in if there are no errors
        if (empty($errors)) {

            $wperrors = register_new_user(  $wpjobportal_user_login,  $wpjobportal_user_email );
            $wpjobportal_new_user_id = "";
            if (!is_wp_error($wperrors)) {
                $wpjobportal_new_user_id = $wperrors;
                if ( $wpjobportal_user_first && $wpjobportal_user_last ) {
                    $wpjobportal_display_name = wpjobportal::wpjobportal_getVariableValue( $wpjobportal_user_first.' '.$wpjobportal_user_last);
                } elseif ( $wpjobportal_user_first ) {
                    $wpjobportal_display_name = $wpjobportal_user_first;
                } elseif ( $wpjobportal_user_last ) {
                    $wpjobportal_display_name = $wpjobportal_user_last;
                } else {
                    $wpjobportal_display_name = $wpjobportal_user_login;
                }
                //update_user_option( $wpjobportal_new_user_id, 'default_password_nag', false, true );
                wp_set_password( $wpjobportal_user_pass, $wpjobportal_new_user_id );
                update_user_option( $wpjobportal_new_user_id, 'first_name', $wpjobportal_user_first, true );
                update_user_option( $wpjobportal_new_user_id, 'last_name', $wpjobportal_user_last, true );
                wp_update_user( array ('ID' => $wpjobportal_new_user_id,  'display_name' => $wpjobportal_display_name) ) ;
            } else {
                wpjobportal_errors()->add('email_invalid', $wperrors->get_error_message());
            }
            if ($wpjobportal_new_user_id) {
                // send an email to the admin alerting them of the registration
                wp_new_user_notification($wpjobportal_new_user_id);
                // log the new user in
                wp_set_current_user($wpjobportal_new_user_id, $wpjobportal_user_login);
                wp_set_auth_cookie($wpjobportal_new_user_id);
                //do_action('wp_login', $wpjobportal_user_login);

                $wpjobportal_role = wpjobportal_get_registration_role();

                if (is_numeric($wpjobportal_role)) {
                    if ($wpjobportal_role == 1) {
                        update_user_meta($wpjobportal_new_user_id, 'jobs_role', 'employer');
                        $wpjobportal_employer_defaultgroup = wpjobportal::$_config->getConfigurationByConfigName('employer_defaultgroup');
                        wp_update_user(array('ID' => $wpjobportal_new_user_id, 'role' => $wpjobportal_employer_defaultgroup));
                    } elseif ($wpjobportal_role == 2) {
                        update_user_meta($wpjobportal_new_user_id, 'jobs_role', 'jobseeker');
                        $wpjobportal_jobseeker_defaultgroup = wpjobportal::$_config->getConfigurationByConfigName('jobseeker_defaultgroup');
                        wp_update_user(array('ID' => $wpjobportal_new_user_id, 'role' => $wpjobportal_jobseeker_defaultgroup));
                    }
                }

                // insert entry into out db also
                $wpjobportal_userrole = get_user_meta($wpjobportal_new_user_id, 'jobs_role', true);
                $wpjobportal_url = '';
                $wpjobportal_msguserrole = $wpjobportal_userrole;
                if ($wpjobportal_userrole == 'employer') {
                    $wpjobportal_userrole = 1;
                    $wpjobportal_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'employer', 'wpjobportallt'=>'controlpanel',"wpjobportalpageid"=>wpjobportal::wpjobportal_getPageid()));

                } elseif ($wpjobportal_userrole == 'jobseeker') {
                    $wpjobportal_userrole = 2;
                    $wpjobportal_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobseeker', 'wpjobportallt'=>'controlpanel',"wpjobportalpageid"=>wpjobportal::wpjobportal_getPageid()));
                }
                    $wpjobportal_row = WPJOBPORTALincluder::getJSTable('users');
                    $wpjobportal_data['uid'] = $wpjobportal_new_user_id;
                    $wpjobportal_data['roleid'] = $wpjobportal_userrole;
                    $wpjobportal_data['first_name'] = $wpjobportal_user_first;
                    $wpjobportal_data['last_name'] = $wpjobportal_user_last;
                    $wpjobportal_data['emailaddress'] = $wpjobportal_user_email;
                    $wpjobportal_data['photo'] = $wpjobportal_photo;
                    $wpjobportal_data['status'] = 1;
                    $wpjobportal_data['created'] = date_i18n('Y-m-d H:i:s');
                    $wpjobportal_data = wpjobportal::wpjobportal_sanitizeData($wpjobportal_data);
                    $wpjobportal_key = WPJOBPORTALincluder::getJSModel($wpjobportal_msguserrole)->getMessagekey();
                    if (!$wpjobportal_row->bind($wpjobportal_data)) {
                        WPJOBPORTALMessages::setLayoutMessage(esc_html(__('Error Updating User', 'wp-job-portal')), 'error',$wpjobportal_key);
                    }
                    if (!$wpjobportal_row->store()) {
                        WPJOBPORTALMessages::setLayoutMessage(esc_html(__('Error Updating User', 'wp-job-portal')), 'error',$wpjobportal_key);
                    }else{
                        $wpjobportal_data = WPJOBPORTALrequest::get('post');
                        WPJOBPORTALincluder::getObjectClass('customfields')->storeCustomFields(4,$wpjobportal_row->id,$wpjobportal_data);
                    }
                    ////Store Image In Folder Of jobeeseker
                    if (isset($_FILES['photo']['size']) && $_FILES['photo']['size'] > 0) {
                        $objectid = $wpjobportal_row->uid;
                        wpjobportal_uploadPhoto($objectid);
                    }
                    //Auto Assign User Package's
                    do_action('wpjobportal_addons_credit_auto_asign_pkg',$wpjobportal_row);

                    WPJOBPORTALincluder::getJSModel('emailtemplate')->sendMail(6,$wpjobportal_userrole,$wpjobportal_row->id); // 6 for regesitration $wpjobportal_role for role jobseeker and employer
                    $wpjobportal_nickname = $wpjobportal_user_first . ' ' . $wpjobportal_user_last;

                    $wpjobportal_pageid = wpjobportal::$_config->getConfigurationByConfigName('register_jobseeker_redirect_page');

                    if($wpjobportal_userrole == 1){
                        $wpjobportal_pageid = wpjobportal::$_config->getConfigurationByConfigName('register_employer_redirect_page');
                    }elseif($wpjobportal_userrole == 2){
                        $wpjobportal_pageid = wpjobportal::$_config->getConfigurationByConfigName('register_jobseeker_redirect_page');
                    }
                    WPJOBPORTALMessages::setLayoutMessage(esc_html(__('User has been successfully created', 'wp-job-portal')), 'updated',$wpjobportal_key);
                    // $wpjobportal_url = home_url();
                    if (empty($wpjobportal_registration_context['redirect_url']) && is_numeric($wpjobportal_pageid) && $wpjobportal_pageid > 0) {
                        if (get_post_status($wpjobportal_pageid) == 'publish') {
                            if ($wpjobportal_userrole == 1) {
                                $wpjobportal_setRegisterLinkEmploye = wpjobportal::$_config->getConfigurationByConfigName('employe_set_register_link');
                                $customeRegisterLinkForEmploye = wpjobportal::$_config->getConfigurationByConfigName('employe_register_link');
                                if ($wpjobportal_setRegisterLinkEmploye == 2) {
                                    wp_redirect(esc_url($customeRegisterLinkForEmploye)); // to handle the case of invalid url showing error,
                                    exit;
                                } else {
                                    $wpjobportal_url = get_the_permalink($wpjobportal_pageid);
                                }
                            } elseif ($wpjobportal_userrole == 2) {
                                $wpjobportal_setRegisterLinkJobSeeker = wpjobportal::$_config->getConfigurationByConfigName('jobseeker_set_register_link');
                                $customeRegisterLinkForJobSeeker = wpjobportal::$_config->getConfigurationByConfigName('jobseeker_register_link');
                                if ($wpjobportal_setRegisterLinkJobSeeker == 2) {
                                    wp_redirect(esc_url($customeRegisterLinkForJobSeeker)); // to handle the case of invalid url showing error,
                                    exit;
                                } else {
                                    $wpjobportal_url = get_the_permalink($wpjobportal_pageid);
                                }
                            }
                        }
                    }
                    if (!empty($wpjobportal_registration_context['redirect_url'])) {
                        wp_safe_redirect($wpjobportal_registration_context['redirect_url']);
                    } else {
                        wp_redirect($wpjobportal_url);
                    }
                    exit;


            }
        }
    }
}

add_action('init', 'wpjobportal_add_new_member');
// Store Photo For Job seekser
    function wpjobportal_uploadPhoto($wpjobportal_id) {
        WPJOBPORTALincluder::getObjectClass('uploads')->uploadJobSeekerPhoto($wpjobportal_id);
        return;
    }
// used for tracking error messages
function wpjobportal_errors() {
    static $wp_error; // Will hold global variable safely
    return isset($wp_error) ? $wp_error : ($wp_error = new WP_Error(null, null, null));
}

// displays error messages from form submissions
function wpjobportal_show_error_messages() {
    if ($codes = wpjobportal_errors()->get_error_codes()) {
        echo '<div class="wpjobportal_errors">';
        // Loop error codes and display errors
        $alert_class = 'danger';
        $wpjobportal_img_name = 'job-alert-unsuccessful.png';
        foreach ($codes as $code) {
            $message = wpjobportal_errors()->get_error_message($code);
            if(wpjobportal::$wpjobportal_theme_chk  != 0){
                echo '<div class="alert alert-' . esc_attr($alert_class) . '" role="alert" id="autohidealert">
                    <img class="leftimg" src="'.esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/'.esc_attr($wpjobportal_img_name).'" />
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    '. esc_html($message) . '
                </div>';
            }else{
                echo '<div class="frontend error"><p>' . esc_html($message) . '</p></div>';
            }

        }
        echo '</div>';
    }
}

// ---------------Remove wp user ---------------

function wpjobportal_remove_jobs_user($wpjobportal_user_id) {
    //$wpjobportal_userrole = get_user_meta( $wpjobportal_new_user_id, 'jobs_role', true );

    $wpjobportal_js_model = WPJOBPORTALincluder::getJSModel('user');
    $wpjobportal_userrole = $wpjobportal_js_model->getUserRoleByWPUid($wpjobportal_user_id);
    $wpjobportal_userid = $wpjobportal_js_model->getUserIDByWPUid($wpjobportal_user_id);
    $wpjobportal_delete_option = WPJOBPORTALrequest::getVar('delete_option','post','');
    if ($wpjobportal_delete_option == 'delete') {
        $wpjobportal_result = $wpjobportal_js_model->enforceDeleteOurUser($wpjobportal_userid, $wpjobportal_userrole);
        if ($wpjobportal_result) {

        } else {

        }
    }
}

add_action('delete_user', 'wpjobportal_remove_jobs_user');

// visual composer hooks

add_action( 'vc_before_init', 'wpjobportal_job_portalvcSetAsTheme' );
function wpjobportal_job_portalvcSetAsTheme() {
    if(wpjobportal::$wpjobportal_theme_chk == 1){
        vc_set_as_theme();

        vc_map( array(
              "name" => esc_html(__( "Employer Control Panel", "wp-job-portal")),
              "base" => "wpjobportal_employer_controlpanel",
              "class" => "",
              "category" => esc_html(__( "WP JOB PORTAL Pages", "wp-job-portal")),
              "icon" => esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/vc-icons/dashboard.png',
              "show_settings_on_create" => false,
            )
        );

        vc_map( array(
              "name" => esc_html(__( "Job Seeker Control Panel", "wp-job-portal")),
              "base" => "wpjobportal_jobseeker_controlpanel",
              "class" => "",
              "category" => esc_html(__( "WP JOB PORTAL Pages", "wp-job-portal")),
              "icon" => esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/vc-icons/dashboard.png',
              "show_settings_on_create" => false,
            )
        );

        vc_map( array(
              "name" => esc_html(__( "Login", "wp-job-portal")),
              "base" => "wpjobportal_login_page",
              "class" => "",
              "category" => esc_html(__( "WP JOB PORTAL Pages", "wp-job-portal")),
              "icon" => esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/vc-icons/login.png',
              "show_settings_on_create" => false,
            )
        );

        vc_map( array(
              "name" => esc_html(__( "Job Search", "wp-job-portal")),
              "base" => "wpjobportal_job_search",
              "class" => "",
              "category" => esc_html(__( "WP JOB PORTAL Pages", "wp-job-portal")),
              "icon" => esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/vc-icons/job-search.png',
              "show_settings_on_create" => false,
            )
        );

        vc_map( array(
              "name" => esc_html(__( "Job Listing", "wp-job-portal")),
              "base" => "wpjobportal_job",
              "class" => "",
              "category" => esc_html(__( "WP JOB PORTAL Pages", "wp-job-portal")),
              "icon" => esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/vc-icons/job-list.png',
              "show_settings_on_create" => false,
            )
        );

        vc_map( array(
              "name" => esc_html(__( "Jobs By Categories", "wp-job-portal")),
              "base" => "wpjobportal_job_categories",
              "class" => "",
              "category" => esc_html(__( "WP JOB PORTAL Pages", "wp-job-portal")),
              "icon" => esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/vc-icons/job-category.png',
              "show_settings_on_create" => false,
            )
        );

        vc_map( array(
              "name" => esc_html(__( "Jobs By Types", "wp-job-portal")),
              "base" => "wpjobportal_job_types",
              "class" => "",
              "category" => esc_html(__( "WP JOB PORTAL Pages", "wp-job-portal")),
              "icon" => esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/vc-icons/job-type.png',
              "show_settings_on_create" => false,
            )
        );

        vc_map( array(
              "name" => esc_html(__( "My Applied Jobs", "wp-job-portal")),
              "base" => "wpjobportal_my_appliedjobs",
              "class" => "",
              "category" => esc_html(__( "WP JOB PORTAL Pages", "wp-job-portal")),
              "icon" => esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/vc-icons/my-applied-job.png',
              "show_settings_on_create" => false,
            )
        );

        vc_map( array(
              "name" => esc_html(__( "My Companies", "wp-job-portal")),
              "base" => "wpjobportal_my_companies",
              "class" => "",
              "category" => esc_html(__( "WP JOB PORTAL Pages", "wp-job-portal")),
              "icon" => esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/vc-icons/companies.png',
              "show_settings_on_create" => false,
            )
        );


        vc_map( array(
              "name" => esc_html(__( "My Jobs", "wp-job-portal")),
              "base" => "wpjobportal_my_jobs",
              "class" => "",
              "category" => esc_html(__( "WP JOB PORTAL Pages", "wp-job-portal")),
              "icon" => esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/vc-icons/jobs.png',
              "show_settings_on_create" => false,
            )
        );

        vc_map( array(
              "name" => esc_html(__( "My Resumes", "wp-job-portal")),
              "base" => "wpjobportal_my_resumes",
              "class" => "",
              "category" => esc_html(__( "WP JOB PORTAL Pages", "wp-job-portal")),
              "icon" => esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/vc-icons/resume.png',
              "show_settings_on_create" => false,
            )
        );

        vc_map( array(
              "name" => esc_html(__( "Add Company", "wp-job-portal")),
              "base" => "wpjobportal_add_company",
              "class" => "",
              "category" => esc_html(__( "WP JOB PORTAL Pages", "wp-job-portal")),
              "icon" => esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/vc-icons/ad-company.png',
              "show_settings_on_create" => false,
            )
        );


        vc_map( array(
              "name" => esc_html(__( "Add Job", "wp-job-portal")),
              "base" => "wpjobportal_add_job",
              "class" => "",
              "category" => esc_html(__( "WP JOB PORTAL Pages", "wp-job-portal")),
              "icon" => esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/vc-icons/ad-job.png',
              "show_settings_on_create" => false,
            )
        );

        vc_map( array(
              "name" => esc_html(__( "Add Resume", "wp-job-portal")),
              "base" => "wpjobportal_add_resume",
              "class" => "",
              "category" => esc_html(__( "WP JOB PORTAL Pages", "wp-job-portal")),
              "icon" => esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/vc-icons/ad-resume.png',
              "show_settings_on_create" => false,
            )
        );

        vc_map( array(
              "name" => esc_html(__( "Resume Search", "wp-job-portal")),
              "base" => "wpjobportal_resume_search",
              "class" => "",
              "category" => esc_html(__( "WP JOB PORTAL Pages", "wp-job-portal")),
              "icon" => esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/vc-icons/resume-search.png',
              "show_settings_on_create" => false,
            )
        );

        vc_map( array(
              "name" => esc_html(__( "Employer Registration", "wp-job-portal")),
              "base" => "wpjobportal_employer_registration",
              "class" => "",
              "category" => esc_html(__( "WP JOB PORTAL Pages", "wp-job-portal")),
              "icon" => esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/vc-icons/employer-register.png',
              "show_settings_on_create" => false,
            )
        );

        vc_map( array(
              "name" => esc_html(__( "Job Seeker Registration", "wp-job-portal")),
              "base" => "wpjobportal_jobseeker_registration",
              "class" => "",
              "category" => esc_html(__( "WP JOB PORTAL Pages", "wp-job-portal")),
              "icon" => esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/vc-icons/jobseeker-register.png',
              "show_settings_on_create" => false,
            )
        );

        vc_map( array(
              "name" => esc_html(__( "All Companies", "wp-job-portal")),
              "base" => "wpjobportal_all_companies",
              "class" => "",
              "category" => esc_html(__( "WP JOB PORTAL Pages", "wp-job-portal")),
              "icon" => esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/vc-icons/all-companies.png',
              "show_settings_on_create" => false,
            )
        );


        vc_map( array(
              "name" => esc_html(__( "My Cover Letters", "wp-job-portal")),
              "base" => "wpjobportal_my_coverletter",
              "class" => "",
              "category" => esc_html(__( "WP JOB PORTAL Pages", "wp-job-portal")),
              "icon" => esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/vc-icons/cover-letter.png',
              "show_settings_on_create" => false,
            )
        );

        vc_map( array(
              "name" => esc_html(__( "My Departments", "wp-job-portal")),
              "base" => "wpjobportal_my_departments",
              "class" => "",
              "category" => esc_html(__( "WP JOB PORTAL Pages", "wp-job-portal")),
              "icon" => esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/vc-icons/department.png',
              "show_settings_on_create" => false,
            )
        );


        vc_map( array(
              "name" => esc_html(__( "Add Cover Letter", "wp-job-portal")),
              "base" => "wpjobportal_add_coverletter",
              "class" => "",
              "category" => esc_html(__( "WP JOB PORTAL Pages", "wp-job-portal")),
              "icon" => esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/vc-icons/ad-cover-letter.png',
              "show_settings_on_create" => false,
            )
        );

        vc_map( array(
              "name" => esc_html(__( "Add Department", "wp-job-portal")),
              "base" => "wpjobportal_add_department",
              "class" => "",
              "category" => esc_html(__( "WP JOB PORTAL Pages", "wp-job-portal")),
              "icon" => esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/vc-icons/ad-department.png',
              "show_settings_on_create" => false,
            )
        );
        vc_map( array(
              "name" => esc_html(__( "Employer My Stats", "wp-job-portal")),
              "base" => "wpjobportal_employer_my_stats",
              "class" => "",
              "category" => esc_html(__( "WP JOB PORTAL Pages", "wp-job-portal")),
              "icon" => esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/vc-icons/employer-stats.png',
              "show_settings_on_create" => false,
            )
        );

        vc_map( array(
              "name" => esc_html(__( "Job Seeker My Stats", "wp-job-portal")),
              "base" => "wpjobportal_jobseeker_my_stats",
              "class" => "",
              "category" => esc_html(__( "WP JOB PORTAL Pages", "wp-job-portal")),
              "icon" => esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/vc-icons/jobseeker-stats.png',
              "show_settings_on_create" => false,
            )
        );

        vc_map( array(
              "name" => esc_html(__( "Featured Jobs", "wp-job-portal")),
              "base" => "wpjobportal_featured_jobs",
              "class" => "",
              "category" => esc_html(__( "WP JOB PORTAL Pages", "wp-job-portal")),
              "icon" => esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/vc-icons/featured-jobs.png',
              "show_settings_on_create" => false,
            )
        );

        vc_map( array(
              "name" => esc_html(__( "Featured Resumes", "wp-job-portal")),
              "base" => "wpjobportal_featured_resumes",
              "class" => "",
              "category" => esc_html(__( "WP JOB PORTAL Pages", "wp-job-portal")),
              "icon" => esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/vc-icons/featured-resumes.png',
              "show_settings_on_create" => false,
            )
        );

        vc_map( array(
              "name" => esc_html(__( "Featured Companies", "wp-job-portal")),
              "base" => "wpjobportal_featured_companies",
              "class" => "",
              "category" => esc_html(__( "WP JOB PORTAL Pages", "wp-job-portal")),
              "icon" => esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/vc-icons/featured-companies.png',
              "show_settings_on_create" => false,
            )
        );


        vc_map( array(
              "name" => esc_html(__( "All Resumes", "wp-job-portal")),
              "base" => "wpjobportal_all_resumes",
              "class" => "",
              "category" => esc_html(__( "WP JOB PORTAL Pages", "wp-job-portal")),
              "icon" => esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/vc-icons/all-resumes.png',
              "show_settings_on_create" => false,
            )
        );



    }
}

// code to show current layout title as wordpress page title(heading)
//add_filter( 'the_title', 'wpjobportal_job_portal_page_title' );
function wpjobportal_job_portal_page_title($title) {
    // making sure current
    if(isset(wpjobportal::$_data) && isset(wpjobportal::$_data['sanitized_args']) && !empty(isset(wpjobportal::$_data['sanitized_args']))){
        $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme');
        $wpjobportal_layout = WPJOBPORTALrequest::getVar('wpjobportallt');
        if ($wpjobportal_module != '' && $wpjobportal_layout != '') { //only check for title if module and layout is set.(that means current page is opening our layout)
            $page_title = wpjobportal_getWPJobPortalPageTitle($wpjobportal_module, $wpjobportal_layout);
            if($page_title != ''){
                $title = $page_title;
            }
        }
    }
    return $title;
}



function wpjobportal_getWPJobPortalPageTitle($wpjobportal_module, $wpjobportal_layout){
    $title = '';
    if ($wpjobportal_module != '' && $wpjobportal_layout != '') {
        switch ($wpjobportal_layout) {
            case 'addcompany':
                $title = esc_html(__('Add Company', 'wp-job-portal'));
                break;
            case 'mycompanies':
                $title =  esc_html(__('My Companies', 'wp-job-portal'));
                break;
            case 'companies':
                $title =  esc_html(__('Companies', 'wp-job-portal'));
                break;
            case 'featuredcompanies':
                $title =  esc_html(__('Featured Companies', 'wp-job-portal'));
                break;
            case 'viewcompany':
                $title =  esc_html(__('Company Information', 'wp-job-portal'));
                break;
            case 'adddepartment':
                $title =  esc_html(__('Add Department', 'wp-job-portal'));
                break;
            case 'mydepartments':
                $title =  esc_html(__('My Departments', 'wp-job-portal'));
                break;
            case 'viewdepartment':
                $title =  esc_html(__('View Department', 'wp-job-portal'));
                break;
            case 'addcoverletter':
                $title =  esc_html(__('Add Cover Letter', 'wp-job-portal'));
                break;
            case 'mycoverletters':
                $title =  esc_html(__('My Cover Letters', 'wp-job-portal'));
                break;
            case 'viewcoverletter':
                $title =  esc_html(__('View Cover Letter', 'wp-job-portal'));
                break;
            case 'addjob':
                $title =  esc_html(__('Add Job', 'wp-job-portal'));
                break;
            case 'myjobs':
                $title =  esc_html(__('My Jobs', 'wp-job-portal'));
                break;
            case 'viewjob':
                $title =  esc_html(__('Job Info', 'wp-job-portal'));
                break;
            case 'jobsbycategories':
                $title =  esc_html(__('Jobs By Categories', 'wp-job-portal'));
                break;
            case 'jobsbytypes':
                $title =  esc_html(__('Jobs By Types', 'wp-job-portal'));
                break;
            case 'jobs':
                $title =  esc_html(__('Newest Jobs', 'wp-job-portal'));
                break;
            case 'newestjobs':
                $title =  esc_html(__('Newest Jobs', 'wp-job-portal'));
                break;
            case 'featuredjobs':
                $title =  esc_html(__('Featured Jobs', 'wp-job-portal'));
                break;
            case 'shortlistedjobs':
                $title =  esc_html(__('Short Listed Jobs', 'wp-job-portal'));
                break;
            case 'visitoraddjob':
                $title = esc_html(__('Add Job','wp-job-portal'));
                break;
            case 'employermessages':
                $title =  esc_html(__('Messages', 'wp-job-portal'));
                break;
            case 'jobseekermessages':
                $title =  esc_html(__('Job Seeker Messages', 'wp-job-portal'));
                break;
            case 'jobmessages':
                $title =  esc_html(__('Job Messages', 'wp-job-portal'));
                break;
            case 'sendmessage':
                $title =  esc_html(__('Send Message', 'wp-job-portal'));
                break;
            case 'resumesearch':
                $title =  esc_html(__('Resume Search', 'wp-job-portal'));
                break;
            case 'resumesavesearch':
                $title =  esc_html(__('Resume Saved Searches', 'wp-job-portal'));
                break;
            case 'resumes':
                $title =  esc_html(__('Resume List', 'wp-job-portal'));
                break;
            case 'employerpurchasehistory':
                $title =  esc_html(__('Purchase History', 'wp-job-portal'));
                break;
            case 'jobseekerpurchasehistory':
                $title =  esc_html(__('Purchase History', 'wp-job-portal'));
                break;
            case 'mysubscriptions':
                $title =  esc_html(__('My Subscriptions', 'wp-job-portal'));
                break;
            case 'purchasehistory':
                $title =  esc_html(__('My Packages', 'wp-job-portal'));
                break;
            case 'paydepartment':
                $title =  esc_html(__('Pay For Department', 'wp-job-portal'));
                break;
            case 'payjobapply':
                $title =  esc_html(__('Pay For Job Apply', 'wp-job-portal'));
                break;
            case 'paycompany':
                $title =  esc_html(__('Pay For Company', 'wp-job-portal'));
                break;
            case 'payfeaturedcompany':
                $title =  esc_html(__('Pay For Featured Company', 'wp-job-portal'));
                break;
            case 'payjob':
                $title =  esc_html(__('Pay For Job', 'wp-job-portal'));
                break;
            case 'payfeaturedjob':
                $title =  esc_html(__('Pay For Featured Job', 'wp-job-portal'));
                break;
            case 'payresumesearch':
                $title =  esc_html(__('Pay For Resume Search', 'wp-job-portal'));
                break;
            case 'payresume':
                $title =  esc_html(__('Pay For Resume', 'wp-job-portal'));
                break;
            case 'payfeaturedresume':
                $title =  esc_html(__('Pay For Featured Resume', 'wp-job-portal'));
                break;
            case 'packages':
                $title =  esc_html(__('Package', 'wp-job-portal'));
                break;
            case 'myinvoices':
                $title =  esc_html(__('My Invoices', 'wp-job-portal'));
                break;
            case 'addfolder':
                $title =  esc_html(__('Add Folder', 'wp-job-portal'));
                break;
            case 'myfolders':
                $title =  esc_html(__('My Folders', 'wp-job-portal'));
                break;
            case 'viewfolder':
                $title =  esc_html(__('View Folder', 'wp-job-portal'));
                break;
            case 'folderresume':
                $title =  esc_html(__('Folder Resumes', 'wp-job-portal'));
                break;
            case 'addresume':
                $title =  esc_html(__('Add Resume', 'wp-job-portal'));
                break;
            case 'myresumes':
                $title =  esc_html(__('My Resumes', 'wp-job-portal'));
                break;
            case 'featuredresumes':
                $title =  esc_html(__('Featured Resumes', 'wp-job-portal'));
                break;
            case 'resumebycategory':
                $title =  esc_html(__('Resume By Categories', 'wp-job-portal'));
                break;
            case 'viewresume':
                $title =  esc_html(__('View Resume', 'wp-job-portal'));
                break;
            case 'myappliedjobs':
                $title =  esc_html(__('My Applied Jobs', 'wp-job-portal'));
                break;
            case 'jobappliedresume':
                $title =  esc_html(__('Job Applied Resume', 'wp-job-portal'));
                break;
            case 'jobalert':
                $title =  esc_html(__('Job Alert', 'wp-job-portal'));
                break;
            case 'jobsearch':
                $title =  esc_html(__('Job Search', 'wp-job-portal'));
                break;
            case 'jobsavesearch':
                $title =  esc_html(__('Job Saved Searches', 'wp-job-portal'));
                break;
            case 'controlpanel':
                $title =  esc_html(__('Dashboard', 'wp-job-portal'));
                break;
            case 'login':
                $title =  esc_html(__('Log In', 'wp-job-portal'));
                break;
            case 'regemployer':
                $title =  esc_html(__('Employer Registration', 'wp-job-portal'));
                break;
            case 'regjobseeker':
                $title =  esc_html(__('Job Seeker Registration', 'wp-job-portal'));
                break;
            case 'formprofile':
                $title =  esc_html(__('Edit Profile', 'wp-job-portal'));
                break;
        }
    }

    return $title;
}

// hook to store ai data for job

//  job ai string
add_action('wpjobportal_after_store_job_hook','wpjobportal_storeAIStringDataForJob',10,1);
function wpjobportal_storeAIStringDataForJob($wpjobportal_data){
    WPJOBPORTALincluder::getJSModel('job')->prepareAIStringDataForJob($wpjobportal_data);
}

//  resume ai string
add_action('wpjobportal_after_store_resume_hook','wpjobportal_storeAIStringDataForResume',10,1);
function wpjobportal_storeAIStringDataForResume($wpjobportal_data){
    WPJOBPORTALincluder::getJSModel('resume')->prepareAIStringDataForResume($wpjobportal_data);
}


// ai banner

// Retrieve the value of the 'wpjobportal_ai_search_data_sync_needed' option
$wpjobportal_sync_needed = get_option('wpjobportal_ai_search_data_sync_needed');
if ($wpjobportal_sync_needed === false || $wpjobportal_sync_needed != 0) { // if not found then show update database banner
    add_action( 'admin_notices', 'wpjobportal_ai_search_data_sync_needed_notice');
}

function wpjobportal_ai_search_data_sync_needed_notice() {
    ?>
    <div class="notice wpjobportal-ai-search-data-synchronize-section-mainwrp is-dismissible">
         <div class="wpjobportal-ai-search-data-synchronize-section">
            <div class="wpjobportal-ai-search-data-synchronize-imgwrp">
                <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/sql_update.png" title="<?php echo esc_attr(__('Update', 'wp-job-portal')); ?>" alt="<?php echo esc_attr(__('Update', 'wp-job-portal')); ?>" class="wpjobportal-ai-search-data-synchronize-img">
            </div>
            <div class="wpjobportal-ai-search-data-synchronize-content-wrp">
                <span class="wpjobportal-ai-search-data-synchronize-content-title"><?php echo esc_html(__('Database Update Needed', 'wp-job-portal'));?></span>
                <span class="wpjobportal-ai-search-data-synchronize-content-disc"><?php echo esc_html(__("A critical update for WP Job Portal is required to maintain performance and prevent issues. Please update now.", 'wp-job-portal'));?></span>
            </div>
            <div class="wpjobportal-ai-search-data-synchronize-button-wrp">
                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_common&task=wpjobportal_synchronize_ai_search_data&action=wpjobportaltask'),'synchronize_ai_search_data')); ?>" id="wpjobportalCheckUpdates" class="wpjobportal_ai_synchronize_data" title="<?php echo esc_attr(__('Update Now', 'wp-job-portal')); ?>">
                    <?php echo esc_html(__('Update Now', 'wp-job-portal')); ?>
                </a>
            </div>
        </div>
    </div>
    <div id="wpjp-loading-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.65); z-index:9999; text-align:center; color:#fff; font-size:20px; padding-top:20%; font-family:sans-serif;">
        <div><?php echo esc_html(__('Updating Database, please wait...', 'wp-job-portal')); ?></div>
    </div>
    <?php
    wp_register_script( 'wpjobportal-inline-handle', '' );
    wp_enqueue_script( 'wpjobportal-inline-handle' );

    $wpjobportal_inline_js_script = "
        jQuery(document).ready(function(){
            jQuery('#wpjobportalCheckUpdates').on('click', function() {
                jQuery('#wpjp-loading-overlay').fadeIn();
            });
        }); ";
    wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );

}


add_action('plugins_loaded', 'wpjobportal_check_and_download_languages');

function wpjobportal_check_and_download_languages() {

    if (!current_user_can('manage_options') && !wp_doing_cron()) {
        return; // Skip download attempt for non-privileged contexts
    }

    $locale = determine_locale();
    if ($locale === 'en_US') return;

    $status = get_option('wpjobportal_translation_status_' . $locale);
    if ($status === 'verified' || $status === 'failed') {
        return;
    }

    $textdomain   = 'wp-job-portal';
    //$default_list = get_option('wh_plugin_default_locales', []);
    $default_list = WPJOBPORTAL_DEFAULT_LANGUAGES;
    $target_dir   = WPJOBPORTAL_PLUGIN_PATH . 'languages/';
    $extensions   = in_array($locale, $default_list) ? ['po'] : ['mo', 'po'];

    $all_exist = true;
    foreach ($extensions as $ext) {
        if (!file_exists($target_dir . "{$textdomain}-{$locale}.{$ext}")) {
            $all_exist = false;
            break;
        }
    }

    if ($all_exist) {
        update_option('wpjobportal_translation_status_' . $locale, 'verified');
        return;
    }

    wpjobportal_execute_download_process_for_languagefiles($locale, $extensions);
}

function wpjobportal_execute_download_process_for_languagefiles($locale, $extensions) {
    global $wp_filesystem;

    // Initialize WP_Filesystem safely
    if (empty($wp_filesystem)) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        if ( ! WP_Filesystem() ) {
            return; // Exit if Filesystem credentials are required but unavailable
        }
    }

    $textdomain = 'wp-job-portal';
    //$cdn_base   = 'https://dev83.joomshine.com/2026/wp/jp252/wp-content/uploads/download-languages/';
    $cdn_base   = 'https://d2cx1dgksy1tik.cloudfront.net/';

    $target_dir = WPJOBPORTAL_PLUGIN_PATH . 'languages/';

    $locales_to_try = [$locale];
    $fallback_locale = wpjobportal_get_fallback_locale($locale);

    if ($fallback_locale) {
        $locales_to_try[] = $fallback_locale;
    }

    $download_successful = false;
    $downloaded_locale = '';

    foreach ($locales_to_try as $attempt_locale) {
        $all_extensions_downloaded = true;

        foreach ($extensions as $ext) {
            $remote_filename = "{$textdomain}-{$attempt_locale}.{$ext}";
            $local_filename  = "{$textdomain}-{$locale}.{$ext}";

            // FIX: Add a timeout argument to prevent freezing the admin dashboard if CDN is slow
            $response = wp_remote_get($cdn_base . $remote_filename, [
                'timeout' => 15
            ]);

            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                if (!$wp_filesystem->is_dir($target_dir)) {
                    $wp_filesystem->mkdir($target_dir, FS_CHMOD_DIR);
                }
                $saved = $wp_filesystem->put_contents($target_dir . $local_filename, wp_remote_retrieve_body($response));
                if (!$saved) {
                    $all_extensions_downloaded = false;
                    break;
                }
            } else {
                $all_extensions_downloaded = false;
                break;
            }
        }

        if ($all_extensions_downloaded) {
            $download_successful = true;
            $downloaded_locale = $attempt_locale;
            break;
        }
    }

    // Determine notice type and save to transient
    if ($download_successful) {
        update_option('wpjobportal_translation_status_' . $locale, 'verified');

        $notice_type = ($downloaded_locale === $locale) ? 'exact_success' : 'fallback_success';
        set_transient('wpjobportal_lang_notice', [
            'type'     => $notice_type,
            'original' => $locale,
            'fallback' => $downloaded_locale
        ], 60);

    } else {
        update_option('wpjobportal_translation_status_' . $locale, 'failed');

        set_transient('wpjobportal_lang_notice', [
            'type'     => 'failed',
            'original' => $locale,
        ], 300);
    }
}

function wpjobportal_get_fallback_locale($locale) {
    $base_lang = substr($locale, 0, 2);

    // Comprehensive fallback map based on standard WordPress locales
    $fallbacks = [
        'af' => 'af',          // Afrikaans
        'ar' => 'ar',          // Arabic
        'bg' => 'bg_BG',       // Bulgarian
        'bs' => 'bs_BA',       // Bosnian
        'ca' => 'ca',          // Catalan
        'cs' => 'cs_CZ',       // Czech
        'da' => 'da_DK',       // Danish
        'de' => 'de_DE',       // German
        'el' => 'el',          // Greek
        'en' => 'en_US',       // English
        'es' => 'es_ES',       // Spanish
        'et' => 'et',          // Estonian
        'fa' => 'fa_IR',       // Persian
        'fi' => 'fi',          // Finnish
        'fr' => 'fr_FR',       // French
        'he' => 'he_IL',       // Hebrew
        'hi' => 'hi_IN',       // Hindi
        'hr' => 'hr',          // Croatian
        'hu' => 'hu_HU',       // Hungarian
        'id' => 'id_ID',       // Indonesian
        'is' => 'is_IS',       // Icelandic
        'it' => 'it_IT',       // Italian
        'ja' => 'ja',          // Japanese
        'ko' => 'ko_KR',       // Korean
        'lt' => 'lt_LT',       // Lithuanian
        'lv' => 'lv',          // Latvian
        'ms' => 'ms_MY',       // Malay
        'nb' => 'nb_NO',       // Norwegian Bokmål
        'nl' => 'nl_NL',       // Dutch
        'pl' => 'pl_PL',       // Polish
        'pt' => 'pt_PT',       // Portuguese (or pt_BR for Brazil)
        'ro' => 'ro_RO',       // Romanian
        'ru' => 'ru_RU',       // Russian
        'sk' => 'sk_SK',       // Slovak
        'sl' => 'sl_SI',       // Slovenian
        'sq' => 'sq',          // Albanian
        'sr' => 'sr_RS',       // Serbian
        'sv' => 'sv_SE',       // Swedish
        'th' => 'th',          // Thai
        'tr' => 'tr_TR',       // Turkish
        'uk' => 'uk',          // Ukrainian
        'vi' => 'vi',          // Vietnamese
        'zh' => 'zh_CN'        // Chinese (Simplified)
    ];

    if (isset($fallbacks[$base_lang]) && $fallbacks[$base_lang] !== $locale) {
        return $fallbacks[$base_lang];
    }

    return false;
}

add_action('admin_notices', 'wpjobportal_display_language_download_notice');

function wpjobportal_display_language_download_notice() {
    // Only show to users who can manage the site
    if (!current_user_can('manage_options')) {
        return;
    }

    $notice = get_transient('wpjobportal_lang_notice');
    if (!$notice) {
        return;
    }

    // Clear the transient immediately so it only shows once
    delete_transient('wpjobportal_lang_notice');

    $type     = $notice['type'];
    $original = esc_html($notice['original']);

    if ($type === 'exact_success') {
        echo '<div class="notice notice-success is-dismissible">';
        echo '<p><strong>WP Job Portal:</strong> Language files for <code>' . $original . '</code> successfully downloaded.</p>';
        echo '</div>';
    }
    elseif ($type === 'fallback_success') {
        $fallback = esc_html($notice['fallback']);
        echo '<div class="notice notice-warning is-dismissible">';
        echo '<p><strong>WP Job Portal:</strong> Alternate language file downloaded. We tried to find <code>' . $original . '</code>, but downloaded <code>' . $fallback . '</code> as a fallback.</p>';
        echo '</div>';
    }
  /*
    elseif ($type === 'failed') {
        echo '<div class="notice notice-error is-dismissible">';
        echo '<p><strong>WP Job Portal:</strong> Locale <code>' . $original . '</code> not found on our server. The plugin will continue to function in the default language.</p>';
        echo '</div>';
    }
    */
}

?>
