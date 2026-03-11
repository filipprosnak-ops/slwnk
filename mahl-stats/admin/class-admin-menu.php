<?php
/**
 * Admin menu registration.
 *
 * @package MAHL_Stats
 */

if (!defined('ABSPATH')) {
	exit;
}

class MAHL_Stats_Admin_Menu
{
	public function register(): void
	{
		add_menu_page(
			'MAHL Stats',
			'MAHL Stats',
			'manage_options',
			'mahl-stats',
			[$this, 'dashboard_page'],
			'dashicons-chart-bar',
			26
		);

		add_submenu_page(
			'mahl-stats',
			'Sezóny',
			'Sezóny',
			'manage_options',
			'mahl-seasons',
			[$this, 'seasons_page']
		);

		add_submenu_page(
			'mahl-stats',
			'Fázy',
			'Fázy',
			'manage_options',
			'mahl-phases',
			[$this, 'phases_page']
		);

		add_submenu_page(
			'mahl-stats',
			'Kolá',
			'Kolá',
			'manage_options',
			'mahl-rounds',
			[$this, 'rounds_page']
		);

		add_submenu_page(
			'mahl-stats',
			'Tímy',
			'Tímy',
			'manage_options',
			'mahl-teams',
			[$this, 'teams_page']
		);

		add_submenu_page(
			'mahl-stats',
			'Hráči',
			'Hráči',
			'manage_options',
			'mahl-players',
			[$this, 'players_page']
		);

		add_submenu_page(
			'mahl-stats',
			'Registrácie',
			'Registrácie',
			'manage_options',
			'mahl-registrations',
			[$this, 'registrations_page']
		);

		add_submenu_page(
			'mahl-stats',
			'Zápasy',
			'Zápasy',
			'manage_options',
			'mahl-games',
			[$this, 'games_page']
		);

		add_submenu_page(
			'mahl-stats',
			'Tabuľka',
			'Tabuľka',
			'manage_options',
			'mahl-standings',
			[$this, 'standings_page']
		);

		add_submenu_page(
			'mahl-stats',
			'Hráčske štatistiky',
			'Hráčske štatistiky',
			'manage_options',
			'mahl-player-stats',
			[$this, 'player_stats_page']
		);
	}

	public function dashboard_page(): void
	{
		echo '<div class="wrap">';
		echo '<h1>MAHL Stats</h1>';
		echo '<p>Štatistický systém ligy.</p>';
		echo '</div>';
	}

	public function seasons_page(): void
	{
		$controller = new MAHL_Stats_Seasons_Admin();
		$controller->render();
	}

	public function phases_page(): void
	{
		$controller = new MAHL_Stats_Phases_Admin();
		$controller->render();
	}

	public function rounds_page(): void
	{
		$controller = new MAHL_Stats_Rounds_Admin();
		$controller->render();
	}

	public function teams_page(): void
	{
		$controller = new MAHL_Stats_Teams_Admin();
		$controller->render();
	}

	public function players_page(): void
	{
		$controller = new MAHL_Stats_Players_Admin();
		$controller->render();
	}

	public function registrations_page(): void
	{
		$controller = new MAHL_Stats_Registrations_Admin();
		$controller->render();
	}

	public function games_page(): void
	{
		$controller = new MAHL_Stats_Games_Admin();
		$controller->render();
	}

	public function standings_page(): void
	{
		$controller = new MAHL_Stats_Standings_Admin();
		$controller->render();
	}

	public function player_stats_page(): void
	{
		$controller = new MAHL_Stats_Player_Stats_Admin();
		$controller->render();
	}
}