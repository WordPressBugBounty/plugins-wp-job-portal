<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALStateModel {

    function getStatebyId($wpjobportal_id) {
        if (is_numeric($wpjobportal_id) == false)
            return false;
        $query = "SELECT * FROM " . wpjobportal::$_db->prefix . "wj_portal_states WHERE id = " . esc_sql($wpjobportal_id);
        wpjobportal::$_data[0] = wpjobportaldb::get_row($query);
        return;
    }

    function getAllCountryStates($wpjobportal_countryid) {
        if (!is_numeric($wpjobportal_countryid))
            return false;
        //Filters
        $wpjobportal_searchname = wpjobportal::$_search['state']['searchname'];
        $city = wpjobportal::$_search['state']['city'];
        $wpjobportal_status = wpjobportal::$_search['state']['status'];

        $wpjobportal_inquery = '';
        if ($wpjobportal_searchname) {
            $wpjobportal_inquery .= " AND name LIKE '%" . esc_sql($wpjobportal_searchname) . "%'";
        }
        if (is_numeric($wpjobportal_status)) {
            $wpjobportal_inquery .= " AND state.enabled = " . esc_sql($wpjobportal_status);
        }

        if ($city == 1) {
            $wpjobportal_inquery .=" AND (SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city WHERE city.stateid = state.id) > 0 ";
        }

        wpjobportal::$_data['filter']['searchname'] = $wpjobportal_searchname;
        wpjobportal::$_data['filter']['status'] = $wpjobportal_status;
        wpjobportal::$_data['filter']['city'] = $city;


        //Pagination
        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_states` AS state WHERE countryid = " . esc_sql($wpjobportal_countryid);
        $query.=$wpjobportal_inquery;
        $wpjobportal_total = wpjobportaldb::get_var($query);
        wpjobportal::$_data['total'] = $wpjobportal_total;
        wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($wpjobportal_total);

        //Data
        $query = "SELECT * FROM `" . wpjobportal::$_db->prefix . "wj_portal_states` AS state WHERE countryid = " . esc_sql($wpjobportal_countryid);
        $query.=$wpjobportal_inquery;
        $query.=" ORDER BY name ASC LIMIT " . WPJOBPORTALpagination::$_offset . "," . WPJOBPORTALpagination::$_limit;
        wpjobportal::$_data[0] = wpjobportaldb::get_results($query);

        return;
    }

    function storeState($wpjobportal_data, $wpjobportal_countryid) {
        if (empty($wpjobportal_data))
            return false;

        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('state');
        $wpjobportal_data['countryid'] = $wpjobportal_countryid;

        if (!$wpjobportal_data['id']) { // only for new
            $wpjobportal_existvalue = $this->isStateExist($wpjobportal_data['name'], $wpjobportal_data['countryid']);
            if ($wpjobportal_existvalue == true)
                return WPJOBPORTAL_ALREADY_EXIST;
        }

        $wpjobportal_data['shortRegion'] = $wpjobportal_data['name'];
        $wpjobportal_data = wpjobportal::wpjobportal_sanitizeData($wpjobportal_data);
        $wpjobportal_data = WPJOBPORTALincluder::getJSmodel('common')->stripslashesFull($wpjobportal_data);// remove slashes with quotes.
        if (!$wpjobportal_row->bind($wpjobportal_data)) {
            return WPJOBPORTAL_SAVE_ERROR;
        }
        if (!$wpjobportal_row->store()) {
            return WPJOBPORTAL_SAVE_ERROR;
        }
        return WPJOBPORTAL_SAVED;
    }

    function deleteStates($wpjobportal_ids) {
        if (empty($wpjobportal_ids))
            return false;
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('state');
        $wpjobportal_notdeleted = 0;
        foreach ($wpjobportal_ids as $wpjobportal_id) {
            if ($this->stateCanDelete($wpjobportal_id) == true) {
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

    function stateCanDelete($wpjobportal_stateid) {
        if (!is_numeric($wpjobportal_stateid))
            return false;
        $query = "SELECT
                    ( SELECT COUNT(mcity.id)
                           FROM `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city
                           JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobcities` AS mcity ON mcity.cityid=city.id
                           WHERE city.stateid = " . esc_sql($wpjobportal_stateid) . "
                   )
                   +
                   ( SELECT COUNT(cmcity.id)
                           FROM `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city
                           JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companycities` AS cmcity ON cmcity.cityid=city.id
                           WHERE city.stateid = " . esc_sql($wpjobportal_stateid) . "
                   )
                   +
                   ( SELECT COUNT(resume.id)
                           FROM `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city
                           JOIN `" . wpjobportal::$_db->prefix . "wj_portal_resumeaddresses` AS resume ON resume.address_city=city.id
                           WHERE city.stateid = " . esc_sql($wpjobportal_stateid) . "
                   )
                   +
                   ( SELECT COUNT(resume.id)
                           FROM `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city
                           JOIN `" . wpjobportal::$_db->prefix . "wj_portal_resumeemployers` AS resume ON resume.employer_city=city.id
                           WHERE city.stateid = " . esc_sql($wpjobportal_stateid) . "
                   )
                    AS total ";
        $wpjobportal_total = wpjobportaldb::get_var($query);
        if ($wpjobportal_total > 0)
            return false;
        else
            return true;
    }

    function stateCanUnpublish($wpjobportal_stateid) {
        return true;
    }

    function isStateExist($wpjobportal_state, $wpjobportal_countryid) {
        if (!is_numeric($wpjobportal_countryid))
            return false;
        $query = "SELECT COUNT(id) FROM " . wpjobportal::$_db->prefix . "wj_portal_states WHERE name = '".esc_sql($wpjobportal_state)."' AND countryid = " . esc_sql($wpjobportal_countryid);
        $wpjobportal_result = wpjobportaldb::get_var($query);
        if ($wpjobportal_result > 0)
            return true;
        else
            return false;
    }

    function getStatesForCombo($wpjobportal_country) {
        if (is_null($wpjobportal_country) OR empty($wpjobportal_country) OR !is_numeric($wpjobportal_country))
            $wpjobportal_country = 0;
        $query = "SELECT id, name AS text FROM `" . wpjobportal::$_db->prefix . "wj_portal_states` WHERE enabled = '1' AND countryid = " . esc_sql($wpjobportal_country) . " ORDER BY name ASC ";
        $wpjobportal_rows = wpjobportaldb::get_results($query);
        if (wpjobportal::$_db->last_error != null) {
            return false;
        }
        return $wpjobportal_rows;
    }

    function publishUnpublish($wpjobportal_ids, $wpjobportal_status) {
        if (empty($wpjobportal_ids))
            return false;
        if (!is_numeric($wpjobportal_status))
            return false;

        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('state');
        $wpjobportal_total = 0;
        if ($wpjobportal_status == 1) {
            foreach ($wpjobportal_ids as $wpjobportal_id) {
                if (!$wpjobportal_row->update(array('id' => $wpjobportal_id, 'enabled' => $wpjobportal_status))) {
                    $wpjobportal_total += 1;
                }
            }
        } else {
            foreach ($wpjobportal_ids as $wpjobportal_id) {
                if ($this->stateCanUnpublish($wpjobportal_id)) {
                    if (!$wpjobportal_row->update(array('id' => $wpjobportal_id, 'enabled' => $wpjobportal_status))) {
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

    function getStateIdByName($wpjobportal_name) { // new function coded
        $query = "SELECT id FROM `" . wpjobportal::$_db->prefix . "wj_portal_states` WHERE REPLACE(LOWER(name), ' ', '') = REPLACE(LOWER('" . esc_sql($wpjobportal_name) . "'), ' ', '') AND enabled = 1";
        $wpjobportal_id = wpjobportaldb::get_var($query);
        return $wpjobportal_id;
    }

    function storeTokenInputState($wpjobportal_data) { // new function coded
        if (empty($wpjobportal_data))
            return false;
        if (!isset($wpjobportal_data['countryid']))
            return false;
        $wpjobportal_data = wpjobportal::wpjobportal_sanitizeData($wpjobportal_data);
        $wpjobportal_data = WPJOBPORTALincluder::getJSmodel('common')->stripslashesFull($wpjobportal_data);// remove slashes with quotes.
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('state');
        if (!$wpjobportal_row->bind($wpjobportal_data)) {
            return false;
        }
        if (!$wpjobportal_row->store()) {
            return false;
        }
        return true;
    }

    // setcookies for search form data
    //search cookies data
    function getSearchFormData(){
        $wpjobportal_jsjp_search_array = array();
        $wpjobportal_jsjp_search_array['searchname'] = WPJOBPORTALrequest::getVar("searchname");
        $wpjobportal_jsjp_search_array['status'] = WPJOBPORTALrequest::getVar("status");
        $wpjobportal_jsjp_search_array['city'] = WPJOBPORTALrequest::getVar("city");
        $wpjobportal_jsjp_search_array['search_from_state'] = 1;
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
        if($wpjp_search_cookie_data != '' && isset($wpjp_search_cookie_data['search_from_state']) && $wpjp_search_cookie_data['search_from_state'] == 1){
            $wpjobportal_jsjp_search_array['searchname'] = $wpjp_search_cookie_data['searchname'];
            $wpjobportal_jsjp_search_array['status'] = $wpjp_search_cookie_data['status'];
            $wpjobportal_jsjp_search_array['city'] = $wpjp_search_cookie_data['city'];
        }
        return $wpjobportal_jsjp_search_array;
    }

    function setSearchVariableForSearch($wpjobportal_jsjp_search_array){
        wpjobportal::$_search['state']['searchname'] = isset($wpjobportal_jsjp_search_array['searchname']) ? $wpjobportal_jsjp_search_array['searchname'] : '';
        wpjobportal::$_search['state']['status'] = isset($wpjobportal_jsjp_search_array['status']) ? $wpjobportal_jsjp_search_array['status'] : '';
        wpjobportal::$_search['state']['city'] = isset($wpjobportal_jsjp_search_array['city']) ? $wpjobportal_jsjp_search_array['city'] : '';
    }

    function getMessagekey(){
        $wpjobportal_key = 'state';if(wpjobportal::$_common->wpjp_isadmin()){$wpjobportal_key = 'admin_'.$wpjobportal_key;}return $wpjobportal_key;
    }


}

?>
