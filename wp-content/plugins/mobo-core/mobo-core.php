<?php
/*
Plugin Name: mobo-core
Description: بروزرسانی خودکار محصولات از https://mobomobo.ir/
Version: 3.4s
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

$plugin_data = get_plugin_data(__FILE__, false, false);
define('MOBO_CORE_VERSION', $plugin_data['Version']);
define('MOBO_CORE_PLUGIN_URL', plugin_dir_url(__FILE__));

require  __DIR__ . '/inc/index.php';
require  __DIR__ . '/pages/index.php';


function trace_log() {
    $backtrace = debug_backtrace();
    $caller = $backtrace[0];
    error_log('Error in file: ' . basename($caller['file']) . ' on line: ' . $caller['line']);
}

add_filter('http_request_args', function ($args) {
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
    add_submenu_page('mobo_core_admin', 'همگام سازی', 'همگام سازی', 'manage_options', 'mobo_core_sync', 'mobo_core_sync_page');
}



add_filter('cron_schedules', 'custom_cron_schedule');
function custom_cron_schedule($schedules)
{
    $schedules['mobo_core_product_interval'] = array(
        'interval' => 40,
        'display'  => 'Every 40 sec',
    );
    $schedules['mobo_core_categories_interval'] = array(
        'interval' => 600,
        'display'  => 'Every 500 sec',
    );
    return $schedules;
}


function mobo_isLicenseExpired()
{
    $apiFunc = new \MoboCore\ApiFunctions(); // Replace with your API function class
    $info = $apiFunc->getLicenseInfo();

    if ($info['isExpired']) {
?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo $info['message']; ?></p>
        </div>
    <?php

        return true;
    } else {
    ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo $info['message']; ?></p>
        </div>
<?php
        return false;
    }
}