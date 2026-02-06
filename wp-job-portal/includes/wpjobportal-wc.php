<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

add_filter('product_type_selector', 'wpjobportal_packages_add_product_type');

function wpjobportal_packages_add_product_type($types) {
    $types['wpjobportal_packages'] = esc_html(__('WP JOB PORTAL Package', 'wp-job-portal'));
    # Per Listing types
    $types['wpjobportal_perlisting'] = esc_html(__('WP JOB PORTAL Perlisting', 'wp-job-portal'));
    return $types;
}

add_action('plugins_loaded', 'wpjobportal_packages_create_custom_product_type');

function wpjobportal_packages_create_custom_product_type() {
    if (!class_exists('WC_Product')) {
        return;
    }
    class WC_Product_Wpjobportal_packages extends WC_Product {

        public $product_type = '';
        public function __construct($wpjobportal_product) {
            $this->product_type = 'wpjobportal_packages';
            parent::__construct($wpjobportal_product);
            // add additional functions here
        }
    }

    class WC_Product_Wpjobportal_perlisting extends WC_Product {

        public function __construct($wpjobportal_product) {
            $this->product_type = 'wpjobportal_perlisting';
            parent::__construct($wpjobportal_product);
            // add additional functions here
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

// add the settings under ‘General’ sub-menu
add_action('woocommerce_product_options_general_product_data', 'wpjobportal_packages_add_custom_settings');

function wpjobportal_packages_add_custom_settings() {
    global $woocommerce, $post;
    echo '<div id="wpjobportal_packages_custom_product_option" class="options_group">';

    // get all packages packs
    $query = "SELECT id,title,price FROM `" . wpjobportal::$_db->prefix . "wj_portal_packages` WHERE status = 1";
    $wpjobportal_result = wpjobportal::$_db->get_results($query);
	$wpjobportal_packagepack_fieldvalue = get_post_meta($post->ID, 'wpjobportal_packagepack_field', true);

    //parse the packages packs
    if ($wpjobportal_result && is_array($wpjobportal_result)) {
        $wpjobportal_options = array('' => esc_html(__('Select Package', 'wp-job-portal')));
        $wpjobportal_fielddata = '';
        $wpjobportal_i = 0;
         foreach ($wpjobportal_result AS $wpjobportal_pack) {
            $wpjobportal_options[$wpjobportal_pack->id] = $wpjobportal_pack->title;
            if($wpjobportal_i != 0) {
                $wpjobportal_fielddata .= '|';
            }
            $wpjobportal_fielddata .= $wpjobportal_pack->id . ':' . $wpjobportal_pack->price;
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
                'label' => esc_html(__('Package combo', 'wp-job-portal')),
                'placeholder' => '',
                'desc_tip' => 'true',
                'description' => esc_html(__('Select packages pack so that user can purchase them.', 'wp-job-portal')),
                'type' => 'number',
                'options' => $wpjobportal_options,
                'value' => $wpjobportal_value,
                'custom_attributes' => array('fielddata' => $wpjobportal_fielddata)
    ));



    echo '</div>';
        wp_register_script( 'wpjobportal-inline-handle', '' );
        wp_enqueue_script( 'wpjobportal-inline-handle' );
        $wpjobportal_inline_js_script = '
        jQuery(document).ready(function(){
                jQuery( ".options_group.pricing" ).addClass( "show_if_wpjobportal_packages" ).show();

                jQuery("#product-type").change(function(){
                    var value = jQuery(this).val();
                    if(value == "wpjobportal_packages"){
                        jQuery("div#wpjobportal_packages_custom_product_option").show();
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
                                    jQuery("input#_sale_price").attr("readonly","true");
                                }
                            }
                        }
                    }else{
                        jQuery("div#wpjobportal_packages_custom_product_option").hide();
                        jQuery("input#_regular_price").removeAttr("readonly");
                        jQuery("input#_sale_price").removeAttr("readonly");
                    }
                });
                jQuery("#product-type").change();
                jQuery("select#wpjobportal_packagepack_field").change(function(){
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
    (object) array('id' => 'company_feature_price_perlisting', 'text' => esc_html(__('Feature Company', 'wp-job-portal'))),
    (object) array('id' => 'job_currency_price_perlisting', 'text' => esc_html(__('Add Job', 'wp-job-portal'))),
    (object) array('id' => 'jobs_feature_price_perlisting', 'text' => esc_html(__('Featuer Job', 'wp-job-portal'))),
    (object) array('id' => 'job_resume_price_perlisting', 'text' => esc_html(__('Add Resume', 'wp-job-portal'))),
    (object) array('id' => 'job_featureresume_price_perlisting', 'text' => esc_html(__('Feature Resume', 'wp-job-portal'))),
    (object) array('id' => 'job_department_price_perlisting', 'text' => esc_html(__('Add Department', 'wp-job-portal'))),
    (object) array('id' => 'job_resumesavesearch_price_perlisting', 'text' => esc_html(__('Resume Save Search', 'wp-job-portal'))),
    (object) array('id' => 'job_jobalert_price_perlisting', 'text' => esc_html(__('Job Alert Time ', 'wp-job-portal'))),
    (object) array('id' => 'job_viewcompanycontact_price_perlisting', 'text' => esc_html(__('View Company Contact Detail ', 'wp-job-portal'))),
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
    wp_register_script( 'wpjobportal-inline-handle', '' );
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
                        jQuery("input#_regular_price").removeAttr("readonly");
                        jQuery("input#_sale_price").removeAttr("readonly");
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
    if($order->status != 'completed'){// to avoint duplication of record in purchase history
       return;
    }
    $wpjobportal_user_id = $order->user_id;
    // getting the product detail **
    $wpjobportal_items = $order->get_items();
    $wpjobportal_packagepackids = array();
    $wpjobportal_module_function = '';
    foreach ($wpjobportal_items as $wpjobportal_item) {
        for($wpjobportal_i=1;$wpjobportal_i<=$wpjobportal_item->get_quantity();$wpjobportal_i++){
            $wpjobportal_product_name = $wpjobportal_item['name'];
            $wpjobportal_product_id = $wpjobportal_item['product_id'];
            $wpjobportal_product_variation_id = $wpjobportal_item['variation_id'];
            $wpjobportal_module = get_post_meta($order_id, '_wpjobporta_billing_perlisting', true);
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
    wp_register_script( 'wpjobportal-inline-handle', '' );
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
  function wpjobportal_custom_orders_items_names( $wpjobportal_item_name, $wpjobportal_item ) {
  // Only in thankyou "Order-received" page
        $wpjobportal_id = WPJOBPORTALrequest::getVar('id');
        $wpjobportal_name = wpjobportal::$_common->getProductDesc($wpjobportal_id);
        if(empty($wpjobportal_name)){
            return $wpjobportal_item_name;
        }
    //$wpjobportal_name = $wpjobportal_id;
      if(is_wc_endpoint_url( 'order-received' ))
    # Specific Item Name For a Product
        $wpjobportal_item_name = $wpjobportal_name;
        return $wpjobportal_item_name;
}


add_filter('woocommerce_get_return_url','wpjobportal_override_return_url',10,2);

function wpjobportal_override_return_url($return_url,$order){
    //create empty array to store url parameters in
    $sku_list = array();
    $wpjobportal_id = wpjobportal::wpjobportal_sanitizeData(WPJOBPORTALrequest::getVar('billing_wpjobportal_mid','post') );
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

add_action('admin_enqueue_scripts', function(){
    wp_enqueue_script('wpjobportalsubscriptionadminjs',plugins_url('/', __FILE__).'js/wc_admin.js');
});

add_filter('product_type_options', 'wpjobportalsubscription_checkbox');
    function wpjobportalsubscription_checkbox($wpjobportal_options){
        $wpjobportal_options['is_wpjobportalsubscription'] = array(
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
        
        $query = "SELECT id,title,price FROM `" . wpjobportal::$_db->prefix . "wj_portal_packages` WHERE status = 1";
        $wpjobportal_result = wpjobportal::$_db->get_results($query);
        $wpjobportal_packagepack_field_id = '';
        $wpjobportal_packagepack_field_id = esc_attr(get_post_meta($post->ID, 'wpjobportal_packagepack_field', true));
        //parse the packages packs
        if ($wpjobportal_result && is_array($wpjobportal_result)) {
            $wpjobportal_options = '<option value = "">'.esc_html(__('Select Package', 'wp-job-portal'))."</option>";
             foreach ($wpjobportal_result AS $wpjobportal_pack) {
                if($wpjobportal_packagepack_field_id == $wpjobportal_pack->id) $wpjobportal_selected = "selected"; else $wpjobportal_selected = "";
                $wpjobportal_options .= '<option value = "'.$wpjobportal_pack->id.'"'.$wpjobportal_selected.'>'.wpjobportal::wpjobportal_getVariableValue($wpjobportal_pack->title)."</option>";
            }
        }

        ?>
        <div class="options_group show_if_wpjobportalsubscription hidden">
            <p class="form-field show_if_subscription hidden">
                <label><?php echo esc_html(__("WP Job Portal Packages",'wp-job-portal')); ?></label>
                <span class="wrap">
                    <select id="wpjobportal_packagepack_field_subscription" name="wpjobportal_packagepack_field_subscription" class="select short"> <?php echo esc_html($wpjobportal_options); ?>"</select>
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
        $wpjobportal_is_wpjobportalsubscription = isset( $_POST['_wpjobportalsubscription'] ) ? 'yes' : 'no';
        $wpjobportal_packagepack_field = $_POST['wpjobportal_packagepack_field_subscription'];
        update_post_meta( $post_id, '_wpjobportalsubscription', $wpjobportal_is_wpjobportalsubscription );
        update_post_meta( $post_id, 'is_wpjobportalsubscription', $wpjobportal_is_wpjobportalsubscription );
        update_post_meta( $post_id, 'wpjobportal_packagepack_field', $wpjobportal_packagepack_field );
    }

?>
