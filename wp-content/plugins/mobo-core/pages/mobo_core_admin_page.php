<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Admin page function
function mobo_core_admin_page()
{

    // Check if the form is submitted
    $message = '';
    if (isset($_POST['save_mobo_core_settings'])) {
        update_option('mobo_core_token', trim($_POST['token']));
        update_option('mobo_core_security_code', trim($_POST['SecurityCode']));

        update_option('global_additional_price', trim($_POST['global_additional_price']));


        $global_product_auto_stock = isset($_POST['global_product_auto_stock']) ? 1 : 0;
        $global_product_auto_price =   isset($_POST['global_product_auto_price']) ? 1 : 0;
        $global_product_auto_title =   isset($_POST['global_product_auto_title']) ? 1 : 0;
        $global_product_auto_caption = isset($_POST['global_product_auto_caption']) ? 1 : 0;

        $global_product_auto_compare_price = isset($_POST['global_product_auto_compare_price']) ? 1 : 0;


        update_option('global_product_auto_stock', $global_product_auto_stock);
        update_option('global_product_auto_price', $global_product_auto_price);
        update_option('global_product_auto_title', $global_product_auto_title);
        update_option('global_product_auto_caption', $global_product_auto_caption);

        update_option('global_product_auto_compare_price', $global_product_auto_compare_price);

        $message = '<div class="updated"><p>تنظیمات موبوکور ذخیره شده</p></div>';
    } else {
        $global_product_auto_stock = get_option('global_product_auto_stock');
        $global_product_auto_price = get_option('global_product_auto_price');
        $global_product_auto_title = get_option('global_product_auto_title');
        $global_product_auto_caption = get_option('global_product_auto_caption');

        $global_product_auto_compare_price = get_option('global_product_auto_compare_price');

        $global_additional_price = get_option('global_additional_price');
    }

?>

    <div class="wrap" style="
                        display: flex;
                        flex-direction: column;
                        gap: 20px;
                        max-width: 500px;">
        <form method="post" action=""
            style="display: flex;
                            flex-direction: column;
                            gap: 20px;
                            max-width: 500px;">


            <?php echo $message; ?>


            <label for="token">Token:</label>
            <input type="text" style="font-family:'Courier New', Courier, monospace;" dir="ltr" name="token" id="token" value="<?php echo get_option('mobo_core_token'); ?>" />


            <label for="SecurityCode">Webhook SecurityCode:</label>
            <input type="text" style="font-family:'Courier New', Courier, monospace;" dir="ltr" name="SecurityCode" id="SecurityCode" value="<?php echo get_option('mobo_core_security_code'); ?>" />

            <hr />

            <label>
                <input type="checkbox" name="global_product_auto_stock" value="1" <?php checked($global_product_auto_stock, '1'); ?>> بروزرسانی اتوماتیک «موجودی انبار»
            </label>
            <label>
                <input type="checkbox" name="global_product_auto_price" value="1" <?php checked($global_product_auto_price, '1'); ?>> بروزرسانی اتوماتیک «قیمت»
            </label>
            <label>
                <input type="checkbox" name="global_product_auto_title" value="1" <?php checked($global_product_auto_title, '1'); ?>> بروزرسانی اتوماتیک «عنوان»
            </label>
            <label>
                <input type="checkbox" name="global_product_auto_caption" value="1" <?php checked($global_product_auto_caption, '1'); ?>> بروزرسانی اتوماتیک «محتوا»
            </label>

            <hr />

            <label for="global_additional_price">
                پیش فرض سود به تومان - عدد فقط به انگیسی وارد کنید
            </label>
            <input type="text" style="font-family:'Courier New', Courier, monospace;" dir="ltr" name="global_additional_price" id="global_additional_price" value="<?php echo $global_additional_price; ?>" />

            <label>
                <input type="checkbox" name="global_product_auto_compare_price" value="1" <?php checked($global_product_auto_compare_price, '1'); ?>> اعمال تخفیف های موبو
            </label>

            <input type="submit" name="save_mobo_core_settings" value="ذخیره تنظیمات" class="button button-primary" />
        </form>
    </div>

<?php
}
