<?php
/**
 * Base custom post type placeholder.
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
		// Placeholder for post type hook registration.
	}

	/**
	 * Return the post type key handled by the class.
	 *
	 * @return string
	 */
	abstract public function get_post_type();
}
