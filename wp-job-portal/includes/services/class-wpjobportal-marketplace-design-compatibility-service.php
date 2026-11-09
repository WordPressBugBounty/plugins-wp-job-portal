<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Keeps optional Marketplace Suite screens visually consistent with Core.
 *
 * This adapter owns presentation only. Marketplace routes, templates, data,
 * permissions, nonces, and mutations remain under the add-on's control.
 */
class WPJOBPORTAL_Marketplace_Design_Compatibility_Service {

    const STYLE_HANDLE = 'wpjobportal-marketplace-compat';
    const SCRIPT_HANDLE = 'wpjobportal-marketplace-compat';

    public static function register() {
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_assets'), 100);
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_frontend_assets'), 100);
        add_action('admin_footer', array(__CLASS__, 'render_admin_shell_template'), 5);
        // The admin menu (WPJOBPORTAL_Admin_Menu_Service) draws these links itself now.
        // add_action('wpjobportal_addons_admin_sidemenu_package', array(__CLASS__, 'render_admin_sidebar'), 20, 2);
    }

    public static function enqueue_admin_assets() {
        if (!self::is_marketplace_available() || !self::is_admin_page()) {
            return;
        }
        wp_enqueue_style(
            self::STYLE_HANDLE,
            WPJOBPORTAL_PLUGIN_URL . 'includes/css/wpjobportal-marketplace-compat.css',
            array('wpjobportal-admin-desktop-css'),
            wpjobportal_asset_version('includes/css/wpjobportal-marketplace-compat.css')
        );
        wp_enqueue_script(
            self::SCRIPT_HANDLE,
            WPJOBPORTAL_PLUGIN_URL . 'includes/js/wpjobportal-marketplace-compat.js',
            array(),
            wpjobportal_asset_version('includes/js/wpjobportal-marketplace-compat.js'),
            true
        );
    }

    public static function enqueue_frontend_assets() {
        if (!self::is_marketplace_available() || !self::is_frontend_view()) {
            return;
        }
        wp_enqueue_style(
            self::STYLE_HANDLE,
            WPJOBPORTAL_PLUGIN_URL . 'includes/css/wpjobportal-marketplace-compat.css',
            array('wpjobportal-style'),
            wpjobportal_asset_version('includes/css/wpjobportal-marketplace-compat.css')
        );
        wp_enqueue_script(
            self::SCRIPT_HANDLE,
            WPJOBPORTAL_PLUGIN_URL . 'includes/js/wpjobportal-marketplace-compat.js',
            array(),
            wpjobportal_asset_version('includes/js/wpjobportal-marketplace-compat.js'),
            true
        );
    }

    public static function render_admin_shell_template() {
        if (!self::is_marketplace_available() || !self::is_admin_page()) {
            return;
        }
        $page = self::admin_page();
        $screens = self::admin_screens();
        $screen = $screens[$page];
        ?>
        <template id="wpjobportal-marketplace-native-shell-template">
            <div id="wpjobportaladmin-wrapper" class="wjpl-page wpjobportal-marketplace-native-shell">
                <div id="wpjobportaladmin-leftmenu">
                    <?php WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
                </div>
                <div id="wpjobportaladmin-data">
                    <?php
                    // W-19: the same top bar and title row as every core screen;
                    // the link to a sibling screen sits in the title row's menu.
                    WPJOBPORTAL_Admin_List_UI::page_head($screen['title'], '', array($screen['action_label'] => $screen['action_url']));
                    ?>
                    <div id="wpjobportal-admin-wrapper" class="wpjobportal-marketplace-native-content p0 bg-n bs-n" data-wpjobportal-marketplace-content></div>
                </div>
            </div>
        </template>
        <?php
    }

    public static function render_admin_sidebar($controller, $layout) {
        unset($layout);
        if (!self::is_marketplace_available()) {
            return;
        }
        $screens = self::admin_screens();
        $active = isset($screens[$controller]) ? ' active' : '';
        echo '<li class="treeview' . esc_attr($active) . '">';
        echo '<a href="' . esc_url(admin_url('admin.php?page=wpjobportal_marketplacesuite')) . '" class="wpjobportaladmin-menu-icon-class wpjobportaladmin-menu-icon-reports" title="' . esc_attr__('Marketplace Suite', 'wp-job-portal') . '">';
        // Inside the label span: it is absolutely positioned, so a sibling in
        // the normal flow would be drawn over the words rather than after them.
        echo '<span class="wpjobportaladmin-text">' . esc_html__('Marketplace Suite', 'wp-job-portal');
        wpjobportal_new_feature_badge('marketplace');
        echo '</span>';
        echo '</a>';
        echo '<ul class="wpjobportaladmin-sidebar-submenu treeview-menu">';
        foreach ($screens as $page => $screen) {
            $class = $controller === $page ? 'active' : '';
            echo '<li class="' . esc_attr($class) . '"><a href="' . esc_url(admin_url('admin.php?page=' . $page)) . '" title="' . esc_attr($screen['title']) . '">' . esc_html($screen['title']) . '</a></li>';
        }
        echo '</ul></li>';
    }

    private static function admin_screens() {
        return array(
            'wpjobportal_marketplacesuite' => array(
                'title' => __('Marketplace Suite', 'wp-job-portal'),
                'action_label' => __('Manage Packages', 'wp-job-portal'),
                'action_url' => admin_url('admin.php?page=wpjobportal_package'),
            ),
            'wpjobportal_marketplace_trust' => array(
                'title' => __('Trust & Safety', 'wp-job-portal'),
                'action_label' => __('Marketplace Overview', 'wp-job-portal'),
                'action_url' => admin_url('admin.php?page=wpjobportal_marketplacesuite'),
            ),
            'wpjobportal_marketplace_commerce' => array(
                'title' => __('Marketplace Reports', 'wp-job-portal'),
                'action_label' => __('Manage Packages', 'wp-job-portal'),
                'action_url' => admin_url('admin.php?page=wpjobportal_package'),
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
        $view = sanitize_key((string) WPJOBPORTALrequest::getVar('marketplace_view', 'get', ''));
        return 'employer' === $module && 'controlpanel' === $layout && in_array($view, array('verification', 'promotions'), true);
    }

    private static function is_marketplace_available() {
        return class_exists('wpjobportalmarketplacesuite');
    }
}
