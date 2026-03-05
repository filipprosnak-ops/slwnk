<?php
/**
 * Admin ID utilities.
 *
 * @package MAHLLeague
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ML_ID_Utils {

	public static function register(): void {
		add_filter( 'manage_ml_team_posts_columns', array( self::class, 'add_id_column' ) );
		add_filter( 'manage_ml_player_posts_columns', array( self::class, 'add_id_column' ) );
		add_filter( 'manage_ml_match_posts_columns', array( self::class, 'add_id_column' ) );

		add_action( 'manage_ml_team_posts_custom_column', array( self::class, 'render_id_column' ), 10, 2 );
		add_action( 'manage_ml_player_posts_custom_column', array( self::class, 'render_id_column' ), 10, 2 );
		add_action( 'manage_ml_match_posts_custom_column', array( self::class, 'render_id_column' ), 10, 2 );

		add_filter( 'manage_edit-ml_team_sortable_columns', array( self::class, 'sortable' ) );
		add_filter( 'manage_edit-ml_player_sortable_columns', array( self::class, 'sortable' ) );
		add_filter( 'manage_edit-ml_match_sortable_columns', array( self::class, 'sortable' ) );
		add_action( 'pre_get_posts', array( self::class, 'order_by_id' ) );
	}

	public static function add_id_column( array $columns ): array {
		$columns['ml_id'] = __( 'ID', 'mahl-league' );
		return $columns;
	}

	public static function render_id_column( string $column, int $post_id ): void {
		if ( 'ml_id' === $column ) {
			echo esc_html( (string) $post_id );
		}
	}

	public static function sortable( array $columns ): array {
		$columns['ml_id'] = 'ID';
		return $columns;
	}

	public static function order_by_id( WP_Query $query ): void {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( 'ID' === $query->get( 'orderby' ) ) {
			$query->set( 'orderby', 'ID' );
		}
	}
}
