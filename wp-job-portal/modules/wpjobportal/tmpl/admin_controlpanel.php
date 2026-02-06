<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

wp_enqueue_style('wpjobportal-redesign-css', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/wpjobportaladmin_redesign.css');
// Enqueue necessary scripts and styles
// wp_enqueue_style('wjp-font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
// wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js');
// Safely load external CSS/JS

wp_enqueue_script( 'jp-google-charts', esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/js/google-charts.js', array(), '1.1.1', false );
wp_enqueue_style('status-graph', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/status_graph.css');


// Register a handle for our inline script
wp_register_script('wp-job-portal-dashboard-js', '');
wp_enqueue_script('wp-job-portal-dashboard-js');

// Define default options and get the user's saved options from the wp_options table
$wpjobportal_wjp_dashboard_defaults = [
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
$wpjobportal_wjp_options = get_option('wjp_dashboard_screen_options', $wpjobportal_wjp_dashboard_defaults);

/**
 * Helper function to determine the CSS class for a system error log entry.
 *
 * @param string $wpjobportal_error_message The error message text.
 * @return string The appropriate CSS class.
 */
if (!function_exists('wpjobportal_get_error_log_class')) {
    function wpjobportal_get_error_log_class($wpjobportal_error_message) {
        $wpjobportal_error_message_lower = strtolower($wpjobportal_error_message);
        if (str_contains($wpjobportal_error_message_lower, 'error') || str_contains($wpjobportal_error_message_lower, 'failed') || str_contains($wpjobportal_error_message_lower, 'fatal')) {
            return 'wjp-log-error';
        } elseif (str_contains($wpjobportal_error_message_lower, 'warning')) {
            return 'wjp-log-warning';
        } elseif (str_contains($wpjobportal_error_message_lower, 'notice')) {
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
                                <svg xmlns="http://www.w3.org/2000/svg" class="wjp-btn-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="4" y1="21" x2="4" y2="14"></line>
                                    <line x1="4" y1="10" x2="4" y2="3"></line>
                                    <line x1="12" y1="21" x2="12" y2="12"></line>
                                    <line x1="12" y1="8" x2="12" y2="3"></line>
                                    <line x1="20" y1="21" x2="20" y2="16"></line>
                                    <line x1="20" y1="12" x2="20" y2="3"></line>
                                    <line x1="1" y1="14" x2="7" y2="14"></line>
                                    <line x1="9" y1="8" x2="15" y2="8"></line>
                                    <line x1="17" y1="16" x2="23" y2="16"></line>
                                </svg>
                                Options
                            </a>

                            <div id="wjp-screen-options-menu" class="wjp-so-popup">
                               <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=wpjobportal&task=savescreenoptions&action=wpjobportaltask')); ?>">
                                    <?php // NONCE & ACTION FIELDS - DO NOT REMOVE ?>
                                    <input type="hidden" name="action" value="wpjobportaltask">
                                    <?php wp_nonce_field('wjp_dashboard_options_nonce', 'wjp_dashboard_nonce'); ?>

                                    <div class="wjp-so-header">
                                        <div class="wjp-so-title"><?php echo esc_html__('Customize Your Dashboard', 'wp-job-portal'); ?></div>
                                        <a href="#" class="wjp-so-close wjp-close-dropdown"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="18" y1="6" x2="6" y2="18"></line>
                                            <line x1="6" y1="6" x2="18" y2="18"></line>
                                        </svg>
                                        </a>
                                    </div>


                                    <div class="wjp-so-content">

                                        <div class="wjp-so-section">
                                            <div class="wjp-so-section-title"><?php echo esc_html__('Core Components', 'wp-job-portal'); ?></div>
                                            <div class="wjp-so-grid">
                                                <div class="wjp-so-item">
                                                    <div class="wjp-so-item-label">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                                                        </svg>
                                                        <span><?php echo esc_html__('Quick Actions', 'wp-job-portal'); ?></span>
                                                    </div>
                                                    <label class="wjp-so-toggle">
                                                        <input type="checkbox" name="wjp_screen_options[quick_actions]" <?php checked( isset($wpjobportal_wjp_options['quick_actions']) ? $wpjobportal_wjp_options['quick_actions'] : '', 'on' ); ?>>
                                                        <span class="wjp-so-slider"></span>
                                                    </label>
                                                </div>
                                                <div class="wjp-so-item">
                                                    <div class="wjp-so-item-label">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                                        </svg>
                                                        <span><?php echo esc_html__('Recent Jobs', 'wp-job-portal'); ?></span>
                                                    </div>
                                                    <label class="wjp-so-toggle">
                                                        <input type="checkbox" name="wjp_screen_options[recent_jobs]" <?php checked( isset($wpjobportal_wjp_options['recent_jobs']) ? $wpjobportal_wjp_options['recent_jobs'] : '', 'on' ); ?>>
                                                        <span class="wjp-so-slider"></span>
                                                    </label>
                                                </div>
                                                <div class="wjp-so-item">
                                                    <div class="wjp-so-item-label">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M22 12A10 10 0 0 0 12 2v10z"></path>
                                                        </svg>
                                                        <span><?php echo esc_html__('Quick Stats', 'wp-job-portal'); ?></span>
                                                    </div>
                                                    <label class="wjp-so-toggle">
                                                        <input type="checkbox" name="wjp_screen_options[quick_stats]" <?php checked( isset($wpjobportal_wjp_options['quick_stats']) ? $wpjobportal_wjp_options['quick_stats'] : '', 'on' ); ?>>
                                                        <span class="wjp-so-slider"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="wjp-so-section">
                                            <div class="wjp-so-section-title"><?php echo esc_html__('Visualizations', 'wp-job-portal'); ?></div>
                                            <div class="wjp-so-grid">
                                                <div class="wjp-so-item">
                                                    <div class="wjp-so-item-label">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                                                        </svg>
                                                        <span><?php echo esc_html__('Platform Growth', 'wp-job-portal'); ?></span>
                                                    </div>
                                                    <label class="wjp-so-toggle">
                                                        <input type="checkbox" name="wjp_screen_options[platform_growth]" <?php checked( isset($wpjobportal_wjp_options['platform_growth']) ? $wpjobportal_wjp_options['platform_growth'] : '', 'on' ); ?>>
                                                        <span class="wjp-so-slider"></span>
                                                    </label>
                                                </div>
                                                <div class="wjp-so-item">
                                                    <div class="wjp-so-item-label">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path>
                                                            <path d="M22 12A10 10 0 0 0 12 2v10z"></path>
                                                        </svg>
                                                        <span><?php echo esc_html__('Jobs by Status', 'wp-job-portal'); ?></span>
                                                    </div>
                                                    <label class="wjp-so-toggle">
                                                        <input type="checkbox" name="wjp_screen_options[jobs_by_status_chart]" <?php checked( isset($wpjobportal_wjp_options['jobs_by_status_chart']) ? $wpjobportal_wjp_options['jobs_by_status_chart'] : '', 'on' ); ?>>
                                                        <span class="wjp-so-slider"></span>
                                                    </label>
                                                </div>
                                                <div class="wjp-so-item">
                                                    <div class="wjp-so-item-label">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <line x1="12" y1="20" x2="12" y2="10"></line>
                                                            <line x1="18" y1="20" x2="18" y2="4"></line>
                                                            <line x1="6" y1="20" x2="6" y2="16"></line>
                                                        </svg>
                                                        <span><?php echo esc_html__('Top Categories', 'wp-job-portal'); ?></span>
                                                    </div>
                                                    <label class="wjp-so-toggle">
                                                        <input type="checkbox" name="wjp_screen_options[top_categories_chart]" <?php checked( isset($wpjobportal_wjp_options['top_categories_chart']) ? $wpjobportal_wjp_options['top_categories_chart'] : '', 'on' ); ?>>
                                                        <span class="wjp-so-slider"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="wjp-so-section">
                                            <div class="wjp-so-section-title"><?php echo esc_html__('Activity & System Feeds', 'wp-job-portal'); ?></div>
                                            <div class="wjp-so-grid">
                                                <div class="wjp-so-item">
                                                    <div class="wjp-so-item-label">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                                            <polyline points="14 2 14 8 20 8"></polyline>
                                                            <path d="M12 18v-3l3-3 3 3v3H12z"></path>
                                                        </svg>
                                                        <span><?php echo esc_html__('Latest Applies', 'wp-job-portal'); ?></span>
                                                    </div>
                                                    <label class="wjp-so-toggle">
                                                        <input type="checkbox" name="wjp_screen_options[latest_job_applies]" <?php checked( isset($wpjobportal_wjp_options['latest_job_applies']) ? $wpjobportal_wjp_options['latest_job_applies'] : '', 'on' ); ?>>
                                                        <span class="wjp-so-slider"></span>
                                                    </label>
                                                </div>
                                                <div class="wjp-so-item">
                                                    <div class="wjp-so-item-label">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                                            <polyline points="14 2 14 8 20 8"></polyline>
                                                            <line x1="16" y1="13" x2="8" y2="13"></line>
                                                            <line x1="16" y1="17" x2="8" y2="17"></line>
                                                            <polyline points="10 9 9 9 8 9"></polyline>
                                                        </svg>
                                                        <span><?php echo esc_html__('Latest Resumes', 'wp-job-portal'); ?></span>
                                                    </div>
                                                    <label class="wjp-so-toggle">
                                                        <input type="checkbox" name="wjp_screen_options[latest_resumes]" <?php checked( isset($wpjobportal_wjp_options['latest_resumes']) ? $wpjobportal_wjp_options['latest_resumes'] : '', 'on' ); ?>>
                                                        <span class="wjp-so-slider"></span>
                                                    </label>
                                                </div>
                                                <div class="wjp-so-item">
                                                    <div class="wjp-so-item-label">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <circle cx="12" cy="12" r="10"></circle>
                                                            <polyline points="12 6 12 12 16 14"></polyline>
                                                        </svg>
                                                        <span><?php echo esc_html__('Activity Log', 'wp-job-portal'); ?></span>
                                                    </div>
                                                    <label class="wjp-so-toggle">
                                                        <input type="checkbox" name="wjp_screen_options[latest_activity]" <?php checked( isset($wpjobportal_wjp_options['latest_activity']) ? $wpjobportal_wjp_options['latest_activity'] : '', 'on' ); ?>>
                                                        <span class="wjp-so-slider"></span>
                                                    </label>
                                                </div>
                                                <div class="wjp-so-item">
                                                    <div class="wjp-so-item-label">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                                                            <line x1="12" y1="9" x2="12" y2="13"></line>
                                                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                                        </svg>
                                                        <span><?php echo esc_html__('Error Log', 'wp-job-portal'); ?></span>
                                                    </div>
                                                    <label class="wjp-so-toggle">
                                                        <input type="checkbox" name="wjp_screen_options[system_error_log]" <?php checked( isset($wpjobportal_wjp_options['system_error_log']) ? $wpjobportal_wjp_options['system_error_log'] : '', 'on' ); ?>>
                                                        <span class="wjp-so-slider"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="wjp-so-section">
                                            <div class="wjp-so-section-title"><?php echo esc_html__('User & Financials', 'wp-job-portal'); ?></div>
                                            <div class="wjp-so-grid">
                                                <div class="wjp-so-item">
                                                    <div class="wjp-so-item-label">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                            <circle cx="9" cy="7" r="4"></circle>
                                                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                                        </svg>
                                                        <span><?php echo esc_html__('Job Seekers', 'wp-job-portal'); ?></span>
                                                    </div>
                                                    <label class="wjp-so-toggle">
                                                        <input type="checkbox" name="wjp_screen_options[latest_job_seekers]" <?php checked( isset($wpjobportal_wjp_options['latest_job_seekers']) ? $wpjobportal_wjp_options['latest_job_seekers'] : '', 'on' ); ?>>
                                                        <span class="wjp-so-slider"></span>
                                                    </label>
                                                </div>
                                                <div class="wjp-so-item">
                                                    <div class="wjp-so-item-label">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect>
                                                            <line x1="9" y1="22" x2="9" y2="22"></line>
                                                            <line x1="15" y1="22" x2="15" y2="22"></line>
                                                            <line x1="12" y1="22" x2="12" y2="22"></line>
                                                            <line x1="12" y1="2" x2="12" y2="22"></line>
                                                        </svg>
                                                        <span><?php echo esc_html__('Employers', 'wp-job-portal'); ?></span>
                                                    </div>
                                                    <label class="wjp-so-toggle">
                                                        <input type="checkbox" name="wjp_screen_options[latest_employers]" <?php checked( isset($wpjobportal_wjp_options['latest_employers']) ? $wpjobportal_wjp_options['latest_employers'] : '', 'on' ); ?>>
                                                        <span class="wjp-so-slider"></span>
                                                    </label>
                                                </div>
                                                <div class="wjp-so-item">
                                                    <div class="wjp-so-item-label">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                                                            <line x1="7" y1="7" x2="7.01" y2="7"></line>
                                                        </svg>
                                                        <span><?php echo esc_html__('Subscriptions', 'wp-job-portal'); ?></span>
                                                    </div>
                                                    <label class="wjp-so-toggle">
                                                        <input type="checkbox" name="wjp_screen_options[latest_subscriptions]" <?php checked( isset($wpjobportal_wjp_options['latest_subscriptions']) ? $wpjobportal_wjp_options['latest_subscriptions'] : '', 'on' ); ?>>
                                                        <span class="wjp-so-slider"></span>
                                                    </label>
                                                </div>
                                                <div class="wjp-so-item">
                                                    <div class="wjp-so-item-label">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                                            <line x1="1" y1="10" x2="23" y2="10"></line>
                                                        </svg>
                                                        <span><?php echo esc_html__('Payments', 'wp-job-portal'); ?></span>
                                                    </div>
                                                    <label class="wjp-so-toggle">
                                                        <input type="checkbox" name="wjp_screen_options[latest_payments]" <?php checked( isset($wpjobportal_wjp_options['latest_payments']) ? $wpjobportal_wjp_options['latest_payments'] : '', 'on' ); ?>>
                                                        <span class="wjp-so-slider"></span>
                                                    </label>
                                                </div>
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
                                <svg xmlns="http://www.w3.org/2000/svg" class="wjp-btn-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                             <?php echo esc_html__('Add New Job', 'wp-job-portal'); ?>
                        </a>
                    </div>
                </div>
                <?php if (isset($wpjobportal_wjp_options['quick_actions'])) : ?>
                <div id="wjp-quick-actions">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_job&wpjobportallt=jobqueue')); ?>" class="wjp-action-card">
                        <div class="wjp-action-icon-wrapper wjp-bg-indigo"> <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                        <p class="wjp-action-title"><?php echo esc_html__('Approve Jobs', 'wp-job-portal'); ?></p>
                        <p class="wjp-action-subtitle"><?php echo esc_html(isset(wpjobportal::$_data['totalnewjobspending']) ? wpjobportal::$_data['totalnewjobspending'] : '0'); ?> <?php echo esc_html__('Pending', 'wp-job-portal'); ?></p>
                    </a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_user')); ?>" class="wjp-action-card">
                        <div class="wjp-action-icon-wrapper wjp-bg-sky">
                        <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
        </div>
                        <p class="wjp-action-title"><?php echo esc_html__('Manage Users', 'wp-job-portal'); ?></p>
                        <p class="wjp-action-subtitle"><?php echo esc_html(isset(wpjobportal::$_data['totaljobapply']) ? wpjobportal::$_data['totaljobapply'] : '0'); ?> <?php echo esc_html__('Total', 'wp-job-portal'); ?></p>
                    </a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_report&wpjobportallt=overallreports')); ?>" class="wjp-action-card">
                        <div class="wjp-action-icon-wrapper wjp-bg-emerald">
                             <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="20" x2="18" y2="10"></line>
                                <line x1="12" y1="20" x2="12" y2="4"></line>
                                <line x1="6" y1="20" x2="6" y2="14"></line>
                            </svg>
                        </div>
                        <p class="wjp-action-title"><?php echo esc_html__('View Reports', 'wp-job-portal'); ?></p>
                        <p class="wjp-action-subtitle"><?php echo esc_html__('Analytics', 'wp-job-portal'); ?></p>
                    </a>
                    <?php if(in_array('credits', wpjobportal::$_active_addons)){ ?>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_package')); ?>" class="wjp-action-card">
                            <div class="wjp-action-icon-wrapper wjp-bg-amber">
                                <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                                    <line x1="7" y1="7" x2="7.01" y2="7"></line>
                                </svg>
                            </div>
                            <p class="wjp-action-title"><?php echo esc_html__('Manage Plans', 'wp-job-portal'); ?></p>
                            <p class="wjp-action-subtitle"><?php echo esc_html__('Subscriptions', 'wp-job-portal'); ?></p>
                        </a>
                    <?php } ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_configuration&wpjobportallt=configurations&wpjpconfigid=general_setting')); ?>" class="wjp-action-card">
                        <div class="wjp-action-icon-wrapper wjp-bg-slate">
                            <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="3"></circle>
                                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                            </svg>
                        </div>
                        <p class="wjp-action-title"><?php echo esc_html__('Settings', 'wp-job-portal'); ?></p>
                        <p class="wjp-action-subtitle"><?php echo esc_html__('Platform Config', 'wp-job-portal'); ?></p>
                    </a>
                </div>
                <?php endif; ?>
                <div id="wjp-main-grid" class="wjp-grid-section">
                    <?php if (isset($wpjobportal_wjp_options['platform_growth'])) : ?>
                    <div id="wjp-platform-growth" class="wjp-card">
                        <div class="wjp-card-header">
                            <div class="wjp-h3" style="margin-bottom:0;"><?php echo esc_html__('Platform Growth', 'wp-job-portal'); ?></div>
                            <div class="wjp-chart-legend">
                                <span class="wjp-legend-item"><span class="wjp-legend-dot" style="background-color: var(--wjp-color-primary);"></span><?php echo esc_html__('Applications', 'wp-job-portal'); ?></span>
                                <span class="wjp-legend-item"><span class="wjp-legend-dot" style="background-color: #14b8a6;"></span><?php echo esc_html__('New Jobs', 'wp-job-portal'); ?></span>
                            </div>
                        </div>
                        <div class="wjp-chart-container"><div id="applicationChart"></div></div>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($wpjobportal_wjp_options['quick_stats'])) : ?>
                    <div id="wjp-quick-stats" class="wjp-card">
                        <div class="wjp-h3"><?php echo esc_html__('Quick Stats', 'wp-job-portal'); ?></div>
                       <div class="wjp-stats-list">

    <div class="wjp-stat-item">
        <div class="wjp-stat-icon-wrapper wjp-bg-amber">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
        </div>
        <div class="wjp-stat-info">
            <p class="wjp-stat-label"><?php echo esc_html__('Pending Jobs', 'wp-job-portal'); ?></p>
            <p class="wjp-stat-value"><?php echo esc_html(wpjobportal::$_data['quick_stats']['pending_jobs']); ?></p>
        </div>
    </div>

    <div class="wjp-stat-item">
        <div class="wjp-stat-icon-wrapper wjp-bg-sky">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="8.5" cy="7" r="4"></circle>
                <line x1="20" y1="8" x2="20" y2="14"></line>
                <line x1="23" y1="11" x2="17" y2="11"></line>
            </svg>
        </div>
        <div class="wjp-stat-info">
            <p class="wjp-stat-label"><?php echo esc_html__('New Applicants', 'wp-job-portal'); ?></p>
            <p class="wjp-stat-value"><?php echo esc_html(wpjobportal::$_data['quick_stats']['new_applicants']); ?></p>
        </div>
    </div>
    <?php if(in_array('credits', wpjobportal::$_active_addons)){ ?>
        <div class="wjp-stat-item">
            <div class="wjp-stat-icon-wrapper wjp-bg-indigo">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                    <line x1="7" y1="7" x2="7.01" y2="7"></line>
                </svg>
            </div>
            <div class="wjp-stat-info">
                <p class="wjp-stat-label"><?php echo esc_html__('Active Subscriptions', 'wp-job-portal'); ?></p>
                <p class="wjp-stat-value"><?php echo esc_html(wpjobportal::$_data['quick_stats']['active_subscriptions']); ?></p>
            </div>
        </div>
    <?php }?>

    <div class="wjp-stat-item">
        <div class="wjp-stat-icon-wrapper wjp-bg-green">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
        </div>
        <div class="wjp-stat-info">
            <p class="wjp-stat-label"><?php echo esc_html__('Total Users', 'wp-job-portal'); ?></p>
            <p class="wjp-stat-value"><?php echo esc_html(wpjobportal::$_data['quick_stats']['total_users']); ?></p>
        </div>
    </div>

    <div class="wjp-stat-item">
        <div class="wjp-stat-icon-wrapper wjp-bg-red">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
        </div>
        <div class="wjp-stat-info">
            <p class="wjp-stat-label"><?php echo esc_html__('Closed Jobs', 'wp-job-portal'); ?></p>
            <p class="wjp-stat-value"><?php echo esc_html(wpjobportal::$_data['quick_stats']['closed_jobs']); ?></p>
        </div>
    </div>
    <?php if(in_array('credits', wpjobportal::$_active_addons)){ ?>
        <div class="wjp-stat-item">
            <div class="wjp-stat-icon-wrapper wjp-bg-emerald">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="1" x2="12" y2="23"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
            </div>
            <div class="wjp-stat-info">
                <p class="wjp-stat-label"><?php echo esc_html__('Monthly Revenue', 'wp-job-portal'); ?></p>
                <p class="wjp-stat-value">$<?php echo esc_html(number_format(wpjobportal::$_data['quick_stats']['monthly_revenue'], 2)); ?></p>
            </div>
        </div>
    <?php } ?>

</div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if (isset($wpjobportal_wjp_options['recent_jobs'])) : ?>
                <div id="wjp-recent-jobs" class="wjp-card">
                    <div class="wjp-h3"><?php echo esc_html__('Recent Job Postings', 'wp-job-portal'); ?></div>
                    <div class="wjp-table-wrapper">
                        <?php
                        // field ordering check
                        $wpjobportal_job_listing_fields = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldOrderingData(2);
                        ?>
                        <table class="wjp-table">
                            <thead>
                                <tr><th><?php echo esc_html__('Job Title', 'wp-job-portal'); ?></th>
                                    <th><?php echo esc_html__('Company', 'wp-job-portal'); ?></th>
                                    <?php if(isset($wpjobportal_job_listing_fields['jobcategory']) && $wpjobportal_job_listing_fields['jobcategory'] !='' ){ ?>
                                        <th><?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_job_listing_fields['jobcategory'])); ?></th>
                                    <?php } ?>
                                    <th><?php echo esc_html__('Date Posted', 'wp-job-portal'); ?></th>
                                    <th><?php echo esc_html__('Status', 'wp-job-portal'); ?></th>
                                    <th><?php echo esc_html__('Actions', 'wp-job-portal'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(isset(wpjobportal::$_data[0]['latestjobs']) && !empty(wpjobportal::$_data[0]['latestjobs'])){
                                    foreach (wpjobportal::$_data[0]['latestjobs'] AS $wpjobportal_job) { ?>
                                    <tr>
                                        <td class="wjp-job-title"><a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_job&wpjobportallt=formjob&wpjobportalid='.$wpjobportal_job->id)); ?>"><?php echo esc_html($wpjobportal_job->title); ?></a></td>
                                        <td class="wjp-company-name"><a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_company&wpjobportallt=formcompany&wpjobportalid='.$wpjobportal_job->companyid)); ?>"><?php echo esc_html($wpjobportal_job->companyname); ?></a></td>
                                        <?php if(isset($wpjobportal_job_listing_fields['jobcategory']) && $wpjobportal_job_listing_fields['jobcategory'] !='' ){ ?>
                                            <td><?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_job->cat_title)); ?></td>
                                        <?php } ?>
                                        <td><?php echo esc_html(human_time_diff(strtotime($wpjobportal_job->created) ,strtotime(date_i18n("Y-m-d H:i:s")))).' '.esc_html(__("Ago",'wp-job-portal')); ?></td>
                                        <td>
                                            <?php
                                            $wpjobportal_status_class = ($wpjobportal_job->status == 1) ? 'wjp-status-active' : 'wjp-status-closed';
                                            if($wpjobportal_job->status == 1){
                                                $wpjobportal_status_label = esc_html__('Approved', 'wp-job-portal');
                                            }elseif($wpjobportal_job->status == 0){
                                                $wpjobportal_status_label = esc_html__('Pending', 'wp-job-portal');
                                            }elseif($wpjobportal_job->status == -1){
                                                $wpjobportal_status_label = esc_html__('Rejected', 'wp-job-portal');
                                            }
                                            ?>
                                            <span class="wjp-status-badge <?php echo esc_attr($wpjobportal_status_class); ?>">
                                                <?php echo esc_html($wpjobportal_status_label); ?>
                                            </span>
                                        </td>
                                        <td class="wjp-table-actions"><a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_job&wpjobportallt=formjob&wpjobportalid='.$wpjobportal_job->id)); ?>"><?php echo esc_html__('Edit', 'wp-job-portal'); ?></a></td>
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
                    <?php if (isset($wpjobportal_wjp_options['jobs_by_status_chart'])) : ?>
                    <div class="wjp-chart-card">
                        <div class="wjp-h3"><?php echo esc_html__('Jobs by Status', 'wp-job-portal'); ?></div>
                        <div class="wjp-chart-container"><div id="jobsStatusChart"></div></div>
                    </div>
                    <?php endif; ?>
                    <?php if (isset($wpjobportal_wjp_options['top_categories_chart'])) : ?>
                    <div class="wjp-chart-card">
                        <div class="wjp-h3"><?php echo esc_html__('Top Job Categories', 'wp-job-portal'); ?></div>
                        <div class="wjp-chart-container"><div id="topCategoriesChart"></div></div>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="wjp-grid-section">
                    <?php if (isset($wpjobportal_wjp_options['latest_job_applies'])) : ?>
                    <div id="" class="wjp-card wjp-new-sections">
                        <div class="wjp-card-header"><div class="wjp-h3" style="margin-bottom:0;"><?php echo esc_html__('Latest Job Applies', 'wp-job-portal'); ?></div></div>
                        <div class="wjp-list wjp-apply-list">
                            <?php if (isset(wpjobportal::$_data[0]['latest_applies']) && !empty(wpjobportal::$_data[0]['latest_applies'])) {
                                $wpjobportal_data_directory = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('data_directory');
                                foreach(wpjobportal::$_data[0]['latest_applies'] as $wpjobportal_apply) {

                                    $wpjobportal_logo = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('employer');
                                    if (isset($wpjobportal_apply->logo) && $wpjobportal_apply->logo != '') {
                                        $wpjobportal_wpdir = wp_upload_dir();
                                        $wpjobportal_logo = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory.'/data/employer/comp_'.$wpjobportal_apply->companyid.'/logo/'. $wpjobportal_apply->logo;
                                    }
                                    ?>
                                    <div class="wjp-apply-item">
                                        <div class="wjp-apply-item-header">
                                            <div class="wjp-applicant-info">
                                                <img src="<?php echo esc_url($wpjobportal_logo)?>" alt="<?php echo esc_attr($wpjobportal_apply->first_name); ?>" class="wjp-avatar">
                                                <div class="wjp-applicant-text">
                                                    <p class="wjp-text-1"><strong><a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_resume&wpjobportallt=viewresume&wpjobportalid='.(isset($wpjobportal_apply->resumeid) ? $wpjobportal_apply->resumeid : ''))); ?>"><?php echo esc_html($wpjobportal_apply->first_name . ' ' . $wpjobportal_apply->last_name); ?></a></strong> <?php echo esc_html__('applied for', 'wp-job-portal'); ?></p>
                                                    <p class="wjp-job-title"><a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_job&wpjobportallt=formjob&wpjobportalid='.(isset($wpjobportal_apply->jobid) ? $wpjobportal_apply->jobid : ''))); ?>"><?php echo esc_html($wpjobportal_apply->job_title); ?></a></p>
                                                    <p class="wjp-company-loc"><?php echo esc_html__('at', 'wp-job-portal'); ?> <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_company&wpjobportallt=formcompany&wpjobportalid='.(isset($wpjobportal_apply->companyid) ? $wpjobportal_apply->companyid : ''))); ?>"><?php echo esc_html($wpjobportal_apply->company_name); ?></a></p>
                                                </div>
                                            </div>
                                            <span class="wjp-timestamp"><?php echo esc_html(human_time_diff(strtotime($wpjobportal_apply->apply_date) ,strtotime(date_i18n("Y-m-d H:i:s")))).' '.esc_html(__("ago",'wp-job-portal')); ?></span>
                                        </div>
                                        <div class="wjp-item-footer">
                                            <div class="wjp-tag-list">
                                                <span class="wjp-tag wjp-tag-sky"><?php echo esc_html($wpjobportal_apply->jobtype_title); ?></span>
                                                <span class="wjp-tag wjp-tag-slate">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                                    <circle cx="12" cy="10" r="3"></circle>
                                                </svg>
                                                 <?php echo esc_html(WPJOBPORTALincluder::getJSModel('city')->getLocationDataForView($wpjobportal_apply->city)); ?></span>
                                                <?php /*
                                                <span class="wjp-tag wjp-tag-emerald">
                                                    <?php echo esc_html(wpjobportal::$_common->getSalaryRangeView($wpjobportal_apply->salarytype, $wpjobportal_apply->salarymin, $wpjobportal_apply->salarymax,$wpjobportal_apply->currency)); ?>
                                                    <?php if($wpjobportal_apply->salarytype==3 || $wpjobportal_apply->salarytype==2) { ?>
                                                        <span class="wpjobportal-salary-type"> <?php echo ' / ' .esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_apply->salaryrangetype)); ?></span>
                                                    <?php }?>
                                                </span>
                                             */ ?>
                                            </div>


                                            <div class="wjp-actions"><a href="<?php echo esc_url_raw(admin_url('admin.php?page=wpjobportal_jobapply&wpjobportallt=jobappliedresume&jobid='.$wpjobportal_apply->jobid)); ?>" class="wjp-btn-xs wjp-btn-indigo"><?php echo esc_html__('View Apply', 'wp-job-portal'); ?></a></div>
                                        </div>
                                    </div>
                                <?php }
                            } else { ?>
                                <div class="wjp-no-records"><?php echo esc_html__('No recent job applications to display.', 'wp-job-portal'); ?></div>
                            <?php } ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if (isset($wpjobportal_wjp_options['latest_resumes'])) : ?>
                    <div id="" class="wjp-card wjp-new-sections">
                         <div class="wjp-card-header"><div class="wjp-h3" style="margin-bottom:0;"><?php echo esc_html__('Latest Resumes', 'wp-job-portal'); ?></div><a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_resume')); ?>" class="wjp-view-all-link"><?php echo esc_html__('View All', 'wp-job-portal'); ?></a></div>
                         <div class="wjp-list">
                            <?php if(isset(wpjobportal::$_data[0]['latestresumes']) && !empty(wpjobportal::$_data[0]['latestresumes'])){
                                $wpjobportal_data_directory = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('data_directory');
                                foreach(wpjobportal::$_data[0]['latestresumes'] AS $wpjobportal_resume){
                                $wpjobportal_photo = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('jobseeker');
                                if (isset($wpjobportal_resume->photo) && $wpjobportal_resume->photo != '') {
                                    $wpjobportal_wpdir = wp_upload_dir();
                                    $wpjobportal_photo = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/jobseeker/resume_' . $wpjobportal_resume->id. '/photo/' . $wpjobportal_resume->photo;
                                }
                                ?>
                                <div class="wjp-list-item">
                                    <div class="wjp-item-main-info">
                                        <img src="<?php echo esc_url($wpjobportal_photo); ?>" alt="<?php echo esc_attr($wpjobportal_resume->application_title); ?>" class="wjp-avatar">
                                        <div class="wjp-item-text"><p class="wjp-name"><a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_resume&wpjobportallt=viewresume&wpjobportalid='.$wpjobportal_resume->id)); ?>"><?php echo esc_html($wpjobportal_resume->first_name . ' ' . $wpjobportal_resume->last_name); ?></a></p><p class="wjp-subtext"> <?php echo esc_html($wpjobportal_resume->application_title); ?></p>
                                            <div class="wjp-tag-list" style="margin-top: 0.25rem;">
                                                <span class="wjp-tag wjp-tag-sky"><?php echo esc_html($wpjobportal_resume->jobtypetitle); ?></span>
                                                <span class="wjp-tag wjp-tag-slate"><?php echo esc_html($wpjobportal_resume->cat_title); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="wjp-item-aside-info"><span class="wjp-tag wjp-tag-blue"><?php echo esc_html__('New', 'wp-job-portal'); ?></span><p class="wjp-date"><?php echo esc_html(human_time_diff(strtotime($wpjobportal_resume->created) ,strtotime(date_i18n("Y-m-d H:i:s")))).' '.esc_html(__("Ago",'wp-job-portal')); ?></p></div>
                                </div>
                            <?php }
                            } else { ?>
                                <div class="wjp-no-records"><?php echo esc_html__('No new resumes have been added recently.', 'wp-job-portal'); ?></div>
                            <?php } ?>
                         </div>
                    </div>
                    <?php endif; ?>

                <?php if(in_array('credits', wpjobportal::$_active_addons)){ ?>

                        <?php if (isset($wpjobportal_wjp_options['latest_subscriptions'])) : ?>
                        <div id="" class="wjp-card wjp-financials">
                            <div class="wjp-h3"><?php echo esc_html__('Latest Package Subscriptions', 'wp-job-portal'); ?></div>
                            <div class="wjp-list">
                                <?php if (isset(wpjobportal::$_data[0]['latest_subscriptions']) && !empty(wpjobportal::$_data[0]['latest_subscriptions'])) {
                                    foreach(wpjobportal::$_data[0]['latest_subscriptions'] as $wpjobportal_sub) { ?>
                                    <div class="wjp-list-item wjp-list-item-simple">
                                        <div class="wjp-item-main-info">
                                            <?php
                                            $wpjobportal_photo = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('jobseeker');
                                            if (isset($wpjobportal_sub->photo) && $wpjobportal_sub->photo != '') {
                                                $wpjobportal_wpdir = wp_upload_dir();
                                                $wpjobportal_data_directory = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('data_directory');
                                                $wpjobportal_photo = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/profile/profile_' . esc_attr($wpjobportal_sub->uid) . '/profile/' . $wpjobportal_sub->photo;
                                            }
                                            ?>
                                            <img src="<?php echo esc_url($wpjobportal_photo);?>" alt="<?php echo esc_attr($wpjobportal_sub->first_name); ?> Logo" class="wjp-logo">
                                            <p>
                                                <span class="wjp-company-name">
                                                    <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_user&wpjobportallt=userdetail&id='.(isset($wpjobportal_sub->uid) ? $wpjobportal_sub->uid : ''))); ?>">
                                                        <?php echo esc_html($wpjobportal_sub->first_name . ' ' . $wpjobportal_sub->last_name); ?>
                                                    </a>
                                                </span>
                                                <?php echo esc_html__('subscribed to', 'wp-job-portal'); ?>
                                                <span class="wjp-plan-pro"><?php echo esc_html($wpjobportal_sub->package_name); ?></span>.
                                            </p>
                                        </div>
                                        <span class="wjp-timestamp"><?php echo esc_html(human_time_diff(strtotime($wpjobportal_sub->created) ,strtotime(date_i18n("Y-m-d H:i:s")))).' '.esc_html(__("ago",'wp-job-portal')); ?></span>
                                    </div>
                                <?php }
                                } else { ?>
                                    <div class="wjp-list-item wjp-list-item-simple"><p><?php echo esc_html__('No new subscriptions found.', 'wp-job-portal'); ?></p></div>
                                <?php } ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (isset($wpjobportal_wjp_options['latest_payments'])) : ?>
                        <div id="" class="wjp-card wjp-financials">
                            <div class="wjp-h3"><?php echo esc_html__('Latest Payments', 'wp-job-portal'); ?></div>
                             <div class="wjp-list">
                                 <?php if (isset(wpjobportal::$_data[0]['latest_payments']) && !empty(wpjobportal::$_data[0]['latest_payments'])) {
                                    foreach(wpjobportal::$_data[0]['latest_payments'] as $wpjobportal_payment) { ?>

                                    <div class="wjp-list-item wjp-list-item-simple">
                                        <div class="wjp-item-main-info">
                                            <?php
                                            $wpjobportal_photo = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('jobseeker');
                                            if (isset($wpjobportal_sub->photo) && $wpjobportal_sub->photo != '') {
                                                $wpjobportal_wpdir = wp_upload_dir();
                                                $wpjobportal_data_directory = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('data_directory');
                                                $wpjobportal_photo = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/profile/profile_' . esc_attr($wpjobportal_sub->uid) . '/profile/' . $wpjobportal_sub->photo;
                                            }
                                            ?>
                                            <img src="<?php echo esc_url($wpjobportal_photo);?>" alt="<?php echo esc_attr($wpjobportal_payment->payer_name); ?> Logo" class="wjp-logo">
                                            <div class="wjp-item-text">
                                                <p class="wjp-name"><?php echo esc_html($wpjobportal_payment->payer_name); ?></p>
                                                <p class="wjp-subtext"><?php echo esc_html($wpjobportal_payment->description); ?></p>
                                            </div>
                                        </div>
                                        <div class="wjp-item-aside-info">
                                            <p class="wjp-amount"><?php echo esc_html($wpjobportal_payment->symbol) . esc_html($wpjobportal_payment->amount); ?></p>
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

                    <?php if (isset($wpjobportal_wjp_options['latest_activity'])) : ?>
                    <div id="" class="wjp-card wjp-system-logs wjp-col-span-3">
                        <div class="wjp-h3"><?php echo esc_html__('Latest Activity', 'wp-job-portal'); ?></div>
                        <div class="wjp-list">
                            <?php if (isset(wpjobportal::$_data[0]['latest_activity']) && !empty(wpjobportal::$_data[0]['latest_activity'])) {
                                foreach(wpjobportal::$_data[0]['latest_activity'] as $wpjobportal_log) {
                                    $wpjobportal_icon_config = $wpjobportal_log->icon_config;
                                    //$wpjobportal_icon_config = function_exists('getActivityLogIconConfigForDashboard') ? getActivityLogIconConfigForDashboard($wpjobportal_log->description) : ['icon' => 'fas fa-info', 'bg_class' => 'wjp-bg-slate'];
                                    ?>
                                <div class="wjp-activity-item">
                                    <div class="wjp-activity-icon <?php echo esc_attr($wpjobportal_icon_config['bg_class']); ?>">
                                        <!-- <i class="<?php echo esc_attr($wpjobportal_icon_config['icon']); ?>"></i> -->
                                          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                                            <line x1="7" y1="7" x2="7.01" y2="7"></line>
                                        </svg>
                                    </div>
                                    <div class="wjp-activity-text">
                                        <p><?php echo isset($wpjobportal_log->description) ? wp_kses_post($wpjobportal_log->description) : ''; ?></p>
                                        <p class="wjp-subtext"><?php echo isset($wpjobportal_log->created) ? esc_html(human_time_diff(strtotime($wpjobportal_log->created) ,strtotime(date_i18n("Y-m-d H:i:s")))).' '.esc_html(__("ago",'wp-job-portal')) : ''; ?></p>
                                    </div>
                                </div>
                            <?php }
                            } ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if (isset($wpjobportal_wjp_options['system_error_log'])) : ?>
                    <div id="" class="wjp-card wjp-system-logs wjp-col-span-2">
                        <div class="wjp-h3"><?php echo esc_html__('System Error Log', 'wp-job-portal'); ?></div>
                        <div class="wjp-list">
                           <?php if (isset(wpjobportal::$_data[0]['latest_errors']) && !empty(wpjobportal::$_data[0]['latest_errors'])) {
                                foreach(wpjobportal::$_data[0]['latest_errors'] as $wpjobportal_log) {
                                    $wpjobportal_log_class = wpjobportal_get_error_log_class($wpjobportal_log->error);
                                    ?>
                                <div class="wjp-list-item wjp-list-item-simple">
                                    <div class="wjp-item-text">
                                        <p class="wjp-log-text <?php echo esc_attr($wpjobportal_log_class); ?>"><?php echo esc_html($wpjobportal_log->error); ?></p>
                                        <p class="wjp-subtext"><?php echo esc_html(human_time_diff(strtotime($wpjobportal_log->created), current_time('timestamp'))) . ' ' . esc_html__('ago', 'wp-job-portal'); ?></p>
                                    </div>

                                </div>
                            <?php }
                           } else { ?>
                                <div class="wjp-no-records"><?php echo esc_html__('Hooray! No system errors to report.', 'wp-job-portal'); ?></div>
                           <?php } ?>

                        </div>
                    </div>
                    <?php endif; ?>


                    <?php if (isset($wpjobportal_wjp_options['latest_job_seekers'])) : ?>
                    <div id="" class="wjp-card wjp-latest-members">
                        <div class="wjp-h3"><?php echo esc_html__('Latest Job Seekers', 'wp-job-portal'); ?></div>
                         <div class="wjp-list">
                            <?php if (isset(wpjobportal::$_data[0]['latest_jobseekers']) && !empty(wpjobportal::$_data[0]['latest_jobseekers'])) {
                                foreach(wpjobportal::$_data[0]['latest_jobseekers'] as $wpjobportal_seeker) { ?>
                                <div class="wjp-list-item wjp-list-item-simple">
                                    <div class="wjp-item-main-info">
                                        <?php
                                        $wpjobportal_photo = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('jobseeker');
                                        if (isset($wpjobportal_seeker->photo) && $wpjobportal_seeker->photo != '') {
                                            $wpjobportal_wpdir = wp_upload_dir();
                                            $wpjobportal_data_directory = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('data_directory');
                                            $wpjobportal_photo = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/profile/profile_' . esc_attr($wpjobportal_seeker->id) . '/profile/' . $wpjobportal_seeker->photo;
                                        }
                                        ?>
                                        <img src="<?php echo esc_url($wpjobportal_photo) ?>" alt="<?php echo esc_attr($wpjobportal_seeker->title); ?>" class="wjp-avatar">
                                        <div class="wjp-item-text">
                                            <p class="wjp-name"><a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_user&wpjobportallt=userdetail&id='.(isset($wpjobportal_seeker->id) ? $wpjobportal_seeker->id : ''))); ?>"><?php echo esc_html($wpjobportal_seeker->username); ?></a></p>
                                            <p class="wjp-subtext"><?php echo esc_html($wpjobportal_seeker->title); ?></p>
                                            <p class="wjp-subtext"><?php echo esc_html__('Joined', 'wp-job-portal'); ?>: <?php echo !empty($wpjobportal_seeker->created) ? esc_html(date_i18n(get_option('date_format'), strtotime($wpjobportal_seeker->created))) : ''; ?></p>
                                        </div>
                                    </div>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_user&wpjobportallt=userdetail&id='.(isset($wpjobportal_seeker->id) ? $wpjobportal_seeker->id : ''))); ?>" class="wjp-view-all-link"><?php echo esc_html__('View Profile', 'wp-job-portal'); ?></a>
                                </div>
                            <?php }
                            } else { ?>
                                <div class="wjp-no-records"><?php echo esc_html__('No new job seekers have registered.', 'wp-job-portal'); ?></div>
                            <?php } ?>
                         </div>
                    </div>
                    <?php endif; ?>
                    <?php if (isset($wpjobportal_wjp_options['latest_employers'])) : ?>
                    <div id="" class="wjp-card wjp-latest-members">
                        <div class="wjp-h3"><?php echo esc_html__('Latest Employers', 'wp-job-portal'); ?></div>
                        <div class="wjp-list">
                            <?php if (isset(wpjobportal::$_data[0]['latest_employers']) && !empty(wpjobportal::$_data[0]['latest_employers'])) {
                                foreach(wpjobportal::$_data[0]['latest_employers'] as $wpjobportal_employer) { ?>
                                <div class="wjp-list-item wjp-list-item-simple">
                                    <div class="wjp-item-main-info">
                                        <?php
                                        $wpjobportal_photo = WPJOBPORTALincluder::getJSModel('common')->getDefaultImage('jobseeker');
                                        if (isset($wpjobportal_employer->photo) && $wpjobportal_employer->photo != '') {
                                            $wpjobportal_wpdir = wp_upload_dir();
                                            $wpjobportal_data_directory = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('data_directory');
                                            $wpjobportal_photo = $wpjobportal_wpdir['baseurl'] . '/' . $wpjobportal_data_directory . '/data/profile/profile_' . esc_attr($wpjobportal_employer->emp_user_id) . '/profile/' . $wpjobportal_employer->photo;
                                        }
                                        ?>
                                        <img src="<?php echo esc_url($wpjobportal_photo) ?>" alt="<?php echo esc_attr($wpjobportal_employer->title); ?> Logo" class="wjp-logo">
                                        <div class="wjp-item-text">
                                            <p class="wjp-name"><a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_user&wpjobportallt=userdetail&id='.(isset($wpjobportal_employer->emp_user_id) ? $wpjobportal_employer->emp_user_id : ''))); ?>"><?php echo esc_html($wpjobportal_employer->username); ?></a></p>
                                            <p class="wjp-subtext"><?php echo esc_html__('Joined', 'wp-job-portal'); ?>: <?php echo !empty($wpjobportal_employer->created) ? esc_html(date_i18n(get_option('date_format'), strtotime($wpjobportal_employer->created))) : ''; ?></p>
                                        </div>
                                    </div>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_user&wpjobportallt=userdetail&id='.(isset($wpjobportal_employer->emp_user_id) ? $wpjobportal_employer->emp_user_id : ''))); ?>" class="wjp-view-all-link"><?php echo esc_html__('View Profile', 'wp-job-portal'); ?></a>
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
$wpjobportal_js = '
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

      });
';
wp_add_inline_script('wp-job-portal-dashboard-js', $wpjobportal_js);

$wpjobportal_labels = isset( wpjobportal::$_data['top_categories']['labels'] )
    ? wpjobportal::$_data['top_categories']['labels']
    : [];

$wpjobportal_data_values = isset( wpjobportal::$_data['top_categories']['data'] )
    ? wpjobportal::$_data['top_categories']['data']
    : [];

// Build rows for Google Charts
$wpjobportal_colors = array('#3366CC', '#DC3912', '#FF9900', '#109618', '#990099', '#B77322', '#8B0707', '#AAAA11', '#316395', '#DD4477', '#3B3EAC', '#ADD042', '#9D98CA', '#ED3237', '#585570', '#4E5A62', '#5CC6D0');
$wpjobportal_rows = [];
$wpjobportal_color_i = 0;
if (!empty($wpjobportal_labels) && !empty($wpjobportal_data_values)) {
    foreach ($wpjobportal_labels as $wpjobportal_index => $wpjobportal_label) {
        $wpjobportal_value = isset($wpjobportal_data_values[$wpjobportal_index]) ? $wpjobportal_data_values[$wpjobportal_index] : 0;
        $wpjobportal_rows[] = "['" . esc_html($wpjobportal_label) . "', " . intval($wpjobportal_value) . ", 'color: ".$wpjobportal_colors[$wpjobportal_color_i]."', " . intval($wpjobportal_value) . "]";
        $wpjobportal_color_i++;
    }
}

$wpjobportal_topCategories_js = "
google.charts.load('current', {'packages':['corechart']});
google.setOnLoadCallback(drawTopCategoriesChart);
function drawTopCategoriesChart() {

    var data = google.visualization.arrayToDataTable([
        ['" . esc_html(__('Category', 'wp-job-portal')) . "', '" . esc_html(__('Jobs', 'wp-job-portal')) . "', { role: 'style' }, { role: 'annotation' }],
        " . implode(",", $wpjobportal_rows) . "
    ]);

    var view = new google.visualization.DataView(data);
    view.setColumns([0, 1,
        { calc: 'stringify', sourceColumn: 1, type: 'string', role: 'annotation' },
        2
    ]);

    var options = {
        title: '',
        width: '100%',
        height: 300,
        bar: { groupWidth: '80%' },
        legend: { position: 'none' },
        chartArea: { width: '90%', top: 50 }
    };

    var chart = new google.visualization.ColumnChart(document.getElementById('topCategoriesChart'));
    chart.draw(view, options);
}
";

wp_add_inline_script( 'wp-job-portal-dashboard-js', $wpjobportal_topCategories_js );



// Prepare labels and values
$wpjobportal_labels = isset(wpjobportal::$_data['jobs_by_status']['labels'])
    ? wpjobportal::$_data['jobs_by_status']['labels']
    : [];

$wpjobportal_data_values = isset(wpjobportal::$_data['jobs_by_status']['data'])
    ? wpjobportal::$_data['jobs_by_status']['data']
    : [];

// Build Google Chart rows
$wpjobportal_rows = [];
if (!empty($wpjobportal_labels) && !empty($wpjobportal_data_values)) {
    foreach ($wpjobportal_labels as $wpjobportal_i => $wpjobportal_label) {
        $wpjobportal_value = intval($wpjobportal_data_values[$wpjobportal_i] ?? 0);
        $wpjobportal_rows[] = "['" . esc_html($wpjobportal_label) . "', $wpjobportal_value]";
    }
}

$wpjobportal_jobsStatus_js = "
google.charts.load('current', {packages:['corechart']});
google.setOnLoadCallback(drawJobsStatusChart);

function drawJobsStatusChart() {

    var data = google.visualization.arrayToDataTable([
        ['" . esc_html(__('Status', 'wp-job-portal')) . "', '" . esc_html(__('Count', 'wp-job-portal')) . "'],
        " . implode(",", $wpjobportal_rows) . "
    ]);

    var options = {
        title: '',
        width: '100%',
        height: 300,
        legend: { position: 'bottom' },
        pieHole: 0.7, // same as Chart.js cutout: '70%'
    };

    var chart = new google.visualization.PieChart(document.getElementById('jobsStatusChart'));
    chart.draw(data, options);
}
";

wp_add_inline_script( 'wp-job-portal-dashboard-js', $wpjobportal_jobsStatus_js );



$wpjobportal_labels = isset(wpjobportal::$_data['platform_growth']['labels'])
    ? wpjobportal::$_data['platform_growth']['labels']
    : [];

$wpjobportal_applications = isset(wpjobportal::$_data['platform_growth']['applies'])
    ? wpjobportal::$_data['platform_growth']['applies']
    : [];

$wpjobportal_jobs = isset(wpjobportal::$_data['platform_growth']['jobs'])
    ? wpjobportal::$_data['platform_growth']['jobs']
    : [];

// Detect if labels are all YYYY-MM or YYYY-MM-DD (monthly or daily ISO)
$wpjobportal_all_iso_month_or_day = true;
foreach ($wpjobportal_labels as $wpjobportal_lab) {
    if (!preg_match('/^\d{4}-\d{2}(-\d{2})?$/', trim($wpjobportal_lab))) {
        $wpjobportal_all_iso_month_or_day = false;
        break;
    }
}

$wpjobportal_rows = [];
if ($wpjobportal_all_iso_month_or_day) {
    // Use date column; convert YYYY-MM or YYYY-MM-DD -> new Date(y, m-1, d)
    foreach ($wpjobportal_labels as $wpjobportal_i => $wpjobportal_dateString) {
        $wpjobportal_app = intval($wpjobportal_applications[$wpjobportal_i] ?? 0);
        $wpjobportal_job = intval($wpjobportal_jobs[$wpjobportal_i] ?? 0);

        $wpjobportal_parts = explode('-', $wpjobportal_dateString);
        $year = (int)$wpjobportal_parts[0];
        $wpjobportal_month = (int)($wpjobportal_parts[1] ?? 1) - 1;
        $wpjobportal_day = isset($wpjobportal_parts[2]) && $wpjobportal_parts[2] !== '' ? (int)$wpjobportal_parts[2] : 1; // default to day 1 for YYYY-MM

        $wpjobportal_rows[] = "[ new Date($year, $wpjobportal_month, $wpjobportal_day), $wpjobportal_app, $wpjobportal_job ]";
    }
    $wpjobportal_x_column_type = 'date';
    $wpjobportal_x_column_label = esc_html(__('Month', 'wp-job-portal'));
} else {
    // Fallback: use string labels (keeps original month strings like "Jan 2025")
    foreach ($wpjobportal_labels as $wpjobportal_i => $wpjobportal_label) {
        $wpjobportal_app = intval($wpjobportal_applications[$wpjobportal_i] ?? 0);
        $wpjobportal_job = intval($wpjobportal_jobs[$wpjobportal_i] ?? 0);

        // Escape single quotes for safe JS string literal
        $wpjobportal_safe_label = str_replace("'", "\\'", (string)$wpjobportal_label);
        $wpjobportal_rows[] = "['" . esc_js($wpjobportal_safe_label) . "', $wpjobportal_app, $wpjobportal_job ]";
    }
    $wpjobportal_x_column_type = 'string';
    $wpjobportal_x_column_label = esc_html(__('Date', 'wp-job-portal'));
}

$wpjobportal_applicationChart_js = "
google.charts.load('current', {packages:['corechart']});
google.setOnLoadCallback(drawApplicationChart);

function drawApplicationChart() {

    var data = new google.visualization.DataTable();
    data.addColumn('" . $wpjobportal_x_column_type . "', '" . $wpjobportal_x_column_label . "');
    data.addColumn('number', '" . esc_html(__('Applications', 'wp-job-portal')) . "');
    data.addColumn('number', '" . esc_html(__('New Jobs', 'wp-job-portal')) . "');

    data.addRows([
        " . implode(",", $wpjobportal_rows) . "
    ]);

    var options = {
        colors:['#5F3BBB', '#179650', '#D98E11', '#1EADD8', '#DB624C'],
        curveType: 'function',
        legend: { position: 'bottom' },
        pointSize: 6,
        height: 350,
        width: '100%',
        focusTarget: 'category',
        chartArea: { width: '90%', top: 50 },
        hAxis: {
            // if date axis, format nicely; otherwise strings show as-is
            format: '" . ($wpjobportal_x_column_type === 'date' ? 'MMM yyyy' : '') . "'
        }
    };

    var chart = new google.visualization.LineChart(document.getElementById('applicationChart'));
    chart.draw(data, options);
}
";

wp_add_inline_script('wp-job-portal-dashboard-js', $wpjobportal_applicationChart_js);


?>
