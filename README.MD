# MoboCore – WooCommerce Integration Plugin

This plugin integrates WooCommerce with the **MoboCore API** (`https://customers.mobomobo.ir/`).  
It synchronizes products, categories, variations, prices, images, and stock levels from the external system into a WordPress/WooCommerce store.  
It also handles webhook events, scheduled syncing, and provides multiple admin tools for manual controls and debugging.

---

## Overview

The system consists of several PHP modules:

- **API Client** – Handles authenticated requests to MoboCore.
- **Category Sync** – Maps external category GUIDs to WooCommerce categories.
- **Product Sync** – Creates and updates WooCommerce products and variations.
- **Webhook Processor** – Receives and queues real-time updates.
- **Admin Tools** – Adds meta boxes, sync pages, and product listings.
- **Utilities** – Helpers for JSON, image handling, locking, and file loading.

Each component is modular, allowing you to extend or override behaviors without modifying the core logic.

---

# File Breakdown

## helper.php
Namespace: `MoboCore`

Provides generic helpers.

### Key Function

**parseJson($json)**  
A generator that safely decodes JSON and yields each item. Throws exceptions on invalid input.

---

## index.php

The bootstrap loader that ensures files load only inside WordPress.  
It includes all implementation modules:

- `category-functions.php`
- `product-functions.php`
- `api-functions.php`
- `custom-metabox.php`
- `webhooks.php`
- `woocommerce-functions.php`

---

# Core Modules

## product-functions.php
Namespace: `MoboCore`

### Main Class  
### **WooCommerceProductManager**

### Responsibilities

- Map external product payloads into WooCommerce products.
- Handle product creation or updating (simple or variable).
- Process variations using GUIDs for identification.
- Import and synchronize product images.
- Map external categories to local terms.
- Apply pricing rules and auto-options.
- Create/update variation attributes.
- Read from webhook queue and process product files.
- Use filesystem-based locking to prevent parallel imports.

### Important Methods

- `update_product($data)`
- `webhook_update_product($data, $filePath)`
- `process_product_data($data, $filePath)`
- `prepare_categories($categories)`
- `handle_images($product, $images)`
- `update_variants($product, $variants, $auto_options, $wp_product_id)`
- `update_attributes($product, $attributes, $wp_product_id)`
- `get_or_create_product($product_id, $attributes)`
- `get_existing_variant_id($product, $variant_guid)`
- `set_prices($product, $price, $comparePrice, $auto_options)`
- `set_variant_prices(...)`
- `set_variant_details(...)`
- `fetch_image_data(...)`
- `upload_image(...)`

---

## webhooks.php

Handles incoming webhook requests from MoboCore and queues them for deferred processing.

### Behavior

- Registers REST route:  
  `POST /mobo-core/v1/webhook`
- Validates security header (`X-SEC`)
- Saves payload into queue directory (`MOBO_CORE_WEBHOOK_FILE_DIR`)
- Scheduled task processes queued files sequentially

### Functions

**mobo_core_webhook_handler(WP_REST_Request $request)**  
Validates request, writes `.json` file, responds to client.

**mobo_core_read_webhook_interval()**  
Processes one queued webhook file at a time and dispatches data to `WooCommerceProductManager`.

---

## woocommerce-functions.php

Admin integration for product-level manual controls.

### Features

- Adds “MoboCore Actions” meta box in product edit page.
- Allows manual *ReSync* of a product via AJAX.
- Adds bulk action “Mobo ReSync”.
- Loads necessary admin JS.

### Functions

- `add_custom_meta_box()`
- `add_custom_button($post)`
- `enqueue_admin_mobo_script()`
- `handle_mobo_resync_action()`
- `handle_custom_resync_ajax()`

---

## api-functions.php
Namespace: `MoboCore`

A thin wrapper for the external MoboCore API.

### Key Functions

- `fetch_data_from_api($url)`
- `getProductsCount($onlyInStock)`
- `getImageByGuid($img_guid)`
- `getProductsAsJson($pageNumber, $recordPerPage, $onlyInStock)`
- `getProductByGuidAsJson($productGuid)`
- `getCategoriesAsJson()`
- `getLicenseInfo()`
- `get_ip()`

---

## category-functions.php
Namespace: `MoboCore`

Handles synchronizing external categories with WooCommerce.

### Main Class  
### **WooCommerceCategoryManager**

### Methods

- `addOrUpdateAllCategories($jsonArray)`
- `addOrUpdateCategory($data)`

---

## mobo_products.php

Admin page for listing WooCommerce products with their external GUID.

### Features

- Pagination
- Page size selector
- Bulk-selection checkbox
- Admin UI table

### Functions

- `custom_product_guid_page()`
- `render_mobo_products_page()`

---

## mobo_sync_page.php

Provides admin UI + scheduled tasks for full category + product sync.

### Features

- Start / stop sync
- Track sync progress
- Store page size, token, etc.
- Display license status

### Functions

- `mobo_core_sync_categories()`
- `mobo_core_sync_products_24()`
- `mobo_core_sync_products()`
- `mobo_core_sync_stop()`
- `mobo_core_sync_page()`

---

# Notes

This plugin relies heavily on:

- WooCommerce product CRUD APIs  
- WordPress REST API  
- WP Cron  
- WordPress options table  
- Filesystem-based queueing  

It is designed for reliable long-running synchronization between an external product source and WooCommerce.

---

# End of README.md
