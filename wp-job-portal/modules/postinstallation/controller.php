<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALpostinstallationController {

    function __construct() {

        self::handleRequest();
    }

    function handleRequest() {
        // A clean WordPress menu slug is required for capability checks. When the
        // Setup & Mode menu is opened without a layout query argument, start on
        // the mode chooser rather than bypassing it and entering step one.
        $wpjobportal_layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'quickstart');
        // The Employer and Job Seeker settings steps were folded into
        // Essentials; an old link or bookmark to either opens Essentials.
        if (in_array($wpjobportal_layout, array('admin_steptwo', 'admin_stepthree'), true)) {
            $wpjobportal_layout = 'admin_stepone';
        }
        if($this->canaddfile($wpjobportal_layout)){
            switch ($wpjobportal_layout) {
                case 'admin_stepone':
                    WPJOBPORTALincluder::getJSModel('postinstallation')->getConfigurationValues();
					WPJOBPORTALincluder::getJSModel('postinstallation')->addMissingUsers();
                break;
                case 'admin_steptwo':
                    WPJOBPORTALincluder::getJSModel('postinstallation')->getConfigurationValues();
                break;
                case 'admin_stepthree':
                    WPJOBPORTALincluder::getJSModel('postinstallation')->getConfigurationValues();
                break;
                case 'admin_themedemodata':
                    wpjobportal::$_data['flag'] = WPJOBPORTALrequest::getVar('flag');
                break;
                case 'admin_demoimporter':
                    WPJOBPORTALincluder::getJSModel('postinstallation')->getListOfDemoVersions();
                break;
                case 'admin_quickstart':
                    wpjobportal::$_data['onboarding'] = WPJOBPORTAL_Onboarding_Service::get_state();
                    wpjobportal::$_data['sample_cleanup_preview'] = WPJOBPORTAL_Sample_Data_Service::get_preview();
                    $wpjobportal_cleanup_result_key = 'wpjobportal_sample_cleanup_result_' . get_current_user_id();
                    wpjobportal::$_data['sample_cleanup_result'] = get_transient($wpjobportal_cleanup_result_key);
                    if (false !== wpjobportal::$_data['sample_cleanup_result']) {
                        delete_transient($wpjobportal_cleanup_result_key);
                    }
                    wpjobportal::$_data['sample_owner_notice'] = $this->takeSampleOwnerNotice();
                break;
                case 'admin_stepfour':
                    wpjobportal::$_data['sample_owner_notice'] = $this->takeSampleOwnerNotice();
                break;
                case 'admin_setupcomplete':
                break;
                case 'admin_modeguide':
                    wpjobportal::$_data['mode_status'] = WPJOBPORTAL_Product_Mode_Service::get_status();
                    wpjobportal::$_data['mode_matrix'] = WPJOBPORTAL_Mode_Policy_Service::get_matrix();
                break;

                default:
                    return;
            }
            $wpjobportal_module = (wpjobportal::$_common->wpjp_isadmin()) ? 'page' : 'wpjobportalme';
            $wpjobportal_module = WPJOBPORTALrequest::getVar($wpjobportal_module, null, 'postinstallation');
            $wpjobportal_module = wpjobportalphplib::wpJP_str_replace('wpjobportal_', '', $wpjobportal_module);
            wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(wp_create_nonce('wpjobportal_nonce'));
            WPJOBPORTALincluder::include_file($wpjobportal_layout, $wpjobportal_module);
        }

    }
    function canaddfile($wpjobportal_layout) {
        if (!is_admin() || (!current_user_can('wpjobportal') && !current_user_can('manage_options'))) {
            return false;
        }

        if (WPJOBPORTALrequest::getVar('form_request', 'post', '') === 'wpjobportal') {
            return false;
        }

        if (WPJOBPORTALrequest::getVar('action', 'get', '') === 'wpjobportaltask') {
            return false;
        }

        $wpjobportal_allowed_layouts = array(
            'admin_quickstart',
            'admin_stepone',
            'admin_steptwo',
            'admin_stepthree',
            'admin_stepfour',
            'admin_setupcomplete',
            'admin_themedemodata',
            'admin_demoimporter',
            'admin_modeguide',
        );

        return in_array($wpjobportal_layout, $wpjobportal_allowed_layouts, true);
    }

    function save(){
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_postinstallation_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_data = WPJOBPORTALrequest::get('post');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('postinstallation')->storeconfigurations($wpjobportal_data);
        $wpjobportal_is_mode_stage = isset($wpjobportal_data['setup_stage'])
            && sanitize_key($wpjobportal_data['setup_stage']) === 'mode';

        if(WPJOBPORTAL_SAVED !== $wpjobportal_result){
            $wpjobportal_failed_layouts = array(
                0 => 'quickstart',
                1 => 'stepone',
                2 => 'steptwo',
                3 => 'stepthree',
            );
            $wpjobportal_failed_step = $wpjobportal_is_mode_stage
                ? 0
                : (isset($wpjobportal_data['step']) ? absint($wpjobportal_data['step']) : 0);
            $wpjobportal_failed_layout = isset($wpjobportal_failed_layouts[$wpjobportal_failed_step])
                ? $wpjobportal_failed_layouts[$wpjobportal_failed_step]
                : 'quickstart';
            $wpjobportal_error_url = add_query_arg(
                array(
                    'page' => 'wpjobportal_postinstallation',
                    'wpjobportallt' => $wpjobportal_failed_layout,
                    'wpjobportal_setup_error' => 1,
                ),
                admin_url('admin.php')
            );
            wp_safe_redirect($wpjobportal_error_url);
            exit();
        }

        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_postinstallation&wpjobportallt=stepone"));
        if($wpjobportal_is_mode_stage){
            $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_postinstallation&wpjobportallt=stepone"));
        }elseif($wpjobportal_data['step'] == 1){
            // Essentials is the only settings step now; sample data is next.
            $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_postinstallation&wpjobportallt=stepfour"));
        }
        if(!$wpjobportal_is_mode_stage && $wpjobportal_data['step'] == 2){
            $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_postinstallation&wpjobportallt=stepthree"));
        }
        if(!$wpjobportal_is_mode_stage && $wpjobportal_data['step'] == 3){
            $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_postinstallation&wpjobportallt=stepfour"));
        }
        wp_safe_redirect($wpjobportal_url);
        exit();
    }

    function savesampledata(){
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_postinstallation_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_data = WPJOBPORTALrequest::get('post');
        $wpjobportal_sampledata = isset($wpjobportal_data['sampledata']) ? absint($wpjobportal_data['sampledata']) : 0;
        $wpjobportal_temp_data = 0;
        $wpjobportal_jsmenu = 0;
        $wpjobportal_empmenu = 0;
	   $wpjobportal_job_listing_menu=0;
        if(isset($wpjobportal_data['temp_data'])){
            $wpjobportal_temp_data = absint($wpjobportal_data['temp_data']);
        }
        // notice for undeined variable
        if(isset($wpjobportal_data['jsmenu'])){
            $wpjobportal_jsmenu = absint($wpjobportal_data['jsmenu']);
        }
        if(isset($wpjobportal_data['empmenu'])){
            $wpjobportal_empmenu = absint($wpjobportal_data['empmenu']);
        }
        if(isset($wpjobportal_data['job_listing_menu'])){
            $wpjobportal_job_listing_menu = absint($wpjobportal_data['job_listing_menu']);
        }

        // Sample data needs an owner for each role. Records nobody owns are
        // refused when an employer saves a job against them and never appear
        // in My Jobs, so importing without owners would ship that fault. The
        // screen requires both; this is the same rule for a request that
        // did not come from it. Nothing is written until both are sound.
        if ($wpjobportal_sampledata == 1) {
            $wpjobportal_employer_owner = isset($wpjobportal_data['employer_id']) ? absint($wpjobportal_data['employer_id']) : 0;
            $wpjobportal_jobseeker_owner = isset($wpjobportal_data['jobseeker_id']) ? absint($wpjobportal_data['jobseeker_id']) : 0;
            $wpjobportal_owner_check = ($wpjobportal_employer_owner === 0 || $wpjobportal_jobseeker_owner === 0)
                ? new WP_Error('no_owner', __('Choose who owns the sample data: an employer and a job seeker. Add a user first if you need one, or choose No to skip the sample data.', 'wp-job-portal'))
                : WPJOBPORTAL_Sample_Data_Service::validate_owners($wpjobportal_employer_owner, $wpjobportal_jobseeker_owner);
            if (is_wp_error($wpjobportal_owner_check)) {
                $this->putSampleOwnerNotice('error', $wpjobportal_owner_check->get_error_message());
                wp_safe_redirect(admin_url('admin.php?page=wpjobportal_postinstallation&wpjobportallt=stepfour'));
                exit();
            }
        }

        if(wpjobportal::$wpjobportal_theme_chk == 1){
            update_option( 'wpjobportal_jobs_sample_data', 1 ); // flag to messge that jobs data has been inserted.
            $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_postinstallation&wpjobportallt=demoimporter"));
        }else{
            //$wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal"));
            $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_postinstallation&wpjobportallt=setupcomplete"));
        }
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('postinstallation')->installSampleData($wpjobportal_sampledata,$wpjobportal_jsmenu,$wpjobportal_empmenu,$wpjobportal_temp_data, $wpjobportal_job_listing_menu);
        wp_safe_redirect($wpjobportal_url);
        exit();
    }

    function savetemplatesampledata(){
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_postinstallation_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $flag = WPJOBPORTALrequest::getVar('flag');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('postinstallation')->installSampleDataTemplate($flag);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal_postinstallation&wpjobportallt=themedemodata&flag=".esc_url($wpjobportal_result)));
        wp_safe_redirect($wpjobportal_url);
        exit();
    }

    /*
     * importtemplatesampledata() used to sit here and has been removed, with
     * its route and its action-policy entry.
     *
     * It was a near-copy of savetemplatesampledata() above - same nonce, same
     * admin check, same redirect - differing only in accepting the 'f' flag
     * alone and in calling importTemplateSampleData(), which this model does
     * not define. The model has installSampleDataTemplate(), which is what
     * savetemplatesampledata() calls and what the Theme Demo Data screen's
     * three buttons actually invoke.
     *
     * So the working task was next door the whole time and nothing linked to
     * this one; it survived only because the route registry and the action
     * policy still advertised it, which made it dispatchable to anyone holding
     * a valid nonce. Importing demo data is unaffected.
     */

    function getdemocode(){
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $wpjobportal_nonce, 'wpjobportal_postinstallation_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $wpjobportal_demoid = WPJOBPORTALrequest::getVar('demoid');
        $foldername = WPJOBPORTALrequest::getVar('foldername');
        $wpjobportal_demo_overwrite = WPJOBPORTALrequest::getVar('demo_overwrite');
        $wpjobportal_result = WPJOBPORTALincluder::getJSModel('postinstallation')->getDemo($wpjobportal_demoid,$foldername,$wpjobportal_demo_overwrite);
        $wpjobportal_url = esc_url_raw(admin_url("admin.php?page=wpjobportal"));
        wp_safe_redirect($wpjobportal_url);
        exit();
    }

    function dismissonboarding(){
        $this->authorizeOnboardingAction();
        WPJOBPORTAL_Onboarding_Service::dismiss(get_current_user_id());
        $this->redirectToQuickStart();
    }

    function reopenonboarding(){
        $this->authorizeOnboardingAction();
        WPJOBPORTAL_Onboarding_Service::reopen(get_current_user_id());
        $this->redirectToQuickStart();
    }

    function cleansampledata(){
        $this->authorizeOnboardingAction(true);
        $confirmation = sanitize_key((string) WPJOBPORTALrequest::getVar('sample_cleanup_confirmation'));
        if ('cleanup_marked_sample_data' !== $confirmation) {
            wp_die(esc_html__('Sample-data cleanup requires explicit confirmation.', 'wp-job-portal'), '', array('response' => 403));
        }

        $result = WPJOBPORTAL_Sample_Data_Service::cleanup();
        set_transient(
            'wpjobportal_sample_cleanup_result_' . get_current_user_id(),
            $result,
            5 * MINUTE_IN_SECONDS
        );
        $this->redirectToQuickStart();
    }

    /**
     * Give the sample data to a different employer or job seeker.
     */
    function assignsampleowner(){
        $this->authorizeOnboardingAction(true);
        $wpjobportal_employer_owner = absint(WPJOBPORTALrequest::getVar('sample_employer_owner'));
        $wpjobportal_jobseeker_owner = absint(WPJOBPORTALrequest::getVar('sample_jobseeker_owner'));

        if ($wpjobportal_employer_owner === 0 && $wpjobportal_jobseeker_owner === 0) {
            $this->putSampleOwnerNotice('error', __('Choose a user for at least one role.', 'wp-job-portal'));
            $this->redirectToQuickStart();
        }

        $wpjobportal_check = WPJOBPORTAL_Sample_Data_Service::validate_owners($wpjobportal_employer_owner, $wpjobportal_jobseeker_owner);
        if (!is_wp_error($wpjobportal_check)) {
            // Checked against the owner already in place for the role that
            // is not changing, so one account cannot end up holding both.
            $wpjobportal_other = $wpjobportal_employer_owner > 0
                ? WPJOBPORTAL_Sample_Data_Service::current_owner(WPJOBPORTAL_Sample_Data_Service::ROLE_JOBSEEKER)
                : WPJOBPORTAL_Sample_Data_Service::current_owner(WPJOBPORTAL_Sample_Data_Service::ROLE_EMPLOYER);
            $wpjobportal_changing = $wpjobportal_employer_owner > 0 ? $wpjobportal_employer_owner : $wpjobportal_jobseeker_owner;
            if ($wpjobportal_employer_owner > 0 && $wpjobportal_jobseeker_owner > 0) {
                $wpjobportal_other = 0;
            }
            if ($wpjobportal_other > 0 && $wpjobportal_other === $wpjobportal_changing) {
                $wpjobportal_check = new WP_Error('same_user', __('Choose different users for the employer and the job seeker. One account cannot be both.', 'wp-job-portal'));
            }
        }
        if (is_wp_error($wpjobportal_check)) {
            $this->putSampleOwnerNotice('error', $wpjobportal_check->get_error_message());
            $this->redirectToQuickStart();
        }

        $wpjobportal_moved = 0;
        $wpjobportal_kept = 0;
        foreach (array(
            WPJOBPORTAL_Sample_Data_Service::ROLE_EMPLOYER => $wpjobportal_employer_owner,
            WPJOBPORTAL_Sample_Data_Service::ROLE_JOBSEEKER => $wpjobportal_jobseeker_owner,
        ) as $wpjobportal_role => $wpjobportal_owner) {
            if ($wpjobportal_owner === 0) {
                continue;
            }
            $wpjobportal_result = WPJOBPORTAL_Sample_Data_Service::assign_owner($wpjobportal_role, $wpjobportal_owner);
            if (is_wp_error($wpjobportal_result)) {
                $this->putSampleOwnerNotice('error', $wpjobportal_result->get_error_message());
                $this->redirectToQuickStart();
            }
            $wpjobportal_moved += $wpjobportal_result['moved'];
            $wpjobportal_kept += $wpjobportal_result['kept'];
        }

        $wpjobportal_text = sprintf(
            /* translators: %d: number of sample records. */
            _n('%d sample record now has its new owner.', '%d sample records now have their new owner.', $wpjobportal_moved, 'wp-job-portal'),
            $wpjobportal_moved
        );
        if ($wpjobportal_kept > 0) {
            $wpjobportal_text .= ' ' . sprintf(
                /* translators: %d: number of sample records. */
                _n('%d you have edited kept its owner.', '%d you have edited kept their owner.', $wpjobportal_kept, 'wp-job-portal'),
                $wpjobportal_kept
            );
        }
        $this->putSampleOwnerNotice('success', $wpjobportal_text);
        $this->redirectToQuickStart();
    }

    private function putSampleOwnerNotice($type, $text){
        set_transient('wpjobportal_sample_owner_notice_' . get_current_user_id(), array('type' => $type, 'text' => $text), 5 * MINUTE_IN_SECONDS);
    }

    private function takeSampleOwnerNotice(){
        $key = 'wpjobportal_sample_owner_notice_' . get_current_user_id();
        $notice = get_transient($key);
        if (false !== $notice) {
            delete_transient($key);
        }
        return is_array($notice) ? $notice : null;
    }

    private function authorizeOnboardingAction($administrator_only = false){
        $method = isset($_SERVER['REQUEST_METHOD'])
            ? strtoupper(sanitize_text_field(wp_unslash($_SERVER['REQUEST_METHOD'])))
            : '';
        if ('POST' !== $method) {
            wp_die(esc_html__('This action requires a POST request.', 'wp-job-portal'), '', array('response' => 405));
        }
        $has_capability = $administrator_only
            ? current_user_can('manage_options')
            : (current_user_can('wpjobportal') || current_user_can('manage_options'));
        if (!is_admin() || !$has_capability) {
            wp_die(esc_html__('You are not allowed to manage WP Job Portal onboarding.', 'wp-job-portal'), '', array('response' => 403));
        }
        $wpjobportal_nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (!is_string($wpjobportal_nonce) || !wp_verify_nonce($wpjobportal_nonce, 'wpjobportal_postinstallation_nonce')) {
            wp_die(esc_html__('Security check failed.', 'wp-job-portal'), '', array('response' => 403));
        }
    }

    private function redirectToQuickStart(){
        wp_safe_redirect(admin_url('admin.php?page=wpjobportal_postinstallation&wpjobportallt=quickstart'));
        exit();
    }
}
$WPJOBPORTALpostinstallationController = new WPJOBPORTALpostinstallationController();
?>
