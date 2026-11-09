<?php
if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * The value lists behind the job form (categories, job types, statuses, career
 * levels, education, salary range types, currencies, tags), drawn as one list
 * screen from the shared list parts (design 4).
 *
 * Each module's model still loads the rows (wpjobportal::$_data[0], total and
 * filter) and each module's tasks still do the work - publish, unpublish,
 * remove, saveordering and the common makedefault - so nonces, capability
 * checks and hooks are unchanged. A module's template only describes itself:
 *
 *   page        page slug, e.g. wpjobportal_category
 *   title       heading; noun / nouns for counts
 *   crumbs      breadcrumb before the heading: [[label, url], ...]
 *   form        layout of the add/edit form, e.g. formcategory
 *   nonce       the module's nonce action
 *   remove      remove task (remove, removetag)
 *   label       row title: field name, or callable(row): string
 *   published   field holding published (isactive, status, enabled)
 *   default_for makedefault key for the common task ('' when none)
 *   default     field holding default (isdefault, default)
 *   ordering    rows can be dragged into a new order (saveordering)
 *   search      [post name, value, placeholder] for the 2.7 search
 *   columns     [[label, callable(row): html, class], ...] between title and status
 *   note        one line under the heading
 *   url_args    query args every URL of the list keeps (countryid, stateid)
 *   checks      extra filter checkboxes: [[post name, label, current value], ...]
 *   row_links   callable(row): [label => url] row actions before Delete
 */
class WPJOBPORTAL_Admin_Lookup_UI {

    public static function render($c) {
        $c += array('url_args' => array(), 'checks' => array(), 'row_links' => null, 'crumbs' => array(), 'remove' => 'remove', 'published' => 'isactive', 'default_for' => '', 'default' => 'isdefault', 'ordering' => true, 'columns' => array(), 'note' => '', 'search' => array('searchname', '', __('Search by name', 'wp-job-portal')));
        WPJOBPORTAL_Admin_List_UI::enqueue();
        if ($c['ordering']) {
            wp_enqueue_script('jquery-ui-sortable');
        }
        WPJOBPORTALincluder::getTemplate('templates/admin/header', array('wpjobportal_module' => str_replace('wpjobportal_', '', $c['page'])));

        $rows = isset(wpjobportal::$_data[0]) && is_array(wpjobportal::$_data[0]) ? wpjobportal::$_data[0] : array();
        $total = isset(wpjobportal::$_data['total']) ? (int) wpjobportal::$_data['total'] : count($rows);
        $status = isset(wpjobportal::$_data['filter']['status']) ? wpjobportal::$_data['filter']['status'] : '';
        $filtered = (string) $c['search'][1] !== '' || is_numeric($status);
        foreach ($c['checks'] as $check) {
            $filtered = $filtered || !empty($check[2]);
        }
        $page = $c['page'];
        $url_args = array_filter((array) $c['url_args']);
        $list_url = add_query_arg(array_map('rawurlencode', array('page' => $page) + $url_args), admin_url('admin.php'));
        $add_url = add_query_arg(array_map('rawurlencode', array('page' => $page, 'wpjobportallt' => $c['form']) + $url_args), admin_url('admin.php'));
        $nonce = wp_create_nonce($c['nonce']);
        $pagenum = max(1, (int) WPJOBPORTALrequest::getVar('pagenum', 'get', 1));
        $label = function ($row) use ($c) {
            return is_callable($c['label']) ? (string) call_user_func($c['label'], $row) : (string) wpjobportal::wpjobportal_getVariableValue($row->{$c['label']});
        };
        $task_url = function ($task, $id) use ($page, $nonce, $pagenum, $url_args) {
            $args = array('page' => $page, 'task' => $task, 'action' => 'wpjobportaltask', 'wpjobportal-cb[]' => (int) $id, '_wpnonce' => $nonce) + $url_args;
            if ($pagenum > 1) {
                $args['pagenum'] = $pagenum;
            }
            return add_query_arg(array_map('rawurlencode', $args), admin_url('admin.php'));
        };
        $crumbs = array();
        foreach ($c['crumbs'] as $crumb) {
            $crumbs[] = array('label' => $crumb[0], 'url' => isset($crumb[1]) ? $crumb[1] : '');
        }
        $crumbs[] = array('label' => $c['title']);
        ?>
        <div id="wpjobportaladmin-wrapper" class="wjpl-page">
            <div id="wpjobportaladmin-leftmenu">
                <?php WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
            </div>
            <div id="wpjobportaladmin-data">
                <div class="wjpl" data-delete-confirm="<?php echo esc_attr(__('Are you sure to delete', 'wp-job-portal') . ' ?'); ?>">
                    <?php
                    WPJOBPORTAL_Admin_List_UI::top_bar($crumbs);
                    WPJOBPORTAL_Admin_List_UI::title_row(
                        $c['title'],
                        /* translators: 1: number, 2: what is counted */
                        sprintf(__('%1$s %2$s', 'wp-job-portal'), number_format_i18n($total), $total === 1 ? $c['noun'] : $c['nouns']) . ($c['note'] !== '' ? ' · ' . $c['note'] : ''),
                        array(),
                        array('label' => sprintf(/* translators: %s: what is added, e.g. category */ __('Add New %s', 'wp-job-portal'), $c['noun_title']), 'url' => $add_url)
                    );
                    WPJOBPORTAL_Admin_List_UI::section_tabs();
                    ?>
                    <section class="wjpl-card" aria-label="<?php echo esc_attr($c['title']); ?>">
                        <form class="wjpl-filters" method="post" action="<?php echo esc_url($list_url); ?>">
                            <input type="hidden" name="WPJOBPORTAL_form_search" value="WPJOBPORTAL_SEARCH">
                            <div class="wjpl-selbar" role="toolbar" aria-label="<?php echo esc_attr(__('Bulk actions', 'wp-job-portal')); ?>" hidden>
                                <b><span class="wjpl-selcount">0</span> <?php echo esc_html(__('selected', 'wp-job-portal')); ?></b>
                                <button class="wjpl-sb" type="button" data-wjpl-bulk="publish"><?php echo esc_html(__('Publish', 'wp-job-portal')); ?></button>
                                <button class="wjpl-sb" type="button" data-wjpl-bulk="unpublish"><?php echo esc_html(__('Unpublish', 'wp-job-portal')); ?></button>
                                <button class="wjpl-sb wjpl-del" type="button" data-wjpl-bulk="delete"><?php echo esc_html(__('Delete', 'wp-job-portal')); ?></button>
                                <button class="wjpl-sb wjpl-clear" type="button"><?php echo esc_html(__('Clear selection', 'wp-job-portal')); ?></button>
                            </div>
                            <div class="wjpl-tb2">
                                <div class="wjpl-search">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                                    <input class="wjpl-inp" type="search" name="<?php echo esc_attr($c['search'][0]); ?>" value="<?php echo esc_attr((string) $c['search'][1]); ?>" placeholder="<?php echo esc_attr($c['search'][2]); ?>" aria-label="<?php echo esc_attr($c['search'][2]); ?>">
                                </div>
                                <select class="wjpl-sel" name="status" data-wjpl-autosubmit aria-label="<?php echo esc_attr(__('Published', 'wp-job-portal')); ?>">
                                    <option value=""><?php echo esc_html(__('Published or not', 'wp-job-portal')); ?></option>
                                    <option value="1"<?php selected((string) $status, '1'); ?>><?php echo esc_html(__('Published', 'wp-job-portal')); ?></option>
                                    <option value="0"<?php selected((string) $status, '0'); ?>><?php echo esc_html(__('Not published', 'wp-job-portal')); ?></option>
                                </select>
                                <?php if (!empty($c['checks'])) : ?>
                                    <span class="wjpl-checks">
                                        <?php foreach ($c['checks'] as $check) : ?>
                                            <label><input type="checkbox" name="<?php echo esc_attr($check[0]); ?>" value="1"<?php checked(!empty($check[2])); ?>> <?php echo esc_html($check[1]); ?></label>
                                        <?php endforeach; ?>
                                    </span>
                                    <span></span>
                                <?php else : ?>
                                    <span></span><span></span>
                                <?php endif; ?>
                                <span class="wjpl-tb2-acts">
                                    <button class="wjpl-btn wjpl-btn-pri" type="submit"><?php echo esc_html(__('Search', 'wp-job-portal')); ?></button>
                                    <button class="wjpl-linkbtn" type="button" data-wjpl-reset><?php echo esc_html(__('Reset', 'wp-job-portal')); ?></button>
                                </span>
                            </div>
                            <?php if ($c['ordering'] && !$filtered && count($rows) > 1) : ?>
                                <p class="wjpl-hint"><?php echo esc_html(__('Drag a row by its handle to change the order on the site.', 'wp-job-portal')); ?></p>
                            <?php endif; ?>
                        </form>

                        <form class="wjpl-bulkform" method="post" action="<?php echo esc_url($list_url); ?>" data-task-publish="publish" data-task-unpublish="unpublish" data-task-delete="<?php echo esc_attr($c['remove']); ?>">
                            <input type="hidden" name="form_request" value="wpjobportal">
                            <input type="hidden" name="task" value="">
                            <input type="hidden" name="bulkop" value="">
                            <input type="hidden" name="deleteall" value="0">
                            <input type="hidden" name="pagenum" value="<?php echo $pagenum > 1 ? esc_attr($pagenum) : ''; ?>">
                            <input type="hidden" name="fields_ordering_new" value="">
                            <input type="hidden" name="_wpnonce" value="<?php echo esc_attr($nonce); ?>">
                            <div class="wjpl-tw">
                            <?php if (!empty($rows)) : ?>
                                <table class="wjpl-table wjpl-lookup">
                                    <thead><tr>
                                        <th class="wjpl-c-cb"><input class="wjpl-cb wjpl-all" type="checkbox" aria-label="<?php echo esc_attr(sprintf(/* translators: %s: what is listed */ __('Select all %s', 'wp-job-portal'), $c['nouns'])); ?>"></th>
                                        <?php if ($c['ordering'] && !$filtered) : ?><th class="wjpl-fo-grip-c" aria-label="<?php echo esc_attr(__('Ordering', 'wp-job-portal')); ?>"></th><?php endif; ?>
                                        <th><?php echo esc_html($c['noun_title']); ?></th>
                                        <?php foreach ($c['columns'] as $column) : ?>
                                            <th class="<?php echo esc_attr(isset($column[2]) ? $column[2] : ''); ?>"><?php echo esc_html($column[0]); ?></th>
                                        <?php endforeach; ?>
                                        <th><?php echo esc_html(__('Status', 'wp-job-portal')); ?></th>
                                    </tr></thead>
                                    <tbody>
                                    <?php foreach ($rows as $row) :
                                        $id = (int) $row->id;
                                        $edit = add_query_arg(array_map('rawurlencode', array('page' => $page, 'wpjobportallt' => $c['form'], 'wpjobportalid' => $id) + $url_args), admin_url('admin.php'));
                                        $is_published = (int) $row->{$c['published']} === 1;
                                        $is_default = $c['default_for'] !== '' && isset($row->{$c['default']}) && (int) $row->{$c['default']} === 1;
                                        $name = $label($row);
                                        ?>
                                        <tr id="id_<?php echo esc_attr($id); ?>">
                                            <td class="wjpl-c-cb">
                                                <input class="wjpl-cb wjpl-pick" type="checkbox" name="wpjobportal-cb[]" value="<?php echo esc_attr($id); ?>" aria-label="<?php echo esc_attr(sprintf(/* translators: %s: item name */ __('Select %s', 'wp-job-portal'), $name)); ?>">
                                            </td>
                                            <?php if ($c['ordering'] && !$filtered) : ?><td class="wjpl-fo-grip-c"><span class="wjpl-fo-grip" title="<?php echo esc_attr(__('Drag to reorder', 'wp-job-portal')); ?>" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="6" r="1.6"/><circle cx="15" cy="6" r="1.6"/><circle cx="9" cy="12" r="1.6"/><circle cx="15" cy="12" r="1.6"/><circle cx="9" cy="18" r="1.6"/><circle cx="15" cy="18" r="1.6"/></svg></span></td><?php endif; ?>
                                            <td class="wjpl-c-main">
                                                <a class="wjpl-title" href="<?php echo esc_url($edit); ?>"><?php echo esc_html($name); ?></a>
                                                <?php if ($is_default) : ?><span class="wjpl-badge wjpl-b-feat"><?php echo esc_html(__('Default', 'wp-job-portal')); ?></span><?php endif; ?>
                                                <div class="wjpl-ra">
                                                    <a href="<?php echo esc_url($edit); ?>"><?php echo esc_html(__('Edit', 'wp-job-portal')); ?></a>
                                                    <a href="<?php echo esc_url($task_url($is_published ? 'unpublish' : 'publish', $id)); ?>"><?php echo esc_html($is_published ? __('Unpublish', 'wp-job-portal') : __('Publish', 'wp-job-portal')); ?></a>
                                                    <?php if ($c['default_for'] !== '' && !$is_default) : ?>
                                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_common&task=makedefault&action=wpjobportaltask&for=' . rawurlencode($c['default_for']) . '&id=' . $id . ($pagenum > 1 ? '&pagenum=' . $pagenum : '')), 'wpjobportal_common_entity_nonce')); ?>"><?php echo esc_html(__('Make default', 'wp-job-portal')); ?></a>
                                                    <?php endif; ?>
                                                    <?php if ($c['row_links']) : foreach ((array) call_user_func($c['row_links'], $row) as $link_label => $link_url) : ?>
                                                        <a href="<?php echo esc_url($link_url); ?>"><?php echo esc_html($link_label); ?></a>
                                                    <?php endforeach; endif; ?>
                                                    <a class="wjpl-del" href="#" data-wjpl-row="delete" data-id="<?php echo esc_attr($id); ?>"><?php echo esc_html(__('Delete', 'wp-job-portal')); ?></a>
                                                </div>
                                            </td>
                                            <?php foreach ($c['columns'] as $column) : ?>
                                                <td class="<?php echo esc_attr(isset($column[2]) ? $column[2] : ''); ?>" data-l="<?php echo esc_attr($column[0]); ?>"><?php echo wp_kses(call_user_func($column[1], $row), WPJOBPORTAL_ALLOWED_TAGS); ?></td>
                                            <?php endforeach; ?>
                                            <td data-l="<?php echo esc_attr(__('Status', 'wp-job-portal')); ?>"><span class="wjpl-badge wjpl-b-<?php echo $is_published ? 'ok' : 'closed'; ?>"><?php echo esc_html($is_published ? __('Published', 'wp-job-portal') : __('Not published', 'wp-job-portal')); ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <?php if ($c['ordering'] && !$filtered) : ?>
                                    <div class="wjpl-saveorder" hidden>
                                        <span><?php echo esc_html(__('The new order is not saved yet.', 'wp-job-portal')); ?></span>
                                        <button class="wjpl-btn wjpl-btn-pri" type="button" data-wjpl-saveorder><?php echo esc_html(__('Save order', 'wp-job-portal')); ?></button>
                                    </div>
                                <?php endif; ?>
                            <?php elseif ($total > 0) : ?>
                                <?php WPJOBPORTAL_Admin_List_UI::empty_state(__('Nothing on this page', 'wp-job-portal'), __('This list has fewer pages now.', 'wp-job-portal'), array(__('Go to the first page', 'wp-job-portal') => $list_url)); ?>
                            <?php elseif ($filtered) : ?>
                                <?php WPJOBPORTAL_Admin_List_UI::empty_state(sprintf(/* translators: %s: what is listed */ __('No %s match these filters', 'wp-job-portal'), $c['nouns']), __('Try fewer words, or reset the filters.', 'wp-job-portal')); ?>
                            <?php else : ?>
                                <?php WPJOBPORTAL_Admin_List_UI::empty_state(sprintf(/* translators: %s: what is listed */ __('No %s yet', 'wp-job-portal'), $c['nouns']), __('Add the first one; it then appears in the job form.', 'wp-job-portal'), array(sprintf(__('Add New %s', 'wp-job-portal'), $c['noun_title']) => $add_url)); ?>
                            <?php endif; ?>
                            </div>
                        </form>
                        <?php WPJOBPORTAL_Admin_List_UI::footer($total, $c['nouns'], function ($p) use ($list_url) {
                            return $p > 1 ? add_query_arg('pagenum', (int) $p, $list_url) : $list_url;
                        }); ?>
                    </section>

                    <dialog id="wjpl-delete" class="wjpl-dialog" aria-labelledby="wjpl-delete-title">
                        <h2 id="wjpl-delete-title"><?php echo esc_html(sprintf(/* translators: %s: what is deleted */ __('Delete %s?', 'wp-job-portal'), $c['nouns'])); ?></h2>
                        <p><?php
                            /* translators: %s: number selected (filled in by script) */
                            echo wp_kses(sprintf(__('%s selected item(s) will be deleted. Items that jobs or resumes still use are kept.', 'wp-job-portal'), '<b class="wjpl-dlg-n">1</b>'), array('b' => array('class' => array())));
                        ?></p>
                        <div class="wjpl-dlg-acts">
                            <button type="button" class="wjpl-btn wjpl-btn-sec wjpl-dlg-cancel"><?php echo esc_html(__('Cancel', 'wp-job-portal')); ?></button>
                            <button type="button" class="wjpl-btn wjpl-btn-danger wjpl-dlg-ok"><?php echo esc_html(__('Delete', 'wp-job-portal')); ?></button>
                        </div>
                    </dialog>
                </div>
            </div>
        </div>
        <?php
        // Reset: an empty search posted to the list clears the saved one (as 2.7's reset did).
        wp_add_inline_script('wpjobportal-admin-list', 'jQuery(document).on("click", "[data-wjpl-reset]", function () { var $f = jQuery(this).closest("form"); $f.find("input[type=search], select[name=status]").val(""); $f.find(".wjpl-checks input").prop("checked", false); $f.trigger("submit"); });');
        if ($c['ordering'] && !$filtered) {
            // Same posting as 2.7: the rows' order as id[]=…, to the saveordering task.
            wp_add_inline_script('wpjobportal-admin-list', 'jQuery(function ($) {
                var $body = $(".wjpl-lookup tbody"), $form = $(".wjpl-bulkform");
                if (!$body.length || !$.fn.sortable) { return; }
                $body.sortable({ handle: ".wjpl-fo-grip", axis: "y", update: function () {
                    $form.find("input[name=\'fields_ordering_new\']").val($body.sortable("serialize"));
                    $(".wjpl-saveorder").prop("hidden", false);
                } });
                $(document).on("click", "[data-wjpl-saveorder]", function () {
                    $form.find("input[name=\'task\']").val("saveordering");
                    $form.trigger("submit");
                });
            });');
        }
    }

    /** A colour swatch for a cell. */
    public static function swatch($colour) {
        $colour = sanitize_hex_color((string) $colour);
        return $colour ? '<span class="wjpl-swatch" style="background:' . esc_attr($colour) . '"></span> <code>' . esc_html($colour) . '</code>' : '';
    }
}
