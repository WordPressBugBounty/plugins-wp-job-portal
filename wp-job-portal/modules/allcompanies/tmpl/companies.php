<?php
if (!defined('ABSPATH')) die('Restricted Access');
?>
<div class="wjportal-main-up-wrapper">
<?php
$wpjobportal_companies = isset(wpjobportal::$_data[0]) && is_array(wpjobportal::$_data[0]) ? wpjobportal::$_data[0] : array();
?>
<div class="wjportal-main-wrapper wjportal-clearfix">
    <div class="wjportal-page-header">
        <div class="wjportal-page-header-cnt">
            <?php 
                if (!WPJOBPORTALincluder::getTemplate('templates/pagetitle',array('wpjobportal_module' => 'company','wpjobportal_layout' => 'companies'))){
                    return;
                }
            ?>
        </div>
        <?php
        if ( !WPJOBPORTALincluder::getTemplate('templates/header',array('wpjobportal_module' => 'company')) ) {
                    return;
        }
        ?>
    </div>
    <div class="wjportal-company-list-wrapper wjportal-my-company-wrp">
        <?php
        if (!empty($wpjobportal_companies)) {
            //////******Data Listing*********//////
            foreach ($wpjobportal_companies AS $wpjobportal_company) {
               WPJOBPORTALincluder::getTemplate('company/views/frontend/companylist', array(
                   'wpjobportal_company' => $wpjobportal_company,
                   'module_company' => 'company',
                   'wpjobportal_layout' => 'companies'
                ));
            }
            ///**pagination Calling*******///
            if(empty(wpjobportal::$_data['shortcode_option_no_of_companies'])){
                if (wpjobportal::$_data[1]) {
                    WPJOBPORTALincluder::getTemplate('templates/pagination',array(
                        'pagination' => wpjobportal::$_data[1],
                        'wpjobportal_module' => 'multicompany'
                    ));
                }
            }

        }else{
            $wpjobportal_msg = __('No Records Found','wp-job-portal');
            // $linkcompany[] = array(
            //     'link' => wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company', 'wpjobportallt'=>'addcompany')),
            //     'text' => __('Add New','wp-job-portal') .' '. __('Company', 'wp-job-portal')
            // );
            WPJOBPORTALlayout::getNoRecordFound($wpjobportal_msg);
        }
        ?>
    </div>
</div>
</div>
