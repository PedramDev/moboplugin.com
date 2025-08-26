<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
// https://mobomobo.ir/admin/store/products/159500255

function mobo_core_sync_categories()
{
    $apiFunc = new \MoboCore\ApiFunctions();

    $categoriesDataJson = $apiFunc->getCategoriesAsJson();
    $catFunc = new \MoboCore\WooCommerceCategoryManager();
    $catFunc->addOrUpdateAllCategories($categoriesDataJson);
}

function mobo_core_sync_products()
{
    $apiFunc = new \MoboCore\ApiFunctions(); // Replace with your API function class
    $productFunc = new \MoboCore\WooCommerceProductManager(); // Replace with your product function class

    // Retrieve stored values
    $page = 1;//intval(get_option('mobo_sync_page', 1));
    $productLeft = get_option('mobo_sync_product_left', null);
    $pageSize = 5;

    if (is_null($productLeft)) {
        // Initial setup
        $totalCount =  $apiFunc->getProductsCount();
        if ($totalCount === false) {
            add_action('admin_notices', function () {
                echo '<div class="error"><p>خطا در دریافت تعداد محصولات</p></div>';
            });
            return;
        }
        $totalCount = intval($totalCount);

        $productLeft = $totalCount;
        update_option('mobo_sync_product_left', $productLeft);
    } else {
        $productLeft = intval($productLeft);
    }


    // Process products
    if ($productLeft > 0) {
        $productsDataJson = $apiFunc->getProductsAsJson($page, $pageSize);
        if ($productsDataJson === false) {
            add_action('admin_notices', function () {
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
add_action('mobo_core_sync_categories_event', 'mobo_core_sync_categories');

// Admin page function
function mobo_core_sync_page()
{

    $apiFunc = new \MoboCore\ApiFunctions(); // Replace with your API function class
    $info = $apiFunc->getLicenseInfo();
    $page = intval(get_option('mobo_sync_page', 1));
    $productLeft = get_option('mobo_sync_product_left', null);

    if (!mobo_isLicenseExpired()) {
        if (isset($_POST['submit'])) {
            if (isset($_POST['mobo_core_sync_categories'])) {
                // Verify nonce for security
                check_admin_referer('mobo_core_sync_categories_nounce');

                // Optional: Add an admin notice
                add_action('admin_notices', function () {
                    echo '<div class="updated"><p>همگام سازی دسته بندی موبو کور با موفقیت انجام شد!</p></div>';
                });

                $apiFunc = new \MoboCore\ApiFunctions();

                $categoriesDataJson = $apiFunc->getCategoriesAsJson();
                $catFunc = new \MoboCore\WooCommerceCategoryManager();
                $catFunc->addOrUpdateAllCategories($categoriesDataJson);

                if (!wp_next_scheduled('mobo_core_sync_products_event')) {
                    wp_schedule_event(time(), 'mobo_core_product_interval', 'mobo_core_sync_products_event');
                }


                if (!wp_next_scheduled('mobo_core_sync_categories_event')) {
                    wp_schedule_event(time(), 'mobo_core_categories_interval', 'mobo_core_sync_categories_event');
                }
            } else if (isset($_POST['mobo_core_update_setting'])) {
                check_admin_referer('mobo_core_update_setting_nounce');
                $page = $_POST['page'];
                update_option('mobo_sync_page', $page);
            }
        }

        $pageLeft = ($productLeft % $page);
        if($productLeft % $page != 0){
            $pageLeft++;
        }

?>
        <p>
            <?php echo $info['message']; ?>
            <br />
            <?php echo $info['timeLeft']; ?>
        </p>
        <p>
            تعداد همگام سازی شده : <?php echo $page; ?> از <?php echo $pageLeft; ?>
            <br />
            زمان تقریبی پایان همگام سازی : <?php echo ($pageLeft * 30);  ?> ثانیه دیگر
        </p>

        <form method="post" action="">
            <p>
                در صورتی که نیاز دارید از اول همگام سازی صورت بگیرد شماره درخواست را به 1 تغییر دهید 
                در غیر این صورت بهتر است به آن کاری نداشته باشید
            </p>
            <input type="hidden" name="mobo_core_update_setting" value="mobo_core_update_setting" />
            <?php wp_nonce_field('mobo_core_update_setting_nounce'); ?>

            <label for="page">شماره درخواست :</label>
            <input type="number" name="page" id="page" step="1" value="<?php echo $page; ?>" />

            <?php submit_button('بروزرسانی تنظیمات'); ?>
        </form>

        <form method="post" action="">
            <input type="hidden" name="mobo_core_sync_categories" value="mobo_core_sync_categories" />
            <?php wp_nonce_field('mobo_core_sync_categories_nounce'); ?>
            <?php submit_button('همگام سازی'); ?>
        </form>
    <?php
    } else {
    ?>
        <p>
            <?php echo $info['message']; ?>
            <br />
            <?php echo $info['timeLeft']; ?>
        </p>
<?php
    }
}
