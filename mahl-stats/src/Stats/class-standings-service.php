<?php
/**
 * Standings calculation service.
 *
 * @package MAHL_Stats
 */

if (!defined('ABSPATH')) {
	exit;
}

class MAHL_Stats_Standings_Service
{
	/**
	 * Calculate standings for a season and optional phase.
	 *
	 * @param int      $season_id Season ID.
	 * @param int|null $phase_id  Optional phase ID.
	 * @return array<int, array<string, mixed>>
	 */
	public function calculate(int $season_id, ?int $phase_id = null): array
	{
		if ($season_id <= 0) {
			return [];
		}

		global $wpdb;

		$games_table = MAHL_Stats_DB::table('games');
		$teams_table = MAHL_Stats_DB::table('teams');

		$where   = "g.season_id = %d AND g.status IN ('finished', 'forfeit')";
		$params  = [$season_id];

		if ($phase_id !== null && $phase_id > 0) {
			$where  .= ' AND g.phase_id = %d';
			$params[] = $phase_id;
		}

		$sql = "
			SELECT 
				g.id,
				g.home_team_id,
				g.away_team_id,
				g.home_score,
				g.away_score,
				g.status,
				ht.name AS home_team_name,
				at.name AS away_team_name
			FROM {$games_table} g
			LEFT JOIN {$teams_table} ht ON ht.id = g.home_team_id
			LEFT JOIN {$teams_table} at ON at.id = g.away_team_id
			WHERE {$where}
			ORDER BY g.game_date ASC, g.id ASC
		";

		$games = $wpdb->get_results(
			$wpdb->prepare($sql, ...$params)
		);

		if (empty($games)) {
			return [];
		}

		$standings = [];
		$team_names = [];

		foreach ($games as $game) {
			$home_id = (int) $game->home_team_id;
			$away_id = (int) $game->away_team_id;

			$team_names[$home_id] = (string) $game->home_team_name;
			$team_names[$away_id] = (string) $game->away_team_name;

			if (!isset($standings[$home_id])) {
				$standings[$home_id] = $this->create_empty_row($home_id, (string) $game->home_team_name);
			}

			if (!isset($standings[$away_id])) {
				$standings[$away_id] = $this->create_empty_row($away_id, (string) $game->away_team_name);
			}

			$home_score = (int) $game->home_score;
			$away_score = (int) $game->away_score;

			$standings[$home_id]['played']++;
			$standings[$away_id]['played']++;

			$standings[$home_id]['gf'] += $home_score;
			$standings[$home_id]['ga'] += $away_score;
			$standings[$away_id]['gf'] += $away_score;
			$standings[$away_id]['ga'] += $home_score;

			$standings[$home_id]['gd'] = $standings[$home_id]['gf'] - $standings[$home_id]['ga'];
			$standings[$away_id]['gd'] = $standings[$away_id]['gf'] - $standings[$away_id]['ga'];

			if ($home_score > $away_score) {
				$standings[$home_id]['wins']++;
				$standings[$home_id]['points'] += 2;
				$standings[$away_id]['losses']++;
			} elseif ($home_score < $away_score) {
				$standings[$away_id]['wins']++;
				$standings[$away_id]['points'] += 2;
				$standings[$home_id]['losses']++;
			} else {
				$standings[$home_id]['draws']++;
				$standings[$away_id]['draws']++;
				$standings[$home_id]['points']++;
				$standings[$away_id]['points']++;
			}
		}

		$rows = array_values($standings);

		$rows = $this->sort_rows_with_head_to_head($rows, $games);

		$position = 1;
		foreach ($rows as &$row) {
			$row['position'] = $position;
			$position++;
		}
		unset($row);

		return $rows;
	}

	/**
	 * Create default standings row.
	 *
	 * @param int    $team_id Team ID.
	 * @param string $team_name Team name.
	 * @return array<string, mixed>
	 */
	private function create_empty_row(int $team_id, string $team_name): array
	{
		return [
			'position' => 0,
			'team_id'  => $team_id,
			'team_name'=> $team_name,
			'played'   => 0,
			'wins'     => 0,
			'draws'    => 0,
			'losses'   => 0,
			'gf'       => 0,
			'ga'       => 0,
			'gd'       => 0,
			'points'   => 0,
		];
	}

	/**
	 * Sort rows using points, head-to-head, goal difference, goals for.
	 *
	 * @param array<int, array<string, mixed>> $rows  Standings rows.
	 * @param array<int, object>               $games Played games.
	 * @return array<int, array<string, mixed>>
	 */
	private function sort_rows_with_head_to_head(array $rows, array $games): array
	{
		usort($rows, function (array $a, array $b): int {
			if ((int) $a['points'] !== (int) $b['points']) {
				return (int) $b['points'] <=> (int) $a['points'];
			}

			return 0;
		});

		$grouped = [];
		foreach ($rows as $row) {
			$key = (string) $row['points'];
			if (!isset($grouped[$key])) {
				$grouped[$key] = [];
			}
			$grouped[$key][] = $row;
		}

		$final_rows = [];

		foreach ($grouped as $group_rows) {
			if (count($group_rows) === 1) {
				$final_rows[] = $group_rows[0];
				continue;
			}

			$mini = $this->build_head_to_head_table($group_rows, $games);

			usort($group_rows, function (array $a, array $b) use ($mini): int {
				$a_id = (int) $a['team_id'];
				$b_id = (int) $b['team_id'];

				$a_h2h_points = $mini[$a_id]['points'] ?? 0;
				$b_h2h_points = $mini[$b_id]['points'] ?? 0;
				if ($a_h2h_points !== $b_h2h_points) {
					return $b_h2h_points <=> $a_h2h_points;
				}

				$a_h2h_gd = $mini[$a_id]['gd'] ?? 0;
				$b_h2h_gd = $mini[$b_id]['gd'] ?? 0;
				if ($a_h2h_gd !== $b_h2h_gd) {
					return $b_h2h_gd <=> $a_h2h_gd;
				}

				$a_h2h_gf = $mini[$a_id]['gf'] ?? 0;
				$b_h2h_gf = $mini[$b_id]['gf'] ?? 0;
				if ($a_h2h_gf !== $b_h2h_gf) {
					return $b_h2h_gf <=> $a_h2h_gf;
				}

				if ((int) $a['gd'] !== (int) $b['gd']) {
					return (int) $b['gd'] <=> (int) $a['gd'];
				}

				if ((int) $a['gf'] !== (int) $b['gf']) {
					return (int) $b['gf'] <=> (int) $a['gf'];
				}

				return strcasecmp((string) $a['team_name'], (string) $b['team_name']);
			});

			foreach ($group_rows as $row) {
				$final_rows[] = $row;
			}
		}

		return $final_rows;
	}

	/**
	 * Build mini-table for tied teams from their mutual games.
	 *
	 * @param array<int, array<string, mixed>> $group_rows Tied rows.
	 * @param array<int, object>               $games All games.
	 * @return array<int, array<string, int>>
	 */
	private function build_head_to_head_table(array $group_rows, array $games): array
	{
		$team_ids = [];
		foreach ($group_rows as $row) {
			$team_ids[(int) $row['team_id']] = true;
		}

		$mini = [];
		foreach (array_keys($team_ids) as $team_id) {
			$mini[$team_id] = [
				'points' => 0,
				'gf'     => 0,
				'ga'     => 0,
				'gd'     => 0,
			];
		}

		foreach ($games as $game) {
			$home_id = (int) $game->home_team_id;
			$away_id = (int) $game->away_team_id;

			if (!isset($team_ids[$home_id]) || !isset($team_ids[$away_id])) {
				continue;
			}

			$home_score = (int) $game->home_score;
			$away_score = (int) $game->away_score;

			$mini[$home_id]['gf'] += $home_score;
			$mini[$home_id]['ga'] += $away_score;
			$mini[$away_id]['gf'] += $away_score;
			$mini[$away_id]['ga'] += $home_score;

			if ($home_score > $away_score) {
				$mini[$home_id]['points'] += 2;
			} elseif ($home_score < $away_score) {
				$mini[$away_id]['points'] += 2;
			} else {
				$mini[$home_id]['points'] += 1;
				$mini[$away_id]['points'] += 1;
			}
		}

		foreach ($mini as $team_id => $stats) {
			$mini[$team_id]['gd'] = $stats['gf'] - $stats['ga'];
		}

		return $mini;
	}
}