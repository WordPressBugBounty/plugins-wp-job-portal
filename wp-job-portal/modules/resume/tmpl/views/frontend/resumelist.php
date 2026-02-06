<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param WP JOB PORTAL 
 */
$wpjobportal_listing_fields = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldOrderingDataForListing(3);

if(!isset($wpjobportal_control)){
	$wpjobportal_control = '';
}

$wpjobportal_list_item_status_extra_class = '';
if($wpjobportal_control == 'myresumes'){
    // handling staus class for main wrap
    if($wpjobportal_myresume->status == -1){
        $wpjobportal_list_item_status_extra_class = ' wpjobportal-list-item-status-rejected ';
    }elseif($wpjobportal_myresume->status == 0){
        $wpjobportal_list_item_status_extra_class = ' wpjobportal-list-item-status-pending ';

    }elseif($wpjobportal_myresume->status == 1){
        $wpjobportal_list_item_status_extra_class = ' wpjobportal-list-item-status-approved ';

    }elseif($wpjobportal_myresume->status == 3){
        $wpjobportal_list_item_status_extra_class = ' wpjobportal-list-item-status-no-payment ';
    }
}
    // handling featured class for main wrap
   	$wpjobportal_curdate = date_i18n('Y-m-d');
   	if(!empty($wpjobportal_myresume->isfeaturedresume)){
        $wpjobportal_featuredexpiry = date_i18n('Y-m-d', strtotime($wpjobportal_myresume->endfeatureddate));
        if($wpjobportal_myresume->isfeaturedresume == 1 && $wpjobportal_featuredexpiry >= $wpjobportal_curdate){
            $wpjobportal_list_item_status_extra_class .= ' wpjobportal-list-item-is-featured ';
        }
    }


 echo '<div id="job-applied-resume" class="wjportal-resume-list '. esc_attr($wpjobportal_list_item_status_extra_class).' ">
		<div class="wjportal-resume-list-top-wrp object_'.esc_attr($wpjobportal_myresume->id).'" data-boxid="resume_'.esc_attr($wpjobportal_myresume->id).'" >';
		if(isset($wpjobportal_listing_fields['photo'])){// show photo if its published and set visible for listing
			WPJOBPORTALincluder::getTemplate('resume/views/frontend/logo',array(
			    'wpjobportal_myresume' => $wpjobportal_myresume
			));
		}

			WPJOBPORTALincluder::getTemplate('resume/views/frontend/main',array(
			    'wpjobportal_myresume' => $wpjobportal_myresume,
			    'wpjobportal_percentage' => $wpjobportal_percentage,
			    'wpjobportal_module' => $wpjobportal_module,
			    'wpjobportal_control'=>$wpjobportal_control
			));
	echo	'</div>
		';
		echo '<div id="wjportal-'.esc_attr($wpjobportal_myresume->id).'" > </div>
            	<div id="comments" class="wjportal-applied-job-actions-popup '.esc_attr($wpjobportal_myresume->id).'" style="display:none" ></div>';
				WPJOBPORTALincluder::getTemplate('resume/views/frontend/control',array(
			        'wpjobportal_control' => $wpjobportal_control,
			        'wpjobportal_myresume'=> $wpjobportal_myresume,
			        'wpjobportal_featuredflag' => true
			    ));
		echo  '</div>';
