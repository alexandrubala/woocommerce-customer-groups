<?php
/**
 * Group settings meta box on the edit screen.
 *
 * @package WooCommerce\CustomerGroups
 */

namespace WooCommerce\CustomerGroups\Admin\MetaBoxes;

use WooCommerce\CustomerGroups\Helpers\PaymentMethodHelper;
use WooCommerce\CustomerGroups\Helpers\Sanitizer;
use WooCommerce\CustomerGroups\Helpers\ShippingMethodHelper;
use WooCommerce\CustomerGroups\Models\CustomerGroup;
use WooCommerce\CustomerGroups\Repositories\CustomerGroupRepository;

defined( 'ABSPATH' ) || exit;

/**
 * Class GroupSettingsMetaBox
 */
final class GroupSettingsMetaBox {

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
		add_action( 'add_meta_boxes', array( $this, 'register_meta_box' ) );
		add_action( 'save_post_' . WCCG_POST_TYPE, array( $this, 'save_meta_box' ), 10, 2 );
		add_action( 'deleted_post', array( $this, 'clear_cache_on_delete' ) );
		add_action( 'trashed_post', array( $this, 'clear_cache_on_delete' ) );
	}

	/**
	 * Clear cached groups when a group post is removed.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function clear_cache_on_delete( int $post_id ): void {
		if ( WCCG_POST_TYPE !== get_post_type( $post_id ) ) {
			return;
		}

		$this->repository->clear_cache();
	}

	/**
	 * Register the settings meta box.
	 *
	 * @return void
	 */
	public function register_meta_box(): void {
		add_meta_box(
			'wccg-group-settings',
			__( 'Group Settings', 'woocommerce-customer-groups' ),
			array( $this, 'render_meta_box' ),
			WCCG_POST_TYPE,
			'normal',
			'high'
		);
	}

	/**
	 * Render the meta box fields.
	 *
	 * @param \WP_Post $post Current post object.
	 * @return void
	 */
	public function render_meta_box( \WP_Post $post ): void {
		wp_nonce_field( 'wccg_save_group', 'wccg_group_nonce' );

		$group = CustomerGroup::from_post( $post );

		$discount_type  = $group->get_discount_type();
		$discount_value = $group->get_discount_value();
		$description    = $group->get_description();
		$allowed_shipping_methods = $group->get_allowed_shipping_methods();
		$shipping_methods_by_zone = ShippingMethodHelper::get_methods_by_zone();
		$allowed_payment_gateways = $group->get_allowed_payment_gateways();
		$payment_gateways         = PaymentMethodHelper::get_active_gateways();
		?>
		<table class="form-table wccg-group-settings" role="presentation">
			<tbody>
				<tr>
					<th scope="row">
						<label for="wccg_discount_type"><?php esc_html_e( 'Discount Type', 'woocommerce-customer-groups' ); ?></label>
					</th>
					<td>
						<select name="wccg_discount_type" id="wccg_discount_type">
							<option value="<?php echo esc_attr( CustomerGroup::DISCOUNT_TYPE_PERCENTAGE ); ?>" <?php selected( $discount_type, CustomerGroup::DISCOUNT_TYPE_PERCENTAGE ); ?>>
								<?php esc_html_e( 'Percentage', 'woocommerce-customer-groups' ); ?>
							</option>
							<option value="<?php echo esc_attr( CustomerGroup::DISCOUNT_TYPE_FIXED ); ?>" <?php selected( $discount_type, CustomerGroup::DISCOUNT_TYPE_FIXED ); ?>>
								<?php esc_html_e( 'Fixed amount', 'woocommerce-customer-groups' ); ?>
							</option>
						</select>
						<p class="description">
							<?php esc_html_e( 'Choose how the group discount is calculated.', 'woocommerce-customer-groups' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="wccg_discount_value"><?php esc_html_e( 'Discount Value', 'woocommerce-customer-groups' ); ?></label>
					</th>
					<td>
						<input
							type="number"
							name="wccg_discount_value"
							id="wccg_discount_value"
							value="<?php echo esc_attr( wc_format_decimal( $discount_value, 2 ) ); ?>"
							min="0"
							step="0.01"
							class="regular-text"
						/>
						<p class="description">
							<?php esc_html_e( 'For percentage discounts, enter a value between 0 and 100. For fixed discounts, enter the amount to subtract from the product price.', 'woocommerce-customer-groups' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="wccg_description"><?php esc_html_e( 'Description', 'woocommerce-customer-groups' ); ?></label>
					</th>
					<td>
						<textarea
							name="wccg_description"
							id="wccg_description"
							rows="4"
							class="large-text"
						><?php echo esc_textarea( $description ); ?></textarea>
						<p class="description">
							<?php esc_html_e( 'Optional internal description for store administrators.', 'woocommerce-customer-groups' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Allowed Shipping Methods', 'woocommerce-customer-groups' ); ?>
					</th>
					<td>
						<?php if ( empty( $shipping_methods_by_zone ) ) : ?>
							<p class="description">
								<?php esc_html_e( 'No enabled shipping methods found. Configure shipping zones in WooCommerce first.', 'woocommerce-customer-groups' ); ?>
							</p>
						<?php else : ?>
							<div class="wccg-allowed-shipping-methods-list">
								<?php foreach ( $shipping_methods_by_zone as $zone_name => $methods ) : ?>
									<div class="wccg-shipping-zone-group">
										<strong class="wccg-shipping-zone-name"><?php echo esc_html( $zone_name ); ?></strong>
										<?php foreach ( $methods as $rate_id => $method_title ) : ?>
											<label class="wccg-allowed-shipping-method-option">
												<input
													type="checkbox"
													name="wccg_allowed_shipping_methods[]"
													value="<?php echo esc_attr( $rate_id ); ?>"
													<?php checked( in_array( $rate_id, $allowed_shipping_methods, true ) ); ?>
												/>
												<?php echo esc_html( $method_title ); ?>
												<code class="wccg-shipping-rate-id"><?php echo esc_html( $rate_id ); ?></code>
											</label>
										<?php endforeach; ?>
									</div>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
						<p class="description">
							<?php esc_html_e( 'Leave all unchecked to allow every shipping method. Select one or more methods to restrict checkout options for customers in this group.', 'woocommerce-customer-groups' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Allowed Payment Gateways', 'woocommerce-customer-groups' ); ?>
					</th>
					<td>
						<?php if ( empty( $payment_gateways ) ) : ?>
							<p class="description">
								<?php esc_html_e( 'No enabled payment gateways found. Configure payment methods in WooCommerce first.', 'woocommerce-customer-groups' ); ?>
							</p>
						<?php else : ?>
							<div class="wccg-allowed-payment-gateways-list">
								<?php foreach ( $payment_gateways as $gateway_id => $gateway_title ) : ?>
									<label class="wccg-allowed-payment-gateway-option">
										<input
											type="checkbox"
											name="wccg_allowed_payment_gateways[]"
											value="<?php echo esc_attr( $gateway_id ); ?>"
											<?php checked( in_array( $gateway_id, $allowed_payment_gateways, true ) ); ?>
										/>
										<?php echo esc_html( $gateway_title ); ?>
										<code class="wccg-payment-gateway-id"><?php echo esc_html( $gateway_id ); ?></code>
									</label>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
						<p class="description">
							<?php esc_html_e( 'Leave all unchecked to allow every payment gateway. Select one or more gateways to restrict checkout options for customers in this group.', 'woocommerce-customer-groups' ); ?>
						</p>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Save meta box values.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 * @return void
	 */
	public function save_meta_box( int $post_id, \WP_Post $post ): void {
		unset( $post );

		if ( ! isset( $_POST['wccg_group_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wccg_group_nonce'] ) ), 'wccg_save_group' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( ! isset( $_POST['wccg_discount_type'], $_POST['wccg_discount_value'] ) ) {
			return;
		}

		$discount_type  = Sanitizer::discount_type( wp_unslash( $_POST['wccg_discount_type'] ) );
		$discount_value = Sanitizer::discount_value( wp_unslash( $_POST['wccg_discount_value'] ), $discount_type );
		$description    = isset( $_POST['wccg_description'] )
			? Sanitizer::description( wp_unslash( $_POST['wccg_description'] ) )
			: '';

		$allowed_shipping_methods = isset( $_POST['wccg_allowed_shipping_methods'] )
			? Sanitizer::allowed_shipping_methods( wp_unslash( $_POST['wccg_allowed_shipping_methods'] ) )
			: array();

		$valid_rate_ids = ShippingMethodHelper::get_available_rate_ids();

		if ( ! empty( $valid_rate_ids ) ) {
			$allowed_shipping_methods = array_values( array_intersect( $allowed_shipping_methods, $valid_rate_ids ) );
		}

		$allowed_payment_gateways = isset( $_POST['wccg_allowed_payment_gateways'] )
			? Sanitizer::allowed_payment_gateways( wp_unslash( $_POST['wccg_allowed_payment_gateways'] ) )
			: array();

		$valid_gateway_ids = PaymentMethodHelper::get_available_gateway_ids();

		if ( ! empty( $valid_gateway_ids ) ) {
			$allowed_payment_gateways = array_values( array_intersect( $allowed_payment_gateways, $valid_gateway_ids ) );
		}

		$meta = array(
			WCCG_META_DISCOUNT_TYPE             => $discount_type,
			WCCG_META_DISCOUNT_VALUE            => $discount_value,
			WCCG_META_DESCRIPTION               => $description,
			WCCG_META_ALLOWED_SHIPPING_METHODS  => $allowed_shipping_methods,
			WCCG_META_ALLOWED_PAYMENT_GATEWAYS  => $allowed_payment_gateways,
		);

		/**
		 * Filter group meta before it is saved.
		 *
		 * @param array $meta    Meta key/value pairs.
		 * @param int   $post_id Group post ID.
		 */
		$meta = apply_filters( 'wccg_group_meta', $meta, $post_id );

		update_post_meta( $post_id, WCCG_META_DISCOUNT_TYPE, $meta[ WCCG_META_DISCOUNT_TYPE ] );
		update_post_meta( $post_id, WCCG_META_DISCOUNT_VALUE, $meta[ WCCG_META_DISCOUNT_VALUE ] );
		update_post_meta( $post_id, WCCG_META_DESCRIPTION, $meta[ WCCG_META_DESCRIPTION ] );
		update_post_meta( $post_id, WCCG_META_ALLOWED_SHIPPING_METHODS, $meta[ WCCG_META_ALLOWED_SHIPPING_METHODS ] );
		update_post_meta( $post_id, WCCG_META_ALLOWED_PAYMENT_GATEWAYS, $meta[ WCCG_META_ALLOWED_PAYMENT_GATEWAYS ] );

		$this->repository->clear_cache();

		/**
		 * Fires after a customer group is saved.
		 *
		 * @param int $post_id Group post ID.
		 */
		do_action( 'wccg_group_saved', $post_id );
	}
}
