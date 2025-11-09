<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}



add_action('add_meta_boxes', 'add_custom_meta_box');

function add_custom_meta_box() {
    add_meta_box(
        'custom_meta_box_id', // Unique ID
        'MoboCore Actions', // Box title
        'add_custom_button', // Content callback
        'product', // Post type
        'side', // Context (where to show)
        'default' // Priority
    );
}

function add_custom_button($post) {
    // Check if product_guid exists
    $product_guid = get_post_meta($post->ID, 'product_guid', true);

    // Only display button if product_guid exists
    if (!empty($product_guid)) {
        echo '<button type="button" class="button mobo-resync-button" id="mobo-resync-button" data-product-id="' . esc_attr($product_guid) . '">همگام سازی</button>';
    } else {
        echo '<p>No product GUID found.</p>';
    }
}


add_action('admin_enqueue_scripts', 'enqueue_admin_mobo_script');

function enqueue_admin_mobo_script() {
    wp_enqueue_script('mobo-admin-script', MOBO_CORE_PLUGIN_URL . 'assets/js/woocommerce-mobo.js', array('jquery'), null, true);
    wp_localize_script('mobo-admin-script', 'moboAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
}

add_action('wp_ajax_mobo_resync_action', 'handle_mobo_resync_action');

function handle_mobo_resync_action() {
    trace_log();
    trace_log($_POST['product_id']);
    $product_id = intval($_POST['product_id']);
    
    $apiFunc = new \MoboCore\ApiFunctions();
    $onlyInStock = intval(get_option('mobo_core_only_in_stock', true));
    $data = $apiFunc->getProductByGuidAsJson($product_id,$onlyInStock);
    trace_log();
    trace_log($product_id);
    $productFunc = new \MoboCore\WooCommerceProductManager(); // Replace with your product function class
    
    $productFunc->update_product($data);
    // Respond back
    wp_send_json_success("ReSync function executed for product ID: " . $product_id);
}