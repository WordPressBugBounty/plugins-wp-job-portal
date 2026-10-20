<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * The plain listing widgets, in the free core.
 *
 * This is one of the three features split between tiers rather than moved
 * wholesale. Core registers the widgets that simply display records already in
 * the database and need nothing but a count. Pro keeps the ones that depend on
 * something more: a map provider, a resume search index, or a page builder.
 *
 * The two sets are disjoint classes, so both tiers can register at once without
 * colliding. The old Front-end Widgets add-on declared these same widgets under
 * the original class names; Core's are WPJOBPORTALCORE* since 2.7.0 (same
 * widget ids, so placed widgets keep working), so they also load while that
 * add-on is active but paused. While it actually runs, the absorbed-features
 * registry leaves the widgets to it.
 *
 * @see WPJOBPORTAL_Feature_Tiers::basic_widgets()
 */
final class WPJOBPORTAL_Feature_widgets {

    public static function register() {
        $base = WPJOBPORTAL_PLUGIN_PATH . 'modules/widgets/';

        foreach (WPJOBPORTAL_Feature_Tiers::basic_widgets() as $widget) {
            $widget = sanitize_key((string) $widget);
            if ($widget === '') {
                continue;
            }

            $file = $base . $widget . '.php';
            if (file_exists($file)) {
                // Each widget file registers itself on widgets_init.
                include_once $file;
            }
        }
    }
}
