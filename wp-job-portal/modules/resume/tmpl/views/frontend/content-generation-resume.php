<?php if (!defined('ABSPATH')) die('Restricted Access');

$wpjobportal_desc_editor = isset($wpjobportal_desc_editor) ? $wpjobportal_desc_editor : '';

// Enqueue marked.js for Markdown parsing
wp_enqueue_script( 'wpjobportal-marked-js', WPJOBPORTAL_PLUGIN_URL . 'includes/js/zywrap/marked.min.js', array('jquery'), WPJOBPORTAL_VERSION, true );

// Register handle for inline JS injection
wp_register_script( 'wpjobportal-resume-inline-handle', '', array(), WPJOBPORTAL_VERSION, false );
wp_enqueue_script('wpjobportal-resume-inline-handle');

// Prepare localized strings and safe variables for JS injection
$wpjobportal_locale_js = esc_js(get_user_locale());
$wpjobportal_ajax_url_js = esc_url(admin_url('admin-ajax.php'));

$wpjobportal_str_ai_addition     = esc_html__('✨ AI ADDITION', 'wp-job-portal');
$wpjobportal_str_analyzing       = esc_html__('Analyzing Profile...', 'wp-job-portal');
$wpjobportal_str_yes             = esc_html__('Yes', 'wp-job-portal');
$wpjobportal_str_generated       = esc_html__('Skills Generated!', 'wp-job-portal');
$wpjobportal_str_failed          = esc_html__('Generation Failed: ', 'wp-job-portal');
$wpjobportal_str_unknown         = esc_html__('Unknown error.', 'wp-job-portal');
$wpjobportal_str_server_err      = esc_html__('Server communication error. Please try again.', 'wp-job-portal');
$wpjobportal_str_generate_btn    = esc_html__('Generate Skills', 'wp-job-portal');

$wpjobportal_inline_js_script = '
var wpjp_current_lang = "' . $wpjobportal_locale_js . '";

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
            var spacer = currentContent ? "\n\n--- ' . $wpjobportal_str_ai_addition . ' ---\n\n" : "";
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
        $text.text("' . $wpjobportal_str_analyzing . '");

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
                        val = $el.next("label").text() || $el.parent().text().trim() || "' . $wpjobportal_str_yes . '";
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

        var wjp_ajaxurl = typeof ajaxurl !== "undefined" ? ajaxurl : "' . $wpjobportal_ajax_url_js . '";

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
                $text.text("' . $wpjobportal_str_generated . '");
                let aiOutput = response.data.output;

                // For resume skills, we convert to raw text since we aren\'t using TinyMCE here
                let plainOutput = ZywrapFormatter.toPlainText(aiOutput);
                ZywrapFormatter.smartInsert("skills", plainOutput);

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
            $("#skills").css("opacity", "1");
        });
    });
});
';

wp_add_inline_script('wpjobportal-resume-inline-handle', $wpjobportal_inline_js_script);

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
wp_register_style('wpjobportal-ai-writer-resume', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/wpjobportal-ai-writer-resume.css', array(), wpjobportal_asset_version('includes/css/wpjobportal-ai-writer-resume.css'));
wp_print_styles('wpjobportal-ai-writer-resume');
?>

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