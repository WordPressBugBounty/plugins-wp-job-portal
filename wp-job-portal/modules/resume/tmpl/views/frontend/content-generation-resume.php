<?php if (!defined('ABSPATH')) die('Restricted Access');

$wpjobportal_desc_editor = isset($wpjobportal_desc_editor) ? $wpjobportal_desc_editor : '';

// Enqueue marked.js for Markdown parsing
wp_enqueue_script(
    'wpjobportal-marked-js',
    WPJOBPORTAL_PLUGIN_URL . 'includes/js/zywrap/marked.min.js',
    array('jquery'),
    '',
    true
);

// Register handle for inline JS injection
wp_register_script('wpjobportal-resume-inline-handle', '');
wp_enqueue_script('wpjobportal-resume-inline-handle');

// Prepare localized strings and safe variables for JS injection
$locale_js = esc_js(get_user_locale());
$ajax_url_js = esc_url(admin_url('admin-ajax.php'));

$str_ai_addition     = esc_html__('✨ AI ADDITION', 'wp-job-portal');
$str_analyzing       = esc_html__('Analyzing Profile...', 'wp-job-portal');
$str_yes             = esc_html__('Yes', 'wp-job-portal');
$str_generated       = esc_html__('Skills Generated!', 'wp-job-portal');
$str_failed          = esc_html__('Generation Failed: ', 'wp-job-portal');
$str_unknown         = esc_html__('Unknown error.', 'wp-job-portal');
$str_server_err      = esc_html__('Server communication error. Please try again.', 'wp-job-portal');
$str_generate_btn    = esc_html__('Generate Skills', 'wp-job-portal');

$wpjobportal_inline_js_script = '
var wpjp_current_lang = "' . $locale_js . '";

jQuery(document).ready(function($) {
    // 1. Utility for handling raw Markdown to Plain Text
    window.ZywrapFormatter = {
        cleanRawMarkdown: function(text) {
            if (!text) return "";
            return text.replace(/([^\s\n])\s+(#{1,6}\s+[A-Z])/g, "$1\n\n$2").replace(/([^\s\n])\s+(-\s+[A-Z0-9])/g, "$1\n$2");
        },
        toPlainText: function(markdownText) {
            var preppedText = this.cleanRawMarkdown(markdownText);
            // Remove markdown syntax for raw textareas
            return preppedText.replace(/\*\*/g, "").replace(/###/g, "").replace(/<li>/g, "• ").replace(/<[^>]*>?/gm, "");
        },
        smartInsert: function(editorId, contentPlain) {
            var el = jQuery("#" + editorId);
            var currentContent = el.val().trim();
            var spacer = currentContent ? "\n\n--- ' . $str_ai_addition . ' ---\n\n" : "";
            el.val(currentContent + spacer + contentPlain);
            el.scrollTop(el[0].scrollHeight);
        }
    };

    // 2. UI Toggles
    $(".wjportal-ai-header-menu").on("click", function(e) {
        e.preventDefault();
        $(".wjportal-ai-panel").toggle();
        $(this).toggleClass("is-active");
    });

    $(document).on("click", ".wjportal-ai-advanced-summary", function(e) {
        e.preventDefault();
        const $accordion = $(this).closest(".wjportal-ai-advanced-accordion");
        $accordion.toggleClass("is-open");
        $accordion.find(".wjportal-ai-accordion-content").slideToggle(250);
    });

    // SEO Field Toggle logic
    const $seoInputWrap = $("#seo-input-wrap");
    $("#ai-seo").on("change", function() {
        if ($(this).is(":checked")) {
            $seoInputWrap.removeClass("wjportal-ai-hidden").hide().slideDown(200);
        } else {
            $seoInputWrap.slideUp(200, function() { $(this).addClass("wjportal-ai-hidden"); });
        }
    });

    const $improveWrapper = $("#ai-improve-wrapper");
    const $improveCheckbox = $("#ai-improve");

    function checkEditorContent() {
        let content = $("#skills").val().trim();
        if (content.length > 0) {
            if ($improveWrapper.is(":hidden")) $improveWrapper.removeClass("wjportal-ai-hidden").hide().slideDown(250);
        } else {
            if ($improveWrapper.is(":visible")) {
                $improveWrapper.slideUp(250, function() { $(this).addClass("wjportal-ai-hidden"); $improveCheckbox.prop("checked", false); });
            }
        }
    }
    setTimeout(checkEditorContent, 800);
    $("#skills").on("input propertychange", checkEditorContent);

    // 3. Execution Logic
    $("#wjportal-generate-btn").on("click", function(e) {
        e.preventDefault();
        const $btn = $(this);
        const $icon = $("#wjportal-btn-icon");
        const $text = $("#wjportal-btn-text");

        $btn.prop("disabled", true).addClass("wjportal-ai-loading-ring");
        $icon.removeClass().addClass("dashicons dashicons-update wjportal-ai-text-white").css("animation", "spin 1s linear infinite");
        $text.text("' . $str_analyzing . '");

        $("#skills").css("opacity", "0.6");

        // Harvest Resume Data automatically if toggle is checked
        const resumeData = [];

        const useFormData = $("#ai-form-data").is(":checked");
        if (useFormData) {
            const fieldsToSkip = [
                "termsconditions", "_wpnonce", "action", "uid", "id",
                "draft", "created", "wpjobportalpageid", "form_request", "upakid"
            ];

            $("#resumeform").find("input:not([type=\"submit\"], [type=\"button\"]), select, textarea")
                .not(".wjportal-ai-app-wrapper input, .wjportal-ai-app-wrapper select, .wjportal-ai-app-wrapper textarea")
                .each(function() {
                    const $el = $(this);
                    const nameAttr = $el.attr("name");
                    const idAttr = $el.attr("id");

                    // Skip disabled, hidden, or ignored fields
                    if ($el.is(":disabled") || $el.prop("disabled") || $el.attr("type") === "hidden") {
                        return;
                    }
                    if (!nameAttr || fieldsToSkip.includes(nameAttr)) {
                        return;
                    }

                    let val = "";
                    if ($el.is("select")) {
                        val = $el.find("option:selected").text();
                    } else if ($el.is(":checkbox") || $el.is(":radio")) {
                        if (!$el.is(":checked")) return;
                        val = $el.next("label").text() || $el.parent().text().trim() || "' . $str_yes . '";
                    } else {
                        val = $el.val();
                    }

                    // Clean useless default values
                    const lowerVal = val.toLowerCase().trim();
                    if (lowerVal.startsWith("select") || lowerVal === "uncategorized") {
                        return;
                    }
                    val = val.trim();

                    if (val) {
                        let labelText = "";

                        // 1. Try to find the label from the form row title
                        labelText = $el.closest(".wjportal-form-row").find(".wjportal-form-title").first().text();

                        // 2. Fallback to the <label for="id">
                        if (!labelText && idAttr) {
                            labelText = $("label[for=\"" + idAttr + "\"]").text();
                        }

                        // 3. Fallback to wrapping label text
                        if (!labelText) {
                            labelText = $el.closest("label").clone().children().remove().end().text();
                        }

                        // 4. Final fallback: format the name attribute
                        if (!labelText) {
                            labelText = nameAttr.replace(/[_-]/g, " ").replace(/\b\w/g, l => l.toUpperCase());
                        }

                        // Clean up asterisks and whitespace
                        labelText = labelText.replace(/\*/g, "").trim();

                        // Add to array if unique
                        if (labelText && !resumeData.find(item => item.label === labelText)) {
                            resumeData.push({ label: labelText, value: val });
                        }
                    }
                });
        }

        const aiData = {
            improving: $improveCheckbox.is(":checked"),
            length: $("input[name=\"ai-length\"]:checked").parent().text().trim(),
            tone: $("input[name=\"ai-tone\"]:checked").parent().text().trim(),
            formatStructure: $("#ai-format-structure").find("option:selected").text().trim(),
            language: wpjp_current_lang,
            customPrompt: $(".wjportal-ai-text-area-input").val() || "",
            seo: $("#ai-seo").is(":checked") ? $("#seo-input-wrap input").val() : ""
        };

        let highlights = [];
        $(".wjportal-ai-pill-checkbox input:checked").each(function() {
            highlights.push($(this).siblings(".wjportal-ai-pill-box").text());
        });

        let existingContent = "";
        if (aiData.improving) {
            existingContent = $("#skills").val().trim();
        }

        var wjp_ajaxurl = typeof ajaxurl !== "undefined" ? ajaxurl : "' . $ajax_url_js . '";

        var requestData = {
            action: "wpjobportal_ajax",
            wpjobportalme: "zywrap",
            task: "executeResumeCopilot",
            _wpnonce: $("input[name=\"_wpnonce\"]").val(),
            resumeData: JSON.stringify(resumeData),
            aiData: JSON.stringify(aiData),
            highlights: JSON.stringify(highlights),
            existing_content: existingContent
        };

        $.post(wjp_ajaxurl, requestData).done(function(response) {
            if(response.success) {
                $text.text("' . $str_generated . '");
                let aiOutput = response.data.output;

                // For resume skills, we convert to raw text since we aren\'t using TinyMCE here
                let plainOutput = ZywrapFormatter.toPlainText(aiOutput);
                ZywrapFormatter.smartInsert("skills", plainOutput);

                checkEditorContent();
            } else {
                alert("' . $str_failed . '" + (response.data.message || "' . $str_unknown . '"));
            }
        }).fail(function() {
            alert("' . $str_server_err . '");
        }).always(function() {
            $btn.prop("disabled", false).removeClass("wjportal-ai-loading-ring");
            $icon.removeClass().addClass("dashicons dashicons-yes wjportal-ai-text-white").css("animation", "");
            setTimeout(function() {
                $icon.removeClass().addClass("dashicons dashicons-lightning wjportal-ai-text-indigo-200").css("animation", "");
                $text.text("' . $str_generate_btn . '");
            }, 2500);
            $("#skills").css("opacity", "1");
        });
    });
});
';

wp_add_inline_script('wpjobportal-resume-inline-handle', $wpjobportal_inline_js_script);

// Enqueue job portal variables CSS
wp_enqueue_style(
    'wpjobportal-variables',
    WPJOBPORTAL_PLUGIN_URL . 'includes/css/job_portal_variables.css'
);
?>

<style>
    /* ==========================================================================
       Zywrap AI Copilot - Soft Glass Premium UI (Light Theme)
       ========================================================================== */
   /* --- CRITICAL FIX: Override global theme/plugin styles that break AI UI --- */
    .wjportal-ai-app-wrapper input[type="checkbox"],
    .wjportal-ai-app-wrapper input[type="radio"] {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
        opacity: 0 !important;
        position: absolute !important;
        margin: 0 !important;
    }
    .wjportal-ai-app-wrapper textarea,
    .wjportal-ai-app-wrapper select,
    .wjportal-ai-app-wrapper input[type="text"] {
        height: auto !important;
        min-height: unset !important;
        line-height: 1.5 !important;
    }
    .wjportal-ai-app-wrapper .wjportal-ai-text-area-input {
        min-height: 80px !important;
    }

    /* --- LAYOUT & PANELS --- */
    .wjportal-ai-app-wrapper {
        background: var(--wpjp-background-color, #f8fafc);
        padding: 1.5rem;
        border-radius: 1rem;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        width: 100%;
        color: var(--wpjp-body-font-color);
    }
    @media (min-width: 1024px) {
        .wjportal-ai-app-wrapper { flex-direction: row; }
    }

    .wjportal-ai-editor-panel {
        background: var(--wpjp-card-background, white);
        border: none;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.01);
        border-radius: 1rem;
        flex: 1;
        display: flex;
        flex-direction: column;
        padding: 1.5rem;
    }

    .wjportal-ai-panel {
        background: var(--wpjp-card-background, rgba(255, 255, 255, 0.7));
        backdrop-filter: blur(12px);
        border: 1px solid var(--wpjp-border-color, rgba(255,255,255,0.7));
        box-shadow: 0 4px 20px -2px rgba(0,0,0,0.05);
        border-radius: 1rem;
        width: 100%;
        display: flex;
        flex-direction: column;
        flex-shrink: 0;
        max-height: 750px;
        overflow: hidden;
    }
    @media (min-width: 1024px) {
        .wjportal-ai-panel { width: 440px; }
    }

    /* --- EDITOR HEADER & TOOLBAR --- */
    .wjportal-ai-editor-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    .wjportal-ai-editor-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--wpjp-secondary-color, #1e293b);
        margin: 0;
    }
    .wjportal-ai-header-menu {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        color: var(--wpjp-primary-color, #3baeda);
        background: var(--wpjp-card-background, #ffffff);
        border: 1px solid var(--wpjp-primary-color, #3baeda);
        transition: all 0.2s ease;
        padding: 0.4rem 1rem;
        border-radius: 2rem;
        cursor: pointer;
        font-size: 0.8125rem;
        font-weight: 600;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    .wjportal-ai-header-menu:hover,
    .wjportal-ai-header-menu.is-active {
        color: #ffffff;
        background-color: var(--wpjp-primary-color, #3baeda);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    }
    .wjportal-ai-header-menu .dashicons {
        font-size: 16px;
        width: 16px;
        height: 16px;
        line-height: 1;
    }

    /* --- MAIN EDITOR INPUT --- */
    .wjportal-ai-editor-input-container {
        display: flex;
        flex-direction: column;
        overflow: hidden;
        flex: 1;
    }
    .wjportal-ai-main-editor::-webkit-scrollbar,
    .wjportal-ai-body::-webkit-scrollbar { width: 6px; }
    .wjportal-ai-main-editor::-webkit-scrollbar-track,
    .wjportal-ai-body::-webkit-scrollbar-track { background: transparent; }
    .wjportal-ai-main-editor::-webkit-scrollbar-thumb,
    .wjportal-ai-body::-webkit-scrollbar-thumb { background: var(--wpjp-border-color, #cbd5e1); border-radius: 4px; }
    .wjportal-ai-main-editor {
        width: 100%;
        border: none;
        resize: vertical;
        min-height: 350px;
        outline: none;
        padding: 0.5rem;
        background: transparent;
        font-family: inherit;
        font-size: inherit;
        color: var(--wpjp-body-font-color, #334155);
        line-height: 1.625;
    }

    /* --- AI SIDEBAR COMPONENTS --- */
    .wjportal-ai-header-top { padding: 1.25rem; border-bottom: 1px solid var(--wpjp-border-color); display: flex; align-items: center; justify-content: space-between; background-color: var(--wpjp-card-background); border-top-left-radius: 1rem; border-top-right-radius: 1rem; }
    .wjportal-ai-header-title-wrapper { display: flex; align-items: center; gap: 0.75rem; }
    .wjportal-ai-header-icon { width: 2rem; height: 2rem; border-radius: 0.5rem; background-color: var(--wpjp-primary-color); opacity: 0.9; color: white; display: flex; align-items: center; justify-content: center; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); }
    .wjportal-ai-header-icon .dashicons { font-size: 16px; width: 16px; height: 16px; }
    .wjportal-ai-header-title { font-weight: 600; color: var(--wpjp-secondary-color, #1e293b); line-height: 1.25; font-size: 1rem; margin: 0; }
    .wjportal-ai-header-subtitle { font-size: 11px; font-weight: 500; color: var(--wpjp-primary-color, #4f46e5); text-transform: uppercase; letter-spacing: 0.025em; margin: 0; }

    .wjportal-ai-body { padding: 1.5rem; overflow-y: auto; flex: 1; min-height: 0; display: flex; flex-direction: column; gap: 1.5rem; background-color: var(--wpjp-card-background); }
    .wjportal-ai-footer { padding: 1.5rem; border-top: 1px solid var(--wpjp-border-color); background-color: var(--wpjp-card-background); border-bottom-left-radius: 1rem; border-bottom-right-radius: 1rem; }
    .wjportal-ai-footer-note { display: flex; align-items: center; justify-content: center; gap: 0.375rem; margin-top: 0.75rem; font-size: 0.75rem; color: var(--wpjp-body-font-color); }
    .wjportal-ai-shield-icon { color: var(--wpjp-body-font-color, #cbd5e1); font-size: 14px; width: 14px; height: 14px; }

    /* --- FORMS & INPUTS --- */
    .wjportal-ai-setting-group { display: flex; flex-direction: column; gap: 1rem; }
    .wjportal-ai-setting-group-md { display: flex; flex-direction: column; gap: 0.75rem; }
    .wjportal-ai-setting-group-sm { display: flex; flex-direction: column; gap: 0.5rem; }
    .wjportal-ai-setting-label { font-size: 0.75rem; font-weight: 600; color: var(--wpjp-body-font-color, #475569); text-transform: uppercase; letter-spacing: 0.05em; display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; }

    .wjportal-ai-input-soft { background: var(--wpjp-card-background, white); border: 1px solid var(--wpjp-border-color, #e2e8f0); border-radius: 0.75rem; box-shadow: inset 0 2px 4px 0 rgba(0,0,0,0.02); width: 100%; }
    .wjportal-ai-select-soft { background: var(--wpjp-card-background, white); border: 1px solid var(--wpjp-border-color, #e2e8f0); border-radius: 0.75rem; padding: 0.625rem 1rem; width: 100%; font-size: 0.875rem; color: var(--wpjp-body-font-color, #334155); appearance: none; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-position: right 0.5rem center; background-repeat: no-repeat; background-size: 1.5em 1.5em; }
    .wjportal-ai-input-soft:focus-within, .wjportal-ai-select-soft:focus { border-color: var(--wpjp-primary-color, #6366f1); outline: none; }
    .wjportal-ai-text-input { width: 100%; padding: 0.625rem; background: transparent; border: none; font-size: 0.875rem; color: var(--wpjp-body-font-color, #334155); outline: none; }
    .wjportal-ai-text-area-input { width: 100%; padding: 0.75rem; background: transparent; border: none; resize: none; font-size: 0.875rem; color: var(--wpjp-body-font-color, #334155); outline: none; }

    /* --- SEGMENTED CONTROLS & PILLS --- */
    .wjportal-ai-segmented-control { background: var(--wpjp-background-color, #e2e8f0); border-radius: 0.75rem; padding: 0.25rem; display: flex; }
    .wjportal-ai-segmented-item { flex: 1; text-align: center; cursor: pointer; padding: 0.5rem 0; font-size: 0.8125rem; color: var(--wpjp-body-font-color, #475569); display: flex; align-items: center; justify-content: center; }
    .wjportal-ai-segmented-item input { display: none; }
    .wjportal-ai-segmented-item:has(input:checked) { background: var(--wpjp-card-background, white); box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-radius: 0.5rem; color: var(--wpjp-primary-color, #4f46e5); font-weight: 600; }

    .wjportal-ai-pill-group { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.25rem; }
    .wjportal-ai-pill-checkbox { cursor: pointer; }
    .wjportal-ai-pill-checkbox input { display: none; }
    .wjportal-ai-pill-box { display: inline-block; padding: 0.375rem 0.875rem; border: 1px solid var(--wpjp-border-color, #e2e8f0); border-radius: 9999px; font-size: 0.8125rem; color: var(--wpjp-body-font-color, #64748b); background: var(--wpjp-card-background, white); transition: all 0.2s; }
    .wjportal-ai-pill-checkbox:hover .wjportal-ai-pill-box { border-color: var(--wpjp-primary-color, #cbd5e1); background: var(--wpjp-background-color, #f8fafc); }
    .wjportal-ai-pill-checkbox:has(input:checked) .wjportal-ai-pill-box { background-color: var(--wpjp-card-background); border-color: var(--wpjp-primary-color); color: var(--wpjp-primary-color); font-weight: 500; box-shadow: inset 0 0 0 1px var(--wpjp-primary-color); }

    /* --- FEATURE CARDS & TOGGLES --- */
    .wjportal-ai-feature-card { background: var(--wpjp-card-background, #ffffff); border: 1px solid var(--wpjp-border-color, #e2e8f0); border-radius: 0.875rem; padding: 1rem; display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; box-shadow: 0 2px 4px rgba(0,0,0,0.02); transition: all 0.2s ease; cursor: pointer; }
    .wjportal-ai-feature-card:hover { border-color: var(--wpjp-primary-color, #cbd5e1); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .wjportal-ai-feature-card:has(input:checked) { border-color: var(--wpjp-primary-color, #a5b4fc); background: var(--wpjp-card-background, #fcfdff); }

    .wjportal-ai-feature-card-text { display: flex; flex-direction: column; gap: 0.25rem; flex: 1; margin-top: -0.125rem; }
    .wjportal-ai-feature-card-header { display: flex; align-items: center; gap: 0.5rem; }
    .wjportal-ai-feature-card-icon { color: var(--wpjp-body-font-color, #94a3b8); font-size: 16px; width: 16px; height: 16px; transition: color 0.2s; }
    .wjportal-ai-feature-card:has(input:checked) .wjportal-ai-feature-card-icon { color: var(--wpjp-primary-color, #6366f1); }
    .wjportal-ai-feature-card-title { font-size: 0.875rem; font-weight: 600; color: var(--wpjp-secondary-color, #1e293b); line-height: 1.2; }
    .wjportal-ai-feature-card-desc { font-size: 0.75rem; color: var(--wpjp-body-font-color, #64748b); line-height: 1.4; }

    .wjportal-ai-switch-container { position: relative; display: inline-block; width: 2.75rem; height: 1.5rem; flex-shrink: 0; pointer-events: none; margin-top: 0.125rem; }
    .wjportal-ai-switch-container input { opacity: 0; width: 0; height: 0; position: absolute; }
    .wjportal-ai-switch-track { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: var(--wpjp-border-color, #cbd5e1); border-radius: 9999px; transition: background-color 0.3s ease; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1); }
    .wjportal-ai-switch-thumb { position: absolute; height: 1.125rem; width: 1.125rem; left: 0.1875rem; bottom: 0.1875rem; background-color: white; border-radius: 50%; transition: transform 0.3s cubic-bezier(0.4, 0.0, 0.2, 1); box-shadow: 0 2px 4px rgba(0,0,0,0.2); display: flex; align-items: center; justify-content: center; }
    .wjportal-ai-switch-icon { font-size: 10px !important; width: 10px !important; height: 10px !important; color: var(--wpjp-primary-color, #6366f1); opacity: 0; transition: opacity 0.3s; }
    .wjportal-ai-feature-card:has(input:checked) .wjportal-ai-switch-track { background-color: var(--wpjp-primary-color, #6366f1); }
    .wjportal-ai-feature-card:has(input:checked) .wjportal-ai-switch-thumb { transform: translateX(1.25rem); }
    .wjportal-ai-feature-card:has(input:checked) .wjportal-ai-switch-icon { opacity: 1; }

    /* --- BUTTONS & STATES --- */
    .wjportal-ai-btn-primary { background: var(--wpjp-primary-color); color: white; border-radius: 0.75rem; box-shadow: 0 4px 12px -2px rgba(0,0,0,0.2); border: none; width: 100%; padding: 0.875rem 0; display: flex; align-items: center; justify-content: center; gap: 0.5rem; font-size: 0.875rem; font-weight: 500; transition: all 0.2s; cursor: pointer; }
    .wjportal-ai-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 16px -2px rgba(0,0,0,0.3); background: var(--wpjp-secondary-color); }
    .wjportal-ai-btn-primary:disabled { opacity: 0.7; cursor: not-allowed; transform: none; box-shadow: none; }

    @keyframes wjportal-ai-pulse-ring { 0% { transform: scale(0.8); opacity: 0.5; } 100% { transform: scale(1.3); opacity: 0; } }
    .wjportal-ai-loading-ring { position: relative; z-index: 1; }
    .wjportal-ai-loading-ring::before { content: ''; position: absolute; left: 0; top: 0; width: 100%; height: 100%; background: inherit; border-radius: inherit; animation: wjportal-ai-pulse-ring 1.5s cubic-bezier(0.215, 0.61, 0.355, 1) infinite; z-index: -1; }

    /* --- UTILITIES & ACCORDION --- */
    .wjportal-ai-divider { border-color: var(--wpjp-border-color, #f1f5f9); border-top: 1px solid var(--wpjp-border-color, #f1f5f9); margin: 0; }
    .wjportal-ai-seo-wrapper { display: flex; flex-direction: column; gap: 0.5rem; }
    .wjportal-ai-mt-1 { margin-top: 0.25rem; }
    .wjportal-ai-bg-white { background-color: var(--wpjp-card-background, white); }
    .wjportal-ai-hidden { display: none !important; }
    .wjportal-ai-text-white { color: #ffffff; }
    .wjportal-ai-text-indigo-200 { color: var(--wpjp-background-color, #c7d2fe); }

    .wjportal-ai-advanced-accordion { background: var(--wpjp-background-color, #f8fafc); border: 1px solid var(--wpjp-border-color, #e2e8f0); border-radius: 0.75rem; transition: all 0.3s ease; }
    .wjportal-ai-advanced-accordion.is-open { background: var(--wpjp-card-background, white); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border-color: var(--wpjp-border-color, #cbd5e1); }
    .wjportal-ai-advanced-summary { padding: 1rem; cursor: pointer; display: flex; justify-content: space-between; align-items: center; font-weight: 600; font-size: 0.875rem; color: var(--wpjp-secondary-color, #334155); user-select: none; width: 100%; box-sizing: border-box; }
    .wjportal-ai-acc-trigger-left { display: flex; align-items: center; gap: 0.5rem; }
    .wjportal-ai-acc-trigger-icon { color: var(--wpjp-body-font-color, #94a3b8); font-size: 16px; width: 16px; height: 16px; }
    .wjportal-ai-accordion-icon { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); color: var(--wpjp-body-font-color, #94a3b8); font-size: 16px; width: 16px; height: 16px; }
    .wjportal-ai-advanced-accordion.is-open .wjportal-ai-accordion-icon { transform: rotate(180deg); color: var(--wpjp-primary-color, #6366f1); }
    .wjportal-ai-accordion-content { display: none; }
    .wjportal-ai-accordion-content-inner { display: flex; flex-direction: column; gap: 1.5rem; padding: 1rem 1rem 1.25rem 1rem; border-top: 1px solid var(--wpjp-border-color, #f1f5f9); }
</style>

<?php
/**
 * PHP Return Block
 * All HTML attributes MUST use double quotes.
 */
return '
<div class="wjportal-ai-app-wrapper">

    <div class="wjportal-ai-editor-panel">
        <div class="wjportal-ai-editor-header">
            <div class="wjportal-ai-editor-title"></div>
            <button type="button" class="wjportal-ai-header-menu" title="' . esc_attr__('Toggle AI Copilot', 'wp-job-portal') . '">
                <span class="dashicons dashicons-admin-generic"></span>
                <span class="wjportal-ai-header-menu-text">' . esc_html__('AI Settings', 'wp-job-portal') . '</span>
            </button>
        </div>
        <div class="wjportal-ai-input-soft wjportal-ai-editor-input-container">
            ' . $wpjobportal_desc_editor . '
        </div>
    </div>

    <div class="wjportal-ai-panel">

        <div class="wjportal-ai-header-top">
            <div class="wjportal-ai-header-title-wrapper">
                <div class="wjportal-ai-header-icon">
                    <span class="dashicons dashicons-media-text"></span>
                </div>
                <div>
                    <h3 class="wjportal-ai-header-title">' . esc_html__('Zywrap Copilot', 'wp-job-portal') . '</h3>
                    <p class="wjportal-ai-header-subtitle">' . esc_html__('Resume Skills AI', 'wp-job-portal') . '</p>
                </div>
            </div>
        </div>

        <div class="wjportal-ai-body">

            <div class="wjportal-ai-setting-group wjportal-ai-hidden" id="ai-improve-wrapper">
                <label class="wjportal-ai-feature-card" for="ai-improve">
                    <div class="wjportal-ai-feature-card-text">
                        <div class="wjportal-ai-feature-card-header">
                            <span class="dashicons dashicons-edit wjportal-ai-feature-card-icon"></span>
                            <span class="wjportal-ai-feature-card-title">' . esc_html__('Improve existing content', 'wp-job-portal') . '</span>
                        </div>
                        <span class="wjportal-ai-feature-card-desc">' . esc_html__('Uses text currently in the editor as base context.', 'wp-job-portal') . '</span>
                    </div>
                    <div class="wjportal-ai-switch-container">
                        <input type="checkbox" id="ai-improve" checked>
                        <div class="wjportal-ai-switch-track">
                            <div class="wjportal-ai-switch-thumb"><span class="dashicons dashicons-yes wjportal-ai-switch-icon"></span></div>
                        </div>
                    </div>
                </label>
            </div>

            <div class="wjportal-ai-setting-group-sm">
                <label class="wjportal-ai-setting-label">' . esc_html__('Output Length', 'wp-job-portal') . '</label>
                <div class="wjportal-ai-segmented-control">
                    <label class="wjportal-ai-segmented-item">
                        <input type="radio" name="ai-length" value="concise"> ' . esc_html__('Concise', 'wp-job-portal') . '
                    </label>
                    <label class="wjportal-ai-segmented-item">
                        <input type="radio" name="ai-length" value="standard" checked> ' . esc_html__('Standard', 'wp-job-portal') . '
                    </label>
                    <label class="wjportal-ai-segmented-item">
                        <input type="radio" name="ai-length" value="comprehensive"> ' . esc_html__('Detailed', 'wp-job-portal') . '
                    </label>
                </div>
            </div>

            <div class="wjportal-ai-setting-group-sm">
                <label class="wjportal-ai-setting-label">' . esc_html__('Focus Areas', 'wp-job-portal') . '</label>
                <div class="wjportal-ai-pill-group">
                    <label class="wjportal-ai-pill-checkbox"><input type="checkbox" checked><span class="wjportal-ai-pill-box">' . esc_html__('Core Competencies', 'wp-job-portal') . '</span></label>
                    <label class="wjportal-ai-pill-checkbox"><input type="checkbox" checked><span class="wjportal-ai-pill-box">' . esc_html__('Technical Skills', 'wp-job-portal') . '</span></label>
                    <label class="wjportal-ai-pill-checkbox"><input type="checkbox" checked><span class="wjportal-ai-pill-box">' . esc_html__('Soft Skills & Leadership', 'wp-job-portal') . '</span></label>
                    <label class="wjportal-ai-pill-checkbox"><input type="checkbox"><span class="wjportal-ai-pill-box">' . esc_html__('Tools & Technologies', 'wp-job-portal') . '</span></label>
                    <label class="wjportal-ai-pill-checkbox"><input type="checkbox"><span class="wjportal-ai-pill-box">' . esc_html__('Frameworks & Methodologies', 'wp-job-portal') . '</span></label>
                    <label class="wjportal-ai-pill-checkbox"><input type="checkbox"><span class="wjportal-ai-pill-box">' . esc_html__('Industry Certifications', 'wp-job-portal') . '</span></label>
                    <label class="wjportal-ai-pill-checkbox"><input type="checkbox"><span class="wjportal-ai-pill-box">' . esc_html__('Languages (Spoken/Written)', 'wp-job-portal') . '</span></label>
                </div>
            </div>

            <div class="wjportal-ai-advanced-accordion wjportal-ai-mt-1">
                <div class="wjportal-ai-advanced-summary">
                    <div class="wjportal-ai-acc-trigger-left">
                        <span class="dashicons dashicons-admin-settings wjportal-ai-acc-trigger-icon"></span>
                        ' . esc_html__('Advanced Settings', 'wp-job-portal') . '
                    </div>
                    <span class="dashicons dashicons-arrow-down-alt2 wjportal-ai-accordion-icon"></span>
                </div>

                <div class="wjportal-ai-accordion-content">
                    <div class="wjportal-ai-accordion-content-inner">

                        <div class="wjportal-ai-setting-group-sm">
                            <label class="wjportal-ai-setting-label">' . esc_html__('Voice & Tone', 'wp-job-portal') . '</label>
                            <div class="wjportal-ai-segmented-control">
                                <label class="wjportal-ai-segmented-item">
                                    <input type="radio" name="ai-tone" value="professional" checked> ' . esc_html__('Corporate', 'wp-job-portal') . '
                                </label>
                                <label class="wjportal-ai-segmented-item">
                                    <input type="radio" name="ai-tone" value="creative"> ' . esc_html__('Creative', 'wp-job-portal') . '
                                </label>
                                <label class="wjportal-ai-segmented-item">
                                    <input type="radio" name="ai-tone" value="technical"> ' . esc_html__('Highly Technical', 'wp-job-portal') . '
                                </label>
                            </div>
                        </div>

                        <div class="wjportal-ai-setting-group-md">
                            <label class="wjportal-ai-setting-label">' . esc_html__('Auto-Attached Context', 'wp-job-portal') . '</label>
                            <div class="wjportal-ai-context-cards-wrapper">
                                <label class="wjportal-ai-feature-card" for="ai-form-data">
                                    <div class="wjportal-ai-feature-card-text">
                                        <div class="wjportal-ai-feature-card-header">
                                            <span class="dashicons dashicons-clipboard wjportal-ai-feature-card-icon"></span>
                                            <span class="wjportal-ai-feature-card-title">' . esc_html__('Resume Form Data', 'wp-job-portal') . '</span>
                                        </div>
                                        <span class="wjportal-ai-feature-card-desc">' . esc_html__('Uses Title and Category fields to inform skill suggestions.', 'wp-job-portal') . '</span>
                                    </div>
                                    <div class="wjportal-ai-switch-container">
                                        <input type="checkbox" id="ai-form-data" checked>
                                        <div class="wjportal-ai-switch-track">
                                            <div class="wjportal-ai-switch-thumb"><span class="dashicons dashicons-yes wjportal-ai-switch-icon"></span></div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <hr class="wjportal-ai-divider">

                        <div class="wjportal-ai-setting-group-sm">
                            <label class="wjportal-ai-setting-label">' . esc_html__('Format Structure', 'wp-job-portal') . '</label>
                            <select id="ai-format-structure" class="wjportal-ai-select-soft wjportal-ai-bg-white">
                                <option value="comma">' . esc_html__('Comma Separated (Compact)', 'wp-job-portal') . '</option>
                                <option value="bullets">' . esc_html__('Bulleted List (Standard)', 'wp-job-portal') . '</option>
                                <option value="categorized">' . esc_html__('Categorized List (e.g., Frontend:, Backend:)', 'wp-job-portal') . '</option>
                                <option value="matrix">' . esc_html__('Skill Matrix (Skill - Proficiency)', 'wp-job-portal') . '</option>
                                <option value="paragraph">' . esc_html__('Narrative Summary (Paragraph)', 'wp-job-portal') . '</option>
                            </select>
                        </div>

                        <div class="wjportal-ai-setting-group-sm">
                            <label class="wjportal-ai-setting-label">' . esc_html__('Specific Free-form Instructions', 'wp-job-portal') . '</label>
                            <div class="wjportal-ai-input-soft wjportal-ai-bg-white">
                                <textarea rows="3" class="wjportal-ai-text-area-input" placeholder="' . esc_attr__('e.g., "Make sure to emphasize my leadership experience..."', 'wp-job-portal') . '"></textarea>
                            </div>
                        </div>

                        <div class="wjportal-ai-seo-wrapper">
                            <label class="wjportal-ai-feature-card" for="ai-seo">
                                <div class="wjportal-ai-feature-card-text">
                                    <div class="wjportal-ai-feature-card-header">
                                        <span class="dashicons dashicons-search wjportal-ai-feature-card-icon"></span>
                                        <span class="wjportal-ai-feature-card-title">' . esc_html__('Optimize for ATS/SEO', 'wp-job-portal') . '</span>
                                    </div>
                                    <span class="wjportal-ai-feature-card-desc">' . esc_html__('Ensure specific keywords are included to pass Applicant Tracking Systems.', 'wp-job-portal') . '</span>
                                </div>
                                <div class="wjportal-ai-switch-container">
                                    <input type="checkbox" id="ai-seo">
                                    <div class="wjportal-ai-switch-track">
                                        <div class="wjportal-ai-switch-thumb"><span class="dashicons dashicons-yes wjportal-ai-switch-icon"></span></div>
                                    </div>
                                </div>
                            </label>
                            <div id="seo-input-wrap" class="wjportal-ai-input-soft wjportal-ai-bg-white wjportal-ai-hidden">
                                <input type="text" class="wjportal-ai-text-input" placeholder="' . esc_attr__('Target Keywords (comma separated)', 'wp-job-portal') . '">
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        <div class="wjportal-ai-footer">
            <button id="wjportal-generate-btn" type="button" class="wjportal-ai-btn-primary">
                <span class="dashicons dashicons-lightning wjportal-ai-text-indigo-200" id="wjportal-btn-icon"></span>
                <span id="wjportal-btn-text">' . esc_html__('Generate Skills', 'wp-job-portal') . '</span>
            </button>
        </div>

    </div>
</div>
';
?>