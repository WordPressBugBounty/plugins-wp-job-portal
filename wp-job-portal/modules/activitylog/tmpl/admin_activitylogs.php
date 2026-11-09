<?php
if (!defined('ABSPATH')) die('Restricted Access');
/*
 * Settings › Activity log (design 4, built from the Jobs list's parts): what admins,
 * employers and job seekers added, changed or deleted, newest first.
 */
WPJOBPORTAL_Admin_List_UI::enqueue();
if (!WPJOBPORTALincluder::getTemplate('templates/admin/header', array('wpjobportal_module' => 'activitylog'))) {
    return;
}

$wpjobportal_list = wpjobportal::$_data['activitylist'];
$wpjobportal_f = $wpjobportal_list['filter'];
$wpjobportal_counts = $wpjobportal_list['counts'];
$wpjobportal_types = $wpjobportal_list['types'];
$link = function ($args = array()) use ($wpjobportal_f) {
    $current = $wpjobportal_f;
    if ($current['view'] === 'all') {
        $current['view'] = '';
    }
    if ($current['sort'] === 'created|-1') {
        $current['sort'] = '';
    }
    return WPJOBPORTAL_Admin_List_UI::url('wpjobportal_activitylog', $current, $args);
};
$wpjobportal_type_label = function ($wpjobportal_key) use ($wpjobportal_types) {
    return isset($wpjobportal_types[$wpjobportal_key]) ? $wpjobportal_types[$wpjobportal_key] : wpjobportalphplib::wpJP_ucwords((string) $wpjobportal_key);
};
$wpjobportal_views = array('all' => array(__('All', 'wp-job-portal'), ''));
foreach ($wpjobportal_counts as $wpjobportal_key => $wpjobportal_n) {
    if ($wpjobportal_key !== 'all') {
        $wpjobportal_views[$wpjobportal_key] = array($wpjobportal_type_label($wpjobportal_key), '');
    }
}
$wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
?>
<div id="wpjobportaladmin-wrapper" class="wjpl-page">
    <div id="wpjobportaladmin-leftmenu">
        <?php WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <div class="wjpl">
            <?php
            WPJOBPORTAL_Admin_List_UI::top_bar(WPJOBPORTAL_Admin_List_UI::menu_crumbs());
            WPJOBPORTAL_Admin_List_UI::title_row(
                __('Activity log', 'wp-job-portal'),
                /* translators: %s: number of log entries */
                sprintf(_n('%s entry', '%s entries', $wpjobportal_counts['all'], 'wp-job-portal'), number_format_i18n($wpjobportal_counts['all'])) . ' · ' . __('What was added, changed or deleted, and by whom.', 'wp-job-portal')
            );
            WPJOBPORTAL_Admin_List_UI::section_tabs();
            ?>
            <section class="wjpl-card" aria-label="<?php echo esc_attr(__('Activity log', 'wp-job-portal')); ?>">
                <form class="wjpl-filters" method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>">
                    <input type="hidden" name="page" value="wpjobportal_activitylog">
                    <?php if ($wpjobportal_f['view'] !== 'all') : ?><input type="hidden" name="view" value="<?php echo esc_attr($wpjobportal_f['view']); ?>"><?php endif; ?>
                    <div class="wjpl-tb1">
                        <?php WPJOBPORTAL_Admin_List_UI::chips($wpjobportal_views, $wpjobportal_counts, $wpjobportal_f['view'], function ($wpjobportal_key) use ($link) {
                            return $link(array('view' => $wpjobportal_key === 'all' ? null : $wpjobportal_key));
                        }); ?>
                        <?php WPJOBPORTAL_Admin_List_UI::sort_select(array(
                            'created|-1' => __('Newest first', 'wp-job-portal'),
                            'created|1' => __('Oldest first', 'wp-job-portal'),
                            'user|1' => __('User A–Z', 'wp-job-portal'),
                            'user|-1' => __('User Z–A', 'wp-job-portal'),
                            'type|1' => __('Type A–Z', 'wp-job-portal'),
                            'type|-1' => __('Type Z–A', 'wp-job-portal'),
                            'id|-1' => __('ID, highest first', 'wp-job-portal'),
                            'id|1' => __('ID, lowest first', 'wp-job-portal'),
                        ), $wpjobportal_f['sort']); ?>
                    </div>
                    <div class="wjpl-tb2">
                        <div class="wjpl-search">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                            <input class="wjpl-inp" type="search" name="q" value="<?php echo esc_attr($wpjobportal_f['q']); ?>" placeholder="<?php echo esc_attr(__('Search what happened or who did it', 'wp-job-portal')); ?>" aria-label="<?php echo esc_attr(__('Search the activity log', 'wp-job-portal')); ?>">
                        </div>
                        <span></span>
                        <span class="wjpl-tb2-acts">
                            <button class="wjpl-btn wjpl-btn-pri" type="submit"><?php echo esc_html(__('Search', 'wp-job-portal')); ?></button>
                            <a class="wjpl-linkbtn" href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_activitylog')); ?>"><?php echo esc_html(__('Reset', 'wp-job-portal')); ?></a>
                        </span>
                    </div>
                </form>
                <div class="wjpl-tw">
                <?php if (!empty($wpjobportal_list['rows'])) : ?>
                    <table class="wjpl-table">
                        <thead><tr>
                            <?php
                            $wpjobportal_th = function ($wpjobportal_key, $label, $class = '') use ($wpjobportal_f, $link) {
                                $parts = explode('|', $wpjobportal_f['sort']);
                                $on = $parts[0] === $wpjobportal_key;
                                $next = ($on && $parts[1] === '1') ? '-1' : '1';
                                echo '<th class="' . esc_attr($class) . '"' . ($on ? ' aria-sort="' . ($parts[1] === '1' ? 'ascending' : 'descending') . '"' : '') . '><a href="' . esc_url($link(array('sort' => $wpjobportal_key . '|' . $next))) . '">' . esc_html($label) . '</a></th>';
                            };
                            echo '<th>' . esc_html(__('What happened', 'wp-job-portal')) . '</th>';
                            $wpjobportal_th('type', __('Type', 'wp-job-portal'));
                            $wpjobportal_th('user', __('By', 'wp-job-portal'), 'wjpl-col-cat');
                            $wpjobportal_th('created', __('When', 'wp-job-portal'), 'wjpl-col-posted');
                            $wpjobportal_th('id', __('ID', 'wp-job-portal'), 'wjpl-num');
                            ?>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($wpjobportal_list['rows'] as $wpjobportal_row) :
                            $wpjobportal_name = trim((string) $wpjobportal_row->first_name . ' ' . (string) $wpjobportal_row->last_name);
                            $wpjobportal_time = strtotime((string) $wpjobportal_row->created);
                            ?>
                            <tr>
                                <td class="wjpl-c-main wjpl-log-desc"><?php echo wp_kses((string) $wpjobportal_row->description, WPJOBPORTAL_ALLOWED_TAGS); ?></td>
                                <td data-l="<?php echo esc_attr(__('Type', 'wp-job-portal')); ?>"><span class="wjpl-badge wjpl-b-closed"><?php echo esc_html($wpjobportal_type_label($wpjobportal_row->referencefor)); ?></span></td>
                                <td class="wjpl-col-cat" data-l="<?php echo esc_attr(__('By', 'wp-job-portal')); ?>">
                                    <?php if ($wpjobportal_name !== '' && (int) $wpjobportal_row->uid) : ?>
                                        <a class="wjpl-meta-link" href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_user&wpjobportallt=userdetail&id=' . (int) $wpjobportal_row->uid)); ?>"><?php echo esc_html($wpjobportal_name); ?></a>
                                    <?php else : ?>
                                        <span class="wjpl-muted"><?php echo esc_html($wpjobportal_name !== '' ? $wpjobportal_name : __('Unknown user', 'wp-job-portal')); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="wjpl-col-posted wjpl-nowrap" data-l="<?php echo esc_attr(__('When', 'wp-job-portal')); ?>">
                                    <?php echo esc_html($wpjobportal_time ? date_i18n($wpjobportal_dateformat, $wpjobportal_time) : ''); ?>
                                    <div class="wjpl-small"><?php echo esc_html($wpjobportal_time ? date_i18n(get_option('time_format'), $wpjobportal_time) : ''); ?></div>
                                </td>
                                <td class="wjpl-num" data-l="<?php echo esc_attr(__('ID', 'wp-job-portal')); ?>"><?php echo esc_html($wpjobportal_row->id); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php elseif ($wpjobportal_list['total'] > 0) : ?>
                    <?php WPJOBPORTAL_Admin_List_UI::empty_state(__('Nothing on this page', 'wp-job-portal'), __('This list has fewer pages now.', 'wp-job-portal'), array(__('Go to the first page', 'wp-job-portal') => $link())); ?>
                <?php elseif ($wpjobportal_counts['all'] === 0) : ?>
                    <?php WPJOBPORTAL_Admin_List_UI::empty_state(__('No activity yet', 'wp-job-portal'), __('Each time a job, company, resume or setting is added, changed or deleted, it is noted here.', 'wp-job-portal')); ?>
                <?php else : ?>
                    <?php WPJOBPORTAL_Admin_List_UI::empty_state(__('Nothing matches these filters', 'wp-job-portal'), __('Try fewer words, or reset the filters.', 'wp-job-portal'), array(__('Reset filters', 'wp-job-portal') => admin_url('admin.php?page=wpjobportal_activitylog'))); ?>
                <?php endif; ?>
                </div>
                <?php WPJOBPORTAL_Admin_List_UI::footer($wpjobportal_list['total'], __('entries', 'wp-job-portal'), function ($page) use ($link) {
                    return $link(array('pagenum' => $page > 1 ? $page : null));
                }); ?>
            </section>
        </div>
    </div>
</div>
