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
        liftIntro(shell, '.wpjobportal-marketplace-native-content .wpjobportal-marketplace-hero');
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
