<?php
/**
 * Register custom post types.
 *
 * @package MAHLLeague
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ML_Post_Types
 */
class ML_Post_Types {

	/**
	 * Register all plugin custom post types.
	 *
	 * @return void
	 */
	public static function register(): void {
		self::register_teams();
		self::register_players();
		self::register_matches();
	}

	/**
	 * Register Teams post type.
	 *
	 * @return void
	 */
	private static function register_teams(): void {
		$labels = array(
			'name'          => __( 'Teams', 'mahl-league' ),
			'singular_name' => __( 'Team', 'mahl-league' ),
			'add_new_item'  => __( 'Add New Team', 'mahl-league' ),
			'edit_item'     => __( 'Edit Team', 'mahl-league' ),
			'new_item'      => __( 'New Team', 'mahl-league' ),
			'view_item'     => __( 'View Team', 'mahl-league' ),
			'search_items'  => __( 'Search Teams', 'mahl-league' ),
			'not_found'     => __( 'No teams found.', 'mahl-league' ),
		);

		register_post_type(
			'ml_team',
			array(
				'labels'          => $labels,
				'public'          => true,
				'show_in_rest'    => true,
				'has_archive'     => true,
				'rewrite'         => array( 'slug' => 'teams' ),
				'menu_icon'       => 'dashicons-groups',
				'supports'        => array( 'title', 'editor', 'thumbnail' ),
				'capability_type' => 'ml_team',
				'map_meta_cap'    => true,
			)
		);
	}

	/**
	 * Register Players post type.
	 *
	 * @return void
	 */
	private static function register_players(): void {
		$labels = array(
			'name'          => __( 'Players', 'mahl-league' ),
			'singular_name' => __( 'Player', 'mahl-league' ),
			'add_new_item'  => __( 'Add New Player', 'mahl-league' ),
			'edit_item'     => __( 'Edit Player', 'mahl-league' ),
			'new_item'      => __( 'New Player', 'mahl-league' ),
			'view_item'     => __( 'View Player', 'mahl-league' ),
			'search_items'  => __( 'Search Players', 'mahl-league' ),
			'not_found'     => __( 'No players found.', 'mahl-league' ),
		);

		register_post_type(
			'ml_player',
			array(
				'labels'          => $labels,
				'public'          => true,
				'show_in_rest'    => true,
				'has_archive'     => true,
				'rewrite'         => array( 'slug' => 'players' ),
				'menu_icon'       => 'dashicons-id-alt',
				'supports'        => array( 'title', 'editor', 'thumbnail' ),
				'capability_type' => 'ml_player',
				'map_meta_cap'    => true,
			)
		);
	}

	/**
	 * Register Matches post type.
	 *
	 * @return void
	 */
	private static function register_matches(): void {
		$labels = array(
			'name'          => __( 'Matches', 'mahl-league' ),
			'singular_name' => __( 'Match', 'mahl-league' ),
			'add_new_item'  => __( 'Add New Match', 'mahl-league' ),
			'edit_item'     => __( 'Edit Match', 'mahl-league' ),
			'new_item'      => __( 'New Match', 'mahl-league' ),
			'view_item'     => __( 'View Match', 'mahl-league' ),
			'search_items'  => __( 'Search Matches', 'mahl-league' ),
			'not_found'     => __( 'No matches found.', 'mahl-league' ),
		);

		register_post_type(
			'ml_match',
			array(
				'labels'          => $labels,
				'public'          => true,
				'show_in_rest'    => true,
				'has_archive'     => true,
				'rewrite'         => array( 'slug' => 'matches' ),
				'menu_icon'       => 'dashicons-calendar-alt',
				'supports'        => array( 'title', 'editor' ),
				'capability_type' => 'ml_match',
				'map_meta_cap'    => true,
			)
		);
	}
}
