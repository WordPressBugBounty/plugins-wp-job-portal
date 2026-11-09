<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Google Maps for the job board, only with the site owner's own key (D-10).
 *
 * Every install used to ship with the same built-in key, so all sites shared one
 * daily quota: when it ran out, maps broke everywhere at once, and the traffic
 * ran through a key the vendor did not control. That key now counts as no key
 * (the 2.8.0 migration also clears it from the settings).
 *
 * With no key the Maps script is not loaded at all: script_url() is empty, and
 * WordPress prints nothing for a script without an address. The map boxes are
 * hidden and a do-nothing stand-in for google.maps keeps the plugin's map code
 * (which builds maps as soon as a page loads) from throwing and stopping other
 * scripts. Latitude and longitude fields are separate inputs and stay.
 */
class WPJOBPORTAL_Google_Maps {

    /** The key every 2.7 install was seeded with. */
    const SHARED_KEY = 'AIzaSyCZcnAK0DiGg8lAXej74e7PlrhkfCM86-M';

    const DISMISSED_META = 'wpjobportal_maps_notice_dismissed';
    const DISMISS_ACTION = 'wpjobportal_dismiss_maps_notice';

    public static function register() {
        add_action('wp_head', array(__CLASS__, 'print_fallback'), 1);
        add_action('admin_head', array(__CLASS__, 'print_fallback'), 1);
        add_action('admin_notices', array(__CLASS__, 'render_notice'));
        add_action('admin_post_' . self::DISMISS_ACTION, array(__CLASS__, 'dismiss'));
    }

    /** The site's own key, or '' (the shared built-in key counts as none). */
    public static function key() {
        $key = '';
        if (isset(wpjobportal::$_configuration['google_map_api_key'])) {
            $key = (string) wpjobportal::$_configuration['google_map_api_key'];
        } elseif (isset(wpjobportal::$_config)) {
            $key = (string) wpjobportal::$_config->getConfigValue('google_map_api_key');
        }
        $key = trim($key);
        return ($key === self::SHARED_KEY) ? '' : $key;
    }

    public static function enabled() {
        return self::key() !== '';
    }

    /**
     * Whether the site draws its maps with Google. OpenStreetMap needs no key, so
     * without one only Google maps are missing.
     */
    public static function uses_google() {
        $service = '';
        if (isset(wpjobportal::$_configuration['mappingservice'])) {
            $service = (string) wpjobportal::$_configuration['mappingservice'];
        } elseif (isset(wpjobportal::$_config)) {
            $service = (string) wpjobportal::$_config->getConfigValue('mappingservice');
        }
        return $service !== 'osm';
    }

    /** The Maps script address (always https), or '' when there is no key. */
    public static function script_url() {
        $key = self::key();
        return $key === '' ? '' : 'https://maps.googleapis.com/maps/api/js?key=' . rawurlencode($key);
    }

    /**
     * Without a key: hide the map boxes and give the map code a stand-in that does nothing.
     *
     * @return void
     */
    public static function print_fallback() {
        if (self::enabled()) {
            return;
        }
        // Hide the map boxes only when the maps would be Google's: an OpenStreetMap
        // site has working maps without a key, and hiding them took its maps away.
        // The job page's Map section goes with its box, or its heading stood over
        // an empty card.
        if (self::uses_google()) {
            echo '<style id="wpjobportal-no-maps">'
                . '[id^="map_container"],#map-canvas,.wjportal-form-map,.wpjobportal-form-map,.resume-map,.wjportal-map-wrp,#map,.wjportal-job-data-map-wrp,.wjportal-job-map{display:none!important}'
                . '</style>' . "\n";
        }
        // Constructors and helpers the plugin's map code touches; each one does nothing.
        echo '<script id="wpjobportal-no-maps-js">(function(w){if(w.google&&w.google.maps){return;}'
            . 'var N=function(){};N.prototype={setCenter:N,setZoom:N,setMap:N,setPosition:N,getPosition:function(){return null;},open:N,close:N,setContent:N,fitBounds:N,extend:N,panTo:N,addListener:N};'
            . 'var F=function(){var f=function(){};f.prototype=N.prototype;return f;};'
            . 'w.google=w.google||{};w.google.maps={Map:F(),Marker:F(),InfoWindow:F(),LatLng:F(),LatLngBounds:F(),Geocoder:F(),Circle:F(),'
            . 'places:{Autocomplete:F(),SearchBox:F()},MapTypeId:{ROADMAP:"roadmap",SATELLITE:"satellite",HYBRID:"hybrid",TERRAIN:"terrain"},'
            . 'Animation:{},ControlPosition:{},event:{addListener:N,addDomListener:N,trigger:N,clearListeners:N},importLibrary:function(){return Promise.reject(new Error("no Google Maps key"));}};'
            . 'w.wpjobportalMapsDisabled=true;})(window);</script>' . "\n";
    }

    /**
     * Admins, on the plugin's pages: maps are off until the site has its own key.
     *
     * @return void
     */
    public static function render_notice() {
        if (self::enabled() || !self::uses_google() || (!current_user_can('wpjobportal') && !current_user_can('manage_options'))) {
            return;
        }
        $page = sanitize_key((string) WPJOBPORTALrequest::getVar('page', 'get', ''));
        if (strpos($page, 'wpjobportal') !== 0 || get_user_meta(get_current_user_id(), self::DISMISSED_META, true)) {
            return;
        }
        $settings = admin_url('admin.php?page=wpjobportal_configuration');
        $dismiss = wp_nonce_url(admin_url('admin-post.php?action=' . self::DISMISS_ACTION), self::DISMISS_ACTION);
        ?>
        <div class="notice notice-info wpjobportal-maps-notice">
            <p>
                <strong><?php echo esc_html__('Add your Google Maps key in Settings to show maps.', 'wp-job-portal'); ?></strong>
                <?php echo esc_html__('Job and resume maps stay hidden until then; latitude and longitude still save.', 'wp-job-portal'); ?>
                <?php
                // Only a site that had the shared key lost anything; a new one never had it
                if (get_option('wpjobportal_280_shared_maps_key_removed')) {
                    echo esc_html__('Version 2.8.0 removed the key every site used to share, because its daily limit kept running out for everyone at once.', 'wp-job-portal');
                } ?>
            </p>
            <p>
                <a class="button button-primary" href="<?php echo esc_url($settings); ?>"><?php echo esc_html__('Open Settings', 'wp-job-portal'); ?></a>
                <a class="button-link" href="<?php echo esc_url($dismiss); ?>"><?php echo esc_html__('Dismiss', 'wp-job-portal'); ?></a>
            </p>
        </div>
        <?php
    }

    /**
     * @return void
     */
    public static function dismiss() {
        if ((!current_user_can('wpjobportal') && !current_user_can('manage_options')) || !check_admin_referer(self::DISMISS_ACTION)) {
            wp_die(esc_html__('You are not allowed to do that.', 'wp-job-portal'));
        }
        update_user_meta(get_current_user_id(), self::DISMISSED_META, 1);
        wp_safe_redirect(wp_get_referer() ? wp_get_referer() : admin_url('admin.php?page=wpjobportal'));
        exit;
    }
}
