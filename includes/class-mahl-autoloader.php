<?php
/**
 * Plugin autoloader.
 *
 * @package MAHLManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load plugin classes from a small explicit class map.
 */
class MAHL_Autoloader {

	/**
	 * Register the autoloader.
	 *
	 * @return void
	 */
	public static function register() {
		spl_autoload_register( array( __CLASS__, 'autoload' ) );
	}

	/**
	 * Load a matching class file.
	 *
	 * @param string $class_name Requested class name.
	 * @return void
	 */
	public static function autoload( $class_name ) {
		if ( 0 !== strpos( $class_name, 'MAHL_' ) ) {
			return;
		}

		$class_map = self::get_class_map();

		if ( isset( $class_map[ $class_name ] ) ) {
			require_once MAHL_MANAGER_PATH . $class_map[ $class_name ];
		}
	}

	/**
	 * Return the supported class map.
	 *
	 * @return array<string, string>
	 */
	protected static function get_class_map() {
		return array(
			'MAHL_Plugin'               => 'includes/class-mahl-plugin.php',
			'MAHL_Activator'            => 'includes/class-mahl-activator.php',
			'MAHL_Deactivator'          => 'includes/class-mahl-deactivator.php',
			'MAHL_Base_Post_Type'       => 'includes/post-types/class-mahl-base-post-type.php',
			'MAHL_Season_Post_Type'     => 'includes/post-types/class-mahl-season-post-type.php',
			'MAHL_Phase_Post_Type'      => 'includes/post-types/class-mahl-phase-post-type.php',
			'MAHL_Team_Post_Type'       => 'includes/post-types/class-mahl-team-post-type.php',
			'MAHL_Player_Post_Type'     => 'includes/post-types/class-mahl-player-post-type.php',
			'MAHL_Game_Post_Type'       => 'includes/post-types/class-mahl-game-post-type.php',
			'MAHL_Base_Service'         => 'includes/services/class-mahl-base-service.php',
			'MAHL_Season_Service'       => 'includes/services/class-mahl-season-service.php',
			'MAHL_Phase_Service'        => 'includes/services/class-mahl-phase-service.php',
			'MAHL_Settings_Service'     => 'includes/services/class-mahl-settings-service.php',
			'MAHL_Team_Service'         => 'includes/services/class-mahl-team-service.php',
			'MAHL_Game_Service'         => 'includes/services/class-mahl-game-service.php',
			'MAHL_Standings_Service'    => 'includes/services/class-mahl-standings-service.php',
			'MAHL_Player_Stats_Service' => 'includes/services/class-mahl-player-stats-service.php',
			'MAHL_Admin'                => 'includes/admin/class-mahl-admin.php',
			'MAHL_Admin_List_Tables'    => 'includes/admin/class-mahl-admin-list-tables.php',
			'MAHL_Admin_Context_Panels' => 'includes/admin/class-mahl-admin-context-panels.php',
			'MAHL_Relationships_Admin'  => 'includes/admin/class-mahl-relationships-admin.php',
			'MAHL_Game_Meta_Admin'      => 'includes/admin/class-mahl-game-meta-admin.php',
			'MAHL_Game_Player_Events_Admin' => 'includes/admin/class-mahl-game-player-events-admin.php',
			'MAHL_Settings_Admin'       => 'includes/admin/class-mahl-settings-admin.php',
			'MAHL_Frontend'             => 'includes/frontend/class-mahl-frontend.php',
			'MAHL_Frontend_Data_Provider' => 'includes/frontend/class-mahl-frontend-data-provider.php',
			'MAHL_Template_Loader'      => 'includes/frontend/class-mahl-template-loader.php',
		);
	}
}
