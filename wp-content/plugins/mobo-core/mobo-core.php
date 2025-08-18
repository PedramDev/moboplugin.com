<?php
/*
Plugin Name: بروزرسانی موبو کور
Description: بروزرسانی خودکار محصولات از https://mobomobo.ir/
Version: 1.0
Author: Pedram Karimi
Author URI: http://github.com/PedramDev/
// Requires PHP: <=8.1.0
Plugin URI: http://github.com/PedramDev/
Requires WooCommerce: >=6.0
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

define('MOBO_CORE_VERSION', $plugin_data['Version']);
define('MOBO_CORE_PLUGIN_URL',plugin_dir_url(__FILE__));

require 'vendor/autoload.php';
require 'inc/index.php';
require 'pages/index.php';


add_filter('http_request_args', function($args) {
    $args['sslverify'] = false;
    return $args;
});


add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'mobo_core_action_links');

function mobo_core_action_links($links)
{
    $settings_link = '<a href="options-general.php?page=mobo_core_admin">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}

// Add admin menu
add_action('admin_menu', 'mobo_core_admin_menu');

function mobo_core_admin_menu()
{
    add_menu_page('تنظیمات موبو', 'تنظیمات موبو', 'manage_options', 'mobo_core_admin', 'mobo_core_admin_page');
    add_submenu_page('mobo_core_admin','همگام سازی دسته بندی', 'همگام سازی دسته بندی', 'manage_options', 'mobo_core_sync', 'mobo_core_sync_page');
}








function monitor_outbound_requests($response, $request, $url) {
    // Start time tracking
    $start_time = microtime(true);

    // Check if the request is an outbound request
    if (isset($request['timeout'])) {
        // Calculate the execution time
        $execution_time = microtime(true) - $start_time;

        // Define a threshold (in seconds)
        $threshold = 1; // Change this value to your desired threshold

        // Log the request if it exceeds the threshold
        if ($execution_time > $threshold) {
            $timestamp = date('Y-m-d H:i:s');
            $log_entry = sprintf("[%s] Slow Outbound Request to %s - Execution Time: %.2f seconds\n", $timestamp, $url, $execution_time);
            error_log($log_entry, 3, __DIR__ . '/outbound_request_log.log');

            // Optionally block the request (for demonstration purposes)
            // Uncomment the following lines to block it
            // return new WP_Error('request_blocked', 'Request blocked due to slow execution time.');
        }
    }

    return $response;
}

add_filter('pre_http_request', 'monitor_outbound_requests', 10, 3);