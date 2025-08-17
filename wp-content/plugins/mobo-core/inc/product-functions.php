<?php
namespace MoboCore;

class WooCommerceProductManager {

    private function getCategoryUrls($categories) {
        $ids = array_map(function($category) {
            return $category['url'];
        }, $categories);
        
        return $ids;
    }

     /**
     * Get category woocommerce IDs by third-party IDs
     * @param array $meta_value_array Array of ThirdPartyIds
     * @return array|null Array of WordPress category IDs or null if none found
     */
    private function get_all_product_categories($slugs)
    {
        if (!is_array($slugs)) {
            return null; // or handle the error as needed
        }

        $formatted_slugs = array_map(function($slug) {
            return basename($slug); // Get the last part of the slug
        }, $slugs);

        $args = array(
                'taxonomy' => 'product_cat',
                'slug'     => $formatted_slugs, // Use an array of slugs for matching
                'fields'   => 'ids', // Only return IDs
                'hide_empty' => false,   // Include empty categories
            );

        $categories = get_terms($args);

        global $wpdb;
        $lastQ =  $wpdb->last_query;
        if (!empty($categories)) {
            return $categories; // Return the array of category IDs
        }

        

        return null; // No categories found
    }

    public function update_product($data) {
        if (!$data) {
            return 'Invalid JSON data';
        }

        foreach ($data['data'] as $product_data) {
            $product_id = $product_data['productId'];
            $stock = $product_data['stock'] ?? 0;
            $price = $product_data['price'];
            $title = $product_data['title'];
            $caption = $product_data['caption'];
            $categories = $product_data['categories'];
            $attributes = $product_data['attributes'];
            $variants = $product_data['variants'];
            $images = $product_data['images'];

            // Prepare category IDs
            $category_ids = $this->getCategoryUrls($categories);
            $wp_category_ids =$this->get_all_product_categories($category_ids);

            // Check if the product exists
            $existing_product_id = null;
            $args = [
                'post_type' => 'product',
                'meta_query' => [
                    [
                        'key' => 'guid',
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

            // Set product details
            $product->set_name($title);
            $product->set_description($caption);
            $product->set_regular_price($price);
            $product->set_manage_stock(true);
            $product->set_stock_quantity($stock);
            $product->set_category_ids($wp_category_ids);
            $product->update_meta_data('guid', $product_id); // Store GUID

            // Handle images
            if (!empty($images)) {
                $image_ids = [];
                foreach ($images as $image) {
                    $image_id = self::upload_image($image['url']);
                    if ($image_id) {
                        $image_ids[] = $image_id;
                        // Store GUID for the image
                        update_post_meta($image_id, 'guid', $image['id']);
                    }
                }
                $product->set_gallery_image_ids($image_ids);
            }

            // Save the product
            $product_id = $product->save();

            // Update or create attributes
            $attribute_data = [];
            foreach ($attributes as $attribute) {
                // Ensure the attribute has multiple values
                $attribute_values = array_filter(array_map('trim', explode(',', $attribute['value'])));
                $attribute_data[] = [
                    'name' => $attribute['name'],
                    'options' => $attribute_values,
                    'position' => 0,
                    'visible' => true,
                    'variation' => true,
                ];
                // Store GUID for the attribute
                update_post_meta($product_id, 'guid', $attribute['id']);
            }
            $product->set_attributes($attribute_data);
            $product->save();

            // Process variants
            foreach ($variants as $variant) {
                $variant_id = $variant['variantId'];
                $existing_variant_id = wc_get_product_id_by_sku($variant_id);
                
                if ($existing_variant_id) {
                    $variation = new \WC_Product_Variation($existing_variant_id);
                } else {
                    $variation = new \WC_Product_Variation();
                    $variation->set_parent_id($product_id);
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
                $variant_attributes = [
                    'model' => $variant['title'] // Assuming 'model' is the attribute name
                ];
                $variation->set_attributes($variant_attributes);
                $variation->update_meta_data('guid', $variant['variantId']); // Store GUID
                $variation->save();
            }
        }

        return 'Products updated successfully';
    }

    private static function upload_image($image_url) {
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