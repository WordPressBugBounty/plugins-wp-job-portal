<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALcountryModel {

    function storeCountry($wpjobportal_data) {
        if (empty($wpjobportal_data))
            return false;

        if ($wpjobportal_data['id'] == '') {
            $wpjobportal_result = $this->isCountryExist($wpjobportal_data['name']);
            if ($wpjobportal_result == true) {
                return WPJOBPORTAL_ALREADY_EXIST;
            }
        }

        $wpjobportal_data['shortCountry'] = wpjobportalphplib::wpJP_str_replace(' ', '-', $wpjobportal_data['name']);
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('country');
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

    function getCountrybyId($wpjobportal_id) {
        if (!is_numeric($wpjobportal_id))
            return false;
        $query = "SELECT * FROM `" . wpjobportal::$_db->prefix . "wj_portal_countries` WHERE id = " . esc_sql($wpjobportal_id);
        wpjobportal::$_data[0] = wpjobportaldb::get_row($query);

        return;
    }

    function getAllCountries() {

        $wpjobportal_countryname = wpjobportal::$_search['country']['countryname'];
        $Status = wpjobportal::$_search['country']['status'];
        $wpjobportal_states = wpjobportal::$_search['country']['states'];
        $city = wpjobportal::$_search['country']['city'];

        $wpjobportal_inquery = '';
        $clause = ' WHERE ';
        if ($wpjobportal_countryname) {
            $wpjobportal_inquery .= esc_sql($clause) . "  country.name LIKE '%" . esc_sql($wpjobportal_countryname) . "%' ";
            $clause = " AND ";
        }
        if (is_numeric($Status)) {
            $wpjobportal_inquery .= esc_sql($clause) . " country.enabled = " . esc_sql($Status);
            $clause = " AND ";
        }

        if ($wpjobportal_states == 1) {
            $wpjobportal_inquery .= esc_sql($clause) . " (SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_states` AS state WHERE state.countryid = country.id) > 0 ";
            $clause = " AND ";
        }

        if ($city == 1) {
            $wpjobportal_inquery .= esc_sql($clause) . " (SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city WHERE city.countryid = country.id) > 0 ";
            $clause = " AND ";
        }

        wpjobportal::$_data['filter']['countryname'] = $wpjobportal_countryname;
        wpjobportal::$_data['filter']['status'] = $Status;
        wpjobportal::$_data['filter']['states'] = $wpjobportal_states;
        wpjobportal::$_data['filter']['city'] = $city;

        // Pagination
        $query = "SELECT COUNT(country.id)
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_countries` AS country";
        $query .= $wpjobportal_inquery;

        $wpjobportal_total = wpjobportaldb::get_var($query);
        wpjobportal::$_data['total'] = $wpjobportal_total;
        wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($wpjobportal_total);

        // Data
        $query = "SELECT country.* FROM `" . wpjobportal::$_db->prefix . "wj_portal_countries` AS country";
        $query .= $wpjobportal_inquery;

        $query .= " ORDER BY country.name ASC LIMIT " . WPJOBPORTALpagination::$_offset . ", " . WPJOBPORTALpagination::$_limit;
        wpjobportal::$_data[0] = wpjobportaldb::get_results($query);

        return;
    }

    function deleteCountries($wpjobportal_ids) {
        if (empty($wpjobportal_ids))
            return false;
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('country');
        $wpjobportal_notdeleted = 0;
        foreach ($wpjobportal_ids as $wpjobportal_id) {
            if ($this->countryCanDelete($wpjobportal_id) == true) {
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

        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('country');
        $wpjobportal_total = 0;
        if ($wpjobportal_status == 1) {
            foreach ($wpjobportal_ids as $wpjobportal_id) {
                if (!$wpjobportal_row->update(array('id' => $wpjobportal_id, 'enabled' => $wpjobportal_status))) {
                    $wpjobportal_total += 1;
                }
            }
        } else {
            foreach ($wpjobportal_ids as $wpjobportal_id) {
                if ($this->countryCanUnpublish($wpjobportal_id)) {
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

    function countryCanUnpublish($wpjobportal_countryid) {
        return true;
    }

    function countryCanDelete($wpjobportal_countryid) {
        if (!is_numeric($wpjobportal_countryid))
            return false;
        $query = "SELECT
                    ( SELECT COUNT(jobcity.id)
                        FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobcities` AS jobcity
                        JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON city.id = jobcity.cityid
                        WHERE city.countryid = " . esc_sql($wpjobportal_countryid) . ")
                    + ( SELECT COUNT(companycity.id)
                            FROM `" . wpjobportal::$_db->prefix . "wj_portal_companycities` AS companycity
                            JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON city.id = companycity.cityid
                            WHERE city.countryid = " . esc_sql($wpjobportal_countryid) . ")
                    + ( SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` WHERE nationality = " . esc_sql($wpjobportal_countryid) . ")
                    + ( SELECT COUNT(resumecity.id)
                            FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumeaddresses` AS resumecity
                            JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON city.id = resumecity.address_city
                            WHERE city.countryid = " . esc_sql($wpjobportal_countryid) . ")
                    + ( SELECT COUNT(employeecity.id)
                            FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumeemployers` AS employeecity
                            JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON city.id = employeecity.employer_city
                            WHERE city.countryid = " . esc_sql($wpjobportal_countryid) . ")
                    + ( SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_states` WHERE countryid = " . esc_sql($wpjobportal_countryid) . ")
                    + ( SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_cities` WHERE countryid = " . esc_sql($wpjobportal_countryid) . ")
            AS total ";
        $wpjobportal_total = wpjobportaldb::get_var($query);
        if ($wpjobportal_total > 0)
            return false;
        else
            return true;
    }

    function isCountryExist($wpjobportal_country) {
        if (!$wpjobportal_country)
            return;
        $query = "SELECT COUNT(id) FROM " . wpjobportal::$_db->prefix . "wj_portal_countries WHERE name = '" . esc_sql($wpjobportal_country) . "'";
        $wpjobportal_total = wpjobportaldb::get_var($query);
        if ($wpjobportal_total > 0)
            return true;
        else
            return false;
    }

    function getCountriesForCombo() {
        $query = "SELECT id , name AS text FROM `" . wpjobportal::$_db->prefix . "wj_portal_countries` WHERE enabled = 1 ORDER BY name ASC ";
        $wpjobportal_rows = wpjobportaldb::get_results($query);
        return $wpjobportal_rows;
    }

    // fucntion to prepare data for country based city import combo box
    function getCountriesForComboForCityImport() {
        $query = "SELECT namecode, name,localname, internationalname FROM `" . wpjobportal::$_db->prefix . "wj_portal_countries` WHERE enabled = 1 ORDER BY internationalname ASC ";
        $wpjobportal_data = wpjobportaldb::get_results($query);
        $wpjobportal_countries = array();
        foreach ($wpjobportal_data as $wpjobportal_country_data) {
            $wpjobportal_country = new stdClass();
            $wpjobportal_country->id = $wpjobportal_country_data->namecode;
            $wpjobportal_country->text = $wpjobportal_country_data->internationalname;
            if($wpjobportal_country_data->localname != '' && $wpjobportal_country->text != ''){
                $wpjobportal_country->text .= '&nbsp;('.$wpjobportal_country_data->localname.')';
            }else{
                $wpjobportal_country->text = $wpjobportal_country_data->name;
            }
            $wpjobportal_countries[] = $wpjobportal_country;
        }
        return $wpjobportal_countries;
    }

    function getCountryIdByName($wpjobportal_name) { // new function coded
        if (!$wpjobportal_name)
            return;
        $query = "SELECT id FROM `" . wpjobportal::$_db->prefix . "wj_portal_countries` WHERE REPLACE(LOWER(name), ' ', '') = REPLACE(LOWER('" . esc_sql($wpjobportal_name) . "'), ' ', '') AND enabled = 1";
        $wpjobportal_id = wpjobportaldb::get_var($query);
        return $wpjobportal_id;
    }

    function getCountryName($wpjobportal_id){
        if(!is_numeric($wpjobportal_id)){
            return false;
        }
        $query = "SELECT name FROM `" . wpjobportal::$_db->prefix . "wj_portal_countries` WHERE id = ".esc_sql($wpjobportal_id);
        $wpjobportal_name = wpjobportaldb::get_var($query);
        return $wpjobportal_name;
    }

    //search cookies data
    function getCountrySearchFormData(){
        $wpjobportal_jsjp_search_array = array();
        $wpjobportal_jsjp_search_array['countryname'] = WPJOBPORTALrequest::getVar("countryname");
        $wpjobportal_jsjp_search_array['status'] = WPJOBPORTALrequest::getVar("status");
        $wpjobportal_jsjp_search_array['states'] = WPJOBPORTALrequest::getVar("states");
        $wpjobportal_jsjp_search_array['city'] = WPJOBPORTALrequest::getVar("city");
        $wpjobportal_jsjp_search_array['search_from_country'] = 1;
        return $wpjobportal_jsjp_search_array;
    }

    function getCountrySavedCookiesData(){
        $wpjobportal_jsjp_search_array = array();
        $wpjp_search_cookie_data = '';
        if(isset($_COOKIE['jsjp_jobportal_search_data'])){
            $wpjp_search_cookie_data = wpjobportal::wpjobportal_sanitizeData($_COOKIE['jsjp_jobportal_search_data']);
            $wpjp_search_cookie_data = wpjobportalphplib::wpJP_safe_decoding($wpjp_search_cookie_data);
            $wpjp_search_cookie_data = json_decode( $wpjp_search_cookie_data , true );
        }
        if($wpjp_search_cookie_data != '' && isset($wpjp_search_cookie_data['search_from_country']) && $wpjp_search_cookie_data['search_from_country'] == 1){
            $wpjobportal_jsjp_search_array['countryname'] = $wpjp_search_cookie_data['countryname'];
            $wpjobportal_jsjp_search_array['status'] = $wpjp_search_cookie_data['status'];
            $wpjobportal_jsjp_search_array['states'] = $wpjp_search_cookie_data['states'];
            $wpjobportal_jsjp_search_array['city'] = $wpjp_search_cookie_data['city'];
        }
        return $wpjobportal_jsjp_search_array;
    }

    function setCountrySearchVariable($wpjobportal_jsjp_search_array){
        wpjobportal::$_search['country']['countryname'] = isset($wpjobportal_jsjp_search_array['countryname']) ? $wpjobportal_jsjp_search_array['countryname'] : '';
        wpjobportal::$_search['country']['status'] = isset($wpjobportal_jsjp_search_array['status']) ? $wpjobportal_jsjp_search_array['status'] : '';
        wpjobportal::$_search['country']['states'] = isset($wpjobportal_jsjp_search_array['states']) ? $wpjobportal_jsjp_search_array['states'] : '';
        wpjobportal::$_search['country']['city'] = isset($wpjobportal_jsjp_search_array['city']) ? $wpjobportal_jsjp_search_array['city'] : '';
    }

    function getMessagekey(){
        $wpjobportal_key = 'country';if(wpjobportal::$_common->wpjp_isadmin()){$wpjobportal_key = 'admin_'.$wpjobportal_key;}return $wpjobportal_key;
    }


}

?>
