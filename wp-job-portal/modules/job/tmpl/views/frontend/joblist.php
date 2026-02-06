<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param job      job object - optional
 */
if (!isset($wpjobportal_job->id)) {
	$wpjobportal_job->id = '';
}
$wpjobportal_list_item_status_extra_class = '';
if(isset($wpjobportal_control)){
	if($wpjobportal_control == 'myjobs'){
	    // handling staus class for main wrap
	    if($wpjobportal_job->status == -1){
	        $wpjobportal_list_item_status_extra_class = ' wpjobportal-list-item-status-rejected ';
	    }elseif($wpjobportal_job->status == 0){
	        $wpjobportal_list_item_status_extra_class = ' wpjobportal-list-item-status-pending ';

	    }elseif($wpjobportal_job->status == 1){
	        $wpjobportal_list_item_status_extra_class = ' wpjobportal-list-item-status-approved ';

	    }elseif($wpjobportal_job->status == 3){
	        $wpjobportal_list_item_status_extra_class = ' wpjobportal-list-item-status-no-payment ';
	    }
    }
    
	   // handling featured class for main wrap
	   $wpjobportal_curdate = date_i18n('Y-m-d');
	   if(!empty($wpjobportal_job->isfeaturedjob)){
	        $wpjobportal_featuredexpiry = date_i18n('Y-m-d', strtotime($wpjobportal_job->endfeatureddate));
	        if($wpjobportal_job->isfeaturedjob == 1 && $wpjobportal_featuredexpiry >= $wpjobportal_curdate){
	            $wpjobportal_list_item_status_extra_class .= ' wpjobportal-list-item-is-featured ';
	        }
	    }
}

?>
<div class="wjportal-jobs-list <?php echo esc_attr($wpjobportal_list_item_status_extra_class);?> ">
	<div class="wjportal-jobs-list-top-wrp object_<?php echo esc_attr($wpjobportal_job->id);?>" data-boxid="job_<?php echo esc_attr($wpjobportal_job->id); ?>">
		<?php
			WPJOBPORTALincluder::getTemplate('job/views/frontend/logo',array(
			    'wpjobportal_layout' => 'toprowlogo',
			    'wpjobportal_job' => $wpjobportal_job
			)); 
			WPJOBPORTALincluder::getTemplate('job/views/frontend/main',array(
			    'wpjobportal_labelflag' => $wpjobportal_labelflag,
			    'wpjobportal_job' => $wpjobportal_job,
			    'wpjobportal_control' => $wpjobportal_control
			));
		?>
	</div>
	<?php
		WPJOBPORTALincluder::getTemplate('job/views/frontend/controls',array(
		    'wpjobportal_job' => $wpjobportal_job,
		    'wpjobportal_control' => $wpjobportal_control
		)); 
 	?>
</div>

