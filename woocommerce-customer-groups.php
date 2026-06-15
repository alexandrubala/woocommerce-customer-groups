<?php
/**
 * Plugin bootstrap for WooCommerce Customer Groups.
 *
 * @package WooCommerce\CustomerGroups
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce Customer Groups
 * Plugin URI:        https://github.com/alexandrubala/woocommerce-customer-groups
 * Description:       Customer segmentation and role-based pricing for WooCommerce stores.
 * Version:           1.0.4
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            alexandrubala
 * Author URI:        https://github.com/alexandrubala
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       woocommerce-customer-groups
 * Domain Path:       /languages
 * WC requires at least: 7.0
 * WC tested up to:   9.0
 */

defined( 'ABSPATH' ) || exit;

define( 'WCCG_VERSION', '1.0.4' );
define( 'WCCG_FILE', __FILE__ );
define( 'WCCG_PATH', plugin_dir_path( __FILE__ ) );
define( 'WCCG_URL', plugin_dir_url( __FILE__ ) );
define( 'WCCG_BASENAME', plugin_basename( __FILE__ ) );
define( 'WCCG_TEXT_DOMAIN', 'woocommerce-customer-groups' );
define( 'WCCG_POST_TYPE', 'wc_customer_group' );
define( 'WCCG_USER_META_GROUP_ID', '_wccg_group_id' );
define( 'WCCG_META_DISCOUNT_TYPE', '_wccg_discount_type' );
define( 'WCCG_META_DISCOUNT_VALUE', '_wccg_discount_value' );
define( 'WCCG_META_DESCRIPTION', '_wccg_description' );
define( 'WCCG_META_VISIBILITY_MODE', '_wccg_visibility_mode' );
define( 'WCCG_META_ALLOWED_GROUP_IDS', '_wccg_allowed_group_ids' );
define( 'WCCG_META_ALLOWED_SHIPPING_METHODS', '_wccg_allowed_shipping_methods' );
define( 'WCCG_META_ALLOWED_PAYMENT_GATEWAYS', '_wccg_allowed_payment_gateways' );
define( 'WCCG_VISIBILITY_MODE_EVERYONE', 'everyone' );
define( 'WCCG_VISIBILITY_MODE_RESTRICTED', 'restricted' );

/**
 * Always use the plugin autoloader on production.
 *
 * The Composer vendor folder is optional and only needed for PHPCS tooling.
 * Loading vendor/autoload.php on servers with an outdated generated map can
 * prevent admin/ and frontend/ classes from being discovered.
 */
require_once WCCG_PATH . 'includes/Autoloader.php';
WooCommerce\CustomerGroups\Autoloader::register();

register_activation_hook( WCCG_FILE, array( 'WooCommerce\CustomerGroups\Installer', 'activate' ) );
register_deactivation_hook( WCCG_FILE, array( 'WooCommerce\CustomerGroups\Deactivator', 'deactivate' ) );

/**
 * Register the customer group post type as early as possible.
 *
 * @return void
 */
function wccg_register_post_type(): void {
	if ( ! class_exists( 'WooCommerce\CustomerGroups\PostTypes\CustomerGroupPostType' ) ) {
		return;
	}

	( new WooCommerce\CustomerGroups\PostTypes\CustomerGroupPostType() )->register();
}
add_action( 'init', 'wccg_register_post_type', 0 );

/**
 * Ensure the Customer Groups menu exists under WooCommerce.
 *
 * WooCommerce 8+ uses its own admin navigation and hides unrelated top-level
 * WordPress menus. Registering under WooCommerce keeps the screen discoverable.
 *
 * @return void
 */
function wccg_register_admin_menu(): void {
	if ( ! is_admin() ) {
		return;
	}

	wccg_register_post_type();

	if ( ! post_type_exists( WCCG_POST_TYPE ) ) {
		return;
	}

	if ( ! class_exists( 'WooCommerce\CustomerGroups\Capabilities' ) ) {
		return;
	}

	if ( ! WooCommerce\CustomerGroups\Capabilities::current_user_can_manage() ) {
		return;
	}

	if ( ! wccg_woocommerce_admin_menu_exists() ) {
		return;
	}

	$menu_slug = 'edit.php?post_type=' . WCCG_POST_TYPE;

	if ( wccg_admin_menu_exists( 'woocommerce', $menu_slug ) ) {
		return;
	}

	add_submenu_page(
		'woocommerce',
		__( 'Customer Groups', 'woocommerce-customer-groups' ),
		__( 'Customer Groups', 'woocommerce-customer-groups' ),
		WooCommerce\CustomerGroups\Capabilities::MANAGE_GROUPS,
		$menu_slug
	);
}
add_action( 'admin_menu', 'wccg_register_admin_menu', 99 );

/**
 * Check whether the WooCommerce admin menu is registered.
 *
 * @return bool
 */
function wccg_woocommerce_admin_menu_exists(): bool {
	global $menu;

	if ( ! is_array( $menu ) ) {
		return false;
	}

	foreach ( $menu as $menu_item ) {
		if ( isset( $menu_item[2] ) && 'woocommerce' === $menu_item[2] ) {
			return true;
		}
	}

	return false;
}

/**
 * Check whether an admin menu item already exists.
 *
 * @param string $parent_slug Parent menu slug.
 * @param string $menu_slug   Target menu slug.
 * @return bool
 */
function wccg_admin_menu_exists( string $parent_slug, string $menu_slug ): bool {
	global $submenu;

	if ( ! is_array( $submenu ) || ! isset( $submenu[ $parent_slug ] ) ) {
		return false;
	}

	foreach ( $submenu[ $parent_slug ] as $menu_item ) {
		if ( isset( $menu_item[2] ) && $menu_slug === $menu_item[2] ) {
			return true;
		}
	}

	return false;
}

add_action(
	'plugins_loaded',
	static function (): void {
		if ( class_exists( 'WooCommerce\CustomerGroups\Capabilities' ) ) {
			WooCommerce\CustomerGroups\Capabilities::register();
		}

		WooCommerce\CustomerGroups\Plugin::instance()->init();
	},
	20
);
