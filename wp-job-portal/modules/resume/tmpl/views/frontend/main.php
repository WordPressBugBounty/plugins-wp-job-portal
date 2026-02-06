<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param WP JOB PORTAL
 * @param Main  
 */
$wpjobportal_listing_fields = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldOrderingDataForListing(3);
echo '<div class="wjportal-resume-cnt-wrp">
        <div class="wjportal-resume-middle-wrp">      ';
            if(isset($wpjobportal_myresume->socialprofile)){
                $wpjobportal_socialprofile = json_decode($wpjobportal_myresume->socialprofile);
            }
            if(isset($wpjobportal_socialprofile)){
                $wpjobportal_myresume->first_name = isset($wpjobportal_myresume->first_name) ? $wpjobportal_myresume->first_name : $wpjobportal_socialprofile->first_name;
                $wpjobportal_myresume->last_name = isset($wpjobportal_myresume->last_name) ? $wpjobportal_myresume->last_name : $wpjobportal_socialprofile->last_name;
                $wpjobportal_myresume->applicationtitle = isset($wpjobportal_myresume->applicationtitle) ? $wpjobportal_myresume->applicationtitle : $wpjobportal_socialprofile->email;
            }
            echo '<div class="wjportal-resume-data">
            <a href='.esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'viewresume', 'wpjobportalid'=>$wpjobportal_myresume->id, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()))).'>
                <span class="wjportal-resume-name">'.esc_html($wpjobportal_myresume->first_name) .' ' . esc_html($wpjobportal_myresume->last_name).' '.'</span></a>';
                $wpjobportal_featuredflag = true;
                $wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
                $wpjobportal_curdate = date_i18n('Y-m-d');
                do_action('wpjobportal_addons_feature_resume_lable',$wpjobportal_myresume);
            echo '</div>  ';
            $wpjobportal_hide_extra_fields = 0;
            if(isset($wpjobportal_myresume->quick_apply) && $wpjobportal_myresume->quick_apply == 1){
                $wpjobportal_hide_extra_fields = 1;
            }
            if($wpjobportal_hide_extra_fields == 0){
                echo '
                <div class="wjportal-resume-data">
                    <span class="wjportal-resume-title">'. esc_html($wpjobportal_myresume->applicationtitle) .'</span>
                </div>
                <div class="wjportal-resume-data">';
                    if(empty(wpjobportal::$_data['shortcode_option_hide_resume_location'])){
                        if(in_array('advanceresumebuilder', wpjobportal::$_active_addons)){
                            if(isset($wpjobportal_listing_fields['address_city'])){
                                echo '<div class="wjportal-resume-data-text wjportal-jobs-data-icon-class-lcoation">
                                    <span class="wjportal-resume-data-title">'. esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_listing_fields['address_city'])) . ': '.'</span>
                                    <span class="wjportal-resume-data-value">'. esc_html($wpjobportal_myresume->location) .'</span>
                                </div>';
                            }
                        }
                    }
                    if(empty(wpjobportal::$_data['shortcode_option_hide_resume_salary'])){
                        if(isset($wpjobportal_listing_fields['salaryfixed']) && (isset($wpjobportal_module) && ($wpjobportal_module== "resume" || $wpjobportal_module == "myresumes"))){
                            echo '<div class="wjportal-resume-data-text wjportal-jobs-data-icon-class-salary">
                                    <span class="wjportal-resume-data-title">';
                                    echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_listing_fields['salaryfixed'])) . ': ';
                            echo '</span>
                                <span class="wjportal-resume-data-value">'. esc_html($wpjobportal_myresume->salary).'</span>
                            </div>';
                        }
                    }
                    if(in_array('advanceresumebuilder', wpjobportal::$_active_addons)){
                        echo '<div class="wjportal-resume-data-text wjportal-jobs-data-icon-class-exprience">
                            <span class="wjportal-resume-data-title">';
                                echo esc_html(__('Experience', 'wp-job-portal')) . ': ';
                            echo '</span>
                            <span class="wjportal-resume-data-value">'.
                                esc_html(wpjobportal::wpjobportal_getVariableValue(wpjobportal::$_common->getTotalExp($wpjobportal_myresume->resumeid))) .'
                            </span>';
                        echo '</div>';
                    }
                    if(isset($wpjobportal_listing_fields['job_category'])){
                        echo '<div class="wjportal-resume-data-text wjportal-jobs-data-icon-class-category">
                            <span class="wjportal-resume-data-title">';
                                echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_listing_fields['job_category'])) . ': ';
                            echo '</span>
                            <span class="wjportal-resume-data-value">'.
                                esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_myresume->cat_title)) .'
                            </span>';
                        echo '</div>';
                    }
                    if($wpjobportal_module == "jobappliedresume" && !empty($wpjobportal_myresume->comments)){
                        echo '<div class="wjportal-resume-data-text wjportal-jobs-data-icon-class-notes">
                                <span class="wjportal-resume-data-title">';
                                    echo esc_html(__('Notes', 'wp-job-portal')) . ': ';
                                echo '</span>
                                <span class="wjportal-resume-data-value">'. esc_html($wpjobportal_myresume->comments)  .'
                                </span>';
                        echo '</div>';
                    }

                    
                    
                    
                    
                    echo '</div>';



            }
                echo  '
        </div>      ';
        if(isset($wpjobportal_jobapply) && $wpjobportal_jobapply == "jobapplied"){
            do_action('wpjobportal_addons_credit_applied_resume_rating',$wpjobportal_myresume);
       }
       
    echo'<div class="wjportal-resume-right-wrp">';
                if(isset($wpjobportal_listing_fields['jobtype'])){
                    echo '
                        <div class="wjportal-resume-data">
                            <span class="wjportal-resume-job-type" style="background-color:'.esc_attr($wpjobportal_myresume->jobtypecolor).'">'.esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_myresume->jobtypetitle)) .'</span>
                        </div>';
                }
                if(isset($wpjobportal_module) && $wpjobportal_module == "jobappliedresume"){
                    echo '<div>';
                    do_action('wpjobportal_addons_credit_applied_resume_rating',$wpjobportal_myresume);
                    echo '</div>';

                    if(in_array('coverletter', wpjobportal::$_active_addons)){

                            $wpjobportal_cover_letter_title = '';
                            $wpjobportal_cover_letter_desc = '';
                            if( isset($wpjobportal_myresume->coverletterdata) && !empty($wpjobportal_myresume->coverletterdata) ){

                                $wpjobportal_cover_letter_title = $wpjobportal_myresume->coverletterdata->title;
                                $wpjobportal_cover_letter_desc = $wpjobportal_myresume->coverletterdata->description;
                            }
                        if(isset($wpjobportal_myresume->coverletterid) && is_numeric($wpjobportal_myresume->coverletterid) && $wpjobportal_myresume->coverletterid > 0){
                            echo '<div id="cover_letter_data_title_'.esc_attr($wpjobportal_myresume->coverletterid).'" style="display:none;" >'.wp_kses($wpjobportal_cover_letter_title, WPJOBPORTAL_ALLOWED_TAGS).'</div>';
                            echo '<div id="cover_letter_data_desc_'.esc_attr($wpjobportal_myresume->coverletterid).'" style="display:none;" >'.wp_kses($wpjobportal_cover_letter_desc, WPJOBPORTAL_ALLOWED_TAGS).'</div>';
                            echo '
                            <a class="wjportal-coverletter-act-btn" href="#" onClick="showCoverLetterData('.esc_attr($wpjobportal_myresume->coverletterid).')" title='. esc_html(__('view coverletter', 'wp-job-portal')) .'>
                                '. esc_html(__('View Cover Letter', 'wp-job-portal')) .'
                            </a>';
                        }else{
                            echo '
                            <span class="wjportal-no-coverletter-btn">
                                '. esc_html(__('No Cover Letter', 'wp-job-portal')) .'
                            </span>';
                        }
                    }
                }
            
      echo '</div> 
    ';
        echo '<div class="wjportal-resume-listing-bottom-full-wrap" >';
        if(isset($wpjobportal_listing_fields['skills'])){
            echo '<div class="wjportal-resume-listing-skills-data-text">
                      <span class="wjportal-resume-data-value">'.
                          esc_html( wp_trim_words( $wpjobportal_myresume->skills, 30, '...' ) )
                            .'
                      </span>';
            echo '</div>';
        }
        if(empty($wpjobportal_myresume->quick_apply)){
            echo '
            <div class="wjportal-custom-field-wrp">';
            $wpjobportal_customfields = wpjobportal::$_wpjpcustomfield->userFieldsData(3,1,1);/*apply_filters('wpjobportal_addons_get_custom_field',false,3,1,1)*/;
            foreach ($wpjobportal_customfields as $wpjobportal_field) {
              $wpjobportal_showCustom =  wpjobportal::$_wpjpcustomfield->showCustomFields($wpjobportal_field,9,$wpjobportal_myresume->params)/*apply_filters('wpjobportal_addons_show_customfields_params',false,$wpjobportal_field,9,$wpjobportal_myresume->params)*/;
              echo wp_kses($wpjobportal_showCustom, WPJOBPORTAL_ALLOWED_TAGS);
            }

            echo  '</div>';
        }
      echo '</div>';


      if(isset($wpjobportal_module) && $wpjobportal_module == "myresumes"){
          echo '<div class="wjportal-progress-bar-container">';
          echo '    <div class="wjportal-progress-bar-header">';
          echo '        <span class="wjportal-progress-bar-title">' . esc_html(__('Profile Status', 'wp-job-portal')) . '</span>';
          echo '        <span class="wjportal-progress-bar-percentage">' . esc_html($wpjobportal_percentage) . '%</span>';
          echo '    </div>';
          echo '    <div class="wjportal-progress-bar-wrapper">';
          echo '        <div class="wjportal-progress-bar-fill" style="width: ' . esc_attr($wpjobportal_percentage) . '%;"></div>';
          echo '    </div>';
          echo '</div>';
      }

echo '</div>';
