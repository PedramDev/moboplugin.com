# Function Reference & Execution Flow

This document explains what each major function does, its parameters, return values, and how it fits into the overall flow of the MoboCore → WooCommerce integration.

---

## 1. Namespace `MoboCore` – Helper & API Layer

### 1.1 `helper.php`

#### `parseJson($json)`
**Purpose**  
Iterate efficiently over a JSON-encoded list (from the API) without loading everything into memory at once.

**Parameters**
- `$json` (string): JSON string representing an array of items.

**Returns**
- `Generator`: Each `yield` is an array/object item decoded from the JSON.

**Side Effects / Notes**
- Throws an exception if JSON is invalid.
- Useful for large payloads from MoboCore.

---

### 1.2 `api-functions.php` – Class `ApiFunctions`

This class wraps all HTTP calls to `https://customers.mobomobo.ir/` and injects the API token stored in `mobo_core_token`.

#### `fetch_data_from_api($url)`
**Purpose**  
Base method for performing authenticated GET requests to the MoboCore API.

**Parameters**
- `$url` (string): Full API endpoint URL.

**Returns**
- `mixed`: Typically a decoded JSON array or string/raw body depending on implementation.

**Side Effects / Notes**
- Adds `Authorization`-like headers using the token from WP options.
- Handles low-level transport and error checks.

---

#### `getProductsCount($onlyInStock)`
**Purpose**  
Retrieve total number of products available in MoboCore, optionally only in-stock ones.

**Parameters**
- `$onlyInStock` (bool): Whether to count only in-stock products.

**Returns**
- `int`: Count of products as reported by the API.

---

#### `getImageByGuid($img_guid)`
**Purpose**  
Retrieve product image by GUID from MoboCore.

**Parameters**
- `$img_guid` (string): Image GUID in external system.

**Returns**
- `mixed`: Image content (binary, base64, or JSON depending on API).

---

#### `getProductsAsJson($pageNumber, $recordPerPage, $onlyInStock)`
**Purpose**  
Fetch a page of products as raw JSON from the MoboCore API.

**Parameters**
- `$pageNumber` (int): Page index (1-based or 0-based depending on API).
- `$recordPerPage` (int): Page size.
- `$onlyInStock` (bool): Filter to in-stock items or not.

**Returns**
- `string`: Raw JSON string returned by API.

---

#### `getProductByGuidAsJson($productGuid)`
**Purpose**  
Fetch a **single product** by external GUID.

**Parameters**
- `$productGuid` (string): MoboCore product GUID.

**Returns**
- `string`: Raw JSON string of the product.

---

#### `getCategoriesAsJson()`
**Purpose**  
Get the full category tree from MoboCore.

**Parameters**
- None.

**Returns**
- `string`: Raw JSON array of categories.

---

#### `getLicenseInfo()`
**Purpose**  
Fetch license/plan/subscription info from MoboCore.

**Parameters**
- None.

**Returns**
- `mixed`: License data (likely decoded JSON response).

---

#### `get_ip()`
**Purpose**  
Retrieve server IP as seen by MoboCore (debug / support use).

**Parameters**
- None.

**Returns**
- `string`: IP address.

---

## 2. Namespace `MoboCore` – Category Management

### 2.1 `category-functions.php` – Class `WooCommerceCategoryManager`

Categories are stored as `product_cat` terms, linked to external categories via a GUID meta key (e.g. `category_guid`).

#### `addOrUpdateAllCategories($jsonArray)`
**Purpose**  
Bulk process all categories from the API, creating/updating WooCommerce terms.

**Parameters**
- `$jsonArray` (array): Decoded category list from MoboCore.

**Returns**
- `void`

**Flow / Notes**
- Iterates the list and calls `addOrUpdateCategory($data)` for each.
- Can be used in scheduled sync or manual sync.

---

#### `addOrUpdateCategory($data)`
**Purpose**  
Create or update a single WooCommerce `product_cat` term based on MoboCore category data.

**Parameters**
- `$data` (array): Category info from API (GUID, name, parent, etc).

**Returns**
- `int|WP_Error`: Term ID on success, error otherwise.

**Flow / Notes**
- Looks up term by stored GUID meta.
- If not found, creates a new term and stores GUID into term meta.
- Updates name/slug/parent when category already exists.

---

## 3. Namespace `MoboCore` – Product Management

### 3.1 `product-functions.php` – Class `WooCommerceProductManager`

This is the main sync engine for products & variations.

#### `__construct()`
**Purpose**  
Initialize the manager: create lock directory, register hooks.

**Parameters**
- None.

**Returns**
- `void`

**Side Effects**
- Ensures `tmp/product_lock_{id}.lock` folder exists.
- Hooks into WordPress actions/filters as needed (e.g. for sync triggers).

---

#### `getCategoryUrls($categories)`
**Purpose**  
Extract category URLs from a category payload.

**Parameters**
- `$categories` (array): Category data from API.

**Returns**
- `array`: List of URLs.

---

#### `get_all_product_categories($guids)`
**Purpose**  
Convert external category GUIDs to WooCommerce category term IDs.

**Parameters**
- `$guids` (array): Array of external GUIDs.

**Returns**
- `array|null`: Array of WP term IDs or `null` if none resolved.

**Flow / Notes**
- Uses `get_terms()` or `get_term_meta()` to find terms with matching GUID meta.
- Used inside product processing to attach categories.

---

#### `fetch_image_data($url)`
**Purpose**  
Download image bytes from URL using WP HTTP API or cURL.

**Parameters**
- `$url` (string): Remote image URL.

**Returns**
- `string|false`: Raw binary string on success, `false` on failure.

---

#### `fetch_image_with_curl($url)`
**Purpose**  
Fallback low-level image fetching via cURL.

**Parameters**
- `$url` (string): Image URL.

**Returns**
- `string|false`: Binary data or `false`.

---

#### `upload_image($image_url)`
**Purpose**  
Sideload an external image into the WordPress Media Library.

**Parameters**
- `$image_url` (string): Direct URL to image.

**Returns**
- `int|false`: Attachment post ID on success, `false` on failure.

**Flow / Notes**
- Validates file mime/extension.
- Uses WordPress functions like `wp_upload_bits()` and `wp_insert_attachment()`.

---

#### `remove_variant($data)`
**Purpose**  
Remove a variant (WooCommerce variation) from a variable product based on external GUID.

**Parameters**
- `$data` (array): Payload including variant GUID and parent product GUID.

**Returns**
- `bool`: `true` on success, `false` otherwise.

---

#### `remove_product_category($data)`
**Purpose**  
Detach product from specific categories based on external GUIDs.

**Parameters**
- `$data` (array): Contains product GUID and list of category GUIDs to remove.

**Returns**
- `bool`

---

#### `update_product($data)`
**Purpose**  
Main entry point for **non-webhook** updates (manual or scheduled).

**Parameters**
- `$data` (array): Product payload from API.

**Returns**
- `bool`: `true` if processed successfully.

**Flow**
1. Prepares/validates data.
2. Delegates to `process_product_data($data, null)`.

---

#### `webhook_update_product($data, $filePath)`
**Purpose**  
Entry point specifically for **webhook queued** data.

**Parameters**
- `$data` (array): Product payload decoded from queue file.
- `$filePath` (string): Path to the processed webhook file.

**Returns**
- `bool`

**Flow**
1. Calls `process_product_data($data, $filePath)`.
2. On success, may delete or mark the file as processed.

---

#### `process_product_data($data, $filePath)`
**Purpose**  
Core pipeline that turns one external product payload into a fully updated WooCommerce product (plus variations, categories, images).

**Parameters**
- `$data` (array): Full product structure from API/webhook.
- `$filePath` (string|null): Queue filename (optional).

**Returns**
- `bool`: `true` on success.

**Flow / Steps (high level)**
1. Acquire per-product lock file (`product_lock_{product_guid}.lock`).
2. Resolve categories via `prepare_categories()`.
3. Use `get_or_create_product()` to locate or create WC product.
4. Apply `set_product_details()` for core fields (title, description, status, prices, stock).
5. `handle_images()` to sync featured + gallery images.
6. `update_attributes()` to ensure attributes exist on parent product.
7. `update_variants()` to create/update variations.
8. Release lock.
9. Optionally delete webhook queue file.

---

#### `prepare_categories($categories)`
**Purpose**  
Normalize category data to local WooCommerce term IDs.

**Parameters**
- `$categories` (array): Category list from payload.

**Returns**
- `array`: WooCommerce category term IDs.

---

#### `get_or_create_product($product_id, $attributes)`
**Purpose**  
Find an existing WooCommerce product by GUID, or create a new one.

**Parameters**
- `$product_id` (string): External product GUID.
- `$attributes` (array): Attributes from payload (may determine if product will be variable).

**Returns**
- `WC_Product`: WooCommerce product instance.

**Flow / Notes**
- Uses post meta to store external GUID.
- If attributes indicate multiple variants, creates a variable product.

---

#### `set_product_details($product, $isNew, $product_url, $title, $caption, $price, $comparePrice, $stock, $auto_options, $wp_category_ids, $publishedAt)`
**Purpose**  
Write all top-level product fields (title, slug, status, description, prices, stock, categories, custom meta).

**Parameters**
- `$product` (`WC_Product`)
- `$isNew` (bool)
- `$product_url` (string)
- `$title` (string)
- `$caption` (string / description)
- `$price` (float|int)
- `$comparePrice` (float|int|null)
- `$stock` (int)
- `$auto_options` (array): Pricing/stock behavior from global options.
- `$wp_category_ids` (array): Term IDs to assign.
- `$publishedAt` (string|int): Timestamp from external system.

**Returns**
- `void`

---

#### `set_conditional_price_without_compare_price($product, $price, $comparePrice, $auto_options)`
**Purpose**  
Apply pricing logic when the external compare price is empty.

**Parameters**
- `$product` (`WC_Product`)
- `$price` (float|int)
- `$comparePrice` (mixed): Likely `null` or `0`.
- `$auto_options` (array)

**Returns**
- `void`

---

#### `set_conditional_price_with_compare_price($product, $price, $comparePrice, $auto_options)`
**Purpose**  
Apply pricing logic when compare price exists.

**Parameters**
- Same as above, with non-null `$comparePrice`.

**Returns**
- `void`

---

#### `set_prices($product, $price, $comparePrice, $auto_options)`
**Purpose**  
Final assignment of `regular_price` and `sale_price` on the WooCommerce product.

**Parameters**
- `$product` (`WC_Product`)
- `$price` (float|int)
- `$comparePrice` (float|int|null)
- `$auto_options` (array)

**Returns**
- `void`

---

#### `handle_images($product, $images)`
**Purpose**  
Ensure product featured image and gallery match the external list.

**Parameters**
- `$product` (`WC_Product`)
- `$images` (array): Image URLs or GUID-based URLs.

**Returns**
- `void`

**Flow**
- For each image:
  - Download via `upload_image()`.
  - Attach to product.
- Remove attachments that are no longer in the external list (if implemented).

---

#### `update_attributes($product, $attributes, $wp_product_id)`
**Purpose**  
Create/update product-level attributes (e.g. Color, Size) required for variations.

**Parameters**
- `$product` (`WC_Product`)
- `$attributes` (array)
- `$wp_product_id` (int)

**Returns**
- `void`

---

#### `update_variants($product, $variants, $auto_options, $wp_product_id)`
**Purpose**  
Create or update each variation of a variable product.

**Parameters**
- `$product` (`WC_Product_Variable`)
- `$variants` (array): Each element describes one variant from MoboCore.
- `$auto_options` (array): Pricing/stock behavior.
- `$wp_product_id` (int)

**Returns**
- `void`

**Flow**
1. For each variant payload:
   - Find existing variation via `get_existing_variant_id()`.
   - If not found, create a new `product_variation`.
   - Call `set_variant_details()` and `set_variant_prices()`.

---

#### `get_existing_variant_id($product, $variant_guid)`
**Purpose**  
Look up an existing WooCommerce variation by external GUID.

**Parameters**
- `$product` (`WC_Product_Variable`)
- `$variant_guid` (string)

**Returns**
- `int|false`: Variation post ID or `false`.

---

#### `set_variant_details($existing_variant_id, $variation, $variant, $auto_options)`
**Purpose**  
Apply stock, status, attributes, and meta to a single variation.

**Parameters**
- `$existing_variant_id` (int)
- `$variation` (`WC_Product_Variation`)
- `$variant` (array): Variant payload from MoboCore.
- `$auto_options` (array)

**Returns**
- `void`

---

#### `set_variant_prices($existing_variant_id, $variation, $variant, $auto_options)`
**Purpose**  
Apply pricing logic for a single variation.

**Parameters**
- Same as `set_variant_details`.

**Returns**
- `void`

---

#### `get_global_product_options()`
**Purpose**  
Load plugin-wide product options from WP options table (pricing rules, auto publish, etc.).

**Parameters**
- None.

**Returns**
- `array`: Associative array of configuration.

---

## 4. Webhook & Admin Functions (Global Scope)

### 4.1 `webhooks.php`

#### `mobo_core_webhook_handler(WP_REST_Request $request)`
**Purpose**  
Handle incoming webhook POST, validate security, queue payload.

**Parameters**
- `$request` (`WP_REST_Request`): Incoming REST request.

**Returns**
- `WP_REST_Response`: JSON response with status.

**Flow**
1. Validate `X-SEC` header against `mobo_core_security_code` option.
2. Validate payload structure.
3. Write payload as `.json` into `MOBO_CORE_WEBHOOK_FILE_DIR`.
4. Return success or error.

---

#### `mobo_core_read_webhook_interval()`
**Purpose**  
Cron-like function that reads and processes queued webhook files.

**Parameters**
- None.

**Returns**
- `void`

**Flow**
1. Find next `.json` file in queue directory.
2. Decode JSON.
3. Instantiate `WooCommerceProductManager` (if not already).
4. Call `$manager->webhook_update_product($data, $filePath)`.
5. On success, delete or archive the file.

---

### 4.2 `woocommerce-functions.php`

#### `add_custom_meta_box()`
**Purpose**  
Register "MoboCore Actions" meta box on the product edit screen.

**Parameters**
- None.

**Returns**
- `void`

---

#### `add_custom_button($post)`
**Purpose**  
Render the meta box content including the ReSync button.

**Parameters**
- `$post` (`WP_Post`): Current product post.

**Returns**
- `void`

---

#### `enqueue_admin_mobo_script()`
**Purpose**  
Enqueue admin-side JS for handling ReSync AJAX and UI interactions.

**Parameters**
- None.

**Returns**
- `void`

---

#### `handle_mobo_resync_action()`
**Purpose**  
Handle bulk action "Mobo ReSync" from the products list table.

**Parameters**
- None (uses global request variables).

**Returns**
- `void`

**Flow**
- Get selected product IDs.
- For each, call product manager to re-import or refresh.

---

#### `handle_custom_resync_ajax()`
**Purpose**  
AJAX endpoint called by ReSync button in meta box.

**Parameters**
- None (reads `$_POST` or `$_REQUEST`).

**Returns**
- JSON output / echo.

**Flow**
- Validate nonce & permissions.
- Call product manager for the single product.

---

## 5. Admin Pages – `mobo_products.php`, `mobo_sync_page.php`

### 5.1 `mobo_products.php`

#### `custom_product_guid_page()`
**Purpose**  
Register "Mobo Products" admin menu page.

**Parameters**
- None.

**Returns**
- `void`

---

#### `render_mobo_products_page()`
**Purpose**  
Render the admin page listing WooCommerce products with their external GUIDs.

**Parameters**
- None.

**Returns**
- `void` (outputs HTML)

**Flow**
- Query `product` posts (paged).
- Display table (ID, title, GUID, etc.).
- Provide pagination & page size selection.

---

### 5.2 `mobo_sync_page.php`

#### `mobo_core_sync_categories()`
**Purpose**  
Scheduled job to pull categories from MoboCore and sync them.

**Parameters**
- None.

**Returns**
- `void`

**Flow**
- Call `ApiFunctions::getCategoriesAsJson()`.
- Decode JSON.
- Call `WooCommerceCategoryManager::addOrUpdateAllCategories()`.

---

#### `mobo_core_sync_products_24()`
**Purpose**  
Legacy/alternative product sync that processes 24 pages at a time.

---

#### `mobo_core_sync_products()`
**Purpose**  
Main recurring product sync task.

**Parameters**
- None.

**Returns**
- `void`

**Flow**
1. Read current page index & state from WP options.
2. Call `ApiFunctions::getProductsAsJson($page, $pageSize, $onlyInStock)`.
3. For each product, call `WooCommerceProductManager::update_product()`.
4. Update progress options.
5. Re-schedule itself until all pages are processed.

---

#### `mobo_core_sync_stop()`
**Purpose**  
Stop any ongoing sync and clear state.

**Flow**
- Clear WP options like `mobo_sync_page`, `mobo_sync_product_left`, etc.
- Unschedule cron events (`mobo_core_sync_categories_event`, `mobo_core_sync_products_event`, etc.).

---

#### `mobo_core_sync_page()`
**Purpose**  
Render the sync configuration/status admin page.

**Flow**
- Show fields: API token, page size, only-in-stock toggle, etc.
- Show buttons: Start Sync, Stop Sync.
- Display license status, last sync info, remaining products, etc.

---

# 6. High-Level System Flows

## 6.1 Scheduled Full Sync (Products & Categories)

1. Admin configures token, page size, etc. in **Sync Page** (`mobo_core_sync_page()`).
2. Admin clicks “Start Sync”:
   - Options are saved (page size, flags).
   - Cron events for `mobo_core_sync_categories_event` and/or `mobo_core_sync_products_event` are scheduled.
3. `mobo_core_sync_categories()` runs:
   - Calls API → categories.
   - Updates WooCommerce via `WooCommerceCategoryManager`.
4. `mobo_core_sync_products()` runs (repeatedly):
   - Reads page index from options.
   - Calls `ApiFunctions::getProductsAsJson(...)`.
   - For each product → `WooCommerceProductManager::update_product()`.
   - Updates state & re-schedules until all pages done.

---

## 6.2 Webhook Flow

1. External system sends `POST` to `/wp-json/mobo-core/v1/webhook` with `X-SEC` header.
2. `mobo_core_webhook_handler()`:
   - Validates security.
   - Writes payload to `MOBO_CORE_WEBHOOK_FILE_DIR` as `.json`.
3. A cron/job triggers `mobo_core_read_webhook_interval()`:
   - Reads next file.
   - Decodes JSON.
   - Calls `$manager->webhook_update_product($data, $filePath)`.
4. `webhook_update_product()` delegates to `process_product_data()`, which:
   - Locks product.
   - Resolves categories.
   - Creates/updates product + variations.
   - Updates images/prices/stock.
   - Unlocks and optionally deletes queue file.

---

## 6.3 Manual Product Resync (Admin)

1. In product edit screen:
   - Meta box “MoboCore Actions” is rendered by `add_custom_button($post)`.
   - A ReSync button triggers JS → `handle_custom_resync_ajax()`.
2. AJAX handler:
   - Validates request.
   - Calls product manager to refresh that specific product from external source.
3. In products list:
   - Bulk action “Mobo ReSync” handled by `handle_mobo_resync_action()`.
   - Loops over selected product IDs and performs the same logic.

---

## 6.4 Product Processing Pipeline (Detailed)

For **each** product payload from API / webhook:

1. Lock file created: `product_lock_{product_guid}.lock`.
2. Categories normalized via `prepare_categories()` / `get_all_product_categories()`.
3. `get_or_create_product()` finds or creates WooCommerce product.
4. `set_product_details()` sets:
   - Title, slug, description.
   - Categories, status.
   - Base prices, stock, custom meta.
5. `handle_images()`:
   - Downloads new images.
   - Sets featured image and gallery.
6. `update_attributes()`:
   - Ensures attributes and taxonomy terms exist for variations.
7. `update_variants()`:
   - For each variant:
     - `get_existing_variant_id()`
     - `set_variant_details()`
     - `set_variant_prices()`
8. Product & variations are saved.
9. Lock is released.
10. For webhook path, queue file is marked processed.

---

# End of Function & Flow Documentation
