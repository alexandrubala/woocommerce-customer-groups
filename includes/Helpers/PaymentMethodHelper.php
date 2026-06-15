<?php
/**
 * WooCommerce payment gateway discovery helpers.
 *
 * @package WooCommerce\CustomerGroups
 */

namespace WooCommerce\CustomerGroups\Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * Class PaymentMethodHelper
 */
final class PaymentMethodHelper {

	/**
	 * Get enabled payment gateways keyed by gateway ID.
	 *
	 * @return array<string, string> Gateway ID => title.
	 */
	public static function get_active_gateways(): array {
		if ( ! function_exists( 'WC' ) || ! WC()->payment_gateways() ) {
			return array();
		}

		$gateways = array();

		foreach ( WC()->payment_gateways()->payment_gateways() as $gateway_id => $gateway ) {
			if ( ! $gateway instanceof \WC_Payment_Gateway || 'yes' !== $gateway->enabled ) {
				continue;
			}

			$gateways[ (string) $gateway_id ] = $gateway->get_title();
		}

		if ( ! empty( $gateways ) ) {
			asort( $gateways );
		}

		return $gateways;
	}

	/**
	 * Get all enabled payment gateway IDs.
	 *
	 * @return string[]
	 */
	public static function get_available_gateway_ids(): array {
		return array_keys( self::get_active_gateways() );
	}
}
