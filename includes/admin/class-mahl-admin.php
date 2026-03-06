<?php
/**
 * Admin bootstrap.
 *
 * @package MAHLManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register admin modules.
 */
class MAHL_Admin {

	/**
	 * Registered admin modules.
	 *
	 * @var array
	 */
	protected $modules = array();

	/**
	 * Set up admin modules.
	 */
	public function __construct() {
		$this->modules = array(
			new MAHL_Settings_Admin(),
			new MAHL_Relationships_Admin(),
			new MAHL_Game_Meta_Admin(),
		);
	}

	/**
	 * Register admin hooks.
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
