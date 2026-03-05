<?php
/**
 * Team stats calculator.
 *
 * @package MAHLLeague
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ML_Team_Stats_Calculator
 */
class ML_Team_Stats_Calculator {

	/**
	 * Calculate aggregated team totals.
	 *
	 * @param array $matches Match IDs.
	 *
	 * @return array
	 */
	public static function calculate( array $matches ): array {
		$teams = array();

		foreach ( $matches as $match_id ) {
			$status = (string) get_post_meta( $match_id, 'ml_status', true );
			if ( 'played' !== $status ) {
				continue;
			}

			$home_team = absint( get_post_meta( $match_id, 'ml_home_team_id', true ) );
			$away_team = absint( get_post_meta( $match_id, 'ml_away_team_id', true ) );
			$home_goal = absint( get_post_meta( $match_id, 'ml_home_score', true ) );
			$away_goal = absint( get_post_meta( $match_id, 'ml_away_score', true ) );

			if ( $home_team <= 0 || $away_team <= 0 ) {
				continue;
			}

			if ( ! isset( $teams[ $home_team ] ) ) {
				$teams[ $home_team ] = self::empty_row( $home_team );
			}

			if ( ! isset( $teams[ $away_team ] ) ) {
				$teams[ $away_team ] = self::empty_row( $away_team );
			}

			$teams[ $home_team ]['goals_for']     += $home_goal;
			$teams[ $home_team ]['goals_against'] += $away_goal;
			$teams[ $away_team ]['goals_for']     += $away_goal;
			$teams[ $away_team ]['goals_against'] += $home_goal;
			++$teams[ $home_team ]['matches'];
			++$teams[ $away_team ]['matches'];
		}

		foreach ( $teams as &$team ) {
			$team['goal_diff'] = $team['goals_for'] - $team['goals_against'];
		}
		unset( $team );

		usort(
			$teams,
			static function ( array $a, array $b ): int {
				if ( $a['goals_for'] !== $b['goals_for'] ) {
					return $b['goals_for'] <=> $a['goals_for'];
				}

				return strcasecmp( $a['team_name'], $b['team_name'] );
			}
		);

		return array_values( $teams );
	}

	/**
	 * Empty row for team totals.
	 *
	 * @param int $team_id Team ID.
	 *
	 * @return array
	 */
	private static function empty_row( int $team_id ): array {
		return array(
			'team_id'       => $team_id,
			'team_name'     => get_the_title( $team_id ),
			'matches'       => 0,
			'goals_for'     => 0,
			'goals_against' => 0,
			'goal_diff'     => 0,
		);
	}
}
