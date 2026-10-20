<?php
if (!defined('ABSPATH')) {
    die('Restricted Access');
}


$wpjobportal_mode_status = isset(wpjobportal::$_data['mode_status']) && is_array(wpjobportal::$_data['mode_status'])
    ? wpjobportal::$_data['mode_status']
    : WPJOBPORTAL_Product_Mode_Service::get_status();
$wpjobportal_mode_matrix = isset(wpjobportal::$_data['mode_matrix']) && is_array(wpjobportal::$_data['mode_matrix'])
    ? wpjobportal::$_data['mode_matrix']
    : WPJOBPORTAL_Mode_Policy_Service::get_matrix();

$wpjobportal_current_mode = isset($wpjobportal_mode_status['mode']) ? $wpjobportal_mode_status['mode'] : WPJOBPORTAL_Product_Mode_Service::MODE_LEGACY;
$wpjobportal_is_careers = WPJOBPORTAL_Product_Mode_Service::MODE_CAREERS === $wpjobportal_current_mode;
$wpjobportal_is_marketplace = WPJOBPORTAL_Product_Mode_Service::MODE_MARKETPLACE === $wpjobportal_current_mode;
$wpjobportal_setup_url = admin_url('admin.php?page=wpjobportal_postinstallation&wpjobportallt=quickstart');
$wpjobportal_settings_url = admin_url('admin.php?page=wpjobportal_configuration&wpjobportallt=configurations');
?>
<div id="wpjobportaladmin-wrapper">
    <div id="wpjobportaladmin-leftmenu">
        <?php WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <?php WPJOBPORTALincluder::getTemplate('templates/admin/pagetitle', array('wpjobportal_module' => 'wpjobportal', 'wpjobportal_layouts' => 'modeguide')); ?>
        <div id="wpjobportal-admin-wrapper" class="p0 bg-n bs-n">
        <div class="wjp-mode-guide">

            <div class="wjp-mode-guide-intro">
                <p class="wjp-mode-guide-lede">
                    <?php echo esc_html__('Your mode decides which features this site needs. Features a mode does not use are hidden from the menu and their settings are set for you, so the admin only shows what applies to the site you are running.', 'wp-job-portal'); ?>
                </p>
                <div class="wjp-mode-guide-current">
                    <span class="wjp-mode-guide-current-label"><?php echo esc_html__('Current mode', 'wp-job-portal'); ?></span>
                    <strong class="wjp-mode-guide-current-value"><?php echo esc_html(WPJOBPORTAL_Product_Mode_Service::get_label($wpjobportal_current_mode)); ?></strong>
                </div>
            </div>

            <?php if (!$wpjobportal_is_careers && !$wpjobportal_is_marketplace) : ?>
                <div class="wjp-mode-guide-notice">
                    <p>
                        <strong><?php echo esc_html__('You have not chosen a mode yet.', 'wp-job-portal'); ?></strong>
                        <?php echo esc_html__('This site runs in existing-site compatibility: nothing is hidden and no setting is locked. Choose a mode when you want the admin tailored to how you actually hire.', 'wp-job-portal'); ?>
                    </p>
                </div>
            <?php endif; ?>

            <div class="wjp-mode-guide-cards">
                <div class="wjp-mode-guide-card<?php echo $wpjobportal_is_careers ? ' is-current' : ''; ?>">
                    <h2><?php echo esc_html__('Careers &amp; ATS', 'wp-job-portal'); ?></h2>
                    <?php if ($wpjobportal_is_careers) : ?>
                        <span class="wjp-mode-guide-chip"><?php echo esc_html__('In use', 'wp-job-portal'); ?></span>
                    <?php endif; ?>
                    <p><?php echo esc_html__('One company hiring for itself. You publish your own openings from the admin, receive applications, and manage candidates in a single workspace.', 'wp-job-portal'); ?></p>
                    <p class="wjp-mode-guide-fit"><?php echo esc_html__('Choose this when the only employer on the site is you.', 'wp-job-portal'); ?></p>
                </div>
                <div class="wjp-mode-guide-card<?php echo $wpjobportal_is_marketplace ? ' is-current' : ''; ?>">
                    <h2><?php echo esc_html__('Job Marketplace', 'wp-job-portal'); ?></h2>
                    <?php if ($wpjobportal_is_marketplace) : ?>
                        <span class="wjp-mode-guide-chip"><?php echo esc_html__('In use', 'wp-job-portal'); ?></span>
                    <?php endif; ?>
                    <p><?php echo esc_html__('Many employers sharing one job board. Companies register, publish their own jobs, and reach candidates, while you moderate and can charge for listings.', 'wp-job-portal'); ?></p>
                    <p class="wjp-mode-guide-fit"><?php echo esc_html__('Choose this when outside employers post to your site.', 'wp-job-portal'); ?></p>
                </div>
            </div>

            <h2 class="wjp-mode-guide-section-title"><?php echo esc_html__('What each mode includes', 'wp-job-portal'); ?></h2>
            <p class="wjp-mode-guide-section-intro">
                <?php echo esc_html__('Everything not listed here works the same in both modes. Only these features change.', 'wp-job-portal'); ?>
            </p>

            <div class="wjp-mode-guide-table-wrap">
                <table class="wjp-mode-guide-table">
                    <thead>
                        <tr>
                            <th scope="col"><?php echo esc_html__('Feature', 'wp-job-portal'); ?></th>
                            <th scope="col"><?php echo esc_html__('Careers &amp; ATS', 'wp-job-portal'); ?></th>
                            <th scope="col"><?php echo esc_html__('Job Marketplace', 'wp-job-portal'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($wpjobportal_mode_matrix as $wpjobportal_row) : ?>
                            <tr>
                                <th scope="row">
                                    <span class="wjp-mode-guide-feature"><?php echo esc_html($wpjobportal_row['label']); ?></span>
                                    <?php if (!empty($wpjobportal_row['description'])) : ?>
                                        <span class="wjp-mode-guide-feature-desc"><?php echo esc_html($wpjobportal_row['description']); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($wpjobportal_row['menu'])) : ?>
                                        <span class="wjp-mode-guide-feature-menu">
                                            <?php
                                            echo esc_html(sprintf(
                                                /* translators: %s: admin menu path, for example "Jobs -> Approval Queue". */
                                                __('Menu: %s', 'wp-job-portal'),
                                                $wpjobportal_row['menu']
                                            ));
                                            ?>
                                        </span>
                                    <?php endif; ?>
                                </th>
                                <?php foreach (array('careers', 'marketplace') as $wpjobportal_column) : ?>
                                    <td class="<?php echo !empty($wpjobportal_row[$wpjobportal_column]) ? 'is-available' : 'is-unavailable'; ?>">
                                        <span class="wjp-mode-guide-state">
                                            <?php echo !empty($wpjobportal_row[$wpjobportal_column]) ? esc_html__('Available', 'wp-job-portal') : esc_html__('Not used', 'wp-job-portal'); ?>
                                        </span>
                                        <?php if (empty($wpjobportal_row[$wpjobportal_column]) && !empty($wpjobportal_row['note'])) : ?>
                                            <span class="wjp-mode-guide-note"><?php echo esc_html($wpjobportal_row['note']); ?></span>
                                        <?php endif; ?>
                                        <?php foreach ((array) $wpjobportal_row['settings'] as $wpjobportal_setting) : ?>
                                            <?php if ('' !== $wpjobportal_setting[$wpjobportal_column]) : ?>
                                                <span class="wjp-mode-guide-setting">
                                                    <?php
                                                    echo esc_html(sprintf(
                                                        /* translators: 1: setting name, 2: value the mode forces. */
                                                        __('%1$s is fixed at %2$s', 'wp-job-portal'),
                                                        $wpjobportal_setting['label'],
                                                        $wpjobportal_setting[$wpjobportal_column]
                                                    ));
                                                    ?>
                                                </span>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <h2 class="wjp-mode-guide-section-title"><?php echo esc_html__('Why a menu item is missing', 'wp-job-portal'); ?></h2>
            <div class="wjp-mode-guide-reasons">
                <p>
                    <?php echo esc_html__('A hidden menu item is never a licensing limit. It means the current mode has no use for that screen, so it is kept out of the way. Two things happen together:', 'wp-job-portal'); ?>
                </p>
                <ul>
                    <li><?php echo esc_html__('The menu entry is hidden, because the screen would always be empty or would not apply.', 'wp-job-portal'); ?></li>
                    <li><?php echo esc_html__('Its settings are set to the value the mode needs and shown as read-only, marked "Set by mode" on the settings screen.', 'wp-job-portal'); ?></li>
                </ul>
                <p>
                    <?php echo esc_html__('Switching modes brings the menu entry back and unlocks its settings again. Your jobs, companies, candidates and applications are never deleted or reset by a mode change.', 'wp-job-portal'); ?>
                </p>
                <p class="wjp-mode-guide-links">
                    <a class="wjp-mode-guide-btn" href="<?php echo esc_url($wpjobportal_setup_url); ?>"><?php echo esc_html__('Change your mode', 'wp-job-portal'); ?></a>
                    <a class="wjp-mode-guide-btn is-secondary" href="<?php echo esc_url($wpjobportal_settings_url); ?>"><?php echo esc_html__('Open settings', 'wp-job-portal'); ?></a>
                </p>
            </div>

            <h2 class="wjp-mode-guide-section-title"><?php echo esc_html__('Differences that are not locked', 'wp-job-portal'); ?></h2>
            <p class="wjp-mode-guide-section-intro">
                <?php echo esc_html__('Choosing a mode also sets a few sensible starting values, such as whether the employer area is enabled and whether a company is required on a job. Those are only defaults: you can change them at any time, and they are not marked "Set by mode".', 'wp-job-portal'); ?>
            </p>

        </div>
        </div>
    </div>
</div>
