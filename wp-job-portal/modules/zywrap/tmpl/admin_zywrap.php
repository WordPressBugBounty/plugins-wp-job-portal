<?php

// global $wpdb;
//             $wpdb->query('SET FOREIGN_KEY_CHECKS = 0;');
//             $wpdb->query("TRUNCATE TABLE `" . $wpdb->prefix . "wj_portal_zywrap_wrappers`");
//             $wpdb->query("TRUNCATE TABLE `" . $wpdb->prefix . "wj_portal_zywrap_categories`");
//             $wpdb->query("TRUNCATE TABLE `" . $wpdb->prefix . "wj_portal_zywrap_languages`");
//             $wpdb->query("TRUNCATE TABLE `" . $wpdb->prefix . "wj_portal_zywrap_block_templates`");
//             $wpdb->query("TRUNCATE TABLE `" . $wpdb->prefix . "wj_portal_zywrap_ai_models`");
//             $wpdb->query('SET FOREIGN_KEY_CHECKS = 1;');
/*

            // Categories table
            $query = "CREATE TABLE IF NOT EXISTS `".wpjobportal::$_db->prefix."wj_portal_zywrap_categories` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `code` varchar(255) NOT NULL,
                `name` varchar(255) NOT NULL,
                `ordering` int(11) DEFAULT NULL,
                `status` tinyint(1) DEFAULT 1,
                UNIQUE KEY `code` (`code`),
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            wpjobportal::$_db->query($query);


            // Languages table
            $query = "CREATE TABLE IF NOT EXISTS `".wpjobportal::$_db->prefix."wj_portal_zywrap_languages` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `code` varchar(10) NOT NULL,
                `name` varchar(255) NOT NULL,
                `ordering` int(11) DEFAULT NULL,
                `status` tinyint(1) DEFAULT 1,
                UNIQUE KEY `code` (`code`),
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            wpjobportal::$_db->query($query);


            // AI Models table
            $query = "CREATE TABLE IF NOT EXISTS `".wpjobportal::$_db->prefix."wj_portal_zywrap_ai_models` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `code` varchar(255) NOT NULL,
                `name` varchar(255) NOT NULL,
                `provider_id` varchar(255) DEFAULT NULL,
                `ordering` int(11) DEFAULT NULL,
                `status` tinyint(1) DEFAULT 1,
                UNIQUE KEY `code` (`code`),
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            wpjobportal::$_db->query($query);


            // Wrappers table
            $query = "CREATE TABLE IF NOT EXISTS `".wpjobportal::$_db->prefix."wj_portal_zywrap_wrappers` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `code` varchar(255) NOT NULL,
                `name` varchar(255) NOT NULL,
                `description` text,
                `category_code` varchar(255) DEFAULT NULL,
                `featured` tinyint(1) DEFAULT NULL,
                `base` tinyint(1) DEFAULT NULL,
                `ordering` int(11) DEFAULT NULL,
                `status` tinyint(1) DEFAULT 1,
                UNIQUE KEY `code` (`code`),
                KEY `category_code` (`category_code`),
                PRIMARY KEY (`id`),
                CONSTRAINT `fk_jp_zywrap_wrappers_cat`
                    FOREIGN KEY (`category_code`)
                    REFERENCES `".wpjobportal::$_db->prefix."wj_portal_zywrap_categories` (`code`)
                    ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            wpjobportal::$_db->query($query);


            // Block templates table
            $query = "CREATE TABLE IF NOT EXISTS `".wpjobportal::$_db->prefix."wj_portal_zywrap_block_templates` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `type` varchar(50) NOT NULL,
                `code` varchar(255) NOT NULL,
                `name` varchar(255) NOT NULL,
                `status` tinyint(1) DEFAULT 1,
                UNIQUE KEY `type_code` (`type`,`code`),
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            wpjobportal::$_db->query($query);


            // Logs table
            $query = "CREATE TABLE IF NOT EXISTS `".wpjobportal::$_db->prefix."wj_portal_zywrap_logs` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `timestamp` datetime NOT NULL,
                `user_id` bigint(20) DEFAULT NULL,
                `status` varchar(50) NOT NULL,
                `action` varchar(100) NOT NULL,
                `wrapper_code` varchar(255) DEFAULT NULL,
                `model_code` varchar(255) DEFAULT NULL,
                `http_code` int(11) DEFAULT NULL,
                `error_message` text DEFAULT NULL,
                `prompt_tokens` int(11) DEFAULT NULL,
                `completion_tokens` int(11) DEFAULT NULL,
                `total_tokens` int(11) DEFAULT NULL,
                `token_data` text DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `user_id` (`user_id`),
                KEY `action_status` (`action`, `status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            wpjobportal::$_db->query($query);

*/
if (!defined('ABSPATH'))
    die('Restricted Access');

// This loads the "Settings Saved!" message
$wpjobportal_msgkey = WPJOBPORTALincluder::getJSModel('wpjobportal')->getMessagekey();
WPJOBPORTALMessages::getLayoutMessage($wpjobportal_msgkey);

// Get the saved key from WordPress options
$wpjobportal_saved_key = get_option('wpjobportal_zywrap_api_key', '');
?>

<div id="wpjobportaladmin-wrapper">
    <div id="wpjobportaladmin-leftmenu">
        <?php  WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>

    <div id="wpjobportaladmin-data">
        <div id="wpjobportal-wrapper-top">
            <div id="wpjobportal-wrapper-top-left">
                <div id="wpjobportal-breadcrumbs">
                    <ul>
                        <li>
                            <a href="<?php echo esc_url_raw(admin_url('admin.php?page=wpjobportal')); ?>" title="<?php echo esc_html(__('dashboard','wp-job-portal')); ?>">
                                <?php echo esc_html(__('Dashboard','wp-job-portal')); ?>
                            </a>
                        </li>
                        <li><?php echo esc_html(__('Zywrap Settings','wp-job-portal')); ?></li>
                    </ul>
                </div>
            </div>
            <div id="wpjobportal-wrapper-top-right">
                <div id="wpjobportal-config-btn">
                    <a href="admin.php?page=wpjobportal_configuration" title="<?php echo esc_html(__('configuration','wp-job-portal')); ?>">
                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/config.png">
                   </a>
                </div>
                <div id="wpjobportal-help-btn" class="wpjobportal-help-btn">
                    <a href="admin.php?page=wpjobportal&wpjobportallt=help" title="<?php echo esc_html(__('help','wp-job-portal')); ?>">
                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/help.png">
                   </a>
                </div>
                <div id="wpjobportal-vers-txt">
                    <?php echo esc_html(__('Version','wp-job-portal')).': '; ?>
                    <span class="wpjobportal-ver"><?php echo esc_html(WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue('versioncode')); ?></span>
                </div>
            </div>
        </div>

        <?php WPJOBPORTALincluder::getTemplate('templates/admin/pagetitle',array('wpjobportal_module' => 'wpjobportal' , 'wpjobportal_layouts' => 'zywrap')); ?>

        <div id="wpjobportal-admin-wrapper" class="wpjobportal-admin-config-wrapper">

            <div id="wpjobportal-head">
                <h1 class="wpjobportal-head-text">
                    <?php echo esc_html(__('Zywrap Settings', 'wp-job-portal')); ?>
                </h1>
            </div>

            <form id="wpjobportal-form" class="wpjobportal-configurations" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=wpjobportal_zywrap&task=save_zywrap_settings"), "save-zywrap-settings")); ?>">

                <div class="wpjobportal-zwrap-settings-section-wrap">
                    <div class="wpjobportal-config-row-wrp">
                        <div class="wpjobportal-config-row wpjobportal-config-row-zywrap-notice">
                            <div class="wpjobportal-config-zywrap-container">
                                <h3 class="wpjobportal-config-zywrap-title">
                                    <span class="dashicons dashicons-info wpjobportal-config-zywrap-icon"></span>
                                    <?php echo __( 'How to get your API Key', 'wp-job-portal' ); ?>
                                </h3>

                                <ol class="wpjobportal-config-zywrap-list">
                                    <li>
                                        <strong><?php echo __( 'Sign Up Free', 'wp-job-portal' ); ?>:</strong>
                                        <a href="https://zywrap.com/register?utm_source=wordpress-plugin&utm_medium=wp-job-portal&utm_campaign=onboarding" target="_blank" class="wpjobportal-config-zywrap-link">
                                            <?php echo __( 'Zywrap.com', 'wp-job-portal' ); ?>
                                            <span class="dashicons dashicons-external"></span>
                                        </a>
                                    </li>
                                    <li>
                                        <strong><?php echo __( 'Get Free Credits', 'wp-job-portal' ); ?>:</strong>
                                        <?php echo __( 'You will receive', 'wp-job-portal' ); ?>
                                        <span class="wpjobportal-config-zywrap-highlight">
                                            <?php echo __( '10,000 Free Credits', 'wp-job-portal' ); ?>
                                        </span>
                                        <?php echo __( 'instantly upon registration.', 'wp-job-portal' ); ?>
                                    </li>
                                    <li>
                                        <strong><?php echo __( 'Create Key', 'wp-job-portal' ); ?>:</strong>
                                        <?php echo __( 'Go to the API Keys section in your dashboard to generate your secret key.', 'wp-job-portal' ); ?>
                                    </li>
                                </ol>
                            </div>

                            <p class="wpjobportal-config-zywrap-description description">
                                <?php echo __( 'Enter your Zywrap.com API Key to connect your account.', 'wp-job-portal' ); ?>
                            </p>
                        </div>
                        <div class="wpjobportal-config-row wpjobportal-config-row-cstm-treatment">
                            <div class="wpjobportal-config-title">
                                <?php echo esc_html(__('Zywrap API Key', 'wp-job-portal')); ?>
                            </div>
                            <div class="wpjobportal-config-value">
                                <?php echo wp_kses(WPJOBPORTALformfield::text('wpjobportal_zywrap_api_key', $wpjobportal_saved_key, array('class' => 'inputbox')), WPJOBPORTAL_ALLOWED_TAGS); ?>
                                <?php
                                if($wpjobportal_saved_key == ''){
                                    echo esc_html(__("To get your API key, log in to", 'wp-job-portal'));
                                    echo ' <a href="https://zywrap.com" target="_blank">'.esc_html('zywrap.com').'</a> ';
                                    echo esc_html(__("and navigate to your Dashboard / API Keys.", 'wp-job-portal')); ?>
                                    <?php
                                }else{
                                    $api_key_status = get_option('wpjobportal_zywrap_api_status');
                                    if(!empty($api_key_status)){
                                        if($api_key_status['status'] == 'ok'){ ?>
                                            <div id="wpjobportal-zywrap-status-result">
                                                <div class="notice notice-success">
                                                    <p><strong><?php echo esc_html__($api_key_status['response'],'wp-job-portal') ?></strong></p>
                                                </div>
                                            </div>
                                            <?php
                                        }else{  ?>
                                            <div id="wpjobportal-zywrap-status-result">
                                                <div class="notice notice-warning">
                                                    <p><strong><?php echo esc_html__($api_key_status['response'],'wp-job-portal') ?></strong></p>
                                                </div>
                                            </div>
                                        <?php
                                            echo esc_html(__("Get a Valid API key, log in to", 'wp-job-portal'));
                                            echo ' <a href="https://zywrap.com" target="_blank">'.esc_html('zywrap.com').'</a> ';
                                            echo esc_html(__("and navigate to your Dashboard / API Keys.", 'wp-job-portal'));
                                        }
                                    }

                                }
                                ?>
                            </div>
                            <div class="wpjobportal-config-description">
                                <button title="<?php echo esc_html(__('Save Key', 'wp-job-portal')); ?>" type="submit" class="button wpjobportal-config-save-btn">
                                    <?php echo esc_html(__('Validate API Key', 'wp-job-portal')); ?>
                                </button>
                            </div>
                        </div>
                    </div>

                    <?php echo wp_kses(WPJOBPORTALformfield::hidden('action', 'zywrap_save_zywrap_settings'), WPJOBPORTAL_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(WPJOBPORTALformfield::hidden('form_request', 'wpjobportal'), WPJOBPORTAL_ALLOWED_TAGS); ?>

                </div>
                <?php
                /* if($wpjobportal_saved_key != ''){ ?>
                    <div class="wpjobportal-config-description wpjobportal-config-description-button wpjobportal-api-status">
                        <h3 class="wpjobportal-config-auto-download-steps-heading">
                            <?php echo esc_html(__("Check API Key Status", 'wp-job-portal')); ?>
                        </h3>
                        <div class="wpjobportal-config-auto-download-button-wrap" >
                            <button type="button" id="wpjobportal-check-zywrap-status" class="wpjobportal-table-act-btn wpjobportal-delete wpjobportal-config-download">
                                <?php echo esc_html(__("Check API Status", 'wp-job-portal')); ?>
                            </button>

                        </div>
                        <div id="wpjobportal-zywrap-status-result"></div>
                    </div>
                <?php } */ ?>
            </form>

            <?php
            $api_key_status = get_option('wpjobportal_zywrap_api_status');
            if(!empty($api_key_status) && $api_key_status['status'] == 'ok'){
                // Check if the API key is saved. If not, this section will be hidden.
                if (!empty($wpjobportal_saved_key)) :
                    $wpjobportal_data_version = get_option('wpjobportal_zywrap_version', '');
                    //$wpjobportal_data_version = 1;
                ?>

                <div class="wpjobportal-zwrap-settings-section-wrap">
                    <div class="wpjobportal-zwrap-settings-section">
                        <div class="wpjobportal-zwrap-settings-section-title">
                            <?php echo esc_html(__('Zywrap Data Sync', 'wp-job-portal')); ?>
                        </div>
                        <div class="wpjobportal-zwrap-settings-section-description">
                            <?php echo esc_html(__('Get the latest data (categories, wrappers, models and languages) from your Zywrap account.', 'wp-job-portal')); ?>
                        </div>
                    </div>

                    <div class="wpjobportal-admin-config-wrapper  wpjobportal-zwrap-settings-bottom-section">
                        <form id="wpjobportal-form-sync" class="wpjobportal-configurations" method="post" action="#">

                            <div class="wpjobportal-config-row-wrp">
                                <div class="wpjobportal-config-row ">
                                    <div class="wpjobportal-config-title">
                                        <?php echo esc_html(__('Sync Status', 'wp-job-portal')); ?> :
                                    </div>
                                    <div class="wpjobportal-config-value">
                                       <?php if (empty($wpjobportal_data_version)) : ?>
                                            <p><img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/no.png"><strong style="color:red;" ><?php echo esc_html(__('Not Synced', 'wp-job-portal')); ?></strong></p>
                                        <?php else : ?>
                                            <p ><img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/yes.png"><strong style="color:green;"  ><?php echo esc_html(__('Synced', 'wp-job-portal')); ?></strong><br>


                                            <?php
                                            $wpjobporatl_update_time = get_option('wpjobportal_zywrap_version_time');
                                            if(!empty($wpjobporatl_update_time)){
                                                echo esc_html(__('Last Sync', 'wp-job-portal')); ?>: <?php echo esc_html(human_time_diff(strtotime($wpjobporatl_update_time),strtotime(date_i18n("Y-m-d H:i:s"))).' '.esc_html(__("Ago",'wp-job-portal')));;
                                            }
                                        ?></p>

                                        <?php endif; ?>
                                    </div>
                                    <div class="wpjobportal-config-description">
                                        <?php echo esc_html(__("Your local database must be synced with the Zywrap catalog to use the Playground and Editor tools.", 'wp-job-portal')); ?>
                                    </div>
                                </div>

                                <div class="wpjobportal-config-row wpjobportal-config-description-bottom-wrap">
                                    <div class="wpjobportal-config-description wpjobportal-config-description-button wpjobportal-api-status">
                                        <?php if (empty($wpjobportal_data_version)) : ?>
                                            <button data-type="1" type="button" class="zywrap-full-import-btn button" style="margin-right: 10px;">
                                                <?php echo esc_html(__("Download & Full Import", 'wp-job-portal')); ?>
                                            </button>
                                        <?php else : ?>
                                            <button data-type="2" type="button" class="zywrap-delta-sync-btn button zywrap-full-import-btn" style="margin-right: 10px;">
                                                <?php echo esc_html(__("Get Updates", 'wp-job-portal')); ?>
                                            </button>
                                            <?php
                                            /*
                                            <button data-type="3"  type="button" class="zywrap-full-import-btn button">
                                                <?php echo esc_html(__("Erase and Download", 'wp-job-portal')); ?>
                                            </button>
                                            */ ?>
                                        <?php endif; ?>
                                        <div id="wpjobportal-zywrap-sync-result" style="margin-top: 10px;"></div>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
                <?php  /*: ?>
                <?php  else: ?>
                    <div id="wpjobportal-head" class="wpjobportal-zywrap-header-2step">
                        <h1 class="wpjobportal-head-text">
                            <?php echo esc_html(__('Step 2', 'wp-job-portal')); ?>
                        </h1>
                    </div>

                    <div class="wpjobportal-admin-config-wrapper" style="border-top: none; padding-top: 0;">
                        <form id="wpjobportal-form-notice" class="wpjobportal-configurations" method="post" action="#">

                            <div class="wpjobportal-config-row-wrp">
                                <h2 class="wpjobportal-config-description-notice">
                                    <?php echo esc_html(__("Please save the API key in Step 1.", 'wp-job-portal')); ?>
                                </h2>
                            </div>

                        </form>
                    </div>
                    <? */ ?>
                <?php endif; ?>
            <?php } ?>
        </div>
    </div>
</div>

<?php
// Add our custom JavaScript to the page
$wpjobportal_js = "
jQuery(document).ready(function($) {
    function processImportBatch() {
        var current_btn = $('#zywrap-delta-sync-btn');
        //var resultDiv = $('#wpjobportal-zywrap-status-result');
        var resultDiv = $('#wpjobportal-zywrap-sync-result');
        var ajaxurl = '" . esc_url(admin_url("admin-ajax.php")) . "';

        // --- SECONDARY / RECURSIVE CALL ---
        $.post(ajaxurl, {
            action: 'wpjobportal_ajax',
            wpjobportalme: 'zywrap',

            // 2. The SEPARATE server function (Task) you requested
            task: 'importZywrapBatchProcess',

            '_wpnonce': '" . esc_attr(wp_create_nonce("zywrap_full_import")) . "'
        })
        .done(function(response) {
            if (response.success) {
                var data = response.data;

                // CASE 1: STILL PAUSED -> RECURSIVE CALL
                if (data.status === 'paused') {
                    // Update UI with progress
                    resultDiv.html('<div class=\"notice notice-warning\"><p><span class=\"spinner is-active\"></span>' + data.message + ' (" . esc_js(__('Remaining', 'wp-job-portal')) . ": <strong>' + data.remaining + '</strong>)</p></div>');

                    // CALL ITSELF AGAIN to process the next batch
                    processImportBatch(current_btn);
                }

                // CASE 2: FINALLY COMPLETED
                else if (data.status === 'completed') {
                    resultDiv.html('<div class=\"notice notice-success\"><p>' + data.message + ' (" . esc_js(__('Imported', 'wp-job-portal')) . ": ' + data.imported + ', " . esc_js(__('Failed', 'wp-job-portal')) . ": ' + data.failed + ')</p></div>');

                    // Done! Reload page
                     setTimeout(function() { location.reload(); }, 5000);
                }

            } else {
                // Handle Error
                resultDiv.html('<div class=\"notice notice-error\"><p>' + (response.data.message || '" . esc_js(__('Error in batch processing', 'wp-job-portal')) . "') + '</p></div>');
                current_btn.prop('disabled', false);
            }
        })
        .fail(function(xhr, textStatus, errorThrown) {
            resultDiv.html('<div class=\"notice notice-error\"><p>" . esc_js(__('Batch Error', 'wp-job-portal')) . ": ' + textStatus + '</p></div>');
            current_btn.prop('disabled', false);
        });
    }
    // --- API KEY CHECK AJAX ---
    $(document).on('click', '#wpjobportal-check-zywrap-status', function(e) {
        e.preventDefault();
        var apiKey = $('input[name=\"wpjobportal_zywrap_api_key\"]').val();
        var resultDiv = $('#wpjobportal-zywrap-status-result');

        if (!apiKey) {
            resultDiv.html('<div class=\"notice notice-error\"><p>" . esc_js(__('Please enter an API key first.', 'wp-job-portal')) . "</p></div>');
            return;
        }

        resultDiv.html('<span class=\"spinner is-active\"></span> " . esc_js(__('Checking API status...', 'wp-job-portal')) . "');

        var ajaxurl = '" . esc_url(admin_url("admin-ajax.php")) . "';
        $.post(ajaxurl, {
            action: 'wpjobportal_ajax',
            wpjobportalme: 'zywrap',
            task: 'checkZywrapApiKey',
            api_key: apiKey,
            '_wpnonce': '" . esc_attr(wp_create_nonce("check-zywrap-key")) . "'
        })
        .done(function(response) {
            if (response.success) {
                // Key is valid
                var statusClass = (response.data.status === 'ok') ? 'notice-success' : 'notice-warning';
                resultDiv.html('<div class=\"notice ' + statusClass + '\"><p><strong>" . esc_js(__('Status', 'wp-job-portal')) . ": ' + response.data.status.toUpperCase() + '</strong><br>' + response.data.message + '</p></div>');
            } else {
                // Key is invalid or API call failed
                resultDiv.html('<div class=\"notice notice-error\"><p><strong>" . esc_js(__('Status', 'wp-job-portal')) . ": ' + (response.data.status || 'Error') + '</strong><br>' + response.data.message + '</p></div>');
            }
        })
        .fail(function(xhr, textStatus, errorThrown) {
            resultDiv.html('<div class=\"notice notice-error\"><p>" . esc_js(__('Request Failed', 'wp-job-portal')) . ": ' + textStatus + ' - ' + errorThrown + '</p></div>');
        });
    });

    // --- FULL IMPORT AJAX ---
    $(document).on('click', '.zywrap-full-import-btn', function(e) {
        e.preventDefault();
        var resultDiv = $('#wpjobportal-zywrap-sync-result');
        var type = jQuery(this).attr('data-type');
        if(type == 1) {
            resultDiv.html('<span class=\"spinner is-active\"></span> " . esc_js(__('Downloading and importing... This may take several minutes.', 'wp-job-portal')) . "');
        }
        if(type == 2) {
            resultDiv.html('<span class=\"spinner is-active\"></span> " . esc_js(__('Only new and changed data is being fetched and imported. This should take a few moments...', 'wp-job-portal')) . "');
        }
        if(type == 3) {
            if (!confirm('" . esc_js(__('This will erase all local wrappers and perform a fresh download. This is recommended. Continue?', 'wp-job-portal')) . "')) {
                return;
            }
            resultDiv.html('<span class=\"spinner is-active\"></span> " . esc_js(__('This will refresh everything from scratch and may take several minutes...', 'wp-job-portal')) . "');
        }

        $(this).prop('disabled', true); // Disable button

        var ajaxurl = '" . esc_url(admin_url("admin-ajax.php")) . "';
        $.post(ajaxurl, {
            action: 'wpjobportal_ajax',
            wpjobportalme: 'zywrap',
            task: 'importZywrapData',
            actionType: type,
            '_wpnonce': '" . esc_attr(wp_create_nonce("zywrap_full_import")) . "'
        })
        .done(function(response) {
            /*
            if (response.success) {
                resultDiv.html('<div class=\"notice notice-success\"><p>' + response.data.message + '</p></div>');
                // Reload the page to show the 'Delta Sync' button
                setTimeout(function() { location.reload(); }, 2000);
            } else {
                resultDiv.html('<div class=\"notice notice-error\"><p>' + response.data.message + '</p></div>');
                $('.zywrap-full-import-btn').prop('disabled', false);
            }
            */
            if (response.success) {
                var data = response.data; // Access the array returned by PHP
                console.log(data);
                // CASE 1: PAUSED - Recursive Call
                if (data.status === 'paused') {
                    // Update UI to show progress (optional but recommended)
                    resultDiv.html('<div class=\"notice notice-warning\"><p>' + data.message + ' (" . esc_js(__('Remaining', 'wp-job-portal')) . ": ' + data.remaining + ')</p></div>');

                    // Immediately trigger the next batch
                    processImportBatch();
                }
                // CASE 2: COMPLETED - Finish Up
                else if (data.status === 'completed') {
                    resultDiv.html('<div class=\"notice notice-success\"><p>' + data.message + ' (" . esc_js(__('Imported', 'wp-job-portal')) . ": ' + data.imported + ', " . esc_js(__('Failed', 'wp-job-portal')) . ": ' + data.failed + ', " . esc_js(__('Skipped', 'wp-job-portal')) . ": ' + data.skipped + ')</p></div>');

                    // Reload the page to show the 'Delta Sync' button
                    // setTimeout(function() { location.reload(); }, 2000);
                }
                // Fallback for other success cases
                else {
                    resultDiv.html('<div class=\"notice notice-info\"><p>' + (data.message || '" . esc_js(__('Action processed', 'wp-job-portal')) . "') + '</p></div>');
                }

            } else {
                // Handle logical error from WP (wp_send_json_error)
                resultDiv.html('<div class=\"notice notice-error\"><p>' + (response.data.message || '" . esc_js(__('Unknown error occurred', 'wp-job-portal')) . "') + '</p></div>');
                $('.zywrap-full-import-btn').prop('disabled', false);
            }
        })
        .fail(function(xhr, textStatus, errorThrown) {
            // This catches timeout errors and PHP fatal errors that return non-JSON
            resultDiv.html('<div class=\"notice notice-error\"><p>" . esc_js(__('Error: The request failed or timed out. Please check your PHP logs.', 'wp-job-portal')) . " (' + textStatus + ')</p></div>');
            $('.zywrap-full-import-btn').prop('disabled', false);
        });
    });

    // --- DELTA SYNC AJAX ---
    $(document).on('click', '#zywrap-delta-sync-btn', function(e) {
        e.preventDefault();
        var resultDiv = $('#wpjobportal-zywrap-sync-result');

        resultDiv.html('<span class=\"spinner is-active\"></span> " . esc_js(__('Checking for updates...', 'wp-job-portal')) . "');
        $(this).prop('disabled', true);

        var ajaxurl = '" . esc_url(admin_url("admin-ajax.php")) . "';
        $.post(ajaxurl, {
            action: 'wpjobportal_ajax',
            wpjobportalme: 'zywrap',
            task: 'sync_zywrap_delta',
            '_wpnonce': '" . esc_attr(wp_create_nonce("zywrap_delta_sync")) . "'
        })
        .done(function(response) {
            if (response.success) {
                resultDiv.html('<div class=\"notice notice-success\"><p>' + response.data.message + '</p></div>');
                setTimeout(function() { location.reload(); }, 2000);
            } else {
                resultDiv.html('<div class=\"notice notice-error\"><p>' + response.data.message + '</p></div>');
                $('#zywrap-delta-sync-btn').prop('disabled', false);
            }
        })
        .fail(function(xhr, textStatus, errorThrown) {
            resultDiv.html('<div class=\"notice notice-error\"><p>" . esc_js(__('Request Failed', 'wp-job-portal')) . ": ' + textStatus + ' - ' + errorThrown + '</p></div>');
            $('#zywrap-delta-sync-btn').prop('disabled', false);
        });
    });
});
";
wp_register_script( 'wpjobportal-inline-handle', '' );
wp_enqueue_script( 'wpjobportal-inline-handle' );

wp_add_inline_script('wpjobportal-inline-handle', $wpjobportal_js);
?>
