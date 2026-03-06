<?php
/**
 * Settings service.
 *
 * @package MAHLManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provide access to league settings stored in options.
 */
class MAHL_Settings_Service extends MAHL_Base_Service {

	/**
	 * League settings option name.
	 *
	 * @var string
	 */
	const OPTION_NAME = 'mahl_league_settings';

	/**
	 * Return all settings merged with defaults.
	 *
	 * @return array
	 */
	public static function get_settings() {
		$stored_settings = get_option( self::OPTION_NAME, array() );

		if ( ! is_array( $stored_settings ) ) {
			$stored_settings = array();
		}

		return self::merge_settings( self::get_defaults(), $stored_settings );
	}

	/**
	 * Return the default league settings.
	 *
	 * @return array
	 */
	public static function get_defaults() {
		return array(
			'game_format'     => array(
				'period_count'          => 2,
				'period_length_minutes' => 20,
			),
			'standings_rules' => array(
				'win_points'  => 2,
				'draw_points' => 1,
				'loss_points' => 0,
			),
			'game_rules'      => array(
				'allow_draw_regular_season'         => 1,
				'overtime_allowed_regular_season'   => 0,
				'shootout_allowed_regular_season'   => 0,
			),
			'playoff_rules'   => array(
				'overtime_allowed_playoff' => 0,
				'shootout_allowed_playoff' => 0,
			),
		);
	}

	/**
	 * Return the option name.
	 *
	 * @return string
	 */
	public static function get_option_name() {
		return self::OPTION_NAME;
	}

	/**
	 * Return game format settings.
	 *
	 * @return array
	 */
	public static function get_game_format() {
		$settings = self::get_settings();

		return $settings['game_format'];
	}

	/**
	 * Return standings rule settings.
	 *
	 * @return array
	 */
	public static function get_standings_rules() {
		$settings = self::get_settings();

		return $settings['standings_rules'];
	}

	/**
	 * Return regular season game rules.
	 *
	 * @return array
	 */
	public static function get_regular_season_rules() {
		$settings = self::get_settings();

		return array(
			'allow_draw'       => (bool) $settings['game_rules']['allow_draw_regular_season'],
			'overtime_allowed' => (bool) $settings['game_rules']['overtime_allowed_regular_season'],
			'shootout_allowed' => (bool) $settings['game_rules']['shootout_allowed_regular_season'],
		);
	}

	/**
	 * Return playoff game rules.
	 *
	 * @return array
	 */
	public static function get_playoff_rules() {
		$settings = self::get_settings();

		return array(
			'overtime_allowed' => (bool) $settings['playoff_rules']['overtime_allowed_playoff'],
			'shootout_allowed' => (bool) $settings['playoff_rules']['shootout_allowed_playoff'],
		);
	}

	/**
	 * Merge stored settings into defaults recursively.
	 *
	 * @param array $defaults Default settings.
	 * @param array $stored_settings Stored settings.
	 * @return array
	 */
	protected static function merge_settings( $defaults, $stored_settings ) {
		foreach ( $defaults as $key => $default_value ) {
			if ( ! array_key_exists( $key, $stored_settings ) ) {
				$stored_settings[ $key ] = $default_value;
				continue;
			}

			if ( is_array( $default_value ) && is_array( $stored_settings[ $key ] ) ) {
				$stored_settings[ $key ] = self::merge_settings( $default_value, $stored_settings[ $key ] );
			}
		}

		return $stored_settings;
	}
}
