<?php
/**
 * Discount strategy contract for future extensibility.
 *
 * @package WooCommerce\CustomerGroups
 */

namespace WooCommerce\CustomerGroups\Contracts;

use WooCommerce\CustomerGroups\Models\CustomerGroup;

defined( 'ABSPATH' ) || exit;

/**
 * Interface DiscountStrategyInterface
 */
interface DiscountStrategyInterface {

	/**
	 * Calculate the discounted price.
	 *
	 * @param float         $base_price Base product price.
	 * @param CustomerGroup $group      Customer group.
	 * @return float
	 */
	public function calculate( float $base_price, CustomerGroup $group ): float;
}
