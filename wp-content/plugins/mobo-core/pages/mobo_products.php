<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function custom_product_guid_page() {
    add_menu_page(
        'Mobo Products', // Page title
        'Mobo Products', // Menu title
        'manage_options', // Capability
        'mobo-products',  // Menu slug
        'render_mobo_products_page', // Callback function
        'dashicons-cart', // Icon
        20                // Position
    );
}
add_action('admin_menu', 'custom_product_guid_page');

function render_mobo_products_page() {
    // Handle deletion of selected products
    if (isset($_POST['delete_products'])) {
        if (!empty($_POST['product_ids'])) {
            foreach ($_POST['product_ids'] as $product_id) {
                wp_delete_post($product_id, true);
            }
            echo '<div class="notice notice-success"><p>Selected products have been deleted.</p></div>';
        } else {
            echo '<div class="notice notice-warning"><p>No products selected for deletion.</p></div>';
        }
    }

    // Query parameters
    $paged = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
    $posts_per_page = isset($_GET['posts_per_page']) ? absint($_GET['posts_per_page']) : 10;

    // Query args
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => $posts_per_page,
        'paged'          => $paged,
        'meta_query'     => array(
            array(
                'key'     => 'product_guid',
                'compare' => 'EXISTS',
            ),
        ),
    );

    $products_query = new WP_Query($args);

    echo '<div class="wrap">';
    echo '<h1>Mobo Products</h1>';

    // Single form for both deletion and page size selection
    echo '<form method="post" action="">';
    echo '<table class="widefat fixed striped">';
    echo '<thead><tr><th><input type="checkbox" id="select_all" /></th><th>Product Title</th><th>View in Shop</th></tr></thead>';
    echo '<tbody>';

    if ($products_query->have_posts()) {
        while ($products_query->have_posts()) {
            $products_query->the_post();
            echo '<tr>';
            echo '<td><input type="checkbox" name="product_ids[]" value="' . get_the_ID() . '" /></td>';
            echo '<td><a href="' . get_edit_post_link() . '">' . get_the_title() . '</a></td>';
            echo '<td><a href="' . get_permalink() . '" target="_blank">View</a></td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="3">No products found.</td></tr>';
    }

    echo '</tbody></table>';

    // Pagination
    $total_pages = $products_query->max_num_pages;
    $current_page = max(1, $paged);
    
    echo paginate_links(array(
        'current' => $current_page,
        'total'   => $total_pages,
        'format'  => '?paged=%#%&posts_per_page=' . $posts_per_page,
        'base'    => admin_url('admin.php?page=mobo-products&posts_per_page=' . $posts_per_page . '&paged=%#%'),
    ));

    // Dropdown for posts per page
    echo '<div style="margin-top: 10px;">';
    echo 'Show <select name="posts_per_page" id="posts_per_page">';
    
    foreach ([5, 10, 20, 50,100,200,500,1000,1500,2000] as $size) {
        echo '<option value="' . $size . '"' . selected($posts_per_page, $size, false) . '>' . $size . '</option>';
    }
    
    echo '</select> products per page.';
    echo '</div>';

    echo '<input type="submit" name="delete_products" class="button button-danger" value="Delete Selected Products" />';
    echo '</form>';

    wp_reset_postdata();
    echo '</div>';
}

add_action('admin_footer', function() {
    ?>
    <script>
        document.getElementById('posts_per_page').addEventListener('change', function() {
            const selectedSize = this.value;
            const currentPage = new URLSearchParams(window.location.search).get('paged') || 1;
            const url = new URL(window.location.href);
            url.searchParams.set('posts_per_page', selectedSize);
            url.searchParams.set('paged', currentPage);
            window.location.href = url.toString(); // Redirect to the new URL
        });

        document.getElementById('select_all').onclick = function() {
            const checkboxes = document.getElementsByName('product_ids[]');
            for (const checkbox of checkboxes) {
                checkbox.checked = this.checked;
            }
        };
    </script>
    <?php
});