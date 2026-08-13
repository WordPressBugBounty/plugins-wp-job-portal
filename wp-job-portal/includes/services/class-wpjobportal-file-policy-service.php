<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Shared file-extension policy for legacy upload and file-management paths.
 *
 * Configuration values are administrator-editable comma-separated strings.
 * Normalize every entry before comparison so formatting differences such as
 * "pdf, doc" do not reject valid files, and use exact membership checks rather
 * than substring matching.
 */
class WPJOBPORTAL_File_Policy_Service {

    const PROTECTED_PATHS = 8;

    /**
     * Normalize a configured extension list.
     *
     * @param string|array $configured_extensions Comma-separated values or arrays.
     * @return array
     */
    public static function parse_extensions($configured_extensions) {
        $pending = is_array($configured_extensions)
            ? $configured_extensions
            : explode(',', (string) $configured_extensions);
        $extensions = array();

        foreach ($pending as $value) {
            if (is_array($value)) {
                $extensions = array_merge($extensions, self::parse_extensions($value));
                continue;
            }

            $parts = explode(',', (string) $value);
            foreach ($parts as $part) {
                $extension = self::normalize_extension($part);
                if ($extension !== '') {
                    $extensions[$extension] = $extension;
                }
            }
        }

        return array_values($extensions);
    }

    /**
     * Determine whether one exact extension is configured.
     *
     * @param string       $extension             Extension reported by WordPress.
     * @param string|array $configured_extensions Configured allowlist.
     * @return bool
     */
    public static function is_extension_allowed($extension, $configured_extensions) {
        $extension = self::normalize_extension($extension);
        if ($extension === '') {
            return false;
        }

        return in_array(
            $extension,
            self::parse_extensions($configured_extensions),
            true
        );
    }

    /**
     * Return non-sensitive policy diagnostics.
     *
     * @return array
     */
    public static function get_status() {
        return array(
            'protected_paths' => self::PROTECTED_PATHS,
            'normalization' => true,
            'exact_matching' => true,
        );
    }

    /**
     * Normalize one extension without accepting paths or compound filenames.
     *
     * @param mixed $extension Raw extension.
     * @return string
     */
    private static function normalize_extension($extension) {
        if (!is_scalar($extension)) {
            return '';
        }

        $extension = strtolower(trim((string) $extension));
        $extension = ltrim($extension, '.');

        if ($extension === '' || !preg_match('/^[a-z0-9]{1,20}$/', $extension)) {
            return '';
        }

        return $extension;
    }
}
