<?php
/**
 * Game post type registration.
 *
 * @package MAHLManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Represent the game custom post type.
 */
class MAHL_Game_Post_Type extends MAHL_Base_Post_Type {

	/**
	 * Return the singular label.
	 *
	 * @return string
	 */
	protected function get_singular_label() {
		return __( 'Game', 'mahl-manager' );
	}

	/**
	 * Return the plural label.
	 *
	 * @return string
	 */
	protected function get_plural_label() {
		return __( 'Games', 'mahl-manager' );
	}

	/**
	 * Return the rewrite slug.
	 *
	 * @return string
	 */
	protected function get_rewrite_slug() {
		return 'games';
	}

	/**
	 * Return the menu icon.
	 *
	 * @return string
	 */
	protected function get_menu_icon() {
		return 'dashicons-calendar';
	}

	/**
	 * Return the game post type key.
	 *
	 * @return string
	 */
	public function get_post_type() {
		return 'mahl_game';
	}
}
