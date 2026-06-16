=== WooCommerce Customer Groups ===
Contributors: alexandrubala
Tags: woocommerce, customers, pricing, groups, b2b
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 8.0
Stable tag: 1.0.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Customer segmentation, role-based pricing, and checkout restrictions for WooCommerce stores.

== Description ==

WooCommerce Customer Groups helps store owners segment customers into groups such as VIP, Reseller, Distributor, or Partner. Assign groups to users and configure group-level discounts, product visibility, shipping methods, and payment gateways.

= Features =

* Create and manage customer groups from a dedicated admin menu
* Configure percentage or fixed-amount group discounts
* Assign a group to any customer from their user profile
* Apply group pricing on product pages, cart, and checkout
* Restrict product visibility per group on the product edit screen
* Limit available shipping methods and payment gateways per group
* Works with classic checkout and WooCommerce Checkout Blocks
* Built-in PSR-4 autoloader (no Composer `vendor/` folder required in production)

= Requirements =

* WordPress 6.0 or higher
* PHP 8.0 or higher
* WooCommerce 7.0 or higher

== Installation ==

1. Upload the `woocommerce-customer-groups` folder to the `/wp-content/plugins/` directory, or install the plugin through the WordPress Plugins screen.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Ensure **WooCommerce** is installed and active.
4. Open **Customer Groups** in the WordPress admin sidebar to create your first group.
5. Assign groups to customers under **Users**, and configure product visibility under **Products**.

After upgrading, deactivate and reactivate the plugin once so capabilities are refreshed for administrator and shop manager roles.

**Note for developers:** Composer is only used for local PHPCS tooling. Do not upload the `vendor/` directory to WordPress.org or production servers; the plugin ships with its own autoloader.

== Frequently Asked Questions ==

= Does this plugin work without WooCommerce? =

No. WooCommerce must be installed and active. If WooCommerce is missing, the plugin shows an admin notice and does not load its features.

= Who can manage customer groups? =

Administrators and shop managers with the `edit_wccg_groups` capability.

= Can I leave shipping or payment restrictions empty? =

Yes. If no methods or gateways are selected for a group, all enabled options remain available at checkout.

== Changelog ==

= 1.0.6 =
* Initial WordPress.org release
* Customer groups with percentage or fixed discounts
* Per-user group assignment and user list column
* Product visibility rules by customer group
* Per-group shipping method and payment gateway restrictions
* Service container with admin and frontend service providers
* Built-in PSR-4 autoloader (no Composer required on production)
* `uninstall.php` removes customer group posts, product meta, user meta, and role capabilities on plugin deletion
* Hardened admin saves with WooCommerce product nonce verification and autosave guards
* Standardized i18n via the `woocommerce-customer-groups` text domain
* Graceful dependency checks with admin notices when WooCommerce is inactive

== Upgrade Notice ==

= 1.0.6 =
Initial public release for the WordPress Plugin Directory.
