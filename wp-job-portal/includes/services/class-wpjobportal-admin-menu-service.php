<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * The plugin's admin menu: 6 groups, 14 sections, as agreed in the admin
 * mockup ("design 4"). One place holds every section, its pages and the
 * third-level links of pages that combine several of today's screens.
 *
 * Every existing page keeps its URL. A menu entry only decides which section
 * and sub-page light up for the page being shown; the screens themselves are
 * unchanged until they are rebuilt (W-19).
 *
 * Add-ons appear only while active. Others can add, move or remove entries
 * through the wpjobportal_admin_menu_items filter.
 */
class WPJOBPORTAL_Admin_Menu_Service {

    const COUNTS_TRANSIENT = 'wpjobportal_admin_menu_counts_v2';

    public static function register() {
        // One WordPress entry: the submenu stays registered (WordPress names each
        // page's hook after it, and refuses the page without it); it is only hidden.
        add_action('admin_head', array(__CLASS__, 'single_menu_entry'));
        add_action('admin_footer', array(__CLASS__, 'keep_flyout_without_menu'));
        add_filter('parent_file', array(__CLASS__, 'highlight_menu_entry'));
        add_action('adminmenu', array(__CLASS__, 'open_menu_on_hidden_pages'));
        // After every add-on has added its pages: seven shortcuts with counts.
        add_action('admin_menu', array(__CLASS__, 'shape_wp_flyout'), 999);
        add_filter('submenu_file', array(__CLASS__, 'flyout_current'), 10, 2);
        foreach (array('job', 'company', 'resume') as $wpjobportal_entity) {
            add_action('wpjobportal_after_store_' . $wpjobportal_entity . '_hook', array(__CLASS__, 'flush_counts'));
            add_action('wpjobportal_after_delete_' . $wpjobportal_entity . '_hook', array(__CLASS__, 'flush_counts'));
        }
        // Any admin action (approve, reject, close, delete...) can change a count, and
        // several run without a save hook; clear the cache before the action runs.
        add_action('init', array(__CLASS__, 'flush_counts_on_action'), 1);
        add_action('wpjobportal_application_submitted', array(__CLASS__, 'flush_counts'));
        add_action('wpjobportal_visitor_application_submitted', array(__CLASS__, 'flush_counts'));
        add_action('wpjobportal_quick_apply_submitted', array(__CLASS__, 'flush_counts'));
        add_action('wp_ajax_wpjobportal_admin_search', array(__CLASS__, 'admin_search'));
        // Add-ons decide which approval queues exist.
        add_action('activated_plugin', array(__CLASS__, 'flush_counts'));
        add_action('deactivated_plugin', array(__CLASS__, 'flush_counts'));
    }

    /**
     * Records for the menu search (Ctrl K): up to five jobs, companies, resumes
     * and people whose text has every word typed. Administrators only.
     */
    public static function admin_search() {
        check_ajax_referer('wpjobportal_admin_search', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(null, 403);
        }
        $db = wpjobportal::$_db;
        $p = $db->prefix . 'wj_portal_';
        $q = isset($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : '';
        $words = array_slice(preg_split('/\s+/', $q, -1, PREG_SPLIT_NO_EMPTY), 0, 6);
        if (!$words || mb_strlen(implode('', $words)) < 2) {
            wp_send_json_success(array());
        }
        // Every word in any of the columns.
        $match = function ($columns) use ($db, $words) {
            $all = array();
            foreach ($words as $word) {
                $like = '%' . $db->esc_like($word) . '%';
                $any = array();
                foreach ($columns as $column) {
                    $any[] = $db->prepare($column . ' LIKE %s', $like);
                }
                $all[] = '(' . implode(' OR ', $any) . ')';
            }
            return implode(' AND ', $all);
        };
        $out = array();
        $add = function ($group, $rows, $title, $sub, $url) use (&$out) {
            foreach ((array) $rows as $row) {
                $out[] = array('g' => $group, 't' => (string) $title($row), 'p' => (string) $sub($row), 'u' => esc_url_raw($url($row)));
            }
        };
        $add(__('Jobs', 'wp-job-portal'),
            $db->get_results("SELECT job.id, job.title, company.name AS companyname FROM `{$p}jobs` AS job LEFT JOIN `{$p}companies` AS company ON company.id = job.companyid WHERE " . $match(array('job.title', 'company.name')) . ' ORDER BY job.created DESC LIMIT 5'),
            function ($r) { return $r->title; }, function ($r) { return $r->companyname; },
            function ($r) { return admin_url('admin.php?page=wpjobportal_job&wpjobportallt=formjob&wpjobportalid=' . (int) $r->id); });
        $add(__('Companies', 'wp-job-portal'),
            $db->get_results("SELECT id, name, contactemail FROM `{$p}companies` WHERE " . $match(array('name', 'contactemail')) . ' ORDER BY name LIMIT 5'),
            function ($r) { return $r->name; }, function ($r) { return $r->contactemail; },
            function ($r) { return admin_url('admin.php?page=wpjobportal_company&wpjobportallt=formcompany&wpjobportalid=' . (int) $r->id); });
        $add(__('Resumes', 'wp-job-portal'),
            $db->get_results("SELECT id, application_title, first_name, last_name FROM `{$p}resume` WHERE COALESCE(quick_apply, 0) <> 1 AND " . $match(array("CONCAT_WS(' ', first_name, last_name)", 'application_title', 'email_address')) . ' ORDER BY created DESC LIMIT 5'),
            function ($r) { return trim($r->first_name . ' ' . $r->last_name); }, function ($r) { return $r->application_title; },
            function ($r) { return admin_url('admin.php?page=wpjobportal_resume&wpjobportallt=viewresume&wpjobportalid=' . (int) $r->id); });
        $add(__('People', 'wp-job-portal'),
            $db->get_results("SELECT id, first_name, last_name, emailaddress FROM `{$p}users` WHERE " . $match(array("CONCAT_WS(' ', first_name, last_name)", 'emailaddress')) . ' ORDER BY first_name LIMIT 5'),
            function ($r) { return trim($r->first_name . ' ' . $r->last_name); }, function ($r) { return $r->emailaddress; },
            function ($r) { return admin_url('admin.php?page=wpjobportal_user&wpjobportallt=userdetail&id=' . (int) $r->id); });
        wp_send_json_success($out);
    }

    /**
     * The WordPress flyout shows only the five shortcuts marked by
     * shape_wp_flyout(); the plugin's own menu has everything else. The other
     * entries stay in $submenu on purpose (hidden by CSS): removing them makes
     * WordPress refuse every page registered under them.
     */
    public static function single_menu_entry() {
        echo '<style id="wpjobportal-single-menu-entry">#adminmenu #toplevel_page_wpjobportal .wp-submenu li:not(.wjp-wpmenu-keep):not(.wp-submenu-head){display:none!important}</style>';
        // "WP Job Portal" and its count on one line. Only for this menu item, and
        // only while the count has at most two digits (shape_wp_flyout() adds the
        // class); a three-digit count would not fit the column, so it wraps.
        echo '<style id="wpjobportal-menu-one-line">#adminmenu #toplevel_page_wpjobportal.wjp-menu-one-line .wp-menu-name{white-space:nowrap}</style>';
    }

    /** The plugin files whose updates the Licences shortcut counts. */
    private static function product_files() {
        return array(
            'wp-job-portal/wp-job-portal.php',
            'wp-job-portal-pro/wp-job-portal-pro.php',
            'wp-job-portal-recruitsuite/wp-job-portal-recruitsuite.php',
            'wp-job-portal-marketplacesuite/wp-job-portal-marketplacesuite.php',
            'wp-job-portal-matchingsuite/wp-job-portal-matchingsuite.php',
        );
    }

    /**
     * WP Job Portal plugins with a newer version, from WordPress's own cached
     * update data (the number WordPress shows on Plugins), so no server call.
     *
     * @return int
     */
    public static function update_count() {
        if (!current_user_can('update_plugins')) {
            return 0;
        }
        $wpjobportal_updates = get_site_transient('update_plugins');
        if (!is_object($wpjobportal_updates) || empty($wpjobportal_updates->response) || !is_array($wpjobportal_updates->response)) {
            return 0;
        }
        return count(array_intersect(array_keys($wpjobportal_updates->response), self::product_files()));
    }

    /** A WordPress menu count bubble, or nothing for 0. */
    private static function bubble($count, $class, $inner) {
        $count = absint($count);
        if ($count < 1) {
            return '';
        }
        return ' <span class="' . esc_attr($class) . ' count-' . $count . '"><span class="' . esc_attr($inner) . '">' . esc_html(number_format_i18n($count)) . '</span></span>';
    }

    /**
     * The WordPress flyout: Dashboard, Approvals (waiting count), Jobs,
     * Candidates (new applications), Companies, Settings, Licences (updates
     * count); the top item shows the waiting and update counts added up.
     * Approvals, Candidates and Licences are added as plain URLs: Approvals is
     * registered under the hidden parent, Candidates opens the Applications
     * layout and Licences is a Dashboard layout, and a page slug here would make
     * WordPress look for the wrong page hook. Candidates and Companies are used
     * daily; 2.7 listed them here and 2.8 had left them to the plugin's own menu.
     */
    public static function shape_wp_flyout() {
        global $submenu, $menu;
        if (empty($submenu['wpjobportal']) || !is_array($submenu['wpjobportal']) || !current_user_can('wpjobportal')) {
            return;
        }
        $wpjobportal_counts = self::counts();
        $wpjobportal_approvals = isset($wpjobportal_counts['approvals']) ? (int) $wpjobportal_counts['approvals'] : 0;
        $wpjobportal_updates = self::update_count();

        $wpjobportal_keep = array('wpjobportal' => 0, 'wpjobportal_job' => 2, 'wpjobportal_company' => 4, 'wpjobportal_configuration' => 5);
        $wpjobportal_ordered = array();
        $wpjobportal_rest = array();
        foreach ($submenu['wpjobportal'] as $wpjobportal_item) {
            $wpjobportal_slug = isset($wpjobportal_item[2]) ? $wpjobportal_item[2] : '';
            if (isset($wpjobportal_keep[$wpjobportal_slug]) && !isset($wpjobportal_ordered[$wpjobportal_keep[$wpjobportal_slug]])) {
                $wpjobportal_item[4] = trim((isset($wpjobportal_item[4]) ? $wpjobportal_item[4] : '') . ' wjp-wpmenu-keep');
                if ($wpjobportal_slug === 'wpjobportal_configuration') {
                    $wpjobportal_item[0] = esc_html__('Settings', 'wp-job-portal');
                }
                $wpjobportal_ordered[$wpjobportal_keep[$wpjobportal_slug]] = $wpjobportal_item;
            } else {
                $wpjobportal_rest[] = $wpjobportal_item;
            }
        }
        $wpjobportal_ordered[1] = array(esc_html__('Approvals', 'wp-job-portal') . self::bubble($wpjobportal_approvals, 'awaiting-mod', 'pending-count'), 'wpjobportal', 'admin.php?page=wpjobportal_approvals', esc_html__('Approvals', 'wp-job-portal'), 'wjp-wpmenu-keep');
        $wpjobportal_new_applications = isset($wpjobportal_counts['new_applications']) ? (int) $wpjobportal_counts['new_applications'] : 0;
        $wpjobportal_ordered[3] = array(esc_html__('Candidates', 'wp-job-portal') . self::bubble($wpjobportal_new_applications, 'update-plugins', 'plugin-count'), 'wpjobportal', 'admin.php?page=wpjobportal_jobapply&wpjobportallt=applications', esc_html__('Candidates', 'wp-job-portal'), 'wjp-wpmenu-keep');
        $wpjobportal_ordered[6] = array(esc_html__('Licences', 'wp-job-portal') . self::bubble($wpjobportal_updates, 'update-plugins', 'plugin-count'), 'wpjobportal', 'admin.php?page=wpjobportal&wpjobportallt=licences', esc_html__('Licences', 'wp-job-portal'), 'wjp-wpmenu-keep');
        ksort($wpjobportal_ordered);
        $submenu['wpjobportal'] = array_merge(array_values($wpjobportal_ordered), $wpjobportal_rest);

        $wpjobportal_total = $wpjobportal_approvals + $wpjobportal_updates;
        if ($wpjobportal_total > 0 && is_array($menu)) {
            foreach ($menu as $wpjobportal_key => $wpjobportal_entry) {
                if (isset($wpjobportal_entry[2]) && $wpjobportal_entry[2] === 'wpjobportal') {
                    $menu[$wpjobportal_key][0] .= self::bubble($wpjobportal_total, 'awaiting-mod', 'pending-count');
                    if ($wpjobportal_total < 100) {
                        $menu[$wpjobportal_key][4] = trim((isset($menu[$wpjobportal_key][4]) ? $menu[$wpjobportal_key][4] : '') . ' wjp-menu-one-line');
                    }
                    break;
                }
            }
        }
    }

    /**
     * Mark the flyout shortcut that holds the screen being shown, from the plugin's
     * own menu, so both menus light the same entry: a resume or cover letter is
     * Candidates, a department is Companies, an approval queue is Approvals, a
     * category is Jobs. Screens of the other sections have no shortcut here.
     */
    public static function flyout_current($submenu_file, $parent_file = '') {
        $wpjobportal_page = sanitize_key((string) WPJOBPORTALrequest::getVar('page', 'get', ''));
        if (strpos($wpjobportal_page, 'wpjobportal') !== 0) {
            return $submenu_file;
        }
        $wpjobportal_shortcuts = array(
            'dashboard' => 'wpjobportal',
            'approvals' => 'admin.php?page=wpjobportal_approvals',
            'jobs' => 'wpjobportal_job',
            'candidates' => 'admin.php?page=wpjobportal_jobapply&wpjobportallt=applications',
            'companies' => 'wpjobportal_company',
            'settings' => 'wpjobportal_configuration',
            'licences' => 'admin.php?page=wpjobportal&wpjobportallt=licences',
        );
        $wpjobportal_current = self::current(self::sections());
        if (isset($wpjobportal_shortcuts[$wpjobportal_current['section']])) {
            return $wpjobportal_shortcuts[$wpjobportal_current['section']];
        }
        // A screen of another section (Stats, Shortcodes...) shares the Dashboard's
        // page slug; WordPress would light Dashboard, so it lights nothing.
        return $wpjobportal_current['section'] !== '' ? '' : $submenu_file;
    }

    /**
     * A plugin screen that draws no plugin menu (today only the full-screen
     * setup wizard) keeps the WordPress flyout, so it is never a dead end.
     */
    public static function keep_flyout_without_menu() {
        $wpjobportal_page = sanitize_key((string) WPJOBPORTALrequest::getVar('page', 'get', ''));
        if ($wpjobportal_page !== 'wpjobportal' && strpos($wpjobportal_page, 'wpjobportal_') !== 0) {
            return;
        }
        // Checked once the page has loaded: some suite screens build their shell in script.
        wp_print_inline_script_tag('window.addEventListener("load",function(){if(!document.getElementById("wpjobportaladmin-leftmenu")){var s=document.getElementById("wpjobportal-single-menu-entry");if(s){s.parentNode.removeChild(s);}}});');
    }

    /**
     * The plugin's other screens (Applications, Approvals, Categories, Departments
     * and some fifty more) are registered under a hidden parent. WordPress works
     * out a page's menu parent again after the parent_file filter, finds that
     * hidden parent, and so drew "WP Job Portal" closed on them: its shortcuts,
     * the current one marked, sat in a hover flyout. Right after the menu is
     * printed, before WordPress's own menu script runs, it is opened as on the
     * plugin's other pages. Registering those screens under the visible parent
     * instead would rename their page hooks, and WordPress would refuse them.
     */
    public static function open_menu_on_hidden_pages() {
        global $parent_file;
        $wpjobportal_page = sanitize_key((string) WPJOBPORTALrequest::getVar('page', 'get', ''));
        if ($parent_file === 'wpjobportal' || ($wpjobportal_page !== 'wpjobportal' && strpos($wpjobportal_page, 'wpjobportal_') !== 0)) {
            return;
        }
        wp_print_inline_script_tag('(function(){var li=document.getElementById("toplevel_page_wpjobportal");if(!li){return;}'
            . 'var a=li.querySelector("a.menu-top");[li,a].forEach(function(e){if(e){e.classList.remove("wp-not-current-submenu");e.classList.add("wp-has-current-submenu","wp-menu-open");}});})();');
    }

    /** Keep "WP Job Portal" highlighted in the WordPress menu on every plugin page. */
    public static function highlight_menu_entry($parent_file) {
        $wpjobportal_page = sanitize_key((string) WPJOBPORTALrequest::getVar('page', 'get', ''));
        if ($wpjobportal_page === 'wpjobportal' || strpos($wpjobportal_page, 'wpjobportal_') === 0) {
            return 'wpjobportal';
        }
        return $parent_file;
    }

    /**
     * The menu, filtered to what is active on this site.
     *
     * Each sub-page: label, url (the part after admin.php?page=), match rules
     * and optional third-level links. A match rule names a page and, when it
     * matters, the layouts (lt), the layouts it must not be (not_lt) and other
     * query values. The most specific matching rule wins.
     *
     * @return array
     */
    public static function sections() {
        $wpjobportal_has = function ($addon) {
            return in_array($addon, (array) wpjobportal::$_active_addons, true);
        };
        $wpjobportal_feature = function ($feature) {
            return !class_exists('WPJOBPORTAL_Mode_Policy_Service') || WPJOBPORTAL_Mode_Policy_Service::is_feature_available($feature);
        };
        $wpjobportal_queues = array('jobqueue', 'companiesqueue', 'resumequeue', 'departmentqueue', 'coverletterqueue', 'foldersqueue', 'messagesqueue');

        $wpjobportal_sections = array(
            array('id' => 'dashboard', 'group' => 'overview', 'label' => __('Dashboard', 'wp-job-portal'), 'icon' => 'home', 'subs' => array(
                array('label' => __('Overview', 'wp-job-portal'), 'url' => 'wpjobportal',
                    'match' => array(array('page' => 'wpjobportal', 'lt' => array('', 'controlpanel')))),
                array('label' => __('Activity log', 'wp-job-portal'), 'url' => 'wpjobportal_activitylog',
                    'match' => array(array('page' => 'wpjobportal_activitylog'))),
            )),
            // One page: Everything waiting, with each queue as a tab (the queue screens keep their URLs).
            array('id' => 'approvals', 'group' => 'overview', 'label' => __('Approvals', 'wp-job-portal'), 'icon' => 'check', 'count' => 'approvals',
                'url' => 'wpjobportal_approvals', 'subs' => array(),
                'match' => array(
                    array('page' => 'wpjobportal_approvals'),
                    array('page' => 'wpjobportal_job', 'lt' => array('jobqueue')),
                    array('page' => 'wpjobportal_company', 'lt' => array('companiesqueue')),
                    array('page' => 'wpjobportal_resume', 'lt' => array('resumequeue')),
                    array('page' => 'wpjobportal_departments', 'lt' => array('departmentqueue')),
                    array('page' => 'wpjobportal_coverletter', 'lt' => array('coverletterqueue')),
                    array('page' => 'wpjobportal_folder', 'lt' => array('foldersqueue')),
                    array('page' => 'wpjobportal_message', 'lt' => array('messagesqueue')),
                )),
            // Reports are a section of their own, as in 2.7; inside Dashboard owners did not find them.
            array('id' => 'reports', 'group' => 'overview', 'label' => __('Reports', 'wp-job-portal'), 'icon' => 'chart', 'subs' => array(
                array('label' => __('Overall', 'wp-job-portal'), 'url' => 'wpjobportal_report&wpjobportallt=overallreports', 'match' => array(array('page' => 'wpjobportal_report'))),
                array('label' => __('Employer', 'wp-job-portal'), 'url' => 'wpjobportal_reports&wpjobportallt=employerreports', 'match' => array(array('page' => 'wpjobportal_reports', 'lt' => array('employerreports'))), 'show' => $wpjobportal_has('reports')),
                array('label' => __('Job seeker', 'wp-job-portal'), 'url' => 'wpjobportal_reports&wpjobportallt=jobseekerreports', 'match' => array(array('page' => 'wpjobportal_reports', 'lt' => array('jobseekerreports'))), 'show' => $wpjobportal_has('reports')),
                array('label' => __('Stats', 'wp-job-portal'), 'url' => 'wpjobportal&wpjobportallt=wpjobportalstats', 'match' => array(array('page' => 'wpjobportal', 'lt' => array('wpjobportalstats')))),
            )),
            // Licences and add-ons are a section of their own, as in 2.7, not a page
            // inside Dashboard where owners did not find them. The count is the
            // product updates WordPress knows about.
            array('id' => 'licences', 'group' => 'overview', 'label' => __('Licences', 'wp-job-portal'), 'icon' => 'key', 'count' => 'updates', 'subs' => array(
                array('label' => __('Licences and add-ons', 'wp-job-portal'), 'url' => 'wpjobportal&wpjobportallt=licences',
                    'match' => array(array('page' => 'wpjobportal', 'lt' => array('licences')))),
                array('label' => __('WordPress updates', 'wp-job-portal'), 'href' => admin_url('update-core.php'), 'match' => array()),
            )),

            array('id' => 'jobs', 'group' => 'hiring', 'label' => __('Jobs', 'wp-job-portal'), 'icon' => 'job', 'count' => 'jobs', 'subs' => array(
                array('label' => __('All jobs', 'wp-job-portal'), 'url' => 'wpjobportal_job',
                    'match' => array(array('page' => 'wpjobportal_job', 'not_lt' => $wpjobportal_queues))),
                array('label' => __('Categories', 'wp-job-portal'), 'url' => 'wpjobportal_category',
                    'match' => array(array('page' => 'wpjobportal_category'), array('page' => 'wpjobportal_jobtype'), array('page' => 'wpjobportal_tag')),
                    'third' => array(
                        array('label' => __('Categories', 'wp-job-portal'), 'url' => 'wpjobportal_category', 'match' => array(array('page' => 'wpjobportal_category'))),
                        array('label' => __('Job types', 'wp-job-portal'), 'url' => 'wpjobportal_jobtype', 'match' => array(array('page' => 'wpjobportal_jobtype'))),
                        array('label' => __('Tags', 'wp-job-portal'), 'url' => 'wpjobportal_tag', 'match' => array(array('page' => 'wpjobportal_tag')), 'show' => $wpjobportal_has('tag')),
                    )),
                array('label' => __('Job alerts', 'wp-job-portal'), 'url' => 'wpjobportal_jobalert',
                    'match' => array(array('page' => 'wpjobportal_jobalert')),
                    'show' => $wpjobportal_has('jobalert') && (!class_exists('WPJOBPORTAL_Feature_Tiers') || WPJOBPORTAL_Feature_Tiers::job_alert_admin_tools())),
            )),
            array('id' => 'candidates', 'group' => 'hiring', 'label' => __('Candidates', 'wp-job-portal'), 'icon' => 'user', 'count' => 'resumes', 'fresh' => 'new_applications', 'subs' => array(
                // Every application across jobs (W-19); one job's applicants screen is part of it.
                array('label' => __('Applications', 'wp-job-portal'), 'url' => 'wpjobportal_jobapply&wpjobportallt=applications',
                    'match' => array(array('page' => 'wpjobportal_jobapply'))),
                array('label' => __('Resumes', 'wp-job-portal'), 'url' => 'wpjobportal_resume',
                    'match' => array(array('page' => 'wpjobportal_resume', 'not_lt' => $wpjobportal_queues))),
                array('label' => __('Cover letters', 'wp-job-portal'), 'url' => 'wpjobportal_coverletter',
                    'match' => array(array('page' => 'wpjobportal_coverletter', 'not_lt' => $wpjobportal_queues)), 'show' => $wpjobportal_has('coverletter')),
                array('label' => __('Folders', 'wp-job-portal'), 'url' => 'wpjobportal_folder',
                    'match' => array(array('page' => 'wpjobportal_folder', 'not_lt' => $wpjobportal_queues)), 'show' => $wpjobportal_has('folder'), 'addon' => true),
                array('label' => __('Messages', 'wp-job-portal'), 'url' => 'wpjobportal_message',
                    'match' => array(array('page' => 'wpjobportal_message', 'not_lt' => $wpjobportal_queues)), 'show' => $wpjobportal_has('message'), 'addon' => true),
            )),
            array('id' => 'companies', 'group' => 'hiring', 'label' => __('Companies', 'wp-job-portal'), 'icon' => 'co', 'count' => 'companies', 'subs' => array(
                array('label' => __('All companies', 'wp-job-portal'), 'url' => 'wpjobportal_company',
                    'match' => array(array('page' => 'wpjobportal_company', 'not_lt' => $wpjobportal_queues))),
                array('label' => __('Departments', 'wp-job-portal'), 'url' => 'wpjobportal_departments',
                    'match' => array(array('page' => 'wpjobportal_departments', 'not_lt' => $wpjobportal_queues)), 'show' => $wpjobportal_has('departments')),
            )),

            array('id' => 'recruiting', 'group' => 'suites', 'label' => __('Recruiting', 'wp-job-portal'), 'icon' => 'flow', 'tag' => __('Recruit', 'wp-job-portal'),
                'show' => $wpjobportal_has('recruitsuite'), 'subs' => array(
                array('label' => __('Pipeline', 'wp-job-portal'), 'url' => 'wpjobportal_recruitpipeline', 'match' => array(array('page' => 'wpjobportal_recruitpipeline'))),
                array('label' => __('Candidate workspace', 'wp-job-portal'), 'url' => 'wpjobportal_recruitworkspace', 'match' => array(array('page' => 'wpjobportal_recruitworkspace'))),
                array('label' => __('Team', 'wp-job-portal'), 'url' => 'wpjobportal_recruitteam', 'match' => array(array('page' => 'wpjobportal_recruitteam'))),
                array('label' => __('Insights', 'wp-job-portal'), 'url' => 'wpjobportal_recruitinsights', 'match' => array(array('page' => 'wpjobportal_recruitinsights'))),
                array('label' => __('Automation', 'wp-job-portal'), 'url' => 'wpjobportal_recruitautomation', 'match' => array(array('page' => 'wpjobportal_recruitautomation'))),
            )),
            array('id' => 'payments', 'group' => 'suites', 'label' => __('Payments', 'wp-job-portal'), 'icon' => 'tag', 'tag' => __('Market', 'wp-job-portal'),
                'show' => $wpjobportal_has('marketplacesuite') && class_exists('wpjobportalmarketplacesuite'),
                'subs' => array(
                array('label' => __('Overview', 'wp-job-portal'), 'url' => 'wpjobportal_marketplacesuite', 'match' => array(array('page' => 'wpjobportal_marketplacesuite'))),
                array('label' => __('Trust & safety', 'wp-job-portal'), 'url' => 'wpjobportal_marketplace_trust', 'match' => array(array('page' => 'wpjobportal_marketplace_trust'))),
                array('label' => __('Sales reports', 'wp-job-portal'), 'url' => 'wpjobportal_marketplace_commerce', 'match' => array(array('page' => 'wpjobportal_marketplace_commerce'))),
                array('label' => __('Plans & credits', 'wp-job-portal'), 'url' => 'wpjobportal_package',
                    'match' => array(array('page' => 'wpjobportal_package'), array('page' => 'wpjobportal_purchasehistory'), array('page' => 'wpjobportal_invoice'), array('page' => 'wpjobportal_paymenthistory'), array('page' => 'wpjobportal_paymentmethodconfiguration'), array('page' => 'wpjobportal_paymenthistorymethodconfiguration')),
                    'show' => $wpjobportal_has('credits') && $wpjobportal_feature('credits'), 'addon' => true,
                    'third' => array(
                        array('label' => __('Packages', 'wp-job-portal'), 'url' => 'wpjobportal_package', 'match' => array(array('page' => 'wpjobportal_package'))),
                        array('label' => __('User packages', 'wp-job-portal'), 'url' => 'wpjobportal_purchasehistory', 'match' => array(array('page' => 'wpjobportal_purchasehistory'), array('page' => 'wpjobportal_paymenthistory'))),
                        array('label' => __('Invoices', 'wp-job-portal'), 'url' => 'wpjobportal_invoice', 'match' => array(array('page' => 'wpjobportal_invoice'))),
                        array('label' => __('Payment methods', 'wp-job-portal'), 'url' => 'wpjobportal_paymentmethodconfiguration', 'match' => array(array('page' => 'wpjobportal_paymentmethodconfiguration'), array('page' => 'wpjobportal_paymenthistorymethodconfiguration'))),
                    )),
            )),

            array('id' => 'pages', 'group' => 'website', 'label' => __('Pages & SEO', 'wp-job-portal'), 'icon' => 'globe', 'subs' => array(
                array('label' => __('SEO', 'wp-job-portal'), 'url' => 'wpjobportal&wpjobportallt=pageseo', 'match' => array(array('page' => 'wpjobportal', 'lt' => array('pageseo')))),
                array('label' => __('Permalinks', 'wp-job-portal'), 'url' => 'wpjobportal_slug&wpjobportallt=slug', 'match' => array(array('page' => 'wpjobportal_slug'))),
            )),
            // Shortcodes are a section of their own, as in 2.7: needed whenever a page is set up.
            array('id' => 'shortcodes', 'group' => 'website', 'label' => __('Shortcodes', 'wp-job-portal'), 'icon' => 'code',
                'url' => 'wpjobportal&wpjobportallt=shortcodes', 'match' => array(array('page' => 'wpjobportal', 'lt' => array('shortcodes'))), 'subs' => array()),
            array('id' => 'design', 'group' => 'website', 'label' => __('Appearance & language', 'wp-job-portal'), 'icon' => 'brush', 'subs' => array(
                array('label' => __('Design', 'wp-job-portal'), 'url' => 'wpjobportal_theme', 'match' => array(array('page' => 'wpjobportal_theme'), array('page' => 'wpjobportal', 'lt' => array('themes')), array('page' => 'wpjobportal_pdf'))),
                array('label' => __('Translations', 'wp-job-portal'), 'url' => 'wpjobportal&wpjobportallt=translations', 'match' => array(array('page' => 'wpjobportal', 'lt' => array('translations')))),
            )),

            array('id' => 'settings', 'group' => 'setup', 'label' => __('Settings', 'wp-job-portal'), 'icon' => 'gear', 'subs' => array(
                array('label' => __('Site mode', 'wp-job-portal'), 'url' => 'wpjobportal_postinstallation&wpjobportallt=quickstart',
                    'match' => array(array('page' => 'wpjobportal_postinstallation')),
                    'third' => array(
                        array('label' => __('Choose mode & setup', 'wp-job-portal'), 'url' => 'wpjobportal_postinstallation&wpjobportallt=quickstart', 'match' => array(array('page' => 'wpjobportal_postinstallation', 'not_lt' => array('modeguide')))),
                        array('label' => __('How modes work', 'wp-job-portal'), 'url' => 'wpjobportal_postinstallation&wpjobportallt=modeguide', 'match' => array(array('page' => 'wpjobportal_postinstallation', 'lt' => array('modeguide')))),
                    )),
                array('label' => __('General', 'wp-job-portal'), 'url' => 'wpjobportal_configuration&wpjobportallt=configurations',
                    'match' => array(array('page' => 'wpjobportal_configuration', 'not' => array('wpjpconfigid' => 'job_apply')))),
                array('label' => __('Apply form', 'wp-job-portal'), 'url' => 'wpjobportal_configuration&wpjobportallt=configurations&wpjpconfigid=job_apply',
                    'match' => array(array('page' => 'wpjobportal_configuration', 'args' => array('wpjpconfigid' => 'job_apply')))),
                array('label' => __('Forms & fields', 'wp-job-portal'), 'url' => 'wpjobportal_fieldordering&ff=2',
                    'match' => array(array('page' => 'wpjobportal_fieldordering'), array('page' => 'wpjobportal_customfield')),
                    'third' => array(
                        self::fields_link(__('Job', 'wp-job-portal'), 2, false),
                        self::fields_link(__('Job search', 'wp-job-portal'), 2, true),
                        self::fields_link(__('Job alert', 'wp-job-portal'), 6, false, $wpjobportal_has('jobalert')),
                        self::fields_link(__('Resume', 'wp-job-portal'), 3, false),
                        self::fields_link(__('Resume search', 'wp-job-portal'), 3, true, $wpjobportal_has('resumesearch')),
                        self::fields_link(__('Quick apply', 'wp-job-portal'), 5, false),
                        self::fields_link(__('Company', 'wp-job-portal'), 1, false),
                        self::fields_link(__('User', 'wp-job-portal'), 4, false),
                    )),
                array('label' => __('Job attributes', 'wp-job-portal'), 'url' => 'wpjobportal_jobstatus',
                    'match' => array(array('page' => 'wpjobportal_jobstatus'), array('page' => 'wpjobportal_careerlevel'), array('page' => 'wpjobportal_highesteducation'), array('page' => 'wpjobportal_currency'), array('page' => 'wpjobportal_salaryrangetype'), array('page' => 'wpjobportal_salaryrange'), array('page' => 'wpjobportal_experience')),
                    'third' => array(
                        array('label' => __('Job status', 'wp-job-portal'), 'url' => 'wpjobportal_jobstatus', 'match' => array(array('page' => 'wpjobportal_jobstatus'))),
                        array('label' => __('Career levels', 'wp-job-portal'), 'url' => 'wpjobportal_careerlevel', 'match' => array(array('page' => 'wpjobportal_careerlevel'))),
                        array('label' => __('Highest educations', 'wp-job-portal'), 'url' => 'wpjobportal_highesteducation', 'match' => array(array('page' => 'wpjobportal_highesteducation'))),
                        array('label' => __('Currency', 'wp-job-portal'), 'url' => 'wpjobportal_currency', 'match' => array(array('page' => 'wpjobportal_currency'))),
                        array('label' => __('Salary range types', 'wp-job-portal'), 'url' => 'wpjobportal_salaryrangetype', 'match' => array(array('page' => 'wpjobportal_salaryrangetype'), array('page' => 'wpjobportal_salaryrange'))),
                    )),
                array('label' => __('Locations', 'wp-job-portal'), 'url' => 'wpjobportal_country',
                    'match' => array(array('page' => 'wpjobportal_country'), array('page' => 'wpjobportal_state'), array('page' => 'wpjobportal_city'), array('page' => 'wpjobportal_addressdata')),
                    'third' => array(
                        array('label' => __('Countries & cities', 'wp-job-portal'), 'url' => 'wpjobportal_country', 'match' => array(array('page' => 'wpjobportal_country'), array('page' => 'wpjobportal_state'), array('page' => 'wpjobportal_city', 'not_lt' => array('loadaddressdata', 'locationnamesettings')))),
                        array('label' => __('Load address data', 'wp-job-portal'), 'url' => 'wpjobportal_city&wpjobportallt=loadaddressdata', 'match' => array(array('page' => 'wpjobportal_city', 'lt' => array('loadaddressdata')), array('page' => 'wpjobportal_addressdata'))),
                        array('label' => __('Location name settings', 'wp-job-portal'), 'url' => 'wpjobportal_city&wpjobportallt=locationnamesettings', 'match' => array(array('page' => 'wpjobportal_city', 'lt' => array('locationnamesettings')))),
                    )),
            )),
            array('id' => 'emails', 'group' => 'setup', 'label' => __('Emails', 'wp-job-portal'), 'icon' => 'mail', 'subs' => array(
                array('label' => __('Templates', 'wp-job-portal'), 'url' => 'wpjobportal_emailtemplate', 'match' => array(array('page' => 'wpjobportal_emailtemplate'))),
                array('label' => __('On/off options', 'wp-job-portal'), 'url' => 'wpjobportal_emailtemplatestatus', 'match' => array(array('page' => 'wpjobportal_emailtemplatestatus'))),
            )),
            array('id' => 'users', 'group' => 'setup', 'label' => __('Users', 'wp-job-portal'), 'icon' => 'users', 'count' => 'users', 'subs' => array(
                array('label' => __('All users', 'wp-job-portal'), 'url' => 'wpjobportal_user', 'match' => array(array('page' => 'wpjobportal_user', 'not_lt' => array('assignrole')))),
                array('label' => __('Roles', 'wp-job-portal'), 'url' => 'wpjobportal_user&wpjobportallt=assignrole', 'match' => array(array('page' => 'wpjobportal_user', 'lt' => array('assignrole')))),
            )),

            array('id' => 'health', 'group' => 'system', 'label' => __('Site health', 'wp-job-portal'), 'icon' => 'pulse', 'subs' => array(
                array('label' => __('System status', 'wp-job-portal'), 'url' => 'wpjobportal_systemstatus', 'match' => array(array('page' => 'wpjobportal_systemstatus'))),
                array('label' => __('Error log', 'wp-job-portal'), 'url' => 'wpjobportal_systemerror', 'match' => array(array('page' => 'wpjobportal_systemerror'))),
                array('label' => __('Scheduled tasks', 'wp-job-portal'), 'url' => 'wpjobportal_cronjob&wpjobportallt=cronjob', 'match' => array(array('page' => 'wpjobportal_cronjob')), 'show' => $wpjobportal_has('cronjob')),
            )),
            array('id' => 'tools', 'group' => 'system', 'label' => __('Tools', 'wp-job-portal'), 'icon' => 'tool', 'subs' => array(
                array('label' => __('Import', 'wp-job-portal'), 'url' => 'wpjobportal_thirdpartyimport', 'match' => array(array('page' => 'wpjobportal_thirdpartyimport'))),
                array('label' => __('AI writer', 'wp-job-portal'), 'url' => 'wpjobportal_zywrap&wpjobportallt=playground',
                    'match' => array(array('page' => 'wpjobportal_zywrap'), array('page' => 'wpjobportal_zywraplogs')),
                    'third' => array(
                        array('label' => __('Playground', 'wp-job-portal'), 'url' => 'wpjobportal_zywrap&wpjobportallt=playground', 'match' => array(array('page' => 'wpjobportal_zywrap', 'lt' => array('playground')))),
                        array('label' => __('Connection', 'wp-job-portal'), 'url' => 'wpjobportal_zywrap&wpjobportallt=zywrap', 'match' => array(array('page' => 'wpjobportal_zywrap', 'not_lt' => array('playground')))),
                        array('label' => __('Logs', 'wp-job-portal'), 'url' => 'wpjobportal_zywraplogs&wpjobportallt=logs', 'match' => array(array('page' => 'wpjobportal_zywraplogs'))),
                    )),
            )),
            array('id' => 'developers', 'group' => 'system', 'label' => __('Hooks & filters', 'wp-job-portal'), 'icon' => 'code',
                'url' => 'wpjobportal&wpjobportallt=actionhooks', 'match' => array(array('page' => 'wpjobportal', 'lt' => array('actionhooks'))), 'subs' => array()),
        );

        $wpjobportal_sections = apply_filters('wpjobportal_admin_menu_items', $wpjobportal_sections);
        return self::visible((array) $wpjobportal_sections);
    }

    /** The groups, in order. */
    public static function groups() {
        return array(
            'overview' => __('Overview', 'wp-job-portal'),
            'hiring' => __('Hiring', 'wp-job-portal'),
            'suites' => __('Suites', 'wp-job-portal'),
            'website' => __('Website', 'wp-job-portal'),
            'setup' => __('Setup', 'wp-job-portal'),
            'system' => __('System', 'wp-job-portal'),
        );
    }

    /**
     * Where the page being shown sits: section id, sub index and third-level
     * index (-1 when none), from the most specific rule that matches.
     *
     * @param array $sections From sections().
     * @return array
     */
    public static function current($sections) {
        $wpjobportal_best = array('section' => '', 'sub' => -1, 'third' => -1, 'score' => -1);
        foreach ($sections as $wpjobportal_section) {
            $wpjobportal_rules = isset($wpjobportal_section['match']) ? $wpjobportal_section['match'] : array();
            $wpjobportal_score = self::score($wpjobportal_rules);
            if ($wpjobportal_score > $wpjobportal_best['score']) {
                $wpjobportal_best = array('section' => $wpjobportal_section['id'], 'sub' => -1, 'third' => -1, 'score' => $wpjobportal_score);
            }
            foreach ($wpjobportal_section['subs'] as $wpjobportal_index => $wpjobportal_sub) {
                $wpjobportal_score = self::score($wpjobportal_sub['match']);
                if ($wpjobportal_score > $wpjobportal_best['score']) {
                    $wpjobportal_best = array('section' => $wpjobportal_section['id'], 'sub' => $wpjobportal_index, 'third' => -1, 'score' => $wpjobportal_score);
                }
            }
        }
        // The third-level link inside the chosen sub-page.
        foreach ($sections as $wpjobportal_section) {
            if ($wpjobportal_section['id'] !== $wpjobportal_best['section'] || $wpjobportal_best['sub'] < 0 || empty($wpjobportal_section['subs'][$wpjobportal_best['sub']]['third'])) {
                continue;
            }
            $wpjobportal_top = -1;
            foreach ($wpjobportal_section['subs'][$wpjobportal_best['sub']]['third'] as $wpjobportal_index => $wpjobportal_third) {
                $wpjobportal_score = self::score($wpjobportal_third['match']);
                if ($wpjobportal_score > $wpjobportal_top) {
                    $wpjobportal_top = $wpjobportal_score;
                    $wpjobportal_best['third'] = $wpjobportal_index;
                }
            }
        }
        return $wpjobportal_best;
    }

    /**
     * Counts for the menu badges, from one query, cached for ten minutes and
     * cleared when a job, company, resume or application is saved.
     *
     * @return array
     */
    public static function counts() {
        $wpjobportal_counts = get_transient(self::COUNTS_TRANSIENT);
        if (is_array($wpjobportal_counts)) {
            return $wpjobportal_counts;
        }
        $wpjobportal_prefix = wpjobportal::$_db->prefix . 'wj_portal_';
        $wpjobportal_waiting = array();
        foreach (self::approval_queues() as $wpjobportal_key => $wpjobportal_queue) {
            $wpjobportal_waiting[$wpjobportal_key] = '(SELECT COUNT(*) FROM `' . $wpjobportal_prefix . $wpjobportal_queue['table'] . '` WHERE ' . $wpjobportal_queue['waiting'] . ')';
        }
        $wpjobportal_row = wpjobportal::$_db->get_row(
            'SELECT (SELECT COUNT(*) FROM `' . $wpjobportal_prefix . 'jobs`) AS jobs,
                (SELECT COUNT(*) FROM `' . $wpjobportal_prefix . 'companies`) AS companies,
                (SELECT COUNT(*) FROM `' . $wpjobportal_prefix . 'resume`) AS resumes,
                (SELECT COUNT(*) FROM `' . $wpjobportal_prefix . 'users`) AS users,
                (SELECT COUNT(*) FROM `' . $wpjobportal_prefix . 'jobapply` WHERE apply_date >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 7 DAY)) AS new_applications'
                . ($wpjobportal_waiting ? ', ' . implode(', ', array_map(function ($key, $sql) { return $sql . ' AS `approvals_' . $key . '`'; }, array_keys($wpjobportal_waiting), $wpjobportal_waiting)) : ''),
            ARRAY_A
        );
        $wpjobportal_counts = array_map('absint', is_array($wpjobportal_row) ? $wpjobportal_row : array());
        $wpjobportal_counts['approvals'] = 0;
        foreach (array_keys($wpjobportal_waiting) as $wpjobportal_key) {
            $wpjobportal_counts['approvals'] += $wpjobportal_counts['approvals_' . $wpjobportal_key];
        }
        set_transient(self::COUNTS_TRANSIENT, $wpjobportal_counts, 10 * MINUTE_IN_SECONDS);
        return $wpjobportal_counts;
    }

    /**
     * The approval queues that are active on this site, in tab order: what
     * "waiting" means for each (the same rule as its queue screen), its table and
     * its screen. Featured requests count while the matching add-on is active.
     *
     * @return array
     */
    public static function approval_queues() {
        $wpjobportal_has = function ($addon) {
            return in_array($addon, (array) wpjobportal::$_active_addons, true);
        };
        $wpjobportal_queues = array(
            'jobs' => array('label' => __('Jobs', 'wp-job-portal'), 'table' => 'jobs', 'url' => 'wpjobportal_job&wpjobportallt=jobqueue',
                'waiting' => 'status = 0' . ($wpjobportal_has('featuredjob') ? ' OR isfeaturedjob = 0' : '')),
            'companies' => array('label' => __('Companies', 'wp-job-portal'), 'table' => 'companies', 'url' => 'wpjobportal_company&wpjobportallt=companiesqueue',
                'waiting' => 'status = 0' . ($wpjobportal_has('featuredcompany') ? ' OR isfeaturedcompany = 0' : '')),
            'resumes' => array('label' => __('Resumes', 'wp-job-portal'), 'table' => 'resume', 'url' => 'wpjobportal_resume&wpjobportallt=resumequeue',
                'waiting' => 'status = 0 AND quick_apply <> 1'),
        );
        // W-18: a single-company site has no use for these queues.
        $wpjobportal_single_company = class_exists('WPJOBPORTAL_Product_Mode_Service') && WPJOBPORTAL_Product_Mode_Service::get_mode() === WPJOBPORTAL_Product_Mode_Service::MODE_CAREERS;
        if ($wpjobportal_single_company) {
            return $wpjobportal_queues;
        }
        if ($wpjobportal_has('departments')) {
            $wpjobportal_queues['departments'] = array('label' => __('Departments', 'wp-job-portal'), 'table' => 'departments', 'url' => 'wpjobportal_departments&wpjobportallt=departmentqueue', 'waiting' => 'status = 0');
        }
        if ($wpjobportal_has('coverletter')) {
            $wpjobportal_queues['coverletters'] = array('label' => __('Cover letters', 'wp-job-portal'), 'table' => 'coverletters', 'url' => 'wpjobportal_coverletter&wpjobportallt=coverletterqueue', 'waiting' => 'status = 0');
        }
        if ($wpjobportal_has('folder')) {
            $wpjobportal_queues['folders'] = array('label' => __('Folders', 'wp-job-portal'), 'table' => 'folders', 'url' => 'wpjobportal_folder&wpjobportallt=foldersqueue', 'waiting' => 'status = 0');
        }
        if ($wpjobportal_has('message')) {
            $wpjobportal_queues['messages'] = array('label' => __('Messages', 'wp-job-portal'), 'table' => 'messages', 'url' => 'wpjobportal_message&wpjobportallt=messagesqueue', 'waiting' => 'status = 0');
        }
        if (class_exists('WPJOBPORTAL_Mode_Policy_Service')) {
            if (!WPJOBPORTAL_Mode_Policy_Service::is_feature_available('job_approval')) {
                unset($wpjobportal_queues['jobs']);
            }
            if (!WPJOBPORTAL_Mode_Policy_Service::is_feature_available('company_approval')) {
                unset($wpjobportal_queues['companies']);
            }
        }
        // A queue whose table is missing (an add-on never installed) cannot be counted.
        // P-7: asked once per table per request; this runs for the menu badge and the page, on every admin screen
        static $wpjobportal_table_exists = array();
        foreach ($wpjobportal_queues as $wpjobportal_key => $wpjobportal_queue) {
            if (!isset($wpjobportal_table_exists[$wpjobportal_queue['table']])) {
                $wpjobportal_table_exists[$wpjobportal_queue['table']] = (bool) wpjobportal::$_db->get_var(wpjobportal::$_db->prepare('SHOW TABLES LIKE %s', wpjobportal::$_db->prefix . 'wj_portal_' . $wpjobportal_queue['table']));
            }
            if (!$wpjobportal_table_exists[$wpjobportal_queue['table']]) {
                unset($wpjobportal_queues[$wpjobportal_key]);
            }
        }
        return $wpjobportal_queues;
    }

    public static function flush_counts_on_action() {
        if (WPJOBPORTALrequest::getVar('action', 'get') === 'wpjobportaltask' || WPJOBPORTALrequest::getVar('form_request', 'post') === 'wpjobportal') {
            self::flush_counts();
        }
    }

    public static function flush_counts() {
        delete_transient(self::COUNTS_TRANSIENT);
        delete_transient(self::COUNTS_TRANSIENT . '_recent');
    }

    /**
     * The five latest applications of the last seven days for the bell, cached
     * and cleared with the counts.
     *
     * @return array Rows with jobid, jobtitle, name and apply_date.
     */
    public static function recent_applications() {
        $wpjobportal_rows = get_transient(self::COUNTS_TRANSIENT . '_recent');
        if (is_array($wpjobportal_rows)) {
            return $wpjobportal_rows;
        }
        $wpjobportal_prefix = wpjobportal::$_db->prefix . 'wj_portal_';
        $wpjobportal_rows = wpjobportal::$_db->get_results(
            'SELECT apply.jobid, job.title AS jobtitle, TRIM(CONCAT_WS(\' \', resume.first_name, resume.last_name)) AS name, apply.apply_date
            FROM `' . $wpjobportal_prefix . 'jobapply` AS apply
            LEFT JOIN `' . $wpjobportal_prefix . 'jobs` AS job ON job.id = apply.jobid
            LEFT JOIN `' . $wpjobportal_prefix . 'resume` AS resume ON resume.id = apply.cvid
            WHERE apply.apply_date >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 7 DAY)
            ORDER BY apply.apply_date DESC, apply.id DESC LIMIT 5',
            ARRAY_A
        );
        $wpjobportal_rows = is_array($wpjobportal_rows) ? $wpjobportal_rows : array();
        set_transient(self::COUNTS_TRANSIENT . '_recent', $wpjobportal_rows, 10 * MINUTE_IN_SECONDS);
        return $wpjobportal_rows;
    }

    /** Inline SVG for a menu icon (the mockup's set). */
    public static function icon($name) {
        $wpjobportal_paths = array(
            'home' => '<path d="M3 11l9-7 9 7v9a1 1 0 0 1-1 1h-5v-6h-6v6H4a1 1 0 0 1-1-1z"/>',
            'check' => '<circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/>',
            'job' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5h6v2"/>',
            'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
            'co' => '<path d="M4 21V5l8-3v19M12 9h8v12M7 8h2M7 12h2M7 16h2M15 13h2M15 17h2"/>',
            'flow' => '<rect x="3" y="4" width="5" height="16" rx="1"/><rect x="10" y="4" width="5" height="10" rx="1"/><rect x="17" y="4" width="4" height="13" rx="1"/>',
            'tag' => '<path d="M3 12V4h8l10 10-8 8z"/><circle cx="7.5" cy="8.5" r="1.5"/>',
            'users' => '<circle cx="9" cy="8" r="3.5"/><path d="M2 20a7 7 0 0 1 14 0M16 4.5a3.5 3.5 0 0 1 0 7M18 20a7 7 0 0 0-2-5"/>',
            'gear' => '<circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M4.2 4.2l2.1 2.1M17.7 17.7l2.1 2.1M2 12h3M19 12h3M4.2 19.8l2.1-2.1M17.7 6.3l2.1-2.1"/>',
            'tool' => '<path d="M14 6a4 4 0 0 0 5 5l-9 9-3-3 9-9z"/>',
            'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
            'globe' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>',
            'brush' => '<path d="M14 4l6 6-8 8H6v-6z"/><path d="M4 20l2-2"/>',
            'code' => '<path d="M8 8l-4 4 4 4M16 8l4 4-4 4M14 5l-4 14"/>',
            'chart' => '<path d="M4 20V11M10 20V5M16 20v-7M2 20h20"/>',
            'key' => '<circle cx="8" cy="15" r="4"/><path d="M11 12l9-9M16 7l3 3M14 9l2 2"/>',
            'help' => '<circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 1 1 3.5 2.3c-.6.3-1 .9-1 1.7M12 17h.01"/>',
            'pulse' => '<path d="M3 12h4l3-8 4 16 3-8h4"/>',
            'chev' => '<path d="M9 6l6 6-6 6"/>',
            'collapse' => '<path d="M15 6l-6 6 6 6"/>',
        );
        $wpjobportal_path = isset($wpjobportal_paths[$name]) ? $wpjobportal_paths[$name] : $wpjobportal_paths['home'];
        return '<svg class="wjpm-i" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $wpjobportal_path . '</svg>';
    }

    /** admin.php link for a menu url. */
    public static function link($item) {
        if (!empty($item['href'])) {
            return $item['href'];
        }
        return admin_url('admin.php?page=' . $item['url']);
    }

    private static function fields_link($label, $ff, $search, $show = true) {
        $wpjobportal_rule = array('page' => 'wpjobportal_fieldordering', 'args' => array('ff' => (string) $ff));
        $wpjobportal_custom = array('page' => 'wpjobportal_customfield', 'args' => array('ff' => (string) $ff));
        if ($search) {
            $wpjobportal_rule['lt'] = $wpjobportal_custom['lt'] = array('searchfields');
        } else {
            $wpjobportal_rule['not_lt'] = $wpjobportal_custom['not_lt'] = array('searchfields');
        }
        return array(
            'label' => $label,
            'url' => 'wpjobportal_fieldordering' . ($search ? '&wpjobportallt=searchfields' : '') . '&ff=' . $ff,
            'match' => array($wpjobportal_rule, $wpjobportal_custom),
            'show' => $show,
        );
    }

    /**
     * Whether the admin page behind a menu url exists. A suite that is active
     * but stopped by its licence gate (no key, expired, another site) registers
     * no pages, and a link to one answers "not allowed". Before admin_menu has
     * run nothing is known, so everything counts as there.
     */
    private static function page_exists($item) {
        global $_registered_pages;
        if (!empty($item['href']) || empty($item['url']) || !did_action('admin_menu') || doing_action('admin_menu') || empty($_registered_pages)) {
            return true;
        }
        $wpjobportal_slug = strtok((string) $item['url'], '&');
        if (strpos($wpjobportal_slug, 'wpjobportal') !== 0) {
            return true;
        }
        foreach (array_keys($_registered_pages) as $wpjobportal_hook) {
            $wpjobportal_at = strpos($wpjobportal_hook, '_page_');
            if ($wpjobportal_at !== false && substr($wpjobportal_hook, $wpjobportal_at + 6) === $wpjobportal_slug) {
                return true;
            }
        }
        return false;
    }

    /** Drop entries switched off by their 'show' flag or whose page is not registered, and sections left empty. */
    private static function visible($sections) {
        $wpjobportal_out = array();
        foreach ($sections as $wpjobportal_section) {
            if (empty($wpjobportal_section['id']) || (isset($wpjobportal_section['show']) && !$wpjobportal_section['show'])) {
                continue;
            }
            if (!empty($wpjobportal_section['url']) && !self::page_exists($wpjobportal_section)) {
                unset($wpjobportal_section['url']);
            }
            $wpjobportal_subs = array();
            foreach ((array) $wpjobportal_section['subs'] as $wpjobportal_sub) {
                if (isset($wpjobportal_sub['show']) && !$wpjobportal_sub['show']) {
                    continue;
                }
                if (!empty($wpjobportal_sub['third'])) {
                    $wpjobportal_sub['third'] = array_values(array_filter($wpjobportal_sub['third'], function ($third) {
                        return (!isset($third['show']) || $third['show']) && self::page_exists($third);
                    }));
                }
                if (!self::page_exists($wpjobportal_sub) && empty($wpjobportal_sub['third'])) {
                    continue;
                }
                $wpjobportal_sub['match'] = isset($wpjobportal_sub['match']) ? $wpjobportal_sub['match'] : array();
                $wpjobportal_subs[] = $wpjobportal_sub;
            }
            if (empty($wpjobportal_subs) && empty($wpjobportal_section['url'])) {
                continue;
            }
            $wpjobportal_section['subs'] = $wpjobportal_subs;
            $wpjobportal_out[] = $wpjobportal_section;
        }
        return $wpjobportal_out;
    }

    /**
     * How specifically a list of rules matches this request: -1 for no match,
     * otherwise 1 for the page, plus 2 when a layout was named and 1 for each
     * other query value checked.
     */
    private static function score($rules) {
        $wpjobportal_request = array(
            'page' => sanitize_key((string) WPJOBPORTALrequest::getVar('page', 'get', '')),
            'lt' => sanitize_key((string) WPJOBPORTALrequest::getVar('wpjobportallt', 'get', '')),
        );
        $wpjobportal_best = -1;
        foreach ((array) $rules as $wpjobportal_rule) {
            if (empty($wpjobportal_rule['page']) || $wpjobportal_rule['page'] !== $wpjobportal_request['page']) {
                continue;
            }
            $wpjobportal_score = 1;
            if (isset($wpjobportal_rule['lt'])) {
                if (!in_array($wpjobportal_request['lt'], $wpjobportal_rule['lt'], true)) {
                    continue;
                }
                $wpjobportal_score += 2;
            }
            if (isset($wpjobportal_rule['not_lt']) && in_array($wpjobportal_request['lt'], $wpjobportal_rule['not_lt'], true)) {
                continue;
            }
            $wpjobportal_ok = true;
            foreach (isset($wpjobportal_rule['args']) ? $wpjobportal_rule['args'] : array() as $wpjobportal_key => $wpjobportal_value) {
                if ((string) WPJOBPORTALrequest::getVar($wpjobportal_key, 'get', '') !== (string) $wpjobportal_value) {
                    $wpjobportal_ok = false;
                    break;
                }
                $wpjobportal_score++;
            }
            foreach (isset($wpjobportal_rule['not']) ? $wpjobportal_rule['not'] : array() as $wpjobportal_key => $wpjobportal_value) {
                if ((string) WPJOBPORTALrequest::getVar($wpjobportal_key, 'get', '') === (string) $wpjobportal_value) {
                    $wpjobportal_ok = false;
                    break;
                }
            }
            if ($wpjobportal_ok && $wpjobportal_score > $wpjobportal_best) {
                $wpjobportal_best = $wpjobportal_score;
            }
        }
        return $wpjobportal_best;
    }
}
