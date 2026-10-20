<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALformhandler {

    function __construct() {
        add_action('init', array($this, 'checkFormRequest'));
        add_action('init', array($this, 'checkDeleteRequest'));
    }

    /*
     * Handle Form request
     */

    function checkFormRequest() {
        $wpjobportal_formrequest = WPJOBPORTALrequest::getVar('form_request', 'post');
        if ($wpjobportal_formrequest == 'wpjobportal') {
            $this->dispatch('form');
        }
    }

    /*
     * Handle Form request
     */

    function checkDeleteRequest() {
        $wpjobportal_action = WPJOBPORTALrequest::getVar('action', 'get');
        if ($wpjobportal_action == 'wpjobportaltask') {
            $this->dispatch('task');
        }
    }

    /**
     * Dispatch one explicitly registered controller action.
     *
     * Existing controllers retain their nonce, capability and object-ownership
     * checks. This boundary prevents arbitrary public controller methods from
     * becoming request routes merely because they have no required arguments.
     *
     * @param string $wpjobportal_channel form or task.
     * @return void
     */
    /**
     * Refuse a request, and say which check refused it.
     *
     * Five different things end here - the wrong HTTP method, an account
     * without the role, an expired form, a value that is not the shape it
     * should be, and a record belonging to somebody else - and all five used
     * to produce one sentence: "You are not allowed to perform this WP Job
     * Portal action." A person who sees it cannot tell which, and neither can
     * the support desk they send it to. Each reason gets its own sentence,
     * and an administrator also gets the route and the machine-readable
     * reason, which is what a bug report needs.
     *
     * @param string $reason  Machine reason from the policy service.
     * @param string $module  Route module.
     * @param string $task    Route task.
     * @param bool   $addon   Whether an add-on policy refused it.
     * @return void
     */
    private function refuse($reason, $module, $task, $addon = false) {
        $reasons = array(
            'http_method' => __('This action cannot be reached the way it was requested.', 'wp-job-portal'),
            'capability' => __('Your account is not allowed to do this. If you should be, ask the site administrator to check your role in WP Job Portal.', 'wp-job-portal'),
            'nonce' => __('This form has expired. Go back, reload the page, and send it again.', 'wp-job-portal'),
            'input_schema' => __('Something in the form was not in the expected format, so it was not saved.', 'wp-job-portal'),
            'object_ownership' => __('That record belongs to another account.', 'wp-job-portal'),
        );

        $reason = sanitize_key($reason);
        $message = isset($reasons[$reason])
            ? $reasons[$reason]
            : ($addon
                ? __('You are not allowed to perform this WP Job Portal add-on action.', 'wp-job-portal')
                : __('You are not allowed to perform this WP Job Portal action.', 'wp-job-portal'));

        $detail = '';
        if (current_user_can('manage_options')) {
            $detail = sprintf(
                /* translators: 1: route, 2: machine-readable reason. */
                __(' (%1$s - refused by: %2$s)', 'wp-job-portal'),
                sanitize_key($module) . '.' . sanitize_key($task),
                $reason !== '' ? $reason : 'unspecified'
            );
        }

        wp_die(
            esc_html($message . $detail),
            esc_html__('Access denied', 'wp-job-portal'),
            array('response' => 403)
        );
    }

    private function dispatch($wpjobportal_channel) {
        $wpjobportal_modulename = is_admin() ? 'page' : 'wpjobportalme';
        $wpjobportal_module = WPJOBPORTALrequest::getVar($wpjobportal_modulename);
        $wpjobportal_module = sanitize_key(
            wpjobportalphplib::wpJP_str_replace('wpjobportal_', '', (string) $wpjobportal_module)
        );
        $wpjobportal_task = preg_replace(
            '/[^A-Za-z0-9_]/',
            '',
            (string) WPJOBPORTALrequest::getVar('task')
        );

        if ($wpjobportal_module === '' || $wpjobportal_task === '') {
            return;
        }

        if (!WPJOBPORTAL_Route_Registry_Service::is_allowed(
            $wpjobportal_module,
            $wpjobportal_task,
            $wpjobportal_channel
        )) {
            wp_die(
                esc_html__('This WP Job Portal action is not registered.', 'wp-job-portal'),
                esc_html__('Invalid request', 'wp-job-portal'),
                array('response' => 403)
            );
        }

        if (!WPJOBPORTAL_Core_Action_Policy_Service::authorize(
            $wpjobportal_module,
            $wpjobportal_task,
            $wpjobportal_channel
        )) {
            $this->refuse(
                WPJOBPORTAL_Core_Action_Policy_Service::last_denial_reason(),
                $wpjobportal_module,
                $wpjobportal_task
            );
        }

        if (!WPJOBPORTAL_Addon_Action_Policy_Service::authorize(
            $wpjobportal_module,
            $wpjobportal_task,
            $wpjobportal_channel
        )) {
            $this->refuse(
                WPJOBPORTAL_Addon_Action_Policy_Service::last_denial_reason(),
                $wpjobportal_module,
                $wpjobportal_task,
                true
            );
        }

        wpjobportal::$_data['sanitized_args']['wpjobportal_nonce'] = esc_html(
            wp_create_nonce('wpjobportal_nonce')
        );
        WPJOBPORTALincluder::include_file($wpjobportal_module);

        $wpjobportal_class = 'WPJOBPORTAL' . $wpjobportal_module . 'Controller';
        if (!class_exists($wpjobportal_class)) {
            WPJOBPORTAL_Route_Registry_Service::record_denial(
                $wpjobportal_module,
                $wpjobportal_task,
                $wpjobportal_channel,
                'controller_missing'
            );
            return;
        }

        $wpjobportal_object = new $wpjobportal_class();
        if (!method_exists($wpjobportal_object, $wpjobportal_task)
            || !is_callable(array($wpjobportal_object, $wpjobportal_task))
        ) {
            WPJOBPORTAL_Route_Registry_Service::record_denial(
                $wpjobportal_module,
                $wpjobportal_task,
                $wpjobportal_channel,
                'method_unavailable'
            );
            return;
        }

        $wpjobportal_reflection = new ReflectionMethod($wpjobportal_object, $wpjobportal_task);
        if (!$wpjobportal_reflection->isPublic()
            || $wpjobportal_reflection->isStatic()
            || $wpjobportal_reflection->getNumberOfRequiredParameters() > 0
        ) {
            WPJOBPORTAL_Route_Registry_Service::record_denial(
                $wpjobportal_module,
                $wpjobportal_task,
                $wpjobportal_channel,
                'method_signature'
            );
            wp_die(esc_html__('Invalid request.', 'wp-job-portal'));
        }

        $wpjobportal_object->$wpjobportal_task();
    }
}

$wpjobportal_formhandler = new WPJOBPORTALformhandler();
?>
