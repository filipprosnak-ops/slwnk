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
	}
}
