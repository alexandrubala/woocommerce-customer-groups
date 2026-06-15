<?php
/**
 * Determines whether a user may view a product based on group visibility rules.
 *
 * @package WooCommerce\CustomerGroups
 */

namespace WooCommerce\CustomerGroups\Services;

defined( 'ABSPATH' ) || exit;

/**
 * Class ProductVisibilityChecker
 */
final class ProductVisibilityChecker {

	/**
	 * Group resolver instance.
	 *
	 * @var GroupResolver
	 */
	private GroupResolver $group_resolver;

	/**
	 * Per-user hidden product ID cache for the current request.
	 *
	 * @var array<int, int[]>
	 */
	private array $hidden_ids_cache = array();

	/**
	 * Constructor.
	 *
	 * @param GroupResolver $group_resolver Group resolver.
	 */
	public function __construct( GroupResolver $group_resolver ) {
		$this->group_resolver = $group_resolver;
	}

	/**
	 * Whether the given user may view a product on the storefront.
	 *
	 * @param int      $product_id Product or variation ID.
	 * @param int|null $user_id    User ID. Defaults to the current user.
	 * @return bool
	 */
	public function user_can_view_product( int $product_id, ?int $user_id = null ): bool {
		$product_id = $this->resolve_product_id( $product_id );

		if ( $product_id <= 0 ) {
			return true;
		}

		$mode = (string) get_post_meta( $product_id, WCCG_META_VISIBILITY_MODE, true );

		if ( WCCG_VISIBILITY_MODE_RESTRICTED !== $mode ) {
			/**
			 * Filter whether a product is visible to a user.
			 *
			 * @param bool $visible    Whether the product is visible.
			 * @param int  $product_id Product ID.
			 * @param int  $user_id    User ID.
			 */
			return (bool) apply_filters( 'wccg_product_visible', true, $product_id, $user_id ?? get_current_user_id() );
		}

		$user_id        = null !== $user_id ? $user_id : get_current_user_id();
		$allowed_groups = $this->get_allowed_group_ids( $product_id );
		$group          = $this->group_resolver->resolve_for_user( $user_id );
		$visible        = null !== $group && in_array( $group->get_id(), $allowed_groups, true );

		/**
		 * Filter whether a product is visible to a user.
		 *
		 * @param bool $visible    Whether the product is visible.
		 * @param int  $product_id Product ID.
		 * @param int  $user_id    User ID.
		 */
		return (bool) apply_filters( 'wccg_product_visible', $visible, $product_id, $user_id );
	}

	/**
	 * Get product IDs that should be hidden from the given user.
	 *
	 * @param int|null $user_id User ID. Defaults to the current user.
	 * @return int[]
	 */
	public function get_hidden_product_ids( ?int $user_id = null ): array {
		$user_id = null !== $user_id ? $user_id : get_current_user_id();

		if ( isset( $this->hidden_ids_cache[ $user_id ] ) ) {
			return $this->hidden_ids_cache[ $user_id ];
		}

		$hidden = array();
		$group  = $this->group_resolver->resolve_for_user( $user_id );
		$group_id = null !== $group ? $group->get_id() : 0;

		foreach ( $this->get_restricted_products_map() as $product_id => $allowed_groups ) {
			if ( $group_id <= 0 || ! in_array( $group_id, $allowed_groups, true ) ) {
				$hidden[] = (int) $product_id;
			}
		}

		$this->hidden_ids_cache[ $user_id ] = $hidden;

		return $hidden;
	}

	/**
	 * Clear cached restricted-product visibility data.
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		wp_cache_delete( 'wccg_restricted_products_map', 'woocommerce-customer-groups' );
	}

	/**
	 * Resolve a variation ID to its parent product ID.
	 *
	 * @param int $product_id Product or variation ID.
	 * @return int
	 */
	private function resolve_product_id( int $product_id ): int {
		if ( 'product_variation' === get_post_type( $product_id ) ) {
			$parent_id = (int) wp_get_post_parent_id( $product_id );

			return $parent_id > 0 ? $parent_id : $product_id;
		}

		return $product_id;
	}

	/**
	 * Get allowed group IDs for a restricted product.
	 *
	 * @param int $product_id Product ID.
	 * @return int[]
	 */
	private function get_allowed_group_ids( int $product_id ): array {
		$allowed = get_post_meta( $product_id, WCCG_META_ALLOWED_GROUP_IDS, true );

		if ( ! is_array( $allowed ) ) {
			return array();
		}

		return array_map( 'absint', $allowed );
	}

	/**
	 * Get all products restricted to specific groups.
	 *
	 * @return array<int, int[]> Map of product ID to allowed group IDs.
	 */
	private function get_restricted_products_map(): array {
		$cache_key = 'wccg_restricted_products_map';
		$cached    = wp_cache_get( $cache_key, 'woocommerce-customer-groups' );

		if ( is_array( $cached ) ) {
			return $cached;
		}

		$product_ids = get_posts(
			array(
				'post_type'              => 'product',
				'post_status'            => 'publish',
				'posts_per_page'         => -1,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'suppress_filters'       => true,
				'update_post_meta_cache' => true,
				'update_post_term_cache' => false,
				'meta_query'             => array(
					array(
						'key'   => WCCG_META_VISIBILITY_MODE,
						'value' => WCCG_VISIBILITY_MODE_RESTRICTED,
					),
				),
			)
		);

		$map = array();

		foreach ( $product_ids as $product_id ) {
			$product_id = (int) $product_id;
			$allowed    = get_post_meta( $product_id, WCCG_META_ALLOWED_GROUP_IDS, true );
			$map[ $product_id ] = is_array( $allowed ) ? array_map( 'absint', $allowed ) : array();
		}

		wp_cache_set( $cache_key, $map, 'woocommerce-customer-groups' );

		return $map;
	}
}
