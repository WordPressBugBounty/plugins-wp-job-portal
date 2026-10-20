<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Where the free tier ends and a paid suite begins.
 *
 * Most of the packaging work moved whole add-ons: a feature belongs to Core or
 * it belongs to a suite. Three features are split down the middle instead —
 * print, widgets and job alerts — where the free core gets a usable version and
 * a suite raises the ceiling.
 *
 * Splitting a feature is more delicate than moving one, so the limits live here
 * rather than being scattered through the code that enforces them:
 *
 *   - Core always owns the implementation. The free tier has to work with no
 *     suite installed, so a suite can never be a hidden dependency.
 *   - A suite raises limits through a filter. It never re-implements the
 *     feature, so there is one code path and the paid tier cannot drift from
 *     the free one.
 *   - Every limit is filtered, so a site can be given a different ceiling
 *     without touching either product.
 *
 * The defaults below are the FREE tier. WP Job Portal Pro filters them upward;
 * see the tier filters in its print, widgets and job alert modules.
 */
final class WPJOBPORTAL_Feature_Tiers {

    /** Job alert frequency codes, as stored in jobalertsetting.alerttype. */
    const ALERT_DAILY = 1;
    const ALERT_WEEKLY = 2;
    const ALERT_MONTHLY = 3;
    const ALERT_INSTANT = 4;

    /**
     * Which alert frequencies a job seeker may choose.
     *
     * Free offers weekly only, which is the digest most boards actually want.
     * Pro adds daily, monthly and instant.
     *
     * Frequency is the whole of this split. The table has always held one row
     * per job seeker, so no tier can offer more than one alert; what genuinely
     * differs in cost is how often delivery runs, and instant alerts most of
     * all, since every published job schedules a pass of its own.
     *
     * @return array
     */
    public static function job_alert_frequencies() {
        $allowed = apply_filters('wpjobportal_tier_job_alert_frequencies', array(self::ALERT_WEEKLY));

        return array_values(array_unique(array_map('intval', (array) $allowed)));
    }

    /**
     * Whether the job alert admin screens and delivery reports are available.
     *
     * @return bool
     */
    public static function job_alert_admin_tools() {
        return (bool) apply_filters('wpjobportal_tier_job_alert_admin_tools', false);
    }

    /**
     * Which side supplies printing.
     *
     * 'basic' is the browser's own print of the page, styled by Core. 'documents'
     * is Pro's formatted print view with its own templates. Both must never draw
     * a control at once, so this is asked at render time rather than at
     * registration, by which point every plugin has loaded.
     *
     * @return string basic|documents
     */
    public static function print_provider() {
        $provider = apply_filters('wpjobportal_tier_print_provider', 'basic');

        return in_array($provider, array('basic', 'documents'), true) ? $provider : 'basic';
    }

    /**
     * Widgets the free core registers.
     *
     * These are the plain listing widgets: they show records that already exist
     * and take no configuration beyond a count. The ones held back need either
     * an external dependency, a search index or a page builder.
     *
     * @return array
     */
    public static function basic_widgets() {
        $widgets = array(
            'jobs',
            'companies',
            'resume',
            'jobsbycategory',
            'jobsbyjobtype',
            'jobsbylocation',
            'jobsstats',
        );

        return (array) apply_filters('wpjobportal_tier_basic_widgets', $widgets);
    }

    /**
     * A short description of the current tier, for the admin and diagnostics.
     *
     * These are the ceilings that would apply, not necessarily what is running.
     * On a site still using the legacy add-ons, Core and the suites both stand
     * down and the add-on owns the feature outright, so the values here are
     * defaults nothing is consulting.
     *
     * @return array
     */
    public static function summary() {
        return array(
            'job_alert_frequencies' => self::job_alert_frequencies(),
            'job_alert_admin_tools' => self::job_alert_admin_tools(),
            'print_provider' => self::print_provider(),
            'basic_widgets' => count(self::basic_widgets()),
        );
    }
}
