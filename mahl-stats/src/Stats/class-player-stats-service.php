<?php
/**
 * Player stats calculation service.
 *
 * @package MAHL_Stats
 */

if (!defined('ABSPATH')) {
	exit;
}

class MAHL_Stats_Player_Stats_Service
{
	/**
	 * Calculate player stats for season and optional phase.
	 *
	 * @param int      $season_id Season ID.
	 * @param int|null $phase_id Optional phase ID.
	 * @return array<int, array<string, mixed>>
	 */
	public function calculate(int $season_id, ?int $phase_id = null): array
	{
		if ($season_id <= 0) {
			return [];
		}

		global $wpdb;

		$games_table      = MAHL_Stats_DB::table('games');
		$rosters_table    = MAHL_Stats_DB::table('game_rosters');
		$goals_table      = MAHL_Stats_DB::table('game_goals');
		$penalties_table  = MAHL_Stats_DB::table('game_penalties');
		$players_table    = MAHL_Stats_DB::table('players');
		$teams_table      = MAHL_Stats_DB::table('teams');

		$where  = "g.season_id = %d AND g.status IN ('finished', 'forfeit')";
		$params = [$season_id];

		if ($phase_id !== null && $phase_id > 0) {
			$where .= " AND g.phase_id = %d";
			$params[] = $phase_id;
		}

		$games_sql = "
			SELECT g.id
			FROM {$games_table} g
			WHERE {$where}
		";

		$game_ids = $wpdb->get_col(
			$wpdb->prepare($games_sql, ...$params)
		);

		if (empty($game_ids)) {
			return [];
		}

		$game_ids = array_map('absint', $game_ids);
		$placeholders = implode(',', array_fill(0, count($game_ids), '%d'));

		$stats = [];

		$roster_sql = "
			SELECT 
				gr.player_id,
				gr.team_id,
				p.first_name,
				p.last_name,
				p.slug,
				t.name AS team_name,
				COUNT(DISTINCT gr.game_id) AS games_played
			FROM {$rosters_table} gr
			LEFT JOIN {$players_table} p ON p.id = gr.player_id
			LEFT JOIN {$teams_table} t ON t.id = gr.team_id
			WHERE gr.game_id IN ({$placeholders})
			GROUP BY gr.player_id, gr.team_id, p.first_name, p.last_name, p.slug, t.name
		";

		$roster_rows = $wpdb->get_results(
			$wpdb->prepare($roster_sql, ...$game_ids)
		);

		foreach ($roster_rows as $row) {
			$key = $this->make_key((int) $row->player_id, (int) $row->team_id);

			$stats[$key] = [
				'player_id'    => (int) $row->player_id,
				'team_id'      => (int) $row->team_id,
				'player_name'  => trim((string) $row->first_name . ' ' . (string) $row->last_name),
				'player_slug'  => (string) $row->slug,
				'team_name'    => (string) $row->team_name,
				'games'        => (int) $row->games_played,
				'goals'        => 0,
				'assists'      => 0,
				'points'       => 0,
				'penalty_minutes' => 0,
			];
		}

		$goals_sql = "
			SELECT 
				gg.team_id,
				gg.scorer_player_id,
				gg.assist_1_player_id,
				gg.assist_2_player_id
			FROM {$goals_table} gg
			WHERE gg.game_id IN ({$placeholders})
		";

		$goal_rows = $wpdb->get_results(
			$wpdb->prepare($goals_sql, ...$game_ids)
		);

		foreach ($goal_rows as $goal) {
			$team_id = (int) $goal->team_id;

			$scorer_key = $this->make_key((int) $goal->scorer_player_id, $team_id);
			if (isset($stats[$scorer_key])) {
				$stats[$scorer_key]['goals']++;
			}

			if (!empty($goal->assist_1_player_id)) {
				$assist1_key = $this->make_key((int) $goal->assist_1_player_id, $team_id);
				if (isset($stats[$assist1_key])) {
					$stats[$assist1_key]['assists']++;
				}
			}

			if (!empty($goal->assist_2_player_id)) {
				$assist2_key = $this->make_key((int) $goal->assist_2_player_id, $team_id);
				if (isset($stats[$assist2_key])) {
					$stats[$assist2_key]['assists']++;
				}
			}
		}

		$penalties_sql = "
			SELECT 
				gp.team_id,
				gp.player_id,
				gp.penalty_minutes
			FROM {$penalties_table} gp
			WHERE gp.game_id IN ({$placeholders})
		";

		$penalty_rows = $wpdb->get_results(
			$wpdb->prepare($penalties_sql, ...$game_ids)
		);

		foreach ($penalty_rows as $penalty) {
			$key = $this->make_key((int) $penalty->player_id, (int) $penalty->team_id);
			if (isset($stats[$key])) {
				$stats[$key]['penalty_minutes'] += (int) $penalty->penalty_minutes;
			}
		}

		foreach ($stats as &$row) {
			$row['points'] = (int) $row['goals'] + (int) $row['assists'];
		}
		unset($row);

		$rows = array_values($stats);

		usort($rows, function (array $a, array $b): int {
			if ((int) $a['points'] !== (int) $b['points']) {
				return (int) $b['points'] <=> (int) $a['points'];
			}

			if ((int) $a['goals'] !== (int) $b['goals']) {
				return (int) $b['goals'] <=> (int) $a['goals'];
			}

			if ((int) $a['assists'] !== (int) $b['assists']) {
				return (int) $b['assists'] <=> (int) $a['assists'];
			}

			return strcasecmp((string) $a['player_name'], (string) $b['player_name']);
		});

		$position = 1;
		foreach ($rows as &$row) {
			$row['position'] = $position;
			$position++;
		}
		unset($row);

		return $rows;
	}

	/**
	 * Build stable key for player-team combination.
	 *
	 * @param int $player_id Player ID.
	 * @param int $team_id Team ID.
	 * @return string
	 */
	private function make_key(int $player_id, int $team_id): string
	{
		return $player_id . ':' . $team_id;
	}
}