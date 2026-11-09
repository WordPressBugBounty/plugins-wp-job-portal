/* WP Job Portal admin lists (design 4): select all, bulk bar, row actions that post the bulk form, delete dialog. */
(function ($) {
    'use strict';

    // WordPress prints admin notices above the plugin's own layout, where its left
    // menu covers them; move them into the content column, under the top bar.
    $(function () {
        var $data = $('#wpjobportaladmin-wrapper #wpjobportaladmin-data');
        if (!$data.length || !$data.find('.wjpl-slim').length) {
            return;
        }
        // WordPress's own "please update" notice is marked inline too
        var $notices = $('#wpbody-content').children('.notice, .updated, .error').not('.hidden').filter(function () {
            return !$(this).hasClass('inline') || $(this).hasClass('update-nag');
        });
        if (!$notices.length) {
            return;
        }
        var $slot = $('<div class="wjpl-wp-notices"></div>');
        var $top = $data.find('.wjpl-slim').first().closest('.wjpl');
        if ($top.length && $top.children('.wjpl-slim').length) {
            $top.children('.wjpl-slim').first().after($slot);
        } else {
            $data.prepend($slot);
        }
        $slot.append($notices);
    });

    $(function () {
        var $list = $('.wjpl');
        if (!$list.length) {
            return;
        }
        var $form = $list.find('form.wjpl-bulkform');
        var $picks = function () { return $form.find('input.wjpl-pick'); };

        // Filters and sort reload the list as soon as they change. Empty fields stay
        // out of the address, so a filtered list has a short link.
        $list.on('submit', 'form.wjpl-filters', function () {
            $(this).find(':input[name]').filter(function () { return this.value === ''; }).prop('disabled', true);
        });
        $list.on('change', '[data-wjpl-autosubmit]', function () {
            $(this.form).trigger('submit');
        });

        function refresh() {
            var n = $picks().filter(':checked').length;
            $picks().each(function () {
                $(this).closest('tr').toggleClass('picked', this.checked);
            });
            $list.find('.wjpl-selbar').prop('hidden', n === 0);
            $list.find('.wjpl-tb1').prop('hidden', n > 0);
            $list.find('.wjpl-selcount').text(n);
            var $all = $list.find('input.wjpl-all');
            $all.prop('checked', n > 0 && n === $picks().length);
            $all.prop('indeterminate', n > 0 && n < $picks().length);
        }

        $list.on('change', 'input.wjpl-all', function () {
            $picks().prop('checked', this.checked);
            refresh();
        });
        $list.on('change', 'input.wjpl-pick', refresh);
        $list.on('click', '.wjpl-clear', function () {
            $picks().prop('checked', false);
            refresh();
        });

        function submit(op, ids) {
            if (ids) {
                $picks().each(function () {
                    this.checked = ids.indexOf(this.value) !== -1;
                });
            }
            $form.find('input[name="bulkop"]').val(op);
            // A list whose actions are separate tasks names them on the form (data-task-approve="approve").
            var task = $form.data('task-' + op);
            if (task) {
                $form.find('input[name="task"]').val(task);
            }
            $form.trigger('submit');
        }

        var dialog = document.getElementById('wjpl-delete');
        var pendingIds = null;
        function askDelete(ids) {
            pendingIds = ids;
            var n = ids ? ids.length : $picks().filter(':checked').length;
            if (!dialog || typeof dialog.showModal !== 'function') {
                if (window.confirm($list.data('delete-confirm'))) {
                    submit('delete', ids);
                }
                return;
            }
            $(dialog).find('.wjpl-dlg-n').text(n);
            $(dialog).find('input[name="wjpl-deleteall"]').prop('checked', false);
            dialog.showModal();
        }

        $list.on('click', '[data-wjpl-bulk]', function () {
            var op = $(this).data('wjpl-bulk');
            if (op === 'delete') {
                askDelete(null);
            } else {
                submit(op, null);
            }
        });
        $list.on('click', '[data-wjpl-row]', function (e) {
            e.preventDefault();
            var op = $(this).data('wjpl-row');
            var ids = [String($(this).data('id'))];
            if (op === 'delete') {
                askDelete(ids);
            } else {
                submit(op, ids);
            }
        });

        if (dialog) {
            $(dialog).on('click', '.wjpl-dlg-cancel', function () {
                dialog.close();
            });
            $(dialog).on('click', '.wjpl-dlg-ok', function () {
                $form.find('input[name="deleteall"]').val($(dialog).find('input[name="wjpl-deleteall"]').is(':checked') ? '1' : '0');
                dialog.close();
                submit('delete', pendingIds);
            });
        }

        refresh();
    });
})(jQuery);
