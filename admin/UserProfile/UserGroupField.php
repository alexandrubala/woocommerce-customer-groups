<?php
/**
 * Customer group assignment field on user profiles.
 *
 * @package WooCommerce\CustomerGroups
 */

namespace WooCommerce\CustomerGroups\Admin\UserProfile;

use WooCommerce\CustomerGroups\Helpers\Sanitizer;
use WooCommerce\CustomerGroups\Repositories\CustomerGroupRepository;

defined( 'ABSPATH' ) || exit;

/**
 * Class UserGroupField
 */
final class UserGroupField {

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
		add_action( 'show_user_profile', array( $this, 'render_field' ) );
		add_action( 'edit_user_profile', array( $this, 'render_field' ) );
		add_action( 'personal_options_update', array( $this, 'save_field' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_field' ) );
		add_filter( 'manage_users_columns', array( $this, 'register_users_column' ) );
		add_filter( 'manage_users_custom_column', array( $this, 'render_users_column' ), 10, 3 );
	}

	/**
	 * Render the customer group field.
	 *
	 * @param \WP_User $user User object.
	 * @return void
	 */
	public function render_field( \WP_User $user ): void {
		if ( ! current_user_can( 'edit_user', $user->ID ) ) {
			return;
		}

		$groups         = $this->repository->get_all();
		$selected_group = (int) get_user_meta( $user->ID, WCCG_USER_META_GROUP_ID, true );

		wp_nonce_field( 'wccg_save_user_group', 'wccg_user_group_nonce' );
		?>
		<h2><?php esc_html_e( 'Customer Group', WCCG_TEXT_DOMAIN ); ?></h2>
		<table class="form-table wccg-user-group-field" role="presentation">
			<tbody>
				<tr>
					<th scope="row">
						<label for="wccg_group_id"><?php esc_html_e( 'Assigned Group', WCCG_TEXT_DOMAIN ); ?></label>
					</th>
					<td>
						<select name="wccg_group_id" id="wccg_group_id">
							<option value="0"><?php esc_html_e( '— No group —', WCCG_TEXT_DOMAIN ); ?></option>
							<?php foreach ( $groups as $group ) : ?>
								<option value="<?php echo esc_attr( (string) $group->get_id() ); ?>" <?php selected( $selected_group, $group->get_id() ); ?>>
									<?php echo esc_html( $group->get_name() ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<p class="description">
							<?php esc_html_e( 'Assign this customer to a group to apply group-specific pricing and benefits.', WCCG_TEXT_DOMAIN ); ?>
						</p>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Save the customer group field.
	 *
	 * @param int $user_id User ID.
	 * @return void
	 */
	public function save_field( int $user_id ): void {
		if ( ! isset( $_POST['wccg_user_group_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wccg_user_group_nonce'] ) ), 'wccg_save_user_group' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return;
		}

		$group_id = isset( $_POST['wccg_group_id'] )
			? Sanitizer::group_id( wp_unslash( $_POST['wccg_group_id'] ) )
			: 0;

		if ( $group_id > 0 && ! $this->repository->exists( $group_id ) ) {
			return;
		}

		if ( $group_id > 0 ) {
			update_user_meta( $user_id, WCCG_USER_META_GROUP_ID, $group_id );
		} else {
			delete_user_meta( $user_id, WCCG_USER_META_GROUP_ID );
		}

		/**
		 * Fires after a user group assignment is saved.
		 *
		 * @param int $user_id  User ID.
		 * @param int $group_id Assigned group ID. 0 means no group.
		 */
		do_action( 'wccg_user_group_saved', $user_id, $group_id );
	}

	/**
	 * Register the users list table column.
	 *
	 * @param array<string, string> $columns Existing columns.
	 * @return array<string, string>
	 */
	public function register_users_column( array $columns ): array {
		$columns['wccg_customer_group'] = __( 'Customer Group', WCCG_TEXT_DOMAIN );

		return $columns;
	}

	/**
	 * Render the users list table column value.
	 *
	 * @param string $output      Column output.
	 * @param string $column_name Column key.
	 * @param int    $user_id     User ID.
	 * @return string
	 */
	public function render_users_column( string $output, string $column_name, int $user_id ): string {
		if ( 'wccg_customer_group' !== $column_name ) {
			return $output;
		}

		$group_id = (int) get_user_meta( $user_id, WCCG_USER_META_GROUP_ID, true );

		if ( $group_id <= 0 ) {
			return esc_html__( '—', WCCG_TEXT_DOMAIN );
		}

		$group = $this->repository->find_by_id( $group_id );

		if ( null === $group ) {
			return esc_html__( '—', WCCG_TEXT_DOMAIN );
		}

		return esc_html( $group->get_name() );
	}
}
