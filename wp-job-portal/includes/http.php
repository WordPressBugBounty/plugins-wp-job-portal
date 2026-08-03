<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Return the TLS verification setting for an outbound WP Job Portal request.
 *
 * Production requests always verify TLS certificates. A developer may opt in
 * to accepting a self-signed certificate for a loopback endpoint only, for
 * example a local mock service running at https://127.0.0.1:8443.
 *
 * Add this to a local wp-config.php when needed:
 * define('WPJOBPORTAL_ALLOW_INSECURE_LOCAL_SSL', true);
 *
 * The exception is also restricted to WP_DEBUG and never applies to private
 * or public network addresses.
 *
 * @param string $wpjobportal_url Request URL.
 * @return bool True when WordPress must verify the TLS certificate.
 */
function wpjobportal_sslverify($wpjobportal_url) {
    $wpjobportal_host = wp_parse_url($wpjobportal_url, PHP_URL_HOST);
    if (!is_string($wpjobportal_host) || $wpjobportal_host === '') {
        return true;
    }

    $wpjobportal_host = strtolower(trim($wpjobportal_host, '[]'));
    $wpjobportal_is_loopback = $wpjobportal_host === 'localhost'
        || $wpjobportal_host === '::1'
        || (filter_var($wpjobportal_host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && strpos($wpjobportal_host, '127.') === 0);

    if (!$wpjobportal_is_loopback || !defined('WP_DEBUG') || !WP_DEBUG) {
        return true;
    }

    $wpjobportal_allow_insecure = defined('WPJOBPORTAL_ALLOW_INSECURE_LOCAL_SSL')
        && WPJOBPORTAL_ALLOW_INSECURE_LOCAL_SSL === true;
    $wpjobportal_allow_insecure = apply_filters(
        'wpjobportal_allow_insecure_local_ssl',
        $wpjobportal_allow_insecure,
        $wpjobportal_url,
        $wpjobportal_host
    );

    return !$wpjobportal_allow_insecure;
}
