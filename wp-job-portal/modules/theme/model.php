<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALthemeModel {
    function getCurrentTheme() {


        $wpjobportal_color1 = "#3baeda";
        $wpjobportal_color2 = "#333333";
        $wpjobportal_color3 = "#575757";

        $wpjobportal_color_string_values = get_option("wpjp_set_theme_colors");
        if($wpjobportal_color_string_values != ''){
            $wpjobportal_json_values = json_decode($wpjobportal_color_string_values,true);
            if(is_array($wpjobportal_json_values) && !empty($wpjobportal_json_values)){
                $wpjobportal_color1 = $wpjobportal_json_values['color1'];
                $wpjobportal_color2 = $wpjobportal_json_values['color2'];
                $wpjobportal_color3 = $wpjobportal_json_values['color3'];
            }
        }

        $wpjobportal_theme['color1'] = esc_attr($wpjobportal_color1);
        $wpjobportal_theme['color2'] = esc_attr($wpjobportal_color2);
        $wpjobportal_theme['color3'] = esc_attr($wpjobportal_color3);
        wpjobportal::$_data[0] = $wpjobportal_theme;
        return $wpjobportal_theme;
    }

    function storeTheme($wpjobportal_data) {

        if (empty($wpjobportal_data))
            return false;
        $wpjobportal_data = wpjobportal::wpjobportal_sanitizeData($wpjobportal_data);

        $return = $this->wpjpGenerateColorVariablesFile($wpjobportal_data);

        update_option('wpjp_set_theme_colors', wp_json_encode($wpjobportal_data));
        // $return = require(WPJOBPORTAL_PLUGIN_PATH . 'includes/css/style_color.php');
        if($return){
            return WPJOBPORTAL_SAVED;
        } else {
            return WPJOBPORTAL_SAVE_ERROR;
        }
    }

    function wpjpGenerateColorVariablesFile($wpjobportal_data) {
        if (empty($wpjobportal_data['color1']) || empty($wpjobportal_data['color2']) || empty($wpjobportal_data['color3'])) {
            return false; // nothing to do
        }

        // Define the file path
        $css_file = WPJOBPORTAL_PLUGIN_PATH . 'includes/css/job_portal_variables.css';


        // Prepare CSS content
        $css_content = ":root {
        --wpjp-primary-color: {$wpjobportal_data['color1']};
        --wpjp-secondary-color: {$wpjobportal_data['color2']};
        --wpjp-body-font-color: {$wpjobportal_data['color3']};
        --wpjp-border-color: #e9ecef; /* Light Gray Border */
        --wpjp-background-color: #f6f6f6; /* Off-white background */
        --wpjp-card-background: #ffffff;
        --wpjp-highlight-color: #FFC300; /* Gold Accent (repurposed from old design) */
        --wpjp-success-color: #28a745;
        --wpjp-warning-color: #17a2b8;
        --wpjp-danger-color: #dc3545;

        /* Font Variables (Desktop Default) */
        --wpjp-main-heading: 32px; /* 35px approx */
        --wpjp-second-sub-heading: 27px; /* 25px approx */
        --wpjp-sub-heading: 22px; /* 19px approx */
        --wpjp-body-font-size: 17px; /* 16px default */


        /* Other Variables */
        --wpjp-card-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        --wpjp-card-hover-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        --wpjp-focus-shadow-color: 0 0 0 3px rgba(98, 36, 198, 0.25);
        --wpjp-error-color: #e53e3e;
        --wpjp-error-focus-shadow: 0 0 0 3px rgba(229, 62, 62, 0.25);
    }
    ";

        // Initialize WordPress filesystem API
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            require_once ABSPATH . '/wp-admin/includes/file.php';
            WP_Filesystem();
        }

        if ($wp_filesystem) {
            $wp_filesystem->put_contents($css_file, $css_content, FS_CHMOD_FILE);
            return true;
        }
    }


    function getColorCode($filestring, $wpjobportal_colorNo) {
        if (wpjobportalphplib::wpJP_strstr($filestring, '$wpjobportal_color' . $wpjobportal_colorNo)) {
            $wpjobportal_path1 = wpjobportalphplib::wpJP_strpos($filestring, '$wpjobportal_color' . $wpjobportal_colorNo);
            $wpjobportal_path1 = wpjobportalphplib::wpJP_strpos($filestring, '#', $wpjobportal_path1);
            $wpjobportal_path2 = wpjobportalphplib::wpJP_strpos($filestring, ';', $wpjobportal_path1);
            $wpjobportal_colorcode = wpjobportalphplib::wpJP_substr($filestring, $wpjobportal_path1, $wpjobportal_path2 - $wpjobportal_path1 - 1);
            return $wpjobportal_colorcode;
        }
    }

      function replaceString(&$filestring, $wpjobportal_colorNo, $wpjobportal_data) {
        if (wpjobportalphplib::wpJP_strstr($filestring, '$wpjobportal_color' . $wpjobportal_colorNo)) {
            $wpjobportal_path1 = wpjobportalphplib::wpJP_strpos($filestring, '$wpjobportal_color' . $wpjobportal_colorNo);
            $wpjobportal_path2 = wpjobportalphplib::wpJP_strpos($filestring, ';', $wpjobportal_path1);
            $filestring = substr_replace($filestring, '$wpjobportal_color' . $wpjobportal_colorNo . ' = "' . $wpjobportal_data['color' . $wpjobportal_colorNo] . '";', $wpjobportal_path1, $wpjobportal_path2 - $wpjobportal_path1 + 1);
        }
    }

}

?>
