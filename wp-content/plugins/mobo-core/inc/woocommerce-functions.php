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
    trace_log('start mobo_resync_action');

    if ( empty($_POST['product_id']) ) {
        wp_send_json_error('Missing product_id');
    }

    $product_id = $_POST['product_id'];
    trace_log('product_id: ' . $product_id);

    $apiFunc = new \MoboCore\ApiFunctions();

    $data = $apiFunc->getProductByGuidAsJson($product_id);
    trace_log('api data received');

    $productFunc = new \MoboCore\WooCommerceProductManager();

    try {
        trace_log('before update_product');

        $productFunc->update_product($data);

        trace_log('after update_product'); // <— if you never see this, we know where it dies

        wp_send_json_success("ReSync function executed for product ID: " . $product_id);
    } catch (\Throwable $e) {
        trace_log('update_product exception: ' . $e->getMessage());
        trace_log($e->getTraceAsString());

        wp_send_json_error([
            'message' => 'update_product failed',
            'error'   => $e->getMessage(),
        ]);
    }
}