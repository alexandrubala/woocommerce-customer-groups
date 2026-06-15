<?php
/**
 * Customer group value object.
 *
 * @package WooCommerce\CustomerGroups
 */

namespace WooCommerce\CustomerGroups\Models;

defined( 'ABSPATH' ) || exit;

/**
 * Class CustomerGroup
 */
final class CustomerGroup {

	/**
	 * Supported discount types.
	 */
	public const DISCOUNT_TYPE_PERCENTAGE = 'percentage';
	public const DISCOUNT_TYPE_FIXED      = 'fixed';

	/**
	 * Group post ID.
	 *
	 * @var int
	 */
	private int $id;

	/**
	 * Group name.
	 *
	 * @var string
	 */
	private string $name;

	/**
	 * Group slug.
	 *
	 * @var string
	 */
	private string $slug;

	/**
	 * Post status.
	 *
	 * @var string
	 */
	private string $status;

	/**
	 * Discount type.
	 *
	 * @var string
	 */
	private string $discount_type;

	/**
	 * Discount value.
	 *
	 * @var float
	 */
	private float $discount_value;

	/**
	 * Optional description.
	 *
	 * @var string
	 */
	private string $description;

	/**
	 * Allowed WooCommerce shipping method rate IDs.
	 *
	 * @var string[]
	 */
	private array $allowed_shipping_methods;

	/**
	 * Allowed WooCommerce payment gateway IDs.
	 *
	 * @var string[]
	 */
	private array $allowed_payment_gateways;

	/**
	 * Constructor.
	 *
	 * @param int      $id                        Group post ID.
	 * @param string   $name                      Group name.
	 * @param string   $slug                      Group slug.
	 * @param string   $status                    Post status.
	 * @param string   $discount_type             Discount type.
	 * @param float    $discount_value            Discount value.
	 * @param string   $description               Optional description.
	 * @param string[] $allowed_shipping_methods  Allowed shipping method rate IDs.
	 * @param string[] $allowed_payment_gateways  Allowed payment gateway IDs.
	 */
	public function __construct(
		int $id,
		string $name,
		string $slug,
		string $status,
		string $discount_type,
		float $discount_value,
		string $description = '',
		array $allowed_shipping_methods = array(),
		array $allowed_payment_gateways = array()
	) {
		$this->id                        = $id;
		$this->name                      = $name;
		$this->slug                      = $slug;
		$this->status                    = $status;
		$this->discount_type             = $discount_type;
		$this->discount_value            = $discount_value;
		$this->description               = $description;
		$this->allowed_shipping_methods  = $allowed_shipping_methods;
		$this->allowed_payment_gateways  = $allowed_payment_gateways;
	}

	/**
	 * Create a model from a WordPress post object.
	 *
	 * @param \WP_Post $post Post object.
	 * @return self
	 */
	public static function from_post( \WP_Post $post ): self {
		$discount_type = (string) get_post_meta( $post->ID, WCCG_META_DISCOUNT_TYPE, true );

		if ( '' === $discount_type ) {
			$discount_type = self::DISCOUNT_TYPE_PERCENTAGE;
		}

		$discount_value = (float) get_post_meta( $post->ID, WCCG_META_DISCOUNT_VALUE, true );
		$description    = (string) get_post_meta( $post->ID, WCCG_META_DESCRIPTION, true );
		$allowed_shipping_methods = get_post_meta( $post->ID, WCCG_META_ALLOWED_SHIPPING_METHODS, true );

		if ( ! is_array( $allowed_shipping_methods ) ) {
			$allowed_shipping_methods = array();
		}

		$allowed_shipping_methods = array_values(
			array_filter(
				array_map( 'strval', $allowed_shipping_methods ),
				static fn( string $rate_id ): bool => '' !== $rate_id
			)
		);

		$allowed_payment_gateways = get_post_meta( $post->ID, WCCG_META_ALLOWED_PAYMENT_GATEWAYS, true );

		if ( ! is_array( $allowed_payment_gateways ) ) {
			$allowed_payment_gateways = array();
		}

		$allowed_payment_gateways = array_values(
			array_filter(
				array_map( 'strval', $allowed_payment_gateways ),
				static fn( string $gateway_id ): bool => '' !== $gateway_id
			)
		);

		return new self(
			(int) $post->ID,
			(string) $post->post_title,
			(string) $post->post_name,
			(string) $post->post_status,
			$discount_type,
			$discount_value,
			$description,
			$allowed_shipping_methods,
			$allowed_payment_gateways
		);
	}

	/**
	 * Get the group ID.
	 *
	 * @return int
	 */
	public function get_id(): int {
		return $this->id;
	}

	/**
	 * Get the group name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * Get the group slug.
	 *
	 * @return string
	 */
	public function get_slug(): string {
		return $this->slug;
	}

	/**
	 * Get the post status.
	 *
	 * @return string
	 */
	public function get_status(): string {
		return $this->status;
	}

	/**
	 * Get the discount type.
	 *
	 * @return string
	 */
	public function get_discount_type(): string {
		return $this->discount_type;
	}

	/**
	 * Get the discount value.
	 *
	 * @return float
	 */
	public function get_discount_value(): float {
		return $this->discount_value;
	}

	/**
	 * Get the description.
	 *
	 * @return string
	 */
	public function get_description(): string {
		return $this->description;
	}

	/**
	 * Whether the group is published.
	 *
	 * @return bool
	 */
	public function is_published(): bool {
		return 'publish' === $this->status;
	}

	/**
	 * Whether the group has an active discount.
	 *
	 * @return bool
	 */
	public function has_discount(): bool {
		return $this->discount_value > 0;
	}

	/**
	 * Get allowed WooCommerce shipping method rate IDs.
	 *
	 * @return string[]
	 */
	public function get_allowed_shipping_methods(): array {
		return $this->allowed_shipping_methods;
	}

	/**
	 * Whether the group restricts available shipping methods.
	 *
	 * @return bool
	 */
	public function has_shipping_restrictions(): bool {
		return ! empty( $this->allowed_shipping_methods );
	}

	/**
	 * Get allowed WooCommerce payment gateway IDs.
	 *
	 * @return string[]
	 */
	public function get_allowed_payment_gateways(): array {
		return $this->allowed_payment_gateways;
	}

	/**
	 * Whether the group restricts available payment gateways.
	 *
	 * @return bool
	 */
	public function has_payment_restrictions(): bool {
		return ! empty( $this->allowed_payment_gateways );
	}
}
