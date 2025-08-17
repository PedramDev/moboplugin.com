<?php

namespace MoboCore;

require 'helper.php';

class WooCommerceCategoryManager
{
    /**
     * Update Or Create all category based on JSON
     */
    public function addOrUpdateAllCategories($jsonArray)
    {
        foreach ($jsonArray as $item) {
            // echo "ID: {$item['id']}, Title: {$item['title']}, URL: {$item['url']}\n";
            $result = $this->addOrUpdateCategory($item);
        }
    }

    /**
     * Get category woocommerce ID by thirdparty ID
     * @param Number $meta_value ThirdPartyId
     * @return Null | Number Wordpress category ID
     */
    public function get_single_product_category($slugs)
    {
        // Ensure slugs are an array
            if (!is_array($slugs)) {
                return null; // or handle the error as needed
            }

            $args = array(
                'taxonomy' => 'product_cat',
                'slug'     => $slugs, // Use an array of slugs for matching
                'fields'   => 'ids' // Only return IDs
            );

            $categories = get_terms($args);

            if (!empty($categories)) {
                return array_map('get_term', $categories); // Get full term objects for each ID
            }

            return []; // No categories found
    }




    private function addOrUpdateCategory($data)
    {
        global $wpdb;

        // Prepare category data
        $categoryName = $data['title'];
        $categorySlug = trim($data['url'], '/'); // Remove leading/trailing slashes
        $guidValue = $data['id'];

        // Check if category exists
        $existingCategory = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT term_id FROM {$wpdb->terms} WHERE slug = %s",
                $categorySlug
            )
        );

        try {
            if ($existingCategory) {
                // Update existing category in term_taxonomy
                $termId = $existingCategory->term_id;

                // Update the term name and meta data
                $wpdb->update(
                    $wpdb->terms,
                    ['name' => $categoryName],
                    ['term_id' => $termId]
                );

                // Update term meta
                update_term_meta($termId, 'guid', $guidValue);

                return "Category updated successfully for ID: " . $data['id'];
            } else {
                // Create new category
                $wpdb->insert(
                    $wpdb->terms,
                    [
                        'name' => $categoryName,
                        'slug' => $categorySlug,
                        'term_group' => 0
                    ]
                );

                // Get the new term ID
                $termId = $wpdb->insert_id;

                // Insert into term_taxonomy
                $wpdb->insert(
                    $wpdb->term_taxonomy,
                    [
                        'term_id' => $termId,
                        'taxonomy' => 'product_cat',
                        'description' => '',
                        'parent' => 0,
                        'count' => 0
                    ]
                );

                // Add term meta
                add_term_meta($termId, 'guid', $guidValue);

                return "Category created successfully for ID: " . $data['id'];
            }
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}
