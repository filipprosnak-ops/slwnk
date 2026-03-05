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
				'show_in_menu'    => false,
				'supports'        => array( 'title', 'thumbnail' ),
				'capability_type' => array( 'ml_team', 'ml_teams' ),
				'capabilities'    => self::get_capabilities( 'team', 'teams' ),
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
				'show_in_menu'    => false,
				'supports'        => array( 'title', 'thumbnail' ),
				'capability_type' => array( 'ml_player', 'ml_players' ),
				'capabilities'    => self::get_capabilities( 'player', 'players' ),
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
				'show_in_menu'    => false,
				'supports'        => array( 'title' ),
				'capability_type' => array( 'ml_match', 'ml_matches' ),
				'capabilities'    => self::get_capabilities( 'match', 'matches' ),
				'map_meta_cap'    => true,
			)
		);
	}

	/**
	 * Disable block editor for MAHL data post types.
	 *
	 * @param bool   $use_block_editor Current block editor usage flag.
	 * @param string $post_type        Post type name.
	 *
	 * @return bool
	 */
	public static function disable_block_editor( bool $use_block_editor, string $post_type ): bool {
		if ( in_array( $post_type, array( 'ml_team', 'ml_player', 'ml_match' ), true ) ) {
			return false;
		}

		return $use_block_editor;
	}

	/**
	 * Build explicit capability map for ML post types.
	 *
	 * @param string $singular Singular key segment.
	 * @param string $plural   Plural key segment.
	 *
	 * @return array
	 */
	private static function get_capabilities( string $singular, string $plural ): array {
		$prefix = 'ml_';

		return array(
			'edit_post'              => 'edit_' . $prefix . $singular,
			'read_post'              => 'read_' . $prefix . $singular,
			'delete_post'            => 'delete_' . $prefix . $singular,
			'edit_posts'             => 'edit_' . $prefix . $plural,
			'edit_others_posts'      => 'edit_others_' . $prefix . $plural,
			'publish_posts'          => 'publish_' . $prefix . $plural,
			'read_private_posts'     => 'read_private_' . $prefix . $plural,
			'delete_posts'           => 'delete_' . $prefix . $plural,
			'delete_private_posts'   => 'delete_private_' . $prefix . $plural,
			'delete_published_posts' => 'delete_published_' . $prefix . $plural,
			'delete_others_posts'    => 'delete_others_' . $prefix . $plural,
			'edit_private_posts'     => 'edit_private_' . $prefix . $plural,
			'edit_published_posts'   => 'edit_published_' . $prefix . $plural,
			'create_posts'           => 'edit_' . $prefix . $plural,
		);
	}
}
