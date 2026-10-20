<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Tell an upgraded site what to do about its old add-ons.
 *
 * Since 2.7.0 the separate add-ons are part of Core, Pro, Recruit Suite,
 * Marketplace Suite and Matching Suite. An old add-on keeps running until the
 * product that replaces it is active, so a site that updated only Core works
 * as before for its users: 2.7.0 changed no table or column the old add-ons
 * use, and Core's absorbed features stand down while their add-on runs. Once
 * the product is active the old add-on is paused: Core leaves it out of
 * wpjobportal::$_active_addons and detaches the hooks its code registered, and
 * the product runs beside it (see legacy_owns()). Add-ons whose feature Core
 * now has are paused at once. must_deactivate() lists any add-on whose
 * replacement cannot load beside it; since Core's widgets were renamed and the
 * Elegant Design folder is read through wpjobportal_elegant_design_path(),
 * there are none.
 *
 * Administrators are moved to the products instead: an admin screen that an
 * old add-on still serves opens the Licences page, where the same licence key
 * installs the product. Only when it cannot (no key, an expired licence, a
 * product outside the licence, a server that cannot write plugins) does that
 * page offer to continue to the old screen, for a day at a time.
 *
 * Two notices say which step is missing:
 *
 *  - Replacement not installed yet: install that product from Licences.
 *  - Replacement running but the old add-on still active: deactivate the old
 *    add-on on the Plugins screen.
 *
 * Plugins are never switched off from here; that stays the administrator's
 * decision. Deactivating by hand is safe for the settings
 * (WPJOBPORTAL_Absorbed_Config_Guard restores the rows an old add-on's
 * deactivation hook switches off) and deleting afterwards is safe for the data
 * (WPJOBPORTAL_Uninstall_Guard declines every drop of a table still in use).
 *
 * @see WPJOBPORTAL_Absorbed_Config_Guard::providers() for the slug => product map.
 */
class WPJOBPORTAL_Superseded_Addons {

    /** Each product's own plugin file. */
    private static $products = array(
        'pro'         => 'wp-job-portal-pro/wp-job-portal-pro.php',
        'recruit'     => 'wp-job-portal-recruitsuite/wp-job-portal-recruitsuite.php',
        'marketplace' => 'wp-job-portal-marketplacesuite/wp-job-portal-marketplacesuite.php',
        'matching'    => 'wp-job-portal-matchingsuite/wp-job-portal-matchingsuite.php',
    );

    /** @var array|null Slugs of the old add-ons paused on this request. */
    private static $paused = null;

    /** @var array|null Slugs of the old add-ons still running on this request. */
    private static $running = null;

    /** @var array Product key => can_install_now() answer, for this request. */
    private static $installable = array();

    /**
     * Old add-ons that are active and whose product is active, and so paused.
     *
     * Read once, from the same active plugin list Core builds its add-on list
     * from, so both agree for the whole request. Core asks while it loads,
     * before any product has, so "active" means in that list, not loaded.
     *
     * @return array Add-on slugs.
     */
    public static function paused() {
        if (self::$paused === null) {
            $map = self::replacements();
            self::$paused = array();
            foreach (self::active_old_addons() as $slug) {
                if (self::product_active($map[$slug])) {
                    self::$paused[] = $slug;
                }
            }
        }
        return self::$paused;
    }

    /**
     * Old add-ons that are active and still serve their feature, because
     * the product that replaces them is not active yet.
     *
     * @return array Add-on slugs.
     */
    public static function running() {
        if (self::$running === null) {
            self::$running = array_values(array_diff(self::active_old_addons(), self::paused()));
        }
        return self::$running;
    }

    /**
     * @param string $slug Add-on slug.
     * @return bool
     */
    public static function is_paused($slug) {
        return in_array((string) $slug, self::paused(), true);
    }

    /**
     * Old add-ons whose replacement cannot load beside them, even paused.
     *
     * Empty since 2.7.0's last clashes were removed: Front-end Widgets declared
     * the same class names as Core's basic widgets (Core's are now
     * WPJOBPORTALCORE*, with the same widget ids), and Elegant Design defines
     * the path constants first (everything now asks
     * wpjobportal_elegant_design_path()/_url(), which Pro answers with its own
     * copy). Kept so a future clash has one place to go.
     *
     * @var array
     */
    private static $must_deactivate = array();

    /**
     * Old add-ons that nothing replaces: no longer supported, so their
     * Plugins line says to remove them rather than naming a product.
     *
     * @var array
     */
    private static $discontinued = array('sociallogin');

    /**
     * @param string $slug Add-on slug.
     * @return bool
     */
    public static function must_deactivate($slug) {
        return in_array((string) $slug, self::$must_deactivate, true);
    }

    /**
     * Does an old add-on still own its feature?
     *
     * Asked by the products before they take a feature over. Its class being
     * loaded is not enough: a paused add-on is loaded and does nothing, so the
     * product runs - unless it is one that cannot load beside its old add-on.
     *
     * @param string $class The old add-on's main class.
     * @return bool
     */
    public static function legacy_owns($class) {
        if (!class_exists((string) $class, false)) {
            return false;
        }
        try {
            $reflection = new ReflectionClass((string) $class);
        } catch (ReflectionException $e) {
            return true;
        }
        $file = wp_normalize_path((string) $reflection->getFileName());
        foreach (self::paused() as $slug) {
            if (strpos($file, wp_normalize_path(WP_PLUGIN_DIR . '/wp-job-portal-' . $slug . '/')) === 0) {
                return self::must_deactivate($slug);
            }
        }
        return true;
    }

    /**
     * Take back every hook a paused add-on registered.
     *
     * WordPress has loaded its code by now and nothing can unload it, but its
     * code only runs through hooks: the ones Core fires for add-ons, and a few
     * of WordPress's own (job alert cron, PDF and print output, RSS feeds,
     * widgets, Elegant's stylesheets). A callback belongs to the add-on when it
     * is defined in a file inside the add-on's folder. Its activation and
     * deactivation hooks stay, so switching it off still runs its own code and
     * WPJOBPORTAL_Absorbed_Config_Guard still protects the settings.
     *
     * @return void
     */
    public static function detach_paused() {
        $folders = array();
        foreach (self::paused() as $slug) {
            $folders[] = wp_normalize_path(WP_PLUGIN_DIR . '/wp-job-portal-' . $slug . '/');
        }
        if (empty($folders)) {
            return;
        }

        global $wp_filter;
        foreach ((array) $wp_filter as $hook => $wp_hook) {
            if (!($wp_hook instanceof WP_Hook) || strpos($hook, 'activate_') === 0 || strpos($hook, 'deactivate_') === 0) {
                continue;
            }
            foreach ($wp_hook->callbacks as $priority => $callbacks) {
                foreach ($callbacks as $callback) {
                    $file = self::callback_file($callback['function']);
                    foreach ($folders as $folder) {
                        if ($file !== '' && strpos($file, $folder) === 0) {
                            remove_filter($hook, $callback['function'], $priority);
                            break;
                        }
                    }
                }
            }
        }
    }

    /**
     * The file a hook callback is defined in.
     *
     * @param mixed $callback Function name, Class::method, array or closure.
     * @return string Normalised path, or '' when it cannot be told.
     */
    private static function callback_file($callback) {
        try {
            if (is_string($callback) && strpos($callback, '::') !== false) {
                $callback = explode('::', $callback, 2);
            }
            if (is_array($callback) && isset($callback[0], $callback[1])) {
                $reflection = new ReflectionMethod($callback[0], $callback[1]);
            } elseif ($callback instanceof Closure || (is_string($callback) && function_exists($callback))) {
                $reflection = new ReflectionFunction($callback);
            } else {
                return '';
            }
        } catch (ReflectionException $e) {
            return '';
        }
        $file = $reflection->getFileName();
        return $file ? wp_normalize_path($file) : '';
    }

    public static function register() {
        // Every plugin is loaded by plugins_loaded, and the old add-ons' own
        // plugins_loaded callbacks (their update routines) have not run yet.
        add_action('plugins_loaded', array(__CLASS__, 'detach_paused'), -1000);
        add_action('admin_notices', array(__CLASS__, 'render_notices'));
        add_action('network_admin_notices', array(__CLASS__, 'render_notices'));
        // The same advice under each old add-on's own row on the Plugins screen.
        add_action('after_plugin_row', array(__CLASS__, 'render_plugin_row'), 10, 2);
        add_action('admin_print_styles-plugins.php', array(__CLASS__, 'plugin_row_style'));
        add_filter('plugin_action_links', array(__CLASS__, 'plugin_action_links'), 10, 2);
        add_filter('network_admin_plugin_action_links', array(__CLASS__, 'plugin_action_links'), 10, 2);
        // Admin screens an old add-on still serves lead to its product.
        add_action('admin_init', array(__CLASS__, 'redirect_legacy_screen'));
        add_action('admin_print_styles', array(__CLASS__, 'menu_badge_style'));
    }

    /**
     * The old add-on that serves an admin page, if one still does.
     *
     * Asks the includer where the page's controller is, so it answers the way
     * the page itself is loaded - sub-screens such as Packages belong to
     * Credits there - and says nothing once a product serves the page.
     *
     * @param string $page The admin page slug, e.g. wpjobportal_coverletter.
     * @return string Add-on slug, or ''.
     */
    public static function legacy_screen($page) {
        $page = sanitize_key((string) $page);
        $running = self::running();
        if (strpos($page, 'wpjobportal_') !== 0 || empty($running) || !class_exists('WPJOBPORTALincluder')) {
            return '';
        }
        $path = WPJOBPORTALincluder::getPluginPath(substr($page, strlen('wpjobportal_')), 'controller');
        $path = is_string($path) ? wp_normalize_path($path) : '';
        if ($path === '') {
            return '';
        }
        foreach ($running as $slug) {
            if (strpos($path, wp_normalize_path(WP_PLUGIN_DIR . '/wp-job-portal-' . $slug . '/')) === 0) {
                return $slug;
            }
        }
        return '';
    }

    /**
     * The product that replaces an old add-on.
     *
     * @param string $slug Add-on slug.
     * @return string Product key, or ''.
     */
    public static function product_for($slug) {
        $map = self::replacements();
        return isset($map[$slug]) ? (string) $map[$slug] : '';
    }

    /**
     * @param string $product Product key.
     * @return string Product name.
     */
    public static function product_name($product) {
        return self::product_label($product);
    }

    /**
     * @param string $product Product key.
     * @return string The product's plugin folder, e.g. wp-job-portal-pro; '' for Core.
     */
    public static function product_folder($product) {
        return isset(self::$products[$product]) ? dirname(self::$products[$product]) : '';
    }

    /**
     * Is the product switched on now?
     *
     * Read afresh, unlike paused(): the Licences page installs and activates
     * a product part way through its own request, after paused() answered.
     *
     * @param string $product Product key.
     * @return bool
     */
    public static function product_switched_on($product) {
        return self::product_active($product);
    }

    /**
     * Can this administrator get the product now, from the Licences page?
     *
     * True when it is installed and only needs switching on, or when the
     * licence is active, covers it, and this server can install it. Reads the
     * licence as last checked; the Licences page checks again as it opens.
     *
     * @param string $product Product key.
     * @return bool
     */
    public static function can_install_now($product) {
        if (!isset(self::$installable[$product])) {
            self::$installable[$product] = self::check_installable($product);
        }
        return self::$installable[$product];
    }

    /**
     * @see can_install_now()
     * @param string $product Product key.
     * @return bool
     */
    private static function check_installable($product) {
        if (!isset(self::$products[$product]) || !current_user_can('activate_plugins')) {
            return false;
        }
        $folder = dirname(self::$products[$product]);
        if (WPJOBPORTAL_Addon_Installer::installed_file($folder) !== '') {
            return true;
        }
        if (!current_user_can('install_plugins') || !WPJOBPORTAL_Addon_Installer::can_write_plugins()) {
            return false;
        }
        $state = WPJOBPORTAL_Licence_Client::state();
        return !empty($state['ok'])
            && isset($state['status']) && $state['status'] === 'active'
            && in_array($folder, isset($state['plugins']) ? (array) $state['plugins'] : array(), true);
    }

    /**
     * Where an old add-on's admin screen sends the administrator.
     *
     * @param string $page The admin page slug.
     * @return string
     */
    public static function update_url($page) {
        return admin_url('admin.php?page=wpjobportal&wpjobportallt=licences&wpjobportal_legacy=' . rawurlencode(sanitize_key((string) $page)));
    }

    /**
     * The link that opens the old screen anyway, offered only when the
     * product cannot be had now.
     *
     * @param string $page The admin page slug.
     * @return string
     */
    public static function continue_url($page) {
        return wp_nonce_url(admin_url('admin.php?page=' . rawurlencode(sanitize_key((string) $page)) . '&wpjobportal_legacy_continue=1'), 'wpjobportal_legacy_continue');
    }

    /**
     * @return string Per-user transient name for "continue to old screens".
     */
    private static function continue_key() {
        return 'wpjobportal_legacy_continue_' . get_current_user_id();
    }

    /**
     * Send an old add-on's admin screen to the Licences page.
     *
     * Only screen views: a request that carries a task or an action is a
     * form or a link doing something, and the old add-on must still be able
     * to answer it - users keep using the feature until the product is in.
     *
     * @return void
     */
    public static function redirect_legacy_screen() {
        if (wp_doing_ajax() || !WPJOBPORTALrequest::has('page', 'get') || (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] !== 'GET')) {
            return;
        }
        $page = sanitize_key((string) WPJOBPORTALrequest::getVar('page', 'get', ''));
        $slug = self::legacy_screen($page);
        if ($slug === '') {
            return;
        }

        if (WPJOBPORTALrequest::has('wpjobportal_legacy_continue', 'get')) {
            $nonce = sanitize_text_field((string) WPJOBPORTALrequest::getVar('_wpnonce', 'get', ''));
            if (wp_verify_nonce($nonce, 'wpjobportal_legacy_continue') && !self::can_install_now(self::product_for($slug))) {
                set_transient(self::continue_key(), 1, DAY_IN_SECONDS);
            }
            wp_safe_redirect(admin_url('admin.php?page=' . rawurlencode($page)));
            exit;
        }

        if (WPJOBPORTALrequest::has('task', 'get') || WPJOBPORTALrequest::has('action', 'get') || get_transient(self::continue_key())) {
            return;
        }
        wp_safe_redirect(self::update_url($page));
        exit;
    }

    /**
     * An "Update" mark on the left-menu entries of old add-on screens.
     *
     * Some of those entries are printed by the old add-ons themselves, so the
     * mark is drawn from their links rather than added to each one. The menu
     * has three looks, and each gets its own: a dot on the icon in the icon
     * bar, a pill after the first link of its hover flyout, and a pill after
     * the label when the menu is expanded.
     *
     * @return void
     */
    public static function menu_badge_style() {
        $current = sanitize_key((string) WPJOBPORTALrequest::getVar('page', 'get', ''));
        if (strpos($current, 'wpjobportal') !== 0 || empty(self::running())) {
            return;
        }
        global $submenu;
        $menu = '#wpjobportaladmin-leftmenu';
        $bar = '#wpjobportaladmin-wrapper:not(.menu-collasped-active) ' . $menu;
        $expanded = '#wpjobportaladmin-wrapper.menu-collasped-active ' . $menu;
        $dots = array();
        $pills = array();
        foreach (array('wpjobportal', 'wpjobportal_hide') as $parent) {
            if (empty($submenu[$parent])) {
                continue;
            }
            foreach ((array) $submenu[$parent] as $item) {
                $page = isset($item[2]) ? sanitize_key($item[2]) : '';
                if ($page === '' || self::legacy_screen($page) === '') {
                    continue;
                }
                foreach (array('[href$="page=' . $page . '"]', '[href*="page=' . $page . '&"]') as $href) {
                    foreach (array('li.treeview:has(> ul a' . $href . ')', 'li.treeview:has(> a' . $href . ')') as $entry) {
                        $dots[] = $bar . ' ' . $entry . ' > a::after';
                        $pills[] = $expanded . ' ' . $entry . ' > a > .wpjobportaladmin-text::after';
                    }
                    // One pill per flyout, on its first link: the whole group is the old add-on's.
                    $pills[] = $bar . ' li.treeview > ul > li:first-child > a' . $href . '::after';
                }
            }
        }
        if (empty($pills)) {
            return;
        }
        // A CSS string inside <style>: no escaping applies there, so the
        // characters that could end it are dropped.
        $label = str_replace(array('\\', '"', '<', '>', "\r", "\n"), '', __('Update', 'wp-job-portal'));
        // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS: selectors are built from fixed strings and admin page slugs, the label has its string-ending characters removed above; HTML escaping would break the child combinators.
        echo '<style id="wpjobportal-legacy-badge">'
            . implode(',', array_unique($dots))
            . '{content:"";position:absolute;top:6px;inset-inline-start:36px;width:8px;height:8px;border-radius:50%;background:#d63638;box-shadow:0 0 0 2px #fff;pointer-events:none}'
            . implode(',', array_unique($pills))
            . '{content:"' . $label . '";display:inline-block;margin-inline-start:6px;padding:0 6px;border-radius:9px;'
            . 'background:#d63638;color:#fff;font-size:10px;line-height:16px;font-weight:600;vertical-align:middle}'
            . '</style>' . "\n";
        // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * The product that replaces an old add-on's plugin file.
     *
     * @param string $plugin_file Plugin file, relative to the plugins folder.
     * @return string Product key, or '' when it is not a replaced add-on.
     */
    private static function replacement_for($plugin_file) {
        if (strpos((string) $plugin_file, 'wp-job-portal-') !== 0) {
            return '';
        }
        $slug = self::slug_of($plugin_file);
        $map = self::replacements();
        return isset($map[$slug]) ? $map[$slug] : '';
    }

    /**
     * What a disabled menu entry for an old add-on should offer.
     *
     * Read by wpjobportal_checkWPJPPluginInfo(). Activating the old add-on is
     * never the answer any more: the step is installing its product, or, once
     * that runs, deactivating the paused add-on.
     *
     * @param string $plugin_file Plugin file, relative to the plugins folder.
     * @return array|null text, availability and url; null when it is not an old add-on.
     */
    public static function plugin_info($plugin_file) {
        $product = self::replacement_for($plugin_file);
        if ($product === '') {
            return null;
        }
        if (!self::product_running($product)) {
            return array(
                'text' => esc_html(__('Install Now', 'wp-job-portal')),
                'availability' => '0',
                'url' => admin_url('admin.php?page=wpjobportal&wpjobportallt=licences'),
            );
        }
        // The product runs beside a paused add-on, so the entry works as usual -
        // except for the add-ons it cannot load beside.
        $wpjobportal_slug = self::slug_of($plugin_file);
        if (self::is_paused($wpjobportal_slug) && self::must_deactivate($wpjobportal_slug)) {
            return array(
                'text' => esc_html(__('Deactivate old add-on', 'wp-job-portal')),
                'availability' => '1',
                'url' => admin_url('plugins.php?plugin_status=active&s=' . rawurlencode(strtok((string) $plugin_file, '/'))),
            );
        }
        return null;
    }

    /**
     * @param string $plugin_file Plugin file, relative to the plugins folder.
     * @return string Add-on slug, e.g. coverletter.
     */
    private static function slug_of($plugin_file) {
        return substr((string) strtok(str_replace('\\', '/', (string) $plugin_file), '/'), strlen('wp-job-portal-'));
    }

    /**
     * No "Activate" link on an old add-on, while its product is on or can be
     * had now.
     *
     * Switching an old add-on back on would run its code again instead of
     * moving the site to the product that replaces it. When the product
     * cannot be had now (an expired licence, say), an add-on switched off by
     * mistake can be switched on again, so its users get the feature back.
     *
     * @param array  $actions     Row action links.
     * @param string $plugin_file Plugin file, relative to the plugins folder.
     * @return array
     */
    public static function plugin_action_links($actions, $plugin_file) {
        $product = isset($actions['activate']) ? self::replacement_for($plugin_file) : '';
        if ($product !== '' && (self::product_active($product) || self::can_install_now($product))) {
            unset($actions['activate']);
        }
        return $actions;
    }

    /**
     * WordPress drops a plugin row's bottom border only when an update line
     * follows it; do the same when our line follows it.
     *
     * @return void
     */
    public static function plugin_row_style() {
        echo '<style>'
            . '#the-list tr:has(+ tr.wpjobportal-superseded-tr) > *{box-shadow:none}'
            . '#the-list tr.active + tr.inactive:has(+ tr.wpjobportal-superseded-tr) > *{box-shadow:inset 0 1px 0 rgba(0,0,0,.02)}'
            . '</style>' . "\n";
    }

    /**
     * A line under an old add-on's row on the Plugins screen, in the style
     * WordPress uses for "There is a new version available".
     *
     * @param string $plugin_file Plugin file, relative to the plugins folder.
     * @param array  $plugin_data Plugin header data.
     * @return void
     */
    public static function render_plugin_row($plugin_file, $plugin_data) {
        if (!current_user_can('activate_plugins')) {
            return;
        }
        $replacement = self::replacement_for($plugin_file);
        if ($replacement === '') {
            return;
        }

        $product = self::product_label($replacement);
        $active = is_plugin_active($plugin_file) || (is_multisite() && is_plugin_active_for_network($plugin_file));

        $running = self::product_running($replacement);

        if (in_array(self::slug_of($plugin_file), self::$discontinued, true)) {
            $type = 'notice-warning';
            $message = esc_html__('No longer supported. This add-on no longer works; you can deactivate and delete it.', 'wp-job-portal');
        } elseif (!$active && $running) {
            $type = 'notice-info';
            /* translators: %s: product name. */
            $message = esc_html(sprintf(__('Now included in %s. You can delete this add-on; your data is kept.', 'wp-job-portal'), $product));
        } elseif ($running && self::must_deactivate(self::slug_of($plugin_file))) {
            $type = 'notice-warning';
            /* translators: %s: product name. */
            $message = esc_html(sprintf(__('Paused. Now included in %s: deactivate this add-on to turn the feature back on. Your data and settings are kept.', 'wp-job-portal'), $product));
        } elseif ($running) {
            $type = 'notice-info';
            /* translators: %s: product name. */
            $message = esc_html(sprintf(__('No longer needed: %s provides this feature now. You can deactivate and delete this add-on; your data and settings are kept.', 'wp-job-portal'), $product));
        } else {
            $type = 'notice-warning';
            if (!$active) {
                /* translators: 1: product name, 2: opening link tag, 3: closing link tag. */
                $format = esc_html__('Replaced by %1$s. Install it from %2$sLicences%3$s.', 'wp-job-portal');
            } elseif (self::is_paused(self::slug_of($plugin_file))) {
                // The product is switched on but not running (its Core
                // requirement is not met), so the add-on is paused regardless.
                /* translators: 1: product name, 2: opening link tag, 3: closing link tag. */
                $format = esc_html__('Paused. Replaced by %1$s: install it from %2$sLicences%3$s, then deactivate this add-on.', 'wp-job-portal');
            } else {
                /* translators: 1: product name, 2: opening link tag, 3: closing link tag. */
                $format = esc_html__('Still running for now. Replaced by %1$s: install it from %2$sLicences%3$s; this add-on then stops and can be deactivated.', 'wp-job-portal');
            }
            $message = sprintf(
                $format,
                esc_html($product),
                '<a href="' . esc_url(admin_url('admin.php?page=wpjobportal&wpjobportallt=licences')) . '">',
                '</a>'
            );
        }

        $columns = 3;
        $table = function_exists('_get_list_table') ? _get_list_table('WP_Plugins_List_Table') : null;
        if ($table) {
            $columns = $table->get_column_count();
        }
        ?>
        <tr class="plugin-update-tr wpjobportal-superseded-tr <?php echo $active ? 'active' : 'inactive'; ?>">
            <td colspan="<?php echo esc_attr($columns); ?>" class="plugin-update colspanchange">
                <div class="update-message notice inline <?php echo esc_attr($type); ?> notice-alt">
                    <p><?php echo wp_kses($message, array('a' => array('href' => array()))); ?></p>
                </div>
            </td>
        </tr>
        <?php
    }

    /**
     * Which product replaces each old add-on.
     *
     * The settings guard's map, with the entries it has no settings for.
     * Print, widgets and job alerts point at Pro here: Core has the feature,
     * but at the free tier's limits, so without Pro the paid add-on still has
     * something to give and should not be advertised as replaced.
     *
     * @return array slug => core|pro|recruit|marketplace|matching.
     */
    public static function replacements() {
        $map = WPJOBPORTAL_Absorbed_Config_Guard::providers();
        foreach (array('print', 'widgets', 'jobalert', 'elegantdesign', 'addressdata') as $slug) {
            $map[$slug] = 'pro';
        }
        // The Color Manager's screen has been Core's own Colors page since 2.7.0.
        $map['theme'] = 'core';
        // Custom fields have been part of Core for several releases; the old
        // add-on adds nothing. Social Login is discontinued (see $discontinued).
        $map['customfield'] = 'core';
        $map['sociallogin'] = 'core';

        /**
         * Adjust which product replaces each old add-on.
         *
         * @param array $map slug => core|pro|recruit|marketplace|matching.
         */
        return (array) apply_filters('wpjobportal_superseded_addons', $map);
    }

    /**
     * Is the replacing product installed, active and running?
     *
     * @param string $product Product key.
     * @return bool
     */
    private static function product_running($product) {
        if ($product === 'core') {
            return true;
        }
        if (!isset(self::$products[$product]) || !is_plugin_active(self::$products[$product])) {
            return false;
        }
        // Active but dormant (its Core requirement not met) does not count.
        return WPJOBPORTAL_Absorbed_Config_Guard::provider_present($product);
    }

    /**
     * Is the replacing product switched on?
     *
     * The answer paused() needs while Core loads, before any product has, so
     * it reads the active plugin list rather than what the product defines.
     * product_running() is the later, stricter question.
     *
     * @param string $product Product key.
     * @return bool
     */
    private static function product_active($product) {
        if ($product === 'core') {
            return true;
        }
        if (!isset(self::$products[$product])) {
            return false;
        }
        return in_array(self::$products[$product], self::active_plugin_files(), true)
            && file_exists(WP_PLUGIN_DIR . '/' . self::$products[$product]);
    }

    /**
     * Active plugin files, this site's and the network's.
     *
     * @return array
     */
    private static function active_plugin_files() {
        $active = (array) get_option('active_plugins', array());
        if (is_multisite()) {
            $active = array_merge($active, array_keys((array) get_site_option('active_sitewide_plugins', array())));
        }
        return $active;
    }

    /**
     * Slugs of the old add-ons that are active.
     *
     * @return array
     */
    private static function active_old_addons() {
        $active = self::active_plugin_files();

        $map = self::replacements();
        $found = array();
        foreach ($active as $plugin) {
            $folder = strtok(str_replace('\\', '/', (string) $plugin), '/');
            if (strpos($folder, 'wp-job-portal-') !== 0) {
                continue;
            }
            $slug = substr($folder, strlen('wp-job-portal-'));
            if (isset($map[$slug])) {
                $found[] = $slug;
            }
        }
        return array_unique($found);
    }

    /**
     * Product names for the notices.
     *
     * @param string $product Product key.
     * @return string
     */
    private static function product_label($product) {
        $labels = array(
            'core'        => __('WP Job Portal', 'wp-job-portal'),
            'pro'         => __('WP Job Portal Pro', 'wp-job-portal'),
            'recruit'     => __('Recruit Suite', 'wp-job-portal'),
            'marketplace' => __('Marketplace Suite', 'wp-job-portal'),
            'matching'    => __('Matching Suite', 'wp-job-portal'),
        );
        return isset($labels[$product]) ? $labels[$product] : $product;
    }

    /**
     * One short banner while any old add-on is paused: install the missing
     * products, or deactivate the old add-ons once they are installed.
     *
     * @return void
     */
    public static function render_notices() {
        if (!current_user_can('activate_plugins') || !self::guide_screen()) {
            return;
        }

        $map = self::replacements();
        $waiting = array();
        $replaced = array();
        foreach (self::active_old_addons() as $slug) {
            if (self::is_paused($slug) && self::product_running($map[$slug])) {
                $replaced[$map[$slug]][] = $slug;
            } else {
                $waiting[$map[$slug]][] = $slug;
            }
        }

        // One banner at a time: installing comes first, and its text already
        // says to deactivate afterwards. No product is named: a licence may
        // not cover every product, and the Licences screen lists the ones it
        // does. A site with no old add-on active (a free site) never gets here.
        if (!empty($waiting)) {
            $text = __('Your site still runs old add-ons from before version 2.7.0. Install the new products included in your licence from the Licences page; the old add-ons then stop on their own and can be deactivated. Your data and settings are kept.', 'wp-job-portal');
            $link = admin_url('admin.php?page=wpjobportal&wpjobportallt=licences');
            $label = __('Go to Licences', 'wp-job-portal');
        } elseif (!empty($replaced)) {
            $text = __('Your old add-ons are no longer needed: the new products provide their features now. Deactivate and delete them when you are ready. Your data and settings are kept.', 'wp-job-portal');
            $link = admin_url('plugins.php?plugin_status=active');
            $label = __('Go to Plugins', 'wp-job-portal');
        } else {
            return;
        }
        if (WPJOBPORTAL_Monetisation::paused()) {
            $text .= ' ' . __('Until this is done, new jobs, companies and resumes cannot be added.', 'wp-job-portal');
        }
        ?>
        <div class="notice notice-warning">
            <p>
                <strong><?php echo esc_html__('WP Job Portal:', 'wp-job-portal'); ?></strong>
                <?php echo esc_html($text); ?>
                <a href="<?php echo esc_url($link); ?>"><?php echo esc_html($label); ?></a>
            </p>
        </div>
        <?php
    }

    /**
     * The notices show on the Dashboard, Plugins, Updates and WP Job Portal's
     * own screens - where an owner looks after an update - not on every page.
     *
     * @return bool
     */
    private static function guide_screen() {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if ($screen && in_array($screen->base, array('dashboard', 'plugins', 'update-core', 'dashboard-network', 'plugins-network'), true)) {
            return true;
        }
        $page = sanitize_key((string) WPJOBPORTALrequest::getVar('page', 'get', ''));
        // Sent to Licences from an old add-on's screen: that page explains
        // the same thing for the screen in hand.
        if ($page === 'wpjobportal' && !empty(WPJOBPORTALrequest::getVar('wpjobportal_legacy', 'get'))) {
            return false;
        }
        return strpos($page, 'wpjobportal') === 0;
    }
}
