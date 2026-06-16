<?php
/**
 * Enforces product visibility rules across the WooCommerce storefront.
 *
 * @package WooCommerce\CustomerGroups
 */

namespace WooCommerce\CustomerGroups\Frontend\Visibility;

use WooCommerce\CustomerGroups\Services\ProductVisibilityChecker;

defined( 'ABSPATH' ) || exit;

/**
 * Class ProductVisibilityGuard
 */
final class ProductVisibilityGuard {

	/**
	 * Visibility checker instance.
	 *
	 * @var ProductVisibilityChecker
	 */
	private ProductVisibilityChecker $visibility_checker;

	/**
	 * Whether hidden product exclusions are currently being applied.
	 *
	 * @var bool
	 */
	private static bool $applying_exclusions = false;

	/**
	 * Constructor.
	 *
	 * @param ProductVisibilityChecker $visibility_checker Visibility checker.
	 */
	public function __construct( ProductVisibilityChecker $visibility_checker ) {
		$this->visibility_checker = $visibility_checker;
	}

	/**
	 * Register WooCommerce visibility hooks.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_action( 'woocommerce_product_query', array( $this, 'filter_product_query' ), 20, 2 );
		add_action( 'pre_get_posts', array( $this, 'filter_pre_get_posts' ), 20, 1 );
		add_filter( 'woocommerce_variation_is_visible', array( $this, 'filter_variation_is_visible' ), 20, 4 );
		add_filter( 'woocommerce_product_is_visible', array( $this, 'filter_product_is_visible' ), 20, 2 );
		add_action( 'template_redirect', array( $this, 'block_restricted_product_page' ), 20 );
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'validate_add_to_cart' ), 20, 3 );
	}

	/**
	 * Exclude hidden products from WooCommerce product loops.
	 *
	 * @param \WP_Query   $query    Query instance.
	 * @param \WC_Query|null $wc_query WooCommerce query instance.
	 * @return void
	 */
	public function filter_product_query( \WP_Query $query, $wc_query = null ): void {
		unset( $wc_query );

		if ( ! $this->should_filter() ) {
			return;
		}

		$this->exclude_hidden_products( $query );
	}

	/**
	 * Exclude hidden products from broader product queries such as search.
	 *
	 * @param \WP_Query $query Query instance.
	 * @return void
	 */
	public function filter_pre_get_posts( \WP_Query $query ): void {
		if ( ! $this->should_filter() || ! $this->is_product_listing_query( $query ) ) {
			return;
		}

		$this->exclude_hidden_products( $query );
	}

	/**
	 * Hide restricted variations on the frontend.
	 *
	 * @param bool             $visible    Whether the variation is visible.
	 * @param int              $variation_id Variation ID.
	 * @param int              $parent_id  Parent product ID.
	 * @param \WC_Product|null $variation  Variation object.
	 * @return bool
	 */
	public function filter_variation_is_visible( bool $visible, int $variation_id, int $parent_id, $variation ): bool {
		unset( $variation_id, $variation );

		if ( ! $visible || ! $this->should_filter() ) {
			return $visible;
		}

		return $this->visibility_checker->user_can_view_product( $parent_id );
	}

	/**
	 * Hide restricted products from widgets, related products, and other lookups.
	 *
	 * @param bool       $visible    Whether the product is visible.
	 * @param int|string $product_id Product ID.
	 * @return bool
	 */
	public function filter_product_is_visible( bool $visible, $product_id ): bool {
		if ( ! $visible || ! $this->should_filter() ) {
			return $visible;
		}

		return $this->visibility_checker->user_can_view_product( (int) $product_id );
	}

	/**
	 * Block direct access to restricted single product pages.
	 *
	 * @return void
	 */
	public function block_restricted_product_page(): void {
		if ( ! $this->should_filter() || ! is_singular( 'product' ) ) {
			return;
		}

		$product_id = (int) get_queried_object_id();

		if ( $product_id <= 0 || $this->visibility_checker->user_can_view_product( $product_id ) ) {
			return;
		}

		wc_add_notice(
			__( 'Sorry, this product is not available to your customer group.', WCCG_TEXT_DOMAIN ),
			'error'
		);

		$redirect = wc_get_page_permalink( 'shop' );

		if ( ! $redirect ) {
			$redirect = home_url( '/' );
		}

		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Block add-to-cart attempts for restricted products.
	 *
	 * @param bool $passed     Whether validation passed.
	 * @param int  $product_id Product ID.
	 * @param int  $quantity   Quantity.
	 * @return bool
	 */
	public function validate_add_to_cart( bool $passed, int $product_id, int $quantity ): bool {
		unset( $quantity );

		if ( ! $passed || ! $this->should_filter() ) {
			return $passed;
		}

		$product = wc_get_product( $product_id );

		if ( ! $product instanceof \WC_Product ) {
			return $passed;
		}

		$check_id = $product->is_type( 'variation' ) ? (int) $product->get_parent_id() : $product_id;

		if ( $this->visibility_checker->user_can_view_product( $check_id ) ) {
			return $passed;
		}

		wc_add_notice(
			__( 'Sorry, this product is not available to your customer group.', WCCG_TEXT_DOMAIN ),
			'error'
		);

		return false;
	}

	/**
	 * Whether visibility filtering should run in the current context.
	 *
	 * @return bool
	 */
	private function should_filter(): bool {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return false;
		}

		return true;
	}

	/**
	 * Whether the query is listing products on the storefront.
	 *
	 * @param \WP_Query $query Query instance.
	 * @return bool
	 */
	private function is_product_listing_query( \WP_Query $query ): bool {
		if ( is_admin() ) {
			return false;
		}

		$post_type = $query->get( 'post_type' );

		if ( 'product' === $post_type ) {
			return true;
		}

		if ( is_array( $post_type ) && in_array( 'product', $post_type, true ) ) {
			return true;
		}

		if ( $query->is_search() && ( '' === $post_type || 'any' === $post_type || ( is_array( $post_type ) && in_array( 'product', $post_type, true ) ) ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Append hidden product IDs to a query exclusion list.
	 *
	 * @param \WP_Query $query Query instance.
	 * @return void
	 */
	private function exclude_hidden_products( \WP_Query $query ): void {
		if ( self::$applying_exclusions ) {
			return;
		}

		self::$applying_exclusions = true;

		try {
			$hidden_ids = $this->visibility_checker->get_hidden_product_ids();
		} finally {
			self::$applying_exclusions = false;
		}

		if ( empty( $hidden_ids ) ) {
			return;
		}

		$not_in = $query->get( 'post__not_in' );

		if ( ! is_array( $not_in ) ) {
			$not_in = array();
		}

		$query->set( 'post__not_in', array_values( array_unique( array_merge( $not_in, $hidden_ids ) ) ) );
	}
}
