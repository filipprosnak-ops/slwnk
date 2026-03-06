<?php
/**
 * Team post type placeholder.
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
	 * Return the team post type key.
	 *
	 * @return string
	 */
	public function get_post_type() {
		return 'mahl_team';
	}
}
