(function () {
    'use strict';

    function enhanceTables(scope) {
        var tables = scope.querySelectorAll('.wpjobportal-marketplace-table-wrap table, .wpjp-marketplace-commerce-table table');
        Array.prototype.forEach.call(tables, function (table) {
            var headings = Array.prototype.map.call(table.querySelectorAll('thead th'), function (heading) {
                return heading.textContent.trim();
            });
            table.classList.add('wpjobportal-marketplace-responsive-table');
            Array.prototype.forEach.call(table.querySelectorAll('tbody tr'), function (row) {
                var cells = row.querySelectorAll('td');
                if (cells.length === 1 && cells[0].hasAttribute('colspan')) {
                    row.classList.add('wpjobportal-marketplace-empty-row');
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

    function mountAdminShell() {
        var marketplace = document.querySelector('body.wp-admin .wpjobportal-marketplace-admin');
        var template = document.getElementById('wpjobportal-marketplace-native-shell-template');
        if (!marketplace || !template || marketplace.closest('.wpjobportal-marketplace-native-shell')) {
            return;
        }

        var fragment = template.content.cloneNode(true);
        var shell = fragment.querySelector('.wpjobportal-marketplace-native-shell');
        var content = fragment.querySelector('[data-wpjobportal-marketplace-content]');
        marketplace.parentNode.insertBefore(shell, marketplace);
        marketplace.classList.remove('wrap');
        content.appendChild(marketplace);
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
