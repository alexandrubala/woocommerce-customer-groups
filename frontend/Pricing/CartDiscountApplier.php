<?php
/**
 * Applies group discounts to WooCommerce product and cart prices.
 *
 * @package WooCommerce\CustomerGroups
 */

namespace WooCommerce\CustomerGroups\Frontend\Pricing;

use WooCommerce\CustomerGroups\Models\CustomerGroup;
use WooCommerce\CustomerGroups\Services\DiscountCalculator;
use WooCommerce\CustomerGroups\Services\GroupResolver;

defined( 'ABSPATH' ) || exit;

/**
 * Class CartDiscountApplier
 */
final class CartDiscountApplier {

	/**
	 * Guard against infinite loops in cart total recalculation.
	 *
	 * @var bool
	 */
	private static bool $running = false;

	/**
	 * Group resolver instance.
	 *
	 * @var GroupResolver
	 */
	private GroupResolver $group_resolver;

	/**
	 * Discount calculator instance.
	 *
	 * @var DiscountCalculator
	 */
	private DiscountCalculator $discount_calculator;

	/**
	 * Constructor.
	 *
	 * @param GroupResolver      $group_resolver      Group resolver.
	 * @param DiscountCalculator $discount_calculator Discount calculator.
	 */
	public function __construct( GroupResolver $group_resolver, DiscountCalculator $discount_calculator ) {
		$this->group_resolver        = $group_resolver;
		$this->discount_calculator   = $discount_calculator;
	}

	/**
	 * Register WooCommerce price hooks.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_filter( 'woocommerce_product_get_price', array( $this, 'filter_price' ), 20, 2 );
		add_filter( 'woocommerce_product_get_regular_price', array( $this, 'filter_regular_price' ), 20, 2 );
		add_filter( 'woocommerce_product_variation_get_price', array( $this, 'filter_price' ), 20, 2 );
		add_filter( 'woocommerce_product_variation_get_regular_price', array( $this, 'filter_regular_price' ), 20, 2 );
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'apply_cart_discounts' ), 20, 1 );
	}

	/**
	 * Apply group discount to the active product price.
	 *
	 * @param string|float     $price   Product price.
	 * @param \WC_Product|null $product Product object.
	 * @return string|float
	 */
	public function filter_price( $price, $product ) {
		if ( ! $this->should_apply() || ! $product instanceof \WC_Product ) {
			return $price;
		}

		if ( '' === $price || null === $price ) {
			return $price;
		}

		$group = $this->get_active_group();

		if ( null === $group ) {
			return $price;
		}

		$regular_price = $product->get_regular_price( 'edit' );

		if ( '' === $regular_price || null === $regular_price ) {
			return $price;
		}

		$discounted = $this->get_discounted_price( (float) $regular_price, $group, $product );

		return $discounted;
	}

	/**
	 * Keep the regular price intact for display comparisons.
	 *
	 * @param string|float     $price   Regular price.
	 * @param \WC_Product|null $product Product object.
	 * @return string|float
	 */
	public function filter_regular_price( $price, $product ) {
		return $price;
	}

	/**
	 * Re-apply discounted prices on cart items during total calculation.
	 *
	 * @param \WC_Cart $cart Cart object.
	 * @return void
	 */
	public function apply_cart_discounts( \WC_Cart $cart ): void {
		if ( self::$running ) {
			return;
		}

		if ( ! $this->should_apply() ) {
			return;
		}

		$group = $this->get_active_group();

		if ( null === $group ) {
			return;
		}

		self::$running = true;

		foreach ( $cart->get_cart() as $cart_item ) {
			if ( empty( $cart_item['data'] ) || ! $cart_item['data'] instanceof \WC_Product ) {
				continue;
			}

			$product = $cart_item['data'];

			$regular_price = $product->get_regular_price( 'edit' );

			if ( '' === $regular_price || null === $regular_price ) {
				continue;
			}

			$discounted = $this->get_discounted_price( (float) $regular_price, $group, $product );
			$product->set_price( $discounted );
		}

		self::$running = false;
	}

	/**
	 * Whether group discounts should be applied in the current context.
	 *
	 * @return bool
	 */
	private function should_apply(): bool {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return false;
		}

		$group = $this->get_active_group();

		return null !== $group && $group->has_discount();
	}

	/**
	 * Get the active customer group for the current user.
	 *
	 * @return CustomerGroup|null
	 */
	private function get_active_group(): ?CustomerGroup {
		return $this->group_resolver->resolve_for_current_user();
	}

	/**
	 * Calculate the discounted price, respecting sale price stacking.
	 *
	 * Group discount is calculated from the regular price. If the product is on
	 * sale at a lower price, the sale price is used instead.
	 *
	 * @param float       $regular_price Regular product price (ex-tax).
	 * @param CustomerGroup $group       Customer group.
	 * @param \WC_Product $product       Product object.
	 * @return float
	 */
	private function get_discounted_price( float $regular_price, CustomerGroup $group, \WC_Product $product ): float {
		$discounted = $this->discount_calculator->calculate( $regular_price, $group, $product );

		$sale_price = $product->get_sale_price( 'edit' );

		if ( '' !== $sale_price && null !== $sale_price ) {
			$discounted = min( $discounted, (float) $sale_price );
		}

		return $discounted;
	}
}
