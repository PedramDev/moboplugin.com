<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_post_mobo_optimize_database', 'mobo_optimize_database');
add_action('admin_post_mobo_delete_all_prod', 'mobo_delete_all_prod');
add_action('admin_post_mobo_core_remCrons', 'mobo_core_remCrons');

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

    $wpdb->query(
        "OPTIMIZE TABLE {$table_prefix}posts"
    );

    $wpdb->query(
        "OPTIMIZE TABLE {$table_prefix}postmeta"
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

function mobo_core_remCrons(){
    trace_log();
    trace_log('mobo_core_remCrons');
    if (current_user_can('manage_options')) { // Ensure only admin can run this

        global $wpdb;
        $table_prefix = $wpdb->prefix;  // Get the table prefix
        // Optimize the table
        
        $wpdb->query(
            "DELETE FROM {$table_prefix}options WHERE option_name = 'cron';"
        );
        
        trace_log();
        // Redirect with a success message
        wp_redirect(admin_url('admin.php?page=your_page_slug&message=crons_removed'));
        exit;
    }
    trace_log();
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
}
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

    <a href="<?php echo admin_url('admin-post.php?action=mobo_optimize_database'); ?>">Optimize Database</a>
    <br>
    <br>
    <a href="<?php echo admin_url('admin-post.php?action=mobo_delete_all_prod'); ?>">Delete All mobo products</a>
    <br>
    <br>
    <a href="<?php echo admin_url('admin-post.php?action=mobo_core_remCrons'); ?>">Remove Cronjobs</a>
    <br>
    <br>

<?php

?>
