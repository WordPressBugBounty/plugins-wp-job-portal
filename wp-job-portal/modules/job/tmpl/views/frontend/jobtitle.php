<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param wpjob portal      job object - optional
*/
wpjobportal::$wpjobportal_data['layout_values']['map_field_enalbed'] = 0;
wpjobportal::$wpjobportal_data['layout_values']['tags_field_enalbed'] = 0;
?>
<!-- Popup Loading For Job Apply -->
<div id="wjportal-popup-background"></div>
<div id="wjportal-listpopup" class="wjportal-popup-wrp">
    <div class="wjportal-popup-cnt">
        <img id="wjportal-popup-close-btn" alt="popup cross" src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/popup-close.png">
        <div class="wjportal-popup-title">
            <span class="wjportal-popup-title2"></span>
        </div>
        <div class="wjportal-popup-contentarea"></div>
    </div>
</div>
<!-- Popup Ends there -->
<!-- Page Title View Job  -->
<div class="wjportal-page-header">
    <?php WPJOBPORTALincluder::getTemplate('templates/pagetitle',array(
        'wpjobportal_module' => 'job'
        ,'wpjobportal_layout' => 'jobdetail'
    )); ?>
</div>
<!-- Page Title Ends there -->

<div class="wjportal-jobdetail-wrapper">
    <?php
    /**
    * @param template redirection 
    * Frontend => detail with icon
    * # case Upper detail =>job_seeker
    **/
        
    ?>
    <div class="wjportal-company-job-viewjob-wrp">
        
        <div class="wjportal-company-job-viewjob-leftwrp">
            <div class="wjportal-job-company-wrp">
                <?php
                /**
                * @param template redirection 
                * Frontend => file logo
                * # case logo
                **/
                    WPJOBPORTALincluder::getTemplate('job/views/frontend/logo',array(
                        'wpjobportal_job' => $wpjobportal_job,
                        'wpjobportal_layout' => 'logo'
                    ));
                ?>
                <div class="wjportal-job-company-cnt">
                    <div class="wjportal-view-job-title-wrp">
                        <?php echo esc_html($wpjobportal_job->title); ?>
                    </div>
                    <?php do_action('wpjobportal_credit_addons_feature_job_popup_for_emp',$wpjobportal_job); ?>
                    <div class="wjportal-job-company-info">
                        <?php 
                        if(in_array('multicompany', wpjobportal::$_active_addons)){
                            $wpjobportal_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'multicompany', 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$wpjobportal_job->companyid));
                        }else{
                            $wpjobportal_url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$wpjobportal_job->companyid));
                        }
                        ?>
                        <?php if (wpjobportal::$_config->getConfigValue('comp_name')) { ?>
                            <a class="wjportal-job-company-name" href="<?php echo esc_url($wpjobportal_url);?>">
                                <?php echo esc_html($wpjobportal_job->companyname); ?>
                            </a>
                        <?php }?>
                    </div>
                    <?php
                    $wpjobportal_comapny_listing_fields = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldOrderingData(1);
                    if(isset($wpjobportal_job) && !empty($wpjobportal_job->companyemail)) :
                        $wpjobportal_config_array = wpjobportal::$_data['config'];
                        if ($wpjobportal_config_array['comp_email_address'] == 1) :
                            if(isset($wpjobportal_comapny_listing_fields['contactemail']) && $wpjobportal_comapny_listing_fields['contactemail'] !='' ){
                    ?>
                                <div class="wjportal-job-company-info">
                                    <span class="wjportal-job-company-info-tit"><?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_comapny_listing_fields['contactemail'])); ?>:</span>
                                    <span class="wjportal-job-company-info-val"><?php echo esc_html($wpjobportal_job->companyemail); ?></span>
                                </div>
                        <?php } ?>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php
                        if(isset($wpjobportal_comapny_listing_fields['url']) && $wpjobportal_comapny_listing_fields['url'] !='' ){
                            if(isset($wpjobportal_job) && !empty($wpjobportal_job->url)) :?>
                                <div class="wjportal-job-company-info">
                                    <span class="wjportal-job-company-info-tit"><?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_comapny_listing_fields['url'])); ?>:</span>
                                    <span class="wjportal-job-company-info-val"><?php echo esc_html($wpjobportal_job->companyurl); ?></span>
                                </div>
                    <?php endif; ?>
                    <?php } ?>

                </div>


            </div>
            <div class="wjportal-job-data-wrp">
                <?php
                /**
                * @param template redirection 
                * Frontend => View Body Data 
                * # case detail body
                **/
                    WPJOBPORTALincluder::getTemplate('job/views/frontend/title',array(
                        'wpjobportal_job' => $wpjobportal_job,
                        'wpjobportal_jobfields' => $wpjobportal_jobfields,
                        'wpjobportal_layout' => 'detailbody'
                    ));
                ?> 
            </div>
                <?php
                if(wpjobportal::$wpjobportal_data['layout_values']['map_field_enalbed'] == 1) { ?>
                    <div class="wjportal-job-data-map-wrp"> <?php
                        do_action('wpjobportal_addons_addressdata_jobview',$wpjobportal_job); ?>
                    </div>
                    <?php
                }

                if(wpjobportal::$wpjobportal_data['layout_values']['tags_field_enalbed'] == 1 && wpjobportal::$_data[0]->tags != '') { ?>
                    <div class="wjportal-job-data-tags-wrp">
                        <?php
                        do_action('wpjobportal_credit_addons_search_job_ref_tags',$wpjobportal_job); ?>
                    </div> <?php
                }

            // job apply form
                    WPJOBPORTALincluder::getTemplate('job/views/frontend/jobapply', array('wpjobportal_job' => $wpjobportal_job));
            ?>
            <div class="wjportal-job-btn-wrp">
                <?php
                /**
                * @param template redirection 
                * Frontend => View btn Job View 
                * # case apply
                **/
                    // design upgraded
                    // WPJOBPORTALincluder::getTemplate('job/views/frontend/title',array(
                    //     'wpjobportal_job' => $wpjobportal_job,
                    //     'wpjobportal_layout' => 'apply'
                    // ));
                ?>
            </div>
        </div>
        <div class="wjportal-company-job-viewjob-rightwrp"> 
            <div class="wjportal-job-company-btn-wrp">
                <?php
                /**
                * @param template redirection 
                * Frontend => View Body Data 
                * # case apply lower btn
                **/
                    WPJOBPORTALincluder::getTemplate('job/views/frontend/title',array(
                        'wpjobportal_job' => $wpjobportal_job,
                        'wpjobportal_layout' => 'apply1'
                    ));
                ?>
            </div>

            <?php 
            WPJOBPORTALincluder::getTemplate('job/views/frontend/title',array(
                'wpjobportal_job' => $wpjobportal_job,
                'wpjobportal_layout' =>'job_seeker'
            )); ?>

            <div class="wjportal-job-detail-about-company-wrap" >

                <div class="wjportal-job-detail-about-company-title" >
                    <?php
                        echo esc_html(__('About','wp-job-portal')). " " .esc_html($wpjobportal_job->companyname);
                    ?>
                </div>
                <div class="wjportal-job-detail-about-company-description" >
                    <?php echo esc_html( wp_trim_words( $wpjobportal_job->company_desc, 30, '...' ) ); ?>
                </div>
                <div class="wjportal-job-detail-about-company-buttons-wrap" >
                    <div class="wjportal-company-btn-wrp">
                         <a href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$wpjobportal_job->companyid))); ?>" class="wjportal-company-view-company-btn" title="<?php echo esc_attr(__('View Company', 'wp-job-portal')); ?>"><?php echo esc_html(__('View Company', 'wp-job-portal')); ?></a>
                    </div>
                    <?php
                    $wpjobportal_config_array = wpjobportal::$_data['config'];
                    if ($wpjobportal_config_array['comp_viewalljobs']==1 && !empty(wpjobportal::$_data['0'])) {
                        $wpjobportal_compalias = wpjobportal::$_data[0]->companyalias.'-'.wpjobportal::$_data[0]->companyid;
                        ?>
                       <div class="wjportal-company-btn-wrp-viewjobs">
                            <a href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'jobs', 'company'=>$wpjobportal_compalias))); ?>" class="wjportal-company-view-all-jobs-btn" title="<?php echo esc_attr(__('View Company Jobs', 'wp-job-portal')); ?>"><?php echo esc_html(__('View Company Jobs', 'wp-job-portal')); ?></a>
                       </div>
                       <?php
                    }
                        ?>

            </div>
    </div>
</div>
