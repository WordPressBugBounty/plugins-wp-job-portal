<?php
if (!defined('ABSPATH')) die('Restricted Access');

/*
 * The setup wizard's step list, in one place.
 *
 * Four steps: choose a mode, the few settings a new site cannot work without,
 * optional sample data, and done. The wizard used to ask 25 settings over three
 * more steps - data directory, slug, date format, default roles - all of which
 * have working defaults and live on the Configurations screen, so a new
 * administrator answered them before seeing anything run.
 *
 * Set $wpjobportal_setup_active to mode, essentials, sample or done first.
 */
$wpjobportal_setup_active = isset($wpjobportal_setup_active) ? $wpjobportal_setup_active : '';
$wpjobportal_setup_steps = array(
    'mode' => array('zero-part', 'quickstart', 'quick-start.png', 'quick-strt-w.png', __('Choose Mode', 'wp-job-portal')),
    'essentials' => array('first-part', 'stepone', 'general-settings.png', 'general-settings-white.png', __('Essentials', 'wp-job-portal')),
    'sample' => array('fourth-part', 'stepfour', 'sample-data.png', 'sample-data-w.png', __('Sample Data', 'wp-job-portal')),
    'done' => array('setup-complete', 'setupcomplete', 'finshed.png', 'finshed-w.png', __('Setup Complete', 'wp-job-portal')),
);
?>
<ul class="wpjobportal-setup-steps">
    <?php foreach ($wpjobportal_setup_steps as $wpjobportal_setup_key => $wpjobportal_setup_step) : ?>
        <li class="<?php echo esc_attr($wpjobportal_setup_step[0] . ($wpjobportal_setup_key === $wpjobportal_setup_active ? ' active' : '')); ?>">
            <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_postinstallation&wpjobportallt=' . $wpjobportal_setup_step[1])); ?>" class="tab_icon"<?php echo $wpjobportal_setup_key === $wpjobportal_setup_active ? ' aria-current="step"' : ''; ?>>
                <span class="wpjobportal-post-installation-lftimages-wrp">
                    <img class="wpjobportal-post-installation-lfticon" src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL . 'includes/images/postinstallation/' . $wpjobportal_setup_step[2]); ?>" alt="" />
                    <img class="wpjobportal-post-installation-white-icon" src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL . 'includes/images/postinstallation/' . $wpjobportal_setup_step[3]); ?>" alt="" />
                    <?php echo esc_html($wpjobportal_setup_step[4]); ?>
                </span>
                <?php if ($wpjobportal_setup_key !== 'done') : ?>
                    <img class="wpjobportal-post-installation-white-arrowicon" src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL . 'includes/images/postinstallation/arrow.png'); ?>" alt="" />
                <?php endif; ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>
