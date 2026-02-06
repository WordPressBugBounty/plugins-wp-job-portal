<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALJobtypeModel {

    function getJobTypebyId($c_id) {
        if (is_numeric($c_id) == false)
            return false;

        $query = "SELECT * FROM " . wpjobportal::$_db->prefix . "wj_portal_jobtypes WHERE id = " . esc_sql($c_id);
        wpjobportal::$_data[0] = wpjobportaldb::get_row($query);
        return;
    }
    function getAllJobTypes() {
        // Filter
        $title = wpjobportal::$_search['jobtype']['title'];
        $wpjobportal_status = wpjobportal::$_search['jobtype']['status'];
        $pagesize = absint(WPJOBPORTALrequest::getVar('pagesize'));
        $wpjobportal_formsearch = WPJOBPORTALrequest::getVar('WPJOBPORTAL_form_search', 'post');
        if ($wpjobportal_formsearch == 'WPJOBPORTAL_SEARCH') {
            update_option( 'wpjobportal_page_size', $pagesize);
        }

        if(get_option( 'wpjobportal_page_size', '' ) != ''){
            $pagesize = get_option( 'wpjobportal_page_size');
        }

        $wpjobportal_inquery = '';
        $clause = ' WHERE ';
        if ($title != null) {
            $wpjobportal_inquery .= esc_sql($clause) . "title LIKE '%" . esc_sql($title) . "%'";
            $clause = ' AND ';
        }
        if ($wpjobportal_status != null)
            $wpjobportal_inquery .=$clause . " isactive = '" . esc_sql($wpjobportal_status) . "'";
        wpjobportal::$_data['filter']['title'] = $title;
        wpjobportal::$_data['filter']['status'] = $wpjobportal_status;
        wpjobportal::$_data['filter']['pagesize'] = $pagesize;
        //Pagination
        if($pagesize){
           WPJOBPORTALpagination::setLimit($pagesize);
        }
        $query = "SELECT COUNT(id) FROM " . wpjobportal::$_db->prefix . "wj_portal_jobtypes";
        $query .= $wpjobportal_inquery;
        $wpjobportal_total = wpjobportaldb::get_var($query);
        wpjobportal::$_data['total'] = $wpjobportal_total;
        wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($wpjobportal_total);
        //Data
        $query = "SELECT * FROM " . wpjobportal::$_db->prefix . "wj_portal_jobtypes $wpjobportal_inquery ORDER BY ordering ASC";
        $query .=" LIMIT " . WPJOBPORTALpagination::$_offset . "," . WPJOBPORTALpagination::$_limit;
        wpjobportal::$_data[0] = wpjobportaldb::get_results($query);
        return;
    }

    function updateIsDefault($wpjobportal_id) {
        if (!is_numeric($wpjobportal_id))
            return false;
        // DB class limiations
        $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` SET isdefault = 0 WHERE id != " . esc_sql($wpjobportal_id);
        wpjobportaldb::query($query);
    }

    function validateFormData(&$wpjobportal_data) {
        $canupdate = false;
        $alias = WPJOBPORTALincluder::getJSModel('common')->removeSpecialCharacter($wpjobportal_data['title']);
        $alias = wpjobportalphplib::wpJP_strtolower(wpjobportalphplib::wpJP_str_replace(' ', '-', $alias));
        $alias = wpjobportalphplib::wpJP_strtolower(wpjobportalphplib::wpJP_str_replace('_', '-', $alias));
        $wpjobportal_data['alias'] = $alias;

        if ($wpjobportal_data['id'] == '') {
            $wpjobportal_result = $this->isJobTypesExist($wpjobportal_data['title']);
            if ($wpjobportal_result == true) {
                return WPJOBPORTAL_ALREADY_EXIST;
            } else {
                $query = "SELECT max(ordering)+1 AS maxordering FROM " . wpjobportal::$_db->prefix . "wj_portal_jobtypes";
                $wpjobportal_data['ordering'] = wpjobportaldb::get_var($query);
            }

            if ($wpjobportal_data['isactive'] == 0) {
                $wpjobportal_data['isdefault'] = 0;
            } else {
                if ($wpjobportal_data['isdefault'] == 1) {
                    $canupdate = true;
                }
            }
        } else {
            if ($wpjobportal_data['wpjobportal_isdefault'] == 1) {
                $wpjobportal_data['isdefault'] = 1;
                $wpjobportal_data['isactive'] = 1;
            } else {
                if ($wpjobportal_data['isactive'] == 0) {
                    $wpjobportal_data['isdefault'] = 0;
                } else {
                    if ($wpjobportal_data['isdefault'] == 1) {
                        $canupdate = true;
                    }
                }
            }
        }
        return $canupdate;
    }

    function storeJobType($wpjobportal_data) {
        if (empty($wpjobportal_data))
            return false;
        $canupdate = $this->validateFormData($wpjobportal_data);
        if ($canupdate === WPJOBPORTAL_ALREADY_EXIST)
            return WPJOBPORTAL_ALREADY_EXIST;
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('jobtype');
        $wpjobportal_data = wpjobportal::wpjobportal_sanitizeData($wpjobportal_data);
        $wpjobportal_data = WPJOBPORTALincluder::getJSmodel('common')->stripslashesFull($wpjobportal_data);// remove slashes with quotes.
        if (!$wpjobportal_row->bind($wpjobportal_data)) {
            return WPJOBPORTAL_SAVE_ERROR;
        }
        if (!$wpjobportal_row->store()) {
            return WPJOBPORTAL_SAVE_ERROR;
        }
        if ($canupdate) {
            $this->updateIsDefault($wpjobportal_row->id);
        }
        return WPJOBPORTAL_SAVED;
    }

    function deleteJobsType($wpjobportal_ids) {
        if (empty($wpjobportal_ids))
            return false;
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('jobtype');
        $wpjobportal_notdeleted = 0;
        foreach ($wpjobportal_ids as $wpjobportal_id) {
            if ($this->jobTypeCanDelete($wpjobportal_id) == true) {
                if (!$wpjobportal_row->delete($wpjobportal_id)) {
                    $wpjobportal_notdeleted += 1;
                }
            } else {
                $wpjobportal_notdeleted += 1;
            }
        }
        if ($wpjobportal_notdeleted == 0) {
            WPJOBPORTALMessages::$wpjobportal_counter = false;
            return WPJOBPORTAL_DELETED;
        } else {
            WPJOBPORTALMessages::$wpjobportal_counter = $wpjobportal_notdeleted;
            return WPJOBPORTAL_DELETE_ERROR;
        }
    }

    function publishUnpublish($wpjobportal_ids, $wpjobportal_status) {
        if (empty($wpjobportal_ids))
            return false;
        if (!is_numeric($wpjobportal_status))
            return false;

        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('jobtype');
        $wpjobportal_total = 0;
        if ($wpjobportal_status == 1) {
            foreach ($wpjobportal_ids as $wpjobportal_id) {
                if (!$wpjobportal_row->update(array('id' => $wpjobportal_id, 'isactive' => $wpjobportal_status))) {
                    $wpjobportal_total += 1;
                }
            }
        } else {
            foreach ($wpjobportal_ids as $wpjobportal_id) {
                if ($this->jobTypeCanUnpublish($wpjobportal_id)) {
                    if (!$wpjobportal_row->update(array('id' => $wpjobportal_id, 'isactive' => $wpjobportal_status))) {
                        $wpjobportal_total += 1;
                    }
                } else {
                    $wpjobportal_total += 1;
                }
            }
        }
        if ($wpjobportal_total == 0) {
            WPJOBPORTALMessages::$wpjobportal_counter = false;
            if ($wpjobportal_status == 1)
                return WPJOBPORTAL_PUBLISHED;
            else
                return WPJOBPORTAL_UN_PUBLISHED;
        }else {
            WPJOBPORTALMessages::$wpjobportal_counter = $wpjobportal_total;
            if ($wpjobportal_status == 1)
                return WPJOBPORTAL_PUBLISH_ERROR;
            else
                return WPJOBPORTAL_UN_PUBLISH_ERROR;
        }
    }

    function jobTypeCanUnpublish($typeid) {
        if (!is_numeric($typeid))
            return false;
        $query = "SELECT
                    ( SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` WHERE id = " . esc_sql($typeid) . " AND isdefault = 1)
                    AS total ";
        $wpjobportal_total = wpjobportaldb::get_var($query);
        if ($wpjobportal_total > 0)
            return false;
        else
            return true;
    }

    function jobTypeCanDelete($typeid) {
        if (!is_numeric($typeid))
            return false;
        $query = "SELECT
                    ( SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` WHERE jobtype = " . esc_sql($typeid) . ")
                    + ( SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` WHERE jobtype = " . esc_sql($typeid) . ")
                    + ( SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` WHERE id = " . esc_sql($typeid) . " AND isdefault = 1)
                    AS total ";
        $wpjobportal_total = wpjobportaldb::get_var($query);

        if ($wpjobportal_total > 0)
            return false;
        else
            return true;
    }

    function getJobTypeForCombo() {

        $query = "SELECT id, title AS text FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` WHERE isactive = 1";
        $query.= " ORDER BY ordering ASC ";

        $wpjobportal_rows = wpjobportaldb::get_results($query);
        if (wpjobportal::$_db->last_error != null) {

            return false;
        }
        return $wpjobportal_rows;
    }

    function getJobTypeForRadio() {
        $query = "SELECT id, title AS text FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` WHERE isactive = 1";
        $query.= " ORDER BY ordering ASC ";

        $wpjobportal_rows = wpjobportaldb::get_results($query);

        $wpjobportal_comboOptions = array();
        foreach ($wpjobportal_rows as $wpjobportal_value) {
            $wpjobportal_comboOptions[$wpjobportal_value->id] = $wpjobportal_value->text;
        }
        if (wpjobportal::$_db->last_error != null) {

            return false;
        }
        return $wpjobportal_comboOptions;
    }

    function isJobTypesExist($title) {
        if (!$title)
            return false;
        $query = "SELECT COUNT(id) FROM " . wpjobportal::$_db->prefix . "wj_portal_jobtypes WHERE title = '" . esc_sql($title) . "'";
        $wpjobportal_result = wpjobportaldb::get_var($query);
        if ($wpjobportal_result > 0)
            return true;
        else
            return false;
    }

    function getDefaultJobTypeId() {
        $query = "SELECT id FROM " . wpjobportal::$_db->prefix . "wj_portal_jobtypes WHERE isdefault = 1";
        $wpjobportal_id = wpjobportaldb::get_var($query);

        return $wpjobportal_id;
    }

    function getTitleByid($wpjobportal_id) {
        if(!is_numeric($wpjobportal_id)) return false;
        $query = "SELECT title FROM " . wpjobportal::$_db->prefix . "wj_portal_jobtypes WHERE id = " . esc_sql($wpjobportal_id);
        $title = wpjobportaldb::get_var($query);
        return $title;
    }

    function getIDByTitle($title) {
        if($title == '') return false;
        $query = "SELECT id FROM " . wpjobportal::$_db->prefix . "wj_portal_jobtypes WHERE title = '" . esc_sql($title) ."'";
        $title = wpjobportaldb::get_var($query);
        return $title;
    }

    function getMessagekey(){
        $wpjobportal_key = 'jobtype';if(wpjobportal::$_common->wpjp_isadmin()){$wpjobportal_key = 'admin_'.$wpjobportal_key;}return $wpjobportal_key;
    }

    // WE will Save the Ordering system in this Function
    function storeOrderingFromPage($wpjobportal_data) {//
        if (empty($wpjobportal_data)) {
            return false;
        }
        $sorted_array = array();
        wpjobportalphplib::wpJP_parse_str($wpjobportal_data['fields_ordering_new'],$sorted_array);
        $sorted_array = reset($sorted_array);
        if(!empty($sorted_array)){
            $wpjobportal_row = WPJOBPORTALincluder::getJSTable('jobtype');
            $ordering_coloumn = 'ordering';
        }
        $page_multiplier = 0;
        $wpjobportal_pagenum = WPJOBPORTALrequest::getVar('pagenum');
        if (isset($wpjobportal_pagenum)) {
            $page_multiplier = $wpjobportal_pagenum - 1;
        }
        $pagesize = get_option( 'wpjobportal_page_size');
        if ($pagesize == 0) {
            $pagesize = wpjobportal::$_configuration['pagination_default_page_size'];
        }
        $page_multiplier = $pagesize * $page_multiplier;
        for ($wpjobportal_i=0; $wpjobportal_i < count($sorted_array) ; $wpjobportal_i++) {
            $wpjobportal_row->update(array('id' => $sorted_array[$wpjobportal_i], $ordering_coloumn => $page_multiplier + $wpjobportal_i));
        }
        WPJOBPORTALMessages::setLayoutMessage(esc_html(__('Ordering updated', 'wp-job-portal')), 'updated', $this->getMessagekey());
        return ;
    }
    // End Function
    //search cookies data
    function getSearchFormData(){
        $wpjobportal_jsjp_search_array = array();
        $wpjobportal_jsjp_search_array['title'] = WPJOBPORTALrequest::getVar("title");
        $wpjobportal_jsjp_search_array['status'] = WPJOBPORTALrequest::getVar("status");
        $wpjobportal_jsjp_search_array['search_from_jobtype'] = 1;
        return $wpjobportal_jsjp_search_array;
    }

    function getSavedCookiesDataForSearch(){
        $wpjobportal_jsjp_search_array = array();
        $wpjp_search_cookie_data = '';
        if(isset($_COOKIE['jsjp_jobportal_search_data'])){
            $wpjp_search_cookie_data = wpjobportal::wpjobportal_sanitizeData($_COOKIE['jsjp_jobportal_search_data']);
            $wpjp_search_cookie_data = wpjobportalphplib::wpJP_safe_decoding($wpjp_search_cookie_data);
            $wpjp_search_cookie_data = json_decode( $wpjp_search_cookie_data , true );
        }
        if($wpjp_search_cookie_data != '' && isset($wpjp_search_cookie_data['search_from_jobtype']) && $wpjp_search_cookie_data['search_from_jobtype'] == 1){
            $wpjobportal_jsjp_search_array['title'] = $wpjp_search_cookie_data['title'];
            $wpjobportal_jsjp_search_array['status'] = $wpjp_search_cookie_data['status'];
        }
        return $wpjobportal_jsjp_search_array;
    }

    function setSearchVariableForSearch($wpjobportal_jsjp_search_array){
        wpjobportal::$_search['jobtype']['title'] = isset($wpjobportal_jsjp_search_array['title']) ? $wpjobportal_jsjp_search_array['title'] : '';
        wpjobportal::$_search['jobtype']['status'] = isset($wpjobportal_jsjp_search_array['status']) ? $wpjobportal_jsjp_search_array['status'] : '';
    }



}

?>
