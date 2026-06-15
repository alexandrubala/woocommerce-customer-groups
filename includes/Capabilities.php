<?php
/**
 * Plugin capability registration.
 *
 * @package WooCommerce\CustomerGroups
 */

namespace WooCommerce\CustomerGroups;

defined( 'ABSPATH' ) || exit;

/**
 * Class Capabilities
 */
final class Capabilities {

	/**
	 * Capability required to manage customer groups.
	 */
	public const MANAGE_GROUPS = 'wccg_manage_groups';

	/**
	 * Roles that should receive the manage-groups capability.
	 *
	 * @var string[]
	 */
	private const ROLES = array(
		'administrator',
		'shop_manager',
	);

	/**
	 * Whether capability hooks were registered.
	 *
	 * @var bool
	 */
	private static bool $registered = false;

	/**
	 * Register capabilities for supported roles.
	 *
	 * @return void
	 */
	public static function register(): void {
		foreach ( self::ROLES as $role_name ) {
			$role = get_role( $role_name );

			if ( $role ) {
				$role->add_cap( self::MANAGE_GROUPS );
			}
		}

		if ( self::$registered ) {
			return;
		}

		self::$registered = true;
		add_filter( 'user_has_cap', array( self::class, 'map_manage_groups_cap' ), 10, 4 );
	}

	/**
	 * Whether the current user can manage customer groups.
	 *
	 * @return bool
	 */
	public static function current_user_can_manage(): bool {
		return current_user_can( self::MANAGE_GROUPS );
	}

	/**
	 * Grant manage-groups to store administrators even if caps were not persisted.
	 *
	 * @param bool[]   $allcaps All capabilities for the user.
	 * @param string[] $caps    Requested capabilities.
	 * @param array    $args    Capability check arguments.
	 * @param \WP_User $user    User object.
	 * @return bool[]
	 */
	public static function map_manage_groups_cap( array $allcaps, array $caps, array $args, \WP_User $user ): array {
		unset( $caps, $args );

		if ( ! empty( $allcaps[ self::MANAGE_GROUPS ] ) ) {
			return $allcaps;
		}

		if ( ! empty( $allcaps['manage_options'] ) || ! empty( $allcaps['manage_woocommerce'] ) ) {
			$allcaps[ self::MANAGE_GROUPS ] = true;
		}

		return $allcaps;
	}
}
