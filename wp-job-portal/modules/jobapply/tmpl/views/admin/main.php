<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
* @param Object--refrence
*/
$wpjobportal_listing_fields = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldOrderingDataForListing(3);
?>
<?php
	switch ($wpjobportal_layout) {
		case 'logo':
			$wpjobportal_photo = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('jobseeker');
			$wpjobportal_padding = ' style="padding:15px;" ';
			if (isset($wpjobportal_data->photo) && $wpjobportal_data->photo != '') {
				$wpjobportal_data_directory = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('data_directory');
				$wpjobportal_wpdir = wp_upload_dir();
				$wpjobportal_photo = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/jobseeker/resume_' . $wpjobportal_data->resumeid . '/photo/' . $wpjobportal_data->photo;
				$wpjobportal_padding = "";
			}
			?>
			<div class="wpjobportal-resume-logo">
                <?php if(isset($wpjobportal_listing_fields['photo'])){ ?>
                    <img src="<?php echo esc_url($wpjobportal_photo); ?>" alt="<?php echo esc_attr(__('logo','wp-job-portal')); ?>" />
                <?php } ?>
                <div class="wpjobportal-resume-crt-date">
                    <?php echo esc_html(date_i18n(wpjobportal::$_configuration['date_format'], strtotime($wpjobportal_data->apply_date))); ?>
                </div>
            </div>
            <?php
		break;
    	case 'detail':
            if(isset($wpjobportal_data->socialprofile)){
                $wpjobportal_socialprofile = json_decode($wpjobportal_data->socialprofile);
            }
            if(isset($wpjobportal_socialprofile)){
                $wpjobportal_data->first_name = isset($wpjobportal_data->first_name) ? $wpjobportal_data->first_name : $wpjobportal_socialprofile->first_name;
                $wpjobportal_data->last_name = isset($wpjobportal_data->last_name) ? $wpjobportal_data->last_name : $wpjobportal_socialprofile->last_name;
                $wpjobportal_data->applicationtitle = isset($wpjobportal_data->applicationtitle) ? $wpjobportal_data->applicationtitle : $wpjobportal_socialprofile->email;
            }
    		?>
			<div class="wpjobportal-resume-cnt-wrp">
                <div class="wpjobportal-resume-middle-wrp">
                    <div class="wpjobportal-resume-data">
                        <?php if( isset($wpjobportal_listing_fields['jobtype']) ){ ?>
                            <span class="wpjobportal-resume-job-type" style="background: <?php echo esc_attr($wpjobportal_data->jobtypecolor); ?>;">
                                <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_data->jobtypetitle)); ?>
                            </span>
                        <?php }?>
                    </div>    
                    <div class="wpjobportal-resume-data">
                        <span class="wpjobportal-resume-name">
                            <?php echo esc_html($wpjobportal_data->first_name) . " " . esc_html($wpjobportal_data->last_name) ?>
                        </span>
                    </div>
                    <?php if($wpjobportal_data->quick_apply != 1){ ?>
                        <div class="wpjobportal-resume-data">
                            <span class="wpjobportal-resume-title">
                                <?php echo esc_html($wpjobportal_data->applicationtitle); ?>
                            </span>
                        </div>

                        <div class="wpjobportal-resume-data">
                            <?php if( isset($wpjobportal_listing_fields['salaryfixed']) ){ ?>
                                <div class="wpjobportal-resume-data-text">
                                    <span class="wpjobportal-resume-data-title">
                                        <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_listing_fields['salaryfixed'])).': '; ?>
                                    </span>
                                    <span class="wpjobportal-resume-data-value">
                                        <?php echo esc_html($wpjobportal_data->salary); ?>
                                    </span>
                                </div>
                            <?php } ?>
                            <?php if(in_array('advanceresumebuilder', wpjobportal::$_active_addons)){ ?>
                                <div class="wpjobportal-resume-data-text">
                                    <span class="wpjobportal-resume-data-title">
                                        <?php echo esc_html(__('Total Experience', 'wp-job-portal')) . ': '; ?>
                                    </span>
                                    <span class="wpjobportal-resume-data-value">
                                        <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue(wpjobportal::$_common->getTotalExp($wpjobportal_data->resumeid))); ?>
                                    </span>
                                </div>
                                <?php if(isset($wpjobportal_listing_fields['address_city'])){ ?>
                                    <div class="wpjobportal-resume-data-text">
                                        <span class="wpjobportal-resume-data-title">
                                            <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_listing_fields['address_city'])) . ': '; ?>
                                        </span>
                                        <span class="wpjobportal-resume-data-value">
                                            <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_data->location)); ?>
                                        </span>
                                    </div>
                                <?php }?>
                            <?php }?>
                            <?php if(isset($wpjobportal_listing_fields['job_category'])) { ?>
                                <div class="wpjobportal-resume-data-text">
                                    <span class="wpjobportal-resume-data-title">
                                        <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_listing_fields['job_category'])) . ': '; ?>
                                    </span>
                                    <span class="wpjobportal-resume-data-value">
                                        <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_data->resume_category)); ?>
                                    </span>
                                </div>
                            <?php }?>
                            <?php if(isset($wpjobportal_listing_fields['jobtype'])) {?>
                                <div class="wpjobportal-resume-data-text">
                                    <span class="wpjobportal-resume-data-title">
                                        <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_listing_fields['jobtype'])) . ': '; ?>
                                    </span>
                                    <span class="wpjobportal-resume-data-value">
                                        <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_data->jobtypetitle)); ?>
                                    </span>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                    <?php if(isset($wpjobportal_data->apply_message) && $wpjobportal_data->apply_message !=''){
                        $wpjobportal_apply_message_label = wpjobportal::$_wpjpfieldordering->getFieldTitleByFieldAndFieldfor('message',5); ?>
                        <div class="wpjobportal-resume-data">
                            <div class="wpjobportal-resume-data-text">
                                <span class="wpjobportal-resume-data-title">
                                    <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_apply_message_label)) . ': '; ?>
                                </span>
                                <span class="wpjobportal-resume-data-value">
                                    <?php echo esc_html($wpjobportal_data->apply_message); ?>
                                </span>
                            </div>
                        </div>
                    <?php }?>
                    <?php do_action('wpjobportal_addon_search_applied_resume'); ?>
                </div>
                <div class="wpjobportal-resume-right-wrp">
                    <?php do_action('wpjobportal_addons_rating_resume_applied',$wpjobportal_data); ?>
                    <?php  do_action('wpjobportal_addons_credit_applied_resume_ratting_admin',$wpjobportal_data); ?>
                    <?php
                        if(in_array('coverletter', wpjobportal::$_active_addons)){
                                 $wpjobportal_cover_letter_title = '';
                                 $wpjobportal_cover_letter_desc = '';
                                 if( isset($wpjobportal_data->coverletterdata) && !empty($wpjobportal_data->coverletterdata) ){

                                     $wpjobportal_cover_letter_title = $wpjobportal_data->coverletterdata->title;
                                     $wpjobportal_cover_letter_desc = $wpjobportal_data->coverletterdata->description;
                                 }
                                if(isset($wpjobportal_data->coverletterid) && is_numeric($wpjobportal_data->coverletterid) && $wpjobportal_data->coverletterid > 0){
                                     echo '<div id="cover_letter_data_title_'.esc_attr($wpjobportal_data->coverletterid).'" style="display:none;" >'.esc_html($wpjobportal_cover_letter_title).'</div>';
                                     echo '<div id="cover_letter_data_desc_'.esc_attr($wpjobportal_data->coverletterid).'" style="display:none;" >'.wp_kses($wpjobportal_cover_letter_desc,WPJOBPORTAL_ALLOWED_TAGS).'</div>';

                                     echo '
                                     <a class="wpjobportal-viewcover-act-btn" href="#" onClick="showCoverLetterData('.esc_attr($wpjobportal_data->coverletterid).')" title='. esc_html(__('view coverletter', 'wp-job-portal')) .'>
                                         '. esc_html(__('View Cover Letter', 'wp-job-portal')) .'
                                     </a>';
                                }else{
                                    echo '
                                    <span class="wjportal-no-coverletter-btn">
                                        '. esc_html(__('No Cover Letter', 'wp-job-portal')) .'
                                    </span>';
                                }
                           }?>
                </div>
            </div>
            <div id="<?php echo esc_attr($wpjobportal_data->appid); ?>" ></div>
            <div id="comments" class="wpjobportal-applied-job-actions-popup <?php echo esc_attr($wpjobportal_data->appid); ?>" ></div>
            <?php
		break;
    }
?>
