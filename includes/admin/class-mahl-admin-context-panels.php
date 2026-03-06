<?php
/**
 * Admin context panels.
 *
 * @package MAHLManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render contextual helper panels on edit screens.
 */
class MAHL_Admin_Context_Panels {

	/**
	 * Register admin hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_filter( 'enter_title_here', array( $this, 'filter_title_placeholder' ), 10, 2 );
	}

	/**
	 * Register contextual meta boxes.
	 *
	 * @return void
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'mahl-phase-context',
			__( 'Competition Context', 'mahl-manager' ),
			array( $this, 'render_phase_context' ),
			'mahl_phase',
			'side',
			'high'
		);

		add_meta_box(
			'mahl-team-context',
			__( 'Team Management Context', 'mahl-manager' ),
			array( $this, 'render_team_context' ),
			'mahl_team',
			'side',
			'high'
		);

		add_meta_box(
			'mahl-player-context',
			__( 'Player Management Context', 'mahl-manager' ),
			array( $this, 'render_player_context' ),
			'mahl_player',
			'side',
			'high'
		);

		add_meta_box(
			'mahl-game-workflow',
			__( 'Match Workflow', 'mahl-manager' ),
			array( $this, 'render_game_context' ),
			'mahl_game',
			'side',
			'high'
		);
	}

	/**
	 * Filter post title placeholders for plugin post types.
	 *
	 * @param string  $placeholder Current placeholder text.
	 * @param WP_Post $post Current post object.
	 * @return string
	 */
	public function filter_title_placeholder( $placeholder, $post ) {
		$placeholders = array(
			'mahl_season' => __( 'Enter season title, for example MAHL 2026/2027', 'mahl-manager' ),
			'mahl_phase'  => __( 'Enter phase title, for example Nadstavba or Playoff', 'mahl-manager' ),
			'mahl_team'   => __( 'Enter team name', 'mahl-manager' ),
			'mahl_player' => __( 'Enter player name', 'mahl-manager' ),
			'mahl_game'   => __( 'Enter game title or fixture label', 'mahl-manager' ),
		);

		return isset( $placeholders[ $post->post_type ] ) ? $placeholders[ $post->post_type ] : $placeholder;
	}

	/**
	 * Render phase context.
	 *
	 * @param WP_Post $post Current phase post.
	 * @return void
	 */
	public function render_phase_context( $post ) {
		$season_id = absint( get_post_meta( $post->ID, '_mahl_season_id', true ) );
		$game_count = $this->count_games_for_phase( $post->ID );

		echo '<div class="mahl-admin-panel">';
		echo '<p class="mahl-admin-panel__intro">' . esc_html__( 'A phase belongs to one season and groups rounds and optional brackets such as Top, Bottom, Semifinal, or Final.', 'mahl-manager' ) . '</p>';
		echo '<dl class="mahl-admin-meta-list">';
		echo '<dt>' . esc_html__( 'Season', 'mahl-manager' ) . '</dt>';
		echo '<dd>' . esc_html( $season_id ? get_the_title( $season_id ) : __( 'Not assigned yet', 'mahl-manager' ) ) . '</dd>';
		echo '<dt>' . esc_html__( 'Games in this phase', 'mahl-manager' ) . '</dt>';
		echo '<dd>' . esc_html( $game_count ) . '</dd>';
		echo '</dl>';
		echo '<div class="mahl-admin-tip">' . esc_html__( 'Recommended workflow: create the season first, then add phases, then assign games to the correct phase and round.', 'mahl-manager' ) . '</div>';
		echo '</div>';
	}

	/**
	 * Render team context.
	 *
	 * @param WP_Post $post Current team post.
	 * @return void
	 */
	public function render_team_context( $post ) {
		$season_id    = absint( get_post_meta( $post->ID, '_mahl_season_id', true ) );
		$roster_count = $this->count_players_for_team( $post->ID );
		$game_count   = $this->count_games_for_team( $post->ID );

		echo '<div class="mahl-admin-panel">';
		echo '<p class="mahl-admin-panel__intro">' . esc_html__( 'Use the team title for the public team name and the content editor for notes or a simple profile. Assign the team to a season before linking players and games.', 'mahl-manager' ) . '</p>';
		echo '<dl class="mahl-admin-meta-list">';
		echo '<dt>' . esc_html__( 'Season', 'mahl-manager' ) . '</dt>';
		echo '<dd>' . esc_html( $season_id ? get_the_title( $season_id ) : __( 'Not assigned yet', 'mahl-manager' ) ) . '</dd>';
		echo '<dt>' . esc_html__( 'Roster size', 'mahl-manager' ) . '</dt>';
		echo '<dd>' . esc_html( $roster_count ) . '</dd>';
		echo '<dt>' . esc_html__( 'Linked games', 'mahl-manager' ) . '</dt>';
		echo '<dd>' . esc_html( $game_count ) . '</dd>';
		echo '</dl>';
		echo '</div>';
	}

	/**
	 * Render player context.
	 *
	 * @param WP_Post $post Current player post.
	 * @return void
	 */
	public function render_player_context( $post ) {
		$team_id   = absint( get_post_meta( $post->ID, '_mahl_team_id', true ) );
		$season_id = ! empty( $team_id ) ? absint( get_post_meta( $team_id, '_mahl_season_id', true ) ) : 0;

		echo '<div class="mahl-admin-panel">';
		echo '<p class="mahl-admin-panel__intro">' . esc_html__( 'Assign the player to the correct team first. The season context is derived from the selected team, which keeps roster and statistics relationships consistent.', 'mahl-manager' ) . '</p>';
		echo '<dl class="mahl-admin-meta-list">';
		echo '<dt>' . esc_html__( 'Team', 'mahl-manager' ) . '</dt>';
		echo '<dd>' . esc_html( $team_id ? get_the_title( $team_id ) : __( 'Not assigned yet', 'mahl-manager' ) ) . '</dd>';
		echo '<dt>' . esc_html__( 'Season Context', 'mahl-manager' ) . '</dt>';
		echo '<dd>' . esc_html( $season_id ? get_the_title( $season_id ) : __( 'Unavailable until team is assigned', 'mahl-manager' ) ) . '</dd>';
		echo '</dl>';
		echo '</div>';
	}

	/**
	 * Render game context.
	 *
	 * @param WP_Post $post Current game post.
	 * @return void
	 */
	public function render_game_context( $post ) {
		$season_id      = absint( get_post_meta( $post->ID, '_mahl_season_id', true ) );
		$phase_id       = absint( get_post_meta( $post->ID, '_mahl_phase_id', true ) );
		$round_number   = absint( get_post_meta( $post->ID, '_mahl_round_number', true ) );
		$group_key      = sanitize_key( get_post_meta( $post->ID, '_mahl_group_key', true ) );
		$home_team_id   = absint( get_post_meta( $post->ID, '_mahl_home_team_id', true ) );
		$away_team_id   = absint( get_post_meta( $post->ID, '_mahl_away_team_id', true ) );
		$status         = sanitize_key( get_post_meta( $post->ID, '_mahl_status', true ) );
		$appearance_set = get_post_meta( $post->ID, '_mahl_game_player_appearances', true );
		$event_set      = get_post_meta( $post->ID, '_mahl_game_events', true );

		echo '<div class="mahl-admin-panel">';
		echo '<p class="mahl-admin-panel__intro">' . esc_html__( 'Recommended workflow: choose season, phase, round, and teams first. Then enter match details, score, and game events. This helps player and standings data stay valid.', 'mahl-manager' ) . '</p>';
		echo '<dl class="mahl-admin-meta-list">';
		echo '<dt>' . esc_html__( 'Season', 'mahl-manager' ) . '</dt>';
		echo '<dd>' . esc_html( $season_id ? get_the_title( $season_id ) : __( 'Not assigned yet', 'mahl-manager' ) ) . '</dd>';
		echo '<dt>' . esc_html__( 'Phase', 'mahl-manager' ) . '</dt>';
		echo '<dd>' . esc_html( $phase_id ? get_the_title( $phase_id ) : __( 'Not assigned yet', 'mahl-manager' ) ) . '</dd>';
		echo '<dt>' . esc_html__( 'Round', 'mahl-manager' ) . '</dt>';
		echo '<dd>' . esc_html( $round_number ? $round_number : __( 'Not set', 'mahl-manager' ) ) . '</dd>';
		echo '<dt>' . esc_html__( 'Group / Bracket', 'mahl-manager' ) . '</dt>';
		echo '<dd>' . esc_html( $group_key ? ucwords( str_replace( array( '-', '_' ), ' ', $group_key ) ) : __( 'Not set', 'mahl-manager' ) ) . '</dd>';
		echo '<dt>' . esc_html__( 'Home Team', 'mahl-manager' ) . '</dt>';
		echo '<dd>' . esc_html( $home_team_id ? get_the_title( $home_team_id ) : __( 'Not assigned yet', 'mahl-manager' ) ) . '</dd>';
		echo '<dt>' . esc_html__( 'Away Team', 'mahl-manager' ) . '</dt>';
		echo '<dd>' . esc_html( $away_team_id ? get_the_title( $away_team_id ) : __( 'Not assigned yet', 'mahl-manager' ) ) . '</dd>';
		echo '<dt>' . esc_html__( 'Status', 'mahl-manager' ) . '</dt>';
		echo '<dd>' . esc_html( $status ? ucwords( str_replace( '_', ' ', $status ) ) : __( 'Not set', 'mahl-manager' ) ) . '</dd>';
		echo '<dt>' . esc_html__( 'Player Appearances', 'mahl-manager' ) . '</dt>';
		echo '<dd>' . esc_html( is_array( $appearance_set ) ? count( $appearance_set ) : 0 ) . '</dd>';
		echo '<dt>' . esc_html__( 'Game Events', 'mahl-manager' ) . '</dt>';
		echo '<dd>' . esc_html( is_array( $event_set ) ? count( $event_set ) : 0 ) . '</dd>';
		echo '</dl>';
		echo '</div>';
	}

	/**
	 * Count players assigned to a team.
	 *
	 * @param int $team_id Team post ID.
	 * @return int
	 */
	protected function count_players_for_team( $team_id ) {
		$players = get_posts(
			array(
				'post_type'      => 'mahl_player',
				'post_status'    => array( 'publish', 'future', 'draft', 'pending', 'private' ),
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_query'     => array(
					array(
						'key'     => '_mahl_team_id',
						'value'   => $team_id,
						'compare' => '=',
						'type'    => 'NUMERIC',
					),
				), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			)
		);

		return count( $players );
	}

	/**
	 * Count games linked to a team.
	 *
	 * @param int $team_id Team post ID.
	 * @return int
	 */
	protected function count_games_for_team( $team_id ) {
		$games = get_posts(
			array(
				'post_type'      => 'mahl_game',
				'post_status'    => array( 'publish', 'future', 'draft', 'pending', 'private' ),
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_query'     => array(
					'relation' => 'OR',
					array(
						'key'     => '_mahl_home_team_id',
						'value'   => $team_id,
						'compare' => '=',
						'type'    => 'NUMERIC',
					),
					array(
						'key'     => '_mahl_away_team_id',
						'value'   => $team_id,
						'compare' => '=',
						'type'    => 'NUMERIC',
					),
				), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			)
		);

		return count( $games );
	}

	/**
	 * Count games assigned to a phase.
	 *
	 * @param int $phase_id Phase post ID.
	 * @return int
	 */
	protected function count_games_for_phase( $phase_id ) {
		$games = get_posts(
			array(
				'post_type'      => 'mahl_game',
				'post_status'    => array( 'publish', 'future', 'draft', 'pending', 'private' ),
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_query'     => array(
					array(
						'key'     => '_mahl_phase_id',
						'value'   => $phase_id,
						'compare' => '=',
						'type'    => 'NUMERIC',
					),
				), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			)
		);

		return count( $games );
	}
}
