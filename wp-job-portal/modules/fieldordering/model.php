<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALFieldorderingModel {

    function __construct() {

    }

    function fieldsRequiredOrNot($wpjobportal_ids, $wpjobportal_value) {
        if (empty($wpjobportal_ids))
            return false;
        if (!is_numeric($wpjobportal_value))
            return false;
        //Db class limitations
        $wpjobportal_total = 0;
        foreach ($wpjobportal_ids as $wpjobportal_id) {
            if(is_numeric($wpjobportal_id) && is_numeric($wpjobportal_value)){
                $query = "UPDATE " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering SET required = " . esc_sql($wpjobportal_value) . " WHERE id = " . esc_sql($wpjobportal_id) . " AND sys=0";
                if (false === wpjobportaldb::query($query)) {
                    $wpjobportal_total += 1;
                }
            }else{
                $wpjobportal_total += 1;
            }
        }
        if ($wpjobportal_total == 0) {
            WPJOBPORTALMessages::$wpjobportal_counter = false;
            if ($wpjobportal_value == 1)
                return WPJOBPORTAL_REQUIRED;
            else
                return WPJOBPORTAL_NOT_REQUIRED;
        }else {
            WPJOBPORTALMessages::$wpjobportal_counter = $wpjobportal_total;
            if ($wpjobportal_value == 1)
                return WPJOBPORTAL_REQUIRED_ERROR;
            else
                return WPJOBPORTAL_NOT_REQUIRED_ERROR;
        }
    }

    function getFieldsOrdering($wpjobportal_fieldfor) {
        if (is_numeric($wpjobportal_fieldfor) == false)
            return false;
        $title = wpjobportal::$_search['customfield']['title'];
        $ustatus = wpjobportal::$_search['customfield']['ustatus'];
        $vstatus = wpjobportal::$_search['customfield']['vstatus'];
        $wpjobportal_required = wpjobportal::$_search['customfield']['required'];
        $wpjobportal_inquery = '';
        if ($title != null)
            $wpjobportal_inquery .= " AND field.fieldtitle LIKE '%".esc_sql($title)."%'";
        if (is_numeric($ustatus))
            $wpjobportal_inquery .= " AND field.published = ".esc_sql($ustatus);
        if (is_numeric($vstatus))
            $wpjobportal_inquery .= " AND field.isvisitorpublished = ".esc_sql($vstatus);
        if (is_numeric($wpjobportal_required))
            $wpjobportal_inquery .= " AND field.required =". esc_sql($wpjobportal_required);

        wpjobportal::$_data['filter']['title'] = $title;
        wpjobportal::$_data['filter']['ustatus'] = $ustatus;
        wpjobportal::$_data['filter']['vstatus'] = $vstatus;
        wpjobportal::$_data['filter']['required'] = $wpjobportal_required;

        //Pagination
        $query = "SELECT COUNT(field.id) FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering AS field WHERE field.fieldfor = " . esc_sql($wpjobportal_fieldfor);
        $query .= $wpjobportal_inquery;
        if($wpjobportal_fieldfor == 3){
            $query .= " AND field.field NOT IN ('heighestfinisheducation','employer_supervisor','resume','section_resume')";
             if(!in_array('advanceresumebuilder', wpjobportal::$_active_addons)){
                $query .= " AND field.section = 1";
            }
        }else if($wpjobportal_fieldfor == 2){
            $query .= " AND field.field NOT IN ('sendmeresume','sendemail')";
        }
        // if(!in_array('customfield', wpjobportal::$_active_addons)){
        //     $query .= " AND (userfieldtype = '' OR userfieldtype = 'text' OR userfieldtype = 'email')";
        // }
        $wpjobportal_total = wpjobportaldb::get_var($query);
        wpjobportal::$_data['total'] = $wpjobportal_total;
        wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($wpjobportal_total);

        //Data
        $query = "SELECT field.*
                    FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering AS field
                    WHERE field.fieldfor = " . esc_sql($wpjobportal_fieldfor);
        $query .= $wpjobportal_inquery;
        // if(!in_array('customfield', wpjobportal::$_active_addons)){
        //     $query .= " AND (userfieldtype = '' OR userfieldtype = 'text' OR userfieldtype = 'email')";
        // }
        if($wpjobportal_fieldfor == 3){
            $query .= " AND field.field NOT IN ('heighestfinisheducation','employer_supervisor','resume','section_resume')";
             if(!in_array('advanceresumebuilder', wpjobportal::$_active_addons)){
                $query .= " AND field.section = 1";
            }
        }else if($wpjobportal_fieldfor == 2){
            $query .= " AND field.field NOT IN ('sendmeresume','sendemail')";
        }

        $query .= ' ORDER BY';
        if ($wpjobportal_fieldfor == 3){
            //$query .=' field.section ASC, field.is_section_headline desc, field.ordering asc';
            $query .=' field.is_section_headline desc, field.ordering asc';
        }else{
            $query .= ' field.ordering';
        }
        if ($wpjobportal_fieldfor == 3){
             $wpjobportal_resumefieldsobject_arr = array();
            $query = "SELECT field.*
                        FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering AS field
                        WHERE field.fieldfor = " . esc_sql($wpjobportal_fieldfor);
                $query .= " AND field.is_section_headline = 1";
                $query .= " AND field.field NOT IN ('heighestfinisheducation','employer_supervisor','resume','section_resume')";
                if(!in_array('advanceresumebuilder', wpjobportal::$_active_addons)){
                    $query .= " AND field.section = 1 ";
                }
                $query .= ' ORDER BY';
                $query .=' field.ordering';
          $wpjobportal_sections = wpjobportaldb::get_results($query);
            foreach ($wpjobportal_sections as $wpjobportal_section) {
                $query = "SELECT field.*
                            FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering AS field
                            WHERE field.fieldfor = " . esc_sql($wpjobportal_fieldfor);
                $query .= " AND field.section = ".$wpjobportal_section->section;
                $query .= " AND (field.is_section_headline IS NULL  || field.is_section_headline = 0) "; // to not fetch sections again
                $query .= $wpjobportal_inquery;
                $query .= " AND field.field NOT IN ('heighestfinisheducation','employer_supervisor','resume','section_resume')";
                 if(!in_array('advanceresumebuilder', wpjobportal::$_active_addons)){
                    $query .= " AND field.section = 1";
                }
                $query .= ' ORDER BY field.ordering ASC'; // to show section fields below the
                //$query .=" LIMIT " . WPJOBPORTALpagination::$_offset . "," . WPJOBPORTALpagination::$_limit;
                    //echo var_dump($query);
                $wpjobportal_section_fields = wpjobportaldb::get_results($query);

                    $wpjobportal_resumefieldsobject_arr[]  = $wpjobportal_section;
                    foreach($wpjobportal_section_fields as $wpjobportal_row){
                        $wpjobportal_resumefieldsobject = new stdClass();
                        $wpjobportal_resumefieldsobject = $wpjobportal_row;
                        $wpjobportal_resumefieldsobject_arr[] = $wpjobportal_resumefieldsobject;
                    } 
            }
            //echo '<pre>';print_r($wpjobportal_resumefieldsobject_arr);echo '</pre>';
            wpjobportal::$_data[0] = $wpjobportal_resumefieldsobject_arr;

//echo $query;        

        }else{
            
            //$query .=" LIMIT " . WPJOBPORTALpagination::$_offset . "," . WPJOBPORTALpagination::$_limit;
            wpjobportal::$_data[0] = wpjobportaldb::get_results($query);
        }
        return;
    }

    function getSearchFieldsOrdering($wpjobportal_fieldfor) {
        if (is_numeric($wpjobportal_fieldfor) == false)
            return false;
        $wpjobportal_search = WPJOBPORTALrequest::getVar('search','',0);
        $wpjobportal_inquery = '';
        $wpjobportal_inquery .= " AND field.cannotsearch = 0";
        // the below code was causing problem for the case of search disablde fields
        // if ($wpjobportal_search == 0){
        //     $wpjobportal_inquery .= " AND (field.search_user  = 1 OR field.search_visitor = 1 ) ";
        // }
        wpjobportal::$_data['filter']['search'] = $wpjobportal_search;
        //Data
        $query = "SELECT field.fieldtitle,field.id,field.search_user,field.search_visitor,field.ordering
                    FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering AS field
                    WHERE field.fieldfor = " . esc_sql($wpjobportal_fieldfor);
        $query .= $wpjobportal_inquery;
        // hide resume sub section fields from search
        if($wpjobportal_fieldfor == 3){
            $query .= " AND field.section = 1 ";
        }
        $query .= ' ORDER BY';
        $query .= ' field.search_ordering,field.ordering ';// "field.ordering" to handle the case of new install when search ordering cloumn is set null

        wpjobportal::$_data[0] = wpjobportaldb::get_results($query);
        return;
    }

    function getFieldsOrderingforForm($wpjobportal_fieldfor) {
        if (is_numeric($wpjobportal_fieldfor) == false){
            return false;
        }
        $wpjobportal_published = (WPJOBPORTALincluder::getObjectClass('user')->isguest()) ? "isvisitorpublished" : "published";
        $query = "SELECT * FROM `" . wpjobportal::$_db->prefix . "wj_portal_fieldsordering`
        WHERE $wpjobportal_published = 1 AND fieldfor = " . esc_sql($wpjobportal_fieldfor) . " ORDER BY";
        if ($wpjobportal_fieldfor == 3) // for resume it must be order by section and ordering
            $query.=" section , ";
        $query.=" ordering ASC";
        $wpjobportal_fields = array();
       // var_dump($query);
        foreach(wpjobportaldb::get_results($query) as $wpjobportal_field){
            $wpjobportal_field->validation = $wpjobportal_field->required == 1 ? 'required' : '';
            $wpjobportal_fields[$wpjobportal_field->field] = $wpjobportal_field;
        }
        if ($wpjobportal_fieldfor == 3){
            $wpjobportal_resumefields = array();
            $query = "SELECT field.*
                        FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering AS field
                        WHERE field.fieldfor = " . esc_sql($wpjobportal_fieldfor);
            $query .= " AND field.is_section_headline = 1";
                $query .= ' ORDER BY';
                $query .=' field.ordering';
          $wpjobportal_sections = wpjobportaldb::get_results($query);
            foreach ($wpjobportal_sections as $wpjobportal_section) {
                $query = "SELECT field.*
                            FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering AS field
                            WHERE $wpjobportal_published = 1 AND field.fieldfor = " . esc_sql($wpjobportal_fieldfor);
                $query .= " AND field.section = ".$wpjobportal_section->section;
                $query .= ' ORDER BY';
                $query .=' field.ordering';
                $wpjobportal_section_fields = wpjobportaldb::get_results($query);
                //echo "<pre>";
                //print_r($wpjobportal_section_fields);
                    foreach($wpjobportal_section_fields as $wpjobportal_field){
                        $wpjobportal_field->validation = $wpjobportal_field->required == 1 ? 'required' : '';
                        $wpjobportal_resumefields[$wpjobportal_field->field] = $wpjobportal_field;
                    } 
            }
            return $wpjobportal_resumefields;
        }

        return $wpjobportal_fields;
    }

    function getFieldsOrderingforSearch($wpjobportal_fieldfor) {
        if (is_numeric($wpjobportal_fieldfor) == false)
            return false;
        if (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
            $wpjobportal_published = ' AND search_visitor = 1 ';
        } else {
            $wpjobportal_published = ' AND search_user = 1 ';
        }
        // to hide resume sub section fields from search
        $wpjobportal_section_query = '';
        if($wpjobportal_fieldfor == 3){
            $wpjobportal_section_query = ' AND section = 1 ';
        }
        $query = "SELECT * FROM `" . wpjobportal::$_db->prefix . "wj_portal_fieldsordering`
                 WHERE cannotsearch = 0 AND  fieldfor = " . esc_sql($wpjobportal_fieldfor) . esc_sql($wpjobportal_published) . esc_sql($wpjobportal_section_query) . " ORDER BY search_ordering ";
        $wpjobportal_rows = wpjobportaldb::get_results($query);
        return $wpjobportal_rows;
    }

    function getFieldsOrderingforView($wpjobportal_fieldfor) {
        if (is_numeric($wpjobportal_fieldfor) == false)
            return false;
        $wpjobportal_published = (WPJOBPORTALincluder::getObjectClass('user')->isguest()) ? "isvisitorpublished" : "published";
        $query = "SELECT field,fieldtitle FROM `" . wpjobportal::$_db->prefix . "wj_portal_fieldsordering`
                WHERE ".esc_sql($wpjobportal_published)." = 1 AND fieldfor =  " . esc_sql($wpjobportal_fieldfor) . " ORDER BY";
        if ($wpjobportal_fieldfor == 3) // fields for resume
            $query.=" section ,";
        $query.=" ordering ASC";
        $wpjobportal_rows = wpjobportaldb::get_results($query);
        $return = array();

//had make changes impliment fieldtitle in view compnay
        // if($wpjobportal_fieldfor == 3){
        //     foreach ($wpjobportal_rows AS $wpjobportal_row) {
        //         $return[$wpjobportal_row->field] = $wpjobportal_row->required;
        //     }
        // }else{
            foreach ($wpjobportal_rows AS $wpjobportal_row) {
                $return[$wpjobportal_row->field] = $wpjobportal_row->fieldtitle;
            }
        // }
        if ($wpjobportal_fieldfor == 3){
            $wpjobportal_resumefields = array();
            $query = "SELECT field.*
                        FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering AS field
                        WHERE field.fieldfor = " . esc_sql($wpjobportal_fieldfor);
            $query .= " AND field.is_section_headline = 1";
                $query .= ' ORDER BY';
                $query .=' field.ordering';
          $wpjobportal_sections = wpjobportaldb::get_results($query);
            foreach ($wpjobportal_sections as $wpjobportal_section) {
                $query = "SELECT field.*
                            FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering AS field
                            WHERE $wpjobportal_published = 1 AND field.fieldfor = " . esc_sql($wpjobportal_fieldfor);
                $query .= " AND field.section = ".$wpjobportal_section->section;
                $query .= ' ORDER BY';
                $query .=' field.ordering';
                $wpjobportal_section_fields = wpjobportaldb::get_results($query);
                    foreach($wpjobportal_section_fields as $wpjobportal_field){
                        $wpjobportal_field->validation = $wpjobportal_field->required == 1 ? 'required' : '';
                        $wpjobportal_resumefields[$wpjobportal_field->field] = $wpjobportal_field;
                    } 
            }
            return $wpjobportal_resumefields;
        }

        return $return;
    }

    function getPublishedResumeSections(){
        // to hide disabled sections
        if (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
            $wpjobportal_published = ' AND field.isvisitorpublished = 1 ';
        } else {
            $wpjobportal_published = ' AND field.published = 1 ';
        }
        $query = "SELECT field.*
                    FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering AS field
                    WHERE field.fieldfor = 3
                    AND field.is_section_headline = 1 ";
        $query .= $wpjobportal_published;
        if(!in_array('advanceresumebuilder', wpjobportal::$_active_addons)){
            $query .= " AND field.field = 'section_personal'"; // for free version
        }
        $query .= ' ORDER BY field.ordering ASC ';
        $wpjobportal_sections = wpjobportaldb::get_results($query);
        $wpjobportal_section_title_array = array();
        foreach($wpjobportal_sections AS $wpjobportal_section){
            $wpjobportal_section_title_array[$wpjobportal_section->field] = $wpjobportal_section->fieldtitle;
        }
        return $wpjobportal_section_title_array;
    }

    function getResumeSections(){
        $query = "SELECT field.*
                    FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering AS field
                    WHERE field.fieldfor = 3
                    AND field.is_section_headline = 1 AND field.field != 'section_resume' ";
        if(!in_array('advanceresumebuilder', wpjobportal::$_active_addons)){
            $query .= " AND field.field = 'section_personal'"; // for free version
        }
        $query .= ' ORDER BY field.ordering';
        $wpjobportal_sections = wpjobportaldb::get_results($query);
        return $wpjobportal_sections;
    }

    function getResumeCustomSections(){
        $query = "SELECT field.field,field.section
                    FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering AS field
                    WHERE field.fieldfor = 3
                    AND field.is_section_headline = 1 AND field.field != 'section_resume' AND field.section > 8 ";// 8 is for language section greater then 8 means custom sections
        if(!in_array('advanceresumebuilder', wpjobportal::$_active_addons)){
            $query .= " AND field.field = 'section_personal'"; // for free version
        }
        $wpjobportal_sections = wpjobportaldb::get_results($query);
        return $wpjobportal_sections;
    }

    function getResumeCustomSectionsFields(){
        $query = "SELECT field.field,field.section,field.userfieldtype
                    FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering AS field
                    WHERE field.fieldfor = 3
                    AND field.is_section_headline = 1 AND field.field != 'section_resume' AND field.section > 8 ";// 8 is for language section greater then 8 means custom sections
        if(!in_array('advanceresumebuilder', wpjobportal::$_active_addons)){
            $query .= " AND field.field = 'section_personal'"; // for free version
        }
        $wpjobportal_sections = wpjobportaldb::get_results($query);
        return $wpjobportal_sections;
    }

    function getResumeCustomSectionFields($wpjobportal_section){
        if(!is_numeric($wpjobportal_section)){
            return false;
        }
        $query = "SELECT field.field,field.fieldtitle,field.section,field.is_section_headline
                    FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering AS field
                    WHERE field.fieldfor = 3
                    AND  field.section = ".esc_sql($wpjobportal_section)." ORDER BY ordering ASC ";

        $wpjobportal_sections = wpjobportaldb::get_results($query);
        return $wpjobportal_sections;
    }

    function getResumeCustomSectionFromSectionField($wpjobportal_section_field){
        $query = "SELECT field.section
                    FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering AS field
                    WHERE field.fieldfor = 3
                    AND  field.field = '".esc_sql($wpjobportal_section_field)."'";

        $wpjobportal_section = wpjobportaldb::get_var($query);
        return $wpjobportal_section;
    }

    function fieldsPublishedOrNot($wpjobportal_ids, $wpjobportal_value) {
        if (empty($wpjobportal_ids))
            return false;
        if (!is_numeric($wpjobportal_value))
            return false;

        $wpjobportal_total = 0;
        foreach ($wpjobportal_ids as $wpjobportal_id) {
            if(is_numeric($wpjobportal_id) && is_numeric($wpjobportal_value)){
                $query = "UPDATE " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering SET published = " . esc_sql($wpjobportal_value) . " WHERE id = " . esc_sql($wpjobportal_id) . " AND cannotunpublish=0";
                if (false === wpjobportaldb::query($query)) {
                    $wpjobportal_total += 1;
                }
            }else{
                $wpjobportal_total += 1;
            }
        }
        if ($wpjobportal_total == 0) {
            WPJOBPORTALMessages::$wpjobportal_counter = false;
            if ($wpjobportal_value == 1)
                return WPJOBPORTAL_PUBLISHED;
            else
                return WPJOBPORTAL_UN_PUBLISHED;
        }else {
            WPJOBPORTALMessages::$wpjobportal_counter = $wpjobportal_total;
            if ($wpjobportal_value == 1)
                return WPJOBPORTAL_PUBLISH_ERROR;
            else
                return WPJOBPORTAL_UN_PUBLISH_ERROR;
        }
    }

    function visitorFieldsPublishedOrNot($wpjobportal_ids, $wpjobportal_value) {
        if (empty($wpjobportal_ids))
            return false;
        if (!is_numeric($wpjobportal_value))
            return false;
        $wpjobportal_total = 0;
        foreach ($wpjobportal_ids as $wpjobportal_id) {
            if(is_numeric($wpjobportal_id) && is_numeric($wpjobportal_value)){
                $query = "UPDATE " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering SET isvisitorpublished = " . esc_sql($wpjobportal_value) . " WHERE id = " . esc_sql($wpjobportal_id) . " AND cannotunpublish=0";
                if (false === wpjobportaldb::query($query)) {
                    $wpjobportal_total += 1;
                }
            }else{
                $wpjobportal_total += 1;
            }
        }
        if ($wpjobportal_total == 0) {
            WPJOBPORTALMessages::$wpjobportal_counter = false;
            if ($wpjobportal_value == 1)
                return WPJOBPORTAL_PUBLISHED;
            else
                return WPJOBPORTAL_UN_PUBLISHED;
        }else {
            WPJOBPORTALMessages::$wpjobportal_counter = $wpjobportal_total;
            if ($wpjobportal_value == 1)
                return WPJOBPORTAL_PUBLISH_ERROR;
            else
                return WPJOBPORTAL_UN_PUBLISH_ERROR;
        }
    }

    /*function fieldOrderingUp($wpjobportal_field_id) {
        if (is_numeric($wpjobportal_field_id) == false)
            return false;
        $query = "UPDATE " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering AS f1, " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering AS f2
                SET f1.ordering = f1.ordering + 1
                WHERE f1.ordering = f2.ordering - 1
                AND f1.fieldfor = f2.fieldfor
                AND f2.id = " . esc_sql($wpjobportal_field_id);

        if (false == wpjobportaldb::query($query)) {
            return WPJOBPORTAL_ORDER_UP_ERROR;
        }

        $query = " UPDATE " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering
                    SET ordering = ordering - 1
                    WHERE id = " . esc_sql($wpjobportal_field_id);

        if (false == wpjobportaldb::query($query)) {
            return WPJOBPORTAL_ORDER_UP_ERROR;
        }
        return WPJOBPORTAL_ORDER_UP;
    }

    function fieldOrderingDown($wpjobportal_field_id) {
        if (is_numeric($wpjobportal_field_id) == false)
            return false;

        $query = "UPDATE " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering AS f1, " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering AS f2
                    SET f1.ordering = f1.ordering - 1
                    WHERE f1.ordering = f2.ordering + 1
                    AND f1.fieldfor = f2.fieldfor
                    AND f2.id = " . esc_sql($wpjobportal_field_id);

        if (false == wpjobportaldb::query($query)) {
            return WPJOBPORTAL_ORDER_DOWN_ERROR;
        }

        $query = " UPDATE " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering
                    SET ordering = ordering + 1
                    WHERE id = " . esc_sql($wpjobportal_field_id);

        if (false == wpjobportaldb::query($query)) {
            return WPJOBPORTAL_ORDER_DOWN_ERROR;
        }
        return WPJOBPORTAL_ORDER_DOWN;
    }*/

    function storeUserField($wpjobportal_data) {
        if (empty($wpjobportal_data)) {
            return false;
        }
        if (!is_numeric($wpjobportal_data['fieldfor'])) {
            return false;
        }

        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('fieldsordering');
        if ($wpjobportal_data['isuserfield'] == 1) {
            // value to add as field ordering
            if ($wpjobportal_data['id'] == '') { // only for new
                $query = "SELECT max(ordering) FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering WHERE fieldfor = " . esc_sql($wpjobportal_data['fieldfor']);
                $wpjobportal_var = wpjobportaldb::get_var($query);
                $wpjobportal_data['ordering'] = $wpjobportal_var + 1;
                // search ordering code //
                $query = "SELECT max(ordering) FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering WHERE fieldfor = " . esc_sql($wpjobportal_data['fieldfor']);
                $wpjobportal_var = wpjobportaldb::get_var($query);
                $wpjobportal_data['search_ordering'] = $wpjobportal_var + 1;

                $wpjobportal_data['cannotsearch'] = 0;
                $query = "SELECT max(id) FROM `".wpjobportal::$_db->prefix."wj_portal_fieldsordering` ";
                $wpjobportal_maxid = wpjobportaldb::get_var($query);
                $wpjobportal_maxid++;
                $wpjobportal_data['field'] = 'ufield_'.$wpjobportal_maxid;
            }
            $wpjobportal_data['isvisitorpublished'] = $wpjobportal_data['published'];
            if(isset($wpjobportal_data['search_user']))
                $wpjobportal_data['search_visitor'] = $wpjobportal_data['search_user'];
            $params = array();
            //code for depandetn field
            /*if (isset($wpjobportal_data['userfieldtype']) && $wpjobportal_data['userfieldtype'] == 'depandant_field') {
                if ($wpjobportal_data['id'] != '') {
                    //to handle edit case of depandat field
                    $wpjobportal_data['arraynames'] = $wpjobportal_data['arraynames2'];
                }
                $flagvar = $this->updateParentField($wpjobportal_data['parentfield'], $wpjobportal_data['field'], $wpjobportal_data['fieldfor']);
                if ($flagvar == false) {
                    return WPJOBPORTAL_SAVE_ERROR;
                }
                if (!empty($wpjobportal_data['arraynames'])) {
                    $wpjobportal_valarrays = wpjobportalphplib::wpJP_explode(',', $wpjobportal_data['arraynames']);
                    foreach ($wpjobportal_valarrays as $wpjobportal_key => $wpjobportal_value) {
                        $wpjobportal_keyvalue = $wpjobportal_value;
                        $wpjobportal_value = wpjobportalphplib::wpJP_str_replace(' ','__',$wpjobportal_value);
                        $wpjobportal_value = wpjobportalphplib::wpJP_str_replace('.','___',$wpjobportal_value);
                        if ( isset($wpjobportal_data[$wpjobportal_value]) && $wpjobportal_data[$wpjobportal_value] != null) {
                            $params[$wpjobportal_keyvalue] = array_filter($wpjobportal_data[$wpjobportal_value]);
                        }
                    }
                }
            }*/

            /*if (!empty($wpjobportal_data['values'])) {
                foreach ($wpjobportal_data['values'] as $wpjobportal_key => $wpjobportal_value) {
                    if ($wpjobportal_value != null) {
                        $params[] = wpjobportalphplib::wpJP_trim($wpjobportal_value);
                    }
                }
            }*/
            $wpjobportal_options = wpjobportalphplib::wpJP_trim($wpjobportal_data['options']);
            if(!empty($wpjobportal_options)){
                $wpjobportal_options = wpjobportalphplib::wpJP_preg_split('/\s*(\r\n|\n|\r)\s*/', $wpjobportal_options);
                foreach($wpjobportal_options as $wpjobportal_value){
                    $params[] = $wpjobportal_value;
                }
            }
            //$params_string = wp_json_encode($params);
			$params_string = wp_json_encode($params, JSON_UNESCAPED_UNICODE);
            $wpjobportal_data['userfieldparams'] = $params_string;

        }
        if($wpjobportal_data['fieldfor'] == 3 && (isset($wpjobportal_data['section']) &&  $wpjobportal_data['section'] != 1 )){
            $wpjobportal_data['cannotshowonlisting'] = 1;
        }
        if ($wpjobportal_data['id'] == '') { // only for new
            if($wpjobportal_data['fieldfor'] == 3 && (isset($wpjobportal_data['userfieldtype']) &&  $wpjobportal_data['userfieldtype'] == 'resumesection' )){
                $wpjobportal_data['is_section_headline'] = 1; // to define current field as section for resume
                // to specify section number that can be used to add fields to this section
                // fetching all unique sections ( section field is varchar so have to handle max value here in php code)
                $query = "SELECT section FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering WHERE fieldfor = 3 GROUP BY section ";
                $wpjobportal_section_values = wpjobportaldb::get_results($query);
                $wpjobportal_section_value = 0;
                foreach ($wpjobportal_section_values as $wpjobportal_key => $wpjobportal_section) { // loop over all sections to get max section value and set it as $wpjobportal_section_value to generate value for new section
                    if($wpjobportal_section->section > $wpjobportal_section_value){
                        $wpjobportal_section_value = $wpjobportal_section->section;
                    }
                }
                $wpjobportal_data['section'] = $wpjobportal_section_value + 1;// plus 1 to exsisting max section value
                $wpjobportal_data['isuserfield'] = 1;
            }
        }

        // disable listing and search for upload field
        if ( (isset($wpjobportal_data['userfieldtype']) && $wpjobportal_data['userfieldtype'] == 'file') || (isset($wpjobportal_data['user_field_type']) && $wpjobportal_data['user_field_type'] == 'file')  ) {
            $wpjobportal_data['showonlisting'] = 0;
            $wpjobportal_data['cannotshowonlisting'] = 1;
            $wpjobportal_data['search_user'] = 0;
            $wpjobportal_data['search_visitor'] = 0;
            $wpjobportal_data['cannotsearch'] = 1;
            $wpjobportal_data['section'] = 1;// file field can only be in main section
        }
        // visible field code
        $wpjobportal_fieldname = $wpjobportal_data['field'];
        // to make sure that edit case works ok.(disabled fields do not submit with form. section field is disabled in edit case)
        if($wpjobportal_data['fieldfor'] == 3){
           if(!isset($wpjobportal_data['section']) && isset($wpjobportal_data['section_value'])){
                $wpjobportal_data['section'] = $wpjobportal_data['section_value'];
           }elseif(empty($wpjobportal_data['section']) && isset($wpjobportal_data['section_value'])){ //  log error fix
                $wpjobportal_data['section'] = $wpjobportal_data['section_value'];
           }

        }

        // to make sure this features is only for resume main sections and custom sections.(job and company are included)
        if($wpjobportal_data['fieldfor'] != 3 || (isset($wpjobportal_data['section']) &&  ($wpjobportal_data['section'] == 1 ||  $wpjobportal_data['section'] > 8) ) ){
            if (isset($wpjobportal_data['visibleParent']) && $wpjobportal_data['visibleParent'] != '' && is_numeric($wpjobportal_data['visibleParent'])  && isset($wpjobportal_data['visibleValue']) && $wpjobportal_data['visibleValue'] != '' && isset($wpjobportal_data['visibleCondition']) && $wpjobportal_data['visibleCondition'] != ''){
                $wpjobportal_visible['visibleParentField'] = $wpjobportal_fieldname;
                $wpjobportal_visible['visibleParent'] = $wpjobportal_data['visibleParent'];
                $wpjobportal_visible['visibleCondition'] = $wpjobportal_data['visibleCondition'];
                $wpjobportal_visible['visibleValue'] = $wpjobportal_data['visibleValue'];
                $wpjobportal_visible_array = array_map(array($this,'sanitize_custom_field'), $wpjobportal_visible);
                $wpjobportal_data['visibleparams'] = wp_json_encode($wpjobportal_visible_array);
                //$wpjobportal_data['required'] = 0;

                $query = "SELECT visible_field FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering WHERE id = " . esc_sql($wpjobportal_data['visibleParent']);
                $old_fieldname = wpjobportal::$_db->get_var($query);
                $wpjobportal_new_fieldname = $wpjobportal_fieldname;
                if ($wpjobportal_data['id'] != '') {
                    $query = "SELECT id,visible_field FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering WHERE visible_field  LIKE '%".esc_sql($wpjobportal_fieldname)."%'";
                    $query_run = wpjobportal::$_db->get_row($query);
                    if (isset($query_run) && !empty($query_run) && is_numeric($query_run->id)) {
                        $query_fieldname = $query_run->visible_field;
                        $query_fieldname =  wpjobportalphplib::wpJP_str_replace(','.$wpjobportal_fieldname, '', $query_fieldname);
                        $query_fieldname =  wpjobportalphplib::wpJP_str_replace($wpjobportal_fieldname, '', $query_fieldname);
                        $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_fieldsordering` SET visible_field = '" . esc_sql($query_fieldname) . "' WHERE id = " . esc_sql($query_run->id);
                        wpjobportal::$_db->query($query);
                    }

                    $old_fieldname =  wpjobportalphplib::wpJP_str_replace(','.$wpjobportal_fieldname, '', $old_fieldname);
                    $old_fieldname =  wpjobportalphplib::wpJP_str_replace($wpjobportal_fieldname, '', $old_fieldname);
                }
                if (isset($old_fieldname) && $old_fieldname != '') {
                    $wpjobportal_new_fieldname = $old_fieldname.','.$wpjobportal_new_fieldname;
                }
                // update value
                if(is_numeric($wpjobportal_data['visibleParent'])){
                    $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_fieldsordering` SET visible_field = '" . esc_sql($wpjobportal_new_fieldname) . "'
                    WHERE id = " . esc_sql($wpjobportal_data['visibleParent']);
                    wpjobportal::$_db->query($query);
                    if (wpjobportal::$_db->last_error != null) {

                        WPJOBPORTALincluder::getJSModel('systemerror')->addSystemError();
                    }
                }

            } else if($wpjobportal_data['id'] != '' && is_numeric($wpjobportal_data['id'])){
                $wpjobportal_data['visibleparams'] = '';
                $query = "SELECT visibleparams FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering WHERE id = " . esc_sql($wpjobportal_data['id']);
                $wpjobportal_visibleparams = wpjobportal::$_db->get_var($query);
                if (isset($wpjobportal_visibleparams)) {
                    $wpjobportal_decodedData = json_decode($wpjobportal_visibleparams);
                    $wpjobportal_visibleParent = $wpjobportal_decodedData->visibleParent;
                }else{
                    $wpjobportal_visibleParent = -1;
                }
                if(is_numeric($wpjobportal_visibleParent)){
                    $query = "SELECT visible_field FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering WHERE id = " . esc_sql($wpjobportal_visibleParent);
                    $old_fieldname = wpjobportal::$_db->get_var($query);
                    $wpjobportal_new_fieldname = $wpjobportal_fieldname;
                    $query = "SELECT id,visible_field FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering WHERE visible_field  LIKE '%".esc_sql($wpjobportal_fieldname)."%'";
                    $query_run = wpjobportal::$_db->get_row($query);
                    if (isset($query_run) && !empty($query_run) && is_numeric($query_run->id)) {
                        $query_fieldname = $query_run->visible_field;
                        $query_fieldname =  wpjobportalphplib::wpJP_str_replace(','.$wpjobportal_fieldname, '', $query_fieldname);
                        $query_fieldname =  wpjobportalphplib::wpJP_str_replace($wpjobportal_fieldname, '', $query_fieldname);
                        $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_fieldsordering` SET visible_field = '" . esc_sql($query_fieldname) . "' WHERE id = " . esc_sql($query_run->id);
                        wpjobportal::$_db->query($query);
                    }
                }
            }
        }

        $wpjobportal_data = wpjobportal::wpjobportal_sanitizeData($wpjobportal_data);
        $wpjobportal_data = WPJOBPORTALincluder::getJSmodel('common')->stripslashesFull($wpjobportal_data);// remove slashes with quotes.
        if (!$wpjobportal_row->bind($wpjobportal_data)) {
            return WPJOBPORTAL_SAVE_ERROR;
        }

        if (!$wpjobportal_row->store()) {
            return WPJOBPORTAL_SAVE_ERROR;
        }

        $wpjobportal_stored_id = $wpjobportal_row->id;
        return WPJOBPORTAL_SAVED;
    }

    function updateParentField($parentfield, $wpjobportal_field, $wpjobportal_fieldfor) {
        if(!is_numeric($parentfield)) return false;
        if(!is_numeric($wpjobportal_fieldfor)) return false;
        $query = "UPDATE `".wpjobportal::$_db->prefix."wj_portal_fieldsordering` SET depandant_field = '' WHERE fieldfor = ".esc_sql($wpjobportal_fieldfor)." AND depandant_field = '".esc_sql($parentfield)."'";
        wpjobportal::$_db->query($query);
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('fieldsordering');
        $wpjobportal_row->update(array('id' => $parentfield, 'depandant_field' => $wpjobportal_field));
        return true;
    }

    function storeSearchFieldOrdering($wpjobportal_data) {//
        if (empty($wpjobportal_data)) {
            return false;
        }
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('fieldsordering');
        $wpjobportal_data = wpjobportal::wpjobportal_sanitizeData($wpjobportal_data);
        if (!$wpjobportal_row->bind($wpjobportal_data)) {
            return WPJOBPORTAL_SAVE_ERROR;
        }

        if (!$wpjobportal_row->store()) {
            return WPJOBPORTAL_SAVE_ERROR;
        }

        $wpjobportal_stored_id = $wpjobportal_row->id;
        return WPJOBPORTAL_SAVED;
    }

    function storeSearchFieldOrderingByForm($wpjobportal_data) {//
        if (empty($wpjobportal_data)) {
            return false;
        }
        wpjobportalphplib::wpJP_parse_str($wpjobportal_data['fields_ordering_new'],$sorted_array);
        $sorted_array = reset($sorted_array);
        if(!empty($sorted_array)){
            $wpjobportal_row = WPJOBPORTALincluder::getJSTable('fieldsordering');
            for ($wpjobportal_i=0; $wpjobportal_i < count($sorted_array) ; $wpjobportal_i++) {
                $wpjobportal_row->update(array('id' => $sorted_array[$wpjobportal_i], 'search_ordering' => 1 + $wpjobportal_i));
                //$wpjobportal_row->update(array('id' => $sorted_array[$wpjobportal_i], 'search_ordering' => 1 + $wpjobportal_i));
            }
        }
        return WPJOBPORTAL_SAVED;
    }

    function getFieldsForComboByFieldFor() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'get-fields-for-combo-by-field-for') ) {
            die( 'Security check Failed' );
        }
        $wpjobportal_fieldfor = WPJOBPORTALrequest::getVar('fieldfor');
        $parentfield = WPJOBPORTALrequest::getVar('parentfield');
        if(!is_numeric($wpjobportal_fieldfor)) return false;
        $wherequery = '';
        if($parentfield){
            $query = "SELECT id FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering WHERE fieldfor = $wpjobportal_fieldfor AND (userfieldtype = 'radio' OR userfieldtype = 'combo' OR userfieldtype = 'depandant_field') AND depandant_field = '" . esc_sql($parentfield) . "' ";
            $parent = wpjobportaldb::get_var($query);
            $wherequery = ' OR id = '.esc_sql($parent);
        }else{
            $parent = '';
        }
        $query = "SELECT fieldtitle AS text ,id FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering WHERE fieldfor = " . esc_sql($wpjobportal_fieldfor) . " AND (userfieldtype = 'radio' OR userfieldtype = 'combo' OR userfieldtype = 'depandant_field') && ( depandant_field = '' ".esc_sql($wherequery)." ) ";
        $wpjobportal_data = wpjobportaldb::get_results($query);
        $wpjobportal_jsFunction = 'getDataOfSelectedField();';
        $wpjobportal_html = WPJOBPORTALformfield::select('parentfield', $wpjobportal_data, $parent, esc_html(__('Select','wp-job-portal')) .' '. esc_html(__('Parent Field', 'wp-job-portal')), array('onchange' => $wpjobportal_jsFunction, 'class' => 'inputbox one'));
        $wpjobportal_data = wp_json_encode($wpjobportal_html);
        return $wpjobportal_data;
    }

    function getFieldsForComboBySection() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'get-fields-for-combo-by-section') ) {
            die( 'Security check Failed' );
        }
        $wpjobportal_sectionfor = WPJOBPORTALrequest::getVar('sectionfor');
        if(!is_numeric($wpjobportal_sectionfor)) return false;

        $query = "SELECT fieldtitle AS text ,id FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering WHERE fieldfor = 3 AND userfieldtype = 'combo' AND section = ".esc_sql($wpjobportal_sectionfor);
        $wpjobportal_data = wpjobportaldb::get_results($query);
        $wpjobportal_jsFunction = '';

        $wpjobportal_html = WPJOBPORTALformfield::select('visibleParent', $wpjobportal_data,'', esc_html(__('Select Parent', 'wp-job-portal')), array('class' => 'inputbox wpjobportal-form-select-field wpjobportal-form-input-field-visible', 'onchange' => 'getChildForVisibleCombobox(this.value);'));
        $wpjobportal_data = wp_json_encode($wpjobportal_html);
        return $wpjobportal_data;
    }


    function getSectionToFillValues() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'get-section-to-fill-values') ) {
            die( 'Security check Failed' );
        }
        $wpjobportal_field = WPJOBPORTALrequest::getVar('pfield');
        if(!is_numeric($wpjobportal_field)){
            return false;
        }
        $query = "SELECT userfieldparams FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering WHERE id = ".esc_sql($wpjobportal_field);
        $wpjobportal_data = wpjobportaldb::get_var($query);
        $wpjobportal_datas = json_decode($wpjobportal_data);
        $wpjobportal_html = '';
        $wpjobportal_fieldsvar = '';
        $wpjobportal_comma = '';
        foreach ($wpjobportal_datas as $wpjobportal_data) {
            if(is_array($wpjobportal_data)){
                for ($wpjobportal_i = 0; $wpjobportal_i < count($wpjobportal_data); $wpjobportal_i++) {
                    $wpjobportal_fieldsvar .= $wpjobportal_comma . "$wpjobportal_data[$wpjobportal_i]";
                    $wpjobportal_textvar = $wpjobportal_data[$wpjobportal_i];
                    $wpjobportal_textvar = wpjobportalphplib::wpJP_str_replace(' ','__',$wpjobportal_textvar);
                    $wpjobportal_textvar = wpjobportalphplib::wpJP_str_replace('.','___',$wpjobportal_textvar);
                    $wpjobportal_divid = $wpjobportal_textvar;
                    $wpjobportal_textvar = $wpjobportal_textvar."[]";
                    $wpjobportal_html .= "<div class='js-field-wrapper js-row no-margin'>";
                    $wpjobportal_html .= "<div class='js-field-title js-col-lg-3 js-col-md-3 no-padding'>" . $wpjobportal_data[$wpjobportal_i] . "</div>";
                    $wpjobportal_html .= "<div class='js-col-lg-9 js-col-md-9 no-padding combo-options-fields' id='" . $wpjobportal_divid . "'>
                                    <span class='input-field-wrapper'>
                                        " . WPJOBPORTALformfield::text($wpjobportal_textvar, '', array('class' => 'inputbox one user-field')) . "
                                        <img class='input-field-remove-img' src='" . esc_url(WPJOBPORTAL_PLUGIN_URL) . "includes/images/remove.png' />
                                    </span>
                                    <input type='button' id='depandant-field-button' onClick='getNextField(\"" . $wpjobportal_divid . "\",this);'  value='Add More' />
                                </div>";
                    $wpjobportal_html .= "</div>";
                    $wpjobportal_comma = ',';
                }
            }else{
                $wpjobportal_fieldsvar .= $wpjobportal_comma . $wpjobportal_data;
                $wpjobportal_textvar = $wpjobportal_data;
                $wpjobportal_textvar = wpjobportalphplib::wpJP_str_replace(' ','__',$wpjobportal_data);
                $wpjobportal_textvar = wpjobportalphplib::wpJP_str_replace('.','___',$wpjobportal_data);
                $wpjobportal_divid = $wpjobportal_textvar;
                $wpjobportal_textvar = $wpjobportal_textvar."[]";
                $wpjobportal_html .= "<div class='js-field-wrapper js-row no-margin'>";
                $wpjobportal_html .= "<div class='js-field-title js-col-lg-3 js-col-md-3 no-padding'>" . $wpjobportal_data . "</div>";
                $wpjobportal_html .= "<div class='js-col-lg-9 js-col-md-9 no-padding combo-options-fields' id='" . $wpjobportal_divid . "'>
                                <span class='input-field-wrapper'>
                                    " . WPJOBPORTALformfield::text($wpjobportal_textvar, '', array('class' => 'inputbox one user-field')) . "
                                    <img class='input-field-remove-img' src='" . esc_url(WPJOBPORTAL_PLUGIN_URL) . "includes/images/remove.png' />
                                </span>
                                <input type='button' id='depandant-field-button' onClick='getNextField(\"" . $wpjobportal_divid . "\",this);'  value='Add More' />
                            </div>";
                $wpjobportal_html .= "</div>";
                $wpjobportal_comma = ',';
            }
        }
        $wpjobportal_html .= " <input type='hidden' name='arraynames' value='" . $wpjobportal_fieldsvar . "' />";
        $wpjobportal_html = wp_json_encode($wpjobportal_html);
        return $wpjobportal_html;
    }

    /*function getOptionsForFieldEdit() {
        $wpjobportal_field = WPJOBPORTALrequest::getVar('field');
        $wpjobportal_yesno = array(
            (object) array('id' => 1, 'text' => esc_html(__('Yes', 'wp-job-portal'))),
            (object) array('id' => 0, 'text' => esc_html(__('No', 'wp-job-portal'))));

        if(!is_numeric($wpjobportal_field)) return false;
        $query = "SELECT * FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering WHERE id=" . esc_sql($wpjobportal_field);
        $wpjobportal_data = wpjobportaldb::get_row($query);

        $wpjobportal_html = '<span class="popup-top">
                    <span id="popup_title" >
                    ' . esc_html(__("Edit Field", "wp-job-portal")) . '
                    </span>
                    <img id="popup_cross" alt="popup cross" onClick="closePopup();" src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/popup-close.png">
                </span>';
        $wpjobportal_html .= '<form id="wpjobportal-form" class="popup-field-from" method="post" action="' . esc_url_raw(admin_url("admin.php?page=wpjobportal_fieldordering&task=saveuserfield")) . '">';
        $wpjobportal_html .= '<div class="popup-field-wrapper">
                    <div class="popup-field-title">' . esc_html(__('Field Title', 'wp-job-portal')) . '<font class="required-notifier">*</font></div>
                    <div class="popup-field-obj">' . WPJOBPORTALformfield::text('fieldtitle', isset($wpjobportal_data->fieldtitle) ? $wpjobportal_data->fieldtitle : 'text', '', array('class' => 'inputbox one', 'data-validation' => 'required')) . '</div>
                </div>';
        if ($wpjobportal_data->cannotunpublish == 0) {
            $wpjobportal_html .= '<div class="popup-field-wrapper">
                        <div class="popup-field-title">' . esc_html(__('User Published', 'wp-job-portal')) . '</div>
                        <div class="popup-field-obj">' . WPJOBPORTALformfield::select('published', $wpjobportal_yesno, isset($wpjobportal_data->published) ? $wpjobportal_data->published : 0, '', array('class' => 'inputbox one', 'data-validation' => 'required')) . '</div>
                    </div>';
            $wpjobportal_html .= '<div class="popup-field-wrapper">
                        <div class="popup-field-title">' . esc_html(__('Visitor published', 'wp-job-portal')) . '</div>
                        <div class="popup-field-obj">' . WPJOBPORTALformfield::select('isvisitorpublished', $wpjobportal_yesno, isset($wpjobportal_data->isvisitorpublished) ? $wpjobportal_data->isvisitorpublished : 0, '', array('class' => 'inputbox one', 'data-validation' => 'required')) . '</div>
                    </div>';

            $wpjobportal_html .= '<div class="popup-field-wrapper">
                    <div class="popup-field-title">' . esc_html(__('Required', 'wp-job-portal')) . '</div>
                    <div class="popup-field-obj">' . WPJOBPORTALformfield::select('required', $wpjobportal_yesno, isset($wpjobportal_data->required) ? $wpjobportal_data->required : 0, '', array('class' => 'inputbox one', 'data-validation' => 'required')) . '</div>
                </div>';
        }

        if ($wpjobportal_data->cannotsearch == 0) {
            $wpjobportal_html .= '<div class="popup-field-wrapper">
                        <div class="popup-field-title">' . esc_html(__('User Search', 'wp-job-portal')) . '</div>
                        <div class="popup-field-obj">' . WPJOBPORTALformfield::select('search_user', $wpjobportal_yesno, isset($wpjobportal_data->search_user) ? $wpjobportal_data->search_user : 0, '', array('class' => 'inputbox one', 'data-validation' => 'required')) . '</div>
                    </div>';
            $wpjobportal_html .= '<div class="popup-field-wrapper">
                        <div class="popup-field-title">' . esc_html(__('Visitor Search', 'wp-job-portal')) . '</div>
                        <div class="popup-field-obj">' . WPJOBPORTALformfield::select('search_visitor', $wpjobportal_yesno, isset($wpjobportal_data->search_visitor) ? $wpjobportal_data->search_visitor : 0, '', array('class' => 'inputbox one', 'data-validation' => 'required')) . '</div>
                    </div>';
        }
        $wpjobportal_showonlisting = true;
        if($wpjobportal_data->fieldfor == 3 && $wpjobportal_data->section != 1 ){
            $wpjobportal_showonlisting = false;
        }
        if (($wpjobportal_data->isuserfield == 1 || $wpjobportal_data->cannotshowonlisting == 0) && $wpjobportal_showonlisting == true) {
            $wpjobportal_html .= '<div class="popup-field-wrapper">
                        <div class="popup-field-title">' . esc_html(__('Show On Listing', 'wp-job-portal')) . '</div>
                        <div class="popup-field-obj">' . WPJOBPORTALformfield::select('showonlisting', $wpjobportal_yesno, isset($wpjobportal_data->showonlisting) ? $wpjobportal_data->showonlisting : 0, '', array('class' => 'inputbox one', 'data-validation' => 'required')) . '</div>
                    </div>';
        }
        $wpjobportal_html .= WPJOBPORTALformfield::hidden('form_request', 'wpjobportal');
        $wpjobportal_html .= WPJOBPORTALformfield::hidden('id', $wpjobportal_data->id);
        $wpjobportal_html .= WPJOBPORTALformfield::hidden('isuserfield', $wpjobportal_data->isuserfield);
        $wpjobportal_html .= WPJOBPORTALformfield::hidden('fieldfor', $wpjobportal_data->fieldfor);
        $wpjobportal_html .='<div class="js-submit-container js-col-lg-10 js-col-md-10 js-col-md-offset-1 js-col-md-offset-1">
                    ' . WPJOBPORTALformfield::submitbutton('save', esc_html(__('Save', 'wp-job-portal')), array('class' => 'button'));
        if ($wpjobportal_data->isuserfield == 1) {
            $wpjobportal_html .= '<a id="user-field-anchor" href="'.esc_url_raw(admin_url('admin.php?page=wpjobportal_fieldordering&wpjobportallt=formuserfield&wpjobportalid=' . esc_attr($wpjobportal_data->id) . '&ff='.esc_attr($wpjobportal_data->fieldfor))).'"> ' . esc_html(__('Advanced', 'wp-job-portal')) . ' </a>';
        }

        $wpjobportal_html .='</div>
            </form>';
        return wp_json_encode($wpjobportal_html);
    }*/

    function deleteUserField($wpjobportal_id, $wpjobportal_is_section_headline=0){
        if (!is_numeric($wpjobportal_id))
           return false;
        $query = "SELECT field,fieldfor,section FROM `".wpjobportal::$_db->prefix."wj_portal_fieldsordering` WHERE id = " . esc_sql($wpjobportal_id);
        $wpjobportal_result = wpjobportaldb::get_row($query);
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('fieldsordering');
        if ($this->userFieldCanDelete($wpjobportal_result) == true) {
            if (!$wpjobportal_row->delete($wpjobportal_id)) {
                return WPJOBPORTAL_DELETE_ERROR;
            }else{
                // delete fields of custom section on deleting section
                if($wpjobportal_is_section_headline == 1){
                    if( is_numeric($wpjobportal_result->section) && $wpjobportal_result->section > 8){// making sure this code only executes for custom sections
                        $query = "SELECT id FROM `".wpjobportal::$_db->prefix."wj_portal_fieldsordering` WHERE section = " . esc_sql($wpjobportal_result->section);
                        $wpjobportal_results = wpjobportaldb::get_results($query);
                        foreach ($wpjobportal_results as $wpjobportal_field) {
                            $wpjobportal_row->delete($wpjobportal_field->id);
                        }
                    }
                }
                return WPJOBPORTAL_DELETED;
            }
        }
        return WPJOBPORTAL_IN_USE;
    }

    function enforceDeleteUserField($wpjobportal_id){
        if (is_numeric($wpjobportal_id) == false)
           return false;
        $query = "SELECT field,fieldfor FROM `".wpjobportal::$_db->prefix."wj_portal_fieldsordering` WHERE id = " . esc_sql($wpjobportal_id);
        $wpjobportal_result = wpjobportaldb::get_row($query);
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('fieldsordering');
        if ($this->userFieldCanDelete($wpjobportal_result) == true) {
            if (!$wpjobportal_row->delete($wpjobportal_id)) {
                return WPJOBPORTAL_DELETE_ERROR;
            }else{
                return WPJOBPORTAL_DELETED;
            }
        }
        return WPJOBPORTAL_IN_USE;
    }

    function userFieldCanDelete($wpjobportal_field) {
        $wpjobportal_fieldname = $wpjobportal_field->field;
        $wpjobportal_fieldfor = $wpjobportal_field->fieldfor;

        if($wpjobportal_fieldfor == 1){//for deleting a company field
            $wpjobportal_table = "companies";
        }elseif($wpjobportal_fieldfor == 2){//for deleting a job field
            $wpjobportal_table = "jobs";
        }elseif($wpjobportal_fieldfor == 3){//for deleting a resume field
            $wpjobportal_table = "resume";
        }elseif($wpjobportal_fieldfor == 4){//for deleting a user field
            $wpjobportal_table = "users";
        }
        $query = ' SELECT
                    ( SELECT COUNT(id) FROM `' . wpjobportal::$_db->prefix . 'wj_portal_'.esc_sql($wpjobportal_table).'` WHERE
                        params LIKE \'%"' . esc_sql($wpjobportal_fieldname) . '":%\'
                    )
                    AS total';
        $wpjobportal_total = wpjobportaldb::get_var($query);
        if ($wpjobportal_total > 0)
            return false;
        else
            return true;
    }

    function getUserfieldsfor($wpjobportal_fieldfor, $wpjobportal_resumesection = null) {
        if (!is_numeric($wpjobportal_fieldfor))
            return false;
        if (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
            $wpjobportal_published = ' isvisitorpublished = 1 ';
        } else {
            $wpjobportal_published = ' published = 1 ';
        }
        if ($wpjobportal_resumesection != null) {
            $wpjobportal_published .= " AND section =". esc_sql($wpjobportal_resumesection);
        }
        $query = "SELECT field,userfieldparams,userfieldtype FROM `" . wpjobportal::$_db->prefix . "wj_portal_fieldsordering` WHERE fieldfor = " . esc_sql($wpjobportal_fieldfor) . " AND isuserfield = 1 AND " . $wpjobportal_published;
        $wpjobportal_fields = wpjobportaldb::get_results($query);
        return $wpjobportal_fields;
    }

    function getUserFieldbyId($wpjobportal_id, $wpjobportal_fieldfor) {
        if ($wpjobportal_id) {
            if (is_numeric($wpjobportal_id) == false)
                return false;
            if (is_numeric($wpjobportal_fieldfor) == false)
                return false;
            $query = "SELECT * FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering WHERE id = " . esc_sql($wpjobportal_id);
            wpjobportal::$_data[0]['userfield'] = wpjobportaldb::get_row($query);
          if(isset(wpjobportal::$_data[0]['userfield']->userfieldparams) && !empty(wpjobportal::$_data[0]['userfield']->userfieldparams)){
              $params = wpjobportal::$_data[0]['userfield']->userfieldparams;
              wpjobportal::$_data[0]['userfieldparams'] = !empty($params) ? json_decode($params, true) : '';
          }else{
            wpjobportal::$_data[0]['userfieldparams'] = '';
          }
        }
        wpjobportal::$_data[0]['fieldfor'] = $wpjobportal_fieldfor;
        if(isset(wpjobportal::$_data[0]['userfield']->visibleparams) && wpjobportal::$_data[0]['userfield']->visibleparams != ''){
            $wpjobportal_visibleparams = wpjobportal::$_data[0]['userfield']->visibleparams;
            wpjobportal::$_data[0]['visibleparams'] = !empty($wpjobportal_visibleparams) ? json_decode($wpjobportal_visibleparams, true) : '';

            $wpjobportal_visibleparams = json_decode($wpjobportal_visibleparams, true);
            $wpjobportal_fieldtypes = array();
            if(isset($wpjobportal_visibleparams['visibleParent']) && is_numeric($wpjobportal_visibleparams['visibleParent'])){
                $query = "SELECT userfieldparams AS params FROM `" . wpjobportal::$_db->prefix . "wj_portal_fieldsordering` WHERE id = " . esc_sql($wpjobportal_visibleparams['visibleParent']);
                $wpjobportal_options = wpjobportal::$_db->get_var($query);
                $wpjobportal_options = json_decode($wpjobportal_options);
                foreach ($wpjobportal_options as $wpjobportal_key => $wpjobportal_option) {
                    $wpjobportal_fieldtypes[$wpjobportal_key] = (object) array('id' => $wpjobportal_option, 'text' => $wpjobportal_option);
                }
            }
            wpjobportal::$_data[0]['visibleValue'] = $wpjobportal_fieldtypes;
        }
        return;
    }

    function makeDependentComboFiledForResume($wpjobportal_val,$childfield,$type,$wpjobportal_section,$wpjobportal_sectionid,$wpjobportal_themecall){

        $query = "SELECT field,depandant_field,userfieldparams,fieldtitle, required FROM `".wpjobportal::$_db->prefix."wj_portal_fieldsordering` WHERE field = '".esc_sql($childfield)."'";
        $wpjobportal_data = wpjobportal::$_db->get_row($query);
        $wpjobportal_decoded_data = json_decode($wpjobportal_data->userfieldparams);
        $wpjobportal_comboOptions = array();
        $wpjobportal_themeclass=($wpjobportal_themecall)?getJobManagerThemeClass('select'):"";

        $flag = 0;
        foreach ($wpjobportal_decoded_data as $wpjobportal_key => $wpjobportal_value) {
            if($wpjobportal_key==$wpjobportal_val){
               for ($wpjobportal_i=0; $wpjobportal_i <count($wpjobportal_value) ; $wpjobportal_i++) {
                   $wpjobportal_comboOptions[] = (object)array('id' => $wpjobportal_value[$wpjobportal_i], 'text' => $wpjobportal_value[$wpjobportal_i]);
                   $flag = 1;
               }
            }
        }
        if($wpjobportal_themecall == 1){
            $wpjobportal_theme_string = ' ,'.$wpjobportal_themecall;
        }else{
            $wpjobportal_theme_string = '';
        }

        $wpjobportal_jsFunction = '';
        if ($wpjobportal_data->depandant_field != null) {
            $wpjobportal_jsFunction = "getDataForDepandantFieldResume('" . $wpjobportal_data->field . "','" . $wpjobportal_data->depandant_field . "','" . $type . "','" . $wpjobportal_section . "','" . $wpjobportal_sectionid . "'".$wpjobportal_theme_string.");";
        }
        $cssclass="";
        if($wpjobportal_data->required == 1){
            $cssclass = 'required';
        }
        //end
        $wpjobportal_extraattr = array('data-validation' => $cssclass, 'class' => "inputbox one $cssclass $wpjobportal_themeclass");
        if(""!=$wpjobportal_jsFunction){
            $wpjobportal_extraattr['onchange']=$wpjobportal_jsFunction;
        }
        // handleformresume
        if($wpjobportal_section AND $wpjobportal_section != 1){
            if($wpjobportal_ishidden){
                if ($wpjobportal_required == 1) {
                    $wpjobportal_extraattr['data-myrequired'] = $cssclass;
                    $wpjobportal_extraattr['class'] = "inputbox one";
                }
            }
        }
        $wpjobportal_textvar =  ($flag == 1) ?  esc_html(__('Select', 'wp-job-portal')).' '.$wpjobportal_data->fieldtitle : '';
        $wpjobportal_html = wpjobportal::$_wpjpcustomfield->selectResume($childfield, $wpjobportal_comboOptions, '', $wpjobportal_textvar, $wpjobportal_extraattr , null,$wpjobportal_section , $wpjobportal_sectionid);
        $phtml = wp_json_encode($wpjobportal_html);
        return $phtml;
    }
    function DataForDepandantFieldResume(){
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('js_nonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wp-job-portal-nonce') ) {
            die( 'Security check Failed' );
        }
        $wpjobportal_val = WPJOBPORTALrequest::getVar('fvalue');
        $childfield = WPJOBPORTALrequest::getVar('child');
        $wpjobportal_section = WPJOBPORTALrequest::getVar('section');
        $wpjobportal_sectionid = WPJOBPORTALrequest::getVar('sectionid');
        $type = WPJOBPORTALrequest::getVar('type');
        $wpjobportal_themecall = WPJOBPORTALrequest::getVar('themecall');
        switch ($type) {
            case 1: //select type dependent combo
            case 2: //radio type dependent combo
                return $this->makeDependentComboFiledForResume($wpjobportal_val,$childfield,$type,$wpjobportal_section,$wpjobportal_sectionid,$wpjobportal_themecall);
            break;
        }
        return;
    }

    function DataForDepandantField(){
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('js_nonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wp-job-portal-nonce') ) {
            die( 'Security check Failed' );
        }
        $wpjobportal_val = WPJOBPORTALrequest::getVar('fvalue');
        $childfield = WPJOBPORTALrequest::getVar('child');
        $wpjobportal_themecall = WPJOBPORTALrequest::getVar('themecall');
        $wpjobportal_themeclass="";
        if($wpjobportal_themecall){
            $wpjobportal_theme_string = ','. $wpjobportal_themecall ;
            if(function_exists("getJobManagerThemeClass")){
                $wpjobportal_themeclass=getJobManagerThemeClass("select");
            }
        }else{
            $wpjobportal_theme_string = '';
        }
        $query = "SELECT userfieldparams, fieldtitle, required, depandant_field,field  FROM `".wpjobportal::$_db->prefix."wj_portal_fieldsordering` WHERE field = '".esc_sql($childfield)."'";
        $wpjobportal_data = wpjobportal::$_db->get_row($query);
        $wpjobportal_decoded_data = json_decode($wpjobportal_data->userfieldparams);
        $wpjobportal_comboOptions = array();
        $flag = 0;
        if(!empty($wpjobportal_decoded_data) && $wpjobportal_decoded_data != ''){
            foreach ($wpjobportal_decoded_data as $wpjobportal_key => $wpjobportal_value) {
                if($wpjobportal_key==$wpjobportal_val){
                   for ($wpjobportal_i=0; $wpjobportal_i <count($wpjobportal_value) ; $wpjobportal_i++) {
                       $wpjobportal_comboOptions[] = (object)array('id' => $wpjobportal_value[$wpjobportal_i], 'text' => $wpjobportal_value[$wpjobportal_i]);
                       $flag = 1;
                   }
                }
            }
        }
        $wpjobportal_textvar =  ($flag == 1) ?  esc_html(__('Select', 'wp-job-portal')).' '.$wpjobportal_data->fieldtitle : '';
        $wpjobportal_required = '';
        if($wpjobportal_data->required == 1){
            $wpjobportal_required = 'required';
        }
        $wpjobportal_jsFunction = '';
        if ($wpjobportal_data->depandant_field != null) {
            $wpjobportal_jsFunction = " getDataForDepandantField('" . $wpjobportal_data->field . "','" . $wpjobportal_data->depandant_field . "','1','',''". $wpjobportal_theme_string.");";
        }
        $wpjobportal_html = WPJOBPORTALformfield::select($childfield, $wpjobportal_comboOptions, '',$wpjobportal_textvar, array('data-validation' => $wpjobportal_required,'class' => 'inputbox one '.$wpjobportal_themeclass, 'onchange' => $wpjobportal_jsFunction));
        $phtml = wp_json_encode($wpjobportal_html);
        return $phtml;
    }

    function getFieldTitleByFieldAndFieldfor($wpjobportal_field,$wpjobportal_fieldfor){
        if(!is_numeric($wpjobportal_fieldfor)) return false;
        $query = "SELECT fieldtitle FROM `".wpjobportal::$_db->prefix."wj_portal_fieldsordering` WHERE field = '".esc_sql($wpjobportal_field)."' AND fieldfor = ".esc_sql($wpjobportal_fieldfor);
        $title = wpjobportal::$_db->get_var($query);
        return $title;
    }
    function getMessagekey(){
        $wpjobportal_key = 'fieldordering';if(wpjobportal::$_common->wpjp_isadmin()){$wpjobportal_key = 'admin_'.$wpjobportal_key;}return $wpjobportal_key;
    }

    function getFieldsForListing($wpjobportal_fieldfor){
        if (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
            $wpjobportal_published = ' isvisitorpublished = 1 ';
        } else {
            $wpjobportal_published = ' published = 1 ';
        }

        $query = "SELECT field  FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering WHERE showonlisting = 1 AND " . esc_sql($wpjobportal_published) . " AND fieldfor =" . esc_sql($wpjobportal_fieldfor) ;
        $wpjobportal_data = wpjobportaldb::get_results($query);
        $return_array = array();
        foreach ($wpjobportal_data as $wpjobportal_field) {
            $return_array[$wpjobportal_field->field] = 1;
        }

        return $return_array;
    }

    function getFieldOrderingData($wpjobportal_fieldfor){ // to handle visibilty in case of mininmum fields
        if(!is_numeric($wpjobportal_fieldfor)){
            return false;
        }

        if (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
            $wpjobportal_published = ' isvisitorpublished = 1 ';
        } else {
            $wpjobportal_published = ' published = 1 ';
        }
        $query = "SELECT field,fieldtitle  FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering WHERE " . esc_sql($wpjobportal_published) . " AND fieldfor =" . esc_sql($wpjobportal_fieldfor) ;
        $wpjobportal_data = wpjobportaldb::get_results($query);
        $return_data = array();
        foreach ($wpjobportal_data as $wpjobportal_field) {
            $return_data[$wpjobportal_field->field] = $wpjobportal_field->fieldtitle;
        }
        return $return_data;
    }

    function getFieldOrderingDataForListing($wpjobportal_fieldfor){
        if (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
            $wpjobportal_published = ' isvisitorpublished = 1 ';
        } else {
            $wpjobportal_published = ' published = 1 ';
        }
        if(!is_numeric($wpjobportal_fieldfor)){
            return false;
        }

        $query = "SELECT field,fieldtitle  FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering WHERE showonlisting = 1 AND " . esc_sql($wpjobportal_published) . " AND fieldfor =" . esc_sql($wpjobportal_fieldfor) ;
        $wpjobportal_data = wpjobportaldb::get_results($query);
        $return_data = array();
        foreach ($wpjobportal_data as $wpjobportal_field) {
            $return_data[$wpjobportal_field->field] = $wpjobportal_field->fieldtitle;
        }
        return $return_data;
    }



    // setcookies for search form data
    //search cookies data
    function getSearchFormData(){
        $wpjobportal_jsjp_search_array = array();
        $wpjobportal_jsjp_search_array['title'] = WPJOBPORTALrequest::getVar("title");
        $wpjobportal_jsjp_search_array['ustatus'] = WPJOBPORTALrequest::getVar("ustatus");
        $wpjobportal_jsjp_search_array['vstatus'] = WPJOBPORTALrequest::getVar("vstatus");
        $wpjobportal_jsjp_search_array['required'] = WPJOBPORTALrequest::getVar("required");
        $wpjobportal_jsjp_search_array['search_from_customfield'] = 1;
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
        if($wpjp_search_cookie_data != '' && isset($wpjp_search_cookie_data['search_from_customfield']) && $wpjp_search_cookie_data['search_from_customfield'] == 1){
            $wpjobportal_jsjp_search_array['title'] = $wpjp_search_cookie_data['title'];
            $wpjobportal_jsjp_search_array['ustatus'] = $wpjp_search_cookie_data['ustatus'];
            $wpjobportal_jsjp_search_array['vstatus'] = $wpjp_search_cookie_data['vstatus'];
            $wpjobportal_jsjp_search_array['required'] = $wpjp_search_cookie_data['required'];
        }
        return $wpjobportal_jsjp_search_array;
    }

    function setSearchVariableForSearch($wpjobportal_jsjp_search_array){
        wpjobportal::$_search['customfield']['title'] = isset($wpjobportal_jsjp_search_array['title']) ? $wpjobportal_jsjp_search_array['title'] : '';
        wpjobportal::$_search['customfield']['ustatus'] = isset($wpjobportal_jsjp_search_array['ustatus']) ? $wpjobportal_jsjp_search_array['ustatus'] : '';
        wpjobportal::$_search['customfield']['vstatus'] = isset($wpjobportal_jsjp_search_array['vstatus']) ? $wpjobportal_jsjp_search_array['vstatus'] : '';
        wpjobportal::$_search['customfield']['required'] = isset($wpjobportal_jsjp_search_array['required']) ? $wpjobportal_jsjp_search_array['required'] : '';
    }

    function getFieldsForVisibleCombobox($wpjobportal_fieldfor, $wpjobportal_field='', $cid='',$wpjobportal_section= '') {
        if(!is_numeric($wpjobportal_fieldfor)){
            return false;
        }
        $wherequery = '';
        if(isset($wpjobportal_field) && $wpjobportal_field !='' ){
            $query = "SELECT id FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering WHERE fieldfor = $wpjobportal_fieldfor AND (userfieldtype = 'combo') AND visible_field = '" . esc_sql($wpjobportal_field) . "' ";
            $parent = wpjobportal::$_db->get_var($query);
            if (is_numeric($parent)) {
                $wherequery = ' OR id = '.esc_sql($parent);
            }
        }
        $wherequeryforedit = '';
        if(isset($cid) && $cid !='' && is_numeric($cid)){
            $wherequeryforedit = ' AND id != '.esc_sql($cid);
        }
        // to handle resume section
        if(isset($wpjobportal_section) && $wpjobportal_section !='' && is_numeric($wpjobportal_section)){
            $wherequeryforedit = ' AND section = '.esc_sql($wpjobportal_section);
        }


        $query = "SELECT fieldtitle AS text ,id FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering WHERE fieldfor = ".esc_sql($wpjobportal_fieldfor)." AND userfieldtype = 'combo' ".$wherequeryforedit.$wherequery;
        $wpjobportal_data = wpjobportal::$_db->get_results($query);
        return $wpjobportal_data;
    }

    function getChildForVisibleCombobox() {
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'get-child-for-visible-combobox') ) {
            die( 'Security check Failed' );
        }
        $wpjobportal_perentid = WPJOBPORTALrequest::getVar('val');
        if (!is_numeric($wpjobportal_perentid)){
            return false;
        }

        $query = "SELECT isuserfield, field FROM `" . wpjobportal::$_db->prefix . "wj_portal_fieldsordering` WHERE id = " . esc_sql($wpjobportal_perentid);
        $wpjobportal_fieldType = wpjobportal::$_db->get_row($query);
        if (isset($wpjobportal_fieldType->isuserfield) && $wpjobportal_fieldType->isuserfield == 1) {
            $query = "SELECT userfieldparams AS params FROM `" . wpjobportal::$_db->prefix . "wj_portal_fieldsordering` WHERE id = " . esc_sql($wpjobportal_perentid);
            $wpjobportal_options = wpjobportal::$_db->get_var($query);
            $wpjobportal_options = json_decode($wpjobportal_options);
            foreach ($wpjobportal_options as $wpjobportal_key => $wpjobportal_option) {
                $wpjobportal_fieldtypes[$wpjobportal_key] = (object) array('id' => $wpjobportal_option, 'text' => $wpjobportal_option);
            }
        } else if ($wpjobportal_fieldType->field == 'department') {
            // $query = "SELECT departmentname AS text ,id FROM " . wpjobportal::$_db->prefix . "js_ticket_departments";
            // $wpjobportal_fieldtypes = wpjobportal::$_db->get_results($query);
        }
        $wpjobportal_combobox = false;
        if(!empty($wpjobportal_fieldtypes)){
            $wpjobportal_combobox = WPJOBPORTALformfield::select('visibleValue', $wpjobportal_fieldtypes, isset(wpjobportal::$_data[0]['userfield']->required) ? wpjobportal::$_data[0]['userfield']->required : 0, '', array('class' => 'inputbox one wpjobportal-form-select-field wpjobportal-form-input-field-visible'));
        }
        return wpjobportalphplib::wpJP_htmlentities($wpjobportal_combobox);
    }

    function getDataForVisibleField($wpjobportal_field) {
        $wpjobportal_field = esc_sql($wpjobportal_field);
        $wpjobportal_field_array = wpjobportalphplib::wpJP_str_replace(",", "','", $wpjobportal_field);
        $query = "SELECT visibleparams FROM ". wpjobportal::$_db->prefix ."wj_portal_fieldsordering WHERE  field IN ('". $wpjobportal_field_array ."')";
        $wpjobportal_fields = wpjobportal::$_db->get_results($query);
        $wpjobportal_data = array();
        foreach ($wpjobportal_fields as $wpjobportal_item) {
            if(isset($wpjobportal_item->visibleparams) && $wpjobportal_item->visibleparams != ''){
                $d = json_decode($wpjobportal_item->visibleparams);
                if(isset($d->visibleParentField)){
                    $d->visibleParentField = Self::getChildForVisibleField($d->visibleParentField);
                    $wpjobportal_data[] = $d;
                }
            }
        }
        return $wpjobportal_data;
    }

    static function getChildForVisibleField($wpjobportal_field) {
        $wpjobportal_field = esc_sql($wpjobportal_field);
        $oldField = wpjobportalphplib::wpJP_explode(',',$wpjobportal_field);
        $wpjobportal_newField = $oldField[sizeof($oldField) - 1];
        $query = "SELECT visible_field FROM ". wpjobportal::$_db->prefix ."wj_portal_fieldsordering WHERE  field = '". $wpjobportal_newField ."'";
        $queryRun = wpjobportal::$_db->get_var($query);
        if (isset($queryRun) && $queryRun != '') {
            $wpjobportal_data = wpjobportalphplib::wpJP_explode(',',$queryRun);
            foreach ($wpjobportal_data as $wpjobportal_value) {
                $wpjobportal_field = $wpjobportal_field.','.$wpjobportal_value;
                $wpjobportal_field = Self::getChildForVisibleField($wpjobportal_field);
            }
        }
        return $wpjobportal_field;
    }

    function isFieldRequired(){
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'is-field-required') ) {
            die( 'Security check Failed' );
        }
        $wpjobportal_field = WPJOBPORTALrequest::getVar('field');
        $query = "SELECT required  FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering WHERE  field ='".esc_sql($wpjobportal_field)."'";
        return wpjobportal::$_db->get_var($query);
    }

    function sanitize_custom_field($wpjobportal_arg) {
        if (is_array($wpjobportal_arg)) {
            // foreach($wpjobportal_arg as $wpjobportal_ikey){
            return array_map(array($this,'sanitize_custom_field'), $wpjobportal_arg);
            // }
        }
        return wpjobportalphplib::wpJP_htmlentities($wpjobportal_arg, ENT_QUOTES, 'UTF-8');
    }

    function checkCompanyFieldForJob(){
        $query = "SELECT required  FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering WHERE  field ='company'";
        return wpjobportal::$_db->get_var($query);
    }

}
?>
