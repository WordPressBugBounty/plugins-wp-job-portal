<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALResumeModel {


    function getResumePercentage( $wpjobportal_resumeid ){
        if(!is_numeric($wpjobportal_resumeid))  return false;
        // get published sections first
        $list = $this->getPublishedSectionsList();
        $wpjobportal_sections_status = array();
        foreach ($list as $wpjobportal_key => $wpjobportal_value) {
            $wpjobportal_field = $wpjobportal_value->field;
            $wpjobportal_field = wpjobportalphplib::wpJP_explode('_', $wpjobportal_field);
            $wpjobportal_sections_status[$wpjobportal_value->section] = array('name' => $wpjobportal_field[1] , 'id' => $wpjobportal_value->section, 'status' => 0);
        }
        // percentage fo personal section
        $wpjobportal_percentage = 40;
        $wpjobportal_number_of_sections = (int) count($list);
        if($wpjobportal_number_of_sections == 0){
            $wpjobportal_section_percentage = 0;
            $wpjobportal_percentage = 100;
        }else{
            // how much percnetage will a section reprsent
            $wpjobportal_section_percentage = 60 / $wpjobportal_number_of_sections;
        }

        foreach ($wpjobportal_sections_status as $wpjobportal_key => $wpjobportal_section) {
            if($wpjobportal_section['id'] == 5 || $wpjobportal_section['id'] == 6){
                $wpjobportal_field = 'skills';
                if($wpjobportal_section['id'] == 6) $wpjobportal_field = 'resume';
                $query = "SELECT $wpjobportal_field FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` WHERE `id` = ".esc_sql($wpjobportal_resumeid);
                $wpjobportal_result = wpjobportal::$_db->get_var($query);
                if($wpjobportal_result !=''){
                    $wpjobportal_sections_status[$wpjobportal_key]['status'] = 1;
                    $wpjobportal_percentage = $wpjobportal_percentage + $wpjobportal_section_percentage;// section is filled add the section percentage to total
                }else{
                    // check their params now
                    $query = "SELECT params FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` WHERE `id` = ".esc_sql($wpjobportal_resumeid);
                    $wpjobportal_result = wpjobportal::$_db->get_var($query);
                    if($wpjobportal_result != '' ){
                        $params = json_decode($wpjobportal_result , true);
                        $wpjobportal_fields = wpjobportal::$_wpjpfieldordering->getUserfieldsfor( 3 , $wpjobportal_section['id']);
                        foreach($wpjobportal_fields AS $wpjobportal_field){
                            if(isset($params[$wpjobportal_field->field]) && $params[$wpjobportal_field->field] != ''){ // only add value for a section once. not for all its custom fields
                                $wpjobportal_sections_status[$wpjobportal_key]['status'] = 1;
                                $wpjobportal_percentage = $wpjobportal_percentage + $wpjobportal_section_percentage;// section is filled add the section percentage to total
                                break; // get out of this loop only counting it once. not for every custom field of a section
                            }
                        }
                    }
                }
            }else{
                $wpjobportal_table_name = 'resume' . $wpjobportal_section['name'] . 's';
                if ($wpjobportal_section['id'] == 2)
                    $wpjobportal_table_name = 'resume' . $wpjobportal_section['name'] . 'es';
                // section name in field ordering education, table name is still institutes
                if($wpjobportal_section['name'] == 'education'){
                    $wpjobportal_table_name = 'resume' .'institutes';
                }
                $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_".esc_sql($wpjobportal_table_name)."` WHERE `resumeid` = ".esc_sql($wpjobportal_resumeid);
                $wpjobportal_count = wpjobportal::$_db->get_var($query);
                if($wpjobportal_count != '' && $wpjobportal_count > 0){
                    $wpjobportal_sections_status[$wpjobportal_key]['status'] = 1;
                    $wpjobportal_percentage = $wpjobportal_percentage + $wpjobportal_section_percentage;// section is filled add the section percentage to total
                }
            }
        }
        //$filled_sections = 0;

/*
    // functionality of this code is handled above now.
        foreach ($wpjobportal_sections_status as $wpjobportal_key => $wpjobportal_value) {
            if($wpjobportal_value['status'] == 1)
                $filled_sections += 1;
        }
        if(empty($wpjobportal_sections_status)){
            $wpjobportal_total = 0;
        }else{
            $wpjobportal_total = count($wpjobportal_sections_status);
        }
        if($wpjobportal_total > 0){
            $others = 75 / $wpjobportal_total;
            $wpjobportal_total_fill = 0;
            for ($wpjobportal_i=1; $wpjobportal_i < $filled_sections; $wpjobportal_i++) {
                $wpjobportal_total_fill += $others;
            }
            if($wpjobportal_total_fill > 0){
                $wpjobportal_percentage = 25 + $wpjobportal_total_fill;
                $wpjobportal_percentage = round($wpjobportal_percentage);
            }else{
                $wpjobportal_percentage = 25;
            }
        }else{
            $wpjobportal_percentage = 100;
        }
*/

        $wpjobportal_sections_status['percentage'] = (int) round($wpjobportal_percentage);
        return $wpjobportal_sections_status;
    }
    function getPublishedSectionsList(){
        $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        if ($wpjobportal_uid != 0)
            $wpjobportal_published = '`published` = 1';
        else
            $wpjobportal_published = '`isvisitorpublished` = 1';
        //'section_institute','section_skills', 'section_language'
        // section_institute has been changed to section_education in database table
        if(in_array('advanceresumebuilder', wpjobportal::$_active_addons)){
            $frp = " ,'section_education','section_skills', 'section_language'";
        }else{
            $frp = "";
        }
        $query = "SELECT field , section FROM `" . wpjobportal::$_db->prefix . "wj_portal_fieldsordering` WHERE `field` IN('section_address',  'section_employer'$frp) AND ".esc_sql($wpjobportal_published)." AND `fieldfor` = 3";
        $wpjobportal_fields = wpjobportal::$_db->get_results($query);
        return $wpjobportal_fields;
    }

    /* new code for resume start */

    function storeResume($wpjobportal_data,$wpjobportal_uid=''){
        if (empty($wpjobportal_data)) return false;
        if (!$this->captchaValidate()) {
            WPJOBPORTALMessages::setLayoutMessage(esc_html(__('Incorrect Captcha code', 'wp-job-portal')), 'error',$this->getMessagekey());
            $wpjobportal_array = wp_json_encode(array('html' => 'error'));
            return $wpjobportal_array;
        }
        if(isset($wpjobportal_data) && !empty($wpjobportal_data['id']) && !wpjobportal::$_common->wpjp_isadmin()){
            if ($this->getIfResumeOwner($wpjobportal_data['id']) == false) {
                return false;
            }
        }
        // check to make sure quick apply is not edited
        if (isset($wpjobportal_data['quick_apply']) && $wpjobportal_data['quick_apply'] == 1 ) {
            return false;
        }


        $wpjobportal_resumeid = $wpjobportal_data['id'];
        $wpjobportal_data['sec_1']['id'] = $wpjobportal_resumeid; // because id is not in any section to put for sections
        if(!current_user_can('manage_options')){ // if not admin then get current user is
            $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        }else{ // if admin then use the uid submitted on the form
            $wpjobportal_uid = $wpjobportal_data['uid'];
        }
        $wpjobportal_data['sec_1']['uid'] = $wpjobportal_uid;

        $wpjobportal_resumedata = $wpjobportal_data['sec_1'];
        $wpjobportal_resumedata['resume_logo_deleted'] = $wpjobportal_data['resume_logo_deleted'];

        $wpjobportal_resume = $this->storePersonalSection($wpjobportal_resumedata); // store persnal section
        if($wpjobportal_resume === false) return false;
        if(isset($wpjobportal_resume[0])) $filestatus = $wpjobportal_resume[0];
        $wpjobportal_resumeid = $wpjobportal_resume[1];
        $wpjobportal_resumealiasid = $wpjobportal_resume[2].'-'.$wpjobportal_resumeid;
        if (wpjobportal::$_common->wpjp_isadmin()) {
            $wpjobportal_resumealiasid = $wpjobportal_resumeid;
        }
        $wpjobportal_sections =
            array(
                1 => array('name' => 'address' , 'id' => 2),
                2 => array('name' => 'institute' , 'id' => 3),
                3 => array('name' => 'employer' , 'id' => 4),
                4 => array('name' => 'skills' , 'id' => 5),
                5 => array('name' => 'editor' , 'id' => 6),
                6 => array('name' => 'reference' , 'id' => 7),
                7 => array('name' => 'language' , 'id' => 8),
            );
        $doremove = false;
        foreach ($wpjobportal_sections as $wpjobportal_sec) {
            $wpjobportal_sec_id = 'sec_'.$wpjobportal_sec['id'];
            // get sections's data object vise
            $wpjobportal_row = array();
            $wpjobportal_total = isset($wpjobportal_data[$wpjobportal_sec_id]) ? count($wpjobportal_data[$wpjobportal_sec_id]['id']) : 0; // only published sections will be considred
            // check if empty section submitted
            $wpjobportal_is_filled = false;
            for ($wpjobportal_i = 0; $wpjobportal_i < $wpjobportal_total; $wpjobportal_i++) {
                $doremove = false;
                foreach ($wpjobportal_data[$wpjobportal_sec_id] as $wpjobportal_key => $wpjobportal_arr) {
                    $wpjobportal_row[$wpjobportal_key] = isset($wpjobportal_arr[$wpjobportal_i]) ? $wpjobportal_arr[$wpjobportal_i] : '';
                    if($wpjobportal_key == 'deletethis' AND $wpjobportal_arr[$wpjobportal_i] == 1){
                        $doremove = true;
                    }
                    if( ! empty($wpjobportal_arr[$wpjobportal_i])){
                        $wpjobportal_is_filled = true;
                    }
                }
                $wpjobportal_row['resumeid'] = $wpjobportal_resumeid;
                wpjobportal::$_data['id'] = $wpjobportal_resumeid;
                if($doremove){
                    //var_dump('do remove sec '.$wpjobportal_sec);
                    $wpjobportal_result = $this->removeResumeSection( $wpjobportal_row, $wpjobportal_sec);
                }else{
                    if($wpjobportal_sec['id'] == 5 || $wpjobportal_sec['id'] == 6){
                        $wpjobportal_is_filled = true;
                    }
                    if( $wpjobportal_is_filled ){
                        $wpjobportal_result = $this->storeResumeSection( $wpjobportal_row , $wpjobportal_sec , $wpjobportal_i); // i is use for geting custom files
                        if($wpjobportal_result==false) return false;
                    }
                }
            }
        }
        // visitor apply
        if (isset($_COOKIE['wpjobportal_apply_visitor'])) {
            if (!is_user_logged_in()) {
                $wpjobportal_url = apply_filters('wpjobportal_addons_applyjob_visitor',false);
                wp_redirect($wpjobportal_url);
                exit;
            }
        }

        // action hook for add resume
        if(empty($wpjobportal_data['id'])){ // changed the if to handle problem case (it will handle unset and null set value case)
            if(!empty($wpjobportal_row['resumeid'])){ // handling log error
                $wpjobportal_data['id'] = $wpjobportal_row['resumeid']; // $wpjobportal_row is not stc class object of table class
            }else{
                $wpjobportal_data['id'] = $wpjobportal_resumeid;
            }

        }
        do_action('wpjobportal_after_store_resume_hook',$wpjobportal_data);

        return WPJOBPORTAL_SAVED;
    }

    private function getSaveSearchForView($wpjobportal_search) {
       if (!is_numeric($wpjobportal_search))
            return false;
        $query = "SELECT * FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumesearches` WHERE id = " . esc_sql($wpjobportal_search);
        $wpjobportal_result = wpjobportal::$_db->get_row($query);
        $wpjobportal_inquery = "";
        $params = array();
        if ( isset($wpjobportal_result->searchparams) && $wpjobportal_result->searchparams != null) {
            $params = json_decode($wpjobportal_result->searchparams, true);
        }
        if (isset($params['application_title'])) {
            wpjobportal::$_data['filter']['application_title'] = $params['application_title'];
            $wpjobportal_inquery .= " AND resume.application_title LIKE '%" . esc_sql($params['application_title']) . "%' ";
        }
        if (isset($params['first_name'])) {
            wpjobportal::$_data['filter']['first_name'] = $params['first_name'];
            $wpjobportal_inquery .= " AND resume.first_name LIKE '%" . esc_sql($params['first_name']) . "%'";
        }
        if (isset($params['middle_name'])) {
            wpjobportal::$_data['filter']['middle_name'] = $params['middle_name'];
            $wpjobportal_inquery .= " AND resume.middle_name LIKE '%" . esc_sql($params['middle_name']) . "%'";
        }
        if (isset($params['last_name'])) {
            wpjobportal::$_data['filter']['last_name'] = $params['last_name'];
            $wpjobportal_inquery .= " AND resume.last_name LIKE '%" .esc_sql( $params['last_name']) . "%'";
        }
        if (isset($params['nationality']) && is_numeric($params['nationality'])) {
            wpjobportal::$_data['filter']['nationality'] = $params['nationality'];
            $wpjobportal_inquery .= " AND resume.nationality = " . esc_sql($params['nationality']);
        }
        if (isset($params['gender'])) {
            wpjobportal::$_data['filter']['gender'] = $params['gender'];
            $wpjobportal_inquery .= " AND resume.gender = '" . esc_sql($params['gender']) . "' ";
        }
        if (isset($params['category']) && is_numeric($params['category'])) {
            wpjobportal::$_data['filter']['category'] = $params['category'];
            $wpjobportal_inquery .= " AND resume.job_category = " . esc_sql($params['category']) . " ";
        }
        if (isset($params['jobtype']) && is_numeric($params['jobtype'])) {
            wpjobportal::$_data['filter']['jobtype'] = $params['jobtype'];
            $wpjobportal_inquery .= " AND resume.jobtype = " . esc_sql($params['jobtype']) . " ";
        }
        if (isset($params['salaryrangetype'])) {
            wpjobportal::$_data['filter']['salaryrangetype'] = $params['salaryrangetype'];
            $wpjobportal_inquery .= " AND salaryrangetype.title LIKE '%" . esc_sql($params['salaryrangetype']) . "%' ";
        }
        if (isset($params['tags'])) {
            wpjobportal::$_data['filter']['tags'] = $params['tags'];
            $res = $this->makeQueryFromArray('tags', $params['tags']);
            if ($res)
                $wpjobportal_inquery .= " AND ( " . $res . " )";
        }
        if (isset($params['city'])) {
            wpjobportal::$_data['filter']['city'] = $params['city'];
            $_SESSION['wpjobportal-searchresume-form']['city'] = $params['city'];
        }
        //custom field code
        $wpjobportal_inquery2 = '';
        if (isset($wpjobportal_result->params) && $wpjobportal_result->params != null) {
            $wpjobportal_data = wpjobportal::$_wpjpcustomfield->userFieldsData(3);
            $or = '';
            if (!empty($wpjobportal_data)) {
                $wpjobportal_inquery2 .= " AND (";
                $wpjobportal_valarray = json_decode($wpjobportal_result->params);

                foreach ($wpjobportal_data as $uf) {
                    $wpjobportal_fieldname = $uf->field;
                    if (isset($wpjobportal_valarray->$wpjobportal_fieldname) && $wpjobportal_valarray->$wpjobportal_fieldname != null) {
                        switch ($uf->userfieldtype) {
                            case 'text':
                            case 'email':
                                //$wpjobportal_inquery2 .= esc_sql($or) . ' resume.params LIKE \'%"' . esc_sql($uf->field) . '":"%' . wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_valarray->$wpjobportal_fieldname) . '%"%\' ';
                                $wpjobportal_inquery2 .= esc_sql($or) . ' resume.params REGEXP \'"' . esc_sql($uf->field) . '":"[^"]*' . wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_valarray->$wpjobportal_fieldname) . '.*"\' ';
                                $or = " OR ";
                                break;
                            case 'combo':
                                $wpjobportal_inquery2 .= esc_sql($or) . ' resume.params LIKE \'%"' . esc_sql($uf->field) . '":"' . wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_valarray->$wpjobportal_fieldname) . '"%\' ';
                                $or = " OR ";
                                break;
                            case 'depandant_field':
                                $wpjobportal_inquery2 .= esc_sql($or) . ' resume.params LIKE \'%"' . esc_sql($uf->field) . '":"' . wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_valarray->$wpjobportal_fieldname) . '"%\' ';
                                $or = " OR ";
                                break;
                            case 'radio':
                                $wpjobportal_inquery2 .= esc_sql($or) . ' resume.params LIKE \'%"' . esc_sql($uf->field) . '":"' . wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_valarray->$wpjobportal_fieldname) . '"%\' ';
                                $or = " OR ";
                                break;
                            case 'checkbox':
                                //$wpjobportal_inquery2 .= esc_sql($or) . ' resume.params LIKE \'%"' . esc_sql($uf->field) . '":"' . wpjobportalphplib::wpJP_htmlspecialchars(implode(", ",$wpjobportal_valarray->$wpjobportal_fieldname)) . '%\' ';
                                $wpjobportal_inquery2 .= esc_sql($or) . ' resume.params REGEXP \'"' . esc_sql($uf->field) . '":"[^"]*' . wpjobportalphplib::wpJP_htmlspecialchars(implode(", ",$wpjobportal_valarray->$wpjobportal_fieldname)) . '.*"\' ';
                                $or = " OR ";
                                break;
                            case 'date':
                                $wpjobportal_inquery2 .= esc_sql($or) . ' resume.params LIKE \'%"' . esc_sql($uf->field) . '":"' . wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_valarray->$wpjobportal_fieldname) . '"%\' ';
                                $or = " OR ";
                                break;
                            case 'editor':
                                $wpjobportal_inquery2 .= esc_sql($or) . ' resume.params LIKE \'%"' . esc_sql($uf->field) . '":"' . wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_valarray->$wpjobportal_fieldname) . '"%\' ';
                                $or = " OR ";
                                break;
                            case 'textarea':
                                // $wpjobportal_inquery2 .= esc_sql($or) . ' resume.params LIKE \'%"' . esc_sql($uf->field) . '":"' . wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_valarray->$wpjobportal_fieldname) . '"%\' ';
                                $wpjobportal_inquery2 .= esc_sql($or) . ' resume.params REGEXP \'"' . esc_sql($uf->field) . '":"[^"]*' . wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_valarray->$wpjobportal_fieldname) . '.*"\' ';
                                $or = " OR ";
                                break;
                            case 'multiple':
                                // $wpjobportal_inquery2 .= esc_sql($or) . ' resume.params LIKE \'%"' . esc_sql($uf->field) . '":[';
                                // $wpjobportal_icomma = '';
                                // for ($wpjobportal_i = 0; $wpjobportal_i < count($wpjobportal_valarray->$wpjobportal_fieldname); $wpjobportal_i++) {
                                //     $multiplevals = $wpjobportal_valarray->$wpjobportal_fieldname;
                                //     $wpjobportal_inquery2 .=$wpjobportal_icomma . '"' . wpjobportalphplib::wpJP_htmlspecialchars($multiplevals[$wpjobportal_i]) . '"';
                                //     $wpjobportal_icomma = ',';
                                // }
                                // $wpjobportal_inquery2 .=']%\' ';



                                $finalvalue = '';
                                foreach($wpjobportal_valarray->$wpjobportal_fieldname AS $wpjobportal_value){
                                    if($wpjobportal_value){
                                        $finalvalue .= $wpjobportal_value.'.*';
                                    }
                                }
                                if($finalvalue){
                                    $wpjobportal_inquery2 .= esc_sql($or) . ' resume.params REGEXP \'%"' . esc_sql($uf->field) . '":"[^"]*'.wpjobportalphplib::wpJP_htmlspecialchars($finalvalue).'"\' ';
                                }
                                $or = " OR ";
                                break;
                        }
                        //to convert an std class object to array
                        if (!empty($wpjobportal_valarray)) {
                            $wpjobportal_valarray = wp_json_encode($wpjobportal_valarray);
                            $wpjobportal_valarray = json_decode($wpjobportal_valarray, true);
                        }
                        wpjobportal::$_data['filter']['params'] = $wpjobportal_valarray;
                    }
                }
                $wpjobportal_inquery2 .= " ) ";
            }
        }
        //patch
        if ($wpjobportal_inquery2 == ' AND ( ) ') {
            $wpjobportal_inquery2 = '';
        }
        //end
        $wpjobportal_inquery .= $wpjobportal_inquery2;
        return $wpjobportal_inquery;
    }
    function storeResumeSection( $wpjobportal_formdata, $wpjobportal_section , $wpjobportal_i) { // i is the index of A section have multi forms
        if(empty($wpjobportal_section)) return false;
        $wpjobportal_sectionid = $wpjobportal_section['id'];
        $wpjobportal_datafor = $wpjobportal_section['name'];

        // store skills/editor sections data
        if($wpjobportal_sectionid == 5 || $wpjobportal_sectionid == 6){
            $wpjobportal_result = $this->storeSkillsAndResumeSection($wpjobportal_formdata , $wpjobportal_section, $wpjobportal_i);
            return $wpjobportal_result;
        }
        if ($wpjobportal_sectionid == 2) {
            $wpjobportal_table_name = 'resume' . $wpjobportal_datafor . 'es';
        } else {
            $wpjobportal_table_name = 'resume' . $wpjobportal_datafor . 's';
        }

       $wpjobportal_row = WPJOBPORTALincluder::getJSTable($wpjobportal_table_name);
        $return_cf = $this->makeResumeTableParams($wpjobportal_formdata,$wpjobportal_sectionid,$wpjobportal_i);
        $params = array();
        $par = json_decode($return_cf['params'],true);
        if(is_array($par)){
            foreach($par AS $wpjobportal_key => $wpjobportal_value){
                $params[$wpjobportal_key] = $wpjobportal_value;
            }
        }
        $wpjobportal_resumeid = $wpjobportal_formdata['resumeid'];

        //check whether form data array is empty;
            $wpjobportal_check_array = $wpjobportal_formdata;
            unset($wpjobportal_check_array['resumeid']);
            $wpjobportal_empty_flag = (count(array_filter($wpjobportal_check_array)) == 0) ? 1 : 0;
            if($wpjobportal_empty_flag == 1){
                return true;
            }
        //

        // moved this code below to avoid saving empty sections because there date fields are filled.
        if($wpjobportal_section['id']==4){
            if(!isset($wpjobportal_formdata['employer_current_status'])){
             $wpjobportal_formdata['employer_current_status']=0;
            }
            if(isset($wpjobportal_formdata['employer_from_date']) && $wpjobportal_formdata['employer_from_date'] != ''){
              $wpjobportal_formdata['employer_from_date']= gmdate('Y-m-d',strtotime($wpjobportal_formdata['employer_from_date']));
            }else{
              $wpjobportal_formdata['employer_from_date'] = '1970-01-01';
            }
            if(isset($wpjobportal_formdata['employer_to_date']) && $wpjobportal_formdata['employer_to_date'] !='' ){
              $wpjobportal_formdata['employer_to_date'] = gmdate('Y-m-d',strtotime($wpjobportal_formdata['employer_to_date']));
            }else{
              $wpjobportal_formdata['employer_to_date'] = '1970-01-01';
            }
        }
        // to handle the case of education section

        if($wpjobportal_datafor == 'institute'){
            if(isset($wpjobportal_formdata['todate']) && $wpjobportal_formdata['todate'] != ''){
              $wpjobportal_formdata['todate']= gmdate('Y-m-d',strtotime($wpjobportal_formdata['todate']));
            }else{
              $wpjobportal_formdata['todate'] = '1970-01-01';
            }
            if(isset($wpjobportal_formdata['fromdate']) && $wpjobportal_formdata['fromdate'] !='' ){
              $wpjobportal_formdata['fromdate'] = gmdate('Y-m-d',strtotime($wpjobportal_formdata['fromdate']));
            }else{
              $wpjobportal_formdata['fromdate'] = '1970-01-01';
            }
        }

        // set created date
        if( ! is_numeric($wpjobportal_formdata['id'])){
            $wpjobportal_formdata['created'] = gmdate('Y-m-d H:i:s');
        }

        if(WPJOBPORTALincluder::getJSModel('common')->checkLanguageSpecialCase()){
            $wpjobportal_formdata = wpjobportal::$_common->stripslashesFull($wpjobportal_formdata);// remove slashes with quotes.
        }

        if($params){
            $wpjobportal_formdata['params'] = wp_json_encode($params);
        }else{
            $wpjobportal_formdata['params'] = '';
        }
        // custom field code end
        $wpjobportal_formdata = wpjobportal::wpjobportal_sanitizeData($wpjobportal_formdata);

        if (!$wpjobportal_row->bind($wpjobportal_formdata)) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }


        if (!$wpjobportal_row->check()) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        if (!$wpjobportal_row->store()) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        return true;
    }
    function storeSkillsAndResumeSection($wpjobportal_formdata , $wpjobportal_section, $wpjobportal_i){
        if(empty($wpjobportal_section)) return '';
        $wpjobportal_sectionid = $wpjobportal_section['id'];
        $wpjobportal_datafor = $wpjobportal_section['name'];

        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('resume');

        $wpjobportal_formdata['id'] = $wpjobportal_formdata['resumeid'];
        $wpjobportal_resumeid = $wpjobportal_formdata['resumeid'];
        if(!is_numeric($wpjobportal_resumeid)){
            return '';
        }
        unset($wpjobportal_formdata['resumeid']);
        if ($wpjobportal_sectionid == 6) { // editor
            //$wpjobportal_formdata['resume'] = JRequest::getVar('resumeeditor', '', 'post', 'string', JREQUEST_ALLOWHTML );
            // RESUME Resume CUSTOM FIELD
            //$params = $this->getDataForParams(6, $wpjobportal_data);
            $return_cf = $this->makeResumeTableParams($wpjobportal_formdata, $wpjobportal_sectionid, $wpjobportal_i);
            $params = $return_cf['params'];

            $pquery = "SELECT params FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` WHERE id = " . esc_sql($wpjobportal_resumeid);
            $parmsvar = wpjobportal::$_db->get_var($pquery);
            $parray = array();
            if (isset($parmsvar) && $parmsvar != '') {
                $parray = json_decode($parmsvar);
            }
            if (isset($params) && $params != '') {
                $params = json_decode($params);
            }
            if(!empty($parray)){
                $params = (object) array_merge((array) $params, (array) $parray);
            }
            if(is_object($params) && !empty($params)){
                $params = wp_json_encode($params);
                $queryparams = " , params='" . $params . "' ";
            }else{
                $queryparams = "";
            }
            //END
            $wpjobportal_resume = WPJOBPORTALrequest::getVar('resume_edit_val');
            if($wpjobportal_resume == ''){
                $wpjobportal_resume = WPJOBPORTALrequest::getVar('resume');
            }
            $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_resume` SET resume='" . esc_sql($wpjobportal_resume) . "' " .$queryparams." WHERE id = ".esc_sql($wpjobportal_resumeid);
            wpjobportal::$_db->query($query);

        }elseif($wpjobportal_sectionid==5){
            $wpjobportal_skills = WPJOBPORTALrequest::getVar('skills');
            $wpjobportal_skills = sanitize_text_field( $wpjobportal_skills );
            // RESUME SKILL CUSTOM FIELD
            //$params = $this->getDataForParams(5, $wpjobportal_data);
            $return_cf = $this->makeResumeTableParams($wpjobportal_formdata, $wpjobportal_sectionid, $wpjobportal_i);
            $params = $return_cf['params'];
            $pquery = "SELECT params FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` WHERE id = " . esc_sql($wpjobportal_resumeid);
            $parmsvar = wpjobportal::$_db->get_var($pquery);

            $parray = array();
            if (isset($parmsvar) && $parmsvar !='' ) {
                $parray = json_decode($parmsvar);
            }
            if (isset($params) && $params != '') {
                $params = json_decode($params);
            }
            if(!empty($parray)){
                $params = (object) array_merge((array) $parray, (array) $params); // in case of edit/update of field old values were presistent
            }
            if(is_object($params) && !empty($params)){
                $params = wp_json_encode($params);
                $queryparams = " , params='" . $params . "' ";
            }else{
                $queryparams = "";
            }
            //END
            $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_resume` SET skills='" . esc_sql($wpjobportal_skills) . "' " . $queryparams . " WHERE id = ".esc_sql($wpjobportal_resumeid);
            wpjobportal::$_db->query($query);

        }
        return true;
        $return_cf = $this->makeResumeTableParams($wpjobportal_formdata, $wpjobportal_sectionid, $wpjobportal_i);
        $wpjobportal_formdata['params'] = $return_cf['params'];
        if (!$wpjobportal_row->bind($wpjobportal_formdata)) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        //retain last state of below vars in edit
        if (is_numeric($wpjobportal_formdata['id']) ){
            unset($wpjobportal_row->isfeaturedresume);
        }

        if (!$wpjobportal_row->store()) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }
        return true;
    }

    function removeResumeSection( $wpjobportal_formdata, $wpjobportal_section ){
        if($wpjobportal_formdata['deletethis'] != 1){
            return;
        }
        if($wpjobportal_formdata['id'] == '' || !isset($wpjobportal_formdata['id'])){
            return;
        }
        //exit;
        if(empty($wpjobportal_section)) return false;
        $wpjobportal_sec_id = $wpjobportal_section['id'];
        $wpjobportal_datafor = $wpjobportal_section['name'];

        $wpjobportal_resumeid = $wpjobportal_formdata['resumeid'];
        $wpjobportal_sectionid = $wpjobportal_formdata['id'];

        $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        if(!is_numeric($wpjobportal_resumeid)) return false;
        if(!is_numeric($wpjobportal_sectionid)) return false;

        if ( ! current_user_can( 'manage_options' ) ) { // user is not admin check perform
            if( ! WPJOBPORTALincluder::getObjectClass('user')->isguest()){
                $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` WHERE id = ".esc_sql($wpjobportal_resumeid)." AND uid =". esc_sql($wpjobportal_uid);
                $wpjobportal_result = wpjobportal::$_db->get_var($query);
                if($wpjobportal_result == 0){
                    return false; // not your resume
                }
            }
        }

        if ($wpjobportal_sec_id == 2) {
            $wpjobportal_table_name = 'resume' . $wpjobportal_datafor . 'es';
        } else {
            $wpjobportal_table_name = 'resume' . $wpjobportal_datafor . 's';
        }

        if($wpjobportal_sec_id == 5 || $wpjobportal_sec_id == 6){ //skill,editor
            return true;
        }else{
            $query = "DELETE FROM `" . wpjobportal::$_db->prefix . "wj_portal_".esc_sql($wpjobportal_table_name)."` WHERE id = ".esc_sql($wpjobportal_sectionid);
            if (wpjobportaldb::query($query)) {
                return true;
            }else{
                return false;
            }
        }
    }

    function storePersonalSection($wpjobportal_data){
       if(empty($wpjobportal_data)) return false;
        if(isset($wpjobportal_data['id']) && $wpjobportal_data['id'] == 0 ) $wpjobportal_data['id'] = '';
        $wpjobportal_id = (int) $wpjobportal_data['id'];
        $wpjobportal_isnew = !$wpjobportal_id;
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('resume');
        $wpjobportal_submission_type = wpjobportal::$_config->getConfigValue('submission_type') ;
        $wpjobportal_user = WPJOBPORTALincluder::getObjectClass('user');
        $wpjobportal_no_package_needed = 0;
        if (empty($wpjobportal_data['id'])) {
            if(isset($wpjobportal_data['application_title'])){
                $wpjobportal_data['alias'] = wpjobportalphplib::wpJP_str_replace(' ', '-', $wpjobportal_data['application_title']);
            }else{
                $alias_string = $wpjobportal_data['first_name'];;
                if(isset($wpjobportal_data['middle_name'])){// min field issue
                    $alias_string .= ' '.$wpjobportal_data['middle_name'];
                }
                $alias_string .= ' '.$wpjobportal_data['last_name'];
                $wpjobportal_data['alias'] = wpjobportalphplib::wpJP_str_replace(' ', '-', $alias_string);
            }
            $wpjobportal_data['alias'] = wpjobportalphplib::wpJP_str_replace('_', '-', $wpjobportal_data['alias']);
            $wpjobportal_data['created'] = gmdate('Y-m-d H:i:s');
            $wpjobportal_visitorcanapply = wpjobportal::$_config->getConfigurationByConfigName('visitor_can_apply_to_job');
            $wpjobportal_isguest = WPJOBPORTALincluder::getObjectClass('user')->isguest();
            if((in_array('credits', wpjobportal::$_active_addons) && $wpjobportal_isguest && $wpjobportal_visitorcanapply != 1) || (in_array('credits', wpjobportal::$_active_addons) && !$wpjobportal_isguest)){
                if($wpjobportal_submission_type == 1){
                    $wpjobportal_data['status'] = wpjobportal::$_config->getConfigurationByConfigName('empautoapprove');
                }elseif ($wpjobportal_submission_type == 2) {
                    // in case of per listing submission mode
                    $wpjobportal_price_check = WPJOBPORTALincluder::getJSModel('credits')->checkIfPriceDefinedForAction('add_resume');
                    if($wpjobportal_price_check == 1){ // if price is defined then status 3
                        $wpjobportal_data['status'] = 3;
                    }else{ // if price not defined then status set to auto approve configuration
                        $wpjobportal_data['status'] = wpjobportal::$_config->getConfigValue('empautoapprove');
                    }
                }elseif ($wpjobportal_submission_type == 3) {

                    $wpjobportal_result = WPJOBPORTALincluder::getJSModel('credits')->checkIfPackageDefinedForRole(2); //2 for job seeker
                    if($wpjobportal_result == 0){ // 0 means no package found. so allow the action.
                        $wpjobportal_no_package_needed = 1;
                    }

                    if($wpjobportal_no_package_needed == 0){
                        $wpjobportal_upakid = WPJOBPORTALrequest::getVar('upakid',null,0);
                        $wpjobportal_package = apply_filters('wpjobportal_addons_userpackages_permodule',false,$wpjobportal_upakid,$wpjobportal_user->uid(),'remresume');
                        if( !$wpjobportal_package ){
                            return WPJOBPORTAL_SAVE_ERROR;
                        }
                        if( $wpjobportal_package->expired ){
                            return WPJOBPORTAL_SAVE_ERROR;
                        }
                        //if Department are not unlimited & there is no remaining left
                        if( $wpjobportal_package->resume!=-1 && !$wpjobportal_package->remresume ){ //-1 = unlimited
                            return WPJOBPORTAL_SAVE_ERROR;
                        }
                        #user packae id--
                        $wpjobportal_data['userpackageid'] = $wpjobportal_upakid;
                    }
                    $wpjobportal_data['status'] = wpjobportal::$_config->getConfigValue('empautoapprove');
                }
            }else{
                $wpjobportal_data['status'] = wpjobportal::$_config->getConfigValue('empautoapprove');
            }
        } else {
            if(current_user_can('manage_options')){
                $wpjobportal_data['status'] = $wpjobportal_data['status'];
            }else{
                $wpjobportal_row->load($wpjobportal_data['id']);
                $wpjobportal_data['status'] = $wpjobportal_row->status;
            }
        }
        /*$query = "SELECT * FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering WHERE field =  'searchable' AND fieldfor =3";
        $record = wpjobportal::$_db->get_row($query);
        if($record->published == 0 AND is_user_logged_in()){
            $wpjobportal_data['searchable'] = 1;
        }elseif($record->isvisitorpublished == 0){
            $wpjobportal_data['searchable'] = 1;
        }*/

        $wpjobportal_data['last_modified'] = gmdate('Y-m-d H:i:s');
        $wpjobportal_section = 1;

        $wpjobportal_data = wpjobportal::wpjobportal_sanitizeData($wpjobportal_data);

        $wpjobportal_data = wpjobportal::$_common->stripslashesFull($wpjobportal_data);// remove slashes with quotes.
        $return_cf = $this->makeResumeTableParams($wpjobportal_data,$wpjobportal_section);
        $wpjobportal_data['params'] = $return_cf['params'];

        // storing  custom section values
        $wpjobportal_resume_custom_sections = WPJOBPORTALincluder::getJSModel('fieldordering')->getResumeCustomSections();

        if(!empty($wpjobportal_resume_custom_sections)){
            $wpjobportal_main_params = json_decode($wpjobportal_data['params'],true); // decoding main paras
            $wpjobportal_sec_data = WPJOBPORTALrequest::get('post'); // fetching POST to extract custom section field values
            $wpjobportal_section_parms_array = array();
            foreach ($wpjobportal_resume_custom_sections as $wpjobportal_resume_section) {
                //$wpjobportal_resume_section_data = $this->makeResumeTableParams($wpjobportal_sec_data,$wpjobportal_resume_section->section); // create and fetch parms for custom section fields using $_POST data and fielf ordering section value
                $wpjobportal_resume_section_data = $this->getDataForParamsResume($wpjobportal_resume_section->section , $wpjobportal_sec_data);

                $wpjobportal_resume_section_data_params = json_decode($wpjobportal_resume_section_data['params'],true);// convert custom section json to php array
                //$wpjobportal_main_params = array_merge($wpjobportal_main_params,$wpjobportal_resume_section_data_params); // merge php arrays main(personal section params) and custom section filds params
                $wpjobportal_section_parms_array = array_merge($wpjobportal_section_parms_array,$wpjobportal_resume_section_data_params); // merge php arrays main(personal section params) and custom section filds params
            }
            if(!is_array($wpjobportal_main_params)){// to handle empty params case
                $wpjobportal_main_params = array();
            }
            $wpjobportal_main_params = array_merge($wpjobportal_main_params,$wpjobportal_section_parms_array);
            $wpjobportal_data['params'] = wp_json_encode($wpjobportal_main_params);// inset new updated params php array as json to data object to stored as params
        }

        if (!$wpjobportal_row->bind($wpjobportal_data)) {
            return WPJOBPORTAL_SAVE_ERROR;
        }
        if (!$wpjobportal_row->store()) {
            return WPJOBPORTAL_SAVE_ERROR;
        }
        $objectid = $wpjobportal_row->id;
        $wpjobportal_resumeid = $wpjobportal_row->id;
        // to handle log error of resume_logo_deleted not set in array
        if(isset($wpjobportal_data['resume_logo_deleted']) && $wpjobportal_data['resume_logo_deleted'] == 1){
            $this->deleteResumeLogoModel($wpjobportal_resumeid);
        }
        if (isset($_FILES['photo']['size']) && $_FILES['photo']['size'] > 0) {
            if(isset($wpjobportal_data['resume_logo_deleted']) && $wpjobportal_data['resume_logo_deleted'] != 1){
                $this->deleteResumeLogoModel($wpjobportal_resumeid);
            }
            $this->uploadPhoto($objectid);
        }

        if (isset($_FILES['resumefiles'])) {
            $filereturnvalue = $this->uploadResume($objectid);
        }
        // upload custom files

        /*
        $return_cf['customflagforadd'] = $customflagforadd;
        $return_cf['customflagfordelete'] = $customflagfordelete;
        $return_cf['custom_field_namesforadd'] = $custom_field_namesforadd;
        $return_cf['custom_field_namesfordelete'] = $custom_field_namesfordelete;
        */
        if(is_array($return_cf) && !empty($return_cf)){
            $this->storeCustomUploadFile($wpjobportal_resumeid,$return_cf);
        }

        // Save resumeid in session in case of visitor add resume is allowed
        if (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
            $wpjobportal_visitor_can_add_resume = wpjobportal::$_config->getConfigurationByConfigName('visitor_can_add_resume');
            if ($wpjobportal_visitor_can_add_resume == 1) {
                $_SESSION['wp-wpjobportal']['resumeid'] = $wpjobportal_resumeid;
            }
        }
        //Update credits log in case of new resume
        if ($wpjobportal_data['id'] == '') {
            if(empty($wpjobportal_data['id'])){
                WPJOBPORTALincluder::getJSModel('emailtemplate')->sendMail(3,1,$wpjobportal_resumeid); // 3 for resume,1 for add new resume
            }
        }

        if(in_array('credits', wpjobportal::$_active_addons)){
            if(!WPJOBPORTALincluder::getObjectClass('user')->isguest() && !wpjobportal::$_common->wpjp_isadmin() && empty($wpjobportal_data['id']) && ($wpjobportal_submission_type == 3 && $wpjobportal_no_package_needed == 0) ){
                do_action('wpjobportal_addons_user_transactionlog',$wpjobportal_row,'resume',$wpjobportal_upakid,$wpjobportal_user->uid(),$wpjobportal_isnew);
            }
        }

        $return = array();
        if(isset($filereturnvalue)) $return[0] = $filereturnvalue;
        $return[1] = $wpjobportal_row->id;
        $return[2] = $wpjobportal_row->alias;
        return $return;
    }

/*

        $return['customflagforadd'] = $customflagforadd;
        $return['customflagfordelete'] = $customflagfordelete;
        $return['custom_field_namesforadd'] = $custom_field_namesforadd;
        $return['custom_field_namesfordelete'] = $custom_field_namesfordelete;

*/
    function storeCustomUploadFile($wpjobportal_resumeid,$wpjobportal_data_array){
        if(!is_numeric($wpjobportal_resumeid)){
            return;
        }

        $wpjobportal_entity_for = 'resume';

        //removing custom field attachments
        if(isset($wpjobportal_data_array['customflagfordelete']) && $wpjobportal_data_array['customflagfordelete'] == true){
            foreach ($wpjobportal_data_array['custom_field_namesfordelete'] as $wpjobportal_key) {
               $res = wpjobportal::$_wpjpcustomfield->removeFileCustom($wpjobportal_resumeid,$wpjobportal_key,$wpjobportal_entity_for);
            }
        }

        //storing custom field attachments
        if(isset($wpjobportal_data_array['customflagforadd']) && $wpjobportal_data_array['customflagforadd'] == true){
            foreach ($wpjobportal_data_array['custom_field_namesforadd'] as $wpjobportal_key) {
                if (isset($_FILES[$wpjobportal_key])) {
                    if ($_FILES[$wpjobportal_key]['size'] > 0) { // logo
                       $res = wpjobportal::$_wpjpcustomfield->uploadFileCustom($wpjobportal_resumeid,$wpjobportal_key,$wpjobportal_entity_for);
                    }
                }
            }
        }
    }


    function makeResumeTableParams($wpjobportal_formdata,$wpjobportal_sectionid,$wpjobportal_i=0){

        $return_cf = $this->getDataForParamsResume($wpjobportal_sectionid , $wpjobportal_formdata, $wpjobportal_i);

        $params_new = $return_cf['params'];

        if(is_numeric($wpjobportal_formdata['id'])){
            $params_new = json_decode($params_new, true);
            $query = "SELECT params FROM `". wpjobportal::$_db->prefix ."wj_portal_resume` WHERE id = ".esc_sql($wpjobportal_formdata['id']);
            $oParams = wpjobportaldb::get_var($query);
            if(!empty($oParams)){
                $oParams = json_decode($oParams,true);
                $unpublihsedFields =/*apply_filters('wpjobportal_addons_customFields_unpublish',false,3,1);*/ WPJOBPORTALincluder::getJSModel('customfield')->getUnpublishedFieldsFor(3,1);
                foreach($unpublihsedFields AS $wpjobportal_field){
                    if(isset($oParams[$wpjobportal_field->field]) && !empty($oParams[$wpjobportal_field->field])){
                        $params_new[$wpjobportal_field->field] = $oParams[$wpjobportal_field->field];
                    }
                }
                $wpjobportal_sectionfields = wpjobportal::$_wpjpfieldordering->getUserfieldsfor(3,$wpjobportal_sectionid);
                foreach($wpjobportal_sectionfields AS $cfield){
                    if(isset($oParams[$cfield->field]))
                        unset($oParams[$cfield->field]);
                }

                foreach($oParams AS $wpjobportal_key => $wpjobportal_value){
                    $params_new[$wpjobportal_key] = $wpjobportal_value;
                }
            }
            if($params_new){
                $params_new = wp_json_encode($params_new);
            }
        }
        $return_cf['params'] = $params_new;
        //fix for resume only
        if($return_cf['params'] == null || $return_cf['params'] == 'null'){
            $return_cf['params'] = '';
        }
        return $return_cf;
    }

    // custom field code start
    function getDataForParamsResume($wpjobportal_sectionid, $wpjobportal_data , $wpjobportal_i = 0) {
        $wpjobportal_userfieldforresume = wpjobportal::$_wpjpfieldordering->getUserfieldsfor(3, $wpjobportal_sectionid);
        $customflagforadd = false;
        $customflagfordelete = false;
        $custom_field_namesforadd = array();
        $custom_field_namesfordelete = array();
        $params = array();
        foreach ($wpjobportal_userfieldforresume AS $ufobj) {
            $wpjobportal_vardata = '';
            if ($ufobj->userfieldtype == 'date') {
                $wpjobportal_vardata = (isset($wpjobportal_data[$ufobj->field]) && $wpjobportal_data[$ufobj->field] !='')  ? gmdate('Y-m-d H:i:s',strtotime($wpjobportal_data[$ufobj->field])) : '';
            } elseif($ufobj->userfieldtype == 'file'){
                // if(isset($_POST[$ufobj->field.'_1']) && $_POST[$ufobj->field.'_1'] == 0 && isset($_POST[$ufobj->field.'_2'])){
                //     $wpjobportal_vardata = $wpjobportal_data[$ufobj->field.'_2'];
                $ufield_1 = WPJOBPORTALrequest::getVar($ufobj->field.'_1','post','');
                $ufield_2 = WPJOBPORTALrequest::getVar($ufobj->field.'_2','post','');
                if($ufield_1 == 0 && $ufield_2 != ''){
                    $wpjobportal_vardata = sanitize_file_name($ufield_2);
                }else{
                    // if($wpjobportal_sectionid == 1){
                    //     $wpjobportal_section_id = 'sec_'.$wpjobportal_sectionid;
                    //     $wpjobportal_vardata = isset($_FILES[$wpjobportal_section_id]['name'][$ufobj->field]) ? sanitize_file_name($_FILES[$wpjobportal_section_id]['name'][$ufobj->field]) : '';
                    // }else{
                        //$wpjobportal_section_id = 'sec_'.$wpjobportal_sectionid;
                        if(isset($_FILES[$ufobj->field])){
                            $wpjobportal_vardata = isset($_FILES[$ufobj->field]['name']) ? sanitize_file_name($_FILES[$ufobj->field]['name']) : '';
                        }
                    //}
                }
                $customflagforadd = true;
                $custom_field_namesforadd[] = $ufobj->field;
            }else{
                $wpjobportal_vardata = isset($wpjobportal_data[$ufobj->field]) ? $wpjobportal_data[$ufobj->field] : '';
            }
            if(isset($wpjobportal_data[$ufobj->field.'_1']) && $wpjobportal_data[$ufobj->field.'_1'] == 1){
                $customflagfordelete = true;
                $custom_field_namesfordelete[]= $wpjobportal_data[$ufobj->field.'_2'];
            }
            if($wpjobportal_vardata != ''){
                if(is_array($wpjobportal_vardata)){
                    $wpjobportal_vardata = implode(', ', $wpjobportal_vardata);
                }
                $params[$ufobj->field] = wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_vardata);
            }else{ // to handle edit case filled issue
                $params[$ufobj->field] = '';
            }
        }
        $params = wp_json_encode($params);

        $return = array();
        $return['params'] = $params;
        $return['customflagforadd'] = $customflagforadd;
        $return['customflagfordelete'] = $customflagfordelete;
        $return['custom_field_namesforadd'] = $custom_field_namesforadd;
        $return['custom_field_namesfordelete'] = $custom_field_namesfordelete;

        return $return;
    }
    // custom field code End

    function getResumeDataBySection($wpjobportal_resumeid, $wpjobportal_sectionName){
        if(!is_numeric($wpjobportal_resumeid)) return false;

        switch ($wpjobportal_sectionName) {
            case 'personal': $wpjobportal_section = 1; break;
            case 'address': $wpjobportal_section = 2; break;
            case 'institute': $wpjobportal_section = 3; break;
            case 'employer': $wpjobportal_section = 4; break;
            case 'skills': $wpjobportal_section = 5; break;
            case 'editor': $wpjobportal_section = 6; break;
            case 'reference': $wpjobportal_section = 7; break;
            case 'language': $wpjobportal_section = 8; break;
            case 'default':
                return false;
        }
        $wpjobportal_data = array();
        if ($wpjobportal_sectionName == 'personal') {
            $wpjobportal_results = $this->getResumeBySection($wpjobportal_resumeid, $wpjobportal_sectionName);
            //$wpjobportal_resumelists = $this->getResumeListsForForm($wpjobportal_results);
            //wpjobportal::$_data[2]=$wpjobportal_resumelists;
        } else {
            $wpjobportal_sectionData = array();
            if ($wpjobportal_sectionName == "skills" OR $wpjobportal_sectionName == "editor") {
                $wpjobportal_results = $this->getResumeBySection($wpjobportal_resumeid, $wpjobportal_sectionName);
            } else {
                $wpjobportal_results = $this->getResumeBySection($wpjobportal_resumeid, $wpjobportal_sectionName);
            }
        }
        $custom_fields =WPJOBPORTALincluder::getObjectClass('customfields')->formCustomFields($wpjobportal_field, 1, 1);
        $wpjobportal_resume_section_fields = WPJOBPORTALincluder::getJSModel('customfield')->getResumeFieldsOrderingBySection($wpjobportal_section);
        wpjobportal::$_data[0] = $wpjobportal_results;
        return;
    }

    function getResumeBySection($wpjobportal_resumeid, $wpjobportal_sectionName ) {
        if (!is_numeric($wpjobportal_resumeid)) {
            return false;
        }
        if (empty($wpjobportal_sectionName)) {
            return false;
        }
        $wpjobportal_resume = '';
        if ($wpjobportal_sectionName == 'personal') {
            $query = "SELECT resume.id,resume.driving_license,resume.tags AS viewtags , resume.tags AS resumetags,resume.uid,resume.application_title, resume.first_name, resume.last_name, resume.cell, resume.email_address, resume.nationality AS nationalityid, resume.photo, resume.gender, resume.job_category, resume.experienceid, resume.home_phone, resume.work_phone, resume.date_of_birth,
                , resume.jobsalaryrangetype, resume.skills, resume.keywords, resume.searchable, resume.iamavailable, cat.cat_title AS categorytitle, jobtype.title AS jobtypetitle, resume.date_start,resume.jobtype
                , resume.resume, saltype.title AS rangetype,nationality.name AS nationality
                ,resume.params,resume.status
                        FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume
                        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON cat.id = resume.job_category
                        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON jobtype.id = resume.jobtype
                        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_salaryrangetypes` AS saltype ON saltype.id = resume.jobsalaryrangetype
                        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_countries` AS nationality ON nationality.id = resume.nationality
                        WHERE resume.id = " . esc_sql($wpjobportal_resumeid);

            $wpjobportal_isguest = WPJOBPORTALincluder::getObjectClass('user')->isguest();
            $wpjobportal_iswpjobportaluser = WPJOBPORTALincluder::getObjectClass('user')->isWPJOBPORTALUser();
            // uid was undefined
            // if(! $wpjobportal_isguest && $wpjobportal_iswpjobportaluser){
            //     if (!current_user_can( 'manage_options' ) && $wpjobportal_uid) {
            //         //$query .= " AND resume.uid  = " . esc_sql($wpjobportal_uid);
            //     }
            // }
            $wpjobportal_resume = wpjobportaldb::get_row($query);
        } elseif ($wpjobportal_sectionName == 'skills') {
            $query = "SELECT id,uid,skills,params FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` WHERE id = " . esc_sql($wpjobportal_resumeid);
            $wpjobportal_resume = wpjobportaldb::get_row($query);
        } elseif ($wpjobportal_sectionName == 'editor') {
            $query = "SELECT id,uid,resume,params FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` WHERE id = " . esc_sql($wpjobportal_resumeid);
            $wpjobportal_resume = wpjobportaldb::get_row($query);
        } elseif ($wpjobportal_sectionName == 'language') {
            $query = "SELECT * FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumelanguages` WHERE resumeid = " . esc_sql($wpjobportal_resumeid);
            $wpjobportal_resume = wpjobportaldb::get_results($query);
        } elseif ($wpjobportal_sectionName == 'address') {
            $query = "SELECT address.*,
                        cities.id AS cityid,
                        cities.name AS city,
                        states.name AS state,
                        countries.name AS country
                        FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumeaddresses` AS address
                        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS cities ON address.address_city = cities.id
                        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_states` AS states ON cities.stateid = states.id
                        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_countries` AS countries ON cities.countryid = countries.id
                        WHERE address.resumeid = " . esc_sql($wpjobportal_resumeid);
            $wpjobportal_resume = wpjobportaldb::get_results($query);
        } else {
            $query = "SELECT " . esc_sql($wpjobportal_sectionName) . ".*,
                        cities.id AS cityid,
                        cities.name AS city,
                        states.name AS state,
                        countries.name AS country
                        FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume" . esc_sql($wpjobportal_sectionName) . "s` AS " . esc_sql($wpjobportal_sectionName) . "
                        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS cities ON " . esc_sql($wpjobportal_sectionName) . "." . esc_sql($wpjobportal_sectionName) . "_city = cities.id
                        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_states` AS states ON cities.stateid = states.id
                        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_countries` AS countries ON cities.countryid = countries.id
                        WHERE " . esc_sql($wpjobportal_sectionName) . ".resumeid = " . esc_sql($wpjobportal_resumeid);
            $wpjobportal_resume = wpjobportaldb::get_results($query);
        }
        return $wpjobportal_resume;
    }
    // joomla code in this function
    // function getResumeListsForForm($wpjobportal_application) {
    //     $wpjobportal_resumelists = array();
    //     $wpjobportal_nationality_required = '';
    //     $license_country_required = '';
    //     $wpjobportal_gender_required = '';
    //     $driving_license_required = '';
    //     $wpjobportal_category_required = '';
    //     $wpjobportal_subcategory_required = '';
    //     $wpjobportal_salary_required = '';
    //     $workpreference_required = '';
    //     $wpjobportal_education_required = '';
    //     $wpjobportal_expsalary_required = '';

    //     // explicit use of site model in case form admin resume
    //     //$wpjobportal_fieldsordering = $this->getJSSiteModel('customfields')->getResumeFieldsOrderingBySection(1);
    //     $wpjobportal_fieldsordering = wpjobportal::$_wpjpcustomfield->getResumeFieldsOrderingBySection(1);
    //     foreach ($wpjobportal_fieldsordering AS $fo) {
    //         switch ($fo->field) {
    //             case "nationality":
    //                 $wpjobportal_nationality_required = ($fo->required ? 'required' : '');
    //                 break;
    //             case "license_country":
    //                 $license_country_required = ($fo->required ? 'required' : '');
    //                 break;
    //             case "gender":
    //                 $wpjobportal_gender_required = ($fo->required ? 'required' : '');
    //                 break;
    //             case "driving_license":
    //                 $driving_license_required = ($fo->required ? 'required' : '');
    //                 break;
    //             case "job_category":
    //                 $wpjobportal_category_required = ($fo->required ? 'required' : '');
    //                 break;
    //             case "job_subcategory":
    //                 $wpjobportal_subcategory_required = ($fo->required ? 'required' : '');
    //                 break;
    //             case "salary":
    //                 $wpjobportal_salary_required = ($fo->required ? 'required' : '');
    //                 break;
    //             case "jobtype":
    //                 $workpreference_required = ($fo->required ? 'required' : '');
    //                 break;
    //             case "heighestfinisheducation":
    //                 $wpjobportal_education_required = ($fo->required ? 'required' : '');
    //                 break;
    //             case "desired_salary":
    //                 $wpjobportal_expsalary_required = ($fo->required ? 'required' : '');
    //                 break;
    //             case "total_experience":
    //                 $wpjobportal_experienceid_required = ($fo->required ? 'required' : '');
    //                 break;
    //         }
    //     }
    //     // since common is already executed form admin


    //     $wpjobportal_gender = WPJOBPORTALincluder::getJSModel('common')->getGender();

    //     $wpjobportal_defaultCategory = WPJOBPORTALincluder::getJSModel('category')->getDefaultCategoryId();
    //     $wpjobportal_defaultJobtype = WPJOBPORTALincluder::getJSModel('jobtype')->getDefaultJobTypeId();
    //     $wpjobportal_yesno=WPJOBPORTALincluder::getJSModel('common')->getYesNo();
    //     $wpjobportal_job_type = WPJOBPORTALincluder::getJSModel('jobtype')->getJobTypeForCombo();
    //     $wpjobportal_job_categories = WPJOBPORTALincluder::getJSModel('category')->getCategoryForCombobox('');
    //     $wpjobportal_countries = WPJOBPORTALincluder::getJSModel('country')->getCountriesForCombo();
    //     if (isset($wpjobportal_application)) {
    //         $wpjobportal_resumelists['nationality'] = JHTML::_('select.genericList', $wpjobportal_countries, 'sec_1[nationality]', 'class="inputbox ' . $wpjobportal_nationality_required . ' wpjobportal-cbo" ' . '', 'value', 'text', $wpjobportal_application->nationality);
    //         $wpjobportal_resumelists['license_country'] = JHTML::_('select.genericList', $wpjobportal_countries, 'sec_1[license_country]', 'class="inputbox ' . $license_country_required . ' wpjobportal-cbo" ' . '', 'value', 'text', $wpjobportal_application->license_country);

    //         $wpjobportal_resumelists['gender'] = JHTML::_('select.genericList', $wpjobportal_gender, 'sec_1[gender]', 'class="inputbox ' . $wpjobportal_gender_required . ' wpjobportal-cbo" ' . '', 'value', 'text', $wpjobportal_application->gender);
    //         $wpjobportal_resumelists['driving_license'] = JHTML::_('select.genericList', $driving_license, 'sec_1[driving_license]', 'class="inputbox ' . $driving_license_required . ' wpjobportal-cbo" ' . '', 'value', 'text', $wpjobportal_application->driving_license);

    //         $wpjobportal_resumelists['job_category'] = JHTML::_('select.genericList', $wpjobportal_job_categories, 'sec_1[job_category]', 'class="inputbox ' . $wpjobportal_category_required . ' wpjobportal-cbo" ' . 'onChange="return fj_getsubcategories(\'job_subcategory\', this.value)"', 'value', 'text', $wpjobportal_application->job_category);
    //         if(!empty($wpjobportal_job_subcategories))
    //             $wpjobportal_resumelists['job_subcategory'] = JHTML::_('select.genericList', $wpjobportal_job_subcategories, 'sec_1[job_subcategory]', 'class="inputbox ' . $wpjobportal_subcategory_required . ' wpjobportal-cbo" ' . '', 'value', 'text', $wpjobportal_application->job_subcategory);
    //         else
    //             $wpjobportal_resumelists['job_subcategory'] = JHTML::_('select.genericList', array(), 'sec_1[job_subcategory]', 'class="inputbox ' . $wpjobportal_subcategory_required . ' wpjobportal-cbo" ' . '', 'value', 'text', $wpjobportal_application->job_subcategory);

    //         $wpjobportal_resumelists['jobtype'] = JHTML::_('select.genericList', $wpjobportal_job_type, 'sec_1[jobtype]', 'class="inputbox ' . $workpreference_required . ' wpjobportal-cbo" ' . '', 'value', 'text', $wpjobportal_application->jobtype);
    //         $wpjobportal_resumelists['jobsalaryrange'] = JHTML::_('select.genericList', $wpjobportal_job_salaryrange, 'sec_1[jobsalaryrange]', 'class="inputbox ' . $wpjobportal_salary_required . ' wpjobportal-cbo" ' . '', 'value', 'text', $wpjobportal_application->jobsalaryrange);
    //         $wpjobportal_resumelists['desired_salary'] = JHTML::_('select.genericList', $wpjobportal_job_salaryrange, 'sec_1[desired_salary]', 'class="inputbox ' . $wpjobportal_expsalary_required . ' wpjobportal-cbo" ' . '', 'value', 'text', $wpjobportal_application->desired_salary);
    //         $wpjobportal_resumelists['jobsalaryrangetypes'] = JHTML::_('select.genericList', $wpjobportal_job_salaryrangetype, 'sec_1[jobsalaryrangetype]', 'class="inputbox wpjobportal-cbo" ' . '', 'value', 'text', $wpjobportal_application->jobsalaryrangetype);
    //         $wpjobportal_resumelists['djobsalaryrangetypes'] = JHTML::_('select.genericList', $wpjobportal_job_salaryrangetype, 'sec_1[djobsalaryrangetype]', 'class="inputbox wpjobportal-cbo" ' . '', 'value', 'text', $wpjobportal_application->djobsalaryrangetype);
    //         $wpjobportal_resumelists['currencyid'] = JHTML::_('select.genericList', $this->getJSModel('currency')->getCurrency(), 'sec_1[currencyid]', 'class="inputbox wpjobportal-cbo" ' . '', 'value', 'text', $wpjobportal_application->currencyid);
    //         $wpjobportal_resumelists['dcurrencyid'] = JHTML::_('select.genericList', $this->getJSModel('currency')->getCurrency(), 'sec_1[dcurrencyid]', 'class="inputbox wpjobportal-cbo" ' . '', 'value', 'text', $wpjobportal_application->dcurrencyid);
    //         $wpjobportal_resumelists['experienceid'] = JHTML::_('select.genericList', $wpjobportal_experiences, 'sec_1[experienceid]', 'class="inputbox wpjobportal-cbo" ' . '', 'value', 'text', $wpjobportal_application->experienceid);
    //     } else {
    //         $wpjobportal_resumelists['license_country'] = JHTML::_('select.genericList', $wpjobportal_countries, 'sec_1[license_country]', 'class="inputbox ' . $license_country_required . ' wpjobportal-cbo" ' . '', 'value', 'text', '');
    //         $wpjobportal_resumelists['nationality'] = JHTML::_('select.genericList', $wpjobportal_countries, 'sec_1[nationality]', 'class="inputbox ' . $wpjobportal_nationality_required . ' wpjobportal-cbo" ' . '', 'value', 'text', '');
    //         $wpjobportal_resumelists['gender'] = JHTML::_('select.genericList', $wpjobportal_gender, 'sec_1[gender]', 'class="inputbox ' . $wpjobportal_gender_required . ' wpjobportal-cbo" ' . '', 'value', 'text', '');
    //         $wpjobportal_resumelists['driving_license'] = JHTML::_('select.genericList', $driving_license, 'sec_1[driving_license]', 'class="inputbox ' . $driving_license_required . ' wpjobportal-cbo" ' . '', 'value', 'text', '');

    //         $wpjobportal_resumelists['job_category'] = JHTML::_('select.genericList', $wpjobportal_job_categories, 'sec_1[job_category]', 'class="inputbox ' . $wpjobportal_category_required . ' wpjobportal-cbo" ' . 'onChange="fj_getsubcategories(\'job_subcategory\', this.value)"', 'value', 'text', $wpjobportal_defaultCategory);
    //         $wpjobportal_resumelists['job_subcategory'] = JHTML::_('select.genericList', $wpjobportal_job_subcategories, 'sec_1[job_subcategory]', 'class="inputbox ' . $wpjobportal_subcategory_required . ' wpjobportal-cbo" ' . '', 'value', 'text', '');

    //         $wpjobportal_resumelists['jobtype'] = JHTML::_('select.genericList', $wpjobportal_job_type, 'sec_1[jobtype]', 'class="inputbox ' . $workpreference_required . ' wpjobportal-cbo" ' . '', 'value', 'text', $wpjobportal_defaultJobtype);

    //     }
    //     return $wpjobportal_resumelists;
    // }
/* new code for resume start */

 function getAllEmpApps() {

        $this->sorting();
        //Filter
        $wpjobportal_searchtitle = wpjobportal::$_search['resumes']['searchtitle'];
        $wpjobportal_searchname = wpjobportal::$_search['resumes']['searchname'];
        $wpjobportal_searchjobcategory = wpjobportal::$_search['resumes']['searchjobcategory'];
        $wpjobportal_searchjobtype = wpjobportal::$_search['resumes']['searchjobtype'];
        $wpjobportal_searchjobsalaryrange = wpjobportal::$_search['resumes']['searchjobsalaryrange'];
        $wpjobportal_status = wpjobportal::$_search['resumes']['status'];
        $wpjobportal_datestart = wpjobportal::$_search['resumes']['datestart'];
        $wpjobportal_dateend = wpjobportal::$_search['resumes']['dateend'];
        $featured = wpjobportal::$_search['resumes']['featured'];

        wpjobportal::$_data['filter']['searchtitle'] = $wpjobportal_searchtitle;
        wpjobportal::$_data['filter']['searchname'] = $wpjobportal_searchname;
        wpjobportal::$_data['filter']['searchjobcategory'] = $wpjobportal_searchjobcategory;
        wpjobportal::$_data['filter']['searchjobtype'] = $wpjobportal_searchjobtype;
        wpjobportal::$_data['filter']['searchjobsalaryrange'] = $wpjobportal_searchjobsalaryrange;
        wpjobportal::$_data['filter']['status'] = $wpjobportal_status;
        wpjobportal::$_data['filter']['datestart'] = $wpjobportal_datestart;
        wpjobportal::$_data['filter']['dateend'] = $wpjobportal_dateend;
        wpjobportal::$_data['filter']['featured'] = $featured;

        if ($wpjobportal_searchjobcategory)
            if (is_numeric($wpjobportal_searchjobcategory) == false)
                return false;
        if ($wpjobportal_searchjobtype)
            if (is_numeric($wpjobportal_searchjobtype) == false)
                return false;
        if ($wpjobportal_searchjobsalaryrange)
            if (is_numeric($wpjobportal_searchjobsalaryrange) == false)
                return false;

        $wpjobportal_inquery = "";
        if ($wpjobportal_searchtitle)
            $wpjobportal_inquery .= " AND LOWER(app.application_title) LIKE '%" . esc_sql($wpjobportal_searchtitle) . "%'";
        if ($wpjobportal_searchname) {
            $wpjobportal_inquery .= " AND (";
            $wpjobportal_inquery .= " LOWER(app.first_name) LIKE '%" . esc_sql($wpjobportal_searchname) . "%'";
            $wpjobportal_inquery .= " OR LOWER(app.last_name) LIKE '%" . esc_sql($wpjobportal_searchname) . "%'";
            // $wpjobportal_inquery .= " OR LOWER(app.middle_name) LIKE '%" . esc_sql($wpjobportal_searchname) . "%'";
            $wpjobportal_inquery .= " )";
        }
        if (is_numeric($wpjobportal_searchjobcategory))
            $wpjobportal_inquery .= " AND app.job_category = " . esc_sql($wpjobportal_searchjobcategory);
        if (is_numeric($wpjobportal_searchjobtype))
            $wpjobportal_inquery .= " AND app.jobtype = " . esc_sql($wpjobportal_searchjobtype);
        if (is_numeric($wpjobportal_searchjobsalaryrange)){
            $wpjobportal_inquery .= " AND (SELECT rangestart FROM `".wpjobportal::$_db->prefix."wj_portal_salaryrange` WHERE id = ".esc_sql($wpjobportal_searchjobsalaryrange).") >= salarystart.rangestart AND (SELECT rangestart FROM `".wpjobportal::$_db->prefix."wj_portal_salaryrange` WHERE id = ".esc_sql($wpjobportal_searchjobsalaryrange).") <= salarystart.rangeend";
        }
        if ($wpjobportal_status != null) {
            if (is_numeric($wpjobportal_status)) {
                $wpjobportal_inquery .= " AND app.status = " . esc_sql($wpjobportal_status);
            }
        }
        if ($wpjobportal_datestart != null) {
            $wpjobportal_datestart = gmdate('Y-m-d',strtotime($wpjobportal_datestart));
            $wpjobportal_inquery .= " AND DATE(app.created) >=  '" . esc_sql($wpjobportal_datestart) . "' ";
        }

        if ($wpjobportal_dateend != null) {
            $wpjobportal_dateend = gmdate('Y-m-d',strtotime($wpjobportal_dateend));
            $wpjobportal_inquery .= " AND DATE(app.created) <=  '" . esc_sql($wpjobportal_dateend) . "'";
        }
        $wpjobportal_curdate = gmdate('Y-m-d');
        if ($featured != null) {
            $wpjobportal_inquery .= " AND app.isfeaturedresume = 1 AND DATE(app.startfeatureddate) <= '".esc_sql($wpjobportal_curdate)."' AND DATE(app.endfeatureddate) >= '".esc_sql($wpjobportal_curdate)."'";
        }
        //Pagination
        $query = "SELECT COUNT(app.id) FROM " . wpjobportal::$_db->prefix . "wj_portal_resume AS app
                WHERE app.status <> 0 AND app.quick_apply <> 1 ";
        $query.=$wpjobportal_inquery;

        $wpjobportal_total = wpjobportaldb::get_var($query);
        wpjobportal::$_data['total'] = $wpjobportal_total;
        wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($wpjobportal_total);

        //Data
        $query = "SELECT app.uid,app.id,app.endfeatureddate, app.application_title,app.first_name, app.last_name,app.jobtype,app.photo,app.salaryfixed,app.created, app.status, cat.cat_title,app.id AS resumeid
                , jobtype.title AS jobtypetitle,app.isfeaturedresume,city.id as city,jobtype.color
            FROM " . wpjobportal::$_db->prefix . "wj_portal_resume AS app
            LEFT JOIN " . wpjobportal::$_db->prefix . "wj_portal_categories AS cat ON app.job_category = cat.id
            LEFT JOIN " . wpjobportal::$_db->prefix . "wj_portal_jobtypes AS jobtype    ON app.jobtype = jobtype.id
            LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON city.id = (SELECT address_city FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumeaddresses` WHERE resumeid = app.id ORDER BY id DESC LIMIT 1)
            WHERE app.status <> 0  AND app.quick_apply <> 1 ";
        $query.=$wpjobportal_inquery;
        $query.=" ORDER BY " . wpjobportal::$_data['sorting'];
        $query.=" LIMIT " . WPJOBPORTALpagination::$_offset . "," . WPJOBPORTALpagination::$_limit;
        wpjobportal::$_data[0] = wpjobportaldb::get_results($query);
        wpjobportal::$wpjobportal_data['fields'] = wpjobportal::$_wpjpfieldordering->getFieldsOrderingforView(3);
        wpjobportal::$_data['config'] = wpjobportal::$_config->getConfigByFor('resume');
        return;
    }


    function sortingrescat() {
        // $wpjobportal_pagenum = WPJOBPORTALrequest::getVar('pagenum');
        // wpjobportal::$_data['sorton'] = isset(wpjobportal::$_search['myresume']['sorton']) ? wpjobportal::$_search['myresume']['sorton'] : 6;
        // wpjobportal::$_data['sortby'] = isset(wpjobportal::$_search['myresume']['sortby']) ? wpjobportal::$_search['myresume']['sortby'] : 2;
        wpjobportal::$_data['sorton'] = WPJOBPORTALrequest::getVar('sorton', 'post', 6);
        wpjobportal::$_data['sortby'] = WPJOBPORTALrequest::getVar('sortby', 'post', 2);

        switch (wpjobportal::$_data['sorton']) {
            case 1: // appilcation title
                wpjobportal::$_data['sorting'] = ' resume.application_title ';
                break;
            case 2: // first name
                wpjobportal::$_data['sorting'] = ' resume.first_name ';
                break;
            case 3: // category
                wpjobportal::$_data['sorting'] = ' category.cat_title ';
                break;
            case 4: // job type
                wpjobportal::$_data['sorting'] = ' resume.jobtype ';
                break;
            case 5: // location
                wpjobportal::$_data['sorting'] = ' city.name ';
                break;
            case 6: // created
                wpjobportal::$_data['sorting'] = ' resume.created ';
                break;
            case 7: // status
                wpjobportal::$_data['sorting'] = ' resume.status ';
                break;
        }
        if (wpjobportal::$_data['sortby'] == 1) {
            wpjobportal::$_data['sorting'] .= ' ASC ';
        } else {
            wpjobportal::$_data['sorting'] .= ' DESC ';
        }
        wpjobportal::$_data['combosort'] = wpjobportal::$_data['sorton'];
    }


    function sorting() {
        $wpjobportal_pagenum = WPJOBPORTALrequest::getVar('pagenum');
        wpjobportal::$_data['sorton'] = wpjobportal::$_search['resumes']['sorton'];
        wpjobportal::$_data['sortby'] = wpjobportal::$_search['resumes']['sortby'];
        switch (wpjobportal::$_data['sorton']) {
            case 1: // appilcation title
                wpjobportal::$_data['sorting'] = ' app.application_title ';
                break;
            case 2: // first name
                wpjobportal::$_data['sorting'] = ' app.first_name ';
                break;
            case 3: // category
                wpjobportal::$_data['sorting'] = ' cat.cat_title ';
                break;
            case 4: // job type
                wpjobportal::$_data['sorting'] = ' app.jobtype ';
                break;
            case 5: // location
                wpjobportal::$_data['sorting'] = ' city.name ';
                break;
            case 6: // created
                wpjobportal::$_data['sorting'] = ' app.created ';
                break;
            case 7: // status
                wpjobportal::$_data['sorting'] = ' app.status ';
                break;
        }
        if (wpjobportal::$_data['sortby'] == 1) {
            wpjobportal::$_data['sorting'] .= ' ASC ';
        } else {
            wpjobportal::$_data['sorting'] .= ' DESC ';
        }
        wpjobportal::$_data['combosort'] = wpjobportal::$_data['sorton'];
    }

    function getAllUnapprovedEmpApps() {
        $this->sorting();
        //Filter
        $wpjobportal_searchtitle = wpjobportal::$_search['resumes']['searchtitle'];
        $wpjobportal_searchname = wpjobportal::$_search['resumes']['searchname'];
        $wpjobportal_searchjobcategory = wpjobportal::$_search['resumes']['searchjobcategory'];
        $wpjobportal_searchjobtype = wpjobportal::$_search['resumes']['searchjobtype'];
        $wpjobportal_searchjobsalaryrange = wpjobportal::$_search['resumes']['searchjobsalaryrange'];
        $wpjobportal_status = wpjobportal::$_search['resumes']['status'];
        $wpjobportal_datestart = wpjobportal::$_search['resumes']['datestart'];
        $wpjobportal_dateend = wpjobportal::$_search['resumes']['dateend'];
        $featured = wpjobportal::$_search['resumes']['featured'];

        wpjobportal::$_data['filter']['searchtitle'] = $wpjobportal_searchtitle;
        wpjobportal::$_data['filter']['searchname'] = $wpjobportal_searchname;
        wpjobportal::$_data['filter']['searchjobcategory'] = $wpjobportal_searchjobcategory;
        wpjobportal::$_data['filter']['searchjobtype'] = $wpjobportal_searchjobtype;
        wpjobportal::$_data['filter']['searchjobsalaryrange'] = $wpjobportal_searchjobsalaryrange;
        wpjobportal::$_data['filter']['status'] = $wpjobportal_status;
        wpjobportal::$_data['filter']['datestart'] = $wpjobportal_datestart;
        wpjobportal::$_data['filter']['dateend'] = $wpjobportal_dateend;
        wpjobportal::$_data['filter']['featured'] = $featured;

        if ($wpjobportal_searchjobcategory)
            if (is_numeric($wpjobportal_searchjobcategory) == false)
                return false;
        if ($wpjobportal_searchjobtype)
            if (is_numeric($wpjobportal_searchjobtype) == false)
                return false;
        if ($wpjobportal_searchjobsalaryrange)
            if (is_numeric($wpjobportal_searchjobsalaryrange) == false)
                return false;

        $wpjobportal_inquery = "";
        if ($wpjobportal_searchtitle)
            $wpjobportal_inquery .= " AND LOWER(app.application_title) LIKE '%" . esc_sql($wpjobportal_searchtitle) . "%'";
        if ($wpjobportal_searchname) {
            $wpjobportal_inquery .= " AND (";
            $wpjobportal_inquery .= " LOWER(app.first_name) LIKE '%" . esc_sql($wpjobportal_searchname) . "%'";
            $wpjobportal_inquery .= " OR LOWER(app.last_name) LIKE '%" . esc_sql($wpjobportal_searchname) . "%'";
            //$wpjobportal_inquery .= " OR LOWER(app.middle_name) LIKE '%" . esc_sql($wpjobportal_searchname) . "%'";
            $wpjobportal_inquery .= " )";
        }
        if (is_numeric($wpjobportal_searchjobcategory))
            $wpjobportal_inquery .= " AND app.job_category = " . esc_sql($wpjobportal_searchjobcategory);
        if (is_numeric($wpjobportal_searchjobtype))
            $wpjobportal_inquery .= " AND app.jobtype = " . esc_sql($wpjobportal_searchjobtype);
        if (is_numeric($wpjobportal_searchjobsalaryrange))
            $wpjobportal_inquery .= " AND app.jobsalaryrangetype = " . esc_sql($wpjobportal_searchjobsalaryrange);
        if ($wpjobportal_status != null) {
            if (is_numeric($wpjobportal_status))
                $wpjobportal_inquery .= " AND app.status = " . esc_sql($wpjobportal_status);
        }

        if ($wpjobportal_datestart != null) {
            $wpjobportal_datestart = gmdate('Y-m-d',strtotime($wpjobportal_datestart));
            $wpjobportal_inquery .= " AND DATE(app.created) >=  '" . esc_sql($wpjobportal_datestart) . "' ";
        }

        if ($wpjobportal_dateend != null) {
            $wpjobportal_dateend = gmdate('Y-m-d',strtotime($wpjobportal_dateend));
            $wpjobportal_inquery .= " AND DATE(app.created) <=  '" . esc_sql($wpjobportal_dateend) . "'";
        }
        
        $wpjobportal_curdate = gmdate('Y-m-d');
        if ($featured != null) {
            $wpjobportal_inquery .= " AND app.isfeaturedresume = 1 AND DATE(app.startfeatureddate) <= '".esc_sql($wpjobportal_curdate)."' AND DATE(app.endfeatureddate) >= '".esc_sql($wpjobportal_curdate)."'";
        }

        //Pagination
        $query = "SELECT COUNT(id) FROM " . wpjobportal::$_db->prefix . "wj_portal_resume AS app
                WHERE (app.status = 0) AND app.quick_apply <> 1";
        $query.=$wpjobportal_inquery;

        $wpjobportal_total = wpjobportaldb::get_var($query);
        wpjobportal::$_data['total'] = $wpjobportal_total;
        wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($wpjobportal_total);

        //Data
        $query = "SELECT app.photo,app.id,app.salaryfixed as salaryfixed, app.application_title,app.first_name, app.last_name, app.jobtype,
                app.created, app.status, app.isfeaturedresume,app.endfeatureddate, cat.cat_title,jobtype.title AS jobtypetitle,city.id as city,jobtype.color as color
            FROM " . wpjobportal::$_db->prefix . "wj_portal_resume AS app
            LEFT JOIN " . wpjobportal::$_db->prefix . "wj_portal_categories AS cat ON app.job_category = cat.id
            LEFT JOIN " . wpjobportal::$_db->prefix . "wj_portal_jobtypes AS jobtype    ON app.jobtype = jobtype.id
            LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON city.id = (SELECT address_city FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumeaddresses` WHERE resumeid = app.id ORDER BY id DESC LIMIT 1)

            WHERE (app.status = 0 OR app.isfeaturedresume = 0 ) AND app.quick_apply <> 1 ";
        $query.=$wpjobportal_inquery;
        $query.=" ORDER BY " . wpjobportal::$_data['sorting'];
        $query.=" LIMIT " . WPJOBPORTALpagination::$_offset . "," . WPJOBPORTALpagination::$_limit;
        wpjobportal::$_data[0] = wpjobportaldb::get_results($query);
        wpjobportal::$wpjobportal_data['fields'] = wpjobportal::$_wpjpfieldordering->getFieldsOrderingforView(3);
        return;
    }

    function getUserStatsResumes($wpjobportal_resumeuid) {
        if (is_numeric($wpjobportal_resumeuid) == false)
            return false;
        //pagination
        $query = "SELECT COUNT(resume.id) FROM " . wpjobportal::$_db->prefix . "wj_portal_resume AS resume WHERE resume.uid =" . esc_sql($wpjobportal_resumeuid);
        $wpjobportal_total = wpjobportaldb::get_var($query);
        wpjobportal::$_data['total'] = $wpjobportal_total;
        wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($wpjobportal_total);

        //Data
        $query = "SELECT resume.id,resume.application_title,resume.first_name,resume.last_name,cat.cat_title,resume.created,resume.status
                    FROM " . wpjobportal::$_db->prefix . "wj_portal_resume AS resume
                    LEFT JOIN " . wpjobportal::$_db->prefix . "wj_portal_categories AS cat ON cat.id=resume.job_category
                    WHERE resume.uid = " . esc_sql($wpjobportal_resumeuid);
        $query .= " ORDER BY resume.first_name";
        $query.=" LIMIT " . WPJOBPORTALpagination::$_offset . "," . WPJOBPORTALpagination::$_limit;

        wpjobportal::$_data[0] = wpjobportaldb::get_results($query);
        return;
    }

    function getResumeSearch() {
        //Filters
        $title = WPJOBPORTALrequest::getVar('title');
        $wpjobportal_name = WPJOBPORTALrequest::getVar('name');
        $wpjobportal_nationality = WPJOBPORTALrequest::getVar('nationality');
        $wpjobportal_gender = WPJOBPORTALrequest::getVar('gender');
        $wpjobportal_iamavailable = WPJOBPORTALrequest::getVar('iamavailable', 0); // b/c when checkbox is unchecked it remain get its last value
        $wpjobportal_jobcategory = WPJOBPORTALrequest::getVar('jobcategory');
        $wpjobportal_jobtype = WPJOBPORTALrequest::getVar('jobtype');
        $wpjobportal_education = WPJOBPORTALrequest::getVar('heighestfinisheducation');
        $currency = WPJOBPORTALrequest::getVar('currency');
        $zipcode = WPJOBPORTALrequest::getVar('zipcode');
        $wpjobportal_jobstatus = WPJOBPORTALrequest::getVar('jobstatus');

        wpjobportal::$_data['filter']['title'] = $title;
        wpjobportal::$_data['filter']['name'] = $wpjobportal_name;
        wpjobportal::$_data['filter']['nationality'] = $wpjobportal_nationality;
        wpjobportal::$_data['filter']['gender'] = $wpjobportal_gender;
        wpjobportal::$_data['filter']['iamavailable'] = $wpjobportal_iamavailable;
        wpjobportal::$_data['filter']['jobcategory'] = $wpjobportal_jobcategory;
        wpjobportal::$_data['filter']['jobtype'] = $wpjobportal_jobtype;
        wpjobportal::$_data['filter']['heighestfinisheducation'] = $wpjobportal_education;
        wpjobportal::$_data['filter']['currency'] = $currency;
        wpjobportal::$_data['filter']['zipcode'] = $zipcode;
        wpjobportal::$_data['filter']['jobstatus'] = $wpjobportal_jobstatus;

        if ($wpjobportal_gender != '')
            if (is_numeric($wpjobportal_gender) == false)
                return false;
        if ($wpjobportal_iamavailable != '')
            if (is_numeric($wpjobportal_iamavailable) == false)
                return false;
        if ($wpjobportal_jobcategory != '')
            if (is_numeric($wpjobportal_jobcategory) == false)
                return false;
        if ($wpjobportal_jobtype != '')
            if (is_numeric($wpjobportal_jobtype) == false)
                return false;
        if ($wpjobportal_jobsalaryrange != '')
            if (is_numeric($wpjobportal_jobsalaryrange) == false)
                return false;
        if ($wpjobportal_education != '')
            if (is_numeric($wpjobportal_education) == false)
                return false;

        if ($currency != '')
            if (is_numeric($currency) == false)
                return false;
        if ($zipcode != '')
            if (is_numeric($zipcode) == false)
                return false;

        $wherequery = '';
        if ($title != '')
            $wherequery .= " AND resume.application_title LIKE '%" . esc_sql(wpjobportalphplib::wpJP_str_replace("'", "", $title)) . "%'";
        if ($wpjobportal_name != '') {
            $wherequery .= " AND (";
            $wherequery .= " LOWER(resume.first_name) LIKE '%" . esc_sql($wpjobportal_name) . "%'";
            $wherequery .= " OR LOWER(resume.last_name) LIKE '%" . esc_sql($wpjobportal_name) . "%'";
            //$wherequery .= " OR LOWER(resume.middle_name) LIKE '%" . esc_sql($wpjobportal_name) . "%'";
            $wherequery .= " )";
        }

        if ($wpjobportal_nationality != '' && is_numeric($wpjobportal_nationality))
            $wherequery .= " AND resume.nationality = '" . esc_sql($wpjobportal_nationality) . "'";
        if ($wpjobportal_gender != '' && is_numeric($wpjobportal_gender))
            $wherequery .= " AND resume.gender = " . esc_sql($wpjobportal_gender);
        if ($wpjobportal_iamavailable != '' && is_numeric($wpjobportal_iamavailable))
            $wherequery .= " AND resume.iamavailable = " . esc_sql($wpjobportal_iamavailable);
        if ($wpjobportal_jobcategory != '' && is_numeric($wpjobportal_jobcategory))
            $wherequery .= " AND resume.job_category = " . esc_sql($wpjobportal_jobcategory);
        if ($wpjobportal_jobtype != '' && is_numeric($wpjobportal_jobtype))
            $wherequery .= " AND resume.jobtype = " . esc_sql($wpjobportal_jobtype);
        if ($wpjobportal_jobsalaryrange != '' && is_numeric($wpjobportal_jobsalaryrange))
            $wherequery .= " AND resume.jobsalaryrange = " . esc_sql($wpjobportal_jobsalaryrange);

        //Pagination
        $query = "SELECT count(resume.id)
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON resume.job_category = cat.id
                WHERE resume.status = 1 AND resume.searchable = 1 ";
        $query .= $wherequery;

        $wpjobportal_total = wpjobportaldb::get_var($query);
        wpjobportal::$_data['total'] = $wpjobportal_total;
        wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($wpjobportal_total);

        //Data
        $query = "SELECT resume.*, cat.cat_title, jobtype.title AS jobtypetitle
                , salary.rangestart, salary.rangeend , currency.symbol
                ,salarytype.title AS salarytype
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON resume.job_category = cat.id
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON resume.jobtype = jobtype.id
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_currencies` AS currency ON currency.id = resume.currencyid
               ";
        $query .= "WHERE resume.status = 1 AND resume.searchable = 1 ";
        $query .= $wherequery;
        $query.=" LIMIT " . WPJOBPORTALpagination::$_offset . "," . WPJOBPORTALpagination::$_limit;

        wpjobportal::$_data[0] = wpjobportaldb::get_results($query);
        return;
    }

    function rejectQueueAllResumesModel($wpjobportal_id, $wpjobportal_actionid) {
        /*
         * *  4 for All
         */
        if (!is_numeric($wpjobportal_id))
            return false;
        $wpjobportal_result = $this->rejectQueueResumeModel($wpjobportal_id);
        return $wpjobportal_result;
    }

    function approveQueueAllResumesModel($wpjobportal_id, $wpjobportal_actionid) {
        /*
         * *  4 for All
         */
        if (!is_numeric($wpjobportal_id))
            return false;
        $wpjobportal_result = $this->approveQueueResumeModel($wpjobportal_id);
        return $wpjobportal_result;
    }

    function rejectQueueResumeModel($wpjobportal_id) {
        if (is_numeric($wpjobportal_id) == false) return false;
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('resume');
        if($wpjobportal_row->load($wpjobportal_id)){
            $wpjobportal_row->columns['status'] = -1;
            if(!$wpjobportal_row->store()){
                return WPJOBPORTAL_REJECT_ERROR;
            }
        }else{
            return WPJOBPORTAL_REJECT_ERROR;
        }
        WPJOBPORTALincluder::getJSModel('emailtemplate')->sendMail(3, 2, $wpjobportal_id); //3 for resume. 2 for resume approve or reject
        return WPJOBPORTAL_REJECTED;
    }

    function rejectQueueFeatureResumeModel($wpjobportal_id) {
        if (is_numeric($wpjobportal_id) == false) return false;
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('resume');
        if($wpjobportal_row->load($wpjobportal_id)){
            $wpjobportal_row->columns['isfeaturedresume'] = -1;
            if(!$wpjobportal_row->store()){
                return WPJOBPORTAL_REJECT_ERROR;
            }
        }else{
            return WPJOBPORTAL_REJECT_ERROR;
        }
        WPJOBPORTALincluder::getJSModel('emailtemplate')->sendMail(3, 4, $wpjobportal_id); //3 for resume. 4 for feature resume approve or reject
        return WPJOBPORTAL_REJECTED;
    }

    function approveQueueResumeModel($wpjobportal_id) {
        if (is_numeric($wpjobportal_id) == false)
            return false;
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('resume');
        if($wpjobportal_row->load($wpjobportal_id)){
            $wpjobportal_row->columns['status'] = 1;
            if(!$wpjobportal_row->store()){
                return WPJOBPORTAL_APPROVE_ERROR;
            }
        }else{
            return WPJOBPORTAL_APPROVE_ERROR;
        }
        WPJOBPORTALincluder::getJSModel('emailtemplate')->sendMail(3, 2, $wpjobportal_id); //3 for resume. 3 for resume approve or reject
        return WPJOBPORTAL_APPROVED;
    }

    function approveQueueFeatureResumeModel($wpjobportal_id) {
        if (is_numeric($wpjobportal_id) == false)
            return false;
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('resume');
        if($wpjobportal_row->load($wpjobportal_id)){
            $wpjobportal_row->columns['isfeaturedresume'] = 1;
            if(!$wpjobportal_row->store()){
                return WPJOBPORTAL_APPROVE_ERROR;
            }
        }else{
            return WPJOBPORTAL_APPROVE_ERROR;
        }
        WPJOBPORTALincluder::getJSModel('emailtemplate')->sendMail(3, 4, $wpjobportal_id); //3 for resume. 4 for feature resume approve or reject
        return WPJOBPORTAL_APPROVED;
    }

    function getResumes_Widget($wpjobportal_resumetype, $wpjobportal_noofresumes) {
        if ((!is_numeric($wpjobportal_resumetype)) || ( !is_numeric($wpjobportal_noofresumes)))
            return false;


        if ($wpjobportal_resumetype == 1) { //newest
            $wpjobportal_inquery = ' ORDER BY resume.created DESC ';
        } elseif ($wpjobportal_resumetype == 2) { //top
            $wpjobportal_inquery = ' ORDER BY resume.hits DESC ';
        } elseif ($wpjobportal_resumetype == 4) { //featurerd
            $wpjobportal_inquery = ' AND resume.isfeaturedresume = 1 AND DATE(resume.endfeatureddate) >= CURDATE() ';
            $wpjobportal_inquery .= ' ORDER BY resume.created DESC ';
        } else {
            return []; // '' was casuing issues
        }

        $wpjobportal_id = "resume.id AS id";
        $alias = ",CONCAT(resume.alias,'-',resume.id) AS resumealiasid ";
        $query = "SELECT resume.id AS resumeid,
                $wpjobportal_id, resume.application_title AS applicationtitle, CONCAT(resume.first_name,' ', resume.last_name) AS name, resume.photo,jobtype.color as jobtypecolor
                ,resume.created AS created , cat.cat_title, jobtype.title AS jobtypetitle,nationality.name AS nationalityname
                $alias,(SELECT address.address_city FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumeaddresses` AS address WHERE address.resumeid = resume.id LIMIT 1) AS city,resume.email_address

                FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON resume.job_category = cat.id
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON resume.jobtype = jobtype.id
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_countries` AS nationality ON nationality.id=resume.nationality
                WHERE resume.status = 1 ";
        $query .= $wpjobportal_inquery;
        if ($wpjobportal_noofresumes != -1 && is_numeric($wpjobportal_noofresumes))
            $query .=" LIMIT " . esc_sql($wpjobportal_noofresumes);

        $wpjobportal_results = wpjobportaldb::get_results($query);
        foreach ($wpjobportal_results as $d) {
            $d->location = WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($d->city);
        }
        return $wpjobportal_results;
    }

     function isYoursResume($wpjobportal_id, $wpjobportal_uid) {
        if (!is_numeric($wpjobportal_id))
            return false;
        if (current_user_can( 'manage_options' )){
            return true;
        }
        if (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
            $conflag = wpjobportal::$_config->getConfigurationByConfigName('visitor_can_add_resume');
            if ($conflag == 1) {
                if (isset($_SESSION['wp-wpjobportal']) && isset($_SESSION['wp-wpjobportal']['resumeid'])) {
                    if ($wpjobportal_id == $_SESSION['wp-wpjobportal']['resumeid']) {
                        return true;
                    } else {
                        return false;
                    }
                }
            }
        }
        if (!is_numeric($wpjobportal_uid))
            return false;
        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` WHERE id = " . esc_sql($wpjobportal_id) . " AND uid = ". esc_sql($wpjobportal_uid);
        $wpjobportal_result = wpjobportaldb::get_var($query);
        if ($wpjobportal_result == 0)
            return false;
        else
            return true;
    }

    function cancelResumeSectionAjax() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'cancel-resume-section-ajax') ) {
            die( 'Security check Failed' );
        }
        $wpjobportal_section = WPJOBPORTALrequest::getVar('section');
        $wpjobportal_data = WPJOBPORTALrequest::get('post');
        $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        $wpjobportal_data['uid'] = $wpjobportal_uid;
        $wpjobportal_resumeid = $wpjobportal_data['resumeid'];
        $objectid = $wpjobportal_data['sectionid'];
        if ($wpjobportal_section != 'skills' && $wpjobportal_section != 'resume' && $wpjobportal_section != 'personal')
            if ($objectid)
                if (!is_numeric($objectid))
                    return false;
        $wpjobportal_result = null;
        $wpjobportal_resumelayout = WPJOBPORTALincluder::getObjectClass('resumeformlayout');
        $wpjobportal_fieldsordering = wpjobportal::$_wpjpfieldordering->getFieldsOrderingforForm(3); // resume fields
        wpjobportal::$_data[2] = array();
        foreach ($wpjobportal_fieldsordering AS $wpjobportal_field) {
            wpjobportal::$_data[2][$wpjobportal_field->section][$wpjobportal_field->field] = $wpjobportal_field->required;
        }
        switch ($wpjobportal_section) {
            case 'addresses':
                if (is_numeric($objectid))
                    wpjobportal::$_data[0]['address_section'][0] = $this->getResumeAddressSection($wpjobportal_resumeid, $wpjobportal_uid, $objectid);
                else
                    wpjobportal::$_data[0]['address_section'][0] = '';
                $wpjobportal_result = $wpjobportal_resumelayout->getAddressesSection(0, 1);
                break;
            case 'institutes':
                if (is_numeric($objectid))
                    wpjobportal::$_data[0]['institute_section'][0] = apply_filters('wpjobportal_addons_getResume_action_ajx_adm',false,'getResumeInstituteSection',$wpjobportal_resumeid,$wpjobportal_uid,$wpjobportal_sectionid);
                else
                    wpjobportal::$_data[0]['institute_section'][0] = '';
                $wpjobportal_result = apply_filters('wpjobportal_addons_view_resume_by_section_resume',false,'getEducationSection');
                break;
            case 'employers':
                if (is_numeric($objectid))
                    wpjobportal::$_data[0]['employer_section'][0] = $this->getResumeEmployerSection($wpjobportal_resumeid, $wpjobportal_uid, $objectid);
                else
                    wpjobportal::$_data[0]['employer_section'][0] = '';
                $wpjobportal_result = $wpjobportal_resumelayout->getEmployerSection(0, 1);
                break;
            case 'languages':
                if (is_numeric($objectid))
                    wpjobportal::$_data[0]['language_section'][0] = apply_filters('wpjobportal_addons_getResume_action_ajx_adm',false,'getResumeLanguageSection',$wpjobportal_resumeid,$wpjobportal_uid,$objectid);
                else
                    wpjobportal::$_data[0]['language_section'][0] = '';
                $wpjobportal_result = apply_filters('wpjobportal_addons_view_resume_by_section_resume',false,'getLanguageSection');
                break;
            case 'skills':
                    if(in_array('advanceresumebuilder', wpjobportal::$_active_addons)){
                        wpjobportal::$_data[0]['personal_section'] = $this->getResumePersonalSection($wpjobportal_resumeid, $wpjobportal_uid);
                        $wpjobportal_result = apply_filters('wpjobportal_addons_view_resume_by_section_resume',false,'getSkillSection');
                    }
                break;
            case 'personal':
                wpjobportal::$_data[0]['personal_section'] = $this->getResumePersonalSection($wpjobportal_resumeid, $wpjobportal_uid);
                wpjobportal::$_data[0]['file_section'] = $this->getResumeFilesSection($wpjobportal_resumeid, $wpjobportal_uid);
                wpjobportal::$wpjobportal_data['resumecontactdetail'] = true;
                $wpjobportal_result = $wpjobportal_resumelayout->getPersonalTopSection(1, 0);
                $wpjobportal_result .= '<div class="resume-section-title personal"><img class="heading-img" src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/personal-info.png" />' . esc_html(__('Personal information', 'wp-job-portal')) . '</div>';
                $wpjobportal_result .= $wpjobportal_resumelayout->getPersonalSection(0);
                break;
        }
        if ($wpjobportal_section != 'skills' && $wpjobportal_section != 'resume' && $wpjobportal_section != 'personal') {
            $canadd = $this->canAddMoreSection($wpjobportal_uid, $wpjobportal_resumeid, $wpjobportal_section);
            $anchor = '<a class="add" data-section="' . $wpjobportal_section . '"> + ' . esc_html(__('Add New', 'wp-job-portal')) . ' ' . wpjobportal::wpjobportal_getVariableValue($wpjobportal_section) . '</a>';
        } else {
            $canadd = 0;
            $anchor = '';
        }
        $wpjobportal_array = wp_json_encode(array('html' => $wpjobportal_result, 'canadd' => $canadd, 'anchor' => $anchor));
        return $wpjobportal_array;
    }

    function captchaValidate() {
        if (!is_user_logged_in()) {
            $wpjobportal_config_array = wpjobportal::$_config->getConfigByFor('captcha');
            if ($wpjobportal_config_array['resume_captcha'] == 1) {
                if ($wpjobportal_config_array['captcha_selection'] == 1) { // Google recaptcha
                    $gresponse = WPJOBPORTALrequest::getVar('g-recaptcha-response','post');
                    $resp = wpjobportal_googleRecaptchaHTTPPost($wpjobportal_config_array['recaptcha_privatekey'] , $gresponse);

                    if ($resp) {
                        return true;
                    } else {
                        wpjobportal::$_data['google_captchaerror'] = esc_html(__("Invalid captcha",'wp-job-portal'));
                        return false;
                    }

                } else { // own captcha
                    $wpjobportal_captcha = new WPJOBPORTALcaptcha;
                    $wpjobportal_result = $wpjobportal_captcha->checkCaptchaUserForm();
                    if ($wpjobportal_result == 1) {
                        return true;
                    } else {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    function getDataForParams($wpjobportal_section, $wpjobportal_data) {
        //custom field code start
        $wpjobportal_userfieldforjob = wpjobportal::$_wpjpfieldordering->getUserfieldsfor(3, $wpjobportal_section);
        $params = array();
        foreach ($wpjobportal_userfieldforjob AS $ufobj) {
            $wpjobportal_vardata = isset($wpjobportal_data[$ufobj->field]) ? $wpjobportal_data[$ufobj->field] : '';
            if($wpjobportal_vardata != ''){
                if($ufobj->userfieldtype == 'multiple'){
                    $wpjobportal_vardata = wpjobportalphplib::wpJP_explode(',', $wpjobportal_vardata[0]); // fixed index
                }
                if(is_array($wpjobportal_vardata)){
                    $wpjobportal_vardata = implode(', ', $wpjobportal_vardata);
                }
                $params[$ufobj->field] = wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_vardata);
            }
        }
        if (!empty($params)) {
            $params = wp_json_encode($params);
            return $params;
        } else {
            return false;
        }
        //custom field code end
    }

    function saveResumeSectionAjax() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'save-resume-section-ajax') ) {
            die( 'Security check Failed' );
        }
        $wpjobportal_section = WPJOBPORTALrequest::getVar('section');
        $wpjobportal_data = WPJOBPORTALrequest::get('post');
        if(!current_user_can('manage_options')){
            $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
            $wpjobportal_data['uid'] = $wpjobportal_uid;
        }else{
			$wpjobportal_uid = $this->getUidByResumeId($wpjobportal_data['resumeid']);
			$wpjobportal_data['uid'] = $wpjobportal_uid;
		}
        $wpjobportal_resumeid = $wpjobportal_data['resumeid'];
        $wpjobportal_row = null;
        switch ($wpjobportal_section) {
            case 'personal':
                $wpjobportal_row = WPJOBPORTALincluder::getJSTable('resume');
                $wpjobportal_data['id'] = $wpjobportal_resumeid;
                $params = $this->getDataForParams(1, $wpjobportal_data);
                $wpjobportal_data['params'] = $params == false ? '' : $params;
                break;
            case 'addresses':
                $wpjobportal_row = WPJOBPORTALincluder::getJSTable('resumeaddress');
                $params = $this->getDataForParams(2, $wpjobportal_data);
                $wpjobportal_data['params'] = $params == false ? '' : $params;
                break;
            case 'institutes':
                $wpjobportal_row = WPJOBPORTALincluder::getJSTable('resumeinstitute');
                $params = $this->getDataForParams(3, $wpjobportal_data);
                $wpjobportal_data['params'] = $params == false ? '' : $params;
                break;
            case 'employers':
                $wpjobportal_row = WPJOBPORTALincluder::getJSTable('resumeemployer');
                $params = $this->getDataForParams(4, $wpjobportal_data);
                $wpjobportal_data['params'] = $params == false ? '' : $params;
                break;
            // case 'references':
            //     $wpjobportal_row = WPJOBPORTALincluder::getJSTable('resumereference');
            //     $params = $this->getDataForParams(7, $wpjobportal_data);
            //     $wpjobportal_data['params'] = $params == false ? '' : $params;
            //     break;
            case 'languages':
                $wpjobportal_row = WPJOBPORTALincluder::getJSTable('resumelanguage');
                $params = $this->getDataForParams(8, $wpjobportal_data);
                $wpjobportal_data['params'] = $params == false ? '' : $params;
                break;
        }
        if ($wpjobportal_row != null) {
            if ($wpjobportal_section == 'personal') { // b/c of form ajax loop we have to unset the photo field if no photo selected
                if (isset($_FILES['photo']) && $_FILES['photo']['size'] > 0) {
                    //empty here to make it simple to understand
                } else {
                    unset($wpjobportal_data['photo']);
                }
                if (empty($wpjobportal_data['id'])) {
                    $wpjobportal_data['alias'] = wpjobportalphplib::wpJP_str_replace(' ', '-', $wpjobportal_data['application_title']);
                    $wpjobportal_data['created'] = gmdate('Y-m-d H:i:s');
                    $wpjobportal_data['status'] = wpjobportal::$_config->getConfigurationByConfigName('empautoapprove');
                } else {
                    if(current_user_can('manage_options')){
                        $wpjobportal_data['status'] = $wpjobportal_data['status'];
                    }else{
                        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('resume');
                        $wpjobportal_row->load($wpjobportal_data['id']);
                        $wpjobportal_data['status'] = $wpjobportal_row->status;
                    }
                }
                if(!empty($wpjobportal_data['date_of_birth']))
                    $wpjobportal_data['date_of_birth'] = gmdate('Y-m-d H:i:s',strtotime($wpjobportal_data['date_of_birth']));
                if(!empty($wpjobportal_data['date_start']))
                    $wpjobportal_data['date_start'] = gmdate('Y-m-d H:i:s',strtotime($wpjobportal_data['date_start']));
				$query = "SELECT * FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering WHERE field =  'searchable' AND fieldfor =3";
				$record = wpjobportal::$_db->get_row($query);
				if($record->published == 0 AND is_user_logged_in()){
					$wpjobportal_data['searchable'] = 1;
				}elseif($record->isvisitorpublished == 0){
					$wpjobportal_data['searchable'] = 1;
				}
                if (!$this->captchaValidate()) {
                    WPJOBPORTALMessages::setLayoutMessage(esc_html(__('Incorrect Captcha code', 'wp-job-portal')), 'error',$this->getMessagekey());
                    $wpjobportal_array = wp_json_encode(array('html' => 'error'));
                    return $wpjobportal_array;
                }
            }
            $wpjobportal_data = wpjobportal::wpjobportal_sanitizeData($wpjobportal_data);
            if (!$wpjobportal_row->bind($wpjobportal_data)) {
                return WPJOBPORTAL_SAVE_ERROR;
            }
            if (!$wpjobportal_row->store()) {
                return WPJOBPORTAL_SAVE_ERROR;
            }
            $objectid = $wpjobportal_row->id;
            if ($wpjobportal_section == 'personal') {
                $wpjobportal_resumeid = $wpjobportal_row->id;
            }
            //Check for the resume photo && files upload
            if ($wpjobportal_section == 'personal') {
                if (isset($_FILES['photo'])) {
                    $this->uploadPhoto($objectid);
                }
                if (isset($_FILES['resumefiles'])) {
                    $this->uploadResume($objectid);
                }
                // Save resumeid in session in case of visitor add resume is allowed
                if (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
                    $wpjobportal_visitor_can_add_resume = wpjobportal::$_config->getConfigurationByConfigName('visitor_can_add_resume');
                    if ($wpjobportal_visitor_can_add_resume == 1) {
                        $_SESSION['wp-wpjobportal']['resumeid'] = $wpjobportal_resumeid;
                    }
                }
                //Update credits log in case of new resume
                if ($wpjobportal_data['resumeid'] == '') {
                    $wpjobportal_actionid = $wpjobportal_data['creditid'];
                }
            }
        } elseif ($wpjobportal_section == 'skills') {
            $wpjobportal_skills = WPJOBPORTALrequest::getVar('skills');
// RESUME SKILL CUSTOM FIELD
            $params = $this->getDataForParams(5, $wpjobportal_data);
            if(!is_numeric($wpjobportal_resumeid)){
                return false;
            }
            $pquery = "SELECT params FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` WHERE id = " . esc_sql($wpjobportal_resumeid);
            $parmsvar = wpjobportal::$_db->get_var($pquery);
            $parray = array();
            if (isset($parmsvar) && !empty($parmsvar)) {
                $parray = json_decode($parmsvar);
            }
            if (isset($params) && !empty($params)) {
                $params = json_decode($params);
            }
            $params = (object) array_merge((array) $params, (array) $parray);
            $params = wp_json_encode($params);
            $queryparams = " , params='" . $params . "' ";
//END
            $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_resume` SET skills='" . esc_sql($wpjobportal_skills) . "' " . $queryparams . " WHERE id = ".esc_sql($wpjobportal_resumeid);
            wpjobportal::$_db->query($query);
        } elseif ($wpjobportal_section == 'resume') {
// RESUME SKILL CUSTOM FIELD
            $params = $this->getDataForParams(6, $wpjobportal_data);
            $pquery = "SELECT params FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` WHERE id = " . esc_sql($wpjobportal_resumeid);
            $parmsvar = wpjobportal::$_db->get_var($pquery);
            $parray = array();
            if (isset($parmsvar) && !empty($parmsvar)) {
                $parray = json_decode($parmsvar);
            }
            if (isset($params) && !empty($params)) {
                $params = json_decode($params);
            }
            $params = (object) array_merge((array) $params, (array) $parray);
            $params = wp_json_encode($params);
            $queryparams = " , params='" . $params . "' ";
//END
            $wpjobportal_resume = WPJOBPORTALrequest::getVar('resume');
            $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_resume` SET resume='" . esc_sql($wpjobportal_resume) . "' " .$queryparams." WHERE id = ".esc_sql($wpjobportal_resumeid);
            wpjobportal::$_db->query($query);
        }
        $wpjobportal_result = null;
        $wpjobportal_resumelayout = WPJOBPORTALincluder::getObjectClass('resumeformlayout');
        $wpjobportal_fieldsordering = wpjobportal::$_wpjpfieldordering->getFieldsOrderingforForm(3); // resume fields
        wpjobportal::$_data[2] = array();
        foreach ($wpjobportal_fieldsordering AS $wpjobportal_field) {
            wpjobportal::$_data[2][$wpjobportal_field->section][$wpjobportal_field->field] = $wpjobportal_field->required;
        }
        switch ($wpjobportal_section) {
            case 'addresses':
                wpjobportal::$_data[0]['address_section'][0] = $this->getResumeAddressSection($wpjobportal_resumeid, $wpjobportal_uid, $objectid);
                $wpjobportal_result = $wpjobportal_resumelayout->getAddressesSection(0, 1);
                break;
            case 'institutes':
                wpjobportal::$_data[0]['institute_section'][0] = apply_filters('wpjobportal_addons_getResume_action_ajx_adm',false,'getResumeInstituteSection',$wpjobportal_resumeid,$wpjobportal_uid,$objectid);
                $wpjobportal_result = apply_filters('wpjobportal_addons_view_resume_by_section_resume',false,'getEducationSection');
                break;
            case 'employers':
                wpjobportal::$_data[0]['employer_section'][0] = $this->getResumeEmployerSection($wpjobportal_resumeid, $wpjobportal_uid, $objectid);
                $wpjobportal_result = $wpjobportal_resumelayout->getEmployerSection(0, 1);
                break;
            case 'languages':
                wpjobportal::$_data[0]['language_section'][0] = apply_filters('wpjobportal_addons_getResume_action_ajx_adm',false,'getResumeLanguageSection',$wpjobportal_resumeid,$wpjobportal_uid,$objectid);
                $wpjobportal_result = apply_filters('wpjobportal_addons_view_resume_by_section_resume',false,'getLanguageSection');
                break;
            case 'skills':
                if(in_array('advanceresumebuilder', wpjobportal::$_active_addons)){
                    wpjobportal::$_data[0]['personal_section'] = $this->getResumePersonalSection($wpjobportal_resumeid, $wpjobportal_uid);
                    $wpjobportal_result = apply_filters('wpjobportal_addons_view_resume_by_section_resume',false,'getSkillSection');
                }
                break;
            case 'resume':
                wpjobportal::$_data[0]['personal_section'] = $this->getResumePersonalSection($wpjobportal_resumeid, $wpjobportal_uid);
                $wpjobportal_result = $wpjobportal_resumelayout->getResumeSection(0, 1);
                break;
            case 'personal':
                wpjobportal::$_data[0]['personal_section'] = $this->getResumePersonalSection($wpjobportal_resumeid, $wpjobportal_uid);
                wpjobportal::$_data[0]['file_section'] = $this->getResumeFilesSection($wpjobportal_resumeid, $wpjobportal_uid);
                wpjobportal::$wpjobportal_data['resumecontactdetail'] = true;
                $wpjobportal_result = $wpjobportal_resumelayout->getPersonalTopSection(1, 0);
                $wpjobportal_result .= '<div class="resume-section-title personal"><img class="heading-img" src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/personal-info.png" />' . esc_html(__('Personal information', 'wp-job-portal')) . '</div>';
                $wpjobportal_result .= $wpjobportal_resumelayout->getPersonalSection(0);
                break;
        }
        if ($wpjobportal_section != 'skills' && $wpjobportal_section != 'resume' && $wpjobportal_section != 'personal') {
            $canadd = $this->canAddMoreSection($wpjobportal_uid, $wpjobportal_resumeid, $wpjobportal_section);
            $anchor = '<a class="add" data-section="' . $wpjobportal_section . '"> + ' . esc_html(__('Add New', 'wp-job-portal')) . ' ' . wpjobportal::wpjobportal_getVariableValue($wpjobportal_section) . '</a>';
        } else {
            $canadd = 0;
            $anchor = '';
        }
        //send email

        if($wpjobportal_section == 'personal' && empty($wpjobportal_data['id'])){
            WPJOBPORTALincluder::getJSModel('emailtemplate')->sendMail(3,1,$wpjobportal_resumeid); // 3 for resume,1 for add new resume
        }
        $wpjobportal_array = wp_json_encode(array('html' => $wpjobportal_result, 'canadd' => $canadd, 'anchor' => $anchor, 'resumeid' => $wpjobportal_resumeid));
        return $wpjobportal_array;
    }

    function deleteResumeSectionAjax() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'delete-resume-section-ajax') ) {
            die( 'Security check Failed' );
        }
        $wpjobportal_section = WPJOBPORTALrequest::getVar('section');
        $wpjobportal_data = WPJOBPORTALrequest::get('post');
        $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        $wpjobportal_data['uid'] = $wpjobportal_uid;
        $wpjobportal_resumeid = $wpjobportal_data['resumeid'];
        $wpjobportal_row = null;
        switch ($wpjobportal_section) {
            case 'languages':
                $wpjobportal_row = WPJOBPORTALincluder::getJSTable('resumelanguage');
                break;
            // case 'references':
            //     $wpjobportal_row = WPJOBPORTALincluder::getJSTable('resumereference');
            //     break;
            case 'employers':
                $wpjobportal_row = WPJOBPORTALincluder::getJSTable('resumeemployer');
                break;
            case 'institutes':
                $wpjobportal_row = WPJOBPORTALincluder::getJSTable('resumeinstitute');
                break;
            case 'addresses':
                $wpjobportal_row = WPJOBPORTALincluder::getJSTable('resumeaddress');
                break;
        }
        $wpjobportal_msg = esc_html(__('Section has been deleted', 'wp-job-portal'));
        $wpjobportal_result = 1;
        if ($this->isYoursResume($wpjobportal_resumeid, $wpjobportal_uid)) {
            if (!$wpjobportal_row->delete($wpjobportal_data['sectionid'])) {
                $wpjobportal_msg = esc_html(__('Error deleting section', 'wp-job-portal'));
                $wpjobportal_result = 0;
            }
        }
        $canadd = $this->canAddMoreSection($wpjobportal_uid, $wpjobportal_resumeid, $wpjobportal_section);
        $anchor = '<a class="add" data-section="' . $wpjobportal_section . '"> + ' . esc_html(__('Add New', 'wp-job-portal')) . ' ' . wpjobportal::wpjobportal_getVariableValue($wpjobportal_section) . '</a>';
        $wpjobportal_array = wp_json_encode(array('canadd' => $canadd, 'msg' => $wpjobportal_msg, 'result' => $wpjobportal_result, 'anchor' => $anchor));
        return $wpjobportal_array;
    }

    function canAddMoreSection($wpjobportal_uid, $wpjobportal_resumeid, $wpjobportal_section) {
        $wpjobportal_config_array = wpjobportal::$_config->getConfigByFor('resume');
        if (!is_numeric($wpjobportal_resumeid))
            return false;
        if (!is_numeric($wpjobportal_uid))
            return false;
        switch ($wpjobportal_section) {
            case 'languages':
                $wpjobportal_tablename = 'wj_portal_resumelanguages';
                $wpjobportal_count = $wpjobportal_config_array['max_resume_languages'];
                break;
            // case 'references':
            //     // $wpjobportal_tablename = 'wj_portal_resumereferences';
            //     // $wpjobportal_count = $wpjobportal_config_array['max_resume_references'];
            //     break;
            case 'employers':
                $wpjobportal_tablename = 'wj_portal_resumeemployers';
                $wpjobportal_count = $wpjobportal_config_array['max_resume_employers'];
                break;
            case 'institutes':
                $wpjobportal_tablename = 'wj_portal_resumeinstitutes';
                $wpjobportal_count = $wpjobportal_config_array['max_resume_institutes'];
                break;
            case 'addresses':
                $wpjobportal_tablename = 'wj_portal_resumeaddresses';
                $wpjobportal_count = $wpjobportal_config_array['max_resume_addresses'];
                break;
        }
        $query = "SELECT COUNT(sec.id)
                    FROM `" . wpjobportal::$_db->prefix . $wpjobportal_tablename . "` AS sec
                    JOIN `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume ON resume.id = sec.resumeid
                    WHERE sec.resumeid = " . esc_sql($wpjobportal_resumeid);
        $wpjobportal_visallowed = 0;
        if (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
            if ($wpjobportal_config_array['visitor_can_add_resume'] == 1) {
                $wpjobportal_visallowed = 1;
            }
        }
        if ($wpjobportal_uid && $wpjobportal_visallowed = 0) {
            $query .= " AND resume.uid = ". esc_sql($wpjobportal_uid);
        }
        $wpjobportal_total = wpjobportal::$_db->get_var($query);
        if ($wpjobportal_count > $wpjobportal_total) {
            return 1;
        } else {
            return 0;
        }
    }

    function getResumeSectionAjax() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'get-resume-section-ajax') ) {
            die( 'Security check Failed' );
        }
        $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        $wpjobportal_section = WPJOBPORTALrequest::getVar('section');
        $wpjobportal_sectionid = WPJOBPORTALrequest::getVar('sectionid');
        $wpjobportal_resumeid = WPJOBPORTALrequest::getVar('resumeid');
        $wpjobportal_resumelayout = WPJOBPORTALincluder::getObjectClass('resumeformlayout');
        $wpjobportal_fieldsordering = wpjobportal::$_wpjpfieldordering->getFieldsOrderingforForm(3); // resume fields
        wpjobportal::$_data[2] = array();
        foreach ($wpjobportal_fieldsordering AS $wpjobportal_field) {
            wpjobportal::$_data[2][$wpjobportal_field->section][$wpjobportal_field->field] = $wpjobportal_field->required;
        }

        $wpjobportal_data = '';
        switch ($wpjobportal_section) {
            case 'addresses':
                wpjobportal::$_data[0]['address_section'] = $this->getResumeAddressSection($wpjobportal_resumeid, $wpjobportal_uid, $wpjobportal_sectionid);
                $wpjobportal_data = $wpjobportal_resumelayout->getAddressesSection(1, 1);
                break;
            case 'institutes':
                wpjobportal::$_data[0]['institute_section'] = apply_filters('wpjobportal_addons_getResume_action_ajx_adm',false,'getResumeInstituteSection',$wpjobportal_resumeid,$wpjobportal_uid,$wpjobportal_sectionid);
                $wpjobportal_data = apply_filters('wpjobportal_addons_view_resume_by_section_resume_ajx',false,'getEducationSection');
                break;
            case 'employers':
                wpjobportal::$_data[0]['employer_section'] = $this->getResumeEmployerSection($wpjobportal_resumeid, $wpjobportal_uid, $wpjobportal_sectionid);
                $wpjobportal_data = $wpjobportal_resumelayout->getEmployerSection(1, 1);
                break;
            case 'languages':
                wpjobportal::$_data[0]['language_section'] = apply_filters('wpjobportal_addons_getResume_action_ajx_adm',false,'getResumeLanguageSection',$wpjobportal_resumeid,$wpjobportal_uid,$wpjobportal_sectionid);
                $wpjobportal_data = apply_filters('wpjobportal_addons_view_resume_by_section_resume_ajx',false,'getLanguageSection');
                break;
            case 'skills':
                if(in_array('advanceresumebuilder', wpjobportal::$_active_addons)){
                    wpjobportal::$_data[0]['personal_section'] =$this->getResumePersonalSection($wpjobportal_resumeid, $wpjobportal_uid, $wpjobportal_sectionid);
                    $wpjobportal_data = apply_filters('wpjobportal_addons_view_resume_by_section_resume_ajx',false,'getSkillSection');
                }
                break;
            case 'personal':
                wpjobportal::$_data[0]['personal_section'] = $this->getResumePersonalSection($wpjobportal_resumeid, $wpjobportal_uid);
                wpjobportal::$_data[0]['file_section'] = $this->getResumeFilesSection($wpjobportal_resumeid, $wpjobportal_uid);
                $wpjobportal_data = $wpjobportal_resumelayout->getPersonalSection(1);
                break;
        }
        return $wpjobportal_data;
    }

    // this function is now also used for role based package
    public function getUidByResumeId($wpjobportal_id) {
        if (!is_numeric($wpjobportal_id)) return false;
        $query = "SELECT uid FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` WHERE id = " . esc_sql($wpjobportal_id);
        $wpjobportal_uid = wpjobportal::$_db->get_var($query);
        return $wpjobportal_uid;
    }

    public function getResumeTitle($wpjobportal_id) {
        if (!is_numeric($wpjobportal_id)) return false;
        $query = "SELECT application_title FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` WHERE id = " . esc_sql($wpjobportal_id);
        $wpjobportal_uid = wpjobportal::$_db->get_var($query);
        return $wpjobportal_uid;
    }

    function getResumeById($wpjobportal_id) {
        if (WPJOBPORTALincluder::getObjectClass('user')->isemployer() || current_user_can( 'manage_options' )) { // Current user is employer
            $wpjobportal_uid = $this->getUidByResumeId($wpjobportal_id);
        } else {
			$wpjobportal_userobject = WPJOBPORTALincluder::getObjectClass('user');
			if($wpjobportal_userobject->isguest() || !$wpjobportal_userobject->isWPJOBPORTALUser()){
                $wpjobportal_uid = $this->getUidByResumeId($wpjobportal_id);
            }else{
                $wpjobportal_uid = $wpjobportal_userobject->uid();
			}
        }
        if(isset($_COOKIE['wpjobportal_apply_visitor']) && is_numeric($_COOKIE['wpjobportal_apply_visitor']) && !is_user_logged_in()){
            $query = "SELECT job.id as id,job.endfeatureddate,job.id,job.uid,job.title,job.isfeaturedjob,job.serverid,job.noofjobs,job.city,job.status,job.currency,
                CONCAT(job.alias,'-',job.id) AS jobaliasid,job.created,job.serverid,company.name AS companyname,company.id AS companyid,company.logofilename,CONCAT(company.alias,'-',company.id) AS compnayaliasid,job.salarytype,job.salarymin,job.salarymax,salaryrangetype.title AS salarydurationtitle,
                cat.cat_title, jobtype.title AS jobtypetitle,salaryrangetype.title AS srangetypetitle,
                (SELECT count(jobapply.id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` AS jobapply
                 WHERE jobapply.jobid = job.id) AS resumeapplied ,job.params,job.startpublishing,job.stoppublishing
                 ,LOWER(jobtype.title) AS jobtypetit,jobtype.color as jobtypecolor,job.description
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                ".wpjobportal::$_company_job_table_join." JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company ON company.id = job.companyid
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON cat.id = job.jobcategory
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON jobtype.id = job.jobtype
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_salaryrangetypes` AS salaryrangetype ON salaryrangetype.id = job.salarytype WHERE job.id = " . sanitize_key($_COOKIE['wpjobportal_apply_visitor']);
            wpjobportal::$_data['jobinfo'] = wpjobportaldb::get_row($query);
            if(wpjobportal::$_data['jobinfo'] != ''){
                wpjobportal::$_data['jobinfo']->location = WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView(wpjobportal::$_data['jobinfo']->city);
            }
            wpjobportal::$wpjobportal_data['fields'] = wpjobportal::$_wpjpfieldordering->getFieldsOrderingforForm(2);
            wpjobportal::$_data['config'] = wpjobportal::$_config->getConfigByFor('job');
        }

        if (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {

            // $guestallowed = wpjobportal::$_config->getConfigurationByConfigName('visitor_can_add_resume'); old code// problem
			$guestallowed = wpjobportal::$_config->getConfigurationByConfigName('visitorview_emp_viewresume');
            if ($guestallowed == 0)
                return false;
        }else {
            if($wpjobportal_uid)
            if (is_numeric($wpjobportal_uid) == false)
                return false;
        }
            if (($wpjobportal_id != '') && ($wpjobportal_id != 0)) {
                if (is_numeric($wpjobportal_id) == false)
                    return false;
                global $job_portal_theme_options;
                // getting personal section
                wpjobportal::$_data[0]['personal_section'] = $this->getResumePersonalSection($wpjobportal_id, $wpjobportal_uid);
                // getting address section
                wpjobportal::$_data[0]['address_section'] = $this->getResumeAddressSection($wpjobportal_id, $wpjobportal_uid);
                // getting employer section
                wpjobportal::$_data[0]['employer_section'] = $this->getResumeEmployerSection($wpjobportal_id, $wpjobportal_uid);
                // getting institutes section
                wpjobportal::$_data[0]['institute_section'] = apply_filters('wpjobportal_addons_resume_by_user_adv',false,'getResumeInstituteSection',$wpjobportal_id,$wpjobportal_uid);
                // getting languages section
                wpjobportal::$_data[0]['language_section'] = apply_filters('wpjobportal_addons_resume_by_user_adv',false,'getResumeLanguageSection',$wpjobportal_id,$wpjobportal_uid);
               // getting file section
                wpjobportal::$_data[0]['file_section'] = $this->getResumeFilesSection($wpjobportal_id, $wpjobportal_uid);
                $wpjobportal_theme = wp_get_theme();
            $wpjobportal_layout = WPJOBPORTALrequest::getVar('wpjobportallt');
            $finalresume = array();
            if($wpjobportal_layout == 'viewresume' && !wpjobportal::$_common->wpjp_isadmin() && !WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()){ // hiding related resumes from jobseeker. no point in showing those to job seeker
                if(wpjobportal::$wpjobportal_theme_chk != 0){
                    // Related Resumes data
                    $wpjobportal_max = $job_portal_theme_options['maximum_relatedresume'];
                    if(!is_numeric($wpjobportal_max)){
                        $wpjobportal_max = 5;
                    }
                    $finalresume = array();
                    $relatedresume=array();
                    $wpjobportal_layout =WPJOBPORTALrequest::getVar("wpjobportallt");
                    if ($wpjobportal_layout != 'printresume') {
                        //var_dump($job_portal_theme_options['relatedresume_criteria_sorter']['enabled']);
                        foreach($job_portal_theme_options['relatedresume_criteria_sorter']['enabled'] AS $wpjobportal_key => $wpjobportal_value){
                            $wpjobportal_inquery = '';
                            switch($wpjobportal_key){
                                case 'category':
                                    if(wpjobportal::$_data[0]['personal_section']->job_category != '' && is_numeric(wpjobportal::$_data[0]['personal_section']->job_category)){

                                        $wpjobportal_inquery = ' resume.job_category = ' . esc_sql(wpjobportal::$_data[0]['personal_section']->job_category);
                                    }
                                break;
                                case 'jobtype':
                                    if(wpjobportal::$_data[0]['personal_section']->jobtype != '' && is_numeric(wpjobportal::$_data[0]['personal_section']->jobtype)){
                                        $wpjobportal_inquery = ' resume.jobtype = ' . esc_sql(wpjobportal::$_data[0]['personal_section']->jobtype);
                                    }
                                break;
                            }
                            if(!empty($wpjobportal_inquery)){
                                $query = "SELECT resume.id,resume.uid,resume.application_title, resume.first_name, resume.last_name,resume.photo,resume.job_category, cat.cat_title AS categorytitle, jobtype.title AS jobtypetitle, resume.jobtype
                                        ,resume.params,resume.status,resume.created,LOWER(jobtype.title) AS jobtypetit
                                        ,resumeaddress.address_city, resumeaddress.address, resumeaddress.longitude, resumeaddress.latitude
                                        ,city.name AS cityname, state.name AS statename, country.name AS countryname ,resumeaddress.params
                                        ,resume.salaryfixed as salary,LOWER(jobtype.title) AS jobtypetit,jobtype.color as jobtypecolor,resume.params
                                        FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume
                                        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON cat.id = resume.job_category
                                        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON jobtype.id = resume.jobtype
                                        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_resumeaddresses` AS resumeaddress ON resumeaddress.resumeid = resume.id
                                        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON city.id = resumeaddress.address_city
                                        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_states` AS state ON state.id = city.stateid
                                        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_countries` AS country ON country.id = city.countryid
                                        WHERE 1=1 AND ".$wpjobportal_inquery." AND resume.id != ".esc_sql($wpjobportal_id)." AND resume.quick_apply <> 1 GROUP BY resume.id LIMIT ".esc_sql($wpjobportal_max);
                                        $wpjobportal_result = wpjobportaldb::get_results($query);
                                        $relatedresume = array_merge($relatedresume, $wpjobportal_result);
                                        $relatedresume = array_map('unserialize', array_unique(array_map('serialize', $relatedresume)));
                                        if(COUNT($relatedresume) >= $wpjobportal_max){
                                            break;
                                        }
                            }
                        }
                    }
                    if(!empty($relatedresume)){
                        foreach ($relatedresume AS $d) {
                            $d->location = WPJOBPORTALincluder::getJSModel('common')->getLocationForView($d->cityname, $d->statename, $d->countryname);
                            //$d->salary = WPJOBPORTALincluder::getJSModel('common')->getSalaryRangeView($d->rangestart, $d->rangeend, $d->rangetype,$d->total_experience);
                            $finalresume[] = $d;
                        }
                    }
                    wpjobportal::$_data['relatedresume'] = $finalresume;
                }
                wpjobportal::$_data['relatedresume'] = $finalresume;
            }
        }
        wpjobportal::$wpjobportal_data['resumecontactdetail'] = false;
        $wpjobportal_resume_contact_detail = wpjobportal::$_config->getConfigValue('resume_contact_detail');

        if ($wpjobportal_resume_contact_detail == 0) { // no one is allowed
            wpjobportal::$wpjobportal_data['resumecontactdetail'] = false;

        } elseif ($wpjobportal_resume_contact_detail == 1) { // everyone is allowed
            wpjobportal::$wpjobportal_data['resumecontactdetail'] = true;

        } elseif ($wpjobportal_resume_contact_detail == 2) { // employers only
            if (WPJOBPORTALincluder::getObjectClass('user')->isemployer()) {
                wpjobportal::$wpjobportal_data['resumecontactdetail'] = true;
            }
        } elseif ($wpjobportal_resume_contact_detail == 3) { // paid only
            if (WPJOBPORTALincluder::getObjectClass('user')->isguest() || (isset(wpjobportal::$_data[0]['personal_section']->uid) && wpjobportal::$_data[0]['personal_section']->uid != WPJOBPORTALincluder::getObjectClass('user')->uid())) {
                //$wpjobportal_result = WPJOBPORTALincluder::getJSModel('credits')->getMinimumCreditIDByAction('view_resume_contact_detail');
                if (in_array('credits', wpjobportal::$_active_addons)) {
                    $wpjobportal_subType = wpjobportal::$_config->getConfigValue('submission_type');
                    if($wpjobportal_subType == 1){
                        wpjobportal::$wpjobportal_data['resumecontactdetail'] = true;
                    }elseif($wpjobportal_subType == 2){
                        $wpjobportal_price = wpjobportal::$_config->getConfigValue('job_viewresumecontact_price_perlisting');
                        if($wpjobportal_price > 0){
                            // Paid
                            wpjobportal::$wpjobportal_data['resumecontactdetail'] = $this->checkAlreadyViewResumeContactDetail($wpjobportal_id);
                        } else {
                            // Free
                            wpjobportal::$wpjobportal_data['resumecontactdetail'] = true;
                        }
                    }elseif($wpjobportal_subType == 3){
                        $wpjobportal_uid = !empty($wpjobportal_uid) ? $wpjobportal_uid : WPJOBPORTALincluder::getObjectClass('user')->uid();
                        if(is_numeric($wpjobportal_uid) && $wpjobportal_uid > 0){
                            $hasPackage = WPJOBPORTALincluder::getJSModel('credits')->checkIfPackageDefinedForUserRole($wpjobportal_uid);
                            if($hasPackage == 0) {
                                // No package system
                                wpjobportal::$wpjobportal_data['resumecontactdetail'] = true;
                            } else {
                                // Package system exists
                                wpjobportal::$wpjobportal_data['resumecontactdetail'] = $this->checkAlreadyViewResumeContactDetail($wpjobportal_id);
                            }
                        } else {
                            // Fallback
                            wpjobportal::$wpjobportal_data['resumecontactdetail'] = $this->checkAlreadyViewResumeContactDetail($wpjobportal_id);
                        }
                    }else{
                        wpjobportal::$wpjobportal_data['resumecontactdetail'] = true;
                    }
                }
            }
        }
        // count resume hits
        if (WPJOBPORTALincluder::getObjectClass('user')->isguest() || (wpjobportal::$_common->wpjp_isadmin() || (isset(wpjobportal::$_data[0]['personal_section']->uid) && wpjobportal::$_data[0]['personal_section']->uid != WPJOBPORTALincluder::getObjectClass('user')->uid()))) {
            if(is_numeric($wpjobportal_id) && $wpjobportal_id > 0){
                // if resume owner not viewing it then count the resume views, its shown on view resume page
                $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_resume` SET hits = hits + 1 WHERE id = " . esc_sql($wpjobportal_id);
                wpjobportal::$_db->query($query);
            }
        }
            // allow owner and admin to view
            if (!WPJOBPORTALincluder::getObjectClass('user')->isguest()&& (wpjobportal::$_common->wpjp_isadmin() || (isset(wpjobportal::$_data[0]['personal_section']->uid) && wpjobportal::$_data[0]['personal_section']->uid == WPJOBPORTALincluder::getObjectClass('user')->uid()))) {
                wpjobportal::$wpjobportal_data['resumecontactdetail'] = true;
            }

             if(in_array('credits', wpjobportal::$_active_addons)){
                wpjobportal::$_data['paymentconfig'] = wpjobportal::$_wpjppaymentconfig->getPaymentConfigFor('paypal,stripe,woocommerce',true);
            }
            // code to show next back on view resume in case of job applied applitcation layout
            $wpjobportal_jobapplyid = WPJOBPORTALrequest::getVar('jobapplyid');
            if(is_numeric($wpjobportal_jobapplyid) && $wpjobportal_jobapplyid > 0){

                // getting jobid and action_status to use in next query to get resumes with same data
                $query = "SELECT jobapply.jobid, jobapply.action_status
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` AS jobapply
                WHERE jobapply.id = " . esc_sql($wpjobportal_jobapplyid);

                $wpjobportal_job_apply_record = wpjobportaldb::get_row($query);
                if(!empty($wpjobportal_job_apply_record) && is_numeric($wpjobportal_job_apply_record->jobid) && is_numeric($wpjobportal_job_apply_record->action_status)){
                    $query = "SELECT jobapply.cvid
                        FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` AS jobapply
                        WHERE jobapply.jobid = " . esc_sql($wpjobportal_job_apply_record->jobid) ." AND jobapply.action_status = ". esc_sql($wpjobportal_job_apply_record->action_status)." ORDER BY jobapply.id DESC";
                    // cv ids that have same jobid and action_status
                    $wpjobportal_job_apply_records_cvids = wpjobportal::$_db->get_col($query);

                    // index of current cv id to get next and back
                    $current_resume_index = array_search($wpjobportal_id, $wpjobportal_job_apply_records_cvids);
                    // the above function may return 0 as index value
                    if($current_resume_index !== FALSE){
                        wpjobportal::$_data['jobapply_resume_next'] = isset($wpjobportal_job_apply_records_cvids[$current_resume_index + 1]) ? $wpjobportal_job_apply_records_cvids[$current_resume_index + 1] : FALSE ;
                        wpjobportal::$_data['jobapply_resume_prev'] = isset($wpjobportal_job_apply_records_cvids[$current_resume_index - 1]) ? $wpjobportal_job_apply_records_cvids[$current_resume_index - 1] : FALSE ;
                        wpjobportal::$_data['jobapply_resume_jobapplyid'] = $wpjobportal_jobapplyid;
                    }

                }

            }
        return;
    }

    function getResumePersonalSection($wpjobportal_id, $wpjobportal_uid) {
        if (!is_numeric($wpjobportal_id))
            return false;
        if ($wpjobportal_uid)
            if (!is_numeric($wpjobportal_uid))
                return false;
        $query = "SELECT resume.id,resume.salaryfixed, resume.tags AS viewtags , resume.tags AS resumetags ,resume.uid,resume.application_title, resume.first_name, resume.last_name, resume.cell, resume.email_address, resume.nationality AS nationalityid, resume.photo, resume.gender, resume.job_category
                    , resume.skills, resume.keywords, cat.cat_title AS categorytitle, jobtype.title AS jobtypetitle,resume.jobtype
                    , resume.resume,nationality.name AS nationality
                    ,resume.params,resume.status,resume.hits AS resumehits ,resume.created,resume.searchable,LOWER(jobtype.title) AS jobtypetit,jobtype.color AS jobtypecolor,resume.quick_apply,resume.isfeaturedresume,resume.endfeatureddate
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON cat.id = resume.job_category
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON jobtype.id = resume.jobtype
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_countries` AS nationality ON nationality.id = resume.nationality
                    WHERE resume.id = " . esc_sql($wpjobportal_id);
        $wpjobportal_isguest = WPJOBPORTALincluder::getObjectClass('user')->isguest();
        $wpjobportal_iswpjobportaluser = WPJOBPORTALincluder::getObjectClass('user')->isWPJOBPORTALUser();
        if(!$wpjobportal_isguest && $wpjobportal_iswpjobportaluser){
            if (!current_user_can( 'manage_options' ) && $wpjobportal_uid) {
                $query .= " AND resume.uid = " . esc_sql($wpjobportal_uid);
            }
        }
        $wpjobportal_result = wpjobportaldb::get_row($query);
        if(!empty($wpjobportal_result)){
            $wpjobportal_result->resumetags = WPJOBPORTALincluder::getJSModel('common')->makeFilterdOrEditedTagsToReturn($wpjobportal_result->resumetags);
        }
        return $wpjobportal_result;
    }

    function getResumeAddressSection($wpjobportal_id, $wpjobportal_uid, $wpjobportal_sectionid = null) {
        if (!is_numeric($wpjobportal_id))
            return false;
        if ($wpjobportal_uid)
            if (!is_numeric($wpjobportal_uid))
                return false;
        if (!$this->isYoursResume($wpjobportal_id, $wpjobportal_uid))
            return false;
        $query = "SELECT resumeaddress.id, resumeaddress.address_city, resumeaddress.address
                        , city.name AS cityname, state.name AS statename, country.name AS countryname ,resumeaddress.params,resumeaddress.longitude,resumeaddress.latitude
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumeaddresses` resumeaddress
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON city.id = resumeaddress.address_city
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_states` AS state ON state.id = city.stateid
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_countries` AS country ON country.id = city.countryid
                    WHERE resumeaddress.resumeid = " . esc_sql($wpjobportal_id);
        if ($wpjobportal_sectionid != null) {
            if (!is_numeric($wpjobportal_sectionid))
                return false;
            $query .= ' AND resumeaddress.id = ' . esc_sql($wpjobportal_sectionid);
            $wpjobportal_result = wpjobportaldb::get_row($query);
        }else {
            $wpjobportal_result = wpjobportaldb::get_results($query);
        }

        return $wpjobportal_result;
    }

    function getResumeEmployerSection($wpjobportal_id, $wpjobportal_uid, $wpjobportal_sectionid = null) {
        if (!is_numeric($wpjobportal_id))
            return false;
        if ($wpjobportal_uid)
            if (!is_numeric($wpjobportal_uid))
                return false;
        if (!$this->isYoursResume($wpjobportal_id, $wpjobportal_uid))
            return false;
        $query = "SELECT employer.id, employer.employer, employer.employer_current_status,employer.employer_from_date, employer.employer_to_date, employer.employer_city,employer.employer_position
                    , employer.employer_phone, employer.employer_address
                    , city.name AS cityname,employer.params, state.name AS statename, country.name AS countryname,city.latitude,city.longitude
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumeemployers` AS employer
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON city.id = employer.employer_city
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_states` AS state ON state.id = city.stateid
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_countries` AS country ON country.id = city.countryid
                    WHERE employer.resumeid = " . esc_sql($wpjobportal_id);
        if ($wpjobportal_sectionid != null) {
            if (!is_numeric($wpjobportal_sectionid))
                return false;
            $query .= ' AND employer.id = ' . esc_sql($wpjobportal_sectionid);
            $wpjobportal_result = wpjobportaldb::get_row($query);
        }else {
            $wpjobportal_result = wpjobportaldb::get_results($query);
        }
        return $wpjobportal_result;
    }

    function getResumeFilesSection($wpjobportal_id, $wpjobportal_uid) {
        if (!is_numeric($wpjobportal_id))
            return false;
        if ($wpjobportal_uid)
            if (!is_numeric($wpjobportal_uid))
                return false;
        if (!$this->isYoursResume($wpjobportal_id, $wpjobportal_uid))
            return false;
        $query = "SELECT *
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumefiles`
                    WHERE resumeid = " . esc_sql($wpjobportal_id);
        $wpjobportal_result = wpjobportaldb::get_results($query);
        return $wpjobportal_result;
    }

    function getResumeFiles() {
        $wpjobportal_resumeid = (int) WPJOBPORTALrequest::getVar('resumeid');
        if(!is_numeric($wpjobportal_resumeid)){
            return false;
        }
        $wpjobportal_data_directory = wpjobportal::$_config->getConfigValue('data_directory');
        $files = array();
        $wpjobportal_totalFilesQry = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumefiles` WHERE resumeid=" . esc_sql($wpjobportal_resumeid);
        $filesFound = wpjobportaldb::get_results($wpjobportal_totalFilesQry);
        if ($filesFound > 0) {
            $query = "SELECT * FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumefiles` WHERE resumeid = " . esc_sql($wpjobportal_resumeid);
            $files = wpjobportaldb::get_results($query);
        }
        // resume form layout class
        include_once(WPJOBPORTAL_PLUGIN_PATH . '/includes/resumeformlayout.php');
        $wpjobportal_resumeformlayout = new WPJOBPORTALResumeformlayout();
        $wpjobportal_data = $wpjobportal_resumeformlayout->getResumeFilesLayout($files, $wpjobportal_data_directory);
        return $wpjobportal_data;
    }

    function uploadResume($wpjobportal_id) {
        if (is_numeric($wpjobportal_id) == false)
            return false;
        WPJOBPORTALincluder::getObjectClass('uploads')->uploadResumeFiles($wpjobportal_id);
        return;
    }

    function uploadPhoto($wpjobportal_id) {
        if (is_numeric($wpjobportal_id) == false)
            return false;
        WPJOBPORTALincluder::getObjectClass('uploads')->uploadResumePhoto($wpjobportal_id);
        return;
    }

    function deleteResume($wpjobportal_ids) {
        if (empty($wpjobportal_ids))
            return false;
        $wpjobportal_notdeleted = 0;
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('resume');
        foreach ($wpjobportal_ids as $wpjobportal_id) {
            if(!is_numeric($wpjobportal_id)){
                continue;
            }
            if ($this->resumeCanDelete($wpjobportal_id) == true) {
                //code for preparing data for delete resume email
                $wpjobportal_resultforsendmail = WPJOBPORTALincluder::getJSModel('resume')->getResumeInfoForEmail($wpjobportal_id);
                $wpjobportal_username = $wpjobportal_resultforsendmail->firstname . '' . $wpjobportal_resultforsendmail->lastname;
                if ($wpjobportal_username == '') {
                    $wpjobportal_username = $wpjobportal_resultforsendmail->username;
                }
                $wpjobportal_email = $wpjobportal_resultforsendmail->useremailfromresume;
                if ($wpjobportal_email == '') {
                    $wpjobportal_email = $wpjobportal_resultforsendmail->useremail;
                }
                $wpjobportal_resumetitle = $wpjobportal_resultforsendmail->resumetitle;
                $wpjobportal_mailextradata = array();
                $wpjobportal_mailextradata['resumetitle'] = $wpjobportal_resumetitle;
                $wpjobportal_mailextradata['jobseekername'] = $wpjobportal_username;
                $wpjobportal_mailextradata['useremail'] = $wpjobportal_email;

                if (!$wpjobportal_row->delete($wpjobportal_id)) {
                    $wpjobportal_notdeleted += 1;
                }
                $query = "DELETE FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumeaddresses` WHERE resumeid = " . esc_sql($wpjobportal_id);
                wpjobportaldb::query($query);
                $query = "DELETE FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumeemployers` WHERE resumeid = " . esc_sql($wpjobportal_id);
                wpjobportaldb::query($query);

                $query = "DELETE FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumefiles` WHERE resumeid = " . esc_sql($wpjobportal_id);
                wpjobportaldb::query($query);
                $query = "DELETE FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumeinstitutes` WHERE resumeid = " . esc_sql($wpjobportal_id);
                wpjobportaldb::query($query);

                $query = "DELETE FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumelanguages` WHERE resumeid = " . esc_sql($wpjobportal_id);
                wpjobportaldb::query($query);
                $wpjobportal_wpdir = wp_upload_dir();
                $wpjobportal_data_directory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
                array_map('wp_delete_file', glob($wpjobportal_wpdir['basedir'] . '/' . $wpjobportal_data_directory . "/data/jobseeker/resume_".$wpjobportal_id."/resume/*.*"));//deleting files
                array_map('wp_delete_file', glob($wpjobportal_wpdir['basedir'] . '/' . $wpjobportal_data_directory . "/data/jobseeker/resume_".$wpjobportal_id."/photo/*.*"));//deleting files
                if ( ! function_exists( 'WP_Filesystem' ) ) {
                    require_once ABSPATH . 'wp-admin/includes/file.php';
                }
                global $wp_filesystem;
                if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ) {
                    $creds = request_filesystem_credentials( site_url() );
                    wp_filesystem( $creds );
                }


                if ($wp_filesystem->exists($wpjobportal_wpdir['basedir'] . '/' . $wpjobportal_data_directory . "/data/jobseeker/resume_".$wpjobportal_id.'/resume')) {
                    @$wp_filesystem->rmdir($wpjobportal_wpdir['basedir'] . '/' . $wpjobportal_data_directory . "/data/jobseeker/resume_".$wpjobportal_id.'/resume');
                }
                if ($wp_filesystem->exists($wpjobportal_wpdir['basedir'] . '/' . $wpjobportal_data_directory . "/data/jobseeker/resume_".$wpjobportal_id.'/photo')) {
                    @$wp_filesystem->rmdir($wpjobportal_wpdir['basedir'] . '/' . $wpjobportal_data_directory . "/data/jobseeker/resume_".$wpjobportal_id.'/photo');
                }
                if ($wp_filesystem->exists($wpjobportal_wpdir['basedir'] . '/' . $wpjobportal_data_directory . "/data/jobseeker/resume_".$wpjobportal_id)) {
                    @$wp_filesystem->rmdir($wpjobportal_wpdir['basedir'] . '/' . $wpjobportal_data_directory . "/data/jobseeker/resume_".$wpjobportal_id);
                }
                WPJOBPORTALincluder::getJSModel('emailtemplate')->sendMail(3, 6, $wpjobportal_id,$wpjobportal_mailextradata); // 3 for resume,6 for DELETE resume
                // action hook for delete resume
                do_action('wpjobportal_after_delete_resume_hook',$wpjobportal_id);
            }else{
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

    function resumeCanDelete($wpjobportal_resumeid) {
        if (!is_numeric($wpjobportal_resumeid))
            return false;
        if(!wpjobportal::$_common->wpjp_isadmin()){
            if(!$this->getIfResumeOwner($wpjobportal_resumeid)){
                return false;
            }
        }
        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` WHERE cvid = " . esc_sql($wpjobportal_resumeid);
        $wpjobportal_total = wpjobportaldb::get_var($query);
        if ($wpjobportal_total > 0)
            return false;
        else
            return true;
    }

    function resumeEnforceDelete($wpjobportal_resumeid, $wpjobportal_uid) {
        if ($wpjobportal_uid)
            if ((is_numeric($wpjobportal_uid) == false) || ($wpjobportal_uid == 0) || ($wpjobportal_uid == ''))
                return false;
        if (is_numeric($wpjobportal_resumeid) == false)
            return false;

        $juid = 0; // jobseeker uid
        $query = "DELETE  resume,apply,resumeaddress,resumeemployers,resumefiles
                            ,resumeinstitutes,resumelanguages";
        if(in_array('folder', wpjobportal::$_active_addons)){
            $query .= ",resumefolder";
        }
        if(in_array('message', wpjobportal::$_active_addons)){
            $query .= ",message";
        }

        $query .= " FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` AS apply ON resume.id=apply.cvid
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_resumeaddresses` AS resumeaddress ON resume.id=resumeaddress.resumeid
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_resumeemployers` AS resumeemployers ON resume.id=resumeemployers.resumeid
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_resumefiles` AS resumefiles ON resume.id=resumefiles.resumeid
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_resumeinstitutes` AS resumeinstitutes ON resume.id=resumeinstitutes.resumeid
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_resumelanguages` AS resumelanguages ON resume.id=resumelanguages.resumeid";
        if(in_array('folder', wpjobportal::$_active_addons)){
            $query .= " LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_folderresumes` AS resumefolder ON 
                    resume.id=resumefolder.resumeid";
        }
        if(in_array('message', wpjobportal::$_active_addons)){
            $query .= " LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_messages` AS message ON
                    resume.id = message.resumeid ";
        }
        $query .= " WHERE resume.id = " . esc_sql($wpjobportal_resumeid);
            //code for preparing data for delete resume email
                $wpjobportal_resultforsendmail = WPJOBPORTALincluder::getJSModel('resume')->getResumeInfoForEmail($wpjobportal_resumeid);
                $wpjobportal_username = $wpjobportal_resultforsendmail->firstname . ' ' . $wpjobportal_resultforsendmail->lastname;
                if ($wpjobportal_username == '') {
                    $wpjobportal_username = $wpjobportal_resultforsendmail->username;
                }
                $wpjobportal_email = $wpjobportal_resultforsendmail->useremailfromresume;
                if ($wpjobportal_email == '') {
                    $wpjobportal_email = $wpjobportal_resultforsendmail->useremail;
                }
                $wpjobportal_resumetitle = $wpjobportal_resultforsendmail->resumetitle;

                $wpjobportal_mailextradata['resumetitle'] = $wpjobportal_resumetitle;
                $wpjobportal_mailextradata['jobseekername'] = $wpjobportal_username;
                $wpjobportal_mailextradata['useremail'] = $wpjobportal_email;

        if (!wpjobportaldb::query($query)) {
            return WPJOBPORTAL_DELETE_ERROR; //error while delete resume
        }

        $wpjobportal_data_directory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
        $wpjobportal_wpdir = wp_upload_dir();
        array_map('wp_delete_file', glob($wpjobportal_wpdir['basedir'] . '/' . $wpjobportal_data_directory . "/data/jobseeker/resume_".$wpjobportal_resumeid."/resume/*.*"));//deleting files
        array_map('wp_delete_file', glob($wpjobportal_wpdir['basedir'] . '/'. $wpjobportal_data_directory . "/data/jobseeker/resume_".$wpjobportal_resumeid."/photo/*.*"));//deleting files
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
        if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ) {
            $creds = request_filesystem_credentials( site_url() );
            wp_filesystem( $creds );
        }


        if($wp_filesystem->exists($wpjobportal_wpdir['basedir'] . '/' . $wpjobportal_data_directory . "/data/jobseeker/resume_".$wpjobportal_resumeid.'/resume')) {
            $wp_filesystem->rmdir($wpjobportal_wpdir['basedir'] . '/' . $wpjobportal_data_directory . "/data/jobseeker/resume_".$wpjobportal_resumeid.'/resume');
        }
        if($wp_filesystem->exists($wpjobportal_wpdir['basedir'] . '/' . $wpjobportal_data_directory . "/data/jobseeker/resume_".$wpjobportal_resumeid.'/photo')) {
            $wp_filesystem->rmdir($wpjobportal_wpdir['basedir'] . '/' . $wpjobportal_data_directory . "/data/jobseeker/resume_".$wpjobportal_resumeid.'/photo');
        }
        if($wp_filesystem->exists($wpjobportal_wpdir['basedir'] . '/' . $wpjobportal_data_directory . "/data/jobseeker/resume_".$wpjobportal_resumeid)) {
            $wp_filesystem->rmdir($wpjobportal_wpdir['basedir'] . '/' . $wpjobportal_data_directory . "/data/jobseeker/resume_".$wpjobportal_resumeid);
        }

        WPJOBPORTALincluder::getJSModel('emailtemplate')->sendMail(3, 6, $wpjobportal_resumeid,$wpjobportal_mailextradata); // 3 for resume,6 for DELETE resume
        // action hook for delete resume
        do_action('wpjobportal_after_delete_resume_hook',$wpjobportal_resumeid);
        return WPJOBPORTAL_DELETED;
    }

    function getResumeInfoForEmail($wpjobportal_resumeid) {
        if ((is_numeric($wpjobportal_resumeid) == false))
            return false;
        // changed join to left join to get mail data for visitor reumes
        $query = 'SELECT resume.application_title AS resumetitle, CONCAT(user.first_name," ",user.last_name) AS username
                        ,resume.email_address AS useremailfromresume
                        ,resume.first_name AS firstname, resume.last_name AS lastname
                        , resume.email_address AS useremail
                        FROM `' . wpjobportal::$_db->prefix . 'wj_portal_resume` AS resume
                        LEFT JOIN `' . wpjobportal::$_db->prefix . 'wj_portal_users` AS user ON user.id = resume.uid
                        WHERE resume.id = '.esc_sql($wpjobportal_resumeid);
        $return_value = wpjobportaldb::get_row($query);
        return $return_value;
    }

    function empappReject($wpjobportal_app_id) {
        if (is_numeric($wpjobportal_app_id) == false)
            return false;

        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('resume');
        if(! $wpjobportal_row->update(array('id' => $wpjobportal_app_id , 'status' => -1))){
            return WPJOBPORTAL_DELETE_ERROR;
        }

        WPJOBPORTALincluder::getJSModel('emailtemplate')->sendMail(3, -1, $wpjobportal_app_id);
        return WPJOBPORTAL_REJECTED;
    }

    function canAddResume($wpjobportal_uid,$wpjobportal_actionname='') {
        #User authentication submission
        if (!is_numeric($wpjobportal_uid))
            return false;
        if(in_array('credits', wpjobportal::$_active_addons)){
            $wpjobportal_credits = apply_filters('wpjobportal_addons_userpackages_module_wise',false,$wpjobportal_uid,$wpjobportal_actionname);
            if($wpjobportal_credits){
                return true;
            }else{
                return false;
            }
        }else{
            return $this->checkAlreadyadd($wpjobportal_uid);
        }
    }

    function getResumeTitleById($wpjobportal_id) {
        if (!is_numeric($wpjobportal_id))
            return false;
        $query = "SELECT resume.application_title FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume WHERE resume.id = " . esc_sql($wpjobportal_id);
        $wpjobportal_jobname = wpjobportal::$_db->get_var($query);
        return $wpjobportal_jobname;
    }

    function getResumes($wpjobportal_vars,$wpjobportal_show_only_featured = 0,$wpjobportal_ignore_serachable = 0) {
        //die('abc');
        $wpjobportal_inquery = '';
        $wpjobportal_jsformresumesearch = WPJOBPORTALrequest::getVar('jsformresumesearch');
        if (isset($wpjobportal_jsformresumesearch) AND $wpjobportal_jsformresumesearch == 1) {
            wpjobportal::$wpjobportal_data['issearchform'] = 1;
            wpjobportal::$_data['filter'] =  array();
        }

        if (isset($wpjobportal_vars['category']) AND $wpjobportal_vars['category'] != '') {
            $wpjobportal_categoryid = $wpjobportal_vars['category'];
            if (!is_numeric($wpjobportal_categoryid))
                return false;
            $wpjp_query = "SELECT id FROM `" . wpjobportal::$_db->prefix . "wj_portal_categories` WHERE parentid = ". esc_sql($wpjobportal_categoryid);
            $wpjp_cats = wpjobportaldb::get_results($wpjp_query);
            $wpjp_ids = [];
            foreach ($wpjp_cats as $wpjp_cat) {
                $wpjp_ids[] = $wpjp_cat->id;
            }
            $wpjp_ids[] = $wpjobportal_categoryid;
            $wpjp_ids = implode(",",$wpjp_ids);
            $wpjobportal_inquery = " AND resume.job_category IN(".$wpjp_ids.")";
            wpjobportal::$_data['filter']['category'] = $wpjobportal_categoryid;
        }
        if (isset($wpjobportal_vars['searchid']) AND $wpjobportal_vars['searchid'] != '') {
            $wpjobportal_search = $wpjobportal_vars['searchid'];
            if (!is_numeric($wpjobportal_search))
                return false;
            $wpjobportal_inquery = $this->getSaveSearchForView($wpjobportal_search);
            wpjobportal::$_data['filter']['search'] = $wpjobportal_search;
        }
        if (isset($wpjobportal_vars['tags']) AND $wpjobportal_vars['tags'] != '') {
            wpjobportal::$_data['fromtags'] = $wpjobportal_vars['tags'];
            $wpjobportal_tags = $wpjobportal_vars['tags'];
            $wpjobportal_inquery = " AND resume.tags LIKE '%" . esc_sql($wpjobportal_tags) . "%'";
            // to populate tags fields on the search form.
            wpjobportal::$_data['filter']['tag'] = WPJOBPORTALincluder::getJSModel('common')->makeFilterdOrEditedTagsToReturn($wpjobportal_tags);
        }

        // ai resume search

        // variable to handle the resme data prepration overide
        $dont_prep_data = 0;

        $wpjobportal_airesumesearcch = isset(wpjobportal::$_search['resumes']['airesumesearcch']) ? wpjobportal::$_search['resumes']['airesumesearcch'] : '';
        if ($wpjobportal_airesumesearcch != '') {
        // AI search
            do_action('wpjobportal_addons_airesumesearch_query');
            if( !empty(wpjobportal::$_data['ai_resume_data_set']) ){
                $dont_prep_data = 1; // ignore the below code data is preped.
            }
        }

        // ai resume search

        // echo '<pre>';print_r(wpjobportal::$_search['resumes']);echo '</pre>';
        // echo '<pre>';print_r($wpjobportal_vars);echo '</pre>';
        // variable to handle the resme data prepration overide
        if (isset($wpjobportal_vars['aisuggestedresumes_job']) AND $wpjobportal_vars['aisuggestedresumes_job'] != '') {
            do_action('wpjobportal_addons_aisuggestedresumes_resumes',$wpjobportal_vars['aisuggestedresumes_job']);
            if( !empty(wpjobportal::$_data['ai_resume_data_set']) ){
                $dont_prep_data = 1; // ignore the below code data is preped.
            }
        }

        $this->sortingrescat();
        //variables form search form
            $title = isset(wpjobportal::$_search['resumes']['application_title']) ? wpjobportal::$_search['resumes']['application_title'] : '';
            if ($title != '') {
                $wpjobportal_inquery .= ' AND resume.application_title LIKE "%' . esc_sql($title) . '%" ';
                wpjobportal::$_data['filter']['application_title'] = $title;
                wpjobportal::$wpjobportal_data['issearchform'] = 1;
            }

            $wpjobportal_firstName = isset(wpjobportal::$_search['resumes']['first_name']) ? wpjobportal::$_search['resumes']['first_name'] : '';
            if ($wpjobportal_firstName != '') {
                $wpjobportal_inquery .= ' AND resume.first_name LIKE "%' . esc_sql($wpjobportal_firstName) . '%" ';
                wpjobportal::$_data['filter']['first_name'] = $wpjobportal_firstName;
                wpjobportal::$wpjobportal_data['issearchform'] = 1;
            }
            $middle_name = isset(wpjobportal::$_search['resumes']['middle_name']) ? wpjobportal::$_search['resumes']['middle_name'] : '';
            $lastName = isset(wpjobportal::$_search['resumes']['last_name']) ? wpjobportal::$_search['resumes']['last_name'] : '';
            if ($lastName != '') {
                $wpjobportal_inquery .= ' AND resume.last_name LIKE "%' . esc_sql($lastName) . '%" ';
                wpjobportal::$_data['filter']['last_name'] = $lastName;
                wpjobportal::$wpjobportal_data['issearchform'] = 1;
            }

            $wpjobportal_nationality = isset(wpjobportal::$_search['resumes']['nationality']) ? wpjobportal::$_search['resumes']['nationality'] : '';
            if ($wpjobportal_nationality != '' && is_numeric($wpjobportal_nationality)) {
                $wpjobportal_inquery .= ' AND resume.nationality =' . esc_sql($wpjobportal_nationality) . '';
                wpjobportal::$_data['filter']['nationality'] = $wpjobportal_nationality;
                wpjobportal::$wpjobportal_data['issearchform'] = 1;
            }

            $wpjobportal_gender = isset(wpjobportal::$_search['resumes']['gender']) ? wpjobportal::$_search['resumes']['gender'] : '';
            if ($wpjobportal_gender != '') {
                $wpjobportal_inquery .= ' AND resume.gender LIKE "%' . esc_sql($wpjobportal_gender) . '%" ';
                wpjobportal::$_data['filter']['gender'] = $wpjobportal_gender;
                wpjobportal::$wpjobportal_data['issearchform'] = 1;
            }

            $wpjobportal_salaryfixed = isset(wpjobportal::$_search['resumes']['salaryfixed']) ? wpjobportal::$_search['resumes']['salaryfixed'] : '';
            if ($wpjobportal_salaryfixed != '' && is_numeric($wpjobportal_salaryfixed)) {
                $wpjobportal_inquery .= ' AND resume.salaryfixed = "' . esc_sql($wpjobportal_salaryfixed).'"';// non numric value casuing query error without quotes
                wpjobportal::$_data['filter']['salaryfixed'] = $wpjobportal_salaryfixed;
                wpjobportal::$wpjobportal_data['issearchform'] = 1;
            }

            $wpjobportal_jobType = isset(wpjobportal::$_search['resumes']['jobtype']) ? wpjobportal::$_search['resumes']['jobtype'] : '';
            if ($wpjobportal_jobType != '' && is_numeric($wpjobportal_jobType)) {
                $wpjobportal_inquery .= ' AND resume.jobtype = ' . esc_sql($wpjobportal_jobType) . ' ';
                wpjobportal::$_data['filter']['jobtype'] = $wpjobportal_jobType;
                wpjobportal::$wpjobportal_data['issearchform'] = 1;
            }

            $wpjobportal_salaryRangeType = isset(wpjobportal::$_search['resumes']['salaryrangetype']) ? wpjobportal::$_search['resumes']['salaryrangetype'] : '';
            if ($wpjobportal_salaryRangeType != '' && is_numeric($wpjobportal_salaryRangeType)) {
                $wpjobportal_inquery .= ' AND resume.jobsalaryrangetype = ' . esc_sql($wpjobportal_salaryRangeType) . '  ';
                wpjobportal::$_data['filter']['salaryrangetype'] = $wpjobportal_salaryRangeType;
                wpjobportal::$wpjobportal_data['issearchform'] = 1;
            }

            $wpjobportal_category = isset(wpjobportal::$_search['resumes']['category']) ? wpjobportal::$_search['resumes']['category'] : '';
            if ($wpjobportal_category != '' && is_numeric($wpjobportal_category)) {
                $wpjobportal_inquery .= ' AND resume.job_category = ' . esc_sql($wpjobportal_category) . ' ';
                wpjobportal::$_data['filter']['category'] = $wpjobportal_category;
                wpjobportal::$wpjobportal_data['issearchform'] = 1;
            }

            $zipCode = isset(wpjobportal::$_search['resumes']['zipcode']) ? wpjobportal::$_search['resumes']['zipcode'] : '';
            if ($zipCode) {
                wpjobportal::$_data['filter']['zipcode'] = $zipCode;
            }

            $wpjobportal_keywords = isset(wpjobportal::$_search['resumes']['keywords']) ? wpjobportal::$_search['resumes']['keywords'] : '';
            if ($wpjobportal_keywords) {
                $res = $this->makeQueryFromArray('keywords', $wpjobportal_keywords);
                if ($res)
                    $wpjobportal_inquery .= " AND ( " . $res . " )";
                wpjobportal::$_data['filter']['keywords'] = $wpjobportal_keywords;
            }

            //Custom field search
            //start
            $wpjobportal_data = wpjobportal::$_wpjpcustomfield->userFieldsData(3);/*apply_filters('wpjobportal_addons_customFields_user',false,3,'userFieldsData')*/;
            $wpjobportal_valarray = array();
            if (!empty($wpjobportal_data)) {
                foreach ($wpjobportal_data as $uf) {
                    $wpjobportal_session_userfield = isset(wpjobportal::$_search['resume_custom_fields'][$uf->field]) ? wpjobportal::$_search['resume_custom_fields'][$uf->field] : '';

                    $wpjobportal_valarray[$uf->field] = $wpjobportal_session_userfield;
                    if (isset($wpjobportal_valarray[$uf->field]) && $wpjobportal_valarray[$uf->field] != null && $wpjobportal_valarray[$uf->field] !="" ) {
                        switch ($uf->userfieldtype) {
                            case 'text':
                            case 'email':
                                $wpjobportal_inquery .= ' AND resume.params REGEXP \'"' . esc_sql($uf->field) . '":"[^"]*' . wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_valarray[$uf->field]) . '.*"\' ';
                                break;
                            case 'combo':
                                $wpjobportal_inquery .= ' AND resume.params LIKE \'%"' . esc_sql($uf->field) . '":"' . wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_valarray[$uf->field]) . '"%\' ';
                                $or = " OR ";
                                break;
                            case 'depandant_field':
                                $wpjobportal_inquery .= ' AND resume.params LIKE \'%"' . esc_sql($uf->field) . '":"' . wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_valarray[$uf->field]) . '"%\' ';
                                break;
                            case 'radio':
                                $wpjobportal_inquery .= ' AND resume.params LIKE \'%"' . esc_sql($uf->field) . '":"' . wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_valarray[$uf->field]) . '"%\' ';
                                break;
                            case 'checkbox':
                                $finalvalue = '';
                                foreach($wpjobportal_valarray[$uf->field] AS $wpjobportal_value){
                                    $finalvalue .= $wpjobportal_value.'.*';
                                }
                                $wpjobportal_inquery .= ' AND resume.params REGEXP \'"' . esc_sql($uf->field) . '":"[^"]*' . wpjobportalphplib::wpJP_htmlspecialchars($finalvalue) . '.*"\' ';
                                break;
                            case 'date':
                                if (isset($wpjobportal_valarray[$uf->field]) && $wpjobportal_valarray[$uf->field] != '') {
                                    $wpjobportal_valarray[$uf->field] = gmdate('Y-m-d H:i:s',strtotime($wpjobportal_valarray[$uf->field]));
                                }
                                $wpjobportal_inquery .= ' AND resume.params LIKE \'%"' . esc_sql($uf->field) . '":"' . wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_valarray[$uf->field]) . '"%\' ';
                                break;
                            case 'textarea':
                                $wpjobportal_inquery .= ' AND resume.params REGEXP \'"' . esc_sql($uf->field) . '":"[^"]*' . wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_valarray[$uf->field]) . '.*"\' ';
                                break;
                            case 'multiple':
                                $finalvalue = '';
                                foreach($wpjobportal_valarray[$uf->field] AS $wpjobportal_value){
                                    if($wpjobportal_value){
                                        $finalvalue .= $wpjobportal_value.'.*';
                                    }
                                }
                                if($finalvalue){
                                    $wpjobportal_inquery .= ' AND resume.params REGEXP \'"' . esc_sql($uf->field) . '":"[^"]*'.wpjobportalphplib::wpJP_htmlspecialchars($finalvalue).'"\'';
                                }
                                break;
                        }
                        wpjobportal::$_data['filter']['params'] = $wpjobportal_valarray;
                        wpjobportal::$wpjobportal_data['issearchform'] = 1;
                    }
                }
            }
            //end
            $wpjobportal_tags = WPJOBPORTALrequest::getVar('tags');
            if ($wpjobportal_tags) {
                wpjobportal::$_data['filter']['tag'] = WPJOBPORTALincluder::getJSModel('common')->makeFilterdOrEditedTagsToReturn($wpjobportal_tags);
                $res = $this->makeQueryFromArray('tags', $wpjobportal_tags);
                if ($res)
                    $wpjobportal_inquery .= " AND ( " . $res . " )";
            }
            $city = isset(wpjobportal::$_search['resumes']['city']) ? wpjobportal::$_search['resumes']['city'] : '';
            if ($city != '') {
                wpjobportal::$_data['filter']['city'] = WPJOBPORTALincluder::getJSModel('common')->getCitiesForFilter($city);
                $res = $this->makeQueryFromArray('city', $city);
                if ($res)
                    $wpjobportal_inquery .= " AND ( " . $res . " )";
            }

            if($wpjobportal_show_only_featured == 1){
                $wpjobportal_inquery .= " AND resume.isfeaturedresume = 1 AND DATE(resume.endfeatureddate) >= CURDATE() ";
            }

        // shortcode options
        $wpjobportal_noofresumes = '';
        $wpjobportal_module_name = WPJOBPORTALrequest::getVar('wpjobportalme');
        if($wpjobportal_module_name == 'allresumes'){
            //shortcode attribute proceesing (filter,ordering,no of resume)
            $attributes_query = $this->processShortcodeAttributesResume();
            if($attributes_query != ''){
                $wpjobportal_inquery .= $attributes_query;
            }
            if(isset(wpjobportal::$_data['shortcode_option_no_of_resumes']) && wpjobportal::$_data['shortcode_option_no_of_resumes'] > 0){
                $wpjobportal_noofresumes = wpjobportal::$_data['shortcode_option_no_of_resumes'];
            }
        }
        if($dont_prep_data == 0){
            //Pagination
            if($wpjobportal_noofresumes == ''){
                $query = "SELECT COUNT(resume.id) AS total
                        FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume
                        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS category ON category.id = resume.job_category ";
                    if($zipCode != ''){
                        $query .= " JOIN `" . wpjobportal::$_db->prefix . "wj_portal_resumeaddresses` AS address1 ON (address1.resumeid = resume.id AND address1.address_zipcode = '".esc_sql($zipCode)."' ) ";
                    }elseif ($city != '') {
                        $query .= " JOIN `" . wpjobportal::$_db->prefix . "wj_portal_resumeaddresses` AS address1 ON address1.resumeid = resume.id ";
                    }elseif (strstr($wpjobportal_inquery, 'address1.address_city')) { // to handle shortcode option case for locations
                        $query .= " JOIN `" . wpjobportal::$_db->prefix . "wj_portal_resumeaddresses` AS address1 ON address1.resumeid = resume.id ";
                    }
                    $query .= " WHERE resume.status = 1 AND resume.quick_apply <> 1 ";
                    if($wpjobportal_ignore_serachable == 0){
                        $query .= " AND resume.searchable = 1 ";
                    }
                $query .= $wpjobportal_inquery;
                $wpjobportal_total = wpjobportaldb::get_var($query);
                wpjobportal::$_data['total'] = $wpjobportal_total;
                wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($wpjobportal_total,'resumes');
            }

            //Data
            $query = "SELECT resume.id,CONCAT(resume.alias,'-',resume.id) AS resumealiasid ,resume.first_name
                    ,resume.last_name,resume.application_title as applicationtitle,resume.email_address,category.cat_title
                    ,resume.created,jobtype.title AS jobtypetitle,resume.photo,
                    resume.isfeaturedresume,resume.endfeatureddate
                    ,resume.status,city.name AS cityname
                    ,state.name AS statename,resume.params,resume.salaryfixed as salary
                    ,resume.last_modified,LOWER(jobtype.title) AS jobtypetit,jobtype.color as jobtypecolor,country.name AS countryname,resume.id as resumeid,resume.skills,resume.quick_apply
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS category ON category.id = resume.job_category ";
                if($zipCode != ''){
                    $query .= " JOIN `" . wpjobportal::$_db->prefix . "wj_portal_resumeaddresses` AS address1 ON (address1.resumeid = resume.id AND address1.address_zipcode = '".esc_sql($zipCode)."' ) ";
                }elseif ($city != '') {
                    $query .= " JOIN `" . wpjobportal::$_db->prefix . "wj_portal_resumeaddresses` AS address1 ON address1.resumeid = resume.id ";
                }elseif (strstr($wpjobportal_inquery, 'address1.address_city')) { // to handle shortcode option case for locations
                    $query .= " JOIN `" . wpjobportal::$_db->prefix . "wj_portal_resumeaddresses` AS address1 ON address1.resumeid = resume.id ";
                }
                $query .= "
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON jobtype.id = resume.jobtype
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON city.id = (SELECT address_city FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumeaddresses` WHERE resumeid = resume.id LIMIT 1)
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_states` AS state ON state.id = city.stateid
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_countries` AS country ON country.id = city.countryid ";
                    $query .= " WHERE resume.status = 1 AND resume.quick_apply <> 1";
                    if($wpjobportal_ignore_serachable == 0){
                        $query .= " AND resume.searchable = 1 ";
                    }
            $query .= $wpjobportal_inquery;
            $query .= " GROUP BY resume.id ";
            $query.= " ORDER BY " . wpjobportal::$_data['sorting'];
            if($wpjobportal_noofresumes == ''){
                $query .=" LIMIT " . WPJOBPORTALpagination::$_offset . "," . WPJOBPORTALpagination::$_limit;
            }elseif(is_numeric($wpjobportal_noofresumes)){
                $query .=" LIMIT " . esc_sql($wpjobportal_noofresumes);
            }

            $wpjobportal_results = wpjobportal::$_db->get_results($query);
            $wpjobportal_data = array();
            foreach ($wpjobportal_results AS $d) {
                //  updated the query select to select 'name' as cityname
                $d->location = WPJOBPORTALincluder::getJSModel('common')->getLocationForView($d->cityname, $d->statename, $d->countryname);
                $wpjobportal_data[] = $d;
            }
            wpjobportal::$_data[0] = $wpjobportal_data;
        }

        wpjobportal::$_data['config'] = wpjobportal::$_config->getConfigByFor('resume');
        wpjobportal::$_data[2] = wpjobportal::$_wpjpfieldordering->getFieldsOrderingforSearch(3);
        wpjobportal::$_data['listingfields'] = wpjobportal::$_wpjpfieldordering->getFieldsForListing(3);
        // not counting the resume searches in transaction log
        return;
    }
    ///

    public function makeQueryFromArray($for, $wpjobportal_array) {
        if (empty($wpjobportal_array))
            return false;
        $qa = array();
        switch ($for) {
            case 'keywords':
                $wpjobportal_array = wpjobportalphplib::wpJP_explode(",", $wpjobportal_array);
                $wpjobportal_total = count($wpjobportal_array);
                for ($wpjobportal_i = 0; $wpjobportal_i < $wpjobportal_total; $wpjobportal_i++) {
                    $qa[] = "resume.keywords LIKE '%" . esc_sql(wpjobportalphplib::wpJP_trim($wpjobportal_array[$wpjobportal_i])) . "%'";
                }
                break;
            case 'tags':
                $wpjobportal_array = wpjobportalphplib::wpJP_explode(',', $wpjobportal_array);
                foreach ($wpjobportal_array as $wpjobportal_item) {
                    $qa[] = "resume.tags LIKE '%" . esc_sql($wpjobportal_item) . "%'";
                }
                break;
            case 'city':
                $wpjobportal_array = wpjobportalphplib::wpJP_explode(',', $wpjobportal_array);
                foreach ($wpjobportal_array as $wpjobportal_item) {
                    if(is_numeric($wpjobportal_item)){
                        $qa[] = " address1.address_city = " . esc_sql($wpjobportal_item);
                    }
                }
                break;
        }
        $query = implode(" OR ", $qa);
        return $query;
    }

    function getAllResumeFiles() {
        $wpjobportal_resumeid = WPJOBPORTALrequest::getVar('resumeid');
        // $wpjobportal_resumeid = WPJOBPORTALincluder::getJSModel('common')->decodeIdForDownload($wpjobportal_resumeid_string);

        if(!is_numeric($wpjobportal_resumeid)){
            return false;
        }
        do_action('wpjobportal_load_wp_pcl_zip');
        $wpjobportal_data_directory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
        $wpjobportal_path = WPJOBPORTAL_PLUGIN_PATH . $wpjobportal_data_directory;

        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
        if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ) {
            $creds = request_filesystem_credentials( site_url() );
            wp_filesystem( $creds );
        }

        if (!$wp_filesystem->exists($wpjobportal_path)) {
            WPJOBPORTALincluder::getJSModel('common')->makeDir($wpjobportal_path);
        }
        $wpjobportal_path .= '/zipdownloads';
        if (!$wp_filesystem->exists($wpjobportal_path)) {
            WPJOBPORTALincluder::getJSModel('common')->makeDir($wpjobportal_path);
        }
        $wpjobportal_randomfolder = $this->getRandomFolderName($wpjobportal_path);
        $wpjobportal_path .= '/' . $wpjobportal_randomfolder;
        if (!$wp_filesystem->exists($wpjobportal_path)) {
            WPJOBPORTALincluder::getJSModel('common')->makeDir($wpjobportal_path);
        }
        $archive = new PclZip($wpjobportal_path . '/allresumefiles.zip');
        $wpjobportal_wpdir = wp_upload_dir();
        $wpjobportal_directory = $wpjobportal_wpdir['basedir'] . '/' . $wpjobportal_data_directory . '/data/jobseeker/resume_' . $wpjobportal_resumeid . '/resume/';
        //$scanned_directory = array_diff(scandir($wpjobportal_directory), array('..', '.'));// code seems reduntant but showing error on deleted resume file downloads
        $filelist = '';
        $query = "SELECT filename FROM `".wpjobportal::$_db->prefix."wj_portal_resumefiles` WHERE resumeid = ".esc_sql($wpjobportal_resumeid);
        $files = wpjobportal::$_db->get_results($query);
        foreach ($files AS $file) {
            $filelist .= $wpjobportal_directory . '/' . $file->filename . ',';
        }
        $filelist = wpjobportalphplib::wpJP_substr($filelist, 0, wpjobportalphplib::wpJP_strlen($filelist) - 1);
        $v_list = $archive->create($filelist, PCLZIP_OPT_REMOVE_PATH, $wpjobportal_directory);
        if ($v_list == 0) {
            die("Error : '" . wp_kses($archive->errorInfo(),WPJOBPORTAL_ALLOWED_TAGS) . "'");
        }
        $file = $wpjobportal_path . '/allresumefiles.zip';
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . wpjobportalphplib::wpJP_basename($file));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        ob_clean();//this was commented and causing problems
        flush();
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
        if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ) {
            $creds = request_filesystem_credentials( site_url() );
            wp_filesystem( $creds );
        }

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Binary file download output
        echo $wp_filesystem->get_contents($file);
        @wp_delete_file($file);
        $wpjobportal_path = WPJOBPORTAL_PLUGIN_PATH . $wpjobportal_data_directory;
        $wpjobportal_path .= '/zipdownloads';
        $wpjobportal_path .= '/' . $wpjobportal_randomfolder;
        @wp_delete_file($wpjobportal_path . '/index.html');


        $wp_filesystem->rmdir($wpjobportal_path);
        exit();
    }

    function getResumeFileDownloadById($fileid) {

        //$fileid = WPJOBPORTALincluder::getJSModel('common')->decodeIdForDownload($fileid_string);

        if (!is_numeric($fileid))
            return false;
        $query = "SELECT filename,resumeid FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumefiles` WHERE id = " . esc_sql($fileid);
        $object = wpjobportal::$_db->get_row($query);
        if(empty($object) || !is_numeric($object->resumeid)){ // if the file record does not exsists.(was logging error on accesing properties for null/empty object)
            exit;
        }
        $wpjobportal_data_directory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
        $wpjobportal_wpdir = wp_upload_dir();
        $file =  $wpjobportal_wpdir['basedir'] . '/' . $wpjobportal_data_directory . '/data/jobseeker/resume_' . $object->resumeid . '/resume/' . $object->filename;

        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
        if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ) {
            $creds = request_filesystem_credentials( site_url() );
            wp_filesystem( $creds );
        }

        if (!$wp_filesystem->exists($file)) {// if file does not exsit then stop executing code(handling log errors)
            exit;
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . wpjobportalphplib::wpJP_basename($file));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
//        ob_clean();
        flush();
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Binary file download output
        echo $wp_filesystem->get_contents($file);
        exit();
    }

    function getRandomFolderName($wpjobportal_path) {
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
        if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ) {
            $creds = request_filesystem_credentials( site_url() );
            wp_filesystem( $creds );
        }

        $wpjobportal_match = '';
        do {
            $rndfoldername = "";
            $length = 5;
            $possible = "2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ";
            $wpjobportal_maxlength = wpjobportalphplib::wpJP_strlen($possible);
            if ($length > $wpjobportal_maxlength) {
                $length = $wpjobportal_maxlength;
            }
            $wpjobportal_i = 0;
            while ($wpjobportal_i < $length) {
                $char = wpjobportalphplib::wpJP_substr($possible, wp_rand(0, $wpjobportal_maxlength - 1), 1);
                if (!wpjobportalphplib::wpJP_strstr($rndfoldername, $char)) {
                    if ($wpjobportal_i == 0) {
                        if (ctype_alpha($char)) {
                            $rndfoldername .= $char;
                            $wpjobportal_i++;
                        }
                    } else {
                        $rndfoldername .= $char;
                        $wpjobportal_i++;
                    }
                }
            }
            $folderexist = $wpjobportal_path . '/' . $rndfoldername;
            if ($wp_filesystem->exists($folderexist))
                $wpjobportal_match = 'Y';
            else
                $wpjobportal_match = 'N';
        }while ($wpjobportal_match == 'Y');

        return $rndfoldername;
    }

    function getResumenameById($wpjobportal_resumeid) {
        if (!is_numeric($wpjobportal_resumeid))
            return false;
        $query = "SELECT resume.application_title FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume WHERE resume.id = " . esc_sql($wpjobportal_resumeid);
        $wpjobportal_resumename = wpjobportal::$_db->get_var($query);
        return $wpjobportal_resumename;
    }

    function addViewContactDetail($wpjobportal_resumeid, $wpjobportal_uid) {
        $wpjobportal_profileid = 0;
        if(wpjobportalphplib::wpJP_strstr($wpjobportal_resumeid, 'jssc-')){
            $wpjobportal_array = wpjobportalphplib::wpJP_explode('-', $wpjobportal_resumeid);
            $wpjobportal_profileid = $wpjobportal_array[1];
            $wpjobportal_resumeid = 0;
        }
        if (!is_numeric($wpjobportal_profileid))
            return false;
        if (!is_numeric($wpjobportal_resumeid))
            return false;
        if (!is_numeric($wpjobportal_uid))
            return false;
        $wpjobportal_curdate = gmdate('Y-m-d H:i:s');
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('employerviewresume');
        $wpjobportal_data = array();
        if(in_array('credits', wpjobportal::$_active_addons)){
            #Submission Type
            $wpjobportal_subType = wpjobportal::$_config->getConfigValue('submission_type');
            if ($wpjobportal_subType == 3) {
                #Membershipe Code for Featured Resume
                $wpjobportal_packageid = WPJOBPORTALrequest::getVar('wpjobportal_packageid');
                # Package Filter's
                $wpjobportal_package = apply_filters('wpjobportal_addons_userpackages_perfeaturemodule',false,$wpjobportal_packageid,'remresumecontactdetail',$wpjobportal_uid);
                if($wpjobportal_package && !$wpjobportal_package->expired && ($wpjobportal_package->resumecontactdetail==-1 || $wpjobportal_package->resumecontactdetail)){ //-1 = unlimited
                    #Data For Featured Company Member
                    $wpjobportal_data['uid'] = $wpjobportal_uid;
                    $wpjobportal_data['resumeid'] = $wpjobportal_resumeid;
                    $wpjobportal_data['status'] = 1;
                    $wpjobportal_data['created'] = $wpjobportal_curdate;
                    $wpjobportal_data['profileid'] = $wpjobportal_profileid;
                    $wpjobportal_data['userpackageid'] = $wpjobportal_package->packageid;
                    $wpjobportal_data = wpjobportal::wpjobportal_sanitizeData($wpjobportal_data);
                    #Job sekker Company View
                    if($this->checkAlreadyViewResumeContactDetail($wpjobportal_resumeid) == false){
                        if($wpjobportal_row->bind($wpjobportal_data)){
                            if($wpjobportal_row->store()){
                                # Company Contact View Resume Transactio Log Entries--
                                $wpjobportal_trans = WPJOBPORTALincluder::getJSTable('transactionlog');
                                $wpjobportal_arr = array();
                                $wpjobportal_arr['userpackageid'] = $wpjobportal_package->id;
                                $wpjobportal_arr['uid'] = $wpjobportal_uid;
                                $wpjobportal_arr['recordid'] = $wpjobportal_resumeid;
                                $wpjobportal_arr['type'] = 'resumecontactdetail';
                                $wpjobportal_arr['created'] = current_time('mysql');
                                $wpjobportal_arr['status'] = 1;
                                $wpjobportal_trans->bind($wpjobportal_arr);
                                $wpjobportal_trans->store();
                                WPJOBPORTALmessages::setLayoutMessage(esc_html(__('You can view Resume Contact Detail Now','wp-job-portal')), 'updated',$this->getMessagekey());
                                return true;
                            }else{
                                return false;
                            }
                        }
                    }else{
                        return false;
                    }
                }else{
                    WPJOBPORTALmessages::setLayoutMessage(esc_html(__("There was some problem performing action",'wp-job-portal')), 'error',$this->getMessagekey());
                    return false;
                }
            }elseif ($wpjobportal_subType == 2) {
                # Paid Perlisting
                $wpjobportal_data['status']  == 3;
            }elseif ($wpjobportal_status == 1) {
                # Free
                $wpjobportal_data['status'] == 1;
            }

        }
        $wpjobportal_data['uid'] = $wpjobportal_uid;
        $wpjobportal_data['resumeid'] = $wpjobportal_resumeid;
        if(!isset($wpjobportal_data['status'])){
            $wpjobportal_data['status'] = 1;
        }
        $wpjobportal_data['created'] = $wpjobportal_curdate;
        $wpjobportal_data['profileid'] = $wpjobportal_profileid;
        $wpjobportal_data = wpjobportal::wpjobportal_sanitizeData($wpjobportal_data);
        if (!$wpjobportal_row->bind($wpjobportal_data)) {
            return false;
        }

        if($wpjobportal_row->store()){
            return true;
        }else{
            return false;
        }
    }

    function getOrdering() {
        $sort = WPJOBPORTALrequest::getVar('sortby', '', null);
        if ($sort == null) {
            $wpjobportal_id = WPJOBPORTALrequest::getVar('wpjobportalid');
            if ($wpjobportal_id != null) {
                $wpjobportal_array = wpjobportalphplib::wpJP_explode('_', $wpjobportal_id);
                if ($wpjobportal_array[1] == '14') {
                    $sort = $wpjobportal_array[0];
                }
            }
        }else{
            $wpjobportal_array = wpjobportalphplib::wpJP_explode('_', $sort);
            if (isset($wpjobportal_array[1]) && $wpjobportal_array[1] == '14') {
                $sort = $wpjobportal_array[0];
            }
        }
        if ($sort == null) {
            $sort = 'posteddesc';
        }

        $this->getListOrdering($sort);
        $this->getListSorting($sort);
    }

    function getListOrdering($sort) {
        switch ($sort) {
            case "titledesc":
                wpjobportal::$_ordering = "resume.application_title DESC";
                wpjobportal::$_sorton = "title";
                wpjobportal::$_sortorder = "DESC";
                break;
            case "titleasc":
                wpjobportal::$_ordering = "resume.application_title ASC";
                wpjobportal::$_sorton = "title";
                wpjobportal::$_sortorder = "ASC";
                break;
            case "jobtypedesc":
                wpjobportal::$_ordering = "jobtype.title DESC";
                wpjobportal::$_sorton = "jobtype";
                wpjobportal::$_sortorder = "DESC";
                break;
            case "jobtypeasc":
                wpjobportal::$_ordering = "jobtype.title ASC";
                wpjobportal::$_sorton = "jobtype";
                wpjobportal::$_sortorder = "ASC";
                break;
            case "salarydesc":
                wpjobportal::$_ordering = "salaryrangestart.rangestart DESC";
                wpjobportal::$_sorton = "salary";
                wpjobportal::$_sortorder = "DESC";
                break;
            case "salaryasc":
                wpjobportal::$_ordering = "salaryrangestart.rangestart ASC";
                wpjobportal::$_sorton = "salary";
                wpjobportal::$_sortorder = "ASC";
                break;
            case "posteddesc":
                wpjobportal::$_ordering = "resume.created DESC";
                wpjobportal::$_sorton = "posted";
                wpjobportal::$_sortorder = "DESC";
                break;
            case "postedasc":
                wpjobportal::$_ordering = "resume.created ASC";
                wpjobportal::$_sorton = "posted";
                wpjobportal::$_sortorder = "ASC";
                break;
            default: wpjobportal::$_ordering = "resume.created DESC";
        }
        return;
    }

    function getSortArg($type, $sort) {
        $wpjobportal_mat = array();
        if (wpjobportalphplib::wpJP_preg_match("/(\w+)(asc|desc)/i", $sort, $wpjobportal_mat)) {
            if ($type == $wpjobportal_mat[1]) {
                return ( $wpjobportal_mat[2] == "asc" ) ? "{$type}desc" : "{$type}asc";
            } else {
                return $type . $wpjobportal_mat[2];
            }
        }
        return "iddesc";
    }

    function getListSorting($sort) {
        wpjobportal::$_sortlinks['title'] = $this->getSortArg("title", $sort);
        wpjobportal::$_sortlinks['salary'] = $this->getSortArg("salary", $sort);
        wpjobportal::$_sortlinks['jobtype'] = $this->getSortArg("jobtype", $sort);
        wpjobportal::$_sortlinks['posted'] = $this->getSortArg("posted", $sort);
        return;
    }

    function removeResumeFileById() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'remove-resume-file-by-id') ) {
            die( 'Security check Failed' );
        }
        $wpjobportal_id = WPJOBPORTALrequest::getVar('id');
        if (!is_numeric($wpjobportal_id))
            return false;
        if(current_user_can('manage_options')){
            $wpjobportal_uid = ' resume.uid ';
        }else{
            $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        }
        $query = "SELECT COUNT(file.id) AS file, resume.id AS resumeid, file.filename
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume
                    JOIN `" . wpjobportal::$_db->prefix . "wj_portal_resumefiles` AS file ON file.resumeid = resume.id
                    WHERE resume.uid = ". esc_sql($wpjobportal_uid)." AND file.id = " . esc_sql($wpjobportal_id);
        $file = wpjobportal::$_db->get_row($query);
        if ($file->file > 0) { // You are the owner
            $query = "DELETE FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumefiles` WHERE id = " . esc_sql($wpjobportal_id);
            wpjobportal::$_db->query($query);
            $wpjobportal_wpdir = wp_upload_dir();
            $wpjobportal_data_directory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
            $file = $wpjobportal_wpdir['basedir'] . '/' . $wpjobportal_data_directory . '/data/jobseeker/resume_' . $file->resumeid . '/resume/' . $file->filename;
            @wp_delete_file($file);
            return true;
        }
        return false;
    }

    function getRssResumes() {
        $wpjobportal_resume_rss = wpjobportal::$_config->getConfigurationByConfigName('resume_rss');
        if ($wpjobportal_resume_rss == 1) {
            $wpjobportal_curdate = date_i18n('Y-m-d H:i:s');
            $query = "SELECT resume.id,resume.application_title,resume.photo,resume.first_name,resume.last_name,
                        resume.email_address,cat.cat_title,resume.gender,
                        CONCAT(resume.alias,'-',resume.id) AS resumealiasid
                        FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume
                        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat ON resume.job_category = cat.id
                        ";
            $wpjobportal_result = wpjobportal::$_db->get_results($query);
            foreach ($wpjobportal_result AS $rs) {
                if(!is_numeric($rs->id)){
                    continue;
                }
                $query = "SELECT filename,filetype,filesize FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumefiles` WHERE resumeid = " . esc_sql($rs->id);
                $rs->filename = wpjobportal::$_db->get_results($query);
            }
            return $wpjobportal_result;
        }
        var_dump($query);
        //return false;
    }
    function makeResumeSeo($wpjobportal_resume_seo , $wpjobportalid){
        if(empty($wpjobportal_resume_seo))
            return '';

        $wpjobportal_common = WPJOBPORTALincluder::getJSModel('common');
        $wpjobportal_id = $wpjobportal_common->parseID($wpjobportalid);
        if(! is_numeric($wpjobportal_id))
            return '';

        $wpjobportal_result = '';
        $wpjobportal_resume_seo = wpjobportalphplib::wpJP_str_replace( ' ', '', $wpjobportal_resume_seo);
        $wpjobportal_resume_seo = wpjobportalphplib::wpJP_str_replace( '[', '', $wpjobportal_resume_seo);
        $wpjobportal_array = wpjobportalphplib::wpJP_explode(']', $wpjobportal_resume_seo);

        $wpjobportal_total = count($wpjobportal_array);
        if($wpjobportal_total > 3)
            $wpjobportal_total = 3;

        for ($wpjobportal_i=0; $wpjobportal_i < $wpjobportal_total; $wpjobportal_i++) {
            $query = '';
            switch ($wpjobportal_array[$wpjobportal_i]) {
                case 'title':
                    $query = "SELECT application_title AS col FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` WHERE id = " . esc_sql($wpjobportal_id);
                break;
                case 'category':
                    $query = "SELECT category.cat_title AS col
                        FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume
                        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS category ON category.id = resume.job_category
                        WHERE resume.id = " . esc_sql($wpjobportal_id);
                break;
                case 'location':
                    $locationquery = "SELECT ra.address_city AS col
                        FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumeaddresses` AS ra
                        JOIN `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume ON resume.id = ra.resumeid
                        WHERE resume.id = " . esc_sql($wpjobportal_id);
                break;
            }

            if($wpjobportal_array[$wpjobportal_i] == 'location'){
                $wpjobportal_rows = wpjobportaldb::get_results($locationquery);
                $location = '';
                foreach ($wpjobportal_rows as $wpjobportal_row) {
                    if($wpjobportal_row->col != '' && is_numeric($wpjobportal_row->col)){
                        $query = "SELECT name FROM `" . wpjobportal::$_db->prefix . "wj_portal_cities` WHERE id = ". esc_sql($wpjobportal_row->col);
                        $cityname = wpjobportaldb::get_row($query);
                        if(isset($cityname->name)){
                            if($location == '')
                                $location .= $cityname->name;
                            else
                                $location .= ' '.$cityname->name;
                        }
                    }
                }
                $location = $wpjobportal_common->removeSpecialCharacter($location);
                // if url encoded string is different from the orginal string dont add it to url
                $wpjobportal_val = $location;
                $test_val = urlencode($wpjobportal_val);
                if($wpjobportal_val != $test_val){
                    continue;
                }
                if($location != ""){
                    if($wpjobportal_result == '')
                        $wpjobportal_result .= wpjobportalphplib::wpJP_str_replace(' ', '-', $location);
                    else{
                        $wpjobportal_result .= '-'.wpjobportalphplib::wpJP_str_replace(' ', '-', $location);
                    }
                }
            }else{
                if($query){
                    $wpjobportal_data = wpjobportaldb::get_row($query);
                    if(isset($wpjobportal_data->col)){
                        $wpjobportal_val = $wpjobportal_common->removeSpecialCharacter($wpjobportal_data->col);
                        // if url encoded string is different from the orginal string dont add it to url
                        $test_val = urlencode($wpjobportal_val);
                        if($wpjobportal_val != $test_val){
                            continue;
                        }
                        if($wpjobportal_result == '')
                            $wpjobportal_result .= wpjobportalphplib::wpJP_str_replace(' ', '-', $wpjobportal_val);
                        else
                            $wpjobportal_result .= '-'.wpjobportalphplib::wpJP_str_replace(' ', '-', $wpjobportal_val);
                    }
                }
            }
        }
        if($wpjobportal_result != ''){
            $wpjobportal_result = wpjobportalphplib::wpJP_str_replace('_', '-', $wpjobportal_result);
        }
        return $wpjobportal_result;
    }

    function makeResumeSeoDocumentTitle($wpjobportal_resume_seo , $wpjobportalid){
        if(empty($wpjobportal_resume_seo))
            return '';

        $wpjobportal_common = wpjobportal::$_common;
        $wpjobportal_id = $wpjobportal_common->parseID($wpjobportalid);
        if(! is_numeric($wpjobportal_id))
            return '';
        $wpjobportal_result = '';

        $wpjobportal_application_title = '';
        $wpjobportal_category_title = '';
        $wpjobportal_jobtype_title = '';
        $wpjobportal_resume_location = '';

        $query = "SELECT resume.application_title, category.cat_title AS resumecategory,jobtype.title AS resumejobtype
                        FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume
                        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS category ON category.id = resume.job_category
                        LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON jobtype.id = resume.jobtype
                        WHERE resume.id = " . esc_sql($wpjobportal_id);

        $wpjobportal_data = wpjobportaldb::get_row($query);

        if(!empty($wpjobportal_data)){
            $wpjobportal_resume_location = '';
            $query = "SELECT city.name AS cityname
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumeaddresses` resumeaddress
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON city.id = resumeaddress.address_city
                WHERE resumeaddress.resumeid = " . esc_sql($wpjobportal_id);
                $city_data = wpjobportaldb::get_results($query);
            if(!empty($city_data)){
                //  updated the query select to select 'name' as cityname
                foreach ($city_data as $wpjobportal_key => $city) {
                    if($wpjobportal_resume_location == ''){
                        $wpjobportal_resume_location .= $city->cityname;
                    }else{
                        $wpjobportal_resume_location .= ', '.$city->cityname;
                    }
                }
            }
            $wpjobportal_application_title = $wpjobportal_data->application_title;
            $wpjobportal_category_title = $wpjobportal_data->resumecategory;
            $wpjobportal_jobtype_title = $wpjobportal_data->resumejobtype;
            $wpjobportal_matcharray = array(
                '[applicationtitle]' => $wpjobportal_application_title,
                '[jobcategory]' => $wpjobportal_category_title,
                '[jobtype]' => $wpjobportal_jobtype_title,
                '[location]' => $wpjobportal_resume_location,
                '[separator]' => '-',
                '[sitename]' => get_bloginfo( 'name', 'display' )
            );
            $wpjobportal_result = $this->replaceMatches($wpjobportal_resume_seo,$wpjobportal_matcharray);

            //echo var_dump($wpjobportal_result);die("3499");

        }

        return $wpjobportal_result;
    }

    function replaceMatches($wpjobportal_string, $wpjobportal_matcharray) {
        foreach ($wpjobportal_matcharray AS $find => $replace) {
            $wpjobportal_string = wpjobportalphplib::wpJP_str_replace($find, $replace, $wpjobportal_string);
        }
        return $wpjobportal_string;
    }

    //getAllRoleLessUsersAjax

   function getMyResumes($wpjobportal_uid) {
        if (!is_numeric($wpjobportal_uid))
            return false;
        $this->sortingrescat();
        //$this->getOrdering();
        $query = "SELECT COUNT(resume.id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS category ON category.id = resume.job_category
                WHERE resume.uid =". esc_sql($wpjobportal_uid)." AND resume.quick_apply <> 1 ";
        $wpjobportal_total = wpjobportaldb::get_var($query);
        if(!in_array('multiresume', wpjobportal::$_active_addons) && $wpjobportal_total > 1){
            $wpjobportal_total = 1;
        }
        wpjobportal::$_data['total'] = $wpjobportal_total;
        wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($wpjobportal_total,'myresume');

        $query = "SELECT resume.id,resume.first_name,resume.last_name,resume.application_title as applicationtitle,CONCAT(resume.alias,'-',resume.id) resumealiasid,resume.email_address,category.cat_title,resume.created,jobtype.title AS jobtypetitle,resume.photo,resume.salaryfixed as salary,
                resume.isfeaturedresume,resume.status,city.name AS cityname,state.name AS statename,country.name AS countryname,resume.id as resumeid,resume.endfeatureddate,resume.params,resume.last_modified,LOWER(jobtype.title) AS jobtypetit,jobtype.color as jobtypecolor,resume.skills,resume.quick_apply
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS category ON category.id = resume.job_category
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON jobtype.id = resume.jobtype
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON city.id = (SELECT address_city FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumeaddresses` WHERE resumeid = resume.id LIMIT 1)
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_states` AS state ON state.id = city.stateid
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_countries` AS country ON country.id = city.countryid
                WHERE resume.uid = ". esc_sql($wpjobportal_uid)." AND resume.quick_apply <> 1 ";
        if(in_array('multiresume', wpjobportal::$_active_addons)){
            $query.= " ORDER BY " . wpjobportal::$_data['sorting'];
            $query.=" LIMIT " . WPJOBPORTALpagination::$_offset . "," . WPJOBPORTALpagination::$_limit;
        }else{
            $query.=" ORDER BY resume.id ASC LIMIT 0,1 ";
        }
        $wpjobportal_results = wpjobportal::$_db->get_results($query);
        $wpjobportal_data = array();
        foreach ($wpjobportal_results AS $d) {//  updated the query select to select 'name' as cityname
            $d->location = wpjobportal::$_common->getLocationForView($d->cityname, $d->statename, $d->countryname);
            $wpjobportal_data[] = $d;
        }
        wpjobportal::$wpjobportal_data['fields'] = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldsOrderingforView(3);
        wpjobportal::$_data[0] = $wpjobportal_data;
        wpjobportal::$_data['config'] = wpjobportal::$_config->getConfigByFor('resume');
        wpjobportal::$_data['listingfields'] = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldsForListing(3);
        // to handle left menu/ my resume page add resume link case
        $query = "SELECT resume.id as resumeid
                    FROM " . wpjobportal::$_db->prefix . "wj_portal_resume AS resume
                    WHERE `uid`='".esc_sql($wpjobportal_uid)."' AND resume.quick_apply <> 1
                    GROUP BY resume.id  ORDER BY resume.id ASC LIMIT 0,1 "; // made the condition same as to remove inconsistency
        wpjobportal::$wpjobportal_data['resumeid'] = wpjobportaldb::get_var($query);
        return;
    }


    function getResumeByCategory() {
        $query = "SELECT category.cat_title, CONCAT(category.alias,'-',category.id) AS aliasid,category.serverid,category.id AS categoryid
            ,(SELECT count(resume.id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume
                where resume.job_category = category.id AND resume.status = 1 AND resume.searchable = 1 AND resume.quick_apply <> 1)  AS totaljobs
            FROM `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS category
            WHERE category.isactive = 1 AND category.parentid = 0 ORDER BY category.ordering ASC";
        $wpjobportal_categories = wpjobportaldb::get_results($query);
        $wpjobportal_config_array = wpjobportal::$_config->getConfigByFor('category');

        $wpjobportal_subcategory_limit = 3;
        if($wpjobportal_config_array['subcategory_limit'] != ''){ // to handle float value in configuration
            $wpjobportal_subcategory_limit = ceil($wpjobportal_config_array['subcategory_limit']);
        }

        foreach($wpjobportal_categories AS $wpjobportal_category){
            $wpjobportal_total = 0;
            if(!is_numeric($wpjobportal_category->categoryid)){
                continue;
            }
            $query = "SELECT category.cat_title, CONCAT(category.alias,'-',category.id) AS aliasid,category.serverid
                ,(SELECT count(resume.id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume
                where resume.job_category = category.id AND resume.status = 1 AND resume.searchable = 1 AND resume.quick_apply <> 1)  AS totaljobs
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS category
                WHERE category.isactive = 1 AND category.parentid = ".esc_sql($wpjobportal_category->categoryid)." ORDER BY category.ordering ASC ";
            $wpjobportal_subcats = wpjobportal::$_db->get_results($query);
            $wpjobportal_i = 0;
            foreach ($wpjobportal_subcats as $wpjobportal_id => $scat) {
                $wpjobportal_total += $scat->totaljobs;
                if($wpjobportal_subcategory_limit <= $wpjobportal_i){
                    unset($wpjobportal_subcats[$wpjobportal_id]);
                }
                $wpjobportal_i++;
            }
            $wpjobportal_category->subcat = $wpjobportal_subcats;
            $wpjobportal_category->total_sub_jobs = $wpjobportal_total;
        }


        if(wpjobportal::$_configuration['job_resume_show_all_categories'] == 0){//conifguration based
            $final_arr = array();
            foreach ($wpjobportal_categories as $wpjobportal_job_category) {
                if($wpjobportal_job_category->totaljobs != 0 || $wpjobportal_job_category->total_sub_jobs != 0){
                    $final_arr[] = $wpjobportal_job_category;
                }
            }
            $wpjobportal_categories = $final_arr;
        }
        wpjobportal::$_data[0] = $wpjobportal_categories;
        wpjobportal::$_data['config'] =  wpjobportal::$_config->getConfigByFor('category');
        return;
    }

    //function for resume files in jobapply email
    function getResumeFilesByResumeId($wpjobportal_resumeid) { // by resumeid because files are stored in seperate table
        if (!is_numeric($wpjobportal_resumeid)) return false;
        $query = "SELECT COUNT(id) FROM `".wpjobportal::$_db->prefix."wj_portal_resumefiles` WHERE resumeid=" . esc_sql($wpjobportal_resumeid);

        $filesFound = wpjobportaldb::get_var($query);
        if ($filesFound > 0) {
           $query = "SELECT * FROM `".wpjobportal::$_db->prefix."wj_portal_resumefiles` WHERE resumeid = " . esc_sql($wpjobportal_resumeid);

           $files = wpjobportaldb::get_results($query);
           return $files;
        } else {
           return false;
        }
    }

    function getResumeExpiryStatus($wpjobportal_id) {
        if (!is_numeric($wpjobportal_id))
            return false;
        $query = "SELECT resume.id
        FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume
        WHERE resume.status = 1 AND resume.id =" . esc_sql($wpjobportal_id);
        $wpjobportal_result = wpjobportal::$_db->get_var($query);
        if ($wpjobportal_result == null) {
            return false;
        } else {
            return true;
        }
    }

    ///***To Add Only one Resume***///
    function checkAlreadyadd($wpjobportal_uid='',$wpjobportal_resumeid=''){
        if(wpjobportal::$_common->wpjp_isadmin()){
            return true;
        }else{
            if(!is_numeric($wpjobportal_uid))
            return false;
        if(in_array('visitorapplyjob', wpjobportal::$_active_addons) || in_array('multiresume', wpjobportal::$_active_addons)){
            return true;
        }
        $query = "SELECT count(resume.id) as resume
        FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume
        WHERE resume.status = 1 AND resume.uid =" . esc_sql($wpjobportal_uid) ." AND resume.status!=-1 AND resume.id!='".esc_sql($wpjobportal_resumeid)."'";
        $wpjobportal_result = wpjobportal::$_db->get_var($query);
        $wpjobportal_count = (int)$wpjobportal_result;
        if($wpjobportal_count > 0){
            return false;
         }else{
            return true;
            }
        }
    }

    public function checkAlreadyViewResumeContactDetail($wpjobportal_resumeid,$wpjobportal_data='') {
        if (!is_numeric($wpjobportal_resumeid))
            return false;
        if (WPJOBPORTALincluder::getObjectClass('user')->isguest() || !WPJOBPORTALincluder::getObjectClass('user')->isWPJOBPORTALUser()) {
            return false;
        }
        if(WPJOBPORTALincluder::getObjectClass('user')->isemployer()){
            $wpjobportal_employerid = WPJOBPORTALincluder::getObjectClass('user')->uid();
            $query = "SELECT count(job.id)
                        FROM `".wpjobportal::$_db->prefix."wj_portal_jobs` AS job
                        JOIN `".wpjobportal::$_db->prefix."wj_portal_jobapply` AS ja ON ja.jobid = job.id
                        WHERE job.uid = ".esc_sql($wpjobportal_employerid)." AND ja.cvid = ".esc_sql($wpjobportal_resumeid);
            $wpjobportal_result = wpjobportal::$_db->get_var($query);
            if($wpjobportal_result > 0){
                return true;
            }
        }

        if(current_user_can('manage_options') && !isset($wpjobportal_data['uid']) ){
            return true;
        }
        if (isset($_SESSION['wp-wpjobportal']) && isset($_SESSION['wp-wpjobportal']['resumeid'])) {
            if($_SESSION['wp-wpjobportal']['resumeid'] == $wpjobportal_resumeid)
                return true;
        }
        if(isset($wpjobportal_data['uid'])!=''){
            $wpjobportal_uid = $wpjobportal_data['uid'];
        }else{
            $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        }
        if(!is_numeric($wpjobportal_uid)){
            return false;
        }
        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_employer_view_resume` WHERE resumeid = ".esc_sql($wpjobportal_resumeid)." AND uid =". esc_sql($wpjobportal_uid);
        $wpjobportal_result = wpjobportal::$_db->get_var($query);
        if ($wpjobportal_result > 0)
            return true;
        else
            return false;
    }

    function getIfResumeOwner($wpjobportal_resumeid) {
        if (!is_numeric($wpjobportal_resumeid))
            return false;
        $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        //extra code
        // if(in_array('multiresume', wpjobportal::$_active_addons)){
        //     $wpjobportal_resumeid = $wpjobportal_jobid;
        // }else{
        //     $query = "SELECT resume.id
        //             FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume
        //             WHERE resume.uid = " . esc_sql($wpjobportal_uid)." ORDER by resume.id DESC LIMIT 0,1";
        //     $wpjobportal_result = wpjobportal::$_db->get_var($query);
        //     $wpjobportal_resumeid = $wpjobportal_jobid;
        // }
        // to handle visitor quick apply case
        if(!is_numeric($wpjobportal_uid)){
            $wpjobportal_uid = 0;
        }
        $query = "SELECT resume.id
        FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume
        WHERE resume.uid = ". esc_sql($wpjobportal_uid)."
        AND resume.id =" . esc_sql($wpjobportal_resumeid);
        $wpjobportal_result = wpjobportal::$_db->get_var($query);
        if ($wpjobportal_result == null) {
            return false;
        } else {
            return true;
        }
    }

    function getPackagePopupForResumeContactDetail(){

            $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
            if (! wp_verify_nonce( $wpjobportal_nonce, 'get-package-popup-for-resume-contact-detail') ) {
                die( 'Security check Failed' );
            }
            $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
            $wpjobportal_resumeid = WPJOBPORTALrequest::getVar('wpjobportalid');
            $wpjobportal_subtype = wpjobportal::$_config->getConfigValue('submission_type');
            #submit type popup for Featured Resume --Listing(Membership)
            if( $wpjobportal_subtype != 3 ){
                return false;
            }
            $wpjobportal_userpackages = array();
            $wpjobportal_pack = apply_filters('wpjobportal_addons_credit_get_Packages_user',false,$wpjobportal_uid,'resumecontactdetail');
            $wpjobportal_addonclass = '';
            if(WPJOBPORTALincluder::getJSModel('common')->isElegantDesignEnabled()){
                $wpjobportal_addonclass = ' wjportal-elegant-addon-packages-popup ';
            }
            foreach($wpjobportal_pack as $wpjobportal_package){
                if($wpjobportal_package->resumecontactdetail == -1 || $wpjobportal_package->remresumecontactdetail > 0){ //-1 = unlimited
                    $wpjobportal_userpackages[] = $wpjobportal_package;
                }
            }
            if (wpjobportal::$wpjobportal_theme_chk == 1) {
                $wpjobportal_content = '
                <div id="wjportal-popup-background" style="display: none;"></div>
                <div id="package-popup" class="wjportal-popup-wrp wjportal-packages-popup">
                    <div class="wjportal-popup-cnt">
                        <img id="wjportal-popup-close-btn" alt="popup cross" src="'.esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/popup-close.png">
                        <div class="wjportal-popup-title">
                            '.esc_html(__("Select Package",'wp-job-portal')).'
                            <div class="wjportal-popup-title3">
                                '.esc_html(__("Please select a package first",'wp-job-portal')).'
                            </div>
                        </div>
                        <div class="wjportal-popup-contentarea">
                            <div class="wjportal-packages-wrp">';
                                if(count($wpjobportal_userpackages) == 0 || empty($wpjobportal_userpackages)){
                                    $wpjobportal_content .= WPJOBPORTALmessages::showMessage(esc_html(__("You do not have any View Resume Contact remaining",'wp-job-portal')),'error',1);
                                } else {
                                    foreach($wpjobportal_userpackages as $wpjobportal_package){
                                        #User Package For Selection in Popup Model --Views
                                        $wpjobportal_content .= '
                                            <div class="wjportal-pkg-item" id="package-div-'.esc_attr($wpjobportal_package->id).'" onclick="selectPackage('.esc_attr($wpjobportal_package->id).');">
                                                <div class="wjportal-pkg-item-top">
                                                    <div class="wjportal-pkg-item-title">
                                                        '.esc_html($wpjobportal_package->title).'
                                                    </div>
                                                </div>
                                                <div class="wjportal-pkg-item-btm">
                                                    <div class="wjportal-pkg-item-row">
                                                        <span class="wjportal-pkg-item-tit">
                                                            '.esc_html(__("View Contact Resume",'wp-job-portal')).' :
                                                        </span>
                                                        <span class="wjportal-pkg-item-val">
                                                            '.($wpjobportal_package->resumecontactdetail==-1 ? esc_html(__("Unlimited",'wp-job-portal')) : esc_attr($wpjobportal_package->resumecontactdetail)).'
                                                        </span>
                                                    </div>
                                                    <div class="wjportal-pkg-item-row">
                                                        <span class="wjportal-pkg-item-tit">
                                                            '.esc_html(__("Remaining",'wp-job-portal')).' :
                                                        </span>
                                                        <span class="wjportal-pkg-item-val">
                                                            '.($wpjobportal_package->resumecontactdetail==-1 ? esc_html(__("Unlimited",'wp-job-portal')) : esc_attr($wpjobportal_package->remresumecontactdetail)).'
                                                        </span>
                                                    </div>
                                                    <div class="wjportal-pkg-item-btn-row">
                                                    <a href="#" class="wjportal-pkg-item-btn">
                                                        '.esc_html(__("Select Package",'wp-job-portal')).'
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    ';
                                }
                                /*$wpjobportal_content .= '<div class="wjportal-pkg-help-txt">
                                                '.esc_html(__("Click on package to select one",'wp-job-portal')).'
                                            </div>';*/
                            }
                            $wpjobportal_content .= '</div>
                            <div class="wjportal-popup-msgs" id="wjportal-package-message"> </div>
                        </div>
                        <div class="wjportal-visitor-msg-btn-wrp">
                            <form action="'.esc_url(wp_nonce_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume','action'=>'wpjobportaltask','task'=>'addviewresumedetail','wpjobportalid'=>$wpjobportal_resumeid,'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid())),'wpjobportal_resume_nonce')).'" method="post">
                                <input type="hidden" id="wpjobportal_packageid" name="wpjobportal_packageid">
                                <input type="submit" rel="button" id="jsre_featured_button" class="wjportal-visitor-msg-btn" value="'.esc_html(__('Show Resume Contact','wp-job-portal')).'" disabled/>
                            </form>
                        </div>
                    </div>
                </div>';
            } else {
           $wpjobportal_content = '
            <div id="wjportal-popup-background" style="display: none;"></div>
            <div id="package-popup" class="wjportal-popup-wrp wjportal-packages-popup '.$wpjobportal_addonclass.'">
                <div class="wjportal-popup-cnt">
                    <img id="wjportal-popup-close-btn" alt="popup cross" src="'.esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/popup-close.png">
                    <div class="wjportal-popup-title">
                        '.esc_html(__("Select Package",'wp-job-portal')).'
                        <div class="wjportal-popup-title3">
                            '.esc_html(__("Please select a package first",'wp-job-portal')).'
                        </div>
                    </div>
                    <div class="wjportal-popup-contentarea">
                        <div class="wjportal-packages-wrp">';
                            if(count($wpjobportal_userpackages) == 0 || empty($wpjobportal_userpackages)){
                                $wpjobportal_content .= WPJOBPORTALmessages::showMessage(esc_html(__("You do not have any View Resume Contact remaining",'wp-job-portal')),'error',1);
                            } else {
                                foreach($wpjobportal_userpackages as $wpjobportal_package){
                                    #User Package For Selection in Popup Model --Views
                                    $wpjobportal_content .= '
                                        <div class="wjportal-pkg-item" id="package-div-'.esc_attr($wpjobportal_package->id).'" onclick="selectPackage('.esc_attr($wpjobportal_package->id).');">
                                            <div class="wjportal-pkg-item-top">
                                                <div class="wjportal-pkg-item-title">
                                                    '.esc_html($wpjobportal_package->title).'
                                                </div>
                                            </div>
                                            <div class="wjportal-pkg-item-btm">
                                                <div class="wjportal-pkg-item-row">
                                                    <span class="wjportal-pkg-item-tit">
                                                        '.esc_html(__("View Contact Resume",'wp-job-portal')).' :
                                                    </span>
                                                    <span class="wjportal-pkg-item-val">
                                                        '.($wpjobportal_package->resumecontactdetail==-1 ? esc_html(__("Unlimited",'wp-job-portal')) : esc_attr($wpjobportal_package->resumecontactdetail)).'
                                                    </span>
                                                </div>
                                                <div class="wjportal-pkg-item-row">
                                                    <span class="wjportal-pkg-item-tit">
                                                        '.esc_html(__("Remaining",'wp-job-portal')).' :
                                                    </span>
                                                    <span class="wjportal-pkg-item-val">
                                                        '.($wpjobportal_package->resumecontactdetail==-1 ? esc_html(__("Unlimited",'wp-job-portal')) : esc_attr($wpjobportal_package->remresumecontactdetail)).'
                                                    </span>
                                                </div>
                                                <div class="wjportal-pkg-item-btn-row">
                                                    <a href="#" class="wjportal-pkg-item-btn">
                                                        '.esc_html(__("Select Package",'wp-job-portal')).'
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    ';
                                }
                                /*$wpjobportal_content .= '<div class="wjportal-pkg-help-txt">
                                                '.esc_html(__("Click on package to select one",'wp-job-portal')).'
                                            </div>';*/
                            }
                        $wpjobportal_content .= '</div>
                        <div class="wjportal-popup-msgs" id="wjportal-package-message">&nbsp;</div>
                    </div>
                    <div class="wjportal-visitor-msg-btn-wrp">
                        <form action="'.esc_url(wp_nonce_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume','action'=>'wpjobportaltask','task'=>'addviewresumedetail','wpjobportalid'=>$wpjobportal_resumeid,'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid())),'wpjobportal_resume_nonce')).'" method="post">
                            <input type="hidden" id="wpjobportal_packageid" name="wpjobportal_packageid">
                            <input type="submit" rel="button" id="jsre_featured_button" class="wjportal-visitor-msg-btn" value="'.esc_html(__('Show Resume Contact','wp-job-portal')).'" disabled/>
                        </form>
                    </div>
                </div>
            </div>';
            }

            echo wp_kses($wpjobportal_content, WPJOBPORTAL_ALLOWED_TAGS);
            exit();
    }

    function UserCanAddResume($wpjobportal_uid){
        if(WPJOBPORTALincluder::getObjectClass('user')->isguest()){
            return true;
        }
        # Check Whether Not More than one
        if(!is_numeric($wpjobportal_uid)){
            return false;
        }
        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` WHERE uid =". esc_sql($wpjobportal_uid);
        $wpjobportal_resume = wpjobportaldb::get_var($query);
        if($wpjobportal_resume > 0){
            return false;
        }
        return true;
    }

    function getMyResumeSearchFormData($wpjobportal_layout){
        $wpjobportal_jsjp_search_array = array();
        if($wpjobportal_layout == 'myresume'){
            $wpjobportal_jsjp_search_array['sorton'] = WPJOBPORTALrequest::getVar('sorton', 'post', 6);
            $wpjobportal_jsjp_search_array['sortby'] = WPJOBPORTALrequest::getVar('sortby', 'post', 2);
        }elseif($wpjobportal_layout == 'resumes'){
            $wpjobportal_customfields = wpjobportal::$_wpjpcustomfield->userFieldsData(3);
            $wpjobportal_jsjp_search_array['sorton'] = WPJOBPORTALrequest::getVar('sorton', 'post', 6);
            $wpjobportal_jsjp_search_array['sortby'] = WPJOBPORTALrequest::getVar('sortby', 'post', 2);
            $wpjobportal_jsjp_search_array['application_title'] = WPJOBPORTALrequest::getVar('application_title');
            $wpjobportal_jsjp_search_array['first_name'] = WPJOBPORTALrequest::getVar('first_name');
            $wpjobportal_jsjp_search_array['middle_name'] = WPJOBPORTALrequest::getVar('middle_name');
            $wpjobportal_jsjp_search_array['last_name'] = WPJOBPORTALrequest::getVar('last_name');
            $wpjobportal_jsjp_search_array['nationality'] = WPJOBPORTALrequest::getVar('nationality');
            $wpjobportal_jsjp_search_array['gender'] = WPJOBPORTALrequest::getVar('gender');
            $wpjobportal_jsjp_search_array['salaryfixed'] = WPJOBPORTALrequest::getVar('salaryfixed');
            $wpjobportal_jsjp_search_array['jobtype'] = WPJOBPORTALrequest::getVar('jobtype');
            $wpjobportal_jsjp_search_array['salaryrangetype'] = WPJOBPORTALrequest::getVar('salaryrangetype');
            $wpjobportal_jsjp_search_array['zipcode'] = WPJOBPORTALrequest::getVar('zipcode');
            $wpjobportal_jsjp_search_array['keywords'] = WPJOBPORTALrequest::getVar('keywords');
            $wpjobportal_jsjp_search_array['city'] = WPJOBPORTALrequest::getVar('city');
            $wpjobportal_jsjp_search_array['airesumesearcch'] = WPJOBPORTALrequest::getVar('airesumesearcch');
            // if(WPJOBPORTALrequest::getVar('resume_filter')){
                $wpjobportal_resume_filter = wpjobportalphplib::wpJP_safe_decoding(WPJOBPORTALrequest::getVar('resume_filter'));
                if($wpjobportal_resume_filter !=''){
                    $wpjobportal_resume_filter = json_decode($wpjobportal_resume_filter, true );
                }
                if(isset($wpjobportal_resume_filter['category'])){
                    $wpjobportal_jsjp_search_array['category'] = $wpjobportal_resume_filter['category'];
                }else{
                    $wpjobportal_jsjp_search_array['category'] = WPJOBPORTALrequest::getVar('category');
                }
            // }
            if (!empty($wpjobportal_customfields)) {
                foreach ($wpjobportal_customfields as $uf) {
                    $wpjobportal_jsjp_search_array['resume_custom_fields'][$uf->field] = WPJOBPORTALrequest::getVar($uf->field, 'post');
                }
            }
        }
        $wpjobportal_jsjp_search_array['search_from_resumes'] = 1;
        return $wpjobportal_jsjp_search_array;
    }

    function getAdminResumeSearchFormData(){
        $wpjobportal_jsjp_search_array = array();
        $wpjobportal_jsjp_search_array['searchtitle'] = WPJOBPORTALrequest::getVar('searchtitle');
        $wpjobportal_jsjp_search_array['searchname'] = WPJOBPORTALrequest::getVar('searchname');
        $wpjobportal_jsjp_search_array['searchjobcategory'] = WPJOBPORTALrequest::getVar('searchjobcategory');
        $wpjobportal_jsjp_search_array['searchjobtype'] = WPJOBPORTALrequest::getVar('searchjobtype');
        $wpjobportal_jsjp_search_array['searchjobsalaryrange'] = WPJOBPORTALrequest::getVar('searchjobsalaryrange');
        $wpjobportal_jsjp_search_array['status'] = WPJOBPORTALrequest::getVar('status');
        $wpjobportal_jsjp_search_array['datestart'] = WPJOBPORTALrequest::getVar('datestart');
        $wpjobportal_jsjp_search_array['dateend'] = WPJOBPORTALrequest::getVar('dateend');
        $wpjobportal_jsjp_search_array['featured'] = WPJOBPORTALrequest::getVar('featured');
        $wpjobportal_jsjp_search_array['sorton'] = WPJOBPORTALrequest::getVar('sorton', 'post', 6);
        $wpjobportal_jsjp_search_array['sortby'] = WPJOBPORTALrequest::getVar('sortby', 'post', 2);
        $wpjobportal_jsjp_search_array['search_from_resumes'] = 1;
        return $wpjobportal_jsjp_search_array;
    }

    function getResumeSavedCookiesData($wpjobportal_layout){
        $wpjobportal_jsjp_search_array = array();
        $wpjp_search_cookie_data = '';
        if(isset($_COOKIE['jsjp_jobportal_search_data'])){
            $wpjp_search_cookie_data = wpjobportal::wpjobportal_sanitizeData($_COOKIE['jsjp_jobportal_search_data']);
            $wpjp_search_cookie_data = wpjobportalphplib::wpJP_safe_decoding($wpjp_search_cookie_data);
            $wpjp_search_cookie_data = json_decode( $wpjp_search_cookie_data , true );
        }
        if($wpjp_search_cookie_data != '' && isset($wpjp_search_cookie_data['search_from_resumes']) && $wpjp_search_cookie_data['search_from_resumes'] == 1){
            if(wpjobportal::$_common->wpjp_isadmin()){
                $wpjobportal_jsjp_search_array['searchtitle'] = $wpjp_search_cookie_data['searchtitle'];
                $wpjobportal_jsjp_search_array['searchname'] = $wpjp_search_cookie_data['searchname'];
                $wpjobportal_jsjp_search_array['searchjobcategory'] = $wpjp_search_cookie_data['searchjobcategory'];
                $wpjobportal_jsjp_search_array['searchjobtype'] = $wpjp_search_cookie_data['searchjobtype'];
                $wpjobportal_jsjp_search_array['searchjobsalaryrange'] = $wpjp_search_cookie_data['searchjobsalaryrange'];
                $wpjobportal_jsjp_search_array['status'] = $wpjp_search_cookie_data['status'];
                $wpjobportal_jsjp_search_array['datestart'] = $wpjp_search_cookie_data['datestart'];
                $wpjobportal_jsjp_search_array['dateend'] = $wpjp_search_cookie_data['dateend'];
                $wpjobportal_jsjp_search_array['featured'] = $wpjp_search_cookie_data['featured'];
                $wpjobportal_jsjp_search_array['sorton'] = $wpjp_search_cookie_data['sorton'];
                $wpjobportal_jsjp_search_array['sortby'] = $wpjp_search_cookie_data['sortby'];
            }else{
                if($wpjobportal_layout == 'myresume'){
                    $wpjobportal_jsjp_search_array['sorton'] = $wpjp_search_cookie_data['sorton'];
                    $wpjobportal_jsjp_search_array['sortby'] = $wpjp_search_cookie_data['sortby'];
                }elseif($wpjobportal_layout == 'resumes'){
                    $wpjobportal_customfields = wpjobportal::$_wpjpcustomfield->userFieldsData(3);
                    $wpjobportal_jsjp_search_array['sorton'] = $wpjp_search_cookie_data['sorton'];
                    $wpjobportal_jsjp_search_array['sortby'] = $wpjp_search_cookie_data['sortby'];
                    $wpjobportal_jsjp_search_array['application_title'] = $wpjp_search_cookie_data['application_title'];
                    $wpjobportal_jsjp_search_array['first_name'] = $wpjp_search_cookie_data['first_name'];
                    $wpjobportal_jsjp_search_array['middle_name'] = $wpjp_search_cookie_data['middle_name'];
                    $wpjobportal_jsjp_search_array['last_name'] = $wpjp_search_cookie_data['last_name'];
                    $wpjobportal_jsjp_search_array['nationality'] = $wpjp_search_cookie_data['nationality'];
                    $wpjobportal_jsjp_search_array['gender'] = $wpjp_search_cookie_data['gender'];
                    $wpjobportal_jsjp_search_array['salaryfixed'] = $wpjp_search_cookie_data['salaryfixed'];
                    $wpjobportal_jsjp_search_array['jobtype'] = $wpjp_search_cookie_data['jobtype'];
                    $wpjobportal_jsjp_search_array['salaryrangetype'] = $wpjp_search_cookie_data['salaryrangetype'];
                    $wpjobportal_jsjp_search_array['category'] = $wpjp_search_cookie_data['category'];
                    $wpjobportal_jsjp_search_array['zipcode'] = $wpjp_search_cookie_data['zipcode'];
                    $wpjobportal_jsjp_search_array['keywords'] = $wpjp_search_cookie_data['keywords'];
                    $wpjobportal_jsjp_search_array['city'] = $wpjp_search_cookie_data['city'];
                    $wpjobportal_jsjp_search_array['airesumesearcch'] = $wpjp_search_cookie_data['airesumesearcch'];
                    if (!empty($wpjobportal_customfields)) {
                        foreach ($wpjobportal_customfields as $uf) {
                            $wpjobportal_jsjp_search_array['resume_custom_fields'][$uf->field] = $wpjp_search_cookie_data['resume_custom_fields'][$uf->field];
                        }
                    }
                }
            }
        }
        return $wpjobportal_jsjp_search_array;
    }

    function setSearchVariableForMyResume($wpjobportal_jsjp_search_array,$wpjobportal_layout){
        wpjobportal::$_search['myresume']['sorton'] = isset($wpjobportal_jsjp_search_array['sorton']) ? $wpjobportal_jsjp_search_array['sorton'] : null;
        wpjobportal::$_search['myresume']['sortby'] = isset($wpjobportal_jsjp_search_array['sortby']) ? $wpjobportal_jsjp_search_array['sortby'] : null;
        if($wpjobportal_layout == 'resumes'){
            $wpjobportal_customfields = wpjobportal::$_wpjpcustomfield->userFieldsData(3);
            wpjobportal::$_search['resumes']['sorton'] = isset($wpjobportal_jsjp_search_array['sorton']) ? $wpjobportal_jsjp_search_array['sorton'] : 6;
            wpjobportal::$_search['resumes']['sortby'] = isset($wpjobportal_jsjp_search_array['sortby']) ? $wpjobportal_jsjp_search_array['sortby'] : 2;
            wpjobportal::$_search['resumes']['application_title'] = isset($wpjobportal_jsjp_search_array['application_title']) ? $wpjobportal_jsjp_search_array['application_title'] : null;
            wpjobportal::$_search['resumes']['first_name'] = isset($wpjobportal_jsjp_search_array['first_name']) ? $wpjobportal_jsjp_search_array['first_name'] : null;
            wpjobportal::$_search['resumes']['middle_name'] = isset($wpjobportal_jsjp_search_array['middle_name']) ? $wpjobportal_jsjp_search_array['middle_name'] : null;
            wpjobportal::$_search['resumes']['last_name'] = isset($wpjobportal_jsjp_search_array['last_name']) ? $wpjobportal_jsjp_search_array['last_name'] : null;
            wpjobportal::$_search['resumes']['nationality'] = isset($wpjobportal_jsjp_search_array['nationality']) ? $wpjobportal_jsjp_search_array['nationality'] : null;
            wpjobportal::$_search['resumes']['gender'] = isset($wpjobportal_jsjp_search_array['gender']) ? $wpjobportal_jsjp_search_array['gender'] : null;
            wpjobportal::$_search['resumes']['salaryfixed'] = isset($wpjobportal_jsjp_search_array['salaryfixed']) ? $wpjobportal_jsjp_search_array['salaryfixed'] : null;
            wpjobportal::$_search['resumes']['jobtype'] = isset($wpjobportal_jsjp_search_array['jobtype']) ? $wpjobportal_jsjp_search_array['jobtype'] : null;
            wpjobportal::$_search['resumes']['salaryrangetype'] = isset($wpjobportal_jsjp_search_array['salaryrangetype']) ? $wpjobportal_jsjp_search_array['salaryrangetype'] : null;
            wpjobportal::$_search['resumes']['category'] = isset($wpjobportal_jsjp_search_array['category']) ? $wpjobportal_jsjp_search_array['category'] : null;
            wpjobportal::$_search['resumes']['zipcode'] = isset($wpjobportal_jsjp_search_array['zipcode']) ? $wpjobportal_jsjp_search_array['zipcode'] : null;
            wpjobportal::$_search['resumes']['keywords'] = isset($wpjobportal_jsjp_search_array['keywords']) ? $wpjobportal_jsjp_search_array['keywords'] : null;
            wpjobportal::$_search['resumes']['city'] = isset($wpjobportal_jsjp_search_array['city']) ? $wpjobportal_jsjp_search_array['city'] : null;
            wpjobportal::$_search['resumes']['airesumesearcch'] = isset($wpjobportal_jsjp_search_array['airesumesearcch']) ? $wpjobportal_jsjp_search_array['airesumesearcch'] : null;
            if (!empty($wpjobportal_customfields)) {
                foreach ($wpjobportal_customfields as $uf) {
                    wpjobportal::$_search['resume_custom_fields'][$uf->field] = isset($wpjobportal_jsjp_search_array['resume_custom_fields'][$uf->field]) ? $wpjobportal_jsjp_search_array['resume_custom_fields'][$uf->field] : '';
                }
            }
        }
    }

    function setSearchVariableForAdminResume($wpjobportal_jsjp_search_array){
        wpjobportal::$_search['resumes']['searchtitle']  = isset($wpjobportal_jsjp_search_array['searchtitle']) ? $wpjobportal_jsjp_search_array['searchtitle'] : null;
        wpjobportal::$_search['resumes']['searchname'] = isset($wpjobportal_jsjp_search_array['searchname']) ? $wpjobportal_jsjp_search_array['searchname'] : null;
        wpjobportal::$_search['resumes']['searchjobcategory'] = isset($wpjobportal_jsjp_search_array['searchjobcategory']) ? $wpjobportal_jsjp_search_array['searchjobcategory'] : null;
        wpjobportal::$_search['resumes']['searchjobtype'] = isset($wpjobportal_jsjp_search_array['searchjobtype']) ? $wpjobportal_jsjp_search_array['searchjobtype'] : null;
        wpjobportal::$_search['resumes']['searchjobsalaryrange'] = isset($wpjobportal_jsjp_search_array['searchjobsalaryrange']) ? $wpjobportal_jsjp_search_array['searchjobsalaryrange'] : null;
        wpjobportal::$_search['resumes']['status'] = isset($wpjobportal_jsjp_search_array['status']) ? $wpjobportal_jsjp_search_array['status'] : null;
        wpjobportal::$_search['resumes']['datestart'] = isset($wpjobportal_jsjp_search_array['datestart']) ? $wpjobportal_jsjp_search_array['datestart'] : null;
        wpjobportal::$_search['resumes']['dateend'] = isset($wpjobportal_jsjp_search_array['dateend']) ? $wpjobportal_jsjp_search_array['dateend'] : null;
        wpjobportal::$_search['resumes']['featured'] = isset($wpjobportal_jsjp_search_array['featured']) ? $wpjobportal_jsjp_search_array['featured'] : null;
        wpjobportal::$_search['resumes']['sorton'] = isset($wpjobportal_jsjp_search_array['sorton']) ? $wpjobportal_jsjp_search_array['sorton'] : 6;
        wpjobportal::$_search['resumes']['sortby'] = isset($wpjobportal_jsjp_search_array['sortby']) ? $wpjobportal_jsjp_search_array['sortby'] : 2;
    }

    function deleteResumeLogo($wpjobportal_resumeid = 0){
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'delete-resume-logo') ) {
            die( 'Security check Failed' );
        }
        if($wpjobportal_resumeid == 0){
            $wpjobportal_resumeid = WPJOBPORTALrequest::getVar('resumeid');
        }
        if(!is_numeric($wpjobportal_resumeid)){
            return false;
        }
		if(!wpjobportal::$_common->wpjp_isadmin()){
            if(!$this->getIfResumeOwner($wpjobportal_resumeid)){
                return false;
            }
        }
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('resume');
        $wpjobportal_data_directory = wpjobportal::$_config->getConfigValue('data_directory');
        $wpjobportal_wpdir = wp_upload_dir();
        $wpjobportal_path = $wpjobportal_wpdir['basedir'] . '/' . $wpjobportal_data_directory . '/data/jobseeker/resume_' . $wpjobportal_resumeid . '/photo';
        $files = glob($wpjobportal_path . '/*.*');
        array_map('wp_delete_file', $files);    // delete all file in the direcoty
        $query = "UPDATE `".wpjobportal::$_db->prefix."wj_portal_resume` SET photo = '' WHERE id = ".esc_sql($wpjobportal_resumeid);
        wpjobportal::$_db->query($query);
        return true;
    }

    function deleteResumeLogoModel($wpjobportal_resumeid = 0){

        if($wpjobportal_resumeid == 0){
            $wpjobportal_resumeid = WPJOBPORTALrequest::getVar('resumeid');
        }
        if(!is_numeric($wpjobportal_resumeid)){
            return false;
        }
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('resume');
        $wpjobportal_data_directory = wpjobportal::$_config->getConfigValue('data_directory');
        $wpjobportal_wpdir = wp_upload_dir();
        $wpjobportal_path = $wpjobportal_wpdir['basedir'] . '/' . $wpjobportal_data_directory . '/data/jobseeker/resume_' . $wpjobportal_resumeid . '/photo';
        $files = glob($wpjobportal_path . '/*.*');
        array_map('wp_delete_file', $files);    // delete all file in the direcoty
        $query = "UPDATE `".wpjobportal::$_db->prefix."wj_portal_resume` SET photo = '' WHERE id = ".esc_sql($wpjobportal_resumeid);
        wpjobportal::$_db->query($query);
        return true;
    }

    function getMessagekey(){
        $wpjobportal_key = 'resume';if(wpjobportal::$_common->wpjp_isadmin()){$wpjobportal_key = 'admin_'.$wpjobportal_key;}return $wpjobportal_key;
    }

    function getResumesForPageBuilderWidget($wpjobportal_no_of_resumes){

        $query = "SELECT resume.id,CONCAT(resume.alias,'-',resume.id) AS resumealiasid ,resume.first_name
            ,resume.last_name,resume.application_title as applicationtitle,resume.email_address,category.cat_title
            ,resume.created,jobtype.title AS jobtypetitle,resume.photo,
            resume.isfeaturedresume,resume.endfeatureddate
            ,resume.status,city.name AS cityname
            ,state.name AS statename,resume.params,resume.salaryfixed as salary
            ,resume.last_modified,LOWER(jobtype.title) AS jobtypetit,jobtype.color as jobtypecolor,country.name AS countryname,resume.id as resumeid
            FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume
            LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS category ON category.id = resume.job_category ";
        $query .= " JOIN `" . wpjobportal::$_db->prefix . "wj_portal_resumeaddresses` AS address1 ON address1.resumeid = resume.id ";
        $query .= "
            LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON jobtype.id = resume.jobtype
            LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON city.id = (SELECT address_city FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumeaddresses` WHERE resumeid = resume.id LIMIT 1)
            LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_states` AS state ON state.id = city.stateid
            LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_countries` AS country ON country.id = city.countryid
            WHERE resume.status = 1  ";
        $query.= " ORDER BY created DESC ";
        //$query.=" LIMIT 4 " ;
        if(is_numeric($wpjobportal_no_of_resumes)){
            $query.=" LIMIT " . esc_sql($wpjobportal_no_of_resumes);
        }

        $wpjobportal_results = wpjobportaldb::get_results($query);

        $wpjobportal_data = array();
        foreach ($wpjobportal_results AS $d) {
            //  updated the query select to select 'name' as cityname
            $d->location = WPJOBPORTALincluder::getJSModel('common')->getLocationForView($d->cityname, $d->statename, $d->countryname);
            $wpjobportal_data[] = $d;
        }
        return $wpjobportal_data;
    }

    ///***Check if Quick Apply resume***///
    function checkQuickApply($wpjobportal_resumeid){
        if(!is_numeric($wpjobportal_resumeid))
            return false;

        $query = "SELECT count(resume.id) as resume_count
        FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume
        WHERE resume.quick_apply = 1 AND resume.id = ".esc_sql($wpjobportal_resumeid);
        $wpjobportal_result = wpjobportal::$_db->get_var($query);
        $wpjobportal_count = (int)$wpjobportal_result;
        if($wpjobportal_count > 0){
            return true;
         }else{
            return false;
        }
    }

    function processShortcodeAttributesResume(){
        $wpjobportal_inquery = '';

        // cities
        $cities_list = WPJOBPORTALrequest::getVar('locations', 'shortcode_option', false);
        if ($cities_list && $cities_list !='' ) { // not empty check
            $city_array = wpjobportalphplib::wpJP_explode( ',' , $cities_list); // handle multi case
            $cityQuery = false;
            foreach($city_array as $city_id){ // loop over all ids
                if($city_id != ''){ // null check
                    $city_id = trim($city_id);
                }
                if(!is_numeric($city_id)){ // numric check
                    continue;
                }
                if($cityQuery){
                    $cityQuery .= " OR address1.address_city =  ".esc_sql($city_id);
                }else{
                    $cityQuery = " address1.address_city =  ".esc_sql($city_id);
                }
            }
            if($cityQuery){
                $wpjobportal_inquery .= " AND ( $cityQuery ) ";
            }
        }

        // tags
        $wpjobportal_tags_list = WPJOBPORTALrequest::getVar('tags', 'shortcode_option', false);
        if ($wpjobportal_tags_list && $wpjobportal_tags_list !='' ) { // not empty check
            $wpjobportal_tag_array = wpjobportalphplib::wpJP_explode( ',' , $wpjobportal_tags_list); // handle multi case
            $wpjobportal_tagQuery = false;
            foreach($wpjobportal_tag_array as $wpjobportal_tag_id){ // loop over all ids
                if($wpjobportal_tag_id != ''){ // null check
                    $wpjobportal_tag_id = trim($wpjobportal_tag_id);
                }
                if(!is_numeric($wpjobportal_tag_id)){ // numric check
                    continue;
                }
                if($wpjobportal_tagQuery){
                    $wpjobportal_tagQuery .= " OR FIND_IN_SET('" . esc_sql($wpjobportal_tag_id) . "', resume.tags) > 0 ";
                }else{
                    $wpjobportal_tagQuery = " FIND_IN_SET('" . esc_sql($wpjobportal_tag_id) . "', resume.tags) > 0 ";
                }
            }
            if($wpjobportal_tagQuery){
                $wpjobportal_inquery .= " AND ( $wpjobportal_tagQuery ) ";
            }
        }

        // categories
        $wpjobportal_category_list = WPJOBPORTALrequest::getVar('categories', 'shortcode_option', false);
        if ($wpjobportal_category_list && $wpjobportal_category_list !='' ) { // not empty check
            $wpjobportal_category_array = wpjobportalphplib::wpJP_explode( ',' , $wpjobportal_category_list); // handle multi case
            $wpjobportal_categoryQuery = false;
            $wpjp_ids = array();
            foreach($wpjobportal_category_array as $wpjobportal_category_id){  // loop over all ids
                if($wpjobportal_category_id != ''){ // null check
                    $wpjobportal_category_id = trim($wpjobportal_category_id);
                }
                if(!is_numeric($wpjobportal_category_id)){ // numric check
                    continue;
                }
                // handle case of child categories of current category
                $wpjp_query = "SELECT id FROM `" . wpjobportal::$_db->prefix . "wj_portal_categories` WHERE parentid = ". esc_sql($wpjobportal_category_id);
                $wpjp_cats = wpjobportaldb::get_results($wpjp_query);

                foreach ($wpjp_cats as $wpjp_cat) {
                    $wpjp_ids[] = $wpjp_cat->id;
                }
                $wpjp_ids[] = $wpjobportal_category_id;

            }
            $wpjp_ids = implode(",",$wpjp_ids);
            $wpjobportal_inquery .= " AND resume.job_category IN(".$wpjp_ids.")";
        }


        // types
        $wpjobportal_jobtype_list = WPJOBPORTALrequest::getVar('types', 'shortcode_option', false);
        if ($wpjobportal_jobtype_list && $wpjobportal_jobtype_list !='' ) { // not empty check
            $wpjobportal_jobtype_array = wpjobportalphplib::wpJP_explode( ',' , $wpjobportal_jobtype_list); // handle multi case
            $wpjobportal_jobtypeQuery = false;
            foreach($wpjobportal_jobtype_array as $wpjobportal_jobtype_id){  // loop over all ids
                if($wpjobportal_jobtype_id != ''){ // null check
                    $wpjobportal_jobtype_id = trim($wpjobportal_jobtype_id);
                }
                if(!is_numeric($wpjobportal_jobtype_id)){ // numric check
                    continue;
                }
                if($wpjobportal_jobtypeQuery){
                    $wpjobportal_jobtypeQuery .= " OR resume.jobtype  = " . esc_sql($wpjobportal_jobtype_id);
                }else{
                    $wpjobportal_jobtypeQuery = " resume.jobtype  =  " . esc_sql($wpjobportal_jobtype_id);
                }
            }
            if($wpjobportal_jobtypeQuery){
                $wpjobportal_inquery .= " AND ( $wpjobportal_jobtypeQuery ) ";
            }
        }

        // resume ids
        $wpjobportal_resume_list = WPJOBPORTALrequest::getVar('ids', 'shortcode_option', false);
        if ($wpjobportal_resume_list && $wpjobportal_resume_list !='' ) { // not empty check
            $wpjobportal_resume_array = wpjobportalphplib::wpJP_explode( ',' , $wpjobportal_resume_list); // handle multi case
            $wpjobportal_resumeQuery = false;
            foreach($wpjobportal_resume_array as $wpjobportal_resume_id){  // loop over all ids
                if($wpjobportal_resume_id != ''){ // null check
                    $wpjobportal_resume_id = trim($wpjobportal_resume_id);
                }
                if(!is_numeric($wpjobportal_resume_id)){ // numric check
                    continue;
                }
                if($wpjobportal_resumeQuery){
                    $wpjobportal_resumeQuery .= " OR resume.id  = " . esc_sql($wpjobportal_resume_id);
                }else{
                    $wpjobportal_resumeQuery = " resume.id  =  " . esc_sql($wpjobportal_resume_id);
                }
            }
            if($wpjobportal_resumeQuery){
                $wpjobportal_inquery .= " AND ( $wpjobportal_resumeQuery ) ";
            }
        }


        //handle attirbute for ordering
        $sorting = WPJOBPORTALrequest::getVar('sorting', 'shortcode_option', false);
        if($sorting && $sorting != ''){
            $this->makeOrderingQueryFromShortcodeAttributesResume($sorting);
        }

        //handle attirbute for no of jobs
        $wpjobportal_no_of_resumes = WPJOBPORTALrequest::getVar('no_of_resumes', 'shortcode_option', false);
        if($wpjobportal_no_of_resumes && $wpjobportal_no_of_resumes != ''){
            wpjobportal::$_data['shortcode_option_no_of_resumes'] = (int) $wpjobportal_no_of_resumes;
        }


        // handle visibilty of data based on shortcode
        $this->handleDataVisibilityByShortcodeAttributesResume();
        return $wpjobportal_inquery;
    }


    function makeOrderingQueryFromShortcodeAttributesResume($sorting) {
        switch ($sorting) {
            //name
            case "name_desc":
                wpjobportal::$_data['sorting'] = " resume.first_name DESC ";
                break;
            case "name_asc":
                wpjobportal::$_data['sorting'] = " resume.first_name ASC ";
                break;
            //posted
            case "posted_desc":
                wpjobportal::$_data['sorting'] = " resume.created DESC ";
                break;
            case "posted_asc":
                wpjobportal::$_data['sorting'] = " resume.created ASC ";
                break;
            // category
            case 'category_asc':
                wpjobportal::$_data['sorting'] = ' category.cat_title ASC ';
                break;
            case 'category_desc':
                wpjobportal::$_data['sorting'] = ' category.cat_title DESC ';
                break;
            // jobtype
            case 'jobtype_asc':
                wpjobportal::$_data['sorting'] = ' jobtype.title ASC ';
                break;
            case 'jobtype_desc':
                wpjobportal::$_data['sorting'] = ' jobtype.title DESC ';
                break;
        }
        return;
    }

    function handleDataVisibilityByShortcodeAttributesResume() {

        //handle attirbute for hide company logo on job listing
        $wpjobportal_hide_resume_photo = WPJOBPORTALrequest::getVar('hide_resume_photo', 'shortcode_option', false);
        if($wpjobportal_hide_resume_photo && $wpjobportal_hide_resume_photo != ''){
            wpjobportal::$_data['shortcode_option_hide_resume_photo'] = 1;
        }

        //handle attirbute for hide company name on job listing
        $wpjobportal_hide_resume_location = WPJOBPORTALrequest::getVar('hide_resume_location', 'shortcode_option', false);
        if($wpjobportal_hide_resume_location && $wpjobportal_hide_resume_location != ''){
            wpjobportal::$_data['shortcode_option_hide_resume_location'] = 1;
        }

        //handle attirbute for hide company name on job listing
        $wpjobportal_hide_resume_salary = WPJOBPORTALrequest::getVar('hide_resume_salary', 'shortcode_option', false);
        if($wpjobportal_hide_resume_salary && $wpjobportal_hide_resume_salary != ''){
            wpjobportal::$_data['shortcode_option_hide_resume_salary'] = 1;
        }
    }

    function getResumesForJobapply(){
        $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        $wpjobportal_resume_list = array();
        if( is_numeric($wpjobportal_uid) && $wpjobportal_uid > 0 ){
            $query = "SELECT id,application_title,first_name,last_name FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` WHERE status = 1 AND quick_apply <> 1 AND uid = ". esc_sql($wpjobportal_uid);
            // code to handle unpublished application_title
            $wpjobportal_resume_data = wpjobportal::$_db->get_results($query);
            $wpjobportal_resume_list = array();
            foreach ($wpjobportal_resume_data as $wpjobportal_single_resume) {
                $wpjobportal_resume_record = new stdClass();
                $wpjobportal_resume_record->id = $wpjobportal_single_resume->id;
                if($wpjobportal_single_resume->application_title != ''){
                    $wpjobportal_resume_record->text = $wpjobportal_single_resume->application_title;
                }else{
                    $wpjobportal_resume_record->text = $wpjobportal_single_resume->first_name.' '.$wpjobportal_single_resume->last_name;
                }
                $wpjobportal_resume_list[] = $wpjobportal_resume_record;
            }
        }
        return $wpjobportal_resume_list;
    }

    function getResumesByResumeIds($wpjobportal_resume_id_list){
        if($wpjobportal_resume_id_list == ''){
            return false;
        }
       //Data
        $query = "SELECT resume.id,CONCAT(resume.alias,'-',resume.id) AS resumealiasid ,resume.first_name
                ,resume.last_name,resume.application_title as applicationtitle,resume.email_address,category.cat_title
                ,resume.created,jobtype.title AS jobtypetitle,resume.photo,
                resume.isfeaturedresume,resume.endfeatureddate
                ,resume.status,city.name AS cityname
                ,state.name AS statename,resume.params,resume.salaryfixed as salary
                ,resume.last_modified,LOWER(jobtype.title) AS jobtypetit,jobtype.color as jobtypecolor,country.name AS countryname,resume.id as resumeid,resume.skills,resume.quick_apply
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS category ON category.id = resume.job_category ";
            $query .= "
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ON jobtype.id = resume.jobtype
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON city.id = (SELECT address_city FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumeaddresses` WHERE resumeid = resume.id LIMIT 1)
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_states` AS state ON state.id = city.stateid
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_countries` AS country ON country.id = city.countryid ";
                $query .= " WHERE resume.status = 1 AND resume.quick_apply <> 1";
                $query .= " AND resume.id IN (".esc_sql($wpjobportal_resume_id_list).")";
        $query .= " GROUP BY resume.id ";

        $wpjobportal_results = wpjobportal::$_db->get_results($query);
        $wpjobportal_data = array();
        foreach ($wpjobportal_results AS $d) {
            //  updated the query select to select 'name' as cityname
            $d->location = WPJOBPORTALincluder::getJSModel('common')->getLocationForView($d->cityname, $d->statename, $d->countryname);
            $wpjobportal_data[] = $d;
        }
        return $wpjobportal_data;
    }


    function updateRecordsForAISearchResume(){

        $query = "SELECT resume.*
                FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume ";
                // not sure about this
                $query .= " WHERE resume.quick_apply <> 1";
                $query .= " ORDER BY resume.id ASC ";

        $wpjobportal_results = wpjobportal::$_db->get_results($query);

        foreach ($wpjobportal_results AS $wpjobportal_resume) {
            $wpjobportal_resume_data = json_decode(json_encode($wpjobportal_resume),true); // main record is in personal section (mostly)
            $this->importAIStringDataForResume($wpjobportal_resume_data);
        }
        return;
    }


    function prepareAIStringDataForResume($wpjobportal_data){
        if(empty($wpjobportal_data)){
            return;
        }
        if(!is_numeric($wpjobportal_data['id'])){
            return;
        }

        $wpjobportal_personal_section = $wpjobportal_data['sec_1'];

        $wpjobportal_resume_ai_string_main = '';

        if (!empty($wpjobportal_personal_section['application_title'])) {
            $wpjobportal_resume_ai_string_main .= wpjobportalphplib::wpJP_trim($wpjobportal_personal_section['application_title']) . ' ';
        }


        // Job Category
        if (!empty($wpjobportal_personal_section['job_category']) && is_numeric($wpjobportal_personal_section['job_category'])) {
            $cat_id = $wpjobportal_personal_section['job_category'];
            $cat_title = WPJOBPORTALincluder::getJSModel('category')->getTitleByCategory($cat_id);
            if ($cat_title) {
                $wpjobportal_resume_ai_string_main .= $cat_title . ' ';
            }
        }

        // Job Type
        if(!isset(wpjobportal::$_data['ai']['resume']['jobtypes'])){
            wpjobportal::$_data['ai']['resume']['jobtypes'] = array();
        }
        if (!empty($wpjobportal_personal_section['jobtype']) && is_numeric($wpjobportal_personal_section['jobtype'])) {
            $type_id = $wpjobportal_personal_section['jobtype'];
            $wpjobportal_jobtype_title = WPJOBPORTALincluder::getJSModel('jobtype')->getTitleByid($type_id);

            if ($wpjobportal_jobtype_title) {
                $wpjobportal_resume_ai_string_main .= $wpjobportal_jobtype_title . ' ';
            }
        }

        // Nationality (Country)
        if(!isset(wpjobportal::$_data['ai']['resume']['countries'])){
            wpjobportal::$_data['ai']['resume']['countries'] = array();
        }
        if (!empty($wpjobportal_personal_section['nationality']) && is_numeric($wpjobportal_personal_section['nationality'])) {
            $wpjobportal_country_id = $wpjobportal_personal_section['nationality'];
            $cuntry_name = WPJOBPORTALincluder::getJSModel('country')->getCountryName($wpjobportal_country_id);
            if ($cuntry_name) {
                $wpjobportal_resume_ai_string_main .= $cuntry_name . ' ';
            }
        }

        if (!empty($wpjobportal_personal_section['gender']) && is_numeric($wpjobportal_personal_section['gender'])) {
            $wpjobportal_gender_title = '';
            if($wpjobportal_personal_section['gender'] == 1){
                $wpjobportal_gender_title = esc_html(__('Male', 'wp-job-portal'));
            }elseif($wpjobportal_personal_section['gender'] == 2){
                $wpjobportal_gender_title = esc_html(__('Female', 'wp-job-portal'));
            }
            if($wpjobportal_gender_title != ''){
                $wpjobportal_resume_ai_string_main .= $wpjobportal_gender_title . ' ';
            }
        }

        if (!empty($wpjobportal_personal_section['salaryfixed'])) {
            $wpjobportal_resume_ai_string_main .= wpjobportalphplib::wpJP_trim($wpjobportal_personal_section['salaryfixed']) . ' ';
        }

        if (!empty($wpjobportal_personal_section['tags'])) {
            $wpjobportal_resume_ai_string_main .= wpjobportalphplib::wpJP_trim($wpjobportal_personal_section['tags']) . ' ';
        }

        // handle custom fields

        $custom_fields = WPJOBPORTALincluder::getJSModel('customfield')->getUserfieldsfor(3,1);// 3 fieldfor 1 section;
        // ignore these field types from current case
        $wpjobportal_skip_types = ['file', 'email', 'textarea'];

        $wpjobportal_text_area_field_values = '';

        foreach ($custom_fields as $wpjobportal_single_field) {
            if(!in_array($wpjobportal_single_field->userfieldtype, $wpjobportal_skip_types)){ // check if type agaisnt array
                if (!empty($wpjobportal_personal_section[$wpjobportal_single_field->field])) { // check value exsists
                    if(is_array($wpjobportal_personal_section[$wpjobportal_single_field->field])){ // to handle multi select and check box case
                        $wpjobportal_resume_ai_string_main .= implode(',', $wpjobportal_personal_section[$wpjobportal_single_field->field]) . ' ';
                    }else{
                        $wpjobportal_resume_ai_string_main .= $wpjobportal_personal_section[$wpjobportal_single_field->field] . ' ';
                    }
                }
            }elseif ($wpjobportal_single_field->userfieldtype == 'textarea'){ // text area value to be included in description column
                if (!empty($wpjobportal_personal_section[$wpjobportal_single_field->field])) { // check value exsists
                    $wpjobportal_text_area_field_values .= $wpjobportal_personal_section[$wpjobportal_single_field->field] . ' ';
                }
            }
        }

        $wpjobportal_resume_ai_string_main = trim($wpjobportal_resume_ai_string_main); // Clean trailing space

        // SEOND LEVEL
        $wpjobportal_resume_ai_string_desc = $wpjobportal_resume_ai_string_main;
        $wpjobportal_resume_ai_string_desc .= $wpjobportal_text_area_field_values; // append personal section text area here

        foreach ($wpjobportal_data as $wpjobportal_section => $wpjobportal_fields) {
            if ($wpjobportal_section == 'sec_1' ||  $wpjobportal_section == 'sec_6' ||  $wpjobportal_section == 'sec_7') {
                continue; // avoid un expected cases & Skip personal, resume and refrence section
            }

            if(!is_array($wpjobportal_fields) ){ // skip fields that are are not sections
                continue;
            }
            $wpjobportal_num_entries = 0;
            if(is_array(reset($wpjobportal_fields))){
                $wpjobportal_num_entries = count(reset($wpjobportal_fields)); // to handle multiple section instances of same section
            }
            for ($wpjobportal_i = 0; $wpjobportal_i < $wpjobportal_num_entries; $wpjobportal_i++) {
                if (!empty($wpjobportal_fields['deletethis'][$wpjobportal_i]) && $wpjobportal_fields['deletethis'][$wpjobportal_i] == 1) { // ignore delete this indexes
                    continue;
                }

                $record_string = "";
                foreach ($wpjobportal_fields as $wpjobportal_key => $wpjobportal_values) {
                    $wpjobportal_value = $wpjobportal_values[$wpjobportal_i] ?? '';

                    // Collect address_city values separately
                    if ($wpjobportal_key === 'address_city' && !empty($wpjobportal_value)) { // handle cityids to get complete location name
                        $location_string = WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($wpjobportal_value);
                        if($location_string){ // the above function may return false
                            $wpjobportal_value = $location_string;
                            $wpjobportal_resume_ai_string_main .= $location_string." ";
                        }
                    }
                    if ($wpjobportal_key === 'deletethis') { // ignore delete this case
                        continue;
                    }
                     // If value is a nested array, flatten it
                    if (is_array($wpjobportal_value)) {
                        $wpjobportal_value = implode(",", array_filter($wpjobportal_value));
                    }
                    if (!empty($wpjobportal_value)) {
                        $record_string .= $wpjobportal_value." ";
                    }
                }
                $wpjobportal_resume_ai_string_desc .= $record_string. " "; //add current section's current instance value to main string
            }
        }

        // handle custom sections

        $wpjobportal_resumesections = WPJOBPORTALincluder::getJSModel('fieldordering')->getResumeSections();
        foreach ($wpjobportal_resumesections as $wpjobportal_section) {
            if($wpjobportal_section->section <= 8){ // avoid the default resume sections
                continue;
            }
            // get fields for section
            $custom_fields = WPJOBPORTALincluder::getJSModel('customfield')->getUserfieldsfor(3,$wpjobportal_section->section);// 3 fieldfor $wpjobportal_section->section for section
            // ignore these field types from current case
            $wpjobportal_skip_types = ['file', 'email'];

            foreach ($custom_fields as $wpjobportal_single_field) {
                if(!in_array($wpjobportal_single_field->userfieldtype, $wpjobportal_skip_types)){ // check if type agaisnt array
                    if (!empty($wpjobportal_data[$wpjobportal_single_field->field])) { // check value exsists
                        if(is_array($wpjobportal_data[$wpjobportal_single_field->field])){ // to handle multi select and check box case
                            $wpjobportal_resume_ai_string_desc .= implode(',', $wpjobportal_data[$wpjobportal_single_field->field]) . ' ';
                        }else{
                            $wpjobportal_resume_ai_string_desc .= $wpjobportal_data[$wpjobportal_single_field->field] . ' ';
                        }
                    }
                }
            }
        }

        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('resume');
        if ($wpjobportal_row->update(array('id'=>$wpjobportal_data['id'], 'airesumesearchtext' => $wpjobportal_resume_ai_string_main, 'airesumesearchdescription' => $wpjobportal_resume_ai_string_desc))) {
            return;
        }
        return;
    }

    function importAIStringDataForResume($wpjobportal_personal_section){
        if(empty($wpjobportal_personal_section)){
            return;
        }
        if(!is_numeric($wpjobportal_personal_section['id'])){
            return;
        }

        $wpjobportal_resume_ai_string_main = '';

        if (!empty($wpjobportal_personal_section['application_title'])) {
            $wpjobportal_resume_ai_string_main .= wpjobportalphplib::wpJP_trim($wpjobportal_personal_section['application_title']) . ' ';
        }

        // Initialize ai temp caches if not already exsist

        if(!isset(wpjobportal::$_data['ai'])){
            wpjobportal::$_data['ai'] = array();
        }
        // second level set to resume to avoid conflit with job records
        if(!isset(wpjobportal::$_data['ai']['resume'])){
            wpjobportal::$_data['ai']['resume'] = array();
        }

        if(!isset(wpjobportal::$_data['ai']['resume']['categories'])){
            wpjobportal::$_data['ai']['resume']['categories'] = array();
        }
        // Job Category
        if (!empty($wpjobportal_personal_section['job_category']) && is_numeric($wpjobportal_personal_section['job_category'])) {
            $cat_id = $wpjobportal_personal_section['job_category'];
            if (!isset(wpjobportal::$_data['ai']['resume']['categories'][$cat_id])) {
                $cat_title = WPJOBPORTALincluder::getJSModel('category')->getTitleByCategory($cat_id);
                wpjobportal::$_data['ai']['resume']['categories'][$cat_id] = $cat_title;
            } else {
                $cat_title = wpjobportal::$_data['ai']['resume']['categories'][$cat_id];
            }

            if ($cat_title) {
                $wpjobportal_resume_ai_string_main .= $cat_title . ' ';
            }
        }

        // Job Type
        if(!isset(wpjobportal::$_data['ai']['resume']['jobtypes'])){
            wpjobportal::$_data['ai']['resume']['jobtypes'] = array();
        }
        if (!empty($wpjobportal_personal_section['jobtype']) && is_numeric($wpjobportal_personal_section['jobtype'])) {
            $type_id = $wpjobportal_personal_section['jobtype'];
            if (!isset(wpjobportal::$_data['ai']['resume']['jobtypes'][$type_id])) {
                $wpjobportal_jobtype_title = WPJOBPORTALincluder::getJSModel('jobtype')->getTitleByid($type_id);
                wpjobportal::$_data['ai']['resume']['jobtypes'][$type_id] = $wpjobportal_jobtype_title;
            } else {
                $wpjobportal_jobtype_title = wpjobportal::$_data['ai']['resume']['jobtypes'][$type_id];
            }

            if ($wpjobportal_jobtype_title) {
                $wpjobportal_resume_ai_string_main .= $wpjobportal_jobtype_title . ' ';
            }
        }

        // Nationality (Country)
        if(!isset(wpjobportal::$_data['ai']['resume']['countries'])){
            wpjobportal::$_data['ai']['resume']['countries'] = array();
        }
        if (!empty($wpjobportal_personal_section['nationality']) && is_numeric($wpjobportal_personal_section['nationality'])) {
            $wpjobportal_country_id = $wpjobportal_personal_section['nationality'];
            if (!isset(wpjobportal::$_data['ai']['resume']['countries'][$wpjobportal_country_id])) {
                $cuntry_name = WPJOBPORTALincluder::getJSModel('country')->getCountryName($wpjobportal_country_id);
                wpjobportal::$_data['ai']['resume']['countries'][$wpjobportal_country_id] = $cuntry_name;
            } else {
                $cuntry_name = wpjobportal::$_data['ai']['resume']['countries'][$wpjobportal_country_id];
            }

            if ($cuntry_name) {
                $wpjobportal_resume_ai_string_main .= $cuntry_name . ' ';
            }
        }

        if (!empty($wpjobportal_personal_section['gender']) && is_numeric($wpjobportal_personal_section['gender'])) {
            $wpjobportal_gender_title = '';
            if($wpjobportal_personal_section['gender'] == 1){
                $wpjobportal_gender_title = esc_html(__('Male', 'wp-job-portal'));
            }elseif($wpjobportal_personal_section['gender'] == 2){
                $wpjobportal_gender_title = esc_html(__('Female', 'wp-job-portal'));
            }
            if($wpjobportal_gender_title != ''){
                $wpjobportal_resume_ai_string_main .= $wpjobportal_gender_title . ' ';
            }
        }

        if (!empty($wpjobportal_personal_section['salaryfixed'])) {
            $wpjobportal_resume_ai_string_main .= wpjobportalphplib::wpJP_trim($wpjobportal_personal_section['salaryfixed']) . ' ';
        }

        if (!empty($wpjobportal_personal_section['tags'])) {
            $wpjobportal_resume_ai_string_main .= wpjobportalphplib::wpJP_trim($wpjobportal_personal_section['tags']) . ' ';
        }

        // handle custom fields
        if(!isset(wpjobportal::$_data['ai']['customfields_user_3'])){
            wpjobportal::$_data['ai']['customfields_user_3'] = WPJOBPORTALincluder::getJSModel('customfield')->getUserfieldsfor(3,1);// 3 fieldfor 1 section;
        }

        $custom_fields = wpjobportal::$_data['ai']['customfields_user_3'];
        // $custom_fields = WPJOBPORTALincluder::getJSModel('customfield')->getUserfieldsfor(3,1);// 3 fieldfor 1 section
        // ignore these field types from current case
        $wpjobportal_skip_types = ['file', 'email', 'textarea'];

        $wpjobportal_text_area_field_values = '';

        foreach ($custom_fields as $wpjobportal_single_field) {
            if(!in_array($wpjobportal_single_field->userfieldtype, $wpjobportal_skip_types)){ // check if type agaisnt array
                if (!empty($wpjobportal_personal_section[$wpjobportal_single_field->field])) { // check value exsists
                    if(is_array($wpjobportal_personal_section[$wpjobportal_single_field->field])){ // to handle multi select and check box case
                        $wpjobportal_resume_ai_string_main .= implode(',', $wpjobportal_personal_section[$wpjobportal_single_field->field]) . ' ';
                    }else{
                        $wpjobportal_resume_ai_string_main .= $wpjobportal_personal_section[$wpjobportal_single_field->field] . ' ';
                    }
                }
            }elseif ($wpjobportal_single_field->userfieldtype == 'textarea'){
                if (!empty($wpjobportal_personal_section[$wpjobportal_single_field->field])) { // check value exsists
                    $wpjobportal_text_area_field_values .= $wpjobportal_personal_section[$wpjobportal_single_field->field] . ' ';
                }
            }
        }

        $wpjobportal_resume_ai_string_main = trim($wpjobportal_resume_ai_string_main); // Clean trailing space

        // SEOND LEVEL
        $wpjobportal_resume_ai_string_desc = $wpjobportal_resume_ai_string_main;
        $wpjobportal_resume_ai_string_desc .= $wpjobportal_text_area_field_values; // append personal section text area here

        // handle custom sections

        $wpjobportal_resumesections = WPJOBPORTALincluder::getJSModel('fieldordering')->getResumeSections();

        // resume sections
        // if(in_array('advanceresumebuilder', wpjobportal::$_active_addons)){
        foreach ($wpjobportal_resumesections AS $wpjobportal_section) {
            switch ($wpjobportal_section->field){
                case 'section_address':
                    $wpjobportal_fields = wpjobportal::$_wpjpfieldordering->getUserfieldsfor(3 , $wpjobportal_section->section);// 3 for resume section for section
                    $wpjobportal_section_data = $this->getAddressSectionDataForAI($wpjobportal_personal_section['id'],$wpjobportal_fields);
                    $wpjobportal_resume_ai_string_main .= $wpjobportal_section_data['location_names'];
                    $wpjobportal_resume_ai_string_desc .= $wpjobportal_section_data['address_string'];
                break;
                case 'section_education':
                    $wpjobportal_fields = wpjobportal::$_wpjpfieldordering->getUserfieldsfor(3 , $wpjobportal_section->section);// 3 for resume section for section
                    $wpjobportal_section_data = $this->getEducationSectionDataForAI($wpjobportal_personal_section['id'],$wpjobportal_fields);
                    $wpjobportal_resume_ai_string_desc .= $wpjobportal_section_data;
                    break;
                case 'section_employer':
                    $wpjobportal_fields = wpjobportal::$_wpjpfieldordering->getUserfieldsfor(3 , $wpjobportal_section->section);// 3 for resume section for section
                    $wpjobportal_section_data = $this->getEmployerSectionDataForAI($wpjobportal_personal_section['id'],$wpjobportal_fields);
                    $wpjobportal_resume_ai_string_desc .= $wpjobportal_section_data;
                    break;
                case 'section_skills':
                    $wpjobportal_fields = wpjobportal::$_wpjpfieldordering->getUserfieldsfor(3 , $wpjobportal_section->section);// 3 for resume section for section
                    $wpjobportal_section_data = $this->getSkillSectionDataForAI($wpjobportal_personal_section['id'],$wpjobportal_fields,$wpjobportal_personal_section);
                    $wpjobportal_resume_ai_string_desc .= $wpjobportal_section_data;
                    break;
                case 'section_language':
                    $wpjobportal_fields = wpjobportal::$_wpjpfieldordering->getUserfieldsfor(3 , $wpjobportal_section->section);// 3 for resume section for section
                    $wpjobportal_section_data = $this->getLanguageSectionDataForAI($wpjobportal_personal_section['id'],$wpjobportal_fields);
                    $wpjobportal_resume_ai_string_desc .= $wpjobportal_section_data;
                    break;
                default:
                    if($wpjobportal_section->is_section_headline == 1){ // to print resume custom sections
                        if($wpjobportal_section->field != 'section_resume' && $wpjobportal_section->section > 8){ // avoid resume editor section legacy code issue and anyother section
                            $wpjobportal_fields = wpjobportal::$_wpjpfieldordering->getUserfieldsfor(3 , $wpjobportal_section->section);// 3 for resume section for section
                            $wpjobportal_section_data = $this->getCustomSectionDataForAI($wpjobportal_personal_section['id'],$wpjobportal_fields,$wpjobportal_personal_section);
                            $wpjobportal_resume_ai_string_desc .= $wpjobportal_section_data;
                        }
                    }
                break;
            }
        }

        // echo var_dump($wpjobportal_resume_ai_string_desc);
        // echo '<br>';
        // echo '<br>';
        // echo '<br>';
        // return;
        // foreach ($wpjobportal_resumesections as $wpjobportal_section) {
        //     if($wpjobportal_section->section <= 8){ // avoid the default resume sections
        //         continue;
        //     }
        //     // get fields for section
        //     $custom_fields = WPJOBPORTALincluder::getJSModel('customfield')->getUserfieldsfor(3,$wpjobportal_section->section);// 3 fieldfor $wpjobportal_section->section for section
        //     // ignore these field types from current case
        //     $wpjobportal_skip_types = ['file', 'email'];

        //     foreach ($custom_fields as $wpjobportal_single_field) {
        //         if(!in_array($wpjobportal_single_field->userfieldtype, $wpjobportal_skip_types)){ // check if type agaisnt array
        //             if (!empty($wpjobportal_data[$wpjobportal_single_field->field])) { // check value exsists
        //                 if(is_array($wpjobportal_data[$wpjobportal_single_field->field])){ // to handle multi select and check box case
        //                     $wpjobportal_resume_ai_string_desc .= implode(',', $wpjobportal_data[$wpjobportal_single_field->field]) . ' ';
        //                 }else{
        //                     $wpjobportal_resume_ai_string_desc .= $wpjobportal_data[$wpjobportal_single_field->field] . ' ';
        //                 }
        //             }
        //         }
        //     }
        // }

        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('resume');
        if ($wpjobportal_row->update(array('id'=>$wpjobportal_personal_section['id'], 'airesumesearchtext' => $wpjobportal_resume_ai_string_main, 'airesumesearchdescription' => $wpjobportal_resume_ai_string_desc))) {
            return;
        }
        return;
    }

    // get resume address sections by resume id
    function getAddressSectionDataForAI($wpjobportal_resumeid,$wpjobportal_fields){
        if (!is_numeric($wpjobportal_resumeid))
            return false;

        $return_data = array();

        $query = "SELECT resumeaddress.address_city, resumeaddress.address
                        ,resumeaddress.params,resumeaddress.longitude,resumeaddress.latitude
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumeaddresses` resumeaddress
                    WHERE resumeaddress.resumeid = " . esc_sql($wpjobportal_resumeid);

        $wpjobportal_results = wpjobportaldb::get_results($query);
        $wpjobportal_resume_address_section_string = '';
        $wpjobportal_main_string_location_names = '';
        $wpjobportal_skip_types = ['file', 'email'];
        if(!empty($wpjobportal_results)){
            foreach ($wpjobportal_results as $wpjobportal_address) {

                // address city
                if(!empty($wpjobportal_address->address_city)){
                    $location_name = WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($wpjobportal_address->address_city);
                    if($location_name != ''){
                        $wpjobportal_resume_address_section_string .= $location_name.' ';
                        $wpjobportal_main_string_location_names .= $location_name.' ';
                    }
                }

                // address
                if(!empty($wpjobportal_address->address)){
                    $wpjobportal_resume_address_section_string .= $wpjobportal_address->address.' ';
                }

                // latitude
                if(!empty($wpjobportal_address->latitude)){
                    $wpjobportal_resume_address_section_string .= $wpjobportal_address->latitude.' ';
                }
                // longitude
                if(!empty($wpjobportal_address->longitude)){
                    $wpjobportal_resume_address_section_string .= $wpjobportal_address->longitude.' ';
                }

                // params
                if(!empty($wpjobportal_address->params)){
                    $params = json_decode($wpjobportal_address->params,true);
                }


                // custom field for address section
                // $wpjobportal_fields = wpjobportal::$_wpjpfieldordering->getUserfieldsfor( 3 , 2);// 3 for resume 2 for address section
                foreach($wpjobportal_fields AS $wpjobportal_field){
                    if(!in_array($wpjobportal_field->userfieldtype,$wpjobportal_skip_types)){
                        if(isset($params[$wpjobportal_field->field]) && $params[$wpjobportal_field->field] != ''){ // only add value for a section once. not for all its custom fields
                            $wpjobportal_resume_address_section_string .= $params[$wpjobportal_field->field].' ';
                        }
                    }
                }
            }
        }

        $return_data['address_string'] = $wpjobportal_resume_address_section_string;
        $return_data['location_names'] = $wpjobportal_main_string_location_names;
        return $return_data;
    }

    // get resume institutes sections by resume id
    function getEducationSectionDataForAI($wpjobportal_resumeid,$wpjobportal_fields){
        if (!is_numeric($wpjobportal_resumeid))
            return false;

        $return_data = '';

        $query = "SELECT resumeinstitute.institute, resumeinstitute.institute_certificate_name
                        ,resumeinstitute.params,resumeinstitute.institute_study_area,resumeinstitute.todate,resumeinstitute.fromdate
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumeinstitutes` resumeinstitute
                    WHERE resumeinstitute.resumeid = " . esc_sql($wpjobportal_resumeid);

        $wpjobportal_results = wpjobportaldb::get_results($query);
        $wpjobportal_resume_institute_section_string = '';
        $wpjobportal_skip_types = ['file', 'email'];
        if(!empty($wpjobportal_results)){
            foreach ($wpjobportal_results as $wpjobportal_resumeinstitute) {
                // institute
                if(!empty($wpjobportal_resumeinstitute->institute)){
                    $wpjobportal_resume_institute_section_string .= $wpjobportal_resumeinstitute->institute.' ';
                }

                // institute_certificate_name
                if(!empty($wpjobportal_resumeinstitute->institute_certificate_name)){
                    $wpjobportal_resume_institute_section_string .= $wpjobportal_resumeinstitute->institute_certificate_name.' ';
                }
                // institute_study_area
                if(!empty($wpjobportal_resumeinstitute->institute_study_area)){
                    $wpjobportal_resume_institute_section_string .= $wpjobportal_resumeinstitute->institute_study_area.' ';
                }
                // todate
                if(!empty($wpjobportal_resumeinstitute->todate)){
                    $wpjobportal_resume_institute_section_string .= $wpjobportal_resumeinstitute->todate.' ';
                }
                // fromdate
                if(!empty($wpjobportal_resumeinstitute->fromdate)){
                    $wpjobportal_resume_institute_section_string .= $wpjobportal_resumeinstitute->fromdate.' ';
                }

                if(!empty($wpjobportal_resumeinstitute->params)){
                    $params = json_decode($wpjobportal_resumeinstitute->params,true);
                }

                // custom field for resumeinstitute section
                // $wpjobportal_fields = wpjobportal::$_wpjpfieldordering->getUserfieldsfor( 3 , 3);// 3 for resume 3 for institute section
                foreach($wpjobportal_fields AS $wpjobportal_field){
                    if(!in_array($wpjobportal_field->userfieldtype,$wpjobportal_skip_types)){
                        if(isset($params[$wpjobportal_field->field]) && $params[$wpjobportal_field->field] != ''){ // only add value for a section once. not for all its custom fields
                            $wpjobportal_resume_institute_section_string .= $params[$wpjobportal_field->field].' ';
                        }
                    }
                }
            }
        }

        $return_data = $wpjobportal_resume_institute_section_string;
        return $return_data;
    }


    // get resume employer sections by resume id
    function getEmployerSectionDataForAI($wpjobportal_resumeid,$wpjobportal_fields){
        if (!is_numeric($wpjobportal_resumeid))
            return false;

        $return_data = '';

        $query = "SELECT resumeemployer.employer, resumeemployer.employer_position, resumeemployer.employer_from_date
                    , resumeemployer.employer_current_status, resumeemployer.employer_to_date, resumeemployer.employer_phone
                    , resumeemployer.employer_address, resumeemployer.employer_city, resumeemployer.params
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumeemployers` resumeemployer
                    WHERE resumeemployer.resumeid = " . esc_sql($wpjobportal_resumeid);

        $wpjobportal_results = wpjobportaldb::get_results($query);
        $wpjobportal_resume_employer_section_string = '';
        $wpjobportal_skip_types = ['file', 'email'];
        if(!empty($wpjobportal_results)){
            foreach ($wpjobportal_results as $wpjobportal_resumeemployer) {

                // employer
                if(!empty($wpjobportal_resumeemployer->employer)){
                    $wpjobportal_resume_employer_section_string .= $wpjobportal_resumeemployer->employer.' ';
                }

                // employer_position
                if(!empty($wpjobportal_resumeemployer->employer_position)){
                    $wpjobportal_resume_employer_section_string .= $wpjobportal_resumeemployer->employer_position.' ';
                }

                // employer_from_date
                if(!empty($wpjobportal_resumeemployer->employer_from_date)){
                    $wpjobportal_resume_employer_section_string .= $wpjobportal_resumeemployer->employer_from_date.' ';
                }

                // employer_current_status
                if(!empty($wpjobportal_resumeemployer->employer_current_status)){
                    $wpjobportal_resume_employer_section_string .= $wpjobportal_resumeemployer->employer_current_status.' ';
                }

                // employer_to_date
                if(!empty($wpjobportal_resumeemployer->employer_to_date)){
                    $wpjobportal_resume_employer_section_string .= $wpjobportal_resumeemployer->employer_to_date.' ';
                }

                // employer_phone
                if(!empty($wpjobportal_resumeemployer->employer_phone)){
                    $wpjobportal_resume_employer_section_string .= $wpjobportal_resumeemployer->employer_phone.' ';
                }

                // employer_address
                if(!empty($wpjobportal_resumeemployer->employer_address)){
                    $wpjobportal_resume_employer_section_string .= $wpjobportal_resumeemployer->employer_address.' ';
                }

                // address city
                if(!empty($wpjobportal_resumeemployer->employer_city)){
                    $location_name = WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($wpjobportal_resumeemployer->employer_city);
                    if($location_name != ''){
                        $wpjobportal_resume_employer_section_string .= $location_name.' ';
                    }
                }

                if(!empty($wpjobportal_resumeemployer->params)){
                    $params = json_decode($wpjobportal_resumeemployer->params,true);
                }

                // custom field for resumeemployer section
                // $wpjobportal_fields = wpjobportal::$_wpjpfieldordering->getUserfieldsfor( 3 , 3);// 3 for resume 3 for institute section
                foreach($wpjobportal_fields AS $wpjobportal_field){
                    if(!in_array($wpjobportal_field->userfieldtype,$wpjobportal_skip_types)){
                        if(isset($params[$wpjobportal_field->field]) && $params[$wpjobportal_field->field] != ''){ // only add value for a section once. not for all its custom fields
                            $wpjobportal_resume_employer_section_string .= $params[$wpjobportal_field->field].' ';
                        }
                    }
                }
            }
        }

        $return_data = $wpjobportal_resume_employer_section_string;
        return $return_data;
    }


    // get resume skill sections by resume id
    function getSkillSectionDataForAI($wpjobportal_resumeid,$wpjobportal_fields,$wpjobportal_data){
        if (!is_numeric($wpjobportal_resumeid))
            return false;

        $return_data = '';
        $wpjobportal_resume_skill_section_string = '';
		$wpjobportal_skip_types = ['file', 'email'];


        // skill
        if(!empty($wpjobportal_data['skills'])){
            $wpjobportal_resume_skill_section_string .= $wpjobportal_data['skills'].' ';
        }

        if(!empty($wpjobportal_data['params'])){
            $params = json_decode($wpjobportal_data['params'],true);
        }

        // custom field for resumeemployer section
        // $wpjobportal_fields = wpjobportal::$_wpjpfieldordering->getUserfieldsfor( 3 , 3);// 3 for resume 3 for institute section
        foreach($wpjobportal_fields AS $wpjobportal_field){
            if(!in_array($wpjobportal_field->userfieldtype,$wpjobportal_skip_types)){
                if(isset($params[$wpjobportal_field->field]) && $params[$wpjobportal_field->field] != ''){ // only add value for a section once. not for all its custom fields
                    $wpjobportal_resume_skill_section_string .= $params[$wpjobportal_field->field].' ';
                }
            }
        }


        $return_data = $wpjobportal_resume_skill_section_string;
        return $return_data;
    }

    // get resume language sections by resume id
    function getLanguageSectionDataForAI($wpjobportal_resumeid,$wpjobportal_fields){
        if (!is_numeric($wpjobportal_resumeid))
            return false;

        $return_data = '';

        $query = "SELECT resumelanguage.language,  resumelanguage.params
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumelanguages` resumelanguage
                    WHERE resumelanguage.resumeid = " . esc_sql($wpjobportal_resumeid);

        $wpjobportal_results = wpjobportaldb::get_results($query);
        $wpjobportal_resume_language_section_string = '';
        $wpjobportal_skip_types = ['file', 'email'];
        if(!empty($wpjobportal_results)){
            foreach ($wpjobportal_results as $wpjobportal_resumelanguage) {

                // language
                if(!empty($wpjobportal_resumelanguage->language)){
                    $wpjobportal_resume_language_section_string .= $wpjobportal_resumelanguage->language.' ';
                }

                if(!empty($wpjobportal_resumelanguage->params)){
                    $params = json_decode($wpjobportal_resumelanguage->params,true);
                }

                // custom field for resumelanguage section
                // $wpjobportal_fields = wpjobportal::$_wpjpfieldordering->getUserfieldsfor( 3 , 3);// 3 for resume 3 for institute section
                foreach($wpjobportal_fields AS $wpjobportal_field){
                    if(!in_array($wpjobportal_field->userfieldtype,$wpjobportal_skip_types)){
                        if(isset($params[$wpjobportal_field->field]) && $params[$wpjobportal_field->field] != ''){ // only add value for a section once. not for all its custom fields
                            $wpjobportal_resume_language_section_string .= $params[$wpjobportal_field->field].' ';
                        }
                    }
                }
            }
        }

        $return_data = $wpjobportal_resume_language_section_string;
        return $return_data;
    }


    // get resume skill sections by resume id
    function getCustomSectionDataForAI($wpjobportal_resumeid,$wpjobportal_fields,$wpjobportal_data){
        if (!is_numeric($wpjobportal_resumeid))
            return false;

        $return_data = '';
        $wpjobportal_resume_custom_section_string = '';

        if(!empty($wpjobportal_data['params'])){
            $params = json_decode($wpjobportal_data['params'],true);
        }
        $wpjobportal_skip_types = ['file', 'email'];
        // custom field for resumeemployer section
        // $wpjobportal_fields = wpjobportal::$_wpjpfieldordering->getUserfieldsfor( 3 , 3);// 3 for resume 3 for institute section
        foreach($wpjobportal_fields AS $wpjobportal_field){
            if(!in_array($wpjobportal_field->userfieldtype,$wpjobportal_skip_types)){
                if(isset($params[$wpjobportal_field->field]) && $params[$wpjobportal_field->field] != ''){ // only add value for a section once. not for all its custom fields
                    $wpjobportal_resume_custom_section_string .= $params[$wpjobportal_field->field].' ';
                }
            }
        }


        $return_data = $wpjobportal_resume_custom_section_string;
        return $return_data;
    }
}
?>
