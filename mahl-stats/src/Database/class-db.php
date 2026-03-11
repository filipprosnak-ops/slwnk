<?php
/**
 * Database table helper.
 *
 * @package MAHL_Stats
 */

if (!defined('ABSPATH')) {
	exit;
}

class MAHL_Stats_DB
{
	/**
	 * Get prefixed table name.
	 *
	 * @param string $table Table suffix.
	 * @return string
	 */
	public static function table(string $table): string
	{
		global $wpdb;

		return $wpdb->prefix . 'mahl_' . $table;
	}

	/**
	 * Get all plugin tables.
	 *
	 * @return array<string, string>
	 */
	public static function tables(): array
	{
		return [
			'seasons'        => self::table('seasons'),
			'phases'         => self::table('phases'),
			'rounds'         => self::table('rounds'),
			'teams'          => self::table('teams'),
			'players'        => self::table('players'),
			'registrations'  => self::table('registrations'),
			'games'          => self::table('games'),
			'game_parts'     => self::table('game_parts'),
			'game_rosters'   => self::table('game_rosters'),
			'game_goals'     => self::table('game_goals'),
			'game_penalties' => self::table('game_penalties'),
			'game_goalies'   => self::table('game_goalies'),
			'game_officials' => self::table('game_officials'),
			'game_notes'     => self::table('game_notes'),
		];
	}
}