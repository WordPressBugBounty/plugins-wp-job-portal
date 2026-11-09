(function () {
    'use strict';

    function enhanceTables(scope) {
        var selector = [
            '.wpjobportal-recruit-team table',
            '.wpjobportal-recruit-insights table',
            '.wpjobportal-recruit-candidates table',
            '.wpjp-native-recruit-view table',
            '.wpjp-native-application-workflow table',
            '.wpjobportal-candidate-portal table'
        ].join(',');
        Array.prototype.forEach.call(scope.querySelectorAll(selector), function (table) {
            var headings = Array.prototype.map.call(table.querySelectorAll('thead th'), function (heading) {
                return heading.textContent.trim();
            });
            if (!headings.length) {
                return;
            }
            table.classList.add('wpjobportal-recruit-responsive-table');
            Array.prototype.forEach.call(table.querySelectorAll('tbody tr'), function (row) {
                var cells = row.querySelectorAll('td');
                if (cells.length === 1 && cells[0].hasAttribute('colspan')) {
                    row.classList.add('wpjobportal-recruit-empty-row');
                    return;
                }
                Array.prototype.forEach.call(cells, function (cell, index) {
                    if (headings[index]) {
                        cell.setAttribute('data-label', headings[index]);
                    }
                });
            });
        });
    }

    function mountSidebarItem(shell) {
        var template = document.getElementById('wpjobportal-recruit-native-sidebar-template');
        var menu = shell.querySelector('.wpjobportaladmin-sidebar-menu');
        if (!template || !menu || menu.querySelector('[data-wpjobportal-recruit-sidebar]')) {
            return;
        }
        var item = template.content.cloneNode(true);
        var helpLink = menu.querySelector('a[href*="wpjobportallt=help"]');
        var helpItem = helpLink ? helpLink.closest('li.treeview') : null;
        menu.insertBefore(item, helpItem);
        template.remove();
    }
    function mountAdminShell() {
        var recruit = document.querySelector('body.wp-admin .wpjobportal-recruit-team, body.wp-admin .wpjobportal-recruit-pipeline, body.wp-admin .wpjobportal-recruit-workspace, body.wp-admin .wpjobportal-recruit-insights, body.wp-admin .wpjobportal-recruit-automation');
        var template = document.getElementById('wpjobportal-recruit-native-shell-template');
        if (!recruit || !template || recruit.closest('.wpjobportal-recruit-native-shell')) {
            return;
        }
        var fragment = template.content.cloneNode(true);
        var shell = fragment.querySelector('.wpjobportal-recruit-native-shell');
        var content = fragment.querySelector('[data-wpjobportal-recruit-content]');
        mountSidebarItem(shell);
        recruit.parentNode.insertBefore(shell, recruit);
        recruit.classList.remove('wrap');
        content.appendChild(recruit);
        liftIntro(shell, '.wpjobportal-recruit-native-content :is(.wjp-auto-heading, .wjp-insights-heading)');
        template.remove();
    }

    /*
     * W-19: as on every core screen, the page's own actions sit in the title
     * row and its one-line description sits under the title. The suite draws
     * both in an intro block at the top of its content; move them up.
     */
    function liftIntro(shell, selector) {
        var head = shell.querySelector('.wjpl-head');
        var intro = shell.querySelector(selector);
        if (!head || !intro) {
            return;
        }
        var acts = head.querySelector('.wjpl-acts');
        Array.prototype.slice.call(intro.children).forEach(function (child) {
            if (child.querySelector('h1')) {
                var text = child.querySelector('p');
                if (text) {
                    var sub = document.createElement('p');
                    sub.className = 'wjpl-sub';
                    sub.textContent = text.textContent;
                    head.parentNode.insertBefore(sub, head.nextSibling);
                }
                child.remove();
                return;
            }
            if (!acts) {
                return;
            }
            Array.prototype.forEach.call(child.matches('a, button') ? [child] : child.querySelectorAll('button, a.button, input[type=submit]'), function (button) {
                button.classList.remove('button', 'button-primary', 'wjportal-form-btn', 'wjportal-search-btn', 'wpjobportal-marketplace-hero-link');
                button.classList.add('wjpl-btn', 'wjpl-btn-pri');
            });
            acts.appendChild(child);
        });
        if (!intro.children.length) {
            intro.remove();
        }
    }

    function init() {
        mountAdminShell();
        enhanceTables(document);
    }

    init();
    if ('loading' === document.readyState) {
        document.addEventListener('DOMContentLoaded', init);
    }
}());
