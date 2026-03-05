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
		ML_Shortcodes::register();
		self::add_capabilities();

		if ( is_admin() ) {
			ML_Admin_Menu::register();
			ML_ID_Utils::register();
		}

		ML_Frontend_Router::register();
	}

	/**
	 * Plugin activation callback.
	 *
	 * @return void
	 */
	public static function activate(): void {
		ML_Post_Types::register();
		ML_Taxonomies::register();
		self::add_capabilities();
		ML_Pages_Service::ensure_pages( false );
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

	/**
	 * Add plugin capabilities to administrator role.
	 *
	 * @return void
	 */
	private static function add_capabilities(): void {
		$role = get_role( 'administrator' );
		if ( ! $role instanceof WP_Role ) {
			return;
		}

		$caps = array(
			'manage_ml_league',
			'edit_ml_teams',
			'edit_ml_team',
			'read_ml_team',
			'delete_ml_team',
			'edit_others_ml_teams',
			'publish_ml_teams',
			'read_private_ml_teams',
			'delete_ml_teams',
			'delete_private_ml_teams',
			'delete_published_ml_teams',
			'delete_others_ml_teams',
			'edit_private_ml_teams',
			'edit_published_ml_teams',
			'edit_ml_players',
			'edit_ml_player',
			'read_ml_player',
			'delete_ml_player',
			'edit_others_ml_players',
			'publish_ml_players',
			'read_private_ml_players',
			'delete_ml_players',
			'delete_private_ml_players',
			'delete_published_ml_players',
			'delete_others_ml_players',
			'edit_private_ml_players',
			'edit_published_ml_players',
			'edit_ml_matches',
			'edit_ml_match',
			'read_ml_match',
			'delete_ml_match',
			'edit_others_ml_matches',
			'publish_ml_matches',
			'read_private_ml_matches',
			'delete_ml_matches',
			'delete_private_ml_matches',
			'delete_published_ml_matches',
			'delete_others_ml_matches',
			'edit_private_ml_matches',
			'edit_published_ml_matches',
		);

		foreach ( $caps as $cap ) {
			$role->add_cap( $cap );
		}
	}
}

