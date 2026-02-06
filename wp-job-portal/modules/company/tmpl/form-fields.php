<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
/**
 * @param company      company details - optional
 * @param inputprefix  prefix to concat with input name and id - optional
 * @param fields       company fields - optional
 */
$wpjobportal_email = '';
if (!isset($wpjobportal_company) && !wpjobportal::$_common->wpjp_isadmin()) {
	$wpjobportal_company = null;
    if(!empty($wpjobportal_userinfo) && !empty($wpjobportal_userinfo->emailaddress)){
        $wpjobportal_email = $wpjobportal_userinfo->emailaddress;
    }
    if(!empty($userinfo) && !empty($userinfo->emailaddress)){
        $wpjobportal_email = $userinfo->emailaddress;
    }

}else{
    $wpjobportal_email = '';
}
if(WPJOBPORTALincluder::getObjectClass('user')->isguest()){
    $wpjobportal_startNod = "company[";
    $wpjobportal_endnote = "]";
    $wpjobportal_sys = "company";
}else{
    $wpjobportal_startNod = "";
    $wpjobportal_endnote = "";
    $wpjobportal_sys = "";
}
if (!isset($wpjobportal_inputprefix)) {
    $wpjobportal_inputprefix = '';
}
if (!isset($wpjobportal_fields)) {
    $wpjobportal_fields = wpjobportal::$_wpjpfieldordering->getFieldsOrderingforForm(1);
}

$wpjobportal_formfields = array();

foreach($wpjobportal_fields AS $wpjobportal_field){
	$wpjobportal_content = '';
    switch ($wpjobportal_field->field){
        case 'name':
            $wpjobportal_content = WPJOBPORTALformfield::text($wpjobportal_inputprefix.$wpjobportal_startNod.'name'.$wpjobportal_endnote, isset($wpjobportal_company->name) ? $wpjobportal_company->name : null, array('data-validation' => $wpjobportal_field->validation,'placeholder' => wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->placeholder),'class' => 'inputbox wjportal-form-input-field'));
        break;
        case 'tagline':
            //if(in_array('tag', wpjobportal::$_active_addons)){
                $wpjobportal_content = WPJOBPORTALformfield::text($wpjobportal_inputprefix.$wpjobportal_startNod.'tagline'.$wpjobportal_endnote, isset($wpjobportal_company->tagline) ? $wpjobportal_company->tagline : '',array('data-validation' => $wpjobportal_field->validation,'placeholder' => wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->placeholder),'class' => 'inputbox wjportal-form-input-field'));;
            ///}
        break;
        case 'contactemail':
            $wpjobportal_content = WPJOBPORTALformfield::email($wpjobportal_inputprefix.$wpjobportal_startNod.'contactemail'.$wpjobportal_endnote,isset($wpjobportal_company->contactemail) ? $wpjobportal_company->contactemail : $wpjobportal_email,array('data-validation' => 'email'.'  '.$wpjobportal_field->validation,'placeholder' => wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->placeholder),'class' => 'inputbox wjportal-form-input-field'));
        break;
        case 'url':
            $wpjobportal_content = WPJOBPORTALformfield::text($wpjobportal_inputprefix.$wpjobportal_startNod.'url'.$wpjobportal_endnote, isset($wpjobportal_company->url) ? $wpjobportal_company->url : '',array('data-validation' => $wpjobportal_field->validation,'placeholder' => wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->placeholder),'class' => 'inputbox wjportal-form-input-field'));
        break;
        case 'description':
        	$wpjobportal_content = WPJOBPORTALformfield::editor($wpjobportal_inputprefix.$wpjobportal_sys.'description', isset($wpjobportal_company->description) ? $wpjobportal_company->description : '',array('class' => 'wjportal-form-textarea-field'));
        break;
        case 'city':
            $wpjobportal_content = WPJOBPORTALformfield::text($wpjobportal_inputprefix.$wpjobportal_sys.'city', '',array('data-validation' => $wpjobportal_field->validation,'placeholder' => wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->placeholder),'class'=>'wpjobportal-company-form-city-field'));
            $wpjobportal_content .= WPJOBPORTALformfield::hidden('cityforedit', isset($wpjobportal_company->multicity) ? $wpjobportal_company->multicity : '');
        break;
        case 'address1':
            $wpjobportal_content = WPJOBPORTALformfield::text($wpjobportal_inputprefix.$wpjobportal_startNod.'address1'.$wpjobportal_endnote, isset($wpjobportal_company->address1) ? $wpjobportal_company->address1 : '',array('data-validation' => $wpjobportal_field->validation,'placeholder' => wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->placeholder),'class' => 'inputbox wjportal-form-input-field'));
        break;
        case 'address2':
            $wpjobportal_content = WPJOBPORTALformfield::text($wpjobportal_inputprefix.$wpjobportal_startNod.'address2'.$wpjobportal_endnote, $wpjobportal_company ? $wpjobportal_company->address2 : '',array('data-validation' => $wpjobportal_field->validation,'placeholder' => wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->placeholder),'class' => 'inputbox wjportal-form-input-field'));
            break;
        case 'logo':
        	ob_start();
            $wpjobportal_diplay_prop = '';
            $wpjobportal_logo_name = isset($wpjobportal_company->logofilename) ? esc_html($wpjobportal_company->logofilename) : '';
            if($wpjobportal_logo_name == ''){
                $wpjobportal_diplay_prop = 'none';
            }
            ?>
            <div class="wjportal-form-upload">
                <div class="wjportal-form-upload-btn-wrp">
                    <span class="wjportal-form-upload-btn-wrp-txt" style="display: <?php echo esc_attr($wpjobportal_diplay_prop); ?>"><?php echo esc_html($wpjobportal_logo_name); ;?> </span>
                    <span class="wjportal-form-upload-btn">
                        <?php echo esc_html(__('Upload Image','wp-job-portal')); ?>
                        <input id="logo" name="logo" type="file">
                    </span>
                </div>
                <?php
                if (isset($wpjobportal_company->logofilename) && $wpjobportal_company->logofilename != "") {
                    $wpjobportal_data_directory = WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue('data_directory');
                    $wpjobportal_wpdir = wp_upload_dir();
                    $wpjobportal_path = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/employer/comp_' . $wpjobportal_company->id . '/logo/' . $wpjobportal_company->logofilename;
                    $wpjobportal_class = '';
                }else{
                    $wpjobportal_path = '';
                    $wpjobportal_class = 'none';
                }?>
                <div class="wjportal-form-image-wrp" style="display:<?php echo esc_attr($wpjobportal_class); ?> ;">
                    <img class="rs_photo wjportal-form-image" src="<?php echo esc_url($wpjobportal_path); ?>" id="rs_photo" />
                    <img id="wjportal-form-delete-image" src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL);?>includes/images/no.png" alt="<?php echo esc_attr(__('cross','wp-job-portal')); ?>">
                </div>
                <?php
                $wpjobportal_logoformat = wpjobportal::$_config->getConfigValue('image_file_type');
                $wpjobportal_maxsize = wpjobportal::$_config->getConfigValue('company_logofilezize');
                echo '<div class="wjportal-form-help-txt">'.esc_html($wpjobportal_logoformat).'</div>';
                echo '<div class="wjportal-form-help-txt">'.esc_html(__("Maximum","wp-job-portal")).' '.esc_html($wpjobportal_maxsize).' Kb'.'</div>';
                ?>

            </div>
                <?php
                $wpjobportal_content = ob_get_clean();
        break;
        case 'facebook_link':
            $wpjobportal_content = WPJOBPORTALformfield::text($wpjobportal_inputprefix.$wpjobportal_startNod.'facebook_link'.$wpjobportal_endnote, $wpjobportal_company ? $wpjobportal_company->facebook_link : '',array('data-validation' => $wpjobportal_field->validation,'placeholder' => wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->placeholder),'class' => 'inputbox wjportal-form-input-field'));
            break;
        case 'youtube_link':
            $wpjobportal_content = WPJOBPORTALformfield::text($wpjobportal_inputprefix.$wpjobportal_startNod.'youtube_link'.$wpjobportal_endnote, $wpjobportal_company ? $wpjobportal_company->youtube_link : '',array('data-validation' => $wpjobportal_field->validation,'placeholder' => wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->placeholder),'class' => 'inputbox wjportal-form-input-field'));
            break;
        case 'twiter_link':
            $wpjobportal_content = WPJOBPORTALformfield::text($wpjobportal_inputprefix.$wpjobportal_startNod.'twiter_link'.$wpjobportal_endnote, $wpjobportal_company ? $wpjobportal_company->twiter_link : '',array('data-validation' => $wpjobportal_field->validation,'placeholder' => wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->placeholder),'class' => 'inputbox wjportal-form-input-field'));
            break;
        case 'linkedin_link':
            $wpjobportal_content = WPJOBPORTALformfield::text($wpjobportal_inputprefix.$wpjobportal_startNod.'linkedin_link'.$wpjobportal_endnote, $wpjobportal_company ? $wpjobportal_company->linkedin_link : '',array('data-validation' => $wpjobportal_field->validation,'placeholder' => wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->placeholder),'class' => 'inputbox wjportal-form-input-field'));
            break;
        case 'termsandconditions':
            if(!isset($wpjobportal_company)){
                $wpjobportal_termsandconditions_flag = 1;
                $wpjobportal_termsandconditions_fieldtitle = $wpjobportal_field->fieldtitle;
                // $wpjobportal_content = get_the_permalink(wpjobportal::$_configuration['terms_and_conditions_page_company']);
            }
            break;
        default:
            $wpjobportal_content = wpjobportal::$_wpjpcustomfield->formCustomFields($wpjobportal_field);
    	break;
    }
    if (!empty($wpjobportal_content)) {
        $wpjobportal_formfields[] = array(
            'wpjobportal_field' => $wpjobportal_field,
            'wpjobportal_content' => $wpjobportal_content
        );
    }
}
return $wpjobportal_formfields;
