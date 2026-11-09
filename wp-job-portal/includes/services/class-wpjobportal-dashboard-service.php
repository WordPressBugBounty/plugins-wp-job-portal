<?php
if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * W-20: what the admin Dashboard shows besides its 2.7 sections - which
 * sections are on, the date range, the "Needs your attention" list and the
 * headline numbers compared with the previous period.
 */
class WPJOBPORTAL_Dashboard_Service {

    const OPTION = 'wjp_dashboard_screen_options';
    const RANGE_META = 'wpjobportal_dashboard_range';
    const LAYOUT_META = 'wpjobportal_dashboard_layout';
    const HEALTH_TRANSIENT = 'wpjobportal_dashboard_health';
    const EXPORT_ACTION = 'wpjobportal_dashboard_export';

    public static function register() {
        add_action('admin_post_' . self::EXPORT_ACTION, array(__CLASS__, 'export'));
    }

    /** An amount in the site's default currency (Settings › Currencies), e.g. "€1,949.99". */
    public static function money($amount) {
        static $symbol = null;
        if ($symbol === null) {
            $model = WPJOBPORTALincluder::getJSModel('currency');
            $id = $model->getDefaultCurrencyId();
            $symbol = $id ? (string) $model->getCurrencySymbol($id) : '';
            $symbol = trim($symbol) !== '' ? trim($symbol) : '$';
        }
        return $symbol . number_format_i18n((float) $amount, 2);
    }

    /**
     * Invoice totals per currency (invoices keep the currency they were paid in,
     * so amounts in different currencies are never added together).
     *
     * @param string $where SQL condition (already escaped).
     * @return array currency id => amount
     */
    public static function amounts($where) {
        $db = wpjobportal::$_db;
        $rows = (array) $db->get_results("SELECT currencyid, SUM(amount) AS total FROM `" . $db->prefix . "wj_portal_invoices` WHERE " . $where . " GROUP BY currencyid");
        $out = array();
        foreach ($rows as $row) {
            if ((float) $row->total != 0) {
                $out[(int) $row->currencyid] = (float) $row->total;
            }
        }
        return $out;
    }

    /** Amounts per currency as text, e.g. "$1,899.99 + Rs.50.00". */
    public static function format_amounts($amounts) {
        if (empty($amounts)) {
            return self::money(0);
        }
        $model = WPJOBPORTALincluder::getJSModel('currency');
        $parts = array();
        foreach ($amounts as $currency => $amount) {
            $symbol = trim((string) $model->getCurrencySymbol($currency));
            $parts[] = ($symbol !== '' ? $symbol : '') . number_format_i18n($amount, 2);
        }
        return implode(' + ', $parts);
    }

    /** The change between two periods, only when both are in the same single currency. */
    public static function delta_amounts($cur, $prev) {
        $currencies = array_unique(array_merge(array_keys($cur), array_keys($prev)));
        if (count($currencies) <= 1) {
            return self::delta(array_sum($cur), array_sum($prev), false); // money keeps the percentage
        }
        // Several currencies: compare the main one (most money now, else before), named by its symbol.
        $main = self::main_currency($cur, $prev);
        $d = self::delta(isset($cur[$main]) ? $cur[$main] : 0, isset($prev[$main]) ? $prev[$main] : 0, false);
        $symbol = trim((string) WPJOBPORTALincluder::getJSModel('currency')->getCurrencySymbol($main));
        /* translators: 1: change, e.g. +750%, 2: currency symbol, e.g. $ */
        return array(sprintf(__('%1$s in %2$s', 'wp-job-portal'), $d[0], $symbol), $d[1]);
    }

    /** The currency with the most money in the current period, else in the previous one. */
    public static function main_currency($cur, $prev) {
        $pool = !empty($cur) ? $cur : $prev;
        arsort($pool);
        return (int) key($pool);
    }


    /** Payments are on when the Credits part of the Marketplace Suite is active. */
    public static function has_payments() {
        return in_array('credits', (array) wpjobportal::$_active_addons, true);
    }

    /** Date ranges offered, in days. */
    public static function ranges() {
        return array(
            7 => __('7 days', 'wp-job-portal'),
            30 => __('30 days', 'wp-job-portal'),
            90 => __('90 days', 'wp-job-portal'),
            365 => __('12 months', 'wp-job-portal'),
        );
    }

    /**
     * Sections on by default: what needs doing, the numbers, growth and the two
     * lists people act on. The rest stay one click away in Options.
     */
    public static function defaults() {
        // A careers site follows its own hiring; a marketplace watches the platform.
        if (class_exists('WPJOBPORTAL_Product_Mode_Service') && WPJOBPORTAL_Product_Mode_Service::get_mode() === WPJOBPORTAL_Product_Mode_Service::MODE_CAREERS) {
            return array('attention' => 'on', 'kpis' => 'on', 'hiring_funnel' => 'on', 'job_performance' => 'on',
                'platform_growth' => 'on', 'activity_feed' => 'on', '_v2' => 1);
        }
        return array('attention' => 'on', 'kpis' => 'on', 'quick_actions' => 'on', 'platform_growth' => 'on',
            'job_performance' => 'on', 'hiring_funnel' => 'on', 'revenue' => 'on', 'activity_feed' => 'on', '_v2' => 1);
    }

    /** Every section key Options can switch. */
    public static function keys() {
        // W-20: Latest applications, Latest Resumes, Latest Job Seekers, Latest Employers,
        // Quick Stats and Recent Job Postings were removed (owner, 2026-10-30): Recent
        // activity, the key numbers, the attention cards and Job performance cover them.
        return array('attention', 'kpis', 'quick_actions', 'platform_growth', 'hiring_funnel', 'job_performance', 'revenue',
            'activity_feed', 'jobs_by_status_chart', 'top_categories_chart', 'latest_subscriptions', 'latest_payments',
            'latest_activity', 'system_error_log');
    }

    /** Sections only full administrators see: money and the error log. */
    public static function admin_only_keys() {
        return array('revenue', 'latest_subscriptions', 'latest_payments', 'system_error_log');
    }

    /** The section keys this user may see. */
    public static function allowed_keys() {
        $keys = self::has_payments() ? self::keys() : array_values(array_diff(self::keys(), array('revenue')));
        return current_user_can('manage_options') ? $keys : array_values(array_diff($keys, self::admin_only_keys()));
    }

    /** This user's saved layout (sections on, order), or null. */
    private static function user_layout() {
        $uid = get_current_user_id();
        $layout = $uid ? get_user_meta($uid, self::LAYOUT_META, true) : '';
        return is_array($layout) ? $layout : null;
    }

    /**
     * The sections that are on for this user: their own layout, else the
     * site-wide choice saved before 2.8.0 (with the two new blocks added), else
     * the defaults. Sections the user may not see are dropped.
     */
    public static function options() {
        $layout = self::user_layout();
        if ($layout !== null && isset($layout['on']) && is_array($layout['on'])) {
            $on = array_fill_keys($layout['on'], 'on');
            // Sections that did not exist when this layout was saved start as their default.
            $seen = isset($layout['seen']) && is_array($layout['seen']) ? $layout['seen'] : array();
            foreach (array_keys(self::defaults()) as $key) {
                if ($key !== '_v2' && !in_array($key, $seen, true) && !in_array($key, $layout['order'] ?? array(), true)) {
                    $on[$key] = 'on';
                }
            }
        } else {
            $saved = get_option(self::OPTION, null);
            if (!is_array($saved)) {
                $on = self::defaults();
            } else {
                $on = empty($saved['_v2']) ? $saved + array('attention' => 'on', 'kpis' => 'on') : $saved;
            }
        }
        return array_intersect_key($on, array_flip(self::allowed_keys()));
    }

    /** The order sections are drawn in: this user's, then any new ones in the usual place. */
    public static function order() {
        $layout = self::user_layout();
        $order = ($layout !== null && isset($layout['order']) && is_array($layout['order'])) ? array_values(array_intersect($layout['order'], self::keys())) : array();
        // A section added after the layout was saved goes in its usual place (after
        // the section it normally follows), not at the end.
        $previous = null;
        foreach (self::keys() as $key) {
            if (!in_array($key, $order, true)) {
                $at = $previous === null ? 0 : array_search($previous, $order, true) + 1;
                array_splice($order, $at, 0, array($key));
            }
            $previous = $key;
        }
        return array_values(array_intersect($order, self::allowed_keys()));
    }

    /** Save this user's layout. */
    public static function save_layout($on, $order) {
        $allowed = self::allowed_keys();
        update_user_meta(get_current_user_id(), self::LAYOUT_META, array(
            'on' => array_values(array_intersect(array_map('sanitize_key', (array) $on), $allowed)),
            'order' => array_values(array_unique(array_intersect(array_map('sanitize_key', (array) $order), self::keys()))),
            'seen' => self::keys(),
        ));
    }

    /** Back to the defaults for this user. */
    public static function reset_layout() {
        update_user_meta(get_current_user_id(), self::LAYOUT_META, array('on' => array_keys(array_diff_key(self::defaults(), array('_v2' => 1))), 'order' => self::keys(), 'seen' => self::keys()));
    }

    /** How wide a section is in the grid: full row, two thirds, one third or half. */
    public static function width($key) {
        $map = array('attention' => 'full', 'kpis' => 'full', 'quick_actions' => 'full', 'activity_feed' => 'full',
            'platform_growth' => 'wide', 'hiring_funnel' => 'narrow', 'job_performance' => 'wide', 'revenue' => 'narrow');
        return isset($map[$key]) ? $map[$key] : 'half';
    }

    /** The name shown for a section in Options. */
    public static function label($key) {
        $labels = array(
            'attention' => __('Needs your attention', 'wp-job-portal'), 'kpis' => __('Key numbers', 'wp-job-portal'),
            'quick_actions' => __('Quick Actions', 'wp-job-portal'), 'platform_growth' => __('Platform Growth', 'wp-job-portal'),
            'job_performance' => __('Job performance', 'wp-job-portal'), 'hiring_funnel' => __('Hiring funnel', 'wp-job-portal'),
            'activity_feed' => __('Recent activity', 'wp-job-portal'), 'revenue' => __('Revenue', 'wp-job-portal'),
            'jobs_by_status_chart' => __('Jobs By Status', 'wp-job-portal'), 'top_categories_chart' => __('Top Job Categories', 'wp-job-portal'),
            'latest_subscriptions' => __('Latest Package Subscriptions', 'wp-job-portal'), 'latest_payments' => __('Latest Payments', 'wp-job-portal'),
            'latest_activity' => __('Change log (staff edits)', 'wp-job-portal'), 'system_error_log' => __('System Error Log', 'wp-job-portal'),
        );
        return isset($labels[$key]) ? $labels[$key] : $key;
    }

    /** The range in days: from the link, else this user's last choice, else 30. */
    public static function range() {
        $ranges = self::ranges();
        $asked = absint(WPJOBPORTALrequest::getVar('range', 'get', 0));
        $uid = get_current_user_id();
        if (isset($ranges[$asked])) {
            if ($uid) {
                update_user_meta($uid, self::RANGE_META, $asked);
            }
            return $asked;
        }
        $kept = $uid ? absint(get_user_meta($uid, self::RANGE_META, true)) : 0;
        return isset($ranges[$kept]) ? $kept : 30;
    }

    /**
     * Everything waiting for someone: approvals by type (the same counts as the
     * menu's Approvals badge), jobs closing within 7 days, applications nobody
     * has opened. Add-ons add theirs with wpjobportal_dashboard_attention.
     *
     * @return array{total_approvals:int, items:array}
     */
    public static function attention() {
        $db = wpjobportal::$_db;
        $prefix = $db->prefix . 'wj_portal_';
        $items = array();
        $counts = WPJOBPORTAL_Admin_Menu_Service::counts();
        foreach (WPJOBPORTAL_Admin_Menu_Service::approval_queues() as $key => $queue) {
            $n = isset($counts['approvals_' . $key]) ? (int) $counts['approvals_' . $key] : 0;
            if ($n > 0) {
                $items[] = array(
                    'count' => $n,
                    /* translators: %s: what waits, e.g. Jobs, Companies */
                    'label' => sprintf(__('%s waiting for approval', 'wp-job-portal'), $queue['label']),
                    'url' => admin_url('admin.php?page=wpjobportal_approvals&type=' . rawurlencode($key)),
                    'action' => __('Review', 'wp-job-portal'),
                    'tone' => 'wait',
                    'icon' => 'check',
                    'queue' => $key,
                );
            }
        }
        // Direction A: the biggest queue keeps its own card; the smaller ones share one.
        $queues = array_values(array_filter($items, function ($i) { return isset($i['queue']); }));
        if (count($queues) > 2) {
            usort($queues, function ($a, $b) { return $b['count'] - $a['count']; });
            $rest = array_slice($queues, 1);
            $names = array();
            foreach ($rest as $r) {
                $q = WPJOBPORTAL_Admin_Menu_Service::approval_queues();
                $names[] = function_exists('mb_strtolower') ? mb_strtolower($q[$r['queue']]['label']) : strtolower($q[$r['queue']]['label']);
            }
            $items = array($queues[0], array(
                'count' => array_sum(array_column($rest, 'count')),
                'label' => __('Other approvals', 'wp-job-portal'),
                'note' => implode(', ', $names),
                'url' => admin_url('admin.php?page=wpjobportal_approvals'),
                'action' => __('Review', 'wp-job-portal'),
                'tone' => 'wait',
                'icon' => 'check',
            ));
        }

        $closed = WPJOBPORTALincluder::getJSModel('jobstatus')->getClosedStatusIds();
        $closing = (int) $db->get_var("SELECT COUNT(id) FROM `" . $prefix . "jobs`
            WHERE status = 1 AND startpublishing < DATE_ADD(CURDATE(), INTERVAL 1 DAY)
            AND DATE(stoppublishing) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)"
            . ($closed ? " AND jobstatus NOT IN (" . implode(',', array_map('intval', $closed)) . ")" : ''));
        if ($closing > 0) {
            $items[] = array(
                'count' => $closing,
                'label' => _n('Job closes in the next 7 days', 'Jobs close in the next 7 days', $closing, 'wp-job-portal'),
                'url' => admin_url('admin.php?page=wpjobportal_job&view=closing'),
                'action' => __('View', 'wp-job-portal'),
                'tone' => 'info',
                'icon' => 'clock',
            );
        }

        $unopened = $db->get_row("SELECT COUNT(id) AS n, SUM(apply_date < DATE_SUB(NOW(), INTERVAL 3 DAY)) AS old
            FROM `" . $prefix . "jobapply` WHERE resumeview = 0 AND status <> 4");
        $n = $unopened ? (int) $unopened->n : 0;
        if ($n > 0) {
            $old = (int) $unopened->old;
            $items[] = array(
                'count' => $n,
                'label' => _n('Application not opened yet', 'Applications not opened yet', $n, 'wp-job-portal'),
                /* translators: %s: number of applications */
                'note' => $old > 0 ? sprintf(_n('%s waiting over 3 days', '%s waiting over 3 days', $old, 'wp-job-portal'), number_format_i18n($old)) : '',
                'url' => admin_url('admin.php?page=wpjobportal_jobapply&wpjobportallt=applications&view=new'),
                'action' => __('Open', 'wp-job-portal'),
                'tone' => $old > 0 ? 'bad' : 'info',
                'icon' => 'mail',
            );
        }

        if (self::has_payments() && current_user_can('manage_options')) {
            $pending = $db->get_row("SELECT COUNT(id) AS n FROM `" . $prefix . "invoices` WHERE status = 0");
            if ($pending && (int) $pending->n > 0) {
                $items[] = array(
                    'count' => (int) $pending->n,
                    'label' => _n('Payment not completed', 'Payments not completed', (int) $pending->n, 'wp-job-portal'),
                    /* translators: %s: amount */
                    'note' => sprintf(__('%s waiting', 'wp-job-portal'), self::format_amounts(self::amounts('status = 0'))),
                    'url' => admin_url('admin.php?page=wpjobportal_invoice'),
                    'action' => __('Review', 'wp-job-portal'),
                    'tone' => 'wait',
                    'icon' => 'card',
                );
            }
        }

        // The setup checklist, while it is unfinished and not dismissed.
        if (class_exists('WPJOBPORTAL_Onboarding_Service')) {
            $setup = WPJOBPORTAL_Onboarding_Service::get_state();
            $left = max(0, (int) $setup['total'] - (int) $setup['completed']);
            if ($left > 0 && empty($setup['dismissed'])) {
                $items[] = array(
                    'count' => $left,
                    'label' => _n('Setup step left', 'Setup steps left', $left, 'wp-job-portal'),
                    'url' => admin_url('admin.php?page=wpjobportal_postinstallation&wpjobportallt=quickstart'),
                    'action' => __('Finish setup', 'wp-job-portal'),
                    'tone' => 'info',
                    'icon' => 'flag',
                );
            }
        }

        // Site health in one line, for administrators.
        if (current_user_can('manage_options')) {
            $health = self::site_health();
            $issues = $health['critical'] + $health['warning'];
            if ($issues > 0 || $health['errors'] > 0) {
                $parts = array();
                if ($health['critical']) {
                    /* translators: %s: number */
                    $parts[] = sprintf(_n('%s critical', '%s critical', $health['critical'], 'wp-job-portal'), number_format_i18n($health['critical']));
                }
                if ($health['warning']) {
                    /* translators: %s: number */
                    $parts[] = sprintf(_n('%s warning', '%s warnings', $health['warning'], 'wp-job-portal'), number_format_i18n($health['warning']));
                }
                if ($health['errors']) {
                    /* translators: %s: number */
                    $parts[] = sprintf(_n('%s error logged this week', '%s errors logged this week', $health['errors'], 'wp-job-portal'), number_format_i18n($health['errors']));
                }
                $items[] = array(
                    'count' => $issues + $health['errors'],
                    'label' => __('Site health', 'wp-job-portal'),
                    'note' => implode(' · ', $parts),
                    'url' => admin_url('admin.php?page=wpjobportal_systemstatus'),
                    'action' => __('Check', 'wp-job-portal'),
                    'tone' => ($health['critical'] || $health['errors']) ? 'bad' : 'info',
                    'icon' => 'pulse',
                );
            }
        }

        $items = (array) apply_filters('wpjobportal_dashboard_attention', $items);
        // Most urgent first: overdue (red), then waiting (amber), then for information.
        $weight = array('bad' => 0, 'wait' => 1, 'info' => 2);
        $index = array_flip(array_keys($items));
        uksort($items, function ($a, $b) use ($items, $weight, $index) {
            $wa = isset($items[$a]['tone'], $weight[$items[$a]['tone']]) ? $weight[$items[$a]['tone']] : 2;
            $wb = isset($items[$b]['tone'], $weight[$items[$b]['tone']]) ? $weight[$items[$b]['tone']] : 2;
            return $wa === $wb ? $index[$a] - $index[$b] : $wa - $wb;
        });
        $items = array_values($items);
        return array('total_approvals' => isset($counts['approvals']) ? (int) $counts['approvals'] : 0, 'items' => $items);
    }

    /**
     * Headline numbers for the range, each with the previous range of the same
     * length to compare with.
     *
     * @param int $days
     * @return array[] label, value, previous, money
     */
    public static function kpis($days) {
        $db = wpjobportal::$_db;
        $prefix = $db->prefix . 'wj_portal_';
        $now = current_time('mysql');
        $from = gmdate('Y-m-d H:i:s', strtotime($now . ' -' . $days . ' days'));
        $before = gmdate('Y-m-d H:i:s', strtotime($now . ' -' . (2 * $days) . ' days'));
        $pair = function ($table, $column, $extra = '', $agg = 'COUNT(*)') use ($db, $prefix, $now, $from, $before) {
            $row = $db->get_row($db->prepare(
                "SELECT COALESCE(SUM(CASE WHEN `$column` > %s AND `$column` <= %s THEN v END), 0) AS cur,
                        COALESCE(SUM(CASE WHEN `$column` > %s AND `$column` <= %s THEN v END), 0) AS prev
                 FROM (SELECT `$column`, " . ($agg === 'COUNT(*)' ? '1' : $agg) . " AS v FROM `" . $prefix . $table . "` WHERE `$column` > %s" . $extra . ") AS t",
                $from, $now, $before, $from, $before
            ));
            return array($row ? (float) $row->cur : 0, $row ? (float) $row->prev : 0);
        };
        $kpis = array();
        list($cur, $prev) = $pair('jobapply', 'apply_date');
        $kpis[] = array('label' => __('Applications', 'wp-job-portal'), 'series' => self::series('jobapply', 'apply_date', $days), 'value' => $cur, 'previous' => $prev, 'url' => admin_url('admin.php?page=wpjobportal_jobapply&wpjobportallt=applications'));
        list($cur, $prev) = $pair('jobs', 'created');
        $kpis[] = array('label' => __('Jobs posted', 'wp-job-portal'), 'series' => self::series('jobs', 'created', $days), 'value' => $cur, 'previous' => $prev, 'url' => admin_url('admin.php?page=wpjobportal_job'));
        list($cur, $prev) = $pair('resume', 'created');
        $kpis[] = array('label' => __('New resumes', 'wp-job-portal'), 'series' => self::series('resume', 'created', $days), 'value' => $cur, 'previous' => $prev, 'url' => admin_url('admin.php?page=wpjobportal_resume'));
        list($cur, $prev) = $pair('companies', 'created');
        $careers = class_exists('WPJOBPORTAL_Product_Mode_Service') && WPJOBPORTAL_Product_Mode_Service::get_mode() === WPJOBPORTAL_Product_Mode_Service::MODE_CAREERS;
        if (!$careers) $kpis[] = array('label' => __('New companies', 'wp-job-portal'), 'series' => self::series('companies', 'created', $days), 'value' => $cur, 'previous' => $prev, 'url' => admin_url('admin.php?page=wpjobportal_company'));
        if (in_array('credits', (array) wpjobportal::$_active_addons, true) && current_user_can('manage_options')) {
            $cur_m = self::amounts("status = 1 AND created > '" . esc_sql($from) . "' AND created <= '" . esc_sql($now) . "'");
            $prev_m = self::amounts("status = 1 AND created > '" . esc_sql($before) . "' AND created <= '" . esc_sql($from) . "'");
            $kpis[] = array('label' => __('Revenue', 'wp-job-portal'), 'money' => true,
                // The trend line follows one currency (amounts in different currencies are not added).
                'series' => self::series('invoices', 'created', $days, ' AND status = 1' . ((!empty($cur_m) || !empty($prev_m)) ? ' AND currencyid = ' . self::main_currency($cur_m, $prev_m) : ''), 'SUM(amount)'),
                'value' => array_sum($cur_m), 'previous' => array_sum($prev_m),
                'display' => self::format_amounts($cur_m), 'previous_display' => self::format_amounts($prev_m),
                'delta' => self::delta_amounts($cur_m, $prev_m));
        }
        return $kpis;
    }

    /**
     * Direction A's opening line: a greeting for the time of day and what
     * needs the user today.
     *
     * @param array $attention attention()
     * @return array{0:string,1:string} greeting, summary
     */
    public static function greeting($attention) {
        $user = wp_get_current_user();
        $name = $user && $user->ID ? ($user->first_name !== '' ? $user->first_name : $user->display_name) : '';
        $hour = (int) current_time('G');
        /* translators: %s: first name */
        $hello = $hour < 12 ? __('Good morning, %s', 'wp-job-portal') : ($hour < 18 ? __('Good afternoon, %s', 'wp-job-portal') : __('Good evening, %s', 'wp-job-portal'));
        // The count is the total of the cards below it, so the two always agree;
        // the first card (most urgent, see attention()) is named.
        $total = (int) array_sum(array_column($attention['items'], 'count'));
        if ($total === 0) {
            $summary = __('Nothing needs you right now. Here is how the job board is doing.', 'wp-job-portal');
        } else {
            $first = $attention['items'][0];
            $first_label = function_exists('mb_strtolower') ? mb_strtolower($first['label']) : strtolower($first['label']);
            /* translators: 1: items waiting, 2: count of the most urgent card, 3: its label, e.g. applications not opened yet */
            $summary = sprintf(_n('%1$s item needs your attention · most urgent: %2$s %3$s.', '%1$s items need your attention · most urgent: %2$s %3$s.', $total, 'wp-job-portal'),
                number_format_i18n($total), number_format_i18n((int) $first['count']), $first_label);
        }
        return array(sprintf($hello, $name), $summary);
    }

    /**
     * One number per point of the range (day, week or month, as Platform
     * Growth uses), for the small trend lines under the key numbers.
     *
     * @return int[]|float[]
     */
    public static function series($table, $column, $days, $extra = '', $agg = 'COUNT(*)') {
        $db = wpjobportal::$_db;
        $unit = $days <= 30 ? 'day' : ($days <= 90 ? 'week' : 'month');
        $start = strtotime('-' . ($days - 1) . ' days', strtotime(current_time('Y-m-d')));
        if ($unit === 'week') {
            $start = strtotime('monday this week', $start);
        } elseif ($unit === 'month') {
            $start = strtotime(gmdate('Y-m-01', strtotime('-11 months', strtotime(current_time('Y-m-01')))));
        }
        $bucket = $unit === 'week' ? "DATE(DATE_SUB(`$column`, INTERVAL WEEKDAY(`$column`) DAY))" : ($unit === 'month' ? "DATE_FORMAT(`$column`, '%Y-%m-01')" : "DATE(`$column`)");
        $rows = (array) $db->get_results("SELECT " . $bucket . " AS d, " . $agg . " AS v FROM `" . $db->prefix . "wj_portal_" . $table . "`
            WHERE `$column` >= '" . esc_sql(gmdate('Y-m-d 00:00:00', $start)) . "'" . $extra . " GROUP BY d");
        $by = array_column($rows, 'v', 'd');
        $out = array();
        $step = $unit === 'day' ? '+1 day' : ($unit === 'week' ? '+1 week' : '+1 month');
        for ($t = $start; $t <= strtotime(current_time('Y-m-d')); $t = strtotime($step, $t)) {
            $out[] = isset($by[gmdate('Y-m-d', $t)]) ? (float) $by[gmdate('Y-m-d', $t)] : 0;
        }
        return $out;
    }

    /** An SVG polyline's points for a series, 100 x 34. */
    public static function spark_points($series) {
        $n = count($series);
        if ($n < 2) {
            return '0,32 100,32';
        }
        $max = max($series) ?: 1;
        $pts = array();
        foreach (array_values($series) as $i => $v) {
            $pts[] = round($i / ($n - 1) * 100, 1) . ',' . round(32 - ($v / $max) * 28, 1);
        }
        return implode(' ', $pts);
    }

    /**
     * Open jobs ranked by applications (with views and the share of viewers who
     * applied), and open jobs older than 7 days that nobody has applied to.
     *
     * @return array{best:array, none:array, none_total:int}
     */
    public static function job_performance() {
        $db = wpjobportal::$_db;
        $prefix = $db->prefix . 'wj_portal_';
        $closed = WPJOBPORTALincluder::getJSModel('jobstatus')->getClosedStatusIds();
        $open = "job.status = 1 AND job.startpublishing < DATE_ADD(CURDATE(), INTERVAL 1 DAY) AND job.stoppublishing >= CURDATE()"
            . ($closed ? " AND job.jobstatus NOT IN (" . implode(',', array_map('intval', $closed)) . ")" : '');
        $apps = "(SELECT COUNT(ja.id) FROM `" . $prefix . "jobapply` AS ja WHERE ja.jobid = job.id AND ja.status <> 4)";
        $best = (array) $db->get_results("SELECT job.id, job.title, job.hits, job.stoppublishing, " . $apps . " AS apps
            FROM `" . $prefix . "jobs` AS job WHERE " . $open . " ORDER BY apps DESC, job.hits DESC LIMIT 5");
        $none_where = $open . " AND job.created < DATE_SUB(NOW(), INTERVAL 7 DAY) AND " . $apps . " = 0";
        $none = (array) $db->get_results("SELECT job.id, job.title, job.hits, job.created FROM `" . $prefix . "jobs` AS job WHERE " . $none_where . " ORDER BY job.created ASC LIMIT 5");
        $none_total = (int) $db->get_var("SELECT COUNT(job.id) FROM `" . $prefix . "jobs` AS job WHERE " . $none_where);
        return array('best' => $best, 'none' => $none, 'none_total' => $none_total);
    }

    /**
     * Applications made in the range, by how far they got (the W-1 candidate
     * statuses; Recruit Suite stages when it is active). Each step counts the
     * applications that reached it or a later one; Not selected is apart.
     *
     * @param int $days
     * @return array{steps:array, not_selected:int, total:int}
     */
    public static function funnel($days) {
        $db = wpjobportal::$_db;
        $from = gmdate('Y-m-d H:i:s', strtotime(current_time('mysql') . ' -' . $days . ' days'));
        $rows = (array) $db->get_results($db->prepare("SELECT id, action_status FROM `" . $db->prefix . "wj_portal_jobapply`
            WHERE status <> 4 AND apply_date > %s ORDER BY id DESC LIMIT 5000", $from));
        $with_stages = has_filter('wpjobportal_application_stage_slug');
        $rank = array('submitted' => 0, 'review' => 1, 'interviewing' => 2, 'offer' => 3, 'hired' => 4);
        $reached = array_fill_keys(array_keys($rank), 0);
        $not_selected = 0;
        foreach ($rows as $row) {
            $key = WPJOBPORTAL_Application_Status::public_key($row->action_status, $with_stages ? WPJOBPORTAL_Application_Status::stage_slug($row->id) : '');
            if ($key === 'not_selected') {
                $not_selected++;
                $reached['submitted']++;
                continue;
            }
            foreach ($rank as $step => $r) {
                if ($rank[$key] >= $r) {
                    $reached[$step]++;
                }
            }
        }
        $labels = WPJOBPORTAL_Application_Status::public_labels();
        $labels['submitted'] = __('Applied', 'wp-job-portal');
        $steps = array();
        foreach ($rank as $step => $r) {
            $steps[] = array('key' => $step, 'label' => $labels[$step], 'count' => $reached[$step]);
        }
        return array('steps' => $steps, 'not_selected' => $not_selected, 'total' => count($rows));
    }

    /**
     * System status in counts (cached for an hour: the checks take a second or
     * two) plus errors logged in the last 7 days.
     *
     * @return array{critical:int, warning:int, errors:int}
     */
    public static function site_health() {
        $health = get_transient(self::HEALTH_TRANSIENT);
        if (!is_array($health)) {
            $health = array('critical' => 0, 'warning' => 0);
            if (class_exists('WPJOBPORTAL_Diagnostics_Service')) {
                $status = WPJOBPORTAL_Diagnostics_Service::get_status();
                foreach (array('environment', 'operational_readiness', 'database', 'request_security', 'privacy_payments', 'scheduled_tasks') as $group) {
                    foreach (isset($status[$group]) ? (array) $status[$group] : array() as $check) {
                        if (is_array($check) && isset($check['status'], $health[$check['status']])) {
                            $health[$check['status']]++;
                        }
                    }
                }
            }
            set_transient(self::HEALTH_TRANSIENT, $health, HOUR_IN_SECONDS);
        }
        $db = wpjobportal::$_db;
        $table = $db->prefix . 'wj_portal_system_errors';
        $health['errors'] = $db->get_var($db->prepare('SHOW TABLES LIKE %s', $table)) ? (int) $db->get_var("SELECT COUNT(*) FROM `" . $table . "` WHERE created > DATE_SUB(NOW(), INTERVAL 7 DAY)") : 0;
        return $health;
    }

    /**
     * Money at a glance: paid this month and last month, payments not
     * completed, active subscriptions and renewals due in 7 days.
     *
     * @return array
     */
    public static function revenue() {
        $db = wpjobportal::$_db;
        $p = $db->prefix . 'wj_portal_';
        $month = current_time('Y-m-01 00:00:00');
        $last = gmdate('Y-m-01 00:00:00', strtotime('-1 month', strtotime($month)));
        $this_m = self::amounts("status = 1 AND created >= '" . esc_sql($month) . "'");
        $last_m = self::amounts("status = 1 AND created >= '" . esc_sql($last) . "' AND created < '" . esc_sql($month) . "'");
        $pending = self::amounts('status = 0');
        $pending_n = (int) $db->get_var("SELECT COUNT(id) FROM `{$p}invoices` WHERE status = 0");
        $subs = $db->get_row("SELECT SUM(status = 1 AND nextbillingdate >= NOW()) AS active,
                SUM(status = 1 AND nextbillingdate BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)) AS renewing
            FROM `{$p}subscriptions`");
        return array(
            'this_month' => self::format_amounts($this_m),
            'last_month' => self::format_amounts($last_m),
            'last_month_empty' => empty($last_m),
            'delta' => self::delta_amounts($this_m, $last_m),
            'pending' => self::format_amounts($pending),
            'pending_n' => $pending_n,
            'active' => $subs ? (int) $subs->active : 0,
            'renewing' => $subs ? (int) $subs->renewing : 0,
        );
    }

    /**
     * Download the numbers for the chosen range as CSV: the key numbers with
     * the previous period, the growth series and the funnel.
     */
    public static function export() {
        if (!current_user_can('wpjobportal') || !wp_verify_nonce((string) WPJOBPORTALrequest::getVar('_wpnonce', 'get', ''), self::EXPORT_ACTION)) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'wp-job-portal'));
        }
        $days = self::range();
        nocache_headers();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="job-board-' . $days . '-days-' . current_time('Y-m-d') . '.csv"');
        $rows = array();
        /* translators: %s: range, e.g. 30 days */
        $rows[] = (array(sprintf(__('Job board summary, last %s', 'wp-job-portal'), self::ranges()[$days]), current_time('Y-m-d H:i')));
        $rows[] = (array());
        $rows[] = (array(__('Key number', 'wp-job-portal'), __('This period', 'wp-job-portal'), __('Previous period', 'wp-job-portal'), __('Change', 'wp-job-portal')));
        foreach (self::kpis($days) as $kpi) {
            $delta = self::delta($kpi['value'], $kpi['previous']);
            if (!empty($kpi['money'])) {
                $rows[] = (array($kpi['label'], $kpi['display'], $kpi['previous_display'], $kpi['delta'][0]));
                continue;
            }
            $rows[] = (array($kpi['label'], $kpi['value'], $kpi['previous'], $delta[0]));
        }
        $rows[] = (array());
        WPJOBPORTALincluder::getJSModel('wpjobportal')->getPlatformGrowthData();
        $growth = isset(wpjobportal::$_data['platform_growth']) ? wpjobportal::$_data['platform_growth'] : array('labels' => array(), 'jobs' => array(), 'applies' => array());
        $rows[] = (array(__('Period', 'wp-job-portal'), __('New jobs', 'wp-job-portal'), __('Applications', 'wp-job-portal')));
        foreach ($growth['labels'] as $i => $label) {
            $rows[] = (array($label, $growth['jobs'][$i], $growth['applies'][$i]));
        }
        $rows[] = (array());
        $funnel = self::funnel($days);
        $rows[] = (array(__('Hiring funnel', 'wp-job-portal'), __('Applications', 'wp-job-portal')));
        foreach ($funnel['steps'] as $step) {
            $rows[] = (array($step['label'], $step['count']));
        }
        $rows[] = (array(__('Not selected', 'wp-job-portal'), $funnel['not_selected']));
        // Built in memory and sent once: no file handle on php://output.
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSV download, not HTML; every field is quoted by csv_line().
        echo "\xEF\xBB\xBF" . implode('', array_map(array(__CLASS__, 'csv_line'), $rows));
        exit;
    }

    /** One CSV line, quoted the way fputcsv() quotes it. */
    private static function csv_line($fields) {
        $out = array();
        foreach ((array) $fields as $field) {
            $field = (string) $field;
            if ($field !== '' && preg_match('/[",\r\n\t \\\\]/', $field)) {
                $field = '"' . str_replace('"', '""', $field) . '"';
            }
            $out[] = $field;
        }
        return implode(',', $out) . "\n";
    }

    /** Which day heading an event goes under: today, yesterday, this week or earlier. */
    public static function day_group($time) {
        $day = gmdate('Y-m-d', strtotime($time));
        $today = current_time('Y-m-d');
        if ($day === $today) {
            return array('today', __('Today', 'wp-job-portal'));
        }
        if ($day === gmdate('Y-m-d', strtotime($today . ' -1 day'))) {
            return array('yesterday', __('Yesterday', 'wp-job-portal'));
        }
        if ($day > gmdate('Y-m-d', strtotime($today . ' -7 days'))) {
            return array('week', __('This week', 'wp-job-portal'));
        }
        return array('earlier', __('Earlier', 'wp-job-portal'));
    }

    /** Activity feed filters: type => label. */
    public static function activity_types() {
        return array(
            'application' => __('Applications', 'wp-job-portal'),
            'job' => __('Jobs', 'wp-job-portal'),
            'resume' => __('Resumes', 'wp-job-portal'),
            'company' => __('Companies', 'wp-job-portal'),
            'member' => __('New members', 'wp-job-portal'),
        );
    }

    /**
     * One feed of what happened lately - applications, jobs, resumes, companies
     * and new members - newest first (up to 8 of each, 25 in all).
     *
     * @return array[] type, text, url, time (mysql)
     */
    public static function activity_feed() {
        $db = wpjobportal::$_db;
        $p = $db->prefix . 'wj_portal_';
        $events = array();
        $name = function ($first, $last, $fallback) {
            $n = trim($first . ' ' . $last);
            return $n !== '' ? $n : $fallback;
        };
        foreach ((array) $db->get_results("SELECT ja.id, ja.jobid, ja.apply_date, j.title, r.first_name, r.last_name FROM `{$p}jobapply` AS ja
            JOIN `{$p}jobs` AS j ON j.id = ja.jobid LEFT JOIN `{$p}resume` AS r ON r.id = ja.cvid
            WHERE ja.status <> 4 ORDER BY ja.apply_date DESC LIMIT 8") as $r) {
            /* translators: 1: candidate, 2: job title */
            $events[] = array('type' => 'application', 'time' => $r->apply_date, 'text' => sprintf(__('%1$s applied for %2$s', 'wp-job-portal'), $name($r->first_name, $r->last_name, __('A candidate', 'wp-job-portal')), $r->title),
                /* translators: %s: job title */
                'title' => $name($r->first_name, $r->last_name, __('A candidate', 'wp-job-portal')), 'detail' => sprintf(__('Applied for %s', 'wp-job-portal'), $r->title),
                'url' => admin_url('admin.php?page=wpjobportal_jobapply&wpjobportallt=jobappliedresume&jobid=' . (int) $r->jobid));
        }
        foreach ((array) $db->get_results("SELECT j.id, j.title, j.created, c.name FROM `{$p}jobs` AS j LEFT JOIN `{$p}companies` AS c ON c.id = j.companyid ORDER BY j.created DESC LIMIT 8") as $r) {
            /* translators: 1: company, 2: job title */
            $events[] = array('type' => 'job', 'time' => $r->created, 'text' => $r->name ? sprintf(__('%1$s posted %2$s', 'wp-job-portal'), $r->name, $r->title) : sprintf(__('New job: %s', 'wp-job-portal'), $r->title),
                /* translators: %s: company name */
                'title' => $r->title, 'detail' => $r->name ? sprintf(__('New job · %s', 'wp-job-portal'), $r->name) : __('New job', 'wp-job-portal'),
                'url' => admin_url('admin.php?page=wpjobportal_job&wpjobportallt=formjob&wpjobportalid=' . (int) $r->id));
        }
        foreach ((array) $db->get_results("SELECT id, first_name, last_name, application_title, created FROM `{$p}resume` WHERE quick_apply <> 1 OR quick_apply IS NULL ORDER BY created DESC LIMIT 8") as $r) {
            /* translators: 1: person, 2: resume title */
            $events[] = array('type' => 'resume', 'time' => $r->created, 'text' => sprintf(__('New resume: %1$s · %2$s', 'wp-job-portal'), $name($r->first_name, $r->last_name, __('Unnamed', 'wp-job-portal')), $r->application_title),
                /* translators: %s: resume title */
                'title' => $name($r->first_name, $r->last_name, __('Unnamed', 'wp-job-portal')), 'detail' => trim((string) $r->application_title) !== '' ? sprintf(__('New resume · %s', 'wp-job-portal'), $r->application_title) : __('New resume', 'wp-job-portal'),
                'url' => admin_url('admin.php?page=wpjobportal_resume&wpjobportallt=viewresume&wpjobportalid=' . (int) $r->id));
        }
        foreach ((array) $db->get_results("SELECT id, name, created FROM `{$p}companies` ORDER BY created DESC LIMIT 8") as $r) {
            /* translators: %s: company name */
            $events[] = array('type' => 'company', 'time' => $r->created, 'text' => sprintf(__('New company: %s', 'wp-job-portal'), $r->name),
                'title' => $r->name, 'detail' => __('New company', 'wp-job-portal'),
                'url' => admin_url('admin.php?page=wpjobportal_company&wpjobportallt=formcompany&wpjobportalid=' . (int) $r->id));
        }
        foreach ((array) $db->get_results("SELECT id, first_name, last_name, roleid, created FROM `{$p}users` WHERE roleid IN (1, 2) ORDER BY created DESC LIMIT 8") as $r) {
            $role = (int) $r->roleid === 1 ? __('an employer', 'wp-job-portal') : __('a job seeker', 'wp-job-portal');
            /* translators: 1: person, 2: an employer / a job seeker */
            $events[] = array('type' => 'member', 'time' => $r->created, 'text' => sprintf(__('%1$s joined as %2$s', 'wp-job-portal'), $name($r->first_name, $r->last_name, __('Someone', 'wp-job-portal')), $role),
                /* translators: %s: an employer / a job seeker */
                'title' => $name($r->first_name, $r->last_name, __('Someone', 'wp-job-portal')), 'detail' => sprintf(__('Joined as %s', 'wp-job-portal'), $role),
                'url' => admin_url('admin.php?page=wpjobportal_user&wpjobportallt=userdetail&id=' . (int) $r->id));
        }
        usort($events, function ($a, $b) { return strcmp((string) $b['time'], (string) $a['time']); });
        return array_slice($events, 0, 25);
    }

    /**
     * The change against the previous period as text and a tone, e.g. "+12%" up.
     *
     * @return array{0:string,1:string} text, tone (up|down|flat)
     */
    public static function delta($cur, $prev, $counts = true) {
        // Small counts read better as a plain difference: 4 -> 0 is "-4", not "-100%",
        // and 1 -> 24 is "+23", not "+2,300%". From 20 up the percentage means something.
        if ($counts && $prev < 20) {
            $diff = (int) round($cur - $prev);
            if ($diff === 0) {
                return array('0', 'flat');
            }
            return array(($diff > 0 ? '+' : '−') . number_format_i18n(abs($diff)), $diff > 0 ? 'up' : 'down');
        }
        if ($prev <= 0) {
            return $cur > 0 ? array(__('New', 'wp-job-portal'), 'up') : array('—', 'flat');
        }
        $pct = (int) round(($cur - $prev) / $prev * 100);
        if ($pct === 0) {
            return array('0%', 'flat');
        }
        return array(($pct > 0 ? '+' : '−') . number_format_i18n(abs($pct)) . '%', $pct > 0 ? 'up' : 'down');
    }

}
