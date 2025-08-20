<?php

if (!defined('ABSPATH')) {
    exit;
}
// Add custom meta field to product variation data
add_action('woocommerce_variation_options', 'add_mobo_additional_price_field', 10, 3);

function add_mobo_additional_price_field($loop, $variation_data, $variation) {
    // Custom Field: Text Input
    woocommerce_wp_text_input(array(
        'id' => 'mobo_additional_price[' . $loop . ']',
        'label' => 'سود',
        'description' => 'مبلغی که میخواهید به این محصول اضافه شود به تومان وارد کنید',
        'desc_tip' => true,
        'value' => get_post_meta($variation->ID, 'mobo_additional_price', true),
        'data_type' => 'price'
    ));
}

// Save custom meta field value for variations
add_action('woocommerce_save_product_variation', 'save_mobo_additional_price_field', 10, 2);

function save_mobo_additional_price_field($variation_id, $i) {
    if (isset($_POST['mobo_additional_price'][$i])) {
        $mobo_additional_price = sanitize_text_field($_POST['mobo_additional_price'][$i]);
        update_post_meta($variation_id, 'mobo_additional_price', $mobo_additional_price);
    }
}