<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALCityModel {

    function getCitybyId($id) {
        if ($id) {
            if (!is_numeric($id))
                return false;
            $query = "SELECT * FROM " . wpjobportal::$_db->prefix . "wj_portal_cities WHERE id = " . esc_sql($id);
            wpjobportal::$_data[0] = wpjobportaldb::get_row($query);
        }
        return;
    }

        function getCityNamebyId($id) {
        if (is_numeric($id) == false)
            return false;
        $query = "SELECT name FROM `". wpjobportal::$_db->prefix ."wj_portal_cities` WHERE id = " . esc_sql($id);
        return wpjobportaldb::get_var($query);
    }

    function getCoordinatesOfCities($pageid){
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
        $data = wpjobportaldb::get_results($query);
        $final_array= array();
        $i = 0;
        foreach($data AS $l){
            if(is_numeric($l->latitude) && is_numeric($l->longitude) ){
                $link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'jobs', 'city'=>$l->cityid , 'wpjobportalpageid' => $pageid ));
                $img =     JOB_PORTAL_THEME_IMAGE.'/location-icons/loction-mark-icon-'.$i.'.png';
                $final_array[] = array('lat' => $l->latitude, 'lng' => $l->longitude ,'link' => $link, 'img' => $img);
                $i ++;
                if($i > 10){
                    $i = 0;
                }
            }
        }
        $jfinal_array = wp_json_encode($final_array);
        wpjobportal::$_data['coordinates'] = $jfinal_array;
        return;
    }


    function getAllStatesCities($countryid, $stateid) {
        if (!is_numeric($countryid))
            return false;

        //Filter
        $searchname = wpjobportal::$_search['city']['searchname'];
        $status = wpjobportal::$_search['city']['status'];

        $inquery = '';
        $clause = ' WHERE ';
        if ($searchname != null) {
            $inquery .= esc_sql($clause) . " name LIKE '%".esc_sql($searchname)."%'";
            $clause = ' AND ';
        }
        if (is_numeric($status)) {
            $inquery .= esc_sql($clause) . " enabled = " . esc_sql($status);
            $clause = ' AND ';
        }

        if ($stateid) {
            if(is_numeric($stateid)){
                $inquery .=esc_sql($clause) . " stateid = " . esc_sql($stateid);
                $clause = ' AND ';
            }
        }
        if ($countryid) {
            $inquery .= esc_sql($clause) . "countryid = " . esc_sql($countryid);
            $clause = ' AND ';
        }

        wpjobportal::$_data['filter']['searchname'] = $searchname;
        wpjobportal::$_data['filter']['status'] = $status;


        //Pagination
        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_cities`";
        $query .= $inquery;
        $total = wpjobportaldb::get_var($query);
        wpjobportal::$_data['total'] = $total;
        wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($total);

        //Data
        $query = "SELECT * FROM `" . wpjobportal::$_db->prefix . "wj_portal_cities`";
        $query .=$inquery;
        $query .=" ORDER BY name ASC LIMIT " . WPJOBPORTALpagination::$_offset . " , " . WPJOBPORTALpagination::$_limit;
        wpjobportal::$_data[0] = wpjobportaldb::get_results($query);

        return;
    }

    function storeCity($data, $countryid, $stateid) {
        if (empty($data))
            return false;

        if ($data['id'] == '') {
            $result = $this->isCityExist($countryid, $stateid, $data['name']);
            if ($result == true) {
                return WPJOBPORTAL_ALREADY_EXIST;
            }
        }

        $data['countryid'] = $countryid;
        $data['stateid'] = $stateid;
        $data['cityName'] = $data['name'];

        $row = WPJOBPORTALincluder::getJSTable('city');
        $data = WPJOBPORTALincluder::getJSmodel('common')->stripslashesFull($data);// remove slashes with quotes.
        $data = wpjobportal::wpjobportal_sanitizeData($data);
        if (!$row->bind($data)) {
            return WPJOBPORTAL_SAVE_ERROR;
        }
        if (!$row->store()) {
            return WPJOBPORTAL_SAVE_ERROR;
        }

        return WPJOBPORTAL_SAVED;
    }

    function deleteCities($ids) {
        if (empty($ids))
            return false;
        $row = WPJOBPORTALincluder::getJSTable('city');
        $notdeleted = 0;
        foreach ($ids as $id) {
            if ($this->cityCanDelete($id) == true) {
                if (!$row->delete($id)) {
                    $notdeleted += 1;
                }
            } else {
                $notdeleted += 1;
            }
        }
        if ($notdeleted == 0) {
            WPJOBPORTALMessages::$counter = false;
            return WPJOBPORTAL_DELETED;
        } else {
            WPJOBPORTALMessages::$counter = $notdeleted;
            return WPJOBPORTAL_DELETE_ERROR;
        }
    }

    function publishUnpublish($ids, $status) {
        if (empty($ids))
            return false;
        if (!is_numeric($status))
            return false;

        $row = WPJOBPORTALincluder::getJSTable('city');
        $total = 0;
        if ($status == 1) {
            foreach ($ids as $id) {
                if (!$row->update(array('id' => $id, 'enabled' => $status))) {
                    $total += 1;
                }
            }
        } else {
            foreach ($ids as $id) {
                if ($this->cityCanUnpublish($id)) {
                    if (!$row->update(array('id' => $id, 'enabled' => $status))) {
                        $total += 1;
                    }
                } else {
                    $total += 1;
                }
            }
        }
        if ($total == 0) {
            WPJOBPORTALMessages::$counter = false;
            if ($status == 1)
                return WPJOBPORTAL_PUBLISHED;
            else
                return WPJOBPORTAL_UN_PUBLISHED;
        }else {
            WPJOBPORTALMessages::$counter = $total;
            if ($status == 1)
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

        $total = wpjobportaldb::get_var($query);

        if ($total > 0)
            return false;
        else
            return true;
    }

    function isCityExist($countryid, $stateid, $title) {
        if (!is_numeric($countryid))
            return false;
        if (!is_numeric($stateid))
            return false;

        $query = "SELECT COUNT(id) FROM " . wpjobportal::$_db->prefix . "wj_portal_cities WHERE countryid=" . esc_sql($countryid) . "
		AND stateid=" . esc_sql($stateid) . " AND LOWER(name) = '" . wpjobportalphplib::wpJP_strtolower(esc_sql($title)) . "'";

        $result = wpjobportaldb::get_var($query);
        if ($result > 0)
            return true;
        else
            return false;
    }

    private function getDataForLocationByCityID($id) {
        if (!is_numeric($id))
            return false;
        $query = "SELECT city.cityName AS cityname,state.name AS statename,country.name AS countryname
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city
                    JOIN `" . wpjobportal::$_db->prefix . "wj_portal_countries` AS country ON country.id = city.countryid
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_states` AS state ON state.id = city.stateid
                    WHERE city.id = " . esc_sql($id);
        $result = wpjobportaldb::get_row($query);
        return $result;
    }

    function getLocationDataForView($cityids) {
        if ($cityids == '')
            return false;
        $location = '';
        if (wpjobportalphplib::wpJP_strstr($cityids, ',')) { // multi cities id
            $cities = wpjobportalphplib::wpJP_explode(',', $cityids);
            $data = array();
            foreach ($cities AS $city) {
                $returndata = $this->getDataForLocationByCityID($city);
                if($returndata !=''){
                    $data[] = $returndata;
                }
            }
            $databycountry = array();
            foreach ($data AS $d) {
                $databycountry[$d->countryname][] = array('cityname' => $d->cityname, 'statename' => $d->statename);
            }
            foreach ($databycountry AS $countryname => $locdata) {
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
                $location .= ', ' . wpjobportal::wpjobportal_getVariableValue($countryname) . '] ';
            }
        } else { // single city id
            $data = $this->getDataForLocationByCityID($cityids);
            if (is_object($data))
                $location = WPJOBPORTALincluder::getJSModel('common')->getLocationForView($data->cityname, $data->statename, $data->countryname);
        }
        return $location;
    }

    function getAddressDataByCityName($cityname, $id = 0) {
        if (!is_numeric($id))
            return false;
        if (!$cityname)
            return false;


        if (wpjobportalphplib::wpJP_strstr($cityname, ',')) {
            $cityname = wpjobportalphplib::wpJP_str_replace(' ', '', $cityname);
            $array = wpjobportalphplib::wpJP_explode(',', $cityname);
            $cityname = $array[0];
			if(wpjobportal::$_configuration['defaultaddressdisplaytype'] == "cs"){ // City, State
				$statename = $array[1];
			}else{
				$countryname = $array[1];
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
        // if ($id == 0)
        //     $query .= " WHERE city.name LIKE '" . esc_sql($cityname) . "%' AND country.enabled = 1 AND city.enabled = 1 LIMIT " . WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue("number_of_cities_for_autocomplete");
        // else
        //     $query .= " WHERE city.id = ".esc_sql($id)." AND country.enabled = 1 AND city.enabled = 1";
        if ($id == 0) {
            if (isset($countryname)) {
                $query .= " WHERE city.name LIKE '" . esc_sql($cityname) . "%' AND country.name LIKE '" . esc_sql($countryname) . "%' AND country.enabled = 1 AND city.enabled = 1 AND IF(state.name is not null,state.enabled,1) = 1 LIMIT " . WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue("number_of_cities_for_autocomplete");
                //$query .= " WHERE city.cityName LIKE '" . esc_sql($cityname) . "%' AND country.name LIKE '" . esc_sql($countryname) . "%' AND country.enabled = 1 AND city.enabled = 1 AND IF(state.name is not null,state.enabled,1) = 1 LIMIT " . WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue("number_of_cities_for_autocomplete");
            }elseif (isset($statename)) {
                $query .= " WHERE city.name LIKE '" . esc_sql($cityname) . "%' AND state.name LIKE '" . esc_sql($statename) . "%' AND state.enabled = 1 AND city.enabled = 1 AND IF(state.name is not null,state.enabled,1) = 1 LIMIT " . WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue("number_of_cities_for_autocomplete");
                //$query .= " WHERE city.cityName LIKE '" . esc_sql($cityname) . "%' AND country.name LIKE '" . esc_sql($countryname) . "%' AND country.enabled = 1 AND city.enabled = 1 AND IF(state.name is not null,state.enabled,1) = 1 LIMIT " . WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue("number_of_cities_for_autocomplete");
            } else {
                $query .= " WHERE city.name LIKE '" . esc_sql($cityname) . "%' AND country.enabled = 1 AND city.enabled = 1 AND IF(state.name is not null,state.enabled,1) = 1 LIMIT " . WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue("number_of_cities_for_autocomplete");
                //$query .= " WHERE city.cityName LIKE '" . esc_sql($cityname) . "%' AND country.enabled = 1 AND city.enabled = 1 AND IF(state.name is not null,state.enabled,1) = 1 LIMIT " . WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue("number_of_cities_for_autocomplete");
            }
        } else {
            $query .= " WHERE city.id = ".esc_sql($id)." AND country.enabled = 1 AND city.enabled = 1";
        }
        $result = wpjobportaldb::get_results($query);
        if (empty($result))
            return null;
        else
            return $result;
    }

    function storeTokenInputCity($input) {

        $latitude = WPJOBPORTALrequest::getVar('latitude','','');
        $longitude = WPJOBPORTALrequest::getVar('longitude','','');

        $tempData = wpjobportalphplib::wpJP_explode(',', $input); // array to maintain spaces
        $input = wpjobportalphplib::wpJP_str_replace(' ', '', $input); // remove spaces from citydata
        // find number of commas
        $num_commas = substr_count($input, ',', 0);
        if ($num_commas == 1) { // only city and country names are given
            $cityname = $tempData[0];
            $countryname = wpjobportalphplib::wpJP_str_replace(' ', '', $tempData[1]);
        } elseif ($num_commas > 1) {
            if ($num_commas > 2)
                return 5;
            $cityname = $tempData[0];
            if (wpjobportalphplib::wpJP_mb_strpos($tempData[1], ' ') == 0) { // remove space from start of state name if exists
                $statename = wpjobportalphplib::wpJP_substr($tempData[1], 1, wpjobportalphplib::wpJP_strlen($tempData[1]));
            } else {
                $statename = $tempData[1];
            }
            $countryname = wpjobportalphplib::wpJP_str_replace(' ', '', $tempData[2]);
        }

        // get list of countries from database and check if exists or not
        $countryid = WPJOBPORTALincluder::getJSModel('country')->getCountryIdByName($countryname); // new function coded
        if (!$countryid) {
            return 4;
        }
        // if state name given in input check if exists or not otherwise store in database
        if (isset($statename)) {
            $stateid = WPJOBPORTALincluder::getJSModel('state')->getStateIdByName(wpjobportalphplib::wpJP_str_replace(' ', '', $statename)); // new function coded
            if (!$stateid) {
                $statedata = array();
                $statedata['id'] = null;
                $statedata['name'] = wpjobportalphplib::wpJP_ucwords($statename);
                $statedata['shortRegion'] = wpjobportalphplib::wpJP_ucwords($statename);
                $statedata['countryid'] = $countryid;
                $statedata['enabled'] = 1;
                $statedata['serverid'] = 0;

                $newstate = WPJOBPORTALincluder::getJSModel('state')->storeTokenInputState($statedata);
                if (!$newstate) {
                    return 3;
                }
                $stateid = WPJOBPORTALincluder::getJSModel('state')->getStateIdByName($statename); // to store with city's new record
            }
        } else {
            $stateid = null;
        }

        $data = array();
        $data['id'] = null;
        $data['cityName'] = wpjobportalphplib::wpJP_ucwords($cityname);
        $data['name'] = wpjobportalphplib::wpJP_ucwords($cityname);
        $data['stateid'] = $stateid;
        $data['countryid'] = $countryid;
        $data['isedit'] = 1;
        $data['enabled'] = 1;
        $data['serverid'] = 0;
        $data['latitude'] = $latitude;
        $data['longitude'] = $longitude;
        $data = wpjobportal::wpjobportal_sanitizeData($data);
        $row = WPJOBPORTALincluder::getJSTable('city');
        $data = WPJOBPORTALincluder::getJSmodel('common')->stripslashesFull($data);// remove slashes with quotes.
        if (!$row->bind($data)) {
            return 2;
        }
        if (!$row->store()) {
            return 2;
        }
        if (isset($statename)) {
            $statename = wpjobportalphplib::wpJP_ucwords($statename);
        } else {
            $statename = '';
        }
        $result[0] = 1;
        $result[1] = $row->id; // get the city id for forms
        $result[2] = WPJOBPORTALincluder::getJSModel('common')->getLocationForView($row->name, $statename, $countryname); // get the city name for forms
        $result[3] = $latitude; // get the city name for forms
        $result[4] = $longitude; // get the city name for forms
        return $result;
    }

    public function savetokeninputcity() {

        $nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'save-token-input-city') ) {
            die( 'Security check Failed' );
        }
        $city_string = WPJOBPORTALrequest::getVar('citydata');
        $result = $this->storeTokenInputCity($city_string);
        if (is_array($result)) {
            $return_value = wp_json_encode(array('id' => $result[1], 'name' => $result[2], 'latitude'=>$result[3], 'longitude'=>$result[4] )); // send back the cityid newely created
        } elseif ($result == 2) {
            $return_value = esc_html(__('Error in saving records please try again', 'wp-job-portal'));
        } elseif ($result == 3) {
            $return_value = esc_html(__('Error while saving new state', 'wp-job-portal'));
        } elseif ($result == 4) {
            $return_value = esc_html(__('Country not found', 'wp-job-portal'));
        } elseif ($result == 5) {
            $return_value = esc_html(__('Location format is not correct please enter city in this format city name, country name', 'wp-job-portal'));
        }
        echo wp_kses($return_value, WPJOBPORTAL_ALLOWED_TAGS);
        exit();
    }

    //search cookies data
    function getSearchFormDataCity(){
        $jsjp_search_array = array();
        $jsjp_search_array['searchname'] = WPJOBPORTALrequest::getVar('searchname');
        $jsjp_search_array['status'] = WPJOBPORTALrequest::getVar('status');
        $jsjp_search_array['search_from_city'] = 1;
        return $jsjp_search_array;
    }

    function getCookiesSavedCity(){
        $jsjp_search_array = array();
        $wpjp_search_cookie_data = '';
        if(isset($_COOKIE['jsjp_jobportal_search_data'])){
            $wpjp_search_cookie_data = wpjobportal::wpjobportal_sanitizeData($_COOKIE['jsjp_jobportal_search_data']);
            $wpjp_search_cookie_data = wpjobportalphplib::wpJP_safe_decoding($wpjp_search_cookie_data);
            $wpjp_search_cookie_data = json_decode( $wpjp_search_cookie_data , true );
        }
        if($wpjp_search_cookie_data != '' && isset($wpjp_search_cookie_data['search_from_city']) && $wpjp_search_cookie_data['search_from_city'] == 1){
            $jsjp_search_array['searchname'] = $wpjp_search_cookie_data['searchname'];
            $jsjp_search_array['status'] = $wpjp_search_cookie_data['status'];
        }
        return $jsjp_search_array;
    }

    function setSearchVariableCity($jsjp_search_array){
        wpjobportal::$_search['city']['searchname'] = isset($jsjp_search_array['searchname']) ? $jsjp_search_array['searchname'] : null;
        wpjobportal::$_search['city']['status'] = isset($jsjp_search_array['status']) ? $jsjp_search_array['status'] : null;
    }

    function getMessagekey(){
        $key = 'city';if(wpjobportal::$_common->wpjp_isadmin()){$key = 'admin_'.$key;}return $key;
    }


}

?>
