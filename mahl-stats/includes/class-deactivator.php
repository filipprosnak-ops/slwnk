<?php
/**
 * Plugin deactivator.
 *
 * @package MAHL_Stats
 */

if (!defined('ABSPATH')) {
	exit;
}

class MAHL_Stats_Deactivator
{
	/**
	 * Deactivation routine.
	 *
	 * @return void
	 */
	public static function deactivate(): void
	{
		if (!current_user_can('activate_plugins')) {
			return;
		}

		flush_rewrite_rules();
	}
}