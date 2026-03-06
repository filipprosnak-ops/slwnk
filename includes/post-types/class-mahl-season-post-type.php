<?php
/**
 * Season post type registration.
 *
 * @package MAHLManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Represent the season custom post type.
 */
class MAHL_Season_Post_Type extends MAHL_Base_Post_Type {

	/**
	 * Return the singular label.
	 *
	 * @return string
	 */
	protected function get_singular_label() {
		return __( 'Season', 'mahl-manager' );
	}

	/**
	 * Return the plural label.
	 *
	 * @return string
	 */
	protected function get_plural_label() {
		return __( 'Seasons', 'mahl-manager' );
	}

	/**
	 * Return the rewrite slug.
	 *
	 * @return string
	 */
	protected function get_rewrite_slug() {
		return 'seasons';
	}

	/**
	 * Return the menu icon.
	 *
	 * @return string
	 */
	protected function get_menu_icon() {
		return 'dashicons-calendar-alt';
	}

	/**
	 * Return the season post type key.
	 *
	 * @return string
	 */
	public function get_post_type() {
		return 'mahl_season';
	}
}
