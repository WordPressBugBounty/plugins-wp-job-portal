<?php
if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * S-4: where a resume's files really are. On disk each has a random 32-character
 * prefix, so its address cannot be guessed, and every folder holds a blank index
 * file, so it cannot be listed. Neither depends on the web server honouring
 * .htaccess, which many do not. The database keeps the name the candidate knows,
 * and files are handed out through the permission-checked download route only.
 *
 * Files from before this change keep working under their plain name until
 * protect_batch() has renamed them.
 */
class WPJOBPORTAL_Resume_File_Store {

    const PREFIX_PATTERN = '/^[0-9a-f]{32}_/';
    const CRON_HOOK = 'wpjobportal_protect_files';
    const VERSION_OPTION = 'wpjobportal_files_protected_for';

    public static function jobseeker_root() {
        $upload = wp_upload_dir();
        return trailingslashit($upload['basedir']) . wpjobportal::$_config->getConfigurationByConfigName('data_directory') . '/data/jobseeker';
    }

    public static function dir($resumeid) {
        return self::jobseeker_root() . '/resume_' . absint($resumeid) . '/resume';
    }

    /**
     * Everything a deleted resume left on disk: CVs, photo and custom-field
     * uploads (ID scans among them), deny files included. @return bool
     */
    public static function delete_resume_files($resumeid) {
        $resumeid = absint($resumeid);
        if (!$resumeid) {
            return false;
        }
        $dir = self::jobseeker_root() . '/resume_' . $resumeid;
        $fs = wpjobportal::wpjobportal_filesystem();
        return $fs && $fs->is_dir($dir) && $fs->delete($dir, true, 'd');
    }

    /** The file on disk for a stored name, or '' when there is none. */
    public static function path($resumeid, $filename) {
        $filename = basename((string) $filename);
        if ($filename === '') {
            return '';
        }
        return self::find(self::dir($resumeid), $filename);
    }

    /** The file in $dir stored under $filename, prefixed or not, or ''. */
    public static function find($dir, $filename, $prefixed_only = false) {
        $filename = basename((string) $filename);
        if ($filename === '') {
            return '';
        }
        foreach ((array) @scandir($dir) as $entry) {
            if (is_string($entry) && preg_match(self::PREFIX_PATTERN, $entry) && substr($entry, 33) === $filename) {
                return $dir . '/' . $entry;
            }
        }
        return !$prefixed_only && is_file($dir . '/' . $filename) ? $dir . '/' . $filename : '';
    }

    /** Writes and renames go through WordPress's file API, as the other uploads do. */
    private static function write($path, $contents) {
        $fs = wpjobportal::wpjobportal_filesystem();
        return $fs && $fs->put_contents($path, $contents);
    }

    /** @return bool */
    public static function move($from, $to) {
        $fs = wpjobportal::wpjobportal_filesystem();
        return $fs && $fs->move($from, $to, false);
    }

    /** Give a file its random prefix. @return bool */
    public static function hide($path) {
        $name = basename($path);
        return !preg_match(self::PREFIX_PATTERN, $name) && self::move($path, dirname($path) . '/' . bin2hex(random_bytes(16)) . '_' . $name);
    }

    /** A name not yet used by this resume, since random prefixes hide clashes from WordPress. */
    public static function unique_name($resumeid, $filename, $prefixed_only = false) {
        return self::unique_name_in(self::dir($resumeid), $filename, $prefixed_only);
    }

    public static function unique_name_in($dir, $filename, $prefixed_only = false) {
        $info = pathinfo($filename);
        $candidate = $filename;
        for ($i = 1; self::find($dir, $candidate, $prefixed_only && $candidate === $filename) !== ''; $i++) {
            $candidate = $info['filename'] . '-' . $i . (isset($info['extension']) ? '.' . $info['extension'] : '');
        }
        return $candidate;
    }

    /**
     * Blank index files from the data root down to one folder, and a deny-all
     * rule in the folder itself. Only there: photos and logos sit in sibling
     * folders and are linked directly. nginx ignores both files; readme.txt
     * gives the rule to add to its configuration.
     */
    public static function protect_folders($dir) {
        $upload = wp_upload_dir();
        $root = untrailingslashit(trailingslashit($upload['basedir']) . wpjobportal::$_config->getConfigurationByConfigName('data_directory'));
        for ($d = $dir; strpos($d, $root) === 0; $d = dirname($d)) {
            if (is_dir($d) && !is_file($d . '/index.html')) {
                self::write($d . '/index.html', '');
            }
        }
        if (is_dir($dir) && strpos($dir, $root) === 0) {
            if (!is_file($dir . '/.htaccess')) {
                self::write($dir . '/.htaccess', "# WP Job Portal: these files are handed out by the plugin's download route only\n<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nOrder deny,allow\nDeny from all\n</IfModule>\n");
            }
            if (!is_file($dir . '/web.config')) {
                self::write($dir . '/web.config', "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<configuration>\n  <system.webServer>\n    <security>\n      <authorization>\n        <remove users=\"*\" roles=\"\" verbs=\"\" />\n        <add accessType=\"Deny\" users=\"*\" />\n      </authorization>\n    </security>\n  </system.webServer>\n</configuration>\n");
            }
        }
    }

    /**
     * A file a custom upload field just stored: prefixed like a CV, under a
     * name not yet used in its folder. @return string The name to keep.
     */
    public static function hide_custom_upload($path) {
        $dir = dirname($path);
        $name = basename($path);
        // WordPress chose a free name, but cannot see prefixed files
        $keep = self::unique_name_in($dir, $name, true); // the plain file is this one
        self::protect_folders($dir); // deny rules first, so the file is never in an open folder
        if (self::move($path, $dir . '/' . bin2hex(random_bytes(16)) . '_' . $keep)) {
            return $keep;
        }
        // Left under its plain name it could be guessed: drop it, as a CV is dropped
        wp_delete_file($path);
        return '';
    }

    public static function register() {
        add_action(self::CRON_HOOK, array(__CLASS__, 'run_batch'));
        add_action('init', array(__CLASS__, 'after_update'));
    }

    /**
     * Files uploaded before an update are protected on the first request after
     * it, not once the database update has run: the deny rules at once, the
     * random names in the background. A site that never opens its dashboard,
     * or whose update fails, would otherwise keep its old CV addresses.
     */
    public static function after_update() {
        if (get_option(self::VERSION_OPTION) === wpjobportal::$_currentversion) {
            return;
        }
        $until = microtime(true) + 5;
        // A blank index in every existing folder (comp_1/, comp_1/logo/, photo/ ...),
        // not only the ones a new upload reaches, so none can be listed
        foreach (self::all_data_folders() as $dir) {
            if (microtime(true) > $until) {
                return; // the next request carries on, skipping folders already done
            }
            if (!is_file($dir . '/index.html')) {
                self::write($dir . '/index.html', '');
            }
        }
        foreach (self::folders() as $dir) {
            if (microtime(true) > $until) {
                return;
            }
            self::protect_folders($dir);
        }
        update_option(self::VERSION_OPTION, wpjobportal::$_currentversion);
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_single_event(time(), self::CRON_HOOK);
        }
    }

    public static function run_batch() {
        if (self::protect_batch(200) > 0) {
            wp_schedule_single_event(time() + 60, self::CRON_HOOK);
        }
    }

    /** The data directory and every folder below it. Links are not followed. */
    private static function all_data_folders() {
        $upload = wp_upload_dir();
        $root = trailingslashit($upload['basedir']) . wpjobportal::$_config->getConfigurationByConfigName('data_directory');
        if (!is_dir($root)) {
            return array();
        }
        $dirs = array($root);
        try {
            $items = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
            foreach ($items as $item) {
                if ($item->isDir() && !$item->isLink()) {
                    $dirs[] = $item->getPathname();
                }
            }
        } catch (Exception $e) {
            // an unreadable folder: index what was found
        }
        return $dirs;
    }

    /** Every existing folder that holds CVs or custom uploads. */
    private static function folders() {
        $upload = wp_upload_dir();
        $data = trailingslashit($upload['basedir']) . wpjobportal::$_config->getConfigurationByConfigName('data_directory') . '/data';
        $dirs = array();
        foreach ((array) glob(self::jobseeker_root() . '/resume_*', GLOB_ONLYDIR) as $resume_dir) {
            $dirs[] = $resume_dir . '/resume';
            $dirs[] = $resume_dir . '/custom_uploads';
        }
        // Custom upload fields hold whatever the form asked for, ID scans included
        foreach (array('/employer/comp_*', '/employer/job_*', '/profile/profile_*') as $pattern) {
            foreach ((array) glob($data . $pattern, GLOB_ONLYDIR) as $owner_dir) {
                $dirs[] = $owner_dir . '/custom_uploads';
            }
        }
        return array_filter($dirs, 'is_dir');
    }

    /** Older files and folders still to protect, up to $limit at a time. @return int Files renamed. */
    public static function protect_batch($limit = 50) {
        $done = 0;
        foreach (self::folders() as $dir) {
            self::protect_folders($dir);
            foreach ((array) scandir($dir) as $entry) {
                if ($done >= $limit) {
                    return $done;
                }
                if (in_array($entry, array('index.html', '.htaccess', 'web.config'), true) || !is_file($dir . '/' . $entry) || preg_match(self::PREFIX_PATTERN, $entry)) {
                    continue;
                }
                if (self::hide($dir . '/' . $entry)) {
                    $done++;
                }
            }
        }
        return $done;
    }
}
