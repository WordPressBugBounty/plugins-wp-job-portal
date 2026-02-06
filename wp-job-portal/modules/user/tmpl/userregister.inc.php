<?php
	if (!defined('ABSPATH'))
    die('Restricted Access');
    ////*******Script Design For Image****//////
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_style('jquery-ui-css', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/jquery-ui-smoothness.css');
	$wpjobportal_config = wpjobportal::$_configuration;
	if ($wpjobportal_config['date_format'] == 'm/d/Y' || $wpjobportal_config['date_format'] == 'd/m/y' || $wpjobportal_config['date_format'] == 'm/d/y' || $wpjobportal_config['date_format'] == 'd/m/Y') {
	    $wpjobportal_dash = '/';
	} else {
	    $wpjobportal_dash = '-';
	}
	$wpjobportal_dateformat = $wpjobportal_config['date_format'];
	$wpjobportal_firstdash = wpjobportalphplib::wpJP_strpos($wpjobportal_dateformat, $wpjobportal_dash, 0);
	$wpjobportal_firstvalue = wpjobportalphplib::wpJP_substr($wpjobportal_dateformat, 0, $wpjobportal_firstdash);
	$wpjobportal_firstdash = $wpjobportal_firstdash + 1;
	$wpjobportal_seconddash = wpjobportalphplib::wpJP_strpos($wpjobportal_dateformat, $wpjobportal_dash, $wpjobportal_firstdash);
	$wpjobportal_secondvalue = wpjobportalphplib::wpJP_substr($wpjobportal_dateformat, $wpjobportal_firstdash, $wpjobportal_seconddash - $wpjobportal_firstdash);
	$wpjobportal_seconddash = $wpjobportal_seconddash + 1;
	$wpjobportal_thirdvalue = wpjobportalphplib::wpJP_substr($wpjobportal_dateformat, $wpjobportal_seconddash, wpjobportalphplib::wpJP_strlen($wpjobportal_dateformat) - $wpjobportal_seconddash);
	$wpjobportal_js_dateformat = '%' . $wpjobportal_firstvalue . $wpjobportal_dash . '%' . $wpjobportal_secondvalue . $wpjobportal_dash . '%' . $wpjobportal_thirdvalue;
	$wpjobportal_js_scriptdateformat = $wpjobportal_firstvalue . $wpjobportal_dash . $wpjobportal_secondvalue . $wpjobportal_dash . $wpjobportal_thirdvalue;
	$wpjobportal_js_scriptdateformat = wpjobportalphplib::wpJP_str_replace('Y', 'yy', $wpjobportal_js_scriptdateformat);
?>
<style type="text/css">
.ui-datepicker{
    float: left;
}
</style>
<?php
    wp_register_script( 'wpjobportal-inline-handle', '' );
    wp_enqueue_script( 'wpjobportal-inline-handle' );

    $wpjobportal_inline_js_script = "
    	jQuery(document).ready(function ($) {
    	    $.validate();
    	});
		jQuery(document).ready(function() {
			addDatePicker();
	        jQuery('#photo').change(function () {
	        	var srcimage = jQuery('img.photo');
	        	readURL(this);
	        });
	     });
		function readURL(input) {
			if (input.files && input.files[0]) {
				var fileext = input.files[0].name.split('.').pop();
		        var filesize = (input.files[0].size / 1024);
		        var allowedsize = '".WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('image_file_size')."';
		        var allowedExt = \"". WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('image_file_type')."\";
		        allowedExt = allowedExt.split(',');
		        if (jQuery.inArray(fileext, allowedExt) != - 1){
		            if (allowedsize > filesize){
		                jQuery('.wjportal-form-image-wrp').show();
						jQuery('#rs_photo')[0].src = (window.URL ? URL : webkitURL).createObjectURL(input.files[0]);
		                jQuery('.wjportal-form-upload-btn-wrp-txt').show();
		                jQuery('.wjportal-form-upload-btn-wrp-txt').html(input.files[0].name);
		                jQuery('img#wjportal-form-delete-image').on('click',function(){
		                    jQuery('.wjportal-form-image-wrp').hide();
		                    jQuery('input#photo').val('').clone(true);
		                    jQuery('span.wjportal-form-upload-btn-wrp-txt').hide();
		                    jQuery('span.wjportal-form-upload-btn-wrp-txt').text('');
		                });
		                jQuery('#password,#confirmpassword').on('change', validatePassword);
		            } else{
		                jQuery('input#photo').replaceWith(jQuery('input#photo').val('').clone(true));
		                alert(\"". esc_html(__("File size is greater then allowed file size", 'wp-job-portal'))."\");
		            }
		        } else{
		            jQuery('input#photo').replaceWith(jQuery('input#photo').val('').clone(true));
		            alert(\"". esc_html(__("File ext. is mismatched", 'wp-job-portal'))."\");
		        }

			}
		}

		function validatePassword(){
			var pass = jQuery('#password').val();
			var cpass = jQuery('#confirmpassword').val();
			if(pass!='' && cpass!='' && pass!=cpass){
				jQuery('#password-validation-span').text(\"". esc_html(__("Passwords do not match",'wp-job-portal'))."\");
			}else{
				jQuery('#password-validation-span').text('');
			}
		}

		function addDatePicker(){
	        jQuery('.custom_date').datepicker({dateFormat: '". $wpjobportal_js_scriptdateformat."'});
	    }
	    function onSubmit(token) {
         document.getElementById('wpjobportal_registration_form').submit();
       }
    ";
    wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
?>
