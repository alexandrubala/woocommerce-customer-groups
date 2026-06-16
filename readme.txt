=== WooCommerce Customer Groups ===
Contributors: alexandrubala
Tags: woocommerce, customer groups, b2b, role based pricing, discounts
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 8.0
Stable tag: 1.0.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Customer segmentation, role-based pricing, and checkout restrictions for WooCommerce stores.

== Description ==

WooCommerce Customer Groups helps store owners segment customers into groups such as VIP, Reseller, Distributor, or Partner. Assign groups to users and configure group-level discounts, product visibility, shipping methods, and payment gateways.

= Customer groups =

* Create and manage customer groups from a dedicated admin menu
* Configure percentage or fixed-amount group discounts
* Add an optional internal description for each group
* Assign a group to any customer from their user profile
* View assigned groups in the Users list table

= Pricing =

* Apply group discounts on product pages, cart, and checkout
* Show discounted prices for logged-in customers with an active group discount

= Product visibility =

* Control product visibility from the Customer Groups tab on the product edit screen
* Visible to everyone (default) or restricted to selected groups
* Hide restricted products from shop listings, search, and direct access for unauthorized users

= Checkout restrictions =

* Limit available shipping methods per group
* Limit available payment gateways per group
* Leave all options unchecked to allow every enabled method or gateway
* Works with classic checkout and WooCommerce Checkout Blocks

= Requirements =

* WordPress 6.0 or higher
* PHP 8.0 or higher
* WooCommerce 7.0 or higher

== Installation ==

1. Upload the woocommerce-customer-groups folder to the /wp-content/plugins/ directory, or install the plugin through the WordPress Plugins screen.
2. Activate the plugin through the Plugins screen in WordPress.
3. Ensure WooCommerce is installed and active.
4. Open Customer Groups in the WordPress admin sidebar to create your first group.
5. Assign groups to customers under Users, and configure product visibility under Products.

After upgrading, deactivate and reactivate the plugin once so capabilities are refreshed for administrator and shop manager roles.

== Frequently Asked Questions ==

= Does this plugin work without WooCommerce? =

No. WooCommerce must be installed and active. If WooCommerce is missing, the plugin shows an admin notice and does not load its storefront features.

= Who can manage customer groups? =

Administrators and shop managers with the edit_wccg_groups capability, granted automatically on plugin activation.

= Can I leave shipping or payment restrictions empty? =

Yes. If no shipping methods or payment gateways are selected for a group, all enabled options remain available at checkout for customers in that group.

= What happens when the plugin is deleted? =

When the plugin is uninstalled (not just deactivated), customer group posts, product visibility settings, user group assignments, and plugin-specific role capabilities are removed from the database.

= Does this plugin require Composer on the server? =

No. The plugin includes a built-in autoloader and does not require a vendor folder in production.

== Screenshots ==

1. Customer Groups admin list.
2. Customer group edit screen.
3. User profile group assignment.
4. Product visibility restrictions.
5. Shipping and payment restrictions.

== Changelog ==

= 1.0.6 =
* Initial WordPress.org release
* Customer groups with percentage or fixed-amount discounts
* Per-user group assignment and Users list column
* Product visibility rules by customer group
* Per-group shipping method and payment gateway restrictions
* Dedicated Customer Groups admin menu
* Built-in PSR-4 autoloader with no Composer dependency in production
* Uninstall routine to remove plugin data on deletion
* Graceful dependency checks with admin notices when WooCommerce is inactive

== Upgrade Notice ==

= 1.0.6 =
Initial public release for the WordPress Plugin Directory.
