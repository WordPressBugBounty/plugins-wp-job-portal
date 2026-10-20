<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

// phpcs:disable WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- this class is the plugin's input layer: every value is unslashed here and cleaned by field type (sanitize_text_field, sanitize_textarea_field, or wp_kses_post for markup fields); the controller of each action checks its nonce before acting on the value.
class WPJOBPORTALrequest {
    /*
     * Check Request from both the Get and post method
     */

    static function getVar($wpjobportal_variable_name, $method = null, $wpjobportal_defaultvalue = null, $typecast = null,$editor_data=null) {
        $wpjobportal_value = null;
        if ($method == null) {
            if (isset($_GET[$wpjobportal_variable_name])) {
                if(is_array($_GET[$wpjobportal_variable_name])){
                    $wpjobportal_value = filter_var_array(wp_unslash($_GET[$wpjobportal_variable_name]));
                }else{
                    $wpjobportal_value = wpjobportal::wpjobportal_sanitizeData(wp_unslash($_GET[$wpjobportal_variable_name]));
                }
            } elseif (isset($_POST[$wpjobportal_variable_name])) {
                if(is_array($_POST[$wpjobportal_variable_name])){
                    $wpjobportal_value = filter_var_array(wp_unslash($_POST[$wpjobportal_variable_name]));
                }else{
                    if($editor_data == null){
                        $wpjobportal_value = wpjobportal::wpjobportal_sanitizeData(wp_unslash($_POST[$wpjobportal_variable_name]));
                    }else{
                        $wpjobportal_value = filter_var(wp_unslash($_POST[$wpjobportal_variable_name]),FILTER_DEFAULT);
                    }
                }
            } elseif (get_query_var($wpjobportal_variable_name)) {
                $wpjobportal_value = get_query_var($wpjobportal_variable_name);
            } elseif (isset(wpjobportal::$_data['sanitized_args'][$wpjobportal_variable_name]) && wpjobportal::$_data['sanitized_args'][$wpjobportal_variable_name] != '') {
                $wpjobportal_value = wpjobportal::$_data['sanitized_args'][$wpjobportal_variable_name];
            }
        } else {
            $method = wpjobportalphplib::wpJP_strtolower($method);
            switch ($method) {
                case 'post':
                    if (isset($_POST[$wpjobportal_variable_name]))
                        if (is_array($_POST[$wpjobportal_variable_name])) {
                            $wpjobportal_value = filter_var_array(wp_unslash($_POST[$wpjobportal_variable_name]));
                        }else{
                            if($editor_data == null){
                                $wpjobportal_value = wpjobportal::wpjobportal_sanitizeData(wp_unslash($_POST[$wpjobportal_variable_name]));
                            }else{
                                $wpjobportal_value = filter_var(wp_unslash($_POST[$wpjobportal_variable_name]),FILTER_DEFAULT);
                            }
                        }
                    break;
                case 'get':
                    if (isset($_GET[$wpjobportal_variable_name]))
                        if (is_array($_GET[$wpjobportal_variable_name])) {
                            $wpjobportal_value = filter_var_array(wp_unslash($_GET[$wpjobportal_variable_name]));
                        }else{
                            $wpjobportal_value = wpjobportal::wpjobportal_sanitizeData(wp_unslash($_GET[$wpjobportal_variable_name]));
                        }
                    break;
                case 'shortcode_option': // new case to handle shortcode attributes (since many variables have already used names so cant use above methods)
                    if (isset(wpjobportal::$_data['shortcode_options'][$wpjobportal_variable_name]) && wpjobportal::$_data['shortcode_options'][$wpjobportal_variable_name] != '') {
                        $wpjobportal_value = wpjobportal::$_data['shortcode_options'][$wpjobportal_variable_name];
                    }
                    break;
            }
        }
        if ($typecast != null) {
            $typecast = wpjobportalphplib::wpJP_strtolower($typecast);
            switch ($typecast) {
                case "int":
                    $wpjobportal_value = (int) $wpjobportal_value;
                    break;
                case "string":
                    $wpjobportal_value = (string) $wpjobportal_value;
                    break;
            }
        }
        if ($wpjobportal_value == null)
            $wpjobportal_value = $wpjobportal_defaultvalue;
        //echo print_r($wpjobportal_value); exit;
        return $wpjobportal_value;
    }

    /**
     * Whether the request carries a value under this name, as isset() would say.
     *
     * getVar() returns its default for an empty or "0" value, so it cannot
     * tell "sent as 0" from "not sent"; this can.
     *
     * @param string      $wpjobportal_variable_name Name to look for.
     * @param string|null $method                    get, post, or null for either.
     * @return bool
     */
    static function has($wpjobportal_variable_name, $method = null) {
        $method = $method === null ? null : wpjobportalphplib::wpJP_strtolower($method);
        if ($method === 'get') {
            return isset($_GET[$wpjobportal_variable_name]);
        }
        if ($method === 'post') {
            return isset($_POST[$wpjobportal_variable_name]);
        }
        return isset($_GET[$wpjobportal_variable_name]) || isset($_POST[$wpjobportal_variable_name]);
    }

    /**
     * A value exactly as sent (POST first, then GET), only unslashed, for a
     * caller that validates it itself - the action policies, which check
     * identifiers against what the user may touch, and add-on handlers that
     * apply their own sanitizer (sanitize_textarea_field() keeps line breaks,
     * which getVar()'s sanitize_text_field() would drop).
     *
     * @param string      $wpjobportal_variable_name Name to read.
     * @param string|null $method  'post' or 'get' to read only that source, 'request'
     *                             for $_REQUEST; null = POST first, then GET.
     * @param mixed       $wpjobportal_defaultvalue Returned when not sent.
     * @return mixed
     */
    static function raw($wpjobportal_variable_name, $method = null, $wpjobportal_defaultvalue = null) {
        $method = $method === null ? null : wpjobportalphplib::wpJP_strtolower($method);
        if ($method === 'request') {
            return isset($_REQUEST[$wpjobportal_variable_name]) ? wp_unslash($_REQUEST[$wpjobportal_variable_name]) : $wpjobportal_defaultvalue;
        }
        if ($method !== 'get' && isset($_POST[$wpjobportal_variable_name])) {
            return wp_unslash($_POST[$wpjobportal_variable_name]);
        }
        if ($method !== 'post' && isset($_GET[$wpjobportal_variable_name])) {
            return wp_unslash($_GET[$wpjobportal_variable_name]);
        }
        return $wpjobportal_defaultvalue;
    }

    /**
     * The path and query of the current request, as a clean URL.
     *
     * @return string
     */
    static function requestUri() {
        return isset($_SERVER['REQUEST_URI']) ? esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])) : '';
    }

    /**
     * Whether the browser sent this cookie.
     *
     * @param string $wpjobportal_name Cookie name.
     * @return bool
     */
    static function hasCookie($wpjobportal_name) {
        return isset($_COOKIE[$wpjobportal_name]);
    }

    /**
     * A cookie's value, unslashed and cleaned as text.
     *
     * @param string $wpjobportal_name    Cookie name.
     * @param mixed  $wpjobportal_default Returned when the cookie is not set.
     * @return mixed
     */
    static function cookie($wpjobportal_name, $wpjobportal_default = null) {
        if (!isset($_COOKIE[$wpjobportal_name])) {
            return $wpjobportal_default;
        }
        return wpjobportal::wpjobportal_sanitizeData(wp_unslash($_COOKIE[$wpjobportal_name]));
    }

    /**
     * A cookie that carries structured data (JSON or serialized), unslashed but
     * not text-cleaned: cleaning would break a serialized string's lengths. The
     * caller decodes it and checks the result (json_decode, or unserialize with
     * allowed_classes false).
     *
     * @param string $wpjobportal_name Cookie name.
     * @return string Empty when the cookie is not set.
     */
    static function rawCookie($wpjobportal_name) {
        return isset($_COOKIE[$wpjobportal_name]) ? (string) wp_unslash($_COOKIE[$wpjobportal_name]) : '';
    }

    /**
     * Whether a file field was sent at all, as isset()/!empty() on $_FILES would say.
     *
     * @param string $wpjobportal_field Form field name.
     * @return bool
     */
    static function hasFile($wpjobportal_field) {
        return !empty($_FILES[$wpjobportal_field]);
    }

    /**
     * Size in bytes of a single uploaded file, 0 when there is none.
     *
     * @param string $wpjobportal_field Form field name.
     * @return int
     */
    static function fileSize($wpjobportal_field) {
        return (isset($_FILES[$wpjobportal_field]['size']) && !is_array($_FILES[$wpjobportal_field]['size'])) ? absint($_FILES[$wpjobportal_field]['size']) : 0;
    }

    /**
     * One uploaded file, each part cleaned with WordPress's own functions, in
     * the shape wp_handle_upload() expects.
     *
     * @param string   $wpjobportal_field Form field name.
     * @param int|null $wpjobportal_index Which file of a multiple upload.
     * @return array|null Null when nothing was sent under that name.
     */
    static function file($wpjobportal_field, $wpjobportal_index = null) {
        if ($wpjobportal_index === null) {
            if (!isset($_FILES[$wpjobportal_field]['name'], $_FILES[$wpjobportal_field]['type'], $_FILES[$wpjobportal_field]['tmp_name'], $_FILES[$wpjobportal_field]['error'], $_FILES[$wpjobportal_field]['size']) || is_array($_FILES[$wpjobportal_field]['name'])) {
                return null;
            }
            return array(
                'name'     => sanitize_file_name($_FILES[$wpjobportal_field]['name']),
                'type'     => sanitize_mime_type($_FILES[$wpjobportal_field]['type']),
                'tmp_name' => sanitize_text_field($_FILES[$wpjobportal_field]['tmp_name']),
                'error'    => absint($_FILES[$wpjobportal_field]['error']),
                'size'     => absint($_FILES[$wpjobportal_field]['size']),
            );
        }
        if (!isset($_FILES[$wpjobportal_field]['name'][$wpjobportal_index], $_FILES[$wpjobportal_field]['type'][$wpjobportal_index], $_FILES[$wpjobportal_field]['tmp_name'][$wpjobportal_index], $_FILES[$wpjobportal_field]['error'][$wpjobportal_index], $_FILES[$wpjobportal_field]['size'][$wpjobportal_index])) {
            return null;
        }
        return array(
            'name'     => sanitize_file_name($_FILES[$wpjobportal_field]['name'][$wpjobportal_index]),
            'type'     => sanitize_mime_type($_FILES[$wpjobportal_field]['type'][$wpjobportal_index]),
            'tmp_name' => sanitize_text_field($_FILES[$wpjobportal_field]['tmp_name'][$wpjobportal_index]),
            'error'    => absint($_FILES[$wpjobportal_field]['error'][$wpjobportal_index]),
            'size'     => absint($_FILES[$wpjobportal_field]['size'][$wpjobportal_index]),
        );
    }

    /**
     * The indexes of a multiple upload's files.
     *
     * @param string $wpjobportal_field Form field name.
     * @return array
     */
    static function fileIndexes($wpjobportal_field) {
        if (!isset($_FILES[$wpjobportal_field]['name']) || !is_array($_FILES[$wpjobportal_field]['name'])) {
            return array();
        }
        return array_keys($_FILES[$wpjobportal_field]['name']);
    }

    /*
     * Check Request from both the Get and post method
     */

    /**
     * Fields that legitimately carry markup, so they are filtered rather than
     * stripped.
     *
     * Most of these are also re-read from $_POST by the model that saves them,
     * which overwrites whatever this array held; they are listed anyway so the
     * array is right on its own rather than right by accident. Two are not
     * re-read and depend on this list entirely: the company description, and
     * the cover letter submitted with a job application.
     *
     * Custom fields never need an entry. The custom field builder offers text,
     * textarea, date, email, file, radio and checkbox — no editor — so the
     * textarea default below already keeps everything they hold.
     *
     * @return array
     */
    static function htmlFields() {
        $wpjobportal_fields = array(
            'description',
            'decription',      // the Folder module's own spelling
            'coverletter_text',
            'jobseekermessage',
            'message',
            'body',
            'offline_text',
            'metadescription',
            'metakeywords',
        );

        /**
         * Add a field that holds markup, for a theme or an add-on whose editor
         * this list cannot know about.
         *
         * @param array $wpjobportal_fields Field names.
         */
        return (array) apply_filters('wpjobportal_request_html_fields', $wpjobportal_fields);
    }

    /**
     * One value, cleaned according to what its field is allowed to hold.
     *
     * @param mixed  $wpjobportal_value Value from the request.
     * @param string $wpjobportal_key   The field it arrived under.
     * @return mixed
     */
    private static function cleanValue($wpjobportal_value, $wpjobportal_key) {
        if (is_array($wpjobportal_value)) {
            $wpjobportal_clean = array();
            foreach ($wpjobportal_value as $wpjobportal_k => $wpjobportal_v) {
                // Nested values keep their parent's name, so a repeated editor
                // field is treated the same whether it arrives alone or in a set.
                $wpjobportal_clean[$wpjobportal_k] = self::cleanValue($wpjobportal_v, $wpjobportal_key);
            }
            return $wpjobportal_clean;
        }

        if (!is_string($wpjobportal_value)) {
            return $wpjobportal_value;
        }

        if (in_array($wpjobportal_key, self::htmlFields(), true)) {
            return wp_kses_post($wpjobportal_value);
        }

        // sanitize_textarea_field rather than sanitize_text_field: it strips
        // tags the same way but keeps newlines, which custom textareas and the
        // comment fields need. A single-line field has none to keep, so nothing
        // is lost by using it everywhere.
        return sanitize_textarea_field($wpjobportal_value);
    }

    /**
     * The whole request array, cleaned.
     *
     * This used to be filter_var_array() with no filter definition, which is
     * FILTER_UNSAFE_RAW — the superglobal, untouched, handed to seventy-odd
     * callers. Cleaning here rather than at each of them means a new caller is
     * safe by default instead of safe if somebody remembered.
     *
     * @param string $method post or get.
     * @return array|null
     */
    static function get($method = null) {
        $wpjobportal_array = null;
        if ($method != null) {
            $method = wpjobportalphplib::wpJP_strtolower($method);
            switch ($method) {
                case 'post':
                    $wpjobportal_array = wp_unslash($_POST);
                    break;
                case 'get':
                    $wpjobportal_array = wp_unslash($_GET);
                    break;
            }
        }

        if (!is_array($wpjobportal_array)) {
            return $wpjobportal_array;
        }

        $wpjobportal_clean = array();
        foreach ($wpjobportal_array as $wpjobportal_key => $wpjobportal_value) {
            $wpjobportal_clean[$wpjobportal_key] = self::cleanValue($wpjobportal_value, (string) $wpjobportal_key);
        }

        return $wpjobportal_clean;
    }

    /*
     * Check Request from both the Get and post method
     */

    static function getLayout($wpjobportal_layout, $method, $wpjobportal_defaultvalue) {
        $wpjobportal_layoutname = null;
        if ($method != null) {
            $method = wpjobportalphplib::wpJP_strtolower($method);
            switch ($method) {
                case 'post':
                    $wpjobportal_layoutname = isset($_POST[$wpjobportal_layout]) ? wpjobportal::wpjobportal_sanitizeData(wp_unslash($_POST[$wpjobportal_layout])) : null;
                    break;
                case 'get':
                    $wpjobportal_layoutname = isset($_GET[$wpjobportal_layout]) ? wpjobportal::wpjobportal_sanitizeData(wp_unslash($_GET[$wpjobportal_layout])) : null;
                    break;
            }
        } else {
            if (isset($_POST[$wpjobportal_layout]))
                $wpjobportal_layoutname = wpjobportal::wpjobportal_sanitizeData(wp_unslash($_POST[$wpjobportal_layout]));
            elseif (isset($_GET[$wpjobportal_layout]))
                $wpjobportal_layoutname = wpjobportal::wpjobportal_sanitizeData(wp_unslash($_GET[$wpjobportal_layout]));
            elseif (get_query_var($wpjobportal_layout))
                $wpjobportal_layoutname = get_query_var($wpjobportal_layout);
            elseif (isset(wpjobportal::$_data['sanitized_args'][$wpjobportal_layout]) && wpjobportal::$_data['sanitized_args'][$wpjobportal_layout] != '')
                $wpjobportal_layoutname = wpjobportal::$_data['sanitized_args'][$wpjobportal_layout];
        }
        if ($wpjobportal_layoutname == null) {
            $wpjobportal_layoutname = $wpjobportal_defaultvalue;
        }

        $wpjobportal_is_elementor_edit_mode = false;

        if ( class_exists('\Elementor\Plugin') && \Elementor\Plugin::$instance->editor ) {
            $wpjobportal_is_elementor_edit_mode = \Elementor\Plugin::$instance->editor->is_edit_mode();
        }

        if (is_admin() && $wpjobportal_is_elementor_edit_mode == false) {
        //if (is_admin() && !wp_is_json_request() && !wp_doing_ajax()) {
            $wpjobportal_layoutname = 'admin_' . $wpjobportal_layoutname;
        }
        return $wpjobportal_layoutname;
    }

}
// phpcs:enable WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

?>
