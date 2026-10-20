<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Removes credentials and personal data from diagnostics before storage or display.
 */
class WPJOBPORTAL_Redactor {

    /**
     * Redact a scalar value or every scalar value in an array/object.
     *
     * @param mixed $value Value to redact.
     * @return mixed
     */
    public static function redact($value) {
        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = self::is_sensitive_key($key) ? '[redacted]' : self::redact($item);
            }
            return $value;
        }

        if (is_object($value)) {
            foreach (get_object_vars($value) as $key => $item) {
                $value->{$key} = self::is_sensitive_key($key) ? '[redacted]' : self::redact($item);
            }
            return $value;
        }

        if (!is_string($value) || $value === '') {
            return $value;
        }

        $redacted = $value;

        if (defined('ABSPATH') && ABSPATH !== '') {
            $redacted = str_replace(wp_normalize_path(ABSPATH), '[ABSPATH]/', wp_normalize_path($redacted));
        }

        $redacted = preg_replace(
            '/([?&](?:api[_-]?key|access[_-]?token|auth|authorization|password|passwd|secret|signature|token|webhook[_-]?secret|licen[cs]e[_-]?key|transaction[_-]?key|key)=)[^&\s]*/i',
            '$1[redacted]',
            $redacted
        );

        $redacted = preg_replace(
            '/((?:api[_-]?key|access[_-]?token|authorization|password|passwd|secret|signature|token|webhook[_-]?secret|licen[cs]e[_-]?key|transaction[_-]?key[a-z0-9_-]*)\s*["\']?\s*[:=]\s*["\']?)[^,\s"\'};]+/i',
            '$1[redacted]',
            $redacted
        );

        $redacted = preg_replace('/\b(?:sk|pk)_(?:live|test)_[A-Za-z0-9_-]+\b/', '[redacted-stripe-key]', $redacted);
        $redacted = preg_replace('/\bwhsec_[A-Za-z0-9_-]+\b/', '[redacted-webhook-secret]', $redacted);
        $redacted = preg_replace('/\bBearer\s+[A-Za-z0-9._~+\/=-]+\b/i', 'Bearer [redacted]', $redacted);
        $redacted = preg_replace('/\bBasic\s+[A-Za-z0-9+\/=]+\b/i', 'Basic [redacted]', $redacted);
        $redacted = preg_replace('/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/i', '[redacted-email]', $redacted);

        return $redacted;
    }

    /**
     * Determine whether an array/object key normally contains a secret.
     *
     * @param mixed $key Key to inspect.
     * @return bool
     */
    private static function is_sensitive_key($key) {
        if (!is_string($key)) {
            return false;
        }

        return (bool) preg_match(
            '/(?:api[_-]?key|access[_-]?token|authorization|password|passwd|secret|signature|private[_-]?key|webhook[_-]?secret|licen[cs]e[_-]?key|transaction[_-]?key)/i',
            $key
        );
    }
}
