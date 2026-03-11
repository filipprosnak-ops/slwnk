<?php
/**
 * Games admin controller.
 *
 * @package MAHL_Stats
 */

if (!defined('ABSPATH')) {
	exit;
}

class MAHL_Stats_Games_Admin
{
	public function render(): void
	{
		$action = isset($_GET['action']) ? sanitize_key((string) $_GET['action']) : 'list';

		if ($action === 'add') {
			$this->render_form();
			return;
		}

		if ($action === 'edit') {
			$this->render_form($this->get_current_game());
			return;
		}

		if ($action === 'roster') {
			$this->render_roster_page();
			return;
		}

		$this->render_list();
	}

	private function render_list(): void
	{
		global $wpdb;

		$games_table   = MAHL_Stats_DB::table('games');
		$seasons_table = MAHL_Stats_DB::table('seasons');
		$phases_table  = MAHL_Stats_DB::table('phases');
		$rounds_table  = MAHL_Stats_DB::table('rounds');
		$teams_table   = MAHL_Stats_DB::table('teams');

		$games = $wpdb->get_results(
			"SELECT g.*,
			        s.name AS season_name,
			        p.name AS phase_name,
			        r.name AS round_name,
			        ht.name AS home_team_name,
			        at.name AS away_team_name
			 FROM {$games_table} g
			 LEFT JOIN {$seasons_table} s ON s.id = g.season_id
			 LEFT JOIN {$phases_table} p ON p.id = g.phase_id
			 LEFT JOIN {$rounds_table} r ON r.id = g.round_id
			 LEFT JOIN {$teams_table} ht ON ht.id = g.home_team_id
			 LEFT JOIN {$teams_table} at ON at.id = g.away_team_id
			 ORDER BY g.game_date DESC, g.id DESC"
		);

		include MAHL_STATS_PLUGIN_DIR . 'admin/games/views/list.php';
	}

	private function render_form(?object $game = null): void
	{
		global $wpdb;

		$seasons_table = MAHL_Stats_DB::table('seasons');
		$phases_table  = MAHL_Stats_DB::table('phases');
		$rounds_table  = MAHL_Stats_DB::table('rounds');
		$teams_table   = MAHL_Stats_DB::table('teams');

		$seasons = $wpdb->get_results("SELECT * FROM {$seasons_table} ORDER BY id DESC");
		$phases  = $wpdb->get_results("SELECT * FROM {$phases_table} ORDER BY season_id DESC, sort_order ASC, id ASC");
		$rounds  = $wpdb->get_results("SELECT * FROM {$rounds_table} ORDER BY phase_id DESC, sort_order ASC, id ASC");
		$teams   = $wpdb->get_results("SELECT * FROM {$teams_table} ORDER BY name ASC");

		if ($game === null) {
			$game = (object) [
				'id'           => 0,
				'season_id'    => 0,
				'phase_id'     => 0,
				'round_id'     => 0,
				'home_team_id' => 0,
				'away_team_id' => 0,
				'venue'        => '',
				'game_date'    => '',
				'status'       => 'scheduled',
				'home_score'   => 0,
				'away_score'   => 0,
				'notes'        => '',
				'is_forfeit'   => 0,
				'slug'         => '',
			];
		}

		include MAHL_STATS_PLUGIN_DIR . 'admin/games/views/form.php';
	}

	private function render_roster_page(): void
	{
		global $wpdb;

		$game = $this->get_current_game();

		$teams_table         = MAHL_Stats_DB::table('teams');
		$registrations_table = MAHL_Stats_DB::table('registrations');
		$players_table       = MAHL_Stats_DB::table('players');
		$rosters_table       = MAHL_Stats_DB::table('game_rosters');
		$goals_table         = MAHL_Stats_DB::table('game_goals');
		$penalties_table     = MAHL_Stats_DB::table('game_penalties');
		$goalies_table       = MAHL_Stats_DB::table('game_goalies');
		$officials_table     = MAHL_Stats_DB::table('game_officials');
		$notes_table         = MAHL_Stats_DB::table('game_notes');

		$home_team = $wpdb->get_row(
			$wpdb->prepare("SELECT * FROM {$teams_table} WHERE id = %d LIMIT 1", (int) $game->home_team_id)
		);

		$away_team = $wpdb->get_row(
			$wpdb->prepare("SELECT * FROM {$teams_table} WHERE id = %d LIMIT 1", (int) $game->away_team_id)
		);

		$home_available = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT r.*, p.first_name, p.last_name
				 FROM {$registrations_table} r
				 LEFT JOIN {$players_table} p ON p.id = r.player_id
				 WHERE r.season_id = %d AND r.team_id = %d AND r.active = 1
				 ORDER BY p.last_name ASC, p.first_name ASC",
				(int) $game->season_id,
				(int) $game->home_team_id
			)
		);

		$away_available = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT r.*, p.first_name, p.last_name
				 FROM {$registrations_table} r
				 LEFT JOIN {$players_table} p ON p.id = r.player_id
				 WHERE r.season_id = %d AND r.team_id = %d AND r.active = 1
				 ORDER BY p.last_name ASC, p.first_name ASC",
				(int) $game->season_id,
				(int) $game->away_team_id
			)
		);

		$home_roster = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT gr.*, p.first_name, p.last_name
				 FROM {$rosters_table} gr
				 LEFT JOIN {$players_table} p ON p.id = gr.player_id
				 WHERE gr.game_id = %d AND gr.team_id = %d
				 ORDER BY p.last_name ASC, p.first_name ASC",
				(int) $game->id,
				(int) $game->home_team_id
			)
		);

		$away_roster = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT gr.*, p.first_name, p.last_name
				 FROM {$rosters_table} gr
				 LEFT JOIN {$players_table} p ON p.id = gr.player_id
				 WHERE gr.game_id = %d AND gr.team_id = %d
				 ORDER BY p.last_name ASC, p.first_name ASC",
				(int) $game->id,
				(int) $game->away_team_id
			)
		);

		$goals = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT g.*,
				        t.name AS team_name,
				        sp.first_name AS scorer_first_name,
				        sp.last_name AS scorer_last_name,
				        a1.first_name AS assist1_first_name,
				        a1.last_name AS assist1_last_name,
				        a2.first_name AS assist2_first_name,
				        a2.last_name AS assist2_last_name
				 FROM {$goals_table} g
				 LEFT JOIN {$teams_table} t ON t.id = g.team_id
				 LEFT JOIN {$players_table} sp ON sp.id = g.scorer_player_id
				 LEFT JOIN {$players_table} a1 ON a1.id = g.assist_1_player_id
				 LEFT JOIN {$players_table} a2 ON a2.id = g.assist_2_player_id
				 WHERE g.game_id = %d
				 ORDER BY g.part_number ASC, g.minute ASC, g.second ASC, g.id ASC",
				(int) $game->id
			)
		);

		$penalties = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p.*,
				        t.name AS team_name,
				        pl.first_name,
				        pl.last_name
				 FROM {$penalties_table} p
				 LEFT JOIN {$teams_table} t ON t.id = p.team_id
				 LEFT JOIN {$players_table} pl ON pl.id = p.player_id
				 WHERE p.game_id = %d
				 ORDER BY p.part_number ASC, p.minute ASC, p.second ASC, p.id ASC",
				(int) $game->id
			)
		);

		$goalies = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT gg.*,
				        t.name AS team_name,
				        pl.first_name,
				        pl.last_name
				 FROM {$goalies_table} gg
				 LEFT JOIN {$teams_table} t ON t.id = gg.team_id
				 LEFT JOIN {$players_table} pl ON pl.id = gg.player_id
				 WHERE gg.game_id = %d
				 ORDER BY gg.team_id ASC, gg.id ASC",
				(int) $game->id
			)
		);

		$officials = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT *
				 FROM {$officials_table}
				 WHERE game_id = %d
				 ORDER BY sort_order ASC, id ASC",
				(int) $game->id
			)
		);

		$notes = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT *
				 FROM {$notes_table}
				 WHERE game_id = %d
				 ORDER BY id DESC",
				(int) $game->id
			)
		);

		include MAHL_STATS_PLUGIN_DIR . 'admin/games/views/roster.php';
	}

	public function handle_save(): void
	{
		if (!current_user_can('manage_options')) {
			wp_die('Nemáš oprávnenie na túto akciu.');
		}

		check_admin_referer('mahl_save_game');

		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			wp_die('Neplatná požiadavka.');
		}

		global $wpdb;

		$table = MAHL_Stats_DB::table('games');

		$game_id       = isset($_POST['game_id']) ? absint($_POST['game_id']) : 0;
		$season_id     = isset($_POST['season_id']) ? absint($_POST['season_id']) : 0;
		$phase_id      = isset($_POST['phase_id']) ? absint($_POST['phase_id']) : 0;
		$round_id      = isset($_POST['round_id']) ? absint($_POST['round_id']) : 0;
		$home_team_id  = isset($_POST['home_team_id']) ? absint($_POST['home_team_id']) : 0;
		$away_team_id  = isset($_POST['away_team_id']) ? absint($_POST['away_team_id']) : 0;
		$venue         = isset($_POST['venue']) ? sanitize_text_field((string) $_POST['venue']) : '';
		$game_date     = isset($_POST['game_date']) ? sanitize_text_field((string) $_POST['game_date']) : '';
		$status        = isset($_POST['status']) ? sanitize_key((string) $_POST['status']) : 'scheduled';
		$home_score    = isset($_POST['home_score']) ? absint($_POST['home_score']) : 0;
		$away_score    = isset($_POST['away_score']) ? absint($_POST['away_score']) : 0;
		$notes         = isset($_POST['notes']) ? sanitize_textarea_field((string) $_POST['notes']) : '';
		$is_forfeit    = isset($_POST['is_forfeit']) ? 1 : 0;
		$slug          = isset($_POST['slug']) ? sanitize_title((string) $_POST['slug']) : '';

		if ($season_id <= 0 || $phase_id <= 0) {
			wp_die('Sezóna a fáza sú povinné.');
		}

		if ($home_team_id <= 0 || $away_team_id <= 0) {
			wp_die('Musíš vybrať oba tímy.');
		}

		if ($home_team_id === $away_team_id) {
			wp_die('Domáci a hosťujúci tím nemôžu byť rovnaké.');
		}

		if ($game_date === '') {
			wp_die('Dátum a čas zápasu sú povinné.');
		}

		$allowed_statuses = ['scheduled', 'finished', 'cancelled', 'forfeit'];
		if (!in_array($status, $allowed_statuses, true)) {
			$status = 'scheduled';
		}

		if ($is_forfeit === 1) {
			$status = 'forfeit';
		}

		if ($game_id > 0) {
			$current_game = $this->get_current_game_by_id($game_id);

			if (!$current_game) {
				wp_die('Zápas neexistuje.');
			}

			$home_team_id = (int) $current_game->home_team_id;
			$away_team_id = (int) $current_game->away_team_id;
		}

		if ($slug === '') {
			$slug = $this->generate_game_slug($home_team_id, $away_team_id, $game_date);
		}

		$existing_slug = $this->get_game_by_slug($slug);
		if ($existing_slug && (int) $existing_slug->id !== $game_id) {
			wp_die('Slug zápasu už existuje.');
		}

		$data = [
			'season_id'    => $season_id,
			'phase_id'     => $phase_id,
			'round_id'     => $round_id > 0 ? $round_id : null,
			'home_team_id' => $home_team_id,
			'away_team_id' => $away_team_id,
			'venue'        => $venue !== '' ? $venue : null,
			'game_date'    => $game_date,
			'status'       => $status,
			'home_score'   => $home_score,
			'away_score'   => $away_score,
			'notes'        => $notes !== '' ? $notes : null,
			'is_forfeit'   => $is_forfeit,
			'slug'         => $slug,
		];

		$formats = ['%d', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%d', '%s', '%d', '%s'];

		if ($game_id > 0) {
			$result = $wpdb->update($table, $data, ['id' => $game_id], $formats, ['%d']);

			if ($result === false) {
				wp_die('Zápas sa nepodarilo upraviť.');
			}

			wp_safe_redirect(admin_url('admin.php?page=mahl-games&message=updated'));
			exit;
		}

		$result = $wpdb->insert($table, $data, $formats);

		if ($result === false) {
			wp_die('Zápas sa nepodarilo uložiť.');
		}

		wp_safe_redirect(admin_url('admin.php?page=mahl-games&message=created'));
		exit;
	}

	public function handle_add_roster_player(): void
	{
		if (!current_user_can('manage_options')) {
			wp_die('Nemáš oprávnenie na túto akciu.');
		}

		check_admin_referer('mahl_add_game_roster');

		global $wpdb;

		$rosters_table       = MAHL_Stats_DB::table('game_rosters');
		$registrations_table = MAHL_Stats_DB::table('registrations');

		$game_id         = isset($_POST['game_id']) ? absint($_POST['game_id']) : 0;
		$team_id         = isset($_POST['team_id']) ? absint($_POST['team_id']) : 0;
		$registration_id = isset($_POST['registration_id']) ? absint($_POST['registration_id']) : 0;

		if ($game_id <= 0 || $team_id <= 0 || $registration_id <= 0) {
			wp_die('Chýbajú údaje pre súpisku.');
		}

		$registration = $wpdb->get_row(
			$wpdb->prepare("SELECT * FROM {$registrations_table} WHERE id = %d LIMIT 1", $registration_id)
		);

		if (!$registration) {
			wp_die('Registrácia neexistuje.');
		}

		$exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$rosters_table} WHERE game_id = %d AND team_id = %d AND player_id = %d LIMIT 1",
				$game_id,
				$team_id,
				(int) $registration->player_id
			)
		);

		if ($exists) {
			wp_safe_redirect(admin_url('admin.php?page=mahl-games&action=roster&game_id=' . $game_id . '&message=roster_exists'));
			exit;
		}

		$role = ((string) $registration->position === 'G') ? 'goalie' : 'player';

		$result = $wpdb->insert(
			$rosters_table,
			[
				'game_id'         => $game_id,
				'team_id'         => $team_id,
				'player_id'       => (int) $registration->player_id,
				'registration_id' => (int) $registration->id,
				'role'            => $role,
				'jersey_number'   => (string) $registration->jersey_number,
			],
			['%d', '%d', '%d', '%d', '%s', '%s']
		);

		if ($result === false) {
			wp_die('Hráča sa nepodarilo pridať do súpisky.');
		}

		wp_safe_redirect(admin_url('admin.php?page=mahl-games&action=roster&game_id=' . $game_id . '&message=roster_added'));
		exit;
	}

	public function handle_delete_roster_player(): void
	{
		if (!current_user_can('manage_options')) {
			wp_die('Nemáš oprávnenie na túto akciu.');
		}

		$roster_id = isset($_GET['roster_id']) ? absint($_GET['roster_id']) : 0;
		$game_id   = isset($_GET['game_id']) ? absint($_GET['game_id']) : 0;

		if ($roster_id <= 0 || $game_id <= 0) {
			wp_die('Chýba ID súpisky.');
		}

		check_admin_referer('mahl_delete_roster_' . $roster_id);

		global $wpdb;

		$rosters_table = MAHL_Stats_DB::table('game_rosters');

		$result = $wpdb->delete(
			$rosters_table,
			['id' => $roster_id],
			['%d']
		);

		if ($result === false) {
			wp_die('Hráča sa nepodarilo odstrániť zo súpisky.');
		}

		wp_safe_redirect(admin_url('admin.php?page=mahl-games&action=roster&game_id=' . $game_id . '&message=roster_deleted'));
		exit;
	}

	public function handle_add_goal(): void
	{
		if (!current_user_can('manage_options')) {
			wp_die('Nemáš oprávnenie na túto akciu.');
		}

		check_admin_referer('mahl_add_game_goal');

		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			wp_die('Neplatná požiadavka.');
		}

		global $wpdb;

		$goals_table   = MAHL_Stats_DB::table('game_goals');
		$rosters_table = MAHL_Stats_DB::table('game_rosters');

		$game_id              = isset($_POST['game_id']) ? absint($_POST['game_id']) : 0;
		$team_id              = isset($_POST['team_id']) ? absint($_POST['team_id']) : 0;
		$scorer_player_id     = isset($_POST['scorer_player_id']) ? absint($_POST['scorer_player_id']) : 0;
		$assist_1_player_id   = isset($_POST['assist_1_player_id']) ? absint($_POST['assist_1_player_id']) : 0;
		$assist_2_player_id   = isset($_POST['assist_2_player_id']) ? absint($_POST['assist_2_player_id']) : 0;
		$part_number          = isset($_POST['part_number']) ? absint($_POST['part_number']) : 1;
		$minute               = isset($_POST['minute']) ? absint($_POST['minute']) : 0;
		$second               = isset($_POST['second']) ? absint($_POST['second']) : 0;
		$strength             = isset($_POST['strength']) ? sanitize_key((string) $_POST['strength']) : 'even';
		$is_game_winning_goal = isset($_POST['is_game_winning_goal']) ? 1 : 0;

		if ($game_id <= 0 || $team_id <= 0 || $scorer_player_id <= 0) {
			wp_die('Tím a strelec gólu sú povinní.');
		}

		if ($minute < 0 || $second < 0 || $second > 59) {
			wp_die('Neplatný čas gólu.');
		}

		$allowed_strengths = ['even', 'powerplay', 'shorthanded', 'shootout'];
		if (!in_array($strength, $allowed_strengths, true)) {
			$strength = 'even';
		}

		$scorer_exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$rosters_table} WHERE game_id = %d AND team_id = %d AND player_id = %d LIMIT 1",
				$game_id,
				$team_id,
				$scorer_player_id
			)
		);

		if (!$scorer_exists) {
			wp_die('Strelec nie je v súpiske daného tímu.');
		}

		if ($assist_1_player_id > 0) {
			$assist1_exists = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT id FROM {$rosters_table} WHERE game_id = %d AND team_id = %d AND player_id = %d LIMIT 1",
					$game_id,
					$team_id,
					$assist_1_player_id
				)
			);

			if (!$assist1_exists) {
				wp_die('Asistencia 1 nie je v súpiske daného tímu.');
			}
		} else {
			$assist_1_player_id = null;
		}

		if ($assist_2_player_id > 0) {
			$assist2_exists = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT id FROM {$rosters_table} WHERE game_id = %d AND team_id = %d AND player_id = %d LIMIT 1",
					$game_id,
					$team_id,
					$assist_2_player_id
				)
			);

			if (!$assist2_exists) {
				wp_die('Asistencia 2 nie je v súpiske daného tímu.');
			}
		} else {
			$assist_2_player_id = null;
		}

		if ($assist_1_player_id !== null && $assist_1_player_id === $scorer_player_id) {
			wp_die('Strelec nemôže byť zároveň asistencia 1.');
		}

		if ($assist_2_player_id !== null && $assist_2_player_id === $scorer_player_id) {
			wp_die('Strelec nemôže byť zároveň asistencia 2.');
		}

		if ($assist_1_player_id !== null && $assist_2_player_id !== null && $assist_1_player_id === $assist_2_player_id) {
			wp_die('Asistencia 1 a asistencia 2 nemôžu byť rovnaký hráč.');
		}

		$result = $wpdb->insert(
			$goals_table,
			[
				'game_id'              => $game_id,
				'team_id'              => $team_id,
				'scorer_player_id'     => $scorer_player_id,
				'assist_1_player_id'   => $assist_1_player_id,
				'assist_2_player_id'   => $assist_2_player_id,
				'part_number'          => $part_number > 0 ? $part_number : 1,
				'minute'               => $minute,
				'second'               => $second,
				'strength'             => $strength,
				'is_game_winning_goal' => $is_game_winning_goal,
			],
			['%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%s', '%d']
		);

		if ($result === false) {
			wp_die('Gól sa nepodarilo uložiť.');
		}

		wp_safe_redirect(admin_url('admin.php?page=mahl-games&action=roster&game_id=' . $game_id . '&message=goal_added'));
		exit;
	}

	public function handle_delete_goal(): void
	{
		if (!current_user_can('manage_options')) {
			wp_die('Nemáš oprávnenie na túto akciu.');
		}

		$goal_id = isset($_GET['goal_id']) ? absint($_GET['goal_id']) : 0;
		$game_id = isset($_GET['game_id']) ? absint($_GET['game_id']) : 0;

		if ($goal_id <= 0 || $game_id <= 0) {
			wp_die('Chýba ID gólu.');
		}

		check_admin_referer('mahl_delete_goal_' . $goal_id);

		global $wpdb;

		$goals_table = MAHL_Stats_DB::table('game_goals');

		$result = $wpdb->delete(
			$goals_table,
			['id' => $goal_id],
			['%d']
		);

		if ($result === false) {
			wp_die('Gól sa nepodarilo odstrániť.');
		}

		wp_safe_redirect(admin_url('admin.php?page=mahl-games&action=roster&game_id=' . $game_id . '&message=goal_deleted'));
		exit;
	}

	public function handle_add_penalty(): void
	{
		if (!current_user_can('manage_options')) {
			wp_die('Nemáš oprávnenie na túto akciu.');
		}

		check_admin_referer('mahl_add_game_penalty');

		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			wp_die('Neplatná požiadavka.');
		}

		global $wpdb;

		$penalties_table = MAHL_Stats_DB::table('game_penalties');
		$rosters_table   = MAHL_Stats_DB::table('game_rosters');

		$game_id         = isset($_POST['game_id']) ? absint($_POST['game_id']) : 0;
		$team_id         = isset($_POST['team_id']) ? absint($_POST['team_id']) : 0;
		$player_id       = isset($_POST['player_id']) ? absint($_POST['player_id']) : 0;
		$part_number     = isset($_POST['part_number']) ? absint($_POST['part_number']) : 1;
		$minute          = isset($_POST['minute']) ? absint($_POST['minute']) : 0;
		$second          = isset($_POST['second']) ? absint($_POST['second']) : 0;
		$penalty_minutes = isset($_POST['penalty_minutes']) ? absint($_POST['penalty_minutes']) : 2;
		$reason          = isset($_POST['reason']) ? sanitize_text_field((string) $_POST['reason']) : '';

		if ($game_id <= 0 || $team_id <= 0 || $player_id <= 0) {
			wp_die('Tím a hráč trestu sú povinní.');
		}

		if ($minute < 0 || $second < 0 || $second > 59) {
			wp_die('Neplatný čas trestu.');
		}

		if ($penalty_minutes <= 0) {
			wp_die('Počet trestných minút musí byť väčší ako 0.');
		}

		$player_exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$rosters_table} WHERE game_id = %d AND team_id = %d AND player_id = %d LIMIT 1",
				$game_id,
				$team_id,
				$player_id
			)
		);

		if (!$player_exists) {
			wp_die('Hráč trestu nie je v súpiske daného tímu.');
		}

		$result = $wpdb->insert(
			$penalties_table,
			[
				'game_id'         => $game_id,
				'team_id'         => $team_id,
				'player_id'       => $player_id,
				'part_number'     => $part_number > 0 ? $part_number : 1,
				'minute'          => $minute,
				'second'          => $second,
				'penalty_minutes' => $penalty_minutes,
				'reason'          => $reason !== '' ? $reason : null,
			],
			['%d', '%d', '%d', '%d', '%d', '%d', '%d', '%s']
		);

		if ($result === false) {
			wp_die('Trest sa nepodarilo uložiť.');
		}

		wp_safe_redirect(admin_url('admin.php?page=mahl-games&action=roster&game_id=' . $game_id . '&message=penalty_added'));
		exit;
	}

	public function handle_delete_penalty(): void
	{
		if (!current_user_can('manage_options')) {
			wp_die('Nemáš oprávnenie na túto akciu.');
		}

		$penalty_id = isset($_GET['penalty_id']) ? absint($_GET['penalty_id']) : 0;
		$game_id    = isset($_GET['game_id']) ? absint($_GET['game_id']) : 0;

		if ($penalty_id <= 0 || $game_id <= 0) {
			wp_die('Chýba ID trestu.');
		}

		check_admin_referer('mahl_delete_penalty_' . $penalty_id);

		global $wpdb;

		$penalties_table = MAHL_Stats_DB::table('game_penalties');

		$result = $wpdb->delete(
			$penalties_table,
			['id' => $penalty_id],
			['%d']
		);

		if ($result === false) {
			wp_die('Trest sa nepodarilo odstrániť.');
		}

		wp_safe_redirect(admin_url('admin.php?page=mahl-games&action=roster&game_id=' . $game_id . '&message=penalty_deleted'));
		exit;
	}

	public function handle_add_goalie(): void
	{
		if (!current_user_can('manage_options')) {
			wp_die('Nemáš oprávnenie na túto akciu.');
		}

		check_admin_referer('mahl_add_game_goalie');

		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			wp_die('Neplatná požiadavka.');
		}

		global $wpdb;

		$goalies_table = MAHL_Stats_DB::table('game_goalies');
		$rosters_table = MAHL_Stats_DB::table('game_rosters');

		$game_id        = isset($_POST['game_id']) ? absint($_POST['game_id']) : 0;
		$team_id        = isset($_POST['team_id']) ? absint($_POST['team_id']) : 0;
		$player_id      = isset($_POST['player_id']) ? absint($_POST['player_id']) : 0;
		$minutes_played = isset($_POST['minutes_played']) ? absint($_POST['minutes_played']) : 0;
		$goals_allowed  = isset($_POST['goals_allowed']) ? absint($_POST['goals_allowed']) : 0;
		$saves          = isset($_POST['saves']) ? absint($_POST['saves']) : 0;
		$shots_against  = isset($_POST['shots_against']) ? absint($_POST['shots_against']) : 0;
		$shutout        = isset($_POST['shutout']) ? 1 : 0;

		if ($game_id <= 0 || $team_id <= 0 || $player_id <= 0) {
			wp_die('Tím a brankár sú povinní.');
		}

		$goalie_exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$rosters_table} WHERE game_id = %d AND team_id = %d AND player_id = %d LIMIT 1",
				$game_id,
				$team_id,
				$player_id
			)
		);

		if (!$goalie_exists) {
			wp_die('Brankár nie je v súpiske daného tímu.');
		}

		$existing_record = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$goalies_table} WHERE game_id = %d AND team_id = %d AND player_id = %d LIMIT 1",
				$game_id,
				$team_id,
				$player_id
			)
		);

		if ($existing_record) {
			wp_die('Tento brankár už má v zápase záznam.');
		}

		$result = $wpdb->insert(
			$goalies_table,
			[
				'game_id'        => $game_id,
				'team_id'        => $team_id,
				'player_id'      => $player_id,
				'minutes_played' => $minutes_played > 0 ? $minutes_played : null,
				'goals_allowed'  => $goals_allowed,
				'saves'          => $saves > 0 ? $saves : null,
				'shots_against'  => $shots_against > 0 ? $shots_against : null,
				'shutout'        => $shutout,
			],
			['%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d']
		);

		if ($result === false) {
			wp_die('Brankára sa nepodarilo uložiť.');
		}

		wp_safe_redirect(admin_url('admin.php?page=mahl-games&action=roster&game_id=' . $game_id . '&message=goalie_added'));
		exit;
	}

	public function handle_delete_goalie(): void
	{
		if (!current_user_can('manage_options')) {
			wp_die('Nemáš oprávnenie na túto akciu.');
		}

		$goalie_id = isset($_GET['goalie_id']) ? absint($_GET['goalie_id']) : 0;
		$game_id   = isset($_GET['game_id']) ? absint($_GET['game_id']) : 0;

		if ($goalie_id <= 0 || $game_id <= 0) {
			wp_die('Chýba ID brankára.');
		}

		check_admin_referer('mahl_delete_goalie_' . $goalie_id);

		global $wpdb;

		$goalies_table = MAHL_Stats_DB::table('game_goalies');

		$result = $wpdb->delete(
			$goalies_table,
			['id' => $goalie_id],
			['%d']
		);

		if ($result === false) {
			wp_die('Brankára sa nepodarilo odstrániť.');
		}

		wp_safe_redirect(admin_url('admin.php?page=mahl-games&action=roster&game_id=' . $game_id . '&message=goalie_deleted'));
		exit;
	}

	public function handle_add_official(): void
	{
		if (!current_user_can('manage_options')) {
			wp_die('Nemáš oprávnenie na túto akciu.');
		}

		check_admin_referer('mahl_add_game_official');

		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			wp_die('Neplatná požiadavka.');
		}

		global $wpdb;

		$officials_table = MAHL_Stats_DB::table('game_officials');

		$game_id        = isset($_POST['game_id']) ? absint($_POST['game_id']) : 0;
		$official_name  = isset($_POST['official_name']) ? sanitize_text_field((string) $_POST['official_name']) : '';
		$role           = isset($_POST['role']) ? sanitize_key((string) $_POST['role']) : 'referee';
		$sort_order     = isset($_POST['sort_order']) ? absint($_POST['sort_order']) : 0;

		if ($game_id <= 0 || $official_name === '') {
			wp_die('Meno rozhodcu je povinné.');
		}

		$allowed_roles = ['referee', 'linesman', 'timekeeper', 'other'];
		if (!in_array($role, $allowed_roles, true)) {
			$role = 'referee';
		}

		$result = $wpdb->insert(
			$officials_table,
			[
				'game_id'       => $game_id,
				'official_name' => $official_name,
				'role'          => $role,
				'sort_order'    => $sort_order,
			],
			['%d', '%s', '%s', '%d']
		);

		if ($result === false) {
			wp_die('Rozhodcu sa nepodarilo uložiť.');
		}

		wp_safe_redirect(admin_url('admin.php?page=mahl-games&action=roster&game_id=' . $game_id . '&message=official_added'));
		exit;
	}

	public function handle_delete_official(): void
	{
		if (!current_user_can('manage_options')) {
			wp_die('Nemáš oprávnenie na túto akciu.');
		}

		$official_id = isset($_GET['official_id']) ? absint($_GET['official_id']) : 0;
		$game_id     = isset($_GET['game_id']) ? absint($_GET['game_id']) : 0;

		if ($official_id <= 0 || $game_id <= 0) {
			wp_die('Chýba ID rozhodcu.');
		}

		check_admin_referer('mahl_delete_official_' . $official_id);

		global $wpdb;

		$officials_table = MAHL_Stats_DB::table('game_officials');

		$result = $wpdb->delete(
			$officials_table,
			['id' => $official_id],
			['%d']
		);

		if ($result === false) {
			wp_die('Rozhodcu sa nepodarilo odstrániť.');
		}

		wp_safe_redirect(admin_url('admin.php?page=mahl-games&action=roster&game_id=' . $game_id . '&message=official_deleted'));
		exit;
	}

	public function handle_add_note(): void
	{
		if (!current_user_can('manage_options')) {
			wp_die('Nemáš oprávnenie na túto akciu.');
		}

		check_admin_referer('mahl_add_game_note');

		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			wp_die('Neplatná požiadavka.');
		}

		global $wpdb;

		$notes_table = MAHL_Stats_DB::table('game_notes');

		$game_id   = isset($_POST['game_id']) ? absint($_POST['game_id']) : 0;
		$note_type = isset($_POST['note_type']) ? sanitize_key((string) $_POST['note_type']) : 'general';
		$content   = isset($_POST['content']) ? sanitize_textarea_field((string) $_POST['content']) : '';

		if ($game_id <= 0 || $content === '') {
			wp_die('Obsah poznámky je povinný.');
		}

		$allowed_types = ['general', 'discipline', 'admin', 'stream'];
		if (!in_array($note_type, $allowed_types, true)) {
			$note_type = 'general';
		}

		$result = $wpdb->insert(
			$notes_table,
			[
				'game_id'   => $game_id,
				'note_type' => $note_type,
				'content'   => $content,
			],
			['%d', '%s', '%s']
		);

		if ($result === false) {
			wp_die('Poznámku sa nepodarilo uložiť.');
		}

		wp_safe_redirect(admin_url('admin.php?page=mahl-games&action=roster&game_id=' . $game_id . '&message=note_added'));
		exit;
	}

	public function handle_delete_note(): void
	{
		if (!current_user_can('manage_options')) {
			wp_die('Nemáš oprávnenie na túto akciu.');
		}

		$note_id = isset($_GET['note_id']) ? absint($_GET['note_id']) : 0;
		$game_id = isset($_GET['game_id']) ? absint($_GET['game_id']) : 0;

		if ($note_id <= 0 || $game_id <= 0) {
			wp_die('Chýba ID poznámky.');
		}

		check_admin_referer('mahl_delete_note_' . $note_id);

		global $wpdb;

		$notes_table = MAHL_Stats_DB::table('game_notes');

		$result = $wpdb->delete(
			$notes_table,
			['id' => $note_id],
			['%d']
		);

		if ($result === false) {
			wp_die('Poznámku sa nepodarilo odstrániť.');
		}

		wp_safe_redirect(admin_url('admin.php?page=mahl-games&action=roster&game_id=' . $game_id . '&message=note_deleted'));
		exit;
	}

	private function get_current_game(): ?object
	{
		$game_id = isset($_GET['game_id']) ? absint($_GET['game_id']) : 0;

		if ($game_id <= 0) {
			wp_die('Chýba ID zápasu.');
		}

		$game = $this->get_current_game_by_id($game_id);

		if (!$game) {
			wp_die('Zápas neexistuje.');
		}

		return $game;
	}

	private function get_current_game_by_id(int $game_id): ?object
	{
		global $wpdb;

		$table = MAHL_Stats_DB::table('games');

		$game = $wpdb->get_row(
			$wpdb->prepare("SELECT * FROM {$table} WHERE id = %d LIMIT 1", $game_id)
		);

		return $game ?: null;
	}

	private function get_game_by_slug(string $slug): ?object
	{
		global $wpdb;

		$table = MAHL_Stats_DB::table('games');

		$game = $wpdb->get_row(
			$wpdb->prepare("SELECT * FROM {$table} WHERE slug = %s LIMIT 1", $slug)
		);

		return $game ?: null;
	}

	private function generate_game_slug(int $home_team_id, int $away_team_id, string $game_date): string
	{
		global $wpdb;

		$teams_table = MAHL_Stats_DB::table('teams');

		$home_team_slug = (string) $wpdb->get_var(
			$wpdb->prepare("SELECT slug FROM {$teams_table} WHERE id = %d LIMIT 1", $home_team_id)
		);

		$away_team_slug = (string) $wpdb->get_var(
			$wpdb->prepare("SELECT slug FROM {$teams_table} WHERE id = %d LIMIT 1", $away_team_id)
		);

		$date_part = substr($game_date, 0, 10);

		return sanitize_title($home_team_slug . '-vs-' . $away_team_slug . '-' . $date_part);
	}
}