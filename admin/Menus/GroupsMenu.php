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
		add_action( 'admin_menu', array( $this, 'connect_wc_admin_pages' ), 5 );
		add_action( 'admin_menu', array( $this, 'register_menu' ), 999 );
	}

	/**
	 * Connect customer group screens with WooCommerce Admin.
	 *
	 * @return void
	 */
	public function connect_wc_admin_pages(): void {
		if ( ! function_exists( 'wc_admin_connect_page' ) ) {
			return;
		}

		wc_admin_connect_page(
			array(
				'id'        => 'wccg-customer-groups',
				'screen_id' => 'edit-' . WCCG_POST_TYPE,
				'title'     => __( 'Customer Groups', 'woocommerce-customer-groups' ),
				'path'      => add_query_arg( 'post_type', WCCG_POST_TYPE, 'edit.php' ),
			)
		);

		wc_admin_connect_page(
			array(
				'id'        => 'wccg-edit-customer-group',
				'parent'    => 'wccg-customer-groups',
				'screen_id' => WCCG_POST_TYPE,
				'title'     => __( 'Edit Customer Group', 'woocommerce-customer-groups' ),
			)
		);
	}

	/**
	 * Ensure the Customer Groups submenu appears under WooCommerce.
	 *
	 * WooCommerce 8+ rebuilds its admin navigation and may remove third-party
	 * submenu items registered earlier. Re-register late as a fallback.
	 *
	 * @return void
	 */
	public function register_menu(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$menu_slug = 'edit.php?post_type=' . WCCG_POST_TYPE;

		if ( $this->is_submenu_registered( 'woocommerce', $menu_slug ) ) {
			return;
		}

		add_submenu_page(
			'woocommerce',
			__( 'Customer Groups', 'woocommerce-customer-groups' ),
			__( 'Customer Groups', 'woocommerce-customer-groups' ),
			'manage_woocommerce',
			$menu_slug
		);
	}

	/**
	 * Check whether a submenu slug is already registered under a parent menu.
	 *
	 * @param string $parent    Parent menu slug.
	 * @param string $menu_slug Submenu slug to find.
	 * @return bool
	 */
	private function is_submenu_registered( string $parent, string $menu_slug ): bool {
		global $submenu;

		if ( empty( $submenu[ $parent ] ) || ! is_array( $submenu[ $parent ] ) ) {
			return false;
		}

		foreach ( $submenu[ $parent ] as $item ) {
			if ( isset( $item[2] ) && $menu_slug === $item[2] ) {
				return true;
			}
		}

		return false;
	}
}
