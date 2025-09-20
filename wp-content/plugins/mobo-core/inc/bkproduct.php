<?php

    public function update_product($data)
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
                if (!empty($attributes)) {
                    $product = new \WC_Product_Variable();
                } else {
                    $product = new \WC_Product();
                }
            }

            $globVal = intval($auto_options['global_additional_price']);
            if ($existing_products) {
                if ($auto_options['global_product_auto_title'] == '1') {
                    $product->set_name($title);
                }


                if ($auto_options['global_product_auto_caption'] == '1') {
                    $product->set_description($caption ?? '');
                }

                if ($auto_options['global_product_auto_price'] == '1') {

                    if (isset($comparePrice)) {
                        $product->set_regular_price(intval($comparePrice) + $globVal);
                        $product->set_sale_price(intval($price) + $globVal);
                    } else {
                        $product->set_regular_price(intval($price) + $globVal);
                        $product->set_sale_price('');
                    }
                }


                $additional_product_price = get_post_meta($existing_products[0]->ID, 'mobo_additional_price_simple', true);
                if (isset($additional_product_price) && !empty($additional_product_price)) {
                    $additional_product_price = intval($additional_product_price);

                    if (isset($comparePrice)) {
                        $product->set_regular_price(intval($comparePrice) + $additional_product_price);
                        $product->set_sale_price(intval($price) + $additional_product_price);
                    } else {
                        $product->set_regular_price(intval($price) + $additional_product_price);
                        $product->set_sale_price('');
                    }
                }

                if ($auto_options['global_product_auto_stock'] == '1') {
                    $product->set_manage_stock(true);
                    $product->set_stock_quantity($stock);
                }
            } else {
                $product->set_name($title);
                $product->set_description($caption ?? '');

                if (isset($comparePrice)) {
                    $product->set_regular_price(intval($comparePrice) + $globVal);
                    $product->set_sale_price(intval($price) + $globVal);
                } else {
                    $product->set_regular_price(intval($price) + $globVal);
                    $product->set_sale_price('');
                }

                $product->set_manage_stock(true);
                $product->set_stock_quantity($stock);

                 if ($auto_options['global_product_auto_price'] == '1') {

                    if (isset($comparePrice)) {
                        $product->set_regular_price(intval($comparePrice) + $globVal);
                        $product->set_sale_price(intval($price) + $globVal);
                    } else {
                        $product->set_regular_price(intval($price) + $globVal);
                        $product->set_sale_price('');
                    }
                }
            }

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


            if (!empty($variants)) {
                foreach ($variants as $variant) {
                    $existing_variant_id = 0;

                    // Process variants
                    $persisted_variations = $product->get_children();

                    if (!empty($persisted_variations)) {
                        // Loop through each variation
                        foreach ($persisted_variations as $variation_id) {
                            // Check if the meta key exists for this variation
                            if (\get_post_meta($variation_id, 'variant_guid', true) == $variant['variantId']) {
                                $existing_variant_id = $variation_id; // Return the variation ID
                            }
                        }
                    }

                    if ($existing_variant_id) {
                        $variation = new \WC_Product_Variation($existing_variant_id);
                    } else {
                        $variation = new \WC_Product_Variation();
                        $variation->set_parent_id($wp_product_id);
                    }

                    // Set variant details
                    $globVal = intval($auto_options['global_additional_price']);
                    if ($auto_options['global_product_auto_price'] == '1') {
                        if (isset($existing_variant_id)) {
                            $additional_price = get_post_meta($existing_variant_id, 'mobo_additional_price', true);
                            if (isset($additional_price) && !empty($additional_price)) {
                                $additional_price = intval($additional_price);

                                if (isset($variant['comparePrice'])) {
                                    $variation->set_regular_price(intval($variant['comparePrice']) + $additional_price);
                                    $variation->set_sale_price(intval($variant['price']) + $additional_price);
                                } else {
                                    $variation->set_regular_price(intval($variant['price']) + $additional_price);
                                    $variation->set_sale_price('');
                                }
                            } else {
                                if (isset($variant['comparePrice'])) {
                                    $variation->set_regular_price(intval($variant['comparePrice']) + $globVal);
                                    $variation->set_sale_price(intval($variant['price']) + $globVal);
                                } else {
                                    $variation->set_regular_price(intval($variant['price']) + $globVal);
                                    $variation->set_sale_price('');
                                }
                            }
                        } else {
                            if (isset($variant['comparePrice'])) {
                                $variation->set_regular_price(intval($variant['comparePrice']) + $globVal);
                                $variation->set_sale_price(intval($variant['price']) + $globVal);
                            } else {
                                $variation->set_regular_price(intval($variant['price']) + $globVal);
                                $variation->set_sale_price('');
                            }
                        }
                    } else {
                        if (!isset($existing_variant_id)) {

                            if (isset($variant['comparePrice'])) {
                                $variation->set_regular_price(intval($variant['comparePrice']) + $globVal);
                                $variation->set_sale_price(intval($variant['price']) + $globVal);
                            } else {
                                $variation->set_regular_price(intval($variant['price']) + $globVal);
                                $variation->set_sale_price('');
                            }
                        }
                    }

                    if (isset($existing_variant_id)) {
                        if ($auto_options['global_product_auto_stock'] == '1') {
                            $variation->set_stock_quantity($variant['stock']);
                            $variation->set_manage_stock(true);
                            $variation->set_stock_status($variant['stock'] > 0 ? 'instock' : 'outofstock');
                        }
                    } else {
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

                    // trace_log(print_r($attribute_data, true)); // Log attributes
                    // trace_log(print_r($variant_attributes, true)); // Log variant attributes

                    $variation->update_meta_data('variant_guid', $variant['variantId']); // Store GUID

                    foreach ($variant['attributes'] as $attribute) {
                        $key = 'attribute_' . \sanitize_title($attribute['name']);
                        $variation->update_meta_data($key, $attribute['option']); // Store GUID
                    }


                    $variation->save();

                    // trace_log(print_r($variation->get_attributes(), true)); // Log variation attributes after saving
                }
            }


            $product->save();
        }

        return 'Products updated successfully';
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
                if (!empty($attributes)) {
                    $product = new \WC_Product_Variable();
                } else {
                    $product = new \WC_Product();
                }
            }

            $globVal = intval($auto_options['global_additional_price']);
            if ($existing_products) {
                if ($auto_options['global_product_auto_title'] == '1') {
                    $product->set_name($title);
                }


                if ($auto_options['global_product_auto_caption'] == '1') {
                    $product->set_description($caption ?? '');
                }

                if ($auto_options['global_product_auto_price'] == '1') {
                    if (isset($comparePrice)) {
                        $product->set_regular_price(intval($comparePrice) + $globVal);
                        $product->set_sale_price(intval($price) + $globVal);
                    } else {
                        $product->set_regular_price(intval($price) + $globVal);
                    }
                }

                $additional_product_price = get_post_meta($existing_products[0]->ID, 'mobo_additional_price_simple', true);
                if (isset($additional_product_price) && !empty($additional_product_price)) {
                    $additional_product_price = intval($additional_product_price);

                    if (isset($comparePrice)) {
                        $product->set_regular_price(intval($comparePrice) + $additional_product_price);
                        $product->set_sale_price(intval($price) + $additional_product_price);
                    } else {
                        $product->set_regular_price(intval($price) + $additional_product_price);
                        $product->set_sale_price('');
                    }
                }

                if ($auto_options['global_product_auto_stock'] == '1') {
                    $product->set_manage_stock(true);
                    $product->set_stock_quantity($stock);
                }
            } else {
                $product->set_name($title);
                $product->set_description($caption ?? '');

                if (isset($comparePrice)) {
                    $product->set_regular_price(intval($comparePrice) + $globVal);
                    $product->set_sale_price(intval($price) + $globVal);
                } else {
                    $product->set_regular_price(intval($price) + $globVal);
                    $product->set_sale_price('');
                }

                $product->set_manage_stock(true);
                $product->set_stock_quantity($stock);
                
                if (isset($comparePrice)) {
                    $product->set_regular_price(intval($comparePrice) + $globVal);
                    $product->set_sale_price(intval($price) + $globVal);
                } else {
                    $product->set_regular_price(intval($price) + $globVal);
                    $product->set_sale_price('');
                }
                if ($auto_options['global_product_auto_price'] == '1') {

                    if (isset($comparePrice)) {
                        $product->set_regular_price(intval($comparePrice) + $globVal);
                        $product->set_sale_price(intval($price) + $globVal);
                    } else {
                        $product->set_regular_price(intval($price) + $globVal);
                        $product->set_sale_price('');
                    }
                }
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

            if (!empty($variants)) {
                foreach ($variants as $variant) {
                    $existing_variant_id = 0;

                    // Process variants
                    $persisted_variations = $product->get_children();

                    if (!empty($persisted_variations)) {
                        // Loop through each variation
                        foreach ($persisted_variations as $variation_id) {
                            // Check if the meta key exists for this variation
                            if (\get_post_meta($variation_id, 'variant_guid', true) == $variant['variantId']) {
                                $existing_variant_id = $variation_id; // Return the variation ID
                            }
                        }
                    }

                    if ($existing_variant_id) {
                        $variation = new \WC_Product_Variation($existing_variant_id);
                    } else {
                        $variation = new \WC_Product_Variation();
                        $variation->set_parent_id($wp_product_id);
                    }



                    // Set variant details
                    if ($auto_options['global_product_auto_price'] == '1') {
                        if (isset($existing_variant_id)) {
                            $additional_price = get_post_meta($existing_variant_id, 'mobo_additional_price', true);
                            if (isset($additional_price) && !empty($additional_price)) {
                                $additional_price = intval($additional_price);

                                if (isset($variant['comparePrice'])) {
                                    $variation->set_regular_price(intval($variant['comparePrice']) + $additional_price);
                                    $variation->set_sale_price(intval($variant['price']) + $additional_price);
                                } else {
                                    $variation->set_regular_price(intval($variant['price']) + $additional_price);
                                    $variation->set_sale_price('');
                                }
                            }
                        } else {
                            if (isset($variant['comparePrice'])) {
                                $variation->set_regular_price(intval($variant['comparePrice']) + $globVal);
                                $variation->set_sale_price(intval($variant['price']) + $globVal);
                            } else {
                                $variation->set_regular_price(intval($variant['price']) + $globVal);
                                $variation->set_sale_price('');
                            }
                        }
                    } else {
                        if (!isset($existing_variant_id)) {

                            if (isset($variant['comparePrice'])) {
                                $variation->set_regular_price(intval($variant['comparePrice']) + $globVal);
                                $variation->set_sale_price(intval($variant['price']) + $globVal);
                            } else {
                                $variation->set_regular_price(intval($variant['price']) + $globVal);
                                $variation->set_sale_price('');
                            }
                        }
                    }


                    if (isset($existing_variant_id)) {
                        if ($auto_options['global_product_auto_stock'] == '1') {
                            $variation->set_stock_quantity($variant['stock']);
                            $variation->set_manage_stock(true);
                            $variation->set_stock_status($variant['stock'] > 0 ? 'instock' : 'outofstock');
                        }
                    } else {
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

                    // trace_log(print_r($attribute_data, true)); // Log attributes
                    // trace_log(print_r($variant_attributes, true)); // Log variant attributes

                    $variation->update_meta_data('variant_guid', $variant['variantId']); // Store GUID

                    foreach ($variant['attributes'] as $attribute) {
                        $key = 'attribute_' . \sanitize_title($attribute['name']);
                        $variation->update_meta_data($key, $attribute['option']); // Store GUID
                    }


                    $variation->save();

                    // trace_log(print_r($variation->get_attributes(), true)); // Log variation attributes after saving
                }
            }

            $product->save();
        }

        return 'Products updated successfully';
    }

?>