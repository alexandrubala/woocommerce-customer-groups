<?php
/**
 * Customer group repository.
 *
 * @package WooCommerce\CustomerGroups
 */

namespace WooCommerce\CustomerGroups\Repositories;

use WooCommerce\CustomerGroups\Contracts\GroupRepositoryInterface;
use WooCommerce\CustomerGroups\Models\CustomerGroup;

defined( 'ABSPATH' ) || exit;

/**
 * Class CustomerGroupRepository
 */
final class CustomerGroupRepository implements GroupRepositoryInterface {

	/**
	 * Find a group by ID.
	 *
	 * @param int $group_id Group post ID.
	 * @return CustomerGroup|null
	 */
	public function find_by_id( int $group_id ): ?CustomerGroup {
		if ( $group_id <= 0 ) {
			return null;
		}

		$post = get_post( $group_id );

		if ( ! $post instanceof \WP_Post || WCCG_POST_TYPE !== $post->post_type ) {
			return null;
		}

		return CustomerGroup::from_post( $post );
	}

	/**
	 * Get all published groups ordered by title.
	 *
	 * @return CustomerGroup[]
	 */
	public function get_all(): array {
		$cache_key = 'wccg_all_groups';
		$cached    = wp_cache_get( $cache_key, WCCG_CACHE_GROUP );

		if ( false !== $cached && is_array( $cached ) ) {
			return $cached;
		}

		$posts = get_posts(
			array(
				'post_type'              => WCCG_POST_TYPE,
				'post_status'            => 'publish',
				'posts_per_page'         => -1,
				'orderby'                => 'title',
				'order'                  => 'ASC',
				'no_found_rows'          => true,
				'update_post_meta_cache' => true,
				'update_post_term_cache' => false,
			)
		);

		$groups = array_map(
			static fn( \WP_Post $post ): CustomerGroup => CustomerGroup::from_post( $post ),
			$posts
		);

		wp_cache_set( $cache_key, $groups, WCCG_CACHE_GROUP );

		return $groups;
	}

	/**
	 * Check whether a group exists and is published.
	 *
	 * @param int $group_id Group post ID.
	 * @return bool
	 */
	public function exists( int $group_id ): bool {
		$group = $this->find_by_id( $group_id );

		return null !== $group && $group->is_published();
	}

	/**
	 * Count users assigned to a group.
	 *
	 * @param int $group_id Group post ID.
	 * @return int
	 */
	public function count_users( int $group_id ): int {
		if ( $group_id <= 0 ) {
			return 0;
		}

		$query = new \WP_User_Query(
			array(
				'fields'     => 'ID',
				'number'     => 1,
				'count_total'=> true,
				'meta_key'   => WCCG_USER_META_GROUP_ID,
				'meta_value' => (string) $group_id,
			)
		);

		return (int) $query->get_total();
	}

	/**
	 * Clear cached group lists.
	 *
	 * @return void
	 */
	public function clear_cache(): void {
		wp_cache_delete( 'wccg_all_groups', WCCG_CACHE_GROUP );
	}
}
