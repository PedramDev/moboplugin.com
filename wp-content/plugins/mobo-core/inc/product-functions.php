<?php

namespace MoboCore;

class WooCommerceProductManager
{
    public function __construct()
    {
        // Hook to a point where WordPress is fully loaded
        add_action('init', [$this, 'init']);
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

    //174789827 multi attr
    public function update_product($data)
    {
        if (!$data) {
            return 'Invalid JSON data';
        }

        foreach ($data['data'] as $product_data) {
            $product_id = $product_data['productId'];
            $stock = $product_data['stock'] ?? 0;
            $price = $product_data['price'];
            $title = $product_data['title'];
            $caption = $product_data['caption'];
            $product_url = $product_data['url'];
            $comparePrice = $product_data['comparePrice'];
            $categories = $product_data['productCategories'];
            $attributes = $product_data['attributes'];
            $variants = $product_data['variants'];
            $images = $product_data['images'];

            // Prepare category IDs
            $category_ids = $this->getCategoryUrls($categories);
            $wp_category_ids = $this->get_all_product_categories($category_ids);

            // Check if the product exists
            $existing_product_id = null;
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
                $existing_product_id = $existing_products[0]->ID;
            }
            if ($existing_product_id) {
                $product = \wc_get_product($existing_product_id);
            } else {
                $product = new \WC_Product_Variable();
            }

            $product->set_name($title);
            $product->set_description($caption ?? '');
            $product->set_regular_price($price);
            if (isset($comparePrice)) {
                $product->set_sale_price($comparePrice);
            }
            $product->set_manage_stock(true);
            $product->set_stock_quantity($stock);
            $product->set_category_ids($wp_category_ids);
            $product->set_slug($product_url);
            $image_ids = $product->get_gallery_image_ids();

            global $wpdb;
            // Handle images
            if (!empty($images)) {
                $image_ids = [];
                foreach ($images as $image) {

                    $query = $wpdb->prepare(
                        "SELECT count(*) Count FROM {$wpdb->postmeta} WHERE meta_key = 'img_guid' AND meta_value = %s",
                        $image['id']
                    );
                    $isImgExist = $wpdb->get_var($query);
                    //if image not exist add it
                    if ($isImgExist == 0) {
                        $image_id = self::upload_image($image['url']);
                        if ($image_id) {
                            $image_ids[] = $image_id;
                            // Store GUID for the image
                            \add_post_meta($image_id, 'img_guid', $image['id']);
                        }
                    }
                }
                $product->set_gallery_image_ids($image_ids);
                if(!empty($image_ids)){
                    $product->set_image_id($image_ids[0]);
                }
            }

            // Save the product
            $wp_product_id = $product->save();
            $product->update_meta_data('product_guid', $product_id); // Store GUID

            // Update or create attributes
            $attribute_data = [];
            foreach ($attributes as $attribute) {
                // Ensure the attribute has multiple values
                $values = [];
                foreach ($attribute['values'] as $value) {
                    $values[] = $value['value'];
                }


                $newAttr = new \WC_Product_Attribute();
                $newAttr->set_name($attribute['name']);
                $newAttr->set_visible(true);
                $newAttr->set_variation(true);
                $newAttr->set_options($values);

                $attribute_data[] = $newAttr;
                // Store GUID for the attribute
                \update_post_meta($wp_product_id, 'attr_guid', $attribute['id']);
            }

            $product->set_attributes($attribute_data);
            $product->save(); // Save the product after setting attributes



            foreach ($variants as $variant) {
                $existing_variant_id = 0;

                // Process variants
                $persisted_variations = $product->get_children();

                // Loop through each variation
                foreach ($persisted_variations as $variation_id) {
                    // Check if the meta key exists for this variation
                    if (\get_post_meta($variation_id, 'variant_guid', true) == $variant['variantId']) {
                        $existing_variant_id = $variation_id; // Return the variation ID
                    }
                }

                if ($existing_variant_id) {
                    $variation = new \WC_Product_Variation($existing_variant_id);
                } else {
                    $variation = new \WC_Product_Variation();
                    $variation->set_parent_id($wp_product_id);
                }

                // Set variant details
                $variation->set_regular_price($variant['price']);
                if (isset($variant['comparePrice'])) {
                    $variation->set_sale_price($variant['comparePrice']);
                }
                $variation->set_stock_quantity($variant['stock']);
                $variation->set_manage_stock(true);
                $variation->set_stock_status($variant['stock'] > 0 ? 'instock' : 'outofstock');

                // Set variant attributes
                $variant_attributes = [];
                foreach ($variant['attributes'] as $attribute) {
                    $variant_attributes[] =
                        [
                            $attribute['name'] => $attribute['option']
                        ];
                }

                // error_log(print_r($attribute_data, true)); // Log attributes
                // error_log(print_r($variant_attributes, true)); // Log variant attributes

                $variation->update_meta_data('variant_guid', $variant['variantId']); // Store GUID

                foreach ($variant['attributes'] as $attribute) {
                    $key = 'attribute_' . \sanitize_title($attribute['name']);
                    $variation->update_meta_data($key, $attribute['option']); // Store GUID
                }


                $variation->save();

                // error_log(print_r($variation->get_attributes(), true)); // Log variation attributes after saving
            }

            $product->save();
        }

        return 'Products updated successfully';
    }

    private function upload_image($image_url)
    {
        // Ensure the URL is valid
        if (\filter_var($image_url, FILTER_VALIDATE_URL)) {
            if (! function_exists('wp_generate_attachment_metadata')) {
                require_once ABSPATH . 'wp-admin/includes/image.php';
            }



            $upload_dir = \wp_upload_dir();
            $image_data = \file_get_contents($image_url);
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


    public function webhook_update_product($data)
    {
        if (!$data) {
            return 'Invalid JSON data';
        }

        $auto_options = self::get_global_product_options();

        foreach ($data['data'] as $product_data) {


            $product_id = $product_data['productId'];
            $stock = $product_data['stock'] ?? 0;
            $price = $product_data['price'];
            $title = $product_data['title'];
            $caption = $product_data['caption'];
            $product_url = $product_data['url'];
            $comparePrice = $product_data['comparePrice'];
            $categories = $product_data['productCategories'];
            $attributes = $product_data['attributes'];
            $variants = $product_data['variants'];
            $images = $product_data['images'];

            // Prepare category IDs
            $category_ids = $this->getCategoryUrls($categories);
            $wp_category_ids = $this->get_all_product_categories($category_ids);

            // Check if the product exists
            $existing_product_id = null;
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
                $existing_product_id = $existing_products[0]->ID;
            }
            if ($existing_product_id) {
                $product = \wc_get_product($existing_product_id);
            } else {
                $product = new \WC_Product_Variable();
            }

            if ($auto_options['global_product_auto_title'] == '1') {
                $product->set_name($title);
            }
            if ($auto_options['global_product_auto_caption'] == '1') {
                $product->set_description($caption ?? '');
            }

            if ($auto_options['global_product_auto_price'] == '1') {
                $product->set_regular_price($price);
                if (isset($comparePrice)) {
                    $product->set_sale_price($comparePrice);
                }
            } else {
            }

            if ($auto_options['global_product_auto_stock'] == '1') {
                $product->set_manage_stock(true);
                $product->set_stock_quantity($stock);
            }

            $product->set_category_ids($wp_category_ids);
            $product->set_slug($product_url);
            $image_ids = $product->get_gallery_image_ids();
            
            global $wpdb;
            // Handle images
            if (!empty($images)) {
                $image_ids = [];
                foreach ($images as $image) {
                    //if image not exist add it
                    $query = $wpdb->prepare(
                        "SELECT count(*) Count FROM {$wpdb->postmeta} WHERE meta_key = 'img_guid' AND meta_value = %s",
                        $image['id']
                    );
                    $isImgExist = $wpdb->get_var($query);

                    //if image not exist add it
                    if ($isImgExist == 0) {
                        $image_id = self::upload_image($image['url']);
                        if ($image_id) {
                            $image_ids[] = $image_id;
                            // Store GUID for the image
                            \add_post_meta($image_id, 'img_guid', $image['id']);
                        }
                    }
                }
                $product->set_gallery_image_ids($image_ids);
                if(!empty($image_ids)){
                    $product->set_image_id($image_ids[0]);
                }
            }

            // Save the product
            $wp_product_id = $product->save();
            $product->update_meta_data('product_guid', $product_id); // Store GUID

            // Update or create attributes
            $attribute_data = [];
            foreach ($attributes as $attribute) {
                // Ensure the attribute has multiple values
                $values = [];
                foreach ($attribute['values'] as $value) {
                    $values[] = $value['value'];
                }


                $newAttr = new \WC_Product_Attribute();
                $newAttr->set_name($attribute['name']);
                $newAttr->set_visible(true);
                $newAttr->set_variation(true);
                $newAttr->set_options($values);

                $attribute_data[] = $newAttr;
                // Store GUID for the attribute
                \update_post_meta($wp_product_id, 'attr_guid', $attribute['id']);
            }

            $product->set_attributes($attribute_data);
            $product->save(); // Save the product after setting attributes


            foreach ($variants as $variant) {
                $existing_variant_id = 0;

                // Process variants
                $persisted_variations = $product->get_children();

                // Loop through each variation
                foreach ($persisted_variations as $variation_id) {
                    // Check if the meta key exists for this variation
                    if (\get_post_meta($variation_id, 'variant_guid', true) == $variant['variantId']) {
                        $existing_variant_id = $variation_id; // Return the variation ID
                    }
                }

                if ($existing_variant_id) {
                    $variation = new \WC_Product_Variation($existing_variant_id);
                } else {
                    $variation = new \WC_Product_Variation();
                    $variation->set_parent_id($wp_product_id);
                }



                // Set variant details
                if ($auto_options['global_product_auto_price']=='1') {
                    $additional_price = get_post_meta($variation_id, 'mobo_additional_price', true);

                    if (isset($additional_price) && !empty($additional_price)) {
                        $additional_price = intval($additional_price);

                        $variation->set_regular_price(intval($variant['price']) + $additional_price);
                        if (isset($variant['comparePrice'])) {
                            $variation->set_sale_price(intval($variant['comparePrice']) + $additional_price);
                        }
                    } else {
                        $globVal = intval($auto_options['global_additional_price']);
                        $variation->set_regular_price(intval($variant['price']) + $globVal);
                        if (isset($variant['comparePrice'])) {
                            $variation->set_sale_price(intval($variant['comparePrice']) + $globVal);
                        }
                    }
                } else {
                    $variation->set_regular_price($variant['price']);
                    if (isset($variant['comparePrice'])) {
                        $variation->set_sale_price($variant['comparePrice']);
                    }
                }

                if ($auto_options['global_product_auto_stock']=='1') {
                    $variation->set_stock_quantity($variant['stock']);
                    $variation->set_manage_stock(true);
                    $variation->set_stock_status($variant['stock'] > 0 ? 'instock' : 'outofstock');
                }

                // Set variant attributes
                $variant_attributes = [];
                foreach ($variant['attributes'] as $attribute) {
                    $variant_attributes[] =
                        [
                            $attribute['name'] => $attribute['option']
                        ];
                }

                // error_log(print_r($attribute_data, true)); // Log attributes
                // error_log(print_r($variant_attributes, true)); // Log variant attributes

                $variation->update_meta_data('variant_guid', $variant['variantId']); // Store GUID

                foreach ($variant['attributes'] as $attribute) {
                    $key = 'attribute_' . \sanitize_title($attribute['name']);
                    $variation->update_meta_data($key, $attribute['option']); // Store GUID
                }


                $variation->save();

                // error_log(print_r($variation->get_attributes(), true)); // Log variation attributes after saving
            }

            $product->save();
        }

        return 'Products updated successfully';
    }

    function get_global_product_options()
    {
        $options = [
            'global_product_auto_stock',
            'global_product_auto_price',
            'global_product_auto_title',
            'global_product_auto_caption',
            'global_additional_price'
        ];

        $option_values = [];
        foreach ($options as $option) {
            $option_values[$option] = get_option($option, '0'); // Default to '0' if not found
        }

        return $option_values;
    }
}
