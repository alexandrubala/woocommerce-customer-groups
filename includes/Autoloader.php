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
			WCCG_PATH . 'admin/' . $relative_path,
			WCCG_PATH . 'frontend/' . $relative_path,
		);

		foreach ( $search_paths as $path ) {
			if ( is_readable( $path ) ) {
				require_once $path;
				return;
			}
		}
	}
}
