<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALhandlesearchcookies {
    public $_jsjp_search_array;
    public $_callfrom;
    public $_setcookies;
    private $_for;
    private $lastToken;

    function __construct( ) {
        $this->_jsjp_search_array = array();
        $this->_callfrom = 3; // 3 means cookies will be reset
        $this->_setcookies = false;
        $this->init();
    }

    /**
     * Search data is now saved in a transient so it can be restored later.
     * This lets us keep filters, sorting, and page number when a user views
     * a detail page and then clicks “Back to Listing.
     * Users don’t lose their search results when moving between
     * listings and detail views.
     */

    private function sanitizeFor($for) {
        $wpjobportal_allowed = array('jobs','job','myresume','resumes','resume','category','city','country','currency','company','state'); // extendable later
        $for     = strtolower(trim((string) $for));
        return in_array($for, $wpjobportal_allowed, true) ? $for : 'job';
    }

    function init(){
        // set/remove any transients in cookies (existing behavior)
        $this->setCookiesFromTransientData();
        $this->removeCookiesFromTransientData();

        $wpjobportal_isadmin = wpjobportal::$_common->wpjp_isadmin();
        $wpjobportal_jstlay = '';
        $page = WPJOBPORTALrequest::getVar('page');
        $wpjobportallt = WPJOBPORTALrequest::getVar('wpjobportallt');
        $wpjobportallay = WPJOBPORTALrequest::getVar('wpjobportallay');
        if($page != '' ){ // page is for admin case
            $wpjobportal_jstlay = $page;
        }elseif($wpjobportallt !=''){// for layouts
            $wpjobportal_jstlay = $wpjobportallt;
        }elseif($wpjobportallay !=''){ // is for search, pagiantion and top sorting case
            $wpjobportal_jstlay = $wpjobportallay;
        }

        $wpjobportal_layoutname = wpjobportalphplib::wpJP_explode("wpjobportal_", $wpjobportal_jstlay);// admin page has wpjobportal_ prefix
        if(isset($wpjobportal_layoutname[1])){
            $wpjobportal_jstlay = $wpjobportal_layoutname[1];
        }

        $from_search = WPJOBPORTALrequest::getVar('WPJOBPORTAL_form_search');
        $wpjobportal_job_portal_search = WPJOBPORTALrequest::getVar('from_search');

        // pagenum
        $wpjobportal_pagenum = WPJOBPORTALrequest::getVar('pagenum', 'get', null);

        if( $from_search != '' && $from_search == 'WPJOBPORTAL_SEARCH'){ // search form is submitted set callfrom =1 to set values in cookie
            $this->_callfrom = 1;
        }elseif( $wpjobportal_job_portal_search != '' && $wpjobportal_job_portal_search == 'WPJOBPORTAL_SEARCH'){ // search form is submitted set callfrom =1 to set values in cookie
            $this->_callfrom = 1;
        }elseif($wpjobportal_pagenum != null){ // pagination case
            $this->_callfrom = 2;
        }

        // to handle the case of sorting not working on layouts
        if($this->_callfrom == 3){
            $sorton = WPJOBPORTALrequest::getVar('sorton','post',0);
            if(is_numeric($sorton) && $sorton > 0){
                $this->_callfrom = 1;
            }else{
                $sortby = WPJOBPORTALrequest::getVar('sortby','post',0);
                if(is_numeric($sortby) && $sortby > 0){
                    $this->_callfrom = 1;
                }
            }
        }

        if($wpjobportal_jstlay == ''){ // to handle the case of theme pages with SEF URLs
            global $post;

            $current_url = add_query_arg(array(), get_permalink());

            // Get the post ID from the URL
            $post_id = url_to_postid($current_url);

            $wpjobportal_content = get_post_field('post_content', $post_id);

            $shortcode = $wpjobportal_content;

            // Define the regular expression pattern to extract the page attribute
            $pattern = '/\[jp_job_portal_theme_pages\s+([^\]]+)\]/';

            // Match the pattern in the shortcode
            preg_match($pattern, $shortcode, $wpjobportal_matches);

            // Check if the matches are found
            if (isset($wpjobportal_matches[1])) {
                $jp_job_attributes = shortcode_parse_atts($wpjobportal_matches[1]);

                // Extract the 'page' attribute value
                $page_value = isset($jp_job_attributes['page']) ? $jp_job_attributes['page'] : '';

                // Use the extracted 'page' value
                if($page_value != '' && is_numeric($page_value)){
                  $wpjobportal_jstlay = $this->getLayoutValueFromPageNum($page_value);
                }
            }
        }

        // Determine context prefer explicit _for param, fallback to 'job'
        $this->_for = $this->sanitizeFor($wpjobportal_jstlay);

        $restore_token = WPJOBPORTALrequest::getVar('wpjobportal_restore_results','','');
        $wpjobportal_ignore_call_from_case = 0;
        if ($restore_token != '') { // checking id restore value is set in param
            $restored = $this->restoreSearchArray(sanitize_text_field($restore_token));

            if (!empty($restored) && is_array($restored)) {
                foreach ($restored as $wpjobportal_key => $wpjobportal_value) {
                    $wpjobportal_key = sanitize_text_field($wpjobportal_key);
                    $wpjobportal_value = sanitize_text_field($wpjobportal_value);
                    if($wpjobportal_key != '' && $wpjobportal_value !=''){
                        // in return case then next paginatino stops working. bottom code handles that
                        if($wpjobportal_key == 'backlink_pagenum'){
                            if(is_numeric($wpjobportal_pagenum) && $wpjobportal_pagenum != $wpjobportal_value){
                                $wpjobportal_value = $wpjobportal_pagenum;
                                $wpjobportal_ignore_call_from_case = 1;
                            }
                        }
                        wpjobportal::$_data['sanitized_args'][$wpjobportal_key] = $wpjobportal_value;
                    }
                }
                if($wpjobportal_ignore_call_from_case == 0){
                    $this->_callfrom = 1;// to handle some issues without modifying too much code
                }
            }
        }


        switch($wpjobportal_jstlay){
            case 'jobs':
            case 'job':
                $this->searchdataforjobs();
            break;
            case 'myresume':
            case 'resumes':
            case 'resume':
                $this->searchFormDataForResume($wpjobportal_jstlay);
            break;
            case 'appliedjobs': // for jobseeker case
            case 'myjobs': // For employer case
            case 'activitylog': // For activity log
            // to handle the sorting and search on these pages.
            case 'myappliedjobs': // for jobseeker case
                $this->searchFormDataForCommonData($wpjobportal_jstlay);
            break;
            case 'careerlevel':
                if(is_admin())
                    $this->searchFormDataForCareerLevel();
            break;
            case 'category':
                if(is_admin())
                    $this->searchFormDataForCategory();
            break;
            case 'city':
                if(is_admin())
                    $this->searchFormDataForCity();
            break;
            case 'country':
                if(is_admin())
                    $this->searchFormDataForCountry();
            break;
            case 'currency':
            case 'fieldordering':
            case 'highesteducation':
            case 'user':
            case 'state':
            case 'slug':
            case 'salaryrangetype':
            case 'jobstatus':
            case 'jobtype':
                if(is_admin()){
                    $this->setSearchFormData($wpjobportal_jstlay);
                }
            break;
            case 'departments':
            case 'jobapply':
            case 'coverletter':
            case 'invoice':
            case 'purchasehistory':
            case 'folder':
            case 'jobalert':
            case 'message':
            case 'company':
            case 'mycompany':
            case 'tag':
            case 'jobappliedresume': //there was a duplicate in the above code
            case 'companies':
            case 'controlpanel':
                    $this->setSearchFormDataAdminListing();
            break;

            default:
                if($wpjobportal_jstlay != '' ){ // avoid deleting cookies for wordpress internal call
                    wpjobportal::removeusersearchcookies();
                }
            break;
        }

        if($this->_setcookies){
            wpjobportal::wpjobportal_setusersearchcookies($this->_setcookies,$this->_jsjp_search_array);
            // preserved original behavior but commented out transient debug
        }

        // Save new search state only if we built a fresh search array (not when restored earlier)
        if (( !empty($this->_jsjp_search_array) && empty($restore_token) ) || $this->_callfrom == 2 ) {

            $wpjobportal_array_to_be_stored = $this->_jsjp_search_array;

            //ignore and clean array for actaul values needed to make sure when to remove transients
            if(isset($wpjobportal_array_to_be_stored['sorton'])){
                unset($wpjobportal_array_to_be_stored['sorton']);
            }
            if(isset($wpjobportal_array_to_be_stored['sortby'])){
                unset($wpjobportal_array_to_be_stored['sortby']);
            }

            $cleanArray = array_filter($wpjobportal_array_to_be_stored, function ($wpjobportal_value) {
                return $wpjobportal_value !== null && $wpjobportal_value !== '';
            });

            if(count($cleanArray) > 1 || !empty($wpjobportal_pagenum) ){ // arrray has atleast 2 elements (1 element is always form idetity e,g (search_from_jobs,search_from_resume))
                $wpjobportal_array_to_be_stored = $cleanArray;
                if(!empty($wpjobportal_pagenum)){ // add page number to array to go back to specific page
                    $wpjobportal_array_to_be_stored['backlink_pagenum'] = $wpjobportal_pagenum;
                }
                $this->lastToken = $this->saveSearchArray($wpjobportal_array_to_be_stored);
            }elseif(($wpjobportal_jstlay != '' && ( $wpjobportallt == '' || $wpjobportallt == $wpjobportal_jstlay) && $wpjobportal_pagenum == '') || ( $from_search != '' && $from_search == 'WPJOBPORTAL_SEARCH')){ //this code is required to clear tranient when a new listing is opened. (it wont delte for detail or form cases only for fresh cases)
                $wpjobportal_token = WPJOBPORTALincluder::getJSModel('common')->getUniqueIdForTransient();
                delete_transient('current_user_token_'.$wpjobportal_jstlay.'_'.$wpjobportal_token);
            }
        }

    }

    private function searchdataforjobs(){
        $wpjobportal_search_userfields = array();
        // $wpjobportal_search_userfields = JSSTincluder::getObjectClass('customfields')->userFieldsForSearch(1);
        if($this->_callfrom == 1 || $this->_callfrom == 3){ //  3 for theme
            if(is_admin()){
                $this->_jsjp_search_array = WPJOBPORTALincluder::getJSModel('job')->getAdminJobSearchFormData($wpjobportal_search_userfields);
            }else{
                $this->_jsjp_search_array = WPJOBPORTALincluder::getJSModel('job')->getFrontSideJobSearchFormData($wpjobportal_search_userfields);
            }
            $this->_setcookies = true;
        }elseif($this->_callfrom == 2){
            $this->_jsjp_search_array = WPJOBPORTALincluder::getJSModel('job')->getCookiesSavedSearchDataJob($wpjobportal_search_userfields);
        }
        WPJOBPORTALincluder::getJSModel('job')->setSearchVariableForJob($this->_jsjp_search_array,$wpjobportal_search_userfields);
    }

    private function searchFormDataForResume($wpjobportal_layout){
        if($this->_callfrom == 1 || $this->_callfrom == 3){ // 3 for theme
            if(is_admin()){
                $this->_jsjp_search_array = WPJOBPORTALincluder::getJSModel('resume')->getAdminResumeSearchFormData();
            }else{
                $this->_jsjp_search_array = WPJOBPORTALincluder::getJSModel('resume')->getMyResumeSearchFormData($wpjobportal_layout);
            }
            $this->_setcookies = true;
        }elseif($this->_callfrom == 2){
            $this->_jsjp_search_array = WPJOBPORTALincluder::getJSModel('resume')->getResumeSavedCookiesData($wpjobportal_layout);
        }
        if(is_admin()){
            WPJOBPORTALincluder::getJSModel('resume')->setSearchVariableForAdminResume($this->_jsjp_search_array,$wpjobportal_layout);
        }else{
            WPJOBPORTALincluder::getJSModel('resume')->setSearchVariableForMyResume($this->_jsjp_search_array,$wpjobportal_layout);
        }
    }

    private function searchFormDataForCommonData($wpjobportal_jstlay){
        if($this->_callfrom == 1){
            $this->_jsjp_search_array = WPJOBPORTALincluder::getJSModel('common')->getSearchFormDataOnlySort($wpjobportal_jstlay);
            $this->_setcookies = true;
        }elseif($this->_callfrom == 2){
            $this->_jsjp_search_array = WPJOBPORTALincluder::getJSModel('common')->getCookiesSavedOnlySortandOrder();
        }
        WPJOBPORTALincluder::getJSModel('common')->setSearchVariableOnlySortandOrder($this->_jsjp_search_array,$wpjobportal_jstlay);
    }

    private function searchFormDataForCompanies(){
        if($this->_callfrom == 1){
            if(is_admin()){
                $this->_jsjp_search_array = WPJOBPORTALincluder::getJSModel('company')->getSearchFormAdminCompanyData();
            }else{
                $this->_jsjp_search_array = WPJOBPORTALincluder::getJSModel('company')->getSearchFormDataMyCompany();
            }
            $this->_setcookies = true;
        }elseif($this->_callfrom == 2){
            if(is_admin()){
                $this->_jsjp_search_array = WPJOBPORTALincluder::getJSModel('company')->getAdminCompanySavedCookies();
            }else{
                $this->_jsjp_search_array = WPJOBPORTALincluder::getJSModel('company')->getCookiesSavedMyCompany();
            }
        }
        if(is_admin()){
            WPJOBPORTALincluder::getJSModel('company')->setAdminCompanySearchVariable($this->_jsjp_search_array);
        }else{
            WPJOBPORTALincluder::getJSModel('company')->setSearchVariableMyCompany($this->_jsjp_search_array);
        }
    }

    private function searchFormDataForCareerLevel(){
        if($this->_callfrom == 1){
            $this->_jsjp_search_array = WPJOBPORTALincluder::getJSModel('careerlevel')->getSearchFormDataCareerLevel();
            $this->_setcookies = true;
        }elseif($this->_callfrom == 2){
            $this->_jsjp_search_array = WPJOBPORTALincluder::getJSModel('careerlevel')->getCookiesSavedCareerLevel();
        }
        WPJOBPORTALincluder::getJSModel('careerlevel')->setSearchVariableCareerLevel($this->_jsjp_search_array);
    }

    private function searchFormDataForCategory(){
        if($this->_callfrom == 1){
            $this->_jsjp_search_array = WPJOBPORTALincluder::getJSModel('category')->getSearchFormDataCategory();
            $this->_setcookies = true;
        }elseif($this->_callfrom == 2){
            $this->_jsjp_search_array = WPJOBPORTALincluder::getJSModel('category')->getCookiesSavedCategory();
        }
        WPJOBPORTALincluder::getJSModel('category')->setSearchVariableCategory($this->_jsjp_search_array);
    }

    private function searchFormDataForCity(){
        if($this->_callfrom == 1){
            $this->_jsjp_search_array = WPJOBPORTALincluder::getJSModel('city')->getSearchFormDataCity();
            $this->_setcookies = true;
        }elseif($this->_callfrom == 2){
            $this->_jsjp_search_array = WPJOBPORTALincluder::getJSModel('city')->getCookiesSavedCity();
        }
        WPJOBPORTALincluder::getJSModel('city')->setSearchVariableCity($this->_jsjp_search_array);
    }

    private function searchFormDataForCountry(){
        if($this->_callfrom == 1){
            $this->_jsjp_search_array = WPJOBPORTALincluder::getJSModel('country')->getCountrySearchFormData();
            $this->_setcookies = true;
        }elseif($this->_callfrom == 2){
            $this->_jsjp_search_array = WPJOBPORTALincluder::getJSModel('country')->getCountrySavedCookiesData();
        }
        WPJOBPORTALincluder::getJSModel('country')->setCountrySearchVariable($this->_jsjp_search_array);
    }

    private function setSearchFormData($wpjobportal_module){
        if($this->_callfrom == 1){
            $this->_jsjp_search_array = WPJOBPORTALincluder::getJSModel($wpjobportal_module)->getSearchFormData();
            $this->_setcookies = true;
        }elseif($this->_callfrom == 2){
            $this->_jsjp_search_array = WPJOBPORTALincluder::getJSModel($wpjobportal_module)->getSavedCookiesDataForSearch();
        }
        WPJOBPORTALincluder::getJSModel($wpjobportal_module)->setSearchVariableForSearch($this->_jsjp_search_array);
    }

    private function setSearchFormDataAdminListing(){
        if($this->_callfrom == 1){
            $this->_jsjp_search_array = WPJOBPORTALincluder::getJSModel('common')->getSearchFormDataAdmin();
            $this->_setcookies = true;
        }elseif($this->_callfrom == 2){
            $this->_jsjp_search_array = WPJOBPORTALincluder::getJSModel('common')->getCookiesSavedAdmin();
        }
        WPJOBPORTALincluder::getJSModel('common')->setSearchVariableAdmin($this->_jsjp_search_array);
    }

    private function setCookiesFromTransientData(){
        $wpjobportal_user_data  =  get_transient( 'wpjobportal-social-login-data');
        //echo 'printing tranient data from handlecookies class 248 <pre>';print_r($wpjobportal_user_data);echo '</pre>';
        if( $wpjobportal_user_data !== FALSE){ // it will be false if transient does not exsist
            if($wpjobportal_user_data != '' && is_array($wpjobportal_user_data) && !empty($wpjobportal_user_data)){
                if (!isset($_COOKIE['wpjobportal-socialid'])){
                    wpjobportalphplib::wpJP_setcookie('wpjobportal-socialid' , $wpjobportal_user_data['socialid'] , time() + 1209600 , COOKIEPATH);
                    if ( SITECOOKIEPATH != COOKIEPATH ){
                        wpjobportalphplib::wpJP_setcookie('wpjobportal-socialid' , $wpjobportal_user_data['socialid'] , time() + 1209600 , SITECOOKIEPATH);
                    }
                }
                if (!isset($_COOKIE['wpjobportal-socialfirstname'])){
                    wpjobportalphplib::wpJP_setcookie('wpjobportal-socialfirstname' , $wpjobportal_user_data['socialfirstname'] , time() + 1209600 , COOKIEPATH);
                    if ( SITECOOKIEPATH != COOKIEPATH ){
                       wpjobportalphplib::wpJP_setcookie('wpjobportal-socialfirstname' , $wpjobportal_user_data['socialfirstname'] , time() + 1209600 , SITECOOKIEPATH);
                    }
                }
                if (!isset($_COOKIE['wpjobportal-sociallastname'])){
                    wpjobportalphplib::wpJP_setcookie('wpjobportal-sociallastname' , $wpjobportal_user_data['sociallastname'], time() + 1209600 , COOKIEPATH);
                    if ( SITECOOKIEPATH != COOKIEPATH ){
                       wpjobportalphplib::wpJP_setcookie('wpjobportal-sociallastname' , $wpjobportal_user_data['sociallastname'], time() + 1209600 , SITECOOKIEPATH);
                    }
                }
                if (!isset($_COOKIE['wpjobportal-socialemail'])){
                    wpjobportalphplib::wpJP_setcookie('wpjobportal-socialemail' , $wpjobportal_user_data['socialemail'], time() + 1209600 , COOKIEPATH);
                    if ( SITECOOKIEPATH != COOKIEPATH ){
                    wpjobportalphplib::wpJP_setcookie('wpjobportal-socialemail' , $wpjobportal_user_data['socialemail'], time() + 1209600 , SITECOOKIEPATH);
                    }
                }
                if (!isset($_COOKIE['wpjobportal-socialmedia'])){
                    wpjobportalphplib::wpJP_setcookie('wpjobportal-socialmedia' , $wpjobportal_user_data['socialmedia'], time() + 1209600 , COOKIEPATH);
                    if ( SITECOOKIEPATH != COOKIEPATH ){
                    wpjobportalphplib::wpJP_setcookie('wpjobportal-socialmedia' , $wpjobportal_user_data['socialmedia'], time() + 1209600 , SITECOOKIEPATH);
                    }
                }
                delete_transient('wpjobportal-social-login-data');// removing transient to avoid re creating cookie on every call
            }
        }
    }

    private function removeCookiesFromTransientData(){
        $remove_coookies  =  get_transient( 'wpjobportal-social-login-logout-cookies');
        if( $remove_coookies !== FALSE){ // it will be false if transient does not exsist
            if($remove_coookies != '' && !empty($remove_coookies) && $remove_coookies == 'remove-cookies' ){

                if(isset($_COOKIE['wpjobportal-socialid'])){
                    wpjobportalphplib::wpJP_setcookie('wpjobportal-socialid' , '' , time() - 3600 , COOKIEPATH);
                    if ( SITECOOKIEPATH != COOKIEPATH ){
                        wpjobportalphplib::wpJP_setcookie('wpjobportal-socialid' , '' , time() - 3600 , SITECOOKIEPATH);
                    }
                    unset($_COOKIE['wpjobportal-socialid']);
                }

                if(isset($_COOKIE['wpjobportal-socialfirstname'])){
                    wpjobportalphplib::wpJP_setcookie('wpjobportal-socialfirstname' , '' , time() - 3600 , COOKIEPATH);
                    if ( SITECOOKIEPATH != COOKIEPATH ){
                       wpjobportalphplib::wpJP_setcookie('wpjobportal-socialfirstname' , '' , time() - 3600 , SITECOOKIEPATH);
                    }
                    unset($_COOKIE['wpjobportal-socialfirstname']);
                }

                if(isset($_COOKIE['wpjobportal-sociallastname'])){
                    wpjobportalphplib::wpJP_setcookie('wpjobportal-sociallastname' , '', time() - 3600 , COOKIEPATH);
                    if ( SITECOOKIEPATH != COOKIEPATH ){
                       wpjobportalphplib::wpJP_setcookie('wpjobportal-sociallastname' , '', time() - 3600 , SITECOOKIEPATH);
                    }
                    unset($_COOKIE['wpjobportal-sociallastname']);
                }

                if(isset($_COOKIE['wpjobportal-socialemail'])){
                    wpjobportalphplib::wpJP_setcookie('wpjobportal-socialemail' , '', time() - 3600 , COOKIEPATH);
                    if ( SITECOOKIEPATH != COOKIEPATH ){
                        wpjobportalphplib::wpJP_setcookie('wpjobportal-socialemail' , '', time() - 3600 , SITECOOKIEPATH);
                    }
                    unset($_COOKIE['wpjobportal-socialemail']);
                }

                if(isset($_COOKIE['wpjobportal-socialmedia'])){
                    wpjobportalphplib::wpJP_setcookie('wpjobportal-socialmedia' , '', time() - 3600 , COOKIEPATH);
                    if ( SITECOOKIEPATH != COOKIEPATH ){
                        wpjobportalphplib::wpJP_setcookie('wpjobportal-socialmedia' , '', time() - 3600 , SITECOOKIEPATH);

                    }
                    unset($_COOKIE['wpjobportal-socialmedia']);
                }

                delete_transient('wpjobportal-social-login-logout-cookies');// removing transient to avoid re creating cookie on every call
            }
        }
    }

    public function getLayoutValueFromPageNum($wpjobportal_pagenum){
        $wpjobportal_layout = 'jobs'; // sensible default
        switch($wpjobportal_pagenum){
            case 1: // jobseeker control panel
                $wpjobportal_module = 'jobseeker';
                $wpjobportal_layout = 'controlpanel';
            break;
            case 2: // newest job
                $wpjobportal_module = 'job';
                $wpjobportal_layout = 'jobs';
            break;
            case 3: // job search
                $wpjobportal_module = 'jobsearch';
                $wpjobportal_layout = 'jobsearch';
            break;
            case 4: // jobs by category
                $wpjobportal_module = 'job';
                $wpjobportal_layout = 'jobsbycategories';
            break;
            case 5: // shortlited jobs
                $wpjobportal_module = 'shortlist';
                $wpjobportal_layout = 'shortlistedjobs';
            break;
            case 6: // add resume
                $wpjobportal_module = in_array('multiresume', wpjobportal::$_active_addons) ? 'multiresume' : 'resume';
                $wpjobportal_layout = 'addresume';
            break;
            case 7: // my resume
                $wpjobportal_module = in_array('multiresume', wpjobportal::$_active_addons) ? 'multiresume' : 'resume';
                $wpjobportal_layout = 'myresumes';
            break;
            case 8: // my applied jobs
                $wpjobportal_module = 'jobapply';
                $wpjobportal_layout = 'myappliedjobs';
            break;
            case 9: // job alert
                $wpjobportal_module = 'jobalert';
                $wpjobportal_layout = 'jobalert';
            break;
            case 10: // company list
                    $wpjobportal_module = in_array('multicompany', wpjobportal::$_active_addons) ? 'multicompany' : 'company';
                    $wpjobportal_layout = 'companies';
            break;
            case 11: // jobseeker messages
                $wpjobportal_module = 'message';
                $wpjobportal_layout = 'jobseekermessages';
            break;
            case 12: // jobseeker registration
                $wpjobportal_module = 'user';
                $wpjobportal_layout = 'regjobseeker';
            break;
            case 13: // employer controlpanel
                $wpjobportal_module = 'employer';
                $wpjobportal_layout = 'controlpanel';
            break;
            case 14: // add company
                $wpjobportal_module = in_array('multicompany', wpjobportal::$_active_addons) ? 'multicompany' : 'company';
                $wpjobportal_layout = 'addcompany';
            break;
            case 15: // my companies
                $wpjobportal_module = in_array('multicompany', wpjobportal::$_active_addons) ? 'multicompany' : 'company';
                $wpjobportal_layout = 'mycompanies';
            break;
            case 16: // add job
                $wpjobportal_module = 'job';
                $wpjobportal_layout = 'addjob';
            break;
            case 17: // my jobs
                $wpjobportal_module = 'job';
                $wpjobportal_layout = 'myjobs';
            break;
            case 18: // resume list
                $wpjobportal_module = 'resumesearch';
                $wpjobportal_layout = 'resumes';
            break;
            case 19: // resume search
                $wpjobportal_module = 'resumesearch';
                $wpjobportal_layout = 'resumesearch';
            break;
            case 20: // resume save search
                $wpjobportal_module = 'resumesearch';
                $wpjobportal_layout = 'resumesavesearch';
            break;
            case 21: // resume by category
                $wpjobportal_module = 'resume';
                $wpjobportal_layout = 'resumebycategory';
            break;
            case 22: // employer messages
                $wpjobportal_module = 'message';
                $wpjobportal_layout = 'employermessages';
            break;
            case 23: // employer registration
                $wpjobportal_module = 'user';
                $wpjobportal_layout = 'regemployer';
            break;
            case 24: // login
                $wpjobportal_module = 'wpjobportal';
                $wpjobportal_layout = 'login';
            break;

            case 25: // featured jobs
                $wpjobportal_module = 'featuredjob';
                $wpjobportal_layout = 'featuredjobs';
            break;
            case 26: // feauted resume
                $wpjobportal_module = 'featureresume';
                $wpjobportal_layout = 'featuredresumes';
            break;
            case 27: // feauted companies
                $wpjobportal_module = 'featuredcompany';
                $wpjobportal_layout = 'featuredcompanies';
            break;
            case 28: // all companies
                $wpjobportal_module = 'allcompanies';
                $wpjobportal_layout = 'companies';
            break;
            case 29: // all resumes
                $wpjobportal_module = 'allresumes';
                $wpjobportal_layout = 'resumes';
            break;

            // default handled by initial assignment
            break;
        }
      return $wpjobportal_layout;
    }



    // =====================================================
    // =====================================================

        private function getUserKey() {
            $wpjobportal_key = WPJOBPORTALincluder::getJSModel('common')->getUniqueIdForTransient();
            return $wpjobportal_key;
        }

        private function buildKey($wpjobportal_token) {
            $wpjobportal_token = sanitize_text_field($wpjobportal_token);
            $wpjobportal_key = 'wpjp_' . $this->getUserKey() . '_' . $this->_for . '_' . $wpjobportal_token;
            return $wpjobportal_key;
        }

        public function saveSearchArray($wpjobportal_search_array) {
            // ensure we have a context
            if (empty($this->_for)) {
                $this->_for = 'job';
            }

            $wpjobportal_token = WPJOBPORTALincluder::getJSModel('common')->getUniqueIdForTransient();
            $this->setCurrentUsertransient($wpjobportal_token,$this->_for);
            // set_transient('current_user_token_'.$this->_for.'_'.$wpjobportal_token, $wpjobportal_token, 1800); // 30 minutes
            $wpjobportal_key   = $this->buildKey($wpjobportal_token);
            set_transient($wpjobportal_key, $wpjobportal_search_array, 1800); // 30 minutes
            return $wpjobportal_token;
        }

        public function restoreSearchArray($wpjobportal_token) {
            if (empty($wpjobportal_token)) return [];
            $wpjobportal_key  = $this->buildKey($wpjobportal_token);
            $wpjobportal_data = get_transient($wpjobportal_key);
            return is_array($wpjobportal_data) ? $wpjobportal_data : [];
        }

        public function getSearchArray() {
            return $this->_jsjp_search_array;
        }

        private function setCurrentUsertransient($wpjobportal_token, $for) {
            // Full list of valid cases
            $wpjobportal_all_cases = [
                'jobs','job','myresume','resumes','resume',
                'appliedjobs','myjobs','activitylog','myappliedjobs',
                'careerlevel','category','city','country','currency',
                'fieldordering','highesteducation','user','state','slug',
                'salaryrangetype','jobstatus','jobtype','departments',
                'jobapply','coverletter','invoice','purchasehistory',
                'folder','jobalert','message','company','mycompany',
                'tag','jobappliedresume','companies','controlpanel'
            ];

            foreach ($wpjobportal_all_cases as $case) {
                if ($case === $for) {
                    set_transient('current_user_token_'.$case.'_'.$wpjobportal_token, $wpjobportal_token, 1800); // 30 minutes
                } else {
                    delete_transient('current_user_token_'.$case.'_'.$wpjobportal_token);
                }
            }
        }


}
?>
