<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Admin page function
function mobo_core_admin_page() {
    // Check if the form is submitted
    if (isset($_POST['submit'])) {
        
        // Optional: Add an admin notice
        add_action('admin_notices', function() {
            echo '<div class="updated"><p>Data saved successfully!</p></div>';
        });
    }

    ?>

    <div class="wrap">
        نرم افزار در حال توسعه است
    </div>

    <?php
}