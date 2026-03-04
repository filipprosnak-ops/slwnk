<?php
/**
 * Player stats calculator.
 *
 * @package MAHLLeague
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ML_Player_Stats_Calculator
 */
class ML_Player_Stats_Calculator {

	/**
	 * Calculate player totals from match-level player stats.
	 *
	 * Expected match meta key: ml_player_stats
	 * Shape:
	 * [
	 *   [ 'player_id' => 123, 'goals' => 2, 'assists' => 1 ],
	 * ]
	 *
	 * @param array $matches Match IDs.
	 *
	 * @return array
	 */
	public static function calculate( array $matches ): array {
		$players = array();

		foreach ( $matches as $match_id ) {
			$status = (string) get_post_meta( $match_id, 'ml_status', true );
			if ( 'played' !== $status ) {
				continue;
			}

			$entries = get_post_meta( $match_id, 'ml_player_stats', true );
			if ( ! is_array( $entries ) ) {
				continue;
			}

			foreach ( $entries as $entry ) {
				if ( ! is_array( $entry ) ) {
					continue;
				}

				$player_id = isset( $entry['player_id'] ) ? absint( $entry['player_id'] ) : 0;
				if ( $player_id <= 0 ) {
					continue;
				}

				if ( ! isset( $players[ $player_id ] ) ) {
					$players[ $player_id ] = self::empty_row( $player_id );
				}

				$goals   = isset( $entry['goals'] ) ? absint( $entry['goals'] ) : 0;
				$assists = isset( $entry['assists'] ) ? absint( $entry['assists'] ) : 0;

				$players[ $player_id ]['goals'] += $goals;
				$players[ $player_id ]['assists'] += $assists;
				$players[ $player_id ]['points'] += ( $goals + $assists );
			}
		}

		usort(
			$players,
			static function ( array $a, array $b ): int {
				if ( $a['points'] !== $b['points'] ) {
					return $b['points'] <=> $a['points'];
				}

				if ( $a['goals'] !== $b['goals'] ) {
					return $b['goals'] <=> $a['goals'];
				}

				return strcasecmp( $a['player_name'], $b['player_name'] );
			}
		);

		return array_values( $players );
	}

	/**
	 * Empty row for player stats.
	 *
	 * @param int $player_id Player ID.
	 *
	 * @return array
	 */
	private static function empty_row( int $player_id ): array {
		return array(
			'player_id'   => $player_id,
			'player_name' => get_the_title( $player_id ),
			'goals'       => 0,
			'assists'     => 0,
			'points'      => 0,
		);
	}
}
