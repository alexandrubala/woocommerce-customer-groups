<?php
/**
 * Lightweight dependency injection container.
 *
 * @package WooCommerce\CustomerGroups
 */

namespace WooCommerce\CustomerGroups;

defined( 'ABSPATH' ) || exit;

/**
 * Class Container
 */
final class Container {

	/**
	 * Registered service factories.
	 *
	 * @var array<string, callable>
	 */
	private array $factories = array();

	/**
	 * Resolved service instances.
	 *
	 * @var array<string, object>
	 */
	private array $instances = array();

	/**
	 * Register a service factory.
	 *
	 * @param string   $id      Service identifier.
	 * @param callable $factory Factory callback.
	 * @return void
	 */
	public function set( string $id, callable $factory ): void {
		$this->factories[ $id ] = $factory;
		unset( $this->instances[ $id ] );
	}

	/**
	 * Resolve a service from the container.
	 *
	 * @param string $id Service identifier.
	 * @return object
	 */
	public function get( string $id ): object {
		if ( isset( $this->instances[ $id ] ) ) {
			return $this->instances[ $id ];
		}

		if ( ! isset( $this->factories[ $id ] ) ) {
			throw new \InvalidArgumentException(
				sprintf(
					'Service "%s" is not registered in the container.',
					$id
				)
			);
		}

		$this->instances[ $id ] = ( $this->factories[ $id ] )();

		return $this->instances[ $id ];
	}

	/**
	 * Check whether a service is registered.
	 *
	 * @param string $id Service identifier.
	 * @return bool
	 */
	public function has( string $id ): bool {
		return isset( $this->factories[ $id ] );
	}
}
