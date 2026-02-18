<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

// 1. Force load WordPress Dashicons
wp_enqueue_style('dashicons');
wp_enqueue_style('jquery-ui-css', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/jquery-ui-smoothness.css');
// We are leaving the plugin's default Select2 scripts disabled
// wp_enqueue_style('wpjobportal-select2css', WPJOBPORTAL_PLUGIN_URL . 'includes/css/select2.min.css');
// wp_enqueue_script('wpjobportal-select2js', WPJOBPORTAL_PLUGIN_URL . 'includes/js/select2.min.js');

// Get all the data we loaded in the controller
$wpjobportal_data = wpjobportal::$_data['playground_data'];
$wpjobportal_categories = $wpjobportal_data['categories'];
$wpjobportal_models = $wpjobportal_data['models'];
$wpjobportal_languages = $wpjobportal_data['languages'];
$wpjobportal_templates = $wpjobportal_data['templates'];
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
                        <li><?php echo esc_html(__('Zywrap Content Generation','wp-job-portal')); ?></li>
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

        <?php WPJOBPORTALincluder::getTemplate('templates/admin/pagetitle',array('wpjobportal_module' => 'wpjobportal' , 'wpjobportal_layouts' => 'playground')); ?>

        <div id="wpjobportal-admin-wrapper" class="wpjobportal-admin-config-wrapper">
            <div id="wpjobportaladmin-wrapper" class="wpjobportaladmin-wrapper">
                <div class="wpjobportaladmin-body-main">


                        <?php /*
                        <div class="wpjobportal-tab-nav">
                          <a href="<?php echo esc_url(wp_nonce_url('admin.php?page=wpjobportal_zywrap&wpjobportallt=zywrap','configuration'))?>" class="wpjobportal-tab-link"><?php echo esc_html(__('AI Settings', 'wp-job-portal')); ?></a>
                          <a href="<?php echo esc_url(wp_nonce_url('admin.php?page=wpjobportal_zywrap&wpjobportallt=playground','Zywrap'))?>" class="wpjobportal-tab-link active"><?php echo esc_html(__('AI Generate Text', 'wp-job-portal')); ?></a>
                          <a href="<?php echo esc_url(wp_nonce_url('admin.php?page=wpjobportal_zywraplogs&wpjobportallt=logs','configuration'))?>" class="wpjobportal-tab-link"><?php echo esc_html(__('AI Logs', 'wp-job-portal')); ?></a>
                        </div>
                        <div id="wpjobportal-head">
                            <h1 class="wpjobportal-head-text">
                                <?php echo esc_html(__('AI Generate Text', 'wp-job-portal')); ?>
                            </h1>
                        </div>
                        */ ?>

                        <div style="clear: both;">
                            <?php if(!$wpjobportal_saved_key): ?>
                                <div class="wpjobportal-setup-notice">
                                    <span class="dashicons dashicons-warning" style="font-size: 24px; color: #d63638;"></span>
                                    <a href="<?php echo esc_url(admin_url("admin.php?page=wpjobportal_zywrap&wpjobportallt=zywrap")); ?>">
                                        <?php echo esc_html(__('Setup Required: Please add your API Key in settings.', 'wp-job-portal')); ?>
                                    </a>
                                </div>
                            <?php else:
                                $wpjobportal_categories_count = count($wpjobportal_categories);
                                if($wpjobportal_categories_count < 1):?>
                                <div class="wpjobportal-setup-notice">
                                    <span class="dashicons dashicons-warning" style="font-size: 24px; color: #d63638;"></span>
                                    <a href="<?php echo esc_url(admin_url("admin.php?page=wpjobportal_zywrap&wpjobportallt=zywrap")); ?>">
                                        <?php echo esc_html(__('Action Needed: Please sync the Wrapper Catalog (Step 2) in settings.', 'wp-job-portal')); ?>
                                    </a>
                                </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <div id="wpjobportal-admin-wrapper" class="wpjobportal-admin-config-wrapper" style="background: transparent; box-shadow: none; border: none; padding: 0;">

                            <div class="zywrap-playground-grid">

                                <div class="zywrap-controls-column">
                                    <div class="wpjobportal-sidebar-section-title"><?php echo esc_html(__('Core Settings', 'wp-job-portal')); ?></div>
                                    <div class="wpjobportal-special-config-wrapper">

                                        <div class="wpjobportal-input-group wpjobportal-input-group-search-trigger ">
                                            <div class="wpjobportal-input-group-search-trigger-text">
                                                <?php echo esc_html(__('Search', 'wp-job-portal')); ?>
                                            </div>
                                            <div class="wpjobportal-input-group-search-trigger-close">
                                                <?php echo esc_html(__('Close', 'wp-job-portal')); ?>
                                            </div>
                                        </div>
                                        <div class="wpjobportal-input-group wpjobportal-input-group-search-case ">
                                            <label class="wpjobportal-input-label-s-case">
                                                <?php echo esc_html(__('Search', 'wp-job-portal')); ?>
                                            </label>
                                            <div class="wpjp-zywrap-jp-chosen-wrap">
                                                <input type="text" id="wpjp-manual-search" class="dark-input" placeholder="<?php echo esc_attr__('e.g. AI, tech, future', 'wp-job-portal'); ?>">
                                            </div>
                                            <div class="wpjp-zywrap-jp-chosen-wrap wpjp-zywrap-jp-chosen-wrap-search-results">
                                                <select id="wpjp-search-wrapper-select" name="wpjp-search-wrapper-select" class="inputbox dark-select wpjp-zywrap-jp-chosen">
                                                    <option value=""><?php echo esc_html(__('-- Search Results --', 'wp-job-portal')); ?></option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- 1. Category -->
                                        <div class="wpjobportal-input-group">
                                            <label class="wpjobportal-input-label-s-case">
                                                <?php echo esc_html(__('Use Case', 'wp-job-portal')); ?>
                                            </label>
                                            <div id="zywrap_category_parent">
                                                <select id="zywrap_category" name="zywrap_category" class="inputbox dark-select wpjp-zywrap-jp-chosen">
                                                    <option value=""><?php echo esc_html(__('-- Select Category --', 'wp-job-portal')); ?></option>
                                                    <?php foreach ($wpjobportal_categories as $wpjobportal_option) : ?>
                                                        <option value="<?php echo esc_attr($wpjobportal_option->code); ?>"><?php echo esc_html($wpjobportal_option->name); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <!-- 2. Wrapper -->
                                        <div class="wpjobportal-input-group wpjobportal-zywrap-playground-container">
                                             <!-- Header Row: Label and Sort -->
                                            <div class="wpjobportal-zywrap-playground-header-row">
                                                <label class="wpjobportal-input-label-s-case">
                                                    <?php echo esc_html(__('Wrapper', 'wp-job-portal')); ?>
                                                </label>

                                                <button type="button" class="wpjobportal-zywrap-playground-sort-toggle" id="wpjobportal-sort-btn" data-ordering-type="1" >
                                                    <span id="wpjobportal-sort-text">Default Order</span>
                                                    <!-- Icon: Arrow Up Down -->
                                                    <svg style="width: 12px; height: 12px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg>
                                                </button>
                                            </div>

                                            <!-- Chips Row -->
                                            <div class="wpjobportal-zywrap-playground-chips-row">
                                                <!-- Chip 1: Featured -->
                                                <button type="button" class="wpjobportal-zywrap-playground-chip" id="filter_featured" data-state="0">
                                                    <svg class="wpjobportal-zywrap-playground-chip-icon icon-star" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                                    <?php echo esc_html(__('Featured', 'wp-job-portal')); ?>
                                                </button>

                                                <!-- Chip 2: Base Only -->
                                                <button type="button" class="wpjobportal-zywrap-playground-chip" id="filter_base" data-state="0">
                                                    <svg class="wpjobportal-zywrap-playground-chip-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                                                    <?php echo esc_html(__('Base Only', 'wp-job-portal')); ?>
                                                </button>
                                            </div>
                                            <div id="zywrap_wrapper_parent">
                                                <select id="zywrap_wrapper" name="zywrap_wrapper" class="inputbox dark-select wpjp-zywrap-jp-chosen" disabled>
                                                    <option value=""><?php echo esc_html(__('-- Select Category First --', 'wp-job-portal')); ?></option>
                                                </select>
                                            </div>
                                            <div id="wpjp_zywrap_wrapper_count" >
                                            </div>

                                        </div>

                                        <div class="wpjobportal-wrapper-description-wrap">
                                            <label class="wpjobportal-input-label-s-case">
                                                <?php echo esc_html(__('Description', 'wp-job-portal')); ?>:
                                            </label>
                                            <div id="wpjobportal-wrapper-description" class="wpjobportal-wrapper-description" >
                                            </div>
                                        </div>
                                    </div>

                                    <div class="wpjobportal-sidebar-section-title"><?php echo esc_html(__('Parameters', 'wp-job-portal')); ?></div>

                                    <!-- 3. AI Model -->
                                    <div class="wpjobportal-input-group">
                                        <label class="input-label">
                                            <?php echo esc_html(__('AI Model', 'wp-job-portal')); ?><span>(<?php echo esc_html(__('Optional', 'wp-job-portal')); ?>)</span>
                                        </label>
                                        <div id="zywrap_model_parent">
                                            <select id="zywrap_model" name="zywrap_model" class="inputbox dark-select wpjp-zywrap-jp-chosen">
                                                <option value=""><?php echo esc_html(__('-- Select Model --', 'wp-job-portal')); ?></option>
                                                <?php
                                                $wpjobportal_first_model = true;
                                                foreach ($wpjobportal_models as $wpjobportal_option) : ?>
                                                    <option value="<?php echo esc_attr($wpjobportal_option->code); ?>" <?php if ($wpjobportal_first_model) { echo 'selected'; $wpjobportal_first_model = false; } ?>>
                                                        <?php echo esc_html($wpjobportal_option->name); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>


                                    <!-- 8. Language -->
                                    <div class="wpjobportal-input-group">
                                        <label class="input-label"><?php echo esc_html(__('Language', 'wp-job-portal')); ?><span>(<?php echo esc_html(__('Optional', 'wp-job-portal')); ?>)</span></label>
                                        <div id="zywrap_language_parent">
                                            <select id="zywrap_language" name="zywrap_language" class="inputbox dark-select wpjp-zywrap-jp-chosen">
                                                <option value=""><?php echo esc_html(__('-- Default (English) --', 'wp-job-portal')); ?></option>
                                                <?php foreach ($wpjobportal_languages as $wpjobportal_option) : ?>
                                                    <option value="<?php echo esc_attr($wpjobportal_option->code); ?>"><?php echo esc_html($wpjobportal_option->name); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <details class="modern-details">
                                        <summary><?php echo esc_html(__('Advanced Constraints', 'wp-job-portal')); ?></summary>
                                        <div class="details-content">
                                            <?php /*
                                            <div>
                                                <div class="wpjobportal-input-group-scase">
                                                    <label class="input-label"><?php echo esc_html(__('Context / Background Data', 'wp-job-portal')); ?></label>
                                                    <textarea id="zywrap_context" class="dark-textarea" placeholder="<?php echo esc_attr__('Paste reference text or context...', 'wp-job-portal'); ?>"></textarea>
                                                </div>
                                                <label class="input-label"><?php echo esc_html(__('SEO Keywords', 'wp-job-portal')); ?></label>
                                                <input type="text" id="zywrap_seo_keywords" class="dark-input" placeholder="<?php echo esc_attr__('e.g. AI, tech, future', 'wp-job-portal'); ?>">
                                            </div>
                                            <div>
                                                <label class="input-label"><?php echo esc_attr__('Negative Words', 'wp-job-portal'); ?></label>
                                                <input type="text" id="zywrap_negative_constraints" class="dark-input" placeholder="<?php echo esc_attr__('e.g. error, bias', 'wp-job-portal'); ?>">
                                            </div>
                                            */ ?>
                                            <div id="zywrap_overrides_parent" class="zywrap-overrides-grid">
                                                <!-- Advanced dropdowns mapped to grid -->
                                                <div class="zywrap_overrides_parent_wrp">
                                                    <?php
                                                    $override_fields = [
                                                        'toneCode' => ['label' => 'Tone', 'data' => $wpjobportal_templates['tones'] ?? []],
                                                        'styleCode' => ['label' => 'Style', 'data' => $wpjobportal_templates['styles'] ?? []],
                                                        'formatCode' => ['label' => 'Format', 'data' => $wpjobportal_templates['formattings'] ?? []],
                                                        'complexityCode' => ['label' => 'Complexity', 'data' => $wpjobportal_templates['complexities'] ?? []],
                                                        'lengthCode' => ['label' => 'Length', 'data' => $wpjobportal_templates['lengths'] ?? []],
                                                        'audienceCode' => ['label' => 'Audience', 'data' => $wpjobportal_templates['audienceLevels'] ?? []],
                                                        'responseGoalCode' => ['label' => 'Goal', 'data' => $wpjobportal_templates['responseGoals'] ?? []],
                                                        'outputCode' => ['label' => 'Output', 'data' => $wpjobportal_templates['outputTypes'] ?? []],
                                                    ];

                                                    if (!empty($override_fields) && is_array($override_fields)):
                                                        foreach($override_fields as $id => $field):
                                                    ?>
                                                        <div id="<?php echo esc_attr($id); ?>_parent" class="wpjp-zywrap-jp-chosen-wrap">
                                                            <select id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($id); ?>" class="inputbox wpjobportal-form-select-field wpjp-zywrap-jp-chosen">
                                                                <option value=""><?php echo esc_html($field['label'] . ' (Default)'); ?></option>
                                                                <?php if(!empty($field['data']) && is_array($field['data'])): ?>
                                                                    <?php foreach ($field['data'] as $opt) : ?>
                                                                        <option value="<?php echo esc_attr($opt['code']); ?>"><?php echo esc_html($opt['name']); ?></option>
                                                                    <?php endforeach; ?>
                                                                <?php endif; ?>
                                                            </select>
                                                        </div>
                                                    <?php endforeach; endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </details>
                                </div>
                                <div class="wpjobportal-app-main">
                                    <!-- Left: Prompting Area -->
                                    <div class="wpjobportal-glass-panel" style="grid-column: 1;">
                                        <div class="wpjobportal-panel-header">
                                            <div class="wpjobportal-panel-title">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:20px;height:20px; color:#6366f1;">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                </svg>
                                                <?php echo esc_html(__('Input', 'wp-job-portal')); ?><span>(<?php echo esc_html(__('Optional', 'wp-job-portal')); ?>)</span>
                                            </div>
                                        </div>
                                        <div class="wpjobportal-input-group" style="display: flex; flex-direction: column;">
                                            <textarea id="zywrap_prompt" class="dark-textarea" style="margin-bottom: 16px;" placeholder="Enter your main prompt here..."></textarea>
                                            <div class="zywrap-run-btn-wrapper">
                                                <button type="button" id="zywrap-run-button" class="btn btn-primary button-hero">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z" /></svg>
                                                    <?php echo esc_html(__('Generate Content', 'wp-job-portal')); ?>
                                                </button>
                                            </div>
                                        </div>
                                        <div id="zywrap-output-error" style="display: none; margin-top: 10px;" class="notice notice-error inline">
                                            <p></p>
                                        </div>
                                        <div class="wpjobportal-wpjobportal-glass-panel-s" style="grid-column: 2; display: flex; flex-direction: column;">
                                            <div class="wpjobportal-panel-header">
                                                <div class="wpjobportal-panel-title">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:20px;height:20px; color:#10b981;">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 9.75L16.5 12l-2.25 2.25m-4.5 0L7.5 12l2.25-2.25M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z" />
                                                    </svg>
                                                    <?php echo esc_html(__('Console Output', 'wp-job-portal')); ?>
                                                </div>
                                                <div class="zywrap-toolbar-actions" style="display: flex; gap: 8px;">
                                                    <!-- === NEW: Summarize Button === -->
                                                    <?php /*
                                                    <button type="button" id="zywrap-summarize-button" class="btn btn-icon"  title="<?php echo esc_attr__('Summarize', 'wp-job-portal'); ?>">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                                    </button>
                                                    <button type="button" id="zywrap-clear-button" class="btn btn-icon" title="<?php echo esc_attr__('Clear Output', 'wp-job-portal'); ?>">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                                    </button>
                                                    */ ?>
                                                    <button type="button" id="zywrap-copy-button" class="btn btn-icon" title="<?php echo esc_attr__('Copy Output', 'wp-job-portal'); ?>">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184" /></svg>
                                                    </button>
                                                </div>
                                            </div>
                                            <div id="zywrap-output-container" class="output-container">
                                                <pre id="zywrap-output"><?php echo esc_html( __('Ready to generate content. Select a wrapper and click Run.', 'wp-job-portal') ); ?></pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php
// Add our custom JavaScript to the page
$wpjobportal_js = "
// var wrapper_descriptions = [];
var globalWrapperData = [];
var allWrapperOptions = [];
var selectwrapperid = '';
var ajaxurl = '" . esc_url(admin_url("admin-ajax.php")) . "';
jQuery(document).ready(function($) {

    jQuery('.wpjp-zywrap-jp-chosen').chosen({});

    var sortBtn = $('#wpjobportal-sort-btn');
    var sortText = $('#wpjobportal-sort-text');
    // 2. Toggle Sort
    sortBtn.on('click', function() {
        var  ordering_status = $(this).attr('data-ordering-type');
        if (ordering_status == 1) {
            sortText.text('" . esc_js(__('Sorted A-Z', 'wp-job-portal'))."');
            $(this).attr('data-ordering-type',0);
        } else {
            sortText.text('" . esc_js(__('Default Order', 'wp-job-portal'))."');
            $(this).attr('data-ordering-type',1);
        }
        reorderSortWrappers();
    });

    function reorderSortWrappers() {
        var wrapperSelect = $('#zywrap_wrapper');
        var orderingType = $('#wpjobportal-sort-btn').attr('data-ordering-type');
        var selectedValue = wrapperSelect.val();

        // Get all current options except placeholder
        var options = wrapperSelect.find('option').not(':first').get();

        options.sort(function (a, b) {

            // DEFAULT ORDER (ordering_number)
            if (orderingType == '1') {
                var orderA = parseInt($(a).data('ordering_number'), 10) || 0;
                var orderB = parseInt($(b).data('ordering_number'), 10) || 0;
                return orderA - orderB;
            }

            // SORT A–Z (text)
            var textA = $(a).text().toLowerCase();
            var textB = $(b).text().toLowerCase();

            if (textA < textB) return -1;
            if (textA > textB) return 1;
            return 0;
        });

        // Re-append sorted options
        $.each(options, function (idx, option) {
            wrapperSelect.append(option);
        });

        // Restore selection
        if (selectedValue) {
            wrapperSelect.val(selectedValue).trigger('change');
        }
        wrapperSelect.trigger('chosen:updated');
    }


    // Initialize Select2
    //$('.wpjp-zywrap-jp-chosen').select2({ width: '100%' });



    // 1. Dependent Dropdown (Category -> Wrappers)
    function updateWrapperList() {
        var categoryCode = $('#zywrap_category').val();

        var showFeatured = $('#filter_featured').attr('data-state');
        var showBase = $('#filter_base').attr('data-state');


        var wrapperSelect = $('#zywrap_wrapper');

        if (!categoryCode) {
            wrapperSelect.empty().append('<option value=\"\">". esc_html(__('-- Select Category First --', 'wp-job-portal'))."</option>').prop('disabled', true).trigger('change');
            return;
        }

        wrapperSelect.prop('disabled', true).empty().append('<option value=\"\">" . esc_js(__('Loading...', 'wp-job-portal')) . "</option>').trigger('change');

        $.post(ajaxurl, {
            action: 'wpjobportal_ajax',
            wpjobportalme: 'zywrap',
            task: 'getWrappersByCategory',
            category_code: categoryCode,
            show_featured: showFeatured,
            show_base: showBase,
            '_wpnonce': '" . esc_attr(wp_create_nonce("zywrap_get_wrappers")) . "'
        }, function(response) {
            if (response.success) {
                wrapperSelect.empty().append('<option value=\"\">". esc_html(__('-- Select Wrapper --', 'wp-job-portal'))."</option>');
                select_value_wrap = '';
                response.data.forEach(function(wrapper) {
                    // wrapperSelect.append($('<option>', {
                    //     value: wrapper.code,
                    //     text: wrapper.name
                    // }));
                    // wrapper_descriptions[wrapper.code] = wrapper.description;
                    wrapperSelect.append($('<option>', {
                            value: wrapper.code,
                            text: wrapper.name
                        }).data('description', wrapper.description).data('isfeatured', wrapper.featured).data('isbase', wrapper.base).data('ordering_number', wrapper.ordering)
                    );
                    if( selectwrapperid != '' && wrapper.id == selectwrapperid){
                        select_value_wrap = wrapper.code;
                    }

                });

                allWrapperOptions = response.data.map(function(wrapper) {
                    return {
                        code: wrapper.code,
                        name: wrapper.name,
                        isFeatured: wrapper.featured == 1,
                        isBase: wrapper.base == 1,
                        description: wrapper.description
                    };
                });
                if(select_value_wrap != ''){
                    wrapperSelect.val(select_value_wrap).trigger('change');
                }

                wrapperSelect.prop('disabled', false).trigger('change');
                // var  ordering_status = $(this).attr('data-ordering-type');
                // reorderSortWrappers(ordering_status);
                wrapperSelect.prop('disabled', false).trigger('chosen:updated');
                // update count
                updateRecordCounts();

            } else {
                wrapperSelect.empty().append('<option value=\"\">". esc_html(__('-- Error Loading --', 'wp-job-portal'))."</option>').trigger('change');
            }
        });
        // wrapperSelect.on('change', function () {
        wrapperSelect.off('change').on('change', function () {
            var desc = $(this).find(':selected').data('description') || '';
            $('#wpjobportal-wrapper-description').text(desc).toggle(!!desc);
            if(desc != ''){
                $('.wpjobportal-wrapper-description-wrap').slideDown('slow');
            }

        });
    }

    function updateRecordCounts(){
        var wrapperSelect = $('#zywrap_wrapper');
        var count = wrapperSelect.find('option').not(':first').length;
        $('#wpjp_zywrap_wrapper_count').html('('+count+ ' " . esc_js(__('Records', 'wp-job-portal')) . ")');
    }

    $('#zywrap_category').on('change', updateWrapperList);

    // featured and base button
    // $('#filter_base, #filter_featured').on('click', function() {
    //     alert('click detected');
    //     current_state =  $(this).attr('data-state');
    //     if(current_state == 1){
    //         $(this).attr('data-state',0);
    //         $(this).removeClass('wpjbpis-active');
    //     }else{
    //         $(this).attr('data-state',1);
    //         $(this).addClass('wpjbpis-active');
    //     }
    //     // update list
    //     // updateWrapperList();
    //     updateWrapperListLocal();
    //     alert('task done');
    // });

    $('#filter_base, #filter_featured').on('click', function() {
        var c_this = $(this);
        var newState = c_this.attr('data-state') === '1' ? '0' : '1';

        c_this.attr('data-state', newState);
        c_this.toggleClass('wpjbpis-active', newState === '1');

        updateWrapperListLocal();
    });

function updateWrapperListLocal() {
    var showFeatured = $('#filter_featured').attr('data-state') === '1';
    var showBase = $('#filter_base').attr('data-state') === '1';
    var wrapperSelect = $('#zywrap_wrapper');
    var currentSelection = wrapperSelect.val();

    wrapperSelect.empty();
    wrapperSelect.append('<option value=\"\">-- Select Wrapper --</option>');
    var addedCount = 0;
    allWrapperOptions.forEach(function(wrapper) {
        var shouldShow = true;

        if (showFeatured || showBase) {
            shouldShow =
                (showFeatured && wrapper.isFeatured) ||
                (showBase && wrapper.isBase);
        }

        if (shouldShow) {
            var option = $('<option>', {
                value: wrapper.code,
                text: wrapper.name
            }).data('description', wrapper.description);

            wrapperSelect.append(option);
            addedCount = addedCount + 1;
        }

    });

    // alert('FILTERING DONE' +'Options added: ' + addedCount);
    var  ordering_status = $('#wpjobportal-sort-btn').attr('data-ordering-type');
    reorderSortWrappers(ordering_status);
    wrapperSelect.val(currentSelection).trigger('change');
    wrapperSelect.trigger('chosen:updated');
    // update count
    updateRecordCounts();
}

    // 2. Clear Button
    $('#zywrap-clear-button').on('click', function() {
        $('#zywrap-output').text('" . esc_js(__('Ready to generate content. Select a wrapper and click Run.', 'wp-job-portal')) . "');
        $('#zywrap-output-error').hide();
    });

    // 3. Copy Button
    $('#zywrap-copy-button').on('click', function() {
        var outputText = $('#zywrap-output').text();
        var button = $(this);
        var originalHtml = button.html();

        navigator.clipboard.writeText(outputText).then(function() {
            button.html('<span class=\"dashicons dashicons-yes\"></span> " . esc_js(__('Copied!', 'wp-job-portal')) . "');
            setTimeout(function() { button.html(originalHtml); }, 2000);
        });
    });

    // === NEW: Summarize Button Logic ===
    $('#zywrap-summarize-button').on('click', function() {
        var outputText = $('#zywrap-output').text();
        // Basic validation: ensure there is text to summarize
        if (outputText.length < 50 || outputText.includes('Ready to generate content')) {
            alert('" . esc_js(__('Please generate some text first before summarizing.', 'wp-job-portal')) . "');
            return;
        }

        // Check if context has content and confirm overwrite
        if ($('#zywrap_context').val().trim() !== '') {
            if (!confirm('" . esc_js(__('This will replace your current text in the Context field. Continue?', 'wp-job-portal')) . "')) {
                return;
            }
        }

        // Logic: Move current output to 'Context', set prompt to 'Summarize this', and trigger Run
        $('#zywrap_context').val(outputText);
        $('#zywrap_prompt').val('Summarize the text above into a concise TL;DR or bullet points.');

        // Scroll to context field to show user what happened
        $('html, body').animate({
            scrollTop: $('#zywrap_context').offset().top - 100
        }, 500);

        // Optional: Auto-click run?
        // $('#zywrap-run-button').click();
        // Better to let user click Run so they can adjust prompt if needed.
    });

    // 4. Run Wrapper
    $('#zywrap-run-button').on('click', function() {
        var button = $(this);
        var outputPre = $('#zywrap-output');
        var errorDiv = $('#zywrap-output-error');
        var originalHtml = button.html();

        outputPre.text('" . esc_js(__('Generating content... Please wait...', 'wp-job-portal')) . "');
        errorDiv.hide().find('p').empty();
        button.prop('disabled', true).html('<span class=\"spinner is-active\" style=\"float:none; margin:0 5px 0 0;\"></span> " . esc_js(__('Running...', 'wp-job-portal')) . "');

        // Collect all override codes
        var overrides = {};
        var override_selects = ['toneCode', 'styleCode', 'formatCode', 'complexityCode', 'lengthCode', 'audienceCode', 'responseGoalCode', 'outputCode'];
        override_selects.forEach(function(key) {
            var value = $('#' + key).val();
            if (value) {
                overrides[key] = value;
            }
        });

        // Collect main data
        var data = {
            action: 'wpjobportal_ajax',
            wpjobportalme: 'zywrap',
            task: 'executeZywrapProxy',
            _wpnonce: '" . esc_attr(wp_create_nonce("zywrap_execute_proxy")) . "',
            model: $('#zywrap_model').val(),
            wrapperCode: $('#zywrap_wrapper').val(),
            language: $('#zywrap_language').val(),
            prompt: $('#zywrap_prompt').val(),
            context: $('#zywrap_context').val(),
            seo_keywords: $('#zywrap_seo_keywords').val(),
            negative_constraints: $('#zywrap_negative_constraints').val(),
            overrides: overrides
        };

        // Simple Validation
        if (!data.wrapperCode) {
            errorDiv.find('p').text('" . esc_js(__('Error: Wrapper is required.', 'wp-job-portal')) . "');
            errorDiv.show();
            outputPre.text('Ready to generate content...');
            button.prop('disabled', false).html(originalHtml);
            return;
        }

        // Make the AJAX call to our model's function
        $.post(ajaxurl, data, function(response) {
            if (response.success) {

                // CHANGED: Parse the 'output' field
                var rawOutput = response.data.output;
                var finalDisplayOutput = rawOutput; // Default to raw output

                if (rawOutput) {
                    // Clean the string: remove markdown code fences
                    var cleanedOutput = rawOutput.replace(/^```json\s*/, '').replace(/\s*```$/, '');

                    // Check if the cleaned output is valid JSON
                    try {
                        // Try to parse it...
                        var jsonObject = JSON.parse(cleanedOutput);
                        // ...and re-stringify it beautifully.
                        finalDisplayOutput = JSON.stringify(jsonObject, null, 2);
                    } catch (e) {
                        // It's not JSON, just use the cleaned text
                        finalDisplayOutput = cleanedOutput;
                    }
                } else {
                    finalDisplayOutput = '" . esc_js(__("Received empty output.", "wp-job-portal")) . "';
                }
                outputPre.text(finalDisplayOutput); // Set the text of the <pre> block
            } else {
                // The API call failed (401, 402, 500, etc)
                errorDiv.find('p').text(response.data.message);
                errorDiv.show();
                outputPre.text('Error occurred.');
            }
            button.prop('disabled', false).html(originalHtml);
        });
    });
});

jQuery(document).ready(function($) {
    var searchTimer;
    //setTimeout(function() { populateDropDownJson(); }, 2000);

    var drop_down_select = $('#wpjp-search-wrapper-select');
    var external_search_input = $('#wpjp-manual-search');

    if (!$.fn.chosen) return;

    // drop_down_select.chosen({
    //     no_results_text: 'No results match',
    //     width: '100%',
    //     search_contains: true
    // });

    external_search_input.on('keyup', function(e) {

        // Clear the previous timer to reset the 200ms countdown
        clearTimeout(searchTimer);

        // Set a new timer
        var allData = globalWrapperData || [];
        var searchTerm = $(this).val().toLowerCase();
        if (searchTerm.length < 3) {
            return;
        }
        searchTimer = setTimeout(function() {

            var filtered = [];
            var count = 0;
            var limit = 100;

            for (var i = 0; i < allData.length; i++) {
                if (count >= limit) break;

                var item = allData[i];

                // Accessing data by index: 1 is Name, 0 is Code/ID
                var name = (item[1] || '').toLowerCase();
                var code = (item[0] || '').toLowerCase();

                if (name.indexOf(searchTerm) > -1 || code.indexOf(searchTerm) > -1) {
                    filtered.push(item);
                    count++;
                }
            }

            drop_down_select.empty();
            var record_count = filtered.length;
            if(record_count == 0){
                var option_message = '" . esc_js(__("No record Found.", "wp-job-portal")) . "';
            }else if(record_count == 100){
                var option_message = '" . esc_js(__("100+ records Found.", "wp-job-portal")) . "';
            }else{
                var option_message = record_count +' " . esc_js(__("records Found.", "wp-job-portal")) . "';
            }
            drop_down_select.append('<option value=\"\">'+option_message+'</option>');

            $.each(filtered, function(index, item) {
                // item[0] = ID, item[1] = Name, item[2] = Category
                //var label = item[0] + ' - ' + item[1];
                var label = item[1];
                drop_down_select.append(
                    $('<option>', {
                        value: item[0],
                        text: label,
                        'data-category': item[2]
                    })
                );
            });

            drop_down_select.trigger('chosen:updated');
            //drop_down_select.trigger('chosen:open');
        }, 250); // The 200ms delay you requested
    });

    drop_down_select.on('change', function() {
        var selectedOption = $(this).find(':selected');
        console.log('Selected ID:', $(this).val());
        console.log('Category:', selectedOption.data('category'));

        cate_code = selectedOption.data('category');
        jQuery('#zywrap_category').val(cate_code).trigger('change');
        jQuery('#zywrap_category').trigger('chosen:updated');
        selectwrapperid = $(this).val();
        // hide saerch section
        jQuery('.wpjobportal-input-group-search-trigger-close').hide();
        jQuery('.wpjobportal-input-group-search-trigger-text').show();
        jQuery('.wpjobportal-input-group-search-case').slideUp();
    });

    function populateDropDownJson(){
        // Define global if not exists

        var data = {
            action: 'wpjobportal_ajax',
            wpjobportalme: 'zywrap',
            task: 'getZywrapAllWrappers',
            _wpnonce: '" . esc_attr(wp_create_nonce('zywrap_get_all_wrappers')) . "'
        };

        jQuery.post(ajaxurl, data, function(response) {
            if (response.success) {
                // FIXED: Assign response data to the global variable
                globalWrapperData = response.data;
                console.log('Wrappers loaded:', globalWrapperData.length);
            } else {
                console.log('Failed to load wrappers');
            }
        });
    }

    populateDropDownJson();
    jQuery('.wpjobportal-input-group-search-trigger-text').on('click', function() {
        if(globalWrapperData.length == ''){
            populateDropDownJson();
        }
       jQuery(this).hide();
       jQuery('.wpjobportal-input-group-search-trigger-close').show();
       jQuery('.wpjobportal-input-group-search-case').slideDown('slow');
    });

    jQuery('.wpjobportal-input-group-search-trigger-close').on('click', function() {
       jQuery(this).hide();
       jQuery('.wpjobportal-input-group-search-trigger-text').show();
       jQuery('.wpjobportal-input-group-search-case').slideUp();
    });



});

";
wp_register_script( 'wpjobportal-inline-handle', '' );
wp_enqueue_script( 'wpjobportal-inline-handle' );
// We are no longer using wp_add_inline_script as it was causing conflicts
wp_add_inline_script('wpjobportal-inline-handle', $wpjobportal_js);

// wp_add_inline_script('wpjobportal-select2js', $wpjobportal_js);

?>
