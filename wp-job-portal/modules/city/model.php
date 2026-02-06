<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALCityModel {

    function getCitybyId($wpjobportal_id) {
        if ($wpjobportal_id) {
            if (!is_numeric($wpjobportal_id))
                return false;
            $query = "SELECT * FROM " . wpjobportal::$_db->prefix . "wj_portal_cities WHERE id = " . esc_sql($wpjobportal_id);
            wpjobportal::$_data[0] = wpjobportaldb::get_row($query);
        }
        return;
    }

    function getCityNamebyId($wpjobportal_id) {
        if (is_numeric($wpjobportal_id) == false)
            return false;
        $query = "SELECT name FROM `". wpjobportal::$_db->prefix ."wj_portal_cities` WHERE id = " . esc_sql($wpjobportal_id);
        return wpjobportaldb::get_var($query);
    }

    function getCoordinatesOfCities($wpjobportal_pageid){
        /*
        $query = "SELECT city.id AS cityid, city.latitude,city.longitude
                    FROM `". wpjobportal::$_db->prefix ."wj_portal_jobs` AS job
                    JOIN `". wpjobportal::$_db->prefix ."wj_portal_cities` AS city ON city.id = job.city
                    JOIN `". wpjobportal::$_db->prefix ."wj_portal_countries` AS country ON country.id = city.countryid
                    WHERE country.enabled = 1 AND job.status = 1 AND job.stoppublishing >= CURDATE() GROUP BY cityid " ;
                    */
        $query="SELECT city.id AS cityid, city.latitude,city.longitude ,count(jobc.cityid) tjob
                FROM `". wpjobportal::$_db->prefix ."wj_portal_jobcities` AS jobc
                JOIN `". wpjobportal::$_db->prefix ."wj_portal_jobs` AS job ON jobc.jobid = job.id
                JOIN `". wpjobportal::$_db->prefix ."wj_portal_cities` AS city ON city.id = jobc.cityid
                JOIN `". wpjobportal::$_db->prefix ."wj_portal_countries` AS country ON country.id = city.countryid
                WHERE country.enabled = 1 AND job.status = 1
                AND DATE(job.stoppublishing) >= CURDATE() AND DATE(job.startpublishing) <= CURDATE() GROUP BY jobc.cityid HAVING tjob > 0";
        $wpjobportal_data = wpjobportaldb::get_results($query);
        $final_array= array();
        $wpjobportal_i = 0;
        foreach($wpjobportal_data AS $l){
            if(is_numeric($l->latitude) && is_numeric($l->longitude) ){
                $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'jobs', 'city'=>$l->cityid , 'wpjobportalpageid' => $wpjobportal_pageid ));
                $wpjobportal_img =     JOB_PORTAL_THEME_IMAGE.'/location-icons/loction-mark-icon-'.$wpjobportal_i.'.png';
                $final_array[] = array('lat' => $l->latitude, 'lng' => $l->longitude ,'link' => $wpjobportal_link, 'img' => $wpjobportal_img);
                $wpjobportal_i ++;
                if($wpjobportal_i > 10){
                    $wpjobportal_i = 0;
                }
            }
        }
        $jfinal_array = wp_json_encode($final_array);
        wpjobportal::$_data['coordinates'] = $jfinal_array;
        return;
    }

    function getAllStatesCities($wpjobportal_countryid, $wpjobportal_stateid) {
        if (!is_numeric($wpjobportal_countryid))
            return false;

        //Filter
        $wpjobportal_searchname = wpjobportal::$_search['city']['searchname'];
        $wpjobportal_status = wpjobportal::$_search['city']['status'];

        $wpjobportal_inquery = '';
        $clause = ' WHERE ';
        if ($wpjobportal_searchname != null) {
            $wpjobportal_inquery .= esc_sql($clause) . " name LIKE '%".esc_sql($wpjobportal_searchname)."%'";
            $clause = ' AND ';
        }
        if (is_numeric($wpjobportal_status)) {
            $wpjobportal_inquery .= esc_sql($clause) . " enabled = " . esc_sql($wpjobportal_status);
            $clause = ' AND ';
        }

        if ($wpjobportal_stateid) {
            if(is_numeric($wpjobportal_stateid)){
                $wpjobportal_inquery .=esc_sql($clause) . " stateid = " . esc_sql($wpjobportal_stateid);
                $clause = ' AND ';
            }
        }
        if (is_numeric($wpjobportal_countryid)) {
            $wpjobportal_inquery .= esc_sql($clause) . "countryid = " . esc_sql($wpjobportal_countryid);
            $clause = ' AND ';
        }

        wpjobportal::$_data['filter']['searchname'] = $wpjobportal_searchname;
        wpjobportal::$_data['filter']['status'] = $wpjobportal_status;


        //Pagination
        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_cities`";
        $query .= $wpjobportal_inquery;
        $wpjobportal_total = wpjobportaldb::get_var($query);
        wpjobportal::$_data['total'] = $wpjobportal_total;
        wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($wpjobportal_total);

        //Data
        $query = "SELECT * FROM `" . wpjobportal::$_db->prefix . "wj_portal_cities`";
        $query .=$wpjobportal_inquery;
        $query .=" ORDER BY name ASC LIMIT " . WPJOBPORTALpagination::$_offset . " , " . WPJOBPORTALpagination::$_limit;
        wpjobportal::$_data[0] = wpjobportaldb::get_results($query);

        return;
    }

    function storeCity($wpjobportal_data, $wpjobportal_countryid, $wpjobportal_stateid) {
        if (empty($wpjobportal_data))
            return false;

        if ($wpjobportal_data['id'] == '') {
            $wpjobportal_result = $this->isCityExist($wpjobportal_countryid, $wpjobportal_stateid, $wpjobportal_data['name']);
            if ($wpjobportal_result == true) {
                return WPJOBPORTAL_ALREADY_EXIST;
            }
        }

        $wpjobportal_data['countryid'] = $wpjobportal_countryid;
        $wpjobportal_data['stateid'] = $wpjobportal_stateid;
        $wpjobportal_data['cityName'] = $wpjobportal_data['name'];

        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('city');
        $wpjobportal_data = WPJOBPORTALincluder::getJSmodel('common')->stripslashesFull($wpjobportal_data);// remove slashes with quotes.
        $wpjobportal_data = wpjobportal::wpjobportal_sanitizeData($wpjobportal_data);
        if (!$wpjobportal_row->bind($wpjobportal_data)) {
            return WPJOBPORTAL_SAVE_ERROR;
        }
        if (!$wpjobportal_row->store()) {
            return WPJOBPORTAL_SAVE_ERROR;
        }

        return WPJOBPORTAL_SAVED;
    }

    function deleteCities($wpjobportal_ids) {
        if (empty($wpjobportal_ids))
            return false;
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('city');
        $wpjobportal_notdeleted = 0;
        foreach ($wpjobportal_ids as $wpjobportal_id) {
            if ($this->cityCanDelete($wpjobportal_id) == true) {
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

        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('city');
        $wpjobportal_total = 0;
        if ($wpjobportal_status == 1) {
            foreach ($wpjobportal_ids as $wpjobportal_id) {
                if (!$wpjobportal_row->update(array('id' => $wpjobportal_id, 'enabled' => $wpjobportal_status))) {
                    $wpjobportal_total += 1;
                }
            }
        } else {
            foreach ($wpjobportal_ids as $wpjobportal_id) {
                if ($this->cityCanUnpublish($wpjobportal_id)) {
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

    function cityCanUnpublish($cityid) {
        return true;
    }

    function cityCanDelete($cityid) {
        if (!is_numeric($cityid))
            return false;
        $query = "SELECT
                    ( SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobcities` WHERE cityid = " . esc_sql($cityid) . ")
                    + ( SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_companycities` WHERE cityid = " . esc_sql($cityid) . ")
                    + ( SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumeaddresses` WHERE address_city = " . esc_sql($cityid) . ")
                    + ( SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumeemployers` WHERE employer_city = " . esc_sql($cityid) . ")
                        AS total ";

        $wpjobportal_total = wpjobportaldb::get_var($query);

        if ($wpjobportal_total > 0)
            return false;
        else
            return true;
    }

    function isCityExist($wpjobportal_countryid, $wpjobportal_stateid, $title) {
        if (!is_numeric($wpjobportal_countryid))
            return false;
        if (!is_numeric($wpjobportal_stateid))
            return false;

        $query = "SELECT COUNT(id) FROM " . wpjobportal::$_db->prefix . "wj_portal_cities WHERE countryid=" . esc_sql($wpjobportal_countryid) . "
		AND stateid=" . esc_sql($wpjobportal_stateid) . " AND LOWER(name) = '" . wpjobportalphplib::wpJP_strtolower(esc_sql($title)) . "'";

        $wpjobportal_result = wpjobportaldb::get_var($query);
        if ($wpjobportal_result > 0)
            return true;
        else
            return false;
    }

    private function getDataForLocationByCityID($wpjobportal_id) {
        if (!is_numeric($wpjobportal_id))
            return false;
        $query = "SELECT city.name AS cityname,state.name AS statename,country.name AS countryname
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city
                    JOIN `" . wpjobportal::$_db->prefix . "wj_portal_countries` AS country ON country.id = city.countryid
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_states` AS state ON state.id = city.stateid
                    WHERE city.id = " . esc_sql($wpjobportal_id);
        $wpjobportal_result = wpjobportaldb::get_row($query);
        return $wpjobportal_result;
    }

    function getLocationDataForView($cityids) {
        if ($cityids == '')
            return false;
        $location = '';
        if (wpjobportalphplib::wpJP_strstr($cityids, ',')) { // multi cities id
            $cities = wpjobportalphplib::wpJP_explode(',', $cityids);
            $wpjobportal_data = array();
            foreach ($cities AS $city) {
                $returndata = $this->getDataForLocationByCityID($city);
                if($returndata !=''){
                    $wpjobportal_data[] = $returndata;
                }
            }
            $wpjobportal_databycountry = array();
            foreach ($wpjobportal_data AS $d) {
                $wpjobportal_databycountry[$d->countryname][] = array('cityname' => $d->cityname, 'statename' => $d->statename);
            }
            foreach ($wpjobportal_databycountry AS $wpjobportal_countryname => $locdata) {
                $call = 0;
                foreach ($locdata AS $dl) {
                    if ($call == 0) {
                        $location .= '[' . wpjobportal::wpjobportal_getVariableValue($dl['cityname']);
                        if ($dl['statename']) {
                            $location .= '-' . wpjobportal::wpjobportal_getVariableValue($dl['statename']);
                        }
                    } else {
                        $location .= ', ' . $dl['cityname'];
                        if ($dl['statename']) {
                            $location .= '-' . wpjobportal::wpjobportal_getVariableValue($dl['statename']);
                        }
                    }
                    $call++;
                }
                $location .= ', ' . wpjobportal::wpjobportal_getVariableValue($wpjobportal_countryname) . '] ';
            }
        } else { // single city id
            $wpjobportal_data = $this->getDataForLocationByCityID($cityids);
            if (is_object($wpjobportal_data))
                $location = WPJOBPORTALincluder::getJSModel('common')->getLocationForView($wpjobportal_data->cityname, $wpjobportal_data->statename, $wpjobportal_data->countryname);
        }
        return $location;
    }

    function getAddressDataByCityName($cityname, $wpjobportal_id = 0) {
        if (!is_numeric($wpjobportal_id))
            return false;
        if (!$cityname)
            return false;


        if (wpjobportalphplib::wpJP_strstr($cityname, ',')) {
            $cityname = wpjobportalphplib::wpJP_str_replace(' ', '', $cityname);
            $wpjobportal_array = wpjobportalphplib::wpJP_explode(',', $cityname);
            $cityname = $wpjobportal_array[0];
			if(wpjobportal::$_configuration['defaultaddressdisplaytype'] == "cs"){ // City, State
				$wpjobportal_statename = $wpjobportal_array[1];
			}else{
				$wpjobportal_countryname = $wpjobportal_array[1];
			}
        }

        $query = "SELECT CONCAT(city.name";
        switch (wpjobportal::$_configuration['defaultaddressdisplaytype']) {
            case 'csc'://City, State, Country
                $query .= " ,', ', (IF(state.name is not null,state.name,'')),IF(state.name is not null,', ',''),country.name)";
                break;
            case 'cs'://City, State
                $query .= " ,', ', (IF(state.name is not null,state.name,'')))";
                break;
            case 'cc'://City, Country
                $query .= " ,', ', country.name)";
                break;
            case 'c'://city by default select for each case
                $query .= ")";
                break;
        }

        $query .= " AS name, city.id AS id,city.latitude,city.longitude
                      FROM `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city
                      JOIN `" . wpjobportal::$_db->prefix . "wj_portal_countries` AS country on city.countryid=country.id
                      LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_states` AS state on city.stateid=state.id";
        // if ($wpjobportal_id == 0)
        //     $query .= " WHERE city.name LIKE '" . esc_sql($cityname) . "%' AND country.enabled = 1 AND city.enabled = 1 LIMIT " . WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue("number_of_cities_for_autocomplete");
        // else
        //     $query .= " WHERE city.id = ".esc_sql($wpjobportal_id)." AND country.enabled = 1 AND city.enabled = 1";
        if ($wpjobportal_id == 0) {
            if (isset($wpjobportal_countryname)) {
                $query .= " WHERE city.name LIKE '" . esc_sql($cityname) . "%' AND country.name LIKE '" . esc_sql($wpjobportal_countryname) . "%' AND country.enabled = 1 AND city.enabled = 1 AND IF(state.name is not null,state.enabled,1) = 1 LIMIT " . WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue("number_of_cities_for_autocomplete");
                //$query .= " WHERE city.name LIKE '" . esc_sql($cityname) . "%' AND country.name LIKE '" . esc_sql($wpjobportal_countryname) . "%' AND country.enabled = 1 AND city.enabled = 1 AND IF(state.name is not null,state.enabled,1) = 1 LIMIT " . WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue("number_of_cities_for_autocomplete");
            }elseif (isset($wpjobportal_statename)) {
                $query .= " WHERE city.name LIKE '" . esc_sql($cityname) . "%' AND state.name LIKE '" . esc_sql($wpjobportal_statename) . "%' AND state.enabled = 1 AND city.enabled = 1 AND IF(state.name is not null,state.enabled,1) = 1 LIMIT " . WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue("number_of_cities_for_autocomplete");
                //$query .= " WHERE city.name LIKE '" . esc_sql($cityname) . "%' AND country.name LIKE '" . esc_sql($wpjobportal_countryname) . "%' AND country.enabled = 1 AND city.enabled = 1 AND IF(state.name is not null,state.enabled,1) = 1 LIMIT " . WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue("number_of_cities_for_autocomplete");
            } else {
                $query .= " WHERE city.name LIKE '" . esc_sql($cityname) . "%' AND country.enabled = 1 AND city.enabled = 1 AND IF(state.name is not null,state.enabled,1) = 1 LIMIT " . WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue("number_of_cities_for_autocomplete");
                //$query .= " WHERE city.name LIKE '" . esc_sql($cityname) . "%' AND country.enabled = 1 AND city.enabled = 1 AND IF(state.name is not null,state.enabled,1) = 1 LIMIT " . WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue("number_of_cities_for_autocomplete");
            }
        } else {
            $query .= " WHERE city.id = ".esc_sql($wpjobportal_id)." AND country.enabled = 1 AND city.enabled = 1";
        }
        $wpjobportal_result = wpjobportaldb::get_results($query);
        if (empty($wpjobportal_result))
            return null;
        else
            return $wpjobportal_result;
    }

    function storeTokenInputCity($wpjobportal_input) {

        $latitude = WPJOBPORTALrequest::getVar('latitude','','');
        $longitude = WPJOBPORTALrequest::getVar('longitude','','');

        $wpjobportal_tempData = wpjobportalphplib::wpJP_explode(',', $wpjobportal_input); // array to maintain spaces
        $wpjobportal_input = wpjobportalphplib::wpJP_str_replace(' ', '', $wpjobportal_input); // remove spaces from citydata
        // find number of commas
        $wpjobportal_num_commas = substr_count($wpjobportal_input, ',', 0);
        if ($wpjobportal_num_commas == 1) { // only city and country names are given
            $cityname = $wpjobportal_tempData[0];
            $wpjobportal_countryname = wpjobportalphplib::wpJP_str_replace(' ', '', $wpjobportal_tempData[1]);
        } elseif ($wpjobportal_num_commas > 1) {
            if ($wpjobportal_num_commas > 2)
                return 5;
            $cityname = $wpjobportal_tempData[0];
            if (wpjobportalphplib::wpJP_mb_strpos($wpjobportal_tempData[1], ' ') == 0) { // remove space from start of state name if exists
                $wpjobportal_statename = wpjobportalphplib::wpJP_substr($wpjobportal_tempData[1], 1, wpjobportalphplib::wpJP_strlen($wpjobportal_tempData[1]));
            } else {
                $wpjobportal_statename = $wpjobportal_tempData[1];
            }
            $wpjobportal_countryname = wpjobportalphplib::wpJP_str_replace(' ', '', $wpjobportal_tempData[2]);
        }

        // get list of countries from database and check if exists or not
        $wpjobportal_countryid = WPJOBPORTALincluder::getJSModel('country')->getCountryIdByName($wpjobportal_countryname); // new function coded
        if (!$wpjobportal_countryid) {
            return 4;
        }
        // if state name given in input check if exists or not otherwise store in database
        if (isset($wpjobportal_statename)) {
            $wpjobportal_stateid = WPJOBPORTALincluder::getJSModel('state')->getStateIdByName(wpjobportalphplib::wpJP_str_replace(' ', '', $wpjobportal_statename)); // new function coded
            if (!$wpjobportal_stateid) {
                $wpjobportal_statedata = array();
                $wpjobportal_statedata['id'] = null;
                $wpjobportal_statedata['name'] = wpjobportalphplib::wpJP_ucwords($wpjobportal_statename);
                $wpjobportal_statedata['shortRegion'] = wpjobportalphplib::wpJP_ucwords($wpjobportal_statename);
                $wpjobportal_statedata['countryid'] = $wpjobportal_countryid;
                $wpjobportal_statedata['enabled'] = 1;
                $wpjobportal_statedata['serverid'] = 0;

                $wpjobportal_newstate = WPJOBPORTALincluder::getJSModel('state')->storeTokenInputState($wpjobportal_statedata);
                if (!$wpjobportal_newstate) {
                    return 3;
                }
                $wpjobportal_stateid = WPJOBPORTALincluder::getJSModel('state')->getStateIdByName($wpjobportal_statename); // to store with city's new record
            }
        } else {
            $wpjobportal_stateid = null;
        }

        $wpjobportal_data = array();
        $wpjobportal_data['id'] = null;
        $wpjobportal_data['cityName'] = wpjobportalphplib::wpJP_ucwords($cityname);
        $wpjobportal_data['name'] = wpjobportalphplib::wpJP_ucwords($cityname);
        $wpjobportal_data['stateid'] = $wpjobportal_stateid;
        $wpjobportal_data['countryid'] = $wpjobportal_countryid;
        $wpjobportal_data['isedit'] = 1;
        $wpjobportal_data['enabled'] = 1;
        $wpjobportal_data['serverid'] = 0;
        $wpjobportal_data['latitude'] = $latitude;
        $wpjobportal_data['longitude'] = $longitude;
        $wpjobportal_data = wpjobportal::wpjobportal_sanitizeData($wpjobportal_data);
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('city');
        $wpjobportal_data = WPJOBPORTALincluder::getJSmodel('common')->stripslashesFull($wpjobportal_data);// remove slashes with quotes.
        if (!$wpjobportal_row->bind($wpjobportal_data)) {
            return 2;
        }
        if (!$wpjobportal_row->store()) {
            return 2;
        }
        if (isset($wpjobportal_statename)) {
            $wpjobportal_statename = wpjobportalphplib::wpJP_ucwords($wpjobportal_statename);
        } else {
            $wpjobportal_statename = '';
        }
        $wpjobportal_result[0] = 1;
        $wpjobportal_result[1] = $wpjobportal_row->id; // get the city id for forms
        $wpjobportal_result[2] = WPJOBPORTALincluder::getJSModel('common')->getLocationForView($wpjobportal_row->name, $wpjobportal_statename, $wpjobportal_countryname); // get the city name for forms
        $wpjobportal_result[3] = $latitude; // get the city name for forms
        $wpjobportal_result[4] = $longitude; // get the city name for forms
        return $wpjobportal_result;
    }

    public function savetokeninputcity() {

        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'save-token-input-city') ) {
            die( 'Security check Failed' );
        }
        $city_string = WPJOBPORTALrequest::getVar('citydata');
        $wpjobportal_result = $this->storeTokenInputCity($city_string);
        if (is_array($wpjobportal_result)) {
            $return_value = wp_json_encode(array('id' => $wpjobportal_result[1], 'name' => $wpjobportal_result[2], 'latitude'=>$wpjobportal_result[3], 'longitude'=>$wpjobportal_result[4] )); // send back the cityid newely created
        } elseif ($wpjobportal_result == 2) {
            $return_value = esc_html(__('Error in saving records please try again', 'wp-job-portal'));
        } elseif ($wpjobportal_result == 3) {
            $return_value = esc_html(__('Error while saving new state', 'wp-job-portal'));
        } elseif ($wpjobportal_result == 4) {
            $return_value = esc_html(__('Country not found', 'wp-job-portal'));
        } elseif ($wpjobportal_result == 5) {
            $return_value = esc_html(__('Location format is not correct please enter city in this format city name, country name', 'wp-job-portal'));
        }
        echo wp_kses($return_value, WPJOBPORTAL_ALLOWED_TAGS);
        exit();
    }

    //search cookies data
    function getSearchFormDataCity(){
        $wpjobportal_jsjp_search_array = array();
        $wpjobportal_jsjp_search_array['searchname'] = WPJOBPORTALrequest::getVar('searchname');
        $wpjobportal_jsjp_search_array['status'] = WPJOBPORTALrequest::getVar('status');
        $wpjobportal_jsjp_search_array['search_from_city'] = 1;
        return $wpjobportal_jsjp_search_array;
    }

    function getCookiesSavedCity(){
        $wpjobportal_jsjp_search_array = array();
        $wpjp_search_cookie_data = '';
        if(isset($_COOKIE['jsjp_jobportal_search_data'])){
            $wpjp_search_cookie_data = wpjobportal::wpjobportal_sanitizeData($_COOKIE['jsjp_jobportal_search_data']);
            $wpjp_search_cookie_data = wpjobportalphplib::wpJP_safe_decoding($wpjp_search_cookie_data);
            $wpjp_search_cookie_data = json_decode( $wpjp_search_cookie_data , true );
        }
        if($wpjp_search_cookie_data != '' && isset($wpjp_search_cookie_data['search_from_city']) && $wpjp_search_cookie_data['search_from_city'] == 1){
            $wpjobportal_jsjp_search_array['searchname'] = $wpjp_search_cookie_data['searchname'];
            $wpjobportal_jsjp_search_array['status'] = $wpjp_search_cookie_data['status'];
        }
        return $wpjobportal_jsjp_search_array;
    }

    function setSearchVariableCity($wpjobportal_jsjp_search_array){
        wpjobportal::$_search['city']['searchname'] = isset($wpjobportal_jsjp_search_array['searchname']) ? $wpjobportal_jsjp_search_array['searchname'] : null;
        wpjobportal::$_search['city']['status'] = isset($wpjobportal_jsjp_search_array['status']) ? $wpjobportal_jsjp_search_array['status'] : null;
    }

    function getMessagekey(){
        $wpjobportal_key = 'city';if(wpjobportal::$_common->wpjp_isadmin()){$wpjobportal_key = 'admin_'.$wpjobportal_key;}return $wpjobportal_key;
    }

    function loadAddressData() {
        $wpjobportal_data = WPJOBPORTALrequest::get('post');

        /*
        data variables
        [country_code] => ae
        [name_preference] => 1
        [keepdata] => 2
        */

        if(!isset($wpjobportal_data['country_code'])){
            return false;
        }

        // $wpjobportal_language code of country
        $wpjobportal_language_code = $wpjobportal_data['country_code'];
        // free data or pro data
        //$wpjobportal_data_to_import = 'free';
        $wpjobportal_data_to_import = $wpjobportal_data['data_to_import'];

        $file_contents = $this->getLocationDataFileContents($wpjobportal_language_code,$wpjobportal_data_to_import);
        if ($file_contents != '') { // making sure the string is not empty (every error case will return this string as empty)
            // checking & removing old data
            if(isset($wpjobportal_data['keepdata'])){
                // removing cities of a country from the database
                if($wpjobportal_data['keepdata'] == 1){
                    // KEEP DATA

                    // code to handle and modify query to avoid duplications
                    // get country id from country name code
                    $query = "SELECT id FROM `" . wpjobportal::$_db->prefix . "wj_portal_countries` WHERE namecode = '".esc_sql($wpjobportal_data['country_code'])."'";
                    $wpjobportal_countryid = wpjobportaldb::get_var($query);

                    if(is_numeric($wpjobportal_countryid) && $wpjobportal_countryid > 0){
                        // get country cities to comapre
                        $fetch_cities = " SELECT internationalname,stateid FROM`" . wpjobportal::$_db->prefix . "wj_portal_cities` WHERE countryid =".esc_sql($wpjobportal_countryid);
                        $wpjobportal_country_cities = wpjobportaldb::get_results($fetch_cities);

                        if(!empty($wpjobportal_country_cities)){ // means there are already cities for this country
                            // this function will find and remove records from query that already exsist in database
                            $file_contents = $this->processFileQueries($file_contents,$wpjobportal_country_cities);
                        }
                    }
                }elseif($wpjobportal_data['keepdata'] == 2){
                    // get country id from country name code
                    $query = "SELECT id FROM `" . wpjobportal::$_db->prefix . "wj_portal_countries` WHERE namecode = '".esc_sql($wpjobportal_data['country_code'])."'";
                    $wpjobportal_countryid = wpjobportaldb::get_var($query);
                    if(is_numeric($wpjobportal_countryid) && $wpjobportal_countryid > 0){
                        // remove specific country cities
                        $remove_cities = "DELETE FROM `" . wpjobportal::$_db->prefix . "wj_portal_cities` WHERE countryid =".esc_sql($wpjobportal_countryid);
                        wpjobportaldb::query($remove_cities);
                    }
                }elseif($wpjobportal_data['keepdata'] == 3){
                    // removing all cities from the database
                    $remove_cities = "DELETE FROM `" . wpjobportal::$_db->prefix . "wj_portal_cities`";
                    wpjobportaldb::query($remove_cities);
                }
            }
            if($file_contents != ''){
                //preparing queries to execute
                $query = wpjobportalphplib::wpJP_str_replace('#__', wpjobportal::$_db->prefix, $file_contents);

                $query_array  = explode(';',$query); // breaking queries up to execute seprately
                foreach ($query_array as $wpjobportal_array_key => $wpjobportal_single_query) {
                    $wpjobportal_single_query = trim($wpjobportal_single_query);
                    if($wpjobportal_single_query != ''){
                        wpjobportaldb::query($wpjobportal_single_query);
                    }
                }
            }

            //if($query_result){ // if query successfully executed return saved
                // function to update name records.
                $this->updateCitiesAndCountriesRecords($wpjobportal_data['name_preference']);
                return WPJOBPORTAL_SAVED;
            //}
        }
        // if call comes to this point means something went wrong.
        return WPJOBPORTAL_SAVE_ERROR;
    }

    function updateCityNameSettings() {
        $wpjobportal_data = WPJOBPORTALrequest::get('post');


        if(!isset($wpjobportal_data['name_preference'])){
            return false;
        }
        /*
        data variable
        [name_preference] => 1
        */

        // function to update records.
        $this->updateCitiesAndCountriesRecords($wpjobportal_data['name_preference']);

        // if($wpjobportal_data['name_preference'] == 1){ // set internation name
        //     // update cities table while making sure the value being set is not empty
        //     $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_cities`
        //                 SET `name` = `internationalname`
        //                 WHERE `internationalname` IS NOT NULL
        //                 AND `internationalname` != '';
        //                 ";
        //     $query_result = wpjobportaldb::query($query);

        //     // update countries table while making sure the value being set is not empty
        //     $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_countries`
        //                 SET `name` = `internationalname`
        //                 WHERE `internationalname` IS NOT NULL
        //                 AND `internationalname` != '';
        //                 ";
        //     $query_result = wpjobportaldb::query($query);
        // }elseif($wpjobportal_data['name_preference'] == 2){ // set local name
        //     // update cities table while making sure the value being set is not empty
        //     $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_cities`
        //                 SET `name` = `localname`
        //                 WHERE `localname` IS NOT NULL
        //                 AND `localname` != '';
        //                 ";
        //     $query_result = wpjobportaldb::query($query);

        //     // update countries table while making sure the value being set is not empty
        //     $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_countries`
        //                 SET `name` = `localname`
        //                 WHERE `localname` IS NOT NULL
        //                 AND `localname` != '';
        //                 ";
        //     $query_result = wpjobportaldb::query($query);
        // }

        // if call comes to this point means something went wrong.
        return WPJOBPORTAL_SAVED;
    }

    //this function updates the name column records for city and country table
    function updateCitiesAndCountriesRecords($wpjobportal_name_preference){
        if(is_numeric($wpjobportal_name_preference) && $wpjobportal_name_preference > 0){
            if($wpjobportal_name_preference == 1){ // set internation name
                // update cities table while making sure the value being set is not empty
                $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_cities`
                            SET `name` = `internationalname`
                            WHERE `internationalname` IS NOT NULL
                            AND `internationalname` != '';
                            ";
                $query_result = wpjobportaldb::query($query);

                // update states table while making sure the value being set is not empty
                $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_states`
                            SET `name` = `internationalname`
                            WHERE `internationalname` IS NOT NULL
                            AND `internationalname` != '';
                            ";
                $query_result = wpjobportaldb::query($query);

                // update countries table while making sure the value being set is not empty
                $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_countries`
                            SET `name` = `internationalname`
                            WHERE `internationalname` IS NOT NULL
                            AND `internationalname` != '';
                            ";
                $query_result = wpjobportaldb::query($query);

            }elseif($wpjobportal_name_preference == 2){ // set local name
                // update cities table while making sure the value being set is not empty
                $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_cities`
                            SET `name` = `localname`
                            WHERE `localname` IS NOT NULL
                            AND `localname` != '';
                            ";
                $query_result = wpjobportaldb::query($query);

                // update states table while making sure the value being set is not empty
                $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_states`
                            SET `name` = `localname`
                            WHERE `localname` IS NOT NULL
                            AND `localname` != '';
                            ";
                $query_result = wpjobportaldb::query($query);

                // update countries table while making sure the value being set is not empty
                $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_countries`
                            SET `name` = `localname`
                            WHERE `localname` IS NOT NULL
                            AND `localname` != '';
                            ";
                $query_result = wpjobportaldb::query($query);
            }
            // setting location name prefrence in options to show
            update_option("wpjobportal_location_name_preference",$wpjobportal_name_preference);
        }
    }


    function getLocationDataFileContents($wpjobportal_country_code,$wpjobportal_data_to_import){

        // if trying to import pro data check if addon is installed
        if(in_array('addressdata',wpjobportal::$_active_addons) && $wpjobportal_data_to_import == 'pro'){
            // pro version get sql content as json
            $wpjobportal_addon_name = 'addressdata';
            $wpjobportal_addon_version = get_option('wpjobportal-addon-addressdata-version');
            // http call to live server to get pro version of city data for the country
            $wpjobportal_json_response = WPJOBPORTALincluder::getJSModel('premiumplugin')->getAddressSqlFile($wpjobportal_addon_name,$wpjobportal_addon_version,$wpjobportal_country_code);
            if($wpjobportal_json_response != ''){
                $response_array = json_decode($wpjobportal_json_response,true);
                if(isset($response_array['error_code'])){
                    $wpjobportal_error_message = "Load Address data addon sql activation error";
                    WPJOBPORTALincluder::getJSModel('systemerror')->addSystemError($wpjobportal_error_message);
                }else if(isset($response_array['verfication_status'])){
                    if($response_array['verfication_status'] == 0){
                        $wpjobportal_error_message = "User authentication failed";
                        WPJOBPORTALincluder::getJSModel('systemerror')->addSystemError($wpjobportal_error_message);
                    }else if($response_array['verfication_status'] == 1){ // everything is correct
                        if(isset($response_array['update_sql']) && $response_array['update_sql'] != ''){
                            return $response_array['update_sql']; //  everything is correct
                        }
                    }
                }
            }
            return '';// somthing went wrong
        }else{// importing free data
            if ( ! function_exists( 'WP_Filesystem' ) ) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
            }
            global $wp_filesystem;
            if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ) {
                $creds = request_filesystem_credentials( site_url() );
                wp_filesystem( $creds );
            }

            $wpjobportal_installfile = WPJOBPORTAL_PLUGIN_PATH . 'includes/data/cities/'.$wpjobportal_country_code.'/cities.txt';
            // check file exsists
            if ($wp_filesystem->exists($wpjobportal_installfile)) {
                // reading the file
                $file_contents = $wp_filesystem->get_contents($wpjobportal_installfile);
                if ($file_contents !== false) { // if no error then proceed
                    return $file_contents; //  everything is correct
                }else{
                    $wpjobportal_error_message = "Address Data file reading error";
                    WPJOBPORTALincluder::getJSModel('systemerror')->addSystemError($wpjobportal_error_message);
                }
            }else{
                $wpjobportal_error_message = "Address Data file not found";
                WPJOBPORTALincluder::getJSModel('systemerror')->addSystemError($wpjobportal_error_message);
            }
        }
        return ''; // somthing went wrong
    }

    function getSampleCities() {
        //Data
        $query = "SELECT name, localname, internationalname FROM `" . wpjobportal::$_db->prefix . "wj_portal_cities`";
        $query .=" ORDER BY id DESC LIMIT 10";
        wpjobportal::$_data[0] = wpjobportaldb::get_results($query);
        return;
    }

    function clean_word($word){
        $word_to_check = str_replace('-', '', $word);
        $word_to_check = str_replace(',', '', $word_to_check);
        $word_to_check = str_replace('.', '', $word_to_check);
        $word_to_check = str_replace(' ', '', $word_to_check);
        $word_to_check = str_replace('"', '', $word_to_check);
        $word_to_check = str_replace("'", '', $word_to_check);
        $word_to_check = strtolower($word_to_check);
        return $word_to_check;
    }


    function processFileQueries($wpjobportal_main_query,$wpjobportal_exsisting_data){
        // if exsisting data empty somehow return the query
        if( empty($wpjobportal_exsisting_data)){
            return $wpjobportal_main_query;
        }
        // this variable is exsisting data in modifed form easy for comapre
        $wpjobportal_data_to_check = array();
        foreach ($wpjobportal_exsisting_data as $city) {
            // using city name as index to check for duplication
            $city_name_for_index = $this->clean_word($city->internationalname);
            if($city->stateid != ''){
                $city_name_for_index .= $city->stateid;
            }
            $wpjobportal_data_to_check[$city_name_for_index] = 1;
        }

        // seprate all insert queries
        $wpjobportal_seprate_queries_array = explode(';',$wpjobportal_main_query);

        $final_query = '';
        // loop over insert queries to process them
        foreach ($wpjobportal_seprate_queries_array as $query) {
            // make sure the query is not just empty string
            $query = trim($query);
            if($query == ''){
                continue;
            }

            $wpjobportal_temp_query = $this->processSingleQuery($query,$wpjobportal_data_to_check);
            // if($wpjobportal_temp_query != ''){ // removing this check in case of all cities already exsist
                $final_query .= $wpjobportal_temp_query;
            // }
        }

        // removing this check in case of all cities already exsist
        // if($final_query != ''){
        //     $wpjobportal_main_query =  $final_query;
        // }
        return $final_query;
    }


    function processSingleQuery($query,$wpjobportal_exsisting_data){

        if( empty($wpjobportal_exsisting_data)){
            return $query;
        }

        // this will separate the insert statemenr from values
        $wpjobportal_main_parts = explode('VALUES', $query);

        // will only contain insert statement (before the word values)
        $wpjobportal_insert_query_part = $wpjobportal_main_parts[0];

        // this will only contain values section
        $wpjobportal_insert_value_part = $wpjobportal_main_parts[1];

        // will add "NEXTRECORD" in text after every record
        $wpjobportal_insert_value_part = str_replace('"),','"),NEXTRECORD', $wpjobportal_insert_value_part);

        // will make an array of line using NEXTRECORD as breaking point
        $wpjobportal_insert_value_parts_array = explode("NEXTRECORD",$wpjobportal_insert_value_part);

        //variable that will contain new cities to be isnerted
        $wpjobportal_new_cities_records_string = '';

        // process indivual record
        foreach ($wpjobportal_insert_value_parts_array as $record_string) {
            $record_query = explode(',',$record_string);

            // 2 index is international name
            $wpjobportal_index_to_check  = $this->clean_word($record_query[2]);
            $record_query[3]= trim($record_query[3]);
            // 3 index is statename
            if($record_query[3] != '' && $record_query[3] != 'NULL' ){
                $wpjobportal_index_to_check  .= $record_query[3];
            }

            if(isset($wpjobportal_exsisting_data[$wpjobportal_index_to_check]) && $wpjobportal_exsisting_data[$wpjobportal_index_to_check] == 1){// check condition ??
                continue;
            }else{
                $wpjobportal_new_cities_records_string .= $record_string; // rebuilding string using single records
            }
        }

        $wpjobportal_new_query = '';
        if($wpjobportal_new_cities_records_string !=''){

            // remove if the last character is a ","
            $wpjobportal_new_cities_records_string = rtrim($wpjobportal_new_cities_records_string, ',');

            // set different parts into same array
            $wpjobportal_make_query = array();
            $wpjobportal_make_query[0] = $wpjobportal_insert_query_part;
            $wpjobportal_make_query[1] = $wpjobportal_new_cities_records_string;

            // convert the array to string
            $wpjobportal_new_query = implode('VALUES', $wpjobportal_make_query);

            // adding semi colon at the end to make the query syntax proper
            $wpjobportal_new_query = $wpjobportal_new_query.";\n\n ";
        }

        return $wpjobportal_new_query;
    }
}

?>