<?php
/**
 * Filters available payment gateways based on the customer's group.
 *
 * @package WooCommerce\CustomerGroups
 */

namespace WooCommerce\CustomerGroups\Frontend\Payment;

use WooCommerce\CustomerGroups\Models\CustomerGroup;
use WooCommerce\CustomerGroups\Services\GroupResolver;

defined( 'ABSPATH' ) || exit;

/**
 * Class PaymentMethodFilter
 */
final class PaymentMethodFilter {

	/**
	 * Group resolver instance.
	 *
	 * @var GroupResolver
	 */
	private GroupResolver $group_resolver;

	/**
	 * Cached allowed gateway ID lookups for the current request.
	 *
	 * @var array<int, array<string, true>|null>
	 */
	private array $allowed_lookup_cache = array();

	/**
	 * Constructor.
	 *
	 * @param GroupResolver $group_resolver Group resolver.
	 */
	public function __construct( GroupResolver $group_resolver ) {
		$this->group_resolver = $group_resolver;
	}

	/**
	 * Register WooCommerce payment hooks.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_filter( 'woocommerce_available_payment_gateways', array( $this, 'filter_available_payment_gateways' ), 20 );
	}

	/**
	 * Remove payment gateways that are not allowed for the current user's group.
	 *
	 * @param array<string, \WC_Payment_Gateway> $available_gateways Available payment gateways.
	 * @return array<string, \WC_Payment_Gateway>
	 */
	public function filter_available_payment_gateways( array $available_gateways ): array {
		if ( ! $this->should_filter() || empty( $available_gateways ) ) {
			return $available_gateways;
		}

		$allowed_lookup = $this->get_allowed_gateway_lookup();

		if ( null === $allowed_lookup ) {
			return $available_gateways;
		}

		foreach ( $available_gateways as $gateway_id => $gateway ) {
			unset( $gateway );

			if ( ! isset( $allowed_lookup[ $gateway_id ] ) ) {
				unset( $available_gateways[ $gateway_id ] );
			}
		}

		$group = $this->group_resolver->resolve_for_current_user();

		/**
		 * Filter available payment gateways after group payment restrictions are applied.
		 *
		 * @param array<string, \WC_Payment_Gateway> $available_gateways Available payment gateways.
		 * @param CustomerGroup|null                 $group                Resolved customer group.
		 */
		return apply_filters( 'wccg_available_payment_gateways', $available_gateways, $group );
	}

	/**
	 * Build a fast lookup map of allowed gateway IDs for the current user.
	 *
	 * Returns null when no group-specific restrictions should be applied.
	 *
	 * @return array<string, true>|null
	 */
	private function get_allowed_gateway_lookup(): ?array {
		$user_id = get_current_user_id();

		if ( array_key_exists( $user_id, $this->allowed_lookup_cache ) ) {
			return $this->allowed_lookup_cache[ $user_id ];
		}

		$group = $this->group_resolver->resolve_for_current_user();

		if ( null === $group || ! $group->has_payment_restrictions() ) {
			$this->allowed_lookup_cache[ $user_id ] = null;

			return null;
		}

		$lookup = array();

		foreach ( $group->get_allowed_payment_gateways() as $gateway_id ) {
			$lookup[ $gateway_id ] = true;
		}

		$this->allowed_lookup_cache[ $user_id ] = $lookup;

		return $lookup;
	}

	/**
	 * Whether payment gateway filtering should run in the current context.
	 *
	 * @return bool
	 */
	private function should_filter(): bool {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return false;
		}

		return true;
	}
}
