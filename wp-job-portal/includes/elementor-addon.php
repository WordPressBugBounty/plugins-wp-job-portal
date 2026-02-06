<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * Description: Elementor intergration for WP Job Portal.
 */

// shortcodes widget
function wpjobportal_register_jp_shortcodes_widget( $widgets_manager ) {

	include_once 'classes/jp-shortcodes-widget.php';

	$widgets_manager->register( new \WPJOBPORTAL_Shortcodes_Wigdet() );
}
add_action( 'elementor/widgets/register', 'wpjobportal_register_jp_shortcodes_widget' );




add_action('save_post','wpjobportal_save_post_job_portal_shortcode_element' , 10, 1);

function wpjobportal_save_post_job_portal_shortcode_element($post_id) {
    // Prevent auto-saves, revisions, or bulk edits from triggering the function
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (wp_is_post_revision($post_id)) return;
    if (!current_user_can('edit_post', $post_id)) return;

    // Check if Elementor data exists(is elementor page)
    $elementor_data = get_post_meta($post_id, '_elementor_data', true);

    if (!$elementor_data) return; // Exit if no Elementor data found

    // Decode Elementor JSON data
    $elements = json_decode($elementor_data, true);

    // Recursive function to search for a widget
    if(!function_exists('wpjobportal_find_elementor_widget')){
        function wpjobportal_find_elementor_widget($elements, $widget_name) {
            foreach ($elements as $element) {
                if (isset($element['widgetType']) && $element['widgetType'] === $widget_name) {
                    return $element['settings']; // Return widget settings
                }
                if (!empty($element['elements'])) {
                    $found = wpjobportal_find_elementor_widget($element['elements'], $widget_name);
                    if ($found) return $found;
                }
            }
            return false;
        }
    }

    // 'jp_shortcodes_wigdet' widget name to find
    $widget_settings = wpjobportal_find_elementor_widget($elements, 'jp_shortcodes_wigdet');
    if (!empty($widget_settings) && isset($widget_settings['jp_shortcode']) && $widget_settings['jp_shortcode'] == 'wpjobportal_jobseeker_controlpanel')  { // only run beloe code for specific shortcode
        $elementor_css = wpjobportal_create_css_from_widget_settings($widget_settings);
        if($elementor_css != ''){
            wpjobportal_jp_put_css_in_file($elementor_css);
        }
        // to handle reset case
        // $wpjobportal_color_array['color1'] = "#3baeda";
        // $wpjobportal_color_array['color2'] = "#333333";
        // $wpjobportal_color_array['color3'] = "#575757";

        // handle color css
        // to handle the case of not all three are changed
        $wpjobportal_color_array = array();
        $wpjobportal_color_string_values = get_option("wpjp_set_theme_colors");
        if($wpjobportal_color_string_values != ''){
            $wpjobportal_json_values = json_decode($wpjobportal_color_string_values,true);
            if(is_array($wpjobportal_json_values) && !empty($wpjobportal_json_values)){
                $wpjobportal_color_array['color1'] = esc_attr($wpjobportal_json_values['color1']);
                $wpjobportal_color_array['color2'] = esc_attr($wpjobportal_json_values['color2']);
                $wpjobportal_color_array['color3'] = esc_attr($wpjobportal_json_values['color3']);
            }
        }


        if(!empty($widget_settings['jp_primarycolor'])){
            $wpjobportal_color_array['color1'] = $widget_settings['jp_primarycolor'];
        }else{
            $wpjobportal_color_array['color1'] = "#3baeda";
        }

        if(!empty($widget_settings['jp_secondarycolor'])){
            $wpjobportal_color_array['color2'] = $widget_settings['jp_secondarycolor'];
        }else{
            $wpjobportal_color_array['color2'] = "#333333";
        }

        if(!empty($widget_settings['jp_contentcolor'])){
            $wpjobportal_color_array['color3'] = $widget_settings['jp_contentcolor'];
        }else{
            $wpjobportal_color_array['color3'] = "#575757";
        }

        // set the option to use in color css generation
        update_option('wpjp_set_theme_colors', wp_json_encode($wpjobportal_color_array));
        $return = require(WPJOBPORTAL_PLUGIN_PATH . 'includes/css/style_color.php');

        // for elegent addon
        if(WPJOBPORTALincluder::getJSModel('common')->isElegantDesignEnabled()){
            wpjobportal_create_css_from_widget_settings_for_elegant($widget_settings);
        }

        // error_log(print_r($widget_settings, true)); // Debugging: log settings to error_log
    }
}


function wpjobportal_create_css_from_widget_settings_for_elegant($wpjobportal_settings){

    $wpjobportal_color1 = '#2a3fca';
    $wpjobportal_color2 = '#000000';
    $wpjobportal_color3 = '#707070';

    if(!empty($wpjobportal_settings['jp_primarycolor'])){
        $wpjobportal_color1 = $wpjobportal_settings['jp_primarycolor'];
    }

    if(!empty($wpjobportal_settings['jp_secondarycolor'])){
        $wpjobportal_color2 = $wpjobportal_settings['jp_secondarycolor'];
    }

    if(!empty($wpjobportal_settings['jp_contentcolor'])){
        $wpjobportal_color3 = $wpjobportal_settings['jp_contentcolor'];
    }




    // typography variables
    // Content Typography settings
    $wpjobportal_content_font_style = !empty($wpjobportal_settings['typography_content_font_style']) ? esc_attr($wpjobportal_settings['typography_content_font_style']) : '';
    $wpjobportal_content_text_transform = !empty($wpjobportal_settings['typography_content_text_transform']) ? esc_attr($wpjobportal_settings['typography_content_text_transform']) : '';
    $wpjobportal_content_font_size = isset($wpjobportal_settings['typography_content_font_size']['size']) ? esc_attr($wpjobportal_settings['typography_content_font_size']['size']) . esc_attr($wpjobportal_settings['typography_content_font_size']['unit']) : '17px';
    $wpjobportal_content_font_weight = isset($wpjobportal_settings['typography_content_font_weight']) ? esc_attr($wpjobportal_settings['typography_content_font_weight']) : '';
    $wpjobportal_content_font_family = !empty($wpjobportal_settings['typography_content_font_family']) ? esc_attr($wpjobportal_settings['typography_content_font_family']) : '';
    $wpjobportal_content_line_height = isset($wpjobportal_settings['typography_content_line_height']['size']) ? esc_attr($wpjobportal_settings['typography_content_line_height']['size']) . esc_attr($wpjobportal_settings['typography_content_line_height']['unit']) : '';
    $wpjobportal_content_letter_spacing = isset($wpjobportal_settings['typography_content_letter_spacing']['size']) ? esc_attr($wpjobportal_settings['typography_content_letter_spacing']['size']) . esc_attr($wpjobportal_settings['typography_content_letter_spacing']['unit']) : '';

    // page heading & section title Typography settings
    $wpjobportal_section_title_font_style = !empty($wpjobportal_settings['typography_section_title_font_style']) ? esc_attr($wpjobportal_settings['typography_section_title_font_style']) : '';
    $wpjobportal_section_title_text_transform = !empty($wpjobportal_settings['typography_section_title_text_transform']) ? esc_attr($wpjobportal_settings['typography_section_title_text_transform']) : '';
    $wpjobportal_section_title_font_size = isset($wpjobportal_settings['typography_section_title_font_size']['size']) ? esc_attr($wpjobportal_settings['typography_section_title_font_size']['size']) . esc_attr($wpjobportal_settings['typography_section_title_font_size']['unit']) : '38px';
    $wpjobportal_section_title_font_weight = isset($wpjobportal_settings['typography_section_title_font_weight']) ? esc_attr($wpjobportal_settings['typography_section_title_font_weight']) : '';
    $wpjobportal_section_title_font_family = !empty($wpjobportal_settings['typography_section_title_font_family']) ? esc_attr($wpjobportal_settings['typography_section_title_font_family']) : '';
    $wpjobportal_section_title_line_height = isset($wpjobportal_settings['typography_section_title_line_height']['size']) ? esc_attr($wpjobportal_settings['typography_section_title_line_height']['size']) . esc_attr($wpjobportal_settings['typography_section_title_line_height']['unit']) : '';
    $wpjobportal_section_title_letter_spacing = isset($wpjobportal_settings['typography_section_title_letter_spacing']['size']) ? esc_attr($wpjobportal_settings['typography_section_title_letter_spacing']['size']) . esc_attr($wpjobportal_settings['typography_section_title_letter_spacing']['unit']) : '';


    // page buttons Typography settings
    $buttons_font_style = !empty($wpjobportal_settings['typography_buttons_font_style']) ? esc_attr($wpjobportal_settings['typography_buttons_font_style']) : '';
    $buttons_text_transform = !empty($wpjobportal_settings['typography_buttons_text_transform']) ? esc_attr($wpjobportal_settings['typography_buttons_text_transform']) : '';
    $buttons_font_size = isset($wpjobportal_settings['typography_buttons_font_size']['size']) ? esc_attr($wpjobportal_settings['typography_buttons_font_size']['size']) . esc_attr($wpjobportal_settings['typography_buttons_font_size']['unit']) : '';
    $buttons_font_weight = isset($wpjobportal_settings['typography_buttons_font_weight']) ? esc_attr($wpjobportal_settings['typography_buttons_font_weight']) : '';
    $buttons_font_family = !empty($wpjobportal_settings['typography_buttons_font_family']) ? esc_attr($wpjobportal_settings['typography_buttons_font_family']) : '';
    $buttons_line_height = isset($wpjobportal_settings['typography_buttons_line_height']['size']) ? esc_attr($wpjobportal_settings['typography_buttons_line_height']['size']) . esc_attr($wpjobportal_settings['typography_buttons_line_height']['unit']) : '';
    $buttons_letter_spacing = isset($wpjobportal_settings['typography_buttons_letter_spacing']['size']) ? esc_attr($wpjobportal_settings['typography_buttons_letter_spacing']['size']) . esc_attr($wpjobportal_settings['typography_buttons_letter_spacing']['unit']) : '';



    $css_variables = '
    /*  Variables */
    :root {
        --primary-color: '.$wpjobportal_color1.';
        --secondary-color: '.$wpjobportal_color2.';
        --border-color: #dedede;
        --body-color:'.$wpjobportal_color3.';

        --font-size: '.$wpjobportal_content_font_size.';
        --main-title-font-size:'.$wpjobportal_section_title_font_size.';
        --second-sub-heading:'.$wpjobportal_section_title_font_size.';
        --sub-heading:24px;
      }
    ';

    // variable overides
    if ( ! function_exists( 'WP_Filesystem' ) ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }
    global $wp_filesystem;
    if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ) {
        $creds = request_filesystem_credentials( site_url() );
        wp_filesystem( $creds );
    }
    //$file = ELEGANTDESIGN_PLUGIN_PATH . 'includes/css/elegant_elementor_overrides.css';
    $file = ELEGANTDESIGN_PLUGIN_PATH . 'includes/css/elegantdesignvariables.css';
    $wp_filesystem->put_contents( $file, '', 0755 );
    $response = $wp_filesystem->put_contents( $file, $css_variables );



          // page body css start
            $custom_css = '

            div.wjportal-elegant-addon-main-up-wrapper *:not(i):not(g):not(.wjportal-elegant-addon-cp-sec-title):not(.wjportal-resume-section-title):not(.wjportal-page-heading):not(.wjportal-elegant-addon-cp-view-btn):not(.wjportal-elegant-addon-tp-banner-buttnrow a):not(button):not(.wjportal-elegant-addon-jobs-apply-btn):not(.wjportal-form-btn-wrp a):not(.wjportal-form-btn):not(.wjportal-save-btn):not(.button):not(.wjportal-elegant-addon-select-role-form-row a):not(div.wjportal-elegant-addon-main-up-wrapper .wjportal-elegant-addon-myjobs-btn-wrp a):not(.wjportal-elegant-addon-item-status):not(.wjportal-elegant-addon-resume-action-wrp a):not([type="button"]):not([type="submit"]):not([type="file"]):not(.wjportal-resume-adv-act-wrp a):not(.wjportal-elegant-addon-act-btn):not(.wjportal-elegant-addon-resume-action-wrp a):not(.wjportal-elegant-addon-department-action-wrp a):not(.wjportal-elegant-addon-folder-action-wrp a):not(.wjportal-elegant-addon-coverletter-action-wrp a):not(.wjportal-elegant-addon-resume-action-wrp a):not(.wjportal-elegant-addon-pkg-list-item-action-wrp a):not(.wjportal-form-btn-wrp a):not(.wjportal-visitor-msg-btn):not(.wjportal-act-btn):not(.wjportal-form-upload-btn):not(.wpjp-add-new-section-link):not(.anchor):not(.map-link):not(.anchor span):not(.map-link span):not(.wjportal-elegant-addon-company-action-wrp a):not(.wjportal-by-category-item-btn-wrp a):not(.wjportal-elegant-addon-view-companyjobs):not(.wjportal-error-msg-actions-wrp a):not(.wjportal-elegant-addon-save-search-action-wrp a):not(.wjportal-elegant-addon-myjobs-btn-wrp a)
            {';

            if (!empty($wpjobportal_content_font_style)) {
                $custom_css .= 'font-style:' . esc_attr($wpjobportal_content_font_style) . ' !important;';
            }

            if (!empty($wpjobportal_content_text_transform)) {
                $custom_css .= 'text-transform:' . esc_attr($wpjobportal_content_text_transform) . ' !important;';
            }

            if (!empty($wpjobportal_content_font_size)) {
                $custom_css .= 'font-size:' . esc_attr($wpjobportal_content_font_size) . ' !important;';
            }

            if (!empty($wpjobportal_content_font_weight)) {
                $custom_css .= 'font-weight:' . esc_attr($wpjobportal_content_font_weight) . ' !important;';
            }

            if (!empty($wpjobportal_content_font_family)) {
                $custom_css .= 'font-family:' . esc_attr($wpjobportal_content_font_family) . ' !important;';
            }

            if (!empty($wpjobportal_content_line_height)) {
                $custom_css .= 'line-height:' . esc_attr($wpjobportal_content_line_height) . ' !important;';
            }

            if (!empty($wpjobportal_content_letter_spacing)) {
                $custom_css .= 'letter-spacing:' . esc_attr($wpjobportal_content_letter_spacing) . ' !important;';
            }
            $custom_css .= ' }'.PHP_EOL;
            // page body css end


          // page title & section title css start
            $custom_css .= 'div.wjportal-elegant-addon-main-up-wrapper .wjportal-page-heading ,
                            div.wjportal-elegant-addon-main-up-wrapper .wjportal-resume-section-title ,
                            div.wjportal-elegant-addon-main-up-wrapper .wjportal-elegant-addon-cp-sec-title
            {';

            if (!empty($wpjobportal_section_title_font_style)) {
                $custom_css .= 'font-style:' . esc_attr($wpjobportal_section_title_font_style) . ' !important;';
            }

            if (!empty($wpjobportal_section_title_text_transform)) {
                $custom_css .= 'text-transform:' . esc_attr($wpjobportal_section_title_text_transform) . ' !important;';
            }

            if (!empty($wpjobportal_section_title_font_size)) {
                $custom_css .= 'font-size:' . esc_attr($wpjobportal_section_title_font_size) . ' !important;';
            }

            if (!empty($wpjobportal_section_title_font_weight)) {
                $custom_css .= 'font-weight:' . esc_attr($wpjobportal_section_title_font_weight) . ' !important;';
            }

            if (!empty($wpjobportal_section_title_font_family)) {
                $custom_css .= 'font-family:' . esc_attr($wpjobportal_section_title_font_family) . ' !important;';
            }

            if (!empty($wpjobportal_section_title_line_height)) {
                $custom_css .= 'line-height:' . esc_attr($wpjobportal_section_title_line_height) . ' !important;';
            }

            if (!empty($wpjobportal_section_title_letter_spacing)) {
                $custom_css .= 'letter-spacing:' . esc_attr($wpjobportal_section_title_letter_spacing) . ' !important;';
            }
            $custom_css .= ' }'.PHP_EOL;
            // page title & section title css end

          // page buttons css start
            $custom_css .= '
            div.wjportal-elegant-addon-main-up-wrapper .wjportal-elegant-addon-cp-view-btn ,
            div.wjportal-elegant-addon-main-up-wrapper .wjportal-elegant-addon-tp-banner-buttnrow a ,
            div.wjportal-elegant-addon-main-up-wrapper button ,
            div.wjportal-elegant-addon-main-up-wrapper .wjportal-elegant-addon-jobs-apply-btn ,
            div.wjportal-elegant-addon-main-up-wrapper .wjportal-form-btn-wrp a,
            div.wjportal-elegant-addon-main-up-wrapper .wjportal-form-btn ,
            div.wjportal-elegant-addon-main-up-wrapper .wjportal-save-btn ,
            div.wjportal-elegant-addon-main-up-wrapper .button ,
            div.wjportal-elegant-addon-main-up-wrapper .wjportal-elegant-addon-select-role-form-row a ,
            div.wjportal-elegant-addon-main-up-wrapper div.wjportal-elegant-addon-main-up-wrapper .wjportal-elegant-addon-myjobs-btn-wrp a ,
            div.wjportal-elegant-addon-main-up-wrapper .wjportal-elegant-addon-resume-action-wrp a ,
            div.wjportal-elegant-addon-main-up-wrapper [type="button"] ,
            div.wjportal-elegant-addon-main-up-wrapper [type="submit"] ,
            div.wjportal-elegant-addon-main-up-wrapper [type="file"] ,
            div.wjportal-elegant-addon-main-up-wrapper .wjportal-resume-adv-act-wrp a ,
            div.wjportal-elegant-addon-main-up-wrapper .wjportal-elegant-addon-act-btn ,
            div.wjportal-elegant-addon-main-up-wrapper .wjportal-elegant-addon-department-action-wrp a ,
            div.wjportal-elegant-addon-main-up-wrapper .wjportal-elegant-addon-folder-action-wrp a ,
            div.wjportal-elegant-addon-main-up-wrapper .wjportal-elegant-addon-coverletter-action-wrp a ,
            div.wjportal-elegant-addon-main-up-wrapper .wjportal-elegant-addon-pkg-list-item-action-wrp a ,
            div.wjportal-elegant-addon-main-up-wrapper .wjportal-elegant-addon-company-action-wrp a ,
            div.wjportal-elegant-addon-main-up-wrapper .wjportal-form-btn-wrp a ,
            div.wjportal-elegant-addon-main-up-wrapper .wjportal-visitor-msg-btn ,
            div.wjportal-elegant-addon-main-up-wrapper .wjportal-act-btn ,
            div.wjportal-elegant-addon-main-up-wrapper .wjportal-form-upload-btn ,
            div.wjportal-elegant-addon-main-up-wrapper .wjportal-form-upload-btn ,
            div.wjportal-elegant-addon-main-up-wrapper .anchor ,
            div.wjportal-elegant-addon-main-up-wrapper .anchor span ,
            div.wjportal-elegant-addon-main-up-wrapper .map-link ,
            div.wjportal-elegant-addon-main-up-wrapper .map-link span ,
            div.wjportal-elegant-addon-main-up-wrapper .wjportal-elegant-addon-view-companyjobs ,
            div.wjportal-elegant-addon-main-up-wrapper .wjportal-by-category-item-btn-wrp a,
            div.wjportal-elegant-addon-main-up-wrapper .wjportal-elegant-addon-save-search-action-wrp a,
            div.wjportal-elegant-addon-main-up-wrapper .wjportal-elegant-addon-myjobs-btn-wrp a,
            .wjportal-error-msg-actions-wrp a,
            div.wjportal-elegant-addon-main-up-wrapper .wpjp-add-new-section-link
            {';

            if (!empty($buttons_font_style)) {
                $custom_css .= 'font-style:' . esc_attr($buttons_font_style) . ' !important;';
            }

            if (!empty($buttons_text_transform)) {
                $custom_css .= 'text-transform:' . esc_attr($buttons_text_transform) . ' !important;';
            }

            if (!empty($buttons_font_size)) {
                $custom_css .= 'font-size:' . esc_attr($buttons_font_size) . ' !important;';
            }

            if (!empty($buttons_font_weight)) {
                $custom_css .= 'font-weight:' . esc_attr($buttons_font_weight) . ' !important;';
            }

            if (!empty($buttons_font_family)) {
                $custom_css .= 'font-family:' . esc_attr($buttons_font_family) . ' !important;';
            }

            if (!empty($buttons_line_height)) {
                $custom_css .= 'line-height:' . esc_attr($buttons_line_height) . ' !important;';
            }

            if (!empty($buttons_letter_spacing)) {
                $custom_css .= 'letter-spacing:' . esc_attr($buttons_letter_spacing) . ' !important;';
            }
            $custom_css .= ' }'.PHP_EOL;
            // page title & section title css end

               // variable overides
            if ( ! function_exists( 'WP_Filesystem' ) ) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
            }
            global $wp_filesystem;
            if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ) {
                $creds = request_filesystem_credentials( site_url() );
                wp_filesystem( $creds );
            }
            //$file = ELEGANTDESIGN_PLUGIN_PATH . 'includes/css/elegant_elementor_overrides.css';
            $file = ELEGANTDESIGN_PLUGIN_PATH . 'includes/css/elegant_elementor_overrides.css';
            $wp_filesystem->put_contents( $file, '', 0755 );
            $response = $wp_filesystem->put_contents( $file, $custom_css );

            return ;
}

function wpjobportal_create_css_from_widget_settings($wpjobportal_settings){

          // Content Typography settings
          $wpjobportal_content_font_style = !empty($wpjobportal_settings['typography_content_font_style']) ? esc_attr($wpjobportal_settings['typography_content_font_style']) : '';
          $wpjobportal_content_text_transform = !empty($wpjobportal_settings['typography_content_text_transform']) ? esc_attr($wpjobportal_settings['typography_content_text_transform']) : '';
          $wpjobportal_content_font_size = isset($wpjobportal_settings['typography_content_font_size']['size']) ? esc_attr($wpjobportal_settings['typography_content_font_size']['size']) . esc_attr($wpjobportal_settings['typography_content_font_size']['unit']) : '';
          $wpjobportal_content_font_weight = isset($wpjobportal_settings['typography_content_font_weight']) ? esc_attr($wpjobportal_settings['typography_content_font_weight']) : '';
          $wpjobportal_content_font_family = !empty($wpjobportal_settings['typography_content_font_family']) ? esc_attr($wpjobportal_settings['typography_content_font_family']) : '';
          $wpjobportal_content_line_height = isset($wpjobportal_settings['typography_content_line_height']['size']) ? esc_attr($wpjobportal_settings['typography_content_line_height']['size']) . esc_attr($wpjobportal_settings['typography_content_line_height']['unit']) : '';
          $wpjobportal_content_letter_spacing = isset($wpjobportal_settings['typography_content_letter_spacing']['size']) ? esc_attr($wpjobportal_settings['typography_content_letter_spacing']['size']) . esc_attr($wpjobportal_settings['typography_content_letter_spacing']['unit']) : '';

          // page heading & section title Typography settings
          $wpjobportal_section_title_font_style = !empty($wpjobportal_settings['typography_section_title_font_style']) ? esc_attr($wpjobportal_settings['typography_section_title_font_style']) : '';
          $wpjobportal_section_title_text_transform = !empty($wpjobportal_settings['typography_section_title_text_transform']) ? esc_attr($wpjobportal_settings['typography_section_title_text_transform']) : '';
          $wpjobportal_section_title_font_size = isset($wpjobportal_settings['typography_section_title_font_size']['size']) ? esc_attr($wpjobportal_settings['typography_section_title_font_size']['size']) . esc_attr($wpjobportal_settings['typography_section_title_font_size']['unit']) : '';
          $wpjobportal_section_title_font_weight = isset($wpjobportal_settings['typography_section_title_font_weight']) ? esc_attr($wpjobportal_settings['typography_section_title_font_weight']) : '';
          $wpjobportal_section_title_font_family = !empty($wpjobportal_settings['typography_section_title_font_family']) ? esc_attr($wpjobportal_settings['typography_section_title_font_family']) : '';
          $wpjobportal_section_title_line_height = isset($wpjobportal_settings['typography_section_title_line_height']['size']) ? esc_attr($wpjobportal_settings['typography_section_title_line_height']['size']) . esc_attr($wpjobportal_settings['typography_section_title_line_height']['unit']) : '';
          $wpjobportal_section_title_letter_spacing = isset($wpjobportal_settings['typography_section_title_letter_spacing']['size']) ? esc_attr($wpjobportal_settings['typography_section_title_letter_spacing']['size']) . esc_attr($wpjobportal_settings['typography_section_title_letter_spacing']['unit']) : '';


          // page buttons Typography settings
          $buttons_font_style = !empty($wpjobportal_settings['typography_buttons_font_style']) ? esc_attr($wpjobportal_settings['typography_buttons_font_style']) : '';
          $buttons_text_transform = !empty($wpjobportal_settings['typography_buttons_text_transform']) ? esc_attr($wpjobportal_settings['typography_buttons_text_transform']) : '';
          $buttons_font_size = isset($wpjobportal_settings['typography_buttons_font_size']['size']) ? esc_attr($wpjobportal_settings['typography_buttons_font_size']['size']) . esc_attr($wpjobportal_settings['typography_buttons_font_size']['unit']) : '';
          $buttons_font_weight = isset($wpjobportal_settings['typography_buttons_font_weight']) ? esc_attr($wpjobportal_settings['typography_buttons_font_weight']) : '';
          $buttons_font_family = !empty($wpjobportal_settings['typography_buttons_font_family']) ? esc_attr($wpjobportal_settings['typography_buttons_font_family']) : '';
          $buttons_line_height = isset($wpjobportal_settings['typography_buttons_line_height']['size']) ? esc_attr($wpjobportal_settings['typography_buttons_line_height']['size']) . esc_attr($wpjobportal_settings['typography_buttons_line_height']['unit']) : '';
          $buttons_letter_spacing = isset($wpjobportal_settings['typography_buttons_letter_spacing']['size']) ? esc_attr($wpjobportal_settings['typography_buttons_letter_spacing']['size']) . esc_attr($wpjobportal_settings['typography_buttons_letter_spacing']['unit']) : '';

          // page body css start
          //.wjportal-elegant-addon-main-wrapper *:not(i):not(g):not(.wjportal-cp-sec-title):not(.wjportal-page-heading)
            $custom_css = '

            .wjportal-main-wrapper *:not(i):not(g):not(.wjportal-cp-sec-title):not(.wjportal-page-heading):not(.wjportal-resume-section-title)

            {';

            if (!empty($wpjobportal_content_font_style)) {
                $custom_css .= 'font-style:' . esc_attr($wpjobportal_content_font_style) . ' !important;';
            }

            if (!empty($wpjobportal_content_text_transform)) {
                $custom_css .= 'text-transform:' . esc_attr($wpjobportal_content_text_transform) . ' !important;';
            }

            if (!empty($wpjobportal_content_font_size)) {
                $custom_css .= 'font-size:' . esc_attr($wpjobportal_content_font_size) . ' !important;';
            }

            if (!empty($wpjobportal_content_font_weight)) {
                $custom_css .= 'font-weight:' . esc_attr($wpjobportal_content_font_weight) . ' !important;';
            }

            if (!empty($wpjobportal_content_font_family)) {
                $custom_css .= 'font-family:' . esc_attr($wpjobportal_content_font_family) . ' !important;';
            }

            if (!empty($wpjobportal_content_line_height)) {
                $custom_css .= 'line-height:' . esc_attr($wpjobportal_content_line_height) . ' !important;';
            }

            if (!empty($wpjobportal_content_letter_spacing)) {
                $custom_css .= 'letter-spacing:' . esc_attr($wpjobportal_content_letter_spacing) . ' !important;';
            }
            $custom_css .= ' }'.PHP_EOL;
            // page body css end


          // page title & section title css start
            $custom_css .= '.wjportal-main-wrapper .wjportal-cp-sec-title, .wjportal-main-wrapper  .wjportal-page-heading , div.wjportal-resume-detail-wrapper div.wjportal-resume-section-title {';

            if (!empty($wpjobportal_section_title_font_style)) {
                $custom_css .= 'font-style:' . esc_attr($wpjobportal_section_title_font_style) . ' !important;';
            }

            if (!empty($wpjobportal_section_title_text_transform)) {
                $custom_css .= 'text-transform:' . esc_attr($wpjobportal_section_title_text_transform) . ' !important;';
            }

            if (!empty($wpjobportal_section_title_font_size)) {
                $custom_css .= 'font-size:' . esc_attr($wpjobportal_section_title_font_size) . ' !important;';
            }

            if (!empty($wpjobportal_section_title_font_weight)) {
                $custom_css .= 'font-weight:' . esc_attr($wpjobportal_section_title_font_weight) . ' !important;';
            }

            if (!empty($wpjobportal_section_title_font_family)) {
                $custom_css .= 'font-family:' . esc_attr($wpjobportal_section_title_font_family) . ' !important;';
            }

            if (!empty($wpjobportal_section_title_line_height)) {
                $custom_css .= 'line-height:' . esc_attr($wpjobportal_section_title_line_height) . ' !important;';
            }

            if (!empty($wpjobportal_section_title_letter_spacing)) {
                $custom_css .= 'letter-spacing:' . esc_attr($wpjobportal_section_title_letter_spacing) . ' !important;';
            }
            $custom_css .= ' }'.PHP_EOL;
            // page title & section title css end

          // page buttons css start
            $custom_css .= '.wjportal-main-wrapper  .wjportal-cp-user-act-btn,
            .wjportal-main-wrapper  .wjportal-cp-view-btn ,
            div.wjportal-jobdetail-wrapper div.wjportal-job-company-wrp .wjportal-job-act-btn,
            div.wjportal-jobdetail-wrapper div.wjportal-job-btn-wrp .wjportal-job-act-btn,
            div.wjportal-form-wrp form p.login-submit #wp-submit,
            div.wjportal-jobs-list div.wjportal-jobs-list-btm-wrp div.wjportal-jobs-action-wrp a.wjportal-jobs-act-btn,
            div.wjportal-page-header div.wjportal-header-actions div.wjportal-act-btn-wrp .wjportal-act-btn,
            div.wjportal-resume-list div.wjportal-resume-list-btm-wrp div.wjportal-resume-action-wrp .wjportal-resume-act-btn,
            div.wjportal-popup-wrp div.wjportal-visitor-msg-btn-wrp .wjportal-visitor-msg-btn,
            div.wjportal-applied-job-actions-popup div.wjportal-job-applied-actions-btn-wrp .wjportal-job-applied-actions-btn,
            div.wjportal-jobdetail-wrapper div.wjportal-job-company-wrp div.wjportal-job-company-btn-wrp .wjportal-job-company-btn
            {';

            if (!empty($buttons_font_style)) {
                $custom_css .= 'font-style:' . esc_attr($buttons_font_style) . ' !important;';
            }

            if (!empty($buttons_text_transform)) {
                $custom_css .= 'text-transform:' . esc_attr($buttons_text_transform) . ' !important;';
            }

            if (!empty($buttons_font_size)) {
                $custom_css .= 'font-size:' . esc_attr($buttons_font_size) . ' !important;';
            }

            if (!empty($buttons_font_weight)) {
                $custom_css .= 'font-weight:' . esc_attr($buttons_font_weight) . ' !important;';
            }

            if (!empty($buttons_font_family)) {
                $custom_css .= 'font-family:' . esc_attr($buttons_font_family) . ' !important;';
            }

            if (!empty($buttons_line_height)) {
                $custom_css .= 'line-height:' . esc_attr($buttons_line_height) . ' !important;';
            }

            if (!empty($buttons_letter_spacing)) {
                $custom_css .= 'letter-spacing:' . esc_attr($buttons_letter_spacing) . ' !important;';
            }
            $custom_css .= ' }'.PHP_EOL;
            // page title & section title css end

            return $custom_css;
}

function wpjobportal_jp_put_css_in_file($css){
    if ( ! function_exists( 'WP_Filesystem' ) ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }
    global $wp_filesystem;
    if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ) {
        $creds = request_filesystem_credentials( site_url() );
        wp_filesystem( $creds );
    }
    $file = WPJOBPORTAL_PLUGIN_PATH . 'includes/css/wpjp_elementor_overrides.css';
    $wp_filesystem->put_contents( $file, '', 0755 );
    $response = $wp_filesystem->put_contents( $file, $css );
}

add_action( 'wp_enqueue_scripts', function() {
    \Elementor\Plugin::$instance->frontend->enqueue_styles();
});


// custom category for job portal
add_action('elementor/init', 'wpjobportal_job_portal_elementor_category');

   function wpjobportal_job_portal_elementor_category() {
        \Elementor\Plugin::instance()->elements_manager->add_category(
            'job-portal-category', // category slug
            [
                'title' => __('WP Job Portal', 'wp-job-portal'), // title shown in Elementor
                'icon' => 'fa fa-briefcase', // optional FontAwesome icon
            ],0
        );
    }
