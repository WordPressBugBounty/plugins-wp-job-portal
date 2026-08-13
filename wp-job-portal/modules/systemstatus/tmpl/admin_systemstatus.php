<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

$status = isset(wpjobportal::$_data['systemstatus']) ? wpjobportal::$_data['systemstatus'] : array();
$status_text = isset(wpjobportal::$_data['systemstatus_text']) ? wpjobportal::$_data['systemstatus_text'] : '';
$storage = isset($status['storage_conversion']) ? $status['storage_conversion'] : array();
$queue = isset($status['notification_queue']) ? $status['notification_queue'] : array();
$privacy = isset($status['privacy']) ? $status['privacy'] : array();
$privacy_settings = isset($privacy['settings']) ? $privacy['settings'] : array();
$storage_result = isset($_GET['wpjobportal_storage_result'])
    ? sanitize_key(wp_unslash($_GET['wpjobportal_storage_result']))
    : '';
$storage_maintenance_needed = !empty($storage['remaining'])
    || !empty($storage['missing_tables'])
    || !empty($storage['last_error']);
$labels = array(
    'environment' => __('Environment', 'wp-job-portal'),
    'database' => __('Database readiness', 'wp-job-portal'),
    'request_security' => __('Request security', 'wp-job-portal'),
    'privacy_payments' => __('Privacy and payments', 'wp-job-portal'),
    'scheduled_tasks' => __('Scheduled tasks and mail', 'wp-job-portal'),
);
$status_symbols = array(
    'good' => '✓',
    'warning' => '!',
    'critical' => '×',
    'info' => 'i',
);

wp_register_script('wpjobportal-system-status', '', array(), wpjobportal::$_currentversion, true);
wp_enqueue_script('wpjobportal-system-status');
wp_add_inline_script(
    'wpjobportal-system-status',
    "document.addEventListener('DOMContentLoaded',function(){var b=document.getElementById('wpjobportal-copy-status');var t=document.getElementById('wpjobportal-system-status-text');if(b&&t){b.addEventListener('click',function(){t.select();t.setSelectionRange(0,99999);if(navigator.clipboard&&window.isSecureContext){navigator.clipboard.writeText(t.value);}else{document.execCommand('copy');}b.textContent='" . esc_js(__('Copied', 'wp-job-portal')) . "';});}var buttons=document.querySelectorAll('[data-wpjobportal-queue-action]');var notice=document.getElementById('wpjobportal-queue-action-result');buttons.forEach(function(button){button.addEventListener('click',function(){var action=button.getAttribute('data-wpjobportal-queue-action');buttons.forEach(function(item){item.disabled=true;});if(notice){notice.textContent='" . esc_js(__('Working…', 'wp-job-portal')) . "';}var data=new URLSearchParams();data.append('action','wpjobportal_notification_queue_action');data.append('queue_action',action);data.append('nonce','" . esc_js(wp_create_nonce('wpjobportal_notification_queue_action')) . "');fetch('" . esc_url(admin_url('admin-ajax.php')) . "',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},body:data.toString()}).then(function(response){return response.json();}).then(function(response){if(!response.success){throw new Error(response.data&&response.data.message?response.data.message:'" . esc_js(__('The queue action failed.', 'wp-job-portal')) . "');}window.location.reload();}).catch(function(error){buttons.forEach(function(item){item.disabled=false;});if(notice){notice.textContent=error.message;notice.className='notice notice-error inline';}});});});});"
);
wp_add_inline_script(
    'wpjobportal-system-status',
    "document.addEventListener('DOMContentLoaded',function(){var form=document.getElementById('wpjobportal-privacy-settings');var notice=document.getElementById('wpjobportal-privacy-result');if(!form){return;}form.addEventListener('submit',function(event){event.preventDefault();var button=form.querySelector('button[type=submit]');button.disabled=true;if(notice){notice.textContent='" . esc_js(__('Saving…', 'wp-job-portal')) . "';}var data=new URLSearchParams(new FormData(form));fetch('" . esc_url(admin_url('admin-ajax.php')) . "',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},body:data.toString()}).then(function(response){return response.json();}).then(function(response){if(!response.success){throw new Error(response.data&&response.data.message?response.data.message:'" . esc_js(__('The privacy settings could not be saved.', 'wp-job-portal')) . "');}if(notice){notice.textContent=response.data.message;notice.className='notice notice-success inline';}button.disabled=false;}).catch(function(error){button.disabled=false;if(notice){notice.textContent=error.message;notice.className='notice notice-error inline';}});});});"
);
?>
<div id="wpjobportaladmin-wrapper">
    <div id="wpjobportaladmin-leftmenu">
        <?php WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <div id="wpjobportal-wrapper-top">
            <div id="wpjobportal-wrapper-top-left">
                <div id="wpjobportal-breadcrumbs">
                    <ul>
                        <li><a href="<?php echo esc_url(admin_url('admin.php?page=wpjobportal')); ?>"><?php echo esc_html__('Dashboard', 'wp-job-portal'); ?></a></li>
                        <li><?php echo esc_html__('System Status', 'wp-job-portal'); ?></li>
                    </ul>
                </div>
            </div>
            <div id="wpjobportal-wrapper-top-right">
                <div id="wpjobportal-vers-txt">
                    <?php echo esc_html__('Version', 'wp-job-portal') . ': '; ?>
                    <span class="wpjobportal-ver"><?php echo esc_html(WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue('versioncode')); ?></span>
                </div>
            </div>
        </div>

        <?php WPJOBPORTALincluder::getTemplate('templates/admin/pagetitle', array('wpjobportal_module' => 'systemstatus', 'wpjobportal_layouts' => 'systemstatus')); ?>

        <div id="wpjobportal-admin-wrapper" class="p0">
            <div style="padding:20px; background-color: #FFF;border-radius: 8px;">
                <p>
                    <?php echo esc_html__('Diagnostics are support-safe and do not expose passwords, API keys, request payloads or candidate email addresses.', 'wp-job-portal'); ?>
                </p>

                <?php if ($storage_maintenance_needed && $storage_result === 'started') : ?>
                    <div class="notice notice-success inline"><p><?php echo esc_html__('InnoDB conversion was scheduled. WordPress Cron will process one core table per batch.', 'wp-job-portal'); ?></p></div>
                <?php elseif ($storage_maintenance_needed && $storage_result === 'paused') : ?>
                    <div class="notice notice-warning inline"><p><?php echo esc_html__('InnoDB conversion was paused. A table already being converted is allowed to finish safely.', 'wp-job-portal'); ?></p></div>
                <?php elseif ($storage_maintenance_needed && in_array($storage_result, array('failed', 'invalid'), true)) : ?>
                    <div class="notice notice-error inline"><p><?php echo esc_html__('The storage-engine action could not be completed. Review the conversion status and support-safe report below.', 'wp-job-portal'); ?></p></div>
                <?php endif; ?>

                <?php foreach ($labels as $group => $group_label) : ?>
                    <h2><?php echo esc_html($group_label); ?></h2>
                    <table class="widefat striped" style="margin-bottom:24px;">
                        <thead>
                            <tr>
                                <th style="width:52px;"><?php echo esc_html__('Status', 'wp-job-portal'); ?></th>
                                <th><?php echo esc_html__('Check', 'wp-job-portal'); ?></th>
                                <th><?php echo esc_html__('Result', 'wp-job-portal'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ((array) $status[$group] as $check) : ?>
                                <tr>
                                    <td>
                                        <span class="wpjobportal-status-<?php echo esc_attr($check['status']); ?>" aria-label="<?php echo esc_attr($check['status']); ?>">
                                            <?php echo esc_html(isset($status_symbols[$check['status']]) ? $status_symbols[$check['status']] : '?'); ?>
                                        </span>
                                    </td>
                                    <td><strong><?php echo esc_html($check['label']); ?></strong></td>
                                    <td><?php echo esc_html($check['value']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endforeach; ?>

                <h2><?php echo esc_html__('Privacy retention controls', 'wp-job-portal'); ?></h2>
                <div class="card" style="max-width:none;margin:0 0 24px;padding:20px;">
                    <p><?php echo esc_html__('Set a value from 0 to the shown maximum. Zero keeps the corresponding records until an administrator or a WordPress privacy erasure request removes them.', 'wp-job-portal'); ?></p>
                    <form id="wpjobportal-privacy-settings">
                        <input type="hidden" name="action" value="wpjobportal_privacy_settings">
                        <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('wpjobportal_privacy_settings')); ?>">
                        <p>
                            <label>
                                <?php echo esc_html__('Activity history (days)', 'wp-job-portal'); ?>
                                <input type="number" min="0" max="3650" name="activity_days" value="<?php echo esc_attr(isset($privacy_settings['activity_days']) ? absint($privacy_settings['activity_days']) : 365); ?>">
                            </label>
                        </p>
                        <p>
                            <label>
                                <?php echo esc_html__('User messages (days)', 'wp-job-portal'); ?>
                                <input type="number" min="0" max="3650" name="message_days" value="<?php echo esc_attr(isset($privacy_settings['message_days']) ? absint($privacy_settings['message_days']) : 730); ?>">
                            </label>
                        </p>
                        <p>
                            <label>
                                <?php echo esc_html__('Operational audit (days)', 'wp-job-portal'); ?>
                                <input type="number" min="0" max="365" name="audit_days" value="<?php echo esc_attr(isset($privacy_settings['audit_days']) ? absint($privacy_settings['audit_days']) : 90); ?>">
                            </label>
                        </p>
                        <p><button type="submit" class="button"><?php echo esc_html__('Save retention settings', 'wp-job-portal'); ?></button></p>
                        <div id="wpjobportal-privacy-result" aria-live="polite"></div>
                    </form>
                </div>

                <h2><?php echo esc_html__('Reliable notification queue', 'wp-job-portal'); ?></h2>
                <div class="card" style="max-width:none;margin:0 0 24px;padding:20px;">
                    <p>
                        <strong><?php echo esc_html__('Mode', 'wp-job-portal'); ?>:</strong>
                        <?php echo !empty($queue['enabled']) ? esc_html__('Queue enabled', 'wp-job-portal') : esc_html__('Direct mail', 'wp-job-portal'); ?>
                    </p>
                    <p>
                        <?php
                        echo esc_html(
                            sprintf(
                                __('%1$d pending, %2$d retrying, %3$d processing, %4$d failed and %5$d recently completed.', 'wp-job-portal'),
                                isset($queue['counts']['pending']) ? absint($queue['counts']['pending']) : 0,
                                isset($queue['counts']['retry']) ? absint($queue['counts']['retry']) : 0,
                                isset($queue['counts']['processing']) ? absint($queue['counts']['processing']) : 0,
                                isset($queue['counts']['failed']) ? absint($queue['counts']['failed']) : 0,
                                isset($queue['counts']['completed']) ? absint($queue['counts']['completed']) : 0
                            )
                        );
                        ?>
                    </p>
                    <p>
                        <?php
                        echo esc_html(
                            sprintf(
                                __('Lifetime counters: %1$d queued, %2$d delivered, %3$d retries, %4$d terminal failures, %5$d direct fallbacks and %6$d duplicates suppressed.', 'wp-job-portal'),
                                isset($queue['stats']['queued']) ? absint($queue['stats']['queued']) : 0,
                                isset($queue['stats']['delivered']) ? absint($queue['stats']['delivered']) : 0,
                                isset($queue['stats']['retried']) ? absint($queue['stats']['retried']) : 0,
                                isset($queue['stats']['failed']) ? absint($queue['stats']['failed']) : 0,
                                isset($queue['stats']['fallbacks']) ? absint($queue['stats']['fallbacks']) : 0,
                                isset($queue['stats']['duplicates']) ? absint($queue['stats']['duplicates']) : 0
                            )
                        );
                        ?>
                    </p>
                    <p>
                        <?php echo esc_html__('Queued recipient, subject and message data is encrypted at rest and never shown in diagnostics. Payloads are removed after successful delivery; terminal failures are retained for manual retry for up to seven days. Messages with attachments continue using direct mail so temporary files cannot disappear before delivery.', 'wp-job-portal'); ?>
                    </p>
                    <?php if (!empty($queue['stats']['last_error'])) : ?>
                        <div class="notice notice-warning inline"><p><?php echo esc_html($queue['stats']['last_error']); ?></p></div>
                    <?php endif; ?>
                    <div id="wpjobportal-queue-action-result" aria-live="polite"></div>
                    <p>
                        <?php if (!empty($queue['enabled'])) : ?>
                            <button type="button" class="button" data-wpjobportal-queue-action="disable"><?php echo esc_html__('Disable queue for new mail', 'wp-job-portal'); ?></button>
                        <?php else : ?>
                            <button type="button" class="button button-primary" data-wpjobportal-queue-action="enable"><?php echo esc_html__('Enable reliable queue', 'wp-job-portal'); ?></button>
                        <?php endif; ?>
                        <?php if (!empty($queue['outstanding'])) : ?>
                            <button type="button" class="button" data-wpjobportal-queue-action="run_now"><?php echo esc_html__('Process next batch now', 'wp-job-portal'); ?></button>
                        <?php endif; ?>
                        <?php if (!empty($queue['counts']['failed'])) : ?>
                            <button type="button" class="button" data-wpjobportal-queue-action="retry_failed"><?php echo esc_html__('Retry failed notifications', 'wp-job-portal'); ?></button>
                        <?php endif; ?>
                    </p>
                    <?php if (empty($queue['enabled']) && !empty($queue['outstanding'])) : ?>
                        <p><em><?php echo esc_html__('Queueing is disabled for new mail, but the worker will continue draining messages already accepted into the queue.', 'wp-job-portal'); ?></em></p>
                    <?php endif; ?>
                </div>

                <?php if ($storage_maintenance_needed) : ?>
                    <h2><?php echo esc_html__('InnoDB conversion', 'wp-job-portal'); ?></h2>
                    <div class="card" style="max-width:none;margin:0 0 24px;padding:20px;">
                    <p>
                        <strong><?php echo esc_html__('Status', 'wp-job-portal'); ?>:</strong>
                        <?php echo esc_html(isset($storage['status']) ? $storage['status'] : 'not_started'); ?>
                    </p>
                    <p>
                        <strong><?php echo esc_html__('Progress', 'wp-job-portal'); ?>:</strong>
                        <?php
                        echo esc_html(
                            sprintf(
                                __('%1$d of %2$d core tables converted (%3$d remaining)', 'wp-job-portal'),
                                isset($storage['converted']) ? absint($storage['converted']) : 0,
                                isset($storage['total']) ? absint($storage['total']) : 0,
                                isset($storage['remaining']) ? absint($storage['remaining']) : 0
                            )
                        );
                        ?>
                    </p>
                    <div style="height:12px;max-width:640px;background:#dcdcde;border-radius:6px;overflow:hidden;margin:8px 0 16px;">
                        <div style="height:100%;width:<?php echo esc_attr(isset($storage['percent']) ? absint($storage['percent']) : 0); ?>%;background:#2271b1;"></div>
                    </div>

                    <?php if (!empty($storage['current_table'])) : ?>
                        <p><strong><?php echo esc_html__('Current table', 'wp-job-portal'); ?>:</strong> <code><?php echo esc_html($storage['current_table']); ?></code></p>
                    <?php endif; ?>
                    <?php if (!empty($storage['last_table'])) : ?>
                        <p><strong><?php echo esc_html__('Last table', 'wp-job-portal'); ?>:</strong> <code><?php echo esc_html($storage['last_table']); ?></code></p>
                    <?php endif; ?>
                    <?php if (!empty($storage['last_error'])) : ?>
                        <div class="notice notice-error inline"><p><?php echo esc_html($storage['last_error']); ?></p></div>
                    <?php endif; ?>

                    <?php if (!empty($storage['missing_tables'])) : ?>
                        <div class="notice notice-error inline">
                            <p>
                                <?php
                                echo esc_html(
                                    sprintf(
                                        __('Expected core tables are missing: %s. Conversion cannot start until the database is repaired.', 'wp-job-portal'),
                                        implode(', ', $storage['missing_tables'])
                                    )
                                );
                                ?>
                            </p>
                        </div>
                    <?php elseif (!empty($storage['remaining'])) : ?>
                        <p>
                            <?php echo esc_html__('Back up the database before starting. Conversion is administrator-initiated, processes one core table per batch, and stops on the first database error.', 'wp-job-portal'); ?>
                        </p>
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                            <input type="hidden" name="action" value="wpjobportal_storage_engine_action">
                            <?php wp_nonce_field('wpjobportal_storage_engine_action', 'wpjobportal_storage_nonce'); ?>
                            <?php if (in_array($storage['status'], array('scheduled', 'running'), true)) : ?>
                                <button type="submit" name="wpjobportal_storage_action" value="pause" class="button">
                                    <?php echo esc_html__('Pause conversion', 'wp-job-portal'); ?>
                                </button>
                            <?php else : ?>
                                <button type="submit" name="wpjobportal_storage_action" value="start" class="button button-primary">
                                    <?php
                                    echo esc_html(
                                        $storage['status'] === 'failed'
                                            ? __('Retry InnoDB conversion', 'wp-job-portal')
                                            : __('Start InnoDB conversion', 'wp-job-portal')
                                    );
                                    ?>
                                </button>
                            <?php endif; ?>
                        </form>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <h2><?php echo esc_html__('Operational audit', 'wp-job-portal'); ?></h2>
                <p>
                    <?php echo esc_html__('This bounded diagnostic log stores operational event names and outcomes only. It does not store recipients, email subjects, message bodies, request payloads or credentials.', 'wp-job-portal'); ?>
                </p>
                <?php if (!empty($status['audit']['recent'])) : ?>
                    <table class="widefat striped" style="margin-bottom:24px;">
                        <thead>
                            <tr>
                                <th><?php echo esc_html__('Event', 'wp-job-portal'); ?></th>
                                <th><?php echo esc_html__('Outcome', 'wp-job-portal'); ?></th>
                                <th><?php echo esc_html__('Recorded (UTC)', 'wp-job-portal'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($status['audit']['recent'] as $entry) : ?>
                                <tr>
                                    <td><?php echo esc_html($entry['event']); ?></td>
                                    <td><?php echo esc_html($entry['outcome']); ?></td>
                                    <td><?php echo esc_html($entry['time']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p><?php echo esc_html__('No operational events have been recorded yet.', 'wp-job-portal'); ?></p>
                <?php endif; ?>

                <h2><?php echo esc_html__('Migration history', 'wp-job-portal'); ?></h2>
                <?php if (!empty($status['migrations']['recent'])) : ?>
                    <table class="widefat striped" style="margin-bottom:24px;">
                        <thead>
                            <tr>
                                <th><?php echo esc_html__('Component', 'wp-job-portal'); ?></th>
                                <th><?php echo esc_html__('Migration', 'wp-job-portal'); ?></th>
                                <th><?php echo esc_html__('Status', 'wp-job-portal'); ?></th>
                                <th><?php echo esc_html__('Attempts', 'wp-job-portal'); ?></th>
                                <th><?php echo esc_html__('Completed (UTC)', 'wp-job-portal'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($status['migrations']['recent'] as $migration) : ?>
                                <tr>
                                    <td><?php echo esc_html($migration['component']); ?></td>
                                    <td><?php echo esc_html($migration['migration']); ?></td>
                                    <td><?php echo esc_html($migration['status']); ?></td>
                                    <td><?php echo esc_html($migration['attempts']); ?></td>
                                    <td><?php echo esc_html($migration['finished_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p><?php echo esc_html__('No migration history is available yet.', 'wp-job-portal'); ?></p>
                <?php endif; ?>

                <h2><?php echo esc_html__('Support-safe report', 'wp-job-portal'); ?></h2>
                <p><?php echo esc_html__('Copy this report when support asks for system information. Sensitive values are removed automatically.', 'wp-job-portal'); ?></p>
                <textarea id="wpjobportal-system-status-text" class="large-text code" rows="18" readonly><?php echo esc_textarea($status_text); ?></textarea>
                <p><button type="button" id="wpjobportal-copy-status" class="button button-primary"><?php echo esc_html__('Copy system status', 'wp-job-portal'); ?></button></p>
            </div>
        </div>
    </div>
</div>
