<?php
if (!defined('ABSPATH')) die('Restricted Access');
/*
 * Setup › Users (design 4, built from the Jobs list's parts).
 * Classic screen: admin_users_classic.php.
 */
WPJOBPORTAL_Admin_List_UI::enqueue();
if (!WPJOBPORTALincluder::getTemplate('templates/admin/header', array('wpjobportal_module' => 'user'))) {
    return;
}

$wpjobportal_list = wpjobportal::$_data['userlist'];
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
    return WPJOBPORTAL_Admin_List_UI::url('wpjobportal_user', $current, $args);
};
$wpjobportal_here = $link(array('pagenum' => WPJOBPORTALpagination::$_currentpage > 1 ? WPJOBPORTALpagination::$_currentpage : null));
$wpjobportal_nonce = wp_create_nonce('wpjobportal_user_nonce');
$wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
$self = (int) WPJOBPORTALincluder::getJSModel('user')->getUserIDByWPUid(get_current_user_id());
$wpjobportal_views = array(
    'all' => array(__('All', 'wp-job-portal'), ''),
    'employer' => array(__('Employers', 'wp-job-portal'), '#4f46e5'),
    'jobseeker' => array(__('Job seekers', 'wp-job-portal'), '#059669'),
    'norole' => array(__('No portal role', 'wp-job-portal'), '#6b7280'),
    'disabled' => array(__('Disabled', 'wp-job-portal'), '#dc2626'),
);
// The last two only when there is something in them (or one is open).
foreach (array('norole', 'disabled') as $wpjobportal_key) {
    if ($wpjobportal_counts[$wpjobportal_key] === 0 && $wpjobportal_f['view'] !== $wpjobportal_key) {
        unset($wpjobportal_views[$wpjobportal_key]);
    }
}
$wpjobportal_roles = array(1 => array('feat', __('Employer', 'wp-job-portal')), 2 => array('ok', __('Job seeker', 'wp-job-portal')));
$wpjobportal_upload = wp_upload_dir();
$wpjobportal_data_dir = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
$wpjobportal_assign_url = admin_url('admin.php?page=wpjobportal_user&wpjobportallt=assignrole');
?>
<div id="wpjobportaladmin-wrapper" class="wjpl-page">
    <div id="wpjobportaladmin-leftmenu">
        <?php WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <div class="wjpl" data-delete-confirm="<?php echo esc_attr(__('Are you sure to delete', 'wp-job-portal') . ' ?'); ?>">
            <?php
            WPJOBPORTAL_Admin_List_UI::top_bar(array(array('label' => __('Setup', 'wp-job-portal')), array('label' => __('Users', 'wp-job-portal'))));
            WPJOBPORTAL_Admin_List_UI::title_row(
                __('Users', 'wp-job-portal'),
                /* translators: 1: number of users, 2: employers, 3: job seekers */
                sprintf(_n('%1$s user · %2$s employers · %3$s job seekers', '%1$s users · %2$s employers · %3$s job seekers', $wpjobportal_counts['all'], 'wp-job-portal'), number_format_i18n($wpjobportal_counts['all']), number_format_i18n($wpjobportal_counts['employer']), number_format_i18n($wpjobportal_counts['jobseeker'])),
                array(
                    __('User profile fields', 'wp-job-portal') => admin_url('admin.php?page=wpjobportal_fieldordering&ff=4'),
                    __('WordPress users', 'wp-job-portal') => admin_url('users.php'),
                ),
                array('label' => __('Assign Role', 'wp-job-portal'), 'url' => $wpjobportal_assign_url)
            );
            ?>
            <section class="wjpl-card" aria-label="<?php echo esc_attr(__('Users', 'wp-job-portal')); ?>">
                <form class="wjpl-filters" method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>">
                    <input type="hidden" name="page" value="wpjobportal_user">
                    <?php if ($wpjobportal_f['view'] !== 'all') : ?><input type="hidden" name="view" value="<?php echo esc_attr($wpjobportal_f['view']); ?>"><?php endif; ?>
                    <div class="wjpl-tb1">
                        <?php WPJOBPORTAL_Admin_List_UI::chips($wpjobportal_views, $wpjobportal_counts, $wpjobportal_f['view'], function ($wpjobportal_key) use ($link) {
                            return $link(array('view' => $wpjobportal_key === 'all' ? null : $wpjobportal_key));
                        }); ?>
                        <?php WPJOBPORTAL_Admin_List_UI::sort_select(array(
                            'created|-1' => __('Newest first', 'wp-job-portal'),
                            'created|1' => __('Oldest first', 'wp-job-portal'),
                            'name|1' => __('Name A–Z', 'wp-job-portal'),
                            'name|-1' => __('Name Z–A', 'wp-job-portal'),
                            'login|1' => __('Login A–Z', 'wp-job-portal'),
                        ), $wpjobportal_f['sort']); ?>
                    </div>
                    <div class="wjpl-selbar" role="toolbar" aria-label="<?php echo esc_attr(__('Bulk actions', 'wp-job-portal')); ?>" hidden>
                        <b><span class="wjpl-selcount">0</span> <?php echo esc_html(__('selected', 'wp-job-portal')); ?></b>
                        <button class="wjpl-sb" type="button" data-wjpl-bulk="enable"><?php echo esc_html(__('Enable', 'wp-job-portal')); ?></button>
                        <button class="wjpl-sb" type="button" data-wjpl-bulk="disable"><?php echo esc_html(__('Disable', 'wp-job-portal')); ?></button>
                        <button class="wjpl-sb wjpl-del" type="button" data-wjpl-bulk="delete"><?php echo esc_html(__('Delete', 'wp-job-portal')); ?></button>
                        <button class="wjpl-sb wjpl-clear" type="button"><?php echo esc_html(__('Clear selection', 'wp-job-portal')); ?></button>
                    </div>
                    <div class="wjpl-tb2">
                        <div class="wjpl-search">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                            <input class="wjpl-inp" type="search" name="q" value="<?php echo esc_attr($wpjobportal_f['q']); ?>" placeholder="<?php echo esc_attr(__('Search name, email, login, company or resume', 'wp-job-portal')); ?>" aria-label="<?php echo esc_attr(__('Search users', 'wp-job-portal')); ?>">
                        </div>
                        <span></span><span></span><span></span>
                        <span class="wjpl-tb2-acts">
                            <button class="wjpl-btn wjpl-btn-pri" type="submit"><?php echo esc_html(__('Search', 'wp-job-portal')); ?></button>
                            <a class="wjpl-linkbtn" href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_user')); ?>"><?php echo esc_html(__('Reset', 'wp-job-portal')); ?></a>
                        </span>
                    </div>
                </form>

                <form class="wjpl-bulkform" method="post" action="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_user')); ?>">
                    <input type="hidden" name="form_request" value="wpjobportal">
                    <input type="hidden" name="task" value="bulkusers">
                    <input type="hidden" name="bulkop" value="">
                    <input type="hidden" name="deleteall" value="0">
                    <input type="hidden" name="returnto" value="<?php echo esc_attr($wpjobportal_here); ?>">
                    <input type="hidden" name="_wpnonce" value="<?php echo esc_attr($wpjobportal_nonce); ?>">
                    <div class="wjpl-tw">
                    <?php if (!empty($wpjobportal_list['rows'])) : ?>
                        <table class="wjpl-table">
                            <thead><tr>
                                <th class="wjpl-c-cb"><input class="wjpl-cb wjpl-all" type="checkbox" aria-label="<?php echo esc_attr(__('Select all users', 'wp-job-portal')); ?>"></th>
                                <?php
                                $wpjobportal_th = function ($wpjobportal_key, $label, $class = '') use ($wpjobportal_f, $link) {
                                    $parts = explode('|', $wpjobportal_f['sort']);
                                    $on = $parts[0] === $wpjobportal_key;
                                    $next = ($on && $parts[1] === '1') ? '-1' : '1';
                                    echo '<th class="' . esc_attr($class) . '"' . ($on ? ' aria-sort="' . ($parts[1] === '1' ? 'ascending' : 'descending') . '"' : '') . '><a href="' . esc_url($link(array('sort' => $wpjobportal_key . '|' . $next))) . '">' . esc_html($label) . ($on ? '<span aria-hidden="true">' . ($parts[1] === '1' ? ' ▲' : ' ▼') . '</span>' : '') . '</a></th>';
                                };
                                $wpjobportal_th('name', __('User', 'wp-job-portal'));
                                $wpjobportal_th('role', __('Role', 'wp-job-portal'));
                                echo '<th class="wjpl-col-cat">' . esc_html(__('Company / resume', 'wp-job-portal')) . '</th>';
                                $wpjobportal_th('created', __('Joined', 'wp-job-portal'), 'wjpl-col-posted');
                                $wpjobportal_th('stat', __('Status', 'wp-job-portal'));
                                ?>
                            </tr></thead>
                            <tbody>
                            <?php foreach ($wpjobportal_list['rows'] as $wpjobportal_u) :
                                $id = (int) $wpjobportal_u->id;
                                $wpjobportal_name = trim((string) $wpjobportal_u->first_name . ' ' . (string) $wpjobportal_u->last_name);
                                $wpjobportal_shown = $wpjobportal_name !== '' ? $wpjobportal_name : ((string) $wpjobportal_u->user_login !== '' ? (string) $wpjobportal_u->user_login : sprintf(/* translators: %d: user id */ __('User #%d', 'wp-job-portal'), $id));
                                $wpjobportal_detail = admin_url('admin.php?page=wpjobportal_user&wpjobportallt=userdetail&id=' . $id);
                                $wpjobportal_photo = trim((string) $wpjobportal_u->photo) !== '' ? $wpjobportal_upload['baseurl'] . '/' . $wpjobportal_data_dir . '/data/profile/profile_' . (int) $wpjobportal_u->uid . '/profile/' . $wpjobportal_u->photo : '';
                                $wpjobportal_initials = strtoupper(implode('', array_map(function ($w) { return function_exists('mb_substr') ? mb_substr($w, 0, 1) : substr($w, 0, 1); }, array_slice(preg_split('/\s+/', $wpjobportal_shown, -1, PREG_SPLIT_NO_EMPTY), 0, 2))));
                                $wpjobportal_is_self = $id === $self;
                                $wpjobportal_enabled = (int) $wpjobportal_u->status === 1;
                                ?>
                                <tr id="user_<?php echo esc_attr($id); ?>">
                                    <td class="wjpl-c-cb"><?php if (!$wpjobportal_is_self) : ?><input class="wjpl-cb wjpl-pick" type="checkbox" name="wpjobportal-cb[]" value="<?php echo esc_attr($id); ?>" aria-label="<?php echo esc_attr(sprintf(/* translators: %s: user name */ __('Select %s', 'wp-job-portal'), $wpjobportal_shown)); ?>"><?php endif; ?></td>
                                    <td class="wjpl-c-main">
                                        <div class="wjpl-item">
                                            <?php if ($wpjobportal_photo !== '') : ?>
                                                <img class="wjpl-logo wjpl-round" src="<?php echo esc_url($wpjobportal_photo); ?>" alt="">
                                            <?php else : ?>
                                                <span class="wjpl-logo wjpl-round" aria-hidden="true"><?php echo esc_html($wpjobportal_initials !== '' ? $wpjobportal_initials : '—'); ?></span>
                                            <?php endif; ?>
                                            <div class="wjpl-item-body">
                                                <a class="wjpl-title" href="<?php echo esc_url($wpjobportal_detail); ?>"><?php echo esc_html($wpjobportal_shown); ?></a>
                                                <?php if ($wpjobportal_is_self) : ?><span class="wjpl-badge wjpl-b-closed"><?php echo esc_html(__('You', 'wp-job-portal')); ?></span><?php endif; ?>
                                                <div class="wjpl-meta wjpl-meta-plain">
                                                    <?php
                                                    $wpjobportal_bits = array();
                                                    if (trim((string) $wpjobportal_u->emailaddress) !== '') { $wpjobportal_bits[] = esc_html($wpjobportal_u->emailaddress); }
                                                    if ((string) $wpjobportal_u->user_login !== '') { $wpjobportal_bits[] = esc_html(sprintf(/* translators: %s: WordPress login */ __('login %s', 'wp-job-portal'), $wpjobportal_u->user_login)); }
                                                    else { $wpjobportal_bits[] = '<span class="wjpl-warn">' . esc_html(__('WordPress account missing', 'wp-job-portal')) . '</span>'; }
                                                    $wpjobportal_bits[] = esc_html('#' . $id);
                                                    echo implode(' · ', $wpjobportal_bits); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- each part escaped above
                                                    ?>
                                                </div>
                                                <div class="wjpl-ra">
                                                    <a href="<?php echo esc_url($wpjobportal_detail); ?>"><?php echo esc_html(__('Details', 'wp-job-portal')); ?></a>
                                                    <?php if ($wpjobportal_u->wprole !== 'administrator') : ?>
                                                        <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_user&wpjobportallt=changerole&wpjobportalid=' . $id)); ?>"><?php echo esc_html(__('Change Role', 'wp-job-portal')); ?></a>
                                                    <?php endif; ?>
                                                    <?php if ($wpjobportal_u->wpuid) : ?>
                                                        <a href="<?php echo esc_url(admin_url('user-edit.php?user_id=' . (int) $wpjobportal_u->wpuid)); ?>"><?php echo esc_html(__('WordPress profile', 'wp-job-portal')); ?></a>
                                                    <?php endif; ?>
                                                    <?php if (!$wpjobportal_is_self) : ?>
                                                        <a href="#" data-wjpl-row="<?php echo $wpjobportal_enabled ? 'disable' : 'enable'; ?>" data-id="<?php echo esc_attr($id); ?>"<?php echo $wpjobportal_enabled ? '' : ' class="wjpl-ok"'; ?>><?php echo esc_html($wpjobportal_enabled ? __('Disable', 'wp-job-portal') : __('Enable', 'wp-job-portal')); ?></a>
                                                        <a class="wjpl-del" href="#" data-wjpl-row="delete" data-id="<?php echo esc_attr($id); ?>"><?php echo esc_html(__('Delete', 'wp-job-portal')); ?></a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td data-l="<?php echo esc_attr(__('Role', 'wp-job-portal')); ?>">
                                        <?php if (isset($wpjobportal_roles[(int) $wpjobportal_u->roleid])) : ?>
                                            <span class="wjpl-badge wjpl-b-<?php echo esc_attr($wpjobportal_roles[(int) $wpjobportal_u->roleid][0]); ?>"><?php echo esc_html($wpjobportal_roles[(int) $wpjobportal_u->roleid][1]); ?></span>
                                        <?php else : ?>
                                            <span class="wjpl-muted"><?php echo esc_html(__('No portal role', 'wp-job-portal')); ?></span>
                                        <?php endif; ?>
                                        <div class="wjpl-small wjpl-wrap"><?php echo esc_html($wpjobportal_u->wprolename !== '' ? sprintf(/* translators: %s: WordPress role name */ __('WordPress: %s', 'wp-job-portal'), $wpjobportal_u->wprolename) : ''); ?></div>
                                    </td>
                                    <td class="wjpl-col-cat" data-l="<?php echo esc_attr(__('Company / resume', 'wp-job-portal')); ?>">
                                        <?php
                                        $wpjobportal_owns = array();
                                        $wpjobportal_first = '';
                                        if ((int) $wpjobportal_u->totalcompanies || (int) $wpjobportal_u->totaljobs) {
                                            $wpjobportal_first = (string) $wpjobportal_u->companyname;
                                            /* translators: %s: number of companies */
                                            $wpjobportal_owns[] = sprintf(_n('%s company', '%s companies', (int) $wpjobportal_u->totalcompanies, 'wp-job-portal'), number_format_i18n($wpjobportal_u->totalcompanies));
                                            /* translators: %s: number of jobs */
                                            $wpjobportal_owns[] = sprintf(_n('%s job', '%s jobs', (int) $wpjobportal_u->totaljobs, 'wp-job-portal'), number_format_i18n($wpjobportal_u->totaljobs));
                                        }
                                        if ((int) $wpjobportal_u->totalresumes || (int) $wpjobportal_u->totalapps) {
                                            if ($wpjobportal_first === '') { $wpjobportal_first = wpjobportal::wpjobportal_getVariableValue((string) $wpjobportal_u->resumetitle); }
                                            /* translators: %s: number of resumes */
                                            $wpjobportal_owns[] = sprintf(_n('%s resume', '%s resumes', (int) $wpjobportal_u->totalresumes, 'wp-job-portal'), number_format_i18n($wpjobportal_u->totalresumes));
                                            /* translators: %s: number of applications */
                                            $wpjobportal_owns[] = sprintf(_n('%s application', '%s applications', (int) $wpjobportal_u->totalapps, 'wp-job-portal'), number_format_i18n($wpjobportal_u->totalapps));
                                        }
                                        if ($wpjobportal_owns) :
                                            echo esc_html(trim($wpjobportal_first) !== '' ? $wpjobportal_first : '—'); ?>
                                            <div class="wjpl-small wjpl-wrap"><?php echo esc_html(implode(' · ', $wpjobportal_owns)); ?></div>
                                        <?php else : ?>
                                            <span class="wjpl-muted"><?php echo esc_html(__('Nothing yet', 'wp-job-portal')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="wjpl-col-posted wjpl-nowrap" data-l="<?php echo esc_attr(__('Joined', 'wp-job-portal')); ?>"><?php echo esc_html(date_i18n($wpjobportal_dateformat, strtotime((string) $wpjobportal_u->created))); ?></td>
                                    <td data-l="<?php echo esc_attr(__('Status', 'wp-job-portal')); ?>"><span class="wjpl-badge wjpl-b-<?php echo $wpjobportal_enabled ? 'ok' : 'bad'; ?>"><?php echo esc_html($wpjobportal_enabled ? __('Enabled', 'wp-job-portal') : __('Disabled', 'wp-job-portal')); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php elseif ($wpjobportal_list['total'] > 0) : ?>
                        <?php WPJOBPORTAL_Admin_List_UI::empty_state(__('Nothing on this page', 'wp-job-portal'), __('This list has fewer pages now.', 'wp-job-portal'), array(__('Go to the first page', 'wp-job-portal') => $link(array('pagenum' => null)))); ?>
                    <?php elseif ($wpjobportal_counts['all'] === 0) : ?>
                        <?php WPJOBPORTAL_Admin_List_UI::empty_state(__('No users yet', 'wp-job-portal'), __('People who register as employers or job seekers appear here. You can also give a portal role to an existing WordPress user.', 'wp-job-portal'), array(__('Assign Role', 'wp-job-portal') => $wpjobportal_assign_url)); ?>
                    <?php else : ?>
                        <?php WPJOBPORTAL_Admin_List_UI::empty_state(__('No users match these filters', 'wp-job-portal'), __('Try fewer words, or reset the filters.', 'wp-job-portal'), array(__('Reset filters', 'wp-job-portal') => admin_url('admin.php?page=wpjobportal_user'))); ?>
                    <?php endif; ?>
                    </div>
                </form>
                <?php WPJOBPORTAL_Admin_List_UI::footer($wpjobportal_list['total'], __('users', 'wp-job-portal'), function ($page) use ($link) {
                    return $link(array('pagenum' => $page > 1 ? $page : null));
                }); ?>
            </section>

            <dialog id="wjpl-delete" class="wjpl-dialog" aria-labelledby="wjpl-delete-title">
                <h2 id="wjpl-delete-title"><?php echo esc_html(__('Delete users?', 'wp-job-portal')); ?></h2>
                <p><?php
                    /* translators: %s: number of selected users (filled in by script) */
                    echo wp_kses(sprintf(__('%s selected user(s) will be deleted, with their WordPress accounts. Users who still own companies, jobs or resumes are kept unless you also tick the box below.', 'wp-job-portal'), '<b class="wjpl-dlg-n">1</b>'), array('b' => array('class' => array())));
                ?></p>
                <label class="wjpl-ck"><input class="wjpl-cb" type="checkbox" name="wjpl-deleteall" value="1"><?php echo esc_html(__('Also delete everything these users own (companies, jobs, resumes, applications and files). This cannot be undone.', 'wp-job-portal')); ?></label>
                <div class="wjpl-dlg-acts">
                    <button type="button" class="wjpl-btn wjpl-btn-sec wjpl-dlg-cancel"><?php echo esc_html(__('Cancel', 'wp-job-portal')); ?></button>
                    <button type="button" class="wjpl-btn wjpl-btn-danger wjpl-dlg-ok"><?php echo esc_html(__('Delete', 'wp-job-portal')); ?></button>
                </div>
            </dialog>
        </div>
    </div>
</div>
