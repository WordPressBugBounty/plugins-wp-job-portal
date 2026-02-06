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
                foreach ($wpjobportal_data AS $wpjobportal_k => $v) {
                    if (isset($this->$wpjobportal_k)) {
                        $this->$wpjobportal_k = $v;
                        $this->columns[$wpjobportal_k] = $v;
                    }
                }
            } else {
                return false; // record cannot be updated b/c of pk not exist
            }
        }
        return true;
    }

    function store() {
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
        $query = "SELECT * FROM `".$this->tablename."` WHERE `".esc_sql($this->primarykey)."` = ".esc_sql($wpjobportal_id);
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
