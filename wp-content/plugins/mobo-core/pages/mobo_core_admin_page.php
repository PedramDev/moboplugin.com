<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Admin page function
function mobo_core_admin_page() {
    // Check if the form is submitted
    if (isset($_POST['save_mobo_core_settings'])) {
        update_option('mobo_core_token', trim($_POST['token']));
        // Optional: Add an admin notice
        add_action('admin_notices', function() {
            echo '<div class="updated"><p>تنظیمات موبوکور ذخیره شده</p></div>';
        });
    }
    ?>

    <div class="wrap">
        <form method="post" action="">
            <label for="token">Token:</label>
            <input type="text" name="token" id="token" value="<?php echo get_option('mobo_core_token'); ?>" />
            <input type="submit" name="save_mobo_core_settings" value="Save Token" class="button button-primary" />
        </form>
    </div>

    <?php
}