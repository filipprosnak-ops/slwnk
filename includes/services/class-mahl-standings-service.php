<?php
/**
 * Standings service placeholder.
 *
 * @package MAHLManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle future standings calculations.
 */
class MAHL_Standings_Service extends MAHL_Base_Service {

	/**
	 * Return the configured standings point rules.
	 *
	 * @return array
	 */
	public function get_point_rules() {
		return MAHL_Settings_Service::get_standings_rules();
	}
}
