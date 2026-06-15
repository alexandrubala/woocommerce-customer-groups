<?php
/**
 * Admin menus for customer groups.
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
	 * Top-level admin menu slug.
	 */
	private const TOP_LEVEL_SLUG = 'wccg-customer-groups';

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_action( 'admin_menu', array( $this, 'register_menus' ), 6 );
		add_action( 'admin_menu', array( $this, 'connect_wc_admin_pages' ), 6 );
		add_action( 'admin_head', array( $this, 'highlight_menu' ) );
		add_filter( 'woocommerce_admin_menu_tree', array( $this, 'add_to_woocommerce_menu_tree' ), 20 );
	}

	/**
	 * Register admin menu entries.
	 *
	 * @return void
	 */
	public function register_menus(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$menu_title = __( 'Customer Groups', 'woocommerce-customer-groups' );
		$list_slug  = 'edit.php?post_type=' . WCCG_POST_TYPE;

		$top_level_hook = add_menu_page(
			$menu_title,
			$menu_title,
			'manage_woocommerce',
			self::TOP_LEVEL_SLUG,
			'__return_null',
			'dashicons-groups',
			56.6
		);

		if ( is_string( $top_level_hook ) ) {
			add_action( 'load-' . $top_level_hook, array( $this, 'redirect_to_list_screen' ) );
		}

		if ( $this->is_woocommerce_menu_available() ) {
			add_submenu_page(
				'woocommerce',
				$menu_title,
				$menu_title,
				'manage_woocommerce',
				$list_slug
			);
		}
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

		$list_path = add_query_arg( 'post_type', WCCG_POST_TYPE, 'edit.php' );

		wc_admin_connect_page(
			array(
				'id'        => 'wccg-customer-groups',
				'screen_id' => 'edit-' . WCCG_POST_TYPE,
				'title'     => __( 'Customer Groups', 'woocommerce-customer-groups' ),
				'path'      => $list_path,
			)
		);

		wc_admin_connect_page(
			array(
				'id'        => 'wccg-customer-groups-add',
				'parent'    => 'wccg-customer-groups',
				'screen_id' => WCCG_POST_TYPE . '-add',
				'title'     => __( 'Add New Customer Group', 'woocommerce-customer-groups' ),
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
	 * Place Customer Groups directly under WooCommerce in nested admin navigation.
	 *
	 * @param array<string, array<string, mixed>> $tree Navigation tree.
	 * @return array<string, array<string, mixed>>
	 */
	public function add_to_woocommerce_menu_tree( array $tree ): array {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return $tree;
		}

		$tree['wccg-customer-groups'] = array(
			'parent' => 'woocommerce',
			'title'  => __( 'Customer Groups', 'woocommerce-customer-groups' ),
			'url'    => admin_url( 'edit.php?post_type=' . WCCG_POST_TYPE ),
		);

		if ( class_exists( '\Automattic\WooCommerce\Internal\Admin\Navigation\WC_Admin_Nav' ) ) {
			\Automattic\WooCommerce\Internal\Admin\Navigation\WC_Admin_Nav::move( $tree, 'wccg-customer-groups', 'woocommerce' );
		}

		return $tree;
	}

	/**
	 * Redirect the registered admin page to the customer group list table.
	 *
	 * @return void
	 */
	public function redirect_to_list_screen(): void {
		wp_safe_redirect( admin_url( 'edit.php?post_type=' . WCCG_POST_TYPE ) );
		exit;
	}

	/**
	 * Keep the correct admin menu highlighted on customer group screens.
	 *
	 * @return void
	 */
	public function highlight_menu(): void {
		global $parent_file, $submenu_file, $post_type;

		if ( WCCG_POST_TYPE !== $post_type ) {
			return;
		}

		$list_slug = 'edit.php?post_type=' . WCCG_POST_TYPE;

		if ( $this->is_woocommerce_menu_available() ) {
			$parent_file  = 'woocommerce';
			$submenu_file = $list_slug;
			return;
		}

		$parent_file  = self::TOP_LEVEL_SLUG;
		$submenu_file = self::TOP_LEVEL_SLUG;
	}

	/**
	 * Whether the current user can see the WooCommerce admin menu.
	 *
	 * @return bool
	 */
	private function is_woocommerce_menu_available(): bool {
		if ( class_exists( '\WC_Admin_Menus' ) && method_exists( '\WC_Admin_Menus', 'can_view_woocommerce_menu_item' ) ) {
			return \WC_Admin_Menus::can_view_woocommerce_menu_item();
		}

		return current_user_can( 'edit_others_shop_orders' );
	}
}
