<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
// https://mobomobo.ir/admin/store/products/159500255

function mobo_core_sync_products() {
    $apiFunc = new \MoboCore\ApiFunctions(); // Replace with your API function class
    $productFunc = new \MoboCore\WooCommerceProductManager(); // Replace with your product function class

    // Retrieve stored values
    $page = intval(get_option('mobo_sync_page', 1));
    $productLeft = get_option('mobo_sync_product_left', null);
    $pageSize = 5;

    if (is_null($productLeft)) {
        // Initial setup
        $totalCount =  $apiFunc->getProductsCount();
        if($totalCount === false){
            add_action('admin_notices', function() {
                echo '<div class="error"><p>خطا در دریافت تعداد محصولات</p></div>';
            });
            return;
        }
        $totalCount = intval($totalCount);

        $productLeft = $totalCount;
        update_option('mobo_sync_product_left', $productLeft);
    }
    else{
        $productLeft = intval($productLeft);
    }


    // Process products
    if($productLeft > 0) {
        $productsDataJson = $apiFunc->getProductsAsJson($page, $pageSize);
        if($productsDataJson === false){
            add_action('admin_notices', function() {
                echo '<div class="error"><p>خطا در دریافت محصولات</p></div>';
            });
            return;
        }
        $productFunc->update_product($productsDataJson);

        // Update counters
        $productLeft -= $pageSize;
        $page++;

        // Update options
        update_option('mobo_sync_page', $page);
        update_option('mobo_sync_product_left', $productLeft);
    }

    // Cleanup after completion
    if ($productLeft <= 0) {
        delete_option('mobo_sync_page');
        delete_option('mobo_sync_product_left');
        wp_clear_scheduled_hook('mobo_core_sync_products_event');
    }
}

add_action('mobo_core_sync_products_event', 'mobo_core_sync_products');

// Admin page function
function mobo_core_sync_page() {

    $apiFunc = new \MoboCore\ApiFunctions(); // Replace with your API function class
    $info = $apiFunc->getLicenseInfo();

    if(!mobo_isLicenseExpired()){
    if (isset($_POST['submit'])) {
        // Verify nonce for security
        check_admin_referer('mobo_core_sync_categories');
        
        // Optional: Add an admin notice
        add_action('admin_notices', function() {
            echo '<div class="updated"><p>همگام سازی دسته بندی موبو کور با موفقیت انجام شد!</p></div>';
        });

        $apiFunc = new \MoboCore\ApiFunctions();

        $categoriesDataJson = $apiFunc->getCategoriesAsJson();
        $catFunc = new \MoboCore\WooCommerceCategoryManager();
        $catFunc->addOrUpdateAllCategories($categoriesDataJson);

        // if (!wp_next_scheduled('mobo_core_sync_products_event')) {
        //     wp_schedule_event(time(), 'mobo_core_interval', 'mobo_core_sync_products_event');
        // }
    }
    ?>
        <p>
            <?php echo $info['message']; ?>
            <br />
            <?php echo $info['timeLeft']; ?>
        </p>
        <form method="post" action="">
            <?php wp_nonce_field('mobo_core_sync_categories'); ?>
            <?php submit_button('همگام سازی'); ?>
        </form>
    <?php
    }
else{
    ?>
    <p>
        <?php echo $info['message']; ?>
        <br />
        <?php echo $info['timeLeft']; ?>
    </p>
    <?php
}
}