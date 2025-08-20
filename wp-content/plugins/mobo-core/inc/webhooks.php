<?php

add_action('rest_api_init', function () {
    register_rest_route('mobo-core/v1', '/webhook', array(
        'methods' => 'POST',
        'callback' => 'mobo_core_webhook_handler',
    ));
});

function mobo_core_webhook_handler(WP_REST_Request $request) {

    $security_code = $request->get_header('securityCode');

    // Define your expected secret code
    $expected_secret_code = get_option('mobo_core_security_code');

    if ($security_code !== $expected_secret_code) {
        return new WP_REST_Response('Unauthorized', 401);
    }
    $productFunc = new \MoboCore\WooCommerceProductManager(); // Replace with your product function class

    

    // Handle the request data
    $data = $request->get_json_params();
    // Process the data...
    switch($data['event']){
        case 'ProductUpdated';
            $productFunc->update_product($data['data']);
            break;
        case 'UpdateCategory';
            $apiFunc = new \MoboCore\ApiFunctions();
            $categoriesDataJson = $apiFunc->getCategoriesAsJson();
            $catFunc = new \MoboCore\WooCommerceCategoryManager();
            $catFunc->addOrUpdateAllCategories($categoriesDataJson);
            break;
        case 'ProductRemoved';
            $productFunc->remove_product($data['data']);
            break;
        case 'DeleteCategory';
            $productFunc->remove_product_category($data['data']);
            break;
    }


    
    return new WP_REST_Response('Success', 200);
}
