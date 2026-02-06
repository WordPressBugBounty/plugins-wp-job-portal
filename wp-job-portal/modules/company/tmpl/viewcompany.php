<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
?>
<div class="wjportal-main-up-wrapper">
<?php
if ( !WPJOBPORTALincluder::getTemplate('templates/header', array('wpjobportal_module' => 'company')) ) {
    return;
}
//get Company id && Company Name

$wpjobportal_companyname = isset(wpjobportal::$_data[0]->name) ? wpjobportal::$_data[0]->name : '';
$wpjobportal_companyid = isset(wpjobportal::$_data[0]->id) ? wpjobportal::$_data[0]->id : '';
if (wpjobportal::$_error_flag == null) {
    function wpjobportal_getDataRow($title, $wpjobportal_value) {
        $wpjobportal_html = '<div class="wjportal-company-data">
                    <span class="wjportal-company-data-tit">' . $title . ':</span>
                    <span class="wjportal-company-data-val">' . $wpjobportal_value . '</span>
                </div>';
        return $wpjobportal_html;
    }
    $wpjobportal_data_class = (isset(wpjobportal::$_data[2]['logo'])) ? 'two_column' : 'one_column';
    $wpjobportal_config_array = wpjobportal::$_data['config'];
    ?>
    <div class="wjportal-main-wrapper wjportal-clearfix">
        <div class="wjportal-page-header">
            <?php
                WPJOBPORTALincluder::getTemplate('templates/pagetitle', array('wpjobportal_module' => 'company', 'wpjobportal_layout' => 'company','wpjobportal_data' => wpjobportal::$_data[0],'wpjobportal_config_array' => $wpjobportal_config_array));
            ?>
        </div>
        <?php
        $wpjobportal_extra_featured_class = '';
        if(!empty(wpjobportal::$_data[0]) && !empty(wpjobportal::$_data[0]->isfeaturedcompany) && wpjobportal::$_data[0]->isfeaturedcompany == 1){
            $wpjobportal_curdate = date_i18n('Y-m-d');
            $wpjobportal_featuredexpiry = date_i18n('Y-m-d', strtotime(wpjobportal::$_data[0]->endfeatureddate));
            if($wpjobportal_featuredexpiry >= $wpjobportal_curdate){
                $wpjobportal_extra_featured_class = 'wjportal-view-page-featured-flag';
            }
        }
        ?>
        <div class="wjportal-companydetail-wrapper <?php echo esc_attr($wpjobportal_extra_featured_class);?>">
            <?php
            /**
            * @param Details Section For Company View
            * @param config => admin Configuration
            **/

            WPJOBPORTALincluder::getTemplate('company/views/frontend/viewcompany',array(
                'wpjobportal_config_array' => $wpjobportal_config_array,
                'wpjobportal_data_class' => $wpjobportal_data_class,
                'wpjobportal_module' => 'company',
                'wpjobportal_config_array' => wpjobportal::$_data['config']
            ));
            if (WPJOBPORTALincluder::getObjectClass('user')->isemployer() == 0) { ?>
                <div class="wjportal-job-btn-wrp">
                    <?php $wpjobportal_compalias = wpjobportal::$_data[0]->alias.'-'.wpjobportal::$_data[0]->id; ?>
                </div>
            <?php } ?>
            <?php 
                if(in_array('credits', wpjobportal::$_active_addons)){
                    if(wpjobportal::$_config->getConfigValue('submission_type') == 2){
                        $wpjobportal_paymentconfig = wpjobportal::$_data['paymentconfig'];
                        $wpjobportal_price = wpjobportal::$_config->getConfigValue('job_viewcompanycontact_price_perlisting');
                        $wpjobportal_currencyid = wpjobportal::$_config->getConfigValue('job_currency_viewcompanycontact_perlisting');
                        $wpjobportal_decimals = WPJOBPORTALincluder::getJSModel('currency')->getDecimalPlaces($wpjobportal_currencyid);
                        $wpjobportal_formattedPrice = wpjobportalphplib::wpJP_number_format($wpjobportal_price,$wpjobportal_decimals);
                        //Price Listing For Department
                        $wpjobportal_priceCompanytlist = wpjobportal::$_common->getFancyPrice($wpjobportal_price,$wpjobportal_currencyid,array('decimal_places'=>$wpjobportal_decimals));
                        //Apply Filter's
                        do_action('wpjobportal_addons_perlisting_payment',$wpjobportal_paymentconfig,$wpjobportal_companyid,'listingpaypalCompanyView','job_viewcompanycontact_price_perlisting','listingCompanyViewstripe','companytitle',$wpjobportal_companyname,$wpjobportal_price,$wpjobportal_currencyid,'Department',wpjobportal::wpjobportal_getPageid());
                    }
                }
            ?>
        </div>
    </div>
        <?php 
        } else {
            // Error Message Throw
            if(wpjobportal::$_error_flag_message !=''){
                echo wp_kses_post(wpjobportal::$_error_flag_message);
            }
        }
        ?>
</div>
