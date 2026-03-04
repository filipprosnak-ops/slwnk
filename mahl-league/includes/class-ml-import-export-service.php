<?php
/**
 * CSV import/export service for MAHL League.
 *
 * @package MAHLLeague
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ML_Import_Export_Service
 */
class ML_Import_Export_Service {

	/**
	 * Parse CSV file with UTF-8 BOM handling and delimiter detection.
	 *
	 * @param string $file_path  CSV file path.
	 * @param string $delimiter  Delimiter (auto|,|;).
	 *
	 * @return array
	 */
	public static function parse_csv( string $file_path, string $delimiter = 'auto' ): array {
		$handle = fopen( $file_path, 'r' );
		if ( false === $handle ) {
			return array(
				'headers' => array(),
				'rows'    => array(),
				'error'   => __( 'Unable to read uploaded CSV file.', 'mahl-league' ),
			);
		}

		$detected_delimiter = self::detect_delimiter( $handle, $delimiter );
		$headers            = fgetcsv( $handle, 0, $detected_delimiter );
		if ( ! is_array( $headers ) || empty( $headers ) ) {
			fclose( $handle );

			return array(
				'headers' => array(),
				'rows'    => array(),
				'error'   => __( 'CSV header row is missing.', 'mahl-league' ),
			);
		}

		$headers = array_map( array( self::class, 'normalize_header' ), $headers );
		if ( isset( $headers[0] ) ) {
			$headers[0] = self::strip_bom( $headers[0] );
		}

		$rows = array();
		while ( ( $data = fgetcsv( $handle, 0, $detected_delimiter ) ) !== false ) {
			if ( 1 === count( $data ) && '' === trim( (string) $data[0] ) ) {
				continue;
			}

			$row = array();
			foreach ( $headers as $index => $header ) {
				$row[ $header ] = isset( $data[ $index ] ) ? self::strip_bom( (string) $data[ $index ] ) : '';
			}
			$rows[] = $row;
		}

		fclose( $handle );

		return array(
			'headers' => $headers,
			'rows'    => $rows,
			'error'   => '',
		);
	}

	/**
	 * Validate CSV headers.
	 *
	 * @param string $entity  Entity type.
	 * @param array  $headers CSV headers.
	 *
	 * @return array
	 */
	public static function validate_headers( string $entity, array $headers ): array {
		$normalized = array_values( array_unique( array_map( 'sanitize_key', $headers ) ) );

		$required = array(
			'teams'   => array( 'name' ),
			'players' => array( 'name' ),
			'matches' => array( 'season', 'competition', 'datetime' ),
		);

		$missing = array();
		foreach ( $required[ $entity ] as $required_header ) {
			if ( ! in_array( $required_header, $normalized, true ) ) {
				$missing[] = $required_header;
			}
		}

		if ( ! empty( $missing ) ) {
			return array(
				'valid'  => false,
				'errors' => array(
					sprintf(
						/* translators: %s: comma separated list of missing headers. */
						__( 'Missing required headers: %s', 'mahl-league' ),
						implode( ', ', $missing )
					),
				),
			);
		}

		if ( 'players' === $entity && ! self::contains_one_of( $normalized, array( 'team_external_id', 'team_slug', 'team_name' ) ) ) {
			return array(
				'valid'  => false,
				'errors' => array( __( 'Players CSV must include one of team_external_id, team_slug, team_name headers.', 'mahl-league' ) ),
			);
		}

		if ( 'matches' === $entity && ! self::contains_one_of( $normalized, array( 'home_team_external_id', 'home_team_slug', 'home_team_name' ) ) ) {
			return array(
				'valid'  => false,
				'errors' => array( __( 'Matches CSV must include one of home_team_external_id, home_team_slug, home_team_name headers.', 'mahl-league' ) ),
			);
		}

		if ( 'matches' === $entity && ! self::contains_one_of( $normalized, array( 'away_team_external_id', 'away_team_slug', 'away_team_name' ) ) ) {
			return array(
				'valid'  => false,
				'errors' => array( __( 'Matches CSV must include one of away_team_external_id, away_team_slug, away_team_name headers.', 'mahl-league' ) ),
			);
		}

		return array(
			'valid'  => true,
			'errors' => array(),
		);
	}

	/**
	 * Sanitize a CSV row.
	 *
	 * @param array $row Row data.
	 *
	 * @return array
	 */
	public static function sanitize_row( array $row ): array {
		$sanitized = array();
		foreach ( $row as $key => $value ) {
			$sanitized[ sanitize_key( (string) $key ) ] = sanitize_text_field( wp_unslash( (string) $value ) );
		}

		return $sanitized;
	}

	/**
	 * Import CSV file.
	 *
	 * @param string $entity  Entity type.
	 * @param string $path    File path.
	 * @param bool   $dry_run Dry run mode.
	 *
	 * @return array
	 */
	public static function import_csv( string $entity, string $path, bool $dry_run ): array {
		$parsed = self::parse_csv( $path );
		if ( '' !== $parsed['error'] ) {
			return self::error_result( $parsed['error'] );
		}

		$validation = self::validate_headers( $entity, $parsed['headers'] );
		if ( ! $validation['valid'] ) {
			return self::error_result( implode( ' ', $validation['errors'] ) );
		}

		$result = array(
			'created'        => 0,
			'updated'        => 0,
			'skipped'        => 0,
			'errors'         => 0,
			'error_messages' => array(),
			'impacted_pairs' => array(),
		);

		foreach ( $parsed['rows'] as $index => $raw_row ) {
			$row_number = $index + 2;
			$row        = self::sanitize_row( $raw_row );

			$upsert_result = self::route_upsert( $entity, $row, $dry_run );
			if ( ! empty( $upsert_result['error'] ) ) {
				++$result['errors'];
				if ( count( $result['error_messages'] ) < 50 ) {
					$result['error_messages'][] = array(
						'row'     => $row_number,
						'message' => $upsert_result['error'],
					);
				}
				continue;
			}

			if ( isset( $upsert_result['status'] ) && isset( $result[ $upsert_result['status'] ] ) ) {
				++$result[ $upsert_result['status'] ];
			}

			if ( isset( $upsert_result['pair_key'] ) ) {
				$result['impacted_pairs'][ $upsert_result['pair_key'] ] = array(
					'season_id'      => (int) $upsert_result['season_id'],
					'competition_id' => (int) $upsert_result['competition_id'],
				);
			}
		}

		if ( ! $dry_run && 'matches' === $entity ) {
			foreach ( $result['impacted_pairs'] as $pair ) {
				ML_Stats_Engine::recompute_pair( (int) $pair['season_id'], (int) $pair['competition_id'] );
			}
		}

		return $result;
	}

	/**
	 * Export entity rows.
	 *
	 * @param string $entity  Entity type.
	 * @param array  $filters Filters.
	 *
	 * @return array
	 */
	public static function export_rows( string $entity, array $filters ): array {
		if ( 'teams' === $entity ) {
			return self::export_teams_rows();
		}

		if ( 'players' === $entity ) {
			return self::export_players_rows( isset( $filters['team_id'] ) ? absint( $filters['team_id'] ) : 0 );
		}

		return self::export_matches_rows(
			isset( $filters['season_id'] ) ? absint( $filters['season_id'] ) : 0,
			isset( $filters['competition_id'] ) ? absint( $filters['competition_id'] ) : 0
		);
	}

	/**
	 * Get sample rows for entity.
	 *
	 * @param string $entity Entity type.
	 *
	 * @return array
	 */
	public static function sample_rows( string $entity ): array {
		if ( 'teams' === $entity ) {
			return array(
				array( 'external_id', 'name', 'slug', 'short_name', 'logo_url' ),
				array( 'TEAM-001', 'MAHL Falcons', 'mahl-falcons', 'MFC', 'https://example.com/logo.png' ),
			);
		}

		if ( 'players' === $entity ) {
			return array(
				array( 'external_id', 'name', 'number', 'position', 'team_external_id', 'team_slug', 'team_name', 'shoots', 'birthdate' ),
				array( 'PLY-1001', 'John Example', '19', 'F', 'TEAM-001', '', '', 'L', '1998-04-22' ),
			);
		}

		return array(
			array( 'external_id', 'season', 'competition', 'phase', 'datetime', 'home_team_external_id', 'home_team_slug', 'home_team_name', 'away_team_external_id', 'away_team_slug', 'away_team_name', 'home_score', 'away_score', 'status', 'venue' ),
			array( 'MAT-0001', '2025/2026', 'MAHL', 'Regular Season', '2026-01-15 18:00', 'TEAM-001', '', '', 'TEAM-002', '', '', '3', '2', 'played', 'Main Arena' ),
		);
	}

	/**
	 * Upsert team row.
	 *
	 * @param array $row     Row data.
	 * @param bool  $dry_run Dry run mode.
	 *
	 * @return array
	 */
	public static function upsert_team( array $row, bool $dry_run ): array {
		$name = isset( $row['name'] ) ? sanitize_text_field( $row['name'] ) : '';
		if ( '' === $name ) {
			return array( 'error' => __( 'Team name is required.', 'mahl-league' ) );
		}

		$post_id = self::find_team_post_id( $row );
		$status  = $post_id > 0 ? 'updated' : 'created';
		if ( $dry_run ) {
			return array( 'status' => $status );
		}

		$slug      = isset( $row['slug'] ) ? sanitize_title( $row['slug'] ) : '';
		$post_data = array(
			'post_type'   => 'ml_team',
			'post_title'  => $name,
			'post_name'   => $slug,
			'post_status' => 'publish',
		);

		if ( $post_id > 0 ) {
			$post_data['ID'] = $post_id;
			$post_id         = wp_update_post( $post_data, true );
		} else {
			$post_id = wp_insert_post( $post_data, true );
		}

		if ( is_wp_error( $post_id ) || $post_id <= 0 ) {
			return array( 'error' => __( 'Failed to save team.', 'mahl-league' ) );
		}

		if ( ! empty( $row['external_id'] ) ) {
			update_post_meta( $post_id, 'ml_external_id', sanitize_text_field( $row['external_id'] ) );
		}
		if ( ! empty( $row['short_name'] ) ) {
			update_post_meta( $post_id, 'ml_team_short', sanitize_text_field( $row['short_name'] ) );
		}
		if ( ! empty( $row['logo_url'] ) ) {
			update_post_meta( $post_id, 'ml_team_logo_url', esc_url_raw( $row['logo_url'] ) );
		}

		return array( 'status' => $status );
	}

	/**
	 * Upsert player row.
	 *
	 * @param array $row     Row data.
	 * @param bool  $dry_run Dry run mode.
	 *
	 * @return array
	 */
	public static function upsert_player( array $row, bool $dry_run ): array {
		$name    = isset( $row['name'] ) ? sanitize_text_field( $row['name'] ) : '';
		$team_id = self::resolve_team_from_row( $row, '' );
		if ( '' === $name ) {
			return array( 'error' => __( 'Player name is required.', 'mahl-league' ) );
		}
		if ( $team_id <= 0 ) {
			return array( 'error' => __( 'Player team is required and must exist.', 'mahl-league' ) );
		}

		$post_id = self::find_player_post_id( $row, $team_id );
		$status  = $post_id > 0 ? 'updated' : 'created';
		if ( $dry_run ) {
			return array( 'status' => $status );
		}

		$post_data = array(
			'post_type'   => 'ml_player',
			'post_title'  => $name,
			'post_status' => 'publish',
		);

		if ( $post_id > 0 ) {
			$post_data['ID'] = $post_id;
			$post_id         = wp_update_post( $post_data, true );
		} else {
			$post_id = wp_insert_post( $post_data, true );
		}

		if ( is_wp_error( $post_id ) || $post_id <= 0 ) {
			return array( 'error' => __( 'Failed to save player.', 'mahl-league' ) );
		}

		if ( ! empty( $row['external_id'] ) ) {
			update_post_meta( $post_id, 'ml_external_id', sanitize_text_field( $row['external_id'] ) );
		}

		update_post_meta( $post_id, 'ml_player_team_id', $team_id );
		update_post_meta( $post_id, 'ml_player_number', isset( $row['number'] ) ? absint( $row['number'] ) : 0 );
		update_post_meta( $post_id, 'ml_player_position', self::normalize_enum( isset( $row['position'] ) ? $row['position'] : '', array( 'G', 'D', 'F' ), '' ) );
		update_post_meta( $post_id, 'ml_player_shoots', self::normalize_enum( isset( $row['shoots'] ) ? $row['shoots'] : '', array( 'L', 'R', 'N' ), '' ) );

		if ( ! empty( $row['birthdate'] ) && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $row['birthdate'] ) ) {
			update_post_meta( $post_id, 'ml_player_birthdate', $row['birthdate'] );
		}

		return array( 'status' => $status );
	}

	/**
	 * Upsert match row.
	 *
	 * @param array $row     Row data.
	 * @param bool  $dry_run Dry run mode.
	 *
	 * @return array
	 */
	public static function upsert_match( array $row, bool $dry_run ): array {
		if ( empty( $row['season'] ) || empty( $row['competition'] ) || empty( $row['datetime'] ) ) {
			return array( 'error' => __( 'Match season, competition and datetime are required.', 'mahl-league' ) );
		}

		$season_term = self::resolve_or_create_term( 'ml_season', $row['season'], ! $dry_run );
		$comp_term   = self::resolve_or_create_term( 'ml_competition', $row['competition'], ! $dry_run );
		if ( is_wp_error( $season_term ) || is_wp_error( $comp_term ) || empty( $season_term ) || empty( $comp_term ) ) {
			return array( 'error' => __( 'Unable to resolve season/competition terms.', 'mahl-league' ) );
		}

		$phase_term = array();
		if ( ! empty( $row['phase'] ) ) {
			$phase_term = self::resolve_or_create_term( 'ml_phase', $row['phase'], ! $dry_run );
			if ( is_wp_error( $phase_term ) ) {
				return array( 'error' => __( 'Unable to resolve phase term.', 'mahl-league' ) );
			}
		}

		$home_team_id = self::resolve_team_from_row( $row, 'home_team_' );
		$away_team_id = self::resolve_team_from_row( $row, 'away_team_' );
		if ( $home_team_id <= 0 || $away_team_id <= 0 ) {
			return array( 'error' => __( 'Home and away teams must exist.', 'mahl-league' ) );
		}

		$datetime = self::normalize_datetime( $row['datetime'] );
		if ( '' === $datetime ) {
			return array( 'error' => __( 'Match datetime must be ISO 8601 or YYYY-MM-DD HH:MM.', 'mahl-league' ) );
		}

		$post_id = self::find_match_post_id( $row, $datetime, $home_team_id, $away_team_id, (int) $season_term['term_id'], (int) $comp_term['term_id'] );
		$status  = $post_id > 0 ? 'updated' : 'created';
		if ( $dry_run ) {
			return array(
				'status'         => $status,
				'pair_key'       => (int) $season_term['term_id'] . ':' . (int) $comp_term['term_id'],
				'season_id'      => (int) $season_term['term_id'],
				'competition_id' => (int) $comp_term['term_id'],
			);
		}

		$home_title = get_the_title( $home_team_id );
		$away_title = get_the_title( $away_team_id );
		$post_data  = array(
			'post_type'   => 'ml_match',
			'post_title'  => trim( $home_title . ' vs ' . $away_title . ' - ' . $datetime ),
			'post_status' => 'publish',
		);

		if ( $post_id > 0 ) {
			$post_data['ID'] = $post_id;
			$post_id         = wp_update_post( $post_data, true );
		} else {
			$post_id = wp_insert_post( $post_data, true );
		}

		if ( is_wp_error( $post_id ) || $post_id <= 0 ) {
			return array( 'error' => __( 'Failed to save match.', 'mahl-league' ) );
		}

		if ( ! empty( $row['external_id'] ) ) {
			update_post_meta( $post_id, 'ml_external_id', sanitize_text_field( $row['external_id'] ) );
		}

		update_post_meta( $post_id, 'ml_home_team_id', $home_team_id );
		update_post_meta( $post_id, 'ml_away_team_id', $away_team_id );
		update_post_meta( $post_id, 'ml_match_datetime', $datetime );
		update_post_meta( $post_id, 'ml_home_score', isset( $row['home_score'] ) ? absint( $row['home_score'] ) : 0 );
		update_post_meta( $post_id, 'ml_away_score', isset( $row['away_score'] ) ? absint( $row['away_score'] ) : 0 );
		update_post_meta( $post_id, 'ml_status', self::normalize_enum( isset( $row['status'] ) ? $row['status'] : '', array( 'scheduled', 'played', 'canceled' ), 'scheduled' ) );
		update_post_meta( $post_id, 'ml_venue', isset( $row['venue'] ) ? sanitize_text_field( $row['venue'] ) : '' );
		update_post_meta( $post_id, 'ml_stage_label', isset( $row['phase'] ) ? sanitize_text_field( $row['phase'] ) : '' );

		wp_set_post_terms( $post_id, array( (int) $season_term['term_id'] ), 'ml_season', false );
		wp_set_post_terms( $post_id, array( (int) $comp_term['term_id'] ), 'ml_competition', false );
		if ( ! empty( $phase_term ) ) {
			wp_set_post_terms( $post_id, array( (int) $phase_term['term_id'] ), 'ml_phase', false );
		}

		return array(
			'status'         => $status,
			'pair_key'       => (int) $season_term['term_id'] . ':' . (int) $comp_term['term_id'],
			'season_id'      => (int) $season_term['term_id'],
			'competition_id' => (int) $comp_term['term_id'],
		);
	}

	/**
	 * Build matches export rows.
	 *
	 * @param int $season_id      Season filter.
	 * @param int $competition_id Competition filter.
	 *
	 * @return array
	 */
	private static function export_matches_rows( int $season_id, int $competition_id ): array {
		$args = array(
			'post_type'      => 'ml_match',
			'post_status'    => array( 'publish', 'draft', 'future', 'pending' ),
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		$tax_query = array();
		if ( $season_id > 0 ) {
			$tax_query[] = array(
				'taxonomy' => 'ml_season',
				'field'    => 'term_id',
				'terms'    => $season_id,
			);
		}
		if ( $competition_id > 0 ) {
			$tax_query[] = array(
				'taxonomy' => 'ml_competition',
				'field'    => 'term_id',
				'terms'    => $competition_id,
			);
		}
		if ( ! empty( $tax_query ) ) {
			if ( 1 < count( $tax_query ) ) {
				$tax_query['relation'] = 'AND';
			}
			$args['tax_query'] = $tax_query;
		}

		$rows   = array();
		$rows[] = array( 'external_id', 'season', 'competition', 'phase', 'datetime', 'home_team_external_id', 'home_team_slug', 'home_team_name', 'away_team_external_id', 'away_team_slug', 'away_team_name', 'home_score', 'away_score', 'status', 'venue' );

		$matches = get_posts( $args );
		foreach ( $matches as $match ) {
			$home_team_id = absint( get_post_meta( $match->ID, 'ml_home_team_id', true ) );
			$away_team_id = absint( get_post_meta( $match->ID, 'ml_away_team_id', true ) );
			$season_term  = self::first_term( $match->ID, 'ml_season' );
			$comp_term    = self::first_term( $match->ID, 'ml_competition' );
			$phase_term   = self::first_term( $match->ID, 'ml_phase' );

			$rows[] = array(
				(string) get_post_meta( $match->ID, 'ml_external_id', true ),
				$season_term ? $season_term->slug : '',
				$comp_term ? $comp_term->slug : '',
				$phase_term ? $phase_term->slug : '',
				(string) get_post_meta( $match->ID, 'ml_match_datetime', true ),
				(string) get_post_meta( $home_team_id, 'ml_external_id', true ),
				(string) get_post_field( 'post_name', $home_team_id ),
				(string) get_the_title( $home_team_id ),
				(string) get_post_meta( $away_team_id, 'ml_external_id', true ),
				(string) get_post_field( 'post_name', $away_team_id ),
				(string) get_the_title( $away_team_id ),
				(string) get_post_meta( $match->ID, 'ml_home_score', true ),
				(string) get_post_meta( $match->ID, 'ml_away_score', true ),
				(string) get_post_meta( $match->ID, 'ml_status', true ),
				(string) get_post_meta( $match->ID, 'ml_venue', true ),
			);
		}

		return $rows;
	}

	/**
	 * Build teams export rows.
	 *
	 * @return array
	 */
	private static function export_teams_rows(): array {
		$rows   = array();
		$rows[] = array( 'external_id', 'name', 'slug', 'short_name', 'logo_url' );

		$teams = get_posts(
			array(
				'post_type'      => 'ml_team',
				'post_status'    => array( 'publish', 'draft' ),
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		foreach ( $teams as $team ) {
			$rows[] = array(
				(string) get_post_meta( $team->ID, 'ml_external_id', true ),
				(string) $team->post_title,
				(string) $team->post_name,
				(string) get_post_meta( $team->ID, 'ml_team_short', true ),
				(string) get_post_meta( $team->ID, 'ml_team_logo_url', true ),
			);
		}

		return $rows;
	}

	/**
	 * Build players export rows.
	 *
	 * @param int $team_id Team filter.
	 *
	 * @return array
	 */
	private static function export_players_rows( int $team_id ): array {
		$args = array(
			'post_type'      => 'ml_player',
			'post_status'    => array( 'publish', 'draft' ),
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		);

		if ( $team_id > 0 ) {
			$args['meta_query'] = array(
				array(
					'key'   => 'ml_player_team_id',
					'value' => $team_id,
				),
			);
		}

		$rows   = array();
		$rows[] = array( 'external_id', 'name', 'number', 'position', 'team_external_id', 'team_slug', 'team_name', 'shoots', 'birthdate' );

		$players = get_posts( $args );
		foreach ( $players as $player ) {
			$player_team_id = absint( get_post_meta( $player->ID, 'ml_player_team_id', true ) );

			$rows[] = array(
				(string) get_post_meta( $player->ID, 'ml_external_id', true ),
				(string) $player->post_title,
				(string) get_post_meta( $player->ID, 'ml_player_number', true ),
				(string) get_post_meta( $player->ID, 'ml_player_position', true ),
				(string) get_post_meta( $player_team_id, 'ml_external_id', true ),
				(string) get_post_field( 'post_name', $player_team_id ),
				(string) get_the_title( $player_team_id ),
				(string) get_post_meta( $player->ID, 'ml_player_shoots', true ),
				(string) get_post_meta( $player->ID, 'ml_player_birthdate', true ),
			);
		}

		return $rows;
	}

	/**
	 * Route row to upsert callback.
	 *
	 * @param string $entity  Entity type.
	 * @param array  $row     Row data.
	 * @param bool   $dry_run Dry run mode.
	 *
	 * @return array
	 */
	private static function route_upsert( string $entity, array $row, bool $dry_run ): array {
		if ( 'teams' === $entity ) {
			return self::upsert_team( $row, $dry_run );
		}

		if ( 'players' === $entity ) {
			return self::upsert_player( $row, $dry_run );
		}

		return self::upsert_match( $row, $dry_run );
	}

	/**
	 * Resolve team post ID from row.
	 *
	 * @param array  $row    Row data.
	 * @param string $prefix Prefix for home/away team columns.
	 *
	 * @return int
	 */
	private static function resolve_team_from_row( array $row, string $prefix ): int {
		$external_key = $prefix . 'external_id';
		$slug_key     = $prefix . 'slug';
		$name_key     = $prefix . 'name';

		if ( ! empty( $row[ $external_key ] ) ) {
			$ids = get_posts(
				array(
					'post_type'      => 'ml_team',
					'post_status'    => array( 'publish', 'draft' ),
					'fields'         => 'ids',
					'posts_per_page' => 1,
					'meta_query'     => array(
						array(
							'key'   => 'ml_external_id',
							'value' => sanitize_text_field( $row[ $external_key ] ),
						),
					),
				)
			);

			if ( ! empty( $ids ) ) {
				return absint( $ids[0] );
			}
		}

		if ( ! empty( $row[ $slug_key ] ) ) {
			$post = get_page_by_path( sanitize_title( $row[ $slug_key ] ), OBJECT, 'ml_team' );
			if ( $post instanceof WP_Post ) {
				return absint( $post->ID );
			}
		}

		if ( ! empty( $row[ $name_key ] ) ) {
			$ids = get_posts(
				array(
					'post_type'      => 'ml_team',
					'post_status'    => array( 'publish', 'draft' ),
					'fields'         => 'ids',
					'posts_per_page' => 1,
					's'              => sanitize_text_field( $row[ $name_key ] ),
				)
			);

			foreach ( $ids as $id ) {
				if ( 0 === strcasecmp( sanitize_text_field( $row[ $name_key ] ), (string) get_the_title( $id ) ) ) {
					return absint( $id );
				}
			}
		}

		return 0;
	}

	/**
	 * Find team by matching strategy.
	 *
	 * @param array $row Row data.
	 *
	 * @return int
	 */
	private static function find_team_post_id( array $row ): int {
		$id = self::resolve_team_from_row(
			array(
				'external_id' => isset( $row['external_id'] ) ? $row['external_id'] : '',
				'slug'        => isset( $row['slug'] ) ? $row['slug'] : '',
				'name'        => isset( $row['name'] ) ? $row['name'] : '',
			),
			''
		);

		return $id;
	}

	/**
	 * Find player by matching strategy.
	 *
	 * @param array $row     Row data.
	 * @param int   $team_id Team ID.
	 *
	 * @return int
	 */
	private static function find_player_post_id( array $row, int $team_id ): int {
		if ( ! empty( $row['external_id'] ) ) {
			$ids = get_posts(
				array(
					'post_type'      => 'ml_player',
					'post_status'    => array( 'publish', 'draft' ),
					'fields'         => 'ids',
					'posts_per_page' => 1,
					'meta_query'     => array(
						array(
							'key'   => 'ml_external_id',
							'value' => sanitize_text_field( $row['external_id'] ),
						),
					),
				)
			);
			if ( ! empty( $ids ) ) {
				return absint( $ids[0] );
			}
		}

		if ( empty( $row['name'] ) ) {
			return 0;
		}

		$ids = get_posts(
			array(
				'post_type'      => 'ml_player',
				'post_status'    => array( 'publish', 'draft' ),
				'fields'         => 'ids',
				'posts_per_page' => -1,
				's'              => sanitize_text_field( $row['name'] ),
				'meta_query'     => array(
					array(
						'key'   => 'ml_player_team_id',
						'value' => $team_id,
					),
				),
			)
		);

		foreach ( $ids as $id ) {
			if ( 0 === strcasecmp( sanitize_text_field( $row['name'] ), (string) get_the_title( $id ) ) ) {
				return absint( $id );
			}
		}

		return 0;
	}

	/**
	 * Find match post by matching strategy.
	 *
	 * @param array  $row            Row data.
	 * @param string $datetime       Normalized datetime.
	 * @param int    $home_team_id   Home team ID.
	 * @param int    $away_team_id   Away team ID.
	 * @param int    $season_id      Season term ID.
	 * @param int    $competition_id Competition term ID.
	 *
	 * @return int
	 */
	private static function find_match_post_id( array $row, string $datetime, int $home_team_id, int $away_team_id, int $season_id, int $competition_id ): int {
		if ( ! empty( $row['external_id'] ) ) {
			$ids = get_posts(
				array(
					'post_type'      => 'ml_match',
					'post_status'    => array( 'publish', 'draft', 'future', 'pending' ),
					'fields'         => 'ids',
					'posts_per_page' => 1,
					'meta_query'     => array(
						array(
							'key'   => 'ml_external_id',
							'value' => sanitize_text_field( $row['external_id'] ),
						),
					),
				)
			);
			if ( ! empty( $ids ) ) {
				return absint( $ids[0] );
			}
		}

		$ids = get_posts(
			array(
				'post_type'      => 'ml_match',
				'post_status'    => array( 'publish', 'draft', 'future', 'pending' ),
				'fields'         => 'ids',
				'posts_per_page' => -1,
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'key'   => 'ml_match_datetime',
						'value' => $datetime,
					),
					array(
						'key'   => 'ml_home_team_id',
						'value' => $home_team_id,
					),
					array(
						'key'   => 'ml_away_team_id',
						'value' => $away_team_id,
					),
				),
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

		return ! empty( $ids ) ? absint( $ids[0] ) : 0;
	}

	/**
	 * Resolve term by name/slug or create it.
	 *
	 * @param string $taxonomy Taxonomy.
	 * @param string $value    Input value.
	 * @param bool   $create   Create term if missing.
	 *
	 * @return array|WP_Error
	 */
	private static function resolve_or_create_term( string $taxonomy, string $value, bool $create ) {
		$clean_value = sanitize_text_field( $value );
		$slug        = sanitize_title( $clean_value );

		$term = get_term_by( 'slug', $slug, $taxonomy );
		if ( ! $term ) {
			$term = get_term_by( 'name', $clean_value, $taxonomy );
		}
		if ( $term instanceof WP_Term ) {
			return array(
				'term_id' => (int) $term->term_id,
				'name'    => $term->name,
				'slug'    => $term->slug,
			);
		}

		if ( ! $create ) {
			return new WP_Error( 'term_missing', __( 'Required taxonomy term does not exist in dry run.', 'mahl-league' ) );
		}

		$result = wp_insert_term(
			$clean_value,
			$taxonomy,
			array( 'slug' => $slug )
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return array(
			'term_id' => (int) $result['term_id'],
			'name'    => $clean_value,
			'slug'    => $slug,
		);
	}

	/**
	 * Create a consistent error result payload.
	 *
	 * @param string $message Error message.
	 *
	 * @return array
	 */
	private static function error_result( string $message ): array {
		return array(
			'created'        => 0,
			'updated'        => 0,
			'skipped'        => 0,
			'errors'         => 1,
			'error_messages' => array(
				array(
					'row'     => 0,
					'message' => $message,
				),
			),
			'impacted_pairs' => array(),
		);
	}

	/**
	 * Normalize enum-like text.
	 *
	 * @param string $value   Input value.
	 * @param array  $allowed Allowed values.
	 * @param string $default Default value.
	 *
	 * @return string
	 */
	private static function normalize_enum( string $value, array $allowed, string $default ): string {
		$normalized = strtoupper( sanitize_text_field( $value ) );
		if ( in_array( $normalized, $allowed, true ) ) {
			return $normalized;
		}

		$normalized_lower = strtolower( sanitize_text_field( $value ) );
		foreach ( $allowed as $item ) {
			if ( strtolower( $item ) === $normalized_lower ) {
				return (string) $item;
			}
		}

		return $default;
	}

	/**
	 * Normalize supported datetime formats.
	 *
	 * @param string $value Raw value.
	 *
	 * @return string
	 */
	private static function normalize_datetime( string $value ): string {
		$clean = trim( sanitize_text_field( $value ) );
		if ( preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $clean ) ) {
			return $clean;
		}

		$timestamp = strtotime( $clean );
		if ( false === $timestamp ) {
			return '';
		}

		return wp_date( 'Y-m-d H:i', $timestamp, wp_timezone() );
	}

	/**
	 * Determine delimiter to use.
	 *
	 * @param resource $handle    File handle.
	 * @param string   $delimiter Delimiter setting.
	 *
	 * @return string
	 */
	private static function detect_delimiter( $handle, string $delimiter ): string {
		if ( in_array( $delimiter, array( ',', ';' ), true ) ) {
			return $delimiter;
		}

		$line = fgets( $handle );
		if ( false === $line ) {
			return ',';
		}
		rewind( $handle );

		$comma_count = substr_count( (string) $line, ',' );
		$semi_count  = substr_count( (string) $line, ';' );

		return $semi_count > $comma_count ? ';' : ',';
	}

	/**
	 * Normalize header key.
	 *
	 * @param string $header Header value.
	 *
	 * @return string
	 */
	private static function normalize_header( string $header ): string {
		$header = strtolower( trim( self::strip_bom( $header ) ) );
		$header = str_replace( array( ' ', '-' ), '_', $header );

		return sanitize_key( $header );
	}

	/**
	 * Strip UTF-8 BOM from value.
	 *
	 * @param string $value Input value.
	 *
	 * @return string
	 */
	private static function strip_bom( string $value ): string {
		return preg_replace( '/^\xEF\xBB\xBF/', '', $value );
	}

	/**
	 * Check if headers contain any value from list.
	 *
	 * @param array $headers Headers.
	 * @param array $any_of  Any-of list.
	 *
	 * @return bool
	 */
	private static function contains_one_of( array $headers, array $any_of ): bool {
		foreach ( $any_of as $item ) {
			if ( in_array( $item, $headers, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get first taxonomy term.
	 *
	 * @param int    $post_id   Post ID.
	 * @param string $taxonomy  Taxonomy.
	 *
	 * @return WP_Term|null
	 */
	private static function first_term( int $post_id, string $taxonomy ): ?WP_Term {
		$terms = wp_get_post_terms( $post_id, $taxonomy );
		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return null;
		}

		return $terms[0] instanceof WP_Term ? $terms[0] : null;
	}
}
