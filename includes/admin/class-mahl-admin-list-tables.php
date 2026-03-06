<?php
/**
 * Admin list table improvements.
 *
 * @package MAHLManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Improve plugin admin list screens with columns and filters.
 */
class MAHL_Admin_List_Tables {

	/**
	 * Register admin hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_filter( 'manage_mahl_phase_posts_columns', array( $this, 'get_phase_columns' ) );
		add_action( 'manage_mahl_phase_posts_custom_column', array( $this, 'render_phase_column' ), 10, 2 );

		add_filter( 'manage_mahl_team_posts_columns', array( $this, 'get_team_columns' ) );
		add_action( 'manage_mahl_team_posts_custom_column', array( $this, 'render_team_column' ), 10, 2 );

		add_filter( 'manage_mahl_player_posts_columns', array( $this, 'get_player_columns' ) );
		add_action( 'manage_mahl_player_posts_custom_column', array( $this, 'render_player_column' ), 10, 2 );

		add_filter( 'manage_mahl_game_posts_columns', array( $this, 'get_game_columns' ) );
		add_action( 'manage_mahl_game_posts_custom_column', array( $this, 'render_game_column' ), 10, 2 );

		add_action( 'restrict_manage_posts', array( $this, 'render_filters' ) );
		add_action( 'pre_get_posts', array( $this, 'apply_filters_to_query' ) );
	}

	/**
	 * Return phase columns.
	 *
	 * @param array $columns Default columns.
	 * @return array
	 */
	public function get_phase_columns( $columns ) {
		return array(
			'cb'              => $columns['cb'],
			'title'           => __( 'Phase', 'mahl-manager' ),
			'mahl_season'     => __( 'Season', 'mahl-manager' ),
			'mahl_games'      => __( 'Games', 'mahl-manager' ),
			'date'            => $columns['date'],
		);
	}

	/**
	 * Render phase columns.
	 *
	 * @param string $column_name Column key.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function render_phase_column( $column_name, $post_id ) {
		if ( 'mahl_season' === $column_name ) {
			$season_id = absint( get_post_meta( $post_id, '_mahl_season_id', true ) );
			echo esc_html( $season_id ? get_the_title( $season_id ) : __( 'Not assigned', 'mahl-manager' ) );
		}

		if ( 'mahl_games' === $column_name ) {
			echo esc_html( $this->count_games_by_phase( $post_id ) );
		}
	}

	/**
	 * Return team columns.
	 *
	 * @param array $columns Default columns.
	 * @return array
	 */
	public function get_team_columns( $columns ) {
		return array(
			'cb'              => $columns['cb'],
			'title'           => __( 'Team', 'mahl-manager' ),
			'mahl_season'     => __( 'Season', 'mahl-manager' ),
			'mahl_roster'     => __( 'Roster', 'mahl-manager' ),
			'mahl_games'      => __( 'Games', 'mahl-manager' ),
			'date'            => $columns['date'],
		);
	}

	/**
	 * Render team columns.
	 *
	 * @param string $column_name Column key.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function render_team_column( $column_name, $post_id ) {
		if ( 'mahl_season' === $column_name ) {
			$season_id = absint( get_post_meta( $post_id, '_mahl_season_id', true ) );
			echo esc_html( $season_id ? get_the_title( $season_id ) : __( 'Not assigned', 'mahl-manager' ) );
		}

		if ( 'mahl_roster' === $column_name ) {
			echo esc_html( $this->count_players_by_team( $post_id ) );
		}

		if ( 'mahl_games' === $column_name ) {
			echo esc_html( $this->count_games_by_team( $post_id ) );
		}
	}

	/**
	 * Return player columns.
	 *
	 * @param array $columns Default columns.
	 * @return array
	 */
	public function get_player_columns( $columns ) {
		return array(
			'cb'              => $columns['cb'],
			'title'           => __( 'Player', 'mahl-manager' ),
			'mahl_team'       => __( 'Team', 'mahl-manager' ),
			'mahl_season'     => __( 'Season', 'mahl-manager' ),
			'date'            => $columns['date'],
		);
	}

	/**
	 * Render player columns.
	 *
	 * @param string $column_name Column key.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function render_player_column( $column_name, $post_id ) {
		$team_id   = absint( get_post_meta( $post_id, '_mahl_team_id', true ) );
		$season_id = ! empty( $team_id ) ? absint( get_post_meta( $team_id, '_mahl_season_id', true ) ) : 0;

		if ( 'mahl_team' === $column_name ) {
			echo esc_html( $team_id ? get_the_title( $team_id ) : __( 'Not assigned', 'mahl-manager' ) );
		}

		if ( 'mahl_season' === $column_name ) {
			echo esc_html( $season_id ? get_the_title( $season_id ) : __( 'Unavailable', 'mahl-manager' ) );
		}
	}

	/**
	 * Return game columns.
	 *
	 * @param array $columns Default columns.
	 * @return array
	 */
	public function get_game_columns( $columns ) {
		return array(
			'cb'              => $columns['cb'],
			'title'           => __( 'Game', 'mahl-manager' ),
			'mahl_matchup'    => __( 'Matchup', 'mahl-manager' ),
			'mahl_season'     => __( 'Season', 'mahl-manager' ),
			'mahl_phase'      => __( 'Phase', 'mahl-manager' ),
			'mahl_round'      => __( 'Round', 'mahl-manager' ),
			'mahl_group'      => __( 'Group', 'mahl-manager' ),
			'mahl_match_date' => __( 'Match Date', 'mahl-manager' ),
			'mahl_status'     => __( 'Status', 'mahl-manager' ),
			'mahl_score'      => __( 'Score', 'mahl-manager' ),
			'date'            => $columns['date'],
		);
	}

	/**
	 * Render game columns.
	 *
	 * @param string $column_name Column key.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function render_game_column( $column_name, $post_id ) {
		$season_id    = absint( get_post_meta( $post_id, '_mahl_season_id', true ) );
		$phase_id     = absint( get_post_meta( $post_id, '_mahl_phase_id', true ) );
		$home_team_id = absint( get_post_meta( $post_id, '_mahl_home_team_id', true ) );
		$away_team_id = absint( get_post_meta( $post_id, '_mahl_away_team_id', true ) );

		if ( 'mahl_matchup' === $column_name ) {
			printf(
				'%1$s vs %2$s',
				esc_html( $home_team_id ? get_the_title( $home_team_id ) : __( 'Home TBD', 'mahl-manager' ) ),
				esc_html( $away_team_id ? get_the_title( $away_team_id ) : __( 'Away TBD', 'mahl-manager' ) )
			);
		}

		if ( 'mahl_season' === $column_name ) {
			echo esc_html( $season_id ? get_the_title( $season_id ) : __( 'Not assigned', 'mahl-manager' ) );
		}

		if ( 'mahl_phase' === $column_name ) {
			echo esc_html( $phase_id ? get_the_title( $phase_id ) : __( 'Not assigned', 'mahl-manager' ) );
		}

		if ( 'mahl_round' === $column_name ) {
			$round_number = absint( get_post_meta( $post_id, '_mahl_round_number', true ) );
			echo esc_html( $round_number ? $round_number : '—' );
		}

		if ( 'mahl_group' === $column_name ) {
			$group_key = sanitize_key( get_post_meta( $post_id, '_mahl_group_key', true ) );
			echo esc_html( $group_key ? ucwords( str_replace( array( '-', '_' ), ' ', $group_key ) ) : '—' );
		}

		if ( 'mahl_match_date' === $column_name ) {
			$match_date = sanitize_text_field( get_post_meta( $post_id, '_mahl_match_date', true ) );
			echo esc_html( $match_date ? $match_date : '—' );
		}

		if ( 'mahl_status' === $column_name ) {
			$status = sanitize_key( get_post_meta( $post_id, '_mahl_status', true ) );
			echo esc_html( $status ? ucwords( str_replace( '_', ' ', $status ) ) : '—' );
		}

		if ( 'mahl_score' === $column_name ) {
			$home_score = get_post_meta( $post_id, '_mahl_score_home_final', true );
			$away_score = get_post_meta( $post_id, '_mahl_score_away_final', true );
			echo esc_html( '' !== $home_score && '' !== $away_score ? $home_score . ' : ' . $away_score : '—' );
		}
	}

	/**
	 * Render list screen filters.
	 *
	 * @return void
	 */
	public function render_filters() {
		global $typenow;

		if ( in_array( $typenow, array( 'mahl_phase', 'mahl_team', 'mahl_game' ), true ) ) {
			$this->render_related_post_filter(
				'mahl_season_filter',
				'mahl_season',
				__( 'All seasons', 'mahl-manager' )
			);
		}

		if ( 'mahl_game' === $typenow ) {
			$this->render_related_post_filter(
				'mahl_phase_filter',
				'mahl_phase',
				__( 'All phases', 'mahl-manager' )
			);
		}

		if ( 'mahl_player' === $typenow ) {
			$this->render_related_post_filter(
				'mahl_team_filter',
				'mahl_team',
				__( 'All teams', 'mahl-manager' )
			);
		}
	}

	/**
	 * Apply admin filters to the current query.
	 *
	 * @param WP_Query $query Current query.
	 * @return void
	 */
	public function apply_filters_to_query( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		$post_type = $query->get( 'post_type' );

		if ( ! in_array( $post_type, array( 'mahl_phase', 'mahl_team', 'mahl_player', 'mahl_game' ), true ) ) {
			return;
		}

		$meta_query = (array) $query->get( 'meta_query' );

		$this->append_numeric_meta_filter( $meta_query, 'mahl_season_filter', '_mahl_season_id' );
		$this->append_numeric_meta_filter( $meta_query, 'mahl_phase_filter', '_mahl_phase_id' );
		$this->append_numeric_meta_filter( $meta_query, 'mahl_team_filter', '_mahl_team_id' );

		if ( ! empty( $meta_query ) ) {
			$query->set( 'meta_query', $meta_query );
		}
	}

	/**
	 * Render a related post filter dropdown.
	 *
	 * @param string $request_key Request key.
	 * @param string $post_type Related post type.
	 * @param string $default_label Default option label.
	 * @return void
	 */
	protected function render_related_post_filter( $request_key, $post_type, $default_label ) {
		$current_value = isset( $_GET[ $request_key ] ) ? absint( wp_unslash( $_GET[ $request_key ] ) ) : 0;
		$options       = get_posts(
			array(
				'post_type'      => $post_type,
				'post_status'    => array( 'publish', 'future', 'draft', 'pending', 'private' ),
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'no_found_rows'  => true,
			)
		);

		printf(
			'<select name="%1$s"><option value="">%2$s</option>',
			esc_attr( $request_key ),
			esc_html( $default_label )
		);

		foreach ( $options as $option ) {
			printf(
				'<option value="%1$d" %2$s>%3$s</option>',
				absint( $option->ID ),
				selected( $current_value, $option->ID, false ),
				esc_html( get_the_title( $option ) )
			);
		}

		echo '</select>';
	}

	/**
	 * Append a numeric meta filter to a query.
	 *
	 * @param array  $meta_query Meta query array.
	 * @param string $request_key Request key.
	 * @param string $meta_key Meta key.
	 * @return void
	 */
	protected function append_numeric_meta_filter( &$meta_query, $request_key, $meta_key ) {
		if ( empty( $_GET[ $request_key ] ) ) {
			return;
		}

		$value = absint( wp_unslash( $_GET[ $request_key ] ) );

		if ( empty( $value ) ) {
			return;
		}

		$meta_query[] = array(
			'key'     => $meta_key,
			'value'   => $value,
			'compare' => '=',
			'type'    => 'NUMERIC',
		);
	}

	/**
	 * Count games assigned to a phase.
	 *
	 * @param int $phase_id Phase post ID.
	 * @return int
	 */
	protected function count_games_by_phase( $phase_id ) {
		return count(
			get_posts(
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
			)
		);
	}

	/**
	 * Count players assigned to a team.
	 *
	 * @param int $team_id Team post ID.
	 * @return int
	 */
	protected function count_players_by_team( $team_id ) {
		return count(
			get_posts(
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
			)
		);
	}

	/**
	 * Count games linked to a team.
	 *
	 * @param int $team_id Team post ID.
	 * @return int
	 */
	protected function count_games_by_team( $team_id ) {
		return count(
			get_posts(
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
			)
		);
	}
}
