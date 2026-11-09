<?php
if (!defined('ABSPATH')) die('Restricted Access');
/*
 * Settings › Error log (design 4, built from the Jobs list's parts): database errors
 * the plugin caught, with the page and query (secrets redacted before display).
 */
WPJOBPORTAL_Admin_List_UI::enqueue();
$wpjobportal_list = wpjobportal::$_data['errorlist'];
$wpjobportal_f = $wpjobportal_list['filter'];
$wpjobportal_counts = $wpjobportal_list['counts'];
$link = function ($args = array()) use ($wpjobportal_f) {
    $current = $wpjobportal_f;
    if ($current['view'] === 'all') {
        $current['view'] = '';
    }
    if ($current['sort'] === 'created|-1') {
        $current['sort'] = '';
    }
    return WPJOBPORTAL_Admin_List_UI::url('wpjobportal_systemerror', $current, $args);
};
$wpjobportal_views = array(
    'all' => array(__('All', 'wp-job-portal'), ''),
    'new' => array(__('New', 'wp-job-portal'), '#dc2626'),
    'seen' => array(__('Seen', 'wp-job-portal'), '#6b7280'),
);
$wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
?>
<div id="wpjobportaladmin-wrapper" class="wjpl-page">
    <div id="wpjobportaladmin-leftmenu">
        <?php WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <?php WPJOBPORTALMessages::getLayoutMessage(WPJOBPORTALincluder::getJSModel('systemerror')->getMessagekey()); ?>
        <div class="wjpl">
            <?php
            WPJOBPORTAL_Admin_List_UI::top_bar(WPJOBPORTAL_Admin_List_UI::menu_crumbs());
            WPJOBPORTAL_Admin_List_UI::title_row(
                __('Error log', 'wp-job-portal'),
                /* translators: 1: number of errors, 2: number not seen yet */
                sprintf(_n('%1$s error · %2$s new', '%1$s errors · %2$s new', $wpjobportal_counts['all'], 'wp-job-portal'), number_format_i18n($wpjobportal_counts['all']), number_format_i18n($wpjobportal_counts['new']))
            );
            WPJOBPORTAL_Admin_List_UI::section_tabs();
            ?>
            <section class="wjpl-card" aria-label="<?php echo esc_attr(__('Error log', 'wp-job-portal')); ?>">
                <form class="wjpl-filters" method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>">
                    <input type="hidden" name="page" value="wpjobportal_systemerror">
                    <?php if ($wpjobportal_f['view'] !== 'all') : ?><input type="hidden" name="view" value="<?php echo esc_attr($wpjobportal_f['view']); ?>"><?php endif; ?>
                    <div class="wjpl-tb1">
                        <?php WPJOBPORTAL_Admin_List_UI::chips($wpjobportal_views, $wpjobportal_counts, $wpjobportal_f['view'], function ($key) use ($link) {
                            return $link(array('view' => $key === 'all' ? null : $key));
                        }); ?>
                        <?php WPJOBPORTAL_Admin_List_UI::sort_select(array(
                            'created|-1' => __('Newest first', 'wp-job-portal'),
                            'created|1' => __('Oldest first', 'wp-job-portal'),
                        ), $wpjobportal_f['sort']); ?>
                    </div>
                    <div class="wjpl-tb2">
                        <div class="wjpl-search">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                            <input class="wjpl-inp" type="search" name="q" value="<?php echo esc_attr($wpjobportal_f['q']); ?>" placeholder="<?php echo esc_attr(__('Search error, page or query', 'wp-job-portal')); ?>" aria-label="<?php echo esc_attr(__('Search the error log', 'wp-job-portal')); ?>">
                        </div>
                        <span></span>
                        <span class="wjpl-tb2-acts">
                            <button class="wjpl-btn wjpl-btn-pri" type="submit"><?php echo esc_html(__('Search', 'wp-job-portal')); ?></button>
                            <a class="wjpl-linkbtn" href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_systemerror')); ?>"><?php echo esc_html(__('Reset', 'wp-job-portal')); ?></a>
                        </span>
                    </div>
                </form>
                <div class="wjpl-tw">
                <?php if (!empty($wpjobportal_list['rows'])) : ?>
                    <table class="wjpl-table">
                        <thead><tr>
                            <th><?php echo esc_html(__('Error', 'wp-job-portal')); ?></th>
                            <th><?php echo esc_html(__('Status', 'wp-job-portal')); ?></th>
                            <th class="wjpl-col-posted"<?php echo ' aria-sort="' . ($wpjobportal_f['sort'] === 'created|1' ? 'ascending' : 'descending') . '"'; ?>><a href="<?php echo esc_url($link(array('sort' => $wpjobportal_f['sort'] === 'created|1' ? null : 'created|1'))); ?>"><?php echo esc_html(__('Date', 'wp-job-portal')); ?></a></th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($wpjobportal_list['rows'] as $wpjobportal_row) :
                            $wpjobportal_raw = WPJOBPORTAL_Redactor::redact((string) $wpjobportal_row->error);
                            $wpjobportal_data = json_decode($wpjobportal_raw, true);
                            $wpjobportal_seen = (int) $wpjobportal_row->isview === 1;
                            $wpjobportal_time = strtotime((string) $wpjobportal_row->created);
                            ?>
                            <tr>
                                <td class="wjpl-c-main wjpl-err">
                                    <?php if (is_array($wpjobportal_data)) : ?>
                                        <span class="wjpl-title wjpl-err-msg"><?php echo esc_html(isset($wpjobportal_data['error']) && $wpjobportal_data['error'] !== '' ? $wpjobportal_data['error'] : __('Unknown error', 'wp-job-portal')); ?></span>
                                        <div class="wjpl-meta wjpl-meta-plain wjpl-ellipsis"><?php echo esc_html(isset($wpjobportal_data['url']) && $wpjobportal_data['url'] !== '' ? $wpjobportal_data['url'] : __('No page recorded', 'wp-job-portal')); ?></div>
                                        <details class="wjpl-err-more">
                                            <summary><?php echo esc_html(__('Path and query', 'wp-job-portal')); ?></summary>
                                            <div class="wjpl-err-code-t"><?php echo esc_html(__('Path', 'wp-job-portal')); ?></div>
                                            <pre class="wjpl-err-code"><?php echo esc_html(isset($wpjobportal_data['path']) && $wpjobportal_data['path'] !== '' ? $wpjobportal_data['path'] : __('N/A', 'wp-job-portal')); ?></pre>
                                            <div class="wjpl-err-code-t"><?php echo esc_html(__('Query', 'wp-job-portal')); ?></div>
                                            <pre class="wjpl-err-code"><?php echo esc_html(isset($wpjobportal_data['query']) && $wpjobportal_data['query'] !== '' ? $wpjobportal_data['query'] : __('N/A', 'wp-job-portal')); ?></pre>
                                        </details>
                                    <?php elseif (trim($wpjobportal_raw) !== '') : ?>
                                        <span class="wjpl-title"><?php echo esc_html(__('Error (older format)', 'wp-job-portal')); ?></span>
                                        <pre class="wjpl-err-code"><?php echo esc_html($wpjobportal_raw); ?></pre>
                                    <?php else : ?>
                                        <span class="wjpl-muted"><?php echo esc_html(__('No error data recorded.', 'wp-job-portal')); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td data-l="<?php echo esc_attr(__('Status', 'wp-job-portal')); ?>"><span class="wjpl-badge wjpl-b-<?php echo $wpjobportal_seen ? 'closed' : 'bad'; ?>"><?php echo esc_html($wpjobportal_seen ? __('Seen', 'wp-job-portal') : __('New', 'wp-job-portal')); ?></span></td>
                                <td class="wjpl-col-posted wjpl-nowrap" data-l="<?php echo esc_attr(__('Date', 'wp-job-portal')); ?>">
                                    <?php echo esc_html($wpjobportal_time ? date_i18n($wpjobportal_dateformat, $wpjobportal_time) : ''); ?>
                                    <div class="wjpl-small"><?php echo esc_html($wpjobportal_time ? date_i18n(get_option('time_format'), $wpjobportal_time) : ''); ?></div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php elseif ($wpjobportal_list['total'] > 0) : ?>
                    <?php WPJOBPORTAL_Admin_List_UI::empty_state(__('Nothing on this page', 'wp-job-portal'), __('This list has fewer pages now.', 'wp-job-portal'), array(__('Go to the first page', 'wp-job-portal') => $link())); ?>
                <?php elseif ($wpjobportal_counts['all'] === 0) : ?>
                    <?php WPJOBPORTAL_Admin_List_UI::empty_state(__('No errors', 'wp-job-portal'), __('When a database query fails, the plugin notes it here with the page and the query.', 'wp-job-portal')); ?>
                <?php else : ?>
                    <?php WPJOBPORTAL_Admin_List_UI::empty_state(__('Nothing matches these filters', 'wp-job-portal'), __('Try fewer words, or reset the filters.', 'wp-job-portal'), array(__('Reset filters', 'wp-job-portal') => admin_url('admin.php?page=wpjobportal_systemerror'))); ?>
                <?php endif; ?>
                </div>
                <?php WPJOBPORTAL_Admin_List_UI::footer($wpjobportal_list['total'], __('errors', 'wp-job-portal'), function ($page) use ($link) {
                    return $link(array('pagenum' => $page > 1 ? $page : null));
                }); ?>
            </section>
        </div>
    </div>
</div>
