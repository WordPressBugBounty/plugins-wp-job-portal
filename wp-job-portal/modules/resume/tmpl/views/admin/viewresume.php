<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param job      job object - optional
*/
?>
<?php
$wpjobportal_extra_featured_class = '';
if( !empty(wpjobportal::$_data[0]) && !empty(wpjobportal::$_data[0]['personal_section']) ){
    $wpjobportal_resume_object = wpjobportal::$_data[0]['personal_section'];
    $wpjobportal_dateformat    = wpjobportal::$_configuration['date_format'];
    $wpjobportal_curdate       = date_i18n('Y-m-d');
    $wpjobportal_featuredexpiry = date_i18n('Y-m-d', strtotime($wpjobportal_resume_object->endfeatureddate));

    if ($wpjobportal_resume_object->isfeaturedresume == 1 && $wpjobportal_featuredexpiry >= $wpjobportal_curdate) {
        $wpjobportal_extra_featured_class = 'wjportal-view-page-featured-flag';
    }
}
 if (isset(wpjobportal::$_data['socialprofile']) && wpjobportal::$_data['socialprofile'] == true && in_array('sociallogin', wpjobportal::$_active_addons)) { // social profile
            $wpjobportal_profileid = wpjobportal::$_data['socialprofileid'];
            WPJOBPORTALincluder::getObjectClass('SocialLogin')->showprofilebyprofileid($wpjobportal_profileid);
        } else {
            $wpjobportal_html = '<div class="wjportal-resume-detail-wrapper '.esc_attr($wpjobportal_extra_featured_class).'">';
            $wpjobportal_isowner = (WPJOBPORTALincluder::getObjectClass('user')->uid() == wpjobportal::$_data[0]['personal_section']->uid) ? 1 : 0;
            $wpjobportal_html .= '<div class="wjportal-resume-detail-left-wrapper">';
            $wpjobportal_html .= $wpjobportal_resumeviewlayout->getResumeLeftSection(0, 1);
            $wpjobportal_html .= '</div>';
            $wpjobportal_html .= '<div class="wjportal-resume-right-wrapper">';
            $wpjobportal_personal_section_title = wpjobportal::wpjobportal_getVariableValue(wpjobportal::$wpjobportal_data['fieldtitles']['section_personal']);
            $wpjobportal_html .= '<div class="wjportal-resume-section-records-wrap" >';
            $wpjobportal_html .= '<div class="wjportal-resume-section-title">'. esc_html($wpjobportal_personal_section_title) . '</div>';
            $wpjobportal_html .= $wpjobportal_resumeviewlayout->getPersonalSection(0, 1);
            $wpjobportal_html .= '</div>';
            $wpjobportal_show_section_that_have_value = wpjobportal::$_config->getConfigValue('show_only_section_that_have_value');

            $wpjobportal_resume_section_ordering = WPJOBPORTALincluder::getJSModel('fieldordering')->getResumeSections();
            foreach ($wpjobportal_resume_section_ordering as $wpjobportal_resume_section) {
                // to show resume section according to ordering in field ordering
                // also changed the fixed titles to section titles from field ordering.
                switch ($wpjobportal_resume_section->field) {
                    case 'section_education':
                        $wpjobportal_showflag = 1;
                        if ($wpjobportal_show_section_that_have_value == 1 && empty(wpjobportal::$_data[0]['institute_section'][0])){
                            $wpjobportal_showflag = 0;
                        }
                        if (isset(wpjobportal::$_data[2][3]['section_education']) && $wpjobportal_showflag == 1) {
                            // Handling Advance Resume Builder's Addons
                            $wpjobportal_education_section_title = wpjobportal::wpjobportal_getVariableValue($wpjobportal_resume_section->fieldtitle);
                            $wpjobportal_html .= apply_filters('wpjobportal_addons_view_resume_by_section',false,'getEducationSection',$wpjobportal_education_section_title);
                        }
                    break;
                    case 'section_employer':
                        $wpjobportal_showflag = 1;
                        if ($wpjobportal_show_section_that_have_value == 1 && empty(wpjobportal::$_data[0]['employer_section'][0])){
                            $wpjobportal_showflag = 0;
                        }
                        if (isset(wpjobportal::$_data[2][4]['section_employer']) && $wpjobportal_showflag == 1) {
                            // Employer Section Resume
                            $wpjobportal_html .= $wpjobportal_resumeviewlayout->getEmployerSection(0, 0, 1);
                        }

                    break;
                    case 'section_address':
                        $wpjobportal_showflag = 1;
                        if ($wpjobportal_show_section_that_have_value == 1 && empty(wpjobportal::$_data[0]['address_section'][0])){
                            $wpjobportal_showflag = 0;
                        }
                        if (isset(wpjobportal::$_data[2][2]['section_address']) && $wpjobportal_showflag == 1) {
                            // Address Section Resume
                            $wpjobportal_html .= $wpjobportal_resumeviewlayout->getAddressesSection(0, 0, 1);
                        }
                    break;
                    case 'section_skills':
                        $wpjobportal_showflag = 1;
                        if ($wpjobportal_show_section_that_have_value == 1 && empty(wpjobportal::$_data[0]['personal_section']->skills)){
                            $wpjobportal_showflag = 0;
                        }
                        if (isset(wpjobportal::$_data[2][5]['section_skills']) && $wpjobportal_showflag == 1) {
                            // Handling Advance Resume Builder's Addons
                            $wpjobportal_skills_section_title = wpjobportal::wpjobportal_getVariableValue($wpjobportal_resume_section->fieldtitle);
                            $wpjobportal_html .= apply_filters('wpjobportal_addons_view_resume_by_section',false,'getSkillSection',$wpjobportal_skills_section_title);
                        }
                    break;
                    case 'section_language':
                        $wpjobportal_showflag = 1;
                        if ($wpjobportal_show_section_that_have_value == 1 && empty(wpjobportal::$_data[0]['language_section'][0])){
                            $wpjobportal_showflag = 0;
                        }
                        if (isset(wpjobportal::$_data[2][8]['section_language']) && $wpjobportal_showflag == 1) {
                            $wpjobportal_language_section_title = wpjobportal::wpjobportal_getVariableValue(wpjobportal::$wpjobportal_data['fieldtitles']['section_language']);
                            $wpjobportal_html .= apply_filters('wpjobportal_addons_view_resume_by_section',false,'getLanguageSection',$wpjobportal_language_section_title);
                        }
                    break;
                    default:
                        $wpjobportal_showflag = 0;
                        if (isset(wpjobportal::$_data[0]['personal_section']) && wpjobportal::$_data[0]['personal_section']->quick_apply == 1 ){// to handle empty section to some extent
                            break;
                        }
                        if (isset(wpjobportal::$_data[0]['personal_section']) && wpjobportal::$_data[0]['personal_section']->params !='[]' ){// to handle empty section to some extent
                            $wpjobportal_showflag = 1;
                        }
                        if ($wpjobportal_showflag == 1) {
                            // Handling Advance Resume Builder's Addons
                            if($wpjobportal_resume_section->section > 8){ // only execute this code for custom sections
                                $wpjobportal_resume_section->fieldtitle = wpjobportal::wpjobportal_getVariableValue($wpjobportal_resume_section->fieldtitle);
                                $wpjobportal_html .= apply_filters('wpjobportal_addons_view_resume_by_section_custom',false,'getCustomSection',$wpjobportal_resume_section);
                            }
                        }
                    break;

                }
            }
            $wpjobportal_html .= '</div>';
            echo wp_kses($wpjobportal_html,WPJOBPORTAL_ALLOWED_TAGS);
        }
