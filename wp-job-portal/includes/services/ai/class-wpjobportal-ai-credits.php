<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

if (class_exists('WPJOBPORTAL_AI_Credits')) {
    return;
}

/**
 * The Zywrap credits a paid WP Job Portal licence adds every month. (2.9.0)
 *
 * A Pro, Business or Recruiting licence funds one Zywrap account with a monthly
 * allowance. wpjobportal.com decides the amount from the licence itself and
 * tells Zywrap which account; Zywrap refills it each month and spends it before
 * the account's own credits. This side only links the account the site is
 * connected to and shows what the licence server says: nothing here can raise
 * the amount.
 *
 * The answer is kept for twelve hours; connecting, disconnecting and "Claim"
 * ask again at once.
 *
 * Pattern: JS Help Desk's JSSTaicredits. Server side: the License Manager's
 * Ai\AiCredits, routes ai/status and ai/link.
 */
class WPJOBPORTAL_AI_Credits {

    const OPT   = 'wpjobportal_ai_credits';
    const TTL   = 43200;
    const RETRY = 3600;

    /** The licence state, refreshed if its token needs it; null without an active licence. */
    private static function licence() {
        if (!class_exists('WPJOBPORTAL_Licence_Client')) {
            return null;
        }
        $client = new WPJOBPORTAL_Licence_Client();
        $state = $client->refresh();
        if (empty($state['ok']) || empty($state['token']) || (isset($state['status']) && $state['status'] !== '' && $state['status'] !== 'active')) {
            return null;
        }
        return $state;
    }

    /** Without asking anything: is a licence recorded as active? */
    private static function licenceKnown() {
        if (!class_exists('WPJOBPORTAL_Licence_Client')) {
            return false;
        }
        $state = WPJOBPORTAL_Licence_Client::state();
        return !empty($state['ok']);
    }

    public static function accountId() {
        $account = get_option('wpjobportal_zywrap_account');
        return (is_array($account) && !empty($account['id'])) ? (string) $account['id'] : '';
    }

    /**
     * What this licence includes and where it goes, or null without an active licence.
     *
     * @return array|null monthly, plan, status, plan_remaining, renews_at, linked_site
     */
    public static function state($force = false) {
        if (!self::licenceKnown()) {
            return null;
        }
        $saved = get_option(self::OPT);
        if (!$force && is_array($saved) && isset($saved['checked']) && (int) $saved['checked'] > time() - self::TTL) {
            return $saved;
        }
        $licence = self::licence();
        if ($licence === null) {
            return null;
        }
        $account = self::accountId();
        return self::remember(self::ask('ai/status', $licence, $account !== '' ? array('account_id' => $account) : array()), $saved);
    }

    /**
     * The administrator pressed "Claim": this licence's monthly credits go to the
     * Zywrap account this site is connected to.
     */
    public static function claim() {
        $licence = self::licence();
        $account = self::accountId();
        if ($licence === null || $account === '') {
            return null;
        }
        $state = self::remember(self::ask('ai/link', $licence, array('account_id' => $account)), get_option(self::OPT));
        if (is_array($state) && in_array($state['status'], array('active', 'pending'), true)) {
            if (class_exists('WPJOBPORTAL_AI_Policy')) {
                WPJOBPORTAL_AI_Policy::record('credits-claim', '');
            }
            return $state;
        }
        return null;
    }

    /** One call to the licence server, with this site's activation token. */
    private static function ask($route, $licence, $body) {
        $url = WPJOBPORTAL_Licence_Client::endpoint() . $route;
        if (!WPJOBPORTAL_Licence_Client::transport_is_acceptable($url)) {
            return new WP_Error('wpjobportal_ai_credits_transport', 'Refused to send the activation token over this address.');
        }
        $response = wp_remote_post($url, array(
            'timeout'     => 15,
            'redirection' => 0,
            'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
            'body'        => wp_json_encode(array_merge((array) $body, array(
                'token'    => (string) $licence['token'],
                'site_url' => home_url(),
            ))),
        ));
        if (is_wp_error($response)) {
            return $response;
        }
        $json = json_decode((string) wp_remote_retrieve_body($response), true);
        return is_array($json) ? $json : new WP_Error('wpjobportal_ai_credits_unreadable', 'Unreadable answer.');
    }

    private static function remember($result, $saved) {
        if (is_wp_error($result) || empty($result['ok'])) {
            if (is_array($saved)) {
                $saved['checked'] = time() - self::TTL + self::RETRY;
                update_option(self::OPT, $saved, false);
                return $saved;
            }
            update_option(self::OPT, array('monthly' => 0, 'status' => 'unknown', 'plan' => '', 'plan_remaining' => null,
                'renews_at' => '', 'linked_site' => '', 'checked' => time() - self::TTL + self::RETRY), false);
            return null;
        }
        $state = array(
            'monthly'        => isset($result['monthly_credits']) ? max(0, (int) $result['monthly_credits']) : 0,
            'plan'           => isset($result['plan']) ? sanitize_key((string) $result['plan']) : '',
            'status'         => isset($result['status']) ? sanitize_key((string) $result['status']) : 'none',
            'plan_remaining' => (isset($result['plan_remaining']) && is_numeric($result['plan_remaining'])) ? max(0, (int) $result['plan_remaining']) : null,
            'renews_at'      => isset($result['renews_at']) ? sanitize_text_field((string) $result['renews_at']) : '',
            'linked_site'    => isset($result['linked_site']) ? sanitize_text_field((string) $result['linked_site']) : '',
            'checked'        => time(),
        );
        // Within one month at one amount the balance only goes down, so a lower
        // figure from a Zywrap reply since the server's daily sync is the newer one.
        if (is_array($saved) && $state['status'] === 'active' && isset($saved['status'], $saved['renews_at'], $saved['monthly'])
                && $saved['status'] === 'active' && $saved['renews_at'] === $state['renews_at']
                && (int) $saved['monthly'] === $state['monthly']
                && isset($saved['plan_remaining']) && $saved['plan_remaining'] !== null && $state['plan_remaining'] !== null) {
            $state['plan_remaining'] = min($state['plan_remaining'], (int) $saved['plan_remaining']);
        }
        update_option(self::OPT, $state, false);
        return $state;
    }

    public static function link() {
        return self::state(true);
    }

    public static function forget() {
        delete_option(self::OPT);
    }

    /** The last answer, without asking: for code that runs after every AI call. */
    public static function cached() {
        if (!self::licenceKnown()) {
            return null;
        }
        $saved = get_option(self::OPT);
        return (is_array($saved) && isset($saved['monthly'])) ? $saved : null;
    }

    public static function noteRemaining($remaining) {
        $saved = get_option(self::OPT);
        if (!is_array($saved) || !is_numeric($remaining)) {
            return;
        }
        $saved['plan_remaining'] = max(0, (int) $remaining);
        update_option(self::OPT, $saved, false);
    }

    public static function planLabel($state) {
        $slug = (is_array($state) && !empty($state['plan'])) ? (string) $state['plan'] : '';
        $names = array('pro' => 'Pro', 'business' => 'Business', 'recruiting' => 'Recruiting');
        return isset($names[$slug]) ? $names[$slug] : ucfirst($slug);
    }

    /**
     * One sentence for the Zywrap card.
     *
     * @return array text, warn, claim (show the Claim button)
     */
    public static function describe($state, $connected) {
        if (!is_array($state) || empty($state['monthly'])) {
            return array('text' => '', 'warn' => false, 'claim' => false);
        }
        $label = self::planLabel($state);
        $amount = number_format_i18n((int) $state['monthly']);
        if (!$connected) {
            return array('warn' => false, 'claim' => false, 'text' => sprintf(
                /* translators: 1: licence plan such as Pro, 2: number of credits */
                __('Your %1$s licence adds %2$s Zywrap credits every month once you connect an account.', 'wp-job-portal'),
                $label, $amount
            ));
        }
        if (in_array($state['status'], array('none', 'cancelled'), true)) {
            return array('warn' => false, 'claim' => true, 'text' => sprintf(
                /* translators: 1: licence plan such as Pro, 2: number of credits */
                __('Your %1$s licence includes %2$s Zywrap credits every month. Claim them for this account.', 'wp-job-portal'),
                $label, $amount
            ));
        }
        if ($state['status'] === 'elsewhere') {
            return array('warn' => true, 'claim' => false, 'text' => sprintf(
                /* translators: %s: the address of another site using this licence */
                __('This licence\'s monthly credits go to the Zywrap account connected on %s.', 'wp-job-portal'),
                $state['linked_site'] !== '' ? $state['linked_site'] : '?'
            ));
        }
        $text = sprintf(
            /* translators: 1: licence plan such as Pro, 2: number of credits */
            __('Your %1$s licence adds %2$s credits to this account every month.', 'wp-job-portal'),
            $label, $amount
        );
        if ($state['status'] === 'active') {
            $renews = ($state['renews_at'] !== '') ? strtotime($state['renews_at']) : false;
            if ($state['plan_remaining'] !== null && $renews) {
                $text .= ' ' . sprintf(
                    /* translators: 1: number of credits, 2: a date */
                    __('%1$s left this month. They refill on %2$s.', 'wp-job-portal'),
                    number_format_i18n((int) $state['plan_remaining']),
                    date_i18n(get_option('date_format'), $renews)
                );
            }
            return array('text' => $text, 'warn' => false, 'claim' => false);
        }
        return array('warn' => true, 'claim' => false, 'text' => $text . ' '
            . __('Waiting for Zywrap to add them. This is checked again twice a day.', 'wp-job-portal'));
    }
}
