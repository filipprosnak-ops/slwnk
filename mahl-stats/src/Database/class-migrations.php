<?php
/**
 * Database migrations runner.
 *
 * @package MAHL_Stats
 */

if (!defined('ABSPATH')) {
	exit;
}

class MAHL_Stats_Migrations
{
	/**
	 * Run plugin migrations.
	 *
	 * @return void
	 */
	public static function migrate(): void
	{
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$schema = MAHL_Stats_Schema::get_schema();

		foreach ($schema as $sql) {
			dbDelta($sql);
		}

		update_option('mahl_stats_db_version', MAHL_STATS_DB_VERSION);
	}
}