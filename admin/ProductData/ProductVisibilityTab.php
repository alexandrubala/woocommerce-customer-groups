<?php
/**
 * Product visibility settings on the WooCommerce product edit screen.
 *
 * @package WooCommerce\CustomerGroups
 */

namespace WooCommerce\CustomerGroups\Admin\ProductData;

use WooCommerce\CustomerGroups\Helpers\Sanitizer;
use WooCommerce\CustomerGroups\Repositories\CustomerGroupRepository;
use WooCommerce\CustomerGroups\Services\ProductVisibilityChecker;

defined( 'ABSPATH' ) || exit;

/**
 * Class ProductVisibilityTab
 */
final class ProductVisibilityTab {

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
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'register_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( $this, 'render_panel' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_meta' ), 10, 2 );
		add_action( 'deleted_post', array( $this, 'clear_cache_on_product_change' ) );
		add_action( 'trashed_post', array( $this, 'clear_cache_on_product_change' ) );
	}

	/**
	 * Clear visibility cache when a product is removed.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function clear_cache_on_product_change( int $post_id ): void {
		if ( 'product' !== get_post_type( $post_id ) ) {
			return;
		}

		ProductVisibilityChecker::clear_cache();
	}

	/**
	 * Register the Customer Groups product data tab.
	 *
	 * @param array<string, array<string, mixed>> $tabs Existing tabs.
	 * @return array<string, array<string, mixed>>
	 */
	public function register_tab( array $tabs ): array {
		$tabs['wccg_customer_groups'] = array(
			'label'    => __( 'Customer Groups', 'woocommerce-customer-groups' ),
			'target'   => 'wccg_customer_groups_data',
			'class'    => array( 'show_if_simple', 'show_if_variable', 'show_if_grouped', 'show_if_external' ),
			'priority' => 80,
		);

		return $tabs;
	}

	/**
	 * Render the Customer Groups product data panel.
	 *
	 * @return void
	 */
	public function render_panel(): void {
		global $post;

		if ( ! $post instanceof \WP_Post ) {
			return;
		}

		$visibility_mode = (string) get_post_meta( $post->ID, WCCG_META_VISIBILITY_MODE, true );

		if ( '' === $visibility_mode ) {
			$visibility_mode = WCCG_VISIBILITY_MODE_EVERYONE;
		}

		$allowed_group_ids = get_post_meta( $post->ID, WCCG_META_ALLOWED_GROUP_IDS, true );

		if ( ! is_array( $allowed_group_ids ) ) {
			$allowed_group_ids = array();
		}

		$allowed_group_ids = array_map( 'absint', $allowed_group_ids );
		$groups            = $this->repository->get_all();
		$is_restricted     = WCCG_VISIBILITY_MODE_RESTRICTED === $visibility_mode;
		?>
		<div id="wccg_customer_groups_data" class="panel woocommerce_options_panel hidden">
			<div class="options_group">
				<?php
				woocommerce_wp_radio(
					array(
						'id'          => WCCG_META_VISIBILITY_MODE,
						'label'       => __( 'Visibility', 'woocommerce-customer-groups' ),
						'value'       => $visibility_mode,
						'options'     => array(
							WCCG_VISIBILITY_MODE_EVERYONE   => __( 'Visible to everyone', 'woocommerce-customer-groups' ),
							WCCG_VISIBILITY_MODE_RESTRICTED => __( 'Restrict to specific groups', 'woocommerce-customer-groups' ),
						),
						'description' => __( 'Control which customer groups can see and purchase this product on the storefront.', 'woocommerce-customer-groups' ),
						'desc_tip'    => false,
					)
				);
				?>
			</div>

			<div class="options_group wccg-allowed-groups-field"<?php echo $is_restricted ? '' : ' style="display:none;"'; ?>>
				<p class="form-field <?php echo esc_attr( WCCG_META_ALLOWED_GROUP_IDS ); ?>_field">
					<label><?php esc_html_e( 'Allowed Groups', 'woocommerce-customer-groups' ); ?></label>
					<?php if ( empty( $groups ) ) : ?>
						<span class="description">
							<?php esc_html_e( 'No active customer groups found. Create a group first to restrict visibility.', 'woocommerce-customer-groups' ); ?>
						</span>
					<?php else : ?>
						<ul class="wccg-allowed-groups-list">
							<?php foreach ( $groups as $group ) : ?>
								<li>
									<label>
										<input
											type="checkbox"
											name="<?php echo esc_attr( WCCG_META_ALLOWED_GROUP_IDS ); ?>[]"
											value="<?php echo esc_attr( (string) $group->get_id() ); ?>"
											<?php checked( in_array( $group->get_id(), $allowed_group_ids, true ) ); ?>
										/>
										<?php echo esc_html( $group->get_name() ); ?>
									</label>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
					<span class="description">
						<?php esc_html_e( 'Only customers assigned to the selected groups will see this product. Guests and other users will not see it.', 'woocommerce-customer-groups' ); ?>
					</span>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Save product visibility settings.
	 *
	 * @param int      $post_id Product ID.
	 * @param \WP_Post $post    Post object.
	 * @return void
	 */
	public function save_product_meta( int $post_id, \WP_Post $post ): void {
		unset( $post );

		if ( ! current_user_can( 'edit_product', $post_id ) ) {
			return;
		}

		if ( ! isset( $_POST[ WCCG_META_VISIBILITY_MODE ] ) ) {
			return;
		}

		$visibility_mode = Sanitizer::visibility_mode( wp_unslash( $_POST[ WCCG_META_VISIBILITY_MODE ] ) );

		update_post_meta( $post_id, WCCG_META_VISIBILITY_MODE, $visibility_mode );

		$allowed_group_ids = array();

		if ( WCCG_VISIBILITY_MODE_RESTRICTED === $visibility_mode && isset( $_POST[ WCCG_META_ALLOWED_GROUP_IDS ] ) ) {
			$allowed_group_ids = Sanitizer::allowed_group_ids( wp_unslash( $_POST[ WCCG_META_ALLOWED_GROUP_IDS ] ) );
			$allowed_group_ids = array_values(
				array_filter(
					$allowed_group_ids,
					fn( int $group_id ): bool => $this->repository->exists( $group_id )
				)
			);
		}

		update_post_meta( $post_id, WCCG_META_ALLOWED_GROUP_IDS, $allowed_group_ids );

		ProductVisibilityChecker::clear_cache();

		/**
		 * Fires after product visibility settings are saved.
		 *
		 * @param int    $post_id           Product ID.
		 * @param string $visibility_mode   Visibility mode.
		 * @param int[]  $allowed_group_ids Allowed group IDs.
		 */
		do_action( 'wccg_product_visibility_saved', $post_id, $visibility_mode, $allowed_group_ids );
	}
}
