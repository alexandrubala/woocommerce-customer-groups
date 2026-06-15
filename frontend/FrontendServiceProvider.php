<?php
/**
 * Frontend service provider.
 *
 * @package WooCommerce\CustomerGroups
 */

namespace WooCommerce\CustomerGroups\Frontend;

use WooCommerce\CustomerGroups\Container;
use WooCommerce\CustomerGroups\Frontend\Pricing\CartDiscountApplier;
use WooCommerce\CustomerGroups\Frontend\Pricing\ProductPriceDisplay;
use WooCommerce\CustomerGroups\Frontend\Visibility\ProductVisibilityGuard;
use WooCommerce\CustomerGroups\Services\DiscountCalculator;
use WooCommerce\CustomerGroups\Services\GroupResolver;
use WooCommerce\CustomerGroups\Services\ProductVisibilityChecker;

defined( 'ABSPATH' ) || exit;

/**
 * Class FrontendServiceProvider
 */
final class FrontendServiceProvider {

	/**
	 * Service container.
	 *
	 * @var Container
	 */
	private Container $container;

	/**
	 * Constructor.
	 *
	 * @param Container $container Service container.
	 */
	public function __construct( Container $container ) {
		$this->container = $container;
	}

	/**
	 * Register frontend services and hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		$this->register_services();

		if ( is_admin() && ! wp_doing_ajax() ) {
			return;
		}

		$this->container->get( CartDiscountApplier::class )->register_hooks();
		$this->container->get( ProductPriceDisplay::class )->register_hooks();
		$this->container->get( ProductVisibilityGuard::class )->register_hooks();

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Register frontend services in the container.
	 *
	 * @return void
	 */
	private function register_services(): void {
		$this->container->set(
			DiscountCalculator::class,
			static fn(): DiscountCalculator => new DiscountCalculator()
		);

		$this->container->set(
			CartDiscountApplier::class,
			fn(): CartDiscountApplier => new CartDiscountApplier(
				$this->container->get( GroupResolver::class ),
				$this->container->get( DiscountCalculator::class )
			)
		);

		$this->container->set(
			ProductPriceDisplay::class,
			fn(): ProductPriceDisplay => new ProductPriceDisplay(
				$this->container->get( GroupResolver::class )
			)
		);

		$this->container->set(
			ProductVisibilityGuard::class,
			fn(): ProductVisibilityGuard => new ProductVisibilityGuard(
				$this->container->get( ProductVisibilityChecker::class )
			)
		);
	}

	/**
	 * Enqueue frontend assets.
	 *
	 * @return void
	 */
	public function enqueue_assets(): void {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$group = $this->container->get( GroupResolver::class )->resolve_for_current_user();

		if ( null === $group || ! $group->has_discount() ) {
			return;
		}

		wp_enqueue_style(
			'wccg-frontend',
			WCCG_URL . 'assets/css/frontend.css',
			array(),
			WCCG_VERSION
		);
	}
}
