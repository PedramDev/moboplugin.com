<?php

add_action('rest_api_init', function () {
    register_rest_route('mobo-core/v1', '/webhook', array(
        'methods' => 'POST',
        'callback' => 'mobo_core_webhook_handler',
        'permission_callback' => '__return_true'
    ));
});

function mobo_core_webhook_handler(WP_REST_Request $request) {

    trace_log();
    $data = $request->get_json_params();

    // Retrieve the X-SEC header
    $security_code = $request->get_header('X-SEC');

    // Define your expected secret code
    $expected_secret_code = get_option('mobo_core_security_code');

    if ($security_code != $expected_secret_code) {
        return new WP_REST_Response('Unauthorized', 401);
        exit;
    }

    trace_log();
    trace_log(json_encode($request->get_json_params()));

    

    // Handle the request data
    $data = $request->get_json_params();

    // Get the current timestamp
    $timestamp = time();

    // Format the timestamp into a readable format if needed
    $formattedTime = date('Y-m-d_H-i-s', $timestamp).'--'. $timestamp;
    // Create the filename
    $filename = "{$formattedTime}.json";

    // Specify the path where you want to save the file
    $filepath = MOBO_CORE_WEBHOOK_FILE_DIR . $filename;

    if(!file_put_contents($filepath, json_encode($data))){
        trace_log();
        trace_log('error to created webhook file.');
    }

    trace_log();

    
    return new WP_REST_Response('Success', 200);
    exit;
}



function mobo_core_read_webhook_interval()
{

    trace_log();

    $directory = MOBO_CORE_WEBHOOK_FILE_DIR;

    $firstFile='';
    // Check if the directory exists
    if (is_dir($directory)) {
        // Scan the directory for files
        $files = scandir($directory);

        // Filter out the current (.) and parent (..) directories
        $files = array_diff($files, ['.', '..']);

        // Filter only .json files
        $jsonFiles = array_filter($files, function($file) {
            return pathinfo($file, PATHINFO_EXTENSION) === 'json';
        });
        
        // Sort the files in ascending order
        sort($jsonFiles);

        // Check if there are any files
        if (!empty($jsonFiles)) {
            // Get the first file
            $firstFile = $directory . $jsonFiles[0];
        } else {
            trace_log('no webhook in the queue');
            return;
        }
    } else {
        trace_log('webhookdir not exist!');
        return;
    }

    
    // Read the file contents
    $jsonData = file_get_contents($firstFile);
    
    // Decode the JSON data into a PHP array
    $data = json_decode($jsonData, true);

    // Check for JSON errors
    if (json_last_error() === JSON_ERROR_NONE) {
        // Successfully decoded JSON
        trace_log();
        trace_log(print_r($data)); // Output the data (or process it as needed)
    } else {
        trace_log();
        trace_log("Error decoding JSON: " . json_last_error_msg());
    }

    $productFunc = new \MoboCore\WooCommerceProductManager(); // Replace with your product function class

    $do_unlink = false;
    // Process the data...
    switch($data['event']){
        case 'ProductUpdated';
            if('Products updated successfully' == $productFunc->webhook_update_product($data)){
                $do_unlink = true;
            }
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

    if($do_unlink){
        if (unlink($firstFile)) {
            trace_log();
            trace_log('webhook unlinked.');
        } else {
            trace_log();
            trace_log("error on webhook unlinked. `$firstFile`");
        }
    }
    else{
        trace_log();
        trace_log("error parse `$firstFile` webhook.");
    }
}