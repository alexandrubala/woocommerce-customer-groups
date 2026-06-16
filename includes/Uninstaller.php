<?php
/**
 * Plugin uninstall routines.
 *
 * @package WooCommerce\CustomerGroups
 */

namespace WooCommerce\CustomerGroups;

defined( 'ABSPATH' ) || exit;

/**
 * Class Uninstaller
 */
final class Uninstaller {

	/**
	 * Remove all plugin data from the database.
	 *
	 * @return void
	 */
	public static function run(): void {
		self::delete_group_posts();
		self::delete_product_meta();
		self::delete_user_meta();
		Capabilities::unregister();
	}

	/**
	 * Permanently delete all customer group posts.
	 *
	 * @return void
	 */
	private static function delete_group_posts(): void {
		$group_ids = get_posts(
			array(
				'post_type'              => WCCG_POST_TYPE,
				'post_status'            => 'any',
				'posts_per_page'         => -1,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		foreach ( $group_ids as $group_id ) {
			wp_delete_post( (int) $group_id, true );
		}
	}

	/**
	 * Remove product visibility meta left on WooCommerce products.
	 *
	 * @return void
	 */
	private static function delete_product_meta(): void {
		delete_post_meta_by_key( WCCG_META_VISIBILITY_MODE );
		delete_post_meta_by_key( WCCG_META_ALLOWED_GROUP_IDS );
	}

	/**
	 * Remove group assignment meta from all users.
	 *
	 * @return void
	 */
	private static function delete_user_meta(): void {
		delete_metadata( 'user', 0, WCCG_USER_META_GROUP_ID, '', true );
	}
}
