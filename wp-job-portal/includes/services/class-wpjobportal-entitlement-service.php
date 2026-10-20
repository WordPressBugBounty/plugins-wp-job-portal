<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Shared, read-only entitlement decision boundary.
 *
 * Core owns the contract so free and premium workflows can ask the same
 * question. Commerce add-ons remain responsible for registering providers and
 * for mutating their own subscription, package, or usage records.
 */
class WPJOBPORTAL_Entitlement_Service {

    const DECISION_ALLOW = 'allow';
    const DECISION_DENY = 'deny';
    const DECISION_ABSTAIN = 'abstain';

    /** Return the canonical capability catalogue. */
    public static function capabilities() {
        $capabilities = array(
            'employer.company.create' => array('legacy_action' => 'company', 'metered' => true),
            'employer.department.create' => array('legacy_action' => 'department', 'metered' => true),
            'employer.job.create' => array('legacy_action' => 'job', 'metered' => true),
            'employer.job.feature' => array('legacy_action' => 'featuredjob', 'metered' => true),
            'employer.company.feature' => array('legacy_action' => 'featuredcompany', 'metered' => true),
            'employer.resume.contact' => array('legacy_action' => 'resumecontactdetail', 'metered' => true),
            'jobseeker.resume.create' => array('legacy_action' => 'resume', 'metered' => true),
            'jobseeker.resume.feature' => array('legacy_action' => 'featuredresume', 'metered' => true),
            'jobseeker.job.apply' => array('legacy_action' => 'jobapply', 'metered' => true),
            'jobseeker.job_alert.create' => array('legacy_action' => 'jobalert', 'metered' => true),
            'jobseeker.cover_letter.create' => array('legacy_action' => 'coverletter', 'metered' => true),
            'jobseeker.company.contact' => array('legacy_action' => 'companycontactdetail', 'metered' => true),
        );

        $filtered = apply_filters('wpjobportal_entitlement_capabilities', $capabilities);
        if (!is_array($filtered)) {
            return $capabilities;
        }

        $normalized = array();
        foreach ($filtered as $key => $definition) {
            $key = self::normalize_key($key);
            if ('' === $key || !is_array($definition)) {
                continue;
            }
            $normalized[$key] = array(
                'legacy_action' => isset($definition['legacy_action']) ? sanitize_key($definition['legacy_action']) : '',
                'metered' => !empty($definition['metered']),
            );
        }
        return $normalized;
    }

    /** Return normalized providers in deterministic priority order. */
    public static function providers() {
        $providers = apply_filters('wpjobportal_entitlement_providers', array());
        if (!is_array($providers)) {
            return array();
        }

        $normalized = array();
        foreach ($providers as $provider_id => $provider) {
            $provider_id = self::normalize_key($provider_id);
            if ('' === $provider_id || !is_array($provider) || empty($provider['check']) || !is_callable($provider['check'])) {
                continue;
            }
            $normalized[$provider_id] = array(
                'id' => $provider_id,
                'label' => isset($provider['label']) ? sanitize_text_field($provider['label']) : $provider_id,
                'priority' => isset($provider['priority']) ? intval($provider['priority']) : 10,
                'check' => $provider['check'],
                'scope' => isset($provider['scope']) && in_array($provider['scope'], array('user', 'company', 'both'), true)
                    ? $provider['scope']
                    : 'user',
            );
        }

        uasort($normalized, static function ($left, $right) {
            if ($left['priority'] === $right['priority']) {
                return strcmp($left['id'], $right['id']);
            }
            return $left['priority'] <=> $right['priority'];
        });
        return $normalized;
    }

    /**
     * Resolve an entitlement without consuming or reserving usage.
     *
     * The first provider to return allow or deny wins. If every provider
     * abstains, legacy/free behavior remains available for compatibility.
     */
    public static function check($capability, $subject = array(), $context = array()) {
        $capability = self::normalize_key($capability);
        $capabilities = self::capabilities();
        if ('' === $capability || !isset($capabilities[$capability])) {
            return self::result(self::DECISION_DENY, false, '', 'unknown_capability');
        }

        $subject = self::normalize_subject($subject);
        $context = is_array($context) ? $context : array();
        foreach (self::providers() as $provider) {
            try {
                $candidate = call_user_func($provider['check'], $capability, $subject, $context, $capabilities[$capability]);
            } catch (Throwable $exception) {
                do_action('wpjobportal_entitlement_provider_error', $provider['id'], $capability);
                continue;
            }

            $candidate = self::normalize_result($candidate, $provider['id']);
            if (self::DECISION_ABSTAIN !== $candidate['decision']) {
                return self::filter_result($candidate, $capability, $subject, $context);
            }
        }

        return self::filter_result(
            self::result(self::DECISION_ABSTAIN, true, '', 'unmanaged'),
            $capability,
            $subject,
            $context
        );
    }

    public static function is_allowed($capability, $subject = array(), $context = array()) {
        $result = self::check($capability, $subject, $context);
        return !empty($result['allowed']);
    }

    /** Return privacy-safe diagnostic information. */
    public static function get_status() {
        $providers = self::providers();
        return array(
            'capabilities' => count(self::capabilities()),
            'providers' => count($providers),
            'provider_ids' => array_keys($providers),
            'default' => 'allow_unmanaged',
        );
    }

    private static function normalize_subject($subject) {
        $subject = is_array($subject) ? $subject : array();
        return array(
            'wp_user_id' => isset($subject['wp_user_id']) ? absint($subject['wp_user_id']) : 0,
            'portal_user_id' => isset($subject['portal_user_id']) ? absint($subject['portal_user_id']) : 0,
            'company_id' => isset($subject['company_id']) ? absint($subject['company_id']) : 0,
        );
    }

    private static function normalize_result($candidate, $provider_id) {
        if (!is_array($candidate)) {
            return self::result(self::DECISION_ABSTAIN, true, $provider_id, 'provider_abstained');
        }

        $decision = isset($candidate['decision']) ? sanitize_key($candidate['decision']) : self::DECISION_ABSTAIN;
        if (!in_array($decision, array(self::DECISION_ALLOW, self::DECISION_DENY, self::DECISION_ABSTAIN), true)) {
            $decision = self::DECISION_ABSTAIN;
        }
        $allowed = self::DECISION_DENY !== $decision;
        $result = self::result(
            $decision,
            $allowed,
            $provider_id,
            isset($candidate['reason']) ? sanitize_key($candidate['reason']) : ''
        );
        $result['metered'] = !empty($candidate['metered']);
        $result['unlimited'] = !empty($candidate['unlimited']);
        $result['remaining'] = isset($candidate['remaining']) && is_numeric($candidate['remaining'])
            ? intval($candidate['remaining'])
            : null;
        $result['expires_at'] = isset($candidate['expires_at']) ? sanitize_text_field($candidate['expires_at']) : '';
        $result['source_id'] = isset($candidate['source_id']) ? absint($candidate['source_id']) : 0;
        return $result;
    }

    private static function result($decision, $allowed, $provider, $reason) {
        return array(
            'decision' => $decision,
            'allowed' => (bool) $allowed,
            'provider' => self::normalize_key($provider),
            'reason' => sanitize_key($reason),
            'metered' => false,
            'unlimited' => false,
            'remaining' => null,
            'expires_at' => '',
            'source_id' => 0,
        );
    }

    private static function filter_result($result, $capability, $subject, $context) {
        $filtered = apply_filters('wpjobportal_entitlement_result', $result, $capability, $subject, $context);
        return is_array($filtered)
            ? self::normalize_result($filtered, isset($filtered['provider']) ? $filtered['provider'] : '')
            : $result;
    }

    private static function normalize_key($value) {
        $value = is_scalar($value) ? strtolower((string) $value) : '';
        return preg_replace('/[^a-z0-9._-]/', '', $value);
    }
}
