<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
/**
* @param wp job portal 
* Company => Detail via Template 
* redirection's 
*/
$wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];

/**
* @param wp job portal
* # company list 
* generic module for cases
*/
$wpjobportal_listing_fields = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldOrderingDataForListing(1);
 if(in_array('multicompany', wpjobportal::$_active_addons)){
    $wpjobportal_mod = "multicompany";
}else{
    $wpjobportal_mod = "company";
} 
?>
<?php
switch ($wpjobportal_layout) {
    case 'companydetail':
        $wpjobportal_config_array = wpjobportal::$_data['config'];
        $wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
        $wpjobportal_description_field_label = '';
        $wpjobportal_company_content = ''; // collect valid rows
        $wpjobportal_company_content_heading = ''; // collect valid rows

        if (wpjobportal::$_data['companycontactdetail'] == true) {
            $wpjobportal_company_content_heading .= '<div class="wjportal-company-sec-title">'
                . esc_html(__('Company Info','wp-job-portal'))
                . '</div>';
        }

        foreach (wpjobportal::$_data[2] as $wpjobportal_key => $wpjobportal_val) {

            switch ($wpjobportal_key) {
                case 'contactemail':
                    if (wpjobportal::$_data['companycontactdetail'] == true && !empty($wpjobportal_config_array['comp_email_address'])&& isset(wpjobportal::$_data[0]->contactemail) && wpjobportal::$_data[0]->contactemail !== '') {
                        $wpjobportal_company_content .= wp_kses(
                            wpjobportal_getDataRow(wpjobportal::wpjobportal_getVariableValue($wpjobportal_val), wpjobportal::$_data[0]->contactemail),
                            WPJOBPORTAL_ALLOWED_TAGS
                        );
                    }
                    break;

                case 'address1':
                    if (wpjobportal::$_data['companycontactdetail'] == true && !empty(wpjobportal::$_data[0]->address1)) {
                        $wpjobportal_company_content .= wp_kses(
                            wpjobportal_getDataRow(wpjobportal::wpjobportal_getVariableValue($wpjobportal_val), wpjobportal::$_data[0]->address1),
                            WPJOBPORTAL_ALLOWED_TAGS
                        );
                    }
                    break;

                case 'address2':
                    if (wpjobportal::$_data['companycontactdetail'] == true && !empty(wpjobportal::$_data[0]->address2) ) {
                        $wpjobportal_company_content .= wp_kses(
                            wpjobportal_getDataRow(wpjobportal::wpjobportal_getVariableValue($wpjobportal_val), wpjobportal::$_data[0]->address2),
                            WPJOBPORTAL_ALLOWED_TAGS
                        );
                    }
                    break;

                default: // handle custom/user fields
                    if ($wpjobportal_key == 'description') {
                        $wpjobportal_description_field_label = $wpjobportal_val;
                    }

                    $wpjobportal_customfields = WPJOBPORTALincluder::getObjectClass('customfields')->userFieldsData(1);
                    foreach ($wpjobportal_customfields as $wpjobportal_field) {
                        if ($wpjobportal_key == $wpjobportal_field->field) {
                            $wpjobportal_showCustom = wpjobportal::$_wpjpcustomfield->showCustomFields($wpjobportal_field, 5, wpjobportal::$_data[0]->params, 'company', wpjobportal::$_data[0]->id );
                            if (trim($wpjobportal_showCustom) !== '') {
                                $wpjobportal_company_content .= wp_kses($wpjobportal_showCustom, WPJOBPORTAL_ALLOWED_TAGS);
                            }
                        }
                    }
                    break;
            }
        }

        // Output company info section only if not empty

        if (trim($wpjobportal_company_content) !== '') {
            echo '<div class="wjportal-company-data-wrp">' .wp_kses($wpjobportal_company_content_heading,WPJOBPORTAL_ALLOWED_TAGS) . wp_kses($wpjobportal_company_content,WPJOBPORTAL_ALLOWED_TAGS) . '</div>';
        }

        // Handle description separately
        if ( $wpjobportal_description_field_label !== '' &&  !empty($wpjobportal_config_array['comp_description']) && !empty(wpjobportal::$_data[0]->description)) {
            echo '<div class="wjportal-company-data-wrp">
                    <div class="wjportal-company-sec-title">' .
                        esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_description_field_label)) .
                    '</div>
                    <div class="wjportal-company-desc">' .
                        wp_kses(wpjobportal::$_data[0]->description, WPJOBPORTAL_ALLOWED_TAGS) .
                    '</div>
                </div>';
        }

    break;
    case 'detail':
     $wpjobportal_config_array = wpjobportal::$_data['config']; ?>
        <div class="wjportal-company-cnt-wrp">
            <div class="wjportal-company-middle-wrp">
                <?php if(isset($wpjobportal_listing_fields['url']) && $wpjobportal_listing_fields['url'] !='' ){ ?>
                    <?php if( $wpjobportal_config_array['comp_show_url'] == 1): ?>
                            <div class="wjportal-company-data">
                                <span class="wjportal-companyname">
                                    <?php echo esc_html($wpjobportal_company->url); ?>
                                </span>
                            </div>
                    <?php endif; ?>
                <?php } ?>

                <div class="wjportal-company-data"> 
                    <?php
                        if(empty(wpjobportal::$_data['shortcode_option_hide_company_name'])){
                            if (wpjobportal::$_config->getConfigValue('comp_name')) { ?>
                                <span class="wjportal-company-title">
                                    <a href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>$wpjobportal_mod, 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$wpjobportal_company->aliasid))); ?>">
                                        <?php echo esc_html($wpjobportal_company->name); ?>
                                    </a>
                                </span>
                                <?php
                            }
                        }
                        // to show featured tag on all companies layout
                        if(WPJOBPORTALincluder::getObjectClass('user')->isemployer() || (isset($wpjobportal_companies_layout) && $wpjobportal_companies_layout == 'companies')){
                            do_action('wpjobportal_addons_lable_comp_feature',$wpjobportal_company);
                        }
                    ?>
                </div>
                <div class="wjportal-company-data">
                    <?php if(!isset($wpjobportal_showcreated) || $wpjobportal_showcreated): ?>
                        <div class="wjportal-company-data-text wjportal-company-data-text-created">
                            <span class="wjportal-company-data-title">
                                <?php echo esc_html(__('Created', 'wp-job-portal')) . ':'; ?>
                            </span>
                            <span class="wjportal-company-data-value">
                                <?php echo esc_html(human_time_diff(strtotime($wpjobportal_company->created),strtotime(date_i18n("Y-m-d H:i:s")))).' '.esc_html(__("Ago",'wp-job-portal')); ?>
                            </span>
                        </div>
                    <?php endif; ?>

                    <?php if(WPJOBPORTALincluder::getObjectClass('user')->isemployer()){ ?>
                    <div class="wjportal-company-data-text wjportal-company-data-status">
                        <span class="wjportal-company-data-title">
                            <?php echo esc_html(__('Status', 'wp-job-portal')) . ':'; ?>
                        </span>
                        <?php
                            $wpjobportal_color = ($wpjobportal_company->status == 1) ? "green" : "red";
                            if ($wpjobportal_company->status == 1) {
                                $wpjobportal_statusCheck = esc_html(__('Approved', 'wp-job-portal'));
                            } elseif ($wpjobportal_company->status == 0) {
                                $wpjobportal_statusCheck = esc_html(__('Waiting for approval', 'wp-job-portal'));
                            }elseif($wpjobportal_company->status == 2){
                                 $wpjobportal_statusCheck = esc_html(__('Pending For Approval of Payment', 'wp-job-portal'));
                            }elseif ($wpjobportal_company->status == 3) {
                                $wpjobportal_statusCheck = esc_html(__('Pending Due To Payment', 'wp-job-portal'));
                            }else {
                                $wpjobportal_statusCheck = esc_html(__('Rejected', 'wp-job-portal'));
                            }
                        ?>
                        <span class="wjportal-company-data-value <?php echo esc_attr($wpjobportal_color); ?>">
                            <?php echo esc_html($wpjobportal_statusCheck); ?>
                        </span>
                    </div>
                    <?php }
                    if(empty(wpjobportal::$_data['shortcode_option_hide_company_location'])){
                        if(isset($wpjobportal_company) && !empty($wpjobportal_company->location) && $wpjobportal_config_array['comp_city'] == 1):
                             if(isset($wpjobportal_listing_fields['city']) && $wpjobportal_listing_fields['city'] !='' ){ ?>
                                <div class="wjportal-company-data-text wjportal-company-data-location">
                                    <span class="wjportal-company-data-title">
                                        <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_listing_fields['city'])) . ':'; ?>
                                    </span>
                                    <span class="wjportal-company-data-value">
                                        <?php echo esc_html($wpjobportal_company->location); ?>
                                    </span>
                                </div><?php
                            }
                         endif;
                    } ?>
                </div>

                <!-- custom fields -->
                <?php
                if(isset($wpjobportal_listing_fields['description']) && $wpjobportal_listing_fields['description'] !='' ){ ?>
                    <div class="wjportal-company-listing-data-description">
                        <?php
                            echo esc_html( wp_trim_words( $wpjobportal_company->description, 30, '...' ) )
                        ?>
                    </div>
                <?php } ?>
                <!-- custom fields -->
                <div class="wjportal-custom-field-wrp">
                    <?php
                        $wpjobportal_customfields = WPJOBPORTALincluder::getObjectClass('customfields')->userFieldsData(1,1);
                            foreach ($wpjobportal_customfields as $wpjobportal_field) {
                                $wpjobportal_showCustom =  wpjobportal::$_wpjpcustomfield->showCustomFields($wpjobportal_field,8,$wpjobportal_company->params);
                                echo wp_kses($wpjobportal_showCustom, WPJOBPORTAL_ALLOWED_TAGS);
                            }
                    ?>
                </div>
            </div>
            <?php /*
            <div class="wjportal-company-right-wrp">
                <div class="wjportal-company-action">
                    <a class="wjportal-company-act-btn" href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>$wpjobportal_mod, 'wpjobportallt'=>'viewcompany', 'wpjobportalid'=>$wpjobportal_company->aliasid))); ?>" title="<?php echo esc_attr(__('View company','wp-job-portal')); ?>">
                        <?php echo esc_html(__('View Company','wp-job-portal')); ?>
                    </a>
                </div>
            </div>
            */ ?>
        </div>
        <?php
    break;
   }
