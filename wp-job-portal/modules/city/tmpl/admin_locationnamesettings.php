<?php
if (!defined('ABSPATH'))
die('Restricted Access');
wp_enqueue_script( 'wpjobportal-res-tables', WPJOBPORTAL_PLUGIN_URL . 'includes/js/responsivetable.js', array(), wpjobportal_asset_version('includes/js/responsivetable.js'), false );
?>
<!-- main wrapper -->
<div id="wpjobportaladmin-wrapper" class="wjpl-page">
    <!-- left menu -->
	<div id="wpjobportaladmin-leftmenu">
        <?php  WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <?php
            $wpjobportal_msgkey = WPJOBPORTALincluder::getJSModel('city')->getMessagekey();
            WPJOBPORTALMessages::getLayoutMessage($wpjobportal_msgkey);
        ?>
        <!-- page content -->
        <?php WPJOBPORTAL_Admin_Form_UI::header(__('Location name settings', 'wp-job-portal'), __('Show city names in English or in their own language.', 'wp-job-portal'), __('City names', 'wp-job-portal'), 'wpjobportal-list-form'); ?>
        <?php WPJOBPORTAL_Admin_List_UI::section_tabs(); ?>
        <div id="wpjobportal-admin-wrapper">
            <form id="wpjobportal-list-form" class="wpjobportal-form wjpl-form wjpl-form-simple" enctype="multipart/form-data" method="post" action="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_city')); ?>"><?php WPJOBPORTAL_Admin_Form_UI::simple_open(); ?>
                <div class="wpjobportal-city-data-form-wrap wpjobportal-city-data-form-wrap-left">
                    <?php
                    $wpjobportal_name_preference = get_option("wpjobportal_location_name_preference");
                     ?>
                    <div class="wpjobportal-form-wrapper">
                        <div class="wpjobportal-form-title">
                            <?php echo esc_html__("City Name Preferences", "wp-job-portal"); ?>
                        </div>
                        <div class="wpjobportal-form-value">
                            <span class="wpjobportal-form-radio-field wpjobportal-form-radio-field-first">
                                <input type="radio" name="name_preference" id="name_preference1"  <?php if($wpjobportal_name_preference != 2) {?> checked="checked" <?php } ?> value="1" />
                                <label for="name_preference1"><?php echo esc_html__("International Name", "wp-job-portal"); ?></label>
                            </span>
                            <span class="wpjobportal-form-radio-field wpjobportal-form-radio-field-second">
                                <input type="radio" name="name_preference" id="name_preference2" <?php if($wpjobportal_name_preference == 2) {?> checked="checked" <?php } ?> value="2" />
                                <label for="name_preference2"><?php echo esc_html__("Native Name", "wp-job-portal"); ?></label>
                            </span>
                            <span id="loadaddressdata_city_name_msg">
                                <?php echo esc_html__("To enhance the user experience, do you prefer displaying city names in English or in their native language.", "wp-job-portal"); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="wpjobportal-form-button">
                    <input class="button wpjobportal-form-save-btn" type="submit" name="submit_app" id="submitbutton" value="<?php echo esc_attr__("Save Settings", "wp-job-portal"); ?>" onclick="return validate_form(document.adminForm)" />
                </div>
                <div class="wpjobportal-city-data-sample" >
                    <div class="wpjobportal-city-data-sample-heading" >
                        <?php echo esc_html__("Sample Data", "wp-job-portal"); ?>
                    </div>
                    <div class="wpjobportal-city-data-table-wrap csl-frst-wdth" >
                        <table class="wpjobportal-city-data-sample-data" >
                            <thead>
                                <tr>
                                    <th><?php echo esc_html__("Name", "wp-job-portal"); ?> *</th>
                                    <th><?php echo esc_html__("International Name", "wp-job-portal"); ?></th>
                                    <th><?php echo esc_html__("Native Name", "wp-job-portal"); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach (wpjobportal::$_data[0] as $wpjobportal_single_city) {
                                ?>
                                    <tr>
                                        <td class="wpjobportal-location-setting-name"><?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_single_city->name));?></td>
                                        <td class="wpjobportal-location-setting-localname" ><?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_single_city->internationalname));?></td>
                                        <td  class="wpjobportal-location-setting-internationalname"><?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_single_city->localname));?></td>

                                    </tr>
                                <?php }?>
                            </tbody>
                        </table>
                        <span class="wpjobportal-set-name-disc" >*<?php echo esc_html__("Currently Set", "wp-job-portal"); ?></span>
                    </div>
                                        <div class="wpjobportal-city-data-table-wrap csl-scnd-wdth" >

                    </div>
                </div>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('form_request', 'wpjobportal'), WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('task', 'savecitynamesettings'), WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('action', 'city_savecitynamesettings'), WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('_wpnonce', wp_create_nonce('wpjobportal_address_data_nonce')), WPJOBPORTAL_ALLOWED_TAGS); ?>
            <?php WPJOBPORTAL_Admin_Form_UI::simple_close(); ?></form>

        </div>
    </div>
</div>

<?php
wp_register_script( 'wpjobportal-inline-handle', '', array(), WPJOBPORTAL_VERSION, false );
wp_enqueue_script( 'wpjobportal-inline-handle' );

$wpjobportal_inline_js_script = "
    jQuery(document).ready(function () {
        jQuery('input[name=\"name_preference\"]').change(function () {
            var selectedValue = jQuery('input[name=\"name_preference\"]:checked').val();

            // Loop through each row that contains the name fields
            jQuery('tr').each(function () {

                // Get the international and native names for each row
                var internationalName = jQuery(this).find('.wpjobportal-location-setting-localname').text();
                var nativeName = jQuery(this).find('.wpjobportal-location-setting-internationalname').text();

                // Update the name based on the selected radio button
                if (selectedValue == '1') {
                    // If 'International Name' is selected, update the td with international name
                    jQuery(this).find('.wpjobportal-location-setting-name').text(internationalName);
                } else if (selectedValue == '2') {
                    // If 'Native Name' is selected, update the td with native name
                    jQuery(this).find('.wpjobportal-location-setting-name').text(nativeName);
                }
            });
        });
    });
    ";
wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
?>

