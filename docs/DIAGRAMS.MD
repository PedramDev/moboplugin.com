# MoboCore – System Diagrams

Below are all visual architecture and flow diagrams for the WooCommerce integration system.

All diagrams use Mermaid syntax, compatible with GitHub, GitLab, Notion, MkDocs, and many renderers.

---

# 1. System Architecture Overview

```mermaid
flowchart TD

    A[MoboCore API] 
    A -->|Products / Categories / Images| B[ApiFunctions.php]

    subgraph CronJobs[WP Cron Jobs]
        C1[mobo_core_sync_categories]
        C2[mobo_core_sync_products]
    end

    B --> C1
    B --> C2

    C1 --> D[WooCommerceCategoryManager<br/>category-functions.php]
    C2 --> E[WooCommerceProductManager<br/>product-functions.php]

    D -->|Create/Update Terms| WC[(WooCommerce)]
    E -->|Create/Update Products & Variations| WC

    subgraph Webhooks[Webhooks]
        W1[mobo_core_webhook_handler]
        W2[mobo_core_read_webhook_interval]
    end

    A -->|Webhook POST| W1
    W1 -->|Write JSON to Queue| Q[Webhook Queue Directory]

    Q -->|Read Next JSON| W2
    W2 -->|Decoded Payload| E

    subgraph Admin[WordPress Admin]
        M1[Mobo Products Page]
        M2[Sync Page]
        M3[MoboCore Meta Box]
    end

    Admin --> E
    M3 -->|Manual ReSync| E

    E --> WC
```

# 2. Product Processing Pipeline

```mermaid
sequenceDiagram
    autonumber

    participant API as MoboCore API
    participant Cron as WP Cron / Webhook
    participant PM as ProductManager
    participant WC as WooCommerce
    participant FS as File System (Locks/Queue)

    Cron->>API: Fetch product JSON
    Cron->>PM: update_product(data)

    PM->>FS: Create lock file

    PM->>PM: prepare_categories()
    PM->>WC: Assign/Create categories

    PM->>WC: get_or_create_product(guid)
    PM->>WC: set_product_details()
    PM->>WC: set_prices()

    PM->>PM: handle_images()
    PM->>WC: Upload + attach images

    PM->>PM: update_attributes()
    PM->>PM: update_variants()

    PM->>WC: Create/Update variations

    PM->>FS: Release lock
```

# 3. Webhook Queue Workflow

```mermaid
flowchart LR

    A[MoboCore Webhook POST] -->|X-SEC| B[mobo_core_webhook_handler]

    B -->|Validate| C{Valid?}
    C -->|No| Z[Reject Request (403)]
    C -->|Yes| D[Write JSON File]

    D --> F[mobo_core_read_webhook_interval]
    F --> G[Read Next JSON]
    G --> H[Decode JSON]
    H --> I[ProductManager<br/>webhook_update_product()]
    I --> J[process_product_data()]
    J --> K[Update Product & Variants]
    K --> L[Delete Queue File]
```
