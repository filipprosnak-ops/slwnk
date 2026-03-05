<?php
/**
 * Frontend shortcodes.
 *
 * @package MAHLLeague
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ML_Shortcodes
 */
class ML_Shortcodes {

	/**
	 * Register shortcodes.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_shortcode( 'ml_teams', array( self::class, 'render_teams' ) );
		add_shortcode( 'ml_matches', array( self::class, 'render_matches' ) );
		add_shortcode( 'ml_standings', array( self::class, 'render_standings' ) );
		add_shortcode( 'ml_stats', array( self::class, 'render_stats' ) );
		add_shortcode( 'ml_playoffs', array( self::class, 'render_playoffs' ) );
	}

	public static function render_teams(): string {
		if ( ! self::is_ready() ) {
			return self::wrap_output( '<p>' . esc_html__( 'League module is temporarily unavailable.', 'mahl-league' ) . '</p>' );
		}

		$teams = get_posts(array('post_type'=>'ml_team','post_status'=>'publish','posts_per_page'=>-1,'orderby'=>'title','order'=>'ASC'));
		if ( empty( $teams ) ) {
			return self::wrap_output( '<p>' . esc_html__( 'No teams available.', 'mahl-league' ) . '</p>' );
		}
		$out = '<ul class="ml-teams-list">';
		foreach ( $teams as $team ) {
			$out .= '<li>' . esc_html( $team->post_title ) . '</li>';
		}
		$out .= '</ul>';
		return self::wrap_output( $out );
	}

	public static function render_matches( array $atts ): string {
		if ( ! self::is_ready() ) {
			return self::wrap_output( '<p>' . esc_html__( 'League module is temporarily unavailable.', 'mahl-league' ) . '</p>' );
		}

		$atts = shortcode_atts( array( 'view' => 'fixtures' ), $atts, 'ml_matches' );
		$view = sanitize_key( $atts['view'] );
		$args = array('post_type'=>'ml_match','post_status'=>'publish','posts_per_page'=>20,'meta_key'=>'ml_match_datetime','orderby'=>'meta_value','order'=>'ASC');
		if ( 'results' === $view ) {
			$args['meta_query'] = array(array('key'=>'ml_status','value'=>'played'));
			$args['order']      = 'DESC';
		}
		$matches = get_posts( $args );
		if ( empty( $matches ) ) {
			return self::wrap_output( '<p>' . esc_html__( 'No matches available.', 'mahl-league' ) . '</p>' );
		}
		$out = '<ul class="ml-matches-list">';
		foreach ( $matches as $match ) {
			$out .= '<li>' . esc_html( $match->post_title ) . '</li>';
		}
		$out .= '</ul>';
		return self::wrap_output( $out );
	}

	public static function render_standings(): string {
		if ( ! self::is_ready() ) {
			return self::wrap_output( '<p>' . esc_html__( 'League module is temporarily unavailable.', 'mahl-league' ) . '</p>' );
		}
		$context   = ML_Frontend_Router::get_selected_context();
		$standings = ML_Cache::get_standings( (int) $context['season_id'], (int) $context['competition_id'] );
		if ( empty( $standings ) ) {
			return self::wrap_output( '<p>' . esc_html__( 'Standings are not available for selected context.', 'mahl-league' ) . '</p>' );
		}
		$out = '<ol class="ml-standings-list">';
		foreach ( $standings as $row ) {
			$name = isset( $row['team_name'] ) ? (string) $row['team_name'] : __( 'Team', 'mahl-league' );
			$out .= '<li>' . esc_html( $name ) . '</li>';
		}
		$out .= '</ol>';
		return self::wrap_output( $out );
	}

	public static function render_stats(): string {
		if ( ! self::is_ready() ) {
			return self::wrap_output( '<p>' . esc_html__( 'League module is temporarily unavailable.', 'mahl-league' ) . '</p>' );
		}
		$context = ML_Frontend_Router::get_selected_context();
		$stats   = ML_Cache::get_stats( (int) $context['season_id'], (int) $context['competition_id'] );
		if ( empty( $stats ) ) {
			return self::wrap_output( '<p>' . esc_html__( 'Statistics are not available for selected context.', 'mahl-league' ) . '</p>' );
		}
		$team_count   = isset( $stats['team_totals'] ) && is_array( $stats['team_totals'] ) ? count( $stats['team_totals'] ) : 0;
		$player_count = isset( $stats['player_totals'] ) && is_array( $stats['player_totals'] ) ? count( $stats['player_totals'] ) : 0;
		return self::wrap_output( '<p>' . esc_html( sprintf( __( 'Cached stats loaded: %1$d teams, %2$d players.', 'mahl-league' ), $team_count, $player_count ) ) . '</p>' );
	}

	public static function render_playoffs(): string {
		if ( ! self::is_ready() ) {
			return self::wrap_output( '<p>' . esc_html__( 'League module is temporarily unavailable.', 'mahl-league' ) . '</p>' );
		}
		$matches = get_posts(array('post_type'=>'ml_match','post_status'=>'publish','posts_per_page'=>-1,'tax_query'=>array(array('taxonomy'=>'ml_phase','operator'=>'EXISTS'))));
		if ( empty( $matches ) ) {
			return self::wrap_output( '<p>' . esc_html__( 'No playoff matches available.', 'mahl-league' ) . '</p>' );
		}
		$out = '<ul class="ml-playoffs-list">';
		foreach ( $matches as $match ) {
			$out .= '<li>' . esc_html( $match->post_title ) . '</li>';
		}
		$out .= '</ul>';
		return self::wrap_output( $out );
	}


	/**
	 * Wrap shortcode output in scoped container.
	 *
	 * @param string $content Content HTML.
	 *
	 * @return string
	 */
	private static function wrap_output( string $content ): string {
		return '<div class="ml-league ml-theme-mahl">' . $content . '</div>';
	}

	private static function is_ready(): bool {
		return defined( 'ML_PLUGIN_READY' ) && true === ML_PLUGIN_READY;
	}
}
