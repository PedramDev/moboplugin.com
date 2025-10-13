<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
// https://mobomobo.ir/admin/store/products/159500255

function mobo_core_sync_categories()
{
    trace_log();
    $lockFile = __DIR__ . '/temp/mobo_cats_sync_lock'; // Temporary lock file path

    // Check if the lock file exists
    if (file_exists($lockFile)) {
        add_action('admin_notices', function () {
            echo '<div class="error"><p>عملیات هم‌زمان مجاز نیست.</p></div>';
        });
        trace_log('عملیات هم‌زمان مجاز نیست.');
        return; // Exit if the function is already running
    }

    // Create a lock file
    $lockCreated = file_put_contents($lockFile, "locked");
    if ($lockCreated == false) {
        trace_log();
    }
    trace_log();

    try {

        $global_update_categories = get_option('global_update_categories');

        if ($global_update_categories == '1') {
            $apiFunc = new \MoboCore\ApiFunctions();
            $categoriesDataJson = $apiFunc->getCategoriesAsJson();
            $catFunc = new \MoboCore\WooCommerceCategoryManager();
            $catFunc->addOrUpdateAllCategories($categoriesDataJson);
            trace_log();
        }
        trace_log();
    } finally {
        // Remove the lock file
        trace_log();
        if (file_exists($lockFile))
            unlink($lockFile);
        trace_log();
    }
}

function mobo_core_sync_products()
{
    $lockFile = __DIR__ . '/temp/mobo_prod_sync_lock'; // Temporary lock file path
    trace_log();

    // Check if the lock file exists
    if (file_exists($lockFile)) {
        add_action('admin_notices', function () {
            echo '<div class="error"><p>عملیات هم‌زمان مجاز نیست.</p></div>';
        });
        trace_log();
        return; // Exit if the function is already running
    }

    // Create a lock file
    $lockCreated = file_put_contents($lockFile, "locked");
    if ($lockCreated == false) {
        trace_log();
    }

    try {
        trace_log();

        $apiFunc = new \MoboCore\ApiFunctions(); // Replace with your API function class
        $productFunc = new \MoboCore\WooCommerceProductManager(); // Replace with your product function class

        // Retrieve stored values
        $page = intval(get_option('mobo_sync_page', 1));
        $productLeft = get_option('mobo_sync_product_left', null);
        $mobo_core_page_size = intval(get_option('mobo_core_page_size', 5));
        $onlyInStock = intval(get_option('mobo_core_only_in_stock', true));
        trace_log();

        if (is_null($productLeft)) {
            trace_log();
            // Initial setup
            $totalCount =  $apiFunc->getProductsCount($onlyInStock);
            if ($totalCount === false) {
                add_action('admin_notices', function () {
                    echo '<div class="error"><p>خطا در دریافت تعداد محصولات</p></div>';
                });
                return;
            }
            $totalCount = intval($totalCount);

            $productLeft = $totalCount;
            update_option('mobo_sync_product_left', $productLeft);
            trace_log();
        } else {
            $productLeft = intval($productLeft);
            trace_log();
        }


        // Process products
        if ($productLeft > 0) {
            trace_log();
            $productsDataJson = $apiFunc->getProductsAsJson($page, $mobo_core_page_size, $onlyInStock);
            if ($productsDataJson === false) {
                add_action('admin_notices', function () {
                    echo '<div class="error"><p>خطا در دریافت محصولات</p></div>';
                });
                return;
            }
            trace_log(print_r($productsDataJson));
            trace_log();

            $productFunc->update_product($productsDataJson);
            trace_log();

            // Update counters
            $productLeft -= $mobo_core_page_size;
            $page++;

            // Update options
            update_option('mobo_sync_page', $page);
            update_option('mobo_sync_product_left', $productLeft);
            trace_log('mobo_sync_product_left:'.$productLeft);
            trace_log();
        }

        // Cleanup after completion
        if ($productLeft <= 0) {
            trace_log();
            mobo_core_sync_stop();
            trace_log();
        }
    } finally {
        // Remove the lock file
        trace_log();
        if (file_exists($lockFile))
            unlink($lockFile);
        trace_log();
    }
}

add_action('mobo_core_sync_products_event', 'mobo_core_sync_products');
add_action('mobo_core_sync_categories_event', 'mobo_core_sync_categories');


function mobo_core_sync_stop()
{
    trace_log();
    delete_option('mobo_sync_page');
    delete_option('mobo_sync_product_left');
    trace_log();

    $timestamp = wp_next_scheduled('mobo_core_sync_products_event');
    if ($timestamp) {
        trace_log();
        wp_unschedule_event($timestamp, 'mobo_core_sync_products_event');
    }
    trace_log();

    add_action('admin_notices', function () {
        echo '<div class="updated"><p>همگام سازی متوقف شد!</p></div>';
    });
    trace_log();

    global $isSyncActive;
    $isSyncActive = false;
    $lockFile1 = __DIR__ . '/temp/mobo_prod_sync_lock'; // Temporary lock file path
    $lockFile2 = __DIR__ . '/temp/mobo_cats_sync_lock'; // Temporary lock file path
    trace_log();

    if (file_exists($lockFile1))
        unlink($lockFile1);
    trace_log();
    if (file_exists($lockFile2))
        unlink($lockFile2);

    trace_log();
}


// Admin page function
function mobo_core_sync_page()
{
    trace_log();

    $apiFunc = new \MoboCore\ApiFunctions(); // Replace with your API function class
    $info = $apiFunc->getLicenseInfo();
    $page = get_option('mobo_sync_page');
    
    $isSyncActive = false;

    if(!empty($page)){
        $isSyncActive = true;
    }
    else{
        $isSyncActive = false;
        $page = 1;
    }

    $productLeft = get_option('mobo_sync_product_left', null);
    trace_log('mobo_sync_product_left:'.$productLeft);

    // $isSyncActive = false;
    // if (wp_next_scheduled('mobo_core_sync_products_event')) {
    //     $isSyncActive = true;
    // }


    if (!mobo_isLicenseExpired()) {
        $mobo_core_page_size = get_option('mobo_core_page_size', 5);
        if (isset($_POST['submit'])) {
            if (isset($_POST['mobo_core_sync_categories'])) {
                // Verify nonce for security
                check_admin_referer('mobo_core_sync_categories_nounce');

                // Optional: Add an admin notice
                add_action('admin_notices', function () {
                    echo '<div class="updated"><p>همگام سازی دسته بندی موبو کور با موفقیت انجام شد!</p></div>';
                });

                $apiFunc = new \MoboCore\ApiFunctions();

                $global_update_categories = get_option('global_update_categories');
                if ($global_update_categories == '1') {
                    $categoriesDataJson = $apiFunc->getCategoriesAsJson();
                    $catFunc = new \MoboCore\WooCommerceCategoryManager();
                    $catFunc->addOrUpdateAllCategories($categoriesDataJson);
                }


                if (!$isSyncActive) {
                    wp_schedule_event(time(), 'mobo_core_product_interval', 'mobo_core_sync_products_event');

                    $isSyncActive = true;
                }


                if (!wp_next_scheduled('mobo_core_sync_categories_event')) {
                    wp_schedule_event(time(), 'mobo_core_categories_interval', 'mobo_core_sync_categories_event');
                }
            } else if (isset($_POST['mobo_core_update_setting'])) {
                check_admin_referer('mobo_core_update_setting_nounce');

                if (get_option('mobo_core_page_size') != trim($_POST['mobo_core_page_size'])) {
                    //stop sync
                    trace_log('mobo_core_page_size:'. get_option('mobo_core_page_size') . ':' . trim($_POST['mobo_core_page_size']));
                    mobo_core_sync_stop();
                }
                else{
                    $page = intval($_POST['page']);
                    update_option('mobo_sync_page', $page);
                }

                update_option('mobo_core_page_size', intval(trim($_POST['mobo_core_page_size'])));
                $mobo_core_page_size = $_POST['mobo_core_page_size'];

            } else if (isset($_POST['mobo_core_sync_stop'])) {
                check_admin_referer('mobo_core_sync_stop_nounce');
                mobo_core_sync_stop();
            }
        }

        if (is_null($productLeft) || $productLeft == false) {
            // Initial setup
            $onlyInStock = intval(get_option('mobo_core_only_in_stock', true));
            $totalCount =  $apiFunc->getProductsCount($onlyInStock);
            if ($totalCount === false) {
                add_action('admin_notices', function () {
                    echo '<div class="error"><p>خطا در دریافت تعداد محصولات</p></div>';
                });
                return;
            }
            $totalCount = intval($totalCount);

            $productLeft = $totalCount;
            trace_log('mobo_sync_product_left:'.$productLeft);
            update_option('mobo_sync_product_left', $productLeft);
        } else {
            $productLeft = intval($productLeft);
        }
        $productLeft = intval($productLeft);

        // if (wp_next_scheduled('mobo_core_sync_products_event')) {
        //     $isSyncActive = true;
        // } else {
        //     $isSyncActive = false;
        // }


?>
        <p>
            <?php echo $info['message']; ?>
            <br />
            <?php echo $info['timeLeft']; ?>
        </p>
        <hr />
        <div>
            وضعیت همگام سازی :
            <?php
            if (!$isSyncActive) {
                echo '<span style="color:red">غیر فعال</span>';
            } else {
                echo '<span style="color:green">فعال</span>';
            }
            ?>
        </div>
        <hr />

        <p>
            <?php
            $intervals = $productLeft / intval($mobo_core_page_size);
            $totalTimeInSeconds = $intervals * 40;
            $minutes = round(floor($totalTimeInSeconds / 60));
            $seconds = round($totalTimeInSeconds % 60);

            ?>
            تعداد محصول همگام سازی نشده : <?php echo $productLeft; ?>
            <br />
            زمان تقریبی پایان همگام سازی : <?php echo $minutes; ?> دقیقه و <?php echo $seconds; ?> ثانیه
        </p>

        <hr />

        <form method="post" action="">
            <p>
                در صورتی که نیاز دارید از اول همگام سازی صورت بگیرد شماره درخواست را به 1 تغییر دهید
                در غیر این صورت بهتر است به آن کاری نداشته باشید
            </p>
            <input type="hidden" name="mobo_core_update_setting" value="mobo_core_update_setting" />
            <?php wp_nonce_field('mobo_core_update_setting_nounce'); ?>

            <label for="page">شماره درخواست :</label>
            <input type="number" name="page" id="page" step="1" value="<?php echo $page; ?>" />

            <hr />

            <label for="mobo_core_page_size">
                تعداد دریافت محصول در هر درخواست
            </label>
            <input type="number" style="font-family:'Courier New', Courier, monospace;" dir="ltr" name="mobo_core_page_size" id="mobo_core_page_size" value="<?php echo $mobo_core_page_size; ?>" />


            <?php submit_button('بروزرسانی تنظیمات'); ?>
        </form>

        <hr />

        <?php if (!$isSyncActive) { ?>
            <form method="post" action="">
                <input type="hidden" name="mobo_core_sync_categories" value="mobo_core_sync_categories" />
                <?php wp_nonce_field('mobo_core_sync_categories_nounce'); ?>


                <?php submit_button('همگام سازی'); ?>
            </form>
        <?php } ?>

        <?php if ($isSyncActive) { ?>
            <form method="post" action="">
                <input type="hidden" name="mobo_core_sync_stop" value="mobo_core_sync_stop" />
                <?php wp_nonce_field('mobo_core_sync_stop_nounce'); ?>


                <?php submit_button('توقف همگام سازی'); ?>
            </form>
        <?php } ?>
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
