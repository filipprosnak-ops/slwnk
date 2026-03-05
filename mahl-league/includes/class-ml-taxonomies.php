<?php
/**
 * Register league taxonomies.
 *
 * @package MAHLLeague
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ML_Taxonomies
 */
class ML_Taxonomies {

	/**
	 * Register plugin taxonomies.
	 *
	 * @return void
	 */
	public static function register(): void {
		self::register_season();
		self::register_competition();
		self::register_phase();
	}

	/**
	 * Register season taxonomy.
	 *
	 * @return void
	 */
	private static function register_season(): void {
		register_taxonomy(
			'ml_season',
			array( 'ml_team', 'ml_player', 'ml_match' ),
			array(
				'label'             => __( 'Seasons', 'mahl-league' ),
				'public'            => true,
				'show_in_rest'      => true,
				'hierarchical'      => true,
				'show_admin_column' => true,
			)
		);
	}

	/**
	 * Register competition taxonomy.
	 *
	 * @return void
	 */
	private static function register_competition(): void {
		register_taxonomy(
			'ml_competition',
			array( 'ml_team', 'ml_player', 'ml_match' ),
			array(
				'label'             => __( 'Competitions', 'mahl-league' ),
				'public'            => true,
				'show_in_rest'      => true,
				'hierarchical'      => true,
				'show_admin_column' => true,
			)
		);
	}

	/**
	 * Register phase taxonomy.
	 *
	 * @return void
	 */
	private static function register_phase(): void {
		register_taxonomy(
			'ml_phase',
			array( 'ml_match' ),
			array(
				'label'             => __( 'Phases', 'mahl-league' ),
				'public'            => true,
				'show_in_rest'      => true,
				'hierarchical'      => true,
				'show_admin_column' => true,
			)
		);
	}
}
