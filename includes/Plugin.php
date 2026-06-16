<?php
/**
 * Main plugin orchestrator.
 *
 * @package WooCommerce\CustomerGroups
 */

namespace WooCommerce\CustomerGroups;

use WooCommerce\CustomerGroups\Admin\AdminServiceProvider;
use WooCommerce\CustomerGroups\Frontend\FrontendServiceProvider;
use WooCommerce\CustomerGroups\Services\RequirementsChecker;

defined( 'ABSPATH' ) || exit;

/**
 * Class Plugin
 */
final class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static ?Plugin $instance = null;

	/**
	 * Service container.
	 *
	 * @var Container
	 */
	private Container $container;

	/**
	 * Whether the plugin has been initialized.
	 *
	 * @var bool
	 */
	private bool $initialized = false;

	/**
	 * Get the singleton instance.
	 *
	 * @return Plugin
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->container = new Container();
		$this->register_core_services();
	}

	/**
	 * Initialize the plugin.
	 *
	 * @return void
	 */
	public function init(): void {
		if ( $this->initialized ) {
			return;
		}

		$this->initialized = true;

		add_action( 'before_woocommerce_init', array( $this, 'declare_woocommerce_compatibility' ) );

		$requirements = $this->container->get( RequirementsChecker::class );

		if ( ! $requirements->passes() ) {
			$requirements->register_admin_notices();
			return;
		}

		Capabilities::register();
		$this->container->get( AdminServiceProvider::class )->register();
		$this->container->get( FrontendServiceProvider::class )->register();
	}

	/**
	 * Get the service container.
	 *
	 * @return Container
	 */
	public function container(): Container {
		return $this->container;
	}

	/**
	 * Register core services in the container.
	 *
	 * @return void
	 */
	private function register_core_services(): void {
		$this->container->set( RequirementsChecker::class, static fn(): RequirementsChecker => new RequirementsChecker() );
		$this->container->set( AdminServiceProvider::class, fn(): AdminServiceProvider => new AdminServiceProvider( $this->container ) );
		$this->container->set( FrontendServiceProvider::class, fn(): FrontendServiceProvider => new FrontendServiceProvider( $this->container ) );
	}

	/**
	 * Declare compatibility with WooCommerce features.
	 *
	 * @return void
	 */
	public function declare_woocommerce_compatibility(): void {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', WCCG_FILE, true );
		}
	}
}
