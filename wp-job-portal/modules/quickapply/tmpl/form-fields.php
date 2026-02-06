<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
/**

 */
$wpjobportal_email = '';

$wpjobportal_fields = wpjobportal::$_wpjpfieldordering->getFieldsOrderingforForm(5);

$wpjobportal_formfields = array();

foreach($wpjobportal_fields AS $wpjobportal_field){
    // If the Elegant Design feature is enabled, set the field placeholder to the field title if it's empty.
    if(WPJOBPORTALincluder::getJSModel('common')->isElegantDesignEnabled()){
        if (empty($wpjobportal_field->placeholder)) {
            $wpjobportal_field->placeholder = $wpjobportal_field->fieldtitle;
        }
    }
	$wpjobportal_content = '';
    switch ($wpjobportal_field->field){
        case 'full_name':
            $wpjobportal_content = WPJOBPORTALformfield::text('full_name', null, array('data-validation' => $wpjobportal_field->validation,'placeholder' => wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->placeholder),'class' => 'inputbox wjportal-form-input-field'));
        break;
        case 'email':
            $wpjobportal_content = WPJOBPORTALformfield::text('email', null, array('data-validation' => 'email'.'  '.$wpjobportal_field->validation,'placeholder' => wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->placeholder),'class' => 'inputbox wjportal-form-input-field'));
        break;
        case 'phone':
            $wpjobportal_content = WPJOBPORTALformfield::text('phone', null, array('data-validation' => $wpjobportal_field->validation,'placeholder' => wpjobportal::wpjobportal_getVariableValue($wpjobportal_field->placeholder),'class' => 'inputbox wjportal-form-input-field'));
        break;
        case 'message':
            $wpjobportal_content = WPJOBPORTALformfield::textarea('message', '', array('class' => 'inputbox one wjportal-form-textarea-field', 'rows' => '7', 'cols' => '25', $wpjobportal_field->validation));
        break;
        case 'resume':
        	ob_start();
            ?>
            <div class="wjportal-form-upload">
                <div class="wjportal-form-upload-btn-wrp">
                    <span class="wjportal-form-upload-btn-wrp-txt" style="display: none;"></span>
                    <span class="wjportal-form-upload-btn">
                        <?php echo esc_html(__('Upload Resume','wp-job-portal')); ?>
                        <input id="resumefiles" name="resumefiles" type="file" >
                    </span>
                </div>
                <?php
                $wpjobportal_logoformat = wpjobportal::$_config->getConfigValue('document_file_type');
                $wpjobportal_maxsize = wpjobportal::$_config->getConfigValue('document_file_size');
                echo '<div class="wjportal-form-help-txt">'.esc_html($wpjobportal_logoformat).'</div>';
                echo '<div class="wjportal-form-help-txt">'.esc_html(__("Maximum","wp-job-portal")).' '.esc_html($wpjobportal_maxsize).' Kb'.'</div>';
                ?>

            </div>
                <?php
                $wpjobportal_content = ob_get_clean();
        break;

        default:
            //$wpjobportal_content = wpjobportal::$_wpjpcustomfield->formCustomFields($wpjobportal_field);
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
