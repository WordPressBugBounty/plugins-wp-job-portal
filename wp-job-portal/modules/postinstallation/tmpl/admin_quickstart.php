<?php
if (!defined('ABSPATH')) {
    die('Restricted Access');
}

$wpjobportal_selected_mode = WPJOBPORTAL_Product_Mode_Service::get_suggested_mode();
$wpjobportal_is_marketplace = WPJOBPORTAL_Product_Mode_Service::MODE_MARKETPLACE === $wpjobportal_selected_mode;
$wpjobportal_onboarding = isset(wpjobportal::$_data['onboarding']) && is_array(wpjobportal::$_data['onboarding'])
    ? wpjobportal::$_data['onboarding']
    : WPJOBPORTAL_Onboarding_Service::get_state();
$wpjobportal_sample_preview = isset(wpjobportal::$_data['sample_cleanup_preview']) && is_array(wpjobportal::$_data['sample_cleanup_preview'])
    ? wpjobportal::$_data['sample_cleanup_preview']
    : WPJOBPORTAL_Sample_Data_Service::get_preview();
$wpjobportal_cleanup_result = isset(wpjobportal::$_data['sample_cleanup_result']) && is_array(wpjobportal::$_data['sample_cleanup_result'])
    ? wpjobportal::$_data['sample_cleanup_result']
    : array();
$wpjobportal_action_url = admin_url('admin.php?page=wpjobportal_postinstallation&action=wpjobportaltask');
?>
<div id="wpjobportaladmin-wrapper" class="wpjobportal-post-installation-wrp">
    <div class="wpjobportal-post-installation">
        <div class="wpjobportal-post-menu">
            <div class="wpjobportal-post-installation-logowrp">
                <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/postinstallation/quickstrt_logo.png" alt="<?php echo esc_attr__('WP Job Portal', 'wp-job-portal'); ?>" />
            </div>
            <?php $wpjobportal_setup_active = 'mode'; include __DIR__ . '/setup-steps.php'; ?>
        </div>

        <div class="wpjobportal-post-data">
            <div class="wpjobportal-post-heading-wrp">
                <div class="wpjobportal-post-heading">
                    <?php echo esc_html__('Choose your WP Job Portal setup', 'wp-job-portal'); ?>
                </div>
                <div class="wpjobportal-post-head-rightbtns-wrp">
                    <span id="wpjobportal-mode-step-count" class="wpjobportal-post-head-pagestep" data-marketplace="<?php echo esc_attr__('Step 1 of 4', 'wp-job-portal'); ?>" data-careers="<?php echo esc_attr__('Step 1 of 4', 'wp-job-portal'); ?>">
                        <?php echo esc_html(__('Step 1 of 4', 'wp-job-portal')); ?>
                    </span>
                    <a class="wpjobportal-post-head-closebtn" href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal')); ?>" title="<?php echo esc_attr__('Close', 'wp-job-portal'); ?>">
                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/postinstallation/close.png" alt="<?php echo esc_attr__('Close', 'wp-job-portal'); ?>" />
                    </a>
                </div>
            </div>

            <form id="wpjobportal-form-ins" class="wpjobportal-form wpjobportal-mode-form" method="post" action="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_postinstallation&task=save&action=wpjobportaltask')); ?>">
                <?php if (absint(WPJOBPORTALrequest::getVar('wpjobportal_setup_error')) === 1) { ?>
                    <div class="notice notice-error inline wpjobportal-mode-error" role="alert">
                        <p><?php echo esc_html__('The setup mode could not be saved. No mode settings were applied. Please review System Status and try again.', 'wp-job-portal'); ?></p>
                    </div>
                <?php } ?>
                <fieldset class="wpjobportal-mode-fieldset">
                    <legend><?php echo esc_html__('What are you building?', 'wp-job-portal'); ?></legend>
                    <p class="wpjobportal-mode-intro">
                        <?php echo esc_html__('This choice tailors setup and navigation. You can change it later without deleting jobs, companies, candidates, or applications.', 'wp-job-portal'); ?>
                        <a class="wpjobportal-mode-guide-link" href="<?php echo esc_url(WPJOBPORTAL_Mode_Policy_Service::guide_url()); ?>"><?php echo esc_html__('See exactly what changes in each mode', 'wp-job-portal'); ?></a>
                    </p>

                    <div class="wpjobportal-post-employer-mode-wrap">
                        <label class="wpjobportal-post-employer-mode-single<?php echo WPJOBPORTAL_Product_Mode_Service::MODE_CAREERS === $wpjobportal_selected_mode ? ' wpj-is-selected' : ''; ?>" for="wpjobportal-product-mode-careers">
                            <span class="wpjobportal-post-employer-mode-single-img-wrap">
                                <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/postinstallation/single-company.png" alt="" />
                            </span>
                            <span class="wpjobportal-post-card-mainwrp">
                                <span class="wpjobportal-post-card-leftclm">
                                    <span class="wpjobportal-post-employer-mode-single-title"><?php echo esc_html__('Careers & ATS', 'wp-job-portal'); ?></span>
                                    <span class="wpjobportal-post-employer-mode-single-desc"><?php echo esc_html__('Publish your company jobs, receive applications, and manage candidates in one hiring workspace.', 'wp-job-portal'); ?></span>
                                    <span class="wpjobportal-mode-points">
                                        <span><?php echo esc_html__('One company careers site', 'wp-job-portal'); ?></span>
                                        <span><?php echo esc_html__('Admin-managed jobs and company profile', 'wp-job-portal'); ?></span>
                                        <span><?php echo esc_html__('Candidate applications and resumes', 'wp-job-portal'); ?></span>
                                    </span>
                                </span>
                                <span class="wpjobportal-mode-radio-wrap">
                                    <input type="radio" id="wpjobportal-product-mode-careers" name="product_mode" value="<?php echo esc_attr(WPJOBPORTAL_Product_Mode_Service::MODE_CAREERS); ?>" required <?php checked(WPJOBPORTAL_Product_Mode_Service::MODE_CAREERS, $wpjobportal_selected_mode); ?> />
                                    <span aria-hidden="true" class="wpjobportal-mode-radio-indicator"></span>
                                    <span class="screen-reader-text"><?php echo esc_html__('Select Careers & ATS', 'wp-job-portal'); ?></span>
                                </span>
                            </span>
                        </label>

                        <label class="wpjobportal-post-employer-mode-single<?php echo WPJOBPORTAL_Product_Mode_Service::MODE_MARKETPLACE === $wpjobportal_selected_mode ? ' wpj-is-selected' : ''; ?>" for="wpjobportal-product-mode-marketplace">
                            <span class="wpjobportal-post-employer-mode-single-img-wrap">
                                <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/postinstallation/job-board-icon.png" alt="" />
                            </span>
                            <span class="wpjobportal-post-card-mainwrp">
                                <span class="wpjobportal-post-card-leftclm">
                                    <span class="wpjobportal-post-employer-mode-single-title"><?php echo esc_html__('Job Marketplace', 'wp-job-portal'); ?></span>
                                    <span class="wpjobportal-post-employer-mode-single-desc"><?php echo esc_html__('Run a multi-employer job portal where companies register, publish jobs, and connect with candidates.', 'wp-job-portal'); ?></span>
                                    <span class="wpjobportal-mode-points">
                                        <span><?php echo esc_html__('Multiple employer accounts', 'wp-job-portal'); ?></span>
                                        <span><?php echo esc_html__('Company and job moderation controls', 'wp-job-portal'); ?></span>
                                        <span><?php echo esc_html__('Marketplace-ready listings and search', 'wp-job-portal'); ?></span>
                                    </span>
                                </span>
                                <span class="wpjobportal-mode-radio-wrap">
                                    <input type="radio" id="wpjobportal-product-mode-marketplace" name="product_mode" value="<?php echo esc_attr(WPJOBPORTAL_Product_Mode_Service::MODE_MARKETPLACE); ?>" required <?php checked(WPJOBPORTAL_Product_Mode_Service::MODE_MARKETPLACE, $wpjobportal_selected_mode); ?> />
                                    <span aria-hidden="true" class="wpjobportal-mode-radio-indicator"></span>
                                    <span class="screen-reader-text"><?php echo esc_html__('Select Job Marketplace', 'wp-job-portal'); ?></span>
                                </span>
                            </span>
                        </label>
                    </div>
                </fieldset>

                <div class="wpjobportal-post-action-btn">
                    <button type="submit" class="next-step wpjobportal-post-act-btn"><?php echo esc_html__('Continue setup', 'wp-job-portal'); ?></button>
                </div>

                <?php echo wp_kses(WPJOBPORTALformfield::hidden('enable_multiple_employers_mode', $wpjobportal_is_marketplace ? 1 : 0), WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('action', 'postinstallation_save'), WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('form_request', 'wpjobportal'), WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('setup_stage', 'mode'), WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('step', 1), WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('_wpnonce', wp_create_nonce('wpjobportal_postinstallation_nonce')), WPJOBPORTAL_ALLOWED_TAGS); ?>
            </form>

            <section class="wpjobportal-onboarding-card" aria-labelledby="wpjobportal-onboarding-title">
                <?php if (!empty($wpjobportal_onboarding['dismissed'])) : ?>
                    <div class="wpjobportal-onboarding-dismissed">
                        <div>
                            <h2 id="wpjobportal-onboarding-title"><?php echo esc_html__('Onboarding checklist', 'wp-job-portal'); ?></h2>
                            <p><?php echo esc_html__('The checklist is hidden for your administrator account. Completion still reflects the real site state.', 'wp-job-portal'); ?></p>
                        </div>
                        <form method="post" action="<?php echo esc_url(add_query_arg('task', 'reopenonboarding', $wpjobportal_action_url)); ?>">
                            <input type="hidden" name="form_request" value="wpjobportal" />
                            <?php wp_nonce_field('wpjobportal_postinstallation_nonce'); ?>
                            <button type="submit" class="button button-secondary"><?php echo esc_html__('Reopen checklist', 'wp-job-portal'); ?></button>
                        </form>
                    </div>
                <?php else : ?>
                    <div class="wpjobportal-onboarding-heading">
                        <div>
                            <h2 id="wpjobportal-onboarding-title"><?php echo esc_html__('Onboarding checklist', 'wp-job-portal'); ?></h2>
                            <p>
                                <?php
                                echo esc_html(sprintf(
                                    /* translators: 1: onboarding completed, 2: onboarding total, 3: onboarding mode label */ __('%1$d of %2$d steps complete for %3$s. Progress is calculated from saved settings and content.', 'wp-job-portal'),
                                    absint($wpjobportal_onboarding['completed']),
                                    absint($wpjobportal_onboarding['total']),
                                    $wpjobportal_onboarding['mode_label']
                                ));
                                ?>
                            </p>
                        </div>
                        <span class="wpjobportal-onboarding-progress" aria-label="<?php echo esc_attr(sprintf(/* translators: 1: onboarding completed, 2: onboarding total */ __('Onboarding progress: %1$d of %2$d', 'wp-job-portal'), absint($wpjobportal_onboarding['completed']), absint($wpjobportal_onboarding['total']))); ?>">
                            <?php echo esc_html(absint($wpjobportal_onboarding['completed']) . '/' . absint($wpjobportal_onboarding['total'])); ?>
                        </span>
                    </div>

                    <ol class="wpjobportal-onboarding-list">
                        <?php $wpjobportal_item_number = 0; ?>
                        <?php foreach ((array) $wpjobportal_onboarding['items'] as $wpjobportal_item) : ?>
                            <?php $wpjobportal_item_number++; ?>
                            <li class="<?php echo !empty($wpjobportal_item['complete']) ? 'is-complete' : 'is-pending'; ?>">
                                <span class="wpjobportal-onboarding-state" aria-hidden="true"><?php echo !empty($wpjobportal_item['complete']) ? '&#10003;' : esc_html($wpjobportal_item_number); ?></span>
                                <div class="wpjobportal-onboarding-copy">
                                    <h3><?php echo esc_html($wpjobportal_item['label']); ?></h3>
                                    <p><?php echo esc_html($wpjobportal_item['description']); ?></p>
                                    <span class="screen-reader-text">
                                        <?php echo !empty($wpjobportal_item['complete']) ? esc_html__('Complete', 'wp-job-portal') : esc_html__('Pending', 'wp-job-portal'); ?>
                                    </span>
                                </div>
                                <a class="button button-secondary" href="<?php echo esc_url($wpjobportal_item['url']); ?>"><?php echo esc_html($wpjobportal_item['action']); ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ol>

                    <form class="wpjobportal-onboarding-dismiss-form" method="post" action="<?php echo esc_url(add_query_arg('task', 'dismissonboarding', $wpjobportal_action_url)); ?>">
                        <input type="hidden" name="form_request" value="wpjobportal" />
                        <?php wp_nonce_field('wpjobportal_postinstallation_nonce'); ?>
                        <button type="submit" class="button-link"><?php echo esc_html__('Hide checklist for my account', 'wp-job-portal'); ?></button>
                    </form>
                <?php endif; ?>
            </section>

            <section class="wpjobportal-sample-cleanup-card" aria-labelledby="wpjobportal-sample-cleanup-title">
                <h2 id="wpjobportal-sample-cleanup-title"><?php echo esc_html__('Sample-data safety', 'wp-job-portal'); ?></h2>

                <?php $wpjobportal_owner_notice = isset(wpjobportal::$_data['sample_owner_notice']) ? wpjobportal::$_data['sample_owner_notice'] : null; ?>
                <?php if (!empty($wpjobportal_owner_notice['text'])) : ?>
                    <div class="notice <?php echo $wpjobportal_owner_notice['type'] === 'success' ? 'notice-success' : 'notice-error'; ?> inline" role="status">
                        <p><?php echo esc_html($wpjobportal_owner_notice['text']); ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($wpjobportal_cleanup_result)) : ?>
                    <div class="notice <?php echo !empty($wpjobportal_cleanup_result['failed']) ? 'notice-error' : 'notice-success'; ?> inline" role="status">
                        <p>
                            <?php
                            echo esc_html(sprintf(
                                /* translators: 1: cleanup result deleted, 2: cleanup result skipped, 3: cleanup result failed */ __('Cleanup result: %1$d deleted, %2$d skipped, %3$d failed.', 'wp-job-portal'),
                                absint($wpjobportal_cleanup_result['deleted']),
                                absint($wpjobportal_cleanup_result['skipped']),
                                absint($wpjobportal_cleanup_result['failed'])
                            ));
                            ?>
                        </p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($wpjobportal_sample_preview['has_markers'])) : ?>
                    <p>
                        <?php
                        echo esc_html(sprintf(
                            /* translators: 1: sample preview tracked, 2: sample preview eligible, 3: sample preview skipped */ __('Cleanup preview: %1$d strongly marked records are tracked; %2$d are currently eligible and %3$d will be skipped to protect changed or dependent data.', 'wp-job-portal'),
                            absint($wpjobportal_sample_preview['tracked']),
                            absint($wpjobportal_sample_preview['eligible']),
                            absint($wpjobportal_sample_preview['skipped'])
                        ));
                        ?>
                    </p>
                    <ul class="wpjobportal-sample-counts">
                        <?php
                        $wpjobportal_sample_labels = array(
                            'companies' => __('Companies', 'wp-job-portal'),
                            'jobs' => __('Jobs', 'wp-job-portal'),
                            'resumes' => __('Resumes', 'wp-job-portal'),
                            'applications' => __('Applications', 'wp-job-portal'),
                            'pages' => __('Pages', 'wp-job-portal'),
                        );
                        foreach ($wpjobportal_sample_labels as $wpjobportal_type => $wpjobportal_label) :
                            $wpjobportal_type_counts = isset($wpjobportal_sample_preview['counts'][$wpjobportal_type])
                                ? $wpjobportal_sample_preview['counts'][$wpjobportal_type]
                                : array('tracked' => 0, 'eligible' => 0, 'skipped' => 0);
                            ?>
                            <li>
                                <strong><?php echo esc_html($wpjobportal_label); ?>:</strong>
                                <?php
                                echo esc_html(sprintf(
                                    /* translators: 1: type counts tracked, 2: type counts eligible, 3: type counts skipped */ __('%1$d tracked, %2$d eligible, %3$d protected', 'wp-job-portal'),
                                    absint($wpjobportal_type_counts['tracked']),
                                    absint($wpjobportal_type_counts['eligible']),
                                    absint($wpjobportal_type_counts['skipped'])
                                ));
                                ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <p><?php echo esc_html__('Cleanup never guesses from titles or content. It preserves unmarked historical data, users, settings, payments, credits, and uploaded files.', 'wp-job-portal'); ?></p>
                    <?php if (current_user_can('manage_options')) : ?>
                        <form class="wpjobportal-sample-cleanup-form" method="post" action="<?php echo esc_url(add_query_arg('task', 'cleansampledata', $wpjobportal_action_url)); ?>">
                            <input type="hidden" name="form_request" value="wpjobportal" />
                            <?php wp_nonce_field('wpjobportal_postinstallation_nonce'); ?>
                            <label>
                                <input type="checkbox" name="sample_cleanup_confirmation" value="cleanup_marked_sample_data" required />
                                <?php echo esc_html__('I understand that only the eligible, plugin-marked records in this preview will be removed.', 'wp-job-portal'); ?>
                            </label>
                            <button type="submit" class="button button-secondary" <?php disabled(0, absint($wpjobportal_sample_preview['eligible'])); ?>><?php echo esc_html__('Clean marked sample data', 'wp-job-portal'); ?></button>
                        </form>
                    <?php else : ?>
                        <p><?php echo esc_html__('An administrator must run sample-data cleanup.', 'wp-job-portal'); ?></p>
                    <?php endif; ?>

                    <?php
                    // Who owns the sample data, and a way to change it. An
                    // administrator who skipped the choice, or picked the wrong
                    // account, should not have to import the demo again.
                    if (current_user_can('manage_options')) :
                        $wpjobportal_owner_roles = array(
                            'employer' => array(WPJOBPORTAL_Sample_Data_Service::ROLE_EMPLOYER, __('Employer', 'wp-job-portal'), __('companies and jobs', 'wp-job-portal')),
                            'jobseeker' => array(WPJOBPORTAL_Sample_Data_Service::ROLE_JOBSEEKER, __('Job seeker', 'wp-job-portal'), __('resumes and applications', 'wp-job-portal')),
                        );
                        ?>
                        <form class="wpjobportal-sample-owner-form" method="post" action="<?php echo esc_url(add_query_arg('task', 'assignsampleowner', $wpjobportal_action_url)); ?>">
                            <input type="hidden" name="form_request" value="wpjobportal" />
                            <?php wp_nonce_field('wpjobportal_postinstallation_nonce'); ?>
                            <h3><?php echo esc_html__('Who owns the sample data', 'wp-job-portal'); ?></h3>
                            <?php foreach ($wpjobportal_owner_roles as $wpjobportal_owner_key => $wpjobportal_owner_role) :
                                $wpjobportal_owner_now = WPJOBPORTAL_Sample_Data_Service::current_owner($wpjobportal_owner_role[0]);
                                $wpjobportal_owner_user = $wpjobportal_owner_now > 0 ? get_userdata($wpjobportal_owner_now) : false;
                                $wpjobportal_owner_list = WPJOBPORTAL_Sample_Data_Service::owner_candidates($wpjobportal_owner_role[0]);
                                $wpjobportal_owner_list[0] = (object) array('id' => '', 'text' => __('Keep as it is', 'wp-job-portal'));
                                $wpjobportal_owner_field = 'sample_' . $wpjobportal_owner_key . '_owner';
                                ?>
                                <div class="wpjobportal-sample-owner-row">
                                    <label for="<?php echo esc_attr($wpjobportal_owner_field); ?>"><?php echo esc_html($wpjobportal_owner_role[1]); ?></label>
                                    <p class="wpjobportal-sample-owner-now">
                                        <?php
                                        echo esc_html($wpjobportal_owner_user
                                            ? sprintf(
                                                /* translators: 1: user's name, 2: what they own. */
                                                __('%1$s owns the sample %2$s.', 'wp-job-portal'), $wpjobportal_owner_user->display_name, $wpjobportal_owner_role[2])
                                            : sprintf(
                                                /* translators: %s: what is unowned. */
                                                __('Nobody owns the sample %s yet, so no dashboard lists them.', 'wp-job-portal'), $wpjobportal_owner_role[2]));
                                        ?>
                                    </p>
                                    <?php echo wp_kses(WPJOBPORTALformfield::select($wpjobportal_owner_field, $wpjobportal_owner_list, '', '', array('class' => 'inputbox')), WPJOBPORTAL_ALLOWED_TAGS); ?>
                                </div>
                            <?php endforeach; ?>
                            <p class="wpjobportal-sample-owner-actions">
                                <button type="submit" class="button button-secondary"><?php echo esc_html__('Change owner', 'wp-job-portal'); ?></button>
                                <a href="<?php echo esc_url(admin_url('user-new.php')); ?>" target="_blank" rel="noopener"><?php echo esc_html__('Add new user', 'wp-job-portal'); ?></a>
                            </p>
                        </form>
                    <?php endif; ?>
                <?php else : ?>
                    <p><?php echo esc_html__('No strongly marked sample records are available for automatic cleanup. Historical sample-like content is preserved for manual review because ownership cannot be proven.', 'wp-job-portal'); ?></p>
                <?php endif; ?>
            </section>
        </div>
    </div>
</div>

<?php
wp_register_script( 'wpjobportal-mode-setup', '', array('jquery'), WPJOBPORTAL_VERSION, true );
wp_enqueue_script('wpjobportal-mode-setup');
wp_add_inline_script(
    'wpjobportal-mode-setup',
    "jQuery(function ($) {
        var updateMode = function () {
            var selected = $('input[name=\"product_mode\"]:checked');
            $('.wpjobportal-post-employer-mode-single').removeClass('wpj-is-selected');
            selected.closest('.wpjobportal-post-employer-mode-single').addClass('wpj-is-selected');
            $('#enable_multiple_employers_mode').val(selected.val() === 'marketplace' ? '1' : '0');
            $('.second-part').toggleClass('wpjobportal-mode-step-optional', selected.val() !== 'marketplace');
            var stepCount = $('#wpjobportal-mode-step-count');
            stepCount.text(selected.val() === 'marketplace' ? stepCount.data('marketplace') : stepCount.data('careers'));
        };
        $('input[name=\"product_mode\"]').on('change', updateMode);
        updateMode();
    });"
);
?>
