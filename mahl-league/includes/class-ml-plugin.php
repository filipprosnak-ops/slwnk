<?php
/**
 * Core plugin bootstrap class.
 *
 * @package MAHLLeague
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ML_Plugin
 */
class ML_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var ML_Plugin|null
	 */
	private static ?ML_Plugin $instance = null;

	/**
	 * Get plugin instance.
	 *
	 * @return ML_Plugin
	 */
	public static function get_instance(): ML_Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize plugin hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'init', array( ML_Post_Types::class, 'register' ) );
		add_action( 'init', array( ML_Taxonomies::class, 'register' ) );

		ML_Stats_Engine::register();

		if ( is_admin() ) {
			ML_Admin_Menu::register();
		} else {
			ML_Frontend_Router::register();
		}
	}

	/**
	 * Plugin activation callback.
	 *
	 * @return void
	 */
	public static function activate(): void {
		ML_Post_Types::register();
		ML_Taxonomies::register();
		flush_rewrite_rules();
	}

	/**
	 * Plugin deactivation callback.
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		flush_rewrite_rules();
	}
}
