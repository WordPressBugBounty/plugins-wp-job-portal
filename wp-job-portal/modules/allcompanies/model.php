<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
class WPJOBPORTALallCompaniesMODEL{
    public $class_prefix = '';

        function __construct(){
            if(wpjobportal::$wpjobportal_theme_chk == 1){
                $this->class_prefix = 'wpj-jp';
            } else {
                $this->class_prefix = 'wjportal';
            }
        }

        function getCompanies($only_featured_companies = 0) {

            //Filters
            $searchcompany = isset(wpjobportal::$_search['search_filter']['searchcompany']) ? wpjobportal::$_search['search_filter']['searchcompany']: '';
            //$searchcompcategory = isset(wpjobportal::$_search['search_filter']['searchcompany']) ? wpjobportal::$_search['search_filter']['searchcompany']: '';

            //Front end search var
            $wpjobportal_city = isset(wpjobportal::$_search['search_filter']['wpjobportal_city']) ? wpjobportal::$_search['search_filter']['wpjobportal_city']: '';
            // $formsearch = WPJOBPORTALrequest::getVar('WPJOBPORTAL_form_search', 'post');
            // if ($formsearch == 'WPJOBPORTAL_SEARCH') {
            //     $_SESSION['WPJOBPORTAL_SEARCH']['searchcompany'] = $searchcompany;
            //     $_SESSION['WPJOBPORTAL_SEARCH']['searchcompcategory'] = $searchcompcategory;
            //     $_SESSION['WPJOBPORTAL_SEARCH']['wpjobportal_city'] = $wpjobportal_city;
            // }
            // if (WPJOBPORTALrequest::getVar('pagenum', 'get', null) != null) {
            //     $searchcompany = (isset($_SESSION['WPJOBPORTAL_SEARCH']['searchcompany']) && $_SESSION['WPJOBPORTAL_SEARCH']['searchcompany'] != '') ? sanitize_key($_SESSION['WPJOBPORTAL_SEARCH']['searchcompany']) : null;
            //     $searchcompcategory = (isset($_SESSION['WPJOBPORTAL_SEARCH']['searchcompcategory']) && $_SESSION['WPJOBPORTAL_SEARCH']['searchcompcategory'] != '') ? sanitize_key($_SESSION['WPJOBPORTAL_SEARCH']['searchcompcategory']) : null;
            //     $wpjobportal_city = (isset($_SESSION['WPJOBPORTAL_SEARCH']['wpjobportal_city']) && $_SESSION['WPJOBPORTAL_SEARCH']['wpjobportal_city'] != '') ? sanitize_key($_SESSION['WPJOBPORTAL_SEARCH']['wpjobportal_city']) : null;
            // } elseif ($formsearch !== 'WPJOBPORTAL_SEARCH') {
            //     if (isset($_SESSION['WPJOBPORTAL_SEARCH'])) {
            //         unset($_SESSION['WPJOBPORTAL_SEARCH']);
            //     }
            // }
            // if ($searchcompcategory)
            //     if (is_numeric($searchcompcategory) == false)
            //         return false;
            $inquery = '';
            if ($searchcompany) {
                $inquery = " AND LOWER(company.name) LIKE '%".esc_sql($searchcompany)."%'";
            }
            if ($wpjobportal_city) {
                $arr = array_filter(array_map('absint', wpjobportalphplib::wpJP_explode(',', (string) $wpjobportal_city)));
                $cityQuery = false;
                foreach($arr as $i){
                    if($cityQuery){
                        $cityQuery .= " OR FIND_IN_SET('" . (int) $i . "', company.city) > 0 ";
                    }else{
                        $cityQuery = " FIND_IN_SET('" . (int) $i . "', company.city) > 0 ";
                    }
                }
                if ($cityQuery) {
                    $inquery .= " AND ( $cityQuery ) ";
                }
            }
            // if ($searchcompcategory) {
            //     $inquery .= " AND company.category = " . (int) ($searchcompcategory);
            // }

            if($only_featured_companies == 1){
                $inquery .= " AND company.isfeaturedcompany = 1 AND DATE(company.endfeatureddate) >= CURDATE() ";
            }
            wpjobportal::$_data['filter']['wpjobportal-city'] = WPJOBPORTALincluder::getJSModel('common')->getCitiesForFilter($wpjobportal_city);
            wpjobportal::$_data['filter']['searchcompany'] = $searchcompany;
        // this field does not exsist
            //wpjobportal::$_data['filter']['searchcompcategory'] = $searchcompcategory;

            //Pagination
            $query = "SELECT COUNT(id) FROM " . wpjobportal::$_db->prefix . "wj_portal_companies AS company WHERE status = 1";
            $query .=$inquery;

            $total = wpjobportaldb::get_var($query);
            wpjobportal::$_data['total'] = $total;
            wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($total);
            //Data
            $query = "SELECT company.id,company.name,company.logofilename,CONCAT(company.alias,'-',company.id) AS aliasid,company.created,company.serverid,company.city,company.status,company.isfeaturedcompany
                     ,company.endfeatureddate,company.params,company.url,company.contactemail
                    FROM " . wpjobportal::$_db->prefix . "wj_portal_companies AS company
                    WHERE company.status = 1 ";
            $query .=$inquery;
            $query .= " ORDER BY company.created DESC LIMIT " . WPJOBPORTALpagination::$_offset . "," . WPJOBPORTALpagination::$_limit;
            wpjobportal::$_data[0] = wpjobportaldb::get_results($query);
            $data = array();
            foreach (wpjobportal::$_data[0] AS $d) {
                $d->location = WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($d->city);
                $data[] = $d;
            }
            wpjobportal::$_data[0] = $data;
            wpjobportal::$wpjobportal_data['fields'] = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldsOrderingforView(1);
            wpjobportal::$_data['config'] = wpjobportal::$_config->getConfigByFor('company');
            return;
        }


}
