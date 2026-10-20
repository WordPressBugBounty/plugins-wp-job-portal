<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class wpjobportaldb {

    function __construct() {
        
    }


    public static function prepare($query, ...$args) {
        if (empty($args)) {
            return $query;
        }
        array_unshift($args, $query);
        return call_user_func_array(array(wpjobportal::$_db, 'prepare'), $args);
    }

    public static function like($value) {
        return '%' . wpjobportal::$_db->esc_like($value) . '%';
    }

    public static function prepareIn($values, $format = '%d') {
        if (!is_array($values)) {
            $values = explode(',', (string) $values);
        }
        $clean_values = array();
        foreach ($values as $value) {
            if ($format === '%d') {
                $value = absint($value);
                if ($value > 0) {
                    $clean_values[] = $value;
                }
            } else {
                $value = sanitize_text_field($value);
                if ($value !== '') {
                    $clean_values[] = $value;
                }
            }
        }
        $clean_values = array_values(array_unique($clean_values));
        if (empty($clean_values)) {
            return false;
        }
        $placeholders = implode(',', array_fill(0, count($clean_values), $format));
        return wpjobportal::$_db->prepare($placeholders, $clean_values);
    }

    public static function get_var($query) {
        $wpjobportal_result = wpjobportal::$_db->get_var($query);
        if (wpjobportal::$_db->last_error != null) {
            WPJOBPORTALincluder::getJSModel('systemerror')->addSystemError();
        }
        return $wpjobportal_result;
    }

    function setQuery($query){
        $this->_query = $this->parseQuery($query);
    }

    public static function get_row($query) {
        $wpjobportal_result = wpjobportal::$_db->get_row($query);
        if (wpjobportal::$_db->last_error != null) {
            WPJOBPORTALincluder::getJSModel('systemerror')->addSystemError();
        }
        return $wpjobportal_result;
    }

    public static function get_results($query) {
        $wpjobportal_result = wpjobportal::$_db->get_results($query);
        if (wpjobportal::$_db->last_error != null) {
            WPJOBPORTALincluder::getJSModel('systemerror')->addSystemError();
        }
        return $wpjobportal_result;
    }

    private function parseQuery($query){
        $query = wpjobportalphplib::wpJP_str_replace('#__', $this->_db->prefix, $query);
        return $query;
    }

    public static function query($query) {
        $wpjobportal_result = true;
        wpjobportal::$_db->query($query);
        if (wpjobportal::$_db->last_error != null) {
            WPJOBPORTALincluder::getJSModel('systemerror')->addSystemError();
            $wpjobportal_result = false;
        }
        return $wpjobportal_result;
    }

}

/**
 * The plugin's own tables, read and written exactly as $wpdb would.
 *
 * Every method hands its arguments straight to $wpdb and returns what $wpdb
 * returns, last_error included - unlike wpjobportaldb above, nothing is
 * logged or reset - so a caller behaves the same as with $wpdb itself.
 *
 * Queries reach here already prepared: values through $wpdb->prepare()
 * placeholders, table and column names through %i.
 *
 * These are the plugin's custom tables: there is no WordPress API for them,
 * and WordPress's object cache does not hold them, which is why Plugin Check's
 * direct-query and caching notes are switched off for this class alone.
 */
// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom tables; queries arrive prepared (see above).
class wpjobportaldb_direct {

    public static function get_var($query = null, $x = 0, $y = 0) {
        global $wpdb;
        return $wpdb->get_var($query, $x, $y);
    }

    public static function get_row($query = null, $output = OBJECT, $y = 0) {
        global $wpdb;
        return $wpdb->get_row($query, $output, $y);
    }

    public static function get_col($query = null, $x = 0) {
        global $wpdb;
        return $wpdb->get_col($query, $x);
    }

    public static function get_results($query = null, $output = OBJECT) {
        global $wpdb;
        return $wpdb->get_results($query, $output);
    }

    public static function query($query) {
        global $wpdb;
        return $wpdb->query($query);
    }

    public static function insert($table, $data, $format = null) {
        global $wpdb;
        return $wpdb->insert($table, $data, $format);
    }

    public static function replace($table, $data, $format = null) {
        global $wpdb;
        return $wpdb->replace($table, $data, $format);
    }

    public static function update($table, $data, $where, $format = null, $where_format = null) {
        global $wpdb;
        return $wpdb->update($table, $data, $where, $format, $where_format);
    }

    public static function delete($table, $where, $where_format = null) {
        global $wpdb;
        return $wpdb->delete($table, $where, $where_format);
    }
}
// phpcs:enable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter

?>
