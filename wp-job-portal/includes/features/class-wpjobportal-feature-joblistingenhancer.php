<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Ajax filtering and sorting on the job list.
 *
 * Absorbed from the Job Listing Enhancer add-on. The behaviour already
 * lives in Core and is switched on by the module key plus the
 * joblisting_ajax_* configuration rows; the add-on supplied little beyond
 * that key and the filter template. Its controller was almost entirely
 * commented out, so only the live parts are carried over.
 *
 * Hook names, routes and output are preserved exactly, and the callbacks are
 * carried over unchanged, so a site that removes the old add-on sees no
 * difference. Registration is skipped entirely while that add-on is still
 * installed; see WPJOBPORTAL_Absorbed_Features.
 */
final class WPJOBPORTAL_Feature_joblistingenhancer {

    public static function register() {
        // Nothing to hook. Core already contains the behaviour and switches
        // it on from the module key, which the registry adds for us.
    }
}
