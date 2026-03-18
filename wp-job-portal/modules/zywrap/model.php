<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALzywrapModel {

    public $zywrap_import_counts =  [
                'categories' => [
                    'imported' => 0,
                    'skipped'  => 0,
                    'failed'   => 0,
                ],
                'usecases' => [
                    'imported' => 0,
                    'skipped'  => 0,
                    'failed'   => 0,
                ],
                'languages' => [
                    'imported' => 0,
                    'skipped'  => 0,
                    'failed'   => 0,
                ],'aimodels' => [
                    'imported' => 0,
                    'skipped'  => 0,
                    'failed'   => 0,
                ],'blocktemplates' => [
                    'imported' => 0,
                    'skipped'  => 0,
                    'failed'   => 0,
                ],'wrappers' => [
                    'imported' => 0,
                    'skipped'  => 0,
                    'failed'   => 0,
                ],
            ];

    private $max_per_run = 10000;

    // Gets the message key for admin notices
    function getMessagekey() {
        $wpjobportal_key = 'zywrap';
        if (is_admin()) {
            $wpjobportal_key = 'admin_' . $wpjobportal_key;
        }
        return $wpjobportal_key;
    }

    /**
     * Helper function to record all API calls
     */
    private function log_api_call($wpjobportal_action, $wpjobportal_status, $wpjobportal_args = array()) {
        global $wpdb;
        $wpjobportal_table_name = $wpdb->prefix . 'wj_portal_zywrap_logs';

        // Get the token/usage array passed from the calling function
        $usage_data = isset($wpjobportal_args['token_data']) ? $wpjobportal_args['token_data'] : null;

        $wpjobportal_data = array(
            'timestamp'     => current_time('mysql'),
            'user_id'       => get_current_user_id(),
            'action'        => $wpjobportal_action,
            'status'        => $wpjobportal_status,
            'wrapper_code'  => $wpjobportal_args['wrapper_code'] ?? null,
            'model_code'    => $wpjobportal_args['model_code'] ?? null,
            'http_code'     => $wpjobportal_args['http_code'] ?? null,
            'error_message' => $wpjobportal_args['error_message'] ?? null,

            // Read from the passed $usage_data array
            'prompt_tokens'     => isset($usage_data['prompt_tokens']) ? (int)$usage_data['prompt_tokens'] : 0,
            'completion_tokens' => isset($usage_data['completion_tokens']) ? (int)$usage_data['completion_tokens'] : 0,
            'total_tokens'      => isset($usage_data['total_tokens']) ? (int)$usage_data['total_tokens'] : 0,

            // Store the full usage JSON for debugging/history
            'token_data'    => $usage_data ? json_encode($usage_data) : null,
        );

        $wpdb->insert($wpjobportal_table_name, $wpjobportal_data);
    }

    function storeZywrapApiKey($api_key) {
        $return_array = [];
        $return_array['status'] = 'error';
        $return_array['response'] = __('An unknown error occurred.','wp-job-portal');
        // only admin user can do this
        if (!current_user_can('manage_options')) {
            return false;
        }
        if(!empty($api_key)){
            // store api key in options
            update_option('wpjobportal_zywrap_api_key', $api_key);

            // code to test the api key
            $wpjobportal_url = 'https://api.zywrap.com/v1/key/check';
            $wpjobportal_args = array(
                'method'  => 'POST',
                'timeout' => 120,
                'headers' => array(
                    'Content-Type' => 'application/json'
                ),
                'body'    => json_encode(array('apiKey' => $api_key))
            );

            $response = wp_remote_post($wpjobportal_url, $wpjobportal_args);

            if (is_wp_error($response)) {
                $return_array['status'] = 'error';
                $return_array['response'] = $response->get_error_message();
                return $return_array;
            }

            $http_code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            $wpjobportal_data = json_decode($body, true);

            if ($http_code == 200) {
                // Log the successful or limited key check
                $this->log_api_call('key_check', $wpjobportal_data['status'], ['http_code' => $http_code]);
                $return_array['status'] = $wpjobportal_data['status'];
                $return_array['response'] = $wpjobportal_data['message'];
            } else {
                // Log the invalid key check
                $this->log_api_call('key_check', $wpjobportal_data['status'] ?? 'error', [
                    'http_code' => $http_code,
                    'error_message' => $wpjobportal_data['message'] ?? __('Invalid Key','wp-job-portal')
                ]);
                $return_array['status'] = $wpjobportal_data['status'] ?? 'error';
                $return_array['response'] = $wpjobportal_data['message'] ?? __('An unknown error occurred.','wp-job-portal');
            }
        }
        return $return_array;
    }


    /**
     * AJAX Function: Calls the /v1/key/check endpoint
     */
    function checkZywrapApiKey() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }

        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($wpjobportal_nonce, 'check-zywrap-key')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }

        $api_key = WPJOBPORTALrequest::getVar('api_key','post');
        if (empty($api_key)) {
            wp_send_json_error(array('message' => 'API Key cannot be empty.'));
        }

        $wpjobportal_url = 'https://api.zywrap.com/v1/key/check';
        $wpjobportal_args = array(
            'method'  => 'POST',
            'timeout' => 15,
            'headers' => array(
                'Content-Type' => 'application/json'
            ),
            'body'    => json_encode(array('apiKey' => $api_key))
        );

        // Use WordPress's built-in function to make the API call
        $response = wp_remote_post($wpjobportal_url, $wpjobportal_args);

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => 'Error: ' . $response->get_error_message()));
        }

        $http_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $wpjobportal_data = json_decode($body, true);

        if ($http_code == 200) {
            // Log the successful or limited key check
            $this->log_api_call('key_check', $wpjobportal_data['status'], ['http_code' => $http_code]);
            wp_send_json_success(array(
                'status'  => $wpjobportal_data['status'],
                'message' => $wpjobportal_data['message']
            ));
        } else {
            // Log the invalid key check
            $this->log_api_call('key_check', $wpjobportal_data['status'] ?? 'error', [
                'http_code' => $http_code,
                'error_message' => $wpjobportal_data['message'] ?? 'Invalid key'
            ]);
            wp_send_json_error(array(
                'status'  => $wpjobportal_data['status'] ?? 'error',
                'message' => $wpjobportal_data['message'] ?? 'An unknown error occurred.'
            ));
        }
    }

    /**
     * AJAX Function: Performs a FULL data import.
     */

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

    // functino to imporo zywrap categories
    // function importZywrapCategories( $wpjobportal_data_categories ) {

    //     if ( empty( $wpjobportal_data_categories ) ) {
    //         return;
    //     }
    //     // // Get max ordering
    //     // $query = "SELECT MAX(cat.ordering)
    //     //             FROM `" . wpjobportal::$_db->prefix . "wj_portal_zywrap_categories` AS cat";
    //     // $ordering = (int) wpjobportal::$_db->get_var( $query );
    //     // $ordering = $ordering + 1;
    //     // $ordering_check = $ordering;
    //     /*
    //     if($ordering_check > 0){

    //         // Prepare list of existing categories to avoid duplicates
    //         $wpjobportal_existing = [];
    //         $query = "SELECT cat.code, cat.name
    //                     FROM `" . wpjobportal::$_db->prefix . "wj_portal_zywrap_categories` AS cat";
    //         $wpjobportal_results = wpjobportal::$_db->get_results( $query );

    //         if ( ! empty( $wpjobportal_results ) ) {
    //             foreach ( $wpjobportal_results as $wpjobportal_row ) {
    //                 // Normalize for matching
    //                 $wpjobportal_existing[] = $this->cleanStringForCompare( $wpjobportal_row->code );
    //             }
    //         }
    //     }
    //     */

    //     // Loop categories from input
    //     foreach ( $wpjobportal_data_categories as $cate => $wpjobportal_category ) {
    //         $wpjobportal_name = $wpjobportal_category['name'];
    //         $wpjobportal_code = $wpjobportal_category['code'];
    //         $wpjobportal_ordering = $wpjobportal_category['ordering'];
    //         /*
    //         if($ordering_check > 0){
    //             $wpjobportal_compare_code  = $this->cleanStringForCompare( $code );

    //             // Skip duplicates
    //             if (in_array( $wpjobportal_compare_code, $wpjobportal_existing ) ) {
    //                 $this->zywrap_import_counts['categories']['skipped'] += 1;
    //                 continue;
    //             }
    //         }
    //         */
    //         // Prepare DB row object
    //         // $wpjobportal_row = WPJOBPORTALincluder::getJSTable('zywrapcategory');

    //         // $created = date_i18n('Y-m-d H:i:s');
    //         // $wpjobportal_updated = date_i18n('Y-m-d H:i:s');

    //         // Build dataset same as job type function
    //         $wpjobportal_data = [];
    //         //$wpjobportal_data['id']       = '';
    //         $wpjobportal_data['code']     = $wpjobportal_code;
    //         $wpjobportal_data['name']     = $wpjobportal_name;
    //         $wpjobportal_data['ordering'] = $wpjobportal_ordering;
    //         $wpjobportal_data['status']   = 1;
    //         // $wpjobportal_data['created']  = $created;
    //         // $wpjobportal_data['updated']  = $wpjobportal_updated;

    //         // Store into DB
    //         // Suppress duplicate-key insert warnings during bulk import
    //         wpjobportal::$_db->suppress_errors( true );

    //         $response = wpjobportal::$_db->insert(wpjobportal::$_db->prefix.'wj_portal_zywrap_categories',$wpjobportal_data);
    //         wpjobportal::$_db->suppress_errors( false );

    //         if ($response) {
    //             $this->zywrap_import_counts['categories']['imported'] += 1;
    //         } else {
    //             $this->zywrap_import_counts['categories']['failed'] += 1;
    //             continue;
    //         }

    //         $ordering++;
    //     }
    // }

    function importZywrapCategories( $wpjobportal_data_categories ) {

        if ( empty( $wpjobportal_data_categories ) ) {
            return;
        }

        $table_name = wpjobportal::$_db->prefix . 'wj_portal_zywrap_categories';

        // 1. Prepare list of existing categories to handle updates
        $wpjobportal_existing = [];
        $query = "SELECT cat.code FROM `" . $table_name . "` AS cat";
        $wpjobportal_results = wpjobportal::$_db->get_results( $query );

        if ( ! empty( $wpjobportal_results ) ) {
            foreach ( $wpjobportal_results as $wpjobportal_row ) {
                // Normalize the code for reliable matching
                $clean_code = $this->cleanStringForCompare( $wpjobportal_row->code );
                // Store the clean code as the key, and original code as the value
                $wpjobportal_existing[ $clean_code ] = $wpjobportal_row->code;
            }
        }

        // 2. Loop categories from input
        foreach ( $wpjobportal_data_categories as $cate => $wpjobportal_category ) {
            $wpjobportal_name     = $wpjobportal_category['name'];
            $wpjobportal_code     = $wpjobportal_category['code'];
            $wpjobportal_ordering = $wpjobportal_category['ordering'];

            // Clean the incoming code for matching against our array
            $wpjobportal_compare_code = $this->cleanStringForCompare( $wpjobportal_code );

            // Build dataset
            $wpjobportal_data = [
                'code'     => $wpjobportal_code,
                'name'     => $wpjobportal_name,
                'ordering' => $wpjobportal_ordering,
                'status'   => 1
            ];

            // Suppress duplicate-key insert warnings during DB operations
            wpjobportal::$_db->suppress_errors( true );

            // 3. Check for matching code and perform the update or insert
            if ( array_key_exists( $wpjobportal_compare_code, $wpjobportal_existing ) ) {

                // UPDATE existing record
                $where = [ 'code' => $wpjobportal_code ];
                $response = wpjobportal::$_db->update( $table_name, $wpjobportal_data, $where );

                if ( $response !== false ) { // update returns false on error, 0 if no rows changed
                    $this->zywrap_import_counts['categories']['imported'] += 1;
                } else {
                    $this->zywrap_import_counts['categories']['failed'] += 1;
                }

            } else {

                // INSERT new record
                $response = wpjobportal::$_db->insert( $table_name, $wpjobportal_data );

                if ( $response ) {
                    $this->zywrap_import_counts['categories']['imported'] += 1;

                    // Add the newly inserted record to our tracking array so if there are
                    // duplicates within the same import payload, it updates instead of crashing
                    $wpjobportal_existing[ $wpjobportal_compare_code ] = $wpjobportal_code;
                } else {
                    $this->zywrap_import_counts['categories']['failed'] += 1;
                }
            }

            wpjobportal::$_db->suppress_errors( false );
        }
    }

    // function to import zywrap use cases
    function importZywrapUseCases( $wpjobportal_data_usecases ) {
            if ( empty( $wpjobportal_data_usecases ) ) {
                return;
            }

            $table_name = wpjobportal::$_db->prefix . 'wj_portal_zywrap_use_cases';

            // 1. Prepare list of existing use cases to handle updates
            $wpjobportal_existing = [];
            $query = "SELECT uc.code FROM `" . $table_name . "` AS uc";
            $wpjobportal_results = wpjobportal::$_db->get_results( $query );

            if ( ! empty( $wpjobportal_results ) ) {
                foreach ( $wpjobportal_results as $wpjobportal_row ) {
                    // Normalize the code for reliable matching
                    $clean_code = $this->cleanStringForCompare( $wpjobportal_row->code );
                    $wpjobportal_existing[ $clean_code ] = $wpjobportal_row->code;
                }
            }

            // 2. Loop use cases from input
            foreach ( $wpjobportal_data_usecases as $code_key => $usecase ) {
                $wpjobportal_code = $usecase['code'] ?? '';

                // Skip if no code is provided to prevent empty matching issues
                if ( empty( $wpjobportal_code ) ) {
                    $this->zywrap_import_counts['usecases']['failed'] += 1;
                    continue;
                }

                $wpjobportal_compare_code = $this->cleanStringForCompare( $wpjobportal_code );

                // Build base dataset (omitting ordering so updates don't reset their position)
                $wpjobportal_data = [
                    'code'          => $wpjobportal_code,
                    'name'          => $usecase['name'] ?? '',
                    'description'   => $usecase['desc'] ?? '',
                    'category_code' => $usecase['cat'] ?? '',
                    'ordering'      => $usecase['ordering'] ?? '',
                    'schema_data'   => !empty($usecase['schema']) ? json_encode($usecase['schema']) : null,
                    'status'        => 1
                ];

                // Suppress duplicate-key insert warnings during DB operations
                wpjobportal::$_db->suppress_errors( true );

                // 3. Check for matching code and perform the update or insert
                if ( array_key_exists( $wpjobportal_compare_code, $wpjobportal_existing ) ) {

                    // UPDATE existing record
                    $where = [ 'code' => $wpjobportal_code ];
                    $response = wpjobportal::$_db->update( $table_name, $wpjobportal_data, $where );

                    if ( $response !== false ) {
                        $this->zywrap_import_counts['usecases']['imported'] += 1;
                    } else {
                        $this->zywrap_import_counts['usecases']['failed'] += 1;
                    }

                } else {

                    // INSERT new record
                    $response = wpjobportal::$_db->insert( $table_name, $wpjobportal_data );

                    if ( $response ) {
                        $this->zywrap_import_counts['usecases']['imported'] += 1;

                        // Add to tracking array to catch duplicates inside the payload
                        $wpjobportal_existing[ $wpjobportal_compare_code ] = $wpjobportal_code;

                        // Increment ordering for the next potential insert
                        //$ordering++;
                    } else {
                        $this->zywrap_import_counts['usecases']['failed'] += 1;
                    }
                }

                wpjobportal::$_db->suppress_errors( false );
            }
        }

    // function to import zywrap languages
    function importZywrapLanguages( $wpjobportal_data_languages ) {

            if ( empty( $wpjobportal_data_languages ) ) {
                return;
            }

            $table_name = wpjobportal::$_db->prefix . 'wj_portal_zywrap_languages';

            // 1. Prepare list of existing languages to handle updates
            $wpjobportal_existing = [];
            $query = "SELECT lang.code FROM `" . $table_name . "` AS lang";
            $wpjobportal_results = wpjobportal::$_db->get_results( $query );

            if ( ! empty( $wpjobportal_results ) ) {
                foreach ( $wpjobportal_results as $wpjobportal_row ) {
                    // Normalize the code for reliable matching
                    $clean_code = $this->cleanStringForCompare( $wpjobportal_row->code );
                    $wpjobportal_existing[ $clean_code ] = $wpjobportal_row->code;
                }
            }

            // 2. Loop languages from input
            foreach ( $wpjobportal_data_languages as $code_key => $wpjobportal_language ) {
                $wpjobportal_code = $wpjobportal_language['code'] ?? '';

                // Skip if no code is provided to prevent empty matching issues
                if ( empty( $wpjobportal_code ) ) {
                    $this->zywrap_import_counts['languages']['failed'] += 1;
                    continue;
                }

                $wpjobportal_compare_code = $this->cleanStringForCompare( $wpjobportal_code );

                // Build dataset (using the fallback logic from your original code)
                $wpjobportal_data = [
                    'code'     => $wpjobportal_code,
                    'name'     => $wpjobportal_language['name'] ?? '',
                    'ordering' => $wpjobportal_language['ordering'] ?? '',
                    'status'   => $wpjobportal_language['status'] ?? ''
                ];

                // Suppress duplicate-key insert warnings during DB operations
                wpjobportal::$_db->suppress_errors( true );

                // 3. Check for matching code and perform the update or insert
                if ( array_key_exists( $wpjobportal_compare_code, $wpjobportal_existing ) ) {

                    // UPDATE existing record
                    $where = [ 'code' => $wpjobportal_code ];
                    $response = wpjobportal::$_db->update( $table_name, $wpjobportal_data, $where );

                    if ( $response !== false ) {
                        $this->zywrap_import_counts['languages']['imported'] += 1;
                    } else {
                        $this->zywrap_import_counts['languages']['failed'] += 1;
                    }

                } else {

                    // INSERT new record
                    $response = wpjobportal::$_db->insert( $table_name, $wpjobportal_data );

                    if ( $response ) {
                        $this->zywrap_import_counts['languages']['imported'] += 1;

                        // Add to tracking array to catch duplicates inside the payload
                        $wpjobportal_existing[ $wpjobportal_compare_code ] = $wpjobportal_code;
                    } else {
                        $this->zywrap_import_counts['languages']['failed'] += 1;
                    }
                }

                wpjobportal::$_db->suppress_errors( false );
            }
        }

    // function to import zywrap AI models
    function importZywrapAiModels( $wpjobportal_data_ai_models ) {

        if ( empty( $wpjobportal_data_ai_models ) ) {
            return;
        }

        $table_name = wpjobportal::$_db->prefix . 'wj_portal_zywrap_ai_models';

        // 1. Prepare list of existing models to handle updates
        $wpjobportal_existing = [];
        $query = "SELECT model.code FROM `" . $table_name . "` AS model";
        $wpjobportal_results = wpjobportal::$_db->get_results( $query );

        if ( ! empty( $wpjobportal_results ) ) {
            foreach ( $wpjobportal_results as $wpjobportal_row ) {
                // Normalize the code for reliable matching
                $clean_code = $this->cleanStringForCompare( $wpjobportal_row->code );
                $wpjobportal_existing[ $clean_code ] = $wpjobportal_row->code;
            }
        }

        // 2. Loop AI models from input
        foreach ( $wpjobportal_data_ai_models as $code_key => $wpjobportal_model ) {
            $wpjobportal_code = $wpjobportal_model['code'] ?? '';

            // Skip if no code is provided to prevent empty matching issues
            if ( empty( $wpjobportal_code ) ) {
                $this->zywrap_import_counts['aimodels']['failed'] += 1;
                continue;
            }

            $wpjobportal_compare_code = $this->cleanStringForCompare( $wpjobportal_code );

            // Extract remaining fields
            $wpjobportal_name     = $wpjobportal_model['name'] ?? '';
            $wpjobportal_ordering = $wpjobportal_model['ordering'] ?? '';
            $wpjobportal_status   = (!isset($wpjobportal_model['status']) || $wpjobportal_model['status']) ? 1 : 0;

            // Build dataset
            $wpjobportal_data = [
                'code'     => $wpjobportal_code,
                'name'     => $wpjobportal_name,
                'ordering' => $wpjobportal_ordering,
                'status'   => $wpjobportal_status
            ];

            // Suppress duplicate-key insert warnings during DB operations
            wpjobportal::$_db->suppress_errors( true );

            // 3. Check for matching code and perform the update or insert
            if ( array_key_exists( $wpjobportal_compare_code, $wpjobportal_existing ) ) {

                // UPDATE existing record
                $where = [ 'code' => $wpjobportal_code ];
                $response = wpjobportal::$_db->update( $table_name, $wpjobportal_data, $where );

                if ( $response !== false ) {
                    $this->zywrap_import_counts['aimodels']['imported'] += 1;
                } else {
                    $this->zywrap_import_counts['aimodels']['failed'] += 1;
                }

            } else {

                // INSERT new record
                $response = wpjobportal::$_db->insert( $table_name, $wpjobportal_data );

                if ( $response ) {
                    $this->zywrap_import_counts['aimodels']['imported'] += 1;

                    // Add to tracking array to catch duplicates inside the payload
                    $wpjobportal_existing[ $wpjobportal_compare_code ] = $wpjobportal_code;
                } else {
                    $this->zywrap_import_counts['aimodels']['failed'] += 1;
                }
            }

            wpjobportal::$_db->suppress_errors( false );
        }
    }

        // function to import zywrap block templates
    function importZywrapBlockTemplates( $wpjobportal_data_templates ) {

        if ( empty( $wpjobportal_data_templates ) ) {
            return;
        }

        $table_name = wpjobportal::$_db->prefix . 'wj_portal_zywrap_block_templates';

        // 1. Prepare list of existing templates using a composite key (type + code)
        $wpjobportal_existing = [];
        $query = "SELECT tpl.type, tpl.code FROM `" . $table_name . "` AS tpl";
        $wpjobportal_results = wpjobportal::$_db->get_results( $query );

        if ( ! empty( $wpjobportal_results ) ) {
            foreach ( $wpjobportal_results as $wpjobportal_row ) {
                // Normalize the composite key for reliable matching
                $wpjobportal_key = $this->cleanStringForCompare( $wpjobportal_row->type . '_' . $wpjobportal_row->code );
                $wpjobportal_existing[ $wpjobportal_key ] = true;
            }
        }

        // 2. Loop through all template groups (types)
        foreach ( $wpjobportal_data_templates as $type => $wpjobportal_templates ) {

            if ( ! is_array( $wpjobportal_templates ) ) {
                continue;
            }

            foreach ( $wpjobportal_templates as $template ) {
                // SDK CHANGE: Look for 'label' first, falling back to 'name'
                $code = $template['code'] ?? '';
                $name = $template['label'] ?? $template['name'] ?? '';

                // SDK CHANGE: Status is now passed from the API
                $status = (!isset($template['status']) || $template['status']) ? 1 : 0;

                // Skip and count as failed if no code is provided
                if ( empty( $code ) ) {
                    $this->zywrap_import_counts['blocktemplates']['failed'] += 1;
                    continue;
                }

                // Create the matching key for this specific iteration
                $compare_key = $this->cleanStringForCompare( $type . '_' . $code );

                // Prepare bind data
                $wpjobportal_data = [
                    'type'   => $type,
                    'code'   => $code,
                    'name'   => $name,
                    'status' => $status
                ];

                // Suppress duplicate-key insert warnings during bulk import
                wpjobportal::$_db->suppress_errors( true );

                // 3. Check for matching composite key and perform update or insert
                if ( array_key_exists( $compare_key, $wpjobportal_existing ) ) {

                    // UPDATE existing record (matching BOTH type and code)
                    $where = [
                        'type' => $type,
                        'code' => $code
                    ];
                    $response = wpjobportal::$_db->update( $table_name, $wpjobportal_data, $where );

                    if ( $response !== false ) {
                        $this->zywrap_import_counts['blocktemplates']['imported'] += 1;
                    } else {
                        $this->zywrap_import_counts['blocktemplates']['failed'] += 1;
                    }

                } else {

                    // INSERT new record
                    $response = wpjobportal::$_db->insert( $table_name, $wpjobportal_data );

                    if ( $response ) {
                        $this->zywrap_import_counts['blocktemplates']['imported'] += 1;

                        // Add to tracking array to catch duplicates inside the payload
                        $wpjobportal_existing[ $compare_key ] = true;
                    } else {
                        $this->zywrap_import_counts['blocktemplates']['failed'] += 1;
                    }
                }

                wpjobportal::$_db->suppress_errors( false );
            }
        }
    }

    function importZywrapWrappersInBatches($wpjobportal_data_wrappers, $wpjobportal_total_count) {
        if (empty($wpjobportal_data_wrappers)) {
            return [
                'status'  => 'error',
                'message' => __('No data to import.', 'wp-job-portal'),
            ];
        }

        // Determine batch size
        $batch_data = $wpjobportal_data_wrappers;
        $remaining  = [];
        //$batch_data = array_slice($wpjobportal_data_wrappers, 0, $this->max_per_run, true);
        //$remaining  = array_slice($wpjobportal_data_wrappers, $this->max_per_run, null, true);

        $this->importZywrapWrappers($batch_data);

        // Handle remaining records or paused datasets
        $pending = [];
        if (!empty($remaining)) {
            // Save remaining items
            set_transient('wpjp_import_wrappers_pending', $remaining, HOUR_IN_SECONDS);
            return [
                'status'    => 'paused',
                'message'   => __('Batch processed successfully.', 'wp-job-portal'),
                'counts'    => $this->zywrap_import_counts,
                'remaining' => count($remaining),
            ];
        }

        // All data processed successfully
        delete_transient('wpjp_import_wrappers_pending');

        // 7. Save Version
        $wpjobportal_version = get_transient('wpjobportal_version_zyrap');
        if($wpjobportal_version){
            update_option('wpjobportal_zywrap_version',$wpjobportal_version);
            update_option('wpjobportal_zywrap_version_time',date_i18n("Y-m-d H:i:s"));
        }
        $this->log_api_call('sync_full', 'success', ['error_message' => 'Imported version: ' . $wpjobportal_version]);
        $wpjobportal_upload_dir = wp_upload_dir();
        $zip_file = $wpjobportal_upload_dir['path'] . '/zywrap-data.zip';
        $wpjobportal_json_file = $wpjobportal_upload_dir['path'] . '/zywrap-data.json';
        @unlink($zip_file);
        @unlink($wpjobportal_json_file);

        // Return completion status
        return [
            'status'    => 'completed',
            'message'   => __('Import completed successfully.', 'wp-job-portal'),
            'counts'    => $this->zywrap_import_counts,
            'imported'  => $this->zywrap_import_counts['wrappers']['imported'] ?? 0,
            'skipped'   => $this->zywrap_import_counts['wrappers']['skipped'] ?? 0,
            'failed'    => $this->zywrap_import_counts['wrappers']['failed'] ?? 0,
        ];
    }


    function importZywrapBatchProcess() {

        if (function_exists('set_time_limit')) {
            set_time_limit(0); // Unlimited execution time
        }
        @ini_set('memory_limit', '512M'); // Increase memory limit if possible


        //  Security Checks
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }

        // Verify the same nonce used in the main import
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($wpjobportal_nonce, 'zywrap_full_import')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }

        // 3. Retrieve Pending Data
        // This transient is created in your 'importZywrapWrappersInBatches' function
        $pending_wrappers = get_transient('wpjp_import_wrappers_pending');

        if (empty($pending_wrappers)) {
            // Safety: If no data is found, we assume completion or expiration
            delete_transient('wpjp_import_counts_cache');
            wp_send_json_success([
                'status'  => 'completed',
                'message' => __('Import process finished (No pending data).', 'wp-job-portal'),
                'counts'  => $this->zywrap_import_counts,
                'imported'=> 0,
                'failed'  => 0
            ]);
        }

        // 4. Restore Previous Counts (Cumulative Statistics)
        // We retrieve the counts from the previous run so the numbers increase (e.g., 50, 100, 150)
        // instead of resetting to 0 on every batch.
        $wpjobportal_saved_counts = get_transient('wpjp_import_counts_cache');
        if ($wpjobportal_saved_counts) {
            $this->zywrap_import_counts = $wpjobportal_saved_counts;
        } else {
            // Initialize defaults if missing
            if (!isset($this->zywrap_import_counts['wrappers'])) {
                $this->zywrap_import_counts['wrappers'] = ['imported' => 0, 'skipped' => 0, 'failed' => 0];
            }
        }

        // 5. Process the Next Batch
        // We reuse your existing helper. It will:
        // - Process 'max_per_run' items
        // - Update the 'wpjp_import_wrappers_pending' transient automatically
        // - Return the 'paused' or 'completed' status array
        $wpjobportal_result = $this->importZywrapWrappersInBatches($pending_wrappers, count($pending_wrappers));

        // 6. Persist Counts for the Next Run
        if ($wpjobportal_result['status'] === 'paused') {
            set_transient('wpjp_import_counts_cache', $this->zywrap_import_counts, HOUR_IN_SECONDS);
        } else {
            // If completed, clean up the stats cache
            delete_transient('wpjp_import_counts_cache');
        }

        // 7. Return JSON response matching your JS structure
        wp_send_json_success($wpjobportal_result);
    }

    function importZywrapWrappers($wpjobportal_data_wrappers) {
        if (empty($wpjobportal_data_wrappers)) {
            return;
        }

        // // Get max ordering
        // $query = "SELECT MAX(wrap.ordering)
        //           FROM `" . wpjobportal::$_db->prefix . "wj_portal_zywrap_wrappers` AS wrap";
        // $ordering = (int) wpjobportal::$_db->get_var($query);
        // $ordering_check = $ordering;

        // Prepare existing wrapper codes for duplicate prevention
        // $wpjobportal_existing = [];
        // if ($ordering_check > 0) {
        //     $query = "SELECT wrap.code
        //               FROM `" . wpjobportal::$_db->prefix . "wj_portal_zywrap_wrappers` ";
        //     $wpjobportal_existing = wpjobportal::$_db->get_col($query);
        // }

        // Initialize counters if not set
        if (!isset($this->zywrap_import_counts['wrappers'])) {
            $this->zywrap_import_counts['wrappers'] = [
                'imported' => 0,
                'skipped'  => 0,
                'failed'   => 0
            ];
        }

        // $ordering = $ordering + 1;

        // Loop incoming wrappers
        /*
        foreach ($wpjobportal_data_wrappers as $code => $wrapper) {
            // Validate required fields
            if (empty($code) || empty($wrapper['name'])) {
                $this->zywrap_import_counts['wrappers']['failed'] += 1;
                continue;
            }

            $wpjobportal_name        = $wrapper['name'] ?? '';
            $wpjobportal_desc        = $wrapper['desc'] ?? '';
            $cat         = $wrapper['cat'] ?? '';
            $featured    = $wrapper['featured'] ?? 0;
            $base        = $wrapper['base'] ?? 0;

            // if ($ordering_check > 0) {
            //     $wpjobportal_compare_code = $this->cleanStringForCompare($code);

            //     // Skip duplicates
            //     if (in_array($wpjobportal_compare_code, $wpjobportal_existing)) {
            //         $this->zywrap_import_counts['wrappers']['skipped'] += 1;
            //         continue;
            //     }
            // }

            // Prepare dataset
            $wpjobportal_data = [];
            $wpjobportal_data['code']          = $code;
            $wpjobportal_data['name']          = $wpjobportal_name;
            $wpjobportal_data['description']   = $wpjobportal_desc;
            $wpjobportal_data['category_code'] = $cat;
            $wpjobportal_data['featured']      = $featured;
            $wpjobportal_data['base']          = $base;
            $wpjobportal_data['ordering']      = $ordering;

            // Store record
            $response = wpjobportal::$_db->insert(wpjobportal::$_db->prefix.'wj_portal_zywrap_wrappers',$wpjobportal_data);
            if ($response) {
                $this->zywrap_import_counts['wrappers']['imported'] += 1;
            } else {
                $this->zywrap_import_counts['wrappers']['failed'] += 1;
                continue;
            }
            $ordering++;
        }
        */
        $batch_size   = 100;
        $batch_values = [];
        $batch_count  = 0;

        $table = wpjobportal::$_db->prefix . 'wj_portal_zywrap_wrappers';

        foreach ($wpjobportal_data_wrappers as $wrp_code => $wrapper) {

            // Validate required fields
            if (empty($wrapper['code']) || empty($wrapper['name'])) {
                $this->zywrap_import_counts['wrappers']['failed']++;
                continue;
            }

            $wpjobportal_code = $wrapper['code'] ?? '';
            $wpjobportal_name = $wrapper['name'] ?? '';
            $wpjobportal_desc = $wrapper['desc'] ?? '';
            //$cat              = $wrapper['cat'] ?? '';
            $usecase          = $wrapper['usecase'] ?? '';
            $featured         = (int) ($wrapper['featured'] ?? 0);
            $base             = (int) ($wrapper['base'] ?? 0);
            $ordering             = (int) ($wrapper['ordering'] ?? 0);

            // Prepare escaped row
            $batch_values[] = wpjobportal::$_db->prepare(
                "(%s, %s, %s, %s, %d, %d, %d)",
                $wpjobportal_code,
                $wpjobportal_name,
                $wpjobportal_desc,
                //$cat,
                $usecase,
                $featured,
                $base,
                $ordering
            );


            $batch_count++;

            // Execute batch when limit reached
            if ($batch_count === $batch_size) {

                // Suppress duplicate-key insert warnings during bulk import
                wpjobportal::$_db->suppress_errors( true );
                $sql = "
                    INSERT INTO {$table}
                    (code, name, description, use_case_code, featured, base, ordering)
                    VALUES " . implode(',', $batch_values);

                $result = wpjobportal::$_db->query($sql);
                wpjobportal::$_db->suppress_errors( false );

                if ($result !== false) {
                    $this->zywrap_import_counts['wrappers']['imported'] += $batch_count;
                } else {
                    $this->zywrap_import_counts['wrappers']['failed'] += $batch_count;
                }

                // Reset batch
                $batch_values = [];
                $batch_count  = 0;
            }
        }

        /**
         * Insert remaining records
         */
        if (!empty($batch_values)) {

            // Suppress duplicate-key insert warnings during bulk import
            wpjobportal::$_db->suppress_errors( true );
            $sql = "
                INSERT INTO {$table}
                (code, name, description, use_case_code, featured, base, ordering)
                VALUES " . implode(',', $batch_values);

            $result = wpjobportal::$_db->query($sql);
            wpjobportal::$_db->suppress_errors( false );

            if ($result !== false) {
                $this->zywrap_import_counts['wrappers']['imported'] += count($batch_values);
            } else {
                $this->zywrap_import_counts['wrappers']['failed'] += count($batch_values);
            }
        }
    }

    function deleteBatch( $table_name, $ids, $pk = 'code' ) {

        if ( empty( $ids ) || ! is_array( $ids ) ) { // ids contains codes
            return;
        }

        $has_error     = false;
        $error_message = '';

        // Suppress DB errors during the loop so we can catch them and rollback safely
        wpjobportal::$_db->suppress_errors( true );

        foreach ( $ids as $id ) {
            $where = [ $pk => $id ];

            // delete() returns the number of rows deleted, or false on error
            $response = wpjobportal::$_db->delete( $table_name, $where );

            // If false is returned, a database error occurred
            if ( $response === false ) {
                // $has_error     = true;
                // $error_message = wpjobportal::$_db->last_error;
                // break; // Stop the loop immediately on the first failure
            }
        }

        // Restore standard error reporting
        wpjobportal::$_db->suppress_errors( false );

    }

    function importZywrapData() {
        if (function_exists('set_time_limit')) {
            set_time_limit(0); // Unlimited execution time
        }
        @ini_set('memory_limit', '512M'); // Increase memory limit if possible

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }

        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($wpjobportal_nonce, 'zywrap_full_import')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }

        $api_key = get_option('wpjobportal_zywrap_api_key');
        if (empty($api_key)) {
            wp_send_json_error(array('message' => 'API Key is not set.'));
        }
       $type = WPJOBPORTALrequest::getVar('actionType');

        // new install case
        $wpjobportal_url = 'https://api.zywrap.com/v1/sdk/v1/sync';

        //$wpjobportal_data_version = '';

        // parameter is required
        $wpjobportal_data_version = get_option('wpjobportal_zywrap_version');
        $truncate_tables = 1;
        //$wpjobportal_data_version = '2020-02-02T13:03:18.438Z';
        if(empty($wpjobportal_data_version)){
            //$wpjobportal_data_version = '2020-02-02T13:03:18.438Z';
            $truncate_tables = 0;
        }


        //$wpjobportal_data_version = '2026-02-25T13:03:18.438Z';
        $ignore_mode_section = 0;
        // if(!empty($wpjobportal_data_version)){
        //     $ignore_mode_section = 0;
        //     $wpjobportal_url = 'https://api.zywrap.com/v1/sdk/v1/sync';
        //     //$wpjobportal_url = 'https://api.zywrap.com/v1/sdk/export/updates/';
        // }

        $wpjobportal_url = add_query_arg('fromVersion', urlencode($wpjobportal_data_version), $wpjobportal_url);


        $response = wp_remote_get( $wpjobportal_url, array(
            'timeout' => 300,
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
            ),
        ) );

        $response_code = wp_remote_retrieve_response_code( $response );


        if ( is_wp_error( $response ) || $response_code !== 200 ) {
            $wpjobportal_error_msg = is_wp_error( $response ) ? $response->get_error_message() : 'HTTP ' . $response_code;

            $this->log_api_call( 'sync_full', 'error', array(
                'error_message' => 'Download failed: ' . $wpjobportal_error_msg,
            ) );

            wp_send_json_error( array(
                'message' => __( 'Failed to download data bundle from Zywrap API.', 'wp-job-portal' ),
            ) );
        }

        $respose_body = wp_remote_retrieve_body($response);
        // MEMORY OPTIMIZATION: Decode and unset raw string immediately
        $json_response_decoded = json_decode($respose_body, true);
        // clean memory
        unset($respose_body);

        $mode = $json_response_decoded['mode'] ?? 'UNKNOWN';
        if ($mode === 'DELTA_UPDATE') { // update case
            /*
            ['metadata']['categories']
            ['metadata']['languages']
            ['metadata']['aiModels']
            ['metadata']['templates']
            ['wrappers']['upserts']
            */

            $wpjobportal_data = [];

            // categories
            if(!empty($json_response_decoded['metadata']['categories'])){
                $wpjobportal_data['categories'] = $json_response_decoded['metadata']['categories'];
            }

            // languages
            if(!empty($json_response_decoded['metadata']['languages'])){
                $wpjobportal_data['languages'] = $json_response_decoded['metadata']['languages'];
            }

            // aiModels
            if(!empty($json_response_decoded['metadata']['aiModels'])){
                $wpjobportal_data['aiModels'] = $json_response_decoded['metadata']['aiModels'];
            }

            // templates
            if(!empty($json_response_decoded['metadata']['templates'])){
                $wpjobportal_data['templates'] = $json_response_decoded['metadata']['templates'];
            }

            // wrappers // only new wrappers to be inserted
            if(!empty($json_response_decoded['wrappers']['upserts'])){
                $wpjobportal_data['wrappers'] = $json_response_decoded['wrappers']['upserts'];
                $wpjobportal_data['deletes']['wrappers'] = $json_response_decoded['wrappers']['deletes'];
            }

            // useCases // only new wrappers to be inserted
            if(!empty($json_response_decoded['useCases']['upserts'])){
                $wpjobportal_data['useCases'] = $json_response_decoded['useCases']['upserts'];
                $wpjobportal_data['deletes']['useCases'] = $json_response_decoded['useCases']['deletes'];
            }

            // data Version
            if (!empty($json_response_decoded['newVersion'])) {
                $wpjobportal_data['version'] = $json_response_decoded['newVersion'];
            }
            // clean memory
            unset($json_response_decoded);

        } elseif ($mode === 'FULL_RESET') { // Case B: Server requesting Full Reset (Too many possible changes)

            // The server might provide a specific download URL, or we fallback to root (fresh install case url)

            $download_url = !empty($json_response_decoded['wrappers']['downloadUrl'])
                                ? esc_url_raw($json_response_decoded['wrappers']['downloadUrl'])
                                : 'https://api.zywrap.com/v1/sdk/v1/sync';
            // clean memory
            unset($json_response_decoded);

            $wpjobportal_upload_dir = wp_upload_dir();
            $zip_file   = trailingslashit( $wpjobportal_upload_dir['path'] ) . 'zywrap-data.zip';

            $response = wp_remote_get( $download_url, array(
                'timeout' => 300,
                'headers' => array(
                    'Authorization' => 'Bearer ' . $api_key,
                ),
                'stream' => true,
                'filename' => $zip_file,
            ) );

            if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
                $wpjobportal_error_msg = is_wp_error( $response ) ? $response->get_error_message() : 'HTTP ' . wp_remote_retrieve_response_code( $response );

                $this->log_api_call( 'sync_full', 'error', array(
                    'error_message' => 'Download failed: ' . $wpjobportal_error_msg,
                ) );

                wp_send_json_error( array(
                    'message' => __( 'Failed to download data bundle from Zywrap API.', 'wp-job-portal' ),
                ) );
            }

            //ignore_mode_section to re use a major code block for both cases
            $ignore_mode_section = 1;

            // full reset case
            //truncate exsisting tables.
            global $wpdb;
            $wpdb->query('SET FOREIGN_KEY_CHECKS = 0;');
            $wpdb->query("TRUNCATE TABLE `" . $wpdb->prefix . "wj_portal_zywrap_wrappers`");
            $wpdb->query("TRUNCATE TABLE `" . $wpdb->prefix . "wj_portal_zywrap_categories`");
            $wpdb->query("TRUNCATE TABLE `" . $wpdb->prefix . "wj_portal_zywrap_use_cases`");
            $wpdb->query("TRUNCATE TABLE `" . $wpdb->prefix . "wj_portal_zywrap_languages`");
            $wpdb->query("TRUNCATE TABLE `" . $wpdb->prefix . "wj_portal_zywrap_block_templates`");
            $wpdb->query("TRUNCATE TABLE `" . $wpdb->prefix . "wj_portal_zywrap_ai_models`");
            $wpdb->query('SET FOREIGN_KEY_CHECKS = 1;');
            //echo '<br/=====DB EMPTY+++++<br/>';

            //}


            //if($ignore_mode_section == 1){ // model section ignore means full install or full reset
            // 2. Prepare file paths

            $wpjobportal_json_file  = trailingslashit( $wpjobportal_upload_dir['path'] ) . 'zywrap-data.json';

            // 3. Initialize WordPress filesystem
            global $wp_filesystem;
            if ( empty( $wp_filesystem ) ) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
                WP_Filesystem();
            }

            if ( ! $wp_filesystem ) {
                wp_send_json_error( array(
                    'message' => __( 'Filesystem initialization failed.', 'wp-job-portal' ),
                ) );
            }

            // 5. Unzip using WordPress built-in safe unzipper
            $unzip_result = unzip_file( $zip_file, $wpjobportal_upload_dir['path'] );

            if ( is_wp_error( $unzip_result ) ) {
                wp_send_json_error( array(
                    //'message' => ,$unzip_result->get_error_message(),
                    'message' => __( 'Failed to unzip data bundle', 'wp-job-portal' ),
                ) );
            }

            // 6. Validate JSON file exists
            if ( ! $wp_filesystem->exists( $wpjobportal_json_file ) ) {
                wp_send_json_error( array(
                    'message' => __( 'Error: zywrap-data.json not found in ZIP.', 'wp-job-portal' ),
                ) );
            }

            // 7. Read JSON safely
            $wpjobportal_json_data = $wp_filesystem->get_contents( $wpjobportal_json_file );
            if ( empty( $wpjobportal_json_data ) ) {
                wp_send_json_error( array(
                    'message' => __( 'Failed to read zywrap-data.json.', 'wp-job-portal' ),
                ) );
            }
            $wpjobportal_data = json_decode($wpjobportal_json_data, true);
            // clean memeory
            unset($wpjobportal_json_data);
            // remove files
            $wp_filesystem->delete($zip_file);
            $wp_filesystem->delete($wpjobportal_json_file);

            // modifyt he data to eahndle difference in case of update and reest
            // Import Categories

            if (!empty($wpjobportal_data['categories'])) {
                $extracted = $this->extractTabular($wpjobportal_data['categories']);
                $wpjobportal_data['categories'] = $extracted;
            }

            // Import Use Cases
            if (!empty($wpjobportal_data['useCases'])) {
                $extracted = $this->extractTabular($wpjobportal_data['useCases']);
                $wpjobportal_data['useCases'] = $extracted;
            }

            // Import Languages
            if (!empty($wpjobportal_data['languages'])) {
                $extracted = $this->extractTabular($wpjobportal_data['languages']);
                $wpjobportal_data['languages'] = $extracted;
            }

            // Import aiModels
            if (!empty($wpjobportal_data['aiModels'])) {
                $extracted = $this->extractTabular($wpjobportal_data['aiModels']);
                $wpjobportal_data['aiModels'] = $extracted;
            }

            // Import templates
            if (!empty($wpjobportal_data['templates'])) {
                foreach ($wpjobportal_data['templates'] as $type => $tabular) {
                    $wpjobportal_data['templates'][$type] = $this->extractTabular($tabular);
                }
                // $extracted = $this->extractTabular($wpjobportal_data['templates']);
                // $wpjobportal_data['templates'] = $extracted;

            }
            // Import wrappers
            if (!empty($wpjobportal_data['wrappers'])) {
                $extracted = $this->extractTabular($wpjobportal_data['wrappers']);
                $wpjobportal_data['wrappers'] = $extracted;
            }

        } // full reset if close
        // echo '<pre>';print_r($wpjobportal_data['templates']);
        //  die('after preping data 1019');

        if (empty($wpjobportal_data)) {
            $this->log_api_call('sync_full', 'error', ['error_message' => 'Could not parse JSON data.']);
            wp_send_json_error(array('message' => 'Error: Could not parse JSON data.'));
        }
        //set version in tranient to store on complete status
        $wpjobportal_version = $wpjobportal_data['version'] ?? 'N/A';
        set_transient('wpjobportal_version_zyrap', $wpjobportal_version, HOUR_IN_SECONDS); // version in transient for an hour to store it in options

        $output_array = [];
        // Import Categories
        if (!empty($wpjobportal_data['categories'])) {
            $output_array['categories'] = count($wpjobportal_data['categories']);
            $this->importZywrapCategories($wpjobportal_data['categories']);
        }
        // Import Use Cases
        if (!empty($wpjobportal_data['useCases'])) {
            $output_array['useCases'] = count($wpjobportal_data['useCases']);
            $this->importZywrapUseCases($wpjobportal_data['useCases']);


        }
        /*
    $wpjobportal_data['deletes']['wrappers']
    $wpjobportal_data['deletes']['useCases']
        */
        if(!empty($wpjobportal_data['deletes']) && !empty($wpjobportal_data['deletes']['wrappers'])){
            $table_name = wpjobportal::$_db->prefix . "wj_portal_zywrap_wrappers";
            $this->deleteBatch( $table_name, $wpjobportal_data['deletes']['wrappers'], 'code' );
        }

        if(!empty($wpjobportal_data['deletes']) && !empty($wpjobportal_data['deletes']['useCases'])){
            $table_name = wpjobportal::$_db->prefix . "wj_portal_zywrap_use_cases";
            $this->deleteBatch( $table_name, $wpjobportal_data['deletes']['useCases'], 'code' );
        }

        // Import Languages
        if (!empty($wpjobportal_data['languages'])) {
            $output_array['languages'] = count($wpjobportal_data['languages']);
            $this->importZywrapLanguages($wpjobportal_data['languages']);
        }

        // Import aiModels
        if (!empty($wpjobportal_data['aiModels'])) {
            $output_array['aiModels'] = count($wpjobportal_data['aiModels']);
            $this->importZywrapAiModels($wpjobportal_data['aiModels']);
        }

        // Import templates
        if (!empty($wpjobportal_data['templates'])) {
            $output_array['templates'] = count($wpjobportal_data['templates']);
            $this->importZywrapBlockTemplates($wpjobportal_data['templates']);
        }
        // Import wrappers
        if (!empty($wpjobportal_data['wrappers'])) {

            $output_array['wrappers'] = count($wpjobportal_data['wrappers']);
            $wpjobportal_result = $this->importZywrapWrappersInBatches($wpjobportal_data['wrappers'], $output_array['wrappers']);
            set_transient('wpjp_import_counts_cache', $this->zywrap_import_counts, HOUR_IN_SECONDS);

            wp_send_json_success($wpjobportal_result);
        }

        // update case success message

        if(!empty($wpjobportal_data['version'])){
            update_option('wpjobportal_zywrap_version',$wpjobportal_data['version']);
            update_option('wpjobportal_zywrap_version_time',date_i18n("Y-m-d H:i:s"));
            wp_send_json_success([
                'status'    => 'completed',
                'message'   => __('Import completed successfully.', 'wp-job-portal'),
                'counts'    => $this->zywrap_import_counts,
                'imported'  => $this->zywrap_import_counts['wrappers']['imported'] ?? 0,
                'skipped'   => $this->zywrap_import_counts['wrappers']['skipped'] ?? 0,
                'failed'    => $this->zywrap_import_counts['wrappers']['failed'] ?? 0,
            ]);
        }

        // echo '<pre>';print_r($wpjobportal_result);echo '</pre>';
        // echo '<pre>';print_r($output_array);echo '</pre>';
        // echo '<pre>';print_r($this->zywrap_import_counts);echo '</pre>';

        // // Import Block Templates
        // if (!empty($wpjobportal_data['categories'])) {
        //     $output_array['categories'] = count($wpjobportal_data['categories']);
        // }

        // // Import Block Templates
        // if (!empty($wpjobportal_data['aiModels'])) {
        //     $output_array['aiModels'] = count($wpjobportal_data['aiModels']);
        // }

        // // Import Block Templates
        // if (!empty($wpjobportal_data['languages'])) {
        //     $output_array['languages'] = count($wpjobportal_data['languages']);
        // }

        // // Import Block Templates
        // if (!empty($wpjobportal_data['templates'])) {
        //     $output_array['templates'] = count($wpjobportal_data['templates']);
        // }

        // // Import Wrappers
        // if (!empty($wpjobportal_data['wrappers'])) {
        //     $output_array['wrappers'] = count($wpjobportal_data['wrappers']);

        // }

        // echo '<pre>';
        // print_r($output_array);
        // echo '</pre>';



        // 7. Save Version
        // $wpjobportal_version = $wpjobportal_data['version'] ?? 'N/A';
        // if (isset($wpjobportal_data['version'])) {
        //     update_option('wpjobportal_zywrap_version', $wpjobportal_data['version']);
        // }

        die(' model code 906 11');

        // try {

            $this->log_api_call('sync_full', 'success', ['error_message' => 'Imported version: ' . $wpjobportal_version]);
            @unlink($zip_file);
            @unlink($wpjobportal_json_file);

        // } catch (Exception $e) {
        //     $this->log_api_call('sync_full', 'error', ['error_message' => 'DB error: ' . $e->getMessage()]);
        //     wp_send_json_error(array('message' => 'Database error during import: ' . $e->getMessage()));
        // }

        // Clean up files

        wp_send_json_success(array('message' => 'Full data import complete! Version: ' . ($wpjobportal_data['version'] ?? 'N/A')));
    }


    /**
         * Helper function to extract Tabular JSON Data
         * matching the V1 API format to associative arrays
         */
    private function extractTabular($tabularData) {
        if (empty($tabularData['cols']) || empty($tabularData['data'])) return [];
        $cols = $tabularData['cols'];
        $result = [];
        foreach ($tabularData['data'] as $row) {
            $result[] = array_combine($cols, $row);
        }
        return $result;
    }

    /**
     * AJAX Function: Performs a DELTA data sync.
     */
    // function sync_zywrap_delta() {
    //     if (!current_user_can('manage_options')) {
    //         wp_send_json_error(array('message' => 'Permission denied.'));
    //     }
    //     $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
    //     if (!wp_verify_nonce($wpjobportal_nonce, 'zywrap_delta_sync')) {
    //         wp_send_json_error(array('message' => 'Security check failed.'));
    //     }

    //     $api_key = get_option('wpjobportal_zywrap_api_key');
    //     if (empty($api_key)) {
    //         wp_send_json_error(array('message' => 'API Key is not set.'));
    //     }

    //     // 1. Get current local version
    //     $current_version = get_option('wpjobportal_zywrap_version');
    //     if (empty($current_version)) {
    //         wp_send_json_error(array('message' => 'No local version found. Please run a Full Import first.'));
    //     }

    //     // 2. Call the sync endpoint
    //     $wpjobportal_url = 'https://api.zywrap.com/v1/sdk/export/updates?fromVersion=' . urlencode($current_version);
    //     $response = wp_remote_get($wpjobportal_url, array(
    //         'timeout' => 60,
    //         'headers' => array(
    //             'Authorization' => 'Bearer ' . $api_key,
    //             'Accept' => 'application/json'
    //         )
    //     ));

    //     if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
    //         $wpjobportal_error_msg = is_wp_error($response) ? $response->get_error_message() : 'HTTP ' . wp_remote_retrieve_response_code($response);
    //         $this->log_api_call('sync_delta', 'error', ['error_message' => 'Fetch failed: ' . $wpjobportal_error_msg]);
    //         wp_send_json_error(array('message' => 'Failed to fetch updates from Zywrap API.'));
    //     }

    //     $patch = json_decode(wp_remote_retrieve_body($response), true);
    //     if (!$patch || empty($patch['newVersion'])) {
    //         wp_send_json_error(array('message' => 'Could not decode a valid patch from the API.'));
    //     }

    //     // 3. Apply the patch
    //     global $wpdb;
    //     $wpdb->query('SET FOREIGN_KEY_CHECKS = 0;');

    //     try {
    //         // Process Updates/Creations (UPSERT)
    //         if (!empty($patch['updates'])) {
    //             // --- Process Wrappers ---
    //             if (!empty($patch['updates']['wrappers'])) {
    //                 foreach ($patch['updates']['wrappers'] as $wpjobportal_item) $wpdb->replace($wpdb->prefix . 'wj_portal_zywrap_wrappers', $wpjobportal_item);
    //             }
    //             // --- Process Categories ---
    //             if (!empty($patch['updates']['categories'])) {
    //                 foreach ($patch['updates']['categories'] as $wpjobportal_item) $wpdb->replace($wpdb->prefix . 'wj_portal_zywrap_categories', $wpjobportal_item);
    //             }
    //             if (!empty($patch['updates']['languages'])) {
    //                   foreach ($patch['updates']['languages'] as $wpjobportal_item) $wpdb->replace($wpdb->prefix . 'wj_portal_zywrap_languages', $wpjobportal_item);
    //             }
    //             if (!empty($patch['updates']['aiModels'])) {
    //                   foreach ($patch['updates']['aiModels'] as $wpjobportal_item) $wpdb->replace($wpdb->prefix . 'wj_portal_zywrap_ai_models', $wpjobportal_item);
    //             }

    //             // Block Templates
    //             $blockTypes = ['tones', 'styles', 'formattings', 'complexities', 'lengths', 'outputTypes', 'responseGoals', 'audienceLevels'];
    //             foreach ($blockTypes as $type) {
    //                 if (!empty($patch['updates'][$type])) {
    //                     foreach ($patch['updates'][$type] as $wpjobportal_item) {
    //                         $wpjobportal_item['type'] = $type;
    //                         $wpdb->replace($wpdb->prefix . 'wj_portal_zywrap_block_templates', $wpjobportal_item);
    //                     }
    //                 }
    //             }
    //         }

    //         // Process Deletions
    //         if (!empty($patch['deletions'])) {
    //             foreach ($patch['deletions'] as $wpjobportal_item) {
    //                 $wpjobportal_table_name = '';
    //                 if ($wpjobportal_item['type'] == 'Wrapper') $wpjobportal_table_name = $wpdb->prefix . 'wj_portal_zywrap_wrappers';
    //                 if ($wpjobportal_item['type'] == 'Category') $wpjobportal_table_name = $wpdb->prefix . 'wj_portal_zywrap_categories';
    //                 if ($wpjobportal_item['type'] == 'Language') $wpjobportal_table_name = $wpdb->prefix . 'wj_portal_zywrap_languages';
    //                 if ($wpjobportal_item['type'] == 'AIModel') $wpjobportal_table_name = $wpdb->prefix . 'wj_portal_zywrap_ai_models';

    //                 if ($wpjobportal_table_name) {
    //                     $wpdb->delete($wpjobportal_table_name, array('code' => $wpjobportal_item['code']));
    //                 }

    //                 if (str_ends_with($wpjobportal_item['type'], 'BlockTemplate')) {
    //                      $wpdb->delete($wpdb->prefix . 'wj_portal_zywrap_block_templates', array('code' => $wpjobportal_item['code']));
    //                 }
    //             }
    //         }

    //         $wpdb->query('SET FOREIGN_KEY_CHECKS = 1;');

    //     } catch (Exception $e) {
    //         $wpdb->query('SET FOREIGN_KEY_CHECKS = 1;');
    //         wp_send_json_error(array('message' => 'Database error applying patch: ' . $e->getMessage()));
    //     }

    //     // 4. Save the new version
    //     update_option('wpjobportal_zywrap_version', $patch['newVersion']);
    //     $this->log_api_call('sync_delta', 'success', ['error_message' => 'Synced to: ' . $patch['newVersion']]);
    //     wp_send_json_success(array('message' => 'Sync complete. New version: ' . $patch['newVersion']));
    // }

    /**
     * Loads all data from local DB tables for the playground UI.
     */
    function getPlaygroundData() {
        global $wpdb;
        $wpjobportal_data = array();

        // Get Categories
        $wpjobportal_data['categories'] = $wpdb->get_results("SELECT code, name FROM `" . $wpdb->prefix . "wj_portal_zywrap_categories` ORDER BY ordering ASC");

        // Get AI Models
        $wpjobportal_data['models'] = $wpdb->get_results("SELECT code, name FROM `" . $wpdb->prefix . "wj_portal_zywrap_ai_models` ORDER BY ordering ASC");

        // Get Languages
        $wpjobportal_data['languages'] = $wpdb->get_results("SELECT code, name FROM `" . $wpdb->prefix . "wj_portal_zywrap_languages` ORDER BY ordering ASC");

        // Get Block Templates (Overrides)
        $wpjobportal_templates_raw = $wpdb->get_results("SELECT type, code, name FROM `" . $wpdb->prefix . "wj_portal_zywrap_block_templates` ORDER BY type, name ASC");

        // Group templates by type
        $wpjobportal_grouped_templates = [];
        foreach ($wpjobportal_templates_raw as $wpjobportal_row) {
            $wpjobportal_grouped_templates[$wpjobportal_row->type][] = array('code' => $wpjobportal_row->code, 'name' => $wpjobportal_row->name);
        }
        $wpjobportal_data['templates'] = $wpjobportal_grouped_templates;

        // Store in the main class data if needed, matching usage in controller

        // wrappers for search implimenteation
        // $query = "SELECT code, name, category_code FROM `" . $wpdb->prefix . "wj_portal_zywrap_wrappers` ORDER BY ordering ASC";

        // $wpjobportal_data['wrappers'] = $wpdb->get_results($query);

        wpjobportal::$_data['playground_data'] = $wpjobportal_data;
        return $wpjobportal_data;
    }

    function getZywrapAllWrappers(){
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($wpjobportal_nonce, 'zywrap_get_all_wrappers')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }

        $query = "SELECT id, name, use_case_code FROM `" . wpjobportal::$_db->prefix . "wj_portal_zywrap_wrappers` ORDER BY ordering ASC";

        $wpjobportal_data = wpjobportal::$_db->get_results($query, ARRAY_N);

        wp_send_json_success($wpjobportal_data);
    }

    /**
     * AJAX Function: Gets wrappers for a specific category.
     */
    function getWrappersByCategory() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($wpjobportal_nonce, 'zywrap_get_wrappers')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }

        global $wpdb;
        //$wpjobportal_use_case_code = WPJOBPORTALrequest::getVar('use_case_code', 'post');
        $wpjobportal_category_code = WPJOBPORTALrequest::getVar('category_code', 'post');
        $wpjobportal_show_featured = WPJOBPORTALrequest::getVar('show_featured', 'post') === 'true';
        $wpjobportal_show_base = WPJOBPORTALrequest::getVar('show_base', 'post') === 'true';

        if (empty($wpjobportal_category_code)) {
            wp_send_json_success(array());
        }
        // Table names with WordPress prefix
        $tbl_wrappers = $wpdb->prefix . 'wj_portal_zywrap_wrappers';
        $tbl_use_cases = $wpdb->prefix . 'wj_portal_zywrap_use_cases';

        // Updated query using JOIN to match new SDK logic
        $query = $wpdb->prepare("
            SELECT
                wrapper.code,
                wrapper.name,
                wrapper.featured,
                wrapper.base,
                wrapper.description,
                wrapper.ordering,
                wrapper.id,
                wrapper.use_case_code
            FROM `{$tbl_wrappers}` wrapper
            JOIN `{$tbl_use_cases}` usecase ON wrapper.use_case_code = usecase.code
            WHERE usecase.category_code = %s
              AND wrapper.status = 1
              AND usecase.status = 1
            ORDER BY wrapper.ordering ASC
        ", $wpjobportal_category_code);

        $wrappers = $wpdb->get_results($query);

        // Apply filters in PHP
        if ($wpjobportal_show_featured) {
            $wrappers = array_filter($wrappers, function($w) { return $w->featured; });
        }
        if ($wpjobportal_show_base) {
            $wrappers = array_filter($wrappers, function($w) { return $w->base; });
        }

        wp_send_json_success(array_values($wrappers)); // Re-index array
    }
    /**
     * AJAX Function: Gets wrappers for a specific category.
     */
    function getSchemaByUseCode() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($wpjobportal_nonce, 'zywrap_get_schema')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }

        global $wpdb;
        $wpjobportal_use_case_code = WPJOBPORTALrequest::getVar('use_case_code', 'post');


        if (empty($wpjobportal_use_case_code)) {
            wp_send_json_success(array());
        }

        // Table names with WordPress prefix
        $tbl_use_cases = $wpdb->prefix . 'wj_portal_zywrap_use_cases';

        // Updated query using JOIN to match new SDK logic
        $query = $wpdb->prepare("
            SELECT
                usecase.schema_data
            FROM `{$tbl_use_cases}` usecase
            WHERE usecase.code = %s
              AND usecase.status = 1

        ", $wpjobportal_use_case_code);

        $scheme_data = $wpdb->get_row($query);


        wp_send_json_success($scheme_data); // Re-index array
    }

    /**
     * AJAX Function: Executes the live API proxy call.
     * [cite: `PhpSdk.jsx`, `Docs.jsx`, `APIReference.jsx`]
     */
    function executeZywrapProxy() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($wpjobportal_nonce, 'zywrap_execute_proxy')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }

        $api_key = get_option('wpjobportal_zywrap_api_key');
        if (empty($api_key)) {
            wp_send_json_error(array('message' => 'API Key is not set.'));
        }

        // Get data from AJAX request
        $wpjobportal_model = WPJOBPORTALrequest::getVar('model', 'post');
        $wrapper_code = WPJOBPORTALrequest::getVar('wrapperCode', 'post');
        $wpjobportal_prompt = WPJOBPORTALrequest::getVar('prompt', 'post');
        $wpjobportal_language = WPJOBPORTALrequest::getVar('language', 'post');
        $wpjobportal_overrides = WPJOBPORTALrequest::getVar('overrides', 'post');
        // $wpjobportal_schema_inputs = WPJOBPORTALrequest::getVar('schema_inputs', 'post');
        $raw_schema_inputs = WPJOBPORTALrequest::getVar('schema_inputs', 'post');
        $wpjobportal_schema_inputs = !empty($raw_schema_inputs) ? json_decode(stripslashes($raw_schema_inputs), true) : array();

        // === NEW FEATURES INPUT ===
        $context = WPJOBPORTALrequest::getVar('context', 'post');
        $seo_keywords = WPJOBPORTALrequest::getVar('seo_keywords', 'post');
        $negative_constraints = WPJOBPORTALrequest::getVar('negative_constraints', 'post');

        // === MODIFY PROMPT WITH NEW FEATURES ===
        if (!empty($seo_keywords)) {
            $wpjobportal_prompt .= "\n\n[IMPORTANT] Naturally integrate the following SEO keywords into the text: " . $seo_keywords;
        }

        if (!empty($negative_constraints)) {
            $wpjobportal_prompt .= "\n\n[CONSTRAINT] Do NOT use the following words or phrases: " . $negative_constraints;
        }

        // --- NEW HOOK INTEGRATION START ---
        // Blueprint: wpjobportal_ai_content_generation_prompt
        // Allows legal compliance plugins to automatically append mandatory requirements to the prompt before execution.
        $ai_parameters = array(
            'model' => $wpjobportal_model,
            'wrapperCode' => $wrapper_code,
            'context' => $context
        );
        $wpjobportal_prompt = apply_filters('wpjobportal_ai_content_generation_prompt', $wpjobportal_prompt, $ai_parameters);
        // --- NEW HOOK INTEGRATION END ---

        // === SEND CONTEXT AS VARIABLE ===
        // if (!empty($wpjobportal_context)) {
        //     $wpjobportal_payloadData['variables'] = array('context' => $context);
        // }

        $api_variables = array();

        // 1. Add Context if it exists
        if (!empty($context)) {
            $api_variables['context'] = $context;
        }

        // 2. Loop through dynamic schema inputs and add them to the variables array
        // if (!empty($wpjobportal_schema_inputs) && is_array($wpjobportal_schema_inputs)) {
        //     foreach ($wpjobportal_schema_inputs as $key => $value) {
        //         // Sanitize the key and value for security
        //         $clean_key = sanitize_text_field($key);
        //         $clean_val = sanitize_text_field($value);

        //         // Only map it if the user actually typed something in the field
        //         if ($clean_val !== '') {
        //             $api_variables[$clean_key] = $clean_val;
        //         }
        //     }
        // }

        $structuredTextParts = [];
        $finalPrompt = $wpjobportal_prompt ?? '';

        // Loop through inputs (same as your existing loop)
        if (!empty($wpjobportal_schema_inputs) && is_array($wpjobportal_schema_inputs)) {
            foreach ($wpjobportal_schema_inputs as $key => $value) {

                $clean_key = sanitize_text_field($key);
                $clean_val = sanitize_text_field($value);

                if ($clean_val !== '') {
                    // Same as: schemaData[key] = val;
                    $api_variables[$clean_key] = $clean_val;

                    // Same as: structuredTextParts.push(key + ': ' + val);
                    $structuredTextParts[] = $clean_key . ': ' . $clean_val;
                }
            }
        }

        // Same as: structuredTextParts.join('\n')
        $structuredText = implode("\n", $structuredTextParts);

        // Same as JS condition
        if (!empty($finalPrompt) && !empty($structuredText)) {
            $finalPrompt .= "\n\n" . $structuredText;
        } elseif (!empty($structuredText)) {
            $finalPrompt = $structuredText;
        }


        // Build the payload
        $wpjobportal_payloadData = array(
            'model' => $wpjobportal_model,
            'wrapperCodes' => array($wrapper_code),
            'prompt' => $finalPrompt
        );

        // 3. Attach the finalized variables array to the main payload
        if (!empty($api_variables)) {
            $wpjobportal_payloadData['variables'] = $api_variables;
        }


        if (!empty($wpjobportal_language)) {
            $wpjobportal_payloadData['language'] = $wpjobportal_language;
        }
        if (!empty($wpjobportal_overrides) && is_array($wpjobportal_overrides)) {
            // Sanitize overrides keys/values
            $clean_overrides = array();
            foreach($wpjobportal_overrides as $wpjobportal_k => $v) {
                $clean_overrides[sanitize_key($wpjobportal_k)] = sanitize_text_field($v);
            }
            $wpjobportal_payloadData = array_merge($wpjobportal_payloadData, $clean_overrides);
        }



        // echo '<pre>';print_r($wpjobportal_payloadData);exit;

        $wpjobportal_url = 'https://api.zywrap.com/v1/proxy';
        $wpjobportal_args = array(
            'method'  => 'POST',
            'timeout' => 300, // Longer timeout for generation
            'sslverify'   => false,
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key
            ),
            'body'    => json_encode($wpjobportal_payloadData)
        );

        $response = wp_remote_post($wpjobportal_url, $wpjobportal_args);

        if (is_wp_error($response)) {
            $wpjobportal_error_msg = $response->get_error_message();
            $this->log_api_call('proxy_execute', 'error', [
                'wrapper_code' => $wrapper_code,
                'model_code' => $wpjobportal_model,
                'error_message' => $wpjobportal_error_msg
            ]);
            wp_send_json_error(array('message' => 'Error: ' . $wpjobportal_error_msg));
        }

        $http_code = wp_remote_retrieve_response_code($response);
        // Check for non-200 status codes
        if ($http_code !== 200) {
            $wpjobportal_error_message = $wpjobportal_data['message'] ?? 'An API error occurred.';
            $this->log_api_call('proxy_execute', 'error', [
                'wrapper_code' => $wrapper_code,
                'model_code' => $wpjobportal_model,
                'http_code' => $http_code,
                'error_message' => $wpjobportal_error_message
            ]);
            wp_send_json_error(array('message' => "Error (Code $http_code): $wpjobportal_error_message"));
        }else{
            $body = wp_remote_retrieve_body($response);
            $lines = explode("\n", $body);
            $finalJson = null;

            foreach ($lines as $line) {
                $line = trim($line);
                if (strpos($line, 'data: ') === 0) {
                    $jsonStr = substr($line, 6);
                    // Try decoding to see if it's the valid payload
                    $data = json_decode($jsonStr, true);
                    if ($data && (isset($data['output']) || isset($data['error']))) {
                        $finalJson = $jsonStr;
                    }
                }
            }

            if ($finalJson) {
                $wpjobportal_data = json_decode($finalJson, true);


                $wpjobportal_token_data = $wpjobportal_data['usage'] ?? null;

                $this->log_api_call('proxy_execute', 'success', [
                    'wrapper_code' => $wrapper_code,
                    'model_code' => $wpjobportal_model,
                    'http_code' => $http_code,
                    'token_data' => $wpjobportal_token_data
                ]);

                // --- NEW HOOK INTEGRATION START ---
                // Blueprint: wpjobportal_ai_content_generation_completed
                // Triggered after the AI Content Generation feature produces a response.
                // Permits developers to log AI usage metrics for internal auditing.
                $generated_text = isset($wpjobportal_data['output']) ? $wpjobportal_data['output'] : '';
                do_action('wpjobportal_ai_content_generation_completed', 0, $generated_text, $wpjobportal_payloadData);
                // --- NEW HOOK INTEGRATION END ---

                wp_send_json_success($wpjobportal_data);
            }else{
                wp_send_json_error(array(
                    'status'  =>  'error',
                    'message' => 'Failed to parse streaming response from Zywrap.'
                ));
            }
        }

    }
}
?>
