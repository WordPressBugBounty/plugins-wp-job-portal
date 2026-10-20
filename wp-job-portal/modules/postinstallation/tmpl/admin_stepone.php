<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/*
 * Setup, step 2 of 4: Essentials.
 *
 * Only what a new job board cannot work without, and nothing it already has a
 * good default for. This step used to be "General Settings" followed by
 * "Employer Settings" and "Job Seeker Settings" - 25 settings in all, from the
 * data directory to the date format - which a new administrator answered
 * before seeing a single job on the site. Every one of them is still under
 * Configurations, and the mode chosen on the previous step has already set
 * them sensibly.
 *
 * What is asked here: who can sign up, where notifications go, and whether
 * new listings go live straight away. Approval questions the chosen mode has
 * already answered (a careers site publishes its own jobs) are not asked.
 */
$wpjobportal_yesno = array(
    (object) array('id' => 1, 'text' => esc_html(__('Yes', 'wp-job-portal'))),
    (object) array('id' => 0, 'text' => esc_html(__('No', 'wp-job-portal'))),
);
$wpjobportal_config_value = function ($wpjobportal_name, $wpjobportal_default = '') {
    $wpjobportal_value = wpjobportal::$_config->getConfigurationByConfigName($wpjobportal_name);
    return $wpjobportal_value === null ? $wpjobportal_default : $wpjobportal_value;
};
$wpjobportal_is_free = function ($wpjobportal_name) {
    return !class_exists('WPJOBPORTAL_Mode_Policy_Service') || empty(WPJOBPORTAL_Mode_Policy_Service::get_setting_lock($wpjobportal_name));
};

// Suggests Open while sign-up is not open yet; the administrator's choice is
// what is saved. See WPJOBPORTAL_Registration_Policy.
$wpjobportal_signup = WPJOBPORTAL_Registration_Policy::setting();
if ($wpjobportal_signup === WPJOBPORTAL_Registration_Policy::FOLLOW_WORDPRESS && !get_option('users_can_register')) {
    $wpjobportal_signup = '1';
}

$wpjobportal_approvals = array(
    'companyautoapprove' => array(__('Publish New Companies', 'wp-job-portal'), __('Yes: a company an employer adds is live at once. No: you approve it first, under Companies.', 'wp-job-portal')),
    'jobautoapprove' => array(__('Publish New Jobs', 'wp-job-portal'), __('Yes: a job is live as soon as it is posted. No: you approve each one first, under Jobs.', 'wp-job-portal')),
    'empautoapprove' => array(__('Publish New Resumes', 'wp-job-portal'), __('Yes: a job seeker\'s resume is visible at once. No: you approve it first, under Resume.', 'wp-job-portal')),
);
?>
<div id="wpjobportaladmin-wrapper" class="wpjobportal-post-installation-wrp">
    <div class="wpjobportal-post-installation">
        <div class="wpjobportal-post-menu">
            <div class="wpjobportal-post-installation-logowrp">
                <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/postinstallation/quickstrt_logo.png" alt="<?php echo esc_attr__('WP Job Portal', 'wp-job-portal'); ?>" />
            </div>
            <?php $wpjobportal_setup_active = 'essentials'; include __DIR__ . '/setup-steps.php'; ?>
        </div>
        <div class="wpjobportal-post-data">
            <div class="wpjobportal-post-heading-wrp">
                <div class="wpjobportal-post-heading">
                    <?php echo esc_html(__('Essentials','wp-job-portal'));?>
                </div>
                <div class="wpjobportal-post-head-rightbtns-wrp">
                    <span class="wpjobportal-post-head-pagestep"><?php echo esc_html(__('Step 2 of 4','wp-job-portal')); ?></span>
                    <a class="wpjobportal-post-head-closebtn" href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal')); ?>" title="<?php echo esc_attr(__('Close','wp-job-portal'));?>">
                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/postinstallation/close.png" alt="<?php echo esc_attr__('Close', 'wp-job-portal'); ?>" />
                    </a>
                </div>
            </div>
            <form id="wpjobportal-form-ins" class="wpjobportal-form" method="post" action="<?php echo esc_url_raw(admin_url("admin.php?page=wpjobportal_postinstallation&task=save&action=wpjobportaltask")); ?>">
                <p class="wpjobportal-post-intro">
                    <?php echo esc_html(__('A few things your job board needs to know. Everything else already has a working default.','wp-job-portal')); ?>
                </p>

                <div class="wpjobportal-post-data-row">
                    <div class="wpjobportal-post-tit">
                        <?php echo esc_html(__('Account Sign-Up','wp-job-portal')); ?>
                    </div>
                    <div class="wpjobportal-post-val">
                        <?php echo wp_kses(WPJOBPORTALformfield::select('account_signup', WPJOBPORTAL_Registration_Policy::options(), $wpjobportal_signup, '', array('class' => 'inputbox')),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <div class="wpjobportal-post-help-text">
                            <?php echo esc_html(__('Lets employers and job seekers create their own accounts on your registration page. This is WP Job Portal\'s own setting; WordPress\'s "Anyone can register" is not changed.','wp-job-portal'));?>
                        </div>
                    </div>
                </div>

                <div class="wpjobportal-post-data-row">
                    <div class="wpjobportal-post-tit">
                        <?php echo esc_html(__('Send Notifications To','wp-job-portal')); ?>
                    </div>
                    <div class="wpjobportal-post-val">
                        <?php echo wp_kses(WPJOBPORTALformfield::text('adminemailaddress', $wpjobportal_config_value('adminemailaddress', get_option('admin_email')), array('class' => 'inputbox')),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        <div class="wpjobportal-post-help-text">
                            <?php echo esc_html(__('New registrations, jobs, companies and applications are reported to this address.','wp-job-portal'));?>
                        </div>
                    </div>
                </div>

                <?php foreach ($wpjobportal_approvals as $wpjobportal_approval => $wpjobportal_approval_text) :
                    if (!$wpjobportal_is_free($wpjobportal_approval)) { continue; } ?>
                    <div class="wpjobportal-post-data-row">
                        <div class="wpjobportal-post-tit">
                            <?php echo esc_html($wpjobportal_approval_text[0]); ?>
                        </div>
                        <div class="wpjobportal-post-val">
                            <?php echo wp_kses(WPJOBPORTALformfield::select($wpjobportal_approval, $wpjobportal_yesno, $wpjobportal_config_value($wpjobportal_approval, 1), '', array('class' => 'inputbox')),WPJOBPORTAL_ALLOWED_TAGS); ?>
                            <div class="wpjobportal-post-help-text">
                                <?php echo esc_html($wpjobportal_approval_text[1]); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if ($wpjobportal_is_free('visitor_can_apply_to_job')) : ?>
                    <div class="wpjobportal-post-data-row">
                        <div class="wpjobportal-post-tit">
                            <?php echo esc_html(__('Visitors Can Apply','wp-job-portal')); ?>
                        </div>
                        <div class="wpjobportal-post-val">
                            <?php echo wp_kses(WPJOBPORTALformfield::select('visitor_can_apply_to_job', $wpjobportal_yesno, $wpjobportal_config_value('visitor_can_apply_to_job', 1), '', array('class' => 'inputbox')),WPJOBPORTAL_ALLOWED_TAGS); ?>
                            <div class="wpjobportal-post-help-text">
                                <?php echo esc_html(__('Lets someone apply to a job without creating an account first.','wp-job-portal'));?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <p class="wpjobportal-post-more-settings">
                    <?php
                    printf(
                        /* translators: %s: link to the Configurations screen. */
                        esc_html__('Site title, email sender, date format, user roles and every other setting are under %s, whenever you need them.', 'wp-job-portal'),
                        '<a href="' . esc_url(admin_url('admin.php?page=wpjobportal_configuration&wpjobportallt=configurations')) . '" target="_blank" rel="noopener">' . esc_html__('Configurations', 'wp-job-portal') . '</a>'
                    );
                    ?>
                </p>

                <div class="wpjobportal-post-action-btn">
                    <a class="next-step wpjobportal-post-act-btn" href="javascript:void();" onclick="document.getElementById('wpjobportal-form-ins').submit();" title="<?php echo esc_attr(__('Next','wp-job-portal')); ?>">
                        <?php echo esc_html(__('Next','wp-job-portal')); ?>
                    </a>
                </div>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('action', 'postinstallation_save'),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('form_request', 'wpjobportal'),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('step', 1),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('_wpnonce', esc_html(wp_create_nonce('wpjobportal_postinstallation_nonce'))),WPJOBPORTAL_ALLOWED_TAGS); ?>
            </form>
        </div>
    </div>
</div>
<?php
    wp_register_script( 'wpjobportal-inline-handle', '', array(), WPJOBPORTAL_VERSION, false );
    wp_enqueue_script( 'wpjobportal-inline-handle' );

    $wpjobportal_inline_js_script = "
    jQuery(document).ready(function () {
        jQuery('.inputbox').on('focus', function () {
            jQuery(this).closest('.wpjobportal-post-val').prev('.wpjobportal-post-tit').css('color', '#1572e8');
        });
        jQuery('.inputbox').on('blur', function () {
            jQuery(this).closest('.wpjobportal-post-val').prev('.wpjobportal-post-tit').css('color', '');
        });
    });
    ";
    wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
?>
