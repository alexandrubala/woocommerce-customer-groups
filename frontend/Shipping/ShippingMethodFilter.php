<?php
/**
 * Filters available shipping methods based on the customer's group.
 *
 * @package WooCommerce\CustomerGroups
 */

namespace WooCommerce\CustomerGroups\Frontend\Shipping;

use WooCommerce\CustomerGroups\Models\CustomerGroup;
use WooCommerce\CustomerGroups\Services\GroupResolver;

defined( 'ABSPATH' ) || exit;

/**
 * Class ShippingMethodFilter
 */
final class ShippingMethodFilter {

	/**
	 * Group resolver instance.
	 *
	 * @var GroupResolver
	 */
	private GroupResolver $group_resolver;

	/**
	 * Cached allowed rate ID lookups for the current request.
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
	 * Register WooCommerce shipping hooks.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_filter( 'woocommerce_package_rates', array( $this, 'filter_package_rates' ), 20, 2 );
	}

	/**
	 * Remove shipping rates that are not allowed for the current user's group.
	 *
	 * @param array<string, \WC_Shipping_Rate> $rates   Available shipping rates.
	 * @param array<string, mixed>               $package Shipping package.
	 * @return array<string, \WC_Shipping_Rate>
	 */
	public function filter_package_rates( array $rates, array $package ): array {
		if ( ! $this->should_filter() || empty( $rates ) ) {
			return $rates;
		}

		$allowed_lookup = $this->get_allowed_rate_lookup();

		if ( null === $allowed_lookup ) {
			return $rates;
		}

		foreach ( $rates as $rate_id => $rate ) {
			unset( $rate );

			if ( ! isset( $allowed_lookup[ $rate_id ] ) ) {
				unset( $rates[ $rate_id ] );
			}
		}

		$group = $this->group_resolver->resolve_for_current_user();

		/**
		 * Filter package rates after group shipping restrictions are applied.
		 *
		 * @param array<string, \WC_Shipping_Rate> $rates   Available shipping rates.
		 * @param array<string, mixed>             $package Shipping package.
		 * @param CustomerGroup|null               $group   Resolved customer group.
		 */
		return apply_filters( 'wccg_package_rates', $rates, $package, $group );
	}

	/**
	 * Build a fast lookup map of allowed rate IDs for the current user.
	 *
	 * Returns null when no group-specific restrictions should be applied.
	 *
	 * @return array<string, true>|null
	 */
	private function get_allowed_rate_lookup(): ?array {
		$user_id = get_current_user_id();

		if ( array_key_exists( $user_id, $this->allowed_lookup_cache ) ) {
			return $this->allowed_lookup_cache[ $user_id ];
		}

		$group = $this->group_resolver->resolve_for_current_user();

		if ( null === $group || ! $group->has_shipping_restrictions() ) {
			$this->allowed_lookup_cache[ $user_id ] = null;

			return null;
		}

		$lookup = array();

		foreach ( $group->get_allowed_shipping_methods() as $rate_id ) {
			$lookup[ $rate_id ] = true;
		}

		$this->allowed_lookup_cache[ $user_id ] = $lookup;

		return $lookup;
	}

	/**
	 * Whether shipping filtering should run in the current context.
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
