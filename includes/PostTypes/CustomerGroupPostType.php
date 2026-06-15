<?php
/**
 * Customer group custom post type registration.
 *
 * @package WooCommerce\CustomerGroups
 */

namespace WooCommerce\CustomerGroups\PostTypes;

use WooCommerce\CustomerGroups\Capabilities;

defined( 'ABSPATH' ) || exit;

/**
 * Class CustomerGroupPostType
 */
final class CustomerGroupPostType {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_action( 'init', array( $this, 'register' ) );
		add_filter( 'enter_title_here', array( $this, 'filter_title_placeholder' ), 10, 2 );
	}

	/**
	 * Register the custom post type.
	 *
	 * @return void
	 */
	public function register(): void {
		$labels = array(
			'name'                  => __( 'Customer Groups', 'woocommerce-customer-groups' ),
			'singular_name'         => __( 'Customer Group', 'woocommerce-customer-groups' ),
			'menu_name'             => __( 'Customer Groups', 'woocommerce-customer-groups' ),
			'name_admin_bar'        => __( 'Customer Group', 'woocommerce-customer-groups' ),
			'add_new'               => __( 'Add New', 'woocommerce-customer-groups' ),
			'add_new_item'          => __( 'Add New Customer Group', 'woocommerce-customer-groups' ),
			'edit_item'             => __( 'Edit Customer Group', 'woocommerce-customer-groups' ),
			'new_item'              => __( 'New Customer Group', 'woocommerce-customer-groups' ),
			'view_item'             => __( 'View Customer Group', 'woocommerce-customer-groups' ),
			'search_items'          => __( 'Search Customer Groups', 'woocommerce-customer-groups' ),
			'not_found'             => __( 'No customer groups found.', 'woocommerce-customer-groups' ),
			'not_found_in_trash'    => __( 'No customer groups found in Trash.', 'woocommerce-customer-groups' ),
			'all_items'             => __( 'All Customer Groups', 'woocommerce-customer-groups' ),
			'item_published'        => __( 'Customer group published.', 'woocommerce-customer-groups' ),
			'item_updated'          => __( 'Customer group updated.', 'woocommerce-customer-groups' ),
		);

		register_post_type(
			WCCG_POST_TYPE,
			array(
				'labels'              => $labels,
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'menu_position'       => 57,
				'menu_icon'           => 'dashicons-groups',
				'show_in_admin_bar'   => true,
				'show_in_nav_menus'   => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'has_archive'         => false,
				'hierarchical'        => false,
				'supports'            => array( 'title' ),
				'capability_type'     => 'post',
				'capabilities'        => array(
					'edit_post'          => Capabilities::MANAGE_GROUPS,
					'read_post'          => Capabilities::MANAGE_GROUPS,
					'delete_post'        => Capabilities::MANAGE_GROUPS,
					'edit_posts'         => Capabilities::MANAGE_GROUPS,
					'edit_others_posts'  => Capabilities::MANAGE_GROUPS,
					'publish_posts'      => Capabilities::MANAGE_GROUPS,
					'read_private_posts' => Capabilities::MANAGE_GROUPS,
					'create_posts'       => Capabilities::MANAGE_GROUPS,
				),
				'map_meta_cap'        => true,
				'rewrite'             => false,
				'query_var'           => false,
			)
		);
	}

	/**
	 * Customize the title field placeholder.
	 *
	 * @param string  $title Placeholder text.
	 * @param \WP_Post $post  Current post object.
	 * @return string
	 */
	public function filter_title_placeholder( string $title, \WP_Post $post ): string {
		if ( WCCG_POST_TYPE !== $post->post_type ) {
			return $title;
		}

		return __( 'Enter group name (e.g. VIP, Reseller, Distributor)', 'woocommerce-customer-groups' );
	}
}
