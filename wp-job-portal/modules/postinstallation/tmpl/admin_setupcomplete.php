<?php
if (!defined('ABSPATH')) die('Restricted Access');
$wpjobportal_mode_status = WPJOBPORTAL_Product_Mode_Service::get_status();
$wpjobportal_mode_label = $wpjobportal_mode_status['label'];
?>
<div id="wpjobportaladmin-wrapper" class="wpjobportal-post-installation-wrp">
    <!-- content -->
    <div class="wpjobportal-post-installation">
        <div class="wpjobportal-post-menu">
            <div class="wpjobportal-post-installation-logowrp">
                <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/postinstallation/quickstrt_logo.png" />
            </div>
            <?php $wpjobportal_setup_active = 'done'; include __DIR__ . '/setup-steps.php'; ?>
        </div>
        <div class="wpjobportal-post-data">
            <div class="wpjobportal-post-heading-wrp">
                <div class="wpjobportal-post-heading">
                    <?php echo esc_html(__('Setup Complete','wp-job-portal'));?>
                </div>
                <div class="wpjobportal-post-head-rightbtns-wrp">
                    <a class="wpjobportal-post-head-closebtn" href="admin.php?page=wpjobportal"title="<?php echo esc_attr(__('Close','wp-job-portal'));?>">
                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/postinstallation/close.png" />
                    </a>
                </div>
            </div>
            <div class="wpjobportal-post-cmpletesetup-wrp">
                <span class="wpjobportal-post-cmpletesetup-logo"><img alt="<?php echo esc_attr(__('Setup Complete','wp-job-portal'));?>" src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/postinstallation/complete-setup.png" /></span>
                <span class="wpjobportal-mode-complete-label"><?php echo esc_html($wpjobportal_mode_label); ?></span>
                <span class="wpjobportal-post-cmpletesetup-title">
                    <?php
                    echo esc_html(sprintf(
                        /* translators: %s: mode label */ __('%s setup is ready. Your settings have been saved.', 'wp-job-portal'),
                        $wpjobportal_mode_label
                    ));
                    ?>
                </span>
                <?php
                // With sample data in, "settings saved" is not the whole story:
                // say who owns it and where to look, since the demo is only
                // visible from those accounts' dashboards and the front end.
                if (!empty(WPJOBPORTAL_Sample_Data_Service::tracked_ids('jobs'))) :
                    $wpjobportal_demo_links = WPJOBPORTAL_Sample_Data_Service::result_links();
                    $wpjobportal_demo_owners = array();
                    foreach (array(WPJOBPORTAL_Sample_Data_Service::ROLE_EMPLOYER, WPJOBPORTAL_Sample_Data_Service::ROLE_JOBSEEKER) as $wpjobportal_demo_role) {
                        $wpjobportal_demo_user = get_userdata(WPJOBPORTAL_Sample_Data_Service::current_owner($wpjobportal_demo_role));
                        $wpjobportal_demo_owners[$wpjobportal_demo_role] = $wpjobportal_demo_user ? $wpjobportal_demo_user->display_name : '';
                    }
                    ?>
                    <div class="wpjobportal-post-demo-result">
                        <p>
                            <?php
                            if ($wpjobportal_demo_owners[1] !== '' && $wpjobportal_demo_owners[2] !== '') {
                                echo esc_html(sprintf(
                                    /* translators: 1: employer's name, 2: job seeker's name. */
                                    __('Sample data is in. Sign in as %1$s to see the employer side, or as %2$s to see the job seeker side.', 'wp-job-portal'),
                                    $wpjobportal_demo_owners[1],
                                    $wpjobportal_demo_owners[2]
                                ));
                            } else {
                                echo esc_html__('Sample data is in.', 'wp-job-portal');
                            }
                            ?>
                        </p>
                        <?php if (!empty($wpjobportal_demo_links)) : ?>
                            <p class="wpjobportal-post-demo-links">
                                <?php
                                $wpjobportal_demo_labels = array(
                                    'jobs' => __('See the sample jobs', 'wp-job-portal'),
                                    'employer' => __('Employer dashboard', 'wp-job-portal'),
                                    'jobseeker' => __('Job seeker dashboard', 'wp-job-portal'),
                                );
                                foreach ($wpjobportal_demo_labels as $wpjobportal_demo_key => $wpjobportal_demo_label) :
                                    if (empty($wpjobportal_demo_links[$wpjobportal_demo_key])) { continue; }
                                    ?>
                                    <a href="<?php echo esc_url($wpjobportal_demo_links[$wpjobportal_demo_key]); ?>" target="_blank" rel="noopener"><?php echo esc_html($wpjobportal_demo_label); ?><span aria-hidden="true"> &#8599;</span><span class="screen-reader-text"><?php echo esc_html__('(opens in a new tab)', 'wp-job-portal'); ?></span></a>
                                <?php endforeach; ?>
                            </p>
                        <?php endif; ?>
                        <p class="wpjobportal-post-demo-note">
                            <?php
                            printf(
                                /* translators: %s: link to Setup & Mode. */
                                esc_html__('Change who owns it, or remove it, any time from %s.', 'wp-job-portal'),
                                '<a href="' . esc_url(admin_url('admin.php?page=wpjobportal_postinstallation&wpjobportallt=quickstart')) . '">' . esc_html__('Setup & Mode', 'wp-job-portal') . '</a>'
                            );
                            ?>
                        </p>
                    </div>
                <?php endif; ?>
                <div class="wpjobportal-post-action-btn">
                    <a class="next-step wpjobportal-post-act-btn" href="admin.php?page=wpjobportal" title="<?php echo esc_attr(__('finish','wp-job-portal')); ?>">
                        <?php echo esc_html(__('Open WP Job Portal Dashboard','wp-job-portal')); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
