<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALthirdpartyimportModel {

    private $_params_flag;
    private $_params_string;


    // job manager import data

    private $job_manager_company_custom_fields = array();
    private $job_manager_job_custom_fields = array();
    private $job_manager_resume_custom_fields = array();

    private $job_manager_users_array = array();

    private $job_manager_company_ids = array();
    private $job_manager_job_ids = array();
    private $job_manager_resume_ids = array();
    private $job_manager_jobapply_ids = array();
    private $job_manager_user_ids = array();
    private $job_manager_jobtype_ids = array();
    private $job_manager_category_ids = array();
    private $job_manager_tag_ids = array();

    // values for counts
    private $job_manager_import_count = [];

    function __construct() {
        $this->_params_flag = 0;
        $this->job_manager_import_count =
            [
                'company' => [
                    'imported' => 0,
                    'skipped'  => 0,
                    'failed'   => 0,
                ],
                'job' => [
                    'imported' => 0,
                    'skipped'  => 0,
                    'failed'   => 0,
                ],
                'resume' => [
                    'imported' => 0,
                    'skipped'  => 0,
                    'failed'   => 0,
                ],
                'user' => [
                    'imported' => 0,
                    'skipped'  => 0,
                    'failed'   => 0,
                ],
                'field' => [
                    'imported' => 0,
                    'skipped'  => 0,
                    'failed'   => 0,
                ],

                'jobtype' => [
                    'imported' => 0,
                    'skipped'  => 0,
                    'failed'   => 0,
                ],
                'category' => [
                    'imported' => 0,
                    'skipped'  => 0,
                    'failed'   => 0,
                ],
                'tag' => [
                    'imported' => 0,
                    'skipped'  => 0,
                    'failed'   => 0,
                ],
                'jobapply' => [
                    'imported' => 0,
                    'skipped'  => 0,
                    'failed'   => 0,
                ]
            ];
    }


    // function getJobManagerDataStats() {
    //     $wpjobportal_entity_counts = [];


    //     // Users ?? need to process further
    //     $wpjobportal_user_query = new WP_User_Query(['count_total' => true]);
    //     $wpjobportal_entity_counts['users'] = $wpjobportal_user_query->get_total();

    //     // Jobs ?? status
    //     $wpjobportal_entity_counts['jobs'] = wp_count_posts('job_listing')->publish;

    //     // Companies (if WP Job Manager Companies addon)
    //     if (post_type_exists('company')) {
    //         $wpjobportal_entity_counts['companies'] = wp_count_posts('company')->publish;
    //     }

    //     // Resumes (if Resume Manager addon)
    //     if (post_type_exists('resume')) {
    //         $wpjobportal_entity_counts['resumes'] = wp_count_posts('resume')->publish;
    //     }

    //     // Job Applications (if using Applications addon)
    //     if (post_type_exists('job_application')) {
    //         $wpjobportal_entity_counts['job_applications'] = wp_count_posts('job_application')->publish;
    //     }

    //     // 6. Categories (job_listing_category taxonomy)
    //     $wpjobportal_categories = get_terms([
    //         'taxonomy'   => 'job_listing_category',
    //         'hide_empty' => false,
    //         'fields'     => 'ids'
    //     ]);
    //     $wpjobportal_entity_counts['categories'] = is_array($wpjobportal_categories) ? count($wpjobportal_categories) : 0;

    //     // 7. Job Types (job_listing_type taxonomy)
    //     $wpjobportal_job_types = get_terms([
    //         'taxonomy'   => 'job_listing_type',
    //         'hide_empty' => false,
    //         'fields'     => 'ids'
    //     ]);
    //     $wpjobportal_entity_counts['job_types'] = is_array($wpjobportal_job_types) ? count($wpjobportal_job_types) : 0;

    //     // 8. Tags
    //     $wpjobportal_tags = get_terms([
    //         'taxonomy'   => 'job_listing_tag',
    //         'hide_empty' => false,
    //     ]);
    //     $wpjobportal_entity_counts['tags'] = is_array($wpjobportal_tags) ? count($wpjobportal_tags) : 0;

    //     return $wpjobportal_entity_counts;
    // }

    function getPostConutByType ( $post_type ) {
        $wpjobportal_counts = wp_count_posts( $post_type );
        return isset( $wpjobportal_counts->publish ) ? (int) $wpjobportal_counts->publish : 0;
    }

    function getJobManagerDataStats($wpjobportal_count_for) {
        // $wpjobportal_count_for handles different plugins

        //  at the moment only 1 is supported/ 1 is for wp job manager
        if($wpjobportal_count_for != 1){
            return;
        }

        // Make sure WP Job Manager is active
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        if ( ! is_plugin_active( 'wp-job-manager/wp-job-manager.php' ) ) {
            //return;
            //return new WP_Error( 'wpjm_inactive', 'WP Job Manager is not active.' );
        }

        $wpjobportal_entity_counts = [];

        // Users
        // $wpjobportal_user_query = new WP_User_Query( [ 'count_total' => true ] );
        // $wpjobportal_entity_counts['users'] = (int) $wpjobportal_user_query->get_total();

        // Jobs
        //$wpjobportal_entity_counts['jobs'] = $this->getPostConutByType( 'job_listing' );

        $wpjobportal_jobs = get_posts([
            'post_type'      => 'job_listing',
            'post_status'    => 'any', // will include all except 'auto-draft'
            'orderby'        => 'ID',
            'order'          => 'ASC',
            'numberposts'    => -1, // fetch all posts
            'exclude'        => wp_list_pluck(
                get_posts([
                    'post_type'   => 'job_listing',
                    'post_status'=> 'auto-draft',
                    'fields'     => 'ids',
                    'numberposts'=> -1
                ]),
                null
            ),
        ]);
        $wpjobportal_entity_counts['jobs'] = count($wpjobportal_jobs);

        // Companies
        $wpjobportal_entity_counts['companies'] = post_type_exists( 'company_listings' ) ? $this->getPostConutByType( 'company_listings' ) : 0;

        // Resumes
        //$wpjobportal_entity_counts['resumes'] = post_type_exists( 'resume' ) ? $this->getPostConutByType( 'resume' ) : 0;
        $wpjobportal_resumes = get_posts( array(
                'post_type'      => 'resume',
                'post_status'    => array_diff( get_post_stati(), array( 'auto-draft' ) ),
                'numberposts'    => -1, // get all
                'orderby'        => 'ID',
                'order'          => 'ASC',
            ) );
        $wpjobportal_entity_counts['resumes'] = count($wpjobportal_resumes);


        // Job Applications
        // $wpjobportal_entity_counts['job_applies'] = post_type_exists( 'job_application' ) ? $this->getPostConutByType( 'job_application' ) : 0;

        $wpjobportal_job_applications = get_posts( [
            'post_type'      => 'job_application',
            'post_status'    => 'any', // includes all except 'auto-draft'
            'orderby'        => 'ID',
            'order'          => 'ASC',
            'numberposts'    => -1, // get all
            'exclude'        => get_posts([
                'post_type'   => 'job_application',
                'post_status'=> 'auto-draft',
                'fields'      => 'ids',
            ]),
        ] );

        $wpjobportal_entity_counts['job_applies'] = count($wpjobportal_job_applications);

        // Categories (job_listing_category taxonomy)
        // $wpjobportal_categories = get_terms([
        //     'taxonomy'   => 'job_listing_category',
        //     'hide_empty' => false,
        //     'fields'     => 'ids'
        // ]);
        // $wpjobportal_entity_counts['job_categories'] = is_array($wpjobportal_categories) ? count($wpjobportal_categories) : 0;


        $wpjobportal_job_categories = get_terms([
            'taxonomy'   => 'job_listing_category',
            'hide_empty' => false,
        ]);

        $wpjobportal_resume_categories = get_terms([
            'taxonomy'   => 'resume_category',
            'hide_empty' => false,
        ]);
        $wpjobportal_categories = [];
        if (!is_wp_error($wpjobportal_job_categories) && is_array($wpjobportal_job_categories)) {
            if(is_array($wpjobportal_resume_categories)){
                $wpjobportal_categories = array_merge($wpjobportal_job_categories, $wpjobportal_resume_categories);
            }else{
                $wpjobportal_categories = $wpjobportal_job_categories;
            }
        }

        $wpjobportal_entity_counts['job_categories'] = count($wpjobportal_categories);

        // Job Types (job_listing_type taxonomy)
        $wpjobportal_job_types = get_terms([
            'taxonomy'   => 'job_listing_type',
            'hide_empty' => false,
            'fields'     => 'ids'
        ]);
        $wpjobportal_entity_counts['job_types'] = is_array($wpjobportal_job_types) ? count($wpjobportal_job_types) : 0;

        // Tags
        // $wpjobportal_tags = get_terms([
        //     'taxonomy'   => 'job_listing_tag',
        //     'hide_empty' => false,
        // ]);

        $query = "SELECT taxonomy.*, terms.*
                    FROM `" . wpjobportal::$_db->prefix . "term_taxonomy` AS taxonomy
                    JOIN `" . wpjobportal::$_db->prefix . "terms` AS terms ON terms.term_id = taxonomy.term_id
                    WHERE taxonomy.taxonomy = 'job_listing_tag';";
        $wpjobportal_tags = wpjobportal::$_db->get_results($query);
        $wpjobportal_entity_counts['tags'] = is_array($wpjobportal_tags) ? count($wpjobportal_tags) : 0;



        // Field count
        $wpjobportal_field_count = 0;

        $wpjobportal_all_custom_fields = get_option("_transient_jmfe_fields_custom");
        if (!empty($wpjobportal_all_custom_fields)) {
            foreach ($wpjobportal_all_custom_fields as $wpjobportal_key => $wpjobportal_value) {
                if ($wpjobportal_key === 'company' || $wpjobportal_key === 'job' || $wpjobportal_key === 'resume_fields') {
                    foreach ($wpjobportal_value as $custom_field) {
                        $wpjobportal_field_count++;
                    }
                }
            }
        }
        $wpjobportal_entity_counts['fields'] = $wpjobportal_field_count;

        wpjobportal::$_data['entity_counts'] = $wpjobportal_entity_counts;

        return;
    }

    // delete data only for development

    function deletejobmanagerimporteddata(){

        $query = "DELETE FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` WHERE id >3;";
        wpjobportal::$_db->query($query);

        $query = "DELETE FROM `" . wpjobportal::$_db->prefix . "wj_portal_fieldsordering` WHERE id >200;";
        wpjobportal::$_db->query($query);

        $query = "DELETE FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` WHERE id > 9;";
        wpjobportal::$_db->query($query);

        $query = "DELETE FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` WHERE id > 10;";
        wpjobportal::$_db->query($query);

        $query = "DELETE FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobcities` WHERE id > 9;";
        wpjobportal::$_db->query($query);

        $query = "DELETE FROM `" . wpjobportal::$_db->prefix . "wj_portal_resume` WHERE id > 9;";
        wpjobportal::$_db->query($query);

        $query = "DELETE FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumeaddresses` WHERE id > 9;";
        wpjobportal::$_db->query($query);
        $query = "DELETE FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumeinstitutes` WHERE id > 1;";
        wpjobportal::$_db->query($query);
        $query = "DELETE FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumeemployers` WHERE id > 1;";
        wpjobportal::$_db->query($query);

        $query = "DELETE FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobapply` WHERE id > 1;";
        wpjobportal::$_db->query($query);

        $query = "DELETE FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` WHERE id > 3;";
        wpjobportal::$_db->query($query);

        $query = "DELETE FROM `" . wpjobportal::$_db->prefix . "wj_portal_categories` WHERE id > 238;";
        wpjobportal::$_db->query($query);
        // $query = "DELETE FROM `" . wpjobportal::$_db->prefix . "wj_portal_tags` WHERE id > 0;";
        // wpjobportal::$_db->query($query);

    }


    function importJobManagerData(){

        // only for delvelopment remove it befoore live
        //$this->deletejobmanagerimporteddata();

        // removing ids from options
        //update_option('job_portal_jm_data_users','');
        //update_option('job_portal_jm_data_companies','');
        //update_option('job_portal_jm_data_jobs','');
        //update_option('job_portal_jm_data_resumes','');


        // import users to wp job portal table
        $this->importUsers();
        $this->importJobFields();
        $this->importCompanies(); // logo missing

        $this->importCategories();
        $this->importJobTypes();
        if(in_array('tag', wpjobportal::$_active_addons)){
            $this->importTags();
        }

        $this->importJobs();
        $this->importResume();
        $this->importJobApplied();
        // echo '<pre>';print_r($this->job_manager_job_custom_fields);
        // echo '<pre>';print_r($this->job_manager_import_count);
        // die('here in job manager import 1817');
        update_option('wpjob_portal_import_counts',$this->job_manager_import_count);
        return;
    }

    function setUserRoleByContent($wpjobportal_user_id) {
        if (empty($wpjobportal_user_id)) {
            return null;
        }

        // post types per user role
        $wpjobportal_employer_types = array('job_listing', 'company');
        $wpjobportal_job_seeker_types = array('resume', 'job_application');

        // number f records
        $wpjobportal_counts = array( 'employer' => 0,'job_seeker' => 0);

        // dates for latest posts
        $latest_post_dates = array('employer' => null,'job_seeker' => null);

        // Check all wp job manafer post types
        $full_types_array = array_merge($wpjobportal_employer_types, $wpjobportal_job_seeker_types);
        foreach ( $full_types_array as $post_type) {
            // fetch posts by type
            $posts = get_posts([
                'post_type'      => $post_type,
                'author'         => $wpjobportal_user_id,
                'posts_per_page' => -1, // all will be fetched
                'post_status'    => ['publish', 'pending', 'draft'],
                'orderby'        => 'date',
                'order'          => 'DESC',
            ]);

            if (!empty($posts)) {
                $wpjobportal_is_employer = in_array($post_type, $wpjobportal_employer_types); // if current post is from employer array
                $wpjobportal_key = $wpjobportal_is_employer ? 'employer' : 'job_seeker'; // current post type employer or job seeker
                $wpjobportal_counts[$wpjobportal_key] += count($posts); // total posts by current type

                // latest post date record
                $latest_date = get_the_date('U', $posts[0]);
                if (empty($latest_post_dates[$wpjobportal_key]) || $latest_date > $latest_post_dates[$wpjobportal_key]) {
                    $latest_post_dates[$wpjobportal_key] = $latest_date;
                }
            }
        }

        // Determine role based on counts and date
        if ($wpjobportal_counts['employer'] > 0 && $wpjobportal_counts['job_seeker'] === 0) { // if employer count > zero but job seeker count xero
            return 'employer';
        } elseif ($wpjobportal_counts['job_seeker'] > 0 && $wpjobportal_counts['employer'] === 0) {// if job seeker count > zero but employer count xero
            return 'job_seeker';
        } elseif ($wpjobportal_counts['employer'] > 0 && $wpjobportal_counts['job_seeker'] > 0) { ////  if both counts more then zero
            // Mixed usage: compare counts or latest activity
            if ($wpjobportal_counts['employer'] > ($wpjobportal_counts['job_seeker'] * 2)) { //  emplopyer entity count is more then 2 times
                return 'employer';
            } elseif ($wpjobportal_counts['job_seeker'] > ($wpjobportal_counts['employer'] * 2)) { //  emplopyer entity count is more then 2 times
                return 'job_seeker';
            } else { // Compare by latest post dates assign role based on latest post
                if ($latest_post_dates['employer'] > $latest_post_dates['job_seeker']) {
                    return 'employer';
                } else {
                    return 'job_seeker';
                }
            }
        }

        return null; // No entities found
    }

    function importUsers(){
        $wpjobportal_users = get_users();

        // check if user already processed for import
        $wpjobportal_imported_users = array();
        $wpjobportal_imported_users_json = get_option('job_portal_jm_data_users');
        if(!empty($wpjobportal_imported_users_json)){
            $wpjobportal_imported_users = json_decode($wpjobportal_imported_users_json,true);
        }

        foreach ($wpjobportal_users as $wpjobportal_user) {
            // check already imported
            if(!empty( $wpjobportal_imported_users ) && in_array($wpjobportal_user->ID, $wpjobportal_imported_users) ){ // if user id already in array skip it
                $this->job_manager_import_count['user']['skipped'] += 1;
                continue;
            }
            // check if user is already in system (uid dupicate check)
            $wpjobportal_user_object = WPJOBPORTALincluder::getJSModel('user')->getUserIDByWPUid($wpjobportal_user->ID);
            if(!empty($wpjobportal_user_object)){ // not empty means it will contain id for corresponding uid
                continue;
            }

            $wpjobportal_data = array();
            $wpjobportal_data['uid'] = $wpjobportal_user->ID;
            $wpjobportal_data['emailaddress'] = $wpjobportal_user->user_email;

            // user role
            $wpjobportal_role = $wpjobportal_user->roles;
            if($wpjobportal_role == 'company'){
                $wpjobportal_data['roleid'] = 1;
            }elseif($wpjobportal_role == 'employer'){
                $wpjobportal_data['roleid'] = 1;
            }elseif($wpjobportal_role == 'candidate'){
                $wpjobportal_data['roleid'] = 2;
            }else{ // if any other role
                // handling the case of no role or wordpress dedfault role
                $wpjobportal_role_string = $this->setUserRoleByContent($wpjobportal_user->ID);
                if($wpjobportal_role_string == 'employer'){
                    $wpjobportal_data['roleid'] = 1;
                }elseif($wpjobportal_role_string == 'job_seeker'){
                    $wpjobportal_data['roleid'] = 2;
                }elseif($wpjobportal_role_string == 'job_seeker'){
                    $wpjobportal_data['roleid'] = 5;
                }
            }

            $wpjobportal_data['first_name'] = get_user_meta( $wpjobportal_user->ID, 'first_name', true );
            $wpjobportal_data['last_name']  = get_user_meta( $wpjobportal_user->ID, 'last_name', true );
            $wpjobportal_data['status'] = 1;
            $wpjobportal_data['created'] = $wpjobportal_user->user_registered;
            $wpjobportal_data = wpjobportal::wpjobportal_sanitizeData($wpjobportal_data);
            $wpjobportal_row = WPJOBPORTALincluder::getJSTable('users');

            if(!($wpjobportal_row->bind($wpjobportal_data) && $wpjobportal_row->check() && $wpjobportal_row->store())){
                $this->job_manager_import_count['user']['failed'] += 1;
                continue; // move on to next post if store fialed
            }else{
                // if no error then
                $this->job_manager_users_array[$wpjobportal_user->ID] = $wpjobportal_row->id; // create an array of uid and ids to use in record insertion
                $this->job_manager_user_ids[] = $wpjobportal_user->ID; // create an array of user ids to store
                $this->job_manager_import_count['user']['imported'] += 1;
            }
        }
        if(!empty($this->job_manager_user_ids)){
            update_option('job_portal_jm_data_users', wp_json_encode($this->job_manager_user_ids) );
        }
    }


    function importCompanies(){
        $wpjobportal_args = array('post_type' => 'company_listings');
        $wpjobportal_companies = get_posts($wpjobportal_args);

        // check if company already processed for import
        $wpjobportal_imported_companies = array();
        $wpjobportal_imported_companies_json = get_option('job_portal_jm_data_companies');
        if(!empty($wpjobportal_imported_companies_json)){
            $wpjobportal_imported_companies = json_decode($wpjobportal_imported_companies_json,true);
        }

        foreach($wpjobportal_companies as $wpjobportal_company){
            // check already imported
            if(!empty( $wpjobportal_imported_companies ) && in_array($wpjobportal_company->ID, $wpjobportal_imported_companies) ){ // if id already in array skip it
                $this->job_manager_import_count['company']['skipped'] += 1;
                continue;
            }
            //echo "<br>".$wpjobportal_company->ID;
            $wpjobportal_args2 = array('post_parent' => $wpjobportal_company->ID);
            $wpjobportal_companies_details = get_posts($wpjobportal_args2);

            $wpjobportal_logo = '';
            $wpjobportal_logoisfile = '';
            $wpjobportal_logo_url = get_the_post_thumbnail_url( $wpjobportal_company->ID, 'full' );
            if($wpjobportal_logo_url != ''){
                $wpjobportal_logo = basename($wpjobportal_logo_url);
                $wpjobportal_logoisfile = 1;
            }

            $post_meta = get_post_meta($wpjobportal_company->ID);
            // echo "<br>";
            // print_r($wpjobportal_company);
            // print_r($post_meta);
            //exit;
            $featured = 0;
            if($post_meta["_company_email"][0]) $wpjobportal_email = $post_meta["_company_email"][0]; else $wpjobportal_email = "";
            if($post_meta["_company_location"][0]) $wpjobportal_address = $post_meta["_company_location"][0]; else $wpjobportal_address = "";
            if($post_meta["_company_website"][0]) $website = $post_meta["_company_website"][0]; else $website = "";
            if($post_meta["_company_twitter"][0]) $twitter = $post_meta["_company_twitter"][0]; else $twitter = "";
            if(!empty($post_meta["_company_tagline"][0])) $wpjobportal_tagline = $post_meta["_company_tagline"][0]; else $wpjobportal_tagline = "";

            $wpjobportal_end_featured_date = '';
            if(in_array('featuredcompany', wpjobportal::$_active_addons)){
                if(isset($post_meta["_featured"][0])) $featured = $post_meta["_featured"][0];
                if($featured == 1){
                    $wpjobportal_end_featured_date = gmdate('Y-m-d H:i:s',strtotime(" +30 days"));
                }
            }

            $alias = wpjobportal::$_common->stringToAlias($wpjobportal_company->post_title);

            $wpjobportal_uid = $this->getUserIDFromAuthorID($wpjobportal_company->post_author);

            $wpjobportal_comapnyparams = $this->getParamsForCustomFields($this->job_manager_company_custom_fields,$post_meta);

            $wpjobportal_data = [
                "id" => "",
                "uid" => $wpjobportal_uid,
                "name" => $wpjobportal_company->post_title,
                "alias" => $alias,
                "url" => $website,
                "logofilename" => $wpjobportal_logo,
                "logoisfile" => $wpjobportal_logoisfile,
                "smalllogofilename" => "",
                "smalllogoisfile" => "",
                "smalllogo" => "",
                "contactemail" => $wpjobportal_email,
                "description" => $wpjobportal_company->post_content,
                "city" => "",
                "address1" => $wpjobportal_address,
                "address2" => "",
                "created" => $wpjobportal_company->post_date,
                "price" => "",
                "modified" => $wpjobportal_company->post_modified,
                "hits" => "",
                "tagline" => $wpjobportal_tagline,
                "status" => "1",
                "isfeaturedcompany" => $featured,
                "startfeatureddate" => "",
                "endfeatureddate" => $wpjobportal_end_featured_date,
                "serverstatus" => "",
                "userpackageid" => "",
                "serverid" => "",
                "params" => $wpjobportal_comapnyparams,
                "twiter_link" => $twitter,
                "linkedin_link" => "",
                "youtube_link" => "",
                "facebook_link" => ""
            ];
            $wpjobportal_row = WPJOBPORTALincluder::getJSTable('company');
            //print_r($wpjobportal_data);

            if(!($wpjobportal_row->bind($wpjobportal_data) && $wpjobportal_row->check() && $wpjobportal_row->store())){
                $this->job_manager_import_count['company']['failed'] += 1;
                continue; // move on to next post if store fialed
            }else{
                // if no error then
                $this->job_manager_company_ids[] = $wpjobportal_company->ID; // create an array of company ids to store
                $this->job_manager_import_count['company']['imported'] += 1;
            }
            // handle logo file upload to wp job portal uploads
            //$wpjobportal_logo_url
            $this->handleUploadFile(1, $wpjobportal_row->id, $wpjobportal_logo_url);
        }
        //print_r($wpjobportal_companies);
        if(!empty($this->job_manager_company_ids)){
            update_option('job_portal_jm_data_companies', wp_json_encode($this->job_manager_company_ids) );
        }
    }

    function ensureFilePathValid($full_path, $wpjobportal_datadirectory) {

        if($full_path == '' || $wpjobportal_datadirectory == ''){
            return;
        }
        global $wp_filesystem;
        if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ) {
            $creds = request_filesystem_credentials( site_url() );
            wp_filesystem( $creds );
        }

        // emove trailing slashes
        $full_path = untrailingslashit($full_path);
        $wpjobportal_datadirectory = untrailingslashit($wpjobportal_datadirectory);

        // Only proceed if path includes datadirectory makeing sure no other path gets proccessed
        $pos = strpos($full_path, $wpjobportal_datadirectory);
        if ($pos === false) return;

        // Get segments from datadirectory onward
        $relative = substr($full_path, $pos);
        $base = substr($full_path, 0, $pos);
        $current = untrailingslashit($base);

        $relative_paths_segments = explode('/', $relative);

        foreach ($relative_paths_segments as $wpjobportal_segment) {
            $current .= '/' . $wpjobportal_segment;
            if ( ! $wp_filesystem->is_dir($current) ) {
                $wp_filesystem->mkdir($current, FS_CHMOD_DIR, true);
            }
            $wpjobportal_index = $current . '/index.html';
            if ( ! $wp_filesystem->exists($wpjobportal_index) ) {
                $wp_filesystem->put_contents($wpjobportal_index, '', FS_CHMOD_FILE);
            }
        }
    }


    function handleUploadFile($wpjobportal_uploadfor, $wpjobportal_enitity_id, $wpjobportal_upload_file){
        // basic validation
        if(is_numeric($wpjobportal_uploadfor) && is_numeric($wpjobportal_enitity_id) && $wpjobportal_upload_file !=''){
            $wpjobportal_datadirectory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
            $wpjobportal_upload_dir = wp_upload_dir();
            $base_path = $wpjobportal_upload_dir['basedir'];
            $wpjobportal_path = $wpjobportal_datadirectory . '/data';

            if($wpjobportal_uploadfor == 1){ // company logo
                $folder_path = $wpjobportal_upload_dir['basedir'].'/'.$wpjobportal_path."/employer/comp_".$wpjobportal_enitity_id."/logo";
            }elseif($wpjobportal_uploadfor == 3){ // resume photo
                $folder_path = $wpjobportal_upload_dir['basedir'].'/'.$wpjobportal_path."/jobseeker/resume_".$wpjobportal_enitity_id."/photo";
            }elseif($wpjobportal_uploadfor == 4){ // resume files
                $folder_path = $wpjobportal_upload_dir['basedir'].'/'.$wpjobportal_path."/jobseeker/resume_".$wpjobportal_enitity_id."/resume";
            }

            // set up direcotiers and index files
            $this->ensureFilePathValid($folder_path,$wpjobportal_datadirectory);

            // Move uploaded file
            require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
            require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
            $filesystem = new WP_Filesystem_Direct( true );

            // remote path to relative path conversion
            $file_path = str_replace( $wpjobportal_upload_dir['baseurl'], $wpjobportal_upload_dir['basedir'], $wpjobportal_upload_file );
            $file_name = basename($wpjobportal_upload_file);

            $source = $file_path;
            $wpjobportal_destination = $folder_path . "/". $file_name;

            // echo "<br>s: ".$source;
            // echo "<br>d: ".$wpjobportal_destination;

            // Make sure file exists before moving
            if ( file_exists( $source ) ) {
                $wpjobportal_result = $filesystem->copy($source, $wpjobportal_destination, true);
             //   var_dump($wpjobportal_result);
            }
            //  else {
            //     echo "<br><strong>Source file not found:</strong> $source";
            // }
        }
    }



    function getParamsForCustomFields($wpjobportal_customfields,$post_meta){
        $params = array();
        foreach($wpjobportal_customfields as $custom_field){
            $meta_key = '_' . $custom_field['name'];
            if (!isset( $post_meta[ $meta_key ])) { // of meta for current field not set ignore it
                continue;
            }

            $custom_field_value = $post_meta[ $meta_key ][0] ?? '';
            if($custom_field_value == ''){ // if no value ignore current field
                continue;
            }
            $wpjobportal_vardata = "";
            switch ( $custom_field['type'] ) { // to handle different type of fields seprately
                case 'date':
                    $wpjobportal_vardata = gmdate("Y-m-d", wpjobportalphplib::wpJP_strtotime($custom_field_value));
                break;
                case 'checkbox':
                case 'combo':
                case 'file':
                    $wpjobportal_vardata = maybe_unserialize($custom_field_value);
                    //$wpjobportal_vardata = unserialize($custom_field_value);
                break;
                default:
                    $wpjobportal_vardata = $custom_field_value;
                break;
            }
            if($wpjobportal_vardata != ''){ //  only add value to params if its not empty
                if(is_array($wpjobportal_vardata)){
                    $wpjobportal_vardata = implode(', ', array_filter($wpjobportal_vardata));
                }
                $params[$custom_field["jp_filedorderingfield"]] = wpjobportalphplib::wpJP_htmlspecialchars($wpjobportal_vardata);
            }
        }
        // echo '<pre>';print_r($params);echo '</pre>';
        if(!empty($params)){
            return html_entity_decode(wp_json_encode($params, JSON_UNESCAPED_UNICODE));
        }else{
            return '';
        }
    }


    function importJobs(){

        // check if job already processed for import
        $wpjobportal_imported_jobs = array();
        $wpjobportal_imported_jobs_json = get_option('job_portal_jm_data_jobs');
        if(!empty($wpjobportal_imported_jobs_json)){
            $wpjobportal_imported_jobs = json_decode($wpjobportal_imported_jobs_json,true);
        }

        $wpjobportal_jobs = get_posts([
            'post_type'      => 'job_listing',
            'post_status'    => 'any', // will include all except 'auto-draft'
            'orderby'        => 'ID',
            'order'          => 'ASC',
            'numberposts'    => -1, // fetch all posts
            'exclude'        => wp_list_pluck(
                get_posts([
                    'post_type'   => 'job_listing',
                    'post_status'=> 'auto-draft',
                    'fields'     => 'ids',
                    'numberposts'=> -1
                ]),
                null
            ),
        ]);

        // print_r($wpjobportal_jobs);

        foreach($wpjobportal_jobs as $wpjobportal_job){
            // check already imported
            if(!empty( $wpjobportal_imported_jobs ) && in_array($wpjobportal_job->ID, $wpjobportal_imported_jobs) ){ // if id already in array skip it
                $this->job_manager_import_count['job']['skipped'] += 1;
                continue;
            }
            $post_meta = get_post_meta($wpjobportal_job->ID);
            // print_r($wpjobportal_job);
            // print_r($post_meta);

            if(isset($post_meta["_company_id"][0])){ // company is already added (company is a seprate entitity)
                $wpjobportal_companyid = $this->getCompanyIdByJobManagerId($post_meta["_company_id"][0]);
            }else{ // add company (compnay data is in job data) (no company addon)
                $wpjobportal_companyid = $this->createJPCompany($wpjobportal_job);
            }

            $wpjobportal_stoppublishing = "";
            if(isset($post_meta["_job_expires"][0])){
                $wpjobportal_stoppublishing = $post_meta["_job_expires"][0];
            }
            if(!$wpjobportal_stoppublishing){
                $wpjobportal_stoppublishing = gmdate('Y-m-d H:i:s',strtotime(" +30 days"));
            }
            //echo "<br>ex: ".$wpjobportal_stoppublishing;
            $featured = $hits = $wpjobportal_job_salary = 0;
            $wpjobportal_salary_currency = $wpjobportal_address = "";
            if(isset($post_meta["_wpjms_visits_total"][0])) $hits = $post_meta["_wpjms_visits_total"][0];

            // salary for the job
            $wpjobportal_salary_array = $this->parseJobManagerSalaryData($wpjobportal_job->ID);

            // Extract and assign values
            $wpjobportal_job_salary_currency = isset($wpjobportal_salary_array['currency']) ? $wpjobportal_salary_array['currency'] : '';
            $wpjobportal_job_salary_duration_type = isset($wpjobportal_salary_array['type']) ? $wpjobportal_salary_array['type'] : '';
            $wpjobportal_job_salary_min = isset($wpjobportal_salary_array['min']) ? floatval($wpjobportal_salary_array['min']) : null;
            $wpjobportal_job_salary_max = isset($wpjobportal_salary_array['max']) ? floatval($wpjobportal_salary_array['max']) : null;
            $wpjobportal_salaryfixed = '';

            $wpjobportal_job_salary_duration = $this->getSalaryDuration($wpjobportal_job_salary_duration_type);

            // Determine range type
            if (!is_null($wpjobportal_job_salary_min) && !is_null($wpjobportal_job_salary_max)) {
                $wpjobportal_salaryrangetype = 3; // min and max
            } elseif (!is_null($wpjobportal_job_salary_min) && is_null($wpjobportal_job_salary_max)) {
                $wpjobportal_salaryrangetype = 2; // only one of min (to hanlde fixed salary case)
                $wpjobportal_salaryfixed = $wpjobportal_job_salary_min; // set fixed salary variable
                $wpjobportal_job_salary_min = null; // un set min varioables
            } else {
                $wpjobportal_salaryrangetype = 1; // neither set
            }


            if(isset($post_meta["_job_location"][0])){
                $city = $post_meta["_job_location"][0];
                $city_arr = explode(",",$city);
                if(count($city_arr) > 1){
                    $cityid = $this->getCityId($city);
                    //if(!$cityid) $wpjobportal_address = $city; // if no city case
                }else{
                    //$wpjobportal_address = $city; // if no city case
                }
            }

            // custom fields handing
            $wpjobportal_jobparams = $this->getParamsForCustomFields($this->job_manager_job_custom_fields,$post_meta);

            // tags are still being fetched by query
            // possible issue

            $wpjobportal_job_manager_tags = array();
            if(in_array('tag', wpjobportal::$_active_addons)){
                $query = "SELECT taxonomy.*, relationships.*, terms.*
                            FROM `" . wpjobportal::$_db->prefix . "term_taxonomy` AS taxonomy
                            JOIN `" . wpjobportal::$_db->prefix . "term_relationships` AS relationships ON relationships.term_taxonomy_id = taxonomy.term_taxonomy_id
                            JOIN `" . wpjobportal::$_db->prefix . "terms` AS terms ON terms.term_id = taxonomy.term_id
                            WHERE relationships.object_id = ".$wpjobportal_job->ID.";";
                $wpjobportal_taxonomy = wpjobportal::$_db->get_results($query);
                foreach($wpjobportal_taxonomy as $wpjobportal_tax){
                    if($wpjobportal_tax->taxonomy == "job_listing_tag"){
                        $wpjobportal_job_manager_tags[] = $wpjobportal_tax->name;
                    }
                }
            }
            $wpjobportal_job_manager_job_type = "";
            $wpjobportal_job_manager_categories = array();

            $wpjobportal_categories = get_the_terms( $wpjobportal_job->ID, 'job_listing_category' );

            if ( ! is_wp_error( $wpjobportal_categories ) && ! empty( $wpjobportal_categories ) ) {
                foreach ( $wpjobportal_categories as $wpjobportal_category ) {
                    $wpjobportal_job_manager_categories[] = $wpjobportal_category->name;
                }
            }

            $wpjobportal_jobtypes = get_the_terms( $wpjobportal_job->ID, 'job_listing_type' );
            if ( ! is_wp_error( $wpjobportal_jobtypes ) && ! empty( $wpjobportal_jobtypes ) ) {
                foreach ( $wpjobportal_jobtypes as $wpjobportal_jobtype ) {
                    $wpjobportal_job_manager_job_type = $wpjobportal_jobtype->name;
                }
            }

            $wpjobportal_jobtype = "";
            if($wpjobportal_job_manager_job_type){
                $wpjobportal_jobtype = $this->getJobTypeByTitle($wpjobportal_job_manager_job_type);
            }

            $wpjobportal_jobcategory = "";
            if(isset($wpjobportal_job_manager_categories[0])){
                $wpjobportal_jobcategory = $this->getJobCategoriesByTitle($wpjobportal_job_manager_categories[0]);
            }
            //echo var_dump($wpjobportal_jobcategory);

            $wpjobportal_tags = "";
            if(!empty($wpjobportal_job_manager_tags)){
                $wpjobportal_tags = $this->getJobTagsByTitle($wpjobportal_job_manager_tags);
            }

            $alias = wpjobportal::$_common->stringToAlias($wpjobportal_job->post_title);

            $wpjobportal_jobid = WPJOBPORTALincluder::getJSModel('job')->getJobId();
            if(!$wpjobportal_stoppublishing){
                $wpjobportal_expiry = "2 years";
                $wpjobportal_curdate = date_i18n('Y-m-d');
                $wpjobportal_stoppublishing = gmdate('Y-m-d H:i:s',strtotime($wpjobportal_curdate.'+'.$wpjobportal_expiry));
            }

            $wpjobportal_uid = $this->getUserIDFromAuthorID($wpjobportal_job->post_author);

            $wpjobportal_end_featured_date = '';
            if(in_array('featuredjob', wpjobportal::$_active_addons)){
                if(isset($post_meta["_featured"][0])) $featured = $post_meta["_featured"][0];
                if($featured == 1){
                    $wpjobportal_end_featured_date = gmdate('Y-m-d H:i:s',strtotime(" +30 days"));
                }
            }

            $wpjobportal_data = [
                "id" => '',
                "uid" => $wpjobportal_uid,
                "companyid" => $wpjobportal_companyid,
                "title" => $wpjobportal_job->post_title,
                "alias" => $alias,
                "jobcategory" => $wpjobportal_jobcategory,
                "jobtype" => $wpjobportal_jobtype,
                "jobstatus" => '1',
                "hidesalaryrange" => '',
                "description" => $wpjobportal_job->post_content,
                "qualifications" => '',
                "prefferdskills" => '',
                "applyinfo" => '',
                "company" => '',
                "city" => '',
                "address1" => '',
                "address2" => '',
                "companyurl" => '',
                "contactname" => '',
                "contactphone" => '',
                "contactemail" => '',
                "showcontact" => '',
                "noofjobs" => '1',
                "reference" => '',
                "duration" => '',
                "heighestfinisheducation" => '',
                "created" => $wpjobportal_job->post_date,
                "created_by" => $wpjobportal_job->post_author,
                "modified" => $wpjobportal_job->post_modified,
                "modified_by" => '',
                "hits" => $hits,
                "experience" => '',
                "startpublishing" => $wpjobportal_job->post_date,
                "stoppublishing" => $wpjobportal_stoppublishing,
                "departmentid" => '',
                "sendemail" => '',
                "metadescription" => '',
                "metakeywords" => '',
                "ordering" => '',
                "aboutjobfile" => '',
                "status" => '1',
                "degreetitle" => '',
                "careerlevel" => '',
                "educationid" => '',
                "map" => '',
                "salarytype" => $wpjobportal_salaryrangetype,
                "salaryfixed" => $wpjobportal_salaryfixed,
                "salarymin" => $wpjobportal_job_salary_min,
                "salarymax" => $wpjobportal_job_salary_max,
                "salaryduration" => $wpjobportal_job_salary_duration,
                "subcategoryid" => '',
                "currency" => $wpjobportal_job_salary_currency,
                "jobid" => $wpjobportal_jobid,
                "longitude" => '',
                "latitude" => '',
                "raf_degreelevel" => '',
                "raf_education" => '',
                "raf_category" => '',
                "raf_subcategory" => '',
                "raf_location" => '',
                "isfeaturedjob" => $featured,
                "serverstatus" => '',
                "serverid" => '',
                "joblink" => '',
                "jobapplylink" => '',
                "tags" => $wpjobportal_tags,
                "params" => $wpjobportal_jobparams,
                "userpackageid" => '',
                "price" => '',
                // log error
                "startfeatureddate" => '',
                "endfeatureddate" => $wpjobportal_end_featured_date,
            ];
            $wpjobportal_row = WPJOBPORTALincluder::getJSTable('job');

            if(!($wpjobportal_row->bind($wpjobportal_data) && $wpjobportal_row->check() && $wpjobportal_row->store())){
                $this->job_manager_import_count['job']['failed'] += 1;
                continue; // move on to next post if store fialed
            }else{
                // if no error then
                $this->job_manager_job_ids[] = $wpjobportal_job->ID; // create an array of job ids to store
                $this->job_manager_import_count['job']['imported'] += 1;
            }

            $wpjobportal_jobid = $wpjobportal_row->id;

            if($cityid){
                $wpjobportal_data = [
                    "id" => '',
                    "jobid" => $wpjobportal_jobid,
                    "cityid" => $cityid
                ];
                $wpjobportal_row = WPJOBPORTALincluder::getJSTable('jobcities');
                if (!$wpjobportal_row->bind($wpjobportal_data)) {
                    //$error[] = wpjobportal::$_db->last_error;
                }
                if (!$wpjobportal_row->store()) {
                    //$error[] = wpjobportal::$_db->last_error;
                    //echo "<br> error job city store----------";
                }

            }
        }
        //print_r($wpjobportal_jobs);
        if(!empty($this->job_manager_job_ids)){
            update_option('job_portal_jm_data_jobs', wp_json_encode($this->job_manager_job_ids) );
        }
    }

    function getUserIDFromAuthorID($wordpres_uid){
        if(!is_numeric($wordpres_uid)){
            return 0;
        }
        if(isset($this->job_manager_users_array[$wordpres_uid])){
            $wpjobportal_uid = $this->job_manager_users_array[$wordpres_uid];
        }else{
            $wpjobportal_uid = WPJOBPORTALincluder::getJSModel('user')->getUserIDByWPUid($wordpres_uid);
        }

        // to handle edge (error) case
        if(!is_numeric($wpjobportal_uid)){
            $wpjobportal_uid = 0;
        }
        return $wpjobportal_uid;
    }

    function createJPCompany($wpjobportal_company){
        $wpjobportal_uid = $this->getUserIDFromAuthorID($wpjobportal_company->post_author);
        $post_meta = get_post_meta($wpjobportal_company->ID);
        if(isset($post_meta["_company_name"][0])){
            $wpjobportal_name = $post_meta["_company_name"][0];
            $query = "SELECT company.id
                        FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company
                        WHERE LOWER(company.name) = '".strtolower(esc_sql($wpjobportal_name))."'
                        AND company.uid = ".$wpjobportal_uid.";";
                        //echo "<br>".$query;
            $jpcompany = wpjobportaldb::get_row($query);
            if(!empty($jpcompany)){
                return $jpcompany->id;
            }else{
                if(!empty($post_meta["_company_website"][0])) $website = $post_meta["_company_website"][0]; else $website = "";
                if(!empty($post_meta["_company_twitter"][0])) $twitter = $post_meta["_company_twitter"][0]; else $twitter = "";
                if(!empty($post_meta["_company_tagline"][0])) $wpjobportal_tagline = $post_meta["_company_tagline"][0]; else $wpjobportal_tagline = "";

                $alias = wpjobportal::$_common->stringToAlias($wpjobportal_name);

                $wpjobportal_companyparams = $this->getParamsForCustomFields($this->job_manager_company_custom_fields,$post_meta);

                $wpjobportal_data = [
                    "id" => "",
                    "uid" => $wpjobportal_uid,
                    "name" => $wpjobportal_name,
                    "alias" => $alias,
                    "url" => $website,
                    "logofilename" => "",
                    "logoisfile" => "",
                    "logo" => "",
                    "smalllogofilename" => "",
                    "smalllogoisfile" => "",
                    "smalllogo" => "",
                    "contactemail" => "",
                    "description" => "",
                    "city" => "",
                    "address1" => "",
                    "address2" => "",
                    "created" => $wpjobportal_company->post_date,
                    "price" => "",
                    "modified" => $wpjobportal_company->post_modified,
                    "hits" => "",
                    "tagline" => $wpjobportal_tagline,
                    "status" => "1",
                    "isfeaturedcompany" => "",
                    "startfeatureddate" => "",
                    "endfeatureddate" => "",
                    "serverstatus" => "",
                    "userpackageid" => "",
                    "serverid" => "",
                    "params" => "",
                    "twiter_link" => $twitter,
                    "linkedin_link" => "",
                    "youtube_link" => "",
                    "params" => $wpjobportal_companyparams,
                    "facebook_link" => ""
                ];
                $wpjobportal_row = WPJOBPORTALincluder::getJSTable('company');
                // print_r($wpjobportal_data);

                if(!($wpjobportal_row->bind($wpjobportal_data) && $wpjobportal_row->check() && $wpjobportal_row->store())){
                    $this->job_manager_import_count['company']['failed'] += 1;
                }else{
                    // if no error then
                    $this->job_manager_company_ids[] = $wpjobportal_company->ID; // create an array of company ids to store
                    $this->job_manager_import_count['company']['imported'] += 1;
                }
            }
        }
    }

    function getCompanyIdByJobManagerId($post_id){
        if(!is_numeric($post_id)){
            return;
        }
        $query = "SELECT post.*
                    FROM `" . wpjobportal::$_db->prefix . "posts` AS post
                    WHERE post.post_type = 'company_listings'
                    AND post.id = ".$post_id;
                    //echo "<br>".$query;


        $jmcompany = wpjobportaldb::get_row($query);
        if(!empty($jmcompany)){
            $wpjobportal_uid = $this->getUserIDFromAuthorID($jmcompany->post_author);
            $query = "SELECT company.id
                        FROM `" . wpjobportal::$_db->prefix . "wj_portal_companies` AS company
                        WHERE LOWER(company.name) = '".strtolower(esc_sql($jmcompany->post_title))."'
                        AND company.uid = ".$wpjobportal_uid;
                        //echo "<br>".$query;
            $jpcompany = wpjobportaldb::get_row($query);
            if($jpcompany)
                return $jpcompany->id;
        }
        return;
    }

    function importResume(){

        // check if resume already processed for import
        $wpjobportal_imported_resumes = array();
        $wpjobportal_imported_resumes_json = get_option('job_portal_jm_data_resumes');
        if(!empty($wpjobportal_imported_resumes_json)){
            $wpjobportal_imported_resumes = json_decode($wpjobportal_imported_resumes_json,true);
        }

        $wpjobportal_resumes = get_posts( array(
                'post_type'      => 'resume',
                'post_status'    => array_diff( get_post_stati(), array( 'auto-draft' ) ),
                'numberposts'    => -1, // get all
                'orderby'        => 'ID',
                'order'          => 'ASC',
            ) );
        foreach($wpjobportal_resumes as $wpjobportal_resume){
            // check already imported
            if(!empty( $wpjobportal_imported_resumes ) && in_array($wpjobportal_resume->ID, $wpjobportal_imported_resumes) ){ // if id already in array skip it
                $this->job_manager_import_count['resume']['skipped'] += 1;
                continue;
            }

            $post_meta = get_post_meta($wpjobportal_resume->ID);

            $featured = 0;
            if($post_meta["_candidate_title"][0]) $candidate_title = $post_meta["_candidate_title"][0]; else $candidate_title = "";
            if(!empty($post_meta["_candidate_photo"][0])) $candidate_photo_url = $post_meta["_candidate_photo"][0]; else $candidate_photo_url = "";
            if($post_meta["_candidate_email"][0]) $candidate_email = $post_meta["_candidate_email"][0]; else $candidate_email = "";
            if($post_meta["_candidate_location"][0]) $candidate_location = $post_meta["_candidate_location"][0]; else $candidate_location = "";

            if(in_array('featureresume', wpjobportal::$_active_addons)){
                if(isset($post_meta["_featured"][0])) $featured = $post_meta["_featured"][0];
                $wpjobportal_end_featured_date = '';
                if($featured == 1){
                    $wpjobportal_end_featured_date = gmdate('Y-m-d H:i:s',strtotime(" +30 days"));
                }
            }

            $candidate_photo = "";
            if($candidate_photo_url != ''){
                $candidate_photo = basename($candidate_photo_url);
            }

            // if name not set use post title // first_name system fielf (required)
            if(!empty($post_meta["_candidate_name"][0])) $candidate_name = $post_meta["_candidate_name"][0]; else $candidate_name = $wpjobportal_resume->post_title;

            // skills field
            if(!empty($post_meta["_resume_skills"][0])) $candidate_skill = $post_meta["_resume_skills"][0]; else $candidate_skill = $wpjobportal_resume->post_title;

            // job_cateogry column resume
            $wpjobportal_categories = get_the_terms( $wpjobportal_resume->ID, 'resume_category' );

            if ( ! is_wp_error( $wpjobportal_categories ) && ! empty( $wpjobportal_categories ) ) {
                foreach ( $wpjobportal_categories as $wpjobportal_category ) {
                    $wpjobportal_job_manager_categories[] = $wpjobportal_category->name;
                }
            }
            $wpjobportal_resumecategory = "";
            if(isset($wpjobportal_job_manager_categories[0])){
                $wpjobportal_resumecategory = $this->getJobCategoriesByTitle($wpjobportal_job_manager_categories[0]);
            }

            $wpjobportal_resumeparams = $this->getParamsForCustomFields($this->job_manager_resume_custom_fields,$post_meta);

            $wpjobportal_uid = $this->getUserIDFromAuthorID($wpjobportal_resume->post_author);

            if($candidate_title != ''){ // use possible application title value for alias
                $alias = wpjobportal::$_common->stringToAlias($candidate_title);
            }else{
                $alias = wpjobportal::$_common->stringToAlias($wpjobportal_resume->post_title);
            }

            if(!empty($post_meta["_resume_file"][0])) $wpjobportal_resume_file = $post_meta["_resume_file"][0]; else $wpjobportal_resume_file = "";

            // unused
            //$wpjobportal_resume->post_content

            $wpjobportal_data = [
                "id" => "",
                "uid" => $wpjobportal_uid,
                "application_title" => $candidate_title,
                "alias" => $alias,
                "first_name" => $candidate_name,
                "last_name" => "",
                "email_address" => $candidate_email,
                "searchable" => "1",
                "photo" => $candidate_photo,
                "status" => "1",
                "resume" => "",
                "skills" => $candidate_skill,
                "isfeaturedresume" => $featured,
                "created" => $wpjobportal_resume->post_date,
                "last_modified" => $wpjobportal_resume->post_modified,
                "published" => "1",
                "job_category" => $wpjobportal_resumecategory,
                "params" => $wpjobportal_resumeparams
            ];
            $wpjobportal_row = WPJOBPORTALincluder::getJSTable('resume');
            // print_r($wpjobportal_data);
            if(!($wpjobportal_row->bind($wpjobportal_data) && $wpjobportal_row->check() && $wpjobportal_row->store())){
                $this->job_manager_import_count['resume']['failed'] += 1;
                continue; // move on to next post if store fialed
            }else{
                // if no error then
                $this->job_manager_resume_ids[] = $wpjobportal_resume->ID; // create an array of resume ids to store
                $this->job_manager_import_count['resume']['imported'] += 1;
            }
            $wpjobportal_resumeid = $wpjobportal_row->id;
            if($candidate_photo_url != ''){ // if photo exisits
                $this->handleUploadFile(3, $wpjobportal_resumeid, $candidate_photo_url); // move the file to wpjobportal uploads
            }
            if(in_array('advanceresumebuilder', wpjobportal::$_active_addons)){
                //resume file
                if($wpjobportal_resume_file !=''){
                    $wpjobportal_resume_file_size = '';
                    $wpjobportal_resume_file_type = '';
                    $filename = basename($wpjobportal_resume_file);
                    $wpjobportal_upload_dir = wp_get_upload_dir();
                    $file_path = str_replace( $wpjobportal_upload_dir['baseurl'], $wpjobportal_upload_dir['basedir'], $wpjobportal_resume_file );
                    if ( file_exists( $file_path ) ) {
                        $file_type_array = wp_check_filetype( $file_path );
                        $wpjobportal_resume_file_type = $file_type_array['type'];
                        $wpjobportal_resume_file_size = filesize( $file_path );
                    }
                    // store resume file record
                    $wpjobportal_row = WPJOBPORTALincluder::getJSTable('resumefile');
                    $cols = array();
                    $cols['resumeid'] = $wpjobportal_resumeid;
                    $cols['filename'] = $filename;
                    $cols['filetype'] = $wpjobportal_resume_file_type;
                    $cols['filesize'] = $wpjobportal_resume_file_size;
                    $cols['created'] = $wpjobportal_resume->post_date;
                    $cols = wpjobportal::wpjobportal_sanitizeData($cols);

                    if($wpjobportal_row->bind($cols) && $wpjobportal_row->store()) { // if record inserted in table
                        $this->handleUploadFile(4, $wpjobportal_resumeid, $wpjobportal_resume_file); // move the file to wpjobportal uploads
                    }
                }

                // address section
                $wpjobportal_address = '';
                if($candidate_location){
                    $wpjobportal_row = WPJOBPORTALincluder::getJSTable('resumeaddresses');
                    $city_arr = explode(",",$candidate_location);
                    if(count($city_arr) > 1){
                        $cityid = $this->getCityId($candidate_location);
                        if(!$cityid) $wpjobportal_address = $candidate_location;
                    }else{
                        $wpjobportal_address = $candidate_location;
                    }
                    $wpjobportal_data = [
                        "id" => "",
                        "resumeid" => $wpjobportal_resumeid,
                        "address" => $wpjobportal_address,
                        "address_city" => $cityid,
                        "created" => $wpjobportal_resume->post_date
                    ];
                    if(!($wpjobportal_row->bind($wpjobportal_data) && $wpjobportal_row->check() && $wpjobportal_row->store())){
                        echo "<br> error --- ";
                        return false;
                    }

                }
                // education section
                if($post_meta["_candidate_education"][0]) {
                    $wpjobportal_educations = unserialize($post_meta["_candidate_education"][0]);
                    $wpjobportal_row = WPJOBPORTALincluder::getJSTable('resumeinstitutes');
                    foreach($wpjobportal_educations as $wpjobportal_education){
                        $wpjobportal_data = [
                            "id" => "",
                            "resumeid" => $wpjobportal_resumeid,
                            "institute" => $wpjobportal_education["location"],
                            "institute_certificate_name" => $wpjobportal_education["qualification"],
                            "institute_study_area" => $wpjobportal_education["date"]."\n".$wpjobportal_education["notes"],
                            "fromdate" => "",
                            "todate" => "",
                            "created" => $wpjobportal_resume->post_date,
                        ];

                        if(!($wpjobportal_row->bind($wpjobportal_data) && $wpjobportal_row->check() && $wpjobportal_row->store())){
                            echo "<br> error --- ";
                            return false;
                        }
                    }
                    //print_r($wpjobportal_educations);
                }
                // employer section
                if($post_meta["_candidate_experience"][0]) {
                    $wpjobportal_experiences = unserialize($post_meta["_candidate_experience"][0]);
                    $wpjobportal_row = WPJOBPORTALincluder::getJSTable('resumeemployers');
                    foreach($wpjobportal_experiences as $wpjobportal_experience){
                        $wpjobportal_data = [
                            "id" => "",
                            "resumeid" => $wpjobportal_resumeid,
                            "employer" => $wpjobportal_experience["employer"],
                            "employer_position" => $wpjobportal_experience["job_title"],
                            "employer_address" => $wpjobportal_experience["date"]."\n".$wpjobportal_experience["notes"],
                            "fromdate" => "",
                            "todate" => "",
                            "created" => $wpjobportal_resume->post_date,
                        ];
                        if(!($wpjobportal_row->bind($wpjobportal_data) && $wpjobportal_row->check() && $wpjobportal_row->store())){
                            echo "<br> error --- ";
                            return false;
                        }
                        // print_r($wpjobportal_data);
                    }
                    // print_r($wpjobportal_experiences);
                }
            }
        }
        //print_r($wpjobportal_companies);
        if(!empty($this->job_manager_resume_ids)){
            update_option('job_portal_jm_data_resumes', wp_json_encode($this->job_manager_resume_ids) );
        }
    }

    function importJobApplied(){
        $wpjobportal_job_applications = get_posts( [
            'post_type'      => 'job_application',
            'post_status'    => 'any', // includes all except 'auto-draft'
            'orderby'        => 'ID',
            'order'          => 'ASC',
            'numberposts'    => -1, // get all
            'exclude'        => get_posts([
                'post_type'   => 'job_application',
                'post_status'=> 'auto-draft',
                'fields'      => 'ids',
            ]),
        ] );

        // check if resume already processed for import
        $wpjobportal_imported_resumes = array();
        $wpjobportal_imported_resumes_json = get_option('job_portal_jm_data_resumes');
        if(!empty($wpjobportal_imported_resumes_json)){
            $wpjobportal_imported_resumes = json_decode($wpjobportal_imported_resumes_json,true);
        }
        foreach($wpjobportal_job_applications as $wpjobportal_job_application){ // store all job applies
            //echo '<pre>';print_r($wpjobportal_job_application);echo '</pre>';
            $post_meta = get_post_meta($wpjobportal_job_application->ID);
            //echo '<pre>';print_r($post_meta);echo '</pre>';
            //die("asd 2730");
            if($wpjobportal_job_application->post_parent){
                $wpjobportal_jobid = $this->getJobPortalJobIdByPost($wpjobportal_job_application->post_parent);
                $wpjobportal_uid = $this->getUserIDFromAuthorID($wpjobportal_job_application->post_author);
                if($wpjobportal_jobid){
                    /// check already imported
                    if(!empty( $wpjobportal_imported_resumes ) && in_array($wpjobportal_job_application->ID, $wpjobportal_imported_resumes) ){ // if id already in array skip it
                        $this->job_manager_import_count['jobapply']['skipped'] += 1;
                        continue;
                    }
                    $wpjobportal_resume_file = "";
                    if(!empty($post_meta["_job_applied_for"][0])) $wpjobportal_job_applied_for = $post_meta["_job_applied_for"][0]; else $wpjobportal_job_applied_for = "";
                    if(!empty($post_meta["_candidate_email"][0])) $candidate_email = $post_meta["_candidate_email"][0]; else $candidate_email = "";
                    if(!empty($post_meta["Message"][0])) $message = $post_meta["Message"][0]; else $message = "";
                    if(!empty($post_meta["_candidate_name"][0])) $full_name = $post_meta["_candidate_name"][0]; else $full_name = $wpjobportal_job_application->post_title;
                    //if(!empty($post_meta["Full name"][0])) $full_name = $post_meta["Full name"][0]; else $full_name = "";
                    // if name not set use post title // first_name system fielf (required)
                    $filename = '';
                    if(!empty($post_meta["_attachment_file"][0])){
                        $attachment_file = unserialize($post_meta["_attachment_file"][0]);
                        $wpjobportal_resume_file = $attachment_file[0];
                    }
                    $alias = wpjobportal::$_common->stringToAlias($full_name);

                    $wpjobportal_data = [
                        "id" => "",
                        "uid" => $wpjobportal_uid,
                        "first_name" => $full_name,
                        "email_address" => $candidate_email,
                        "alias" => $alias,
                        "status" => 1,
                        "quick_apply" => "1",
                        "last_modified" => $wpjobportal_job_application->post_date,
                        "created" => $wpjobportal_job_application->post_date
                    ];
                    $wpjobportal_row = WPJOBPORTALincluder::getJSTable('resume');
                    // not counting resume for job applies in resume to show consisitent data for stats and results page
                    if(!($wpjobportal_row->bind($wpjobportal_data) && $wpjobportal_row->check() && $wpjobportal_row->store())){
                        // $this->job_manager_import_count['resume']['failed'] += 1;
                        continue; // move on to next post if store fialed
                    }else{
                        // if no error then
                        $this->job_manager_resume_ids[] = $wpjobportal_job_application->ID; // create an array of resume ids to store
                        // $this->job_manager_import_count['resume']['imported'] += 1;
                    }
                    $wpjobportal_resumeid = $wpjobportal_row->id;

                    // handle resume file if any
                    if($wpjobportal_resume_file !=''){
                        $wpjobportal_resume_file_size = '';
                        $wpjobportal_resume_file_type = '';
                        $filename = basename($wpjobportal_resume_file);
                        $wpjobportal_upload_dir = wp_get_upload_dir();
                        $file_path = str_replace( $wpjobportal_upload_dir['baseurl'], $wpjobportal_upload_dir['basedir'], $wpjobportal_resume_file );
                        if ( file_exists( $file_path ) ) {
                            $file_type_array = wp_check_filetype( $file_path );
                            $wpjobportal_resume_file_type = $file_type_array['type'];
                            $wpjobportal_resume_file_size = filesize( $file_path );
                        }
                        // store resume file record
                        $wpjobportal_row = WPJOBPORTALincluder::getJSTable('resumefile');
                        $cols = array();
                        $cols['resumeid'] = $wpjobportal_resumeid;
                        $cols['filename'] = $filename;
                        $cols['filetype'] = $wpjobportal_resume_file_type;
                        $cols['filesize'] = $wpjobportal_resume_file_size;
                        $cols['created'] = $wpjobportal_job_application->post_date;
                        $cols = wpjobportal::wpjobportal_sanitizeData($cols);
                        //print_r($cols);
                        if($wpjobportal_row->bind($cols) && $wpjobportal_row->store()) { // if record inserted in table
                            $this->handleUploadFile(4, $wpjobportal_resumeid, $wpjobportal_resume_file); // move the file to wpjobportal uploads
                        }
                    }

                    $wpjobportal_data = [
                        "id" => "",
                        "jobid" => $wpjobportal_jobid,
                        "cvid" => $wpjobportal_resumeid,
                        "apply_date" => $wpjobportal_job_application->post_date,
                        "action_status" => 1,
                        "status" => 1,
                        "quick_apply" => 1,
                        "apply_message" => $message,
                        "params" => "",
                        "created" => $wpjobportal_job_application->post_date
                    ];
                    //print_r($wpjobportal_data);
                    $wpjobportal_row = WPJOBPORTALincluder::getJSTable('jobapply');
                    if(!($wpjobportal_row->bind($wpjobportal_data) && $wpjobportal_row->check() && $wpjobportal_row->store())){
                        $this->job_manager_import_count['jobapply']['failed'] += 1;
                        continue; // move on to next post if store fialed
                    }else{
                        // if no error then
                        $this->job_manager_jobapply_ids[] = $wpjobportal_job_application->ID; // create an array of jobapply ids to store
                        $this->job_manager_import_count['jobapply']['imported'] += 1;
                    }
                }else{ // job id not found for some reason then fail the job apply
                    $this->job_manager_import_count['jobapply']['failed'] += 1;
                }
            }
        }
        if(!empty($this->job_manager_resume_ids)){
            update_option('job_portal_jm_data_resumes', wp_json_encode($this->job_manager_resume_ids) );
        }
    }

    function getJobPortalJobIdByPost($postid){
        $wpjobportal_job = get_post( $postid );

        if(!empty($wpjobportal_job)){
            $wpjobportal_uid = $this->getUserIDFromAuthorID($wpjobportal_job->post_author);
            // if($wpjobportal_uid == 0){
            //     return false;
            // }
            $query = "SELECT job.id
                        FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobs` AS job
                        WHERE job.uid = ".esc_sql($wpjobportal_uid)."
                        AND LOWER(job.title) ='".strtolower(esc_sql($wpjobportal_job->post_title))."';";
            $jpjob_job_id = wpjobportaldb::get_var($query);
            if(is_numeric($jpjob_job_id) && $jpjob_job_id > 0){
                return $jpjob_job_id;
            }
        }
        return false;
    }

    function getCityId($city){
        $city_name = $wpjobportal_state_name = $wpjobportal_country_name = "";
        $city_arr = explode(",",$city);
        $city_name = $city_arr[0];
        if(count($city_arr) == 2){
            $wpjobportal_country_name = $city_arr[1];
        }
        if(count($city_arr) == 3){
            $wpjobportal_state_name = $city_arr[1];
            $wpjobportal_country_name = $city_arr[2];
        }
        if($wpjobportal_country_name){
            $query = "SELECT country.id
                        FROM `" . wpjobportal::$_db->prefix . "wj_portal_countries` AS country
                        WHERE LOWER(country.name) = '".strtolower(trim(esc_sql($wpjobportal_country_name)))."' OR LOWER(country.shortCountry) = '".strtolower(esc_sql($wpjobportal_country_name))."';";
                        //echo "<br>".$query;
            $jpcountry = wpjobportaldb::get_row($query);
            if($jpcountry){
                if($wpjobportal_state_name){
                    $query = "SELECT state.id
                                FROM `" . wpjobportal::$_db->prefix . "wj_portal_states` AS state
                                WHERE LOWER(state.name) = '".strtolower(trim(esc_sql($wpjobportal_state_name)))."' OR LOWER(state.shortRegion) = '".strtolower(esc_sql($wpjobportal_state_name))."'
                                AND state.countryid = ".$jpcountry->id.";";
                                //echo "<br>".$query;
                    $jpstate = wpjobportaldb::get_row($query);
                    if($jpstate){
                        $query = "SELECT city.id
                                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city
                                    WHERE (LOWER(city.name) = '".strtolower(trim(esc_sql($city_name)))."' OR LOWER(city.localname) = '".strtolower(trim(esc_sql($city_name)))."' OR LOWER(city.internationalname) = '".strtolower(trim($city_name))."')
                                    AND city.countryid = ".$jpcountry->id."
                                    AND city.stateid = ".$jpstate->id." ;";
                                    //echo "<br>".$query;
                        $jpcity = wpjobportaldb::get_row($query);
                        if($jpcity)
                            return $jpcity->id;
                    }else{
                        $query = "SELECT city.id
                                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city
                                    WHERE (LOWER(city.name) = '".strtolower(trim($city_name))."' OR LOWER(city.localname) = '".strtolower(trim(esc_sql($city_name)))."' OR LOWER(city.internationalname) = '".strtolower(trim(esc_sql($city_name)))."')
                                    AND city.countryid = ".$jpcountry->id.";";
                                    //echo "<br>".$query;
                        $jpcity = wpjobportaldb::get_row($query);
                        if($jpcity)
                            return $jpcity->id;
                    }
                }else{
                        $query = "SELECT city.id
                                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_cities` AS city
                                    WHERE (LOWER(city.name) = '".strtolower(trim($city_name))."' OR LOWER(city.localname) = '".strtolower(trim(esc_sql($city_name)))."' OR LOWER(city.internationalname) = '".strtolower(trim(esc_sql($city_name)))."')
                                    AND city.countryid = ".$jpcountry->id.";";
                                    //echo "<br>".$query;
                        $jpcity = wpjobportaldb::get_row($query);
                        if($jpcity)
                            return $jpcity->id;
                }
            }
        }
        return;

    }
    function getSalaryDuration($duration){
        if($duration == ''){
            return '';
        }
        $title = "Per ".$duration;
        $query = "SELECT type.id
                    FROM `" . wpjobportal::$_db->prefix . "wj_portal_salaryrangetypes` AS type
                    WHERE LOWER(type.title) = '".strtolower(esc_sql($title))."';";
                    //echo "<br>".$query;
        $jptitle = wpjobportaldb::get_row($query);
        if($jptitle){
            return $jptitle->id;
        }else{

            $query = "SELECT MAX(sal_type.ordering)
                  FROM `" . wpjobportal::$_db->prefix . "wj_portal_salaryrangetypes` AS sal_type";
                $ordering = (int) wpjobportal::$_db->get_var($query);

            $wpjobportal_data = [
                "id" => "",
                "title" => $title,
                "status" => "1",
                "isdefault" => "",
                "ordering" => ++$ordering
            ];
            $wpjobportal_row = WPJOBPORTALincluder::getJSTable('salaryrangetype');

            if(!($wpjobportal_row->bind($wpjobportal_data) && $wpjobportal_row->check() && $wpjobportal_row->store())){
                return false;
            }else{
                return $wpjobportal_row->id;
            }

        }

    }

    function getJobTypeByTitle($title) {
        $wpjobportal_job_type = '';
        // Fetch all job types
        $query = "SELECT jobtype.id, jobtype.title
                  FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype";
        $jpjobtypes = wpjobportal::$_db->get_results($query);

        $jp_job_types_array = [];

        // make array for comparison
        foreach ($jpjobtypes as $wpjobportal_jobtype) {
            $jp_job_types_array[$this->cleanStringForCompare($wpjobportal_jobtype->title)] = $wpjobportal_jobtype->id;
        }

        $wpjobportal_compare_name = $this->cleanStringForCompare($title);

        // Check if the cleaned title exists as a key in the array and retrieve the corresponding ID
        if (isset($jp_job_types_array[$wpjobportal_compare_name])) {
            $wpjobportal_job_type = $jp_job_types_array[$wpjobportal_compare_name];
        }

        return $wpjobportal_job_type;
    }

    function getJobCategoriesByTitle($title) {
        // Fetch all categories
        $query = "SELECT category.id, category.cat_title
                  FROM `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS category";
        $wpjobportal_categories = wpjobportal::$_db->get_results($query);

        $jp_category_names_array = [];

        foreach ($wpjobportal_categories as $wpjobportal_category) {
            $jp_category_names_array[$this->cleanStringForCompare($wpjobportal_category->cat_title)] = $wpjobportal_category->id;
        }

        $wpjobportal_compare_name = $this->cleanStringForCompare($title);

        // Check if the cleaned category title exists as a
        if (isset($jp_category_names_array[$wpjobportal_compare_name])) {
            return $jp_category_names_array[$wpjobportal_compare_name];
        }
        return '';
    }

    function getJobTagsByTitle($wpjobportal_tags){
        $wpjobportal_tags_title = "";
        foreach($wpjobportal_tags as $wpjobportal_tag){
            $query = "SELECT tag.tag
                        FROM `" . wpjobportal::$_db->prefix . "wj_portal_tags` AS tag
                        WHERE LOWER(tag.tag) = '".strtolower(esc_sql($wpjobportal_tag))."';";
                        //echo "<br>".$query;
            $jptags = wpjobportaldb::get_row($query);
            if($jptags){
                if($wpjobportal_tags_title) $wpjobportal_tags_title = $wpjobportal_tags_title.", ".$jptags->tag;
                else $wpjobportal_tags_title = $jptags->tag;
            }
        }
        return $wpjobportal_tags_title;
    }

    function importCategories() {
        $wpjobportal_job_categories = get_terms([
            'taxonomy'   => 'job_listing_category',
            'hide_empty' => false,
        ]);

        $wpjobportal_resume_categories = get_terms([
            'taxonomy'   => 'resume_category',
            'hide_empty' => false,
        ]);
	$wpjobportal_categories = [];
        if (!is_wp_error($wpjobportal_job_categories) && is_array($wpjobportal_job_categories)) {
            if(is_array($wpjobportal_resume_categories)){
                $wpjobportal_categories = array_merge($wpjobportal_job_categories, $wpjobportal_resume_categories);
            }else{
                $wpjobportal_categories = $wpjobportal_job_categories;
            }
        }

        // Get max ordering from existing categories table
        $query = "SELECT MAX(category.ordering)
                  FROM `" . wpjobportal::$_db->prefix . "wj_portal_categories` AS category";
        $ordering = (int) wpjobportal::$_db->get_var($query);

        $jp_category_names = [];

        // Fetch all existing category names
        $query = "SELECT id,cat_title FROM `" . wpjobportal::$_db->prefix . "wj_portal_categories`";
        $wpjobportal_existing_categories = wpjobportal::$_db->get_results($query);

        foreach ($wpjobportal_existing_categories as $wpjobportal_existing_category) {
            // sanitized for comparison
            $jp_category_names[$wpjobportal_existing_category->id] = $this->cleanStringForCompare($wpjobportal_existing_category->cat_title);
        }

        if ( !empty($wpjobportal_categories)) {
            foreach ($wpjobportal_categories as $wpjobportal_category) {
                $parent_category_id = '';
                $wpjobportal_name = $wpjobportal_category->name;
                $wpjobportal_compare_name = $this->cleanStringForCompare($wpjobportal_name);

                if (!empty($jp_category_names) && in_array($wpjobportal_compare_name, $jp_category_names)) {
                    $this->job_manager_import_count['category']['skipped'] += 1;
                    continue;
                }

                $alias = wpjobportal::$_common->stringToAlias($wpjobportal_name);

                // Handle parent ID lookup using WP functions
                if ($wpjobportal_category->parent) {
                    $parent_term = get_term($wpjobportal_category->parent);
                    if ($parent_term && !is_wp_error($parent_term)) {
                        $parent_compare_name = $this->cleanStringForCompare($parent_term->name);

                        $parent_id = array_search($parent_compare_name, $jp_category_names);
                        if ($parent_id !== false) {
                            // Parent category found, assign the id
                            $parent_category_id = $parent_id;
                        }
                    }
                }

                $wpjobportal_row = WPJOBPORTALincluder::getJSTable('categories');
                $wpjobportal_updated = date_i18n('Y-m-d H:i:s');
                $created = date_i18n('Y-m-d H:i:s');

                $wpjobportal_data = [];
                $wpjobportal_data['id']         = '';
                $wpjobportal_data['cat_value']  = '';
                $wpjobportal_data['cat_title']  = $wpjobportal_name;
                $wpjobportal_data['alias']      = $alias;
                $wpjobportal_data['isactive']   = '1';
                $wpjobportal_data['isdefault']  = '0';
                $wpjobportal_data['ordering']   = $ordering;
                $wpjobportal_data['parentid']   = $parent_category_id;

                if (!($wpjobportal_row->bind($wpjobportal_data) && $wpjobportal_row->check() && $wpjobportal_row->store())) {
                    $this->job_manager_import_count['category']['failed'] += 1;
                    continue;
                } else {
                    $this->job_manager_category_ids[] = $wpjobportal_category->term_id;
                    $this->job_manager_import_count['category']['imported'] += 1;
                }
                $ordering++;
            }
        }
    }

    function cleanStringForCompare($wpjobportal_string){
        if($wpjobportal_string == ''){
            return $wpjobportal_string;
        }
        // already null checked so no need for         wpjobportalphplib::wpJP_ functions
        $wpjobportal_string = str_replace(' ', '', $wpjobportal_string);
        $wpjobportal_string = str_replace('-', '', $wpjobportal_string);
        $wpjobportal_string = str_replace('_', '', $wpjobportal_string);
        $wpjobportal_string = trim($wpjobportal_string);
        $wpjobportal_string = strtolower($wpjobportal_string);
        return $wpjobportal_string;
    }

    function importJobTypes(){
        $wpjobportal_jobtypes = get_terms( [
                'taxonomy'   => 'job_listing_type',
                'hide_empty' => false, // set to true if you only want terms with posts
            ] );
        // max ordering from table
        $query = "SELECT MAX(jobtype.ordering)
                        FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ";
        $ordering = (int) wpjobportal::$_db->get_var($query);
        // $ordering = 25;

        $jp_job_types_array = [];
        if ( ! is_wp_error( $wpjobportal_jobtypes ) && ! empty( $wpjobportal_jobtypes ) ) {

            // to compare job portal job types with wp job manager job types
            $query = "SELECT jobtype.id, jobtype.title
                            FROM `" . wpjobportal::$_db->prefix . "wj_portal_jobtypes` AS jobtype ";
            $jpjobtypes = wpjobportal::$_db->get_results($query);
            foreach ($jpjobtypes as $wpjobportal_jobtype) {
                $jp_job_types_array[] = $this->cleanStringForCompare($wpjobportal_jobtype->title);
            }

            // colors for new job types
            $wpjobportal_colors = ['#3E4095','#ED3237','#EC268F','#A8518A','#F58634','#84716B','#48887B','#6E4D8B'];
            $wpjobportal_colorIndex = 0;

            foreach($wpjobportal_jobtypes AS $wpjobportal_jobtype){
                $parent_category_id = "";
                $wpjobportal_name = $wpjobportal_jobtype->name;
                $wpjobportal_compare_name = $this->cleanStringForCompare($wpjobportal_name);

                if(!empty($jp_job_types_array) && in_array($wpjobportal_compare_name, $jp_job_types_array) ){ // try and match job type title
                    $this->job_manager_import_count['jobtype']['skipped'] += 1;
                    continue; // ignore current job type if it mathces
                }
                $alias = wpjobportal::$_common->stringToAlias($wpjobportal_name);

                $wpjobportal_row = WPJOBPORTALincluder::getJSTable('jobtype');
                $wpjobportal_updated = date_i18n('Y-m-d H:i:s');
                $created = date_i18n('Y-m-d H:i:s');

                $wpjobportal_data = []; // reset object
                $wpjobportal_data['id'] = '';
                $wpjobportal_data['title'] = $wpjobportal_name;
                // handle tangent ccase
                if($wpjobportal_colorIndex > 7){
                    $wpjobportal_colorIndex = 0;
                }
                $wpjobportal_data['color'] = $wpjobportal_colors[$wpjobportal_colorIndex];
                $wpjobportal_colorIndex++;
                $wpjobportal_data['alias'] = $alias;
                $wpjobportal_data['isactive'] = "1";
                $wpjobportal_data['isdefault'] = '0';
                $wpjobportal_data['ordering'] = $ordering;
                $wpjobportal_data['status'] = "1";

                if(!($wpjobportal_row->bind($wpjobportal_data) && $wpjobportal_row->check() && $wpjobportal_row->store())){
                    $this->job_manager_import_count['jobtype']['failed'] += 1;
                    continue; // move on to next post if store fialed
                }else{
                    // if no error then
                    //$this->job_manager_jobtype_ids[] = $wpjobportal_jobtype->id; // create an array of job type ids to store
                    $this->job_manager_import_count['jobtype']['imported'] += 1;
                }
                $ordering = $ordering + 1;
            }
        }
    }

    function importTags(){
        // problem case
        $query = "SELECT taxonomy.*, terms.*
                    FROM `" . wpjobportal::$_db->prefix . "term_taxonomy` AS taxonomy
                    JOIN `" . wpjobportal::$_db->prefix . "terms` AS terms ON terms.term_id = taxonomy.term_id
                    WHERE taxonomy.taxonomy = 'job_listing_tag';";
        $wpjobportal_tags = wpjobportal::$_db->get_results($query);

        if($wpjobportal_tags){
            foreach($wpjobportal_tags AS $wpjobportal_tag){
                $wpjobportal_name = $wpjobportal_tag->name;

                $query = "SELECT tag.*
                            FROM `" . wpjobportal::$_db->prefix . "wj_portal_tags` AS tag
                            WHERE LOWER(tag.tag) = '".strtolower(esc_sql($wpjobportal_name))."'";
                $jptag = wpjobportal::$_db->get_row($query);

                if(!$jptag){ // not exists
                    $alias = wpjobportal::$_common->stringToAlias($wpjobportal_name);

                    $wpjobportal_row = WPJOBPORTALincluder::getJSTable('tag');
                    $wpjobportal_updated = date_i18n('Y-m-d H:i:s');
                    $created = date_i18n('Y-m-d H:i:s');

                    $wpjobportal_data['id'] = '';
                    $wpjobportal_data['tag'] = $wpjobportal_name;
                    $wpjobportal_data['alias'] = $alias;
                    $wpjobportal_data['tagfor'] = "1";
                    $wpjobportal_data['status'] = "1";
                    $wpjobportal_data['created'] = $created;
                    $wpjobportal_data['createdby'] = "";
                    //print_r($wpjobportal_data);

                    if(!($wpjobportal_row->bind($wpjobportal_data) && $wpjobportal_row->check() && $wpjobportal_row->store())){
                        $this->job_manager_import_count['tag']['failed'] += 1;
                        continue; // move on to next post if store fialed
                    }else{
                        // if no error then
                        $this->job_manager_import_count['tag']['imported'] += 1;
                    }
                }else{ // if record matched then ignore
                    $this->job_manager_import_count['tag']['skipped'] += 1;
                }
            }
        }
    }

    function importJobFields(){
        $wpjobportal_all_custom_fields = get_option("_transient_jmfe_fields_custom"); // job custom fields
        //if(isset($custom_fields["job"]))$custom_fields_job = $custom_fields["job"];
        //print_r($wpjobportal_all_custom_fields);
        //die();

        if(empty($wpjobportal_all_custom_fields)){ // to handle error
            return;
        }

        //print_r($custom_fields_job);
        foreach($wpjobportal_all_custom_fields as $wpjobportal_key=>$wpjobportal_value){
            foreach($wpjobportal_value AS $custom_field){
                switch ($custom_field["type"]){
                    case "text":
                        $wpjobportal_fieldtype = "text"; break;
                    case "select":
                        $wpjobportal_fieldtype = "combo"; break;
                    case "radio":
                        $wpjobportal_fieldtype = "radio"; break;
                    case "checklist":
                        $wpjobportal_fieldtype = "checkbox"; break;
                    case "textarea":
                        $wpjobportal_fieldtype = "textarea"; break;
                    case "number":
                        $wpjobportal_fieldtype = "text"; break;
                    case "range":
                        $wpjobportal_fieldtype = "text"; break;
                    case "email":
                        $wpjobportal_fieldtype = "email"; break;
                    case "url":
                        $wpjobportal_fieldtype = "text"; break;
                    case "tel":
                        $wpjobportal_fieldtype = "text"; break;
                    case "wp-editor":
                        $wpjobportal_fieldtype = "textarea"; break;
                    case "file":
                        $wpjobportal_fieldtype = "file"; break;
                    case "date":
                        $wpjobportal_fieldtype = "date"; break;
                    case "fpdate":
                        $wpjobportal_fieldtype = "date"; break;
                    case "fptime":
                        $wpjobportal_fieldtype = "date"; break;
                    case "phone":
                        $wpjobportal_fieldtype = "text"; break;
                    case "checkbox":
                        $wpjobportal_fieldtype = "checkbox"; break;
                    case "multiselect":
                        $wpjobportal_fieldtype = "combo"; break;

                }
                $wpjobportal_fieldfor = "";
                $wpjobportal_section = "";
                if($wpjobportal_key == "company") $wpjobportal_fieldfor = 1;
                elseif($wpjobportal_key == "job") $wpjobportal_fieldfor = 2;
                elseif($wpjobportal_key == "resume_fields"){ $wpjobportal_fieldfor = 3; $wpjobportal_section = 1; }
                // check if field already exsists

                $query = "SELECT id,field FROM `" . wpjobportal::$_db->prefix . "wj_portal_fieldsordering` WHERE isuserfield = 1 AND LOWER(fieldtitle) ='".esc_sql(strtolower($custom_field["label"]))."' AND userfieldtype ='".esc_sql($wpjobportal_fieldtype)."' AND fieldfor = " . esc_sql($wpjobportal_fieldfor);
                $wpjobportal_field_record = wpjobportaldb::get_row($query);

                if(!empty($wpjobportal_field_record)){ // this will make sure
                    // set field to array so that i can be used for importing entities (job,company,resume)
                    if($wpjobportal_fieldfor == 1){
                        $this->job_manager_company_custom_fields[] = array("name" => $custom_field["meta_key"], "type" =>$wpjobportal_fieldtype, "jp_filedorderingid" => $wpjobportal_field_record->id, "jp_filedorderingfield" => $wpjobportal_field_record->field);
                    }elseif($wpjobportal_fieldfor == 2){
                        $this->job_manager_job_custom_fields[] = array("name" => $custom_field["meta_key"], "type" =>$wpjobportal_fieldtype, "jp_filedorderingid" => $wpjobportal_field_record->id, "jp_filedorderingfield" => $wpjobportal_field_record->field);
                    }elseif($wpjobportal_fieldfor == 3){
                        $this->job_manager_resume_custom_fields[] = array("name" => $custom_field["meta_key"], "type" =>$wpjobportal_fieldtype, "jp_filedorderingid" => $wpjobportal_field_record->id, "jp_filedorderingfield" => $wpjobportal_field_record->field);
                    }

                    $this->job_manager_import_count['field']['skipped'] += 1;
                    continue;
                }

                $wpjobportal_option_values = "";
                if(isset($custom_field["options"])){
                    foreach($custom_field["options"] as $opt_key => $opt_value){
                        if(empty($wpjobportal_option_values)) $wpjobportal_option_values = $opt_value;
                        else $wpjobportal_option_values = $wpjobportal_option_values ."\n ". $opt_value;
                    }
                }
                $wpjobportal_required = 0;
                if(isset($custom_field["required"])){
                    if($custom_field["required"] == "yes") $wpjobportal_required = 1;
                }
                    //die();
                    //echo "<br> set";
                    $wpjobportal_fieldOrderingData = [
                        "id" =>"",
                        "field" => $custom_field["meta_key"],
                        "fieldtitle" => $custom_field["label"],
                        "placeholder" => $custom_field["placeholder"],
                        "description" => $custom_field["description"],
                        "ordering" => "",
                        "section" => $wpjobportal_section,
                        "fieldfor" => $wpjobportal_fieldfor,
                        "published" => "1",
                        "isvisitorpublished" => "1",
                        "sys" => "0",
                        "cannotunpublish" => "0",
                        "required" => $wpjobportal_required,
                        "cannotsearch" => "0",
                        "search_ordering" => "",
                        "isuserfield" => "1",
                        "userfieldtype" => $wpjobportal_fieldtype,
                        "options" => $wpjobportal_option_values,
                        "search_user" => "0",
                        "search_visitor" => "0",
                        "showonlisting" => "0",
                        "cannotshowonlisting" => "0",
                        "depandant_field" => "",
                        "j_script" => "",
                        "size" => "",
                        "maxlength" => "255",
                        "cols" => "",
                        "rows" => "",
                        "readonly" => "",
                        "is_section_headline" => "",
                        "visible_field" => "",
                        "visibleparams" => "",
                    ];
                    //echo "<br>key: ".$wpjobportal_key."<br>";
                    //print_r($wpjobportal_fieldOrderingData);
                    //echo "<br>cf: ".$wpjobportal_fieldtype;
                    //print_r($custom_field);

                    //die();
                    // WPJOBPORTAL_SAVE_ERROR


                    $record_saved =  WPJOBPORTALincluder::getJSModel('fieldordering')->storeUserField($wpjobportal_fieldOrderingData);
                    if($record_saved == WPJOBPORTAL_SAVED){
                        $query = "SELECT * FROM `" . wpjobportal::$_db->prefix . "wj_portal_fieldsordering` ORDER BY id DESC LIMIT 0, 1";
                        //echo "<br> ".$query;
                        $latest_record = wpjobportal::$_db->get_row($query);
                        if($wpjobportal_fieldfor == 1){
                            $this->job_manager_company_custom_fields[] = array("name" => $custom_field["meta_key"], "type" =>$wpjobportal_fieldtype, "jp_filedorderingid" => $latest_record->id, "jp_filedorderingfield" => $latest_record->field);
                        }elseif($wpjobportal_fieldfor == 2){
                            $this->job_manager_job_custom_fields[] = array("name" => $custom_field["meta_key"], "type" =>$wpjobportal_fieldtype, "jp_filedorderingid" => $latest_record->id, "jp_filedorderingfield" => $latest_record->field);
                        }elseif($wpjobportal_fieldfor == 3){
                            $this->job_manager_resume_custom_fields[] = array("name" => $custom_field["meta_key"], "type" =>$wpjobportal_fieldtype, "jp_filedorderingid" => $latest_record->id, "jp_filedorderingfield" => $latest_record->field);
                        }
                        $this->job_manager_import_count['field']['imported'] += 1;
                    }else{
                        $this->job_manager_import_count['field']['failed'] += 1;
                        continue;
                    }
            }
        }
    }

    function parseJobManagerSalaryData($post_id) {
        $wpjobportal_salary_raw = get_post_meta($post_id, '_job_salary');
        $wpjobportal_salary_min = get_post_meta($post_id, '_job_salary_min', true);
        $wpjobportal_salary_max = get_post_meta($post_id, '_job_salary_max', true);
        $wpjobportal_salary_currency = get_post_meta($post_id, '_job_salary_currency', true);
        $wpjobportal_salary_type = get_post_meta($post_id, '_job_salary_unit', true); // e.g., yearly, monthly, etc.

        // return array
        $wpjobportal_result = array(
            'currency' => null,
            'type'     => null,
            'min'      => null,
            'max'      => null,
        );

        // Normalize and sanitize input
        $type = is_string($wpjobportal_salary_type) ? strtolower(trim($wpjobportal_salary_type)) : '';
        $currency_code = is_string($wpjobportal_salary_currency) ? strtoupper(trim($wpjobportal_salary_currency)) : '';

        $currency_map = array(
                '€' => 'EUR',
                '$' => 'USD',
                '£' => 'GBP',
                '₹' => 'INR',
                '¥' => 'JPY',
                '₽' => 'RUB',
                '₩' => 'KRW',
                'AED' => 'AED',
                'Rs' => 'PKR',
            );

        if (!empty($wpjobportal_salary_min) || !empty($wpjobportal_salary_max)) {
            $wpjobportal_result['min'] = !empty($wpjobportal_salary_min) ? $this->normalize_salary_value($wpjobportal_salary_min) : null;
            $wpjobportal_result['max'] = !empty($wpjobportal_salary_max) ? $this->normalize_salary_value($wpjobportal_salary_max) : null;
            $wpjobportal_result['type'] = !empty($type) ? $type : null;
            $wpjobportal_result['currency'] = !empty($currency_code) ? $currency_code : null;
            // Parse currency
            foreach ($currency_map as $symbol => $code) {
                if (strpos($currency_code, $symbol) !== false || stripos($currency_code, $code) !== false) {
                    $wpjobportal_result['currency'] = $symbol;
                    break;
                }
            }
        } elseif (!empty($wpjobportal_salary_raw)) {
            // Convert array to string if needed
            if (is_array($wpjobportal_salary_raw)) {
                $flattened = [];
                foreach ($wpjobportal_salary_raw as $wpjobportal_item) {
                    if (is_array($wpjobportal_item)) {
                        $flattened = array_merge($flattened, $wpjobportal_item);
                    } else {
                        $flattened[] = $wpjobportal_item;
                    }
                }
                $wpjobportal_salary_raw = implode(' - ', $flattened);
            }

            // Now apply regex
            preg_match_all('/[\$€£₹¥₽₩]?\s*(\d{1,3}(?:[.,]?\d{3})*|\d+)(k)?/i', $wpjobportal_salary_raw, $wpjobportal_matches);

            $wpjobportal_numbers = [];
            if (!empty($wpjobportal_matches[1])) {
                foreach ($wpjobportal_matches[1] as $wpjobportal_index => $wpjobportal_number) {
                    $wpjobportal_value = floatval(str_replace([',', ' '], '', $wpjobportal_number));
                    $wpjobportal_is_k = isset($wpjobportal_matches[2][$wpjobportal_index]) && strtolower($wpjobportal_matches[2][$wpjobportal_index]) === 'k';

                    if ($wpjobportal_is_k) {
                        $wpjobportal_value *= 1000;
                    }

                    $wpjobportal_numbers[] = $wpjobportal_value;
                }
            }
            if (count($wpjobportal_numbers) === 1) {
                $wpjobportal_result['min'] = $wpjobportal_numbers[0];
            } elseif (count($wpjobportal_numbers) >= 2) {
                $wpjobportal_result['min'] = min($wpjobportal_numbers);
                $wpjobportal_result['max'] = max($wpjobportal_numbers);
            }

            //Parse currency
            foreach ($currency_map as $symbol => $code) {
                if (strpos($wpjobportal_salary_raw, $symbol) !== false || stripos($wpjobportal_salary_raw, $code) !== false) {
                    $wpjobportal_result['currency'] = $symbol;
                    break;
                }
            }

            if(empty($wpjobportal_salary_type)){
                //Parse salary type
                $raw = strtolower($wpjobportal_salary_raw);
                if (strpos($raw, 'year') !== false || strpos($raw, 'annum') !== false) {
                    $wpjobportal_result['type'] = 'Year';
                } elseif (strpos($raw, 'month') !== false) {
                    $wpjobportal_result['type'] = 'Month';
                } elseif (strpos($raw, 'week') !== false) {
                    $wpjobportal_result['type'] = 'Week';
                } elseif (strpos($raw, 'day') !== false) {
                    $wpjobportal_result['type'] = 'Day';
                } elseif (strpos($raw, 'hour') !== false) {
                    $wpjobportal_result['type'] = 'Hour';
                }
            }else{
                $wpjobportal_result['type'] = is_string($wpjobportal_salary_type) ? trim($wpjobportal_salary_type) : '';
            }
        }
        return $wpjobportal_result;
    }

    function getMessagekey(){
        $wpjobportal_key = 'thirdpartyimport';if(wpjobportal::$_common->wpjp_isadmin()){$wpjobportal_key = 'admin_'.$wpjobportal_key;}return $wpjobportal_key;
    }

}

?>
