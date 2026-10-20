/**
 * WP Job Portal accessible form enhancement layer.
 *
 * Preserves legacy markup and validation while adding durable label, help,
 * error, required-field, autocomplete, and mobile-input semantics.
 */
(function () {
    'use strict';

    var fieldSequence = 0;
    var errorSequence = 0;
    var rowSelector = '.wjportal-form-row, .wpjobportal-form-wrapper, .wpjobportal-form-field';
    var controlSelector = 'input:not([type="hidden"]):not([type="submit"]):not([type="button"]):not([type="reset"]), select, textarea';
    var observer = null;
    var applying = false;

    function uniqueId(prefix) {
        fieldSequence += 1;
        return prefix + fieldSequence;
    }

    /*
     * classList.add() and setAttribute() queue a MutationObserver record even
     * when the resulting value is identical, so every write below is guarded to
     * only touch the DOM when something actually changes.
     */
    function addClass(element, name) {
        if (element && !element.classList.contains(name)) {
            element.classList.add(name);
        }
    }

    function setAttr(element, name, value) {
        if (element && element.getAttribute(name) !== value) {
            element.setAttribute(name, value);
        }
    }

    function tokens(value) {
        return (value || '').trim().split(/\s+/).filter(Boolean);
    }

    function appendToken(element, attribute, token) {
        if (!element || !token) {
            return;
        }
        var values = tokens(element.getAttribute(attribute));
        if (values.indexOf(token) === -1) {
            values.push(token);
            element.setAttribute(attribute, values.join(' '));
        }
    }

    function removeToken(element, attribute, token) {
        if (!element || !token) {
            return;
        }
        var current = tokens(element.getAttribute(attribute));
        var values = current.filter(function (value) {
            return value !== token;
        });
        if (values.length === current.length) {
            return;
        }
        if (values.length) {
            element.setAttribute(attribute, values.join(' '));
        } else {
            element.removeAttribute(attribute);
        }
    }

    function isRequired(control, title) {
        var validation = control.getAttribute('data-validation') || '';
        return control.required
            || /(^|\s)required(\s|$)/i.test(validation)
            || control.getAttribute('aria-required') === 'true'
            || !!(title && title.querySelector('.required-notifier, .wpjp-ds-required'));
    }

    function inferInputHints(control) {
        if (!control || control.tagName !== 'INPUT') {
            return;
        }
        var type = (control.type || '').toLowerCase();
        var name = (control.name || control.id || '').toLowerCase();

        if (!control.getAttribute('autocomplete')) {
            if (type === 'email' || name.indexOf('email') !== -1) {
                control.setAttribute('autocomplete', 'email');
            } else if (name.indexOf('first_name') !== -1 || name.indexOf('user_first') !== -1) {
                control.setAttribute('autocomplete', 'given-name');
            } else if (name.indexOf('last_name') !== -1 || name.indexOf('user_last') !== -1) {
                control.setAttribute('autocomplete', 'family-name');
            } else if (name.indexOf('phone') !== -1 || name.indexOf('mobile') !== -1) {
                control.setAttribute('autocomplete', 'tel');
            } else if (name.indexOf('website') !== -1 || name.indexOf('url') !== -1) {
                control.setAttribute('autocomplete', 'url');
            }
        }

        if (!control.getAttribute('inputmode')) {
            if (type === 'email' || name.indexOf('email') !== -1) {
                control.setAttribute('inputmode', 'email');
            } else if (name.indexOf('phone') !== -1 || name.indexOf('mobile') !== -1) {
                control.setAttribute('inputmode', 'tel');
            } else if (name.indexOf('website') !== -1 || name.indexOf('url') !== -1) {
                control.setAttribute('inputmode', 'url');
            } else if (/(^|_)(salary|amount|number|noofjobs|zip|postal)(_|$)/.test(name)) {
                control.setAttribute('inputmode', 'decimal');
            }
        }
    }

    function findHelp(row) {
        return row.querySelector('.wjportal-form-help-txt, .wpjobportal-form-description, .wpjobportal-form-help-text, .wpjp-ds-help');
    }

    function enhanceChosen(control, labelId, descriptionId) {
        var chosen = control.nextElementSibling;
        if (!chosen || !chosen.classList.contains('chosen-container')) {
            return;
        }
        setAttr(chosen, 'aria-labelledby', labelId);
        if (descriptionId) {
            setAttr(chosen, 'aria-describedby', descriptionId);
        }
        var search = chosen.querySelector('input[type="text"]');
        if (search) {
            setAttr(search, 'aria-labelledby', labelId);
            if (descriptionId) {
                setAttr(search, 'aria-describedby', descriptionId);
            }
        }
    }

    function enhanceRow(row) {
        if (!row || !row.querySelectorAll) {
            return;
        }
        var title = row.querySelector('.wjportal-form-title, .wpjobportal-form-title, .wpjobportal-form-label');
        var value = row.querySelector('.wjportal-form-value, .wpjobportal-form-value, .wpjobportal-form-val') || row;
        var controls = Array.prototype.slice.call(value.querySelectorAll(controlSelector));
        if (!title) {
            return;
        }

        if (!title.id) {
            title.id = uniqueId('wpjp-form-label-');
        }
        var help = findHelp(row);
        if (help && !help.id) {
            help.id = uniqueId('wpjp-form-help-');
        }

        addClass(row, 'wpjp-ds-field');
        addClass(title, 'wpjp-ds-label');
        if (!controls.length) {
            return;
        }
        setAttr(value, 'role', 'group');
        setAttr(value, 'aria-labelledby', title.id);

        controls.forEach(function (control, index) {
            if (!control.id) {
                control.id = uniqueId('wpjp-form-control-');
            }
            if (controls.length === 1 && title.tagName === 'LABEL') {
                setAttr(title, 'for', control.id);
            } else {
                setAttr(control, 'aria-labelledby', title.id);
            }
            if (help) {
                appendToken(control, 'aria-describedby', help.id);
            }
            if (isRequired(control, title)) {
                setAttr(control, 'aria-required', 'true');
            }
            if (control.type !== 'checkbox' && control.type !== 'radio') {
                addClass(control, 'wpjp-ds-control');
            }
            inferInputHints(control);
            enhanceChosen(control, title.id, help ? help.id : '');

            if (index > 0 && controls.length > 1 && !control.getAttribute('aria-label') && !control.getAttribute('aria-labelledby')) {
                setAttr(control, 'aria-labelledby', title.id);
            }
        });
    }

    function nearestControl(error) {
        var parent = error.parentElement;
        if (!parent) {
            return null;
        }
        return parent.querySelector(controlSelector)
            || (parent.previousElementSibling && parent.previousElementSibling.matches(controlSelector) ? parent.previousElementSibling : null);
    }

    function syncErrorsIn(root) {
        var scope = root && root.querySelectorAll ? root : document;
        var invalidControls = scope.querySelectorAll('input.error, select.error, textarea.error, .has-error input, .has-error select, .has-error textarea');
        Array.prototype.forEach.call(invalidControls, function (control) {
            if (control.type === 'hidden') {
                return;
            }
            setAttr(control, 'aria-invalid', 'true');
            var parent = control.parentElement;
            var error = parent ? parent.querySelector('.form-error, .wpjp-ds-error') : null;
            if (error) {
                if (!error.id) {
                    errorSequence += 1;
                    error.id = 'wpjp-form-error-' + errorSequence;
                }
                setAttr(error, 'role', 'alert');
                setAttr(error, 'aria-live', 'polite');
                appendToken(control, 'aria-describedby', error.id);
            }
        });

        var errors = scope.querySelectorAll('.form-error, .wpjp-ds-error');
        Array.prototype.forEach.call(errors, function (error) {
            if (!error.textContent.trim()) {
                return;
            }
            if (!error.id) {
                errorSequence += 1;
                error.id = 'wpjp-form-error-' + errorSequence;
            }
            setAttr(error, 'role', 'alert');
            setAttr(error, 'aria-live', 'polite');
            var control = nearestControl(error);
            if (control) {
                setAttr(control, 'aria-invalid', 'true');
                appendToken(control, 'aria-describedby', error.id);
            }
        });

        var validControls = scope.querySelectorAll('input.valid, select.valid, textarea.valid');
        Array.prototype.forEach.call(validControls, function (control) {
            control.removeAttribute('aria-invalid');
            tokens(control.getAttribute('aria-describedby')).forEach(function (token) {
                if (token.indexOf('wpjp-form-error-') === 0 && !document.getElementById(token)) {
                    removeToken(control, 'aria-describedby', token);
                }
            });
        });
    }

    function enhanceIn(root) {
        var scope = root && root.querySelectorAll ? root : document;
        var rows = [];
        if (scope.matches && scope.matches(rowSelector)) {
            rows.push(scope);
        }
        rows = rows.concat(Array.prototype.slice.call(scope.querySelectorAll(rowSelector)));
        rows.forEach(enhanceRow);

        var forms = scope.querySelectorAll('form');
        Array.prototype.forEach.call(forms, function (form) {
            if (form.closest('#wpjobportal-wrapper, #wpjobportaladmin-wrapper, #wpjobportal-admin-wrapper, .wjportal-main-up-wrapper, .wpjobportal-admin-wrapper')) {
                addClass(form, 'wpjp-ds-form');
            }
        });
        syncErrorsIn(scope);
    }

    function connectObserver() {
        if (!observer) {
            return;
        }
        observer.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['class']
        });
    }

    /*
     * Every write this script performs lands on an element the observer is
     * watching, so an unguarded pass re-triggers itself and the record queue
     * grows without bound. Detaching around the write keeps our own changes out
     * of the queue; takeRecords() drops anything queued before the detach.
     */
    function applyWithoutFeedback(work) {
        if (applying) {
            work();
            return;
        }
        applying = true;
        if (observer) {
            observer.disconnect();
        }
        try {
            work();
        } finally {
            if (observer) {
                observer.takeRecords();
                connectObserver();
            }
            applying = false;
        }
    }

    function enhance(root) {
        applyWithoutFeedback(function () {
            enhanceIn(root);
        });
    }

    function syncErrors(root) {
        applyWithoutFeedback(function () {
            syncErrorsIn(root);
        });
    }

    /*
     * A complaint about a field nobody can see.
     *
     * The validator marks a control invalid and puts its message beside it.
     * When that control is hidden - a select Chosen has replaced, or a field
     * the template renders as a hidden input - the message is hidden with it,
     * so pressing Save appears to do nothing at all. That is the single most
     * common way a portal form "stops working".
     *
     * So: say it at the top of the form, where it can be read, and name the
     * fields it is about.
     */
    function visibleStandIn(control) {
        var sibling = control.nextElementSibling;
        if (sibling && sibling.classList.contains('chosen-container')) {
            return sibling;
        }
        return null;
    }

    function isOnScreen(element) {
        return !!(element && (element.offsetWidth || element.offsetHeight || element.getClientRects().length));
    }

    function fieldLabel(control) {
        var described = control.getAttribute('aria-labelledby');
        var label = described ? document.getElementById(described.split(/\s+/)[0]) : null;
        if (!label) {
            var row = control.closest ? control.closest(rowSelector) : null;
            label = row ? row.querySelector('label, .wjportal-form-title, .wpjp-ds-label') : null;
        }
        var text = label ? label.textContent : (control.name || '');
        return text.replace(/\s+/g, ' ').replace(/[:*]\s*$/, '').trim();
    }

    function summariseHiddenErrors(form) {
        var invalid = form.querySelectorAll('[aria-invalid="true"], input.error, select.error, textarea.error');
        var unseen = [];
        for (var i = 0; i < invalid.length; i++) {
            if (isOnScreen(invalid[i]) || isOnScreen(visibleStandIn(invalid[i]))) {
                continue;
            }
            var name = fieldLabel(invalid[i]);
            if (name && unseen.indexOf(name) === -1) {
                unseen.push(name);
            }
        }

        var summary = form.querySelector('.wpjp-hidden-error-summary');
        if (!unseen.length) {
            if (summary) {
                summary.hidden = true;
            }
            return;
        }
        if (!summary) {
            summary = document.createElement('div');
            summary.className = 'wpjp-hidden-error-summary';
            summary.setAttribute('role', 'alert');
            summary.setAttribute('tabindex', '-1');
            form.insertBefore(summary, form.firstChild);
        }
        summary.hidden = false;
        summary.textContent = l10n('needed', 'This form cannot be sent yet:') + ' ' + unseen.join(', ');
        if (typeof summary.scrollIntoView === 'function') {
            summary.scrollIntoView({behavior: 'smooth', block: 'center'});
        }
        summary.focus();
    }

    function focusFirstError(form) {
        window.setTimeout(function () {
            syncErrors(form);
            summariseHiddenErrors(form);
            var first = form.querySelector('[aria-invalid="true"], input.error, select.error, textarea.error');
            /*
             * A searchable select is hidden behind its Chosen container, and
             * focusing something with display:none does nothing at all - the
             * form would refuse to send with the cursor nowhere. Hand the
             * focus to the control the person can actually see.
             */
            if (first && first.hasAttribute && first.hasAttribute('data-wpjp-searchable')) {
                var container = first.nextElementSibling;
                if (container && container.classList.contains('chosen-container')) {
                    var opener = container.querySelector('input[type="text"], a.chosen-single');
                    if (opener) {
                        first = opener;
                    }
                }
            }
            if (first && typeof first.focus === 'function') {
                first.focus({preventScroll: true});
                if (typeof first.scrollIntoView === 'function') {
                    first.scrollIntoView({behavior: 'smooth', block: 'center'});
                }
            }
        }, 0);
    }

    /*
     * Collapse a batch of records into the smallest set of scopes to re-scan.
     * Anything that is not inside a form row falls back to a single document
     * pass rather than one pass per record.
     */
    function collectScopes(mutations) {
        var scopes = [];
        for (var i = 0; i < mutations.length; i++) {
            var target = mutations[i].target;
            if (!target || target.nodeType !== 1) {
                return [document];
            }
            var row = target.closest ? target.closest(rowSelector) : null;
            if (!row) {
                return [document];
            }
            if (scopes.indexOf(row) === -1) {
                scopes.push(row);
            }
        }
        return scopes;
    }

    function l10n(key, fallback) {
        var strings = window.wpjobportalFormsL10n;
        return (strings && strings[key]) ? strings[key] : fallback;
    }

    /*
     * A form that has been sent looks exactly like a form that has not. On a
     * slow upload - a CV is the common case - people press Apply again, and
     * the second press either duplicates the application or races the first.
     * The button says what it is doing, and the form refuses a second send.
     *
     * The button is not disabled: a disabled submit control is left out of the
     * submitted data, which would change what the server receives.
     */
    function markPending(form, submitter) {
        if (form.getAttribute('data-wpjp-submitting') === '1') {
            return false;
        }
        form.setAttribute('data-wpjp-submitting', '1');
        form.setAttribute('aria-busy', 'true');

        var button = submitter;
        if (!button || !form.contains(button)) {
            button = form.querySelector('input[type="submit"], button[type="submit"]');
        }
        if (!button) {
            return true;
        }

        var label = l10n('working', 'Working…');
        if (button.name === 'save' && form.querySelector('input[name="jobid"], input[name="wpjobportalid-jobid"]')) {
            label = l10n('applying', 'Applying…');
        }

        addClass(button, 'wpjp-is-pending');
        setAttr(button, 'aria-busy', 'true');
        if (button.tagName === 'INPUT') {
            button.value = label;
        } else {
            button.textContent = label;
        }
        return true;
    }

    /*
     * "Apply On This Job" at the top of a job is a link to the form further
     * down the page. Left to the browser it jumps, and on a short job it does
     * not move at all - the press reads as a button that did nothing. Move the
     * page and the keyboard focus to the first thing to fill in.
     */
    function focusApplyForm(target) {
        var field = target.querySelector(controlSelector + ', input[type="submit"], button');
        if (!field || typeof field.focus !== 'function') {
            field = target;
            setAttr(field, 'tabindex', '-1');
        }
        /*
         * Focus first and let it do the scrolling: that always moves the page,
         * where a smooth scrollIntoView can be ignored outright. The smooth
         * call afterwards only refines where the form sits on screen.
         */
        field.focus();
        if (typeof target.scrollIntoView === 'function') {
            target.scrollIntoView({behavior: 'smooth', block: 'start'});
        }
    }

    /*
     * A select with 238 options in it - job categories, on a site with the
     * sample data - is a scroll bar and a memory test. The plugin already
     * ships Chosen, loads it on every portal page, and both looks already
     * style .chosen-container; nothing was ever asking for it. A long list
     * gets a search field, a short one is left alone: a native select is
     * better than a scripted one whenever it fits on screen.
     */
    var longSelectThreshold = 20;
    var portalScopes = '#wpjobportal-wrapper, #wpjobportaladmin-wrapper, #wpjobportal-admin-wrapper, .wjportal-main-up-wrapper, .wpj-jp-main-up-wrapper';

    /*
     * Below this width the stylesheet has already decided: a native select is
     * more reliable than a scripted one on a phone, so it hides the Chosen
     * container and forces the real select back. Building one anyway started a
     * tug of war - Chosen hid the select, the stylesheet un-hid it and hid
     * Chosen, and what was left was a 26px-wide control in a row that had
     * collapsed to nothing. One decision, taken in one place.
     */
    var searchableFrom = '(min-width: 783px)';

    function enhanceLongSelects(root) {
        var $ = window.jQuery;
        if (!$ || !$.fn || !$.fn.chosen) {
            return;
        }
        if (window.matchMedia && !window.matchMedia(searchableFrom).matches) {
            return;
        }
        var selects = root.querySelectorAll('select:not([multiple]):not([data-wpjp-searchable])');
        for (var i = 0; i < selects.length; i++) {
            var select = selects[i];
            if (select.options.length < longSelectThreshold) {
                continue;
            }
            var sibling = select.nextElementSibling;
            if (sibling && sibling.classList.contains('chosen-container')) {
                continue;
            }
            if (!select.closest || !select.closest(portalScopes)) {
                continue;
            }
            setAttr(select, 'data-wpjp-searchable', '1');
            /*
             * Chosen mirrors on a class, not on the page direction, and it
             * reads that class once when it builds the control. Ask for the
             * direction that is actually in force on this field - a site can
             * set dir on <html>, on <body>, or on the portal wrapper alone.
             */
            if (window.getComputedStyle(select).direction === 'rtl') {
                addClass(select, 'chosen-rtl');
            }
            $(select).chosen({
                width: '100%',
                search_contains: true,
                no_results_text: l10n('noMatch', 'Nothing matches')
            });
        }
    }

    /*
     * The form row of a Chosen control, marked so the stylesheet can let the
     * list hang out of it. The add-job form clips its rows; the stylesheet's
     * :has() release is lost on browsers without :has() (Firefox before 121
     * drops the whole rule), and the list opened with nothing in it. A class
     * works everywhere. Covers controls built before this runs (the scan) and
     * after it (Chosen's own chosen:ready event), whoever built them.
     */
    function markChosenRow(container) {
        var row = container && container.closest ? container.closest('.wjportal-form-value') : null;
        addClass(row, 'wpjp-has-chosen');
    }

    function markChosenRows(root) {
        var containers = root.querySelectorAll('.chosen-container');
        for (var i = 0; i < containers.length; i++) {
            markChosenRow(containers[i]);
        }
    }

    function init() {
        if (window.jQuery) {
            window.jQuery(document).on('chosen:ready', function (event) {
                var select = event.target;
                markChosenRow(select && select.nextElementSibling && select.nextElementSibling.classList.contains('chosen-container') ? select.nextElementSibling : select);
            });
        }
        applyWithoutFeedback(function () {
            enhanceLongSelects(document);
            markChosenRows(document);
        });
        enhance(document);

        document.addEventListener('click', function (event) {
            var link = event.target && event.target.closest ? event.target.closest('a[href^="#"]') : null;
            if (!link || event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey) {
                return;
            }
            var id = link.getAttribute('href').slice(1);
            if (id.indexOf('job-apply-form') === -1) {
                return;
            }
            var target = document.getElementById(id);
            if (!target) {
                return;
            }
            event.preventDefault();
            focusApplyForm(target);
        });

        document.addEventListener('submit', function (event) {
            var form = event.target;
            if (!form || !form.matches || !form.matches('form') || event.defaultPrevented) {
                return;
            }
            if (form.hasAttribute('data-wpjp-no-pending')) {
                return;
            }
            applyWithoutFeedback(function () {
                if (!markPending(form, event.submitter)) {
                    event.preventDefault();
                }
            });
        });

        document.addEventListener('submit', function (event) {
            if (event.target && event.target.matches('form')) {
                focusFirstError(event.target);
            }
        }, true);

        document.addEventListener('input', function (event) {
            var row = event.target && event.target.closest ? event.target.closest(rowSelector) : null;
            if (row) {
                window.setTimeout(function () { syncErrors(row); }, 0);
            }
        }, true);

        if ('MutationObserver' in window) {
            var pending = false;
            observer = new MutationObserver(function (mutations) {
                if (applying || pending) {
                    return;
                }
                pending = true;
                var scopes = collectScopes(mutations);
                window.requestAnimationFrame(function () {
                    pending = false;
                    applyWithoutFeedback(function () {
                        for (var i = 0; i < scopes.length; i++) {
                            enhanceIn(scopes[i]);
                        }
                    });
                });
            });
            connectObserver();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
