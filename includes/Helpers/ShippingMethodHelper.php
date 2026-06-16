<?php
/**
 * WooCommerce shipping method discovery helpers.
 *
 * @package WooCommerce\CustomerGroups
 */

namespace WooCommerce\CustomerGroups\Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * Class ShippingMethodHelper
 */
final class ShippingMethodHelper {

	/**
	 * Get enabled shipping method instances grouped by zone.
	 *
	 * Each method is keyed by its WooCommerce rate ID (e.g. flat_rate:3).
	 *
	 * @return array<string, array<string, string>> Zone name => [ rate_id => label ].
	 */
	public static function get_methods_by_zone(): array {
		if ( ! class_exists( 'WC_Shipping_Zones' ) ) {
			return array();
		}

		$grouped = array();
		$zones   = \WC_Shipping_Zones::get_zones();

		$zones[] = array(
			'zone_id' => 0,
		);

		foreach ( $zones as $zone_data ) {
			$zone_id   = isset( $zone_data['zone_id'] ) ? (int) $zone_data['zone_id'] : 0;
			$zone      = \WC_Shipping_Zones::get_zone( $zone_id );
			$zone_name = $zone->get_zone_name();

			if ( '' === $zone_name ) {
				$zone_name = __( 'Locations not covered by your other zones', WCCG_TEXT_DOMAIN );
			}

			foreach ( $zone->get_shipping_methods( true ) as $instance ) {
				if ( ! $instance instanceof \WC_Shipping_Method || ! $instance->is_enabled() ) {
					continue;
				}

				$rate_id = $instance->get_rate_id();

				if ( '' === $rate_id ) {
					continue;
				}

				$grouped[ $zone_name ][ $rate_id ] = $instance->get_title();
			}
		}

		if ( ! empty( $grouped ) ) {
			ksort( $grouped );

			foreach ( $grouped as $zone_name => $methods ) {
				asort( $grouped[ $zone_name ] );
			}
		}

		return $grouped;
	}

	/**
	 * Get all enabled shipping method rate IDs configured in WooCommerce.
	 *
	 * @return string[]
	 */
	public static function get_available_rate_ids(): array {
		$rate_ids = array();

		foreach ( self::get_methods_by_zone() as $methods ) {
			$rate_ids = array_merge( $rate_ids, array_keys( $methods ) );
		}

		return array_values( array_unique( $rate_ids ) );
	}
}
