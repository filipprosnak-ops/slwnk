<?php
/**
 * Team post type registration.
 *
 * @package MAHLManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Represent the team custom post type.
 */
class MAHL_Team_Post_Type extends MAHL_Base_Post_Type {

	/**
	 * Return the singular label.
	 *
	 * @return string
	 */
	protected function get_singular_label() {
		return __( 'Team', 'mahl-manager' );
	}

	/**
	 * Return the plural label.
	 *
	 * @return string
	 */
	protected function get_plural_label() {
		return __( 'Teams', 'mahl-manager' );
	}

	/**
	 * Return the rewrite slug.
	 *
	 * @return string
	 */
	protected function get_rewrite_slug() {
		return 'teams';
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
		return 'dashicons-groups';
	}

	/**
	 * Return the team post type key.
	 *
	 * @return string
	 */
	public function get_post_type() {
		return 'mahl_team';
	}
}
