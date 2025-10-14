<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_post_mobo_optimize_database', 'mobo_optimize_database');
add_action('admin_post_mobo_delete_all_prod', 'mobo_delete_all_prod');

function mobo_optimize_database()
{
    if (!current_user_can('delete_posts')) {
        wp_die('You are not allowed to delete posts.');
    }


    global $wpdb;
    $table_prefix = $wpdb->prefix;  // Get the table prefix

    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$table_prefix}options WHERE option_name LIKE %s",
            '_transient_%'
        )
    );

    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$table_prefix}options WHERE option_name LIKE %s",
            '_transient_timeout_%'
        )
    );

    // Optimize the table
    $wpdb->query(
        "OPTIMIZE TABLE {$table_prefix}options"
    );

    wp_redirect(wp_get_referer());
    exit;
}

function mobo_force_delete_product()
{
    // Check if the user has the necessary permissions
    if (!current_user_can('delete_posts')) {
        wp_die('You are not allowed to delete posts.');
    }

    // Get the post ID from the URL
    $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;

    // Call your function to force delete the product
    if ($post_id) {
        //Begin Data Here

        global $wpdb;
        $table_prefix = $wpdb->prefix;  // Get the table prefix

        // Prepare the query with a placeholder
        $dup_delete_query = $wpdb->prepare(
            "DELETE FROM `{$table_prefix}postmeta` WHERE post_id = %d", // Use %d for integer
            $post_id
        );

        // Execute the query
        $result = $wpdb->query($dup_delete_query);


        //End Data Here

        // Redirect back to the previous page or a specific page
        wp_redirect(wp_get_referer());
        exit;
    }
}


function mobo_delete_wrong_attr()
{
    // Check if the user has the necessary permissions
    if (!current_user_can('delete_posts')) {
        wp_die('You are not allowed to delete posts.');
    }
    // Call your function to force delete the product
    //Begin Data Here

    global $wpdb;
    $table_prefix = $wpdb->prefix;  // Get the table prefix

    // Prepare the query with a placeholder
    $query = $wpdb->prepare(
        "DELETE FROM `{$table_prefix}postmeta` 
             WHERE post_id IN (
                 SELECT pm.post_id 
                 FROM `{$table_prefix}postmeta` pm
                 WHERE pm.meta_key = 'attr_guid' 
                 AND pm.post_id NOT IN (
                     SELECT p.ID 
                     FROM `{$table_prefix}posts` p
                 )
                 GROUP BY pm.meta_value 
                 HAVING COUNT(pm.post_id) > 1
             );"
    );

    // Execute the query
    $result = $wpdb->query($query);


    //End Data Here

    // Redirect back to the previous page or a specific page
    wp_redirect(wp_get_referer());
    exit;
}


function mobo_delete_all_prod()
{
    if (!current_user_can('delete_posts')) {
        wp_die('You are not allowed to delete posts.');
    }


    global $wpdb;
    $table_prefix = $wpdb->prefix;  // Get the table prefix

    // Prepare the query with a placeholder

    
    global $wpdb;
    $table_prefix = $wpdb->prefix;  // Get the table prefix

    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$table_prefix}options WHERE option_name LIKE %s",
            '_transient_%'
        )
    );

    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$table_prefix}options WHERE option_name LIKE %s",
            '_transient_timeout_%'
        )
    );

    // Optimize the table
    $wpdb->query(
        "OPTIMIZE TABLE {$table_prefix}options"
    );


    $wpdb->query(
    $wpdb->prepare("
        DELETE from {$table_prefix}posts
        where id in (SELECT post_id FROM `{$table_prefix}postmeta` WHERE meta_key like '%_guid');
        "    )
    );

    $wpdb->query(
    $wpdb->prepare("
        DELETE FROM {$table_prefix}postmeta
        WHERE post_id NOT IN (SELECT id FROM {$table_prefix}posts);
        "    )
    );

    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$table_prefix}options WHERE option_name LIKE %s",
            '_transient_%'
        )
    );

    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$table_prefix}options WHERE option_name LIKE %s",
            '_transient_timeout_%'
        )
    );

    // Optimize the table
    $wpdb->query(
        "OPTIMIZE TABLE {$table_prefix}options"
    );

    wp_redirect(wp_get_referer());
    exit;
}


function mobo_core_fixer_page()
{
    global $wpdb;
    $table_prefix = $wpdb->prefix;  // Get the table prefix


    $dup_product = $wpdb->prepare(
        "SELECT post_id 
        FROM `{$table_prefix}postmeta` m 
        WHERE m.meta_key = 'product_guid' 
        AND m.meta_value IN (
            SELECT meta_value 
            FROM `{$table_prefix}postmeta` 
            WHERE meta_key = 'product_guid' 
            GROUP BY meta_value 
            HAVING COUNT(*) > 1
        );"
    );

    $dup_product_results = $wpdb->get_results($dup_product);

    //dup
?>

    <style>
        .table-container {
            margin: 20px 0;
        }

        .table-container table {
            width: 100%;
            border-collapse: collapse;
        }

        .table-container th,
        .table-container td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        .table-container th {
            background-color: #f2f2f2;
        }

        .table-container tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .table-container tr:hover {
            background-color: #eaeaea;
        }
    </style>

<?php /*
    <section>
        محصولات مشابه بخاطر در لیست زیر نمایش داده میشوند - بررسی و پاک کنید تا ۱۲ ساعت بعد دوباره وارد سیستم خواهند شد بصورت سالم
        <br>
        <bdi dir="rtl">
            اگر حذف در دسترس نبود یا نمایش در فروشگاه - از ForceDelete استفاده کنید
        </bdi>
        <br>
        <table class="table-container">
            <tr>
                <th>Post ID</th>
                <th>View</th>
                <th>Edit</th>
                <th>Delete</th>
                <th>Force Delete</th>
            </tr>
            <?php foreach ($dup_product_results as $row): ?>
                <tr>
                    <td><?php echo $row->post_id; ?></td>
                    <td><a href="<?php echo get_permalink($row->post_id); ?>">View</a></td>
                    <td><a href="<?php echo admin_url('post.php?post=' . $row->post_id . '&action=edit'); ?>">Edit</a></td>
                    <td><a href="<?php echo admin_url('post.php?post=' . $row->post_id . '&action=delete'); ?>">Delete</a></td>
                    <td><a href="<?php echo admin_url('admin-post.php?action=force_delete_product&post_id=' . $row->post_id); ?>">Force Delete</a></td>
                </tr>
            <?php endforeach; ?>
        </table>

    </section>

    <section>
        <a href="<?php echo admin_url('admin-post.php?action=mobo_delete_wrong_attr'); ?>">Fix Attr</a>
    </section>

    <hr>
*/ ?>

<a href="<?php echo admin_url('admin-post.php?action=mobo_optimize_database'); ?>">Optimize Database</a>
<br>
<a href="<?php echo admin_url('admin-post.php?action=mobo_delete_all_prod'); ?>">Delete All mobo products</a>

<?php

?>

<?php } ?>