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

		$teams = ML_Cache::get_teams_view();
		if ( empty( $teams ) ) {
			return self::wrap_output( '<p>' . esc_html__( 'Data not ready. Please recompute in admin.', 'mahl-league' ) . '</p>' );
		}

		$out = '<ul class="ml-teams-list">';
		foreach ( $teams as $team ) {
			$title = isset( $team['title'] ) ? (string) $team['title'] : '';
			$url   = isset( $team['url'] ) ? (string) $team['url'] : '';
			$out  .= '<li><a href="' . esc_url( $url ) . '">' . esc_html( $title ) . '</a></li>';
		}
		$out .= '</ul>';

		return self::wrap_output( $out );
	}

	public static function render_matches( array $atts ): string {
		if ( ! self::is_ready() ) {
			return self::wrap_output( '<p>' . esc_html__( 'League module is temporarily unavailable.', 'mahl-league' ) . '</p>' );
		}

		$atts    = shortcode_atts( array( 'view' => 'fixtures' ), $atts, 'ml_matches' );
		$view    = sanitize_key( $atts['view'] );
		$context = ML_Frontend_Router::get_selected_context();
		$items   = 'results' === $view
			? ML_Cache::get_results( (int) $context['season_id'], (int) $context['competition_id'] )
			: ML_Cache::get_fixtures( (int) $context['season_id'], (int) $context['competition_id'] );

		if ( empty( $items ) ) {
			return self::wrap_output( '<p>' . esc_html__( 'Data not ready. Please recompute in admin.', 'mahl-league' ) . '</p>' );
		}

		$out = '<ul class="ml-matches-list">';
		foreach ( $items as $item ) {
			$home = isset( $item['home_name'] ) ? (string) $item['home_name'] : '';
			$away = isset( $item['away_name'] ) ? (string) $item['away_name'] : '';
			$url  = isset( $item['url'] ) ? (string) $item['url'] : '';
			$out .= '<li><a href="' . esc_url( $url ) . '">' . esc_html( $home . ' vs ' . $away ) . '</a></li>';
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

		$context = ML_Frontend_Router::get_selected_context();
		$items   = ML_Cache::get_playoffs( (int) $context['season_id'], (int) $context['competition_id'] );
		if ( empty( $items ) ) {
			return self::wrap_output( '<p>' . esc_html__( 'Data not ready. Please recompute in admin.', 'mahl-league' ) . '</p>' );
		}
		$out = '<ul class="ml-playoffs-list">';
		foreach ( $items as $item ) {
			$home = isset( $item['home_name'] ) ? (string) $item['home_name'] : '';
			$away = isset( $item['away_name'] ) ? (string) $item['away_name'] : '';
			$url  = isset( $item['url'] ) ? (string) $item['url'] : '';
			$out .= '<li><a href="' . esc_url( $url ) . '">' . esc_html( $home . ' vs ' . $away ) . '</a></li>';
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
