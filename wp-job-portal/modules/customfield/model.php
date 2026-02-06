<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALCustomFieldModel {

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
        // $title = WPJOBPORTALrequest::getVar('title');
        // $ustatus = WPJOBPORTALrequest::getVar('ustatus');
        // $vstatus = WPJOBPORTALrequest::getVar('vstatus');
        // $wpjobportal_required = WPJOBPORTALrequest::getVar('required');
        // $wpjobportal_formsearch = WPJOBPORTALrequest::getVar('WPJOBPORTAL_form_search', 'post');
        // if ($wpjobportal_formsearch == 'WPJOBPORTAL_SEARCH') {
        //     $_SESSION['WPJOBPORTAL_SEARCH']['title'] = $title;
        //     $_SESSION['WPJOBPORTAL_SEARCH']['ustatus'] = $ustatus;
        //     $_SESSION['WPJOBPORTAL_SEARCH']['vstatus'] = $vstatus;
        //     $_SESSION['WPJOBPORTAL_SEARCH']['required'] = $wpjobportal_required;
        // }
        // if (WPJOBPORTALrequest::getVar('pagenum', 'get', null) != null) {
        //     $title = (isset($_SESSION['WPJOBPORTAL_SEARCH']['title']) && $_SESSION['WPJOBPORTAL_SEARCH']['title'] != '') ? sanitize_key($_SESSION['WPJOBPORTAL_SEARCH']['title']) : null;
        //     $ustatus = (isset($_SESSION['WPJOBPORTAL_SEARCH']['ustatus']) && $_SESSION['WPJOBPORTAL_SEARCH']['ustatus'] != '') ? sanitize_key($_SESSION['WPJOBPORTAL_SEARCH']['ustatus']) : null;
        //     $vstatus = (isset($_SESSION['WPJOBPORTAL_SEARCH']['vstatus']) && $_SESSION['WPJOBPORTAL_SEARCH']['vstatus'] != '') ? sanitize_key($_SESSION['WPJOBPORTAL_SEARCH']['vstatus']) : null;
        //     $wpjobportal_required = (isset($_SESSION['WPJOBPORTAL_SEARCH']['required']) && $_SESSION['WPJOBPORTAL_SEARCH']['required'] != '') ? sanitize_key($_SESSION['WPJOBPORTAL_SEARCH']['required']) : null;
        // } else if ($wpjobportal_formsearch !== 'WPJOBPORTAL_SEARCH') {
        //     unset($_SESSION['WPJOBPORTAL_SEARCH']);
        // }



        $title = wpjobportal::$_search['search_filter']['title'];
        $ustatus = wpjobportal::$_search['search_filter']['ustatus'];
        $vstatus = wpjobportal::$_search['search_filter']['vstatus'];
        $wpjobportal_required = wpjobportal::$_search['search_filter']['required'];

        $wpjobportal_inquery = '';
        if ($title != null)
            $wpjobportal_inquery .= " AND field.fieldtitle LIKE '%".esc_sql($title)."%'";
        if (is_numeric($ustatus))
            $wpjobportal_inquery .= " AND field.published = ".esc_sql($ustatus);
        if (is_numeric($vstatus))
            $wpjobportal_inquery .= " AND field.isvisitorpublished = ".esc_sql($vstatus);
        if (is_numeric($wpjobportal_required))
            $wpjobportal_inquery .= " AND field.required = ".esc_sql($wpjobportal_required);

        wpjobportal::$_data['filter']['title'] = $title;
        wpjobportal::$_data['filter']['ustatus'] = $ustatus;
        wpjobportal::$_data['filter']['vstatus'] = $vstatus;
        wpjobportal::$_data['filter']['required'] = $wpjobportal_required;

        //Pagination
        $query = "SELECT COUNT(field.id) FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering AS field WHERE field.fieldfor = " . esc_sql($wpjobportal_fieldfor);
        $query .= $wpjobportal_inquery;
        $wpjobportal_total = wpjobportaldb::get_var($query);
        wpjobportal::$_data['total'] = $wpjobportal_total;
        wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($wpjobportal_total);

        //Data
        $query = "SELECT field.*
                    FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering AS field
                    WHERE field.fieldfor = " . esc_sql($wpjobportal_fieldfor);
        $query .= $wpjobportal_inquery;
        $query .= ' ORDER BY';
        $query .= ' field.ordering';
        if ($wpjobportal_fieldfor == 3)
            $query .=' ,field.section';
        $query .=" LIMIT " . WPJOBPORTALpagination::$_offset . "," . WPJOBPORTALpagination::$_limit;
        wpjobportal::$_data[0] = wpjobportaldb::get_results($query);
        return;
    }

    function getSearchFieldsOrdering($wpjobportal_fieldfor) {
        if (is_numeric($wpjobportal_fieldfor) == false)
            return false;
        $wpjobportal_search = WPJOBPORTALrequest::getVar('search','',0);
        $wpjobportal_inquery = '';
            $wpjobportal_inquery .= " AND field.cannotsearch = 0";
        if ($wpjobportal_search == 0){
            $wpjobportal_inquery .= " AND (field.search_user  = 1 OR field.search_visitor = 1 ) ";
        }
        wpjobportal::$_data['filter']['search'] = $wpjobportal_search;
        //Data
        $query = "SELECT field.fieldtitle,field.id,field.search_user,field.search_visitor,field.ordering
                    FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering AS field
                    WHERE field.fieldfor = " . esc_sql($wpjobportal_fieldfor);
        $query .= $wpjobportal_inquery;
        $query .= ' ORDER BY';
        $query .= ' field.ordering';

        wpjobportal::$_data[0] = wpjobportaldb::get_results($query);
        return;
    }

    function getFieldsOrderingforForm($wpjobportal_fieldfor) {
        if (is_numeric($wpjobportal_fieldfor) == false){
            return false;
        }
        $wpjobportal_published = (WPJOBPORTALincluder::getObjectClass('user')->isguest()) ? "isvisitorpublished" : "published";
        $query = "SELECT * FROM `" . wpjobportal::$_db->prefix . "wj_portal_fieldsordering`
        WHERE ".esc_sql($wpjobportal_published)." = 1 AND fieldfor = " . esc_sql($wpjobportal_fieldfor) . " ORDER BY";
        if ($wpjobportal_fieldfor == 3) // for resume it must be order by section and ordering
            $query.=" section , ";
        $query.=" ordering";
        $wpjobportal_fields = array();
       // var_dump($query);
        foreach(wpjobportaldb::get_results($query) as $wpjobportal_field){
            $wpjobportal_field->validation = $wpjobportal_field->required == 1 ? 'required' : '';
            $wpjobportal_fields[$wpjobportal_field->field] = $wpjobportal_field;
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
        $query = "SELECT * FROM `" . wpjobportal::$_db->prefix . "wj_portal_fieldsordering`
                 WHERE cannotsearch = 0 AND  fieldfor = " . esc_sql($wpjobportal_fieldfor) . esc_sql($wpjobportal_published ). " ORDER BY ordering";
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
        $query.=" ordering";
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

        return $return;
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

    /*function visitorFieldsPublishedOrNot($wpjobportal_ids, $wpjobportal_value) {
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
    }*/

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

        if(!is_numeric($wpjobportal_data['fieldfor'])){
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
            $params_string = wp_json_encode($params);
            $wpjobportal_data['userfieldparams'] = $params_string;

        }
        if($wpjobportal_data['fieldfor'] == 3 && $wpjobportal_data['section'] != 1){
            $wpjobportal_data['cannotshowonlisting'] = 1;
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
                $wpjobportal_row->update(array('id' => $sorted_array[$wpjobportal_i], 'ordering' => 1 + $wpjobportal_i));
                //$wpjobportal_row->update(array('id' => $sorted_array[$wpjobportal_i], 'search_ordering' => 1 + $wpjobportal_i));
            }
        }
        return WPJOBPORTAL_SAVED;
    }

    function getFieldsForComboByFieldFor() {
        $wpjobportal_fieldfor = WPJOBPORTALrequest::getVar('fieldfor');
        $parentfield = WPJOBPORTALrequest::getVar('parentfield');
        if(!is_numeric($wpjobportal_fieldfor)) return false;
        $wherequery = '';
        if($parentfield){
            $query = "SELECT id FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering WHERE fieldfor = ".esc_sql($wpjobportal_fieldfor)." AND (userfieldtype = 'radio' OR userfieldtype = 'combo' OR userfieldtype = 'depandant_field') AND depandant_field = '" . esc_sql($parentfield) . "' ";
            $parent = wpjobportaldb::get_var($query);
            $wherequery = ' OR id = '.esc_sql($parent);
        }else{
            $parent = '';
        }
        $query = "SELECT fieldtitle AS text ,id FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering WHERE fieldfor = " . esc_sql($wpjobportal_fieldfor) . " AND (userfieldtype = 'radio' OR userfieldtype = 'combo' OR userfieldtype = 'depandant_field') && ( depandant_field = '' ".$wherequery." ) ";
        $wpjobportal_data = wpjobportaldb::get_results($query);
        $wpjobportal_jsFunction = 'getDataOfSelectedField();';
        $wpjobportal_html = WPJOBPORTALformfield::select('parentfield', $wpjobportal_data, $parent, esc_html(__('Select','wp-job-portal')) .' '. esc_html(__('Parent Field', 'wp-job-portal')), array('onchange' => $wpjobportal_jsFunction, 'class' => 'inputbox one'));
        $wpjobportal_data = wp_json_encode($wpjobportal_html);
        return $wpjobportal_data;
    }

    function getSectionToFillValues() {
        $wpjobportal_field = WPJOBPORTALrequest::getVar('pfield');
        if(!is_numeric($wpjobportal_field)){
            return '';
        }
        $query = "SELECT userfieldparams FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering WHERE id =". esc_sql($wpjobportal_field);
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
                    <img id="popup_cross" alt="'.esc_attr(__('popup close','wp-job-portal')).'" title="'.esc_attr(__('popup close','wp-job-portal')).'" onClick="closePopup();" src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/popup-close.png">
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

    function deleteUserField($wpjobportal_id){
        if (!is_numeric($wpjobportal_id))
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
        if ($wpjobportal_resumesection != null && is_numeric($wpjobportal_resumesection)) {
            $wpjobportal_published .= " AND section = ".esc_sql($wpjobportal_resumesection) ;
        }
        $query = "SELECT field,userfieldparams,userfieldtype FROM `" . wpjobportal::$_db->prefix . "wj_portal_fieldsordering` WHERE fieldfor = " . esc_sql($wpjobportal_fieldfor) . " AND isuserfield = 1 AND " . esc_sql($wpjobportal_published);
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
            $params = wpjobportal::$_data[0]['userfield']->userfieldparams;
            wpjobportal::$_data[0]['userfieldparams'] = !empty($params) ? json_decode($params, True) : '';

            wpjobportal::$_data[0]['fieldfor'] = $wpjobportal_fieldfor;

            $wpjobportal_visibleparams = wpjobportal::$_data[0]['userfield']->visibleparams;
            wpjobportal::$_data[0]['visibleparams'] = !empty($wpjobportal_visibleparams) ? json_decode($wpjobportal_visibleparams, True) : '';

            $wpjobportal_visibleparams = json_decode($wpjobportal_visibleparams, True);
            if(!is_numeric($wpjobportal_visibleparams['visibleParent'])){
                wpjobportal::$_data[0]['visibleValue'] = array();
                return;
            }
            $query = "SELECT userfieldparams AS params FROM `" . wpjobportal::$_db->prefix . "wj_portal_fieldsordering` WHERE id = " . esc_sql($wpjobportal_visibleparams['visibleParent']);
            $wpjobportal_options = wpjobportal::$_db->get_var($query);
            $wpjobportal_options = json_decode($wpjobportal_options);
            foreach ($wpjobportal_options as $wpjobportal_key => $wpjobportal_option) {
                $wpjobportal_fieldtypes[$wpjobportal_key] = (object) array('id' => $wpjobportal_option, 'text' => $wpjobportal_option);
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
        $wpjobportal_html =WPJOBPORTALincluder::getObjectClass('customfields')->selectResume($childfield, $wpjobportal_comboOptions, '', $wpjobportal_textvar, $wpjobportal_extraattr , null,$wpjobportal_section , $wpjobportal_sectionid);
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



    function getFieldsForListing($wpjobportal_fieldfor){
        if (WPJOBPORTALincluder::getObjectClass('user')->isguest()) {
            $wpjobportal_published = ' isvisitorpublished = 1 ';
        } else {
            $wpjobportal_published = ' published = 1 ';
        }

        $return_array = array();
        if(!is_numeric($wpjobportal_fieldfor)){
            return $return_array;
        }

        $query = "SELECT field  FROM " . wpjobportal::$_db->prefix . "wj_portal_fieldsordering WHERE showonlisting = 1 AND " . esc_sql($wpjobportal_published) . " AND fieldfor =" . esc_sql($wpjobportal_fieldfor) ;
        $wpjobportal_data = wpjobportaldb::get_results($query);
        foreach ($wpjobportal_data as $wpjobportal_field) {
            $return_array[$wpjobportal_field->field] = 1;
        }

        return $return_array;
    }

     function getUnpublishedFieldsFor($wpjobportal_fieldfor,$wpjobportal_section = null){
        if(!is_numeric($wpjobportal_fieldfor)) return false;
        if($wpjobportal_section != null)
            if(!is_numeric($wpjobportal_section)) return false;

        $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        if ($wpjobportal_uid != "" AND $wpjobportal_uid != 0){ // is admin Or is logged in
            $wpjobportal_published = "published = 0";
        }else{
            $wpjobportal_published = "isvisitorpublished = 0";
        }
        if($wpjobportal_section != null){
            $wpjobportal_published .= ' AND section = '.$wpjobportal_section;
        }

        $query = "SELECT field FROM `". wpjobportal::$_db->prefix ."wj_portal_fieldsordering` WHERE fieldfor = ".esc_sql($wpjobportal_fieldfor)." AND ".esc_sql($wpjobportal_published);
        $wpjobportal_fields = wpjobportaldb::get_results($query);
        return $wpjobportal_fields;
    }


    function getResumeFieldsOrderingBySection($wpjobportal_section) {
        if(!is_numeric($wpjobportal_section))  return false;

        $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        $wpjobportal_is_visitor = '';
        if ($wpjobportal_uid != "" AND $wpjobportal_uid != 0){ // is admin Or is logged in
            $wpjobportal_published = "published = 1";
        }else{
            $wpjobportal_published = "isvisitorpublished = 1";
            $wpjobportal_is_visitor = ' , fields.isvisitorpublished AS published ';
        }

        $query = "SELECT fields.* ".esc_sql($wpjobportal_is_visitor)." FROM `". wpjobportal::$_db->prefix ."wj_portal_fieldsordering` AS fields
            WHERE ".esc_sql($wpjobportal_published)." AND fieldfor = 3 AND section = ".esc_sql($wpjobportal_section);
        $query .= " ORDER BY section,ordering ASC";
        $wpjobportal_fieldsOrdering = wpjobportaldb::get_results($query);
        return $wpjobportal_fieldsOrdering;
    }

    function getResumeFieldsOrderingBySection1($wpjobportal_section) { // created and used by muhiaudin for resume view 'formresume'
        $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        if (empty($wpjobportal_section)) {
            return false;
        }
        if (is_numeric($wpjobportal_section)) {
            return false;
        }
        if ($wpjobportal_uid != "" AND $wpjobportal_uid != 0) {
            $wpjobportal_fieldfor = 3;
        } else {
            $wpjobportal_fieldfor = 16;
        }

        if ($wpjobportal_fieldfor == 16) { // resume visitor case
            $wpjobportal_fieldfor = 3;
            $query = "SELECT  id,field,fieldtitle,ordering,section,fieldfor,isvisitorpublished AS published,sys,cannotunpublish,required
                        FROM `" . wpjobportal::$_db->prefix . "wj_portal_fieldsordering`
                        WHERE isvisitorpublished = 1 AND fieldfor =  " . esc_sql($wpjobportal_fieldfor) . " AND section = " . esc_sql($wpjobportal_section)
                    . " ORDER BY section,ordering";
        } else {
            $wpjobportal_published_field = "published = 1";
            if (is_user_logged_in() == false) {
                $wpjobportal_published_field = "isvisitorpublished = 1";
            }
            $query = "SELECT  * FROM `" . wpjobportal::$_db->prefix . "wj_portal_fieldsordering`
                        WHERE " . esc_sql($wpjobportal_published_field) . " AND fieldfor =  " . esc_sql($wpjobportal_fieldfor) . " AND section = " . esc_sql($wpjobportal_section)
                    . " ORDER BY section,ordering ";
        }
        $wpjobportal_fieldsOrdering = wpjobportaldb::get_results($query);
        return $wpjobportal_fieldsOrdering;
    }


    function downloadCustomUploadedFile($wpjobportal_upload_for,$file_name,$wpjobportal_entity_id){
        //$wpjobportal_upload_for to handle different entities(company, job, resume)
        //$wpjobportal_entity_id to create path for enitity directory where the file is located
        //$file_name to access the file and download it

        $filename = wpjobportalphplib::wpJP_str_replace(' ', '_', $file_name);

        // clean file name to remove relative path
        $filename = wpjobportalphplib::wpJP_clean_file_path($filename);
        $filename = sanitize_file_name($filename);

        if($filename == ''){
            return;
        }

        if(!is_numeric($wpjobportal_entity_id)){
            return;
        }

        $wpjobportal_maindir = wp_upload_dir();
        $basedir = $wpjobportal_maindir['basedir'];
        $wpjobportal_datadirectory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');

        $wpjobportal_path = $basedir . '/' . $wpjobportal_datadirectory. '/data';

        if($wpjobportal_upload_for == 'company'){
            $wpjobportal_path = $wpjobportal_path . '/employer/comp_'.$wpjobportal_entity_id.'/custom_uploads';
        }elseif($wpjobportal_upload_for == 'job'){
            $wpjobportal_path = $wpjobportal_path . '/employer/job_'.$wpjobportal_entity_id.'/custom_uploads';
        }elseif($wpjobportal_upload_for == 'resume'){
            $wpjobportal_path = $wpjobportal_path . '/jobseeker/resume_'.$wpjobportal_entity_id.'/custom_uploads';
        }elseif($wpjobportal_upload_for == 'profile'){
            $wpjobportal_path = $wpjobportal_path . '/profile/profile_'.$wpjobportal_entity_id.'/custom_uploads';
        }


        $file = $wpjobportal_path .'/'.$filename;
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . wpjobportalphplib::wpJP_basename($file));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        //ob_clean();
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
        exit();
    }


    function getMessagekey(){
        $wpjobportal_key = 'fieldordering';if(wpjobportal::$_common->wpjp_isadmin()){$wpjobportal_key = 'admin_'.$wpjobportal_key;}return $wpjobportal_key;
    }

}
?>
