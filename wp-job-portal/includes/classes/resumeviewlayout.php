<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALResumeViewlayout {

    public $config_array_sec=array();
    public $themecall = 0;
    public $class_prefix = '';


    function __construct(){
        $this->config_array_sec = wpjobportal::$_config->getConfigByFor('resume');
        $wpjobportal_fieldsordering = wpjobportal::$_wpjpfieldordering->getFieldsOrderingforForm(3); // resume fields
        wpjobportal::$_data[2] = array();
        foreach ($wpjobportal_fieldsordering AS $wpjobportal_field) {
            wpjobportal::$wpjobportal_data['fieldtitles'][$wpjobportal_field->field] = $wpjobportal_field->fieldtitle;
            wpjobportal::$_data[2][$wpjobportal_field->section][$wpjobportal_field->field] = $wpjobportal_field;
        }
        if(wpjobportal::$wpjobportal_theme_chk == 2){/// code to manage class prefix for diffrent template cases
            $this->class_prefix = 'jsjb-jh';
            $this->themecall = 2;

        }elseif(wpjobportal::$wpjobportal_theme_chk == 1){
            $this->class_prefix = 'wpj-jp';
            $this->themecall = 1;
        }else{
            $this->class_prefix = '';
        }

    }
    function getRowMapForView($wpjobportal_text, $longitude, $latitude,$wpjobportal_themecall=null) {
        $wpjobportal_id = "div-id".uniqid();// unidiq might cause problem for starting with number value
        if(null != $wpjobportal_themecall){
            $wpjobportal_html = '<div class="'.esc_attr($this->class_prefix).'-resumedetail-address-map-wrap">
                        <div class="'.esc_attr($this->class_prefix).'-resumedetail-address-map">
                            <span class="'.esc_attr($this->class_prefix).'-resumedetail-address-map-showhide"><img src="' . JOB_PORTAL_THEME_IMAGE . '/cu_loc.png" class="image"/></span>
                            ' . $wpjobportal_text . '
                        </div>
                        <div class="'.esc_attr($this->class_prefix).'-resumedetail-address-map-area" style="display: none;">
                            <div class="'.esc_attr($this->class_prefix).'-map-inner">
                                <div id="'.esc_attr($this->class_prefix).'-map" style="position: relative; overflow: hidden;">
                                    <div id="' . $wpjobportal_id . '" class="map" style="width:100%;min-height:200px;">' . esc_attr($longitude) . ' - ' . esc_html($latitude) . '</div>
                                </div>
                            </div>
                        </div>';
                        wp_register_script( 'wpjobportal-inline-handle', '' );
                        wp_enqueue_script( 'wpjobportal-inline-handle' );
                        $wpjobportal_inline_js_script = '
                            jQuery(document).ready(function(){
                                initialize("' . esc_attr($latitude) . '","' . esc_attr($longitude) . '","' . esc_attr($wpjobportal_id) . '");
                            });';
                        wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
                        $wpjobportal_html .='
                    </div>';
        }else{
            $wpjobportal_html = '<div class="resume-map">
                    <div class="row-title"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/resume/hide-map.png" class="image"/>' . esc_html($wpjobportal_text) . '</div>
                    <div class="row-value"><div id="' . esc_attr($wpjobportal_id) . '" class="map" style="width:100%;min-height:200px;">' . esc_html($longitude) . ' - ' . esc_html($latitude) . '</div></div>
                    ';
                    wp_register_script( 'wpjobportal-inline-handle', '' );
                    wp_enqueue_script( 'wpjobportal-inline-handle' );
                    $wpjobportal_inline_js_script = '
                        jQuery(document).ready(function() {
                            initialize("' . esc_attr($latitude) . '","' . esc_attr($longitude) . '","' . esc_attr($wpjobportal_id) . '");
                        });
                    ';
                    wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
                    $wpjobportal_html .='
                </div>';
        }
        return $wpjobportal_html;
    }


    function getAttachmentRowForViewJobManager($adminLogin) {
        return $this->getAttachmentRowForViewForTemplate($adminLogin);
    }

    function getAttachmentRowForViewJobHub($adminLogin) {
        return $this->getAttachmentRowForViewForTemplate($adminLogin);
    }

    function getAttachmentRowForViewForTemplate($adminLogin) {
        $wpjobportal_html='<div id="'.esc_attr($this->class_prefix).'-resumedetail-attachment" class="'.esc_attr($this->class_prefix).'-resumedetail-section">
            <div class="'.esc_attr($this->class_prefix).'-resumedetail-section-title">
                <span class="'.esc_attr($this->class_prefix).'-resumedetail-section-icon">
                    <img alt="attachment" title="attachment" src="'.JOB_PORTAL_THEME_IMAGE.'/attchments.png">
                </span>
                <h5 class="'.esc_attr($this->class_prefix).'-resumedetail-section-txt">
                    '.esc_html(__("Attachment","wp-job-portal")).'
                </h5>
            </div>
            <div class="'.esc_attr($this->class_prefix).'-resumedetail-sec-data">
                <div class="'.esc_attr($this->class_prefix).'-resumedetail-sec-download">
                    <div class="input-group">';
                        foreach (wpjobportal::$_data[0]['file_section'] AS $file) {
                            $files=$file->filename;
                            $wpjobportal_exp_extension = wpjobportalphplib::wpJP_explode(".", $files);
                            $wpjobportal_extension = end($wpjobportal_exp_extension);
                            $filename=wpjobportalphplib::wpJP_substr($files,'0','3')."...";
                            //$file_id_string = WPJOBPORTALincluder::getJSModel('common')->encodeIdForDownload($file->id);
                            $wpjobportal_html .= '<a target="_blank" href="' . esc_url(wp_nonce_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'action'=>'wpjobportaltask', 'task'=>'getresumefiledownloadbyid', 'wpjobportalid'=>$file->id, 'wpjobportalpageid'=>WPJOBPORTALRequest::getVar('wpjobportalpageid'))),'wpjobportal_resume_nonce'.$file->id)) . '" class="file">
                                        <span class="wpjp-filename">' . esc_html($filename) . '</span><span class="wpjp-fileext">'.esc_html($wpjobportal_extension).'</span>
                                        <i class="fa fa-download download" aria-hidden="true"></i>
                                    </a>';
                        }
                    $wpjobportal_html .='</div>';
                    if(!empty(wpjobportal::$_data[0]['file_section']) && (wpjobportal::$wpjobportal_data['resumecontactdetail'] == true || $adminLogin)){
                         $wpjobportal_html .= apply_filters('wpjobportal_addons_resume_action_ResumeFile',false,wpjobportal::$_data[0]['personal_section']);
                    }
                $wpjobportal_html .= '</div>
            </div>
        </div>';
        return $wpjobportal_html;
    }

    function getAttachmentRowForView($wpjobportal_text,$wpjobportal_themecall=null) {
        if(null !=$wpjobportal_themecall) return;
        $wpjobportal_html = '<div class="wjportal-resume-sec-row wjportal-resume-attachments-wrp">
                    <div class="wjportal-resume-sec-data wjportal-resume-row-full-width">
                        <div class="wjportal-resume-sec-data-title">' . wpjobportal::wpjobportal_getVariableValue($wpjobportal_text) . ':</div>
                        <div class="wjportal-resume-sec-data-value">';
        if (!empty(wpjobportal::$_data[0]['file_section'])) {
            foreach (wpjobportal::$_data[0]['file_section'] AS $file) {
                //$file_id_string = WPJOBPORTALincluder::getJSModel('common')->encodeIdForDownload($file->id);
                $wpjobportal_html .= '<a target="_blank" href="' . esc_url(wp_nonce_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'action'=>'wpjobportaltask', 'task'=>'getresumefiledownloadbyid', 'wpjobportalid'=>$file->id, 'wpjobportalpageid'=>WPJOBPORTALRequest::getVar('wpjobportalpageid'))),'wpjobportal_resume_nonce'.$file->id)) . '" class="file">
                            <span class="wjportal-resume-attachment-filename">' . $file->filename . '</span>
                            <span class="wjportal-resume-attachment-file-ext"></span>
                            <img class="wjportal-resume-attachment-file-download" src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/resume/download.png" />
                        </a>';
            }
        }
        $wpjobportal_html .= '      </div>
                    </div>
                </div>';
        return $wpjobportal_html;
    }


    function getResumeSection($wpjobportal_resumeformview, $call, $viewlayout = 0,$wpjobportal_themecall=null) {
        $wpjobportal_html = '';
        if ($wpjobportal_resumeformview == 0) { // edit form
            $wpjobportal_html .= '<div class="wjportal-resume-section-wrapper '.esc_attr($this->class_prefix).'-resumedetail-sec-data" data-section="resume" data-sectionid="">';
            $wpjobportal_i = 0;
            foreach (wpjobportal::$_data[2][6] AS $wpjobportal_field => $wpjobportal_required) {
                switch ($wpjobportal_field) {
                    case 'resume':
                        if(null==$wpjobportal_themecall){
                            if ($wpjobportal_i % 2 != 0) { // close the div if one field is print and the function is finished;
                                $wpjobportal_html .= '</div>'; // closing div for the more option
                            }
                        }
                        $wpjobportal_value = wpjobportal::$_data[0]['personal_section']->resume;
                        $wpjobportal_html .= '<div class="resume-section-data">' . $wpjobportal_value . '</div>';
                        $wpjobportal_i = 0;
                        break;
                    default:
                        $wpjobportal_array = wpjobportal::$_wpjpcustomfield->showCustomFields($wpjobportal_field, 11,wpjobportal::$_data[0]['personal_section']->params); //11 for view resume
                        if (is_array($wpjobportal_array))
                            $wpjobportal_html .= $this->getRowForView($wpjobportal_array['title'], $wpjobportal_array['value'], $wpjobportal_i);
                        break;
                }
            }
            if(null==$wpjobportal_themecall){
                if ($wpjobportal_i % 2 != 0) { // close the div if one field is print and the function is finished;
                    $wpjobportal_html .= '</div>'; // closing div for the more option
                }
            }
            $wpjobportal_html .= '</div>';
        }
        return $wpjobportal_html;
    }



    function getEmployerSection($wpjobportal_resumeformview, $call, $viewlayout = 0,$wpjobportal_themecall=null) {
        $wpjobportal_html = '';
        if(in_array('advanceresumebuilder', wpjobportal::$_active_addons)){
        if ($wpjobportal_resumeformview == 0) { // edit form
            if (!empty(wpjobportal::$_data[0]['employer_section'][0])){
                // section heading to print only once and printing it from field ordering
                $wpjobportal_html .= '<div class="wjportal-resume-section-title">' . esc_html($this->getFieldTitleByField('section_employer')) . '</div>';
                foreach (wpjobportal::$_data[0]['employer_section'] AS $wpjobportal_employer) {
                    $wpjobportal_html .= '<div class="wjportal-resume-section-wrapper '.esc_attr($this->class_prefix).'-resumedetail-sec-data" data-section="employers" data-sectionid="' . $wpjobportal_employer->id . '">';
                    $wpjobportal_i = 0;
                    $wpjobportal_value = $wpjobportal_employer->employer;
                    if( ($wpjobportal_employer->employer_from_date != '' && !strstr($wpjobportal_employer->employer_from_date, '1970')) && ($wpjobportal_employer->employer_to_date != '' && !strstr($wpjobportal_employer->employer_to_date, '1970')) ){
                        $wpjobportal_value .= '<span class="wpjp-resume-employer-dates">(' . date_i18n('M Y', strtotime($wpjobportal_employer->employer_from_date)) . ' - ' . date_i18n('M Y', strtotime($wpjobportal_employer->employer_to_date)) . ')</span>';
                    }
                    if ($viewlayout == 0) {
                        $wpjobportal_value .= '<a class="edit" href="#"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/edit-resume.png" /></a>';
                        $wpjobportal_value .= '<a class="delete" href="#"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/delete-resume.png" /></a>';
                    }
                    $wpjobportal_html .= $this->getHeadingRowForView($wpjobportal_value,$wpjobportal_themecall);
                    foreach (wpjobportal::$_data[2][4] AS $wpjobportal_field => $wpjobportal_required) {
                        switch ($wpjobportal_field) {
                            case 'employer_position':
                                $wpjobportal_text = $this->getFieldTitleByField($wpjobportal_field);
                                $wpjobportal_value = $wpjobportal_employer->employer_position;
                                $wpjobportal_html .= $this->getRowForView($wpjobportal_text, $wpjobportal_value, $wpjobportal_i,$wpjobportal_themecall,1);
                                break;
                            case 'employer_city':
                                $wpjobportal_text = $this->getFieldTitleByField($wpjobportal_field);
                                $wpjobportal_value = wpjobportal::$_common->getLocationForView($wpjobportal_employer->cityname, '', '');
                                $wpjobportal_html .= $this->getRowForView($wpjobportal_text, $wpjobportal_value, $wpjobportal_i,$wpjobportal_themecall,1);
                                break;
                            /*EMPLOYEER STATUS IM AVAILABLE     */
                             case 'employer_current_status':
                                $wpjobportal_text = $this->getFieldTitleByField($wpjobportal_field);
                                $wpjobportal_value = $wpjobportal_employer->employer_current_status;
                                if($wpjobportal_value!="" && $wpjobportal_value==1){
                                    $wpjobportal_originalDate = $wpjobportal_employer->employer_from_date;
                                    $currentDate  = gmdate('d/m/Y');
                                    $multidate=human_time_diff(strtotime($wpjobportal_originalDate),strtotime(date_i18n("Y-m-d H:i:s")));
                                    /*
                                    $duration=wpjobportal::$_common->getYearMonth($mkarray);
                                    $multidate='';
                                    foreach ($duration as $wpjobportal_key => $wpjobportal_value) {
                                        $wpjobportal_name=array_search($wpjobportal_value,$duration);
                                        switch ($wpjobportal_name) {
                                            case 'years':
                                                if($wpjobportal_value>0){
                                                $multidate.=' '.$wpjobportal_value.'  '.wpjobportalphplib::wpJP_strtoupper($wpjobportal_name);
                                                }
                                                break;
                                            case 'month':
                                               if($wpjobportal_value>0){
                                                $multidate.=' '.$wpjobportal_value.'  '.wpjobportalphplib::wpJP_strtoupper($wpjobportal_name);
                                                }
                                                break;
                                            case 'days':
                                                if($wpjobportal_value>0){
                                                $multidate.=' '.$wpjobportal_value.'  '.wpjobportalphplib::wpJP_strtoupper($wpjobportal_name);
                                                }
                                                break;
                                            default:
                                                if(isset($wpjobportal_value)!=''>0){
                                                $multidate.=' '.$wpjobportal_value.'  '.wpjobportalphplib::wpJP_strtoupper($wpjobportal_name);
                                                }
                                                break;
                                        }
                                    }*/
                                    $wpjobportal_html .= $this->getRowForView($wpjobportal_text, $multidate, $wpjobportal_i,$wpjobportal_themecall,1);
                                }
                                break;
                            case 'employer_phone':
                                $wpjobportal_text = $this->getFieldTitleByField($wpjobportal_field);
                                $wpjobportal_value = $wpjobportal_employer->employer_phone;
                                $wpjobportal_html .= $this->getRowForView($wpjobportal_text, $wpjobportal_value, $wpjobportal_i,$wpjobportal_themecall,1);
                                break;
                            case 'employer_address':
                                $wpjobportal_text = $this->getFieldTitleByField($wpjobportal_field);
                                $wpjobportal_value = $wpjobportal_employer->employer_address;
                                $wpjobportal_html .= $this->getRowForView($wpjobportal_text, $wpjobportal_value, $wpjobportal_i,$wpjobportal_themecall,1);

                                break;

                            default:
                                $wpjobportal_array = wpjobportal::$_wpjpcustomfield->showCustomFields($wpjobportal_field,11,$wpjobportal_employer->params); //11 for view resume
                                if (is_array($wpjobportal_array))
                                    $wpjobportal_html .= $this->getRowForView($wpjobportal_array['title'], $wpjobportal_array['value'], $wpjobportal_i,$wpjobportal_themecall,1);
                                break;
                        }
                    }
                    if(null==$wpjobportal_themecall){
                        if ($wpjobportal_i % 2 != 0) { // close the div if one field is print and the function is finished;
                            $wpjobportal_html .= '</div>';
                        }
                    }
                    $wpjobportal_html .= '</div>'; // section wrapper end;
                }
            }// new if closed (old code had without prenthisis forech below the if now there is title below the if so addded these prensthisis)
        }
        }
        return $wpjobportal_html;
    }

   function getAddressesSection($wpjobportal_resumeformview, $call, $viewlayout = 0,$wpjobportal_themecall=null) {
        $wpjobportal_html = '';
        if(in_array('advanceresumebuilder', wpjobportal::$_active_addons)){
        if ($wpjobportal_resumeformview == 0) { // view address sections
            if (!empty(wpjobportal::$_data[0]['address_section'][0])){
                // section heading to print only once and printing it from field ordering
                $wpjobportal_html .= '<div class="wjportal-resume-section-title">' . esc_html($this->getFieldTitleByField('section_address')) . '</div>';
                foreach (wpjobportal::$_data[0]['address_section'] AS $wpjobportal_address) {
                    $wpjobportal_html .= '<div class="wjportal-resume-section-wrapper '.esc_attr($this->class_prefix).'-resumedetail-sec-data" data-section="addresses" data-sectionid="' . $wpjobportal_address->id . '">';
                    $wpjobportal_i = 0;
                    $loc = 0;
                    $wpjobportal_value = $wpjobportal_address->address;
                    if ($viewlayout == 0) {
                        $wpjobportal_value .= '<a class="edit" href="#"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/edit-resume.png" /></a>';
                        $wpjobportal_value .= '<a class="delete" href="#"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/delete-resume.png" /></a>';
                    }
                    $wpjobportal_html .= $this->getHeadingRowForView($wpjobportal_value,$wpjobportal_themecall);
                    foreach (wpjobportal::$_data[2][2] AS $wpjobportal_field => $wpjobportal_required) {
                        switch ($wpjobportal_field) {
                            case 'address_city':
                            case 'address_state':
                            case 'address_country':
                                if ($loc == 0) {
                                    $wpjobportal_text = $this->getFieldTitleByField($wpjobportal_field);
                                    $wpjobportal_value = wpjobportal::$_common->getLocationForView($wpjobportal_address->cityname, $wpjobportal_address->statename, $wpjobportal_address->countryname);
                                    $wpjobportal_html .= $this->getRowForView($wpjobportal_text, $wpjobportal_value, $wpjobportal_i,$wpjobportal_themecall,1);
                                    $loc++;
                                }
                                break;
                            case 'address_location':
                                if(!empty($wpjobportal_address->longitude) && !empty($wpjobportal_address->latitude)){
                                    $wpjobportal_text = $this->getFieldTitleByField($wpjobportal_field);
                                    $wpjobportal_html .= apply_filters('wpjobportal_addons_map_resune_view',false,$wpjobportal_text,$wpjobportal_address,$wpjobportal_themecall);
                                }
                                break;

                            default:
                                $wpjobportal_array = wpjobportal::$_wpjpcustomfield->showCustomFields($wpjobportal_field,11,$wpjobportal_address->params);
                                //11 for view resume
                                if (is_array($wpjobportal_array))
                                    $wpjobportal_html .= $this->getRowForView($wpjobportal_array['title'], $wpjobportal_array['value'], $wpjobportal_i,$wpjobportal_themecall,1);
                                break;
                        }
                    }
                    if(null==$wpjobportal_themecall){
                        if ($wpjobportal_i % 2 != 0) { // close the div if one field is print and the function is finished;
                            $wpjobportal_html .= '</div>';
                        }
                    }
                $wpjobportal_html .= '</div>'; //section wrapper end;
            }
            } // new if closed the old code had no prenthisis on the if and had a foreach directly below if statement now there is section title
        }
        }
        return $wpjobportal_html;
    }

    function getPersonalSection($wpjobportal_resumeformview, $viewlayout = 0,$wpjobportal_themecall=null) {
        $wpjobportal_is_qucik_apply = 0;
        if(isset(wpjobportal::$_data[0]['personal_section']->quick_apply)){
            $wpjobportal_is_qucik_apply = wpjobportal::$_data[0]['personal_section']->quick_apply;
        }
        $wpjobportal_html = '';
        $wpjobportal_personal=wpjobportal::$_data[0]['personal_section'];
        if ($wpjobportal_resumeformview == 0) { // view section resume
            $wpjobportal_html .= '<div class="wjportal-resume-section-wrapper '.esc_attr($this->class_prefix).'-resumedetail-sec-data" data-section="personal" data-sectionid="">';
            $wpjobportal_i = 0;
            $files_html ='';
            foreach (wpjobportal::$_data[2][1] AS $wpjobportal_field => $wpjobportal_required) {
                switch ($wpjobportal_field) {
                    case 'cell':
                        if (wpjobportal::$wpjobportal_data['resumecontactdetail'] == true) {
                            $wpjobportal_text = $this->getFieldTitleByField($wpjobportal_field);
                            $wpjobportal_value = wpjobportal::$_data[0]['personal_section']->cell;
                            $wpjobportal_html .= $this->getRowForView($wpjobportal_text, $wpjobportal_value, $wpjobportal_i,$wpjobportal_themecall);
                        }
                        break;
                    case 'first_name':// in case of admin view resume first name was not printing at all
                        // only first name field is required so making it visible in content area
                            $wpjobportal_text = $this->getFieldTitleByField($wpjobportal_field);
                            $wpjobportal_value = wpjobportal::$_data[0]['personal_section']->first_name;
                            $wpjobportal_html .= $this->getRowForView($wpjobportal_text, $wpjobportal_value, $wpjobportal_i,$wpjobportal_themecall);
                        break;
                    case 'last_name':// in case of admin view resume last name was not printing at all
                        // only last name field is required so making it visible in content area
                            $wpjobportal_text = $this->getFieldTitleByField($wpjobportal_field);
                            $wpjobportal_value = wpjobportal::$_data[0]['personal_section']->last_name;
                            $wpjobportal_html .= $this->getRowForView($wpjobportal_text, $wpjobportal_value, $wpjobportal_i,$wpjobportal_themecall);
                        break;

                    case 'nationality':
                        if($wpjobportal_is_qucik_apply == 1){ // dont print this field for quick apply resume
                            break;
                        }
                        $wpjobportal_text = $this->getFieldTitleByField($wpjobportal_field);
                        $wpjobportal_value = wpjobportal::$_data[0]['personal_section']->nationality;
                        $wpjobportal_html .= $this->getRowForView($wpjobportal_text, $wpjobportal_value, $wpjobportal_i,$wpjobportal_themecall);
                        break;
                    case 'gender':
                        if($wpjobportal_is_qucik_apply == 1){ // dont print this field for quick apply resume
                            break;
                        }
                        $wpjobportal_text = $this->getFieldTitleByField($wpjobportal_field);
                        $wpjobportal_value = '';
                        switch (wpjobportal::$_data[0]['personal_section']->gender) {
                            case '0':$wpjobportal_value = esc_html(__('Does not matter', 'wp-job-portal'));
                                break;
                            case '1':$wpjobportal_value = esc_html(__('Male', 'wp-job-portal'));
                                break;
                            case '2':$wpjobportal_value = esc_html(__('Female', 'wp-job-portal'));
                                break;
                        }
                        $wpjobportal_html .= $this->getRowForView($wpjobportal_text, $wpjobportal_value, $wpjobportal_i,$wpjobportal_themecall);
                        break;
                    case 'job_category':
                        if($wpjobportal_is_qucik_apply == 1){ // dont print this field for quick apply resume
                            break;
                        }
                        $wpjobportal_text = $this->getFieldTitleByField($wpjobportal_field);
                        $wpjobportal_value = wpjobportal::$_data[0]['personal_section']->categorytitle;
                        $wpjobportal_html .= $this->getRowForView($wpjobportal_text, $wpjobportal_value, $wpjobportal_i,$wpjobportal_themecall);
                        break;
                    case 'jobtype':
                        if($wpjobportal_is_qucik_apply == 1){ // dont print this field for quick apply resume
                            break;
                        }
                        $wpjobportal_text = $this->getFieldTitleByField($wpjobportal_field);
                        $wpjobportal_value = wpjobportal::$_data[0]['personal_section']->jobtypetitle;
                        $wpjobportal_html .= $this->getRowForView($wpjobportal_text, $wpjobportal_value, $wpjobportal_i,$wpjobportal_themecall);
                        break;
                    case 'salaryfixed':
                        if($wpjobportal_is_qucik_apply == 1){ // dont print this field for quick apply resume
                            break;
                        }
                        $wpjobportal_text = $this->getFieldTitleByField($wpjobportal_field);
                        $wpjobportal_value = isset(wpjobportal::$_data[0]['personal_section']->salaryfixed) ?wpjobportal::$_data[0]['personal_section']->salaryfixed : '';
                        $wpjobportal_html .= $this->getRowForView($wpjobportal_text, $wpjobportal_value, $wpjobportal_i,$wpjobportal_themecall,1);
                        break;
                    case 'keywords':
                        if($wpjobportal_is_qucik_apply == 1){ // dont print this field for quick apply resume
                            break;
                        }
                        $wpjobportal_text = $this->getFieldTitleByField($wpjobportal_field);
                        $wpjobportal_value = wpjobportal::$_data[0]['personal_section']->keywords;
                        $wpjobportal_html .= $this->getRowForView($wpjobportal_text, $wpjobportal_value, $wpjobportal_i,$wpjobportal_themecall);
                        break;
                    case 'searchable':
                        if($wpjobportal_is_qucik_apply == 1){ // dont print this field for quick apply resume
                            break;
                        }
                        $wpjobportal_text = $this->getFieldTitleByField($wpjobportal_field);
                        $wpjobportal_value = (wpjobportal::$_data[0]['personal_section']->searchable == 1) ? esc_html(__('Yes','wp-job-portal')) : esc_html(__('No','wp-job-portal'));
                        $wpjobportal_html .= $this->getRowForView($wpjobportal_text, $wpjobportal_value, $wpjobportal_i,$wpjobportal_themecall);
                        break;
                    case 'resumefiles':
                        if (wpjobportal::$wpjobportal_data['resumecontactdetail'] == true) {
                            // $files_html = '';
                            if ($wpjobportal_i % 2 != 0) { // close the div if one field is print and the function is finished;
                                $files_html .= '</div>'; // closing div for the more option
                            }
                            $wpjobportal_text = $this->getFieldTitleByField($wpjobportal_field);
                            $files_html .= $this->getAttachmentRowForView($wpjobportal_text,$wpjobportal_themecall);
                            $wpjobportal_i = 0;
                        }
                        break;
                    default:
                        if($wpjobportal_is_qucik_apply == 1){ // dont print this field for quick apply resume
                            break;
                        }
                        $wpjobportal_array =
                         wpjobportal::$_wpjpcustomfield->showCustomFields($wpjobportal_field,11,wpjobportal::$_data[0]['personal_section']->params,'resume',wpjobportal::$_data[0]['personal_section']->id);// new parameters required for upload field
                        if (is_array($wpjobportal_array)){
                            $wpjobportal_html .= $this->getRowForView($wpjobportal_array['title'], $wpjobportal_array['value'], $wpjobportal_i,$wpjobportal_themecall);
                        }
                        break;
                }
            }
            // printing quick apply message
            $wpjobportal_html .= $files_html;
            if($wpjobportal_is_qucik_apply == 1){
                if ($wpjobportal_i % 2 != 0) { // close the div if one field is print and the function is finished;
                    $wpjobportal_html .= '</div>'; // closing div for the more option
                }
                // fetching apply message field label
                $wpjobportal_text = wpjobportal::$_wpjpfieldordering->getFieldTitleByFieldAndFieldfor('message',5);

                $wpjobportal_value = WPJOBPORTALincluder::getJSModel('jobapply')->getQuickApplyMessageByresume(wpjobportal::$_data[0]['personal_section']->id);
                // $wpjobportal_html .= $this->getRowForView($wpjobportal_text, $wpjobportal_value, $wpjobportal_i,$wpjobportal_themecall,1,1);

                $wpjobportal_html .= '<div class="wjportal-resume-sec-row '.esc_attr($this->class_prefix).'-resumedetail-sec-value wjportal-resume-row-full-width-row">';
                $wpjobportal_html .= '<div class="wjportal-custom-field wjportal-resume-sec-data wjportal-resume-row-full-width">
                        <div class="wjportal-custom-field-tit wjportal-resume-sec-data-title">' . esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_text)) . ':</div>
                        <div class="wjportal-custom-field-val wjportal-resume-sec-data-value">' . esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_value)) . '</div>
                    </div>';
                $wpjobportal_html .= '</div>';

                $wpjobportal_i = 0;
            }


            if ($wpjobportal_i % 2 != 0) { // close the div if one field is print and the function is finished;
                $wpjobportal_html .= '</div>'; // closing div for the more option
            }
            $wpjobportal_html .= '</div>'; //section wrapper end;// commented it to solve issue with design.
        }
        return $wpjobportal_html;
    }

    function getPersonalTopSection($owner, $wpjobportal_resumeformview) {
        $adminLogin = current_user_can('manage_options');
        $wpjobportal_is_qucik_apply = 0;
        if(isset(wpjobportal::$_data[0]['personal_section']->quick_apply)){
            $wpjobportal_is_qucik_apply = wpjobportal::$_data[0]['personal_section']->quick_apply;
        }
        $wpjobportal_html = '<div class="wjportal-resume-top-section">';
        if(!WPJOBPORTALincluder::getJSModel('common')->isElegantDesignEnabled()){
            if($wpjobportal_is_qucik_apply == 0){ // hide photo which is not set in case of quick apply
                if (isset(wpjobportal::$_data[2][1]['photo'])) {
                    $wpjobportal_html .= '<div class="wjportal-resume-image">';
                    $wpjobportal_img = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('jobseeker');
                    if (wpjobportal::$_data[0]['personal_section']->photo != '') {
                        $wpjobportal_wpdir = wp_upload_dir();
                        $wpjobportal_data_directory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
                        $wpjobportal_img = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/jobseeker/resume_' . wpjobportal::$_data[0]['personal_section']->id . '/photo/' . wpjobportal::$_data[0]['personal_section']->photo;
                    }
                    $wpjobportal_html .= '<img src="' . esc_url($wpjobportal_img) . '" />';
                    $wpjobportal_html .= '</div>';
                }
            }
        // only hide photo section if unpublished
            $wpjobportal_html .= '<div class="wjportal-resume-adv-act-wrp">';
            $wpjobportal_layout = WPJOBPORTALrequest::getVar('wpjobportallt');
                if ($wpjobportal_layout != 'printresume') {
                    if ($owner != 1) { // Current user is not owner and (Consider as employer)
                        if(!current_user_can('manage_options') && WPJOBPORTALincluder::getObjectClass('user')->isemployer()){
                            $wpjobportal_html .= apply_filters('wpjobportal_addons_sendMessage_resume',false) ;
                        }
                    }

                    if (wpjobportal::$wpjobportal_data['resumecontactdetail'] == true || $adminLogin) {
                        $wpjobportal_class = '';
                        //PDF + EXCEL HOOK
                            $wpjobportal_html  .= apply_filters('wpjobportal_addons_resume_views_action_for_pdf',false,wpjobportal::$_data[0]['personal_section']->id);
                            $wpjobportal_html  .= apply_filters('wpjobportal_addons_resume_views_action_export',false,wpjobportal::$_data[0]['personal_section']->id);
                       }
                       //PRINT HOOK
                       $wpjobportal_html .= apply_filters('wpjobportal_addons_resume_views_action_for_print',false,wpjobportal::$_data[0]['personal_section']->id);
                    if(!empty(wpjobportal::$_data[0]['file_section']) && (wpjobportal::$wpjobportal_data['resumecontactdetail'] == true || $adminLogin)){
                        //Downloadable File Addons HOOK
                        $wpjobportal_html .= apply_filters('wpjobportal_addons_resume_action_ResumeFile',false,wpjobportal::$_data[0]['personal_section']);
                    }
                    $wpjobportal_html .= apply_filters('wpjobportal_addons_showresume_contact_detail',false,wpjobportal::$_data[0]['personal_section']->id,wpjobportal::$wpjobportal_data['resumecontactdetail'],$adminLogin);

                } elseif ($wpjobportal_layout == 'printresume') {
                    $wpjobportal_html .= '<a href="#" onClick="window.print();" class="grayBtn">' . esc_html(__('Print', 'wp-job-portal')) . '</a>';
                }
            $wpjobportal_html .='</div>';
        }
            $wpjobportal_html .= '<div class="wjportal-personal-data">';

        //getResumeSectionAjax
        if (isset(wpjobportal::$_data[2][1]['first_name']) || isset(wpjobportal::$_data[2][1]['last_name'])) {
            $wpjobportal_layout = WPJOBPORTALrequest::getVar('layout');
            $editsocialclass = '';
            /*if ($wpjobportal_resumeformview == 0 && ($wpjobportal_layout == 'addresume' || $owner == 1)) {
                $wpjobportal_html .= '<a class="personal_section_edit" href="#"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/edit-resume.png" /></a>';
                $editsocialclass = 'editform';
            }elseif($adminLogin || (!is_user_logged_in() && isset($_SESSION['wp-wpjobportal']))) {
                $wpjobportal_html .= '<a class="personal_section_edit" href="#"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/edit-resume.png" /></a>';
                $editsocialclass = 'editform';
            }*/
            $wpjobportal_html .= '<div id="job-info-sociallink" class="' . $editsocialclass . '">';
            if (!empty(wpjobportal::$_data[0]['personal_section']->facebook)) {
                if(wpjobportalphplib::wpJP_strstr(wpjobportal::$_data[0]['personal_section']->facebook, 'http') ){
                    $facebook = wpjobportal::$_data[0]['personal_section']->facebook ;
                }else{
                    $facebook = 'http://'.wpjobportal::$_data[0]['personal_section']->facebook;
                }
                $wpjobportal_html .= '<a href="' . esc_url($facebook) . '" target="_blank"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/scround/fb.png"/></a>';
            }
            if (!empty(wpjobportal::$_data[0]['personal_section']->twitter)) {
                if(wpjobportalphplib::wpJP_strstr(wpjobportal::$_data[0]['personal_section']->twitter, 'http') ){
                    $twitter = wpjobportal::$_data[0]['personal_section']->twitter;
                }else{
                    $twitter = 'http://'.wpjobportal::$_data[0]['personal_section']->twitter;
                }
                $wpjobportal_html .= '<a href="' . esc_url($twitter) . '" target="_blank"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/scround/twitter.png"/></a>';
            }
            if (!empty(wpjobportal::$_data[0]['personal_section']->googleplus)) {
                if(wpjobportalphplib::wpJP_strstr(wpjobportal::$_data[0]['personal_section']->googleplus, 'http') ){
                    $wpjobportal_googleplus = wpjobportal::$_data[0]['personal_section']->googleplus;
                }else{
                    $wpjobportal_googleplus = 'http://'.wpjobportal::$_data[0]['personal_section']->googleplus;
                }
                $wpjobportal_html .= '<a href="' . esc_url($wpjobportal_googleplus) . '" target="_blank"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/scround/gmail.png"/></a>';
            }
            if (!empty(wpjobportal::$_data[0]['personal_section']->linkedin)) {
                if(wpjobportalphplib::wpJP_strstr(wpjobportal::$_data[0]['personal_section']->linkedin, 'http') ){
                    $wpjobportal_linkedin = wpjobportal::$_data[0]['personal_section']->linkedin;
                }else{
                    $wpjobportal_linkedin = 'http://'.wpjobportal::$_data[0]['personal_section']->linkedin;
                }
                $wpjobportal_html .= '<a href="' . esc_url($wpjobportal_linkedin) . '" target="_blank"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/scround/in.png"/></a>';
            }
            $wpjobportal_html .= '</div>';

            $wpjobportal_html .= '</span>';
        }
        if (isset(wpjobportal::$_data[2][1]['application_title'])) {
            $wpjobportal_html .= '<div class="wjportal-resume-title">' . wpjobportal::$_data[0]['personal_section']->application_title . '</div>';
        }
        if (wpjobportal::$wpjobportal_data['resumecontactdetail'] == true || $adminLogin) {
            if (isset(wpjobportal::$_data[2][1]['jobtype'])) {
                if(isset(wpjobportal::$_data[0]) && !empty(wpjobportal::$_data[0]['personal_section']->jobtypetitle)){
                    $wpjobportal_html .= '<div class="wjportal-resume-info"> <span class="wjportal-jobtype" style="background-color: '.wpjobportal::$_data[0]['personal_section']->jobtypecolor.';">'  . esc_html(wpjobportal::wpjobportal_getVariableValue(wpjobportal::$_data[0]['personal_section']->jobtypetitle)) . '</span></div>';
                }
            }
            if (isset(wpjobportal::$_data[2][1]['email_address'])) {
                $wpjobportal_html .= '<div class="wjportal-resume-info"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/email.png" alt="'.esc_attr(__('email','wp-job-portal')).'" title="'.esc_attr(__('email','wp-job-portal')).'" />' . wpjobportal::$_data[0]['personal_section']->email_address . '</div>';
            }

            if (isset(wpjobportal::$_data[2][1]['salaryfixed'])) {
                if(isset(wpjobportal::$_data[0]) && !empty(wpjobportal::$_data[0]['personal_section']->salaryfixed)){
                    $wpjobportal_html .= '<div class="wjportal-resume-info"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/salary.png" alt="'.esc_attr(__('salary','wp-job-portal')).'" title="'.esc_attr(__('salary','wp-job-portal')).'"/>'  . wpjobportal::$_data[0]['personal_section']->salaryfixed . '</div>';
                }
            }
            if (isset(wpjobportal::$_data[2][1]['cell'])) {
                    if(isset(wpjobportal::$_data[0]) && !empty(wpjobportal::$_data[0]['personal_section']->cell)){
                        $wpjobportal_html .= '<div class="wjportal-resume-info"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/number.png" alt="'.esc_attr(__('number','wp-job-portal')).'"title="'.esc_html(__('number','wp-job-portal')).'" />'  . wpjobportal::$_data[0]['personal_section']->cell . '</div>';
                    }
            }

            if (isset(wpjobportal::$_data[2][2]['address'])) {
                if(isset(wpjobportal::$_data[0]) && !empty(wpjobportal::$_data[0]['personal_section']->address)){
                    $wpjobportal_address = isset(wpjobportal::$_data[0]['address_section'][0]) ?  wpjobportal::$_data[0]['address_section'][0]->address : '';
                    $wpjobportal_country = isset(wpjobportal::$_data[0]['address_section'][0]) ? wpjobportal::$_data[0]['address_section'][0]->countryname : '';
                    $wpjobportal_html .= '<div class="wjportal-resume-info"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/location.png" alt="'.esc_attr(__('location','wp-job-portal')).'" title="'.esc_attr(__('location','wp-job-portal')).'"/>' . $wpjobportal_address.','.$wpjobportal_country . '</div>';
                }
            }

        }
        $wpjobportal_html .= '</div>'; // close for the inner section
        $wpjobportal_html .= '</div>'; // closing div of resume-top-section
        return $wpjobportal_html;
    }

    function getResumeLeftSection($owner, $wpjobportal_resumeformview) {
        $adminLogin = current_user_can('manage_options');
        $wpjobportal_is_qucik_apply = 0;
        if(isset(wpjobportal::$_data[0]['personal_section']->quick_apply)){
            $wpjobportal_is_qucik_apply = wpjobportal::$_data[0]['personal_section']->quick_apply;
        }
        $wpjobportal_html = '<div class="wjportal-resume-left-section">';


        $wpjobportal_resume_object = wpjobportal::$_data[0]['personal_section'];
        $wpjobportal_dateformat    = wpjobportal::$_configuration['date_format'];
        $wpjobportal_curdate       = date_i18n('Y-m-d');
        $wpjobportal_featuredexpiry = date_i18n('Y-m-d', strtotime($wpjobportal_resume_object->endfeatureddate));

        if ($wpjobportal_resume_object->isfeaturedresume == 1 && $wpjobportal_featuredexpiry >= $wpjobportal_curdate) {
            $wpjobportal_expiry_text = ($wpjobportal_resume_object->isfeaturedresume == 1)
                ? __("Expiry Date", "wp-job-portal") . ': ' . esc_html(date_i18n($wpjobportal_dateformat, strtotime($wpjobportal_resume_object->endfeatureddate)))
                : __('Featured Approval Waiting', 'wp-job-portal');

            $wpjobportal_html .= '
                <span class="wjportal-featured-tag-icon-wrp">
                    <span class="wjportal-featured-tag-icon">
                        <i class="fa fa-star"></i>
                    </span>
                    <span class="featurednew-onhover wjportal-featured-hover-wrp" id="gold' . esc_attr($wpjobportal_resume_object->id) . '" style="display:none">
                        ' . $wpjobportal_expiry_text . '
                    </span>
                </span>';
        }


        $wpjobportal_html .= '<div class="wjportal-personal-data">';
        if(!WPJOBPORTALincluder::getJSModel('common')->isElegantDesignEnabled()){
            if($wpjobportal_is_qucik_apply == 0){ // hide photo which is not set in case of quick apply
                if (isset(wpjobportal::$_data[2][1]['photo'])) {
                    $wpjobportal_html .= '<div class="wjportal-resume-image">';
                    $wpjobportal_img = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('jobseeker');
                    if (wpjobportal::$_data[0]['personal_section']->photo != '') {
                        $wpjobportal_wpdir = wp_upload_dir();
                        $wpjobportal_data_directory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
                        $wpjobportal_img = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/jobseeker/resume_' . wpjobportal::$_data[0]['personal_section']->id . '/photo/' . wpjobportal::$_data[0]['personal_section']->photo;
                    }
                    $wpjobportal_html .= '<img src="' . esc_url($wpjobportal_img) . '" />';
                    $wpjobportal_html .= '</div>';
                }
            }
            // only hide photo section if unpublished
        }

        //getResumeSectionAjax
        if (isset(wpjobportal::$_data[2][1]['first_name']) || isset(wpjobportal::$_data[2][1]['last_name'])) {
            $wpjobportal_layout = WPJOBPORTALrequest::getVar('layout');
            $editsocialclass = '';
            /*if ($wpjobportal_resumeformview == 0 && ($wpjobportal_layout == 'addresume' || $owner == 1)) {
                $wpjobportal_html .= '<a class="personal_section_edit" href="#"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/edit-resume.png" /></a>';
                $editsocialclass = 'editform';
            }elseif($adminLogin || (!is_user_logged_in() && isset($_SESSION['wp-wpjobportal']))) {
                $wpjobportal_html .= '<a class="personal_section_edit" href="#"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/edit-resume.png" /></a>';
                $editsocialclass = 'editform';
            }*/
            $wpjobportal_html .= '<div id="job-info-sociallink" class="' . $editsocialclass . '">';
            if (!empty(wpjobportal::$_data[0]['personal_section']->facebook)) {
                if(wpjobportalphplib::wpJP_strstr(wpjobportal::$_data[0]['personal_section']->facebook, 'http') ){
                    $facebook = wpjobportal::$_data[0]['personal_section']->facebook ;
                }else{
                    $facebook = 'http://'.wpjobportal::$_data[0]['personal_section']->facebook;
                }
                $wpjobportal_html .= '<a href="' . esc_url($facebook) . '" target="_blank"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/scround/fb.png"/></a>';
            }
            if (!empty(wpjobportal::$_data[0]['personal_section']->twitter)) {
                if(wpjobportalphplib::wpJP_strstr(wpjobportal::$_data[0]['personal_section']->twitter, 'http') ){
                    $twitter = wpjobportal::$_data[0]['personal_section']->twitter;
                }else{
                    $twitter = 'http://'.wpjobportal::$_data[0]['personal_section']->twitter;
                }
                $wpjobportal_html .= '<a href="' . esc_url($twitter) . '" target="_blank"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/scround/twitter.png"/></a>';
            }
            if (!empty(wpjobportal::$_data[0]['personal_section']->googleplus)) {
                if(wpjobportalphplib::wpJP_strstr(wpjobportal::$_data[0]['personal_section']->googleplus, 'http') ){
                    $wpjobportal_googleplus = wpjobportal::$_data[0]['personal_section']->googleplus;
                }else{
                    $wpjobportal_googleplus = 'http://'.wpjobportal::$_data[0]['personal_section']->googleplus;
                }
                $wpjobportal_html .= '<a href="' . esc_url($wpjobportal_googleplus) . '" target="_blank"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/scround/gmail.png"/></a>';
            }
            if (!empty(wpjobportal::$_data[0]['personal_section']->linkedin)) {
                if(wpjobportalphplib::wpJP_strstr(wpjobportal::$_data[0]['personal_section']->linkedin, 'http') ){
                    $wpjobportal_linkedin = wpjobportal::$_data[0]['personal_section']->linkedin;
                }else{
                    $wpjobportal_linkedin = 'http://'.wpjobportal::$_data[0]['personal_section']->linkedin;
                }
                $wpjobportal_html .= '<a href="' . esc_url($wpjobportal_linkedin) . '" target="_blank"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/scround/in.png"/></a>';
            }
            $wpjobportal_html .= '</div>';

            $wpjobportal_html .= '</span>';
        }
        if (isset(wpjobportal::$_data[2][1]['application_title'])) {
            $wpjobportal_html .= '<div class="wjportal-resume-title">' . esc_html(wpjobportal::$_data[0]['personal_section']->application_title) . '</div>';
        }
            if (isset(wpjobportal::$_data[2][1]['jobtype'])) {
                if(isset(wpjobportal::$_data[0]) && !empty(wpjobportal::$_data[0]['personal_section']->jobtypetitle)){
                    $wpjobportal_html .= '<div class="wjportal-resume-info  wjportal-resume-info-jobtype"> <span class="wjportal-jobtype" style="background-color: '.wpjobportal::$_data[0]['personal_section']->jobtypecolor.';">'  . esc_html(wpjobportal::wpjobportal_getVariableValue(wpjobportal::$_data[0]['personal_section']->jobtypetitle)) . '</span></div>';
                }
            }
        if (wpjobportal::$wpjobportal_data['resumecontactdetail'] == true || $adminLogin) {
            if (isset(wpjobportal::$_data[2][1]['email_address'])) {
                $wpjobportal_html .= '<div class="wjportal-resume-info  wjportal-resume-info-email-address"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/email.png" alt="'.esc_attr(__('email','wp-job-portal')).'" title="'.esc_attr(__('email','wp-job-portal')).'" />' . wpjobportal::$_data[0]['personal_section']->email_address . '</div>';
            }
        }

            if (isset(wpjobportal::$_data[2][1]['salaryfixed'])) {
                if(isset(wpjobportal::$_data[0]) && !empty(wpjobportal::$_data[0]['personal_section']->salaryfixed)){
                    $wpjobportal_html .= '<div class="wjportal-resume-info  wjportal-resume-info-salary"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/salary.png" alt="'.esc_attr(__('salary','wp-job-portal')).'" title="'.esc_attr(__('salary','wp-job-portal')).'"/>'  . wpjobportal::$_data[0]['personal_section']->salaryfixed . '</div>';
                }
            }
        if (wpjobportal::$wpjobportal_data['resumecontactdetail'] == true || $adminLogin) {
            if (isset(wpjobportal::$_data[2][1]['cell'])) {
                    if(isset(wpjobportal::$_data[0]) && !empty(wpjobportal::$_data[0]['personal_section']->cell)){
                        $wpjobportal_html .= '<div class="wjportal-resume-info wjportal-resume-info-cell "><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/number.png" alt="'.esc_attr(__('number','wp-job-portal')).'"title="'.esc_html(__('number','wp-job-portal')).'" />'  . wpjobportal::$_data[0]['personal_section']->cell . '</div>';
                    }
            }

            if (isset(wpjobportal::$_data[2][2]['address'])) {
                if(isset(wpjobportal::$_data[0]) && !empty(wpjobportal::$_data[0]['personal_section']->address)){
                    $wpjobportal_address = isset(wpjobportal::$_data[0]['address_section'][0]) ?  wpjobportal::$_data[0]['address_section'][0]->address : '';
                    $wpjobportal_country = isset(wpjobportal::$_data[0]['address_section'][0]) ? wpjobportal::$_data[0]['address_section'][0]->countryname : '';
                    $wpjobportal_html .= '<div class="wjportal-resume-info"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/location.png" alt="'.esc_attr(__('location','wp-job-portal')).'" title="'.esc_attr(__('location','wp-job-portal')).'"/>' . $wpjobportal_address.','.$wpjobportal_country . '</div>';
                }
            }
        }

        $wpjobportal_html .= '</div>'; // close for the inner section
        // only show wrapper if any of the buttons is active
        $wpjobportal_extra_actions_html = '';
        $wpjobportal_layout = WPJOBPORTALrequest::getVar('wpjobportallt');
            if ($wpjobportal_layout != 'printresume') {
                if ($owner != 1) { // Current user is not owner and (Consider as employer)
                    if(!current_user_can('manage_options') && WPJOBPORTALincluder::getObjectClass('user')->isemployer()){
                        $wpjobportal_extra_actions_html .= apply_filters('wpjobportal_addons_sendMessage_resume',false) ;
                    }
                }

                if (wpjobportal::$wpjobportal_data['resumecontactdetail'] == true || $adminLogin) {
                    $wpjobportal_class = '';
                    //PDF + EXCEL HOOK
                        $wpjobportal_extra_actions_html  .= apply_filters('wpjobportal_addons_resume_views_action_for_pdf',false,wpjobportal::$_data[0]['personal_section']->id);
                        $wpjobportal_extra_actions_html  .= apply_filters('wpjobportal_addons_resume_views_action_export',false,wpjobportal::$_data[0]['personal_section']->id);
                   }
                   //PRINT HOOK
                   $wpjobportal_extra_actions_html .= apply_filters('wpjobportal_addons_resume_views_action_for_print',false,wpjobportal::$_data[0]['personal_section']->id);
                if(!empty(wpjobportal::$_data[0]['file_section']) && (wpjobportal::$wpjobportal_data['resumecontactdetail'] == true || $adminLogin)){
                    //Downloadable File Addons HOOK
                    $wpjobportal_extra_actions_html .= apply_filters('wpjobportal_addons_resume_action_ResumeFile',false,wpjobportal::$_data[0]['personal_section']);
                }
                $wpjobportal_extra_actions_html .= apply_filters('wpjobportal_addons_showresume_contact_detail',false,wpjobportal::$_data[0]['personal_section']->id,wpjobportal::$wpjobportal_data['resumecontactdetail'],$adminLogin);

            } elseif ($wpjobportal_layout == 'printresume') {
                $wpjobportal_extra_actions_html .= '<a href="#" onClick="window.print();" class="grayBtn">' . esc_html(__('Print', 'wp-job-portal')) . '</a>';
            }
        if($wpjobportal_extra_actions_html != ''){
            $wpjobportal_html .= '<div class="wjportal-resume-adv-act-wrp">';
            $wpjobportal_html .= $wpjobportal_extra_actions_html;
            $wpjobportal_html .='</div>';
        }

        $wpjobportal_html .= '</div>'; // closing div of resume-top-section
        return $wpjobportal_html;
    }

    function getFieldTitleByField($wpjobportal_field){
        return wpjobportal::wpjobportal_getVariableValue(wpjobportal::$wpjobportal_data['fieldtitles'][$wpjobportal_field]);
    }

    function getRowForView($wpjobportal_text, $wpjobportal_value, &$wpjobportal_i,$wpjobportal_themecall=null,$full=0) {
        $wpjobportal_html = '';
        if(null != $wpjobportal_themecall){
            if(1!=$full){
                if ($wpjobportal_i == 0 || $wpjobportal_i % 2 == 0) {
                    $wpjobportal_html .= '<div class="wjportal-resume-sec-row '.esc_attr($this->class_prefix).'-resumedetail-sec-value">';
                }
            }
        }else{
            if ($wpjobportal_i == 0 || $wpjobportal_i % 2 == 0) {
                $wpjobportal_html .= '<div class="wjportal-resume-sec-row '.esc_attr($this->class_prefix).'-resumedetail-sec-value">';
            }
        }
        if(null != $wpjobportal_themecall){
            if(0==$full){
                $wpjobportal_html .= '<div class="'.esc_attr($this->class_prefix).'-resumedetail-sec-value-left '.esc_attr($this->class_prefix).'-bigfont">
                            <span class="'.esc_attr($this->class_prefix).'-resumedetail-title">' . $wpjobportal_text . ':</span>
                            <span class="'.esc_attr($this->class_prefix).'-resumedetail-value">' . wpjobportal::wpjobportal_getVariableValue($wpjobportal_value) . '</span>
                        </div>';
            }else if(1==$full){
                $wpjobportal_html .='<div class="'.esc_attr($this->class_prefix).'-resumedetail-sec-value '.esc_attr($this->class_prefix).'-bigfont">
                            <span class="'.esc_attr($this->class_prefix).'-resumedetail-sec-title">' . $wpjobportal_text . ':</span>
                            <span class="'.esc_attr($this->class_prefix).'-resumedetail-sec-value">' . wpjobportal::wpjobportal_getVariableValue($wpjobportal_value) . '</span>
                        </div>';
            }
        }else{
            $wpjobportal_html .= '<div class="wjportal-custom-field wjportal-resume-sec-data">
                        <div class="wjportal-custom-field-tit wjportal-resume-sec-data-title">' . $wpjobportal_text . ':</div>
                        <div class="wjportal-custom-field-val wjportal-resume-sec-data-value">' . wpjobportal::wpjobportal_getVariableValue($wpjobportal_value) . '</div>
                    </div>';
        }
        $wpjobportal_i++;
        if(null != $wpjobportal_themecall){
            if(1!=$full){
                if ($wpjobportal_i % 2 == 0) {
                    $wpjobportal_html .= '</div>';
                }
            }
        }else{
            if ($wpjobportal_i % 2 == 0) {
                $wpjobportal_html .= '</div>';
            }
        }
        return $wpjobportal_html;
    }

    function wpjobportal_getRowForForm($wpjobportal_text, $wpjobportal_value) {
        $wpjobportal_html = '<div class="wpjp-resume-date-wrp form">
                    <div class="row-title">' . $wpjobportal_text . ':</div>
                    <div class="row-value">' . $wpjobportal_value . '</div>
                </div>';
        return $wpjobportal_html;
    }
    function getHeadingRowForView($wpjobportal_value,$wpjobportal_themecall=null) {
        if(null != $wpjobportal_themecall){
            $wpjobportal_html='<div class="'.esc_attr($this->class_prefix).'-resumedetail-sec-title1">
                <h6 class="'.esc_attr($this->class_prefix).'-resumedetail-sec-title1-txt">'.$wpjobportal_value.'</h6>
            </div>';
        }else{
            $wpjobportal_html = '<div class="wjportal-resume-inner-sec-heading">' . $wpjobportal_value . '</div>';
        }
        return $wpjobportal_html;
    }
    function makeanchorfortags($wpjobportal_tags,$wpjobportal_themecall=null) {
        if (empty($wpjobportal_tags)) {
            if(null != $wpjobportal_themecall) return;
            $anchor = '<div id="jsresume-tags-wrapper"></div>';
            return $anchor;
        }
        $wpjobportal_array = wpjobportalphplib::wpJP_explode(',', $wpjobportal_tags);
        $anchor="";
        if(null != $wpjobportal_themecall){
            for ($wpjobportal_i = 0; $wpjobportal_i < count($wpjobportal_array); $wpjobportal_i++) {
                $with_spaces = wpjobportal::tagfillin($wpjobportal_array[$wpjobportal_i]);
                $anchor .= '<a title="tags" class="'.esc_attr($this->class_prefix).'-tag" href="' . wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'resumes', 'tags'=>$with_spaces)) . '"><i class="fas fa-tags tag" aria-hidden="true"></i>' . wpjobportal::wpjobportal_getVariableValue($wpjobportal_array[$wpjobportal_i]) . '</a>';
            }
        }else{
            $anchor .= '<div id="jsresume-tags-wrapper">';
            $anchor .= '<span class="jsresume-tags-title">' . esc_html(__('Tags', 'wp-job-portal')) . '</span>';
            $anchor .= '<div class="tags-wrapper-border">';
            for ($wpjobportal_i = 0; $wpjobportal_i < count($wpjobportal_array); $wpjobportal_i++) {
                $with_spaces = wpjobportal::tagfillin($wpjobportal_array[$wpjobportal_i]);
                $anchor .= '<a class="wpjobportal_tags_a" href="' . wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'resumes', 'tags'=>$with_spaces)) . '">' . wpjobportal::wpjobportal_getVariableValue($wpjobportal_array[$wpjobportal_i]) . '</a>';
            }
            $anchor .= '</div>';
            $anchor .= '</div>';
        }
        return $anchor;
    }

}

?>
