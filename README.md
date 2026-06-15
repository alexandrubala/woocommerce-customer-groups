# WooCommerce Customer Groups

Customer segmentation, role-based pricing, and checkout restrictions for WooCommerce stores.

Create customer groups (VIP, Reseller, Distributor, Partner), assign them to users, and configure group-level discounts, product visibility, shipping methods, and payment gateways. Built with PHP 8.0+, OOP architecture, and WordPress Coding Standards.

## Requirements

| Component    | Minimum |
| ------------ | ------- |
| WordPress    | 6.0+    |
| PHP          | 8.0+    |
| WooCommerce  | 7.0+    |

## Installation

1. Clone into `wp-content/plugins/`:

   ```bash
   git clone https://github.com/alexandrubala/woocommerce-customer-groups.git
   ```

2. Install Composer dependencies (development tooling):

   ```bash
   cd woocommerce-customer-groups
   composer install
   ```

3. Activate **WooCommerce Customer Groups** in WordPress (WooCommerce must be active).

No Composer step is required on production — the plugin ships with a built-in autoloader. Do **not** upload the `vendor/` folder to production. Run `composer install` only locally for PHPCS tooling.

## Admin menu

Customer groups are managed from **WooCommerce → Customer Groups** in the WordPress admin sidebar.

- **WooCommerce → Customer Groups** — list, create, and edit groups
- **Users → Edit user** — assign a group to a customer
- **Products → Customer Groups** tab — restrict product visibility by group

Direct URL: `/wp-admin/edit.php?post_type=wc_customer_group`

Requires the `wccg_manage_groups` capability (granted to Administrators and Shop Managers on activation). Current plugin version: **1.0.4**.

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

- Create and manage customer groups from **WooCommerce → Customer Groups**
- Configure group-level discounts (percentage or fixed amount)
- Optional internal description per group
- Assign a group to any user from their profile
- View assigned groups in the Users list table

### Pricing

- Apply group discounts on product pages, cart, and checkout
- Display discounted prices for logged-in customers with an active group discount

### Product visibility

- Per-product visibility settings on the **Customer Groups** product data tab
- **Everyone** — product is visible to all visitors (default)
- **Restricted** — product is visible only to customers in selected groups
- Hidden products are excluded from shop, search, and direct access for unauthorized users

### Shipping restrictions

- Per-group allowed shipping methods on the group edit screen
- Checkbox list of all enabled shipping methods, grouped by WooCommerce zone (title + rate ID)
- Leave all unchecked to allow every method; select specific methods to restrict checkout options
- Stale rate IDs are automatically removed when a group is saved
- Works on classic checkout and WooCommerce Checkout Blocks

### Payment gateway restrictions

- Per-group allowed payment gateways on the group edit screen
- Checkbox list of all enabled payment gateways (title + gateway ID)
- Leave all unchecked to allow every gateway; select specific gateways to restrict checkout options
- Stale gateway IDs are automatically removed when a group is saved
- Works on classic checkout and WooCommerce Checkout Blocks

### Architecture

- Service container with dedicated admin and frontend service providers
- `CustomerGroup` model, repository caching, and `GroupResolver` for user-to-group mapping
- Sanitization helpers and extensibility hooks (`wccg_group_meta`, `wccg_package_rates`, `wccg_available_payment_gateways`, and others)
- Translation-ready strings via the `woocommerce-customer-groups` text domain

### Roadmap

- Admin list column for allowed shipping/payment methods (optional UI enhancement)
- Additional group-based rules and reporting as needed

## License

GPL-2.0-or-later
