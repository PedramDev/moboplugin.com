<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_post_mobo_optimize_database', 'mobo_optimize_database');
add_action('admin_post_mobo_delete_all_prod', 'mobo_delete_all_prod');
add_action('admin_post_mobo_core_remCrons', 'mobo_core_remCrons');
add_action('admin_post_mobo_core_fixImages', 'mobo_core_fixImages');
add_action('admin_post_mobo_core_remProdDesc', 'mobo_core_remProdDesc');

function mobo_optimize_database()
{
    if (!current_user_can('administrator')) {
        wp_die('You are not allowed to perform this action.');
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
    if (!current_user_can('administrator')) {
        wp_die('You are not allowed to perform this action.');
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
    if (!current_user_can('administrator')) {
        wp_die('You are not allowed to perform this action.');
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
    if (!current_user_can('administrator')) {
        wp_die('You are not allowed to perform this action.');
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

    if (!current_user_can('administrator')) {
        wp_die('You are not allowed to perform this action.');
    }

    global $wpdb;
    $table_prefix = $wpdb->prefix;  // Get the table prefix
    // Optimize the table
    
    $wpdb->query(
        "DELETE FROM {$table_prefix}options WHERE option_name = 'cron';"
    );
    
    trace_log();
    // Redirect with a success message
    wp_redirect(wp_get_referer());
    exit;
    trace_log();
}


function mobo_core_fixImages() {
    $apiFunc     = new \MoboCore\ApiFunctions();               // getImageByGuid()
    $productFunc = new \MoboCore\WooCommerceProductManager();  // fetch_image_data()

    trace_log();
    trace_log('mobo_core_fixImages');

    if (!current_user_can('administrator')) {
        wp_die('You are not allowed to perform this action.');
    }

    global $wpdb;

    $attachments = $wpdb->get_results("
        SELECT p.ID, p.guid, pm.meta_value AS img_guid
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm 
            ON pm.post_id = p.ID 
            AND pm.meta_key = 'img_guid'
        WHERE p.post_type = 'attachment'
    ");

    if (!$attachments) {
        trace_log('No attachments with img_guid found.');
        wp_redirect(wp_get_referer() ? wp_get_referer() : admin_url());
        exit;
    }

    if (!function_exists('wp_generate_attachment_metadata')) {
        require_once ABSPATH . 'wp-admin/includes/image.php';
    }
    if (!function_exists('WP_Filesystem')) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }

    WP_Filesystem();
    global $wp_filesystem;

    $upload_dir = wp_upload_dir();
    if (!empty($upload_dir['error'])) {
        trace_log('Upload dir error: ' . $upload_dir['error']);
        wp_redirect(wp_get_referer() ? wp_get_referer() : admin_url());
        exit;
    }

    if (!method_exists($productFunc, 'fetch_image_data')) {
        trace_log('fetch_image_data() not found on WooCommerceProductManager. Aborting.');
        wp_redirect(wp_get_referer() ? wp_get_referer() : admin_url());
        exit;
    }

    $site_host = parse_url(home_url(), PHP_URL_HOST);

    foreach ($attachments as $att) {
        $attachment_id = (int) $att->ID;
        $img_guid      = $att->img_guid;
        $current_file  = get_attached_file($attachment_id);

        // --- Decide if this attachment needs repair ---
        $needs_repair = false;

        if ($current_file && file_exists($current_file)) {
            if (function_exists('getimagesize')) {
                if (@getimagesize($current_file) !== false) {
                    // Valid image: nothing to do
                    continue;
                }
            } else {
                // Cannot validate -> assume ok
                continue;
            }

            // File exists but not a valid image
            $needs_repair = true;
            trace_log("Attachment {$attachment_id} has invalid file. Will attempt re-download.");
        } else {
            // No file at all
            $needs_repair = true;
            trace_log("Attachment {$attachment_id} missing file. Will attempt re-download.");
        }

        if (!$needs_repair) {
            continue;
        }

        // --- Resolve source URL (PREFER API BY GUID) ---
        $image_url = '';

        // 1) From API by img_guid
        if (!empty($img_guid) && method_exists($apiFunc, 'getImageByGuid')) {
            $resolved = trim($apiFunc->getImageByGuid($img_guid));
            if ($resolved && filter_var($resolved, FILTER_VALIDATE_URL)) {
                $image_url = $resolved;
            }
        }

        // 2) Fallback: if guid is a URL AND not clearly local uploads path
        if (!$image_url && filter_var($att->guid, FILTER_VALIDATE_URL)) {
            $guid_host = parse_url($att->guid, PHP_URL_HOST);

            // Only trust guid if it's external or your system really uses remote GUIDs
            if ($guid_host && $guid_host !== $site_host) {
                $image_url = $att->guid;
            }
        }

        if (!$image_url) {
            trace_log("No valid source URL for attachment {$attachment_id} (img_guid: {$img_guid}). Deleting broken attachment.");
            wp_delete_attachment($attachment_id, true);
            continue;
        }

        // --- Fetch strict-validated image data ---
        $image_data = $productFunc->fetch_image_data($image_url);

        if ($image_data === false || empty($image_data)) {
            trace_log("Failed to fetch valid image data for {$image_url} (attachment {$attachment_id}). Deleting broken attachment.");
            wp_delete_attachment($attachment_id, true);
            continue;
        }

        // --- Build safe filename ---
        $path      = parse_url($image_url, PHP_URL_PATH);
        $pathinfo  = pathinfo($path ?: '');
        $basename  = !empty($pathinfo['filename']) ? $pathinfo['filename'] : ('image-' . $attachment_id);
        $ext       = !empty($pathinfo['extension']) ? $pathinfo['extension'] : 'jpg';
        $filename  = wp_unique_filename($upload_dir['path'], $basename . '.' . $ext);
        $file_path = trailingslashit($upload_dir['path']) . $filename;

        // --- Write file ---
        $written = false;

        if ($wp_filesystem && $wp_filesystem->put_contents($file_path, $image_data, FS_CHMOD_FILE)) {
            $written = true;
        } elseif (file_put_contents($file_path, $image_data) !== false) {
            $written = true;
        }

        if (!$written || !file_exists($file_path)) {
            trace_log("Failed to write file for {$image_url} ({$file_path}) for attachment {$attachment_id}. Deleting broken attachment.");
            wp_delete_attachment($attachment_id, true);
            continue;
        }

        // --- Validate MIME ---
        $filetype = wp_check_filetype($filename, null);
        if (empty($filetype['type'])) {
            trace_log("Invalid mime for {$file_path}. Deleting broken attachment {$attachment_id}.");
            wp_delete_attachment($attachment_id, true);
            continue;
        }

        // --- Update existing attachment to use new real file ---
        update_attached_file($attachment_id, $file_path);

        wp_update_post([
            'ID'             => $attachment_id,
            'post_mime_type' => $filetype['type'],
            'guid'           => trailingslashit($upload_dir['url']) . $filename,
        ]);

        $metadata = wp_generate_attachment_metadata($attachment_id, $file_path);
        if (!is_wp_error($metadata) && !empty($metadata)) {
            wp_update_attachment_metadata($attachment_id, $metadata);
        }

        trace_log("Repaired image for attachment {$attachment_id} from {$image_url}.");
    }

    trace_log('mobo_core_fixImages done');

    wp_redirect(wp_get_referer() ? wp_get_referer() : admin_url());
    exit;
}

function mobo_core_remProdDesc() {
    trace_log();
    trace_log('mobo_core_remProdDesc start');

    // Security check
    if ( ! current_user_can('administrator')) {
        wp_die('You are not allowed to perform this action.');
    }

    // (Optional but recommended) Nonce check if triggered from a custom button/form
    // if ( ! isset($_GET['_wpnonce']) || ! wp_verify_nonce($_GET['_wpnonce'], 'mobo_core_remProdDesc') ) {
    //     wp_die('Invalid nonce.');
    // }

    // Query products that have 'product_guid' meta key
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1, // OK for small/mid catalogs; use batching for huge ones
        'meta_query'     => array(
            array(
                'key'     => 'product_guid',
                'compare' => 'EXISTS',
            ),
        ),
        'fields' => 'ids',
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        foreach ($query->posts as $product_id) {
            wp_update_post(array(
                'ID'           => $product_id,
                'post_content' => '',
                'post_excerpt' => '',
            ));

            trace_log("Cleared description for product ID: $product_id");
        }

        trace_log('mobo_core_remProdDesc completed');

        // Optional: store result for an admin notice
        set_transient('mobo_core_remProdDesc_notice', 'Descriptions cleared for all products with product_guid.', 60);
    } else {
        trace_log('No products found with product_guid');
        set_transient('mobo_core_remProdDesc_notice', 'No products found with product_guid.', 60);
    }

    wp_reset_postdata();

    // Redirect back (no echo before this)
    $redirect_url = wp_get_referer() ? wp_get_referer() : admin_url();
    wp_redirect($redirect_url);
    exit;
}



function mobo_core_fixer_page()
{
?>

    <style>
        .container{
            margin: 0 auto;
            padding: 20px 200px;
        }
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
<div class="container">
    
        <!-- <a href="<?php echo admin_url('admin-post.php?action=mobo_optimize_database'); ?>">Optimize Database</a> -->
        <br>
        <br>
        <!-- <a href="<?php echo admin_url('admin-post.php?action=mobo_delete_all_prod'); ?>">Delete All mobo products</a> -->
        <br>
        <br>
        <!-- <a href="<?php echo admin_url('admin-post.php?action=mobo_core_remCrons'); ?>">Remove Cronjobs</a> -->
        <br>
        <br>
        <br>
        <br>
        <a href="<?php echo admin_url('admin-post.php?action=mobo_core_fixImages'); ?>">Fix Images</a>
        <br>
        <br>
        
        <br>
        <br>
        <a href="<?php echo admin_url('admin-post.php?action=mobo_core_remProdDesc'); ?>">حذف توضیحات محصولات موبو</a>
        <br>
        <br>
</div>

<?php
}
?>
