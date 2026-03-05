<?php
/**
 * Register admin menu for MAHL League.
 *
 * @package MAHLLeague
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ML_Admin_Menu
 */
class ML_Admin_Menu {

	/**
	 * Register admin hooks.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_menu' ) );
		add_action( 'admin_post_ml_save_match_editor', array( ML_Admin_Screens::class, 'handle_match_editor_save' ) );
		add_action( 'admin_post_ml_import_csv', array( ML_Admin_Screens::class, 'handle_import_csv' ) );
		add_action( 'admin_post_ml_export_csv', array( ML_Admin_Screens::class, 'handle_export_csv' ) );
		add_action( 'admin_post_ml_import_errors_csv', array( ML_Admin_Screens::class, 'handle_import_errors_csv' ) );
		add_action( 'admin_post_ml_create_repair_pages', array( ML_Admin_Screens::class, 'handle_create_repair_pages' ) );
		add_action( 'admin_post_ml_export_backup', array( ML_Admin_Screens::class, 'handle_export_backup' ) );
		add_action( 'admin_post_ml_restore_backup', array( ML_Admin_Screens::class, 'handle_restore_backup' ) );
	}

	/**
	 * Register admin menu tree.
	 *
	 * @return void
	 */
	public static function register_menu(): void {
		add_menu_page(
			__( 'MAHL Liga', 'mahl-league' ),
			__( 'MAHL Liga', 'mahl-league' ),
			'manage_ml_league',
			'ml-dashboard',
			array( ML_Admin_Screens::class, 'render_dashboard' ),
			'dashicons-shield',
			25
		);

		add_submenu_page(
			'ml-dashboard',
			__( 'Dashboard', 'mahl-league' ),
			__( 'Dashboard', 'mahl-league' ),
			'manage_ml_league',
			'ml-dashboard',
			array( ML_Admin_Screens::class, 'render_dashboard' )
		);

		add_submenu_page(
			'ml-dashboard',
			__( 'Teams', 'mahl-league' ),
			__( 'Teams', 'mahl-league' ),
			'edit_ml_teams',
			'edit.php?post_type=ml_team'
		);

		add_submenu_page(
			'ml-dashboard',
			__( 'Players', 'mahl-league' ),
			__( 'Players', 'mahl-league' ),
			'edit_ml_players',
			'edit.php?post_type=ml_player'
		);

		add_submenu_page(
			'ml-dashboard',
			__( 'Matches', 'mahl-league' ),
			__( 'Matches', 'mahl-league' ),
			'edit_ml_matches',
			'edit.php?post_type=ml_match'
		);

		add_submenu_page(
			'ml-dashboard',
			__( 'Match Editor', 'mahl-league' ),
			__( 'Match Editor', 'mahl-league' ),
			'manage_ml_league',
			'ml-match-editor',
			array( ML_Admin_Screens::class, 'render_match_editor' )
		);

		add_submenu_page(
			'ml-dashboard',
			__( 'Standings', 'mahl-league' ),
			__( 'Standings', 'mahl-league' ),
			'manage_ml_league',
			'ml-standings',
			array( ML_Admin_Screens::class, 'render_standings' )
		);

		add_submenu_page(
			'ml-dashboard',
			__( 'Statistics', 'mahl-league' ),
			__( 'Statistics', 'mahl-league' ),
			'manage_ml_league',
			'ml-statistics',
			array( ML_Admin_Screens::class, 'render_statistics' )
		);

		add_submenu_page(
			'ml-dashboard',
			__( 'Playoffs', 'mahl-league' ),
			__( 'Playoffs', 'mahl-league' ),
			'manage_ml_league',
			'ml-playoffs',
			array( ML_Admin_Screens::class, 'render_playoffs' )
		);

		add_submenu_page(
			'ml-dashboard',
			__( 'Import / Export', 'mahl-league' ),
			__( 'Import / Export', 'mahl-league' ),
			'manage_ml_league',
			'ml-import-export',
			array( ML_Admin_Screens::class, 'render_import_export' )
		);


		add_submenu_page(
			'ml-dashboard',
			__( 'IDs', 'mahl-league' ),
			__( 'IDs', 'mahl-league' ),
			'manage_ml_league',
			'ml-ids',
			array( ML_Admin_Screens::class, 'render_ids' )
		);

		add_submenu_page(
			'ml-dashboard',
			__( 'Backup / Restore', 'mahl-league' ),
			__( 'Backup / Restore', 'mahl-league' ),
			'manage_ml_league',
			'ml-backup-restore',
			array( ML_Admin_Screens::class, 'render_backup_restore' )
		);

		add_submenu_page(
			'ml-dashboard',
			__( 'Settings', 'mahl-league' ),
			__( 'Settings', 'mahl-league' ),
			'manage_ml_league',
			'ml-settings',
			array( ML_Admin_Screens::class, 'render_settings' )
		);
	}
}
