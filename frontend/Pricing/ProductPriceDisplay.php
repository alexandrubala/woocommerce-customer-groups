<?php
/**
 * Renders discounted price HTML on the frontend.
 *
 * @package WooCommerce\CustomerGroups
 */

namespace WooCommerce\CustomerGroups\Frontend\Pricing;

use WooCommerce\CustomerGroups\Models\CustomerGroup;
use WooCommerce\CustomerGroups\Services\GroupResolver;

defined( 'ABSPATH' ) || exit;

/**
 * Class ProductPriceDisplay
 */
final class ProductPriceDisplay {

	/**
	 * Group resolver instance.
	 *
	 * @var GroupResolver
	 */
	private GroupResolver $group_resolver;

	/**
	 * Constructor.
	 *
	 * @param GroupResolver $group_resolver Group resolver.
	 */
	public function __construct( GroupResolver $group_resolver ) {
		$this->group_resolver = $group_resolver;
	}

	/**
	 * Register WooCommerce display hooks.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_filter( 'woocommerce_get_price_html', array( $this, 'filter_price_html' ), 20, 2 );
		add_filter( 'woocommerce_cart_item_price', array( $this, 'filter_cart_item_price' ), 20, 3 );
	}

	/**
	 * Replace price HTML with regular and discounted prices.
	 *
	 * @param string       $price_html Default price HTML.
	 * @param \WC_Product  $product    Product object.
	 * @return string
	 */
	public function filter_price_html( string $price_html, $product ): string {
		if ( ! $product instanceof \WC_Product || ! $this->should_show_discount() ) {
			return $price_html;
		}

		$group = $this->group_resolver->resolve_for_current_user();

		if ( null === $group ) {
			return $price_html;
		}

		if ( $product->is_type( 'variable' ) ) {
			return $this->filter_variable_price_html( $price_html, $product, $group );
		}

		return $this->build_price_html( $product, $group ) ?? $price_html;
	}

	/**
	 * Show consistent discounted price markup in the cart.
	 *
	 * @param string $price_html Default cart item price HTML.
	 * @param array  $cart_item  Cart item data.
	 * @param string $cart_item_key Cart item key.
	 * @return string
	 */
	public function filter_cart_item_price( string $price_html, array $cart_item, string $cart_item_key ): string {
		if ( empty( $cart_item['data'] ) || ! $cart_item['data'] instanceof \WC_Product || ! $this->should_show_discount() ) {
			return $price_html;
		}

		$group = $this->group_resolver->resolve_for_current_user();

		if ( null === $group ) {
			return $price_html;
		}

		return $this->build_price_html( $cart_item['data'], $group ) ?? $price_html;
	}

	/**
	 * Build discounted price markup for a simple or variation product.
	 *
	 * @param \WC_Product   $product Product object.
	 * @param CustomerGroup $group   Customer group.
	 * @return string|null
	 */
	private function build_price_html( \WC_Product $product, CustomerGroup $group ): ?string {
		$regular_price = $product->get_regular_price();

		if ( '' === $regular_price || null === $regular_price ) {
			return null;
		}

		$regular_price    = (float) $regular_price;
		$discounted_price = (float) $product->get_price();

		if ( $regular_price <= 0 || $discounted_price >= $regular_price ) {
			return null;
		}

		$html = sprintf(
			'<span class="wccg-price"><del><span class="wccg-price-regular">%1$s %2$s</span></del> <ins><span class="wccg-price-discounted">%3$s %4$s</span></ins></span>',
			esc_html__( 'Regular:', 'woocommerce-customer-groups' ),
			wp_kses_post( wc_price( $regular_price ) ),
			esc_html__( 'Your price:', 'woocommerce-customer-groups' ),
			wp_kses_post( wc_price( $discounted_price ) )
		);

		/**
		 * Filter the frontend price HTML markup.
		 *
		 * @param string        $html             Price HTML.
		 * @param \WC_Product   $product          Product object.
		 * @param CustomerGroup $group            Customer group.
		 * @param float         $regular_price    Regular price.
		 * @param float         $discounted_price Discounted price.
		 */
		return (string) apply_filters( 'wccg_price_html', $html, $product, $group, $regular_price, $discounted_price );
	}

	/**
	 * Adjust variable product price HTML when all visible variations share a discount.
	 *
	 * @param string        $price_html Default price HTML.
	 * @param \WC_Product   $product    Variable product.
	 * @param CustomerGroup $group      Customer group.
	 * @return string
	 */
	private function filter_variable_price_html( string $price_html, \WC_Product $product, CustomerGroup $group ): string {
		$prices = $product->get_variation_prices( true );

		if ( empty( $prices['regular_price'] ) || empty( $prices['price'] ) ) {
			return $price_html;
		}

		$min_regular    = (float) min( $prices['regular_price'] );
		$max_regular    = (float) max( $prices['regular_price'] );
		$min_discounted = (float) min( $prices['price'] );
		$max_discounted = (float) max( $prices['price'] );

		if ( $min_regular <= 0 || $min_discounted >= $min_regular ) {
			return $price_html;
		}

		if ( $min_regular === $max_regular && $min_discounted === $max_discounted ) {
			$html = sprintf(
				'<span class="wccg-price"><del><span class="wccg-price-regular">%1$s %2$s</span></del> <ins><span class="wccg-price-discounted">%3$s %4$s</span></ins></span>',
				esc_html__( 'Regular:', 'woocommerce-customer-groups' ),
				wp_kses_post( wc_price( $min_regular ) ),
				esc_html__( 'Your price:', 'woocommerce-customer-groups' ),
				wp_kses_post( wc_price( $min_discounted ) )
			);

			/**
			 * Filter the frontend price HTML markup.
			 *
			 * @param string        $html             Price HTML.
			 * @param \WC_Product   $product          Product object.
			 * @param CustomerGroup $group            Customer group.
			 * @param float         $regular_price    Minimum regular price.
			 * @param float         $discounted_price Minimum discounted price.
			 */
			return (string) apply_filters( 'wccg_price_html', $html, $product, $group, $min_regular, $min_discounted );
		}

		$regular_range = wc_format_price_range( $min_regular, $max_regular );
		$price_range   = wc_format_price_range( $min_discounted, $max_discounted );

		$html = sprintf(
			'<span class="wccg-price"><del><span class="wccg-price-regular">%1$s %2$s</span></del> <ins><span class="wccg-price-discounted">%3$s %4$s</span></ins></span>',
			esc_html__( 'Regular:', 'woocommerce-customer-groups' ),
			wp_kses_post( $regular_range ),
			esc_html__( 'Your price:', 'woocommerce-customer-groups' ),
			wp_kses_post( $price_range )
		);

		/**
		 * Filter the frontend price HTML markup.
		 *
		 * @param string        $html             Price HTML.
		 * @param \WC_Product   $product          Product object.
		 * @param CustomerGroup $group            Customer group.
		 * @param float         $regular_price    Minimum regular price.
		 * @param float         $discounted_price Minimum discounted price.
		 */
		return (string) apply_filters( 'wccg_price_html', $html, $product, $group, $min_regular, $min_discounted );
	}

	/**
	 * Whether discounted price markup should be shown.
	 *
	 * @return bool
	 */
	private function should_show_discount(): bool {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return false;
		}

		$group = $this->group_resolver->resolve_for_current_user();

		return null !== $group && $group->has_discount();
	}
}
