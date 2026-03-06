<?php
/**
 * Frontend data provider.
 *
 * @package MAHLManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Prepare frontend data for plugin templates.
 */
class MAHL_Frontend_Data_Provider {

	/**
	 * Standings service instance.
	 *
	 * @var MAHL_Standings_Service
	 */
	protected $standings_service;

	/**
	 * Player statistics service instance.
	 *
	 * @var MAHL_Player_Stats_Service
	 */
	protected $player_stats_service;

	/**
	 * Set up service dependencies.
	 */
	public function __construct() {
		$this->standings_service    = new MAHL_Standings_Service();
		$this->player_stats_service = new MAHL_Player_Stats_Service();
	}

	/**
	 * Return prepared data for a template.
	 *
	 * @param string $template_name Template filename.
	 * @return array
	 */
	public function get_template_data( $template_name ) {
		switch ( $template_name ) {
			case 'archive-mahl_season.php':
				return $this->get_season_archive_data();

			case 'archive-mahl_team.php':
				return $this->get_team_archive_data();

			case 'archive-mahl_player.php':
				return $this->get_player_archive_data();

			case 'archive-mahl_game.php':
				return $this->get_game_archive_data();

			case 'single-mahl_season.php':
				return $this->get_season_single_data( get_queried_object_id() );

			case 'single-mahl_team.php':
				return $this->get_team_single_data( get_queried_object_id() );

			case 'single-mahl_player.php':
				return $this->get_player_single_data( get_queried_object_id() );

			case 'single-mahl_game.php':
				return $this->get_game_single_data( get_queried_object_id() );
		}

		return array();
	}

	/**
	 * Return prepared season archive data.
	 *
	 * @return array
	 */
	protected function get_season_archive_data() {
		return array(
			'title'   => post_type_archive_title( '', false ),
			'entries' => array_map(
				array( $this, 'build_season_archive_entry' ),
				$this->get_current_query_posts()
			),
		);
	}

	/**
	 * Return prepared team archive data.
	 *
	 * @return array
	 */
	protected function get_team_archive_data() {
		return array(
			'title'   => post_type_archive_title( '', false ),
			'entries' => array_map(
				array( $this, 'build_team_archive_entry' ),
				$this->get_current_query_posts()
			),
		);
	}

	/**
	 * Return prepared player archive data.
	 *
	 * @return array
	 */
	protected function get_player_archive_data() {
		return array(
			'title'   => post_type_archive_title( '', false ),
			'entries' => array_map(
				array( $this, 'build_player_archive_entry' ),
				$this->get_current_query_posts()
			),
		);
	}

	/**
	 * Return prepared game archive data.
	 *
	 * @return array
	 */
	protected function get_game_archive_data() {
		return array(
			'title'   => post_type_archive_title( '', false ),
			'entries' => array_map(
				array( $this, 'build_game_archive_entry' ),
				$this->get_current_query_posts()
			),
		);
	}

	/**
	 * Return prepared season single data.
	 *
	 * @param int $season_id Season post ID.
	 * @return array
	 */
	protected function get_season_single_data( $season_id ) {
		$season_id = absint( $season_id );
		$phases    = $this->get_phase_posts_for_season( $season_id );
		$teams     = $this->get_team_posts_for_season( $season_id );
		$games     = $this->get_game_posts_for_season( $season_id );

		return array(
			'title'              => get_the_title( $season_id ),
			'excerpt'            => has_excerpt( $season_id ) ? get_the_excerpt( $season_id ) : '',
			'content'            => $this->get_post_content( $season_id ),
			'structure_overview' => array(
				'phase_count'       => count( $phases ),
				'team_count'        => count( $teams ),
				'game_count'        => count( $games ),
				'available_groups'  => $this->get_unique_group_keys_from_games( $games ),
			),
			'phases'             => $this->build_phase_summary_list( $phases ),
			'standings_sections' => $this->get_season_standings_sections( $season_id, $phases ),
			'games_by_phase'     => $this->group_games_by_phase( $games, $phases ),
		);
	}

	/**
	 * Return prepared team single data.
	 *
	 * @param int $team_id Team post ID.
	 * @return array
	 */
	protected function get_team_single_data( $team_id ) {
		$team_id         = absint( $team_id );
		$season_id       = absint( get_post_meta( $team_id, '_mahl_season_id', true ) );
		$roster          = $this->get_roster_for_team( $team_id );
		$games           = $this->get_games_for_team( $team_id );
		$standings       = $this->get_team_standings_context( $team_id, $season_id );

		return array(
			'title'             => get_the_title( $team_id ),
			'excerpt'           => has_excerpt( $team_id ) ? get_the_excerpt( $team_id ) : '',
			'content'           => $this->get_post_content( $team_id ),
			'season'            => $this->get_link_data( $season_id ),
			'roster'            => array_map( array( $this, 'build_player_link_entry' ), $roster ),
			'games'             => $this->build_game_list( $games ),
			'standings_context' => $standings,
		);
	}

	/**
	 * Return prepared player single data.
	 *
	 * @param int $player_id Player post ID.
	 * @return array
	 */
	protected function get_player_single_data( $player_id ) {
		$player_id  = absint( $player_id );
		$team_id    = absint( get_post_meta( $player_id, '_mahl_team_id', true ) );
		$season_id  = ! empty( $team_id ) ? absint( get_post_meta( $team_id, '_mahl_season_id', true ) ) : 0;
		$stats      = ! empty( $season_id ) ? $this->player_stats_service->get_player_stat_line( $player_id, array( 'season_id' => $season_id ) ) : array();

		return array(
			'title'      => get_the_title( $player_id ),
			'excerpt'    => has_excerpt( $player_id ) ? get_the_excerpt( $player_id ) : '',
			'content'    => $this->get_post_content( $player_id ),
			'team'       => $this->get_link_data( $team_id ),
			'season'     => $this->get_link_data( $season_id ),
			'statistics' => $this->get_player_statistics_row( $player_id, $team_id, $stats ),
		);
	}

	/**
	 * Return prepared game single data.
	 *
	 * @param int $game_id Game post ID.
	 * @return array
	 */
	protected function get_game_single_data( $game_id ) {
		$game_id      = absint( $game_id );
		$period_count = MAHL_Settings_Service::get_game_format()['period_count'];
		$game_data    = $this->build_game_list_item( $game_id );

		return array(
			'title'          => get_the_title( $game_id ),
			'excerpt'        => has_excerpt( $game_id ) ? get_the_excerpt( $game_id ) : '',
			'content'        => $this->get_post_content( $game_id ),
			'season'         => $this->get_link_data( absint( get_post_meta( $game_id, '_mahl_season_id', true ) ) ),
			'phase'          => $this->get_link_data( absint( get_post_meta( $game_id, '_mahl_phase_id', true ) ) ),
			'round_number'   => absint( get_post_meta( $game_id, '_mahl_round_number', true ) ),
			'group_key'      => sanitize_key( get_post_meta( $game_id, '_mahl_group_key', true ) ),
			'venue'          => sanitize_text_field( get_post_meta( $game_id, '_mahl_venue', true ) ),
			'match_date'     => $this->format_match_date( get_post_meta( $game_id, '_mahl_match_date', true ) ),
			'match_time'     => $this->format_match_time( get_post_meta( $game_id, '_mahl_match_time', true ) ),
			'status'         => sanitize_key( get_post_meta( $game_id, '_mahl_status', true ) ),
			'status_label'   => $this->format_status_label( get_post_meta( $game_id, '_mahl_status', true ) ),
			'home_team'      => $this->get_link_data( absint( get_post_meta( $game_id, '_mahl_home_team_id', true ) ) ),
			'away_team'      => $this->get_link_data( absint( get_post_meta( $game_id, '_mahl_away_team_id', true ) ) ),
			'final_score'    => $game_data['score_text'],
			'period_scores'  => $this->get_period_scores( $game_id, $period_count ),
			'events'         => $this->get_game_event_entries( $game_id ),
		);
	}

	/**
	 * Return posts from the current query.
	 *
	 * @return array
	 */
	protected function get_current_query_posts() {
		global $wp_query;

		return isset( $wp_query->posts ) && is_array( $wp_query->posts ) ? $wp_query->posts : array();
	}

	/**
	 * Build a season archive entry.
	 *
	 * @param WP_Post $post Season post object.
	 * @return array
	 */
	protected function build_season_archive_entry( $post ) {
		$season_id = $post->ID;
		$phases    = $this->get_phase_posts_for_season( $season_id );

		return array(
			'id'             => $season_id,
			'title'          => get_the_title( $season_id ),
			'permalink'      => get_permalink( $season_id ),
			'excerpt'        => has_excerpt( $season_id ) ? get_the_excerpt( $season_id ) : '',
			'phase_count'    => count( $phases ),
			'game_count'     => count( $this->get_game_posts_for_season( $season_id ) ),
		);
	}

	/**
	 * Build a team archive entry.
	 *
	 * @param WP_Post $post Team post object.
	 * @return array
	 */
	protected function build_team_archive_entry( $post ) {
		$team_id   = $post->ID;
		$season_id = absint( get_post_meta( $team_id, '_mahl_season_id', true ) );

		return array(
			'id'        => $team_id,
			'title'     => get_the_title( $team_id ),
			'permalink' => get_permalink( $team_id ),
			'excerpt'   => has_excerpt( $team_id ) ? get_the_excerpt( $team_id ) : '',
			'season'    => $this->get_link_data( $season_id ),
			'roster_count' => count( $this->get_roster_for_team( $team_id ) ),
		);
	}

	/**
	 * Build a player archive entry.
	 *
	 * @param WP_Post $post Player post object.
	 * @return array
	 */
	protected function build_player_archive_entry( $post ) {
		$player_id = $post->ID;
		$team_id   = absint( get_post_meta( $player_id, '_mahl_team_id', true ) );
		$season_id = ! empty( $team_id ) ? absint( get_post_meta( $team_id, '_mahl_season_id', true ) ) : 0;

		return array(
			'id'        => $player_id,
			'title'     => get_the_title( $player_id ),
			'permalink' => get_permalink( $player_id ),
			'excerpt'   => has_excerpt( $player_id ) ? get_the_excerpt( $player_id ) : '',
			'team'      => $this->get_link_data( $team_id ),
			'season'    => $this->get_link_data( $season_id ),
		);
	}

	/**
	 * Build a game archive entry.
	 *
	 * @param WP_Post $post Game post object.
	 * @return array
	 */
	protected function build_game_archive_entry( $post ) {
		return $this->build_game_list_item( $post->ID );
	}

	/**
	 * Return a phase summary list for a season.
	 *
	 * @param array $phases Phase posts.
	 * @return array
	 */
	protected function build_phase_summary_list( $phases ) {
		$phase_summaries = array();

		foreach ( $phases as $phase ) {
			$games = $this->get_game_posts_for_context( 0, $phase->ID );

			$phase_summaries[] = array(
				'id'         => $phase->ID,
				'title'      => get_the_title( $phase ),
				'permalink'  => get_permalink( $phase ),
				'game_count' => count( $games ),
				'group_keys' => $this->get_unique_group_keys_from_games( $games ),
			);
		}

		return $phase_summaries;
	}

	/**
	 * Return standings sections for a season.
	 *
	 * @param int   $season_id Season post ID.
	 * @param array $phases Phase posts.
	 * @return array
	 */
	protected function get_season_standings_sections( $season_id, $phases ) {
		$sections = array();
		$overall  = $this->standings_service->get_standings(
			array(
				'season_id' => $season_id,
			)
		);

		if ( ! empty( $overall['rows'] ) ) {
			$sections[] = array(
				'title' => __( 'Overall Season Standings', 'mahl-manager' ),
				'rows'  => $overall['rows'],
			);
		}

		foreach ( $phases as $phase ) {
			$phase_standings = $this->standings_service->get_standings(
				array(
					'season_id' => $season_id,
					'phase_id'  => $phase->ID,
				)
			);

			if ( empty( $phase_standings['rows'] ) ) {
				continue;
			}

			$sections[] = array(
				'title' => get_the_title( $phase ),
				'rows'  => $phase_standings['rows'],
			);
		}

		return $sections;
	}

	/**
	 * Group games by phase for season rendering.
	 *
	 * @param array $games Game posts.
	 * @param array $phases Phase posts.
	 * @return array
	 */
	protected function group_games_by_phase( $games, $phases ) {
		$grouped = array();

		foreach ( $phases as $phase ) {
			$grouped[ $phase->ID ] = array(
				'phase' => array(
					'id'        => $phase->ID,
					'title'     => get_the_title( $phase ),
					'permalink' => get_permalink( $phase ),
				),
				'games' => array(),
			);
		}

		$grouped[0] = array(
			'phase' => array(
				'id'        => 0,
				'title'     => __( 'Unassigned Phase', 'mahl-manager' ),
				'permalink' => '',
			),
			'games' => array(),
		);

		foreach ( $games as $game ) {
			$phase_id = absint( get_post_meta( $game->ID, '_mahl_phase_id', true ) );

			if ( ! isset( $grouped[ $phase_id ] ) ) {
				$grouped[ $phase_id ] = array(
					'phase' => $this->get_link_data( $phase_id ),
					'games' => array(),
				);
			}

			$grouped[ $phase_id ]['games'][] = $this->build_game_list_item( $game->ID );
		}

		$groups = array_values(
			array_filter(
				$grouped,
				function ( $group ) {
					return ! empty( $group['games'] );
				}
			)
		);

		foreach ( $groups as $index => $group ) {
			$groups[ $index ]['games'] = $this->sort_game_entries( $group['games'] );
		}

		return $groups;
	}

	/**
	 * Return the standings context for a team.
	 *
	 * @param int $team_id Team post ID.
	 * @param int $season_id Season post ID.
	 * @return array
	 */
	protected function get_team_standings_context( $team_id, $season_id ) {
		$context = array(
			'season' => array(),
			'phases' => array(),
		);

		if ( empty( $season_id ) ) {
			return $context;
		}

		$overall = $this->standings_service->get_standings(
			array(
				'season_id' => $season_id,
			)
		);

		$context['season'] = $this->find_team_standing_row( $overall['rows'], $team_id );

		foreach ( $this->get_phase_posts_for_season( $season_id ) as $phase ) {
			$phase_standings = $this->standings_service->get_standings(
				array(
					'season_id' => $season_id,
					'phase_id'  => $phase->ID,
				)
			);
			$row             = $this->find_team_standing_row( $phase_standings['rows'], $team_id );

			if ( empty( $row ) ) {
				continue;
			}

			$context['phases'][] = array(
				'phase' => $this->get_link_data( $phase->ID ),
				'row'   => $row,
			);
		}

		return $context;
	}

	/**
	 * Return a prepared player statistics row with defaults.
	 *
	 * @param int   $player_id Player post ID.
	 * @param int   $team_id Team post ID.
	 * @param array $stats Player statistics row.
	 * @return array
	 */
	protected function get_player_statistics_row( $player_id, $team_id, $stats ) {
		$defaults = array(
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

		return wp_parse_args( $stats, $defaults );
	}

	/**
	 * Return event entries for a game.
	 *
	 * @param int $game_id Game post ID.
	 * @return array
	 */
	protected function get_game_event_entries( $game_id ) {
		$events  = get_post_meta( $game_id, '_mahl_game_events', true );
		$entries = array();

		if ( ! is_array( $events ) ) {
			return $entries;
		}

		foreach ( $events as $event ) {
			if ( ! is_array( $event ) ) {
				continue;
			}

			$event_type = isset( $event['event_type'] ) ? sanitize_key( $event['event_type'] ) : '';
			$team_id    = isset( $event['team_id'] ) ? absint( $event['team_id'] ) : 0;

			if ( ! in_array( $event_type, array( 'goal', 'penalty' ), true ) ) {
				continue;
			}

			$entry = array(
				'event_type'      => $event_type,
				'event_type_label'=> 'goal' === $event_type ? __( 'Goal', 'mahl-manager' ) : __( 'Penalty', 'mahl-manager' ),
				'team'            => $this->get_link_data( $team_id ),
				'period_number'   => isset( $event['period_number'] ) ? absint( $event['period_number'] ) : 0,
				'event_time'      => isset( $event['event_time'] ) ? sanitize_text_field( $event['event_time'] ) : '',
				'event_order'     => isset( $event['event_order'] ) ? absint( $event['event_order'] ) : 0,
				'penalty_minutes' => isset( $event['penalty_minutes'] ) ? absint( $event['penalty_minutes'] ) : 0,
				'label'           => isset( $event['label'] ) ? sanitize_text_field( $event['label'] ) : '',
				'scorer'          => array(),
				'assists'         => array(),
				'penalized_player'=> array(),
			);

			if ( ! empty( $event['participants'] ) && is_array( $event['participants'] ) ) {
				foreach ( $event['participants'] as $participant ) {
					if ( empty( $participant['player_id'] ) || empty( $participant['role'] ) ) {
						continue;
					}

					$player_link = $this->get_link_data( absint( $participant['player_id'] ) );
					$role        = sanitize_key( $participant['role'] );

					if ( 'scorer' === $role ) {
						$entry['scorer'] = $player_link;
					} elseif ( 'assist' === $role ) {
						$entry['assists'][] = $player_link;
					} elseif ( 'penalized_player' === $role ) {
						$entry['penalized_player'] = $player_link;
					}
				}
			}

			$entries[] = $entry;
		}

		usort(
			$entries,
			array( $this, 'compare_game_event_entries' )
		);

		return $entries;
	}

	/**
	 * Return period score rows for a game.
	 *
	 * @param int $game_id Game post ID.
	 * @param int $period_count Configured period count.
	 * @return array
	 */
	protected function get_period_scores( $game_id, $period_count ) {
		$rows = array();

		for ( $period = 1; $period <= max( 3, absint( $period_count ) ); $period++ ) {
			$home_score = get_post_meta( $game_id, '_mahl_score_home_period_' . $period, true );
			$away_score = get_post_meta( $game_id, '_mahl_score_away_period_' . $period, true );

			if ( '' === $home_score && '' === $away_score ) {
				continue;
			}

			$rows[] = array(
				'label'      => sprintf(
					/* translators: %d: period number. */
					__( 'Period %d', 'mahl-manager' ),
					$period
				),
				'home_score' => '' !== $home_score ? absint( $home_score ) : null,
				'away_score' => '' !== $away_score ? absint( $away_score ) : null,
			);
		}

		return $rows;
	}

	/**
	 * Return phase posts for a season.
	 *
	 * @param int $season_id Season post ID.
	 * @return array
	 */
	protected function get_phase_posts_for_season( $season_id ) {
		return get_posts(
			array(
				'post_type'      => 'mahl_phase',
				'post_status'    => array( 'publish', 'future', 'draft', 'pending', 'private' ),
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'no_found_rows'  => true,
				'meta_query'     => array(
					array(
						'key'     => '_mahl_season_id',
						'value'   => $season_id,
						'compare' => '=',
						'type'    => 'NUMERIC',
					),
				), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			)
		);
	}

	/**
	 * Return team posts for a season.
	 *
	 * @param int $season_id Season post ID.
	 * @return array
	 */
	protected function get_team_posts_for_season( $season_id ) {
		return get_posts(
			array(
				'post_type'      => 'mahl_team',
				'post_status'    => array( 'publish', 'future', 'draft', 'pending', 'private' ),
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_query'     => array(
					array(
						'key'     => '_mahl_season_id',
						'value'   => $season_id,
						'compare' => '=',
						'type'    => 'NUMERIC',
					),
				), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			)
		);
	}

	/**
	 * Return game posts for a season.
	 *
	 * @param int $season_id Season post ID.
	 * @return array
	 */
	protected function get_game_posts_for_season( $season_id ) {
		return $this->get_game_posts_for_context( $season_id, 0 );
	}

	/**
	 * Return game posts for a season and optional phase.
	 *
	 * @param int $season_id Season post ID.
	 * @param int $phase_id Optional phase post ID.
	 * @return array
	 */
	protected function get_game_posts_for_context( $season_id, $phase_id = 0 ) {
		$meta_query = array();

		if ( ! empty( $season_id ) ) {
			$meta_query[] = array(
				'key'     => '_mahl_season_id',
				'value'   => $season_id,
				'compare' => '=',
				'type'    => 'NUMERIC',
			);
		}

		if ( ! empty( $phase_id ) ) {
			$meta_query[] = array(
				'key'     => '_mahl_phase_id',
				'value'   => $phase_id,
				'compare' => '=',
				'type'    => 'NUMERIC',
			);
		}

		$games = get_posts(
			array(
				'post_type'      => 'mahl_game',
				'post_status'    => array( 'publish', 'future', 'draft', 'pending', 'private' ),
				'posts_per_page' => -1,
				'orderby'        => 'date',
				'order'          => 'ASC',
				'no_found_rows'  => true,
				'meta_query'     => $meta_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			)
		);

		usort(
			$games,
			array( $this, 'compare_game_posts' )
		);

		return $games;
	}

	/**
	 * Return roster posts for a team.
	 *
	 * @param int $team_id Team post ID.
	 * @return array
	 */
	protected function get_roster_for_team( $team_id ) {
		return get_posts(
			array(
				'post_type'      => 'mahl_player',
				'post_status'    => array( 'publish', 'future', 'draft', 'pending', 'private' ),
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'no_found_rows'  => true,
				'meta_query'     => array(
					array(
						'key'     => '_mahl_team_id',
						'value'   => $team_id,
						'compare' => '=',
						'type'    => 'NUMERIC',
					),
				), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			)
		);
	}

	/**
	 * Return game posts for a team.
	 *
	 * @param int $team_id Team post ID.
	 * @return array
	 */
	protected function get_games_for_team( $team_id ) {
		$games = get_posts(
			array(
				'post_type'      => 'mahl_game',
				'post_status'    => array( 'publish', 'future', 'draft', 'pending', 'private' ),
				'posts_per_page' => -1,
				'orderby'        => 'date',
				'order'          => 'ASC',
				'no_found_rows'  => true,
				'meta_query'     => array(
					'relation' => 'OR',
					array(
						'key'     => '_mahl_home_team_id',
						'value'   => $team_id,
						'compare' => '=',
						'type'    => 'NUMERIC',
					),
					array(
						'key'     => '_mahl_away_team_id',
						'value'   => $team_id,
						'compare' => '=',
						'type'    => 'NUMERIC',
					),
				), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			)
		);

		usort(
			$games,
			array( $this, 'compare_game_posts' )
		);

		return $games;
	}

	/**
	 * Build a list of player link entries.
	 *
	 * @param WP_Post $player Player post object.
	 * @return array
	 */
	protected function build_player_link_entry( $player ) {
		return $this->get_link_data( $player->ID );
	}

	/**
	 * Build game list entries from posts.
	 *
	 * @param array $games Game posts.
	 * @return array
	 */
	protected function build_game_list( $games ) {
		return array_map(
			array( $this, 'build_game_post_entry' ),
			$games
		);
	}

	/**
	 * Build a game entry from a post object.
	 *
	 * @param WP_Post $game Game post object.
	 * @return array
	 */
	protected function build_game_post_entry( $game ) {
		return $this->build_game_list_item( $game->ID );
	}

	/**
	 * Build a normalized game list item.
	 *
	 * @param int $game_id Game post ID.
	 * @return array
	 */
	protected function build_game_list_item( $game_id ) {
		$season_id        = absint( get_post_meta( $game_id, '_mahl_season_id', true ) );
		$phase_id         = absint( get_post_meta( $game_id, '_mahl_phase_id', true ) );
		$home_team_id     = absint( get_post_meta( $game_id, '_mahl_home_team_id', true ) );
		$away_team_id     = absint( get_post_meta( $game_id, '_mahl_away_team_id', true ) );
		$home_score_final = get_post_meta( $game_id, '_mahl_score_home_final', true );
		$away_score_final = get_post_meta( $game_id, '_mahl_score_away_final', true );

		return array(
			'id'           => $game_id,
			'title'        => get_the_title( $game_id ),
			'permalink'    => get_permalink( $game_id ),
			'season'       => $this->get_link_data( $season_id ),
			'phase'        => $this->get_link_data( $phase_id ),
			'home_team'    => $this->get_link_data( $home_team_id ),
			'away_team'    => $this->get_link_data( $away_team_id ),
			'status'       => sanitize_key( get_post_meta( $game_id, '_mahl_status', true ) ),
			'status_label' => $this->format_status_label( get_post_meta( $game_id, '_mahl_status', true ) ),
			'match_date'   => $this->format_match_date( get_post_meta( $game_id, '_mahl_match_date', true ) ),
			'match_time'   => $this->format_match_time( get_post_meta( $game_id, '_mahl_match_time', true ) ),
			'group_key'    => sanitize_key( get_post_meta( $game_id, '_mahl_group_key', true ) ),
			'score_text'   => ( '' !== $home_score_final && '' !== $away_score_final ) ? $home_score_final . ' : ' . $away_score_final : '',
		);
	}

	/**
	 * Return a linked post data structure.
	 *
	 * @param int $post_id Related post ID.
	 * @return array
	 */
	protected function get_link_data( $post_id ) {
		$post_id = absint( $post_id );

		if ( empty( $post_id ) || ! get_post( $post_id ) ) {
			return array();
		}

		return array(
			'id'        => $post_id,
			'title'     => get_the_title( $post_id ),
			'permalink' => get_permalink( $post_id ),
		);
	}

	/**
	 * Return post content prepared for frontend rendering.
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	protected function get_post_content( $post_id ) {
		$content = get_post_field( 'post_content', $post_id );

		if ( empty( $content ) ) {
			return '';
		}

		return apply_filters( 'the_content', $content );
	}

	/**
	 * Return unique group keys from a game list.
	 *
	 * @param array $games Game posts.
	 * @return array
	 */
	protected function get_unique_group_keys_from_games( $games ) {
		$keys = array();

		foreach ( $games as $game ) {
			$group_key = sanitize_key( get_post_meta( $game->ID, '_mahl_group_key', true ) );

			if ( '' !== $group_key ) {
				$keys[ $group_key ] = $this->format_group_key_label( $group_key );
			}
		}

		return array_values( $keys );
	}

	/**
	 * Return a team standing row from standings rows.
	 *
	 * @param array $rows Standings rows.
	 * @param int   $team_id Team post ID.
	 * @return array
	 */
	protected function find_team_standing_row( $rows, $team_id ) {
		foreach ( $rows as $row ) {
			if ( absint( $row['team_id'] ) === absint( $team_id ) ) {
				return $row;
			}
		}

		return array();
	}

	/**
	 * Compare two game posts by match schedule.
	 *
	 * @param WP_Post $left Left game post.
	 * @param WP_Post $right Right game post.
	 * @return int
	 */
	protected function compare_game_posts( $left, $right ) {
		return $this->compare_schedule_values( $left->ID, $right->ID );
	}

	/**
	 * Compare two game list entries by schedule.
	 *
	 * @param array $left Left game entry.
	 * @param array $right Right game entry.
	 * @return int
	 */
	protected function compare_game_entries( $left, $right ) {
		return $this->compare_schedule_values( $left['id'], $right['id'] );
	}

	/**
	 * Compare two game event entries.
	 *
	 * @param array $left Left event entry.
	 * @param array $right Right event entry.
	 * @return int
	 */
	protected function compare_game_event_entries( $left, $right ) {
		$period_comparison = absint( $left['period_number'] ) - absint( $right['period_number'] );

		if ( 0 !== $period_comparison ) {
			return $period_comparison;
		}

		$left_time  = $this->get_event_time_seconds( $left['event_time'] );
		$right_time = $this->get_event_time_seconds( $right['event_time'] );

		if ( $left_time !== $right_time ) {
			return $left_time - $right_time;
		}

		return absint( $left['event_order'] ) - absint( $right['event_order'] );
	}

	/**
	 * Compare two game schedule values.
	 *
	 * @param int $left_game_id Left game post ID.
	 * @param int $right_game_id Right game post ID.
	 * @return int
	 */
	protected function compare_schedule_values( $left_game_id, $right_game_id ) {
		$left_date  = get_post_meta( $left_game_id, '_mahl_match_date', true );
		$right_date = get_post_meta( $right_game_id, '_mahl_match_date', true );

		if ( $left_date !== $right_date ) {
			return strcmp( (string) $left_date, (string) $right_date );
		}

		$left_time  = get_post_meta( $left_game_id, '_mahl_match_time', true );
		$right_time = get_post_meta( $right_game_id, '_mahl_match_time', true );

		if ( $left_time !== $right_time ) {
			return strcmp( (string) $left_time, (string) $right_time );
		}

		return absint( $left_game_id ) - absint( $right_game_id );
	}

	/**
	 * Sort game list entries by schedule.
	 *
	 * @param array $games Game entries.
	 * @return array
	 */
	protected function sort_game_entries( $games ) {
		usort(
			$games,
			array( $this, 'compare_game_entries' )
		);

		return $games;
	}

	/**
	 * Format a status key for display.
	 *
	 * @param string $status Status key.
	 * @return string
	 */
	protected function format_status_label( $status ) {
		$status = sanitize_key( $status );

		if ( empty( $status ) ) {
			return '';
		}

		return ucwords( str_replace( '_', ' ', $status ) );
	}

	/**
	 * Format a group key for display.
	 *
	 * @param string $group_key Group key.
	 * @return string
	 */
	protected function format_group_key_label( $group_key ) {
		$group_key = sanitize_key( $group_key );

		if ( empty( $group_key ) ) {
			return '';
		}

		return ucwords( str_replace( array( '-', '_' ), ' ', $group_key ) );
	}

	/**
	 * Format a stored match date for display.
	 *
	 * @param string $date Raw stored date.
	 * @return string
	 */
	protected function format_match_date( $date ) {
		$date = sanitize_text_field( $date );

		if ( empty( $date ) ) {
			return '';
		}

		$timestamp = strtotime( $date );

		if ( false === $timestamp ) {
			return $date;
		}

		return wp_date( get_option( 'date_format' ), $timestamp );
	}

	/**
	 * Format a stored match time for display.
	 *
	 * @param string $time Raw stored time.
	 * @return string
	 */
	protected function format_match_time( $time ) {
		$time = sanitize_text_field( $time );

		if ( empty( $time ) ) {
			return '';
		}

		$timestamp = strtotime( $time );

		if ( false === $timestamp ) {
			return $time;
		}

		return wp_date( get_option( 'time_format' ), $timestamp );
	}

	/**
	 * Convert an event time value to sortable seconds.
	 *
	 * @param string $event_time Raw event time.
	 * @return int
	 */
	protected function get_event_time_seconds( $event_time ) {
		$event_time = sanitize_text_field( $event_time );

		if ( empty( $event_time ) || ! preg_match( '/^(\d{1,2}):(\d{2})$/', $event_time, $matches ) ) {
			return 999999;
		}

		return ( absint( $matches[1] ) * 60 ) + absint( $matches[2] );
	}
}
