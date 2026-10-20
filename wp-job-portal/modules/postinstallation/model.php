<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALPostinstallationModel {

    private static $wpjobportal_theme_demo_created_posts = array();

    function updateInstallationStatusConfiguration(){
            $flag = get_option('wpjobportal_post_installation');
            if($flag == false){
                add_option( 'wpjobportal_post_installation', '1', '', 'yes' );
            }else{
                update_option( 'wpjobportal_post_installation', '1');
            }
    }

    function storeconfigurations($wpjobportal_data){
        if (empty($wpjobportal_data))
            return false;
        $error = false;
        unset($wpjobportal_data['action']);
        unset($wpjobportal_data['form_request']);
        $wpjobportal_is_mode_stage = isset($wpjobportal_data['setup_stage'])
            && sanitize_key($wpjobportal_data['setup_stage']) === 'mode';
        if($wpjobportal_data['step'] == 0 || $wpjobportal_is_mode_stage){
            $wpjobportal_mode = isset($wpjobportal_data['product_mode'])
                ? sanitize_key($wpjobportal_data['product_mode'])
                : '';

            // Preserve compatibility with older setup forms and extensions.
            if($wpjobportal_mode == '' && isset($wpjobportal_data['enable_multiple_employers_mode'])){
                $wpjobportal_mode = absint($wpjobportal_data['enable_multiple_employers_mode']) === 1
                    ? WPJOBPORTAL_Product_Mode_Service::MODE_MARKETPLACE
                    : WPJOBPORTAL_Product_Mode_Service::MODE_CAREERS;
            }

            if(!WPJOBPORTAL_Product_Mode_Service::apply_mode($wpjobportal_mode)){
                $error = true;
            }
        }else{
            unset($wpjobportal_data['step']);
            unset($wpjobportal_data['setup_stage']);
            if(isset($wpjobportal_data['enable_multiple_employers_mode'])){
                unset($wpjobportal_data['enable_multiple_employers_mode']);
            }
            // Later wizard steps write whatever they are given, so the mode's
            // locked values are re-applied here for the same reason they are in
            // the settings screen.
            $wpjobportal_data = WPJOBPORTAL_Mode_Policy_Service::enforce_settings($wpjobportal_data);
            foreach ($wpjobportal_data as $wpjobportal_key => $wpjobportal_value) {
                $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_config` SET `configvalue` = '" . esc_sql($wpjobportal_value) . "' WHERE `configname`= '" . esc_sql($wpjobportal_key) . "'";
                if (!wpjobportaldb::query($query)) {
                    $error = true;
                }
            }

        }
            if ($error)
                return WPJOBPORTAL_SAVE_ERROR;
            else
                return WPJOBPORTAL_SAVED;
    }

    function getConfigurationValues(){
        $this->updateInstallationStatusConfiguration();
        $query = "SELECT configvalue,configname  FROM`" . wpjobportal::$_db->prefix . "wj_portal_config`";
        $wpjobportal_data = wpjobportaldb::get_results($query);
        $wpjobportal_config_array = array();
        foreach ($wpjobportal_data as $wpjobportal_config) {
            if($wpjobportal_config->configname == 'offline'){
                $wpjobportal_config_array['offline']=$wpjobportal_config->configvalue;
            }
            if($wpjobportal_config->configname == 'title'){
                $wpjobportal_config_array['title']=$wpjobportal_config->configvalue;
            }
            if($wpjobportal_config->configname == 'adminemailaddress'){
                $wpjobportal_config_array['adminemailaddress']=$wpjobportal_config->configvalue;
            }
            if($wpjobportal_config->configname == 'mailfromaddress'){
                $wpjobportal_config_array['mailfromaddress']=$wpjobportal_config->configvalue;
            }
            if($wpjobportal_config->configname == 'system_slug'){
                $wpjobportal_config_array['system_slug']=$wpjobportal_config->configvalue;
            }
            if($wpjobportal_config->configname == 'disable_employer'){
                $wpjobportal_config_array['disable_employer']=$wpjobportal_config->configvalue;
            }
            if($wpjobportal_config->configname == 'cur_location'){
                $wpjobportal_config_array['cur_location']=$wpjobportal_config->configvalue;
            }
            if($wpjobportal_config->configname == 'companyautoapprove'){
                $wpjobportal_config_array['companyautoapprove']=$wpjobportal_config->configvalue;
            }
            if($wpjobportal_config->configname == 'jobautoapprove'){
                $wpjobportal_config_array['jobautoapprove']=$wpjobportal_config->configvalue;
            }
            if($wpjobportal_config->configname == 'empautoapprove'){
                $wpjobportal_config_array['empautoapprove']=$wpjobportal_config->configvalue;
            }
            if($wpjobportal_config->configname == 'newdays'){
                $wpjobportal_config_array['newdays']=$wpjobportal_config->configvalue;
            }
            if($wpjobportal_config->configname == 'searchjobtag'){
                $wpjobportal_config_array['searchjobtag']=$wpjobportal_config->configvalue;
            }
            if($wpjobportal_config->configname == 'visitor_can_apply_to_job'){
                $wpjobportal_config_array['visitor_can_apply_to_job']=$wpjobportal_config->configvalue;
            }
            if($wpjobportal_config->configname == 'visitor_can_post_job'){
                $wpjobportal_config_array['visitor_can_post_job']=$wpjobportal_config->configvalue;
            }
            if($wpjobportal_config->configname == 'employerview_js_controlpanel'){
                $wpjobportal_config_array['employerview_js_controlpanel']=$wpjobportal_config->configvalue;
            }
            if($wpjobportal_config->configname == 'data_directory'){
                $wpjobportal_config_array['data_directory']=$wpjobportal_config->configvalue;
            }
            if($wpjobportal_config->configname == 'date_format'){
                $wpjobportal_config_array['date_format']=$wpjobportal_config->configvalue;
            }
            if($wpjobportal_config->configname == 'mailfromname'){
                $wpjobportal_config_array['mailfromname']=$wpjobportal_config->configvalue;
            }
            if($wpjobportal_config->configname == 'showemployerlink'){
                $wpjobportal_config_array['showemployerlink']=$wpjobportal_config->configvalue;
            }
            if($wpjobportal_config->configname == 'system_have_gold_job'){
                $wpjobportal_config_array['system_have_gold_job']=$wpjobportal_config->configvalue;
            }
            if($wpjobportal_config->configname == 'system_have_featured_job'){
                $wpjobportal_config_array['system_have_featured_job']=$wpjobportal_config->configvalue;
            }
            if($wpjobportal_config->configname == 'allow_jobshortlist'){
                $wpjobportal_config_array['allow_jobshortlist']=$wpjobportal_config->configvalue;
            }
            if($wpjobportal_config->configname == 'allow_tellafriend'){
                $wpjobportal_config_array['allow_tellafriend']=$wpjobportal_config->configvalue;
            }
            if($wpjobportal_config->configname == 'employer_defaultgroup'){
                $wpjobportal_config_array['employer_defaultgroup']=$wpjobportal_config->configvalue;
            }
            if($wpjobportal_config->configname == 'jobseeker_defaultgroup'){
                $wpjobportal_config_array['jobseeker_defaultgroup']=$wpjobportal_config->configvalue;
            }
            if($wpjobportal_config->configname == 'default_pageid'){
                $wpjobportal_config_array['default_pageid']=$wpjobportal_config->configvalue;
            }
            if($wpjobportal_config->configname == 'allow_search_resume'){
                $wpjobportal_config_array['allow_search_resume']=$wpjobportal_config->configvalue;
            }
            if($wpjobportal_config->configname == 'defaultaddressdisplaytype'){
                $wpjobportal_config_array['defaultaddressdisplaytype']=$wpjobportal_config->configvalue;
            }
            if($wpjobportal_config->configname == 'quick_apply_for_user'){
                $wpjobportal_config_array['quick_apply_for_user']=$wpjobportal_config->configvalue;
            }
            if($wpjobportal_config->configname == 'quick_apply_for_visitor'){
                $wpjobportal_config_array['quick_apply_for_visitor']=$wpjobportal_config->configvalue;
            }
        }
        wpjobportal::$_data[0] = $wpjobportal_config_array;

    }

    /**
     * Who the sample records should belong to.
     *
     * @param int $wpjobportal_chosen Portal user id picked on the onboarding screen, or 0.
     * @param int $wpjobportal_roleid 1 employer, 2 job seeker.
     * @return int Portal user id, or 0 when the site holds nobody of that role.
     */
    function installSampleData($wpjobportal_insertsampledata, $wpjobportal_jsmenu,$wpjobportal_empmenu,$wpjobportal_temp_data = 0,$wpjobportal_joblist_menu = 0) {


        if($wpjobportal_jsmenu == 1){
            $query = "SELECT COUNT(ID) FROM ".wpjobportal::$_db->prefix."posts WHERE post_content LIKE '%[wpjobportal_jobseeker_controlpanel]%'";
            $pageexists = wpjobportal::$_db->get_var($query);
            if($pageexists == 0){
                $post = array(
                    'post_name' => 'wp-job-portal-jobseeker-controlpanel',
                    'post_title' => 'Job seeker',
                    'post_status' => 'publish',
                    'post_content' => '[wpjobportal_jobseeker_controlpanel]',
                    'post_type' => 'page'
                );
                $post_ID = wp_insert_post($post);
                if (!is_wp_error($post_ID)) {
                    WPJOBPORTAL_Sample_Data_Service::record_entity('pages', $post_ID);
                }
            }
        }
        if($wpjobportal_empmenu == 1){
            $query = "SELECT COUNT(ID) FROM ".wpjobportal::$_db->prefix."posts WHERE post_content LIKE '%[wpjobportal_employer_controlpanel]%'";
            $pageexists = wpjobportal::$_db->get_var($query);
            if($pageexists == 0){
                $post = array(
                    'post_name' => 'wp-job-portal-employer-controlpanel',
                    'post_title' => 'Employer',
                    'post_status' => 'publish',
                    'post_content' => '[wpjobportal_employer_controlpanel]',
                    'post_type' => 'page'
                );
                $post_ID = wp_insert_post($post);
                if (!is_wp_error($post_ID)) {
                    WPJOBPORTAL_Sample_Data_Service::record_entity('pages', $post_ID);
                }
            }
        }
        if($wpjobportal_joblist_menu == 1){
            $query = "SELECT COUNT(ID) FROM ".wpjobportal::$_db->prefix."posts WHERE post_content LIKE '%[wpjobportal_job]%'";
            $pageexists = wpjobportal::$_db->get_var($query);
            if($pageexists == 0){
                $post = array(
                    'post_name' => 'wp-job-portal-newest-jobs',
                    'post_title' => 'Newest Jobs',
                    'post_status' => 'publish',
                    'post_content' => '[wpjobportal_job]',
                    'post_type' => 'page'
                );
                $post_ID = wp_insert_post($post);
                if (!is_wp_error($post_ID)) {
                    WPJOBPORTAL_Sample_Data_Service::record_entity('pages', $post_ID);
                }
            }
        }
        if ($wpjobportal_insertsampledata == 1) {
            // The owners were chosen on the previous screen, and the controller
            // refused the request unless both were sound. Choosing an existing
            // WordPress user gives them the WP Job Portal profile for the role;
            // no WordPress user is ever created here.
            $wpjobportal_employer_id = 0;
            $wpjobportal_jobseeker_id = 0;
            $post_employer_id = absint(WPJOBPORTALrequest::getVar('employer_id','post'));
            $post_jobseeker_id = absint(WPJOBPORTALrequest::getVar('jobseeker_id','post'));
            if ($post_employer_id > 0) {
                $wpjobportal_owner = WPJOBPORTAL_Sample_Data_Service::portal_account_for($post_employer_id, WPJOBPORTAL_Sample_Data_Service::ROLE_EMPLOYER);
                $wpjobportal_employer_id = is_wp_error($wpjobportal_owner) ? 0 : $wpjobportal_owner;
            }
            if ($post_jobseeker_id > 0) {
                $wpjobportal_owner = WPJOBPORTAL_Sample_Data_Service::portal_account_for($post_jobseeker_id, WPJOBPORTAL_Sample_Data_Service::ROLE_JOBSEEKER);
                $wpjobportal_jobseeker_id = is_wp_error($wpjobportal_owner) ? 0 : $wpjobportal_owner;
            }

            // The records themselves come from includes/sample-data/sample-data.json,
            // sized to what this site shows; see WPJOBPORTAL_Sample_Data_Importer.
            if ($wpjobportal_employer_id > 0 && $wpjobportal_jobseeker_id > 0) {
                WPJOBPORTAL_Sample_Data_Importer::import($wpjobportal_employer_id, $wpjobportal_jobseeker_id);
            }
        }
        WPJOBPORTAL_Sample_Data_Service::finalize_batch();
        return true;
    }

    function installSampleDataTemplate($flag) {
        if(wpjobportal::$wpjobportal_theme_chk == 2){
            return $this->installSampleDataTemplateJobHub($flag);
        }
        $wpjobportal_product_type = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('producttype');
        if($flag == 'p'){
            if($wpjobportal_product_type != 'pro'){
                return 0;
            }
                $wpjobportal_pro  = 1;
        }elseif($flag == 'f'){
            if($wpjobportal_product_type != 'free'){
                return 0;
            }
            $wpjobportal_pro  = 0;
        }elseif($flag == 'ftp'){
            if($wpjobportal_product_type != 'pro'){
                return 0;
            }
            $this->installFreeToProData();
            return 1;
        }
        return 1;
    }

    function installSampleDataTemplateJobHub($flag) {
        $wpjobportal_product_type = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('producttype');
        if($flag == 'p'){
            return 0;
        }elseif($flag == 'f'){
            if($wpjobportal_product_type != 'free'){
                return 0;
            }
            $wpjobportal_pro  = 0;
        }elseif($flag == 'ftp'){
                return 0;
        }
        // Check for the rev slider
        $wp_upload_dir = wp_upload_dir();
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
        $wp_filesystem = wpjobportal::wpjobportal_filesystem();

        if ($wp_filesystem->exists(get_template_directory() . "/framework/plugins/sample-data.zip")) {
            /*
             * The demo's pictures, unpacked into uploads.
             *
             * This called PclZip directly with the uploads path exactly as
             * WordPress reports it, which on a Windows host is "D:\...\uploads".
             * PclZip reads that as a relative folder called "D:", fails with
             * PCLZIP_ERR_DIR_CREATE_FAIL, and still hands back its file list -
             * so nothing was unpacked and nothing said so. unzip_file() is the
             * WordPress call for this and copes with either kind of path.
             */
            unzip_file(get_template_directory() . "/framework/plugins/sample-data.zip", $wp_upload_dir["basedir"]);
        }
        if( ! function_exists("wpjobportal_wp_update_post_meta")){
            function wpjobportal_wp_update_post_meta( $post_id, $wpjobportal_field_name, $wpjobportal_value = "" ){
                if ( empty( $wpjobportal_value ) OR ! $wpjobportal_value ){
                    delete_post_meta( $post_id, $wpjobportal_field_name );
                }elseif ( ! get_post_meta( $post_id, $wpjobportal_field_name ) ){
                    add_post_meta( $post_id, $wpjobportal_field_name, $wpjobportal_value );
                }else{
                    update_post_meta( $post_id, $wpjobportal_field_name, $wpjobportal_value );
                }
            }
        }
        if( ! function_exists("wpjobportal_uploadPostFeatureImage")){
            function wpjobportal_uploadPostFeatureImage($filename,$parent_post_id){
                // Check the type of file. We"ll use this as the "post_mime_type".
                $filetype = wp_check_filetype( wpjobportalphplib::wpJP_basename( $filename ), null );
                // Get the path to the upload directory.
                $wp_upload_dir = wp_upload_dir();
                // Prepare an array of post data for the attachment.
                $attachment = array(
                    "guid"           => $wp_upload_dir["url"] . "/" . wpjobportalphplib::wpJP_basename( $filename ),
                    "post_mime_type" => $filetype["type"],
                    "post_title"     => wpjobportalphplib::wpJP_preg_replace(  "/\.[^.]+$/", "", wpjobportalphplib::wpJP_basename( $filename ) ),
                    "post_content"   => "",
                    "post_status"    => "inherit"
                );
                // Insert the attachment.
                $attach_id = wp_insert_attachment( $attachment, $filename, $parent_post_id );
                // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
                do_action('wpjobportal_load_wp_image');
                // Generate the metadata for the attachment, and update the database record.
                $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
                wp_update_attachment_metadata( $attach_id, $attach_data );
                set_post_thumbnail( $parent_post_id, $attach_id );
            }
        }
        $jh_pages = array();
        // Home
        $wpjobportal_new_page_title = "Home";
        $wpjobportal_new_page_template = "templates/template-homepage.php";
        $page_check = $this->wpjobportal_get_page_by_title($wpjobportal_new_page_title);
        $wpjobportal_new_page = array(
                "post_type" => "page",
                "post_title" => $wpjobportal_new_page_title,
                "post_content" => "",
                "post_status" => "publish",
                "post_author" => 1,
                "post_parent" => 0,
        );
        if(!isset($page_check->ID)){
            $jh_pages["home"] = wp_insert_post($wpjobportal_new_page);
        }else{
            $wpjobportal_new_page["post_title"] = "Job Hub ".$wpjobportal_new_page_title;
            $jh_pages["home"] = wp_insert_post($wpjobportal_new_page);
        }
        update_post_meta($jh_pages["home"], "_wp_page_template", $wpjobportal_new_page_template);
        update_post_meta($jh_pages["home"], "jh_show_header", 2);
        // Home 1
        $wpjobportal_new_page_title = "Home 1";
        $wpjobportal_new_page_template = "templates/template-homepage.php";
        $page_check = $this->wpjobportal_get_page_by_title($wpjobportal_new_page_title);
        $wpjobportal_new_page = array(
                "post_type" => "page",
                "post_title" => $wpjobportal_new_page_title,
                "post_content" => "",
                "post_status" => "publish",
                "post_author" => 1,
                "post_parent" => 0,
        );
        if(!isset($page_check->ID)){
            $jh_pages["home1"] = wp_insert_post($wpjobportal_new_page);
        }else{
            $wpjobportal_new_page["post_title"] = "Job Hub ".$wpjobportal_new_page_title;
            $jh_pages["home1"] = wp_insert_post($wpjobportal_new_page);
        }
        update_post_meta($jh_pages["home1"], "_wp_page_template", $wpjobportal_new_page_template);
        update_post_meta($jh_pages["home1"], "jh_show_header", 2);
        
        $wp_upload_dir = wp_upload_dir();
        /* end of homepages */
        // Price table
        $wpjobportal_new_page_title = "Pricing Table";
        $wpjobportal_new_page_content = '<div class="jsjb-jh-pricicing-box-wrap"><div class="jsjb-jh-price-box-row"><div class="col-md-4"><div class="jsjb-jh-price-box jsjb-jh-pkg-color-1"><div class="jsjb-jh-price-box-heading"><h3 class="jsjb-jh-price-box-heading-txt">Basic Package</h3><h5 class="jsjb-jh-price-box-title">Basic Package</h5></div><ul class="list-group jsjb-jh-price-box-crdts-list"> <li>Credits</li><li>50,000</li><li>Expire In 60 Days</li></ul><div class="jsjb-jh-price-box-price-wrp">$200</div><a class="jsjb-jh-price-box-bn-btn-txt" title="Buy Now" href="#">Buy Now</a></div></div><div class="col-md-4"><div class="jsjb-jh-price-box jsjb-jh-pkg-color-2"><div class="jsjb-jh-price-box-heading"><h3 class="jsjb-jh-price-box-heading-txt">Basic Package</h3><h5 class="jsjb-jh-price-box-title">Basic Package</h5></div><ul class="list-group jsjb-jh-price-box-crdts-list"> <li>Credits</li><li>50,000</li><li>Expire In 60 Days</li></ul><div class="jsjb-jh-price-box-price-wrp">$200</div><a class="jsjb-jh-price-box-bn-btn-txt" title="Buy Now" href="#">Buy Now</a></div></div><div class="col-md-4"><div class="jsjb-jh-price-box jsjb-jh-pkg-color-3"><div class="jsjb-jh-price-box-heading"><h3 class="jsjb-jh-price-box-heading-txt">Basic Package</h3><h5 class="jsjb-jh-price-box-title">Basic Package</h5></div><ul class="list-group jsjb-jh-price-box-crdts-list"> <li>Credits</li><li>50,000</li><li>Expire In 60 Days</li></ul><div class="jsjb-jh-price-box-price-wrp">$200</div><a class="jsjb-jh-price-box-bn-btn-txt" title="Buy Now" href="#">Buy Now</a></div></div><div class="col-md-4"><div class="jsjb-jh-price-box jsjb-jh-pkg-color-4"><div class="jsjb-jh-price-box-heading"><h3 class="jsjb-jh-price-box-heading-txt">Basic Package</h3><h5 class="jsjb-jh-price-box-title">Basic Package</h5></div><ul class="list-group jsjb-jh-price-box-crdts-list"> <li>Credits</li><li>50,000</li><li>Expire In 60 Days</li></ul><div class="jsjb-jh-price-box-price-wrp">$200</div><a class="jsjb-jh-price-box-bn-btn-txt" title="Buy Now" href="#">Buy Now</a></div></div><div class="col-md-4"><div class="jsjb-jh-price-box jsjb-jh-pkg-color-5"><div class="jsjb-jh-price-box-heading"><h3 class="jsjb-jh-price-box-heading-txt">Basic Package</h3><h5 class="jsjb-jh-price-box-title">Basic Package</h5></div><ul class="list-group jsjb-jh-price-box-crdts-list"> <li>Credits</li><li>50,000</li><li>Expire In 60 Days</li></ul><div class="jsjb-jh-price-box-price-wrp">$200</div><a class="jsjb-jh-price-box-bn-btn-txt" title="Buy Now" href="#">Buy Now</a></div></div><div class="col-md-4"><div class="jsjb-jh-price-box jsjb-jh-pkg-color-6"><div class="jsjb-jh-price-box-heading"><h3 class="jsjb-jh-price-box-heading-txt">Basic Package</h3><h5 class="jsjb-jh-price-box-title">Basic Package</h5></div><ul class="list-group jsjb-jh-price-box-crdts-list"> <li>Credits</li><li>50,000</li><li>Expire In 60 Days</li></ul><div class="jsjb-jh-price-box-price-wrp">$200</div><a class="jsjb-jh-price-box-bn-btn-txt" title="Buy Now" href="#">Buy Now</a></div></div><div class="col-md-4"><div class="jsjb-jh-price-box jsjb-jh-pkg-color-7"><div class="jsjb-jh-price-box-heading"><h3 class="jsjb-jh-price-box-heading-txt">Basic Package</h3><h5 class="jsjb-jh-price-box-title">Basic Package</h5></div><ul class="list-group jsjb-jh-price-box-crdts-list"> <li>Credits</li><li>50,000</li><li>Expire In 60 Days</li></ul><div class="jsjb-jh-price-box-price-wrp">$200</div><a class="jsjb-jh-price-box-bn-btn-txt" title="Buy Now" href="#">Buy Now</a></div></div><div class="col-md-4"><div class="jsjb-jh-price-box jsjb-jh-pkg-color-8"><div class="jsjb-jh-price-box-heading"><h3 class="jsjb-jh-price-box-heading-txt">Basic Package</h3><h5 class="jsjb-jh-price-box-title">Basic Package</h5></div><ul class="list-group jsjb-jh-price-box-crdts-list"> <li>Credits</li><li>50,000</li><li>Expire In 60 Days</li></ul><div class="jsjb-jh-price-box-price-wrp">$200</div><a class="jsjb-jh-price-box-bn-btn-txt" title="Buy Now" href="#">Buy Now</a></div></div><div class="col-md-4"><div class="jsjb-jh-price-box jsjb-jh-pkg-color-9"><div class="jsjb-jh-price-box-heading"><h3 class="jsjb-jh-price-box-heading-txt">Basic Package</h3><h5 class="jsjb-jh-price-box-title">Basic Package</h5></div><ul class="list-group jsjb-jh-price-box-crdts-list"> <li>Credits</li><li>50,000</li><li>Expire In 60 Days</li></ul><div class="jsjb-jh-price-box-price-wrp">$200</div><a class="jsjb-jh-price-box-bn-btn-txt" title="Buy Now" href="#">Buy Now</a></div></div></div></div>';
        $wpjobportal_new_page_template = "templates/template-fullwidth.php";
        $page_check = $this->wpjobportal_get_page_by_title($wpjobportal_new_page_title);
        $wpjobportal_new_page = array(
                "post_type" => "page",
                "post_title" => $wpjobportal_new_page_title,
                "post_content" => $wpjobportal_new_page_content,
                "post_status" => "publish",
                "post_author" => 1,
                "post_parent" => 0,
        );
        if(!isset($page_check->ID)){
            $jh_pages["pricing_table"] = wp_insert_post($wpjobportal_new_page);
        }else{
            $wpjobportal_new_page["post_title"] = "Job Hub ".$wpjobportal_new_page_title;
            $jh_pages["pricing_table"] = wp_insert_post($wpjobportal_new_page);
        }
        update_post_meta($jh_pages["pricing_table"], "jh_show_header", 1);
        update_post_meta($jh_pages["pricing_table"], "_wp_page_template", $wpjobportal_new_page_template);
    // job hub pages
        $page_array[1] = "Jobseeker Control Panel";
        $page_array[2] = "Newest Jobs";
        $page_array[3] = "My Applied Jobs";
        $page_array[4] = "My Resume";
        $page_array[5] = "Search Job";
        $page_array[6] = "Jobs By Category";
        $page_array[8] = "Add Resume";
        $page_array[11] = "All Companies";

        $page_array[18] = "Jobseeker Stats";
        $page_array[19] = "Employer Control Panel";
        $page_array[20] = "My Jobs";
        $page_array[21] = "Add Job";
        $page_array[22] = "Resume Search";
        $page_array[23] = "Resume By Categories";
        $page_array[24] = "My Companies";
        $page_array[25] = "Add Company";
        $page_array[32] = "Employer Stats";
        $page_array[33] = "Login";
        $page_array[34] = "Employer Registration";
        $page_array[35] = "Jobseeker Registration";
        $page_array[36] = "Thank You";
        foreach ($page_array as $wpjobportal_key => $wpjobportal_value) {
            // $wpjobportal_value_string = wpjobportalphplib::wpJP_strtolower($wpjobportal_value);
            // $wpjobportal_value_string = sanitize_title($wpjobportal_value_string);
            $wpjobportal_value_string = wpjobportalphplib::wpJP_strtolower($wpjobportal_value);
            $wpjobportal_value_string = wpjobportalphplib::wpJP_str_replace(" ","_",$wpjobportal_value_string);
            $wpjobportal_new_page_title = $wpjobportal_value;
            $wpjobportal_new_page_content = '[vc_row][vc_column][jh_job_hub_pages page page="'.$wpjobportal_key.'"][/vc_column][/vc_row]';
            $wpjobportal_new_page_template = "templates/template-fullwidth.php";
            $page_check = $this->wpjobportal_get_page_by_title($wpjobportal_new_page_title);
            $wpjobportal_new_page = array(
                    "post_type" => "page",
                    "post_title" => $wpjobportal_new_page_title,
                    "post_content" => $wpjobportal_new_page_content,
                    "post_status" => "publish",
                    "post_author" => 1,
                    "post_parent" => 0,
            );
            if(!isset($page_check->ID)){
                $jh_pages[$wpjobportal_value_string] = wp_insert_post($wpjobportal_new_page);
            }else{
                $wpjobportal_new_page["post_title"] = "Job Hub ".$wpjobportal_new_page_title;
                $jh_pages[$wpjobportal_value_string] = wp_insert_post($wpjobportal_new_page);
            }
            update_post_meta($jh_pages[$wpjobportal_value_string], "jh_show_header", 1);
            update_post_meta($jh_pages[$wpjobportal_value_string], "_wp_page_template", $wpjobportal_new_page_template);
        }
    // job hub pages end
        // News & Rumors
        $wpjobportal_new_page_title = "News & Rumors";
        $wpjobportal_new_page_content = '[vc_row][vc_column][jh_news_and_rumors style="3" posts_per_page="2"][/vc_column][/vc_row]';
        $wpjobportal_new_page_template = "templates/template-news_and_rumors.php";
        $page_check = $this->wpjobportal_get_page_by_title($wpjobportal_new_page_title);
        $wpjobportal_new_page = array(
                "post_type" => "page",
                "post_title" => $wpjobportal_new_page_title,
                "post_content" => $wpjobportal_new_page_content,
                "post_status" => "publish",
                "post_author" => 1,
                "post_parent" => 0,
        );
        if(!isset($page_check->ID)){
            $jh_pages["news_and_rumors"] = wp_insert_post($wpjobportal_new_page);
        }else{
            $wpjobportal_new_page["post_title"] = "Job Hub ".$wpjobportal_new_page_title;
            $jh_pages["news_and_rumors"] = wp_insert_post($wpjobportal_new_page);
        }
        update_post_meta($jh_pages["news_and_rumors"], "jh_show_header", 1);
        update_post_meta($jh_pages["news_and_rumors"], "_wp_page_template", $wpjobportal_new_page_template);
        // FAQ
        $wpjobportal_new_page_title = "FAQ";
        $wpjobportal_new_page_content = '[vc_row][vc_column][vc_tta_accordion active_section="1" el_class="jsjb-jh-faq-wrap"][vc_tta_section title="Lorem Ipsum is simply dummy text of the printing and typesetting industry." tab_id="1482754286724-c9ed48ed-c7f7" el_class="jsjb-jh-faq"][vc_column_text]I am text block. Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.[/vc_column_text][/vc_tta_section][vc_tta_section title="Lorem Ipsum is simply dummy text of the printing and typesetting industry." tab_id="1482754537433-695717d6-6bfc" el_class="jsjb-jh-faq"][vc_column_text]I am text block. Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.[/vc_column_text][/vc_tta_section][vc_tta_section title="Lorem Ipsum is simply dummy text of the printing and typesetting industry." tab_id="1482754536332-73927004-0a28" el_class="jsjb-jh-faq"][vc_column_text]I am text block. Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.[/vc_column_text][/vc_tta_section][vc_tta_section title="Lorem Ipsum is simply dummy text of the printing and typesetting industry." tab_id="1482754535606-7dffd7a9-2119" el_class="jsjb-jh-faq"][vc_column_text]I am text block. Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.[/vc_column_text][/vc_tta_section][vc_tta_section title="Lorem Ipsum is simply dummy text of the printing and typesetting industry." tab_id="1482754534808-bb9dfb79-6d18" el_class="jsjb-jh-faq"][vc_column_text]I am text block. Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.[/vc_column_text][/vc_tta_section][vc_tta_section title="Lorem Ipsum is simply dummy text of the printing and typesetting industry." tab_id="1482754534095-ec21098b-4397" el_class="jsjb-jh-faq"][vc_column_text]I am text block. Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.[/vc_column_text][/vc_tta_section][vc_tta_section title="Lorem Ipsum is simply dummy text of the printing and typesetting industry." tab_id="1482754529612-21aedc15-ddd9" el_class="jsjb-jh-faq"][vc_column_text]I am text block. Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.[/vc_column_text][/vc_tta_section][vc_tta_section title="Lorem Ipsum is simply dummy text of the printing and typesetting industry." tab_id="1482754528056-93a9c873-2efd" el_class="jsjb-jh-faq"][vc_column_text]I am text block. Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.[/vc_column_text][/vc_tta_section][/vc_tta_accordion][/vc_column][/vc_row]';
        $wpjobportal_new_page_template = "templates/template-fullwidth.php";
        $page_check = $this->wpjobportal_get_page_by_title($wpjobportal_new_page_title);
        $wpjobportal_new_page = array(
                "post_type" => "page",
                "post_title" => $wpjobportal_new_page_title,
                "post_content" => $wpjobportal_new_page_content,
                "post_status" => "publish",
                "post_author" => 1,
                "post_parent" => 0,
        );
        if(!isset($page_check->ID)){
            $jh_pages["faq"] = wp_insert_post($wpjobportal_new_page);
        }else{
            $wpjobportal_new_page["post_title"] = "Job Hub ".$wpjobportal_new_page_title;
            $jh_pages["faq"] = wp_insert_post($wpjobportal_new_page);
        }
        update_post_meta($jh_pages["faq"], "jh_show_header", 1);
        update_post_meta($jh_pages["faq"], "_wp_page_template", $wpjobportal_new_page_template);
       // Our Team
        $wpjobportal_new_page_title = "Our Team";
        $wpjobportal_new_page_content = '[vc_row][vc_column][jh_team_memebers per_row="3" posts_per_page="4" style="3"][/vc_column][/vc_row]';
        $wpjobportal_new_page_template = "templates/template-fullwidth.php";
        $page_check = $this->wpjobportal_get_page_by_title($wpjobportal_new_page_title);
        $wpjobportal_new_page = array(
                "post_type" => "page",
                "post_title" => $wpjobportal_new_page_title,
                "post_content" => $wpjobportal_new_page_content,
                "post_status" => "publish",
                "post_author" => 1,
                "post_parent" => 0,
        );
        if(!isset($page_check->ID)){
            $jh_pages["ourteam"] = wp_insert_post($wpjobportal_new_page);
        }else{
            $wpjobportal_new_page["post_title"] = "Job Hub ".$wpjobportal_new_page_title;
            $jh_pages["ourteam"] = wp_insert_post($wpjobportal_new_page);
        }
        update_post_meta($jh_pages["ourteam"], "_wp_page_template", $wpjobportal_new_page_template);
        update_post_meta($jh_pages["ourteam"], "jh_show_header", 1);
        // Contact Us
        $wpjobportal_new_page_title = "Contact Us";
        $wpjobportal_new_page_content = "";
        $wpjobportal_new_page_template = "templates/template-contactus.php";
        $page_check = $this->wpjobportal_get_page_by_title($wpjobportal_new_page_title);
        $wpjobportal_new_page = array(
                "post_type" => "page",
                "post_title" => $wpjobportal_new_page_title,
                "post_content" => $wpjobportal_new_page_content,
                "post_status" => "publish",
                "post_author" => 1,
                "post_parent" => 0,
        );
        if(!isset($page_check->ID)){
            $jh_pages["contact_us"] = wp_insert_post($wpjobportal_new_page);
        }else{
            $wpjobportal_new_page["post_title"] = "Job Hub ".$wpjobportal_new_page_title;
            $jh_pages["contact_us"] = wp_insert_post($wpjobportal_new_page);
        }
        update_post_meta($jh_pages["contact_us"], "_wp_page_template", $wpjobportal_new_page_template);
        update_post_meta($jh_pages["contact_us"], "jh_show_header", 1);
        // Blog Page
        $wpjobportal_new_page_title = "Blog";
        $wpjobportal_new_page_content = "";
        $page_check = $this->wpjobportal_get_page_by_title($wpjobportal_new_page_title);
        $wpjobportal_new_page = array(
                "post_type" => "page",
                "post_title" => $wpjobportal_new_page_title,
                "post_content" => $wpjobportal_new_page_content,
                "post_status" => "publish",
                "post_author" => 1,
                "post_parent" => 0,
        );

        if(!isset($page_check->ID)){
            $jh_pages["blog"] = wp_insert_post($wpjobportal_new_page);
        }else{
            $wpjobportal_new_page["post_title"] = "Job Hub ".$wpjobportal_new_page_title;
            $jh_pages["blog"] = wp_insert_post($wpjobportal_new_page);
        }
        update_option("page_for_posts", $jh_pages["blog"]);
    // Update home page contents
        //Home
        $wpjobportal_new_page_content = '[vc_row][vc_column][jh_job_search wpjobportalpageid="'.esc_attr($jh_pages["newest_jobs"]).'" style="7" subtitle="Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text"][/vc_column][/vc_row][vc_row][vc_column][jh_feature_3box][/vc_column][/vc_row][vc_row][vc_column][jh_jobs_category wpjobportalpageid="'.esc_attr($jh_pages["newest_jobs"]).'" style="2" category="1" category1="1" category2="1" category3="1" category4="1" category5="1" category6="1" category7="1"][/vc_column][/vc_row][vc_row][vc_column][jh_5count_box style="2" count1="587" count2="146" count3="919" count4="796"][/vc_column][/vc_row][vc_row][vc_column][jh_jobs wpjobportalpageid="'.esc_attr($jh_pages["newest_jobs"]).'" title1="Latest Jobs" noofjobs="6" style="2"][/vc_column][/vc_row][vc_row][vc_column][jh_job_hub_custom_link wpjobportalpageid="'.esc_attr($jh_pages["newest_jobs"]).'" style="6" title="REGISTER AS EMPLOYER" description="Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut veniam ." description1="Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut veniam ."][/vc_column][/vc_row][vc_row][vc_column][jh_price_tables style="2" posts_per_page="3"][/vc_column][/vc_row][vc_row][vc_column][jh_companies wpjobportalpageid="'.esc_attr($jh_pages["newest_jobs"]).'" companytype="1" style="1" bgcolor="#ffffff" title="Popular Companies" scrollstyle="2" speed="1" posts_per_page="10"][/vc_column][/vc_row][vc_row][vc_column][jh_shortdescription_with_btn][/vc_column][/vc_row]';
        $wpjobportal_my_post = array(
            "ID"           => $jh_pages["home"],
            "post_content" => $wpjobportal_new_page_content,
        );
        wp_update_post( $wpjobportal_my_post );
        //Home 1
        $wpjobportal_new_page_content = '[vc_row][vc_column][jh_job_search wpjobportalpageid="'.esc_attr($jh_pages["newest_jobs"]).'" style="8"][/vc_column][/vc_row][vc_row][vc_column][jh_latest_featured_jobs wpjobportalpageid="'.esc_attr($jh_pages["newest_jobs"]).'" title="Latest Jobs" heading="Featured Jobs" noofjobs="6"][/vc_column][/vc_row][vc_row][vc_column][jh_job_hub_custom_link wpjobportalpageid="'.esc_attr($jh_pages["newest_jobs"]).'" style="7" description="Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo"][/vc_column][/vc_row][vc_row][vc_column][jh_latest_resume wpjobportalpageid="'.esc_attr($jh_pages["newest_jobs"]).'" style="5" title="Latest Resume" description="Here you can see most recent resume added by users.You are able to set how many resume will be displayed ,order them or chose the specific taxonomy terms of resume." post_per_page="5" scrollstyle="2" speed="1"][/vc_column][/vc_row][vc_row][vc_column][jh_5count_box style="6" count1="587" count2="146" count3="919" count4="796"][/vc_column][/vc_row][vc_row][vc_column][jh_job_hub_custom_link wpjobportalpageid="'.esc_attr($jh_pages["newest_jobs"]).'" style="8" description="Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo"][/vc_column][/vc_row][vc_row][vc_column][jh_news_and_rumors style="1"][/vc_column][/vc_row][vc_row][vc_column][jh_companies wpjobportalpageid="'.esc_attr($jh_pages["newest_jobs"]).'" companytype="1" style="2" title="Popular Companies" posts_per_page="6"][/vc_column][/vc_row][vc_row][vc_column][jh_shortdescription_with_btn][/vc_column][/vc_row]';
        $wpjobportal_my_post = array(
            "ID"           => $jh_pages["home1"],
            "post_content" => $wpjobportal_new_page_content,
        );
        wp_update_post( $wpjobportal_my_post );
        //Home 2
        $wpjobportal_new_page_content = '[vc_row video_bg="yes"][vc_column][vc_empty_space height="550px"][/vc_column][/vc_row][vc_row][vc_column][jh_job_search wpjobportalpageid="'.esc_attr($jh_pages["newest_jobs"]).'" style="2"][/vc_column][/vc_row][vc_row][vc_column][jh_latest_jobs_types_categories_cities wpjobportalpageid="'.esc_attr($jh_pages["newest_jobs"]).'" title="Latest Jobs" noofjobs="5" display_style="1" show_jobs_by_types="1" number_of_job_type="5" show_number_of_jobs_by_type="1" show_jobs_by_categories="1" number_of_categories="5" show_number_of_jobs_by_category="1" show_jobs_by_cities="1" number_of_cities="5" show_number_of_jobs_by_city="1"][/vc_column][/vc_row][vc_row][vc_column][jh_image_and_text_box_with_links][/vc_column][/vc_row][vc_row][vc_column][jh_latest_resume wpjobportalpageid="'.esc_attr($jh_pages["newest_jobs"]).'" style="4" title="Latest Resume" description="Here you can see most recent resume added by users.You are able to set how many resume will be displayed ,order them or chose the specific taxonomy terms of resume." post_per_page="5"][/vc_column][/vc_row][vc_row][vc_column][jh_news_and_rumors style="4"][/vc_column][/vc_row][vc_row][vc_column][jh_testimonial style="4" heading="Kind Words From Happy Candidates" description="Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et ."][/vc_column][/vc_row][vc_row el_class="jsjb-jh-companies-carasole-without-bg"][vc_column][jh_companies wpjobportalpageid="'.esc_attr($jh_pages["newest_jobs"]).'" companytype="1" style="2" title="Popular Companies" posts_per_page="6"][/vc_column][/vc_row][vc_row][vc_column][jh_post_add style="1"][/vc_column][/vc_row]';
        $wpjobportal_my_post = array(
            "ID"           => $jh_pages["home2"],
            "post_content" => $wpjobportal_new_page_content,
        );
        wp_update_post( $wpjobportal_my_post );
        // Update WP Options
        $wp_page_array = array();
        foreach ($page_array as $wpjobportal_key => $wpjobportal_value) {
            $wpjobportal_value_string = wpjobportalphplib::wpJP_strtolower($wpjobportal_value);
            $wpjobportal_value_string = wpjobportalphplib::wpJP_str_replace(" ","_",$wpjobportal_value_string);
            $wp_page_array[$wpjobportal_value_string] = $jh_pages[$wpjobportal_value_string];
        }
        update_option("job-hub-layout", $wp_page_array);
        // ----------------Posts -------- //
        $wpjobportal_new_page_title = "Lorem ipsum dolor sit amet, consectetur adipiscing.";
        $wpjobportal_new_page_content = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus sed sapien non elit rhoncus faucibus id mattis metus. Integer in dictum lectus. Cras et risus leo. Morbi viverra congue sem vel posuere. Aenean odio turpis, posuere ac sem id, posuere viverra nisi. Integer pellentesque ornare tortor, ut suscipit leo sagittis vitae. In eu porta nisi. In id odio non risus blandit ultricies ut aliquam ex.Curabitur at ante pulvinar, mattis ipsum sit amet, aliquam turpis. Vivamus et sem mollis, ornare odio nec, consequat leo. Quisque commodo eget velit vitae sagittis. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Maecenas cursus augue at enim fringilla, eget egestas risus hendrerit. In hac habitasse platea dictumst. Nulla vitae enim id odio porttitor mollis. In vehicula finibus eleifend. Aenean gravida, nisl ac dapibus tincidunt, mi orci pretium sapien, eu varius magna nunc egestas metus.Vivamus vitae rhoncus mi, vel ultrices dolor. Mauris vitae ex laoreet, sagittis dolor id, commodo mauris. Integer pellentesque mi non dictum vehicula. Sed a elit velit. Suspendisse vel justo sed enim gravida iaculis. Nulla pretium a odio non convallis. Donec lacus lectus, ultrices vel elit vel, auctor laoreet odio. Donec velit est, consectetur ac condimentum eu, scelerisque in elit. Sed ultricies quis enim id congue. Aliquam nec aliquam urna. Ut iaculis vel purus nec pellentesque. Proin quis lorem eros. Praesent lacinia id sapien sed elementum. Fusce sodales nisl orci, ac venenatis erat tincidunt faucibus. Cras elementum efficitur lorem eu pellentesque. Donec efficitur fringilla arcu ac.  ';
        $wpjobportal_new_page = array(
                "post_type" => "post",
                "post_title" => $wpjobportal_new_page_title,
                "post_content" => $wpjobportal_new_page_content,
                "post_status" => "publish",
                "post_author" => 1,
                "post_parent" => 0,
        );
        $wpjobportal_new_page_id = wp_insert_post($wpjobportal_new_page);
        $wp_upload_dir = wp_upload_dir();
        $filename = $wp_upload_dir["basedir"]."/2017/01/post2.jpg";
        $parent_post_id = $wpjobportal_new_page_id;
        wpjobportal_uploadPostFeatureImage($filename,$parent_post_id);

        $wpjobportal_new_page_title = "Lorem ipsum dolor sit";
        $wpjobportal_new_page_content = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus sed sapien non elit rhoncus faucibus id mattis metus. Integer in dictum lectus. Cras et risus leo. Morbi viverra congue sem vel posuere. Aenean odio turpis, posuere ac sem id, posuere viverra nisi. Integer pellentesque ornare tortor, ut suscipit leo sagittis vitae. In eu porta nisi. In id odio non risus blandit ultricies ut aliquam ex.Curabitur at ante pulvinar, mattis ipsum sit amet, aliquam turpis. Vivamus et sem mollis, ornare odio nec, consequat leo. Quisque commodo eget velit vitae sagittis. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Maecenas cursus augue at enim fringilla, eget egestas risus hendrerit. In hac habitasse platea dictumst. Nulla vitae enim id odio porttitor mollis. In vehicula finibus eleifend. Aenean gravida, nisl ac dapibus tincidunt, mi orci pretium sapien, eu varius magna nunc egestas metus.Vivamus vitae rhoncus mi, vel ultrices dolor. Mauris vitae ex laoreet, sagittis dolor id, commodo mauris. Integer pellentesque mi non dictum vehicula. Sed a elit velit. Suspendisse vel justo sed enim gravida iaculis. Nulla pretium a odio non convallis. Donec lacus lectus, ultrices vel elit vel, auctor laoreet odio. Donec velit est, consectetur ac condimentum eu, scelerisque in elit. Sed ultricies quis enim id congue. Aliquam nec aliquam urna. Ut iaculis vel purus nec pellentesque. Proin quis lorem eros. Praesent lacinia id sapien sed elementum. Fusce sodales nisl orci, ac venenatis erat tincidunt faucibus. Cras elementum efficitur lorem eu pellentesque. Donec efficitur fringilla arcu ac.  ';
        $wpjobportal_new_page = array(
                "post_type" => "post",
                "post_title" => $wpjobportal_new_page_title,
                "post_content" => $wpjobportal_new_page_content,
                "post_status" => "publish",
                "post_author" => 1,
                "post_parent" => 0,
        );
        $wpjobportal_new_page_id = wp_insert_post($wpjobportal_new_page);
        $wp_upload_dir = wp_upload_dir();
        $filename = $wp_upload_dir["basedir"]."/2017/01/post3.jpg";
        $parent_post_id = $wpjobportal_new_page_id;
        wpjobportal_uploadPostFeatureImage($filename,$parent_post_id);

        $wpjobportal_new_page_title = "Lorem ipsum dolor sit amet, consectetur";
        $wpjobportal_new_page_content = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus sed sapien non elit rhoncus faucibus id mattis metus. Integer in dictum lectus. Cras et risus leo. Morbi viverra congue sem vel posuere. Aenean odio turpis, posuere ac sem id, posuere viverra nisi. Integer pellentesque ornare tortor, ut suscipit leo sagittis vitae. In eu porta nisi. In id odio non risus blandit ultricies ut aliquam ex.Curabitur at ante pulvinar, mattis ipsum sit amet, aliquam turpis. Vivamus et sem mollis, ornare odio nec, consequat leo. Quisque commodo eget velit vitae sagittis. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Maecenas cursus augue at enim fringilla, eget egestas risus hendrerit. In hac habitasse platea dictumst. Nulla vitae enim id odio porttitor mollis. In vehicula finibus eleifend. Aenean gravida, nisl ac dapibus tincidunt, mi orci pretium sapien, eu varius magna nunc egestas metus.Vivamus vitae rhoncus mi, vel ultrices dolor. Mauris vitae ex laoreet, sagittis dolor id, commodo mauris. Integer pellentesque mi non dictum vehicula. Sed a elit velit. Suspendisse vel justo sed enim gravida iaculis. Nulla pretium a odio non convallis. Donec lacus lectus, ultrices vel elit vel, auctor laoreet odio. Donec velit est, consectetur ac condimentum eu, scelerisque in elit. Sed ultricies quis enim id congue. Aliquam nec aliquam urna. Ut iaculis vel purus nec pellentesque. Proin quis lorem eros. Praesent lacinia id sapien sed elementum. Fusce sodales nisl orci, ac venenatis erat tincidunt faucibus. Cras elementum efficitur lorem eu pellentesque. Donec efficitur fringilla arcu ac.  ';
        $wpjobportal_new_page = array(
                "post_type" => "post",
                "post_title" => $wpjobportal_new_page_title,
                "post_content" => $wpjobportal_new_page_content,
                "post_status" => "publish",
                "post_author" => 1,
                "post_parent" => 0,
        );
        $wpjobportal_new_page_id = wp_insert_post($wpjobportal_new_page);
        $wp_upload_dir = wp_upload_dir();
        $filename = $wp_upload_dir["basedir"]."/2017/01/post1.jpg";
        $parent_post_id = $wpjobportal_new_page_id;
        wpjobportal_uploadPostFeatureImage($filename,$parent_post_id);

        $wpjobportal_new_page_title = "Lorem ipsum dolor sit amet, consectetur adipiscing elit.";
        $wpjobportal_new_page_content = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus sed sapien non elit rhoncus faucibus id mattis metus. Integer in dictum lectus. Cras et risus leo. Morbi viverra congue sem vel posuere. Aenean odio turpis, posuere ac sem id, posuere viverra nisi. Integer pellentesque ornare tortor, ut suscipit leo sagittis vitae. In eu porta nisi. In id odio non risus blandit ultricies ut aliquam ex.Curabitur at ante pulvinar, mattis ipsum sit amet, aliquam turpis. Vivamus et sem mollis, ornare odio nec, consequat leo. Quisque commodo eget velit vitae sagittis. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Maecenas cursus augue at enim fringilla, eget egestas risus hendrerit. In hac habitasse platea dictumst. Nulla vitae enim id odio porttitor mollis. In vehicula finibus eleifend. Aenean gravida, nisl ac dapibus tincidunt, mi orci pretium sapien, eu varius magna nunc egestas metus.Vivamus vitae rhoncus mi, vel ultrices dolor. Mauris vitae ex laoreet, sagittis dolor id, commodo mauris. Integer pellentesque mi non dictum vehicula. Sed a elit velit. Suspendisse vel justo sed enim gravida iaculis. Nulla pretium a odio non convallis. Donec lacus lectus, ultrices vel elit vel, auctor laoreet odio. Donec velit est, consectetur ac condimentum eu, scelerisque in elit. Sed ultricies quis enim id congue. Aliquam nec aliquam urna. Ut iaculis vel purus nec pellentesque. Proin quis lorem eros. Praesent lacinia id sapien sed elementum. Fusce sodales nisl orci, ac venenatis erat tincidunt faucibus. Cras elementum efficitur lorem eu pellentesque. Donec efficitur fringilla arcu ac.  ';
        $wpjobportal_new_page = array(
                "post_type" => "post",
                "post_title" => $wpjobportal_new_page_title,
                "post_content" => $wpjobportal_new_page_content,
                "post_status" => "publish",
                "post_author" => 1,
                "post_parent" => 0,
        );
        $wpjobportal_new_page_id = wp_insert_post($wpjobportal_new_page);
        $wp_upload_dir = wp_upload_dir();
        $filename = $wp_upload_dir["basedir"]."/2017/01/post5.jpg";
        $parent_post_id = $wpjobportal_new_page_id;
        wpjobportal_uploadPostFeatureImage($filename,$parent_post_id);

        $wpjobportal_new_page_title = "Lorem ipsum dolor sit amet";
        $wpjobportal_new_page_content = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus sed sapien non elit rhoncus faucibus id mattis metus. Integer in dictum lectus. Cras et risus leo. Morbi viverra congue sem vel posuere. Aenean odio turpis, posuere ac sem id, posuere viverra nisi. Integer pellentesque ornare tortor, ut suscipit leo sagittis vitae. In eu porta nisi. In id odio non risus blandit ultricies ut aliquam ex.Curabitur at ante pulvinar, mattis ipsum sit amet, aliquam turpis. Vivamus et sem mollis, ornare odio nec, consequat leo. Quisque commodo eget velit vitae sagittis. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Maecenas cursus augue at enim fringilla, eget egestas risus hendrerit. In hac habitasse platea dictumst. Nulla vitae enim id odio porttitor mollis. In vehicula finibus eleifend. Aenean gravida, nisl ac dapibus tincidunt, mi orci pretium sapien, eu varius magna nunc egestas metus.Vivamus vitae rhoncus mi, vel ultrices dolor. Mauris vitae ex laoreet, sagittis dolor id, commodo mauris. Integer pellentesque mi non dictum vehicula. Sed a elit velit. Suspendisse vel justo sed enim gravida iaculis. Nulla pretium a odio non convallis. Donec lacus lectus, ultrices vel elit vel, auctor laoreet odio. Donec velit est, consectetur ac condimentum eu, scelerisque in elit. Sed ultricies quis enim id congue. Aliquam nec aliquam urna. Ut iaculis vel purus nec pellentesque. Proin quis lorem eros. Praesent lacinia id sapien sed elementum. Fusce sodales nisl orci, ac venenatis erat tincidunt faucibus. Cras elementum efficitur lorem eu pellentesque. Donec efficitur fringilla arcu ac.  ';
        $wpjobportal_new_page = array(
                "post_type" => "post",
                "post_title" => $wpjobportal_new_page_title,
                "post_content" => $wpjobportal_new_page_content,
                "post_status" => "publish",
                "post_author" => 1,
                "post_parent" => 0,
        );
        $wpjobportal_new_page_id = wp_insert_post($wpjobportal_new_page);
        $wp_upload_dir = wp_upload_dir();
        $filename = $wp_upload_dir["basedir"]."/2017/01/post4.jpg";
        $parent_post_id = $wpjobportal_new_page_id;
        wpjobportal_uploadPostFeatureImage($filename,$parent_post_id);

        // ----------------Custom posts -------- //
        // News & Rumors
        $wpjobportal_new_page_title = "Advertising For Your Business";
        $wpjobportal_new_page_content = 'It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using "Content here, content here", making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for "lorem ipsum" will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).';
        $wpjobportal_new_page = array(
                "post_type" => "jh_news_and_rumors",
                "post_title" => $wpjobportal_new_page_title,
                "post_content" => $wpjobportal_new_page_content,
                "post_status" => "publish",
                "post_author" => 1,
                "post_parent" => 0,
        );
        $wpjobportal_new_page_id = wp_insert_post($wpjobportal_new_page);
        $wp_upload_dir = wp_upload_dir();
        $filename = $wp_upload_dir["basedir"]."/2017/01/nar_1.png";
        $parent_post_id = $wpjobportal_new_page_id;
        wpjobportal_uploadPostFeatureImage($filename,$parent_post_id);

        $wpjobportal_new_page_title = "Attract More Attention Sales";
        $wpjobportal_new_page_content = '<strong>Lorem Ipsum</strong> is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry"s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.<strong></strong>';
        $page_check = $this->wpjobportal_get_page_by_title($wpjobportal_new_page_title);
        $wpjobportal_new_page = array(
                "post_type" => "jh_news_and_rumors",
                "post_title" => $wpjobportal_new_page_title,
                "post_content" => $wpjobportal_new_page_content,
                "post_status" => "publish",
                "post_author" => 1,
                "post_parent" => 0,
        );
        $wpjobportal_new_page_id = wp_insert_post($wpjobportal_new_page);
        $filename = $wp_upload_dir["basedir"]."/2017/01/nar_2.png";
        $parent_post_id = $wpjobportal_new_page_id;
        wpjobportal_uploadPostFeatureImage($filename,$parent_post_id);

        $wpjobportal_new_page_title = "Top fun activities tips for you ";
        $wpjobportal_new_page_content = '<strong>Lorem Ipsum</strong> is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry"s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.';
        $page_check = $this->wpjobportal_get_page_by_title($wpjobportal_new_page_title);
        $wpjobportal_new_page = array(
                "post_type" => "jh_news_and_rumors",
                "post_title" => $wpjobportal_new_page_title,
                "post_content" => $wpjobportal_new_page_content,
                "post_status" => "publish",
                "post_author" => 1,
                "post_parent" => 0,
        );
        $wpjobportal_new_page_id = wp_insert_post($wpjobportal_new_page);
        $filename = $wp_upload_dir["basedir"]."/2017/01/nar_3.png";
        $parent_post_id = $wpjobportal_new_page_id;
        wpjobportal_uploadPostFeatureImage($filename,$parent_post_id);


        // Team members
        $wpjobportal_new_page_title = "Member 4";
        $wpjobportal_new_page_content = "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.";
        $wpjobportal_new_page = array(
                "post_type" => "jh_team_member",
                "post_title" => $wpjobportal_new_page_title,
                "post_content" => $wpjobportal_new_page_content,
                "post_status" => "publish",
                "post_author" => 1,
                "post_parent" => 0,
        );
        $wpjobportal_new_page_id = wp_insert_post($wpjobportal_new_page);
        update_post_meta($wpjobportal_new_page_id, "team_member_title", "Front-end Developer");
        update_post_meta($wpjobportal_new_page_id, "team_member_facebook", "http://www.facebook.com");
        update_post_meta($wpjobportal_new_page_id, "team_member_twitter", "http://www.twitter.com");
        update_post_meta($wpjobportal_new_page_id, "team_member_linkedin", "http://www.linkedin.com");
        update_post_meta($wpjobportal_new_page_id, "team_member_gplus", "http://www.googleplus.com");
        update_post_meta($wpjobportal_new_page_id, "team_member_instagram", "http://www.instagram.com");
        update_post_meta($wpjobportal_new_page_id, "team_member_pinterest", "http://www.pinterest.com");
        $wp_upload_dir = wp_upload_dir();
        $filename = $wp_upload_dir["basedir"]."/2017/01/tm_1.png";
        $parent_post_id = $wpjobportal_new_page_id;
        wpjobportal_uploadPostFeatureImage($filename,$parent_post_id);

        $wpjobportal_new_page_title = "Member 3";
        $wpjobportal_new_page_content = "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.";
        $wpjobportal_new_page = array(
                "post_type" => "jh_team_member",
                "post_title" => $wpjobportal_new_page_title,
                "post_content" => $wpjobportal_new_page_content,
                "post_status" => "publish",
                "post_author" => 1,
                "post_parent" => 0,
        );
        $wpjobportal_new_page_id = wp_insert_post($wpjobportal_new_page);
        update_post_meta($wpjobportal_new_page_id, "team_member_title", "Project Manager");
        update_post_meta($wpjobportal_new_page_id, "team_member_facebook", "http://www.facebook.com");
        update_post_meta($wpjobportal_new_page_id, "team_member_twitter", "http://www.twitter.com");
        update_post_meta($wpjobportal_new_page_id, "team_member_linkedin", "http://www.linkedin.com");
        update_post_meta($wpjobportal_new_page_id, "team_member_gplus", "http://www.googleplus.com");
        update_post_meta($wpjobportal_new_page_id, "team_member_instagram", "http://www.instagram.com");
        update_post_meta($wpjobportal_new_page_id, "team_member_pinterest", "http://www.pinterest.com");
        $wp_upload_dir = wp_upload_dir();
        $filename = $wp_upload_dir["basedir"]."/2017/01/tm_2.jpg";
        $parent_post_id = $wpjobportal_new_page_id;
        wpjobportal_uploadPostFeatureImage($filename,$parent_post_id);

        $wpjobportal_new_page_title = "Member 2";
        $wpjobportal_new_page_content = "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.";
        $wpjobportal_new_page = array(
                "post_type" => "jh_team_member",
                "post_title" => $wpjobportal_new_page_title,
                "post_content" => $wpjobportal_new_page_content,
                "post_status" => "publish",
                "post_author" => 1,
                "post_parent" => 0,
        );
        $wpjobportal_new_page_id = wp_insert_post($wpjobportal_new_page);
        update_post_meta($wpjobportal_new_page_id, "team_member_title", "Team Leader");
        update_post_meta($wpjobportal_new_page_id, "team_member_facebook", "http://www.facebook.com");
        update_post_meta($wpjobportal_new_page_id, "team_member_twitter", "http://www.twitter.com");
        update_post_meta($wpjobportal_new_page_id, "team_member_linkedin", "http://www.linkedin.com");
        update_post_meta($wpjobportal_new_page_id, "team_member_gplus", "http://www.googleplus.com");
        update_post_meta($wpjobportal_new_page_id, "team_member_instagram", "http://www.instagram.com");
        update_post_meta($wpjobportal_new_page_id, "team_member_pinterest", "http://www.pinterest.com");
        $wp_upload_dir = wp_upload_dir();
        $filename = $wp_upload_dir["basedir"]."/2017/01/tm_3.png";
        $parent_post_id = $wpjobportal_new_page_id;
        wpjobportal_uploadPostFeatureImage($filename,$parent_post_id);

        $wpjobportal_new_page_title = "Member 1";
        $wpjobportal_new_page_content = "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.";
        $wpjobportal_new_page = array(
                "post_type" => "jh_team_member",
                "post_title" => $wpjobportal_new_page_title,
                "post_content" => $wpjobportal_new_page_content,
                "post_status" => "publish",
                "post_author" => 1,
                "post_parent" => 0,
        );
        $wpjobportal_new_page_id = wp_insert_post($wpjobportal_new_page);
        update_post_meta($wpjobportal_new_page_id, "team_member_title", "Cheif executive office / CEO");
        update_post_meta($wpjobportal_new_page_id, "team_member_facebook", "http://www.facebook.com");
        update_post_meta($wpjobportal_new_page_id, "team_member_twitter", "http://www.twitter.com");
        update_post_meta($wpjobportal_new_page_id, "team_member_linkedin", "http://www.linkedin.com");
        update_post_meta($wpjobportal_new_page_id, "team_member_gplus", "http://www.googleplus.com");
        update_post_meta($wpjobportal_new_page_id, "team_member_instagram", "http://www.instagram.com");
        update_post_meta($wpjobportal_new_page_id, "team_member_pinterest", "http://www.pinterest.com");
        $wp_upload_dir = wp_upload_dir();
        $filename = $wp_upload_dir["basedir"]."/2017/01/tm_4.jpg";
        $parent_post_id = $wpjobportal_new_page_id;
        wpjobportal_uploadPostFeatureImage($filename,$parent_post_id);

        // Price Table
        $wpjobportal_new_page_title = "Basic Package";
        $wpjobportal_new_page_content = "";
        $page_check = $this->wpjobportal_get_page_by_title($wpjobportal_new_page_title);
        $wpjobportal_new_page = array(
                "post_type" => "jh_price_table",
                "post_title" => $wpjobportal_new_page_title,
                "post_content" => $wpjobportal_new_page_content,
                "post_status" => "publish",
                "post_author" => 1,
                "post_parent" => 0,
        );
        $wpjobportal_new_page_id = wp_insert_post($wpjobportal_new_page);
        update_post_meta($wpjobportal_new_page_id, "_wp_page_template", $wpjobportal_new_page_template);
        wpjobportal_wp_update_post_meta($wpjobportal_new_page_id, "jh_price" , "$ 500");
        wpjobportal_wp_update_post_meta($wpjobportal_new_page_id, "jh_line1" , "1500 Credits");
        wpjobportal_wp_update_post_meta($wpjobportal_new_page_id, "jh_line2" , "New Company 500 Credits");
        wpjobportal_wp_update_post_meta($wpjobportal_new_page_id, "jh_line3" , "New Job 250 Credits");
        wpjobportal_wp_update_post_meta($wpjobportal_new_page_id, "jh_line4" , "Featured Job 100 Credits");
        wpjobportal_wp_update_post_meta($wpjobportal_new_page_id, "jh_buynowlink" , "#");

        $wpjobportal_new_page_title = "Business Package";
        $wpjobportal_new_page_content = "";
        $page_check = $this->wpjobportal_get_page_by_title($wpjobportal_new_page_title);
        $wpjobportal_new_page = array(
                "post_type" => "jh_price_table",
                "post_title" => $wpjobportal_new_page_title,
                "post_content" => $wpjobportal_new_page_content,
                "post_status" => "publish",
                "post_author" => 1,
                "post_parent" => 0,
        );
        $wpjobportal_new_page_id = wp_insert_post($wpjobportal_new_page);
        update_post_meta($wpjobportal_new_page_id, "_wp_page_template", $wpjobportal_new_page_template);
        wpjobportal_wp_update_post_meta($wpjobportal_new_page_id, "jh_price" , "$ 750");
        wpjobportal_wp_update_post_meta($wpjobportal_new_page_id, "jh_line1" , "2500 Credits");
        wpjobportal_wp_update_post_meta($wpjobportal_new_page_id, "jh_line2" , "New Company 500 Credits");
        wpjobportal_wp_update_post_meta($wpjobportal_new_page_id, "jh_line3" , "New Job 250 Credits");
        wpjobportal_wp_update_post_meta($wpjobportal_new_page_id, "jh_line4" , "Featured Job 100 Credits");
        wpjobportal_wp_update_post_meta($wpjobportal_new_page_id, "jh_buynowlink" , "#");

        $wpjobportal_new_page_title = "Complete Package";
        $wpjobportal_new_page_content = "";
        $page_check = $this->wpjobportal_get_page_by_title($wpjobportal_new_page_title);
        $wpjobportal_new_page = array(
                "post_type" => "jh_price_table",
                "post_title" => $wpjobportal_new_page_title,
                "post_content" => $wpjobportal_new_page_content,
                "post_status" => "publish",
                "post_author" => 1,
                "post_parent" => 0,
        );
        $wpjobportal_new_page_id = wp_insert_post($wpjobportal_new_page);
        update_post_meta($wpjobportal_new_page_id, "_wp_page_template", $wpjobportal_new_page_template);
        wpjobportal_wp_update_post_meta($wpjobportal_new_page_id, "jh_price" , "$ 1500");
        wpjobportal_wp_update_post_meta($wpjobportal_new_page_id, "jh_line1" , "6000 Credits");
        wpjobportal_wp_update_post_meta($wpjobportal_new_page_id, "jh_line2" , "New Company 500 Credits");
        wpjobportal_wp_update_post_meta($wpjobportal_new_page_id, "jh_line3" , "New Job 250 Credits");
        wpjobportal_wp_update_post_meta($wpjobportal_new_page_id, "jh_line4" , "Featured Job 100 Credits");
        wpjobportal_wp_update_post_meta($wpjobportal_new_page_id, "jh_buynowlink" , "#");

        // TESTIMONIAL
        $wpjobportal_new_page_title = "Auro Navanth";
        $wpjobportal_new_page_content = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam iaculis quam sit amet dolor fermentum, in porta nisi egestas. Nullam convallis laoreet gravida. Pellentesque sed.";
        $wpjobportal_new_page_template = "";
        $page_check = $this->wpjobportal_get_page_by_title($wpjobportal_new_page_title);
        $wpjobportal_new_page = array(
                "post_type" => "jh_testimonials",
                "post_title" => $wpjobportal_new_page_title,
                "post_content" => $wpjobportal_new_page_content,
                "post_status" => "publish",
                "post_author" => 1,
                "post_parent" => 0,
        );

        $wpjobportal_new_page_id = wp_insert_post($wpjobportal_new_page);
        update_post_meta($wpjobportal_new_page_id, "_wp_page_template", $wpjobportal_new_page_template);
        // set feature image
        $filename = $wp_upload_dir["basedir"]."/2017/01/tsti_1.jpg";
        $parent_post_id = $wpjobportal_new_page_id;
        wpjobportal_uploadPostFeatureImage($filename,$parent_post_id);

        $wpjobportal_new_page_title = "Naro MathDoe";
        $wpjobportal_new_page_content = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec at tempus velit. Aliquam et diam convallis, tempus ligula ut, placerat sem. Nulla condimentum nulla a.";
        $wpjobportal_new_page_template = "";
        $page_check = $this->wpjobportal_get_page_by_title($wpjobportal_new_page_title);
        $wpjobportal_new_page = array(
                "post_type" => "jh_testimonials",
                "post_title" => $wpjobportal_new_page_title,
                "post_content" => $wpjobportal_new_page_content,
                "post_status" => "publish",
                "post_author" => 1,
                "post_parent" => 0,
        );
        $wpjobportal_new_page_id = wp_insert_post($wpjobportal_new_page);
        update_post_meta($wpjobportal_new_page_id, "_wp_page_template", $wpjobportal_new_page_template);
        // set feature image
        $filename = $wp_upload_dir["basedir"]."/2017/01/tsti_2.jpg";
        $parent_post_id = $wpjobportal_new_page_id;
        wpjobportal_uploadPostFeatureImage($filename,$parent_post_id);

        $wpjobportal_new_page_title = "MARY DOE";
        $wpjobportal_new_page_content = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce accumsan vitae massa vel aliquet. Morbi sed nibh eget lectus consequat tempor. Aliquam erat volutpat. Nam.";
        $wpjobportal_new_page_template = "";
        $page_check = $this->wpjobportal_get_page_by_title($wpjobportal_new_page_title);
        $wpjobportal_new_page = array(
                "post_type" => "jh_testimonials",
                "post_title" => $wpjobportal_new_page_title,
                "post_content" => $wpjobportal_new_page_content,
                "post_status" => "publish",
                "post_author" => 1,
                "post_parent" => 0,
        );
        $wpjobportal_new_page_id = wp_insert_post($wpjobportal_new_page);
        update_post_meta($wpjobportal_new_page_id, "_wp_page_template", $wpjobportal_new_page_template);
        // set feature image
        $filename = $wp_upload_dir["basedir"]."/2017/01/tsti_3.jpg";
        $parent_post_id = $wpjobportal_new_page_id;
        wpjobportal_uploadPostFeatureImage($filename,$parent_post_id);

        $wpjobportal_new_page_title = "Robert Lafore";
        $wpjobportal_new_page_content = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent eleifend lacinia enim at dapibus. Nam eget accumsan neque. Nam felis augue, egestas ut varius vel.";
        $wpjobportal_new_page_template = "";
        $page_check = $this->wpjobportal_get_page_by_title($wpjobportal_new_page_title);
        $wpjobportal_new_page = array(
                "post_type" => "jh_testimonials",
                "post_title" => $wpjobportal_new_page_title,
                "post_content" => $wpjobportal_new_page_content,
                "post_status" => "publish",
                "post_author" => 1,
                "post_parent" => 0,
        );
        $wpjobportal_new_page_id = wp_insert_post($wpjobportal_new_page);
        update_post_meta($wpjobportal_new_page_id, "_wp_page_template", $wpjobportal_new_page_template);
        // set feature image
        $filename = $wp_upload_dir["basedir"]."/2017/01/tsti_4.jpeg";
        $parent_post_id = $wpjobportal_new_page_id;
        wpjobportal_uploadPostFeatureImage($filename,$parent_post_id);

        $wpjobportal_new_page_title = "Auro Navanth";
        $wpjobportal_new_page_content = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam commodo laoreet neque, vitae facilisis quam eleifend a. In consectetur purus quis arcu dictum, sit amet.";
        $wpjobportal_new_page_template = "";
        $page_check = $this->wpjobportal_get_page_by_title($wpjobportal_new_page_title);
        $wpjobportal_new_page = array(
                "post_type" => "jh_testimonials",
                "post_title" => $wpjobportal_new_page_title,
                "post_content" => $wpjobportal_new_page_content,
                "post_status" => "publish",
                "post_author" => 1,
                "post_parent" => 0,
        );
        $wpjobportal_new_page_id = wp_insert_post($wpjobportal_new_page);
        update_post_meta($wpjobportal_new_page_id, "_wp_page_template", $wpjobportal_new_page_template);
        // set feature image
        $filename = $wp_upload_dir["basedir"]."/2017/01/tsti_5.png";
        $parent_post_id = $wpjobportal_new_page_id;
        wpjobportal_uploadPostFeatureImage($filename,$parent_post_id);
        // Pages and custom post are created Now create Menu ----------------

        update_option( "page_on_front", $jh_pages["home"] );
        update_option( "show_on_front", "page" );

        // MENU
        // Check if the menu exists
        $menu_name = "Job Hub";
        $menu_exists = wp_get_nav_menu_object( $menu_name );

        // If it doesn"t exist, let"s create it.
        if( !$menu_exists){
            $menu_id = wp_create_nav_menu($menu_name);

            $locations = get_theme_mod("nav_menu_locations");
            $locations["primary"] = $menu_id;
            set_theme_mod( "nav_menu_locations", $locations );

            $wpjobportal_itemData =  array(
                "menu-item-object-id" => $jh_pages["home"],
                "menu-item-parent-id" => 0,
                "menu-item-object" => "page",
                "menu-item-type"      => "post_type",
                "menu-item-status"    => "publish"
              );
            $parent_home = wp_update_nav_menu_item($menu_id, 0, $wpjobportal_itemData);
            $wpjobportal_itemData =  array(
                "menu-item-object-id" => $jh_pages["home1"],
                "menu-item-parent-id" => $parent_home,
                "menu-item-object" => "page",
                "menu-item-type"      => "post_type",
                "menu-item-status"    => "publish"
              );
            wp_update_nav_menu_item($menu_id, 0, $wpjobportal_itemData);
            $wpjobportal_itemData =  array(
                "menu-item-object-id" => $jh_pages["home2"],
                "menu-item-parent-id" => $parent_home,
                "menu-item-object" => "page",
                "menu-item-type"      => "post_type",
                "menu-item-status"    => "publish"
              );
            wp_update_nav_menu_item($menu_id, 0, $wpjobportal_itemData);
            // Job seeker

            $wpjobportal_itemData =  array(
                "menu-item-title" => "Job Seeker",
                "menu-item-object-id" => $jh_pages["jobseeker_control_panel"],
                "menu-item-parent-id" => 0,
                "menu-item-object" => "page",
                "menu-item-type"      => "post_type",
                "menu-item-status"    => "publish"
            );
            $parent_jobseeker = wp_update_nav_menu_item($menu_id, 0, $wpjobportal_itemData);
            $wpjobportal_itemData =  array(
                "menu-item-title" => "Jobs",
                "menu-item-object-id" => $jh_pages["newest_jobs"],
                "menu-item-parent-id" => $parent_jobseeker,
                "menu-item-object" => "page",
                "menu-item-type"      => "post_type",
                "menu-item-status"    => "publish"
            );
            wp_update_nav_menu_item($menu_id, 0, $wpjobportal_itemData);
            $wpjobportal_itemData =  array(
                "menu-item-title" => "Search Job",
                "menu-item-object-id" => $jh_pages["search_job"],
                "menu-item-parent-id" => $parent_jobseeker,
                "menu-item-object" => "page",
                "menu-item-type"      => "post_type",
                "menu-item-status"    => "publish"
            );
            wp_update_nav_menu_item($menu_id, 0, $wpjobportal_itemData);
            $wpjobportal_itemData =  array(
                "menu-item-title" => "Jobs By Categories",
                "menu-item-object-id" => $jh_pages["jobs_by_category"],
                "menu-item-parent-id" => $parent_jobseeker,
                "menu-item-object" => "page",
                "menu-item-type"      => "post_type",
                "menu-item-status"    => "publish"
            );
            wp_update_nav_menu_item($menu_id, 0, $wpjobportal_itemData);
            $wpjobportal_itemData =  array(
                "menu-item-title" => "Add Resume",
                "menu-item-object-id" => $jh_pages["add_resume"],
                "menu-item-parent-id" => $parent_jobseeker,
                "menu-item-object" => "page",
                "menu-item-type"      => "post_type",
                "menu-item-status"    => "publish"
            );
            wp_update_nav_menu_item($menu_id, 0, $wpjobportal_itemData);
            $wpjobportal_itemData =  array(
                "menu-item-title" => "My Applied Jobs",
                "menu-item-object-id" => $jh_pages["my_applied_jobs"],
                "menu-item-parent-id" => $parent_jobseeker,
                "menu-item-object" => "page",
                "menu-item-type"      => "post_type",
                "menu-item-status"    => "publish"
            );
            wp_update_nav_menu_item($menu_id, 0, $wpjobportal_itemData);
            // employer
            $wpjobportal_itemData =  array(
                "menu-item-title" => "Employer",
                "menu-item-object-id" => $jh_pages["employer_control_panel"],
                "menu-item-parent-id" => 0,
                "menu-item-object" => "page",
                "menu-item-type"      => "post_type",
                "menu-item-status"    => "publish"
            );
            $parent_employer = wp_update_nav_menu_item($menu_id, 0, $wpjobportal_itemData);
            $wpjobportal_itemData =  array(
                "menu-item-title" => "Add Job",
                "menu-item-object-id" => $jh_pages["add_job"],
                "menu-item-parent-id" => $parent_employer,
                "menu-item-object" => "page",
                "menu-item-type"      => "post_type",
                "menu-item-status"    => "publish"
            );
            wp_update_nav_menu_item($menu_id, 0, $wpjobportal_itemData);
            $wpjobportal_itemData =  array(
                "menu-item-title" => "My Jobs",
                "menu-item-object-id" => $jh_pages["my_jobs"],
                "menu-item-parent-id" => $parent_employer,
                "menu-item-object" => "page",
                "menu-item-type"      => "post_type",
                "menu-item-status"    => "publish"
            );
            wp_update_nav_menu_item($menu_id, 0, $wpjobportal_itemData);
            $wpjobportal_itemData =  array(
                "menu-item-title" => "Resume Search",
                "menu-item-object-id" => $jh_pages["resume_search"],
                "menu-item-parent-id" => $parent_employer,
                "menu-item-object" => "page",
                "menu-item-type"      => "post_type",
                "menu-item-status"    => "publish"
            );
            wp_update_nav_menu_item($menu_id, 0, $wpjobportal_itemData);
            $wpjobportal_itemData =  array(
                "menu-item-title" => "Resumes By Categories",
                "menu-item-object-id" => $jh_pages["resume_by_category"],
                "menu-item-parent-id" => $parent_employer,
                "menu-item-object" => "page",
                "menu-item-type"      => "post_type",
                "menu-item-status"    => "publish"
            );
            wp_update_nav_menu_item($menu_id, 0, $wpjobportal_itemData);
            $wpjobportal_itemData =  array(
                "menu-item-title" => "Pages",
                "menu-item-object-id" => $jh_pages["blog"],
                "menu-item-parent-id" => 0,
                "menu-item-object" => "page",
                "menu-item-type"      => "post_type",
                "menu-item-status"    => "publish"
              );
            $parent_pages = wp_update_nav_menu_item($menu_id, 0, $wpjobportal_itemData);
            $wpjobportal_itemData =  array(
                "menu-item-object-id" => $jh_pages["news_and_rumors"],
                "menu-item-parent-id" => $parent_pages,
                "menu-item-object" => "page",
                "menu-item-type"      => "post_type",
                "menu-item-status"    => "publish"
              );
            wp_update_nav_menu_item($menu_id, 0, $wpjobportal_itemData);
            $wpjobportal_itemData =  array(
                "menu-item-object-id" => $jh_pages["faq"],
                "menu-item-parent-id" => $parent_pages,
                "menu-item-object" => "page",
                "menu-item-type"      => "post_type",
                "menu-item-status"    => "publish"
              );
            wp_update_nav_menu_item($menu_id, 0, $wpjobportal_itemData);
            $wpjobportal_itemData =  array(
                "menu-item-object-id" => $jh_pages["blog"],
                "menu-item-parent-id" => $parent_pages,
                "menu-item-object" => "page",
                "menu-item-type"      => "post_type",
                "menu-item-status"    => "publish"
              );
            wp_update_nav_menu_item($menu_id, 0, $wpjobportal_itemData);
            $wpjobportal_itemData =  array(
                "menu-item-object-id" => $jh_pages["pricing_table"],
                "menu-item-parent-id" => $parent_pages,
                "menu-item-object" => "page",
                "menu-item-type"      => "post_type",
                "menu-item-status"    => "publish"
              );
            wp_update_nav_menu_item($menu_id, 0, $wpjobportal_itemData);
            $wpjobportal_itemData =  array(
                "menu-item-object-id" => $jh_pages["thank_you"],
                "menu-item-parent-id" => $parent_pages,
                "menu-item-object" => "page",
                "menu-item-type"      => "post_type",
                "menu-item-status"    => "publish"
              );
            wp_update_nav_menu_item($menu_id, 0, $wpjobportal_itemData);
            if($wpjobportal_pro == 1){

            $wpjobportal_itemData =  array(
                "menu-item-title" => "Credits",
                "menu-item-object-id" => $jh_pages["jobseeker_credits_pack"],
                "menu-item-parent-id" => $parent_pages,
                "menu-item-object" => "page",
                "menu-item-type"      => "post_type",
                "menu-item-status"    => "publish"
              );
            $parent_credits = wp_update_nav_menu_item($menu_id, 0, $wpjobportal_itemData);
            $wpjobportal_itemData =  array(
                "menu-item-title" => "Job Seeker Credits Pack",
                "menu-item-object-id" => $jh_pages["jobseeker_credits_pack"],
                "menu-item-parent-id" => $parent_pages,
                "menu-item-object" => "page",
                "menu-item-type"      => "post_type",
                "menu-item-status"    => "publish"
              );
            $parent_credits = wp_update_nav_menu_item($menu_id, 0, $wpjobportal_itemData);

            $wpjobportal_itemData =  array(
                "menu-item-title" => "Job Seeker Credits Log",
                "menu-item-object-id" => $jh_pages["jobseeker_credits_log"],
                "menu-item-parent-id" => $parent_credits,
                "menu-item-object" => "page",
                "menu-item-type"      => "post_type",
                "menu-item-status"    => "publish"
              );
            wp_update_nav_menu_item($menu_id, 0, $wpjobportal_itemData);

            $wpjobportal_itemData =  array(
                "menu-item-title" => "Job Seeker Rate List",
                "menu-item-object-id" => $jh_pages["jobseeker_rate_list"],
                "menu-item-parent-id" => $parent_credits,
                "menu-item-object" => "page",
                "menu-item-type"      => "post_type",
                "menu-item-status"    => "publish"
              );
            wp_update_nav_menu_item($menu_id, 0, $wpjobportal_itemData);
            $wpjobportal_itemData =  array(
                "menu-item-title" => "Employer Credits Pack",
                "menu-item-object-id" => $jh_pages["employer_credits_pack"],
                "menu-item-parent-id" => $parent_credits,
                "menu-item-object" => "page",
                "menu-item-type"      => "post_type",
                "menu-item-status"    => "publish"
              );
            wp_update_nav_menu_item($menu_id, 0, $wpjobportal_itemData);

            $wpjobportal_itemData =  array(
                "menu-item-title" => "Employer Credits Log",
                "menu-item-object-id" => $jh_pages["employer_credits_log"],
                "menu-item-parent-id" => $parent_credits,
                "menu-item-object" => "page",
                "menu-item-type"      => "post_type",
                "menu-item-status"    => "publish"
              );
            wp_update_nav_menu_item($menu_id, 0, $wpjobportal_itemData);

            $wpjobportal_itemData =  array(
                "menu-item-title" => "Employer Rate List",
                "menu-item-object-id" => $jh_pages["employer_rate_list"],
                "menu-item-parent-id" => $parent_credits,
                "menu-item-object" => "page",
                "menu-item-type"      => "post_type",
                "menu-item-status"    => "publish"
              );
            wp_update_nav_menu_item($menu_id, 0, $wpjobportal_itemData);
            }

            $wpjobportal_itemData =  array(
                "menu-item-object-id" => $jh_pages["ourteam"],
                "menu-item-parent-id" => 0,
                "menu-item-object" => "page",
                "menu-item-type"      => "post_type",
                "menu-item-status"    => "publish"
              );
            wp_update_nav_menu_item($menu_id, 0, $wpjobportal_itemData);
            $wpjobportal_itemData =  array(
                "menu-item-object-id" => $jh_pages["contact_us"],
                "menu-item-parent-id" => 0,
                "menu-item-object" => "page",
                "menu-item-type"      => "post_type",
                "menu-item-status"    => "publish"
              );
            wp_update_nav_menu_item($menu_id, 0, $wpjobportal_itemData);
            }
            $widget_positions = get_option("sidebars_widgets");
            // Woocommerce sidebar
                $widget_positions["woocommerce-sidebar"][] = "woocommerce_widget_cart-1";
                $widget_woocommerce_widget_cart_array[1] = array("title" => "My Cart");
                $widget_woocommerce_widget_cart_array["_multiwidget"] = 1;
                // Left sidebar
                $widget_positions["left-sidebar"][] = "search-1";
                $wpjobportal_search_array[1] = array("title" => "Search");
                $wpjobportal_search_array["_multiwidget"] = 1;
                $widget_positions["left-sidebar"][] = "recent-posts-1";
                $recent_posts_array[1] = array("title" => "Recent Posts", "number" => 5);
                $recent_posts_array["_multiwidget"] = 1;
                $widget_positions["left-sidebar"][] = "recent-comments-1";
                $recent_comments_array[1] = array("title" => "Recent Comments", "number" => 5);
                $recent_comments_array["_multiwidget"] = 1;
                $widget_positions["left-sidebar"][] = "archives-1";
                $archives_array[1] = array("title" => "Archives");
                $archives_array["_multiwidget"] = 1;
                $widget_positions["left-sidebar"][] = "categories-1";
                $wpjobportal_categories_array[1] = array("title" => "Categories");
                $wpjobportal_categories_array["_multiwidget"] = 1;
                $widget_positions["left-sidebar"][] = "meta-1";
                $meta_array[1] = array("title" => "Meta");
                $meta_array["_multiwidget"] = 1;
                // Right sidebar
                $widget_positions["right-sidebar"][] = "calendar-1";
                $calendar_array[1] = array("title" => "Calendar");
                $calendar_array["_multiwidget"] = 1;
                $widget_positions["right-sidebar"][] = "widget_jsjb_recent_comments-1";
                $widget_jsjb_recent_comments_array[1] = array("title" => "Job Hub Recent Comments", "count" => 2);
                $widget_jsjb_recent_comments_array["_multiwidget"] = 1;
                $widget_positions["right-sidebar"][] = "widget_jsjb_recent_posts-1";
                $widget_jsjb_recent_posts_array[1] = array("title" => "Job Hub Recent Posts", "category" => "");
                $widget_jsjb_recent_posts_array["_multiwidget"] = 1;
                $widget_positions["right-sidebar"][] = "nav_menu-1";
                $wpjobportal_nav_menu_array[1] = array("title" => "Custom Menu", "nav_menu" => "");
                $wpjobportal_nav_menu_array["_multiwidget"] = 1;
                $widget_positions["right-sidebar"][] = "pages-1";
                $pages_array[1] = array("title" => "Pages", "sortby" => "post_title");
                $pages_array["_multiwidget"] = 1;
                $widget_positions["right-sidebar"][] = "tag_cloud-1";
                $wpjobportal_tag_cloud_array[1] = array("title" => "Tag Cloud", "taxonomy" => "post_tag");
                $wpjobportal_tag_cloud_array["_multiwidget"] = 1;
                $widget_positions["right-sidebar"][] = "text-1";
                $wpjobportal_text_array[1] = array("title" => "Text Heading", "text" => "Text body is here for lorem ipsum Text body is here for lorem ipsum Text body is here for lorem ipsum Text body is here for lorem ipsum Text body is here for lorem ipsum Text body is here for lorem ipsum Text body is here for lorem ipsum Text body is here for lorem ipsum Text body is here for lorem ipsum Text body is here for lorem ipsum Text body is here for lorem ipsum Text body is here for lorem ipsum");
                $wpjobportal_text_array["_multiwidget"] = 1;
                // News and rumors
                $widget_positions["news_and_rumors"][] = "search-2";
                $wpjobportal_search_array[2] = array("title" => "Search");
                $wpjobportal_search_array["_multiwidget"] = 1;
                $widget_positions["news_and_rumors"][] = "recent-posts-2";
                $recent_posts_array[2] = array("title" => "Recent Posts", "number" => 5);
                $recent_posts_array["_multiwidget"] = 1;
                // footer1
                if (!is_active_sidebar( 'footer1' ) ) { // check if widget was activated from template. to avoid dupilication
                    $widget_positions["footer1"][] = "widget_jsjb_footeraboutus-1";
                    $widget_jsjb_footeraboutus_array[1] = array("title" => "Job Hub", "description" => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.");
                    $widget_jsjb_footeraboutus_array["_multiwidget"] = 1;
                }
                // footer2
                $widget_positions["footer2"][] = "widget_jsjb_footerusefullinks-1";
                $widget_jsjb_footerusefullinks_array[1] = array(
                    "title" => "Useful Links",
                    "title1"=>"Newest Jobs", "link1"=> get_the_permalink($jh_pages["newest_jobs"]),
                    "title3"=>"Search Job", "link3"=> get_the_permalink($jh_pages["search_job"]),
                    "title2"=>"Resume Search", "link2"=> get_the_permalink($jh_pages["resume_search"]),
                    "title4"=>"Shortlisted Jobs", "link4"=> get_the_permalink($jh_pages["shortlisted_jobs"]),
                    "title5"=>"All Companies", "link5"=> get_the_permalink($jh_pages["all_companies"]),
                    "title6"=>"", "link6"=> "#",
                    "title7"=>"", "link7"=> "#",
                    "title8"=>"", "link8"=> "#",
                    "title9"=>"", "link9"=> "#",
                    "title10"=>"", "link10"=> "#",
                    );
                $widget_jsjb_footerusefullinks_array["_multiwidget"] = 1;
                // footer3
                // $widget_positions["footer3"][] = "widget_jsjs_footercompaniesimages-1";
                $widget_jsjs_footercompaniesimages_array[1] = array("title" => "Featured Companies", "companytype" => 1, "max_images"=>9, "column"=>3);
                $widget_jsjs_footercompaniesimages_array["_multiwidget"] = 1;
                // footer4
                if (!is_active_sidebar( 'footer3' ) ) { // check if widget was activated from template. to avoid dupilication
                    $widget_positions["footer3"][] = "widget_jsjb_footercontactus-1";
                    $widget_jsjb_footercontactus_array[1] = array("title" => "Contact Us", "email" => "jobhub@yourdomain.com", "address"=>"At vero eos et accusamus et iusto odio dignissimos", "phone"=>"+1234567890");
                    $widget_jsjb_footercontactus_array["_multiwidget"] = 1;
                }

                update_option("widget_"."widget_woocommerce_widget_cart"  , $widget_woocommerce_widget_cart_array);
                update_option("widget_"."search"  , $wpjobportal_search_array);
                update_option("widget_"."recent-posts"  , $recent_posts_array);
                update_option("widget_"."recent-comments"  , $recent_comments_array);
                update_option("widget_"."archives"  , $archives_array);
                update_option("widget_"."categories"  , $wpjobportal_categories_array);
                update_option("widget_"."meta"  , $meta_array);
                update_option("widget_"."calendar"  , $calendar_array);
                update_option("widget_"."widget_jsjb_recent_comments"  , $widget_jsjb_recent_comments_array);
                update_option("widget_"."widget_jsjb_recent_posts"  , $widget_jsjb_recent_posts_array);
                update_option("widget_"."nav_menu"  , $wpjobportal_nav_menu_array);
                update_option("widget_"."pages"  , $pages_array);
                update_option("widget_"."tag_cloud"  , $wpjobportal_tag_cloud_array);
                update_option("widget_"."text"  , $wpjobportal_text_array);
                update_option("widget_"."widget_jsjb_footerusefullinks"  , $widget_jsjb_footerusefullinks_array);
                update_option("widget_"."widget_jsjs_footercompaniesimages"  , $widget_jsjs_footercompaniesimages_array);
                if (!is_active_sidebar( 'footer3' ) ) { // check if widget was activated from template. to avoid dupilication
                    update_option("widget_"."widget_jsjb_footercontactus"  , $widget_jsjb_footercontactus_array);
                }
                if (!is_active_sidebar( 'footer1' ) ) { // check if widget was activated from template. to avoid dupilication
                    update_option("widget_"."widget_jsjb_footeraboutus"  , $widget_jsjb_footeraboutus_array);
                }

                // update this array at last
                update_option( "sidebars_widgets" , $widget_positions);


                $wpjobportal_pageid = wpjobportal::$_db->get_var("Select id FROM `" . wpjobportal::$_db->prefix . "posts` WHERE post_name = 'js-support-ticket-controlpanel'");
                if(is_numeric($wpjobportal_pageid) && $wpjobportal_pageid > 0){
                    update_post_meta($wpjobportal_pageid, "_wp_page_template", "templates/template-fullwidth.php");
                }
            // Update the configuration default page to Vehicles
                $query = "UPDATE `".wpjobportal::$_db->prefix."wj_portal_config` SET configvalue = '".esc_sql($jh_pages["newest_jobs"])."' WHERE configname = 'default_pageid'";
                wpjobportaldb::query($query);
                update_option("rewrite_rules", "");
            return 1;
    }

    function insertJobCities($wpjobportal_jobid, $cityid) {
        $wpjobportal_insert_jobcity = "INSERT INTO `" . wpjobportal::$_db->prefix . "wj_portal_jobcities` (`jobid`, `cityid`)
        VALUES( " . esc_sql($wpjobportal_jobid) . ", " . esc_sql($cityid) . ");";
        wpjobportaldb::query($wpjobportal_insert_jobcity);
        return true;
    }

    function getPageList() {
        $query = "SELECT ID AS id, post_title AS text FROM `" . wpjobportal::$_db->prefix . "posts` WHERE post_type = 'page' AND post_status = 'publish' ";
        $pages = wpjobportal::$_db->get_results($query);
        return $pages;
    }

    function getListOfDemoVersions() {
        $post_data = array();// data to posted with curl call
        if(class_exists("job_portal_theme_Options_plugins")){
            job_portal_theme_Options_plugins::getListOfDemoVersions($post_data);
        }
        return ;
    }

    function getDemo($wpjobportal_demoid,$foldername,$wpjobportal_demo_overwrite =0) {
        if($wpjobportal_demoid == ''){
            die('demo not seleceted');
        }

        if($wpjobportal_demoid == ''){
            die('demo not seleceted');
        }
        if($wpjobportal_demo_overwrite == 1){
            WPJOBPORTALincluder::getJSModel('postinstallation')->removeJobPortalDemoData();
        }
        //$wpjobportal_url = 'https://setup.joomsky.com/jobmanagertheme/demoimporter/demos/'.$foldername.'/democode.php';
        $wpjobportal_url = 'https://wpjobportal.com/setup/theme/demoimporter/demos/'.$foldername.'/democode.php';
        //$wpjobportal_url = 'http://192.168.10.20/2023/jobportaltheme/democode.php';
        $post_data = array();
        $response = wp_remote_post( $wpjobportal_url, array('body' => $post_data,'timeout'=>7,'sslverify'=>wpjobportal_sslverify($wpjobportal_url)));
        if( !is_wp_error($response) && $response['response']['code'] == 200 && isset($response['body']) ){
            $call_result = $response['body'];
        }else{
            $call_result = false;
            if(!is_wp_error($response)){
               $error = $response['response']['message'];
           }else{
                $error = $response->get_error_message();
           }
        }

        if($call_result){
           $return_string = $call_result;
           $wpjobportal_result = json_decode(base64_decode($return_string),true);
           $this->installSampleDataTemplateJobPortal($wpjobportal_result);
           update_option('job_portal_demno_id',$wpjobportal_demoid);// record the id of the demo currently imported.
        }

        // if(class_exists("job_portal_theme_Options_plugins")){
        //     job_portal_theme_Options_plugins::getDemo($wpjobportal_demoid,$foldername,$wpjobportal_demo_overwrite);
        // }
        //$this->installSampleDataTemplateJobPortal();
        // die('post installtion model 1806');
        return;
    }

    function recursiveremove($wpjobportal_dir) {
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
        $wp_filesystem = wpjobportal::wpjobportal_filesystem();
        $wpjobportal_data_directory = WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue('data_directory');
        $wpjobportal_structure = glob(wpjobportalphplib::wpJP_rtrim($wpjobportal_dir, "/").'/*');
        if (is_array($wpjobportal_structure)) {
            foreach($wpjobportal_structure as $file) {
                if (is_dir($file)){
                    if($file != $wpjobportal_dir."/".$wpjobportal_data_directory && $file != $wpjobportal_dir."/languages")
                    $this->recursiveremove($file);
                }elseif (is_file($file)){
                    if($wp_filesystem->exists($file)){
                        if($wp_filesystem->is_writable($file)){
                            if(!wp_delete_file($file)){
                            }else{

                            }
                        }else{

                        }
                    }else{

                    }
                }
            }
        }
        if($wp_filesystem->exists($wpjobportal_dir) && count(glob("$wpjobportal_dir/*")) === 0 ){

            $wp_filesystem->rmdir($wpjobportal_dir);
        }
    }

    function makeDir($wpjobportal_path){
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
        $wp_filesystem = wpjobportal::wpjobportal_filesystem();

        if (!$wp_filesystem->exists($wpjobportal_path)){
            $wp_filesystem->mkdir( $wpjobportal_path, 0755 );
            $ourFileName = $wpjobportal_path.'/index.html';
            $ourFileHandle = $wp_filesystem->put_contents($ourFileName,'');
            if($ourFileHandle !== false){
            }else{
                die("can't open file (".esc_html($ourFileName).")");
            }
        }
    }

    function getWpUsersList() {
        // This site's users only: on multisite wp_users holds the whole network.
        $query = "SELECT ID as id,CONCAT(user_login,' ( ',display_name,' ) - ',id) AS text  FROM `" . wpjobportal::$_db->users . "` WHERE 1 = 1" . WPJOBPORTALincluder::getJSModel('user')->siteMembersOnly('ID');
        $wpjobportal_users = wpjobportal::$_db->get_results($query);
        $wpjobportal_data[0] = (object) array('id' => 0, 'text' => esc_html(__('Select User', 'wp-job-portal')));
        foreach ($wpjobportal_users as $wpjobportal_user) {
            $wpjobportal_data[] = $wpjobportal_user;
        }
        return $wpjobportal_data;
    }

    function addMissingUsers(){
        // wpuid column does not esist in the table. it was showing a query error in log
        $missingUser2 = 0;
        $query = "SELECT ID FROM `" . wpjobportal::$_db->users . "` WHERE 1 = 1" . WPJOBPORTALincluder::getJSModel('user')->siteMembersOnly('ID');
        $wpjobportal_users = wpjobportal::$_db->get_results($query);
        $wpUsers = array();
        $wpjobportal_jsstUsers = array();
        foreach ($wpjobportal_users as $wpjobportal_key => $wpjobportal_user) {
            $wpUsers[] = $wpjobportal_user->ID;
        }
        $query = " SELECT uid AS wpuid FROM `" . wpjobportal::$_db->prefix . "wj_portal_users`";
        $wpjobportal_users = wpjobportal::$_db->get_results($query);
        foreach ($wpjobportal_users as $wpjobportal_key => $wpjobportal_user) {
            $wpjobportal_jsstUsers[] = $wpjobportal_user->wpuid;
        }

        $missingUsers = array_diff($wpUsers,$wpjobportal_jsstUsers);
        foreach ($missingUsers as $missingUser) {
            $query = "SELECT count(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_users` WHERE uid = " . (int) ($missingUser);
            $wpjobportal_total = wpjobportal::$_db->get_var($query);
            if ($wpjobportal_total == 0) {
                $query = "SELECT * FROM `" . wpjobportal::$_db->users . "` WHERE id = " . (int) ($missingUser);
                $wpjobportal_user = wpjobportal::$_db->get_row($query);
                if (isset($wpjobportal_user)) {
                    $wpjobportal_row = WPJOBPORTALincluder::getJSTable('users');
                    $wpjobportal_data['uid'] = $wpjobportal_user->ID;
                    $wpjobportal_data['first_name'] = $wpjobportal_user->display_name;
                    $wpjobportal_data['emailaddress'] = $wpjobportal_user->user_email;
                    $wpjobportal_data['issocial'] = 0;
                    $wpjobportal_data['socialid'] = null;
                    $wpjobportal_data['status'] = 1;
                    $wpjobportal_data['created'] = date_i18n('Y-m-d H:i:s');
                    $wpjobportal_data = wpjobportal::wpjobportal_sanitizeData($wpjobportal_data);
                    $wpjobportal_row->bind($wpjobportal_data);
                    $wpjobportal_row->store();
                    $missingUser2 = 1;
                }
            }
        }
        if ($missingUser2 == 1) {
            //JSSTmessage::setMessage(esc_html(__('Missing user(s) added successfully!', 'wp-job-portal')), 'updated');
        } else {
            //JSSTmessage::setMessage(esc_html(__('No missing user found!', 'wp-job-portal')), 'error');
        }
        return;
    }

    function installSampleDataTemplateJobPortal($wpjobportal_data_arrays) {
        self::$wpjobportal_theme_demo_created_posts = array();
        if(isset($wpjobportal_data_arrays['plugin_pages_array'])){
            $wpjobportal_plugin_pages_array = $wpjobportal_data_arrays['plugin_pages_array'];
        }else{
            return 'error in demo data from live';
        }

        if(isset($wpjobportal_data_arrays['other_pages_array'])){
            $other_pages_array = $wpjobportal_data_arrays['other_pages_array'];
        }else{
            return 'error in demo data from live';
        }

        if(isset($wpjobportal_data_arrays['job_portal_posts_array'])){
            $wpjobportal_job_portal_posts_array = $wpjobportal_data_arrays['job_portal_posts_array'];
        }else{
            return 'error in demo data from live';
        }


        if(isset($wpjobportal_data_arrays['menu_array'])){
            $menu_array = $wpjobportal_data_arrays['menu_array'];
        }else{
            return 'error in demo data from live';
        }

        if(isset($wpjobportal_data_arrays['widgets_array'])){
            $widgets_array = $wpjobportal_data_arrays['widgets_array'];
        }else{
            return 'error in demo data from live';
        }

        if(isset($wpjobportal_data_arrays['theme_options'])){
            $wpjobportal_theme_options = $wpjobportal_data_arrays['theme_options'];
        }else{
            return 'error in demo data from live';
        }

        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
        $wp_filesystem = wpjobportal::wpjobportal_filesystem();


        $wp_upload_dir = wp_upload_dir();
        if ($wp_filesystem->exists(get_template_directory() . "/framework/plugins/sample-data.zip")) {
            /*
             * The demo's pictures, unpacked into uploads.
             *
             * This called PclZip directly with the uploads path exactly as
             * WordPress reports it, which on a Windows host is "D:\...\uploads".
             * PclZip reads that as a relative folder called "D:", fails with
             * PCLZIP_ERR_DIR_CREATE_FAIL, and still hands back its file list -
             * so nothing was unpacked and nothing said so. unzip_file() is the
             * WordPress call for this and copes with either kind of path.
             */
            unzip_file(get_template_directory() . "/framework/plugins/sample-data.zip", $wp_upload_dir["basedir"]);
        }
        if( ! function_exists("wpjobportal_wp_update_post_meta")){
            function wpjobportal_wp_update_post_meta( $post_id, $wpjobportal_field_name, $wpjobportal_value = "" ){
                if ( empty( $wpjobportal_value ) OR ! $wpjobportal_value ){
                    delete_post_meta( $post_id, $wpjobportal_field_name );
                }elseif ( ! get_post_meta( $post_id, $wpjobportal_field_name ) ){
                    add_post_meta( $post_id, $wpjobportal_field_name, $wpjobportal_value );
                }else{
                    update_post_meta( $post_id, $wpjobportal_field_name, $wpjobportal_value );
                }
            }
        }
        if( ! function_exists("wpjobportal_uploadPostFeatureImage")){
            function wpjobportal_uploadPostFeatureImage($filename,$parent_post_id){
                // Check the type of file. We"ll use this as the "post_mime_type".
                $filetype = wp_check_filetype( wpjobportalphplib::wpJP_basename( $filename ), null );
                // Get the path to the upload directory.
                $wp_upload_dir = wp_upload_dir();
                // Prepare an array of post data for the attachment.
                $attachment = array(
                    "guid"           => $wp_upload_dir["url"] . "/" . wpjobportalphplib::wpJP_basename( $filename ),
                    "post_mime_type" => $filetype["type"],
                    "post_title"     => wpjobportalphplib::wpJP_preg_replace( "/\.[^.]+$/", "", wpjobportalphplib::wpJP_basename( $filename ) ),
                    "post_content"   => "",
                    "post_status"    => "inherit"
                );
                // Insert the attachment.
                $attach_id = wp_insert_attachment( $attachment, $filename, $parent_post_id );
                // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
                do_action('wpjobportal_load_wp_image');
                // Generate the metadata for the attachment, and update the database record.
                $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
                wp_update_attachment_metadata( $attach_id, $attach_data );
                set_post_thumbnail( $parent_post_id, $attach_id );
            }
        }
        $jp_pages = array();

        $wp_upload_dir = wp_upload_dir();

    // job portal pages
        // plugin pages.
        $full_width_page_template = "templates/template-fullwidth.php";
        $wp_page_array = array();
        foreach ($wpjobportal_plugin_pages_array as $wpjobportal_key => $page_title) {
            $page_title_string = wpjobportalphplib::wpJP_strtolower($page_title);
            $page_title_string = wpjobportalphplib::wpJP_str_replace(" ","_",$page_title_string);
            $page_content = '[vc_row][vc_column][jp_job_portal_theme_pages page page="'.esc_attr($wpjobportal_key).'"][/vc_column][/vc_row]';
            $jp_pages[$page_title_string] = WPJOBPORTALincluder::getJSModel('postinstallation')->createTemplatePagesGeneric($page_title,$page_content,$full_width_page_template,1);
            $wp_page_array[$page_title_string] = $jp_pages[$page_title_string];
        }
        update_option("job-portal-layout", $wp_page_array);
    // job portal pages end


    // job portal theme pages
    // $other_pages_array = array();


    // // Home

    foreach ($other_pages_array as $wpjobportal_key => $page_data) {
        $page_title = $page_data['page_title'];
        $page_content = $page_data['page_content'];
        $page_template = $page_data['page_template'];
        $wpjobportal_show_header = $page_data['show_header'];
        $page_title_string = wpjobportalphplib::wpJP_strtolower($page_title);
        $page_title_string = wpjobportalphplib::wpJP_str_replace(" ","_",$page_title_string);
        if($page_title_string == 'home'){// to handle page ids for url
            $page_content = str_replace('{page_id}', $jp_pages['newest_jobs'], $page_content);
        }
        $jp_pages[$page_title_string] = WPJOBPORTALincluder::getJSModel('postinstallation')->createTemplatePagesGeneric($page_title,$page_content,$page_template,$wpjobportal_show_header);
    }
    update_option("page_for_posts", $jp_pages["blog"]);

        $jp_post_ids = array();
        $jp_post_ids_for_links = array();
        foreach ($wpjobportal_job_portal_posts_array as $wpjobportal_key => $post_data) {
            // $post_data will have all the relvent information for post creation
            $post_type = $post_data['post_type'];
            $post_title = $post_data['post_title'];
            $post_content = $post_data['post_content'];
            $post_image = $post_data['post_image'];
            $post_meta = $post_data['post_meta'];
            $post_title_string = wpjobportalphplib::wpJP_strtolower($post_title);
            $post_title_string = wpjobportalphplib::wpJP_str_replace(" ","_",$post_title_string);

            // handle dynamic data case
            if($post_title_string == 'register_as_job_seeker'){
                $post_meta['wpj_jp_widget_image_URL_two'] = JOB_PORTAL_THEME_IMAGE.'/register-jobseeker.png';
                $post_meta['wpj_mw_register_btn_link'] = get_the_permalink($jp_pages["jobseeker_registration"]);
            }

            if($post_title_string == 'register_as_employer'){
                $post_meta['wpj_jp_widget_image_URL_two'] = JOB_PORTAL_THEME_IMAGE.'/register-employer.png';
                $post_meta['wpj_mw_register_btn_link'] = get_the_permalink($jp_pages["employer_registration"]);
            }

            // call the function to create post with above data and return post id to keep record(handle remove demo data case)
            $post_id = WPJOBPORTALincluder::getJSModel('postinstallation')->createSamplePosts($post_type,$post_title,$post_content,$post_image,$post_meta);
            $jp_post_ids[] = $post_id;
            $jp_post_ids_for_links[$post_title_string] = $post_id;
        }

        // Pages and custom post are created Now create Menu ----------------

        // Update WP Options
        update_option( "page_on_front", $jp_pages["home"] );
        update_option( "show_on_front", "page" );

        // main array indexes are the page_title_strings strtolower and without spaces

            // echo '<pre>';print_r($menu_array);echo '</pre>';
            // echo '<pre>';print_r($jp_post_ids_for_links);echo '</pre>';
            // die('asdf');
        $menu_name = "Job Portal";
        $menu_exists = wp_get_nav_menu_object( $menu_name );

        // If it doesn"t exist, let"s create it.
        if( !$menu_exists){
            $menu_id = wp_create_nav_menu($menu_name);

            $locations = get_theme_mod("nav_menu_locations");
            $locations["primary"] = $menu_id;
            set_theme_mod( "nav_menu_locations", $locations );

            foreach ($menu_array as $page_string => $menu_item) { // main menu objects
                //createSampleMenuItem($menu_id,$wp_pageid,$parent_id = 0,$title='')
                $wpjobportal_number_of_columns = ''; // to handle the case of menu widgets
                $wp_pageid = $jp_pages[$page_string]; // js_pages has ids of created pages by the title string as index
                $menu_item_type = $menu_item['menu_object'];
                if(isset($menu_item['number_of_columns']) && $menu_item['number_of_columns'] !='' ){
                    $wpjobportal_number_of_columns = $menu_item['number_of_columns'];
                }
                $menu_parent_id = WPJOBPORTALincluder::getJSModel('postinstallation')->createSampleMenuItem($menu_id,$wp_pageid,$menu_item['menu_title'],0,$menu_item_type,$wpjobportal_number_of_columns);
                if( is_array($menu_item['sub_menu']) && !empty($menu_item['sub_menu']) ){
                    foreach ($menu_item['sub_menu'] as $wpjobportal_sub_page_string => $wpjobportal_sub_menu_item) {

                        //jp_menu_widget
                        if(isset($jp_pages[$wpjobportal_sub_page_string])){
                            $wp_pageid = $jp_pages[$wpjobportal_sub_page_string]; // js_pages has ids of created pages by the title string as index
                        }elseif(isset($jp_post_ids_for_links[$wpjobportal_sub_page_string])){
                            $wp_pageid = $jp_post_ids_for_links[$wpjobportal_sub_page_string]; // js_pages has ids of created pages by the title string as index
                        }else{
                            continue; // to avoid any errors. if id not found
                        }

                        $menu_item_type = $wpjobportal_sub_menu_item['menu_object'];
                        $wpjobportal_number_of_columns = ''; // to handle the case of menu widgets
                        if(isset($wpjobportal_sub_menu_item['number_of_columns']) && $wpjobportal_sub_menu_item['number_of_columns'] !='' ){
                            $wpjobportal_number_of_columns = $wpjobportal_sub_menu_item['number_of_columns'];
                        }
                        $wpjobportal_sub_menu_id = WPJOBPORTALincluder::getJSModel('postinstallation')->createSampleMenuItem($menu_id,$wp_pageid,$wpjobportal_sub_menu_item['menu_title'],$menu_parent_id,$menu_item_type,$wpjobportal_number_of_columns);
                    }
                }
            }
        }

        // add widgets to side bars and footer positions
        $params_data = array();
        $params_data['logo'] = JOB_PORTAL_THEME_IMAGE.'/logo.png';
        $params_data['pages'] = $jp_pages;
         // echo '<pre>';print_r($widgets_array);echo '</pre>';
         // die('asdf');
        foreach ($widgets_array as $wpjobportal_key => $widget_data) {
            WPJOBPORTALincluder::getJSModel('postinstallation')->createTemplateWidget($widget_data,$params_data);
        }
        // redux options// template options
        //$redux_options_json = '{"animated_menu":"1", "fixed_menu":"1", "menu_login_logout":"1", "jobs_layout":"1", "footer_copyright_show":"1", "header_bg":"#378AD8", "primary_color":"#378AD8", "copyright_bg":"#378AD8"}';// json is generated from export redux options interface,
        // json is generated from export redux options interface, and stored in data index of this array
        $redux_options_json  = isset($wpjobportal_theme_options['data']) ? $wpjobportal_theme_options['data']: wp_json_encode(array());

        $wpjobportal_options = json_decode($redux_options_json, true);
        $redux_option = ReduxFrameworkInstances::get_instance('job_portal_theme_options');
        global $job_portal_theme_options;
        if(isset($job_portal_theme_options['header_bg']['hover'])){
            $wpjobportal_options['header_bg']['hover'] = $job_portal_theme_options['header_bg']['hover'];// to solve a merge array problem;
        }
        $wpjobportal_options = array_merge($job_portal_theme_options,$wpjobportal_options);
        //$redux_option->set_options($wpjobportal_options);

        $opt_name = 'job_portal_theme_options'; // TODO - Replace with your opt_name
        foreach ($wpjobportal_options as $wpjobportal_key => $wpjobportal_value) {
            Redux::set_option( $opt_name, $wpjobportal_key, $wpjobportal_value );
        }

        //$redux_option->set($wpjobportal_options); // to avoid a notice. not working ....

        // storing values of pages and posts
        $wpjobportal_theme_demo_batch = wp_generate_uuid4();
        $wpjobportal_created_post_ids = array_values(array_unique(array_map(
            'absint',
            self::$wpjobportal_theme_demo_created_posts
        )));
        $wpjobportal_tracked_pages = array();
        foreach ((array) $jp_pages as $wpjobportal_page_key => $wpjobportal_page_id) {
            if (in_array(absint($wpjobportal_page_id), $wpjobportal_created_post_ids, true)) {
                $wpjobportal_tracked_pages[$wpjobportal_page_key] = absint($wpjobportal_page_id);
            }
        }
        $wpjobportal_tracked_posts = array_values(array_filter(array_map(
            'absint',
            (array) $jp_post_ids
        ), function ($wpjobportal_post_id) use ($wpjobportal_created_post_ids) {
            return in_array($wpjobportal_post_id, $wpjobportal_created_post_ids, true);
        }));
        foreach ($wpjobportal_created_post_ids as $wpjobportal_demo_post_id) {
            if (!is_scalar($wpjobportal_demo_post_id)) {
                continue;
            }
            $wpjobportal_demo_post_id = absint($wpjobportal_demo_post_id);
            if ($wpjobportal_demo_post_id > 0 && get_post($wpjobportal_demo_post_id)) {
                update_post_meta($wpjobportal_demo_post_id, '_wpjobportal_theme_demo_batch', $wpjobportal_theme_demo_batch);
                update_post_meta($wpjobportal_demo_post_id, '_wpjobportal_theme_demo_signature', $this->themeDemoPostSignature($wpjobportal_demo_post_id));
            }
        }
        update_option('job_portal_theme_demo_pages_ids',$wpjobportal_tracked_pages);
        update_option('job_portal_theme_demo_post_ids',$wpjobportal_tracked_posts);
        update_option('job_portal_theme_demo_batch',$wpjobportal_theme_demo_batch, false);
        $wpjobportal_demo_data_array = array();
        $wpjobportal_demo_data_array['color'] = isset($wpjobportal_theme_options['color']) ? $wpjobportal_theme_options['color'] : "#378AD8";
        update_option('job_portal_theme_demo_demo_specific_data',$wpjobportal_demo_data_array);



        $wpjobportal_pageid = wpjobportal::$_db->get_var("Select id FROM `" . wpjobportal::$_db->prefix . "posts` WHERE post_name = 'js-support-ticket-controlpanel'");
        if(is_numeric($wpjobportal_pageid) && $wpjobportal_pageid > 0){
            update_post_meta($wpjobportal_pageid, "_wp_page_template", "templates/template-fullwidth.php");
        }
    // Update the configuration default page to Vehicles
        $query = "UPDATE `".wpjobportal::$_db->prefix."wj_portal_config` SET configvalue = '".esc_sql($jp_pages["newest_jobs"])."' WHERE configname = 'default_pageid'";
        wpjobportaldb::query($query);
        update_option("rewrite_rules", "");
            ///die(' all code executed');
        return 1;
    }

    function removeJobPortalDemoData(){
        $wpjobportal_theme_demo_batch = (string) get_option('job_portal_theme_demo_batch', '');
        if ('' === $wpjobportal_theme_demo_batch) {
            return array('deleted' => 0, 'skipped' => 0);
        }
        $pages_array = get_option('job_portal_theme_demo_pages_ids');
        $post_array = get_option('job_portal_theme_demo_post_ids');
        $wpjobportal_remaining_pages = array();
        $wpjobportal_remaining_posts = array();
        $wpjobportal_result = array('deleted' => 0, 'skipped' => 0);
        foreach (array(
            'pages' => (array) $pages_array,
            'posts' => (array) $post_array,
        ) as $wpjobportal_collection => $wpjobportal_values) {
            foreach ($wpjobportal_values as $wpjobportal_key => $wpjobportal_value) {
            if (!is_scalar($wpjobportal_value)) {
                $wpjobportal_result['skipped']++;
                continue;
            }
            $wpjobportal_value = absint($wpjobportal_value);
            if ($wpjobportal_value <= 0 || !get_post($wpjobportal_value)) {
                continue;
            }
            $wpjobportal_saved_signature = (string) get_post_meta($wpjobportal_value, '_wpjobportal_theme_demo_signature', true);
            $wpjobportal_owned = $wpjobportal_theme_demo_batch === (string) get_post_meta($wpjobportal_value, '_wpjobportal_theme_demo_batch', true)
                && '' !== $wpjobportal_saved_signature
                && hash_equals($wpjobportal_saved_signature, $this->themeDemoPostSignature($wpjobportal_value));
            if ($wpjobportal_owned && false !== wp_delete_post($wpjobportal_value, true)) {
                $wpjobportal_result['deleted']++;
                continue;
            }

            $wpjobportal_result['skipped']++;
            if ('pages' === $wpjobportal_collection) {
                $wpjobportal_remaining_pages[$wpjobportal_key] = $wpjobportal_value;
            } else {
                $wpjobportal_remaining_posts[$wpjobportal_key] = $wpjobportal_value;
            }
            }
        }
        if (empty($wpjobportal_remaining_pages)) {
            delete_option('job_portal_theme_demo_pages_ids');
        } else {
            update_option('job_portal_theme_demo_pages_ids', $wpjobportal_remaining_pages, false);
        }
        if (empty($wpjobportal_remaining_posts)) {
            delete_option('job_portal_theme_demo_post_ids');
        } else {
            update_option('job_portal_theme_demo_post_ids', $wpjobportal_remaining_posts, false);
        }
        if (empty($wpjobportal_remaining_pages) && empty($wpjobportal_remaining_posts)) {
            delete_option('job_portal_theme_demo_batch');
        }
        if (class_exists('WPJOBPORTAL_Audit_Service')) {
            WPJOBPORTAL_Audit_Service::record(
                'theme_demo_cleanup',
                $wpjobportal_result['skipped'] > 0 ? 'warning' : 'success',
                $wpjobportal_result
            );
        }
        return $wpjobportal_result;
    }

    function isUntouchedThemeDemoPost($post_id){
        $post_id = absint($post_id);
        $wpjobportal_saved_signature = (string) get_post_meta($post_id, '_wpjobportal_theme_demo_signature', true);
        return $post_id > 0
            && '' !== $wpjobportal_saved_signature
            && hash_equals($wpjobportal_saved_signature, $this->themeDemoPostSignature($post_id));
    }

    function themeDemoPostSignature($post_id){
        $wpjobportal_post = get_post(absint($post_id));
        if (!$wpjobportal_post) {
            return '';
        }
        return hash('sha256', wp_json_encode(array(
            (string) $wpjobportal_post->ID,
            (string) $wpjobportal_post->post_type,
            (string) $wpjobportal_post->post_status,
            (string) $wpjobportal_post->post_name,
            (string) $wpjobportal_post->post_title,
            (string) $wpjobportal_post->post_content,
        )));
    }

    function getMessagekey(){
        $wpjobportal_key = 'postinstallation';if(wpjobportal::$_common->wpjp_isadmin()){$wpjobportal_key = 'admin_'.$wpjobportal_key;}return $wpjobportal_key;
    }


    function wpjobportal_uploadPostFeatureImage($filename,$parent_post_id){
        // Check the type of file. We"ll use this as the "post_mime_type".
        $filetype = wp_check_filetype( wpjobportalphplib::wpJP_basename( $filename ), null );
        // Get the path to the upload directory.
        $wp_upload_dir = wp_upload_dir();
        /*
         * The theme demo names its pictures relative to the uploads folder -
         * "/2017/01/post2.jpg", the layout sample-data.zip is unpacked into -
         * and this passed that string on as though it were a file path. It is
         * not one: wp_insert_attachment() recorded "/2017/01/post2.jpg" as the
         * file, so every URL came out as uploads//2017/01/post2.jpg, and
         * wp_generate_attachment_metadata() warned that it could not open a
         * file at the root of the drive. Every blog, news and team picture of
         * an imported demo was a broken image.
         *
         * A path already inside uploads is used as it is; anything else is
         * taken as relative to uploads. The GUID follows the file rather than
         * this month's folder, which the file was never in.
         */
        $wpjobportal_basedir = wp_normalize_path($wp_upload_dir["basedir"]);
        $wpjobportal_given = wp_normalize_path($filename);
        if (strpos($wpjobportal_given, $wpjobportal_basedir . '/') === 0) {
            $wpjobportal_relative = substr($wpjobportal_given, strlen($wpjobportal_basedir) + 1);
        } else {
            $wpjobportal_relative = ltrim($wpjobportal_given, '/');
        }
        // Built from WordPress's own uploads path, not the normalised one:
        // update_attached_file() strips exactly that prefix to store the file
        // as "2017/01/post2.jpg", and a path it does not recognise is stored
        // whole - which then resolves to no URL at all.
        $filename = $wp_upload_dir["basedir"] . '/' . $wpjobportal_relative;
        if (!file_exists($filename)) {
            // An attachment pointing at nothing draws a broken image wherever
            // the post is listed. Without the picture, leave the post without one.
            return;
        }
        // Prepare an array of post data for the attachment.
        $attachment = array(
            "guid"           => $wp_upload_dir["baseurl"] . "/" . $wpjobportal_relative,
            "post_mime_type" => $filetype["type"],
            "post_title"     => wpjobportalphplib::wpJP_preg_replace( "/\.[^.]+$/", "", wpjobportalphplib::wpJP_basename( $filename ) ),
            "post_content"   => "",
            "post_status"    => "inherit"
        );
        // Insert the attachment.
        $attach_id = wp_insert_attachment( $attachment, $filename, $parent_post_id );
        // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
        do_action('wpjobportal_load_wp_image');
        // Generate the metadata for the attachment, and update the database record.
        $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
        wp_update_attachment_metadata( $attach_id, $attach_data );
        set_post_thumbnail( $parent_post_id, $attach_id );
    }

    function createSamplePosts($post_type,$post_title,$post_content,$file_path = '',$postmeta = array()){
        // $wpjobportal_new_page_template = '';
        $wpjobportal_new_post = array(
                        "post_type" => $post_type,
                        "post_title" => $post_title,
                        "post_content" => $post_content,
                        "post_status" => "publish",
                        "post_author" => 1,
                        "post_parent" => 0,
                );
        $post_id = wp_insert_post($wpjobportal_new_post);
        if (!is_wp_error($post_id) && absint($post_id) > 0) {
            self::$wpjobportal_theme_demo_created_posts[] = absint($post_id);
        }
        // update_post_meta($post_id, "_wp_page_template", $wpjobportal_new_page_template);
        if($file_path != ''){
            $this->wpjobportal_uploadPostFeatureImage($file_path,$post_id);
        }
        if(!empty($postmeta)){
            foreach ($postmeta as $meta_key => $meta_value) {
                wpjobportal_wp_update_post_meta($post_id, $meta_key, $meta_value);
            }
        }
        return $post_id;
    }


    function createSampleMenuItem($menu_id,$wp_pageid,$title,$parent_id,$menu_item_type,$wpjobportal_number_of_columns = 0){
        $wpjobportal_itemData = array();
        if($title != ''){
            $wpjobportal_itemData['menu-item-title'] = $title;
        }
        $wpjobportal_itemData["menu-item-object-id"] = $wp_pageid;
        $wpjobportal_itemData["menu-item-parent-id"] = $parent_id;
        $wpjobportal_itemData["menu-item-object"] = $menu_item_type;
        $wpjobportal_itemData["menu-item-type"] = "post_type";
        $wpjobportal_itemData["menu-item-status"] = "publish";
        if($wpjobportal_number_of_columns > 0){
            $wpjobportal_itemData["menu-item-numberofsubcolumns"] = $wpjobportal_number_of_columns;
        }
        // original sample

        $return_parent_id = wp_update_nav_menu_item($menu_id, 0, $wpjobportal_itemData);
        if($wpjobportal_number_of_columns > 0){
            update_post_meta( $return_parent_id, '_menu_item_numberofsubcolumns', $wpjobportal_number_of_columns );
        }
        return $return_parent_id;
    }

    function getPageByTitle($title){
        $query = new WP_Query(
            array(
                'post_type'              => 'page',
                'title'                  => $title,
                'posts_per_page'         => 1,
                'no_found_rows'          => true,
                'update_post_term_cache' => false,
                'update_post_meta_cache' => false,
            )
        );

        if ( ! empty( $query->post ) ) {
            return $query->post;

        } else {
            return false;
        }
    }

    function createTemplatePagesGeneric($page_title,$page_content,$page_template,$wpjobportal_show_header,$file_path=''){
        $wpjobportal_new_page = array(
                "post_type" => "page",
                "post_title" => $page_title,
                "post_content" => $page_content,
                "post_status" => "publish",
                "post_author" => 1,
                "post_parent" => 0,
        );
        $page_check = $this->getPageByTitle($page_title);
        $wpjobportal_page_created = false;
        if(!isset($page_check->ID)){
            $page_id = wp_insert_post($wpjobportal_new_page);
            $wpjobportal_page_created = !is_wp_error($page_id) && absint($page_id) > 0;
        }else{
            $wpjobportal_new_page["post_title"] = "Job Portal ".$page_title;
            $page_check = $this->getPageByTitle($wpjobportal_new_page["post_title"]);
            if(!isset($page_check->ID)){
                $page_id = wp_insert_post($wpjobportal_new_page);
                $wpjobportal_page_created = !is_wp_error($page_id) && absint($page_id) > 0;
            }else{
                $page_id = $page_check->ID;
            }
        }
        /*
         * Importing a second demo found the first one's "Home" (and from the
         * third import on, its "Job Portal Home") and kept it, so only the
         * colours changed and the new demo's page layout never arrived. A page
         * an earlier import made, still exactly as it made it, takes the new
         * demo's content; one the site owner has edited since is left alone.
         */
        if(!$wpjobportal_page_created && !empty($page_id) && $this->isUntouchedThemeDemoPost($page_id)){
            $wpjobportal_updated = wp_update_post(array(
                'ID' => absint($page_id),
                'post_content' => $page_content,
            ), true);
            $wpjobportal_page_created = !is_wp_error($wpjobportal_updated);
        }
        if($wpjobportal_page_created){
            self::$wpjobportal_theme_demo_created_posts[] = absint($page_id);
            update_post_meta($page_id, "jp_show_header", $wpjobportal_show_header);
            update_post_meta($page_id, "jp_headerimage_url", $file_path);
            if($page_template != ''){
                update_post_meta($page_id, "_wp_page_template", $page_template);
            }
        }

        return $page_id;
    }

    function createTemplateWidget($widget_array,$params_data = array()){
        $wpjobportal_name_string = $widget_array['name_string'];
        $widget_position = $widget_array['position_name'];

        // to handle the case of wigdets having dynamic created data

        if(!empty($params_data)){
            if($widget_position == 'footer1'){
                $widget_array['params']['logo'] = $params_data['logo'];
            }elseif ($widget_position == 'footer2') {
                $widget_array['params']['link1'] = get_the_permalink($params_data['pages']["newest_jobs"]);
                $widget_array['params']['link3'] = get_the_permalink($params_data['pages']["search_job"]);
                $widget_array['params']['link2'] = get_the_permalink($params_data['pages']["resume_search"]);
                $widget_array['params']['link4'] = get_the_permalink($params_data['pages']["shortlisted_jobs"]);
                $widget_array['params']['link5'] = get_the_permalink($params_data['pages']["all_companies"]);

            }elseif ($widget_position == 'footer3') {
                $widget_array['params']['link1'] = get_the_permalink($params_data['pages']["my_jobs"]);
                $widget_array['params']['link3'] = get_the_permalink($params_data['pages']["resume_search"]);// corrct ordering
                $widget_array['params']['link2'] = get_the_permalink($params_data['pages']["add_job"]);
                $widget_array['params']['link4'] = get_the_permalink($params_data['pages']["my_companies"]);

            }
        }

        $widget_params = array();
        // to handle the case of same widget on two different positions
        if($widget_position != 'footer3' ){
            $widget_params[1] = $widget_array['params'];
        }else{
            $widget_params =  json_decode( wp_json_encode(get_option("widget_".$wpjobportal_name_string)),true);
            $widget_params[] = $widget_array['params'];
        }

        $widget_params['_multiwidget'] = $widget_array['_multiwidget'];
        update_option("widget_".$wpjobportal_name_string  , $widget_params);

        // set widget in wordpress site bar options
            $widget_positions = get_option("sidebars_widgets");
            if($widget_position != 'footer3' ){ // to handle the case of using same widget in muliple footer positions.
                $widget_id = $wpjobportal_name_string."-1";
            }else{
                $widget_id = $wpjobportal_name_string."-2";
            }
            // Placed once. Every demo import used to add the widget again, so a
            // site imported twice showed each footer column twice.
            if(!isset($widget_positions[$widget_position]) || !is_array($widget_positions[$widget_position])){
                $widget_positions[$widget_position] = array();
            }
            if(!in_array($widget_id, $widget_positions[$widget_position], true)){
                $widget_positions[$widget_position][] = $widget_id;
            }
            update_option( "sidebars_widgets" , $widget_positions);
            return;
    }

    function wpjobportal_get_page_by_title( $title ) {
        $wpjobportal_args = array(
            'post_type'   => 'page',
            'title'       => $title,
            'post_status' => 'publish',
            'numberposts' => 1,
        );

        $query = new WP_Query( $wpjobportal_args );

        if ( $query->have_posts() ) {
            return $query->posts[0];
        }

        return null;
    }


}
?>
