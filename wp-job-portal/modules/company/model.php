<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALCompanyModel {

    function getCompanies_Widget($wpjobportal_companytype, $wpjobportal_noofcompanies,$custom_company_ids_list = []) {
        if ((!is_numeric($wpjobportal_companytype)) || ( !is_numeric($wpjobportal_noofcompanies)))
            return false;

        if ($wpjobportal_companytype == 1) { // latest companies
            $wpjobportal_inquery = '  ';
        } elseif ($wpjobportal_companytype == 2) { // featured companeis
            $wpjobportal_inquery = ' AND company.isfeaturedcompany = 1 AND DATE(company.endfeatureddate) >= CURDATE() ';
        } elseif ($wpjobportal_companytype == 3) { // custom selection of companies
            // make sure the selection is not empty
            if (!empty($custom_company_ids_list) && is_array($custom_company_ids_list)) {
                $escaped_ids = array_map('intval', $custom_company_ids_list); // Sanitize Ids to int check
                $wpjobportal_inquery = ' AND company.id IN (' . implode(',', $escaped_ids) . ') ';
            } else {
                return []; // No companies selected
            }
        } else {
            return '';
        }

        $query = "SELECT  company.*, CONCAT(company.alias,'-',company.id) AS companyaliasid ,company.id AS companyid,company.logofilename AS companylogo
            FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company
            WHERE company.status = 1  ";
        $query .= $wpjobportal_inquery . " ORDER BY company.created DESC ";
        if ($wpjobportal_noofcompanies != -1 && is_numeric($wpjobportal_noofcompanies))
            $query .=" LIMIT " . esc_sql($wpjobportal_noofcompanies);
        $wpjobportal_results = wpjobportaldb::get_results($query);

        $wpjobportal_results = wpjobportaldb::get_results($query);
        foreach ($wpjobportal_results AS $d) {
            $d->location = WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($d->city);
            $d->noofjobs = WPJOBPORTALincluder::getJSModel('job')->getNumberOfJobsByCompany($d->companyid);
        }
        wpjobportal::$_data['config'] = wpjobportal::$_config->getConfigByFor('company');
        return $wpjobportal_results;
    }

    function getAllCompaniesForSearchForCombo() {
        $query = "SELECT id, name AS text FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` ORDER BY name ASC ";
        $wpjobportal_rows = wpjobportaldb::get_results($query);
        return $wpjobportal_rows;
    }

    function getCompanybyIdForView($wpjobportal_companyid) {
        if (is_numeric($wpjobportal_companyid) == false)
            return false;

        $query = "SELECT company.*,city.name AS cityname
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON company.city = city.id
                    WHERE  company.id = " . esc_sql($wpjobportal_companyid);
        wpjobportal::$_data[0] = wpjobportaldb::get_row($query);
        wpjobportal::$_data[0]->location = WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView(wpjobportal::$_data[0]->city);
        wpjobportal::$_data[2] = wpjobportal::$_wpjpfieldordering->getFieldsOrderingforView(1);
        wpjobportal::$_data[3] = wpjobportal::$_data[0]->params;
        $wpjobportal_company_contact_detail = wpjobportal::$_config->getConfigValue('company_contact_detail');

        // view contact details
        wpjobportal::$_data['companycontactdetail'] = false;
        if($wpjobportal_company_contact_detail == 0){ // no one is allowed to view contact details.
            wpjobportal::$_data['companycontactdetail'] = false;
        }elseif($wpjobportal_company_contact_detail == 1){ // everyone one is allowed to view contact details.
            wpjobportal::$_data['companycontactdetail'] = true;
        }elseif($wpjobportal_company_contact_detail == 2){ // logged in job seeker is allowed to view
            if (WPJOBPORTALincluder::getObjectClass('user')->isjobseeker()) { // if current use is looged in job seeker
                wpjobportal::$_data['companycontactdetail'] = true;
            }
        }elseif($wpjobportal_company_contact_detail == 3){ // if company contact details is set to paid in config
            // if user is guest or other then owner then make sure of contact detail on view company
            if (WPJOBPORTALincluder::getObjectClass('user')->isguest() || wpjobportal::$_data[0]->uid != WPJOBPORTALincluder::getObjectClass('user')->uid()) {
                if(in_array('credits',wpjobportal::$_active_addons)){
                    $wpjobportal_subType = wpjobportal::$_config->getConfigValue('submission_type');
                    if($wpjobportal_subType == 1){
                        wpjobportal::$_data['companycontactdetail'] = true;
                    }elseif($wpjobportal_subType == 2){
                        $wpjobportal_price = wpjobportal::$_config->getConfigValue('job_viewcompanycontact_price_perlisting');

                        if($wpjobportal_price > 0){
                            // Paid
                            wpjobportal::$_data['companycontactdetail'] = $this->checkAlreadyViewCompanyContactDetail($wpjobportal_companyid);
                        }else{
                            // Free
                            wpjobportal::$_data['companycontactdetail'] = true;
                        }
                    }elseif ($wpjobportal_subType == 3) {
                        $wpjobportal_uid = !empty($wpjobportal_uid) ? $wpjobportal_uid : WPJOBPORTALincluder::getObjectClass('user')->uid();
                        if (is_numeric($wpjobportal_uid) && $wpjobportal_uid > 0) {
                            $hasPackage = WPJOBPORTALincluder::getJSModel('credits')->checkIfPackageDefinedForUserRole($wpjobportal_uid);
                            if ($hasPackage == 0) {
                                wpjobportal::$_data['companycontactdetail'] = true;
                            } else {
                                wpjobportal::$_data['companycontactdetail'] = $this->checkAlreadyViewCompanyContactDetail($wpjobportal_companyid);
                            }
                        } else {
                            wpjobportal::$_data['companycontactdetail'] = $this->checkAlreadyViewCompanyContactDetail($wpjobportal_companyid);
                        }
                    }else{
                        wpjobportal::$_data['companycontactdetail'] = true;
                    }
                }
            }
        }
        // allow owner and admin to view contact details
        if (!WPJOBPORTALincluder::getObjectClass('user')->isguest() && (wpjobportal::$_common->wpjp_isadmin() || wpjobportal::$_data[0]->uid == WPJOBPORTALincluder::getObjectClass('user')->uid()) ) {
            wpjobportal::$_data['companycontactdetail'] = true;
        }
        //update the company view counter
        //DB class limitations
        $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_companies` SET hits = hits + 1 WHERE id = " . esc_sql($wpjobportal_companyid);
        wpjobportal::$_db->query($query);
        wpjobportal::$_data['config'] = wpjobportal::$_config->getConfigByFor('company');
        if(in_array('credits', wpjobportal::$_active_addons)){
            wpjobportal::$_data['paymentconfig'] = wpjobportal::$_wpjppaymentconfig->getPaymentConfigFor('paypal,stripe,woocommerce',true);
        }

        return;
    }

    public function checkAlreadyViewCompanyContactDetail($wpjobportal_companyid,$wpjobportal_data='') {
        $wpjobportal_userobject = WPJOBPORTALincluder::getObjectClass('user');

        if($wpjobportal_userobject->isguest() || !$wpjobportal_userobject->isWPJOBPORTALuser())
            return false;
        if (!is_numeric($wpjobportal_companyid))
            return false;

        if(current_user_can( 'manage_options' ) && !isset($wpjobportal_data['uid'])){
            return true;
        }
        if(isset($wpjobportal_data['uid'])){
           $wpjobportal_uid = $wpjobportal_data['uid'];
        }else{
            $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        }
        if(!is_numeric($wpjobportal_uid)) return false;
        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobseeker_view_company` WHERE companyid = " . esc_sql($wpjobportal_companyid) . " AND uid = " . esc_sql($wpjobportal_uid);
        $wpjobportal_result = wpjobportal::$_db->get_var($query);
        if ($wpjobportal_result > 0)
            return true;
        else
            return false;
    }

    function getCompanybyId($c_id) {

        if ($c_id)
            if (!is_numeric($c_id))
                return false;
        if ($c_id) {
            $query = "SELECT * FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` WHERE id =" . esc_sql($c_id);
            wpjobportal::$_data[0] = wpjobportaldb::get_row($query);
            if(wpjobportal::$_data[0] != ''){
                wpjobportal::$_data[0]->multicity = wpjobportal::$_common->getMultiSelectEdit($c_id, 2);
            }
        }
        wpjobportal::$_data[2] = wpjobportal::$_wpjpfieldordering->getFieldsOrderingforForm(WPJOBPORTAL_COMPANY); // company fields
        return;
    }


    function getMyCompanies($wpjobportal_uid) {
        if (!is_numeric($wpjobportal_uid)) return false;
        //Filters
        $wpjobportal_searchcompany = isset(wpjobportal::$_search['search_filter']['searchcompany']) ? wpjobportal::$_search['search_filter']['searchcompany'] : '';

        //Front end search var
        $wpjobportal_city = isset(wpjobportal::$_search['search_filter']['wpjobportal_city']) ? wpjobportal::$_search['search_filter']['wpjobportal_city'] : '';

        $wpjobportal_inquery = '';
        if ($wpjobportal_searchcompany) {
            $wpjobportal_inquery = " AND LOWER(company.name) LIKE '%".esc_sql($wpjobportal_searchcompany)."%'";
        }
        if ($wpjobportal_city) {
            if(is_numeric($wpjobportal_city)){
                $wpjobportal_inquery .= " AND LOWER(company.city) LIKE '%".esc_sql($wpjobportal_city)."%'";
            }else{
                $wpjobportal_arr = wpjobportalphplib::wpJP_explode( ',' , $wpjobportal_city);
                $cityQuery = false;
                foreach($wpjobportal_arr as $wpjobportal_i){
                    if($cityQuery){
                        $cityQuery .= " OR LOWER(company.city) LIKE '%".esc_sql($wpjobportal_i)."%' ";
                    }else{
                        $cityQuery = " LOWER(company.city) LIKE '%".esc_sql($wpjobportal_i)."%' ";
                    }
                }
                $wpjobportal_inquery .= " AND ( $cityQuery ) ";
            }
        }


        wpjobportal::$_data['filter']['wpjobportal-city'] = wpjobportal::$_common->getCitiesForFilter($wpjobportal_city);
        wpjobportal::$_data['filter']['searchcompany'] = $wpjobportal_searchcompany;


        //Pagination
        // to handle base plugin showing pagination (to accomodate data query below)
            $query = "SELECT COUNT(id) FROM " . wpjobportal::$_db->prefix . "wj_portal_companies AS company WHERE uid = " . esc_sql($wpjobportal_uid);
            $query .= $wpjobportal_inquery;
            $wpjobportal_total = wpjobportaldb::get_var($query);
        // to handle the case of show 1 in case of base plugin and 0 in case of not record found

        if(!in_array('multicompany', wpjobportal::$_active_addons)){
            if($wpjobportal_total > 1){
                $wpjobportal_total = 1;
            }
        }
        wpjobportal::$_data['total'] = $wpjobportal_total;
        wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($wpjobportal_total,'mycompany');
        //Data
        $query = "SELECT company.id,company.name,company.logofilename,CONCAT(company.alias,'-',company.id) AS aliasid,company.created,company.serverid,company.city,company.status,company.isfeaturedcompany
                 ,company.endfeatureddate,company.params,company.url,company.description,company.contactemail, company.description
                FROM " . wpjobportal::$_db->prefix . "wj_portal_companies AS company
                WHERE company.uid = " . esc_sql($wpjobportal_uid);
        $query .= $wpjobportal_inquery;
        if(in_array('multicompany', wpjobportal::$_active_addons)){
            $query .= " ORDER BY company.created DESC LIMIT " . WPJOBPORTALpagination::$_offset . "," . WPJOBPORTALpagination::$_limit;
        }else{
            $query .= " ORDER BY company.id ASC LIMIT 0,1";
        }
        wpjobportal::$_data[0] = wpjobportaldb::get_results($query);
        $wpjobportal_data = array();
        foreach (wpjobportal::$_data[0] AS $d) {
            $d->location = WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($d->city);
            $wpjobportal_data[] = $d;
        }
        wpjobportal::$_data[0] = $wpjobportal_data;
        wpjobportal::$wpjobportal_data['fields'] = wpjobportal::$_wpjpfieldordering->getFieldsOrderingforView(1);
        wpjobportal::$_data['config'] = wpjobportal::$_config->getConfigByFor('company');
        return;
    }

    function sorting() {
        $wpjobportal_pagenum = WPJOBPORTALrequest::getVar('pagenum');
        wpjobportal::$_data['sorton'] = wpjobportal::$_search['search_filter']['sorton'] != '' ? wpjobportal::$_search['search_filter']['sorton']: 3;
        wpjobportal::$_data['sortby'] = wpjobportal::$_search['search_filter']['sortby'] != '' ? wpjobportal::$_search['search_filter']['sortby']: 2;

        switch (wpjobportal::$_data['sorton']) {
            case 3: // created
                wpjobportal::$_data['sorting'] = ' company.created ';
                break;
            case 1: // company title
                wpjobportal::$_data['sorting'] = ' company.name ';
                break;
            case 2: // category
                wpjobportal::$_data['sorting'] = ' cat.cat_title ';
                break;
            case 4: // location
                wpjobportal::$_data['sorting'] = ' city.name ';
                break;
            case 5: // status
                wpjobportal::$_data['sorting'] = ' company.status ';
                break;
            default:
                //wpjobportal::$_data['sorting'] = ' company.created ';
            break;
        }
        if (wpjobportal::$_data['sortby'] == 1) {
            wpjobportal::$_data['sorting'] .= ' ASC ';
        } else {
            wpjobportal::$_data['sorting'] .= ' DESC ';
        }
        wpjobportal::$_data['combosort'] = wpjobportal::$_data['sorton'];
    }

    function getAllCompanies() {
        if(wpjobportal::$_common->wpjp_isadmin()){
            $this->sorting();
        }else{
            $this->getOrdering();
        }

        //Filters
        $wpjobportal_searchcompany = wpjobportal::$_search['search_filter']['searchcompany'];
        $wpjobportal_searchjobcategory = wpjobportal::$_search['search_filter']['searchjobcategory'];
        $wpjobportal_status = wpjobportal::$_search['search_filter']['status'];
        $wpjobportal_datestart = wpjobportal::$_search['search_filter']['datestart'];
        $wpjobportal_dateend = wpjobportal::$_search['search_filter']['dateend'];
        $featured = wpjobportal::$_search['search_filter']['featured'];
        //Front end search var
        $wpjobportal_company = wpjobportal::$_search['search_filter']['wpjobportal_company'];
        $wpjobportal_city = wpjobportal::$_search['search_filter']['wpjobportal_city'];
        if ($wpjobportal_searchjobcategory)
            if (is_numeric($wpjobportal_searchjobcategory) == false)
                return false;
        $wpjobportal_inquery = '';
        if ($wpjobportal_searchcompany) {
            $wpjobportal_inquery = " AND LOWER(company.name) LIKE '%".esc_sql($wpjobportal_searchcompany)."%'";
        }
        if ($wpjobportal_company) {
            $wpjobportal_inquery = " AND LOWER(company.name) LIKE '%".esc_sql($wpjobportal_company)."%'";
        }
        if ($wpjobportal_city) {
			if(is_numeric($wpjobportal_city)){
				$wpjobportal_inquery .= " AND company.city = ".esc_sql($wpjobportal_city)." ";
			}else{
				$wpjobportal_arr = wpjobportalphplib::wpJP_explode( ',' , $wpjobportal_city);
				$cityQuery = false;
				foreach($wpjobportal_arr as $wpjobportal_i){
                    if(is_numeric($wpjobportal_i)){
    					if($cityQuery){
    						$cityQuery .= " OR company.city = ".esc_sql($wpjobportal_i)." ";
    					}else{
    						$cityQuery = " company.city = ".esc_sql($wpjobportal_i)." ";
    					}
                    }
				}
				$wpjobportal_inquery .= " AND ( $cityQuery ) ";
			}
        }

        if (is_numeric($wpjobportal_status)) {
            $wpjobportal_inquery .= " AND company.status = " . esc_sql($wpjobportal_status);
        }

        if ($wpjobportal_datestart != null) {
            $wpjobportal_datestart = gmdate('Y-m-d',strtotime($wpjobportal_datestart));
            $wpjobportal_inquery .= " AND DATE(company.created) >= '" . esc_sql($wpjobportal_datestart) . "'";
        }

        if ($wpjobportal_dateend != null) {
            $wpjobportal_dateend = gmdate('Y-m-d',strtotime($wpjobportal_dateend));
            $wpjobportal_inquery .= " AND DATE(company.created) <= '" . esc_sql($wpjobportal_dateend) . "'";
        }
        $wpjobportal_curdate = gmdate('Y-m-d');
        if ($featured != null) {
           $wpjobportal_inquery .= apply_filters('wpjobportal_addons_search_feature_query',false);
        }

        wpjobportal::$_data['filter']['wpjobportal-company'] = $wpjobportal_company;
        wpjobportal::$_data['filter']['wpjobportal-city'] = wpjobportal::$_common->getCitiesForFilter($wpjobportal_city);
        wpjobportal::$_data['filter']['searchcompany'] = $wpjobportal_searchcompany;
        wpjobportal::$_data['filter']['searchjobcategory'] = $wpjobportal_searchjobcategory;
        wpjobportal::$_data['filter']['status'] = $wpjobportal_status;
        wpjobportal::$_data['filter']['datestart'] = $wpjobportal_datestart;
        wpjobportal::$_data['filter']['dateend'] = $wpjobportal_dateend;
        wpjobportal::$_data['filter']['featured'] = $featured;
        //Pagination
        $query = "SELECT COUNT(id) FROM " . wpjobportal::$_db->prefix . "wj_portal_companies AS company WHERE company.status != 0";
        $query .=$wpjobportal_inquery;

        $wpjobportal_total = wpjobportaldb::get_var($query);
        wpjobportal::$_data['total'] = $wpjobportal_total;
        wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($wpjobportal_total);

        //Data
        $query = "SELECT company.uid,company.name,CONCAT(company.alias,'-',company.id) AS aliasid,
                company.city, company.created,company.logofilename,
                company.status,company.url,company.id,company.params,company.isfeaturedcompany,company.endfeatureddate,company.description
                FROM " . wpjobportal::$_db->prefix . "wj_portal_companies AS company
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON city.id = (SELECT cityid FROM `" . wpjobportal::$_db->prefix . "wj_portal_companycities` WHERE companyid = company.id ORDER BY id DESC LIMIT 1)
                WHERE company.status != 0";

        $query .= $wpjobportal_inquery;
        if(wpjobportal::$_common->wpjp_isadmin()){
            $query .= " ORDER BY " . wpjobportal::$_data['sorting'];
        }else{
            $query.= " ORDER BY " . wpjobportal::$_ordering;
        }
        $query .= " LIMIT " . WPJOBPORTALpagination::$_offset . "," . WPJOBPORTALpagination::$_limit;
        $wpjobportal_results = wpjobportaldb::get_results($query);
        $wpjobportal_data = array();
        foreach ($wpjobportal_results AS $d) {
            $d->location = WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($d->city);
            $wpjobportal_data[] = $d;
        }
        wpjobportal::$_data[0] = $wpjobportal_data;
        if(wpjobportal::$wpjobportal_theme_chk == 1 && wpjobportal::$_data != '' && isset($wpjobportal_city) && $wpjobportal_city != ''){
                wpjobportal::$_data['multicity'] = $this->getCitySelected($wpjobportal_city);
            }
        wpjobportal::$wpjobportal_data['fields'] = wpjobportal::$_wpjpfieldordering->getFieldsOrderingforView(1);
        wpjobportal::$_data['config'] = wpjobportal::$_config->getConfigByFor('company');
        return;
    }

    function getCitySelected($city){

        $query = "SELECT id, name FROM " . wpjobportal::$_db->prefix . "wj_portal_cities WHERE id IN (".esc_sql($city).")";
        $wpjobportal_results = wpjobportaldb::get_results($query);
        return $wpjobportal_json_response = wp_json_encode($wpjobportal_results);
    }

    function getAllUnapprovedCompanies() {
        $this->sorting();
        //Filters
        $wpjobportal_searchcompany = wpjobportal::$_search['search_filter']['searchcompany'];
        // $wpjobportal_categoryid = wpjobportal::$_search['search_filter']['searchjobcategory'];
        $wpjobportal_datestart = wpjobportal::$_search['search_filter']['datestart'];
        $wpjobportal_dateend = wpjobportal::$_search['search_filter']['dateend'];

        wpjobportal::$_data['filter']['searchcompany'] = $wpjobportal_searchcompany;
        // wpjobportal::$_data['filter']['searchjobcategory'] = $wpjobportal_categoryid;
        wpjobportal::$_data['filter']['datestart'] = $wpjobportal_datestart;
        wpjobportal::$_data['filter']['dateend'] = $wpjobportal_dateend;

        $wpjobportal_inquery = '';
        if ($wpjobportal_searchcompany)
            $wpjobportal_inquery = " AND LOWER(company.name) LIKE '%".esc_sql($wpjobportal_searchcompany)."%'";

        if ($wpjobportal_datestart != null) {
            $wpjobportal_datestart = gmdate('Y-m-d',strtotime($wpjobportal_datestart));
            $wpjobportal_inquery .= " AND DATE(company.created) >= '" . esc_sql($wpjobportal_datestart) . "'";
        }

        if ($wpjobportal_dateend != null) {
            $wpjobportal_dateend = gmdate('Y-m-d',strtotime($wpjobportal_dateend));
            $wpjobportal_inquery .= " AND DATE(company.created) <= '" . esc_sql($wpjobportal_dateend) . "'";
        }

        //Pagination
        $query = "SELECT COUNT(company.id)
                    FROM " . wpjobportal::$_db->prefix . "wj_portal_companies AS company
                    WHERE (company.status = 0 )";
        $query .=$wpjobportal_inquery;

        $wpjobportal_total = wpjobportaldb::get_var($query);
        wpjobportal::$_data['total'] = $wpjobportal_total;
        wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($wpjobportal_total);

        //Data
        $query = "SELECT company.*
                FROM " . wpjobportal::$_db->prefix . "wj_portal_companies AS company
                LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON city.id = (SELECT cityid FROM `" . wpjobportal::$_db->prefix . "wj_portal_companycities` WHERE companyid = company.id ORDER BY id DESC LIMIT 1)
                WHERE (company.status = 0 OR company.isfeaturedcompany = 0)";
        $query .=$wpjobportal_inquery;
        $query .= " ORDER BY " . wpjobportal::$_data['sorting'] . " LIMIT " . WPJOBPORTALpagination::$_offset . "," . WPJOBPORTALpagination::$_limit;

        wpjobportal::$_data[0] = wpjobportaldb::get_results($query);
        wpjobportal::$wpjobportal_data['fields'] = wpjobportal::$_wpjpfieldordering->getFieldsOrderingforView(1);
        // print_r(wpjobportal::$_data[0]);
        return;
    }

    function storeCompany($wpjobportal_data){
        if(empty($wpjobportal_data)){
            return false;
        }
        # request parameters
            $cuser = WPJOBPORTALincluder::getObjectClass('user');
            $wpjobportal_id = (int) $wpjobportal_data['id'];

        $wpjobportal_isnew = true;
        if(is_numeric($wpjobportal_id) && $wpjobportal_id > 0){
            $wpjobportal_isnew = false;
        }

        //making sure uid is not changed
        // admin can edit other employers companies
        if(!wpjobportal::$_common->wpjp_isadmin()){
            $wpjobportal_data['uid'] = $cuser->uid();
        }

        # prepare data + business logic
        $wpjobportal_no_package_needed = 0;
            if($wpjobportal_isnew){
                $wpjobportal_data['created'] = current_time('mysql');
                $wpjobportal_submissionType = wpjobportal::$_config->getConfigValue('submission_type');
                if(!wpjobportal::$_common->wpjp_isadmin()){
                    $wpjobportal_data['uid'] = $cuser->uid();
                    if(in_array('credits', wpjobportal::$_active_addons)){
                        # prepare data + credits
                        if($wpjobportal_submissionType == 1){
                            $wpjobportal_data['status'] = wpjobportal::$_config->getConfigValue('companyautoapprove');
                        }elseif ($wpjobportal_submissionType == 2) {
                            // in case of per listing submission mode
                            $wpjobportal_price_check = WPJOBPORTALincluder::getJSModel('credits')->checkIfPriceDefinedForAction('add_company');
                            if($wpjobportal_price_check == 1){ // if price is defined then status 3
                                $wpjobportal_data['status'] = 3;
                            }else{ // if price not defined then status set to auto approve configuration
                                $wpjobportal_data['status'] = wpjobportal::$_config->getConfigValue('companyautoapprove');
                            }
                        }elseif ($wpjobportal_submissionType == 3) {
                            if(is_numeric($wpjobportal_data['uid']) && $wpjobportal_data['uid'] > 0){ // check for logged in user
                                $wpjobportal_result = WPJOBPORTALincluder::getJSModel('credits')->checkIfPackageDefinedForUserRole($wpjobportal_data['uid']);
                                if($wpjobportal_result == 0){ // 0 means no package found. so allow the action.
                                    $wpjobportal_no_package_needed = 1;
                                }
                            }
                            if($wpjobportal_no_package_needed == 0){
                                $wpjobportal_upakid = WPJOBPORTALrequest::getVar('upakid',null,0);
                                /*Getting Package filter for All Module*/
                                $wpjobportal_package = apply_filters('wpjobportal_addons_userpackages_permodule',false,$wpjobportal_upakid,$cuser->uid(),'remcompany');
                                if( !$wpjobportal_package ){
                                    return WPJOBPORTAL_SAVE_ERROR;
                                }
                                if( $wpjobportal_package->expired ){
                                    return WPJOBPORTAL_SAVE_ERROR;
                                }
                                //if Department are not unlimited & there is no remaining left
                                if( $wpjobportal_package->companies!=-1 && !$wpjobportal_package->remcompany ){ //-1 = unlimited
                                    return WPJOBPORTAL_SAVE_ERROR;
                                }
                                #user packae id--
                                $wpjobportal_data['userpackageid'] = $wpjobportal_upakid;
                            }
                            $wpjobportal_data['status'] = wpjobportal::$_config->getConfigValue('companyautoapprove');
                        }
                    }else{
                        $wpjobportal_data['status'] = wpjobportal::$_config->getConfigValue('companyautoapprove');
                    }
                }else{
                    if(wpjobportal::$_common->wpjp_isadmin()){
                        if(in_array('credits', wpjobportal::$_active_addons)){
                            if ($wpjobportal_submissionType == 3) {
                                $wpjobportal_result = WPJOBPORTALincluder::getJSModel('credits')->checkIfPackageDefinedForRole(1); //1 for employer;
                                if($wpjobportal_result == 0){ // 0 means no package found. so allow the action.
                                    $wpjobportal_no_package_needed = 1;
                                }
                                if($wpjobportal_no_package_needed == 0){
                                    if ($wpjobportal_data['payment'] == 0) {
                                        $wpjobportal_upakid = WPJOBPORTALrequest::getVar('upakid',null,0);
                                        $wpjobportal_data['userpackageid'] = $wpjobportal_upakid;
                                    } else {
                                        $wpjobportal_upakid = WPJOBPORTALrequest::getVar('upakid',null,0);
                                        /*Getting Package filter for All Module*/
                                        $wpjobportal_package = apply_filters('wpjobportal_addons_userpackages_permodule',false,$wpjobportal_upakid,$wpjobportal_data['uid'],'remcompany');
                                        if( !$wpjobportal_package ){
                                            return WPJOBPORTAL_SAVE_ERROR;
                                        }
                                        if( $wpjobportal_package->expired ){
                                            return WPJOBPORTAL_SAVE_ERROR;
                                        }
                                        //if Department are not unlimited & there is no remaining left
                                        if( $wpjobportal_package->companies!=-1 && !$wpjobportal_package->remcompany ){ //-1 = unlimited
                                            return WPJOBPORTAL_SAVE_ERROR;
                                        }
                                        #user packae id--
                                        $wpjobportal_data['userpackageid'] = $wpjobportal_upakid;
                                    }
                                }
                            }
                        }
                    }
                }
            }else{ // edit case
                if(!wpjobportal::$_common->wpjp_isadmin()){ // checking if is admin
                    // verify that can current user is editing his owned entity
                    if(!$this->getIfCompanyOwner($wpjobportal_id)){
                        // if current entity being edited is not owned by current user dont allow to procced further
                        return false;
                    }
                }
            }
            // admin creating a company with minimum fields (status field is unpublished)
            if(wpjobportal::$_common->wpjp_isadmin()){
                if(!isset($wpjobportal_data['status'])){
                    $wpjobportal_data['status'] = 1;
                }
            }
            $wpjobportal_data['alias'] = wpjobportal::$_common->stringToAlias(empty($wpjobportal_data['alias']) ? $wpjobportal_data['name'] : $wpjobportal_data['alias']);
        # sanitize data
            if(isset($wpjobportal_data['description'])){
                $wpjobportal_tempdesc = $wpjobportal_data['description'];
            }
            $wpjobportal_data = wpjobportal::wpjobportal_sanitizeData($wpjobportal_data);
            if(isset($wpjobportal_data['description'])){
                $wpjobportal_data['description'] = wpautop(wptexturize(wptexturize(wpjobportalphplib::wpJP_stripslashes($wpjobportal_tempdesc))));
            }

            if(WPJOBPORTALincluder::getJSModel('common')->checkLanguageSpecialCase()){
                $wpjobportal_data = wpjobportal::$_common->stripslashesFull($wpjobportal_data);
            }

        # store in db
            $wpjobportal_row = WPJOBPORTALincluder::getJSTable('company');
            if(!($wpjobportal_row->bind($wpjobportal_data) && $wpjobportal_row->check() && $wpjobportal_row->store())){
                return false;
            }
            $wpjobportal_companyid = $wpjobportal_row->id;
            wpjobportal::$_data['id'] = $wpjobportal_companyid;
        #store custom fields
        wpjobportal::$_wpjpcustomfield->storeCustomFields(WPJOBPORTAL_COMPANY,$wpjobportal_companyid,$wpjobportal_data);
        if(in_array('credits', wpjobportal::$_active_addons)){
            if($wpjobportal_isnew && $wpjobportal_submissionType == 3  && $wpjobportal_no_package_needed == 0){
                $wpjobportal_trans = WPJOBPORTALincluder::getJSTable('transactionlog');
                $wpjobportal_arr = array();
                if (!wpjobportal::$_common->wpjp_isadmin()) {
                    $wpjobportal_arr['uid'] = $cuser->uid();
                }elseif (wpjobportal::$_common->wpjp_isadmin()) {
                    $wpjobportal_arr['uid'] = $wpjobportal_data['uid'];
                }
                $wpjobportal_arr['userpackageid'] = $wpjobportal_upakid;
                $wpjobportal_arr['recordid'] = $wpjobportal_row->id;
                $wpjobportal_arr['type'] = 'company';
                $wpjobportal_arr['created'] = current_time('mysql');
                $wpjobportal_arr['status'] = 1;
                $wpjobportal_trans->bind($wpjobportal_arr);
                $wpjobportal_trans->store();
            }
        }

        # store multiple cities with company
            if(isset($wpjobportal_data['city'])){
                $this->storeMultiCitiesCompany($wpjobportal_data['city'], $wpjobportal_companyid);
            }

        # save company logo
            if(isset($wpjobportal_data['company_logo_deleted'])){
                $this->deleteCompanyLogoModel($wpjobportal_companyid);
            }
            if(isset($_FILES['logo'])){// min field issue
                if ($_FILES['logo']['size'] > 0) {
                    if(!isset($wpjobportal_data['company_logo_deleted'])){
                        $this->deleteCompanyLogoModel($wpjobportal_companyid);
                    }
                    $res = $this->uploadFile($wpjobportal_companyid);
                    if ($res == 6){
                        $wpjobportal_msg = WPJOBPORTALMessages::getMessage(WPJOBPORTAL_FILE_TYPE_ERROR, '');
                        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->getMessagekey());
                    }
                    if($res == 5){
                        $wpjobportal_msg = WPJOBPORTALMessages::getMessage(WPJOBPORTAL_FILE_SIZE_ERROR, '');
                        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'],$this->getMessagekey());
                    }
                }
            }

        # send new company email
            if($wpjobportal_isnew){
                WPJOBPORTALincluder::getJSModel('emailtemplate')->sendMail(1, 1, $wpjobportal_companyid);
            }
        // action hook for add company
        if(!isset($wpjobportal_data['id'])){
            $wpjobportal_data['id'] = $wpjobportal_row->id;
        }
        do_action('wpjobportal_after_store_company_hook',$wpjobportal_data);
        return $wpjobportal_companyid;
    }

    function storeMultiCitiesCompany($city_id, $wpjobportal_companyid) { // city id comma seprated
        if (!is_numeric($wpjobportal_companyid)){
            return false;
        }

        $query = "SELECT cityid FROM " . wpjobportal::$_db->prefix . "wj_portal_companycities WHERE companyid = " . esc_sql($wpjobportal_companyid);
        $old_cities = wpjobportaldb::get_results($query);

        $wpjobportal_id_array = wpjobportalphplib::wpJP_explode(",", $city_id);
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('companycities');
        $error = array();

        foreach ($old_cities AS $oldcityid) {
            $wpjobportal_match = false;
            foreach ($wpjobportal_id_array AS $cityid) {
                if ($oldcityid->cityid == $cityid) {
                    $wpjobportal_match = true;
                    break;
                }
            }
            if ($wpjobportal_match == false) {
                $query = "DELETE FROM " . wpjobportal::$_db->prefix . "wj_portal_companycities WHERE companyid = " . esc_sql($wpjobportal_companyid) . " AND cityid=" . esc_sql($oldcityid->cityid);

                if (!wpjobportaldb::query($query)) {
                    $err = wpjobportal::$_db->last_error;
                    $error[] = $err;
                }
            }
        }
        foreach ($wpjobportal_id_array AS $cityid) {
            $wpjobportal_insert = true;
            foreach ($old_cities AS $oldcityid) {
                if ($oldcityid->cityid == $cityid) {
                    $wpjobportal_insert = false;
                    break;
                }
            }
            if ($wpjobportal_insert) {
                $cols = array();
                $cols['id'] = "";
                $cols['companyid'] = $wpjobportal_companyid;
                $cols['cityid'] = $cityid;
                if (!$wpjobportal_row->bind($cols)) {
                    $err = wpjobportal::$_db->last_error;
                    $error[] = $err;
                }
                if (!$wpjobportal_row->store()) {
                    $err = wpjobportal::$_db->last_error;
                    $error[] = $err;
                }
            }
        }
        if (empty($error)){
            return true;
        }
        return false;
    }

    function getUidByCompanyId($wpjobportal_companyid) {
        if (!is_numeric($wpjobportal_companyid))
            return false;
        $query = "SELECT uid FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` WHERE id = " . esc_sql($wpjobportal_companyid);
        $wpjobportal_uid = wpjobportaldb::get_var($query);
        // var_dump($query);
        // die();
        return $wpjobportal_uid;
    }

    function deleteCompanies($wpjobportal_ids) {
        if (empty($wpjobportal_ids))
            return false;
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('company');
        $wpjobportal_notdeleted = 0;
        $wpjobportal_mailextradata = array();
        foreach ($wpjobportal_ids as $wpjobportal_id) {
            if(!is_numeric($wpjobportal_id)){
                continue;
            }
            $query = "SELECT company.name,company.contactemail AS contactemail FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company  WHERE company.id = " . esc_sql($wpjobportal_id);
            $wpjobportal_companyinfo = wpjobportaldb::get_row($query);
            if(empty($wpjobportal_companyinfo)){
                continue;
            }
            $wpjobportal_mailextradata['companyname'] = $wpjobportal_companyinfo->name;
            /*$wpjobportal_mailextradata['contactname'] = $wpjobportal_companyinfo->contactname;*/
            $wpjobportal_mailextradata['contactemail'] = $wpjobportal_companyinfo->contactemail;
            if ($this->companyCanDelete($wpjobportal_id) == true) {
                if (!$wpjobportal_row->delete($wpjobportal_id)) {
                    $wpjobportal_notdeleted += 1;
                } else {
                    $query = "DELETE FROM `" . wpjobportal::$_db->prefix . "wj_portal_companycities` WHERE companyid = " . esc_sql($wpjobportal_id);
                    wpjobportaldb::query($query);
                    WPJOBPORTALincluder::getJSModel('emailtemplate')->sendMail(1, 2, $wpjobportal_id,$wpjobportal_mailextradata); // 1 for company,2 for delete company

                    $wpjobportal_data_directory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
                    $wpjobportal_wpdir = wp_upload_dir();
                    array_map('wp_delete_file', glob($wpjobportal_wpdir['basedir'] . '/' . $wpjobportal_data_directory . "/data/employer/comp_".$wpjobportal_id."/logo/*.*"));//deleting files
                    if ( ! function_exists( 'WP_Filesystem' ) ) {
                    require_once ABSPATH . 'wp-admin/includes/file.php';
                    }
                    global $wp_filesystem;
                    if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ) {
                        $creds = request_filesystem_credentials( site_url() );
                        wp_filesystem( $creds );
                    }

                    if(is_dir($wpjobportal_wpdir['basedir'] . '/' . $wpjobportal_data_directory . "/data/employer/comp_".$wpjobportal_id."/logo")){
                        @$wp_filesystem->rmdir($wpjobportal_wpdir['basedir'] . '/' . $wpjobportal_data_directory . "/data/employer/comp_".$wpjobportal_id."/logo");
                    }
                    array_map('wp_delete_file', glob($wpjobportal_wpdir['basedir'] . '/' . $wpjobportal_data_directory . "/data/employer/comp_".$wpjobportal_id."/*.*"));//deleting files
                    if ($wp_filesystem->exists($wpjobportal_wpdir['basedir'] . '/' . $wpjobportal_data_directory . "/data/employer/comp_".$wpjobportal_id)) {
                        @$wp_filesystem->rmdir($wpjobportal_wpdir['basedir'] . '/' . $wpjobportal_data_directory . "/data/employer/comp_".$wpjobportal_id);
                    }
                    // action hook for delete company
                    do_action('wpjobportal_after_delete_company_hook',$wpjobportal_id);
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

    function companyCanDelete($wpjobportal_companyid) {
        if (!is_numeric($wpjobportal_companyid))
            return false;
        if(!wpjobportal::$_common->wpjp_isadmin()){
            if(!$this->getIfCompanyOwner($wpjobportal_companyid)){
                return false;
            }
        }
        $query = "SELECT
                    ( SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` WHERE companyid = " . esc_sql($wpjobportal_companyid) . ")";
                    if(in_array('departments', wpjobportal::$_active_addons)){
                        $query .= " + ( SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_departments` WHERE companyid = " . esc_sql($wpjobportal_companyid) . ")";
                    }
                    $query .= " AS total ";
        $wpjobportal_total = wpjobportaldb::get_var($query);
        wpjobportal::$_data['total'] = $wpjobportal_total;
        wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($wpjobportal_total);
        if ($wpjobportal_total > 0)
            return false;
        else
            return true;
    }

    function companyEnforceDeletes($wpjobportal_companyid) {
        if (empty($wpjobportal_companyid))
            return false;
        if (!is_numeric($wpjobportal_companyid))
            return false;

        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('company');
        $wpjobportal_mailextradata = array();
        $query1 = "SELECT company.name,company.contactemail AS contactemail FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company  WHERE company.id = " . esc_sql($wpjobportal_companyid);
        $wpjobportal_companyinfo = wpjobportaldb::get_row($query1);
        $wpjobportal_mailextradata['companyname'] = $wpjobportal_companyinfo->name;
        /* $wpjobportal_mailextradata['contactname'] = $wpjobportal_companyinfo->contactname;*/
        $wpjobportal_mailextradata['contactemail'] = $wpjobportal_companyinfo->contactemail;
        $query = "DELETE  company,job,companycity";
        if(in_array('departments', wpjobportal::$_active_addons)){
            $query .= " ,department ";
        }
        if(in_array('shortlist', wpjobportal::$_active_addons)){
            $query .= ",jobshortlist";
        }
        // job enforce deleta has this code to remove messages, for the job adding it here to make the data consistent
        if(in_array('message', wpjobportal::$_active_addons)){
            $query .= ",message";
        }
        $query .= " , apply, jobcity
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_companycities` AS companycity ON company.id=companycity.companyid ";
                    if(in_array('departments', wpjobportal::$_active_addons)){
                        $query .= " LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_departments` AS department ON company.id=department.companyid";
                    }
        $query .= " LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job ON company.id=job.companyid";
                    if(in_array('message', wpjobportal::$_active_addons)){
                        $query .= " LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_messages` AS message ON job.id = message.jobid";
                    }
                    if(in_array('shortlist', wpjobportal::$_active_addons)){
                        $query .= " LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobshortlist` AS jobshortlist ON job.id = jobshortlist.jobid";
                    }
        $query .= "
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` AS apply ON job.id=apply.jobid
                    LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_jobcities` AS jobcity ON job.id=jobcity.jobid
                    WHERE company.id =" . esc_sql($wpjobportal_companyid);
        if (!wpjobportaldb::query($query)) {
            return WPJOBPORTAL_DELETE_ERROR;
        }
        WPJOBPORTALincluder::getJSModel('emailtemplate')->sendMail(1, 2, $wpjobportal_companyid,$wpjobportal_mailextradata); // 1 for company,2 for delete company

        $wpjobportal_data_directory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
        $wpjobportal_wpdir = wp_upload_dir();
        $file = $wpjobportal_wpdir['basedir'] . '/' . $wpjobportal_data_directory . "/data/employer/comp_".$wpjobportal_companyid."/logo/*.*";
        $files = glob($file);
        array_map('wp_delete_file', $files);//deleting files
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
        if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ) {
            $creds = request_filesystem_credentials( site_url() );
            wp_filesystem( $creds );
        }
        if($wp_filesystem->exists($wpjobportal_wpdir['basedir'] . '/' . $wpjobportal_data_directory . "/data/employer/comp_".$wpjobportal_companyid."/logo")) {
            $wp_filesystem->rmdir($wpjobportal_wpdir['basedir'] . '/' . $wpjobportal_data_directory . "/data/employer/comp_".$wpjobportal_companyid."/logo");
        }
        if($wp_filesystem->exists($wpjobportal_wpdir['basedir'] . '/' . $wpjobportal_data_directory . "/data/employer/comp_".$wpjobportal_companyid)) {
            $wp_filesystem->rmdir($wpjobportal_wpdir['basedir'] . '/' . $wpjobportal_data_directory . "/data/employer/comp_".$wpjobportal_companyid);
        }

        // action hook for delete company
        do_action('wpjobportal_after_delete_company_hook',$wpjobportal_companyid);

        return WPJOBPORTAL_DELETED;
    }

    function getCompanyForDept() {
        $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        $query = "SELECT id  FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` WHERE status = 1 ";
        if ($wpjobportal_uid != null) {
            if (!is_numeric($wpjobportal_uid))
                return false;
            $query .= " AND uid = " . esc_sql($wpjobportal_uid);
        }
        $query .= " ORDER BY id ASC LIMIT 0,1";
        $wpjobportal_companies = wpjobportaldb::get_var($query);
        if (wpjobportal::$_db->last_error != null) {
            return false;
        }
        return $wpjobportal_companies;
    }

    function getCompanyForCombo($wpjobportal_uid = null) {
        $query = "SELECT id, name AS text FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` WHERE status = 1 ";
        if ($wpjobportal_uid != null) {
            if (!is_numeric($wpjobportal_uid))
                return false;
            $query .= " AND uid = " . esc_sql($wpjobportal_uid);
        }
        $query .= " ORDER BY id ASC ";
        $wpjobportal_companies = wpjobportaldb::get_results($query);
        if (wpjobportal::$_db->last_error != null) {
            return false;
        }
        return $wpjobportal_companies;
    }

    function deleteCompanyLogo($wpjobportal_companyid = 0){
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'delete-company-logo') ) {
            die( 'Security check Failed' );
        }
        if($wpjobportal_companyid == 0){
            $wpjobportal_companyid = WPJOBPORTALrequest::getVar('companyid');
        }
        if(!is_numeric($wpjobportal_companyid)){
            return false;
        }
        if (!current_user_can('manage_options')) { // checking if is admin
            // verify that can current user is editing his owned entity
            if(!$this->getIfCompanyOwner($wpjobportal_companyid)){
                // if current entity being edited is not owned by current user dont allow to procced further
                return false;
            }
        }

        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('company');
        $wpjobportal_data_directory = wpjobportal::$_config->getConfigValue('data_directory');
        $wpjobportal_wpdir = wp_upload_dir();
        $wpjobportal_path = $wpjobportal_wpdir['basedir'] . '/' . $wpjobportal_data_directory . '/data/employer/comp_' . $wpjobportal_companyid . '/logo';
        $files = glob($wpjobportal_path . '/*.*');
        array_map('wp_delete_file', $files);    // delete all file in the direcoty
        $query = "UPDATE `".wpjobportal::$_db->prefix."wj_portal_companies` SET logofilename = '', logoisfile = -1 WHERE id = ".esc_sql($wpjobportal_companyid);
        wpjobportal::$_db->query($query);
        return true;
    }

    function deleteCompanyLogoModel($wpjobportal_companyid = 0){

        if($wpjobportal_companyid == 0){
            $wpjobportal_companyid = WPJOBPORTALrequest::getVar('companyid');
        }
        if(!is_numeric($wpjobportal_companyid)){
            return false;
        }
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('company');
        $wpjobportal_data_directory = wpjobportal::$_config->getConfigValue('data_directory');
        $wpjobportal_wpdir = wp_upload_dir();
        $wpjobportal_path = $wpjobportal_wpdir['basedir'] . '/' . $wpjobportal_data_directory . '/data/employer/comp_' . $wpjobportal_companyid . '/logo';
        $files = glob($wpjobportal_path . '/*.*');
        array_map('wp_delete_file', $files);    // delete all file in the direcoty
        $query = "UPDATE `".wpjobportal::$_db->prefix."wj_portal_companies` SET logofilename = '', logoisfile = -1 WHERE id = ".esc_sql($wpjobportal_companyid);
        wpjobportal::$_db->query($query);
        return true;
    }

    function uploadFile($wpjobportal_id) {
        $wpjobportal_result =  WPJOBPORTALincluder::getObjectClass('uploads')->uploadCompanyLogo($wpjobportal_id);
        return $wpjobportal_result;
    }

    function approveQueueCompanyModel($wpjobportal_id) {
        if (is_numeric($wpjobportal_id) == false)
            return false;
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('company');
        if($wpjobportal_row->load($wpjobportal_id)){
            $wpjobportal_row->columns['status'] = 1;
            if(!$wpjobportal_row->store()){
                return WPJOBPORTAL_APPROVE_ERROR;
            }
        }else{
            return WPJOBPORTAL_APPROVE_ERROR;
        }
        //send email
        $wpjobportal_company_queue_approve_email = WPJOBPORTALincluder::getJSModel('emailtemplate')->sendMail(1, 3, $wpjobportal_id); // 1 for company, 3 for company approve
        return WPJOBPORTAL_APPROVED;
    }

    function approveQueueFeaturedCompanyModel($wpjobportal_id) {
        if (is_numeric($wpjobportal_id) == false)
            return false;
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('company');
        if($wpjobportal_row->load($wpjobportal_id)){
            $wpjobportal_row->columns['isfeaturedcompany'] = 1;
            $wpjobportal_startfeatureddate = strtotime($wpjobportal_row->startfeatureddate);
            $wpjobportal_endfeatureddate = strtotime($wpjobportal_row->endfeatureddate);
            $wpjobportal_datediff = $wpjobportal_endfeatureddate - $wpjobportal_startfeatureddate;
            $wpjobportal_diff_days = floor($wpjobportal_datediff/(60*60*24));
            $wpjobportal_row->columns['startfeatureddate'] = gmdate('Y-m-d H:i:s');
            $wpjobportal_row->columns['endfeatureddate'] = gmdate('Y-m-d H:i:s',strtotime(" +$wpjobportal_diff_days days"));
            if(!$wpjobportal_row->store()){
                return WPJOBPORTAL_APPROVE_ERROR;
            }
        }else{
            return WPJOBPORTAL_APPROVE_ERROR;
        }
        //send email
        $wpjobportal_company_queue_approve_email = WPJOBPORTALincluder::getJSModel('emailtemplate')->sendMail(1, 5, $wpjobportal_id); // 1 for company, 5 for company featured approve
        return WPJOBPORTAL_APPROVED;
    }

    function rejectQueueCompanyModel($wpjobportal_id) {
        if (is_numeric($wpjobportal_id) == false)
            return false;
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('company');
        if (!$wpjobportal_row->update(array('id' => $wpjobportal_id, 'status' => -1))) {
            return WPJOBPORTAL_REJECT_ERROR;
        }
        //send email
        $wpjobportal_company_approve_email = WPJOBPORTALincluder::getJSModel('emailtemplate')->sendMail(1, 3, $wpjobportal_id); // 1 for company, 3 for company reject
        return WPJOBPORTAL_REJECTED;
    }

    function rejectQueueFeatureCompanyModel($wpjobportal_id) {
        if (is_numeric($wpjobportal_id) == false)
            return false;
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('company');
        if (!$wpjobportal_row->update(array('id' => $wpjobportal_id, 'isfeaturedcompany' => -1))) {
            return WPJOBPORTAL_REJECT_ERROR;
        }
        //send email
        $wpjobportal_company_queue_approve_email = WPJOBPORTALincluder::getJSModel('emailtemplate')->sendMail(1, 5, $wpjobportal_id); // 1 for company, 5 for company featured approve
        return WPJOBPORTAL_REJECTED;
    }


    function approveQueueAllCompaniesModel($wpjobportal_id, $wpjobportal_actionid) {
        /*
         * *  4 for All
         */
        if (!is_numeric($wpjobportal_id))
            return false;

        $wpjobportal_result = $this->approveQueueCompanyModel($wpjobportal_id);
        return $wpjobportal_result;
    }

    function rejectQueueAllCompaniesModel($wpjobportal_id, $wpjobportal_actionid) {
        /*
         * *  4 for All
         */
        if (!is_numeric($wpjobportal_id))
            return false;

        $wpjobportal_result = $this->rejectQueueCompanyModel($wpjobportal_id);
        return $wpjobportal_result;
    }

    function getCompaniesForCombo() {
        $query = "SELECT id, name AS text FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` WHERE status = 1 ORDER BY name ASC ";
        $wpjobportal_rows = wpjobportaldb::get_results($query);
        return $wpjobportal_rows;
    }

    function getUserCompaniesForCombo() {
        $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        if(!is_numeric($wpjobportal_uid)) return false;
        $query = "SELECT id, name AS text FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` WHERE uid = " . esc_sql($wpjobportal_uid) . " AND status = 1 ORDER BY name ASC ";
        if(!wpjobportal::$_common->wpjp_isadmin()){
            if(!in_array('multicompany', wpjobportal::$_active_addons)){
                $query .= "LIMIT 1";
            }
        }
        $wpjobportal_rows = wpjobportaldb::get_results($query);
        return $wpjobportal_rows;
    }

    function getCompanynameById($wpjobportal_id) {
        if (!is_numeric($wpjobportal_id))
            return false;
        $query = "SELECT company.name FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company WHERE company.id = " . esc_sql($wpjobportal_id);
        $wpjobportal_companyname = wpjobportal::$_db->get_var($query);
        return $wpjobportal_companyname;
    }

    function addViewContactDetail($wpjobportal_companyid, $wpjobportal_uid) {
// made this function funcanality same as multicompany module to handle probelms with membership case
        // if (!is_numeric($wpjobportal_companyid))
        //     return false;
        // if (!is_numeric($wpjobportal_uid))
        //     return false;
        // // $wpjobportal_curdate was undefined
        // $wpjobportal_curdate = current_time('mysql');
        // $wpjobportal_data = array();
        // $wpjobportal_data['uid'] = $wpjobportal_uid;
        // $wpjobportal_data['companyid'] = $wpjobportal_companyid;
        // $wpjobportal_data['status'] = 1;
        // $wpjobportal_data['created'] = $wpjobportal_curdate;
        // $wpjobportal_data = wpjobportal::wpjobportal_sanitizeData($wpjobportal_data);
        // $wpjobportal_row = WPJOBPORTALincluder::getJSTable('jobseekerviewcompany');
        // if (!$wpjobportal_row->bind($wpjobportal_data)) {
        //     return false;
        // }

        // if ($wpjobportal_row->store()) {
        //     return true;
        // }else{
        //     return false;
        // }
        if (!is_numeric($wpjobportal_companyid))
            return false;
        if (!is_numeric($wpjobportal_uid))
            return false;
        $wpjobportal_curdate = gmdate('Y-m-d H:i:s');
        $wpjobportal_data = array();
        if(in_array('credits', wpjobportal::$_active_addons)){
            #Submission Type
            $wpjobportal_subType = wpjobportal::$_config->getConfigValue('submission_type');
            if ($wpjobportal_subType == 3) {
                       #Membershipe Code for Featured Resume
                $wpjobportal_packageid = WPJOBPORTALrequest::getVar('wpjobportal_packageid','',0);
                if($wpjobportal_packageid == 0){
                    return false;
                }
                # Package Filter's
                $wpjobportal_package = apply_filters('wpjobportal_addons_userpackages_perfeaturemodule',false,$wpjobportal_packageid,'remcompanycontactdetail',$wpjobportal_uid);
                if($wpjobportal_package && !$wpjobportal_package->expired && ($wpjobportal_package->companycontactdetail==-1 || $wpjobportal_package->remcompanycontactdetail)){ //-1 = unlimited
                    #Data For Featured Company Member
                    $wpjobportal_data['uid'] = $wpjobportal_uid;
                    $wpjobportal_data['companyid'] = $wpjobportal_companyid;
                    $wpjobportal_data['status'] = 1;
                    $wpjobportal_data['created'] = $wpjobportal_curdate;
                    $wpjobportal_data['userpackageid'] = $wpjobportal_package->packageid;
                    #Job sekker Company View
                    $wpjobportal_row = WPJOBPORTALincluder::getJSTable('jobseekerviewcompany');
                    if($this->checkAlreadyViewCompanyContactDetail($wpjobportal_companyid) == false){
                       if($wpjobportal_row->bind($wpjobportal_data)){
                            if($wpjobportal_row->store()){
                                # Company Contact View Resume Transactio Log Entries--
                                $wpjobportal_trans = WPJOBPORTALincluder::getJSTable('transactionlog');
                                $wpjobportal_arr = array();
                                $wpjobportal_arr['userpackageid'] = $wpjobportal_package->id;
                                $wpjobportal_arr['uid'] = $wpjobportal_uid;
                                $wpjobportal_arr['recordid'] = $wpjobportal_companyid;
                                $wpjobportal_arr['type'] = 'companycontactdetail';
                                $wpjobportal_arr['created'] = current_time('mysql');
                                $wpjobportal_arr['status'] = 1;
                                $wpjobportal_trans->bind($wpjobportal_arr);
                                $wpjobportal_trans->store();
                               WPJOBPORTALmessages::setLayoutMessage(__('You can view Company Contact Detail Now','wp-job-portal'), 'updated','company');
                                return true;
                            }else{
                                return false;
                            }
                        }
                    }else{
                        return false;
                    }
                }else{
                    // the user does not have nessery package
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
        // In case Of Free
        $wpjobportal_data['uid'] = $wpjobportal_uid;
        $wpjobportal_data['companyid'] = $wpjobportal_companyid;
        if(!isset($wpjobportal_data['status']) && empty($wpjobportal_data['status'])){
            $wpjobportal_data['status'] = 1;
        }
        $wpjobportal_data['created'] = $wpjobportal_curdate;
        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('jobseekerviewcompany');
        if (!$wpjobportal_row->bind($wpjobportal_data)) {
            return false;
        }

        if ($wpjobportal_row->store()) {
            return true;
        }else{
            return false;
        }
    }

    function canAddCompany($wpjobportal_uid,$wpjobportal_actionname='') {
        if (!is_numeric($wpjobportal_uid))
            return false;
        if(in_array('credits', wpjobportal::$_active_addons)){
            $wpjobportal_credits = apply_filters('wpjobportal_addons_userpackages_module_wise',false,$wpjobportal_uid,$wpjobportal_actionname);
            return $wpjobportal_credits;
        }else{

            return $this->userCanAddCompany($wpjobportal_uid);
        }

    }

    function employerHaveCompany($wpjobportal_uid) {
        if (!is_numeric($wpjobportal_uid))
            return false;
        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` WHERE uid = " . esc_sql($wpjobportal_uid);
        $wpjobportal_result = wpjobportal::$_db->get_var($query);
        if ($wpjobportal_result == 0) {
            return false;
        } else {
            return true;
        }
    }

    function makeCompanySeo($wpjobportal_company_seo , $wpjobportalid){
        //Fareed
        if(empty($wpjobportal_company_seo))
            return '';

        $wpjobportal_common = wpjobportal::$_common;
        $wpjobportal_id = $wpjobportal_common->parseID($wpjobportalid);
        if(! is_numeric($wpjobportal_id))
            return '';
        $wpjobportal_result = '';
        $wpjobportal_company_seo = wpjobportalphplib::wpJP_str_replace( ' ', '', $wpjobportal_company_seo);
        $wpjobportal_company_seo = wpjobportalphplib::wpJP_str_replace( '[', '', $wpjobportal_company_seo);
        $wpjobportal_array = wpjobportalphplib::wpJP_explode(']', $wpjobportal_company_seo);

        $wpjobportal_total = count($wpjobportal_array);
        if($wpjobportal_total > 3)
            $wpjobportal_total = 3;

        for ($wpjobportal_i=0; $wpjobportal_i < $wpjobportal_total; $wpjobportal_i++) {
            $query = '';
            switch ($wpjobportal_array[$wpjobportal_i]) {
                case 'name':
                    $query = "SELECT name AS col FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` WHERE id = " . esc_sql($wpjobportal_id);
                break;
                case 'category':
                    break;
                    $query = "SELECT category.cat_title AS col
                        FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company
                        WHERE company.id = " . esc_sql($wpjobportal_id);
                break;
                case 'location':
                    $query = "SELECT company.city AS col
                        FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company WHERE company.id = " . esc_sql($wpjobportal_id);
                break;
            }
            if($query){
                $wpjobportal_data = wpjobportaldb::get_row($query);
                if(isset($wpjobportal_data->col)){
                    if($wpjobportal_array[$wpjobportal_i] == 'location'){
                        $cityids = wpjobportalphplib::wpJP_explode(',', $wpjobportal_data->col);
                        $location = '';
                        for ($j=0; $j < count($cityids); $j++) {
                            if(is_numeric($cityids[$j])){
                                $query = "SELECT name FROM `" . wpjobportal::$_db->prefix . "wj_portal_cities` WHERE id = ". esc_sql($cityids[$j]);
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
                        if($location != ''){
                            if($wpjobportal_result == '')
                                $wpjobportal_result .= wpjobportalphplib::wpJP_str_replace(' ', '-', $location);
                            else
                                $wpjobportal_result .= '-'.wpjobportalphplib::wpJP_str_replace(' ', '-', $location);
                        }
                    }else{
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


    function makeCompanySeoDocumentTitle($wpjobportal_company_seo , $wpjobportalid){
        if(empty($wpjobportal_company_seo))
            return '';

        $wpjobportal_common = wpjobportal::$_common;
        $wpjobportal_id = $wpjobportal_common->parseID($wpjobportalid);
        if(! is_numeric($wpjobportal_id))
            return '';
        $wpjobportal_result = '';

        $wpjobportal_companyname = '';
        $wpjobportal_companylocation = '';

        $query = "SELECT name AS companyname, city AS companycity FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` WHERE id = " . esc_sql($wpjobportal_id);
        $wpjobportal_data = wpjobportaldb::get_row($query);
        if(!empty($wpjobportal_data)){
            $wpjobportal_companylocation = '';
            if($wpjobportal_data->companycity != '' && is_numeric($wpjobportal_data->companycity)){
                $query = "SELECT name FROM `" . wpjobportal::$_db->prefix . "wj_portal_cities` WHERE id = ". esc_sql($wpjobportal_data->companycity);
                $wpjobportal_companylocation = wpjobportaldb::get_var($query);
            }
            $wpjobportal_companyname = $wpjobportal_data->companyname;
            $wpjobportal_matcharray = array(
                '[name]' => $wpjobportal_companyname,
                '[location]' => $wpjobportal_companylocation,
                '[separator]' => '-',
                '[sitename]' => get_bloginfo( 'name', 'display' )
            );
            $wpjobportal_result = $this->replaceMatches($wpjobportal_company_seo,$wpjobportal_matcharray);

        }

        return $wpjobportal_result;
    }

    function replaceMatches($wpjobportal_string, $wpjobportal_matcharray) {
        foreach ($wpjobportal_matcharray AS $find => $replace) {
            $wpjobportal_string = wpjobportalphplib::wpJP_str_replace($find, $replace, $wpjobportal_string);
        }
        return $wpjobportal_string;
    }

    function getCompanyExpiryStatus($wpjobportal_id) {
        if (!is_numeric($wpjobportal_id))
            return false;
        $query = "SELECT company.id
        FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company
        WHERE company.status = 1
        AND company.id =" . esc_sql($wpjobportal_id);
        $wpjobportal_result = wpjobportal::$_db->get_var($query);
        if ($wpjobportal_result == null) {
            return false;
        } else {
            return true;
        }
    }

    function getIfCompanyOwner($wpjobportal_id) {
        if (!is_numeric($wpjobportal_id))
            return false;
        $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        $query = "SELECT company.id
        FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company
        WHERE company.uid = " . esc_sql($wpjobportal_uid) . "
        AND company.id =" . esc_sql($wpjobportal_id);
        $wpjobportal_result = wpjobportal::$_db->get_var($query);
        if ($wpjobportal_result == null) {
            return false;
        } else {
            return true;
        }
    }

    function getMessagekey(){
        $wpjobportal_key = 'company';if(wpjobportal::$_common->wpjp_isadmin()){$wpjobportal_key = 'admin_'.$wpjobportal_key;}return $wpjobportal_key;
    }


    function getOrdering() {
        $sort = WPJOBPORTALrequest::getVar('sortby', '', 'posteddesc');
        $this->getListOrdering($sort);
        $this->getListSorting($sort);
    }

    function getListOrdering($sort) {
        switch ($sort) {
            case "namedesc":
                wpjobportal::$_ordering = "company.name DESC";
                wpjobportal::$_sorton = "name";
                wpjobportal::$_sortorder = "DESC";
                break;
            case "nameasc":
                wpjobportal::$_ordering = "company.name ASC";
                wpjobportal::$_sorton = "name";
                wpjobportal::$_sortorder = "ASC";
                break;
            case "categorydesc":
                wpjobportal::$_ordering = "cat.cat_title DESC";
                wpjobportal::$_sorton = "category";
                wpjobportal::$_sortorder = "DESC";
                break;
            case "categoryasc":
                wpjobportal::$_ordering = "cat.cat_title ASC";
                wpjobportal::$_sorton = "category";
                wpjobportal::$_sortorder = "ASC";
                break;
            case "locationdesc":
                wpjobportal::$_ordering = "city.name DESC";
                wpjobportal::$_sorton = "location";
                wpjobportal::$_sortorder = "DESC";
                break;
            case "locationasc":
                wpjobportal::$_ordering = "city.name ASC";
                wpjobportal::$_sorton = "location";
                wpjobportal::$_sortorder = "ASC";
                break;
            case "posteddesc":
                wpjobportal::$_ordering = "company.created DESC";
                wpjobportal::$_sorton = "posted";
                wpjobportal::$_sortorder = "DESC";
                break;
            case "postedasc":
                wpjobportal::$_ordering = "company.created ASC";
                wpjobportal::$_sorton = "posted";
                wpjobportal::$_sortorder = "ASC";
                break;
            default: wpjobportal::$_ordering = "company.created DESC";
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
        wpjobportal::$_sortlinks['name'] = $this->getSortArg("name", $sort);
        wpjobportal::$_sortlinks['category'] = $this->getSortArg("category", $sort);
        wpjobportal::$_sortlinks['location'] = $this->getSortArg("location", $sort);
        wpjobportal::$_sortlinks['posted'] = $this->getSortArg("posted", $sort);
        return;
    }

    function validateUserCompany($wpjobportal_companyid,$wpjobportal_uid){
        if(!is_numeric($wpjobportal_companyid) || !is_numeric($wpjobportal_uid)){
            return false;
        }
        $query = "SELECT id FROM `".wpjobportal::$_db->prefix."wj_portal_companies` WHERE uid = ".esc_sql($wpjobportal_uid)." AND id = ".esc_sql($wpjobportal_companyid);
        $wpjobportal_result = wpjobportal::$_db->get_var($query);
        if($wpjobportal_result){
            return true;
        }
        return false;
    }

    function getSingleCompanyByUid($wpjobportal_uid){
        if(!is_numeric($wpjobportal_uid)){
            return false;
        }
        $query = "SELECT * FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` WHERE uid =" . esc_sql($wpjobportal_uid)." AND status =1 LIMIT 1";
        $wpjobportal_company = wpjobportaldb::get_row($query);
        if($wpjobportal_company){
            $wpjobportal_company->location = WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($wpjobportal_company->city);
        }
        return $wpjobportal_company;
    }

    function userCanAddCompany($wpjobportal_uid){
        # Check Whether Not More than one
        if(!is_numeric($wpjobportal_uid)){
            return false;
        }
        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` WHERE uid =" . esc_sql($wpjobportal_uid);
        $wpjobportal_company = wpjobportaldb::get_var($query);
        if($wpjobportal_company > 0){
            return false;
        }
        return true;
    }

    function getLogoUrl($wpjobportal_companyid,$wpjobportal_logofilename){
        $wpjobportal_logourl = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('employer');
        if(is_numeric($wpjobportal_companyid) && !empty($wpjobportal_logofilename)){
            $wpjobportal_wpdir = wp_upload_dir();
            $wpjobportal_dir = wpjobportal::$_config->getConfigValue('data_directory');
            $wpjobportal_logourl = $wpjobportal_wpdir['baseurl'].'/'.$wpjobportal_dir.'/data/employer/comp_'.$wpjobportal_companyid.'/logo/'.$wpjobportal_logofilename;
        }
        return $wpjobportal_logourl;
    }

     function getCompanyDataFromJobForm($wpjobportal_jobformdata){
        $wpjobportal_companydata = array();
        if(is_array($wpjobportal_jobformdata)){
            $wpjobportal_companycustomfields = array();
            foreach(wpjobportal::$_wpjpfieldordering->getUserfieldsfor(WPJOBPORTAL_COMPANY) as $wpjobportal_field){
                $wpjobportal_companycustomfields[] = $wpjobportal_field->field;
            }
            foreach($wpjobportal_jobformdata as $wpjobportal_name => $wpjobportal_value){
                if(wpjobportalphplib::wpJP_stristr($wpjobportal_name, 'company_')){
                    $wpjobportal_companydata[wpjobportalphplib::wpJP_str_replace('company_', '', $wpjobportal_name)] = $wpjobportal_value;
                }elseif(in_array($wpjobportal_name, $wpjobportal_companycustomfields)){
                    $wpjobportal_companydata[$wpjobportal_name] = $wpjobportal_value;
                }
            }
        }
        return $wpjobportal_companydata;
    }

    // front end coookies search form data
    function getSearchFormDataMyCompany(){
        $wpjobportal_jsjp_search_array = array();
        $wpjobportal_jsjp_search_array['searchcompany'] = WPJOBPORTALrequest::getVar('searchcompany');
        $wpjobportal_jsjp_search_array['wpjobportal-city'] = WPJOBPORTALrequest::getVar('wpjobportal-city');
        $wpjobportal_jsjp_search_array['search_from_myapply_mycompanies'] = 1;
        return $wpjobportal_jsjp_search_array;
    }

    function getCookiesSavedMyCompany(){
        $wpjobportal_jsjp_search_array = array();
        $wpjp_search_cookie_data = '';
        if(isset($_COOKIE['jsjp_jobportal_search_data'])){
            $wpjp_search_cookie_data = wpjobportal::wpjobportal_sanitizeData($_COOKIE['jsjp_jobportal_search_data']);
            $wpjp_search_cookie_data = wpjobportalphplib::wpJP_safe_decoding($wpjp_search_cookie_data);
            $wpjp_search_cookie_data = json_decode( $wpjp_search_cookie_data , true );
        }
        if($wpjp_search_cookie_data != '' && isset($wpjp_search_cookie_data['search_from_myapply_mycompanies']) && $wpjp_search_cookie_data['search_from_myapply_mycompanies'] == 1){
            $wpjobportal_jsjp_search_array['searchcompany'] = $wpjp_search_cookie_data['searchcompany'];
            $wpjobportal_jsjp_search_array['wpjobportal-city'] = $wpjp_search_cookie_data['wpjobportal-city'];
        }
        return $wpjobportal_jsjp_search_array;
    }

    function setSearchVariableMyCompany($wpjobportal_jsjp_search_array){
        wpjobportal::$_search['mycompany']['searchcompany'] = isset($wpjobportal_jsjp_search_array['searchcompany']) ? $wpjobportal_jsjp_search_array['searchcompany'] : null;
        wpjobportal::$_search['mycompany']['wpjobportal-city'] = isset($wpjobportal_jsjp_search_array['wpjobportal-city']) ? $wpjobportal_jsjp_search_array['wpjobportal-city'] : null;
    }

    // Admin search cookies form data
    function getSearchFormAdminCompanyData(){
        $wpjobportal_jsjp_search_array = array();
        $wpjobportal_jsjp_search_array['sorton'] = WPJOBPORTALrequest::getVar('sorton', 'post', 3);
        $wpjobportal_jsjp_search_array['sortby'] = WPJOBPORTALrequest::getVar('sortby', 'post', 2);
        //Filters
        $wpjobportal_jsjp_search_array['searchcompany'] = WPJOBPORTALrequest::getVar('searchcompany');
        $wpjobportal_jsjp_search_array['searchjobcategory'] = WPJOBPORTALrequest::getVar('searchjobcategory');
        $wpjobportal_jsjp_search_array['status'] = WPJOBPORTALrequest::getVar('status');
        $wpjobportal_jsjp_search_array['datestart'] = WPJOBPORTALrequest::getVar('datestart');
        $wpjobportal_jsjp_search_array['dateend'] = WPJOBPORTALrequest::getVar('dateend');
         $wpjobportal_jsjp_search_array['featured'] = WPJOBPORTALrequest::getVar('featured');
        //Front end search var
        $wpjobportal_company = WPJOBPORTALrequest::getVar('wpjobportal-company');
        $wpjobportal_jsjp_search_array['wpjobportal_company'] = wpjobportal::parseSpaces($wpjobportal_company);
        $wpjobportal_jsjp_search_array['wpjobportal_city'] = WPJOBPORTALrequest::getVar('wpjobportal-city');
        $wpjobportal_jsjp_search_array['search_from_admin_company'] = 1;
        return $wpjobportal_jsjp_search_array;
    }

    function getAdminCompanySavedCookies(){
        $wpjobportal_jsjp_search_array = array();
        $wpjp_search_cookie_data = '';
        if(isset($_COOKIE['jsjp_jobportal_search_data'])){
            $wpjp_search_cookie_data = wpjobportal::wpjobportal_sanitizeData($_COOKIE['jsjp_jobportal_search_data']);
            $wpjp_search_cookie_data = wpjobportalphplib::wpJP_safe_decoding($wpjp_search_cookie_data);
            $wpjp_search_cookie_data = json_decode( $wpjp_search_cookie_data , true );
        }
        if($wpjp_search_cookie_data != '' && isset($wpjp_search_cookie_data['search_from_admin_company']) && $wpjp_search_cookie_data['search_from_admin_company'] == 1){
            $wpjobportal_jsjp_search_array['sorton'] = $wpjp_search_cookie_data['sorton'];
            $wpjobportal_jsjp_search_array['sortby'] = $wpjp_search_cookie_data['sortby'];
            $wpjobportal_jsjp_search_array['searchcompany'] = $wpjp_search_cookie_data['searchcompany'];
            $wpjobportal_jsjp_search_array['searchjobcategory'] = $wpjp_search_cookie_data['searchjobcategory'];
            $wpjobportal_jsjp_search_array['status'] = $wpjp_search_cookie_data['status'];
            $wpjobportal_jsjp_search_array['datestart'] = $wpjp_search_cookie_data['datestart'];
            $wpjobportal_jsjp_search_array['dateend'] = $wpjp_search_cookie_data['dateend'];
            $wpjobportal_jsjp_search_array['featured'] = $wpjp_search_cookie_data['featured'];
            $wpjobportal_jsjp_search_array['wpjobportal_company'] = $wpjp_search_cookie_data['wpjobportal_company'];
            $wpjobportal_jsjp_search_array['wpjobportal_company'] = $wpjp_search_cookie_data['wpjobportal_company'];
            $wpjobportal_jsjp_search_array['wpjobportal_city'] = $wpjp_search_cookie_data['wpjobportal_city'];
        }
        return $wpjobportal_jsjp_search_array;
    }

    function setAdminCompanySearchVariable($wpjobportal_jsjp_search_array){
        wpjobportal::$_search['company']['sorton'] = isset($wpjobportal_jsjp_search_array['sorton']) ? $wpjobportal_jsjp_search_array['sorton'] : 3;
        wpjobportal::$_search['company']['sortby'] = isset($wpjobportal_jsjp_search_array['sortby']) ? $wpjobportal_jsjp_search_array['sortby'] : 2;
        wpjobportal::$_search['company']['searchcompany'] = isset($wpjobportal_jsjp_search_array['searchcompany']) ? $wpjobportal_jsjp_search_array['searchcompany'] : '';
        wpjobportal::$_search['company']['searchjobcategory'] = isset($wpjobportal_jsjp_search_array['searchjobcategory']) ? $wpjobportal_jsjp_search_array['searchjobcategory'] : '';
        wpjobportal::$_search['company']['status'] = isset($wpjobportal_jsjp_search_array['status']) ? $wpjobportal_jsjp_search_array['status'] : '';
        wpjobportal::$_search['company']['datestart'] = isset($wpjobportal_jsjp_search_array['datestart']) ? $wpjobportal_jsjp_search_array['datestart'] : '';
        wpjobportal::$_search['company']['dateend'] = isset($wpjobportal_jsjp_search_array['dateend']) ? $wpjobportal_jsjp_search_array['dateend'] : '';
        wpjobportal::$_search['company']['featured'] = isset($wpjobportal_jsjp_search_array['featured']) ? $wpjobportal_jsjp_search_array['featured'] : '';
        wpjobportal::$_search['company']['wpjobportal_company'] = isset($wpjobportal_jsjp_search_array['wpjobportal_company']) ? $wpjobportal_jsjp_search_array['wpjobportal_company'] : '';
        wpjobportal::$_search['company']['wpjobportal_city'] = isset($wpjobportal_jsjp_search_array['wpjobportal_city']) ? $wpjobportal_jsjp_search_array['wpjobportal_city'] : '';
    }

    function getCompaniesForPageBuilderWidget($wpjobportal_no_of_companies,$wpjobportal_company_type){
        $query = "SELECT company.uid,company.name,CONCAT(company.alias,'-',company.id) AS aliasid,
            company.city, company.created,company.logofilename,
            company.status,company.url,company.id,company.params,company.isfeaturedcompany,company.endfeatureddate,company.description
            FROM " . wpjobportal::$_db->prefix . "wj_portal_companies AS company
            LEFT JOIN `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city ON city.id = (SELECT cityid FROM `" . wpjobportal::$_db->prefix . "wj_portal_companycities` WHERE companyid = company.id ORDER BY id DESC LIMIT 1)
            WHERE company.status = 1";
        if($wpjobportal_company_type == 2 && in_array('featuredcompanies',wpjobportal::$_active_addons)){
            $query .=" AND company.isfeaturedcompany=1";
        }
        if(is_numeric($wpjobportal_no_of_companies)){
            $query .= " ORDER BY company.created DESC LIMIT " . esc_sql($wpjobportal_no_of_companies);
        }

        $wpjobportal_companies = wpjobportaldb::get_results($query);
        return $wpjobportal_companies;
    }

    function getPackagePopupForCompanyContactDetail(){
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'get-package-popup-for-company-contact-detail') ) {
            die( 'Security check Failed' );
        }
            $wpjobportal_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
            $wpjobportal_companyid = WPJOBPORTALrequest::getVar('wpjobportalid');
            $wpjobportal_subtype = wpjobportal::$_config->getConfigValue('submission_type');
            #submit type popup for Featured Resume --Listing(Membership)
          // die($wpjobportal_subtype);
            if( $wpjobportal_subtype != 3 ){
                return false;
            }
            $wpjobportal_userpackages = array();
            $wpjobportal_pack = apply_filters('wpjobportal_addons_credit_get_Packages_user',false,$wpjobportal_uid,'companycontactdetail');
            foreach($wpjobportal_pack as $wpjobportal_package){
                if($wpjobportal_package->companycontactdetail == -1 || $wpjobportal_package->remcompanycontactdetail > 0){ //-1 = unlimited
                    $wpjobportal_userpackages[] = $wpjobportal_package;
                }
            }
            $wpjobportal_addonclass = '';
            if(WPJOBPORTALincluder::getJSModel('common')->isElegantDesignEnabled()){
                $wpjobportal_addonclass = ' wjportal-elegant-addon-packages-popup ';
            }
            if (wpjobportal::$wpjobportal_theme_chk == 1) {
                $wpjobportal_content = '
                <div id="wpj-jp-popup-background" style="display: none;"></div>
                <div id="package-popup" class="wpj-jp-popup-wrp wpj-jp-packages-popup">
                    <div class="wpj-jp-popup-cnt-wrp">
                        <i class="fas fa-times wpj-jp-popup-close-icon" data-dismiss="modal"></i>
                        <h3 class="wpj-jp-popup-heading">
                            '.esc_html__("Select Package",'wp-job-portal').'
                            <div class="wpj-jp-popup-desc">
                                '.esc_html__("Please select a package first",'wp-job-portal').'
                            </div>
                        </h3>
                        <div class="wpj-jp-popup-contentarea">
                            <div class="wpj-jp-packages-wrp">';
                                if(count($wpjobportal_userpackages) == 0){
                                    $wpjobportal_content .= WPJOBPORTALmessages::showMessage(esc_html__("You do not have any View Company Contact remaining",'wp-job-portal'),'error',1);
                                } else {
                                    foreach($wpjobportal_userpackages as $wpjobportal_package){
                                        #User Package For Selection in Popup Model --Views
                                        $wpjobportal_content .= '
                                            <div class="wpj-jp-pkg-item" id="package-div-'.esc_attr($wpjobportal_package->id).'" onclick="selectPackage('.esc_attr($wpjobportal_package->id).');">
                                                <div class="wpj-jp-pkg-item-top">
                                                    <h4 class="wpj-jp-pkg-item-title">'.wpjobportal::wpjobportal_getVariableValue( $wpjobportal_package->title).'</h4>
                                                </div>
                                                <div class="wpj-jp-pkg-item-mid">
                                                    <div class="wpj-jp-pkg-item-row">
                                                        <span class="wpj-jp-pkg-item-tit">
                                                            '.esc_html__("View Company Contact",'wp-job-portal').' :
                                                        </span>
                                                        <span class="wpj-jp-pkg-item-val">
                                                            '.($wpjobportal_package->companycontactdetail==-1 ? esc_html__("Unlimited",'wp-job-portal') : esc_attr($wpjobportal_package->companycontactdetail)).'
                                                        </span>
                                                    </div>
                                                    <div class="wpj-jp-pkg-item-row">
                                                        <span class="wpj-jp-pkg-item-tit">
                                                            '.esc_html__("Remaining View Company Contact",'wp-job-portal').' :
                                                        </span>
                                                        <span class="wpj-jp-pkg-item-val">
                                                            '.($wpjobportal_package->companycontactdetail==-1 ? esc_html__("Unlimited",'wp-job-portal') : esc_attr($wpjobportal_package->remcompanycontactdetail)).'
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="wpj-jp-pkg-item-btm">
                                                    <a href="#" class="wpj-jp-outline-btn wpj-jp-block-btn" onclick="selectPackage('.esc_attr($wpjobportal_package->id).');" title="'.esc_attr__("Select Package","wp-job-portal").'">
                                                        '.esc_html__("Select Package","wp-job-portal").'
                                                    </a>
                                                </div>
                                            </div>
                                        ';
                                    }
                                }
                            $wpjobportal_content .= '</div>
                            <div class="wpj-jp-popup-msgs" id="wjportal-package-message">&nbsp;</div>
                        </div>';
                        // if user does not have any package do not show the button to view company contact detail on popup
                    if(count($wpjobportal_userpackages) != 0){
                        $wpjobportal_content .= '
                        <div class="wpj-jp-visitor-msg-btn-wrp">
                            <form action="'.esc_url(wp_nonce_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company','action'=>'wpjobportaltask','task'=>'addviewcontactdetail','wpjobportalid'=>esc_attr($wpjobportal_companyid),'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid())),'wpjobportal_company_nonce')).'" method="post">
                                <input type="hidden" id="wpjobportal_packageid" name="wpjobportal_packageid">
                                <input type="submit" rel="button" id="jsre_featured_button" class="wpj-jp-visitor-msg-btn" value="'.esc_attr__('Show Company Contact','wp-job-portal').'" disabled/>
                            </form>
                        </div>';
                    }
                        $wpjobportal_content .= '
                    </div>
                </div>';
            } else {
            $wpjobportal_content = '
            <div id="wjportal-popup-background" style="display: none;"></div>
            <div id="package-popup" class="wjportal-popup-wrp wjportal-packages-popup '.$wpjobportal_addonclass.'">
                <div class="wjportal-popup-cnt">
                    <img id="wjportal-popup-close-btn" alt="popup cross" src="'.esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/images/popup-close.png">
                    <div class="wjportal-popup-title">
                        '.__("Select Package",'wp-job-portal').'
                        <div class="wjportal-popup-title3">
                            '.__("Please select a package first",'wp-job-portal').'
                        </div>
                    </div>
                    <div class="wjportal-popup-contentarea">
                        <div class="wjportal-packages-wrp">';
                            if(count($wpjobportal_userpackages) == 0){
                                $wpjobportal_content .= WPJOBPORTALmessages::showMessage(__("You do not have any View Company Contact remaining",'wp-job-portal'),'error',1);
                            } else {
                                foreach($wpjobportal_userpackages as $wpjobportal_package){
                                    #User Package For Selection in Popup Model --Views
                                    $wpjobportal_content .= '
                                        <div class="wjportal-pkg-item" id="package-div-'.esc_attr($wpjobportal_package->id).'" onclick="selectPackage('.esc_js($wpjobportal_package->id).');">
                                            <div class="wjportal-pkg-item-top">
                                                <div class="wjportal-pkg-item-title">'.esc_html($wpjobportal_package->title).'</div>
                                            </div>
                                            <div class="wjportal-pkg-item-btm">
                                                <div class="wjportal-pkg-item-row">
                                                    <span class="wjportal-pkg-item-tit">
                                                        '.__("View Company Contact",'wp-job-portal').' :
                                                    </span>
                                                    <span class="wjportal-pkg-item-val">
                                                        '.($wpjobportal_package->companycontactdetail==-1 ? __("Unlimited",'wp-job-portal') : esc_html($wpjobportal_package->companycontactdetail)).'
                                                    </span>
                                                </div>
                                                <div class="wjportal-pkg-item-row">
                                                    <span class="wjportal-pkg-item-tit">
                                                        '.__("Remaining View Company Contact",'wp-job-portal').' :
                                                    </span>
                                                    <span class="wjportal-pkg-item-val">
                                                        '.($wpjobportal_package->companycontactdetail==-1 ? __("Unlimited",'wp-job-portal') : esc_html($wpjobportal_package->remcompanycontactdetail)).'
                                                    </span>
                                                </div>
                                                <div class="wjportal-pkg-item-btn-row">
                                                    <a href="#" class="wjportal-pkg-item-btn">
                                                        '.__("Select Package","wp-job-portal").'
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    ';
                                }
                                /*$wpjobportal_content .= '<div class="wjportal-pkg-help-txt">
                                                '.__("Click on package to select one",'wp-job-portal').'
                                            </div>';*/
                            }
                        $wpjobportal_content .= '</div>
                        <div class="wjportal-popup-msgs" id="wjportal-package-message">&nbsp;</div>
                    </div>
                    <div class="wjportal-visitor-msg-btn-wrp">
                        <form action="'.esc_url(wp_nonce_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'company','action'=>'wpjobportaltask','task'=>'addviewcontactdetail','wpjobportalid'=>$wpjobportal_companyid,'wpjobportalpageid'=>wpjobportal::wpjobportal_getPageid())),'wpjobportal_company_nonce')).'" method="post">
                            <input type="hidden" id="wpjobportal_packageid" name="wpjobportal_packageid">

                                <input type="submit" rel="button" id="jsre_featured_button" class="wjportal-visitor-msg-btn" value="'.esc_html__('Show Company Contact','wp-job-portal').'" disabled/>
                        </form>
                    </div>
                </div>
            </div>';
            }
            echo wp_kses($wpjobportal_content, WPJOBPORTAL_ALLOWED_TAGS);
            exit();
    }

    function getCompanies($only_featured_companies = 0) {

            //Filters
            $wpjobportal_searchcompany = isset(wpjobportal::$_search['search_filter']['searchcompany']) ? wpjobportal::$_search['search_filter']['searchcompany']: '';
            //$wpjobportal_searchcompcategory = isset(wpjobportal::$_search['search_filter']['searchcompany']) ? wpjobportal::$_search['search_filter']['searchcompany']: '';

            //Front end search var
            $wpjobportal_city = isset(wpjobportal::$_search['search_filter']['wpjobportal_city']) ? wpjobportal::$_search['search_filter']['wpjobportal_city']: '';
            // $wpjobportal_formsearch = WPJOBPORTALrequest::getVar('WPJOBPORTAL_form_search', 'post');
            // if ($wpjobportal_formsearch == 'WPJOBPORTAL_SEARCH') {
            //     $_SESSION['WPJOBPORTAL_SEARCH']['searchcompany'] = $wpjobportal_searchcompany;
            //     $_SESSION['WPJOBPORTAL_SEARCH']['searchcompcategory'] = $wpjobportal_searchcompcategory;
            //     $_SESSION['WPJOBPORTAL_SEARCH']['wpjobportal_city'] = $wpjobportal_city;
            // }
            // if (WPJOBPORTALrequest::getVar('pagenum', 'get', null) != null) {
            //     $wpjobportal_searchcompany = (isset($_SESSION['WPJOBPORTAL_SEARCH']['searchcompany']) && $_SESSION['WPJOBPORTAL_SEARCH']['searchcompany'] != '') ? sanitize_key($_SESSION['WPJOBPORTAL_SEARCH']['searchcompany']) : null;
            //     $wpjobportal_searchcompcategory = (isset($_SESSION['WPJOBPORTAL_SEARCH']['searchcompcategory']) && $_SESSION['WPJOBPORTAL_SEARCH']['searchcompcategory'] != '') ? sanitize_key($_SESSION['WPJOBPORTAL_SEARCH']['searchcompcategory']) : null;
            //     $wpjobportal_city = (isset($_SESSION['WPJOBPORTAL_SEARCH']['wpjobportal_city']) && $_SESSION['WPJOBPORTAL_SEARCH']['wpjobportal_city'] != '') ? sanitize_key($_SESSION['WPJOBPORTAL_SEARCH']['wpjobportal_city']) : null;
            // } elseif ($wpjobportal_formsearch !== 'WPJOBPORTAL_SEARCH') {
            //     if (isset($_SESSION['WPJOBPORTAL_SEARCH'])) {
            //         unset($_SESSION['WPJOBPORTAL_SEARCH']);
            //     }
            // }
            // if ($wpjobportal_searchcompcategory)
            //     if (is_numeric($wpjobportal_searchcompcategory) == false)
            //         return false;
            $wpjobportal_inquery = '';
            if ($wpjobportal_searchcompany) {
                $wpjobportal_inquery = " AND LOWER(company.name) LIKE '%".esc_sql($wpjobportal_searchcompany)."%'";
            }
            if ($wpjobportal_city) {
                if(is_numeric($wpjobportal_city)){
                    $wpjobportal_inquery .= " AND FIND_IN_SET('" . esc_sql($wpjobportal_city) . "', company.city) > 0 ";
                }else{
                    $wpjobportal_arr = wpjobportalphplib::wpJP_explode( ',' , esc_sql($wpjobportal_city));
                    $cityQuery = false;
                    foreach($wpjobportal_arr as $wpjobportal_i){
                        if($cityQuery){
                            $cityQuery .= " OR FIND_IN_SET('" . esc_sql($wpjobportal_i) . "', company.city) > 0 ";
                        }else{
                            $cityQuery = " FIND_IN_SET('" . esc_sql($wpjobportal_i) . "', company.city) > 0 ";
                        }
                    }
                    $wpjobportal_inquery .= " AND ( $cityQuery ) ";
                }
            }
            // if ($wpjobportal_searchcompcategory) {
            //     $wpjobportal_inquery .= " AND company.category = " . esc_sql($wpjobportal_searchcompcategory);
            // }

            if($only_featured_companies == 1){
                $wpjobportal_inquery .= " AND company.isfeaturedcompany = 1 AND DATE(company.endfeatureddate) >= CURDATE() ";
            }

            // this function is used for more than one case ?? not sure atm!!

            // by default these options are set to 0(so the data will be visible.)
            wpjobportal::$_data['shortcode_option_hide_company_logo'] = 0;
            wpjobportal::$_data['shortcode_option_hide_company_name'] = 0;

            wpjobportal::$_ordering = "company.created DESC"; // defult value for ordering(handling without shortocde calls to this function)
            $wpjobportal_noofcompanies = '';
            $wpjobportal_module_name = WPJOBPORTALrequest::getVar('wpjobportalme');
            if($wpjobportal_module_name == 'allcompanies'){
                //shortcode attribute proceesing (filter,ordering,no of jobs)
                $attributes_query = $this->processShortcodeAttributesCompany();
                if($attributes_query != ''){
                    $wpjobportal_inquery .= $attributes_query;
                }
                if(isset(wpjobportal::$_data['shortcode_option_no_of_companies']) && wpjobportal::$_data['shortcode_option_no_of_companies'] > 0){
                    $wpjobportal_noofcompanies = wpjobportal::$_data['shortcode_option_no_of_companies'];
                }
            }

            wpjobportal::$_data['filter']['wpjobportal-city'] = WPJOBPORTALincluder::getJSModel('common')->getCitiesForFilter($wpjobportal_city);
            wpjobportal::$_data['filter']['searchcompany'] = $wpjobportal_searchcompany;
        // this field does not exsist
            //wpjobportal::$_data['filter']['searchcompcategory'] = $wpjobportal_searchcompcategory;


            //Pagination
            if($wpjobportal_noofcompanies == ''){
                $query = "SELECT COUNT(id) FROM " . wpjobportal::$_db->prefix . "wj_portal_companies AS company WHERE status = 1";
                $query .=$wpjobportal_inquery;

                $wpjobportal_total = wpjobportaldb::get_var($query);
                wpjobportal::$_data['total'] = $wpjobportal_total;
                wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($wpjobportal_total);
            }
            //Data
            $query = "SELECT company.id,company.name,company.logofilename,CONCAT(company.alias,'-',company.id) AS aliasid,company.created,company.serverid,company.city,company.status,company.isfeaturedcompany
                     ,company.endfeatureddate,company.params,company.url,company.contactemail
                    FROM " . wpjobportal::$_db->prefix . "wj_portal_companies AS company
                    WHERE company.status = 1 ";
            $query .= $wpjobportal_inquery;
            $query .= " ORDER BY ".wpjobportal::$_ordering;
            if($wpjobportal_noofcompanies == ''){
                $query .= " LIMIT " . WPJOBPORTALpagination::$_offset . "," . WPJOBPORTALpagination::$_limit;
            }elseif(is_numeric($wpjobportal_noofcompanies)){
                $query.= " LIMIT " . esc_sql($wpjobportal_noofcompanies);
            }

            wpjobportal::$_data[0] = wpjobportaldb::get_results($query);
            $wpjobportal_data = array();
            foreach (wpjobportal::$_data[0] AS $d) {
                $d->location = WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($d->city);
                $wpjobportal_data[] = $d;
            }
            wpjobportal::$_data[0] = $wpjobportal_data;
            wpjobportal::$wpjobportal_data['fields'] = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldsOrderingforView(1);
            wpjobportal::$_data['config'] = wpjobportal::$_config->getConfigByFor('company');
            return;
        }

        function processShortcodeAttributesCompany(){
            $wpjobportal_inquery = '';
            // cities
            $cities_list = WPJOBPORTALrequest::getVar('locations', 'shortcode_option', false);
            if ($cities_list && $cities_list !='' ) { // not empty check
                $city_array = wpjobportalphplib::wpJP_explode( ',' , esc_sql($cities_list)); // handle multi case
                $cityQuery = false;
                foreach($city_array as $city_id){ // loop over all ids
                    if($city_id != ''){ // null check
                        $city_id = trim($city_id);
                    }
                    if(!is_numeric($city_id)){ // numric check
                        continue;
                    }
                    if($cityQuery){
                        $cityQuery .= " OR FIND_IN_SET('" . esc_sql($city_id) . "', company.city) > 0 ";
                    }else{
                        $cityQuery = " FIND_IN_SET('" . esc_sql($city_id) . "', company.city) > 0 ";
                    }
                }
                $wpjobportal_inquery .= " AND ( $cityQuery ) ";
            }

            // employers
            $wpjobportal_employer_list = WPJOBPORTALrequest::getVar('employers', 'shortcode_option', false);
            if ($wpjobportal_employer_list && $wpjobportal_employer_list !='' ) { // not empty check
                $wpjobportal_employer_array = wpjobportalphplib::wpJP_explode( ',' , esc_sql($wpjobportal_employer_list)); // handle multi case
                $wpjobportal_employerQuery = false;
                foreach($wpjobportal_employer_array as $wpjobportal_employer_id){ // loop over all ids
                    if($wpjobportal_employer_id != ''){ // null check
                        $wpjobportal_employer_id = trim($wpjobportal_employer_id);
                    }
                    if(!is_numeric($wpjobportal_employer_id)){ // numric check
                        continue;
                    }
                    if($wpjobportal_employerQuery){
                        $wpjobportal_employerQuery .= " OR company.uid  = " . esc_sql($wpjobportal_employer_id);
                    }else{
                        $wpjobportal_employerQuery = " company.uid  =  " . esc_sql($wpjobportal_employer_id);
                    }
                }
                $wpjobportal_inquery .= " AND ( $wpjobportal_employerQuery ) ";
            }

            // company_ids
            $wpjobportal_company_list = WPJOBPORTALrequest::getVar('ids', 'shortcode_option', false);
            if ($wpjobportal_company_list && $wpjobportal_company_list !='' ) { // not empty check
                $wpjobportal_company_array = wpjobportalphplib::wpJP_explode( ',' , esc_sql($wpjobportal_company_list)); // handle multi case
                $wpjobportal_companyQuery = false;
                foreach($wpjobportal_company_array as $wpjobportal_company_id){ // loop over all ids
                    if($wpjobportal_company_id != ''){ // null check
                        $wpjobportal_company_id = trim($wpjobportal_company_id);
                    }
                    if(!is_numeric($wpjobportal_company_id)){ // numric check
                        continue;
                    }
                    if($wpjobportal_companyQuery){
                        $wpjobportal_companyQuery .= " OR company.id  = " . esc_sql($wpjobportal_company_id);
                    }else{
                        $wpjobportal_companyQuery = " company.id  =  " . esc_sql($wpjobportal_company_id);
                    }
                }
                $wpjobportal_inquery .= " AND ( $wpjobportal_companyQuery ) ";
            }


            //handle attirbute for ordering
            $sorting = WPJOBPORTALrequest::getVar('sorting', 'shortcode_option', false);
            if($sorting && $sorting != ''){
                $this->makeOrderingQueryFromShortcodeAttributesCompany($sorting);
            }

            //handle attirbute for no of jobs
            $wpjobportal_no_of_companies = WPJOBPORTALrequest::getVar('no_of_companies', 'shortcode_option', false);
            if($wpjobportal_no_of_companies && $wpjobportal_no_of_companies != ''){
                wpjobportal::$_data['shortcode_option_no_of_companies'] = (int) $wpjobportal_no_of_companies;
            }


            // handle visibilty of data based on shortcode
            $this->handleDataVisibilityByShortcodeAttributesCompany();
            return $wpjobportal_inquery;

        }


        function makeOrderingQueryFromShortcodeAttributesCompany($sorting) {
            switch ($sorting) {
                case "name_desc":
                    wpjobportal::$_ordering = "company.name DESC";
                    break;
                case "name_asc":
                    wpjobportal::$_ordering = "company.name ASC";
                    break;
                case "posted_desc":
                    wpjobportal::$_ordering = "company.created DESC";
                    break;
                case "posted_asc":
                    wpjobportal::$_ordering = "company.created ASC";
                    break;
            }
            return;
        }

        function handleDataVisibilityByShortcodeAttributesCompany() {
            /*
                'hide_filter' => '',
                'hide_filter_job_title' => '',
                'hide_filter_job_location' => '',
            */

            //handle attirbute for hide company logo on all company listing
            $wpjobportal_hide_company_logo = WPJOBPORTALrequest::getVar('hide_company_logo', 'shortcode_option', false);
            if($wpjobportal_hide_company_logo && $wpjobportal_hide_company_logo != ''){
                wpjobportal::$_data['shortcode_option_hide_company_logo'] = 1;
            }

            //handle attirbute for hide company name on all company listing
            $wpjobportal_hide_company_name = WPJOBPORTALrequest::getVar('hide_company_name', 'shortcode_option', false);
            if($wpjobportal_hide_company_name && $wpjobportal_hide_company_name != ''){
                wpjobportal::$_data['shortcode_option_hide_company_name'] = 1;
            }

            //handle attirbute for hide company name on all company listing
            $wpjobportal_hide_company_location = WPJOBPORTALrequest::getVar('hide_company_location', 'shortcode_option', false);
            if($wpjobportal_hide_company_location && $wpjobportal_hide_company_location != ''){
                wpjobportal::$_data['shortcode_option_hide_company_location'] = 1;
            }

        }
}
?>
