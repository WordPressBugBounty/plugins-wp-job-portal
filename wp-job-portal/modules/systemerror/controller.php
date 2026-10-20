<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALsystemerrorController {

    function __construct() {

        self::handleRequest();
    }

    function handleRequest() {
        $wpjobportal_layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'systemerrors');

        if (self::canaddfile($wpjobportal_layout)) {
            switch ($wpjobportal_layout) {
                case 'admin_systemerrors':
                    WPJOBPORTALincluder::getJSModel('systemerror')->getSystemErrors();
                    break;

                /*
                 * 'admin_addsystemerror' used to be handled here and is gone.
                 *
                 * It called getsystemerrorForForm() on the systemerror model,
                 * which does not define it - and nor does anything else, here or
                 * in the original distribution. There is no template for the
                 * layout and nothing routes to it, so it could never have
                 * rendered; reaching it would have fataled. The id it read came
                 * from a request variable named jssupportticketid, which belongs
                 * to a different product altogether.
                 *
                 * The System Errors listing next door is a real screen, linked
                 * from the admin menu, and is untouched.
                 */
                default:
                    return;
            }
            $wpjobportal_module = (wpjobportal::$_common->wpjp_isadmin()) ? 'page' : 'wpjobportalme';
            $wpjobportal_module = WPJOBPORTALrequest::getVar($wpjobportal_module, null, 'systemerror');
            $wpjobportal_module = wpjobportalphplib::wpJP_str_replace('wpjobportal_', '', $wpjobportal_module);
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

}

$wpjobportal_systemerrorController = new WPJOBPORTALsystemerrorController();
?>
