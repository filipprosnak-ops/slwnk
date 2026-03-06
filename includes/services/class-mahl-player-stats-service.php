<?php
/**
 * Player statistics service.
 *
 * @package MAHLManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Calculate player statistics from game appearances and events.
 */
class MAHL_Player_Stats_Service extends MAHL_Base_Service {

	/**
	 * Completed game status value.
	 *
	 * @var string
	 */
	const COMPLETED_GAME_STATUS = 'final';

	/**
	 * Appearance meta key.
	 *
	 * @var string
	 */
	const APPEARANCES_META_KEY = '_mahl_game_player_appearances';

	/**
	 * Event meta key.
	 *
	 * @var string
	 */
	const EVENTS_META_KEY = '_mahl_game_events';

	/**
	 * Return normalized player statistics for a competition context.
	 *
	 * Returned structure:
	 *
	 * array(
	 *     'context' => array(
	 *         'season_id' => (int),
	 *         'phase_id'  => (int),
	 *         'group_key' => (string),
	 *         'game_count' => (int),
	 *     ),
	 *     'rows' => array(
	 *         array(
	 *             'position'        => (int),
	 *             'player_id'       => (int),
	 *             'player_name'     => (string),
	 *             'team_id'         => (int),
	 *             'team_name'       => (string),
	 *             'games_played'    => (int),
	 *             'goals'           => (int),
	 *             'assists'         => (int),
	 *             'points'          => (int),
	 *             'penalty_minutes' => (int),
	 *         ),
	 *     ),
	 * )
	 *
	 * @param array $args {
	 *     Optional. Player statistics query arguments.
	 *
	 *     @type int    $season_id Required season post ID.
	 *     @type int    $phase_id  Optional phase post ID.
	 *     @type string $group_key Optional normalized group or bracket key.
	 * }
	 * @return array
	 */
	public function get_player_statistics( $args = array() ) {
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
		$response  = $this->get_empty_statistics_response( $season_id, $phase_id, $group_key );

		if ( empty( $season_id ) ) {
			return $response;
		}

		$games        = $this->get_completed_games( $season_id, $phase_id, $group_key );
		$player_stats = array();

		foreach ( $games as $game_id ) {
			$game_context = $this->get_game_context( $game_id );

			if ( empty( $game_context['team_ids'] ) ) {
				continue;
			}

			$participants = $this->get_game_participants( $game_id, $game_context );

			foreach ( $participants as $player_id => $team_id ) {
				$this->ensure_player_row( $player_stats, $player_id, $team_id );
				$player_stats[ $player_id ]['games_played']++;
			}

			$this->apply_game_events_to_statistics( $player_stats, $game_id, $game_context );
		}

		$response['context']['game_count'] = count( $games );
		$response['rows']                  = $this->add_positions_to_rows( $this->sort_player_statistics( $player_stats ) );

		return $response;
	}

	/**
	 * Return one player statistics row for a competition context.
	 *
	 * @param int   $player_id Player post ID.
	 * @param array $args Optional query arguments passed to get_player_statistics().
	 * @return array
	 */
	public function get_player_stat_line( $player_id, $args = array() ) {
		$player_id  = absint( $player_id );
		$statistics = $this->get_player_statistics( $args );

		if ( empty( $player_id ) ) {
			return array();
		}

		foreach ( $statistics['rows'] as $row ) {
			if ( absint( $row['player_id'] ) === $player_id ) {
				return $row;
			}
		}

		return array();
	}

	/**
	 * Apply event-based statistics for one game.
	 *
	 * @param array $player_stats Aggregated player statistics.
	 * @param int   $game_id Game post ID.
	 * @param array $game_context Normalized game context.
	 * @return void
	 */
	protected function apply_game_events_to_statistics( &$player_stats, $game_id, $game_context ) {
		$events = get_post_meta( $game_id, self::EVENTS_META_KEY, true );
		$touched_player_ids = array();

		if ( ! is_array( $events ) ) {
			return;
		}

		foreach ( $events as $event ) {
			$normalized_event = $this->normalize_event( $event, $game_context );

			if ( empty( $normalized_event ) ) {
				continue;
			}

			foreach ( $normalized_event['participants'] as $participant ) {
				$player_id = $participant['player_id'];
				$role      = $participant['role'];
				$team_id   = $normalized_event['team_id'];

				$this->ensure_player_row( $player_stats, $player_id, $team_id );

				if ( 'goal' === $normalized_event['event_type'] && 'scorer' === $role ) {
					$player_stats[ $player_id ]['goals']++;
					$touched_player_ids[ $player_id ] = true;
				}

				if ( 'goal' === $normalized_event['event_type'] && 'assist' === $role ) {
					$player_stats[ $player_id ]['assists']++;
					$touched_player_ids[ $player_id ] = true;
				}

				if ( 'penalty' === $normalized_event['event_type'] && 'penalized_player' === $role ) {
					$player_stats[ $player_id ]['penalty_minutes'] += $normalized_event['penalty_minutes'];
				}
			}
		}

		foreach ( array_keys( $touched_player_ids ) as $player_id ) {
			$player_stats[ $player_id ]['points'] = $player_stats[ $player_id ]['goals'] + $player_stats[ $player_id ]['assists'];
		}
	}

	/**
	 * Return the completed games for a player stats context.
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
				'post_status'    => array( 'publish', 'future', 'draft', 'pending', 'private' ),
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'orderby'        => 'date',
				'order'          => 'ASC',
				'no_found_rows'  => true,
				'meta_query'     => $meta_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			)
		);
	}

	/**
	 * Return the normalized game context used for validation.
	 *
	 * @param int $game_id Game post ID.
	 * @return array
	 */
	protected function get_game_context( $game_id ) {
		$team_ids = array_values(
			array_unique(
				array_filter(
					array(
						absint( get_post_meta( $game_id, '_mahl_home_team_id', true ) ),
						absint( get_post_meta( $game_id, '_mahl_away_team_id', true ) ),
					)
				)
			)
		);

		return array(
			'game_id'   => absint( $game_id ),
			'team_ids'  => $team_ids,
			'team_map'  => $this->get_team_player_map( $team_ids ),
		);
	}

	/**
	 * Return a map of valid players by team.
	 *
	 * @param array $team_ids Team post IDs.
	 * @return array
	 */
	protected function get_team_player_map( $team_ids ) {
		$team_map = array();

		foreach ( $team_ids as $team_id ) {
			$team_map[ $team_id ] = array();
		}

		if ( empty( $team_ids ) ) {
			return $team_map;
		}

		$players = get_posts(
			array(
				'post_type'      => 'mahl_player',
				'post_status'    => array( 'publish', 'future', 'draft', 'pending', 'private' ),
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_query'     => array(
					array(
						'key'     => '_mahl_team_id',
						'value'   => $team_ids,
						'compare' => 'IN',
						'type'    => 'NUMERIC',
					),
				), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			)
		);

		foreach ( $players as $player_id ) {
			$team_id = absint( get_post_meta( $player_id, '_mahl_team_id', true ) );

			if ( isset( $team_map[ $team_id ] ) ) {
				$team_map[ $team_id ][] = absint( $player_id );
			}
		}

		return $team_map;
	}

	/**
	 * Return the set of players who participated in a game.
	 *
	 * This uses stored appearances first and also includes event participants
	 * as a fallback so stat-producing players still count as participants.
	 *
	 * @param int   $game_id Game post ID.
	 * @param array $game_context Normalized game context.
	 * @return array
	 */
	protected function get_game_participants( $game_id, $game_context ) {
		$participants = array();
		$appearances  = get_post_meta( $game_id, self::APPEARANCES_META_KEY, true );

		if ( is_array( $appearances ) ) {
			foreach ( $appearances as $appearance ) {
				$normalized_appearance = $this->normalize_appearance( $appearance, $game_context );

				if ( empty( $normalized_appearance ) ) {
					continue;
				}

				$participants[ $normalized_appearance['player_id'] ] = $normalized_appearance['team_id'];
			}
		}

		$events = get_post_meta( $game_id, self::EVENTS_META_KEY, true );

		if ( is_array( $events ) ) {
			foreach ( $events as $event ) {
				$normalized_event = $this->normalize_event( $event, $game_context );

				if ( empty( $normalized_event ) ) {
					continue;
				}

				foreach ( $normalized_event['participants'] as $participant ) {
					$participants[ $participant['player_id'] ] = $normalized_event['team_id'];
				}
			}
		}

		return $participants;
	}

	/**
	 * Normalize an appearance row.
	 *
	 * @param array $appearance Raw appearance row.
	 * @param array $game_context Normalized game context.
	 * @return array
	 */
	protected function normalize_appearance( $appearance, $game_context ) {
		if ( ! is_array( $appearance ) ) {
			return array();
		}

		$team_id   = isset( $appearance['team_id'] ) ? absint( $appearance['team_id'] ) : 0;
		$player_id = isset( $appearance['player_id'] ) ? absint( $appearance['player_id'] ) : 0;

		if ( ! $this->is_valid_player_for_team( $player_id, $team_id, $game_context ) ) {
			return array();
		}

		return array(
			'team_id'   => $team_id,
			'player_id' => $player_id,
		);
	}

	/**
	 * Normalize a game event row.
	 *
	 * @param array $event Raw event row.
	 * @param array $game_context Normalized game context.
	 * @return array
	 */
	protected function normalize_event( $event, $game_context ) {
		if ( ! is_array( $event ) ) {
			return array();
		}

		$event_type = isset( $event['event_type'] ) ? sanitize_key( $event['event_type'] ) : '';
		$team_id    = isset( $event['team_id'] ) ? absint( $event['team_id'] ) : 0;

		if ( ! in_array( $event_type, array( 'goal', 'penalty' ), true ) ) {
			return array();
		}

		if ( ! in_array( $team_id, $game_context['team_ids'], true ) ) {
			return array();
		}

		$normalized_event = array(
			'event_type'      => $event_type,
			'team_id'         => $team_id,
			'period_number'   => isset( $event['period_number'] ) ? max( 1, absint( $event['period_number'] ) ) : 1,
			'event_order'     => isset( $event['event_order'] ) ? max( 1, absint( $event['event_order'] ) ) : 1,
			'event_time'      => isset( $event['event_time'] ) ? sanitize_text_field( $event['event_time'] ) : '',
			'participants'    => array(),
			'penalty_minutes' => isset( $event['penalty_minutes'] ) ? absint( $event['penalty_minutes'] ) : 0,
			'label'           => isset( $event['label'] ) ? sanitize_text_field( $event['label'] ) : ( isset( $event['notes'] ) ? sanitize_text_field( $event['notes'] ) : '' ),
		);

		if ( empty( $event['participants'] ) || ! is_array( $event['participants'] ) ) {
			return array();
		}

		foreach ( $event['participants'] as $participant ) {
			if ( ! is_array( $participant ) ) {
				continue;
			}

			$role      = isset( $participant['role'] ) ? sanitize_key( $participant['role'] ) : '';
			$player_id = isset( $participant['player_id'] ) ? absint( $participant['player_id'] ) : 0;

			if ( ! in_array( $role, array( 'scorer', 'assist', 'penalized_player' ), true ) ) {
				continue;
			}

			if ( ! $this->is_valid_player_for_team( $player_id, $team_id, $game_context ) ) {
				continue;
			}

			if ( $this->participant_exists( $normalized_event['participants'], $player_id ) ) {
				continue;
			}

			$normalized_event['participants'][] = array(
				'role'      => $role,
				'player_id' => $player_id,
			);
		}

		if ( empty( $normalized_event['participants'] ) ) {
			return array();
		}

		if ( 'goal' === $event_type && ! $this->event_has_role( $normalized_event['participants'], 'scorer' ) ) {
			return array();
		}

		if ( 'penalty' === $event_type ) {
			if ( $normalized_event['penalty_minutes'] <= 0 ) {
				return array();
			}

			if ( ! $this->event_has_role( $normalized_event['participants'], 'penalized_player' ) ) {
				return array();
			}
		}

		return $normalized_event;
	}

	/**
	 * Determine whether a participant exists in an event.
	 *
	 * @param array $participants Normalized event participants.
	 * @param int   $player_id Player post ID.
	 * @return bool
	 */
	protected function participant_exists( $participants, $player_id ) {
		foreach ( $participants as $participant ) {
			if ( absint( $participant['player_id'] ) === absint( $player_id ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine whether an event contains a participant role.
	 *
	 * @param array  $participants Normalized event participants.
	 * @param string $role Participant role.
	 * @return bool
	 */
	protected function event_has_role( $participants, $role ) {
		foreach ( $participants as $participant ) {
			if ( $participant['role'] === $role ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine whether a player is valid for a team in the current game context.
	 *
	 * @param int   $player_id Player post ID.
	 * @param int   $team_id Team post ID.
	 * @param array $game_context Normalized game context.
	 * @return bool
	 */
	protected function is_valid_player_for_team( $player_id, $team_id, $game_context ) {
		if ( empty( $player_id ) || empty( $team_id ) ) {
			return false;
		}

		if ( 'mahl_player' !== get_post_type( $player_id ) ) {
			return false;
		}

		if ( ! in_array( $team_id, $game_context['team_ids'], true ) ) {
			return false;
		}

		return isset( $game_context['team_map'][ $team_id ] ) && in_array( $player_id, $game_context['team_map'][ $team_id ], true );
	}

	/**
	 * Ensure a player row exists in the statistics table.
	 *
	 * @param array $player_stats Player statistics rows keyed by player ID.
	 * @param int   $player_id Player post ID.
	 * @param int   $team_id Team post ID.
	 * @return void
	 */
	protected function ensure_player_row( &$player_stats, $player_id, $team_id = 0 ) {
		if ( isset( $player_stats[ $player_id ] ) ) {
			if ( empty( $player_stats[ $player_id ]['team_id'] ) && ! empty( $team_id ) ) {
				$player_stats[ $player_id ]['team_id']   = $team_id;
				$player_stats[ $player_id ]['team_name'] = get_the_title( $team_id );
			}

			return;
		}

		$current_team_id = absint( get_post_meta( $player_id, '_mahl_team_id', true ) );

		if ( empty( $team_id ) && ! empty( $current_team_id ) ) {
			$team_id = $current_team_id;
		}

		$player_stats[ $player_id ] = array(
			'player_id'       => $player_id,
			'player_name'     => get_the_title( $player_id ),
			'team_id'         => $team_id,
			'team_name'       => ! empty( $team_id ) ? get_the_title( $team_id ) : '',
			'games_played'    => 0,
			'goals'           => 0,
			'assists'         => 0,
			'points'          => 0,
			'penalty_minutes' => 0,
		);
	}

	/**
	 * Return the default player statistics response structure.
	 *
	 * @param int    $season_id Season post ID.
	 * @param int    $phase_id Phase post ID.
	 * @param string $group_key Normalized group key.
	 * @return array
	 */
	protected function get_empty_statistics_response( $season_id, $phase_id, $group_key ) {
		return array(
			'context' => array(
				'season_id'  => $season_id,
				'phase_id'   => $phase_id,
				'group_key'  => $group_key,
				'game_count' => 0,
			),
			'rows'    => array(),
		);
	}

	/**
	 * Sort player statistics for future admin and template use.
	 *
	 * @param array $player_stats Player statistics rows keyed by player ID.
	 * @return array
	 */
	protected function sort_player_statistics( $player_stats ) {
		$rows = array_values( $player_stats );

		usort(
			$rows,
			array( $this, 'compare_player_rows' )
		);

		return $rows;
	}

	/**
	 * Add 1-based position values to ordered player rows.
	 *
	 * @param array $rows Sorted player rows.
	 * @return array
	 */
	protected function add_positions_to_rows( $rows ) {
		foreach ( $rows as $index => $row ) {
			$rows[ $index ]['position'] = $index + 1;
		}

		return $rows;
	}

	/**
	 * Compare two player statistics rows.
	 *
	 * @param array $left Left row.
	 * @param array $right Right row.
	 * @return int
	 */
	protected function compare_player_rows( $left, $right ) {
		$comparisons = array(
			$right['points'] - $left['points'],
			$right['goals'] - $left['goals'],
			$right['assists'] - $left['assists'],
			$right['games_played'] - $left['games_played'],
		);

		foreach ( $comparisons as $comparison ) {
			if ( 0 !== $comparison ) {
				return $comparison;
			}
		}

		return strcasecmp( $left['player_name'], $right['player_name'] );
	}
}
