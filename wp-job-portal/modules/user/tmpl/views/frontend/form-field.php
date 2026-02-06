<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
* @param wp job portal 
* Edit & ADD form For User 
* wp job Portal Users
*/
if(isset($wpjobportal_user)){

}
function wpjobportal_printFormField($title, $wpjobportal_field, $wpjobportal_description) {
    $wpjobportal_html = '<div class="wjportal-form-row">
    <div class="wjportal-form-title">' . wp_kses(wpjobportal::wpjobportal_getVariableValue($title),WPJOBPORTAL_ALLOWED_TAGS) . '</div>
    <div class="wjportal-form-value">' . $wpjobportal_field;
    if (!empty($wpjobportal_description)) {
        $wpjobportal_html .= '<div class="wjportal-form-help-txt">'.$wpjobportal_description.'</div>';
    }
    $wpjobportal_html .= '</div></div>';
    return $wpjobportal_html;
}
function wpjobportal_getRowForForm($wpjobportal_text, $wpjobportal_value,$wpjobportal_themecall=null) {
    $wpjobportal_html = '<div class="wjportal-form-row">
    <div class="wjportal-form-title">' . wp_kses(wpjobportal::wpjobportal_getVariableValue($wpjobportal_text),WPJOBPORTAL_ALLOWED_TAGS) . ':</div>
    <div class="wjportal-form-value">' . $wpjobportal_value . '</div>
    </div>';
    return $wpjobportal_html;
}
?>

<?php
$wpjobportal_fieldslist = wpjobportal::$_wpjpfieldordering->getFieldsOrderingforForm(4);
foreach ($wpjobportal_fieldslist AS $wpjobportal_field) {
    switch ($wpjobportal_field->field) {
        case 'skype':
        if($wpjobportal_field->published == 1) {
            $wpjobportal_req = '';
            $wpjobportal_optional = 'true';
            $title = wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle);
            $wpjobportal_description = '';
            if($wpjobportal_field->required == 1) {
                $wpjobportal_req = 'required';
                $title .= '<font class="required-notifier">*</font>';
                $wpjobportal_optional = '';
            }
            $wpjobportal_field = WPJPOBPORTALformfield::text($wpjobportal_field->field, isset(wpjobportal::$_data[0]->skype) ? wpjobportal::$_data[0]->skype : '', array('class' => 'inputbox one wjportal-form-input-field', 'data-validation' => $wpjobportal_req,'data-validation-optional'=>$wpjobportal_optional));
            echo wp_kses(wpjobportal_printFormField($title, $wpjobportal_field, $wpjobportal_description), WPJOBPORTAL_ALLOWED_TAGS);
        }
        break;
        case 'wpjobportal_user_email':
        if(!isset(wpjobportal::$_data[0])){
            if($wpjobportal_field->published == 1) {
                $wpjobportal_req = '';
				$title = wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle);
                $wpjobportal_description = $wpjobportal_field->description;
                if($wpjobportal_field->required == 1) {
                    $wpjobportal_req = 'email';
                    $title .= '<font class="required-notifier">*</font>';
                }
                $wpjobportal_field = WPJOBPORTALformfield::text('wpjobportal_user_email', isset(wpjobportal::$_data[0]->emailaddress) ? wpjobportal::$_data[0]->emailaddress : '', array('class' => 'inputbox one wjportal-form-input-field', 'data-validation' => $wpjobportal_req, 'placeholder' => $wpjobportal_field->placeholder));
                echo wp_kses(wpjobportal_printFormField($title, $wpjobportal_field, $wpjobportal_description), WPJOBPORTAL_ALLOWED_TAGS);
            }
        }
        break;
        case 'wpjobportal_user_first':
        if($wpjobportal_field->published == 1) {
            $wpjobportal_req = '';
            $title = wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle);
            $wpjobportal_description = $wpjobportal_field->description;
            if($wpjobportal_field->required == 1) {
                $wpjobportal_req = 'required';
                $title .= '<font class="required-notifier">*</font>';
            }
            $wpjobportal_field = WPJOBPORTALformfield::text('wpjobportal_user_first', isset(wpjobportal::$_data[0]->first_name) ? wpjobportal::$_data[0]->first_name : '', array('class' => 'inputbox one wjportal-form-input-field', 'data-validation' => $wpjobportal_req, 'placeholder' => $wpjobportal_field->placeholder));
            echo wp_kses(wpjobportal_printFormField($title, $wpjobportal_field, $wpjobportal_description), WPJOBPORTAL_ALLOWED_TAGS);
        }
        break;
        case 'wpjobportal_user_last':
        if($wpjobportal_field->published == 1) {
            $wpjobportal_req = '';
            $title = wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle);
            $wpjobportal_description = $wpjobportal_field->description;
            if($wpjobportal_field->required == 1) {
                $wpjobportal_req = 'required';
                $title .= '<font class="required-notifier">*</font>';
            }
            $wpjobportal_field = WPJOBPORTALformfield::text('wpjobportal_user_last', isset(wpjobportal::$_data[0]->last_name) ? wpjobportal::$_data[0]->last_name : '', array('class' => 'inputbox one wjportal-form-input-field', 'data-validation' => $wpjobportal_req, 'placeholder' => $wpjobportal_field->placeholder));
            echo wp_kses(wpjobportal_printFormField($title, $wpjobportal_field, $wpjobportal_description), WPJOBPORTAL_ALLOWED_TAGS);
        }
        break;
        case 'wpjobportal_user_login':
        if(!isset(wpjobportal::$_data[0])){
            if($wpjobportal_field->published == 1) {
                $wpjobportal_req = '';
		$title = wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle);
                $wpjobportal_description = $wpjobportal_field->description;
                if($wpjobportal_field->required == 1) {
                    $wpjobportal_req = 'required';
                    $title .= '<font class="required-notifier">*</font>';
                }
                $wpjobportal_field = WPJOBPORTALformfield::text('wpjobportal_user_login', isset(wpjobportal::$_data[0]->name) ? wpjobportal::$_data[0]->name : '', array('class' => 'inputbox one wjportal-form-input-field', 'data-validation' => $wpjobportal_req, 'placeholder' => $wpjobportal_field->placeholder));
                echo wp_kses(wpjobportal_printFormField($title, $wpjobportal_field, $wpjobportal_description), WPJOBPORTAL_ALLOWED_TAGS);
            }
        }
        break;
        case 'photo':   
        if($wpjobportal_field->published == 1) {
            $wpjobportal_req = '';
            $title = wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle);
            $wpjobportal_description = $wpjobportal_field->description;
            ?>
            <div class="wjportal-form-row">
                <div class="wjportal-form-title" for="wjportal_user_profile">
                    <?php echo esc_html($title);?>
                </div>
                <div class="wjportal-form-value">
                    <?php
                                    /////**********Use OF Field Ordering Method**********/////
                    $wpjobportal_themecall='';
                    $wpjobportal_text = '';
                    $wpjobportal_photo_required ='';
                    $wpjobportal_imgpath = '';

                    $wpjobportal_img = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('jobseeker');
                    $wpjobportal_display = 'none';
                    $wpjobportal_photoname = '';
                    if(isset(wpjobportal::$_data[0]->photo) && !empty(wpjobportal::$_data[0]->photo)){
                        $wpjobportal_wpdir = wp_upload_dir();
                        $wpjobportal_data_directory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
                        $wpjobportal_img = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/profile/profile_' . wpjobportal::$_data[0]->uid . '/profile/' . wpjobportal::$_data[0]->photo;
                        $wpjobportal_display = '';
                        $wpjobportal_photoname  = wpjobportal::$_data[0]->photo;
                    }

                    $wpjobportal_fieldvalue = '
                    <div class="wjportal-form-upload-btn-wrp">
                    <span class="wjportal-form-upload-btn-wrp-txt"  style="display:'.$wpjobportal_display.';">'.$wpjobportal_photoname.'
                    </span>
                    <span class="wjportal-form-upload-btn">
                    '.esc_html(__("Upload Image","wp-job-portal")).'
                    <input type="file" name="photo" class="photo wjportal-form-upload-field" id="photo" value='.$wpjobportal_photoname.' />
                    </span>
                    </div>
                    <div class="wjportal-form-image-wrp" style="display:'.$wpjobportal_display.';">
                    <img class="rs_photo wjportal-form-image" id="rs_photo" src="' . $wpjobportal_img . '" alt="'.esc_attr(__('Profile image','wp-job-portal')).'"/>';
                    if(isset(wpjobportal::$_data[0]) && !empty(wpjobportal::$_data[0]->id)){
                        $wpjobportal_fieldvalue .= '<img id="wjportal-form-delete-image" onClick="return removeLogo('.wpjobportal::$_data[0]->uid.')" alt="cross" src="'.esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/no.png" />';
                    }else{
                        $wpjobportal_fieldvalue .= '<img id="wjportal-form-delete-image"  alt="cross" src="'.esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/no.png" />';
                    }
                    $wpjobportal_fieldvalue .=  '</div>';
                    $wpjobportal_logoformat = wpjobportal::$_config->getConfigurationByConfigName('image_file_type');
                    $wpjobportal_maxsize = wpjobportal::$_config->getConfigurationByConfigName('image_file_size');
                    $wpjobportal_p_detail = '<div class="wjportal-form-help-txt"> ('.$wpjobportal_logoformat.') </div>';
                    $wpjobportal_p_detail .= '<div class="wjportal-form-help-txt">  ('.esc_html(__("Max logo size allowed","wp-job-portal")).' '.$wpjobportal_maxsize.' Kb) </div>';
                    if (!empty($wpjobportal_description)) {
                        $wpjobportal_p_detail .= '<div class="wjportal-form-help-txt">'.$wpjobportal_description.'</div>';
                    }
                    $wpjobportal_fieldvalue .= $wpjobportal_p_detail;
                    ?>
                    <div class="wjportal-form-upload">
                        <?php echo wp_kses($wpjobportal_fieldvalue, WPJOBPORTAL_ALLOWED_TAGS) ;?>
                    </div>
                </div>
            </div>
            <?php
        }
        break;
        default:
        if($wpjobportal_field->isuserfield == 1) {
            $wpjobportal_req = '';
            $title = wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->fieldtitle);
            $wpjobportal_description = $wpjobportal_field->description;
            if($wpjobportal_field->required == 1) {
                $wpjobportal_req = 'required';
                $title .= '<font class="required-notifier">*</font>';
            }
            $wpjobportal_field = wpjobportal::$_wpjpcustomfield->formCustomFields($wpjobportal_field);
            echo wp_kses(wpjobportal_printFormField($title, $wpjobportal_field, $wpjobportal_description), WPJOBPORTAL_ALLOWED_TAGS);
        }
        break;
    }
} 
?>
<!-- Static Input Get Validate -->
<?php  if(!isset(wpjobportal::$_data[0])){  ?>
    <div class="wjportal-form-row">
        <div class="wjportal-form-title" for="password">
            <?php echo  esc_html(__("Password","wp-job-portal")); ?> <font>*</font>
        </div>
        <div class="wjportal-form-value">
            <input name="wpjobportal_user_pass" id="password" data-validation="required" class="required wjportal-form-input-field" type="password" placeholder="<?php echo esc_attr(__("Password",'wp-job-portal')); ?>"/>
        </div>
    </div>
    <div class="wjportal-form-row">
        <div class="wjportal-form-title" for="password_again">
            <?php echo esc_html(__('Password Again','wp-job-portal')); ?> <font>*</font>
        </div>
        <div class="wjportal-form-value">
            <input name="wpjobportal_user_pass_confirm" id="password_again" data-validation="required" class="required wjportal-form-input-field" type="password" placeholder="<?php echo esc_attr(__('Password again','wp-job-portal')); ?>"/>
        </div>
    </div>
    <div class="wjportal-form-row wjportal-form-roles">
        <?php
        do_action('wpjobportal_register_form');
        ?>
    </div>
    <?php
    $wpjobportal_config_array = wpjobportal::$_config->getConfigByFor('captcha');
    $wpjobportal_google_recaptcha_3 = false;

    if ($wpjobportal_config_array['cap_on_reg_form'] == 1) {
        if ($wpjobportal_config_array['captcha_selection'] == 1) { // Google recaptcha

            if ($wpjobportal_config_array['recaptcha_version'] == 1) { // Google recaptcha 2
                ?>
                <div class="g-recaptcha" data-sitekey="<?php echo esc_attr($wpjobportal_config_array['recaptcha_publickey']);?>"></div>
                <?php
            }else{
                $wpjobportal_google_recaptcha_3 = true;
            }
        } else { // own captcha
            $wpjobportal_captcha = new WPJOBPORTALcaptcha;
            echo wp_kses($wpjobportal_captcha->getCaptchaForForm(), WPJOBPORTAL_ALLOWED_TAGS);
        }
    }
    echo wp_kses(WPJOBPORTALformfield::hidden('wpjobportalpageid', wpjobportal::wpjobportal_getPageid()), WPJOBPORTAL_ALLOWED_TAGS);
    echo wp_kses(WPJOBPORTALformfield::hidden('wpjobportal_nonce', esc_html(wp_create_nonce('wpjobportal_nonce'))), WPJOBPORTAL_ALLOWED_TAGS);
    ?>
    <div class="wjportal-form-btn-wrp">
        <?php if($wpjobportal_google_recaptcha_3 == false){ ?>
            <input type="submit" id="save" class="button wjportal-form-btn wjportal-save-btn g-recaptcha" value="<?php echo esc_attr(__('Register New Account','wp-job-portal')); ?>"/>
        <?php }else{ ?>
            <input type="submit" id="save" data-sitekey="<?php echo esc_attr($wpjobportal_config_array['recaptcha_publickey']);?>" data-callback='onSubmit' data-action='submit' class="button wjportal-form-btn wjportal-save-btn g-recaptcha" value="<?php echo esc_attr(__('Register New Account','wp-job-portal')); ?>"/>
        <?php } ?>
    </div>
    <input type="hidden" name="wpjobportal_jobs_register_nonce" value="<?php echo esc_attr(wp_create_nonce('wpjobportal-jobs-register-nonce')); ?>"/>
    <?php } ?>
