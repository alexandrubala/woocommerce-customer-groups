<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package WooCommerce\CustomerGroups
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

define( 'WCCG_PATH', plugin_dir_path( __FILE__ ) );
define( 'WCCG_POST_TYPE', 'wc_customer_group' );
define( 'WCCG_USER_META_GROUP_ID', '_wccg_group_id' );
define( 'WCCG_META_VISIBILITY_MODE', '_wccg_visibility_mode' );
define( 'WCCG_META_ALLOWED_GROUP_IDS', '_wccg_allowed_group_ids' );

require_once WCCG_PATH . 'includes/Autoloader.php';

WooCommerce\CustomerGroups\Autoloader::register();

WooCommerce\CustomerGroups\Uninstaller::run();
