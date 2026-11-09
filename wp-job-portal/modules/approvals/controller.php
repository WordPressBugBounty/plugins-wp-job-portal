<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * Approvals (design 4): one page, Everything waiting, with each approval queue
 * as a tab. The queue screens keep their own URLs and actions.
 */
class WPJOBPORTALapprovalsController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $wpjobportal_layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'approvals');
        if (self::canaddfile($wpjobportal_layout)) {
            switch ($wpjobportal_layout) {
                case 'admin_approvals':
                    WPJOBPORTALincluder::getJSModel('approvals')->getEverythingWaiting(sanitize_key((string) WPJOBPORTALrequest::getVar('type')), sanitize_text_field((string) WPJOBPORTALrequest::getVar('q')));
                    break;
                default:
                    return;
            }
            wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
            WPJOBPORTALincluder::include_file($wpjobportal_layout, 'approvals');
        }
    }

    function canaddfile($wpjobportal_layout) {
        if (!current_user_can('manage_options')) {
            return false;
        }
        // Only routing: a form post or a task is handled elsewhere, with its own nonce
        if (WPJOBPORTALrequest::getVar('form_request', 'post') == 'wpjobportal') {
            return false;
        }
        if (WPJOBPORTALrequest::getVar('action', 'get') == 'wpjobportaltask') {
            return false;
        }
        return is_admin() || strpos($wpjobportal_layout, 'admin_') !== 0;
    }

}

$WPJOBPORTALapprovalsController = new WPJOBPORTALapprovalsController();
?>
