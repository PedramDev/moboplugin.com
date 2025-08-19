<?php

namespace MoboCore;

class ApiFunctions
{
    private $base_url = 'https://customers.mobomobo.ir/';

    function fetch_data_from_api($url)
    {

        // Make a GET request
        $response = \wp_remote_get($url);

        // Check for errors
        if (\is_wp_error($response)) {
            return false;
        }

        // Get the response body
        $body = \wp_remote_retrieve_body($response);

        // Decode the JSON into an associative array
        $data = json_decode($body, true);

        if ($data === null) {
            return false;
        }
        
        return $data; // Return the data or process it as needed
    }

    public function getProductsCount(){
        // Make a GET request
        $response = \wp_remote_get($this->base_url . 'get-products-count');

        // Check for errors
        if (is_wp_error($response)) {
            return false;
        }

        // Get the response body
        $body = \wp_remote_retrieve_body($response);

        return $body;
    }

    public function getProductsAsJson($pageNumber,$recordPerPage){
        $productsArray = $this->fetch_data_from_api($this->base_url . "get-products?PageNumber=$pageNumber&RecordPerPage=$recordPerPage");
        return $productsArray;
    }
    
    public function getCategoriesAsJson(){
        $categoriesArray = $this->fetch_data_from_api($this->base_url . 'get-categories');
        return $categoriesArray;
    }
}
