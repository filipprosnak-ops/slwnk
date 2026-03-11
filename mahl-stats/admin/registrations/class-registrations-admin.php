<?php
/**
 * Registrations admin controller.
 *
 * @package MAHL_Stats
 */

if (!defined('ABSPATH')) {
	exit;
}

class MAHL_Stats_Registrations_Admin
{
	/**
	 * Render registrations admin page.
	 *
	 * @return void
	 */
	public function render(): void
	{
		$action = isset($_GET['action']) ? sanitize_key((string) $_GET['action']) : 'list';

		if ($action === 'add') {
			$this->render_form();
			return;
		}

		if ($action === 'edit') {
			$this->render_form($this->get_current_registration());
			return;
		}

		$this->render_list();
	}

	/**
	 * Render registrations list.
	 *
	 * @return void
	 */
	private function render_list(): void
	{
		global $wpdb;

		$registrations_table = MAHL_Stats_DB::table('registrations');
		$players_table       = MAHL_Stats_DB::table('players');
		$teams_table         = MAHL_Stats_DB::table('teams');
		$seasons_table       = MAHL_Stats_DB::table('seasons');

		$registrations = $wpdb->get_results(
			"SELECT r.*, 
			        p.first_name, p.last_name,
			        t.name AS team_name,
			        s.name AS season_name
			 FROM {$registrations_table} r
			 LEFT JOIN {$players_table} p ON p.id = r.player_id
			 LEFT JOIN {$teams_table} t ON t.id = r.team_id
			 LEFT JOIN {$seasons_table} s ON s.id = r.season_id
			 ORDER BY r.id DESC"
		);

		include MAHL_STATS_PLUGIN_DIR . 'admin/registrations/views/list.php';
	}

	/**
	 * Render add/edit form.
	 *
	 * @param object|null $registration Registration object.
	 * @return void
	 */
	private function render_form(?object $registration = null): void
	{
		global $wpdb;

		$players_table = MAHL_Stats_DB::table('players');
		$teams_table   = MAHL_Stats_DB::table('teams');
		$seasons_table = MAHL_Stats_DB::table('seasons');

		$players = $wpdb->get_results("SELECT * FROM {$players_table} ORDER BY last_name ASC, first_name ASC");
		$teams   = $wpdb->get_results("SELECT * FROM {$teams_table} ORDER BY name ASC");
		$seasons = $wpdb->get_results("SELECT * FROM {$seasons_table} ORDER BY id DESC");

		if ($registration === null) {
			$registration = (object) [
				'id'            => 0,
				'season_id'     => 0,
				'team_id'       => 0,
				'player_id'     => 0,
				'jersey_number' => '',
				'position'      => 'F',
				'active'        => 1,
			];
		}

		include MAHL_STATS_PLUGIN_DIR . 'admin/registrations/views/form.php';
	}

	/**
	 * Handle registration save.
	 *
	 * @return void
	 */
	public function handle_save(): void
	{
		if (!current_user_can('manage_options')) {
			wp_die('Nemáš oprávnenie na túto akciu.');
		}

		check_admin_referer('mahl_save_registration');

		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			wp_die('Neplatná požiadavka.');
		}

		global $wpdb;

		$table = MAHL_Stats_DB::table('registrations');

		$registration_id = isset($_POST['registration_id']) ? absint($_POST['registration_id']) : 0;
		$season_id       = isset($_POST['season_id']) ? absint($_POST['season_id']) : 0;
		$team_id         = isset($_POST['team_id']) ? absint($_POST['team_id']) : 0;
		$player_id       = isset($_POST['player_id']) ? absint($_POST['player_id']) : 0;
		$jersey_number   = isset($_POST['jersey_number']) ? sanitize_text_field((string) $_POST['jersey_number']) : '';
		$position        = isset($_POST['position']) ? strtoupper(trim((string) $_POST['position'])) : 'F';
		$active          = isset($_POST['active']) ? 1 : 0;

		if ($season_id <= 0 || $team_id <= 0 || $player_id <= 0) {
			wp_die('Sezóna, tím aj hráč sú povinné.');
		}

		if ($jersey_number === '') {
			wp_die('Číslo dresu je povinné.');
		}

		$allowed_positions = ['G', 'D', 'F'];
		if (!in_array($position, $allowed_positions, true)) {
			$position = 'F';
		}

		$existing = $this->get_existing_registration($season_id, $team_id, $player_id);

		if ($existing && (int) $existing->id !== $registration_id) {
			wp_die('Tento hráč už je v danom tíme a sezóne registrovaný.');
		}

		$data = [
			'season_id'     => $season_id,
			'team_id'       => $team_id,
			'player_id'     => $player_id,
			'jersey_number' => $jersey_number,
			'position'      => $position,
			'active'        => $active,
		];

		$formats = [
			'%d',
			'%d',
			'%d',
			'%s',
			'%s',
			'%d',
		];

		if ($registration_id > 0) {
			$result = $wpdb->update(
				$table,
				$data,
				['id' => $registration_id],
				$formats,
				['%d']
			);

			if ($result === false) {
				wp_die('Registráciu sa nepodarilo upraviť.');
			}

			wp_safe_redirect(admin_url('admin.php?page=mahl-registrations&message=updated'));
			exit;
		}

		$result = $wpdb->insert($table, $data, $formats);

		if ($result === false) {
			wp_die('Registráciu sa nepodarilo uložiť.');
		}

		wp_safe_redirect(admin_url('admin.php?page=mahl-registrations&message=created'));
		exit;
	}

	/**
	 * Get current registration.
	 *
	 * @return object|null
	 */
	private function get_current_registration(): ?object
	{
		$registration_id = isset($_GET['registration_id']) ? absint($_GET['registration_id']) : 0;

		if ($registration_id <= 0) {
			wp_die('Chýba ID registrácie.');
		}

		global $wpdb;

		$table = MAHL_Stats_DB::table('registrations');

		$registration = $wpdb->get_row(
			$wpdb->prepare("SELECT * FROM {$table} WHERE id = %d LIMIT 1", $registration_id)
		);

		if (!$registration) {
			wp_die('Registrácia neexistuje.');
		}

		return $registration;
	}

	/**
	 * Check if registration already exists for season/team/player.
	 *
	 * @param int $season_id Season ID.
	 * @param int $team_id Team ID.
	 * @param int $player_id Player ID.
	 * @return object|null
	 */
	private function get_existing_registration(int $season_id, int $team_id, int $player_id): ?object
	{
		global $wpdb;

		$table = MAHL_Stats_DB::table('registrations');

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE season_id = %d AND team_id = %d AND player_id = %d LIMIT 1",
				$season_id,
				$team_id,
				$player_id
			)
		);

		return $row ?: null;
	}
}