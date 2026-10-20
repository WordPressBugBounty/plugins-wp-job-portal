<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALwpjpsession {

    public $sessionid;
    public $sessionexpire;
    private $sessiondata;
    private $datafor;
    private $nextsessionexpire;

    function __construct( ) {
        $this->init();
    }

    function init(){
        if (WPJOBPORTALrequest::hasCookie('_wpjsjp_session_')) {
            $cookie = WPJOBPORTALrequest::cookie('_wpjsjp_session_'); // unslashed there, as wpJP_stripslashes() did here
            $wpjobportal_user_cookie = wpjobportalphplib::wpJP_explode('/', $cookie);
            $this->sessionid = wpjobportalphplib::wpJP_preg_replace("/[^A-Za-z0-9_]/", '', $wpjobportal_user_cookie[0]);
            $this->sessionexpire = absint($wpjobportal_user_cookie[1]);
            $this->nextsessionexpire = absint($wpjobportal_user_cookie[2]);
            // Update options session expiration
            if (time() > $this->nextsessionexpire) {
                $this->jsjp_set_cookies_expiration();
            }
        } else {
            $wpjobportal_sessionid = $this->jsjp_generate_id();
            $this->sessionid = $wpjobportal_sessionid . get_option( '_wpjsjp_session_', 0 );
            $this->jsjp_set_cookies_expiration();
        }
        // Network-activated plugins are loaded before WordPress defines its
        // cookie constants, and reading COOKIEPATH then was a fatal error on
        // every request of the network. Set the cookie once they exist.
        if (defined('COOKIEPATH') && defined('COOKIE_DOMAIN')) {
            $this->jshd_set_user_cookies();
        } else {
            add_action('plugins_loaded', function () {
                $this->jshd_set_user_cookies();
            }, 0);
        }
        return $this->sessionid;
    }

    private function jsjp_set_cookies_expiration(){
        $this->sessionexpire = time() + (int)(30*60);
        $this->nextsessionexpire = time() + (int)(60*60);
    }

    private function jsjp_generate_id(){
        do_action('wpjobportal_load_phpass');

        $hash = new PasswordHash( 16, false );

        return wpjobportalphplib::wpJP_md5( $hash->get_random_bytes( 32 ) );
    }

    private function jshd_set_user_cookies(){
        wpjobportalphplib::wpJP_setcookie( '_wpjsjp_session_', $this->sessionid . '/' . $this->sessionexpire . '/' . $this->nextsessionexpire , $this->sessionexpire, COOKIEPATH, COOKIE_DOMAIN);
        $wpjobportal_count = get_option( '_wpjsjp_session_', 0 );
        update_option( '_wpjsjp_session_', ++$wpjobportal_count);
    }

}

?>