<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

wp_enqueue_style('wpjobportal-redesign-css', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/wpjobportaladmin_redesign.css');
// Enqueue necessary scripts and styles
// wp_enqueue_style('wjp-font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
// wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js');
// Safely load external CSS/JS

    // Font Awesome (CSS)
    wp_enqueue_style(
        'wjp-font-awesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css',
        [], // no dependencies
        '5.15.4' // version
    );

    // Chart.js (JS)
    wp_enqueue_script(
        'chart-js',
        'https://cdn.jsdelivr.net/npm/chart.js',
        [],        // dependencies (none here, but you could add 'jquery' if needed)
        '4.4.0',   // version (always specify if possible)
        true       // load in footer (better for performance)
    );


// Register a handle for our inline script
wp_register_script('wp-job-portal-dashboard-js', '');
wp_enqueue_script('wp-job-portal-dashboard-js');

// Define default options and get the user's saved options from the wp_options table
$wjp_dashboard_defaults = [
    'quick_actions' => 'on',
    'platform_growth' => 'on',
    'quick_stats' => 'on',
    'recent_jobs' => 'on',
    'jobs_by_status_chart' => 'on',
    'top_categories_chart' => 'on',
    'latest_job_applies' => 'on',
    'latest_resumes' => 'on',
    'latest_subscriptions' => 'on',
    'latest_payments' => 'on',
    'latest_activity' => 'on',
    'system_error_log' => 'on',
    'latest_job_seekers' => 'on',
    'latest_employers' => 'on',
];
$wjp_options = get_option('wjp_dashboard_screen_options', $wjp_dashboard_defaults);

/**
 * Helper function to determine the CSS class for a system error log entry.
 *
 * @param string $error_message The error message text.
 * @return string The appropriate CSS class.
 */
if (!function_exists('get_error_log_class')) {
    function get_error_log_class($error_message) {
        $error_message_lower = strtolower($error_message);
        if (str_contains($error_message_lower, 'error') || str_contains($error_message_lower, 'failed') || str_contains($error_message_lower, 'fatal')) {
            return 'wjp-log-error';
        } elseif (str_contains($error_message_lower, 'warning')) {
            return 'wjp-log-warning';
        } elseif (str_contains($error_message_lower, 'notice')) {
            return 'wjp-log-notice';
        }
        return ''; // Default class
    }
}

?>

<div id="wjp-dashboard-wrapper">
    <div id="wpjobportaladmin-wrapper">
        <div id="wpjobportaladmin-leftmenu">
            <?php wpjobportalincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
        </div>
        <div id="wpjobportaladmin-data">
            <main class="wjp-main-content">

                <div id="wjp-header">
                    <div class="wjp-header-title">
                        <div class="wjp-h2"><?php echo esc_html__('Welcome back!', 'wp-job-portal'); ?></div>
                        <p><?php echo esc_html__('Here\'s a snapshot of your job board\'s performance.', 'wp-job-portal'); ?></p>
                    </div>
                    <div class="wjp-header-actions">
                        <div class="wjp-dropdown">
                            <a href="#" id="wjp-screen-options-btn" class="wjp-btn wjp-btn-secondary">
                                <i class="fas fa-sliders-h wjp-btn-icon"></i> <?php echo esc_html__('Options', 'wp-job-portal'); ?>
                            </a>

                            <div id="wjp-screen-options-menu" class="wjp-so-popup">
                               <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=wpjobportal&task=savescreenoptions&action=wpjobportaltask')); ?>">
                                    <?php // NONCE & ACTION FIELDS - DO NOT REMOVE ?>
                                    <input type="hidden" name="action" value="wpjobportaltask">
                                    <?php wp_nonce_field('wjp_dashboard_options_nonce', 'wjp_dashboard_nonce'); ?>

                                    <div class="wjp-so-header">
                                        <div class="wjp-so-title"><?php echo esc_html__('Customize Your Dashboard', 'wp-job-portal'); ?></div>
                                        <a href="#" class="wjp-so-close wjp-close-dropdown"><i class="fas fa-times"></i></a>
                                    </div>

                                    <div class="wjp-so-content">

                                        <div class="wjp-so-section">
                                            <div class="wjp-so-section-title"><?php echo esc_html__('Core Components', 'wp-job-portal'); ?></div>
                                            <div class="wjp-so-grid">
                                                <div class="wjp-so-item">
                                                    <div class="wjp-so-item-label"><i class="fas fa-bolt"></i><span><?php echo esc_html__('Quick Actions', 'wp-job-portal'); ?></span></div>
                                                    <label class="wjp-so-toggle"><input type="checkbox" name="wjp_screen_options[quick_actions]" <?php checked(isset($wjp_options['quick_actions'])); ?>><span class="wjp-so-slider"></span></label>
                                                </div>
                                                <div class="wjp-so-item">
                                                    <div class="wjp-so-item-label"><i class="fas fa-briefcase"></i><span><?php echo esc_html__('Recent Jobs', 'wp-job-portal'); ?></span></div>
                                                    <label class="wjp-so-toggle"><input type="checkbox" name="wjp_screen_options[recent_jobs]" <?php checked(isset($wjp_options['recent_jobs'])); ?>><span class="wjp-so-slider"></span></label>
                                                </div>
                                                <div class="wjp-so-item">
                                                    <div class="wjp-so-item-label"><i class="fas fa-tachometer-alt"></i><span><?php echo esc_html__('Quick Stats', 'wp-job-portal'); ?></span></div>
                                                    <label class="wjp-so-toggle"><input type="checkbox" name="wjp_screen_options[quick_stats]" <?php checked(isset($wjp_options['quick_stats'])); ?>><span class="wjp-so-slider"></span></label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="wjp-so-section">
                                            <div class="wjp-so-section-title"><?php echo esc_html__('Visualizations', 'wp-job-portal'); ?></div>
                                            <div class="wjp-so-grid">
                                                <div class="wjp-so-item">
                                                    <div class="wjp-so-item-label"><i class="fas fa-chart-line"></i><span><?php echo esc_html__('Platform Growth', 'wp-job-portal'); ?></span></div>
                                                    <label class="wjp-so-toggle"><input type="checkbox" name="wjp_screen_options[platform_growth]" <?php checked(isset($wjp_options['platform_growth'])); ?>><span class="wjp-so-slider"></span></label>
                                                </div>
                                                <div class="wjp-so-item">
                                                    <div class="wjp-so-item-label"><i class="fas fa-chart-pie"></i><span><?php echo esc_html__('Jobs by Status', 'wp-job-portal'); ?></span></div>
                                                    <label class="wjp-so-toggle"><input type="checkbox" name="wjp_screen_options[jobs_by_status_chart]" <?php checked(isset($wjp_options['jobs_by_status_chart'])); ?>><span class="wjp-so-slider"></span></label>
                                                </div>
                                                <div class="wjp-so-item">
                                                    <div class="wjp-so-item-label"><i class="fas fa-chart-bar"></i><span><?php echo esc_html__('Top Categories', 'wp-job-portal'); ?></span></div>
                                                    <label class="wjp-so-toggle"><input type="checkbox" name="wjp_screen_options[top_categories_chart]" <?php checked(isset($wjp_options['top_categories_chart'])); ?>><span class="wjp-so-slider"></span></label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="wjp-so-section">
                                            <div class="wjp-so-section-title"><?php echo esc_html__('Activity & System Feeds', 'wp-job-portal'); ?></div>
                                            <div class="wjp-so-grid">
                                                <div class="wjp-so-item">
                                                    <div class="wjp-so-item-label"><i class="fas fa-file-signature"></i><span><?php echo esc_html__('Latest Applies', 'wp-job-portal'); ?></span></div>
                                                    <label class="wjp-so-toggle"><input type="checkbox" name="wjp_screen_options[latest_job_applies]" <?php checked(isset($wjp_options['latest_job_applies'])); ?>><span class="wjp-so-slider"></span></label>
                                                </div>
                                                <div class="wjp-so-item">
                                                    <div class="wjp-so-item-label"><i class="fas fa-file-alt"></i><span><?php echo esc_html__('Latest Resumes', 'wp-job-portal'); ?></span></div>
                                                    <label class="wjp-so-toggle"><input type="checkbox" name="wjp_screen_options[latest_resumes]" <?php checked(isset($wjp_options['latest_resumes'])); ?>><span class="wjp-so-slider"></span></label>
                                                </div>
                                                <div class="wjp-so-item">
                                                    <div class="wjp-so-item-label"><i class="fas fa-history"></i><span><?php echo esc_html__('Activity Log', 'wp-job-portal'); ?></span></div>
                                                    <label class="wjp-so-toggle"><input type="checkbox" name="wjp_screen_options[latest_activity]" <?php checked(isset($wjp_options['latest_activity'])); ?>><span class="wjp-so-slider"></span></label>
                                                </div>
                                                <div class="wjp-so-item">
                                                    <div class="wjp-so-item-label"><i class="fas fa-exclamation-triangle"></i><span><?php echo esc_html__('Error Log', 'wp-job-portal'); ?></span></div>
                                                    <label class="wjp-so-toggle"><input type="checkbox" name="wjp_screen_options[system_error_log]" <?php checked(isset($wjp_options['system_error_log'])); ?>><span class="wjp-so-slider"></span></label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="wjp-so-section">
                                            <div class="wjp-so-section-title"><?php echo esc_html__('User & Financials', 'wp-job-portal'); ?></div>
                                            <div class="wjp-so-grid">
                                                <div class="wjp-so-item">
                                                    <div class="wjp-so-item-label"><i class="fas fa-user-friends"></i><span><?php echo esc_html__('Job Seekers', 'wp-job-portal'); ?></span></div>
                                                    <label class="wjp-so-toggle"><input type="checkbox" name="wjp_screen_options[latest_job_seekers]" <?php checked(isset($wjp_options['latest_job_seekers'])); ?>><span class="wjp-so-slider"></span></label>
                                                </div>
                                                <div class="wjp-so-item">
                                                    <div class="wjp-so-item-label"><i class="fas fa-building"></i><span><?php echo esc_html__('Employers', 'wp-job-portal'); ?></span></div>
                                                    <label class="wjp-so-toggle"><input type="checkbox" name="wjp_screen_options[latest_employers]" <?php checked(isset($wjp_options['latest_employers'])); ?>><span class="wjp-so-slider"></span></label>
                                                </div>
                                                <?php if(in_array('credits', wpjobportal::$_active_addons)){ ?>
                                                    <div class="wjp-so-item">
                                                        <div class="wjp-so-item-label"><i class="fas fa-tags"></i><span><?php echo esc_html__('Subscriptions', 'wp-job-portal'); ?></span></div>
                                                        <label class="wjp-so-toggle"><input type="checkbox" name="wjp_screen_options[latest_subscriptions]" <?php checked(isset($wjp_options['latest_subscriptions'])); ?>><span class="wjp-so-slider"></span></label>
                                                    </div>
                                                    <div class="wjp-so-item">
                                                        <div class="wjp-so-item-label"><i class="fas fa-credit-card"></i><span><?php echo esc_html__('Payments', 'wp-job-portal'); ?></span></div>
                                                        <label class="wjp-so-toggle"><input type="checkbox" name="wjp_screen_options[latest_payments]" <?php checked(isset($wjp_options['latest_payments'])); ?>><span class="wjp-so-slider"></span></label>
                                                    </div>
                                                <?php }?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="wjp-so-footer">
                                        <button type="submit" name="wjp_reset_options" value="1" class="wjp-btn-sm wjp-btn-reset"><?php echo esc_html__('Reset to Defaults', 'wp-job-portal'); ?></button>
                                        <button type="submit" class="wjp-btn-sm wjp-btn-primary"><?php echo esc_html__('Apply Changes', 'wp-job-portal'); ?></button>
                                    </div>
                               </form>
                            </div>
                            </div>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_job&wpjobportallt=formjob')); ?>" class="wjp-btn wjp-btn-primary">
                            <i class="fas fa-plus wjp-btn-icon"></i> <?php echo esc_html__('Add New Job', 'wp-job-portal'); ?>
                        </a>
                    </div>
                </div>
                <?php if (isset($wjp_options['quick_actions'])) : ?>
                <div id="wjp-quick-actions">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_job&wpjobportallt=jobqueue')); ?>" class="wjp-action-card">
                        <div class="wjp-action-icon-wrapper wjp-bg-indigo"><i class="fas fa-check"></i></div>
                        <p class="wjp-action-title"><?php echo esc_html__('Approve Jobs', 'wp-job-portal'); ?></p>
                        <p class="wjp-action-subtitle"><?php echo esc_html(isset(wpjobportal::$_data['totalnewjobspending']) ? wpjobportal::$_data['totalnewjobspending'] : '0'); ?> <?php echo esc_html__('Pending', 'wp-job-portal'); ?></p>
                    </a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_user')); ?>" class="wjp-action-card">
                        <div class="wjp-action-icon-wrapper wjp-bg-sky"><i class="fas fa-users-cog"></i></div>
                        <p class="wjp-action-title"><?php echo esc_html__('Manage Users', 'wp-job-portal'); ?></p>
                        <p class="wjp-action-subtitle"><?php echo esc_html(isset(wpjobportal::$_data['totaljobapply']) ? wpjobportal::$_data['totaljobapply'] : '0'); ?> <?php echo esc_html__('Total', 'wp-job-portal'); ?></p>
                    </a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_report&wpjobportallt=overallreports')); ?>" class="wjp-action-card">
                        <div class="wjp-action-icon-wrapper wjp-bg-emerald"><i class="fas fa-chart-bar"></i></div>
                        <p class="wjp-action-title"><?php echo esc_html__('View Reports', 'wp-job-portal'); ?></p>
                        <p class="wjp-action-subtitle"><?php echo esc_html__('Analytics', 'wp-job-portal'); ?></p>
                    </a>
                    <?php if(in_array('credits', wpjobportal::$_active_addons)){ ?>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_package')); ?>" class="wjp-action-card">
                            <div class="wjp-action-icon-wrapper wjp-bg-amber"><i class="fas fa-tags"></i></div>
                            <p class="wjp-action-title"><?php echo esc_html__('Manage Plans', 'wp-job-portal'); ?></p>
                            <p class="wjp-action-subtitle"><?php echo esc_html__('Subscriptions', 'wp-job-portal'); ?></p>
                        </a>
                    <?php } ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_configuration&wpjobportallt=configurations&wpjpconfigid=general_setting')); ?>" class="wjp-action-card">
                        <div class="wjp-action-icon-wrapper wjp-bg-slate"><i class="fas fa-cogs"></i></div>
                        <p class="wjp-action-title"><?php echo esc_html__('Settings', 'wp-job-portal'); ?></p>
                        <p class="wjp-action-subtitle"><?php echo esc_html__('Platform Config', 'wp-job-portal'); ?></p>
                    </a>
                </div>
                <?php endif; ?>
                <div id="wjp-main-grid" class="wjp-grid-section">
                    <?php if (isset($wjp_options['platform_growth'])) : ?>
                    <div id="wjp-platform-growth" class="wjp-card">
                        <div class="wjp-card-header">
                            <div class="wjp-h3" style="margin-bottom:0;"><?php echo esc_html__('Platform Growth', 'wp-job-portal'); ?></div>
                            <div class="wjp-chart-legend">
                                <span class="wjp-legend-item"><span class="wjp-legend-dot" style="background-color: var(--wjp-color-primary);"></span><?php echo esc_html__('Applications', 'wp-job-portal'); ?></span>
                                <span class="wjp-legend-item"><span class="wjp-legend-dot" style="background-color: #14b8a6;"></span><?php echo esc_html__('New Jobs', 'wp-job-portal'); ?></span>
                            </div>
                        </div>
                        <div class="wjp-chart-container"><canvas id="applicationChart"></canvas></div>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($wjp_options['quick_stats'])) : ?>
                    <div id="wjp-quick-stats" class="wjp-card">
                        <div class="wjp-h3"><?php echo esc_html__('Quick Stats', 'wp-job-portal'); ?></div>
                        <div class="wjp-stats-list">
                            <div class="wjp-stat-item"><div class="wjp-stat-icon-wrapper wjp-bg-amber"><i class="fas fa-file-invoice"></i></div><div class="wjp-stat-info"><p class="wjp-stat-label"><?php echo esc_html__('Pending Jobs', 'wp-job-portal'); ?></p><p class="wjp-stat-value"><?php echo esc_html(wpjobportal::$_data['quick_stats']['pending_jobs']); ?></p></div></div>
                            <div class="wjp-stat-item"><div class="wjp-stat-icon-wrapper wjp-bg-sky"><i class="fas fa-user-plus"></i></div><div class="wjp-stat-info"><p class="wjp-stat-label"><?php echo esc_html__('New Applicants', 'wp-job-portal'); ?></p><p class="wjp-stat-value"><?php echo esc_html(wpjobportal::$_data['quick_stats']['new_applicants']); ?></p></div></div>
                            <?php if(in_array('credits', wpjobportal::$_active_addons)){ ?>
                                <div class="wjp-stat-item"><div class="wjp-stat-icon-wrapper wjp-bg-indigo"><i class="fas fa-tags"></i></div><div class="wjp-stat-info"><p class="wjp-stat-label"><?php echo esc_html__('Active Subscriptions', 'wp-job-portal'); ?></p><p class="wjp-stat-value"><?php echo esc_html(wpjobportal::$_data['quick_stats']['active_subscriptions']); ?></p></div></div>
                            <?php } ?>
                            <div class="wjp-stat-item"><div class="wjp-stat-icon-wrapper wjp-bg-green"><i class="fas fa-users"></i></div><div class="wjp-stat-info"><p class="wjp-stat-label"><?php echo esc_html__('Total Users', 'wp-job-portal'); ?></p><p class="wjp-stat-value"><?php echo esc_html(wpjobportal::$_data['quick_stats']['total_users']); ?></p></div></div>
                            <div class="wjp-stat-item"><div class="wjp-stat-icon-wrapper wjp-bg-red"><i class="fas fa-times-circle"></i></div><div class="wjp-stat-info"><p class="wjp-stat-label"><?php echo esc_html__('Closed Jobs', 'wp-job-portal'); ?></p><p class="wjp-stat-value"><?php echo esc_html(wpjobportal::$_data['quick_stats']['closed_jobs']); ?></p></div></div>
                            <?php if(in_array('credits', wpjobportal::$_active_addons)){ ?>
                                <div class="wjp-stat-item"><div class="wjp-stat-icon-wrapper wjp-bg-emerald"><i class="fas fa-dollar-sign"></i></div><div class="wjp-stat-info"><p class="wjp-stat-label"><?php echo esc_html__('Monthly Revenue', 'wp-job-portal'); ?></p><p class="wjp-stat-value">$<?php echo esc_html(number_format(wpjobportal::$_data['quick_stats']['monthly_revenue'], 2)); ?></p></div></div>
                            <?php } ?>

                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if (isset($wjp_options['recent_jobs'])) : ?>
                <div id="wjp-recent-jobs" class="wjp-card">
                    <div class="wjp-h3"><?php echo esc_html__('Recent Job Postings', 'wp-job-portal'); ?></div>
                    <div class="wjp-table-wrapper">
                        <?php
                        // field ordering check
                        $job_listing_fields = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldOrderingData(2);
                        ?>
                        <table class="wjp-table">
                            <thead>
                                <tr><th><?php echo esc_html__('Job Title', 'wp-job-portal'); ?></th>
                                    <th><?php echo esc_html__('Company', 'wp-job-portal'); ?></th>
                                    <?php if(isset($job_listing_fields['jobcategory']) && $job_listing_fields['jobcategory'] !='' ){ ?>
                                        <th><?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($job_listing_fields['jobcategory'])); ?></th>
                                    <?php } ?>
                                    <th><?php echo esc_html__('Date Posted', 'wp-job-portal'); ?></th>
                                    <th><?php echo esc_html__('Status', 'wp-job-portal'); ?></th>
                                    <th><?php echo esc_html__('Actions', 'wp-job-portal'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(isset(wpjobportal::$_data[0]['latestjobs']) && !empty(wpjobportal::$_data[0]['latestjobs'])){
                                    foreach (wpjobportal::$_data[0]['latestjobs'] AS $job) { ?>
                                    <tr>
                                        <td class="wjp-job-title"><a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_job&wpjobportallt=formjob&wpjobportalid='.$job->id)); ?>"><?php echo esc_html($job->title); ?></a></td>
                                        <td class="wjp-company-name"><a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_company&wpjobportallt=formcompany&wpjobportalid='.$job->companyid)); ?>"><?php echo esc_html($job->companyname); ?></a></td>
                                        <?php if(isset($job_listing_fields['jobcategory']) && $job_listing_fields['jobcategory'] !='' ){ ?>
                                            <td><?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($job->cat_title)); ?></td>
                                        <?php } ?>
                                        <td><?php echo esc_html(human_time_diff(strtotime($job->created) ,strtotime(date_i18n("Y-m-d H:i:s")))).' '.esc_html(__("Ago",'wp-job-portal')); ?></td>
                                        <td>
                                            <?php
                                            $status_class = ($job->status == 1) ? 'wjp-status-active' : 'wjp-status-closed';
                                            $status_label = ($job->status == 1) ? esc_html__('Approved', 'wp-job-portal') : esc_html__('Pending', 'wp-job-portal');
                                            ?>
                                            <span class="wjp-status-badge <?php echo $status_class; ?>">
                                                <?php echo $status_label; ?>
                                            </span>
                                        </td>
                                        <td class="wjp-table-actions"><a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_job&wpjobportallt=formjob&wpjobportalid='.$job->id)); ?>"><?php echo esc_html__('Edit', 'wp-job-portal'); ?></a></td>
                                    </tr>
                                <?php }
                                } else { ?>
                                    <tr class="wjp-no-records">
                                        <td colspan="6"><?php echo esc_html__('No recent job postings found.', 'wp-job-portal'); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
                <div id="wjp-new-charts" class="">
                    <?php if (isset($wjp_options['jobs_by_status_chart'])) : ?>
                    <div class="wjp-chart-card">
                        <div class="wjp-h3"><?php echo esc_html__('Jobs by Status', 'wp-job-portal'); ?></div>
                        <div class="wjp-chart-container"><canvas id="jobsStatusChart"></canvas></div>
                    </div>
                    <?php endif; ?>
                    <?php if (isset($wjp_options['top_categories_chart'])) : ?>
                    <div class="wjp-chart-card">
                        <div class="wjp-h3"><?php echo esc_html__('Top Job Categories', 'wp-job-portal'); ?></div>
                        <div class="wjp-chart-container"><canvas id="topCategoriesChart"></canvas></div>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="wjp-grid-section">
                    <?php if (isset($wjp_options['latest_job_applies'])) : ?>
                    <div id="" class="wjp-card wjp-new-sections">
                        <div class="wjp-card-header"><div class="wjp-h3" style="margin-bottom:0;"><?php echo esc_html__('Latest Job Applies', 'wp-job-portal'); ?></div></div>
                        <div class="wjp-list wjp-apply-list">
                            <?php if (isset(wpjobportal::$_data[0]['latest_applies']) && !empty(wpjobportal::$_data[0]['latest_applies'])) {
                                $data_directory = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('data_directory');
                                foreach(wpjobportal::$_data[0]['latest_applies'] as $apply) {

                                    $logo = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('employer');
                                    if (isset($apply->logo) && $apply->logo != '') {
                                        $wpdir = wp_upload_dir();
                                        $logo = $wpdir['baseurl'] . '/' . $data_directory.'/data/employer/comp_'.$apply->companyid.'/logo/'. $apply->logo;
                                    }
                                    ?>
                                    <div class="wjp-apply-item">
                                        <div class="wjp-apply-item-header">
                                            <div class="wjp-applicant-info">
                                                <img src="<?php echo esc_attr($logo)?>" alt="<?php echo esc_attr($apply->first_name); ?>" class="wjp-avatar">
                                                <div class="wjp-applicant-text">
                                                    <p class="wjp-text-1"><strong><a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_resume&wpjobportallt=viewresume&wpjobportalid='.(isset($apply->resumeid) ? $apply->resumeid : ''))); ?>"><?php echo esc_html($apply->first_name . ' ' . $apply->last_name); ?></a></strong> <?php echo esc_html__('applied for', 'wp-job-portal'); ?></p>
                                                    <p class="wjp-job-title"><a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_job&wpjobportallt=formjob&wpjobportalid='.(isset($apply->jobid) ? $apply->jobid : ''))); ?>"><?php echo esc_html($apply->job_title); ?></a></p>
                                                    <p class="wjp-company-loc"><?php echo esc_html__('at', 'wp-job-portal'); ?> <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_company&wpjobportallt=formcompany&wpjobportalid='.(isset($apply->companyid) ? $apply->companyid : ''))); ?>"><?php echo esc_html($apply->company_name); ?></a></p>
                                                </div>
                                            </div>
                                            <span class="wjp-timestamp"><?php echo esc_html(human_time_diff(strtotime($apply->apply_date) ,strtotime(date_i18n("Y-m-d H:i:s")))).' '.esc_html(__("ago",'wp-job-portal')); ?></span>
                                        </div>
                                        <div class="wjp-item-footer">
                                            <div class="wjp-tag-list">
                                                <span class="wjp-tag wjp-tag-sky"><?php echo esc_html($apply->jobtype_title); ?></span>
                                                <span class="wjp-tag wjp-tag-slate"><i class="fas fa-map-marker-alt"></i> <?php echo esc_html(WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($apply->city)); ?></span>
                                                <?php /*
                                                <span class="wjp-tag wjp-tag-emerald">
                                                    <?php echo esc_html(wpjobportal::$_common->getSalaryRangeView($apply->salarytype, $apply->salarymin, $apply->salarymax,$apply->currency)); ?>
                                                    <?php if($apply->salarytype==3 || $apply->salarytype==2) { ?>
                                                        <span class="wpjobportal-salary-type"> <?php echo ' / ' .esc_html(wpjobportal::wpjobportal_getVariableValue($apply->salaryrangetype)); ?></span>
                                                    <?php }?>
                                                </span>
                                             */ ?>
                                            </div>


                                            <div class="wjp-actions"><a href="<?php echo esc_url_raw(admin_url('admin.php?page=wpjobportal_jobapply&wpjobportallt=jobappliedresume&jobid='.$apply->jobid)); ?>" class="wjp-btn-xs wjp-btn-indigo"><?php echo esc_html__('View App', 'wp-job-portal'); ?></a></div>
                                        </div>
                                    </div>
                                <?php }
                            } else { ?>
                                <div class="wjp-no-records"><?php echo esc_html__('No recent job applications to display.', 'wp-job-portal'); ?></div>
                            <?php } ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if (isset($wjp_options['latest_resumes'])) : ?>
                    <div id="" class="wjp-card wjp-new-sections">
                         <div class="wjp-card-header"><div class="wjp-h3" style="margin-bottom:0;"><?php echo esc_html__('Latest Resumes', 'wp-job-portal'); ?></div><a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_resume')); ?>" class="wjp-view-all-link"><?php echo esc_html__('View All', 'wp-job-portal'); ?></a></div>
                         <div class="wjp-list">
                            <?php if(isset(wpjobportal::$_data[0]['latestresumes']) && !empty(wpjobportal::$_data[0]['latestresumes'])){
                                $data_directory = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('data_directory');
                                foreach(wpjobportal::$_data[0]['latestresumes'] AS $resume){
                                $photo = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('jobseeker');
                                if (isset($resume->photo) && $resume->photo != '') {
                                    $wpdir = wp_upload_dir();
                                    $photo = $wpdir['baseurl'] . '/' . $data_directory . '/data/jobseeker/resume_' . $resume->id. '/photo/' . $resume->photo;
                                }
                                ?>
                                <div class="wjp-list-item">
                                    <div class="wjp-item-main-info">
                                        <img src="<?php echo esc_attr($photo); ?>" alt="<?php echo esc_attr($resume->application_title); ?>" class="wjp-avatar">
                                        <div class="wjp-item-text"><p class="wjp-name"><a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_resume&wpjobportallt=viewresume&wpjobportalid='.$resume->id)); ?>"><?php echo esc_html($resume->first_name . ' ' . $resume->last_name); ?></a></p><p class="wjp-subtext"> <?php echo esc_html($resume->application_title); ?></p>
                                            <div class="wjp-tag-list" style="margin-top: 0.25rem;">
                                                <span class="wjp-tag wjp-tag-sky"><?php echo esc_html($resume->jobtypetitle); ?></span>
                                                <span class="wjp-tag wjp-tag-slate"><?php echo esc_html($resume->cat_title); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="wjp-item-aside-info"><span class="wjp-tag wjp-tag-blue"><?php echo esc_html__('New', 'wp-job-portal'); ?></span><p class="wjp-date"><?php echo esc_html(human_time_diff(strtotime($resume->created) ,strtotime(date_i18n("Y-m-d H:i:s")))).' '.esc_html(__("Ago",'wp-job-portal')); ?></p></div>
                                </div>
                            <?php }
                            } else { ?>
                                <div class="wjp-no-records"><?php echo esc_html__('No new resumes have been added recently.', 'wp-job-portal'); ?></div>
                            <?php } ?>
                         </div>
                    </div>
                    <?php endif; ?>

                <?php if(in_array('credits', wpjobportal::$_active_addons)){ ?>

                        <?php if (isset($wjp_options['latest_subscriptions'])) : ?>
                        <div id="" class="wjp-card wjp-financials">
                            <div class="wjp-h3"><?php echo esc_html__('Latest Package Subscriptions', 'wp-job-portal'); ?></div>
                            <div class="wjp-list">
                                <?php if (isset(wpjobportal::$_data[0]['latest_subscriptions']) && !empty(wpjobportal::$_data[0]['latest_subscriptions'])) {
                                    foreach(wpjobportal::$_data[0]['latest_subscriptions'] as $sub) { ?>
                                    <div class="wjp-list-item wjp-list-item-simple">
                                        <div class="wjp-item-main-info">
                                            <?php
                                            $photo = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('jobseeker');
                                            if (isset($sub->photo) && $sub->photo != '') {
                                                $wpdir = wp_upload_dir();
                                                $data_directory = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('data_directory');
                                                $photo = $wpdir['baseurl'] . '/' . $data_directory . '/data/profile/profile_' . esc_attr($sub->uid) . '/profile/' . $sub->photo;
                                            }
                                            ?>
                                            <img src="<?php echo esc_attr($photo);?>" alt="<?php echo esc_attr($sub->first_name); ?> Logo" class="wjp-logo">
                                            <p>
                                                <span class="wjp-company-name">
                                                    <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_user&wpjobportallt=userdetail&id='.(isset($sub->uid) ? $sub->uid : ''))); ?>">
                                                        <?php echo esc_html($sub->first_name . ' ' . $sub->last_name); ?>
                                                    </a>
                                                </span>
                                                <?php echo esc_html__('subscribed to', 'wp-job-portal'); ?>
                                                <span class="wjp-plan-pro"><?php echo esc_html($sub->package_name); ?></span>.
                                            </p>
                                        </div>
                                        <span class="wjp-timestamp"><?php echo esc_html(human_time_diff(strtotime($sub->created) ,strtotime(date_i18n("Y-m-d H:i:s")))).' '.esc_html(__("ago",'wp-job-portal')); ?></span>
                                    </div>
                                <?php }
                                } else { ?>
                                    <div class="wjp-list-item wjp-list-item-simple"><p><?php echo esc_html__('No new subscriptions found.', 'wp-job-portal'); ?></p></div>
                                <?php } ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (isset($wjp_options['latest_payments'])) : ?>
                        <div id="" class="wjp-card wjp-financials">
                            <div class="wjp-h3"><?php echo esc_html__('Latest Payments', 'wp-job-portal'); ?></div>
                             <div class="wjp-list">
                                 <?php if (isset(wpjobportal::$_data[0]['latest_payments']) && !empty(wpjobportal::$_data[0]['latest_payments'])) {
                                    foreach(wpjobportal::$_data[0]['latest_payments'] as $payment) { ?>

                                    <div class="wjp-list-item wjp-list-item-simple">
                                        <div class="wjp-item-main-info">
                                            <?php
                                            $photo = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('jobseeker');
                                            if (isset($sub->photo) && $sub->photo != '') {
                                                $wpdir = wp_upload_dir();
                                                $data_directory = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('data_directory');
                                                $photo = $wpdir['baseurl'] . '/' . $data_directory . '/data/profile/profile_' . esc_attr($sub->uid) . '/profile/' . $sub->photo;
                                            }
                                            ?>
                                            <img src="<?php echo esc_attr($photo);?>" alt="<?php echo esc_attr($payment->payer_name); ?> Logo" class="wjp-logo">
                                            <div class="wjp-item-text">
                                                <p class="wjp-name"><?php echo esc_html($payment->payer_name); ?></p>
                                                <p class="wjp-subtext"><?php echo esc_html($payment->description); ?></p>
                                            </div>
                                        </div>
                                        <div class="wjp-item-aside-info">
                                            <p class="wjp-amount"><?php echo esc_html($payment->symbol) . esc_html($payment->amount); ?></p>
                                        </div>
                                    </div>
                                 <?php }
                                 } else { ?>
                                    <div class="wjp-no-records"><?php echo esc_html__('No recent payments to display.', 'wp-job-portal'); ?></div>
                                 <?php } ?>
                             </div>
                        </div>
                        <?php endif; ?>

                <?php } ?>

                    <?php if (isset($wjp_options['latest_activity'])) : ?>
                    <div id="" class="wjp-card wjp-system-logs wjp-col-span-3">
                        <div class="wjp-h3"><?php echo esc_html__('Latest Activity', 'wp-job-portal'); ?></div>
                        <div class="wjp-list">
                            <?php if (isset(wpjobportal::$_data[0]['latest_activity']) && !empty(wpjobportal::$_data[0]['latest_activity'])) {
                                foreach(wpjobportal::$_data[0]['latest_activity'] as $log) {
                                    $icon_config = $log->icon_config;
                                    //$icon_config = function_exists('getActivityLogIconConfigForDashboard') ? getActivityLogIconConfigForDashboard($log->description) : ['icon' => 'fas fa-info', 'bg_class' => 'wjp-bg-slate'];
                                    ?>
                                <div class="wjp-activity-item">
                                    <div class="wjp-activity-icon <?php echo esc_attr($icon_config['bg_class']); ?>"><i class="<?php echo esc_attr($icon_config['icon']); ?>"></i></div>
                                    <div class="wjp-activity-text">
                                        <p><?php echo isset($log->description) ? wp_kses_post($log->description) : ''; ?></p>
                                        <p class="wjp-subtext"><?php echo isset($log->created) ? esc_html(human_time_diff(strtotime($log->created) ,strtotime(date_i18n("Y-m-d H:i:s")))).' '.esc_html(__("ago",'wp-job-portal')) : ''; ?></p>
                                    </div>
                                </div>
                            <?php }
                            } ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if (isset($wjp_options['system_error_log'])) : ?>
                    <div id="" class="wjp-card wjp-system-logs wjp-col-span-2">
                        <div class="wjp-h3"><?php echo esc_html__('System Error Log', 'wp-job-portal'); ?></div>
                        <div class="wjp-list">
                           <?php if (isset(wpjobportal::$_data[0]['latest_errors']) && !empty(wpjobportal::$_data[0]['latest_errors'])) {
                                foreach(wpjobportal::$_data[0]['latest_errors'] as $log) {
                                    $log_class = get_error_log_class($log->error);
                                    ?>
                                <div class="wjp-list-item wjp-list-item-simple">
                                    <div class="wjp-item-text">
                                        <p class="wjp-log-text <?php echo esc_attr($log_class); ?>"><?php echo esc_html($log->error); ?></p>
                                        <p class="wjp-subtext"><?php echo esc_html(human_time_diff(strtotime($log->created), current_time('timestamp'))) . ' ' . esc_html__('ago', 'wp-job-portal'); ?></p>
                                    </div>

                                </div>
                            <?php }
                           } else { ?>
                                <div class="wjp-no-records"><?php echo esc_html__('Hooray! No system errors to report.', 'wp-job-portal'); ?></div>
                           <?php } ?>
                        </div>
                    </div>
                    <?php endif; ?>


                    <?php if (isset($wjp_options['latest_job_seekers'])) : ?>
                    <div id="" class="wjp-card wjp-latest-members">
                        <div class="wjp-h3"><?php echo esc_html__('Latest Job Seekers', 'wp-job-portal'); ?></div>
                         <div class="wjp-list">
                            <?php if (isset(wpjobportal::$_data[0]['latest_jobseekers']) && !empty(wpjobportal::$_data[0]['latest_jobseekers'])) {
                                foreach(wpjobportal::$_data[0]['latest_jobseekers'] as $seeker) { ?>
                                <div class="wjp-list-item wjp-list-item-simple">
                                    <div class="wjp-item-main-info">
                                        <?php
                                        $photo = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('jobseeker');
                                        if (isset($seeker->photo) && $seeker->photo != '') {
                                            $wpdir = wp_upload_dir();
                                            $data_directory = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('data_directory');
                                            $photo = $wpdir['baseurl'] . '/' . $data_directory . '/data/profile/profile_' . esc_attr($seeker->id) . '/profile/' . $seeker->photo;
                                        }
                                        ?>
                                        <img src="<?php echo esc_attr($photo) ?>" alt="<?php echo esc_attr($seeker->title); ?>" class="wjp-avatar">
                                        <div class="wjp-item-text">
                                            <p class="wjp-name"><a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_user&wpjobportallt=userdetail&id='.(isset($seeker->id) ? $seeker->id : ''))); ?>"><?php echo esc_html($seeker->username); ?></a></p>
                                            <p class="wjp-subtext"><?php echo esc_html($seeker->title); ?></p>
                                            <p class="wjp-subtext"><?php echo esc_html__('Joined', 'wp-job-portal'); ?>: <?php echo !empty($seeker->created) ? esc_html(date_i18n(get_option('date_format'), strtotime($seeker->created))) : ''; ?></p>
                                        </div>
                                    </div>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_user&wpjobportallt=userdetail&id='.(isset($seeker->id) ? $seeker->id : ''))); ?>" class="wjp-view-all-link"><?php echo esc_html__('View Profile', 'wp-job-portal'); ?></a>
                                </div>
                            <?php }
                            } else { ?>
                                <div class="wjp-no-records"><?php echo esc_html__('No new job seekers have registered.', 'wp-job-portal'); ?></div>
                            <?php } ?>
                         </div>
                    </div>
                    <?php endif; ?>
                    <?php if (isset($wjp_options['latest_employers'])) : ?>
                    <div id="" class="wjp-card wjp-latest-members">
                        <div class="wjp-h3"><?php echo esc_html__('Latest Employers', 'wp-job-portal'); ?></div>
                        <div class="wjp-list">
                            <?php if (isset(wpjobportal::$_data[0]['latest_employers']) && !empty(wpjobportal::$_data[0]['latest_employers'])) {
                                foreach(wpjobportal::$_data[0]['latest_employers'] as $employer) { ?>
                                <div class="wjp-list-item wjp-list-item-simple">
                                    <div class="wjp-item-main-info">
                                        <?php
                                        $photo = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('jobseeker');
                                        if (isset($employer->photo) && $employer->photo != '') {
                                            $wpdir = wp_upload_dir();
                                            $data_directory = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('data_directory');
                                            $photo = $wpdir['baseurl'] . '/' . $data_directory . '/data/profile/profile_' . esc_attr($employer->id) . '/profile/' . $employer->photo;
                                        }
                                        ?>
                                        <img src="<?php echo esc_attr($photo) ?>" alt="<?php echo esc_attr($employer->title); ?> Logo" class="wjp-logo">
                                        <div class="wjp-item-text">
                                            <p class="wjp-name"><a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_user&wpjobportallt=userdetail&id='.(isset($employer->id) ? $employer->id : ''))); ?>"><?php echo esc_html($employer->username); ?></a></p>
                                            <p class="wjp-subtext"><?php echo esc_html__('Joined', 'wp-job-portal'); ?>: <?php echo !empty($employer->created) ? esc_html(date_i18n(get_option('date_format'), strtotime($employer->created))) : ''; ?></p>
                                        </div>
                                    </div>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_user&wpjobportallt=userdetail&id='.(isset($employer->id) ? $employer->id : ''))); ?>" class="wjp-view-all-link"><?php echo esc_html__('View Profile', 'wp-job-portal'); ?></a>
                                </div>
                            <?php }
                            } else { ?>
                                <div class="wjp-no-records"><?php echo esc_html__('No new employers have registered.', 'wp-job-portal'); ?></div>
                            <?php } ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                </main>
        </div>
    </div>
</div>
<?php
$wojobportal_js = '
    jQuery(document).ready(function($) {
        // --- DROPDOWN LOGIC ---
        const $dropdownMenu = $("#wjp-screen-options-menu");
        const $dropdownButton = $("#wjp-screen-options-btn");

        $dropdownButton.on("click", function(event) {
            event.preventDefault();
            event.stopPropagation();
            $dropdownMenu.toggle();
        });

        $(".wjp-close-dropdown").on("click", function(event) {
            event.preventDefault();
            $dropdownMenu.hide();
        });

        $(document).on("click", function(event) {
            if ($dropdownMenu.is(":visible") && !$(event.target).closest($dropdownMenu).length && !$(event.target).closest($dropdownButton).length) {
                $dropdownMenu.hide();
            }
        });

        // --- CHARTS INITIALIZATION ---
        const chartColors = {
            primary: "#4f46e5",
            secondary: "#0ea5e9",
            success: "#10b981",
            warning: "#f59e0b",
            danger: "#ef4444",
            slateBg: "#e2e8f0",
            borderColor: "#e2e8f0",
            teal: "#14b8a6",
            tealBg: "rgba(20, 184, 166, 0.1)",
            primaryTransparent: "rgba(79, 70, 229, 0.1)"
        };

        if (jQuery("#applicationChart").length) {
            new Chart($("#applicationChart")[0].getContext("2d"), {
                type: "line",
                data: {
                    labels: '. (isset(wpjobportal::$_data['platform_growth']['labels']) ? json_encode(wpjobportal::$_data['platform_growth']['labels']) : '[]') .',
                    datasets: [
                        {
                            label: "'. esc_js(__('Applications', 'wp-job-portal')) .'",
                            data: '. (isset(wpjobportal::$_data['platform_growth']['applies']) ? json_encode(wpjobportal::$_data['platform_growth']['applies']) : '[]') .',
                            borderColor: chartColors.primary,
                            backgroundColor: chartColors.primaryTransparent,
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: chartColors.primary,
                            pointRadius: 5,
                            pointHoverRadius: 7
                        },
                        {
                            label: "'. esc_js(__('New Jobs', 'wp-job-portal')) .'",
                            data: '. (isset(wpjobportal::$_data['platform_growth']['jobs']) ? json_encode(wpjobportal::$_data['platform_growth']['jobs']) : '[]') .',
                            borderColor: chartColors.teal,
                            backgroundColor: chartColors.tealBg,
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: chartColors.teal,
                            pointRadius: 5,
                            pointHoverRadius: 7
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: chartColors.borderColor }
                        },
                        x: {
                            grid: { display: false }
                        }
                    },
                    plugins: {
                        legend: { display: false }
                    },
                    interaction: {
                        intersect: false,
                        mode: "index"
                    }
                }
            });
        }

        if (jQuery("#jobsStatusChart").length) {
            new Chart($("#jobsStatusChart")[0].getContext("2d"), {
                type: "doughnut",
                data: {
                    labels: '. (isset(wpjobportal::$_data['jobs_by_status']['labels']) ? json_encode(wpjobportal::$_data['jobs_by_status']['labels']) : '[]') .',
                    datasets: [{
                        data: '. (isset(wpjobportal::$_data['jobs_by_status']['data']) ? json_encode(wpjobportal::$_data['jobs_by_status']['data']) : '[]') .',
                        backgroundColor: [chartColors.primary, chartColors.secondary, chartColors.warning, chartColors.slateBg, chartColors.success, chartColors.danger],
                        hoverOffset: 4,
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: "70%",
                    plugins: {
                        legend: {
                            position: "bottom",
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8,
                                padding: 15
                            }
                        }
                    }
                }
            });
        }

        if (jQuery("#topCategoriesChart").length) {
            new Chart($("#topCategoriesChart")[0].getContext("2d"), {
                type: "bar",
                data: {
                    labels: '. (isset(wpjobportal::$_data['top_categories']['labels']) ? json_encode(wpjobportal::$_data['top_categories']['labels']) : '[]') .',
                    datasets: [{
                        data: '. (isset(wpjobportal::$_data['top_categories']['data']) ? json_encode(wpjobportal::$_data['top_categories']['data']) : '[]') .',
                        backgroundColor: [chartColors.primary, chartColors.secondary, chartColors.success, chartColors.warning, chartColors.danger],
                        borderRadius: 4
                    }]
                },
                options: {
                    indexAxis: "y",
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: { color: chartColors.borderColor }
                        },
                        y: {
                            grid: { display: false }
                        }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }
    });
';

wp_add_inline_script('wp-job-portal-dashboard-js', $wojobportal_js);
?>