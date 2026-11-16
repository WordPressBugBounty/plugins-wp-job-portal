<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

/*
 * WP Job Portal > AI (2.9.0). One screen for the AI layer:
 *   Engine & account  Zywrap (sign up or connect here, licence credits), or an
 *                     Anthropic / OpenAI key; the model; a spare engine; the
 *                     Zywrap catalogue sync used by the playground and editors.
 *   Features          The master switch and each AI task.
 *   Usage             Calls, tokens and cost, and the limits.
 *   Privacy           What is removed before text leaves the site, and the notice.
 * Keys are never shown back: only their last four characters.
 */

$wpjobportal_msgkey = WPJOBPORTALincluder::getJSModel('wpjobportal')->getMessagekey();
WPJOBPORTALMessages::getLayoutMessage($wpjobportal_msgkey);

$wpjobportal_tabs = array(
    'engine'   => __('Engine & account', 'wp-job-portal'),
    'features' => __('Features', 'wp-job-portal'),
    'usage'    => __('Usage & limits', 'wp-job-portal'),
    'privacy'  => __('Privacy', 'wp-job-portal'),
);
$wpjobportal_tab = sanitize_key((string) WPJOBPORTALrequest::getVar('tab', 'get', 'engine'));
if (!isset($wpjobportal_tabs[$wpjobportal_tab])) {
    $wpjobportal_tab = 'engine';
}
$wpjobportal_base = admin_url('admin.php?page=wpjobportal_zywrap&wpjobportallt=zywrap');
$wpjobportal_action = admin_url('admin.php?page=wpjobportal_zywrap&task=save_ai_settings');
$wpjobportal_status = WPJOBPORTAL_AI_Policy::status();
$wpjobportal_current = WPJOBPORTAL_AI_Engine::currentId();
$wpjobportal_engines = WPJOBPORTAL_AI_Engine::engines();

/** The hidden fields every form on this screen carries. */
$wpjobportal_hidden = function ($do, $extra = array()) use ($wpjobportal_tab) {
    $html = wp_nonce_field('save-ai-settings', '_wpnonce', true, false);
    $html .= '<input type="hidden" name="action" value="zywrap_save_ai_settings">';
    $html .= '<input type="hidden" name="form_request" value="wpjobportal">';
    $html .= '<input type="hidden" name="tab" value="' . esc_attr($wpjobportal_tab) . '">';
    $html .= '<input type="hidden" name="do" value="' . esc_attr($do) . '">';
    foreach ($extra as $name => $value) {
        $html .= '<input type="hidden" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '">';
    }
    return $html;
};
$wpjobportal_allowed_form = array(
    'input' => array('type' => true, 'name' => true, 'value' => true, 'id' => true),
);
?>
<style>
/* AI settings: the look of the plugin's Settings screen (wpjobportaladmin_redesign.css tokens). */
.wpjp-ai-screen{--wpjp-ai-primary:var(--wjp-color-primary,#4f46e5);--wpjp-ai-primary-dark:var(--wjp-color-primary-dark,#4338ca);--wpjp-ai-border:var(--wjp-border-color,#e2e8f0);--wpjp-ai-field:#cbd5e1;--wpjp-ai-text:var(--wjp-text-dark,#1e293b);--wpjp-ai-muted:var(--wjp-text-light,#64748b);color:var(--wpjp-ai-text);font-size:14px}
.wpjp-ai-tabs{display:flex;gap:6px;flex-wrap:wrap;margin:0 0 20px;padding:6px;background:var(--wjp-bg-light,#f1f5f9);border-radius:10px;border:0}
.wpjp-ai-tabs a{padding:8px 16px;text-decoration:none;color:var(--wjp-text-medium,#475569);background:#fff;border:1px solid var(--wpjp-ai-border);border-radius:6px;font-weight:500;font-size:14px;line-height:20px;transition:background .15s,color .15s,border-color .15s}
.wpjp-ai-tabs a:hover{color:var(--wpjp-ai-primary);border-color:var(--wpjp-ai-primary)}
.wpjp-ai-tabs a:focus{outline:2px solid var(--wpjp-ai-primary);outline-offset:1px;box-shadow:none}
.wpjp-ai-tabs a.is-active{background:var(--wpjp-ai-primary);border-color:var(--wpjp-ai-primary);color:#fff}
.wpjp-ai-status{padding:12px 16px;border:1px solid var(--wpjp-ai-border);border-left:4px solid #72aee6;border-radius:8px;background:#fff;margin:0 0 16px}
.wpjp-ai-status.is-ready{border-left-color:var(--wjp-color-success,#10b981)}.wpjp-ai-status.is-off{border-left-color:var(--wjp-color-danger,#ef4444)}
.wpjp-ai-card{background:#fff;border:1px solid var(--wpjp-ai-border);border-radius:12px;padding:20px 24px;margin:0 0 16px;box-shadow:0 1px 2px rgba(15,23,42,.04)}
.wpjp-ai-card h3{margin:0 0 6px;font-size:16px;font-weight:600;color:var(--wpjp-ai-text);display:flex;align-items:center;flex-wrap:wrap;gap:6px}
.wpjp-ai-card h3 label{display:inline-flex;align-items:center;gap:8px;cursor:pointer}
.wpjp-ai-card p{margin:6px 0;line-height:1.55}
.wpjp-ai-card.is-current{border-color:var(--wpjp-ai-primary);box-shadow:0 0 0 1px var(--wpjp-ai-primary)}
.wpjp-ai-muted{color:var(--wpjp-ai-muted);font-size:13px}
.wpjp-ai-row{display:grid;grid-template-columns:200px minmax(0,440px);gap:6px 20px;align-items:center;margin:14px 0}
.wpjp-ai-row > .wpjp-ai-muted{grid-column:2}
.wpjp-ai-row.is-wide{grid-template-columns:minmax(0,1fr)}
#wpjobportal-admin-wrapper.wpjp-ai-screen .wpjp-ai-row.is-wide input{max-width:720px}
.wpjp-ai-card p > label{display:inline-flex;align-items:center;gap:8px}
.wpjp-ai-row > label{font-weight:500;color:var(--wpjp-ai-text);font-size:14px;min-width:0}
.wpjp-ai-indent{margin-left:220px !important}
#wpjobportal-admin-wrapper.wpjp-ai-screen input[type=text],#wpjobportal-admin-wrapper.wpjp-ai-screen input[type=email],#wpjobportal-admin-wrapper.wpjp-ai-screen input[type=password],#wpjobportal-admin-wrapper.wpjp-ai-screen input[type=number],#wpjobportal-admin-wrapper.wpjp-ai-screen select{box-sizing:border-box;width:100%;max-width:440px;min-height:44px !important;height:44px !important;padding:8px 12px;margin:0;background-color:#fff;border:1px solid var(--wpjp-ai-field);border-radius:6px;box-shadow:0 1px 2px rgba(0,0,0,.05);font-size:14px;line-height:1.4;color:#334155;font-family:var(--wjp-font-family,inherit)}
#wpjobportal-admin-wrapper.wpjp-ai-screen select{padding-right:32px}
#wpjobportal-admin-wrapper.wpjp-ai-screen input[type=number]{max-width:160px}
#wpjobportal-admin-wrapper.wpjp-ai-screen input:focus,#wpjobportal-admin-wrapper.wpjp-ai-screen select:focus{outline:none;border-color:var(--wpjp-ai-primary);box-shadow:0 0 0 2px rgba(79,70,229,.2)}
#wpjobportal-admin-wrapper.wpjp-ai-screen input[type=radio],#wpjobportal-admin-wrapper.wpjp-ai-screen input[type=checkbox]{accent-color:var(--wpjp-ai-primary);margin:0}
#wpjobportal-admin-wrapper.wpjp-ai-screen .button{display:inline-flex;align-items:center;justify-content:center;min-height:40px;height:auto;padding:0 16px;margin:0;font-size:14px;font-weight:500;line-height:1.3;border-radius:6px;border:1px solid var(--wpjp-ai-field);background:#fff;color:#334155;box-shadow:0 1px 2px rgba(0,0,0,.05);transition:background .15s,border-color .15s,color .15s;cursor:pointer;white-space:normal;text-align:center}
#wpjobportal-admin-wrapper.wpjp-ai-screen .button:hover{background:var(--wjp-bg-hover,#f8fafc);border-color:#94a3b8;color:var(--wpjp-ai-text)}
#wpjobportal-admin-wrapper.wpjp-ai-screen .button:focus{outline:2px solid var(--wpjp-ai-primary);outline-offset:1px;box-shadow:none}
#wpjobportal-admin-wrapper.wpjp-ai-screen .button-primary{background:var(--wpjp-ai-primary);border-color:var(--wpjp-ai-primary);color:#fff}
#wpjobportal-admin-wrapper.wpjp-ai-screen .button-primary:hover,#wpjobportal-admin-wrapper.wpjp-ai-screen .button-primary:focus{background:var(--wpjp-ai-primary-dark);border-color:var(--wpjp-ai-primary-dark);color:#fff}
#wpjobportal-admin-wrapper.wpjp-ai-screen .button[disabled]{opacity:.55;cursor:not-allowed}
#wpjobportal-admin-wrapper.wpjp-ai-screen .button-link{color:var(--wpjp-ai-primary);font-size:14px;text-decoration:underline;background:none;border:0;padding:0;cursor:pointer}
#wpjobportal-admin-wrapper.wpjp-ai-screen .button-link:hover{color:var(--wpjp-ai-primary-dark)}
#wpjobportal-admin-wrapper.wpjp-ai-screen .wpjp-ai-danger{color:var(--wjp-color-danger-text,#b91c1c)}
#wpjobportal-admin-wrapper.wpjp-ai-screen .wpjp-ai-danger:hover{color:#7f1d1d}
.wpjp-ai-actions,.wpjp-ai-footer{display:flex;flex-wrap:wrap;align-items:center;gap:12px;margin-top:14px}
.wpjp-ai-footer{margin:4px 0 24px}
.wpjp-ai-inline-controls{display:flex;flex-wrap:wrap;align-items:center;gap:10px}
#wpjobportal-admin-wrapper .wpjp-ai-inline-controls input[type=text]{max-width:160px}
.wpjp-ai-sub{margin-top:16px;padding-top:14px;border-top:1px solid var(--wpjp-ai-border)}
.wpjp-ai-sub-head{display:flex;flex-wrap:wrap;align-items:center;gap:8px 12px}
.wpjp-ai-sub-head .button{margin-left:auto}
.wpjp-ai-sub-state{color:var(--wpjp-ai-muted)}
.wpjp-ai-sub details{margin-top:8px}
.wpjp-ai-sub summary{cursor:pointer;color:var(--wpjp-ai-primary);font-weight:500}
.wpjp-ai-card .wpjp-ai-consent > label{display:block;position:relative;padding-left:26px;line-height:1.55}
.wpjp-ai-card .wpjp-ai-consent > label input{position:absolute;left:0;top:3px}
.wpjp-ai-badge{display:inline-block;padding:2px 10px;border-radius:999px;background:var(--wjp-color-primary-bg,#eef2ff);color:var(--wpjp-ai-primary);font-size:12px;font-weight:600;line-height:18px}
.wpjp-ai-badge.is-green{background:var(--wjp-color-success-bg,#d1fae5);color:var(--wjp-color-success-text,#065f46)}.wpjp-ai-badge.is-warn{background:var(--wjp-color-warning-bg,#fef3c7);color:var(--wjp-color-warning-text,#b45309)}
.wpjp-ai-table{width:100%;border-collapse:collapse}.wpjp-ai-table th,.wpjp-ai-table td{text-align:left;padding:10px 8px;border-bottom:1px solid var(--wpjp-ai-border);vertical-align:top}
.wpjp-ai-table th{font-weight:600;color:var(--wjp-text-medium,#475569);font-size:13px}
.wpjp-ai-inline{display:inline}
.wpjp-ai-alert{padding:10px 14px;margin:0 0 10px;border-left:4px solid var(--wjp-color-warning,#f59e0b);background:var(--wjp-color-warning-bg,#fef3c7);border-radius:6px}
.wpjp-ai-alert.is-stop{border-left-color:var(--wjp-color-danger,#ef4444);background:var(--wjp-color-danger-bg,#fee2e2)}
@media (max-width:782px){.wpjp-ai-row{grid-template-columns:1fr}.wpjp-ai-row > .wpjp-ai-muted{grid-column:1}.wpjp-ai-indent{margin-left:0 !important}#wpjobportal-admin-wrapper.wpjp-ai-screen input[type=text],#wpjobportal-admin-wrapper.wpjp-ai-screen input[type=email],#wpjobportal-admin-wrapper.wpjp-ai-screen input[type=password],#wpjobportal-admin-wrapper.wpjp-ai-screen select{max-width:100%}.wpjp-ai-sub-head .button{margin-left:0}}
</style>

<div id="wpjobportaladmin-wrapper">
    <div id="wpjobportaladmin-leftmenu">
        <?php WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <?php WPJOBPORTAL_Admin_List_UI::legacy_top(); ?>
        <?php WPJOBPORTALincluder::getTemplate('templates/admin/pagetitle', array('wpjobportal_module' => 'wpjobportal', 'wpjobportal_layouts' => 'zywrap')); ?>

        <div id="wpjobportal-admin-wrapper" class="wpjobportal-admin-config-wrapper wpjp-ai-screen">
            <div class="wpjp-ai-status <?php echo $wpjobportal_status['state'] === 'ready' ? 'is-ready' : ($wpjobportal_status['state'] === 'off' ? 'is-off' : ''); ?>">
                <strong><?php echo esc_html($wpjobportal_status['label']); ?></strong>
            </div>

            <nav class="wpjp-ai-tabs" aria-label="<?php echo esc_attr__('AI settings', 'wp-job-portal'); ?>">
                <?php foreach ($wpjobportal_tabs as $wpjobportal_id => $wpjobportal_label) : ?>
                    <a href="<?php echo esc_url(add_query_arg('tab', $wpjobportal_id, $wpjobportal_base)); ?>" class="<?php echo $wpjobportal_id === $wpjobportal_tab ? 'is-active' : ''; ?>"><?php echo esc_html($wpjobportal_label); ?></a>
                <?php endforeach; ?>
            </nav>

<?php if ($wpjobportal_tab === 'engine') :
    $wpjobportal_account = WPJOBPORTAL_Zywrap_Connect::account();
    $wpjobportal_zconnected = WPJOBPORTAL_AI_Engine::configured('zywrap');
    $wpjobportal_credits = WPJOBPORTAL_AI_Credits::state();
    $wpjobportal_credit_line = WPJOBPORTAL_AI_Credits::describe($wpjobportal_credits, $wpjobportal_zconnected);
    global $wpdb;
    $wpjobportal_zmodels = array();
    if ($wpjobportal_zconnected) {
        $wpjobportal_zmodels = (array) $wpdb->get_results("SELECT code, name FROM `" . $wpdb->prefix . "wj_portal_zywrap_ai_models` ORDER BY ordering ASC, name ASC");
    }
    ?>
            <form method="post" action="<?php echo esc_url($wpjobportal_action); ?>" autocomplete="off" id="wpjp-ai-f-save">
                <?php echo wp_kses($wpjobportal_hidden('save'), $wpjobportal_allowed_form); ?>

                <!-- Zywrap -->
                <div class="wpjp-ai-card <?php echo $wpjobportal_current === 'zywrap' ? 'is-current' : ''; ?>">
                    <h3>
                        <label><input type="radio" name="ai_engine" value="zywrap" <?php checked($wpjobportal_current, 'zywrap'); ?>> Zywrap</label>
                        <span class="wpjp-ai-badge"><?php echo esc_html__('Recommended', 'wp-job-portal'); ?></span>
                        <?php if ($wpjobportal_zconnected) : ?><span class="wpjp-ai-badge is-green"><?php echo esc_html__('Connected', 'wp-job-portal'); ?></span><?php endif; ?>
                    </h3>
                    <p class="wpjp-ai-muted"><?php echo esc_html($wpjobportal_engines['zywrap']['blurb']); ?></p>

                    <?php if ($wpjobportal_zconnected) : ?>
                        <p>
                            <?php if (!empty($wpjobportal_account['email'])) : ?>
                                <?php /* translators: %s: e-mail address of the Zywrap account */ echo esc_html(sprintf(__('Account: %s', 'wp-job-portal'), $wpjobportal_account['email'])); ?>
                            <?php else : ?>
                                <?php /* translators: %s: the last characters of the key */ echo esc_html(sprintf(__('Key: %s', 'wp-job-portal'), WPJOBPORTAL_AI_Engine::keyHint('zywrap'))); ?>
                            <?php endif; ?>
                            <?php if (isset($wpjobportal_account['credits']) && $wpjobportal_account['credits'] !== null) : ?>
                                &middot; <?php /* translators: %s: number of credits */ echo esc_html(sprintf(__('%s credits available', 'wp-job-portal'), number_format_i18n((int) $wpjobportal_account['credits']))); ?>
                            <?php endif; ?>
                            &middot; <a href="<?php echo esc_url(WPJOBPORTAL_AI_Engine::zywrapUrl('dashboard/credits')); ?>" target="_blank" rel="noopener"><?php echo esc_html__('Buy credits on zywrap.com', 'wp-job-portal'); ?></a>
                        </p>
                        <?php if ($wpjobportal_credit_line['text'] !== '') : ?>
                            <p class="<?php echo $wpjobportal_credit_line['warn'] ? 'wpjp-ai-alert' : ''; ?>"><?php echo esc_html($wpjobportal_credit_line['text']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($wpjobportal_zmodels)) : ?>
                            <div class="wpjp-ai-row">
                                <label for="ai_model_zywrap"><?php echo esc_html__('Model', 'wp-job-portal'); ?></label>
                                <select name="ai_model_zywrap" id="ai_model_zywrap">
                                    <option value=""><?php echo esc_html__('Let Zywrap choose (recommended)', 'wp-job-portal'); ?></option>
                                    <?php $wpjobportal_zm = (string) get_option(WPJOBPORTAL_AI_Engine::OPT_ZYWRAP_MODEL, ''); ?>
                                    <?php foreach ($wpjobportal_zmodels as $wpjobportal_m) : ?>
                                        <option value="<?php echo esc_attr($wpjobportal_m->code); ?>" <?php selected($wpjobportal_zm, $wpjobportal_m->code); ?>><?php echo esc_html($wpjobportal_m->name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <?php $wpjobportal_data_version = get_option('wpjobportal_zywrap_version', ''); ?>
                        <div class="wpjp-ai-sub">
                            <div class="wpjp-ai-sub-head">
                                <strong><?php echo esc_html__('Zywrap catalogue', 'wp-job-portal'); ?></strong>
                                <span class="wpjp-ai-sub-state">
                                    <?php if (empty($wpjobportal_data_version)) : ?>
                                        <?php echo esc_html__('Not downloaded yet.', 'wp-job-portal'); ?>
                                    <?php else :
                                        $wpjobportal_when = get_option('wpjobportal_zywrap_version_time');
                                        if (!empty($wpjobportal_when)) {
                                            /* translators: %s: how long ago, e.g. 3 days */
                                            echo esc_html(sprintf(__('Last updated %s ago.', 'wp-job-portal'), human_time_diff(strtotime($wpjobportal_when), strtotime(date_i18n('Y-m-d H:i:s')))));
                                        }
                                    endif; ?>
                                </span>
                                <?php if (empty($wpjobportal_data_version)) : ?>
                                    <button data-type="1" type="button" class="zywrap-full-import-btn button"><?php echo esc_html__('Download the catalogue', 'wp-job-portal'); ?></button>
                                <?php else : ?>
                                    <button data-type="2" type="button" class="zywrap-full-import-btn button"><?php echo esc_html__('Get updates', 'wp-job-portal'); ?></button>
                                <?php endif; ?>
                            </div>
                            <p class="wpjp-ai-muted"><?php echo esc_html__('The playground and the editor buttons choose from Zywrap\'s catalogue of prompts, models and languages, kept on this site. The AI buttons on the job, company and resume forms do not need it.', 'wp-job-portal'); ?></p>
                            <div id="wpjobportal-zywrap-sync-result"></div>
                        </div>

                        <div class="wpjp-ai-actions">
                            <?php if ($wpjobportal_credit_line['claim']) : ?>
                                <button type="submit" form="wpjp-ai-f-claim" class="button button-primary"><?php echo esc_html__('Claim the licence credits', 'wp-job-portal'); ?></button>
                            <?php endif; ?>
                            <button type="submit" form="wpjp-ai-f-disconnect" class="button-link wpjp-ai-danger"><?php echo esc_html__('Disconnect Zywrap', 'wp-job-portal'); ?></button>
                        </div>
                    <?php else : ?>
                        <?php if ($wpjobportal_credit_line['text'] !== '') : ?>
                            <p><strong><?php echo esc_html($wpjobportal_credit_line['text']); ?></strong></p>
                        <?php endif; ?>
                        <div id="wpjp-ai-zsignup">
                            <div class="wpjp-ai-row">
                                <label for="wpjp-ai-zemail"><?php echo esc_html__('Your e-mail', 'wp-job-portal'); ?></label>
                                <input type="email" id="wpjp-ai-zemail" value="<?php echo esc_attr(wp_get_current_user()->user_email); ?>">
                            </div>
                            <div class="wpjp-ai-row">
                                <label for="wpjp-ai-zname"><?php echo esc_html__('Your name (optional)', 'wp-job-portal'); ?></label>
                                <input type="text" id="wpjp-ai-zname" value="<?php echo esc_attr(wp_get_current_user()->display_name); ?>">
                            </div>
                            <p class="wpjp-ai-consent">
                                <label><input type="checkbox" id="wpjp-ai-zaccept" value="1">
                                <?php
                                echo wp_kses(sprintf(
                                    /* translators: 1: link to the Zywrap terms, 2: link to the Zywrap privacy policy */
                                    __('I agree to the Zywrap %1$s and %2$s, and to this site\'s address and my e-mail being sent to Zywrap to create or connect the account.', 'wp-job-portal'),
                                    '<a href="https://zywrap.com/terms" target="_blank" rel="noopener">' . esc_html__('Terms of Service', 'wp-job-portal') . '</a>',
                                    '<a href="https://zywrap.com/privacy" target="_blank" rel="noopener">' . esc_html__('Privacy Policy', 'wp-job-portal') . '</a>'
                                ), array('a' => array('href' => true, 'target' => true, 'rel' => true)));
                                ?></label>
                            </p>
                            <div class="wpjp-ai-actions">
                                <button type="button" class="button button-primary" id="wpjp-ai-zstart"><?php echo esc_html__('Create or connect a free Zywrap account', 'wp-job-portal'); ?></button>
                                <span class="wpjp-ai-muted"><?php echo esc_html__('New accounts start with 10,000 free credits.', 'wp-job-portal'); ?></span>
                            </div>
                            <div id="wpjp-ai-zcode" style="display:none">
                                <div class="wpjp-ai-row">
                                    <label for="wpjp-ai-zcodein"><?php echo esc_html__('6-digit code from the e-mail', 'wp-job-portal'); ?></label>
                                    <div class="wpjp-ai-inline-controls">
                                        <input type="text" id="wpjp-ai-zcodein" inputmode="numeric" maxlength="7" autocomplete="one-time-code">
                                        <button type="button" class="button button-primary" id="wpjp-ai-zverify"><?php echo esc_html__('Connect', 'wp-job-portal'); ?></button>
                                        <button type="button" class="button-link" id="wpjp-ai-zresend"><?php echo esc_html__('Send a new code', 'wp-job-portal'); ?></button>
                                    </div>
                                </div>
                            </div>
                            <div id="wpjp-ai-zmsg" aria-live="polite"></div>
                        </div>
                        <div class="wpjp-ai-sub">
                            <div class="wpjp-ai-row">
                                <label for="ai_key_zywrap"><?php echo esc_html__('Or paste a Zywrap key', 'wp-job-portal'); ?></label>
                                <input type="password" name="ai_key_zywrap" id="ai_key_zywrap" value="" autocomplete="new-password">
                            </div>
                            <p class="wpjp-ai-muted wpjp-ai-indent"><?php echo esc_html__('The key is checked with the provider before it is saved, and kept encrypted.', 'wp-job-portal'); ?></p>
                            <details>
                                <summary><?php echo esc_html__('Other ways to connect', 'wp-job-portal'); ?></summary>
                                <p class="wpjp-ai-muted"><?php echo esc_html__('Signed in to Zywrap with Google, GitHub or two-factor sign-in? Connect on zywrap.com instead:', 'wp-job-portal'); ?></p>
                                <p><button type="submit" class="button" name="do" value="browser" formnovalidate><?php echo esc_html__('Connect on zywrap.com instead', 'wp-job-portal'); ?></button></p>
                            </details>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Anthropic and OpenAI -->
                <?php foreach (array('anthropic', 'openai') as $wpjobportal_id) :
                    $wpjobportal_def = $wpjobportal_engines[$wpjobportal_id];
                    $wpjobportal_hint = WPJOBPORTAL_AI_Engine::keyHint($wpjobportal_id);
                    $wpjobportal_model = WPJOBPORTAL_AI_Engine::model($wpjobportal_id);
                    ?>
                    <div class="wpjp-ai-card <?php echo $wpjobportal_current === $wpjobportal_id ? 'is-current' : ''; ?>">
                        <h3>
                            <label><input type="radio" name="ai_engine" value="<?php echo esc_attr($wpjobportal_id); ?>" <?php checked($wpjobportal_current, $wpjobportal_id); ?>> <?php echo esc_html($wpjobportal_def['label']); ?></label>
                            <?php if ($wpjobportal_hint !== '') : ?><span class="wpjp-ai-badge is-green"><?php echo esc_html__('Key saved', 'wp-job-portal'); ?></span><?php endif; ?>
                        </h3>
                        <p class="wpjp-ai-muted"><?php echo esc_html($wpjobportal_def['blurb']); ?></p>
                        <div class="wpjp-ai-row">
                            <label for="ai_key_<?php echo esc_attr($wpjobportal_id); ?>"><?php echo esc_html__('API key', 'wp-job-portal'); ?></label>
                            <input type="password" name="ai_key_<?php echo esc_attr($wpjobportal_id); ?>" id="ai_key_<?php echo esc_attr($wpjobportal_id); ?>" value="" autocomplete="new-password"
                                placeholder="<?php echo esc_attr($wpjobportal_hint !== '' ? $wpjobportal_hint . '  (' . __('leave empty to keep', 'wp-job-portal') . ')' : ''); ?>">
                        </div>
                        <p class="wpjp-ai-muted wpjp-ai-indent"><?php echo esc_html($wpjobportal_def['keyhint']); ?> <?php echo esc_html__('The key is checked with the provider before it is saved, and kept encrypted.', 'wp-job-portal'); ?></p>
                        <div class="wpjp-ai-row">
                            <label for="ai_model_<?php echo esc_attr($wpjobportal_id); ?>"><?php echo esc_html__('Model', 'wp-job-portal'); ?></label>
                            <select name="ai_model_<?php echo esc_attr($wpjobportal_id); ?>" id="ai_model_<?php echo esc_attr($wpjobportal_id); ?>">
                                <?php foreach ($wpjobportal_def['models'] as $wpjobportal_code => $wpjobportal_name) : ?>
                                    <option value="<?php echo esc_attr($wpjobportal_code); ?>" <?php selected($wpjobportal_model, $wpjobportal_code); ?>><?php echo esc_html($wpjobportal_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php if ($wpjobportal_hint !== '') : ?>
                            <div class="wpjp-ai-actions">
                                <button type="submit" form="wpjp-ai-f-forget-<?php echo esc_attr($wpjobportal_id); ?>" class="button-link wpjp-ai-danger"><?php /* translators: %s: provider name */ echo esc_html(sprintf(__('Remove the %s key', 'wp-job-portal'), $wpjobportal_def['label'])); ?></button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <div class="wpjp-ai-card">
                    <div class="wpjp-ai-row">
                        <label for="ai_fallback"><?php echo esc_html__('Spare engine', 'wp-job-portal'); ?></label>
                        <select name="ai_fallback" id="ai_fallback">
                            <option value=""><?php echo esc_html__('None', 'wp-job-portal'); ?></option>
                            <?php foreach ($wpjobportal_engines as $wpjobportal_id => $wpjobportal_def) : ?>
                                <option value="<?php echo esc_attr($wpjobportal_id); ?>" <?php selected(WPJOBPORTAL_AI_Engine::fallbackId(), $wpjobportal_id); ?>><?php echo esc_html($wpjobportal_def['label']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <p class="wpjp-ai-muted wpjp-ai-indent"><?php echo esc_html__('Used only when the chosen engine is down, busy or out of credit, and only if it is set up.', 'wp-job-portal'); ?></p>
                </div>

                <div class="wpjp-ai-footer">
                    <button type="submit" class="button button-primary"><?php echo esc_html__('Save', 'wp-job-portal'); ?></button>
                    <button type="submit" form="wpjp-ai-f-test" class="button" <?php disabled(!WPJOBPORTAL_AI_Engine::usable()); ?>><?php echo esc_html__('Test the connection', 'wp-job-portal'); ?></button>
                </div>
            </form>

            <!-- The one-click actions: small forms of their own, which the buttons above point at. -->
            <form method="post" action="<?php echo esc_url($wpjobportal_action); ?>" id="wpjp-ai-f-test" hidden>
                <?php echo wp_kses($wpjobportal_hidden('test'), $wpjobportal_allowed_form); ?>
            </form>
            <?php if ($wpjobportal_zconnected) : ?>
                <form method="post" action="<?php echo esc_url($wpjobportal_action); ?>" id="wpjp-ai-f-claim" hidden>
                    <?php echo wp_kses($wpjobportal_hidden('claim'), $wpjobportal_allowed_form); ?>
                </form>
                <form method="post" action="<?php echo esc_url($wpjobportal_action); ?>" id="wpjp-ai-f-disconnect" hidden onsubmit="return confirm('<?php echo esc_js(__('Disconnect Zywrap from this site?', 'wp-job-portal')); ?>');">
                    <?php echo wp_kses($wpjobportal_hidden('disconnect'), $wpjobportal_allowed_form); ?>
                </form>
            <?php endif; ?>
            <?php foreach (array('anthropic', 'openai') as $wpjobportal_id) : if (WPJOBPORTAL_AI_Engine::keyHint($wpjobportal_id) === '') { continue; } ?>
                <form method="post" action="<?php echo esc_url($wpjobportal_action); ?>" id="wpjp-ai-f-forget-<?php echo esc_attr($wpjobportal_id); ?>" hidden onsubmit="return confirm('<?php echo esc_js(__('Remove this key from the site?', 'wp-job-portal')); ?>');">
                    <?php echo wp_kses($wpjobportal_hidden('forget', array('engine' => $wpjobportal_id)), $wpjobportal_allowed_form); ?>
                </form>
            <?php endforeach; ?>

<?php elseif ($wpjobportal_tab === 'features') : ?>
            <form method="post" action="<?php echo esc_url($wpjobportal_action); ?>">
                <?php echo wp_kses($wpjobportal_hidden('save'), $wpjobportal_allowed_form); ?>
                <div class="wpjp-ai-card">
                    <p><label><input type="checkbox" name="ai_master" value="1" <?php checked(WPJOBPORTAL_AI_Policy::enabled()); ?>>
                        <strong><?php echo esc_html__('AI is switched on for this site', 'wp-job-portal'); ?></strong></label></p>
                    <p class="wpjp-ai-muted"><?php echo esc_html__('Off means off: no AI button is shown and nothing is sent anywhere, whatever is set below.', 'wp-job-portal'); ?></p>
                </div>
                <div class="wpjp-ai-card">
                    <table class="wpjp-ai-table">
                        <thead><tr><th><?php echo esc_html__('On', 'wp-job-portal'); ?></th><th><?php echo esc_html__('Task', 'wp-job-portal'); ?></th><th><?php echo esc_html__('Who uses it', 'wp-job-portal'); ?></th></tr></thead>
                        <tbody>
                        <?php
                        $wpjobportal_who = array(
                            'employer'  => __('Employers and administrators', 'wp-job-portal'),
                            'jobseeker' => __('Job seekers and administrators', 'wp-job-portal'),
                            'any'       => __('Signed-in users and administrators', 'wp-job-portal'),
                        );
                        foreach (WPJOBPORTAL_AI_Tasks::catalogue() as $wpjobportal_id => $wpjobportal_task) : ?>
                            <tr>
                                <td><input type="checkbox" name="ai_task[<?php echo esc_attr($wpjobportal_id); ?>]" value="1" <?php checked(WPJOBPORTAL_AI_Tasks::enabled($wpjobportal_id)); ?> aria-label="<?php echo esc_attr($wpjobportal_task['label']); ?>"></td>
                                <td><?php echo esc_html($wpjobportal_task['label']); ?></td>
                                <td class="wpjp-ai-muted"><?php echo esc_html(isset($wpjobportal_who[$wpjobportal_task['who']]) ? $wpjobportal_who[$wpjobportal_task['who']] : $wpjobportal_task['who']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p class="wpjp-ai-muted"><?php echo esc_html__('Administrators can use every task while AI is on. Each task runs only when a person presses its button; nothing runs on its own, and no task ranks or rejects candidates.', 'wp-job-portal'); ?></p>
                </div>
                <p><button type="submit" class="button button-primary"><?php echo esc_html__('Save', 'wp-job-portal'); ?></button></p>
            </form>

<?php elseif ($wpjobportal_tab === 'usage') :
    $wpjobportal_budget = WPJOBPORTAL_AI_Usage::budget();
    $wpjobportal_day = WPJOBPORTAL_AI_Usage::spend('day');
    $wpjobportal_month = WPJOBPORTAL_AI_Usage::spend('month');
    $wpjobportal_tasks = WPJOBPORTAL_AI_Tasks::catalogue();
    ?>
            <?php foreach (WPJOBPORTAL_AI_Usage::alerts() as $wpjobportal_alert) : ?>
                <div class="wpjp-ai-alert <?php echo $wpjobportal_alert['level'] === 'stop' ? 'is-stop' : ''; ?>">
                    <?php echo esc_html($wpjobportal_alert['text']); ?>
                    <?php if (!empty($wpjobportal_alert['link'])) : ?> <a href="<?php echo esc_url($wpjobportal_alert['link']); ?>" target="_blank" rel="noopener"><?php echo esc_html__('zywrap.com', 'wp-job-portal'); ?></a><?php endif; ?>
                </div>
            <?php endforeach; ?>
            <div class="wpjp-ai-card">
                <table class="wpjp-ai-table">
                    <thead><tr><th></th><th><?php echo esc_html__('Requests', 'wp-job-portal'); ?></th><th><?php echo esc_html__('Failed', 'wp-job-portal'); ?></th><th><?php echo esc_html__('Tokens', 'wp-job-portal'); ?></th><th><?php echo esc_html__('Cost on your own key', 'wp-job-portal'); ?></th></tr></thead>
                    <tbody>
                        <?php foreach (array(__('Today', 'wp-job-portal') => $wpjobportal_day, __('This month', 'wp-job-portal') => $wpjobportal_month) as $wpjobportal_label => $wpjobportal_s) : ?>
                            <tr>
                                <th><?php echo esc_html($wpjobportal_label); ?></th>
                                <td><?php echo esc_html(number_format_i18n($wpjobportal_s['calls'])); ?></td>
                                <td><?php echo esc_html(number_format_i18n($wpjobportal_s['failed'])); ?></td>
                                <td><?php echo esc_html(number_format_i18n($wpjobportal_s['tokens'])); ?></td>
                                <td><?php echo esc_html(WPJOBPORTAL_AI_Usage::money($wpjobportal_s['cost'])); ?><?php if ($wpjobportal_s['zywrap'] > 0) : ?> <span class="wpjp-ai-muted">(<?php /* translators: %s: number of requests */ echo esc_html(sprintf(__('%s on Zywrap credits', 'wp-job-portal'), number_format_i18n($wpjobportal_s['zywrap']))); ?>)</span><?php endif; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="wpjp-ai-card">
                <h3><?php echo esc_html__('This month by task', 'wp-job-portal'); ?></h3>
                <table class="wpjp-ai-table">
                    <thead><tr><th><?php echo esc_html__('Task', 'wp-job-portal'); ?></th><th><?php echo esc_html__('Requests', 'wp-job-portal'); ?></th><th><?php echo esc_html__('Failed', 'wp-job-portal'); ?></th><th><?php echo esc_html__('Tokens', 'wp-job-portal'); ?></th><th><?php echo esc_html__('Cost', 'wp-job-portal'); ?></th></tr></thead>
                    <tbody>
                    <?php $wpjobportal_rows = WPJOBPORTAL_AI_Usage::breakdown('feature', 'month'); ?>
                    <?php if (empty($wpjobportal_rows)) : ?>
                        <tr><td colspan="5" class="wpjp-ai-muted"><?php echo esc_html__('No AI requests this month yet.', 'wp-job-portal'); ?></td></tr>
                    <?php endif; ?>
                    <?php foreach ($wpjobportal_rows as $wpjobportal_r) : ?>
                        <tr>
                            <td><?php echo esc_html(isset($wpjobportal_tasks[$wpjobportal_r['key']]) ? $wpjobportal_tasks[$wpjobportal_r['key']]['label'] : ($wpjobportal_r['key'] === 'playground' ? __('Playground and editor', 'wp-job-portal') : ($wpjobportal_r['key'] === 'test' ? __('Connection tests', 'wp-job-portal') : $wpjobportal_r['key']))); ?></td>
                            <td><?php echo esc_html(number_format_i18n($wpjobportal_r['calls'])); ?></td>
                            <td><?php echo esc_html(number_format_i18n($wpjobportal_r['failed'])); ?></td>
                            <td><?php echo esc_html(number_format_i18n($wpjobportal_r['tokens'])); ?></td>
                            <td><?php echo esc_html(WPJOBPORTAL_AI_Usage::money($wpjobportal_r['cost'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <form method="post" action="<?php echo esc_url($wpjobportal_action); ?>">
                <?php echo wp_kses($wpjobportal_hidden('save'), $wpjobportal_allowed_form); ?>
                <div class="wpjp-ai-card">
                    <h3><?php echo esc_html__('Limits', 'wp-job-portal'); ?></h3>
                    <p class="wpjp-ai-muted"><?php echo esc_html__('0 means no limit. A limit is checked before a request is sent.', 'wp-job-portal'); ?></p>
                    <div class="wpjp-ai-row"><label for="ai_usercalls"><?php echo esc_html__('Requests per user per day', 'wp-job-portal'); ?></label><input type="number" min="0" step="1" name="ai_usercalls" id="ai_usercalls" value="<?php echo esc_attr($wpjobportal_budget['usercalls']); ?>"> <span class="wpjp-ai-muted"><?php echo esc_html__('Employers and job seekers; administrators are not limited.', 'wp-job-portal'); ?></span></div>
                    <div class="wpjp-ai-row"><label for="ai_daycalls"><?php echo esc_html__('Requests per day, whole site', 'wp-job-portal'); ?></label><input type="number" min="0" step="1" name="ai_daycalls" id="ai_daycalls" value="<?php echo esc_attr($wpjobportal_budget['daycalls']); ?>"></div>
                    <div class="wpjp-ai-row"><label for="ai_daymoney"><?php echo esc_html__('Daily budget, own key (USD)', 'wp-job-portal'); ?></label><input type="number" min="0" step="0.01" name="ai_daymoney" id="ai_daymoney" value="<?php echo esc_attr($wpjobportal_budget['daymoney']); ?>"></div>
                    <div class="wpjp-ai-row"><label for="ai_monthmoney"><?php echo esc_html__('Monthly budget, own key (USD)', 'wp-job-portal'); ?></label><input type="number" min="0" step="0.01" name="ai_monthmoney" id="ai_monthmoney" value="<?php echo esc_attr($wpjobportal_budget['monthmoney']); ?>"></div>
                    <p><label><input type="checkbox" name="ai_alerts" value="1" <?php checked($wpjobportal_budget['alerts']); ?>> <?php echo esc_html__('E-mail the site administrator at 80% and 100% of a limit', 'wp-job-portal'); ?></label></p>
                </div>
                <p><button type="submit" class="button button-primary"><?php echo esc_html__('Save', 'wp-job-portal'); ?></button></p>
            </form>
            <div class="wpjp-ai-card">
                <h3><?php echo esc_html__('Latest requests', 'wp-job-portal'); ?></h3>
                <table class="wpjp-ai-table">
                    <thead><tr><th><?php echo esc_html__('When', 'wp-job-portal'); ?></th><th><?php echo esc_html__('Task', 'wp-job-portal'); ?></th><th><?php echo esc_html__('Engine / model', 'wp-job-portal'); ?></th><th><?php echo esc_html__('User', 'wp-job-portal'); ?></th><th><?php echo esc_html__('Result', 'wp-job-portal'); ?></th></tr></thead>
                    <tbody>
                    <?php foreach (WPJOBPORTAL_AI_Usage::recent(25) as $wpjobportal_r) :
                        $wpjobportal_u = $wpjobportal_r->userid ? get_userdata((int) $wpjobportal_r->userid) : null; ?>
                        <tr>
                            <td><?php echo esc_html($wpjobportal_r->created); ?></td>
                            <td><?php echo esc_html(isset($wpjobportal_tasks[$wpjobportal_r->feature]) ? $wpjobportal_tasks[$wpjobportal_r->feature]['label'] : $wpjobportal_r->feature); ?></td>
                            <td><?php echo esc_html($wpjobportal_r->engine . ' / ' . $wpjobportal_r->model); ?></td>
                            <td><?php echo esc_html($wpjobportal_u ? $wpjobportal_u->user_login : '-'); ?></td>
                            <td><?php echo $wpjobportal_r->ok ? esc_html__('OK', 'wp-job-portal') : esc_html($wpjobportal_r->error); ?> <span class="wpjp-ai-muted"><?php echo esc_html(number_format_i18n((int) $wpjobportal_r->ms)); ?> ms</span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="wpjp-ai-muted"><?php echo esc_html__('Only counts are kept here, never the text that was sent or written. Rows older than 400 days are removed.', 'wp-job-portal'); ?></p>
            </div>

<?php else : /* privacy */ ?>
            <form method="post" action="<?php echo esc_url($wpjobportal_action); ?>">
                <?php echo wp_kses($wpjobportal_hidden('save'), $wpjobportal_allowed_form); ?>
                <div class="wpjp-ai-card">
                    <h3><?php echo esc_html__('Removed before text leaves the site', 'wp-job-portal'); ?></h3>
                    <?php foreach (WPJOBPORTAL_AI_Policy::redactionModes() as $wpjobportal_id => $wpjobportal_mode) : ?>
                        <p><label><input type="radio" name="ai_redact" value="<?php echo esc_attr($wpjobportal_id); ?>" <?php checked(WPJOBPORTAL_AI_Policy::redactionMode(), $wpjobportal_id); ?>>
                            <strong><?php echo esc_html($wpjobportal_mode['label']); ?></strong></label><br><span class="wpjp-ai-muted"><?php echo esc_html($wpjobportal_mode['blurb']); ?></span></p>
                    <?php endforeach; ?>
                    <p class="wpjp-ai-muted"><?php echo esc_html__('Names, e-mail, phone, address and date-of-birth fields of a resume are never sent, at any level.', 'wp-job-portal'); ?></p>
                </div>
                <div class="wpjp-ai-card">
                    <h3><?php echo esc_html__('Notice under the AI buttons', 'wp-job-portal'); ?></h3>
                    <div class="wpjp-ai-row is-wide"><input type="text" name="ai_disclosure" value="<?php echo esc_attr((string) get_option(WPJOBPORTAL_AI_Policy::OPT_NOTICE, '')); ?>" placeholder="<?php echo esc_attr(WPJOBPORTAL_AI_Policy::disclosure()); ?>" style="width:100%"></div>
                    <p class="wpjp-ai-muted"><?php echo esc_html__('Leave empty for the default. Tell people their text is sent to an AI provider; mention it in your privacy policy too.', 'wp-job-portal'); ?></p>
                </div>
                <p><button type="submit" class="button button-primary"><?php echo esc_html__('Save', 'wp-job-portal'); ?></button></p>
            </form>
            <div class="wpjp-ai-card">
                <h3><?php echo esc_html__('Changes to these settings', 'wp-job-portal'); ?></h3>
                <table class="wpjp-ai-table"><tbody>
                <?php foreach (array_slice(WPJOBPORTAL_AI_Policy::journal(), 0, 20) as $wpjobportal_e) :
                    $wpjobportal_u = !empty($wpjobportal_e['user']) ? get_userdata((int) $wpjobportal_e['user']) : null; ?>
                    <tr><td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), (int) $wpjobportal_e['when'])); ?></td>
                        <td><?php echo esc_html(WPJOBPORTAL_AI_Policy::describe($wpjobportal_e)); ?></td>
                        <td class="wpjp-ai-muted"><?php echo esc_html($wpjobportal_u ? $wpjobportal_u->user_login : ''); ?></td></tr>
                <?php endforeach; ?>
                </tbody></table>
            </div>
<?php endif; ?>
        </div>
    </div>
</div>
<?php
$wpjobportal_js = "
jQuery(function($) {
    var ajaxurl = '" . esc_url(admin_url('admin-ajax.php')) . "';
    var nonce = '" . esc_js(wp_create_nonce(WPJOBPORTAL_Zywrap_Connect::NONCE)) . "';
    var msg = $('#wpjp-ai-zmsg');
    function say(text, bad) {
        msg.empty().append($('<p>').addClass(bad ? 'wpjp-ai-alert is-stop' : 'wpjp-ai-alert').text(text));
    }
    function start() {
        if (!$('#wpjp-ai-zaccept').is(':checked')) { say('" . esc_js(__('Tick the box to agree to the Zywrap Terms of Service and Privacy Policy.', 'wp-job-portal')) . "', true); return; }
        $('#wpjp-ai-zstart').prop('disabled', true);
        say('" . esc_js(__('Sending a code to your e-mail...', 'wp-job-portal')) . "');
        $.post(ajaxurl, {action: 'wpjobportal_zywrap_signup_start', _wpnonce: nonce, email: $('#wpjp-ai-zemail').val(), name: $('#wpjp-ai-zname').val(), accept: '1'})
            .done(function(r) {
                if (r && r.success) { $('#wpjp-ai-zcode').show(); $('#wpjp-ai-zcodein').focus(); say(r.data.existingAccount ? '" . esc_js(__('You already have a Zywrap account. Enter the code we sent to sign in.', 'wp-job-portal')) . "' : '" . esc_js(__('Check your e-mail for a 6-digit code.', 'wp-job-portal')) . "'); }
                else { say((r && r.data && r.data.message) ? r.data.message : '" . esc_js(__('Something went wrong. Try again.', 'wp-job-portal')) . "', true); }
            })
            .fail(function() { say('" . esc_js(__('The request failed. Try again.', 'wp-job-portal')) . "', true); })
            .always(function() { $('#wpjp-ai-zstart').prop('disabled', false); });
    }
    $('#wpjp-ai-zstart, #wpjp-ai-zresend').on('click', function(e) { e.preventDefault(); start(); });
    $('#wpjp-ai-zverify').on('click', function(e) {
        e.preventDefault();
        var btn = $(this).prop('disabled', true);
        $.post(ajaxurl, {action: 'wpjobportal_zywrap_signup_verify', _wpnonce: nonce, code: $('#wpjp-ai-zcodein').val()})
            .done(function(r) {
                if (r && r.success) { window.location.reload(); return; }
                say((r && r.data && r.data.message) ? r.data.message : '" . esc_js(__('Something went wrong. Try again.', 'wp-job-portal')) . "', true);
            })
            .fail(function() { say('" . esc_js(__('The request failed. Try again.', 'wp-job-portal')) . "', true); })
            .always(function() { btn.prop('disabled', false); });
    });

    // The Zywrap catalogue download (unchanged from 2.8.0, batched).
    function processImportBatch() {
        var resultDiv = $('#wpjobportal-zywrap-sync-result');
        $.post(ajaxurl, {action: 'wpjobportal_ajax', wpjobportalme: 'zywrap', task: 'importZywrapBatchProcess', '_wpnonce': '" . esc_js(wp_create_nonce('zywrap_full_import')) . "'})
        .done(function(response) {
            if (response.success) {
                var data = response.data;
                if (data.status === 'paused') {
                    resultDiv.text(data.message + ' (" . esc_js(__('Remaining', 'wp-job-portal')) . ": ' + data.remaining + ')');
                    processImportBatch();
                } else if (data.status === 'completed') {
                    resultDiv.text(data.message);
                    setTimeout(function() { location.reload(); }, 3000);
                }
            } else {
                resultDiv.text((response.data && response.data.message) || '" . esc_js(__('Error in batch processing', 'wp-job-portal')) . "');
                $('.zywrap-full-import-btn').prop('disabled', false);
            }
        })
        .fail(function(xhr, textStatus) { resultDiv.text('" . esc_js(__('Batch Error', 'wp-job-portal')) . ": ' + textStatus); $('.zywrap-full-import-btn').prop('disabled', false); });
    }
    $(document).on('click', '.zywrap-full-import-btn', function(e) {
        e.preventDefault();
        var resultDiv = $('#wpjobportal-zywrap-sync-result');
        var type = $(this).attr('data-type');
        resultDiv.text(type == 1 ? '" . esc_js(__('Downloading and importing... This may take several minutes.', 'wp-job-portal')) . "' : '" . esc_js(__('Fetching what changed...', 'wp-job-portal')) . "');
        $(this).prop('disabled', true);
        $.post(ajaxurl, {action: 'wpjobportal_ajax', wpjobportalme: 'zywrap', task: 'importZywrapData', actionType: type, '_wpnonce': '" . esc_js(wp_create_nonce('zywrap_full_import')) . "'})
        .done(function(response) {
            if (response.success) {
                var data = response.data;
                if (data.status === 'paused') { resultDiv.text(data.message + ' (" . esc_js(__('Remaining', 'wp-job-portal')) . ": ' + data.remaining + ')'); processImportBatch(); }
                else { resultDiv.text(data.message || '" . esc_js(__('Done.', 'wp-job-portal')) . "'); if (data.status === 'completed') { setTimeout(function() { location.reload(); }, 3000); } }
            } else {
                resultDiv.text((response.data && response.data.message) || '" . esc_js(__('Unknown error occurred', 'wp-job-portal')) . "');
                $('.zywrap-full-import-btn').prop('disabled', false);
            }
        })
        .fail(function(xhr, textStatus) { resultDiv.text('" . esc_js(__('Error: The request failed or timed out. Please check your PHP logs.', 'wp-job-portal')) . " (' + textStatus + ')'); $('.zywrap-full-import-btn').prop('disabled', false); });
    });
});
";
wp_register_script('wpjobportal-ai-settings', '', array('jquery'), WPJOBPORTAL_VERSION, true);
wp_enqueue_script('wpjobportal-ai-settings');
wp_add_inline_script('wpjobportal-ai-settings', $wpjobportal_js);
