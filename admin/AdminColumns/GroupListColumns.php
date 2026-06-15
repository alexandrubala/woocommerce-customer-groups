<?php
/**
 * Custom columns on the customer groups list table.
 *
 * @package WooCommerce\CustomerGroups
 */

namespace WooCommerce\CustomerGroups\Admin\AdminColumns;

use WooCommerce\CustomerGroups\Helpers\Formatter;
use WooCommerce\CustomerGroups\Models\CustomerGroup;
use WooCommerce\CustomerGroups\Repositories\CustomerGroupRepository;

defined( 'ABSPATH' ) || exit;

/**
 * Class GroupListColumns
 */
final class GroupListColumns {

	/**
	 * Repository instance.
	 *
	 * @var CustomerGroupRepository
	 */
	private CustomerGroupRepository $repository;

	/**
	 * Constructor.
	 *
	 * @param CustomerGroupRepository $repository Group repository.
	 */
	public function __construct( CustomerGroupRepository $repository ) {
		$this->repository = $repository;
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_filter( 'manage_' . WCCG_POST_TYPE . '_posts_columns', array( $this, 'register_columns' ) );
		add_action( 'manage_' . WCCG_POST_TYPE . '_posts_custom_column', array( $this, 'render_column' ), 10, 2 );
	}

	/**
	 * Register custom list table columns.
	 *
	 * @param array<string, string> $columns Existing columns.
	 * @return array<string, string>
	 */
	public function register_columns( array $columns ): array {
		$new_columns = array();

		foreach ( $columns as $key => $label ) {
			$new_columns[ $key ] = $label;

			if ( 'title' === $key ) {
				$new_columns['wccg_discount_type']  = __( 'Discount Type', 'woocommerce-customer-groups' );
				$new_columns['wccg_discount_value'] = __( 'Discount', 'woocommerce-customer-groups' );
				$new_columns['wccg_users_count']    = __( 'Users', 'woocommerce-customer-groups' );
			}
		}

		return $new_columns;
	}

	/**
	 * Render a custom column value.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function render_column( string $column, int $post_id ): void {
		$post = get_post( $post_id );

		if ( ! $post instanceof \WP_Post ) {
			return;
		}

		$group = CustomerGroup::from_post( $post );

		switch ( $column ) {
			case 'wccg_discount_type':
				echo esc_html( Formatter::discount_type_label( $group->get_discount_type() ) );
				break;

			case 'wccg_discount_value':
				echo wp_kses_post( Formatter::format_discount( $group ) );
				break;

			case 'wccg_users_count':
				echo esc_html( (string) $this->repository->count_users( $post_id ) );
				break;
		}
	}
}
