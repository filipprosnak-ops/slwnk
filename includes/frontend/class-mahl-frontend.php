<?php
/**
 * Frontend bootstrap.
 *
 * @package MAHLManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register frontend modules.
 */
class MAHL_Frontend {

	/**
	 * Registered frontend modules.
	 *
	 * @var array
	 */
	protected $modules = array();

	/**
	 * Set up frontend modules.
	 */
	public function __construct() {
		$this->modules = array(
			new MAHL_Template_Loader(),
		);
	}

	/**
	 * Register frontend hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		foreach ( $this->modules as $module ) {
			if ( is_object( $module ) && method_exists( $module, 'register' ) ) {
				$module->register();
			}
		}
	}

	/**
	 * Enqueue frontend assets.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		wp_enqueue_style(
			'mahl-manager-frontend',
			MAHL_MANAGER_URL . 'assets/css/frontend.css',
			array(),
			MAHL_MANAGER_VERSION
		);
	}
}
