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

    // The 2.8.0 "Save Key" form: a Zywrap key typed by hand. Kept for old links;
    // the AI screen's own form posts to save_ai_settings.
    function save_zywrap_settings() {
        if (!current_user_can('manage_options')) {
            die('Security check failed');
        }
        if (!wp_verify_nonce(WPJOBPORTALrequest::getVar('_wpnonce'), 'save-zywrap-settings')) {
            die('Security check Failed');
        }
        $api_key = trim((string) WPJOBPORTALrequest::getVar('wpjobportal_zywrap_api_key'));
        if ($api_key !== '') {
            $this->aiNotice(WPJOBPORTAL_AI_Engine::saveKey('zywrap', $api_key), __('Zywrap key saved.', 'wp-job-portal'));
        }
        wp_safe_redirect(admin_url('admin.php?page=wpjobportal_zywrap&wpjobportallt=zywrap'));
        die();
    }

    private function aiNotice($result, $success) {
        if (is_wp_error($result)) {
            WPJOBPORTALMessages::setLayoutMessage(esc_html($result->get_error_message()), 'error', $this->_msgkey);
        } elseif ($success !== '') {
            WPJOBPORTALMessages::setLayoutMessage(esc_html($success), 'updated', $this->_msgkey);
        }
    }

    /**
     * Everything on the AI screen (2.9.0): engine, keys, models, switches,
     * limits, and the one-click actions (test, forget a key, disconnect Zywrap,
     * claim the licence credits, connect on zywrap.com). Administrators only;
     * the dispatcher has already checked the role, the method and the nonce.
     */
    function save_ai_settings() {
        if (!current_user_can('manage_options') || !wp_verify_nonce(WPJOBPORTALrequest::getVar('_wpnonce'), 'save-ai-settings')) {
            die('Security check failed');
        }
        $tab = sanitize_key((string) WPJOBPORTALrequest::getVar('tab', 'post', 'engine'));
        if (!in_array($tab, array('engine', 'features', 'usage', 'privacy'), true)) {
            $tab = 'engine';
        }
        $back = admin_url('admin.php?page=wpjobportal_zywrap&wpjobportallt=zywrap&tab=' . $tab);
        $do = sanitize_key((string) WPJOBPORTALrequest::getVar('do', 'post', 'save'));
        $engine = sanitize_key((string) WPJOBPORTALrequest::getVar('engine', 'post', ''));

        switch ($do) {
            case 'test':
                $test = WPJOBPORTAL_AI_Engine::selfTest();
                if ($test['ok']) {
                    $this->aiNotice(true, sprintf(
                        /* translators: 1: the AI engine, 2: milliseconds */
                        __('%1$s answered in %2$s ms. AI is working.', 'wp-job-portal'),
                        WPJOBPORTAL_AI_Engine::currentLabel(),
                        number_format_i18n($test['ms'])
                    ));
                } else {
                    $this->aiNotice(new WP_Error('test', sprintf(
                        /* translators: %s: the reason the test failed */
                        __('The test did not pass: %s', 'wp-job-portal'),
                        $test['detail']
                    )), '');
                }
                break;
            case 'forget':
                if (WPJOBPORTAL_AI_Engine::byId($engine)) {
                    WPJOBPORTAL_AI_Engine::forgetKey($engine);
                    WPJOBPORTAL_AI_Policy::record('key-forget', $engine);
                    $this->aiNotice(true, __('The key was removed from this site.', 'wp-job-portal'));
                }
                break;
            case 'disconnect':
                WPJOBPORTAL_Zywrap_Connect::disconnect();
                $this->aiNotice(true, __('Zywrap was disconnected from this site. The key stays valid on zywrap.com until you revoke it there.', 'wp-job-portal'));
                break;
            case 'claim':
                $state = WPJOBPORTAL_AI_Credits::claim();
                if ($state) {
                    $this->aiNotice(true, __('This licence\'s monthly credits now go to the connected Zywrap account.', 'wp-job-portal'));
                } else {
                    $this->aiNotice(new WP_Error('claim', __('The credits could not be claimed. Check that the licence is active and Zywrap is connected, then try again.', 'wp-job-portal')), '');
                }
                break;
            case 'browser':
                wp_redirect(WPJOBPORTAL_Zywrap_Connect::browserUrl());
                die();
            case 'save':
            default:
                $this->aiSaveForm($tab);
                break;
        }
        wp_safe_redirect($back);
        die();
    }

    private function aiSaveForm($tab) {
        $errors = array();
        if ($tab === 'engine') {
            $chosen = sanitize_key((string) WPJOBPORTALrequest::getVar('ai_engine', 'post', ''));
            if (WPJOBPORTAL_AI_Engine::byId($chosen)) {
                WPJOBPORTAL_AI_Engine::setEngine($chosen);
            }
            foreach (array('zywrap', 'anthropic', 'openai') as $id) {
                $key = trim((string) WPJOBPORTALrequest::getVar('ai_key_' . $id, 'post', ''));
                if ($key !== '') {
                    $saved = WPJOBPORTAL_AI_Engine::saveKey($id, $key);
                    if (is_wp_error($saved)) {
                        $def = WPJOBPORTAL_AI_Engine::byId($id);
                        $errors[] = $def['def']['label'] . ': ' . $saved->get_error_message();
                    }
                }
            }
            foreach (array('anthropic', 'openai', 'zywrap') as $id) {
                $model = WPJOBPORTALrequest::getVar('ai_model_' . $id, 'post', null);
                if ($model !== null) {
                    WPJOBPORTAL_AI_Engine::setModel($id, (string) $model);
                }
            }
            WPJOBPORTAL_AI_Engine::setFallback(sanitize_key((string) WPJOBPORTALrequest::getVar('ai_fallback', 'post', '')));
        } elseif ($tab === 'features') {
            WPJOBPORTAL_AI_Policy::setMaster((string) WPJOBPORTALrequest::getVar('ai_master', 'post', '0') === '1');
            $on = WPJOBPORTALrequest::getVar('ai_task', 'post', array());
            $on = is_array($on) ? $on : array();
            foreach (array_keys(WPJOBPORTAL_AI_Tasks::catalogue()) as $id) {
                WPJOBPORTAL_AI_Tasks::setEnabled($id, !empty($on[$id]));
            }
            WPJOBPORTAL_AI_Policy::record('features', '');
        } elseif ($tab === 'usage') {
            WPJOBPORTAL_AI_Usage::setBudget(array(
                'usercalls'  => WPJOBPORTALrequest::getVar('ai_usercalls', 'post', 0),
                'daycalls'   => WPJOBPORTALrequest::getVar('ai_daycalls', 'post', 0),
                'daymoney'   => WPJOBPORTALrequest::getVar('ai_daymoney', 'post', 0),
                'monthmoney' => WPJOBPORTALrequest::getVar('ai_monthmoney', 'post', 0),
                'alerts'     => (string) WPJOBPORTALrequest::getVar('ai_alerts', 'post', '0') === '1',
            ));
        } elseif ($tab === 'privacy') {
            WPJOBPORTAL_AI_Policy::setRedactionMode(sanitize_key((string) WPJOBPORTALrequest::getVar('ai_redact', 'post', 'contacts')));
            WPJOBPORTAL_AI_Policy::setDisclosure((string) WPJOBPORTALrequest::getVar('ai_disclosure', 'post', ''));
        }
        if ($errors) {
            $this->aiNotice(new WP_Error('save', implode(' ', $errors)), '');
        } else {
            $this->aiNotice(true, __('AI settings saved.', 'wp-job-portal'));
        }
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