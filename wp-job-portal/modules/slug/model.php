<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALslugModel {

    private $_params_flag;
    private $_params_string;

    function __construct() {
        $this->_params_flag = 0;
    }

    function getSlug() {
        // Filter
        $wpjobportal_slug = wpjobportal::$_search['slug']['slug'];

        $wpjobportal_inquery = '';
        if ($wpjobportal_slug != null){
            $wpjobportal_inquery .= " AND slug.slug LIKE '%".esc_sql($wpjobportal_slug)."%'";
        }
        wpjobportal::$_data['slug'] = $wpjobportal_slug;

        //pagination
        $query = "SELECT COUNT(id) FROM ".wpjobportal::$_db->prefix."wj_portal_slug AS slug WHERE slug.status = 1 ";
        $query .= $wpjobportal_inquery;
        $wpjobportal_total = wpjobportaldb::get_var($query);

        wpjobportal::$_data['total'] = $wpjobportal_total;
        wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($wpjobportal_total);

        //Data
        $query = "SELECT *
                  FROM ".wpjobportal::$_db->prefix ."wj_portal_slug AS slug WHERE slug.status = 1 ";
        $query .= $wpjobportal_inquery;
        $query .= " LIMIT " . WPJOBPORTALpagination::$_offset . " , " . WPJOBPORTALpagination::$_limit;
        wpjobportal::$_data[0] = wpjobportaldb::get_results($query);

        return;
    }


    function storeSlug($wpjobportal_data) {
        if (empty($wpjobportal_data)) {
            return false;
        }
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('slug');
        foreach ($wpjobportal_data as $wpjobportal_id => $wpjobportal_slug) {
            if($wpjobportal_id != '' && is_numeric($wpjobportal_id)){
                $wpjobportal_slug = sanitize_title($wpjobportal_slug);
                if($wpjobportal_slug != ''){
                    $query = "SELECT COUNT(id) FROM " . wpjobportal::$_db->prefix . "wj_portal_slug
                            WHERE slug = '" . esc_sql($wpjobportal_slug)."' ";
                    $wpjobportal_slug_flag = wpjobportaldb::get_var($query);
                    if($wpjobportal_slug_flag > 0){
                        continue;
                    }else{
                        $wpjobportal_row->update(array('id' => $wpjobportal_id, 'slug' => $wpjobportal_slug));
                    }
                }
            }
        }
        update_option('rewrite_rules', '');
        return WPJOBPORTAL_SAVED;
    }

    function savePrefix($wpjobportal_data) {
        if (empty($wpjobportal_data)) {
            return false;
        }
        $wpjobportal_data['prefix'] = sanitize_title($wpjobportal_data['prefix']);
        if($wpjobportal_data['prefix'] == ''){
            return WPJOBPORTAL_SAVE_ERROR;
        }
        $query = "UPDATE " . wpjobportal::$_db->prefix . "wj_portal_config
                    SET configvalue = '".esc_sql($wpjobportal_data['prefix'])."'
                    WHERE configname = 'slug_prefix'";
        if(wpjobportaldb::query($query)){
             update_option('rewrite_rules', '');
            return WPJOBPORTAL_SAVED;
        }else{
             update_option('rewrite_rules', '');
            return WPJOBPORTAL_SAVE_ERROR;
        }
    }

    function saveHomePrefix($wpjobportal_data) {
        if (empty($wpjobportal_data)) {
            return false;
        }
        $wpjobportal_data['prefix'] = sanitize_title($wpjobportal_data['prefix']);
        if($wpjobportal_data['prefix'] == ''){
            return WPJOBPORTAL_SAVE_ERROR;
        }
        $query = "UPDATE " . wpjobportal::$_db->prefix . "wj_portal_config
                    SET configvalue = '".esc_sql($wpjobportal_data['prefix'])."'
                    WHERE configname = 'home_slug_prefix'";
        if(wpjobportaldb::query($query)){
            update_option('rewrite_rules', '');
            return WPJOBPORTAL_SAVED;
        }else{
             update_option('rewrite_rules', '');
            return WPJOBPORTAL_SAVE_ERROR;
        }
    }

    function resetAllSlugs() {
        $query = "UPDATE " . wpjobportal::$_db->prefix . "wj_portal_slug
                    SET slug = defaultslug ";
        if(wpjobportaldb::query($query)){
            update_option('rewrite_rules', '');
            return WPJOBPORTAL_SAVED;
        }else{
             update_option('rewrite_rules', '');
            return WPJOBPORTAL_SAVE_ERROR;
        }
    }

    function getOptionsForEditSlug() {

        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'get-options-for-edit-slug') ) {
            die( 'Security check Failed' );
        }
        $wpjobportal_slug = WPJOBPORTALrequest::getVar('slug');
        $wpjobportal_html = '<span class="popup-top">
                    <span id="popup_title" >' . esc_html(__("Edit","wp-job-portal"))." ". esc_html(__("Slug", "wp-job-portal")) . '</span>
                        <img id="popup_cross" alt="popup cross" onClick="closePopup();" src="' . esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/images/popup-close.png"></span>';

        $wpjobportal_html .= '<div class="popup-field-wrapper">
                    <div class="popup-field-title">' . esc_html(__('Slug','wp-job-portal')).' '. esc_html(__('Name', 'wp-job-portal')) . ' <span style="color: red;"> *</span></div>
                         <div class="popup-field-obj">' . WPJOBPORTALformfield::text('slugedit', isset($wpjobportal_slug) ? wpjobportalphplib::wpJP_trim($wpjobportal_slug) : 'text',  array('class' => 'inputbox one', 'data-validation' => 'required')) . '</div>
                    </div>';
        $wpjobportal_html .='<div class="popup-act-btn-wrp">
                    ' . WPJOBPORTALformfield::button('save', esc_html(__('Save', 'wp-job-portal')), array('class' => 'button savebutton popup-act-btn','onClick'=>'getFieldValue();'));
        $wpjobportal_html .='</div>';
        return wp_json_encode($wpjobportal_html);
    }

    function getDefaultSlugFromSlug($wpjobportal_layout) {
        $query = "SELECT  defaultslug FROM `".wpjobportal::$_db->prefix."wj_portal_slug` WHERE slug = '".esc_sql($wpjobportal_layout)."'";
        $wpjobportal_val = wpjobportal::$_db->get_var($query);
        return sanitize_title($wpjobportal_val);
    }

    function getSlugFromFileName($wpjobportal_layout,$wpjobportal_module) {
        $where_query = '';
        if($wpjobportal_layout == 'controlpanel'){
            if($wpjobportal_module == 'jobseeker'){
                $where_query = " AND defaultslug = 'jobseeker-control-panel'";
            }elseif($wpjobportal_module == 'employer'){
                $where_query = " AND defaultslug = 'employer-control-panel'";
            }
        }
        if($wpjobportal_layout == 'mystats'){
            if($wpjobportal_module == 'jobseeker'){
                $where_query = " AND defaultslug = 'jobseeker-my-stats'";
            }elseif($wpjobportal_module == 'employer'){
                $where_query = " AND defaultslug = 'employer-my-stats'";
            }
        }
        $query = "SELECT slug FROM `".wpjobportal::$_db->prefix."wj_portal_slug` WHERE filename = '".esc_sql($wpjobportal_layout)."' ".$where_query;
        $wpjobportal_val = wpjobportal::$_db->get_var($query);
        return $wpjobportal_val;
    }

    function getSlugString($home_page = 0) {

            //$query = "SELECT slug AS value, pkey AS akey FROM `".wpjobportal::$_db->prefix."wj_portal_slug`";
            global $wp_rewrite;
            $rules = wp_json_encode($wp_rewrite->rules);
            $query = "SELECT slug AS value FROM `".wpjobportal::$_db->prefix."wj_portal_slug`";
            $wpjobportal_val = wpjobportal::$_db->get_results($query);
            $wpjobportal_string = '';
            $bstring = '';
            //$rules = wp_json_encode($rules);
            $wpjobportal_prefix = WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue('slug_prefix');
            $homeprefix = WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue('home_slug_prefix');
            foreach ($wpjobportal_val as $wpjobportal_slug) {
                    if($home_page == 1){
                        $wpjobportal_slug->value = $homeprefix.$wpjobportal_slug->value;
                    }
                    if(wpjobportalphplib::wpJP_strpos($rules,$wpjobportal_slug->value) === false){
                        $wpjobportal_string .= $bstring. $wpjobportal_slug->value;
                    }else{
                        $wpjobportal_string .= $bstring.$wpjobportal_prefix. $wpjobportal_slug->value;
                    }
                $bstring = '|';
            }
        return $wpjobportal_string;
    }

    function getRedirectCanonicalArray() {
        global $wp_rewrite;
        $wpjobportal_slug_prefix = WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue('slug_prefix');
        $homeprefix = WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue('home_slug_prefix');
        $rules = wp_json_encode($wp_rewrite->rules);
        $query = "SELECT slug AS value FROM `".wpjobportal::$_db->prefix."wj_portal_slug`";
        $wpjobportal_val = wpjobportal::$_db->get_results($query);
        $wpjobportal_string = array();
        $bstring = '';
        foreach ($wpjobportal_val as $wpjobportal_slug) {
            $wpjobportal_slug->value = $homeprefix.$wpjobportal_slug->value;
            $wpjobportal_string[] = $bstring.$wpjobportal_slug->value;
            $bstring = '/';
        }
        return $wpjobportal_string;
    }

    // setcookies for search form data
    //search cookies data
    function getSearchFormData(){
        $wpjobportal_jsjp_search_array = array();
        $wpjobportal_jsjp_search_array['slug'] = WPJOBPORTALrequest::getVar("slug");
        $wpjobportal_jsjp_search_array['search_from_slug'] = 1;
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
        if($wpjp_search_cookie_data != '' && isset($wpjp_search_cookie_data['search_from_slug']) && $wpjp_search_cookie_data['search_from_slug'] == 1){
            $wpjobportal_jsjp_search_array['slug'] = $wpjp_search_cookie_data['slug'];
        }
        return $wpjobportal_jsjp_search_array;
    }

    function setSearchVariableForSearch($wpjobportal_jsjp_search_array){
        wpjobportal::$_search['slug']['slug'] = isset($wpjobportal_jsjp_search_array['slug']) ? $wpjobportal_jsjp_search_array['slug'] : '';
    }

    function getMessagekey(){
        $wpjobportal_key = 'slug';if(wpjobportal::$_common->wpjp_isadmin()){$wpjobportal_key = 'admin_'.$wpjobportal_key;}return $wpjobportal_key;
    }


}

?>
