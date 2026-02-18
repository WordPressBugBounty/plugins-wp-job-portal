<?php
if (!defined('ABSPATH')) die('Restricted Access');
// No PHP logic or DB queries here. We rely strictly on the JS variables passed by your controller.
?>

<style>
    /* --- VARIABLES & RESET --- */
    #zywrap-classic-modal-wrap {
        /* Modern SaaS Palette (Slate & Indigo) */
        --wjp-primary: #6366f1;       /* Indigo 500 */
        --wjp-primary-hover: #4f46e5; /* Indigo 600 */
        --wjp-primary-light: #eef2ff; /* Indigo 50 */
        --wjp-border: #cbd5e1;        /* Slate 300 - Crisper borders */
        --wjp-text-main: #0f172a;     /* Slate 900 - Darker text for contrast */
        --wjp-text-muted: #64748b;    /* Slate 500 */
        --wjp-bg-side: #f8fafc;       /* Slate 50 - Subtle sidebar bg */
        --wjp-bg-input: #ffffff;
        --wjp-radius: 12px;
        --wjp-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        box-sizing: border-box;
    }
    #zywrap-classic-modal-wrap * { box-sizing: border-box; }

    /* --- BACKDROP --- */
    #zywrap-classic-modal-backdrop {
        display: none;
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(15, 23, 42, 0.6); /* Darker, modern blur */
        z-index: 100000;
        backdrop-filter: blur(4px);
    }

    /* --- MODAL WRAPPER --- */
    #zywrap-classic-modal-wrap {
        display: none;
        position: fixed;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);

        /* DIMENSIONS */
        width: 95%;
        max-width: 1500px;
        min-width: 800px;
        height: 85vh; /* Fixed height for studio feel */

        background: #fff;
        border-radius: var(--wjp-radius);
        box-shadow: var(--wjp-shadow);
        z-index: 100001;
        font-family: -apple-system, BlinkMacSystemFont, "Inter", "Segoe UI", Roboto, sans-serif;

        /* LAYOUT */
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    /* --- HEADER --- */
    #zywrap-classic-modal-header {
        height: 64px;
        padding: 0 24px;
        border-bottom: 1px solid var(--wjp-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fff;
        flex-shrink: 0;
    }
    #zywrap-classic-modal-header h2 {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--wjp-text-main);
        display: flex;
        align-items: center;
        gap: 12px;
    }
    #zywrap-classic-modal-header h2:before {
        content: "\f10c"; /* Dashicons-art */
        font-family: dashicons;
        color: var(--wjp-primary);
        font-size: 24px;
        background: var(--wjp-primary-light);
        width: 32px; height: 32px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
    }
    #zywrap-classic-modal-close {
        font-size: 24px;
        text-decoration: none;
        color: var(--wjp-text-muted);
        line-height: 1;
        padding: 8px;
        border-radius: 6px;
        transition: all 0.2s;
    }
    #zywrap-classic-modal-close:hover { background: #fee2e2; color: #ef4444; }

    /* --- BODY --- */
    .zywrap-classic-body {
        display: flex;
        flex: 1;
        min-height: 0;
        overflow: hidden;
    }

    /* --- LEFT SIDEBAR --- */
    .zywrap-classic-sidebar {
        width: 340px;
        min-width: 340px;
        background: var(--wjp-bg-side);
        border-right: 1px solid var(--wjp-border);
        padding: 24px;
        overflow-y: auto; /* Internal Scroll */
        height: 100%;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    /* --- RIGHT WORKSPACE --- */
    .zywrap-classic-main {
        flex: 1;
        background: #fff;
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: hidden;
    }

    /* Prompt Section */
    .zywrap-workspace-prompt {
        padding: 20px 30px;
        flex-shrink: 0;
        background: #fff;
        border-bottom: 1px solid var(--wjp-border);
    }

    #zywrap-classic-prompt {
        width: 100%;
        height: 80px;
        min-height: 80px;
        resize: none;
        margin-top: 8px;
        border: 1px solid var(--wjp-border);
        border-radius: 8px;
        padding: 12px;
        font-size: 14px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    #zywrap-classic-prompt:focus { border-color: var(--wjp-primary); outline: none; box-shadow: 0 0 0 3px var(--wjp-primary-light); }

    .zywrap-action-row {
        margin-top: 12px;
        display: flex;
        justify-content: flex-end;
    }

    #zywrap-classic-run {
        background: var(--wjp-primary);
        border: none;
        color: #fff;
        padding: 8px 20px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex; align-items: center; gap: 8px;
        box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.3);
        transition: all 0.2s;
    }
    #zywrap-classic-run:hover { background: var(--wjp-primary-hover); transform: translateY(-1px); }

    /* Response Section */
    .zywrap-workspace-response {
        flex-grow: 1;
        min-height: 0;
        padding: 20px 30px 0 30px;
        display: flex;
        flex-direction: column;
        background: #fff;
    }

    #zywrap-classic-response-area {
        flex: 1;
        height: 100%;
        width: 100%;
        resize: none;
        font-family: 'Courier New', Courier, monospace;
        line-height: 1.6;
        border: 1px solid var(--wjp-border);
        border-radius: 8px 8px 0 0;
        border-bottom: none;
        padding: 20px;
        background: #fafafa;
        color: #334155;
    }
    #zywrap-classic-response-area:focus { background: #fff; border-color: var(--wjp-primary); outline: none; }

    /* --- FOOTER --- */
    #zywrap-classic-modal-footer {
        height: 64px;
        padding: 0 30px;
        border-top: 1px solid var(--wjp-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fff;
        flex-shrink: 0;
    }

    #zywrap-classic-insert-btn {
        background: #10b981;
        border: none;
        color: #fff;
        padding: 10px 24px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        margin-left: auto;
        box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.3);
        transition: all 0.2s;
    }
    #zywrap-classic-insert-btn:hover { background: #059669; transform: translateY(-1px); }

    /* --- UI ELEMENTS --- */
    .zywrap-label {
        display: block;
        margin-bottom: 6px;
        font-weight: 600;
        font-size: 12px;
        color: var(--wjp-text-main);
    }
    .zywrap-label span { color: var(--wjp-text-muted); font-weight: 400; font-size: 11px; margin-left: 4px; }

    /* Search */
    .zywrap-search-trigger {
        cursor: pointer; color: var(--wjp-primary); font-size: 13px; font-weight: 600;
        display: flex; align-items: center; justify-content: center; gap: 8px; padding: 10px;
        background: #fff; border: 1px dashed var(--wjp-border); border-radius: 8px;
        transition: all 0.2s;
    }
    .zywrap-search-trigger:hover { background: var(--wjp-primary-light); border-color: var(--wjp-primary); }
    #zywrap-search-wrapper { display: none; background: #fff; border: 1px solid var(--wjp-border); border-radius: 8px; padding: 15px; box-shadow: 0 4px 6px -2px rgba(0,0,0,0.05); }

    /* --- ADVANCED TOGGLE (UPDATED) --- */
    .sidebar-divider { height: 1px; background: var(--wjp-border); margin: 10px 0; flex-shrink: 0; opacity: 0.5; }

    .zywrap-advanced-toggle {
        border: 1px solid var(--wjp-border);
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
        flex-shrink: 0;
    }
    .zywrap-advanced-toggle summary {
        cursor: pointer; padding: 12px 16px; background: #fff; font-size: 13px; font-weight: 600;
        color: var(--wjp-text-main); display: flex; justify-content: space-between; align-items: center; outline: none;
        transition: background 0.2s;
    }
    .zywrap-advanced-toggle summary:hover { background: #f8fafc; }

    /* Rotate SVG Icon */
    .zywrap-advanced-toggle summary svg { transition: transform 0.3s ease; color: var(--wjp-text-muted); }
    .zywrap-advanced-toggle[open] summary svg { transform: rotate(180deg); color: var(--wjp-primary); }

    .zywrap-advanced-toggle[open] summary { border-bottom: 1px solid var(--wjp-border); background: #f8fafc; }
    .zywrap-advanced-content { padding: 16px; display: flex; flex-direction: column; gap: 15px; }

    /* --- SORT & CHIPS --- */
    .zywrap-header-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }

    /* Improved Sort Button */
    .zywrap-sort-toggle {
        font-size: 11px; color: var(--wjp-text-muted);
        background: #f1f5f9; border: 1px solid var(--wjp-border); border-radius: 20px;
        cursor: pointer; padding: 4px 10px;
        display: flex; align-items: center; gap: 6px;
        transition: all 0.2s;
    }
    .zywrap-sort-toggle:hover { border-color: var(--wjp-primary); color: var(--wjp-primary); background: #fff; }
    .zywrap-sort-toggle svg { width: 12px; height: 12px; stroke-width: 2.5; }

    .zywrap-chips-row { display: flex; gap: 8px; margin-bottom: 12px; }
    .zywrap-chip {
        flex: 1; font-size: 11px; padding: 6px; border-radius: 6px; border: 1px solid var(--wjp-border);
        background: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px;
        color: var(--wjp-text-muted); transition: all 0.2s;
    }
    .zywrap-chip:hover { border-color: var(--wjp-primary); color: var(--wjp-primary); }
    .zywrap-chip.active { background-color: var(--wjp-primary-light); border-color: var(--wjp-primary); color: var(--wjp-primary); font-weight: 600; }
    .zywrap-chip svg { width: 14px; height: 14px; fill: none; stroke: currentColor; stroke-width: 2; }

    /* Input Styling */
    textarea, input[type="text"], select {
        width: 100%;
        border: 1px solid var(--wjp-border);
        border-radius: 8px;
        padding: 10px;
        font-size: 13px;
        background: var(--wjp-bg-input);
        color: var(--wjp-text-main);
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    textarea:focus, input[type="text"]:focus, select:focus {
        border-color: var(--wjp-primary);
        box-shadow: 0 0 0 3px var(--wjp-primary-light);
        outline: none;
    }

    /* Chosen Fixes */
    .chosen-container { width: 100% !important; }
    .chosen-container-single .chosen-single { height: 38px !important; line-height: 36px !important; border-radius: 8px !important; border-color: var(--wjp-border) !important; background: #fff !important; box-shadow: none !important; font-size: 13px !important; color: var(--wjp-text-main) !important; }
    .chosen-container-active .chosen-single { border-color: var(--wjp-primary) !important; box-shadow: 0 0 0 3px var(--wjp-primary-light) !important; }

    /* Add these to your existing CSS */

    .zywrap-fab-button {
        transition: all 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
    }

    .zywrap-fab-button:hover {
        transform: scale(1.1) translateY(-4px) !important;
        background-color: #4f46e5 !important;
        box-shadow: 0 20px 25px -5px rgba(99, 102, 241, 0.4) !important;
    }

    .zywrap-fab-button:active {
        transform: scale(0.95);
    }

    /* Pulsing effect when generating (optional class to add via JS) */
    .zywrap-fab-loading {
        animation: zywrap-pulse 1.5s infinite;
    }

    @keyframes zywrap-pulse {
        0% { box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.7); }
        70% { box-shadow: 0 0 0 15px rgba(99, 102, 241, 0); }
        100% { box-shadow: 0 0 0 0 rgba(99, 102, 241, 0); }
    }

    /* Ensure modal is always on top of Gutenberg header */
    #zywrap-classic-modal-backdrop,
    #zywrap-classic-modal-wrap {
        z-index: 999999 !important;
    }

    @keyframes spin {
        from {
            transform: rotate(0deg);
        }
        to {
            transform: rotate(360deg);
        }
    }

</style>

<div id="zywrap-classic-modal-backdrop"></div>
<div id="zywrap-classic-modal-wrap">

    <div id="zywrap-classic-modal-header">
        <h2><?php echo __( 'AI Content Generator', 'wp-job-portal' ); ?></h2>
        <a href="#" id="zywrap-classic-modal-close">&times;</a>
    </div>
    <?php
        $wpjobportal_saved_key = get_option('wpjobportal_zywrap_api_key', '');
        ?>
        <div style="clear: both;">
            <?php if(!$wpjobportal_saved_key): ?>
                <div class="wpjobportal-setup-notice">
                    <span class="dashicons dashicons-warning" style="font-size: 24px; color: #d63638;"></span>
                    <a href="<?php echo esc_url(admin_url("admin.php?page=wpjobportal_zywrap&wpjobportallt=zywrap")); ?>">
                        <?php echo esc_html(__('Setup Required: Please add your API Key in settings.', 'wp-job-portal')); ?>
                    </a>
                </div>
            <?php else:
                global $wpdb;
                $wpjobportal_categories = $wpdb->get_results("SELECT code, name FROM `" . $wpdb->prefix . "wj_portal_zywrap_categories` ORDER BY ordering ASC");
                $wpjobportal_categories_count = count($wpjobportal_categories);
                if($wpjobportal_categories_count < 1):?>
                <div class="wpjobportal-setup-notice">
                    <span class="dashicons dashicons-warning" style="font-size: 24px; color: #d63638;"></span>
                    <a href="<?php echo esc_url(admin_url("admin.php?page=wpjobportal_zywrap&wpjobportallt=zywrap")); ?>">
                        <?php echo esc_html(__('Action Needed: Please sync the Wrapper Catalog (Step 2) in settings.', 'wp-job-portal')); ?>
                    </a>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    <div class="zywrap-classic-body">

        <aside class="zywrap-classic-sidebar">
            <div class="zywrap-search-trigger" id="zywrap-search-toggle-btn">
                <span class="dashicons dashicons-search"></span>
                <span id="zywrap-search-toggle-text"><?php echo __( 'Search Wrapper', 'wp-job-portal' ); ?></span>
            </div>

            <div id="zywrap-search-wrapper">
                <div id="zywrap-manual-search-container" style="margin-bottom:12px;">
                    <label class="zywrap-label"><?php echo __( 'Quick Search', 'wp-job-portal' ); ?></label>
                    <input type="text" id="zywrap-classic-search-input" placeholder="<?php echo esc_attr__( 'e.g. Blog, SEO...', 'wp-job-portal' ); ?>">
                </div>
                <div id="zywrap-search-results-container">
                    <label class="zywrap-label"><?php echo __( 'Results', 'wp-job-portal' ); ?></label>
                    <select id="zywrap-classic-search-select" class="wpjp-zywrap-jp-chosen">
                        <option value=""><?php echo __( '-- Select Result --', 'wp-job-portal' ); ?></option>
                    </select>
                </div>
            </div>

            <div class="zywrap-input-group">
                <label class="zywrap-label"><?php echo __( 'Category', 'wp-job-portal' ); ?></label>
                <select id="zywrap-classic-category" class="wpjp-zywrap-jp-chosen"></select>
            </div>

            <div class="zywrap-input-group">
                <div class="zywrap-header-row">
                    <label class="zywrap-label" style="margin:0;"><?php echo __( 'Wrapper Template', 'wp-job-portal' ); ?></label>

                    <button type="button" id="zywrap-classic-sort" class="zywrap-sort-toggle" data-ordering="1">
                        <span style="opacity:0.7;"><?php echo __( 'Sort:', 'wp-job-portal' ); ?></span>
                        <span id="zywrap-sort-text" style="font-weight:600; color:var(--wjp-text-main);"><?php echo __( 'Default', 'wp-job-portal' ); ?></span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg>
                    </button>
                </div>

                <div class="zywrap-chips-row">
                    <div id="filter-featured-classic" class="zywrap-chip" data-state="0">
                        <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg> <?php echo __( 'Featured', 'wp-job-portal' ); ?>
                    </div>
                    <div id="filter-base-classic" class="zywrap-chip" data-state="0">
                        <svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg> <?php echo __( 'Base', 'wp-job-portal' ); ?>
                    </div>
                </div>

                <select id="zywrap-classic-wrapper" class="wpjp-zywrap-jp-chosen" disabled>
                    <option value=""><?php echo __( '-- Select Category --', 'wp-job-portal' ); ?></option>
                </select>
                <div style="text-align:right; font-size:11px; color:var(--wjp-text-muted); margin-top:6px;" id="zywrap-classic-wrapper-count"></div>
            </div>

            <div class="zywrap-input-group">
                <label class="zywrap-label"><?php echo __( 'AI Model', 'wp-job-portal' ); ?></label>
                <select id="zywrap-classic-model" class="wpjp-zywrap-jp-chosen"></select>
            </div>

            <div class="zywrap-input-group">
                <label class="zywrap-label"><?php echo __( 'Language', 'wp-job-portal' ); ?></label>
                <select id="zywrap-classic-language" class="wpjp-zywrap-jp-chosen"></select>
            </div>

            <div class="sidebar-divider"></div>

            <details class="zywrap-advanced-toggle">
                <summary>
                    <span><?php echo __( 'Advanced Settings', 'wp-job-portal' ); ?></span>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                </summary>
                <div class="zywrap-advanced-content">
                    <div>
                        <label class="zywrap-label"><?php echo __( 'Reference Context', 'wp-job-portal' ); ?> <span><?php echo __( '(Source Data)', 'wp-job-portal' ); ?></span></label>
                        <textarea id="zywrap-classic-context" rows="4" placeholder="<?php echo esc_attr__( 'Paste source text or data context here...', 'wp-job-portal' ); ?>"></textarea>
                    </div>
                    <div>
                        <label class="zywrap-label"><?php echo __( 'SEO Keywords', 'wp-job-portal' ); ?></label>
                        <input type="text" id="zywrap-classic-seo" placeholder="<?php echo esc_attr__( 'comma, separated, keywords', 'wp-job-portal' ); ?>">
                    </div>
                    <div>
                        <label class="zywrap-label"><?php echo __( 'Negative Words', 'wp-job-portal' ); ?></label>
                        <input type="text" id="zywrap-classic-negative" placeholder="<?php echo esc_attr__( 'words to avoid...', 'wp-job-portal' ); ?>">
                    </div>
                    <div id="zywrap-classic-overrides-grid" style="display:grid; gap:12px;"></div>
                </div>
            </details>
        </aside>

        <main class="zywrap-classic-main">
            <div id="zywrap-classic-description" style="display:none; background:var(--wjp-primary-light); padding:14px 30px; border-bottom:1px solid #c7d2fe; color:var(--wjp-text-main); font-size:13px;">
                <label class="zywrap-label"><?php echo __( 'Description:', 'wp-job-portal' ); ?> </label>
                <div id="zywrap-classic-description-inner" >
                </div>

            </div>

            <div class="zywrap-workspace-prompt">
                <label class="zywrap-label"><?php echo __( 'Instructions / Prompt', 'wp-job-portal' ); ?> (<?php echo __( 'Optional', 'wp-job-portal' ); ?>)</label>
                <textarea id="zywrap-classic-prompt" placeholder="<?php echo esc_attr__( 'Describe exactly what you want the AI to create...', 'wp-job-portal' ); ?>"></textarea>
                <div class="zywrap-action-row">
                    <button type="button" id="zywrap-classic-run"><?php echo __( 'Generate Output', 'wp-job-portal' ); ?></button>
                </div>
            </div>

            <div class="zywrap-workspace-response">
                <label class="zywrap-label" style="display:flex; justify-content:space-between;">
                    <?php echo __( 'Generated Output', 'wp-job-portal' ); ?> <span style="font-weight:400; font-size:11px; color:#94a3b8;"><?php echo __( '(Editable)', 'wp-job-portal' ); ?></span>
                </label>
                <textarea id="zywrap-classic-response-area" placeholder="<?php echo esc_attr__( 'AI output will appear here...', 'wp-job-portal' ); ?>"></textarea>
            </div>
        </main>
    </div>

    <div id="zywrap-classic-modal-footer">
        <span id="zywrap-classic-spinner" class="spinner" style="float:none;"></span>
        <button type="button" id="zywrap-classic-insert-btn" style="display:none;"><?php echo __( 'Insert into Editor', 'wp-job-portal' ); ?></button>
    </div>
</div>