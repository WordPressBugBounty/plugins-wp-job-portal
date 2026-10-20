<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param job      job object - optional
 */
/*
 * A card with no job is nothing to draw.
 *
 * This used to read `if (!isset($wpjobportal_job->id)) { $wpjobportal_job->id = ''; }`,
 * which on PHP 8 is a fatal when the variable is null rather than an object:
 * assigning a property on null stopped being a silent auto-vivification and
 * became an Error. Any caller that reaches this template without a job - or
 * that passes one under a different name, which is how the Elegant Design
 * add-on's own callers do it - took the whole page down with it.
 */
if (!is_object($wpjobportal_job)) {
	return;
}
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
	        $wpjobportal_featuredexpiry = date_i18n('Y-m-d', strtotime((string) $wpjobportal_job->endfeatureddate));
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
		/*
		 * The description and the tag list used to exist only in the Elegant
		 * Design add-on's copy of this card, which is why that add-on had to
		 * fork the card at all - and forking the card forked every rule in it.
		 * Both are Core's now, so one card serves both looks and the difference
		 * between them is a stylesheet.
		 *
		 * The description is behind a setting because turning it on is a
		 * visible change to a site that has never shown it. Default off: a
		 * Core site looks exactly as it did.
		 */
		if (wpjobportal::$_config->getConfigurationByConfigName('show_job_description_in_list') == 1) {
			WPJOBPORTALincluder::getTemplate('job/views/frontend/description',array(
			    'wpjobportal_job' => $wpjobportal_job
			));
		}

		/*
		 * Tags, on the newest-jobs listing only, and only when the Tags feature
		 * is present and the administrator has the tag links switched on. Those
		 * three conditions were being evaluated inside the add-on, where Core
		 * could not see them - so a site that turned tag links off still saw
		 * them on an Elegant Design job card.
		 */
		if (isset($wpjobportal_control) && $wpjobportal_control == 'newestjobs'
			&& !empty($wpjobportal_job->jobtags)
			&& in_array('tag', wpjobportal::$_active_addons)
		) {
			$wpjobportal_tag_links = WPJOBPORTALincluder::getJSModel('job')->checkLinks('tags');
			if (isset($wpjobportal_tag_links[0]) && $wpjobportal_tag_links[0] == 1) {
				$wpjobportal_tags = array_filter(array_map('trim', explode(',', $wpjobportal_job->jobtags)));
				if ($wpjobportal_tags) {
					echo '<div class="wpjp-job-tags-list">';
					foreach ($wpjobportal_tags as $wpjobportal_tag) {
						echo '<a href="#" class="wpjp-clickable-tag" data-tag="' . esc_attr($wpjobportal_tag) . '">#' . esc_html($wpjobportal_tag) . '</a>';
					}
					echo '</div>';
				}
			}
		}

		WPJOBPORTALincluder::getTemplate('job/views/frontend/controls',array(
		    'wpjobportal_job' => $wpjobportal_job,
		    'wpjobportal_control' => $wpjobportal_control
		)); 
		if (isset($wpjobportal_control) && $wpjobportal_control === 'resumetitle') {
			do_action('wpjobportal_my_applied_job_extensions', $wpjobportal_job);
		}
 	?>
</div>
