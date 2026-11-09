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

    /**
     * One uploaded file from $_FILES, each part cleaned with WordPress's own
     * functions, in the shape wp_handle_upload() expects.
     *
     * @param string   $wpjobportal_field The form field it was sent under.
     * @param int|null $wpjobportal_index Which file of a multiple upload.
     * @return array|null Null when nothing was sent under that name.
     */
    private function uploadedFile($wpjobportal_field, $wpjobportal_index = null) {
        return WPJOBPORTALrequest::file($wpjobportal_field, $wpjobportal_index);
    }

    /**
     * The indexes of a multiple upload's files.
     *
     * @param string $wpjobportal_field The form field they were sent under.
     * @return array
     */
    private function uploadedFileIndexes($wpjobportal_field) {
        return WPJOBPORTALrequest::fileIndexes($wpjobportal_field);
    }

    function wpjobportal_upload_dir( $wpjobportal_dir ) {
        $wpjobportal_form_request = WPJOBPORTALrequest::getVar('form_request');
        if($wpjobportal_form_request == 'wpjobportal' || ($this->uploadfor == 'resumephoto' || $this->uploadfor == 'resumefiles'||$this->uploadfor=='profile')){ // Patch b/c of resume is ajax base
            $wpjobportal_datadirectory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
            // test code
            if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
            $wp_filesystem = wpjobportal::wpjobportal_filesystem();

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

            // A blank index in every level below the data folder (employer/,
            // comp_1/, comp_1/logo/ ...), so none of them can be listed. The
            // path used to be relative, so this write never landed.
            $wpjobportal_index_dir = untrailingslashit($wpjobportal_dir['basedir'] . '/' . $wpjobportal_path);
            if (wp_mkdir_p($wpjobportal_index_dir)) {
                $wpjobportal_index_root = $wpjobportal_dir['basedir'] . '/' . $wpjobportal_datadirectory;
                for ($d = $wpjobportal_index_dir; strpos($d, $wpjobportal_index_root) === 0; $d = dirname($d)) {
                    if (!is_file($d . '/index.html')) {
                        $wp_filesystem->put_contents($d . '/index.html', '', FS_CHMOD_FILE);
                    }
                }
            }

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

    /**
     * Whether the image posted in $field would be accepted: the type checks the uploads below make
     * (wp_check_filetype + the image types setting) and, when a size setting is named, its limit in KB.
     * Callers check this before removing the current image, so a refused file (evil.php, a PDF as a
     * logo, a file too big) no longer leaves the record with no image at all.
     *
     * @return int 1 = acceptable, 5 = too big, 6 = not an allowed image type.
     */
    function checkImageUpload($wpjobportal_field, $wpjobportal_size_config = '') {
        $file = $this->uploadedFile($wpjobportal_field);
        if (empty($file) || empty($file['name'])) {
            return 6;
        }
        if ($wpjobportal_size_config != '') {
            $wpjobportal_limit = wpjobportal::$_config->getConfigurationByConfigName($wpjobportal_size_config);
            if (is_numeric($wpjobportal_limit) && ($file['size'] / 1024) > $wpjobportal_limit) {
                return 5;
            }
        }
        $filetyperesult = wp_check_filetype($file['name']);
        if (empty($filetyperesult['ext']) || empty($filetyperesult['type'])) {
            return 6;
        }
        $wpjobportal_image_file_types = wpjobportal::$_config->getConfigurationByConfigName('image_file_type');
        return WPJOBPORTAL_File_Policy_Service::is_extension_allowed($filetyperesult['ext'], $wpjobportal_image_file_types) ? 1 : 6;
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
        $file = $this->uploadedFile('logo');
        $wpjobportal_uploadfilesize = $file['size'] / 1024; //kb
        $wpjobportal_key = WPJOBPORTALincluder::getJSModel('company')->getMessagekey();
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
        $wp_filesystem = wpjobportal::wpjobportal_filesystem();
        if($wpjobportal_uploadfilesize > $file_size){
            WPJOBPORTALMessages::setLayoutMessage(esc_html(__('File size is greater then allowed file size', 'wp-job-portal')), 'error',$wpjobportal_key);
            $return = 5;
        }else{
            $filetyperesult = wp_check_filetype($file['name']);
            if(!empty($filetyperesult['ext']) && !empty($filetyperesult['type'])){
                $wpjobportal_image_file_types = wpjobportal::$_config->getConfigurationByConfigName('image_file_type');
                if(WPJOBPORTAL_File_Policy_Service::is_extension_allowed($filetyperesult['ext'], $wpjobportal_image_file_types)){
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
            $query = "UPDATE `".wpjobportal::$_db->prefix."wj_portal_companies` SET logofilename = '".esc_sql($filename)."', logoisfile = 1 WHERE id = " . (int) ($wpjobportal_id);
            wpjobportal::$_db->query($query);

            // --- NEW HOOK INTEGRATION START ---
            // Blueprint: wpjobportal_file_uploaded
            // Fires when a company logo is successfully uploaded, enabling S3 cloud offloading.
            do_action('wpjobportal_file_uploaded', $wpjobportal_imageresult[0], 'company_logo', $wpjobportal_id);
            // --- NEW HOOK INTEGRATION END ---

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
            $query = "UPDATE `".wpjobportal::$_db->prefix."wj_portal_companies` SET logofilename = '', logoisfile = -1 WHERE id = " . (int) ($wpjobportal_id);
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
        $wpjobportal_result = $this->uploadImage($this->uploadedFile('photo'));
        $wpjobportal_profilepath = wpjobportalphplib::wpJP_explode($wpjobportal_result['filename'], $wpjobportal_result['url']);
        if( !isset($wpjobportal_result['error']) ){
            $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_users` SET photo = '".esc_sql($wpjobportal_result['filename'])."' WHERE uid = " . (int) ($wpjobportal_id);
            wpjobportal::$_db->query($query);

            // --- NEW HOOK INTEGRATION START ---
            // Blueprint: wpjobportal_file_uploaded
            do_action('wpjobportal_file_uploaded', $wpjobportal_result['file'], 'user_photo', $wpjobportal_id);
            // --- NEW HOOK INTEGRATION END ---

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
        // remove traversal
        $filename = wpjobportalphplib::wpJP_clean_file_path($filename);

        // Sanitize filename safely
        $filename = sanitize_file_name($filename);

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
            $query = "UPDATE `" . wpjobportal::$_db->prefix . "wj_portal_users` SET photo = '' WHERE uid = " . (int) ($wpjobportal_uid);
            wpjobportal::$_db->query($query);
        }
        return true;
    }

    function uploadFile($file, $wpjobportal_allowed_types, $wpjobportal_allowed_size){
        $filetyperesult = wp_check_filetype($file['name']);
        if(!WPJOBPORTAL_File_Policy_Service::is_extension_allowed($filetyperesult['ext'], $wpjobportal_allowed_types)){
            return array('error'=>esc_html(__('This file type is not allowed', 'wp-job-portal')));
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
        $wp_filesystem = wpjobportal::wpjobportal_filesystem();

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
        $file = $this->uploadedFile('photo');
        $filetyperesult = wp_check_filetype($file['name']);
        if(!empty($filetyperesult['ext']) && !empty($filetyperesult['type'])){
            $wpjobportal_image_file_types = wpjobportal::$_config->getConfigurationByConfigName('image_file_type');
            if(WPJOBPORTAL_File_Policy_Service::is_extension_allowed($filetyperesult['ext'], $wpjobportal_image_file_types)){
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
        if($return === true && $filename != ''){ // 6 == true and a failed upload both used to write an empty name over the photo
            $query = "UPDATE `".wpjobportal::$_db->prefix."wj_portal_resume` SET photo = '".esc_sql($filename)."' WHERE id = " . (int) ($wpjobportal_id);
            wpjobportal::$_db->query($query);

            // --- NEW HOOK INTEGRATION START ---
            // Blueprint: wpjobportal_file_uploaded
            do_action('wpjobportal_file_uploaded', $wpjobportal_result['file'], 'resume_photo', $wpjobportal_id);
            // --- NEW HOOK INTEGRATION END ---

             // index code
            $wpjobportal_datadirectory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
            $wpjobportal_dir = wp_upload_dir();

            if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
            $wp_filesystem = wpjobportal::wpjobportal_filesystem();


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
        $file = $this->uploadedFile('photo');
        $filetyperesult = wp_check_filetype($file['name']);
        if(!empty($filetyperesult['ext']) && !empty($filetyperesult['type'])){
            $wpjobportal_image_file_types = wpjobportal::$_config->getConfigurationByConfigName('image_file_type');
            if(WPJOBPORTAL_File_Policy_Service::is_extension_allowed($filetyperesult['ext'], $wpjobportal_image_file_types)){
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
        if($return === true && $filename != ''){ // 6 == true and a failed upload both used to write an empty name over the photo
            $query = "UPDATE `".wpjobportal::$_db->prefix."wj_portal_users` SET photo = '".esc_sql($filename)."' WHERE id = " . (int) ($wpjobportal_id);
            wpjobportal::$_db->query($query);

            // --- NEW HOOK INTEGRATION START ---
            // Blueprint: wpjobportal_file_uploaded
            do_action('wpjobportal_file_uploaded', $wpjobportal_result['file'], 'jobseeker_photo', $wpjobportal_id);
            // --- NEW HOOK INTEGRATION END ---

             // index code
            $wpjobportal_datadirectory = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
            $wpjobportal_dir = wp_upload_dir();

            if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
            $wp_filesystem = wpjobportal::wpjobportal_filesystem();

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
        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_resumefiles` WHERE resumeid =" . (int) ($wpjobportal_id);
        $wpjobportal_totalfiles = wpjobportal::$_db->get_var($query);
        foreach($this->uploadedFileIndexes('resumefiles') AS $wpjobportal_key){
            if ($wpjobportal_maxfiles > $wpjobportal_totalfiles) {
                $file = $this->uploadedFile('resumefiles', $wpjobportal_key);
                if($file && $file['size'] > 0){
                    // S-4: the browser's limit was the only one
                    $wpjobportal_max_kb = (int) wpjobportal::$_config->getConfigurationByConfigName('document_file_size');
                    if ($wpjobportal_max_kb > 0 && $file['size'] / 1024 > $wpjobportal_max_kb) {
                        /* translators: %d: the largest allowed file size in KB */
                        $this->refuseResumeFile($file['name'], sprintf(__('it is larger than %d KB.', 'wp-job-portal'), $wpjobportal_max_kb));
                        continue;
                    }
                    $filetyperesult = wp_check_filetype($file['name']);
                    if(!empty($filetyperesult['ext']) && !empty($filetyperesult['type'])){
                        $document_file_types = wpjobportal::$_config->getConfigurationByConfigName('document_file_type');
                        if(WPJOBPORTAL_File_Policy_Service::is_extension_allowed($filetyperesult['ext'], $document_file_types)){
                            $wpjobportal_result = wp_handle_upload($file, array('test_form' => false));
                            if ( $wpjobportal_result && ! isset( $wpjobportal_result['error'] ) ) {
                                // S-4: the database keeps a name of its own, the disk a random one
                                $filename = WPJOBPORTAL_Resume_File_Store::unique_name($wpjobportal_id, wpjobportalphplib::wpJP_basename( $wpjobportal_result['file'] ), true); // the plain file is this one
                                $wpjobportal_hidden_path = dirname($wpjobportal_result['file']) . '/' . bin2hex(random_bytes(16)) . '_' . $filename;
                                // Deny rules first, so the file is never in an open folder
                                WPJOBPORTAL_Resume_File_Store::protect_folders(dirname($wpjobportal_result['file']));
                                if (!WPJOBPORTAL_Resume_File_Store::move($wpjobportal_result['file'], $wpjobportal_hidden_path)) {
                                    // A CV left under its plain name would be guessable and
                                    // might not match the name saved below: refuse it instead
                                    wp_delete_file($wpjobportal_result['file']);
                                    $this->refuseResumeFile($file['name'], __('it could not be stored safely. Please try again.', 'wp-job-portal'));
                                    continue;
                                }
                                $wpjobportal_result['file'] = $wpjobportal_hidden_path;
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

                                // --- NEW HOOK INTEGRATION START ---
                                // Blueprint: wpjobportal_file_uploaded
                                // Triggers when a resume file (PDF/Doc) is saved, allowing immediate cloud offloading.
                                do_action('wpjobportal_file_uploaded', $wpjobportal_result['file'], 'resume_file', $wpjobportal_row->id);
                                // --- NEW HOOK INTEGRATION END ---

                            } else {
                                /**
                                 * Error generated by _wp_handle_upload()
                                 * @see _wp_handle_upload() in wp-admin/includes/file.php
                                 */
                                $wpjobportal_real = wp_check_filetype_and_ext($file['tmp_name'], $file['name']);
                                $this->refuseResumeFile($file['name'], empty($wpjobportal_real['ext']) ? $this->resumeFileTypesText(true) : $wpjobportal_result['error']);
                            }
                        }else{
                            $return = 6; // a known type that is not in the allowed document types
                            $this->refuseResumeFile($file['name'], $this->resumeFileTypesText());
                        }
                    }else{
                        $return = 6;
                        $this->refuseResumeFile($file['name'], $this->resumeFileTypesText());
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
        $wp_filesystem = wpjobportal::wpjobportal_filesystem();
        $wpjobportal_path = $wpjobportal_datadirectory . '/data';

        $file = $wpjobportal_dir['basedir'].'/'.$wpjobportal_path."/jobseeker/resume_".$wpjobportal_id."/photo/index.html";
        if (@ $wp_filesystem->put_contents( $file, '', 0755 ) ) {
        }
        // Set everything back to normal.
        remove_filter( 'upload_dir', array($this,'wpjobportal_upload_dir'));
        return $return;
    }

    /**
     * One message per refused CV that says the rest of the resume was kept.
     * WordPress's own refusal ("not allowed to upload this file type") sat
     * next to "Resume has been successfully saved" and read as a contradiction;
     * it also refuses a renamed file, so the allowed types are the useful part.
     */
    private function refuseResumeFile($name, $reason) {
        /* translators: 1: the file's name, 2: why it was refused */
        $message = sprintf(__('The resume was saved, but the file "%1$s" was not added: %2$s', 'wp-job-portal'), wpjobportalphplib::wpJP_basename((string) $name), $reason);
        WPJOBPORTALMessages::setLayoutMessage(esc_html($message), 'error', WPJOBPORTALincluder::getJSModel('resume')->getMessagekey());
    }

    private function resumeFileTypesText($mismatch = false) {
        $types = wpjobportal::$_config->getConfigurationByConfigName('document_file_type');
        $types = implode(', ', array_filter(array_map('trim', explode(',', (string) $types))));
        $reason = $mismatch
            ? __('its content does not match its file extension.', 'wp-job-portal')
            : __('this file type is not accepted.', 'wp-job-portal');
        /* translators: %s: allowed file types, e.g. "pdf, doc, docx" */
        return $reason . ' ' . sprintf(__('Accepted types: %s.', 'wp-job-portal'), $types);
    }

    function storeCustomUploadFile($wpjobportal_id,$wpjobportal_field,$wpjobportal_uploadfor){
        if (null === $this->uploadedFile($wpjobportal_field)) {
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
        $file = $this->uploadedFile($wpjobportal_field);
        $wpjobportal_uploadfilesize = $file['size'] / 1024; //kb // wpjobportal_sanitizeData() function uses wordpress santize functions
        if($wpjobportal_uploadfilesize > $filesize){
            $wpjobportal_error_msg = esc_html(__('Error file size too large', 'wp-job-portal'));
            WPJOBPORTALmessages::setLayoutMessage($wpjobportal_error_msg,'error',WPJOBPORTALincluder::getJSModel($wpjobportal_uploadfor)->getMessagekey());
            return;
        }
        $filename = '';
        $filetyperesult = wp_check_filetype($file['name']);
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
            if(WPJOBPORTAL_File_Policy_Service::is_extension_allowed($filetyperesult['ext'], $wpjobportal_image_file_types)){

                $wpjobportal_result = wp_handle_upload($file, array('test_form' => false));
                if (isset( $wpjobportal_result['error'] ) ) {
                    /**
                     * Error generated by _wp_handle_upload()
                     * @see _wp_handle_upload() in wp-admin/includes/file.php
                     */
                    $wpjobportal_error_msg = $wpjobportal_result['error'];
                    WPJOBPORTALmessages::setLayoutMessage($wpjobportal_error_msg,'error',WPJOBPORTALincluder::getJSModel($wpjobportal_uploadfor)->getMessagekey());
                }else{
                    // S-4: an unguessable name on disk; the params keep the plain one
                    $filename = WPJOBPORTAL_Resume_File_Store::hide_custom_upload($wpjobportal_result['file']);
                    if ($filename === '') {
                        WPJOBPORTALmessages::setLayoutMessage(esc_html(__('The file could not be stored safely. Please try again.', 'wp-job-portal')), 'error', WPJOBPORTALincluder::getJSModel($wpjobportal_uploadfor)->getMessagekey());
                    }
                    $wpjobportal_result['file'] = WPJOBPORTAL_Resume_File_Store::find(dirname($wpjobportal_result['file']), $filename);
                }
            }
        }
        // Set everything back to normal.
        remove_filter( 'upload_dir', array($this,'wpjobportal_upload_dir'));
        //to store name of custom file in params
        if($filename != ''){ // to avoid calling this function if there is no file name
            WPJOBPORTALincluder::getObjectClass('customfields')->storeUploadFieldValueInParams($wpjobportal_id,$filename,$wpjobportal_field,$this->uploadfor);

            // --- NEW HOOK INTEGRATION START ---
            // Blueprint: wpjobportal_file_uploaded
            do_action('wpjobportal_file_uploaded', $wpjobportal_result['file'], 'custom_field_'.$this->uploadfor, $wpjobportal_id);
            // --- NEW HOOK INTEGRATION END ---
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
        $file = $this->uploadedFile('default_image');
        $wpjobportal_uploadfilesize = $file['size'] / 1024; //kb
        $wpjobportal_key = WPJOBPORTALincluder::getJSModel('configuration')->getMessagekey();
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
        $wp_filesystem = wpjobportal::wpjobportal_filesystem();
        if($wpjobportal_uploadfilesize > $file_size){
            WPJOBPORTALMessages::setLayoutMessage(esc_html(__('File size is greater then allowed file size', 'wp-job-portal')), 'error',$wpjobportal_key);
            $return = 5;
        }else{
            $filetyperesult = wp_check_filetype($file['name']);
            if(!empty($filetyperesult['ext']) && !empty($filetyperesult['type'])){
                $wpjobportal_image_file_types = wpjobportal::$_config->getConfigurationByConfigName('image_file_type');
                if(WPJOBPORTAL_File_Policy_Service::is_extension_allowed($filetyperesult['ext'], $wpjobportal_image_file_types)){
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
