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
	 * Primitive capability required to list and manage customer groups.
	 */
	public const MANAGE_GROUPS = 'edit_wccg_groups';

	/**
	 * Roles that should receive customer group capabilities.
	 *
	 * @var string[]
	 */
	private const ROLES = array(
		'administrator',
		'shop_manager',
	);

	/**
	 * Capabilities generated for the customer group post type.
	 *
	 * @var string[]
	 */
	private const GROUP_CAPS = array(
		'edit_wccg_groups',
		'edit_wccg_group',
		'read_wccg_group',
		'delete_wccg_group',
		'edit_others_wccg_groups',
		'publish_wccg_groups',
		'read_private_wccg_groups',
		'delete_wccg_groups',
		'delete_private_wccg_groups',
		'delete_published_wccg_groups',
		'delete_others_wccg_groups',
		'edit_private_wccg_groups',
		'edit_published_wccg_groups',
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

			if ( ! $role ) {
				continue;
			}

			foreach ( self::GROUP_CAPS as $capability ) {
				$role->add_cap( $capability );
			}
		}

		if ( self::$registered ) {
			return;
		}

		self::$registered = true;
		add_filter( 'user_has_cap', array( self::class, 'grant_group_caps' ), 10, 4 );
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
	 * Grant customer group capabilities to store administrators.
	 *
	 * @param bool[]   $allcaps All capabilities for the user.
	 * @param string[] $caps    Requested capabilities.
	 * @param array    $args    Capability check arguments.
	 * @param \WP_User $user    User object.
	 * @return bool[]
	 */
	public static function grant_group_caps( array $allcaps, array $caps, array $args, \WP_User $user ): array {
		unset( $caps, $args );

		if ( self::user_is_group_manager( $user, $allcaps ) ) {
			foreach ( self::GROUP_CAPS as $capability ) {
				$allcaps[ $capability ] = true;
			}
		}

		return $allcaps;
	}

	/**
	 * Whether a user should be allowed to manage customer groups.
	 *
	 * @param \WP_User $user    User object.
	 * @param bool[]   $allcaps Known capabilities for the user.
	 * @return bool
	 */
	private static function user_is_group_manager( \WP_User $user, array $allcaps ): bool {
		if ( ! empty( $allcaps[ self::MANAGE_GROUPS ] ) ) {
			return true;
		}

		if ( ! empty( $allcaps['manage_options'] ) || ! empty( $allcaps['manage_woocommerce'] ) ) {
			return true;
		}

		$roles = (array) $user->roles;

		return in_array( 'administrator', $roles, true ) || in_array( 'shop_manager', $roles, true );
	}
}
