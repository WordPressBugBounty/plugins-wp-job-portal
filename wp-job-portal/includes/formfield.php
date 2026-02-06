<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALformfield {
    /*
     * Create the form text field
     */
    static function resumetext($wpjobportal_fieldName, $wpjobportal_value,$wpjobportal_section, $wpjobportal_extraattr = array(),$wpjobportal_over_limit = 0) {
        $wpjobportal_name = $wpjobportal_section.'['.$wpjobportal_fieldName.']';
        $wpjobportal_textfield = '<input type="text" name="' . $wpjobportal_name . '" id="' . $wpjobportal_fieldName . '"
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

    static function text($wpjobportal_name, $wpjobportal_value, $wpjobportal_extraattr = array(),$wpjobportal_over_limit = 0) {
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
        $wpjobportal_textarea = '<textarea name="' . $wpjobportal_name . '" id="' . $wpjobportal_name . '" ';
        if (!empty($wpjobportal_extraattr))
            foreach ($wpjobportal_extraattr AS $wpjobportal_key => $wpjobportal_val)
                $wpjobportal_textarea .= ' ' . $wpjobportal_key . '="' . $wpjobportal_val . '"';
        $wpjobportal_textarea .= ' >' . $wpjobportal_value . '</textarea>';
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
        $wpjobportal_textfield = '<input type="button" name="' . $wpjobportal_name . '" id="' . $wpjobportal_name . '" value="' .  wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_value) . '" ';
        if (!empty($wpjobportal_extraattr))
            foreach ($wpjobportal_extraattr AS $wpjobportal_key => $wpjobportal_val)
                $wpjobportal_textfield .= ' ' . $wpjobportal_key . '="' . $wpjobportal_val . '"';
        $wpjobportal_textfield .= ' />';
        return $wpjobportal_textfield;
    }

    static function resumeSelect($wpjobportal_fieldName, $list, $wpjobportal_defaultvalue,$wpjobportal_section,$title = '', $wpjobportal_extraattr = array()) {
        $wpjobportal_name = $wpjobportal_section.'['.$wpjobportal_fieldName.']';

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
                    $wpjobportal_selectfield .= '<option selected="selected" value="' . $record->id . '">' . wpjobportal::wpjobportal_getVariableValue($record->text) . '</option>';
                else
                    $wpjobportal_selectfield .= '<option value="' . $record->id . '">' . wpjobportal::wpjobportal_getVariableValue($record->text) . '</option>';
            }

        $wpjobportal_selectfield .= '</select>';
        return $wpjobportal_selectfield;
    }



    /*
     * Create the form select field
     */

    static function select($wpjobportal_name, $list, $wpjobportal_defaultvalue, $title = '', $wpjobportal_extraattr = array()) {
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
                    $wpjobportal_selectfield .= '<option class="' . $wpjobportal_class . '"  selected="selected" value="' . $record->id . '">' . wpjobportal::wpjobportal_getVariableValue($record->text) . '</option>';
                else
                    $wpjobportal_selectfield .= '<option class="' . $wpjobportal_class . '" value="' . $record->id . '">' . wpjobportal::wpjobportal_getVariableValue($record->text) . '</option>';
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
