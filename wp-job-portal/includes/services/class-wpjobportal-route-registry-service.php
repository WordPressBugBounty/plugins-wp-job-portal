<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Explicit registry for legacy controller actions.
 *
 * Core and supported commercial add-on controller methods are denied unless
 * they are listed here. Third-party and retired add-ons can register their own
 * routes through the documented filter. If an unknown add-on is active, the
 * add-on dispatcher automatically returns to compatibility mode so an update
 * cannot unexpectedly break that extension.
 */
class WPJOBPORTAL_Route_Registry_Service {

    const STATS_OPTION = 'wpjobportal_route_registry_stats';

    /**
     * Return the core controller actions that may be reached by the legacy
     * form/task dispatcher.
     *
     * Method names are normalized to lowercase for case-insensitive matching,
     * which mirrors PHP method invocation.
     *
     * @return array
     */
    public static function core_routes() {
        return array(
            'activitylog' => array(),
            'careerlevel' => array('savecareerlevel', 'remove', 'publish', 'unpublish', 'saveordering'),
            'category' => array('savecategory', 'remove', 'publish', 'unpublish', 'saveordering', 'getcategorydatabyname'),
            'city' => array('getaddressdatabycityname', 'removecity', 'publish', 'unpublish', 'savecity', 'loadaddressdata', 'savecitynamesettings'),
            'common' => array('makedefault', 'savenewinwpjobportal', 'wpjobportal_synchronize_ai_search_data'),
            'company' => array(
                'approvequeuecompany',
                'approvequeuefeaturedcompany',
                'rejectqueuecompany',
                'rejectqueuefeaturecompany',
                'approvequeueallcompanies',
                'rejectqueueallcompanies',
                'savecompany',
                'enforcedelete',
                'remove',
                'addviewcontactdetail',
            ),
            'configuration' => array('saveconfiguration'),
            'country' => array('remove', 'publish', 'unpublish', 'savecountry'),
            'currency' => array('savecurrency', 'remove', 'publish', 'unpublish', 'saveordering'),
            'customfield' => array(
                'fieldrequired',
                'fieldnotrequired',
                'fieldpublished',
                'fieldunpublished',
                'visitorfieldunpublished',
                'customfielddown',
                'saveuserfield',
                'remove',
                'downloadcustomfile',
            ),
            'emailtemplate' => array('saveemailtemplate'),
            'emailtemplatestatus' => array('sendemail', 'nosendemail'),
            'employer' => array(),
            'fieldordering' => array(
                'fieldrequired',
                'fieldnotrequired',
                'fieldpublished',
                'fieldunpublished',
                'visitorfieldpublished',
                'visitorfieldunpublished',
                'saveuserfield',
                'savesearchfieldordering',
                'savesearchfieldorderingfromform',
                'remove',
            ),
            'highesteducation' => array('savehighesteducation', 'remove', 'publish', 'unpublish', 'saveordering'),
            'job' => array(
                'approvequeuejob',
                'rejectqueuejob',
                'approvequeuefeaturedjob',
                'rejectqueuefeaturedjob',
                'savejob',
                'remove',
                'jobenforcedelete',
            ),
            'jobapply' => array('jobapplyasvisitor', 'applyonjob'),
            'jobsearch' => array('savejobsearch', 'removesavedjobsearch'),
            'jobseeker' => array(),
            'jobstatus' => array('savejobstatus', 'remove', 'publish', 'unpublish', 'saveordering'),
            'jobtype' => array('savejobtype', 'remove', 'publish', 'unpublish', 'saveordering'),
            'postinstallation' => array(
                'save',
                'savesampledata',
                'savetemplatesampledata',
                'getdemocode',
                'dismissonboarding',
                'reopenonboarding',
                'cleansampledata',
                'assignsampleowner',
            ),
            'quickapply' => array('addtoquickapply', 'quickapplyonjob'),
            'report' => array(),
            'resume' => array(
                'approvequeueresume',
                'approvequeuefeatureresume',
                'rejectqueueresume',
                'rejectqueuefeatureresume',
                'approvequeueallresumes',
                'rejectqueueallresumes',
                'resumeenforcedelete',
                'saveresume',
                'removeresume',
                'getallresumefiles',
                'getresumefiledownloadbyid',
                'addviewresumedetail',
            ),
            'salaryrangetype' => array('savesalaryrangetype', 'remove', 'publish', 'unpublish', 'saveordering'),
            'slug' => array('saveslug', 'saveprefix', 'savehomeprefix', 'resetallslugs'),
            'state' => array('remove', 'publish', 'unpublish', 'savestate'),
            'systemerror' => array(),
            'systemstatus' => array(),
            'theme' => array('savetheme', 'savedesign'),
            'thirdpartyimport' => array('importjobmanagerdata', 'getjobmanagerdatastats'),
            'user' => array('saveuserrole', 'saveuser', 'assignuserrole', 'changeuserstatus', 'deleteuser', 'enforcedeleteuser'),
            'wpjobportal' => array('saveordering', 'savedocumenttitleoptions', 'savescreenoptions'),
            'zywrap' => array('save_zywrap_settings'),
            'zywraplogs' => array(),
        );
    }

    /**
     * Commercial add-ons included in the coordinated 2026 release.
     *
     * Add-ons without a public controller route remain in this list so status
     * reporting can distinguish a fully reviewed installation from a legacy or
     * third-party extension. Retired Social Login is intentionally excluded.
     *
     * @return array
     */
    public static function supported_addons() {
        return array(
            'addressdata',
            'advanceresumebuilder',
            'aijobsearch',
            'airesumesearch',
            'aisuggestedjobs',
            'aisuggestedresumes',
            'allcompanies',
            'allresumes',
            'copyjob',
            'coverletter',
            'credits',
            'cronjob',
            'departments',
            'elegantdesign',
            'export',
            'featuredcompany',
            'featuredjob',
            'featureresume',
            'folder',
            'jobalert',
            'joblistingenhancer',
            'message',
            'multicompany',
            'multiresume',
            'pdf',
            'print',
            'reports',
            'recruitsuite',
            'resumeaction',
            'resumesearch',
            'rssfeedback',
            'shortlist',
            'smartmatching',
            'socialshare',
            'tag',
            'tellfriend',
            'visitorapplyjob',
            'visitorcanaddjob',
            'widgets',
        );
    }

    /**
     * WP Job Portal's own 2.7.0 products and the add-on keys they register.
     *
     * Not unknown, so System Status does not flag them, but their routes are
     * not all listed below yet, so they keep compatibility mode while active.
     *
     * @return array
     */
    public static function first_party_products() {
        return array(
            'pro',
            'marketplacesuite',
            'matchingsuite',
            'rating',
        );
    }

    /**
     * Reviewed controller routes supplied by the 38 supported add-ons.
     *
     * Helper methods, constructors, page handlers and parameterized internal
     * methods are deliberately absent. Method names are normalized later.
     *
     * @return array
     */
    public static function coordinated_addon_routes() {
        return array(
            'addressdata' => array('loadaddressdata'),
            'aijobsearch' => array(),
            'airesumesearch' => array(),
            'aisuggestedjobs' => array(),
            'aisuggestedresumes' => array(),
            'allcompanies' => array(),
            'allresumes' => array(),
            'copyjob' => array('addtocopyJob'),
            'coverletter' => array('approve', 'reject', 'savecoverletter', 'remove'),
            'credits' => array('saveCredits', 'remove', 'publish', 'unpublish'),
            'cronjob' => array(),
            'departments' => array('approve', 'reject', 'savedepartment', 'remove'),
            'export' => array('exportallresume', 'exportresume'),
            'featuredcompany' => array('removefeaturedcompany', 'addtofeaturedcompany'),
            'featuredjob' => array('addtoFeaturedJobs', 'removefeaturedjob'),
            'featureresume' => array('addtoFeaturedResume', 'removefeaturedresume'),
            'folder' => array('approve', 'reject', 'removeresumefromfolder', 'savefolder', 'remove'),
            'folderresume' => array('removeresumefromfolder'),
            'invoice' => array('publishinvoices', 'unpublishinvoices', 'remove'),
            'jobalert' => array(
                'savejobalert',
                'unsubscribeJobAlertSetting',
                'unsubscribeJobAlertSettingEmail',
                'sendjobalert',
            ),
            'message' => array(
                'publishmessages',
                'unpublishmessages',
                'savemessage',
                'remove',
                'removemessage',
                'enforcedeletemessage',
            ),
            'multicompany' => array('savecompany', 'remove', 'addviewcontactdetail'),
            'multiresume' => array('saveresume'),
            'mystats' => array(),
            'package' => array('savepackage', 'remove', 'publish', 'unpublish'),
            'paymentmethodconfiguration' => array('savepaymentconf'),
            'purchase' => array(
                'listingstripe',
                'listingCoverletterstripe',
                'listingCompanystripe',
                'listingResumeViewstripe',
                'listingFeatureCompanystripe',
                'listingFeaturedResumestripe',
                'listingJobstripe',
                'listingstripeFeaturedJob',
                'listingResumestripe',
                'listingResumeSearchstripe',
                'listingCompanyViewstripe',
                'listingstripeJobAlert',
                'packagestripe',
                'stripewebhook',
                'paypalFeaturedCompany',
                'listingpaypalFeaturedJob',
                'listingpaypalFeaturedResume',
                'featuredcompanypaypal',
                'featuredjobpaypal',
                'featuredresumepaypal',
                'listingpaypal',
                'listingpaypalcoverletter',
                'listingcoverletterpaypal',
                'listingdepartmentpaypal',
                'listingjobapplypaypal',
                'listingresumesearchpaypal',
                'listingpaypalJobAlert',
                'jobAlertpaypal',
                'listingcompanypaypal',
                'listingresumeviewpaypal',
                'listingcompanyviewpaypal',
                'listingjobpaypal',
                'listingpaypalJobApply',
                'listingresumepaypal',
                'listingpaypalResumeSearch',
                'packagepaypal',
                'purchasepackagepaypal',
                'paypalagreementapproved',
                'paypalwebhook',
                'listingpaypalCompany',
                'listingpaypalCompanyView',
                'listingpaypalResumeView',
                'listingpaypalJob',
                'listingpaypalResume',
                'woocommerceorder',
                'woocommedeptrceorder',
            ),
            'purchasehistory' => array(
                'savepurchasehistory',
                'saveuserpackage',
                'publish',
                'unpublish',
                'remove',
                'cancelsubscription',
            ),
            'reports' => array(),
            'resumeaction' => array(),
            'resumesearch' => array('removeSavedSearch', 'saveResumeSearch'),
            'shortlist' => array('removeshortlist'),
            'smartmatching' => array('approve'),
            'tag' => array('gettagsbytagname', 'removetag', 'publish', 'unpublish', 'savetag'),
            'visitorcanaddjob' => array('savevisitorjob'),
        );
    }

    /**
     * Determine whether a sanitized module/task pair may be dispatched.
     *
     * @param string $module  Module name.
     * @param string $task    Controller method.
     * @param string $channel form or task.
     * @return bool
     */
    public static function is_allowed($module, $task, $channel) {
        $module = sanitize_key($module);
        $task = strtolower(preg_replace('/[^A-Za-z0-9_]/', '', (string) $task));
        $channel = sanitize_key($channel);

        if ($module === '' || $task === '' || !in_array($channel, array('form', 'task'), true)) {
            self::record_denial($module, $task, $channel, 'invalid_route');
            return false;
        }

        $core_routes = self::core_routes();
        if (array_key_exists($module, $core_routes)) {
            if (in_array($task, $core_routes[$module], true)) {
                return true;
            }

            self::record_denial($module, $task, $channel, 'unregistered_core_route');
            return false;
        }

        $extension_routes = self::extension_routes();
        if (isset($extension_routes[$module]) && in_array($task, $extension_routes[$module], true)) {
            return true;
        }

        /**
         * Unknown add-ons receive a compatibility fallback only while at least
         * one active extension is outside the coordinated supported set.
         * Third-party add-ons can avoid compatibility mode by registering every
         * public route with wpjobportal_route_registry.
         */
        $allow_legacy = (bool) apply_filters(
            'wpjobportal_allow_legacy_addon_form_route',
            !self::strict_addon_mode(),
            $module,
            $task,
            $channel
        );

        if (!$allow_legacy) {
            self::record_denial($module, $task, $channel, 'unregistered_addon_route');
        }

        return $allow_legacy;
    }

    /**
     * Record a dispatcher denial without retaining request payloads.
     *
     * @param string $module  Module name.
     * @param string $task    Controller method.
     * @param string $channel form or task.
     * @param string $reason  Fixed reason code.
     * @return void
     */
    public static function record_denial($module, $task, $channel, $reason) {
        $stats = get_option(self::STATS_OPTION, array());
        if (!is_array($stats)) {
            $stats = array();
        }

        $stats = array(
            'denied' => isset($stats['denied']) ? absint($stats['denied']) + 1 : 1,
            'last_denied_at' => current_time('mysql', true),
            'last_reason' => sanitize_key($reason),
        );

        if (get_option(self::STATS_OPTION, null) === null) {
            add_option(self::STATS_OPTION, $stats, '', 'no');
        } else {
            update_option(self::STATS_OPTION, $stats, false);
        }

        if (class_exists('WPJOBPORTAL_Audit_Service')) {
            WPJOBPORTAL_Audit_Service::record(
                'request_route_denied',
                'warning',
                array(
                    'module' => sanitize_key($module),
                    'task' => sanitize_key($task),
                    'channel' => sanitize_key($channel),
                    'reason' => sanitize_key($reason),
                )
            );
        }
    }

    /**
     * Return support-safe registry diagnostics.
     *
     * @return array
     */
    public static function get_status() {
        $stats = get_option(self::STATS_OPTION, array());
        if (!is_array($stats)) {
            $stats = array();
        }

        $extension_routes = self::extension_routes();
        $unknown_addons = self::unknown_active_addons();
        $compatibility_products = self::compatibility_products();
        $active_addons = array_values(array_unique(array_map('sanitize_key', (array) wpjobportal::$_active_addons)));
        $supported_active = array_intersect($active_addons, self::supported_addons());

        return array(
            'mode' => self::strict_addon_mode()
                ? 'core_and_supported_addons_enforced'
                : 'core_enforced_unknown_addon_compatibility',
            'core_modules' => count(self::core_routes()),
            'core_routes' => self::count_routes(self::core_routes()),
            'extension_modules' => count($extension_routes),
            'extension_routes' => self::count_routes($extension_routes),
            'supported_addons' => count(self::supported_addons()),
            'supported_active_addons' => count($supported_active),
            'unknown_active_addons' => $unknown_addons,
            'compatibility_products' => $compatibility_products,
            'strict_addon_mode' => self::strict_addon_mode(),
            'denied' => isset($stats['denied']) ? absint($stats['denied']) : 0,
            'last_denied_at' => isset($stats['last_denied_at']) ? sanitize_text_field($stats['last_denied_at']) : '',
            'last_reason' => isset($stats['last_reason']) ? sanitize_key($stats['last_reason']) : '',
        );
    }

    /**
     * Return normalized add-on registrations.
     *
     * Expected shape:
     * array('addonmodule' => array('taskone', 'tasktwo'))
     *
     * @return array
     */
    private static function extension_routes() {
        $registered = apply_filters(
            'wpjobportal_route_registry',
            self::coordinated_addon_routes()
        );
        $normalized = array();

        if (!is_array($registered)) {
            return $normalized;
        }

        foreach ($registered as $module => $tasks) {
            $module = sanitize_key($module);
            if ($module === '' || !is_array($tasks)) {
                continue;
            }

            $normalized[$module] = array();
            foreach ($tasks as $task) {
                $task = strtolower(preg_replace('/[^A-Za-z0-9_]/', '', (string) $task));
                if ($task !== '') {
                    $normalized[$module][$task] = $task;
                }
            }
        }

        foreach ($normalized as $module => $tasks) {
            $normalized[$module] = array_values($tasks);
        }

        return $normalized;
    }

    /**
     * Return active add-on slugs that are not in the coordinated set.
     *
     * @return array
     */
    private static function unknown_active_addons() {
        $active = array_values(array_unique(array_map('sanitize_key', (array) wpjobportal::$_active_addons)));
        $unknown = array_values(array_diff($active, self::supported_addons(), self::first_party_products()));
        sort($unknown);
        return $unknown;
    }

    /**
     * Return active first-party product keys, which keep compatibility mode.
     *
     * @return array
     */
    private static function compatibility_products() {
        $active = array_values(array_unique(array_map('sanitize_key', (array) wpjobportal::$_active_addons)));
        $products = array_values(array_intersect($active, self::first_party_products()));
        sort($products);
        return $products;
    }

    /**
     * @return bool
     */
    private static function strict_addon_mode() {
        return empty(self::unknown_active_addons()) && empty(self::compatibility_products());
    }

    /**
     * @param array $routes Module route map.
     * @return int
     */
    private static function count_routes($routes) {
        $count = 0;
        foreach ((array) $routes as $tasks) {
            $count += is_array($tasks) ? count($tasks) : 0;
        }
        return $count;
    }
}
