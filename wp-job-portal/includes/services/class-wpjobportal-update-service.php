<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Updates for every WP Job Portal product, through WordPress's own screens.
 *
 * The system this replaces had its own three-step wizard, its own "premium
 * add-ons" screens, and its own idea of what installing meant. A customer had
 * to learn a second updater that lived beside the one WordPress already gave
 * them, and any plugin the wizard did not know about simply never updated.
 *
 * There is nothing to learn here. Products appear in Dashboard → Updates and on
 * the Plugins screen exactly as any other plugin does, with the same "update
 * now" link, the same bulk selection, the same automatic-update controls, and
 * the same View details popup. WordPress does the downloading and installing;
 * this only answers the two questions it asks:
 *
 *   pre_set_site_transient_update_plugins — is there a newer version?
 *   plugins_api                           — tell me about this plugin.
 *
 * The rules it holds to, in order of importance:
 *
 * 1. An unreachable or wrong server offers no updates and disables nothing. A
 *    customer's site does not change behaviour because our server had a bad
 *    minute.
 * 2. A product with no licence keeps working. Only the update offer stops,
 *    which is what the published policy says happens when a licence lapses.
 * 3. Nothing is offered without a package URL from a host we accept, and no
 *    reply is ever executed — it is data, read into fields, and nothing else.
 */
class WPJOBPORTAL_Update_Service {

    /** @var WPJOBPORTAL_Update_Client|null */
    private static $client = null;

    public static function register() {
        add_filter('pre_set_site_transient_update_plugins', array(__CLASS__, 'offer_updates'));
        add_filter('plugins_api', array(__CLASS__, 'plugin_details'), 10, 3);

        // A customer who has just pasted a licence key should not wait twelve
        // hours to see what it unlocked.
        add_action('update_option_wpjobportal_license_key', array(__CLASS__, 'forget'));
        add_action('upgrader_process_complete', array(__CLASS__, 'forget'));

        add_filter('wpjobportal_update_manifest', array(__CLASS__, 'manifest'));
    }

    /**
     * @param WPJOBPORTAL_Update_Client|null $client Injected for tests.
     * @return void
     */
    public static function use_client($client) {
        self::$client = $client;
    }

    /**
     * @return WPJOBPORTAL_Update_Client
     */
    private static function client() {
        if (self::$client === null) {
            self::$client = new WPJOBPORTAL_Update_Client();
        }

        return self::$client;
    }

    /**
     * What the endpoint currently says.
     *
     * @param bool $force Ignore the cache.
     * @return array
     */
    public static function manifest($force = false) {
        return self::client()->manifest((bool) $force);
    }

    /**
     * Drop the cached answer.
     *
     * @return void
     */
    public static function forget() {
        WPJOBPORTAL_Update_Client::flush();
    }

    /**
     * Tell WordPress which of our plugins have a newer version.
     *
     * @param object $transient WordPress's update data.
     * @return object
     */
    public static function offer_updates($transient) {
        if (!is_object($transient)) {
            return $transient;
        }

        $manifest = self::manifest();
        if (empty($manifest)) {
            return $transient;
        }

        if (!isset($transient->response) || !is_array($transient->response)) {
            $transient->response = array();
        }
        if (!isset($transient->no_update) || !is_array($transient->no_update)) {
            $transient->no_update = array();
        }

        $installed = WPJOBPORTAL_Update_Client::installed();

        foreach ($manifest as $slug => $product) {
            $file = $product['file'];

            if (!isset($installed[$file])) {
                continue;
            }

            $current = $installed[$file];
            $offered = $product['version'];

            $item = (object) array(
                'id' => $file,
                'slug' => $slug,
                'plugin' => $file,
                'new_version' => $offered !== '' ? $offered : $current,
                'url' => $product['homepage'],
                'package' => $product['package'],
                'requires' => $product['requires'],
                'requires_php' => $product['requires_php'],
                'tested' => $product['tested'],
            );

            $newer = ($offered !== '' && version_compare($offered, $current, '>'));

            // An update with nothing to download is not an update. Offering one
            // gives the customer a button that fails.
            if ($newer && $product['package'] !== '') {
                $transient->response[$file] = $item;
                unset($transient->no_update[$file]);
                continue;
            }

            // Listed as up to date so WordPress shows it under automatic
            // updates and stops treating it as an unknown plugin.
            $transient->no_update[$file] = $item;
            unset($transient->response[$file]);
        }

        return $transient;
    }

    /**
     * Fill in the View details popup.
     *
     * @param false|object|array $result What WordPress has so far.
     * @param string             $action What it is asking for.
     * @param object             $args   Which plugin.
     * @return false|object|array
     */
    public static function plugin_details($result, $action, $args) {
        if ($action !== 'plugin_information' || empty($args->slug)) {
            return $result;
        }

        $slug = (string) $args->slug;
        if (strpos($slug, 'wp-job-portal') !== 0) {
            return $result;
        }

        $manifest = self::manifest();
        if (!isset($manifest[$slug])) {
            return $result;
        }

        $product = $manifest[$slug];

        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugins = (array) get_plugins();
        $data = isset($plugins[$product['file']]) ? $plugins[$product['file']] : array();

        return (object) array(
            'name' => isset($data['Name']) ? $data['Name'] : $slug,
            'slug' => $slug,
            'version' => $product['version'] !== '' ? $product['version'] : (isset($data['Version']) ? $data['Version'] : ''),
            'author' => isset($data['Author']) ? $data['Author'] : '',
            'homepage' => $product['homepage'],
            'requires' => $product['requires'],
            'requires_php' => $product['requires_php'],
            'tested' => $product['tested'],
            'download_link' => $product['package'],
            'sections' => array(
                'description' => isset($data['Description']) ? $data['Description'] : '',
                'changelog' => $product['changelog'],
            ),
        );
    }

    /**
     * A short account of what the endpoint said, for the admin status screen.
     *
     * @return array
     */
    public static function status() {
        $manifest = self::manifest();

        $licensed = 0;
        $updates = 0;
        $installed = WPJOBPORTAL_Update_Client::installed();

        foreach ($manifest as $product) {
            if ($product['licensed']) { $licensed++; }

            $current = isset($installed[$product['file']]) ? $installed[$product['file']] : '';
            if ($product['version'] !== '' && $current !== ''
                && version_compare($product['version'], $current, '>')
                && $product['package'] !== ''
            ) {
                $updates++;
            }
        }

        return array(
            'endpoint' => WPJOBPORTAL_Update_Client::endpoint(),
            'answered' => !empty($manifest),
            'products' => count($manifest),
            'licensed' => $licensed,
            'updates' => $updates,
            'installed' => count($installed),
        );
    }
}
