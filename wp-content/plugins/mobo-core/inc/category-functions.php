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



    private function addOrUpdateCategory($data)
    {
        global $wpdb;

        // Prepare category data
        $categoryName = $data['title'];
        $categorySlug = basename(trim($data['url'], '/')); // Remove leading/trailing slashes
        $guidValue = $data['id'];
        $parentGuidValue = $data['parentId'];

        // Check if category exists
        $query = $wpdb->prepare(
            "SELECT term_id FROM {$wpdb->termmeta} WHERE meta_key = 'category_guid' AND meta_value = %s",
            $guidValue
        );
        $existingCategory = $wpdb->get_row($query);

        $parentCategory = null;
        if($parentGuidValue != null){
            $parentCategory = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT term_id FROM {$wpdb->termmeta} WHERE meta_key = 'category_guid' AND meta_value = %s",
                    $parentGuidValue
                )
            );
        }

        try {
            if ($existingCategory) {
                // Update existing category in term_taxonomy
                $termId = $existingCategory->term_id;

                // Update the term name and meta data
                $wpdb->update(
                    $wpdb->terms,
                    [
                        'name' => $categoryName,
                        'slug' => $categorySlug
                    ],
                    ['term_id' => $termId]
                );

                if($parentCategory != null){
                    $wpdb->update(
                        $wpdb->term_taxonomy,
                        [
                            'parent' => $parentCategory->term_id
                        ],
                        ['term_id' => $termId]
                    );
                }

                // Update term meta
                \update_term_meta($termId, 'category_guid', $guidValue);

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
                        'parent' => $parentCategory->term_id,
                        'count' => 0
                    ]
                );

                // Add term meta
                \add_term_meta($termId, 'category_guid', $guidValue);

                return "Category created successfully for ID: " . $data['id'];
            }
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}
