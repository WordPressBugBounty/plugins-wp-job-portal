<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

if (class_exists('WPJOBPORTAL_AI_Usage')) {
    return;
}

/**
 * Every AI call counted in one table, the limits checked before a call goes
 * out, and the warnings when a limit comes near. (2.9.0)
 *
 * AI is the only part of a job board with a cost per use, and on a job board
 * the people pressing the buttons are mostly not the site owner: employers and
 * job seekers. So the per-user daily limit is on by default; the site-wide and
 * money limits are off until the owner sets them.
 *
 * Two ways a call is paid for, never added together:
 *   zywrap  Zywrap credits: the licence's monthly plan credits first, then the
 *           account's own. Counted in calls; Zywrap keeps the balance.
 *   byok    The site's own Anthropic or OpenAI key: real money, priced from the
 *           price list below, and the only thing the money limits govern.
 *
 * Pattern: JS Help Desk's JSSTaiusage.
 */
class WPJOBPORTAL_AI_Usage {

    const TABLE          = 'wj_portal_ai_usage';
    const SCHEMA_VERSION = '2.9.0';
    const OPT_SCHEMA     = 'wpjobportal_ai_usage_schema';
    const OPT_BUDGET     = 'wpjobportal_ai_budget';
    const OPT_ALERTED    = 'wpjobportal_ai_budget_alerted';

    const FUNDED_ZYWRAP = 'zywrap';
    const FUNDED_BYOK   = 'byok';

    const WARN_AT   = 0.8;
    const KEEP_DAYS = 400;

    /* ------------------------------------------------------------------ *
     * Schema
     * ------------------------------------------------------------------ */

    public static function register() {
        add_action('admin_init', array(__CLASS__, 'ensureSchema'), 2);
        add_action('wpjobportal_ai_usage_prune', array(__CLASS__, 'prune'));
        add_action('init', array(__CLASS__, 'schedulePrune'));
    }

    public static function table() {
        global $wpdb;
        return $wpdb->prefix . self::TABLE;
    }

    /** Also run by activation and the 2.9.0 update, so the table exists before any button is pressed. */
    public static function createTable() {
        global $wpdb;
        $wpdb->query("CREATE TABLE IF NOT EXISTS `" . self::table() . "` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `created` datetime DEFAULT NULL,
            `engine` varchar(50) NOT NULL DEFAULT '',
            `model` varchar(100) NOT NULL DEFAULT '',
            `funded` varchar(10) NOT NULL DEFAULT 'byok',
            `feature` varchar(60) NOT NULL DEFAULT '',
            `itemid` bigint(20) NOT NULL DEFAULT '0',
            `userid` bigint(20) NOT NULL DEFAULT '0',
            `intokens` int(11) NOT NULL DEFAULT '0',
            `outtokens` int(11) NOT NULL DEFAULT '0',
            `tokensknown` tinyint(1) NOT NULL DEFAULT '1',
            `cost` decimal(12,6) NOT NULL DEFAULT '0.000000',
            `priced` tinyint(1) NOT NULL DEFAULT '1',
            `ms` int(11) NOT NULL DEFAULT '0',
            `ok` tinyint(1) NOT NULL DEFAULT '1',
            `error` varchar(255) NOT NULL DEFAULT '',
            PRIMARY KEY (`id`),
            KEY `wpjp_ai_when` (`created`),
            KEY `wpjp_ai_user` (`userid`, `created`)
        ) " . $wpdb->get_charset_collate());
        update_option(self::OPT_SCHEMA, self::SCHEMA_VERSION, false);
    }

    public static function ensureSchema() {
        if (get_option(self::OPT_SCHEMA) === self::SCHEMA_VERSION && self::available()) {
            return;
        }
        self::createTable();
    }

    public static function available() {
        global $wpdb;
        static $known = null;
        if ($known === true) {
            return true;
        }
        $table = self::table();
        $known = ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) === $table);
        return $known;
    }

    public static function schedulePrune() {
        if (!wp_next_scheduled('wpjobportal_ai_usage_prune')) {
            wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', 'wpjobportal_ai_usage_prune');
        }
    }

    public static function prune() {
        global $wpdb;
        if (!self::available()) {
            return 0;
        }
        return (int) $wpdb->query($wpdb->prepare(
            "DELETE FROM `" . self::table() . "` WHERE created < %s",
            gmdate('Y-m-d H:i:s', time() - (self::KEEP_DAYS * DAY_IN_SECONDS))
        ));
    }

    /* ------------------------------------------------------------------ *
     * The limits
     * ------------------------------------------------------------------ */

    public static function defaults() {
        return array(
            'usercalls'  => 40,    // AI requests per signed-in user per day (administrators exempt)
            'daycalls'   => 0,     // AI requests per day, whole site
            'daymoney'   => 0.0,   // USD per day on the site's own key
            'monthmoney' => 0.0,   // USD per calendar month on the site's own key
            'alerts'     => true,  // e-mail the administrator at 80% and 100%
        );
    }

    public static function budget() {
        $saved = get_option(self::OPT_BUDGET, array());
        return self::cleanBudget(array_merge(self::defaults(), is_array($saved) ? $saved : array()));
    }

    public static function setBudget($new) {
        if (!is_array($new)) {
            return false;
        }
        $before = self::budget();
        $clean = self::cleanBudget(array_merge($before, $new));
        update_option(self::OPT_BUDGET, $clean, false);
        if ($before != $clean) {
            delete_option(self::OPT_ALERTED);
            if (class_exists('WPJOBPORTAL_AI_Policy')) {
                WPJOBPORTAL_AI_Policy::record('budget', '');
            }
        }
        return true;
    }

    private static function cleanBudget($b) {
        return array(
            'usercalls'  => max(0, (int) (isset($b['usercalls']) ? $b['usercalls'] : 0)),
            'daycalls'   => max(0, (int) (isset($b['daycalls']) ? $b['daycalls'] : 0)),
            'daymoney'   => max(0, round((float) (isset($b['daymoney']) ? $b['daymoney'] : 0), 2)),
            'monthmoney' => max(0, round((float) (isset($b['monthmoney']) ? $b['monthmoney'] : 0), 2)),
            'alerts'     => !empty($b['alerts']),
        );
    }

    /* ------------------------------------------------------------------ *
     * Prices (USD per million tokens, in / out), for the site's own key
     * ------------------------------------------------------------------ */

    public static function prices() {
        return (array) apply_filters('wpjobportal_ai_prices', array(
            'claude-haiku-5-5'  => array(0.10, 0.50),
            'claude-sonnet-5-5' => array(2.00, 10.00),
            'claude-opus-5-5'   => array(4.00, 20.00),
            'gpt-6-luna'        => array(0.10, 0.50),
            'gpt-5.6-luna'      => array(0.20, 1.20),
            'gpt-6.1-terra'     => array(1.00, 5.00),
            'gpt-6.1-sol'       => array(2.00, 10.00),
        ));
    }

    /** The longest matching prefix, so a dated model id finds its family. */
    public static function price($model) {
        $model = trim((string) $model);
        $best = null;
        $len = -1;
        foreach (self::prices() as $key => $pair) {
            if ($model !== '' && strpos($model, $key) === 0 && strlen($key) > $len) {
                $best = $pair;
                $len = strlen($key);
            }
        }
        return $best;
    }

    /** What one call cost, or null when the model has no price. Zywrap calls are 0: credits, not money. */
    public static function estimate($model, $in, $out, $funded) {
        if ($funded === self::FUNDED_ZYWRAP) {
            return 0.0;
        }
        $price = self::price($model);
        if ($price === null) {
            return null;
        }
        return round(((int) $in / 1000000) * $price[0] + ((int) $out / 1000000) * $price[1], 6);
    }

    /* ------------------------------------------------------------------ *
     * Asking first
     * ------------------------------------------------------------------ */

    /**
     * May this call be made? Asked before the request goes out: a limit checked
     * afterwards is only a report. A missing table never refuses: that would
     * take every AI button off a site for a reason nothing would explain.
     */
    public static function guard($context = array()) {
        if (!self::available()) {
            return self::allowed();
        }
        $budget = self::budget();
        $user = isset($context['user']) ? (int) $context['user'] : get_current_user_id();

        if ($budget['usercalls'] > 0 && $user > 0 && !user_can($user, 'manage_options')) {
            if (self::userCallsToday($user) >= $budget['usercalls']) {
                return self::refused('usercalls', sprintf(
                    /* translators: %s: number of AI requests a user may make per day */
                    __('You have used today\'s %s AI requests. Try again tomorrow.', 'wp-job-portal'),
                    number_format_i18n($budget['usercalls'])
                ));
            }
        }
        if ($budget['daycalls'] > 0) {
            $today = self::spend('day');
            if ($today['calls'] >= $budget['daycalls']) {
                return self::refused('daycalls', __('This site has reached its daily limit of AI requests. Try again tomorrow.', 'wp-job-portal'));
            }
        }
        $funded = isset($context['funded']) ? $context['funded'] : self::FUNDED_BYOK;
        if ($funded === self::FUNDED_BYOK) {
            if ($budget['daymoney'] > 0) {
                $today = self::spend('day');
                if ($today['cost'] >= $budget['daymoney']) {
                    return self::refused('daymoney', __('This site\'s daily AI budget has been spent. Try again tomorrow.', 'wp-job-portal'));
                }
            }
            if ($budget['monthmoney'] > 0) {
                $month = self::spend('month');
                if ($month['cost'] >= $budget['monthmoney']) {
                    return self::refused('monthmoney', __('This site\'s monthly AI budget has been spent.', 'wp-job-portal'));
                }
            }
        }
        return self::allowed();
    }

    private static function allowed() {
        return array('state' => 'ok', 'reason' => '', 'detail' => '');
    }

    private static function refused($reason, $detail) {
        return array('state' => 'off', 'reason' => $reason, 'detail' => $detail);
    }

    /* ------------------------------------------------------------------ *
     * Counting
     * ------------------------------------------------------------------ */

    /**
     * Write down one call, failures included and with their tokens: a request
     * charged for and then failed is exactly the spend nobody can account for.
     * Never stores the prompt or the answer.
     */
    public static function record($context = array(), $result = array()) {
        global $wpdb;
        if (!self::available()) {
            return 0;
        }
        $funded = isset($context['funded']) ? (string) $context['funded'] : self::FUNDED_BYOK;
        $model = isset($context['model']) ? (string) $context['model'] : '';
        $known = (isset($result['intokens']) || isset($result['outtokens']));
        $in = isset($result['intokens']) ? max(0, (int) $result['intokens']) : 0;
        $out = isset($result['outtokens']) ? max(0, (int) $result['outtokens']) : 0;
        $cost = $known ? self::estimate($model, $in, $out, $funded) : ($funded === self::FUNDED_ZYWRAP ? 0.0 : null);

        $wpdb->insert(self::table(), array(
            'created'     => current_time('mysql'),
            'engine'      => substr((string) (isset($context['engine']) ? $context['engine'] : ''), 0, 50),
            'model'       => substr($model, 0, 100),
            'funded'      => $funded,
            'feature'     => substr((string) (isset($context['feature']) ? $context['feature'] : ''), 0, 60),
            'itemid'      => isset($context['item']) ? (int) $context['item'] : 0,
            'userid'      => isset($context['user']) ? (int) $context['user'] : get_current_user_id(),
            'intokens'    => $in,
            'outtokens'   => $out,
            'tokensknown' => $known ? 1 : 0,
            'cost'        => ($cost === null) ? 0 : $cost,
            'priced'      => ($cost === null) ? 0 : 1,
            'ms'          => isset($context['ms']) ? (int) $context['ms'] : 0,
            'ok'          => empty($result['ok']) ? 0 : 1,
            'error'       => substr(sanitize_text_field((string) (isset($result['error']) ? $result['error'] : '')), 0, 255),
        ));
        $id = (int) $wpdb->insert_id;
        self::maybeAlert();
        return $id;
    }

    /** Guard, run, record, as one call, so nothing that spends can be half-metered. */
    public static function meter($context, $work) {
        $verdict = self::guard($context);
        if ($verdict['state'] !== 'ok') {
            return new WP_Error('wpjobportal_ai_limit', $verdict['detail'], array('reason' => $verdict['reason']));
        }
        $started = microtime(true);
        $result = call_user_func($work);
        $context['ms'] = (int) round((microtime(true) - $started) * 1000);

        if (is_wp_error($result)) {
            $row = array('ok' => false, 'error' => $result->get_error_message());
            $data = $result->get_error_data();
            if (is_array($data)) {
                foreach (array('intokens', 'outtokens') as $k) {
                    if (isset($data[$k])) {
                        $row[$k] = (int) $data[$k];
                    }
                }
            }
            self::record($context, $row);
            return $result;
        }
        $row = is_array($result) ? $result : array();
        $row['ok'] = true;
        if (isset($context['engine']) && $context['engine'] === 'zywrap' && !empty($row['model']) && $row['model'] !== 'zywrap') {
            $context['model'] = (string) $row['model'];
        }
        self::record($context, $row);
        return $result;
    }

    /* ------------------------------------------------------------------ *
     * Reading it back
     * ------------------------------------------------------------------ */

    private static function since($window) {
        switch ($window) {
            case 'day':
                return current_time('Y-m-d') . ' 00:00:00';
            case 'month':
                return current_time('Y-m') . '-01 00:00:00';
            case 'year':
                return current_time('Y') . '-01-01 00:00:00';
        }
        return '1970-01-01 00:00:00';
    }

    public static function userCallsToday($user) {
        global $wpdb;
        if (!self::available()) {
            return 0;
        }
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(id) FROM `" . self::table() . "` WHERE userid = %d AND created >= %s",
            (int) $user, self::since('day')
        ));
    }

    public static function spend($window = 'month') {
        global $wpdb;
        $out = array('calls' => 0, 'tokens' => 0, 'cost' => 0.0, 'unpriced' => 0, 'failed' => 0, 'zywrap' => 0);
        if (!self::available()) {
            return $out;
        }
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT COUNT(id) AS calls,
                    COALESCE(SUM(intokens + outtokens), 0) AS tokens,
                    COALESCE(SUM(cost), 0) AS cost,
                    COALESCE(SUM(CASE WHEN priced = 0 THEN 1 ELSE 0 END), 0) AS unpriced,
                    COALESCE(SUM(CASE WHEN ok = 0 THEN 1 ELSE 0 END), 0) AS failed,
                    COALESCE(SUM(CASE WHEN funded = %s THEN 1 ELSE 0 END), 0) AS zywrap
               FROM `" . self::table() . "` WHERE created >= %s",
            self::FUNDED_ZYWRAP, self::since($window)
        ));
        if ($row) {
            $out = array(
                'calls'    => (int) $row->calls,
                'tokens'   => (int) $row->tokens,
                'cost'     => (float) $row->cost,
                'unpriced' => (int) $row->unpriced,
                'failed'   => (int) $row->failed,
                'zywrap'   => (int) $row->zywrap,
            );
        }
        return $out;
    }

    /** Spend grouped by one of a fixed set of columns. */
    public static function breakdown($by = 'feature', $window = 'month', $limit = 20) {
        global $wpdb;
        $columns = array('feature' => 'feature', 'model' => 'model', 'engine' => 'engine', 'funded' => 'funded');
        if (!isset($columns[$by]) || !self::available()) {
            return array();
        }
        $col = $columns[$by];
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT `" . $col . "` AS k, COUNT(id) AS calls, COALESCE(SUM(intokens + outtokens), 0) AS tokens,
                    COALESCE(SUM(cost), 0) AS cost, COALESCE(SUM(CASE WHEN ok = 0 THEN 1 ELSE 0 END), 0) AS failed
               FROM `" . self::table() . "` WHERE created >= %s
              GROUP BY `" . $col . "` ORDER BY cost DESC, calls DESC LIMIT %d",
            self::since($window), max(1, min(100, (int) $limit))
        ));
        $out = array();
        foreach ((array) $rows as $row) {
            $out[] = array('key' => (string) $row->k, 'calls' => (int) $row->calls, 'tokens' => (int) $row->tokens, 'cost' => (float) $row->cost, 'failed' => (int) $row->failed);
        }
        return $out;
    }

    public static function recent($limit = 40) {
        global $wpdb;
        if (!self::available()) {
            return array();
        }
        return (array) $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM `" . self::table() . "` ORDER BY id DESC LIMIT %d",
            max(1, min(200, (int) $limit))
        ));
    }

    /* ------------------------------------------------------------------ *
     * Warnings
     * ------------------------------------------------------------------ */

    public static function alerts() {
        $out = array();
        $budget = self::budget();
        $day = self::spend('day');
        $month = self::spend('month');
        $checks = array(
            array('key' => 'daymoney', 'cap' => $budget['daymoney'], 'used' => $day['cost'], 'money' => true, 'label' => __('daily AI budget', 'wp-job-portal')),
            array('key' => 'monthmoney', 'cap' => $budget['monthmoney'], 'used' => $month['cost'], 'money' => true, 'label' => __('monthly AI budget', 'wp-job-portal')),
            array('key' => 'daycalls', 'cap' => $budget['daycalls'], 'used' => $day['calls'], 'money' => false, 'label' => __('daily AI request limit', 'wp-job-portal')),
        );
        foreach ($checks as $check) {
            if ($check['cap'] <= 0) {
                continue;
            }
            $share = $check['used'] / $check['cap'];
            if ($share < self::WARN_AT) {
                continue;
            }
            $cap = $check['money'] ? self::money($check['cap']) : number_format_i18n($check['cap']);
            $out[] = array(
                'key'   => $check['key'],
                'level' => ($share >= 1) ? 'stop' : 'warn',
                'text'  => ($share >= 1)
                    /* translators: 1: the name of a limit, 2: the amount it is set to */
                    ? sprintf(__('The %1$s of %2$s is used up. AI requests are refused until it resets.', 'wp-job-portal'), $check['label'], $cap)
                    /* translators: 1: the name of a limit, 2: the amount it is set to, 3: a percentage */
                    : sprintf(__('The %1$s of %2$s is %3$d%% used.', 'wp-job-portal'), $check['label'], $cap, (int) floor($share * 100)),
            );
        }
        if (class_exists('WPJOBPORTAL_AI_Credits')) {
            $state = WPJOBPORTAL_AI_Credits::cached();
            if (is_array($state) && !empty($state['monthly']) && $state['status'] === 'active'
                    && $state['plan_remaining'] !== null && $state['plan_remaining'] <= $state['monthly'] * (1 - self::WARN_AT)) {
                $out[] = array(
                    'key'   => 'allowance',
                    'level' => 'warn',
                    'text'  => sprintf(
                        /* translators: 1: credits left, 2: monthly credits included with the licence */
                        __('Only %1$s of this month\'s %2$s included Zywrap credits are left. After that, Zywrap uses the account\'s own credits.', 'wp-job-portal'),
                        number_format_i18n((int) $state['plan_remaining']),
                        number_format_i18n((int) $state['monthly'])
                    ),
                    'link'  => WPJOBPORTAL_AI_Engine::zywrapUrl('dashboard/credits'),
                );
            }
        }
        if (get_transient(WPJOBPORTAL_AI_Engine::OPT_ZYWRAP_NO_CREDITS)) {
            $out[] = array(
                'key'   => 'zywrapcredits',
                'level' => 'stop',
                'text'  => __('The Zywrap credits have run out, so AI requests are refused until you add credits.', 'wp-job-portal'),
                'link'  => WPJOBPORTAL_AI_Engine::zywrapUrl('dashboard/credits'),
            );
        }
        return $out;
    }

    /** E-mail the administrator once per period per alert. */
    public static function maybeAlert() {
        $budget = self::budget();
        if (empty($budget['alerts'])) {
            return 0;
        }
        $sent = get_option(self::OPT_ALERTED, array());
        if (!is_array($sent)) {
            $sent = array();
        }
        $fired = 0;
        foreach (self::alerts() as $alert) {
            $period = in_array($alert['key'], array('daycalls', 'daymoney'), true) ? current_time('Y-m-d') : current_time('Y-m');
            $marker = $alert['key'] . ':' . $alert['level'] . ':' . $period;
            if (isset($sent[$marker])) {
                continue;
            }
            $sent[$marker] = time();
            wp_mail(
                get_option('admin_email'),
                sprintf(
                    /* translators: %s: the site name */
                    __('[%s] AI usage warning', 'wp-job-portal'),
                    wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES)
                ),
                $alert['text'] . "\n\n" . (!empty($alert['link']) ? $alert['link'] . "\n\n" : '')
                    . admin_url('admin.php?page=wpjobportal_zywrap&wpjobportallt=zywrap&tab=usage')
            );
            $fired++;
        }
        if ($fired > 0) {
            arsort($sent);
            update_option(self::OPT_ALERTED, array_slice($sent, 0, 60, true), false);
        }
        return $fired;
    }

    /* ------------------------------------------------------------------ *
     * Presentation
     * ------------------------------------------------------------------ */

    public static function money($amount) {
        $amount = (float) $amount;
        $places = ($amount > 0 && $amount < 0.01) ? 4 : 2;
        return '$' . number_format_i18n($amount, $places);
    }

    public static function fundedLabel($key) {
        return ($key === self::FUNDED_ZYWRAP) ? __('Zywrap credits', 'wp-job-portal') : __('Your own provider key', 'wp-job-portal');
    }
}
