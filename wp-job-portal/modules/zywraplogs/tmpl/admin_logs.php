<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
if (!WPJOBPORTALincluder::getTemplate('templates/admin/header',array('module' => 'zywraplogs'))){
    return;
}

// $query = "CREATE TABLE IF NOT EXISTS `".wpjobportal::$_db->prefix."wj_portal_zywrap_logs` (
//                   `id` int(11) NOT NULL AUTO_INCREMENT,
//                   `timestamp` datetime NOT NULL,
//                   `user_id` bigint(20) DEFAULT NULL,
//                   `status` varchar(50) NOT NULL,
//                   `action` varchar(100) NOT NULL,
//                   `wrapper_code` varchar(255) DEFAULT NULL,
//                   `model_code` varchar(255) DEFAULT NULL,
//                   `http_code` int(11) DEFAULT NULL,
//                   `error_message` text DEFAULT NULL,
//                   `prompt_tokens` int(11) DEFAULT NULL,
//                   `completion_tokens` int(11) DEFAULT NULL,
//                   `total_tokens` int(11) DEFAULT NULL,
//                   `token_data` text DEFAULT NULL,
//                   PRIMARY KEY (`id`),
//                   KEY `user_id` (`user_id`),
//                   KEY `action_status` (`action`, `status`)
//                 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
//             wpjobportal::$_db->query($query);



// Ensure wpjobportal::$_data[0] is retrieved and initialized safely
$data = wpjobportal::$_data[0] ?? array();

// Extract logs and summary data with safe fallbacks
$logs = $data['logs'] ?? array();
   
// Initialize summary data with default/N/A values, excluding cost.
$summary_data = $data['summary'] ?? array(
    'runs'    => '0',
    'errors'  => '0',
    'model'   => __('N/A (Error)', 'wp-job-portal'),
);
?>

<div id="wpjobportaladmin-wrapper">
    <div id="wpjobportaladmin-leftmenu">
        <?php  WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>

    <div id="wpjobportaladmin-data">
        <div id="wpjobportal-wrapper-top">
            <div id="wpjobportal-wrapper-top-left">
                <div id="wpjobportal-breadcrumbs">
                    <ul>
                        <li>
                            <a href="<?php echo esc_url_raw(admin_url('admin.php?page=wpjobportal')); ?>" title="<?php echo esc_html(__('dashboard','wp-job-portal')); ?>">
                                <?php echo esc_html(__('Dashboard','wp-job-portal')); ?>
                            </a>
                        </li>
                        <li><?php echo esc_html(__('Zywrap Logs','wp-job-portal')); ?></li>
                    </ul>
                </div>
            </div>
            <div id="wpjobportal-wrapper-top-right">
                <div id="wpjobportal-config-btn">
                    <a href="admin.php?page=wpjobportal_configuration" title="<?php echo esc_html(__('configuration','wp-job-portal')); ?>">
                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/config.png">
                   </a>
                </div>
                <div id="wpjobportal-help-btn" class="wpjobportal-help-btn">
                    <a href="admin.php?page=wpjobportal&wpjobportallt=help" title="<?php echo esc_html(__('help','wp-job-portal')); ?>">
                        <img src="<?php echo esc_url(WPJOBPORTAL_PLUGIN_URL); ?>includes/images/control_panel/dashboard/help.png">
                   </a>
                </div>
                <div id="wpjobportal-vers-txt">
                    <?php echo esc_html(__('Version','wp-job-portal')).': '; ?>
                    <span class="wpjobportal-ver"><?php echo esc_html(WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue('versioncode')); ?></span>
                </div>
            </div>
        </div>

        <?php WPJOBPORTALincluder::getTemplate('templates/admin/pagetitle',array('wpjobportal_module' => 'wpjobportal' , 'wpjobportal_layouts' => 'zywraplogs')); ?>

        <div id="wpjobportal-admin-wrapper" class="wpjobportal-admin-config-wrapper p0 bg-n bs-n wpjobportal-admin-error-log-container">

        <div id="wpjobportaladmin-wrapper" class="wpjobportaladmin-wrapper">

            <div class="wpjobportaladmin-body-main">

                        <div class="wpjobportal-admin-wrapper">
                            <div class="wpjobportal-admin-container">
                                <div class="wpjobportal-summary-grid">
                                    <div class="wpjobportal-summary-card">
                                        <div class="wpjobportal-summary-label"><?php echo esc_html( __('Total API Runs (24h)', 'wp-job-portal') ); ?></div>
                                        <div class="wpjobportal-summary-value"><?php echo esc_html( $summary_data['runs'] ); ?></div>
                                    </div>
                                    <div class="wpjobportal-summary-card">
                                        <div class="wpjobportal-summary-label"><?php echo esc_html( __('API Errors (24h)', 'wp-job-portal') ); ?></div>
                                        <div class="wpjobportal-summary-value wpjobportal-error"><?php echo esc_html( $summary_data['errors'] ); ?></div>
                                    </div>
                                    <div class="wpjobportal-summary-card">
                                        <div class="wpjobportal-summary-label"><?php echo esc_html( __('Top Model Used', 'wp-job-portal') ); ?></div>
                                        <div class="wpjobportal-summary-value wpjobportal-model"><?php echo esc_html( $summary_data['model'] ); ?></div>
                                    </div>
                                </div>
                                <div class="wpjobportal-filter-bar">
                                    <input type="text" placeholder="<?php echo esc_attr( __('Search by Action, User or Wrapper...', 'wp-job-portal') ); ?>" class="wpjobportal-filter-input wpjobportal-search-input" id="wpjobportal-search-input">

                                    <select class="wpjobportal-filter-input wpjobportal-select-input" id="wpjobportal-status-filter">
                                        <option value=""><?php echo esc_html( __('Filter by Status', 'wp-job-portal') ); ?></option>
                                        <option value="success"><?php echo esc_html( __('Success', 'wp-job-portal') ); ?></option>
                                        <option value="error"><?php echo esc_html( __('Error', 'wp-job-portal') ); ?></option>
                                        <option value="warning"><?php echo esc_html( __('Limited', 'wp-job-portal') ); ?></option>
                                        <option value="ok"><?php echo esc_html( __('OK/Internal', 'wp-job-portal') ); ?></option>
                                    </select>
                                    <button class="wpjobportal-filter-button" id="wpjobportal-apply-filter">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                          <polygon points="3 4 21 4 14 13 14 20 10 18 10 13 3 4"></polygon>
                                        </svg>
                                        <?php echo esc_html( __('Filter', 'wp-job-portal') ); ?>
                                    </button>
                                </div>
                                <table class="wpjobportal-table wpjobportal-table-zywrap-log">
                                    <thead>
                                        <tr>
                                            <th style="width: 60px;"><?php echo esc_html( __('Status', 'wp-job-portal') ); ?></th>
                                            <th style="width: 15%;"><?php echo esc_html( __('Action & User', 'wp-job-portal') ); ?></th>
                                            <th style="width: 20%;"><?php echo esc_html( __('Wrapper / Model', 'wp-job-portal') ); ?></th>
                                            <th style="width: 25%;"><?php echo esc_html( __('Performance & Tokens', 'wp-job-portal') ); ?></th>
                                            <th style="width: 100px;"><?php echo esc_html( __('Time', 'wp-job-portal') ); ?></th>
                                            <th class="wpjobportal-text-center" style="width: 40px;"><?php echo esc_html( __('Details', 'wp-job-portal') ); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ( empty( $logs ) ) : ?>
                                            <tr>
                                                <td colspan="7" class="wpjobportal-text-center">
                                                    <?php echo esc_html( __('No log entries found or a database error occurred.', 'wp-job-portal') ); ?>
                                                </td>
                                            </tr>
                                        <?php else : ?>
                                            <?php foreach ( $logs as $log ) :
                                                // Safely retrieve data using the null coalescing operator (??)
                                                $http_status = absint($log['status'] ?? 0); // Assuming status is the HTTP status code
                                                $tokens_in = absint($log['prompt_tokens'] ?? 0);
                                                $tokens_out = absint($log['completion_tokens'] ?? 0);
                                                $total_tokens = $tokens_in + $tokens_out;

                                                // Determine status class/text
                                                $status_text = $log['status'];
                                                $status_class = 'wpjobportal-status-ok';
                                                if ($http_status >= 500 || $status_text === 'error') {
                                                    $status_class = 'wpjobportal-status-error';
                                                    $status_text = 'ERR';
                                                } elseif ($http_status >= 400 || $status_text === 'warning') {
                                                    $status_class = 'wpjobportal-status-warning';
                                                    $status_text = 'LIM';
                                                } elseif ($http_status === 200 || $status_text === 'success') {
                                                    $status_class = 'wpjobportal-status-success';
                                                    $status_text = 'SUC';
                                                }

                                                // Fallback/Simulated data for display strings (if not provided by $data)
                                                $log_id = $log['id'] ?? $log['log_id'] ?? 0;
                                                $action = $log['action'] ?? __('N/A', 'wp-job-portal');
                                                $user = $log['user'] ?? __('Guest', 'wp-job-portal');
                                                $user_id = $log['user_id'] ?? 0;
                                                $wrapper = $log['wrapper_code'] ?? '-';
                                                $model_code = $log['model_code'] ?? '-';
                                                $message = $log['response_message'] ?? $log['error_message'] ?? __('Request completed or details N/A.', 'wp-job-portal');
                                                $log_timestamp = $log['timestamp'] ?? null;
                                                $time_ago = __('N/A', 'wp-job-portal');

                                                if ( $log_timestamp ) {
                                                    // Convert the database timestamp string to a Unix time
                                                    $timestamp_unix = strtotime($log_timestamp);

                                                    // Ensure the timestamp is valid and not in the future
                                                    if ( $timestamp_unix && $timestamp_unix <= current_time('timestamp') ) {
                                                        $time_ago = sprintf(
                                                            __('%s ago', 'wp-job-portal'),
                                                            human_time_diff($timestamp_unix, current_time('timestamp'))
                                                        );
                                                    } else {
                                                         // If timestamp exists but cannot be calculated (e.g., future time)
                                                        $time_ago = esc_html( date('M j, Y H:i', $timestamp_unix) );
                                                    }
                                                }

                                                // We assume the log array might be missing 'status_class', 'status', 'log_id', etc. from the DB,
                                                // so we derive them above and use the derived variables.
                                            ?>
                                        <tr>
                                            <td>
                                                <span class="wpjobportal-status-badge <?php echo esc_attr( $status_class ); ?>">
                                                    <?php echo esc_html( $status_text ); ?>
                                                    <?php if($http_status >= 400){ ?>
                                                        <span class="wpjobportal-font-bold"><?php echo esc_html( __('HTTP:', 'wp-job-portal') ); ?></span><span class="wpjobportal-text-error">                                                        <?php echo esc_html( $http_status ); ?></span>
                                                    <?php } ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="wpjobportal-font-bold wpjobportal-text-gray-800"><?php echo esc_html( $action ); ?></div>
                                                <div class="wpjobportal-text-sm wpjobportal-text-gray-500" title="<?php echo esc_attr( sprintf( __('User ID: %s', 'wp-job-portal'), $user_id ) ); ?>">
                                                    <?php echo esc_html( $user ); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="wpjobportal-wrapper-model-cell">
                                                    <div title="<?php echo esc_attr( $wrapper ); ?>">
                                                        <span class="wpjobportal-text-xs wpjobportal-font-semibold wpjobportal-text-gray-500"><?php echo esc_html( __('W:', 'wp-job-portal') ); ?></span>
                                                        <?php echo esc_html( $wrapper === '-' ? '-' : substr($wrapper, 0, 28) . '...' ); ?>
                                                    </div>
                                                    <div title="<?php echo esc_attr( $model_code ); ?>">
                                                        <span class="wpjobportal-text-xs wpjobportal-font-semibold wpjobportal-text-gray-500"><?php echo esc_html( __('M:', 'wp-job-portal') ); ?></span>
                                                        <?php echo esc_html( $model_code === '-' ? '-' : substr($model_code, 0, 28) . '...' ); ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="wpjobportal-performance-stats">
                                                    <?php if ($total_tokens > 0) : ?>
                                                        <div class="wpjobportal-token-stats">
                                                            <span class="wpjobportal-font-bold"><?php echo esc_html( __('Tokens:', 'wp-job-portal') ); ?></span>
                                                            <span class="wpjobportal-text-gray-700" title="<?php echo esc_attr( sprintf( __('In: %s / Out: %s', 'wp-job-portal'), number_format_i18n($tokens_in), number_format_i18n($tokens_out) ) ); ?>">
                                                                <?php echo esc_html( number_format_i18n($total_tokens) ); ?>
                                                            </span>
                                                        </div>
                                                    <?php else : ?>
                                                        <span class="wpjobportal-text-gray-400"><?php echo esc_html( __('No tokens recorded', 'wp-job-portal') ); ?></span>
                                                    <?php endif; ?>

                                                    </div>
                                            </td>
                                            <td title="<?php echo esc_attr( $log['timestamp'] ?? 'N/A' ); ?>" class="wpjobportal-text-gray-600">
                                                <?php echo esc_html( $time_ago ); ?>
                                            </td>
                                            <td class="wpjobportal-text-center">
                                                <button class="wpjobportal-details-btn" data-log-id="<?php echo esc_attr( $log_id ); ?>">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                                  <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/>
                                                  <circle cx="12" cy="12" r="3"/>
                                                </svg>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php
                                if (wpjobportal::$_data[1]) {
                                    WPJOBPORTALincluder::getTemplate('templates/admin/pagination',array('wpjobportal_module' => 'zywrap' , 'pagination' => wpjobportal::$_data[1]));
                                }
                                ?>
                        </div>

            </div>
        </div>
    </div>
</div>

<script>
/**
 * jQuery implementation for log details and filtering.
 * Wrapped in the WordPress-recommended no-conflict closure.
 */
jQuery(document).ready(function($) {
    // Log data map used for the simulated detail view (matching PHP structure above)
    // IMPORTANT: This static map is purely for simulation and should ideally fetch detailed data via AJAX.
    const wpjobportal_log_data_map = {
        '125': { tokens: '<?php echo esc_js('24,024'); ?>', status: '<?php echo esc_js('SUCCESS'); ?>' },
        '124': { tokens: '<?php echo esc_js('1,234'); ?>', status: '<?php echo esc_js('ERROR'); ?>' },
        '123': { tokens: '<?php echo esc_js('N/A'); ?>', status: '<?php echo esc_js('LIMITED'); ?>' },
        '122': { tokens: '<?php echo esc_js('N/A'); ?>', status: '<?php echo esc_js('OK'); ?>' }
    };

    // Event listener for the Details buttons
    $('.wpjobportal-details-btn').on('click', function(e) {
        e.preventDefault();

        const logId = $(this).data('log-id');
        const row = $(this).closest('tr');
        const errorDiv = row.find('.wpjobportal-text-error-detail');

        let errorMessage;
        if (errorDiv.length) {
            // Use the title attribute for full error message
            errorMessage = errorDiv.attr('title') || '<?php echo esc_js( __('Error details not explicitly logged.', 'wp-job-portal') ); ?>';
        } else {
            errorMessage = '<?php echo esc_js( __('Request completed successfully. Full request/response data would be displayed here.', 'wp-job-portal') ); ?>';
        }

        const logData = wpjobportal_log_data_map[logId] || { tokens: 'N/A', status: 'N/A' };

        // Construct the detailed message
        const detailMessage = 
            '<?php echo esc_js( __('Log ID:', 'wp-job-portal') ); ?> ' + logId +
            '\n<?php echo esc_js( __('Status:', 'wp-job-portal') ); ?> ' + logData.status +
            '\n<?php echo esc_js( __('Tokens:', 'wp-job-portal') ); ?> ' + logData.tokens +
            '\n\n--- <?php echo esc_js( __('Error/Detail', 'wp-job-portal') ); ?> ---\n' + errorMessage;

        // Use alert() for simple simulation
        alert(detailMessage);
    });

    // Basic filter button functionality (Simulated)
    $('#wpjobportal-apply-filter').on('click', function(e) {
        e.preventDefault();
        
        const searchVal = $('#wpjobportal-search-input').val();
        const statusVal = $('#wpjobportal-status-filter').val();

        console.log('<?php echo esc_js( __('Filtering logs:', 'wp-job-portal') ); ?>' + ' Search="' + searchVal + '", Status="' + statusVal + '"');
        alert('<?php echo esc_js( __('Simulating filter applied.', 'wp-job-portal') ); ?>');
    });
});
</script>