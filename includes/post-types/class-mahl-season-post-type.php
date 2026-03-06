<?php
/**
 * Season post type placeholder.
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
	 * Return the season post type key.
	 *
	 * @return string
	 */
	public function get_post_type() {
		return 'mahl_season';
	}
}
