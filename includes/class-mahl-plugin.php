<?php
/**
 * Main plugin loader.
 *
 * @package MAHLManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Coordinate the plugin modules.
 */
class MAHL_Plugin {

	/**
	 * Registered post type modules.
	 *
	 * @var array
	 */
	protected $post_types = array();

	/**
	 * Registered service modules.
	 *
	 * @var array
	 */
	protected $services = array();

	/**
	 * Admin module.
	 *
	 * @var MAHL_Admin
	 */
	protected $admin;

	/**
	 * Frontend module.
	 *
	 * @var MAHL_Frontend
	 */
	protected $frontend;

	/**
	 * Set up the plugin modules.
	 */
	public function __construct() {
		$this->load_modules();
	}

	/**
	 * Register the plugin modules with WordPress.
	 *
	 * @return void
	 */
	public function run() {
		$this->register_modules( $this->post_types );
		$this->register_modules( $this->services );
		$this->register_modules( array( $this->admin, $this->frontend ) );
	}

	/**
	 * Build the module list.
	 *
	 * @return void
	 */
	protected function load_modules() {
		$this->post_types = array(
			new MAHL_Season_Post_Type(),
			new MAHL_Phase_Post_Type(),
			new MAHL_Team_Post_Type(),
			new MAHL_Player_Post_Type(),
			new MAHL_Game_Post_Type(),
		);

		$this->services = array(
			new MAHL_Season_Service(),
			new MAHL_Phase_Service(),
			new MAHL_Settings_Service(),
			new MAHL_Team_Service(),
			new MAHL_Game_Service(),
			new MAHL_Standings_Service(),
			new MAHL_Player_Stats_Service(),
		);

		$this->admin    = new MAHL_Admin();
		$this->frontend = new MAHL_Frontend();
	}

	/**
	 * Register a list of modules if they expose a register method.
	 *
	 * @param array $modules Module instances.
	 * @return void
	 */
	protected function register_modules( $modules ) {
		foreach ( $modules as $module ) {
			if ( is_object( $module ) && method_exists( $module, 'register' ) ) {
				$module->register();
			}
		}
	}
}
