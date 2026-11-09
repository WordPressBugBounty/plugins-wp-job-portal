<?php
    if(!defined('ABSPATH'))
     die('Restricted Access');
    wp_enqueue_script( 'wpjobportal-res-tables', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/responsivetable.js', array(), wpjobportal_asset_version('includes/js/responsivetable.js'), false );
    wp_enqueue_script('jquery-ui-sortable');
    $wpjobportal_protocol = is_ssl() ? "https://" : "http://";
    wp_enqueue_style('jquery-ui-css', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/jquery-ui-smoothness.css', array(), wpjobportal_asset_version('includes/css/jquery-ui-smoothness.css'));
    $wpjobportal_searchable_combo = array(
         (object) array('id' => 1, 'text' => esc_html(__('Enabled', 'wp-job-portal'))),
         (object) array('id' => 0, 'text' => esc_html(__('Disabled', 'wp-job-portal'))));
?>
<!-- main wrapper -->
<div id="wpjobportaladmin-wrapper" class="wjpl-page">
    <!-- left menu -->
    <div id="wpjobportaladmin-leftmenu">
        <?php  WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <div id="full_background" style="display:none;"></div>
        <div id="popup_main" style="display:none;">
            <span class="popup-top">
                <span id="popup_title" ></span>
                <img id="popup_cross" alt="<?php echo esc_attr__('Close', 'wp-job-portal'); ?>" onClick="closePopup();" src="<?php echo  esc_url(WPJOBPORTAL_PLUGIN_URL);?>includes/images/popup-close.png">
            </span>
            <?php $wpjobportal_ff = wpjobportal::$wpjobportal_data["fieldfor"]; ?>
            <form id="wpjobportal-form" class="popup-field-from" method="post" action="<?php echo esc_url_raw(admin_url("admin.php?page=wpjobportal_fieldordering&task=savesearchfieldordering&action=wpjobportaltask&fieldfor=$wpjobportal_ff&ff=$wpjobportal_ff"));?>">
                <div class="popup-field-wrapper">
                    <div class="popup-field-title"><?php echo  esc_html(__('User Search', 'wp-job-portal'));?></div>
                    <div class="popup-field-obj"><?php echo  wp_kses(WPJOBPORTALformfield::select('search_user', $wpjobportal_searchable_combo, 0, '', array('class' => 'inputbox one')),WPJOBPORTAL_ALLOWED_TAGS);?></div>
                </div>
                <div class="popup-field-wrapper">
                    <div class="popup-field-title"><?php echo  esc_html(__('Visitor Search', 'wp-job-portal'));?></div>
                    <div class="popup-field-obj"><?php echo  wp_kses(WPJOBPORTALformfield::select('search_visitor', $wpjobportal_searchable_combo, 0, '', array('class' => 'inputbox one')),WPJOBPORTAL_ALLOWED_TAGS);?></div>
                </div>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('form_request', 'wpjobportal'),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('id',''),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('fieldfor',wpjobportal::$wpjobportal_data['fieldfor']),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('_wpnonce',wp_create_nonce('wpjobportal_field_nonce')),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <div class="popup-act-btn-wrp">
                    <?php echo  wp_kses(WPJOBPORTALformfield::submitbutton('save', esc_html(__('Save', 'wp-job-portal')), array('class' => 'button popup-act-btn')),WPJOBPORTAL_ALLOWED_TAGS); ?>
                </div>
            </form>
        </div>
        <?php
            wp_register_script( 'wpjobportal-inline-handle', '', array(), WPJOBPORTAL_VERSION, false );
            wp_enqueue_script( 'wpjobportal-inline-handle' );
            $wpjobportal_inline_js_script = "
                jQuery(document).ready(function () {
                    jQuery('div#full_background').click(function () {
                        closePopup();
                    });
                    jQuery('table#wpjobportal-table tbody').sortable({
                        handle : '.grid-rows , .wpjobportal-text-left, .wpjobportal-order-grab-column',
                        update  : function () {
                            var abc =  jQuery('table#wpjobportal-table tbody').sortable('serialize');
                            jQuery('input#fields_ordering_new').val(abc);
                        }

                    });
                });

                function showPopupAndSetValues(id,title_string, search_user, search_visitor) {
                    jQuery('select#search_user').val(search_user);
                    jQuery('select#search_visitor').val(search_visitor);
                    jQuery('input#id').val(id);
                    jQuery('span#popup_title').html(title_string);
                    jQuery('div#full_background').css('display', 'block');
                    jQuery('div#popup_main').slideDown('slow');
                }

                function closePopup() {
                    jQuery('div#popup_main').slideUp('slow');
                    setTimeout(function () {
                        jQuery('div#full_background').hide();
                    }, 700);
                }
            ";
            wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
        ?>
        <?php
            $wpjobportal_msgkey = WPJOBPORTALincluder::getJSModel('fieldordering')->getMessagekey();
            WPJOBPORTALMessages::getLayoutMessage($wpjobportal_msgkey);
            $wpjobportal_search_combo = array(
                (object) array('id' => 0, 'text' => esc_html(__('Search Fields', 'wp-job-portal'))),
                (object) array('id' => 1, 'text' => esc_html(__('All Fields', 'wp-job-portal'))));
        ?>
        <!-- top head -->
        <?php ob_start(); ?>
                <?php
                    if(wpjobportal::$wpjobportal_data['fieldfor'] == 1){
                        echo esc_html(__('Company','wp-job-portal'));
                    }elseif(wpjobportal::$wpjobportal_data['fieldfor'] == 2){
                        echo esc_html(__('Job','wp-job-portal'));
                    }elseif(wpjobportal::$wpjobportal_data['fieldfor'] == 3){
                        echo esc_html(__('Resume','wp-job-portal'));
                    }
                    echo ' '.esc_html(__('Search Fields', 'wp-job-portal'));
                ?>
            <?php WPJOBPORTAL_Admin_List_UI::page_head(trim(html_entity_decode(wp_strip_all_tags(ob_get_clean()), ENT_QUOTES))); ?>
        <!-- page content -->
        <?php
        /*
         * Settings › Forms & fields › … search (design 4): one card; the fields of the search
         * form in their order, drag to reorder then Save Ordering; who can search by each
         * field (users / visitors), changed in the Edit window as in 2.7.
         */
        WPJOBPORTAL_Admin_List_UI::enqueue();
        $wpjobportal_ff = (int) wpjobportal::$wpjobportal_data['fieldfor'];
        $wpjobportal_rows = !empty(wpjobportal::$_data[0]) ? (array) wpjobportal::$_data[0] : array();
        // Shown as a switch; changing it opens the field's Edit window (where 2.7 changed it).
        $wpjobportal_state = function ($on, $open, $label) {
            echo '<a href="#" class="wjpl-sw' . ($on ? ' is-on' : '') . '" onclick="' . esc_attr($open) . '" aria-label="' . esc_attr($label . ': ' . ($on ? __('On', 'wp-job-portal') : __('Off', 'wp-job-portal'))) . '"><span class="wjpl-sw-k"></span><span class="wjpl-sw-t">' . esc_html($on ? __('On', 'wp-job-portal') : __('Off', 'wp-job-portal')) . '</span></a>';
        };
        ?>
        <div class="wjpl">
            <section class="wjpl-card wjpl-fo" aria-label="<?php echo esc_attr(__('Search Fields', 'wp-job-portal')); ?>">
                <div class="wjpl-filters">
                    <div class="wjpl-tb1">
                        <form class="wjpl-fo-show" name="wpjobportalform" id="wpjobportalform" method="post" action="<?php echo esc_url_raw(admin_url('admin.php?page=wpjobportal_fieldordering&wpjobportallt=searchfields&ff=' . $wpjobportal_ff)); ?>">
                            <label class="wjpl-sort"><?php echo esc_html(__('Show', 'wp-job-portal')); ?>
                                <select class="wjpl-sel" name="search" id="search" onchange="this.form.submit();">
                                    <option value="0"<?php selected((int) wpjobportal::$_data['filter']['search'], 0); ?>><?php echo esc_html(__('Search Fields', 'wp-job-portal')); ?></option>
                                    <option value="1"<?php selected((int) wpjobportal::$_data['filter']['search'], 1); ?>><?php echo esc_html(__('All Fields', 'wp-job-portal')); ?></option>
                                </select>
                            </label>
                            <input type="hidden" name="fieldfor" value="<?php echo esc_attr($wpjobportal_ff); ?>">
                            <noscript><button class="wjpl-btn wjpl-btn-sec" type="submit" name="btnsubmit" value="1"><?php echo esc_html(__('Go', 'wp-job-portal')); ?></button></noscript>
                        </form>
                        <span class="wjpl-fo-sum">
                            <?php
                            /* translators: %s: number of fields */
                            echo esc_html(sprintf(_n('%s field', '%s fields', count($wpjobportal_rows), 'wp-job-portal'), number_format_i18n(count($wpjobportal_rows))));
                            ?>
                            <span class="wjpl-fo-hint"><?php echo esc_html(__('Drag a row to change the order on the search form.', 'wp-job-portal')); ?></span>
                        </span>
                        <button type="submit" form="wjpl-sf-form" name="save" class="wjpl-btn wjpl-btn-pri js-form-button" hidden><?php echo esc_html(__('Save', 'wp-job-portal') . ' ' . __('Ordering', 'wp-job-portal')); ?></button>
                    </div>
                </div>
                <?php if (!empty($wpjobportal_rows)) : ?>
                    <form id="wjpl-sf-form" class="search-fields-form" method="post" action="<?php echo esc_url_raw(admin_url("admin.php?page=wpjobportal_fieldordering&action=wpjobportaltask&task=savesearchfieldorderingFromForm&fieldfor=$wpjobportal_ff&ff=$wpjobportal_ff")); ?>">
                        <div class="wjpl-tw">
                            <table class="wjpl-table wjpl-fo-table" id="wpjobportal-table">
                                <thead><tr>
                                    <th class="wjpl-fo-grip-c" aria-label="<?php echo esc_attr(__('Ordering', 'wp-job-portal')); ?>"></th>
                                    <th><?php echo esc_html(__('Title', 'wp-job-portal')); ?></th>
                                    <th class="wjpl-fo-sw-c"><?php echo esc_html(__('User Search', 'wp-job-portal')); ?></th>
                                    <th class="wjpl-fo-sw-c"><?php echo esc_html(__('Visitor Search', 'wp-job-portal')); ?></th>
                                </tr></thead>
                                <tbody>
                                <?php foreach ($wpjobportal_rows as $wpjobportal_row) :
                                    $wpjobportal_ftitle = wpjobportal::wpjobportal_getVariableValue($wpjobportal_row->fieldtitle);
                                    $wpjobportal_open = 'showPopupAndSetValues(' . (int) $wpjobportal_row->id . ", '" . esc_js($wpjobportal_ftitle) . "', " . (int) $wpjobportal_row->search_user . ', ' . (int) $wpjobportal_row->search_visitor . '); return false;';
                                    ?>
                                    <tr id="id_<?php echo esc_attr($wpjobportal_row->id); ?>">
                                        <td class="wjpl-fo-grip-c"><span class="wjpl-fo-grip" title="<?php echo esc_attr(__('Drag to reorder', 'wp-job-portal')); ?>" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="6" r="1.6"/><circle cx="15" cy="6" r="1.6"/><circle cx="9" cy="12" r="1.6"/><circle cx="15" cy="12" r="1.6"/><circle cx="9" cy="18" r="1.6"/><circle cx="15" cy="18" r="1.6"/></svg></span></td>
                                        <td class="wjpl-c-main">
                                            <a class="wjpl-title" href="#" onclick="<?php echo esc_attr($wpjobportal_open); ?>"><?php echo esc_html($wpjobportal_ftitle); ?></a>
                                            <span class="wjpl-ra"><a href="#" onclick="<?php echo esc_attr($wpjobportal_open); ?>"><?php echo esc_html(__('Edit', 'wp-job-portal')); ?></a></span>
                                        </td>
                                        <td class="wjpl-fo-sw-c" data-l="<?php echo esc_attr(__('User Search', 'wp-job-portal')); ?>"><?php $wpjobportal_state((int) $wpjobportal_row->search_user === 1, $wpjobportal_open, __('User Search', 'wp-job-portal')); ?></td>
                                        <td class="wjpl-fo-sw-c" data-l="<?php echo esc_attr(__('Visitor Search', 'wp-job-portal')); ?>"><?php $wpjobportal_state((int) $wpjobportal_row->search_visitor === 1, $wpjobportal_open, __('Visitor Search', 'wp-job-portal')); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <input type="hidden" name="form_request" value="wpjobportal">
                        <input type="hidden" name="fieldfor" value="<?php echo esc_attr($wpjobportal_ff); ?>">
                        <input type="hidden" name="fields_ordering_new" id="fields_ordering_new" value="">
                        <input type="hidden" name="_wpnonce" value="<?php echo esc_attr(wp_create_nonce('wpjobportal_field_nonce')); ?>">
                    </form>
                <?php else : ?>
                    <?php WPJOBPORTAL_Admin_List_UI::empty_state(__('No search fields', 'wp-job-portal'), __('Show all fields to pick which ones people can search by.', 'wp-job-portal')); ?>
                <?php endif; ?>
            </section>
        </div>
    </div>
</div>
<?php
// After a drag, Save Ordering appears (2.7 showed it all the time, with Cancel).
wp_add_inline_script('wpjobportal-inline-handle', "jQuery(function () { jQuery('table#wpjobportal-table tbody').sortable('option', 'handle', '.wjpl-fo-grip').on('sortupdate', function () { jQuery('.js-form-button').prop('hidden', false); }); });");
