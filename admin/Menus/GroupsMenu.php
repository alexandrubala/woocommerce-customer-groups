<?php
/**
 * WooCommerce submenu for customer groups.
 *
 * @package WooCommerce\CustomerGroups
 */

namespace WooCommerce\CustomerGroups\Admin\Menus;

defined( 'ABSPATH' ) || exit;

/**
 * Class GroupsMenu
 */
final class GroupsMenu {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ), 60 );
	}

	/**
	 * Register the Customer Groups submenu under WooCommerce.
	 *
	 * @return void
	 */
	public function register_menu(): void {
		add_submenu_page(
			'woocommerce',
			__( 'Customer Groups', 'woocommerce-customer-groups' ),
			__( 'Customer Groups', 'woocommerce-customer-groups' ),
			'manage_woocommerce',
			'edit.php?post_type=' . WCCG_POST_TYPE
		);
	}
}
