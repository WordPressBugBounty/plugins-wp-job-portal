<?php
if (!defined('ABSPATH')) die('Restricted Access');
/**
 * The plugin's admin menu (design 4): 6 groups, 14 sections. What it holds and
 * which entry lights up for the page being shown is decided by
 * WPJOBPORTAL_Admin_Menu_Service; this file only draws it.
 *
 * Every admin screen includes this file in its left column, so all of them get
 * the menu without their own template changing.
 */
$wpjobportal_sections = WPJOBPORTAL_Admin_Menu_Service::sections();
$wpjobportal_current = WPJOBPORTAL_Admin_Menu_Service::current($wpjobportal_sections);
$wpjobportal_counts = WPJOBPORTAL_Admin_Menu_Service::counts();
// Not cached with the others: WordPress's own update data changes on its own schedule.
$wpjobportal_counts['updates'] = WPJOBPORTAL_Admin_Menu_Service::update_count();
$wpjobportal_groups = WPJOBPORTAL_Admin_Menu_Service::groups();

wp_register_script('wpjobportal-menu-handle', '', array(), WPJOBPORTAL_VERSION, false);
wp_enqueue_script('wpjobportal-menu-handle');

// Wide by default, as in the design; the toggle narrows it to icons and the
// choice is kept in a cookie. It is applied as the page is drawn, so the menu
// does not first appear in one width and then jump to the other. On a phone or
// a small tablet the wide menu fills the screen above the page, so it starts
// narrow there unless it was opened.
$wpjobportal_menu_cookie = WPJOBPORTALrequest::cookie('wpjobportaladmin_menu_mini');
if ($wpjobportal_menu_cookie !== '1') {
    $wpjobportal_small_check = ($wpjobportal_menu_cookie === '0') ? 'true' : 'window.innerWidth>782';
    wp_print_inline_script_tag('(function(w){if(w&&(' . $wpjobportal_small_check . ')){w.classList.add("menu-collasped-active");}})(document.getElementById("wpjobportaladmin-wrapper"));');
}
?>
<?php
$wpjobportal_mode_label = class_exists('WPJOBPORTAL_Product_Mode_Service') ? WPJOBPORTAL_Product_Mode_Service::get_label() : '';
$wpjobportal_create = array(
    array(__('New job', 'wp-job-portal'), 'wpjobportal_job&wpjobportallt=formjob', 'job'),
    array(__('New company', 'wp-job-portal'), 'wpjobportal_company&wpjobportallt=formcompany', 'co'),
);
if (in_array('departments', (array) wpjobportal::$_active_addons, true)) {
    $wpjobportal_create[] = array(__('New department', 'wp-job-portal'), 'wpjobportal_departments&wpjobportallt=formdepartment', 'co');
}
// No "New resume": resumes are created by job seekers; the admin form has no way to choose one (it would belong to the admin). As in 2.7, admins edit resumes only.
if (in_array('jobalert', (array) wpjobportal::$_active_addons, true) && (!class_exists('WPJOBPORTAL_Feature_Tiers') || WPJOBPORTAL_Feature_Tiers::job_alert_admin_tools())) {
    $wpjobportal_create[] = array(__('New job alert', 'wp-job-portal'), 'wpjobportal_jobalert&wpjobportallt=formjobalert', 'mail');
}
?>
<div class="wjpm-brand">
    <a class="wjpm-mark" href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal')); ?>" aria-label="<?php echo esc_attr(__('WP Job Portal dashboard', 'wp-job-portal')); ?>">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5h6v2"/></svg>
    </a>
    <span class="wjpm-bt">
        <a class="wjpm-name" href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal')); ?>"><?php echo esc_html(__('WP Job Portal', 'wp-job-portal')); ?></a>
        <?php if ($wpjobportal_mode_label !== '') : ?>
            <a class="wjpm-mode" href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_postinstallation&wpjobportallt=quickstart')); ?>" title="<?php echo esc_attr(__('Site mode', 'wp-job-portal')); ?>"><?php echo esc_html($wpjobportal_mode_label); ?><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg></a>
        <?php endif; ?>
    </span>
    <button type="button" class="wjpm-burger wjpm-toggle" aria-label="<?php echo esc_attr(__('Show or hide the menu', 'wp-job-portal')); ?>"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button>
</div>
<div class="wjpm-top">
    <div class="wjpm-search">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
        <input type="search" id="wjpm-q" autocomplete="off" role="combobox" aria-expanded="false" aria-controls="wjpm-results" aria-label="<?php echo esc_attr(__('Search pages, jobs, companies, resumes and people (Ctrl K)', 'wp-job-portal')); ?>" placeholder="<?php echo esc_attr(__('Search…', 'wp-job-portal')); ?>">
        <kbd>Ctrl K</kbd>
        <div class="wjpm-pop" id="wjpm-results" role="listbox" hidden></div>
    </div>
    <details class="wjpm-new">
        <summary aria-label="<?php echo esc_attr(__('Create new', 'wp-job-portal')); ?>" title="<?php echo esc_attr(__('Create new', 'wp-job-portal')); ?>"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg></summary>
        <div class="wjpm-pop" role="menu">
            <?php foreach ($wpjobportal_create as $wpjobportal_c) : ?>
                <a role="menuitem" href="<?php echo esc_url(admin_url('admin.php?page=' . $wpjobportal_c[1])); ?>"><?php echo WPJOBPORTAL_Admin_Menu_Service::icon($wpjobportal_c[2]); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed SVG markup ?><?php echo esc_html($wpjobportal_c[0]); ?></a>
            <?php endforeach; ?>
        </div>
    </details>
</div>
<nav class="wjpm" aria-label="<?php echo esc_attr(__('WP Job Portal', 'wp-job-portal')); ?>">
<?php foreach ($wpjobportal_groups as $wpjobportal_group_id => $wpjobportal_group_label) :
    $wpjobportal_in_group = array_filter($wpjobportal_sections, function ($section) use ($wpjobportal_group_id) {
        return isset($section['group']) && $section['group'] === $wpjobportal_group_id;
    });
    if (empty($wpjobportal_in_group)) {
        continue;
    } ?>
    <div class="wjpm-grp">
        <h6><?php echo esc_html($wpjobportal_group_label); ?></h6>
        <?php foreach ($wpjobportal_in_group as $wpjobportal_section) :
            $wpjobportal_is_current = $wpjobportal_section['id'] === $wpjobportal_current['section'];
            $wpjobportal_subs = array();
            foreach ($wpjobportal_section['subs'] as $wpjobportal_index => $wpjobportal_sub) {
                $wpjobportal_sub_current = $wpjobportal_is_current && $wpjobportal_current['sub'] === $wpjobportal_index;
                if (!empty($wpjobportal_sub['only_when_current']) && !$wpjobportal_sub_current) {
                    continue;
                }
                $wpjobportal_sub['is_current'] = $wpjobportal_sub_current;
                $wpjobportal_subs[] = $wpjobportal_sub;
            }
            // A section opens its first page; one without pages has its own link.
            $wpjobportal_first = null;
            foreach ($wpjobportal_subs as $wpjobportal_sub) {
                if (empty($wpjobportal_sub['only_when_current'])) {
                    $wpjobportal_first = $wpjobportal_sub;
                    break;
                }
            }
            $wpjobportal_href = $wpjobportal_first ? WPJOBPORTAL_Admin_Menu_Service::link($wpjobportal_first) : WPJOBPORTAL_Admin_Menu_Service::link($wpjobportal_section);
            $wpjobportal_count_key = isset($wpjobportal_section['count']) ? $wpjobportal_section['count'] : '';
            $wpjobportal_fresh_key = isset($wpjobportal_section['fresh']) ? $wpjobportal_section['fresh'] : '';
            $wpjobportal_sub_id = 'wjpm-sub-' . sanitize_html_class($wpjobportal_section['id']);
            ?>
            <div class="wjpm-sec<?php echo $wpjobportal_is_current ? ' cur open' : ''; ?>">
                <a class="wjpm-it" href="<?php echo esc_url($wpjobportal_href); ?>" title="<?php echo esc_attr($wpjobportal_section['label']); ?>"<?php echo $wpjobportal_is_current ? ' aria-current="true"' : ''; ?>>
                    <span class="wjpm-ibox"><?php echo WPJOBPORTAL_Admin_Menu_Service::icon($wpjobportal_section['icon']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed SVG markup from the service ?></span>
                    <span class="wjpm-lbl"><?php echo esc_html($wpjobportal_section['label']); ?></span>
                    <span class="wjpm-end">
                        <?php if (!empty($wpjobportal_section['tag'])) : ?><span class="wjpm-tag"><?php echo esc_html($wpjobportal_section['tag']); ?></span><?php endif; ?>
                        <?php if ($wpjobportal_fresh_key !== '' && !empty($wpjobportal_counts[$wpjobportal_fresh_key])) : ?>
                            <span class="wjpm-new" title="<?php echo esc_attr(__('Applications in the last 7 days', 'wp-job-portal')); ?>"><i></i><?php echo esc_html(sprintf(/* translators: %d: number of new applications */ __('%d new', 'wp-job-portal'), $wpjobportal_counts[$wpjobportal_fresh_key])); ?></span>
                        <?php elseif ($wpjobportal_count_key !== '' && isset($wpjobportal_counts[$wpjobportal_count_key]) && (!in_array($wpjobportal_count_key, array('approvals', 'updates'), true) || $wpjobportal_counts[$wpjobportal_count_key] > 0)) : ?>
                            <span class="wjpm-cnt<?php echo in_array($wpjobportal_count_key, array('approvals', 'updates'), true) ? ' wjpm-due' : ''; ?>"><?php echo esc_html(number_format_i18n($wpjobportal_counts[$wpjobportal_count_key])); ?></span>
                        <?php endif; ?>
                    </span>
                </a>
                <?php if (!empty($wpjobportal_subs)) : ?>
                    <button type="button" class="wjpm-chev" aria-expanded="<?php echo $wpjobportal_is_current ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr($wpjobportal_sub_id); ?>" title="<?php echo esc_attr(sprintf(/* translators: %s: menu section */ __('Show the pages in %s', 'wp-job-portal'), $wpjobportal_section['label'])); ?>"><?php echo WPJOBPORTAL_Admin_Menu_Service::icon('chev'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed SVG markup ?></button>
                    <div class="wjpm-sub" id="<?php echo esc_attr($wpjobportal_sub_id); ?>">
                        <span class="wjpm-flyhead"><?php echo esc_html($wpjobportal_section['label']); ?></span>
                        <?php foreach ($wpjobportal_subs as $wpjobportal_sub) : ?>
                            <a class="<?php echo $wpjobportal_sub['is_current'] ? 'on' : ''; ?>" href="<?php echo esc_url(WPJOBPORTAL_Admin_Menu_Service::link($wpjobportal_sub)); ?>"<?php echo $wpjobportal_sub['is_current'] ? ' aria-current="page"' : ''; ?>>
                                <?php echo esc_html($wpjobportal_sub['label']); ?><?php if (!empty($wpjobportal_sub['addon'])) : ?><span class="wjpm-ao"><?php echo esc_html(__('add-on', 'wp-job-portal')); ?></span><?php endif; ?>
                            </a>
                            <?php if ($wpjobportal_sub['is_current'] && !empty($wpjobportal_sub['third'])) : ?>
                                <div class="wjpm-3" role="group" aria-label="<?php echo esc_attr($wpjobportal_sub['label']); ?>">
                                    <?php foreach ($wpjobportal_sub['third'] as $wpjobportal_third_index => $wpjobportal_third) :
                                        $wpjobportal_third_on = $wpjobportal_current['third'] === $wpjobportal_third_index; ?>
                                        <a class="<?php echo $wpjobportal_third_on ? 'on3' : ''; ?>" href="<?php echo esc_url(WPJOBPORTAL_Admin_Menu_Service::link($wpjobportal_third)); ?>"<?php echo $wpjobportal_third_on ? ' aria-current="page"' : ''; ?>>
                                            <?php echo esc_html($wpjobportal_third['label']); ?><?php if (!empty($wpjobportal_third['addon'])) : ?><span class="wjpm-ao"><?php echo esc_html(__('add-on', 'wp-job-portal')); ?></span><?php endif; ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endforeach; ?>
<?php
// Add-ons that still print their own links into the old menu: our suites now
// register through the menu service, so anything printed here comes from a
// third-party add-on. Shown under its own heading rather than dropped.
$wpjobportal_controller = sanitize_key((string) WPJOBPORTALrequest::getVar('page', 'get', 'wpjobportal'));
$wpjobportal_layout = sanitize_key((string) WPJOBPORTALrequest::getVar('wpjobportallt', 'get', ''));
ob_start();
foreach (array('wpjobportal_addons_sidemenue_admin_jobalert', 'wpjobportal_addons_admin_sidemenu_suite_groups', 'wpjobportal_addons_admin_sidemenu_links_for_reports', 'wpjobportal_addons_admin_sidemenu_links_for_message', 'wpjobportal_addons_admin_sidemenu_package', 'wpjobportal_addons_admin_sidemenu_links_for_folder', 'wpjobportal_addons_admin_sidemenu_links_for_tags') as $wpjobportal_legacy_hook) {
    do_action($wpjobportal_legacy_hook, $wpjobportal_controller, $wpjobportal_layout);
}
$wpjobportal_legacy_links = trim(ob_get_clean());
if ($wpjobportal_legacy_links !== '') : ?>
    <div class="wjpm-grp wjpm-legacy">
        <h6><?php echo esc_html(__('More add-ons', 'wp-job-portal')); ?></h6>
        <ul class="wpjobportaladmin-sidebar-menu"><?php echo $wpjobportal_legacy_links; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup printed by add-on callbacks, as before ?></ul>
    </div>
<?php endif; ?>
    <div class="wjpm-foot">
        <button type="button" class="wjpm-toggle" id="wpjobportaladmin-menu-toggle" title="<?php echo esc_attr(__('Collapse or expand the menu', 'wp-job-portal')); ?>">
            <?php echo WPJOBPORTAL_Admin_Menu_Service::icon('collapse'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed SVG markup ?>
            <span class="wjpm-lbl"><?php echo esc_html(__('Collapse', 'wp-job-portal')); ?></span>
        </button>
        <a class="<?php echo ($wpjobportal_controller === 'wpjobportal' && $wpjobportal_layout === 'help') ? 'cur' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal&wpjobportallt=help')); ?>" title="<?php echo esc_attr(__('Help & docs', 'wp-job-portal')); ?>">
            <?php echo WPJOBPORTAL_Admin_Menu_Service::icon('help'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed SVG markup ?>
            <span class="wjpm-lbl"><?php echo esc_html(__('Help & docs', 'wp-job-portal')); ?></span>
        </a>
        <span class="wjpm-ver"><?php echo esc_html(sprintf(/* translators: %s: version */ __('Version %s', 'wp-job-portal'), WPJOBPORTAL_VERSION)); ?></span>
    </div>
</nav>
<?php
// Every page in the menu, for the jump-to-page search.
$wpjobportal_jump = array();
foreach ($wpjobportal_sections as $wpjobportal_section) {
    if (empty($wpjobportal_section['subs'])) {
        $wpjobportal_jump[] = array('t' => $wpjobportal_section['label'], 'p' => '', 'u' => WPJOBPORTAL_Admin_Menu_Service::link($wpjobportal_section));
    }
    foreach ($wpjobportal_section['subs'] as $wpjobportal_sub) {
        if (empty($wpjobportal_sub['only_when_current'])) {
            $wpjobportal_jump[] = array('t' => $wpjobportal_sub['label'], 'p' => $wpjobportal_section['label'], 'u' => WPJOBPORTAL_Admin_Menu_Service::link($wpjobportal_sub));
        }
        foreach (isset($wpjobportal_sub['third']) ? $wpjobportal_sub['third'] : array() as $wpjobportal_third) {
            $wpjobportal_jump[] = array('t' => $wpjobportal_third['label'], 'p' => $wpjobportal_section['label'] . ' › ' . $wpjobportal_sub['label'], 'u' => WPJOBPORTAL_Admin_Menu_Service::link($wpjobportal_third));
        }
    }
}
$wpjobportal_jump[] = array('t' => __('Help & docs', 'wp-job-portal'), 'p' => '', 'u' => admin_url('admin.php?page=wpjobportal&wpjobportallt=help'));
$wpjobportal_menu_js_script = 'window.wpjobportalMenuPages = ' . wp_json_encode($wpjobportal_jump) . ';
    window.wpjobportalMenuText = ' . wp_json_encode(array('none' => __('Nothing matches', 'wp-job-portal'), 'pages' => __('Pages', 'wp-job-portal'), 'searching' => __('Searching jobs and people…', 'wp-job-portal'))) . ';
    window.wpjobportalMenuSearch = ' . wp_json_encode(array('url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('wpjobportal_admin_search'))) . ';
    jQuery(function ($) {
        var pageWrapper = $("#wpjobportaladmin-wrapper");
        $(".wjpm-toggle").on("click", function (e) {
            e.preventDefault();
            var wide = !pageWrapper.hasClass("menu-collasped-active");
            pageWrapper.toggleClass("menu-collasped-active", wide);
            document.cookie = "wpjobportaladmin_menu_mini=" + (wide ? "0" : "1") + "; expires=Sat, 01 Jan 2050 00:00:00 UTC; path=/";
        });
        $(".wjpm .wjpm-chev").on("click", function () {
            var sec = $(this).closest(".wjpm-sec");
            sec.toggleClass("open");
            $(this).attr("aria-expanded", sec.hasClass("open") ? "true" : "false");
        });

        // Search: pages of the menu at once, then jobs, companies, resumes and
        // people from the server. Every word must appear, in any order.
        var $q = $("#wjpm-q"), $res = $("#wjpm-results"), pages = [], records = [], hits = [], at = 0, timer = null, asked = "";
        function render(loading) {
            hits = pages.map(function (p) { return $.extend({ g: window.wpjobportalMenuText.pages }, p); }).concat(records);
            at = 0;
            $res.empty();
            var group = "";
            hits.forEach(function (p, i) {
                if (p.g !== group) { group = p.g; $res.append($("<div class=\"wjpm-grp-h\"></div>").text(group)); }
                var $a = $("<a role=\"option\"></a>").attr("href", p.u).toggleClass("on", i === 0);
                $a.append($("<span></span>").text(p.t));
                if (p.p) { $a.append($("<small></small>").text(p.p)); }
                $res.append($a);
            });
            if (loading) { $res.append($("<div class=\"wjpm-none\"></div>").text(window.wpjobportalMenuText.searching)); }
            else if (!hits.length) { $res.append($("<div class=\"wjpm-none\"></div>").text(window.wpjobportalMenuText.none)); }
            $res.prop("hidden", false); $q.attr("aria-expanded", "true");
            place();
        }
        // The menu clips what sticks out of it, so the list is fixed to the
        // window, under the search box, and may run over the page.
        function place() {
            if ($res.prop("hidden")) { return; }
            var box = $q.closest(".wjpm-search")[0].getBoundingClientRect(), vw = document.documentElement.clientWidth;
            var width = Math.max(box.width + 2, Math.min(460, vw - 16));
            var left = document.dir === "rtl" ? box.right + 1 - width : box.left - 1;
            $res.css({ top: box.bottom + 6, left: Math.max(8, Math.min(left, vw - width - 8)), width: width });
        }
        $(window).on("resize", place); $("#wpjobportaladmin-leftmenu").on("scroll", place);
        function draw() {
            var text = $q.val().trim(), words = text.toLowerCase().split(/\s+/).filter(Boolean);
            clearTimeout(timer);
            records = [];
            if (!words.length) { pages = []; hits = []; $res.prop("hidden", true).empty(); $q.attr("aria-expanded", "false"); return; }
            pages = window.wpjobportalMenuPages.filter(function (p) {
                var hay = (p.p + " " + p.t).toLowerCase();
                return words.every(function (w) { return hay.indexOf(w) !== -1; });
            }).slice(0, 6);
            var ask = words.join("").length >= 2;
            render(ask);
            if (!ask) { return; }
            timer = setTimeout(function () {
                asked = text;
                $.getJSON(window.wpjobportalMenuSearch.url, { action: "wpjobportal_admin_search", nonce: window.wpjobportalMenuSearch.nonce, q: text }).done(function (r) {
                    if (asked !== $q.val().trim()) { return; }
                    records = (r && r.success && r.data) ? r.data : [];
                    render(false);
                }).fail(function () { if (asked === $q.val().trim()) { render(false); } });
            }, 250);
        }
        $q.on("input", draw).on("keydown", function (e) {
            if (e.key === "ArrowDown" || e.key === "ArrowUp") {
                e.preventDefault();
                if (!hits.length) { return; }
                at = (at + (e.key === "ArrowDown" ? 1 : hits.length - 1)) % hits.length;
                var $on = $res.children("a").removeClass("on").eq(at).addClass("on"); if ($on[0] && $on[0].scrollIntoView) { $on[0].scrollIntoView({ block: "nearest" }); }
            } else if (e.key === "Enter" && hits.length) {
                e.preventDefault(); window.location.href = hits[at].u;
            } else if (e.key === "Escape") {
                $q.val(""); draw(); $q.blur();
            }
        }).on("blur", function () { setTimeout(function () { $res.prop("hidden", true); }, 150); });
        $(document).on("keydown", function (e) {
            if ((e.ctrlKey || e.metaKey) && (e.key === "k" || e.key === "K")) {
                e.preventDefault();
                if (!pageWrapper.hasClass("menu-collasped-active")) { pageWrapper.addClass("menu-collasped-active"); }
                $q.trigger("focus");
            }
        });
        $(document).on("click", function (e) {
            if (!$(e.target).closest(".wjpm-new").length) { $(".wjpm-new").removeAttr("open"); }
            if (!$(e.target).closest(".wjpl-bell").length) { $(".wjpl-bell").removeAttr("open"); }
        });
        $(document).on("keydown", function (e) {
            if (e.key === "Escape" && $(".wjpl-bell[open]").length) { $(".wjpl-bell").removeAttr("open").children("summary").trigger("focus"); }
        });

        // Full screen: hide the WordPress menu (kept for this browser).
        function focusMode(on) {
            $("body").toggleClass("wjpm-focus", on);
            $(".wjpl-focusbtn").attr("aria-pressed", on ? "true" : "false");
            try { window.localStorage.setItem("wpjobportal_focus", on ? "1" : "0"); } catch (err) {}
        }
        $(document).on("click", ".wjpl-focusbtn", function () { focusMode(!$("body").hasClass("wjpm-focus")); });
        try { if (window.localStorage.getItem("wpjobportal_focus") === "1") { focusMode(true); } } catch (err) {}
    });
';
wp_add_inline_script('wpjobportal-menu-handle', $wpjobportal_menu_js_script);
?>
