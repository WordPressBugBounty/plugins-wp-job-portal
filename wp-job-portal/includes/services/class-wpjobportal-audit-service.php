<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Bounded, privacy-safe operational audit events.
 *
 * This is intentionally not a user-activity log. It stores only operational
 * event names, outcomes and small sanitized metadata values. Email addresses,
 * subjects, message bodies, request payloads and credentials are never stored.
 */
class WPJOBPORTAL_Audit_Service {

    const OPTION = 'wpjobportal_operational_audit';
    const LIMIT = 100;

    /**
     * Record one operational event.
     *
     * @param string $event Event name.
     * @param string $outcome success, failed, warning or info.
     * @param array  $context Non-sensitive scalar metadata.
     * @return void
     */
    public static function record($event, $outcome, $context = array()) {
        $event = sanitize_key($event);
        $allowed_outcomes = array('success', 'failed', 'warning', 'info');
        $outcome = in_array($outcome, $allowed_outcomes, true) ? $outcome : 'info';

        if ($event === '') {
            return;
        }

        $events = get_option(self::OPTION, array());
        if (!is_array($events)) {
            $events = array();
        }

        array_unshift(
            $events,
            array(
                'time' => current_time('mysql', true),
                'event' => $event,
                'outcome' => $outcome,
                'context' => self::sanitize_context($context),
            )
        );

        $events = array_slice($events, 0, self::LIMIT);
        self::store_events($events);
    }

    /**
     * Return the newest audit entries.
     *
     * @param int $limit Maximum entries.
     * @return array
     */
    public static function get_recent($limit = 20) {
        $events = get_option(self::OPTION, array());
        if (!is_array($events)) {
            return array();
        }

        return array_slice($events, 0, max(0, min(absint($limit), self::LIMIT)));
    }

    /**
     * Return aggregate audit information for diagnostics.
     *
     * @return array
     */
    public static function get_summary() {
        $events = self::get_recent(self::LIMIT);
        $summary = array(
            'retained' => count($events),
            'failed' => 0,
            'last_event' => '',
            'last_outcome' => '',
            'last_time' => '',
        );

        foreach ($events as $index => $entry) {
            if (isset($entry['outcome']) && $entry['outcome'] === 'failed') {
                $summary['failed']++;
            }
            if ($index === 0) {
                $summary['last_event'] = isset($entry['event']) ? sanitize_key($entry['event']) : '';
                $summary['last_outcome'] = isset($entry['outcome']) ? sanitize_key($entry['outcome']) : '';
                $summary['last_time'] = isset($entry['time']) ? sanitize_text_field($entry['time']) : '';
            }
        }

        return $summary;
    }

    /**
     * @param array $context Raw context.
     * @return array
     */
    private static function sanitize_context($context) {
        $sanitized = array();

        if (!is_array($context)) {
            return $sanitized;
        }

        foreach ($context as $key => $value) {
            $key = sanitize_key($key);
            if ($key === '' || is_array($value) || is_object($value) || is_resource($value)) {
                continue;
            }

            if (is_bool($value)) {
                $sanitized[$key] = $value ? 1 : 0;
                continue;
            }

            if (is_int($value) || is_float($value)) {
                $sanitized[$key] = $value;
                continue;
            }

            $value = WPJOBPORTAL_Redactor::redact(sanitize_text_field((string) $value));
            $sanitized[$key] = substr($value, 0, 250);
        }

        return $sanitized;
    }

    /**
     * Keep the bounded log out of WordPress's autoloaded options.
     *
     * @param array $events Sanitized events.
     * @return void
     */
    private static function store_events($events) {
        if (get_option(self::OPTION, null) === null) {
            add_option(self::OPTION, $events, '', 'no');
            return;
        }

        update_option(self::OPTION, $events, false);
    }
}
