<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
// Prevent undefined variable warning for the editor description
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
wp_register_script('wpjobportal-inline-handle', '');
wp_enqueue_script('wpjobportal-inline-handle');

// Prepare localized strings and safe variables for JS injection
$locale_js = esc_js(get_user_locale());
$ajax_url_js = esc_url(admin_url('admin-ajax.php'));

$str_ai_addition     = esc_html__('✨ AI ADDITION', 'wp-job-portal');
$str_analyzing       = esc_html__('Analyzing parameters...', 'wp-job-portal');
$str_yes             = esc_html__('Yes', 'wp-job-portal');
$str_compensation    = esc_html__('Compensation', 'wp-job-portal');
$str_generated       = esc_html__('Content Generated!', 'wp-job-portal');
$str_no_entities     = esc_html__('No specific entities found.', 'wp-job-portal');
$str_instructions    = esc_html__('✨ Copilot Instructions Applied:', 'wp-job-portal');
$str_tone            = esc_html__('Tone:', 'wp-job-portal');
$str_length          = esc_html__('Length:', 'wp-job-portal');
$str_format          = esc_html__('Format:', 'wp-job-portal');
$str_seo_target      = esc_html__('SEO Target:', 'wp-job-portal');
$str_custom          = esc_html__('Custom:', 'wp-job-portal');
$str_failed          = esc_html__('Generation Failed: ', 'wp-job-portal');
$str_unknown         = esc_html__('Unknown error.', 'wp-job-portal');
$str_server_err      = esc_html__('Server communication error. Please try again.', 'wp-job-portal');
$str_generate_btn    = esc_html__('Generate Description', 'wp-job-portal');

$wpjobportal_inline_js_script = '
var wpjp_current_lang = "' . $locale_js . '";

jQuery(document).ready(function($) {
    window.ZywrapFormatter = {
        cleanRawMarkdown: function(text) {
            if (!text) return "";
            return text
                .replace(/([^\s\n])\s+(#{1,6}\s+[A-Z])/g, "$1\n\n$2")
                .replace(/([^\s\n])\s+(-\s+[A-Z0-9])/g, "$1\n$2");
        },
        toHTML: function(markdownText) {
            var preppedText = this.cleanRawMarkdown(markdownText);
            if (typeof marked !== "undefined") {
                return marked.parse(preppedText, { breaks: true, gfm: true });
            }
            return preppedText;
        },
        toPlainText: function(markdownText) {
            var html = this.toHTML(markdownText);
            html = html.replace(/<br\s*[\/ ]?>/gi, "\n");
            html = html.replace(/<\/p>/gi, "\n\n");
            html = html.replace(/<h[1-6]>(.*?)<\/h[1-6]>/gi, "\n=== $1 ===\n\n");
            html = html.replace(/<li>(.*?)<\/li>/gi, " • $1\n");
            html = html.replace(/<\/ul>/gi, "\n");
            html = html.replace(/<\/ol>/gi, "\n");
            var temp = document.createElement("div");
            temp.innerHTML = html;
            var plainText = temp.innerText || temp.textContent;
            return plainText.trim().replace(/\n{3,}/g, "\n\n");
        },
        formatJSON: function(jsonObj) {
            if (!jsonObj || typeof jsonObj !== "object") return "";
            var html = "<div class=\"zywrap-json-container\" style=\"background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:1rem; margin-bottom:1rem;\">";
            var hasData = false;
            for (var key in jsonObj) {
                var val = jsonObj[key];
                if (val === null || val === "" || (Array.isArray(val) && val.length === 0)) continue;
                if (typeof val === "object" && !Array.isArray(val)) {
                    var hasSubData = false;
                    for (var sk in val) { if (val[sk] !== null && val[sk] !== "" && (!Array.isArray(val[sk]) || val[sk].length > 0)) hasSubData = true; }
                    if (!hasSubData) continue;
                }
                hasData = true;
                var cleanKey = key.replace(/_/g, " ").replace(/\b\w/g, function(l){ return l.toUpperCase(); });
                html += "<div class=\"zywrap-json-card\" style=\"margin-bottom:1rem;\">";
                html += "<div class=\"zywrap-json-header\" style=\"font-weight:600; color:#4f46e5; margin-bottom:0.5rem; border-bottom:1px solid #e2e8f0; padding-bottom:0.25rem;\">" + cleanKey + "</div>";
                html += "<div class=\"zywrap-json-body\" style=\"color:#334155;\">";
                if (Array.isArray(val)) {
                    html += "<ul style=\"margin:0; padding-left:1.2rem;\">";
                    val.forEach(function(item) {
                        if (typeof item === "object") {
                            for(var subK in item) {
                                var subVal = item[subK];
                                if(Array.isArray(subVal)) {
                                    subVal.forEach(function(sv) { html += "<li>" + sv + "</li>"; });
                                } else if (subVal !== null && subVal !== "") {
                                    html += "<li>" + subVal + "</li>";
                                }
                            }
                        } else {
                            html += "<li>" + item + "</li>";
                        }
                    });
                    html += "</ul>";
                } else if (typeof val === "object") {
                    html += "<div style=\"display:grid; grid-template-columns: 1fr 1fr; gap:0.5rem;\">";
                    for (var subKey in val) {
                        var subVal = val[subKey];
                        if (subVal === null || subVal === "" || (Array.isArray(subVal) && subVal.length===0)) continue;
                        var cleanSubKey = subKey.replace(/_/g, " ").replace(/\b\w/g, function(l){ return l.toUpperCase(); });
                        var displayVal = Array.isArray(subVal) ? subVal.join(", ") : subVal;
                        html += "<div><strong>" + cleanSubKey + ":</strong> " + displayVal + "</div>";
                    }
                    html += "</div>";
                } else {
                    html += val;
                }
                html += "</div></div>";
            }
            html += "</div>";
            if (!hasData) return "<div style=\"color:#64748b; font-style:italic; margin-bottom:1rem;\">" + "' . $str_no_entities . '" + "</div>";
            return html;
        },
        smartInsert: function(editorId, contentHtml, contentPlain) {
            var isVisualActive = typeof tinyMCE !== "undefined" && tinyMCE.get(editorId) && !jQuery("#wp-" + editorId + "-wrap").hasClass("html-active");
            if (isVisualActive) {
                tinyMCE.get(editorId).execCommand("mceInsertContent", false, contentHtml);
            } else {
                var el = jQuery("#" + editorId);
                var currentContent = el.val().trim();
                var spacer = currentContent ? "\n\n--- ' . $str_ai_addition . ' ---\n\n" : "";
                el.val(currentContent + spacer + contentPlain);
                el.scrollTop(el[0].scrollHeight);
            }
        }
    };

    const $improveWrapper = $("#ai-improve-wrapper");
    const $improveCheckbox = $("#ai-improve");

    $(".wjportal-ai-header-menu").on("click", function(e) {
        e.preventDefault();
        $(".wjportal-ai-panel").toggle();
        $(this).toggleClass("is-active");
    });

    function checkEditorContent() {
        let content = "";
        if (typeof tinymce !== "undefined" && tinymce.get("description")) {
            content = tinymce.get("description").getContent({format: "text"}).trim();
        } else {
            content = $("#description").val().trim();
        }
        if (content.length > 0) {
            if ($improveWrapper.is(":hidden")) $improveWrapper.removeClass("wjportal-ai-hidden").hide().slideDown(250);
        } else {
            if ($improveWrapper.is(":visible")) {
                $improveWrapper.slideUp(250, function() {
                    $(this).addClass("wjportal-ai-hidden");
                    $improveCheckbox.prop("checked", false);
                });
            }
        }
    }
    setTimeout(checkEditorContent, 800);
    $("#description").on("input propertychange", checkEditorContent);
    if (typeof tinymce !== "undefined") {
        tinymce.on("AddEditor", function(e) {
            if(e.editor.id === "description") e.editor.on("keyup change", checkEditorContent);
        });
    }

    $(document).on("click", ".wjportal-ai-advanced-summary", function(e) {
        e.preventDefault();
        const $accordion = $(this).closest(".wjportal-ai-advanced-accordion");
        $accordion.toggleClass("is-open");
        $accordion.find(".wjportal-ai-accordion-content").slideToggle(250);
    });

    const $seoInputWrap = $("#seo-input-wrap");
    $("#ai-seo").on("change", function() {
        if ($(this).is(":checked")) {
            $seoInputWrap.removeClass("wjportal-ai-hidden").hide().slideDown(200);
        } else {
            $seoInputWrap.slideUp(200, function() { $(this).addClass("wjportal-ai-hidden"); });
        }
    });

    const cleanVal = (val) => {
        if (!val) return "";
        const lower = val.toLowerCase().trim();
        if (lower.startsWith("select") || lower === "uncategorized") return "";
        return val.trim();
    };

    $("#wjportal-generate-btn").on("click", function(e) {
        e.preventDefault();
        const $btn = $(this);
        const $icon = $("#wjportal-btn-icon");
        const $text = $("#wjportal-btn-text");

        $btn.prop("disabled", true).addClass("wjportal-ai-loading-ring");
        $icon.removeClass().addClass("dashicons dashicons-update wjportal-ai-text-white").css("animation", "spin 1s linear infinite");
        $text.text("' . $str_analyzing . '");

        if (typeof tinymce !== "undefined" && tinymce.get("description")) {
            $(tinymce.get("description").getContainer()).css("opacity", "0.6");
        }
        $("#description").css("opacity", "0.6");

        const jobData = [];
        const fieldsToSkip = ["termsconditions", "_wpnonce", "action", "uid", "id", "draft", "created", "wpjobportalpageid", "form_request", "upakid", "salaryfixed", "salarymin", "salarymax", "salaryduration", "salarytype"];

        $("#wpjobportal-form").find("input:not([type=\"submit\"], [type=\"button\"]), select, textarea")
            .not("#description")
            .not(".wjportal-ai-app-wrapper input, .wjportal-ai-app-wrapper select, .wjportal-ai-app-wrapper textarea")
            .each(function() {
                const $el = $(this);
                const nameAttr = $el.attr("name");
                const idAttr = $el.attr("id");
                if ($el.is(":disabled") || $el.prop("disabled")) return;
                if (!nameAttr || fieldsToSkip.includes(nameAttr) || nameAttr.startsWith("default_") || nameAttr.startsWith("edit_")) return;
                if ($el.attr("type") === "hidden" && !$el.hasClass("wpjobportal-job-form-city-field") && idAttr !== "tags") return;

                let val = "";
                if ($el.hasClass("wpjobportal-job-form-city-field") || idAttr === "tags") {
                    const tokens = [];
                    $el.siblings(".wpjobportal-input-list-wpjobportal, .token-input-list-wpjobportal").find("li p").each(function() { tokens.push($(this).text()); });
                    val = tokens.join(", ") || $el.val();
                } else if ($el.is("select")) {
                    val = $el.find("option:selected").text();
                } else if ($el.is(":checkbox") || $el.is(":radio")) {
                    if (!$el.is(":checked")) return;
                    val = $el.next("label").text() || $el.parent().text().trim() || "' . $str_yes . '";
                } else {
                    val = $el.val();
                }

                val = cleanVal(val);
                if (val) {
                    let labelText = "";
                    labelText = $el.closest(".wjportal-form-row").find(".wjportal-form-title").first().text();
                    if (!labelText && idAttr) labelText = $("label[for=\"" + idAttr + "\"]").text();
                    if (!labelText) labelText = $el.closest("label").clone().children().remove().end().text();
                    if (!labelText) labelText = nameAttr.replace(/[_-]/g, " ").replace(/\b\w/g, l => l.toUpperCase());
                    labelText = labelText.replace(/\*/g, "").trim();

                    if (labelText && !jobData.find(item => item.label === labelText)) {
                        jobData.push({ label: labelText, value: val });
                    }
                }
            });

        const salaryFixed = cleanVal($("input[name=\"salaryfixed\"]").val());
        const salaryMin = cleanVal($("input[name=\"salarymin\"]").val());
        const salaryMax = cleanVal($("input[name=\"salarymax\"]").val());
        const salaryDuration = cleanVal($("select[name=\"salaryduration\"] option:selected").text());
        let compensation = "";
        if (salaryFixed) {
            compensation = "$" + salaryFixed + " " + salaryDuration;
        } else if (salaryMin && salaryMax) {
            compensation = "$" + salaryMin + " - $" + salaryMax + " " + salaryDuration;
        }
        if (compensation.trim()) {
            jobData.push({ label: "' . $str_compensation . '", value: compensation.trim() });
        }

        const useFormData = $("#ai-form-data").is(":checked");
        const useCompanyData = $("#ai-company").is(":checked");

        if (!useFormData) {
            jobData.length = 0;
        }

        let selectedCompanyId = "";
        if (useCompanyData) {
            selectedCompanyId = $("select[name=\"companyid\"]").val() || $("input[name=\"companyid\"]").val() || "";
        }

        const aiData = {
            improving: $improveCheckbox.is(":checked"),
            length: $("input[name=\"ai-length\"]:checked").parent().text().trim(),
            tone: $("input[name=\"ai-tone\"]:checked").parent().text().trim(),
            companyContext: useCompanyData,
            companyId: selectedCompanyId,
            formDataContext: useFormData,
            formatStructure: $("#ai-format-structure").find("option:selected").text().trim(),
            language: wpjp_current_lang,
            customPrompt: $(".wjportal-ai-text-area-input").val() || "",
            seo: $("#ai-seo").is(":checked") ? $("#seo-input-wrap input").val() : ""
        };

        let highlights = [];
        $(".wjportal-ai-pill-checkbox input:checked").each(function() {
            highlights.push($(this).siblings(".wjportal-ai-pill-box").text());
        });

        var wjp_ajaxurl = typeof ajaxurl !== "undefined" ? ajaxurl : "' . $ajax_url_js . '";

        let existingContent = "";
        if (aiData.improving) {
            if (typeof tinymce !== "undefined" && tinymce.get("description")) {
                existingContent = tinymce.get("description").getContent({format: "text"}).trim();
            } else {
                existingContent = $("#description").val().trim();
            }
        }

        var requestData = {
            action: "wpjobportal_ajax",
            wpjobportalme: "zywrap",
            task: "executeJobCopilot",
            _wpnonce: $("input[name=\"_wpnonce\"]").val(),
            jobData: JSON.stringify(jobData),
            aiData: JSON.stringify(aiData),
            highlights: JSON.stringify(highlights),
            existing_content: existingContent
        };

        $.post(wjp_ajaxurl, requestData).done(function(response) {
            if(response.success) {
                $text.text("' . $str_generated . '");

                let aiOutput = response.data.output;

                let metaBlock = `<div style="background: #f8fafc; padding: 1rem; border-left: 4px solid #6366f1; border-radius: 0 0.5rem 0.5rem 0; margin-bottom: 1rem; font-family: sans-serif;">
                    <h4 style="margin-top: 0; color: #4f46e5; font-size: 14px;">' . $str_instructions . '</h4>
                    <ul style="margin-bottom: 0; font-size: 13px; color: #475569; line-height: 1.5;">
                        <li><strong>' . $str_tone . '</strong> ${aiData.tone} | <strong>' . $str_length . '</strong> ${aiData.length} | <strong>' . $str_format . '</strong> ${aiData.formatStructure}</li>
                        ${aiData.seo ? `<li><strong>' . $str_seo_target . '</strong> ${aiData.seo}</li>` : ""}
                        ${aiData.customPrompt ? `<li><strong>' . $str_custom . '</strong> "${aiData.customPrompt}"</li>` : ""}
                    </ul>
                </div>`;

                if (typeof tinyMCE !== "undefined" && tinyMCE.get("description") && !tinyMCE.get("description").isHidden()) {
                    let finalHtml = metaBlock + ZywrapFormatter.toHTML(aiOutput);
                    tinyMCE.get("description").setContent(finalHtml);
                } else {
                    let plainMeta = metaBlock.replace(/<[^>]*>?/gm, "\n").replace(/\n+/g, "\n").trim();
                    let plainOutput = ZywrapFormatter.toPlainText(aiOutput);
                    $("#description").val(plainMeta + "\n" + plainOutput);
                }

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

            if (typeof tinymce !== "undefined" && tinymce.get("description")) {
                $(tinymce.get("description").getContainer()).css("opacity", "1");
            }
            $("#description").css("opacity", "1");
        });
    });
});
';

wp_add_inline_script('wpjobportal-inline-handle', $wpjobportal_inline_js_script);

// Enqueue job portal variables CSS
wp_enqueue_style(
    'wpjobportal-variables',
    WPJOBPORTAL_PLUGIN_URL . 'includes/css/job_portal_variables.css'
);
?>

<style>
    /* ==========================================================================
       Zywrap AI Copilot - Soft Glass Premium UI
       ========================================================================== */

    /* --- CRITICAL FIX: Override global theme/plugin styles that break AI UI --- */
    .wjportal-ai-app-wrapper input[type="checkbox"],
    .wjportal-ai-app-wrapper input[type="radio"] {
        display: none !important;
        padding: 0 !important;
        border: none !important;
        border-radius: 0 !important;
        cursor: default !important;
        font-size: inherit !important;
        font-weight: inherit !important;
        width: 0 !important;
        height: 0 !important;
        opacity: 0 !important;
        position: absolute !important;
        margin: 0 !important;
    }

    .wjportal-ai-app-wrapper textarea,
    .wjportal-ai-app-wrapper select,
    .wjportal-ai-app-wrapper input[type="text"],
    .wjportal-ai-app-wrapper input[type="email"],
    .wjportal-ai-app-wrapper input[type="search"] {
        height: auto !important;
        min-height: unset !important;
        line-height: 1.5 !important;
    }

    .wjportal-ai-app-wrapper .wjportal-ai-text-area-input {
        min-height: 80px !important;
    }
    /* --- END CRITICAL FIXES --- */

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

        /* SCROLL FIX: Bounding the parent so the child is forced to scroll */
        max-height: 750px;
        overflow: hidden;
    }

    @media (min-width: 1024px) {
        .wjportal-ai-panel { width: 440px; }
    }

    /* --- EDITOR HEADER & TOOLBAR --- */
    .wjportal-ai-editor-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
    .wjportal-ai-editor-title { font-size: 1.125rem; font-weight: 600; color: var(--wpjp-secondary-color, #1e293b); margin: 0; }

    /* --- NEW REDESIGNED TOGGLE BUTTON --- */
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
    .wjportal-ai-header-menu .dashicons { font-size: 16px; width: 16px; height: 16px; line-height: 1; }
    /* --- END REDESIGNED BUTTON --- */

    .wjportal-ai-toolbar { display: flex; gap: 0.25rem; color: var(--wpjp-body-font-color, #94a3b8); background-color: var(--wpjp-background-color, #f8fafc); border: 1px solid var(--wpjp-border-color, #e2e8f0); border-radius: 0.5rem; padding: 0.25rem; }
    .wjportal-ai-toolbar-btn { width: 2rem; height: 2rem; display: flex; align-items: center; justify-content: center; border-radius: 0.25rem; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); transition: all 0.2s; background: transparent; border: none; cursor: pointer; color: var(--wpjp-body-font-color, #94a3b8); }
    .wjportal-ai-toolbar-btn:hover { color: var(--wpjp-secondary-color, #334155); background-color: var(--wpjp-card-background, white); }
    .wjportal-ai-toolbar-divider { width: 1px; height: 1.25rem; background-color: var(--wpjp-border-color, #e2e8f0); margin: auto 0.25rem; }

    /* --- MAIN EDITOR INPUT --- */
    .wjportal-ai-editor-input-container { display: flex; flex-direction: column; overflow: hidden; flex: 1; }
    .wjportal-ai-main-editor {
        flex: 1;
        width: 100%;
        padding: 1.25rem;
        background: transparent;
        border: none;
        resize: vertical;
        color: var(--wpjp-body-font-color, #334155);
        line-height: 1.625;
        outline: none;
        min-height: 400px;
        font-family: inherit;
        font-size: inherit;
    }

    /* Scrollbar styling for both editor and settings body */
    .wjportal-ai-main-editor::-webkit-scrollbar,
    .wjportal-ai-body::-webkit-scrollbar { width: 6px; }
    .wjportal-ai-main-editor::-webkit-scrollbar-track,
    .wjportal-ai-body::-webkit-scrollbar-track { background: transparent; }
    .wjportal-ai-main-editor::-webkit-scrollbar-thumb,
    .wjportal-ai-body::-webkit-scrollbar-thumb { background: var(--wpjp-border-color, #cbd5e1); border-radius: 4px; }

    /* --- AI SIDEBAR COMPONENTS --- */
    .wjportal-ai-header-top { padding: 1.25rem; border-bottom: 1px solid var(--wpjp-border-color); display: flex; align-items: center; justify-content: space-between; background-color: var(--wpjp-card-background); border-top-left-radius: 1rem; border-top-right-radius: 1rem; }
    .wjportal-ai-header-title-wrapper { display: flex; align-items: center; gap: 0.75rem; }
    .wjportal-ai-header-icon { width: 2rem; height: 2rem; border-radius: 0.5rem; background-color: var(--wpjp-primary-color); opacity: 0.9; color: white; display: flex; align-items: center; justify-content: center; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); }
    .wjportal-ai-header-icon .dashicons { font-size: 16px; width: 16px; height: 16px; }
    .wjportal-ai-header-title { font-weight: 600; color: var(--wpjp-secondary-color, #1e293b); line-height: 1.25; font-size: 1rem; margin: 0; }
    .wjportal-ai-header-subtitle { font-size: 11px; font-weight: 500; color: var(--wpjp-primary-color, #4f46e5); text-transform: uppercase; letter-spacing: 0.025em; margin: 0; }

    /* SCROLL FIX: flex: 1 combined with min-height: 0 ensures this shrinks and scrolls correctly */
    .wjportal-ai-body {
        padding: 1.5rem;
        overflow-y: auto;
        flex: 1;
        min-height: 0;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        background-color: var(--wpjp-card-background);
    }

    .wjportal-ai-footer { padding: 1.5rem; border-top: 1px solid var(--wpjp-border-color); background-color: var(--wpjp-card-background); border-bottom-left-radius: 1rem; border-bottom-right-radius: 1rem; }
    .wjportal-ai-footer-note { display: flex; align-items: center; justify-content: center; gap: 0.375rem; margin-top: 0.75rem; font-size: 0.75rem; color: var(--wpjp-body-font-color); }
    .wjportal-ai-shield-icon { color: var(--wpjp-body-font-color, #cbd5e1); font-size: 14px; width: 14px; height: 14px; }

    /* --- FORMS & INPUTS --- */
    .wjportal-ai-setting-group { display: flex; flex-direction: column; gap: 1rem; }
    .wjportal-ai-setting-group-md { display: flex; flex-direction: column; gap: 0.75rem; }
    .wjportal-ai-setting-group-sm { display: flex; flex-direction: column; gap: 0.5rem; }
    .wjportal-ai-setting-label { font-size: 0.75rem; font-weight: 600; color: var(--wpjp-body-font-color, #475569); text-transform: uppercase; letter-spacing: 0.05em; display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; }

    .wjportal-ai-input-soft { background: var(--wpjp-card-background, white); border: 1px solid var(--wpjp-border-color, #e2e8f0); border-radius: 0.75rem; box-shadow: inset 0 2px 4px 0 rgba(0,0,0,0.02); width: 100%; }
    .wjportal-ai-input-soft:focus-within, .wjportal-ai-select-soft:focus { border-color: var(--wpjp-primary-color, #6366f1); outline: none; }
    .wjportal-ai-select-soft { background: var(--wpjp-card-background, white); border: 1px solid var(--wpjp-border-color, #e2e8f0); border-radius: 0.75rem; padding: 0.625rem 1rem; width: 100%; font-size: 0.875rem; color: var(--wpjp-body-font-color, #334155); appearance: none; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-position: right 0.5rem center; background-repeat: no-repeat; background-size: 1.5em 1.5em; }
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
    .wjportal-ai-context-cards-wrapper { display: flex; flex-direction: column; gap: 0.625rem; }
    .wjportal-ai-feature-card { background: var(--wpjp-card-background, #ffffff); border: 1px solid var(--wpjp-border-color, #e2e8f0); border-radius: 0.875rem; padding: 1rem; display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; box-shadow: 0 2px 4px rgba(0,0,0,0.02); transition: all 0.2s ease; cursor: pointer; }
    .wjportal-ai-feature-card:hover { border-color: var(--wpjp-primary-color, #cbd5e1); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .wjportal-ai-feature-card:has(input:checked) { border-color: var(--wpjp-primary-color, #a5b4fc); background: var(--wpjp-card-background, #fcfdff); }
    .wjportal-ai-feature-card:focus-within { outline: 2px solid var(--wpjp-primary-color, #a5b4fc); outline-offset: 2px; }

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

    /* --- UTILITIES --- */
    .wjportal-ai-divider { border-color: var(--wpjp-border-color, #f1f5f9); border-top: 1px solid var(--wpjp-border-color, #f1f5f9); margin: 0; }
    .wjportal-ai-seo-wrapper { display: flex; flex-direction: column; gap: 0.5rem; }
    .wjportal-ai-mt-1 { margin-top: 0.25rem; }
    .wjportal-ai-bg-white { background-color: var(--wpjp-card-background, white); }
    .wjportal-ai-hidden { display: none !important; }
    .wjportal-ai-text-white { color: #ffffff; }
    .wjportal-ai-text-indigo-200 { color: var(--wpjp-background-color, #c7d2fe); }

    /* --- OVERFLOW-SAFE ACCORDION --- */
    .wjportal-ai-advanced-accordion {
        background: var(--wpjp-background-color, #f8fafc);
        border: 1px solid var(--wpjp-border-color, #e2e8f0);
        border-radius: 0.75rem;
        transition: all 0.3s ease;
    }

    .wjportal-ai-advanced-accordion.is-open {
        background: var(--wpjp-card-background, white);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        border-color: var(--wpjp-border-color, #cbd5e1);
    }

    .wjportal-ai-advanced-summary {
        padding: 1rem;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--wpjp-secondary-color, #334155);
        user-select: none;
        width: 100%;
        box-sizing: border-box;
    }

    .wjportal-ai-acc-trigger-left { display: flex; align-items: center; gap: 0.5rem; }
    .wjportal-ai-acc-trigger-icon { color: var(--wpjp-body-font-color, #94a3b8); font-size: 16px; width: 16px; height: 16px; }
    .wjportal-ai-accordion-icon { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); color: var(--wpjp-body-font-color, #94a3b8); font-size: 16px; width: 16px; height: 16px; }
    .wjportal-ai-advanced-accordion.is-open .wjportal-ai-accordion-icon { transform: rotate(180deg); color: var(--wpjp-primary-color, #6366f1); }

    .wjportal-ai-accordion-content {
        display: none;
    }

    .wjportal-ai-accordion-content-inner {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        padding: 1rem 1rem 1.25rem 1rem;
        border-top: 1px solid var(--wpjp-border-color, #f1f5f9);
    }
</style>

<?php
/**
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
                    <span class="dashicons dashicons-admin-customizer"></span>
                </div>
                <div>
                    <h3 class="wjportal-ai-header-title">' . esc_html__('Zywrap Copilot', 'wp-job-portal') . '</h3>
                    <p class="wjportal-ai-header-subtitle">' . esc_html__('AI Generation', 'wp-job-portal') . '</p>
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
                <label class="wjportal-ai-setting-label">' . esc_html__('Highlight & Prioritize', 'wp-job-portal') . '</label>
                <div class="wjportal-ai-pill-group">
                    <label class="wjportal-ai-pill-checkbox"><input type="checkbox" checked><span class="wjportal-ai-pill-box">' . esc_html__('Role Requirements', 'wp-job-portal') . '</span></label>
                    <label class="wjportal-ai-pill-checkbox"><input type="checkbox" checked><span class="wjportal-ai-pill-box">' . esc_html__('Day-to-Day Responsibilities', 'wp-job-portal') . '</span></label>
                    <label class="wjportal-ai-pill-checkbox"><input type="checkbox"><span class="wjportal-ai-pill-box">' . esc_html__('Company Culture & Vibe', 'wp-job-portal') . '</span></label>
                    <label class="wjportal-ai-pill-checkbox"><input type="checkbox"><span class="wjportal-ai-pill-box">' . esc_html__('Growth & Career Path', 'wp-job-portal') . '</span></label>
                    <label class="wjportal-ai-pill-checkbox"><input type="checkbox"><span class="wjportal-ai-pill-box">' . esc_html__('Benefits & Perks', 'wp-job-portal') . '</span></label>
                    <label class="wjportal-ai-pill-checkbox"><input type="checkbox"><span class="wjportal-ai-pill-box">' . esc_html__('Tech Stack / Tools', 'wp-job-portal') . '</span></label>
                    <label class="wjportal-ai-pill-checkbox"><input type="checkbox"><span class="wjportal-ai-pill-box">' . esc_html__('Work-Life Balance', 'wp-job-portal') . '</span></label>
                    <label class="wjportal-ai-pill-checkbox"><input type="checkbox"><span class="wjportal-ai-pill-box">' . esc_html__('Compensation & Salary', 'wp-job-portal') . '</span></label>
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
                            <label class="wjportal-ai-setting-label">' . esc_html__('Brand Voice & Tone', 'wp-job-portal') . '</label>
                            <div class="wjportal-ai-segmented-control">
                                <label class="wjportal-ai-segmented-item">
                                    <input type="radio" name="ai-tone" value="professional" checked> ' . esc_html__('Corporate', 'wp-job-portal') . '
                                </label>
                                <label class="wjportal-ai-segmented-item">
                                    <input type="radio" name="ai-tone" value="approachable"> ' . esc_html__('Approachable', 'wp-job-portal') . '
                                </label>
                                <label class="wjportal-ai-segmented-item">
                                    <input type="radio" name="ai-tone" value="technical"> ' . esc_html__('Technical', 'wp-job-portal') . '
                                </label>
                            </div>
                        </div>

                        <div class="wjportal-ai-setting-group-md">
                            <label class="wjportal-ai-setting-label">' . esc_html__('Auto-Attached Context', 'wp-job-portal') . '</label>
                            <div class="wjportal-ai-context-cards-wrapper">
                                <label class="wjportal-ai-feature-card" for="ai-company">
                                    <div class="wjportal-ai-feature-card-text">
                                        <div class="wjportal-ai-feature-card-header">
                                            <span class="dashicons dashicons-building wjportal-ai-feature-card-icon"></span>
                                            <span class="wjportal-ai-feature-card-title">' . esc_html__('Company Profile', 'wp-job-portal') . '</span>
                                        </div>
                                        <span class="wjportal-ai-feature-card-desc">' . esc_html__('Includes company mission, culture, and core values.', 'wp-job-portal') . '</span>
                                    </div>
                                    <div class="wjportal-ai-switch-container">
                                        <input type="checkbox" id="ai-company" checked>
                                        <div class="wjportal-ai-switch-track">
                                            <div class="wjportal-ai-switch-thumb"><span class="dashicons dashicons-yes wjportal-ai-switch-icon"></span></div>
                                        </div>
                                    </div>
                                </label>

                                <label class="wjportal-ai-feature-card" for="ai-form-data">
                                    <div class="wjportal-ai-feature-card-text">
                                        <div class="wjportal-ai-feature-card-header">
                                            <span class="dashicons dashicons-clipboard wjportal-ai-feature-card-icon"></span>
                                            <span class="wjportal-ai-feature-card-title">' . esc_html__('Job Form Meta Data', 'wp-job-portal') . '</span>
                                        </div>
                                        <span class="wjportal-ai-feature-card-desc">' . esc_html__('Uses location, salary, and job type from the form.', 'wp-job-portal') . '</span>
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
                                <option value="balanced">' . esc_html__('Standard (Paragraphs & Bullet Points)', 'wp-job-portal') . '</option>
                                <option value="bullets">' . esc_html__('Highly Skimmable (Mostly Bullet Points)', 'wp-job-portal') . '</option>
                                <option value="paragraphs">' . esc_html__('Story-Driven (Narrative Paragraphs)', 'wp-job-portal') . '</option>
                            </select>
                        </div>

                        <div class="wjportal-ai-setting-group-sm">
                            <label class="wjportal-ai-setting-label">' . esc_html__('Specific Free-form Instructions', 'wp-job-portal') . '</label>
                            <div class="wjportal-ai-input-soft wjportal-ai-bg-white">
                                <textarea rows="3" class="wjportal-ai-text-area-input" placeholder="' . esc_attr__('e.g., "Must mention our 4-day work week and our specific tech stack..." ', 'wp-job-portal') . '"></textarea>
                            </div>
                        </div>

                        <div class="wjportal-ai-setting-group-sm wjportal-ai-hidden">
                            <label class="wjportal-ai-setting-label">' . esc_html__('Output Language', 'wp-job-portal') . '</label>
                            <select id="ai-language" class="wjportal-ai-select-soft wjportal-ai-bg-white">
                                <option value="en">' . esc_html__('English (US)', 'wp-job-portal') . '</option>
                                <option value="en-uk">' . esc_html__('English (UK)', 'wp-job-portal') . '</option>
                                <option value="es">' . esc_html__('Spanish', 'wp-job-portal') . '</option>
                                <option value="fr">' . esc_html__('French', 'wp-job-portal') . '</option>
                                <option value="de">' . esc_html__('German', 'wp-job-portal') . '</option>
                            </select>
                        </div>

                        <div class="wjportal-ai-seo-wrapper">
                            <label class="wjportal-ai-feature-card" for="ai-seo">
                                <div class="wjportal-ai-feature-card-text">
                                    <div class="wjportal-ai-feature-card-header">
                                        <span class="dashicons dashicons-search wjportal-ai-feature-card-icon"></span>
                                        <span class="wjportal-ai-feature-card-title">' . esc_html__('Optimize for SEO', 'wp-job-portal') . '</span>
                                    </div>
                                    <span class="wjportal-ai-feature-card-desc">' . esc_html__('Improve visibility on Google Jobs & Indeed.', 'wp-job-portal') . '</span>
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
                <span id="wjportal-btn-text">' . esc_html__('Generate Description', 'wp-job-portal') . '</span>
            </button>
        </div>

    </div>
</div>';
?>