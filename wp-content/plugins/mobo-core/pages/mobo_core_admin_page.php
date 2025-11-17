<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Admin page function
function mobo_core_admin_page()
{
    $site_url = get_option('siteurl');
    $max_dynamic = 5;
    // Check if the form is submitted

    $cronjob_message = '';
    // check cronjob is open
    if(defined('DISABLE_WP_CRON')){
        if(!DISABLE_WP_CRON){
            $cronjob_message = '<p class="success">کرون جاب فعال است</p>';
        }
        else{
            $cronjob_message = '<p class="error">کرون جاب غیرفعال است
            <br/>
            همین حالا فعالش کنید!
            <br/>
            wp-config.php > define(\'DISABLE_WP_CRON\', false);
            </p>';
        }
    }

    $max_execution_time = ini_get('max_execution_time');
    $max_input_vars = ini_get('max_input_vars');

    $memory_limit = ini_get('memory_limit');
    $upload_max_filesize = ini_get('upload_max_filesize');
    $post_max_size = ini_get('post_max_size');



    if($max_execution_time < 300){
        echo '
        <p class="error">
            max_execution_time کمتر از ۳۰۰ ثانیه است و ممکن است در ذخیره سازی محصولات مشکل بوجود بیاید، حتما بالای ۳۰۰ بگذارید
            <br/>
            max_execution_time فعلی : '. $max_execution_time .
        '</p>
        کد برای کپی: (php.ini , user.ini)
        <br/>
        <code>
            max_execution_time = 300
        </code>
        ';
    }
    else{
          echo '
        <p class="success">
            max_execution_time فعلی : '. $max_execution_time .
        '</p>
        ';
    }

    if($max_input_vars < 3000){
        echo '
        <p class="error">
            max_input_vars کمتر از ۳۰۰۰ ثانیه است و ممکن است در ذخیره سازی محصولات مشکل بوجود بیاید، حتما بالای ۳۰۰۰ بگذارید
            <br/>
            max_input_vars فعلی : '. $max_input_vars .
        '</p>
        کد برای کپی: (php.ini , user.ini)
        <br/>
        <code>
            max_input_vars = 3000
        </code>
        ';
    }
    else{
          echo '
        <p class="success">
            max_input_vars فعلی : '. $max_input_vars .
        '</p>
        ';
    }


    // --- memory_limit ---
    if ((int)$memory_limit < 256) {
        echo '
        <p class="error">
            memory_limit کمتر از ۲۵۶MB است و ممکن است باعث خطا در ذخیره‌سازی یا پردازش شود.
            <br/>
            memory_limit فعلی : ' . $memory_limit . '
        </p>
        کد برای کپی: (php.ini , user.ini)
        <br/>
        <code>
            memory_limit = 256M
        </code>
        ';
    } else {
        echo '
        <p class="success">
            memory_limit فعلی : ' . $memory_limit . '
        </p>
        ';
    }


    // --- upload_max_filesize ---
    if ((int)$upload_max_filesize < 64) {
        echo '
        <p class="error">
            upload_max_filesize کمتر از ۶۴MB است و ممکن است آپلود فایل‌های حجیم با خطا مواجه شود.
            <br/>
            upload_max_filesize فعلی : ' . $upload_max_filesize . '
        </p>
        کد برای کپی: (php.ini , user.ini)
        <br/>
        <code>
            upload_max_filesize = 64M
        </code>
        ';
    } else {
        echo '
        <p class="success">
            upload_max_filesize فعلی : ' . $upload_max_filesize . '
        </p>
        ';
    }


    // --- post_max_size ---
    if ((int)$post_max_size < 64) {
        echo '
        <p class="error">
            post_max_size کمتر از ۶۴MB است و ممکن است هنگام ارسال داده‌های فرم مشکل ایجاد کند.
            <br/>
            post_max_size فعلی : ' . $post_max_size . '
        </p>
        کد برای کپی: (php.ini , user.ini)
        <br/>
        <code>
            post_max_size = 64M
        </code>
        ';
    } else {
        echo '
        <p class="success">
            post_max_size فعلی : ' . $post_max_size . '
        </p>
        ';
    }

    // end check

    $message = '';
    if (isset($_POST['save_mobo_core_settings'])) {
        update_option('mobo_core_token', trim($_POST['token']));
        update_option('mobo_core_security_code', trim($_POST['SecurityCode']));
        
        $mobo_active_debug = isset($_POST['mobo_active_debug']) ? 1 : 0;

        $mobo_default_category_id = trim($_POST['mobo_default_category_id']);

        $mobo_core_only_in_stock = isset($_POST['mobo_core_only_in_stock']) ? 1 : 0;


        $global_product_auto_stock = isset($_POST['global_product_auto_stock']) ? 1 : 0;
        $global_product_auto_price =   isset($_POST['global_product_auto_price']) ? 1 : 0;
        $global_product_auto_title =   isset($_POST['global_product_auto_title']) ? 1 : 0;
        $global_product_auto_caption = isset($_POST['global_product_auto_caption']) ? 1 : 0;
        $global_product_auto_slug = isset($_POST['global_product_auto_slug']) ? 1 : 0;

        $global_product_auto_compare_price = isset($_POST['global_product_auto_compare_price']) ? 1 : 0;
        $global_update_categories = isset($_POST['global_update_categories']) ? 1 : 0;
        $global_update_images = isset($_POST['global_update_images']) ? 1 : 0;

        update_option('mobo_active_debug', $mobo_active_debug);

        update_option('mobo_core_only_in_stock', $mobo_core_only_in_stock);

        update_option('global_product_auto_stock', $global_product_auto_stock);
        update_option('global_product_auto_price', $global_product_auto_price);
        update_option('global_product_auto_title', $global_product_auto_title);
        update_option('global_product_auto_caption', $global_product_auto_caption);
        update_option('global_product_auto_slug', $global_product_auto_slug);

        update_option('global_product_auto_compare_price', $global_product_auto_compare_price);
        update_option('global_update_categories', $global_update_categories);
        update_option('global_update_images', $global_update_images);

        update_option('mobo_default_category_id', $mobo_default_category_id);


        $message = '<div class="updated"><p>تنظیمات موبوکور ذخیره شده</p></div>';
    } else {
        
        $mobo_active_debug = intval(get_option('mobo_active_debug', false));

        $mobo_core_only_in_stock = intval(get_option('mobo_core_only_in_stock', true));

        $global_product_auto_stock = get_option('global_product_auto_stock');
        $global_product_auto_price = get_option('global_product_auto_price');
        $global_product_auto_title = get_option('global_product_auto_title');
        $global_product_auto_caption = get_option('global_product_auto_caption');
        $global_product_auto_slug = get_option('global_product_auto_slug');

        $global_product_auto_compare_price = get_option('global_product_auto_compare_price');
        $global_update_categories = get_option('global_update_categories');
        $global_update_images = get_option('global_update_images');
        
        $mobo_default_category_id = get_option('mobo_default_category_id');

    }

    if (isset($_POST['save_mobo_core_price'])) {
        $global_additional_price = trim($_POST['global_additional_price']);
        $global_additional_percentage = trim(trim($_POST['global_additional_percentage']), '%');
        $mobo_price_type = trim($_POST['mobo_price_type']);


        update_option('global_additional_price', $global_additional_price);
        update_option('global_additional_percentage', $global_additional_percentage);
        update_option('mobo_price_type', $mobo_price_type);

        $json = json_encode($_POST['dynamic_condition']);
        update_option('mobo_dynamic_price', $json);

        $dynamic_condition = $_POST['dynamic_condition'];
    } else {

        $dynamic_condition = [];
        for ($i = 0; $i < $max_dynamic; $i++) {
            $dynamic_condition[] = [
                'low' => 0,
                'high' => 100_000,
                'benefit_type' => 'static',
                'benefit' => 20_000,
                'is_active' => false
            ];
        }

        $global_additional_price = get_option('global_additional_price');
        $global_additional_percentage = get_option('global_additional_percentage');
        $mobo_price_type = get_option('mobo_price_type', 'static-price');

        $persisted_dynamic_condition = get_option('mobo_dynamic_price');
        if ($persisted_dynamic_condition != false) {
            $dynamic_condition = json_decode($persisted_dynamic_condition, true);
        }
    }


?>

    <style>
        .success{
            color: green;
            font-size: 18px;
        }
        .error{
            color: red;
            font-size: 40px;
        }
    </style>

    <div class="wrap" style="
                        display: flex;
                        flex-direction: column;
                        gap: 20px;
                        max-width: 700px;">

                        <div>
                            <?php echo $cronjob_message; ?>
                        </div>

        <div style="border: 1px solid #3e3e3e;"
            style="
                        display: flex;
                        flex-direction: column;
                        gap: 20px;
                        max-width: 700px;">

            <h2>تنظیمات اصلی</h2>
            <form method="post" action=""
                style="display: flex;
                            flex-direction: column;
                            gap: 20px;
                            max-width: 700px;
                            padding:10px;">


                <?php echo $message; ?>

                <p>
                    لطفا حتما این کد را در cpanel خود وارد کنید تا از لایو موندن اپلیکیشن (وردپرس) اطمینان حاصل شود
                    <br>
                    توضیحات اضافه : در تمام سیستم های هاستینگ برای استفاده صحیح از منابع ، در صورتی که درخواستی از بیرون به سیستم ارسال نشود - نرم افزار خاموش میشود، این کد نرم افزار شما را دوباره زنده میکند
                    <br>
                    <br>
                    لطفا فقط یکی از کرون جاب ها را درج کنید
                    <bdi dir="ltr">
                        Common Settings : Once Per Minute(* * * * *)
                    </bdi>
                    <br>
                    <?php 
                    $php_path = defined( 'TOP_PHP_CLI_PATH' ) && TOP_PHP_CLI_PATH
                        ? TOP_PHP_CLI_PATH
                        : ( PHP_BINARY ?: 'php' );

                    $wp_cron_path = trailingslashit( ABSPATH ) . 'wp-cron.php';
                    $command      = sprintf( '%s %s', $php_path, $wp_cron_path );

                    ?>
                    <code dir="ltr">wget -q -O - <?php echo $site_url; ?>/wp-cron.php?doing_wp_cron >/dev/null 2>&1</code>
                    <br>
                    <code dir="ltr">/usr/local/bin/curl --silent -L "<?php echo $site_url; ?>/wp-cron.php?doing_wp_cron" >/dev/null 2>&1</code>
                    <br>
                    <code dir="ltr"><?php echo $command; ?></code>
                    <br>
                    <code dir="ltr">/usr/local/bin/php <?php echo $wp_cron_path; ?></code>
                </p>


                <label for="token">Token:</label>
                <input type="text" style="font-family:'Courier New', Courier, monospace;" dir="ltr" name="token" id="token" value="<?php echo get_option('mobo_core_token'); ?>" />


                <label for="SecurityCode">Webhook SecurityCode:</label>
                <input type="text" style="font-family:'Courier New', Courier, monospace;" dir="ltr" name="SecurityCode" id="SecurityCode" value="<?php echo get_option('mobo_core_security_code'); ?>" />

                <p>
                    <br>
                    وب هوک سایت شما:
                    <br>
                    <code dir="ltr">
                        <a href="<?php echo $site_url; ?>/index.php?rest_route=/mobo-core/v1/webhook"><?php echo $site_url; ?>/index.php?rest_route=/mobo-core/v1/webhook</a>
                    </code>
                </p>

                <hr />

                <label>
                    <input type="checkbox" name="mobo_active_debug" value="1" <?php checked($mobo_active_debug, '1'); ?>> DEBUG
                </label>

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
                    <input type="checkbox" name="mobo_core_only_in_stock" value="1" <?php checked($mobo_core_only_in_stock, '1'); ?>> فقط محصولات موجود
                </label>

                <label>
                    <input type="checkbox" name="global_product_auto_compare_price" value="1" <?php checked($global_product_auto_compare_price, '1'); ?>> اعمال تخفیف های موبو
                </label>

                <label>
                    <input type="checkbox" name="global_update_categories" value="1" <?php checked($global_update_categories, '1'); ?>> آپدیت اتوماتیک دسته بندی های محصول
                </label>
                
                <label>
                    <input type="checkbox" name="global_update_images" value="1" <?php checked($global_update_images, '1'); ?>> آپدیت اتوماتیک عکس ها محصول
                </label>

                
                <label for="mobo_default_category_id">
                    دسته بندی پیشفرض
                    <input type="text" style="font-family:'Courier New', Courier, monospace;" dir="ltr" name="mobo_default_category_id" id="mobo_default_category_id" value="<?php echo get_option('mobo_default_category_id'); ?>" />
                    <small>
                        در صورتی که نیاز دارید محصولات جدید در دسته ای مشخص لیست شوند - شناسه دسته مورد نظر را در این قسمت قرار دهید
                    </small>
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



        <br>
        <br>
        <br>
        <br>
        <p dir="rtl" style="text-align: right;">
            حالت شرطی کاملا منطقی است یعنی اگر یک مبلغی در رنج مبالغ انتخابی نبود اعمال نمیشود!
            <br>
            برای مثال اگر برای بازه بین ۱۰۰۰ تا ۱۰۰۰۰۰ تومان سود انتخاب کرده باشید و مبلغ محصول در موبو ۱۰۰۰۰۱ بود و در هیچ شرطی قرار نگرفت، مبغ سود نرخ ثابت در نظر گرفته میشود، بنابر این حتما تمام مبالغ مورد نیاز را اعمال کنید و برای مبالغ بالاتر نیز برنامه ریزی داشته باشید
            <br>
            مقایسه به صورت بزرگتر یا کوچتر صورت میگیرد و بزرگتر و مساوی یا کوچکتر و مساوی نیست!
            <br>
            سیستم قیمت دهی ثابت را حتما پر کنید حتی اگر از حالت شرطی استفاده
            <br>
            <bdi dir="ltr">
                1 > price < 10000 => 10,000
            </bdi>
            <br>
            <bdi dir="ltr">
                10000 > price < 20000000 => 20%
            </bdi>
        </p>
        <br>
        <br>

        <div style="border: 1px solid #3e3e3e;"
            style="
                        display: flex;
                        flex-direction: column;
                        gap: 20px;
                        max-width: 500px;">
            <h2 style="
                padding: 10px;
            ">تنظیمات قیمت</h2>

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
                    for ($i = 0; $i < $max_dynamic; $i++) {
                    ?>

                        <div style="border: 1px dashed #3e3e3e; padding:10px;margin:10px">

                            <div>
                                <input type="radio" name="dynamic_condition[<?php echo $i ?>][is_active]" value="true" <?php if ($dynamic_condition[$i]['is_active'] == "true") echo 'checked'; ?>> فعال<br>
                                <input type="radio" name="dynamic_condition[<?php echo $i ?>][is_active]" value="false" <?php if ($dynamic_condition[$i]['is_active'] == "false" || !isset($dynamic_condition[$i]['is_active'])) echo 'checked'; ?>> غیرفعال<br>
                            </div>
                            <br />

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
                                <input type="radio" name="dynamic_condition[<?php echo $i ?>][benefit_type]" value="static" <?php if (!isset($dynamic_condition[$i]['benefit_type']) || $dynamic_condition[$i]['benefit_type'] == "static") echo 'checked'; ?>> قیمت ثابت<br>
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
