<?php

namespace MoboCore;

class WooCommerceProductManager
{

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
                    'key' => 'guid',
                    'value' => $guids,
                    'compare' => 'in'
                )
            ),
            'hide_empty' => false,   // Include empty categories
            'fields' => 'ids' // Only return IDs
        );

        $categories = get_terms($args);

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
            $categories = $product_data['productCategories'];
            $attributes = $product_data['attributes'];
            $variants = $product_data['variants'];
            $images = [];//$product_data['images'];

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

            $existing_products = get_posts($args);
            if (!empty($existing_products)) {
                $existing_product_id = $existing_products[0]->ID;
            }
            if ($existing_product_id) {
                $product = wc_get_product($existing_product_id);
            } else {
                $product = new \WC_Product_Variable();
            }

            $product->set_name($title);
            $product->set_description($caption);
            $product->set_regular_price($price);
            $product->set_manage_stock(true);
            $product->set_stock_quantity($stock);
            $product->set_category_ids($wp_category_ids);

            // Handle images
            if (!empty($images)) {
                $image_ids = [];
                foreach ($images as $image) {
                    $image_id = self::upload_image($image['url']);
                    if ($image_id) {
                        $image_ids[] = $image_id;
                        // Store GUID for the image
                        update_post_meta($image_id, 'img_guid', $image['id']);
                    }
                }
                $product->set_gallery_image_ids($image_ids);
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
                // $newAttr->set_name($attribute['name']);
                $newAttr->set_name(sanitize_title('Model'));
                $newAttr->set_visible(true);
                $newAttr->set_variation(true);
                $newAttr->set_options(['A12']);

                $attribute_data[] = $newAttr;
                // Store GUID for the attribute
                update_post_meta($wp_product_id, 'attr_guid', $attribute['id']);
            }
            // $js = json_encode($attribute_data);
            
            $product->set_attributes($attribute_data);
            $product->save(); // Save the product after setting attributes

            

            foreach ($variants as $variant) {
                $existing_variant_id =0;

                // Process variants
                $persisted_variations = $product->get_children();

                // Loop through each variation
                foreach ($persisted_variations as $variation_id) {
                    // Check if the meta key exists for this variation
                    if (get_post_meta($variation_id, 'variant_guid', true) == $variant['variantId']) {
                        $existing_variant_id = $variation_id; // Return the variation ID
                    }
                }
                
                if ($existing_variant_id) {
                    $variation = new \WC_Product_Variation($existing_variant_id);
                } else {
                    $variation = new \WC_Product_Variation();
                    $variation->set_parent_id($wp_product_id);
                }

                // $m = $variation->get_attribute('Model');
                // $m2 = $variation->get_attributes();
                // $m3 = $variation->get_attribute_summary();
                // $m4 = $variation->has_attributes();
                // $m5 = $variation->get_variation_attributes();

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
                $variant_attributes = [
                    'pa_model' => 'A12' // Use the slug of the attribute here
                ];
                // foreach ($variant['attributes'] as $attribute) {
                //     $variant_attributes[] =
                //     [
                //         'name'  => $attribute['name'],
                //         'value' => $attribute['option']
                //     ];
                // }

                error_log(print_r($attribute_data, true)); // Log attributes
                error_log(print_r($variant_attributes, true)); // Log variant attributes
                $variation->set_attributes($variant_attributes);
                $variation->set_default_attributes($variant_attributes);
                // $variation->set_attributes($variant['attributes']);
                $variation->update_meta_data('variant_guid', $variant['variantId']); // Store GUID
                $variation->save();
                error_log(print_r($variation->get_attributes(), true)); // Log variation attributes after saving
                break;
            }

            $product->save();
        }

        return 'Products updated successfully';
    }

    private static function upload_image($image_url)
    {
        // Ensure the URL is valid
        if (filter_var($image_url, FILTER_VALIDATE_URL)) {
            $upload_dir = wp_upload_dir();
            $image_data = file_get_contents($image_url);
            $filename = basename($image_url);

            // Check if the image was successfully retrieved
            if ($image_data) {
                $file_path = $upload_dir['path'] . '/' . $filename;
                file_put_contents($file_path, $image_data);

                // Prepare the attachment
                $wp_filetype = wp_check_filetype($filename, null);
                $attachment = [
                    'post_mime_type' => $wp_filetype['type'],
                    'post_title' => sanitize_file_name($filename),
                    'post_content' => '',
                    'post_status' => 'inherit'
                ];

                // Insert the attachment into the media library
                $attachment_id = wp_insert_attachment($attachment, $file_path);
                // Generate attachment metadata
                $attach_data = wp_generate_attachment_metadata($attachment_id, $file_path);
                wp_update_attachment_metadata($attachment_id, $attach_data);

                return $attachment_id;
            }
        }
        return false; // Return false if the image could not be uploaded
    }
}
