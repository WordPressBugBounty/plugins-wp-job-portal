<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Shared payment boundary for first-party commerce add-ons.
 *
 * Provider adapters retain responsibility for remote API calls and signature
 * verification. This service supplies route normalization, request rules,
 * privacy-safe outcomes and a bounded cross-provider idempotency ledger.
 */
class WPJOBPORTAL_Payment_Service {

    const STATS_OPTION = 'wpjobportal_payment_service_stats';
    const EVENTS_OPTION = 'wpjobportal_payment_event_ledger';
    const MAX_EVENTS = 250;
    const EVENT_TTL = 2592000;

    public static function providers() {
        $providers = array(
            'paypal' => array(
                'label' => 'PayPal',
                'webhook_task' => 'paypalwebhook',
                'verification' => 'remote_ipn',
            ),
            'stripe' => array(
                'label' => 'Stripe',
                'webhook_task' => 'stripewebhook',
                'verification' => 'signed_payload',
            ),
            'woocommerce' => array(
                'label' => 'WooCommerce',
                'webhook_task' => '',
                'verification' => 'wordpress_order',
            ),
        );
        $filtered = apply_filters('wpjobportal_payment_providers', $providers);
        return is_array($filtered) ? $filtered : $providers;
    }

    /**
     * Every task that is a provider calling us, rather than a person.
     *
     * A provider may declare one webhook task or several.
     *
     * @return array
     */
    public static function webhook_tasks() {
        $tasks = array();
        foreach (self::providers() as $provider) {
            if (empty($provider['webhook_task'])) {
                continue;
            }
            foreach ((array) $provider['webhook_task'] as $task) {
                $task = strtolower(preg_replace('/[^A-Za-z0-9_]/', '', (string) $task));
                if ($task !== '') {
                    $tasks[] = $task;
                }
            }
        }

        return array_values(array_unique($tasks));
    }

    /**
     * Validate the shared boundary before a purchase controller runs.
     */
    public static function authorize_route($task) {
        $task = strtolower(preg_replace('/[^A-Za-z0-9_]/', '', (string) $task));
        $provider = self::provider_for_task($task);
        if ($provider === '') {
            self::record('rejected', 'unknown_provider');
            return false;
        }

        $method = isset($_SERVER['REQUEST_METHOD'])
            ? strtoupper(sanitize_text_field(wp_unslash($_SERVER['REQUEST_METHOD'])))
            : '';
        // Taken from the providers map rather than a list written out here,
        // so a gateway that grows a second endpoint - PayPal's REST webhooks
        // beside its IPN listener - does not fall through to the logged-in
        // branch and reject its own provider's calls.
        $is_webhook = in_array($task, self::webhook_tasks(), true);
        if ($is_webhook && $method !== 'POST') {
            self::record('rejected', 'webhook_method');
            return false;
        }
        if (!$is_webhook && !is_user_logged_in()) {
            self::record('rejected', 'login_required');
            return false;
        }

        // The legacy Stripe flow posts a card token, and a request without one
        // is not a purchase. A hosted-checkout flow has no token by design -
        // no card detail reaches this site at all - so the requirement belongs
        // to the task rather than to the gateway.
        $token_required = ($provider === 'stripe' && !$is_webhook);

        /**
         * Filter whether a route must carry a card token.
         *
         * @param bool   $token_required Whether a token is required.
         * @param string $task           Task being run.
         * @param string $provider       Payment provider.
         */
        $token_required = (bool) apply_filters('wpjobportal_payment_token_required', $token_required, $task, $provider);

        if ($token_required) {
            $token = WPJOBPORTALrequest::getVar('stripeToken', 'post');
            if (!is_string($token) || trim($token) === '') {
                self::record('rejected', 'missing_payment_token');
                return false;
            }
        }

        self::record('authorized', $provider);
        do_action('wpjobportal_payment_route_authorized', $provider, $task, $is_webhook);
        return true;
    }

    /**
     * Atomically claim a provider event without storing its raw identifier.
     */
    public static function claim_event($provider, $event_id) {
        $provider = sanitize_key($provider);
        $event_id = is_scalar($event_id) ? trim((string) $event_id) : '';
        if (!isset(self::providers()[$provider]) || $event_id === '') {
            self::record('rejected', 'invalid_event');
            return false;
        }

        $ledger = self::event_ledger();
        $key = hash_hmac('sha256', $provider . ':' . $event_id, wp_salt('auth'));
        if (isset($ledger[$key])) {
            $reclaimable = $ledger[$key]['state'] === 'failed'
                || ($ledger[$key]['state'] === 'processing'
                    && absint($ledger[$key]['updated']) < (time() - 900));
            if (!$reclaimable) {
                self::record('duplicate', $provider);
                return false;
            }
        }

        $ledger[$key] = array(
            'provider' => $provider,
            'state' => 'processing',
            'created' => time(),
            'updated' => time(),
        );
        self::store_event_ledger($ledger);
        self::record('claimed', $provider);
        return true;
    }

    public static function complete_event($provider, $event_id, $success) {
        $provider = sanitize_key($provider);
        $event_id = is_scalar($event_id) ? trim((string) $event_id) : '';
        if ($event_id === '') {
            return false;
        }

        $ledger = self::event_ledger();
        $key = hash_hmac('sha256', $provider . ':' . $event_id, wp_salt('auth'));
        if (!isset($ledger[$key])) {
            return false;
        }
        $ledger[$key]['state'] = $success ? 'completed' : 'failed';
        $ledger[$key]['updated'] = time();
        self::store_event_ledger($ledger);
        self::record($success ? 'completed' : 'failed', $provider);
        return true;
    }

    public static function get_status() {
        $stats = get_option(self::STATS_OPTION, array());
        if (!is_array($stats)) {
            $stats = array();
        }
        $ledger = self::event_ledger();
        $counts = array('processing' => 0, 'completed' => 0, 'failed' => 0);
        foreach ($ledger as $event) {
            if (isset($counts[$event['state']])) {
                $counts[$event['state']]++;
            }
        }

        return array(
            'providers' => count(self::providers()),
            'authorized' => isset($stats['authorized']) ? absint($stats['authorized']) : 0,
            'rejected' => isset($stats['rejected']) ? absint($stats['rejected']) : 0,
            'duplicates' => isset($stats['duplicate']) ? absint($stats['duplicate']) : 0,
            'completed' => isset($stats['completed']) ? absint($stats['completed']) : 0,
            'failed' => isset($stats['failed']) ? absint($stats['failed']) : 0,
            'events' => $counts,
            'last_outcome' => isset($stats['last_outcome']) ? sanitize_key($stats['last_outcome']) : '',
            'last_at' => isset($stats['last_at']) ? sanitize_text_field($stats['last_at']) : '',
        );
    }

    private static function provider_for_task($task) {
        if (strpos($task, 'stripe') !== false) {
            return 'stripe';
        }
        if (strpos($task, 'paypal') !== false) {
            return 'paypal';
        }
        if (strpos($task, 'woocommerce') !== false || strpos($task, 'woocomme') !== false) {
            return 'woocommerce';
        }
        return '';
    }

    private static function event_ledger() {
        $ledger = get_option(self::EVENTS_OPTION, array());
        if (!is_array($ledger)) {
            $ledger = array();
        }
        $cutoff = time() - self::EVENT_TTL;
        foreach ($ledger as $key => $event) {
            if (!is_array($event)
                || empty($event['updated'])
                || absint($event['updated']) < $cutoff
            ) {
                unset($ledger[$key]);
            }
        }
        return $ledger;
    }

    private static function store_event_ledger($ledger) {
        if (count($ledger) > self::MAX_EVENTS) {
            uasort($ledger, static function ($left, $right) {
                return absint($left['updated']) <=> absint($right['updated']);
            });
            $ledger = array_slice($ledger, -self::MAX_EVENTS, null, true);
        }
        update_option(self::EVENTS_OPTION, $ledger, false);
    }

    private static function record($outcome, $reason) {
        $outcome = sanitize_key($outcome);
        $stats = get_option(self::STATS_OPTION, array());
        if (!is_array($stats)) {
            $stats = array();
        }
        $stats[$outcome] = isset($stats[$outcome]) ? absint($stats[$outcome]) + 1 : 1;
        $stats['last_outcome'] = $outcome;
        $stats['last_reason'] = sanitize_key($reason);
        $stats['last_at'] = current_time('mysql', true);
        update_option(self::STATS_OPTION, $stats, false);

        if (class_exists('WPJOBPORTAL_Audit_Service')) {
            WPJOBPORTAL_Audit_Service::record(
                'payment_' . $outcome,
                in_array($outcome, array('rejected', 'failed'), true) ? 'warning' : 'success',
                array('reason' => sanitize_key($reason))
            );
        }
    }
}
