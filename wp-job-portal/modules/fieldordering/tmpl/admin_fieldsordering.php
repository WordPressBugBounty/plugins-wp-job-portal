<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
wp_enqueue_script( 'wpjobportal-res-tables', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/responsivetable.js', array(), wpjobportal_asset_version('includes/js/responsivetable.js'), false );
wp_enqueue_script('jquery-ui-sortable');
$wpjobportal_protocol = is_ssl() ? "https://" : "http://";
wp_enqueue_style('jquery-ui-css', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/jquery-ui-smoothness.css', array(), wpjobportal_asset_version('includes/css/jquery-ui-smoothness.css'));
wp_enqueue_script( 'jquery-multisortable', esc_url(WPJOBPORTAL_PLUGIN_URL).'/includes/js/jquery.multisortable.js', array(), wpjobportal_asset_version('includes/js/jquery.multisortable.js'), false );
?>
<!-- main wrapper -->
<div id="wpjobportaladmin-wrapper" class="wjpl-page">
     <!-- left menu -->
    <div id="wpjobportaladmin-leftmenu">
        <?php  WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <div id="full_background" style="display:none;"></div>
        <div id="popup_main" style="display:none;"></div>
        <?php
            $wpjobportal_msgkey = WPJOBPORTALincluder::getJSModel('fieldordering')->getMessagekey();
            WPJOBPORTALMessages::getLayoutMessage($wpjobportal_msgkey);
        ?>
        <!-- top head -->
        <?php ob_start(); ?>
                <?php
                    if(wpjobportal::$wpjobportal_data['fieldfor'] == 1){
                        echo esc_html(__('Company','wp-job-portal'));
                    } elseif (wpjobportal::$wpjobportal_data['fieldfor'] == 2){
                        echo esc_html(__('Job','wp-job-portal'));
                    } elseif (wpjobportal::$wpjobportal_data['fieldfor'] == 3){
                        echo esc_html(__('Resume','wp-job-portal'));
                    } elseif (wpjobportal::$wpjobportal_data['fieldfor'] == 5){
                        echo esc_html(__('Quick Apply','wp-job-portal'));
                    }
                    echo ' '.esc_html(__('Fields', 'wp-job-portal'));
                ?>
            <?php $wpjobportal_title = trim(html_entity_decode(wp_strip_all_tags(ob_get_clean()), ENT_QUOTES)); ?>
        <?php WPJOBPORTAL_Admin_List_UI::page_head($wpjobportal_title, '', array(), (wpjobportal::$wpjobportal_data['fieldfor'] != 5 && wpjobportal::$wpjobportal_data['fieldfor'] != 6) ? array('label' => __('Add User Field', 'wp-job-portal'), 'url' => admin_url('admin.php?page=wpjobportal_customfield&wpjobportallt=formuserfield&ff=' . (int) wpjobportal::$wpjobportal_data['fieldfor'])) : array()); ?>
        <!-- page content -->
        <?php
        /*
         * Settings › Forms & fields (design 4, the Jobs list's pattern): one card with the
         * selection bar (user / visitor publish, required), search and status filters, then
         * the fields in their form order. Drag rows to reorder, then Save ordering (2.7's
         * multisortable and saveordering task, unchanged). Each row: On/Off switches for
         * users, visitors and required; locked ones say so; Edit and Delete (custom fields).
         */
        WPJOBPORTAL_Admin_List_UI::enqueue();
        $wpjobportal_ff = (int) wpjobportal::$wpjobportal_data['fieldfor'];
        $wpjobportal_rows = !empty(wpjobportal::$_data[0]) ? (array) wpjobportal::$_data[0] : array();
        $wpjobportal_fnonce = 'wpjobportal_field_nonce';
        $wpjobportal_toggle = function ($row, $task_on, $task_off, $is_on, $label) use ($wpjobportal_ff, $wpjobportal_fnonce) {
            $task = $is_on ? $task_off : $task_on;
            $url = wp_nonce_url(admin_url('admin.php?page=wpjobportal_fieldordering&task=' . $task . '&action=wpjobportaltask&wpjobportal-cb[]=' . (int) $row->id . '&ff=' . $wpjobportal_ff), $wpjobportal_fnonce);
            /* translators: 1: Users / Visitors / Required, 2: field title */
            $aria = sprintf(__('%1$s: %2$s, click to change', 'wp-job-portal'), $label, wpjobportal::wpjobportal_getVariableValue($row->fieldtitle));
            echo '<a class="wjpl-sw' . ($is_on ? ' is-on' : '') . '" href="' . esc_url($url) . '" role="switch" aria-checked="' . ($is_on ? 'true' : 'false') . '" aria-label="' . esc_attr($aria) . '"><span class="wjpl-sw-k"></span><span class="wjpl-sw-t">' . esc_html($is_on ? __('On', 'wp-job-portal') : __('Off', 'wp-job-portal')) . '</span></a>';
        };
        $wpjobportal_locked = function ($text, $on) {
            echo '<span class="wjpl-sw-lock' . ($on ? ' is-on' : '') . '"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>' . esc_html($text) . '</span>';
        };
        $wpjobportal_count_custom = 0;
        foreach ($wpjobportal_rows as $wpjobportal_r) { if ((int) $wpjobportal_r->isuserfield === 1) { $wpjobportal_count_custom++; } }
        ?>
        <div class="wjpl">
            <section class="wjpl-card wjpl-fo" aria-label="<?php echo esc_attr($wpjobportal_title); ?>">
                <div class="wjpl-filters">
                    <div class="wjpl-tb1">
                        <span class="wjpl-fo-sum">
                            <?php
                            /* translators: 1: number of fields, 2: number of custom fields */
                            echo esc_html(sprintf(_n('%1$s field · %2$s custom', '%1$s fields · %2$s custom', count($wpjobportal_rows), 'wp-job-portal'), number_format_i18n(count($wpjobportal_rows)), number_format_i18n($wpjobportal_count_custom)));
                            ?>
                            <span class="wjpl-fo-hint"><?php echo esc_html(__('Drag a row to change the order on the form.', 'wp-job-portal')); ?></span>
                        </span>
                        <button type="submit" form="wpjobportal-list-form" id="saveordering" name="saveordering" class="wjpl-btn wjpl-btn-pri js-form-button" hidden><?php echo esc_html(__('Save Ordering', 'wp-job-portal')); ?></button>
                    </div>
                    <div class="wjpl-selbar" role="toolbar" aria-label="<?php echo esc_attr(__('Bulk actions', 'wp-job-portal')); ?>" hidden>
                        <b><span class="wjpl-selcount">0</span> <?php echo esc_html(__('selected', 'wp-job-portal')); ?></b>
                        <span class="wjpl-fo-grp"><?php echo esc_html(__('Users', 'wp-job-portal')); ?>
                            <button class="wjpl-sb" type="button" data-wjpl-bulk="upub"><?php echo esc_html(__('On', 'wp-job-portal')); ?></button>
                            <button class="wjpl-sb" type="button" data-wjpl-bulk="uunpub"><?php echo esc_html(__('Off', 'wp-job-portal')); ?></button>
                        </span>
                        <span class="wjpl-fo-grp"><?php echo esc_html(__('Visitors', 'wp-job-portal')); ?>
                            <button class="wjpl-sb" type="button" data-wjpl-bulk="vpub"><?php echo esc_html(__('On', 'wp-job-portal')); ?></button>
                            <button class="wjpl-sb" type="button" data-wjpl-bulk="vunpub"><?php echo esc_html(__('Off', 'wp-job-portal')); ?></button>
                        </span>
                        <span class="wjpl-fo-grp"><?php echo esc_html(__('Required', 'wp-job-portal')); ?>
                            <button class="wjpl-sb" type="button" data-wjpl-bulk="req"><?php echo esc_html(__('On', 'wp-job-portal')); ?></button>
                            <button class="wjpl-sb" type="button" data-wjpl-bulk="notreq"><?php echo esc_html(__('Off', 'wp-job-portal')); ?></button>
                        </span>
                        <button class="wjpl-sb wjpl-clear" type="button"><?php echo esc_html(__('Clear selection', 'wp-job-portal')); ?></button>
                    </div>
                    <?php // A post form, so the saved search keeps working; the shared script's GET tidy-up is for form.wjpl-filters only. ?>
                    <form class="wjpl-tb2" name="wpjobportalform" id="wpjobportalform" method="post" action="<?php echo esc_url_raw(admin_url('admin.php?page=wpjobportal_fieldordering&ff=' . $wpjobportal_ff)); ?>">
                        <div class="wjpl-search">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                            <input class="wjpl-inp" type="search" name="title" id="title" value="<?php echo esc_attr(wpjobportal::$_data['filter']['title']); ?>" placeholder="<?php echo esc_attr(__('Search field titles', 'wp-job-portal')); ?>" aria-label="<?php echo esc_attr(__('Title', 'wp-job-portal')); ?>">
                        </div>
                        <?php
                        $wpjobportal_sel = function ($name, $options, $value, $placeholder) {
                            echo '<select class="wjpl-sel" name="' . esc_attr($name) . '" id="' . esc_attr($name) . '" onchange="this.form.submit();" aria-label="' . esc_attr($placeholder) . '"><option value="">' . esc_html($placeholder) . '</option>';
                            foreach ((array) $options as $o) {
                                echo '<option value="' . esc_attr($o->id) . '"' . selected(is_numeric($value) ? (string) $value : '', (string) $o->id, false) . '>' . esc_html($o->text) . '</option>';
                            }
                            echo '</select>';
                        };
                        $wpjobportal_sel('ustatus', WPJOBPORTALincluder::getJSModel('common')->getStatus(), wpjobportal::$_data['filter']['ustatus'], __('Any status', 'wp-job-portal'));
                        $wpjobportal_sel('required', WPJOBPORTALincluder::getJSModel('common')->getYesNo(), wpjobportal::$_data['filter']['required'], __('Required or not', 'wp-job-portal'));
                        ?>
                        <span class="wjpl-tb2-acts">
                            <input type="hidden" name="WPJOBPORTAL_form_search" value="WPJOBPORTAL_SEARCH">
                            <button class="wjpl-btn wjpl-btn-pri" type="submit" name="btnsubmit" value="1"><?php echo esc_html(__('Search', 'wp-job-portal')); ?></button>
                            <button class="wjpl-linkbtn" type="submit" onclick="this.form.title.value='';this.form.ustatus.value='';this.form.required.value='';"><?php echo esc_html(__('Reset', 'wp-job-portal')); ?></button>
                        </span>
                    </form>
                </div>

                <form id="wpjobportal-list-form" class="wjpl-bulkform" method="post" action="<?php echo esc_url_raw(admin_url('admin.php?page=wpjobportal_fieldordering')); ?>"
                    data-task-upub="fieldpublished" data-task-uunpub="fieldunpublished" data-task-vpub="visitorfieldpublished" data-task-vunpub="visitorfieldunpublished" data-task-req="fieldrequired" data-task-notreq="fieldnotrequired">
                    <div class="wjpl-tw">
                    <?php if (!empty($wpjobportal_rows)) : ?>
                        <table class="wjpl-table wjpl-fo-table" id="wpjobportal-table">
                            <thead><tr>
                                <th class="wjpl-c-cb"><input class="wjpl-cb wjpl-all" type="checkbox" aria-label="<?php echo esc_attr(__('Select all fields', 'wp-job-portal')); ?>"></th>
                                <th class="wjpl-fo-grip-c" aria-label="<?php echo esc_attr(__('Ordering', 'wp-job-portal')); ?>"></th>
                                <th><?php echo esc_html(__('Field', 'wp-job-portal')); ?></th>
                                <th class="wjpl-fo-sw-c"><?php echo esc_html(__('Users', 'wp-job-portal')); ?></th>
                                <th class="wjpl-fo-sw-c"><?php echo esc_html(__('Visitors', 'wp-job-portal')); ?></th>
                                <th class="wjpl-fo-sw-c"><?php echo esc_html(__('Required', 'wp-job-portal')); ?></th>
                            </tr></thead>
                            <tbody>
                            <?php foreach ($wpjobportal_rows as $wpjobportal_row) :
                                $wpjobportal_is_head = (int) $wpjobportal_row->is_section_headline === 1;
                                $wpjobportal_ftitle = wpjobportal::wpjobportal_getVariableValue($wpjobportal_row->fieldtitle);
                                $wpjobportal_edit = admin_url('admin.php?page=wpjobportal_fieldordering&wpjobportallt=formuserfield&ff=' . $wpjobportal_ff . '&wpjobportalid=' . (int) $wpjobportal_row->id);
                                ?>
                                <tr class="sortable<?php echo $wpjobportal_is_head ? ' wjpl-fo-head' : ''; ?>" id="id_<?php echo esc_attr($wpjobportal_row->id); ?>">
                                    <td class="wjpl-c-cb"><input class="wjpl-cb wjpl-pick wpjobportal-cb" type="checkbox" name="wpjobportal-cb[]" value="<?php echo esc_attr($wpjobportal_row->id); ?>" aria-label="<?php echo esc_attr(sprintf(/* translators: %s: field title */ __('Select %s', 'wp-job-portal'), $wpjobportal_ftitle)); ?>"></td>
                                    <td class="wjpl-fo-grip-c"><span class="wjpl-fo-grip" title="<?php echo esc_attr(__('Drag to reorder', 'wp-job-portal')); ?>" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="6" r="1.6"/><circle cx="15" cy="6" r="1.6"/><circle cx="9" cy="12" r="1.6"/><circle cx="15" cy="12" r="1.6"/><circle cx="9" cy="18" r="1.6"/><circle cx="15" cy="18" r="1.6"/></svg></span></td>
                                    <td class="wjpl-c-main">
                                        <a class="wjpl-title" href="<?php echo esc_url($wpjobportal_edit); ?>"><?php echo esc_html($wpjobportal_ftitle); ?></a>
                                        <?php if ($wpjobportal_is_head) : ?><span class="wjpl-badge wjpl-b-closed"><?php echo esc_html(__('Section', 'wp-job-portal')); ?></span><?php endif; ?>
                                        <?php if ((int) $wpjobportal_row->isuserfield === 1) : ?><span class="wjpl-badge wjpl-b-pri"><?php echo esc_html(__('Custom', 'wp-job-portal')); ?></span><?php endif; ?>
                                        <div class="wjpl-ra">
                                            <a href="<?php echo esc_url($wpjobportal_edit); ?>"><?php echo esc_html(__('Edit', 'wp-job-portal')); ?></a>
                                            <?php if ((int) $wpjobportal_row->isuserfield === 1) : ?>
                                                <a class="wjpl-del" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpjobportal_fieldordering&task=remove&action=wpjobportaltask&fieldid=' . (int) $wpjobportal_row->id . '&ff=' . $wpjobportal_ff . ($wpjobportal_is_head ? '&is_section_headline=1' : '')), $wpjobportal_fnonce)); ?>" onclick="return confirm('<?php echo esc_js(__('Delete this field? Values people entered in it are deleted too.', 'wp-job-portal')); ?>');"><?php echo esc_html(__('Delete', 'wp-job-portal')); ?></a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="wjpl-fo-sw-c" data-l="<?php echo esc_attr(__('Users', 'wp-job-portal')); ?>">
                                        <?php if ((int) $wpjobportal_row->cannotunpublish === 1) { $wpjobportal_locked(__('Always on', 'wp-job-portal'), true); } else { $wpjobportal_toggle($wpjobportal_row, 'fieldpublished', 'fieldunpublished', (int) $wpjobportal_row->published === 1, __('Users', 'wp-job-portal')); } ?>
                                    </td>
                                    <td class="wjpl-fo-sw-c" data-l="<?php echo esc_attr(__('Visitors', 'wp-job-portal')); ?>">
                                        <?php if ((int) $wpjobportal_row->cannotunpublish === 1) { $wpjobportal_locked(__('Always on', 'wp-job-portal'), true); } else { $wpjobportal_toggle($wpjobportal_row, 'visitorfieldpublished', 'visitorfieldunpublished', (int) $wpjobportal_row->isvisitorpublished === 1, __('Visitors', 'wp-job-portal')); } ?>
                                    </td>
                                    <td class="wjpl-fo-sw-c" data-l="<?php echo esc_attr(__('Required', 'wp-job-portal')); ?>">
                                        <?php
                                        if ($wpjobportal_is_head) {
                                            echo '<span class="wjpl-muted">—</span>';
                                        } elseif ((int) $wpjobportal_row->sys === 1 || $wpjobportal_row->field === 'termsandconditions') {
                                            $wpjobportal_locked(__('Always', 'wp-job-portal'), true);
                                        } elseif (in_array($wpjobportal_row->field, array('resumefiles', 'photo', 'logo'), true) || $wpjobportal_row->userfieldtype === 'file') {
                                            $wpjobportal_locked(__('Not allowed', 'wp-job-portal'), false);
                                        } else {
                                            $wpjobportal_toggle($wpjobportal_row, 'fieldrequired', 'fieldnotrequired', (int) $wpjobportal_row->required === 1, __('Required', 'wp-job-portal'));
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else : ?>
                        <?php
                        $wpjobportal_actions = array(__('Reset filters', 'wp-job-portal') => admin_url('admin.php?page=wpjobportal_fieldordering&ff=' . $wpjobportal_ff));
                        if ($wpjobportal_ff !== 5 && $wpjobportal_ff !== 6) {
                            $wpjobportal_actions[__('Add User Field', 'wp-job-portal')] = admin_url('admin.php?page=wpjobportal_customfield&wpjobportallt=formuserfield&ff=' . $wpjobportal_ff);
                        }
                        WPJOBPORTAL_Admin_List_UI::empty_state(__('No fields match', 'wp-job-portal'), __('Try fewer words, or reset the filters.', 'wp-job-portal'), $wpjobportal_actions);
                        ?>
                    <?php endif; ?>
                    </div>
                    <input type="hidden" name="task" id="task" value="">
                    <input type="hidden" name="bulkop" value="">
                    <input type="hidden" name="pagenum" value="">
                    <input type="hidden" name="form_request" value="wpjobportal">
                    <input type="hidden" name="fieldfor" value="<?php echo esc_attr($wpjobportal_ff); ?>">
                    <input type="hidden" name="ff" value="<?php echo esc_attr($wpjobportal_ff); ?>">
                    <input type="hidden" name="fields_ordering_new" id="fields_ordering_new" value="">
                    <input type="hidden" name="ordering_for" value="fieldordering">
                    <input type="hidden" name="pagenum_for_ordering" value="<?php echo esc_attr(WPJOBPORTALrequest::getVar('pagenum', 'get', 1)); ?>">
                    <input type="hidden" name="_wpnonce" value="<?php echo esc_attr(wp_create_nonce($wpjobportal_fnonce)); ?>">
                </form>
                <p class="wjpl-hint wjpl-fo-note"><?php echo esc_html(__('File upload fields and text editor fields cannot be made required.', 'wp-job-portal')); ?></p>
            </section>
        </div>
    </div>
</div>
<?php
wp_register_script('wpjobportal-inline-handle', '', array(), WPJOBPORTAL_VERSION, false);
wp_enqueue_script('wpjobportal-inline-handle');
wp_add_inline_script('wpjobportal-inline-handle', "
    jQuery(function () {
        // 2.7's drag ordering: rows are id_<id>; after a move, Save Ordering appears and posts them in order.
        jQuery('#wpjobportal-table tbody').multisortable({
            items: '.sortable',
            selectedClass: 'wjpl-fo-dragsel',
            handle: '.wjpl-fo-grip',
            stop: function () {
                jQuery('.js-form-button').prop('hidden', false);
                jQuery('input#fields_ordering_new').val(jQuery('#wpjobportal-table tbody').sortable('serialize'));
            }
        });
        jQuery('#saveordering').on('click', function () {
            jQuery('#wpjobportal-list-form').attr('action', '" . esc_url_raw(admin_url('admin.php?page=wpjobportal&task=saveordering')) . "');
        });
    });
");
