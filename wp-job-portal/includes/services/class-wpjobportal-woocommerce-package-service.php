<?php

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/** Idempotent WooCommerce fulfillment for the existing Credits package catalogue. */
class WPJOBPORTAL_WooCommerce_Package_Service {

    const PRODUCT_META = 'wpjobportal_packagepack_field';
    const FULFILLED_META = '_wpjobportal_package_fulfilled_quantities';
    const GRANTS_META = '_wpjobportal_package_grants';
    const STATUS_META = '_wpjobportal_package_fulfillment_status';
    const AUDIENCE_NOTICE_META = '_wpjobportal_package_audience_noticed';

    public static function register() {
        remove_action('woocommerce_payment_complete', 'wpjobportal_woocommerce_payment_complete');
        remove_action('woocommerce_order_status_completed', 'wpjobportal_woocommerce_payment_completed');
        remove_action('woocommerce_order_status_refunded', 'wpjobportal_woocommerce_payment_refunded');
        remove_action('woocommerce_order_status_cancelled', 'wpjobportal_woocommerce_payment_cancelled');

        add_action('woocommerce_payment_complete', array(__CLASS__, 'fulfill_order'), 30);
        add_action('woocommerce_order_status_processing', array(__CLASS__, 'fulfill_order'), 30);
        add_action('woocommerce_order_status_completed', array(__CLASS__, 'fulfill_order'), 30);
        add_action('woocommerce_order_status_refunded', array(__CLASS__, 'reverse_order'), 30);
        add_action('woocommerce_order_status_cancelled', array(__CLASS__, 'reverse_order'), 30);
        add_action('woocommerce_order_status_failed', array(__CLASS__, 'reverse_order'), 30);
        add_action('woocommerce_process_product_meta', array(__CLASS__, 'save_product_mapping'), 100);
        add_action('woocommerce_before_calculate_totals', array(__CLASS__, 'price_cart_items'), 30);
        add_filter('woocommerce_add_to_cart_validation', array(__CLASS__, 'refuse_wrong_audience'), 30, 3);
        add_filter('woocommerce_product_get_price', array(__CLASS__, 'quote_price'), 30, 2);
        add_filter('woocommerce_product_get_regular_price', array(__CLASS__, 'quote_regular_price'), 30, 2);
        add_filter('woocommerce_product_get_sale_price', array(__CLASS__, 'quote_sale_price'), 30, 2);
    }

    /**
     * Charge what the portal quoted.
     *
     * A package carries its own price and its own discount, and the Packages
     * screen advertises the result: "10 % OFF", 49$ struck through, 44.10$ as
     * the price. PayPal and Stripe both charge that figure - they ask
     * WPJOBPORTAL_Package_Price::final_price(), whose own docblock calls it
     * "the price to quote and to charge".
     *
     * WooCommerce did not. This route finds a product whose meta names the
     * package, drops it in the cart, and charges whatever price the product
     * carries - so a buyer was quoted 44.10 and billed 49.00, and the
     * discount was honoured on two gateways out of three.
     *
     * The price is set here rather than at add-to-cart, so it holds however
     * the product reached the cart: the portal's Buy Now, the shop page, a
     * saved link, a restored session.
     *
     * Left alone, deliberately: a package priced in a currency the shop does
     * not sell in. Converting is not this code's business, and quietly
     * writing 44.10 of one currency as 44.10 of another would be worse than
     * the fault it is fixing.
     */
    public static function price_cart_items($cart) {
        if (is_admin() && !wp_doing_ajax()) {
            return;
        }
        if (!class_exists('WPJOBPORTAL_Package_Price') || !function_exists('get_woocommerce_currency')) {
            return;
        }
        if (!is_object($cart) || !method_exists($cart, 'get_cart')) {
            return;
        }

        $shop_currency = get_woocommerce_currency();

        foreach ($cart->get_cart() as $cart_item) {
            if (empty($cart_item['data']) || !is_object($cart_item['data'])) {
                continue;
            }

            $product_id = absint(isset($cart_item['variation_id']) ? $cart_item['variation_id'] : 0);
            if ($product_id < 1) {
                $product_id = absint(isset($cart_item['product_id']) ? $cart_item['product_id'] : 0);
            }
            if ($product_id < 1) {
                continue;
            }

            $package_id = absint(get_post_meta($product_id, self::PRODUCT_META, true));
            if ($package_id < 1 && !empty($cart_item['product_id'])) {
                $package_id = absint(get_post_meta(absint($cart_item['product_id']), self::PRODUCT_META, true));
            }
            if ($package_id < 1) {
                continue;
            }

            $package = self::package_row($package_id);
            if (null === $package) {
                continue;
            }

            if (self::package_currency_code($package) !== $shop_currency) {
                continue;
            }

            $price = WPJOBPORTAL_Package_Price::final_price($package, false);
            $price = apply_filters('wpjobportal_woocommerce_package_price', $price, $package_id, $cart_item);
            if (!is_numeric($price) || (float) $price < 0) {
                continue;
            }

            if (self::is_subscription_product($cart_item['data'])) {
                continue;
            }

            $cart_item['data']->set_price((float) $price);
        }
    }

    /**
     * Quote what will be charged, everywhere it is quoted.
     *
     * price_cart_items() corrects the figure once the buyer reaches the cart,
     * which is the last moment that matters for the charge and the first
     * moment anybody sees the truth. Until then WooCommerce shows whatever
     * price the product row happens to hold - 49.00 on the shop, 49.00 on the
     * product page - while the portal's own Packages screen advertises 44.10
     * and the cart goes on to charge 44.10.
     *
     * Nobody is overcharged by that, but the shop is quoting a price it will
     * not take, and a buyer comparing the two screens has no way to know which
     * one is real. The package is the authority on its own price, so it
     * answers for every screen rather than only the last one.
     *
     * The regular price stays the undiscounted figure and the discount is
     * given as a sale price, so WooCommerce renders it the way the Packages
     * screen already does: the old price struck through beside the new one.
     */
    public static function quote_price($price, $product) {
        $quoted = self::quoted_figures($product);
        return null === $quoted ? $price : $quoted['final'];
    }

    public static function quote_regular_price($price, $product) {
        $quoted = self::quoted_figures($product);
        return null === $quoted ? $price : $quoted['regular'];
    }

    public static function quote_sale_price($price, $product) {
        $quoted = self::quoted_figures($product);
        if (null === $quoted) {
            return $price;
        }
        return $quoted['final'] < $quoted['regular'] ? $quoted['final'] : '';
    }

    /**
     * What the package says its price is, or null to leave the product alone.
     *
     * The guards are the ones price_cart_items() already applies, and for the
     * same reasons: a product that names no package is an ordinary product,
     * and a package priced in a currency this shop does not sell in is left
     * as it is rather than renamed into the shop's currency.
     */
    private static function quoted_figures($product) {
        if (!is_object($product) || !method_exists($product, 'get_id')) {
            return null;
        }
        if (!class_exists('WPJOBPORTAL_Package_Price') || !function_exists('get_woocommerce_currency')) {
            return null;
        }

        $product_id = absint($product->get_id());
        if ($product_id < 1) {
            return null;
        }

        /*
         * A subscription prices itself.
         *
         * This quotes what the portal says a package costs, because the shop
         * was advertising one figure and the cart charging another. A
         * subscription is not that: what it takes is a recurring amount the
         * shop owner set, and the package is what each renewal grants. Forcing
         * the one-off package price onto it would rewrite the plan.
         */
        if (self::is_subscription_product($product)) {
            return null;
        }

        $package_id = absint(get_post_meta($product_id, self::PRODUCT_META, true));
        if ($package_id < 1) {
            return null;
        }

        $package = self::package_row($package_id);
        if (null === $package || self::package_currency_code($package) !== get_woocommerce_currency()) {
            return null;
        }

        $final = WPJOBPORTAL_Package_Price::final_price($package, false);
        $final = apply_filters('wpjobportal_woocommerce_package_price', $final, $package_id, array());
        if (!is_numeric($final) || (float) $final < 0) {
            return null;
        }

        return array(
            'regular' => (float) $package->price,
            'final' => (float) $final,
        );
    }

    /** What an order's list-shaped meta holds, or nothing at all. */
    private static function meta_array($order, $key) {
        $value = $order->get_meta($key, true);
        return is_array($value) ? $value : array();
    }

    /**
     * Keep a package with the side it was sold to.
     *
     * A package carries packagefor - 1 for employers, 2 for job seekers, both
     * for one sold to either - and the portal's own Packages screen has always
     * honoured it: getPackages() adds FIND_IN_SET(roleid, packagefor) and an
     * employer is never shown a job-seeker package.
     *
     * The WooCommerce route honoured nothing. Neither this service nor
     * wpjobportal-wc.php had ever read the column, so the same package sat in
     * the shop for anybody, an employer could buy job-seeker credits, a job
     * seeker could buy an employer plan, and fulfilment granted it - it checked
     * only that the package was active and not free. What the buyer then held
     * was a package their side of the site cannot spend.
     *
     * Same rule as the portal, in both the places it matters: the buyer is
     * turned away at the cart, where it can still be explained, and refused
     * again at fulfilment, because a cart can be filled before anybody signs in
     * and a role can be chosen afterwards.
     */
    public static function refuse_wrong_audience($passed, $product_id, $quantity) {
        if (!$passed) {
            return $passed;
        }

        $package_id = absint(get_post_meta(absint($product_id), self::PRODUCT_META, true));
        if ($package_id < 1) {
            return $passed;
        }

        $package = self::package_row($package_id);
        if (null === $package) {
            return $passed;
        }

        /*
         * A visitor with no role yet is not judged, exactly as the portal's own
         * listing does not judge one. They may be about to register as either,
         * and fulfilment asks again once there is an answer.
         */
        $roleid = self::portal_role(get_current_user_id());
        if ($roleid < 1 || self::package_suits_role($package, $roleid)) {
            return $passed;
        }

        if (function_exists('wc_add_notice')) {
            wc_add_notice(
                sprintf(
                    /* translators: %s: the kind of account the package is sold to. */
                    esc_html__('This package is sold to %s, so it cannot be added to your cart.', 'wp-job-portal'),
                    self::audience_name($package)
                ),
                'error'
            );
        }

        return false;
    }

    /** Whether a package is sold to the side of the site this role is on. */
    private static function package_suits_role($package, $roleid) {
        $roleid = absint($roleid);
        if ($roleid < 1) {
            return true;
        }
        $sold_to = array_filter(array_map('absint', explode(',', (string) (isset($package->packagefor) ? $package->packagefor : ''))));
        /*
         * A package that names nobody is sold to everybody. That is how the
         * portal reads it too - FIND_IN_SET against an empty column matches
         * nothing, so such a package is simply never filtered out by role.
         */
        if (empty($sold_to)) {
            return true;
        }

        return in_array($roleid, $sold_to, true);
    }

    /** The portal role of a WordPress user, or 0 where there is none. */
    private static function portal_role($wp_user_id) {
        global $wpdb;
        $wp_user_id = absint($wp_user_id);
        if ($wp_user_id < 1) {
            return 0;
        }

        $roleid = absint(wpjobportaldb_direct::get_var($wpdb->prepare(
            'SELECT COALESCE(roleid, 0) FROM `' . $wpdb->prefix . 'wj_portal_users` WHERE uid = %d',
            $wp_user_id
        )));

        /*
         * A buyer can have a role this table does not record. A Recruit Suite
         * recruiter is a company team member with no wj_portal_users row, so
         * this returns 0 and the audience gate, reading 0 as "an undecided
         * visitor", waved job-seeker packages through. A recruiter is not
         * undecided - they are on the employer side. The add-on that owns that
         * fact answers here; core stays out of it and only asks.
         */
        return absint(apply_filters('wpjobportal_woocommerce_buyer_role', $roleid, $wp_user_id));
    }

    /** Who a package is sold to, in words a buyer can act on. */
    private static function audience_name($package) {
        $sold_to = array_filter(array_map('absint', explode(',', (string) (isset($package->packagefor) ? $package->packagefor : ''))));
        $employer = in_array(1, $sold_to, true);
        $seeker = in_array(2, $sold_to, true);
        if ($employer && !$seeker) {
            return esc_html__('employers', 'wp-job-portal');
        }
        if ($seeker && !$employer) {
            return esc_html__('job seekers', 'wp-job-portal');
        }

        return esc_html__('another kind of account', 'wp-job-portal');
    }

    /** Whether WooCommerce Subscriptions owns this product's pricing. */
    private static function is_subscription_product($product) {
        if (!is_object($product) || !method_exists($product, 'is_type')) {
            return false;
        }

        return (bool) $product->is_type(array('subscription', 'variable-subscription', 'subscription_variation'));
    }

    private static function package_row($package_id) {
        global $wpdb;
        $row = wpjobportaldb_direct::get_row($wpdb->prepare(
            'SELECT * FROM `' . $wpdb->prefix . 'wj_portal_packages` WHERE id = %d AND status = 1 AND isfree = 0',
            absint($package_id)
        ));
        return $row ? $row : null;
    }

    private static function package_currency_code($package) {
        $currency = WPJOBPORTALincluder::getJSModel('currency');
        if (!$currency || !method_exists($currency, 'getCurrencyCode')) {
            return '';
        }
        return (string) $currency->getCurrencyCode(isset($package->currencyid) ? $package->currencyid : 0);
    }

    public static function save_product_mapping($post_id) {
        $post_id = absint($post_id);
        if ($post_id < 1 || !current_user_can('edit_post', $post_id)) {
            return;
        }
        $package_id = absint(WPJOBPORTALrequest::getVar(self::PRODUCT_META, 'post', 0));
        if ($package_id > 0 && self::package_exists($package_id)) {
            update_post_meta($post_id, self::PRODUCT_META, $package_id);
            return;
        }
        delete_post_meta($post_id, self::PRODUCT_META);
    }

    /*
     * Processing is not paid for every gateway.
     *
     * WooCommerce counts processing as paid, and for a card it is: the money
     * has arrived by the time the order gets there. Cash on delivery, bank
     * transfer and cheque put the order in processing the moment it is placed,
     * with nothing received - and the package was granted then, so a buyer
     * could spend it before paying. For those the shop marking the order
     * completed is what says the money came in.
     */
    private static function is_offline_payment($order) {
        $methods = apply_filters('wpjobportal_package_offline_payment_methods', array('cod', 'bacs', 'cheque'), $order);
        return in_array($order->get_payment_method(), (array) $methods, true);
    }

    public static function fulfill_order($order_id) {
        if (!self::is_ready() || !function_exists('wc_get_order')) {
            return false;
        }
        $order = wc_get_order(absint($order_id));
        if (!$order || !$order->is_paid() || !$order->has_status(array('processing', 'completed'))) {
            return false;
        }
        if (!$order->has_status('completed') && self::is_offline_payment($order)) {
            return false;
        }
        $wp_user_id = absint($order->get_user_id());
        if ($wp_user_id < 1) {
            if ('guest_customer' !== $order->get_meta(self::STATUS_META, true)) {
                $order->update_meta_data(self::STATUS_META, 'guest_customer');
                $order->save();
                $order->add_order_note(__('WP Job Portal packages require a registered customer account; no package was granted.', 'wp-job-portal'));
            }
            return false;
        }

        $portal_uid = absint(WPJOBPORTALincluder::getObjectClass('user')->getwpjobportaluidbyuserid($wp_user_id));
        if ($portal_uid < 1) {
            return false;
        }
        if (self::legacy_order_was_fulfilled($order, $portal_uid)) {
            return true;
        }

        /*
         * An order that has never been fulfilled holds '' here, and casting an
         * empty string to an array gives array('') - one element, keyed 0,
         * holding nothing. That empty entry was then written back with the
         * real grants beside it, so every fulfilled order carried a stray
         * "0": "" in its meta and anything counting the grants counted one too
         * many.
         */
        $fulfilled = self::meta_array($order, self::FULFILLED_META);
        $grants = self::meta_array($order, self::GRANTS_META);
        $found = false;
        $changed = false;
        $success = true;

        foreach ($order->get_items('line_item') as $item_id => $item) {
            $package_id = self::package_id_for_item($item);
            if ($package_id < 1) {
                continue;
            }
            $found = true;
            $package = WPJOBPORTALincluder::getJSTable('package');
            if (!$package || !$package->load($package_id) || empty($package->status) || !empty($package->isfree)) {
                $success = false;
                continue;
            }
            /*
             * And again here, because the cart is not the last word. An order
             * can be built before anybody signs in, and the account that pays
             * for it can choose its role afterwards - so the side of the site
             * the buyer is on is only settled now. Granting a package their
             * side cannot spend helps nobody, and the order says why rather
             * than failing silently.
             */
            if (!self::package_suits_role($package, self::portal_role($wp_user_id))) {
                $success = false;
                /*
                 * Said once per package, not once per attempt. Fulfilment runs
                 * again on payment_complete, on processing, on completed and on
                 * any manual retry, and a note repeated five times reads like
                 * five faults. The already-said list is kept under a key of its
                 * own: STATUS_META is written by the caller below after this
                 * runs, so a marker left there is overwritten and says nothing
                 * the next time round.
                 */
                $told = self::meta_array($order, self::AUDIENCE_NOTICE_META);
                if (!in_array($package_id, array_map('absint', $told), true)) {
                    $told[] = $package_id;
                    $order->update_meta_data(self::AUDIENCE_NOTICE_META, $told);
                    $order->add_order_note(sprintf(
                        /* translators: 1: package title, 2: the kind of account it is sold to. */
                        __('"%1$s" is sold to %2$s, and this customer is not one, so no package was granted.', 'wp-job-portal'),
                        sanitize_text_field($package->title),
                        self::audience_name($package)
                    ));
                    $order->save();
                }
                continue;
            }
            $quantity = max(1, absint($item->get_quantity()));
            $done = isset($fulfilled[$item_id]) ? absint($fulfilled[$item_id]) : 0;
            for ($index = $done + 1; $index <= $quantity; $index++) {
                $transaction = 'wc:' . absint($order->get_id()) . ':' . absint($item_id) . ':' . $index;
                $existing_userpackage_id = self::userpackage_id_for_transaction($portal_uid, $transaction);
                if ($existing_userpackage_id > 0) {
                    $grants[$transaction] = $existing_userpackage_id;
                    $fulfilled[$item_id] = $index;
                    $changed = true;
                    continue;
                }
                $data = array(
                    'uid' => $wp_user_id,
                    'id' => $package_id,
                    'currencyid' => absint($package->currencyid),
                    'amount' => (float) $order->get_line_total($item, true) / $quantity,
                    'paymethod' => 'WooCommerce - ' . sanitize_text_field($order->get_payment_method_title()),
                    'payer_email' => sanitize_email($order->get_billing_email()),
                    'payer_name' => sanitize_text_field(trim($order->get_formatted_billing_full_name())),
                    'payer_transactionumber' => $transaction,
                    'payer_address' => sanitize_text_field(implode(', ', array_filter(array($order->get_billing_city(), $order->get_billing_state(), $order->get_billing_country())))),
                    'payer_contactnumber' => sanitize_text_field($order->get_billing_phone()),
                );
                $result = WPJOBPORTALincluder::getJSModel('purchasehistory')->storeUserPackage($package_id, $data);
                $userpackage_id = self::userpackage_id_for_transaction($portal_uid, $transaction);
                if (WPJOBPORTAL_SAVED !== $result && $userpackage_id < 1) {
                    $success = false;
                    break;
                }
                if ($userpackage_id > 0) {
                    $grants[$transaction] = $userpackage_id;
                }
                $fulfilled[$item_id] = $index;
                $changed = true;
            }
        }

        if (!$found) {
            return true;
        }
        if ($changed || $order->get_meta(self::STATUS_META, true) !== ($success ? 'fulfilled' : 'needs_attention')) {
            $previous = $order->get_meta(self::STATUS_META, true);
            $order->update_meta_data(self::FULFILLED_META, $fulfilled);
            $order->update_meta_data(self::GRANTS_META, $grants);
            $order->update_meta_data(self::STATUS_META, $success ? 'fulfilled' : 'needs_attention');
            $order->save();
            if ($previous !== ($success ? 'fulfilled' : 'needs_attention')) {
                $order->add_order_note($success
                    ? __('WP Job Portal package access was granted from the Credits catalogue.', 'wp-job-portal')
                    : __('Some WP Job Portal package access could not be granted. Review the product mapping and customer role.', 'wp-job-portal'));
            }
        }
        return $success;
    }

    public static function reverse_order($order_id) {
        global $wpdb;
        if (!self::is_ready() || !function_exists('wc_get_order')) {
            return false;
        }
        $order = wc_get_order(absint($order_id));
        if (!$order || 'reversed' === $order->get_meta(self::STATUS_META, true)) {
            return true;
        }
        $grants = array_filter(array_map('absint', self::meta_array($order, self::GRANTS_META)));
        if (empty($grants)) {
            return true;
        }

        $revoked = 0;
        $used = 0;
        foreach ($grants as $transaction => $userpackage_id) {
            $usage = absint(wpjobportaldb_direct::get_var($wpdb->prepare(
                'SELECT COUNT(*) FROM `' . $wpdb->prefix . 'wj_portal_transactionlog` WHERE userpackageid = %d AND status = 1',
                $userpackage_id
            )));
            if ($usage > 0) {
                $used++;
                continue;
            }
            $package_updated = wpjobportaldb_direct::update($wpdb->prefix . 'wj_portal_userpackages', array('status' => 2), array('id' => $userpackage_id, 'status' => 1), array('%d'), array('%d', '%d'));
            if (false !== $package_updated) {
                wpjobportaldb_direct::update($wpdb->prefix . 'wj_portal_invoices', array('status' => 0), array('recordid' => $userpackage_id, 'payer_transactionumber' => sanitize_text_field($transaction)), array('%d'), array('%d', '%s'));
                $revoked++;
            }
        }
        $status = $used > 0 ? 'reversal_needs_attention' : 'reversed';
        $order->update_meta_data(self::STATUS_META, $status);
        $order->save();
        $order->add_order_note($used > 0
            ? sprintf(/* translators: 1: revoked, 2: used */ __('WP Job Portal revoked %1$d unused package grant(s); %2$d used grant(s) require manual review.', 'wp-job-portal'), $revoked, $used)
            : sprintf(/* translators: %d: revoked */ _n('%d unused WP Job Portal package grant was revoked.', '%d unused WP Job Portal package grants were revoked.', $revoked, 'wp-job-portal'), $revoked));
        return 0 === $used;
    }

    private static function is_ready() {
        // The old Credits add-on's class, or the Marketplace Suite's copy of it.
        return (class_exists('wpjobportalcredit') || class_exists('wpjobportalmarketplacecredit'))
            && WPJOBPORTAL_Monetisation::usable('purchasehistory')
            && WPJOBPORTALincluder::getJSModel('purchasehistory');
    }

    private static function package_exists($package_id) {
        global $wpdb;
        return absint(wpjobportaldb_direct::get_var($wpdb->prepare(
            'SELECT COUNT(*) FROM `' . $wpdb->prefix . 'wj_portal_packages` WHERE id = %d AND status = 1 AND isfree = 0',
            absint($package_id)
        ))) > 0;
    }

    private static function package_id_for_item($item) {
        $product_id = absint($item->get_product_id());
        $variation_id = absint($item->get_variation_id());
        $package_id = absint(get_post_meta($variation_id ?: $product_id, self::PRODUCT_META, true));
        if ($package_id < 1 && $variation_id > 0) {
            $package_id = absint(get_post_meta($product_id, self::PRODUCT_META, true));
        }
        return $package_id;
    }

    private static function userpackage_id_for_transaction($portal_uid, $transaction) {
        global $wpdb;
        return absint(wpjobportaldb_direct::get_var($wpdb->prepare(
            'SELECT recordid FROM `' . $wpdb->prefix . 'wj_portal_invoices` WHERE uid = %d AND payer_transactionumber = %s ORDER BY id DESC LIMIT 1',
            absint($portal_uid),
            sanitize_text_field($transaction)
        )));
    }

    private static function legacy_order_was_fulfilled($order, $portal_uid) {
        global $wpdb;
        if (!empty($order->get_meta(self::FULFILLED_META, true))) {
            return false;
        }
        $invoice_id = absint(wpjobportaldb_direct::get_var($wpdb->prepare(
            'SELECT id FROM `' . $wpdb->prefix . 'wj_portal_invoices` WHERE uid = %d AND payer_transactionumber = %s LIMIT 1',
            absint($portal_uid),
            (string) absint($order->get_id())
        )));
        if ($invoice_id < 1) {
            return false;
        }
        $order->update_meta_data(self::STATUS_META, 'legacy_fulfilled');
        $order->save();
        return true;
    }
}
