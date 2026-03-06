<?php
/**
 * Game post type placeholder.
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
	 * Return the game post type key.
	 *
	 * @return string
	 */
	public function get_post_type() {
		return 'mahl_game';
	}
}
