# WooCommerce Customer Groups

Customer segmentation, role-based pricing, and checkout restrictions for WooCommerce stores.

Create customer groups (VIP, Reseller, Distributor, Partner), assign them to users, and configure group-level discounts, product visibility, shipping methods, and payment gateways. Built with PHP 8.0+, OOP architecture, and WordPress Coding Standards.

**Current version:** 1.0.7

## Changelog

### 1.0.7

- Added `uninstall.php` to remove customer group posts, product meta, user meta, and role capabilities on plugin deletion
- Hardened admin saves: WooCommerce product nonce verification, autosave/revision guards, and post-type checks
- Standardized i18n via the `WCCG_TEXT_DOMAIN` constant across all translatable strings
- Improved input sanitization (`wp_unslash()` centralized in `Sanitizer`)
- Fixed unescaped attribute output in the product visibility panel
- Replaced anonymous `plugins_loaded` closure with prefixed `wccg_bootstrap_plugin()` function
- Added `WCCG_CACHE_GROUP` constant for object-cache namespacing

### 1.0.6

- Previous stable release

## Requirements

| Component    | Minimum |
| ------------ | ------- |
| WordPress    | 6.0+    |
| PHP          | 8.0+    |
| WooCommerce  | 7.0+    |

## Installation

1. Clone or upload into `wp-content/plugins/`:

   ```bash
   git clone https://github.com/alexandrubala/woocommerce-customer-groups.git
   ```

2. Install Composer dependencies (development tooling only):

   ```bash
   cd woocommerce-customer-groups
   composer install
   ```

3. Activate **WooCommerce Customer Groups** in WordPress (WooCommerce must be active).

**Production deploy:** the plugin uses a built-in autoloader. Do **not** upload the `vendor/` folder to production — run `composer install` only locally for PHPCS tooling.

After upgrading, deactivate and reactivate the plugin once so capabilities are refreshed for administrator and shop manager roles.

## Admin menu

Customer groups are managed from the standalone **Customer Groups** item in the WordPress admin sidebar (icon: groups), usually near **WooCommerce** and **Products**.

| Location | Purpose |
| -------- | ------- |
| **Customer Groups** | List, create, and edit groups (discounts, shipping, payment rules) |
| **Users → Edit user** | Assign a customer group to a user |
| **Products → Customer Groups** tab | Restrict product visibility by group |

Direct URL: `/wp-admin/edit.php?post_type=wc_customer_group`

Access requires the `edit_wccg_groups` capability, granted to **Administrators** and **Shop Managers** on activation.

## Development

```bash
composer phpcs    # Run coding standards
composer phpcbf   # Auto-fix coding standards
```

Generate translation template:

```bash
wp i18n make-pot . languages/woocommerce-customer-groups.pot
```

## Features

### Customer groups

- Create and manage groups from **Customer Groups** in the admin sidebar
- Configure group-level discounts (percentage or fixed amount)
- Optional internal description per group
- Assign a group to any user from their profile
- View assigned groups in the Users list table

### Pricing

- Apply group discounts on product pages, cart, and checkout
- Display discounted prices for logged-in customers with an active group discount

### Product visibility

- Per-product visibility on the **Customer Groups** product data tab
- **Everyone** — visible to all visitors (default)
- **Restricted** — visible only to customers in selected groups
- Hidden products are excluded from shop, search, and direct access for unauthorized users

### Shipping restrictions

- Per-group allowed shipping methods on the group edit screen
- Checkbox list of enabled methods, grouped by WooCommerce zone (title + rate ID)
- Leave all unchecked to allow every method; select specific methods to restrict checkout
- Stale rate IDs are removed automatically when a group is saved
- Works on classic checkout and WooCommerce Checkout Blocks

### Payment gateway restrictions

- Per-group allowed payment gateways on the group edit screen
- Checkbox list of enabled gateways (title + gateway ID)
- Leave all unchecked to allow every gateway; select specific gateways to restrict checkout
- Stale gateway IDs are removed automatically when a group is saved
- Works on classic checkout and WooCommerce Checkout Blocks

### Architecture

- Service container with dedicated admin and frontend service providers
- Built-in PSR-4 autoloader (no Composer required on production)
- `CustomerGroup` model, repository caching, and `GroupResolver` for user-to-group mapping
- Sanitization helpers and extensibility hooks (`wccg_group_meta`, `wccg_package_rates`, `wccg_available_payment_gateways`, `wccg_product_visible`, and others)
- Translation-ready strings via the `woocommerce-customer-groups` text domain

### Roadmap

- Admin list column for allowed shipping/payment methods (optional UI enhancement)
- Additional group-based rules and reporting as needed

## License

GPL-2.0-or-later
