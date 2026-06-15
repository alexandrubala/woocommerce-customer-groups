<?php
/**
 * Input sanitization helpers.
 *
 * @package WooCommerce\CustomerGroups
 */

namespace WooCommerce\CustomerGroups\Helpers;

use WooCommerce\CustomerGroups\Models\CustomerGroup;

defined( 'ABSPATH' ) || exit;

/**
 * Class Sanitizer
 */
final class Sanitizer {

	/**
	 * Sanitize a discount type value.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function discount_type( $value ): string {
		$value = sanitize_key( (string) $value );

		if ( CustomerGroup::DISCOUNT_TYPE_FIXED === $value ) {
			return CustomerGroup::DISCOUNT_TYPE_FIXED;
		}

		return CustomerGroup::DISCOUNT_TYPE_PERCENTAGE;
	}

	/**
	 * Sanitize a discount value based on type.
	 *
	 * @param mixed  $value Raw value.
	 * @param string $type  Discount type.
	 * @return float
	 */
	public static function discount_value( $value, string $type ): float {
		$value = (float) wc_format_decimal( wp_unslash( (string) $value ) );

		if ( CustomerGroup::DISCOUNT_TYPE_PERCENTAGE === $type ) {
			return max( 0.0, min( 100.0, $value ) );
		}

		return max( 0.0, $value );
	}

	/**
	 * Sanitize a group ID.
	 *
	 * @param mixed $value Raw value.
	 * @return int
	 */
	public static function group_id( $value ): int {
		return max( 0, absint( $value ) );
	}

	/**
	 * Sanitize a description field.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function description( $value ): string {
		return sanitize_textarea_field( wp_unslash( (string) $value ) );
	}
}
