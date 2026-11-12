<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALtable {

    public $isnew = false;
    public $columns = array();
    public $primarykey = '';
    public $tablename = '';

    function __construct($tbl, $pk) {
        $this->tablename = wpjobportal::$_db->prefix . 'wj_portal_' . $tbl;
        $this->primarykey = $pk;
    }

    public function bind($wpjobportal_data) {
        if ((!is_array($wpjobportal_data)) || (empty($wpjobportal_data)))
            return false;
        if (isset($wpjobportal_data['id']) && !empty($wpjobportal_data['id'])) { // Edit case
            $this->isnew = false;
        } else { // New case
            $this->isnew = true;
        }
        $wpjobportal_result = $this->setColumns($wpjobportal_data);
        return $wpjobportal_result;
    }

    protected function setColumns($wpjobportal_data) {
        if ($this->isnew == true) { // new record insert
            $wpjobportal_array = get_object_vars($this);
		if(isset($wpjobportal_array['id'])){
		    unset($wpjobportal_array['id']);
		}
            unset($wpjobportal_array['isnew']);
            unset($wpjobportal_array['primarykey']);
            unset($wpjobportal_array['tablename']);
            unset($wpjobportal_array['columns']);
            foreach ($wpjobportal_array AS $wpjobportal_k => $v) {
                if (isset($wpjobportal_data[$wpjobportal_k])) {
                    $this->$wpjobportal_k = $wpjobportal_data[$wpjobportal_k];
                }
                $this->columns[$wpjobportal_k] = $this->$wpjobportal_k;
            }
        } else { // update record
            if (isset($wpjobportal_data[$this->primarykey])) {
                // Security (SQLi, reported Nov 2026): take the column names from
                // this table's own declared properties, never from the request
                // keys. A request key named "columns" would otherwise replace the
                // whole column map, and a crafted key such as "name` = SLEEP(1)-- "
                // would reach $wpdb->update() as a raw SQL identifier. Only real,
                // declared columns are written, exactly as the insert branch does.
                $wpjobportal_array = get_object_vars($this);
                unset($wpjobportal_array['isnew'], $wpjobportal_array['columns'], $wpjobportal_array['primarykey'], $wpjobportal_array['tablename']);
                foreach ($wpjobportal_array AS $wpjobportal_k => $v) {
                    if (array_key_exists($wpjobportal_k, $wpjobportal_data)) {
                        $this->$wpjobportal_k = $wpjobportal_data[$wpjobportal_k];
                        $this->columns[$wpjobportal_k] = $wpjobportal_data[$wpjobportal_k];
                    }
                }
            } else {
                return false; // record cannot be updated b/c of pk not exist
            }
        }
        return true;
    }

    /**
     * Keep latitude/longitude columns numeric.
     *
     * Security: coordinates are typed by employers (job map) and job seekers
     * (resume address map) and are later printed inside inline JavaScript
     * strings and HTML attributes. sanitize_text_field() keeps quotes and
     * backslashes, so a crafted "coordinate" could break out of those contexts
     * (stored XSS on the public job page and the admin Edit Job / Edit Resume
     * screens). Every table with these columns stores through here, so accept
     * only a decimal number, or a comma-separated list of them (a job with
     * several cities), and store anything else as empty.
     */
    protected function normalizeCoordinateColumns() {
        foreach (array('latitude', 'longitude') as $wpjobportal_col) {
            if (!is_array($this->columns) || !array_key_exists($wpjobportal_col, $this->columns)) {
                continue;
            }
            $wpjobportal_value = $this->columns[$wpjobportal_col];
            if ($wpjobportal_value === null || $wpjobportal_value === '') {
                continue;
            }
            $wpjobportal_parts = explode(',', str_replace(' ', '', (string) $wpjobportal_value));
            $wpjobportal_clean = array();
            foreach ($wpjobportal_parts as $wpjobportal_part) {
                if ($wpjobportal_part === '') {
                    continue;
                }
                if (!preg_match('/^-?\d{1,3}(\.\d{1,20})?$/', $wpjobportal_part)) {
                    $wpjobportal_clean = array();
                    break;
                }
                $wpjobportal_clean[] = $wpjobportal_part;
            }
            $wpjobportal_value = implode(',', $wpjobportal_clean);
            $this->columns[$wpjobportal_col] = $wpjobportal_value;
            if (property_exists($this, $wpjobportal_col)) {
                $this->$wpjobportal_col = $wpjobportal_value;
            }
        }
    }

    function store() {
        $this->normalizeCoordinateColumns();
        // Security (SQLi, reported Nov 2026): a real column name is always
        // [A-Za-z0-9_]. Drop any other key so a crafted identifier can never
        // reach $wpdb->insert()/update() unescaped, whatever built $columns.
        if (is_array($this->columns)) {
            foreach (array_keys($this->columns) as $wpjobportal_col) {
                if (!is_string($wpjobportal_col) || !preg_match('/^[A-Za-z0-9_]+$/', $wpjobportal_col)) {
                    unset($this->columns[$wpjobportal_col]);
                }
            }
        }
        if ($this->isnew == true) { // new record store
            wpjobportal::$_db->insert($this->tablename, $this->columns);
            if (wpjobportal::$_db->last_error == null) {
                $this->{$this->primarykey} = wpjobportal::$_db->insert_id;
                $wpjobportal_id = wpjobportal::$_db->insert_id;
                //activity log //1 for insert
                WPJOBPORTALincluder::getJSModel('activitylog')->storeActivity(1, $this->tablename, $this->columns, $wpjobportal_id);
            } else {
                WPJOBPORTALincluder::getJSModel('systemerror')->addSystemError();
                return false;
            }
        } else { // record updated
            wpjobportal::$_db->update($this->tablename, $this->columns, array($this->primarykey => $this->columns[$this->primarykey]));
            WPJOBPORTALincluder::getJSModel('activitylog')->storeActivity(2, $this->tablename, $this->columns);
            if (wpjobportal::$_db->last_error != null) {
                WPJOBPORTALincluder::getJSModel('systemerror')->addSystemError();
                return false;
            }
        }
        return true;
    }

    function update($wpjobportal_data) {
        $wpjobportal_result = $this->bind($wpjobportal_data);
        if ($wpjobportal_result == false) {
            return false;
        }
        $wpjobportal_result = $this->store();
        if ($wpjobportal_result == false) {
            return false;
        }
        return true;
    }

    function delete($wpjobportal_id) {
        if (!is_numeric($wpjobportal_id))
            return false;
        //data for delete
        $wpjobportal_data = WPJOBPORTALincluder::getJSModel('activitylog')->getDeleteActionDataToStore($this->tablename, $wpjobportal_id);
        wpjobportal::$_db->delete($this->tablename, array($this->primarykey => $wpjobportal_id));
        if (wpjobportal::$_db->last_error == null) {
            WPJOBPORTALincluder::getJSModel('activitylog')->storeActivityLogForActionDelete($wpjobportal_data, $wpjobportal_id);
            return true;
        } else {
            WPJOBPORTALincluder::getJSModel('systemerror')->addSystemError();
            return false;
        }
    }

    function check() {
        return true;
    }

    function load($wpjobportal_id){
        if(!is_numeric($wpjobportal_id)) return false;
        $query = "SELECT * FROM `".$this->tablename."` WHERE `".esc_sql($this->primarykey)."` = " . (int) ($wpjobportal_id);
        $wpjobportal_result = wpjobportal::$_db->get_row($query);
        $wpjobportal_array = get_object_vars($this);
        unset($wpjobportal_array['isnew']);
        unset($wpjobportal_array['primarykey']);
        unset($wpjobportal_array['tablename']);
        unset($wpjobportal_array['columns']);
        foreach ($wpjobportal_array AS $wpjobportal_k => $v) {
            if (isset($wpjobportal_result->$wpjobportal_k)) {
                $this->$wpjobportal_k = $wpjobportal_result->$wpjobportal_k;
            }
            $this->columns[$wpjobportal_k] = $this->$wpjobportal_k;
        }
        return true;
    }

}

?>
