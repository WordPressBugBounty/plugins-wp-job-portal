<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Basic browser printing, in the free core.
 *
 * The second of the three tier-split features. Core gives every site a print
 * control that uses the browser's own print dialogue, plus a stylesheet that
 * makes the printed page readable: navigation, buttons and other page furniture
 * are hidden so the record itself survives onto paper.
 *
 * Pro's Documents module replaces this with a formatted print view built from
 * its own templates. Both must never draw a control at once, so the provider is
 * resolved at render time rather than at registration — by then every plugin
 * has loaded and Pro has had its chance to claim it.
 *
 * @see WPJOBPORTAL_Feature_Tiers::print_provider()
 */
final class WPJOBPORTAL_Feature_print {

    const HANDLE = 'wpjobportal-print';

    public static function register() {
        $feature = new self();

        add_action('wp_enqueue_scripts', array($feature, 'enqueue_print_styles'));

        // The same extension points Pro's Documents module uses, so whichever
        // provider is in charge draws in the same places.
        add_action('wpjobportal_addons_resume_bottom_action_appliedresume', array($feature, 'render_applied_resume_action'), 10, 2);
        add_filter('wpjobportal_addons_resume_views_action_for_print', array($feature, 'render_resume_action'), 10, 2);
    }

    /**
     * Whether Core is the print provider on this request.
     *
     * @return bool
     */
    private function owns_printing() {
        return WPJOBPORTAL_Feature_Tiers::print_provider() === 'basic';
    }

    /**
     * A print stylesheet, so the browser's own print produces something usable.
     */
    public function enqueue_print_styles() {
        if (!$this->owns_printing()) {
            return;
        }

        $relative = 'includes/css/print.css';
        $file = WPJOBPORTAL_PLUGIN_PATH . $relative;
        if (!file_exists($file)) {
            return;
        }

        wp_enqueue_style(
            self::HANDLE,
            WPJOBPORTAL_PLUGIN_URL . $relative,
            array(),
            function_exists('wpjobportal_asset_version') ? wpjobportal_asset_version($relative) : null,
            'print'
        );
    }

    /**
     * Print control on an applied resume row.
     *
     * @param object $resume Applied-resume row.
     * @param string $class  Existing contextual action class.
     */
    public function render_applied_resume_action($resume, $class = '') {
        if (!$this->owns_printing()) {
            return;
        }

        $classes = 'wjportal-resume-act-btn wjportal-resume-act-action-links wjportal-list-act-btn-print-resume';
        if (isset(wpjobportal::$wpjobportal_theme_chk) && wpjobportal::$wpjobportal_theme_chk == 1) {
            $classes = 'wpj-jp-outline-btn';
        }
        $classes .= ' ' . sanitize_html_class((string) $class);

        printf(
            '<a href="#" onclick="window.print();return false;" class="%1$s" title="%2$s" aria-label="%2$s">%3$s</a>',
            esc_attr(trim($classes)),
            esc_attr__('Print this page', 'wp-job-portal'),
            esc_html__('Print', 'wp-job-portal')
        );
    }

    /**
     * Print control on a resume view.
     *
     * @param string $default   Existing filter value.
     * @param mixed  $resume_id Resume record id.
     * @return string
     */
    public function render_resume_action($default, $resume_id) {
        if (!$this->owns_printing()) {
            return (string) $default;
        }

        return sprintf(
            '<a class="wjportal-resume-adv-act-btn wjportal-list-act-btn-print" href="#"'
            . ' onclick="window.print();return false;" title="%1$s" aria-label="%1$s">%2$s</a>',
            esc_attr__('Print this page', 'wp-job-portal'),
            esc_html__('Print', 'wp-job-portal')
        );
    }
}
