<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Offer to load the city list on a site that has none.
 *
 * Core creates the cities table but seeds no rows, so a new install has an
 * empty city list until an administrator finds Load Address Data. Nothing says
 * the screen exists, and the symptom shows up somewhere else entirely — an
 * employer posting a job cannot pick a location.
 *
 * So the site asks, once, on its own admin pages: here is the country you look
 * like you are in, here is how many cities that would add, one button. The
 * button posts to the same route as the import form with the same nonce, which
 * means this adds no new way to write to the database — it is that form,
 * pre-filled.
 *
 * The notice stops appearing on its own once any city exists, however it got
 * there, so a site that imports through the normal screen never sees it again.
 *
 * @see WPJOBPORTAL_Site_Country_Service for how the country is guessed.
 */
class WPJOBPORTAL_Address_Setup_Notice {

    /** Per administrator, matching how the onboarding checklist is dismissed. */
    const DISMISSED_META = 'wpjobportal_address_notice_dismissed';

    const DISMISS_ACTION = 'wpjobportal_dismiss_address_notice';

    public static function register() {
        add_action('admin_notices', array(__CLASS__, 'render'));
        add_action('admin_post_' . self::DISMISS_ACTION, array(__CLASS__, 'dismiss'));
    }

    /**
     * Show the prompt, when there is something to prompt about.
     *
     * @return void
     */
    public static function render() {
        if (!current_user_can('wpjobportal') && !current_user_can('manage_options')) {
            return;
        }

        $page = sanitize_key((string) WPJOBPORTALrequest::getVar('page', 'get', ''));
        if (strpos($page, 'wpjobportal') !== 0) {
            return;
        }

        // Not on the import screen itself: they are already there, and the form
        // below them says all of this better.
        $layout = sanitize_key((string) WPJOBPORTALrequest::getVar('wpjobportallt', 'get', ''));
        if ($page === 'wpjobportal_city' && $layout === 'loadaddressdata') {
            return;
        }

        if (get_user_meta(get_current_user_id(), self::DISMISSED_META, true)) {
            return;
        }

        if (self::has_cities()) {
            return;
        }

        $code = WPJOBPORTAL_Site_Country_Service::country_code();
        $country = WPJOBPORTAL_Site_Country_Service::country_name();
        $available = $code === '' ? 0 : self::available_for($code);

        $screen = admin_url('admin.php?page=wpjobportal_city&wpjobportallt=loadaddressdata');

        ?>
        <div class="notice notice-warning wpjobportal-address-setup-notice">
            <p>
                <strong><?php echo esc_html__('No cities have been loaded yet.', 'wp-job-portal'); ?></strong>
                <?php echo esc_html__('Employers cannot choose a job location until a city list exists.', 'wp-job-portal'); ?>
            </p>
            <?php if ($available > 0) { ?>
                <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_city')); ?>">
                    <input type="hidden" name="form_request" value="wpjobportal" />
                    <input type="hidden" name="task" value="loadaddressdata" />
                    <input type="hidden" name="action" value="city_loadaddressdata" />
                    <input type="hidden" name="country_code" value="<?php echo esc_attr($code); ?>" />
                    <input type="hidden" name="data_to_import" value="free" />
                    <input type="hidden" name="keepdata" value="1" />
                    <input type="hidden" name="name_preference" value="1" />
                    <input type="hidden" name="_wpnonce" value="<?php echo esc_attr(wp_create_nonce('wpjobportal_address_data_nonce')); ?>" />
                    <p>
                        <button type="submit" class="button button-primary">
                            <?php
                                /* translators: 1: number of cities, 2: country name. */
                                echo esc_html(sprintf(
                                    /* translators: 1: available, 2: country */ _n('Import %1$d city for %2$s', 'Import %1$d cities for %2$s', $available, 'wp-job-portal'),
                                    $available,
                                    $country
                                ));
                            ?>
                        </button>
                        <a class="button button-secondary" href="<?php echo esc_url($screen); ?>">
                            <?php echo esc_html__('Choose a different country', 'wp-job-portal'); ?>
                        </a>
                        <a href="<?php echo esc_url(self::dismiss_url()); ?>">
                            <?php echo esc_html__('Not now', 'wp-job-portal'); ?>
                        </a>
                    </p>
                </form>
            <?php } else { ?>
                <p>
                    <a class="button button-primary" href="<?php echo esc_url($screen); ?>">
                        <?php echo esc_html__('Choose a country to import', 'wp-job-portal'); ?>
                    </a>
                    <a href="<?php echo esc_url(self::dismiss_url()); ?>">
                        <?php echo esc_html__('Not now', 'wp-job-portal'); ?>
                    </a>
                </p>
            <?php } ?>
        </div>
        <?php
    }

    /**
     * Stop showing the prompt to this administrator.
     *
     * @return void
     */
    public static function dismiss() {
        if (!current_user_can('wpjobportal') && !current_user_can('manage_options')) {
            wp_die(esc_html__('You are not allowed to do this.', 'wp-job-portal'));
        }

        check_admin_referer(self::DISMISS_ACTION);

        update_user_meta(get_current_user_id(), self::DISMISSED_META, 1);

        $back = wp_get_referer();
        wp_safe_redirect($back ? $back : admin_url('admin.php?page=wpjobportal'));
        exit;
    }

    /**
     * Show the prompt again. For tests and for a support instruction.
     *
     * @param int $user_id WordPress user id.
     * @return bool
     */
    public static function reopen($user_id) {
        $user_id = absint($user_id);
        if ($user_id <= 0) {
            return false;
        }

        delete_user_meta($user_id, self::DISMISSED_META);

        return true;
    }

    /**
     * Whether the site has any city at all.
     *
     * Asked on every WP Job Portal admin page, so it stops at the first row
     * rather than counting a table that may hold tens of thousands.
     *
     * @return bool
     */
    public static function has_cities() {
        global $wpdb;

        return null !== wpjobportaldb_direct::get_var("SELECT `id` FROM `{$wpdb->prefix}wj_portal_cities` LIMIT 1");
    }

    /**
     * How many cities the free data holds for a country.
     *
     * Counted from the shipped file rather than assumed, so the button never
     * promises a number the import will not deliver.
     *
     * @param string $code Country code, as stored in the countries table.
     * @return int
     */
    public static function available_for($code) {
        $code = strtolower(preg_replace('/[^A-Za-z]/', '', (string) $code));
        if ($code === '') {
            return 0;
        }

        $file = WPJOBPORTAL_PLUGIN_PATH . 'includes/data/cities/' . $code . '/cities.txt';
        if (!file_exists($file)) {
            return 0;
        }

        $contents = file_get_contents($file);
        if ($contents === false) {
            return 0;
        }

        // One row per line, each opening with the quoted city name.
        return substr_count($contents, "\n(\"") + (strncmp(ltrim($contents), '("', 2) === 0 ? 1 : 0);
    }

    /**
     * @return string
     */
    private static function dismiss_url() {
        return wp_nonce_url(
            admin_url('admin-post.php?action=' . self::DISMISS_ACTION),
            self::DISMISS_ACTION
        );
    }
}
