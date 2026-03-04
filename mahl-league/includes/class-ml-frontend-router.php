<?php
/**
 * Frontend template router for MAHL League pages.
 *
 * @package MAHLLeague
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ML_Frontend_Router
 */
class ML_Frontend_Router {

	/**
	 * Register frontend hooks.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_filter( 'template_include', array( self::class, 'resolve_template' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue_assets' ) );
	}


	/**
	 * Enqueue frontend assets for league pages.
	 *
	 * @return void
	 */
	public static function enqueue_assets(): void {
		if ( ! self::is_league_view() ) {
			return;
		}

		wp_enqueue_style(
			'mahl-league-frontend',
			plugins_url( 'assets/css/league.css', dirname( __DIR__ ) . '/mahl-league.php' ),
			array(),
			'0.1.0'
		);
	}

	/**
	 * Resolve plugin templates for league routes.
	 *
	 * @param string $template Template path.
	 *
	 * @return string
	 */
	public static function resolve_template( string $template ): string {
		if ( is_post_type_archive( 'ml_team' ) ) {
			return self::template_path( 'archive-ml_team.php', $template );
		}

		if ( is_post_type_archive( 'ml_match' ) ) {
			return self::template_path( 'archive-ml_match.php', $template );
		}

		if ( is_page( 'results' ) ) {
			return self::template_path( 'page-results.php', $template );
		}

		if ( is_page( 'standings' ) ) {
			return self::template_path( 'page-standings.php', $template );
		}

		if ( is_page( 'stats' ) ) {
			return self::template_path( 'page-stats.php', $template );
		}

		if ( is_page( 'playoffs' ) ) {
			return self::template_path( 'page-playoffs.php', $template );
		}

		return $template;
	}


	/**
	 * Determine whether current request is a league frontend view.
	 *
	 * @return bool
	 */
	private static function is_league_view(): bool {
		return is_post_type_archive( 'ml_team' )
			|| is_post_type_archive( 'ml_match' )
			|| is_page( 'results' )
			|| is_page( 'standings' )
			|| is_page( 'stats' )
			|| is_page( 'playoffs' );
	}

	/**
	 * Build plugin template path when file exists.
	 *
	 * @param string $file            File name in templates directory.
	 * @param string $default_template Current template path.
	 *
	 * @return string
	 */
	private static function template_path( string $file, string $default_template ): string {
		$path = trailingslashit( dirname( __DIR__ ) ) . 'templates/' . $file;

		if ( file_exists( $path ) ) {
			return $path;
		}

		return $default_template;
	}

	/**
	 * Get selected season and competition IDs from URL.
	 *
	 * @return array
	 */
	public static function get_selected_context(): array {
		$season_id      = isset( $_GET['season_id'] ) ? absint( wp_unslash( $_GET['season_id'] ) ) : 0;
		$competition_id = isset( $_GET['competition_id'] ) ? absint( wp_unslash( $_GET['competition_id'] ) ) : 0;

		if ( $season_id <= 0 ) {
			$season = get_terms(
				array(
					'taxonomy'   => 'ml_season',
					'hide_empty' => false,
					'number'     => 1,
				)
			);
			if ( ! is_wp_error( $season ) && is_array( $season ) && ! empty( $season ) ) {
				$season_id = absint( $season[0]->term_id );
			}
		}

		if ( $competition_id <= 0 ) {
			$competition = get_terms(
				array(
					'taxonomy'   => 'ml_competition',
					'hide_empty' => false,
					'number'     => 1,
				)
			);
			if ( ! is_wp_error( $competition ) && is_array( $competition ) && ! empty( $competition ) ) {
				$competition_id = absint( $competition[0]->term_id );
			}
		}

		return array(
			'season_id'      => $season_id,
			'competition_id' => $competition_id,
		);
	}
}
