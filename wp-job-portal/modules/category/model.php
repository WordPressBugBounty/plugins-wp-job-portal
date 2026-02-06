<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALCategoryModel {
    public $class_prefix = '';

    function __construct(){
        if(wpjobportal::$wpjobportal_theme_chk == 1){
            $this->class_prefix = 'wpj-jp';
        }elseif(wpjobportal::$wpjobportal_theme_chk == 2){
            $this->class_prefix = 'jsjb-jh';
        }
    }

    function getCategorybyId($wpjobportal_id,$wpjobportal_count_flag = 0) {
        if (is_numeric($wpjobportal_id) == false) return false;

        $query = " SELECT * FROM " . wpjobportal::$_db->prefix . "wj_portal_categories WHERE id = " . esc_sql($wpjobportal_id);
        wpjobportal::$_data[0] = wpjobportaldb::get_row($query);

        if($wpjobportal_count_flag == 3 || $wpjobportal_count_flag == 2){
            $query = " SELECT count(job.id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                       WHERE job.jobcategory = ".esc_sql($wpjobportal_id)." AND DATE(job.startpublishing) <= CURDATE() AND DATE(job.stoppublishing) >= CURDATE() AND job.status = 1 ";
            wpjobportal::$_data[0]->count = wpjobportaldb::get_var($query);
        }else{
            if(wpjobportal::$_data[0] == ''){
                wpjobportal::$_data[0] = new stdClass();
            }
            wpjobportal::$_data[0]->count = -1;
        }
        return;
    }

    function getAllCategories() {
        //Filters
        $wpjobportal_categoryname = wpjobportal::$_search['category']['searchname'];
        $wpjobportal_status = wpjobportal::$_search['category']['status'];
        $pagesize = absint(WPJOBPORTALrequest::getVar('pagesize'));
        $wpjobportal_formsearch = WPJOBPORTALrequest::getVar('WPJOBPORTAL_form_search', 'post');
        if ($wpjobportal_formsearch == 'WPJOBPORTAL_SEARCH') {
            update_option( 'wpjobportal_page_size', $pagesize);
        }
        if(get_option( 'wpjobportal_page_size', '' ) != ''){
            $pagesize = get_option( 'wpjobportal_page_size');
        }
        $wpjobportal_inquery = '';
        $wpjobportal_statusop = 'WHERE parentid = 0';
        $filter_flag = 0;
        if ($wpjobportal_categoryname != null) {
            $wpjobportal_inquery .= " AND cat_title LIKE '%".esc_sql($wpjobportal_categoryname)."%'";
            $wpjobportal_statusop = 'WHERE 1 = 1 ';
            $filter_flag = 1;
        }
        if (is_numeric($wpjobportal_status)) {
            $wpjobportal_statusop = 'WHERE 1 = 1 ';
            $wpjobportal_inquery .=" AND isactive = " . esc_sql($wpjobportal_status);
            $filter_flag = 1;
        }
        $wpjobportal_inquery .= "";

        wpjobportal::$_data['filter']['searchname'] = $wpjobportal_categoryname;
        wpjobportal::$_data['filter']['status'] = $wpjobportal_status;
        wpjobportal::$_data['filter']['pagesize'] = $pagesize;
        //pagination
        if($pagesize){
           WPJOBPORTALpagination::setLimit($pagesize);
        }
        $query = "SELECT COUNT(id) FROM " . wpjobportal::$_db->prefix . "wj_portal_categories  $wpjobportal_statusop";
        $query .= $wpjobportal_inquery;
        $wpjobportal_total = wpjobportaldb::get_var($query);
        wpjobportal::$_data['total'] = $wpjobportal_total;
        wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($wpjobportal_total);
        //data
        $wpjobportal_result = array();
        $wpjobportal_prefix = '|-- ';
        $query = "SELECT * FROM " . wpjobportal::$_db->prefix . "wj_portal_categories $wpjobportal_statusop ";
        $query .= $wpjobportal_inquery;
        $query .= " ORDER BY ordering ASC ";
        $wpjobportal_categories = wpjobportal::$_db->get_results($query);

        if($filter_flag == 0){
            if (isset($wpjobportal_categories)) {
                foreach ($wpjobportal_categories as $cat) {
                    $record = (object) array();
                    $record->id = $cat->id;
                    $record->cat_title = $cat->cat_title;
                    $record->alias = $cat->alias;
                    $record->isactive = $cat->isactive;
                    $record->isdefault = $cat->isdefault;
                    $record->ordering = $cat->ordering;
                    $wpjobportal_result[] = $record;
                    $this->getCategoryChild($cat->id, $wpjobportal_prefix, $wpjobportal_result);
                }
            }
        }else{
            foreach ($wpjobportal_categories as $cat) {
                if($cat->parentid != 0){
                    $cat->cat_title = '|--'.$cat->cat_title;
                }
                $wpjobportal_result[] = (object) $cat;
            }
        }
        $wpjobportal_totalresult = count($wpjobportal_result);
        wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($wpjobportal_totalresult);

        $finalresult = array();
        WPJOBPORTALpagination::$_limit = WPJOBPORTALpagination::$_limit + WPJOBPORTALpagination::$_offset;
        if (WPJOBPORTALpagination::$_limit >= $wpjobportal_totalresult)
            WPJOBPORTALpagination::$_limit = $wpjobportal_totalresult;
        for ($wpjobportal_i = WPJOBPORTALpagination::$_offset; $wpjobportal_i < WPJOBPORTALpagination::$_limit; $wpjobportal_i++) {
            $finalresult[] = $wpjobportal_result[$wpjobportal_i];
        }

        wpjobportal::$_data[0] = $finalresult;
        return;
    }

    private function getCategoryChild($parentid, $wpjobportal_prefix, &$wpjobportal_result, $for_combo = 0) {

        if (!is_numeric($parentid))
            return false;
        // to handle the case that this function handles sub categories for combo box and admin category listing. in one case we have to show the unpublished
        $wpjobportal_is_active_check = '';
        if($for_combo == 1){
            $wpjobportal_is_active_check = ' AND category.isactive = 1 ';
        }

        $query = "SELECT * FROM `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS category WHERE category.parentid = " . esc_sql($parentid);
        $query .= $wpjobportal_is_active_check;
        $query .= " ORDER by category.ordering "; // "isactive = 1" check to hide unpublished categories
        $wpjobportal_kbcategories = wpjobportal::$_db->get_results($query);
        if (!empty($wpjobportal_kbcategories)) {
            foreach ($wpjobportal_kbcategories as $cat) {
                $wpjobportal_subrecord = (object) array();
                $wpjobportal_subrecord->id = $cat->id;
                $wpjobportal_subrecord->cat_title = $wpjobportal_prefix . wpjobportal::wpjobportal_getVariableValue($cat->cat_title);
                $wpjobportal_subrecord->alias = $cat->alias;
                $wpjobportal_subrecord->isactive = $cat->isactive;
                $wpjobportal_subrecord->isdefault = $cat->isdefault;
                $wpjobportal_subrecord->ordering = $cat->ordering;
                $wpjobportal_result[] = $wpjobportal_subrecord;
                $this->getCategoryChild($cat->id, $wpjobportal_prefix . '|-- ', $wpjobportal_result, $for_combo);
            }
            return $wpjobportal_result;
        }
    }

    function getCategoryForCombobox($wpjobportal_themecall=null) {
        $wpjobportal_result = array();
        $wpjobportal_prefix = '|-- ';
        $query = "SELECT category.* from `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS category
                    WHERE category.parentid = 0 AND category.isactive = 1 ORDER by category.ordering";
        $wpjobportal_knowledgebase = wpjobportal::$_db->get_results($query);
        if (isset($wpjobportal_knowledgebase)) {
            foreach ($wpjobportal_knowledgebase as $wpjobportal_kb) {
                $record = (object) array();
                $record->id = $wpjobportal_kb->id;
                $record->cat_title = $wpjobportal_kb->cat_title;
                $wpjobportal_result[] = $record;
                $this->getCategoryChild($wpjobportal_kb->id, $wpjobportal_prefix, $wpjobportal_result,1); // 4th parameter 1 is for making sure only published categories or subcategories are shown.
            }
        }
        $list = array();
        foreach ($wpjobportal_result AS $wpjobportal_category) {
            if(null != $wpjobportal_themecall){
                //$list[$wpjobportal_category->id] = $wpjobportal_category->cat_title;
                $list[$wpjobportal_category->cat_title] = intval($wpjobportal_category->id);
            }else{
                $list[] = (object) array('id' => $wpjobportal_category->id, 'text' => $wpjobportal_category->cat_title);

            }
        }
        return $list;
    }

    function updateIsDefault($wpjobportal_id) {
        if (!is_numeric($wpjobportal_id))
            return false;
        //DB class limitations
        $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_categories` SET isdefault = 0 WHERE id != " . esc_sql($wpjobportal_id);
        wpjobportaldb::query($query);
    }

    function validateFormData(&$wpjobportal_data) {
        $wpjobportal_category = WPJOBPORTALrequest::getVar('parentid');
        $wpjobportal_inquery = ' ';
        if (is_numeric($wpjobportal_category)) {
            $wpjobportal_inquery .=" WHERE parentid = ".esc_sql($wpjobportal_category);
        }
        $canupdate = false;
        if ($wpjobportal_data['id'] == '') {
            $wpjobportal_result = $this->isCategoryExist($wpjobportal_data['cat_title']);
            if ($wpjobportal_result == true) {
                return WPJOBPORTAL_ALREADY_EXIST;
            } else {
                $query = "SELECT max(ordering)+1 AS maxordering FROM " . wpjobportal::$_db->prefix . "wj_portal_categories " . $wpjobportal_inquery;
                $wpjobportal_data['ordering'] = wpjobportaldb::get_var($query);
                if ($wpjobportal_data['ordering'] == null)
                    $wpjobportal_data['ordering'] = 1;
            }

            if ($wpjobportal_data['isactive'] == 0) {
                $wpjobportal_data['isdefault'] = 0;
            } else {
                if (isset($wpjobportal_data['isdefault']) AND $wpjobportal_data['isdefault'] == 1) {
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

    function storeCategory($wpjobportal_data) {
        if (empty($wpjobportal_data))
            return false;

        $canupdate = $this->validateFormData($wpjobportal_data);
        if ($canupdate === WPJOBPORTAL_ALREADY_EXIST)
            return WPJOBPORTAL_ALREADY_EXIST;

        if (!empty($wpjobportal_data['alias']))
            $cat_title_alias = WPJOBPORTALincluder::getJSModel('common')->removeSpecialCharacter($wpjobportal_data['alias']);
        else
            $cat_title_alias = WPJOBPORTALincluder::getJSModel('common')->removeSpecialCharacter($wpjobportal_data['cat_title']);

        $cat_title_alias = wpjobportalphplib::wpJP_strtolower(wpjobportalphplib::wpJP_str_replace(' ', '-', $cat_title_alias));
        $cat_title_alias = wpjobportalphplib::wpJP_strtolower(wpjobportalphplib::wpJP_str_replace('/', '-', $cat_title_alias));
        $cat_title_alias = wpjobportalphplib::wpJP_strtolower(wpjobportalphplib::wpJP_str_replace('_', '-', $cat_title_alias));
        $wpjobportal_data['alias'] = $cat_title_alias;

        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('categories');

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

    function deleteCategories($wpjobportal_ids) {
        if (empty($wpjobportal_ids))
            return false;
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('categories');
        $wpjobportal_notdeleted = 0;
        foreach ($wpjobportal_ids as $wpjobportal_id) {
            if ($this->categoryCanDelete($wpjobportal_id) == true) {
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

        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('categories');
        $wpjobportal_total = 0;
        if ($wpjobportal_status == 1) {
            foreach ($wpjobportal_ids as $wpjobportal_id) {
                if (!$wpjobportal_row->update(array('id' => $wpjobportal_id, 'isactive' => $wpjobportal_status))) {
                    $wpjobportal_total += 1;
                }
            }
        } else {
            foreach ($wpjobportal_ids as $wpjobportal_id) {
                if ($this->categoryCanUnpublish($wpjobportal_id)) {
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

    function categoryCanUnpublish($wpjobportal_categoryid) {
        if (!is_numeric($wpjobportal_categoryid))
            return false;
        $query = "SELECT
                    ( SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_categories` WHERE id = " . esc_sql($wpjobportal_categoryid) . " AND isdefault = 1)
                    AS total ";
        $wpjobportal_total = wpjobportaldb::get_var($query);
        if ($wpjobportal_total > 0)
            return false;
        else
            return true;
    }

    function categoryCanDelete($wpjobportal_categoryid) {
        if (!is_numeric($wpjobportal_categoryid))
            return false;
        $query = "SELECT
                    ( SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` WHERE jobcategory = " . esc_sql($wpjobportal_categoryid) . ")
                    +( SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` WHERE job_category = " . esc_sql($wpjobportal_categoryid) . ")
                    +( SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_categories` WHERE id = " . esc_sql($wpjobportal_categoryid) . " AND isdefault = 1)
                    AS total ";
        $wpjobportal_total = wpjobportaldb::get_var($query);
        if ($wpjobportal_total > 0)
            return false;
        else
            return true;
    }

    function isCategoryExist($title) {

        $query = "SELECT COUNT(id) FROM " . wpjobportal::$_db->prefix . "wj_portal_categories WHERE cat_title = '" . esc_sql($title) . "'";
        $wpjobportal_result = wpjobportaldb::get_var($query);
        if ($wpjobportal_result > 0)
            return true;
        else
            return false;
    }

    function getCategoriesForCombo() {
        $wpjobportal_rows = $this->getCategoryForCombobox();
        return $wpjobportal_rows;
    }

    function getsubcategories() {
        $wpjobportal_categoryalias = WPJOBPORTALrequest::getVar('category');
        $wpjobportal_categoryid = WPJOBPORTALincluder::getJSModel('job')->parseid($wpjobportal_categoryalias);
        if (!is_numeric($wpjobportal_categoryid))
            return false;
        $query = "SELECT count(cat.id)
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat
                    WHERE cat.parentid = " . esc_sql($wpjobportal_categoryid);
        $wpjobportal_count = wpjobportal::$_db->get_var($query);
        $query = "SELECT cat.cat_title
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat
                    WHERE cat.id = " . esc_sql($wpjobportal_categoryid);
        $cat_title = wpjobportal::$_db->get_var($query);
        $wpjobportal_config_array = WPJOBPORTALincluder::getJSModel('configuration')->getConfigByFor('category');
        $wpjobportal_subcategory_limit = 3;
        if($wpjobportal_config_array['subcategory_limit'] != ''){ // to handle float value in configuration
            $wpjobportal_subcategory_limit = ceil($wpjobportal_config_array['subcategory_limit']);
        }
        $query = "SELECT cat.cat_title, CONCAT(cat.alias,'-',cat.id) AS aliasid,
                    (SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` WHERE jobcategory = cat.id) AS totaljobs
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat
                    WHERE cat.parentid = " . esc_sql($wpjobportal_categoryid) . " ORDER BY cat.ordering ASC LIMIT " . esc_sql($wpjobportal_subcategory_limit);
        $wpjobportal_result = wpjobportal::$_db->get_results($query);
        $wpjobportal_html = '';
        $wpjobportal_resume = WPJOBPORTALrequest::getVar('resume');
        if(wpjobportal::$wpjobportal_theme_chk == 2){
            $wpjobportal_prefix = $this->class_prefix.'-';
            $wpjobportal_main_wrap = '';
        }else{
            $wpjobportal_prefix = '';
            $wpjobportal_main_wrap = 'js';
        }
        if (!empty($wpjobportal_result)) {
            $wpjobportal_html .= '<div class="'.esc_attr($wpjobportal_prefix).esc_attr($wpjobportal_main_wrap).'jobs-subcategory-wrapper">';
            foreach ($wpjobportal_result AS $cat) {
                if ($wpjobportal_resume == 1) {
                    $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'resumes', 'category'=>$cat->aliasid, 'wpjobportalpageid'=>WPJOBPORTALRequest::getVar('wpjobportalpageid')));
                } else {
                    $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'jobs', 'category'=>$cat->aliasid, 'wpjobportalpageid'=>WPJOBPORTALRequest::getVar('wpjobportalpageid')));
                }
                $wpjobportal_html .= '  <div class="'.esc_attr($wpjobportal_prefix).'category-wrapper" style="width:100%;">
                                <a href="' . esc_url($wpjobportal_link) . '">
                                <div class="'.esc_attr($wpjobportal_prefix).'jobs-by-categories-wrapper">
                                    <span class="'.esc_attr($wpjobportal_prefix).'title">' . wpjobportal::wpjobportal_getVariableValue($cat->cat_title) . '</span>';
                if ($wpjobportal_resume == 1) {
                    if($wpjobportal_config_array['categories_numberofresumes'] == 1){
                        $wpjobportal_html .= '<span class="'.esc_attr($wpjobportal_prefix).esc_html('totat-jobs">)(') . esc_html($cat->totaljobs) . ')</span>';
                    }
                }else{
                    if($wpjobportal_config_array['categories_numberofjobs'] == 1){
                        $wpjobportal_html .= '<span class="'.esc_attr($wpjobportal_prefix).'totat-jobs">(' . esc_html($cat->totaljobs) . ')</span>';
                    }
                }
                $wpjobportal_html .=    '</div>
                            </a>
                        </div>';
            }
            if ($wpjobportal_count > $wpjobportal_subcategory_limit) {
                $wpjobportal_html .= '  <div class="showmore-wrapper">
                                <a href="#" class="showmorebutton" data-title="' . esc_attr($cat_title) . '" data-id="' . esc_attr($wpjobportal_categoryalias) . '">' . esc_html(__('Show More', 'wp-job-portal')) . '</a>
                            </div>';
            }
            $wpjobportal_html .= '</div>';
        }
        return $wpjobportal_html;
    }

    private function getAllParentListTillRoot($wpjobportal_categoryid,&$parentsarray){
        if(!is_numeric($wpjobportal_categoryid)) return false;
        $query = "SELECT id, cat_title, parentid
        FROM `".wpjobportal::$_db->prefix."wj_portal_categories`
        WHERE id = " . esc_sql($wpjobportal_categoryid);
        $wpjobportal_result = wpjobportal::$_db->get_row($query);
        if($wpjobportal_result){
            $parentsarray[$wpjobportal_result->id] = $wpjobportal_result->cat_title;
            if(is_numeric($wpjobportal_result->parentid) && $wpjobportal_result->parentid != 0){
                $wpjobportal_categoryid = $wpjobportal_result->parentid;
                $this->getAllParentListTillRoot($wpjobportal_categoryid,$parentsarray);
            }
        }
        return;
    }

    function getsubcategorypopup() {

        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'get-subcategory-popup') ) {
            die( 'Security check Failed' );
        }
        $wpjobportal_category = WPJOBPORTALrequest::getVar('category');
        $wpjobportal_categoryid = WPJOBPORTALincluder::getJSModel('job')->parseid($wpjobportal_category);
        $wpjobportal_config_array = WPJOBPORTALincluder::getJSModel('configuration')->getConfigByFor('category');
        $wpjobportal_subcategory_limit = 3;
        if($wpjobportal_config_array['subcategory_limit'] != ''){ // to handle float value in configuration
            $wpjobportal_subcategory_limit = ceil($wpjobportal_config_array['subcategory_limit']);
        }
        $wpjobportal_resume = WPJOBPORTALrequest::getVar('resume');
        if (!is_numeric($wpjobportal_categoryid))
            return false;
        if($wpjobportal_resume == 1){
            $query = "SELECT cat.cat_title, CONCAT(cat.alias,'-',cat.id) AS aliasid,cat.id AS categoryid,
                        (SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` WHERE job_category = cat.id AND status = 1 AND searchable = 1) AS totaljobs
                        FROM `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat
                        WHERE cat.isactive = 1 AND cat.parentid = " . esc_sql($wpjobportal_categoryid)
                         ;
        }else{
            $query = "SELECT cat.cat_title, CONCAT(cat.alias,'-',cat.id) AS aliasid,cat.id AS categoryid,
                        (SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS jobs WHERE jobs.jobcategory = cat.id AND DATE(jobs.startpublishing) <= CURDATE() AND DATE(jobs.stoppublishing) >= CURDATE() AND jobs.status = 1) AS totaljobs
                        FROM `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS cat
                        WHERE cat.isactive = 1 AND cat.parentid = " . esc_sql($wpjobportal_categoryid)
                         ;
        }
        $wpjobportal_result = wpjobportal::$_db->get_results($query);
        foreach($wpjobportal_result AS $cat_child){
            if($wpjobportal_resume == 1){
                $query = "SELECT category.cat_title, CONCAT(category.alias,'-',category.id) AS aliasid,category.serverid
                    ,(SELECT count(resume.id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` AS resume
                        where resume.job_category = category.id AND resume.status = 1)  AS totaljobs
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS category
                    WHERE category.isactive = 1 AND category.parentid = ".esc_sql($cat_child->categoryid)." ORDER BY category.ordering ASC LIMIT ".esc_sql($wpjobportal_subcategory_limit);
            }else{
                $query = "SELECT category.cat_title, CONCAT(category.alias,'-',category.id) AS aliasid,category.serverid
                    ,(SELECT count(job.id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                        where job.jobcategory = category.id AND DATE(job.startpublishing) <= CURDATE() AND DATE(job.stoppublishing) >= CURDATE())  AS totaljobs
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS category
                    WHERE category.isactive = 1 AND category.parentid = ".esc_sql($cat_child->categoryid)." ORDER BY category.ordering ASC LIMIT ".esc_sql($wpjobportal_subcategory_limit);
            }
            $cat_child->subcat = wpjobportal::$_db->get_results($query);
        }
        $wpjobportal_html = '';
        if (!empty($wpjobportal_result)) {
            if(wpjobportal::$wpjobportal_theme_chk == 1){
                $wpjobportal_prefix = $this->class_prefix.'-';
            $wpjobportal_html .= '<div class="'.esc_attr($wpjobportal_prefix).'by-sub-category">';
                $wpjobportal_main_wrap = '';
            }else{
                $wpjobportal_prefix = 'wjportal-';
                $wpjobportal_main_wrap = 'js';
            $wpjobportal_html .= '<div class="'.esc_attr($wpjobportal_prefix).'by-sub-catagory">';
            }
            foreach ($wpjobportal_result AS $cat) {
                if ($wpjobportal_resume == 1) {
                    $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'resumes', 'category'=>$cat->aliasid, 'wpjobportalpageid'=>WPJOBPORTALRequest::getVar('page_id')));
                } else {
                    $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'jobs', 'category'=>$cat->aliasid, 'wpjobportalpageid'=>WPJOBPORTALRequest::getVar('page_id')));
                }
                $wpjobportal_html .= '  <div data-id="' . $cat->aliasid . '" class="'.esc_attr($wpjobportal_prefix).'by-category-wrp" style="width:calc(50% - 0.5rem);">
                                <a href="' . esc_url($wpjobportal_link) . '">
                                <div class="'.esc_attr($wpjobportal_prefix).'by-category-item">
                                    <span class="'.esc_attr($wpjobportal_prefix).'by-category-item-title">' . wpjobportal::wpjobportal_getVariableValue($cat->cat_title) . '</span>';
                        if ($wpjobportal_resume == 1) {
                            if($wpjobportal_config_array['categories_numberofresumes'] == 1){
                                $wpjobportal_html .= '<span class="'.esc_attr($wpjobportal_prefix).'by-category-item-number">(' . esc_html($cat->totaljobs) . ')</span>';
                            }
                        }else{
                            if($wpjobportal_config_array['categories_numberofjobs'] == 1){
                                $wpjobportal_html .= '<span class="'.esc_attr($wpjobportal_prefix).'by-category-item-number">(' . esc_html($cat->totaljobs) . ')</span>';
                            }
                        }
                $wpjobportal_html .= '
                                </div>
                                </a>';
                if (!empty($cat->subcat)) {
                    $wpjobportal_html .= '<div class="'.esc_attr($wpjobportal_prefix).esc_attr($wpjobportal_main_wrap).'by-sub-catagory" style="display:none;">';
                    $wpjobportal_subcount = 0;
                    foreach ($cat->subcat AS $wpjobportal_sub_cat) {
                        if($wpjobportal_resume == 1){
                            $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'resumes', 'category'=>$wpjobportal_sub_cat->aliasid, 'wpjobportalpageid'=>WPJOBPORTALRequest::getVar('page_id')));
                        }else{
                            $wpjobportal_link = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'job', 'wpjobportallt'=>'jobs', 'category'=>$wpjobportal_sub_cat->aliasid, 'wpjobportalpageid'=>WPJOBPORTALRequest::getVar('page_id')));
                        }
                        $wpjobportal_html .= '  <div class="'.esc_attr($wpjobportal_prefix).'by-category-wrp" style="width:100%;">
                                        <a href="' . esc_url($wpjobportal_link) . '">
                                        <div class="'.esc_attr($wpjobportal_prefix).'by-category-item">
                                            <span class="'.esc_attr($wpjobportal_prefix).'by-category-item-title">' . wpjobportal::wpjobportal_getVariableValue($wpjobportal_sub_cat->cat_title) . '</span>';
                        if ($wpjobportal_resume == 1) {
                            if($wpjobportal_config_array['categories_numberofresumes'] == 1){
                                $wpjobportal_html .= '<span class="'.esc_attr($wpjobportal_prefix).'by-category-item-number">(' . $wpjobportal_sub_cat->totaljobs . ')</span>';
                            }
                        }else{
                            if($wpjobportal_config_array['categories_numberofjobs'] == 1){
                                $wpjobportal_html .= '<span class="'.esc_attr($wpjobportal_prefix).'by-category-item-number">(' . $wpjobportal_sub_cat->totaljobs . ')</span>';
                            }
                        }
                        $wpjobportal_html .=    '</div>
                                    </a>
                                </div>';
                        $wpjobportal_subcount++;
                    }
                    if ($wpjobportal_subcount >= $wpjobportal_subcategory_limit) {
                        $wpjobportal_html .= '  <div class="'.esc_attr($wpjobportal_prefix).'by-category-item-btn">
                                        <a href="#" class="'.esc_attr($wpjobportal_prefix).'wjportal-by-category-item-btn-wrp" onclick="getPopupAjax(\'' . $cat->aliasid . '\', \'' . wpjobportal::wpjobportal_getVariableValue($cat->cat_title) . '\');">' . esc_html(__('Show More', 'wp-job-portal')) . '</a>
                                    </div>';
                    }
                    $wpjobportal_html .= '</div>';
                }

                $wpjobportal_html .= '</div>';
            }
            $wpjobportal_html .= '</div>';
        }
        // Navigation get all parents
        $parentsarray = array();
        $this->getAllParentListTillRoot($wpjobportal_categoryid,$parentsarray);
        if(!empty($parentsarray)){
            if(wpjobportal::$wpjobportal_theme_chk == 1){
                $wpjobportal_prefix = $this->class_prefix.'-';
            }else{
                $wpjobportal_prefix = 'wjportal-';
            }
            $wpjobportal_html .= '<ul class="'.esc_attr($wpjobportal_prefix).'popup-navigation">';
            foreach($parentsarray AS $pcatid => $pcattitle){
                $wpjobportal_html .= '<li onclick="getPopupAjax('.$pcatid.',\''.wpjobportal::wpjobportal_getVariableValue($pcattitle).'\');">'.wpjobportal::wpjobportal_getVariableValue($pcattitle).'</li>';
            }
            $wpjobportal_html .= '</ul>';
        }
        return $wpjobportal_html;
    }
    function getDefaultCategoryId() {

        $query = "SELECT id FROM " . wpjobportal::$_db->prefix . "wj_portal_categories WHERE isdefault = 1";
        $wpjobportal_id = wpjobportaldb::get_var($query);
        return $wpjobportal_id;
    }

    function getTitleByCategory($wpjobportal_id) {
        if(!is_numeric($wpjobportal_id)) return false;
        $query = "SELECT cat_title FROM " . wpjobportal::$_db->prefix . "wj_portal_categories WHERE id = " . esc_sql($wpjobportal_id);
        $title = wpjobportaldb::get_var($query);
        return $title;
    }

    function getMessagekey(){
        $wpjobportal_key = 'category';if(wpjobportal::$_common->wpjp_isadmin()){$wpjobportal_key = 'admin_'.$wpjobportal_key;}return $wpjobportal_key;
    }

    function getTopCategories($limit){
        $query = "SELECT category.id,category.cat_title AS title
            ,(SELECT count(job.id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                where job.jobcategory = category.id AND DATE(job.startpublishing) <= CURDATE() AND DATE(job.stoppublishing) >= CURDATE() AND job.status = 1)  AS totaljobs
            FROM `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS category
            WHERE category.isactive = 1 having totaljobs > 0 ORDER BY totaljobs DESC LIMIT ".esc_sql($limit);
        $wpjobportal_data = wpjobportal::$_db->get_results($query);
        return $wpjobportal_data;
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
            $wpjobportal_row = WPJOBPORTALincluder::getJSTable('categories');
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
            $wpjobportal_row->update(array('id' => $sorted_array[$wpjobportal_i], $ordering_coloumn => ($page_multiplier + $wpjobportal_i +1) ));//+1 to handle 0
        }
        WPJOBPORTALMessages::setLayoutMessage(esc_html(__('Ordering updated', 'wp-job-portal')), 'updated', $this->getMessagekey());
        return ;
    }

    //search cookies data
    function getSearchFormDataCategory(){
        $wpjobportal_jsjp_search_array = array();
        $wpjobportal_jsjp_search_array['searchname'] = WPJOBPORTALrequest::getVar('searchname');
        $wpjobportal_jsjp_search_array['status'] = WPJOBPORTALrequest::getVar('status');
        $wpjobportal_jsjp_search_array['search_from_category'] = 1;
        return $wpjobportal_jsjp_search_array;
    }

    function getCookiesSavedCategory(){
        $wpjobportal_jsjp_search_array = array();
        $wpjp_search_cookie_data = '';
        if(isset($_COOKIE['jsjp_jobportal_search_data'])){
            $wpjp_search_cookie_data = wpjobportal::wpjobportal_sanitizeData($_COOKIE['jsjp_jobportal_search_data']);
            $wpjp_search_cookie_data = wpjobportalphplib::wpJP_safe_decoding($wpjp_search_cookie_data);
            $wpjp_search_cookie_data = json_decode( $wpjp_search_cookie_data , true );
        }
        if($wpjp_search_cookie_data != '' && isset($wpjp_search_cookie_data['search_from_category']) && $wpjp_search_cookie_data['search_from_category'] == 1){
            $wpjobportal_jsjp_search_array['searchname'] = $wpjp_search_cookie_data['searchname'];
            $wpjobportal_jsjp_search_array['status'] = $wpjp_search_cookie_data['status'];
        }
        return $wpjobportal_jsjp_search_array;
    }

    function setSearchVariableCategory($wpjobportal_jsjp_search_array){
        wpjobportal::$_search['category']['searchname'] = isset($wpjobportal_jsjp_search_array['searchname']) ? $wpjobportal_jsjp_search_array['searchname'] : null;
        wpjobportal::$_search['category']['status'] = isset($wpjobportal_jsjp_search_array['status']) ? $wpjobportal_jsjp_search_array['status'] : null;
    }
}

?>
