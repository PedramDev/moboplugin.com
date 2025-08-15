<?php

namespace MoboCore;

use Automattic\WooCommerce\Client;

class WooCommerceCategoryManager
{
    private $woocommerce;

    public function __construct($url, $consumerKey, $consumerSecret)
    {
        $this->woocommerce = new Client(
            $url,
            $consumerKey,
            $consumerSecret,
            ['version' => 'wc/v3']
        );
    }


    /**
     * Update Or Create all category based on JSON
     */
    public function addOrUpdateAllCategories($jsonData)
    {
        // Decode JSON data
        $dataArray = json_decode($jsonData, true);

        if (!is_array($dataArray)) {
            error_log("Invalid JSON data provided.");
            return;
        }

        $logFile = 'category_update_log.txt';

        foreach ($dataArray as $data) {
            $result = $this->addOrUpdateCategory($data);
            // Log the result to the file
            file_put_contents($logFile, $result . PHP_EOL, FILE_APPEND);
        }
    }

    /**
     * Get category woocommerce ID by thirdparty ID
     * @param Number $meta_value ThirdPartyId
     * @return Null | Number Wordpress category ID
     */
    public function get_single_product_category($meta_value)
    {
        $args = array(
            'taxonomy' => 'product_cat',
            'meta_query' => array(
                array(
                    'key'     => 'guid',
                    'value'   => $meta_value,
                    'compare' => '='
                )
            ),
            'number'   => 1, // Limit to 1 result
            'fields'   => 'ids' // Only return IDs
        );

        $categories = get_terms($args);

        if (!empty($categories)) {
            return get_term($categories[0]); // Get the full term object
        }

        return null; // No category found
    }


   

    private function addOrUpdateCategory($data)
    {
        // Prepare category data
        $categoryData = [
            'name' => $data['title'],
            'slug' => trim($data['slug'], '/'), // Remove leading/trailing slashes
            'meta_data' => [
                [
                    'key' => 'guid', // Custom key for your extra data
                    'value' => $data['id'], // Value to store
                ],
            ],
        ];

        try {
            // Check if category exists
            $categoryId = $this->get_single_product_category($data['id']);


            if ($categoryId) {
                // Update existing category
                $this->woocommerce->put("products/categories/{$categoryId}", $categoryData);
                return "Category updated successfully for ID: " . $data['id'];
            } else {
                // Create new category
                $this->woocommerce->post('products/categories', $categoryData);
                return "Category created successfully for ID: " . $data['id'];
            }
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}
