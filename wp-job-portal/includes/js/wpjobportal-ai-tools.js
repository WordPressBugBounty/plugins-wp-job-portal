/*
 * WP Job Portal 2.9.0: the AI tools bar.
 *
 * One bar can hold several task buttons. A button may ask for text first (an
 * input box opens), and the bar may send the surrounding form's fields (the job
 * form). Everything is read from class names (wpjp-ai-task-*, wpjp-ai-ctx-*),
 * because the bar is printed inside forms whose content goes through wp_kses.
 * The answer is shown as plain text only (textContent), so nothing the model
 * writes can run as markup, and it is only ever copied by the person: nothing
 * is inserted or sent anywhere on its own.
 */
(function ($) {
    'use strict';
    if (typeof wpjpAiTools === 'undefined') {
        return;
    }

    function classValue(el, prefix) {
        var found = '';
        $.each(String(el.className || '').split(/\s+/), function (i, c) {
            if (c.indexOf(prefix) === 0) {
                found = c.substring(prefix.length);
            }
        });
        return found;
    }

    function context($bar) {
        var ctx = {};
        var job = classValue($bar[0], 'wpjp-ai-ctx-job-');
        var app = classValue($bar[0], 'wpjp-ai-ctx-application-');
        if (/^\d+$/.test(job)) { ctx.job = parseInt(job, 10); }
        if (/^\d+$/.test(app)) { ctx.application = parseInt(app, 10); }
        return ctx;
    }

    function show($el, on) {
        $el.toggleClass('wpjp-ai-hide', !on);
        var $bar = $el.closest('.wpjp-ai-tools');
        $bar.toggleClass('is-open', $bar.find('.wpjp-ai-tools-input, .wpjp-ai-tools-result').not('.wpjp-ai-hide').length > 0);
    }

    /* The form's fields as label/value/name, as the 2.8.0 writers collect them. */
    function formFields($bar) {
        var $form = $bar.closest('form');
        var out = [];
        var seen = {};
        if (!$form.length) {
            return out;
        }
        // Not the bar itself, and not the description writer's own panel (its tone and length options).
        $form.find('input, select, textarea').filter(function () {
            // The editor itself sits inside that panel, so the description is kept.
            return !$(this).closest('.wpjp-ai-tools').length
                && (this.name === 'description' || !$(this).closest('.wjportal-ai-app-wrapper').length);
        }).each(function () {
            var $el = $(this);
            var name = $el.attr('name') || '';
            var type = ($el.attr('type') || '').toLowerCase();
            if (!name || $el.is(':disabled') || ['hidden', 'password', 'file', 'submit', 'button'].indexOf(type) !== -1) {
                return;
            }
            if (/(nonce|token|captcha|termsandconditions|form_request|wpjobportalpageid)/i.test(name)) {
                return;
            }
            var val;
            if ($el.is('select')) {
                // A placeholder option has an empty value, in every language.
                val = $el.find('option:selected').filter(function () { return $(this).val() !== ''; }).map(function () { return $(this).text(); }).get().join(', ');
            } else if (type === 'checkbox' || type === 'radio') {
                if (!$el.is(':checked')) {
                    return;
                }
                val = $.trim($el.next('label').text() || $el.parent().text()) || 'yes';
            } else {
                val = $el.val();
            }
            if (name === 'description' && typeof tinymce !== 'undefined' && tinymce.get('description') && !tinymce.get('description').isHidden()) {
                val = tinymce.get('description').getContent({format: 'text'});
            }
            val = $.trim(String(val || ''));
            if (!val || /^(select|choose)\b/i.test(val)) {
                return;
            }
            var $label = $el.closest('.wjportal-form-row, .wpjobportal-form-wrapper, tr').find('.wjportal-form-title, .wpjobportal-form-title, label').first().clone();
            // Screen-reader extras ("required") and the required star are not part of the name.
            $label.find('.screen-reader-text, .wpjp-ds-visually-hidden, .wpjp-ds-required, .wjportal-sr-only, .sr-only, .error-msg, [aria-hidden="true"]').remove();
            var label = $.trim($label.text());
            if (!label) {
                label = name.replace(/[\[\]_-]+/g, ' ');
            }
            label = $.trim(label.replace(/\*/g, ''));
            if (seen[label + '|' + val]) {
                return;
            }
            seen[label + '|' + val] = true;
            out.push({label: label.substring(0, 80), value: val.substring(0, 3000), name: name});
        });
        return out.slice(0, 40);
    }

    function run($bar, task, text) {
        var $result = $bar.find('.wpjp-ai-tools-result');
        var $out = $bar.find('.wpjp-ai-tools-out');
        var $buttons = $bar.find('button');
        $buttons.prop('disabled', true);
        show($result, true);
        $out.removeClass('is-error').text(wpjpAiTools.working);
        var data = {
            action: 'wpjobportal_ajax',
            wpjobportalme: 'zywrap',
            task: 'executeAiTask',
            ai_task: task,
            _wpnonce: wpjpAiTools.nonce,
            context: JSON.stringify(context($bar)),
            text: text || ''
        };
        if ($bar.hasClass('wpjp-ai-ctx-form')) {
            data.fields = JSON.stringify(formFields($bar));
        }
        $.post(wpjpAiTools.ajaxurl, data).done(function (r) {
            if (r && r.success && r.data && typeof r.data.output === 'string') {
                $out.text(r.data.output);
            } else {
                $out.addClass('is-error').text(wpjpAiTools.failed + ' ' + ((r && r.data && r.data.message) ? r.data.message : wpjpAiTools.server));
            }
        }).fail(function () {
            $out.addClass('is-error').text(wpjpAiTools.server);
        }).always(function () {
            $buttons.prop('disabled', false);
        });
    }

    $(document).on('click', '.wpjp-ai-tool', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var $bar = $btn.closest('.wpjp-ai-tools');
        var task = classValue(this, 'wpjp-ai-task-');
        var input = (wpjpAiTools.inputs && wpjpAiTools.inputs[task]) ? wpjpAiTools.inputs[task] : null;
        $bar.find('.wpjp-ai-tool').removeClass('is-active');
        $btn.addClass('is-active');
        var $box = $bar.find('.wpjp-ai-tools-input');
        if (input) {
            show($box, true);
            $box.data('task', task).data('mode', input.mode);
            $box.find('textarea').attr('placeholder', input.placeholder || '').focus();
            return;
        }
        show($box, false);
        run($bar, task, '');
    });

    $(document).on('click', '.wpjp-ai-tools-run', function (e) {
        e.preventDefault();
        var $bar = $(this).closest('.wpjp-ai-tools');
        var $box = $bar.find('.wpjp-ai-tools-input');
        var text = $.trim($box.find('textarea').val());
        if ($box.data('mode') === 'required' && !text) {
            show($bar.find('.wpjp-ai-tools-result'), true);
            $bar.find('.wpjp-ai-tools-out').addClass('is-error').text(wpjpAiTools.needText);
            return;
        }
        run($bar, $box.data('task'), text);
    });

    $(document).on('click', '.wpjp-ai-tools-copy', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var text = $btn.closest('.wpjp-ai-tools').find('.wpjp-ai-tools-out').text();
        var done = function () {
            var was = $btn.text();
            $btn.text(wpjpAiTools.copied);
            setTimeout(function () { $btn.text(was); }, 1500);
        };
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(done);
        } else {
            var $t = $('<textarea>').val(text).css({position: 'fixed', top: '-1000px'}).appendTo('body');
            $t[0].select();
            try { document.execCommand('copy'); done(); } catch (err) {}
            $t.remove();
        }
    });

    $(document).on('click', '.wpjp-ai-tools-close', function (e) {
        e.preventDefault();
        var $bar = $(this).closest('.wpjp-ai-tools');
        show($bar.find('.wpjp-ai-tools-result, .wpjp-ai-tools-input'), false);
        $bar.find('.wpjp-ai-tool').removeClass('is-active');
    });
})(jQuery);
