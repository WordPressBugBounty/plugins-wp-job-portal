<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALuploads {

    private $uploadfor;
    private $companyid;
    private $resumeid;
    private $jobseekerid;
    private $userid;
    private $custom_field_upload = 0;
    private $entitey_id;

    function wpjobportal_upload_dir( $wpjobportal_dir ) {
        $wpjobportal_form_request = WPJOBPORTALrequest::getVar('form_request');
        if($wpjobportal_form_request == 'wpjobportal' || ($this->uploadfor == 'resumephoto' || $this->uploadfor == 'resumefiles'||$this->uploadfor=='profile')){ // Patch b/c of resume is ajax base
            $wpjobportal_datadirectory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
            // test code
            if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
            if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ) {
                $creds = request_filesystem_credentials( site_url() );
                wp_filesystem( $creds );
            }

            $file = $wpjobportal_dir['basedir'].'/'.$wpjobportal_datadirectory."/index.html";
            if (@ $wp_filesystem->put_contents( $file, '', 0755 ) ) {
            }

            $wpjobportal_path = $wpjobportal_datadirectory . '/data';
            // // test code
            $file = $wpjobportal_dir['basedir'].'/'.$wpjobportal_path."/index.html";
            if (@ $wp_filesystem->put_contents( $file, '', 0755 ) ) {
            }
            //
            $apath = $wpjobportal_path;
            if($this->custom_field_upload == 0){
                if($this->uploadfor == 'company'){
                    $wpjobportal_path = $wpjobportal_path . '/employer/comp_'.$this->companyid.'/logo';
                }elseif($this->uploadfor == 'resumephoto'){
                    $wpjobportal_path = $wpjobportal_path . '/jobseeker/resume_'.$this->resumeid.'/photo';
                }elseif($this->uploadfor == 'resumefiles'){
                    $wpjobportal_path = $wpjobportal_path . '/jobseeker/resume_'.$this->resumeid.'/resume';
                }elseif($this->uploadfor == 'profile'){
                    $wpjobportal_path = $wpjobportal_path . '/profile/profile_'.$this->userid.'/profile';
                }elseif($this->uploadfor == 'default_image'){
                    $wpjobportal_path = $wpjobportal_path . '/default_image/';
                }else{

                }
            }else{
                if($this->uploadfor == 'company'){
                    $wpjobportal_path = $wpjobportal_path . '/employer/comp_'.$this->entitey_id.'/custom_uploads';
                }elseif($this->uploadfor == 'job'){
                    $wpjobportal_path = $wpjobportal_path . '/employer/job_'.$this->entitey_id.'/custom_uploads';
                }elseif($this->uploadfor == 'resume'){
                    $wpjobportal_path = $wpjobportal_path . '/jobseeker/resume_'.$this->entitey_id.'/custom_uploads';
                }elseif($this->uploadfor == 'profile'){
                    $wpjobportal_path = $wpjobportal_path . '/profile/profile_'.$this->entitey_id.'/custom_uploads';
                }else{

                }
            }

            // // test code
            $file = $wpjobportal_path."/index.html";
            if (@ $wp_filesystem->put_contents( $file, '', 0755 ) ) {
            }
            //

            $wpjobportal_userpath = $wpjobportal_path;
            $wpjobportal_array = array(
                'path'   => $wpjobportal_dir['basedir'] . '/' . $wpjobportal_userpath,
                'url'    => $wpjobportal_dir['baseurl'] . '/' . $wpjobportal_userpath,
                'subdir' => '/'. $wpjobportal_userpath,
            ) + $wpjobportal_dir;
            return $wpjobportal_array;
        }else{
            return $wpjobportal_dir;
        }
    }

    function uploadCompanyLogo($wpjobportal_id){
        if(!is_numeric($wpjobportal_id)){
            return false;
        }
        $file_size = wpjobportal::$_config->getConfigurationByConfigName('company_logofilezize');
        if (!function_exists('wp_handle_upload')) {
            do_action('wpjobportal_load_wp_file');
        }
        $this->companyid = $wpjobportal_id;
        $this->uploadfor = 'company';
        // Register our path override.
        add_filter( 'upload_dir', array($this,'wpjobportal_upload_dir'));
        // Do our thing. WordPress will move the file to 'uploads/mycustomdir'.
        $wpjobportal_result = array();
        $filename = '';
        $return = 1;
        $file = array(
                'name'     => sanitize_file_name($_FILES['logo']['name']),
                'type'     => wpjobportal::wpjobportal_sanitizeData($_FILES['logo']['type']),
                'tmp_name' => wpjobportal::wpjobportal_sanitizeData($_FILES['logo']['tmp_name']),
                'error'    => wpjobportal::wpjobportal_sanitizeData($_FILES['logo']['error']),
                'size'     => wpjobportal::wpjobportal_sanitizeData($_FILES['logo']['size'])
                );
        $wpjobportal_uploadfilesize = $file['size'] / 1024; //kb
        $wpjobportal_key = WPJOBPORTALincluder::getJSModel('company')->getMessagekey();
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
        if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ) {
            $creds = request_filesystem_credentials( site_url() );
            wp_filesystem( $creds );
        }
        if($wpjobportal_uploadfilesize > $file_size){
            WPJOBPORTALMessages::setLayoutMessage(esc_html(__('File size is greater then allowed file size', 'wp-job-portal')), 'error',$wpjobportal_key);
            $return = 5;
        }else{
            $filetyperesult = wp_check_filetype(sanitize_file_name($_FILES['logo']['name']));
            if(!empty($filetyperesult['ext']) && !empty($filetyperesult['type'])){
                $wpjobportal_image_file_types = wpjobportal::$_config->getConfigurationByConfigName('image_file_type');
                if(wpjobportalphplib::wpJP_strstr($wpjobportal_image_file_types, $filetyperesult['ext'])){
                    $wpjobportal_result = wp_handle_upload($file, array('test_form' => false));
                    if ( $wpjobportal_result && ! isset( $wpjobportal_result['error'] ) ) {
                        $filename = wpjobportalphplib::wpJP_basename( $wpjobportal_result['file'] );
                        $wpjobportal_imageresult[0] = $wpjobportal_result['file'];
                        $wpjobportal_imageresult[1] = $wpjobportal_result['url'];
                    } else {
                        /**
                         * Error generated by _wp_handle_upload()
                         * @see _wp_handle_upload() in wp-admin/includes/file.php
                         */
						WPJOBPORTALMessages::setLayoutMessage($wpjobportal_result['error'], 'error','company');
                    }
                }else{
                    $return = 5;
                }
            }else{
                $return = 6;
            }

        }
        // Set everything back to normal.
        remove_filter( 'upload_dir', array($this,'wpjobportal_upload_dir'));
        if($return == 1){
            $query = "UPDATE `".wpjobportal::$_db->prefix."wj_portal_companies` SET logofilename = '".esc_sql($filename)."', logoisfile = 1 WHERE id = ".esc_sql($wpjobportal_id);
            wpjobportal::$_db->query($query);

            // index code
            $wpjobportal_datadirectory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
            $wpjobportal_dir = wp_upload_dir();

            $file = $wpjobportal_dir['basedir'].'/'.$wpjobportal_datadirectory."/index.html";

            if (@ $wp_filesystem->put_contents( $file, '', 0755 ) ) {
            }

            $wpjobportal_path = $wpjobportal_datadirectory . '/data';
            $file = $wpjobportal_dir['basedir'].'/'.$wpjobportal_path."/index.html";
            if (@ $wp_filesystem->put_contents( $file, '', 0755 ) ) {
            }

            $file = $wpjobportal_dir['basedir'].'/'.$wpjobportal_path."/employer/index.html";
            if (@ $wp_filesystem->put_contents( $file, '', 0755 ) ) {
            }

            $file = $wpjobportal_dir['basedir'].'/'.$wpjobportal_path."/employer/comp_".$this->companyid."/index.html";
            if (@ $wp_filesystem->put_contents( $file, '', 0755 ) ) {
            }

            $file = $wpjobportal_dir['basedir'].'/'.$wpjobportal_path."/employer/comp_".$this->companyid."/logo/index.html";
            if (@ $wp_filesystem->put_contents( $file, '', 0755 ) ) {
            }
        }else{
            $query = "UPDATE `".wpjobportal::$_db->prefix."wj_portal_companies` SET logofilename = '', logoisfile = -1 WHERE id = ".esc_sql($wpjobportal_id);
            wpjobportal::$_db->query($query);
        }

/*
        // cropingg and resizzing images
        $wpjobportal_wpdir = wp_upload_dir();
        $wpjobportal_data_directory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
        $wpjobportal_path = $wpjobportal_wpdir['basedir'] . '/' . $wpjobportal_data_directory . '/data/employer/comp_' .$wpjobportal_id.'/logo' ;

        if(is_array($wpjobportal_imageresult) && !empty($wpjobportal_imageresult)){
            $file_size = filesize($wpjobportal_imageresult[0]);
            $wpjobportal_temp_file_name = wpjobportalphplib::wpJP_basename( $wpjobportal_imageresult[0] );
            $wpjobportal_imageresult[1] = wpjobportalphplib::wpJP_str_replace($wpjobportal_temp_file_name, '', $wpjobportal_imageresult[1]);
           // to add sufix of image s m l ms
            $file_name = 'jsjb-logo_'.$wpjobportal_temp_file_name;
            $this->createThumbnail($file_name,322,291,$wpjobportal_imageresult[0],$wpjobportal_path);
            // need to store image name in above code.
        }
  */


        return $return;
    }

    function createThumbnail($filename,$wpjobportal_width,$height,$file = null,$wpjobportal_path='',$crop_flag = 0) {
        /* // thumbnail are not need any more 
        $handle = new WPJOBPORTALupload($file);
        $wpjobportal_parts = wpjobportalphplib::wpJP_explode(".",$filename);
        $wpjobportal_extension = end($wpjobportal_parts);
        $filename = wpjobportalphplib::wpJP_str_replace("." . $wpjobportal_extension,"",$filename);
        if ($handle->uploaded) {
            if($crop_flag != 3){
                $handle->file_new_name_body   = $filename;
                $handle->image_resize         = true;
                $handle->image_x              = $wpjobportal_width;
                $handle->image_y              = $height;
                $handle->image_ratio_fill     = true;
                $handle->image_ratio          = true;
            }else{
                $handle->file_auto_rename = false;
                $handle->file_overwrite = true;
            }

            $handle->process($wpjobportal_path);
            @$handle->processed;
            // uncomment this code to check for error.
            // if ($handle->processed) {
            //     // opration successful
            // } else {
            //     echo 'error : ' . $handle->error;
            //     return false;
            // }
        }else{
            echo 'error : ' . $handle->error;
        }
        */
    }


     function uploadUserPhoto($wpjobportal_id){
        if(!is_numeric($wpjobportal_id)){
            return false;
        }
        // Register our path override.
        $this->uploadfor = 'profile';
        $this->userid = $wpjobportal_id;
        $wpjobportal_array = add_filter( 'upload_dir', array($this,'wpjobportal_upload_dir'));
        $return = true;
        $wpjobportal_result = $this->uploadImage(filter_var_array($_FILES['photo']));
        $wpjobportal_profilepath = wpjobportalphplib::wpJP_explode($wpjobportal_result['filename'], $wpjobportal_result['url']);
        if( !isset($wpjobportal_result['error']) ){
            $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_users` SET photo = '".esc_sql($wpjobportal_result['filename'])."' WHERE uid = ".esc_sql($wpjobportal_id);
            wpjobportal::$_db->query($query);

            //crop and store images
            $filename = wpjobportalphplib::wpJP_basename($wpjobportal_result['file']);
            $file_name = 'm_'.$filename;
            $this->createThumbnail($file_name,400,302,$wpjobportal_result['file'],$wpjobportal_profilepath[0]);
            $file_name = 's_'.$filename;
            $this->createThumbnail($file_name,150,150,$wpjobportal_result['file'],$wpjobportal_profilepath[0]);

        }else{
            WPJOBPORTALmessages::setLayoutMessage($wpjobportal_result['error'],'error',WPJOBPORTALincluder::getJSModel('user')->getMessagekey());
            $return = false;
        }

        // Set everything back to normal.
        remove_filter( 'upload_dir', array($this,'wpjobportal_upload_dir'));

        return $return;
    }

    function uploadImage($file){
        $wpjobportal_allowed_types = WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue('image_file_type');
        $wpjobportal_allowed_size = WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue('image_file_size');
        return $this->uploadFile($file, $wpjobportal_allowed_types, $wpjobportal_allowed_size);
    }

    function removeUserPhoto($wpjobportal_uid){
        if(!is_numeric($wpjobportal_uid)){
            return false;
        }
        $wpjobportal_user = WPJOBPORTALincluder::getJSTable('users');

        // to hanld the case of allowing only currrent user to remove his own image
        $current_uid = WPJOBPORTALincluder::getObjectClass('user')->uid();
        if(!current_user_can('manage_options')){ // allow admin
            if(WPJOBPORTALincluder::getJSModel('user')->getUserIDByWPUid($wpjobportal_uid) != $current_uid){
                return false;
            }
        }

        $wpjobportal_user->load(WPJOBPORTALincluder::getObjectClass('user')->uid());
        $filename = $wpjobportal_user->photo;
        if(!empty($filename)){
            $wpjobportal_wpdir = wp_upload_dir();
            $wpjobportal_data_directory = wpjobportal::$_config->getConfigValue('data_directory');
            $filepath = $wpjobportal_wpdir['basedir'] . '/' . $wpjobportal_data_directory . '/data/profile/profile_' . $wpjobportal_uid.'/profile/';
            $wpjobportal_path = $filepath.$filename;
            @wp_delete_file($wpjobportal_path);
            $wpjobportal_path = $filepath.'m_'.$filename;
            @wp_delete_file($wpjobportal_path);
            $wpjobportal_path = $filepath.'s_'.$filename;
            @wp_delete_file($wpjobportal_path);
            $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_users` SET photo = '' WHERE uid = ".esc_sql($wpjobportal_uid);
            wpjobportal::$_db->query($query);
        }
        return true;
    }

    function uploadFile($file, $wpjobportal_allowed_types, $wpjobportal_allowed_size){
        $filetyperesult = wp_check_filetype($file['name']);
        $wpjobportal_allowed_types  = array_map('strtolower', wpjobportalphplib::wpJP_explode(',', $wpjobportal_allowed_types));
        if( !in_array(wpjobportalphplib::wpJP_strtolower($filetyperesult['ext']), $wpjobportal_allowed_types) ){
            return array('error'=>esc_html(__('File ext. is mismatched', 'wp-job-portal')));
        }
        $filesize = $file['size'] / 1024;
        if( $filesize > $wpjobportal_allowed_size ){
            return array('error'=>esc_html(__('File size is greater then allowed file size', 'wp-job-portal')));
        }
        if (!function_exists('wp_handle_upload')) {
            do_action('wpjobportal_load_wp_file');
        }
        $wpjobportal_result = wp_handle_upload($file, array('test_form' => false));
        if(!($wpjobportal_result && !isset($wpjobportal_result['error']))) {
            return $wpjobportal_result;
        }
        $wpjobportal_result['filename'] = wpjobportalphplib::wpJP_basename($wpjobportal_result['file']);
        $wpjobportal_result['ischanged'] = $wpjobportal_result['filename'] == $file['name'] ? 0 : 1;

        //creating index.html files in directories
        /*
        --------Working-----------
        let $wpjobportal_dir['basedir'] = /realestate/wp-admin/uploads
        let $wpjobportal_result['file'] = /realestate/wp-admin/uploads/data/property/images/filename.png
        ---$wpjobportal_dirstr = wpjobportalphplib::wpJP_str_replace('/'.$wpjobportal_result['filename'], '', $wpjobportal_result['file']);
        after above line $wpjobportal_dirstr = /realestate/wp-admin/uploads/data/property/images
        loop 1st Iteration:
            create index.html file in /realestate/wp-admin/uploads/data/property/images
            and changes $wpjobportal_dirstr = /realestate/wp-admin/uploads/data/property
        loop 2nd iteration:
            create index.html file in /realestate/wp-admin/uploads/data/property
            and changes $wpjobportal_dirstr = /realestate/wp-admin/uploads/data
        loop 3rd iteration:
            create index.html file in /realestate/wp-admin/uploads/data
            and changes $wpjobportal_dirstr = /realestate/wp-admin/uploads
        now $wpjobportal_dirstr == $wpjobportal_dir['basedie'], so loop exists
        */
        $wpjobportal_dir = wp_upload_dir();
        $wpjobportal_dirstr = wpjobportalphplib::wpJP_str_replace('/'.$wpjobportal_result['filename'], '', $wpjobportal_result['file']);
        $wpjobportal_i=0;
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
        if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ) {
            $creds = request_filesystem_credentials( site_url() );
            wp_filesystem( $creds );
        }

        do{
            $file = $wpjobportal_dirstr."/index.html";
            if (@ $wp_filesystem->put_contents( $file, '', 0755 ) ) {
            }
            $wpjobportal_dirstr = wpjobportalphplib::wpJP_preg_replace('/\/[^\/]+$/', '', $wpjobportal_dirstr);
            $wpjobportal_i++;
        }while( $wpjobportal_dirstr !== $wpjobportal_dir['basedir'] && $wpjobportal_i<20);

        return $wpjobportal_result;
    }


    function uploadResumePhoto($wpjobportal_id){
        if(!is_numeric($wpjobportal_id)) return false;
        $this->resumeid = $wpjobportal_id;
        $this->uploadfor = 'resumephoto';

        if (!function_exists('wp_handle_upload')) {
            do_action('wpjobportal_load_wp_file');
        }
        $this->companyid = $wpjobportal_id;
        $this->uploadfor = 'resumephoto';
        // Register our path override.
        add_filter( 'upload_dir', array($this,'wpjobportal_upload_dir'));
        // Do our thing. WordPress will move the file to 'uploads/mycustomdir'.
        $wpjobportal_result = array();
        $filename = '';
        $return = true;
        $file = array(
                'name'     => sanitize_file_name($_FILES['photo']['name']),
                'type'     => wpjobportal::wpjobportal_sanitizeData($_FILES['photo']['type']),
                'tmp_name' => wpjobportal::wpjobportal_sanitizeData($_FILES['photo']['tmp_name']),
                'error'    => wpjobportal::wpjobportal_sanitizeData($_FILES['photo']['error']),
                'size'     => wpjobportal::wpjobportal_sanitizeData($_FILES['photo']['size'])
                );
        $filetyperesult = wp_check_filetype(sanitize_file_name($_FILES['photo']['name']));
        if(!empty($filetyperesult['ext']) && !empty($filetyperesult['type'])){
            $wpjobportal_image_file_types = wpjobportal::$_config->getConfigurationByConfigName('image_file_type');
            if(wpjobportalphplib::wpJP_strstr($wpjobportal_image_file_types, $filetyperesult['ext'])){
                $wpjobportal_result = wp_handle_upload($file, array('test_form' => false));
                if ( $wpjobportal_result && ! isset( $wpjobportal_result['error'] ) ) {
                    $filename = wpjobportalphplib::wpJP_basename( $wpjobportal_result['file'] );
                } else {
                    /**
                     * Error generated by _wp_handle_upload()
                     * @see _wp_handle_upload() in wp-admin/includes/file.php
                     */
					WPJOBPORTALMessages::setLayoutMessage($wpjobportal_result['error'], 'error','resume');
                }
            }else{
                $return = null;
            }
        }else{
            $return = 6;
        }
        // Set everything back to normal.
        remove_filter( 'upload_dir', array($this,'wpjobportal_upload_dir'));
        if($return == true){
            $query = "UPDATE `".wpjobportal::$_db->prefix."wj_portal_resume` SET photo = '".esc_sql($filename)."' WHERE id = ".esc_sql($wpjobportal_id);
            wpjobportal::$_db->query($query);

             // index code
            $wpjobportal_datadirectory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
            $wpjobportal_dir = wp_upload_dir();

            if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
            if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ) {
                $creds = request_filesystem_credentials( site_url() );
                wp_filesystem( $creds );
            }


            $file = $wpjobportal_dir['basedir'].'/'.$wpjobportal_datadirectory."/index.html";
            if (@ $wp_filesystem->put_contents( $file, '', 0755 ) ) {
            }
            $wpjobportal_path = $wpjobportal_datadirectory . '/data';
            $file = $wpjobportal_dir['basedir'].'/'.$wpjobportal_path."/index.html";
            if (@ $wp_filesystem->put_contents( $file, '', 0755 ) ) {
            }
            $file = $wpjobportal_dir['basedir'].'/'.$wpjobportal_path."/jobseeker/index.html";
            if (@ $wp_filesystem->put_contents( $file, '', 0755 ) ) {
            }
            $file = $wpjobportal_dir['basedir'].'/'.$wpjobportal_path."/jobseeker/resume_".$wpjobportal_id."/index.html";
            if (@ $wp_filesystem->put_contents( $file, '', 0755 ) ) {
            }
            $file = $wpjobportal_dir['basedir'].'/'.$wpjobportal_path."/jobseeker/resume_".$wpjobportal_id."/photo/index.html";
            if (@ $wp_filesystem->put_contents( $file, '', 0755 ) ) {
            }
        }
        return $return;
    }

    //////////**********To Add Job Seeker PROFILE PHOTO*******///////////////
    function uploadJobSeekerPhoto($wpjobportal_id){
        if(!is_numeric($wpjobportal_id)) return false;
        $this->userid = $wpjobportal_id;
        $this->uploadfor = 'profile';

        if (!function_exists('wp_handle_upload')) {
            do_action('wpjobportal_load_wp_file');
        }
        $this->companyid = $wpjobportal_id;
        $this->uploadfor = 'profile';
        if (!function_exists('wp_handle_upload')) {
            do_action('wpjobportal_load_wp_file');
         }
        // Register our path override.
        add_filter( 'upload_dir', array($this,'wpjobportal_upload_dir'));
        // Do our thing. WordPress will move the file to 'uploads/mycustomdir'.
        $wpjobportal_result = array();
        $filename = '';
        $return = true;
        $file = array(
                'name'     => sanitize_file_name($_FILES['photo']['name']),
                'type'     => wpjobportal::wpjobportal_sanitizeData($_FILES['photo']['type']),
                'tmp_name' => wpjobportal::wpjobportal_sanitizeData($_FILES['photo']['tmp_name']),
                'error'    => wpjobportal::wpjobportal_sanitizeData($_FILES['photo']['error']),
                'size'     => wpjobportal::wpjobportal_sanitizeData($_FILES['photo']['size'])
                );
        $filetyperesult = wp_check_filetype(sanitize_file_name($_FILES['photo']['name']));
        if(!empty($filetyperesult['ext']) && !empty($filetyperesult['type'])){
            $wpjobportal_image_file_types = wpjobportal::$_config->getConfigurationByConfigName('image_file_type');
            if(wpjobportalphplib::wpJP_strstr($wpjobportal_image_file_types, $filetyperesult['ext'])){
                $wpjobportal_result = wp_handle_upload($file, array('test_form' => false));
                if ( $wpjobportal_result && ! isset( $wpjobportal_result['error'] ) ) {
                    $filename = wpjobportalphplib::wpJP_basename( $wpjobportal_result['file'] );
                } else {
                    /**
                     * Error generated by _wp_handle_upload()
                     * @see _wp_handle_upload() in wp-admin/includes/file.php
                     */
                    WPJOBPORTALMessages::setLayoutMessage($wpjobportal_result['error'], 'error','user');
                }
            }else{
                $return = null;
            }
        }else{
            $return = 6;
        }
        // Set everything back to normal.
        remove_filter( 'upload_dir', array($this,'wpjobportal_upload_dir'));
        if($return == true){
            $query = "UPDATE `".wpjobportal::$_db->prefix."wj_portal_users` SET photo = '".esc_sql($filename)."' WHERE id = ".esc_sql($wpjobportal_id);
            wpjobportal::$_db->query($query);

             // index code
            $wpjobportal_datadirectory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
            $wpjobportal_dir = wp_upload_dir();

            if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
            if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ) {
                $creds = request_filesystem_credentials( site_url() );
                wp_filesystem( $creds );
            }

            $file = $wpjobportal_dir['basedir'].'/'.$wpjobportal_datadirectory."/index.html";
            if (@ $wp_filesystem->put_contents( $file, '', 0755 ) ) {
                        }

            $wpjobportal_path = $wpjobportal_datadirectory . '/data';
            $file = $wpjobportal_dir['basedir'].'/'.$wpjobportal_path."/index.html";
            if (@ $wp_filesystem->put_contents( $file, '', 0755 ) ) {
                        }

            $file = $wpjobportal_dir['basedir'].'/'.$wpjobportal_path."/profile/index.html";
            if (@ $wp_filesystem->put_contents( $file, '', 0755 ) ) {
                        }

            $file = $wpjobportal_dir['basedir'].'/'.$wpjobportal_path."/profile/profile_".$wpjobportal_id."/index.html";
            if (@ $wp_filesystem->put_contents( $file, '', 0755 ) ) {
                        }

            $file = $wpjobportal_dir['basedir'].'/'.$wpjobportal_path."/profile/profile_".$wpjobportal_id."/photo/index.html";
            if (@ $wp_filesystem->put_contents( $file, '', 0755 ) ) {
            }

        }
        return $return;
    }


    function uploadResumeFiles($wpjobportal_id){
        if(!is_numeric($wpjobportal_id)) return false;
        $return = true;
        if (!function_exists('wp_handle_upload')) {
            do_action('wpjobportal_load_wp_file');
        }
        $this->resumeid = $wpjobportal_id;
        $this->uploadfor = 'resumefiles';
        // Register our path override.
        add_filter( 'upload_dir', array($this,'wpjobportal_upload_dir'));
        // Do our thing. WordPress will move the file to 'uploads/mycustomdir'.
        $wpjobportal_result = array();
        $filename = '';
        $return = true;
        $wpjobportal_maxfiles = wpjobportal::$_config->getConfigurationByConfigName('document_max_files');
        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumefiles` WHERE resumeid =". esc_sql($wpjobportal_id);
        $wpjobportal_totalfiles = wpjobportal::$_db->get_var($query);
        foreach($_FILES['resumefiles']['name'] AS $wpjobportal_key => $wpjobportal_value){
            if ($wpjobportal_maxfiles > $wpjobportal_totalfiles) {
                if($_FILES['resumefiles']['size'][$wpjobportal_key] > 0){
                    $file = array(
                            'name'     => sanitize_file_name($_FILES['resumefiles']['name'][$wpjobportal_key]),
                            'type'     => wpjobportal::wpjobportal_sanitizeData($_FILES['resumefiles']['type'][$wpjobportal_key]),
                            'tmp_name' => wpjobportal::wpjobportal_sanitizeData($_FILES['resumefiles']['tmp_name'][$wpjobportal_key]),
                            'error'    => wpjobportal::wpjobportal_sanitizeData($_FILES['resumefiles']['error'][$wpjobportal_key]),
                            'size'     => wpjobportal::wpjobportal_sanitizeData($_FILES['resumefiles']['size'][$wpjobportal_key])
                            );
                    $filetyperesult = wp_check_filetype(sanitize_file_name($_FILES['resumefiles']['name'][$wpjobportal_key]));
                    if(!empty($filetyperesult['ext']) && !empty($filetyperesult['type'])){
                        $document_file_types = wpjobportal::$_config->getConfigurationByConfigName('document_file_type');
                        if(wpjobportalphplib::wpJP_strstr($document_file_types, $filetyperesult['ext'])){
                            $wpjobportal_result = wp_handle_upload($file, array('test_form' => false));
                            if ( $wpjobportal_result && ! isset( $wpjobportal_result['error'] ) ) {
                                $filename = wpjobportalphplib::wpJP_basename( $wpjobportal_result['file'] );
                                $wpjobportal_row = WPJOBPORTALincluder::getJSTable('resumefile');
                                $cols = array();
                                $cols['id'] = '';
                                $cols['resumeid'] = $wpjobportal_id;
                                $cols['filename'] = $filename;
                                $cols['filetype'] = $file['type'];
                                $cols['filesize'] = $file['size'];
                                $cols['created'] = gmdate('Y-m-d H:i:s');
                                $cols = wpjobportal::wpjobportal_sanitizeData($cols);
                                $wpjobportal_row->bind($cols);
                                $wpjobportal_row->store();
                                $wpjobportal_totalfiles++; //increment file has been uploaded
                            } else {
                                /**
                                 * Error generated by _wp_handle_upload()
                                 * @see _wp_handle_upload() in wp-admin/includes/file.php
                                 */
								WPJOBPORTALMessages::setLayoutMessage($wpjobportal_result['error'], 'error','resume');
                            }
                        }
                    }else{
                        $return = 6;
                    }
                }
            }
        }

             // index code
        $wpjobportal_datadirectory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
        $wpjobportal_dir = wp_upload_dir();

        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
        if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ) {
            $creds = request_filesystem_credentials( site_url() );
            wp_filesystem( $creds );
        }
        $wpjobportal_path = $wpjobportal_datadirectory . '/data';

        $file = $wpjobportal_dir['basedir'].'/'.$wpjobportal_path."/jobseeker/resume_".$wpjobportal_id."/photo/index.html";
        if (@ $wp_filesystem->put_contents( $file, '', 0755 ) ) {
        }
        // Set everything back to normal.
        remove_filter( 'upload_dir', array($this,'wpjobportal_upload_dir'));
        return $return;
    }

    function storeCustomUploadFile($wpjobportal_id,$wpjobportal_field,$wpjobportal_uploadfor){
        if(!isset($_FILES[$wpjobportal_field])){
            return;
        }
        if (!function_exists('wp_handle_upload')) {
            do_action('wpjobportal_load_wp_file');
        }
        $filesize = wpjobportal::$_config->getConfigurationByConfigName('document_file_size');
        $this->entitey_id = $wpjobportal_id;
        $this->uploadfor = $wpjobportal_uploadfor;
        $this->custom_field_upload = 1;
        // Register our path override.
        add_filter( 'upload_dir', array($this,'wpjobportal_upload_dir'));
        // Do our thing. WordPress will move the file to 'uploads/mycustomdir'.
        $wpjobportal_result = array();
        $file = array(
            'name'     => sanitize_file_name($_FILES[$wpjobportal_field]['name']),
            'type'     => wpjobportal::wpjobportal_sanitizeData($_FILES[$wpjobportal_field]['type']),
            'tmp_name' => wpjobportal::wpjobportal_sanitizeData($_FILES[$wpjobportal_field]['tmp_name']),
            'error'    => wpjobportal::wpjobportal_sanitizeData($_FILES[$wpjobportal_field]['error']),
            'size'     => wpjobportal::wpjobportal_sanitizeData($_FILES[$wpjobportal_field]['size'])
        ); // wpjobportal_sanitizeData() function uses wordpress santize functions
        $wpjobportal_uploadfilesize = wpjobportal::wpjobportal_sanitizeData($_FILES[$wpjobportal_field]['size']) / 1024; //kb // wpjobportal_sanitizeData() function uses wordpress santize functions
        if($wpjobportal_uploadfilesize > $filesize){
            $wpjobportal_error_msg = esc_html(__('Error file size too large', 'wp-job-portal'));
            WPJOBPORTALmessages::setLayoutMessage($wpjobportal_error_msg,'error',WPJOBPORTALincluder::getJSModel($wpjobportal_uploadfor)->getMessagekey());
            return;
        }
        $filename = '';
        $filetyperesult = wp_check_filetype(sanitize_file_name($_FILES[$wpjobportal_field]['name']));
        if(!empty($filetyperesult['ext']) && !empty($filetyperesult['type'])){
            $wpjobportal_image_file_type = wpjobportal::$_config->getConfigurationByConfigName('image_file_type');
            $document_file_type = wpjobportal::$_config->getConfigurationByConfigName('document_file_type');

            $fileext  = '';
            $fileext .= $document_file_type.','.$wpjobportal_image_file_type;

            $fileext = wpjobportalphplib::wpJP_explode(',', $fileext);
            $fileext = array_unique($fileext);
            $fileext = implode(',', $fileext);
            //$wpjobportal_image_file_types = WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue('file_extension');
            $wpjobportal_image_file_types = $fileext;
            if(wpjobportalphplib::wpJP_strstr($wpjobportal_image_file_types, $filetyperesult['ext'])){

                $wpjobportal_result = wp_handle_upload($file, array('test_form' => false));
                if (isset( $wpjobportal_result['error'] ) ) {
                    /**
                     * Error generated by _wp_handle_upload()
                     * @see _wp_handle_upload() in wp-admin/includes/file.php
                     */
                    $wpjobportal_error_msg = $wpjobportal_result['error'];
                    WPJOBPORTALmessages::setLayoutMessage($wpjobportal_error_msg,'error',WPJOBPORTALincluder::getJSModel($wpjobportal_uploadfor)->getMessagekey());
                }else{
                    $filename = wpjobportalphplib::wpJP_basename( $wpjobportal_result['file'] );
                }
            }
        }
        // Set everything back to normal.
        remove_filter( 'upload_dir', array($this,'wpjobportal_upload_dir'));
        //to store name of custom file in params
        if($filename != ''){ // to avoid calling this function if there is no file name
            WPJOBPORTALincluder::getObjectClass('customfields')->storeUploadFieldValueInParams($wpjobportal_id,$filename,$wpjobportal_field,$this->uploadfor);
        }
    }

    function uploadDeafultImage(){
        if (!function_exists('wp_handle_upload')) {
            do_action('wpjobportal_load_wp_file');
        }
        $file_size = wpjobportal::$_config->getConfigurationByConfigName('image_file_size');;
        $this->uploadfor = 'default_image';
        // Register our path override.
        add_filter( 'upload_dir', array($this,'wpjobportal_upload_dir'));
        // Do our thing. WordPress will move the file to 'uploads/mycustomdir'.
        $wpjobportal_result = array();
        $filename = '';
        $return = 1;
        $file = array(
                'name'     => sanitize_file_name($_FILES['default_image']['name']),
                'type'     => wpjobportal::wpjobportal_sanitizeData($_FILES['default_image']['type']),
                'tmp_name' => wpjobportal::wpjobportal_sanitizeData($_FILES['default_image']['tmp_name']),
                'error'    => wpjobportal::wpjobportal_sanitizeData($_FILES['default_image']['error']),
                'size'     => wpjobportal::wpjobportal_sanitizeData($_FILES['default_image']['size'])
                );
        $wpjobportal_uploadfilesize = $file['size'] / 1024; //kb
        $wpjobportal_key = WPJOBPORTALincluder::getJSModel('configuration')->getMessagekey();
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
        if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ) {
            $creds = request_filesystem_credentials( site_url() );
            wp_filesystem( $creds );
        }
        if($wpjobportal_uploadfilesize > $file_size){
            WPJOBPORTALMessages::setLayoutMessage(esc_html(__('File size is greater then allowed file size', 'wp-job-portal')), 'error',$wpjobportal_key);
            $return = 5;
        }else{
            $filetyperesult = wp_check_filetype(sanitize_file_name($_FILES['default_image']['name']));
            if(!empty($filetyperesult['ext']) && !empty($filetyperesult['type'])){
                $wpjobportal_image_file_types = wpjobportal::$_config->getConfigurationByConfigName('image_file_type');
                if(wpjobportalphplib::wpJP_strstr($wpjobportal_image_file_types, $filetyperesult['ext'])){
                    $wpjobportal_result = wp_handle_upload($file, array('test_form' => false));
                    if ( $wpjobportal_result && ! isset( $wpjobportal_result['error'] ) ) {
                        $filename = wpjobportalphplib::wpJP_basename( $wpjobportal_result['file'] );
                        $wpjobportal_imageresult[0] = $wpjobportal_result['file'];
                        $wpjobportal_imageresult[1] = $wpjobportal_result['url'];
                    } else {
                        /**
                         * Error generated by _wp_handle_upload()
                         * @see _wp_handle_upload() in wp-admin/includes/file.php
                         */
                        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_result['error'], 'error',$wpjobportal_key);
                    }
                }else{
                    $return = 5;
                }
            }else{
                $return = 6;
            }

        }
        // Set everything back to normal.
        remove_filter( 'upload_dir', array($this,'wpjobportal_upload_dir'));
        if($return == 1){
            $query = "UPDATE `".wpjobportal::$_db->prefix."wj_portal_config` SET configvalue = '".esc_sql($filename)."' WHERE configname = 'default_image'";
            wpjobportal::$_db->query($query);

            // index code
            $wpjobportal_datadirectory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
            $wpjobportal_dir = wp_upload_dir();

            $file = $wpjobportal_dir['basedir'].'/'.$wpjobportal_datadirectory."/index.html";

            if (@ $wp_filesystem->put_contents( $file, '', 0755 ) ) {
            }

            $wpjobportal_path = $wpjobportal_datadirectory . '/data';
            $file = $wpjobportal_dir['basedir'].'/'.$wpjobportal_path."/index.html";
            if (@ $wp_filesystem->put_contents( $file, '', 0755 ) ) {
            }

            $file = $wpjobportal_dir['basedir'].'/'.$wpjobportal_path."/default_image/index.html";
            if (@ $wp_filesystem->put_contents( $file, '', 0755 ) ) {
            }

        }else{
            $query = "UPDATE `".wpjobportal::$_db->prefix."wj_portal_config` SET configvalue = '' WHERE configname = 'default_image'";
            wpjobportal::$_db->query($query);
        }
        return $return;
    }

}

?>
