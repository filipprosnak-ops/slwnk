<?php
/**
 * Player post type placeholder.
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
	 * Return the player post type key.
	 *
	 * @return string
	 */
	public function get_post_type() {
		return 'mahl_player';
	}
}
