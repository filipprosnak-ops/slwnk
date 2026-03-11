<?php
/**
 * Database schema builder.
 *
 * @package MAHL_Stats
 */

if (!defined('ABSPATH')) {
	exit;
}

class MAHL_Stats_Schema
{
	/**
	 * Get SQL statements for dbDelta.
	 *
	 * @return array<int, string>
	 */
	public static function get_schema(): array
	{
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$tables = MAHL_Stats_DB::tables();

		return [

			"CREATE TABLE {$tables['seasons']} (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				name VARCHAR(191) NOT NULL,
				slug VARCHAR(191) NOT NULL,
				start_date DATE NULL,
				end_date DATE NULL,
				status VARCHAR(50) NOT NULL DEFAULT 'draft',
				created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				UNIQUE KEY slug (slug)
			) {$charset_collate};",

			"CREATE TABLE {$tables['phases']} (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				season_id BIGINT UNSIGNED NOT NULL,
				name VARCHAR(191) NOT NULL,
				slug VARCHAR(191) NOT NULL,
				type VARCHAR(50) NOT NULL DEFAULT 'regular',
				sort_order INT UNSIGNED NOT NULL DEFAULT 0,
				created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				KEY season_id (season_id),
				KEY slug (slug)
			) {$charset_collate};",

			"CREATE TABLE {$tables['rounds']} (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				phase_id BIGINT UNSIGNED NOT NULL,
				name VARCHAR(191) NOT NULL,
				slug VARCHAR(191) NOT NULL,
				sort_order INT UNSIGNED NOT NULL DEFAULT 0,
				created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				KEY phase_id (phase_id),
				KEY slug (slug)
			) {$charset_collate};",

			"CREATE TABLE {$tables['teams']} (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				name VARCHAR(191) NOT NULL,
				slug VARCHAR(191) NOT NULL,
				logo_id BIGINT UNSIGNED NULL,
				city VARCHAR(191) NULL,
				founded_year SMALLINT UNSIGNED NULL,
				created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				UNIQUE KEY slug (slug)
			) {$charset_collate};",

			"CREATE TABLE {$tables['players']} (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				first_name VARCHAR(191) NOT NULL,
				last_name VARCHAR(191) NOT NULL,
				slug VARCHAR(191) NOT NULL,
				birth_date DATE NULL,
				default_position VARCHAR(10) NOT NULL DEFAULT 'F',
				shoots VARCHAR(1) NOT NULL DEFAULT 'L',
				created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				KEY last_name (last_name),
				KEY first_name (first_name),
				KEY slug (slug)
			) {$charset_collate};",

			"CREATE TABLE {$tables['registrations']} (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				season_id BIGINT UNSIGNED NOT NULL,
				team_id BIGINT UNSIGNED NOT NULL,
				player_id BIGINT UNSIGNED NOT NULL,
				jersey_number VARCHAR(20) NOT NULL,
				position VARCHAR(10) NOT NULL DEFAULT 'F',
				active TINYINT(1) NOT NULL DEFAULT 1,
				created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				KEY season_id (season_id),
				KEY team_id (team_id),
				KEY player_id (player_id),
				UNIQUE KEY season_team_player (season_id, team_id, player_id)
			) {$charset_collate};",

			"CREATE TABLE {$tables['games']} (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				season_id BIGINT UNSIGNED NOT NULL,
				phase_id BIGINT UNSIGNED NOT NULL,
				round_id BIGINT UNSIGNED NULL,
				home_team_id BIGINT UNSIGNED NOT NULL,
				away_team_id BIGINT UNSIGNED NOT NULL,
				venue VARCHAR(191) NULL,
				game_date DATETIME NOT NULL,
				status VARCHAR(50) NOT NULL DEFAULT 'scheduled',
				home_score SMALLINT UNSIGNED NOT NULL DEFAULT 0,
				away_score SMALLINT UNSIGNED NOT NULL DEFAULT 0,
				parts_format VARCHAR(50) NOT NULL DEFAULT 'periods',
				notes LONGTEXT NULL,
				is_forfeit TINYINT(1) NOT NULL DEFAULT 0,
				slug VARCHAR(191) NOT NULL,
				created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				KEY season_id (season_id),
				KEY phase_id (phase_id),
				KEY round_id (round_id),
				KEY home_team_id (home_team_id),
				KEY away_team_id (away_team_id),
				UNIQUE KEY slug (slug)
			) {$charset_collate};",

			"CREATE TABLE {$tables['game_parts']} (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				game_id BIGINT UNSIGNED NOT NULL,
				part_number SMALLINT UNSIGNED NOT NULL,
				part_label VARCHAR(100) NOT NULL,
				home_score SMALLINT UNSIGNED NOT NULL DEFAULT 0,
				away_score SMALLINT UNSIGNED NOT NULL DEFAULT 0,
				PRIMARY KEY (id),
				KEY game_id (game_id),
				UNIQUE KEY game_part (game_id, part_number)
			) {$charset_collate};",

			"CREATE TABLE {$tables['game_rosters']} (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				game_id BIGINT UNSIGNED NOT NULL,
				team_id BIGINT UNSIGNED NOT NULL,
				player_id BIGINT UNSIGNED NOT NULL,
				registration_id BIGINT UNSIGNED NULL,
				role VARCHAR(20) NOT NULL DEFAULT 'player',
				jersey_number VARCHAR(20) NULL,
				PRIMARY KEY (id),
				KEY game_id (game_id),
				KEY team_id (team_id),
				KEY player_id (player_id),
				KEY registration_id (registration_id),
				UNIQUE KEY game_team_player (game_id, team_id, player_id)
			) {$charset_collate};",

			"CREATE TABLE {$tables['game_goals']} (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				game_id BIGINT UNSIGNED NOT NULL,
				team_id BIGINT UNSIGNED NOT NULL,
				scorer_player_id BIGINT UNSIGNED NOT NULL,
				assist_1_player_id BIGINT UNSIGNED NULL,
				assist_2_player_id BIGINT UNSIGNED NULL,
				part_number SMALLINT UNSIGNED NOT NULL DEFAULT 1,
				minute SMALLINT UNSIGNED NULL,
				second SMALLINT UNSIGNED NULL,
				strength VARCHAR(30) NOT NULL DEFAULT 'even',
				is_game_winning_goal TINYINT(1) NOT NULL DEFAULT 0,
				created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				KEY game_id (game_id),
				KEY team_id (team_id),
				KEY scorer_player_id (scorer_player_id)
			) {$charset_collate};",

			"CREATE TABLE {$tables['game_penalties']} (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				game_id BIGINT UNSIGNED NOT NULL,
				team_id BIGINT UNSIGNED NOT NULL,
				player_id BIGINT UNSIGNED NOT NULL,
				part_number SMALLINT UNSIGNED NOT NULL DEFAULT 1,
				minute SMALLINT UNSIGNED NULL,
				second SMALLINT UNSIGNED NULL,
				penalty_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 2,
				reason VARCHAR(191) NULL,
				created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				KEY game_id (game_id),
				KEY team_id (team_id),
				KEY player_id (player_id)
			) {$charset_collate};",

			"CREATE TABLE {$tables['game_goalies']} (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				game_id BIGINT UNSIGNED NOT NULL,
				team_id BIGINT UNSIGNED NOT NULL,
				player_id BIGINT UNSIGNED NOT NULL,
				minutes_played SMALLINT UNSIGNED NULL,
				goals_allowed SMALLINT UNSIGNED NOT NULL DEFAULT 0,
				saves SMALLINT UNSIGNED NULL,
				shots_against SMALLINT UNSIGNED NULL,
				shutout TINYINT(1) NOT NULL DEFAULT 0,
				PRIMARY KEY (id),
				KEY game_id (game_id),
				KEY team_id (team_id),
				KEY player_id (player_id),
				UNIQUE KEY game_team_goalie (game_id, team_id, player_id)
			) {$charset_collate};",

			"CREATE TABLE {$tables['game_officials']} (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				game_id BIGINT UNSIGNED NOT NULL,
				official_name VARCHAR(191) NOT NULL,
				role VARCHAR(50) NOT NULL DEFAULT 'referee',
				sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
				PRIMARY KEY (id),
				KEY game_id (game_id)
			) {$charset_collate};",

			"CREATE TABLE {$tables['game_notes']} (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				game_id BIGINT UNSIGNED NOT NULL,
				note_type VARCHAR(50) NOT NULL DEFAULT 'general',
				content LONGTEXT NOT NULL,
				created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				KEY game_id (game_id),
				KEY note_type (note_type)
			) {$charset_collate};",
		];
	}
}