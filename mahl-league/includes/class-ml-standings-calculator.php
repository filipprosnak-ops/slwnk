<?php
/**
 * Standings calculator.
 *
 * @package MAHLLeague
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ML_Standings_Calculator
 */
class ML_Standings_Calculator {

	/**
	 * Calculate standings from played matches.
	 *
	 * @param array $matches Match IDs.
	 *
	 * @return array
	 */
	public static function calculate( array $matches ): array {
		$rows = array();

		foreach ( $matches as $match_id ) {
			$home_team = absint( get_post_meta( $match_id, 'ml_home_team_id', true ) );
			$away_team = absint( get_post_meta( $match_id, 'ml_away_team_id', true ) );
			$status    = (string) get_post_meta( $match_id, 'ml_status', true );

			if ( $home_team <= 0 || $away_team <= 0 || 'played' !== $status ) {
				continue;
			}

			$home_score = absint( get_post_meta( $match_id, 'ml_home_score', true ) );
			$away_score = absint( get_post_meta( $match_id, 'ml_away_score', true ) );

			if ( ! isset( $rows[ $home_team ] ) ) {
				$rows[ $home_team ] = self::empty_row( $home_team );
			}

			if ( ! isset( $rows[ $away_team ] ) ) {
				$rows[ $away_team ] = self::empty_row( $away_team );
			}

			++$rows[ $home_team ]['played'];
			++$rows[ $away_team ]['played'];

			$rows[ $home_team ]['gf'] += $home_score;
			$rows[ $home_team ]['ga'] += $away_score;
			$rows[ $away_team ]['gf'] += $away_score;
			$rows[ $away_team ]['ga'] += $home_score;

			if ( $home_score > $away_score ) {
				++$rows[ $home_team ]['wins'];
				++$rows[ $away_team ]['losses'];
				$rows[ $home_team ]['points'] += 3;
			} elseif ( $away_score > $home_score ) {
				++$rows[ $away_team ]['wins'];
				++$rows[ $home_team ]['losses'];
				$rows[ $away_team ]['points'] += 3;
			} else {
				++$rows[ $home_team ]['draws'];
				++$rows[ $away_team ]['draws'];
				++$rows[ $home_team ]['points'];
				++$rows[ $away_team ]['points'];
			}
		}

		foreach ( $rows as &$row ) {
			$row['gd'] = $row['gf'] - $row['ga'];
		}
		unset( $row );

		usort(
			$rows,
			static function ( array $a, array $b ): int {
				if ( $a['points'] !== $b['points'] ) {
					return $b['points'] <=> $a['points'];
				}

				if ( $a['gd'] !== $b['gd'] ) {
					return $b['gd'] <=> $a['gd'];
				}

				if ( $a['gf'] !== $b['gf'] ) {
					return $b['gf'] <=> $a['gf'];
				}

				return strcasecmp( $a['team_name'], $b['team_name'] );
			}
		);

		$position = 1;
		foreach ( $rows as &$row ) {
			$row['position'] = $position;
			++$position;
		}
		unset( $row );

		return array_values( $rows );
	}

	/**
	 * Create empty standings row.
	 *
	 * @param int $team_id Team ID.
	 *
	 * @return array
	 */
	private static function empty_row( int $team_id ): array {
		return array(
			'team_id'   => $team_id,
			'team_name' => get_the_title( $team_id ),
			'played'    => 0,
			'wins'      => 0,
			'draws'     => 0,
			'losses'    => 0,
			'gf'        => 0,
			'ga'        => 0,
			'gd'        => 0,
			'points'    => 0,
			'position'  => 0,
		);
	}
}
