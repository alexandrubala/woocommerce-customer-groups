<?php
/**
 * Plugin activation routines.
 *
 * @package WooCommerce\CustomerGroups
 */

namespace WooCommerce\CustomerGroups;

use WooCommerce\CustomerGroups\PostTypes\CustomerGroupPostType;

defined( 'ABSPATH' ) || exit;

/**
 * Class Installer
 */
final class Installer {

	/**
	 * Run activation tasks.
	 *
	 * @return void
	 */
	public static function activate(): void {
		$requirements = new Services\RequirementsChecker();

		if ( ! $requirements->passes( false ) ) {
			deactivate_plugins( WCCG_BASENAME );

			wp_die(
				esc_html__( 'WooCommerce Customer Groups requires WordPress 6.0+, PHP 8.0+, and an active WooCommerce installation.', 'woocommerce-customer-groups' ),
				esc_html__( 'Plugin Activation Error', 'woocommerce-customer-groups' ),
				array( 'back_link' => true )
			);
		}

		$post_type = new CustomerGroupPostType();
		$post_type->register();

		flush_rewrite_rules();
	}
}
