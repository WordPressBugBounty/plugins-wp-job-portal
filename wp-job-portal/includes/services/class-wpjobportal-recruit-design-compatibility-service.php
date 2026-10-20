<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Keeps optional Recruit Suite screens visually consistent with Core.
 *
 * This adapter owns presentation only. Recruit Suite routes, templates, data,
 * permissions, nonces, and mutations remain under the add-on's control.
 */
class WPJOBPORTAL_Recruit_Design_Compatibility_Service {

    const STYLE_HANDLE = 'wpjobportal-recruit-compat';
    const SCRIPT_HANDLE = 'wpjobportal-recruit-compat';

    public static function register() {
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_assets'), 100);
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_frontend_assets'), 100);
        add_action('admin_footer', array(__CLASS__, 'render_admin_shell_template'), 6);
    }

    public static function enqueue_admin_assets() {
        if (!self::is_recruit_available() || !self::is_admin_page()) {
            return;
        }
        wp_enqueue_style(
            self::STYLE_HANDLE,
            WPJOBPORTAL_PLUGIN_URL . 'includes/css/wpjobportal-recruit-compat.css',
            array('wpjobportal-admin-desktop-css'),
            wpjobportal_asset_version('includes/css/wpjobportal-recruit-compat.css')
        );
        wp_enqueue_script(
            self::SCRIPT_HANDLE,
            WPJOBPORTAL_PLUGIN_URL . 'includes/js/wpjobportal-recruit-compat.js',
            array(),
            wpjobportal_asset_version('includes/js/wpjobportal-recruit-compat.js'),
            true
        );
    }

    public static function enqueue_frontend_assets() {
        if (!self::is_recruit_available() || !self::is_frontend_view()) {
            return;
        }
        wp_enqueue_style(
            self::STYLE_HANDLE,
            WPJOBPORTAL_PLUGIN_URL . 'includes/css/wpjobportal-recruit-compat.css',
            array('wpjobportal-style', 'wpjobportal-recruitsuite'),
            wpjobportal_asset_version('includes/css/wpjobportal-recruit-compat.css')
        );
        wp_enqueue_script(
            self::SCRIPT_HANDLE,
            WPJOBPORTAL_PLUGIN_URL . 'includes/js/wpjobportal-recruit-compat.js',
            array(),
            wpjobportal_asset_version('includes/js/wpjobportal-recruit-compat.js'),
            true
        );
    }

    public static function render_admin_shell_template() {
        if (!self::is_recruit_available() || !self::is_admin_page()) {
            return;
        }
        $page = self::admin_page();
        $screens = self::admin_screens();
        $screen = $screens[$page];
        ?>
        <template id="wpjobportal-recruit-native-shell-template">
            <div id="wpjobportaladmin-wrapper" class="wpjobportal-recruit-native-shell">
                <div id="wpjobportaladmin-leftmenu">
                    <?php WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
                </div>
                <div id="wpjobportaladmin-data">
                    <div id="wpjobportal-wrapper-top">
                        <div id="wpjobportal-wrapper-top-left">
                            <div id="wpjobportal-breadcrumbs">
                                <ul>
                                    <li><a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal')); ?>" title="<?php echo esc_attr__('Dashboard', 'wp-job-portal'); ?>"><?php echo esc_html__('Dashboard', 'wp-job-portal'); ?></a></li>
                                    <li><?php echo esc_html($screen['title']); ?></li>
                                </ul>
                            </div>
                        </div>
                        <div id="wpjobportal-wrapper-top-right">
                            <div id="wpjobportal-config-btn"><a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_configuration')); ?>" title="<?php echo esc_attr__('Configuration', 'wp-job-portal'); ?>"><img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL . 'includes/images/control_panel/dashboard/config.png'); ?>" alt="<?php echo esc_attr__('Configuration', 'wp-job-portal'); ?>"></a></div>
                            <div id="wpjobportal-help-btn" class="wpjobportal-help-btn"><a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal&wpjobportallt=help')); ?>" title="<?php echo esc_attr__('Help', 'wp-job-portal'); ?>"><img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL . 'includes/images/control_panel/dashboard/help.png'); ?>" alt="<?php echo esc_attr__('Help', 'wp-job-portal'); ?>"></a></div>
                            <div id="wpjobportal-vers-txt"><?php echo esc_html__('Version', 'wp-job-portal') . ': '; ?><span class="wpjobportal-ver"><?php echo esc_html(WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue('versioncode')); ?></span></div>
                        </div>
                    </div>
                    <div id="wpjobportal-head">
                        <h1 class="wpjobportal-head-text"><?php echo esc_html($screen['title']); ?></h1>
                        <a class="wpjobportal-add-link button" href="<?php echo esc_url($screen['action_url']); ?>" title="<?php echo esc_attr($screen['action_label']); ?>"><?php echo esc_html($screen['action_label']); ?></a>
                    </div>
                    <div id="wpjobportal-admin-wrapper" class="wpjobportal-recruit-native-content p0 bg-n bs-n" data-wpjobportal-recruit-content></div>
                </div>
            </div>
        </template>
        <?php if (!self::suite_renders_own_sidebar()) : ?>
        <template id="wpjobportal-recruit-native-sidebar-template">
            <?php self::render_admin_sidebar($page, ''); ?>
        </template>
        <?php endif; ?>
        <?php
    }

    public static function render_admin_sidebar($controller, $layout) {
        unset($layout);
        if (!self::is_recruit_available()) {
            return;
        }
        $screens = self::admin_screens();
        $active = isset($screens[$controller]) ? ' active' : '';
        echo '<li class="treeview' . esc_attr($active) . '" data-wpjobportal-recruit-sidebar>';
        echo '<a href="' . esc_url(admin_url('admin.php?page=wpjobportal_recruitteam')) . '" class="wpjobportaladmin-menu-icon-class wpjobportaladmin-menu-icon-resumes" title="' . esc_attr__('Recruit Suite', 'wp-job-portal') . '">';
        echo '<span class="wpjobportaladmin-text">' . esc_html__('Recruit Suite', 'wp-job-portal') . '</span></a>';
        echo '<ul class="wpjobportaladmin-sidebar-submenu treeview-menu">';
        foreach ($screens as $page => $screen) {
            $class = $controller === $page ? 'active' : '';
            echo '<li class="' . esc_attr($class) . '"><a href="' . esc_url(admin_url('admin.php?page=' . $page)) . '" title="' . esc_attr($screen['title']) . '">' . esc_html($screen['title']) . '</a></li>';
        }
        echo '</ul></li>';
    }

    private static function admin_screens() {
        return array(
            'wpjobportal_recruitteam' => array(
                'title' => __('Recruiting Team', 'wp-job-portal'),
                'action_label' => __('Candidate Workspace', 'wp-job-portal'),
                'action_url' => admin_url('admin.php?page=wpjobportal_recruitworkspace'),
            ),
            'wpjobportal_recruitpipeline' => array(
                'title' => __('Recruit Pipeline', 'wp-job-portal'),
                'action_label' => __('Candidate Workspace', 'wp-job-portal'),
                'action_url' => admin_url('admin.php?page=wpjobportal_recruitworkspace'),
            ),
            'wpjobportal_recruitworkspace' => array(
                'title' => __('Candidate Workspace', 'wp-job-portal'),
                'action_label' => __('Recruit Pipeline', 'wp-job-portal'),
                'action_url' => admin_url('admin.php?page=wpjobportal_recruitpipeline'),
            ),
            'wpjobportal_recruitinsights' => array(
                'title' => __('Recruiting Insights', 'wp-job-portal'),
                'action_label' => __('Recruit Pipeline', 'wp-job-portal'),
                'action_url' => admin_url('admin.php?page=wpjobportal_recruitpipeline'),
            ),
            'wpjobportal_recruitautomation' => array(
                'title' => __('Recruiting Automation', 'wp-job-portal'),
                'action_label' => __('Candidate Workspace', 'wp-job-portal'),
                'action_url' => admin_url('admin.php?page=wpjobportal_recruitworkspace'),
            ),
        );
    }

    private static function admin_page() {
        return sanitize_key((string) WPJOBPORTALrequest::getVar('page', 'get', ''));
    }

    private static function is_admin_page() {
        $screens = self::admin_screens();
        return isset($screens[self::admin_page()]);
    }

    private static function is_frontend_view() {
        $module = sanitize_key((string) WPJOBPORTALrequest::getVar('wpjobportalme', 'get', ''));
        $layout = sanitize_key((string) WPJOBPORTALrequest::getVar('wpjobportallt', 'get', ''));
        $view = sanitize_key((string) WPJOBPORTALrequest::getVar('recruit_view', 'get', ''));
        $recruit_views = array('overview', 'jobs', 'candidates', 'pipeline', 'tasks', 'interviews', 'scorecards', 'offers', 'notifications', 'insights', 'pools', 'team');
        if ('employer' === $module && 'controlpanel' === $layout && in_array($view, $recruit_views, true)) {
            return true;
        }
        if ('recruitsuite' === $module && in_array($layout, array('candidateportal', 'recruitcenter'), true)) {
            return true;
        }
        return 'resume' === $module && 'viewresume' === $layout && !empty(WPJOBPORTALrequest::getVar('application_id', 'get'));
    }

    /**
     * Whether the installed suite contributes its own admin menu group.
     *
     * This service exists for a Recruit Suite that predates Core's admin
     * shell and cannot put itself in the menu. A current suite does: it
     * hooks wpjobportal_addons_admin_sidemenu_suite_groups, and the shell
     * template below already includes the whole Core side menu, which fires
     * that hook. Injecting this group as well gave the sidebar two entries
     * for the same five screens - "Recruitment" from the suite and "Recruit
     * Suite" from here - which is what a tester found.
     *
     * The test is the hook rather than a version number, because what
     * matters is whether something is already drawing the group, and an
     * older suite that draws nothing still gets it from here.
     *
     * @return bool
     */
    private static function suite_renders_own_sidebar() {
        return (bool) has_action('wpjobportal_addons_admin_sidemenu_suite_groups');
    }

    private static function is_recruit_available() {
        return class_exists('wpjobportalrecruitsuite');
    }
}
