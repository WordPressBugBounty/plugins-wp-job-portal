<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

// Enqueue necessary scripts and styles
// wp_enqueue_style( 'wjp-font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', array(), WPJOBPORTAL_VERSION );
// wp_enqueue_script( 'chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), WPJOBPORTAL_VERSION, false );
// Safely load external CSS/JS

wp_enqueue_script( 'jp-google-charts', esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/js/google-charts.js', array(), '1.1.1', false );
wp_enqueue_style('status-graph', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/status_graph.css', array(), wpjobportal_asset_version('includes/css/status_graph.css'));


// Register a handle for our inline script
wp_register_script( 'wp-job-portal-dashboard-js', '', array(), WPJOBPORTAL_VERSION, false );
wp_enqueue_script('wp-job-portal-dashboard-js');

// W-20: sections on/off, the date range, what needs attention and the headline numbers.
$wpjobportal_wjp_options = WPJOBPORTAL_Dashboard_Service::options();
$wpjobportal_range = WPJOBPORTAL_Dashboard_Service::range();
$wpjobportal_att = WPJOBPORTAL_Dashboard_Service::attention();
list($wpjobportal_hello, $wpjobportal_hello_sub) = WPJOBPORTAL_Dashboard_Service::greeting($wpjobportal_att);
// Line icons for the attention cards and the activity feed (direction A).
$wpjobportal_icon = function ($name) {
    $paths = array(
        'check' => '<path d="M20 6 9 17l-5-5"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
        'card' => '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/>',
        'flag' => '<path d="M5 21V4h11l-2 4 2 4H5"/>',
        'pulse' => '<path d="M3 12h4l3-8 4 16 3-8h4"/>',
        'brief' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5h6v2"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21c1-4 4-6 8-6s7 2 8 6"/>',
        'file' => '<path d="M14 3H6v18h12V7z"/><path d="M14 3v4h4"/>',
        'build' => '<path d="M4 21V5l8-2v18M12 9h8v12"/>',
    );
    return '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . (isset($paths[$name]) ? $paths[$name] : $paths['check']) . '</svg>';
};

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

<?php
// People without a photo get their initials (as applications already showed a lettered logo), not the stock face.
$wpjobportal_initials = function ($wpjobportal_name) {
    $wpjobportal_letters = '';
    foreach (array_slice(preg_split('/\s+/u', trim((string) $wpjobportal_name), -1, PREG_SPLIT_NO_EMPTY), 0, 2) as $wpjobportal_word) {
        $wpjobportal_letters .= function_exists('mb_substr') ? mb_strtoupper(mb_substr($wpjobportal_word, 0, 1)) : strtoupper(substr($wpjobportal_word, 0, 1));
    }
    return '<span class="wjp-avatar wjp-initials" aria-hidden="true">' . esc_html($wpjobportal_letters !== '' ? $wpjobportal_letters : '?') . '</span>';
};
?>
<div id="wjp-dashboard-wrapper">
    <div id="wpjobportaladmin-wrapper">
        <div id="wpjobportaladmin-leftmenu">
            <?php wpjobportalincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
        </div>
        <div id="wpjobportaladmin-data">
            <?php WPJOBPORTAL_Admin_List_UI::legacy_top(); ?>
            <main class="wjp-main-content">

                <div id="wjp-header" class="wjp-hello">
                    <div class="wjp-header-title">
                        <span class="wjp-hello-date"><?php echo esc_html(date_i18n('l, j F')); ?></span>
                        <h1 class="wjp-h2"><?php echo esc_html($wpjobportal_hello); ?></h1>
                        <?php $wpjobportal_att_total = (int) array_sum(array_column($wpjobportal_att['items'], 'count')); ?>
                        <?php if ($wpjobportal_att_total === 0) : ?>
                            <p><?php echo esc_html($wpjobportal_hello_sub); ?></p>
                        <?php else : $wpjobportal_urgent = $wpjobportal_att['items'][0]; ?>
                            <p class="wjp-hello-sub">
                                <span class="wjp-hello-count"><?php /* translators: %s: number of items */ echo esc_html(sprintf(_n('%s item needs your attention', '%s items need your attention', $wpjobportal_att_total, 'wp-job-portal'), number_format_i18n($wpjobportal_att_total))); ?></span>
                                <span class="wjp-hello-urgent"><?php echo esc_html__('Most urgent:', 'wp-job-portal'); ?> <a href="<?php echo esc_url($wpjobportal_urgent['url']); ?>"><?php echo esc_html(number_format_i18n((int) $wpjobportal_urgent['count']) . ' ' . (function_exists('mb_strtolower') ? mb_strtolower($wpjobportal_urgent['label']) : strtolower($wpjobportal_urgent['label']))); ?> →</a></span>
                            </p>
                        <?php endif; ?>
                    </div>
                    <div class="wjp-header-actions">
                        <?php if (!isset($wpjobportal_wjp_options['kpis'])) : // the range lives with Key numbers; without them it stays here ?>
                        <nav class="wjp-range" aria-label="<?php echo esc_attr__('Date range', 'wp-job-portal'); ?>">
                        <?php foreach (WPJOBPORTAL_Dashboard_Service::ranges() as $wpjobportal_r_days => $wpjobportal_r_label) : ?>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal&range=' . $wpjobportal_r_days)); ?>"<?php echo $wpjobportal_r_days === $wpjobportal_range ? ' class="is-on" aria-current="true"' : ''; ?>><?php echo esc_html($wpjobportal_r_label); ?></a>
                        <?php endforeach; ?>
                    </nav>
                        <?php endif; ?>
                        <a class="wjp-btn wjp-btn-secondary" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=' . WPJOBPORTAL_Dashboard_Service::EXPORT_ACTION . '&range=' . $wpjobportal_range), WPJOBPORTAL_Dashboard_Service::EXPORT_ACTION)); ?>" title="<?php echo esc_attr__('Download the numbers for this period as a spreadsheet (CSV)', 'wp-job-portal'); ?>"><?php echo esc_html__('Export', 'wp-job-portal'); ?></a>
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
                                <?php echo esc_html__('Options', 'wp-job-portal'); ?>
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
                                        <p class="wjp-so-hint"><?php echo esc_html__('Switch sections on or off and drag them (or use the arrows) into the order you want. This layout is saved for you only.', 'wp-job-portal'); ?></p>
                                        <ul class="wjp-so-arrange" id="wjp-so-arrange">
                                            <?php foreach (WPJOBPORTAL_Dashboard_Service::order() as $wpjobportal_so_key) : ?>
                                                <li draggable="true" data-key="<?php echo esc_attr($wpjobportal_so_key); ?>">
                                                    <span class="wjp-so-grip" aria-hidden="true">⋮⋮</span>
                                                    <input type="hidden" name="wjp_screen_order[]" value="<?php echo esc_attr($wpjobportal_so_key); ?>">
                                                    <span class="wjp-so-name"><?php echo esc_html(WPJOBPORTAL_Dashboard_Service::label($wpjobportal_so_key)); ?></span>
                                                    <button type="button" class="wjp-so-move" data-dir="-1" aria-label="<?php echo esc_attr(sprintf(/* translators: %s: section name */ __('Move %s up', 'wp-job-portal'), WPJOBPORTAL_Dashboard_Service::label($wpjobportal_so_key))); ?>">↑</button>
                                                    <button type="button" class="wjp-so-move" data-dir="1" aria-label="<?php echo esc_attr(sprintf(/* translators: %s: section name */ __('Move %s down', 'wp-job-portal'), WPJOBPORTAL_Dashboard_Service::label($wpjobportal_so_key))); ?>">↓</button>
                                                    <label class="wjp-so-toggle">
                                                        <input type="checkbox" name="wjp_screen_options[<?php echo esc_attr($wpjobportal_so_key); ?>]" <?php checked(isset($wpjobportal_wjp_options[$wpjobportal_so_key])); ?> aria-label="<?php echo esc_attr(sprintf(/* translators: %s: section name */ __('Show %s', 'wp-job-portal'), WPJOBPORTAL_Dashboard_Service::label($wpjobportal_so_key))); ?>">
                                                        <span class="wjp-so-slider"></span>
                                                    </label>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>

                                    <div class="wjp-so-footer">
                                        <button type="submit" name="wjp_reset_options" value="1" class="wjp-btn-sm wjp-btn-reset"><?php echo esc_html__('Reset To Defaults', 'wp-job-portal'); ?></button>
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
<?php
                // W-20: every section is captured, then printed in this user's order in one
                // row-filling grid (the 2.7 wrappers around pairs of cards are dropped).
                $wpjobportal_sec = array();
                ob_start(); ?>
                                <?php if (isset($wpjobportal_wjp_options['attention'])) : ob_start(); ?>
                <section id="wjp-attention" aria-label="<?php echo esc_attr__('Needs your attention', 'wp-job-portal'); ?>">
                    <?php if (empty($wpjobportal_att['items'])) : ?>
                    <div class="wjp-card wjp-att-none">
                        <?php if ((int) wpjobportal::$_db->get_var('SELECT COUNT(id) FROM `' . wpjobportal::$_db->prefix . 'wj_portal_jobs`') === 0) : // W-20: a new site gets first steps ?>
                            <p class="wjp-att-empty"><?php echo esc_html__('Your job board is ready. Start by adding a job, or load sample data to look around.', 'wp-job-portal'); ?></p>
                            <p class="wjp-first-steps">
                                <a class="wjp-btn wjp-btn-primary" href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_job&wpjobportallt=formjob')); ?>"><?php echo esc_html__('Add your first job', 'wp-job-portal'); ?></a>
                                <a class="wjp-view-all-link" href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_postinstallation&wpjobportallt=stepfour&wizard=1')); ?>"><?php echo esc_html__('Load sample data', 'wp-job-portal'); ?></a>
                            </p>
                        <?php else : ?>
                            <p class="wjp-att-empty"><?php echo esc_html__('Nothing is waiting. You are all caught up.', 'wp-job-portal'); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php else : ?>
                        <div class="wjp-todo">
                            <?php foreach ($wpjobportal_att['items'] as $wpjobportal_item) : $wpjobportal_tone = isset($wpjobportal_item['tone']) ? $wpjobportal_item['tone'] : 'info'; ?>
                                <a class="wjp-todo-card wjp-att-<?php echo esc_attr($wpjobportal_tone); ?>" href="<?php echo esc_url($wpjobportal_item['url']); ?>">
                                    <span class="wjp-todo-ico"><?php echo $wpjobportal_icon(isset($wpjobportal_item['icon']) ? $wpjobportal_item['icon'] : 'check'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed SVG ?></span>
                                    <span class="wjp-todo-text">
                                        <b><?php echo esc_html(number_format_i18n((int) $wpjobportal_item['count'])); ?></b>
                                        <span><?php echo esc_html($wpjobportal_item['label']); ?><?php if (!empty($wpjobportal_item['note'])) : ?> · <?php echo esc_html($wpjobportal_item['note']); ?><?php endif; ?></span>
                                    </span>
                                    <span class="wjp-todo-go" aria-hidden="true">→</span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
                <?php $wpjobportal_sec['attention'] = ob_get_clean(); endif; ?>
                <?php if (isset($wpjobportal_wjp_options['kpis'])) : ob_start();
                    ?>
                <div class="wjp-kpis-head">
                    <div class="wjp-h3" style="margin:0;"><?php echo esc_html__('Key numbers', 'wp-job-portal'); ?> <small><?php /* translators: %s: range, e.g. 30 days */ echo esc_html(sprintf(__('last %1$s, compared with the %1$s before', 'wp-job-portal'), WPJOBPORTAL_Dashboard_Service::ranges()[$wpjobportal_range])); ?></small></div>
                    <nav class="wjp-range" aria-label="<?php echo esc_attr__('Date range', 'wp-job-portal'); ?>">
                        <?php foreach (WPJOBPORTAL_Dashboard_Service::ranges() as $wpjobportal_r_days => $wpjobportal_r_label) : ?>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal&range=' . $wpjobportal_r_days)); ?>"<?php echo $wpjobportal_r_days === $wpjobportal_range ? ' class="is-on" aria-current="true"' : ''; ?>><?php echo esc_html($wpjobportal_r_label); ?></a>
                        <?php endforeach; ?>
                    </nav>
                </div>
                <section id="wjp-kpis" aria-label="<?php echo esc_attr__('Key numbers', 'wp-job-portal'); ?>">
                    <?php foreach (WPJOBPORTAL_Dashboard_Service::kpis($wpjobportal_range) as $wpjobportal_kpi) :
                        list($wpjobportal_d_text, $wpjobportal_d_tone) = isset($wpjobportal_kpi['delta']) ? $wpjobportal_kpi['delta'] : WPJOBPORTAL_Dashboard_Service::delta($wpjobportal_kpi['value'], $wpjobportal_kpi['previous']);
                        $wpjobportal_val = isset($wpjobportal_kpi['display']) ? $wpjobportal_kpi['display'] : number_format_i18n($wpjobportal_kpi['value']);
                        $wpjobportal_tag = !empty($wpjobportal_kpi['url']) ? 'a' : 'div'; ?>
                        <<?php echo $wpjobportal_tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- a or div ?> class="wjp-kpi"<?php echo !empty($wpjobportal_kpi['url']) ? ' href="' . esc_url($wpjobportal_kpi['url']) . '"' : ''; ?>>
                            <span class="wjp-kpi-l"><?php echo esc_html($wpjobportal_kpi['label']); ?></span>
                            <b class="wjp-kpi-n<?php echo strpos($wpjobportal_val, ' + ') !== false ? ' wjp-kpi-multi' : ''; ?>"><?php echo esc_html($wpjobportal_val); ?></b>
                            <?php
                            // Owner: the earlier number in plain words, no maths for the reader; an arrow shows the direction.
                            $wpjobportal_prev_txt = isset($wpjobportal_kpi['previous_display']) ? $wpjobportal_kpi['previous_display'] : number_format_i18n($wpjobportal_kpi['previous']);
                            $wpjobportal_arrow = $wpjobportal_d_tone === 'up' ? '▲' : ($wpjobportal_d_tone === 'down' ? '▼' : '');
                            ?>
                            <span class="wjp-kpi-d wjp-kpi-<?php echo esc_attr($wpjobportal_d_tone); ?>" title="<?php echo esc_attr(sprintf(/* translators: %s: change, e.g. +21 or +35% */ __('Change: %s', 'wp-job-portal'), $wpjobportal_d_text)); ?>"><?php if ($wpjobportal_arrow) : ?><b class="wjp-kpi-arrow" aria-hidden="true"><?php echo esc_html($wpjobportal_arrow); ?></b> <?php endif; ?><small><?php /* translators: 1: range, e.g. 7 days, 2: the previous period's number */ echo esc_html(sprintf(__('Previous %1$s: %2$s', 'wp-job-portal'), WPJOBPORTAL_Dashboard_Service::ranges()[$wpjobportal_range], $wpjobportal_prev_txt)); ?></small></span>
                            <?php if (!empty($wpjobportal_kpi['series'])) : ?><svg class="wjp-spark" viewBox="0 0 100 34" preserveAspectRatio="none" aria-hidden="true"><polyline points="<?php echo esc_attr(WPJOBPORTAL_Dashboard_Service::spark_points($wpjobportal_kpi['series'])); ?>"/></svg><?php endif; ?>
                        </<?php echo $wpjobportal_tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- a or div ?>>
                    <?php endforeach; ?>
                </section>
                <?php $wpjobportal_sec['kpis'] = ob_get_clean(); endif; ?>
                <?php if (isset($wpjobportal_wjp_options['quick_actions'])) : ob_start(); ?>
                <div id="wjp-quick-actions">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_job&wpjobportallt=jobqueue')); ?>" class="wjp-action-card">
                        <div class="wjp-action-icon-wrapper wjp-bg-indigo"> <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                        <p class="wjp-action-title"><?php echo esc_html__('Approve Jobs', 'wp-job-portal'); ?></p>
                        <p class="wjp-action-subtitle"><?php $wpjobportal_menu_counts = WPJOBPORTAL_Admin_Menu_Service::counts(); // W-20: the same count as Approvals › Jobs and the attention list
echo esc_html(number_format_i18n(isset($wpjobportal_menu_counts['approvals_jobs']) ? $wpjobportal_menu_counts['approvals_jobs'] : 0)); ?> <?php echo esc_html__('Pending', 'wp-job-portal'); ?></p>
                    </a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_job')); ?>" class="wjp-action-card">
                        <div class="wjp-action-icon-wrapper wjp-bg-sky">
                            <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <p class="wjp-action-title"><?php echo esc_html__('Applied Jobs', 'wp-job-portal'); ?></p>
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
                <?php $wpjobportal_sec['quick_actions'] = ob_get_clean(); endif; ?>
                <div id="wjp-main-grid" class="wjp-grid-section">
                    <?php if (isset($wpjobportal_wjp_options['platform_growth'])) : ob_start(); ?>
                    <div id="wjp-platform-growth" class="wjp-card">
                        <div class="wjp-card-header">
                            <div class="wjp-h3" style="margin-bottom:0;"><?php echo esc_html__('Platform Growth', 'wp-job-portal'); ?></div>
                            <div class="wjp-chart-legend">
                                <span class="wjp-legend-item"><span class="wjp-legend-dot" style="background-color: var(--wjp-color-primary);"></span><?php echo esc_html__('Applications', 'wp-job-portal'); ?></span>
                                <span class="wjp-legend-item"><span class="wjp-legend-dot" style="background-color: #94a3b8;"></span><?php echo esc_html__('New Jobs', 'wp-job-portal'); ?></span>
                            </div>
                        </div>
                        <?php $wpjobportal_pg = isset(wpjobportal::$_data['platform_growth']) ? wpjobportal::$_data['platform_growth'] : array('jobs' => array(), 'applies' => array());
                        if (array_sum($wpjobportal_pg['jobs']) + array_sum($wpjobportal_pg['applies']) === 0) : // W-20: nothing to draw ?>
                            <p class="wjp-att-empty wjp-chart-empty"><?php echo esc_html__('No new jobs or applications in this period. Try a longer range, or add a job to get started.', 'wp-job-portal'); ?></p>
                        <?php endif; ?>
                        <div class="wjp-chart-container"><div id="applicationChart"></div></div>
                    </div>
                    <?php $wpjobportal_sec['platform_growth'] = ob_get_clean(); endif; ?>

                </div>
                <div id="wjp-new-charts" class="">
                    <?php if (isset($wpjobportal_wjp_options['jobs_by_status_chart'])) : ob_start(); ?>
                    <div class="wjp-chart-card">
                        <div class="wjp-h3"><?php echo esc_html__('Jobs By Status', 'wp-job-portal'); ?></div>
                        <div class="wjp-chart-container"><div id="jobsStatusChart"></div></div>
                    </div>
                    <?php $wpjobportal_sec['jobs_by_status_chart'] = ob_get_clean(); endif; ?>
                    <?php if (isset($wpjobportal_wjp_options['top_categories_chart'])) : ob_start(); ?>
                    <div class="wjp-chart-card">
                        <div class="wjp-h3"><?php echo esc_html__('Top Job Categories', 'wp-job-portal'); ?></div>
                        <div class="wjp-chart-container"><div id="topCategoriesChart"></div></div>
                    </div>
                    <?php $wpjobportal_sec['top_categories_chart'] = ob_get_clean(); endif; ?>
                </div>
                <div class="wjp-grid-section">

                    <?php if(in_array('credits', wpjobportal::$_active_addons)){ ?>

                        <?php if (isset($wpjobportal_wjp_options['latest_subscriptions'])) : ob_start(); ?>
                        <div id="wjp-latest-subscriptions" class="wjp-card">
                            <div class="wjp-card-header"><div class="wjp-h3" style="margin-bottom:0;"><?php echo esc_html__('Latest subscriptions', 'wp-job-portal'); ?></div><a class="wjp-view-all-link" href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_purchasehistory')); ?>"><?php echo esc_html__('Purchase history', 'wp-job-portal'); ?></a></div>
                            <?php $wpjobportal_subs = isset(wpjobportal::$_data[0]['latest_subscriptions']) ? (array) wpjobportal::$_data[0]['latest_subscriptions'] : array(); ?>
                            <?php if (empty($wpjobportal_subs)) : ?>
                                <p class="wjp-att-empty"><?php echo esc_html__('No subscriptions yet.', 'wp-job-portal'); ?></p>
                            <?php else : ?>
                                <div class="wjp-table-wrapper"><table class="wjp-table wjp-money-table">
                                    <thead><tr><th><?php echo esc_html__('Member', 'wp-job-portal'); ?></th><th><?php echo esc_html__('Package', 'wp-job-portal'); ?></th><th class="wjp-num"><?php echo esc_html__('Price', 'wp-job-portal'); ?></th><th><?php echo esc_html__('Status', 'wp-job-portal'); ?></th></tr></thead>
                                    <tbody>
                                    <?php foreach ($wpjobportal_subs as $wpjobportal_sub) :
                                        $wpjobportal_sub_name = trim($wpjobportal_sub->first_name . ' ' . (isset($wpjobportal_sub->last_name) ? $wpjobportal_sub->last_name : ''));
                                        $wpjobportal_sub_active = !empty($wpjobportal_sub->status) && (empty($wpjobportal_sub->enddate) || strtotime($wpjobportal_sub->enddate) >= current_time('timestamp'));
                                        $wpjobportal_sub_price = !empty($wpjobportal_sub->isfree) || (float) $wpjobportal_sub->price <= 0 ? __('Free', 'wp-job-portal') : WPJOBPORTAL_Dashboard_Service::format_amounts(array((int) $wpjobportal_sub->currencyid => (float) $wpjobportal_sub->price)); ?>
                                        <tr>
                                            <td><div class="wjp-who"><?php if (!empty($wpjobportal_sub->photo)) : ?><img class="wjp-logo" src="<?php echo esc_url(wp_upload_dir()['baseurl'] . '/' . WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('data_directory') . '/data/profile/profile_' . (int) $wpjobportal_sub->uid . '/profile/' . $wpjobportal_sub->photo); ?>" alt=""><?php else : echo $wpjobportal_initials($wpjobportal_sub_name); endif; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper ?><span><a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_user&wpjobportallt=userdetail&id=' . (int) $wpjobportal_sub->userid)); ?>"><?php echo esc_html($wpjobportal_sub_name !== '' ? $wpjobportal_sub_name : __('Member', 'wp-job-portal')); ?></a><small><?php /* translators: %s: time, e.g. 3 days */ echo esc_html(sprintf(__('%s ago', 'wp-job-portal'), human_time_diff(strtotime($wpjobportal_sub->created), current_time('timestamp')))); ?></small></span></div></td>
                                            <td><?php echo esc_html($wpjobportal_sub->package_name); ?></td>
                                            <td class="wjp-num"><?php echo esc_html($wpjobportal_sub_price); ?></td>
                                            <td><?php if ($wpjobportal_sub_active) : ?><span class="wjp-pill wjp-pill-ok"><?php echo esc_html(!empty($wpjobportal_sub->enddate) ? sprintf(/* translators: %s: short date, e.g. 5 Nov 2026 */ __('Until %s', 'wp-job-portal'), date_i18n('j M Y', strtotime($wpjobportal_sub->enddate))) : __('Active', 'wp-job-portal')); ?></span><?php else : ?><span class="wjp-pill"><?php echo esc_html__('Ended', 'wp-job-portal'); ?></span><?php endif; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table></div>
                            <?php endif; ?>
                        </div>
                        <?php $wpjobportal_sec['latest_subscriptions'] = ob_get_clean(); endif; ?>
                        <?php if (isset($wpjobportal_wjp_options['latest_payments'])) : ob_start(); ?>
                        <div id="wjp-latest-payments" class="wjp-card">
                            <div class="wjp-card-header"><div class="wjp-h3" style="margin-bottom:0;"><?php echo esc_html__('Latest payments received', 'wp-job-portal'); ?></div><a class="wjp-view-all-link" href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_invoice')); ?>"><?php echo esc_html__('Invoices', 'wp-job-portal'); ?></a></div>
                            <?php $wpjobportal_pays = isset(wpjobportal::$_data[0]['latest_payments']) ? (array) wpjobportal::$_data[0]['latest_payments'] : array(); ?>
                            <?php if (empty($wpjobportal_pays)) : ?>
                                <p class="wjp-att-empty"><?php echo esc_html__('No payments received yet.', 'wp-job-portal'); ?></p>
                            <?php else : ?>
                                <div class="wjp-table-wrapper"><table class="wjp-table wjp-money-table">
                                    <thead><tr><th><?php echo esc_html__('Paid by', 'wp-job-portal'); ?></th><th><?php echo esc_html__('For', 'wp-job-portal'); ?></th><th class="wjp-num"><?php echo esc_html__('Amount', 'wp-job-portal'); ?></th></tr></thead>
                                    <tbody>
                                    <?php foreach ($wpjobportal_pays as $wpjobportal_payment) :
                                        $wpjobportal_pay_name = trim((string) $wpjobportal_payment->payer_name);
                                        if ($wpjobportal_pay_name === '') {
                                            $wpjobportal_pay_name = trim((isset($wpjobportal_payment->first_name) ? $wpjobportal_payment->first_name : '') . ' ' . (isset($wpjobportal_payment->last_name) ? $wpjobportal_payment->last_name : ''));
                                        } ?>
                                        <tr>
                                            <td><div class="wjp-who"><?php echo $wpjobportal_initials($wpjobportal_pay_name !== '' ? $wpjobportal_pay_name : __('Guest', 'wp-job-portal')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper ?><span><b class="wjp-who-name"><?php echo esc_html($wpjobportal_pay_name !== '' ? $wpjobportal_pay_name : __('Name not given', 'wp-job-portal')); ?></b><small><?php echo esc_html(date_i18n('j M Y', strtotime($wpjobportal_payment->created))); ?><?php if (!empty($wpjobportal_payment->paymethod)) : ?> · <?php echo esc_html(ucfirst((string) $wpjobportal_payment->paymethod)); ?><?php endif; ?></small></span></div></td>
                                            <td class="wjp-pay-for"><?php echo esc_html(wp_trim_words(wp_strip_all_tags((string) $wpjobportal_payment->description), 8)); ?></td>
                                            <td class="wjp-num"><b><?php echo esc_html(trim((string) $wpjobportal_payment->symbol) . number_format_i18n((float) $wpjobportal_payment->amount, 2)); ?></b></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table></div>
                            <?php endif; ?>
                        </div>
                        <?php $wpjobportal_sec['latest_payments'] = ob_get_clean(); endif; ?>

                    <?php } ?>

                    <?php if (isset($wpjobportal_wjp_options['latest_activity'])) : ob_start(); ?>
                    <div id="" class="wjp-card wjp-system-logs wjp-col-span-3">
                        <div class="wjp-h3"><?php echo esc_html__('Change log', 'wp-job-portal'); ?></div>
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
                    <?php $wpjobportal_sec['latest_activity'] = ob_get_clean(); endif; ?>
                    <?php if (isset($wpjobportal_wjp_options['system_error_log'])) : ob_start(); ?>
                    <div id="" class="wjp-card wjp-system-logs wjp-col-span-2">
                        <div class="wjp-h3"><?php echo esc_html__('System Error Log', 'wp-job-portal'); ?></div>
                        <div class="wjp-list">
                           <?php if (isset(wpjobportal::$_data[0]['latest_errors']) && !empty(wpjobportal::$_data[0]['latest_errors'])) {
                                foreach(wpjobportal::$_data[0]['latest_errors'] as $wpjobportal_log) {
                                    $wpjobportal_log_class = wpjobportal_get_error_log_class($wpjobportal_log->error);
                                    
                                    $wpjobportal_error = $wpjobportal_log->error;
                                    $wpjobportal_decoded_error = json_decode( $wpjobportal_error );
                                    if ( json_last_error() === JSON_ERROR_NONE && isset( $wpjobportal_decoded_error->error ) ) {
                                        $wpjobportal_error = $wpjobportal_decoded_error->error;
                                    }
                                    ?>
                                <div class="wjp-list-item wjp-list-item-simple">
                                    <div class="wjp-item-text">
                                        <p class="wjp-log-text <?php echo esc_attr($wpjobportal_log_class); ?>"><?php echo esc_html($wpjobportal_error); ?></p>
                                        <p class="wjp-subtext"><?php echo esc_html(human_time_diff(strtotime($wpjobportal_log->created), current_time('timestamp'))) . ' ' . esc_html__('ago', 'wp-job-portal'); ?></p>
                                    </div>

                                </div>
                            <?php }
                           } else { ?>
                                <div class="wjp-no-records"><?php echo esc_html__('Hooray! No system errors to report.', 'wp-job-portal'); ?></div>
                           <?php } ?>

                        </div>
                    </div>
                    <?php $wpjobportal_sec['system_error_log'] = ob_get_clean(); endif; ?>


                </div>
<?php if (isset($wpjobportal_wjp_options['job_performance'])) : ob_start();
                    $wpjobportal_perf = WPJOBPORTAL_Dashboard_Service::job_performance(); ?>
                <div id="wjp-job-performance" class="wjp-card">
                    <div class="wjp-h3"><?php echo esc_html__('Job performance', 'wp-job-portal'); ?></div>
                    <?php if (empty($wpjobportal_perf['best'])) : ?>
                        <p class="wjp-att-empty"><?php echo esc_html__('No open jobs right now.', 'wp-job-portal'); ?> <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_job&wpjobportallt=formjob')); ?>"><?php echo esc_html__('Add a job', 'wp-job-portal'); ?></a></p>
                    <?php else : ?>
                        <div class="wjp-table-wrapper">
                            <table class="wjp-table wjp-perf-table">
                                <thead><tr><th><?php echo esc_html__('Open job', 'wp-job-portal'); ?></th><th class="wjp-num"><?php echo esc_html__('Views', 'wp-job-portal'); ?></th><th class="wjp-num"><?php echo esc_html__('Applications', 'wp-job-portal'); ?></th><th class="wjp-num" title="<?php echo esc_attr__('Applications per 100 views', 'wp-job-portal'); ?>"><?php echo esc_html__('Apply rate', 'wp-job-portal'); ?></th><th></th></tr></thead>
                                <tbody>
                                <?php foreach ($wpjobportal_perf['best'] as $wpjobportal_pj) :
                                    $wpjobportal_rate = (int) $wpjobportal_pj->hits > 0 ? round((int) $wpjobportal_pj->apps / (int) $wpjobportal_pj->hits * 100) . '%' : '—'; ?>
                                    <tr>
                                        <td class="wjp-job-title"><a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_job&wpjobportallt=formjob&wpjobportalid=' . (int) $wpjobportal_pj->id)); ?>"><?php echo esc_html($wpjobportal_pj->title); ?></a></td>
                                        <td class="wjp-num"><?php echo esc_html(number_format_i18n((int) $wpjobportal_pj->hits)); ?></td>
                                        <td class="wjp-num"><?php echo esc_html(number_format_i18n((int) $wpjobportal_pj->apps)); ?></td>
                                        <td class="wjp-num"><?php echo esc_html($wpjobportal_rate); ?></td>
                                        <td class="wjp-table-actions"><a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_jobapply&wpjobportallt=jobappliedresume&jobid=' . (int) $wpjobportal_pj->id)); ?>"><?php echo esc_html__('Applications', 'wp-job-portal'); ?></a></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if ($wpjobportal_perf['none_total'] > 0) : ?>
                            <div class="wjp-perf-none">
                                <div class="wjp-perf-none-title"><?php /* translators: %s: number of jobs */ echo esc_html(sprintf(_n('%s open job has no applications after 7 days', '%s open jobs have no applications after 7 days', $wpjobportal_perf['none_total'], 'wp-job-portal'), number_format_i18n($wpjobportal_perf['none_total']))); ?></div>
                                <ul>
                                    <?php foreach ($wpjobportal_perf['none'] as $wpjobportal_nj) : ?>
                                        <li><span><?php echo esc_html($wpjobportal_nj->title); ?></span> <small><?php /* translators: %s: number of views */ echo esc_html(sprintf(_n('%s view', '%s views', (int) $wpjobportal_nj->hits, 'wp-job-portal'), number_format_i18n((int) $wpjobportal_nj->hits))); ?></small> <a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_job&wpjobportallt=formjob&wpjobportalid=' . (int) $wpjobportal_nj->id)); ?>"><?php echo esc_html__('Edit', 'wp-job-portal'); ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <?php $wpjobportal_sec['job_performance'] = ob_get_clean(); endif; ?>
                <?php if (isset($wpjobportal_wjp_options['hiring_funnel'])) : ob_start();
                    $wpjobportal_fun = WPJOBPORTAL_Dashboard_Service::funnel($wpjobportal_range);
                    $wpjobportal_fun_top = max(1, (int) $wpjobportal_fun['steps'][0]['count']); ?>
                <div id="wjp-hiring-funnel" class="wjp-card">
                    <div class="wjp-h3"><?php echo esc_html__('Hiring funnel', 'wp-job-portal'); ?></div>
                    <p class="wjp-fun-sub"><?php /* translators: %s: range, e.g. 30 days */ echo esc_html(sprintf(__('Applications made in the last %s, by how far they got.', 'wp-job-portal'), WPJOBPORTAL_Dashboard_Service::ranges()[$wpjobportal_range])); ?></p>
                    <?php if ($wpjobportal_fun['total'] === 0) : ?>
                        <p class="wjp-att-empty"><?php echo esc_html__('No applications in this period.', 'wp-job-portal'); ?></p>
                    <?php else : ?>
                        <ul class="wjp-fun">
                            <?php foreach ($wpjobportal_fun['steps'] as $wpjobportal_step) :
                                $wpjobportal_pct = round($wpjobportal_step['count'] / $wpjobportal_fun_top * 100); ?>
                                <li>
                                    <span class="wjp-fun-l"><?php echo esc_html($wpjobportal_step['label']); ?></span>
                                    <span class="wjp-fun-bar"><span style="width:<?php echo esc_attr(max(2, $wpjobportal_pct)); ?>%"></span></span>
                                    <b class="wjp-fun-n"><?php echo esc_html(number_format_i18n($wpjobportal_step['count'])); ?></b>
                                    <small class="wjp-fun-p"><?php echo esc_html($wpjobportal_pct . '%'); ?></small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <p class="wjp-fun-foot"><?php /* translators: %s: number of applications */ echo esc_html(sprintf(_n('%s not selected', '%s not selected', $wpjobportal_fun['not_selected'], 'wp-job-portal'), number_format_i18n($wpjobportal_fun['not_selected']))); ?></p>
                    <?php endif; ?>
                </div>
                <?php $wpjobportal_sec['hiring_funnel'] = ob_get_clean(); endif; ?>
                <?php if (isset($wpjobportal_wjp_options['activity_feed'])) : ob_start();
                    $wpjobportal_feed = WPJOBPORTAL_Dashboard_Service::activity_feed(); ?>
                <div id="wjp-activity-feed" class="wjp-card">
                    <div class="wjp-card-header">
                        <div class="wjp-h3" style="margin-bottom:0;"><?php echo esc_html__('Recent activity', 'wp-job-portal'); ?></div>
                        <div class="wjp-feed-filter" role="group" aria-label="<?php echo esc_attr__('Show', 'wp-job-portal'); ?>">
                            <button type="button" class="is-on" data-type="" aria-pressed="true"><?php echo esc_html__('All', 'wp-job-portal'); ?></button>
                            <?php foreach (WPJOBPORTAL_Dashboard_Service::activity_types() as $wpjobportal_ft => $wpjobportal_fl) : ?>
                                <button type="button" data-type="<?php echo esc_attr($wpjobportal_ft); ?>" aria-pressed="false"><?php echo esc_html($wpjobportal_fl); ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php if (empty($wpjobportal_feed)) : ?>
                        <p class="wjp-att-empty"><?php echo esc_html__('Nothing has happened yet. New jobs, applications and members will show here.', 'wp-job-portal'); ?></p>
                    <?php else : ?>
<?php $wpjobportal_ev_n = 0; $wpjobportal_groups = array();
                        foreach ($wpjobportal_feed as $wpjobportal_ev) {
                            $wpjobportal_g = WPJOBPORTAL_Dashboard_Service::day_group($wpjobportal_ev['time']);
                            $wpjobportal_groups[$wpjobportal_g[0]]['label'] = $wpjobportal_g[1];
                            $wpjobportal_groups[$wpjobportal_g[0]]['items'][] = $wpjobportal_ev;
                        }
                        $wpjobportal_ev_icon = array('application' => array('mail', 'amber'), 'job' => array('brief', 'pri'), 'resume' => array('file', 'sky'), 'company' => array('build', 'green'), 'member' => array('user', 'pri')); ?>
                        <div class="wjp-feed">
                            <?php foreach ($wpjobportal_groups as $wpjobportal_group) : ?>
                                <div class="wjp-feed-group">
                                    <div class="wjp-feed-day"><?php echo esc_html($wpjobportal_group['label']); ?></div>
                                    <ul>
                                        <?php foreach ($wpjobportal_group['items'] as $wpjobportal_ev) : $wpjobportal_ei = $wpjobportal_ev_icon[$wpjobportal_ev['type']]; ?>
                                            <li data-type="<?php echo esc_attr($wpjobportal_ev['type']); ?>"<?php echo $wpjobportal_ev_n++ >= 8 ? ' class="wjp-feed-more"' : ''; ?>>
                                                <span class="wjp-feed-ico wjp-t-<?php echo esc_attr($wpjobportal_ei[1]); ?>" title="<?php echo esc_attr(WPJOBPORTAL_Dashboard_Service::activity_types()[$wpjobportal_ev['type']]); ?>"><?php echo $wpjobportal_icon($wpjobportal_ei[0]); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed SVG ?></span>
                                                <a class="wjp-feed-text" href="<?php echo esc_url($wpjobportal_ev['url']); ?>">
                                                    <b><?php echo esc_html($wpjobportal_ev['title']); ?></b>
                                                    <span><?php echo esc_html($wpjobportal_ev['detail']); ?></span>
                                                </a>
                                                <time datetime="<?php echo esc_attr(mysql2date('c', $wpjobportal_ev['time'])); ?>"><?php /* translators: %s: time, e.g. 3 hours */ echo esc_html(sprintf(__('%s ago', 'wp-job-portal'), human_time_diff(strtotime($wpjobportal_ev['time']), strtotime(current_time('mysql'))))); ?></time>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($wpjobportal_feed) > 8) : ?><button type="button" class="wjp-feed-toggle" aria-expanded="false"><?php echo esc_html__('Show more', 'wp-job-portal'); ?></button><?php endif; ?>
                    <?php endif; ?>
                </div>
                <?php $wpjobportal_sec['activity_feed'] = ob_get_clean(); endif; ?>
                <?php if (isset($wpjobportal_wjp_options['revenue']) && WPJOBPORTAL_Dashboard_Service::has_payments() && current_user_can('manage_options')) : ob_start();
                    $wpjobportal_rev = WPJOBPORTAL_Dashboard_Service::revenue();
                    list($wpjobportal_rev_d, $wpjobportal_rev_t) = $wpjobportal_rev['delta']; ?>
                <div id="wjp-revenue" class="wjp-card">
                    <div class="wjp-card-header">
                        <div class="wjp-h3" style="margin-bottom:0;"><?php echo esc_html__('Revenue', 'wp-job-portal'); ?></div>
                        <a class="wjp-view-all-link" href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal_invoice')); ?>"><?php echo esc_html__('Invoices', 'wp-job-portal'); ?></a>
                    </div>
                    <div class="wjp-rev-main">
                        <span class="wjp-kpi-l"><?php echo esc_html__('Paid this month', 'wp-job-portal'); ?></span>
                        <b class="wjp-kpi-n"><?php echo esc_html($wpjobportal_rev['this_month']); ?></b>
                        <span class="wjp-kpi-d wjp-kpi-<?php echo esc_attr($wpjobportal_rev_t); ?>" title="<?php echo esc_attr(sprintf(/* translators: %s: change */ __('Change: %s', 'wp-job-portal'), $wpjobportal_rev_d)); ?>"><?php if ($wpjobportal_rev_t !== 'flat') : ?><b class="wjp-kpi-arrow" aria-hidden="true"><?php echo esc_html($wpjobportal_rev_t === 'up' ? '▲' : '▼'); ?></b> <?php endif; ?><small><?php /* translators: %s: amount paid last month */ echo esc_html(sprintf(__('Last month: %s', 'wp-job-portal'), $wpjobportal_rev['last_month_empty'] ? __('nothing', 'wp-job-portal') : $wpjobportal_rev['last_month'])); ?></small></span>
                    </div>
                    <ul class="wjp-rev-list">
                        <li><span><?php echo esc_html__('Payments not completed', 'wp-job-portal'); ?></span><b><?php echo esc_html(number_format_i18n($wpjobportal_rev['pending_n'])); ?><?php if ($wpjobportal_rev['pending_n']) : ?> · <?php echo esc_html($wpjobportal_rev['pending']); ?><?php endif; ?></b></li>
                        <li><span><?php echo esc_html__('Active subscriptions', 'wp-job-portal'); ?></span><b><?php echo esc_html(number_format_i18n($wpjobportal_rev['active'])); ?></b></li>
                        <li><span><?php echo esc_html__('Renewing in 7 days', 'wp-job-portal'); ?></span><b><?php echo esc_html(number_format_i18n($wpjobportal_rev['renewing'])); ?></b></li>
                    </ul>
                </div>
                <?php $wpjobportal_sec['revenue'] = ob_get_clean(); endif; ?>
                <?php ob_end_clean(); ?>
                <div id="wjp-sections">
                    <?php foreach (WPJOBPORTAL_Dashboard_Service::order() as $wpjobportal_sec_key) {
                        if (!isset($wpjobportal_sec[$wpjobportal_sec_key])) {
                            continue;
                        }
                        echo '<div class="wjp-sec wjp-sec-' . esc_attr(WPJOBPORTAL_Dashboard_Service::width($wpjobportal_sec_key)) . '" data-key="' . esc_attr($wpjobportal_sec_key) . '">';
                        echo $wpjobportal_sec[$wpjobportal_sec_key]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- each section escapes its own output above
                        echo '</div>';
                    } ?>
                </div>
            </main>
            <?php
            // --- NEW HOOK INTEGRATION START ---
            // Blueprint: wpjobportal_admin_dashboard_loaded (Admin corollary)
            // Allows external plugins to hook into the admin control panel initialization
            // to inject custom queries or operational alerts.
            do_action('wpjobportal_admin_dashboard_loaded', wpjobportal::$_data);
            // --- NEW HOOK INTEGRATION END ---
            ?>
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
// W-20: the activity feed's filter buttons.
wp_add_inline_script('wp-job-portal-dashboard-js', "
function wjpFeedGroups(card) {
    if (!card) { return; }
    var open = card.classList.contains('is-open');
    card.querySelectorAll('.wjp-feed-group').forEach(function (g) {
        var shown = Array.prototype.some.call(g.querySelectorAll('li'), function (li) { return !li.hidden && (open || !li.classList.contains('wjp-feed-more')); });
        g.hidden = !shown;
    });
}
document.addEventListener('DOMContentLoaded', function () { wjpFeedGroups(document.getElementById('wjp-activity-feed')); });
document.addEventListener('click', function (e) {
    var more = e.target.closest('.wjp-feed-toggle');
    if (more) {
        var open = more.getAttribute('aria-expanded') !== 'true';
        more.closest('#wjp-activity-feed').classList.toggle('is-open', open);
        more.setAttribute('aria-expanded', open ? 'true' : 'false');
        more.textContent = open ? '" . esc_js(__('Show less', 'wp-job-portal')) . "' : '" . esc_js(__('Show more', 'wp-job-portal')) . "';
        wjpFeedGroups(more.closest('#wjp-activity-feed'));
        return;
    }
    var btn = e.target.closest('.wjp-feed-filter button');
    if (!btn) { return; }
    var card = btn.closest('#wjp-activity-feed'), type = btn.dataset.type;
    card.querySelectorAll('.wjp-feed-filter button').forEach(function (b) { b.classList.toggle('is-on', b === btn); b.setAttribute('aria-pressed', b === btn ? 'true' : 'false'); });
    card.querySelectorAll('.wjp-feed li').forEach(function (li) { li.hidden = type !== '' && li.dataset.type !== type; });
    // A filtered list shows every match, so Show more has nothing to add; All goes back to the short list.
    var toggle = card.querySelector('.wjp-feed-toggle');
    card.classList.toggle('is-open', type !== '');
    if (toggle) {
        toggle.hidden = type !== '';
        toggle.setAttribute('aria-expanded', 'false');
        toggle.textContent = '" . esc_js(__('Show more', 'wp-job-portal')) . "';
    }
    wjpFeedGroups(card);
});
");
// W-20: arrange the sections in Options - drag a row, or use its arrows.
wp_add_inline_script('wp-job-portal-dashboard-js', "
document.addEventListener('DOMContentLoaded', function () {
    var list = document.getElementById('wjp-so-arrange');
    if (!list) { return; }
    var dragged = null;
    list.addEventListener('click', function (e) {
        var btn = e.target.closest('.wjp-so-move');
        if (!btn) { return; }
        var li = btn.closest('li');
        if (btn.dataset.dir === '-1' && li.previousElementSibling) { list.insertBefore(li, li.previousElementSibling); }
        if (btn.dataset.dir === '1' && li.nextElementSibling) { list.insertBefore(li.nextElementSibling, li); }
        btn.focus();
    });
    list.addEventListener('dragstart', function (e) { dragged = e.target.closest('li'); if (dragged) { dragged.classList.add('is-dragging'); e.dataTransfer.effectAllowed = 'move'; } });
    list.addEventListener('dragend', function () { if (dragged) { dragged.classList.remove('is-dragging'); } dragged = null; });
    list.addEventListener('dragover', function (e) {
        var over = e.target.closest('li');
        if (!dragged || !over || over === dragged) { return; }
        e.preventDefault();
        var r = over.getBoundingClientRect();
        list.insertBefore(dragged, (e.clientY - r.top) > r.height / 2 ? over.nextElementSibling : over);
    });
});
");

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
    // One colour for every bar (the dashboard's indigo), not a colour per category.
    view.setColumns([0, 1,
        { calc: 'stringify', sourceColumn: 1, type: 'string', role: 'annotation' }
    ]);

    var options = {
        title: '',
        width: '100%',
        height: 300,
        bar: { groupWidth: '80%' },
        legend: { position: 'none' },
        colors: ['#4f46e5'],
        chartArea: { width: '90%', top: 50 },
        vAxis: { viewWindow: { min: 0 } }
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
        colors: ['#4f46e5', '#818cf8', '#c7d2fe', '#3730a3', '#a5b4fc', '#64748b'],
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
        colors: ['#4f46e5', '#94a3b8', '#818cf8', '#334155', '#c7d2fe'],
        curveType: 'function',
        legend: { position: 'bottom' },
        pointSize: 0,
        areaOpacity: 0.22,
        lineWidth: 2.5,
        height: 350,
        width: '100%',
        focusTarget: 'category',
        chartArea: { width: '90%', top: 50 },
        vAxis: { viewWindow: { min: 0 } }, // counts are never negative; the smoothed curve dipped below 0
        hAxis: {
            // if date axis, format nicely; otherwise strings show as-is
            format: '" . ($wpjobportal_x_column_type === 'date' ? 'MMM yyyy' : '') . "'
        }
    };

    var chart = new google.visualization.AreaChart(document.getElementById('applicationChart')); // direction A: filled area
    chart.draw(data, options);
}
";

wp_add_inline_script('wp-job-portal-dashboard-js', $wpjobportal_applicationChart_js);

/*
 * Google Charts draws at the width its box has at that moment and never
 * again. The content area animates its width for 500ms on load and whenever
 * the left menu is toggled, so a chart drawn mid-animation overflowed its
 * card and the page scrolled sideways. Redraw once the width has settled.
 */
$wpjobportal_chart_redraw_js = "
(function () {
    var timer = null;
    function redrawCharts() {
        if (typeof google === 'undefined' || !google.visualization) {
            return;
        }
        if (typeof drawApplicationChart === 'function') { drawApplicationChart(); }
        if (typeof drawTopCategoriesChart === 'function') { drawTopCategoriesChart(); }
        if (typeof drawJobsStatusChart === 'function') { drawJobsStatusChart(); }
    }
    function scheduleRedraw() {
        clearTimeout(timer);
        timer = setTimeout(redrawCharts, 150);
    }
    function watchContentWidth() {
        var data = document.getElementById('wpjobportaladmin-data');
        if (data) {
            data.addEventListener('transitionend', function (event) {
                if (event.target === data && event.propertyName === 'padding-left') {
                    scheduleRedraw();
                }
            });
        }
    }
    window.addEventListener('resize', scheduleRedraw);
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', watchContentWidth);
    } else {
        watchContentWidth();
    }
})();
";

wp_add_inline_script('wp-job-portal-dashboard-js', $wpjobportal_chart_redraw_js);


?>
