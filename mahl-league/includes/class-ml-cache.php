<?php
/**
 * Cache helpers for MAHL League computed data.
 *
 * @package MAHLLeague
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ML_Cache
 */
class ML_Cache {

	/**
	 * Build standings cache key.
	 *
	 * @param int $competition_id Competition term ID.
	 *
	 * @return string
	 */
	public static function get_standings_key( int $competition_id ): string {
		return 'ml_standings_' . $competition_id;
	}

	/**
	 * Build stats cache key.
	 *
	 * @param int $competition_id Competition term ID.
	 *
	 * @return string
	 */
	public static function get_stats_key( int $competition_id ): string {
		return 'ml_stats_' . $competition_id;
	}

	/**
	 * Build fixtures cache key.
	 *
	 * @param int $competition_id Competition term ID.
	 *
	 * @return string
	 */
	public static function get_fixtures_key( int $competition_id ): string {
		return 'ml_fixtures_' . $competition_id;
	}

	/**
	 * Build results cache key.
	 *
	 * @param int $competition_id Competition term ID.
	 *
	 * @return string
	 */
	public static function get_results_key( int $competition_id ): string {
		return 'ml_results_' . $competition_id;
	}

	/**
	 * Build playoffs cache key.
	 *
	 * @param int $competition_id Competition term ID.
	 *
	 * @return string
	 */
	public static function get_playoffs_key( int $competition_id ): string {
		return 'ml_playoffs_' . $competition_id;
	}


	/**
	 * Read standings cache from season term meta.
	 *
	 * @param int $season_id      Season term ID.
	 * @param int $competition_id Competition term ID.
	 *
	 * @return array
	 */
	public static function get_standings( int $season_id, int $competition_id ): array {
		$data = get_term_meta( $season_id, self::get_standings_key( $competition_id ), true );
		return is_array( $data ) ? $data : array();
	}

	/**
	 * Store standings cache in season term meta.
	 *
	 * @param int   $season_id      Season term ID.
	 * @param int   $competition_id Competition term ID.
	 * @param array $data           Standings data.
	 *
	 * @return void
	 */
	public static function set_standings( int $season_id, int $competition_id, array $data ): void {
		update_term_meta( $season_id, self::get_standings_key( $competition_id ), $data );
	}

	/**
	 * Read statistics cache from season term meta.
	 *
	 * @param int $season_id      Season term ID.
	 * @param int $competition_id Competition term ID.
	 *
	 * @return array
	 */
	public static function get_stats( int $season_id, int $competition_id ): array {
		$data = get_term_meta( $season_id, self::get_stats_key( $competition_id ), true );
		return is_array( $data ) ? $data : array();
	}

	/**
	 * Store statistics cache in season term meta.
	 *
	 * @param int   $season_id      Season term ID.
	 * @param int   $competition_id Competition term ID.
	 * @param array $data           Stats data.
	 *
	 * @return void
	 */
	public static function set_stats( int $season_id, int $competition_id, array $data ): void {
		update_term_meta( $season_id, self::get_stats_key( $competition_id ), $data );
	}

	/**
	 * Get cached fixtures view.
	 *
	 * @param int $season_id      Season term ID.
	 * @param int $competition_id Competition term ID.
	 *
	 * @return array
	 */
	public static function get_fixtures( int $season_id, int $competition_id ): array {
		$data = get_term_meta( $season_id, self::get_fixtures_key( $competition_id ), true );
		return is_array( $data ) ? $data : array();
	}

	/**
	 * Set cached fixtures view.
	 *
	 * @param int   $season_id      Season term ID.
	 * @param int   $competition_id Competition term ID.
	 * @param array $data           Fixtures data.
	 *
	 * @return void
	 */
	public static function set_fixtures( int $season_id, int $competition_id, array $data ): void {
		update_term_meta( $season_id, self::get_fixtures_key( $competition_id ), $data );
	}

	/**
	 * Get cached results view.
	 *
	 * @param int $season_id      Season term ID.
	 * @param int $competition_id Competition term ID.
	 *
	 * @return array
	 */
	public static function get_results( int $season_id, int $competition_id ): array {
		$data = get_term_meta( $season_id, self::get_results_key( $competition_id ), true );
		return is_array( $data ) ? $data : array();
	}

	/**
	 * Set cached results view.
	 *
	 * @param int   $season_id      Season term ID.
	 * @param int   $competition_id Competition term ID.
	 * @param array $data           Results data.
	 *
	 * @return void
	 */
	public static function set_results( int $season_id, int $competition_id, array $data ): void {
		update_term_meta( $season_id, self::get_results_key( $competition_id ), $data );
	}

	/**
	 * Get cached playoffs view.
	 *
	 * @param int $season_id      Season term ID.
	 * @param int $competition_id Competition term ID.
	 *
	 * @return array
	 */
	public static function get_playoffs( int $season_id, int $competition_id ): array {
		$data = get_term_meta( $season_id, self::get_playoffs_key( $competition_id ), true );
		return is_array( $data ) ? $data : array();
	}

	/**
	 * Set cached playoffs view.
	 *
	 * @param int   $season_id      Season term ID.
	 * @param int   $competition_id Competition term ID.
	 * @param array $data           Playoffs data.
	 *
	 * @return void
	 */
	public static function set_playoffs( int $season_id, int $competition_id, array $data ): void {
		update_term_meta( $season_id, self::get_playoffs_key( $competition_id ), $data );
	}

	/**
	 * Get cached teams view.
	 *
	 * @return array
	 */
	public static function get_teams_view(): array {
		$data = get_option( 'ml_teams_view', array() );
		return is_array( $data ) ? $data : array();
	}

	/**
	 * Set cached teams view.
	 *
	 * @param array $data Teams data.
	 *
	 * @return void
	 */
	public static function set_teams_view( array $data ): void {
		update_option( 'ml_teams_view', $data, false );
	}

	/**
	 * Invalidate teams view cache.
	 *
	 * @return void
	 */
	public static function invalidate_teams_view(): void {
		delete_option( 'ml_teams_view' );
	}


	/**
	 * Invalidate computed caches for given season and competition.
	 *
	 * @param int $season_id      Season term ID.
	 * @param int $competition_id Competition term ID.
	 *
	 * @return void
	 */
	public static function invalidate( int $season_id, int $competition_id ): void {
		delete_term_meta( $season_id, self::get_standings_key( $competition_id ) );
		delete_term_meta( $season_id, self::get_stats_key( $competition_id ) );
		delete_term_meta( $season_id, self::get_fixtures_key( $competition_id ) );
		delete_term_meta( $season_id, self::get_results_key( $competition_id ) );
		delete_term_meta( $season_id, self::get_playoffs_key( $competition_id ) );
	}
}
