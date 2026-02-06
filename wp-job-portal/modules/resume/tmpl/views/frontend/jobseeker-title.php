<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param job      job object - optional
*/
$wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
?>
		<span class="my-resume-modified-date" >
		    <span class="my-resume-modified-date-title" >
		        <?php echo esc_html(__('Modified Date','wp-job-portal')); ?>
		    </span>
		    <span class="my-resume-modified-date-value" >
		        <?php echo esc_html(date_i18n($wpjobportal_dateformat,strtotime($wpjobportal_myresume->last_modified))); ?>
		    </span>
		</span>
	</div>
	<div class="data-bigupper">
	<div class="big-upper-upper">
	<div class="headingtext item-title">
	<a href="<?php echo esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'viewresume', 'wpjobportalid'=>$wpjobportal_myresume->id, 'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid()))); ?>">
	    <span class="title"><?php echo esc_html($wpjobportal_myresume->first_name) . " " . esc_html($wpjobportal_myresume->last_name); ?></span>
	</a>
	</div>
	<span class="buttonu"><?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_myresume->jobtypetitle)); ?></span><span class="datecreated"><?php echo esc_html(__('Created', 'wp-job-portal')) . ': ' . esc_html(date_i18n($wpjobportal_dateformat, strtotime($wpjobportal_myresume->created))); ?></span>
	</div>
	<div class="big-upper-lower listing-fields">
	<div class="myresume-list-bottom-left">
	<span class="lower-upper-title">(
	        <?php echo esc_html($wpjobportal_myresume->application_title); ?>
	    </a>)
	</span>
	<div class="custom-field-wrapper">
	    <span class="js-bold"><?php echo esc_html(wpjobportal::wpjobportal_getVariableValue(wpjobportal::$wpjobportal_data['fields']['email_address'])) . ': '; ?></span>
	    <span class="get-text"><?php echo esc_html($wpjobportal_myresume->email_address); ?></span>
	</div>
	<div class="custom-field-wrapper">
	    <span class="js-bold">
	    <?php
	        if(!isset(wpjobportal::$wpjobportal_data['fields']['desired_salary'])){
	            wpjobportal::$wpjobportal_data['fields']['desired_salary'] = wpjobportal::$_wpjpfieldordering->getFieldTitleByFieldAndFieldfor('desired_salary',3);
	        }
	        echo esc_html(wpjobportal::wpjobportal_getVariableValue(wpjobportal::$wpjobportal_data['fields']['desired_salary'])) . ': '; ?>
	    </span>
	    <span class="get-text"><?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_myresume->salaryfixed)); ?></span>
	</div>
	<div class="custom-field-wrapper">
	    <span class="js-bold"><?php echo esc_html(wpjobportal::wpjobportal_getVariableValue(wpjobportal::$wpjobportal_data['fields']['job_category'])) . ': '; ?></span>
	    <span class="get-text"><?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_myresume->cat_title)); ?></span>
	</div>
	<div class="custom-field-wrapper">
	    <span class="js-bold">
	    <?php
	        if(!isset(wpjobportal::$wpjobportal_data['fields']['total_experience'])){
	            wpjobportal::$wpjobportal_data['fields']['total_experience'] = wpjobportal::$_wpjpfieldordering->getFieldTitleByFieldAndFieldfor('total_experience',3);
	        }
	        echo esc_html(wpjobportal::wpjobportal_getVariableValue(wpjobportal::$wpjobportal_data['fields']['total_experience'])) . ': '; ?></span>
	    <span class="get-text"><?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_myresume->total_experience)); ?></span>
	</div>
	<?php
	// custom fields
	// if(in_array('customfield', wpjobportal::$_active_addons)){
		$wpjobportal_customfields = apply_filters('wpjobportal_addons_get_custom_field',false,3,1,1);
		foreach ($wpjobportal_customfields as $wpjobportal_field) {
		    $wpjobportal_showCustom = apply_filters('wpjobportal_addons_show_customfields_params',false,$wpjobportal_field,9,$wpjobportal_myresume->params);
		    echo esc_attr($wpjobportal_showCustom);
		}
	// }
	?>
</div>
