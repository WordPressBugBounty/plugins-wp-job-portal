<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Job alerts, in the free core.
 *
 * The third of the three tier-split features, and the one that had to move
 * rather than be duplicated. A free tier that cannot send a job alert is not a
 * credible job board, so Core owns the whole implementation — routes, model,
 * cron and delivery — and Pro raises the limits instead of shipping a second
 * copy. One code path means the paid tier cannot drift from the free one.
 *
 * Free offers the weekly digest. Pro adds daily, monthly and instant delivery,
 * and unlocks the admin screens.
 *
 * The tables are adopted in place by migration 272; nothing is created on a
 * site that already has them.
 *
 * @see WPJOBPORTAL_Feature_Tiers
 */
final class WPJOBPORTAL_Feature_jobalert {

    const MODULE = 'jobalert';

    const SCHEDULED_ALERT_HOOK = 'wpjobportal_send_scheduled_job_alerts';
    const LEGACY_CRON_KEY = 'f1877c1756a68271d12db39ddc87dad7';

    /** Layouts served from this module's own tmpl folder. */
    private static $layouts = array('admin_formjobalert', 'admin_jobalert', 'jobalert');

    /** Sub-modules Core resolves through this module's folder. */
    private static $submodules = array('jobalertsetting', 'jobalertcities');

    public static function register() {
        $feature = new self();

        add_action('wpjobportal_addons_jobseeker_dashboard_bottom_btn', array($feature, 'wpjobportal_addons_list_job_jobalert'), 10, 2);
        add_action('wpjobportal_addons_jobseeker_dashboard_bottom_btn_jobalert', array($feature, 'wpjobportal_addons_list_job_jobalert'), 10, 2);
        add_filter('wpjobportal_credit_job_input_for_email_filter', array($feature, 'wpjobportal_addons_credit_email_filter_for_job'), 10, 3);
        add_action('wpjobportal_trigger_instant_alerts', array($feature, 'wpjobportal_process_instant_alerts_background'), 10, 1);

        // Admin screens are a paid capability. The route stays registered so a
        // direct link still resolves, but the menu entry only appears once a
        // suite has unlocked it - asked when the menu is drawn, not here: this
        // runs while Core's own file loads, before Pro has added its filter, so
        // checking here hid the links even with Pro installed.
        add_action('wpjobportal_addons_sidemenue_admin_jobalert', array($feature, 'admin_sidemenu'), 10, 2);

        // Delivery. The per-site secret replaces the hardcoded key the legacy
        // add-on shipped; it is generated in code because migrations here never
        // run UPDATE.
        //
        // On init, not here. register() runs while the plugin is being
        // constructed, which is before WordPress loads pluggable.php, so
        // wp_generate_password() does not exist yet. A site whose key had
        // already rotated returned before reaching it and never noticed; a
        // fresh install, or any site still on the legacy key, fataled on load.
        add_action('init', array(__CLASS__, 'wpjobportal_ensure_cron_secret'), 5);
        add_action(self::SCHEDULED_ALERT_HOOK, array($feature, 'wpjobportal_process_scheduled_alerts'));
        add_action('init', array($feature, 'wpjobportal_ensure_scheduled_alerts'));

        // Free-tier ceiling. Pro filters this upward.
        //
        // Both ends are covered on purpose: the form only offers what the tier
        // allows, and the save path re-checks, because a select element is not
        // a permission check.
        add_filter('wpjobportal_job_alert_frequency_options', array(__CLASS__, 'enforce_frequencies'));
        add_filter('wpjobportal_job_alert_saved_frequency', array(__CLASS__, 'enforce_saved_frequency'));
    }

    /**
     * Restrict the frequency choices offered on the alert form.
     *
     * Takes the combo list as the model builds it — objects carrying an id and
     * a label — and drops anything the tier does not include. If that would
     * leave nothing at all the original list is returned instead, since an
     * empty select would make the form unusable rather than merely limited.
     *
     * @param array $options Objects with ->id and ->text.
     * @return array
     */
    public static function enforce_frequencies($options) {
        if (!is_array($options) || empty($options)) {
            return $options;
        }

        $allowed = WPJOBPORTAL_Feature_Tiers::job_alert_frequencies();
        $filtered = array();
        foreach ($options as $key => $option) {
            $code = (is_object($option) && isset($option->id)) ? $option->id : $key;
            if (in_array((int) $code, $allowed, true)) {
                $filtered[] = $option;
            }
        }

        return $filtered ? $filtered : $options;
    }

    /**
     * Hold a submitted frequency to the tier.
     *
     * A value outside the tier is not treated as an error, because the
     * commonest way to reach one is a site that dropped from a paid tier to
     * the free one with alerts already saved. Coercing to the nearest allowed
     * frequency keeps those alerts delivering rather than failing to save.
     *
     * @param mixed $alerttype Submitted frequency code.
     * @return int
     */
    public static function enforce_saved_frequency($alerttype) {
        $allowed = WPJOBPORTAL_Feature_Tiers::job_alert_frequencies();
        $alerttype = (int) $alerttype;

        if (empty($allowed) || in_array($alerttype, $allowed, true)) {
            return $alerttype;
        }

        return in_array(WPJOBPORTAL_Feature_Tiers::ALERT_WEEKLY, $allowed, true)
            ? WPJOBPORTAL_Feature_Tiers::ALERT_WEEKLY
            : (int) reset($allowed);
    }

    /* ------------------------------------------------------------------ */
    /* Callbacks carried over unchanged from the legacy add-on             */
    /* ------------------------------------------------------------------ */

    public function wpjobportal_addons_list_job_jobalert($print, $elegantdesign = ''){
        $print = wpjobportal_jobseekercheckLinks('jobalertsetting');
        if ($print) {
            if ($elegantdesign == 1) {
                $url = wpjobportal_elegant_design_url();
            } else {
                $url = WPJOBPORTAL_PLUGIN_URL;
            }
            $job_alert_allowed = 1;
            if(WPJOBPORTALincluder::getObjectClass('user')->isguest()){ // check job alert overwrite configuration for guest
                $job_alert_allowed = wpjobportal::$_config->getConfigurationByConfigName('overwrite_jobalert_settings');
            }

            if($job_alert_allowed == 1){
                echo ' <div class="wjportal-cp-list">
                            <a class="wjportal-list-anchor" href='. esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'jobalert', 'wpjobportallt'=>'jobalert'))).'>'. wp_kses(wpjobportal_cp_menu_icon('bell'), WPJOBPORTAL_ALLOWED_TAGS) .'<span class="wjportal-cp-link-text">'. esc_html__('Job Alert', 'wp-job-portal').'</span></a>
                        </div>';
            }
        }
    }

    /** The admin menu entries, when a suite has unlocked the admin screens. */
    public function admin_sidemenu($c, $layout) {
        if (!WPJOBPORTAL_Feature_Tiers::job_alert_admin_tools()) {
            return;
        }
        $this->wpjobportal_addon_sidemenue($c, $layout);
    }

    public function wpjobportal_addon_sidemenue($c,$layout){
        $class = '';
        if($c == 'wpjobportal_jobalert' && ($layout == '')) $class = 'active';
        echo ' <li class="'.esc_attr($class).'">
                    <a href="admin.php?page=wpjobportal_jobalert" title="'.esc_attr__('Job Alert', 'wp-job-portal').'">
                        '.esc_html__('Job Alert', 'wp-job-portal').'
                    </a>
            </li>';
        $class = '';
        if($c == 'wpjobportal_jobalert' && ($layout == 'formjobalert')) $class = 'active';
        echo ' <li class="'.esc_attr($class).'">
                    <a href="admin.php?page=wpjobportal_jobalert&wpjobportallt=formjobalert" title="'.esc_attr__('Add New Job Alert', 'wp-job-portal').'">
                        '.esc_html__('Add New Job Alert', 'wp-job-portal').'
                    </a>
            </li>';

        // --- NEW FIELDS MENU ITEM ---
        $class = '';
        // Check if controller is fieldordering and ff=6 (Job Alert Fields) to set active state
        $ff = absint(WPJOBPORTALrequest::getVar('ff', 'get', 0));
        if(($c == 'wpjobportal_fieldordering' || $c == 'wpjobportal_customfield') && ($layout == '' || $layout == 'formuserfield') && $ff === 6) $class = 'active';

        echo ' <li class="'.esc_attr($class).'">
                    <a href="admin.php?page=wpjobportal_fieldordering&ff=6" title="'.esc_attr(__('Fields', 'wp-job-portal')).'">
                        '.esc_html(__('Job Alert Fields', 'wp-job-portal')).'
                    </a>
            </li>';

    }

    public function wpjobportal_addons_credit_email_filter_for_job($default_val,$job,$field){
        //Load Template For Email Filter
            $html = '';
            $req = '';
            if (isset(wpjobportal::$_data[0]->sendemail)) {
                $selected = wpjobportal::$_data[0]->sendemail;
            }else{
                $selected = 1; // making sure deault value is not set "do not send email"
            }
            if ($field->required == 1) {
                    $req = 'required';
                }
           $html = ' <div class="wjportal-form-email-field-wrp"> <div class="wjportal-form-email-field">'.WPJOBPORTALformfield::radiobutton('sendemail', array('0' => __('Do not email me', 'wp-job-portal')), $selected, array('class' => 'radiobutton', 'data-validation' => $req)).'</div>';
            $html .= ' <div class="wjportal-form-email-field-txt">'. __('No job applied notification will be emailed to you','wp-job-portal') .', '. __('you can only check your workspace for new applicants', 'wp-job-portal').'</div> </div>';
           $html .= ' <div class="wjportal-form-email-field-wrp"> <div class="wjportal-form-email-field">'.WPJOBPORTALformfield::radiobutton('sendemail', array('1' => __('Email me resume of new applicants', 'wp-job-portal')), $selected, array('class' => 'radiobutton', 'data-validation' => $req)).'</div>';
           $html .= ' <div class="wjportal-form-email-field-txt">'. __('Job applied notification will be emailed to you with resume of the applicant','wp-job-portal') .', '. __('you can also check your workspace', 'wp-job-portal').'</div> </div>';
            $html .= '<div class="wjportal-form-email-field-wrp"> <div class="wjportal-form-email-field">'. WPJOBPORTALformfield::radiobutton('sendemail', array('2' => __('Email me resume and resume attachments of new applicant', 'wp-job-portal')), $selected, array('class' => 'radiobutton', 'data-validation' => $req)).'</div>';
            $html .= ' <div class="wjportal-form-email-field-txt">'. __('Job applied notification will be emailed to you with resume and attachments of the applicant','wp-job-portal') .', '. __('you can also check your workspace', 'wp-job-portal').'</div> </div>';
            return $html;
    }

    public function wpjobportal_process_instant_alerts_background($job_id) {
        if (!is_numeric($job_id)) return;

        // model function for instant job alert
        WPJOBPORTALincluder::getJSModel('jobalert')->sendInstantAlertsBackground($job_id);
        return;
    }

    /**
     * A 64-character secret, without depending on when this is called.
     *
     * wp_generate_password() lives in pluggable.php, which WordPress loads
     * after plugin files are included. Anything reaching for it during plugin
     * construction takes the site down instead of getting a password, so this
     * falls back to the same randomness WordPress itself uses underneath.
     *
     * @return string Empty when no source of randomness is available.
     */
    private static function wpjobportal_random_secret() {
        if (function_exists('wp_generate_password')) {
            return wp_generate_password(64, false, false);
        }

        if (function_exists('random_bytes')) {
            try {
                return substr(bin2hex(random_bytes(32)), 0, 64);
            } catch (Exception $e) {
                return '';
            }
        }

        return '';
    }

    public static function wpjobportal_ensure_cron_secret() {
        if (!class_exists('wpjobportal')) {
            return false;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'wj_portal_config';
        $key = wpjobportaldb_direct::get_var($wpdb->prepare(
            "SELECT configvalue FROM %i WHERE configname = %s LIMIT 1",
            $table,
            'cron_job_alert_key'
        ));

        if (!empty($key) && !hash_equals(self::LEGACY_CRON_KEY, (string) $key)) {
            return true;
        }

        $secret = self::wpjobportal_random_secret();
        if (empty($secret)) {
            return false;
        }

        $updated = wpjobportaldb_direct::query($wpdb->prepare(
            "UPDATE %i SET configvalue = %s WHERE configname = %s AND (configvalue = %s OR configvalue = '')",
            $table,
            $secret,
            'cron_job_alert_key',
            self::LEGACY_CRON_KEY
        ));

        return false !== $updated;
    }

    public function wpjobportal_process_scheduled_alerts() {
        WPJOBPORTALincluder::getJSModel('jobalert')->sendAllJobAlerts();
    }

    public function wpjobportal_ensure_scheduled_alerts() {
        self::wpjobportal_schedule_alerts();
    }

    public static function wpjobportal_schedule_alerts() {
        if (!wp_next_scheduled(self::SCHEDULED_ALERT_HOOK)) {
            wp_schedule_event(time() + wp_rand(60, 300), 'daily', self::SCHEDULED_ALERT_HOOK);
        }
    }
}
