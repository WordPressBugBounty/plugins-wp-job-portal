<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALthemeModel {
    function getCurrentTheme() {


        $wpjobportal_color1 = "#3baeda";
        $wpjobportal_color2 = "#333333";
        $wpjobportal_color3 = "#575757";

        $wpjobportal_json_values = array();
        $wpjobportal_color_string_values = get_option("wpjp_set_theme_colors");
        if($wpjobportal_color_string_values != ''){
            $wpjobportal_json_values = (array) json_decode($wpjobportal_color_string_values,true);
            if(is_array($wpjobportal_json_values) && !empty($wpjobportal_json_values)){
                $wpjobportal_color1 = $wpjobportal_json_values['color1'];
                $wpjobportal_color2 = $wpjobportal_json_values['color2'];
                $wpjobportal_color3 = $wpjobportal_json_values['color3'];
            }
        }

        $wpjobportal_theme['color1'] = esc_attr(self::wpjpHexColor($wpjobportal_color1, '#3baeda'));
        $wpjobportal_theme['color2'] = esc_attr(self::wpjpHexColor($wpjobportal_color2, '#333333'));
        $wpjobportal_theme['color3'] = esc_attr(self::wpjpHexColor($wpjobportal_color3, '#575757'));

        /*
         * The type scale belongs to whoever runs the site, not to a stylesheet.
         *
         * These four sizes were literals inside the generator, so changing them
         * meant editing CSS - which is exactly what a site owner should never
         * have to do, and what a plugin update would overwrite. They are read
         * here with the published defaults and clamped on the way out, so a
         * stored value that is missing, empty or nonsense still renders a
         * usable page.
         */
        foreach (self::wpjpFontSizeFields() as $wpjobportal_key => $wpjobportal_rules) {
            $wpjobportal_stored = isset($wpjobportal_json_values[$wpjobportal_key])
                ? $wpjobportal_json_values[$wpjobportal_key]
                : null;
            $wpjobportal_theme[$wpjobportal_key] = self::wpjpFontSize($wpjobportal_stored, $wpjobportal_rules);
        }
        $wpjobportal_json_values = self::wpjpWithElegantPrimary($wpjobportal_json_values);
        foreach (self::wpjpElegantFields() as $wpjobportal_key => $wpjobportal_rules) {
            $wpjobportal_theme[$wpjobportal_key] = self::wpjpElegantValue(
                isset($wpjobportal_json_values[$wpjobportal_key]) ? $wpjobportal_json_values[$wpjobportal_key] : null,
                $wpjobportal_rules
            );
        }
        wpjobportal::$_data[0] = $wpjobportal_theme;
        return $wpjobportal_theme;
    }

    function storeTheme($wpjobportal_data) {

        if (empty($wpjobportal_data))
            return false;
        $wpjobportal_data = wpjobportal::wpjobportal_sanitizeData($wpjobportal_data);
        unset($wpjobportal_data['form_request']);

        // Each design shows only its own fields, so a save from one must not
        // wipe what was set under the other: start from what is stored.
        $wpjobportal_stored = json_decode((string) get_option('wpjp_set_theme_colors', ''), true);
        $wpjobportal_stored = self::wpjpWithElegantPrimary(is_array($wpjobportal_stored) ? $wpjobportal_stored : array());
        unset($wpjobportal_stored['form_request']);
        $wpjobportal_data = array_merge($wpjobportal_stored, $wpjobportal_data);
        $wpjobportal_data['color1'] = self::wpjpHexColor(isset($wpjobportal_data['color1']) ? $wpjobportal_data['color1'] : '', '#3baeda');
        $wpjobportal_data['color2'] = self::wpjpHexColor(isset($wpjobportal_data['color2']) ? $wpjobportal_data['color2'] : '', '#333333');
        $wpjobportal_data['color3'] = self::wpjpHexColor(isset($wpjobportal_data['color3']) ? $wpjobportal_data['color3'] : '', '#575757');
        foreach (self::wpjpElegantFields() as $wpjobportal_key => $wpjobportal_rules) {
            $wpjobportal_data[$wpjobportal_key] = self::wpjpElegantValue(
                isset($wpjobportal_data[$wpjobportal_key]) ? $wpjobportal_data[$wpjobportal_key] : null,
                $wpjobportal_rules
            );
        }

        $wpjobportal_elegant = self::wpjpElegantActive();

        // Core's sheet always, so pages outside the design's wrapper keep the
        // site's colours; the design's own sheet too while it is the design.
        $return = $this->wpjpGenerateColorVariablesFile($wpjobportal_data);
        if ($wpjobportal_elegant) {
            $return = $this->wpjpGenerateColorVariablesFileElegant($wpjobportal_data) && $return;
        }

        // Store the sizes as they were applied, not as they were typed. A row
        // holding 999 while the stylesheet says 36px is a record that disagrees
        // with the site, and the next person to read it believes the row.
        foreach (self::wpjpFontSizeFields() as $wpjobportal_key => $wpjobportal_rules) {
            $wpjobportal_data[$wpjobportal_key] = self::wpjpFontSize(
                isset($wpjobportal_data[$wpjobportal_key]) ? $wpjobportal_data[$wpjobportal_key] : null,
                $wpjobportal_rules
            );
        }

        update_option('wpjp_set_theme_colors', wp_json_encode($wpjobportal_data));
        // $return = require(WPJOBPORTAL_PLUGIN_PATH . 'includes/css/style_color.php');
        if($return){
            return WPJOBPORTAL_SAVED;
        } else {
            return WPJOBPORTAL_SAVE_ERROR;
        }
    }

    /**
     * The four sizes the Themes screen owns, with the published defaults and
     * the range each one is allowed to take.
     *
     * The bounds are not decoration. Every one of these values is written
     * verbatim into includes/css/job_portal_variables.css, a file that every
     * front-end page then loads, so an unchecked value is arbitrary CSS - and
     * "16px; } html { display: none" is a defacement typed into a text box.
     * Clamping to an integer count of pixels makes that impossible to express.
     */
    /**
     * The four sizes, with the default and the range each one is held to.
     *
     * Public because the Colors screen reads the defaults from here to fill
     * its Reset control. Stating them twice - once here, once in a button -
     * is how the two quietly come to disagree.
     */
    public static function wpjpFontSizeFields() {
        return array(
            'mainheading'       => array('default' => 32, 'min' => 18, 'max' => 64),
            'secondsubheading'  => array('default' => 27, 'min' => 16, 'max' => 48),
            'subheading'        => array('default' => 22, 'min' => 14, 'max' => 36),
            'bodyfontsize'      => array('default' => 17, 'min' => 12, 'max' => 24),
        );
    }

    /**
     * A stored size as a whole number of pixels inside its allowed range.
     *
     * Anything that is not a number at all - empty, absent, "large", markup -
     * becomes the default rather than nothing, because a missing font-size
     * would hand the page back to whatever the theme happens to set, which is
     * the inconsistency this screen exists to remove.
     */
    private static function wpjpFontSize($wpjobportal_value, $wpjobportal_rules) {
        if (!is_scalar($wpjobportal_value) || !is_numeric(trim((string) $wpjobportal_value))) {
            return (int) $wpjobportal_rules['default'];
        }
        $wpjobportal_size = (int) round((float) trim((string) $wpjobportal_value));
        if ($wpjobportal_size < $wpjobportal_rules['min']) {
            return (int) $wpjobportal_rules['min'];
        }
        if ($wpjobportal_size > $wpjobportal_rules['max']) {
            return (int) $wpjobportal_rules['max'];
        }
        return $wpjobportal_size;
    }

    /**
     * The Design screen's stylesheets, in the page head.
     *
     * Called on admin_enqueue_scripts for this screen only, and again by the
     * template, which needs the answer; the second call adds nothing.
     *
     * With a design add-on active (Elegant Design) the preview is styled the
     * way the front end is: Core's sheets in the wpjp-legacy layer and the
     * add-on's theme unlayered on top.
     *
     * @return bool Whether a design add-on is active.
     */
    public static function enqueueScreenStyles() {
        static $wpjobportal_done = null;
        if ($wpjobportal_done !== null) {
            return $wpjobportal_done;
        }
        $rand = random_int(1,999);
        wp_enqueue_style('wpjobportal-style1', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/theme-interface.css', array('wpjobportal-design-tokens'), wpjobportal_asset_version('includes/css/theme-interface.css'));
        /*
         * With a design add-on active (Elegant Design) the preview is styled the way
         * the front end is: Core's sheets in the wpjp-legacy layer and the add-on's
         * theme unlayered on top. Loaded plainly, style.css would outrank the theme
         * and the preview would not look like the site.
         */
        $wpjobportal_look = apply_filters('wpjobportal_look_provider', array());
        $wpjobportal_look_active = !empty($wpjobportal_look['theme']['src']);
        if ($wpjobportal_look_active) {
            $wpjobportal_look_css = '@layer wpjp-legacy, wpjp-foundation;' . "\n";
            foreach (array('job_portal_variables.css', 'style.css', 'jobseekercp.css', 'employercp.css') as $wpjobportal_core_sheet) {
                $wpjobportal_look_css .= wpjobportal_layered_import('includes/css/' . $wpjobportal_core_sheet, 'wpjp-legacy');
            }
            foreach ((array) (isset($wpjobportal_look['legacy']) ? $wpjobportal_look['legacy'] : array()) as $wpjobportal_sheet) {
                if (!empty($wpjobportal_sheet['src'])) {
                    $wpjobportal_look_css .= wpjobportal_layered_import(
                        $wpjobportal_sheet['src'],
                        'wpjp-legacy',
                        isset($wpjobportal_sheet['media']) ? $wpjobportal_sheet['media'] : '',
                        isset($wpjobportal_sheet['ver']) ? $wpjobportal_sheet['ver'] : ''
                    );
                }
            }
            $wpjobportal_look_css .= wpjobportal_layered_import('includes/css/wpjobportal-design-system.css', 'wpjp-foundation');
            $wpjobportal_look_css .= wpjobportal_layered_import(
                $wpjobportal_look['theme']['src'],
                '',
                '',
                isset($wpjobportal_look['theme']['ver']) ? $wpjobportal_look['theme']['ver'] : ''
            );
            // The admin sheet sizes the preview's page heading for the standard
            // design; under the design add-on it follows the design, as on the site.
            $wpjobportal_look_css .= '#wpjobportal-admin-wrapper .js_effect_preview .wjportal-page-heading{font-size:var(--wpjp-ed-fs-h1);}' . "\n";
            wp_register_style( 'wpjobportal-style2', false, array(), WPJOBPORTAL_VERSION );
            wp_enqueue_style('wpjobportal-style2');
            wp_add_inline_style('wpjobportal-style2', $wpjobportal_look_css);
        }
        if (!$wpjobportal_look_active) {
            wp_enqueue_style('wpjobportal-style2', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/style.css?f_ignore_cache='.$rand, array(), wpjobportal_asset_version('includes/css/style.css'));
        }

        if (is_rtl()) {
            wp_register_style('wpjobportal-style-rtl', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/stylertl.css', array(), wpjobportal_asset_version('includes/css/stylertl.css'));
            wp_enqueue_style('wpjobportal-style-rtl');
        }

        $wpjobportal_done = $wpjobportal_look_active;
        return $wpjobportal_done;
    }

    /**
     * A colour as a lowercase hex, or the fallback.
     *
     * Colours are written verbatim into stylesheets every front-end page
     * loads, so only a hex is let through; anything else would be arbitrary
     * CSS typed into a text box.
     */
    public static function wpjpHexColor($wpjobportal_value, $wpjobportal_fallback) {
        $wpjobportal_value = is_scalar($wpjobportal_value) ? trim((string) $wpjobportal_value) : '';
        if (preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $wpjobportal_value)) {
            return strtolower($wpjobportal_value);
        }
        return $wpjobportal_fallback;
    }

    /**
     * What the Design screen sets under Elegant Design.
     *
     * Each design keeps its own settings: these are stored apart from the
     * standard design's color1-3 and font sizes, and saving one design never
     * changes the other. The defaults are Elegant Design's own, the same ones
     * its stylesheet falls back to, so a site that never touches them looks
     * exactly as it did. 'var' is the custom property the variables sheet
     * writes; elegant-theme.css reads it.
     */
    public static function wpjpElegantFields() {
        return array(
            'ed_color1'           => array('type' => 'color', 'default' => '#3baeda', 'var' => '--primary-color'),
            'ed_color2'           => array('type' => 'color', 'default' => '#12172b', 'var' => '--wpjp-ed-user-secondary'),
            'ed_color3'           => array('type' => 'color', 'default' => '#4d5566', 'var' => '--wpjp-ed-user-text'),
            'ed_border'           => array('type' => 'color', 'default' => '#e3e6ee', 'var' => '--wpjp-ed-user-border'),
            'ed_background'       => array('type' => 'color', 'default' => '#f6f7fb', 'var' => '--wpjp-ed-user-canvas'),
            'ed_card'             => array('type' => 'color', 'default' => '#ffffff', 'var' => '--wpjp-ed-user-card'),
            'ed_mainheading'      => array('type' => 'size', 'default' => 40, 'min' => 18, 'max' => 64, 'var' => '--wpjp-ed-user-fs-h1'),
            'ed_secondsubheading' => array('type' => 'size', 'default' => 24, 'min' => 16, 'max' => 48, 'var' => '--wpjp-ed-user-fs-h2'),
            'ed_subheading'       => array('type' => 'size', 'default' => 34, 'min' => 14, 'max' => 48, 'var' => '--wpjp-ed-user-fs-card'),
            'ed_bodyfontsize'     => array('type' => 'size', 'default' => 17, 'min' => 12, 'max' => 24, 'var' => '--wpjp-ed-user-fs-body'),
            'ed_corners'          => array('type' => 'choice', 'default' => 'normal', 'choices' => array(
                'square' => array('--wpjp-ed-user-radius' => '0', '--wpjp-ed-user-radius-sm' => '0', '--wpjp-ed-user-radius-lg' => '0', '--wpjp-ed-user-radius-xs' => '0', '--wpjp-ed-user-field-radius' => '0'),
                'normal' => array('--wpjp-ed-user-radius' => '10px', '--wpjp-ed-user-radius-sm' => '6px', '--wpjp-ed-user-radius-lg' => '14px', '--wpjp-ed-user-radius-xs' => '5px', '--wpjp-ed-user-field-radius' => '8px'),
                'round'  => array('--wpjp-ed-user-radius' => '18px', '--wpjp-ed-user-radius-sm' => '12px', '--wpjp-ed-user-radius-lg' => '22px', '--wpjp-ed-user-radius-xs' => '10px', '--wpjp-ed-user-field-radius' => '14px'),
            )),
            'ed_font'             => array('type' => 'choice', 'default' => 'theme', 'choices' => array(
                'theme'  => array('--wpjp-ed-user-font' => 'inherit'),
                'system' => array('--wpjp-ed-user-font' => 'system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif'),
            )),
        );
    }

    /**
     * Is Elegant Design the site's design right now?
     *
     * Asked the way the Colors screen asks: through the plugin that provides
     * the design (Pro), or the old standalone add-on. isElegantDesignEnabled()
     * knows only the add-on, so with Pro it answered no.
     */
    public static function wpjpElegantActive() {
        $wpjobportal_look = apply_filters('wpjobportal_look_provider', array());
        if (!empty($wpjobportal_look['theme']['src']) && wpjobportal_elegant_design_path() !== '') {
            return true;
        }
        return (bool) WPJOBPORTALincluder::getJSModel('common')->isElegantDesignEnabled();
    }

    /**
     * Square corners for the whole portal, not only for the parts that read
     * Elegant Design's corner sizes.
     *
     * Core, the design system and the suites set some fifty corners as fixed
     * sizes - logos, chips, badges, filter fields - so "Square" left them
     * round. Chasing each one would never finish, so Square adds this one
     * rule instead. Written into the variables sheet, which loads in the
     * legacy cascade layer, its !important outranks every one of them.
     * Dots, spinners, circular progress and radio buttons keep their shape:
     * square, they stop being what they are.
     *
     * @param string $scope The wrapper the rule is limited to.
     * @return string
     */
    public static function wpjpElegantSquareCss($scope = '.wjportal-main-up-wrapper') {
        $keep = ':not(input[type="radio"]):not([class*="dot"]):not([class*="spin"]):not([class*="loader"]):not([class*="loading"])'
            . ':not([class*="circle"]):not([class*="circle"] *):not(.mask):not(.mask *)';
        return $scope . ', ' . $scope . ' *' . $keep . ' { border-radius: 0 !important; }';
    }

    /**
     * Elegant Design's primary colour, for settings saved before it had one.
     *
     * Until 2.7.0 both designs shared color1, so a site that has not saved
     * since starts Elegant Design from that colour rather than from a default
     * it never chose.
     *
     * @param array $wpjobportal_values Stored settings.
     * @return array
     */
    public static function wpjpWithElegantPrimary($wpjobportal_values) {
        $wpjobportal_values = is_array($wpjobportal_values) ? $wpjobportal_values : array();
        if (!isset($wpjobportal_values['ed_color1']) && isset($wpjobportal_values['color1'])) {
            $wpjobportal_values['ed_color1'] = $wpjobportal_values['color1'];
        }
        return $wpjobportal_values;
    }

    /** One Elegant Design setting, held to its type; anything else is the default. */
    public static function wpjpElegantValue($wpjobportal_value, $wpjobportal_rules) {
        if ($wpjobportal_rules['type'] === 'color') {
            return self::wpjpHexColor($wpjobportal_value, $wpjobportal_rules['default']);
        }
        if ($wpjobportal_rules['type'] === 'size') {
            return self::wpjpFontSize($wpjobportal_value, $wpjobportal_rules);
        }
        $wpjobportal_value = is_scalar($wpjobportal_value) ? (string) $wpjobportal_value : '';
        return isset($wpjobportal_rules['choices'][$wpjobportal_value]) ? $wpjobportal_value : $wpjobportal_rules['default'];
    }

    /**
     * "#3baeda" as "59, 174, 218", for the one place a colour has to be stated
     * as components rather than as a hex. Anything that is not a hex - a named
     * colour a site has typed in by hand, say - falls back rather than writing
     * a broken rgba() into a stylesheet every page then loads.
     */
    private static function wpjpHexToRgb($wpjobportal_hex, $wpjobportal_fallback) {
        $wpjobportal_hex = ltrim(trim((string) $wpjobportal_hex), '#');
        if (strlen($wpjobportal_hex) === 3) {
            $wpjobportal_hex = $wpjobportal_hex[0] . $wpjobportal_hex[0]
                . $wpjobportal_hex[1] . $wpjobportal_hex[1]
                . $wpjobportal_hex[2] . $wpjobportal_hex[2];
        }
        if (!preg_match('/^[0-9a-fA-F]{6}$/', $wpjobportal_hex)) {
            return $wpjobportal_fallback;
        }
        return hexdec(substr($wpjobportal_hex, 0, 2)) . ', '
            . hexdec(substr($wpjobportal_hex, 2, 2)) . ', '
            . hexdec(substr($wpjobportal_hex, 4, 2));
    }

    function wpjpGenerateColorVariablesFile($wpjobportal_data) {
        if (empty($wpjobportal_data['color1']) || empty($wpjobportal_data['color2']) || empty($wpjobportal_data['color3'])) {
            return false; // nothing to do
        }

        $wpjobportal_data['color1'] = self::wpjpHexColor($wpjobportal_data['color1'], '#3baeda');
        $wpjobportal_data['color2'] = self::wpjpHexColor($wpjobportal_data['color2'], '#333333');
        $wpjobportal_data['color3'] = self::wpjpHexColor($wpjobportal_data['color3'], '#575757');

        // Define the file path
        $css_file = WPJOBPORTAL_PLUGIN_PATH . 'includes/css/job_portal_variables.css';

        /*
         * The focus ring follows the colour that was actually chosen.
         *
         * It had been written out as a fixed rgba(98, 36, 198, .25) - a purple
         * belonging to no palette in the picker - so a site on Ruby Red or
         * Ocean Cyan still outlined every focused field in violet.
         */
        $wpjobportal_focus_rgb = self::wpjpHexToRgb($wpjobportal_data['color1'], '59, 174, 218');

        // Clamped here as well as on read: this is the value that reaches the
        // stylesheet, and it must be safe whatever the form posted.
        $wpjobportal_fonts = array();
        foreach (self::wpjpFontSizeFields() as $wpjobportal_key => $wpjobportal_rules) {
            $wpjobportal_fonts[$wpjobportal_key] = self::wpjpFontSize(
                isset($wpjobportal_data[$wpjobportal_key]) ? $wpjobportal_data[$wpjobportal_key] : null,
                $wpjobportal_rules
            );
        }

        // Prepare CSS content
        $css_content = "
        :root {
            --wpjp-primary-color: {$wpjobportal_data['color1']};
            --wpjp-secondary-color: {$wpjobportal_data['color2']};
            --wpjp-body-font-color: {$wpjobportal_data['color3']};
            --wpjp-border-color: #e9ecef; /* Light Gray Border */
            --wpjp-background-color: #f6f6f6; /* Off-white background */
            --wpjp-card-background: #ffffff;
            --wpjp-highlight-color: #FFC300; /* Gold Accent (repurposed from old design) */
            --wpjp-success-color: #28a745;
            --wpjp-warning-color: #a15c00; /* amber, as the design system has always expected; this had been a cyan */
            --wpjp-danger-color: #dc3545;

            /* Font Variables - set on the Themes screen */
            --wpjp-main-heading: {$wpjobportal_fonts['mainheading']}px;
            --wpjp-second-sub-heading: {$wpjobportal_fonts['secondsubheading']}px;
            --wpjp-sub-heading: {$wpjobportal_fonts['subheading']}px;
            --wpjp-body-font-size: {$wpjobportal_fonts['bodyfontsize']}px;


            /* Other Variables */
            --wpjp-card-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            --wpjp-card-hover-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            --wpjp-focus-shadow-color: 0 0 0 3px rgba({$wpjobportal_focus_rgb}, 0.25);
            --wpjp-error-color: #e53e3e;
            --wpjp-error-focus-shadow: 0 0 0 3px rgba(229, 62, 62, 0.25);
        }
        ";

        // Initialize WordPress filesystem API
        global $wp_filesystem;
        $wp_filesystem = wpjobportal::wpjobportal_filesystem();

        if ($wp_filesystem) {
            $wp_filesystem->put_contents($css_file, $css_content, FS_CHMOD_FILE);
            return true;
        }
    }

    function wpjpGenerateColorVariablesFileElegant($wpjobportal_data) {
        if (empty($wpjobportal_data['color1']) || empty($wpjobportal_data['color2']) || empty($wpjobportal_data['color3'])) {
            return false; // nothing to do
        }
        // Elegant Design's own colours. The three legacy names stay, because
        // wpjobportal-design-bridge.css still reads them.
        $wpjobportal_data = self::wpjpWithElegantPrimary($wpjobportal_data);
        $wpjobportal_fields = self::wpjpElegantFields();
        $wpjobportal_color1 = self::wpjpElegantValue(isset($wpjobportal_data['ed_color1']) ? $wpjobportal_data['ed_color1'] : null, $wpjobportal_fields['ed_color1']);
        $wpjobportal_color2 = self::wpjpElegantValue(isset($wpjobportal_data['ed_color2']) ? $wpjobportal_data['ed_color2'] : null, $wpjobportal_fields['ed_color2']);
        $wpjobportal_color3 = self::wpjpElegantValue(isset($wpjobportal_data['ed_color3']) ? $wpjobportal_data['ed_color3'] : null, $wpjobportal_fields['ed_color3']);

        // Define the file path
        $css_file = wpjobportal_elegant_design_path().'includes/css/elegantdesignvariables.css';

        // The Colors screen's Elegant Design settings, each held to its type
        // before it reaches the stylesheet. Only those changed from their
        // default are written; elegant-theme.css falls back to its own values
        // for the rest, so an untouched site is unchanged.
        $wpjobportal_settings = '';
        foreach (self::wpjpElegantFields() as $wpjobportal_key => $wpjobportal_rules) {
            $wpjobportal_value = self::wpjpElegantValue(
                isset($wpjobportal_data[$wpjobportal_key]) ? $wpjobportal_data[$wpjobportal_key] : null,
                $wpjobportal_rules
            );
            if ($wpjobportal_key === 'ed_color1' || $wpjobportal_value === $wpjobportal_rules['default']) {
                continue; // the primary is written above; otherwise the stylesheet's own value stands
            }
            if ($wpjobportal_rules['type'] === 'choice') {
                foreach ($wpjobportal_rules['choices'][$wpjobportal_value] as $wpjobportal_var => $wpjobportal_css) {
                    $wpjobportal_settings .= "                {$wpjobportal_var}: {$wpjobportal_css};\n";
                }
                continue;
            }
            $wpjobportal_css = $wpjobportal_rules['type'] === 'size' ? $wpjobportal_value . 'px' : $wpjobportal_value;
            $wpjobportal_settings .= "                {$wpjobportal_rules['var']}: {$wpjobportal_css};\n";
        }

        // Prepare CSS content
        $css_content = "
        /*  Variables */
            :root {
                --primary-color: {$wpjobportal_color1};
                --secondary-color: {$wpjobportal_color2};
                --body-color: {$wpjobportal_color3};
                --border-color: #dedede;
                --font-size: 17px;
                --main-title-font-size:40px;
                --second-sub-heading:35px;
                --sub-heading:24px;

                /* Set on WP Job Portal > Colors under Elegant Design */
{$wpjobportal_settings}            }
        ";
        if (isset($wpjobportal_data['ed_corners']) && $wpjobportal_data['ed_corners'] === 'square') {
            $css_content .= "\n        /* Corners: Square */\n        " . self::wpjpElegantSquareCss() . "\n";
        }

        // Initialize WordPress filesystem API
        global $wp_filesystem;
        $wp_filesystem = wpjobportal::wpjobportal_filesystem();

        if ($wp_filesystem) {
            $wp_filesystem->put_contents($css_file, $css_content, FS_CHMOD_FILE);
            return true;
        }
    }
}

?>