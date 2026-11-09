<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * One runner for changes to existing records during an update (D-13).
 *
 * The .sql migrations stay for settings and schema: a handful of rows, done in
 * one request. Anything that walks a whole table (job, company or resume
 * records) goes through here instead, so a large site on shared hosting does
 * not time out half way:
 *
 * - Nothing runs until an administrator confirms they have a backup.
 * - Work is done a fixed number of rows at a time. With the admin page open,
 *   the browser asks for the next step and shows a progress bar; once started,
 *   WP-Cron carries on every minute when nobody is watching.
 * - Each job keeps a cursor (the last id it finished), saved after every step,
 *   so a timeout or a closed tab picks up where it stopped.
 * - Each finished job is written to the migration ledger and never runs again;
 *   a job with nothing to change is marked done without asking anyone.
 *
 * Jobs come from jobs() and the 'wpjobportal_batch_jobs' filter. A job is
 * ['label', 'count' => callable(): int, 'step' => callable($after_id, $limit):
 * ['last_id' => int, 'seen' => int, 'changed' => int]]; 'seen' below $limit
 * means the end of the table.
 */
class WPJOBPORTAL_Batch_Runner {

    const STATE_OPTION = 'wpjobportal_batch_runner';
    const CRON_HOOK = 'wpjobportal_batch_runner_cron';
    const START_ACTION = 'wpjobportal_batch_start';
    const STEP_ACTION = 'wpjobportal_batch_step';
    const RETRY_ACTION = 'wpjobportal_batch_retry';
    const LOCK = 'batch';

    public static function register() {
        add_action('admin_init', array(__CLASS__, 'maybe_prepare'), 20);
        add_action('admin_notices', array(__CLASS__, 'render_notice'));
        add_action('wp_ajax_' . self::START_ACTION, array(__CLASS__, 'ajax_start'));
        add_action('wp_ajax_' . self::STEP_ACTION, array(__CLASS__, 'ajax_step'));
        add_action('wp_ajax_' . self::RETRY_ACTION, array(__CLASS__, 'ajax_retry'));
        add_action(self::CRON_HOOK, array(__CLASS__, 'cron'));
    }

    private static function allowed() {
        return current_user_can('manage_options') || current_user_can('wpjobportal');
    }

    /** Rows per step. */
    private static function batch_size() {
        return max(10, min(1000, (int) apply_filters('wpjobportal_batch_size', 100)));
    }

    /**
     * The jobs, by id.
     *
     * @return array
     */
    public static function jobs() {
        $jobs = array(
            // D-9: 2.7 kept non-Latin letters, spaces and symbols in slugs, which
            // this site's permalinks answer with 404. The id after the last
            // hyphen is what finds the record, so old links keep working.
            'slugs-jobs' => self::slug_job(__('Job web addresses', 'wp-job-portal'), 'wj_portal_jobs', '`title`', 'job'),
            'slugs-companies' => self::slug_job(__('Company web addresses', 'wp-job-portal'), 'wj_portal_companies', '`name`', 'company'),
            'slugs-resumes' => self::slug_job(__('Resume web addresses', 'wp-job-portal'), 'wj_portal_resume', "COALESCE(NULLIF(`application_title`, ''), CONCAT_WS(' ', `first_name`, `last_name`))", 'resume'),
        );
        $jobs = apply_filters('wpjobportal_batch_jobs', $jobs);
        foreach ($jobs as $id => $job) {
            if (!is_array($job) || !isset($job['label'], $job['count'], $job['step']) || !is_callable($job['count']) || !is_callable($job['step'])) {
                unset($jobs[$id]);
            }
        }
        return $jobs;
    }

    /**
     * A job that rewrites slugs that are not plain a-z, 0-9 and single inner hyphens.
     *
     * @param string $label
     * @param string $table  Without the prefix.
     * @param string $source SQL for the text the slug is made from.
     * @param string $fallback Word used when the text has no usable letters.
     * @return array
     */
    private static function slug_job($label, $table, $source, $fallback) {
        global $wpdb;
        $table = $wpdb->prefix . $table;
        // BINARY: the default collation would let capitals through.
        $bad = "(`alias` IS NULL OR `alias` = '' OR CAST(`alias` AS BINARY) REGEXP BINARY '[^a-z0-9-]' OR `alias` LIKE '-%' OR `alias` LIKE '%-')";
        return array(
            'label' => $label,
            'count' => function () use ($table, $bad) {
                global $wpdb;
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $bad is the fixed condition above, not input
                return (int) wpjobportaldb_direct::get_var($wpdb->prepare("SELECT COUNT(*) FROM %i WHERE {$bad}", $table));
            },
            'step' => function ($after_id, $limit) use ($table, $bad, $source, $fallback) {
                global $wpdb;
                $rows = wpjobportaldb_direct::get_results($wpdb->prepare(
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $source and $bad are fixed SQL written in this class, not input
                    "SELECT `id`, `alias`, {$source} AS source FROM %i WHERE `id` > %d AND {$bad} ORDER BY `id` ASC LIMIT %d",
                    $table,
                    $after_id,
                    $limit
                ));
                if ($rows === null && $wpdb->last_error) {
                    throw new RuntimeException(esc_html($wpdb->last_error));
                }
                $last = $after_id;
                $changed = 0;
                foreach ((array) $rows as $row) {
                    $last = (int) $row->id;
                    $alias = wpjobportal::$_common->stringToAlias((string) $row->source, $fallback);
                    if ($alias === '' || $alias === $row->alias) {
                        continue;
                    }
                    // Only if nobody saved the record since it was read.
                    $result = wpjobportaldb_direct::query($wpdb->prepare(
                        "UPDATE %i SET `alias` = %s WHERE `id` = %d AND `alias` <=> %s",
                        $table,
                        $alias,
                        $row->id,
                        $row->alias
                    ));
                    if ($result === false) {
                        throw new RuntimeException(esc_html($wpdb->last_error));
                    }
                    $changed += (int) $result;
                }
                return array('last_id' => $last, 'seen' => count((array) $rows), 'changed' => $changed);
            },
        );
    }

    /** @return array */
    public static function get_state() {
        $state = get_option(self::STATE_OPTION, array());
        if (!is_array($state)) {
            $state = array();
        }
        return $state + array('jobs' => array(), 'confirmed_by' => 0, 'confirmed_at' => '', 'log' => array());
    }

    private static function save_state($state) {
        update_option(self::STATE_OPTION, $state, false);
    }

    /**
     * Once the .sql migrations are done: count the work for any job not yet
     * done, and mark the ones with nothing to do as done.
     *
     * @return void
     */
    public static function maybe_prepare() {
        if (!self::allowed() || wp_doing_ajax() || !class_exists('WPJOBPORTAL_Migration_Service')) {
            return;
        }
        $installed = (string) wpjobportal::$_config->getConfigValue('versioncode');
        if ((int) str_replace('.', '', $installed) < (int) wpjobportal::$_currentversion) {
            return; // the .sql run comes first
        }
        $state = self::get_state();
        $jobs = self::jobs();
        $new = array_diff_key($jobs, $state['jobs']);
        if (!$new) {
            return;
        }
        foreach ($new as $id => $job) {
            $entry = WPJOBPORTAL_Migration_Service::get_batch_entry($id);
            if ($entry && $entry->status === 'success') {
                $state['jobs'][$id] = array('status' => 'done', 'total' => 0, 'done' => 0, 'changed' => 0, 'cursor' => 0, 'error' => '');
                continue;
            }
            try {
                $total = (int) call_user_func($job['count']);
            } catch (Throwable $e) {
                $total = -1;
            }
            $state['jobs'][$id] = array('status' => $total === 0 ? 'done' : 'pending', 'total' => max(0, $total), 'done' => 0, 'changed' => 0, 'cursor' => 0, 'error' => '');
            if ($total === 0) {
                WPJOBPORTAL_Migration_Service::record_batch($id, 'success', '');
            }
        }
        self::save_state($state);
    }

    /** Ids of jobs still to run (pending or running), in order. */
    private static function open_jobs($state) {
        $open = array();
        foreach ($state['jobs'] as $id => $job) {
            if (in_array($job['status'], array('pending', 'running'), true)) {
                $open[] = $id;
            }
        }
        return $open;
    }

    private static function has_failed($state) {
        foreach ($state['jobs'] as $job) {
            if ($job['status'] === 'failed') {
                return true;
            }
        }
        return false;
    }

    /**
     * Work for up to $seconds, one step at a time.
     *
     * @param int $seconds
     * @return array The state afterwards; 'busy' when another request holds the lock.
     */
    public static function run_for($seconds) {
        $result = WPJOBPORTAL_Migration_Service::with_lock(self::LOCK, function () use ($seconds) {
            return self::run_unlocked($seconds);
        }, null, 120);
        if ($result === null) {
            $state = self::get_state();
            $state['busy'] = true;
            return $state;
        }
        return $result;
    }

    private static function run_unlocked($seconds) {
        $start = microtime(true);
        $jobs = self::jobs();
        $state = self::get_state();
        if (!$state['confirmed_by']) {
            return $state;
        }
        $limit = self::batch_size();
        $steps = 0;
        foreach (self::open_jobs($state) as $id) {
            if (!isset($jobs[$id])) {
                $state['jobs'][$id]['status'] = 'done'; // its add-on went away
                continue;
            }
            $job = &$state['jobs'][$id];
            if ($job['status'] === 'pending') {
                $job['status'] = 'running';
                WPJOBPORTAL_Migration_Service::record_batch($id, 'running');
            }
            // At least one step per run, however short the time given.
            while ($steps === 0 || (microtime(true) - $start) < $seconds) {
                $steps++;
                try {
                    $step = call_user_func($jobs[$id]['step'], (int) $job['cursor'], $limit);
                } catch (Throwable $e) {
                    $job['status'] = 'failed';
                    $job['error'] = WPJOBPORTAL_Redactor::redact($e->getMessage());
                    WPJOBPORTAL_Migration_Service::record_batch($id, 'failed', $e->getMessage());
                    WPJOBPORTAL_Audit_Service::record('batch_migration', 'failed', array('job' => $id, 'after_id' => (int) $job['cursor']));
                    self::log($state, $id, 'failed', 0, 0);
                    break;
                }
                $job['cursor'] = max((int) $job['cursor'], (int) $step['last_id']);
                $job['done'] += (int) $step['seen'];
                $job['changed'] += (int) $step['changed'];
                self::log($state, $id, 'step', (int) $step['seen'], (int) $step['changed']);
                if ((int) $step['seen'] < $limit) {
                    $job['status'] = 'done';
                    $job['done'] = max($job['done'], $job['total']);
                    WPJOBPORTAL_Migration_Service::record_batch($id, 'success');
                    WPJOBPORTAL_Audit_Service::record('batch_migration', 'success', array('job' => $id, 'changed' => (int) $job['changed']));
                    self::save_state($state);
                    break;
                }
                // Saved after every step: a timeout resumes from here.
                self::save_state($state);
            }
            unset($job);
            if ((microtime(true) - $start) >= $seconds || $state['jobs'][$id]['status'] === 'failed') {
                break;
            }
        }
        if (!self::open_jobs($state) && !self::has_failed($state) && empty($state['finished_at'])) {
            $state['finished_at'] = current_time('mysql', true);
        }
        self::save_state($state);
        return $state;
    }

    private static function log(&$state, $id, $what, $seen, $changed) {
        array_unshift($state['log'], array('time' => current_time('mysql', true), 'job' => $id, 'what' => $what, 'seen' => $seen, 'changed' => $changed));
        $state['log'] = array_slice($state['log'], 0, 50);
    }

    /** Carry on in the background; reschedules itself while work is left. */
    public static function cron() {
        $state = self::run_for(20);
        self::schedule($state);
    }

    private static function schedule($state) {
        if ($state['confirmed_by'] && self::open_jobs($state) && !wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_single_event(time() + 60, self::CRON_HOOK);
        }
    }

    /**
     * Progress for the page: overall percent and one line per open job.
     *
     * @return array
     */
    public static function summary($state) {
        $jobs = self::jobs();
        $total = 0;
        $done = 0;
        $lines = array();
        foreach ($state['jobs'] as $id => $job) {
            if ($job['status'] === 'done' && empty($job['total'])) {
                continue;
            }
            $total += max(1, (int) $job['total']);
            $done += min(max(1, (int) $job['total']), (int) $job['done']);
            $lines[] = array(
                'label' => isset($jobs[$id]) ? $jobs[$id]['label'] : $id,
                'status' => $job['status'],
                'done' => (int) $job['done'],
                'total' => (int) $job['total'],
                'error' => (string) $job['error'],
            );
        }
        return array(
            'percent' => $total ? (int) floor($done * 100 / $total) : 100,
            'open' => count(self::open_jobs($state)),
            'failed' => self::has_failed($state),
            'busy' => !empty($state['busy']),
            'lines' => $lines,
        );
    }

    private static function check_ajax() {
        if (!self::allowed() || !check_ajax_referer(self::STEP_ACTION, 'nonce', false)) {
            wp_send_json_error(array('message' => __('You are not allowed to do that.', 'wp-job-portal')), 403);
        }
    }

    public static function ajax_start() {
        self::check_ajax();
        if ((string) WPJOBPORTALrequest::getVar('backup', 'post', '') !== '1') {
            wp_send_json_error(array('message' => __('Please confirm you have a backup first.', 'wp-job-portal')), 400);
        }
        $state = self::get_state();
        if (!$state['confirmed_by']) {
            $state['confirmed_by'] = get_current_user_id();
            $state['confirmed_at'] = current_time('mysql', true);
            self::save_state($state);
            WPJOBPORTAL_Audit_Service::record('batch_migration_backup_confirmed', 'info', array('user' => get_current_user_id()));
        }
        self::schedule($state);
        wp_send_json_success(self::summary($state));
    }

    public static function ajax_step() {
        self::check_ajax();
        $state = self::run_for(4);
        self::schedule($state);
        wp_send_json_success(self::summary($state));
    }

    /** A failed job goes back to the queue, from its last cursor. */
    public static function ajax_retry() {
        self::check_ajax();
        $state = self::get_state();
        foreach ($state['jobs'] as $id => $job) {
            if ($job['status'] === 'failed') {
                $state['jobs'][$id]['status'] = 'pending';
                $state['jobs'][$id]['error'] = '';
            }
        }
        unset($state['finished_at']);
        self::save_state($state);
        self::schedule($state);
        wp_send_json_success(self::summary($state));
    }

    /**
     * The backup question, then the progress bar, on the plugin's pages and Plugins.
     *
     * @return void
     */
    public static function render_notice() {
        if (!self::allowed()) {
            return;
        }
        $page = sanitize_key((string) WPJOBPORTALrequest::getVar('page', 'get', ''));
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (strpos($page, 'wpjobportal') !== 0 && !($screen && $screen->id === 'plugins')) {
            return;
        }
        $state = self::get_state();
        if (!self::open_jobs($state) && !self::has_failed($state)) {
            return;
        }
        $summary = self::summary($state);
        $confirmed = (bool) $state['confirmed_by'];
        ?>
        <div class="notice notice-warning wpjobportal-batch-notice" id="wpjobportal-batch" data-confirmed="<?php echo $confirmed ? '1' : '0'; ?>">
            <p>
                <strong><?php echo esc_html__('WP Job Portal needs to update some existing records.', 'wp-job-portal'); ?></strong>
                <?php echo esc_html__('This runs in small steps, so it is safe on large sites. Your site keeps working while it runs.', 'wp-job-portal'); ?>
            </p>
            <ul style="margin:4px 0 8px 18px;list-style:disc" class="wpjobportal-batch-lines">
                <?php foreach ($summary['lines'] as $line) { ?>
                    <li><?php
                        echo esc_html($line['label']) . ': ';
                        /* translators: 1: records checked, 2: records to check */
                        echo esc_html(sprintf(__('%1$s of %2$s', 'wp-job-portal'), number_format_i18n($line['done']), number_format_i18n($line['total'])));
                        if ($line['status'] === 'failed') {
                            echo ' — ' . esc_html__('stopped', 'wp-job-portal') . ($line['error'] !== '' ? ': ' . esc_html($line['error']) : '');
                        }
                    ?></li>
                <?php } ?>
            </ul>
            <div class="wpjobportal-batch-bar" style="height:10px;max-width:420px;background:#e2e8f0;border-radius:6px;overflow:hidden;margin:4px 0 8px;<?php echo $confirmed ? '' : 'display:none;'; ?>">
                <span style="display:block;height:100%;width:<?php echo (int) $summary['percent']; ?>%;background:#4f46e5;transition:width .3s"></span>
            </div>
            <p class="wpjobportal-batch-status" aria-live="polite" style="margin:0 0 6px">
                <?php
                if ($summary['failed']) {
                    echo esc_html__('A step failed. Nothing was lost: it stopped at the last record that finished. Try again, or open System status.', 'wp-job-portal');
                } elseif ($confirmed) {
                    /* translators: %s: percent */
                    echo esc_html(sprintf(__('%s%% done. It carries on in the background if you leave this page.', 'wp-job-portal'), $summary['percent']));
                }
                ?>
            </p>
            <?php if (!$confirmed) { ?>
                <p>
                    <label><input type="checkbox" class="wpjobportal-batch-backup"> <?php echo esc_html__('I have a recent backup of the database', 'wp-job-portal'); ?></label>
                </p>
                <p><button type="button" class="button button-primary wpjobportal-batch-start" disabled><?php echo esc_html__('Start the update', 'wp-job-portal'); ?></button></p>
            <?php } elseif ($summary['failed']) { ?>
                <p>
                    <button type="button" class="button button-primary wpjobportal-batch-retry"><?php echo esc_html__('Try again', 'wp-job-portal'); ?></button>
                    <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_systemstatus')); ?>"><?php echo esc_html__('System status', 'wp-job-portal'); ?></a>
                </p>
            <?php } ?>
        </div>
        <script>
        (function ($) {
            var box = $('#wpjobportal-batch'), url = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>, nonce = <?php echo wp_json_encode(wp_create_nonce(self::STEP_ACTION)); ?>;
            var txt = {
                done: <?php echo wp_json_encode(__('All records are up to date.', 'wp-job-portal')); ?>,
                running: <?php echo wp_json_encode(/* translators: %s: percentage of records done */ __('%s%% done. It carries on in the background if you leave this page.', 'wp-job-portal')); ?>,
                busy: <?php echo wp_json_encode(__('Another request is working on it; checking again shortly.', 'wp-job-portal')); ?>,
                failed: <?php echo wp_json_encode(__('A step failed. Nothing was lost: it stopped at the last record that finished. Reload the page to try again.', 'wp-job-portal')); ?>,
                error: <?php echo wp_json_encode(__('The server did not answer; trying again shortly.', 'wp-job-portal')); ?>,
                /* translators: 1: records checked, 2: records to check */
                of: <?php echo wp_json_encode(__('%1$s of %2$s', 'wp-job-portal')); ?>,
                stopped: <?php echo wp_json_encode(__('stopped', 'wp-job-portal')); ?>
            };
            // The lines were drawn once by PHP; redraw them so the counts follow the bar.
            function lines(list) {
                var ul = box.find('.wpjobportal-batch-lines').empty();
                $.each(list || [], function (i, l) {
                    var t = l.label + ': ' + txt.of.replace('%1$s', Number(l.done).toLocaleString()).replace('%2$s', Number(l.total).toLocaleString());
                    if (l.status === 'failed') { t += ' — ' + txt.stopped + (l.error ? ': ' + l.error : ''); }
                    ul.append($('<li>').text(t));
                });
            }
            function show(s) {
                box.find('.wpjobportal-batch-bar').show().find('span').css('width', s.percent + '%');
                lines(s.lines);
                var status = box.find('.wpjobportal-batch-status');
                if (s.failed) { status.text(txt.failed); return false; }
                if (!s.open) { status.text(txt.done); box.removeClass('notice-warning').addClass('notice-success'); return false; }
                status.text(s.busy ? txt.busy : txt.running.replace('%s%%', s.percent + '%'));
                return true;
            }
            function step() {
                $.post(url, {action: <?php echo wp_json_encode(self::STEP_ACTION); ?>, nonce: nonce}).done(function (r) {
                    if (r && r.success && show(r.data)) { setTimeout(step, r.data.busy ? 5000 : 300); }
                }).fail(function () { box.find('.wpjobportal-batch-status').text(txt.error); setTimeout(step, 10000); });
            }
            box.on('change', '.wpjobportal-batch-backup', function () { box.find('.wpjobportal-batch-start').prop('disabled', !this.checked); });
            box.on('click', '.wpjobportal-batch-start', function () {
                var b = $(this).prop('disabled', true);
                $.post(url, {action: <?php echo wp_json_encode(self::START_ACTION); ?>, nonce: nonce, backup: '1'}).done(function (r) {
                    if (r && r.success) { b.closest('p').prev('p').remove(); b.closest('p').remove(); box.attr('data-confirmed', '1'); show(r.data); step(); }
                    else { b.prop('disabled', false); }
                });
            });
            box.on('click', '.wpjobportal-batch-retry', function () {
                var b = $(this).closest('p');
                $.post(url, {action: <?php echo wp_json_encode(self::RETRY_ACTION); ?>, nonce: nonce}).done(function (r) {
                    if (r && r.success) { b.remove(); show(r.data); step(); }
                });
            });
            if (box.attr('data-confirmed') === '1' && !box.find('.wpjobportal-batch-retry').length) { step(); }
        })(jQuery);
        </script>
        <?php
    }
}
