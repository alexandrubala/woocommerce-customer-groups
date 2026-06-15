<?php
/**
 * Environment requirements checker.
 *
 * @package WooCommerce\CustomerGroups
 */

namespace WooCommerce\CustomerGroups\Services;

defined( 'ABSPATH' ) || exit;

/**
 * Class RequirementsChecker
 */
final class RequirementsChecker {

	/**
	 * Minimum supported WordPress version.
	 */
	private const MIN_WP_VERSION = '6.0';

	/**
	 * Minimum supported PHP version.
	 */
	private const MIN_PHP_VERSION = '8.0';

	/**
	 * Minimum supported WooCommerce version.
	 */
	private const MIN_WC_VERSION = '7.0';

	/**
	 * Check whether all requirements are met.
	 *
	 * @param bool $require_wc_loaded Whether WooCommerce must already be loaded.
	 * @return bool
	 */
	public function passes( bool $require_wc_loaded = true ): bool {
		if ( version_compare( get_bloginfo( 'version' ), self::MIN_WP_VERSION, '<' ) ) {
			return false;
		}

		if ( version_compare( PHP_VERSION, self::MIN_PHP_VERSION, '<' ) ) {
			return false;
		}

		if ( ! $this->is_woocommerce_active() ) {
			return false;
		}

		if ( $require_wc_loaded && defined( 'WC_VERSION' ) && version_compare( WC_VERSION, self::MIN_WC_VERSION, '<' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Register admin notices for unmet requirements.
	 *
	 * @return void
	 */
	public function register_admin_notices(): void {
		add_action( 'admin_notices', array( $this, 'render_admin_notices' ) );
	}

	/**
	 * Render requirement failure notices.
	 *
	 * @return void
	 */
	public function render_admin_notices(): void {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		if ( version_compare( get_bloginfo( 'version' ), self::MIN_WP_VERSION, '<' ) ) {
			$this->render_notice(
				sprintf(
					/* translators: %s: minimum WordPress version */
					__( 'WooCommerce Customer Groups requires WordPress %s or higher.', 'woocommerce-customer-groups' ),
					self::MIN_WP_VERSION
				)
			);
		}

		if ( version_compare( PHP_VERSION, self::MIN_PHP_VERSION, '<' ) ) {
			$this->render_notice(
				sprintf(
					/* translators: %s: minimum PHP version */
					__( 'WooCommerce Customer Groups requires PHP %s or higher.', 'woocommerce-customer-groups' ),
					self::MIN_PHP_VERSION
				)
			);
		}

		if ( ! $this->is_woocommerce_active() ) {
			$this->render_notice(
				__( 'WooCommerce Customer Groups requires WooCommerce to be installed and active.', 'woocommerce-customer-groups' )
			);
		} elseif ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, self::MIN_WC_VERSION, '<' ) ) {
			$this->render_notice(
				sprintf(
					/* translators: %s: minimum WooCommerce version */
					__( 'WooCommerce Customer Groups requires WooCommerce %s or higher.', 'woocommerce-customer-groups' ),
					self::MIN_WC_VERSION
				)
			);
		}
	}

	/**
	 * Check whether WooCommerce is active.
	 *
	 * @return bool
	 */
	public function is_woocommerce_active(): bool {
		if ( class_exists( 'WooCommerce' ) ) {
			return true;
		}

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_active( 'woocommerce/woocommerce.php' );
	}

	/**
	 * Render a single admin notice.
	 *
	 * @param string $message Notice message.
	 * @return void
	 */
	private function render_notice( string $message ): void {
		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html( $message )
		);
	}
}
