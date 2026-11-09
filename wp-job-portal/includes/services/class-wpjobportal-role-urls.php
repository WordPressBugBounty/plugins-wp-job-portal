<?php
if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * W-5: addresses that match who they are for. Public job and company pages
 * live under one neutral Jobs page instead of the job seeker's dashboard, each
 * dashboard lives on its own page, and the old addresses answer with a 301.
 */
class WPJOBPORTAL_Role_Urls {

    const JOBS_SHORTCODE = 'wpjobportal_job';

    public static function register() {
        add_action('template_redirect', array(__CLASS__, 'redirect_old_address'), 1);
        add_action('template_redirect', array(__CLASS__, 'careers_employer_area'), 2);
        add_filter('get_pages', array(__CLASS__, 'careers_hide_employer_page'));
        add_filter('wp_nav_menu_objects', array(__CLASS__, 'careers_hide_employer_menu_item'));
    }

    /** Layouts anyone may read, whatever their role. */
    private static function public_layouts() {
        return array('jobs', 'newestjobs', 'viewjob', 'viewcompany', 'companies', 'jobsearch', 'jobsbycategories', 'jobsbytypes', 'jobsbycities');
    }

    /** A published page carrying the shortcode, or 0 (a trashed page means the owner did not want it). */
    private static function live_page($shortcode) {
        static $pages = array();
        if (!isset($pages[$shortcode])) {
            $id = WPJOBPORTAL_Page_Provisioning_Service::page_carrying($shortcode);
            $pages[$shortcode] = ($id > 0 && get_post_status($id) === 'publish') ? $id : 0;
        }
        return $pages[$shortcode];
    }

    public static function jobs_page() {
        return self::live_page(self::JOBS_SHORTCODE);
    }

    /** The page a layout belongs on, or 0 when it follows the current page. */
    public static function page_for($module, $layout) {
        $page = 0;
        if ($layout === 'controlpanel' && ($module === 'employer' || $module === 'jobseeker')) {
            $page = self::live_page('wpjobportal_' . $module . '_controlpanel');
        } elseif (in_array($layout, self::public_layouts(), true)) {
            $page = self::jobs_page();
        }
        /**
         * Filter the page a front-end layout is addressed under; 0 keeps the current page.
         *
         * @param int    $page   The page decided so far.
         * @param string $module The module.
         * @param string $layout The layout.
         */
        return (int) apply_filters('wpjobportal_role_url_page', $page, $module, $layout);
    }

    /** The bare page address when the page itself shows this layout, else ''. */
    public static function bare_url($args, $page) {
        global $wp_rewrite;
        if (!$page || !in_array($args['wpjobportallt'], array('controlpanel', 'jobs'), true) || !$wp_rewrite->using_permalinks()) {
            return '';
        }
        if (array_diff(array_keys($args), array('wpjobportalme', 'wpjobportallt', 'wpjobportalpageid'))) {
            return '';
        }
        return (string) get_the_permalink($page);
    }

    /** A personal layout reached from the Jobs page goes back under the visitor's own dashboard. */
    public static function leave_jobs_page($page, $args) {
        if (empty($args['wpjobportallt']) || (int) $page !== self::jobs_page() || in_array($args['wpjobportallt'], self::public_layouts(), true)) {
            return $page;
        }
        return wpjobportal::wpjobportal_getDefaultPageid();
    }

    /** Old address under a dashboard page: 301 to the same path under the right page. */
    public static function redirect_old_address() {
        if (is_admin() || !is_page() || !in_array(isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '', array('GET', 'HEAD'), true) || !get_query_var('wpjobportallayout')) {
            return;
        }
        $args = isset(wpjobportal::$_data['sanitized_args']) ? wpjobportal::$_data['sanitized_args'] : array();
        if (empty($args['wpjobportallt']) || empty($args['wpjobportalme'])) {
            return;
        }
        $current = (int) get_queried_object_id();
        $target = self::page_for($args['wpjobportalme'], $args['wpjobportallt']);
        $on_dashboard = in_array($current, array(self::live_page('wpjobportal_employer_controlpanel'), self::live_page('wpjobportal_jobseeker_controlpanel')), true);
        if (!$target || $target === $current || (!$on_dashboard && $args['wpjobportallt'] !== 'controlpanel')) {
            return;
        }
        $to = untrailingslashit(get_the_permalink($target));
        $uri = isset($_SERVER['REQUEST_URI']) ? esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])) : '';
        $uri = remove_query_arg('wpjobportalpageid', $uri);
        $from = untrailingslashit((string) wp_parse_url(get_the_permalink($current), PHP_URL_PATH));
        $path = (string) wp_parse_url($uri, PHP_URL_PATH);
        $query = (string) wp_parse_url($uri, PHP_URL_QUERY);
        if (strpos($path, $from . '/') !== 0) {
            return;
        }
        $rest = $args['wpjobportallt'] === 'controlpanel' ? '/' : substr($path, strlen($from));
        wp_safe_redirect($to . $rest . ($query !== '' ? '?' . $query : ''), 301);
        exit;
    }

    /** W-18: Careers mode has one company; its people work in the employer area, nobody else sees it. */
    private static function hides_employer_area() {
        if (!class_exists('WPJOBPORTAL_Product_Mode_Service') || WPJOBPORTAL_Product_Mode_Service::get_mode() !== WPJOBPORTAL_Product_Mode_Service::MODE_CAREERS) {
            return false;
        }
        return !current_user_can('manage_options') && !WPJOBPORTALincluder::getObjectClass('user')->isemployer();
    }

    public static function careers_employer_area() {
        if (is_page() && self::hides_employer_area() && (int) get_queried_object_id() === self::live_page('wpjobportal_employer_controlpanel') && self::jobs_page()) {
            wp_safe_redirect(get_the_permalink(self::jobs_page()));
            exit;
        }
    }

    /** W-10: the workspace is for hiring staff, so only they see it in a page menu. */
    private static function hides_workspace() {
        return !current_user_can('manage_options') && !WPJOBPORTALincluder::getObjectClass('user')->isemployer()
            && !(class_exists('WPJOBPORTAL_Recruit_Suite_Service') && WPJOBPORTAL_Recruit_Suite_Service::has_active_membership());
    }

    public static function careers_hide_employer_page($pages) {
        if (is_admin() || !is_array($pages)) {
            return $pages;
        }
        $hidden = array();
        if (self::hides_employer_area()) {
            $hidden[] = self::live_page('wpjobportal_employer_controlpanel');
        }
        if (self::hides_workspace()) {
            $hidden[] = self::live_page('wpjobportal_recruit_workspace');
        }
        return empty($hidden) ? $pages : array_values(array_filter($pages, function ($page) use ($hidden) {
            return !in_array((int) $page->ID, $hidden, true);
        }));
    }

    public static function careers_hide_employer_menu_item($items) {
        if (!self::hides_employer_area()) {
            return $items;
        }
        $hidden = self::live_page('wpjobportal_employer_controlpanel');
        return array_filter($items, function ($item) use ($hidden) {
            return !($item->object === 'page' && (int) $item->object_id === $hidden);
        });
    }

    /** "Log in" for a guest state, coming back to this page afterwards. */
    public static function login_button() {
        $back = wpjobportalphplib::wpJP_safe_encoding((string) get_the_permalink());
        $url = wpjobportal::wpjobportal_makeUrl(array('wpjobportalme' => 'wpjobportal', 'wpjobportallt' => 'login', 'wpjobportalredirecturl' => $back, 'wpjobportalpageid' => wpjobportal::wpjobportal_getDefaultPageid())); // a page whose shortcode can draw the form
        return ' <a class="wjportal-guest-login-btn" href="' . esc_url($url) . '">' . esc_html__('Log in', 'wp-job-portal') . '</a>';
    }
}
