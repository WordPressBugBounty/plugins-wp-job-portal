<?php
if (!defined('ABSPATH')) die('Restricted Access');
/*
 * Approvals (design 4). Everything waiting lists what waits across all queues;
 * each queue's tab is the same page for one queue (?type=jobs, and the 2.7
 * queue URLs such as wpjobportallt=jobqueue), with all of its items, search and
 * delete. Approve / Reject / Delete run the queue's own tasks and come back.
 */
WPJOBPORTAL_Admin_List_UI::enqueue();
// The queue tasks leave their messages under their own module.
WPJOBPORTALincluder::getTemplate('templates/admin/header', array('wpjobportal_module' => 'job'));
WPJOBPORTALincluder::getTemplate('templates/admin/header', array('wpjobportal_module' => 'company'));
WPJOBPORTALincluder::getTemplate('templates/admin/header', array('wpjobportal_module' => 'resume'));
foreach (array('departments', 'coverletter', 'folder', 'message') as $wpjobportal_addon) {
    if (in_array($wpjobportal_addon, (array) wpjobportal::$_active_addons, true)) {
        WPJOBPORTALincluder::getTemplate('templates/admin/header', array('wpjobportal_module' => $wpjobportal_addon === 'departments' ? 'departments' : $wpjobportal_addon));
    }
}

$wpjobportal_data = wpjobportal::$_data['approvals'];
$wpjobportal_rows = $wpjobportal_data['rows'];
$wpjobportal_queues = $wpjobportal_data['queues'];
$wpjobportal_view = isset($wpjobportal_data['type']) ? $wpjobportal_data['type'] : '';
$wpjobportal_q = isset($wpjobportal_data['q']) ? $wpjobportal_data['q'] : '';
$wpjobportal_here = add_query_arg(array_map('rawurlencode', array_filter(array('page' => 'wpjobportal_approvals', 'type' => $wpjobportal_view, 'q' => $wpjobportal_q))), admin_url('admin.php'));
$wpjobportal_dateformat = wpjobportal::$_configuration['date_format'];
// The task, nonce and edit screen behind each kind of item.
$wpjobportal_actions = array(
    'jobs' => array('page' => 'wpjobportal_job', 'nonce' => 'wpjobportal_job_nonce', 'edit' => 'wpjobportal_job&wpjobportallt=formjob&wpjobportalid=',
        'approval' => array('approveQueueJob', 'rejectQueueJob'), 'feature' => array('approveQueueFeaturedJob', 'rejectQueueFeaturedJob')),
    'companies' => array('page' => 'wpjobportal_company', 'nonce' => 'wpjobportal_company_nonce', 'edit' => 'wpjobportal_company&wpjobportallt=formcompany&wpjobportalid=',
        'approval' => array('approveQueueCompany', 'rejectQueueCompany'), 'feature' => array('approveQueueFeaturedCompany', 'rejectQueueFeatureCompany')),
    'resumes' => array('page' => 'wpjobportal_resume', 'nonce' => 'wpjobportal_resume_nonce', 'edit' => 'wpjobportal_resume&wpjobportallt=formresume&wpjobportalid=',
        'approval' => array('approveQueueResume', 'rejectQueueResume')),
    'departments' => array('page' => 'wpjobportal_departments', 'nonce' => 'wpjobportal_department_nonce', 'edit' => 'wpjobportal_departments&wpjobportallt=formdepartment&wpjobportalid=',
        'approval' => array('approve', 'reject'), 'list_ids' => true),
    'coverletters' => array('page' => 'wpjobportal_coverletter', 'nonce' => 'wpjobportal_coverletter_nonce', 'edit' => 'wpjobportal_coverletter&wpjobportallt=formcoverletter&wpjobportalid=',
        'approval' => array('approve', 'reject'), 'list_ids' => true),
    'folders' => array('page' => 'wpjobportal_folder', 'nonce' => 'wpjobportal_folder_nonce', 'edit' => 'wpjobportal_folder&wpjobportallt=formfolder&wpjobportalid=',
        'approval' => array('approve', 'reject'), 'list_ids' => true),
    'messages' => array('page' => 'wpjobportal_message', 'nonce' => 'wpjobportal_message_nonce', 'edit' => 'wpjobportal_message&wpjobportallt=messagedetail&wpjobportalid=',
        'approval' => array('publishmessages', 'unpublishmessages'), 'id_param' => 'messageid'),
);
// The delete task behind each queue (callfrom 2 sends jobs and companies back to their queue).
$wpjobportal_removes = array('jobs' => 'remove', 'companies' => 'remove', 'resumes' => 'removeresume', 'departments' => 'remove', 'coverletters' => 'remove', 'folders' => 'remove', 'messages' => 'remove');
$wpjobportal_nonces = array();
$wpjobportal_task_url = function ($type, $task, $id) use ($wpjobportal_actions, $wpjobportal_here, &$wpjobportal_nonces) {
    $a = $wpjobportal_actions[$type];
    if (!isset($wpjobportal_nonces[$a['nonce']])) {
        $wpjobportal_nonces[$a['nonce']] = wp_create_nonce($a['nonce']);
    }
    $args = array('page' => $a['page'], 'action' => 'wpjobportaltask', 'task' => $task, '_wpnonce' => $wpjobportal_nonces[$a['nonce']], 'returnto' => $wpjobportal_here);
    $args[!empty($a['id_param']) ? $a['id_param'] : (!empty($a['list_ids']) ? 'wpjobportal-cb[]' : 'id')] = (int) $id;
    return add_query_arg(array_map('rawurlencode', $args), admin_url('admin.php'));
};
$wpjobportal_type_labels = array(
    'jobs' => __('Job', 'wp-job-portal'), 'companies' => __('Company', 'wp-job-portal'), 'resumes' => __('Resume', 'wp-job-portal'),
    'departments' => __('Department', 'wp-job-portal'), 'coverletters' => __('Cover letter', 'wp-job-portal'), 'folders' => __('Folder', 'wp-job-portal'), 'messages' => __('Message', 'wp-job-portal'),
);
$wpjobportal_counts = WPJOBPORTAL_Admin_Menu_Service::counts();
?>
<div id="wpjobportaladmin-wrapper" class="wjpl-page">
    <div id="wpjobportaladmin-leftmenu">
        <?php WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <div class="wjpl" data-delete-confirm="<?php echo esc_attr(__('Are you sure to delete', 'wp-job-portal') . ' ?'); ?>">
            <?php
            $wpjobportal_one = $wpjobportal_view !== '' ? $wpjobportal_queues[$wpjobportal_view] : null;
            $wpjobportal_crumbs = array(array('label' => __('Approvals', 'wp-job-portal'), 'url' => $wpjobportal_one ? admin_url('admin.php?page=wpjobportal_approvals') : ''));
            if ($wpjobportal_one) {
                $wpjobportal_crumbs[] = array('label' => $wpjobportal_one['label']);
            }
            WPJOBPORTAL_Admin_List_UI::top_bar($wpjobportal_crumbs);
            $wpjobportal_waiting = $wpjobportal_one ? (int) (isset($wpjobportal_counts['approvals_' . $wpjobportal_view]) ? $wpjobportal_counts['approvals_' . $wpjobportal_view] : count($wpjobportal_rows)) : (int) $wpjobportal_counts['approvals'];
            WPJOBPORTAL_Admin_List_UI::title_row(
                $wpjobportal_one ? sprintf(/* translators: %s: queue name, e.g. Jobs */ __('%s waiting for approval', 'wp-job-portal'), $wpjobportal_one['label']) : __('Approvals', 'wp-job-portal'),
                /* translators: %s: number of items waiting */
                sprintf(_n('%s item waiting for a decision', '%s items waiting for a decision', $wpjobportal_waiting, 'wp-job-portal'), number_format_i18n($wpjobportal_waiting)),
                array(),
                array()
            );
            WPJOBPORTAL_Admin_List_UI::approvals_tabs($wpjobportal_one ? $wpjobportal_view : 'all');
            $wpjobportal_act_one = $wpjobportal_one && isset($wpjobportal_actions[$wpjobportal_view]) ? $wpjobportal_actions[$wpjobportal_view] : null;
            ?>
            <section class="wjpl-card" aria-label="<?php echo esc_attr($wpjobportal_one ? $wpjobportal_one['label'] : __('Everything waiting', 'wp-job-portal')); ?>">
                <?php if ($wpjobportal_one) : ?>
                    <form class="wjpl-filters" method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>">
                        <input type="hidden" name="page" value="wpjobportal_approvals">
                        <input type="hidden" name="type" value="<?php echo esc_attr($wpjobportal_view); ?>">
                        <div class="wjpl-selbar" role="toolbar" aria-label="<?php echo esc_attr(__('Bulk actions', 'wp-job-portal')); ?>" hidden>
                            <b><span class="wjpl-selcount">0</span> <?php echo esc_html(__('selected', 'wp-job-portal')); ?></b>
                            <button class="wjpl-sb wjpl-del" type="button" data-wjpl-bulk="delete"><?php echo esc_html(__('Delete', 'wp-job-portal')); ?></button>
                            <button class="wjpl-sb wjpl-clear" type="button"><?php echo esc_html(__('Clear selection', 'wp-job-portal')); ?></button>
                        </div>
                        <div class="wjpl-tb2">
                            <div class="wjpl-search">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                                <input class="wjpl-inp" type="search" name="q" value="<?php echo esc_attr($wpjobportal_q); ?>" placeholder="<?php echo esc_attr(__('Search by name or who sent it', 'wp-job-portal')); ?>" aria-label="<?php echo esc_attr(__('Search', 'wp-job-portal')); ?>">
                            </div>
                            <span></span><span></span><span></span>
                            <span class="wjpl-tb2-acts">
                                <button class="wjpl-btn wjpl-btn-pri" type="submit"><?php echo esc_html(__('Search', 'wp-job-portal')); ?></button>
                                <a class="wjpl-linkbtn" href="<?php echo esc_url(add_query_arg(array('page' => 'wpjobportal_approvals', 'type' => $wpjobportal_view), admin_url('admin.php'))); ?>"><?php echo esc_html(__('Reset', 'wp-job-portal')); ?></a>
                            </span>
                        </div>
                    </form>
                <?php endif; ?>
                <?php if ($wpjobportal_act_one) : ?>
                <form class="wjpl-bulkform" method="post" action="<?php echo esc_url(admin_url('admin.php?page=' . $wpjobportal_act_one['page'])); ?>" data-task-delete="<?php echo esc_attr($wpjobportal_removes[$wpjobportal_view]); ?>">
                    <input type="hidden" name="form_request" value="wpjobportal">
                    <input type="hidden" name="task" value="">
                    <input type="hidden" name="bulkop" value="">
                    <input type="hidden" name="deleteall" value="0">
                    <input type="hidden" name="callfrom" value="2">
                    <input type="hidden" name="returnto" value="<?php echo esc_attr($wpjobportal_here); ?>">
                    <input type="hidden" name="_wpnonce" value="<?php echo esc_attr(wp_create_nonce($wpjobportal_act_one['nonce'])); ?>">
                <?php endif; ?>
                <div class="wjpl-tw">
                <?php if (!empty($wpjobportal_rows)) : ?>
                    <table class="wjpl-table">
                        <thead><tr>
                            <?php if ($wpjobportal_act_one) : ?><th class="wjpl-c-cb"><input class="wjpl-cb wjpl-all" type="checkbox" aria-label="<?php echo esc_attr(__('Select all', 'wp-job-portal')); ?>"></th><?php endif; ?>
                            <th><?php echo esc_html(__('Waiting', 'wp-job-portal')); ?></th>
                            <?php if (!$wpjobportal_one) : ?><th class="wjpl-col-cat"><?php echo esc_html(__('Type', 'wp-job-portal')); ?></th><?php endif; ?>
                            <th class="wjpl-col-sal"><?php echo esc_html(__('From', 'wp-job-portal')); ?></th>
                            <th class="wjpl-col-posted"><?php echo esc_html(__('Submitted', 'wp-job-portal')); ?></th>
                            <th><?php echo esc_html(__('Needs', 'wp-job-portal')); ?></th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($wpjobportal_rows as $wpjobportal_row) :
                            $wpjobportal_type = $wpjobportal_row->type;
                            $wpjobportal_act = isset($wpjobportal_actions[$wpjobportal_type]) ? $wpjobportal_actions[$wpjobportal_type] : null;
                            $wpjobportal_queue_url = admin_url('admin.php?page=' . $wpjobportal_queues[$wpjobportal_type]['url']);
                            $wpjobportal_open = $wpjobportal_act ? admin_url('admin.php?page=' . $wpjobportal_act['edit'] . (int) $wpjobportal_row->id) : $wpjobportal_queue_url;
                            $wpjobportal_title = trim((string) $wpjobportal_row->title) !== '' ? $wpjobportal_row->title : sprintf(/* translators: %d: record id */ __('Untitled (#%d)', 'wp-job-portal'), (int) $wpjobportal_row->id);
                            $wpjobportal_feature = $wpjobportal_row->reason === 'feature';
                            $wpjobportal_tasks = ($wpjobportal_act && isset($wpjobportal_act[$wpjobportal_row->reason])) ? $wpjobportal_act[$wpjobportal_row->reason] : null;
                            ?>
                            <tr id="approval_<?php echo esc_attr($wpjobportal_type . '_' . $wpjobportal_row->reason . '_' . (int) $wpjobportal_row->id); ?>">
                                <?php if ($wpjobportal_act_one) : ?><td class="wjpl-c-cb"><input class="wjpl-cb wjpl-pick" type="checkbox" name="wpjobportal-cb[]" value="<?php echo esc_attr((int) $wpjobportal_row->id); ?>" aria-label="<?php echo esc_attr(sprintf(/* translators: %s: item name */ __('Select %s', 'wp-job-portal'), $wpjobportal_title)); ?>"></td><?php endif; ?>
                                <td class="wjpl-c-main">
                                    <a class="wjpl-title" href="<?php echo esc_url($wpjobportal_open); ?>"><?php echo esc_html($wpjobportal_title); ?></a>
                                    <div class="wjpl-ra">
                                        <?php if ($wpjobportal_tasks) : ?>
                                            <a class="wjpl-ok" href="<?php echo esc_url($wpjobportal_task_url($wpjobportal_type, $wpjobportal_tasks[0], $wpjobportal_row->id)); ?>"><?php echo esc_html($wpjobportal_feature ? __('Approve feature', 'wp-job-portal') : __('Approve', 'wp-job-portal')); ?></a>
                                            <a class="wjpl-del" href="<?php echo esc_url($wpjobportal_task_url($wpjobportal_type, $wpjobportal_tasks[1], $wpjobportal_row->id)); ?>"><?php echo esc_html($wpjobportal_feature ? __('Reject feature', 'wp-job-portal') : __('Reject', 'wp-job-portal')); ?></a>
                                        <?php endif; ?>
                                        <?php if ($wpjobportal_act) : ?><a href="<?php echo esc_url($wpjobportal_open); ?>"><?php echo esc_html(__('Edit', 'wp-job-portal')); ?></a><?php endif; ?>
                                        <?php if ($wpjobportal_act_one) : ?>
                                            <a class="wjpl-del" href="#" data-wjpl-row="delete" data-id="<?php echo esc_attr((int) $wpjobportal_row->id); ?>"><?php echo esc_html(__('Delete', 'wp-job-portal')); ?></a>
                                        <?php else : ?>
                                            <a href="<?php echo esc_url($wpjobportal_queue_url); ?>"><?php echo esc_html(sprintf(/* translators: %s: queue name, e.g. Jobs */ __('Open in %s', 'wp-job-portal'), $wpjobportal_queues[$wpjobportal_type]['label'])); ?></a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <?php if (!$wpjobportal_one) : ?><td class="wjpl-col-cat" data-l="<?php echo esc_attr(__('Type', 'wp-job-portal')); ?>"><?php echo esc_html($wpjobportal_type_labels[$wpjobportal_type]); ?></td><?php endif; ?>
                                <td class="wjpl-col-sal" data-l="<?php echo esc_attr(__('From', 'wp-job-portal')); ?>"><?php echo trim((string) $wpjobportal_row->context) !== '' ? esc_html($wpjobportal_row->context) : '<span class="wjpl-muted">—</span>'; ?></td>
                                <td class="wjpl-col-posted wjpl-nowrap" data-l="<?php echo esc_attr(__('Submitted', 'wp-job-portal')); ?>"><?php echo esc_html(date_i18n($wpjobportal_dateformat, strtotime((string) $wpjobportal_row->created))); ?></td>
                                <td data-l="<?php echo esc_attr(__('Needs', 'wp-job-portal')); ?>"><span class="wjpl-badge <?php echo $wpjobportal_feature ? 'wjpl-b-feat' : 'wjpl-b-wait'; ?>"><?php echo esc_html($wpjobportal_feature ? __('Featured request', 'wp-job-portal') : __('Approval', 'wp-job-portal')); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php elseif ($wpjobportal_q !== '') : ?>
                    <?php WPJOBPORTAL_Admin_List_UI::empty_state(__('Nothing waiting matches', 'wp-job-portal'), __('Try fewer words, or reset the search.', 'wp-job-portal')); ?>
                <?php else : ?>
                    <?php WPJOBPORTAL_Admin_List_UI::empty_state(__('Nothing is waiting', 'wp-job-portal'), $wpjobportal_one ? __('Items that need your approval appear here.', 'wp-job-portal') : __('New jobs, companies, resumes and other items that need your approval appear here.', 'wp-job-portal')); ?>
                <?php endif; ?>
                </div>
                <?php if ($wpjobportal_act_one) : ?></form><?php endif; ?>
            </section>
            <?php if ($wpjobportal_act_one) : ?>
                <dialog id="wjpl-delete" class="wjpl-dialog" aria-labelledby="wjpl-delete-title">
                    <h2 id="wjpl-delete-title"><?php echo esc_html(__('Delete?', 'wp-job-portal')); ?></h2>
                    <p><?php
                        /* translators: %s: number selected (filled in by script) */
                        echo wp_kses(sprintf(__('%s selected item(s) will be deleted.', 'wp-job-portal'), '<b class="wjpl-dlg-n">1</b>'), array('b' => array('class' => array())));
                    ?></p>
                    <div class="wjpl-dlg-acts">
                        <button type="button" class="wjpl-btn wjpl-btn-sec wjpl-dlg-cancel"><?php echo esc_html(__('Cancel', 'wp-job-portal')); ?></button>
                        <button type="button" class="wjpl-btn wjpl-btn-danger wjpl-dlg-ok"><?php echo esc_html(__('Delete', 'wp-job-portal')); ?></button>
                    </div>
                </dialog>
            <?php endif; ?>
        </div>
    </div>
</div>
