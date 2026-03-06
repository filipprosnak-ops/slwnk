<?php
/**
 * Standings service.
 *
 * @package MAHLManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Calculate standings from finalized game data.
 */
class MAHL_Standings_Service extends MAHL_Base_Service {

	/**
	 * Completed game status value.
	 *
	 * @var string
	 */
	const COMPLETED_GAME_STATUS = 'final';

	/**
	 * Return normalized standings data for a competition context.
	 *
	 * Returned structure:
	 *
	 * array(
	 *     'context' => array(
	 *         'season_id'   => (int),
	 *         'phase_id'    => (int),
	 *         'group_key'   => (string),
	 *         'point_rules' => array(
	 *             'win_points'  => (int),
	 *             'draw_points' => (int),
	 *             'loss_points' => (int),
	 *         ),
	 *         'game_count'   => (int),
	 *     ),
	 *     'rows' => array(
	 *         array(
	 *             'position'        => (int),
	 *             'team_id'         => (int),
	 *             'team_name'       => (string),
	 *             'games_played'    => (int),
	 *             'wins'            => (int),
	 *             'draws'           => (int),
	 *             'losses'          => (int),
	 *             'goals_for'       => (int),
	 *             'goals_against'   => (int),
	 *             'goal_difference' => (int),
	 *             'points'          => (int),
	 *         ),
	 *     ),
	 * )
	 *
	 * @param array $args {
	 *     Optional. Standings query arguments.
	 *
	 *     @type int    $season_id Required season post ID.
	 *     @type int    $phase_id  Optional phase post ID.
	 *     @type string $group_key Optional normalized group or bracket key.
	 * }
	 * @return array
	 */
	public function get_standings( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'season_id' => 0,
				'phase_id'  => 0,
				'group_key' => '',
			)
		);

		$season_id = absint( $args['season_id'] );
		$phase_id  = absint( $args['phase_id'] );
		$group_key = sanitize_key( $args['group_key'] );
		$response  = $this->get_empty_standings_response( $season_id, $phase_id, $group_key );

		if ( empty( $season_id ) ) {
			return $response;
		}

		$point_rules = $this->get_point_rules();
		$games       = $this->get_completed_games( $season_id, $phase_id, $group_key );
		$standings   = array();

		foreach ( $games as $game_id ) {
			$game_data = $this->get_game_data( $game_id );

			if ( ! $this->is_valid_game_for_standings( $game_data ) ) {
				continue;
			}

			$this->ensure_team_row( $standings, $game_data['home_team_id'] );
			$this->ensure_team_row( $standings, $game_data['away_team_id'] );

			$this->apply_game_statistics( $standings[ $game_data['home_team_id'] ], $standings[ $game_data['away_team_id'] ], $game_data );
			$this->apply_result_points( $standings[ $game_data['home_team_id'] ], $standings[ $game_data['away_team_id'] ], $game_data, $point_rules );
		}

		$response['context']['point_rules'] = $point_rules;
		$response['context']['game_count']  = count( $games );
		$response['rows']                   = $this->add_positions_to_rows( $this->sort_standings( $standings ) );

		return $response;
	}

	/**
	 * Calculate standings rows for a competition context.
	 *
	 * This remains as a convenience wrapper for callers that only need rows.
	 *
	 * @param int    $season_id Season post ID.
	 * @param int    $phase_id Optional phase post ID.
	 * @param string $group_key Optional normalized group or bracket key.
	 * @return array
	 */
	public function calculate_standings( $season_id, $phase_id = 0, $group_key = '' ) {
		$standings = $this->get_standings(
			array(
				'season_id' => $season_id,
				'phase_id'  => $phase_id,
				'group_key' => $group_key,
			)
		);

		return $standings['rows'];
	}

	/**
	 * Return the configured standings point rules.
	 *
	 * @return array
	 */
	public function get_point_rules() {
		$rules = MAHL_Settings_Service::get_standings_rules();

		return array(
			'win_points'  => absint( $rules['win_points'] ),
			'draw_points' => absint( $rules['draw_points'] ),
			'loss_points' => absint( $rules['loss_points'] ),
		);
	}

	/**
	 * Return completed games for a standings context.
	 *
	 * @param int    $season_id Season post ID.
	 * @param int    $phase_id Optional phase post ID.
	 * @param string $group_key Optional normalized group key.
	 * @return array
	 */
	protected function get_completed_games( $season_id, $phase_id, $group_key ) {
		$meta_query = array(
			array(
				'key'     => '_mahl_season_id',
				'value'   => $season_id,
				'compare' => '=',
				'type'    => 'NUMERIC',
			),
			array(
				'key'     => '_mahl_status',
				'value'   => self::COMPLETED_GAME_STATUS,
				'compare' => '=',
			),
		);

		if ( ! empty( $phase_id ) ) {
			$meta_query[] = array(
				'key'     => '_mahl_phase_id',
				'value'   => $phase_id,
				'compare' => '=',
				'type'    => 'NUMERIC',
			);
		}

		if ( '' !== $group_key ) {
			$meta_query[] = array(
				'key'     => '_mahl_group_key',
				'value'   => $group_key,
				'compare' => '=',
			);
		}

		return get_posts(
			array(
				'post_type'      => 'mahl_game',
				'post_status'    => $this->get_supported_game_post_statuses(),
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'orderby'        => 'date',
				'order'          => 'ASC',
				'meta_query'     => $meta_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			)
		);
	}

	/**
	 * Return the supported post statuses for standings queries.
	 *
	 * @return array
	 */
	protected function get_supported_game_post_statuses() {
		return array( 'publish', 'future', 'draft', 'pending', 'private' );
	}

	/**
	 * Return the normalized game data used in standings calculations.
	 *
	 * @param int $game_id Game post ID.
	 * @return array
	 */
	protected function get_game_data( $game_id ) {
		return array(
			'game_id'          => absint( $game_id ),
			'home_team_id'     => absint( get_post_meta( $game_id, '_mahl_home_team_id', true ) ),
			'away_team_id'     => absint( get_post_meta( $game_id, '_mahl_away_team_id', true ) ),
			'home_score_final' => $this->get_score_value( $game_id, '_mahl_score_home_final' ),
			'away_score_final' => $this->get_score_value( $game_id, '_mahl_score_away_final' ),
			'overtime_played'  => '1' === get_post_meta( $game_id, '_mahl_overtime_played', true ),
			'shootout_played'  => '1' === get_post_meta( $game_id, '_mahl_shootout_played', true ),
		);
	}

	/**
	 * Return a validated final score value.
	 *
	 * @param int    $game_id Game post ID.
	 * @param string $meta_key Score meta key.
	 * @return int|null
	 */
	protected function get_score_value( $game_id, $meta_key ) {
		$value = get_post_meta( $game_id, $meta_key, true );

		if ( '' === $value || ! preg_match( '/^\d+$/', (string) $value ) ) {
			return null;
		}

		return absint( $value );
	}

	/**
	 * Determine whether a game record can be used for standings.
	 *
	 * @param array $game_data Normalized game data.
	 * @return bool
	 */
	protected function is_valid_game_for_standings( $game_data ) {
		if ( empty( $game_data['home_team_id'] ) || empty( $game_data['away_team_id'] ) ) {
			return false;
		}

		if ( $game_data['home_team_id'] === $game_data['away_team_id'] ) {
			return false;
		}

		if ( null === $game_data['home_score_final'] || null === $game_data['away_score_final'] ) {
			return false;
		}

		return 'mahl_team' === get_post_type( $game_data['home_team_id'] )
			&& 'mahl_team' === get_post_type( $game_data['away_team_id'] );
	}

	/**
	 * Ensure a team row exists in the standings table.
	 *
	 * @param array $standings Standings table.
	 * @param int   $team_id Team post ID.
	 * @return void
	 */
	protected function ensure_team_row( &$standings, $team_id ) {
		if ( isset( $standings[ $team_id ] ) ) {
			return;
		}

		$standings[ $team_id ] = array(
			'team_id'         => $team_id,
			'team_name'       => get_the_title( $team_id ),
			'games_played'    => 0,
			'wins'            => 0,
			'draws'           => 0,
			'losses'          => 0,
			'goals_for'       => 0,
			'goals_against'   => 0,
			'goal_difference' => 0,
			'points'          => 0,
		);
	}

	/**
	 * Apply game totals to the home and away team rows.
	 *
	 * @param array $home_row Home team standings row.
	 * @param array $away_row Away team standings row.
	 * @param array $game_data Normalized game data.
	 * @return void
	 */
	protected function apply_game_statistics( &$home_row, &$away_row, $game_data ) {
		$home_row['games_played']++;
		$away_row['games_played']++;

		$home_row['goals_for']     += $game_data['home_score_final'];
		$home_row['goals_against'] += $game_data['away_score_final'];
		$away_row['goals_for']     += $game_data['away_score_final'];
		$away_row['goals_against'] += $game_data['home_score_final'];

		$home_row['goal_difference'] = $home_row['goals_for'] - $home_row['goals_against'];
		$away_row['goal_difference'] = $away_row['goals_for'] - $away_row['goals_against'];
	}

	/**
	 * Apply points and result counters for a game.
	 *
	 * @param array $home_row Home team standings row.
	 * @param array $away_row Away team standings row.
	 * @param array $game_data Normalized game data.
	 * @param array $point_rules Configured point rules.
	 * @return void
	 */
	protected function apply_result_points( &$home_row, &$away_row, $game_data, $point_rules ) {
		$result_type = $this->determine_result_type( $game_data );

		if ( 'home_win' === $result_type ) {
			$home_row['wins']++;
			$away_row['losses']++;
			$home_row['points'] += $point_rules['win_points'];
			$away_row['points'] += $point_rules['loss_points'];
			return;
		}

		if ( 'away_win' === $result_type ) {
			$away_row['wins']++;
			$home_row['losses']++;
			$away_row['points'] += $point_rules['win_points'];
			$home_row['points'] += $point_rules['loss_points'];
			return;
		}

		$home_row['draws']++;
		$away_row['draws']++;
		$home_row['points'] += $point_rules['draw_points'];
		$away_row['points'] += $point_rules['draw_points'];
	}

	/**
	 * Determine the result type for a game.
	 *
	 * Future versions can branch here for overtime or shootout win models.
	 *
	 * @param array $game_data Normalized game data.
	 * @return string
	 */
	protected function determine_result_type( $game_data ) {
		if ( $game_data['home_score_final'] > $game_data['away_score_final'] ) {
			return 'home_win';
		}

		if ( $game_data['home_score_final'] < $game_data['away_score_final'] ) {
			return 'away_win';
		}

		return 'draw';
	}

	/**
	 * Sort the standings table for stable display and later rendering.
	 *
	 * @param array $standings Standings rows keyed by team ID.
	 * @return array
	 */
	protected function sort_standings( $standings ) {
		$sorted_rows = array_values( $standings );

		usort(
			$sorted_rows,
			array( $this, 'compare_standings_rows' )
		);

		return $sorted_rows;
	}

	/**
	 * Return the default standings response structure.
	 *
	 * @param int    $season_id Season post ID.
	 * @param int    $phase_id Phase post ID.
	 * @param string $group_key Normalized group key.
	 * @return array
	 */
	protected function get_empty_standings_response( $season_id, $phase_id, $group_key ) {
		return array(
			'context' => array(
				'season_id'   => $season_id,
				'phase_id'    => $phase_id,
				'group_key'   => $group_key,
				'point_rules' => $this->get_point_rules(),
				'game_count'  => 0,
			),
			'rows'    => array(),
		);
	}

	/**
	 * Add 1-based position values to ordered standings rows.
	 *
	 * @param array $rows Sorted standings rows.
	 * @return array
	 */
	protected function add_positions_to_rows( $rows ) {
		foreach ( $rows as $index => $row ) {
			$rows[ $index ]['position'] = $index + 1;
		}

		return $rows;
	}

	/**
	 * Compare two standings rows.
	 *
	 * @param array $left Left row.
	 * @param array $right Right row.
	 * @return int
	 */
	protected function compare_standings_rows( $left, $right ) {
		$comparisons = array(
			$right['points'] - $left['points'],
			$right['goal_difference'] - $left['goal_difference'],
			$right['goals_for'] - $left['goals_for'],
		);

		foreach ( $comparisons as $comparison ) {
			if ( 0 !== $comparison ) {
				return $comparison;
			}
		}

		return strcasecmp( $left['team_name'], $right['team_name'] );
	}
}
