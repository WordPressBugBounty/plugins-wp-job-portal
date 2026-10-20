<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
wp_enqueue_script('iris');
/*
 * The screen's stylesheets load in the page head (the admin class calls this
 * on admin_enqueue_scripts). Asked for from here, mid-page, WordPress could
 * only print them in the footer, and until the page finished loading the
 * layout was unstyled: the presets showed up in the top right corner. The
 * call here only reads back whether a design add-on is active.
 */
$wpjobportal_look_active = WPJOBPORTALthemeModel::enqueueScreenStyles();
// The design add-on has its own settings; the standard design's fields stay
// in the form, hidden, so a save keeps what they hold.
$wpjobportal_look_hide = $wpjobportal_look_active ? 'display:none' : '';
?>
<!-- main wrapper -->
<div id="wpjobportaladmin-wrapper">
    <!-- left menu -->
    <div id="wpjobportaladmin-leftmenu">
        <?php WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <!-- top bar -->
        <div id="wpjobportal-wrapper-top">
            <div id="wpjobportal-wrapper-top-left">
                <div id="wpjobportal-breadcrumbs">
                    <ul>
                        <li>
                            <a href="<?php echo esc_url_raw(admin_url('admin.php?page=wpjobportal')); ?>"
                                title="<?php echo esc_attr(__('Dashboard', 'wp-job-portal')); ?>">
                                <?php echo esc_html(__('Dashboard', 'wp-job-portal')); ?>
                            </a>
                        </li>
                        <li><?php echo esc_html(__('Design', 'wp-job-portal')); ?></li>
                    </ul>
                </div>
            </div>
            <div id="wpjobportal-wrapper-top-right">
                <div id="wpjobportal-config-btn">
                    <a href="admin.php?page=wpjobportal_configuration"
                        title="<?php echo esc_attr(__('Configuration', 'wp-job-portal')); ?>">
                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/config.png">
                    </a>
                </div>
                <div id="wpjobportal-vers-txt">
                    <?php echo esc_html(__('Version', 'wp-job-portal')) . ': '; ?>
                    <span class="wpjobportal-ver"><?php echo esc_html(WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue('versioncode')); ?></span>
                </div>
            </div>
        </div>
        <!-- top head -->
        <div id="wpjobportal-head">
            <h1 class="wpjobportal-head-text">
                <?php echo esc_html(__('Design', 'wp-job-portal')); ?>
            </h1>
        </div>
        <!-- page content -->
        <div id="wpjobportal-admin-wrapper" class="p0 wpjobportal-admin-themepge-wrapper">
            <?php include __DIR__ . '/design-switch.php'; ?>
            <div id="theme_heading">
                <div class="js_themes_presets">
                    <div class="wp-section-title">
                        <div class="wp-icon-box-txt-wrp">
                            <div class="wp-icon-box">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" data-lucide="swatch-book" class="lucide lucide-swatch-book">
                                    <path d="M11 17a4 4 0 0 1-8 0V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2Z"></path>
                                    <path d="M16.7 13H19a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2H7"></path>
                                    <path d="M 7 17h.01"></path>
                                    <path
                                        d="m11 8 2.3-2.3a2.4 2.4 0 0 1 3.404.004L18.6 7.6a2.4 2.4 0 0 1 .026 3.434L9.9 19.8">
                                    </path>
                                </svg>
                            </div>
                            <h2><?php echo esc_html(__('Presets', 'wp-job-portal')); ?></h2>
                        </div>

                        <span class="wpjb-box-styles"> 8 <?php echo esc_html(__('Styles', 'wp-job-portal')); ?></span>
                    </div>
                    <div class="theme_platte">
                        <div class="color_wrapper">
                            <div class="color 1" style="background:#36bc9a;"></div>
                            <div class="color 2" style="background:#333333;"></div>
                            <div class="color 3" style="background:#575757;"></div>
                            <a href="#" class="set_theme"><?php echo esc_html(__('Teal Slate', 'wp-job-portal')); ?></a>
                        </div>
                    </div>
                    <div class="theme_platte">
                        <div class="color_wrapper">
                            <div class="color 1" style="background:#e43039;"></div>
                            <div class="color 2" style="background:#940007;"></div>
                            <div class="color 3" style="background:#575757;"></div>
                            <a href="#" class="set_theme"><?php echo esc_html(__('Ruby Red', 'wp-job-portal')); ?></a>
                        </div>
                    </div>
                    <div class="theme_platte">
                        <div class="color_wrapper">
                            <div class="color 1" style="background:#3baeda;"></div>
                            <div class="color 2" style="background:#333333;"></div>
                            <div class="color 3" style="background:#575757;"></div>
                            <a href="#" class="set_theme"><?php echo esc_html(__('Ocean Cyan', 'wp-job-portal')); ?></a>
                        </div>
                    </div>
                    <div class="theme_platte">
                        <div class="color_wrapper">
                            <div class="color 1" style="background:#4d89dc;"></div>
                            <div class="color 2" style="background:#000000;"></div>
                            <div class="color 3" style="background:#575757;"></div>
                            <a href="#"
                                class="set_theme"><?php echo esc_html(__('Royal Azure', 'wp-job-portal')); ?></a>
                        </div>
                    </div>
                    <div class="theme_platte">
                        <div class="color_wrapper">
                            <div class="color 1" style="background:#8cc051;"></div>
                            <div class="color 2" style="background:#366600;"></div>
                            <div class="color 3" style="background:#575757;"></div>
                            <a href="#"
                                class="set_theme"><?php echo esc_html(__('Forest Lime', 'wp-job-portal')); ?></a>
                        </div>
                    </div>
                    <div class="theme_platte">
                        <div class="color_wrapper">
                            <div class="color 1" style="background:#db4453;"></div>
                            <div class="color 2" style="background:#80000d;"></div>
                            <div class="color 3" style="background:#575757;"></div>
                            <a href="#" class="set_theme"><?php echo esc_html(__('Rose Berry', 'wp-job-portal')); ?></a>
                        </div>
                    </div>
                    <div class="theme_platte">
                        <div class="color_wrapper">
                            <div class="color 1" style="background:#967bdc;"></div>
                            <div class="color 2" style="background:#391a8c;"></div>
                            <div class="color 3" style="background:#575757;"></div>
                            <a href="#"
                                class="set_theme"><?php echo esc_html(__('Deep Amethyst', 'wp-job-portal')); ?></a>
                        </div>
                    </div>
                    <div class="theme_platte">
                        <div class="color_wrapper">
                            <div class="color 1" style="background:#000000;"></div>
                            <div class="color 2" style="background:#120045;"></div>
                            <div class="color 3" style="background:#575757;"></div>
                            <a href="#"
                                class="set_theme"><?php echo esc_html(__('Midnight Mode', 'wp-job-portal')); ?></a>
                        </div>
                    </div>
                </div>
                <form action="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_theme&task=savetheme&action=task'), 'wpjobportal_theme_nonce')); ?>" method="POST" name="adminForm" id="adminForm">
                    <div class="wp-section-title wpjp-tuning-sect">
                        <div class="wp-icon-box-txt-wrp">
                            <div class="wp-icon-box pink">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" data-lucide="sliders-horizontal"
                                    class="lucide lucide-sliders-horizontal">
                                    <path d="M10 5H3"></path>
                                    <path d="M12 19H3"></path>
                                    <path d="M14 3v4"></path>
                                    <path d="M16 17v4"></path>
                                    <path d="M21 12h-9"></path>
                                    <path d="M21 19h-5"></path>
                                    <path d="M21 5h-7"></path>
                                    <path d="M8 10v4"></path>
                                    <path d="M8 12H3"></path>
                                </svg>
                            </div>
                            <h2><?php
                            /*
                             * Each design keeps its own settings, so the title
                             * names the one being edited: the design that is
                             * live. With no second design there is nothing to
                             * tell apart.
                             */
                            $wpjobportal_design_offer = apply_filters('wpjobportal_design_switch', array());
                            if (is_array($wpjobportal_design_offer) && !empty($wpjobportal_design_offer['label'])) {
                                /* translators: %s: design name, e.g. Elegant Design or Standard design */
                                echo esc_html(sprintf(__('Customize: %s', 'wp-job-portal'), $wpjobportal_look_active ? $wpjobportal_design_offer['label'] : __('Standard design', 'wp-job-portal')));
                            } else {
                                echo esc_html(__('Customize', 'wp-job-portal'));
                            }
                            ?></h2>
                        </div>
                    </div>
                    <div class="color_portion" style="<?php echo esc_attr($wpjobportal_look_hide); ?>">
                        <span class="color_title">
                            <?php echo esc_html(__('Primary Color', 'wp-job-portal')); ?>
                        </span>
                        <span class="color_wrp">
                            <input type="text" name="color1" id="color1" value="<?php echo esc_attr(wpjobportal::$_data[0]['color1']); ?>" style="background:<?php echo esc_attr(wpjobportal::$_data[0]['color1']); ?> !important;" />
                            <span class="color_wrp_img">
                                <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/themes/colorpicker.png">
                            </span>
                        </span>
                    </div>
                    <?php if ($wpjobportal_look_active) {
                        /*
                         * Elegant Design's own settings, separate from the
                         * standard design's (still in the form, hidden, so a
                         * save keeps them unchanged). Each field names the custom property
                         * it sets, which is how the preview follows it.
                         */
                        $wpjobportal_ed_fields = WPJOBPORTALthemeModel::wpjpElegantFields();
                        $wpjobportal_ed_colors = array(
                            'ed_color1'     => __('Primary Color', 'wp-job-portal'),
                            'ed_color2'     => __('Secondary Color', 'wp-job-portal'),
                            'ed_color3'     => __('Content Color', 'wp-job-portal'),
                            'ed_border'     => __('Border Color', 'wp-job-portal'),
                            'ed_background' => __('Page Background', 'wp-job-portal'),
                            'ed_card'       => __('Card Background', 'wp-job-portal'),
                        );
                        $wpjobportal_ed_sizes = array(
                            'ed_mainheading'      => __('Main Heading', 'wp-job-portal'),
                            'ed_secondsubheading' => __('Second Sub Heading', 'wp-job-portal'),
                            'ed_subheading'       => __('Sub Heading', 'wp-job-portal'),
                            'ed_bodyfontsize'     => __('Body Text', 'wp-job-portal'),
                        );
                        $wpjobportal_ed_choices = array(
                            'ed_corners' => array(__('Corners', 'wp-job-portal'), array(
                                'square' => __('Square', 'wp-job-portal'),
                                'normal' => __('Normal', 'wp-job-portal'),
                                'round'  => __('Round', 'wp-job-portal'),
                            )),
                            'ed_font' => array(__('Font', 'wp-job-portal'), array(
                                'theme'  => __('Theme font', 'wp-job-portal'),
                                'system' => __('System font', 'wp-job-portal'),
                            )),
                        );
                        ?>
                        <div class="wpjp-theme-group-title wpjp-theme-group-title-actions">
                            <?php echo esc_html(__('Colors', 'wp-job-portal')); ?>
                            <button type="button" class="button button-small wpjp-font-size-reset" id="wpjpResetElegant">
                                <?php echo esc_html(__('Reset to Elegant defaults', 'wp-job-portal')); ?>
                            </button>
                        </div>
                        <?php foreach ($wpjobportal_ed_colors as $wpjobportal_ed_key => $wpjobportal_ed_label) {
                            // Dark text on a light swatch, so the value can be read.
                            $wpjobportal_ed_hex = ltrim(wpjobportal::$_data[0][$wpjobportal_ed_key], '#');
                            if (strlen($wpjobportal_ed_hex) === 3) {
                                $wpjobportal_ed_hex = $wpjobportal_ed_hex[0] . $wpjobportal_ed_hex[0] . $wpjobportal_ed_hex[1] . $wpjobportal_ed_hex[1] . $wpjobportal_ed_hex[2] . $wpjobportal_ed_hex[2];
                            }
                            $wpjobportal_ed_ink = (0.299 * hexdec(substr($wpjobportal_ed_hex, 0, 2)) + 0.587 * hexdec(substr($wpjobportal_ed_hex, 2, 2)) + 0.114 * hexdec(substr($wpjobportal_ed_hex, 4, 2))) > 160 ? '#1d2327' : '#ffffff';
                            ?>
                            <div class="color_portion">
                                <span class="color_title"><?php echo esc_html($wpjobportal_ed_label); ?></span>
                                <span class="color_wrp">
                                    <input type="text" name="<?php echo esc_attr($wpjobportal_ed_key); ?>" id="<?php echo esc_attr($wpjobportal_ed_key); ?>" class="wpjp-ed-field wpjp-ed-color"
                                        data-wpjp-default="<?php echo esc_attr($wpjobportal_ed_fields[$wpjobportal_ed_key]['default']); ?>"
                                        data-wpjp-var="<?php echo esc_attr($wpjobportal_ed_fields[$wpjobportal_ed_key]['var']); ?>"
                                        value="<?php echo esc_attr(wpjobportal::$_data[0][$wpjobportal_ed_key]); ?>" style="background:<?php echo esc_attr(wpjobportal::$_data[0][$wpjobportal_ed_key]); ?> !important; color:<?php echo esc_attr($wpjobportal_ed_ink); ?> !important;" />
                                    <span class="color_wrp_img">
                                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/themes/colorpicker.png">
                                    </span>
                                </span>
                            </div>
                        <?php } ?>
                        <div class="wpjp-theme-group-title">
                            <?php echo esc_html(__('Font Sizes', 'wp-job-portal')); ?>
                        </div>
                        <?php foreach ($wpjobportal_ed_sizes as $wpjobportal_ed_key => $wpjobportal_ed_label) {
                            $wpjobportal_ed_rules = $wpjobportal_ed_fields[$wpjobportal_ed_key]; ?>
                            <div class="color_portion">
                                <span class="color_title"><?php echo esc_html($wpjobportal_ed_label); ?></span>
                                <span class="color_wrp">
                                    <input type="number" name="<?php echo esc_attr($wpjobportal_ed_key); ?>" id="<?php echo esc_attr($wpjobportal_ed_key); ?>" class="wpjp-ed-field wpjp-ed-size wpjp-font-size-field"
                                        data-wpjp-default="<?php echo esc_attr($wpjobportal_ed_rules['default']); ?>"
                                        data-wpjp-var="<?php echo esc_attr($wpjobportal_ed_rules['var']); ?>"
                                        min="<?php echo esc_attr($wpjobportal_ed_rules['min']); ?>" max="<?php echo esc_attr($wpjobportal_ed_rules['max']); ?>" step="1"
                                        value="<?php echo esc_attr(wpjobportal::$_data[0][$wpjobportal_ed_key]); ?>" />
                                    <span class="wpjp-font-size-unit">px</span>
                                </span>
                            </div>
                        <?php } ?>
                        <div class="wpjp-theme-group-title">
                            <?php echo esc_html(__('Style', 'wp-job-portal')); ?>
                        </div>
                        <?php foreach ($wpjobportal_ed_choices as $wpjobportal_ed_key => $wpjobportal_ed_choice) {
                            $wpjobportal_ed_rules = $wpjobportal_ed_fields[$wpjobportal_ed_key]; ?>
                            <div class="color_portion">
                                <span class="color_title"><?php echo esc_html($wpjobportal_ed_choice[0]); ?></span>
                                <span class="color_wrp">
                                    <select name="<?php echo esc_attr($wpjobportal_ed_key); ?>" id="<?php echo esc_attr($wpjobportal_ed_key); ?>" class="wpjp-ed-field wpjp-ed-choice"
                                        data-wpjp-default="<?php echo esc_attr($wpjobportal_ed_rules['default']); ?>"
                                        data-wpjp-vars="<?php echo esc_attr(wp_json_encode($wpjobportal_ed_rules['choices'])); ?>"
                                        <?php if ($wpjobportal_ed_key === 'ed_corners') { ?>data-wpjp-square="<?php echo esc_attr(WPJOBPORTALthemeModel::wpjpElegantSquareCss('.js_effect_preview .wjportal-main-up-wrapper')); ?>"<?php } ?>>
                                        <?php foreach ($wpjobportal_ed_choice[1] as $wpjobportal_ed_value => $wpjobportal_ed_text) { ?>
                                            <option value="<?php echo esc_attr($wpjobportal_ed_value); ?>"<?php selected(wpjobportal::$_data[0][$wpjobportal_ed_key], $wpjobportal_ed_value); ?>><?php echo esc_html($wpjobportal_ed_text); ?></option>
                                        <?php } ?>
                                    </select>
                                </span>
                            </div>
                        <?php } ?>
                    <?php } ?>
                    <div class="color_portion" style="<?php echo esc_attr($wpjobportal_look_hide); ?>">
                        <span class="color_title">
                            <?php echo esc_html(__('Secondary Color', 'wp-job-portal')); ?></span>
                        <span class="color_wrp">
                            <input type="text" name="color2" id="color2" value="<?php echo esc_attr(wpjobportal::$_data[0]['color2']); ?>" style="background:<?php echo esc_attr(wpjobportal::$_data[0]['color2']); ?> !important;" />
                            <span class="color_wrp_img">
                                <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/themes/colorpicker.png">
                            </span>
                        </span>
                    </div>
                    <div class="color_portion" style="<?php echo esc_attr($wpjobportal_look_hide); ?>">
                        <span class="color_title">
                            <?php echo esc_html(__('Content Color', 'wp-job-portal')); ?>
                        </span>
                        <span class="color_wrp">
                            <input type="text" name="color3" id="color3" value="<?php echo esc_attr(wpjobportal::$_data[0]['color3']); ?>" style="background:<?php echo esc_attr(wpjobportal::$_data[0]['color3']); ?> !important;" />
                            <span class="color_wrp_img">
                                <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/themes/colorpicker.png">
                            </span>
                        </span>
                    </div>
                    <?php
                    /*
                     * The defaults come from the model, so Reset and the value
                     * a blank field falls back to are the same numbers.
                     */
                    $wpjobportal_font_defaults = array();
                    foreach (WPJOBPORTALthemeModel::wpjpFontSizeFields() as $wpjobportal_fs_key => $wpjobportal_fs_rules) {
                        $wpjobportal_font_defaults[$wpjobportal_fs_key] = $wpjobportal_fs_rules['default'];
                    }
                    ?>
                    <div class="wpjp-theme-group-title" style="<?php echo esc_attr($wpjobportal_look_hide); ?>">
                        <?php echo esc_html(__('Font Sizes', 'wp-job-portal')); ?>
                        <?php
                        /*
                         * The mark is inside the screen it advertises, so it is
                         * drawn first and cleared afterwards - marking it seen
                         * before this point would mean it never appeared once.
                         */
                        if (function_exists('wpjobportal_new_feature_badge')) {
                            wpjobportal_new_feature_badge('typography');
                        }
                        ?>
                        <button type="button" class="button button-small wpjp-font-size-reset" id="wpjpResetFontSizes">
                            <?php echo esc_html(__('Reset to defaults', 'wp-job-portal')); ?>
                        </button>
                    </div>
                    <div class="color_portion" style="<?php echo esc_attr($wpjobportal_look_hide); ?>">
                        <span class="color_title">
                            <?php echo esc_html(__('Main Heading', 'wp-job-portal')); ?>
                        </span>
                        <span class="color_wrp">
                            <input type="number" name="mainheading" id="mainheading" class="wpjp-font-size-field"
                                data-wpjp-default="<?php echo esc_attr($wpjobportal_font_defaults['mainheading']); ?>"
                                min="18" max="64" step="1"
                                value="<?php echo esc_attr(wpjobportal::$_data[0]['mainheading']); ?>" />
                            <span class="wpjp-font-size-unit">px</span>
                        </span>
                    </div>
                    <div class="color_portion" style="<?php echo esc_attr($wpjobportal_look_hide); ?>">
                        <span class="color_title">
                            <?php echo esc_html(__('Second Sub Heading', 'wp-job-portal')); ?>
                        </span>
                        <span class="color_wrp">
                            <input type="number" name="secondsubheading" id="secondsubheading" class="wpjp-font-size-field"
                                data-wpjp-default="<?php echo esc_attr($wpjobportal_font_defaults['secondsubheading']); ?>"
                                min="16" max="48" step="1"
                                value="<?php echo esc_attr(wpjobportal::$_data[0]['secondsubheading']); ?>" />
                            <span class="wpjp-font-size-unit">px</span>
                        </span>
                    </div>
                    <div class="color_portion" style="<?php echo esc_attr($wpjobportal_look_hide); ?>">
                        <span class="color_title">
                            <?php echo esc_html(__('Sub Heading', 'wp-job-portal')); ?>
                        </span>
                        <span class="color_wrp">
                            <input type="number" name="subheading" id="subheading" class="wpjp-font-size-field"
                                data-wpjp-default="<?php echo esc_attr($wpjobportal_font_defaults['subheading']); ?>"
                                min="14" max="36" step="1"
                                value="<?php echo esc_attr(wpjobportal::$_data[0]['subheading']); ?>" />
                            <span class="wpjp-font-size-unit">px</span>
                        </span>
                    </div>
                    <div class="color_portion" style="<?php echo esc_attr($wpjobportal_look_hide); ?>">
                        <span class="color_title">
                            <?php echo esc_html(__('Body Text', 'wp-job-portal')); ?>
                        </span>
                        <span class="color_wrp">
                            <input type="number" name="bodyfontsize" id="bodyfontsize" class="wpjp-font-size-field"
                                data-wpjp-default="<?php echo esc_attr($wpjobportal_font_defaults['bodyfontsize']); ?>"
                                min="12" max="24" step="1"
                                value="<?php echo esc_attr(wpjobportal::$_data[0]['bodyfontsize']); ?>" />
                            <span class="wpjp-font-size-unit">px</span>
                        </span>
                    </div>
                    <input type="hidden" name="form_request" value="wpjobportal" />
                    <?php
                    // Shown once; from the next visit on there is nothing new here.
                    if (function_exists('wpjobportal_mark_feature_seen')) {
                        wpjobportal_mark_feature_seen('typography');
                    }
                    ?>
                </form>
                <div class="wpjobportal-add-button-wrp">
                    <a class="wpjobportal-add-link button" id="saveColors" href="#"
                        title="<?php echo esc_attr(__('Save Changes', 'wp-job-portal')) ?>">
                        <?php echo esc_html(__('Save Changes', 'wp-job-portal')) ?>
                    </a>
                    <div class="wpjobportal-sugestion-alert-wrp">
                        <div class="wpjobportal-sugestion-alert">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="info" size="14" style="display:inline; vertical-align:middle;" class="lucide lucide-info"><circle cx="12" cy="12" r="10"></circle><path d="M12 16v-4"></path><path d="M12 8h.01"></path></svg>
                            <?php echo esc_html(__('Some changes may require clearing your cache to take effect.', 'wp-job-portal')) ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="js_effect_preview">
                <div class="js_preview-sidetitle">
                    <div class="js_preview-sidetitle_icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="36" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            data-lucide="monitor" style="color: var(--primary);" class="lucide lucide-monitor">
                            <rect width="20" height="14" x="2" y="3" rx="2"></rect>
                            <line x1="8" x2="16" y1="21" y2="21"></line>
                            <line x1="12" x2="12" y1="17" y2="21"></line>
                        </svg>
                    </div>
                    <?php echo esc_html(__('Live Preview', 'wp-job-portal')); ?>
                </div>
                <div class="wjportal-main-up-wrapper">
                    <div class="wjportal-main-wrapper wjportal-clearfix">
                        <div class="wjportal-page-header">
                            <div class="wjportal-page-header-cnt">
                                <div class="wjportal-page-heading">
                                    <?php echo esc_html(__('My Jobs', 'wp-job-portal')); ?></div>
                                <div class="wjportal-breadcrumbs-wrp">
                                    <div class="wjportal-breadcrumbs-links wjportal-breadcrumbs-firstlinks"><a
                                            class="wjportal-breadcrumbs-link"
                                            href="#"><?php echo esc_html(__('Dashboard', 'wp-job-portal')); ?></a>
                                    </div>
                                    <div class="wjportal-breadcrumbs-links wjportal-breadcrumbs-lastlink">
                                        <?php echo esc_html(__('My Jobs', 'wp-job-portal')); ?></div>
                                </div>
                            </div>
                            <div class="wjportal-header-actions">
                                <div class="wjportal-filter-wrp">
                                    <div class="wjportal-filter"><select name="sorting" id="sorting"
                                            onchange="changeCombo()">
                                            <option value=""><?php echo esc_html(__('Default', 'wp-job-portal')); ?>
                                            </option>
                                            <option class="" value="1">
                                                <?php echo esc_html(__('Job Title', 'wp-job-portal')); ?></option>
                                            <option class="" value="2">
                                                <?php echo esc_html(__('Company Name', 'wp-job-portal')); ?></option>
                                            <option class="" value="3">
                                                <?php echo esc_html(__('Category', 'wp-job-portal')); ?></option>
                                            <option class="" value="5">
                                                <?php echo esc_html(__('Location', 'wp-job-portal')); ?></option>
                                            <option class="" value="7">
                                                <?php echo esc_html(__('Status', 'wp-job-portal')); ?></option>
                                            <option class="" value="4">
                                                <?php echo esc_html(__('Job Type', 'wp-job-portal')); ?></option>
                                            <option class="" selected="selected" value="6">
                                                <?php echo esc_html(__('Created', 'wp-job-portal')); ?></option>
                                            <option class="" value="8">
                                                <?php echo esc_html(__('Salary', 'wp-job-portal')); ?></option>
                                        </select> </div>
                                    <div class="wjportal-filter-image"><a class="sort-icon" href="#"
                                            data-image1="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/sort-up.png"
                                            data-image2="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/sort-down.png"
                                            data-sortby="2"><img decoding="async" id="sortingimage"
                                                src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/sort-down.png"></a>
                                    </div>
                                </div>
                                <div class="wjportal-act-btn-wrp"> <a class="wjportal-act-btn" href="#">
                                        <i class="fa fa-plus"></i><?php echo esc_html(__('Add New Job', 'wp-job-portal')); ?></a>
                                </div>
                            </div>
                        </div>
                        <div class="wjportal-jobs-list-wrapper wjportal-my-jobs-wrp">
                            <form id="job_form" method="post" action="#">
                                <div
                                    class="wjportal-jobs-list  wpjobportal-list-item-status-approved  wpjobportal-list-item-is-featured  ">
                                    <div class="wjportal-jobs-list-top-wrp object_11" data-boxid="job_11">
                                        <div class="wjportal-jobs-logo">
                                            <a href="#">
                                                <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/default_logo.png" alt="<?php echo esc_attr__('Company logo', 'wp-job-portal'); ?>">
                                            </a>
                                        </div>

                                        <div class="wjportal-jobs-cnt-wrp">
                                            <div class="wjportal-jobs-middle-wrp">
                                                <div class="wjportal-jobs-data">
                                                </div>
                                                <div class="wjportal-jobs-data">
                                                    <span class="wjportal-job-title">
                                                        <a href="#">
                                                            <?php echo esc_html(__('Android Developer', 'wp-job-portal')); ?>
                                                        </a>
                                                        <span class="wjportal-featured-tag-icon-wrp">
                                                            <span class="wjportal-featured-tag-icon">
                                                                <i class="fa fa-star"></i>
                                                            </span>
                                                            <span class="featurednew-onhover wjportal-featured-hover-wrp" id="gold11" style="display:none"> <?php echo esc_html(__('Expiry Date', 'wp-job-portal')); ?> : 12/28/2025 </span>
                                                        </span>

                                                    </span>
                                                    <span class="wjportal-item-status" style="background:var(--wpjp-ds-color-success, #16803c);">
                                                        <?php echo esc_html(__('Publish', 'wp-job-portal')); ?></span>
                                                    <a class="wjportal-companyname"
                                                        href="#"><?php echo esc_html(__('Buruj Solution', 'wp-job-portal')); ?></a>
                                                </div>
                                                <div class="wjportal-jobs-data">
                                                    <span class="wjportal-jobs-data-text wjportal-jobs-data-icon-class-category">
                                                        <?php echo esc_html(__('Computer/IT', 'wp-job-portal')); ?></span>
                                                    <span class="wjportal-jobs-data-text wjportal-jobs-data-icon-class-location">
                                                        <?php echo esc_html(__('Karachi, Pakistan', 'wp-job-portal')); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="wjportal-jobs-right-wrp">
                                                <div class="wjportal-jobs-info">
                                                    <span class="wjportal-job-type" style="--wpjp-jobtype-color:#00abfa">
                                                        <?php echo esc_html(__('Full-Time', 'wp-job-portal')); ?>
                                                    </span>
                                                </div>
                                                <div class="wjportal-jobs-info">
                                                    <div class="wjportal-jobs-salary">
                                                        1,000 - 1,500 $<span class="wjportal-salary-type"> /
                                                            <?php echo esc_html(__('Per Month', 'wp-job-portal')); ?></span>
                                                    </div>
                                                </div>
                                                <div class="wjportal-jobs-info">
                                                    8 <?php echo esc_html(__('hours Ago', 'wp-job-portal')); ?> </div>
                                                <div class="wjportal-jobs-status">
                                                    <span class="wjportal-jobs-status-text "></span>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="wjportal-jobs-list-btm-wrp">
                                        <div class="wjportal-jobs-action-wrp"><a
                                                class="wjportal-jobs-act-btn wjportal-list-act-btn-edit"
                                                job=""
                                                href="#"><?php echo esc_html(__('Edit Job', 'wp-job-portal')); ?></a>
                                            <a class="wjportal-jobs-act-btn wjportal-list-act-btn-delete"
                                                href="#"
                                                ><?php echo esc_html(__('Delete Job', 'wp-job-portal')); ?></a>
                                            <a class="wjportal-jobs-act-btn wjportal-list-act-btn-copy-job" href="#"
                                                >
                                                <?php echo esc_html(__('Copy Job', 'wp-job-portal')); ?></a>
                                            <a class="wjportal-jobs-act-btn wjportal-jobs-apply-res wjportal-list-act-btn-applied-resumes"
                                                title="<?php echo esc_attr__('Resume', 'wp-job-portal'); ?>"
                                                href="#"><?php echo esc_html(__('Resume', 'wp-job-portal')); ?>
                                                (0)</a> <a
                                                class="wjportal-jobs-act-btn wjportal-jobs-act-btn-ai-suggested-resumes"
                                                href="#"><?php echo esc_html(__('Suggested Resumes', 'wp-job-portal')); ?></a>
                                        </div>
                                    </div>
                                </div>

                                <div class="wjportal-jobs-list  wpjobportal-list-item-status-approved  ">
                                    <div class="wjportal-jobs-list-top-wrp object_10" data-boxid="job_10">
                                        <div class="wjportal-jobs-logo">
                                            <a
                                                href="#">
                                                <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/default_logo.png"
                                                    alt="<?php echo esc_attr__('Company logo', 'wp-job-portal'); ?>">
                                            </a>
                                        </div>

                                        <div class="wjportal-jobs-cnt-wrp">
                                            <div class="wjportal-jobs-middle-wrp">
                                                <div class="wjportal-jobs-data">
                                                </div>
                                                <div class="wjportal-jobs-data">
                                                    <span class="wjportal-job-title">
                                                        <a
                                                            href="#">
                                                            <?php echo esc_html(__('PHP Developer', 'wp-job-portal')); ?></a>
                                                    </span>
                                                    <span class="wjportal-item-status"
                                                        style="background:var(--wpjp-ds-color-success, #16803c);"><?php echo esc_html(__('Publish', 'wp-job-portal')); ?></span>
                                                    <a class="wjportal-companyname"
                                                        href="#"><?php echo esc_html(__('Buruj Solution', 'wp-job-portal')); ?></a>
                                                </div>
                                                <div class="wjportal-jobs-data">
                                                    <span class="wjportal-jobs-data-text wjportal-jobs-data-icon-class-category">
                                                        <?php echo esc_html(__('Computer/IT', 'wp-job-portal')); ?></span>
                                                    <span class="wjportal-jobs-data-text wjportal-jobs-data-icon-class-location">
                                                        <?php echo esc_html(__('Gujranwala, Pakistan', 'wp-job-portal')); ?></span>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="wjportal-jobs-right-wrp">
                                                <div class="wjportal-jobs-info">
                                                    <span class="wjportal-job-type" style="--wpjp-jobtype-color:#00abfa">
                                                        <?php echo esc_html(__('Full-Time', 'wp-job-portal')); ?>
                                                    </span>
                                                </div>
                                                <div class="wjportal-jobs-info">
                                                    <div class="wjportal-jobs-salary">
                                                        1,000 - 1,500 $ <span class="wjportal-salary-type"> /
                                                            <?php echo esc_html(__('Per Month', 'wp-job-portal')); ?></span>
                                                    </div>
                                                </div>
                                                <div class="wjportal-jobs-info">
                                                    8 <?php echo esc_html(__('hours Ago', 'wp-job-portal')); ?> </div>
                                                <div class="wjportal-jobs-status">
                                                    <span class="wjportal-jobs-status-text "></span>
                                                </div>

                                            </div>

                                        </div>
                                    </div>
                                    <div class="wjportal-jobs-list-btm-wrp">
                                        <div class="wjportal-jobs-action-wrp">
                                            <a class="wjportal-jobs-act-btn wjportal-list-act-btn-edit" job="" href="#/10"><?php echo esc_html(__('Edit Job', 'wp-job-portal')); ?></a>
                                            <a href="#" data-spectype="featured" id="featuredjob10" data-anchorid="featuredjob10" class="wjportal-jobs-act-btn  wjportal-list-act-btn-featured"> <?php echo esc_html(__('Add Featured', 'wp-job-portal')); ?></a>
                                            <a class="wjportal-jobs-act-btn wjportal-list-act-btn-delete" href="#"><?php echo esc_html(__('Delete Job', 'wp-job-portal')); ?></a>
                                            <a class="wjportal-jobs-act-btn wjportal-list-act-btn-copy-job" href="#"><?php echo esc_html(__('Copy Job', 'wp-job-portal')); ?></a>
                                            <a class="wjportal-jobs-act-btn wjportal-jobs-apply-res wjportal-list-act-btn-applied-resumes" title="<?php echo esc_attr__('Resume', 'wp-job-portal'); ?>"href="#"><?php echo esc_html(__('Resume', 'wp-job-portal')); ?>(1)</a>
                                            <a class="wjportal-jobs-act-btn wjportal-jobs-act-btn-ai-suggested-resumes" href="#"><?php echo esc_html(__('Suggested Resumes', 'wp-job-portal')); ?></a>
                                        </div>
                                    </div>
                                </div>

                                <div class="wjportal-jobs-list  wpjobportal-list-item-status-approved  ">
                                    <div class="wjportal-jobs-list-top-wrp object_10" data-boxid="job_10">
                                        <div class="wjportal-jobs-logo">
                                            <a
                                                href="#">
                                                <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/default_logo.png"
                                                    alt="<?php echo esc_attr__('Company logo', 'wp-job-portal'); ?>">
                                            </a>
                                        </div>

                                        <div class="wjportal-jobs-cnt-wrp">
                                            <div class="wjportal-jobs-middle-wrp">
                                                <div class="wjportal-jobs-data">
                                                </div>
                                                <div class="wjportal-jobs-data">
                                                    <span class="wjportal-job-title">
                                                        <a
                                                            href="#">
                                                            <?php echo esc_html(__('Accountant', 'wp-job-portal')); ?></a>
                                                    </span>
                                                    <span class="wjportal-item-status"
                                                        style="background:var(--wpjp-ds-color-success, #16803c);"><?php echo esc_html(__('Publish', 'wp-job-portal')); ?></span>
                                                    <a class="wjportal-companyname"
                                                        href="#"><?php echo esc_html(__('Buruj Solution', 'wp-job-portal')); ?></a>
                                                </div>
                                                <div class="wjportal-jobs-data">
                                                    <span class="wjportal-jobs-data-text wjportal-jobs-data-icon-class-category" >
                                                        <?php echo esc_html(__('Computer/IT', 'wp-job-portal')); ?></span>
                                                    <span class="wjportal-jobs-data-text wjportal-jobs-data-icon-class-location">
                                                        <?php echo esc_html(__('Lahore, Pakistan', 'wp-job-portal')); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="wjportal-jobs-right-wrp">
                                                <div class="wjportal-jobs-info">
                                                    <span class="wjportal-job-type" style="--wpjp-jobtype-color:#00abfa">
                                                        <?php echo esc_html(__('Full-Time', 'wp-job-portal')); ?>
                                                    </span>
                                                </div>
                                                <div class="wjportal-jobs-info">
                                                    <div class="wjportal-jobs-salary">
                                                        1,000 - 1,500 $ <span class="wjportal-salary-type"> /
                                                            <?php echo esc_html(__('Per Month', 'wp-job-portal')); ?></span>
                                                    </div>
                                                </div>
                                                <div class="wjportal-jobs-info">
                                                    8 <?php echo esc_html(__('hours Ago', 'wp-job-portal')); ?> </div>
                                                <div class="wjportal-jobs-status">
                                                    <span class="wjportal-jobs-status-text "></span>
                                                </div>

                                            </div>

                                        </div>
                                    </div>
                                    <div class="wjportal-jobs-list-btm-wrp">
                                        <div class="wjportal-jobs-action-wrp"><a
                                                class="wjportal-jobs-act-btn wjportal-list-act-btn-edit"
                                                job=""
                                                href="#/10"><?php echo esc_html(__('Edit Job', 'wp-job-portal')); ?></a>
                                            <a href="#" data-spectype="featured" id="featuredjob10"
                                                data-anchorid="featuredjob10"
                                                class="wjportal-jobs-act-btn  wjportal-list-act-btn-featured"
                                                >
                                                <?php echo esc_html(__('Add Featured', 'wp-job-portal')); ?></a>
                                            <a class="wjportal-jobs-act-btn wjportal-list-act-btn-delete"
                                                href="#"
                                                ><?php echo esc_html(__('Delete Job', 'wp-job-portal')); ?></a>
                                            <a class="wjportal-jobs-act-btn wjportal-list-act-btn-copy-job" href="#"
                                                >
                                                <?php echo esc_html(__('Copy Job', 'wp-job-portal')); ?></a>
                                            <a class="wjportal-jobs-act-btn wjportal-jobs-apply-res wjportal-list-act-btn-applied-resumes"
                                                title="<?php echo esc_attr__('Resume', 'wp-job-portal'); ?>"
                                                href="#"><?php echo esc_html(__('Resume', 'wp-job-portal')); ?>
                                                (0)</a> <a
                                                class="wjportal-jobs-act-btn wjportal-jobs-act-btn-ai-suggested-resumes"
                                                href="#"><?php echo esc_html(__('Suggested Resumes', 'wp-job-portal')); ?></a>
                                        </div>
                                    </div>
                                </div>

                                <div class="wjportal-jobs-list  wpjobportal-list-item-status-approved  ">
                                    <div class="wjportal-jobs-list-top-wrp object_10" data-boxid="job_10">
                                        <div class="wjportal-jobs-logo">
                                            <a
                                                href="#">
                                                <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/default_logo.png"
                                                    alt="<?php echo esc_attr__('Company logo', 'wp-job-portal'); ?>">
                                            </a>
                                        </div>

                                        <div class="wjportal-jobs-cnt-wrp">
                                            <div class="wjportal-jobs-middle-wrp">
                                                <div class="wjportal-jobs-data">
                                                </div>
                                                <div class="wjportal-jobs-data">
                                                    <span class="wjportal-job-title">
                                                        <a
                                                            href="#">
                                                            <?php echo esc_html(__('Database Administrator', 'wp-job-portal')); ?></a>
                                                    </span>
                                                    <span class="wjportal-item-status" style="background:var(--wpjp-ds-color-success, #16803c);">
                                                        <?php echo esc_html(__('Publish', 'wp-job-portal')); ?></span>
                                                    <a class="wjportal-companyname"
                                                        href="#"><?php echo esc_html(__('Buruj Solution', 'wp-job-portal')); ?></a>
                                                </div>
                                                <div class="wjportal-jobs-data">
                                                    <span class="wjportal-jobs-data-text wjportal-jobs-data-icon-class-category">
                                                        <?php echo esc_html(__('Computer/IT', 'wp-job-portal')); ?></span>
                                                    <span class="wjportal-jobs-data-text wjportal-jobs-data-icon-class-location">
                                                        <?php echo esc_html(__('Karachi, Pakistan', 'wp-job-portal')); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="wjportal-jobs-right-wrp">
                                                <div class="wjportal-jobs-info">
                                                    <span class="wjportal-job-type" style="--wpjp-jobtype-color:#00abfa">
                                                        <?php echo esc_html(__('Full-Time', 'wp-job-portal')); ?>
                                                    </span>
                                                </div>
                                                <div class="wjportal-jobs-info">
                                                    <div class="wjportal-jobs-salary">
                                                        1,000 - 1,500 $ <span class="wjportal-salary-type"> /
                                                            <?php echo esc_html(__('Per Month', 'wp-job-portal')); ?></span>
                                                    </div>
                                                </div>
                                                <div class="wjportal-jobs-info">
                                                    8 <?php echo esc_html(__('hours Ago', 'wp-job-portal')); ?> </div>
                                                <div class="wjportal-jobs-status">
                                                    <span class="wjportal-jobs-status-text "></span>
                                                </div>

                                            </div>

                                        </div>
                                    </div>
                                    <div class="wjportal-jobs-list-btm-wrp">
                                        <div class="wjportal-jobs-action-wrp"><a
                                                class="wjportal-jobs-act-btn wjportal-list-act-btn-edit"
                                                job=""
                                                href="#/10"><?php echo esc_html(__('Edit Job', 'wp-job-portal')); ?></a>
                                            <a href="#" data-spectype="featured" id="featuredjob10"
                                                data-anchorid="featuredjob10"
                                                class="wjportal-jobs-act-btn  wjportal-list-act-btn-featured"
                                                >
                                                <?php echo esc_html(__('Add Featured', 'wp-job-portal')); ?></a>
                                            <a class="wjportal-jobs-act-btn wjportal-list-act-btn-delete"
                                                href="#"
                                                ><?php echo esc_html(__('Delete Job', 'wp-job-portal')); ?></a>
                                            <a class="wjportal-jobs-act-btn wjportal-list-act-btn-copy-job" href="#"
                                                >
                                                <?php echo esc_html(__('Copy Job', 'wp-job-portal')); ?></a>
                                            <a class="wjportal-jobs-act-btn wjportal-jobs-apply-res wjportal-list-act-btn-applied-resumes"
                                                title="<?php echo esc_attr__('Resume', 'wp-job-portal'); ?>"
                                                href="#"><?php echo esc_html(__('Resume', 'wp-job-portal')); ?>
                                                (0)</a> <a
                                                class="wjportal-jobs-act-btn wjportal-jobs-act-btn-ai-suggested-resumes"
                                                href="#"><?php echo esc_html(__('Suggested Resumes', 'wp-job-portal')); ?></a>
                                        </div>
                                    </div>
                                </div>

                                <input type="hidden" name="sortby" id="sortby" value="2"><input type="hidden"
                                    name="sorton" id="sorton" value="6"><input type="hidden"
                                    name="WPJOBPORTAL_form_search" id="WPJOBPORTAL_form_search"
                                    value="WPJOBPORTAL_SEARCH"><input type="hidden" name="wpjobportallay"
                                    id="wpjobportallay" value="appliedjobs">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
wp_register_script( 'wpjobportal-inline-handle', '', array(), WPJOBPORTAL_VERSION, false );
wp_enqueue_script('wpjobportal-inline-handle');

$wpjobportal_inline_js_script = "
        /* Under a design add-on the preview reads the primary colour from one
           custom property, as the front end does; nothing is painted inline. */
        var wpjpLookActive = " . ($wpjobportal_look_active ? 'true' : 'false') . ";
        function wpjpLookPrimary(hex) {
            jQuery('.js_effect_preview .wjportal-main-up-wrapper').each(function () {
                this.style.setProperty('--primary-color', hex);
            });
        }
        jQuery(document).ready(function () {
            makeColorPicker('" . esc_js(wpjobportal::$_data[0]['color1']) . "', '" . esc_js(wpjobportal::$_data[0]['color2']) . "', '" . esc_js(wpjobportal::$_data[0]['color3']) . "');

        function makeColorPicker(color1, color2, color3) {
            jQuery('input#color1').iris({
                color: color1,
                onShow: function (colpkr) {
                    jQuery(colpkr).fadeIn(500);
                    return false;
                },
                onHide: function (colpkr) {
                    jQuery(colpkr).fadeOut(500);
                    return false;
                },
                change: function (c_event, ui) {
                    hex = ui.color.toString();
                    if (wpjpLookActive) {
                        wpjpLookPrimary(hex);
                        jQuery('input#color1').attr('style', 'background-color: ' + hex + ' !important;');
                        return;
                    }
                    jQuery('div#wpjobportal-header-main-wrapper').css('background-color', hex);
                    jQuery('.wjportal-jobs-act-btn.wjportal-jobs-act-btn-ai-suggested-resumes').css('background-color', hex);
                    jQuery('.wjportal-jobs-act-btn.wjportal-jobs-act-btn-ai-suggested-resumes').css('borderColor', hex);
                    jQuery('.wjportal-act-btn-wrp a.wjportal-act-btn').css('background-color', hex);
                    jQuery('div.wjportal-breadcrumbs-wrp div.wjportal-breadcrumbs-links a.wjportal-breadcrumbs-link').css('color', hex);
                    jQuery('div.wjportal-jobs-list div.wjportal-jobs-list-top-wrp div.wjportal-jobs-cnt-wrp div.wjportal-jobs-middle-wrp div.wjportal-jobs-data a.wjportal-companyname').css('color', hex);
                    jQuery('a.headerlinks').mouseover(function () {
                        jQuery(this).css('color', '#' + hex);
                    });
                    jQuery('a.headerlinks').mouseout(function () {
                        jQuery(this).css('color', jQuery('input#color7').val());
                    });
                    jQuery('span.wjportal-featured-tag-icon-wrp span.wjportal-featured-tag-icon').css('background-color',hex);
                    jQuery('input#color1').attr('style', 'background-color: ' + hex + ' !important;');
                    jQuery('.wjportal-jobs-act-btn.wjportal-jobs-act-btn-ai-suggested-resumes').hover(function () {
                        jQuery(this).css('borderColor', jQuery('input#color2').val());
                        jQuery(this).css('background-color', jQuery('input#color2').val());
                        jQuery(this).css('color', '#fff');
                    }, function () {
                        jQuery(this).css('borderColor', jQuery('input#color1').val());
                        jQuery(this).css('color', jQuery('input#color2').val());
                        jQuery(this).css('background-color', jQuery('input#color1').val());
                    })
                    jQuery('.wjportal-act-btn-wrp a.wjportal-act-btn').hover(function () {
                        jQuery(this).css('borderColor', jQuery('input#color2').val());
                        jQuery(this).css('background-color', jQuery('input#color2').val());
                        jQuery(this).css('color', '#fff');
                    }, function () {
                        jQuery(this).css('borderColor', jQuery('input#color1').val());
                        jQuery(this).css('background-color', jQuery('input#color1').val());
                        jQuery(this).css('color', '#fff');

                    })
                    jQuery('div.wjportal-jobs-list div.wjportal-jobs-list-top-wrp div.wjportal-jobs-cnt-wrp div.wjportal-jobs-middle-wrp div.wjportal-jobs-data a.wjportal-companyname').hover(function () {
                        jQuery(this).css('color', jQuery('input#color2').val());
                    }, function () {
                        jQuery(this).css('color', jQuery('input#color1').val());
                    })

                }
            });
            jQuery('input#color2').iris({
                color: color2,
                onShow: function (colpkr) {
                    jQuery(colpkr).fadeIn(500);
                    return false;
                },
                onHide: function (colpkr) {
                    jQuery(colpkr).fadeOut(500);
                    return false;
                },
                change: function (c_event, ui) {
                    hex = ui.color.toString()
                    if (wpjpLookActive) {
                        jQuery('input#color2').attr('style', 'background-color: ' + hex + ' !important;');
                        return;
                    }
                    jQuery('div.wjportal-jobs-list div.wjportal-jobs-list-top-wrp div.wjportal-jobs-cnt-wrp div.wjportal-jobs-right-wrp div.wjportal-jobs-info div.wjportal-jobs-salary').css('color', hex);
                    jQuery('div.wjportal-jobs-list div.wjportal-jobs-list-top-wrp div.wjportal-jobs-cnt-wrp div.wjportal-jobs-middle-wrp div.wjportal-jobs-data span.wjportal-job-title a').css('color',hex);
                    jQuery('div.wjportal-jobs-list div.wjportal-jobs-list-btm-wrp div.wjportal-jobs-action-wrp a.wjportal-jobs-act-btn').css('color',hex);
                    jQuery('div.wjportal-jobs-list div.wjportal-jobs-list-top-wrp div.wjportal-jobs-cnt-wrp div.wjportal-jobs-right-wrp div.wjportal-jobs-info').css('color', hex);
                    jQuery('.wjportal-page-heading').css('color', hex);
                    jQuery('div.wjportal-jobs-list.wpjobportal-list-item-is-featured div.wjportal-jobs-list-top-wrp div.wjportal-jobs-cnt-wrp div.wjportal-jobs-middle-wrp div.wjportal-jobs-data span.wjportal-jobs-data-text').css('color', hex);
                    jQuery('div.wjportal-jobs-list div.wjportal-jobs-list-btm-wrp div.wjportal-jobs-action-wrp a.wjportal-jobs-act-btn').hover(function () {
                        jQuery(this).css('borderColor', jQuery('input#color2').val());
                        jQuery(this).css('background-color', jQuery('input#color2').val());
                        jQuery(this).css('color', '#fff');
                    }, function () {
                        jQuery(this).css('borderColor', '#e9ecef');
                        jQuery(this).css('color', jQuery('input#color2').val());
                        jQuery(this).css('background-color', '#fff');
                    })
                    jQuery('.wjportal-jobs-act-btn.wjportal-jobs-act-btn-ai-suggested-resumes').hover(function () {
                        jQuery(this).css('borderColor', jQuery('input#color2').val());
                        jQuery(this).css('background-color', jQuery('input#color2').val());
                        jQuery(this).css('color', '#fff');
                    }, function () {
                        jQuery(this).css('borderColor', jQuery('input#color1').val());
                        jQuery(this).css('color', jQuery('input#color2').val());
                        jQuery(this).css('background-color', jQuery('input#color1').val());
                    })
                    jQuery('.wjportal-act-btn-wrp a.wjportal-act-btn').hover(function () {
                        jQuery(this).css('borderColor', jQuery('input#color2').val());
                        jQuery(this).css('background-color', jQuery('input#color2').val());
                        jQuery(this).css('color', '#fff');
                    }, function () {
                        jQuery(this).css('borderColor', jQuery('input#color1').val());
                        jQuery(this).css('background-color', jQuery('input#color1').val());
                        jQuery(this).css('color', '#fff');
                    })
                    jQuery('div.wjportal-jobs-list div.wjportal-jobs-list-top-wrp div.wjportal-jobs-cnt-wrp div.wjportal-jobs-middle-wrp div.wjportal-jobs-data a.wjportal-companyname').hover(function () {
                        jQuery(this).css('color', jQuery('input#color2').val());
                    }, function () {
                        jQuery(this).css('color', jQuery('input#color1').val());
                    })
                    jQuery('input#color2').attr('style', 'background-color: ' + hex + ' !important;');
                }
            });
            jQuery('input#color3').iris({
                color: color3,
                onShow: function (colpkr) {
                    jQuery(colpkr).fadeIn(500);
                    return false;
                },
                onHide: function (colpkr) {
                    jQuery(colpkr).fadeOut(500);
                    return false;
                },
                change: function (c_event, ui) {
                    hex = ui.color.toString()
                    if (wpjpLookActive) {
                        jQuery('input#color3').attr('style', 'background-color: ' + hex + ' !important;');
                        return;
                    }
                    jQuery('div.wjportal-jobs-list div.wjportal-jobs-list-top-wrp div.wjportal-jobs-cnt-wrp div.wjportal-jobs-middle-wrp div.wjportal-jobs-data span.wjportal-jobs-data-text').css('color', hex);
                    jQuery('.wjportal-breadcrumbs-links.wjportal-breadcrumbs-lastlink').css('color', hex);
                    jQuery('.wjportal-main-up-wrapper').css('color', hex);
                    jQuery('div.wjportal-jobs-list div.wjportal-jobs-list-top-wrp div.wjportal-jobs-cnt-wrp div.wjportal-jobs-right-wrp div.wjportal-jobs-info').css('color', hex);
                    jQuery('.wjportal-main-up-wrapper select')
                    .attr('style', 'color: ' + hex + ' !important');
                    jQuery('input#color3').attr('style', 'background-color: ' + hex + ' !important;');
                    jQuery('span.wjportal-jobs-data-text')
                        .css('--wpjp-body-font-color', hex)
                        .css('color', hex);
                    jQuery('div.wjportal-jobs-list.wpjobportal-list-item-is-featured div.wjportal-jobs-list-top-wrp div.wjportal-jobs-cnt-wrp div.wjportal-jobs-middle-wrp div.wjportal-jobs-data span.wjportal-jobs-data-text')
                        .css('color', jQuery('input#color2').val())
                                                    }

            });
            jQuery(document).click(function (e) {
                if (!jQuery(e.target).is('#color1, #color2, #color3')) {
                    jQuery('#color1, #color2, #color3').iris('hide');
                }
            });
            jQuery('#color1, #color2, #color3').click(function (event) {
                jQuery('#color1, #color2, #color3').iris('hide');
                jQuery(this).iris('show');
                return false;
            });
        }
        });
    ";
wp_add_inline_script('wpjobportal-inline-handle', $wpjobportal_inline_js_script);
?>
<?php
if ($wpjobportal_look_active) {
    /*
     * The Elegant Design fields: each one sets the custom property its
     * data-wpjp-var names on the preview, the same property the variables
     * sheet writes for the front end, so the preview is the site.
     */
    $wpjobportal_inline_js_script = "
        function wpjpEdPreview(field) {
            var el = jQuery(field), wrap = jQuery('.js_effect_preview .wjportal-main-up-wrapper');
            if (el.hasClass('wpjp-ed-choice')) {
                var sets = el.data('wpjp-vars') || {}, set = sets[el.val()] || {};
                wrap.each(function () {
                    for (var name in set) { this.style.setProperty(name, set[name]); }
                });
                /* Square also flattens the fixed corners; the same rule the
                   variables sheet gets, limited to the preview. */
                if (el.data('wpjp-square')) {
                    var tag = jQuery('#wpjp-ed-square-preview');
                    if (!tag.length) { tag = jQuery('<style id=\"wpjp-ed-square-preview\"></style>').appendTo('head'); }
                    tag.text(el.val() === 'square' ? el.data('wpjp-square') : '');
                }
                return;
            }
            var value = el.val();
            if (el.hasClass('wpjp-ed-size')) {
                var n = parseInt(value, 10), min = parseInt(el.attr('min'), 10), max = parseInt(el.attr('max'), 10);
                if (isNaN(n)) { n = parseInt(el.data('wpjp-default'), 10); }
                value = Math.min(max, Math.max(min, n)) + 'px';
            } else if (!/^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(value)) {
                return;
            }
            wrap.each(function () { this.style.setProperty(el.data('wpjp-var'), value); });
        }
        function wpjpEdSwatch(el, hex) {
            var h = hex.replace('#', '');
            if (h.length === 3) { h = h[0] + h[0] + h[1] + h[1] + h[2] + h[2]; }
            var lum = 0.299 * parseInt(h.substr(0, 2), 16) + 0.587 * parseInt(h.substr(2, 2), 16) + 0.114 * parseInt(h.substr(4, 2), 16);
            el.attr('style', 'background-color: ' + hex + ' !important; color: ' + (lum > 160 ? '#1d2327' : '#ffffff') + ' !important;');
        }
        function wpjpEdSet(field, value) {
            var el = jQuery(field);
            el.val(value);
            if (el.hasClass('wpjp-ed-color')) {
                el.iris('color', value);
                wpjpEdSwatch(el, value);
            }
            wpjpEdPreview(el);
        }
        jQuery(document).ready(function () {
            jQuery('input.wpjp-ed-color').each(function () {
                var el = jQuery(this);
                el.iris({
                    color: el.val(),
                    change: function (c_event, ui) {
                        var hex = ui.color.toString();
                        wpjpEdSwatch(el, hex);
                        el.val(hex);
                        wpjpEdPreview(el);
                    }
                });
            });
            jQuery('input.wpjp-ed-color').on('click', function () {
                jQuery('#color1, #color2, #color3, input.wpjp-ed-color').not(this).iris('hide');
                jQuery(this).iris('show');
                return false;
            });
            jQuery(document).on('click', function (e) {
                if (!jQuery(e.target).closest('.iris-picker, input.wpjp-ed-color').length) {
                    jQuery('input.wpjp-ed-color').iris('hide');
                }
            });
            jQuery('.wpjp-ed-field').on('input change', function () { wpjpEdPreview(this); });
            /* Fills the fields with Elegant Design's defaults; nothing is
               stored until Save Changes. */
            jQuery('#wpjpResetElegant').on('click', function (e) {
                e.preventDefault();
                jQuery('.wpjp-ed-field').each(function () {
                    wpjpEdSet(this, String(jQuery(this).data('wpjp-default')));
                });
            });
            /* A preset fills this design's primary, heading and text
               colours, as it does the standard design's on that design. */
            jQuery('a.set_theme').on('click', function (e) {
                e.preventDefault();
                var div = jQuery(this).parent();
                wpjpEdSet('#ed_color1', rgb2hex(div.find('div.1').css('background-color')));
                wpjpEdSet('#ed_color2', rgb2hex(div.find('div.2').css('background-color')));
                wpjpEdSet('#ed_color3', rgb2hex(div.find('div.3').css('background-color')));
            });
        });
    ";
    wp_add_inline_script('wpjobportal-inline-handle', $wpjobportal_inline_js_script);
}
$wpjobportal_inline_js_script = "
        jQuery(document).ready(function () {
            jQuery('#saveColors').click(function (e) {
                jQuery('form#adminForm').submit();
            });
            /* Puts the four sizes back to the shipped defaults. It fills the
               fields and leaves them; nothing is stored until Save Changes, so
               a Reset pressed by mistake costs nothing. */
            jQuery('#wpjpResetFontSizes').on('click', function (e) {
                e.preventDefault();
                jQuery('.wpjp-font-size-field').each(function () {
                    var d = jQuery(this).data('wpjp-default');
                    if (typeof d !== 'undefined' && d !== '') {
                        jQuery(this).val(d);
                    }
                });
            });
            jQuery('a#preset_theme').click(function (e) {
                e.preventDefault();
                jQuery('div#js_jobapply_main_wrapper').fadeIn();
                jQuery('div#black_wrapper_jobapply').fadeIn();
            });
            jQuery('div#black_wrapper_jobapply , img.closepp').click(function () {
                jQuery('div#js_jobapply_main_wrapper').fadeOut();
                jQuery('div#black_wrapper_jobapply').fadeOut();
            });
            jQuery('a.preview').each(function (index, element) {
                jQuery(this).hover(function () {
                    if (index > 2)
                        jQuery(this).parent().find('img.preview').css('top', '-110px');
                    jQuery(jQuery(this).parent().find('img.preview')).show();
                }, function () {
                    jQuery(jQuery(this).parent().find('img.preview')).hide();
                });
            });
            jQuery('a.set_theme').each(function (index, element) {
                jQuery(this).click(function (e) {
                    e.preventDefault();
                    var div = jQuery(this).parent();
                    var color1 = rgb2hex(jQuery(div.find('div.1')).css('background-color'));
                    var color2 = rgb2hex(jQuery(div.find('div.2')).css('background-color'));
                    var color3 = rgb2hex(jQuery(div.find('div.3')).css('background-color'));
                    /* Under a design add-on the preset fills that design's own
                       fields (its handler); the standard design's stay as saved. */
                    if (wpjpLookActive) {
                        return;
                    }


                    jQuery('input#color1').attr('style', 'background-color: ' + color1 + ' !important;');
                    jQuery('input#color2').attr('style', 'background-color: ' + color2 + ' !important;');
                    jQuery('input#color3').attr('style', 'background-color: ' + color3 + ' !important;');
                    jQuery('input#color1').val(color1);
                    jQuery('input#color2').val(color2);
                    jQuery('input#color3').val(color3);

                    jQuery('input.submit-button').css('background-color', color2);
                    themeSelectionEffect();
                    jQuery('div#js_jobapply_main_wrapper').fadeOut();
                    jQuery('div#black_wrapper_jobapply').fadeOut();
                });
            });
        });
        function rgb2hex(rgb) {
            rgb = rgb.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+))?\)$/);
            function hex(x) {
                return ('0' + parseInt(x).toString(16)).slice(-2);
            }
            return '#' + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
        }
        function themeSelectionEffect() {
            if (wpjpLookActive) {
                wpjpLookPrimary(jQuery('input#color1').val());
                return;
            }
            jQuery('div.wjportal-jobs-list div.wjportal-jobs-list-top-wrp div.wjportal-jobs-cnt-wrp div.wjportal-jobs-middle-wrp div.wjportal-jobs-data a.wjportal-companyname').css('color', jQuery('input#color1').val());
            jQuery('div.wjportal-jobs-list div.wjportal-jobs-list-top-wrp div.wjportal-jobs-cnt-wrp div.wjportal-jobs-middle-wrp div.wjportal-jobs-data a.wjportal-companyname').hover(function () {
                jQuery(this).css('color', jQuery('input#color2').val());
            }, function () {
                    jQuery(this).css('color', jQuery('input#color1').val());
            })
            jQuery('div.wjportal-jobs-list div.wjportal-jobs-list-top-wrp div.wjportal-jobs-cnt-wrp div.wjportal-jobs-middle-wrp div.wjportal-jobs-data span.wjportal-job-title a').css('color', jQuery('input#color2').val());
            jQuery('div.wjportal-jobs-list div.wjportal-jobs-list-top-wrp div.wjportal-jobs-cnt-wrp div.wjportal-jobs-middle-wrp div.wjportal-jobs-data span.wjportal-jobs-data-text').css('color', jQuery('input#color3').val());
            jQuery('span.wjportal-featured-tag-icon-wrp span.wjportal-featured-tag-icon').css('background-color', jQuery('input#color1').val());
            jQuery('.wjportal-page-heading').css('color', jQuery('input#color2').val());
            jQuery('div.wjportal-breadcrumbs-wrp div.wjportal-breadcrumbs-links a.wjportal-breadcrumbs-link').css('color', jQuery('input#color1').val());
            jQuery('.wjportal-breadcrumbs-links.wjportal-breadcrumbs-lastlink').css('color', jQuery('input#color3').val());
            jQuery('.wjportal-main-up-wrapper select')
            .attr('style', 'color: ' + jQuery('input#color3').val() + ' !important;');
            jQuery('div.wjportal-jobs-list div.wjportal-jobs-list-top-wrp div.wjportal-jobs-cnt-wrp div.wjportal-jobs-right-wrp div.wjportal-jobs-info').css('color', jQuery('input#color3').val());
            jQuery('.wjportal-main-up-wrapper').css('color', jQuery('input#color3').val());
            jQuery('div.wjportal-jobs-list div.wjportal-jobs-list-top-wrp div.wjportal-jobs-cnt-wrp div.wjportal-jobs-right-wrp div.wjportal-jobs-info div.wjportal-jobs-salary').css('color', jQuery('input#color2').val());
            jQuery('div.wjportal-jobs-list.wpjobportal-list-item-is-featured div.wjportal-jobs-list-top-wrp div.wjportal-jobs-cnt-wrp div.wjportal-jobs-middle-wrp div.wjportal-jobs-data span.wjportal-jobs-data-text').css('color', jQuery('input#color2').val());
            jQuery('div.wjportal-jobs-list div.wjportal-jobs-list-btm-wrp div.wjportal-jobs-action-wrp a.wjportal-jobs-act-btn').css('color', jQuery('input#color2').val());
            jQuery('div.wjportal-jobs-list div.wjportal-jobs-list-btm-wrp div.wjportal-jobs-action-wrp a.wjportal-jobs-act-btn').hover(function () {
                jQuery(this).css('borderColor', jQuery('input#color2').val());
                jQuery(this).css('background-color', jQuery('input#color2').val());
                jQuery(this).css('color', '#fff');
            }, function () {
                jQuery(this).css('borderColor', '#e9ecef');
                jQuery(this).css('color', jQuery('input#color2').val());
                jQuery(this).css('background-color', '#fff');
            })
            jQuery('.wjportal-act-btn-wrp a.wjportal-act-btn').css('background-color', jQuery('input#color1').val());
            jQuery('.wjportal-act-btn-wrp a.wjportal-act-btn').hover(function () {
                jQuery(this).css('borderColor', jQuery('input#color2').val());
                jQuery(this).css('background-color', jQuery('input#color2').val());
                jQuery(this).css('color', '#fff');
            }, function () {
                jQuery(this).css('borderColor', jQuery('input#color1').val());
                jQuery(this).css('background-color', jQuery('input#color1').val());
                jQuery(this).css('color', '#fff');
            })
            jQuery('.wjportal-jobs-act-btn.wjportal-jobs-act-btn-ai-suggested-resumes').css('background-color', jQuery('input#color1').val());
            jQuery('.wjportal-jobs-act-btn.wjportal-jobs-act-btn-ai-suggested-resumes').css('borderColor', jQuery('input#color1').val());
            jQuery('.wjportal-jobs-act-btn.wjportal-jobs-act-btn-ai-suggested-resumes').hover(function () {
                jQuery(this).css('borderColor', jQuery('input#color2').val());
                jQuery(this).css('background-color', jQuery('input#color2').val());
                jQuery(this).css('color', '#fff');
            }, function () {
                jQuery(this).css('borderColor', jQuery('input#color1').val());
                jQuery(this).css('background-color', jQuery('input#color1').val());
                jQuery(this).css('color', '#fff');
            })
            jQuery('div.wjportal-jobs-list div.wjportal-jobs-list-btm-wrp div.wjportal-jobs-action-wrp a.wjportal-jobs-apply-res').css('color', jQuery('input#color2').val());
            jQuery('div.wjportal-jobs-list div.wjportal-jobs-list-btm-wrp div.wjportal-jobs-action-wrp a.wjportal-jobs-apply-res').hover(function () {
                jQuery(this).css('borderColor', jQuery('input#color2').val());
                jQuery(this).css('background-color', jQuery('input#color2').val());
                jQuery(this).css('color', '#fff');
            }, function () {
                jQuery(this).css('borderColor', '#d4d4d5');
                jQuery(this).css('color', jQuery('input#color2').val());
                jQuery(this).css('background-color', '#fff');
            })
            jQuery('span.wjportal-jobs-data-text')
            .css('--wpjp-body-font-color', jQuery('input#color3').val())
            .css('color', jQuery('input#color3').val());
        }
    ";
wp_add_inline_script('wpjobportal-inline-handle', $wpjobportal_inline_js_script);
?>