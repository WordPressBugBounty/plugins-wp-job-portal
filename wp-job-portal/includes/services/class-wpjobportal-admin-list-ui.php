<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * The parts every admin list shares in the redesign (design 4), with the Jobs
 * list as the reference: title row, status chips, sort list, pager and empty
 * state, plus the link builder that keeps the active filters.
 *
 * A list keeps its state in plain query values, so each part builds its links
 * from the page's current values and changes only what it is about.
 */
class WPJOBPORTAL_Admin_List_UI {

    /**
     * admin.php link for this list with the current filters, changed by $args.
     * Empty values are dropped and the page number resets unless given.
     *
     * @param string $page    The list's page slug.
     * @param array  $current The list's current filter values.
     * @param array  $args    Values to change (null removes one).
     * @return string
     */
    public static function url($page, $current, $args = array()) {
        $wpjobportal_query = array_merge((array) $current, array('pagenum' => null), $args);
        // An unset filter is null, '' or 0 (ids); the string '0' is a real value (an approval status).
        $wpjobportal_query = array_filter($wpjobportal_query, function ($value) {
            return $value !== null && $value !== '' && $value !== 0;
        });
        return add_query_arg(array_map('rawurlencode', array('page' => $page) + $wpjobportal_query), admin_url('admin.php'));
    }

    /**
     * Title row: heading, a short count, the ⋯ menu of related pages and the
     * green Add New button.
     *
     * @param string $title
     * @param string $count_text
     * @param array  $more  [label => url] for the ⋯ menu.
     * @param array  $add   ['label' => .., 'url' => ..] or empty.
     */
    public static function title_row($title, $count_text, $more = array(), $add = array()) {
        ?>
        <div class="wjpl-head">
            <h1><?php echo esc_html($title); ?></h1>
            <?php if ($count_text !== '') : ?><span class="wjpl-count"><?php
                // The form note's "*" is shown in the same red as the * on each required field.
                if ($count_text === __('Fields marked * are required.', 'wp-job-portal')) {
                    echo wp_kses(str_replace('*', '<span class="wjpl-req">*</span>', esc_html($count_text)), array('span' => array('class' => array())));
                } else {
                    echo esc_html($count_text);
                }
            ?></span><?php endif; ?>
            <div class="wjpl-acts">
                <?php if (!empty($more)) : ?>
                    <details class="wjpl-more">
                        <summary class="wjpl-iconbtn" aria-label="<?php echo esc_attr(__('More pages', 'wp-job-portal')); ?>" title="<?php echo esc_attr(__('More', 'wp-job-portal')); ?>">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="5" cy="12" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="19" cy="12" r="1.8"/></svg>
                        </summary>
                        <div class="wjpl-menu" role="menu">
                            <?php foreach ($more as $wpjobportal_label => $wpjobportal_url) : ?>
                                <a role="menuitem" href="<?php echo esc_url($wpjobportal_url); ?>"><?php echo esc_html($wpjobportal_label); ?></a>
                            <?php endforeach; ?>
                        </div>
                    </details>
                <?php endif; ?>
                <?php if (!empty($add)) : ?>
                    <a class="wjpl-btn wjpl-btn-add" href="<?php echo esc_url($add['url']); ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                        <?php echo esc_html($add['label']); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Status chips with counts. Each chip is a link, so it works without
     * script and can be opened in a new tab.
     *
     * @param array  $views   [key => [label, colour or '']].
     * @param array  $counts  [key => n].
     * @param string $current Current view key.
     * @param callable $link  function ($key): url.
     */
    public static function chips($views, $counts, $current, $link) {
        foreach ($views as $wpjobportal_key => $wpjobportal_view) {
            list($wpjobportal_label, $wpjobportal_colour) = $wpjobportal_view;
            $wpjobportal_on = $wpjobportal_key === $current;
            ?>
            <a class="wjpl-chip" href="<?php echo esc_url($link($wpjobportal_key)); ?>"<?php echo $wpjobportal_on ? ' aria-current="true"' : ''; ?>>
                <?php if ($wpjobportal_colour !== '') : ?><span class="wjpl-dot" style="background:<?php echo esc_attr($wpjobportal_colour); ?>"></span><?php endif; ?>
                <?php echo esc_html($wpjobportal_label); ?>
                <span class="wjpl-c"><?php echo esc_html(number_format_i18n(isset($counts[$wpjobportal_key]) ? $counts[$wpjobportal_key] : 0)); ?></span>
            </a>
            <?php
        }
    }

    /**
     * Sort list. Changing it reloads the list (script), with a button for
     * browsers without script.
     *
     * @param array  $options [value => label], value is "key|1" or "key|-1".
     * @param string $current
     */
    public static function sort_select($options, $current) {
        ?>
        <label class="wjpl-sort"><?php echo esc_html(__('Sort', 'wp-job-portal')); ?>
            <select class="wjpl-sel" name="sort" data-wjpl-autosubmit aria-label="<?php echo esc_attr(__('Sort by', 'wp-job-portal')); ?>">
                <?php foreach ($options as $wpjobportal_value => $wpjobportal_label) : ?>
                    <option value="<?php echo esc_attr($wpjobportal_value); ?>"<?php selected($current, $wpjobportal_value); ?>><?php echo esc_html($wpjobportal_label); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <?php
    }

    /**
     * Footer: "Showing a–b of n" and the pager.
     *
     * @param int      $total
     * @param string   $noun_plural e.g. "jobs".
     * @param callable $link  function ($page): url.
     */
    public static function footer($total, $noun_plural, $link) {
        if ((int) $total === 0) {
            return; // the empty state already says there is nothing
        }
        $wpjobportal_limit = max(1, WPJOBPORTALpagination::getLimit());
        $wpjobportal_current = max(1, (int) WPJOBPORTALpagination::$_currentpage);
        $wpjobportal_pages = max(1, (int) ceil($total / $wpjobportal_limit));
        $wpjobportal_first = $total ? (($wpjobportal_current - 1) * $wpjobportal_limit) + 1 : 0;
        $wpjobportal_last = min($total, $wpjobportal_current * $wpjobportal_limit);
        if ($wpjobportal_first > $total) {
            // A page number past the end (an old link, a changed filter).
            $wpjobportal_first = $wpjobportal_last = 0;
        }
        ?>
        <div class="wjpl-foot">
            <span><?php
                if ((int) $total === 1) {
                    // The noun is passed in the plural only ("1 jobs"), so one result goes without it
                    echo esc_html(__('Showing 1 of 1', 'wp-job-portal'));
                } else {
                    /* translators: 1: first row shown, 2: last row shown, 3: total, 4: what is listed, e.g. jobs */
                    echo esc_html(sprintf(__('Showing %1$s–%2$s of %3$s %4$s', 'wp-job-portal'), number_format_i18n($wpjobportal_first), number_format_i18n($wpjobportal_last), number_format_i18n($total), $noun_plural));
                }
            ?></span>
            <?php if ($wpjobportal_pages > 1) : ?>
                <nav class="wjpl-pager" aria-label="<?php echo esc_attr(__('Pages', 'wp-job-portal')); ?>">
                    <?php if ($wpjobportal_current > 1) : ?>
                        <a href="<?php echo esc_url($link($wpjobportal_current - 1)); ?>" aria-label="<?php echo esc_attr(__('Previous page', 'wp-job-portal')); ?>">‹</a>
                    <?php else : ?><span class="wjpl-off" aria-hidden="true">‹</span><?php endif; ?>
                    <?php foreach (self::page_numbers($wpjobportal_current, $wpjobportal_pages) as $wpjobportal_page) : ?>
                        <?php if ($wpjobportal_page === 0) : ?>
                            <span class="wjpl-gap" aria-hidden="true">…</span>
                        <?php elseif ($wpjobportal_page === $wpjobportal_current) : ?>
                            <span class="wjpl-on" aria-current="page"><?php echo esc_html(number_format_i18n($wpjobportal_page)); ?></span>
                        <?php else : ?>
                            <a href="<?php echo esc_url($link($wpjobportal_page)); ?>"><?php echo esc_html(number_format_i18n($wpjobportal_page)); ?></a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if ($wpjobportal_current < $wpjobportal_pages) : ?>
                        <a href="<?php echo esc_url($link($wpjobportal_current + 1)); ?>" aria-label="<?php echo esc_attr(__('Next page', 'wp-job-portal')); ?>">›</a>
                    <?php else : ?><span class="wjpl-off" aria-hidden="true">›</span><?php endif; ?>
                </nav>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Empty state.
     *
     * @param string $title
     * @param string $text
     * @param array  $actions [label => url]
     */
    public static function empty_state($title, $text, $actions = array()) {
        ?>
        <div class="wjpl-empty">
            <b><?php echo esc_html($title); ?></b>
            <?php echo esc_html($text); ?>
            <?php if (!empty($actions)) : ?>
                <div class="wjpl-empty-acts">
                    <?php foreach ($actions as $wpjobportal_label => $wpjobportal_url) : ?>
                        <a class="wjpl-btn wjpl-btn-sec" href="<?php echo esc_url($wpjobportal_url); ?>"><?php echo esc_html($wpjobportal_label); ?></a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * The slim top bar of a rebuilt screen: breadcrumb (Job Portal › …) and the
     * full-screen button. Settings, help and the version live in the menu.
     */
    public static function top_bar($crumbs) {
        // Every redesigned screen draws this bar, so it brings the shared CSS and
        // script (which also moves WordPress notices out from under the menu).
        self::enqueue();
        ?>
        <div class="wjpl-slim">
            <nav class="wjpl-crumbs" aria-label="<?php echo esc_attr(__('Breadcrumb', 'wp-job-portal')); ?>">
                <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal')); ?>"><?php echo esc_html(__('Job Portal', 'wp-job-portal')); ?></a>
                <?php foreach (array_values($crumbs) as $wpjobportal_i => $wpjobportal_crumb) : ?>
                    <span aria-hidden="true">/</span>
                    <?php if (!empty($wpjobportal_crumb['url'])) : ?>
                        <a href="<?php echo esc_url($wpjobportal_crumb['url']); ?>"><?php echo esc_html($wpjobportal_crumb['label']); ?></a>
                    <?php elseif ($wpjobportal_i === count($crumbs) - 1) : ?>
                        <b aria-current="page"><?php echo esc_html($wpjobportal_crumb['label']); ?></b>
                    <?php else : ?>
                        <span><?php echo esc_html($wpjobportal_crumb['label']); ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>
            <div class="wjpl-tools">
                <?php self::bell(); ?>
                <button class="wjpl-ib wjpl-focusbtn" type="button" aria-pressed="false" aria-label="<?php echo esc_attr(__('Full screen: hide the WordPress menu', 'wp-job-portal')); ?>" title="<?php echo esc_attr(__('Full screen', 'wp-job-portal')); ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5"/></svg>
                </button>
            </div>
        </div>
        <?php
    }

    /**
     * The notifications bell: approvals waiting and applications of the last
     * seven days, from the menu's cached counts. The number is their total.
     */
    public static function bell() {
        $wpjobportal_counts = WPJOBPORTAL_Admin_Menu_Service::counts();
        $wpjobportal_items = array();
        $wpjobportal_waiting = array();
        $wpjobportal_approvals = 0;
        foreach (WPJOBPORTAL_Admin_Menu_Service::approval_queues() as $wpjobportal_key => $wpjobportal_queue) {
            $wpjobportal_n = isset($wpjobportal_counts['approvals_' . $wpjobportal_key]) ? (int) $wpjobportal_counts['approvals_' . $wpjobportal_key] : 0;
            if ($wpjobportal_n > 0) {
                $wpjobportal_waiting[] = $wpjobportal_queue['label'] . ' ' . $wpjobportal_n;
                $wpjobportal_approvals += $wpjobportal_n;
            }
        }
        if ($wpjobportal_approvals > 0) {
            $wpjobportal_items[] = array(
                'kind' => 'approval',
                /* translators: %d: number of items waiting for approval */
                'title' => sprintf(_n('%d waiting for approval', '%d waiting for approval', $wpjobportal_approvals, 'wp-job-portal'), $wpjobportal_approvals),
                'sub' => implode(' · ', $wpjobportal_waiting),
                'url' => admin_url('admin.php?page=wpjobportal_approvals'),
            );
        }
        $wpjobportal_new = isset($wpjobportal_counts['new_applications']) ? (int) $wpjobportal_counts['new_applications'] : 0;
        if ($wpjobportal_new > 0) {
            $wpjobportal_format = wpjobportal::$_configuration['date_format'] ?? get_option('date_format');
            foreach (WPJOBPORTAL_Admin_Menu_Service::recent_applications() as $wpjobportal_row) {
                $wpjobportal_items[] = array(
                    'kind' => 'application',
                    'title' => __('New application', 'wp-job-portal'),
                    /* translators: 1: candidate name, 2: job title */
                    'sub' => sprintf(__('%1$s applied to %2$s', 'wp-job-portal'), $wpjobportal_row['name'] !== '' ? $wpjobportal_row['name'] : __('A candidate', 'wp-job-portal'), (string) $wpjobportal_row['jobtitle']),
                    'date' => $wpjobportal_row['apply_date'] ? date_i18n($wpjobportal_format, strtotime($wpjobportal_row['apply_date'])) : '',
                    'url' => admin_url('admin.php?page=wpjobportal_jobapply&wpjobportallt=jobappliedresume&jobid=' . (int) $wpjobportal_row['jobid']),
                );
            }
        }
        $wpjobportal_total = $wpjobportal_approvals + $wpjobportal_new;
        $wpjobportal_label = $wpjobportal_total
            /* translators: %d: number of notifications */
            ? sprintf(_n('Notifications, %d new', 'Notifications, %d new', $wpjobportal_total, 'wp-job-portal'), $wpjobportal_total)
            : __('Notifications', 'wp-job-portal');
        ?>
        <details class="wjpl-bell">
            <summary class="wjpl-ib" aria-label="<?php echo esc_attr($wpjobportal_label); ?>" title="<?php echo esc_attr(__('Notifications', 'wp-job-portal')); ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 9a6 6 0 0 1 12 0c0 6 2 7 2 7H4s2-1 2-7M10 20a2 2 0 0 0 4 0"/></svg>
                <?php if ($wpjobportal_total) : ?><span class="wjpl-bell-n" aria-hidden="true"><?php echo esc_html($wpjobportal_total > 99 ? '99+' : (string) $wpjobportal_total); ?></span><?php endif; ?>
            </summary>
            <div class="wjpl-bell-pop">
                <div class="wjpl-bell-h"><?php echo esc_html(__('Notifications', 'wp-job-portal')); ?></div>
                <?php if (!$wpjobportal_items) : ?>
                    <p class="wjpl-bell-none"><?php echo esc_html(__('Nothing new. No approvals are waiting and no one applied in the last seven days.', 'wp-job-portal')); ?></p>
                <?php endif; ?>
                <?php foreach ($wpjobportal_items as $wpjobportal_item) : ?>
                    <a class="wjpl-bell-it is-<?php echo esc_attr($wpjobportal_item['kind']); ?>" href="<?php echo esc_url($wpjobportal_item['url']); ?>">
                        <span class="wjpl-bell-k" aria-hidden="true"><?php echo WPJOBPORTAL_Admin_Menu_Service::icon($wpjobportal_item['kind'] === 'approval' ? 'check' : 'user'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed SVG markup ?></span>
                        <span class="wjpl-bell-t">
                            <b><?php echo esc_html($wpjobportal_item['title']); ?></b>
                            <span><?php echo esc_html($wpjobportal_item['sub']); ?></span>
                            <?php if (!empty($wpjobportal_item['date'])) : ?><small><?php echo esc_html($wpjobportal_item['date']); ?></small><?php endif; ?>
                        </span>
                    </a>
                <?php endforeach; ?>
                <?php if ($wpjobportal_new > 0) : ?>
                    <a class="wjpl-bell-all" href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_jobapply&wpjobportallt=applications')); ?>">
                        <?php /* translators: %d: applications in the last seven days */ echo esc_html(sprintf(_n('See all applications (%d this week)', 'See all applications (%d this week)', $wpjobportal_new, 'wp-job-portal'), $wpjobportal_new)); ?>
                    </a>
                <?php endif; ?>
            </div>
        </details>
        <?php
    }

    /**
     * Where an action goes afterwards: back to the list the admin came from
     * (filters kept) when the request names one of that list's own links,
     * otherwise the given default.
     *
     * @param string $page    The list's page slug, e.g. wpjobportal_job.
     * @param string $default admin.php path.
     * @return string
     */
    public static function return_url($page, $default) {
        // Raw, then URL-escaped: the text sanitizer behind getVar() strips the encoded
        // characters of a link (a space in a search became nothing).
        $wpjobportal_return = WPJOBPORTALrequest::raw('returnto');
        $wpjobportal_return = is_string($wpjobportal_return) ? esc_url_raw($wpjobportal_return) : '';
        if ($wpjobportal_return !== '' && strpos($wpjobportal_return, admin_url('admin.php')) === 0) {
            parse_str((string) wp_parse_url($wpjobportal_return, PHP_URL_QUERY), $wpjobportal_query);
            // Back to the list, or to Approvals when the action was taken there.
            if (isset($wpjobportal_query['page']) && in_array($wpjobportal_query['page'], array($page, 'wpjobportal_approvals'), true)) {
                return $wpjobportal_return;
            }
        }
        return esc_url_raw(admin_url($default));
    }

    /**
     * The Approvals tabs: Everything waiting, then one tab per active queue with
     * its count. Drawn on the Approvals page and above each queue screen.
     *
     * @param string $current Tab key ('all' or a queue key).
     */
    public static function approvals_tabs($current) {
        $wpjobportal_counts = WPJOBPORTAL_Admin_Menu_Service::counts();
        $wpjobportal_tabs = array('all' => array('label' => __('Everything waiting', 'wp-job-portal'), 'url' => 'wpjobportal_approvals', 'n' => isset($wpjobportal_counts['approvals']) ? $wpjobportal_counts['approvals'] : 0));
        foreach (WPJOBPORTAL_Admin_Menu_Service::approval_queues() as $wpjobportal_key => $wpjobportal_queue) {
            $wpjobportal_tabs[$wpjobportal_key] = array('label' => $wpjobportal_queue['label'], 'url' => $wpjobportal_queue['url'], 'n' => isset($wpjobportal_counts['approvals_' . $wpjobportal_key]) ? $wpjobportal_counts['approvals_' . $wpjobportal_key] : 0);
        }
        ?>
        <nav class="wjpl-tabs" aria-label="<?php echo esc_attr(__('Approvals', 'wp-job-portal')); ?>">
            <?php foreach ($wpjobportal_tabs as $wpjobportal_key => $wpjobportal_tab) : ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=' . $wpjobportal_tab['url'])); ?>"<?php echo $wpjobportal_key === $current ? ' aria-current="page"' : ''; ?>>
                    <?php echo esc_html($wpjobportal_tab['label']); ?><span class="wjpl-tab-n<?php echo $wpjobportal_tab['n'] ? ' wjpl-due' : ''; ?>"><?php echo esc_html(number_format_i18n($wpjobportal_tab['n'])); ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
        <?php
    }

    /**
     * Tabs for a page that combines several of today's screens (design 4):
     * Reports, Categories, Job attributes, Locations, Forms & fields, AI writer,
     * Site mode (from the menu's third-level links) and Emails (its two pages).
     * Draws nothing on other pages, so screens can call it unconditionally.
     */
    public static function section_tabs() {
        $wpjobportal_sections = WPJOBPORTAL_Admin_Menu_Service::sections();
        $wpjobportal_current = WPJOBPORTAL_Admin_Menu_Service::current($wpjobportal_sections);
        $wpjobportal_tabs = array();
        $wpjobportal_on = -1;
        foreach ($wpjobportal_sections as $wpjobportal_section) {
            if ($wpjobportal_section['id'] !== $wpjobportal_current['section'] || $wpjobportal_current['sub'] < 0) {
                continue;
            }
            $wpjobportal_sub = $wpjobportal_section['subs'][$wpjobportal_current['sub']];
            if (!empty($wpjobportal_sub['third']) && count($wpjobportal_sub['third']) > 1) {
                $wpjobportal_tabs = $wpjobportal_sub['third'];
                $wpjobportal_on = $wpjobportal_current['third'];
                $wpjobportal_label = $wpjobportal_sub['label'];
            } elseif (in_array($wpjobportal_section['id'], array('emails', 'reports', 'licences'), true) && count($wpjobportal_section['subs']) > 1) {
                // Reports and Licences are menu sections of their own again; their pages keep the tabs they had as Dashboard pages.
                $wpjobportal_tabs = $wpjobportal_section['subs'];
                $wpjobportal_on = $wpjobportal_current['sub'];
                $wpjobportal_label = $wpjobportal_section['label'];
            }
        }
        if (empty($wpjobportal_tabs)) {
            return;
        }
        ?>
        <nav class="wjpl-tabs" aria-label="<?php echo esc_attr($wpjobportal_label); ?>">
            <?php foreach ($wpjobportal_tabs as $wpjobportal_index => $wpjobportal_tab) : ?>
                <a href="<?php echo esc_url(WPJOBPORTAL_Admin_Menu_Service::link($wpjobportal_tab)); ?>"<?php echo $wpjobportal_index === $wpjobportal_on ? ' aria-current="page"' : ''; ?>><?php echo esc_html($wpjobportal_tab['label']); ?></a>
            <?php endforeach; ?>
        </nav>
        <?php
    }

    /**
     * Breadcrumb from the menu: section › page › tab, the last without a link.
     * For the screens that keep their 2.7 body inside the new frame.
     *
     * @return array For top_bar().
     */
    public static function menu_crumbs() {
        $wpjobportal_sections = WPJOBPORTAL_Admin_Menu_Service::sections();
        $wpjobportal_current = WPJOBPORTAL_Admin_Menu_Service::current($wpjobportal_sections);
        $wpjobportal_crumbs = array();
        foreach ($wpjobportal_sections as $wpjobportal_section) {
            if ($wpjobportal_section['id'] !== $wpjobportal_current['section']) {
                continue;
            }
            $wpjobportal_first = !empty($wpjobportal_section['subs']) ? reset($wpjobportal_section['subs']) : null;
            $wpjobportal_crumbs[] = array('label' => $wpjobportal_section['label'], 'url' => $wpjobportal_first ? self::menu_link($wpjobportal_first) : (isset($wpjobportal_section['url']) ? admin_url('admin.php?page=' . $wpjobportal_section['url']) : ''));
            if ($wpjobportal_current['sub'] >= 0 && isset($wpjobportal_section['subs'][$wpjobportal_current['sub']])) {
                $wpjobportal_sub = $wpjobportal_section['subs'][$wpjobportal_current['sub']];
                if ($wpjobportal_sub['label'] !== $wpjobportal_section['label']) {
                    $wpjobportal_crumbs[] = array('label' => $wpjobportal_sub['label'], 'url' => self::menu_link($wpjobportal_sub));
                }
                if ($wpjobportal_current['third'] >= 0 && isset($wpjobportal_sub['third'][$wpjobportal_current['third']])) {
                    $wpjobportal_crumbs[] = array('label' => $wpjobportal_sub['third'][$wpjobportal_current['third']]['label'], 'url' => '');
                }
            }
        }
        if ($wpjobportal_crumbs) {
            $wpjobportal_crumbs[count($wpjobportal_crumbs) - 1]['url'] = '';
        }
        return $wpjobportal_crumbs;
    }

    /**
     * Breadcrumb, title row and section tabs for a screen whose body keeps its own
     * markup (it replaces legacy_top() plus the 2.7 banner title).
     *
     * @param string $title
     * @param string $subtitle Line beside the title.
     * @param array  $more     [label => url] for the ⋯ menu.
     * @param array  $add      ['label' => .., 'url' => ..] for the green button.
     */
    public static function page_head($title, $subtitle = '', $more = array(), $add = array()) {
        self::$top_done = true;
        $crumbs = self::menu_crumbs();
        if (!$crumbs) {
            // A screen outside the menu's sections (Help, Shortcodes …) still names itself.
            $crumbs = array(array('label' => $title));
        }
        echo '<div class="wjpl wjpl-formhead">';
        self::top_bar($crumbs);
        self::title_row($title, $subtitle, $more, $add);
        self::section_tabs();
        echo '</div>';
    }

    /** Whether this request already printed the design 4 top bar (legacy_top or pagetitle). */
    private static $top_done = false;

    /**
     * The design 4 top bar where a 2.7 screen had its own top strip (breadcrumb,
     * Configuration and Help buttons, version). Breadcrumb from the menu.
     */
    public static function legacy_top() {
        if (self::$top_done) {
            return;
        }
        self::$top_done = true;
        echo '<div class="wjpl wjpl-shell wjpl-shell-top">';
        self::top_bar(self::menu_crumbs());
        echo '</div>';
    }

    /** For templates/admin/pagetitle.php: true when it should print the top bar itself. */
    public static function claim_top() {
        if (self::$top_done) {
            return false;
        }
        self::$top_done = true;
        return true;
    }

    private static function menu_link($item) {
        return WPJOBPORTAL_Admin_Menu_Service::link($item);
    }

    /**
     * A 2.7 approval queue URL (jobqueue, companiesqueue, …) draws the Approvals
     * page for that one queue, so old links and bookmarks still land there.
     *
     * @param string $type Queue key: jobs, companies, resumes, departments, coverletters, folders, messages.
     */
    public static function approval_queue($type) {
        WPJOBPORTALincluder::getJSModel('approvals')->getEverythingWaiting($type, sanitize_text_field((string) WPJOBPORTALrequest::getVar('q')));
        WPJOBPORTALincluder::include_file('admin_approvals', 'approvals');
    }

    /** Shared list stylesheet and behaviour (select all, bulk bar, delete dialog, auto-submit). */
    public static function enqueue() {
        wp_enqueue_style('wpjobportal-admin-list', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/wpjobportal-admin-list.css', array('wpjobportal-admin-desktop-css'), wpjobportal_asset_version('includes/css/wpjobportal-admin-list.css'));
        wp_enqueue_script('wpjobportal-admin-list', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/wpjobportal-admin-list.js', array('jquery'), wpjobportal_asset_version('includes/js/wpjobportal-admin-list.js'), true);
    }

    /** Page numbers with gaps (0) around the current page. */
    private static function page_numbers($current, $pages) {
        $wpjobportal_out = array();
        $wpjobportal_last = 0;
        for ($wpjobportal_page = 1; $wpjobportal_page <= $pages; $wpjobportal_page++) {
            if ($wpjobportal_page === 1 || $wpjobportal_page === $pages || abs($wpjobportal_page - $current) <= 2) {
                if ($wpjobportal_last && $wpjobportal_page - $wpjobportal_last > 1) {
                    $wpjobportal_out[] = 0;
                }
                $wpjobportal_out[] = $wpjobportal_page;
                $wpjobportal_last = $wpjobportal_page;
            }
        }
        return $wpjobportal_out;
    }
}
