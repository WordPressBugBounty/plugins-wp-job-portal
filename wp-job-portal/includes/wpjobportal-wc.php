<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

add_filter('product_type_selector', 'wpjobportal_packages_add_product_type');

function wpjobportal_packages_add_product_type($types) {
    $types['wpjobportal_packages'] = esc_html(__('WP JOB PORTAL Package', 'wp-job-portal'));
    # Per Listing types
    $types['wpjobportal_perlisting'] = esc_html(__('WP JOB PORTAL Per Listing', 'wp-job-portal'));
    return $types;
}

/*
 * The tick that went missing on refresh.
 *
 * WooCommerce marks the Virtual and Downloadable checkboxes show_if_simple,
 * and its admin script hides every show_if_* control that does not name the
 * type currently chosen. Pick a WP Job Portal type and Virtual disappears -
 * ticked, saved, sitting in the database as _virtual = yes, and nowhere on the
 * screen. Refresh and it looks as though the setting was thrown away.
 *
 * The worse half is a product being made for the first time: the box is hidden
 * before anyone can tick it, so _virtual is never set, and WooCommerce then
 * treats a package as something to weigh and post.
 *
 * Naming the types here puts the control back where the value is.
 */
add_filter('product_type_options', 'wpjobportal_packages_show_type_options');

function wpjobportal_packages_show_type_options($options) {
    if (!isset($options['virtual']['wrapper_class'])) {
        return $options;
    }
    $options['virtual']['wrapper_class'] .= ' show_if_wpjobportal_packages show_if_wpjobportal_perlisting';
    return $options;
}

add_action('plugins_loaded', 'wpjobportal_packages_create_custom_product_type');

function wpjobportal_packages_create_custom_product_type() {
    if (!class_exists('WC_Product')) {
        return;
    }
    /*
     * These extend the simple product rather than the bare one.
     *
     * WC_Product on its own has no buying behaviour: add_to_cart_url() gives
     * back the product page instead of an add-to-cart link, and the shop loop
     * button follows it there. A package is bought exactly the way a simple
     * product is, so it inherits that and only the type name differs.
     */
    class WC_Product_Wpjobportal_packages extends WC_Product_Simple { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- WooCommerce looks up a product type's class as WC_Product_{type}.

        public $product_type = '';
        public function __construct($wpjobportal_product) {
            $this->product_type = 'wpjobportal_packages';
            parent::__construct($wpjobportal_product);
            // add additional functions here
        }

        /*
         * WC_Product_Simple::get_type() answers 'simple' outright rather than
         * reading the property, and everything asks the object its type: the
         * admin type dropdown, the template hook, is_type(). Left alone the
         * package would report itself as an ordinary product and the next save
         * would make it one.
         */
        public function get_type() {
            return $this->product_type;
        }
    }

    class WC_Product_Wpjobportal_perlisting extends WC_Product_Simple { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- WooCommerce looks up a product type's class as WC_Product_{type}.

        public $product_type = '';
        public function __construct($wpjobportal_product) {
            $this->product_type = 'wpjobportal_perlisting';
            parent::__construct($wpjobportal_product);
            // add additional functions here
        }

        /*
         * WC_Product_Simple::get_type() answers 'simple' outright rather than
         * reading the property, and everything asks the object its type: the
         * admin type dropdown, the template hook, is_type(). Left alone the
         * package would report itself as an ordinary product and the next save
         * would make it one.
         */
        public function get_type() {
            return $this->product_type;
        }
    }
    /*
    // declare the product class
    class WC_Product_WPJobPortaljobs extends WC_Product {

        public function __construct($wpjobportal_product) {
            $this->product_type = 'wpjobportal_packages,wpjobportal_perlisting';
            parent::__construct($wpjobportal_product);
            // add additional functions here
        }

    }
    */

}

/*
 * The button that was never drawn.
 *
 * WooCommerce's single-product template asks for the add-to-cart form by the
 * product's own type - do_action('woocommerce_' . $product->get_type() .
 * '_add_to_cart') - and ships handlers for simple, variable, grouped and
 * external only. A package is a type of its own, so nothing answered, and the
 * page showed the price and the description with no way to buy it. Every
 * report of "there is no Add to cart" on a package product is this line
 * missing.
 */
add_action('woocommerce_wpjobportal_packages_add_to_cart', 'woocommerce_simple_add_to_cart');
add_action('woocommerce_wpjobportal_perlisting_add_to_cart', 'woocommerce_simple_add_to_cart');

// add the settings under ‘General’ sub-menu
add_action('woocommerce_product_options_general_product_data', 'wpjobportal_packages_add_custom_settings');

function wpjobportal_packages_add_custom_settings() {
    global $woocommerce, $post;
    echo '<div id="wpjobportal_packages_custom_product_option" class="options_group">';

    // get all packages packs
    $query = "SELECT id,title,price,discount,discounttype,renewdiscount,renewdiscounttype,currencyid FROM `" . wpjobportal::$_db->prefix . "wj_portal_packages` WHERE status = 1 AND isfree = 0";
    $wpjobportal_result = wpjobportal::$_db->get_results($query);
	$wpjobportal_packagepack_fieldvalue = get_post_meta($post->ID, 'wpjobportal_packagepack_field', true);

    $wpjobportal_options = array('' => esc_html(__('Select Package', 'wp-job-portal')));
    $wpjobportal_fielddata = '';

    // Parse the single Credits package catalogue.
    if ($wpjobportal_result && is_array($wpjobportal_result)) {
        $wpjobportal_i = 0;
         foreach ($wpjobportal_result AS $wpjobportal_pack) {
            $wpjobportal_options[$wpjobportal_pack->id] = $wpjobportal_pack->title;
            if($wpjobportal_i != 0) {
                $wpjobportal_fielddata .= '|';
            }
            /*
             * "price,sale" - the script below fills Sale price from the part
             * after the comma, and 0 there means no sale. Only the price was
             * ever sent, so a package's discount never reached the product.
             */
            $wpjobportal_sale = 0;
            if (class_exists('WPJOBPORTAL_Package_Price')) {
                $wpjobportal_final = WPJOBPORTAL_Package_Price::final_price($wpjobportal_pack);
                if ($wpjobportal_final < (float) $wpjobportal_pack->price) {
                    $wpjobportal_sale = $wpjobportal_final;
                }
            }
            $wpjobportal_fielddata .= $wpjobportal_pack->id . ':' . $wpjobportal_pack->price . ',' . $wpjobportal_sale;
            $wpjobportal_i++;
        }
    }

    // Create a number field, for example for UPC
	$wpjobportal_value = "";
	if($wpjobportal_packagepack_fieldvalue)
		$wpjobportal_value = $wpjobportal_packagepack_fieldvalue;
    woocommerce_wp_select(
            array(
                'id' => 'wpjobportal_packagepack_field',
                'label' => esc_html(__('WP Job Portal package', 'wp-job-portal')),
                'placeholder' => '',
                'desc_tip' => 'true',
                'description' => esc_html(__('Map this WooCommerce product to one paid package from WP Job Portal Credits. Free packages are claimed directly in WP Job Portal.', 'wp-job-portal')),
                'type' => 'number',
                'options' => $wpjobportal_options,
                'value' => $wpjobportal_value,
                'custom_attributes' => array('fielddata' => $wpjobportal_fielddata)
    ));



    echo '</div>';
        wp_register_script( 'wpjobportal-inline-handle', '', array(), WPJOBPORTAL_VERSION, false );
        wp_enqueue_script( 'wpjobportal-inline-handle' );
        $wpjobportal_inline_js_script = '
        jQuery(document).ready(function(){
                jQuery( ".options_group.pricing" ).addClass( "show_if_wpjobportal_packages" ).show();

                /*
                 * Which types may name a package.
                 *
                 * The picker used to appear for wpjobportal_packages alone, and
                 * the product type dropdown is single-select - so a WooCommerce
                 * Subscription product, which must be type "subscription" to
                 * renew at all, had nowhere to say which package it sells. The
                 * rest of the route never cared about the type: the price
                 * filter, the cart and fulfilment all read the mapping meta,
                 * and a renewal order reaches processing like any other order,
                 * so each renewal grants the package again. Only this one
                 * dropdown stood in the way.
                 */
                var wpjpPackageTypes = ["wpjobportal_packages", "subscription", "variable-subscription"];

                jQuery("#product-type").change(function(){
                    var value = jQuery(this).val();
                    if(jQuery.inArray(value, wpjpPackageTypes) !== -1){
                        jQuery("div#wpjobportal_packages_custom_product_option").show();
                    }else{
                        jQuery("div#wpjobportal_packages_custom_product_option").hide();
                    }
                    /*
                     * A subscription prices itself. What it charges is a
                     * recurring amount the shop owner sets, not the one-off package
                     * price, so the fields are left alone there - and a
                     * variable subscription has no price field on this tab at
                     * all.
                     */
                    if(value == "wpjobportal_packages"){
                        var selectedvalue = jQuery("select#wpjobportal_packagepack_field").val();
                        var value = jQuery("select#wpjobportal_packagepack_field").attr("fielddata");
                        var array = value.split("|");
                        for(i=0; i < array.length; i++){
                            var valarray = array[i].split(":");
                            var index = valarray[0];
                            var ans = valarray[1].split(",");
                            if(selectedvalue == index){
                                jQuery("input#_regular_price").val(ans[0]).attr("readonly","true");
                                if(ans[1] != 0){
                                    jQuery("input#_sale_price").val(ans[1]).attr("readonly","true");
                                }else{
                                    jQuery("input#_sale_price").val("").attr("readonly","true");
                                }
                            }
                        }
                    }else{
                        jQuery("input#_regular_price").removeAttr("readonly");
                        jQuery("input#_sale_price").removeAttr("readonly");
                    }
                });
                jQuery("#product-type").change();
                jQuery("select#wpjobportal_packagepack_field").change(function(){
                    if(jQuery("#product-type").val() != "wpjobportal_packages"){
                        return;
                    }
                    var selectedvalue = jQuery(this).val();
                    var value = jQuery(this).attr("fielddata");
                    var array = value.split("|");
                    for(i=0; i < array.length; i++){
                        var valarray = array[i].split(":");
                        var index = valarray[0];
                        var ans = valarray[1].split(",");
                        if(selectedvalue == index){
                            jQuery("input#_regular_price").val(ans[0]).attr("readonly","true");
                            if(ans[1] != 0){
                                jQuery("input#_sale_price").val(ans[1]).attr("readonly","true");
                            }else{
                                jQuery("input#_sale_price").val("").attr("readonly","true");
                            }
                        }
                    }
                });
            });
    ';
    wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
}

add_action('woocommerce_process_product_meta', 'wpjobportal_packages_save_custom_settings');

function wpjobportal_packages_save_custom_settings($post_id) {
    // save wpjobportal_packagepack_field
    $wpjobportal_packagepack_field = wpjobportal::wpjobportal_sanitizeData(WPJOBPORTALrequest::getVar('wpjobportal_packagepack_field','post') );
    if (!empty($wpjobportal_packagepack_field))
        update_post_meta($post_id, 'wpjobportal_packagepack_field', esc_attr($wpjobportal_packagepack_field));
}

function wpjobportal_payment_complete_by_wc($order_id){
    $order = new WC_Order($order_id);
	if($order->status != 'completed'){// to avoint duplication of record in purchase history
       return;
	}
    $wpjobportal_user_id = $order->user_id;
    // getting the product detail **
    $wpjobportal_items = $order->get_items();
    $wpjobportal_packagepackids = array();
    foreach ($wpjobportal_items as $wpjobportal_item) {
        for($wpjobportal_i=1;$wpjobportal_i<=$wpjobportal_item->get_quantity();$wpjobportal_i++){
            $wpjobportal_product_name = $wpjobportal_item['name'];
            $wpjobportal_product_id = $wpjobportal_item['product_id'];
            $wpjobportal_product_variation_id = $wpjobportal_item['variation_id'];
            $wpjobportal_packageid = get_post_meta($wpjobportal_product_id, 'wpjobportal_packagepack_field', true);
            if(!empty($wpjobportal_packageid)) {
                $wpjobportal_package = WPJOBPORTALincluder::getJSTable('package');
                $wpjobportal_package->load($wpjobportal_packageid);
                $wpjobportal_arr = array();
                $wpjobportal_arr['uid']                    = $wpjobportal_user_id;
                $wpjobportal_arr['id']                     = $wpjobportal_packageid;
                $wpjobportal_arr['currencyid']             = $wpjobportal_package->currencyid;
                $wpjobportal_arr['amount']                 = $wpjobportal_item->get_product()->get_price();
                $wpjobportal_arr['paymethod']              = 'Woocommerce - ' . $order->get_payment_method_title();
                $wpjobportal_arr['payer_email']            = $order->get_billing_email();
                $wpjobportal_arr['payer_name']             = $order->get_billing_first_name().' '.$order->get_billing_last_name();
                $wpjobportal_arr['payer_transactionumber'] = $order->get_id();
                $wpjobportal_arr['payer_address']          = $order->get_billing_city() . ', ' . $order->get_billing_state() . ', ' . $order->get_billing_country();
                $wpjobportal_arr['payer_contactnumber']    = $order->get_billing_phone();
                WPJOBPORTALincluder::getJSModel('purchasehistory')->storeUserPackage($wpjobportal_packageid, $wpjobportal_arr);
            }
        }
    }
}

$wpjobportal_perlisting = array();

 //Type Module Perlisting
add_action('woocommerce_product_options_general_product_data', 'wpjobportal_perlisting_add_custom_settings');

function wpjobportal_perlisting_add_custom_settings() {
    global $woocommerce, $post;
   $wpjobportal_perlisting = array(
    (object) array('id' => 'company_price_perlisting', 'text' => esc_html(__('Company', 'wp-job-portal')),),
    (object) array('id' => 'company_feature_price_perlisting', 'text' => esc_html(__('Featured Company', 'wp-job-portal'))),
    (object) array('id' => 'job_currency_price_perlisting', 'text' => esc_html(__('Add Job', 'wp-job-portal'))),
    (object) array('id' => 'jobs_feature_price_perlisting', 'text' => esc_html(__('Featured Job', 'wp-job-portal'))),
    (object) array('id' => 'job_resume_price_perlisting', 'text' => esc_html(__('Add Resume', 'wp-job-portal'))),
    (object) array('id' => 'job_featureresume_price_perlisting', 'text' => esc_html(__('Featured Resume', 'wp-job-portal'))),
    (object) array('id' => 'job_department_price_perlisting', 'text' => esc_html(__('Add Department', 'wp-job-portal'))),
    (object) array('id' => 'job_resumesavesearch_price_perlisting', 'text' => esc_html(__('Resume Save Search', 'wp-job-portal'))),
    (object) array('id' => 'job_jobalert_price_perlisting', 'text' => esc_html(__('Job Alert', 'wp-job-portal'))),
    (object) array('id' => 'job_viewcompanycontact_price_perlisting', 'text' => esc_html(__('View Company Contact Detail', 'wp-job-portal'))),
    (object) array('id' => 'job_viewresumecontact_price_perlisting', 'text' => esc_html(__('View Resume Contact Detail', 'wp-job-portal'))),
    (object) array('id' => 'job_jobapply_price_perlisting', 'text' => esc_html(__('Job Apply', 'wp-job-portal'))),
    (object) array('id' => 'job_coverletter_price_perlisting', 'text' => esc_html(__('Add Cover Letter', 'wp-job-portal')))

    );



    echo '<div id="wpjobportal_perlisting_custom_product_option" class="options_group">';
    $wpjobportal_options = array('' => esc_html(__('Select Package', 'wp-job-portal')));
        $wpjobportal_i = 0;
        $wpjobportal_fielddata = '';
        foreach ($wpjobportal_perlisting as $wpjobportal_key => $wpjobportal_value) {
            $wpjobportal_options[$wpjobportal_value->id] = $wpjobportal_value->text;
            if($wpjobportal_i != 0) {
                $wpjobportal_fielddata .= '|';
            }
            $wpjobportal_fielddata .= esc_html($wpjobportal_value->id). ':' . esc_html(wpjobportal::$_config->getConfigValue($wpjobportal_value->id));
            $wpjobportal_i++;
        }
    # Perlisting Module Package Selection
    woocommerce_wp_select(
            array(
                'id' => 'wpjobportal_perlistingpack_field',
                'label' => esc_html(__('Package combo', 'wp-job-portal')),
                'placeholder' => '',
                'desc_tip' => 'true',
                'description' => esc_html(__('Select packages pack so that user can purchase them.', 'wp-job-portal')),
                'type' => 'number',
                'options' => $wpjobportal_options,
                'custom_attributes' => array('fielddata' => $wpjobportal_fielddata)
    ));
    echo '</div>';
    wp_register_script( 'wpjobportal-inline-handle', '', array(), WPJOBPORTAL_VERSION, false );
    wp_enqueue_script( 'wpjobportal-inline-handle' );
    $wpjobportal_inline_js_script = '
            jQuery(document).ready(function(){
                jQuery( ".options_group.pricing" ).addClass( "show_if_wpjobportal_perlisting" ).show();

                jQuery("#product-type").change(function(){
                    var value = jQuery(this).val();
                    if(value == "wpjobportal_perlisting"){
                        jQuery("div#wpjobportal_perlisting_custom_product_option").show();
                        var selectedvalue = jQuery("select#wpjobportal_perlistingpack_field").val();
                        var value = jQuery("select#wpjobportal_perlistingpack_field").attr("fielddata");
                        var array = value.split("|");
                        for(i=0; i < array.length; i++){
                            var valarray = array[i].split(":");
                            var index = valarray[0];
                            var ans = valarray[1].split(",");
                            if(selectedvalue == index){
                                jQuery("input#_regular_price").val(ans[0]).attr("readonly","true");
                                if(ans[1] != 0){
                                    jQuery("input#_sale_price").val(ans[1]).attr("readonly","true");
                                }else{
                                    jQuery("input#_sale_price").attr("readonly","true");
                                }
                            }
                        }
                    }else{
                        jQuery("div#wpjobportal_perlisting_custom_product_option").hide();
                        // a package product locks its own prices; this runs after that script
                        if(value != "wpjobportal_packages"){
                            jQuery("input#_regular_price").removeAttr("readonly");
                            jQuery("input#_sale_price").removeAttr("readonly");
                        }
                    }
                });
                jQuery("#product-type").change();
                jQuery("select#wpjobportal_perlistingpack_field").change(function(){
                    var selectedvalue = jQuery(this).val();
                    var value = jQuery(this).attr("fielddata");
                    var array = value.split("|");
                    for(i=0; i < array.length; i++){
                        var valarray = array[i].split(":");
                        var index = valarray[0];
                        var ans = valarray[1].split(",");
                        if(selectedvalue == index){
                            jQuery("input#_regular_price").val(ans[0]).attr("readonly","true");
                            if(ans[1] != 0){
                                jQuery("input#_sale_price").val(ans[1]).attr("readonly","true");
                            }else{
                                jQuery("input#_sale_price").val("").attr("readonly","true");
                            }
                        }
                    }
                });
            });
    ';
    wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
}

add_action('woocommerce_process_product_meta', 'wpjobportal_perlisting_save_custom_settings');

function wpjobportal_perlisting_save_custom_settings($post_id) {
    // save wpjobportal_packagepack_field
    $wpjobportal_perlistingpack_field = wpjobportal::wpjobportal_sanitizeData(WPJOBPORTALrequest::getVar('wpjobportal_perlistingpack_field','post') );
    //echo $wpjobportal_perlistingpack_field;
    if (!empty($wpjobportal_perlistingpack_field))
        update_post_meta($post_id, 'wpjobportal_perlistingpack_field', esc_attr($wpjobportal_perlistingpack_field));
}


# Payment Woo Commerce
function wpjobportal_paymentperlisting_complete_by_wc($order_id){
    $order = new WC_Order($order_id);
    if($order->get_status() != 'completed'){// to avoint duplication of record in purchase history
       return;
    }
    /*
     * Per-listing orders only.
     *
     * This runs on woocommerce_payment_complete and order_status_completed for
     * EVERY order, including ordinary package purchases, which are handled by
     * WPJOBPORTAL_WooCommerce_Package_Service. That service removes the four
     * legacy package handlers on boot but not these two per-listing twins,
     * because per-listing is a live feature they must not switch off.
     *
     * A package order carries no _wpjobporta_billing_perlisting meta, so the
     * explode below produced an empty array and the two reads after it raised
     * "Undefined array key 0" and "... key 1" on every package purchase. The
     * work that followed was a no-op in that case anyway - it is all guarded on
     * a module id that was never there - so this returns before any of it.
     */
    $wpjobportal_perlisting = wpjobportal_get_order_perlisting($order);
    if (!is_string($wpjobportal_perlisting) || strpos($wpjobportal_perlisting, '-') === false) {
        return;
    }
    /*
     * Once per order.
     *
     * Both woocommerce_payment_complete and order_status_completed lead here,
     * and a gateway that completes the order as it takes the payment fires the
     * two in one request - each published the record and wrote an invoice.
     * The mark is saved before the work so the second call finds it.
     */
    if ($order->get_meta('_wpjobportal_perlisting_fulfilled', true) !== '') {
        return;
    }
    $order->update_meta_data('_wpjobportal_perlisting_fulfilled', current_time('mysql'));
    $order->save_meta_data();
    $wpjobportal_user_id = $order->get_user_id();
    // getting the product detail **
    $wpjobportal_items = $order->get_items();
    $wpjobportal_packagepackids = array();
    $wpjobportal_module_function = '';
    /*
     * Each record once, whatever the quantity or the number of lines.
     *
     * This used to run once per unit of every line, always with the one id
     * the order carried: a quantity of 2 published the same record twice and
     * wrote two invoices, and a second record bought in the same order was
     * paid for and never published. A line put in the cart by the per-listing
     * route names its own record; an order from before that, or from the
     * classic checkout, falls back to the order's id as it always did.
     */
    $wpjobportal_done = array();
    $wpjobportal_lines_named = false;
    foreach ($wpjobportal_items as $wpjobportal_item) {
        if ($wpjobportal_item->get_meta('_wpjobportal_perlisting', true) !== '') {
            $wpjobportal_lines_named = true;
            break;
        }
    }
    foreach ($wpjobportal_items as $wpjobportal_item) {
        $wpjobportal_module = $wpjobportal_item->get_meta('_wpjobportal_perlisting', true);
        if (!is_string($wpjobportal_module) || strpos($wpjobportal_module, '-') === false) {
            if ($wpjobportal_lines_named) {
                continue; // an ordinary product bought alongside
            }
            $wpjobportal_module = $wpjobportal_perlisting;
        }
        if (isset($wpjobportal_done[$wpjobportal_module])) {
            continue;
        }
        $wpjobportal_done[$wpjobportal_module] = true;
        $wpjobportal_product_name = $wpjobportal_item['name'];
        $wpjobportal_product_id = $wpjobportal_item['product_id'];
        $wpjobportal_product_variation_id = $wpjobportal_item['variation_id'];
        $parse = wpjobportalphplib::wpJP_explode('-', $wpjobportal_module);
        $wpjobportal_moduleid = $parse[1];
        $wpjobportal_actionname = $parse[0];
        # switch case
        # Need to change this
           // $wpjobportal_currencyid = wpjobportal::$_config->getConfigValue('job_currency_department_perlisting');

        # Defination --OF Currency id For Department And Price
        if(!empty($wpjobportal_moduleid)) {
            //$wpjobportal_package = WPJOBPORTALincluder::getJSTable('package');
            //$wpjobportal_package->load($wpjobportal_packageid);
            $wpjobportal_arr = array();
            $wpjobportal_arr['uid']                    = $wpjobportal_user_id;
            $wpjobportal_arr['id']                     = $wpjobportal_moduleid;
            $wpjobportal_arr['currencyid']             = '';
            $wpjobportal_arr['amount']                 = $wpjobportal_item->get_product()->get_price();
            $wpjobportal_arr['price']                  = $wpjobportal_item->get_product()->get_price();
            $wpjobportal_arr['paymethod']              = 'Woocommerce - ' . $order->get_payment_method_title();
            $wpjobportal_arr['payer_email']            = $order->get_billing_email();
            $wpjobportal_arr['payer_name']             = $order->get_billing_first_name().' '.$order->get_billing_last_name();
            $wpjobportal_arr['payer_transactionumber'] = $order->get_id();
            $wpjobportal_arr['payer_address']          = $order->get_billing_city() . ', ' . $order->get_billing_state() . ', ' . $order->get_billing_country();
            $wpjobportal_arr['payer_contactnumber']    = $order->get_billing_phone();
            # Same function For Paypal,woocommerce,stripe-->
            WPJOBPORTALincluder::getJSModel('wpjobportal')->storeModule($wpjobportal_arr,$wpjobportal_actionname);
        }
    }
}
# Fetching module --set up woocommerce
add_action( 'woocommerce_after_checkout_billing_form', 'wpjobportal_add_custom_checkout_hidden_field' );
add_action( 'woocommerce_after_order_notes', 'wpjobportal_add_custom_checkout_hidden_field' );
function wpjobportal_add_custom_checkout_hidden_field( $wpjobportal_checkout ) {
        $wpjobportal_moduleid = WPJOBPORTALrequest::getVar('id');
        echo '<div id="user_link_hidden_checkout_field">
                <input type="hidden" class="input-hidden" name="billing_wpjobportal_mid" id="billing_wpjobportal_mid" value="' . esc_attr($wpjobportal_moduleid) . '">
        </div>';
   // }
}


# Member
function wpjobportal_woocommerce_payment_complete($order_id) {
    wpjobportal_payment_complete_by_wc($order_id);
}
# Perlisting
function wpjobportal_woocommerce_paymentperlisting_complete($order_id) {
    wpjobportal_paymentperlisting_complete_by_wc($order_id);
}

function wpjobportal_woocommerce_payment_pending($order_id) {
}

function wpjobportal_woocommerce_payment_failed($order_id) {
}

function wpjobportal_woocommerce_payment_hold($order_id) {
}

function wpjobportal_woocommerce_payment_processing($order_id) {
}
# Member
function wpjobportal_woocommerce_payment_completed($order_id) {
    wpjobportal_payment_complete_by_wc($order_id);
}
# Perlisting
function wpjobportal_woocommerce_paymentperlisting_completed($order_id) {
    wpjobportal_paymentperlisting_complete_by_wc($order_id);
}

function wpjobportal_woocommerce_payment_refunded($order_id) {
}

function wpjobportal_woocommerce_payment_cancelled($order_id) {
}

/*
 * Which record a per-listing order paid for.
 *
 * The classic checkout posts billing_wpjobportal_mid and it is stored with
 * add_post_meta, so that is read first, exactly as before. The block checkout
 * posts no such field and fires no woocommerce_checkout_update_order_meta, so
 * an order placed there only has the copy taken from the cart line - kept as
 * order meta, which is the only kind HPOS holds against the order itself.
 */
function wpjobportal_get_order_perlisting($order) {
    $wpjobportal_perlisting = get_post_meta($order->get_id(), '_wpjobporta_billing_perlisting', true);
    if (is_string($wpjobportal_perlisting) && $wpjobportal_perlisting !== '') {
        return $wpjobportal_perlisting;
    }
    return $order->get_meta('_wpjobporta_billing_perlisting', true);
}

/*
 * Runs for every line of every order on both checkouts, and does nothing
 * unless the line was put in the cart by the per-listing payment route.
 * An order that already carries a value keeps it.
 */
add_action('woocommerce_checkout_create_order_line_item', 'wpjobportal_perlisting_copy_cart_item_to_order', 10, 4);

function wpjobportal_perlisting_copy_cart_item_to_order($item, $cart_item_key, $values, $order) {
    if (empty($values['wpjobportal_perlisting']) || !is_string($values['wpjobportal_perlisting'])) {
        return;
    }
    $wpjobportal_perlisting = sanitize_text_field($values['wpjobportal_perlisting']);
    if (strpos($wpjobportal_perlisting, '-') === false) {
        return;
    }
    $item->add_meta_data('_wpjobportal_perlisting', $wpjobportal_perlisting, true);
    if ($order->get_meta('_wpjobporta_billing_perlisting', true) === '') {
        $order->update_meta_data('_wpjobporta_billing_perlisting', $wpjobportal_perlisting);
    }
}

/*
 * One of each record in the cart.
 *
 * Each record is paid for once, so a per-listing line is held at a quantity
 * of 1 whatever the cart or the block cart's stepper asks for. Every other
 * line is left alone.
 */
add_action('woocommerce_before_calculate_totals', 'wpjobportal_perlisting_hold_quantity_at_one', 20);

function wpjobportal_perlisting_hold_quantity_at_one($cart) {
    if (!is_object($cart) || !method_exists($cart, 'get_cart')) {
        return;
    }
    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        if (!empty($cart_item['wpjobportal_perlisting']) && (int) $cart_item['quantity'] > 1) {
            $cart->set_quantity($cart_item_key, 1, false);
        }
    }
}

/*
 * What each per-listing setting pays for.
 *
 * Three kinds, because they are bought by different people:
 *   listing  - the owner pays to publish a record of their own; it waits on
 *              status 3 until then.
 *   featured - the owner pays to feature an approved record of their own.
 *   view     - somebody else pays to see a record's contact details; the
 *              access is a row in a view table, and the payer is not the owner.
 * 'type' is the invoice type the store function writes for that purchase.
 */
function wpjobportal_perlisting_record_types() {
    return array(
        'company_price_perlisting'                => array('kind' => 'listing', 'table' => 'companies', 'type' => 'company', 'label' => 'company'),
        'job_currency_price_perlisting'           => array('kind' => 'listing', 'table' => 'jobs', 'type' => 'job', 'label' => 'job'),
        'job_resume_price_perlisting'             => array('kind' => 'listing', 'table' => 'resume', 'type' => 'resume', 'label' => 'resume'),
        'job_department_price_perlisting'         => array('kind' => 'listing', 'table' => 'departments', 'type' => 'department', 'label' => 'department'),
        'job_coverletter_price_perlisting'        => array('kind' => 'listing', 'table' => 'coverletters', 'type' => 'coverletter', 'label' => 'cover letter'),
        'job_jobalert_price_perlisting'           => array('kind' => 'listing', 'table' => 'jobalertsetting', 'type' => 'jobalert', 'label' => 'job alert'),
        'job_resumesavesearch_price_perlisting'   => array('kind' => 'listing', 'table' => 'resumesearches', 'type' => 'resumesavesearch', 'label' => 'resume search'),
        'job_jobapply_price_perlisting'           => array('kind' => 'listing', 'table' => 'jobapply', 'type' => 'jobapply', 'label' => 'job application'),
        'company_feature_price_perlisting'        => array('kind' => 'featured', 'table' => 'companies', 'flag' => 'isfeaturedcompany', 'type' => 'featuredcompany', 'label' => 'featured company'),
        'jobs_feature_price_perlisting'           => array('kind' => 'featured', 'table' => 'jobs', 'flag' => 'isfeaturedjob', 'type' => 'featuredjob', 'label' => 'featured job'),
        'job_featureresume_price_perlisting'      => array('kind' => 'featured', 'table' => 'resume', 'flag' => 'isfeaturedresume', 'type' => 'featuredresume', 'label' => 'featured resume'),
        'job_viewcompanycontact_price_perlisting' => array('kind' => 'view', 'table' => 'companies', 'view_table' => 'jobseeker_view_company', 'view_column' => 'companyid', 'type' => 'companycontactdetail', 'label' => 'company contact detail'),
        'job_viewresumecontact_price_perlisting'  => array('kind' => 'view', 'table' => 'resume', 'view_table' => 'employer_view_resume', 'view_column' => 'resumeid', 'type' => 'resumecontactdetail', 'label' => 'resume contact detail'),
    );
}

/*
 * Whether this portal user may pay for this record.
 *
 * Without it any id could be put in the cart, and the completed order then
 * published, featured or handed over a record the payer had no business with.
 * A setting not in the table above is left as it always was.
 */
function wpjobportal_perlisting_can_pay($config, $recordid, $portal_uid) {
    $types = wpjobportal_perlisting_record_types();
    if (!isset($types[$config])) {
        return true;
    }
    if (!is_numeric($recordid) || (int) $recordid < 1 || !is_numeric($portal_uid) || (int) $portal_uid < 1) {
        return false;
    }
    global $wpdb;
    $t = $types[$config];
    $table = $wpdb->prefix . 'wj_portal_' . $t['table'];
    switch ($t['kind']) {
        case 'listing':
            $query = $wpdb->prepare("SELECT COUNT(id) FROM `$table` WHERE id = %d AND uid = %d AND status = 3", $recordid, $portal_uid); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name comes from the fixed list above.
            break;
        case 'featured':
            /*
             * The flag reads 1 featured, 0 paid for and waiting on an
             * administrator, 2 never featured. Not featured, or a feature that
             * has run out, may be bought; one that is live or still waiting may
             * not - that would be paying twice.
             */
            $flag = $t['flag'];
            $query = $wpdb->prepare("SELECT COUNT(id) FROM `$table` WHERE id = %d AND uid = %d AND status = 1 AND ($flag IS NULL OR $flag = 2 OR ($flag = 1 AND endfeatureddate < %s))", $recordid, $portal_uid, current_time('mysql')); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table and column names come from the fixed list above.
            break;
        case 'view':
            $view_table = $wpdb->prefix . 'wj_portal_' . $t['view_table'];
            $query = $wpdb->prepare("SELECT COUNT(id) FROM `$table` WHERE id = %d AND uid != %d AND NOT EXISTS (SELECT 1 FROM `$view_table` WHERE " . $t['view_column'] . " = %d AND uid = %d)", $recordid, $portal_uid, $recordid, $portal_uid); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table and column names come from the fixed list above.
            break;
        default:
            return true;
    }
    return (int) $wpdb->get_var($query) > 0; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- prepared above.
}

/*
 * A refund or a cancellation takes back what the order published.
 *
 * Only for an order this file fulfilled, and only once - the way the package
 * service treats a refunded package. A new listing goes back to waiting for
 * payment, a featured record stops being featured, and a contact view is
 * taken away from whoever bought it; each one's invoice is withdrawn and the
 * order says what was done. Clearing the fulfilled mark lets a later
 * completion publish again.
 */
add_action('woocommerce_order_status_refunded', 'wpjobportal_perlisting_reverse_order', 20);
add_action('woocommerce_order_status_cancelled', 'wpjobportal_perlisting_reverse_order', 20);

function wpjobportal_perlisting_reverse_order($order_id) {
    $order = wc_get_order($order_id);
    if (!$order || $order->get_meta('_wpjobportal_perlisting_fulfilled', true) === '') {
        return;
    }
    $wpjobportal_records = array();
    foreach ($order->get_items() as $wpjobportal_item) {
        $wpjobportal_module = $wpjobportal_item->get_meta('_wpjobportal_perlisting', true);
        if (is_string($wpjobportal_module) && strpos($wpjobportal_module, '-') !== false) {
            $wpjobportal_records[$wpjobportal_module] = true;
        }
    }
    if (empty($wpjobportal_records)) {
        $wpjobportal_records[wpjobportal_get_order_perlisting($order)] = true;
    }

    $order->delete_meta_data('_wpjobportal_perlisting_fulfilled');
    $order->save_meta_data();

    global $wpdb;
    $types = wpjobportal_perlisting_record_types();
    $buyer = absint(WPJOBPORTALincluder::getObjectClass('user')->getwpjobportaluidbyuserid($order->get_user_id()));
    foreach (array_keys($wpjobportal_records) as $wpjobportal_module) {
        $parse = wpjobportalphplib::wpJP_explode('-', $wpjobportal_module);
        if (count($parse) < 2 || !is_numeric($parse[1])) {
            continue;
        }
        $wpjobportal_actionname = $parse[0];
        $wpjobportal_moduleid = (int) $parse[1];
        if (!isset($types[$wpjobportal_actionname])) {
            /* translators: %s: the per-listing item, e.g. job_currency_price_perlisting-12. */
            $order->add_order_note(sprintf(__('WP Job Portal: %s was not reversed automatically; please review it.', 'wp-job-portal'), $wpjobportal_module));
            continue;
        }
        $t = $types[$wpjobportal_actionname];
        $table = $wpdb->prefix . 'wj_portal_' . $t['table'];
        if ($t['kind'] === 'listing') {
            $wpdb->update($table, array('status' => 3), array('id' => $wpjobportal_moduleid), array('%d'), array('%d'));
            /* translators: 1: kind of record, e.g. cover letter. 2: its id. */
            $note = __('WP Job Portal: %1$s #%2$d is waiting for payment again and its invoice was withdrawn.', 'wp-job-portal');
        } elseif ($t['kind'] === 'featured') {
            // 2 is "not featured"; 0 would put it in the administrator's approval queue
            $wpdb->update($table, array($t['flag'] => 2), array('id' => $wpjobportal_moduleid), array('%d'), array('%d'));
            /* translators: 1: kind of record, e.g. featured job. 2: its id. */
            $note = __('WP Job Portal: %1$s #%2$d is no longer featured and its invoice was withdrawn.', 'wp-job-portal');
        } else {
            if ($buyer > 0) {
                $wpdb->delete($wpdb->prefix . 'wj_portal_' . $t['view_table'], array($t['view_column'] => $wpjobportal_moduleid, 'uid' => $buyer), array('%d', '%d'));
            }
            /* translators: 1: kind of purchase, e.g. company contact detail. 2: the record's id. */
            $note = __('WP Job Portal: access to %1$s #%2$d was taken back and its invoice was withdrawn.', 'wp-job-portal');
        }
        $wpdb->update($wpdb->prefix . 'wj_portal_invoices', array('status' => 0), array('type' => $t['type'], 'recordid' => $wpjobportal_moduleid, 'payer_transactionumber' => (string) $order->get_id()), array('%d'), array('%s', '%d', '%s'));
        $order->add_order_note(sprintf($note, $t['label'], $wpjobportal_moduleid));
    }
}

add_action( 'woocommerce_checkout_update_order_meta', 'wpjobportal_add_order_delivery_date_to_order' , 10, 1);

function wpjobportal_add_order_delivery_date_to_order ( $order_id ) {
    # Billing Module --($wpjobportal_module per id)
    $billing_wpjobportal_mid  = WPJOBPORTALrequest::getVar('billing_wpjobportal_mid','post','');
    if (  '' != $billing_wpjobportal_mid ) {
        add_post_meta( $order_id, '_wpjobporta_billing_perlisting',  sanitize_text_field( $billing_wpjobportal_mid ) );
    }
}

add_action( 'wp_footer', function(){
    # Hide return to shop
    wp_register_script( 'wpjobportal-inline-handle', '', array(), WPJOBPORTAL_VERSION, false );
    wp_enqueue_script( 'wpjobportal-inline-handle' );
    $wpjobportal_inline_js_script = "
    jQuery(window).on('load', function() {
        var backBtn = jQuery('a.button.wc-backward');
        if (backBtn.length) {
            backBtn.hide();
        }
    });
    ";
    wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
});



  add_filter( 'woocommerce_order_item_name', 'wpjobportal_custom_orders_items_names', 10, 2 );
  /*
   * Rename the line on the thank-you page, and only there.
   *
   * A per-listing purchase reaches checkout as an id shaped
   * "<setting>-<record>", and this turns it back into something a buyer
   * recognises. The comment always said "Only in thankyou Order-received
   * page" - but the endpoint was tested after the lookup had already run, and
   * the filter fires on every order item name WooCommerce renders.
   *
   * On the admin order screen that was a warning on every order. WooCommerce
   * edits an order at admin.php?page=wc-orders&action=edit&id=123, so `id`
   * there is the order id, and getProductDesc('123') has no "-" to split on.
   *
   * The endpoint is asked first now, so nothing else on the site pays for a
   * lookup meant for one page.
   */
  function wpjobportal_custom_orders_items_names( $wpjobportal_item_name, $wpjobportal_item ) {
        if (!function_exists('is_wc_endpoint_url') || !is_wc_endpoint_url('order-received')) {
            return $wpjobportal_item_name;
        }
        $wpjobportal_id = WPJOBPORTALrequest::getVar('id');
        $wpjobportal_name = wpjobportal::$_common->getProductDesc($wpjobportal_id);
        if(empty($wpjobportal_name)){
            return $wpjobportal_item_name;
        }
        # Specific Item Name For a Product
        return $wpjobportal_name;
}


add_filter('woocommerce_get_return_url','wpjobportal_override_return_url',10,2);

function wpjobportal_override_return_url($return_url,$order){
    //create empty array to store url parameters in
    $sku_list = array();
    $wpjobportal_id = wpjobportal::wpjobportal_sanitizeData(WPJOBPORTALrequest::getVar('billing_wpjobportal_mid','post') );
    // the block checkout posts no billing_wpjobportal_mid; the order has it from the cart line
    if (empty($wpjobportal_id) && is_a($order, 'WC_Order')) {
        $wpjobportal_id = wpjobportal_get_order_perlisting($order);
    }
    // retrive products in order
    if(isset($order)){
        foreach($order->get_items() as $wpjobportal_key => $wpjobportal_item){
          $wpjobportal_product = wc_get_product($wpjobportal_item['product_id']);
          //get sku of each product and insert it in array
          $sku_list['product_'.$wpjobportal_item['product_id'] . 'sku'] = $wpjobportal_product->get_sku();
        }
        //build query strings out of the SKU array
        $wpjobportal_url_extension = http_build_query($sku_list);
        //append our strings to original url
        //append id perlisting moduleid
        $wpjobportal_modified_url = $return_url.'&'.$wpjobportal_url_extension.'&id='.$wpjobportal_id;
        return $wpjobportal_modified_url;
    }
}

function wpjobportal_cart_variation_description( $title, $cart_item, $cart_item_key ) {
    $wpjobportal_item = $cart_item['data'];

    if(!empty($wpjobportal_item) && $wpjobportal_item->is_type( 'variation' ) ) {
        return $wpjobportal_item->get_name();
    } else
        return $title;
}

# Member
add_action('woocommerce_payment_complete', 'wpjobportal_woocommerce_payment_complete');
add_filter( 'woocommerce_cart_item_name', 'wpjobportal_cart_variation_description', 20, 3);
# Per listing
add_action('woocommerce_payment_complete', 'wpjobportal_woocommerce_paymentperlisting_complete');
add_action('woocommerce_order_status_pending', 'wpjobportal_woocommerce_payment_pending');
add_action('woocommerce_order_status_failed', 'wpjobportal_woocommerce_payment_failed');
add_action('woocommerce_order_status_on-hold', 'wpjobportal_woocommerce_payment_hold');
// Note that it's woocommerce_order_status_on-hold, not on_hold.
add_action('woocommerce_order_status_processing', 'wpjobportal_woocommerce_payment_processing');
add_action('woocommerce_order_status_completed', 'wpjobportal_woocommerce_payment_completed');
add_action('woocommerce_order_status_completed', 'wpjobportal_woocommerce_paymentperlisting_completed');
add_action('woocommerce_order_status_refunded', 'wpjobportal_woocommerce_payment_refunded');
add_action('woocommerce_order_status_cancelled', 'wpjobportal_woocommerce_payment_cancelled');

// subscription code

/*
 * The script that reveals the package selector belongs to the product editor
 * and nowhere else. It used to be asked for on every admin page, against a
 * path that has never held a file, so every screen in wp-admin carried a 404
 * for it.
 */
add_action('admin_enqueue_scripts', function($wpjobportal_hook){
    if (!in_array($wpjobportal_hook, array('post.php', 'post-new.php'), true)) {
        return;
    }
    $wpjobportal_screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$wpjobportal_screen || $wpjobportal_screen->post_type !== 'product') {
        return;
    }
    // Bind after WooCommerce's own product meta box script, so its first
    // show/hide pass has run before ours reads what is visible.
    $wpjobportal_deps = array('jquery');
    if (wp_script_is('wc-admin-product-meta-boxes', 'registered')) {
        $wpjobportal_deps[] = 'wc-admin-product-meta-boxes';
    }
    wp_enqueue_script('wpjobportalsubscriptionadminjs', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/wc_admin.js', $wpjobportal_deps, wpjobportal_asset_version('includes/js/wc_admin.js'), true);
});

/*
 * WooCommerce keys a product type option by the meta name without its leading
 * underscore - 'virtual' for _virtual - and reads the tick back from
 * '_' . <key>. Keyed as is_wpjobportalsubscription, it went looking for
 * _is_wpjobportalsubscription, which nothing has ever written, so the checkbox
 * came up empty on every reload however the product was saved. The key now
 * matches the id, and so matches the meta the save below writes.
 */
add_filter('product_type_options', 'wpjobportalsubscription_checkbox');
    function wpjobportalsubscription_checkbox($wpjobportal_options){
        $wpjobportal_options['wpjobportalsubscription'] = array(
            'id'            => '_wpjobportalsubscription',
            'wrapper_class' => 'show_if_subscription',
            'label'         => __('WP Job Portal', 'wp-job-portal'),
            'description'   => __('Check if you want to use WP Job Portal packages', 'wp-job-portal'),
            'default'       => 'no',
        );
        return $wpjobportal_options;
    }

add_action('woocommerce_product_options_general_product_data', 'wpjobportal_subscription_packages');
    function wpjobportal_subscription_packages(){
        global $post;
        
        $query = "SELECT id,title,price FROM `" . wpjobportal::$_db->prefix . "wj_portal_packages` WHERE status = 1 AND isfree = 0";
        $wpjobportal_result = wpjobportal::$_db->get_results($query);
        $wpjobportal_packagepack_field_id = '';
        $wpjobportal_packagepack_field_id = esc_attr(get_post_meta($post->ID, 'wpjobportal_packagepack_field', true));
        $wpjobportal_options = '<option value="">'.esc_html(__('Select Package', 'wp-job-portal')).'</option>';
        //parse the packages packs
        if ($wpjobportal_result && is_array($wpjobportal_result)) {
             foreach ($wpjobportal_result AS $wpjobportal_pack) {
                $wpjobportal_selected = selected($wpjobportal_packagepack_field_id, $wpjobportal_pack->id, false);
                $wpjobportal_options .= '<option value="'.esc_attr($wpjobportal_pack->id).'" '.$wpjobportal_selected.'>'.esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_pack->title)).'</option>';
            }
        }

        ?>
        <div class="options_group show_if_wpjobportalsubscription hidden">
            <p class="form-field show_if_subscription hidden">
                <label><?php echo esc_html(__("WP Job Portal Packages",'wp-job-portal')); ?></label>
                <span class="wrap">
                    <select id="wpjobportal_packagepack_field_subscription" name="wpjobportal_packagepack_field_subscription" class="select short"><?php echo wp_kses($wpjobportal_options, array('option' => array('value' => true, 'selected' => true))); ?></select>
                    <span class="woocommerce-help-tip" data-tip="<?php echo esc_attr(__("Select packages pack so that user can purchase them",'wp-job-portal')); ?>"></span>
                </span>
            </p>
        </div>
        <?php
    }

//add_action( 'woocommerce_process_product_meta_simple', array($this,'wpjobportal_save_wpjobportalsubscription_option_fields'  ));
//add_action( 'woocommerce_process_product_meta_variable', array($this,'wpjobportal_save_wpjobportalsubscription_option_fields'  ));
add_action( 'woocommerce_process_product_meta_subscription', 'wpjobportal_save_wpjobportalsubscription_option_fields'  );
    function wpjobportal_save_wpjobportalsubscription_option_fields( $post_id ) {
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        $wpjobportal_is_wpjobportalsubscription = WPJOBPORTALrequest::has('_wpjobportalsubscription', 'post') ? 'yes' : 'no';
        $wpjobportal_packagepack_field = absint(WPJOBPORTALrequest::getVar('wpjobportal_packagepack_field_subscription', 'post', 0));
        // One key, the one WooCommerce reads. The second copy this used to
        // write under is_wpjobportalsubscription was never read by anything,
        // and having two names for the tick is what hid the mismatch above.
        update_post_meta( $post_id, '_wpjobportalsubscription', $wpjobportal_is_wpjobportalsubscription );
        $package_is_sellable = $wpjobportal_packagepack_field > 0 && absint(wpjobportal::$_db->get_var(wpjobportaldb::prepare(
            "SELECT COUNT(*) FROM `" . wpjobportal::$_db->prefix . "wj_portal_packages` WHERE id = %d AND status = 1 AND isfree = 0",
            $wpjobportal_packagepack_field
        ))) > 0;
        if ($package_is_sellable) {
            update_post_meta( $post_id, 'wpjobportal_packagepack_field', $wpjobportal_packagepack_field );
        } else {
            delete_post_meta( $post_id, 'wpjobportal_packagepack_field' );
        }
    }

/*
 * The toolbar belongs to WordPress, and to the person signed in.
 *
 * WooCommerce hides it from anybody who cannot edit posts, on the reasonable
 * assumption that such a person is a shop customer with no business in the
 * admin. An employer or a job seeker here is neither: they are signed in to
 * their own control panel, and the toolbar is how they reach their profile,
 * their account and the way back out. On a site with WooCommerce installed it
 * disappeared for every portal user - including those whose own WordPress
 * setting says to show it - and on a site without WooCommerce it was there,
 * which made it look like a fault in this plugin.
 *
 * So WooCommerce is asked, through the filter it provides for exactly this, to
 * leave portal users alone. Nothing is forced on: WordPress then decides as it
 * always would, from the person's own "Show Toolbar when viewing site"
 * setting. A site that wants it gone for everyone can say so:
 *
 *     add_filter('wpjobportal_allow_admin_bar', '__return_false');
 */
add_filter('woocommerce_disable_admin_bar', 'wpjobportal_keep_admin_bar_for_portal_users');

function wpjobportal_keep_admin_bar_for_portal_users($disable) {
    if (!$disable || is_admin() || !is_user_logged_in()) {
        return $disable;
    }
    if (!apply_filters('wpjobportal_allow_admin_bar', true)) {
        return $disable;
    }
    if (!class_exists('WPJOBPORTALincluder')) {
        return $disable;
    }
    $wpjobportal_user = WPJOBPORTALincluder::getObjectClass('user');
    if (!is_object($wpjobportal_user)) {
        return $disable;
    }
    $wpjobportal_is_portal_user = $wpjobportal_user->isemployer() || $wpjobportal_user->isjobseeker();

    /*
     * Somebody can belong here without holding either role. A recruiter on a
     * company's team has no portal role of their own, and answers no to both
     * questions above, so the add-on that knows about teams is asked too.
     */
    $wpjobportal_is_portal_user = (bool) apply_filters(
        'wpjobportal_is_portal_user',
        $wpjobportal_is_portal_user,
        get_current_user_id()
    );

    return $wpjobportal_is_portal_user ? false : $disable;
}

