<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Admin page function
function mobo_core_admin_page()
{
    $max_dynamic = 1;
    // Check if the form is submitted
    $message = '';
    if (isset($_POST['save_mobo_core_settings'])) {
        update_option('mobo_core_token', trim($_POST['token']));
        update_option('mobo_core_security_code', trim($_POST['SecurityCode']));



        $global_product_auto_stock = isset($_POST['global_product_auto_stock']) ? 1 : 0;
        $global_product_auto_price =   isset($_POST['global_product_auto_price']) ? 1 : 0;
        $global_product_auto_title =   isset($_POST['global_product_auto_title']) ? 1 : 0;
        $global_product_auto_caption = isset($_POST['global_product_auto_caption']) ? 1 : 0;
        $global_product_auto_slug = isset($_POST['global_product_auto_slug']) ? 1 : 0;

        $global_product_auto_compare_price = isset($_POST['global_product_auto_compare_price']) ? 1 : 0;


        update_option('global_product_auto_stock', $global_product_auto_stock);
        update_option('global_product_auto_price', $global_product_auto_price);
        update_option('global_product_auto_title', $global_product_auto_title);
        update_option('global_product_auto_caption', $global_product_auto_caption);
        update_option('global_product_auto_slug', $global_product_auto_slug);

        update_option('global_product_auto_compare_price', $global_product_auto_compare_price);

        $message = '<div class="updated"><p>تنظیمات موبوکور ذخیره شده</p></div>';
    } else {
        $global_product_auto_stock = get_option('global_product_auto_stock');
        $global_product_auto_price = get_option('global_product_auto_price');
        $global_product_auto_title = get_option('global_product_auto_title');
        $global_product_auto_caption = get_option('global_product_auto_caption');
        $global_product_auto_slug = get_option('global_product_auto_slug');

        $global_product_auto_compare_price = get_option('global_product_auto_compare_price');
    }

    if (isset($_POST['save_mobo_core_price'])) {
        $global_additional_price = trim($_POST['global_additional_price']);
        $global_additional_percentage = trim(trim($_POST['global_additional_percentage']), '%');
        $mobo_price_type = trim($_POST['mobo_price_type']);


        update_option('global_additional_price',$global_additional_price );
        update_option('global_additional_percentage', $global_additional_percentage);
        update_option('mobo_price_type', $mobo_price_type);

        $json = json_encode($_POST['dynamic_condition']);
        update_option('mobo_dynamic_price', $json);

        // switch ($mobo_price_type) {
        //     case 'static-price':
                
        //         break;
        //     case 'static-percentage':
                
        //         break;
        //     case 'dynamic-price':
                
        //         break;
        // }

    } else {

        $dynamic_condition = [];
        for ($i = 0; $i < $max_dynamic; $i++) {
            $dynamic_condition[] = [
                'low' => 0,
                'high' => 100_000,
                'benefit_type' => 'static',
                'benefit' => 20_000
            ];
        }

        $global_additional_price = get_option('global_additional_price');
        $global_additional_percentage = get_option('global_additional_percentage');
        $mobo_price_type = get_option('mobo_price_type', 'static-price');

        $persisted_dynamic_condition = get_option('mobo_dynamic_price');
        if ($persisted_dynamic_condition != false) {
            $dynamic_condition = json_decode($persisted_dynamic_condition,true);
        }
    }


?>

    <div class="wrap" style="
                        display: flex;
                        flex-direction: column;
                        gap: 20px;
                        max-width: 500px;">

        <div style="border: 1px solid #3e3e3e;"
            style="
                        display: flex;
                        flex-direction: column;
                        gap: 20px;
                        max-width: 500px;">

            <h2>تنظیمات اصلی</h2>
            <form method="post" action=""
                style="display: flex;
                            flex-direction: column;
                            gap: 20px;
                            max-width: 500px;
                            padding:10px;">


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
                <label>
                    <input type="checkbox" name="global_product_auto_slug" value="1" <?php checked($global_product_auto_slug, '1'); ?>> بروزرسانی اتوماتیک «آدرس محصول»
                </label>

                <hr />

                <label>
                    <input type="checkbox" name="global_product_auto_compare_price" value="1" <?php checked($global_product_auto_compare_price, '1'); ?>> اعمال تخفیف های موبو
                </label>


                <input type="submit" name="save_mobo_core_settings" value="ذخیره تنظیمات اصلی" class="button button-primary" />
            </form>
        </div>
        <br />
        <br />
        <br />
        <br />

        <style>
            .mobo_input_group {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            .d-none {
                display: none !important;
            }
        </style>

        <div style="border: 1px solid #3e3e3e;"
            style="
                        display: flex;
                        flex-direction: column;
                        gap: 20px;
                        max-width: 500px;">
            <h2>تنظیمات قیمت</h2>

            <form method="post" id="mobo_price_coditions" action=""
                style="display: flex;
                            flex-direction: column;
                            gap: 20px;
                            max-width: 500px;
                            padding:10px;">

                <div>
                    <label for="lbl-dynamic-price">
                        <input type="radio" name="mobo_price_type" id="lbl-dynamic-price" value="dynamic-price" <?php if ($mobo_price_type == "dynamic-price") echo 'checked'; ?>> سیستم قیمت دهی شرطی<br>
                    </label>
                    <label for="lbl-static-percentage">
                        <input type="radio" name="mobo_price_type" id="lbl-static-percentage" value="static-percentage" <?php if ($mobo_price_type == "static-percentage") echo 'checked'; ?>> سیستم قیمت دهی درصد<br>
                    </label>
                    <label for="lbl-static-price">
                        <input type="radio" name="mobo_price_type" id="lbl-static-price" value="static-price" <?php if ($mobo_price_type == "static-price") echo 'checked'; ?>> سیستم قیمت دهی ثابت<br>
                    </label>
                </div>

                <div id="static-price" class="price-mode mobo_input_group <?php if ($mobo_price_type != "static-price") echo 'd-none'; ?>">
                    <label for="global_additional_price">
                        پیش فرض سود به تومان - عدد فقط به انگیسی وارد کنید
                    </label>
                    <input type="text" style="font-family:'Courier New', Courier, monospace;" dir="ltr" name="global_additional_price" id="global_additional_price" value="<?php echo $global_additional_price; ?>" />
                </div>
                <div id="static-percentage" class="price-mode mobo_input_group <?php if ($mobo_price_type != "static-percentage") echo 'd-none'; ?>">
                    <label for="global_additional_percentage">
                        پیش فرض سود به درصد - عدد فقط به انگیسی وارد کنید
                    </label>
                    <input type="text" style="font-family:'Courier New', Courier, monospace;" dir="ltr" name="global_additional_percentage" id="global_additional_percentage" value="<?php echo $global_additional_percentage; ?>" />
                    <small>
                        مثلا برای 20% باید عدد 20 وارد کنید - علامت درصد را وارد نکنید
                    </small>
                </div>
                <div id="dynamic-price" class="price-mode mobo_input_group <?php if ($mobo_price_type != "dynamic-price") echo 'd-none'; ?>">
                    <?php
                    for ($i = 0; $i < 1; $i++) {
                    ?>

                        <div style="border: 1px dashed #3e3e3e; padding:10px;margin:10px">
                            <div class="mobo_input_group">
                                <label for="dynamic_condition[<?php echo $i ?>][low]">
                                    حداقل قیمت:
                                </label>
                                <input type="text" style="font-family:'Courier New', Courier, monospace;" dir="ltr" name="dynamic_condition[<?php echo $i ?>][low]" id="dynamic_condition[<?php echo $i ?>][low]" value="<?php echo $dynamic_condition[$i]['low'] ?>" />
                            </div>
                            <br />

                            <div class="mobo_input_group">
                                <label for="dynamic_condition[<?php echo $i ?>][high]">
                                    حداکثر قیمت:
                                </label>
                                <input type="text" style="font-family:'Courier New', Courier, monospace;" dir="ltr" name="dynamic_condition[<?php echo $i ?>][high]" id="dynamic_condition[<?php echo $i ?>][high]" value="<?php echo $dynamic_condition[$i]['high'] ?>" />
                            </div>
                            <br />

                            <div>
                                <input type="radio" name="dynamic_condition[<?php echo $i ?>][benefit_type]" value="percentage" <?php if ($dynamic_condition[$i]['benefit_type'] == "percentage") echo 'checked'; ?>> درصد<br>
                                <input type="radio" name="dynamic_condition[<?php echo $i ?>][benefit_type]" value="static" <?php if ($dynamic_condition[$i]['benefit_type'] == "static") echo 'checked'; ?>> قیمت ثابت<br>
                            </div>
                            <br />
                            <div class="mobo_input_group">
                                <label for="dynamic_condition[<?php echo $i ?>][benefit]">
                                    پیش فرض سود به انگیسی
                                </label>
                                <input type="text" style="font-family:'Courier New', Courier, monospace;" dir="ltr" name="dynamic_condition[<?php echo $i ?>][benefit]" id="dynamic_condition[<?php echo $i ?>][benefit]" value="<?php echo $dynamic_condition[$i]['benefit'] ?>" />
                            </div>
                        </div>

                    <?php
                    }
                    ?>
                </div>

                <input type="submit" name="save_mobo_core_price" value="ذخیره تنظیمات قیمت" class="button button-primary" />
            </form>
        </div>

    </div>

    <script>
        jQuery(function($) {
            $('input[name="mobo_price_type"]').change(function() {
                var selectedValue = $(this).val();

                switch (selectedValue) {
                    case 'static-price':
                        $('#static-percentage').addClass('d-none');
                        $('#dynamic-price').addClass('d-none');
                        break;
                    case 'static-percentage':
                        $('#static-price').addClass('d-none');
                        $('#dynamic-price').addClass('d-none');

                        break;
                    case 'dynamic-price':
                        $('#static-price').addClass('d-none');
                        $('#static-percentage').addClass('d-none');
                        break;
                }
                $('#' + selectedValue).removeClass('d-none');
            });
        });
    </script>

<?php
}
