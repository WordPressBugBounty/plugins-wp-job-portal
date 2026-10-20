<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>

<!-- Main Wrapper -->
<div id="wpjobportaladmin-wrapper">
    <!-- Left Menu -->
    <div id="wpjobportaladmin-leftmenu">
        <?php WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>

    <div id="wpjobportaladmin-data">
        <?php
        $status = array(
            (object) array('id' => 0,  'text' => __('Pending', 'wp-job-portal')),
            (object) array('id' => 1,  'text' => __('Approved', 'wp-job-portal')),
            (object) array('id' => -1, 'text' => __('Rejected', 'wp-job-portal')),
            (object) array('id' => 3,  'text' => __('Pending Payment', 'wp-job-portal'))
        );
        ?>

        <script>
            jQuery(document).ready(function ($) {
                $.validate();

                // Token Input: Cities
                var multicities = <?php echo isset(wpjobportal::$_data['multi_lists']) ? wp_json_encode(json_decode(wpjobportal::$_data['multi_lists'])) : "''"; ?>;
                getTokenInput(multicities);

                // Token Input: Tags (Addon Check)
                <?php if(in_array('tag', wpjobportal::$_active_addons)): ?>
                getTokenInputtags();
                <?php endif; ?>
            });

            var ajaxurl = "<?php echo esc_url_raw(admin_url('admin-ajax.php')); ?>";

            // --- City Token Input Function ---
            function getTokenInput(multicities) {
                var cityArray = '<?php echo esc_url_raw(admin_url("admin.php?page=wpjobportal_city&action=wpjobportaltask&task=getaddressdatabycityname")); ?>';
                var city = jQuery("#cityforedit").val();

                var tokenConfig = {
                    theme: "wpjobportal",
                    preventDuplicates: true,
                    hintText: "<?php echo esc_html__('Type In A Search Term', 'wp-job-portal'); ?>",
                    noResultsText: "<?php echo esc_html__('No Results', 'wp-job-portal'); ?>",
                    searchingText: "<?php echo esc_html__('Searching', 'wp-job-portal'); ?>",
                    <?php $wpjobportal_newtyped_cities = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('newtyped_cities');
                    if ($wpjobportal_newtyped_cities == 1) { ?>
                    onResult: function (item) {
                        if (jQuery.isEmptyObject(item)) {
                            return [{id: 0, name: jQuery("tester").text()}];
                        } else {
                            item.unshift({id: 0, name: jQuery("tester").text()});
                            return item;
                        }
                    },
                    onAdd: function (item) {
                        if (item.id > 0) return;

                        if (item.name.search(",") == -1) {
                            alert("<?php echo esc_html__('Location Format Is Not Correct. Please Enter City In This Format: City Name, Country Name OR City Name, State Name, Country Name', 'wp-job-portal'); ?>");
                            jQuery("#city").tokenInput("remove", item);
                            return false;
                        } else {
                            jQuery.post(ajaxurl, {
                                action: 'wpjobportal_ajax',
                                wpjobportalme: 'city',
                                task: 'savetokeninputcity',
                                citydata: jQuery("tester").text(),
                                '_wpnonce': '<?php echo esc_attr(wp_create_nonce("save-token-input-city")); ?>'
                            }, function (data) {
                                if (data) {
                                    try {
                                        var value = jQuery.parseJSON(data);
                                        jQuery('#city').tokenInput('remove', item);
                                        jQuery('#city').tokenInput('add', {id: value.id, name: value.name});
                                    } catch (err) {
                                        jQuery("#city").tokenInput("remove", item);
                                        alert(data);
                                    }
                                }
                            });
                        }
                    }
                    <?php } ?>
                };

                if (city != "") {
                    tokenConfig.prePopulate = multicities;
                }

                jQuery("#city").tokenInput(cityArray, tokenConfig);
            }

            // --- Tags Token Input Function (Addon) ---
            <?php if(in_array('tag', wpjobportal::$_active_addons)): ?>
            function getTokenInputtags() {
                var tagarray = '<?php echo esc_url_raw(admin_url("admin.php?page=wpjobportal_tag&tagfor=1&action=wpjobportaltask&task=gettagsbytagname")); ?>';
                <?php
                $wpjobportal_pre_populate = '';
                if (isset(wpjobportal::$_data[0]->tags) && !empty(wpjobportal::$_data[0]->tags)) {
                    $wpjobportal_pre_populate = json_decode(wpjobportal::$_common->makeFilterdOrEditedTagsToReturn(wpjobportal::$_data[0]->tags));
                }
                ?>
                jQuery('#tags').tokenInput(tagarray, {
                    theme: 'wpjobportal',
                    preventDuplicates: true,
                    tokenLimit: 5,
                    <?php if (!empty($wpjobportal_pre_populate)) { echo 'prePopulate: ' . wp_json_encode($wpjobportal_pre_populate) . ','; } ?>
                    hintText: "<?php echo esc_html__('Type In A Search Term', 'wp-job-portal'); ?>",
                    noResultsText: "<?php echo esc_html__('No Results', 'wp-job-portal'); ?>",
                    searchingText: "<?php echo esc_html__('Searching', 'wp-job-portal'); ?>"
                });
            }
            <?php endif; ?>
        </script>

        <!-- Top Bar -->
        <div id="wpjobportal-wrapper-top">
            <div id="wpjobportal-wrapper-top-left">
                <div id="wpjobportal-breadcrumbs">
                    <ul>
                        <li>
                            <a href="<?php echo esc_url_raw(admin_url('admin.php?page=wpjobportal')); ?>" title="<?php echo esc_attr__('Dashboard', 'wp-job-portal'); ?>">
                                <?php echo esc_html__('Dashboard', 'wp-job-portal'); ?>
                            </a>
                        </li>
                        <li><?php echo esc_html__('Add New Job Alert', 'wp-job-portal'); ?></li>
                    </ul>
                </div>
            </div>
            <div id="wpjobportal-wrapper-top-right">
                <div id="wpjobportal-config-btn">
                    <a href="admin.php?page=wpjobportal_configuration" title="<?php echo esc_attr__('Configuration', 'wp-job-portal'); ?>">
                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/config.png" alt="<?php echo esc_attr__('Configuration', 'wp-job-portal'); ?>">
                    </a>
                </div>
                <div id="wpjobportal-help-btn" class="wpjobportal-help-btn">
                    <a href="admin.php?page=wpjobportal&wpjobportallt=help" title="<?php echo esc_attr__('Help', 'wp-job-portal'); ?>">
                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/help.png" alt="<?php echo esc_attr__('Help', 'wp-job-portal'); ?>">
                    </a>
                </div>
                <div id="wpjobportal-vers-txt">
                    <?php echo esc_html__('Version', 'wp-job-portal') . ': '; ?>
                    <span class="wpjobportal-ver"><?php echo esc_html(WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue('versioncode')); ?></span>
                </div>
            </div>
        </div>

        <!-- Page Header -->
        <div id="wpjobportal-head">
            <h1 class="wpjobportal-head-text">
                <?php if (isset(wpjobportal::$_data[0])): ?>
                    <?php echo esc_html__('Edit', 'wp-job-portal') . '&nbsp;' . esc_html__('Job Alert', 'wp-job-portal'); ?>
                <?php else: ?>
                    <?php echo esc_html__('Add', 'wp-job-portal') . '&nbsp;' . esc_html__('Job Alert', 'wp-job-portal'); ?>
                <?php endif; ?>
            </h1>
        </div>

        <!-- Page Content -->
        <div id="wpjobportal-admin-wrapper">
            <form id="wpjobportal-form" class="wpjobportal-form" method="post" action="<?php echo esc_url_raw(admin_url('admin.php?page=wpjobportal_jobalert&task=savejobalert')); ?>">

                <?php
                // --- DYNAMIC FORM FIELDS INTEGRATION START ---
                $wpjobportal_formfields = WPJOBPORTALincluder::getTemplate('jobalert/form-fields', array(
                    'wpjobportal_jobalert' => isset(wpjobportal::$_data[0]) ? wpjobportal::$_data[0] : null,
                    'module_name'          => 'jobalert'
                ));

                foreach ($wpjobportal_formfields as $wpjobportal_field_data) {
                    $wpjobportal_field   = $wpjobportal_field_data['wpjobportal_field'];
                    $wpjobportal_content = $wpjobportal_field_data['wpjobportal_content'];

                    if (empty($wpjobportal_content)) continue;

                    // Normalize frontend classes to admin classes to prevent CSS breaks
                    $wpjobportal_content = str_replace('wjportal-form-', 'wpjobportal-form-', $wpjobportal_content);

                    // Check if field is required based on validation rules
                    $wpjobportal_is_required = (isset($wpjobportal_field->validation) && strpos($wpjobportal_field->validation, 'required') !== false);
                    ?>
                    <div class="wpjobportal-form-wrapper">
                        <div class="wpjobportal-form-title">
                            <?php echo esc_html($wpjobportal_field->fieldtitle); ?>
                            <?php if ($wpjobportal_is_required): ?><span style="color: red;">*</span><?php endif; ?>
                        </div>
                        <div class="wpjobportal-form-value">
                            <?php echo wp_kses($wpjobportal_content, WPJOBPORTAL_ALLOWED_TAGS); ?>
                        </div>
                    </div>
                    <?php
                }
                // --- DYNAMIC FORM FIELDS INTEGRATION END ---
                ?>

                <!-- Preserve cityidforedit for tokenizer/backend sync -->
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('cityidforedit', isset(wpjobportal::$_data[0]->cityidforedit) ? wpjobportal::$_data[0]->cityidforedit : ''), WPJOBPORTAL_ALLOWED_TAGS); ?>

                <!-- Admin Only: Status Field -->
                <div class="wpjobportal-form-wrapper">
                    <div class="wpjobportal-form-title">
                        <?php echo esc_html__('Status', 'wp-job-portal'); ?>
                    </div>
                    <div class="wpjobportal-form-value">
                        <?php echo wp_kses(WPJOBPORTALformfield::select('status', $status, isset(wpjobportal::$_data[0]->status) ? wpjobportal::$_data[0]->status : '', __('Select Status', 'wp-job-portal'), array('class' => 'inputbox wpjobportal-form-select-field')), WPJOBPORTAL_ALLOWED_TAGS); ?>
                    </div>
                </div>

                <!-- Hidden Fields -->
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('id', isset(wpjobportal::$_data[0]->id) ? wpjobportal::$_data[0]->id : ''), WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('created', isset(wpjobportal::$_data[0]->created) ? wpjobportal::$_data[0]->created : ''), WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('uid', isset(wpjobportal::$_data[0]->uid) ? wpjobportal::$_data[0]->uid : ''), WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('action', 'jobalert_savejobalert'), WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('form_request', 'wpjobportal'), WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('_wpnonce', wp_create_nonce('wpjobportal_job_alert_nonce')), WPJOBPORTAL_ALLOWED_TAGS); ?>

                <!-- Form Buttons -->
                <div class="wpjobportal-form-button">
                    <a id="form-cancel-button" class="wpjobportal-form-cancel-btn" href="<?php echo esc_url_raw(admin_url('admin.php?page=wpjobportal_jobalert')); ?>" title="<?php echo esc_attr__('Cancel', 'wp-job-portal'); ?>">
                        <?php echo esc_html__('Cancel', 'wp-job-portal'); ?>
                    </a>
                    <?php echo wp_kses(WPJOBPORTALformfield::submitbutton('save', __('Save', 'wp-job-portal') . ' ' . __('Job Alert', 'wp-job-portal'), array('class' => 'button wpjobportal-form-save-btn')), WPJOBPORTAL_ALLOWED_TAGS); ?>
                </div>
            </form>
        </div>
    </div>
</div>