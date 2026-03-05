<?php
/**
 * Render admin screens for MAHL League.
 *
 * @package MAHLLeague
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ML_Admin_Screens
 */
class ML_Admin_Screens {

	/**
	 * Render dashboard screen.
	 *
	 * @return void
	 */
	public static function render_dashboard(): void {
		if ( ! current_user_can( 'manage_ml_league' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'mahl-league' ) );
		}

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'MAHL Liga Dashboard', 'mahl-league' ) . '</h1>';
		echo '<p>' . esc_html__( 'Use Teams, Players and Matches menus for basic CRUD. Use Match Editor for structured match meta.', 'mahl-league' ) . '</p>';
		echo '</div>';
	}

	/**
	 * Render teams management list.
	 *
	 * @return void
	 */
	public static function render_teams(): void {
		if ( ! current_user_can( 'edit_ml_teams' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'mahl-league' ) );
		}

		$teams = get_posts(
			array(
				'post_type'      => 'ml_team',
				'post_status'    => array( 'publish', 'draft' ),
				'posts_per_page' => 300,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		echo '<div class="wrap">';
		echo '<h1 class="wp-heading-inline">' . esc_html__( 'Teams', 'mahl-league' ) . '</h1>';
		echo ' <a href="' . esc_url( admin_url( 'admin.php?page=ml-team-edit' ) ) . '" class="page-title-action">' . esc_html__( 'Add Team', 'mahl-league' ) . '</a>';
		self::render_admin_notice();
		echo '<hr class="wp-header-end" />';
		echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Name', 'mahl-league' ) . '</th><th>' . esc_html__( 'Slug', 'mahl-league' ) . '</th><th>' . esc_html__( 'Short Name', 'mahl-league' ) . '</th><th>' . esc_html__( 'Logo URL', 'mahl-league' ) . '</th><th>' . esc_html__( 'Actions', 'mahl-league' ) . '</th></tr></thead><tbody>';
		if ( empty( $teams ) ) {
			echo '<tr><td colspan="5">' . esc_html__( 'No teams found.', 'mahl-league' ) . '</td></tr>';
		} else {
			foreach ( $teams as $team ) {
				$edit_url   = add_query_arg( array( 'page' => 'ml-team-edit', 'team_id' => (int) $team->ID ), admin_url( 'admin.php' ) );
				$delete_url = wp_nonce_url(
					add_query_arg(
						array(
							'action'  => 'ml_delete_team',
							'team_id' => (int) $team->ID,
						),
						admin_url( 'admin-post.php' )
					),
					'ml_delete_team_' . (int) $team->ID,
					'ml_delete_team_nonce'
				);

				echo '<tr>';
				echo '<td>' . esc_html( $team->post_title ) . '</td>';
				echo '<td>' . esc_html( (string) $team->post_name ) . '</td>';
				echo '<td>' . esc_html( (string) get_post_meta( $team->ID, 'ml_team_short', true ) ) . '</td>';
				echo '<td>' . esc_html( (string) get_post_meta( $team->ID, 'ml_team_logo_url', true ) ) . '</td>';
				echo '<td><a href="' . esc_url( $edit_url ) . '">' . esc_html__( 'Edit', 'mahl-league' ) . '</a> | <a href="' . esc_url( $delete_url ) . '">' . esc_html__( 'Delete', 'mahl-league' ) . '</a></td>';
				echo '</tr>';
			}
		}
		echo '</tbody></table>';
		echo '</div>';
	}

	/**
	 * Render team create/edit form.
	 *
	 * @return void
	 */
	public static function render_team_edit(): void {
		if ( ! current_user_can( 'edit_ml_teams' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'mahl-league' ) );
		}

		$team_id = isset( $_GET['team_id'] ) ? absint( wp_unslash( $_GET['team_id'] ) ) : 0;
		if ( $team_id > 0 && 'ml_team' !== get_post_type( $team_id ) ) {
			wp_die( esc_html__( 'Invalid team selected.', 'mahl-league' ) );
		}

		$title     = $team_id > 0 ? get_the_title( $team_id ) : '';
		$slug      = $team_id > 0 ? (string) get_post_field( 'post_name', $team_id ) : '';
		$short_name = $team_id > 0 ? (string) get_post_meta( $team_id, 'ml_team_short', true ) : '';
		$logo_url  = $team_id > 0 ? (string) get_post_meta( $team_id, 'ml_team_logo_url', true ) : '';

		echo '<div class="wrap">';
		echo '<h1>' . esc_html( $team_id > 0 ? __( 'Edit Team', 'mahl-league' ) : __( 'Add Team', 'mahl-league' ) ) . '</h1>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="ml_save_team" />';
		echo '<input type="hidden" name="team_id" value="' . esc_attr( (string) $team_id ) . '" />';
		wp_nonce_field( 'ml_save_team', 'ml_save_team_nonce' );
		echo '<table class="form-table" role="presentation"><tbody>';
		self::render_input_row( 'team_name', __( 'Name', 'mahl-league' ), (string) $title );
		self::render_input_row( 'team_slug', __( 'Slug (optional)', 'mahl-league' ), $slug );
		self::render_input_row( 'team_short_name', __( 'Short Name', 'mahl-league' ), $short_name );
		self::render_input_row( 'team_logo_url', __( 'Logo URL', 'mahl-league' ), $logo_url );
		echo '</tbody></table>';
		submit_button( $team_id > 0 ? __( 'Save Team', 'mahl-league' ) : __( 'Create Team', 'mahl-league' ) );
		echo ' <a class="button" href="' . esc_url( admin_url( 'admin.php?page=ml-teams' ) ) . '">' . esc_html__( 'Back to Teams', 'mahl-league' ) . '</a>';
		echo '</form></div>';
	}

	/**
	 * Render players management list.
	 *
	 * @return void
	 */
	public static function render_players(): void {
		if ( ! current_user_can( 'edit_ml_players' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'mahl-league' ) );
		}

		$team_filter = isset( $_GET['team_id'] ) ? absint( wp_unslash( $_GET['team_id'] ) ) : 0;
		$args        = array(
			'post_type'      => 'ml_player',
			'post_status'    => array( 'publish', 'draft' ),
			'posts_per_page' => 300,
			'orderby'        => 'title',
			'order'          => 'ASC',
		);
		if ( $team_filter > 0 ) {
			$args['meta_query'] = array(
				array(
					'key'   => 'ml_player_team_id',
					'value' => $team_filter,
				),
			);
		}
		$players = get_posts( $args );
		$teams   = get_posts(
			array(
				'post_type'      => 'ml_team',
				'post_status'    => array( 'publish', 'draft' ),
				'posts_per_page' => 300,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		echo '<div class="wrap">';
		echo '<h1 class="wp-heading-inline">' . esc_html__( 'Players', 'mahl-league' ) . '</h1>';
		echo ' <a href="' . esc_url( admin_url( 'admin.php?page=ml-player-edit' ) ) . '" class="page-title-action">' . esc_html__( 'Add Player', 'mahl-league' ) . '</a>';
		self::render_admin_notice();
		echo '<hr class="wp-header-end" />';
		echo '<form method="get" action="' . esc_url( admin_url( 'admin.php' ) ) . '">';
		echo '<input type="hidden" name="page" value="ml-players" />';
		echo '<label for="ml-player-team-filter" style="margin-right:6px;">' . esc_html__( 'Team', 'mahl-league' ) . '</label>';
		echo '<select id="ml-player-team-filter" name="team_id">';
		echo '<option value="0">' . esc_html__( 'All teams', 'mahl-league' ) . '</option>';
		foreach ( $teams as $team ) {
			echo '<option value="' . esc_attr( (string) $team->ID ) . '" ' . selected( $team_filter, $team->ID, false ) . '>' . esc_html( $team->post_title ) . '</option>';
		}
		echo '</select> ';
		submit_button( __( 'Filter', 'mahl-league' ), 'secondary', '', false );
		echo '</form>';

		echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Name', 'mahl-league' ) . '</th><th>' . esc_html__( 'Number', 'mahl-league' ) . '</th><th>' . esc_html__( 'Position', 'mahl-league' ) . '</th><th>' . esc_html__( 'Team', 'mahl-league' ) . '</th><th>' . esc_html__( 'External ID', 'mahl-league' ) . '</th><th>' . esc_html__( 'Actions', 'mahl-league' ) . '</th></tr></thead><tbody>';
		if ( empty( $players ) ) {
			echo '<tr><td colspan="6">' . esc_html__( 'No players found.', 'mahl-league' ) . '</td></tr>';
		} else {
			foreach ( $players as $player ) {
				$edit_url        = add_query_arg( array( 'page' => 'ml-player-edit', 'player_id' => (int) $player->ID ), admin_url( 'admin.php' ) );
				$player_team_id  = absint( get_post_meta( $player->ID, 'ml_player_team_id', true ) );
				$player_team_name = $player_team_id > 0 ? (string) get_the_title( $player_team_id ) : '';
				echo '<tr>';
				echo '<td>' . esc_html( $player->post_title ) . '</td>';
				echo '<td>' . esc_html( (string) get_post_meta( $player->ID, 'ml_player_number', true ) ) . '</td>';
				echo '<td>' . esc_html( (string) get_post_meta( $player->ID, 'ml_player_position', true ) ) . '</td>';
				echo '<td>' . esc_html( $player_team_name ) . '</td>';
				echo '<td>' . esc_html( (string) get_post_meta( $player->ID, 'ml_external_id', true ) ) . '</td>';
				echo '<td><a href="' . esc_url( $edit_url ) . '">' . esc_html__( 'Edit', 'mahl-league' ) . '</a></td>';
				echo '</tr>';
			}
		}
		echo '</tbody></table>';
		echo '</div>';
	}

	/**
	 * Render player create/edit form.
	 *
	 * @return void
	 */
	public static function render_player_edit(): void {
		if ( ! current_user_can( 'edit_ml_players' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'mahl-league' ) );
		}

		$player_id = isset( $_GET['player_id'] ) ? absint( wp_unslash( $_GET['player_id'] ) ) : 0;
		if ( $player_id > 0 && 'ml_player' !== get_post_type( $player_id ) ) {
			wp_die( esc_html__( 'Invalid player selected.', 'mahl-league' ) );
		}

		$teams = get_posts(
			array(
				'post_type'      => 'ml_team',
				'post_status'    => array( 'publish', 'draft' ),
				'posts_per_page' => 300,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		$title      = $player_id > 0 ? get_the_title( $player_id ) : '';
		$number     = $player_id > 0 ? (string) get_post_meta( $player_id, 'ml_player_number', true ) : '';
		$position   = $player_id > 0 ? (string) get_post_meta( $player_id, 'ml_player_position', true ) : '';
		$team_id    = $player_id > 0 ? absint( get_post_meta( $player_id, 'ml_player_team_id', true ) ) : 0;
		$external_id = $player_id > 0 ? (string) get_post_meta( $player_id, 'ml_external_id', true ) : '';

		echo '<div class="wrap">';
		echo '<h1>' . esc_html( $player_id > 0 ? __( 'Edit Player', 'mahl-league' ) : __( 'Add Player', 'mahl-league' ) ) . '</h1>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="ml_save_player" />';
		echo '<input type="hidden" name="player_id" value="' . esc_attr( (string) $player_id ) . '" />';
		wp_nonce_field( 'ml_save_player', 'ml_save_player_nonce' );
		echo '<table class="form-table" role="presentation"><tbody>';
		self::render_input_row( 'player_name', __( 'Name', 'mahl-league' ), (string) $title );
		self::render_input_row( 'player_number', __( 'Number', 'mahl-league' ), $number, 'number' );
		self::render_input_row( 'player_position', __( 'Position', 'mahl-league' ), $position );
		echo '<tr><th scope="row"><label for="player_team_id">' . esc_html__( 'Team', 'mahl-league' ) . '</label></th><td><select name="player_team_id" id="player_team_id">';
		echo '<option value="0">' . esc_html__( 'Select team', 'mahl-league' ) . '</option>';
		foreach ( $teams as $team ) {
			echo '<option value="' . esc_attr( (string) $team->ID ) . '" ' . selected( $team_id, $team->ID, false ) . '>' . esc_html( $team->post_title ) . '</option>';
		}
		echo '</select></td></tr>';
		self::render_input_row( 'player_external_id', __( 'External ID (optional)', 'mahl-league' ), $external_id );
		echo '</tbody></table>';
		submit_button( $player_id > 0 ? __( 'Save Player', 'mahl-league' ) : __( 'Create Player', 'mahl-league' ) );
		echo ' <a class="button" href="' . esc_url( admin_url( 'admin.php?page=ml-players' ) ) . '">' . esc_html__( 'Back to Players', 'mahl-league' ) . '</a>';
		echo '</form></div>';
	}

	/**
	 * Render matches list with link to match editor.
	 *
	 * @return void
	 */
	public static function render_matches_list(): void {
		if ( ! current_user_can( 'edit_ml_matches' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'mahl-league' ) );
		}

		$matches = get_posts(
			array(
				'post_type'      => 'ml_match',
				'post_status'    => array( 'publish', 'draft', 'pending', 'future' ),
				'posts_per_page' => 200,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'Matches', 'mahl-league' ) . '</h1>';
		echo '<p><a class="button button-primary" href="' . esc_url( admin_url( 'admin.php?page=ml-match-editor' ) ) . '">' . esc_html__( 'Open Match Editor', 'mahl-league' ) . '</a></p>';
		echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Title', 'mahl-league' ) . '</th><th>' . esc_html__( 'Date', 'mahl-league' ) . '</th><th>' . esc_html__( 'Actions', 'mahl-league' ) . '</th></tr></thead><tbody>';
		if ( empty( $matches ) ) {
			echo '<tr><td colspan="3">' . esc_html__( 'No matches found.', 'mahl-league' ) . '</td></tr>';
		} else {
			foreach ( $matches as $match ) {
				$edit_url = add_query_arg( array( 'page' => 'ml-match-editor', 'match_id' => (int) $match->ID ), admin_url( 'admin.php' ) );
				echo '<tr><td>' . esc_html( $match->post_title ) . '</td><td>' . esc_html( (string) get_post_meta( $match->ID, 'ml_match_datetime', true ) ) . '</td><td><a href="' . esc_url( $edit_url ) . '">' . esc_html__( 'Edit in Match Editor', 'mahl-league' ) . '</a></td></tr>';
			}
		}
		echo '</tbody></table>';
		echo '</div>';
	}

	/**
	 * Render standings screen placeholder.
	 *
	 * @return void
	 */
	public static function render_standings(): void {
		self::render_placeholder_screen(
			__( 'Standings', 'mahl-league' ),
			__( 'Standings will be available in Phase 3.', 'mahl-league' )
		);
	}

	/**
	 * Render statistics screen placeholder.
	 *
	 * @return void
	 */
	public static function render_statistics(): void {
		self::render_placeholder_screen(
			__( 'Statistics', 'mahl-league' ),
			__( 'Statistics will be available in Phase 3.', 'mahl-league' )
		);
	}

	/**
	 * Render playoffs screen placeholder.
	 *
	 * @return void
	 */
	public static function render_playoffs(): void {
		self::render_placeholder_screen(
			__( 'Playoffs', 'mahl-league' ),
			__( 'Playoffs management will be available in a later phase.', 'mahl-league' )
		);
	}

	/**
	 * Render import/export screen placeholder.
	 *
	 * @return void
	 */
	public static function render_import_export(): void {
		if ( ! current_user_can( 'manage_ml_league' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'mahl-league' ) );
		}

		$seasons = get_terms(
			array(
				'taxonomy'   => 'ml_season',
				'hide_empty' => false,
			)
		);

		$competitions = get_terms(
			array(
				'taxonomy'   => 'ml_competition',
				'hide_empty' => false,
			)
		);

		$teams = get_posts(
			array(
				'post_type'      => 'ml_team',
				'post_status'    => array( 'publish', 'draft' ),
				'posts_per_page' => 300,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'Import / Export', 'mahl-league' ) . '</h1>';
		echo '<p>' . esc_html__( 'Secure CSV tools for Teams, Players and Matches.', 'mahl-league' ) . '</p>';

		self::render_import_notices();

		echo '<hr />';
		echo '<h2>' . esc_html__( 'Import CSV', 'mahl-league' ) . '</h2>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" enctype="multipart/form-data">';
		echo '<input type="hidden" name="action" value="ml_import_csv" />';
		wp_nonce_field( 'ml_import_csv', 'ml_import_csv_nonce' );

		echo '<table class="form-table" role="presentation"><tbody>';
		echo '<tr><th scope="row"><label for="ml-import-entity">' . esc_html__( 'Entity', 'mahl-league' ) . '</label></th>';
		echo '<td><select id="ml-import-entity" name="entity" required>';
		echo '<option value="teams">' . esc_html__( 'Teams', 'mahl-league' ) . '</option>';
		echo '<option value="players">' . esc_html__( 'Players', 'mahl-league' ) . '</option>';
		echo '<option value="matches">' . esc_html__( 'Matches', 'mahl-league' ) . '</option>';
		echo '</select></td></tr>';

		echo '<tr><th scope="row"><label for="ml-import-file">' . esc_html__( 'CSV File', 'mahl-league' ) . '</label></th>';
		echo '<td><input type="file" id="ml-import-file" name="import_file" accept=".csv,text/csv" required /></td></tr>';

		echo '<tr><th scope="row"><label for="ml-import-dry-run">' . esc_html__( 'Dry Run', 'mahl-league' ) . '</label></th>';
		echo '<td><label><input type="checkbox" id="ml-import-dry-run" name="dry_run" value="1" /> ' . esc_html__( 'Dry run (validate only, no changes)', 'mahl-league' ) . '</label></td></tr>';
		echo '</tbody></table>';

		submit_button( __( 'Import CSV', 'mahl-league' ) );
		echo '</form>';

		echo '<p><strong>' . esc_html__( 'Sample CSV files:', 'mahl-league' ) . '</strong> ';
		echo '<a href="' . esc_url( self::build_sample_export_link( 'teams' ) ) . '">' . esc_html__( 'Teams', 'mahl-league' ) . '</a> | ';
		echo '<a href="' . esc_url( self::build_sample_export_link( 'players' ) ) . '">' . esc_html__( 'Players', 'mahl-league' ) . '</a> | ';
		echo '<a href="' . esc_url( self::build_sample_export_link( 'matches' ) ) . '">' . esc_html__( 'Matches', 'mahl-league' ) . '</a>';
		echo '</p>';

		echo '<hr />';
		echo '<h2>' . esc_html__( 'Export CSV', 'mahl-league' ) . '</h2>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="ml_export_csv" />';
		wp_nonce_field( 'ml_export_csv', 'ml_export_csv_nonce' );

		echo '<table class="form-table" role="presentation"><tbody>';
		echo '<tr><th scope="row"><label for="ml-export-entity">' . esc_html__( 'Entity', 'mahl-league' ) . '</label></th>';
		echo '<td><select id="ml-export-entity" name="entity">';
		echo '<option value="teams">' . esc_html__( 'Teams', 'mahl-league' ) . '</option>';
		echo '<option value="players">' . esc_html__( 'Players', 'mahl-league' ) . '</option>';
		echo '<option value="matches">' . esc_html__( 'Matches', 'mahl-league' ) . '</option>';
		echo '</select></td></tr>';

		echo '<tr><th scope="row"><label for="ml-export-team-id">' . esc_html__( 'Team filter (players)', 'mahl-league' ) . '</label></th><td><select id="ml-export-team-id" name="team_id">';
		echo '<option value="0">' . esc_html__( 'All teams', 'mahl-league' ) . '</option>';
		foreach ( $teams as $team ) {
			echo '<option value="' . esc_attr( (string) $team->ID ) . '">' . esc_html( $team->post_title ) . '</option>';
		}
		echo '</select></td></tr>';

		echo '<tr><th scope="row"><label for="ml-export-season-id">' . esc_html__( 'Season filter (matches)', 'mahl-league' ) . '</label></th><td><select id="ml-export-season-id" name="season_id">';
		echo '<option value="0">' . esc_html__( 'All seasons', 'mahl-league' ) . '</option>';
		if ( is_array( $seasons ) ) {
			foreach ( $seasons as $season ) {
				echo '<option value="' . esc_attr( (string) $season->term_id ) . '">' . esc_html( $season->name ) . '</option>';
			}
		}
		echo '</select></td></tr>';

		echo '<tr><th scope="row"><label for="ml-export-competition-id">' . esc_html__( 'Competition filter (matches)', 'mahl-league' ) . '</label></th><td><select id="ml-export-competition-id" name="competition_id">';
		echo '<option value="0">' . esc_html__( 'All competitions', 'mahl-league' ) . '</option>';
		if ( is_array( $competitions ) ) {
			foreach ( $competitions as $competition ) {
				echo '<option value="' . esc_attr( (string) $competition->term_id ) . '">' . esc_html( $competition->name ) . '</option>';
			}
		}
		echo '</select></td></tr>';
		echo '</tbody></table>';

		submit_button( __( 'Export CSV', 'mahl-league' ), 'secondary' );
		echo '</form>';
		echo '</div>';
	}

	/**
	 * Render settings screen placeholder.
	 *
	 * @return void
	 */
	public static function render_settings(): void {
		if ( ! current_user_can( 'manage_ml_league' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'mahl-league' ) );
		}

		$required_pages = ML_Pages_Service::get_required_pages();
		$pages_map      = ML_Pages_Service::get_pages_map();

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'Settings', 'mahl-league' ) . '</h1>';
		echo '<h2>' . esc_html__( 'Pages', 'mahl-league' ) . '</h2>';

		if ( isset( $_GET['ml_pages_repaired'] ) && '1' === wp_unslash( $_GET['ml_pages_repaired'] ) ) {
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Pages were created/repaired.', 'mahl-league' ) . '</p></div>';
		}

		echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Slug', 'mahl-league' ) . '</th><th>' . esc_html__( 'Status', 'mahl-league' ) . '</th><th>' . esc_html__( 'Page ID', 'mahl-league' ) . '</th><th>' . esc_html__( 'Links', 'mahl-league' ) . '</th></tr></thead><tbody>';
		foreach ( $required_pages as $slug => $def ) {
			$page = get_page_by_path( $slug, OBJECT, 'page' );
			echo '<tr>';
			echo '<td>' . esc_html( $slug ) . '</td>';
			echo '<td>' . esc_html( $page instanceof WP_Post ? __( 'Exists', 'mahl-league' ) : __( 'Missing', 'mahl-league' ) ) . '</td>';
			echo '<td>' . esc_html( (string) ( $page instanceof WP_Post ? $page->ID : ( isset( $pages_map[ $slug ] ) ? absint( $pages_map[ $slug ] ) : 0 ) ) ) . '</td>';
			echo '<td>';
			if ( $page instanceof WP_Post ) {
				echo '<a href="' . esc_url( get_edit_post_link( $page->ID ) ) . '">' . esc_html__( 'Edit', 'mahl-league' ) . '</a> | ';
				echo '<a href="' . esc_url( get_permalink( $page->ID ) ) . '" target="_blank" rel="noopener">' . esc_html__( 'View', 'mahl-league' ) . '</a>';
			} else {
				echo '&mdash;';
			}
			echo '</td>';
			echo '</tr>';
		}
		echo '</tbody></table>';

		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin-top:16px;">';
		echo '<input type="hidden" name="action" value="ml_create_repair_pages" />';
		wp_nonce_field( 'ml_create_repair_pages', 'ml_pages_nonce' );
		submit_button( __( 'Create/Repair Pages', 'mahl-league' ), 'secondary', 'submit', false );
		echo '</form>';
		echo '</div>';
	}

	/**
	 * Render IDs screen.
	 *
	 * @return void
	 */
	public static function render_ids(): void {
		if ( ! current_user_can( 'manage_ml_league' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'mahl-league' ) );
		}
		echo '<div class="wrap"><h1>' . esc_html__( 'IDs', 'mahl-league' ) . '</h1>';
		echo '<script>function mlCopyId(v){navigator.clipboard&&navigator.clipboard.writeText(String(v));}</script>';
		self::render_ids_table( 'ml_team', __( 'Teams', 'mahl-league' ) );
		self::render_ids_table( 'ml_player', __( 'Players', 'mahl-league' ) );
		self::render_ids_table( 'ml_match', __( 'Matches', 'mahl-league' ) );
		self::render_term_ids_table( 'ml_season', __( 'Seasons', 'mahl-league' ) );
		self::render_term_ids_table( 'ml_competition', __( 'Competitions', 'mahl-league' ) );
		self::render_term_ids_table( 'ml_phase', __( 'Phases', 'mahl-league' ) );
		echo '<h2>' . esc_html__( 'Plugin Pages', 'mahl-league' ) . '</h2><ul>';
		foreach ( ML_Pages_Service::get_required_pages() as $slug => $def ) {
			$page = get_page_by_path( $slug, OBJECT, 'page' );
			echo '<li>' . esc_html( $slug ) . ': ' . esc_html( (string) ( $page instanceof WP_Post ? $page->ID : 0 ) ) . '</li>';
		}
		echo '</ul></div>';
	}

	/**
	 * Render backup/restore screen.
	 *
	 * @return void
	 */
	public static function render_backup_restore(): void {
		if ( ! current_user_can( 'manage_ml_league' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'mahl-league' ) );
		}
		echo '<div class="wrap"><h1>' . esc_html__( 'Backup / Restore', 'mahl-league' ) . '</h1>';
		if ( isset( $_GET['ml_restore_done'] ) && '1' === wp_unslash( $_GET['ml_restore_done'] ) ) {
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Restore completed.', 'mahl-league' ) . '</p></div>';
		}
		echo '<h2>' . esc_html__( 'Export', 'mahl-league' ) . '</h2>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="ml_export_backup" />';
		wp_nonce_field( 'ml_export_backup', 'ml_backup_export_nonce' );
		submit_button( __( 'Download JSON Backup', 'mahl-league' ), 'secondary', 'submit', false );
		echo '</form>';
		echo '<h2>' . esc_html__( 'Restore', 'mahl-league' ) . '</h2>';
		echo '<form method="post" enctype="multipart/form-data" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="ml_restore_backup" />';
		wp_nonce_field( 'ml_restore_backup', 'ml_backup_restore_nonce' );
		echo '<input type="file" name="backup_file" accept="application/json,.json" required />';
		submit_button( __( 'Restore from JSON', 'mahl-league' ), 'primary', 'submit', false );
		echo '</form></div>';
	}

	/**
	 * Render match editor screen.
	 *
	 * @return void
	 */
	public static function render_match_editor(): void {
		if ( ! current_user_can( 'manage_ml_league' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'mahl-league' ) );
		}

		$matches = get_posts(
			array(
				'post_type'      => 'ml_match',
				'posts_per_page' => 100,
				'post_status'    => array( 'publish', 'draft', 'pending', 'future' ),
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$teams = get_posts(
			array(
				'post_type'      => 'ml_team',
				'posts_per_page' => 200,
				'post_status'    => array( 'publish', 'draft' ),
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		$match_id = isset( $_GET['match_id'] ) ? absint( wp_unslash( $_GET['match_id'] ) ) : 0;

		$current = array(
			'home_team'   => 0,
			'away_team'   => 0,
			'datetime'    => '',
			'venue'       => '',
			'referees'    => '',
			'status'      => 'scheduled',
			'home_score'  => 0,
			'away_score'  => 0,
			'stage_label' => '',
			'round_label' => '',
		);

		if ( $match_id > 0 ) {
			$current['home_team']   = absint( get_post_meta( $match_id, 'ml_home_team_id', true ) );
			$current['away_team']   = absint( get_post_meta( $match_id, 'ml_away_team_id', true ) );
			$current['datetime']    = (string) get_post_meta( $match_id, 'ml_match_datetime', true );
			$current['venue']       = (string) get_post_meta( $match_id, 'ml_venue', true );
			$current['referees']    = (string) get_post_meta( $match_id, 'ml_referees', true );
			$current['status']      = (string) get_post_meta( $match_id, 'ml_status', true );
			$current['home_score']  = absint( get_post_meta( $match_id, 'ml_home_score', true ) );
			$current['away_score']  = absint( get_post_meta( $match_id, 'ml_away_score', true ) );
			$current['stage_label'] = (string) get_post_meta( $match_id, 'ml_stage_label', true );
			$current['round_label'] = (string) get_post_meta( $match_id, 'ml_round_label', true );
		}

		$statuses = array(
			'scheduled' => __( 'Scheduled', 'mahl-league' ),
			'played'    => __( 'Played', 'mahl-league' ),
			'canceled'  => __( 'Canceled', 'mahl-league' ),
		);

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'Match Editor', 'mahl-league' ) . '</h1>';

		if ( isset( $_GET['ml_updated'] ) && '1' === wp_unslash( $_GET['ml_updated'] ) ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Match data has been saved.', 'mahl-league' ) . '</p></div>';
		}

		if ( isset( $_GET['ml_match_notice'] ) ) {
			$notice_key = sanitize_key( wp_unslash( $_GET['ml_match_notice'] ) );
			$messages   = array(
				'nonce_failed'     => __( 'Security check failed. Please retry.', 'mahl-league' ),
				'invalid_match'    => __( 'Invalid match selected.', 'mahl-league' ),
				'capability_failed'=> __( 'You do not have permission to save this match.', 'mahl-league' ),
				'validation_failed' => __( 'Validation failed: please select valid home and away teams.', 'mahl-league' ),
				'save_failed'      => __( 'Unable to save match. Please try again.', 'mahl-league' ),
			);

			if ( isset( $messages[ $notice_key ] ) ) {
				echo '<div class="notice notice-error"><p>' . esc_html( $messages[ $notice_key ] ) . '</p></div>';
			}
		}

		echo '<form method="get" action="">';
		echo '<input type="hidden" name="page" value="ml-match-editor" />';
		echo '<label for="ml-match-id"><strong>' . esc_html__( 'Select Match', 'mahl-league' ) . '</strong></label> ';
		echo '<select id="ml-match-id" name="match_id">';
		echo '<option value="0">' . esc_html__( 'Choose a match', 'mahl-league' ) . '</option>';
		foreach ( $matches as $match ) {
			echo '<option value="' . esc_attr( (string) $match->ID ) . '" ' . selected( $match_id, $match->ID, false ) . '>' . esc_html( $match->post_title ) . '</option>';
		}
		echo '</select> ';
		submit_button( __( 'Load Match', 'mahl-league' ), 'secondary', '', false );
		echo '</form>';

		echo '<hr />';
		echo '<h2>' . esc_html__( 'Match Overview', 'mahl-league' ) . '</h2>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="ml_save_match_editor" />';
		echo '<input type="hidden" name="match_id" value="' . esc_attr( (string) $match_id ) . '" />';
		wp_nonce_field( 'ml_save_match_editor', 'ml_match_editor_nonce' );

		echo '<h3>' . esc_html__( 'Teams', 'mahl-league' ) . '</h3>';
		echo '<table class="form-table" role="presentation"><tbody>';
		self::render_team_select_row( 'ml_home_team_id', __( 'Home Team', 'mahl-league' ), $teams, $current['home_team'] );
		self::render_team_select_row( 'ml_away_team_id', __( 'Away Team', 'mahl-league' ), $teams, $current['away_team'] );
		echo '</tbody></table>';

		echo '<h3>' . esc_html__( 'Schedule & Venue', 'mahl-league' ) . '</h3>';
		echo '<table class="form-table" role="presentation"><tbody>';
		self::render_input_row( 'ml_match_datetime', __( 'Date & Time', 'mahl-league' ), $current['datetime'], 'datetime-local' );
		self::render_input_row( 'ml_venue', __( 'Venue', 'mahl-league' ), $current['venue'] );
		self::render_input_row( 'ml_referees', __( 'Referees', 'mahl-league' ), $current['referees'] );
		echo '</tbody></table>';

		echo '<h3>' . esc_html__( 'Result & Labels', 'mahl-league' ) . '</h3>';
		echo '<table class="form-table" role="presentation"><tbody>';
		self::render_select_row( 'ml_status', __( 'Status', 'mahl-league' ), $statuses, $current['status'] );
		self::render_input_row( 'ml_home_score', __( 'Home Score', 'mahl-league' ), (string) $current['home_score'], 'number' );
		self::render_input_row( 'ml_away_score', __( 'Away Score', 'mahl-league' ), (string) $current['away_score'], 'number' );
		self::render_input_row( 'ml_stage_label', __( 'Stage Label', 'mahl-league' ), $current['stage_label'] );
		self::render_input_row( 'ml_round_label', __( 'Round Label', 'mahl-league' ), $current['round_label'] );
		echo '</tbody></table>';

		submit_button( __( 'Save Match', 'mahl-league' ), 'primary', 'submit', true );
		echo '</form>';
		echo '</div>';
	}

	/**
	 * Security template for future admin_post_ml_* handlers:
	 * 1) Verify capability checks before state changes.
	 * 2) Verify nonce with check_admin_referer()/wp_verify_nonce().
	 * 3) Sanitize all input (including recursive arrays).
	 * 4) Redirect with wp_safe_redirect() and exit.
	 */

	/**
	 * Handle match editor save.
	 *
	 * @return void
	 */
	public static function handle_match_editor_save(): void {
		if ( ! isset( $_POST['ml_match_editor_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ml_match_editor_nonce'] ) ), 'ml_save_match_editor' ) ) {
			self::log_match_editor_error( 'nonce_failed' );
			self::redirect_match_editor( 0, 'nonce_failed' );
		}

		$match_id = isset( $_POST['match_id'] ) ? absint( wp_unslash( $_POST['match_id'] ) ) : 0;

		if ( $match_id > 0 && 'ml_match' !== get_post_type( $match_id ) ) {
			self::log_match_editor_error( 'invalid_match', array( 'match_id' => $match_id ) );
			self::redirect_match_editor( 0, 'invalid_match' );
		}

		if ( $match_id > 0 ) {
			if ( ! current_user_can( 'edit_post', $match_id ) && ! current_user_can( 'edit_ml_match' ) && ! current_user_can( 'edit_ml_matches' ) ) {
				self::log_match_editor_error( 'capability_failed_existing', array( 'match_id' => $match_id ) );
				self::redirect_match_editor( $match_id, 'capability_failed' );
			}
		} elseif ( ! current_user_can( 'publish_ml_matches' ) && ! current_user_can( 'edit_ml_matches' ) ) {
			self::log_match_editor_error( 'capability_failed_create' );
			self::redirect_match_editor( 0, 'capability_failed' );
		}

		$home_team_id = isset( $_POST['ml_home_team_id'] ) ? absint( wp_unslash( $_POST['ml_home_team_id'] ) ) : 0;
		$away_team_id = isset( $_POST['ml_away_team_id'] ) ? absint( wp_unslash( $_POST['ml_away_team_id'] ) ) : 0;
		if ( $home_team_id <= 0 || $away_team_id <= 0 || $home_team_id === $away_team_id ) {
			self::log_match_editor_error( 'validation_failed_teams', array( 'home' => $home_team_id, 'away' => $away_team_id ) );
			self::redirect_match_editor( $match_id, 'validation_failed' );
		}

		$allowed_statuses = array( 'scheduled', 'played', 'canceled' );
		$status           = isset( $_POST['ml_status'] ) ? sanitize_text_field( wp_unslash( $_POST['ml_status'] ) ) : 'scheduled';
		if ( ! in_array( $status, $allowed_statuses, true ) ) {
			$status = 'scheduled';
		}

		$datetime    = isset( $_POST['ml_match_datetime'] ) ? sanitize_text_field( wp_unslash( $_POST['ml_match_datetime'] ) ) : '';
		$venue       = isset( $_POST['ml_venue'] ) ? sanitize_text_field( wp_unslash( $_POST['ml_venue'] ) ) : '';
		$referees    = isset( $_POST['ml_referees'] ) ? sanitize_text_field( wp_unslash( $_POST['ml_referees'] ) ) : '';
		$home_score  = isset( $_POST['ml_home_score'] ) ? absint( wp_unslash( $_POST['ml_home_score'] ) ) : 0;
		$away_score  = isset( $_POST['ml_away_score'] ) ? absint( wp_unslash( $_POST['ml_away_score'] ) ) : 0;
		$stage_label = isset( $_POST['ml_stage_label'] ) ? sanitize_text_field( wp_unslash( $_POST['ml_stage_label'] ) ) : '';
		$round_label = isset( $_POST['ml_round_label'] ) ? sanitize_text_field( wp_unslash( $_POST['ml_round_label'] ) ) : '';

		if ( $match_id <= 0 ) {
			$home_name   = (string) get_the_title( $home_team_id );
			$away_name   = (string) get_the_title( $away_team_id );
			$match_title = trim( $home_name . ' vs ' . $away_name );
			if ( '' !== $datetime ) {
				$match_title .= ' (' . $datetime . ')';
			}

			$inserted = wp_insert_post(
				wp_slash(
					array(
						'post_type'   => 'ml_match',
						'post_status' => 'publish',
						'post_title'  => '' !== $match_title ? $match_title : __( 'New Match', 'mahl-league' ),
					)
				),
				true
			);

			if ( is_wp_error( $inserted ) ) {
				self::log_match_editor_error( 'insert_failed', array( 'error' => $inserted->get_error_message() ) );
				self::redirect_match_editor( 0, 'save_failed' );
			}

			$match_id = absint( $inserted );
		}

		$updated_title = trim( (string) get_the_title( $home_team_id ) . ' vs ' . (string) get_the_title( $away_team_id ) );
		if ( '' !== $updated_title ) {
			wp_update_post(
				wp_slash(
					array(
						'ID'         => $match_id,
						'post_title' => $updated_title,
					)
				)
			);
		}

		update_post_meta( $match_id, 'ml_home_team_id', $home_team_id );
		update_post_meta( $match_id, 'ml_away_team_id', $away_team_id );
		update_post_meta( $match_id, 'ml_match_datetime', $datetime );
		update_post_meta( $match_id, 'ml_venue', $venue );
		update_post_meta( $match_id, 'ml_referees', $referees );
		update_post_meta( $match_id, 'ml_status', $status );
		update_post_meta( $match_id, 'ml_home_score', $home_score );
		update_post_meta( $match_id, 'ml_away_score', $away_score );
		update_post_meta( $match_id, 'ml_stage_label', $stage_label );
		update_post_meta( $match_id, 'ml_round_label', $round_label );

		self::redirect_match_editor( $match_id );
	}

	/**
	 * Handle team save action.
	 *
	 * @return void
	 */
	public static function handle_save_team(): void {
		if ( ! current_user_can( 'edit_ml_teams' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'mahl-league' ) );
		}

		check_admin_referer( 'ml_save_team', 'ml_save_team_nonce' );

		$team_id     = isset( $_POST['team_id'] ) ? absint( wp_unslash( $_POST['team_id'] ) ) : 0;
		$team_name   = isset( $_POST['team_name'] ) ? sanitize_text_field( wp_unslash( $_POST['team_name'] ) ) : '';
		$team_slug   = isset( $_POST['team_slug'] ) ? sanitize_title( wp_unslash( $_POST['team_slug'] ) ) : '';
		$short_name  = isset( $_POST['team_short_name'] ) ? sanitize_text_field( wp_unslash( $_POST['team_short_name'] ) ) : '';
		$logo_url    = isset( $_POST['team_logo_url'] ) ? esc_url_raw( wp_unslash( $_POST['team_logo_url'] ) ) : '';

		if ( '' === $team_name ) {
			self::redirect_admin_page( 'ml-teams', array( 'ml_notice' => 'team_name_required', 'ml_notice_type' => 'error' ) );
		}

		if ( $team_id > 0 && 'ml_team' !== get_post_type( $team_id ) ) {
			wp_die( esc_html__( 'Invalid team selected.', 'mahl-league' ) );
		}

		$post_data = array(
			'post_title'  => $team_name,
			'post_type'   => 'ml_team',
			'post_status' => 'publish',
		);

		if ( '' !== $team_slug ) {
			$post_data['post_name'] = $team_slug;
		}

		if ( $team_id > 0 ) {
			if ( ! current_user_can( 'edit_post', $team_id ) ) {
				wp_die( esc_html__( 'You do not have permission to perform this action.', 'mahl-league' ) );
			}
			$post_data['ID'] = $team_id;
			$result          = wp_update_post( wp_slash( $post_data ), true );
		} else {
			$result = wp_insert_post( wp_slash( $post_data ), true );
		}

		if ( is_wp_error( $result ) ) {
			self::redirect_admin_page( 'ml-teams', array( 'ml_notice' => 'team_save_error', 'ml_notice_type' => 'error' ) );
		}

		$team_id = absint( $result );
		update_post_meta( $team_id, 'ml_team_short', $short_name );
		update_post_meta( $team_id, 'ml_team_logo_url', $logo_url );

		self::redirect_admin_page( 'ml-teams', array( 'ml_notice' => 'team_saved', 'ml_notice_type' => 'success' ) );
	}

	/**
	 * Handle team delete action.
	 *
	 * @return void
	 */
	public static function handle_delete_team(): void {
		$team_id = isset( $_GET['team_id'] ) ? absint( wp_unslash( $_GET['team_id'] ) ) : 0;
		if ( $team_id <= 0 || 'ml_team' !== get_post_type( $team_id ) ) {
			wp_die( esc_html__( 'Invalid team selected.', 'mahl-league' ) );
		}

		if ( ! current_user_can( 'delete_post', $team_id ) && ! current_user_can( 'delete_ml_teams' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'mahl-league' ) );
		}

		if ( ! isset( $_GET['ml_delete_team_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['ml_delete_team_nonce'] ) ), 'ml_delete_team_' . $team_id ) ) {
			wp_die( esc_html__( 'Invalid request.', 'mahl-league' ) );
		}

		wp_delete_post( $team_id, true );
		self::redirect_admin_page( 'ml-teams', array( 'ml_notice' => 'team_deleted', 'ml_notice_type' => 'success' ) );
	}

	/**
	 * Handle player save action.
	 *
	 * @return void
	 */
	public static function handle_save_player(): void {
		if ( ! current_user_can( 'edit_ml_players' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'mahl-league' ) );
		}

		check_admin_referer( 'ml_save_player', 'ml_save_player_nonce' );

		$player_id    = isset( $_POST['player_id'] ) ? absint( wp_unslash( $_POST['player_id'] ) ) : 0;
		$player_name  = isset( $_POST['player_name'] ) ? sanitize_text_field( wp_unslash( $_POST['player_name'] ) ) : '';
		$number       = isset( $_POST['player_number'] ) ? absint( wp_unslash( $_POST['player_number'] ) ) : 0;
		$position     = isset( $_POST['player_position'] ) ? sanitize_text_field( wp_unslash( $_POST['player_position'] ) ) : '';
		$team_id      = isset( $_POST['player_team_id'] ) ? absint( wp_unslash( $_POST['player_team_id'] ) ) : 0;
		$external_id  = isset( $_POST['player_external_id'] ) ? sanitize_text_field( wp_unslash( $_POST['player_external_id'] ) ) : '';

		if ( '' === $player_name || $team_id <= 0 ) {
			self::redirect_admin_page( 'ml-players', array( 'ml_notice' => 'player_required_fields', 'ml_notice_type' => 'error' ) );
		}

		if ( 'ml_team' !== get_post_type( $team_id ) ) {
			self::redirect_admin_page( 'ml-players', array( 'ml_notice' => 'player_invalid_team', 'ml_notice_type' => 'error' ) );
		}

		if ( $player_id > 0 && 'ml_player' !== get_post_type( $player_id ) ) {
			wp_die( esc_html__( 'Invalid player selected.', 'mahl-league' ) );
		}

		$post_data = array(
			'post_title'  => $player_name,
			'post_type'   => 'ml_player',
			'post_status' => 'publish',
		);

		if ( $player_id > 0 ) {
			if ( ! current_user_can( 'edit_post', $player_id ) ) {
				wp_die( esc_html__( 'You do not have permission to perform this action.', 'mahl-league' ) );
			}
			$post_data['ID'] = $player_id;
			$result          = wp_update_post( wp_slash( $post_data ), true );
		} else {
			$result = wp_insert_post( wp_slash( $post_data ), true );
		}

		if ( is_wp_error( $result ) ) {
			self::redirect_admin_page( 'ml-players', array( 'ml_notice' => 'player_save_error', 'ml_notice_type' => 'error' ) );
		}

		$player_id = absint( $result );
		update_post_meta( $player_id, 'ml_player_number', $number > 0 ? (string) $number : '' );
		update_post_meta( $player_id, 'ml_player_position', $position );
		update_post_meta( $player_id, 'ml_player_team_id', $team_id );
		update_post_meta( $player_id, 'ml_external_id', $external_id );

		self::redirect_admin_page( 'ml-players', array( 'ml_notice' => 'player_saved', 'ml_notice_type' => 'success' ) );
	}

	/**
	 * Handle CSV import action.
	 *
	 * @return void
	 */
	public static function handle_import_csv(): void {
		if ( ! current_user_can( 'manage_ml_league' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'mahl-league' ) );
		}

		check_admin_referer( 'ml_import_csv', 'ml_import_csv_nonce' );

		$entity  = isset( $_POST['entity'] ) ? sanitize_key( wp_unslash( $_POST['entity'] ) ) : '';
		$dry_run = isset( $_POST['dry_run'] ) && '1' === wp_unslash( $_POST['dry_run'] );
		if ( ! in_array( $entity, array( 'teams', 'players', 'matches' ), true ) ) {
			self::redirect_import_export( array( 'ml_message' => 'invalid_entity' ) );
		}

		if ( empty( $_FILES['import_file']['name'] ) ) {
			self::redirect_import_export( array( 'ml_message' => 'missing_file' ) );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		$upload = wp_handle_upload(
			$_FILES['import_file'],
			array(
				'test_form' => false,
				'mimes'     => array(
					'csv' => 'text/csv',
					'txt' => 'text/plain',
				),
			)
		);

		if ( isset( $upload['error'] ) ) {
			self::redirect_import_export( array( 'ml_message' => 'upload_error' ) );
		}

		$result = ML_Import_Export_Service::import_csv( $entity, (string) $upload['file'], $dry_run );

		$token = wp_generate_password( 20, false, false );
		set_transient(
			'ml_import_result_' . $token,
			array(
				'entity'  => $entity,
				'dry_run' => $dry_run,
				'result'  => $result,
			),
			15 * MINUTE_IN_SECONDS
		);

		self::redirect_import_export(
			array(
				'ml_import_result' => $token,
			)
		);
	}

	/**
	 * Handle CSV export action.
	 *
	 * @return void
	 */
	public static function handle_export_csv(): void {
		if ( ! current_user_can( 'manage_ml_league' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'mahl-league' ) );
		}

		$is_sample = isset( $_GET['sample'] ) && '1' === wp_unslash( $_GET['sample'] );
		if ( $is_sample ) {
			if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'ml_export_sample_csv' ) ) {
				wp_die( esc_html__( 'Invalid sample download request.', 'mahl-league' ) );
			}

			$entity = isset( $_GET['entity'] ) ? sanitize_key( wp_unslash( $_GET['entity'] ) ) : '';
			if ( ! in_array( $entity, array( 'teams', 'players', 'matches' ), true ) ) {
				wp_die( esc_html__( 'Invalid export entity.', 'mahl-league' ) );
			}

			$rows = ML_Import_Export_Service::sample_rows( $entity );
			self::send_csv_response( $entity . '-sample', $rows );
		}

		check_admin_referer( 'ml_export_csv', 'ml_export_csv_nonce' );

		$entity = isset( $_POST['entity'] ) ? sanitize_key( wp_unslash( $_POST['entity'] ) ) : '';
		if ( ! in_array( $entity, array( 'teams', 'players', 'matches' ), true ) ) {
			self::redirect_import_export( array( 'ml_message' => 'invalid_entity' ) );
		}

		$filters = array(
			'season_id'      => isset( $_POST['season_id'] ) ? absint( wp_unslash( $_POST['season_id'] ) ) : 0,
			'competition_id' => isset( $_POST['competition_id'] ) ? absint( wp_unslash( $_POST['competition_id'] ) ) : 0,
			'team_id'        => isset( $_POST['team_id'] ) ? absint( wp_unslash( $_POST['team_id'] ) ) : 0,
		);

		$rows = ML_Import_Export_Service::export_rows( $entity, $filters );
		self::send_csv_response( $entity . '-export-' . gmdate( 'Ymd-His' ), $rows );
	}

	/**
	 * Download import errors as CSV.
	 *
	 * @return void
	 */
	public static function handle_import_errors_csv(): void {
		if ( ! current_user_can( 'manage_ml_league' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'mahl-league' ) );
		}

		check_admin_referer( 'ml_import_errors_csv', 'ml_import_errors_nonce' );

		$token = isset( $_GET['result'] ) ? sanitize_key( wp_unslash( $_GET['result'] ) ) : '';
		if ( '' === $token ) {
			wp_die( esc_html__( 'Missing import result token.', 'mahl-league' ) );
		}

		$stored = get_transient( 'ml_import_result_' . $token );
		if ( ! is_array( $stored ) || empty( $stored['result']['error_messages'] ) ) {
			wp_die( esc_html__( 'Import error report is not available.', 'mahl-league' ) );
		}

		$rows   = array();
		$rows[] = array( 'row', 'message' );
		foreach ( $stored['result']['error_messages'] as $error ) {
			$rows[] = array(
				(string) ( isset( $error['row'] ) ? absint( $error['row'] ) : 0 ),
				(string) ( isset( $error['message'] ) ? sanitize_text_field( $error['message'] ) : '' ),
			);
		}

		self::send_csv_response( 'import-errors-' . gmdate( 'Ymd-His' ), $rows );
	}


	/**
	 * Handle create/repair pages action.
	 *
	 * @return void
	 */
	public static function handle_create_repair_pages(): void {
		if ( ! current_user_can( 'manage_ml_league' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'mahl-league' ) );
		}
		check_admin_referer( 'ml_create_repair_pages', 'ml_pages_nonce' );
		ML_Pages_Service::ensure_pages( true );
		$redirect_url = add_query_arg(
			array(
				'page' => 'ml-settings',
				'ml_pages_repaired' => 1,
			),
			admin_url( 'admin.php' )
		);
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Handle backup export.
	 *
	 * @return void
	 */
	public static function handle_export_backup(): void {
		if ( ! current_user_can( 'manage_ml_league' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'mahl-league' ) );
		}
		check_admin_referer( 'ml_export_backup', 'ml_backup_export_nonce' );
		$payload = ML_Backup_Restore_Service::export_data();
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=mahl-league-backup-' . gmdate( 'Ymd-His' ) . '.json' );
		echo wp_json_encode( $payload );
		exit;
	}

	/**
	 * Handle backup restore.
	 *
	 * @return void
	 */
	public static function handle_restore_backup(): void {
		if ( ! current_user_can( 'manage_ml_league' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'mahl-league' ) );
		}
		check_admin_referer( 'ml_restore_backup', 'ml_backup_restore_nonce' );
		if ( empty( $_FILES['backup_file']['name'] ) ) {
			self::redirect_backup_screen();
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		$upload = wp_handle_upload(
			$_FILES['backup_file'],
			array(
				'test_form' => false,
				'mimes'     => array( 'json' => 'application/json' ),
			)
		);
		if ( isset( $upload['error'] ) ) {
			self::redirect_backup_screen();
		}

		$content = file_get_contents( (string) $upload['file'] );
		if ( false === $content ) {
			self::redirect_backup_screen();
		}
		$payload = json_decode( $content, true );
		if ( ! is_array( $payload ) ) {
			self::redirect_backup_screen();
		}
		ML_Backup_Restore_Service::restore_data( $payload );
		$redirect_url = add_query_arg(
			array(
				'page' => 'ml-backup-restore',
				'ml_restore_done' => 1,
			),
			admin_url( 'admin.php' )
		);
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Render CPT ID table.
	 *
	 * @param string $post_type Post type.
	 * @param string $title     Title.
	 *
	 * @return void
	 */
	private static function render_ids_table( string $post_type, string $title ): void {
		$items = get_posts( array( 'post_type' => $post_type, 'post_status' => 'any', 'posts_per_page' => 200, 'orderby' => 'title', 'order' => 'ASC' ) );
		echo '<h2>' . esc_html( $title ) . '</h2><table class="widefat striped"><thead><tr><th>' . esc_html__( 'Title', 'mahl-league' ) . '</th><th>ID</th><th>' . esc_html__( 'Edit', 'mahl-league' ) . '</th></tr></thead><tbody>';
		foreach ( $items as $item ) {
			echo '<tr><td>' . esc_html( $item->post_title ) . '</td><td>' . esc_html( (string) $item->ID ) . ' <button type="button" class="button button-small" onclick="mlCopyId(' . esc_attr( (string) $item->ID ) . ')">' . esc_html__( 'Copy', 'mahl-league' ) . '</button></td><td><a href="' . esc_url( get_edit_post_link( $item->ID ) ) . '">' . esc_html__( 'Edit', 'mahl-league' ) . '</a></td></tr>';
		}
		echo '</tbody></table>';
	}

	/**
	 * Render taxonomy ID table.
	 *
	 * @param string $taxonomy Taxonomy.
	 * @param string $title    Title.
	 *
	 * @return void
	 */
	private static function render_term_ids_table( string $taxonomy, string $title ): void {
		$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );
		echo '<h2>' . esc_html( $title ) . '</h2><table class="widefat striped"><thead><tr><th>' . esc_html__( 'Name', 'mahl-league' ) . '</th><th>' . esc_html__( 'Term ID', 'mahl-league' ) . '</th></tr></thead><tbody>';
		if ( is_array( $terms ) ) {
			foreach ( $terms as $term ) {
				echo '<tr><td>' . esc_html( $term->name ) . '</td><td>' . esc_html( (string) $term->term_id ) . '</td></tr>';
			}
		}
		echo '</tbody></table>';
	}

	/**
	 * Redirect to backup screen.
	 *
	 * @return void
	 */
	private static function redirect_backup_screen(): void {
		$redirect_url = add_query_arg( array( 'page' => 'ml-backup-restore' ), admin_url( 'admin.php' ) );
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Recursively sanitize data for future complex request payloads.
	 *
	 * @param mixed $value Incoming request value.
	 *
	 * @return mixed
	 */
	private static function sanitize_recursive( $value ) {
		if ( is_array( $value ) ) {
			$sanitized = array();
			foreach ( $value as $key => $item ) {
				$sanitized[ sanitize_key( (string) $key ) ] = self::sanitize_recursive( $item );
			}

			return $sanitized;
		}

		if ( is_bool( $value ) ) {
			return (bool) $value;
		}

		if ( is_int( $value ) || ( is_string( $value ) && is_numeric( $value ) ) ) {
			return absint( $value );
		}

		if ( is_scalar( $value ) ) {
			return sanitize_text_field( (string) $value );
		}

		return '';
	}

	/**
	 * Render import screen notices.
	 *
	 * @return void
	 */
	private static function render_import_notices(): void {
		if ( isset( $_GET['ml_message'] ) ) {
			$code    = sanitize_key( wp_unslash( $_GET['ml_message'] ) );
			$message = '';
			if ( 'invalid_entity' === $code ) {
				$message = __( 'Invalid entity selection.', 'mahl-league' );
			} elseif ( 'missing_file' === $code ) {
				$message = __( 'Please upload a CSV file.', 'mahl-league' );
			} elseif ( 'upload_error' === $code ) {
				$message = __( 'CSV upload failed.', 'mahl-league' );
			}

			if ( '' !== $message ) {
				echo '<div class="notice notice-error"><p>' . esc_html( $message ) . '</p></div>';
			}
		}

		if ( ! isset( $_GET['ml_import_result'] ) ) {
			return;
		}

		$token  = sanitize_key( wp_unslash( $_GET['ml_import_result'] ) );
		$stored = get_transient( 'ml_import_result_' . $token );
		if ( ! is_array( $stored ) || empty( $stored['result'] ) ) {
			return;
		}

		$result  = $stored['result'];
		$notice  = ( isset( $result['errors'] ) && (int) $result['errors'] > 0 ) ? 'notice-warning' : 'notice-success';
		$dry_run = ! empty( $stored['dry_run'] ) ? __( ' (dry run)', 'mahl-league' ) : '';

		echo '<div class="notice ' . esc_attr( $notice ) . '"><p>';
		echo esc_html(
			sprintf(
				/* translators: 1: created count, 2: updated count, 3: skipped count, 4: errors count, 5: dry-run marker. */
				__( 'Import summary%5$s: created %1$d, updated %2$d, skipped %3$d, errors %4$d.', 'mahl-league' ),
				isset( $result['created'] ) ? absint( $result['created'] ) : 0,
				isset( $result['updated'] ) ? absint( $result['updated'] ) : 0,
				isset( $result['skipped'] ) ? absint( $result['skipped'] ) : 0,
				isset( $result['errors'] ) ? absint( $result['errors'] ) : 0,
				$dry_run
			)
		);
		echo '</p>';

		if ( ! empty( $result['error_messages'] ) ) {
			echo '<p><strong>' . esc_html__( 'First 50 errors:', 'mahl-league' ) . '</strong></p><ol>';
			foreach ( $result['error_messages'] as $error ) {
				echo '<li>';
				echo esc_html(
					sprintf(
						/* translators: 1: CSV row, 2: error message. */
						__( 'Row %1$d: %2$s', 'mahl-league' ),
						isset( $error['row'] ) ? absint( $error['row'] ) : 0,
						isset( $error['message'] ) ? sanitize_text_field( $error['message'] ) : ''
					)
				);
				echo '</li>';
			}
			echo '</ol>';

			$error_link = wp_nonce_url(
				add_query_arg(
					array(
						'action' => 'ml_import_errors_csv',
						'result' => $token,
					),
					admin_url( 'admin-post.php' )
				),
				'ml_import_errors_csv',
				'ml_import_errors_nonce'
			);

			echo '<p><a class="button" href="' . esc_url( $error_link ) . '">' . esc_html__( 'Download error report CSV', 'mahl-league' ) . '</a></p>';
		}
		echo '</div>';
	}

	/**
	 * Redirect to Match Editor page.
	 *
	 * @param int    $match_id Match ID.
	 * @param string $notice   Notice code.
	 *
	 * @return void
	 */
	private static function redirect_match_editor( int $match_id, string $notice = '' ): void {
		$args = array(
			'page' => 'ml-match-editor',
		);

		if ( $match_id > 0 ) {
			$args['match_id'] = $match_id;
		}

		if ( '' !== $notice ) {
			$args['ml_match_notice'] = sanitize_key( $notice );
		} else {
			$args['ml_updated'] = 1;
		}

		wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Write Match Editor debug log when WP_DEBUG is enabled.
	 *
	 * @param string $event   Event name.
	 * @param array  $context Extra context.
	 *
	 * @return void
	 */
	private static function log_match_editor_error( string $event, array $context = array() ): void {
		if ( ! defined( 'WP_DEBUG' ) || true !== WP_DEBUG ) {
			return;
		}

		$payload = wp_json_encode( $context );
		error_log( '[MAHL League][Match Editor] ' . $event . ' ' . ( false !== $payload ? $payload : '' ) );
	}

	/**
	 * Render generic admin notice from query args.
	 *
	 * @return void
	 */
	private static function render_admin_notice(): void {
		if ( ! isset( $_GET['ml_notice'] ) ) {
			return;
		}

		$notice_key  = sanitize_key( wp_unslash( $_GET['ml_notice'] ) );
		$notice_type = isset( $_GET['ml_notice_type'] ) ? sanitize_key( wp_unslash( $_GET['ml_notice_type'] ) ) : 'success';
		$messages    = array(
			'team_saved'             => __( 'Team has been saved.', 'mahl-league' ),
			'team_deleted'           => __( 'Team has been deleted.', 'mahl-league' ),
			'team_name_required'     => __( 'Team name is required.', 'mahl-league' ),
			'team_save_error'        => __( 'Unable to save team.', 'mahl-league' ),
			'player_saved'           => __( 'Player has been saved.', 'mahl-league' ),
			'player_required_fields' => __( 'Player name and team are required.', 'mahl-league' ),
			'player_invalid_team'    => __( 'Selected team is invalid.', 'mahl-league' ),
			'player_save_error'      => __( 'Unable to save player.', 'mahl-league' ),
		);

		if ( ! isset( $messages[ $notice_key ] ) ) {
			return;
		}

		$classes = 'notice notice-success';
		if ( 'error' === $notice_type ) {
			$classes = 'notice notice-error';
		}

		echo '<div class="' . esc_attr( $classes ) . '"><p>' . esc_html( $messages[ $notice_key ] ) . '</p></div>';
	}

	/**
	 * Redirect to an admin page.
	 *
	 * @param string $page_slug Page slug.
	 * @param array  $args      Query args.
	 *
	 * @return void
	 */
	private static function redirect_admin_page( string $page_slug, array $args = array() ): void {
		$redirect_url = add_query_arg( array_merge( array( 'page' => $page_slug ), $args ), admin_url( 'admin.php' ) );
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Build sample CSV export link.
	 *
	 * @param string $entity Entity type.
	 *
	 * @return string
	 */
	private static function build_sample_export_link( string $entity ): string {
		return wp_nonce_url(
			add_query_arg(
				array(
					'action' => 'ml_export_csv',
					'sample' => 1,
					'entity' => $entity,
				),
				admin_url( 'admin-post.php' )
			),
			'ml_export_sample_csv'
		);
	}

	/**
	 * Redirect to import/export page.
	 *
	 * @param array $args Query args.
	 *
	 * @return void
	 */
	private static function redirect_import_export( array $args ): void {
		$redirect_url = add_query_arg(
			array_merge(
				array(
					'page' => 'ml-import-export',
				),
				$args
			),
			admin_url( 'admin.php' )
		);

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Stream CSV response.
	 *
	 * @param string $filename Base file name.
	 * @param array  $rows     CSV rows.
	 *
	 * @return void
	 */
	private static function send_csv_response( string $filename, array $rows ): void {
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . sanitize_file_name( $filename ) . '.csv' );

		$output = fopen( 'php://output', 'w' );
		if ( false === $output ) {
			wp_die( esc_html__( 'Unable to generate CSV output.', 'mahl-league' ) );
		}

		foreach ( $rows as $row ) {
			fputcsv( $output, $row );
		}

		fclose( $output );
		exit;
	}

	/**
	 * Render placeholder screen.
	 *
	 * @param string $title   Title.
	 * @param string $message Message.
	 *
	 * @return void
	 */
	private static function render_placeholder_screen( string $title, string $message ): void {
		if ( ! current_user_can( 'manage_ml_league' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'mahl-league' ) );
		}

		echo '<div class="wrap">';
		echo '<h1>' . esc_html( $title ) . '</h1>';
		echo '<p>' . esc_html( $message ) . '</p>';
		echo '</div>';
	}

	/**
	 * Render generic input row.
	 *
	 * @param string $name  Input name.
	 * @param string $label Input label.
	 * @param string $value Input value.
	 * @param string $type  Input type.
	 *
	 * @return void
	 */
	private static function render_input_row( string $name, string $label, string $value, string $type = 'text' ): void {
		echo '<tr>';
		echo '<th scope="row"><label for="' . esc_attr( $name ) . '">' . esc_html( $label ) . '</label></th>';
		echo '<td><input name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '" type="' . esc_attr( $type ) . '" value="' . esc_attr( $value ) . '" class="regular-text" /></td>';
		echo '</tr>';
	}

	/**
	 * Render generic select row.
	 *
	 * @param string $name    Field name.
	 * @param string $label   Field label.
	 * @param array  $options Options.
	 * @param string $value   Selected value.
	 *
	 * @return void
	 */
	private static function render_select_row( string $name, string $label, array $options, string $value ): void {
		echo '<tr>';
		echo '<th scope="row"><label for="' . esc_attr( $name ) . '">' . esc_html( $label ) . '</label></th>';
		echo '<td><select name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '">';
		foreach ( $options as $option_value => $option_label ) {
			echo '<option value="' . esc_attr( (string) $option_value ) . '" ' . selected( $value, (string) $option_value, false ) . '>' . esc_html( (string) $option_label ) . '</option>';
		}
		echo '</select></td>';
		echo '</tr>';
	}

	/**
	 * Render team select row.
	 *
	 * @param string $name     Field name.
	 * @param string $label    Field label.
	 * @param array  $teams    Team posts.
	 * @param int    $selected Selected team ID.
	 *
	 * @return void
	 */
	private static function render_team_select_row( string $name, string $label, array $teams, int $selected ): void {
		echo '<tr>';
		echo '<th scope="row"><label for="' . esc_attr( $name ) . '">' . esc_html( $label ) . '</label></th>';
		echo '<td><select name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '">';
		echo '<option value="0">' . esc_html__( 'Select team', 'mahl-league' ) . '</option>';
		foreach ( $teams as $team ) {
			echo '<option value="' . esc_attr( (string) $team->ID ) . '" ' . selected( $selected, $team->ID, false ) . '>' . esc_html( $team->post_title ) . '</option>';
		}
		echo '</select></td>';
		echo '</tr>';
	}
}
