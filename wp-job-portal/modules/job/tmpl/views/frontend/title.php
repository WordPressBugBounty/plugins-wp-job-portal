<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param job      job object - optional
 */
$wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
$wpjobportal_html = '';
switch ($wpjobportal_layout) {
    case 'job':
            if(in_array('multicompany', wpjobportal::$_active_addons)){
                $wpjobportal_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$wpjobportal_job->companyid));
            }else{
                $wpjobportal_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$wpjobportal_job->companyid));
            } ?>
            <div class="wjportal-jobs-middle-wrp">
                <div class="wjportal-jobs-data">
                </div>
                <div class="wjportal-jobs-data">
                    <span class="wjportal-job-title">
                        <a href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'viewjob', 'wpjobportalid'=>$wpjobportal_job->jobaliasid))); ?>">
                            <?php echo esc_html($wpjobportal_job->title); ?>
                        </a>
                        <?php  do_action('wpjobportal_credit_addons_feature_job_popup_for_emp',$wpjobportal_job); ?>
                    </span>
                    <?php
                        $wpjobportal_featuredexpiry = date_i18n('Y-m-d', strtotime($wpjobportal_job->endfeatureddate));
                        $wpjobportal_startdate = date_i18n('Y-m-d',strtotime($wpjobportal_job->startpublishing));
                        $wpjobportal_enddate = date_i18n('Y-m-d',strtotime($wpjobportal_job->stoppublishing));
                        $wpjobportal_curdate = date_i18n('Y-m-d');
                        if($wpjobportal_startdate > $wpjobportal_curdate){
                            $wpjobportal_publishstatus = esc_html(__('Not publish','wp-job-portal'));
                            $wpjobportal_publishstyle = 'background:#fea702;';
                        }elseif($wpjobportal_startdate <= $wpjobportal_curdate && $wpjobportal_enddate >= $wpjobportal_curdate){
                            $wpjobportal_publishstatus = esc_html(__('Publish','wp-job-portal'));
                            $wpjobportal_publishstyle = 'background:#00a859;';
                        }else{
                            $wpjobportal_publishstatus = esc_html(__('Expired','wp-job-portal'));
                            $wpjobportal_publishstyle = 'background:#ed3237;';
                        }
                    if($wpjobportal_job->status == 1 && WPJOBPORTALrequest::getVar('wpjobportallt') == "myjobs"){ ?>
                        <span class="wjportal-item-status" style="<?php echo esc_attr($wpjobportal_publishstyle); ?>"><?php echo esc_html($wpjobportal_publishstatus); ?>
                        </span><?php
                    }

                    if(empty(wpjobportal::$_data['shortcode_option_hide_company_name'])){ // if this value is set means hide this company name is set in shortcode
                        if (wpjobportal::$_config->getConfigValue('comp_name')) { ?>
                            <a class="wjportal-companyname" href="<?php echo esc_url($wpjobportal_url) ; ?>"><?php echo esc_html($wpjobportal_job->companyname); ?></a><?php ?>
                        <?php
                        }
                    }
                    ?>
                </div>
                <div class="wjportal-jobs-data">
                    <?php  $wpjobportal_print = WPJOBPORTALincluder::getJSModel('job')->checkLinks('jobcategory');
                    if(isset($wpjobportal_print[0]) && $wpjobportal_print[0] == 1){// field publihsed check
                        if(isset($wpjobportal_job) && !empty($wpjobportal_job->cat_title)){ ?>
                            <span class="wjportal-jobs-data-text wjportal-jobs-data-icon-class-category">
                                <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_job->cat_title)); ?>
                            </span>
                        <?php
                        }
                    } ?>
                    <?php  $wpjobportal_print = WPJOBPORTALincluder::getJSModel('job')->checkLinks('city'); ?>
                    <?php
                    if(isset($wpjobportal_print[0]) && $wpjobportal_print[0] == 1){// field publihsed check
                        if(isset($wpjobportal_job) && !empty($wpjobportal_job->location)){ ?>
                        <span class="wjportal-jobs-data-text wjportal-jobs-data-icon-class-location">
                            <?php echo esc_html($wpjobportal_job->location); ?>
                        </span>
                    <?php
                        }
                    }
                    ?>
                </div>
            </div>
            <div class="wjportal-jobs-right-wrp">
                <div class="wjportal-jobs-info">
                    <?php $wpjobportal_print = WPJOBPORTALincluder::getJSModel('job')->checkLinks('jobtype'); ?>
                    <?php if (isset($wpjobportal_print[0]) && $wpjobportal_print[0] == 1) { ?>
                        <span class="wjportal-job-type" style="background:<?php echo esc_attr($wpjobportal_job->jobtypecolor); ?>">
                            <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_job->jobtypetitle)); ?>
                        </span>
                    <?php } ?>
                </div>
                <?php

                        $wpjobportal_print = WPJOBPORTALincluder::getJSModel('job')->checkLinks('jobsalaryrange');
                        if (isset($wpjobportal_print[0]) && $wpjobportal_print[0] == 1) {  ?>
                            <div class="wjportal-jobs-info">
                                <div class="wjportal-jobs-salary">
                                    <?php echo esc_html(wpjobportal::$_common->getSalaryRangeView($wpjobportal_job->salarytype, $wpjobportal_job->salarymin, $wpjobportal_job->salarymax,$wpjobportal_job->currency)); ?>
                                    <?php if($wpjobportal_job->salarytype==3 || $wpjobportal_job->salarytype==2) { ?>
                                        <span class="wjportal-salary-type"> <?php echo ' / ' .esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_job->srangetypetitle)) ?></span>
                                    <?php }?>
                                </div>
                            </div>
                        <?php
                        }
                        ?>
                        <div class="wjportal-jobs-info">
                            <?php
                            if($wpjobportal_control != 'newestjobs'){
                                $wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
                                echo esc_html(human_time_diff(strtotime($wpjobportal_job->created),strtotime(date_i18n("Y-m-d H:i:s"))).' '.esc_html(__("Ago",'wp-job-portal')));
                            }
                            ?>
                        </div>
                        <div class="wjportal-jobs-status">
                            <?php
                                $wpjobportal_color = ($wpjobportal_job->status == 1) ? "green" : "red";
                                if ($wpjobportal_job->status == 1) {
                                    $wpjobportal_statusCheck = esc_html(__('Approved', 'wp-job-portal'));
                                } elseif ($wpjobportal_job->status == 0) {
                                    $wpjobportal_statusCheck = esc_html(__('Waiting for approval', 'wp-job-portal'));
                                } else {
                                    $wpjobportal_statusCheck = esc_html(__('Rejected', 'wp-job-portal'));
                                }
                            ?>
                             <span class="wjportal-jobs-status-text <?php //echo esc_attr($wpjobportal_color); ?>"><?php //echo esc_html($wpjobportal_statusCheck); ?></span>
                        </div>

            </div>

            <div class="wjportal-jobs-bottom-full-wrp">
                <div class="wjportal-jobs-data">
                    <?php
                    $wpjobportal_print = WPJOBPORTALincluder::getJSModel('job')->checkLinks('description');
                    if (isset($wpjobportal_print[0]) && $wpjobportal_print[0] == 1) { ?>
                        <div class="wjportal-job-listing-description-wrap">
                            <span class="wjportal-job-listing-description-val">
                                <?php echo esc_html( wp_trim_words( $wpjobportal_job->description, 30, '...' ) ); ?>
                            </span>
                        </div><?php
                    } ?>
                    <!-- custom fields -->
                    <div class="wjportal-custom-field-wrp">
                        <?php
                            // custom fiedls
                                $wpjobportal_customfields = wpjobportal::$_wpjpcustomfield->userFieldsData(2,1);
                                foreach ($wpjobportal_customfields as $wpjobportal_field) {
                                   $wpjobportal_showCustom = wpjobportal::$_wpjpcustomfield->showCustomFields($wpjobportal_field,7,$wpjobportal_job->params);
                                   echo wp_kses($wpjobportal_showCustom, WPJOBPORTAL_ALLOWED_TAGS);
                                }
                          /*  }*/
                        ?>
                    </div>
                    <?php
                        if(isset($wpjobportal_control) && $wpjobportal_control == "shortlistjob"){
                            do_action('wpjobportal_addons_shortlist_comments',$wpjobportal_job);
                        }
                    ?>
                </div>
            </div>

            <?php
        break;
        case 'detailbody':
            echo' <div class="wjportal-job-sec-title">'.  esc_html(__("Job Info",'wp-job-portal')) .'</div>';
                $wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
                $wpjobportal_description_field_label = '';
                $wpjobportal_map_field_enalbed = 0;
                $wpjobportal_tags_field_enalbed = 0;
                wpjobportal::$wpjobportal_data['fields_titles_array'] = array();// to print correct field titles from field ordering for hook/filter fields
                $wpjobportal_html ='<div class="wpjp-jobtype-info">';
                            foreach ($wpjobportal_jobfields AS $wpjobportal_key => $wpjobportal_fields) {
                                switch ($wpjobportal_fields->field) {
                                    case 'department':
                                        if(in_array('departments', wpjobportal::$_active_addons)){
                                            echo wp_kses(wpjobportal_getDataRow(wpjobportal::wpjobportal_getVariableValue($wpjobportal_fields->fieldtitle), $wpjobportal_job->departmentname), WPJOBPORTAL_ALLOWED_TAGS);
                                        }
                                    break;
                                    case 'jobstatus':
                                        echo wp_kses(wpjobportal_getDataRow(wpjobportal::wpjobportal_getVariableValue($wpjobportal_fields->fieldtitle), wpjobportal::wpjobportal_getVariableValue($wpjobportal_job->jobstatustitle)), WPJOBPORTAL_ALLOWED_TAGS);
                                    break;
                                    case 'noofjobs':
                                        echo wp_kses(wpjobportal_getDataRow(wpjobportal::wpjobportal_getVariableValue($wpjobportal_fields->fieldtitle), $wpjobportal_job->noofjobs), WPJOBPORTAL_ALLOWED_TAGS);
                                    break;
                                    case 'duration':
                                        echo wp_kses(wpjobportal_getDataRow(wpjobportal::wpjobportal_getVariableValue($wpjobportal_fields->fieldtitle), $wpjobportal_job->duration), WPJOBPORTAL_ALLOWED_TAGS);
                                    break;
                                    case 'careerlevel':
                                        echo wp_kses(wpjobportal_getDataRow(wpjobportal::wpjobportal_getVariableValue($wpjobportal_fields->fieldtitle), wpjobportal::wpjobportal_getVariableValue($wpjobportal_job->careerleveltitle)), WPJOBPORTAL_ALLOWED_TAGS);
                                    break;
                                    case 'experience':
                                        $wpjobportal_experience = !empty($wpjobportal_job->experience) ? $wpjobportal_job->experience.' '.esc_html(__("Years",'wp-job-portal')) : '';
                                        echo wp_kses(wpjobportal_getDataRow(wpjobportal::wpjobportal_getVariableValue($wpjobportal_fields->fieldtitle), $wpjobportal_experience), WPJOBPORTAL_ALLOWED_TAGS);
                                    break;
                                    case 'heighesteducation':
                                        echo wp_kses(wpjobportal_getDataRow(wpjobportal::wpjobportal_getVariableValue($wpjobportal_fields->fieldtitle), $wpjobportal_job->educationtitle), WPJOBPORTAL_ALLOWED_TAGS);
                                        echo wp_kses(wpjobportal_getDataRow(esc_html(__('Degree Title', 'wp-job-portal')), $wpjobportal_job->degreetitle), WPJOBPORTAL_ALLOWED_TAGS);
                                    break;
                                    case 'startpublishing':
                                        echo wp_kses(wpjobportal_getDataRow(esc_html(__('Posted', 'wp-job-portal')), date_i18n($wpjobportal_dateformat, strtotime($wpjobportal_job->startpublishing))), WPJOBPORTAL_ALLOWED_TAGS);
                                    break;
                                    case 'stoppublishing':
                                        echo wp_kses(wpjobportal_getDataRow(esc_html(__('Apply Before', 'wp-job-portal')), date_i18n($wpjobportal_dateformat, strtotime($wpjobportal_job->stoppublishing))), WPJOBPORTAL_ALLOWED_TAGS);

                                    break;
                                    default:
                                        wpjobportal::$wpjobportal_data['fields_titles_array'][$wpjobportal_fields->field] = $wpjobportal_fields->fieldtitle;
                                        if($wpjobportal_fields->field == 'description'){
                                            $wpjobportal_description_field_label =$wpjobportal_fields->fieldtitle;
                                        }
                                        if($wpjobportal_fields->field == 'map'){
                                            wpjobportal::$wpjobportal_data['layout_values']['map_field_enalbed'] = 1;

                                        }
                                        if($wpjobportal_fields->field == 'tags'){
                                            wpjobportal::$wpjobportal_data['layout_values']['tags_field_enalbed'] = 1;
                                        }
                                        if($wpjobportal_fields->isuserfield == 1){
                                            // if(!in_array('customfield', wpjobportal::$_active_addons)){
                                            //     if($wpjobportal_fields->userfieldtype == 'text' || $wpjobportal_fields->userfieldtype == 'email'){
                                                    $wpjobportal_showCustom = wpjobportal::$_wpjpcustomfield->showCustomFields($wpjobportal_fields,7,$wpjobportal_job->params,'job',$wpjobportal_job->id);
                                                    echo wp_kses($wpjobportal_showCustom, WPJOBPORTAL_ALLOWED_TAGS);
                                            //     }
                                            // }else{
                                            //     $wpjobportal_showCustom = wpjobportal::$_wpjpcustomfield->showCustomFields($wpjobportal_fields,7,$wpjobportal_job->params);
                                            //     echo wp_kses($wpjobportal_showCustom, WPJOBPORTAL_ALLOWED_TAGS);
                                            // }
                                        }
                                    break;
                                    }
                                }
                            if($wpjobportal_description_field_label != '' && $wpjobportal_job->description != ''){// to handle min fields
                            echo '</div>
                                <div class="wjportal-job-data-wrp"> ';
                                echo '
                                    <div class="wjportal-job-desc">
                                        <div class="wjportal-job-sec-title">'. esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_description_field_label)) .'</div>
                                        '. wp_kses($wpjobportal_job->description, WPJOBPORTAL_ALLOWED_TAGS) .'
                                    </div>';
                            }


        break;
        case 'job_seeker':
            echo ' <div class="wjportal-jobinfo-wrp">'; ?>
                        <div class="wjportal-job-detail-about-job-title" >
                            <?php
                                echo esc_html(__('Basic Info','wp-job-portal'));
                            ?>
                        </div>
                    <?php
                    if(isset(wpjobportal::$wpjobportal_data['published_fields']['jobtype'])){
                        if(isset($wpjobportal_job) && !empty($wpjobportal_job->jobtypetitle)){
                            echo'<div class="wjportal-jobinfo wjportal-jobinfo-right-data-jobtype">
                                   <span class="wjportal-jobtype" style="background:'.esc_attr($wpjobportal_job->jobtypecolor).'">'.esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_job->jobtypetitle)).'</span>
                                </div>';
                        }
                    }

                    if(isset(wpjobportal::$wpjobportal_data['published_fields']['jobsalaryrange'])){
                        if(isset($wpjobportal_job) && !empty($wpjobportal_job->salarytype)){
                         echo '<div class="wjportal-jobinfo  wjportal-jobinfo-right-data-salary">
                                <span class="wjportal-jobinfo-data">
                                    <img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/salary.png" alt="'.esc_attr(__("salary",'wp-job-portal')).'" title="'.esc_attr(__("salary",'wp-job-portal')).'" />
                                    '.wp_kses(wpjobportal::$_common->getSalaryRangeView($wpjobportal_job->salarytype, $wpjobportal_job->salarymin, $wpjobportal_job->salarymax, $wpjobportal_job->currency), WPJOBPORTAL_ALLOWED_TAGS).'
                                 </span>';if($wpjobportal_job->salarytype==3 || $wpjobportal_job->salarytype==2) { ?>
                                    <span class="wjportal-salary-type"> <?php  echo ' / ' .esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_job->srangetypetitle)); ?></span>
                                <?php }
                         echo '</div>';
                        }
                    }
                    if(isset(wpjobportal::$wpjobportal_data['published_fields']['jobcategory'])){
                        if(isset($wpjobportal_job) && !empty($wpjobportal_job->cat_title)){
                            echo '<div class="wjportal-jobinfo  wjportal-jobinfo-right-data-category">
                                    <span class="wjportal-jobinfo-data">
                                        <img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/category.png" alt="'.esc_attr(__("category",'wp-job-portal')).'" title="'.esc_attr(__("category",'wp-job-portal')).'" />
                                        '. esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_job->cat_title)) .'
                                     </span>
                                    </div>';
                        }
                    }

                    if(isset($wpjobportal_job) && $wpjobportal_job->created){
                        echo ' <div class="wjportal-jobinfo  wjportal-jobinfo-right-data-created">
                                    <span class="wjportal-jobinfo-data">
                                        <img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/calander.png" alt="'.esc_attr(__("created",'wp-job-portal')).'" title="'.esc_attr(__("created",'wp-job-portal')).'" />
                                        '.esc_html(date_i18n(wpjobportal::$_configuration['date_format'],strtotime($wpjobportal_job->created))).'
                                     </span>
                                </div>';
                    }
                    if(isset(wpjobportal::$wpjobportal_data['published_fields']['stoppublishing'])){
                       if(isset($wpjobportal_job) && $wpjobportal_job->stoppublishing){
                            echo '<div class="wjportal-jobinfo-highlight  wjportal-jobinfo-right-data-close-date">
                                        <span class="wjportal-jobinfo-data ">
                                            <img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/end-date.png" alt="'.esc_attr(__("end date",'wp-job-portal')).'" title="'.esc_attr(__("end date",'wp-job-portal')).'" />'.esc_html(__('Closes','wp-job-portal')).':
                                            '.esc_html(date_i18n(wpjobportal::$_configuration['date_format'],strtotime($wpjobportal_job->stoppublishing))) .'
                                         </span>
                                    </div>';
                        }
                    }
                    if(isset(wpjobportal::$wpjobportal_data['published_fields']['city'])){
                       if(isset($wpjobportal_job) && !empty($wpjobportal_job->multicity)){
                            echo '<div class="wjportal-jobinfo  wjportal-jobinfo-right-data-location">
                                    <span class="wjportal-jobinfo-data">
                                        <img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/locationn.png" alt="'.esc_attr(__("location",'wp-job-portal')).'" title="'.esc_attr(__("location",'wp-job-portal')).'"/>
                                        '. esc_html($wpjobportal_job->multicity) .'
                                     </span>
                                    </div>';
                        }
                    }
                   if(isset($wpjobportal_job) && !empty($wpjobportal_job->hits)){
                        echo '<div class="wjportal-jobinfo  wjportal-jobinfo-right-data-views">
                                    <span class="wjportal-jobinfo-data-view">
                                        <img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/job-views.png" alt="'.esc_attr(__("location",'wp-job-portal')).'" title="'.esc_attr(__("location",'wp-job-portal')).'"/>
                                        '.esc_html(__('Views','wp-job-portal')).':&nbsp;'. esc_html($wpjobportal_job->hits) .'
                                    </span>
                                </div>';
                    }

            echo '</div>';                
        break;
    case 'apply1':
        $wpjobportal_html = '';
        /*
        $wpjobportal_show_apply_form  = wpjobportal::$_config->getConfigurationByConfigName('quick_apply_for_user');
        if (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
            $wpjobportal_show_apply_form  = wpjobportal::$_config->getConfigurationByConfigName('quick_apply_for_visitor');
        }

        if($wpjobportal_show_apply_form == 0){ // show apply button if not showing form
            if(!WPJOBPORTALincluder::getObjectClass('user')->isguest()){
                $wpjobportal_applied = WPJOBPORTALincluder::getJSModel('job')->checkAlreadyAppliedJob($wpjobportal_job->id,$wpjobportal_uid);
                if(wpjobportal::$_config->getConfigValue('showapplybutton') == 1){
                    if($wpjobportal_job->jobapplylink == 1 && !empty($wpjobportal_job->joblink)){
                        echo '<a class="wjportal-job-company-btn" href="'.esc_url($wpjobportal_job->joblink).'"  target="_blank">' . esc_html(__('Apply On This Job', 'wp-job-portal')).'</a>';
                    }elseif(in_array('credits', wpjobportal::$_active_addons)){
                        $wpjobportal_submission_Type = wpjobportal::$_data['submission_type'];
                        do_action('wpjobportal_addons_addressdata_job_apply_btn',$wpjobportal_submission_Type,$wpjobportal_applied,$wpjobportal_job);
                    }else{
                        if(isset($wpjobportal_applied) && !empty($wpjobportal_applied)){
                            if($wpjobportal_applied->no == 1){
                                echo'<span class="wjportal-job-company-apply-status" >'. esc_html(__("You Already Applied to this job ",'wp-job-portal')) .' </span>';
                            }elseif ($wpjobportal_applied->no == 0) {
                                echo '<a class="wjportal-job-company-btn" onclick="getApplyNowByJobid('.esc_js($wpjobportal_job->id).','.esc_js(wpjobportal::wpjobportal_getPageid()).')">' . esc_html(__('Apply On This Job', 'wp-job-portal')).'</a>';
                            }
                        }else{
                            echo '<a class="wjportal-job-company-btn" onclick="getApplyNowByJobid('.esc_js($wpjobportal_job->id).','.esc_js(wpjobportal::wpjobportal_getPageid()).')">' . esc_html(__('Apply On This Job', 'wp-job-portal')).'</a>';
                        }
                    }
                }
            }else{
                if(wpjobportal::$_config->getConfigValue('showapplybutton') == 1){
                    $wpjobportal_visitorcanapply = wpjobportal::$_config->getConfigurationByConfigName('visitor_can_apply_to_job');
					if($wpjobportal_job->jobapplylink == 1 && !empty($wpjobportal_job->joblink)){
						echo '<a class="wjportal-job-act-btn" href="'.esc_url($wpjobportal_job->joblink).'"  target="_blank" >' . esc_html(__('Apply On This Job', 'wp-job-portal')).'</a>';
                    }elseif(in_array('credits', wpjobportal::$_active_addons) && $wpjobportal_visitorcanapply != 1){
                        $wpjobportal_finalurl = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'wpjobportal', 'wpjobportallt'=>'login'));
                        echo '<a href="'.esc_url($wpjobportal_finalurl).'" class="wjportal-job-company-btn" title="' . esc_attr(__('Login to Apply On This Job', 'wp-job-portal')) . '">' . esc_html(__('Login to Apply On This Job', 'wp-job-portal')) . '</a>';
                    } else {
                        $wpjobportal_visitor_show_login_message = wpjobportal::$_config->getConfigurationByConfigName('visitor_show_login_message');
                        if ($wpjobportal_visitor_show_login_message == 1) {
                            echo '<a class="wjportal-job-company-btn" onclick="getApplyNowByJobid('.esc_js($wpjobportal_job->id).','.esc_js(wpjobportal::wpjobportal_getPageid()).')">' . esc_html(__('Apply On This Job', 'wp-job-portal')).'</a>';
                        } else {
                            echo '<a class="wjportal-job-company-btn" href="' . esc_url(wp_nonce_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobapply', 'action'=>'wpjobportaltask', 'task'=>'jobapplyasvisitor', 'wpjobportalid-jobid'=>esc_attr($wpjobportal_job->id), 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid())),'wpjobportal_job_apply_nonce')) . '">' . esc_html(__('Apply On This Job', 'wp-job-portal')).'</a>';
                        }
                    }
                }
            }
        }
        */

        $wpjobportal_package = '';
        if(!WPJOBPORTALincluder::getObjectClass('user')->isguest()){
            $wpjobportal_applied = WPJOBPORTALincluder::getJSModel('job')->checkAlreadyAppliedJob($wpjobportal_job->id,$wpjobportal_uid);
            if($wpjobportal_job->jobapplylink == 1 && !empty($wpjobportal_job->joblink)){
                echo '<a class="wjportal-job-act-btn" href="'.esc_url($wpjobportal_job->joblink).'"  target="_blank" >' . esc_html(__('Apply On This Job', 'wp-job-portal')).'</a>';
            }elseif(in_array('credits', wpjobportal::$_active_addons)){
                //do_action('wpjobportal_addons_job_apply_jobseeker',$wpjobportal_job,$wpjobportal_package,$wpjobportal_applied);
                echo '<a class="wjportal-job-act-btn" href="#wjportal-view-job-page-job-apply-form-bottom-wraper">' . esc_html(__('Apply On This Job', 'wp-job-portal')).'</a>';
            }else{
                if(wpjobportal::$_config->getConfigValue('showapplybutton') == 1){
                    if(isset($wpjobportal_applied) && !empty($wpjobportal_applied)){
                        if($wpjobportal_applied->no != 1){
                            echo '<a class="wjportal-job-act-btn" href="#wjportal-view-job-page-job-apply-form-bottom-wraper">' . esc_html(__('Apply On This Job', 'wp-job-portal')).'</a>';
                        }
                    }
                }
            }
        }else{
            if(wpjobportal::$_config->getConfigValue('showapplybutton') == 1){
                $wpjobportal_visitorcanapply = wpjobportal::$_config->getConfigurationByConfigName('visitor_can_apply_to_job');
                if($wpjobportal_job->jobapplylink == 1 && !empty($wpjobportal_job->joblink)){
                    echo '<a class="wjportal-job-act-btn" href="'.esc_url($wpjobportal_job->joblink).'"  target="_blank" >' . esc_html(__('Apply On This Job', 'wp-job-portal')).'</a>';
                }elseif(in_array('credits', wpjobportal::$_active_addons) && $wpjobportal_visitorcanapply != 1){
                    $wpjobportal_finalurl = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'wpjobportal', 'wpjobportallt'=>'login'));
                    echo '<a href="'.esc_url($wpjobportal_finalurl).'" class="wjportal-job-act-btn" title="' . esc_attr(__('Login to Apply On This Job', 'wp-job-portal')) . '">' . esc_html(__('Login to Apply On This Job', 'wp-job-portal')) . '</a>';
                } else {
                    $wpjobportal_visitor_show_login_message = wpjobportal::$_config->getConfigurationByConfigName('visitor_show_login_message');
                    if ($wpjobportal_visitor_show_login_message == 1) {
                        echo '<a class="wjportal-job-act-btn" href="#wjportal-view-job-page-job-apply-form-bottom-wraper">' . esc_html(__('Apply On This Job', 'wp-job-portal')).'</a>';
                    } else {
                        echo '<a class="wjportal-job-act-btn" href="' . esc_url(wp_nonce_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobapply', 'action'=>'wpjobportaltask', 'task'=>'jobapplyasvisitor', 'wpjobportalid-jobid'=>esc_attr($wpjobportal_job->id), 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid())),'wpjobportal_job_apply_nonce')) . '">' . esc_html(__('Apply On This Job', 'wp-job-portal')).'</a>';
                    }
                }
            }
        }
        

        
        $wpjobportal_allow_tellafriend  = wpjobportal::$_config->getConfigurationByConfigName('allow_tellafriend');
        if($wpjobportal_allow_tellafriend == 1 ){
            # Apply shortlist,Alertjob..
            do_action('wpjobportal_addons_newestjob_btm_btn_for_tellfriend',$wpjobportal_job);
        }
        $wpjobportal_allow_jobshortlist  = wpjobportal::$_config->getConfigurationByConfigName('allow_jobshortlist');
        if($wpjobportal_allow_jobshortlist == 1 AND (! WPJOBPORTALincluder::getObjectClass('user')->isemployer())){
            if(!WPJOBPORTALincluder::getObjectClass('user')->isguest()){
                do_action('wpjobportal_addons_newestjob_btm_btn_for_shortlist',$wpjobportal_job);
            }
        }
        break;

    case 'apply':
        $wpjobportal_package = '';
        $wpjobportal_show_apply_form  = wpjobportal::$_config->getConfigurationByConfigName('quick_apply_for_user');
        if (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
            $wpjobportal_show_apply_form  = wpjobportal::$_config->getConfigurationByConfigName('quick_apply_for_visitor');
        }
        if($wpjobportal_show_apply_form == 0){ // hide apply now button if quick apply is enabled
            if(!WPJOBPORTALincluder::getObjectClass('user')->isguest()){
                $wpjobportal_applied = WPJOBPORTALincluder::getJSModel('job')->checkAlreadyAppliedJob($wpjobportal_job->id,$wpjobportal_uid);
                if($wpjobportal_job->jobapplylink == 1 && !empty($wpjobportal_job->joblink)){
                    echo '<a class="wjportal-job-act-btn" href="'.esc_url($wpjobportal_job->joblink).'"  target="_blank" >' . esc_html(__('Apply On This Job', 'wp-job-portal')).'</a>';
                }elseif(in_array('credits', wpjobportal::$_active_addons)){
                    do_action('wpjobportal_addons_job_apply_jobseeker',$wpjobportal_job,$wpjobportal_package,$wpjobportal_applied);
                }else{
                    if(wpjobportal::$_config->getConfigValue('showapplybutton') == 1){
                        if(isset($wpjobportal_applied) && !empty($wpjobportal_applied)){
                            if($wpjobportal_applied->no != 1){
                                echo '<a class="wjportal-job-act-btn" onclick="getApplyNowByJobid('.esc_js($wpjobportal_job->id).','.esc_js(wpjobportal::wpjobportal_getPageid()).','.esc_js($wpjobportal_package).')">' . esc_html(__('Apply On This Job', 'wp-job-portal')).'</a>';
                            }
                        }
                    }
                }
            }else{
                if(wpjobportal::$_config->getConfigValue('showapplybutton') == 1){
                    $wpjobportal_visitorcanapply = wpjobportal::$_config->getConfigurationByConfigName('visitor_can_apply_to_job');
					if($wpjobportal_job->jobapplylink == 1 && !empty($wpjobportal_job->joblink)){
						echo '<a class="wjportal-job-act-btn" href="'.esc_url($wpjobportal_job->joblink).'"  target="_blank" >' . esc_html(__('Apply On This Job', 'wp-job-portal')).'</a>';
                    }elseif(in_array('credits', wpjobportal::$_active_addons) && $wpjobportal_visitorcanapply != 1){
                        $wpjobportal_finalurl = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'wpjobportal', 'wpjobportallt'=>'login'));
                        echo '<a href="'.esc_url($wpjobportal_finalurl).'" class="wjportal-job-act-btn" title="' . esc_attr(__('Login to Apply On This Job', 'wp-job-portal')) . '">' . esc_html(__('Login to Apply On This Job', 'wp-job-portal')) . '</a>';
                    } else {
                        $wpjobportal_visitor_show_login_message = wpjobportal::$_config->getConfigurationByConfigName('visitor_show_login_message');
                        if ($wpjobportal_visitor_show_login_message == 1) {
                            echo '<a class="wjportal-job-act-btn" onclick="getApplyNowByJobid('.esc_js($wpjobportal_job->id).','.esc_js(wpjobportal::wpjobportal_getPageid()).')">' . esc_html(__('Apply On This Job', 'wp-job-portal')).'</a>';
                        } else {
                            echo '<a class="wjportal-job-act-btn" href="' . esc_url(wp_nonce_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobapply', 'action'=>'wpjobportaltask', 'task'=>'jobapplyasvisitor', 'wpjobportalid-jobid'=>esc_attr($wpjobportal_job->id), 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid())),'wpjobportal_job_apply_nonce')) . '">' . esc_html(__('Apply On This Job', 'wp-job-portal')).'</a>';
                        }
                    }
                }
            }
        }
         # Social Share
         do_action('wpjobportal_credit_addons_social_share_links_job',$wpjobportal_job);
         # Social Comment's
         do_action('wpjobportal_credit_social_comments_for_jobs');
       break;
    }
?>
