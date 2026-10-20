<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Translations, delivered from wpjobportal.com.
 *
 * The site's own language - and every language WordPress has installed for
 * its users - is installed the way WordPress installs its own: into
 * wp-content/languages/plugins/, which the text domain loader reads first and
 * a plugin update cannot delete. One file per language covers Core and every
 * product: they share the wp-job-portal text domain.
 *
 * Nobody has to click anything. It runs when WP Job Portal is installed or
 * updated, when the site language changes, when WordPress adds a language,
 * and once a day for improved translations. The Translations screen shows
 * what is installed and installs any other language on request.
 *
 * What it downloads is data: a .mo (what WordPress reads) and the .po it was
 * made from (what a person edits). Never .l10n.php - that is PHP, and a
 * plugin may not fetch code from its own server. Every package is checked
 * against the SHA-256 the list gives for it before anything is written.
 *
 * Nothing about the site is sent: a plain GET for a static list and a static
 * file, with a user agent that does not name the site.
 *
 * The languages bundled in the plugin's own languages/ folder still work
 * underneath: the loader falls back to them, and this screen says so.
 */
class WPJOBPORTAL_Translations {

    const DOMAIN = 'wp-job-portal';
    const SOURCE = 'https://wpjobportal.com/translations/v1/index.json';
    const OPT_STATE = 'wpjobportal_translations';
    const T_MANIFEST = 'wpjobportal_translations_manifest';
    const T_LOCK = 'wpjobportal_translations_lock';
    const CRON = 'wpjobportal_translations_daily';
    const RETRY_AFTER = 21600; // six hours between automatic retries of a failed language
    const MAX_FILE = 15728640; // 15 MB; a complete translation is far smaller

    public static function register() {
        add_action(self::CRON, array(__CLASS__, 'cron'));
        add_action('admin_init', array(__CLASS__, 'maybe_sync'));
        // The site language changed, or WordPress just installed something -
        // possibly a new language, possibly a new version of this plugin.
        // Forgetting the signature makes the next admin page check again.
        add_action('update_option_WPLANG', array(__CLASS__, 'forget'));
        add_action('add_option_WPLANG', array(__CLASS__, 'forget'));
        add_action('upgrader_process_complete', array(__CLASS__, 'forget'), 30);
        add_action('admin_notices', array(__CLASS__, 'notice'));
        add_action('admin_post_wpjobportal_translations', array(__CLASS__, 'handle_post'));
    }

    /**
     * Whether to install translations without being asked. On unless a site
     * says otherwise: define('WPJOBPORTAL_TRANSLATIONS_AUTO', false) in
     * wp-config.php, or the wpjobportal_translations_auto filter. The
     * Translations screen still works either way.
     */
    public static function automatic() {
        $on = !defined('WPJOBPORTAL_TRANSLATIONS_AUTO') || WPJOBPORTAL_TRANSLATIONS_AUTO;
        return (bool) apply_filters('wpjobportal_translations_auto', $on);
    }

    /** Whether this site lets plugins write language files at all. */
    public static function can_write() {
        return !function_exists('wp_is_file_mod_allowed') || wp_is_file_mod_allowed('download_language_pack');
    }

    /** Where the list of translations lives. A constant for testing against another server. */
    public static function source_url() {
        $url = defined('WPJOBPORTAL_TRANSLATIONS_URL') ? WPJOBPORTAL_TRANSLATIONS_URL : self::SOURCE;
        return (string) apply_filters('wpjobportal_translations_source', $url);
    }

    /* ------------------------------------------------------------------
     * State
     * ---------------------------------------------------------------- */

    public static function state() {
        $state = get_option(self::OPT_STATE, array());
        return wp_parse_args(is_array($state) ? $state : array(), array(
            'sig' => '',
            'checked' => 0,
            'installed' => array(), // locale => array(version, from, at)
            'failed' => array(), // locale => array(at, error)
        ));
    }

    private static function save($state) {
        update_option(self::OPT_STATE, $state, true);
    }

    /** Make the next admin page check again. Hooked; takes whatever the hook passes. */
    public static function forget() {
        $state = self::state();
        $state['sig'] = '';
        self::save($state);
    }

    /**
     * The languages this site needs: its own, and every language WordPress has
     * installed, because an admin can choose any of those for their profile.
     * English needs nothing.
     */
    public static function wanted_locales() {
        $locales = array(get_locale());
        if (function_exists('get_available_languages')) {
            $locales = array_merge($locales, get_available_languages());
        }
        $locales = array_values(array_unique(array_filter($locales, function ($locale) {
            return is_string($locale) && $locale !== '' && $locale !== 'en_US' && self::valid_locale($locale);
        })));
        return array_slice($locales, 0, 20);
    }

    private static function valid_locale($locale) {
        return (bool) preg_match('/^[a-z]{2,3}(_[A-Z]{2})?(_[a-z0-9]+)?$/', (string) $locale);
    }

    private static function signature() {
        return md5(implode(',', self::wanted_locales()) . '|' . wpjobportal::$_currentversion . '|' . self::source_url());
    }

    /* ------------------------------------------------------------------
     * When to look
     * ---------------------------------------------------------------- */

    /**
     * On an admin page: check when something changed since the last look, or
     * when a failed language is due another try. Usually nothing to do - one
     * option read.
     */
    public static function maybe_sync() {
        if (wp_doing_ajax() || !current_user_can('install_languages')) {
            return;
        }
        if (!wp_next_scheduled(self::CRON)) {
            wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', self::CRON);
        }
        $state = self::state();
        $due = $state['sig'] !== self::signature();
        foreach ($state['failed'] as $failed) {
            if ((int) $failed['at'] < time() - self::RETRY_AFTER) {
                $due = true;
            }
        }
        if ($due) {
            self::sync(false);
        }
    }

    public static function cron() {
        self::sync(false, true);
    }

    /** Stop the daily check. Called when WP Job Portal is deactivated. */
    public static function deactivate() {
        wp_clear_scheduled_hook(self::CRON);
    }

    /**
     * Bring every language this site needs up to date.
     *
     * @param bool $force   Asked for by a person: ask the server again, retry failures now.
     * @param bool $refresh Ask the server again even if the list is cached.
     * @return array The new state.
     */
    public static function sync($force = false, $refresh = false) {
        $state = self::state();
        $state['sig'] = self::signature();
        if ((!$force && !self::automatic()) || !self::can_write()) {
            self::save($state);
            return $state;
        }
        $wanted = self::wanted_locales();
        if (empty($wanted)) {
            $state['failed'] = array();
            self::save($state);
            return $state;
        }
        if (!$force && get_transient(self::T_LOCK)) {
            return $state;
        }
        set_transient(self::T_LOCK, 1, 120);

        $manifest = self::manifest($force || $refresh);
        $state['checked'] = time();
        foreach ($wanted as $locale) {
            if (is_wp_error($manifest)) {
                $state['failed'][$locale] = array('at' => time(), 'error' => $manifest->get_error_message());
                continue;
            }
            $entry = self::entry_for($locale, $manifest);
            if (!$entry || self::is_current($locale, $entry, $state)) {
                unset($state['failed'][$locale]);
                continue;
            }
            if (!$force && isset($state['failed'][$locale])
                && (int) $state['failed'][$locale]['at'] > time() - self::RETRY_AFTER) {
                continue;
            }
            $done = self::install($locale, $entry, $state);
            if (is_wp_error($done)) {
                $state['failed'][$locale] = array('at' => time(), 'error' => $done->get_error_message());
            } else {
                unset($state['failed'][$locale]);
            }
        }
        // A language the site no longer uses is no longer a failure worth showing.
        $state['failed'] = array_intersect_key($state['failed'], array_flip($wanted));
        self::save($state);
        delete_transient(self::T_LOCK);
        return $state;
    }

    /* ------------------------------------------------------------------
     * The list on wpjobportal.com
     * ---------------------------------------------------------------- */

    /**
     * The list of translations, cached for twelve hours.
     *
     * @param bool $refresh Ask the server even when a cached list exists.
     * @return array|WP_Error locale => entry
     */
    public static function manifest($refresh = false) {
        if (!$refresh) {
            $cached = get_site_transient(self::T_MANIFEST);
            if (is_array($cached)) {
                return $cached;
            }
        }
        $url = self::source_url();
        $response = wp_remote_get($url, array(
            'timeout' => 15,
            'user-agent' => 'WP Job Portal/' . wpjobportal::$_currentversion,
        ));
        if (is_wp_error($response)) {
            return new WP_Error('wpjobportal_tr_unreachable', sprintf(
                /* translators: %s: the reason the connection failed */
                __('wpjobportal.com could not be reached (%s).', 'wp-job-portal'),
                $response->get_error_message()
            ));
        }
        $code = (int) wp_remote_retrieve_response_code($response);
        $data = json_decode((string) wp_remote_retrieve_body($response), true);
        if ($code !== 200 || !is_array($data) || !isset($data['translations']) || !is_array($data['translations'])) {
            return new WP_Error('wpjobportal_tr_badlist', sprintf(
                /* translators: %d: an HTTP status code */
                __('The list of translations could not be read (HTTP %d).', 'wp-job-portal'),
                $code
            ));
        }
        $out = array();
        foreach ($data['translations'] as $entry) {
            $entry = self::clean_entry($entry, $url);
            if ($entry) {
                $out[$entry['locale']] = $entry;
            }
        }
        set_site_transient(self::T_MANIFEST, $out, 12 * HOUR_IN_SECONDS);
        return $out;
    }

    /** One entry of the list, checked; links resolved against the list's own address. */
    private static function clean_entry($entry, $base) {
        if (!is_array($entry) || empty($entry['locale']) || !self::valid_locale($entry['locale'])
            || empty($entry['version']) || empty($entry['package'])
            || empty($entry['sha256']) || !preg_match('/^[a-f0-9]{64}$/', (string) $entry['sha256'])) {
            return null;
        }
        $clean = array(
            'locale' => (string) $entry['locale'],
            'name' => isset($entry['name']) ? sanitize_text_field($entry['name']) : (string) $entry['locale'],
            'native' => isset($entry['native']) ? sanitize_text_field($entry['native']) : '',
            'version' => sanitize_text_field($entry['version']),
            'percent' => isset($entry['percent']) ? max(0, min(100, (int) $entry['percent'])) : 0,
            'sha256' => (string) $entry['sha256'],
        );
        foreach (array('package', 'po', 'mo') as $key) {
            $clean[$key] = isset($entry[$key]) ? self::resolve_url((string) $entry[$key], $base) : '';
        }
        return $clean['package'] === '' ? null : $clean;
    }

    private static function resolve_url($url, $base) {
        if ($url === '') {
            return '';
        }
        if (!preg_match('#^https?://#i', $url)) {
            $url = trailingslashit(dirname($base)) . ltrim($url, '/');
        }
        // https only, unless the list itself was fetched over http (a local test server).
        $scheme = strtolower((string) wp_parse_url($url, PHP_URL_SCHEME));
        $base_scheme = strtolower((string) wp_parse_url($base, PHP_URL_SCHEME));
        if ($scheme !== 'https' && !($scheme === 'http' && $base_scheme === 'http')) {
            return '';
        }
        return esc_url_raw($url);
    }

    /**
     * The translation for a locale: its own, or the nearest one. Swiss German
     * gets German, Mexican Spanish gets Spanish, Portugal gets Brazilian
     * Portuguese - closer than English. Not Chinese: Traditional readers are
     * not served by Simplified.
     */
    public static function entry_for($locale, $manifest) {
        if (!is_array($manifest)) {
            return null;
        }
        if (isset($manifest[$locale])) {
            return $manifest[$locale];
        }
        $lang = strtok($locale, '_');
        if ($lang === 'zh') {
            return null;
        }
        foreach ($manifest as $entry) {
            if (strtok($entry['locale'], '_') === $lang) {
                return $entry;
            }
        }
        return null;
    }

    private static function is_current($locale, $entry, $state) {
        $have = isset($state['installed'][$locale]) ? $state['installed'][$locale] : null;
        return $have
            && $have['version'] === $entry['version']
            && $have['from'] === $entry['locale']
            && file_exists(self::file($locale, 'mo'));
    }

    public static function file($locale, $ext) {
        return WP_LANG_DIR . '/plugins/' . self::DOMAIN . '-' . $locale . '.' . $ext;
    }

    /**
     * The .mo shipped inside the plugin for a locale, if any - found the way
     * the text domain loader finds it: the locale, then the language alone.
     *
     * @param string $locale Locale.
     * @return string Path, or '' when the plugin carries none.
     */
    public static function bundled_file($locale) {
        $candidates = array_unique(array_filter(array(
            $locale,
            strtolower((string) $locale),
            strpos((string) $locale, '_') !== false ? substr((string) $locale, 0, 2) : '',
        )));
        foreach ($candidates as $candidate) {
            $path = trailingslashit(WPJOBPORTAL_PLUGIN_PATH) . 'languages/' . self::DOMAIN . '-' . $candidate . '.mo';
            if (file_exists($path)) {
                return $path;
            }
        }
        return '';
    }

    /* ------------------------------------------------------------------
     * Installing
     * ---------------------------------------------------------------- */

    /**
     * Download, check and install one language.
     *
     * @param string $locale The locale it is installed as.
     * @param array  $entry  The list's entry (maybe a nearby locale's).
     * @param array  $state  Updated in place.
     * @return true|WP_Error
     */
    public static function install($locale, $entry, &$state) {
        if (!self::can_write()) {
            return new WP_Error('wpjobportal_tr_nowrite', __('This site does not allow plugins to install language files (DISALLOW_FILE_MODS).', 'wp-job-portal'));
        }
        require_once ABSPATH . 'wp-admin/includes/file.php';
        $tmp = download_url($entry['package'], 60);
        if (is_wp_error($tmp)) {
            return new WP_Error('wpjobportal_tr_download', sprintf(
                /* translators: %s: the reason the download failed */
                __('The download failed (%s).', 'wp-job-portal'),
                $tmp->get_error_message()
            ));
        }
        $hash = hash_file('sha256', $tmp);
        if (!hash_equals($entry['sha256'], (string) $hash)) {
            wp_delete_file($tmp);
            return new WP_Error('wpjobportal_tr_checksum', __('The downloaded file did not match its checksum, so it was not installed.', 'wp-job-portal'));
        }
        $base = self::DOMAIN . '-' . $entry['locale'];
        $files = self::read_zip($tmp, array($base . '.mo', $base . '.po'));
        wp_delete_file($tmp);
        if (is_wp_error($files)) {
            return $files;
        }
        $mo = $files[$base . '.mo'];
        $magic = substr($mo, 0, 4);
        if ($magic !== "\xde\x12\x04\x95" && $magic !== "\x95\x04\x12\xde") {
            return new WP_Error('wpjobportal_tr_badfile', __('The downloaded translation is not a valid .mo file.', 'wp-job-portal'));
        }

        // The plugin's own filesystem helper: no credentials form, no fatal.
        $fs = wpjobportal::wpjobportal_filesystem();
        if (!$fs) {
            return new WP_Error('wpjobportal_tr_fs', __('WordPress could not open its files for writing.', 'wp-job-portal'));
        }
        $dir = WP_LANG_DIR . '/plugins';
        if (!$fs->is_dir($dir) && !wp_mkdir_p($dir)) {
            return new WP_Error('wpjobportal_tr_dir', __('The folder wp-content/languages/plugins could not be created.', 'wp-job-portal'));
        }
        // A file this did not put there is someone's: an older download, or
        // wording an admin changed by hand. Kept beside it rather than lost.
        if (!isset($state['installed'][$locale])) {
            foreach (array('po', 'mo') as $ext) {
                if (file_exists(self::file($locale, $ext))) {
                    $fs->move(self::file($locale, $ext), self::file($locale, $ext) . '.bak-' . gmdate('Ymd'), true);
                }
            }
        }
        $po = isset($files[$base . '.po']) ? $files[$base . '.po'] : '';
        if ($po !== '' && !$fs->put_contents(self::file($locale, 'po'), $po, FS_CHMOD_FILE)) {
            return new WP_Error('wpjobportal_tr_write', __('The folder wp-content/languages/plugins is not writable.', 'wp-job-portal'));
        }
        if (!$fs->put_contents(self::file($locale, 'mo'), $mo, FS_CHMOD_FILE)) {
            return new WP_Error('wpjobportal_tr_write', __('The folder wp-content/languages/plugins is not writable.', 'wp-job-portal'));
        }
        // WordPress prefers a .l10n.php to a .mo of the same name, so an older
        // one would hide what was just installed.
        if (file_exists(self::file($locale, 'l10n.php'))) {
            $fs->delete(self::file($locale, 'l10n.php'));
        }
        // The same cache WordPress clears after installing a language pack.
        wp_cache_delete(md5(WP_LANG_DIR . '/plugins/'), 'translation_files');

        $state['installed'][$locale] = array(
            'version' => $entry['version'],
            'from' => $entry['locale'],
            'at' => time(),
        );
        return true;
    }

    /**
     * The named files from a zip, as strings.
     *
     * @return array|WP_Error name => contents; the .mo is required.
     */
    private static function read_zip($zip, $names) {
        $out = array();
        if (class_exists('ZipArchive')) {
            $archive = new ZipArchive();
            if ($archive->open($zip) !== true) {
                return new WP_Error('wpjobportal_tr_zip', __('The downloaded translation could not be opened.', 'wp-job-portal'));
            }
            foreach ($names as $name) {
                $stat = $archive->statName($name);
                if ($stat && $stat['size'] <= self::MAX_FILE) {
                    $out[$name] = (string) $archive->getFromName($name);
                }
            }
            $archive->close();
        } else {
            require_once ABSPATH . 'wp-admin/includes/class-pclzip.php';
            $archive = new PclZip($zip);
            $list = $archive->extract(PCLZIP_OPT_BY_NAME, $names, PCLZIP_OPT_EXTRACT_AS_STRING);
            if (!is_array($list)) {
                return new WP_Error('wpjobportal_tr_zip', __('The downloaded translation could not be opened.', 'wp-job-portal'));
            }
            foreach ($list as $file) {
                if (isset($file['stored_filename'], $file['content']) && strlen($file['content']) <= self::MAX_FILE) {
                    $out[$file['stored_filename']] = (string) $file['content'];
                }
            }
        }
        if (empty($out[$names[0]])) {
            return new WP_Error('wpjobportal_tr_zip', __('The downloaded translation could not be opened.', 'wp-job-portal'));
        }
        return $out;
    }

    /* ------------------------------------------------------------------
     * What the screen shows
     * ---------------------------------------------------------------- */

    /**
     * One row per language: this site's languages first, then every other
     * translation on offer.
     */
    public static function rows() {
        $state = self::state();
        $manifest = get_site_transient(self::T_MANIFEST);
        if (!is_array($manifest)) {
            $manifest = self::manifest();
        }
        $list_error = is_wp_error($manifest) ? $manifest->get_error_message() : '';
        $manifest = is_array($manifest) ? $manifest : array();
        $wanted = self::wanted_locales();
        $rows = array();
        foreach (array_unique(array_merge($wanted, array_keys($manifest))) as $locale) {
            $site = in_array($locale, $wanted, true);
            $entry = $site ? self::entry_for($locale, $manifest) : $manifest[$locale];
            $have = isset($state['installed'][$locale]) ? $state['installed'][$locale] : null;
            $present = file_exists(self::file($locale, 'mo'));
            $bundled = $present ? '' : self::bundled_file($locale);
            if (!$site) {
                $status = 'offered';
            } elseif ($present && $entry && $have && self::is_current($locale, $entry, $state)) {
                $status = 'current';
            } elseif ($present && $entry && $have) {
                $status = 'update';
            } elseif ($present) {
                // Not put there by this: someone's own file, or WordPress's.
                $status = $entry ? 'update' : 'own';
            } elseif ($bundled !== '') {
                // Still translated, from the copy inside the plugin.
                $status = 'bundled';
            } elseif (isset($state['failed'][$locale])) {
                $status = 'failed';
            } elseif ($entry) {
                $status = 'missing';
            } else {
                $status = 'none';
            }
            $rows[] = array(
                'locale' => $locale,
                'name' => self::language_name($locale, $entry),
                'site' => $site,
                'entry' => $entry,
                'have' => $have,
                'status' => $status,
                'error' => isset($state['failed'][$locale]) ? $state['failed'][$locale]['error'] : '',
            );
        }
        return array('rows' => $rows, 'listerror' => $list_error, 'checked' => (int) $state['checked']);
    }

    /**
     * A language's name. In a list, "French (France) - Français"; inside a
     * sentence ($own), the language's own name alone, because an English name
     * reads wrongly in the middle of a translated sentence.
     */
    public static function language_name($locale, $entry = null, $own = false) {
        if ($entry && $entry['locale'] === $locale) {
            if ($own && $entry['native'] !== '') {
                return $entry['native'];
            }
            return $entry['native'] && $entry['native'] !== $entry['name']
                ? $entry['name'] . ' - ' . $entry['native'] : $entry['name'];
        }
        $known = get_site_transient('available_translations'); // WordPress's own list, if it has fetched it
        if (is_array($known) && isset($known[$locale])) {
            $item = $known[$locale];
            if ($own && !empty($item['native_name'])) {
                return $item['native_name'];
            }
            if (!empty($item['english_name'])) {
                return $item['english_name'];
            }
        }
        return $locale;
    }

    /** Where Install, Update now, Use it for me and Check for updates go. */
    public static function action_url($do, $locale = '') {
        $args = array('action' => 'wpjobportal_translations', 'do' => $do);
        if ($locale !== '') {
            $args['locale'] = $locale;
        }
        return wp_nonce_url(add_query_arg($args, admin_url('admin-post.php')), 'wpjobportal-translations');
    }

    public static function screen_url() {
        return admin_url('admin.php?page=wpjobportal&wpjobportallt=translations');
    }

    /** Install, Update now, Use it for me, Check for updates. */
    public static function handle_post() {
        if (!current_user_can('install_languages')) {
            wp_die(esc_html__('You are not allowed to install languages on this site.', 'wp-job-portal'));
        }
        check_admin_referer('wpjobportal-translations');
        $do = (WPJOBPORTALrequest::has('do', 'get') ? sanitize_key((string) WPJOBPORTALrequest::getVar('do', 'get', '')) : 'sync');
        $locale = sanitize_text_field((string) WPJOBPORTALrequest::getVar('locale', 'get', ''));
        $extra = array();
        if ($do === 'install' && self::valid_locale($locale)) {
            $result = is_wp_error(self::install_one($locale, true)) ? 'failed' : 'done';
        } elseif ($do === 'add' && self::valid_locale($locale)) {
            // A language this site does not have yet: WordPress's own
            // language pack first, so the language can be chosen under
            // Settings > General and in a profile, then WP Job Portal's.
            $wordpress = self::install_wordpress_language($locale);
            $done = self::install_one($locale, true);
            $result = is_wp_error($done) ? 'failed' : ($wordpress ? 'added' : 'addednowp');
            $extra['wpjobportal_trl'] = $locale;
        } elseif ($do === 'useme' && ($locale === 'en_US'
            || (self::valid_locale($locale) && in_array($locale, get_available_languages(), true)))) {
            // Just this admin: the rest of the site keeps its language.
            update_user_meta(get_current_user_id(), 'locale', $locale);
            $result = 'useme';
        } else {
            $state = self::sync(true, true);
            $result = empty($state['failed']) ? 'done' : 'failed';
        }
        $back = wp_get_referer();
        if (!$back || strpos($back, 'wpjobportallt=translations') === false) {
            $back = self::screen_url();
        }
        wp_safe_redirect(add_query_arg(
            array_merge(array('wpjobportal_tr' => $result), $extra),
            remove_query_arg(array('wpjobportal_tr', 'wpjobportal_trl'), $back)
        ));
        exit;
    }

    /**
     * WordPress's own language pack for a locale, from WordPress.org - the same
     * download Settings > General makes when a language is chosen there.
     *
     * @return bool Whether WordPress now has the language.
     */
    public static function install_wordpress_language($locale) {
        if (in_array($locale, get_available_languages(), true)) {
            return true;
        }
        require_once ABSPATH . 'wp-admin/includes/translation-install.php';
        if (!wp_can_install_language_pack()) {
            return false;
        }
        return (bool) wp_download_language_pack($locale);
    }

    /**
     * Install one language now, asked for by a person.
     *
     * @return true|WP_Error
     */
    public static function install_one($locale, $refresh = false) {
        $manifest = self::manifest($refresh);
        $state = self::state();
        if (is_wp_error($manifest)) {
            $result = $manifest;
        } else {
            $entry = self::entry_for($locale, $manifest);
            if (!$entry) {
                return new WP_Error('wpjobportal_tr_none', __('There is no translation for this language yet.', 'wp-job-portal'));
            }
            $result = self::install($locale, $entry, $state);
        }
        if (is_wp_error($result)) {
            $state['failed'][$locale] = array('at' => time(), 'error' => $result->get_error_message());
        } else {
            unset($state['failed'][$locale]);
        }
        self::save($state);
        return $result;
    }

    /**
     * The one banner: WP Job Portal is in English on a site that is not,
     * because its translation could not be installed and the plugin carries
     * no copy of its own. Updates that fail stay quiet - the installed
     * translation still works - and show on the Translations screen.
     */
    public static function notice() {
        if (!current_user_can('install_languages')) {
            return;
        }
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || in_array($screen->id, array('update', 'update-network'), true)) {
            return;
        }
        // Where an owner looks after the site, not every admin page.
        $page = sanitize_key((string) WPJOBPORTALrequest::getVar('page', 'get', ''));
        if (!in_array($screen->base, array('dashboard', 'plugins', 'update-core'), true) && strpos($page, 'wpjobportal') !== 0) {
            return;
        }
        if (WPJOBPORTALrequest::getVar('wpjobportallt', 'get', '') === 'translations') {
            return; // the screen says it itself
        }
        $state = self::state();
        $missing = array();
        foreach ($state['failed'] as $locale => $failed) {
            if (!file_exists(self::file($locale, 'mo')) && self::bundled_file($locale) === '') {
                $missing[$locale] = $failed;
            }
        }
        if (empty($missing)) {
            return;
        }
        $locale = isset($missing[get_user_locale()]) ? get_user_locale() : key($missing);
        $name = self::language_name($locale, self::entry_for($locale, get_site_transient(self::T_MANIFEST)), true);
        ?>
        <div class="notice notice-warning">
            <p>
                <strong><?php
                    /* translators: %s: a language name, e.g. Français */
                    echo esc_html(sprintf(__('WP Job Portal could not install its %s translation.', 'wp-job-portal'), $name));
                ?></strong>
                <?php
                /* translators: %s: the reason, e.g. "The download failed (...)." */
                echo esc_html(sprintf(__('It is shown in English until it is installed. %s', 'wp-job-portal'), $missing[$locale]['error']));
                ?>
                <a href="<?php echo esc_url(self::action_url('sync')); ?>"><?php echo esc_html__('Try again', 'wp-job-portal'); ?></a>
                |
                <a href="<?php echo esc_url(self::screen_url()); ?>"><?php echo esc_html__('Translations', 'wp-job-portal'); ?></a>
            </p>
        </div>
        <?php
    }
}
