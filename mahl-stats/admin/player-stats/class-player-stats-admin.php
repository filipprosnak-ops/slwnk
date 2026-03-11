<?php
/**
 * Player stats admin controller.
 *
 * @package MAHL_Stats
 */

if (!defined('ABSPATH')) {
	exit;
}

class MAHL_Stats_Player_Stats_Admin
{
	public function render(): void
	{
		global $wpdb;

		$seasons_table = MAHL_Stats_DB::table('seasons');
		$phases_table  = MAHL_Stats_DB::table('phases');

		$seasons = $wpdb->get_results("SELECT * FROM {$seasons_table} ORDER BY id DESC");
		$phases  = $wpdb->get_results("SELECT * FROM {$phases_table} ORDER BY season_id DESC, sort_order ASC, id ASC");

		$selected_season_id = isset($_GET['season_id']) ? absint($_GET['season_id']) : 0;
		$selected_phase_id  = isset($_GET['phase_id']) ? absint($_GET['phase_id']) : 0;

		$rows = [];
		if ($selected_season_id > 0) {
			$service = new MAHL_Stats_Player_Stats_Service();
			$rows = $service->calculate(
				$selected_season_id,
				$selected_phase_id > 0 ? $selected_phase_id : null
			);
		}

		include MAHL_STATS_PLUGIN_DIR . 'admin/player-stats/views/list.php';
	}
}