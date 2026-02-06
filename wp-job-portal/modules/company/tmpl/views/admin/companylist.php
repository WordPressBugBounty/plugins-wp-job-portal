<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
/**
* @param company Details 
*/
?>
<div id="company_<?php echo esc_attr($wpjobportal_company->id); ?>" class="wpjobportal-company-list">
	<div id="item-data">
		<div class="wpjobportal-company-list-top-wrp">
			<?php
			    /**
			    * @param Feature Company Label 
			    */
			    do_action('wpjobportal_addons_lable_admin_company',$wpjobportal_company);
			?>
	        <span id="selector_<?php echo esc_attr($wpjobportal_company->id); ?>" class="selector">
	        	<input type="checkbox" onclick="javascript:highlight(<?php echo esc_js($wpjobportal_company->id); ?>);" class="wpjobportal-cb" id="wpjobportal-cb" name="wpjobportal-cb[]" value="<?php echo esc_attr($wpjobportal_company->id); ?>" />
	        </span>
	    	<?php
				WPJOBPORTALincluder::getTemplate('company/views/admin/logo',array(
					'wpjobportal_company' => $wpjobportal_company,
					'wpjobportal_layout' => $wpjobportal_layout,
					'wpjobportal_wpdir' => $wpjobportal_wpdir
				));

				WPJOBPORTALincluder::getTemplate('company/views/admin/detail',array(
					'wpjobportal_company' => $wpjobportal_company
				));
			?>
		</div>
		<div class="wpjobportal-company-list-btm-wrp">
			<?php
				WPJOBPORTALincluder::getTemplate('company/views/admin/control',array(
					'wpjobportal_company' => $wpjobportal_company,
					'wpjobportal_control' => $wpjobportal_control,
					'wpjobportal_arr' => $wpjobportal_arr
				));
			?>
		</div>
	</div>
</div>
