<?php
/**
 * Plugin activator.
 *
 * @package MAHL_Stats
 */

if (!defined('ABSPATH')) {
	exit;
}

class MAHL_Stats_Activator
{
	/**
	 * Activation routine.
	 *
	 * @return void
	 */
	public static function activate(): void
	{
		if (!current_user_can('activate_plugins')) {
			return;
		}

		self::set_default_options();
		self::set_plugin_version();

		MAHL_Stats_Migrations::migrate();

		flush_rewrite_rules();
	}

	/**
	 * Store plugin version in DB.
	 *
	 * @return void
	 */
	protected static function set_plugin_version(): void
	{
		update_option('mahl_stats_version', MAHL_STATS_VERSION);
	}

	/**
	 * Set default options.
	 *
	 * @return void
	 */
	protected static function set_default_options(): void
	{
		$default_options = [
			'plugin_version' => MAHL_STATS_VERSION,
			'db_version'     => MAHL_STATS_DB_VERSION,
			'text_domain'    => 'mahl-stats',
			'language'       => 'sk',
		];

		$existing = get_option('mahl_stats_options', []);

		if (!is_array($existing)) {
			$existing = [];
		}

		update_option(
			'mahl_stats_options',
			wp_parse_args($existing, $default_options)
		);
	}
}