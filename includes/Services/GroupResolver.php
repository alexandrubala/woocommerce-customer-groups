<?php
/**
 * Resolves the current user's customer group.
 *
 * @package WooCommerce\CustomerGroups
 */

namespace WooCommerce\CustomerGroups\Services;

use WooCommerce\CustomerGroups\Contracts\GroupRepositoryInterface;
use WooCommerce\CustomerGroups\Models\CustomerGroup;

defined( 'ABSPATH' ) || exit;

/**
 * Class GroupResolver
 */
final class GroupResolver {

	/**
	 * Repository instance.
	 *
	 * @var GroupRepositoryInterface
	 */
	private GroupRepositoryInterface $repository;

	/**
	 * Cached group for the current request.
	 *
	 * @var array<int, CustomerGroup|null>
	 */
	private array $cache = array();

	/**
	 * Constructor.
	 *
	 * @param GroupRepositoryInterface $repository Group repository.
	 */
	public function __construct( GroupRepositoryInterface $repository ) {
		$this->repository = $repository;
	}

	/**
	 * Resolve the group for a user ID.
	 *
	 * @param int $user_id User ID.
	 * @return CustomerGroup|null
	 */
	public function resolve_for_user( int $user_id ): ?CustomerGroup {
		if ( isset( $this->cache[ $user_id ] ) ) {
			return $this->cache[ $user_id ];
		}

		$this->cache[ $user_id ] = null;

		if ( $user_id <= 0 ) {
			return null;
		}

		$group_id = (int) get_user_meta( $user_id, WCCG_USER_META_GROUP_ID, true );

		if ( $group_id <= 0 ) {
			return null;
		}

		$group = $this->repository->find_by_id( $group_id );

		if ( null === $group || ! $group->is_published() ) {
			return null;
		}

		/**
		 * Filter the resolved customer group for a user.
		 *
		 * @param CustomerGroup|null $group   Resolved group.
		 * @param int                $user_id User ID.
		 */
		$group = apply_filters( 'wccg_resolved_group', $group, $user_id );

		$this->cache[ $user_id ] = $group instanceof CustomerGroup ? $group : null;

		return $this->cache[ $user_id ];
	}

	/**
	 * Resolve the group for the current logged-in user.
	 *
	 * @return CustomerGroup|null
	 */
	public function resolve_for_current_user(): ?CustomerGroup {
		return $this->resolve_for_user( get_current_user_id() );
	}
}
