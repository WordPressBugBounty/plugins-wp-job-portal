<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
?>
<div id="wpjobportaladmin-wrapper" class="wpjobportal-admin-add-on-page-wrapper">
    <div id="wpjobportaladmin-leftmenu">
        <?php WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <div id="wpjobportal-wrapper-top">
            <div id="wpjobportal-wrapper-top-left">
                <div id="wpjobportal-breadcrumbs">
                    <ul>
                        <li>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal')); ?>"
                               title="<?php echo esc_attr(__('Dashboard','wp-job-portal')); ?>">
                                <?php echo esc_html(__('Dashboard','wp-job-portal')); ?>
                            </a>
                        </li>
                        <li><?php echo esc_html(__('Update Key','wp-job-portal')); ?></li>
                    </ul>
                </div>
            </div>
            <div id="wpjobportal-wrapper-top-right">
                <div id="wpjobportal-config-btn">
                    <a href="<?php echo esc_url(admin_url("admin.php?page=wpjobportal_configuration")); ?>"
                       title="<?php echo esc_attr(__('Configuration','wp-job-portal')); ?>">
                        <img alt="<?php echo esc_attr(__('Configuration','wp-job-portal')); ?>"
                             src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/config.png" />
                    </a>
                </div>
                <div id="wpjobportal-help-btn" class="wpjobportal-help-btn">
                    <a href="<?php echo esc_url(admin_url("admin.php?page=wpjobportal&wpjobportallt=help")); ?>"
                       title="<?php echo esc_attr(__('Help','wp-job-portal')); ?>">
                        <img alt="<?php echo esc_attr(__('Help','wp-job-portal')); ?>"
                             src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/help.png" />
                    </a>
                </div>
                <div id="wpjobportal-vers-txt">
                    <?php echo esc_html(__('Version','wp-job-portal')).': '; ?>
                    <span class="wpjobportal-ver">
                        <?php echo esc_html(WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue('versioncode')); ?>
                    </span>
                </div>
            </div>
        </div>

        <div id="wpjobportal-head">
            <h1 class="wpjobportal-head-text"><?php echo esc_html(__("Update Key", 'wp-job-portal')); ?></h1>
        </div>

        <div id="wpjobportal-data-wrp" class="p0 bg-n bs-n">
            <div id="wpjobportal-admin-wrapper" >
                <form class="wpjobportal-update-key-form" id="wpjobportalfrom"
                      action="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_premiumplugin&task=wpjobportalupdatetransactionkey&action=wpjobportaltask'),"wpjobportal_premiumplugin_nonce")); ?>"
                      method="post">
                    <div class="wpjobportal-update-key-wrp">
                        <div class="wpjobportal-update-key-section">
                            <h2 class="wpjobportal-update-key-title">
                                <?php echo esc_html(__("WP Job Portal Activation Key", 'wp-job-portal')); ?>
                            </h2>
                            <input id="transactionkey" name="transactionkey" required type="text"
                                   placeholder="<?php echo esc_attr(__("XXXXX-XXXXX-XXXXX-XXXXX", 'wp-job-portal')); ?>"
                                   value="<?php echo isset( wpjobportal::$_data['token'] ) ? esc_attr( wpjobportal::$_data['token'] ) : ''; ?>">
                        </div>
                        <div class="wpjobportal-update-key-custom-errormsgwrp">
                            <?php
                                $wpjobportal_msgkey = WPJOBPORTALincluder::getJSModel('premiumplugin')->getMessagekey();
                                WPJOBPORTALMessages::getLayoutMessage($wpjobportal_msgkey);
                             ?>
                        </div>

                        <?php if (!empty(wpjobportal::$_data['extra_addons'])) { ?>
                            <div class="wpjobportal-update-key-errormsgwrp">
                                <img alt="<?php echo esc_attr(__("Info", 'wp-job-portal')); ?>"
                                     src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/icon.png" />
                                <?php echo esc_html(__("The highlighted addons are not included in your current license. Please adjust your selection accordingly.", 'wp-job-portal')); ?>
                            </div>
                        <?php } ?>

                        <div class="wpjobportal-update-key-slctall-addonswrp">
                            <span class="wpjobportal-update-key-slctall-addon-title">
                                <?php echo esc_html(__("Select Addons to Update with New Activation Key", 'wp-job-portal')); ?>
                            </span>
                            <div class="wpjobportal-update-key-slctall-addon-checkbox-wrp">
                                <input class="wpjobportal-update-key-checkbox" id="select-all" type="checkbox">
                                <?php echo esc_html(__("Select All Addons", 'wp-job-portal')); ?>
                            </div>
                        </div>

                        <?php
                        $wpjobportal_addon_array   = [];
                        $wpjobportal_all_plugins   = get_plugins();
                        $wpjobportal_extra_addons  = wpjobportal::$_data['extra_addons'];
                        $wpjobportal_allowed_addons= wpjobportal::$_data['allowed_addons'];

                        foreach ($wpjobportal_all_plugins as $wpjobportal_plugin_file => $wpjobportal_plugin_data) {
                            if (strpos($wpjobportal_plugin_file, 'wp-job-portal-') === 0) {
                                $wpjobportal_slug = dirname($wpjobportal_plugin_file);
                                $wpjobportal_addon_array[$wpjobportal_slug] = $wpjobportal_plugin_data;
                            }
                        }
                        ?>

                        <div class="wpjobportal-update-key-all-addons-wrp">
                            <?php
                            if (!empty($wpjobportal_addon_array)) {
                                $wpjobportal_addons = WPJOBPORTALincluder::getJSModel('wpjobportal')->getWPJPAddonsArray();
                                foreach ($wpjobportal_addon_array as $wpjobportal_key => $wpjobportal_value) {
                                    $wpjobportal_error_class = '';
                                    $wpjobportal_isChecked   = false;

                                    if (!empty($wpjobportal_extra_addons) && wpjobportalphplib::wpJP_strpos($wpjobportal_extra_addons, $wpjobportal_key) !== false) {
                                        $wpjobportal_error_class = 'wpjobportal-update-key-single-addon-red';
                                    }

                                    if (!empty($wpjobportal_allowed_addons) && wpjobportalphplib::wpJP_strpos($wpjobportal_allowed_addons, $wpjobportal_key) !== false) {
                                        $wpjobportal_isChecked = true;
                                    } ?>
                                    <div class="wpjobportal-update-key-single-addon <?php echo esc_attr($wpjobportal_error_class); ?>">
                                        <input id="addon-<?php echo esc_attr($wpjobportal_key); ?>"
                                               name="<?php echo esc_attr($wpjobportal_key); ?>"
                                               class="wpjobportal-update-key-checkbox"
                                               type="checkbox" <?php checked($wpjobportal_isChecked, true); ?>>
                                        <?php
                                        if (!empty($wpjobportal_addons[$wpjobportal_value['TextDomain']]['title'])) {
                                            echo esc_html($wpjobportal_addons[$wpjobportal_value['TextDomain']]['title']);
                                        } else {
                                            echo esc_html(wpjobportalphplib::wpJP_str_replace('WP Job Portal ', '', $wpjobportal_value['Name']));
                                        }
                                        ?>
                                    </div>
                                <?php }
                            } else { ?>
                                <div class="wpjobportal-update-key-no-addon-msg">
                                    <?php echo esc_html(__("No Addon Installed!", 'wp-job-portal')); ?>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="wpjobportal-update-key-infomsgwrp">
                            <img alt="<?php echo esc_attr(__("Info", 'wp-job-portal')); ?>"
                                 src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/info-icon.png" />
                            <?php echo esc_html(__("This will replace the old key with the new one.", 'wp-job-portal')); ?>
                        </div>
                        <div class="wpjobportal-update-key-updtebtn-wrp">
                            <button class="wpjobportal-update-key-updtebtn" type="submit">
                                <?php echo esc_html(__("Update Key", 'wp-job-portal')); ?>
                            </button>
                        </div>
                    </div>
                </form>
                <?php if (!empty(wpjobportal::$_data['unused_keys'])) { ?>
                    <div id="wpjobportal-data-wrp" class="p0 bg-n bs-n" style="margin-top: 25px;">
                        <form class="wpjobportal-update-key-form" action="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_premiumplugin&task=wpjobportal_remove_unused_keys&action=wpjobportaltask'),"delete-transaction-key")); ?>" method="post">
                            <div class="wpjobportal-update-key-wrp">
                                <div class="wpjobportal-update-key-slctall-addonswrp">
                                    <span class="wpjobportal-update-key-slctall-addon-title">
                                        <?php echo esc_html(__("Manage Unused Keys", 'wp-job-portal')); ?>
                                    </span>
                                </div>
                                <div id="delete-info" class="mb-4">
                                    <p class="text-gray-600">
                                        <?php echo esc_html(__("You have", 'wp-job-portal')); ?>
                                        <span id="unused-count" class="font-bold text-red-600">
                                            <?php echo esc_html(wpjobportal::$_data['unused_keys']); ?>
                                        </span>
                                        <?php echo esc_html(__("unused key(s) that can be safely removed.", 'wp-job-portal')); ?>
                                    </p>
                                </div>
                                <div class="wpjobportal-update-key-updtebtn-wrp">
                                    <button class="mb-4 wpjobportal-update-key-updtebtn" type="submit" style="margin-bottom: 0;">
                                        <?php echo esc_html(__("Delete All Unused Keys", 'wp-job-portal')); ?>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php } ?>
            </div>
        </div>


    </div>
</div>

<?php
wp_register_script('wpjobportal-inline-handle', '');
wp_enqueue_script('wpjobportal-inline-handle');

$wpjobportal_inline_js_script = "
jQuery(document).ready(function() {
    jQuery('#select-all').on('change', function() {
        var isChecked = jQuery(this).is(':checked');
        jQuery('.wpjobportal-update-key-checkbox').prop('checked', isChecked);
    });

    jQuery('.wpjobportal-update-key-checkbox').on('change', function() {
        var allChecked = jQuery('.wpjobportal-update-key-checkbox').length === jQuery('.wpjobportal-update-key-checkbox:checked').length;
        jQuery('#select-all').prop('checked', allChecked);
    });
});
";
wp_add_inline_script('wpjobportal-inline-handle', $wpjobportal_inline_js_script);
?>
