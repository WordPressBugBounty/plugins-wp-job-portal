<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Share links for jobs and companies.
 *
 * Absorbed from the Social Share add-on. Owns no tables and no
 * routes; it renders share controls into existing pages.
 *
 * Hook names, routes and output are preserved exactly, and the callbacks are
 * carried over unchanged, so a site that removes the old add-on sees no
 * difference. Registration is skipped entirely while that add-on is still
 * installed; see WPJOBPORTAL_Absorbed_Features.
 */
final class WPJOBPORTAL_Feature_socialshare {

    public static function register() {
        $feature = new self();

        add_action('wpjobportal_credit_addons_social_share_for_jobs', array($feature, 'wpjobportal_addons_social_share_jobs'), 10, 1);
        add_action('wpjobportal_credit_addons_social_share_links_job', array($feature, 'wpjobportal_addons_social_link_share'), 10, 1);
        add_action('wpjobportal_credit_social_comments_for_jobs', array($feature, 'wpjobportal_addons_social_comments_for_jobs'), 10, 1);
        add_action('wpjobportal_addons_social_share_company', array($feature, 'wpjobportal_addons_social_company_share'), 10, 2);
    }

    /* ------------------------------------------------------------------ */
    /* Callbacks carried over unchanged from the add-on                    */
    /* ------------------------------------------------------------------ */

    public function  wpjobportal_addons_social_share_jobs($data){
        echo '<div id="job-info-sociallink">';
            if (!empty($data->facebook)) {
                echo '<a href="' . esc_url($data->facebook) . '" target="_blank"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL . 'includes/images/scround/fb.png') . '"/></a>';
            }
            if (!empty($data->twitter)) {
                echo '<a href="' . esc_url($data->twitter) . '" target="_blank"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL . 'includes/images/scround/twitter.png') . '"/></a>';
            }
            if (!empty($data->googleplus)) {
                echo '<a href="' . esc_url($data->googleplus) . '" target="_blank"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL . 'includes/images/scround/gmail.png') . '"/></a>';
            }
            if (!empty($data->linkedin)) {
                echo '<a href="' . esc_url($data->linkedin) . '" target="_blank"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL . 'includes/images/scround/in.png') . '"/></a>';
            }
        echo '</div>';

    }

    public function wpjobportal_addons_social_link_share($data){
        if(!empty($data)){
            $this->wpjobportal_socialshare_get_template('views/social-sharelinks', array(
                'data' => $data
            ));
        }
    }

    public function wpjobportal_addons_social_comments_for_jobs(){
        $this->wpjobportal_socialshare_get_template('views/social-comments-job');
    }

    public function wpjobportal_addons_social_company_share($company,$data_class){
         if ($data_class == 'one_column') { ?>
                <div id="job-info-sociallink">
                    <?php
                    if (!empty($company->facebook)) {
                        echo '<a href="' . esc_url($company->facebook) . '" target="_blank"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL . 'includes/images/scround/fb.png') . '"/></a>';
                    }
                    if (!empty($company->twitter)) {
                        echo '<a href="' . esc_url($company->twitter) . '" target="_blank"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL . 'includes/images/scround/twitter.png') . '"/></a>';
                    }
                    if (!empty($company->googleplus)) {
                        echo '<a href="' . esc_url($company->googleplus) . '" target="_blank"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL . 'includes/images/scround/gmail.png') . '"/></a>';
                    }
                    if (!empty($company->linkedin)) {
                        echo '<a href="' . esc_url($company->linkedin) . '" target="_blank"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL . 'includes/images/scround/in.png') . '"/></a>';
                    }
                    ?>
                </div>
            <?php }

        if ($data_class == 'two_column') { ?>
            <div id="job-info-sociallink">
                <?php
                if (!empty($company->facebook)) {
                    echo '<a href="' . esc_url($company->facebook) . '" target="_blank"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL . 'includes/images/scround/fb.png') . '"/></a>';
                }
                if (!empty($company->twitter)) {
                    echo '<a href="' . esc_url($company->twitter) . '" target="_blank"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL . 'includes/images/scround/twitter.png') . '"/></a>';
                }
                if (!empty($company->googleplus)) {
                    echo '<a href="' . esc_url($company->googleplus) . '" target="_blank"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL . 'includes/images/scround/gmail.png') . '"/></a>';
                }
                if (!empty($company->linkedin)) {
                    echo '<a href="' . esc_url($company->linkedin) . '" target="_blank"><img src="' . esc_url(WPJOBPORTAL_PLUGIN_URL . 'includes/images/scround/in.png') . '"/></a>';
                }
                ?>
            </div>
        <?php }
    }

    public function wpjobportal_socialshare_get_template($template_name, $args = array()) {
        // Core's own module tree, not dirname(__FILE__). This class used to
        // live in wp-job-portal-socialshare/, where module/tmpl/views/ sat
        // beside it; absorbing it moved the class to includes/features/ and
        // left the path pointing at a directory that has never existed here.
        // file_exists() then failed and this returned false without a word, so
        // the share links were enabled in the settings and simply absent from
        // every job page.
        $allowed_templates = array(
            'views/social-sharelinks' => WPJOBPORTAL_PLUGIN_PATH . 'modules/socialshare/tmpl/views/social-sharelinks.php',
            'views/social-comments-job' => WPJOBPORTAL_PLUGIN_PATH . 'modules/socialshare/tmpl/views/social-comments-job.php',
        );

        if (!isset($allowed_templates[$template_name]) || !file_exists($allowed_templates[$template_name])) {
            return false;
        }

        if (!empty($args) && is_array($args)) {
            extract($args);
        }

        include $allowed_templates[$template_name];
        return true;
    }

}

/**
 * Is one of the share buttons switched on?
 *
 * The share templates call this, and it is defined in the job detail view's
 * include - so it exists on a job page and nowhere else. That was survivable
 * only while the templates could not be found at all; with the path corrected,
 * rendering a share block from anywhere else - the company page hook does
 * exactly that - would be a fatal error instead.
 *
 * Defined here, guarded, so the definition in the job view still wins where it
 * loads first and the behaviour is identical either way. It also answers from
 * the configuration directly when the job page has not populated its cache,
 * rather than reading a missing array key.
 *
 * @param string $wpjobportal_name Configuration name.
 * @return bool
 */
if (!function_exists('wpjobportal_checkLinks')) {
    function wpjobportal_checkLinks($wpjobportal_name) {
        if (isset(wpjobportal::$_data['config'][$wpjobportal_name])) {
            return (int) wpjobportal::$_data['config'][$wpjobportal_name] === 1;
        }

        return (int) wpjobportal::$_config->getConfigValue($wpjobportal_name) === 1;
    }
}
