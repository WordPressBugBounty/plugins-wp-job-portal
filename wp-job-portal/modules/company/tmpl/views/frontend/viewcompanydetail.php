<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
/**
 * @param wp job portal      job object - optional
 * & section wise Company Detail
*/
?>

<div class="wjportal-company-wrp">
    <div class="wjportal-companyinfo-left-wrap" >
        <?php
        if (isset(wpjobportal::$_data[2]['logo'])) { ?>
            <?php
                $wpjobportal_html='';
                    WPJOBPORTALincluder::getTemplate('company/views/frontend/logo',array(
                        'wpjobportal_layout' => 'complogo',
                        'wpjobportal_html' => $wpjobportal_html,
                        'wpjobportal_classname' => 'wjportal-company-logo-image',
                        'wpjobportal_module' => $wpjobportal_module
                ));
        } ?>
    </div>
    <div class="wjportal-companyinfo-middle-wrap" >
        <?php

            $wpjobportal_data = wpjobportal::$_data[0];
            if (isset($wpjobportal_data->name) && $wpjobportal_config_array['comp_name'] == 1) {
                echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_data->name));
            }// to show tag line when company name is hidden from configuration
            if(isset(wpjobportal::$_data[2]) && isset(wpjobportal::$_data[2]['tagline']) && wpjobportal::$_data[2]['tagline'] != '' && !empty($wpjobportal_data->tagline) ){
                echo '<span class="wjportal-company-salogon">
                            -'.esc_html($wpjobportal_data->tagline).'
                    </span>';
            }
            do_action('wpjobportal_addons_lable_comp_feature',$wpjobportal_data);
            WPJOBPORTALincluder::getTemplate('company/views/frontend/title',array(
                'wpjobportal_layouts' => 'viewcomp_uppersection',
                'wpjobportal_config_array' => $wpjobportal_config_array,
                'wpjobportal_data_class' => $wpjobportal_data_class,
                'wpjobportal_module' => $wpjobportal_module
            ));
        ?>
    </div>
    <div class="wjportal-companyinfo-right-wrap" >
        <div class="wjportal-companyinfo-social-links-wrapper" >
            <div class="wjportal-job-btn-wrp">
                <?php
                if(in_array('credits', wpjobportal::$_active_addons)){
                    if(wpjobportal::$_config->getConfigValue('submission_type') == 2){
                        $wpjobportal_paymentconfig = wpjobportal::$_data['paymentconfig'];
                        $wpjobportal_price = wpjobportal::$_config->getConfigValue('job_viewcompanycontact_price_perlisting');
                        $wpjobportal_currencyid = wpjobportal::$_config->getConfigValue('job_currency_viewcompanycontact_perlisting');
                        $wpjobportal_decimals = WPJOBPORTALincluder::getJSModel('currency')->getDecimalPlaces($wpjobportal_currencyid);
                        $wpjobportal_formattedPrice = wpjobportalphplib::wpJP_number_format($wpjobportal_price,$wpjobportal_decimals);
                        //Price Listing For Department
                        $wpjobportal_companyid = wpjobportal::$_data[0]->id;
                        $wpjobportal_companyname = wpjobportal::$_data[0]->name;
                        $wpjobportal_priceCompanytlist = wpjobportal::$_common->getFancyPrice($wpjobportal_price,$wpjobportal_currencyid,array('decimal_places'=>$wpjobportal_decimals));
                        //Apply Filter's
                        do_action('wpjobportal_addons_perlisting_payment',$wpjobportal_paymentconfig,$wpjobportal_companyid,'listingpaypalCompanyView','job_viewcompanycontact_price_perlisting','listingCompanyViewstripe','companytitle',$wpjobportal_companyname,$wpjobportal_price,$wpjobportal_currencyid,'Department',wpjobportal::wpjobportal_getPageid());
                    }
                }
                do_action('wpjobportal_addons_company_contact_detail',wpjobportal::$_data[0],wpjobportal::$_data['companycontactdetail']);
                ?>
            </div>
            <?php
            if (wpjobportal::$_data['companycontactdetail'] == true){
                foreach (wpjobportal::$_data[2] AS $wpjobportal_key => $wpjobportal_val) {
                    switch ($wpjobportal_key) {
                        case 'facebook_link':
                            if(isset(wpjobportal::$_data[0]->facebook_link) && wpjobportal::$_data[0]->facebook_link != ''){ ?>
                                <a class="wjportal-companyinfo-social-link" href="<?php echo esc_url(wpjobportal::$_data[0]->facebook_link);?>"><i class="fa fa-facebook"></i></a>
                                <?php
                            }
                        break;
                        case 'youtube_link':
                            if(isset(wpjobportal::$_data[0]->youtube_link) && wpjobportal::$_data[0]->youtube_link != ''){ ?>
                                <a class="wjportal-companyinfo-social-link" href="<?php echo esc_url(wpjobportal::$_data[0]->youtube_link);?>"><i class="fa fa-youtube"></i></a>
                                <?php
                            }
                        break;
                        case 'linkedin_link':
                            if(isset(wpjobportal::$_data[0]->linkedin_link) && wpjobportal::$_data[0]->linkedin_link != ''){ ?>
                                <a class="wjportal-companyinfo-social-link" href="<?php echo esc_url(wpjobportal::$_data[0]->linkedin_link);?>"><i class="fa fa-linkedin"></i></a>
                                <?php
                            }
                        break;
                        case 'twiter_link':
                            if(isset(wpjobportal::$_data[0]->twiter_link) && wpjobportal::$_data[0]->twiter_link != ''){ ?>
                                <a class="wjportal-companyinfo-social-link" href="<?php echo esc_url(wpjobportal::$_data[0]->twiter_link);?>"><i class="fa fa-twitter"></i></a>
                                <?php
                            }
                        break;
                    }
                }
            }
            ?>
        </div>
    </div>
</div>
<?php
    /**
     * @param Middle Section 
     * Company Contact Body Detail
     **/
    WPJOBPORTALincluder::getTemplate('company/views/frontend/detail',array(
        'wpjobportal_layout' => 'companydetail',
        'wpjobportal_data_class' => $wpjobportal_data_class,
        'wpjobportal_config_array' => $wpjobportal_config_array,
        'wpjobportal_module' => $wpjobportal_module
    ));
?>
<?php
    /**
     * @param Button Section
     * To view All job's Related Companies
     **/
    WPJOBPORTALincluder::getTemplate('company/views/frontend/control',array(
        'wpjobportal_config_array' => $wpjobportal_config_array,
        'wpjobportal_layout' => 'showalljobs',
        'wpjobportal_module' => $wpjobportal_module
    ));
?>    
