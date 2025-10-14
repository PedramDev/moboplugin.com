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
        // Check if allow_url_fopen is enabled
        if (ini_get('allow_url_fopen')) {
            $image_data = @file_get_contents($url);

            if ($image_data === false) {
                // Handle error for file_get_contents
                return 'Error fetching image using file_get_contents: ' . error_get_last()['message'];
            }
        } else {
            // Fall back to cURL if allow_url_fopen is disabled
            $image_data = self::fetch_image_with_curl($url);
            if (is_string($image_data)) {
                return $image_data; // Return error from cURL
            }
        }

        return $image_data; // Return the fetched image data
    }

    function fetch_image_with_curl($url)
    {
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $image_data = curl_exec($ch);

        if ($image_data === false) {
            // Handle cURL error
            return 'Error fetching image using cURL: ' . curl_error($ch);
        }

        curl_close($ch);
        return $image_data; // Return the image data
    }

    private function upload_image($image_url)
    {
        // Ensure the URL is valid
        if (\filter_var($image_url, FILTER_VALIDATE_URL)) {
            if (! function_exists('wp_generate_attachment_metadata')) {
                require_once ABSPATH . 'wp-admin/includes/image.php';
            }



            $upload_dir = \wp_upload_dir();
            $image_data = self::fetch_image_data($image_url);
            $filename = \basename($image_url);

            // Check if the image was successfully retrieved
            if ($image_data) {
                $file_path = $upload_dir['path'] . '/' . $filename;
                \file_put_contents($file_path, $image_data);

                // Prepare the attachment
                $wp_filetype = \wp_check_filetype($filename, null);
                $attachment = [
                    'post_mime_type' => $wp_filetype['type'],
                    'post_title' => \sanitize_file_name($filename),
                    'post_content' => '',
                    'post_status' => 'inherit'
                ];

                // Insert the attachment into the media library
                $attachment_id = \wp_insert_attachment($attachment, $file_path);
                // Generate attachment metadata
                $attach_data = \wp_generate_attachment_metadata($attachment_id, $file_path);
                \wp_update_attachment_metadata($attachment_id, $attach_data);

                return $attachment_id;
            }
        }
        return false; // Return false if the image could not be uploaded
    }

    public function remove_product($data)
    {
        if (empty($data) || !isset($data['listOfId'])) {
            return 'Invalid JSON data';
        }

        $ids = $data['listOfId'];

        // Ensure $ids is an array
        if (!is_array($ids)) {
            $ids = [$ids]; // Convert to array if it's not
        }

        global $wpdb;

        // Prepare the placeholders for the SQL query
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));

        // Prepare and execute the SQL query to delete products
        $query = $wpdb->prepare(
            "DELETE FROM $wpdb->posts
        WHERE ID IN (
            SELECT ID FROM $wpdb->postmeta
            WHERE meta_key = 'product_guid' AND meta_value IN ($placeholders)
        ) AND post_type = 'product'",
            ...$ids // Unpack the array into the query
        );

        // Execute the query
        $deleted_rows = $wpdb->query($query);

        return $deleted_rows ? "$deleted_rows products deleted." : 'No products found.';
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

        trace_log(print_r($data));

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

                $setProdDetailResult = $this->set_product_details($result['product'], $result['isNew'], $product_url, $title, $caption, $price, $comparePrice, $stock, $auto_options, $wp_category_ids, $publishDate);
                if ($setProdDetailResult == false) {
                    trace_log('save product aborted!');
                    continue;
                }
                trace_log();

                $wp_product_id = $result['product']->save();
                $result['product']->update_meta_data('product_guid', $product_id); // Store GUID

                trace_log();

                if ($result['isNew'] || $auto_options['global_update_images'] == '1') {
                    $this->handle_images($result['product'], $images);
                }
                trace_log();
                $this->update_attributes($result['product'], $attributes, $wp_product_id);

                $result['product']->save();

                $this->update_variants($result['product'], $variants, $auto_options, $wp_product_id);
                trace_log();

                $result['product']->save();
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
        if ($isNew || $auto_options['global_product_auto_caption'] == '1') {
            $product->set_description($caption ?? '');
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
        $image_ids = [];

        if (!empty($images)) {
            foreach ($images as $image) {
                $query = $wpdb->prepare(
                    "SELECT count(*) FROM {$wpdb->postmeta} WHERE meta_key = 'img_guid' AND meta_value = %s",
                    $image['id']
                );
                $isImgExist = $wpdb->get_var($query);

                if ($isImgExist == 0) {
                    $image_id = self::upload_image($image['url']);
                    if ($image_id) {
                        $image_ids[] = $image_id;
                        \update_post_meta($image_id, 'img_guid', $image['id']);
                    }
                }
            }

            $product->set_gallery_image_ids($image_ids);
            if (!empty($image_ids)) {
                $product->set_image_id($image_ids[0]);
            } else if (!empty($images)) {
                $query = $wpdb->prepare(
                    "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'img_guid' AND meta_value = %s",
                    $images[0]['id']
                );
                $img_id = $wpdb->get_var($query);
                $product->set_image_id($img_id);
            }
        }
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
