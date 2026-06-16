<?php
/**
 * Customer group custom post type registration.
 *
 * @package WooCommerce\CustomerGroups
 */

namespace WooCommerce\CustomerGroups\PostTypes;

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
		add_filter( 'enter_title_here', array( $this, 'filter_title_placeholder' ), 10, 2 );
	}

	/**
	 * Register the custom post type.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( post_type_exists( WCCG_POST_TYPE ) ) {
			return;
		}

		$labels = array(
			'name'                  => __( 'Customer Groups', WCCG_TEXT_DOMAIN ),
			'singular_name'         => __( 'Customer Group', WCCG_TEXT_DOMAIN ),
			'menu_name'             => __( 'Customer Groups', WCCG_TEXT_DOMAIN ),
			'name_admin_bar'        => __( 'Customer Group', WCCG_TEXT_DOMAIN ),
			'add_new'               => __( 'Add New', WCCG_TEXT_DOMAIN ),
			'add_new_item'          => __( 'Add New Customer Group', WCCG_TEXT_DOMAIN ),
			'edit_item'             => __( 'Edit Customer Group', WCCG_TEXT_DOMAIN ),
			'new_item'              => __( 'New Customer Group', WCCG_TEXT_DOMAIN ),
			'view_item'             => __( 'View Customer Group', WCCG_TEXT_DOMAIN ),
			'search_items'          => __( 'Search Customer Groups', WCCG_TEXT_DOMAIN ),
			'not_found'             => __( 'No customer groups found.', WCCG_TEXT_DOMAIN ),
			'not_found_in_trash'    => __( 'No customer groups found in Trash.', WCCG_TEXT_DOMAIN ),
			'all_items'             => __( 'All Customer Groups', WCCG_TEXT_DOMAIN ),
			'item_published'        => __( 'Customer group published.', WCCG_TEXT_DOMAIN ),
			'item_updated'          => __( 'Customer group updated.', WCCG_TEXT_DOMAIN ),
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
				'capability_type'     => array( 'wccg_group', 'wccg_groups' ),
				'map_meta_cap'        => true,
				'rewrite'             => false,
				'query_var'           => false,
			)
		);
	}

	/**
	 * Customize the title field placeholder.
	 *
	 * @param string   $title Placeholder text.
	 * @param \WP_Post $post  Current post object.
	 * @return string
	 */
	public function filter_title_placeholder( string $title, \WP_Post $post ): string {
		if ( WCCG_POST_TYPE !== $post->post_type ) {
			return $title;
		}

		return __( 'Enter group name (e.g. VIP, Reseller, Distributor)', WCCG_TEXT_DOMAIN );
	}
}
