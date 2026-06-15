<?php
/**
 * Pure discount calculation logic.
 *
 * @package WooCommerce\CustomerGroups
 */

namespace WooCommerce\CustomerGroups\Services;

use WooCommerce\CustomerGroups\Contracts\DiscountStrategyInterface;
use WooCommerce\CustomerGroups\Models\CustomerGroup;

defined( 'ABSPATH' ) || exit;

/**
 * Class DiscountCalculator
 */
final class DiscountCalculator implements DiscountStrategyInterface {

	/**
	 * Calculate the discounted price for a product.
	 *
	 * @param float              $base_price Base product price (ex-tax, as WooCommerce provides).
	 * @param CustomerGroup      $group      Customer group.
	 * @param \WC_Product|null   $product    Product being priced (optional, for filters).
	 * @return float
	 */
	public function calculate( float $base_price, CustomerGroup $group, ?\WC_Product $product = null ): float {
		if ( $base_price <= 0 || ! $group->has_discount() ) {
			return $base_price;
		}

		$discounted = $base_price;

		if ( CustomerGroup::DISCOUNT_TYPE_PERCENTAGE === $group->get_discount_type() ) {
			$amount     = $base_price * ( $group->get_discount_value() / 100 );
			$discounted = $base_price - $amount;
		} elseif ( CustomerGroup::DISCOUNT_TYPE_FIXED === $group->get_discount_type() ) {
			$discounted = $base_price - $group->get_discount_value();
		}

		$discounted = max( 0.0, $discounted );

		/**
		 * Filter the calculated discounted price.
		 *
		 * @param float              $discounted   Calculated discounted price.
		 * @param float              $base_price   Original base price.
		 * @param CustomerGroup      $group        Customer group.
		 * @param \WC_Product|null   $product      Product being priced.
		 */
		$discounted = apply_filters( 'wccg_group_discount', $discounted, $base_price, $group, $product );

		return (float) wc_format_decimal( $discounted );
	}
}
