<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALzywrapController {

    private $_msgkey;

    function __construct() {
        self::handleRequest();
        $this->_msgkey = WPJOBPORTALincluder::getJSModel('wpjobportal')->getMessagekey();
    }

    // Handles which template file to load (Settings vs. Playground)
    function handleRequest() {
        $wpjobportal_layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'zywrap');
        // Determine layout from request, default to 'zywrap' (Settings)
        // $wpjobportal_layout = isset($_REQUEST['wpjobportallt']) ? sanitize_text_field($_REQUEST['wpjobportallt']) : 'zywrap';

        // // Map layout names to actual template files
        // $wpjobportal_template = ($wpjobportal_layout == 'playground') ? 'admin_playground' : 'admin_zywrap';

        // // Include the template file
        // WPJOBPORTALincluder::getTemplate($wpjobportal_template, array('wpjobportal_module' => 'zywrap', 'wpjobportal_layouts' => $wpjobportal_layout));

        if (self::canaddfile($wpjobportal_layout)) {
            switch ($wpjobportal_layout) {
                case 'zywrap': // admin_zywrap
                case 'admin_zywrap': // admin_zywrap
                    break;
                case 'playground': // admin_playground
                case 'admin_playground': // admin_playground
                    // This is our new Playground page, load all the dropdown data
                    // Calls getPlaygroundData() in WPJOBPORTALwpjobportalModel
                    WPJOBPORTALincluder::getJSModel('zywrap')->getPlaygroundData();
                    break;
                default:
                    exit;
            }
            $wpjobportal_module = (wpjobportal::$_common->wpjp_isadmin()) ? 'page' : 'wpjobportalme';
            $wpjobportal_module = WPJOBPORTALrequest::getVar($wpjobportal_module, null, 'wpjobportal');
            $wpjobportal_module = wpjobportalphplib::wpJP_str_replace('wpjobportal_', '', $wpjobportal_module);
            if($wpjobportal_layout=="thankyou"){
                if($wpjobportal_module=="" || $wpjobportal_module!="wpjobportal") $wpjobportal_module="wpjobportal";
            }
            wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
            WPJOBPORTALincluder::include_file($wpjobportal_layout, $wpjobportal_module);
        }
    }

    function canaddfile($wpjobportal_layout) {
        $wpjobportal_nonce_value = WPJOBPORTALrequest::getVar('wpjobportal_nonce');
        if ( wp_verify_nonce( $wpjobportal_nonce_value, 'wpjobportal_nonce') ) {
            if (isset($_POST['form_request']) && $_POST['form_request'] == 'wpjobportal')
                return false;
            elseif (isset($_GET['action']) && $_GET['action'] == 'wpjobportaltask')
                return false;
            else{
                if(!is_admin() && strpos($wpjobportal_layout, 'admin_') === 0){
                    return false;
                }
                return true;
            }
        }
    }

    // Handles the "Save Key" form submission
    function save_zywrap_settings() {
        if (!current_user_can('manage_options')) {
            die('Security check failed');
        }
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($wpjobportal_nonce, 'save-zywrap-settings')) {
            die('Security check Failed');
        }
        $api_key = WPJOBPORTALrequest::getVar('wpjobportal_zywrap_api_key');
        if(!empty($api_key)){
            $response = WPJOBPORTALincluder::getJSModel('zywrap')->storeZywrapApiKey($api_key);
            if(!empty($response)){
                update_option('wpjobportal_zywrap_api_status', $response);
            }
        }
        //update_option('wpjobportal_zywrap_api_key', $api_key);

        // Use WP Job Portal's message system to show "Saved!"
        $wpjobportal_msg = WPJOBPORTALMessages::getMessage(WPJOBPORTAL_SAVED, 'configuration');
        WPJOBPORTALMessages::setLayoutMessage($wpjobportal_msg['message'], $wpjobportal_msg['status'], $this->_msgkey);

        $wpjobportal_url = admin_url("admin.php?page=wpjobportal_zywrap&wpjobportallt=zywrap");
        wp_redirect($wpjobportal_url);
        die();
    }
}

$WPJOBPORTALzywrapController = new WPJOBPORTALzywrapController();

/*
add_action('add_meta_boxes', ('add_zywrap_meta_box'));
// === NEW: META BOX FUNCTIONS ===

    /**
     * Registers the meta box on posts, pages, and products.
     *
     */
/* function add_zywrap_meta_box() {
        // Only show the box if the API key is set
        if (!get_option('wpjobportal_zywrap_api_key', '')) {
            return;
        }

        $post_types = array('post', 'page', 'product'); // Target these post types
        foreach ($post_types as $post_type) {
            add_meta_box(
                'wpjobportal_zywrap_meta_box', // ID
                __('Zywrap Content Generator', 'wp-job-portal'), // Title
                array($this, 'render_zywrap_meta_box'), // Callback
                $post_type, // Screen
                'side', // Context (sidebar)
                'high' // Priority
            );
        }
    }
*/
?>