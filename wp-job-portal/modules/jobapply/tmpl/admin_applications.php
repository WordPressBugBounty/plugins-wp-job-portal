<?php
if (!defined('ABSPATH')) die('Restricted Access');
/*
 * Candidates › Applications (design 4, built from the Jobs list's parts).
 * New in 2.8.0: 2.7 showed applications one job at a time (admin_jobappliedresume.php),
 * which stays the place to change a status until the status model work (D-1 / W-1).
 */
WPJOBPORTAL_Admin_List_UI::enqueue();
if (!WPJOBPORTALincluder::getTemplate('templates/admin/header', array('wpjobportal_module' => 'jobapply'))) {
    return;
}

$wpjobportal_list = wpjobportal::$_data['applicationlist'];
$wpjobportal_f = $wpjobportal_list['filter'];
$wpjobportal_counts = $wpjobportal_list['counts'];
$link = function ($args = array()) use ($wpjobportal_f) {
    $current = $wpjobportal_f;
    if ($current['view'] === 'all') {
        $current['view'] = '';
    }
    if ($current['sort'] === 'applied|-1') {
        $current['sort'] = '';
    }
    return WPJOBPORTAL_Admin_List_UI::url('wpjobportal_jobapply', array('wpjobportallt' => 'applications') + $current, $args);
};
$wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
$wpjobportal_views = array(
    'all' => array(__('All', 'wp-job-portal'), ''),
    'new' => array(__('New', 'wp-job-portal'), '#4f46e5'),
    'inbox' => array(__('Inbox', 'wp-job-portal'), '#6b7280'),
    'shortlisted' => array(__('Short listed', 'wp-job-portal'), '#0284c7'),
    'hired' => array(__('Hired', 'wp-job-portal'), '#059669'),
    'rejected' => array(__('Rejected', 'wp-job-portal'), '#dc2626'),
    'spam' => array(__('Spam', 'wp-job-portal'), '#9ca3af'),
);
$wpjobportal_statuses = array(1 => array('closed', __('Inbox', 'wp-job-portal')), 5 => array('feat', __('Short listed', 'wp-job-portal')), 3 => array('ok', __('Hired', 'wp-job-portal')), 4 => array('bad', __('Rejected', 'wp-job-portal')), 2 => array('closed', __('Spam', 'wp-job-portal')));
$wpjobportal_upload = wp_upload_dir();
$wpjobportal_data_dir = wpjobportal::$_config->getConfigurationByConfigName('data_directory');
$wpjobportal_job_title = '';
foreach ($wpjobportal_list['jobs'] as $wpjobportal_j) {
    if ((int) $wpjobportal_j->id === (int) $wpjobportal_f['job']) {
        $wpjobportal_job_title = $wpjobportal_j->title;
    }
}
?>
<div id="wpjobportaladmin-wrapper" class="wjpl-page">
    <div id="wpjobportaladmin-leftmenu">
        <?php WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <div class="wjpl">
            <?php
            $wpjobportal_crumbs = array(array('label' => __('Candidates', 'wp-job-portal')));
            if ($wpjobportal_job_title !== '') {
                $wpjobportal_crumbs[] = array('label' => __('Applications', 'wp-job-portal'), 'url' => admin_url('admin.php?page=wpjobportal_jobapply&wpjobportallt=applications'));
                $wpjobportal_crumbs[] = array('label' => $wpjobportal_job_title);
            } else {
                $wpjobportal_crumbs[] = array('label' => __('Applications', 'wp-job-portal'));
            }
            WPJOBPORTAL_Admin_List_UI::top_bar($wpjobportal_crumbs);
            WPJOBPORTAL_Admin_List_UI::title_row(
                __('Applications', 'wp-job-portal'),
                /* translators: 1: number of applications, 2: number not opened yet */
                sprintf(_n('%1$s application · %2$s new', '%1$s applications · %2$s new', $wpjobportal_counts['all'], 'wp-job-portal'), number_format_i18n($wpjobportal_counts['all']), number_format_i18n($wpjobportal_counts['new'])),
                array()
            );
            ?>
            <section class="wjpl-card" aria-label="<?php echo esc_attr(__('Applications', 'wp-job-portal')); ?>">
                <form class="wjpl-filters" method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>">
                    <input type="hidden" name="page" value="wpjobportal_jobapply">
                    <input type="hidden" name="wpjobportallt" value="applications">
                    <?php if ($wpjobportal_f['view'] !== 'all') : ?><input type="hidden" name="view" value="<?php echo esc_attr($wpjobportal_f['view']); ?>"><?php endif; ?>
                    <div class="wjpl-tb1">
                        <?php WPJOBPORTAL_Admin_List_UI::chips($wpjobportal_views, $wpjobportal_counts, $wpjobportal_f['view'], function ($key) use ($link) {
                            return $link(array('view' => $key === 'all' ? null : $key));
                        }); ?>
                        <?php WPJOBPORTAL_Admin_List_UI::sort_select(array(
                            'applied|-1' => __('Newest first', 'wp-job-portal'),
                            'applied|1' => __('Oldest first', 'wp-job-portal'),
                            'name|1' => __('Candidate A–Z', 'wp-job-portal'),
                            'job|1' => __('Job A–Z', 'wp-job-portal'),
                            'rating|-1' => __('Highest rated', 'wp-job-portal'),
                        ), $wpjobportal_f['sort']); ?>
                    </div>
                    <div class="wjpl-tb2">
                        <div class="wjpl-search">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                            <input class="wjpl-inp" type="search" name="q" value="<?php echo esc_attr($wpjobportal_f['q']); ?>" placeholder="<?php echo esc_attr(__('Search candidate, email, resume, job or company', 'wp-job-portal')); ?>" aria-label="<?php echo esc_attr(__('Search applications', 'wp-job-portal')); ?>">
                        </div>
                        <select class="wjpl-sel" name="job" data-wjpl-autosubmit aria-label="<?php echo esc_attr(__('Job', 'wp-job-portal')); ?>">
                            <option value=""><?php echo esc_html(__('All jobs', 'wp-job-portal')); ?></option>
                            <?php foreach ($wpjobportal_list['jobs'] as $wpjobportal_j) : ?>
                                <option value="<?php echo esc_attr($wpjobportal_j->id); ?>"<?php selected((int) $wpjobportal_f['job'], (int) $wpjobportal_j->id); ?>><?php echo esc_html($wpjobportal_j->title); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select class="wjpl-sel" name="company" data-wjpl-autosubmit aria-label="<?php echo esc_attr(__('Company', 'wp-job-portal')); ?>">
                            <option value=""><?php echo esc_html(__('All companies', 'wp-job-portal')); ?></option>
                            <?php foreach ($wpjobportal_list['companies'] as $wpjobportal_co) : ?>
                                <option value="<?php echo esc_attr($wpjobportal_co->id); ?>"<?php selected((int) $wpjobportal_f['company'], (int) $wpjobportal_co->id); ?>><?php echo esc_html($wpjobportal_co->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span></span>
                        <span class="wjpl-tb2-acts">
                            <button class="wjpl-btn wjpl-btn-pri" type="submit"><?php echo esc_html(__('Search', 'wp-job-portal')); ?></button>
                            <a class="wjpl-linkbtn" href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_jobapply&wpjobportallt=applications')); ?>"><?php echo esc_html(__('Reset', 'wp-job-portal')); ?></a>
                        </span>
                    </div>
                </form>

                    <div class="wjpl-tw">
                    <?php if (!empty($wpjobportal_list['rows'])) : ?>
                        <table class="wjpl-table">
                            <thead><tr>
                                <?php
                                $wpjobportal_th = function ($key, $label, $class = '') use ($wpjobportal_f, $link) {
                                    $parts = explode('|', $wpjobportal_f['sort']);
                                    $on = $parts[0] === $key;
                                    $next = ($on && $parts[1] === '1') ? '-1' : '1';
                                    echo '<th class="' . esc_attr($class) . '"' . ($on ? ' aria-sort="' . ($parts[1] === '1' ? 'ascending' : 'descending') . '"' : '') . '><a href="' . esc_url($link(array('sort' => $key . '|' . $next))) . '">' . esc_html($label) . ($on ? '<span aria-hidden="true">' . ($parts[1] === '1' ? ' ▲' : ' ▼') . '</span>' : '') . '</a></th>';
                                };
                                $wpjobportal_th('name', __('Candidate', 'wp-job-portal'));
                                $wpjobportal_th('job', __('Job', 'wp-job-portal'), 'wjpl-col-cat');
                                $wpjobportal_th('rating', __('Rating', 'wp-job-portal'), 'wjpl-num');
                                $wpjobportal_th('applied', __('Applied', 'wp-job-portal'), 'wjpl-col-posted');
                                $wpjobportal_th('stat', __('Status', 'wp-job-portal'));
                                ?>
                            </tr></thead>
                            <tbody>
                            <?php foreach ($wpjobportal_list['rows'] as $wpjobportal_c) :
                                $id = (int) $wpjobportal_c->id;
                                $wpjobportal_name = trim((string) $wpjobportal_c->first_name . ' ' . (string) $wpjobportal_c->last_name);
                                $wpjobportal_shown = $wpjobportal_name !== '' ? $wpjobportal_name : sprintf(/* translators: %d: application id */ __('Application #%d', 'wp-job-portal'), $id);
                                $wpjobportal_resume = (int) $wpjobportal_c->cvid ? admin_url('admin.php?page=wpjobportal_resume&wpjobportallt=viewresume&wpjobportalid=' . (int) $wpjobportal_c->cvid) : '';
                                $wpjobportal_applicants = admin_url('admin.php?page=wpjobportal_jobapply&wpjobportallt=jobappliedresume&jobid=' . (int) $wpjobportal_c->jobid);
                                $wpjobportal_photo = trim((string) $wpjobportal_c->photo) !== '' ? $wpjobportal_upload['baseurl'] . '/' . $wpjobportal_data_dir . '/data/jobseeker/resume_' . (int) $wpjobportal_c->cvid . '/photo/' . $wpjobportal_c->photo : '';
                                $wpjobportal_initials = strtoupper(implode('', array_map(function ($w) { return function_exists('mb_substr') ? mb_substr($w, 0, 1) : substr($w, 0, 1); }, array_slice(preg_split('/\s+/', $wpjobportal_name, -1, PREG_SPLIT_NO_EMPTY), 0, 2))));
                                $status = isset($wpjobportal_statuses[(int) $wpjobportal_c->action_status]) ? $wpjobportal_statuses[(int) $wpjobportal_c->action_status] : array('closed', __('No status', 'wp-job-portal'));
                                if (isset($wpjobportal_c->applystatus) && (int) $wpjobportal_c->applystatus === WPJOBPORTAL_Application_Status::APPLY_WITHDRAWN) {
                                    $status = array('closed', __('Withdrawn by candidate', 'wp-job-portal'));
                                }
                                ?>
                                <tr id="application_<?php echo esc_attr($id); ?>">
                                    <td class="wjpl-c-main">
                                        <div class="wjpl-item">
                                            <?php if ($wpjobportal_photo !== '') : ?>
                                                <img class="wjpl-logo wjpl-round" src="<?php echo esc_url($wpjobportal_photo); ?>" alt="">
                                            <?php else : ?>
                                                <span class="wjpl-logo wjpl-round" aria-hidden="true"><?php echo esc_html($wpjobportal_initials !== '' ? $wpjobportal_initials : '—'); ?></span>
                                            <?php endif; ?>
                                            <div class="wjpl-item-body">
                                                <?php if ($wpjobportal_resume !== '') : ?><a class="wjpl-title" href="<?php echo esc_url($wpjobportal_resume); ?>"><?php echo esc_html($wpjobportal_shown); ?></a><?php else : ?><span class="wjpl-title"><?php echo esc_html($wpjobportal_shown); ?></span><?php endif; ?>
                                                <?php if (!(int) $wpjobportal_c->resumeview) : ?><span class="wjpl-badge wjpl-b-feat"><?php echo esc_html(__('New', 'wp-job-portal')); ?></span><?php endif; ?>
                                                <?php if ((int) $wpjobportal_c->quick_apply) : ?><span class="wjpl-badge wjpl-b-closed"><?php echo esc_html(__('Quick apply', 'wp-job-portal')); ?></span><?php endif; ?>
                                                <div class="wjpl-meta wjpl-meta-plain">
                                                    <?php
                                                    $wpjobportal_bits = array();
                                                    if (trim((string) $wpjobportal_c->application_title) !== '') { $wpjobportal_bits[] = esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_c->application_title)); }
                                                    if (trim((string) $wpjobportal_c->email_address) !== '') { $wpjobportal_bits[] = esc_html($wpjobportal_c->email_address); }
                                                    if ((int) $wpjobportal_c->coverletterid) { $wpjobportal_bits[] = esc_html(__('with a cover letter', 'wp-job-portal')); }
                                                    echo $wpjobportal_bits ? implode(' · ', $wpjobportal_bits) : '<span class="wjpl-warn">' . esc_html(__('Resume not found', 'wp-job-portal')) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- each part escaped above
                                                    ?>
                                                </div>
                                                <div class="wjpl-ra">
                                                    <?php if ($wpjobportal_resume !== '') : ?><a href="<?php echo esc_url($wpjobportal_resume); ?>"><?php echo esc_html(__('View resume', 'wp-job-portal')); ?></a><?php endif; ?>
                                                    <a href="<?php echo esc_url($wpjobportal_applicants); ?>"><?php echo esc_html(__('Change status on the job\'s applicants', 'wp-job-portal')); ?></a>
                                                    <a href="<?php echo esc_url($link(array('job' => (int) $wpjobportal_c->jobid))); ?>"><?php echo esc_html(__('Only this job', 'wp-job-portal')); ?></a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="wjpl-col-cat" data-l="<?php echo esc_attr(__('Job', 'wp-job-portal')); ?>">
                                        <a class="wjpl-meta-link" href="<?php echo esc_url($wpjobportal_applicants); ?>"><?php echo esc_html((string) $wpjobportal_c->jobtitle); ?></a>
                                        <?php if (trim((string) $wpjobportal_c->companyname) !== '') : ?><div class="wjpl-small wjpl-wrap"><?php echo esc_html($wpjobportal_c->companyname); ?></div><?php endif; ?>
                                    </td>
                                    <td class="wjpl-num wjpl-nowrap" data-l="<?php echo esc_attr(__('Rating', 'wp-job-portal')); ?>"><?php echo (float) $wpjobportal_c->rating > 0 ? esc_html('★ ' . number_format_i18n((float) $wpjobportal_c->rating, 1)) : '<span class="wjpl-muted">—</span>'; ?></td>
                                    <td class="wjpl-col-posted wjpl-nowrap" data-l="<?php echo esc_attr(__('Applied', 'wp-job-portal')); ?>"><?php echo esc_html(date_i18n($wpjobportal_dateformat, strtotime((string) $wpjobportal_c->apply_date))); ?></td>
                                    <td data-l="<?php echo esc_attr(__('Status', 'wp-job-portal')); ?>">
                                        <span class="wjpl-badge wjpl-b-<?php echo esc_attr($status[0]); ?>"><?php echo esc_html($status[1]); ?></span>
                                        <?php if ($wpjobportal_list['recruit'] && !empty($wpjobportal_c->stagetitle)) : ?><div class="wjpl-small"><span class="wjpl-dot" style="background:<?php echo esc_attr(sanitize_hex_color((string) $wpjobportal_c->stagecolor) ? $wpjobportal_c->stagecolor : '#64748b'); ?>"></span> <?php echo esc_html(sprintf(/* translators: %s: pipeline stage */ __('stage: %s', 'wp-job-portal'), $wpjobportal_c->stagetitle)); ?></div><?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php elseif ($wpjobportal_list['total'] > 0) : ?>
                        <?php WPJOBPORTAL_Admin_List_UI::empty_state(__('Nothing on this page', 'wp-job-portal'), __('This list has fewer pages now.', 'wp-job-portal'), array(__('Go to the first page', 'wp-job-portal') => $link(array('pagenum' => null)))); ?>
                    <?php elseif ($wpjobportal_counts['all'] === 0) : ?>
                        <?php WPJOBPORTAL_Admin_List_UI::empty_state(__('No applications yet', 'wp-job-portal'), __('When job seekers apply to jobs, their applications appear here.', 'wp-job-portal'), array(__('See the jobs', 'wp-job-portal') => admin_url('admin.php?page=wpjobportal_job'))); ?>
                    <?php else : ?>
                        <?php WPJOBPORTAL_Admin_List_UI::empty_state(__('No applications match these filters', 'wp-job-portal'), __('Try fewer words, or reset the filters.', 'wp-job-portal'), array(__('Reset filters', 'wp-job-portal') => admin_url('admin.php?page=wpjobportal_jobapply&wpjobportallt=applications'))); ?>
                    <?php endif; ?>
                    </div>
                <?php WPJOBPORTAL_Admin_List_UI::footer($wpjobportal_list['total'], __('applications', 'wp-job-portal'), function ($page) use ($link) {
                    return $link(array('pagenum' => $page > 1 ? $page : null));
                }); ?>
            </section>

        </div>
    </div>
</div>
