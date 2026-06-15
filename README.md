# WooCommerce Customer Groups

Customer segmentation and role-based pricing for WooCommerce stores.

Create customer groups (VIP, Reseller, Distributor, Partner), assign them to users, and configure group-level discounts. Built with PHP 8.0+, OOP architecture, and WordPress Coding Standards.

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

### v1.0 (current)

- Create and manage customer groups from **WooCommerce → Customer Groups**
- Configure global group discounts (percentage or fixed amount)
- Assign a group to any user from their profile
- View assigned groups in the Users list table

### Roadmap

- **v1.1** — Apply group discounts on product pages, cart, and checkout
- **v2** — Product visibility restricted by group
- **v3** — Shipping method restrictions by group
- **v4** — Payment gateway restrictions by group

## License

GPL-2.0-or-later
