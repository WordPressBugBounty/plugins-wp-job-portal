<?php
if (!defined('ABSPATH')) die('Restricted Access');
/*
 * Jobs › Job alerts (design 4, built from the Jobs list's parts).
 * Classic screen: admin_jobalert_classic.php.
 */
WPJOBPORTAL_Admin_List_UI::enqueue();
if (!WPJOBPORTALincluder::getTemplate('templates/admin/header', array('wpjobportal_module' => 'jobalert'))) {
    return;
}

$wpjobportal_list = wpjobportal::$_data['jobalertlist'];
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
    return WPJOBPORTAL_Admin_List_UI::url('wpjobportal_jobalert', $current, $args);
};
$wpjobportal_here = $link(array('pagenum' => WPJOBPORTALpagination::$_currentpage > 1 ? WPJOBPORTALpagination::$_currentpage : null));
$wpjobportal_nonce = wp_create_nonce('wpjobportal_job_alert_nonce');
$wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
$wpjobportal_all_views = array(
    'all' => array(__('All', 'wp-job-portal'), ''),
    'approved' => array(__('Approved', 'wp-job-portal'), '#059669'),
    'waiting' => array(__('Waiting for approval', 'wp-job-portal'), '#d97706'),
    'rejected' => array(__('Rejected', 'wp-job-portal'), '#dc2626'),
    'payment' => array(__('Pending payment', 'wp-job-portal'), '#d97706'),
);
$wpjobportal_views = array_intersect_key($wpjobportal_all_views, array_flip($wpjobportal_list['views']));
$wpjobportal_statuses = array(1 => array('ok', __('Approved', 'wp-job-portal')), 0 => array('wait', __('Waiting for approval', 'wp-job-portal')), -1 => array('bad', __('Rejected', 'wp-job-portal')), 3 => array('wait', __('Pending payment', 'wp-job-portal')));
$wpjobportal_frequencies = array(1 => __('Daily', 'wp-job-portal'), 2 => __('Weekly', 'wp-job-portal'), 3 => __('Monthly', 'wp-job-portal'), 4 => __('Instant', 'wp-job-portal'));
$wpjobportal_add_url = admin_url('admin.php?page=wpjobportal_jobalert&wpjobportallt=formjobalert');
?>
<div id="wpjobportaladmin-wrapper" class="wjpl-page">
    <div id="wpjobportaladmin-leftmenu">
        <?php WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <div class="wjpl" data-delete-confirm="<?php echo esc_attr(__('Are you sure to unsubscribe', 'wp-job-portal') . ' ?'); ?>">
            <?php
            WPJOBPORTAL_Admin_List_UI::top_bar(array(array('label' => __('Jobs', 'wp-job-portal'), 'url' => admin_url('admin.php?page=wpjobportal_job')), array('label' => __('Job alerts', 'wp-job-portal'))));
            WPJOBPORTAL_Admin_List_UI::title_row(
                __('Job alerts', 'wp-job-portal'),
                /* translators: %s: number of job alerts */
                sprintf(_n('%s alert', '%s alerts', $wpjobportal_counts['all'], 'wp-job-portal'), number_format_i18n($wpjobportal_counts['all'])),
                array(
                    __('Job alert form fields', 'wp-job-portal') => admin_url('admin.php?page=wpjobportal_fieldordering&ff=6'),
                ),
                array('label' => __('Add New Job Alert', 'wp-job-portal'), 'url' => $wpjobportal_add_url)
            );
            ?>
            <section class="wjpl-card" aria-label="<?php echo esc_attr(__('Job alerts', 'wp-job-portal')); ?>">
                <form class="wjpl-filters" method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>">
                    <input type="hidden" name="page" value="wpjobportal_jobalert">
                    <?php if ($wpjobportal_f['view'] !== 'all') : ?><input type="hidden" name="view" value="<?php echo esc_attr($wpjobportal_f['view']); ?>"><?php endif; ?>
                    <div class="wjpl-tb1">
                        <?php WPJOBPORTAL_Admin_List_UI::chips($wpjobportal_views, $wpjobportal_counts, $wpjobportal_f['view'], function ($key) use ($link) {
                            return $link(array('view' => $key === 'all' ? null : $key));
                        }); ?>
                        <?php WPJOBPORTAL_Admin_List_UI::sort_select(array(
                            'created|-1' => __('Newest first', 'wp-job-portal'),
                            'created|1' => __('Oldest first', 'wp-job-portal'),
                            'name|1' => __('Name A–Z', 'wp-job-portal'),
                            'sent|-1' => __('Last sent', 'wp-job-portal'),
                        ), $wpjobportal_f['sort']); ?>
                    </div>
                    <div class="wjpl-selbar" role="toolbar" aria-label="<?php echo esc_attr(__('Bulk actions', 'wp-job-portal')); ?>" hidden>
                        <b><span class="wjpl-selcount">0</span> <?php echo esc_html(__('selected', 'wp-job-portal')); ?></b>
                        <button class="wjpl-sb wjpl-del" type="button" data-wjpl-bulk="delete"><?php echo esc_html(__('Unsubscribe', 'wp-job-portal')); ?></button>
                        <button class="wjpl-sb wjpl-clear" type="button"><?php echo esc_html(__('Clear selection', 'wp-job-portal')); ?></button>
                    </div>
                    <div class="wjpl-tb2">
                        <div class="wjpl-search">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                            <input class="wjpl-inp" type="search" name="q" value="<?php echo esc_attr($wpjobportal_f['q']); ?>" placeholder="<?php echo esc_attr(__('Search name, email or keywords', 'wp-job-portal')); ?>" aria-label="<?php echo esc_attr(__('Search job alerts', 'wp-job-portal')); ?>">
                        </div>
                        <select class="wjpl-sel" name="cat" data-wjpl-autosubmit aria-label="<?php echo esc_attr(__('Category', 'wp-job-portal')); ?>">
                            <option value=""><?php echo esc_html(__('All categories', 'wp-job-portal')); ?></option>
                            <?php foreach ($wpjobportal_list['categories'] as $cat) : ?>
                                <option value="<?php echo esc_attr($cat->id); ?>"<?php selected((int) $wpjobportal_f['cat'], (int) $cat->id); ?>><?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($cat->title)); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select class="wjpl-sel" name="freq" data-wjpl-autosubmit aria-label="<?php echo esc_attr(__('Frequency', 'wp-job-portal')); ?>">
                            <option value=""><?php echo esc_html(__('Any frequency', 'wp-job-portal')); ?></option>
                            <?php foreach ($wpjobportal_frequencies as $wpjobportal_value => $wpjobportal_label) : ?>
                                <option value="<?php echo esc_attr($wpjobportal_value); ?>"<?php selected($wpjobportal_f['freq'], (string) $wpjobportal_value); ?>><?php echo esc_html($wpjobportal_label); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span></span>
                        <span class="wjpl-tb2-acts">
                            <button class="wjpl-btn wjpl-btn-pri" type="submit"><?php echo esc_html(__('Search', 'wp-job-portal')); ?></button>
                            <a class="wjpl-linkbtn" href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_jobalert')); ?>"><?php echo esc_html(__('Reset', 'wp-job-portal')); ?></a>
                        </span>
                    </div>
                </form>

                <form class="wjpl-bulkform" method="post" action="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_jobalert')); ?>">
                    <input type="hidden" name="form_request" value="wpjobportal">
                    <input type="hidden" name="task" value="bulkjobalerts">
                    <input type="hidden" name="bulkop" value="">
                    <input type="hidden" name="deleteall" value="0">
                    <input type="hidden" name="returnto" value="<?php echo esc_attr($wpjobportal_here); ?>">
                    <input type="hidden" name="_wpnonce" value="<?php echo esc_attr($wpjobportal_nonce); ?>">
                    <div class="wjpl-tw">
                    <?php if (!empty($wpjobportal_list['rows'])) : ?>
                        <table class="wjpl-table">
                            <thead><tr>
                                <th class="wjpl-c-cb"><input class="wjpl-cb wjpl-all" type="checkbox" aria-label="<?php echo esc_attr(__('Select all job alerts', 'wp-job-portal')); ?>"></th>
                                <?php
                                $wpjobportal_th = function ($key, $wpjobportal_label, $class = '') use ($wpjobportal_f, $link) {
                                    $parts = explode('|', $wpjobportal_f['sort']);
                                    $on = $parts[0] === $key;
                                    $next = ($on && $parts[1] === '1') ? '-1' : '1';
                                    echo '<th class="' . esc_attr($class) . '"' . ($on ? ' aria-sort="' . ($parts[1] === '1' ? 'ascending' : 'descending') . '"' : '') . '><a href="' . esc_url($link(array('sort' => $key . '|' . $next))) . '">' . esc_html($wpjobportal_label) . ($on ? '<span aria-hidden="true">' . ($parts[1] === '1' ? ' ▲' : ' ▼') . '</span>' : '') . '</a></th>';
                                };
                                $wpjobportal_th('name', __('Alert', 'wp-job-portal'));
                                $wpjobportal_th('cat', __('Category', 'wp-job-portal'), 'wjpl-col-cat');
                                $wpjobportal_th('freq', __('Frequency', 'wp-job-portal'));
                                $wpjobportal_th('created', __('Created', 'wp-job-portal'), 'wjpl-col-posted');
                                $wpjobportal_th('stat', __('Status', 'wp-job-portal'));
                                ?>
                            </tr></thead>
                            <tbody>
                            <?php foreach ($wpjobportal_list['rows'] as $wpjobportal_a) :
                                $id = (int) $wpjobportal_a->id;
                                $wpjobportal_edit = admin_url('admin.php?page=wpjobportal_jobalert&wpjobportallt=formjobalert&wpjobportalid=' . $id);
                                $status = isset($wpjobportal_statuses[(int) $wpjobportal_a->status]) ? $wpjobportal_statuses[(int) $wpjobportal_a->status] : array('closed', (string) $wpjobportal_a->status);
                                $wpjobportal_name = trim((string) $wpjobportal_a->name) !== '' ? (string) $wpjobportal_a->name : sprintf(/* translators: %d: job alert id */ __('Job alert #%d', 'wp-job-portal'), $id);
                                $wpjobportal_sent = (string) $wpjobportal_a->lastmailsend !== '' && strpos((string) $wpjobportal_a->lastmailsend, '0000') !== 0 ? strtotime((string) $wpjobportal_a->lastmailsend) : 0;
                                ?>
                                <tr id="jobalert_<?php echo esc_attr($id); ?>">
                                    <td class="wjpl-c-cb"><input class="wjpl-cb wjpl-pick" type="checkbox" name="wpjobportal-cb[]" value="<?php echo esc_attr($id); ?>" aria-label="<?php echo esc_attr(sprintf(/* translators: %s: job alert name */ __('Select %s', 'wp-job-portal'), $wpjobportal_name)); ?>"></td>
                                    <td class="wjpl-c-main">
                                        <a class="wjpl-title" href="<?php echo esc_url($wpjobportal_edit); ?>"><?php echo esc_html($wpjobportal_name); ?></a>
                                        <div class="wjpl-meta wjpl-meta-plain">
                                            <?php
                                            $wpjobportal_bits = array();
                                            $wpjobportal_bits[] = trim((string) $wpjobportal_a->contactemail) !== '' ? esc_html($wpjobportal_a->contactemail) : '<span class="wjpl-warn">' . esc_html(__('No email', 'wp-job-portal')) . '</span>';
                                            if (trim((string) $wpjobportal_a->keywords) !== '') { $wpjobportal_bits[] = esc_html(sprintf(/* translators: %s: alert keywords */ __('keywords: %s', 'wp-job-portal'), $wpjobportal_a->keywords)); }
                                            if ((int) $wpjobportal_a->totalcities) { $wpjobportal_bits[] = esc_html(sprintf(/* translators: %s: number of cities */ _n('%s city', '%s cities', (int) $wpjobportal_a->totalcities, 'wp-job-portal'), number_format_i18n($wpjobportal_a->totalcities))); }
                                            echo implode(' · ', $wpjobportal_bits); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- each part escaped above
                                            ?>
                                        </div>
                                        <div class="wjpl-ra">
                                            <a href="<?php echo esc_url($wpjobportal_edit); ?>"><?php echo esc_html(__('Edit', 'wp-job-portal')); ?></a>
                                            <a class="wjpl-del" href="#" data-wjpl-row="delete" data-id="<?php echo esc_attr($id); ?>"><?php echo esc_html(__('Unsubscribe', 'wp-job-portal')); ?></a>
                                        </div>
                                    </td>
                                    <td class="wjpl-col-cat" data-l="<?php echo esc_attr(__('Category', 'wp-job-portal')); ?>"><?php echo trim((string) $wpjobportal_a->cat_title) !== '' ? esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_a->cat_title)) : '<span class="wjpl-muted">' . esc_html(__('Any category', 'wp-job-portal')) . '</span>'; ?></td>
                                    <td class="wjpl-nowrap" data-l="<?php echo esc_attr(__('Frequency', 'wp-job-portal')); ?>">
                                        <?php echo isset($wpjobportal_frequencies[(int) $wpjobportal_a->alerttype]) ? esc_html($wpjobportal_frequencies[(int) $wpjobportal_a->alerttype]) : '<span class="wjpl-muted">—</span>'; ?>
                                        <div class="wjpl-small"><?php echo $wpjobportal_sent ? esc_html(sprintf(/* translators: %s: date */ __('last sent %s', 'wp-job-portal'), date_i18n($wpjobportal_dateformat, $wpjobportal_sent))) : esc_html(__('not sent yet', 'wp-job-portal')); ?></div>
                                    </td>
                                    <td class="wjpl-col-posted wjpl-nowrap" data-l="<?php echo esc_attr(__('Created', 'wp-job-portal')); ?>"><?php echo esc_html(date_i18n($wpjobportal_dateformat, strtotime((string) $wpjobportal_a->created))); ?></td>
                                    <td data-l="<?php echo esc_attr(__('Status', 'wp-job-portal')); ?>"><span class="wjpl-badge wjpl-b-<?php echo esc_attr($status[0]); ?>"><?php echo esc_html($status[1]); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php elseif ($wpjobportal_list['total'] > 0) : ?>
                        <?php WPJOBPORTAL_Admin_List_UI::empty_state(__('Nothing on this page', 'wp-job-portal'), __('This list has fewer pages now.', 'wp-job-portal'), array(__('Go to the first page', 'wp-job-portal') => $link(array('pagenum' => null)))); ?>
                    <?php elseif ($wpjobportal_counts['all'] === 0) : ?>
                        <?php WPJOBPORTAL_Admin_List_UI::empty_state(__('No job alerts yet', 'wp-job-portal'), __('Visitors and job seekers subscribe to job alerts from the site and get matching jobs by email. You can add one here too.', 'wp-job-portal'), array(__('Add New Job Alert', 'wp-job-portal') => $wpjobportal_add_url)); ?>
                    <?php else : ?>
                        <?php WPJOBPORTAL_Admin_List_UI::empty_state(__('No job alerts match these filters', 'wp-job-portal'), __('Try fewer words, or reset the filters.', 'wp-job-portal'), array(__('Reset filters', 'wp-job-portal') => admin_url('admin.php?page=wpjobportal_jobalert'))); ?>
                    <?php endif; ?>
                    </div>
                </form>
                <?php WPJOBPORTAL_Admin_List_UI::footer($wpjobportal_list['total'], __('job alerts', 'wp-job-portal'), function ($page) use ($link) {
                    return $link(array('pagenum' => $page > 1 ? $page : null));
                }); ?>
            </section>

            <dialog id="wjpl-delete" class="wjpl-dialog" aria-labelledby="wjpl-delete-title">
                <h2 id="wjpl-delete-title"><?php echo esc_html(__('Unsubscribe job alerts?', 'wp-job-portal')); ?></h2>
                <p><?php
                    /* translators: %s: number of selected job alerts (filled in by script) */
                    echo wp_kses(sprintf(__('%s selected job alert(s) will be removed and stop sending emails. This cannot be undone.', 'wp-job-portal'), '<b class="wjpl-dlg-n">1</b>'), array('b' => array('class' => array())));
                ?></p>
                <div class="wjpl-dlg-acts">
                    <button type="button" class="wjpl-btn wjpl-btn-sec wjpl-dlg-cancel"><?php echo esc_html(__('Cancel', 'wp-job-portal')); ?></button>
                    <button type="button" class="wjpl-btn wjpl-btn-danger wjpl-dlg-ok"><?php echo esc_html(__('Unsubscribe', 'wp-job-portal')); ?></button>
                </div>
            </dialog>
        </div>
    </div>
</div>
