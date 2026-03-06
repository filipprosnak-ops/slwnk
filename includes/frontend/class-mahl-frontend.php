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
		foreach ( $this->modules as $module ) {
			if ( is_object( $module ) && method_exists( $module, 'register' ) ) {
				$module->register();
			}
		}
	}
}
