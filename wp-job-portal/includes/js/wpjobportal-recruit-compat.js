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
        template.remove();
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
