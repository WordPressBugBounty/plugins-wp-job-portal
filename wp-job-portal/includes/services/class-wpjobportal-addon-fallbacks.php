<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * What the site shows when an add-on is switched off (D-12).
 *
 * - Shortcodes: with Recruit off, the Candidate Portal page printed the raw text
 *   [wpjobportal_candidate_portal]. Core now registers a stand-in for every
 *   add-on shortcode the add-on itself has not registered: nothing for
 *   visitors, a short "needs the … add-on" box for administrators.
 * - Admin screens: core keeps add-on screens in its menu map, and with the
 *   add-on off their module is missing, so the page was blank. It now says
 *   which add-on the screen needs and links to Plugins.
 * - Switching an add-on off: if published pages use its shortcodes, the admin
 *   is offered to set them to draft (or keep them), on the next admin page.
 */
class WPJOBPORTAL_Addon_Fallbacks {

    const PENDING_OPTION = 'wpjobportal_addon_pages_pending';
    const UNPUBLISH_ACTION = 'wpjobportal_unpublish_addon_pages';
    const KEEP_ACTION = 'wpjobportal_keep_addon_pages';

    /** Add-on folder => its name. */
    private static function addons() {
        return array(
            'wp-job-portal-pro' => __('WP Job Portal Pro', 'wp-job-portal'),
            'wp-job-portal-recruitsuite' => __('Recruit Suite', 'wp-job-portal'),
            'wp-job-portal-marketplacesuite' => __('Marketplace Suite', 'wp-job-portal'),
            'wp-job-portal-matchingsuite' => __('Matching Suite', 'wp-job-portal'),
        );
    }

    /** Shortcode => add-on folder. */
    private static function shortcodes() {
        return array(
            'wpjobportal_resume_search' => 'wp-job-portal-pro',
            'wpjobportal_all_resumes' => 'wp-job-portal-pro',
            'wpjobportal_candidate_portal' => 'wp-job-portal-recruitsuite',
            'wpjobportal_recruit_team' => 'wp-job-portal-recruitsuite',
            'wpjobportal_recruit_automation' => 'wp-job-portal-recruitsuite',
            'wpjobportal_recruit_workspace' => 'wp-job-portal-recruitsuite',
            'wpjobportal_recruit_pipeline' => 'wp-job-portal-recruitsuite',
            'wpjobportal_recruit_center' => 'wp-job-portal-recruitsuite',
            'wpjobportal_recruit_insights' => 'wp-job-portal-recruitsuite',
            'wpjobportal_featured_companies' => 'wp-job-portal-marketplacesuite',
            'wpjobportal_featured_resumes' => 'wp-job-portal-marketplacesuite',
            'wpjobportal_featured_jobs' => 'wp-job-portal-marketplacesuite',
            'wpjobportal_myinvoices' => 'wp-job-portal-marketplacesuite',
            'wpjobportal_allpackages' => 'wp-job-portal-marketplacesuite',
            'wpjobportal_mysubscription' => 'wp-job-portal-marketplacesuite',
            'wpjobportal_mypackages' => 'wp-job-portal-marketplacesuite',
        );
    }

    public static function register() {
        // Late, so an active add-on has registered its own first.
        add_action('init', array(__CLASS__, 'register_shortcodes'), 999);
        add_action('deactivated_plugin', array(__CLASS__, 'on_deactivated'));
        add_action('admin_notices', array(__CLASS__, 'render_pages_notice'));
        add_action('admin_post_' . self::UNPUBLISH_ACTION, array(__CLASS__, 'unpublish_pages'));
        add_action('admin_post_' . self::KEEP_ACTION, array(__CLASS__, 'keep_pages'));
    }

    public static function register_shortcodes() {
        foreach (self::shortcodes() as $tag => $addon) {
            if (!shortcode_exists($tag)) {
                add_shortcode($tag, function () use ($addon) {
                    return WPJOBPORTAL_Addon_Fallbacks::shortcode_placeholder($addon);
                });
            }
        }
    }

    /** Nothing for visitors; for administrators, which add-on the block needs. */
    public static function shortcode_placeholder($addon) {
        if (!current_user_can('manage_options')) {
            return '';
        }
        $addons = self::addons();
        $name = isset($addons[$addon]) ? $addons[$addon] : $addon;
        return '<div class="wpjobportal-addon-off" style="padding:12px 16px;border:1px dashed #94a3b8;border-radius:8px;background:#f8fafc;color:#334155;font-size:14px;">'
            /* translators: %s: add-on name */
            . esc_html(sprintf(__('This part of the page needs the %s add-on, which is switched off. Visitors see nothing here.', 'wp-job-portal'), $name))
            . ' <a href="' . esc_url(admin_url('plugins.php')) . '">' . esc_html__('Plugins', 'wp-job-portal') . '</a></div>';
    }

    /**
     * The add-on folder that holds an admin module, whether or not it is active, or ''.
     *
     * @param string $module Page slug without "wpjobportal_" (departments, coverletter, package…).
     */
    public static function addon_for_module($module) {
        $want = preg_replace('/[^a-z0-9]/', '', strtolower((string) $module));
        if ($want === '') {
            return '';
        }
        foreach (array_keys(self::addons()) as $folder) {
            foreach (array('modules', 'credits') as $sub) {
                $dir = WP_PLUGIN_DIR . '/' . $folder . '/' . $sub;
                if (!is_dir($dir)) {
                    continue;
                }
                foreach ((array) scandir($dir) as $entry) {
                    if ($entry[0] !== '.' && is_dir($dir . '/' . $entry) && preg_replace('/[^a-z0-9]/', '', strtolower($entry)) === $want) {
                        return $folder;
                    }
                }
            }
        }
        return '';
    }

    /**
     * An admin screen whose module is not there: say which add-on it needs.
     *
     * @param string $module
     * @return void
     */
    public static function render_missing_screen($module) {
        $addons = self::addons();
        $folder = self::addon_for_module($module);
        $name = $folder !== '' ? $addons[$folder] : __('an add-on', 'wp-job-portal');
        $title = ucwords(str_replace(array('-', '_'), ' ', (string) $module));
        ?>
        <div id="wpjobportaladmin-wrapper" class="wjpl-page">
            <div id="wpjobportaladmin-leftmenu">
                <?php WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
            </div>
            <div id="wpjobportaladmin-data">
                <?php WPJOBPORTAL_Admin_List_UI::page_head($title); ?>
                <div class="wjpl">
                    <section class="wjpl-card">
                        <?php
                        WPJOBPORTAL_Admin_List_UI::empty_state(
                            /* translators: %s: add-on name */
                            sprintf(__('This screen needs %s', 'wp-job-portal'), $name),
                            $folder !== ''
                                ? __('The add-on is installed but switched off. Switch it on in Plugins to use this screen; its data is kept.', 'wp-job-portal')
                                : __('The add-on it belongs to is not installed or is switched off.', 'wp-job-portal'),
                            array(__('Open Plugins', 'wp-job-portal') => admin_url('plugins.php'), __('Dashboard', 'wp-job-portal') => admin_url('admin.php?page=wpjobportal'))
                        );
                        ?>
                    </section>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * When an add-on is switched off, note its published pages for the offer below.
     *
     * @param string $plugin plugin-folder/file.php
     */
    public static function on_deactivated($plugin) {
        $folder = strtok((string) $plugin, '/');
        if (!isset(self::addons()[$folder])) {
            return;
        }
        $tags = array_keys(array_filter(self::shortcodes(), function ($f) use ($folder) { return $f === $folder; }));
        if (!$tags) {
            return;
        }
        global $wpdb;
        $like = array();
        foreach ($tags as $tag) {
            $like[] = $wpdb->prepare('post_content LIKE %s', '%[' . $wpdb->esc_like($tag) . '%');
        }
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- each LIKE clause in $like was built by $wpdb->prepare() above
        $ids = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_type = 'page' AND post_status = 'publish' AND (" . implode(' OR ', $like) . ')');
        if ($ids) {
            $pending = (array) get_option(self::PENDING_OPTION, array());
            $pending[$folder] = array_map('absint', $ids);
            update_option(self::PENDING_OPTION, $pending, false);
        }
    }

    public static function render_pages_notice() {
        if (!current_user_can('manage_options')) {
            return;
        }
        $pending = (array) get_option(self::PENDING_OPTION, array());
        $addons = self::addons();
        foreach ($pending as $folder => $ids) {
            // The add-on was switched back on: nothing to offer.
            if (!isset($addons[$folder]) || is_plugin_active($folder . '/' . $folder . '.php')) {
                continue;
            }
            $titles = array();
            foreach ((array) $ids as $id) {
                $post = get_post((int) $id);
                if ($post && $post->post_status === 'publish') {
                    $titles[] = '<a href="' . esc_url(get_permalink($post)) . '">' . esc_html(get_the_title($post)) . '</a>';
                }
            }
            if (!$titles) {
                continue;
            }
            $args = array('folder' => $folder);
            ?>
            <div class="notice notice-warning wpjobportal-addon-pages-notice">
                <p>
                    <strong><?php echo esc_html(sprintf(/* translators: %s: add-on name */ __('%s is switched off.', 'wp-job-portal'), $addons[$folder])); ?></strong>
                    <?php echo esc_html__('These published pages use it and now show nothing to visitors:', 'wp-job-portal'); ?>
                    <?php echo wp_kses(implode(', ', $titles), array('a' => array('href' => array()))); ?>.
                    <?php echo esc_html__('Set them to draft so menus and search engines stop sending people there?', 'wp-job-portal'); ?>
                </p>
                <p>
                    <a class="button button-primary" href="<?php echo esc_url(wp_nonce_url(add_query_arg($args, admin_url('admin-post.php?action=' . self::UNPUBLISH_ACTION)), self::UNPUBLISH_ACTION . $folder)); ?>"><?php echo esc_html__('Set these pages to draft', 'wp-job-portal'); ?></a>
                    <a class="button" href="<?php echo esc_url(wp_nonce_url(add_query_arg($args, admin_url('admin-post.php?action=' . self::KEEP_ACTION)), self::KEEP_ACTION . $folder)); ?>"><?php echo esc_html__('Keep them published', 'wp-job-portal'); ?></a>
                </p>
            </div>
            <?php
        }
    }

    private static function take_pending($action) {
        $folder = sanitize_key((string) WPJOBPORTALrequest::getVar('folder', 'get', ''));
        if (!current_user_can('manage_options') || !check_admin_referer($action . $folder)) {
            wp_die(esc_html__('You are not allowed to do that.', 'wp-job-portal'));
        }
        $pending = (array) get_option(self::PENDING_OPTION, array());
        $ids = isset($pending[$folder]) ? (array) $pending[$folder] : array();
        unset($pending[$folder]);
        update_option(self::PENDING_OPTION, $pending, false);
        return $ids;
    }

    public static function unpublish_pages() {
        foreach (self::take_pending(self::UNPUBLISH_ACTION) as $id) {
            $post = get_post((int) $id);
            if ($post && $post->post_type === 'page' && $post->post_status === 'publish') {
                wp_update_post(array('ID' => $post->ID, 'post_status' => 'draft'));
            }
        }
        wp_safe_redirect(wp_get_referer() ? wp_get_referer() : admin_url('admin.php?page=wpjobportal'));
        exit;
    }

    public static function keep_pages() {
        self::take_pending(self::KEEP_ACTION);
        wp_safe_redirect(wp_get_referer() ? wp_get_referer() : admin_url('admin.php?page=wpjobportal'));
        exit;
    }
}
