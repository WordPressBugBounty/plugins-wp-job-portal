<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
// custom field date picker was not working without this code
wp_enqueue_script('jquery-ui-datepicker');
$wpjobportal_protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
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
<style>
.ui-datepicker{
    float: left;
}
</style>
<?php
    wp_register_script( 'wpjobportal-inline-handle', '' );
    wp_enqueue_script( 'wpjobportal-inline-handle' );

    $wpjobportal_inline_js_script = "
        jQuery(document).ready(function ($) {
            jQuery('.custom_date').datepicker({dateFormat: '". $wpjobportal_js_scriptdateformat ."'});
            //Token Input
            ";
            $wpjobportal_multicities = "var multicities = '';";
            if(isset(wpjobportal::$_data['filter']['city'])) 
                if(!(wpjobportal::$_data['filter']['city'] == "[]")) 
                    $wpjobportal_multicities = "var multicities = ". wpjobportal::$_data['filter']['city'].";";
            
            $wpjobportal_inline_js_script .= $wpjobportal_multicities;
            $wpjobportal_inline_js_script .= "
            getTokenInput(multicities);
             jQuery('a.sort-icon').click(function (e) {
                e.preventDefault();
                changeSortBy();
            });

        });
        //Token in put
        function getTokenInput(multicities) {
            var cityArray = '". esc_url_raw(admin_url("admin.php?page=wpjobportal_city&action=wpjobportaltask&task=getaddressdatabycityname"))."';
            jQuery('#city').tokenInput(cityArray, {
                theme: 'wpjobportal',
                preventDuplicates: true,
                prePopulate: multicities,
                hintText: \"". esc_html(__('Type In A Search Term', 'wp-job-portal')) ."\",
                noResultsText: \"". esc_html(__('No Results', 'wp-job-portal'))."\",
                searchingText: \"". esc_html(__('Searching', 'wp-job-portal'))."\"
            });
            jQuery('#wpjobportal-input-city').attr('placeholder', \"". esc_html(__('Type city', 'wp-job-portal')).' :'."\");
        }

        function closePopupJobManager() {
            closePopupForTemplate();
        }

        function closePopupJobHub() {
            closePopupForTemplate()
        }

        function closePopupForTemplate() {
            jQuery('div#'+common.theme_chk_prefix+'-popup').slideUp('slow');
            setTimeout(function () {
                jQuery('div#'+common.theme_chk_prefix+'-popup-background').hide();
                //jQuery('#'+common.theme_chk_prefix+'-modal-ar-title').html('');
                jQuery('div#'+common.theme_chk_prefix+'-popup').css('display', 'none');
                /*jQuery('span#popup_coverletter_title.coverletter').html('');
                jQuery('span#popup_coverletter_desc.coverletter').html('');*/
            }, 700);

        }

        function changeSortBy() {
            var value = jQuery('a.sort-icon').attr('data-sortby');
            var img = '';
            if (value == 1) {
                value = 2;
                img = jQuery('a.sort-icon').attr('data-image2');
            } else {
                img = jQuery('a.sort-icon').attr('data-image1');
                value = 1;
            }
            jQuery('img#sortingimage').attr('src', img);
            jQuery('input#sortby').val(value);
            jQuery('form#resume_form').submit();
        }
        function changeCombo() {
            jQuery('input#sorton').val(jQuery('select#sorting').val());
            changeSortBy();
        }
    ";
    wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
?>
