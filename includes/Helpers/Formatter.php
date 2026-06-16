<?php
/**
 * Display formatting helpers.
 *
 * @package WooCommerce\CustomerGroups
 */

namespace WooCommerce\CustomerGroups\Helpers;

use WooCommerce\CustomerGroups\Models\CustomerGroup;

defined( 'ABSPATH' ) || exit;

/**
 * Class Formatter
 */
final class Formatter {

	/**
	 * Format a discount for display.
	 *
	 * @param CustomerGroup $group Customer group.
	 * @return string
	 */
	public static function format_discount( CustomerGroup $group ): string {
		if ( ! $group->has_discount() ) {
			return __( 'No discount', WCCG_TEXT_DOMAIN );
		}

		if ( CustomerGroup::DISCOUNT_TYPE_FIXED === $group->get_discount_type() ) {
			return wp_kses_post( wc_price( $group->get_discount_value() ) );
		}

		return sprintf(
			/* translators: %s: discount percentage */
			__( '%s%%', WCCG_TEXT_DOMAIN ),
			wc_format_decimal( $group->get_discount_value(), 2 )
		);
	}

	/**
	 * Get human-readable discount type label.
	 *
	 * @param string $discount_type Discount type slug.
	 * @return string
	 */
	public static function discount_type_label( string $discount_type ): string {
		if ( CustomerGroup::DISCOUNT_TYPE_FIXED === $discount_type ) {
			return __( 'Fixed amount', WCCG_TEXT_DOMAIN );
		}

		return __( 'Percentage', WCCG_TEXT_DOMAIN );
	}
}
