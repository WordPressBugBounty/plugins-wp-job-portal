<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
// Prevent undefined variable warning for the editor description
$wpjobportal_desc_editor = isset($wpjobportal_desc_editor) ? $wpjobportal_desc_editor : '';

wp_enqueue_script( 'wpjobportal-marked-js', WPJOBPORTAL_PLUGIN_URL . 'includes/js/zywrap/marked.min.js', array('jquery'), WPJOBPORTAL_VERSION, true );

// Register handle for inline JS injection
wp_register_script( 'wpjobportal-inline-handle', '', array(), WPJOBPORTAL_VERSION, false );
wp_enqueue_script('wpjobportal-inline-handle');

// Prepare localized strings and safe variables for JS injection
$wpjobportal_locale_js = esc_js(get_user_locale());
$wpjobportal_ajax_url_js = esc_url(admin_url('admin-ajax.php'));

$wpjobportal_str_ai_addition     = esc_html__('✨ AI ADDITION', 'wp-job-portal');
$wpjobportal_str_analyzing       = esc_html__('Analyzing parameters...', 'wp-job-portal');
$wpjobportal_str_yes             = esc_html__('Yes', 'wp-job-portal');
$wpjobportal_str_generated       = esc_html__('Content Generated!', 'wp-job-portal');
$wpjobportal_str_instructions    = esc_html__('✨ Company Copilot Instructions Applied:', 'wp-job-portal');
$wpjobportal_str_tone            = esc_html__('Tone:', 'wp-job-portal');
$wpjobportal_str_length          = esc_html__('Length:', 'wp-job-portal');
$wpjobportal_str_format          = esc_html__('Format:', 'wp-job-portal');
$wpjobportal_str_seo_target      = esc_html__('SEO Target:', 'wp-job-portal');
$wpjobportal_str_custom          = esc_html__('Custom:', 'wp-job-portal');
$wpjobportal_str_failed          = esc_html__('Generation Failed: ', 'wp-job-portal');
$wpjobportal_str_unknown         = esc_html__('Unknown error.', 'wp-job-portal');
$wpjobportal_str_server_err      = esc_html__('Server communication error. Please try again.', 'wp-job-portal');
$wpjobportal_str_generate_btn    = esc_html__('Generate Profile', 'wp-job-portal');

$wpjobportal_inline_js_script = '
var wpjp_current_lang = "' . $wpjobportal_locale_js . '";

jQuery(document).ready(function($) {
    window.ZywrapFormatter = {
        cleanRawMarkdown: function(text) { if (!text) return ""; return text.replace(/([^\s\n])\s+(#{1,6}\s+[A-Z])/g, "$1\n\n$2").replace(/([^\s\n])\s+(-\s+[A-Z0-9])/g, "$1\n$2"); },
        toHTML: function(markdownText) { var preppedText = this.cleanRawMarkdown(markdownText); if (typeof marked !== "undefined") { return marked.parse(preppedText, { breaks: true, gfm: true }); } return preppedText; },
        toPlainText: function(markdownText) { var html = this.toHTML(markdownText); html = html.replace(/<br\s*[\/ ]?>/gi, "\n"); html = html.replace(/<\/p>/gi, "\n\n"); html = html.replace(/<h[1-6]>(.*?)<\/h[1-6]>/gi, "\n=== $1 ===\n\n"); html = html.replace(/<li>(.*?)<\/li>/gi, " • $1\n"); html = html.replace(/<\/ul>/gi, "\n"); html = html.replace(/<\/ol>/gi, "\n"); var temp = document.createElement("div"); temp.innerHTML = html; var plainText = temp.innerText || temp.textContent; return plainText.trim().replace(/\n{3,}/g, "\n\n"); },
        smartInsert: function(editorId, contentHtml, contentPlain) { var isVisualActive = typeof tinyMCE !== "undefined" && tinyMCE.get(editorId) && !jQuery("#wp-" + editorId + "-wrap").hasClass("html-active"); if (isVisualActive) { tinyMCE.get(editorId).execCommand("mceInsertContent", false, contentHtml); } else { var el = jQuery("#" + editorId); var currentContent = el.val().trim(); var spacer = currentContent ? "\n\n--- ' . $wpjobportal_str_ai_addition . ' ---\n\n" : ""; el.val(currentContent + spacer + contentPlain); el.scrollTop(el[0].scrollHeight); } }
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
                $improveWrapper.slideUp(250, function() { $(this).addClass("wjportal-ai-hidden"); $improveCheckbox.prop("checked", false); });
            }
        }
    }
    setTimeout(checkEditorContent, 800);
    $("#description").on("input propertychange", checkEditorContent);
    if (typeof tinymce !== "undefined") { tinymce.on("AddEditor", function(e) { if(e.editor.id === "description") e.editor.on("keyup change", checkEditorContent); }); }

    $(document).on("click", ".wjportal-ai-advanced-summary", function(e) {
        e.preventDefault();
        const $accordion = $(this).closest(".wjportal-ai-advanced-accordion");
        $accordion.toggleClass("is-open");
        $accordion.find(".wjportal-ai-accordion-content").slideToggle(250);
    });

    const $seoInputWrap = $("#seo-input-wrap");
    $("#ai-seo").on("change", function() {
        if ($(this).is(":checked")) { $seoInputWrap.removeClass("wjportal-ai-hidden").hide().slideDown(200); } else { $seoInputWrap.slideUp(200, function() { $(this).addClass("wjportal-ai-hidden"); }); }
    });

    const cleanVal = (val) => { if (!val) return ""; const lower = val.toLowerCase().trim(); if (lower.startsWith("select") || lower === "uncategorized") return ""; return val.trim(); };

    $("#wjportal-generate-btn").on("click", function(e) {
        e.preventDefault();
        const $btn = $(this);
        const $icon = $("#wjportal-btn-icon");
        const $text = $("#wjportal-btn-text");

        $btn.prop("disabled", true).addClass("wjportal-ai-loading-ring");
        $icon.removeClass().addClass("dashicons dashicons-update wjportal-ai-text-white").css("animation", "spin 1s linear infinite");
        $text.text("' . $wpjobportal_str_analyzing . '");

        if (typeof tinymce !== "undefined" && tinymce.get("description")) { $(tinymce.get("description").getContainer()).css("opacity", "0.6"); }
        $("#description").css("opacity", "0.6");

        const companyData = [];
        const fieldsToSkip = ["termsconditions", "_wpnonce", "action", "uid", "id", "draft", "created", "wpjobportalpageid", "form_request", "upakid"];

        $("#wpjobportal-form").find("input:not([type=\"submit\"], [type=\"button\"]), select, textarea")
            .not("#description")
            .not(".wjportal-ai-app-wrapper input, .wjportal-ai-app-wrapper select, .wjportal-ai-app-wrapper textarea")
            .each(function() {
                const $el = $(this);
                const nameAttr = $el.attr("name");
                const idAttr = $el.attr("id");
                if ($el.is(":disabled") || $el.prop("disabled") || $el.attr("type") === "hidden") return;
                if (!nameAttr || fieldsToSkip.includes(nameAttr)) return;

                let val = "";
                if ($el.hasClass("wpjobportal-company-form-city-field") || idAttr === "tags") {
                    const tokens = [];
                    $el.siblings(".wpjobportal-input-list-wpjobportal, .token-input-list-wpjobportal").find("li p").each(function() { tokens.push($(this).text()); });
                    val = tokens.join(", ") || $el.val();
                } else if ($el.is("select")) {
                    val = $el.find("option:selected").text();
                } else if ($el.is(":checkbox") || $el.is(":radio")) {
                    if (!$el.is(":checked")) return;
                    val = $el.next("label").text() || $el.parent().text().trim() || "' . $wpjobportal_str_yes . '";
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

                    if (labelText && !companyData.find(item => item.label === labelText)) {
                        companyData.push({ label: labelText, value: val });
                    }
                }
            });

        const useFormData = $("#ai-form-data").is(":checked");
        if (!useFormData) companyData.length = 0;

        const aiData = {
            improving: $improveCheckbox.is(":checked"),
            length: $("input[name=\"ai-length\"]:checked").parent().text().trim(),
            tone: $("input[name=\"ai-tone\"]:checked").parent().text().trim(),
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

        var wjp_ajaxurl = typeof ajaxurl !== "undefined" ? ajaxurl : "' . $wpjobportal_ajax_url_js . '";

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
            task: "executeCompanyCopilot",
            _wpnonce: $("input[name=\"_wpnonce\"]").val(),
            companyData: JSON.stringify(companyData),
            aiData: JSON.stringify(aiData),
            highlights: JSON.stringify(highlights),
            existing_content: existingContent
        };

        $.post(wjp_ajaxurl, requestData).done(function(response) {
            if(response.success) {
                $text.text("' . $wpjobportal_str_generated . '");
                let aiOutput = response.data.output;

                let metaBlock = `<div style="background: #f8fafc; padding: 1rem; border-left: 4px solid #6366f1; border-radius: 0 0.5rem 0.5rem 0; margin-bottom: 1rem; font-family: inherit;">
                    <h4 style="margin-top: 0; color: #4f46e5; font-size: 14px;">' . $wpjobportal_str_instructions . '</h4>
                    <ul style="margin-bottom: 0; font-size: 13px; color: #475569; line-height: 1.5;">
                        <li><strong>' . $wpjobportal_str_tone . '</strong> ${aiData.tone} | <strong>' . $wpjobportal_str_length . '</strong> ${aiData.length} | <strong>' . $wpjobportal_str_format . '</strong> ${aiData.formatStructure}</li>
                        ${aiData.seo ? `<li><strong>' . $wpjobportal_str_seo_target . '</strong> ${aiData.seo}</li>` : ""}
                        ${aiData.customPrompt ? `<li><strong>' . $wpjobportal_str_custom . '</strong> "${aiData.customPrompt}"</li>` : ""}
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
                alert("' . $wpjobportal_str_failed . '" + (response.data.message || "' . $wpjobportal_str_unknown . '"));
            }
        }).fail(function() {
            alert("' . $wpjobportal_str_server_err . '");
        }).always(function() {
            $btn.prop("disabled", false).removeClass("wjportal-ai-loading-ring");
            $icon.removeClass().addClass("dashicons dashicons-yes wjportal-ai-text-white").css("animation", "");
            setTimeout(function() {
                $icon.removeClass().addClass("dashicons dashicons-lightning wjportal-ai-text-indigo-200").css("animation", "");
                $text.text("' . $wpjobportal_str_generate_btn . '");
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
    WPJOBPORTAL_PLUGIN_URL . 'includes/css/job_portal_variables.css',
    array(),
    wpjobportal_asset_version('includes/css/job_portal_variables.css')
);
?>

<?php
// Styles from a file browsers cache, printed where the inline block was so the cascade is unchanged
wp_register_style('wpjobportal-ai-writer-company', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/wpjobportal-ai-writer-company.css', array(), wpjobportal_asset_version('includes/css/wpjobportal-ai-writer-company.css'));
wp_print_styles('wpjobportal-ai-writer-company');
?>

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
                    <span class="dashicons dashicons-building"></span>
                </div>
                <div>
                    <h3 class="wjportal-ai-header-title">' . esc_html__('Zywrap Copilot', 'wp-job-portal') . '</h3>
                    <p class="wjportal-ai-header-subtitle">' . esc_html__('Company Profile AI', 'wp-job-portal') . '</p>
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
                    <label class="wjportal-ai-pill-checkbox"><input type="checkbox" checked><span class="wjportal-ai-pill-box">' . esc_html__('Mission & Vision', 'wp-job-portal') . '</span></label>
                    <label class="wjportal-ai-pill-checkbox"><input type="checkbox" checked><span class="wjportal-ai-pill-box">' . esc_html__('Core Values', 'wp-job-portal') . '</span></label>
                    <label class="wjportal-ai-pill-checkbox"><input type="checkbox"><span class="wjportal-ai-pill-box">' . esc_html__('Company History', 'wp-job-portal') . '</span></label>
                    <label class="wjportal-ai-pill-checkbox"><input type="checkbox"><span class="wjportal-ai-pill-box">' . esc_html__('Products & Services', 'wp-job-portal') . '</span></label>
                    <label class="wjportal-ai-pill-checkbox"><input type="checkbox"><span class="wjportal-ai-pill-box">' . esc_html__('Workplace Culture', 'wp-job-portal') . '</span></label>
                    <label class="wjportal-ai-pill-checkbox"><input type="checkbox"><span class="wjportal-ai-pill-box">' . esc_html__('Diversity & Inclusion', 'wp-job-portal') . '</span></label>
                    <label class="wjportal-ai-pill-checkbox"><input type="checkbox"><span class="wjportal-ai-pill-box">' . esc_html__('Tech Stack / Tools', 'wp-job-portal') . '</span></label>
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
                                    <input type="radio" name="ai-tone" value="innovative"> ' . esc_html__('Innovative/Tech', 'wp-job-portal') . '
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
                                            <span class="wjportal-ai-feature-card-title">' . esc_html__('Company Form Data', 'wp-job-portal') . '</span>
                                        </div>
                                        <span class="wjportal-ai-feature-card-desc">' . esc_html__('Uses location, industry, and size data from the form.', 'wp-job-portal') . '</span>
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
                                <textarea rows="3" class="wjportal-ai-text-area-input" placeholder="' . esc_attr__('e.g., "Mention our recent Series A funding..."', 'wp-job-portal') . '"></textarea>
                            </div>
                        </div>

                        <div class="wjportal-ai-seo-wrapper">
                            <label class="wjportal-ai-feature-card" for="ai-seo">
                                <div class="wjportal-ai-feature-card-text">
                                    <div class="wjportal-ai-feature-card-header">
                                        <span class="dashicons dashicons-search wjportal-ai-feature-card-icon"></span>
                                        <span class="wjportal-ai-feature-card-title">' . esc_html__('Optimize for SEO', 'wp-job-portal') . '</span>
                                    </div>
                                    <span class="wjportal-ai-feature-card-desc">' . esc_html__('Improve visibility on search engines.', 'wp-job-portal') . '</span>
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
                <span id="wjportal-btn-text">' . esc_html__('Generate Profile', 'wp-job-portal') . '</span>
            </button>
        </div>

    </div>
</div>';
?>