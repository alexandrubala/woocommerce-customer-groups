<?php
/**
 * Admin service provider.
 *
 * @package WooCommerce\CustomerGroups
 */

namespace WooCommerce\CustomerGroups\Admin;

use WooCommerce\CustomerGroups\Admin\AdminColumns\GroupListColumns;
use WooCommerce\CustomerGroups\Admin\MetaBoxes\GroupSettingsMetaBox;
use WooCommerce\CustomerGroups\Admin\Menus\GroupsMenu;
use WooCommerce\CustomerGroups\Admin\UserProfile\UserGroupField;
use WooCommerce\CustomerGroups\Container;
use WooCommerce\CustomerGroups\Contracts\GroupRepositoryInterface;
use WooCommerce\CustomerGroups\PostTypes\CustomerGroupPostType;
use WooCommerce\CustomerGroups\Repositories\CustomerGroupRepository;
use WooCommerce\CustomerGroups\Services\GroupResolver;

defined( 'ABSPATH' ) || exit;

/**
 * Class AdminServiceProvider
 */
final class AdminServiceProvider {

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
	 * Register admin services and hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		$this->register_services();

		if ( ! is_admin() ) {
			return;
		}

		$this->container->get( CustomerGroupPostType::class )->register_hooks();
		$this->container->get( GroupsMenu::class )->register_hooks();
		$this->container->get( GroupSettingsMetaBox::class )->register_hooks();
		$this->container->get( GroupListColumns::class )->register_hooks();
		$this->container->get( UserGroupField::class )->register_hooks();

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Register shared services in the container.
	 *
	 * @return void
	 */
	private function register_services(): void {
		$this->container->set(
			CustomerGroupRepository::class,
			static fn(): CustomerGroupRepository => new CustomerGroupRepository()
		);

		$this->container->set(
			GroupRepositoryInterface::class,
			fn(): GroupRepositoryInterface => $this->container->get( CustomerGroupRepository::class )
		);

		$this->container->set(
			GroupResolver::class,
			fn(): GroupResolver => new GroupResolver( $this->container->get( GroupRepositoryInterface::class ) )
		);

		$this->container->set(
			CustomerGroupPostType::class,
			static fn(): CustomerGroupPostType => new CustomerGroupPostType()
		);

		$this->container->set(
			GroupsMenu::class,
			static fn(): GroupsMenu => new GroupsMenu()
		);

		$this->container->set(
			GroupSettingsMetaBox::class,
			fn(): GroupSettingsMetaBox => new GroupSettingsMetaBox( $this->container->get( CustomerGroupRepository::class ) )
		);

		$this->container->set(
			GroupListColumns::class,
			fn(): GroupListColumns => new GroupListColumns( $this->container->get( CustomerGroupRepository::class ) )
		);

		$this->container->set(
			UserGroupField::class,
			fn(): UserGroupField => new UserGroupField( $this->container->get( CustomerGroupRepository::class ) )
		);
	}

	/**
	 * Enqueue admin assets on plugin screens.
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 * @return void
	 */
	public function enqueue_assets( string $hook_suffix ): void {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( ! $screen ) {
			return;
		}

		$is_group_screen = WCCG_POST_TYPE === $screen->post_type;
		$is_user_screen  = in_array( $hook_suffix, array( 'user-edit.php', 'profile.php' ), true );

		if ( ! $is_group_screen && ! $is_user_screen ) {
			return;
		}

		wp_enqueue_style(
			'wccg-admin',
			WCCG_URL . 'assets/css/admin.css',
			array(),
			WCCG_VERSION
		);
	}
}
