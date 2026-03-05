<?php
/**
 * Simple class autoloader for MAHL League plugin classes.
 *
 * @package MAHLLeague
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ML_Autoloader
 */
class ML_Autoloader {

	/**
	 * Register autoload callback.
	 *
	 * @return void
	 */
	public static function register(): void {
		spl_autoload_register( array( self::class, 'autoload' ) );
	}

	/**
	 * Autoload ML_* class files from includes directory.
	 *
	 * @param string $class_name Class name.
	 *
	 * @return void
	 */
	public static function autoload( string $class_name ): void {
		if ( 0 !== strpos( $class_name, 'ML_' ) ) {
			return;
		}

		$file_name = strtolower( str_replace( '_', '-', $class_name ) );
		$file_path = __DIR__ . '/class-' . $file_name . '.php';

		if ( file_exists( $file_path ) ) {
			require_once $file_path;
		}
	}
}
