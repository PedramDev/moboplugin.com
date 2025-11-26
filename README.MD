# MoboCore – WooCommerce Integration

This plugin synchronizes products, categories, variations, prices, and stock levels from the MoboCore API into WooCommerce.
It supports scheduled syncing, real-time webhooks, manual resync controls, and a filesystem-based webhook queue system.

---

## Documentation

Full documentation is located in the `/docs` directory.


### 1. README.MD
Full documentation

Link: [./docs/README.MD](./docs/README.MD)


### 2. FUNCTIONS.MD
Detailed reference for every function, class, and subsystem in the plugin.

Link: [./docs/FUNCTIONS.MD](./docs/FUNCTIONS.MD)

---

### 3. DIAGRAMS.MD
Visual architecture diagrams using Mermaid, including:

- System architecture
- Product processing pipeline
- Webhook queue flow

Link: [./docs/DIAGRAMS.MD](./docs/DIAGRAMS.MD)

---
## Features

- Product sync from MoboCore API
- Variable product and variation support
- Image downloading and media library sideloading
- Category mapping using external GUIDs
- Webhook ingestion with filesystem-based queue
- Manual product resync tools in admin
- Cron-based automated syncing
- Per-product locking to ensure safe operations

---

```markdown
## Folder Structure

mobo-core/
│
├── api-functions.php
├── category-functions.php
├── product-functions.php
├── webhooks.php
├── woocommerce-functions.php
├── mobo_products.php
├── mobo_sync_page.php
├── helper.php
│
└── docs/
    ├── README.md
    ├── FUNCTIONS.md
    └── DIAGRAMS.md
```

---
## System Overview

- Products and variations sync via external GUID identifiers.
- Categories sync using GUID-based term meta mapping.
- Webhooks save payloads as JSON files for safe, asynchronous processing.
- Cron jobs handle bulk imports in batches to prevent overload.
- Lock files ensure each product is processed safely without duplicates.

---
