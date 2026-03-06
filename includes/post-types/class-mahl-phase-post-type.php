<?php
/**
 * Phase post type registration.
 *
 * @package MAHLManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Represent the phase custom post type.
 */
class MAHL_Phase_Post_Type extends MAHL_Base_Post_Type {

	/**
	 * Return the singular label.
	 *
	 * @return string
	 */
	protected function get_singular_label() {
		return __( 'Phase', 'mahl-manager' );
	}

	/**
	 * Return the plural label.
	 *
	 * @return string
	 */
	protected function get_plural_label() {
		return __( 'Phases', 'mahl-manager' );
	}

	/**
	 * Return the rewrite slug.
	 *
	 * @return string
	 */
	protected function get_rewrite_slug() {
		return 'phases';
	}

	/**
	 * Return the menu icon.
	 *
	 * @return string
	 */
	protected function get_menu_icon() {
		return 'dashicons-networking';
	}

	/**
	 * Return the phase post type key.
	 *
	 * @return string
	 */
	public function get_post_type() {
		return 'mahl_phase';
	}
}
