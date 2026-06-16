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
		$value = sanitize_key( (string) wp_unslash( $value ) );

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
		$value = (float) wc_format_decimal( (string) wp_unslash( $value ) );

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
		return max( 0, absint( wp_unslash( $value ) ) );
	}

	/**
	 * Sanitize a description field.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function description( $value ): string {
		return sanitize_textarea_field( (string) wp_unslash( $value ) );
	}

	/**
	 * Sanitize a product visibility mode value.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function visibility_mode( $value ): string {
		$value = sanitize_key( (string) wp_unslash( $value ) );

		if ( WCCG_VISIBILITY_MODE_RESTRICTED === $value ) {
			return WCCG_VISIBILITY_MODE_RESTRICTED;
		}

		return WCCG_VISIBILITY_MODE_EVERYONE;
	}

	/**
	 * Sanitize an array of allowed group IDs.
	 *
	 * @param mixed $value Raw value.
	 * @return int[]
	 */
	public static function allowed_group_ids( $value ): array {
		if ( ! is_array( $value ) ) {
			return array();
		}

		$value = wp_unslash( $value );
		$ids   = array_map( 'absint', $value );

		return array_values( array_unique( array_filter( $ids ) ) );
	}

	/**
	 * Sanitize an array of WooCommerce shipping method rate IDs.
	 *
	 * @param mixed $value Raw value.
	 * @return string[]
	 */
	public static function allowed_shipping_methods( $value ): array {
		if ( ! is_array( $value ) ) {
			return array();
		}

		$value = wp_unslash( $value );
		$methods = array();

		foreach ( $value as $method_id ) {
			$method_id = sanitize_text_field( (string) $method_id );

			if ( preg_match( '/^[a-z0-9_]+(?::\d+)?$/', $method_id ) ) {
				$methods[] = $method_id;
			}
		}

		return array_values( array_unique( $methods ) );
	}

	/**
	 * Sanitize an array of WooCommerce payment gateway IDs.
	 *
	 * @param mixed $value Raw value.
	 * @return string[]
	 */
	public static function allowed_payment_gateways( $value ): array {
		if ( ! is_array( $value ) ) {
			return array();
		}

		$value = wp_unslash( $value );
		$gateways = array();

		foreach ( $value as $gateway_id ) {
			$gateway_id = sanitize_key( (string) $gateway_id );

			if ( '' !== $gateway_id ) {
				$gateways[] = $gateway_id;
			}
		}

		return array_values( array_unique( $gateways ) );
	}
}
