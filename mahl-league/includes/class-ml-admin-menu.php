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
		add_action( 'admin_init', array( self::class, 'redirect_default_editors' ) );
		add_action( 'admin_post_ml_save_match_editor', array( ML_Admin_Screens::class, 'handle_match_editor_save' ) );
		add_action( 'admin_post_ml_save_team', array( ML_Admin_Screens::class, 'handle_save_team' ) );
		add_action( 'admin_post_ml_delete_team', array( ML_Admin_Screens::class, 'handle_delete_team' ) );
		add_action( 'admin_post_ml_save_player', array( ML_Admin_Screens::class, 'handle_save_player' ) );
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
			'ml-teams',
			array( ML_Admin_Screens::class, 'render_teams' )
		);

		add_submenu_page(
			'ml-dashboard',
			__( 'Players', 'mahl-league' ),
			__( 'Players', 'mahl-league' ),
			'edit_ml_players',
			'ml-players',
			array( ML_Admin_Screens::class, 'render_players' )
		);

		add_submenu_page(
			'ml-dashboard',
			__( 'Matches', 'mahl-league' ),
			__( 'Matches', 'mahl-league' ),
			'edit_ml_matches',
			'ml-matches',
			array( ML_Admin_Screens::class, 'render_matches_list' )
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
			null,
			__( 'Add Team', 'mahl-league' ),
			__( 'Add Team', 'mahl-league' ),
			'edit_ml_teams',
			'ml-team-edit',
			array( ML_Admin_Screens::class, 'render_team_edit' )
		);

		add_submenu_page(
			null,
			__( 'Add Player', 'mahl-league' ),
			__( 'Add Player', 'mahl-league' ),
			'edit_ml_players',
			'ml-player-edit',
			array( ML_Admin_Screens::class, 'render_player_edit' )
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

	/**
	 * Redirect default post editors to MAHL custom screens.
	 *
	 * @return void
	 */
	public static function redirect_default_editors(): void {
		if ( ! is_admin() || wp_doing_ajax() ) {
			return;
		}

		global $pagenow;

		if ( 'post-new.php' === $pagenow ) {
			$post_type = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : '';
			if ( 'ml_team' === $post_type && current_user_can( 'edit_ml_teams' ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=ml-team-edit' ) );
				exit;
			}
			if ( 'ml_player' === $post_type && current_user_can( 'edit_ml_players' ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=ml-player-edit' ) );
				exit;
			}
			if ( 'ml_match' === $post_type && current_user_can( 'edit_ml_matches' ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=ml-match-editor' ) );
				exit;
			}
		}

		if ( 'post.php' !== $pagenow ) {
			return;
		}

		$action  = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : '';
		$post_id = isset( $_GET['post'] ) ? absint( wp_unslash( $_GET['post'] ) ) : 0;
		if ( 'edit' !== $action || $post_id <= 0 ) {
			return;
		}

		$post_type = get_post_type( $post_id );
		if ( 'ml_team' === $post_type && current_user_can( 'edit_post', $post_id ) ) {
			wp_safe_redirect( add_query_arg( array( 'page' => 'ml-team-edit', 'team_id' => $post_id ), admin_url( 'admin.php' ) ) );
			exit;
		}
		if ( 'ml_player' === $post_type && current_user_can( 'edit_post', $post_id ) ) {
			wp_safe_redirect( add_query_arg( array( 'page' => 'ml-player-edit', 'player_id' => $post_id ), admin_url( 'admin.php' ) ) );
			exit;
		}
		if ( 'ml_match' === $post_type && current_user_can( 'edit_post', $post_id ) ) {
			wp_safe_redirect( add_query_arg( array( 'page' => 'ml-match-editor', 'match_id' => $post_id ), admin_url( 'admin.php' ) ) );
			exit;
		}
	}
}
