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
		add_action( 'deleted_post', array( self::class, 'handle_deleted_match' ) );
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
	 * Recompute when match is deleted.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return void
	 */
	public static function handle_deleted_match( int $post_id ): void {
		if ( 'ml_match' !== get_post_type( $post_id ) ) {
			return;
		}

		$pair = self::get_match_term_pair( $post_id );
		if ( empty( $pair ) ) {
			return;
		}

		self::recompute_pair( (int) $pair['season_id'], (int) $pair['competition_id'] );
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
