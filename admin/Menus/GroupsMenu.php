<?php
/**
 * WooCommerce Admin integration for customer group screens.
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
		add_action( 'admin_menu', array( $this, 'connect_wc_admin_pages' ), 100 );
	}

	/**
	 * Connect customer group screens with WooCommerce Admin headers.
	 *
	 * @return void
	 */
	public function connect_wc_admin_pages(): void {
		if ( ! function_exists( 'wc_admin_connect_page' ) ) {
			return;
		}

		$list_path = add_query_arg( 'post_type', WCCG_POST_TYPE, 'edit.php' );

		wc_admin_connect_page(
			array(
				'id'        => 'wccg-customer-groups',
				'screen_id' => 'edit-' . WCCG_POST_TYPE,
				'title'     => __( 'Customer Groups', WCCG_TEXT_DOMAIN ),
				'path'      => $list_path,
			)
		);

		wc_admin_connect_page(
			array(
				'id'        => 'wccg-customer-groups-add',
				'parent'    => 'wccg-customer-groups',
				'screen_id' => WCCG_POST_TYPE . '-add',
				'title'     => __( 'Add New Customer Group', WCCG_TEXT_DOMAIN ),
			)
		);

		wc_admin_connect_page(
			array(
				'id'        => 'wccg-edit-customer-group',
				'parent'    => 'wccg-customer-groups',
				'screen_id' => WCCG_POST_TYPE,
				'title'     => __( 'Edit Customer Group', WCCG_TEXT_DOMAIN ),
			)
		);
	}
}
