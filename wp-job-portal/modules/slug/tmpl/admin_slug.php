<?php
if (!defined('ABSPATH')) die('Restricted Access');
/*
 * Pages & SEO › Permalinks (design 4): the words in the job portal's page addresses,
 * edited in place (2.7 used a popup per row), and the two prefixes.
 */
WPJOBPORTAL_Admin_List_UI::enqueue();
$wpjobportal_rows = isset(wpjobportal::$_data[0]) ? (array) wpjobportal::$_data[0] : array();
$wpjobportal_total = isset(wpjobportal::$_data['total']) ? (int) wpjobportal::$_data['total'] : count($wpjobportal_rows);
$search = (string) wpjobportal::$_data['slug'];
$wpjobportal_pagenum = (int) WPJOBPORTALrequest::getVar('pagenum', 'get', 1);
$wpjobportal_nonce = wp_create_nonce('wpjobportal_slug_nonce');
$wpjobportal_reset_url = wp_nonce_url(admin_url('admin.php?page=wpjobportal_slug&task=resetallslugs&action=wpjobportaltask'), 'wpjobportal_slug_nonce');
// Every 2.7 description starts "Slug for …"; the column already says that.
$wpjobportal_page_name = function ($description) {
    $text = trim((string) wpjobportal::wpjobportal_getVariableValue($description));
    $short = preg_replace('/^slug\s+for\s+/i', '', $text);
    return $short !== '' ? wpjobportalphplib::wpJP_ucfirst($short) : $text;
};
$wpjobportal_prefix_form = function ($task, $value, $label, $help) use ($wpjobportal_nonce) {
    ?>
    <form class="wjpl-slug-prefix" method="post" action="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_slug&task=' . $task)); ?>">
        <label class="wjpl-slug-prefix-l" for="wjpl-<?php echo esc_attr($task); ?>"><?php echo esc_html($label); ?></label>
        <div class="wjpl-slug-prefix-c">
            <input class="wjpl-inp" type="text" id="wjpl-<?php echo esc_attr($task); ?>" name="prefix" value="<?php echo esc_attr($value); ?>">
            <button class="wjpl-btn wjpl-btn-sec" type="submit" name="btnsubmit" value="1"><?php echo esc_html(__('Save', 'wp-job-portal')); ?></button>
            <div class="wjpl-small"><?php echo esc_html($help); ?></div>
        </div>
        <input type="hidden" name="form_request" value="wpjobportal">
        <input type="hidden" name="_wpnonce" value="<?php echo esc_attr($wpjobportal_nonce); ?>">
    </form>
    <?php
};
?>
<div id="wpjobportaladmin-wrapper" class="wjpl-page">
    <div id="wpjobportaladmin-leftmenu">
        <?php WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <?php WPJOBPORTALMessages::getLayoutMessage(WPJOBPORTALincluder::getJSModel('slug')->getMessagekey()); ?>
        <div class="wjpl">
            <?php
            WPJOBPORTAL_Admin_List_UI::top_bar(WPJOBPORTAL_Admin_List_UI::menu_crumbs());
            WPJOBPORTAL_Admin_List_UI::title_row(
                __('Permalinks', 'wp-job-portal'),
                /* translators: %s: number of page addresses */
                sprintf(_n('%s page address', '%s page addresses', $wpjobportal_total, 'wp-job-portal'), number_format_i18n($wpjobportal_total)) . ' · ' . __('The words used in the job portal\'s links.', 'wp-job-portal'),
                array(__('Reset all to defaults', 'wp-job-portal') => $wpjobportal_reset_url)
            );
            WPJOBPORTAL_Admin_List_UI::section_tabs();
            ?>
            <section class="wjpl-card wjpl-slug-prefixes" aria-label="<?php echo esc_attr(__('Prefixes', 'wp-job-portal')); ?>">
                <?php
                $wpjobportal_prefix_form('savehomeprefix', wpjobportal::$_configuration['home_slug_prefix'], __('Home page prefix', 'wp-job-portal'), __('This prefix will be added to slug in case of homepage links', 'wp-job-portal'));
                $wpjobportal_prefix_form('saveprefix', wpjobportal::$_configuration['slug_prefix'], __('Conflict prefix', 'wp-job-portal'), __('This prefix will be added to slug in case of conflict', 'wp-job-portal'));
                ?>
            </section>
            <section class="wjpl-card" aria-label="<?php echo esc_attr(__('Page addresses', 'wp-job-portal')); ?>">
                <form class="wjpl-filters" method="post" action="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_slug')); ?>">
                    <input type="hidden" name="WPJOBPORTAL_form_search" value="WPJOBPORTAL_SEARCH">
                    <div class="wjpl-tb2">
                        <div class="wjpl-search">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                            <input class="wjpl-inp" type="search" name="slug" id="slug" value="<?php echo esc_attr($search); ?>" placeholder="<?php echo esc_attr(__('Search By Slug', 'wp-job-portal')); ?>" aria-label="<?php echo esc_attr(__('Search By Slug', 'wp-job-portal')); ?>">
                        </div>
                        <span></span>
                        <span class="wjpl-tb2-acts">
                            <button class="wjpl-btn wjpl-btn-pri" type="submit" name="btnsubmit" value="1"><?php echo esc_html(__('Search', 'wp-job-portal')); ?></button>
                            <button class="wjpl-linkbtn" type="submit" onclick="this.form.slug.value='';"><?php echo esc_html(__('Reset', 'wp-job-portal')); ?></button>
                        </span>
                    </div>
                </form>
                <?php if (!empty($wpjobportal_rows)) : ?>
                    <form id="wpjobportal-list-form" method="post" action="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_slug&task=saveSlug')); ?>">
                        <div class="wjpl-tw">
                            <table class="wjpl-table">
                                <thead><tr>
                                    <th><?php echo esc_html(__('Page', 'wp-job-portal')); ?></th>
                                    <th class="wjpl-slug-col"><?php echo esc_html(__('Word in the link', 'wp-job-portal')); ?></th>
                                </tr></thead>
                                <tbody>
                                <?php foreach ($wpjobportal_rows as $wpjobportal_row) : ?>
                                    <tr>
                                        <td class="wjpl-c-main"><label class="wjpl-title" for="wjpl-slug-<?php echo esc_attr($wpjobportal_row->id); ?>"><?php echo esc_html($wpjobportal_page_name($wpjobportal_row->description)); ?></label></td>
                                        <td class="wjpl-slug-col" data-l="<?php echo esc_attr(__('Word in the link', 'wp-job-portal')); ?>">
                                            <input class="wjpl-inp wjpl-slug-inp" type="text" id="wjpl-slug-<?php echo esc_attr($wpjobportal_row->id); ?>" name="<?php echo esc_attr($wpjobportal_row->id); ?>" value="<?php echo esc_attr($wpjobportal_row->slug); ?>" spellcheck="false">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="wjpl-slug-save">
                            <span class="wjpl-small"><?php echo esc_html(__('This button will only save slugs on current page', 'wp-job-portal')); ?></span>
                            <button class="wjpl-btn wjpl-btn-pri" type="submit" name="btnsubmit" value="1"><?php echo esc_html(__('Save', 'wp-job-portal')); ?></button>
                        </div>
                        <input type="hidden" name="task" value="">
                        <input type="hidden" name="pagenum" value="<?php echo $wpjobportal_pagenum > 1 ? esc_attr($wpjobportal_pagenum) : ''; ?>">
                        <input type="hidden" name="form_request" value="wpjobportal">
                        <input type="hidden" name="_wpnonce" value="<?php echo esc_attr($wpjobportal_nonce); ?>">
                    </form>
                <?php elseif ($search !== '') : ?>
                    <?php WPJOBPORTAL_Admin_List_UI::empty_state(__('Nothing matches this search', 'wp-job-portal'), __('Try fewer letters, or reset the search.', 'wp-job-portal')); ?>
                <?php else : ?>
                    <?php WPJOBPORTAL_Admin_List_UI::empty_state(__('No page addresses', 'wp-job-portal'), __('Use Reset all to defaults in the ⋯ menu to restore them.', 'wp-job-portal')); ?>
                <?php endif; ?>
                <?php WPJOBPORTAL_Admin_List_UI::footer($wpjobportal_total, __('page addresses', 'wp-job-portal'), function ($page) {
                    return add_query_arg(array('page' => 'wpjobportal_slug', 'pagenum' => $page > 1 ? $page : null), admin_url('admin.php'));
                }); ?>
            </section>

            <dialog id="wjpl-slug-reset" class="wjpl-dialog" aria-labelledby="wjpl-slug-reset-t">
                <h2 id="wjpl-slug-reset-t"><?php echo esc_html(__('Reset all page addresses?', 'wp-job-portal')); ?></h2>
                <p><?php echo esc_html(__('Every word you changed goes back to its default. Links people saved with your words will stop working.', 'wp-job-portal')); ?></p>
                <div class="wjpl-dlg-acts">
                    <button type="button" class="wjpl-btn wjpl-btn-sec" onclick="this.closest('dialog').close();"><?php echo esc_html(__('Cancel', 'wp-job-portal')); ?></button>
                    <a class="wjpl-btn wjpl-btn-danger" href="<?php echo esc_url($wpjobportal_reset_url); ?>"><?php echo esc_html(__('Reset all', 'wp-job-portal')); ?></a>
                </div>
            </dialog>
        </div>
    </div>
</div>
<?php
wp_register_script('wpjobportal-inline-handle', '', array(), WPJOBPORTAL_VERSION, false);
wp_enqueue_script('wpjobportal-inline-handle');
// The ⋯ menu's reset link asks first.
wp_add_inline_script('wpjobportal-inline-handle', 'document.addEventListener("click", function (e) { var a = e.target.closest(".wjpl-more a[href*=\\"task=resetallslugs\\"]"); if (a) { e.preventDefault(); a.closest("details").open = false; document.getElementById("wjpl-slug-reset").showModal(); } });');
