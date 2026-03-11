<?php
/**
 * Core plugin class.
 *
 * @package MAHL_Stats
 */

if (!defined('ABSPATH')) {
	exit;
}

class MAHL_Stats_Plugin
{
	protected MAHL_Stats_Loader $loader;
	protected string $version;
	protected string $plugin_name;

	public function __construct()
	{
		$this->version     = MAHL_STATS_VERSION;
		$this->plugin_name = 'mahl-stats';

		$this->load_dependencies();
		$this->define_core_hooks();
	}

	private function load_dependencies(): void
	{
		require_once MAHL_STATS_PLUGIN_DIR . 'admin/class-admin.php';
		require_once MAHL_STATS_PLUGIN_DIR . 'admin/class-admin-menu.php';

		require_once MAHL_STATS_PLUGIN_DIR . 'admin/seasons/class-seasons-admin.php';
		require_once MAHL_STATS_PLUGIN_DIR . 'admin/phases/class-phases-admin.php';
		require_once MAHL_STATS_PLUGIN_DIR . 'admin/rounds/class-rounds-admin.php';
		require_once MAHL_STATS_PLUGIN_DIR . 'admin/teams/class-teams-admin.php';
		require_once MAHL_STATS_PLUGIN_DIR . 'admin/players/class-players-admin.php';
		require_once MAHL_STATS_PLUGIN_DIR . 'admin/registrations/class-registrations-admin.php';
		require_once MAHL_STATS_PLUGIN_DIR . 'admin/games/class-games-admin.php';
		require_once MAHL_STATS_PLUGIN_DIR . 'admin/standings/class-standings-admin.php';
		require_once MAHL_STATS_PLUGIN_DIR . 'admin/player-stats/class-player-stats-admin.php';

		require_once MAHL_STATS_PLUGIN_DIR . 'src/Stats/class-standings-service.php';
		require_once MAHL_STATS_PLUGIN_DIR . 'src/Stats/class-player-stats-service.php';

		$this->loader = new MAHL_Stats_Loader();
	}

	private function define_core_hooks(): void
	{
		$this->loader->add_action('plugins_loaded', $this, 'load_textdomain');
		$this->loader->add_action('plugins_loaded', $this, 'maybe_upgrade_database');
		$this->loader->add_action('init', $this, 'register_rewrite_tags');
		$this->loader->add_action('init', $this, 'register_rewrite_rules');

		if (is_admin()) {
			$admin = new MAHL_Stats_Admin($this->plugin_name, $this->version);
			$this->loader->add_action('admin_menu', $admin, 'register_menu');

			$seasons_admin = new MAHL_Stats_Seasons_Admin();
			$this->loader->add_action('admin_post_mahl_save_season', $seasons_admin, 'handle_save');
			$this->loader->add_action('admin_post_mahl_archive_season', $seasons_admin, 'handle_archive');

			$phases_admin = new MAHL_Stats_Phases_Admin();
			$this->loader->add_action('admin_post_mahl_save_phase', $phases_admin, 'handle_save');

			$rounds_admin = new MAHL_Stats_Rounds_Admin();
			$this->loader->add_action('admin_post_mahl_save_round', $rounds_admin, 'handle_save');

			$teams_admin = new MAHL_Stats_Teams_Admin();
			$this->loader->add_action('admin_post_mahl_save_team', $teams_admin, 'handle_save');

			$players_admin = new MAHL_Stats_Players_Admin();
			$this->loader->add_action('admin_post_mahl_save_player', $players_admin, 'handle_save');

			$registrations_admin = new MAHL_Stats_Registrations_Admin();
			$this->loader->add_action('admin_post_mahl_save_registration', $registrations_admin, 'handle_save');

			$games_admin = new MAHL_Stats_Games_Admin();
			$this->loader->add_action('admin_post_mahl_save_game', $games_admin, 'handle_save');
			$this->loader->add_action('admin_post_mahl_add_game_roster', $games_admin, 'handle_add_roster_player');
			$this->loader->add_action('admin_post_mahl_delete_game_roster', $games_admin, 'handle_delete_roster_player');
			$this->loader->add_action('admin_post_mahl_add_game_goal', $games_admin, 'handle_add_goal');
			$this->loader->add_action('admin_post_mahl_delete_game_goal', $games_admin, 'handle_delete_goal');
			$this->loader->add_action('admin_post_mahl_add_game_penalty', $games_admin, 'handle_add_penalty');
			$this->loader->add_action('admin_post_mahl_delete_game_penalty', $games_admin, 'handle_delete_penalty');
			$this->loader->add_action('admin_post_mahl_add_game_goalie', $games_admin, 'handle_add_goalie');
			$this->loader->add_action('admin_post_mahl_delete_game_goalie', $games_admin, 'handle_delete_goalie');
			$this->loader->add_action('admin_post_mahl_add_game_official', $games_admin, 'handle_add_official');
			$this->loader->add_action('admin_post_mahl_delete_game_official', $games_admin, 'handle_delete_official');
			$this->loader->add_action('admin_post_mahl_add_game_note', $games_admin, 'handle_add_note');
			$this->loader->add_action('admin_post_mahl_delete_game_note', $games_admin, 'handle_delete_note');
		}
	}

	public function run(): void
	{
		$this->loader->run();
	}

	public function load_textdomain(): void
	{
		load_plugin_textdomain(
			$this->plugin_name,
			false,
			dirname(MAHL_STATS_PLUGIN_BASENAME) . '/languages/'
		);
	}

	public function register_rewrite_tags(): void
	{
		add_rewrite_tag('%mahl_season%', '([^&]+)');
		add_rewrite_tag('%mahl_team%', '([^&]+)');
		add_rewrite_tag('%mahl_player%', '([^&]+)');
		add_rewrite_tag('%mahl_game%', '([^&]+)');
	}

	public function register_rewrite_rules(): void
	{
		add_rewrite_rule('^sezona/([^/]+)/?$', 'index.php?mahl_season=$matches[1]', 'top');
		add_rewrite_rule('^tim/([^/]+)/?$', 'index.php?mahl_team=$matches[1]', 'top');
		add_rewrite_rule('^hrac/([^/]+)/?$', 'index.php?mahl_player=$matches[1]', 'top');
		add_rewrite_rule('^zapas/([^/]+)/?$', 'index.php?mahl_game=$matches[1]', 'top');
	}

	public function maybe_upgrade_database(): void
	{
		$current_db_version = get_option('mahl_stats_db_version', '');

		if ($current_db_version !== MAHL_STATS_DB_VERSION) {
			MAHL_Stats_Migrations::migrate();
		}
	}

	public function get_plugin_name(): string
	{
		return $this->plugin_name;
	}

	public function get_version(): string
	{
		return $this->version;
	}

	public function get_loader(): MAHL_Stats_Loader
	{
		return $this->loader;
	}
}