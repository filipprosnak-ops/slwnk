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
			new MAHL_Admin_List_Tables(),
			new MAHL_Admin_Context_Panels(),
			new MAHL_Relationships_Admin(),
			new MAHL_Game_Meta_Admin(),
			new MAHL_Game_Player_Events_Admin(),
		);
	}

	/**
	 * Register admin hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		foreach ( $this->modules as $module ) {
			if ( is_object( $module ) && method_exists( $module, 'register' ) ) {
				$module->register();
			}
		}
	}

	/**
	 * Enqueue shared admin assets on plugin screens.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		$screen = get_current_screen();

		if ( ! $screen ) {
			return;
		}

		$is_plugin_post_type = ! empty( $screen->post_type ) && 0 === strpos( $screen->post_type, 'mahl_' );
		$is_plugin_page      = ! empty( $screen->base ) && false !== strpos( $screen->base, 'mahl' );

		if ( ! $is_plugin_post_type && ! $is_plugin_page ) {
			return;
		}

		wp_enqueue_style(
			'mahl-manager-admin',
			MAHL_MANAGER_URL . 'assets/css/admin.css',
			array(),
			MAHL_MANAGER_VERSION
		);
	}
}
