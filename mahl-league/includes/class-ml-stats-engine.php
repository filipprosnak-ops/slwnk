<?php
/**
 * Stats engine orchestration.
 *
 * @package MAHLLeague
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/class-ml-cache.php';
require_once __DIR__ . '/class-ml-standings-calculator.php';
require_once __DIR__ . '/class-ml-team-stats-calculator.php';
require_once __DIR__ . '/class-ml-player-stats-calculator.php';

/**
 * Class ML_Stats_Engine
 */
class ML_Stats_Engine {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_action( 'save_post_ml_match', array( self::class, 'handle_match_change' ), 10, 3 );
		add_action( 'save_post_ml_team', array( self::class, 'handle_team_change' ), 10, 3 );
		add_action( 'before_delete_post', array( self::class, 'handle_before_delete_post' ) );
	}


	/**
	 * Rebuild teams cache when team changes.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @param bool    $update  Update flag.
	 *
	 * @return void
	 */
	public static function handle_team_change( int $post_id, WP_Post $post, bool $update ): void {
		unset( $update );

		if ( wp_is_post_revision( $post_id ) || 'ml_team' !== $post->post_type ) {
			return;
		}

		self::rebuild_teams_view_cache();
	}

	/**
	 * Recompute when match changes.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @param bool    $update  True when update.
	 *
	 * @return void
	 */
	public static function handle_match_change( int $post_id, WP_Post $post, bool $update ): void {
		unset( $post, $update );

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		$pair = self::get_match_term_pair( $post_id );
		if ( empty( $pair ) ) {
			return;
		}

		self::recompute_pair( (int) $pair['season_id'], (int) $pair['competition_id'] );
	}

	/**
	 * Handle pre-delete lifecycle for league posts.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return void
	 */
	public static function handle_before_delete_post( int $post_id ): void {
		$post_type = get_post_type( $post_id );

		if ( 'ml_team' === $post_type ) {
			ML_Cache::invalidate_teams_view();
			return;
		}

		if ( 'ml_player' === $post_type ) {
			return;
		}

		if ( 'ml_match' !== $post_type ) {
			return;
		}

		$pair = self::get_match_term_pair( $post_id );
		if ( empty( $pair ) ) {
			return;
		}

		$season_id      = (int) $pair['season_id'];
		$competition_id = (int) $pair['competition_id'];

		ML_Cache::invalidate( $season_id, $competition_id );
		self::recompute_pair( $season_id, $competition_id );
	}

	/**
	 * Manual recompute for all season/competition combinations.
	 *
	 * @return int Number of recomputed combinations.
	 */
	public static function recompute_all(): int {
		$seasons      = get_terms( array( 'taxonomy' => 'ml_season', 'hide_empty' => false ) );
		$competitions = get_terms( array( 'taxonomy' => 'ml_competition', 'hide_empty' => false ) );

		if ( is_wp_error( $seasons ) || is_wp_error( $competitions ) ) {
			return 0;
		}

		$count = 0;
		foreach ( $seasons as $season ) {
			foreach ( $competitions as $competition ) {
				self::recompute_pair( (int) $season->term_id, (int) $competition->term_id );
				++$count;
			}
		}

		return $count;
	}

	/**
	 * Recompute all stats for a single season/competition pair.
	 *
	 * @param int $season_id      Season ID.
	 * @param int $competition_id Competition ID.
	 *
	 * @return void
	 */
	public static function recompute_pair( int $season_id, int $competition_id ): void {
		if ( $season_id <= 0 || $competition_id <= 0 ) {
			return;
		}

		$matches = self::query_matches( $season_id, $competition_id );

		ML_Cache::invalidate( $season_id, $competition_id );
		ML_Cache::set_standings( $season_id, $competition_id, ML_Standings_Calculator::calculate( $matches ) );
		ML_Cache::set_stats(
			$season_id,
			$competition_id,
			array(
				'team_totals'   => ML_Team_Stats_Calculator::calculate( $matches ),
				'player_totals' => ML_Player_Stats_Calculator::calculate( $matches ),
			)
		);

		$fixtures = self::build_match_view_model( $matches, 'fixtures' );
		$results  = self::build_match_view_model( $matches, 'results' );
		$playoffs = self::build_match_view_model( $matches, 'playoffs' );

		ML_Cache::set_fixtures( $season_id, $competition_id, $fixtures );
		ML_Cache::set_results( $season_id, $competition_id, $results );
		ML_Cache::set_playoffs( $season_id, $competition_id, $playoffs );
	}


	/**
	 * Build and store teams view cache.
	 *
	 * @return void
	 */
	public static function rebuild_teams_view_cache(): void {
		$teams = get_posts(
			array(
				'post_type'      => 'ml_team',
				'post_status'    => array( 'publish', 'draft' ),
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		$view = array();
		foreach ( $teams as $team ) {
			$view[] = array(
				'id'    => (int) $team->ID,
				'title' => (string) $team->post_title,
				'url'   => (string) get_permalink( $team->ID ),
			);
		}

		ML_Cache::set_teams_view( $view );
	}

	/**
	 * Build match view model from cached match IDs.
	 *
	 * @param array  $matches Match IDs.
	 * @param string $mode    View mode.
	 *
	 * @return array
	 */
	private static function build_match_view_model( array $matches, string $mode ): array {
		$view = array();
		foreach ( $matches as $match_id ) {
			$status = (string) get_post_meta( $match_id, 'ml_status', true );
			if ( 'results' === $mode && 'played' !== $status ) {
				continue;
			}
			if ( 'fixtures' === $mode && 'played' === $status ) {
				continue;
			}
			if ( 'playoffs' === $mode ) {
				$phases = wp_get_post_terms( $match_id, 'ml_phase', array( 'fields' => 'ids' ) );
				if ( is_wp_error( $phases ) || empty( $phases ) ) {
					continue;
				}
			}

			$home_team_id = absint( get_post_meta( $match_id, 'ml_home_team_id', true ) );
			$away_team_id = absint( get_post_meta( $match_id, 'ml_away_team_id', true ) );
			$view[]       = array(
				'id'         => (int) $match_id,
				'title'      => (string) get_the_title( $match_id ),
				'url'        => (string) get_permalink( $match_id ),
				'home_name'  => $home_team_id > 0 ? (string) get_the_title( $home_team_id ) : (string) __( 'TBD', 'mahl-league' ),
				'away_name'  => $away_team_id > 0 ? (string) get_the_title( $away_team_id ) : (string) __( 'TBD', 'mahl-league' ),
				'datetime'   => (string) get_post_meta( $match_id, 'ml_match_datetime', true ),
				'home_score' => absint( get_post_meta( $match_id, 'ml_home_score', true ) ),
				'away_score' => absint( get_post_meta( $match_id, 'ml_away_score', true ) ),
				'status'     => $status,
			);
		}

		if ( 'results' === $mode ) {
			usort(
				$view,
				static function ( array $a, array $b ): int {
					return strcmp( (string) $b['datetime'], (string) $a['datetime'] );
				}
			);
		} else {
			usort(
				$view,
				static function ( array $a, array $b ): int {
					return strcmp( (string) $a['datetime'], (string) $b['datetime'] );
				}
			);
		}

		return $view;
	}

	/**
	 * Query matches for season and competition.
	 *
	 * @param int $season_id      Season term ID.
	 * @param int $competition_id Competition term ID.
	 *
	 * @return array
	 */
	private static function query_matches( int $season_id, int $competition_id ): array {
		$query = get_posts(
			array(
				'post_type'      => 'ml_match',
				'post_status'    => array( 'publish', 'draft', 'future', 'pending' ),
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'tax_query'      => array(
					'relation' => 'AND',
					array(
						'taxonomy' => 'ml_season',
						'field'    => 'term_id',
						'terms'    => $season_id,
					),
					array(
						'taxonomy' => 'ml_competition',
						'field'    => 'term_id',
						'terms'    => $competition_id,
					),
				),
			)
		);

		return is_array( $query ) ? array_map( 'absint', $query ) : array();
	}

	/**
	 * Get season/competition pair assigned to match.
	 *
	 * @param int $match_id Match ID.
	 *
	 * @return array
	 */
	private static function get_match_term_pair( int $match_id ): array {
		$seasons = wp_get_post_terms( $match_id, 'ml_season', array( 'fields' => 'ids' ) );
		$comps   = wp_get_post_terms( $match_id, 'ml_competition', array( 'fields' => 'ids' ) );

		if ( is_wp_error( $seasons ) || is_wp_error( $comps ) || empty( $seasons ) || empty( $comps ) ) {
			return array();
		}

		return array(
			'season_id'      => absint( $seasons[0] ),
			'competition_id' => absint( $comps[0] ),
		);
	}
}
