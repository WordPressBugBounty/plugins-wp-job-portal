/**
 * Shows the package selector on a WooCommerce product when the seller ticks
 * "WP Job Portal".
 *
 * The selector's wrapper is markup we add ourselves, classed
 * show_if_wpjobportalsubscription and shipped with WordPress's `hidden` class
 * so it does not flash before anything decides about it. WooCommerce only
 * toggles show_if_<product type> - the types it is told about, plus the
 * virtual and downloadable checkboxes it hardcodes - and a product_type_options
 * entry like ours is neither. So nothing ever revealed that wrapper, and the
 * package dropdown was unreachable no matter what the seller ticked.
 */
(function ($) {
    'use strict';

    var CHECKBOX = '#_wpjobportalsubscription';
    var SHOW = '.show_if_wpjobportalsubscription';
    var HIDE = '.hide_if_wpjobportalsubscription';

    /**
     * The packages only mean anything on a product that can carry them, and
     * the checkbox is itself only offered on those. Reading its visibility
     * rather than the product type keeps this correct without naming any
     * type here: whoever decides where the checkbox belongs decides this too.
     * It also covers stale meta - a product saved as a subscription and later
     * changed - which would otherwise leave an empty options group behind.
     */
    function wpjobportalPackagesWanted() {
        var $checkbox = $(CHECKBOX);
        return $checkbox.is(':checked') && $checkbox.closest('label').is(':visible');
    }

    function wpjobportalApply() {
        var wanted = wpjobportalPackagesWanted();
        // Drop the shipped `hidden` class once, so from here on the only thing
        // deciding is the inline display these calls set.
        $(SHOW).removeClass('hidden').toggle(wanted);
        $(HIDE).removeClass('hidden').toggle(!wanted);
    }

    $(function () {
        if (!$(CHECKBOX).length) {
            return;
        }

        wpjobportalApply();
        $(document).on('change', CHECKBOX, wpjobportalApply);

        /*
         * Changing the product type makes WooCommerce re-run its own show and
         * hide pass, which resets every panel it knows about and leaves ours
         * where it was. It announces the change once that pass has finished,
         * so this is the point at which our answer is worth recomputing - the
         * checkbox may have just become reachable, or stopped being.
         */
        $(document.body).on('woocommerce-product-type-change', wpjobportalApply);
    });
})(jQuery);
