<?php
/**
 * Base custom post type registration.
 *
 * @package MAHLManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shared base class for custom post type modules.
 */
abstract class MAHL_Base_Post_Type {

	/**
	 * Register WordPress hooks for the post type.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'init', array( $this, 'register_post_type' ) );
	}

	/**
	 * Register the custom post type.
	 *
	 * @return void
	 */
	public function register_post_type() {
		register_post_type( $this->get_post_type(), $this->get_args() );
	}

	/**
	 * Return the post type registration arguments.
	 *
	 * @return array
	 */
	protected function get_args() {
		return array(
			'labels'             => $this->get_labels(),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => true,
			'show_in_rest'       => true,
			'has_archive'        => $this->get_archive_slug(),
			'rewrite'            => array(
				'slug'       => $this->get_rewrite_slug(),
				'with_front' => false,
			),
			'query_var'          => true,
			'menu_icon'          => $this->get_menu_icon(),
			'supports'           => $this->get_supports(),
			'map_meta_cap'       => true,
		);
	}

	/**
	 * Build the labels array for the post type.
	 *
	 * @return array
	 */
	protected function get_labels() {
		$singular = $this->get_singular_label();
		$plural   = $this->get_plural_label();

		return array(
			'name'                  => $plural,
			'singular_name'         => $singular,
			'menu_name'             => $plural,
			'name_admin_bar'        => $singular,
			'add_new'               => __( 'Add New', 'mahl-manager' ),
			'add_new_item'          => sprintf(
				/* translators: %s: singular post type label. */
				__( 'Add New %s', 'mahl-manager' ),
				$singular
			),
			'edit_item'             => sprintf(
				/* translators: %s: singular post type label. */
				__( 'Edit %s', 'mahl-manager' ),
				$singular
			),
			'new_item'              => sprintf(
				/* translators: %s: singular post type label. */
				__( 'New %s', 'mahl-manager' ),
				$singular
			),
			'view_item'             => sprintf(
				/* translators: %s: singular post type label. */
				__( 'View %s', 'mahl-manager' ),
				$singular
			),
			'view_items'            => sprintf(
				/* translators: %s: plural post type label. */
				__( 'View %s', 'mahl-manager' ),
				$plural
			),
			'search_items'          => sprintf(
				/* translators: %s: plural post type label. */
				__( 'Search %s', 'mahl-manager' ),
				$plural
			),
			'not_found'             => sprintf(
				/* translators: %s: plural post type label. */
				__( 'No %s found.', 'mahl-manager' ),
				strtolower( $plural )
			),
			'not_found_in_trash'    => sprintf(
				/* translators: %s: plural post type label. */
				__( 'No %s found in Trash.', 'mahl-manager' ),
				strtolower( $plural )
			),
			'all_items'             => sprintf(
				/* translators: %s: plural post type label. */
				__( 'All %s', 'mahl-manager' ),
				$plural
			),
			'archives'              => sprintf(
				/* translators: %s: singular post type label. */
				__( '%s Archives', 'mahl-manager' ),
				$singular
			),
			'attributes'            => sprintf(
				/* translators: %s: singular post type label. */
				__( '%s Attributes', 'mahl-manager' ),
				$singular
			),
			'insert_into_item'      => sprintf(
				/* translators: %s: singular post type label. */
				__( 'Insert into %s', 'mahl-manager' ),
				strtolower( $singular )
			),
			'uploaded_to_this_item' => sprintf(
				/* translators: %s: singular post type label. */
				__( 'Uploaded to this %s', 'mahl-manager' ),
				strtolower( $singular )
			),
			'featured_image'        => __( 'Featured image', 'mahl-manager' ),
			'set_featured_image'    => __( 'Set featured image', 'mahl-manager' ),
			'remove_featured_image' => __( 'Remove featured image', 'mahl-manager' ),
			'use_featured_image'    => __( 'Use as featured image', 'mahl-manager' ),
		);
	}

	/**
	 * Return the supported editor features.
	 *
	 * @return array
	 */
	protected function get_supports() {
		return array( 'title', 'editor' );
	}

	/**
	 * Return the archive slug.
	 *
	 * @return string
	 */
	protected function get_archive_slug() {
		return $this->get_rewrite_slug();
	}

	/**
	 * Return the menu icon.
	 *
	 * @return string
	 */
	protected function get_menu_icon() {
		return 'dashicons-admin-post';
	}

	/**
	 * Return the singular label.
	 *
	 * @return string
	 */
	abstract protected function get_singular_label();

	/**
	 * Return the plural label.
	 *
	 * @return string
	 */
	abstract protected function get_plural_label();

	/**
	 * Return the rewrite slug.
	 *
	 * @return string
	 */
	abstract protected function get_rewrite_slug();

	/**
	 * Return the post type key handled by the class.
	 *
	 * @return string
	 */
	abstract public function get_post_type();
}
