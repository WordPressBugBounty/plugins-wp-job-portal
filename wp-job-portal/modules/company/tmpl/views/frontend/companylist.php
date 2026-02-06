<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
/**
* @param company 	company object
*/

if (!$wpjobportal_company) {
	return;
}

$wpjobportal_list_item_status_extra_class = '';
if(isset($wpjobportal_layout) && $wpjobportal_layout == 'control'){
    // handling staus class for main wrap
    if($wpjobportal_company->status == -1){
        $wpjobportal_list_item_status_extra_class = ' wpjobportal-list-item-status-rejected ';
    }elseif($wpjobportal_company->status == 0){
        $wpjobportal_list_item_status_extra_class = ' wpjobportal-list-item-status-pending ';

    }elseif($wpjobportal_company->status == 1){
        $wpjobportal_list_item_status_extra_class = ' wpjobportal-list-item-status-approved ';

    }elseif($wpjobportal_company->status == 3){
        $wpjobportal_list_item_status_extra_class = ' wpjobportal-list-item-status-no-payment ';
    }

}
// handling featured class for main wrap
$wpjobportal_curdate = date_i18n('Y-m-d');
if(!empty($wpjobportal_company->isfeaturedcompany)){
    $wpjobportal_featuredexpiry = date_i18n('Y-m-d', strtotime($wpjobportal_company->endfeatureddate));
    if($wpjobportal_company->isfeaturedcompany == 1 && $wpjobportal_featuredexpiry >= $wpjobportal_curdate){
        $wpjobportal_list_item_status_extra_class .= ' wpjobportal-list-item-is-featured ';
    }
}
?>
<div class="wjportal-company-list <?php echo esc_attr($wpjobportal_list_item_status_extra_class);?> ">
    <div class="wjportal-company-list-top-wrp object_<?php echo esc_attr($wpjobportal_company->id); ?>" data-boxid="company_<?php echo esc_attr($wpjobportal_company->id); ?>">
        <?php

            if(empty(wpjobportal::$_data['shortcode_option_hide_company_logo'])){
            	WPJOBPORTALincluder::getTemplate('company/views/frontend/logo', array(
            		'wpjobportal_company' => $wpjobportal_company,
                    'wpjobportal_layout' => 'complogo',
                    'wpjobportal_module' => 'company'

                ));
            }
            WPJOBPORTALincluder::getTemplate('company/views/frontend/detail', array(
        		'wpjobportal_company' => $wpjobportal_company,
                'wpjobportal_layout' => 'detail',
                'wpjobportal_companies_layout' => $wpjobportal_layout,
                'wpjobportal_module' => 'company'
        	));
    	?>
    </div>
    <div class="wjportal-company-list-btm-wrp">
    	<?php
        	WPJOBPORTALincluder::getTemplate('company/views/frontend/control', array(
        		'wpjobportal_company' => $wpjobportal_company,
                'wpjobportal_layout' => $wpjobportal_layout,
                'wpjobportal_module' => 'company'
        	));
    	?>
    </div>
    
</div>
