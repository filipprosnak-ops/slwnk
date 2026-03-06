<?php
/**
 * Player post type registration.
 *
 * @package MAHLManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Represent the player custom post type.
 */
class MAHL_Player_Post_Type extends MAHL_Base_Post_Type {

	/**
	 * Return the singular label.
	 *
	 * @return string
	 */
	protected function get_singular_label() {
		return __( 'Player', 'mahl-manager' );
	}

	/**
	 * Return the plural label.
	 *
	 * @return string
	 */
	protected function get_plural_label() {
		return __( 'Players', 'mahl-manager' );
	}

	/**
	 * Return the rewrite slug.
	 *
	 * @return string
	 */
	protected function get_rewrite_slug() {
		return 'players';
	}

	/**
	 * Return the supported editor features.
	 *
	 * @return array
	 */
	protected function get_supports() {
		return array( 'title', 'editor', 'thumbnail' );
	}

	/**
	 * Return the menu icon.
	 *
	 * @return string
	 */
	protected function get_menu_icon() {
		return 'dashicons-admin-users';
	}

	/**
	 * Return the player post type key.
	 *
	 * @return string
	 */
	public function get_post_type() {
		return 'mahl_player';
	}
}
