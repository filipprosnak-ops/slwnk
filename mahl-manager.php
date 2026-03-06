<?php
/**
 * Plugin Name:       MAHL Manager
 * Description:       Manage amateur hockey leagues with seasons, teams, players, and games.
 * Version:           0.1.0
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            MAHL Manager
 * Text Domain:       mahl-manager
 *
 * @package MAHLManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MAHL_MANAGER_VERSION', '0.1.0' );
define( 'MAHL_MANAGER_FILE', __FILE__ );
define( 'MAHL_MANAGER_PATH', plugin_dir_path( __FILE__ ) );
define( 'MAHL_MANAGER_URL', plugin_dir_url( __FILE__ ) );

require_once MAHL_MANAGER_PATH . 'includes/class-mahl-autoloader.php';
require_once MAHL_MANAGER_PATH . 'includes/class-mahl-activator.php';
require_once MAHL_MANAGER_PATH . 'includes/class-mahl-deactivator.php';

MAHL_Autoloader::register();

register_activation_hook( MAHL_MANAGER_FILE, array( 'MAHL_Activator', 'activate' ) );
register_deactivation_hook( MAHL_MANAGER_FILE, array( 'MAHL_Deactivator', 'deactivate' ) );

if ( ! function_exists( 'mahl_manager' ) ) {
	/**
	 * Return the main plugin instance.
	 *
	 * @return MAHL_Plugin
	 */
	function mahl_manager() {
		static $plugin = null;

		if ( null === $plugin ) {
			$plugin = new MAHL_Plugin();
		}

		return $plugin;
	}
}

mahl_manager()->run();
