<?php

namespace MoboCore;

class WooCommerceProductManager
{
    public function __construct()
    {
        // Hook to a point where WordPress is fully loaded
        add_action('init', [$this, 'init']);

        // Create a lock file
        $lockDirectory = dirname(__DIR__ . "/tmp"); // Get the directory of the lock file

        // Check if the directory exists, if not, create it
        if (!is_dir($lockDirectory)) {
            mkdir($lockDirectory, 0755, true); // Create the directory with proper permissions
        }
    }

    private function getCategoryUrls($categories)
    {
        $ids = array_map(function ($category) {
            return $category['categoryId'];
        }, $categories);

        return $ids;
    }

    /**
     * Get category woocommerce IDs by third-party IDs
     * @param array $meta_value_array Array of ThirdPartyIds
     * @return array|null Array of WordPress category IDs or null if none found
     */
    private function get_all_product_categories($guids)
    {
        if (!is_array($guids)) {
            return null; // or handle the error as needed
        }

        $args = array(
            'taxonomy' => 'product_cat',
            'meta_query' => array(
                array(
                    'key' => 'category_guid',
                    'value' => $guids,
                    'compare' => 'in'
                )
            ),
            'hide_empty' => false,   // Include empty categories
            'fields' => 'ids' // Only return IDs
        );

        $categories = \get_terms($args);

        // global $wpdb;
        // $lastQ =  $wpdb->last_query;
        if (!empty($categories)) {
            return $categories; // Return the array of category IDs
        }



        return null; // No categories found
    }


    function fetch_image_data($url)
    {
        trace_log("fetch_image_data() called with URL: {$url}");

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            trace_log("Invalid URL -> {$url}");
            return false;
        }

        // Use global WP HTTP API
        if (function_exists('\wp_remote_get')) {
            trace_log("Using wp_remote_get()");

            $response = \wp_remote_get($url, [
                'timeout'     => 20,
                'redirection' => 5,
            ]);

            if (\is_wp_error($response)) {
                trace_log("wp_remote_get() error -> " . $response->get_error_message());
                return false;
            }

            $code = \wp_remote_retrieve_response_code($response);
            trace_log("HTTP response code -> {$code}");

            if ($code !== 200) {
                trace_log("Non-200 response code -> {$code}");
                return false;
            }

            $content_type = \wp_remote_retrieve_header($response, 'content-type');
            trace_log("Content-Type -> " . ($content_type ?: 'none'));

            if (!$content_type || stripos($content_type, 'image/') !== 0) {
                trace_log("Invalid content-type -> {$content_type}");
                return false;
            }

            $body = \wp_remote_retrieve_body($response);
            if (!$body) {
                trace_log("Empty response body");
                return false;
            }

            if (function_exists('getimagesizefromstring')) {
                $info = @getimagesizefromstring($body);
                if ($info === false) {
                    trace_log("getimagesizefromstring() failed — not a valid image");
                    return false;
                } else {
                    trace_log("Image validated successfully ({$info[0]}x{$info[1]})");
                }
            }

            trace_log("Image fetch successful from {$url}");
            return $body;
        }

        // Fallback
        trace_log("wp_remote_get() not available — falling back to cURL");
        $data = self::fetch_image_with_curl($url);

        if ($data === false) {
            trace_log("cURL fetch failed for {$url}");
        } else {
            trace_log("cURL fetch successful for {$url}");
        }

        return $data;
    }

    function fetch_image_with_curl($url)
    {
        trace_log("fetch_image_with_curl() called with URL: {$url}");

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_HEADER         => true,
        ]);

        trace_log("cURL initialized and options set");

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            trace_log("cURL exec failed: {$error}");
            curl_close($ch);
            return false;
        }

        $header_size  = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $body         = substr($response, $header_size);
        $http_code    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        trace_log("HTTP code: {$http_code}");
        trace_log("Content-Type: " . ($content_type ?: 'none'));
        trace_log("Header size: {$header_size}, Body length: " . strlen($body));

        curl_close($ch);

        if ($http_code !== 200) {
            trace_log("Non-200 HTTP response code ({$http_code})");
            return false;
        }

        if (!$content_type || stripos($content_type, 'image/') !== 0) {
            trace_log("Invalid or missing content-type: {$content_type}");
            return false;
        }

        if (!$body) {
            trace_log("Empty response body");
            return false;
        }

        if (function_exists('getimagesizefromstring')) {
            $info = @getimagesizefromstring($body);
            if ($info === false) {
                trace_log("getimagesizefromstring() failed — not a valid image");
                return false;
            } else {
                trace_log("Image validated successfully ({$info[0]}x{$info[1]})");
            }
        }

        trace_log("Image successfully fetched via cURL from {$url}");
        return $body;
    }


    private function upload_image($image_url)
    {
        trace_log("upload_image() called with URL: " . $image_url);

        // Validate URL
        if (!\filter_var($image_url, FILTER_VALIDATE_URL)) {
            trace_log("Invalid image URL: " . $image_url);
            return false;
        }

        if (!function_exists('wp_generate_attachment_metadata')) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }

        $upload_dir = \wp_upload_dir();
        trace_log("Upload directory: " . print_r($upload_dir, true));

        // Ensure upload directory is writable and valid
        if (!empty($upload_dir['error'])) {
            trace_log("Upload dir error: " . $upload_dir['error']);
            return false;
        }

        $image_data = self::fetch_image_data($image_url);
        if ($image_data === false) {
            trace_log("Failed to fetch image data from URL: " . $image_url);
            return false;
        }

        // Create unique and clean filename
        $filename  = \basename(parse_url($image_url, PHP_URL_PATH));
        $filename  = \wp_unique_filename($upload_dir['path'], $filename);
        $file_path = trailingslashit($upload_dir['path']) . $filename;
        trace_log("Resolved file path: " . $file_path);

        // Save file using WordPress filesystem API when possible
        if (!function_exists('WP_Filesystem')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        \WP_Filesystem();
        global $wp_filesystem;

        $file_written = false;
        if ($wp_filesystem && $wp_filesystem->put_contents($file_path, $image_data, FS_CHMOD_FILE)) {
            $file_written = true;
            trace_log("File written using WP_Filesystem: " . $file_path);
        } elseif (\file_put_contents($file_path, $image_data) !== false) {
            $file_written = true;
            trace_log("File written using file_put_contents: " . $file_path);
        }

        if (!$file_written || !file_exists($file_path)) {
            trace_log("File not written or missing: " . $file_path);
            return false;
        }

        // Validate MIME type
        $wp_filetype = \wp_check_filetype($filename, null);
        trace_log("Detected filetype: " . print_r($wp_filetype, true));

        if (empty($wp_filetype['type'])) {
            trace_log("Invalid MIME type for file: " . $filename);
            if (file_exists($file_path)) {
                \unlink($file_path);
            }
            return false;
        }

        // Create attachment entry
        $attachment = [
            'post_mime_type' => $wp_filetype['type'],
            'post_title'     => \sanitize_file_name(pathinfo($filename, PATHINFO_FILENAME)),
            'post_content'   => '',
            'post_status'    => 'inherit',
        ];

        $attachment_id = \wp_insert_attachment($attachment, $file_path);
        if (is_wp_error($attachment_id) || !$attachment_id) {
            trace_log("Attachment insert failed for file: " . $file_path);
            if (file_exists($file_path)) {
                \unlink($file_path);
            }
            return false;
        }

        trace_log("Attachment created successfully. ID: " . $attachment_id);

        // Generate and save attachment metadata
        $attach_data = \wp_generate_attachment_metadata($attachment_id, $file_path);
        if (!is_wp_error($attach_data) && !empty($attach_data)) {
            \wp_update_attachment_metadata($attachment_id, $attach_data);
            trace_log("Attachment metadata generated and updated for ID: " . $attachment_id);
        } else {
            trace_log("Failed to generate attachment metadata for ID: " . $attachment_id);
        }

        trace_log("upload_image() completed successfully. Attachment ID: " . $attachment_id);
        return $attachment_id;
    }


    public function remove_variant($data)
    {
        if (empty($data) || !isset($data['variantId'])) {
            return 'Invalid JSON data';
        }

        $ids = $data['variantId'];

        // Ensure $ids is an array
        if (!is_array($ids)) {
            $ids = [$ids]; // Convert to array if it's not
        }

        global $wpdb;

        // Prepare the placeholders for the SQL query
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));

        // Prepare and execute the SQL query to delete variations
        $query = $wpdb->prepare(
            "DELETE FROM $wpdb->posts
        WHERE ID IN (
            SELECT ID FROM $wpdb->postmeta
            WHERE meta_key = 'variant_guid' AND meta_value IN ($placeholders)
        ) AND post_type = 'product_variation'",
            ...$ids // Unpack the array into the query
        );

        // Execute the query
        $deleted_rows = $wpdb->query($query);

        return $deleted_rows ? "$deleted_rows variants deleted." : 'No variants found.';
    }

    public function remove_product_category($data)
    {
        if (empty($data) || !isset($data['categoryId'])) {
            return 'Invalid JSON data';
        }

        $ids = $data['categoryId'];

        // Ensure $ids is an array
        if (!is_array($ids)) {
            $ids = [$ids]; // Convert to array if it's not
        }

        global $wpdb;

        // Prepare the placeholders for the SQL query
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));

        // Prepare and execute the SQL query to delete product categories
        $query = $wpdb->prepare(
            "DELETE FROM $wpdb->terms
        WHERE term_id IN (
            SELECT term_id FROM $wpdb->termmeta
            WHERE meta_key = 'category_guid' AND meta_value IN ($placeholders)
        )",
            ...$ids // Unpack the array into the query
        );

        // Execute the query
        $deleted_rows = $wpdb->query($query);

        return $deleted_rows ? "$deleted_rows categories deleted." : 'No categories found.';
    }

    //174789827 multi attr
    public function update_product($data)
    {
        return $this->process_product_data($data);
    }

    public function webhook_update_product($data)
    {
        return $this->process_product_data($data);
    }

    //fix
    private function process_product_data($data)
    {
        trace_log();
        if (!$data) {
            return 'Invalid JSON data';
        }

        $auto_options = self::get_global_product_options();
        trace_log();

        trace_log($data);

        foreach ($data['data'] as $product_data) {
            trace_log();

            $product_id = $product_data['productId'];

            $lockFile = __DIR__ . "/tmp/product_lock_{$product_id}.lock"; // Lock file path
            if (file_exists($lockFile)) {
                trace_log();
                return; // Exit if the function is already running
            }

            // Create a lock file
            $lockCreated = file_put_contents($lockFile, "locked");
            if ($lockCreated == false) {
                trace_log();
            }


            try {
                trace_log();

                $stock = $product_data['stock'];
                $price = $product_data['price'];
                $title = $product_data['title'];
                $caption = $product_data['caption'];
                $product_url = $product_data['url'];
                $comparePrice = $product_data['comparePrice'];
                $categories = $product_data['productCategories'];
                $attributes = $product_data['attributes'];
                $variants = $product_data['variants'];
                $images = $product_data['images'];
                $publishDate = $product_data['publishedAt'];
                trace_log();

                $wp_category_ids = $this->prepare_categories($categories);
                trace_log();

                $result = $this->get_or_create_product($product_id, $attributes);
                trace_log();

                $product = $result['product'];

                $setProdDetailResult = $this->set_product_details($product, $result['isNew'], $product_url, $title, $caption, $price, $comparePrice, $stock, $auto_options, $wp_category_ids, $publishDate);
                if ($setProdDetailResult == false) {
                    trace_log('save product aborted!');
                    continue;
                }
                trace_log();

                $wp_product_id = $product->save();
                trace_log('error!!! app stoped!');
                $product->update_meta_data('product_guid', $product_id); // Store GUID

                trace_log();

                if ($result['isNew'] || $auto_options['global_update_images'] == '1') {
                    $this->handle_images($product, $images);
                }
                trace_log();
                $this->update_attributes($product, $attributes, $wp_product_id);

                $product->save();

                $this->update_variants($product, $variants, $auto_options, $wp_product_id);
                trace_log();

                $product->save();
                trace_log();
            } finally {
                // Remove the lock file
                trace_log();
                if (file_exists($lockFile))
                    unlink($lockFile);
                trace_log();
            }
        }

        #region fix options fat
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
            "OPTIMIZE TABLE {$table_prefix}posts"
        );

        $wpdb->query(
            "OPTIMIZE TABLE {$table_prefix}postmeta"
        );
        #endregion


        return 'Products updated successfully';
    }

    //fix
    private function prepare_categories($categories)
    {
        $category_ids = $this->getCategoryUrls($categories);
        return $this->get_all_product_categories($category_ids);
    }

    //fix
    private function get_or_create_product($product_id, $attributes)
    {
        $result = ['isNew' => false, 'product' => null];
        $args = [
            'post_type' => 'product',
            'meta_query' => [
                [
                    'key' => 'product_guid',
                    'value' => $product_id,
                    'compare' => '='
                ]
            ],
            'posts_per_page' => 1,
        ];


        $existing_products = \get_posts($args);
        if (!empty($existing_products)) {
            $result['product'] = \wc_get_product($existing_products[0]->ID);
            $result['isNew'] = false;
        } else {
            $result['isNew'] = true;
            $result['product'] = !empty($attributes) ? new \WC_Product_Variable() : new \WC_Product();
        }

        return $result;
    }

    private function set_product_details($product, $isNew, $product_url, $title, $caption, $price, $comparePrice, $stock, $auto_options, $wp_category_ids, $publishedAt)
    {
        if ($isNew || $auto_options['global_product_auto_title'] == '1') {
            $product->set_name($title);
        }

        //به درخواست موبو - این بخش حذف شد
        // if ($isNew || $auto_options['global_product_auto_caption'] == '1') {
        //     $product->set_description($caption ?? '');
        // }
        if ($isNew) {
            $product->set_description('');
        }


        if ($isNew || $auto_options['global_product_auto_slug'] == '1') {
            $product->set_slug($product_url);
        }

        if ($isNew || $auto_options['global_product_auto_price'] == '1') {
            $this->set_prices($product, $price, $comparePrice, $auto_options);
        }

        if ($isNew || $auto_options['global_product_auto_stock'] == '1') {
            if ($isNew && $auto_options['mobo_core_only_in_stock'] == '1' && $stock === 0) {
                return false;
            }
            $product->set_manage_stock(true);
            $product->set_stock_quantity($stock === null ? 9999 : $stock);
            $product->set_stock_status(($stock > 0 || $stock === null) ? 'instock' : 'outofstock');
        }

        if ($isNew || $auto_options['global_update_categories'] == '1') {

            $mobo_default_category_id = get_option('mobo_default_category_id');
            if (isset($mobo_default_category_id) && !empty($mobo_default_category_id)) {
                $product->set_category_ids([$mobo_default_category_id]);
            } else {
                $product->set_category_ids($wp_category_ids);
            }
        }

        if ($isNew) {
            if (isset($publishedAt) && !empty($publishedAt)) {
                $date_time = \DateTime::createFromFormat('m/d/Y H:i:s', $publishedAt);
                $product->set_date_created($date_time);
            } else {
                $product->set_date_created(current_time('mysql'));
            }
        } else {
            if (isset($publishedAt) && !empty($publishedAt)) {
                $date_time = \DateTime::createFromFormat('m/d/Y H:i:s', $publishedAt);
                $product->set_date_modified($date_time);
            } else {
                $product->set_date_modified(current_time('mysql'));
            }
        }

        return true;
    }


    private function set_conditional_price_without_compare_price($product, $price, $comparePrice,  $auto_options)
    {
        $static_percentage = floatval('1.' . $auto_options['global_additional_percentage']);
        $static_price = intval($auto_options['global_additional_price']);
        $dynamic_condition = json_decode($auto_options['mobo_dynamic_price'], true);

        $price_type = $auto_options['mobo_price_type'];

        $newPrice = $price;
        if (isset($comparePrice) && $comparePrice != null) {
            $newPrice = $comparePrice;
        }

        switch ($price_type) {
            case 'static-price':

                $product->set_regular_price(intval($newPrice) + $static_price);
                $product->set_sale_price('');

                break;
            case 'static-percentage':

                $product->set_regular_price(intval($newPrice) * $static_percentage);
                $product->set_sale_price('');

                break;
            case 'dynamic-price':

                foreach ($dynamic_condition as $condition) {
                    if ($condition['is_active'] == 'true' && $newPrice >= intval($condition['low']) && $newPrice <= intval($condition['high'])) {
                        if ($condition['benefit_type'] == 'static') {
                            $product->set_regular_price(intval($newPrice) + intval($condition['benefit']));
                            $product->set_sale_price('');
                        } else {
                            $product->set_regular_price(intval($newPrice) *  floatval('1.' . $condition['benefit']));
                            $product->set_sale_price('');
                        }
                    }
                }

                break;

            default:
                $product->set_regular_price(intval($newPrice) + $static_price);
                $product->set_sale_price('');
                break;
        }
    }

    private function set_conditional_price_with_compare_price($product, $price, $comparePrice, $auto_options)
    {
        $static_percentage = floatval('1.' . $auto_options['global_additional_percentage']);
        $static_price = intval($auto_options['global_additional_price']);
        $dynamic_condition = json_decode($auto_options['mobo_dynamic_price'], true);

        $price_type = $auto_options['mobo_price_type'];

        switch ($price_type) {
            case 'static-price':

                $product->set_regular_price(intval($comparePrice) + $static_price);
                $product->set_sale_price(intval($price) + $static_price);

                break;
            case 'static-percentage':

                $product->set_regular_price(intval($comparePrice) * $static_percentage);
                $product->set_sale_price(intval($price) * $static_percentage);

                break;
            case 'dynamic-price':

                foreach ($dynamic_condition as $condition) {
                    if ($condition['is_active'] == 'true' && $price >= intval($condition['low']) && $price <= intval($condition['high'])) {
                        if ($condition['benefit_type'] == 'static') {
                            $product->set_regular_price(intval($comparePrice) + intval($condition['benefit']));
                            $product->set_sale_price(intval($price) + intval($condition['benefit']));
                        } else {
                            $product->set_regular_price(intval($comparePrice) * floatval('1.' . $condition['benefit']));
                            $product->set_sale_price(intval($price) *  floatval('1.' . $condition['benefit']));
                        }
                    }
                }

                break;
            default:
                $product->set_regular_price(intval($comparePrice) + $static_price);
                $product->set_sale_price(intval($price) + $static_price);
                break;
        }
    }

    private function set_prices($product, $price, $comparePrice, $auto_options)
    {
        $auto_compare = $auto_options['global_product_auto_compare_price'];

        if (isset($comparePrice) && $auto_compare == '1') {

            self::set_conditional_price_with_compare_price($product, $price, $comparePrice, $auto_options);
        } else {
            self::set_conditional_price_without_compare_price($product, $price, $comparePrice,  $auto_options);
        }
    }

    private function handle_images($product, $images)
    {
        global $wpdb;

        // Start
        trace_log('handle_images(): start');

        // Basic validation
        if (!$product instanceof \WC_Product) {
            trace_log('handle_images(): invalid $product. Got: ' . (is_object($product) ? get_class($product) : gettype($product)));
            return;
        }

        if (!is_array($images)) {
            trace_log('handle_images(): $images is not an array. Got: ' . gettype($images));
            return;
        }

        trace_log('handle_images(): received images: ' . print_r($images, true));

        $image_ids = [];

        if (!empty($images)) {
            foreach ($images as $index => $image) {
                trace_log("handle_images(): processing index {$index}: " . print_r($image, true));

                // Ensure required keys
                if (empty($image['id']) || empty($image['url'])) {
                    trace_log("handle_images(): missing 'id' or 'url' at index {$index}, skipping.");
                    continue;
                }

                // Check if this GUID already exists
                $query = $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = 'img_guid' AND meta_value = %s",
                    $image['id']
                );
                $isImgExist = $wpdb->get_var($query);

                trace_log("handle_images(): guid {$image['id']} exists count: {$isImgExist}");

                if ($isImgExist == 0) {
                    // Upload new image
                    trace_log("handle_images(): uploading new image from URL: {$image['url']}");
                    $image_id = self::upload_image($image['url']);
                    trace_log("handle_images(): upload_image() result: " . var_export($image_id, true));

                    if (!empty($image_id)) {
                        $image_ids[] = $image_id;
                        update_post_meta($image_id, 'img_guid', $image['id']);
                        trace_log("handle_images(): set img_guid {$image['id']} for attachment {$image_id}");
                    } else {
                        trace_log("handle_images(): upload_image() failed for {$image['url']}");
                    }
                } else {
                    trace_log("handle_images(): image with guid {$image['id']} already exists, not re-uploading.");
                }
            }

            trace_log('handle_images(): collected image_ids: ' . print_r($image_ids, true));

            // Set gallery + featured image if we have new uploads
            if (!empty($image_ids)) {
                $product->set_gallery_image_ids($image_ids);
                $featured_id = $image_ids[array_key_last($image_ids)];
                $product->set_image_id($featured_id);
                trace_log("handle_images(): set gallery and featured image_id: {$featured_id}");
            } else {
                // No new uploads; try to reuse last known img_guid
                $last_image = end($images);
                trace_log('handle_images(): no new images uploaded, using fallback last_image: ' . print_r($last_image, true));

                if (!empty($last_image['id'])) {
                    $query = $wpdb->prepare(
                        "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'img_guid' AND meta_value = %s",
                        $last_image['id']
                    );
                    $img_id = $wpdb->get_var($query);
                    trace_log("handle_images(): fallback lookup for guid {$last_image['id']} returned post_id: {$img_id}");

                    if (!empty($img_id)) {
                        $product->set_image_id($img_id);
                        trace_log("handle_images(): set featured image via fallback: {$img_id}");
                    } else {
                        trace_log("handle_images(): no attachment found for fallback guid {$last_image['id']}");
                    }
                } else {
                    trace_log('handle_images(): fallback failed, last_image has no id');
                }
            }
        } else {
            trace_log('handle_images(): empty $images array, nothing to do.');
        }

        trace_log('handle_images(): end');
    }


    private function update_attributes($product, $attributes, $wp_product_id)
    {
        $attribute_data = [];
        foreach ($attributes as $attribute) {
            $values = array_map(fn($value) => $value['value'], $attribute['values']);
            $newAttr = new \WC_Product_Attribute();
            $newAttr->set_name($attribute['name']);
            $newAttr->set_visible(true);
            $newAttr->set_variation(true);
            $newAttr->set_options($values);
            $attribute_data[] = $newAttr;

            \update_post_meta($wp_product_id, 'attr_guid', $attribute['id']);
        }

        $product->set_attributes($attribute_data);
    }

    private function update_variants($product, $variants, $auto_options, $wp_product_id)
    {
        trace_log();
        foreach ($variants as $variant) {
            $existing_variant_id = $this->get_existing_variant_id($product, $variant['variantId']);
            $variation = $existing_variant_id ? new \WC_Product_Variation($existing_variant_id) : new \WC_Product_Variation();
            $variation->set_parent_id($wp_product_id);
            trace_log();
            $this->set_variant_details($existing_variant_id, $variation, $variant, $auto_options);
            $variation->save();
        }
    }

    private function get_existing_variant_id($product, $variant_guid)
    {
        foreach ($product->get_children() as $variation_id) {
            if (\get_post_meta($variation_id, 'variant_guid', true) == $variant_guid) {
                return $variation_id;
            }
        }
        return null;
    }

    private function set_variant_details($existing_variant_id, $variation, $variant, $auto_options)
    {
        trace_log();

        $is_new = $existing_variant_id == 0;
        $auto_price = $auto_options['global_product_auto_price'] == '1';

        if ($is_new || $auto_price) {
            $this->set_variant_prices($existing_variant_id, $variation, $variant, $auto_options);
        }

        if ($variant['stock'] === null) {
            $variation->set_stock_quantity(9999);
            $variation->set_manage_stock(true);
            $variation->set_stock_status('instock');
        } else {
            $variation->set_stock_quantity($variant['stock']);
            $variation->set_manage_stock(true);
            $variation->set_stock_status($variant['stock'] > 0 ? 'instock' : 'outofstock');
        }

        trace_log();

        trace_log(print_r($variant, true));
        foreach ($variant['attributes'] as $attribute) {
            $key = 'attribute_' . \sanitize_title($attribute['name']);
            trace_log("Updating $key with " . $attribute['option']);
            $variation->update_meta_data($key, $attribute['option']);
        }
        $variation->update_meta_data('variant_guid', $variant['variantId']);
    }

    private function set_variant_prices($existing_variant_id, $variation, $variant, $auto_options)
    {
        $auto_compare = $auto_options['global_product_auto_compare_price'];

        $price = $variant['price'];

        $newPrice = $variant['price'];

        if (isset($variant['comparePrice']) && $variant['comparePrice'] != null) {
            $newPrice = $variant['comparePrice'];
        }

        $additional_price = get_post_meta($existing_variant_id, 'mobo_additional_price', true);
        if (isset($additional_price) && !empty($additional_price)) {
            $additional_price = intval($additional_price);


            if (isset($comparePrice) && $auto_compare == '1') {
                $variation->set_regular_price(intval($comparePrice) + $additional_price);
                $variation->set_sale_price(intval($price) + $additional_price);
            } else {
                $variation->set_regular_price(intval($newPrice) + $additional_price);
                $variation->set_sale_price('');
            }
        } else {
            $static_percentage = floatval('1.' . $auto_options['global_additional_percentage']);
            $static_price = intval($auto_options['global_additional_price']);
            $dynamic_condition = json_decode($auto_options['mobo_dynamic_price'], true);

            $price_type = $auto_options['mobo_price_type'];

            switch ($price_type) {
                default:
                case 'static-price':

                    if (isset($comparePrice) && $auto_compare == '1') {
                        $variation->set_regular_price(intval($comparePrice) + $static_price);
                        $variation->set_sale_price($price);
                    } else {
                        $variation->set_regular_price(intval($newPrice) + $static_price);
                        $variation->set_sale_price('');
                    }

                    break;
                case 'static-percentage':

                    if (isset($comparePrice) && $auto_compare == '1') {
                        $variation->set_regular_price(intval($comparePrice) * $static_percentage);
                        $variation->set_sale_price($price);
                    } else {
                        $variation->set_regular_price(intval($newPrice) * $static_percentage);
                        $variation->set_sale_price('');
                    }

                    break;
                case 'dynamic-price':

                    foreach ($dynamic_condition as $condition) {
                        if ($condition['is_active'] == 'true' && $price >= intval($condition['low']) && $price <= intval($condition['high'])) {
                            if ($condition['benefit_type'] == 'static') {

                                if (isset($comparePrice) && $auto_compare == '1') {
                                    $variation->set_regular_price(intval($comparePrice) + intval($condition['benefit']));
                                    $variation->set_sale_price($price);
                                } else {
                                    $variation->set_regular_price(intval($newPrice) + intval($condition['benefit']));
                                    $variation->set_sale_price('');
                                }
                            } else {

                                if (isset($comparePrice) && $auto_compare == '1') {

                                    $variation->set_regular_price(intval($comparePrice) *  floatval('1.' . $condition['benefit']));
                                    $variation->set_sale_price($price);
                                } else {

                                    $variation->set_regular_price(intval($newPrice) *  floatval('1.' . $condition['benefit']));
                                    $variation->set_sale_price('');
                                }
                            }
                        }
                    }
                    break;
            }
        }
    }

    function get_global_product_options()
    {
        $options = [
            'global_product_auto_stock',
            'global_product_auto_price',
            'global_product_auto_title',
            'global_product_auto_caption',
            'global_product_auto_compare_price',
            'global_product_auto_slug',
            'global_update_categories',
            'global_update_images',

            'mobo_core_only_in_stock',
            'mobo_price_type',
            'global_additional_price',
            'global_additional_percentage',
            'mobo_dynamic_price'
        ];

        $option_values = [];
        foreach ($options as $option) {
            $option_values[$option] = get_option($option, '0'); // Default to '0' if not found
        }

        return $option_values;
    }
}
