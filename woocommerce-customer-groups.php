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
 * Version:           1.0.1
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

define( 'WCCG_VERSION', '1.0.1' );
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

$wccg_autoloader = WCCG_PATH . 'vendor/autoload.php';

if ( is_readable( $wccg_autoloader ) ) {
	require_once $wccg_autoloader;
} else {
	require_once WCCG_PATH . 'includes/Autoloader.php';
	WooCommerce\CustomerGroups\Autoloader::register();
}

register_activation_hook( WCCG_FILE, array( 'WooCommerce\CustomerGroups\Installer', 'activate' ) );
register_deactivation_hook( WCCG_FILE, array( 'WooCommerce\CustomerGroups\Deactivator', 'deactivate' ) );

add_action(
	'plugins_loaded',
	static function (): void {
		WooCommerce\CustomerGroups\Plugin::instance()->init();
	},
	20
);
