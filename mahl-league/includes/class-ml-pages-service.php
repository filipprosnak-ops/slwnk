<?php
/**
 * Managed pages service.
 *
 * @package MAHLLeague
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ML_Pages_Service
 */
class ML_Pages_Service {

	/**
	 * Managed pages option key.
	 */
	private const OPTION_KEY = 'ml_pages_map';

	/**
	 * Managed page definitions.
	 *
	 * @return array
	 */
	public static function get_required_pages(): array {
		return array(
			'timy'       => array( 'title' => 'Tímy', 'shortcode' => '[ml_teams]' ),
			'zapasy'     => array( 'title' => 'Zápasy', 'shortcode' => '[ml_matches view="fixtures"]' ),
			'vysledky'   => array( 'title' => 'Výsledky', 'shortcode' => '[ml_matches view="results"]' ),
			'tabulky'    => array( 'title' => 'Tabuľky', 'shortcode' => '[ml_standings]' ),
			'statistiky' => array( 'title' => 'Štatistiky', 'shortcode' => '[ml_stats]' ),
			'play-off'   => array( 'title' => 'Play-off', 'shortcode' => '[ml_playoffs]' ),
		);
	}

	/**
	 * Ensure required pages exist.
	 *
	 * @param bool $repair_managed_content Whether to repair managed content.
	 *
	 * @return array
	 */
	public static function ensure_pages( bool $repair_managed_content = false ): array {
		$existing_map = get_option( self::OPTION_KEY, array() );
		$map          = is_array( $existing_map ) ? $existing_map : array();
		$summary      = array( 'created' => 0, 'linked' => 0, 'repaired' => 0 );

		foreach ( self::get_required_pages() as $slug => $definition ) {
			$marker  = self::marker( $slug );
			$content = $marker . "\n" . $definition['shortcode'];
			$post    = get_page_by_path( $slug, OBJECT, 'page' );

			if ( ! $post instanceof WP_Post ) {
				$post_id = wp_insert_post(
					array(
						'post_type'    => 'page',
						'post_status'  => 'publish',
						'post_title'   => $definition['title'],
						'post_name'    => $slug,
						'post_content' => $content,
					),
					true
				);

				if ( ! is_wp_error( $post_id ) && $post_id > 0 ) {
					$map[ $slug ] = absint( $post_id );
					++$summary['created'];
				}
				continue;
			}

			$map[ $slug ] = absint( $post->ID );
			++$summary['linked'];

			$should_repair = $repair_managed_content && false !== strpos( (string) $post->post_content, $marker ) && trim( (string) $post->post_content ) !== trim( $content );
			if ( $should_repair ) {
				wp_update_post(
					array(
						'ID'           => $post->ID,
						'post_content' => $content,
					),
					true
				);
				++$summary['repaired'];
			}
		}

		update_option( self::OPTION_KEY, $map );

		return $summary;
	}

	/**
	 * Get map from option.
	 *
	 * @return array
	 */
	public static function get_pages_map(): array {
		$map = get_option( self::OPTION_KEY, array() );
		return is_array( $map ) ? $map : array();
	}

	/**
	 * Build managed marker.
	 *
	 * @param string $slug Page slug.
	 *
	 * @return string
	 */
	private static function marker( string $slug ): string {
		return '<!-- ml-managed-page:' . sanitize_key( $slug ) . ' -->';
	}
}
