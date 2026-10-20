<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * WordPress cron integration and its admin screen.
 *
 * Absorbed from the Cron Job add-on. This is infrastructure rather
 * than a feature: scheduled work such as job expiry and alerts depends on it,
 * so it belongs in the free core.
 *
 * Hook names, routes and output are preserved exactly, and the callbacks are
 * carried over unchanged, so a site that removes the old add-on sees no
 * difference. Registration is skipped entirely while that add-on is still
 * installed; see WPJOBPORTAL_Absorbed_Features.
 */
final class WPJOBPORTAL_Feature_cronjob {

    public static function register() {
        $feature = new self();

        add_action('wpjobportal_addons_admin_sidemenu_admin_cronjobs', array($feature, 'wpjobportal_addons_cronjobs'), 10, 2);
    }

    /* ------------------------------------------------------------------ */
    /* Callbacks carried over unchanged from the add-on                    */
    /* ------------------------------------------------------------------ */

    public function wpjobportal_addons_cronjobs($c,$layout){
        $class = '';
        if($c == 'wpjobportal_cronjob' && ($layout == 'cronjob')) $class = 'active';
        echo ' <li class="'.esc_attr($class).'">
                    <a href="admin.php?page=wpjobportal_cronjob&wpjobportallt=cronjob" title="'.esc_attr__('Cron Job', 'wp-job-portal').'">
                        '.esc_html__('Cron Job', 'wp-job-portal').'
                    </a>
            </li>';
    }

}
