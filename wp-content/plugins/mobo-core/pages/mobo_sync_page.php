<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}


// Admin page function
function mobo_core_sync_page() {

    if (isset($_POST['submit'])) {
        // Verify nonce for security
        check_admin_referer('mobo_core_sync_categories');
        
        // Optional: Add an admin notice
        add_action('admin_notices', function() {
            echo '<div class="updated"><p>همگام سازی دسته بندی موبو کور با موفقیت انجام شد!</p></div>';
        });

        $apiFunc = new \MoboCore\ApiFunctions();

        // $categoriesDataJson = $apiFunc->getCategoriesAsJson();
        // $catFunc = new \MoboCore\WooCommerceCategoryManager();
        // $catFunc->addOrUpdateAllCategories($categoriesDataJson);


        // $productsDataJson = $apiFunc->getProductsAsJson();
       $productsDataJson=   json_decode(file_get_contents(__DIR__.'\sample1.json'), true);

        // $productsDataJson = json_decode($body, true);
        $productFunc = new \MoboCore\WooCommerceProductManager();
        // $productFunc->create_variable_product_with_variants();
        $productFunc->update_product($productsDataJson);

    }
    ?>
        <form method="post" action="">
            <?php wp_nonce_field('mobo_core_sync_categories'); ?>
            <?php submit_button('همگام سازی دسته بندی'); ?>
        </form>
    <?php
}