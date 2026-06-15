<?php
/**
 * PSR-4 autoloader fallback when Composer vendor is unavailable.
 *
 * @package WooCommerce\CustomerGroups
 */

namespace WooCommerce\CustomerGroups;

defined( 'ABSPATH' ) || exit;

/**
 * Class Autoloader
 */
final class Autoloader {

	/**
	 * Register the autoloader.
	 *
	 * @return void
	 */
	public static function register(): void {
		spl_autoload_register( array( self::class, 'autoload' ) );
	}

	/**
	 * Autoload plugin classes.
	 *
	 * @param string $class Fully qualified class name.
	 * @return void
	 */
	public static function autoload( string $class ): void {
		$prefix = 'WooCommerce\\CustomerGroups\\';

		if ( 0 !== strpos( $class, $prefix ) ) {
			return;
		}

		$relative_class = substr( $class, strlen( $prefix ) );
		$relative_path  = str_replace( '\\', '/', $relative_class ) . '.php';
		$search_paths   = array(
			WCCG_PATH . 'includes/' . $relative_path,
		);

		$admin_prefix = 'Admin/';

		if ( str_starts_with( $relative_path, $admin_prefix ) ) {
			$search_paths[] = WCCG_PATH . 'admin/' . substr( $relative_path, strlen( $admin_prefix ) );
		}

		$frontend_prefix = 'Frontend/';

		if ( str_starts_with( $relative_path, $frontend_prefix ) ) {
			$search_paths[] = WCCG_PATH . 'frontend/' . substr( $relative_path, strlen( $frontend_prefix ) );
		}

		foreach ( $search_paths as $path ) {
			if ( is_readable( $path ) ) {
				require_once $path;
				return;
			}
		}
	}
}
