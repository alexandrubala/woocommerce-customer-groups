<?php
/**
 * Customer group repository contract.
 *
 * @package WooCommerce\CustomerGroups
 */

namespace WooCommerce\CustomerGroups\Contracts;

use WooCommerce\CustomerGroups\Models\CustomerGroup;

defined( 'ABSPATH' ) || exit;

/**
 * Interface GroupRepositoryInterface
 */
interface GroupRepositoryInterface {

	/**
	 * Find a group by ID.
	 *
	 * @param int $group_id Group post ID.
	 * @return CustomerGroup|null
	 */
	public function find_by_id( int $group_id ): ?CustomerGroup;

	/**
	 * Get all published groups.
	 *
	 * @return CustomerGroup[]
	 */
	public function get_all(): array;

	/**
	 * Check whether a group exists and is published.
	 *
	 * @param int $group_id Group post ID.
	 * @return bool
	 */
	public function exists( int $group_id ): bool;

	/**
	 * Count users assigned to a group.
	 *
	 * @param int $group_id Group post ID.
	 * @return int
	 */
	public function count_users( int $group_id ): int;
}
