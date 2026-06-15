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
	 * Register capabilities for supported roles.
	 *
	 * @return void
	 */
	public static function register(): void {
		foreach ( self::ROLES as $role_name ) {
			$role = get_role( $role_name );

			if ( $role && ! $role->has_cap( self::MANAGE_GROUPS ) ) {
				$role->add_cap( self::MANAGE_GROUPS );
			}
		}
	}
}
