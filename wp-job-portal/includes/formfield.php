<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALformfield {

    /**
     * Add the shared Release 3 form contract without removing legacy classes.
     *
     * @param string $wpjobportal_name Field name.
     * @param array  $wpjobportal_extraattr Existing attributes.
     * @param string $wpjobportal_class Shared component class.
     * @param string $wpjobportal_type Optional input type.
     * @return array
     */
    private static function prepareControlAttributes($wpjobportal_name, $wpjobportal_extraattr, $wpjobportal_class = 'wpjp-ds-control', $wpjobportal_type = '') {
        if (!is_array($wpjobportal_extraattr)) {
            $wpjobportal_extraattr = array();
        }

        $wpjobportal_classes = isset($wpjobportal_extraattr['class'])
            ? preg_split('/\s+/', trim((string) $wpjobportal_extraattr['class']))
            : array();
        $wpjobportal_classes = array_filter((array) $wpjobportal_classes);
        if (!in_array($wpjobportal_class, $wpjobportal_classes, true)) {
            $wpjobportal_classes[] = $wpjobportal_class;
        }
        $wpjobportal_extraattr['class'] = implode(' ', $wpjobportal_classes);

        $wpjobportal_validation = isset($wpjobportal_extraattr['data-validation'])
            ? (string) $wpjobportal_extraattr['data-validation']
            : '';
        if (preg_match('/(^|\s|required)/i', $wpjobportal_validation) && false !== stripos($wpjobportal_validation, 'required')) {
            $wpjobportal_extraattr['aria-required'] = 'true';
        }

        $wpjobportal_normalized_name = strtolower(preg_replace('/[^a-z0-9_]+/i', '_', (string) $wpjobportal_name));
        if (!isset($wpjobportal_extraattr['autocomplete'])) {
            $wpjobportal_autocomplete = self::getAutocompleteValue($wpjobportal_normalized_name, $wpjobportal_type);
            if ('' !== $wpjobportal_autocomplete) {
                $wpjobportal_extraattr['autocomplete'] = $wpjobportal_autocomplete;
            }
        }
        if (!isset($wpjobportal_extraattr['inputmode'])) {
            $wpjobportal_inputmode = self::getInputMode($wpjobportal_normalized_name, $wpjobportal_type);
            if ('' !== $wpjobportal_inputmode) {
                $wpjobportal_extraattr['inputmode'] = $wpjobportal_inputmode;
            }
        }

        return $wpjobportal_extraattr;
    }

    /**
     * @param string $wpjobportal_name Normalized field name.
     * @param string $wpjobportal_type Input type.
     * @return string
     */
    private static function getAutocompleteValue($wpjobportal_name, $wpjobportal_type) {
        if ('email' === $wpjobportal_type || false !== strpos($wpjobportal_name, 'email')) {
            return 'email';
        }
        if (false !== strpos($wpjobportal_name, 'first_name') || false !== strpos($wpjobportal_name, 'user_first')) {
            return 'given-name';
        }
        if (false !== strpos($wpjobportal_name, 'last_name') || false !== strpos($wpjobportal_name, 'user_last')) {
            return 'family-name';
        }
        if (false !== strpos($wpjobportal_name, 'phone') || false !== strpos($wpjobportal_name, 'mobile')) {
            return 'tel';
        }
        if (false !== strpos($wpjobportal_name, 'website') || false !== strpos($wpjobportal_name, 'url')) {
            return 'url';
        }
        if ('password' === $wpjobportal_type) {
            return false !== strpos($wpjobportal_name, 'confirm') ? 'new-password' : 'current-password';
        }

        return '';
    }

    /**
     * @param string $wpjobportal_name Normalized field name.
     * @param string $wpjobportal_type Input type.
     * @return string
     */
    private static function getInputMode($wpjobportal_name, $wpjobportal_type) {
        if ('email' === $wpjobportal_type || false !== strpos($wpjobportal_name, 'email')) {
            return 'email';
        }
        if (false !== strpos($wpjobportal_name, 'phone') || false !== strpos($wpjobportal_name, 'mobile')) {
            return 'tel';
        }
        if (false !== strpos($wpjobportal_name, 'website') || false !== strpos($wpjobportal_name, 'url')) {
            return 'url';
        }
        if (preg_match('/(^|_)(salary|amount|number|noofjobs|zip|postal)(_|$)/', $wpjobportal_name)) {
            return 'decimal';
        }

        return '';
    }

    /**
     * Add label/help relationships to controls rendered by legacy templates.
     *
     * @param string $wpjobportal_html Control HTML.
     * @param string $wpjobportal_label_id Visible label id.
     * @param string $wpjobportal_description_id Optional help-text id.
     * @param bool   $wpjobportal_required Whether the field is required.
     * @return string
     */
    public static function prepareAccessibleContent($wpjobportal_html, $wpjobportal_label_id, $wpjobportal_description_id = '', $wpjobportal_required = false) {
        $wpjobportal_label_id = sanitize_html_class((string) $wpjobportal_label_id);
        $wpjobportal_description_id = sanitize_html_class((string) $wpjobportal_description_id);

        return preg_replace_callback(
            '/<(input|select|textarea)\b([^>]*)>/i',
            static function ($wpjobportal_matches) use ($wpjobportal_label_id, $wpjobportal_description_id, $wpjobportal_required) {
                $wpjobportal_tag = strtolower($wpjobportal_matches[1]);
                $wpjobportal_attributes = $wpjobportal_matches[2];
                if ('input' === $wpjobportal_tag && preg_match('/\btype\s*=\s*(["\'])hidden\1/i', $wpjobportal_attributes)) {
                    return $wpjobportal_matches[0];
                }
                $wpjobportal_self_closing = (bool) preg_match('/\s*\/\s*$/', $wpjobportal_attributes);
                if ($wpjobportal_self_closing) {
                    $wpjobportal_attributes = preg_replace('/\s*\/\s*$/', '', $wpjobportal_attributes);
                }

                $wpjobportal_attributes = self::addHtmlAttribute($wpjobportal_attributes, 'aria-labelledby', $wpjobportal_label_id);
                if ('' !== $wpjobportal_description_id) {
                    $wpjobportal_attributes = self::appendHtmlAttributeToken($wpjobportal_attributes, 'aria-describedby', $wpjobportal_description_id);
                }
                if ($wpjobportal_required) {
                    $wpjobportal_attributes = self::addHtmlAttribute($wpjobportal_attributes, 'aria-required', 'true');
                }

                $wpjobportal_non_box_input = 'input' !== $wpjobportal_tag
                    || !preg_match('/\btype\s*=\s*(["\'])(checkbox|radio)\1/i', $wpjobportal_attributes);
                if ($wpjobportal_non_box_input) {
                    $wpjobportal_attributes = self::appendHtmlAttributeToken($wpjobportal_attributes, 'class', 'wpjp-ds-control');
                }

                return '<' . $wpjobportal_tag . $wpjobportal_attributes . ($wpjobportal_self_closing ? ' /' : '') . '>';
            },
            (string) $wpjobportal_html
        );
    }

    /**
     * @param string $wpjobportal_html Control HTML.
     * @return string
     */
    public static function getFirstControlId($wpjobportal_html) {
        if (preg_match('/<(?:input|select|textarea)\b[^>]*\bid\s*=\s*(["\'])([^"\']+)\1/i', (string) $wpjobportal_html, $wpjobportal_matches)) {
            return sanitize_html_class($wpjobportal_matches[2]);
        }
        return '';
    }

    /**
     * @param string $wpjobportal_attributes Raw HTML attributes including leading spaces.
     * @param string $wpjobportal_name Attribute name.
     * @param string $wpjobportal_value Attribute value.
     * @return string
     */
    private static function addHtmlAttribute($wpjobportal_attributes, $wpjobportal_name, $wpjobportal_value) {
        if ('' === $wpjobportal_value || preg_match('/\b' . preg_quote($wpjobportal_name, '/') . '\s*=/i', $wpjobportal_attributes)) {
            return $wpjobportal_attributes;
        }
        return $wpjobportal_attributes . ' ' . $wpjobportal_name . '="' . esc_attr($wpjobportal_value) . '"';
    }

    /**
     * @param string $wpjobportal_attributes Raw HTML attributes including leading spaces.
     * @param string $wpjobportal_name Attribute name.
     * @param string $wpjobportal_token Token to append.
     * @return string
     */
    private static function appendHtmlAttributeToken($wpjobportal_attributes, $wpjobportal_name, $wpjobportal_token) {
        if ('' === $wpjobportal_token) {
            return $wpjobportal_attributes;
        }
        $wpjobportal_pattern = '/\b' . preg_quote($wpjobportal_name, '/') . '\s*=\s*(["\'])([^"\']*)\1/i';
        if (!preg_match($wpjobportal_pattern, $wpjobportal_attributes, $wpjobportal_matches)) {
            return self::addHtmlAttribute($wpjobportal_attributes, $wpjobportal_name, $wpjobportal_token);
        }
        $wpjobportal_tokens = preg_split('/\s+/', trim($wpjobportal_matches[2]));
        if (in_array($wpjobportal_token, (array) $wpjobportal_tokens, true)) {
            return $wpjobportal_attributes;
        }
        $wpjobportal_value = trim($wpjobportal_matches[2] . ' ' . $wpjobportal_token);
        return preg_replace($wpjobportal_pattern, $wpjobportal_name . '="' . esc_attr($wpjobportal_value) . '"', $wpjobportal_attributes, 1);
    }
    /*
     * Create the form text field
     */

    static function text($wpjobportal_name, $wpjobportal_value, $wpjobportal_extraattr = array(),$wpjobportal_over_limit = 0) {
        $wpjobportal_extraattr = self::prepareControlAttributes($wpjobportal_name, $wpjobportal_extraattr, 'wpjp-ds-control', 'text');
        $wpjobportal_textfield = '<input type="text" name="' . $wpjobportal_name . '" id="' . $wpjobportal_name . '"
        value="' .  wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_value) . '" ';
        if (!empty($wpjobportal_extraattr))
            foreach ($wpjobportal_extraattr AS $wpjobportal_key => $wpjobportal_val)
                $wpjobportal_textfield .= ' ' . $wpjobportal_key . '="' . $wpjobportal_val . '"';
        if($wpjobportal_over_limit == 0){
            $wpjobportal_textfield .= ' maxlength="255" ';
        }
        $wpjobportal_textfield .= ' />';

        return $wpjobportal_textfield;
    }

    static function email($wpjobportal_name, $wpjobportal_value, $wpjobportal_extraattr = array()) {
        $wpjobportal_extraattr = self::prepareControlAttributes($wpjobportal_name, $wpjobportal_extraattr, 'wpjp-ds-control', 'email');
        $wpjobportal_textfield = '<input type="email" name="' . $wpjobportal_name . '" id="' . $wpjobportal_name . '"
        value="' .  wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_value) . '" ';
        if (!empty($wpjobportal_extraattr))
            foreach ($wpjobportal_extraattr AS $wpjobportal_key => $wpjobportal_val)
                $wpjobportal_textfield .= ' ' . $wpjobportal_key . '="' . $wpjobportal_val . '"';
        $wpjobportal_textfield .= ' />';
        return $wpjobportal_textfield;
    }

    /*
     * Create the form password field
     */

    static function password($wpjobportal_name, $wpjobportal_value, $wpjobportal_extraattr = array()) {
        $wpjobportal_extraattr = self::prepareControlAttributes($wpjobportal_name, $wpjobportal_extraattr, 'wpjp-ds-control', 'password');
        $wpjobportal_textfield = '<input type="password" name="' . $wpjobportal_name . '" id="' . $wpjobportal_name . '" value="' .  wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_value) . '" ';
        if (!empty($wpjobportal_extraattr))
            foreach ($wpjobportal_extraattr AS $wpjobportal_key => $wpjobportal_val)
                $wpjobportal_textfield .= ' ' . $wpjobportal_key . '="' . $wpjobportal_val . '"';
        $wpjobportal_textfield .= ' />';
        return $wpjobportal_textfield;
    }

    /*
     * Create the form text area
     */

    static function textarea($wpjobportal_name, $wpjobportal_value, $wpjobportal_extraattr = array()) {
        $wpjobportal_extraattr = self::prepareControlAttributes($wpjobportal_name, $wpjobportal_extraattr);
        $wpjobportal_textarea = '<textarea name="' . $wpjobportal_name . '" id="' . $wpjobportal_name . '" ';
        if (!empty($wpjobportal_extraattr))
            foreach ($wpjobportal_extraattr AS $wpjobportal_key => $wpjobportal_val)
                $wpjobportal_textarea .= ' ' . $wpjobportal_key . '="' . $wpjobportal_val . '"';
        // Escaped like every other control here. Unescaped, a front-end form (shortcode output, which
        // wptexturize runs over and does not skip <textarea>) turned "cloud" into “cloud” on every save,
        // and a stored </textarea> ended the field early. Entities already in the value (saves store & as
        // &amp;) are not encoded again, so the field still shows & as it did before.
        $wpjobportal_textarea .= ' >' . htmlspecialchars((string) $wpjobportal_value, ENT_QUOTES, 'UTF-8', false) . '</textarea>';
        return $wpjobportal_textarea;
    }

    /*
     * Create the form hidden field
     */

    static function hidden($wpjobportal_name, $wpjobportal_value, $wpjobportal_extraattr = array(),$wpjobportal_id='') {
        $wpjobportal_textfield = '';
        if($wpjobportal_id == ''){
            $wpjobportal_id = $wpjobportal_name;
        }
        if(is_array($wpjobportal_value)){
            if(wpjobportalphplib::wpJP_strstr($wpjobportal_name, '[]')){
                for ($wpjobportal_i=0; $wpjobportal_i < count($wpjobportal_value) ; $wpjobportal_i++) {
                    $wpjobportal_textfield .= "<input type='hidden' name='" . $wpjobportal_name . "' id='" . $wpjobportal_id . "' value='" .  wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_value[$wpjobportal_i]) . "' /> ";
                }
                return $wpjobportal_textfield;
            }
        }
        $wpjobportal_textfield = "<input type='hidden' name='" . $wpjobportal_name . "' id='" . $wpjobportal_id . "' value='" . sanitize_text_field( wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_value)) . "'" ;
        
        if (!empty($wpjobportal_extraattr))
            foreach ($wpjobportal_extraattr AS $wpjobportal_key => $wpjobportal_val)
                $wpjobportal_textfield .= " " . $wpjobportal_key . "='" . $wpjobportal_val . "'";
        $wpjobportal_textfield .= " />";
        return $wpjobportal_textfield;
    }

    /*
     * Create the form submitbutton
     */

    static function submitbutton($wpjobportal_name, $wpjobportal_value, $wpjobportal_extraattr = array()) {
        $wpjobportal_extraattr = self::prepareControlAttributes($wpjobportal_name, $wpjobportal_extraattr, 'wpjp-ds-button');
        $wpjobportal_textfield = '<input type="submit" name="' . $wpjobportal_name . '" id="' . $wpjobportal_name . '" value="' .  wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_value) . '" ';
        if (!empty($wpjobportal_extraattr))
            foreach ($wpjobportal_extraattr AS $wpjobportal_key => $wpjobportal_val)
                $wpjobportal_textfield .= ' ' . $wpjobportal_key . '="' . $wpjobportal_val . '"';
        $wpjobportal_textfield .= ' />';
        return $wpjobportal_textfield;
    }

    /*
     * Create the form button
     */

    static function button($wpjobportal_name, $wpjobportal_value, $wpjobportal_extraattr = array()) {
        $wpjobportal_extraattr = self::prepareControlAttributes($wpjobportal_name, $wpjobportal_extraattr, 'wpjp-ds-button');
        $wpjobportal_textfield = '<input type="button" name="' . $wpjobportal_name . '" id="' . $wpjobportal_name . '" value="' .  wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_value) . '" ';
        if (!empty($wpjobportal_extraattr))
            foreach ($wpjobportal_extraattr AS $wpjobportal_key => $wpjobportal_val)
                $wpjobportal_textfield .= ' ' . $wpjobportal_key . '="' . $wpjobportal_val . '"';
        $wpjobportal_textfield .= ' />';
        return $wpjobportal_textfield;
    }

    static function resumeSelect($wpjobportal_fieldName, $list, $wpjobportal_defaultvalue,$wpjobportal_section,$title = '', $wpjobportal_extraattr = array()) {
        $wpjobportal_name = $wpjobportal_section.'['.$wpjobportal_fieldName.']';
        $wpjobportal_extraattr = self::prepareControlAttributes($wpjobportal_name, $wpjobportal_extraattr);

        $wpjobportal_selectfield = '<select name="' . $wpjobportal_name . '" id="' . $wpjobportal_fieldName . '" ';
        if (!empty($wpjobportal_extraattr))
            foreach ($wpjobportal_extraattr AS $wpjobportal_key => $wpjobportal_val) {
                $wpjobportal_selectfield .= ' ' . $wpjobportal_key . '="' .  wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_val) . '"';
            }
        $wpjobportal_selectfield .= ' >';
        if ($title != '') {
            $wpjobportal_selectfield .= '<option value="">' . $title . '</option>';
        }
        if($wpjobportal_defaultvalue == ''){
            $wpjobportal_defaultvalue = -9999; // B/c '' == 0 in php
        }
        if (!empty($list))
            foreach ($list AS $record) {
                if ((is_array($wpjobportal_defaultvalue) && in_array($record->id, $wpjobportal_defaultvalue)) || $wpjobportal_defaultvalue == $record->id)
                    $wpjobportal_selectfield .= '<option selected="selected" value="' .  wpjobportalphplib::wpJP_htmlspecialchars($record->id) . '">' . wpjobportal::wpjobportal_getVariableValue($record->text) . '</option>';
                else
                    $wpjobportal_selectfield .= '<option value="' .  wpjobportalphplib::wpJP_htmlspecialchars($record->id) . '">' . wpjobportal::wpjobportal_getVariableValue($record->text) . '</option>';
            }

        $wpjobportal_selectfield .= '</select>';
        return $wpjobportal_selectfield;
    }



    /*
     * Create the form select field
     */

    static function select($wpjobportal_name, $list, $wpjobportal_defaultvalue, $title = '', $wpjobportal_extraattr = array()) {
        $wpjobportal_extraattr = self::prepareControlAttributes($wpjobportal_name, $wpjobportal_extraattr);
        $wpjobportal_selectfield = '<select name="' . $wpjobportal_name . '" id="' . $wpjobportal_name . '" ';
        if (!empty($wpjobportal_extraattr))
            foreach ($wpjobportal_extraattr AS $wpjobportal_key => $wpjobportal_val) {
                $wpjobportal_selectfield .= ' ' . $wpjobportal_key . '="' .  wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_val) . '"';
            }
        $wpjobportal_selectfield .= ' >';
        if ($title != '') {
            $wpjobportal_selectfield .= '<option value="">' . $title . '</option>';
        }
        if($wpjobportal_defaultvalue == ''){
            $wpjobportal_defaultvalue = -9999; // B/c '' == 0 in php
        }
        if (!empty($list))
            foreach ($list AS $record) {
                $wpjobportal_class=isset($record->class)?$record->class:"";
                if ((is_array($wpjobportal_defaultvalue) && in_array($record->id, $wpjobportal_defaultvalue)) || $wpjobportal_defaultvalue == $record->id)
                    $wpjobportal_selectfield .= '<option class="' . $wpjobportal_class . '"  selected="selected" value="' .  wpjobportalphplib::wpJP_htmlspecialchars($record->id) . '">' . wpjobportal::wpjobportal_getVariableValue($record->text) . '</option>';
                else
                    $wpjobportal_selectfield .= '<option class="' . $wpjobportal_class . '" value="' .  wpjobportalphplib::wpJP_htmlspecialchars($record->id) . '">' . wpjobportal::wpjobportal_getVariableValue($record->text) . '</option>';
            }

        $wpjobportal_selectfield .= '</select>';
        return $wpjobportal_selectfield;
    }

    /*
     * Create the form radio button
     */

    static function radiobutton($wpjobportal_name, $list, $wpjobportal_defaultvalue, $wpjobportal_extraattr = array()) {
        $radiobutton = '';
        $wpjobportal_count = 1;
        foreach ($list AS $wpjobportal_value => $wpjobportal_label) {
            //for admin forms added field wrapper
            $radiobutton .= '<span class="wpjobportal-form-radio-field" >';
            $radiobutton .= '<input type="radio" name="' . $wpjobportal_name . '" id="' . $wpjobportal_name . $wpjobportal_count . '" value="' .  wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_value) . '"';
            if ($wpjobportal_defaultvalue == $wpjobportal_value)
                $radiobutton .= ' checked="checked"';

            if (!empty($wpjobportal_extraattr))
                foreach ($wpjobportal_extraattr AS $wpjobportal_key => $wpjobportal_val) {
                    $radiobutton .= ' ' . $wpjobportal_key . '="' . $wpjobportal_val . '"';
                }
            $radiobutton .= '/><label id="for' . $wpjobportal_name . '" for="' . $wpjobportal_name . $wpjobportal_count . '">' . $wpjobportal_label . '</label>';
            $radiobutton .= '</span>';
            $wpjobportal_count++;
        }
        return $radiobutton;
    }

    /*
     * Create the form checkbox
     */

    static function checkbox($wpjobportal_name, $list, $wpjobportal_defaultvalue, $wpjobportal_extraattr = array()) {
        $wpjobportal_checkbox = '';
        $wpjobportal_count = 1;
        foreach ($list AS $wpjobportal_value => $wpjobportal_label) {
            //for admin forms added field wrapper
            $wpjobportal_checkbox .= '<span class="wpjobportal-form-chkbox-field" >';
            $wpjobportal_checkbox .= '<input type="checkbox" name="' . $wpjobportal_name . '" id="' . $wpjobportal_name . $wpjobportal_count . '" value="' .  wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_value) . '"';
            if ($wpjobportal_defaultvalue == $wpjobportal_value)
                $wpjobportal_checkbox .= ' checked="checked"';
            if (!empty($wpjobportal_extraattr))
                foreach ($wpjobportal_extraattr AS $wpjobportal_key => $wpjobportal_val) {
                    $wpjobportal_checkbox .= ' ' . $wpjobportal_key . '="' . $wpjobportal_val . '"';
                }
            $wpjobportal_checkbox .= '/><label id="for' . $wpjobportal_name . '" for="' . $wpjobportal_name . $wpjobportal_count . '">' . $wpjobportal_label . '</label>';
            $wpjobportal_checkbox .= '</span>';
            $wpjobportal_count++;
        }
        return $wpjobportal_checkbox;
    }

    /*
     * Create the form wp editor
     */

    static function editor($wpjobportal_name, $wpjobportal_defaultvalue='') {
        $wpjobportal_settings = array(
            //'textarea_name' => isset( $wpjobportal_field['name'] ) ? $wpjobportal_field['name'] : $wpjobportal_key,
            'media_buttons' => false,
            'textarea_rows' => 8,
            'quicktags'     => false,
            'tinymce'       => array(
                'plugins'                       => 'lists,paste,tabfocus,wplink,wordpress',
                'paste_as_text'                 => true,
                'paste_auto_cleanup_on_paste'   => true,
                'paste_remove_spans'            => true,
                'paste_remove_styles'           => true,
                'paste_remove_styles_if_webkit' => true,
                'paste_strip_class_attributes'  => true,
                'toolbar1'                      => 'bold,italic,|,bullist,numlist,|,link,unlink,|,undo,redo',
                'toolbar2'                      => '',
                'toolbar3'                      => '',
                'toolbar4'                      => ''
            ),
        );
        ob_start();
        wp_editor( !empty($wpjobportal_defaultvalue) ? wp_kses_post($wpjobportal_defaultvalue) : '', $wpjobportal_name, $wpjobportal_settings);
        return ob_get_clean();
    }

}

?>
