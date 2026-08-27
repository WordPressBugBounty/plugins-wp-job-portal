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

    function focusFirstError(form) {
        window.setTimeout(function () {
            syncErrors(form);
            var first = form.querySelector('[aria-invalid="true"], input.error, select.error, textarea.error');
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

    function init() {
        enhance(document);

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
