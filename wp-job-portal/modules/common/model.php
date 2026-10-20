<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALCommonModel {

    public $module_name = '';
    public $file_name = '';


    function removeSpecialCharacter($wpjobportal_string) {
        $wpjobportal_string = sanitize_title($wpjobportal_string);
        return $wpjobportal_string;
    }

    function stringToAlias($wpjobportal_string){
        $wpjobportal_string = $this->removeSpecialCharacter($wpjobportal_string);
        $wpjobportal_string = wpjobportalphplib::wpJP_strtolower(wpjobportalphplib::wpJP_str_replace(' ', '-', $wpjobportal_string));
        $wpjobportal_string = wpjobportalphplib::wpJP_strtolower(wpjobportalphplib::wpJP_str_replace('_', '-', $wpjobportal_string));
        return $wpjobportal_string;
    }

     function getTimeForView($time,$unit){
        // made the change to translate unit value(day,days,week,weeks etc)
        if($time > 1){
            $unit .= 's';
        }
        $wpjobportal_text = $time.' '.wpjobportal::wpjobportal_getVariableValue(wpjobportalphplib::wpJP_ucfirst($unit));
        return $wpjobportal_text;
    }

    function getGoogleMapApiAddress() {

        // The address pattern used to be fetched over HTTP from a file in the plugin; it is the same fixed text.
        $file_key_string = '{PROTOCOL}maps.googleapis.com/maps/api/js?key={KEY}';

        $wpjobportal_key =wpjobportal::$_configuration['google_map_api_key'];
        $wpjobportal_protocol = is_ssl() ? "https://" : "http://";
        $wpjobportal_matcharray = array(
            '{PROTOCOL}' => $wpjobportal_protocol,
            '{KEY}' => $wpjobportal_key,
        );
        foreach ($wpjobportal_matcharray AS $find => $replace) {
            $file_key_string = wpjobportalphplib::wpJP_str_replace($find, $replace, $file_key_string);
        }
        return $file_key_string;
    }

    function getFancyPrice($wpjobportal_price,$currentyid=0,$wpjobportal_override_config=array()){
        $currency_align = isset($wpjobportal_override_config['currency_align']) ? $wpjobportal_override_config['currency_align'] : wpjobportal::$_configuration['currency_align'];
        $thousand_separator = isset($wpjobportal_override_config['thousand_separator']) ? $wpjobportal_override_config['thousand_separator'] : wpjobportal::$_configuration['thousand_separator'];
        $short_price = isset($wpjobportal_override_config['short_price']) ? $wpjobportal_override_config['short_price'] : wpjobportal::$_configuration['short_price'];
        if(isset($wpjobportal_override_config['decimal_places'])){
            if($wpjobportal_override_config['decimal_places'] === 'fit_to_currency' && $currentyid){
                $wpjobportal_decimal_places = '';//wpjobportalphplib::wpJP_strlen(WPJOBPORTALincluder::getJSModel('currency')->getCurrencySmallestUnit($currentyid))-1;
            }else{
                $wpjobportal_decimal_places = $wpjobportal_override_config['decimal_places'];
            }
        }else{
            $wpjobportal_decimal_places = wpjobportal::$_configuration['decimal_places'];
        }

        $wpjobportal_text = '';
        if($currency_align == 1 && $currentyid ){//left
            $wpjobportal_text .= WPJOBPORTALincluder::getJSModel('currency')->getCurrencySymbol($currentyid);
        }

        if( $short_price ){
            $wpjobportal_text .= $this->getShortFormatPrice($wpjobportal_price);
        }else{
            $wpjobportal_text .= wpjobportalphplib::wpJP_number_format($wpjobportal_price,$wpjobportal_decimal_places,'.',$thousand_separator);
        }

        if($currency_align == 2 && $currentyid ){ //right
            $wpjobportal_text .= WPJOBPORTALincluder::getJSModel('currency')->getCurrencySymbol($currentyid);
        }
        return $wpjobportal_text;
    }

    function getShortFormatPrice($wpjobportal_n, $wpjobportal_precision = 1){
        if($wpjobportal_n < 900) {
            // 0 - 900
            $wpjobportal_n_format = wpjobportalphplib::wpJP_number_format($wpjobportal_n, $wpjobportal_precision);
            $wpjobportal_suffix = '';
        }else if($wpjobportal_n < 900000) {
            // 0.9k-850k
            $wpjobportal_n_format = wpjobportalphplib::wpJP_number_format($wpjobportal_n / 1000, $wpjobportal_precision);
            $wpjobportal_suffix = 'K';
        }else if($wpjobportal_n < 900000000) {
            // 0.9m-850m
            $wpjobportal_n_format = wpjobportalphplib::wpJP_number_format($wpjobportal_n / 1000000, $wpjobportal_precision);
            $wpjobportal_suffix = 'M';
        }else if($wpjobportal_n < 900000000000) {
            // 0.9b-850b
            $wpjobportal_n_format = wpjobportalphplib::wpJP_number_format($wpjobportal_n / 1000000000, $wpjobportal_precision);
            $wpjobportal_suffix = 'B';
        }else{
            // 0.9t+
            $wpjobportal_n_format = wpjobportalphplib::wpJP_number_format($wpjobportal_n / 1000000000000, $wpjobportal_precision);
            $wpjobportal_suffix = 'T';
        }
      // Remove unecessary zeroes after decimal. "1.0" -> "1"; "1.00" -> "1"
      // Intentionally does not affect partials, eg "1.50" -> "1.50"
        if( $wpjobportal_precision > 0 ) {
            $dotzero = '.' . wpjobportalphplib::wpJP_str_repeat( '0', $wpjobportal_precision );
            $wpjobportal_n_format = wpjobportalphplib::wpJP_str_replace( $dotzero, '', $wpjobportal_n_format );
        }
        return $wpjobportal_n_format . $wpjobportal_suffix;
    }
    /**
    * @param wp job portal Function's
    * @param Get Status
    */
    function setDefaultForDefaultTable($wpjobportal_id, $wpjobportal_tablename) {
        if (is_numeric($wpjobportal_id) == false)
            return false;

        switch ($wpjobportal_tablename) {
            case "jobtypes":
            case "jobstatus":
            case "heighesteducation":
            case "careerlevels":
            case "experiences":
            case "currencies":
            case "salaryrangetypes":
            case "categories":
            case "subcategories":
                if (self::checkCanMakeDefault($wpjobportal_id, $wpjobportal_tablename)) {
                    if ($wpjobportal_tablename == "currencies")
                        $column = "default";
                    else
                        $column = "isdefault";
                    //DB class limitations
                    $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_" . esc_sql($wpjobportal_tablename) . "` AS t SET t." . esc_sql($column) . " = 0 ";
                    wpjobportaldb::query($query);
                    $query = "UPDATE  `" . wpjobportal::$_db->prefix . "wj_portal_" . esc_sql($wpjobportal_tablename) . "` AS t SET t." . esc_sql($column) . " = 1 WHERE id=" . (int) ($wpjobportal_id);
                    if (!wpjobportaldb::query($query))
                        return WPJOBPORTAL_SET_DEFAULT_ERROR;
                    else
                        return WPJOBPORTAL_SET_DEFAULT;
                    break;
                }else {
                    return WPJOBPORTAL_UNPUBLISH_DEFAULT_ERROR;
                }
                break;
        }
    }

    function checkCanMakeDefault($wpjobportal_id, $wpjobportal_tablename) {
        if (!is_numeric($wpjobportal_id))
            return false;
        switch ($wpjobportal_tablename) {
            case 'jobtypes':
            case 'jobstatus':
            case 'heighesteducation':
            case 'categories':
                $column = "isactive";
                break;
            default:
                $column = "status";
                break;
        }
        $query = "SELECT " . esc_sql($column) . " FROM `" . wpjobportal::$_db->prefix . "wj_portal_" . esc_sql($wpjobportal_tablename) . "` WHERE id=" . (int) ($wpjobportal_id);
        $res = wpjobportaldb::get_var($query);
        if ($res == 1)
            return true;
        else
            return false;
    }

    function getTranskey($wpjobportal_option_name){
         $query = "SELECT `option_value` FROM " . wpjobportal::$_wpprefixforuser . "options WHERE option_name = '".esc_sql($wpjobportal_option_name)."'";
         $wpjobportal_transactionKey = wpjobportaldb::get_var($query);
         return $wpjobportal_transactionKey;
    }

      function getDefaultValue($wpjobportal_table) {

        switch ($wpjobportal_table) {
            case "categories":
            case "jobtypes":
            case "jobstatus":
            case "shifts":
            case "heighesteducation":
            case "ages":
            case "careerlevels":
            case "experiences":
            case "salaryrange":
            case "salaryrangetypes":
            case "subcategories":
                $query = "SELECT id FROM `" . wpjobportal::$_db->prefix . "wj_portal_" . esc_sql($wpjobportal_table) . "` WHERE isdefault=1";

                $wpjobportal_default_id = wpjobportaldb::get_var($query);
                if ($wpjobportal_default_id)
                    return $wpjobportal_default_id;
                else {
                    $query = "SELECT min(id) AS id FROM `" . wpjobportal::$_db->prefix . "wj_portal_" . esc_sql($wpjobportal_table) . "`";

                    $min_id = wpjobportaldb::get_var($query);
                    return $min_id;
                }
            case "currencies":
                $query = "SELECT id FROM `" . wpjobportal::$_db->prefix . "wj_portal_" . esc_sql($wpjobportal_table) . "` WHERE `default`=1";

                $wpjobportal_default_id = wpjobportaldb::get_var($query);
                if ($wpjobportal_default_id)
                    return $wpjobportal_default_id;
                else {
                    $query = "SELECT min(id) AS id FROM `" . wpjobportal::$_db->prefix . "wj_portal_" . esc_sql($wpjobportal_table) . "`";

                    $min_id = wpjobportaldb::get_var($query);
                    return $min_id;
                }
                break;
        }
    }

    // function setOrderingUpForDefaultTable($wpjobportal_field_id, $wpjobportal_table) {
    //     if (is_numeric($wpjobportal_field_id) == false)
    //         return false;
    //     //DB class limitations
    //     if($wpjobportal_table == 'categories'){
    //         $parentid = wpjobportal::$_db->get_var("SELECT parentid FROM `".wpjobportal::$_db->prefix."wj_portal_categories` WHERE id = " . (int) ($wpjobportal_field_id));
    //         $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_" . esc_sql($wpjobportal_table) . "` AS f1, `" . wpjobportal::$_db->prefix . "wj_portal_" . esc_sql($wpjobportal_table) . "` AS f2
    //                     SET f1.ordering = f1.ordering + 1
    //                     WHERE f1.ordering = f2.ordering - 1 AND f1.parentid = " . (int) ($parentid)."
    //                     AND f2.id = " . (int) ($wpjobportal_field_id);
    //     }else{
    //         $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_" . esc_sql($wpjobportal_table) . "` AS f1, `" . wpjobportal::$_db->prefix . "wj_portal_" . esc_sql($wpjobportal_table) . "` AS f2
    //                     SET f1.ordering = f1.ordering + 1
    //                     WHERE f1.ordering = f2.ordering - 1
    //                     AND f2.id = " . (int) ($wpjobportal_field_id);
    //     }
    //     if (false == wpjobportaldb::query($query)) {
    //         return WPJOBPORTAL_ORDER_UP_ERROR;
    //     }
    //     $query = " UPDATE " . wpjobportal::$_db->prefix . "wj_portal_" . esc_sql($wpjobportal_table) . "
    //                 SET ordering = ordering - 1
    //                 WHERE id = " . (int) ($wpjobportal_field_id);

    //     if (false == wpjobportaldb::query($query)) {
    //         return WPJOBPORTAL_ORDER_UP_ERROR;
    //     }
    //     return WPJOBPORTAL_ORDER_UP;
    // }

    // function setOrderingDownForDefaultTable($wpjobportal_field_id, $wpjobportal_table) {
    //     if (is_numeric($wpjobportal_field_id) == false)
    //         return false;
    //     //DB class limitations
    //     if($wpjobportal_table == 'categories'){
    //         $parentid = wpjobportal::$_db->get_var("SELECT parentid FROM `".wpjobportal::$_db->prefix."wj_portal_categories` WHERE id = " . (int) ($wpjobportal_field_id));
    //         $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_" . esc_sql($wpjobportal_table) . "` AS f1, `" . wpjobportal::$_db->prefix . "wj_portal_" . esc_sql($wpjobportal_table) . "` AS f2
    //                     SET f1.ordering = f1.ordering - 1
    //                     WHERE f1.ordering = f2.ordering + 1 AND f1.parentid = " . (int) ($parentid)."
    //                     AND f2.id = " . (int) ($wpjobportal_field_id);
    //     }else{
    //         $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_" . esc_sql($wpjobportal_table) . "` AS f1, `" . wpjobportal::$_db->prefix . "wj_portal_" . esc_sql($wpjobportal_table) . "` AS f2
    //                     SET f1.ordering = f1.ordering - 1
    //                     WHERE f1.ordering = f2.ordering + 1
    //                     AND f2.id = " . (int) ($wpjobportal_field_id);
    //     }

    //     if (false == wpjobportaldb::query($query)) {
    //         return WPJOBPORTAL_ORDER_DOWN_ERROR;
    //     }
    //     $query = " UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_" . esc_sql($wpjobportal_table) . "`
    //                 SET ordering = ordering + 1
    //                 WHERE id = " . (int) ($wpjobportal_field_id);

    //     if (false == wpjobportaldb::query($query)) {
    //         return WPJOBPORTAL_ORDER_DOWN_ERROR;
    //     }
    //     return WPJOBPORTAL_ORDER_DOWN;
    // }

     function getMultiSelectEdit($wpjobportal_id, $for) {
        if (!is_numeric($wpjobportal_id))
            return false;
        $wpjobportal_config = wpjobportal::$_config->getConfigByFor('default');
       $query = "SELECT city.id AS id, CONCAT(city.name";
        switch ($wpjobportal_config['defaultaddressdisplaytype']) {
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
        $query .= " AS name ,city.latitude,city.longitude";
        switch ($for) {
            case 1:
                $query .= " FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobcities` AS mcity";
                break;
            case 2:
                $query .= " FROM `" . wpjobportal::$_db->prefix . "wj_portal_companycities` AS mcity";
                break;
            case 3:
                $query .= " FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobalertcities` AS mcity";
                break;
        }
        $query .=" JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city on city.id=mcity.cityid
                  JOIN `" . wpjobportal::$_db->prefix . "wj_portal_countries` AS country on city.countryid=country.id
                  LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_states` AS state on city.stateid=state.id";
        switch ($for) {
            case 1:
                $query .= " WHERE mcity.jobid = " . (int) ($wpjobportal_id)." AND country.enabled = 1 AND city.enabled = 1";
                break;
            case 2:
                $query .= " WHERE mcity.companyid = " . (int) ($wpjobportal_id)." AND country.enabled = 1 AND city.enabled = 1";
                break;
            case 3:
                $query .= " WHERE mcity.alertid = " . (int) ($wpjobportal_id)." AND country.enabled = 1 AND city.enabled = 1";
                break;
        }
        $wpjobportal_result = wpjobportaldb::get_results($query);
        $wpjobportal_json_array = wp_json_encode($wpjobportal_result);
        if (empty($wpjobportal_json_array))
            return null;
        else
            return $wpjobportal_json_array;
    }

    function getRequiredTravel() {
        $wpjobportal_requiredtravel = array();
        $wpjobportal_requiredtravel[] = (object) array('id' => 1, 'text' => esc_html(__('Not Required', 'wp-job-portal')));
        $wpjobportal_requiredtravel[] = (object) array('id' => 2, 'text' => esc_html(__('25 Percent', 'wp-job-portal')));
        $wpjobportal_requiredtravel[] = (object) array('id' => 3, 'text' => esc_html(__('50 Percent', 'wp-job-portal')));
        $wpjobportal_requiredtravel[] = (object) array('id' => 4, 'text' => esc_html(__('75 Percent', 'wp-job-portal')));
        $wpjobportal_requiredtravel[] = (object) array('id' => 5, 'text' => esc_html(__('100 Percent', 'wp-job-portal')));
        return $wpjobportal_requiredtravel;
    }

    function getRequiredTravelValue($wpjobportal_value) {
        switch ($wpjobportal_value) {
            case '1': return esc_html(__('Not Required', 'wp-job-portal')); break;
            case '2': return esc_html(__('25 Percent', 'wp-job-portal')); break;
            case '3': return esc_html(__('50 Percent', 'wp-job-portal')); break;
            case '4': return esc_html(__('75 Percent', 'wp-job-portal')); break;
            case '5': return esc_html(__('100 Percent', 'wp-job-portal')); break;
        }
    }

    /**
    * @param wp job portal Function
    * @param Log Action's
    */
    function getMiniMax() {
        $minimax = array();
        $minimax[] = (object) array('id' => '1', 'text' => esc_html(__('Minimum', 'wp-job-portal')));
        $minimax[] = (object) array('id' => '2', 'text' => esc_html(__('Maximum', 'wp-job-portal')));
        return $minimax;
    }
    /**
    * @param wp job portal Function's
    * @param Get Yes Or No
    */

    function getYesNo() {
        $wpjobportal_yesno = array();
        $wpjobportal_yesno[] = (object) array('id' => '1', 'text' => esc_html(__('Yes', 'wp-job-portal')));
        $wpjobportal_yesno[] = (object) array('id' => '0', 'text' => esc_html(__('No', 'wp-job-portal')));
        return $wpjobportal_yesno;
    }

    /**
    * @param wp job portal Function's
    * @param Get gender
    */

    function getGender() {
        $wpjobportal_gender = array();
        $wpjobportal_gender[] = (object) array('id' => '1', 'text' => esc_html(__('Male', 'wp-job-portal')));
        $wpjobportal_gender[] = (object) array('id' => '2', 'text' => esc_html(__('Female', 'wp-job-portal')));
        return $wpjobportal_gender;
    }

    /**
    * @param wp job portal Function's
    * @param Get Status
    */

    function getStatus() {
        $wpjobportal_status = array();
        $wpjobportal_status[] = (object) array('id' => '1', 'text' => esc_html(__('Published', 'wp-job-portal')));
        $wpjobportal_status[] = (object) array('id' => '0', 'text' => esc_html(__('Unpublished', 'wp-job-portal')));
        return $wpjobportal_status;
    }

    function getTotalExp(&$wpjobportal_resumeid){
        ///To get Total Experience From Resume Section's
        if(!is_numeric($wpjobportal_resumeid)){
            return '';
        }
        $wpjobportal_resume_id = $wpjobportal_resumeid;
        $query ="SELECT resume.employer_from_date AS fromdate,resume.employer_to_date AS todate,resume.employer_current_status
                AS status FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumeemployers` AS resume
                WHERE resumeid='".esc_sql($wpjobportal_resume_id)."' ORDER BY ID ASC
                ";
        wpjobportal::$_data[3] = wpjobportaldb::get_results($query);
        $wpjobportal_daystodate = wpjobportal::$_data[3];
        $wpjobportal_totalYear = 0;
        $wpjobportal_totalmonth = 0;
        $wpjobportal_totaldays = 0;
        $wpjobportal_html = '';
        for ($wpjobportal_i=0; $wpjobportal_i < count(wpjobportal::$_data[3]) ; $wpjobportal_i++) {
            $wpjobportal_status = $wpjobportal_daystodate[$wpjobportal_i]->status;
            $from = $wpjobportal_daystodate[$wpjobportal_i]->fromdate;
            $wpjobportal_to   = $wpjobportal_daystodate[$wpjobportal_i]->todate;
            $wpjobportal_diff = abs(strtotime($wpjobportal_to) - strtotime($from));
            $years = floor($wpjobportal_diff / (365*60*60*24));
            $wpjobportal_months = floor(($wpjobportal_diff - $years * 365*60*60*24) / (30*60*60*24));
            $wpjobportal_days = floor(($wpjobportal_diff - $years * 365*60*60*24 - $wpjobportal_months*30*60*60*24)/ (60*60*24));
            $wpjobportal_totalYear += $years;
            $wpjobportal_totalmonth += $wpjobportal_months;
            $wpjobportal_totaldays += $wpjobportal_days;
        }

        if(!empty($wpjobportal_daystodate)){

            if($wpjobportal_totalYear > 0){
                $wpjobportal_html.= $wpjobportal_totalYear;

            }
            if($wpjobportal_totalYear > 0 && $wpjobportal_totalmonth > 5){
                $wpjobportal_html.= '.5+'.' '.'Years';
            }else if($wpjobportal_totalYear>0 && $wpjobportal_totalmonth<=5) {
                $wpjobportal_html.= ' '.'+'.'Years';
            }else if ($wpjobportal_totalYear<=0 && $wpjobportal_totalmonth<5) {
                $wpjobportal_html.='Less than 1 year';
            }
            else{
                $wpjobportal_html.='Less than 1 year';
            }
        }
        else{
            $wpjobportal_html.="Fresh";
        }
        return $wpjobportal_html;
    }

    /**
    * @param wp job portal Function
    * @param Option's For Job Alert
    */

    function getOptionsForJobAlert() {
        $wpjobportal_status = array();
        $wpjobportal_status[] = (object) array('id' => '1', 'text' => esc_html(__('Subscribed', 'wp-job-portal')));
        $wpjobportal_status[] = (object) array('id' => '0', 'text' => esc_html(__('Unsubscribed', 'wp-job-portal')));
        return $wpjobportal_status;
    }

    function getQueStatus() {
        $wpjobportal_status = array();
        $wpjobportal_status[] = (object) array('id' => '1', 'text' => esc_html(__('Approved', 'wp-job-portal')));
        $wpjobportal_status[] = (object) array('id' => '-1', 'text' => esc_html(__('Rejected', 'wp-job-portal')));// rejected status is -1
        return $wpjobportal_status;
    }

    /**
    * @param wp job portal
    * Roles For Combo
    */

    function getListingStatus() {
        $wpjobportal_status = array();
        $wpjobportal_status[] = (object) array('id' => '1', 'text' => esc_html(__('Approved', 'wp-job-portal')));
        $wpjobportal_status[] = (object) array('id' => '-1', 'text' => esc_html(__('Rejected', 'wp-job-portal')));
        return $wpjobportal_status;
    }

    /**
    * @param wp job portal
    * Roles For Combo
    */

    function getRolesForCombo() {
        $wpjobportal_roles = array();
        $wpjobportal_empflag  = wpjobportal::$_config->getConfigurationByConfigName('disable_employer');
        $wpjobportal_showemployerlink  = wpjobportal::$_config->getConfigurationByConfigName('showemployerlink');
        if($wpjobportal_empflag == 1 && $wpjobportal_showemployerlink == 1){
            $wpjobportal_roles[] = (object) array('id' => '1', 'text' => esc_html(__('Employer', 'wp-job-portal')));
        }
        $wpjobportal_roles[] = (object) array('id' => '2', 'text' => esc_html(__('Job seeker', 'wp-job-portal')));
        return $wpjobportal_roles;
    }

    /**
    * @param wp job portal
    * Fields Type's
    */

    function getFeilds() {
        $wpjobportal_values = array();
        $wpjobportal_values[] = (object) array('id' => 'text', 'text' => esc_html(__('Text Field', 'wp-job-portal')));
        $wpjobportal_values[] = (object) array('id' => 'textarea', 'text' => esc_html(__('Text Area', 'wp-job-portal')));
        $wpjobportal_values[] = (object) array('id' => 'checkbox', 'text' => esc_html(__('Check Box', 'wp-job-portal')));
        $wpjobportal_values[] = (object) array('id' => 'date', 'text' => esc_html(__('Date', 'wp-job-portal')));
        $wpjobportal_values[] = (object) array('id' => 'select', 'text' => esc_html(__('Drop Down', 'wp-job-portal')));
        $wpjobportal_values[] = (object) array('id' => 'emailaddress', 'text' => esc_html(__('Email Address', 'wp-job-portal')));
        return $wpjobportal_values;
    }


   function getJobsStats_Widget($wpjobportal_classname, $title, $wpjobportal_showtitle, $wpjobportal_employers, $wpjobportal_jobseekers, $wpjobportal_jobs, $wpjobportal_companies, $wpjobportal_activejobs, $wpjobportal_resumes, $wpjobportal_todaystats) {
        //listModuleJobs
        $wpjobportal_curdate = gmdate('Y-m-d');
        $wpjobportal_data = array();
        if ($wpjobportal_employers == 1) {
            $query = "SELECT count(user.id) AS totalemployer
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` AS user
                WHERE user.roleid = 1";
            $wpjobportal_data['employer'] = wpjobportaldb::get_var($query);
        }
        if ($wpjobportal_jobseekers == 1) {
            $query = "SELECT count(user.id) AS totaljobseeker
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` AS user
                WHERE user.roleid = 2";
            $wpjobportal_data['jobseeker'] = wpjobportaldb::get_var($query);
        }
        if ($wpjobportal_jobs == 1) {
            $query = "SELECT count(job.id) AS totaljobs
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                WHERE job.status = 1 ";
            $wpjobportal_data['totaljobs'] = wpjobportaldb::get_var($query);
        }
        if ($wpjobportal_companies == 1) {
            $query = "SELECT count(company.id) AS totalcomapnies
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company
                WHERE company.status = 1 ";
            $wpjobportal_data['totalcompanies'] = wpjobportaldb::get_var($query);
        }
        if ($wpjobportal_activejobs == 1) {
            $query = "SELECT count(job.id) AS totalactivejobs
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                WHERE job.status = 1 AND DATE(job.startpublishing) <= " . (int) ($wpjobportal_curdate) . " AND DATE(job.stoppublishing) >= " . (int) ($wpjobportal_curdate);
            $wpjobportal_data['tatalactivejobs'] = wpjobportaldb::get_var($query);
        }
        if ($wpjobportal_resumes == 1) {
            $query = "SELECT count(resume.id) AS totalresume
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume
                WHERE resume.status = 1 ";
            $wpjobportal_data['totalresume'] = wpjobportaldb::get_var($query);
        }

        if ($wpjobportal_todaystats == 1) {
            if ($wpjobportal_employers == 1) {
                $query = "SELECT count(user.id) AS todayemployer
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` AS user
                    WHERE user.roleid = 1 AND DATE(user.created) = '" . esc_sql($wpjobportal_curdate)."'";
                $wpjobportal_data['todyemployer'] = wpjobportaldb::get_var($query);
            }
            if ($wpjobportal_jobseekers == 1) {
                $query = "SELECT count(user.id) AS todayjobseeker
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` AS user
                    WHERE user.roleid = 2 AND DATE(user.created) = '" . esc_sql($wpjobportal_curdate)."'";
                $wpjobportal_data['todyjobseeker'] = wpjobportaldb::get_var($query);
            }
            if ($wpjobportal_jobs == 1) {
                $query = "SELECT count(job.id) AS todayjobs
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                    WHERE job.status = 1 AND DATE(job.startpublishing) = '" . esc_sql($wpjobportal_curdate)."'";

                $wpjobportal_data['todayjobs'] = wpjobportaldb::get_var($query);
            }
            if ($wpjobportal_companies == 1) {
                $query = "SELECT count(company.id) AS todaycomapnies
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company
                    WHERE company.status = 1 AND DATE(company.created) = '" . esc_sql($wpjobportal_curdate)."'";

                $wpjobportal_data['todaycompanies'] = wpjobportaldb::get_var($query);
            }
            if ($wpjobportal_activejobs == 1) {
                $query = "SELECT count(job.id) AS todayactivejobs
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                    WHERE job.status = 1 AND DATE(job.startpublishing) = '" . esc_sql($wpjobportal_curdate)."'";
                $wpjobportal_data['todayactivejobs'] = wpjobportaldb::get_var($query);
            }
            if ($wpjobportal_resumes == 1) {
                $query = "SELECT count(resume.id) AS todayresume
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume
                    WHERE resume.status = 1 AND DATE(resume.created) = '" . esc_sql($wpjobportal_curdate)."'";
                $wpjobportal_data['todayresume'] = wpjobportaldb::get_var($query);
            }
        }
        return $wpjobportal_data;
    }

    function getExtension($wpjobportal_str) {
        if($wpjobportal_str == ''){
            return "";
        }
        $wpjobportal_i = strrpos($wpjobportal_str, ".");
        if (!$wpjobportal_i) {
            return "";
        }
        $l = wpjobportalphplib::wpJP_strlen($wpjobportal_str) - $wpjobportal_i;
        $wpjobportal_ext = wpjobportalphplib::wpJP_substr($wpjobportal_str, $wpjobportal_i + 1, $l);
        return $wpjobportal_ext;
    }

    function makeDir($wpjobportal_path) {
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
        $wp_filesystem = wpjobportal::wpjobportal_filesystem();

        if (!$wp_filesystem->exists($wpjobportal_path)) { // create directory
            $wp_filesystem->mkdir($wpjobportal_path, 0755);
            $ourFileName = $wpjobportal_path . '/index.html';
            $ourFileHandle = $wp_filesystem->put_contents($ourFileName,'');
            if($ourFileHandle !== false){
            }else{
                die("can't open file (".esc_html($ourFileName).")");
            }

        }
    }
    function WPJPcheck_field() {
		$autfored = WPJOBPORTALincluder::getJSModel('wpjobportal')->WPJPcheck_autfored();

        // Nothing to compare against, so nothing to encrypt: there is no key
        // on a site that was never licensed, and encrypting against no key
        // only ever produced a deprecation notice and a false.
        if (!is_string($autfored) || $autfored === '') {
            return false;
        }

        $wpjobportal_encrypted_field = openssl_encrypt('ed', 'AES-128-ECB', $autfored);
        $wpjobportal_option_name = 'wjportal_ed';
        $wpjobportal_stored_data = get_option($wpjobportal_option_name);

        if ($wpjobportal_stored_data) {
            if ($wpjobportal_stored_data['encrypted_field_name'] === $wpjobportal_encrypted_field) {
                return true; // Match found
            }
        }
        return false; // No match
    }

    /**
     * May the person looking at this page see resumes?
     *
     * Two settings decided this between them, in two different sections of the
     * settings screen, and they contradicted each other out of the box:
     *
     *   Resume Settings  "Allow Resume Search"        Allowed only to employers
     *   Content Access   "Visitor Can Search Resumes" Yes
     *
     * "Allow Resume Search" ran first and won, so an administrator could read
     * Content Access, see that visitors were allowed, and be wrong. The visitor
     * toggle governed one page - the resume search form - and not the results,
     * so even where it did apply the visitor reached a form that refused them,
     * or a categories page whose every link led to "You are not allowed".
     *
     * Now there is one answer, given here, and the visitor toggle is what it
     * always looked like: a sub-switch of "Allowed only to employers", asked
     * only when that is the mode in force.
     *
     * @param string $wpjobportal_visitor_setting The Content Access toggle that
     *                                            governs this particular page.
     * @return bool
     */
    public function resumesAreVisible($wpjobportal_visitor_setting = 'visitorview_emp_resumesearch') {
        $wpjobportal_allow = (int) wpjobportal::$_config->getConfigurationByConfigName('allow_search_resume');

        // Nobody, and nothing overrides it.
        if ($wpjobportal_allow === 0) {
            return false;
        }

        if ($this->wpjp_isadmin()) {
            return true;
        }

        // Everyone.
        if ($wpjobportal_allow === 1) {
            return true;
        }

        /*
         * Employers only - which is also where an unrecognised value lands,
         * deliberately: a value nobody expected should close the door rather
         * than open it.
         */
        $wpjobportal_empflag = wpjobportal::$_config->getConfigurationByConfigName('disable_employer');
        if (WPJOBPORTALincluder::getObjectClass('user')->isemployer() && $wpjobportal_empflag == 1) {
            return true;
        }

        return wpjobportal::$_config->getConfigurationByConfigName($wpjobportal_visitor_setting) == 1;
    }

    /**
     * A setting that is about to be written into JavaScript as a number.
     *
     * Settings reach the page by string concatenation, so a value that is not a
     * number does not produce a wrong number - it produces a syntax error, and
     * the whole inline script stops parsing. An empty "Maximum Number of Files
     * for Resume" emitted `var maxFiles = ;` and took the add-resume form down
     * with it: the file picker stopped responding, and the tag fields went with
     * it, because everything after that point in the block never ran. The field
     * is free text in the settings screen with nothing stopping an admin from
     * clearing it.
     *
     * The fallback is used for anything that is not a positive whole number, so
     * the page keeps working and the worst case is a limit the admin did not
     * choose rather than a form nobody can use.
     *
     * @param string $wpjobportal_configname Setting to read.
     * @param int    $wpjobportal_fallback   Used when it is unusable.
     * @return int Always safe to concatenate into JavaScript.
     */
    public function configAsJsNumber($wpjobportal_configname, $wpjobportal_fallback) {
        $wpjobportal_value = wpjobportal::$_config->getConfigurationByConfigName($wpjobportal_configname);

        if (is_string($wpjobportal_value)) {
            $wpjobportal_value = trim($wpjobportal_value);
        }

        if (!is_numeric($wpjobportal_value)) {
            return (int) $wpjobportal_fallback;
        }

        $wpjobportal_value = (int) $wpjobportal_value;

        // Zero and negatives are as unusable as a blank: a limit of none means
        // the picker refuses every file without saying why.
        if ($wpjobportal_value < 1) {
            return (int) $wpjobportal_fallback;
        }

        return $wpjobportal_value;
    }

    /**
     * Read a salary the way somebody would write one.
     *
     * The salary fields are plain text with the currency symbol printed beside
     * them, so people type what they see: "$50,000", "50 000", "45,000.50".
     * The columns are floats, which means an unparsed string was stored as 0 -
     * a job advertised at nothing - and on a server with strict SQL mode the
     * insert would fail outright.
     *
     * Returns null rather than 0 for anything that cannot be read as a number.
     * A salary nobody can parse is a salary nobody stated, and recording it as
     * zero would be recording something the employer never said.
     *
     * @param mixed $value What was typed, or what is stored.
     * @return float|null
     */
    public function normaliseSalary($value) {
        if (is_float($value) || is_int($value)) {
            return (float) $value;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        // A letter touching a digit changes what the number means - "50k" is
        // fifty thousand, not fifty - and there is no honest way to guess which
        // multiplier was intended. A letter standing apart from the digits is a
        // currency name, "USD 50,000", and can be dropped safely.
        if (preg_match('/\d\p{L}|\p{L}\d/u', $value)) {
            return null;
        }

        // Currency symbols, spaces, names, and anything else that is not part
        // of the number. What is left is digits and the two characters that
        // might be separating them.
        $value = preg_replace('/[^0-9.,-]/', '', $value);
        if ($value === '' || $value === '-') {
            return null;
        }

        $dot = strrpos($value, '.');
        $comma = strrpos($value, ',');

        if ($dot !== false && $comma !== false) {
            // Both present: whichever comes last is the decimal mark, and the
            // other is grouping. "1.234,56" and "1,234.56" both mean the same.
            $decimal = $dot > $comma ? '.' : ',';
            $grouping = $decimal === '.' ? ',' : '.';
            $value = str_replace($grouping, '', $value);
            $value = str_replace($decimal, '.', $value);
        } elseif ($comma !== false) {
            // One kind of separator, so its meaning has to be guessed. Two
            // digits after a single comma is a decimal; anything else is
            // grouping, which is the far more common way to write a salary.
            $value = preg_match('/^-?\d+,\d{2}$/', $value)
                ? str_replace(',', '.', $value)
                : str_replace(',', '', $value);
        }

        // A stray second dot, or a dash that was not a minus sign - "3000-6000"
        // typed into a single field - leaves something that is not a number,
        // and guessing which half was meant would be inventing a salary.
        return is_numeric($value) ? (float) $value : null;
    }

    // Whole figures read better without a trailing ".00"; only a salary that
    // actually has pence keeps them.
    function formatSalaryFigure($value) {
        if (fmod((float) $value, 1) !== 0.00) {
            return wpjobportalphplib::wpJP_number_format((float) $value, 2);
        }

        return wpjobportalphplib::wpJP_number_format((float) $value);
    }

    // Right alignment is the deliberate choice; anything else, including a
    // config value that was never saved, puts the symbol in front - which is
    // what most currencies do, and better than printing no salary at all.
    function alignSalaryCurrency($figure, $currencysymbol, $currency_align) {
        if ($currency_align == 2) {
            return $figure . ' ' . $currencysymbol;
        }

        return $currencysymbol . ' ' . $figure;
    }

    function getSalaryRangeView($type, $min, $wpjobportal_max, $currency=""){
        $wpjobportal_salary = '';
        $currencysymbol =  isset($currency) ? $currency : wpjobportal::$_config->getConfigValue('job_currency');
        $currency_align = wpjobportal::$_config->getConfigValue('currency_align');

        // Read as numbers before anything arithmetic is done to them. fmod()
        // on a string is a fatal error in PHP 8, and this is reached from job
        // listings, emails, RSS, widgets and PDFs - so one unparseable salary
        // took out every one of them.
        $min = $this->normaliseSalary($min);
        $wpjobportal_max = $this->normaliseSalary($wpjobportal_max);

        // A job posted without a salary stores nothing, or a zero, in these
        // columns, and formatting that anyway printed "0 $" on the card - a
        // stated salary of nothing. A figure that is absent or zero is treated
        // as no figure at all, and the caller is given nothing to print.
        $has_min = ($min !== null && abs($min) > 0);
        $has_max = ($wpjobportal_max !== null && abs($wpjobportal_max) > 0);

        $min = $has_min ? $this->formatSalaryFigure($min) : '';
        $wpjobportal_max = $has_max ? $this->formatSalaryFigure($wpjobportal_max) : '';

        if($type == 1){
            $wpjobportal_salary = esc_html(__("Negotiable",'wp-job-portal'));
        }else if($type == 2 && $has_min){
            $wpjobportal_salary = $this->alignSalaryCurrency($min, $currencysymbol, $currency_align);
        }else if($type == 3 && ($has_min || $has_max)){
            // One end of a range on its own is still worth saying; a dash with
            // nothing after it is not.
            if($has_min && $has_max){
                $wpjobportal_figure = $min . ' - ' . $wpjobportal_max;
            }else{
                $wpjobportal_figure = $has_min ? $min : $wpjobportal_max;
            }
            $wpjobportal_salary = $this->alignSalaryCurrency($wpjobportal_figure, $currencysymbol, $currency_align);
        }

        // Always a string: old add-on templates pass this to wp_kses(), which
        // PHP 8 warns about when it gets null.
        return $wpjobportal_salary;
    }

    function getYearMonth($wpjobportal_args=array()){
            $wpjobportal_previousTimeStamp = gmdate('Y-m-d',strtotime($wpjobportal_args['originalDate']));
            $lastTimeStamp = gmdate('Y-m-d',strtotime($wpjobportal_args['currentDate']));
            $wpjobportal_diff = abs(strtotime($wpjobportal_previousTimeStamp) - strtotime($lastTimeStamp));
            $years = floor($wpjobportal_diff / (365*60*60*24));
            $wpjobportal_months = floor(($wpjobportal_diff - $years * 365*60*60*24) / (30*60*60*24));
            $wpjobportal_days = floor(($wpjobportal_diff - $years * 365*60*60*24 - $wpjobportal_months*30*60*60*24)/ (60*60*24));
            return array('month'=>$wpjobportal_months,'days'=>$wpjobportal_days,'years'=>$years);
    }

    function getLocationForView($cityname, $wpjobportal_statename, $wpjobportal_countryname) {
        $location = $cityname;
        $wpjobportal_defaultaddressdisplaytype = wpjobportal::$_configuration['defaultaddressdisplaytype'];
        switch ($wpjobportal_defaultaddressdisplaytype) {
            case 'csc':
                if ($wpjobportal_statename)
                    $location .= ', ' . wpjobportal::wpjobportal_getVariableValue($wpjobportal_statename);
                if ($wpjobportal_countryname)
                    $location .= ', ' . wpjobportal::wpjobportal_getVariableValue($wpjobportal_countryname);
                break;
            case 'cs':
                if ($wpjobportal_statename)
                    $location .= ', ' . wpjobportal::wpjobportal_getVariableValue($wpjobportal_statename);
                break;
            case 'cc':
                if ($wpjobportal_countryname)
                    $location .= ', ' . wpjobportal::wpjobportal_getVariableValue($wpjobportal_countryname);
                break;
        }
        return $location;
    }

    function getUidByObjectId($wpjobportal_actionfor, $wpjobportal_id) {
        if (!is_numeric($wpjobportal_id))
            return false;
        switch ($wpjobportal_actionfor) {
            case'company':
                $wpjobportal_table = 'wj_portal_companies';
                break;
            case'job':
                $wpjobportal_table = 'wj_portal_jobs';
                break;
            case'resume':
                $wpjobportal_table = 'wj_portal_resume';
                break;
        }
        $query = "SELECT uid FROM `" . wpjobportal::$_db->prefix . $wpjobportal_table . "`WHERE id = " . (int) ($wpjobportal_id);
        $wpjobportal_result = wpjobportaldb::get_var($query);

        return $wpjobportal_result;
    }

    public function makeFilterdOrEditedTagsToReturn($wpjobportal_tags) {
        if (empty($wpjobportal_tags))
            return null;
        $wpjobportal_temparray = wpjobportalphplib::wpJP_explode(',', $wpjobportal_tags);
        $wpjobportal_array = array();
        for ($wpjobportal_i = 0; $wpjobportal_i < count($wpjobportal_temparray); $wpjobportal_i++) {
            $wpjobportal_array[] = array('id' => $wpjobportal_temparray[$wpjobportal_i], 'name' => $wpjobportal_temparray[$wpjobportal_i]);
        }
        return wp_json_encode($wpjobportal_array);
    }

    function saveNewInWPJOBPORTAL($wpjobportal_data) {
        if (empty($wpjobportal_data))
            return false;

        $wpjobportal_empflag  = wpjobportal::$_config->getConfigurationByConfigName('disable_employer');
        $wpjobportal_allow_reg_as_emp  = wpjobportal::$_config->getConfigurationByConfigName('showemployerlink');
        if($wpjobportal_empflag == 0 || $wpjobportal_allow_reg_as_emp == 0){
            $wpjobportal_data['roleid'] = 2;
        }
        // if($wpjobportal_allow_reg_as_emp != 1){
        //     $wpjobportal_data['roleid'] = 2;
        // }

        if(isset($wpjobportal_data['socialmedia']) && !empty($wpjobportal_data['socialid'])){
            $wpjobportal_data['uid'] = "";
            $wpjobportal_data['socialmedia'] = $wpjobportal_data['socialmedia'];
        } else {
            $currentuser = get_userdata(get_current_user_id());
            $wpjobportal_data['socialid'] = '';
            $wpjobportal_data['socialmedia'] = '';
            $wpjobportal_data['first_name'] = $currentuser->first_name;
            $wpjobportal_data['last_name'] = $currentuser->last_name;
            $wpjobportal_data['emailaddress'] = $currentuser->user_email;
            $wpjobportal_data['uid'] = $currentuser->ID;
        }
     
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('users');
        $wpjobportal_data['status'] = 1; // all user autoapprove when registered as WP Job Portal users
        $wpjobportal_data['created'] = gmdate('Y-m-d H:i:s');
        $wpjobportal_data = wpjobportal::wpjobportal_sanitizeData($wpjobportal_data);
        if (!$wpjobportal_row->bind($wpjobportal_data)) {
            return WPJOBPORTAL_SAVE_ERROR;
        }
        if (!$wpjobportal_row->check()) {
            return WPJOBPORTAL_SAVE_ERROR;
        }
        if (!$wpjobportal_row->store()) {
            return WPJOBPORTAL_SAVE_ERROR;
        }

	if (WPJOBPORTALrequest::hasCookie('first_name')){
		wpjobportalphplib::wpJP_setcookie('first_name' , '' , time() + 0 , COOKIEPATH);
		if ( SITECOOKIEPATH != COOKIEPATH ){
		wpjobportalphplib::wpJP_setcookie('first_name' , '' , time() + 0 , SITECOOKIEPATH);
		}
	}
	if (WPJOBPORTALrequest::hasCookie('last_name')){
		wpjobportalphplib::wpJP_setcookie('last_name' , '', time() + 0 , COOKIEPATH);
		if ( SITECOOKIEPATH != COOKIEPATH ){
		wpjobportalphplib::wpJP_setcookie('last_name' , '', time() + 0 , SITECOOKIEPATH);
		}
	}
	if (WPJOBPORTALrequest::hasCookie('email')){
		wpjobportalphplib::wpJP_setcookie('email' , '', time() + 0 , COOKIEPATH);
		if ( SITECOOKIEPATH != COOKIEPATH ){
		wpjobportalphplib::wpJP_setcookie('email' , '', time() + 0 , SITECOOKIEPATH);
		}
	}
        return WPJOBPORTAL_SAVED;
    }

    function parseID($wpjobportal_id){
        if ($wpjobportal_id === '' || $wpjobportal_id === null) {
            return '';
        }
        if (is_numeric($wpjobportal_id)) {
            return absint($wpjobportal_id);
        }
        // URL aliases use the numeric id as the final dash-separated segment.
        $wpjobportal_id_parts = wpjobportalphplib::wpJP_explode('-', (string) $wpjobportal_id);
        $wpjobportal_id = end($wpjobportal_id_parts);
        return absint($wpjobportal_id);
    }

    function sendEmail($recevierEmail, $wpjobportal_subject, $body, $wpjobportal_senderEmail, $wpjobportal_senderName, $attachments = '') {
        if (!$wpjobportal_senderName)
            $wpjobportal_senderName = wpjobportal::$_configuration['title'];
        $headers = 'From: ' . $wpjobportal_senderName . ' <' . $wpjobportal_senderEmail . '>' . "\r\n";
	//2.6.0 is removed it        
//	add_filter('wp_mail_content_type', function(){return "text/html";});
        $body = wpjobportalphplib::wpJP_preg_replace('/\r?\n|\r/', '<br/>', $body);
        $body = wpjobportalphplib::wpJP_str_replace(array("\r\n", "\r", "\n"), "<br/>", $body);
        $body = nl2br($body);
        $wpjobportal_result = WPJOBPORTAL_Notification_Service::send(
            $recevierEmail,
            $wpjobportal_subject,
            $body,
            $headers,
            $attachments,
            'common'
        );
        return $wpjobportal_result;
    }

    function jsMakeRedirectURL($wpjobportal_module, $wpjobportal_layout, $for, $cpfor = null){
        if(empty($wpjobportal_module) AND empty($wpjobportal_layout) AND empty($for))
            return null;

        $finalurl = '';
        if( $for == 1 ){ // login links
            $wpjobportal_jsthisurl = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>$wpjobportal_module, 'wpjobportallt'=>$wpjobportal_layout));
            $wpjobportal_jsthisurl = wpjobportalphplib::wpJP_safe_encoding($wpjobportal_jsthisurl);
            $finalurl = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'wpjobportal', 'wpjobportallt'=>'login', 'wpjobportalredirecturl'=>$wpjobportal_jsthisurl));
        }

        return $finalurl;
    }

    function getCitiesForFilter($cities){
        if(empty($cities))
            return NULL;


        $cities = wpjobportalphplib::wpJP_explode(',', $cities);
        $wpjobportal_result = array();

        $wpjobportal_defaultaddressdisplaytype = wpjobportal::$_config->getConfigurationByConfigName('defaultaddressdisplaytype');

        foreach ($cities as $city) {
            if(!is_numeric($city)){
                continue;
            }
            $query = "SELECT city.id AS id, CONCAT(city.name";
            switch ($wpjobportal_defaultaddressdisplaytype) {
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
            $query .= " AS name ";
            $query .= " FROM `".wpjobportal::$_db->prefix."wj_portal_cities` AS city
                        JOIN `".wpjobportal::$_db->prefix."wj_portal_countries` AS country on city.countryid=country.id
                        LEFT JOIN `".wpjobportal::$_db->prefix."wj_portal_states` AS state on city.stateid=state.id
                        WHERE country.enabled = 1 AND city.enabled = 1";
            $query .= " AND city.id =" . (int) ($city);


            $wpjobportal_result[] = wpjobportaldb::get_row($query);
        }
        if(!empty($wpjobportal_result)){
            return wp_json_encode($wpjobportal_result);
        }else{
            return NULL;
        }
    }
    function getMessagekey(){
        $wpjobportal_key = 'common';if(wpjobportal::$_common->wpjp_isadmin()){$wpjobportal_key = 'admin_'.$wpjobportal_key;}return $wpjobportal_key;
    }

    function stripslashesFull($wpjobportal_input){// testing this function/.
        if($wpjobportal_input == ''){
            return $wpjobportal_input;
        }
        if (is_array($wpjobportal_input)) {
            $wpjobportal_input = array_map(array($this,'stripslashesFull'), $wpjobportal_input);
        } elseif (is_object($wpjobportal_input)) {
            $wpjobportal_vars = get_object_vars($wpjobportal_input);
            foreach ($wpjobportal_vars as $wpjobportal_k=>$v) {
                $wpjobportal_input->{$wpjobportal_k} = stripslashesFull($v);
            }
        } else {
            $wpjobportal_input = wpjobportalphplib::wpJP_stripslashes($wpjobportal_input);
        }
        return $wpjobportal_input;
    }

    /**
     * Somebody who already belongs somewhere else in the product.
     *
     * A recruiting team member holds no portal role - membership is what
     * grants them anything - so every "is this one of our users?" test
     * answers no, and the job-seeker area drew the only conclusions it knew:
     * either invite them to pick a role, or treat them as a passing visitor.
     * A recruiter opening Add Resume was handed the visitor form and could
     * have filed a resume of their own.
     *
     * An add-on that owns such a person answers this filter with
     *
     *     array('message' => ..., 'link' => ..., 'linktext' => ...)
     *
     * and the area refuses them by name instead of guessing.
     *
     * @return array|false
     */
    function userBoundToAnotherArea($wpjobportal_area = '') {
        if (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
            return false;
        }

        // Which area is being entered, so an add-on can answer for the ones
        // that are not its people's - a recruiter belongs on the employer
        // panel and must not be turned away from it.
        $wpjobportal_bound = apply_filters(
            'wpjobportal_user_bound_to_other_area',
            false,
            get_current_user_id(),
            sanitize_key($wpjobportal_area)
        );

        return (is_array($wpjobportal_bound) && !empty($wpjobportal_bound['message'])) ? $wpjobportal_bound : false;
    }

    function validateEmployerArea(){
        // first handle visitor case to show appropriate message to visitor
        $cuser = WPJOBPORTALincluder::getObjectClass('user');
        if($cuser->isguest()){
            $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme');
            $wpjobportal_layout = WPJOBPORTALrequest::getLayout('wpjobportallt',null,'');
            $wpjobportal_link = WPJOBPORTALincluder::getJSModel('common')->jsMakeRedirectURL($wpjobportal_module, $wpjobportal_layout, 1);
            $wpjobportal_linktext = esc_html(__('Login','wp-job-portal'));
            throw new Exception( wp_kses(WPJOBPORTALLayout::setMessageFor(1 , $wpjobportal_link , $wpjobportal_linktext,1),WPJOBPORTAL_ALLOWED_TAGS) );
        }
        $wpjobportal_employerAreaEnabled = wpjobportal::$_config->getConfigValue('disable_employer');
        if(!$wpjobportal_employerAreaEnabled){
            throw new Exception( wp_kses(WPJOBPORTALLayout::setMessageFor(5,null,null,1),WPJOBPORTAL_ALLOWED_TAGS) );
        }
        if(!$cuser->isemployer()){
            if($cuser->isjobseeker()){
               throw new Exception( wp_kses(WPJOBPORTALLayout::setMessageFor(2,null,null,1),WPJOBPORTAL_ALLOWED_TAGS) );
            }
            if(!$cuser->isWPJOBPortalUser()){
                $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'common', 'wpjobportallt'=>'newinwpjobportal'));
                $wpjobportal_linktext = esc_html(__('Select Role','wp-job-portal'));
                throw new Exception( wp_kses(WPJOBPORTALLayout::setMessageFor(9 , $wpjobportal_link , $wpjobportal_linktext,1),WPJOBPORTAL_ALLOWED_TAGS) );
            }
        }
    }

    function getMessagesForAddMore($wpjobportal_module){
        $wpjobportal_linktext = esc_html(__('You are not allowed to add more than one','wp-job-portal').'&nbsp;'.wpjobportal::wpjobportal_getVariableValue($wpjobportal_module).' !'. __('Contact Administrator', 'wp-job-portal'));
        wpjobportal::$_error_flag = true;
        throw new Exception(wp_kses(WPJOBPORTALLayout::setMessageFor(16,'',$wpjobportal_module,1),WPJOBPORTAL_ALLOWED_TAGS));
    }

    function getBuyErrMsg(){
        $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'package', 'wpjobportallt'=>'packages'));
        $wpjobportal_linktext = esc_html(__('Buy Package', 'wp-job-portal'));
        wpjobportal::$_error_flag = true;
        wpjobportal::$_error_flag_message_for=15;
        throw new Exception(wp_kses(WPJOBPORTALLayout::setMessageFor(15,$wpjobportal_link,$wpjobportal_linktext,1),WPJOBPORTAL_ALLOWED_TAGS));
    }


    function getCalendarDateFormat(){
        static $wpjobportal_js_scriptdateformat;
        if ($wpjobportal_js_scriptdateformat) {
            return $wpjobportal_js_scriptdateformat;
        }
        if (wpjobportal::$_configuration['date_format'] == 'm/d/Y' || wpjobportal::$_configuration['date_format'] == 'd/m/y' || wpjobportal::$_configuration['date_format'] == 'm/d/y' || wpjobportal::$_configuration['date_format'] == 'd/m/Y') {
            $wpjobportal_dash = '/';
        } else {
            $wpjobportal_dash = '-';
        }
        $wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
        $wpjobportal_firstdash = wpjobportalphplib::wpJP_strpos($wpjobportal_dateformat, $wpjobportal_dash, 0);
        $wpjobportal_firstvalue = wpjobportalphplib::wpJP_substr($wpjobportal_dateformat, 0, $wpjobportal_firstdash);
        $wpjobportal_firstdash = $wpjobportal_firstdash + 1;
        $wpjobportal_seconddash = wpjobportalphplib::wpJP_strpos($wpjobportal_dateformat, $wpjobportal_dash, $wpjobportal_firstdash);
        $wpjobportal_secondvalue = wpjobportalphplib::wpJP_substr($wpjobportal_dateformat, $wpjobportal_firstdash, $wpjobportal_seconddash - $wpjobportal_firstdash);
        $wpjobportal_seconddash = $wpjobportal_seconddash + 1;
        $wpjobportal_thirdvalue = wpjobportalphplib::wpJP_substr($wpjobportal_dateformat, $wpjobportal_seconddash, wpjobportalphplib::wpJP_strlen($wpjobportal_dateformat) - $wpjobportal_seconddash);
        $wpjobportal_js_dateformat = '%' . $wpjobportal_firstvalue . $wpjobportal_dash . '%' . $wpjobportal_secondvalue . $wpjobportal_dash . '%' . $wpjobportal_thirdvalue;
        $wpjobportal_js_scriptdateformat = $wpjobportal_firstvalue . $wpjobportal_dash . $wpjobportal_secondvalue . $wpjobportal_dash . $wpjobportal_thirdvalue;
        $wpjobportal_js_scriptdateformat = wpjobportalphplib::wpJP_str_replace('Y', 'yy', $wpjobportal_js_scriptdateformat);
        return $wpjobportal_js_scriptdateformat;
    }

    function getProductDesc($wpjobportal_id){
        $wpjobportal_name = '';
        if(empty($wpjobportal_id)){
            return $wpjobportal_name;
        }
        $parse = wpjobportalphplib::wpJP_explode('-', $wpjobportal_id);
        if(empty($parse[0])){
            return $wpjobportal_name;
        }
        /*
         * An id shaped "<setting>-<record>" is what this reads. Anything else
         * is not one of ours and has no description to give - an order id, a
         * post id, whatever else happens to arrive in the request as `id`.
         * Reading $parse[1] before knowing that was a warning on every
         * WooCommerce admin order screen, which edits an order at
         * ...&action=edit&id=123 and so hands a number with nothing to split.
         */
        if(!isset($parse[1]) || $parse[1] === ''){
            return $wpjobportal_name;
        }
        $wpjobportal_moduleid = $parse[1];
        $wpjobportal_configname = $parse[0];
        if(is_array($parse) && !empty($parse)){
            if(!empty($wpjobportal_id)){
                switch ($wpjobportal_configname) {
                    case 'job_currency_department_perlisting':
                        //print_r(WPJOBPORTALincluder::getJSModel('departments')->getDepartmentById($wpjobportal_moduleid));
                        break;
                    case 'company_price_perlisting':
                        break;
                    case 'company_feature_price_perlisting':
                        break;
                    case 'job_currency_price_perlisting':
                        break;
                    case 'jobs_feature_price_perlisting':
                        break;
                    case 'job_resume_price_perlisting':
                        break;
                    case 'job_featureresume_price_perlisting':
                        break;
                    case 'job_jobalert_price_perlisting':
                        break;
                    case 'job_resumesavesearch_price_perlisting':
                        $wpjobportal_name = WPJOBPORTALincluder::getJSModel('resumesearch')->getResumeSearchName($wpjobportal_moduleid);
                        # Resume Search Payment
                        break;
                   default:
                        # code...
                        break;
                }
                return $wpjobportal_name;
            }
        }
    }

    function listModuleJobsStats($wpjobportal_classname, $title, $wpjobportal_showtitle, $wpjobportal_employers, $wpjobportal_jobseekers, $wpjobportal_jobs, $wpjobportal_companies, $wpjobportal_activejobs, $wpjobportal_resumes, $wpjobportal_todaystats,$wpjobportal_data){
        $wpjobportal_my_html = '
            <div id="wpjobportals_mod_wrapper" class="wjportal-stats-mod"> ';
		$wpjobportal_my_html .= '<div id="wpjobportals-mod-heading" class="wjportal-mod-heading"> ' . esc_html(__('Stats', 'wp-job-portal')) . '</div>';

        $wpjobportal_my_html .='
                <div id="wpjobportals-data-wrapper" class="' . $wpjobportal_classname . ' wjportal-stats">';
        $wpjobportal_curdate = gmdate('Y-m-d');
        if ($wpjobportal_employers == 1) {
            $wpjobportal_my_html .='<div class="wpjobportals-value wjportal-stats-data">' . esc_html(__('Employer', 'wp-job-portal')) . ' <span class="wjportal-stats-num">(' . $wpjobportal_data['employer'] . ')</span></div>';
        }
        if ($wpjobportal_jobseekers == 1) {
            $wpjobportal_my_html .='<div class="wpjobportals-value wjportal-stats-data">' . esc_html(__('Job seeker', 'wp-job-portal')) . ' <span class="wjportal-stats-num">(' . $wpjobportal_data['jobseeker'] . ')</span></div>';
        }
        if ($wpjobportal_jobs == 1) {
            $wpjobportal_my_html .='<div class="wpjobportals-value wjportal-stats-data">' . esc_html(__('Jobs', 'wp-job-portal')) . ' <span class="wjportal-stats-num">(' . $wpjobportal_data['totaljobs'] . ')</span></div>';
        }
        if ($wpjobportal_companies == 1) {
            $wpjobportal_my_html .='<div class="wpjobportals-value wjportal-stats-data">' . esc_html(__('Companies', 'wp-job-portal')) . ' <span class="wjportal-stats-num">(' . $wpjobportal_data['totalcompanies'] . ')</span></div>';
        }
        if ($wpjobportal_activejobs == 1) {
            $wpjobportal_my_html .='<div class="wpjobportals-value wjportal-stats-data">' . esc_html(__('Active Jobs', 'wp-job-portal')) . ' <span class="wjportal-stats-num">(' . $wpjobportal_data['tatalactivejobs'] . ')</span></div>';
        }
        if ($wpjobportal_resumes == 1) {
            $wpjobportal_my_html .='<div class="wpjobportals-value wjportal-stats-data">' . esc_html(__('Resume', 'wp-job-portal')) . ' <span class="wjportal-stats-num">(' . $wpjobportal_data['totalresume'] . ')</span></div>';
        }
        if ($wpjobportal_todaystats == 1) {
			$wpjobportal_my_html .= '</div> <div id="wpjobportals-mod-heading" class="wjportal-mod-heading"> ' . esc_html(__('Today Stats', 'wp-job-portal')) . '</div>';
            $wpjobportal_my_html .='
                <div id="wpjobportals-data-wrapper" class="' . $wpjobportal_classname . ' wjportal-stats">';
            if ($wpjobportal_employers == 1) {
                $wpjobportal_my_html .='<div class="wpjobportals-value wjportal-stats-data">' . esc_html(__('Employer', 'wp-job-portal')) . ' <span class="wjportal-stats-num">(' . $wpjobportal_data['todyemployer'] . ')</span></div>';
            }
            if ($wpjobportal_jobseekers == 1) {
                $wpjobportal_my_html .='<div class="wpjobportals-value wjportal-stats-data">' . esc_html(__('Job seeker', 'wp-job-portal')) . ' <span class="wjportal-stats-num">(' . $wpjobportal_data['todyjobseeker'] . ')</span></div>';
            }
            if ($wpjobportal_jobs == 1) {
                $wpjobportal_my_html .='<div class="wpjobportals-value wjportal-stats-data">' . esc_html(__('Jobs', 'wp-job-portal')) . ' <span class="wjportal-stats-num">(' . $wpjobportal_data['todayjobs'] . ')</span></div>';
            }
            if ($wpjobportal_companies == 1) {
                $wpjobportal_my_html .='<div class="wpjobportals-value wjportal-stats-data">' . esc_html(__('Companies', 'wp-job-portal')) . ' <span class="wjportal-stats-num">(' . $wpjobportal_data['todaycompanies'] . ')</span></div>';
            }
            if ($wpjobportal_activejobs == 1) {
                $wpjobportal_my_html .='<div class="wpjobportals-value wjportal-stats-data">' . esc_html(__('Active Jobs', 'wp-job-portal')) . ' <span class="wjportal-stats-num">(' . $wpjobportal_data['todayactivejobs'] . ')</span></div>';
            }
            if ($wpjobportal_resumes == 1) {
                $wpjobportal_my_html .='<div class="wpjobportals-value wjportal-stats-data">' . esc_html(__('Resume', 'wp-job-portal')) . ' <span class="wjportal-stats-num">(' . $wpjobportal_data['todayresume'] . ')</span></div>';
            }
            $wpjobportal_my_html .= '</div>';
        }

        $wpjobportal_my_html .= '</div>';
        return $wpjobportal_my_html;
    }

    function getSearchFormDataOnlySort($wpjobportal_jstlay){
        if($wpjobportal_jstlay == 'activitylog'){
            $wpjobportal_val1 = 4;
        }else{
            $wpjobportal_val1 = 6;
        }
        $wpjobportal_jsjp_search_array = array();
        $wpjobportal_jsjp_search_array['sorton'] = WPJOBPORTALrequest::getVar('sorton', 'post', $wpjobportal_val1);
        $wpjobportal_jsjp_search_array['sortby'] = WPJOBPORTALrequest::getVar('sortby', 'post', 2);
        $wpjobportal_jsjp_search_array['search_from_myapply_myjobs'] = 1;
        return $wpjobportal_jsjp_search_array;
    }

    function getCookiesSavedOnlySortandOrder(){
        $wpjobportal_jsjp_search_array = array();
        $wpjp_search_cookie_data = '';
        if(WPJOBPORTALrequest::hasCookie('jsjp_jobportal_search_data')){
            $wpjp_search_cookie_data = WPJOBPORTALrequest::cookie('jsjp_jobportal_search_data');
            $wpjp_search_cookie_data = wpjobportalphplib::wpJP_safe_decoding($wpjp_search_cookie_data);
            $wpjp_search_cookie_data = json_decode( $wpjp_search_cookie_data , true );
        }
        if($wpjp_search_cookie_data != '' && isset($wpjp_search_cookie_data['search_from_myapply_myjobs']) && $wpjp_search_cookie_data['search_from_myapply_myjobs'] == 1){
            $wpjobportal_jsjp_search_array['sorton'] = $wpjp_search_cookie_data['sorton'];
            $wpjobportal_jsjp_search_array['sortby'] = $wpjp_search_cookie_data['sortby'];
        }
        return $wpjobportal_jsjp_search_array;
    }

    function setSearchVariableOnlySortandOrder($wpjobportal_jsjp_search_array,$wpjobportal_jstlay){
        if($wpjobportal_jstlay == 'activitylog'){
            $wpjobportal_val1 = 4;
        }else{
            $wpjobportal_val1 = 6;
        }
        wpjobportal::$_search['jobs']['sorton'] = isset($wpjobportal_jsjp_search_array['sorton']) ? $wpjobportal_jsjp_search_array['sorton'] : $wpjobportal_val1;
        wpjobportal::$_search['jobs']['sortby'] = isset($wpjobportal_jsjp_search_array['sortby']) ? $wpjobportal_jsjp_search_array['sortby'] : 2;
    }

    function getServerProtocol(){
        static $wpjobportal_protocol;
        if ($wpjobportal_protocol) {
            return $wpjobportal_protocol;
        }
        $wpjobportal_protocol = is_ssl() ? "https://" : "http://";
        return $wpjobportal_protocol;
    }

    function wpjp_isadmin(){
        if (current_user_can('manage_options')) {
            return true;
        } else {
            return false;
        }
    }

    function getSearchFormDataAdmin(){
        $wpjobportal_jsjp_search_array = array();
        $wpjobportal_jsjp_search_array['departmentname'] = WPJOBPORTALrequest::getVar('departmentname','','');
        $wpjobportal_jsjp_search_array['companyname'] = WPJOBPORTALrequest::getVar('companyname','','');
        $wpjobportal_jsjp_search_array['status'] = WPJOBPORTALrequest::getVar('status','','');

        $wpjobportal_jsjp_search_array['title'] = WPJOBPORTALrequest::getVar('title','','');
        $wpjobportal_jsjp_search_array['name'] = WPJOBPORTALrequest::getVar('name','','');

        if($wpjobportal_jsjp_search_array['title'] == ''){// to handle job applied resume case
            $wpjobportal_jsjp_search_array['title'] = WPJOBPORTALrequest::getVar('application_title','','');
        }
        if($wpjobportal_jsjp_search_array['name'] == ''){// to handle job applied resume case
            $wpjobportal_jsjp_search_array['name'] = WPJOBPORTALrequest::getVar('applicantname','','');
        }


        $wpjobportal_jsjp_search_array['ustatus'] = WPJOBPORTALrequest::getVar('ustatus','','');
        $wpjobportal_jsjp_search_array['vstatus'] = WPJOBPORTALrequest::getVar('vstatus','','');
        $wpjobportal_jsjp_search_array['required'] = WPJOBPORTALrequest::getVar('required','','');

        $wpjobportal_jsjp_search_array['nationality'] = WPJOBPORTALrequest::getVar('nationality','','');
        $wpjobportal_jsjp_search_array['jobcategory'] = WPJOBPORTALrequest::getVar('jobcategory','','');
        $wpjobportal_jsjp_search_array['gender'] = WPJOBPORTALrequest::getVar('gender','','');
        $wpjobportal_jsjp_search_array['jobtype'] = WPJOBPORTALrequest::getVar('jobtype','','');
        $wpjobportal_jsjp_search_array['currency'] = WPJOBPORTALrequest::getVar('currency','','');
        $wpjobportal_jsjp_search_array['jobsalaryrange'] = WPJOBPORTALrequest::getVar('jobsalaryrange','','');
        // The salary a job actually carries: a type, and then either one
        // figure or a band. Same parameter names the job search uses, so a
        // filter means the same thing wherever it is applied.
        $wpjobportal_jsjp_search_array['salarytype'] = WPJOBPORTALrequest::getVar('salarytype','','');
        $wpjobportal_jsjp_search_array['salaryfixed'] = WPJOBPORTALrequest::getVar('salaryfixed','','');
        $wpjobportal_jsjp_search_array['salarymin'] = WPJOBPORTALrequest::getVar('salarymin','','');
        $wpjobportal_jsjp_search_array['salarymax'] = WPJOBPORTALrequest::getVar('salarymax','','');
        $wpjobportal_jsjp_search_array['heighestfinisheducation'] = WPJOBPORTALrequest::getVar('heighestfinisheducation','','');

        $wpjobportal_jsjp_search_array['coverlettertitle'] = WPJOBPORTALrequest::getVar('coverlettertitle','','');

        $wpjobportal_jsjp_search_array['username'] = WPJOBPORTALrequest::getVar('username','','');
        //$wpjobportal_jsjp_search_array['status'] = WPJOBPORTALrequest::getVar('status','','');
        $wpjobportal_jsjp_search_array['currencyid'] = WPJOBPORTALrequest::getVar('currencyid','','');
        $wpjobportal_jsjp_search_array['type'] = WPJOBPORTALrequest::getVar('type','','');


        $wpjobportal_jsjp_search_array['foldername'] = WPJOBPORTALrequest::getVar('foldername','','');

        $wpjobportal_jsjp_search_array['searchcompany'] = WPJOBPORTALrequest::getVar('searchcompany','','');
        $wpjobportal_jsjp_search_array['searchcompcategory'] = WPJOBPORTALrequest::getVar('searchcompcategory','','');

        //$wpjobportal_jsjp_search_array['searchcompany'] = WPJOBPORTALrequest::getVar('searchcompany','','');
        $wpjobportal_jsjp_search_array['searchjobcategory'] = WPJOBPORTALrequest::getVar('searchjobcategory','','');
        $wpjobportal_jsjp_search_array['datestart'] = WPJOBPORTALrequest::getVar('datestart','','');
        $wpjobportal_jsjp_search_array['dateend'] = WPJOBPORTALrequest::getVar('dateend','','');
        $wpjobportal_jsjp_search_array['featured'] = WPJOBPORTALrequest::getVar('featured','','');

        $wpjobportal_jsjp_search_array['wpjobportal_city'] = WPJOBPORTALrequest::getVar('wpjobportal-city','','');
        $wpjobportal_jsjp_search_array['wpjobportal_company'] = WPJOBPORTALrequest::getVar('wpjobportal-company','','');

        $wpjobportal_jsjp_search_array['jobseeker'] = WPJOBPORTALrequest::getVar('jobseeker','','');
        $wpjobportal_jsjp_search_array['employer'] = WPJOBPORTALrequest::getVar('employer','','');
        $wpjobportal_jsjp_search_array['read'] = WPJOBPORTALrequest::getVar('read','','');
        $wpjobportal_jsjp_search_array['company'] = WPJOBPORTALrequest::getVar('company','','');
        $wpjobportal_jsjp_search_array['searchjobtitle'] = WPJOBPORTALrequest::getVar('searchjobtitle','','');
        $wpjobportal_jsjp_search_array['searchresumetitle'] = WPJOBPORTALrequest::getVar('searchresumetitle','','');
        $wpjobportal_jsjp_search_array['resumetitle'] = WPJOBPORTALrequest::getVar('resumetitle','','');
        $wpjobportal_jsjp_search_array['jobtitle'] = WPJOBPORTALrequest::getVar('jobtitle','','');
        $wpjobportal_jsjp_search_array['subject'] = WPJOBPORTALrequest::getVar('subject','','');
        $wpjobportal_jsjp_search_array['searchsubject'] = WPJOBPORTALrequest::getVar('searchsubject','','');
        $wpjobportal_jsjp_search_array['conflicted'] = WPJOBPORTALrequest::getVar('conflicted','','');

        //$wpjobportal_jsjp_search_array['title'] = WPJOBPORTALrequest::getVar('title','','');
        $wpjobportal_jsjp_search_array['email'] = WPJOBPORTALrequest::getVar('email','','');
        $wpjobportal_jsjp_search_array['location'] = WPJOBPORTALrequest::getVar('location','','');
        $wpjobportal_jsjp_search_array['category'] = WPJOBPORTALrequest::getVar('category','','');
        $wpjobportal_jsjp_search_array['alertstatus'] = WPJOBPORTALrequest::getVar('alertstatus','','');
        //$wpjobportal_jsjp_search_array['status'] = WPJOBPORTALrequest::getVar('status','','');

        $wpjobportal_jsjp_search_array['searchname'] = WPJOBPORTALrequest::getVar('searchname','','');

        $wpjobportal_jsjp_search_array['sorton'] = WPJOBPORTALrequest::getVar('sorton','',3);
        $wpjobportal_jsjp_search_array['sortby'] = WPJOBPORTALrequest::getVar('sortby','',2);

        // job applied resume layout
        // $wpjobportal_jsjp_search_array['application_title'] = WPJOBPORTALrequest::getVar('application_title','','');
        // $wpjobportal_jsjp_search_array['applicantname'] = WPJOBPORTALrequest::getVar('applicantname','','');


        $wpjobportal_jsjp_search_array['search_from_admin_listing'] = 1;
        return $wpjobportal_jsjp_search_array;
    }

    function getCookiesSavedAdmin(){
        $wpjobportal_jsjp_search_array = array();
        $wpjp_search_cookie_data = '';
        if(WPJOBPORTALrequest::hasCookie('jsjp_jobportal_search_data')){
            $wpjp_search_cookie_data = WPJOBPORTALrequest::cookie('jsjp_jobportal_search_data');
            $wpjp_search_cookie_data = wpjobportalphplib::wpJP_safe_decoding($wpjp_search_cookie_data);
            $wpjp_search_cookie_data = json_decode( $wpjp_search_cookie_data, true );
        }
        if($wpjp_search_cookie_data != '' && isset($wpjp_search_cookie_data['search_from_admin_listing']) && $wpjp_search_cookie_data['search_from_admin_listing'] == 1){
            $wpjobportal_jsjp_search_array['departmentname'] = $wpjp_search_cookie_data['departmentname'];
            $wpjobportal_jsjp_search_array['companyname'] = $wpjp_search_cookie_data['companyname'];
            $wpjobportal_jsjp_search_array['status'] = $wpjp_search_cookie_data['status'];

            $wpjobportal_jsjp_search_array['title'] = $wpjp_search_cookie_data['title'];
            $wpjobportal_jsjp_search_array['ustatus'] = $wpjp_search_cookie_data['ustatus'];
            $wpjobportal_jsjp_search_array['vstatus'] = $wpjp_search_cookie_data['vstatus'];
            $wpjobportal_jsjp_search_array['required'] = $wpjp_search_cookie_data['required'];

            //$wpjobportal_jsjp_search_array['title'] = $wpjp_search_cookie_data['title'];
            $wpjobportal_jsjp_search_array['name'] = $wpjp_search_cookie_data['name'];
            $wpjobportal_jsjp_search_array['nationality'] = $wpjp_search_cookie_data['nationality'];
            $wpjobportal_jsjp_search_array['jobcategory'] = $wpjp_search_cookie_data['jobcategory'];
            $wpjobportal_jsjp_search_array['gender'] = $wpjp_search_cookie_data['gender'];
            $wpjobportal_jsjp_search_array['jobtype'] = $wpjp_search_cookie_data['jobtype'];
            $wpjobportal_jsjp_search_array['currency'] = $wpjp_search_cookie_data['currency'];
            $wpjobportal_jsjp_search_array['jobsalaryrange'] = $wpjp_search_cookie_data['jobsalaryrange'];
            foreach (array('salarytype', 'salaryfixed', 'salarymin', 'salarymax') as $wpjobportal_salarykey) {
                $wpjobportal_jsjp_search_array[$wpjobportal_salarykey] = isset($wpjp_search_cookie_data[$wpjobportal_salarykey])
                    ? $wpjp_search_cookie_data[$wpjobportal_salarykey] : '';
            }
            $wpjobportal_jsjp_search_array['heighestfinisheducation'] = $wpjp_search_cookie_data['heighestfinisheducation'];

            $wpjobportal_jsjp_search_array['coverlettertitle'] = $wpjp_search_cookie_data['coverlettertitle'];
            //$wpjobportal_jsjp_search_array['status'] = $wpjp_search_cookie_data['status'];

            $wpjobportal_jsjp_search_array['username'] = $wpjp_search_cookie_data['username'];
            //$wpjobportal_jsjp_search_array['status'] = $wpjp_search_cookie_data['status'];
            $wpjobportal_jsjp_search_array['currencyid'] = $wpjp_search_cookie_data['currencyid'];
            $wpjobportal_jsjp_search_array['type'] = $wpjp_search_cookie_data['type'];


            $wpjobportal_jsjp_search_array['foldername'] = $wpjp_search_cookie_data['foldername'];

            $wpjobportal_jsjp_search_array['searchcompany'] = $wpjp_search_cookie_data['searchcompany'];
            $wpjobportal_jsjp_search_array['searchcompcategory'] = $wpjp_search_cookie_data['searchcompcategory'];
            $wpjobportal_jsjp_search_array['wpjobportal_city'] = $wpjp_search_cookie_data['wpjobportal_city'];

            //$wpjobportal_jsjp_search_array['searchcompany'] = $wpjp_search_cookie_data['searchcompany'];
            $wpjobportal_jsjp_search_array['searchjobcategory'] = $wpjp_search_cookie_data['searchjobcategory'];
            $wpjobportal_jsjp_search_array['datestart'] = $wpjp_search_cookie_data['datestart'];
            $wpjobportal_jsjp_search_array['dateend'] = $wpjp_search_cookie_data['dateend'];
            $wpjobportal_jsjp_search_array['featured'] = $wpjp_search_cookie_data['featured'];

            $wpjobportal_jsjp_search_array['wpjobportal_company'] = $wpjp_search_cookie_data['wpjobportal_company'];

            $wpjobportal_jsjp_search_array['jobseeker'] = $wpjp_search_cookie_data['jobseeker'];
            $wpjobportal_jsjp_search_array['employer'] = $wpjp_search_cookie_data['employer'];
            $wpjobportal_jsjp_search_array['read'] = $wpjp_search_cookie_data['read'];
            $wpjobportal_jsjp_search_array['company'] = $wpjp_search_cookie_data['company'];
            $wpjobportal_jsjp_search_array['jobtitle'] = $wpjp_search_cookie_data['jobtitle'];
            $wpjobportal_jsjp_search_array['resumetitle'] = $wpjp_search_cookie_data['resumetitle'];
            $wpjobportal_jsjp_search_array['subject'] = $wpjp_search_cookie_data['subject'];
            $wpjobportal_jsjp_search_array['conflicted'] = $wpjp_search_cookie_data['conflicted'];

            $wpjobportal_jsjp_search_array['searchjobtitle'] = $wpjp_search_cookie_data['searchjobtitle'];
            $wpjobportal_jsjp_search_array['searchresumetitle'] = $wpjp_search_cookie_data['searchresumetitle'];
            $wpjobportal_jsjp_search_array['searchsubject'] = $wpjp_search_cookie_data['searchsubject'];

            //$wpjobportal_jsjp_search_array['title'] = $wpjp_search_cookie_data['title'];
            $wpjobportal_jsjp_search_array['email'] = $wpjp_search_cookie_data['email'];
            $wpjobportal_jsjp_search_array['location'] = $wpjp_search_cookie_data['location'];
            $wpjobportal_jsjp_search_array['category'] = $wpjp_search_cookie_data['category'];
            $wpjobportal_jsjp_search_array['alertstatus'] = $wpjp_search_cookie_data['alertstatus'];
            //$wpjobportal_jsjp_search_array['status'] = $wpjp_search_cookie_data['status'];

            $wpjobportal_jsjp_search_array['searchname'] = $wpjp_search_cookie_data['searchname'];


            // $wpjobportal_jsjp_search_array['application_title'] = $wpjp_search_cookie_data['application_title'];
            // $wpjobportal_jsjp_search_array['applicantname'] = $wpjp_search_cookie_data['applicantname'];


            $wpjobportal_jsjp_search_array['sorton'] = $wpjp_search_cookie_data['sorton'];
            $wpjobportal_jsjp_search_array['sortby'] = $wpjp_search_cookie_data['sortby'];
        }
        return $wpjobportal_jsjp_search_array;
    }

    function setSearchVariableAdmin($wpjobportal_jsjp_search_array){
        wpjobportal::$_search['search_filter']['departmentname'] = isset($wpjobportal_jsjp_search_array['departmentname']) ? $wpjobportal_jsjp_search_array['departmentname'] : null;
        wpjobportal::$_search['search_filter']['companyname'] = isset($wpjobportal_jsjp_search_array['companyname']) ? $wpjobportal_jsjp_search_array['companyname'] : null;
        wpjobportal::$_search['search_filter']['status'] = isset($wpjobportal_jsjp_search_array['status']) ? $wpjobportal_jsjp_search_array['status'] : null;

        wpjobportal::$_search['search_filter']['title'] = isset($wpjobportal_jsjp_search_array['title']) ? $wpjobportal_jsjp_search_array['title'] : null;
        wpjobportal::$_search['search_filter']['ustatus'] = isset($wpjobportal_jsjp_search_array['ustatus']) ? $wpjobportal_jsjp_search_array['ustatus'] : null;
        wpjobportal::$_search['search_filter']['vstatus'] = isset($wpjobportal_jsjp_search_array['vstatus']) ? $wpjobportal_jsjp_search_array['vstatus'] : null;
        wpjobportal::$_search['search_filter']['required'] = isset($wpjobportal_jsjp_search_array['required']) ? $wpjobportal_jsjp_search_array['required'] : null;

        //wpjobportal::$_search['search_filter']['title'] = isset($wpjobportal_jsjp_search_array['title']) ? $wpjobportal_jsjp_search_array['title'] : null;
        wpjobportal::$_search['search_filter']['name'] = isset($wpjobportal_jsjp_search_array['name']) ? $wpjobportal_jsjp_search_array['name'] : null;
        wpjobportal::$_search['search_filter']['nationality'] = isset($wpjobportal_jsjp_search_array['nationality']) ? $wpjobportal_jsjp_search_array['nationality'] : null;
        wpjobportal::$_search['search_filter']['jobcategory'] = isset($wpjobportal_jsjp_search_array['jobcategory']) ? $wpjobportal_jsjp_search_array['jobcategory'] : null;
        wpjobportal::$_search['search_filter']['gender'] = isset($wpjobportal_jsjp_search_array['gender']) ? $wpjobportal_jsjp_search_array['gender'] : null;
        wpjobportal::$_search['search_filter']['jobtype'] = isset($wpjobportal_jsjp_search_array['jobtype']) ? $wpjobportal_jsjp_search_array['jobtype'] : null;
        wpjobportal::$_search['search_filter']['currency'] = isset($wpjobportal_jsjp_search_array['currency']) ? $wpjobportal_jsjp_search_array['currency'] : null;
        wpjobportal::$_search['search_filter']['jobsalaryrange'] = isset($wpjobportal_jsjp_search_array['jobsalaryrange']) ? $wpjobportal_jsjp_search_array['jobsalaryrange'] : null;
        foreach (array('salarytype', 'salaryfixed', 'salarymin', 'salarymax') as $wpjobportal_salarykey) {
            wpjobportal::$_search['search_filter'][$wpjobportal_salarykey] = isset($wpjobportal_jsjp_search_array[$wpjobportal_salarykey])
                ? $wpjobportal_jsjp_search_array[$wpjobportal_salarykey] : null;
        }
        wpjobportal::$_search['search_filter']['heighestfinisheducation'] = isset($wpjobportal_jsjp_search_array['heighestfinisheducation']) ? $wpjobportal_jsjp_search_array['heighestfinisheducation'] : null;

        wpjobportal::$_search['search_filter']['coverlettertitle'] = isset($wpjobportal_jsjp_search_array['coverlettertitle']) ? $wpjobportal_jsjp_search_array['coverlettertitle'] : null;
        //wpjobportal::$_search['search_filter']['status'] = isset($wpjobportal_jsjp_search_array['status']) ? $wpjobportal_jsjp_search_array['status'] : null;

        wpjobportal::$_search['search_filter']['username'] = isset($wpjobportal_jsjp_search_array['username']) ? $wpjobportal_jsjp_search_array['username'] : null;
        // wpjobportal::$_search['search_filter']['status'] = isset($wpjobportal_jsjp_search_array['status']) ? $wpjobportal_jsjp_search_array['status'] : null;
        wpjobportal::$_search['search_filter']['currencyid'] = isset($wpjobportal_jsjp_search_array['currencyid']) ? $wpjobportal_jsjp_search_array['currencyid'] : null;
        wpjobportal::$_search['search_filter']['type'] = isset($wpjobportal_jsjp_search_array['type']) ? $wpjobportal_jsjp_search_array['type'] : null;

        wpjobportal::$_search['search_filter']['name'] = isset($wpjobportal_jsjp_search_array['name']) ? $wpjobportal_jsjp_search_array['name'] : null;
        wpjobportal::$_search['search_filter']['foldername'] = isset($wpjobportal_jsjp_search_array['foldername']) ? $wpjobportal_jsjp_search_array['foldername'] : null;

        wpjobportal::$_search['search_filter']['searchcompany'] = isset($wpjobportal_jsjp_search_array['searchcompany']) ? $wpjobportal_jsjp_search_array['searchcompany'] : null;
        wpjobportal::$_search['search_filter']['searchcompcategory'] = isset($wpjobportal_jsjp_search_array['searchcompcategory']) ? $wpjobportal_jsjp_search_array['searchcompcategory'] : null;
        wpjobportal::$_search['search_filter']['wpjobportal_city'] = isset($wpjobportal_jsjp_search_array['wpjobportal_city']) ? $wpjobportal_jsjp_search_array['wpjobportal_city'] : null;

        //wpjobportal::$_search['search_filter']['searchcompany'] = isset($wpjobportal_jsjp_search_array['searchcompany']) ? $wpjobportal_jsjp_search_array['searchcompany'] : null;
        wpjobportal::$_search['search_filter']['searchjobcategory'] = isset($wpjobportal_jsjp_search_array['searchjobcategory']) ? $wpjobportal_jsjp_search_array['searchjobcategory'] : null;
        wpjobportal::$_search['search_filter']['datestart'] = isset($wpjobportal_jsjp_search_array['datestart']) ? $wpjobportal_jsjp_search_array['datestart'] : null;
        wpjobportal::$_search['search_filter']['dateend'] = isset($wpjobportal_jsjp_search_array['dateend']) ? $wpjobportal_jsjp_search_array['dateend'] : null;
        wpjobportal::$_search['search_filter']['featured'] = isset($wpjobportal_jsjp_search_array['featured']) ? $wpjobportal_jsjp_search_array['featured'] : null;

        wpjobportal::$_search['search_filter']['wpjobportal_company'] = isset($wpjobportal_jsjp_search_array['wpjobportal_company']) ? $wpjobportal_jsjp_search_array['wpjobportal_company'] : null;

        wpjobportal::$_search['search_filter']['jobseeker'] = isset($wpjobportal_jsjp_search_array['jobseeker']) ? $wpjobportal_jsjp_search_array['jobseeker'] : null;
        wpjobportal::$_search['search_filter']['employer'] = isset($wpjobportal_jsjp_search_array['employer']) ? $wpjobportal_jsjp_search_array['employer'] : null;
        wpjobportal::$_search['search_filter']['read'] = isset($wpjobportal_jsjp_search_array['read']) ? $wpjobportal_jsjp_search_array['read'] : null;
        wpjobportal::$_search['search_filter']['company'] = isset($wpjobportal_jsjp_search_array['company']) ? $wpjobportal_jsjp_search_array['company'] : null;
        wpjobportal::$_search['search_filter']['jobtitle'] = isset($wpjobportal_jsjp_search_array['jobtitle']) ? $wpjobportal_jsjp_search_array['jobtitle'] : null;
        wpjobportal::$_search['search_filter']['resumetitle'] = isset($wpjobportal_jsjp_search_array['resumetitle']) ? $wpjobportal_jsjp_search_array['resumetitle'] : null;
        wpjobportal::$_search['search_filter']['subject'] = isset($wpjobportal_jsjp_search_array['subject']) ? $wpjobportal_jsjp_search_array['subject'] : null;
        wpjobportal::$_search['search_filter']['conflicted'] = isset($wpjobportal_jsjp_search_array['conflicted']) ? $wpjobportal_jsjp_search_array['conflicted'] : null;

        wpjobportal::$_search['search_filter']['searchjobtitle'] = isset($wpjobportal_jsjp_search_array['searchjobtitle']) ? $wpjobportal_jsjp_search_array['searchjobtitle'] : null;
        wpjobportal::$_search['search_filter']['searchresumetitle'] = isset($wpjobportal_jsjp_search_array['searchresumetitle']) ? $wpjobportal_jsjp_search_array['searchresumetitle'] : null;
        wpjobportal::$_search['search_filter']['searchsubject'] = isset($wpjobportal_jsjp_search_array['searchsubject']) ? $wpjobportal_jsjp_search_array['searchsubject'] : null;

        //wpjobportal::$_search['search_filter']['title'] = isset($wpjobportal_jsjp_search_array['title']) ? $wpjobportal_jsjp_search_array['title'] : null;
        wpjobportal::$_search['search_filter']['email'] = isset($wpjobportal_jsjp_search_array['email']) ? $wpjobportal_jsjp_search_array['email'] : null;
        wpjobportal::$_search['search_filter']['location'] = isset($wpjobportal_jsjp_search_array['location']) ? $wpjobportal_jsjp_search_array['location'] : null;
        wpjobportal::$_search['search_filter']['category'] = isset($wpjobportal_jsjp_search_array['category']) ? $wpjobportal_jsjp_search_array['category'] : null;
        wpjobportal::$_search['search_filter']['alertstatus'] = isset($wpjobportal_jsjp_search_array['alertstatus']) ? $wpjobportal_jsjp_search_array['alertstatus'] : null;
        //wpjobportal::$_search['search_filter']['status'] = isset($wpjobportal_jsjp_search_array['status']) ? $wpjobportal_jsjp_search_array['status'] : null;

        wpjobportal::$_search['search_filter']['searchname'] = isset($wpjobportal_jsjp_search_array['searchname']) ? $wpjobportal_jsjp_search_array['searchname'] : null;

        wpjobportal::$_search['search_filter']['application_title'] = isset($wpjobportal_jsjp_search_array['application_title']) ? $wpjobportal_jsjp_search_array['application_title'] : null;
        wpjobportal::$_search['search_filter']['applicantname'] = isset($wpjobportal_jsjp_search_array['applicantname']) ? $wpjobportal_jsjp_search_array['applicantname'] : null;

        wpjobportal::$_search['search_filter']['sorton'] = isset($wpjobportal_jsjp_search_array['sorton']) ? $wpjobportal_jsjp_search_array['sorton'] : null;
        wpjobportal::$_search['search_filter']['sortby'] = isset($wpjobportal_jsjp_search_array['sortby']) ? $wpjobportal_jsjp_search_array['sortby'] : null;
    }

    function getDefaultImage($wpjobportal_role){
        // job seeker deafult image
        if(WPJOBPORTALincluder::getJSModel('common')->isElegantDesignEnabled()){
            $wpjobportal_url = wpjobportal_elegant_design_url();
        } else {
            $wpjobportal_url = WPJOBPORTAL_PLUGIN_URL;
        }
        $wpjobportal_img_path = esc_url($wpjobportal_url) . "includes/images/users.png";

        // employer default image
        if($wpjobportal_role == 'employer'){
            $wpjobportal_img_path = esc_url(WPJOBPORTAL_PLUGIN_URL) . "includes/images/default_logo.png";
        }

        // admin set image
        if(!empty(wpjobportal::$_configuration['default_image'])){
            $wpjobportal_data_directory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
            $wpjobportal_wpdir = wp_upload_dir();
            $wpjobportal_img_path = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/default_image/' . wpjobportal::$_configuration['default_image'];
        }

        return $wpjobportal_img_path;
    }

    function addWPSEOHooks($wpjobportal_module_name,$file_name){
        $this->module_name = $wpjobportal_module_name;
        $this->file_name = $file_name;
        add_filter( 'pre_get_document_title', array($this, 'WPJobPortalGetDocumentTitle'),99);
        //add_action("wp_head", array($this, "WPJobPortalMetaTags"));
    }

    function WPJobPortalGetDocumentTitle($title) {
        $wpjobportal_module = $this->module_name;
        $wpjobportal_layout = $this->file_name;

        // Several slugs share one layout where only one template exists, so the
        // layout alone cannot name the page: /jobs and /newest-jobs both render
        // the jobs layout, and both were titled "Jobs" while the second headed
        // itself "Newest Jobs".
        //
        // Read from the URL rather than from the routed arguments. This filter
        // runs while the document head is being written, and the request's
        // sanitized arguments are not populated at that point — checked, rather
        // than assumed, after the routed value arrived empty here every time.
        $wpjobportal_slug = self::requestedSlug();
        if ($wpjobportal_slug !== '') {
            $wpjobportal_by_slug = $this->getPageTitleBySlug($wpjobportal_slug);
            if ($wpjobportal_by_slug !== '') {
                return $wpjobportal_by_slug;
            }
        }
        // making sure our layout is being opened & making sure proper values are set
        if ($wpjobportal_module != '' && $wpjobportal_layout != '') {
            // get page title for current page
            $page_title = $this->getWPJobPortalDocumentTitleByPage($wpjobportal_module, $wpjobportal_layout);
            if($page_title != ''){
                $title = $page_title;
            }
        }
        return $title;
    }

    function getWPJobPortalDocumentTitleByPage($wpjobportal_module='',$wpjobportal_layout=''){
        if($wpjobportal_module=='' && $wpjobportal_layout==''){
            $wpjobportal_layout = WPJOBPORTALrequest::getVar('wpjobportallt');
            $wpjobportal_module = WPJOBPORTALrequest::getVar('wpjobportalme');
        }
        $title = '';
        if ($wpjobportal_module != '' && $wpjobportal_layout != '') {
            $title = $this->getPageTitleByFileName($wpjobportal_layout,$wpjobportal_module);
        }
        return $title;
    }

    /**
     * The last path segment of the current request.
     *
     * A detail page ends in a record's own slug, which matches no row, so the
     * caller falls back to the layout. Only a segment that names a portal page
     * resolves to anything.
     *
     * @return string
     */
    private static function requestedSlug() {
        $wpjobportal_uri = WPJOBPORTALrequest::requestUri();
        $wpjobportal_path = (string) wp_parse_url($wpjobportal_uri, PHP_URL_PATH);
        $wpjobportal_path = trim($wpjobportal_path, '/');

        if ($wpjobportal_path === '') {
            return '';
        }

        $wpjobportal_parts = explode('/', $wpjobportal_path);

        return sanitize_title(end($wpjobportal_parts));
    }

    /**
     * The page title recorded against a URL slug.
     *
     * getPageTitleByFileName() keys on the layout, which is right until two
     * slugs share one. /jobs and /newest-jobs both render the jobs layout, so
     * the layout cannot tell them apart and both were titled "Jobs".
     *
     * @param string $wpjobportal_slug Slug as it appears in the URL.
     * @return string Empty when the slug has no title of its own.
     */
    function getPageTitleBySlug($wpjobportal_slug) {
        $wpjobportal_slug = sanitize_title($wpjobportal_slug);
        if ($wpjobportal_slug === '') {
            return '';
        }

        $wpjobportal_query = "SELECT pagetitle FROM `" . wpjobportal::$_db->prefix . "wj_portal_slug` WHERE slug = '" . esc_sql($wpjobportal_slug) . "' OR defaultslug = '" . esc_sql($wpjobportal_slug) . "'";
        $wpjobportal_title = (string) wpjobportal::$_db->get_var($wpjobportal_query);

        if (trim($wpjobportal_title) === '') {
            return '';
        }

        return $this->replaceMatches($wpjobportal_title, array(
            '[separator]' => '|',
            '[sitename]' => get_bloginfo('name', 'display'),
        ));
    }

    function getPageTitleByFileName($wpjobportal_layout,$wpjobportal_module) {
        $page_title_val = '';
        $where_query = '';
        if($wpjobportal_layout == 'controlpanel' || $wpjobportal_layout == 'mystats'){
            $where_query = " AND modulename = '".esc_sql($wpjobportal_module)."'";
        }

        $query = "SELECT pagetitle FROM `".wpjobportal::$_db->prefix."wj_portal_slug` WHERE filename = '".esc_sql($wpjobportal_layout)."' ".$where_query;
        $page_title_val = wpjobportal::$_db->get_var($query);
        if($page_title_val != ''){
            // using switch to handle different layouts seprately
            switch ($wpjobportal_layout) {
                case 'viewcompany':
                    $wpjobportal_companyid = isset(wpjobportal::$_data[0]->id) ? wpjobportal::$_data[0]->id : '';
                    if($wpjobportal_companyid == ''){
                        $wpjobportal_companyid = WPJOBPORTALrequest::getVar('wpjobportalid');
                        $wpjobportal_companyid = wpjobportal::$_common->parseID($wpjobportal_companyid);
                    }
                    if(is_numeric($wpjobportal_companyid) && $wpjobportal_companyid > 0){
                        // below code is only here until the interface is not built properly
                        $wpjobportal_company_title_options = get_option('wpjobportal_company_document_title_settings');
                        if(!empty($wpjobportal_company_title_options)){
                            $page_title_val = $wpjobportal_company_title_options;
                        }

                        $page_title_val = WPJOBPORTALincluder::getJSModel('company')->makeCompanySeoDocumentTitle($page_title_val , $wpjobportal_companyid);
                    }
                break;
                case 'viewjob':
                    $wpjobportal_jobid = isset(wpjobportal::$_data[0]->id) ? wpjobportal::$_data[0]->id : '';
                    if($wpjobportal_jobid == ''){
                        $wpjobportal_jobid = WPJOBPORTALrequest::getVar('wpjobportalid');
                        $wpjobportal_jobid = wpjobportal::$_common->parseID($wpjobportal_jobid);
                    }
                    if(is_numeric($wpjobportal_jobid) && $wpjobportal_jobid > 0){
                        // below code is only here until the interface is not built properly
                        $wpjobportal_job_title_options = get_option('wpjobportal_job_document_title_settings');
                        if(!empty($wpjobportal_job_title_options)){
                            $page_title_val = $wpjobportal_job_title_options;
                        }

                        $page_title_val = WPJOBPORTALincluder::getJSModel('job')->makeJobSeoDocumentTitle($page_title_val , $wpjobportal_jobid);
                    }
                break;
                case 'viewresume':
                    $wpjobportal_resumeid = (!empty(wpjobportal::$_data[0]['personal_section']) && isset(wpjobportal::$_data[0]['personal_section']->id)) ? wpjobportal::$_data[0]['personal_section']->id : '';
                    if($wpjobportal_resumeid == ''){
                        $wpjobportal_resumeid = WPJOBPORTALrequest::getVar('wpjobportalid');
                        $wpjobportal_resumeid = wpjobportal::$_common->parseID($wpjobportal_resumeid);
                    }
                    if(is_numeric($wpjobportal_resumeid) && $wpjobportal_resumeid > 0){
                        // below code is only here until the interface is not built properly
                        $wpjobportal_resume_title_options = get_option('wpjobportal_resume_document_title_settings');
                        if(!empty($wpjobportal_resume_title_options)){
                            $page_title_val = $wpjobportal_resume_title_options;
                        }

                        $page_title_val = WPJOBPORTALincluder::getJSModel('resume')->makeResumeSeoDocumentTitle($page_title_val , $wpjobportal_resumeid);
                    }
                break;
                default: // for all other layouts
                    $wpjobportal_matcharray = array(
                        '[separator]' => '|',
                        '[sitename]' => get_bloginfo( 'name', 'display' )
                    );
                    $page_title_val = $this->replaceMatches($page_title_val,$wpjobportal_matcharray);
                    break;
            }
        }

        /*
         * Whatever the page could not answer.
         *
         * A resume title is configured as "[applicationtitle] [jobcategory]
         * [separator] [sitename]", and the record it names is only read once
         * the visitor is allowed to see it. A visitor who is not - and any
         * request where the id never resolved - got the placeholders
         * themselves, printed in the browser tab and in search results. The
         * two that can always be answered are answered; the rest are dropped
         * rather than shown.
         */
        if (wpjobportalphplib::wpJP_strstr($page_title_val, '[')) {
            $page_title_val = $this->replaceMatches($page_title_val, array(
                '[separator]' => '-',
                '[sitename]' => get_bloginfo('name', 'display'),
            ));
            $page_title_val = $this->tidySeoDocumentTitle($page_title_val);
            if ($page_title_val === '') {
                $page_title_val = get_bloginfo('name', 'display');
            }
        }

        return $page_title_val;
    }

    /*
     * Drops the [tokens] a title could not fill (a resume with no category,
     * a job with no city) and the separators and spaces they leave behind.
     * The make*SeoDocumentTitle() builders call it too, because themes call
     * them directly and never reach the clean-up above.
     */
    function tidySeoDocumentTitle($wpjobportal_title) {
        $wpjobportal_title = preg_replace('/\[[a-z0-9_]+\]/i', '', (string) $wpjobportal_title);
        $wpjobportal_title = preg_replace('/\s+([-|,])(\s+[-|,])+(?=\s)/', ' $1', $wpjobportal_title);
        $wpjobportal_title = trim(preg_replace('/\s{2,}/', ' ', $wpjobportal_title));
        return trim($wpjobportal_title, " -|,");
    }

    function replaceMatches($wpjobportal_string, $wpjobportal_matcharray) {
        foreach ($wpjobportal_matcharray AS $find => $replace) {
            $wpjobportal_string = wpjobportalphplib::wpJP_str_replace($find, $replace, $wpjobportal_string);
        }
        return $wpjobportal_string;
    }

    function WPJobPortalMetaTags(){
        $wpjobportal_module = $this->module_name;
        $wpjobportal_layout = $this->file_name;
        // making sure our layout is being opened & making sure proper values are set
        if ($wpjobportal_module != '' && $wpjobportal_layout != '') {
            $wpjobportal_description = $this->getWPJobPortalMetaDescriptionByPage($wpjobportal_module, $wpjobportal_layout);
            if(!empty($wpjobportal_description)){
                echo '<meta name="description" content="'.esc_html($wpjobportal_description).'"/>'."\n";
            }
        }
    }
	
    function isElegantDesignEnabled(){
        if(in_array('elegantdesign', wpjobportal::$_active_addons)){
            // check if addon is not properly installed
            if (!$this->WPJPcheck_field()) {
				if(!current_user_can('manage_options')){
					return false;
				}
            }
            return true;
        }

        // Elegant Design is part of Pro since 2.7.0. Pro's module says so here
        // instead of through an active plugin folder, and Pro's licence never
        // gates a feature, so the install check above does not apply to it.
        return (bool) apply_filters('wpjobportal_elegantdesign_provided', false);
    }

    function getWPJobPortalMetaDescriptionByPage($wpjobportal_module,$wpjobportal_layout){
        $wpjobportal_description = '';
        if ($wpjobportal_module != '' && $wpjobportal_layout != '') {
            switch ($wpjobportal_layout) {
                case 'controlpanel':
                    $wpjobportal_description = esc_html(__('This Is Meta Description', 'wp-job-portal'));
                    break;
                }
        }
        return $wpjobportal_description;
    }

    function checkLanguageSpecialCase(){
        $locale = get_locale();
        $locale = strtolower(substr($locale, 0,2));
        switch ($locale) {
            case 'ja':
            // case 'ja_JP':
                return false;
            break;
            case 'ko':
            // case 'ko_KR':
                return false;
            break;
            case 'es':
            // case 'es_ES':
                return false;
            break;
            case 'zh':
            // case 'zh_CN':
            // case 'zh_TW':
            // case 'zh_HK':
                return false;
            break;
            case 'el':
                return false;
            break;
            case 'de':
            //case 'de_DE':
                return false;
            break;

        }
        return true;
    }

    function encodeIdForDownload($wpjobportal_resume_id){
        if( $wpjobportal_resume_id == ''){
            return '';
        }
        $wpjobportal_string_data = gmdate( 'Y-m-d H:i:s' )."Z".$wpjobportal_resume_id;
        //$wpjobportal_resume_id_string = (base64_encode($wpjobportal_string_data));
        $wpjobportal_resume_id_string = strtr(base64_encode($wpjobportal_string_data), '+/', '-_');;
        return $wpjobportal_resume_id_string;
    }

    function decodeIdForDownload($wpjobportal_resume_id_string){
        if($wpjobportal_resume_id_string == ''){
            return '';
        }

        $wpjobportal_string_val = base64_decode($wpjobportal_resume_id_string);
        $wpjobportal_string_val = base64_decode(strtr($wpjobportal_resume_id_string, '-_', '+/'));

        $wpjobportal_string_array = explode('Z', $wpjobportal_string_val);

        $wpjobportal_date_time = $wpjobportal_string_array[0];
        $current_time = gmdate( 'Y-m-d H:i:s' );

        $wpjobportal_dateTime1 = new DateTime($wpjobportal_date_time);
        $wpjobportal_dateTime2 = new DateTime($current_time);

        // Calculate the difference
        // $wpjobportal_interval = $wpjobportal_dateTime1->diff($wpjobportal_dateTime2);

        // Get the total difference in seconds
        $wpjobportal_secondsDifference = abs($wpjobportal_dateTime1->getTimestamp() - $wpjobportal_dateTime2->getTimestamp());

        // Check if the difference is less than an hour (3600 seconds)
        if ($wpjobportal_secondsDifference < 3600) {
            $wpjobportal_resume_id = $wpjobportal_string_array[1];
            return $wpjobportal_resume_id;
        }

        return '';
    }


    /**
     * Keep the results worth showing, and drop the rest.
     *
     * @param array $wpjobportal_results     Scored rows, best first.
     * @param float $highest_score           The best relevance score among them.
     * @param int   $wpjobportal_enitity_for 1 for jobs, 2 for resumes.
     * @param int   $wpjobportal_score_override
     *        1 when the caller has already chosen which records these are and
     *        only wants them scored.
     *
     *        Two call sites have passed this fourth argument since the feature
     *        shipped, to a function that declared three parameters. PHP drops a
     *        surplus argument without a word, so it had never once had an
     *        effect. It matters on the path it was written for:
     *        applyAIScoresToJobs() hands over the ids of the jobs already on the
     *        page and asks what each one scores, so filtering that list by
     *        relevance does not remove a bad suggestion - it removes the score
     *        from a job that is on the page regardless, and the card renders
     *        with no percentage on it. Twelve jobs in, nine scores out.
     *
     *        The relevance filtering below is for choosing records. When the
     *        records are already chosen, all that is left to do is de-duplicate.
     * @return array
     */
    function applyThresholdOnResults($wpjobportal_results, $highest_score, $wpjobportal_enitity_for, $wpjobportal_score_override = 0) {
        if (empty($wpjobportal_results)) {
            return $wpjobportal_results; // Return early if no results
        }

        $threshold = 30; // Percentage threshold
        $highest_custom_score = $wpjobportal_results[0]->custom_score ?? 0;

        // Calculate threshold values
        $custom_score_threshold_value = ($threshold / 100) * $highest_custom_score;
        $score_threshold_value = ($threshold / 100) * $highest_score;

        // Track highest scores for each jobid
        $unique_results = [];

        foreach ($wpjobportal_results as $wpjobportal_result) {
            if (!$wpjobportal_score_override) {
                // Skip results below the threshold (except the first result)
                if (
                    ($wpjobportal_result->custom_score <= $custom_score_threshold_value && $wpjobportal_result !== $wpjobportal_results[0]) &&
                    ($wpjobportal_result->score <= $score_threshold_value && $wpjobportal_result !== $wpjobportal_results[0])
                ) {
                    continue;
                }

                if($wpjobportal_result->custom_score == 0 && $wpjobportal_result->score < 1.5) continue;
            }
            // Ensure uniqueness by entitiy id, keeping the highest custom_score and then the highest score
            if($wpjobportal_enitity_for == 1){
                $record_id = $wpjobportal_result->jobid;
            }else{
                $record_id = $wpjobportal_result->resumeid;
            }

            if (
                !isset($unique_results[$record_id]) ||
                $wpjobportal_result->custom_score > $unique_results[$record_id]->custom_score ||
                ($wpjobportal_result->custom_score === $unique_results[$record_id]->custom_score && $wpjobportal_result->score > $unique_results[$record_id]->score)
            ) {
                $unique_results[$record_id] = $wpjobportal_result;
            }

            if (!isset($unique_results[$record_id]) || $wpjobportal_result->score > $unique_results[$record_id]->score) {
                $unique_results[$record_id] = $wpjobportal_result;
            }
        }
        return array_values($unique_results); // Return reindexed array
    }

    function getUniqueIdForTransient() {
        $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        if ( is_numeric($wpjobportal_uid) && $wpjobportal_uid > 0 ) {
            $wpjobportal_transient_id = 'user_' .$wpjobportal_uid;
        } else { // Fallback: generate ID from IP + User Agent
            $wpjobportal_ip = !empty($_SERVER['REMOTE_ADDR']) ? filter_var(wp_unslash($_SERVER['REMOTE_ADDR']), FILTER_VALIDATE_IP) : '0.0.0.0';
            $wpjobportal_useragent = !empty($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : 'unknown';
            // $wpjobportal_transient_id = $wpjobportal_ip .'_'. $wpjobportal_useragent;
            // Create short, URL-safe string
            $hash = rtrim(strtr(base64_encode(md5($wpjobportal_ip . $wpjobportal_useragent, true)), '+/', '-_'), '=');
            $wpjobportal_transient_id = 'guest_' . substr($hash, 0, 12);
        }

        return  $wpjobportal_transient_id;
    }

    function getRecordIdsForCurrentPage($wpjobportal_job_ids_list, $page_num) {
        $current_records_to_show = '';
        if($wpjobportal_job_ids_list !=''){
            if(!is_numeric($page_num) ){ // handle no page(first page)
                $page_num = 1;
            }
            // make an array
            $wpjobportal_job_id_list_arrray = array_map('intval', explode(',', $wpjobportal_job_ids_list)); // Convert to array of integers

            $pagination_size = wpjobportal::$_configuration['pagination_default_page_size'];; // How many per page

            // Calculate start index
            $page_num_offset = ($page_num - 1) * $pagination_size;

            // Get current slice of ids (array elements)
            $current_records_to_show_array = array_slice($wpjobportal_job_id_list_arrray, $page_num_offset, $pagination_size);
            if(!empty($current_records_to_show_array)){
                $current_records_to_show = implode(',', esc_sql($current_records_to_show_array)); // create comma sperated string from array
            }
        }
        return $current_records_to_show;
    }

    function storeAIRecordsIDListTransient($wpjobportal_job_ids_list, $wpjobportal_transient_for) {
        switch ($wpjobportal_transient_for) {
            case 1:
                $wpjobportal_transient_string = 'ai_suggested_jobs_list_';
                break;
            case 2:
                $wpjobportal_transient_string = 'ai_suggested_jobs_dashboard_';
                break;
            case 3:
                $wpjobportal_transient_string = 'ai_websearch_jobs_list_';
                break;
            case 4:
                $wpjobportal_transient_string = 'ai_suggested_resume_list_';
                break;
            case 5:
                $wpjobportal_transient_string = 'ai_suggested_resume_dashboard_';
                break;
            case 6:
                $wpjobportal_transient_string = 'ai_websearch_resume_list_';
                break;
            default:
                $wpjobportal_transient_string = 'ai_suggested_jobs_list_';
                break;
        }
        // get unique transient id for current user/guesat
        $wpjobportal_transient_id = WPJOBPORTALincluder::getJSModel('common')->getUniqueIdForTransient();
        // Store the data in a transient (expires in 1 hour)
        if($wpjobportal_job_ids_list != '' &&  $wpjobportal_transient_id != ''){ // making sure the data is not empty
            set_transient($wpjobportal_transient_string.$wpjobportal_transient_id, $wpjobportal_job_ids_list, HOUR_IN_SECONDS);
        }
    }

    function getAIRecordsIdListFromTransient($wpjobportal_transient_for){
        switch ($wpjobportal_transient_for) {
            case 1:
                $wpjobportal_transient_string = 'ai_suggested_jobs_list_';
                break;
            case 2:
                $wpjobportal_transient_string = 'ai_suggested_jobs_dashboard_';
                break;
            case 3:
                $wpjobportal_transient_string = 'ai_websearch_jobs_list_';
                break;
            case 4:
                $wpjobportal_transient_string = 'ai_suggested_resume_list_';
                break;
            case 5:
                $wpjobportal_transient_string = 'ai_suggested_resume_dashboard_';
                break;
            case 6:
                $wpjobportal_transient_string = 'ai_websearch_resume_list_';
                break;
            default:
                $wpjobportal_transient_string = 'ai_suggested_jobs_list_';
                break;
        }

        $wpjobportal_result_list = '';
        // get unique transient id for current user/guesat
        $wpjobportal_transient_id = WPJOBPORTALincluder::getJSModel('common')->getUniqueIdForTransient();
        // Store the data in a transient (expires in 1 hour)
        if($wpjobportal_transient_id != ''){
            $wpjobportal_result = get_transient($wpjobportal_transient_string.$wpjobportal_transient_id);
            if ($wpjobportal_result !== false) { // if data is found
                $wpjobportal_result_list = $wpjobportal_result;
            }
        }
        return $wpjobportal_result_list;
    }


    function updateRecordsForAISearch(){
       WPJOBPORTALincluder::getJSModel('job')->updateRecordsForAISearchJob();
        WPJOBPORTALincluder::getJSModel('resume')->updateRecordsForAISearchResume();
        update_option( 'wpjobportal_ai_search_data_sync_needed', 0,);
        return ;
    }

}
?>
