<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Admin page function
function mobo_core_admin_page() {
    // Check if the form is submitted
    if (isset($_POST['submit'])) {
        // Verify nonce for security
        check_admin_referer('mobo_core_save_data');

        // Sanitize and save the data
        $consumerKey = sanitize_text_field($_POST['consumerKey']);
        $consumerSecret = sanitize_text_field($_POST['consumerSecret']);

        // Save the data in the database (options table)
        update_option('consumerKey', $consumerKey);
        update_option('consumerSecret', $consumerSecret);
        
        // Optional: Add an admin notice
        add_action('admin_notices', function() {
            echo '<div class="updated"><p>Data saved successfully!</p></div>';
        });
    }

    // Get saved values
    $saved_consumerKey = get_option('consumerKey', '');
    $saved_consumerSecret = get_option('consumerSecret', '');
    ?>

    <div class="wrap">
        <h1>تنظیمات موبو کور</h1>

        <p>
            Consumer Key , Consumer Secret
            در بخش <bdi dir="ltr">Woocommerce api قابل ایجاد است</bdi>

            <hr>
            <br />
            <a href="/wp-admin/admin.php?page=wc-settings&tab=advanced&section=keys&create-key=1">برای ساخت جدید کلیک کنید</a>
            <br />
            <a href="<?php echo MOBO_CORE_PLUGIN_URL . 'assets/img/Create Woocommerce API Keys - Step1.JPG'; ?>">برای دیدن عکس راهنما کلیک کنید - گام ۱</a>
            <br />
            <a href="<?php echo MOBO_CORE_PLUGIN_URL . 'assets/img/Create Woocommerce API Keys - Step2.JPG'; ?>">برای دیدن عکس راهنما کلیک کنید - گام ۲</a>
            <br />
            در ضمن این کلید فقط در اختیار شماست و به هیچ عنوان این کلید ها را به کسی ندهید
        </p>


        <form method="post" action="">
            <?php wp_nonce_field('mobo_core_save_data'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="consumerKey">Consumer Key</label></th>
                    <td><input name="consumerKey" type="text" id="consumerKey" value="<?php echo esc_attr($saved_consumerKey); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="consumerSecret">Consumer Secret</label></th>
                    <td><input name="consumerSecret" type="text" id="consumerSecret" value="<?php echo esc_attr($saved_consumerSecret); ?>" class="regular-text"></td>
                </tr>
            </table>
            <?php submit_button('ذخیره'); ?>
        </form>
    </div>

    <?php
}